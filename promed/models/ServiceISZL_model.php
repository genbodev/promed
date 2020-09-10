<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceISZL_model - модель для синхронизации данных с АИС «Информационное сопровождение застрахованных лиц»
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			ServiceISZL
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.04.2017
 */

require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ServiceISZL_model extends SwModel {
	protected $host;
	protected $port;
	protected $user;
	protected $password;
	protected $request_queue;
	protected $reply_queue;
	protected $consumer_timeout;
	protected $ServiceList_id;
	protected $init_date;
	protected $allowed_lpus = array();

	protected $LpuIdFilter = null;
	protected $minutesLimit = 25;

	protected $connections = array();
	protected $channels = array();
	protected $queues = array();
	protected $allowSaveGUID = true;
	protected $errorIndex = -1;
	
	// В каких регионах работает валидацияци  пакетов
	protected $validateOnRegions = array(
		//'krym',
		//'buryatiya',
	);

	private $packagesTableStruct = "
		Lpu_id bigint,
		LPU int,
		ObjectID bigint,
		ID uniqueidentifier,
		TYPE varchar(20),
		PACKAGE_TYPE varchar(100),
		DATE datetime
	";

	protected $packagesIndexedFields = "PACKAGE_TYPE, ObjectID, DATE";

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->helper('xml');

		$this->load->model('ServiceList_model');
		$this->load->helper('ServiceListLog');

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('IszlKrym');

		$this->load->model('ObjectSynchronLog_model');
		$this->ServiceList_id = $this->ServiceList_model->getServiceListId('AISInfoSup');
		if (empty($this->ServiceList_id)) {
			throw new Exception('Не найден сервис AISInfoSup в stg.ServiceList');
		}

		$config = $this->config->item('ISZL');
		$this->host = $config['host'];
		$this->port = $config['port'];
		$this->user = $config['user'];
		$this->password = $config['password'];
		$this->request_queue = $config['request_queue'];
		$this->reply_queue = $config['reply_queue'];
		$this->consumer_timeout = $config['consumer_timeout'];
		$this->init_date = !empty($config['init_date'])?$config['init_date']:null;
		$this->allowed_lpus = !empty($config['allowed_lpus'])?$config['allowed_lpus']:array();
		if (!empty($config['lpu_id_filter'])) {
			$this->LpuIdFilter = $config['lpu_id_filter'];
		}
	}

	/**
	 * @param string $serviceMode
	 * @param string $listName
	 * @param string $packageType
	 * @return string
	 */
	function getQueue($serviceMode, $listName, $packageType = 'default') {
		$key = implode(';', array($serviceMode, $listName, $packageType));

		if (!isset($this->queues[$key])) {
			list($channel, $connection) = $this->getChannel(null, $serviceMode, $listName, $packageType);
			list($queue,,) = $channel->queue_declare($this->request_queue, false, true, false, false);
			$this->queues[$key] = $queue;
		} else {
			list($channel, $connection) = $this->getChannel($this->queues[$key], $serviceMode, $listName, $packageType);
		}

		return array($this->queues[$key], $channel, $connection);
	}

	/**
	 * @return AMQPStreamConnection
	 * @throws Exception
	 */
	function connectToAMQP($queue = array()) {
		if (empty($this->host)) {
			throw new Exception('Не указан host');
		}
		if (empty($this->port)) {
			throw new Exception('Не указан port');
		}
		if (empty($this->user)) {
			throw new Exception('Не указан user');
		}
		if (empty($this->user)) {
			throw new Exception('Не указан passowrd');
		}
		$host = isset($queue['host']) ? $queue['host'] : $this->host;
		$port = isset($queue['port']) ? $queue['port'] : $this->port;
		$user = isset($queue['user']) ? $queue['user'] : $this->user;
		$password = isset($queue['password']) ? $queue['password'] : $this->password;
		return new AMQPStreamConnection($host, $port, $user, $password, '/', false, 'AMQPLAIN', null, 'en_US', 210.0, 210.0, null, true, 100);
	}

	/**
	 * @param string $serviceMode
	 * @param string $listName
	 * @param string $packageType
	 * @return string
	 */
	function getChannel($queue, $serviceMode, $listName, $packageType = 'default') {
		$key = implode(';', array($serviceMode, $listName, $packageType));
		$queue = is_array($queue) ? $queue : array();
		$hostkey = isset($queue['host']) ? $queue['host'] : 'default';

		if (!isset($this->channels[$hostkey])) {
			$connection = $this->connectToAMQP($queue);
			$this->connections[$hostkey] = $connection;
		} else {
			$connection = $this->connections[$hostkey];
		}

		if (!isset($this->channels[$key])) {
			$channel = $connection->channel();
			$this->channels[$key] = $channel;
		}

		return array($this->channels[$key], $connection);
	}

	function closeConnections() {
		foreach($this->channels as $key => $channel) {
			$channel->close();
			$this->channels = array();
		}
		foreach($this->connections as $key => $connection) {
			$connection->close();
			$this->connections = array();
		}
	}

	/**
	 * @param array $arr
	 * @return array
	 */
	function htmlspecialchars_arr($arr) {
		return array_map(function($value) {
			if (is_array($value)) {
				return $this->htmlspecialchars_arr($value);
			} else {
				return htmlspecialchars(trim($value));
			}
		}, $arr);
	}

	/**
	 * @return array
	 */
	function getPackageTypeMap() {
		return array();
	}

	/**
	 * @param array $packageTypes
	 * @param bool $includeBaseType
	 * @param bool $inlineSQL
	 * @return array
	 */
	function getPackageTypes($packageTypes, $includeBaseType = false, $inlineSQL = false) {
		if(empty($packageTypes)) return [];

		if(!is_array($packageTypes)) $packageTypes = [$packageTypes];

		$packageTypesMap = $this->getPackageTypeMap();

		$result = [];

		foreach ($packageTypes as $packageType){
			if(!empty($packageTypesMap) && isset($packageTypesMap[$packageType])){
				$result[] = $packageTypesMap[$packageType];
				if($includeBaseType && $packageTypesMap[$packageType] != $packageType){
					$result[] = $packageType;
				}
			}else{
				$result[] = $packageType;
			}
		}

		$result = array_unique($result);

		return $inlineSQL ? "'".implode("','", $result)."'" : $result;
	}

	/**
	 * @return array
	 */
	function getPackageFieldsMap() {
		return [];
	}
	

	/**
	 * @param string $packageType
	 * @return string
	 */
	function packageTypeMapper($packageType) {
		$map = $this->packageTypeMap;
		return isset($map[$packageType])?$map[$packageType]:$packageType;
	}

	/**
	 * @param string $packageType
	 * @return string
	 */
	function packageTypeReverseMapper($packageType) {
		$map = array_flip($this->packageTypeMap);
		return isset($map[$packageType])?$map[$packageType]:$packageType;
	}

	/**
	 * @param string $packageType
	 * @param array $item
	 * @return array
	 */
	function packageFieldsMapper($packageType, $item) {
		$map = $this->packageFieldsMap;
		if (empty($map[$packageType])) {
			return $item;
		}
		$map = $map[$packageType];
		$_item = array();
		foreach($item as $key => $value) {
			if (is_array($value) && isset($value[0])) {
				$value = array_map(function($item) use($packageType) {
					return $this->packageFieldsMapper($packageType, $item, true);
				}, $value);
			}
			if (isset($map[$key])) {
				$_item[$map[$key]] = $value;
			} else {
				$_item[$key] = $value;
			}
		}
		return $_item;
	}

	/**
	 * @param string $packageType
	 * @return null|string
	 */
	function loadSchema($packageType) {
		$path = $_SERVER['DOCUMENT_ROOT'].'/documents/xsd/TFOMSAutoInteract/'.$this->regionNick.'/'.mb_strtolower($packageType).'.xsd';
		if (!file_exists($path)) {
			$path = $_SERVER['DOCUMENT_ROOT'].'/documents/xsd/TFOMSAutoInteract/'.mb_strtolower($packageType).'.xsd';
		}
		return file_exists($path)?file_get_contents($path):null;
	}

	/**
	 * @param string $packageType
	 * @return null|string
	 */
	function getSchema($packageType) {
		if (!array_key_exists($packageType, $this->schemas)) {
			$this->schemas[$packageType] = $this->loadSchema($packageType);
		}
		return $this->schemas[$packageType];
	}

	/**
	 * @param string $packageType
	 * @param string $packageBody
	 * @return array
	 */
	function checkPackage($packageType, $packageBody) {
		$errors = array();

		if (!in_array($this->regionNick, $this->validateOnRegions)) {
			return $errors;
		}

		$xsd = $this->getSchema($packageType);
		if (!$xsd) return $errors;

		if (empty($packageBody)) {
			return $errors;
		}

		libxml_use_internal_errors(true);

		$xml = new DOMDocument();
		$xml->loadXML($packageBody);

		if (!$xml->schemaValidateSource($xsd)) {
			if (!empty($_REQUEST['getDebug'])) {
				echo '<pre>';print_r(libxml_get_errors());
			}
			$errors = array_map(function($error) {
				return trim($error->message);
			}, libxml_get_errors());
		}

		libxml_clear_errors();

		return $errors;
	}

	/**
	 * @param string $packageType
	 * @param string $packageBody
	 * @return bool
	 * @throws Exception
	 */
	function isNotSendPackage($packageType, $procDataType, $objectID, $packageBody, $log) {
		$packageBody = xml_to_array($packageBody);
		$packageData = json_encode($packageBody);

		$dataHash = $this->ServiceList_model->generationDataHash($packageBody);

		$params = [
			'ServiceList_id' => $this->ServiceList_id,
			'ServiceListPackage_ObjectID' => $objectID,
			'ServicePackage_Data' => $packageData,
			'ServicePackage_DataHash' => $dataHash
		];

		$query = "
				select top 1
					case 
						when SLP.ServiceListPackage_IsNotSend is not null and SLP.ServiceListPackage_IsNotSend = 2 then 1 
						when SLP.ServiceListPackage_IsNotSend is null then 1
						else 0 
					end as IsNotSend
				from
					stg.v_ServiceListPackage SLP with(nolock)
					inner join stg.v_ServiceListLog SLL with(nolock) on SLL.ServiceListLog_id = SLP.ServiceListLog_id
					inner join stg.v_ServiceListPackageType SLPT with(nolock) on SLPT.ServiceListPackageType_id = SLP.ServiceListPackageType_id
					inner join stg.v_PackageRouteType PRT with(nolock) on PRT.PackageRouteType_id = SLPT.PackageRouteType_id
					inner join stg.v_PackageStatus PS with(nolock) on PS.PackageStatus_id = SLP.PackageStatus_id
					left join stg.v_ServicePackage SP with(nolock) on SP.ServiceListPackage_id = SLP.ServiceListPackage_id
				where 1=1
					and SLL.ServiceList_id = :ServiceList_id
					and SLPT.ServiceListPackageType_Name in ({$this->getPackageTypes($packageType, true, true)})
					and SLP.ServiceListPackage_ObjectID = :ServiceListPackage_ObjectID
					and SP.ServicePackage_IsResp = 1
					and SP.ServicePackage_DataHash = :ServicePackage_DataHash
				order by
					SLP.ServiceListPackage_insDT desc
			";

		$IsNotSend = $this->getFirstResultFromQuery($query, $params, true);
		if ($IsNotSend === false) {
			throw new Exception('Ошибка при проверке запрета отправки пакета');
		}

		return !empty($IsNotSend);
	}

	/**
	 * @param string $packageBody
	 * @return string
	 */
	function injectErrorsInPackage($packageBody) {
		if (empty($_REQUEST['errors']) || ($this->errorIndex + 1) >= $_REQUEST['errors']) {
			return $packageBody;
		}

		$getValues = function(&$xml) use(&$getValues) {
			$values = array();
			foreach($xml as $key => &$value) {
				if (is_array($value)) {
					$values = array_merge($values, $getValues($value));
				} else {
					$values[] =& $value;
				}
			}
			return $values;
		};

		$getKeys = function($xml, $onlyLeaf = false) use(&$getKeys) {
			$keys = array();
			if ($onlyLeaf) {
				foreach($xml as $key => $value) {
					if (is_array($value)) {
						$keys = array_merge($keys, $getKeys($value, $onlyLeaf));
					} else {
						$keys[] = $key;
					}
				}
			} else {
				foreach($xml as $key => $value) {
					$keys[] = $key;
					if (is_array($value)) {
						$keys = array_merge($keys, $getKeys($value, $onlyLeaf));
					}
				}
			}
			return $keys;
		};

		$errors = array(
			//1. Очистить наполнение одного из тегов
			function($xml) use($getValues) {
				$xmlArray = XmlToArray($xml);
				$values = $getValues($xmlArray);
				$idx = rand(0, count($values) - 1);
				$values[$idx] = '';
				$xml = ArrayToXml($xmlArray);
				$xml = preg_replace('/<data>/', '', $xml);
				$xml = preg_replace('/<\/data>/', '', $xml);
				return $xml;
			},
			//2. Добавить в наименование одного из тегов одну английскую букву
			function($xml) use($getKeys) {
				$xmlArray = XmlToArray($xml);
				$keys = $getKeys($xmlArray);
				$key = $keys[rand(0, count($keys) - 1)];
				$chars = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
				$char = $chars[rand(0, count($chars) - 1)];
				$xml = preg_replace('/<('.$key.')>/', '<$1'.$char.'>', $xml);
				$xml = preg_replace('/<\/('.$key.')>/', '</$1'.$char.'>', $xml);
				return $xml;
			},
			//3. Изменить уровень одного из тегов
			function($xml) use($getKeys) {
				$xmlArray = XmlToArray($xml);
				$keys = $getKeys($xmlArray, true);
				$key = $keys[rand(0, count($keys) - 1)];
				$xmlDom = new DOMDocument();
				$xmlDom->loadXML($xml);
				$xpath = new DOMXpath($xmlDom);
				$result = $xpath->query('.//'.$key);
				foreach($result as $node) {
					$newNode = $xmlDom->createElement($node->nodeName, $node->nodeValue);
					$node->parentNode->parentNode->appendChild($newNode);
					$node->parentNode->removeChild($node);
					break;
				}
				return $xmlDom->saveXML();
			},
			//4. Поменять местами наполнение двух тегов в записи
			function($xml) use($getValues) {
				$xmlArray = XmlToArray($xml);
				$values = $getValues($xmlArray);
				$idx1 = rand(0, count($values) - 1);
				$idx2 = rand(0, count($values) - 1);
				$value1 = $values[$idx1];
				$value2 = $values[$idx2];
				$values[$idx1] = $value2;
				$values[$idx2] = $value1;
				$xml = ArrayToXml($xmlArray);
				$xml = preg_replace('/<data>/', '', $xml);
				$xml = preg_replace('/<\/data>/', '', $xml);
				return $xml;
			},
			//5. Заменить одну дату на "Тест"
			function($xml) use($getValues) {
				$xmlArray = XmlToArray($xml);
				$values = $getValues($xmlArray);
				$dates = array();
				foreach($values as &$value) {
					if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $value) ||
						preg_match('/^\d{4}\-\d{2}\-\d{2}T\d{2}:\d{2}:\d{2}$/', $value)
					) {
						$dates[] =& $value;
					}
				}
				if (empty($dates)) {
					return $this->injectErrorsInPackage($xml);
				}
				$idx = rand(0, count($dates) - 1);
				$dates[$idx] = 'Тест';
				$xml = ArrayToXml($xmlArray);
				$xml = preg_replace('/<data>/', '', $xml);
				$xml = preg_replace('/<\/data>/', '', $xml);
				return $xml;
			}
		);

		$index = (++$this->errorIndex);
		$count = count($errors);

		$packageBodyWithError = $errors[$index%$count]($packageBody);

		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>with error:<br/>';
			echo htmlentities($packageBodyWithError);
		}

		return $packageBodyWithError;
	}

	/**
	 * @param mixed $config
	 */
	function showConfig($config) {
		if (!isSuperadmin()) return;
		if (is_array($config)) {
			unset($config['password']);
		}
		echo '<pre>';
		print_r($config);
	}

	/**
	 * @param string $name
	 */
	function showServiceConfig() {
		$this->showConfig($this->config->item('ISZL'));
	}

	/**
	 * @return string
	 */
	function GUID() {
		if (function_exists('com_create_guid')){
			return trim(com_create_guid(), '{}');
		} else {
			mt_srand((double)microtime()*10000);
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			return ''
			.substr($charid, 0, 8).$hyphen
			.substr($charid, 8, 4).$hyphen
			.substr($charid,12, 4).$hyphen
			.substr($charid,16, 4).$hyphen
			.substr($charid,20,12);
		}
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * @param string $packageType
	 * @param array $item
	 * @return string
	 */
	function createPackageBody($packageType, $item) {
		$this->load->library('parser');
		$template = 'export_xml/package_' . mb_strtolower($packageType);
		$item = $this->packageFieldsMapper($packageType, $item);
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r(array($this->packageTypeMapper($packageType) => $item));
		}
		$item['isISZL'] = true;
		$item = $this->htmlspecialchars_arr($item);
		$xml = '<?xml version="1.0" encoding="utf-8"?>'.$this->parser->parse_ext($template, $item, true);
		$xml = preg_replace('/\t+(?:\r\n|\r|\n)/', "", $xml);
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';echo htmlentities($xml);
		}
		return $xml;
	}

	/**
	 * Формирование параметров для передачи пакета в очередь
	 * @param string $packageType
	 * @param string|int $id
	 * @param string|null $replyQueue
	 * @return array
	 */
	function createPackageProperties($packageType, $id, $replyQueue = null) {
		$_packageType = $this->packageTypeMapper($packageType);
		$app_id = 'Promed';
		$properties = array(
			'app_id' => $app_id,			//Имя сервера
			'user_id' => $this->user,		//Имя пользователя
			'type' => $_packageType,		//Тип пакета (ServiceListPackageType_Name)
			'message_id' => $id,			//Идентификатор пакета
			'content_encoding' => 'utf-8',	//Кодировка
			'content_type' => 'Xml',		//Тип контента
			'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
		);
		if (!empty($replyQueue)) {
			$properties['reply_to'] = $replyQueue;
		}
		return $properties;
	}

	/**
	 * Создание временной таблицы для хранения актуальных пакетов
	 * @return string
	 * @throws Exception
	 */
	function createActualPackageTable() {
		$tmpTable = '#actual_packages_'.time();

		$query = "
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				IF OBJECT_ID(N'tempdb..{$tmpTable}', N'U') IS NOT NULL
					DROP TABLE {$tmpTable};
				create table {$tmpTable} ({$this->packagesTableStruct})
				CREATE UNIQUE CLUSTERED INDEX Idx1 ON {$tmpTable}({$this->packagesIndexedFields});
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->queryResult($query);

		if (!is_array($result)) {
			throw new Exception('Ошибка при создании временной таблицы');
		}
		if (!$this->isSuccessful($result)) {
			throw new Exception($result[0]['Error_Msg']);
		}

		return $tmpTable;
	}

	/**
	 * Заполнение временной таблицы
     * @param array $data
	 * @param string $tmpTable
	 * @param string|array|null $packageTypes
	 * @throws Exception
	 */
	function fillActualPackagesTable($data, $tmpTable, $packageTypes = null) {
		$package_types = array();
		$params = array();

		if (!empty($packageTypes)) {
			$package_types = is_array($packageTypes)?$packageTypes:array($packageTypes);
		} else {
			$procConfig = $this->getProcConfig();
			foreach($procConfig['Insert'] as $object => $packageTypes) {
				if (is_array($packageTypes)) {
					$package_types = array_merge($package_types, $packageTypes);
				} else {
					$package_types[] = $packageTypes;
				}
			}
		}

		$package_types_str = "'".implode("','", $package_types)."'";

		$lpuFilter = '';
		if (!empty($this->LpuIdFilter)) {
			$lpuFilter = "and SLP.Lpu_id = :LpuIdFilter";
			$params['LpuIdFilter'] = $this->LpuIdFilter;
		}
		if (!empty($this->allowed_lpus)) {
			$allowed_lpus_str = implode(",", $this->allowed_lpus);
			$lpuFilter = "and SLP.Lpu_id in ({$allowed_lpus_str})";
		}
        
		$params['ServiceList_id'] = $this->ServiceList_id;

		$tmpTmpTable = '#all_packages_'.time();

		//Заполнить временную таблицу актуальными записями, которые были экспортированны ранее
		$query = "
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				delete from {$tmpTable}
				where PACKAGE_TYPE in ({$package_types_str});
				
				create table {$tmpTmpTable} (
					Lpu_id bigint,
					LPU int,
					ObjectID bigint,
					TYPE_ID bigint,
					TYPE varchar(20),
					PACKAGE_TYPE varchar(100),
					DATE datetime
				);
				
				insert into {$tmpTmpTable}
				select
					L.Lpu_id,
					L.Lpu_f003mcod as LPU,
					SLP.ServiceListPackage_ObjectID as ObjectID,
					SLPDT.ServiceListProcDataType_id as TYPE_ID,
					SLPDT.ServiceListProcDataType_Name as TYPE,
					SLPT.ServiceListPackageType_NAME as PACKAGE_TYPE,
					max(SLL.ServiceListLog_begDT) as DATE
				from
					stg.v_ServiceListPackage SLP with(nolock)
					inner join stg.v_ServiceListLog SLL with(nolock) on SLL.ServiceListLog_id = SLP.ServiceListLog_id
					inner join stg.v_ServiceListPackageType SLPT with(nolock) on SLPT.ServiceListPackageType_id = SLP.ServiceListPackageType_id
					inner join stg.v_ServiceListProcDataType SLPDT with(nolock) on SLPDT.ServiceListProcDataType_id = SLP.ServiceListProcDataType_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = SLP.Lpu_id
				where
					SLL.ServiceList_id = :ServiceList_id
					and SLPT.ServiceListPackageType_Name in ({$package_types_str})
					{$lpuFilter}
					and not exists(
						select * from stg.v_ServiceListDetailLog SLDL with(nolock)
						where SLDL.ServiceListDetailLog_id = SLL.ServiceListLog_id
						and SLDL.ServiceListPackage_id = SLP.ServiceListPackage_id
						and SLDL.ServiceListLogType_id = 2
					)
				group by
					L.Lpu_id,
					L.Lpu_f003mcod,
					SLP.ServiceListPackage_ObjectID,
					SLPDT.ServiceListProcDataType_id,
					SLPDT.ServiceListProcDataType_Name,
					SLPT.ServiceListPackageType_NAME
				
				insert into {$tmpTable}
				select
					PL.Lpu_id,
					PL.LPU,
					PL.ObjectID,
					OSL.Object_Guid AS ID,
					lastUpdate.TYPE,
					PL.PACKAGE_TYPE,
					lastUpdate.DATE as DATE
				from 
					{$tmpTmpTable} as PL
					outer apply(
						select top 1 DATE, TYPE
						from {$tmpTmpTable} where PACKAGE_TYPE = PL.PACKAGE_TYPE 
						and ObjectID = PL.ObjectID and TYPE_ID in (1,3) and DATE >= PL.DATE
						order by DATE desc
					) lastUpdate
					outer apply (
						select top 1 OSL.Object_Guid
						from v_ObjectSynchronLog OSL with(nolock)
						inner join v_ObjectSynchronLogService OSLS with(nolock) on OSLS.ObjectSynchronLogService_id = OSL.ObjectSynchronLogService_id
						where OSLS.ObjectSynchronLogService_SysNick = 'IszlKrym' 
						and OSL.Object_Name = PL.PACKAGE_TYPE and OSL.Object_id = PL.ObjectID
						order by OSL.Object_setDT desc
					) OSL
				where 
					PL.TYPE_ID = 1
					and not exists (
						select * from {$tmpTmpTable}
						where ObjectID = PL.ObjectID and PACKAGE_TYPE = PL.PACKAGE_TYPE
						and TYPE_ID = 2 and DATE >= PL.DATE
					);
					
				DROP TABLE {$tmpTmpTable};
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
                
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			throw new Exception("Ошибка при заполении временной таблицы объектами {$package_types_str}");
		}
		if (!$this->isSuccessful($result)) {
			throw new Exception($result[0]['Error_Msg']);
		}
	}

	/**
	 * Получение данных о подразделениях
	 */
	function package_BRANCH($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and LB.LpuBuilding_id in ({$exportIds_str})";
		}
        
		if ($procDataType == 'Delete') {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and PL.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and PL.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					PL.Lpu_id,
					PL.LPU,
					'Delete' as TYPE,
					PL.ObjectID,
					PL.ID
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = PL.ObjectID
					 -- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'BRANCH'
					and LB.LpuBuilding_id is null
					{$lpuFilter}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as LPU,
					ProcDataType.Value as TYPE,
					LB.LpuBuilding_id as ObjectID,
					isnull(BRANCH.ID, newid()) as ID,
					LB.LpuBuilding_Code as BR_CODE,
					LB.LpuBuilding_Name as NAME,
					convert(varchar(10), LB.LpuBuilding_begDate, 120) as DATE_ADD,
					A.Address_Address as ADDRESS,
					case
						when L.MesAgeLpuType_id = 2 then 1	--Детское
						when L.MesAgeLpuType_id = 3 then 4	--Общее
						else 3								--Взрослое
					end as LPU_TYPE,
					case
						when LB.LpuBuildingType_id in (2,13,22,24,28) then 1 	--Поликлиника
						when LB.LpuBuildingType_id in (1,4,5,23) then 2			--Стационар
						when LB.LpuBuildingType_id = 27 then 5	--Скорая помощь
					end as CATEGORY,
					LBH.LpuBuildingHealth_Phone as PHONE,
					case 
						when LB.LpuBuilding_endDate is null 
						then 1 else 0 
					end as ACTIVE,
					convert(varchar(10), LB.LpuBuilding_endDate, 120) as DATE_REMOVE
					-- end select
				from
					-- from
					v_LpuBuilding LB with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
					left join v_LpuBuildingHealth LBH with(nolock) on LBH.LpuBuilding_id = LB.LpuBuilding_id
					left join v_Address A with(nolock) on A.Address_id = isnull(LB.PAddress_id, LB.Address_id)
					left join {$tmpTable} BRANCH on BRANCH.PACKAGE_TYPE = 'BRANCH' and BRANCH.ObjectID = LB.LpuBuilding_id
					outer apply(
						select case
							when BRANCH.ID is null and LB.LpuBuilding_endDate is null then 'Insert'
							when BRANCH.ID is not null and BRANCH.DATE <= LB.LpuBuilding_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					LB.LpuBuildingType_id in (1,2,4,5,13,22,23,24,27,28)
					{$lpuFilter}
					and ProcDataType.Value like :ProcDataType
					-- end where
				order by
					-- order by
					L.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Получение данных об отделениях
	 */
	function package_DIVISION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
			'ServiceList_id' => $this->ServiceList_id
		);
        
		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and LS.LpuSection_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		}

		if ($procDataType == 'Delete') {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and PL.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}

			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and PL.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					PL.Lpu_id,
					PL.LPU,
					'Delete' as TYPE,
					PL.ObjectID,
					PL.ID
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'DIVISION'
					and LS.LpuSection_id is null
					{$lpuFilter}
                    {$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
			}
			
			// По задаче PROMEDWEB-4767 - выгружаем только со статусом оплаты  ОМС
			$joins = '';
			if ($this->regionNick == 'krym') {
				$joins = "
					inner join v_LpuSectionFinans LSF with(nolock) on LSF.LpuSection_id = LS.LpuSection_id
					inner join v_PayType PT with(nolock) on PT.PayType_id = LSF.PayType_id and PT.PayType_SysNick = 'oms'
				";
			}

			$query = "
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as LPU,
					ProcDataType.Value as TYPE,
					LS.LpuSection_id as ObjectID,
					isnull(DIVISION.ID, newid()) as ID,
					LS.LpuSection_Code as DV_CODE,
					rtrim(LS.LpuSection_Name) as NAME,
					rtrim(LS.LpuSection_Name) as FULLNAME,
					1 as LEVEL,
					L.Lpu_f003mcod+right('00'+LB.LpuBuilding_Code, 3)+right('00'+LS.LpuSection_Code, 3) as CODE_MZ,
					LU.LpuUnit_Phone as PHONE,
					A.Address_Address as ADDRESS,
					BRANCH.ID as BRANCH_ID,
					case 
						when LU.LpuUnitType_SysNick = 'stac' then 1
						when LU.LpuUnitType_SysNick = 'dstac' then 9
						when LU.LpuUnitType_SysNick = 'pstac' then 7
						when LU.LpuUnitType_SysNick = 'priem' then 6
					end as DIVISION_TYPE,
					case 
						when LSFC.AllCnt > 0 and LSFC.OmsCnt = 0 
						then 12902 else 12901
					end as REVENUE_TYPE,
					L.Lpu_id as ID_NUMBER_CARD,
					case 
						when LS.LpuSection_disDate is null
						then 2 else 0
					end as ACTIVE
					-- end select
				from 
					-- from
					v_LpuSection LS with(nolock)
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					inner join v_LpuBuildingType LBT with(nolock) on LBT.LpuBuildingType_id = LB.LpuBuildingType_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
					{$joins}
					left join v_Address A with(nolock) on A.Address_id = isnull(LB.PAddress_id, LB.Address_id)
					outer apply (
						select top 1 (
							select top 1 count(*) 
							from v_LpuSectionFinans with(nolock) 
							where LpuSection_id = LS.LpuSection_id
						) as AllCnt, (
							select top 1 count(*)
							from v_LpuSectionFinans LSF with(nolock)
							inner join v_PayType PT with(nolock) on PT.PayType_id = LSF.PayType_id
							where LpuSection_id = LS.LpuSection_id and PT.PayType_SysNick = 'oms'
						) as OMSCnt
					) LSFC
					inner join {$tmpTable} BRANCH on BRANCH.PACKAGE_TYPE = 'BRANCH' and BRANCH.ObjectID = LB.LpuBuilding_id
					left join {$tmpTable} DIVISION on DIVISION.PACKAGE_TYPE = 'DIVISION' and DIVISION.ObjectID = LS.LpuSection_id
					outer apply(
						select case
							when DIVISION.ID is null and LS.LpuSection_disDate is null then 'Insert'
							when DIVISION.ID is not null and DIVISION.DATE <= LS.LpuSection_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					LU.LpuUnitType_SysNick in ('stac','dstac','pstac','priem')
					and LBT.LpuBuildingType_id in (1,2,4,5,13,22,23,24,27,28)
					{$lpuFilter}
					{$filters}
					and ProcDataType.Value like :ProcDataType
					-- end where
				order by
					-- order by
					L.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Получение данных о койках
	 */
	function package_BED($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType
		);
        
        $packageType = 'AMOUNT_BED';
        if (!empty($data['packageType'])) {
            $packageType = $data['packageType'];
        }

		$filters = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and LS.LpuSection_id in ({$exportIds_str})";
		}
        
		if ($procDataType == 'Delete') {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and PL.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and PL.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					PL.Lpu_id,
					PL.LPU,
					'Delete' as TYPE,
					PL.ObjectID,
					PL.ID
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_LpuSectionBedState LSBS with(nolock) on LSBS.LpuSectionBedState_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = '{$packageType}'
					and LSBS.LpuSectionBedState_id is null
					{$lpuFilter}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
			}

			if($this->regionNick == 'buryatiya') {
				$branch_select = "
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000000' + CAST(LS.LpuSection_Code as varchar(10)), 6) as DIVISION_ID,
				";
			} else {
				$branch_select = "
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3) + RIGHT('000' + CAST(isnull(SectionCode.Value, LS.LpuSection_Code) as varchar(10)), 3) as DIVISION_ID,
				";
			}

			// PROMEDWEB-4767 (Крым)
			$planned_beds_select = $joinPayType = $amount_select = "";
			if ($this->regionNick == 'krym') {

				//для AMOUNT_BED
				// Если в статусе коек (dbo.LpuSectionBedState) Количество коек, оплачиваемых по ОМС (LpuSectionBedState_CountOms) больше 0,
				// то выгружается только информация по койкам, оплачиваемым по ОМС, иначе информация не выгружается.
				
				if(in_array($packageType, ['AMOUNT_BED','FREE_BEDS_INFORMATION'])){
					$filters .= " and LSBS.LpuSectionBedState_CountOms > 0";
				}
				
				$planned_beds_select = "
					case 
						when LSBS.LpuSectionBedState_CountOms >= LSBS.LpuSectionBedState_Plan
						then LSBS.LpuSectionBedState_Plan
						else LSBS.LpuSectionBedState_CountOms
					end as PLANNED_BEDS_COUNT,
				";

				//для FREE_BEDS_INFORMATION
				$joinPayType = "inner join v_PayType PT on PT.PayType_id = ES.PayType_id and PT.PayType_SysNick = 'oms'";
				$amount_select = "LSBS.LpuSectionBedState_CountOms - FactAmount.Value as AMOUNT,";
				
			}else{
				$planned_beds_select = "LSBS.LpuSectionBedState_Plan as PLANNED_BEDS_COUNT,";
				$amount_select = "LSBS.LpuSectionBedState_Plan - FactAmount.Value as AMOUNT,";
			}

			$query = "
				-- variables
				declare @date date = dbo.tzGetDate();
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as LPU,
					ProcDataType.Value as TYPE,
					LSBS.LpuSectionBedState_id as ObjectID,
					DLSB.Value as ID,
					{$branch_select}
					LSP.LpuSectionProfile_Code as STRUCTURE_BED,
					LSBP.LpuSectionBedProfile_Code as V020_STRUCTURE_BED,
					convert(varchar(10), LSBS.LpuSectionBedState_begDate, 120) as DATE_BEGIN,
					convert(varchar(10), LSBS.LpuSectionBedState_endDate, 120) as DATE_END,
					0 as FEMALE_BED,
					0 as MALE_BED,
					0 as BEDS_COUNT,
					0 as REPAIR_BED,
					{$planned_beds_select}
					0 as PLANNED_MALE_BED,
					0 as PLANNED_FEMALE_BED,
					convert(varchar(120), @date, 120) as ACTUAL_DATE,
					{$amount_select}
					DLSB.Value as DLSB
					-- end select
				from
					-- from
					v_LpuSectionBedState LSBS with(nolock)
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = LSBS.LpuSection_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = LS.Lpu_id
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LSBS.LpuSectionProfile_id
					inner join {$tmpTable} DIVISION on DIVISION.PACKAGE_TYPE = 'DIVISION' and DIVISION.ObjectID = LS.LpuSection_id
					outer apply (
						select top 1 LSBP.*
						from fed.v_LpuSectionBedProfileLink LSBPL with(nolock)
						inner join fed.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
						where LSBPL.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
					) LSBP
					outer apply(
						select count(*) as Value
						from v_EvnSection ES with(nolock)
						{$joinPayType}
						where ES.LpuSection_id = LS.LpuSection_id
						and ES.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
						and @date between ES.EvnSection_setDate and isnull(ES.EvnSection_disDate, @date)
					) FactAmount
					left join {$tmpTable} BED on BED.PACKAGE_TYPE = '{$packageType}' and BED.ObjectID = LSBS.LpuSectionBedState_id
					outer apply (
						select top 1 isnull(BED.ID, newid()) as Value
					) DLSB
					outer apply(
						select case
							when BED.ID is null and LS.LpuSection_disDate is null then 'Insert'
							when BED.ID is not null and BED.DATE < @date then 'Update'
						end as Value
					) ProcDataType
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						where [AS].AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and LSBS.LpuSectionBedState_begDate between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, LSBS.LpuSectionBedState_begDate)
						order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
					) ASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Section_Code'
					) SectionCode
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) BuildingCode
					-- end from
				where
					-- where
					LU.LpuUnitType_SysNick in ('stac','dstac','pstac','priem')
					and LB.LpuBuildingType_id in (1,2,4,5,13,22,23,24,27,28)
					and LSBS.LpuSectionBedState_begDate <= @date
					and (LSBS.LpuSectionBedState_endDate is null or LSBS.LpuSectionBedState_endDate > @date)
					{$lpuFilter}
					{$filters}
					and ProcDataType.Value = :ProcDataType
					-- end where
				order by
					-- order by
					L.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Получение данных о направлениях на госпитализацию
	 */
	function package_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		//Направления не изменяются и не удаляются, только создаются
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : array();
		}

		$params = array();

		$lpuFilter = '';
		if (!empty($this->LpuIdFilter)) {
			$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
			$params['LpuIdFilter'] = $this->LpuIdFilter;
		}
		if (!empty($this->allowed_lpus)) {
			$allowed_lpus_str = implode(",", $this->allowed_lpus);
			$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
		}

		$filters = "";
		if (!empty($this->init_date)) {
			$filters .= " and ED.EvnDirection_insDT >= :InitDate";
			$params['InitDate'] = $this->init_date;
		}
		
		$joins = '';
		if ($this->regionNick == 'krym') {
			$filters .= " and dLSBP.LpuSectionBedProfile_Code is not null";
			
			// По задаче PROMEDWEB-4767 - выгружаем только со статусом оплаты ОМС
			$joins = "inner join v_PayType PayT with(nolock) on PayT.PayType_id = ED.PayType_id and PayT.PayType_SysNick = 'oms'";
			
		}
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ED.EvnDirection_id in ({$exportIds_str})";
		}
		
		if($this->regionNick == 'buryatiya') {
			$branch_select = "
				CAST(dL.Lpu_f003mcod as varchar(10)) + RIGHT('00' + CAST(dLB.LpuBuilding_Code as varchar(10)), 2) as BRANCH_TO,
				CAST(dL.Lpu_f003mcod as varchar(10)) + RIGHT('000000' + CAST(dLS.LpuSection_Code as varchar(10)), 6) as DIVISION_TO,
				CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('00' + CAST(LB.LpuBuilding_Code as varchar(10)), 2) as BRANCH_FROM,
			";
		} else {
			$branch_select = "
				CAST(dL.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(dBuildingCode.Value, dLB.LpuBuilding_Code) as varchar(10)), 3) as BRANCH_TO,
				CAST(dL.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(dBuildingCode.Value, dLB.LpuBuilding_Code) as varchar(10)), 3) + RIGHT('000' + CAST(isnull(dSectionCode.Value, dLS.LpuSection_Code) as varchar(10)), 3) as DIVISION_TO,
				CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3) as BRANCH_FROM,
			";
		}

		$query = "
			select
				-- select
				L.Lpu_id,
				L.Lpu_f003mcod as LPU,
				'Insert' as TYPE,
				dL.Lpu_f003mcod as LPU_TO,
				ED.EvnDirection_id as ObjectID,
				isnull(HOSPITALISATION_REFERRAL.ID, newid()) as ID,
				ED.EvnDirection_Num as REFERRAL_NUMBER,
				convert(varchar(10), ED.EvnDirection_setDate, 120) as REFERRAL_DATE,
				convert(
					varchar(10),
					case
						when tts.TimetableStac_setDate is not null then tts.TimetableStac_setDate
						when ED.EvnQueue_id is not null and ED.EvnDirection_desDT is not null then ED.EvnDirection_desDT
						else ED.EvnDirection_setDate + 30
					end,
					120
				) as HOSPITALISATION_DATE,
				case 
					when ED.DirType_id = 1 then 0
					when ED.DirType_id = 5 then 1
				end as HOSPITALISATION_TYPE,
				{$branch_select}
				dLSBP.LpuSectionBedProfile_Code as V020_STRUCTURE_BED,
				dLSP.LpuSectionProfile_Code as STRUCTURE_BED,
				D.Diag_Code as MKB,
				D.Diag_Name as DIAGNOSIS,
				convert(varchar(10), isnull(ED.EvnDirection_desDT, dateadd(day, 14,  ED.EvnDirection_setDate)), 120) as PLANNED_DATE,
				case 
					when dLU.LpuUnitType_SysNick = 'stac'
					then 1 else 2
				end as USL_OK,
				nullif(MSF.Person_Snils, '00000000000') as DOC_CODE,
				PT.PolisType_CodeF008 as POLICY_TYPE,
				Polis.Polis_Ser as POLIS_SERIAL,
				Polis.Polis_Num as POLIS_NUMBER,
				SMO.Orgsmo_f002smocod as SMO,
				rtrim(PS.Person_FirName) as FIRST_NAME,
				rtrim(PS.Person_SurName) as LAST_NAME,
				rtrim(PS.Person_SecName) as FATHER_NAME,
				case 
					when S.Sex_Code in (1,3) then '10301'
					when S.Sex_Code in (2) then '10302'
				end as SEX,
				convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
				isnull(PS.PersonPhone_Phone, 'не указан') as PHONE,
				PS.Person_id,
				PATIENT.ID as PATIENT,
				0 as ANOTHER_REGION
				-- end select
			from
				-- from
				v_EvnDirection ED with(nolock)
				inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ED.MedStaffFact_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = isnull(ED.LpuSection_id, MSF.LpuSection_id)
				inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = isnull(LS.LpuBuilding_id, MSF.LpuBuilding_id)
				inner join v_Lpu L with(nolock) on L.Lpu_id = ED.Lpu_id
				inner join v_LpuSection dLS with(nolock) on dLS.LpuSection_id = ED.LpuSection_did
				inner join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
				inner join v_LpuBuilding dLB with(nolock) on dLB.LpuBuilding_id = dLS.LpuBuilding_id
				inner join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
				inner join v_LpuSectionProfile dLSP with(nolock) on dLSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				outer apply (
					select top 1 LSBP.*
					from v_LpuSectionBedProfileLink LSBPL with(nolock)
					inner join fed.v_LpuSectionBedProfileLink fLSBPL with(nolock) on fLSBPL.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_id
					inner join fed.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = fLSBPL.LpuSectionBedProfile_fedid
					where LSBPL.LpuSectionProfile_id = ED.LpuSectionProfile_id
					and ED.EvnDirection_setDate between LSBPL.LpuSectionBedProfileLink_begDT and isnull(LSBPL.LpuSectionBedProfileLink_endDT, ED.EvnDirection_setDate)
					order by LSBPL.LpuSectionBedProfileLink_begDT
				) dLSBP
				inner join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
				inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = ED.PersonEvn_id and PS.Server_id = ED.Server_id
				{$joins}
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
				left join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = Polis.OrgSMO_id
				left join v_Sex S with(nolock) on S.Sex_id = PS.Sex_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnDirection_id = ED.EvnDirection_id
				outer apply (
					select top 1 ASV.AttributeSignValue_id
					from v_AttributeSignValue ASV with(nolock)
					inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
					where [AS].AttributeSign_Code = 1
					and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
					and ED.EvnDirection_setDate between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, ED.EvnDirection_setDate)
					order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
				) ASV
				outer apply (
					select top 1 AV.AttributeValue_ValueString as Value
					from v_AttributeValue AV with(nolock)
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
					and A.Attribute_SysNick like 'Section_Code'
				) SectionCode
				outer apply (
					select top 1 AV.AttributeValue_ValueString as Value
					from v_AttributeValue AV with(nolock)
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
					and A.Attribute_SysNick like 'Building_Code'
				) BuildingCode
				outer apply (
					select top 1 ASV.AttributeSignValue_id
					from v_AttributeSignValue ASV with(nolock)
					inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
					where [AS].AttributeSign_Code = 1
					and ASV.AttributeSignValue_TablePKey = dLS.LpuSection_id
					and ED.EvnDirection_setDate between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, ED.EvnDirection_setDate)
					order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
				) dASV
				outer apply (
					select top 1 AV.AttributeValue_ValueString as Value
					from v_AttributeValue AV with(nolock)
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where AV.AttributeSignValue_id = dASV.AttributeSignValue_id
					and A.Attribute_SysNick like 'Section_Code'
				) dSectionCode
				outer apply (
					select top 1 AV.AttributeValue_ValueString as Value
					from v_AttributeValue AV with(nolock)
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where AV.AttributeSignValue_id = dASV.AttributeSignValue_id
					and A.Attribute_SysNick like 'Building_Code'
				) dBuildingCode
				left join {$tmpTable} HOSPITALISATION_REFERRAL on HOSPITALISATION_REFERRAL.PACKAGE_TYPE = 'HOSPITALISATION_REFERRAL'
					and HOSPITALISATION_REFERRAL.ObjectID = ED.EvnDirection_id
				left join v_TimetableStac_lite tts with (nolock) on tts.TimetableStac_id = ed.TimetableStac_id
				outer apply (
					select top 1 OSL.Object_Guid as ID
					from v_ObjectSynchronLog OSL with(nolock)
					inner join v_ObjectSynchronLogService OSLS with(nolock) on OSLS.ObjectSynchronLogService_id = OSL.ObjectSynchronLogService_id
					where OSLS.ObjectSynchronLogService_SysNick = 'IszlKrym' 
					and OSL.Object_Name = 'PATIENT' and OSL.Object_id = PS.Person_id
					order by OSL.Object_setDT desc
				) PATIENT
				-- end from
			where
				-- where
				ED.DirType_id in (1,5)
				and ED.EvnDirection_failDT is null
				and HOSPITALISATION_REFERRAL.ID is null
				{$lpuFilter}
				{$filters}
				-- end where
			order by
				-- order by
				L.Lpu_id
				-- end order by
		";

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Получение данных об отмене направлений на госпитализацию
	 */
	function package_CANCEL_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		//Направления не изменяются и не удаляются, только создаются
		if ($procDataType != 'Insert' && !in_array($this->regionNick, array('pskov'))) {
			return $returnType == 'count' ? 0 : array();
		}

		$params = array(
			'procDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($this->init_date)) {
			$filters .= " and ED.EvnDirection_insDT >= :InitDate";
			$params['InitDate'] = $this->init_date;
		}
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ED.EvnDirection_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		}

		if ($procDataType == 'Delete') {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and PL.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and PL.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					PL.Lpu_id,
					PL.LPU,
					'Delete' as TYPE,
					PL.ObjectID,
					PL.ID
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnDirection ED with(nolock) on ED.EvnDirection_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION_REFERRAL'
					and (
						ED.EvnDirection_id is null
						or ED.EvnDirection_failDT is null
					)
					{$lpuFilter}
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
			}
			$joins = '';
			if ($this->regionNick == 'krym') {
				// https://redmine.swan-it.ru/issues/180591
				// "Написать в комментах в коде номер задачи и примечание, что исключено по просьбе региона" (с) Капгер Анна
				$filters .= '
					and HOSPITALISATION_REFERRAL.ID is not null
				';

				// По задаче PROMEDWEB-4767 - выгружаем только со статусом оплаты ОМС
				$joins = "inner join v_PayType PayT with(nolock) on PayT.PayType_id = ED.PayType_id and PayT.PayType_SysNick = 'oms'";
			}

			$query = "
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as LPU,
					'Insert' as TYPE,
					dL.Lpu_f003mcod as LPU_TO,
					ED.EvnDirection_id as ObjectID,
					isnull(CANCEL_HOSPITALISATION_REFERRAL.ID, newid()) as ID,
					ED.EvnDirection_Num as REFERRAL_NUMBER,
					convert(varchar(10), ED.EvnDirection_setDate, 120) as DATE,
					L.Lpu_f003mcod as REFERRAL_LPU,
					case
						when dbo.getRegion() = 3
						then CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('00' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 2)
						else CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3)
					end as BRANCH,
					case 
						when ESC.EvnStatusCause_Code = 18 then 0
						when ESC.EvnStatusCause_Code = 22 then 1
						when ESC.EvnStatusCause_Code = 1 then 2
						when ESC.EvnStatusCause_Code = 5 then 3
						when ESC.EvnStatusCause_Code = 17 then 5
						else 4
					end REASON,
					2 as CANCEL_SOURSE, -- todo: check
					convert(varchar(10), ED.EvnDirection_failDT, 120) as DATE_CANCEL,
					0 as CANCEL_TYPE,
					null as CANCEL_DESCRIPTION,
					ED.Person_id,
					PATIENT.ID as PATIENT
					-- end select
				from
					-- from
					v_EvnDirection ED with(nolock)
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = isnull(ED.LpuSection_id, ED.LpuSection_did)
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = ED.Lpu_id
					inner join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
					{$joins}
					cross apply (
						select top 1 ESC.*
						from v_EvnStatusCauseLink ESCL with(nolock)
						inner join v_EvnStatusCause ESC with(nolock) on ESC.EvnStatusCause_id = ESCL.EvnStatusCause_id
						where ESCL.DirFailType_id = ED.DirFailType_id
						order by ESC.EvnStatusCause_Code
					) ESC
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						where [AS].AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and ED.EvnDirection_setDate between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, ED.EvnDirection_setDate)
						order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
					) ASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) BuildingCode
					left join {$tmpTable} CANCEL_HOSPITALISATION_REFERRAL on CANCEL_HOSPITALISATION_REFERRAL.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION_REFERRAL' 
						and CANCEL_HOSPITALISATION_REFERRAL.ObjectID = ED.EvnDirection_id
					outer apply (
						select top 1 OSL.Object_Guid as ID
						from v_ObjectSynchronLog OSL with(nolock)
						inner join v_ObjectSynchronLogService OSLS with(nolock) on OSLS.ObjectSynchronLogService_id = OSL.ObjectSynchronLogService_id
						where OSLS.ObjectSynchronLogService_SysNick = 'IszlKrym' 
						and OSL.Object_Name = 'PATIENT' and OSL.Object_id = ED.Person_id
						order by OSL.Object_setDT desc
					) PATIENT
					outer apply (
						select top 1 HOSPITALISATION_REFERRAL.ID
						from {$tmpTable} HOSPITALISATION_REFERRAL (nolock)
						where HOSPITALISATION_REFERRAL.PACKAGE_TYPE = 'HOSPITALISATION_REFERRAL' 
							and HOSPITALISATION_REFERRAL.ObjectID = ED.EvnDirection_id
					) HOSPITALISATION_REFERRAL
					-- end from
				where
					-- where
					ED.DirType_id in (1,5)
					and ED.EvnDirection_failDT is not null
					and CANCEL_HOSPITALISATION_REFERRAL.ID is null
					{$lpuFilter}
					{$filters}
					-- end where
				order by
					-- order by
					L.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Получение данных о госпитализации
	 */
	function package_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and EPS.EvnPS_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and EPS.EvnPS_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
		}

		if ($procDataType == 'Delete') {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and PL.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and PL.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					PL.Lpu_id,
					PL.LPU,
					'Delete' as TYPE,
					PL.ObjectID,
					PL.ID
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'HOSPITALISATION'
					and EPS.EvnPS_id is null
					{$filters_del}
					{$lpuFilter}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
			}
			
			$joins = '';
            if ($this->regionNick == 'krym') {
                $priemFilter = ' exists (
                	select 1 from v_EvnSection ES1
                    where ES1.EvnSection_pid = ES.EvnSection_pid
                    and isnull(ES1.EvnSection_IsPriem, 1) = 1)';

				// По задаче PROMEDWEB-4767 - выгружаем только со статусом оплаты ОМС
				$joins = "inner join v_PayType PayT with(nolock) on PayT.PayType_id = EPS.PayType_id and PayT.PayType_SysNick = 'oms'";
            } else {
                $priemFilter = " LU.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac')";
            }
		
			if($this->regionNick == 'buryatiya') {
				$branch_select = "
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('00' + CAST(LB.LpuBuilding_Code as varchar(10)), 2) as BRANCH,
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000000' + CAST(LS.LpuSection_Code as varchar(10)), 6) as DIVISION,
				";
			} else {
				$branch_select = "
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3) as BRANCH,
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3) + RIGHT('000' + CAST(isnull(SectionCode.Value, LS.LpuSection_Code) as varchar(10)), 3) as DIVISION,
				";
			}

			$query = "
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as LPU,
					ProcDataType.Value as TYPE,
					EPS.EvnPS_id as ObjectID,
					isnull(HOSPITALISATION.ID, newid()) as ID,
					ED.EvnDirection_Num as REFERRAL_NUMBER,
					convert(varchar(10), ED.EvnDirection_setDate, 120) as REFERRAL_DATE,
					rL.Lpu_f003mcod as REFERRAL_MO,
					L.Lpu_f003mcod as MO,
					{$branch_select}
					case 
						when PT.PrehospType_SysNick = 'plan' then 0
						when PT.PrehospType_SysNick in ('extreme','oper') then 1
					end as FORM_MEDICAL_CARE,
					convert(varchar(10), isnull(EPS.EvnPS_setDT, ES.EvnSection_setDT), 120) as HOSPITALISATION_DATE,
					convert(varchar(19), isnull(EPS.EvnPS_setDT, ES.EvnSection_setDT), 120) as HOSPITALISATION_TIME,
					PolT.PolisType_CodeF008 as POLICY_TYPE,
					Polis.Polis_Ser as POLIS_SERIAL,
					Polis.Polis_Num as POLIS_NUMBER,
					SMO.Orgsmo_f002smocod as SMO,
					rtrim(PS.Person_FirName) as FIRST_NAME,
					rtrim(PS.Person_SurName) as LAST_NAME,
					rtrim(PS.Person_SecName) as FATHER_NAME,
					case 
						when S.Sex_Code in (1,3) then '10301'
						when S.Sex_Code in (2) then '10302'
					end as SEX,
					convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
					case 
						when LU.LpuUnitType_SysNick = 'stac'
						then 1 else 2
					end as USL_OK,
					LSBP.LpuSectionBedProfile_Code as V020_STRUCTURE_BED,
					LSP.LpuSectionProfile_Code as STRUCTURE_BED,
					EPS.EvnPS_NumCard as MED_CARD_NUMBER,
					D.Diag_Code as MKB,
					D.Diag_Name as DIAGNOSIS,
					PS.Person_id,
					PATIENT.ID as PATIENT
					-- end select
				from
					-- from
					v_EvnPS EPS with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = EPS.Lpu_id
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPS.LpuSection_id
					inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
					inner join v_Diag D with(nolock) on D.Diag_id = EPS.Diag_id
					inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = EPS.PersonEvn_id and PS.Server_id = EPS.Server_id
					inner join v_Sex S with(nolock) on S.Sex_id = PS.Sex_id
					{$joins}
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PolT with(nolock) on PolT.PolisType_id = Polis.PolisType_id
					left join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = Polis.OrgSmo_id
					left join v_EvnDirection ED with(nolock) on ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_Lpu rL with(nolock) on rL.Lpu_id = ED.Lpu_id
					outer apply (
						select top 1 ES.*
						from v_EvnSection ES with(nolock)
						where ES.EvnSection_pid = EPS.EvnPS_id
						order by ES.EvnSection_setDT
					) ES
					outer apply (
						select top 1 LSBP.*
						from fed.v_LpuSectionBedProfileLink LSBPL with(nolock)
						inner join fed.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
						where LSBPL.LpuSectionBedProfile_id = ES.LpuSectionBedProfile_id
					) LSBP
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						where [AS].AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and EPS.EvnPS_setDate between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, EPS.EvnPS_setDate)
						order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
					) ASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Section_Code'
					) SectionCode
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) BuildingCode
					inner join {$tmpTable} DIVISION on DIVISION.PACKAGE_TYPE = 'DIVISION' and DIVISION.ObjectID = LS.LpuSection_id
					left join {$tmpTable} HOSPITALISATION on HOSPITALISATION.PACKAGE_TYPE = 'HOSPITALISATION' and HOSPITALISATION.ObjectID = EPS.EvnPS_id
					outer apply(
						select case
							when HOSPITALISATION.ID is null then 'Insert'
							when HOSPITALISATION.ID is not null and HOSPITALISATION.DATE <= EPS.EvnPS_updDT then 'Update'
						end as Value
					) ProcDataType
					outer apply (
						select top 1 OSL.Object_Guid as ID
						from v_ObjectSynchronLog OSL with(nolock)
						inner join v_ObjectSynchronLogService OSLS with(nolock) on OSLS.ObjectSynchronLogService_id = OSL.ObjectSynchronLogService_id
						where OSLS.ObjectSynchronLogService_SysNick = 'IszlKrym' 
						and OSL.Object_Name = 'PATIENT' and OSL.Object_id = PS.Person_id
						order by OSL.Object_setDT desc
					) PATIENT
					-- end from
				where
					-- where
					{$priemFilter}
					{$lpuFilter}
					{$filters}
					and ProcDataType.Value = :ProcDataType
					-- end where
				order by
					-- order by
					L.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Получение данных о движении при госпитализации
	 */
	function package_MOTION_IN_HOSPITAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($this->init_date)) {
			$filters .= " and ES.EvnSection_insDT >= :InitDate";
			$params['InitDate'] = $this->init_date;
		}
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ES.EvnSection_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		}

		if ($procDataType == 'Delete') {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and PL.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and PL.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					PL.Lpu_id,				
					PL.LPU,
					'Delete' as TYPE,
					PL.ObjectID,
					PL.ID
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnSection ES with(nolock) on ES.EvnSection_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'MOTION_IN_HOSPITAL'
					and ES.EvnSection_id is null
					{$lpuFilter}
                    {$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
			}

			if ($procDataType == 'Insert') {
				$filters .= " and not exists(
					select * from {$tmpTable} 
					where ObjectID = ES.EvnSection_id and PACKAGE_TYPE = 'MOTION_IN_HOSPITAL'
				)";
			} else {
				$filters .= " and exists(
					select * from {$tmpTable} 
					where ObjectID = ES.EvnSection_id and PACKAGE_TYPE = 'MOTION_IN_HOSPITAL' and DATE <= ES.EvnSection_updDT
				)";
			}
			$joins = '';
            if ($this->regionNick == 'krym') {
                $priemFilter = " isnull(ES.EvnSection_IsPriem, 1) = 1";
                
				// По задаче PROMEDWEB-4767 - выгружаем только со статусом оплаты ОМС
				$joins = "inner join v_PayType PayT with(nolock) on PayT.PayType_id = ES.PayType_id and PayT.PayType_SysNick = 'oms'";
            } else {
                $priemFilter = " LU.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac')";
            }
		
			if($this->regionNick == 'buryatiya') {
				$branch_select = "
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('00' + CAST(LB.LpuBuilding_Code as varchar(10)), 2) as BRANCH,
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000000' + CAST(LS.LpuSection_Code as varchar(10)), 6) as DIVISION,
				";
			} else {
				$branch_select = "
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3) as BRANCH,
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3) + RIGHT('000' + CAST(isnull(SectionCode.Value, LS.LpuSection_Code) as varchar(10)), 3) as DIVISION,
				";
			}
			
			$query = "
				select
					-- select
					L.Lpu_id,				
					L.Lpu_f003mcod as LPU,
					'{$procDataType}' as TYPE,
					ES.EvnSection_id as ObjectID,
					isnull(MOTION_IN_HOSPITAL.ID, newid()) as ID,
					HOSPITALISATION.ID as HOSPITALISATION_ID,
					{$branch_select}
					LSBP.LpuSectionBedProfile_Code as V020_STRUCTURE_BED,
					LSP.LpuSectionProfile_Code as STRUCTURE_BED,
					convert(varchar(10), ES.EvnSection_setDT, 120) as DATE_IN,
					convert(varchar(10), ES.EvnSection_disDT, 120) as DATE_OUT,
					EPS.EvnPS_NumCard as MED_CARD_NUMBER,
					convert(varchar(10), EPS.EvnPS_setDT, 120) as HOSPITALISATION_DATE,
					case 
						when LU.LpuUnitType_SysNick = 'stac'
						then 1 else 2
					end as USL_OK,
					case 
						when RD.ResultDesease_SysNick in ('kszdor','ksuluc','dszdor','dsuluc','rem') then 1
						when RD.ResultDesease_SysNick in ('ksbper','dsbper','noteff') then 2
						when RD.ResultDesease_SysNick in ('ksuchud','dsuchud') then 3
					end as OUTCOME
					-- end select
				from
					-- from
					v_EvnSection ES with(nolock)
					inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid
					inner join v_Lpu L with(nolock) on L.Lpu_id = ES.Lpu_id
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
					inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					{$joins}
					outer apply (
						select top 1 LSBP.*
						from fed.v_LpuSectionBedProfileLink LSBPL with(nolock)
						inner join fed.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
						where LSBPL.LpuSectionBedProfile_id = ES.LpuSectionBedProfile_id
					) LSBP
					left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ES.EvnSection_id
					left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ES.EvnSection_id
					left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ES.EvnSection_id
					left join v_EvnOtherSectionBedProfile EOSBP with (nolock) on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
					left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ES.EvnSection_id
					left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ES.EvnSection_id
					left join v_ResultDesease RD with(nolock) on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id)
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						where [AS].AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and ES.EvnSection_setDate between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, ES.EvnSection_setDate)
						order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
					) ASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Section_Code'
					) SectionCode
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) BuildingCode
					inner join {$tmpTable} DIVISION on DIVISION.PACKAGE_TYPE = 'DIVISION' and DIVISION.ObjectID = LS.LpuSection_id
					inner join {$tmpTable} HOSPITALISATION on HOSPITALISATION.PACKAGE_TYPE = 'HOSPITALISATION' and HOSPITALISATION.ObjectID = EPS.EvnPS_id
					left join {$tmpTable} MOTION_IN_HOSPITAL on MOTION_IN_HOSPITAL.PACKAGE_TYPE = 'MOTION_IN_HOSPITAL' and MOTION_IN_HOSPITAL.ObjectID = ES.EvnSection_id
					-- end from
				where
					-- where
                    {$priemFilter}
					{$lpuFilter}
					{$filters}
					-- end where
				order by
					-- order by
					L.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Получение данных об отмене госпитализации
	 */
	function package_CANCEL_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array();

		$filters = "";
		$filters_del = "";
		if (!empty($this->init_date)) {
			$filters .= " and ES.EvnSection_insDT >= :InitDate";
			$params['InitDate'] = $this->init_date;
		}
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and EPS.EvnPS_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		}
        

		if ($procDataType == 'Delete') {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and PL.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and PL.Lpu_id in ({$allowed_lpus_str})";
			}

			$query = "
				select
					-- select
					PL.Lpu_id,				
					PL.LPU,
					'Delete' as TYPE,
					PL.ObjectID,
					PL.ID
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnSection ES with(nolock) on ES.EvnSection_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION'
					and ES.EvnSection_id is null
					{$filters_del}
					{$lpuFilter}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$lpuFilter = '';
			if (!empty($this->LpuIdFilter)) {
				$lpuFilter = "and L.Lpu_id = :LpuIdFilter";
				$params['LpuIdFilter'] = $this->LpuIdFilter;
			}
			if (!empty($this->allowed_lpus)) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$lpuFilter = "and L.Lpu_id in ({$allowed_lpus_str})";
			}

			$joins = '';
			if ($this->regionNick == 'krym') {
				// По задаче PROMEDWEB-4767 - выгружаем только со статусом оплаты ОМС
				$joins = "inner join v_PayType PayT with(nolock) on PayT.PayType_id = ES.PayType_id and PayT.PayType_SysNick = 'oms'";
			}

			$query = "
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as LPU,
					'Insert' as TYPE,
					ES.EvnSection_id as ObjectID,
					newid() as ID,
					convert(varchar(10), EPS.EvnPS_setDT, 120) as DATE,
					HOSPITALISATION.ID as HOSPITALISATION_ID,
					case
						when dbo.getRegion() = 3
						then CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('00' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 2)
						else CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3)
					end as BRANCH,
					CancelReason.Value as REASON,
					1 as CANCEL_SOURSE,
					case when EPS.PrehospWaifRefuseCause_id is not null 
						then convert(varchar(10), EPS.EvnPS_OutcomeDT, 120)
						else convert(varchar(10), ES.EvnSection_disDT, 120)
					end as DATE_CANCEL,
					1 as CANCEL_TYPE,
					CAST(L.Lpu_f003mcod as varchar(10)) + RIGHT('000' + CAST(isnull(BuildingCode.Value, LB.LpuBuilding_Code) as varchar(10)), 3) + RIGHT('000' + CAST(isnull(SectionCode.Value, LS.LpuSection_Code) as varchar(10)), 3) as HOSPITALISATION_DIVISION,
					EPS.EvnPS_NumCard as MED_CARD_NUMBER,
					ES.Person_id,
					PATIENT.ID as PATIENT
					-- end select
				from
					-- from
					v_EvnSection ES with(nolock)
					inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid
					inner join v_Lpu L with(nolock) on L.Lpu_id = ES.Lpu_id
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					{$joins}
					left join v_EvnLeave EL with(nolock) on EL.EvnLeave_pid = ES.EvnSection_id
					left join v_ResultDesease RD with(nolock) on RD.ResultDesease_id = EL.ResultDesease_id
					left join v_PrehospWaifRefuseCause PWRC with(nolock) on PWRC.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
					outer apply(
						select case
							when PWRC.PrehospWaifRefuseCause_Code = 11 then 0
							when RD.ResultDesease_Code = 202 then 5
							when PWRC.PrehospWaifRefuseCause_Code = 2 then 2
							when PWRC.PrehospWaifRefuseCause_Code = 9 then 1
							when PWRC.PrehospWaifRefuseCause_Code = 10 or RD.ResultDesease_Code in (205,206) then 3
							when PWRC.PrehospWaifRefuseCause_Code not in (2,9,10) then 4
						end as Value
					) CancelReason
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						where [AS].AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and ES.EvnSection_setDate between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, ES.EvnSection_setDate)
						order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
					) ASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Section_Code'
					) SectionCode
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) BuildingCode
					inner join {$tmpTable} DIVISION on DIVISION.PACKAGE_TYPE = 'DIVISION' and DIVISION.ObjectID = LS.LpuSection_id
					inner join {$tmpTable} HOSPITALISATION on HOSPITALISATION.PACKAGE_TYPE = 'HOSPITALISATION' and HOSPITALISATION.ObjectID = EPS.EvnPS_id
					outer apply (
						select top 1 OSL.Object_Guid as ID
						from v_ObjectSynchronLog OSL with(nolock)
						inner join v_ObjectSynchronLogService OSLS with(nolock) on OSLS.ObjectSynchronLogService_id = OSL.ObjectSynchronLogService_id
						where OSLS.ObjectSynchronLogService_SysNick = 'IszlKrym' 
						and OSL.Object_Name = 'PATIENT' and OSL.Object_id = ES.Person_id
						order by OSL.Object_setDT desc
					) PATIENT
					-- end from
				where
					-- where
					LU.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac')
					{$lpuFilter}
					and CancelReason.Value is not null
					and not exists(
						select * from {$tmpTable}
						where ObjectID = ES.EvnSection_id and PACKAGE_TYPE = 'CANCEL_HOSPITALISATION'
					)
					{$filters}
					-- end where
				order by
					-- order by
					L.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}

	/**
	 * Разрыв соединения c клиентом после запуска импорта
	 */
	function sendImportResponse() {
		ignore_user_abort(true);
		ob_start();
		echo json_encode(array("success" => "true"));

		$size = ob_get_length();

		header("Content-Length: $size");
		header("Content-Encoding: none");
		header("Connection: close");

		ob_end_flush();
		ob_flush();
		flush();

		if (session_id()) session_write_close();
	}

	/**
	 * Получение порядка обработки данных
	 * packages - массив пакетов которые должны уйти в очередь
	 * related - массив пакетов по которым должна формироваться временная таблица
	 */
	function getProcConfig() {
		$procConfig = array(
			'Insert' => array(
				'BRANCH' => 'BRANCH',
				'DIVISION' =>array(
					'packages' => array(
						'DIVISION',
					),
					'related'=> array(
						'BRANCH',
						'DIVISION'
					)
				),
				'BED' => array(
					'packages'=> array(
						'DIVISION_LINK_STRUCTURE_BED',
						'AMOUNT_BED',
						'FREE_BEDS_INFORMATION'
					),
					'related'=> array(
						'DIVISION_LINK_STRUCTURE_BED',
						'AMOUNT_BED',
						'FREE_BEDS_INFORMATION',
						'DIVISION'
					),
				),
				'HOSPITALISATION_REFERRAL' => 'HOSPITALISATION_REFERRAL',
				'CANCEL_HOSPITALISATION_REFERRAL' => 'CANCEL_HOSPITALISATION_REFERRAL',
				'HOSPITALISATION' => array(
					'packages'=> array(
						'HOSPITALISATION',
					),
					'related'=> array(
						'DIVISION',
						'HOSPITALISATION'
					),
				),
				'MOTION_IN_HOSPITAL' => array(
					'packages'=> array(
						'MOTION_IN_HOSPITAL',
					),
					'related'=> array(
						'DIVISION',
						'HOSPITALISATION',
						'MOTION_IN_HOSPITAL'
					)
				),
				'CANCEL_HOSPITALISATION' => array(
					'packages'=> array(
						'CANCEL_HOSPITALISATION',
					),
					'related'=> array(
						'DIVISION',
						'HOSPITALISATION',
						'CANCEL_HOSPITALISATION'
					)
				)
			),
			'Update' => array(),
			'Delete' => array(),
		);

		if ($this->regionNick == 'krym') {
			foreach (array('packages','related') as $value) {
				$key = array_search('DIVISION_LINK_STRUCTURE_BED', $procConfig['Insert']['BED'][$value]);
				if ($key !== false) {
					unset($procConfig['Insert']['BED'][$value][$key]);
					$procConfig['Insert']['BED'][$value] = array_values($procConfig['Insert']['BED'][$value]);
				}
			}
		}

		$procConfig['Update'] = $procConfig['Insert'];
		$procConfig['Delete'] = array_reverse($procConfig['Insert']);

		return $procConfig;
	}

	/**
	 * Получение карты объектов
	 */
	function getObjectMap() {
		return array(
			'BRANCH' => 'LpuBuilding',
			'DIVISION' => 'LpuSection',
			'BED' => 'LpuSectionBedState',
			'HOSPITALISATION_REFERRAL' => 'EvnDirection',
			'CANCEL_HOSPITALISATION_REFERRAL' => 'EvnDirection',
			'HOSPITALISATION' => 'EvnPS',
			'MOTION_IN_HOSPITAL' => 'EvnSection',
			'CANCEL_HOSPITALISATION' => 'EvnSection',
		);
	}

	/**
	 * Запуск отправки данных в очередь RabbitMQ
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function runPublisher($data) {
		set_time_limit(0);

		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';
		}

		$objectMap = $this->getObjectMap();
		$patients = array();

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$allowedPackageTypes = null;
		if (!empty($data['packageType'])) {
			$allowedPackageTypes = explode('|', $data['packageType']);
		}
		$allowedProcDataTypes = null;
		if (!empty($data['procDataType'])) {
			$allowedProcDataTypes = explode('|', $data['procDataType']);
		}
		$packageLimit = null;
		if (!empty($data['packageLimit'])) {
			$packageLimit = $data['packageLimit'];
		}
		if (!empty($data['exportId']) && !is_array($data['exportId'])) {
			$data['exportId'] = array($data['exportId']);
		}

		$processPackageTypes = function($packageTypeParent, $packageTypes) use($allowedPackageTypes) {
			if (!is_array($packageTypes)) $packageTypes = array($packageTypes);
			if (!$allowedPackageTypes) return $packageTypes;

			if(in_array($packageTypeParent, $allowedPackageTypes))	return $packageTypes;

			return array_filter($packageTypes, function($packageType) use($allowedPackageTypes) {
				return in_array($packageType, $allowedPackageTypes);
			});
		};

		$procConfig = $this->getProcConfig();
		$package_types = [];
		foreach($procConfig['Insert'] as $object => $packageTypes) {
			$related = $packages = [];
			if (is_array($packageTypes)) {
				$related = isset($packageTypes['related']) && count($packageTypes['related'])>0?$packageTypes['related']:$packageTypes['packages'];
				$packages = $packageTypes['packages'];
			} else {
				$related = $packages = [$packageTypes];
			}
			$bool = false;
			if(is_array($allowedPackageTypes)) {
				$filtered = array_filter($packages, function($pack) use($allowedPackageTypes) {
					return in_array($pack, $allowedPackageTypes);
				});
				$bool = !empty($filtered) || in_array($object, $allowedPackageTypes);
			}

			if( $bool || !is_array($allowedPackageTypes)){
				$package_types = array_merge($package_types, $related);
			}
		}

		$packageTypesInsert = array_unique($package_types);

		$startDT = date_create();

		$this->load->library('textlog', array('file' => 'ServiceISZL_'.$startDT->format('Y-m-d').'.log'));

		$log = new ServiceListLog($this->ServiceList_id, $pmUser_id);

		$resp = $log->start();
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		if (empty($_REQUEST['getDebug'])) {
			$this->sendImportResponse();
		}

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			$tmpTable = $this->createActualPackageTable();
			$this->fillActualPackagesTable($data, $tmpTable, $packageTypesInsert);

			foreach ($this->getProcConfig() as $procDataType => $packagesOrder) {
				if ($allowedProcDataTypes && !in_array($procDataType, $allowedProcDataTypes)) {
					continue;
				}
				foreach($packagesOrder as $object => $packageTypes) {
					$packageTypes = is_array($packageTypes) && isset($packageTypes['packages'])? $packageTypes['packages']: array($packageTypes);
					$packageTypes = $processPackageTypes($object, $packageTypes);
					
					if (count($packageTypes) == 0) continue;

					$method = 'package_'.$object;
					$processCount = 0;
					$start = 0;
					$limit = 500;

					if ($packageLimit && $limit > $packageLimit) {
						$limit = $packageLimit;
					}

					$packageData = $this->$method($tmpTable, $procDataType, $data, 'data', $start, $limit);
					$processCount += count($packageData);
					if ($processCount > 0) {
						foreach ($packageTypes as $packageType) {
							foreach ($packageData as $package) {
								$package['MESSAGE_ID'] = $this->GUID();
								
								list($requestQueue, $channel) = $this->getQueue('publisher', 'common', $packageType);
								
								$body = $this->createPackageBody($packageType, $package);
								
								if ($this->isNotSendPackage($packageType, $procDataType, $package['ObjectID'], $body, $log)) {
									continue;
								}
								
								$properties = $this->createPackageProperties($packageType, $package['MESSAGE_ID'], $this->reply_queue);
								
								$errors = $this->checkPackage($packageType, $body);
								
								if (array_key_exists('PATIENT', $package) && empty($package['PATIENT']) && !empty($package['Person_id'])) {
									$key = $package['Person_id'];
									if (!isset($patients[$key])) {
										$patients[$key] = $this->GUID();
										$this->ObjectSynchronLog_model->saveObjectSynchronLog('PATIENT', $key, $patients[$key]);
									}
									$package['PATIENT'] = $patients[$key];
								}
								
								$resp = $log->addPackage($objectMap[$object], $package['ObjectID'], $package['MESSAGE_ID'], $package['Lpu_id'], $packageType, $procDataType, $body);
								if (!$this->isSuccessful($resp)) {
									throw new Exception($resp[0]['Error_Msg']);
								}
								$packageId = $resp[0]['ServiceListPackage_id'];
								
								if (count($errors) > 0) {
									$this->textlog->add(['properties' => $properties, 'body' => $body, 'errors' => $errors]);
									$log->setPackageStatus($packageId, 'ErrFormed');
									$log->add(false, array_merge(["Пакет не сформирован:"], $errors), $packageId);
									continue;
								}
								
								$log->setPackageStatus($packageId, 'Formed');
								
								try {
									$this->beginTransaction();
									
									if ($this->allowSaveGUID) {
										$this->ObjectSynchronLog_model->saveObjectSynchronLog($packageType, $package['ObjectID'], $package['ID']);
									}
									
									$this->textlog->add(array('properties' => $properties, 'body' => $body));
									
									$msg = new AMQPMessage($body, $properties);
									$channel->basic_publish($msg, '', $requestQueue);
									
									$log->setPackageStatus($packageId, 'Sent');
									$this->commitTransaction();
									
									$diff = date_diff($startDT, date_create());
									
									if (!empty($this->minutesLimit) && $diff->i >= $this->minutesLimit) {
										//заверщение выгрузки при привышении лимита времени
										$this->closeConnections();
										$log->finish(true);
										
										restore_exception_handler();
										
										return [['success' => true, 'ServiceListLog_id' => $log->getId()]];
									}
								} catch(Exception $e) {
									$this->rollbackTransaction();
									$log->setPackageStatus($packageId, 'ErrSent');
									$log->add(false, ["Ошибка отпавки пакета:", $e->getMessage()], $packageId);
								}
							}
						}
						
						$this->fillActualPackagesTable($data, $tmpTable, $packageTypes);
					}
				}
			}

			$this->closeConnections();

			$log->finish(true);

			restore_exception_handler();
		} catch (Exception $e) {
			restore_exception_handler();

			$this->closeConnections();

			$code = $e->getCode();
			$error = $e->getMessage();

			$this->textlog->add($error);

			$resp = $log->addPackage('DummyPackage', null);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$log->add(false, array("Импорт данных завершён с ошибкой:", $error), $resp[0]['ServiceListPackage_id']);
			$log->setPackageStatus($resp[0]['ServiceListPackage_id'], 'ErrFormed');
			$log->finish(false);

			$response = $this->createError($code, $error);
			$response[0]['ServiceListLog_id'] = $log->getId();
			$response[0]['address'] = $this->host.':'.$this->port;
			$response[0]['queue'] = $this->request_queue;

			return $response;
		}

		return [['success' => true, 'ServiceListLog_id' => $log->getId()]];
	}

	/**
	 * Запуск получения ответов из очереди RabbitMQ
	 * @param array $data
	 * @return array
	 */
	function runConsumer($data) {
		set_time_limit(0);

		if (empty($_REQUEST['getDebug'])) {
			$this->sendImportResponse();
		}
		
		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$this->load->library('textlog', array(
			'file' => 'ServiceISZL_'.date('Y-m-d').'.log',
			'format' => 'json',
			'parse_xml' => true
		));
		
		$log = new ServiceListLog($this->ServiceList_id, $pmUser_id);
		$resp = $log->start();
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$timeout = !empty($data['timeout'])?$data['timeout']:$this->consumer_timeout;

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			list($replyQueue, $channel, $connection) = $this->getQueue('consumer', 'answer');

			$channel->basic_qos(null, 1, null);
			$channel->basic_consume($replyQueue, '', false, false, false, false, function($msg) use($data, $log) {
				$this->consumerCallback($msg, $data['pmUser_id'], $log);
			});

			$start = date_create();
			while(count($channel->callbacks)) {
				$minutes_from_start = date_diff($start, date_create())->i;
				if ($minutes_from_start >= $timeout) {
					throw new Exception('timeout');
				}

				try {
					//Ожидание одного сообщения. Не больше минуты.
					$channel->wait(null, false, 5);
				} catch (AMQPTimeoutException $e) {
					break;
				}
			}

			$this->closeConnections();
			$log->finish(true);

			restore_exception_handler();
		} catch (Exception $e) {
			restore_exception_handler();

			$this->closeConnections();

			if ($e->getMessage() == 'timeout') {
				$response = array(array('success' => true, 'timeout' => true));
			} else {
				$response = $this->createError($e->getCode(), $e->getMessage());
				$response[0]['address'] = $this->host.':'.$this->port;
				$response[0]['queue'] = $this->request_queue;
				$resp = $log->addPackage('DummyPackage', null);
				if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
				$log->add(false, array("Импорт данных завершён с ошибкой:", $e->getMessage()), $resp[0]['ServiceListPackage_id']);
				$log->finish(false);
				
			}
			return $response;
		}

		return array(array(
			'success' => true,
			'ServiceListLog_id' => $log->getId())
		);
	}

	/**
	 * Обработка ответа
	 * @param AMQPMessage $msg
	 * @param int $pmUser_id
	 * @throws Exception
	 */
	function consumerCallback($msg, $pmUser_id, $log) {
		$properties = $msg->get_properties();
		$channel = $msg->delivery_info['channel'];
		$delivery_tag = $msg->delivery_info['delivery_tag'];
		$body = trim($msg->getBody());

		$this->textlog->add(array('properties' => $properties, 'body' => $body));

		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r(array('properties' => $properties, 'body' => htmlentities($body)));
		}
		
		if ($properties['type'] == 'HOSPITALISATION_REFERRAL') {
			$type = $properties['type'];
			$msgBody = XmlToArray($body);
			$procDataType = $msgBody[$type]['header']['TYPE'] ?? null;
			$message_id = $msgBody['header']['ID'] ?? null;
			$result = $msgBody['body']['RESULT'] ?? null;
			$error = $msgBody['body']['DESCRIPTION'] ?? null;
			/*if ($procDataType == 'reserve' && $result == 2) {
				$error = $msgBody['body']['DESCRIPTION'];

				$params = array(
					'ServiceListDetailLog_id' => null,
					'ServiceListLog_id' => null,
					'ServiceListPackage_id' => null,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => $error,
					'pmUser_id' => $pmUser_id,
				);

				$tmp = $this->getFirstRowFromQuery("
					select top 1 
						SLP.ServiceListPackage_id.
						SLP.ServiceListLog_id
					from 
						stg.v_ServiceListPackage SLP with(nolock)
						inner join stg.ServiceListLog SLL with(nolock) on SLL.ServiceListLog_id = SLP.ServiceListLog_id
					where 
						SLL.ServiceList_id = :ServiceList_id
						and SLP.ServiceListPackage_GUID = :ServiceListPackage_GUID
				", array(
					'ServiceListPackage_GUID' => $message_id,
					'ServiceList_id' => $this->ServiceList_id
				));

				if (is_array($tmp)) {
					$params = array_merge($params, $tmp);
					$resp = $this->ServiceList_model->saveServiceListDetailLog($params);
					if ($this->isSuccessful($resp)) return;
				}
			}*/
		} else if ($properties['content_type'] == 'Xml' && strpos($properties['type'], 'ERROR') !== false) {
			$reply_xml = objectToArray(simplexml_load_string($body));
			$request_xml = objectToArray(simplexml_load_string(base64_decode($reply_xml['body'])));

			$error = $reply_xml['header']['DESCRIPTION'] ?? null;
			$message_id = $request_xml['header']['ID'] ?? null;

			/*$params = array(
				'ServiceListDetailLog_id' => null,
				'ServiceListLog_id' => null,
				'ServiceListPackage_id' => null,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $error,
				'pmUser_id' => $pmUser_id,
			);

			$tmp = $this->getFirstRowFromQuery("
				select top 1 
					SLP.ServiceListPackage_id, 
					SLP.ServiceListLog_id
				from 
					stg.v_ServiceListPackage SLP with(nolock)
					inner join stg.ServiceListLog SLL with(nolock) on SLL.ServiceListLog_id = SLP.ServiceListLog_id
				where 
					SLL.ServiceList_id = :ServiceList_id
					and SLP.ServiceListPackage_GUID = :ServiceListPackage_GUID
			", array(
				'ServiceListPackage_GUID' => $message_id,
				'ServiceList_id' => $this->ServiceList_id
			));

			if (is_array($tmp)) {
				$params = array_merge($params, $tmp);

				$resp = $this->ServiceList_model->saveServiceListDetailLog($params);
				if ($this->isSuccessful($resp)) return;
			}*/
		}

		$channel->basic_ack($delivery_tag);	//Подтверждение приема сообщения
		if(empty($message_id)) return;
		
		$params = array(
			'ServiceList_id' => $this->ServiceList_id,
			//'ServiceListPackage_ObjectName' => $objectMap[$packageType],
			'ServiceListPackage_GUID' => $message_id,
		);
		$query = "
			select top 1
				SLP.ServiceListPackage_id
			from stg.v_ServiceListPackage SLP with(nolock)
			inner join stg.v_ServiceListLog SLL with(nolock) on SLL.ServiceListLog_id = SLP.ServiceListLog_id
			where SLL.ServiceList_id = :ServiceList_id
			and SLP.ServiceListPackage_ObjectName like :ServiceListPackage_ObjectName
			and SLP.ServiceListPackage_GUID = :ServiceListPackage_GUID
		";
		$resp = $this->getFirstRowFromQuery($query, $params, true);
		if (!is_array($resp)) {
			$resp = $log->addPackage('DummyPackage', null, $message_id);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$log->add(false, 'Не найден идентификатор объекта из ответа', $resp[0]['ServiceListPackage_id']);
			return;
		}
		$packageId = $resp['ServiceListPackage_id'];

		$resp = $log->addPackageData($packageId, $error);
		if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
		
	}
}
