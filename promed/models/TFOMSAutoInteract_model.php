<?php
defined('BASEPATH') or die ('No direct script access allowed');

require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 * TFOMSAutoInteract_model - модель для автоматического взаимодействия с ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			TFOMSAutoInteract
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan-it.ru)
 * @version			11.2018
 *
 * @property ServiceListLog $log
 * @property ObjectSynchronLog_model $sync
 */
class TFOMSAutoInteract_model extends swModel {
	protected $host;
	protected $port;
	protected $user;
	protected $password;
	protected $publisher_queues;
	protected $consumer_queues;
	protected $init_date;
	protected $read_only = false;
	protected $allowed_lpus = array();
	protected $ServiceList_id;
	protected $ServiceList_SysNick;

	protected $log;

	protected $schemas = array();
	protected $connections = array();
	protected $channels = array();
	protected $queues = array();
	protected $allowSaveGUID = false;
	protected $errorIndex = -1;

	public $allowedServices = array(
		'TFOMSAutoInteract',
		'ExchInspectPlan',
	);

	protected $validateOnRegions = array(
		'perm',
		'kareliya',
	);

	protected $packagesTableStruct = "
		Lpu_id bigint,
		LPU int,
		ObjectID bigint,
		ID bigint,
		GUID uniqueidentifier,
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

		$this->load->model('ObjectSynchronLog_model', 'sync');
		$this->sync->setServiceSysNick('TFOMSAutoInteract');
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
	function showServiceConfig($name) {
		if (in_array($name, $this->allowedServices)) {
			$this->showConfig($this->config->item($name));
		}
	}

	/**
	 * @param string $sysNick
	 */
	function setService($name) {
		if (!in_array($name, $this->allowedServices)) {
			throw new Exception("Не доступен сервис {$name}");
		}

		$this->ServiceList_id = $this->ServiceList_model->getServiceListId($name);
		$this->ServiceList_SysNick = $name;

		$config = $this->config->item($name);
		if (!empty($_REQUEST['showConfig'])) {
			$this->showConfig($config);
		}
		if (!is_array($config)) {
			throw new Exception("Не найден конфиг {$name}");
		}
		if (!isset($config['publisher_queues']) || !is_array($config['publisher_queues'])) {
			throw new Exception("Не настроен список очередей для экспорта данных");
		}
		if (!isset($config['consumer_queues']) || !is_array($config['consumer_queues'])) {
			throw new Exception("Не настроен список очередей для импорта данных");
		}

		$this->host = $config['host'];
		$this->port = $config['port'];
		$this->user = $config['user'];
		$this->password = $config['password'];
		$this->publisher_queues = $config['publisher_queues'];
		$this->consumer_queues = $config['consumer_queues'];
		$this->init_date = !empty($config['init_date'])?$config['init_date']:null;
		$this->read_only = !empty($config['read_only'])?$config['read_only']:false;
		$this->allowed_lpus = !empty($config['allowed_lpus'])?$config['allowed_lpus']:array();
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
	 * @param array $arr
	 * @return array
	 */
	function convertDates($arr) {
		$formatList = array(
			'/^\d{2}\.\d{2}\.\d{4}$/',
			'/^\d{4}-\d{2}-\d{2}$/',
			'/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/',
		);
		array_walk_recursive($arr, function(&$var, $key) use($formatList) {
			$isDate = false;
			foreach($formatList as $format) {
				if (preg_match($format, $var)) {
					$isDate = true;break;
				}
			}
			if ($isDate && $tmp = ConvertDateFormat($var, 'Y-m-d')) {
				$var = $tmp;
			}
		});
		return $arr;
	}

	/**
	 * @param string $serviceMode
	 * @param string $listName
	 * @param string $packageType
	 * @return string
	 * @throws Exception
	 */
	function getQueueName($serviceMode, $listName, $packageType = 'default') {
		$_packageType = $this->packageTypeMapper($packageType);
		$queuesCollection = $serviceMode.'_queues';
		$queues = $this->$queuesCollection[$listName];
		$queueName = null;

		if (is_string($queues)) {
			$queueName = $queues;
		} else if (is_array($queues)) {
			if (!empty($queues[$_packageType])) {
				$queueName = $queues[$_packageType];
			} else if (!empty($queues[$packageType])) {
				$queueName = $queues[$packageType];
			} else if (!empty($queues['default'])) {
				$queueName = $queues['default'];
			}
		}

		if (empty($queueName)) {
			throw new Exception(trim("Не найдена очередь для {$serviceMode} {$listName} {$_packageType}"));
		}

		return $queueName;
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
			$queue = $this->getQueueName($serviceMode, $listName, $packageType);
			$queueName = is_array($queue) ? $queue['name'] : $queue;
			$channel = $this->getChannel($queue, $serviceMode, $listName, $packageType);
			list($queue,,) = $channel->queue_declare($queueName, false, true, false, false);
			$this->queues[$key] = $queue;
		} else {
			$channel = $this->getChannel($this->queues[$key], $serviceMode, $listName, $packageType);
		}

		return array($this->queues[$key], $channel);
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
		
		return $this->channels[$key];
	}

	/**
	 * 
	 */
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
	 * @param array $data
	 * @return array
	 */
	function runService($data) {
		set_time_limit(0);

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$this->setService('TFOMSAutoInteract');

		$log = new ServiceListLog($this->ServiceList_id, $pmUser_id);

		$log->start();

		if (empty($_REQUEST['getDebug'])) {
			$this->sendImportResponse();
		}

		$result = $this->runPublisher($data, $log);
		if (!$this->isSuccessful($result)) {
			return $result;
		}

		$result = $this->runConsumer($data, 'common', $log);
		if (!$this->isSuccessful($result)) {
			return $result;
		}

		$result = $this->runConsumer($data, 'answer', $log);
		if (!$this->isSuccessful($result)) {
			return $result;
		}

		$log->finish(true);

		return array(array(
			'success' => true,
			'ServiceList_id' => $log->getId()
		));
	}

	/**
	 * @param array $data
	 * @param array $fields
	 * @return array
	 */
	function getParams($data, $fields) {
		$params = array();
		foreach($fields as $field) {
			if (empty($data[$field])) {
				$params[$field] = null;
			} else {
				$params[$field] = $data[$field];
			}
		}
		return $params;
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
		return array(
			'ANSWER' => array(
				'PERSONID' => 'PERSON_ID',
				'BDZID' => 'BDZ_ID',
			)
		);
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
		$item['isTFOMSAutoInteract'] = true;
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
		if ($_packageType == 'ATTACH_DATA' && $this->regionNick == 'kareliya') {
			$app_id = 'TfomsToPromed';
		}
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
				create table {$tmpTable} ({$this->packagesTableStruct});
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
	 * @param $packageTypesCur
	 * @param string|array|null $packageTypes
	 * @throws Exception
	 */
	function fillActualPackagesTable($data, $tmpTable, $packageTypesCur, $packageTypes = null) {
		$package_types = array();
		$params = array(
			'syncService' => $this->sync->getServiceSysNick()
		);

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

		$package_types_str = $this->getPackageTypes($package_types, true, true);
		$packageTypesCur_str = $this->getPackageTypes($packageTypesCur, true, true);

		$params['ServiceList_id'] = $this->ServiceList_id;

		$filter = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',',$data['exportId']);
			$exportIdFilter = " and SLP.ServiceListPackage_ObjectID in ({$exportIds_str})";

			if (!empty($data['packageType'])) {
				$allowedPackageTypes = explode('|', $data['packageType']);
				if (in_array('PERSONATTACH', $allowedPackageTypes) || in_array('PERSONATTACHDISTRICT', $allowedPackageTypes)) {
					// для прикреплений нужны ещё и участки
					$personCardIds = $this->queryList("select LpuRegion_id from v_PersonCard_all with (nolock) where PersonCard_id in ({$exportIds_str})");

					if (!empty($personCardIds)) {
						$exportIds_str = implode(',', array_merge($data['exportId'], $personCardIds));
						$exportIdFilter = " and SLP.ServiceListPackage_ObjectID in ({$exportIds_str})";
					}
				}
			}

			$filter .= $exportIdFilter;
		}
		
		if ($this->regionNick == 'kareliya') {
			$filter .= " and not exists(
				select top 1 * from stg.v_ServiceListPackage SLP2 with(nolock)
				inner join stg.v_ServiceListDetailLog SLDL with(nolock) on SLDL.ServiceListPackage_id = SLP2.ServiceListPackage_id
				where SLP2.ServiceListPackage_GUID = SLP.ServiceListPackage_GUID
					and SLDL.ServiceListLogType_id = 2
			)";
		}
		
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
					SLPT.ServiceListPackageType_Name as PACKAGE_TYPE,
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
					and not exists(
						select top 1 ServiceListDetailLog_id from stg.v_ServiceListDetailLog SLDL with(nolock)
						where SLDL.ServiceListLog_id = SLP.ServiceListLog_id
						and SLDL.ServiceListPackage_id = SLP.ServiceListPackage_id
						and SLDL.ServiceListLogType_id = 2
					)
					{$filter}
				group by
					L.Lpu_id,
					L.Lpu_f003mcod,
					SLP.ServiceListPackage_ObjectID,
					SLPDT.ServiceListProcDataType_id,
					SLPDT.ServiceListProcDataType_Name,
					SLPT.ServiceListPackageType_NAME;
				
				insert into {$tmpTable}
				select
					PL.Lpu_id,
					PL.LPU,
					PL.ObjectID,
					PL.ObjectID AS ID,
					OSL.Object_Guid as GUID,
					lastUpdate.TYPE,
					PL.PACKAGE_TYPE,
					lastUpdate.DATE as DATE
				from 
					{$tmpTmpTable} as PL
					outer apply(
						select top 1 DATE, TYPE
						from {$tmpTmpTable} where PACKAGE_TYPE in ({$packageTypesCur_str}) 
						and ObjectID = PL.ObjectID and TYPE_ID in (1,3) and DATE >= PL.DATE
						order by DATE desc
					) lastUpdate
					outer apply (
						select top 1 OSL.Object_Guid
						from v_ObjectSynchronLog OSL with(nolock)
						inner join v_ObjectSynchronLogService OSLS with(nolock) on OSLS.ObjectSynchronLogService_id = OSL.ObjectSynchronLogService_id
						where OSLS.ObjectSynchronLogService_SysNick = :syncService
						and OSL.Object_Name in ({$packageTypesCur_str}) and OSL.Object_id = PL.ObjectID
						order by OSL.Object_setDT desc
					) OSL
				where 
					PL.TYPE_ID = 1
					and not exists (
						select * from {$tmpTmpTable}
						where ObjectID = PL.ObjectID and PACKAGE_TYPE in ({$packageTypesCur_str})
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
	 * Получение данных об участках
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_DISTRICT($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and LR.LpuRegion_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and LR.LpuRegion_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesInSQL = $this->getPackageTypes(['DISTRICT'], true, true);

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as LPUREGION_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					LPT.PassportToken_tid as ID_MO,
					convert(varchar(10), @date, 120) as DATE,
					LR.LpuRegion_Name as LPUREGION_NAME,
					LRT.LpuRegionType_Code as LPUREGIONTYPE,
					coalesce(SectionCode.Value, LS.LpuSection_Code) as LPUSECTION_CODETFOMS,
					coalesce(BuildingCode.Value, LB.LpuBuilding_Code) as LPUBUILDING_CODETFOMS,
					MSF.Person_Snils as DOC_SNILS,
					case when MSF.PostKind_id = 1 then 1 else 2 end as DOC_TYPE,
					convert(varchar(10), MSF.MedStaffRegion_begDate, 120) as DOC_DATE,
					convert(varchar(10), LR.LpuRegion_begDate, 120) as LPUREGION_BEGDATE,
					convert(varchar(10), LR.LpuRegion_endDate, 120) as LPUREGION_ENDDATE
					-- end select
				from
					-- from
					{$tmpTable} PL
					inner join fed.v_PassportToken LPT with(nolock) on LPT.Lpu_id = PL.Lpu_id
					left join LpuRegion LR with(nolock) on LR.LpuRegion_id = PL.ObjectID
					left join v_LpuRegionType LRT with(nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = LR.LpuSection_id
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
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
						select top 1 
							MSF.*,
							MSFR.MedStaffRegion_begDate,
							MSFRM.MedStaffRegionMain_insDT
						from v_MedStaffRegion MSFR with(nolock)
							inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
							left join MedStaffRegionMain MSFRM (nolock) on MSFRM.MedStaffRegion_id = MSFR.MedStaffRegion_id
						where MSFR.LpuRegion_id = LR.LpuRegion_id
							and MSFR.MedStaffRegion_begDate <= isnull(LR.LpuRegion_endDate, @date)
							and isnull(MSFR.MedStaffRegion_endDate, @date) >= LR.LpuRegion_begDate
						order by
							MSFR.MedStaffRegion_isMain desc,
							MSFR.MedStaffRegion_begDate desc
					) MSF
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and LR.LpuRegion_deleted = 2
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			
			if ($this->regionNick == 'kareliya') {
				$update_select = "
					when 
						DISTRICT.ID is not null and 
						DISTRICT.DATE <= MSF.MedStaffRegionMain_insDT and 
						MSF.MedStaffFact_id != isnull(MSFR_old.MedStaffFact_id, 0)
					then 'Update'
				";
			} else {
				$update_select = " when DISTRICT.ID is not null and DISTRICT.DATE <= LR.LpuRegion_updDT then 'Update' ";
			}
			
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					LR.LpuRegion_id as ObjectID,
					LR.LpuRegion_id as LPUREGION_ID,
					isnull(DISTRICT.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					LPT.PassportToken_tid as ID_MO,
					convert(varchar(10), @date, 120) as DATE,
					LR.LpuRegion_Name as LPUREGION_NAME,
					LRT.LpuRegionType_Code as LPUREGIONTYPE,
					coalesce(SectionCode.Value, LS.LpuSection_Code) as LPUSECTION_CODETFOMS,
					coalesce(BuildingCode.Value, LB.LpuBuilding_Code) as LPUBUILDING_CODETFOMS,
					MSF.Person_Snils as DOC_SNILS,
					case when MSF.PostKind_id = 1 then 1 else 2 end as DOC_TYPE,
					convert(varchar(10), MSF.MedStaffRegion_begDate, 120) as DOC_DATE,
					convert(varchar(10), LR.LpuRegion_begDate, 120) as LPUREGION_BEGDATE,
					convert(varchar(10), LR.LpuRegion_endDate, 120) as LPUREGION_ENDDATE
					-- end select
				from
					-- from
					v_LpuRegion LR with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = LR.Lpu_id
					inner join v_LpuRegionType LRT with(nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					inner join fed.v_PassportToken LPT with(nolock) on LPT.Lpu_id = LR.Lpu_id
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = LR.LpuSection_id
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
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
						select top 1 
							MSF.*,
							MSFR.MedStaffRegion_begDate,
							MSFRM.MedStaffRegionMain_insDT
						from v_MedStaffRegion MSFR with(nolock)
							inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
							left join MedStaffRegionMain MSFRM (nolock) on MSFRM.MedStaffRegion_id = MSFR.MedStaffRegion_id
						where MSFR.LpuRegion_id = LR.LpuRegion_id
							and MSFR.MedStaffRegion_begDate <= isnull(LR.LpuRegion_endDate, @date)
							and isnull(MSFR.MedStaffRegion_endDate, @date) >= LR.LpuRegion_begDate
						order by
							MSFR.MedStaffRegion_isMain desc,
							MSFR.MedStaffRegion_begDate desc
					) MSF
					left join {$tmpTable} DISTRICT on DISTRICT.PACKAGE_TYPE in ({$packageTypesInSQL}) and DISTRICT.ObjectID = LR.LpuRegion_id
					-- подзапрос для получения основного врача участка на момент предыдущего запуска
					outer apply (
						select top 1 MSFR.MedStaffRegion_id, MSFR.MedStaffFact_id
						from MedStaffRegion MSFR (nolock)
						inner join MedStaffRegionMain MSFRM (nolock) on MSFRM.MedStaffRegion_id = MSFR.MedStaffRegion_id
						where 
							MSFR.LpuRegion_id = LR.LpuRegion_id and
							MSFR.MedStaffRegion_isMain = 2 and
							DISTRICT.DATE > MSF.MedStaffRegionMain_insDT
						order by 
							MSFR.MedStaffRegion_id desc
					) MSFR_old
					outer apply (
						select case
							when DISTRICT.ID is null then 'Insert'
							{$update_select}
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value like :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filters}
					-- end where
				order by
					-- order by
					LR.Lpu_id
					-- end order by
			";
		}

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			$result = $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
			if ($this->regionNick == 'kareliya') {
				$op_type = [
					'Insert' => 1,
					'Delete' => 3,
					'Update' => 4,
				];
				foreach($result as &$row) {
					$row['OPERATIONTYPE'] = $op_type[$row['OPERATIONTYPE']];
					$row['LPUREGIONTYPE'] = isset($row['LPUREGIONTYPE']) ? intval($row['LPUREGIONTYPE']) : null;
				}
			}
			return $result;
		}
	}

	/**
	 * Получение данных о прикреплениях
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_PERSONATTACH($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and PC.PersonCard_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and PC.PersonCardBeg_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesDISTRICTInSQL = $this->getPackageTypes(['DISTRICT'], true, true);
		$packageTypesInSQL = $this->getPackageTypes(['PERSONATTACH'], true, true);

		if ($procDataType == 'Delete') {
			$query = "
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as PERSONATTACHID,
					PL.GUID,
					'Delete' as OPERATIONTYPE
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_PersonCard_all PC with(nolock) on PC.PersonCard_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and PC.PersonCard_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			if (!in_array($this->regionNick, array('kareliya'))) {
				$filters .= " and P.BDZ_id is not null";
			}

			$query = "
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					PC.PersonCard_id as ObjectID,
					PC.PersonCard_id as PERSONATTACHID,
					isnull(PERSONATTACH.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					P.BDZ_id as BDZID,
					PC.LpuRegion_id as LPUREGION_ID,
					PC.LpuRegion_fapid as LPUREGIONFAP_ID,
					convert(varchar(10), PC.PersonCard_begDate, 120) as PERSONATTACHDATE,
					convert(varchar(10), PC.PersonCard_endDate, 120) as PERSONATTACHENDT,
					IsAttachCondit.YesNo_Code as PERSONATTACHUSL,
					CCC.CardCloseCause_Code as CARDCLSECAUSE
					-- end select
				from
					-- from
					v_PersonCard_all PC with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = PC.Lpu_id
					inner join Person P with(nolock) on P.Person_id = PC.Person_id
					left join v_YesNo IsAttachCondit with(nolock) on IsAttachCondit.YesNo_id = isnull(PC.PersonCard_IsAttachCondit, 1)
					left join v_CardCloseCause CCC with(nolock) on CCC.CardCloseCause_id = PC.CardCloseCause_id
					inner join {$tmpTable} DISTRICT on DISTRICT.PACKAGE_TYPE in ({$packageTypesDISTRICTInSQL}) and DISTRICT.ObjectID = PC.LpuRegion_id
					left join {$tmpTable} PERSONATTACH on PERSONATTACH.PACKAGE_TYPE in ({$packageTypesInSQL}) and PERSONATTACH.ObjectID = PC.PersonCard_id
					outer apply (
						select case
							when PERSONATTACH.ID is null then 'Insert'
							when PERSONATTACH.ID is not null and PERSONATTACH.DATE <= isnull(PC.PersonCardEnd_updDT, PC.PersonCardBeg_updDT) then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and PC.LpuRegion_id is not null
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
	 * Получение данных о прикреплениях с участками
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_PERSONATTACHDISTRICT($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "1=1";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and PC.PersonCard_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and PC.PersonCardBeg_insDT >= :InitDate";
				$filters .= " and PC.PersonCard_begDate >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}
		
		$packageTypesInSQL = $this->getPackageTypes(['PERSONATTACHDISTRICT'], true, true);		

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					LPT.PassportToken_tid as ID_MO,
					PL.ObjectID,
					PL.ID as PERSONATTACHID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					3 as OPER_TYPE,
					convert(varchar(10), @date, 120) as DATA,
					P.BDZ_id as BDZID,
					P.Person_id as ID_PAC,
					rtrim(PS.Person_SurName) as FAM,
					rtrim(PS.Person_FirName) as IM,
					rtrim(PS.Person_SecName) as OT,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as DR,
					Smo.OrgSmo_f002smocod as SMO,
					PT.PolisType_CodeF008 as VPOLIS,
					Polis.Polis_Ser as SPOLIS,
					Polis.Polis_Num as NPOLIS,
					DT.DocumentType_Code as DOCTYPE,
					case when DT.DocumentType_Code = 14 and len(Document.Document_Ser) = 4
						then substring(Document.Document_Ser, 1, 2)+' '+substring(Document.Document_Ser, 3, 2)
						else Document.Document_Ser
					end as DOCSER,
					Document.Document_Num as DOCNUM,
					PS.Person_Snils as SNILS,
					case when PC.PersonCard_endDate is null then 1 else 0 end as INFO_TYPE,
					convert(varchar(10), isnull(PC.PersonCard_endDate, PC.PersonCard_begDate), 120) as DATE,
					case when IsAttachCondit.YesNo_Code = 1 then 1 else 2 end as ATTACH_TYPE,
					0 as T_PRIK,
					convert(varchar(10), PC.PersonCard_begDate, 120) as ATTACH_DT_MO,
					L.Lpu_f003mcod as ATTACH_CODE_MO,
					convert(varchar(10), PC.PersonCard_endDate, 120) as DETACH_DT_MO,
					case 
						when PC.PersonCard_endDate is not null 
						then isnull(CCC.CardCloseCause_Code, CCCByNextPC.CardCloseCause_Code)
					end as DETACH_CAUSE_MO,
					coalesce(BuildingCode.Value, LB.LpuBuilding_Code) as PODR,
					coalesce(SectionCode.Value, LS.LpuSection_Code) as OTD,
					LR.LpuRegion_Name as UCH,
					LRT.LpuRegionType_Code as UCH_TYPE,
					case 
						when LR.LpuRegionType_SysNick = 'ter' then 1
						when LR.LpuRegionType_SysNick = 'ped' then 2
						when LR.LpuRegionType_SysNick = 'vop' then 3
					end as TIP_UCH,
					null as PUNKT,
					MSF.Person_Snils as SNILS_VR,
					convert(varchar(10), PC.PersonCard_begDate, 120) as ATTACH_DT,
					coalesce(fapBuildingCode.Value, fapLB.LpuBuilding_Code) as PODR_F,
					coalesce(fapSectionCode.Value, fapLS.LpuSection_Code) as OTD_F,
					fapLR.LpuRegion_Name as UCH_F,
					null as PUNKT_F,
					fapMSF.Person_Snils as SNILS_VR_F,
					case
						when fapLR.LpuRegion_id is not null 
						then convert(varchar(10), PC.PersonCard_begDate, 120)
					end as ATTACH_DT_F,
					case 
						when fapLR.LpuRegion_id is not null 
						then convert(varchar(10), PC.PersonCard_endDate, 120)
					end as DETACH_DT_F,
					case 
						when PC.PersonCard_endDate is not null and fapLR.LpuRegion_id is not null 
						then CRD.CardReasonDetach_Code
					end as DETACH_F_CAUSE,
					PS.Person_Phone as PHONE1,
					null as PHONE2
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join PersonCard PC with(nolock) on PC.PersonCard_id = PL.ObjectID
					inner join fed.v_PassportToken LPT with(nolock) on LPT.Lpu_id = PL.Lpu_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = PC.Lpu_id
					inner join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					inner join Person P with(nolock) on P.Person_id = PC.Person_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = PC.Person_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_YesNo IsAttachCondit with(nolock) on IsAttachCondit.YesNo_id = isnull(PC.PersonCard_IsAttachCondit, 1)
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_OmsSprTerr ost with(nolock) on ost.OmsSprTerr_id = Polis.OmsSprTerr_id
					left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
					left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
					left join v_Document Document with(nolock) on Document.Document_id = PS.Document_id
					left join v_DocumentType DT with(nolock) on DT.DocumentType_id = Document.DocumentType_id
					left join v_LpuRegionType LRT with(nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = LR.LpuSection_id
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					outer apply (
						select top 1 nextPC.*
						from v_PersonCard nextPC with(nolock)
						where nextPC.Person_id = PS.Person_id
						and nextPC.PersonCard_begDate > PC.PersonCard_begDate
						order by nextPC.PersonCard_begDate asc
					) nextPC
					left join v_CardCloseCause CCC with(nolock) on CCC.CardCloseCause_id = PC.CardCloseCause_id
					left join v_CardCloseCause CCCByNextPC with(nolock) on CCCByNextPC.CardCloseCause_id = case 
						when PC.PersonCard_endDate is not null and PC.CardCloseCause_id is null
						then case
							when nextPC.Lpu_id <> PC.Lpu_id then 1
							when nextPC.LpuRegion_id <> PC.LpuRegion_id then 4
							when exists(
								select * from v_PersonPolis PP with(nolock)
								where PP.Person_id = PS.Person_id 
								and PP.Polis_begDate = nextPC.PersonCard_begDate
							) then 10
							else 9
						end
					end
					outer apply (
						select top 1
							crd.CardReasonDetach_Code
						from
							v_CardReasonDetachLink crdl (nolock)
							inner join v_CardReasonDetach crd (nolock) on crd.CardReasonDetach_id = crdl.CardReasonDetach_id
						where
							crdl.CardCloseCause_id = isnull(CCC.CardCloseCause_id, CCCByNextPC.CardCloseCause_id)
					) CRD
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
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
						select top 1 MSF.*
						from v_MedStaffRegion MSFR with(nolock)
						inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
						where MSFR.LpuRegion_id = LR.LpuRegion_id
						and MSFR.MedStaffRegion_begDate <= isnull(PC.PersonCard_endDate, @date)
						and isnull(MSFR.MedStaffRegion_endDate, @date) >= PC.PersonCard_begDate
						order by
						MSFR.MedStaffRegion_isMain desc,
						MSFR.MedStaffRegion_begDate desc
					) MSF
					left join v_LpuRegion fapLR with(nolock) on fapLR.LpuRegion_id = PC.LpuRegion_fapid
					left join v_LpuRegionType fapLRT with(nolock) on fapLRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_LpuSection fapLS with(nolock) on fapLS.LpuSection_id = fapLR.LpuSection_id
					left join v_LpuBuilding fapLB with(nolock) on fapLB.LpuBuilding_id = fapLS.LpuBuilding_id
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = fapLS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
						order by ASV.AttributeSignValue_begDate desc,
						ASV.AttributeSignValue_insDT desc
					) fapASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = fapASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Section_Code'
					) fapSectionCode
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = fapASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) fapBuildingCode
					outer apply (
						select top 1 MSF.*
						from v_MedStaffRegion MSFR with(nolock)
						inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
						where MSFR.LpuRegion_id = fapLR.LpuRegion_id
						and MSFR.MedStaffRegion_begDate <= isnull(PC.PersonCard_endDate, @date)
						and isnull(MSFR.MedStaffRegion_endDate, @date) >= PC.PersonCard_begDate
						order by
						MSFR.MedStaffRegion_isMain desc,
						MSFR.MedStaffRegion_begDate desc
					) fapMSF
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and PC.PersonCard_deleted = 2
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			if ($procDataType == 'Insert') {
				$filters .= " and not exists(
					select * from {$tmpTable} 
					where ObjectID = PC.PersonCard_id and PACKAGE_TYPE in ({$packageTypesInSQL})
				)";
			} else {
				$filters .= " and exists(
					select * from {$tmpTable} 
					where ObjectID = PC.PersonCard_id and PACKAGE_TYPE in ({$packageTypesInSQL})
					and DATE <= isnull(PC.PersonCardEnd_updDT, PC.PersonCardBeg_updDT)
				)";
			}

			if (!in_array($this->regionNick, array('kareliya'))) {
				$filters .= " and P.BDZ_id is not null";
			}

			if ($this->regionNick == 'perm') {
				$filters .= " and nullif(SectionCode.Value,'') is not null";
				$filters .= " and nullif(BuildingCode.Value,'') is not null";
			}

			if ($this->regionNick == 'kareliya') {
				$filters .= " and ost.KLRgn_id = dbo.GetRegion() ";
				$filters .= " and PC.LpuAttachType_id = 1 ";
			}

			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					LPT.PassportToken_tid as ID_MO,
					PC.PersonCard_id as ObjectID,
					PC.PersonCard_id as PERSONATTACHID,
					isnull(PERSONATTACHDISTRICT.GUID, newid()) as GUID,
					'{$procDataType}' as OPERATIONTYPE,
					case 
						when '{$procDataType}' = 'Insert' then 1
						when '{$procDataType}' = 'Update' then 2
					end as OPER_TYPE,
					convert(varchar(10), @date, 120) as DATA,
					P.BDZ_id as BDZID,
					P.Person_id as ID_PAC,
					upper(rtrim(PS.Person_SurName)) as FAM,
					upper(rtrim(PS.Person_FirName)) as IM,
					upper(rtrim(PS.Person_SecName)) as OT,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as DR,
					Smo.OrgSmo_f002smocod as SMO,
					PT.PolisType_CodeF008 as VPOLIS,
					Polis.Polis_Ser as SPOLIS,
					Polis.Polis_Num as NPOLIS,
					DT.DocumentType_Code as DOCTYPE,
					case when DT.DocumentType_Code = 14 and len(Document.Document_Ser) = 4
						then substring(Document.Document_Ser, 1, 2)+' '+substring(Document.Document_Ser, 3, 2)
						else Document.Document_Ser
					end as DOCSER,
					Document.Document_Num as DOCNUM,
					PS.Person_Snils as SNILS,
					case when PC.PersonCard_endDate is null then 1 else 0 end as INFO_TYPE,
					convert(varchar(10), isnull(PC.PersonCard_endDate, PC.PersonCard_begDate), 120) as DATE,
					case when IsAttachCondit.YesNo_Code = 1 then 1 else 2 end as ATTACH_TYPE,
					0 as T_PRIK,
					convert(varchar(10), PC.PersonCard_begDate, 120) as ATTACH_DT_MO,
					L.Lpu_f003mcod as ATTACH_CODE_MO,
					convert(varchar(10), PC.PersonCard_endDate, 120) as DETACH_DT_MO,
					case 
						when PC.PersonCard_endDate is not null 
						then isnull(CCC.CardCloseCause_Code, CCCByNextPC.CardCloseCause_Code)
					end as DETACH_CAUSE_MO,
					coalesce(BuildingCode.Value, LB.LpuBuilding_Code) as PODR,
					coalesce(SectionCode.Value, LS.LpuSection_Code) as OTD,
					case 
						when dbo.getRegion() = 59 then coalesce(BuildingCode.Value, LB.LpuBuilding_Code) + coalesce(SectionCode.Value, LS.LpuSection_Code) + LR.LpuRegion_Name
						else LR.LpuRegion_Name
					end as UCH,
					LRT.LpuRegionType_Code as UCH_TYPE,
					case 
						when LR.LpuRegionType_SysNick = 'ter' then 1
						when LR.LpuRegionType_SysNick = 'ped' then 2
						when LR.LpuRegionType_SysNick = 'vop' then 3
					end as TIP_UCH,
					null as PUNKT,
					MSF.Person_Snils as SNILS_VR,
					convert(varchar(10), PC.PersonCard_begDate, 120) as ATTACH_DT,
					coalesce(fapBuildingCode.Value, fapLB.LpuBuilding_Code) as PODR_F,
					coalesce(fapSectionCode.Value, fapLS.LpuSection_Code) as OTD_F,
					case 
						when dbo.getRegion() = 59 then coalesce(fapBuildingCode.Value, fapLB.LpuBuilding_Code) + coalesce(fapSectionCode.Value, fapLS.LpuSection_Code) + fapLR.LpuRegion_Name
						else fapLR.LpuRegion_Name
					end as UCH_F,
					null as PUNKT_F,
					fapMSF.Person_Snils as SNILS_VR_F,
					case
						when fapLR.LpuRegion_id is not null 
						then convert(varchar(10), PC.PersonCard_begDate, 120)
					end as ATTACH_DT_F,
					case 
						when fapLR.LpuRegion_id is not null 
						then convert(varchar(10), PC.PersonCard_endDate, 120)
					end as DETACH_DT_F,
					case 
						when PC.PersonCard_endDate is not null and fapLR.LpuRegion_id is not null 
						then CRD.CardReasonDetach_Code
					end as DETACH_F_CAUSE,
					PS.Person_Phone as PHONE1,
					null as PHONE2
					-- end select
				from
					-- from
					v_PersonCard_all PC with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = PC.Lpu_id
					inner join fed.v_PassportToken LPT with(nolock) on LPT.Lpu_id = L.Lpu_id
					inner join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					inner join Person P with(nolock) on P.Person_id = PC.Person_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = PC.Person_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_YesNo IsAttachCondit with(nolock) on IsAttachCondit.YesNo_id = isnull(PC.PersonCard_IsAttachCondit, 1)
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_OmsSprTerr ost with(nolock) on ost.OmsSprTerr_id = Polis.OmsSprTerr_id
					left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
					left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
					left join v_Document Document with(nolock) on Document.Document_id = PS.Document_id
					left join v_DocumentType DT with(nolock) on DT.DocumentType_id = Document.DocumentType_id
					left join v_LpuRegionType LRT with(nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = LR.LpuSection_id
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					outer apply (
						select top 1 nextPC.*
						from v_PersonCard nextPC with(nolock)
						where nextPC.Person_id = PS.Person_id
						and nextPC.PersonCard_begDate > PC.PersonCard_begDate
						order by nextPC.PersonCard_begDate asc
					) nextPC
					left join v_CardCloseCause CCC with(nolock) on CCC.CardCloseCause_id = PC.CardCloseCause_id
					left join v_CardCloseCause CCCByNextPC with(nolock) on CCCByNextPC.CardCloseCause_id = case 
						when PC.PersonCard_endDate is not null and PC.CardCloseCause_id is null
						then case
							when nextPC.Lpu_id <> PC.Lpu_id then 1
							when nextPC.LpuRegion_id <> PC.LpuRegion_id then 4
							when exists(
								select * from v_PersonPolis PP with(nolock)
								where PP.Person_id = PS.Person_id 
								and PP.Polis_begDate = nextPC.PersonCard_begDate
							) then 10
							else 9
						end
					end
					outer apply (
						select top 1
							crd.CardReasonDetach_Code
						from
							v_CardReasonDetachLink crdl (nolock)
							inner join v_CardReasonDetach crd (nolock) on crd.CardReasonDetach_id = crdl.CardReasonDetach_id
						where
							crdl.CardCloseCause_id = isnull(CCC.CardCloseCause_id, CCCByNextPC.CardCloseCause_id)
					) CRD
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = LS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
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
						select top 1 MSF.*
						from v_MedStaffRegion MSFR with(nolock)
						inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
						where MSFR.LpuRegion_id = LR.LpuRegion_id
						and MSFR.MedStaffRegion_begDate <= isnull(PC.PersonCard_endDate, @date)
						and isnull(MSFR.MedStaffRegion_endDate, @date) >= PC.PersonCard_begDate
						order by
						MSFR.MedStaffRegion_isMain desc,
						MSFR.MedStaffRegion_begDate desc
					) MSF
					left join v_LpuRegion fapLR with(nolock) on fapLR.LpuRegion_id = PC.LpuRegion_fapid
					left join v_LpuRegionType fapLRT with(nolock) on fapLRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_LpuSection fapLS with(nolock) on fapLS.LpuSection_id = fapLR.LpuSection_id
					left join v_LpuBuilding fapLB with(nolock) on fapLB.LpuBuilding_id = fapLS.LpuBuilding_id
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = fapLS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
						order by ASV.AttributeSignValue_begDate desc,
						ASV.AttributeSignValue_insDT desc
					) fapASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = fapASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Section_Code'
					) fapSectionCode
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = fapASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) fapBuildingCode
					outer apply (
						select top 1 MSF.*
						from v_MedStaffRegion MSFR with(nolock)
						inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
						where MSFR.LpuRegion_id = fapLR.LpuRegion_id
						and MSFR.MedStaffRegion_begDate <= isnull(PC.PersonCard_endDate, @date)
						and isnull(MSFR.MedStaffRegion_endDate, @date) >= PC.PersonCard_begDate
						order by
						MSFR.MedStaffRegion_isMain desc,
						MSFR.MedStaffRegion_begDate desc
					) fapMSF
					inner join {$tmpTable} DISTRICT on DISTRICT.PACKAGE_TYPE in ({$this->getPackageTypes(['DISTRICT'], true, true)}) and DISTRICT.ObjectID = PC.LpuRegion_id
					left join {$tmpTable} PERSONATTACHDISTRICT on PERSONATTACHDISTRICT.PACKAGE_TYPE in ({$packageTypesInSQL})
						and PERSONATTACHDISTRICT.ObjectID = PC.PersonCard_id
					-- end from
				where
					-- where
					{$filters}
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and (L.Lpu_endDate is null or L.Lpu_endDate > @date)
					and PC.LpuRegion_id is not null
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
	 * Получение данных о дисп.наблюдении
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_DISP($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		//NB: Метод переопределен на Карелии
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$fields = "";
		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and PD.PersonDisp_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and PD.PersonDisp_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesInSQL = $this->getPackageTypes(['DISP'], true, true);

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as DISP_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_PersonDISP PD with(nolock) on PD.PersonDisp_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and PD.PersonDisp_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {

			if ($this->regionNick == 'ufa') {
				$fields .= "P.BDZ_Guid as BDZ_GUID,";
				$filters .= " and P.BDZ_Guid is not null";
			} else {
				$fields .= "P.BDZ_id as BDZ_ID,";
			}

			if (!in_array($this->regionNick, array('kareliya', 'ufa'))) {
				$filters .= " and P.BDZ_id is not null";
			}

			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					PD.PersonDisp_id as ObjectID,
					PD.PersonDisp_id as DISP_ID,
					isnull(DISP.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					{$fields}
					P.Person_id as ID_PAC,
					upper(rtrim(PS.Person_SurName)) as FAM,
					upper(rtrim(PS.Person_FirName)) as IM,
					upper(rtrim(PS.Person_SecName)) as OT,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as DR,
					PT.PolisType_CodeF008 as VPOLIS,
					Polis.Polis_Ser as SPOLIS,
					case
						when PT.PolisType_CodeF008 = 3
						then PS.Person_EdNum
						else Polis.Polis_Num
					end  as NPOLIS,
					DT.DocumentType_Code as DOCTYPE,
					case when DT.DocumentType_Code = 14 and len(Document.Document_Ser) = 4
						then substring(Document.Document_Ser, 1, 2)+' '+substring(Document.Document_Ser, 3, 2)
						else Document.Document_Ser
					end as DOCSER,
					Document.Document_Num as DOCNUM,
					PS.Person_Snils as SNILS,
					case when PD.PersonDisp_endDate is null then 1 else 0 end as ATTACH_DISP_TYPE,
					convert(varchar(10), PD.PersonDisp_begDate, 120) as DATE_IN,
					D.Diag_Code as DS,
					DetectType.DeseaseDispType_Code as DS_DETECT,
					Detect.DiagDetectType_Code as DS_DETECTTYPE,
					MPPS.Person_Snils as SNILS_VR,
					convert(varchar(10), PD.PersonDisp_endDate, 120) as DATE_OUT,
					case 
						when DOT.DispOutType_Code in (1,2,3) then DOT.DispOutType_Code
						when PD.PersonDisp_endDate is not null then 4
					end as RESULT_OUT
					-- end select
				from
					-- from
					v_PersonDisp PD with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = PD.Lpu_id
					inner join Person P with(nolock) on P.Person_id = PD.Person_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
					left join v_Document Document with(nolock) on Document.Document_id = PS.Document_id
					left join v_DocumentType DT with(nolock) on DT.DocumentType_id = Document.DocumentType_id
					left join v_Diag D with(nolock) on D.Diag_id = PD.Diag_id
					left join v_DiagDetectType Detect with(nolock) on Detect.DiagDetectType_id = PD.DiagDetectType_id
					left join v_DeseaseDispType DetectType with(nolock) on DetectType.DeseaseDispType_id = PD.DeseaseDispType_id
					left join v_DispOutType DOT with(nolock) on DOT.DispOutType_id = PD.DispOutType_id
					outer apply (
						select top 1 MP.*
						from v_MedPersonal MP with(nolock)
						where MP.MedPersonal_id = PD.MedPersonal_id
					) MP
					left join v_PersonState MPPS with(nolock) on MPPS.Person_id = MP.Person_id
					left join {$tmpTable} DISP on DISP.PACKAGE_TYPE in ({$packageTypesInSQL}) and DISP.ObjectID = PD.PersonDisp_id
					outer apply (
						select case
							when DISP.ID is null then 'Insert'
							when DISP.ID is not null and DISP.DATE <= PD.PersonDisp_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from 
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
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
			$resp = $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
			if (!is_array($resp) || empty($resp)) {
				return false;
			}

			$ids = array();
			$result = array();
			foreach($resp as $item) {
				$id = $item['ObjectID'];
				$ids[] = $id;
				$result[$id] = $item;
			}

			if ($procDataType != 'Delete') {
				$ids_str = implode(',', $ids);

				$query = "
					select
						PDV.PersonDisp_id as ObjectID,
						convert(varchar(10), PDV.PersonDispVizit_NextDate, 120) as PLAN_DATE
					from
						v_PersonDispVizit PDV with(nolock)
					where
						PDV.PersonDisp_id in ({$ids_str})
						and PDV.PersonDispVizit_NextDate is not null
					order by
						PDV.PersonDisp_id,
						PDV.PersonDispVizit_NextDate
				";
				$resp = $this->queryResult($query);
				if (!is_array($resp) || empty($resp)) {
					return false;
				}

				foreach($resp as $item) {
					$result[$item['ObjectID']]['DATES'][] = array(
						'PLAN_DATE' => $item['PLAN_DATE']
					);
				}

				foreach($result as $ObjectID => $item) {
					if (!empty($item['DATES'])) {
						continue;
					}
					$result[$ObjectID]['DATES'][] = array(
						'PLAN_DATE' => $item['DATE_IN']
					);
				}
			}

			return array_values($result);
		}
	}

	/**
	 * Получение данных о снятии c дисп.наблюдения
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_DISPOUT($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$fields = "";
		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and PD.PersonDisp_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and PD.PersonDisp_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesInSQL = $this->getPackageTypes(['DISPOUT'], true, true);

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as DISP_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_PersonDISP PD with(nolock) on PD.PersonDisp_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and PD.PersonDisp_endDate is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {

			if ($this->regionNick == 'ufa') {
				$fields .= "P.BDZ_Guid as BDZ_GUID,";
				$filters .= " and P.BDZ_Guid is not null";
			} else {
				$fields .= "P.BDZ_id as BDZID,";
			}

			if (!in_array($this->regionNick, array('kareliya', 'ufa'))) {
				$filters .= " and P.BDZ_id is not null";
			}
			
			if ($this->regionNick == 'kareliya') {
				$filters .= " and DISP.ID is not null";
			}

			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					PD.PersonDisp_id as ObjectID,
					PD.PersonDisp_id as DISP_ID,
					isnull(DISPOUT.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					{$fields}
					P.Person_id as ID_PAC,
					upper(rtrim(PS.Person_SurName)) as FAM,
					upper(rtrim(PS.Person_FirName)) as IM,
					upper(rtrim(PS.Person_SecName)) as OT,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as DR,
					PT.PolisType_CodeF008 as VPOLIS,
					Polis.Polis_Ser as SPOLIS,
					case
						when PT.PolisType_CodeF008 = 3
						then PS.Person_EdNum 
						else Polis.Polis_Num
					end  as NPOLIS,
					D.Diag_Code as DS,
					MPPS.Person_Snils as SNILS_VR,
					convert(varchar(10), PD.PersonDisp_endDate, 120) as DATE_OUT,
					case 
						when DOT.DispOutType_Code in (1,2,3) then DOT.DispOutType_Code
						when PD.PersonDisp_endDate is not null then 4
					end as RESULT_OUT
					-- end select
				from
					-- from
					v_PersonDisp PD with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = PD.Lpu_id
					inner join Person P with(nolock) on P.Person_id = PD.Person_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
					left join v_Diag D with(nolock) on D.Diag_id = PD.Diag_id
					left join v_DispOutType DOT with(nolock) on DOT.DispOutType_id = PD.DispOutType_id
					outer apply (
						select top 1 MP.*
						from v_MedPersonal MP with(nolock)
						where MP.MedPersonal_id = PD.MedPersonal_id
					) MP
					left join v_PersonState MPPS with(nolock) on MPPS.Person_id = MP.Person_id
					inner join {$tmpTable} DISP on DISP.PACKAGE_TYPE in ({$this->getPackageTypes(['DISP'], true, true)})  and DISP.ObjectID = PD.PersonDisp_id
					left join {$tmpTable} DISPOUT on DISPOUT.PACKAGE_TYPE in ({$packageTypesInSQL}) and DISPOUT.ObjectID = PD.PersonDisp_id
					outer apply (
						select case
							when DISPOUT.ID is null then 'Insert'
							when DISPOUT.ID is not null and DISPOUT.DATE <= PD.PersonDisp_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from 
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and PD.PersonDisp_endDate is not null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_FREE_BEDS_INFORMATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : array();
		}

		$params = array();
		$filters = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and LS.LpuSection_id in ({$exportIds_str})";
		} else {
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$query = "
			-- variables
			declare @dt datetime = dbo.tzGetDate();
			declare @date date = cast(@dt as date);
			declare @prevdate date = dateadd(day, -1, @date);
			-- end variables
			select
				-- select
				L.Lpu_id,
				L.Lpu_f003mcod as CODE_MO,
				LS.LpuSection_id as ObjectID,
				LS.LpuSection_id as FBI_ID,
				isnull(FBI.GUID, newid()) as GUID,
				'Insert' as OPERATIONTYPE,
				convert(varchar(10), @date, 120) as DATA,
				convert(varchar(10), @date, 120) as ACTUAL_DATE,
				LB.LpuBuilding_Code as BRANCH,
				LSP.LpuSectionProfile_Code as DIVISIONPROFIL,
				fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
				case 
					when dbo.getRegion() <> 10 then null
					when LU.LpuUnitType_SysNick like 'stac' then 1
					when LU.LpuUnitType_SysNick like 'dstac' then 2
					when LU.LpuUnitType_SysNick like 'pstac' then 2
					when LU.LpuUnitType_SysNick like 'hstac' then 2
				end as CARETYPE,
				case 
					when dbo.getRegion() <> 10 OR CurrAmount.Value > 0 
					then CurrAmount.Value
					else 0 
				end as BEDOCCUPIED,
				case 
					when dbo.getRegion() <> 10 OR PrevAmount.Value > 0 
					then PrevAmount.Value
					else 0 
				end as BEDOCCUPIEDTODAY,
				case 
					when dbo.getRegion() <> 10 OR PrevLeaveAmount.Value > 0 
					then PrevLeaveAmount.Value
					else 0 
				end as BEDCLEARTODAY,
				case 
					when dbo.getRegion() <> 10 OR LSBS.LpuSectionBedState_Plan > 0 
					then LSBS.LpuSectionBedState_Plan
					else 0 
				end as BEDPLANNED,
				case 
					when dbo.getRegion() <> 10 OR LSBS.LpuSectionBedState_Fact > 0 then LSBS.LpuSectionBedState_Fact
					else 0 
				end as BEDFREE,
				case
					when 
						(isnull(LS.LpuSectionAge_id, 1) <> 2 AND dbo.getRegion() <> 10) 
						OR (isnull(LS.LpuSectionAge_id, 1) <> 2 AND (LSBS.LpuSectionBedState_Fact - CurrAmount.Value - CurrDirectionAmount.Value) > 0)
					then LSBS.LpuSectionBedState_Fact - CurrAmount.Value - CurrDirectionAmount.Value 
					else 0
				end as BEDFREEADULT,
				case 
					when 
						(isnull(LS.LpuSectionAge_id, 1) = 2 AND dbo.getRegion() <> 10) 
						OR (isnull(LS.LpuSectionAge_id, 1) = 2 AND (LSBS.LpuSectionBedState_Fact - CurrAmount.Value - CurrDirectionAmount.Value) > 0)
					then LSBS.LpuSectionBedState_Fact - CurrAmount.Value - CurrDirectionAmount.Value 
					else 0
				end as BEDFREECHILD
				-- end select
			from
				-- from
				v_LpuSection LS with(nolock)
				inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				inner join v_Lpu L with(nolock) on L.Lpu_id = LS.Lpu_id
				inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				inner join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				inner join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
				outer apply (
					select top 1
						isnull(sum(LSBS.LpuSectionBedState_Plan), 0) as LpuSectionBedState_Plan,
						isnull(sum(LSBS.LpuSectionBedState_Fact), 0) as LpuSectionBedState_Fact
					from v_LpuSectionBedState LSBS with(nolock)
					where LSBS.LpuSection_id = LS.LpuSection_id
					and LSBS.LpuSectionBedState_begDate <= @date
					and (LSBS.LpuSectionBedState_endDate is null or LSBS.LpuSectionBedState_endDate > @date)
				) LSBS
				outer apply(
					select top 1 count(*) as Value
					from v_EvnSection ES with(nolock)
					where ES.LpuSection_id = LS.LpuSection_id
					and @date between ES.EvnSection_setDate and isnull(ES.EvnSection_disDate, @date)
				) CurrAmount
				outer apply(
					select top 1 count(*) as Value
					from v_EvnSection ES with(nolock)
					where ES.LpuSection_id = LS.LpuSection_id
					and @prevdate between ES.EvnSection_setDate and isnull(ES.EvnSection_disDate, @prevdate)
				) PrevAmount
				outer apply (
					select top 1 count(*) as Value
					from v_EvnSection ES with(nolock)
					where ES.LpuSection_id = LS.LpuSection_id
					and ES.EvnSection_disDate = @prevdate
				) PrevLeaveAmount
				outer apply (
					select top 1 count(*) as Value
					from v_EvnDirection_all ED with(nolock)
					where ED.LpuSection_did = LS.LpuSection_id
					and ED.DirType_id in (1,5)
					and ED.EvnDirection_failDT is null
					and not exists(
						select * from v_EvnPS with(nolock) 
						where EvnDirection_id = ED.EvnDirection_id
					)
				) CurrDirectionAmount
				left join {$tmpTable} FBI on FBI.PACKAGE_TYPE = 'FREE_BEDS_INFORMATION' and FBI.ObjectID = LS.LpuSection_id
				-- end from
			where
				-- where
				nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
				and LU.LpuUnitType_SysNick in ('stac','dstac','pstac','hstac')
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		// по задаче #197576
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : [];
		}
		
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ED.EvnDirection_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and ED.EvnDirection_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					dL.Lpu_f003mcod as CODE_MO_TO,
					PL.ObjectID,
					PL.ID as HR_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					inner join EvnDirection ED with(nolock) on ED.EvnDirection_id = PL.ObjectID
					left join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
					left join v_Evn_del EDel with(nolock) on EDel.Evn_id = ED.EvnDirection_id
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'HOSPITALISATION_REFERRAL'
					and ED.DirType_id in (1,5)
					and isnull(ED.EvnDirection_failDT, EDel.Evn_delDT) > PL.DATE
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				declare @region int = dbo.getRegion();
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					dL.Lpu_f003mcod as CODE_MO_TO,
					ED.EvnDirection_id as ObjectID,
					ED.EvnDirection_id as HR_ID,
					isnull(HOSPITALISATION_REFERRAL.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					ED.EvnDirection_Num as REFERRAL_NUMBER,
					(
						cast(L.Lpu_f003mcod as varchar)+
						cast(year(ED.EvnDirection_setDT) as varchar)+
						right('000000'+cast(ED.EvnDirection_Num as varchar), 6)
					) as NOM_NAP,
					convert(varchar(10), ED.EvnDirection_setDT, 120) as REFERRAL_DATE,
					convert(varchar(10), coalesce(
						TTS.TimeTableStac_setDate, ED.EvnDirection_desDT, ED.EvnDirection_setDT
					), 120) as HOSPITALISATION_DATE,
					case 
						when @region = 10 and DirType.DirType_Code = 1 then 3
						when @region = 10 and DirType.DirType_Code = 5 then 1
						when DirType.DirType_Code = 1 then 0
						when DirType.DirType_Code = 5 then 1
					end as HOSPITALISATION_TYPE,
					--DirType.DirType_Code as FRM_MP,
					--coalesce(dBuildingCode.Value, dLB.LpuBuilding_Code) as BRANCH_TO,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(isnull(dLS.LpuSection_Code,'00'), 2)
						else coalesce(dBuildingCode.Value, dLB.LpuBuilding_Code)
					end as BRANCH_TO,
					coalesce(dSectionCode.Value, dLS.LpuSection_Code) as DIVISION_TO,
					fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
					LSP.LpuSectionProfile_Code as STRUCTURE_BED,
					LSBS.LpuSectionBedState_id as DLSB,
					case
						when @region <> 10 then null
						when dLU.LpuUnitType_SysNick like 'stac' then 1
						when dLU.LpuUnitType_SysNick like 'dstac' then 2
						when dLU.LpuUnitType_SysNick like 'pstac' then 2
						when dLU.LpuUnitType_SysNick like 'hstac' then 2
					end as CARETYPE,
					--LB.LpuBuilding_Code as BRANCH_FROM,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(isnull(LS.LpuSection_Code,'00'), 2)
						else LB.LpuBuilding_Code 
					end as BRANCH_FROM,
					D.Diag_Code as MKB,
					D.Diag_Name as DIAGNOSIS,
					convert( varchar(10), coalesce(
						TTS.TimeTableStac_setDate, ED.EvnDirection_desDT, ED.EvnDirection_setDT
					), 120) as PLANNED_DATE,
					MP.Person_Snils as DOC_CODE,
					(
						left(MP.Person_Snils, 3) + '-' + substring(MP.Person_Snils, 4, 3) + '-' + 
						substring(MP.Person_Snils, 7, 3) + ' ' + right(MP.Person_Snils, 2)
					) as DOC_CODE_14,
					PT.PolisType_CodeF008 as POLICY_TYPE,
					nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
					case when PT.PolisType_CodeF008 = 3 
						then PS.Person_EdNum else Polis.Polis_Num
					end as POLIS_NUMBER,
					Smo.OrgSmo_Name as SMO,
					Smo.OrgSmo_f002smocod as SMO_CODE,
					SmoRgn.KLAdr_Ocatd as SMO_OKATO,
					left(SmoRgn.KLAdr_Ocatd, 5) as ST_OKATO,
					upper(rtrim(PS.Person_SurName)) as LAST_NAME,
					upper(rtrim(PS.Person_FirName)) as FIRST_NAME,
					upper(rtrim(PS.Person_SecName)) as FATHER_NAME,
					case when Sex.Sex_fedid = 1 then 10301 else 10302 end as SEX,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
					isnull(nullif(PS.PersonPhone_Phone, ''), 'не указан') as PHONE,
					PS.Person_id as PATIENT,
					0 as ANOTHER_REGION
					-- end select
				from
					-- from
					v_EvnDirection ED with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = ED.Lpu_id
					left join v_DirType DirType with(nolock) on DirType.DirType_id = ED.DirType_id
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ED.LpuSection_id
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
					inner join v_LpuSection dLS with(nolock) on dLS.LpuSection_id = ED.LpuSection_did
					inner join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
					inner join v_LpuBuilding dLB with(nolock) on dLB.LpuBuilding_id = dLS.LpuBuilding_id
					inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = isnull(dLS.LpuSectionProfile_id, ED.LpuSectionProfile_id)
					inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = dLS.LpuSectionBedProfile_id
					inner join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
					inner join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
					inner join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
					inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = ED.PersonEvn_id
					inner join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
					left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
					left join v_KLArea SmoRgn with(nolock) on SmoRgn.KLArea_id = Smo.KLRgn_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_TimeTableStac TTS with(nolock) on TTS.TimeTableStac_id = ED.TimeTableStac_id
					outer apply (
						select top 1 LSBS.*
						from v_LpuSectionBedState LSBS with(nolock)
						where LSBS.LpuSection_id = dLS.LpuSection_id
						--and LSBS.LpuSectionProfile_id = LSP.LpuSectionProfile_id	--todo: check
						and ED.EvnDirection_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, ED.EvnDirection_setDate)
						order by LSBS.LpuSectionBedState_begDate desc
					) LSBS
					outer apply (
						select top 1 MP.*
						from v_MedPersonal MP with(nolock)
						where MP.MedPersonal_id = ED.MedPersonal_id
					) MP
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = dLS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
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
					outer apply (
						select case
							when HOSPITALISATION_REFERRAL.ID is null then 'Insert'
							when HOSPITALISATION_REFERRAL.ID is not null and HOSPITALISATION_REFERRAL.DATE <= ED.EvnDirection_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and nullif(nullif(dL.Lpu_f003mcod,'0'),'') is not null
					and nullif(Smo.Orgsmo_f002smocod, '') is not null
					and DirType.DirType_Code in (1,5)
					and ED.EvnDirection_failDT is null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_CANCEL_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		// по задаче #197576
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : [];
		}
		
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ED.EvnDirection_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and ED.EvnDirection_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}
		
		if ($this->regionNick == 'kareliya') {
			$msf = " isnull(ED.MedStaffFact_fid, ED.MedStaffFact_id) ";
		} else {
			$msf = " ED.MedStaffFact_fid ";
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					dL.Lpu_f003mcod as CODE_MO_TO,
					PL.ObjectID,
					PL.ID as CHR_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					inner join v_EvnDirection ED with(nolock) on ED.EvnDirection_id = PL.ObjectID
					left join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION_REFERRAL'
					and ED.DirType_id in (1,5)
					and ED.EvnDirection_failDT is null
					and ED.EvnDirection_updDT > PL.DATE
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				declare @region int = dbo.getRegion();
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					dL.Lpu_f003mcod as CODE_MO_TO,
					ED.EvnDirection_id as ObjectID,
					ED.EvnDirection_id as CHR_ID,
					isnull(CANCEL_HOSPITALISATION_REFERRAL.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					ED.EvnDirection_Num as REFERRAL_NUMBER,
					(
						cast(L.Lpu_f003mcod as varchar)+
						cast(year(ED.EvnDirection_setDT) as varchar)+
						right('000000'+cast(ED.EvnDirection_Num as varchar), 6)
					) as NOM_NAP,
					convert(varchar(10), ED.EvnDirection_setDT, 120) as DATE,
					L.Lpu_f003mcod as REFERRAL_LPU,
					--coalesce(fBuildingCode.Value, fLB.LpuBuilding_Code) as BRANCH,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(fLS.LpuSection_Code, 2)
						else coalesce(fBuildingCode.Value, fLB.LpuBuilding_Code)
					end as BRANCH,
					case 
						when ESC.EvnStatusCause_Code = 18 then 0
						when ESC.EvnStatusCause_Code = 22 then 1
						when ESC.EvnStatusCause_Code = 1 then 2
						when ESC.EvnStatusCause_Code = 5 then 3
						else 5
					end as REASON,
					case when dbo.getRegion() = 10
						then 2 else 1
					end as CANCEL_SOURSE,
					fL.Lpu_f003mcod as CANCEL_CODE,
					convert(varchar(10), ED.EvnDirection_failDT, 120) as DATE_CANCEL,
					0 as CANCEL_TYPE,
					null as CANCEL_DESCRIPTION,
					ED.Person_id as PATIENT
					-- end select
				from
					-- from
					v_EvnDirection ED with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = ED.Lpu_id
					inner join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
					left join v_MedStaffFact fMSF with(nolock) on fMSF.MedStaffFact_id = {$msf}
					left join v_Lpu fL with(nolock) on fL.Lpu_id = coalesce(fMSF.Lpu_id, ED.Lpu_cid, ED.Lpu_id)
					left join v_LpuSection fLS with(nolock) on fLS.LpuSection_id = fMSF.LpuSection_id
					left join v_LpuBuilding fLB with(nolock) on fLB.LpuBuilding_id = fMSF.LpuBuilding_id
					outer apply (
						select top 1 ASV.AttributeSignValue_id
						from v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
						where ASign.AttributeSign_Code = 1
						and ASV.AttributeSignValue_TablePKey = fLS.LpuSection_id
						and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
						order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
					) fASV
					outer apply (
						select top 1 AV.AttributeValue_ValueString as Value
						from v_AttributeValue AV with(nolock)
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
						where AV.AttributeSignValue_id = fASV.AttributeSignValue_id
						and A.Attribute_SysNick like 'Building_Code'
					) fBuildingCode
					outer apply (
						select top 1 ESH.*
						from v_EvnStatusHistory ESH with(nolock)
						where 
							ESH.Evn_id = ED.EvnDirection_id 
							and ESH.EvnStatusCause_id is not NULL -- по #PROMEDWEB-10556 - так как в v_EvnStatusHistory берутся статусы с EvnStatusCause_id=NULL
						order by ESH.EvnStatusHistory_begDate desc
					) ESH
					outer apply (
						select top 1 ESCL.EvnStatusCause_id
						from v_EvnStatusCauseLink ESCL with(nolock)
						left join v_EvnStatusCause ESC with(nolock) on ESC.EvnStatusCause_id = ESCL.EvnStatusCause_id
						where ESCL.EvnStatusCauseLink_id = ED.DirFailType_id
						and ESC.EvnStatusCause_Code in (1,5,18,22)
						order by ESCL.EvnStatusCauseLink_id
					) ESCL
					left join v_EvnStatusCause ESC with(nolock) on ESC.EvnStatusCause_id = isnull(ESH.EvnStatusCause_id, ESCL.EvnStatusCause_id)
				
					left join {$tmpTable} CANCEL_HOSPITALISATION_REFERRAL on CANCEL_HOSPITALISATION_REFERRAL.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION_REFERRAL'
						and CANCEL_HOSPITALISATION_REFERRAL.ObjectID = ED.EvnDirection_id
					outer apply (
						select case
							when CANCEL_HOSPITALISATION_REFERRAL.ID is null then 'Insert'
							when CANCEL_HOSPITALISATION_REFERRAL.ID is not null and CANCEL_HOSPITALISATION_REFERRAL.DATE <= ED.EvnDirection_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and ED.DirType_id in (1,5)
					and ED.EvnDirection_failDT is not null
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filters}
					-- end where
				order by
					-- order by
					ED.Lpu_id
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		// по задаче #197576
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : [];
		}
		
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
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesInSQL = $this->getPackageTypes(['HOSPITALISATION'], true, true);

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as H_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and EPS.EvnPS_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				declare @region int = dbo.getRegion();
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					EPS.EvnPS_id as ObjectID,
					EPS.EvnPS_id as H_ID,
					isnull(HOSPITALISATION.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as REFERRAL_NUMBER,
					(
						cast(isnull(sL.Lpu_f003mcod,'000000') as varchar)+
						cast(year(isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT)) as varchar)+
						right('000000'+cast(isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as varchar), 6)
					) as NOM_NAP,
					convert(varchar(10), isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 120) as REFERRAL_DATE,
					isnull(sL.Lpu_f003mcod,'000000') as REFERRAL_MO,
					--sLB.LpuBuilding_Code as REFERRAL_BRANCH,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(isnull(LS.LpuSection_Code,'00'), 2)
						else sLB.LpuBuilding_Code 
					end as REFERRAL_BRANCH,
					L.Lpu_f003mcod as MO,
					--LB.LpuBuilding_Code as BRANCH,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(isnull(sLS.LpuSection_Code,'00'), 2)
						else LB.LpuBuilding_Code
					end as BRANCH,
					LS.LpuSection_Code as DIVISION,
					case 
						when @region = 10 and PT.PrehospType_Code = 1 then 1
						when @region = 10 then 2
						when PT.PrehospType_Code = 1 then 0 
						else 1 
					end as FORM_MEDICAL_CARE,
					convert(varchar(10), EPS.EvnPS_setDT, 120) as HOSPITALISATION_DATE,
					convert(varchar(19), EPS.EvnPS_setDT, 126) as HOSPITALISATION_TIME,
					(
						right('0'+cast(datepart(hour, EPS.EvnPS_setDT) as varchar), 2)+'-'+
						right('0'+cast(datepart(minute, EPS.EvnPS_setDT) as varchar), 2)
					) as HOSPITALISATION_TIME_STR,
					PolisType.PolisType_CodeF008 as POLICY_TYPE,
					nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
					case when PolisType.PolisType_CodeF008 = 3
						then PS.Person_EdNum else Polis.Polis_Num
					end as POLIS_NUMBER,
					Smo.Orgsmo_f002smocod as SMO,
					upper(rtrim(PS.Person_SurName)) as LAST_NAME,
					upper(rtrim(PS.Person_FirName)) as FIRST_NAME,
					upper(rtrim(PS.Person_SecName)) as FATHER_NAME,
					case when Sex.Sex_fedid = 1 then 10301 else 10302 end as SEX,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
					LSP.LpuSectionProfile_Code as STRUCTURE_BED,
					fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
					LSBS.LpuSectionBedState_id as DLSB,
					case
						when dbo.getRegion() <> 10 then null
						when LU.LpuUnitType_SysNick like 'stac' then 1
						when LU.LpuUnitType_SysNick like 'dstac' then 2
						when LU.LpuUnitType_SysNick like 'pstac' then 2
						when LU.LpuUnitType_SysNick like 'hstac' then 2
					end as CARETYPE,
					EPS.EvnPS_NumCard as MED_CARD_NUMBER,
					D.Diag_Code as MKB,
					D.Diag_Name as DIAGNOSIS,
					EPS.Person_id as PATIENT
					-- end select
				from
					-- from
					v_EvnPS EPS with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = EPS.Lpu_id
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPS.LpuSection_id
					inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
					inner join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
					inner join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
					inner join v_Diag D with(nolock) on D.Diag_id = EPS.Diag_id
					inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = EPS.PersonEvn_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PolisType with(nolock) on PolisType.PolisType_id = Polis.PolisType_id
					left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
					left join v_EvnDirection ED with(nolock) on  ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_LpuSection sLS with(nolock) on sLS.LpuSection_id = isnull(ED.LpuSection_id, EPS.LpuSection_did)
					left join v_Lpu sL with(nolock) on sL.Lpu_id = coalesce(ED.Lpu_sid, EPS.Lpu_did, sLS.Lpu_id)
					left join v_LpuBuilding sLB with(nolock) on sLB.LpuBuilding_id = sLS.LpuBuilding_id
					outer apply (
						select top 1 LSBS.*
						from v_LpuSectionBedState LSBS with(nolock)
						where LSBS.LpuSection_id = EPS.LpuSection_id
						and EPS.EvnPS_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, EPS.EvnPS_setDate)
						order by LSBS.LpuSectionBedState_begDate desc
					) LSBS
					left join {$tmpTable} HOSPITALISATION with(nolock) on HOSPITALISATION.PACKAGE_TYPE in ({$packageTypesInSQL})
						and HOSPITALISATION.ObjectID = EPS.EvnPS_id
					outer apply (
						select case
							when HOSPITALISATION.ID is null then 'Insert'
							when HOSPITALISATION.ID is not null and HOSPITALISATION.DATE <= EPS.EvnPS_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and (dbo.getRegion() != 10 or PT.PrehospType_Code = 1)
					and EPS.PrehospWaifRefuseCause_id is null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_EXTRHOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		// по задаче #197576
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : [];
		}
		
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
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesInSQL = $this->getPackageTypes(['EXTRHOSPITALISATION'], true, true);

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as H_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and EPS.EvnPS_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				declare @region int = dbo.getRegion();
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					EPS.EvnPS_id as ObjectID,
					EPS.EvnPS_id as H_ID,
					isnull(EXTRHOSPITALISATION.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as REFERRAL_NUMBER,
					(
						cast(isnull(sL.Lpu_f003mcod,'000000') as varchar)+
						cast(year(isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT)) as varchar)+
						right('000000'+cast(isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as varchar), 6)
					) as NOM_NAP,
					convert(varchar(10), isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 120) as REFERRAL_DATE,
					isnull(sL.Lpu_f003mcod,'000000') as REFERRAL_MO,
					--sLB.LpuBuilding_Code as REFERRAL_BRANCH,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(LS.LpuSection_Code, 2)
						else sLB.LpuBuilding_Code 
					end as REFERRAL_BRANCH,
					L.Lpu_f003mcod as MO,
					--LB.LpuBuilding_Code as BRANCH,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(sLS.LpuSection_Code, 2)
						else LB.LpuBuilding_Code
					end as BRANCH,
					LS.LpuSection_Code as DIVISION,
					case 
						when @region = 10 and PT.PrehospType_Code = 1 then 1
						when @region = 10 then 2
						when PT.PrehospType_Code = 1 then 0 
						else 1
					end as FORM_MEDICAL_CARE,
					convert(varchar(10), EPS.EvnPS_setDT, 120) as HOSPITALISATION_DATE,
					convert(varchar(19), EPS.EvnPS_setDT, 126) as HOSPITALISATION_TIME,
					(
						right('0'+cast(datepart(hour, EPS.EvnPS_setDT) as varchar), 2)+'-'+
						right('0'+cast(datepart(minute, EPS.EvnPS_setDT) as varchar), 2)
					) as HOSPITALISATION_TIME_STR,
					PolisType.PolisType_CodeF008 as POLICY_TYPE,
					nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
					case when PolisType.PolisType_CodeF008 = 3
						then PS.Person_EdNum else Polis.Polis_Num
					end as POLIS_NUMBER,
					Smo.Orgsmo_f002smocod as SMO,
					SmoRgn.KLAdr_Ocatd as SMO_OKATO,
					left(SmoRgn.KLAdr_Ocatd, 5) as ST_OKATO,
					upper(rtrim(PS.Person_SurName)) as LAST_NAME,
					upper(rtrim(PS.Person_FirName)) as FIRST_NAME,
					upper(rtrim(PS.Person_SecName)) as FATHER_NAME,
					case when Sex.Sex_fedid = 1 then 10301 else 10302 end as SEX,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
					LSP.LpuSectionProfile_Code as STRUCTURE_BED,
					fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
					LSBS.LpuSectionBedState_id as DLSB,
					case
						when dbo.getRegion() <> 10 then null
						when LU.LpuUnitType_SysNick like 'stac' then 1
						when LU.LpuUnitType_SysNick like 'dstac' then 2
						when LU.LpuUnitType_SysNick like 'pstac' then 2
						when LU.LpuUnitType_SysNick like 'hstac' then 2
					end as CARETYPE,
					EPS.EvnPS_NumCard as MED_CARD_NUMBER,
					D.Diag_Code as MKB,
					D.Diag_Name as DIAGNOSIS,
					EPS.Person_id as PATIENT
					-- end select
				from
					-- from
					v_EvnPS EPS with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = EPS.Lpu_id
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPS.LpuSection_id
					inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
					inner join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
					inner join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
					inner join v_Diag D with(nolock) on D.Diag_id = EPS.Diag_id
					inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = EPS.PersonEvn_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PolisType with(nolock) on PolisType.PolisType_id = Polis.PolisType_id
					left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
					left join v_KLArea SmoRgn with(nolock) on SmoRgn.KLArea_id = Smo.KLRgn_id
					left join v_EvnDirection ED with(nolock) on  ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_LpuSection sLS with(nolock) on sLS.LpuSection_id = isnull(ED.LpuSection_id, EPS.LpuSection_did)
					left join v_Lpu sL with(nolock) on sL.Lpu_id = coalesce(ED.Lpu_sid, EPS.Lpu_did, sLS.Lpu_id)
					left join v_LpuBuilding sLB with(nolock) on sLB.LpuBuilding_id = sLS.LpuBuilding_id
					outer apply (
						select top 1 LSBS.*
						from v_LpuSectionBedState LSBS with(nolock)
						where LSBS.LpuSection_id = EPS.LpuSection_id
						and EPS.EvnPS_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, EPS.EvnPS_setDate)
						order by LSBS.LpuSectionBedState_begDate desc
					) LSBS
					left join {$tmpTable} EXTRHOSPITALISATION with(nolock) on EXTRHOSPITALISATION.PACKAGE_TYPE in ({$packageTypesInSQL})
						and EXTRHOSPITALISATION.ObjectID = EPS.EvnPS_id
					outer apply (
						select case
							when EXTRHOSPITALISATION.ID is null then 'Insert'
							when EXTRHOSPITALISATION.ID is not null and EXTRHOSPITALISATION.DATE <= EPS.EvnPS_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and PT.PrehospType_Code <> 1
					and EPS.PrehospWaifRefuseCause_id is null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_CANCEL_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		// по задаче #197576
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : [];
		}
		
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
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as CH_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION'
					and (
						EPS.EvnPS_id is null or
						EPS.PrehospWaifRefuseCause_id is null
					)
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					EPS.EvnPS_id as ObjectID,
					EPS.EvnPS_id as CH_ID,
					isnull(CANCEL_HOSPITALISATION.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), EPS.EvnPS_setDT, 120) as DATE,
					EPS.EvnPS_id as HOSPITALISATION_ID,
					--pLB.LpuBuilding_Code as BRANCH,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(pLS.LpuSection_Code, 2)
						else pLB.LpuBuilding_Code
					end as BRANCH,
					0 as REASON,
					1 as CANCEL_SOURSE,
					convert(varchar(10), EPS.EvnPS_OutcomeDT, 120) as DATE_CANCEL,
					1 as CANCEL_TYPE,
					LB.LpuBuilding_Code as HOSPITALISATION_DIVISION,
					EPS.EvnPS_NumCard as MED_CARD_NUMBER,
					EPS.Person_id as PATIENT
					-- end select
				from
					-- from
					v_EvnPS EPS with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = EPS.Lpu_id
					inner join v_LpuSection pLS with(nolock) on pLS.LpuSection_id = EPS.LpuSection_pid
					inner join v_LpuBuilding pLB with(nolock) on pLB.LpuBuilding_id = pLS.LpuBuilding_id
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = pLB.LpuBuilding_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuSection_id
					left join {$tmpTable} CANCEL_HOSPITALISATION with(nolock) on CANCEL_HOSPITALISATION.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION' 
						and CANCEL_HOSPITALISATION.ObjectID = EPS.EvnPS_id
					outer apply (
						select case
							when CANCEL_HOSPITALISATION.ID is null then 'Insert'
							when CANCEL_HOSPITALISATION.ID is not null and CANCEL_HOSPITALISATION.DATE <= EPS.EvnPS_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and EPS.PrehospWaifRefuseCause_id is not null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_MOTION_IN_HOSPITAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		// по задаче #197576
		if ($procDataType != 'Insert') {
			return $returnType == 'count' ? 0 : [];
		}
		
		$region = $this->regionNick;
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ES.EvnSection_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and ES.EvnSection_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($region == 'kareliya') {
			$filters .= " and ES.EvnSection_disDate is not null";
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as MIH_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
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
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				declare @region int = dbo.getRegion();
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					ES.EvnSection_id as ObjectID,
					ES.EvnSection_id as MIH_ID,
					ProcDataType.Value as OPERATIONTYPE,
					EPS.EvnPS_id as HOSPITALISATION_ID,
					isnull(MOTION_IN_HOSPITAL.GUID, newid()) as GUID,
					convert(varchar(10), @date, 120) as DATA,
					isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as REFERRAL_NUMBER,
					(
						cast(isnull(sL.Lpu_f003mcod,'000000') as varchar)+
						cast(year(isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT)) as varchar)+
						right('000000'+cast(isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as varchar), 6)
					) as NOM_NAP,
					convert(varchar(10), isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 120) as REFERRAL_DATE,
					case 
						when @region = 10 and PT.PrehospType_Code = 1 then 1
						when @region = 10 then 2
						when PT.PrehospType_Code = 1 then 0 
						else 1
					end as HOSPITALISATION_TYPE,
					--LB.LpuBuilding_Code as BRANCH,
					case 
						--  #195757 - по требованию Макаровой Елены (Карелия)
						when @region = 10 then left(LS.LpuSection_Code, 2)
						else LB.LpuBuilding_Code
					end as BRANCH,
					LS.LpuSection_Code as DIVISION,
					LSP.LpuSectionProfile_Code as STRUCTURE_BED,
					fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
					LSBS.LpuSectionBedState_id as DLSB,
					case 
						when dbo.getRegion() <> 10 then null
						when LU.LpuUnitType_SysNick like 'stac' then 1
						when LU.LpuUnitType_SysNick like 'dstac' then 2
						when LU.LpuUnitType_SysNick like 'pstac' then 2
						when LU.LpuUnitType_SysNick like 'hstac' then 2
					end as CARETYPE,
					convert(varchar(10), ES.EvnSection_setDT, 120) as DATE_IN,
					convert(varchar(10), ES.EvnSection_disDT, 120) as DATE_OUT,
					EPS.EvnPS_NumCard as MED_CARD_NUMBER,
					convert(varchar(10), EPS.EvnPS_setDT, 120) as HOSPITALISATION_DATE,
					PolisType.PolisType_CodeF008 as POLICY_TYPE,
					nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
					case when PolisType.PolisType_CodeF008 = 3
						then PS.Person_EdNum else Polis.Polis_Num
					end as POLIS_NUMBER,
					Smo.Orgsmo_f002smocod as SMO,
					upper(rtrim(PS.Person_SurName)) as LAST_NAME,
					upper(rtrim(PS.Person_FirName)) as FIRST_NAME,
					upper(rtrim(PS.Person_SecName)) as FATHER_NAME,
					case when Sex.Sex_fedid = 1 then 10301 else 10302 end as SEX,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
					ES.Person_id as PATIENT
					-- end select
				from
					-- from
					v_Lpu L with(nolock)
					inner join v_EvnSection ES with(nolock) on L.Lpu_id = ES.Lpu_id
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
					inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
					inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
					inner join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
					inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid 
					inner join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
					inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = EPS.PersonEvn_id 
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PolisType with(nolock) on PolisType.PolisType_id = Polis.PolisType_id
					left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
					left join v_EvnDirection ED with(nolock) on ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_LpuSection sLS with(nolock) on sLS.LpuSection_id = isnull(ED.LpuSection_id, EPS.LpuSection_did)
					left join v_Lpu sL with(nolock) on sL.Lpu_id = coalesce(ED.Lpu_sid, EPS.Lpu_did, sLS.Lpu_id)
					outer apply (
						select top 1 LSBS.*
						from v_LpuSectionBedState LSBS with(nolock)
						where LSBS.LpuSection_id = EPS.LpuSection_id
						and EPS.EvnPS_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, EPS.EvnPS_setDate)
						order by LSBS.LpuSectionBedState_begDate desc
					) LSBS
					left join {$tmpTable} MOTION_IN_HOSPITAL with(nolock) on MOTION_IN_HOSPITAL.PACKAGE_TYPE = 'MOTION_IN_HOSPITAL' 
						and MOTION_IN_HOSPITAL.ObjectID = ES.EvnSection_id
					outer apply (
						select case
							when MOTION_IN_HOSPITAL.ID is null then 'Insert'
							when MOTION_IN_HOSPITAL.ID is not null and MOTION_IN_HOSPITAL.DATE <= ES.EvnSection_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and (
						PT.PrehospType_SysNick <> 'plan'
						or ED.EvnDirection_Num is not null
						or EPS.EvnDirection_Num is not null
					)
					and ES.EvnSection_setDT is not null
					and EPS.EvnPS_setDT is not null
					and EPS.EvnPS_NumCard is not null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_ONKOTRAMADOL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ER.EvnRecept_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and ER.EvnRecept_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as REG_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnRecept ER with(nolock) on ER.EvnRecept_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'ONKOTRAMADOL'
					and ER.EvnRecept_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					ER.EvnRecept_id as ObjectID,
					ER.EvnRecept_id as REG_ID,
					ProcDataType.Value as OPERATIONTYPE,
					isnull(ONKOTRAMADOL.GUID, newid()) as GUID,
					convert(varchar(10), @date, 120) as DATA,
					P.Person_id as ID_PAC,
					P.BDZ_id as BDZ_ID,
					Diag.Diag_Code as DS,
					datepart(quarter, ER.EvnRecept_setDT) as KV,
					datepart(year, ER.EvnRecept_setDT) as YEAR,
					null as RECEPTCOMMENT
					-- end select
				from
					-- from
					v_EvnRecept ER with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = ER.Lpu_id
					inner join Person P with(nolock) on P.Person_id = ER.Person_id
					inner join v_Diag Diag with(nolock) on Diag.Diag_id = ER.Diag_id
					left join rls.v_Drug D with(nolock) on D.Drug_id = ER.Drug_rlsid
					inner join rls.v_DrugComplexMnn DCM with (nolock) on DCM.DrugComplexMnn_id = coalesce(ER.DrugComplexMnn_id, D.DrugComplexMnn_id)
					inner join rls.v_DrugComplexMnnName DCMN with (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
					inner join rls.v_ACTMATTERS A with(nolock) on A.ACTMATTERS_ID = DCMN.ACTMATTERS_id
					left join {$tmpTable} ONKOTRAMADOL with(nolock) on ONKOTRAMADOL.PACKAGE_TYPE = 'ONKOTRAMADOL' 
						and ONKOTRAMADOL.ObjectID = ER.EvnRecept_id
					outer apply (
						select case
							when ONKOTRAMADOL.ID is null then 'Insert'
							when ONKOTRAMADOL.ID is not null and ONKOTRAMADOL.DATE <= ER.EvnRecept_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = 'Insert'
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and exists(
						select * from v_MorbusDiag MD with(nolock)
						inner join v_MorbusType MT with(nolock) on MT.MorbusType_id = MD.MorbusType_id
						where MD.Diag_id = Diag.Diag_id and MT.MorbusType_SysNick = 'onko'
					)
					and A.RUSNAME = 'Трамадол'
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_ONKODRUG($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ER.EvnRecept_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and ER.EvnRecept_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as REG_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnRecept ER with(nolock) on ER.EvnRecept_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'ONKODRUG'
					and ER.EvnRecept_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					ER.EvnRecept_id as ObjectID,
					ER.EvnRecept_id as REG_ID,
					ProcDataType.Value as OPERATIONTYPE,
					isnull(ONKODRUG.GUID, newid()) as GUID,
					convert(varchar(10), @date, 120) as DATA,
					P.Person_id as ID_PAC,
					P.BDZ_id as BDZ_ID,
					Diag.Diag_Code as DS,
					datepart(quarter, ER.EvnRecept_setDT) as KV,
					datepart(year, ER.EvnRecept_setDT) as YEAR,
					null as RECEPTCOMMENT
					-- end select
				from
					-- from
					v_EvnRecept ER with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = ER.Lpu_id
					inner join Person P with(nolock) on P.Person_id = ER.Person_id
					inner join v_Diag Diag with(nolock) on Diag.Diag_id = ER.Diag_id
					left join v_Drug D with(nolock) on D.Drug_id = ER.Drug_id
					left join rls.v_Drug rlsD with(nolock) on rlsD.Drug_id = isnull(ER.Drug_rlsid, D.RLS_id)
					inner join rls.v_DrugComplexMnn DCM with (nolock) on DCM.DrugComplexMnn_id = coalesce(ER.DrugComplexMnn_id, rlsD.DrugComplexMnn_id)
					inner join rls.v_DrugComplexMnnName DCMN with (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
					inner join rls.v_ACTMATTERS A with(nolock) on A.ACTMATTERS_ID = DCMN.ACTMATTERS_id
					left join {$tmpTable} ONKODRUG with(nolock) on ONKODRUG.PACKAGE_TYPE = 'ONKODRUG' 
						and ONKODRUG.ObjectID = ER.EvnRecept_id
					outer apply (
						select case
							when ONKODRUG.ID is null then 'Insert'
							when ONKODRUG.ID is not null and ONKODRUG.DATE <= ER.EvnRecept_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = 'Insert'
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and exists(
						select * from v_MorbusDiag MD with(nolock)
						inner join v_MorbusType MT with(nolock) on MT.MorbusType_id = MD.MorbusType_id
						where MD.Diag_id = Diag.Diag_id and MT.MorbusType_SysNick = 'onko'
					)
					and A.NARCOGROUPID = 2
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_PERSCANCER($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and PR.PersonRegister_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and PR.PersonRegister_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ObjectID as REG_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join PersonRegister PR with(nolock) on PR.PersonRegister_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'PERSCANCER'
					and (
						PR.PersonRegister_id is null or
						PR.PersonRegister_delDT is not null
					)
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date datetime = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					PR.PersonRegister_id as REG_ID,
					PR.PersonRegister_id as ObjectID,
					ProcDataType.Value as OPERATIONTYPE,
					isnull(PERSCANCER.GUID, newid()) as GUID,
					convert(varchar(10), @date, 120) as DATA,
					P.BDZ_id as BDZ_ID,
					Diag.Diag_Code as DS,
					convert(varchar(10), PR.PersonRegister_setDate, 120) as DATE_IN,
					convert(varchar(10), PR.PersonRegister_disDate, 120) as DATE_OUT,
					PROutCause.PersonRegisterOutCause_Code as CAUSEOUT,
					null as CANCERCOMMENT
					-- end select
				from
					-- from
					PersonRegister PR with(nolock)
					inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = PR.Person_id
					inner join Person P with(nolock) on P.Person_id = PS.Person_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = PR.Lpu_iid
					left join v_Diag Diag with(nolock) on Diag.Diag_id = PR.Diag_id
					left join v_PersonRegisterOutCause PROutCause with(nolock) on PROutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join {$tmpTable} PERSCANCER with(nolock) on PERSCANCER.PACKAGE_TYPE = 'PERSCANCER'
						and PERSCANCER.ObjectID = PR.PersonRegister_id
					outer apply (
						select case
							when PERSCANCER.ID is null then 'Insert'
							when PERSCANCER.ID is not null and PERSCANCER.DATE <= PR.PersonRegister_updDT then 'Update'
							when PERSCANCER.ID is not null and PERSCANCER.DATE <= PR.PersonRegister_delDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					PR.PersonRegister_delDT is null
					and PRT.PersonRegisterType_SysNick = 'onko'
					and ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filters}
					-- end where
				order by
					-- order by
					L.Lpu_id,
					P.Person_id
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_HTMC_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and EDH.EvnDirectionHTM_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and EDH.EvnDirectionHTM_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					LH.LpuHTM_f003mcod as CODE_MO_TO,
					EDH.EvnDirectionHTM_id as HTMC_ID,
					PL.ObjectID,
					PL.ID as RECEPT_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_EvnDirectionHTM EDH with(nolock) on EDH.EvnDirectionHTM_id = PL.ObjectID
					inner join EvnDirectionHTM EDHD with(nolock) on EDHD.EvnDirectionHTM_id = PL.ObjectID
					inner join v_LpuHTM LH with(nolock) on LH.LpuHTM_id = EDHD.LpuHTM_id
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'HTMC_REFERRAL'
					and EDH.EvnDirectionHTM_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					LH.LpuHTM_f003mcod as CODE_MO_TO,
					EDH.EvnDirectionHTM_id as ObjectID,
					EDH.EvnDirectionHTM_id as HTMC_ID,
					ProcDataType.Value as OPERATIONTYPE,
					isnull(HTMC_REFERRAL.GUID, newid()) as GUID,
					convert(varchar(10), @date, 120) as DATA,
					P.BDZ_id as BDZ_ID,
					EDH.EvnDirectionHTM_Num as REFERRAL_NUMBER,
					convert(varchar(10), EDH.EvnDirectionHTM_directDate, 120) as REFERRAL_DATE,
					EDH.EvnDirectionHTM_TalonNum as TALON_NUMBER,
					convert(varchar(10), EDH.EvnDirectionHTM_setDate, 120) as TALON_DATE,
					MSF.Person_Snils as DOC_CODE,
					(
						left(MSF.Person_Snils, 3) + '-' + substring(MSF.Person_Snils, 4, 3) + '-' + 
						substring(MSF.Person_Snils, 7, 3) + ' ' + right(MSF.Person_Snils, 2)
					) as DOC_CODE_14,
					HTMCC.HTMedicalCareClass_Code as HTCCLASS,
					EDH.EvnDirectionHTM_VKProtocolNum as PROTNUM,
					convert(varchar(10), EDH.EvnDirectionHTM_VKProtocolDate, 120) as PROTDATE,
					coalesce(EVK.EvnVK_ConclusionDescr, EVK.EvnVK_ExpertDescr) as PROTCOMMENT,
					EDH.EvnDirectionHTM_Descr as HTMCCOMMENT
					-- end select
				from
					-- from
					v_EvnDirectionHTM EDH with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = EDH.Lpu_sid
					inner join v_LpuHTM LH with(nolock) on LH.LpuHTM_id = EDH.LpuHTM_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = EDH.Person_id
					inner join Person P with(nolock) on P.Person_id = PS.Person_id
					inner join v_EvnPS EPS with(nolock) on EPS.EvnDirection_id = EDH.EvnDirectionHTM_id
					outer apply (
						select top 1 ES.*
						from v_EvnSection ES with(nolock)
						where ES.EvnSection_pid = EPS.EvnPS_id
						and ES.HTMedicalCareClass_id is not null
						order by ES.EvnSection_setDT desc
					) ES
					left join v_HTMedicalCareClass HTMCC with(nolock) on HTMCC.HTMedicalCareClass_id = ES.HTMedicalCareClass_id
					left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = EDH.MedStaffFact_id
					left join v_EvnVK EVK with(nolock) on EVK.EvnVK_id = EDH.EvnDirectionHTM_pid
					left join {$tmpTable} HTMC_REFERRAL with(nolock) on HTMC_REFERRAL.PACKAGE_TYPE = 'HTMC_REFERRAL'
						and HTMC_REFERRAL.ObjectID = EDH.EvnDirectionHTM_id
					outer apply (
						select case
							when HTMC_REFERRAL.ID is null then 'Insert'
							when HTMC_REFERRAL.ID is not null and HTMC_REFERRAL.DATE <= EDH.EvnDirectionHTM_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and nullif(nullif(LH.LpuHTM_f003mcod,'0'),'') is not null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_ECO_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and EDE.EvnDirectionEco_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and EDE.EvnDirectionEco_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					dL.Org_f003mcod as CODE_MO_TO,
					PL.ObjectID,
					PL.ID as HR_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					inner join v_EvnDirectionEco EDE with(nolock) on EDE.EvnDirectionEco_id = PL.ObjectID
					left join v_Org dL with(nolock) on dL.Org_id = EDE.Org_id
					left join v_Evn_del EDel with(nolock) on EDel.Evn_id = EDE.EvnDirectionEco_id
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'ECO_REFERRAL'
					and isnull(EDE.EvnDirectionEco_failDT, EDel.Evn_delDT) > PL.DATE
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					case
						when L.Lpu_id = 13002457 then '0'
						else L.Lpu_f003mcod
					end as CODE_MO,
					LD.Org_f003mcod as CODE_MO_TO,
					EDE.EvnDirectionEco_id as ECO_ID,
					EDE.EvnDirectionEco_id as ObjectID,
					isnull(ECO_REFERRAL.GUID, newid()) as GUID,
					P.BDZ_id as BDZ_ID,
					EDE.EvnDirectionEco_Num as REFERRAL_NUMBER,
					convert(varchar(10), EDE.EvnDirectionEco_setDT, 120) as REFERRAL_DATE,
					MSF.Person_Snils as DOC_CODE,
					EDE.EvnDirectionEco_NumVKMZ as PROTNUM,
					convert(varchar(10), EDE.EvnDirectionEco_VKMZDate, 120) as PROTDATE,
					EDE.EvnDirectionEco_CommentVKMZ as PROTCOMMENT,
					EDE.EvnDirectionEco_Comment as ECOCOMMENT
					-- end select
				from
					-- from
					v_EvnDirectionEco EDE with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = EDE.Lpu_sid
					inner join v_Org LD with(nolock) on LD.Org_id = EDE.Org_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = EDE.Person_id
					inner join Person P with(nolock) on P.Person_id = PS.Person_id
					left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = EDE.MedStaffFact_id
					left join {$tmpTable} ECO_REFERRAL with(nolock) on ECO_REFERRAL.PACKAGE_TYPE = 'ECO_REFERRAL'
						and ECO_REFERRAL.ObjectID = EDE.EvnDirectionEco_id
					outer apply (
						select case
							when ECO_REFERRAL.ID is null then 'Insert'
							when ECO_REFERRAL.ID is not null and ECO_REFERRAL.DATE <= EDE.EvnDirectionEco_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and (L.Lpu_id = 13002457 or nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null)
					and nullif(nullif(LD.Org_f003mcod,'0'),'') is not null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_HEMODIAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and MN.MorbusNephro_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and MN.MorbusNephro_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as REG_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					inner join MorbusNephro MN with(nolock) on MN.MorbusNephro_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'HEMODIAL'
					and MN.MorbusNephro_delDT > PL.DATE
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					L.Lpu_f003mcod as CODE_MO,
					MN.MorbusNephro_id as REG_ID,
					MN.MorbusNephro_id as ObjectID,
					isnull(HEMODIAL.GUID, newid()) as GUID,
					P.BDZ_id as BDZ_ID,
					convert(varchar(10), MNDF.MorbusNephroDialysis_begDT, 120) as DATE_IN,
					convert(varchar(10), MNDL.MorbusNephroDialysis_endDT, 120) as DATE_OUT,
					PROutCause.PersonRegisterOutCause_Code as CAUSEOUT,
					'' as DIALCOMMENT
					-- end select
				from
					-- from
					v_MorbusNephro MN with(nolock)
					cross apply (
						select top 1
							MorbusNephroDialysis_begDT
						from
							v_MorbusNephroDialysis MND with(nolock)
						where
							MN.MorbusNephro_id = MND.MorbusNephro_id
						order by
							MND.MorbusNephroDialysis_begDT asc
					) MNDF
					cross apply (
						select top 1
							MorbusNephroDialysis_endDT,
							PersonRegisterOutCause_id,
							Lpu_id
						from
							v_MorbusNephroDialysis MND with(nolock)
						where
							MN.MorbusNephro_id = MND.MorbusNephro_id
						order by
							MND.MorbusNephroDialysis_begDT desc
					) MNDL
					inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MN.MorbusBase_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = MNDL.Lpu_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = MB.Person_id
					inner join Person P with(nolock) on P.Person_id = PS.Person_id
					left join v_PersonRegisterOutCause PROutCause with(nolock) on PROutCause.PersonRegisterOutCause_id = MNDL.PersonRegisterOutCause_id
					left join {$tmpTable} HEMODIAL with(nolock) on HEMODIAL.PACKAGE_TYPE = 'HEMODIAL'
						and HEMODIAL.ObjectID = MN.MorbusNephro_id
					outer apply (
						select case
							when HEMODIAL.ID is null then 'Insert'
							when HEMODIAL.ID is not null and HEMODIAL.DATE <= MN.MorbusNephro_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
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
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_PERSHEPATITIS($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and MHP.MorbusHepatitisPlan_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and MHP.MorbusHepatitisPlan_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.LPU as CODE_MO,
					PL.ObjectID,
					PL.ID as REG_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					inner join MorbusHepatitisPlan MHP with(nolock) on MHP.MorbusHepatitisPlan_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'PERSHEPATITIS'
					and MHP.MorbusHepatitisPlan_delDT > PL.DATE
					and nullif(nullif(PL.LPU,'0'),'') is not null
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					L.Lpu_id,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					L.Lpu_f003mcod as CODE_MO,
					MHP.MorbusHepatitisPlan_id as REG_ID,
					MHP.MorbusHepatitisPlan_id as ObjectID,
					isnull(PERSHEPATITIS.GUID, newid()) as GUID,
					P.BDZ_id as BDZ_ID,
					MHP.MorbusHepatitisPlan_Year as YEAR,
					MHP.MorbusHepatitisPlan_Month as MONTH,
					MCT.MedicalCareType_Code as MCTYPE,
					'' as HEPATITISCOMMENT
					-- end select
				from
					-- from
					v_MorbusHepatitisPlan MHP with(nolock)
					inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHP.MorbusHepatitis_id
					inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MH.MorbusBase_id
					inner join v_Lpu L with(nolock) on L.Lpu_id = MHP.Lpu_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = MB.Person_id
					inner join Person P with(nolock) on P.Person_id = PS.Person_id
					inner join fed.v_MedicalCareType MCT with (nolock) on MCT.MedicalCareType_id = MHP.MedicalCareType_id
					left join {$tmpTable} PERSHEPATITIS with(nolock) on PERSHEPATITIS.PACKAGE_TYPE = 'PERSHEPATITIS'
						and PERSHEPATITIS.ObjectID = MHP.MorbusHepatitisPlan_id
					outer apply (
						select case
							when PERSHEPATITIS.ID is null then 'Insert'
							when PERSHEPATITIS.ID is not null and PERSHEPATITIS.DATE <= MHP.MorbusHepatitisPlan_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
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
	 * Получение порядка обработки данных
	 * @return array поряодок выполнение пакетов => связные пакеты, для формирования временной таблицы
	 */
	function getProcConfig() {
		$region = $this->regionNick;
		$packages = array();

		$packages['DISTRICT'] = 'DISTRICT';
		if (!in_array($region, array('perm'))) {
			$packages['PERSONATTACH'] = array('DISTRICT','PERSONATTACH');
		}
		$packages['PERSONATTACHDISTRICT'] = array('DISTRICT','PERSONATTACHDISTRICT');
		$packages['DISP'] = 'DISP';
		if (in_array($region, array('kareliya'))) {
			$packages['DISPOUT'] = array('DISP','DISPOUT');
		}
		$packages['FREE_BEDS_INFORMATION'] = 'FREE_BEDS_INFORMATION';
		$packages['HOSPITALISATION_REFERRAL'] = 'HOSPITALISATION_REFERRAL';
		$packages['CANCEL_HOSPITALISATION_REFERRAL'] = 'CANCEL_HOSPITALISATION_REFERRAL';
		$packages['HOSPITALISATION'] = 'HOSPITALISATION';
		if (in_array($region, array('kareliya'))) {
			$packages['EXTRHOSPITALISATION'] = 'EXTRHOSPITALISATION';
		}
		$packages['CANCEL_HOSPITALISATION'] = 'CANCEL_HOSPITALISATION';
		$packages['MOTION_IN_HOSPITAL'] = 'MOTION_IN_HOSPITAL';
		if (in_array($region, array('perm'))) {
			$packages['ONKOTRAMADOL'] = 'ONKOTRAMADOL';
			$packages['ONKODRUG'] = 'ONKODRUG';
			$packages['PERSCANCER'] = 'PERSCANCER';
			$packages['HTMC_REFERRAL'] = 'HTMC_REFERRAL';
			$packages['ECO_REFERRAL'] = 'ECO_REFERRAL';
			$packages['HEMODIAL'] = 'HEMODIAL';
			$packages['PERSHEPATITIS'] = 'PERSHEPATITIS';
			$packages['GEBT'] = 'GEBT';
		}

		return array(
			'Insert' => $packages,
			'Update' => $packages,
			'Delete' => array_reverse($packages)
		);
	}

	/**
	 * Получение карты объектов
	 */
	function getObjectMap() {
		return array(
			'DISTRICT' => 'LpuRegion',
			'PERSONATTACH' => 'PersonCard',
			'PERSONATTACHDISTRICT' => 'PersonCard',
			'DISP' => 'PersonDisp',
			'DISPOUT' => 'PersonDisp',
			'PERS' => 'Person',
			'FREE_BEDS_INFORMATION' => 'LpuSection',
			'HOSPITALISATION_REFERRAL' => 'EvnDirection',
			'CANCEL_HOSPITALISATION_REFERRAL' => 'EvnDirection',
			'HOSPITALISATION' => 'EvnPS',
			'EXTRHOSPITALISATION' => 'EvnPS',
			'CANCEL_HOSPITALISATION' => 'EvnPS',
			'MOTION_IN_HOSPITAL' => 'EvnSection',
			'ONKOTRAMADOL' => 'EvnRecept',
			'ONKODRUG' => 'EvnRecept',
			'PERSCANCER' => 'PersonRegister',
			'HTMC_REFERRAL' => 'EvnDirectionHTM',
			'ECO_REFERRAL' => 'EvnDirectionEco',
			'HEMODIAL' => 'MorbusNephro',
			'PERSHEPATITIS' => 'MorbusHepatitisPlan',
			'GEBT' => 'MorbusGEBT',
			'POLIS' => 'ImportPolisData',
			'DOUBLES' => 'ImportDoubles',
			'PERSCHANGE' => 'ImportPersChange',
			'CHECKPERS' => 'ImportCheckPers',
			'IDLEPERSONS' => 'ImportIdlePersons',
		);
	}

	/**
	 * @param array $data
	 * @param ServiceListLog|null $commonLog
	 * @return array
	 * @throws Exception
	 */
	function runPublisher($data, $commonLog = null) {
		set_time_limit(0);

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;
		$objectMap = $this->getObjectMap();

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

		$processPackageTypes = function($packageTypes) use($allowedPackageTypes) {
			if (!is_array($packageTypes)) $packageTypes = array($packageTypes);
			if (!$allowedPackageTypes) return $packageTypes;
			return array_filter($packageTypes, function($packageType) use($allowedPackageTypes) {
				return in_array($packageType, $allowedPackageTypes);
			});
		};

		$procConfig = $this->getProcConfig();
		$package_types = [];
		foreach($procConfig['Insert'] as $object => $packageTypes) {
			if(is_array($allowedPackageTypes) && in_array($object, $allowedPackageTypes) || !is_array($allowedPackageTypes)){
				if (is_array($packageTypes)) {
					$package_types = array_merge($package_types, $packageTypes);
				} else {
					$package_types[] = $packageTypes;
				}
			}
		}

		//$packageTypesInsert = $processPackageTypes($package_types);
		$packageTypesInsert = array_unique($package_types);

		if ($commonLog) {
			$log = $commonLog;
		} else {
			$this->setService('TFOMSAutoInteract');
			$log = new ServiceListLog($this->ServiceList_id, $pmUser_id);
			$resp = $log->start();
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		$this->load->library('textlog', array(
			'file' => 'TFOMSAutoInteract_'.$log->begDT->format('Y-m-d').'.log',
			'format' => 'json',
			'parse_xml' => true
		));

		if (empty($_REQUEST['getDebug']) && !$commonLog) {
			$this->sendImportResponse();
		}

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			$tmpTable = $this->createActualPackageTable();
			$this->fillActualPackagesTable($data, $tmpTable, $allowedPackageTypes, $packageTypesInsert);

			foreach ($this->getProcConfig() as $procDataType => $packagesOrder) {
				if ($allowedProcDataTypes && !in_array($procDataType, $allowedProcDataTypes)) {
					continue;
				}

				foreach($packagesOrder as $object => $packageTypes) {
					$packageTypes = $processPackageTypes([$object]);

					if (count($packageTypes) == 0) continue;

					$method = 'package_'.$object;
					$processCount = 0;
					$start = 0;
					$limit = 500;

					if ($packageLimit && $limit > $packageLimit) {
						$limit = $packageLimit;
					}
					
					$packageData = $this->$method($tmpTable, $procDataType, $data, 'data', $start, $limit);
					if(is_array($packageData)){
						$processCount += count($packageData);
						$start = $processCount;
					
						foreach ($packageTypes as $packageType) {
							foreach ($packageData as $package) {
								$package['MESSAGE_ID'] = $this->GUID();

								list($queue, $channel) = $this->getQueue('publisher', 'common', $packageType);

								$body = $this->createPackageBody($packageType, $package);
								if ($this->isNotSendPackage($packageType, $procDataType, $package['ObjectID'], $body, $log)) {
									$this->checkPackage($packageType, $body);
									if(isset($_REQUEST['getDebug'])){
										echo "<pre>isNotSendPackage(): Не отправляем. Ранее сформирован с ошибкой и больше не менялся.".PHP_EOL;
									}
									continue;
								}

								$properties = $this->createPackageProperties($packageType, $package['MESSAGE_ID']);
								$errors = $this->checkPackage($packageType, $body);

								$packageTypesMap = $this->getPackageTypeMap();
								if(!empty($packageTypesMap) && isset($packageTypesMap[$packageType])){
									$packageTypesRegion = $packageTypesMap[$packageType];
								}else{
									$packageTypesRegion = $packageType;
								}

								$resp = $log->addPackage($objectMap[$object], $package['ObjectID'], $package['MESSAGE_ID'], $package['Lpu_id'], $packageTypesRegion, $procDataType, $body);
								if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
								$packageId = $resp[0]['ServiceListPackage_id'];

								if (count($errors) > 0) {
									$this->textlog->add(['properties' => $properties, 'body' => $body, 'errors' => $errors]);
									$log->setPackageStatus($packageId, 'ErrFormed');
									$log->add(false, array_merge(["Пакет не сформирован:"], $errors), $packageId);
									continue;
								}

								if (!empty($_REQUEST['getDebug']) && !empty($_REQUEST['errors'])) {
									$body = $this->injectErrorsInPackage($body);
								}

								$log->setPackageStatus($packageId, 'Formed');

								try {
									$this->beginTransaction();

									if ($this->allowSaveGUID) {
										$this->sync->saveObjectSynchronLog($packageType, $package['ObjectID'], $package['GUID']);
									}

									$this->textlog->add(array('properties' => $properties, 'body' => $body));

									$msg = new AMQPMessage($body, $properties);
									$channel->basic_publish($msg, '', $queue);

									$log->setPackageStatus($packageId, 'Sent');
									$this->commitTransaction();
								} catch(Exception $e) {
									$this->rollbackTransaction();
									$log->setPackageStatus($packageId, 'ErrSent');
									$log->add(false, ["Ошибка отпавки пакета:", $e->getMessage()], $packageId);
								}
							}
						}
					}

					if ($processCount > 0) {
						$this->fillActualPackagesTable($data, $tmpTable, $allowedPackageTypes, $packageTypes);
					}
				}
			}

			$this->closeConnections();

			if ($log != $commonLog) {
				$log->finish(true);
			}

			restore_exception_handler();
		} catch (Exception $e) {
			restore_exception_handler();

			if (isset($channel)) $channel->close();
			if (isset($connection)) $connection->close();

			$code = $e->getCode();
			$error = $e->getMessage();
			if(!isset($resp[0]['ServiceListPackage_id'])) {
				$resp = $log->addPackage('DummyPackage', null);
				if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			}
			$log->add(false, array("Экспорт данных завершён с ошибкой:", $error), $resp[0]['ServiceListPackage_id']);
			$log->setPackageStatus($resp[0]['ServiceListPackage_id'], 'ErrFormed');
			if ($log != $commonLog) {
				$log->finish(false);
			}

			$response = $this->createError($code, $error);
			$response[0]['ServiceListLog_id'] = $log->getId();

			return $response;
		}

		return array(array(
			'success' => true,
			'ServiceListLog_id' => $log->getId()
		));
	}

    /**
     * @param array $data
     * @param string $queueNick
     * @param ServiceListLog|null $commonLog
     * @return array
     * @throws Exception
     */
	function runConsumer($data, $queueNick, $commonLog = null) {
		set_time_limit(0);

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$this->load->library('textlog', array(
			'file' => 'TFOMSAutoInteract_'.date('Y-m-d').'.log',
			'format' => 'json',
			'parse_xml' => true
		));

		if ($commonLog) {
			$log = $commonLog;
		} else {
			$this->setService('TFOMSAutoInteract');
			$log = new ServiceListLog($this->ServiceList_id, $pmUser_id);
			$resp = $log->start();
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		if (empty($_REQUEST['getDebug']) && !$commonLog) {
			$this->sendImportResponse();
		}

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			list($consumerQueue, $consumerChannel) = $this->getQueue('consumer', $queueNick);

			$publicatePackage = function($procDataType, $packageType, $package) use($log) {
				$objectMap = $this->getObjectMap();
				$package['MESSAGE_ID'] = $this->GUID();
				$package['OPERATIONTYPE'] = $procDataType;
				$package['DATA'] = $package['DATA'] ?? $this->currentDT->format('Y-m-d');
				if (empty($package['Lpu_id'])) $package['Lpu_id'] = null;

				$body = $this->createPackageBody($packageType, $package);
				$properties = $this->createPackageProperties($packageType, $package['MESSAGE_ID']);
				$errors = $this->checkPackage($packageType, $body);

				$resp = $log->addPackage($objectMap[$packageType], $package['ObjectID'], $package['MESSAGE_ID'], $package['Lpu_id'], $packageType, $procDataType, $body);
				if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
				$packageId = $resp[0]['ServiceListPackage_id'];

				if (count($errors) > 0) {
					$this->textlog->add(['properties' => $properties, 'body' => $body, 'errors' => $errors]);
					$log->add(false, array_merge(["Пакет не сформирован:"], $errors), $packageId);
					$log->setPackageStatus($packageId, 'ErrFormed');
					return false;
				}
				if (!empty($_REQUEST['getDebug']) && !empty($_REQUEST['errors'])) {
					$body = $this->injectErrorsInPackage($body);
				}

				$log->setPackageStatus($packageId, 'Formed');

				try {
					$this->textlog->add(array('properties' => $properties, 'body' => $body));
					$msg = new AMQPMessage($body, $properties);
					list($queue, $publisherChannel) = $this->getQueue('publisher', 'common', $packageType);
					$publisherChannel->basic_publish($msg, '', $queue);
					$log->setPackageStatus($packageId, 'Sent');
				} catch(Exception $e) {
					$log->add(false, ["Ошибка отпавки пакета:", $e->getMessage()], $packageId);
					$log->setPackageStatus($packageId, 'ErrSent');
				}

				return $package['MESSAGE_ID'];
			};

			$publicateAnswer = function($package, $packageId = null, $packageErrors = []) use($log) {
				$id = $this->GUID();
				$packageType = 'ANSWER';

				$body = $this->createPackageBody($packageType, $package);
				$properties = $this->createPackageProperties($packageType, $id);
				$answerErrors = $this->checkPackage($packageType, $body);

				$resp = $log->addPackageData($packageId, $body);
				if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);

				if (count($answerErrors) > 0) {
					$this->textlog->add(['properties' => $properties, 'body' => $body, 'errors' => $answerErrors]);
					$log->add(false, array_merge(["Пакет отклонен:"], $packageErrors, $answerErrors), $packageId);
					$log->setPackageStatus($packageId, 'Rejected');
					return false;
				}
				if (!empty($_REQUEST['getDebug']) && !empty($_REQUEST['errors'])) {
					$body = $this->injectErrorsInPackage($body);
				}

				try {
					$this->textlog->add(array('properties' => $properties, 'body' => $body));
					$msg = new AMQPMessage($body, $properties);
					list($queue, $answerChannel) = $this->getQueue('publisher', 'answer');
					$answerChannel->basic_publish($msg, '', $queue);

					if (count($packageErrors) > 0) {
						$log->add(true, array_merge(["Пакет отклонен:"], $answerErrors), $packageId);
						$log->setPackageStatus($packageId, 'Rejected');
					} else {
						$log->add(true, "Пакет обработан", $packageId);
						$log->setPackageStatus($packageId, 'Processed');
					}
				} catch(Exception $e) {
					$log->add(false, ["Пакет отклонен:", $e->getMessage()], $packageId);
					$log->setPackageStatus($packageId, 'Rejected');
				}

				return $id;
			};

			$consumerChannel->basic_qos(null, 1, null);
			$consumerChannel->basic_consume($consumerQueue, '', false, false, false, false, function($msg) use($data, $log, $publicatePackage, $publicateAnswer) {
				$this->consumerCallback($msg, $data, $log, $publicatePackage, $publicateAnswer);
			});

			while(count($consumerChannel->callbacks)) {
				try {
					$consumerChannel->wait(null, false, 5);
				} catch (AMQPTimeoutException $e) {
					break;
				}
			}

			$this->closeConnections();

			if ($log != $commonLog) {
				$log->finish(true);
			}

			restore_exception_handler();
		} catch(Exception $e) {
			restore_exception_handler();

			if (isset($publisherChannel)) $publisherChannel->close();
			if (isset($consumerChannel)) $consumerChannel->close();
			if (isset($answerChannel)) $answerChannel->close();
			if (isset($connection)) $connection->close();

			$code = $e->getCode();
			$error = $e->getMessage();

			$resp = $log->addPackage('DummyPackage', null);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$log->add(false, array("Импорт данных завершён с ошибкой:", $error), $resp[0]['ServiceListPackage_id']);
			if ($log != $commonLog) {
				$log->finish(false);
			}

			$response = $this->createError($code, $error);
			$response[0]['ServiceListLog_id'] = $log->getId();

			return $response;
		}

		return array(array(
			'success' => true,
			'ServiceListLog_id' => $log->getId()
		));
	}

	/**
	 * @param AMQPMessage $msg
	 * @param array $data
	 * @param ServiceListLog $log
	 * @param callback $publicateAnswer
	 */
	function consumerCallback($msg, $data, $log, $publicatePackage, $publicateAnswer) {
		$objectMap = $this->getObjectMap();
		$properties = $msg->get_properties();
		$body = trim($msg->getBody());
		$msgBody = xml_to_array($body);
		$abortPackege = false;
		$properties['type'] = strtoupper($properties['type']);

		$this->textlog->add(array('properties' => $properties, 'body' => $body));

		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r(array('properties' => $properties, 'body' => htmlentities($body)));
		}

		if (in_array($this->regionNick, ['kareliya', 'perm']) 
			&& isset($properties['type']) 
			&& isset($objectMap[$this->packageTypeReverseMapper($properties['type'])])
		) {
			$packageType = $properties['type'];
		} elseif (isset($msgBody['ANSWER']['HEADER']['TYPE'])){
			$packageType = strtoupper($msgBody['ANSWER']['HEADER']['TYPE']);
		} else {
			$abortPackege = true;
		}
		
		//Сделано для того чтобы левые пакеты из Карелии не ломали очередь
		if (in_array($this->regionNick, ['kareliya']) && $packageType == "ANSWER"){
			$abortPackege = true;
		}

		$procDataType = null;
		if ($properties['type'] == 'ANSWER' || isset($msgBody['ANSWER'])) {
			$procDataType = 'ANSWER';
		} elseif (isset($msgBody[$packageType]['HEADER']['OPERATIONTYPE']) && !empty($msgBody[$packageType]['HEADER']['OPERATIONTYPE'])) {
			$procDataType = $msgBody[$packageType]['HEADER']['OPERATIONTYPE'];
		} else {
			$abortPackege = true;
		}

		$channel = $msg->delivery_info['channel'];
		$delivery_tag = $msg->delivery_info['delivery_tag'];
		$channel->basic_nack($delivery_tag);

		if ($abortPackege || empty($properties) || empty($properties['type'])) {
			return;
		}

		if ($this->regionNick != 'kareliya') {
			$errors = $this->checkPackage($properties['type'], $body);
			if (count($errors) > 0) {
				$this->textlog->add(array('properties' => $properties, 'body' => $body, 'errors' => $errors));
				$resp = $log->addPackage('DummyPackage', null, $properties['message_id']);
				if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
				$log->add(false, array_merge(array("Не загружен {$properties['type']} {$properties['message_id']}:"), $errors), $resp[0]['ServiceListPackage_id']);

				$answer = array(
					'QUEUE_NAME' => $msg->delivery_info['routing_key'],
					'TYPE' => $properties['type'],
					'MESSAGE_ID' => $properties['message_id'],
					'RESULT' => 'ERROR',
					'RESULT_ERROR' => array(),
				);
				foreach($errors as $error) {
					$answer['ERROR_RESULT'][] = array(
						'RESULT_CODE' => 0,
						'RESULT_NAME' => $error
					);
				}

				$publicateAnswer($answer);
				$channel->basic_nack($delivery_tag);
				return;
			}
		}

		$importTypes = array(
			'POLIS',
			'DOUBLES',
			'PERSCHANGE',
			'CHECKPERS',
			'IDLEPERSONS',
		);

		if (in_array($packageType, $importTypes)) {
			$resp = $log->addPackage($objectMap[$packageType], null, $properties['message_id'], null, $packageType, $procDataType, $body);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$packageId = $resp[0]['ServiceListPackage_id'];

			$answer = null;
			$errors = $this->checkPackage($packageType, $body);

			if (count($errors) > 0) {
				$this->textlog->add(['properties' => $properties, 'body' => $body, 'errors' => $errors]);

				$answer = array(
					'QUEUE_NAME' => $msg->delivery_info['routing_key'],
					'TYPE' => $properties['type'],
					'MESSAGE_ID' => $properties['message_id'],
					'RESULT' => 'ERROR',
					'RESULT_ERROR' => array(),
				);
				foreach($errors as $error) {
					$answer['ERROR_RESULT'][] = array(
						'RESULT_CODE' => 0,
						'RESULT_NAME' => $error
					);
				}
			} else {
				$procDataType = $msgBody[$packageType]['HEADER']['OPERATIONTYPE'];
				$method = 'import_package_'.$packageType;
				$result = $this->$method($procDataType, $msgBody[$packageType], $data, $log, $publicatePackage, $properties);

				$answer = array(
					'QUEUE_NAME' => $msg->delivery_info['routing_key'],
					'TYPE' => $packageType,
					'MESSAGE_ID' => $properties['message_id'],
				);

				if ($this->isSuccessful($result)) {
					$answer['RESULT'] = 'OK';
					if (!empty($result[0]['PERSON'])) {
						$answer['PERSON'] = $result[0]['PERSON'];
					}
				} else {
					$errors[] = $result[0]['Error_Msg'];
					$answer['RESULT'] = 'ERROR';
					$answer['ERROR_RESULT'][] = array(
						'RESULT_CODE' => !empty($result[0]['Error_Code'])?$result[0]['Error_Code']:0,
						'RESULT_NAME' => $result[0]['Error_Msg'],
					);
				}
			}

			$publicateAnswer($answer, $packageId, $errors);
			return;
		}

		if (
			$properties['type'] == 'ANSWER' ||
			($this->regionNick == 'kareliya' && !in_array($packageType, $importTypes))
		) {
			if (!isset($msgBody['ANSWER'])) {
				return;
			}

			if ($this->packageTypeReverseMapper($packageType) == 'DISPPLAN') {
				$this->import_package_ANSWER_DISPPLAN($msgBody['ANSWER'], $body, $properties, $data, $log);
			} else {
				$this->import_package_ANSWER($msgBody['ANSWER'], $body, $properties, $packageType, $data, $log);
			}

			return;
		}
	}

	/**
	 * @param array $package
	 * @param string $body
	 * @param array $properties
	 * @param array $data
	 * @param ServiceListLog $log
	 */
	function import_package_ANSWER($package, $body, $properties, $packageType, $data, $log) {
		if (isset($package['BODY']['RESULTS'])) {
			$results = $package['BODY']['RESULTS'];
		} else if (isset($package['BODY']['RESULT'])) {
			$results = $package['BODY']['RESULT'];
		} else {
			$results = false;
		}

		$objectMap = $this->getObjectMap();
		$packageType = $this->packageTypeReverseMapper($packageType);

		$messageId = $package['HEADER']['MESSAGE_ID'];

		$params = array(
			'ServiceList_id' => $this->ServiceList_id,
			'ServiceListPackage_ObjectName' => $objectMap[$packageType],
			'ServiceListPackage_GUID' => $messageId,
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
			$resp = $log->addPackage('DummyPackage', null, $messageId);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$log->add(false, 'Не найден идентификатор объекта из ответа', $resp[0]['ServiceListPackage_id']);
			$log->addPackageData($resp[0]['ServiceListPackage_id'], $body);
			return;
		}
		$packageId = $resp['ServiceListPackage_id'];

		$resp = $log->addPackageData($packageId, $body);
		if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);

		$errors = $this->checkPackage("ANSWER", $body);
		if (count($errors) > 0) {
			$this->textlog->add(['properties' => $properties, 'body' => $body, 'errors' => $errors]);
			$log->add(false, array_merge(["Пакет отклонен:"], $errors), $packageId);
			$log->setPackageStatus($packageId, 'Rejected');
			return;
		}

		if ($results['RESULT'] == 'OK') {
			$log->add(true, "Принят ТФОМС", $packageId);
			$log->setPackageStatus($packageId, 'AcceptedTFOMS');
			return;
		}

		if (isset($results['ERROR_RESULT'])) {
			$error_result = $results['ERROR_RESULT'];
			if (isset($error_result['RESULT_NAME'])) {
				$error_result = [$error_result];
			}

			$errors = array_map(function($item) {
				return $item['RESULT_NAME'];
			}, $error_result);

			$this->textlog->add(array('properties' => $properties, 'body' => $body));

			$log->add(true, array_merge(["Получен ответ с ошибкой:"], $errors), $packageId);
			$log->setPackageStatus($packageId, 'RejectedTFOMS');
			return;
		}
	}

	/**
	 * @param string $procDataType
	 * @param array $package
	 * @param array $data
	 * @param ServiceListLog $log
	 * @param callback $publicatePackage
	 * @return array
	 * @throws Error
	 */
	function import_package_POLIS($procDataType, $package, $data, $log, $publicatePackage, $properties) {
		$tfomsPerson = !empty($package['BODY']['PERSON'])?$package['BODY']['PERSON']:array();
		$tfomsPolis = !empty($package['BODY']['POLIS_DATA'])?$package['BODY']['POLIS_DATA']:null;
		$tfomsPersonCard = (!empty($package['BODY']['ATTACH'])&&!empty($package['BODY']['ATTACH']['ATTACHMO']))?$package['BODY']['ATTACH']:null;

		$tfomsPerson['BDZID'] = !empty($package['HEADER']['BDZID'])?$package['HEADER']['BDZID']:null;
		if (isset($tfomsPerson['DS']) && empty($tfomsPerson['DS'])) $tfomsPerson['DS'] = null;
		
		$message_id = isset($properties['message_id']) ? $properties['message_id'] : null;
		$resp = $log->addPackage('PersonPolis', null, $message_id, null, 'POLIS', 'ANSWER');
		if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
		$ServiceListPackage_id = $resp[0]['ServiceListPackage_id'];

		if (empty($tfomsPolis) && !empty($package['BODY']['PERSONPOLISID'])) {
			$tfomsPolis = $package['BODY'];
		}

		$promedPerson = null;

		$data['Person_id'] = null;
		$data['Polis_id'] = null;
		$data['PersonCard_id'] = null;
		$newPerson = false;
		$newPolis = false;
		$allowImport = true;

		$response = array(array(
			'success' => true,
		));

		$addLog = function($person, $tpl) use($log, $ServiceListPackage_id) {
			if (empty($person['OT'])) $person['OT'] = '';
			$message = $this->parser->parse_string($tpl, $person, true);
			$this->textlog->add($message);
			$log->add(true, $message, $ServiceListPackage_id);
		};

		$this->load->library('textlog', array(
			'file' => 'TFOMSAutoInteract_'.date('Y-m-d').'.log',
			'format' => 'json',
			'parse_xml' => true
		));
		$this->load->library('parser');

		//Обработка полиса с операцией Delete
		if ($procDataType == 'Delete') {
			try {
				$resp = $this->searchPerson(array(
					'BDZID' => $tfomsPerson['BDZID'],
				));
				if (count($resp) > 1) {
					foreach($resp as $promedPerson) {
						$this->setPerson(array(
							'Person_id' => $promedPerson['Person_id'], 'BDZID' => null
						), $data);
						$publicatePackage('Query', 'PERS', $promedPerson);
					}
				} else if (count($resp) > 0) {
					$promedPolisForClose = $this->getPolisForClose($tfomsPerson);
					if ($promedPolisForClose === false) {
						throw new Exception('Ошибка при поиска полиса для закрытия');
					}
					if (is_array($promedPolisForClose)) {
						$this->updatePolis($promedPolisForClose, $data);
						$tpl = "Полис человека {Person_id} {FAM} {IM} {OT} закрыт по данным ТФОМС {POLISENDDT}";
						$addLog($promedPolisForClose, $tpl);
					}
				}
				return $response;
			} catch(Exception $e) {
				$this->textlog->add($e->getMessage());
				return $this->createError($e->getCode(), $e->getMessage());
			}
		}

		//Обработка полиса с операцией Insert/Update
		$this->beginTransaction();

		try {
			//Импорт данных человека
			$resp = $this->searchPerson(array(
				'BDZID' => $tfomsPerson['BDZID'],
			));
			if (!is_array($resp)) {
				throw new Exception('Ошибка при поиске человека по BDZID');
			}

			if (count($resp) > 1) {
				//Найдено несколько человек по БДЗ, обнуляем BDZ_id у всех
				foreach($resp as $promedPerson) {
					$this->setPerson(array(
						'Person_id' => $promedPerson['Person_id'], 'BDZID' => null
					), $data);
					$publicatePackage('Query', 'PERS', $promedPerson);
				}
				$allowImport = false;
			} else if (count($resp) > 0) {
				//Если найден по BDZID
				$promedPerson = $resp[0];

				$data['Person_id'] = $promedPerson['Person_id'];

				if (!empty($tfomsPerson['FAM'])) {
					$dataForSave = array();
					$diffFields = array();

					foreach ($promedPerson as $field => $promedValue) {
						$tfomsValue = !empty($tfomsPerson[$field]) ? $tfomsPerson[$field] : null;
						if (in_array($field, array('FAM','IM','OT'))) {
							$promedValue = trim($promedValue);
							$tfomsValue = trim($tfomsValue);
						}
						if (in_array($field, array('SNILS','DOCSER'))) {
							$promedValue = str_replace(array(' ','-'), '', $promedValue);
							$tfomsValue = str_replace(array(' ','-'), '', $tfomsValue);
						}

						if (empty($promedValue) && !empty($tfomsValue)) {
							$dataForSave[$field] = $tfomsValue;
						}
						if (!empty($promedValue) && !empty($tfomsValue) && $promedValue != $tfomsValue) {
							$diffFields[] = $field;
						}
					}

					if (!empty($diffFields)) {
						$this->setPerson(array(
							'Person_id' => $promedPerson['Person_id'], 'BDZID' => null
						), $data);
						$allowImport = false;
						$tpl = "По идентификатору ТФОМС найден человек {Person_id} {FAM} {IM} {OT} {DR} с другими данными: ";
						$tpl .= implode(', ', $diffFields) . " Человек направлен на повторную идентификацию в ТФОМС";
						$addLog($promedPerson, $tpl);
						$publicatePackage('Query', 'PERS', $promedPerson);
					} else if (empty($dataForSave)) {
						$tpl = "Данные человека {Person_id} {FAM} {IM} {OT} {DR} актуальны";
						$addLog($promedPerson, $tpl);
					} else {
						$savedFields = array_keys($dataForSave);

						if (!empty($dataForSave['DOCTYPE'])) {
							$data['Document_id'] = $this->setDocument($dataForSave, $data);
							unset($dataForSave['DOCTYPE']);
							unset($dataForSave['DOCSER']);
							unset($dataForSave['DOCNUM']);
						}
						if (!empty($dataForSave)) {
							$data['Person_id'] = $this->setPerson($dataForSave, $data);
						}

						$tpl = "Данные человека {Person_id} {FAM} {IM} {OT} {DR} обновлены: " . implode(', ', $savedFields);
						$addLog($promedPerson, $tpl);
					}
				}
			} else if (!empty($tfomsPerson['FAM'])) {
				//Если не найден по BDZID, то ищем по кобинациям параметров человека
				$combinations = array(
					array('FAM', 'IM', 'OT', 'DR', 'DOCTYPE', 'DOCSER', 'DOCNUM'),
					array('FAM', 'IM', 'OT', 'DR', 'SNILS'),
				);
				$searchCount = 0;
				$promedPersonList = array();

				foreach ($combinations as $combination) {
					$resp = $this->searchPerson($this->getParams($tfomsPerson, $combination));
					if (!is_array($resp)) {
						throw new Exception('Ошибка при поиске человека по полученным параметрам');
					}
					if (count($resp) > 1) {
						$searchCount = max($searchCount, count($resp));
						$promedPersonList = $resp;
					} else if (count($resp) == 1) {
						$searchCount = 1;
						$promedPerson = $resp[0];
						break;
					}
				}

				if ($searchCount > 1) {
					foreach($promedPersonList as $promedPerson) {
						$this->setPerson(array(
							'Person_id' => $promedPerson['Person_id'], 'BDZID' => null,
						), $data);
						$publicatePackage('Query', 'PERS', $promedPerson);
					}
					$allowImport = false;
					$tpl = "По данным ФИО, ДУЛ и СНИЛС найдено более 1 записи {FAM} {IM} {OT}, {SNILS}. ";
					$tpl .= "Пациенты направлены на повторную идентификацию в ТФОМС";
					$addLog($tfomsPerson, $tpl);
				} else if ($promedPerson) {
					$dataForSave = array();

					$data['Person_id'] = $promedPerson['Person_id'];

					foreach ($promedPerson as $field => $promedValue) {
						$tfomsValue = !empty($tfomsPerson[$field]) ? $tfomsPerson[$field] : null;

						if (empty($promedValue) && !empty($tfomsValue)) {
							$dataForSave[$field] = $tfomsValue;
						} else if (in_array($field, array('BDZID')) && $promedValue != $tfomsValue) {
							$dataForSave[$field] = $tfomsValue;
						}
					}

					if (!empty($dataForSave['DOCTYPE'])) {
						$data['Document_id'] = $this->setDocument($dataForSave, $data);
						unset($dataForSave['DOCTYPE']);
						unset($dataForSave['DOCSER']);
						unset($dataForSave['DOCNUM']);
					}
					if (!empty($dataForSave)) {
						$data['Person_id'] = $this->setPerson($dataForSave, $data);
					}
				}
			}
			if (empty($data['Person_id']) && empty($tfomsPerson['FAM']) && !empty($tfomsPolis['ENP']) && $allowImport) {
				$allowImport = false;
				if (!empty($tfomsPolis['ENP'])) {
					$resp = $this->searchPerson(array(
						'ENP' => $tfomsPolis['ENP'],
					));
					if (count($resp) > 0) {
						$promedPerson = $resp[0];
					}
				}
				if ($promedPerson) {
					$promedPerson = $resp[0];
					$publicatePackage('Query', 'PERS', $promedPerson);
				} else {
					$tpl = "В БД не найден пациент по следующим данным ТФОМС ПК {BDZID}";
					$tpl .= ", {SMOCODE}, {POLISTYPE}, {POLISSER}, {POLISNUM}, {ENP}, {POLISBEGDT}.";
					$addLog(array_merge(array('POLISSER' => null), $tfomsPerson, $tfomsPolis), $tpl);
				}
			}
			if ($this->regionNick == 'khak' && empty($data['Person_id']) && !empty($tfomsPerson['DS'])) {
				$allowImport = false;
			}
			if (empty($data['Person_id']) && $allowImport) {
				//Человек не найден, создаем новую запись
				$newPerson = true;
				$data['Person_id'] = $this->setPerson($tfomsPerson, $data);
				if (!empty($tfomsPerson['DOCTYPE'])) {
					$data['Document_id'] = $this->setDocument($tfomsPerson, $data);
				}
				$promedPerson = array_merge(array(
					'Person_id' => $data['Person_id']
				), $tfomsPerson);
			}

			//Импорт данных полиса
			if (!empty($data['Person_id']) && !$newPerson && $allowImport) {
				//Поиск полиса существующего в базе человека
				$combination = array(
					'POLISTYPE', 'POLISSER', 'POLISNUM', 'ENP', 'SMOCODE', 'POLISBEGDT'
				);

				$resp = $this->searchPolis(array_merge(
					array('Person_id' => $data['Person_id']),
					$this->getParams($tfomsPolis, $combination)
				));

				$tfomsPolis['POLISENDDT'] = !empty($tfomsPolis['POLISENDDT']) ? $tfomsPolis['POLISENDDT'] : null;
				$tfomsPolis['POLISCLOSECAUSE'] = !empty($tfomsPolis['POLISCLOSECAUSE']) ? $tfomsPolis['POLISCLOSECAUSE'] : null;

				if (!is_array($resp)) {
					throw new Exception('Ошибка при поиске полиса по полученным параметрам');
				}
				if (count($resp) > 0) {
					$promedPolis = $resp[0];
					$data['Polis_id'] = $promedPolis['Polis_id'];
					$data['PersonPolis_id'] = $promedPolis['PersonPolis_id'];

					if ($promedPolis['POLISENDDT'] != $tfomsPolis['POLISENDDT']) {
						$dataForSave['POLISENDDT'] = $tfomsPolis['POLISENDDT'];
					}
					if ($promedPolis['POLISCLOSECAUSE'] != $tfomsPolis['POLISCLOSECAUSE']) {
						$dataForSave['POLISCLOSECAUSE'] = $tfomsPolis['POLISCLOSECAUSE'];
					}

					if (!empty($dataForSave)) {
						$data['Polis_id'] = $this->updatePolis($dataForSave, $data);
						$diffFields = array_keys($dataForSave);
						$tpl = "Полисные данные человека {Person_id} {FAM} {IM} {OT} {DR} обновлены: ".implode(', ', $diffFields);
						$addLog($promedPerson, $tpl);
					} else {
						$tpl = "Полисные данные человека {Person_id} {FAM} {IM} {OT} {DR} актуальны";
						$addLog($promedPerson, $tpl);
					}
				} else {
					//Если полис не найден, то обрабатываем полиса, попадающие в период действия импортируемого полиса
					$resp = $this->searchPolis(array(
						'Person_id' => $data['Person_id'],
						'begPeriod' => $tfomsPolis['POLISBEGDT'],
						'endPeriod' => $tfomsPolis['POLISENDDT'],
					));

					foreach ($resp as $promedPolis) {
						$pbeg = date_create($promedPolis['POLISBEGDT']);
						$pend = date_create(!empty($promedPolis['POLISENDDT']) ? $promedPolis['POLISENDDT'] : '2999-12-31');
						$tbeg = date_create($tfomsPolis['POLISBEGDT']);
						$tend = date_create(!empty($tfomsPolis['POLISENDDT']) ? $tfomsPolis['POLISENDDT'] : '2999-12-31');

						if ($pbeg >= $tbeg && $pbeg <= $tend && $pend >= $tbeg && $pend <= $tend) {
							$this->deletePolis($promedPolis, $data);
						} else if ($pbeg <= $tend && $pbeg >= $tbeg) {
							$promedPolis['POLISBEGDT'] = $tend->modify('+1 day')->format('Y-m-d');
							$this->updatePolis($promedPolis, $data);
						} else if ($pend >= $tbeg && $pend <= $tend) {
							$promedPolis['POLISENDDT'] = $tbeg->modify('-1 day')->format('Y-m-d');
							$this->updatePolis($promedPolis, $data);
						}
					}
				}
			}
			if (empty($data['Polis_id']) && !empty($data['Person_id']) && $allowImport) {
				//Полис не найден, создаем новую запись
				$newPolis = true;
				$data['Polis_id'] = $this->createPolis($tfomsPolis, $data);
				if ($tfomsPolis['POLISTYPE'] == 3) {
					$data['PersonPolisEdNum_id'] = $this->setPolisEdNum($tfomsPolis, $data);
				}
				$tpl = "Периодики полисных данных человека {Person_id} {FAM} {IM} {OT} {DR} обновлены";
				$addLog($promedPerson, $tpl);
			}

			if (!empty($tfomsPersonCard) && !empty($data['Polis_id']) && $newPolis && $allowImport) {
				//При создании полиса создаем условное прикрепление, если прикрпления у человека нет
				$tfomsPersonCard['Person_id'] = $data['Person_id'];
				$count = $this->getPersonCardCount($tfomsPersonCard);
				if ($count === false) {
					throw new Exception('Ошибка при проверке существования прикрепления');
				}
				if ($count == 0) {
					$data['PersonCard_id'] = $this->setPersonCard($tfomsPersonCard, $data);
				}
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			$this->textlog->add($e->getMessage());
			$log->add(false, $e->getMessage(), $ServiceListPackage_id);
			if (!empty($_REQUEST['getDebug'])) {
				echo '<pre>'.$e->getMessage();
			}
			return $this->createError('', 'Ошибка обработки пакета POLIS');
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * @param string $procDataType
	 * @param array $package
	 * @param array $data
	 * @param ServiceListLog $log
	 * @param callback $publicatePackage
	 * @return array
	 * @throws Exception
	 */
	function import_package_DOUBLES($procDataType, $package, $data, $log, $publicatePackage, $properties) {
		$BDZID = $package['HEADER']['BDZID'];
		$BDZDID = $package['BODY']['BDZDID'];

		$response = array(array(
			'succes' => true
		));

		$persons = array();

		$resp = $this->searchPerson(array('BDZID' => $BDZID));
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при поиске человека по BDZID');
		}
		$persons = array_merge($persons, $resp);

		$resp = $this->searchPerson(array('BDZID' => $BDZDID));
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при поиске человека по BDZID');
		}
		$persons = array_merge($persons, $resp);

		foreach ($persons as $person) {
			$this->setPerson(array(
				'Person_id' => $person['Person_id'],
				'BDZID' => null
			), $data);
			$publicatePackage('Query', 'PERS', $person);
		}

		return $response;
	}

	/**
	 * @param string $procDataType
	 * @param array $package
	 * @param array $data
	 * @param ServiceListLog $log
	 * @param callback $publicatePackage
	 * @return array
	 * @throws Exception
	 */
	function import_package_PERSCHANGE($procDataType, $package, $data, $log, $publicatePackage, $properties) {
		$BDZID = $package['BODY']['BDZID'];

		$response = array(array(
			'success' => true
		));

		$resp = $this->searchPerson(array(
			'BDZID' => $BDZID,
		));
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при поиске человека по BDZID');
		}

		if (count($resp) == 0) {
			return $this->createError('','Пациент отсутствует в базе данных');
		} else if (count($resp) > 1) {
			foreach ($resp as $person) {
				$this->setPerson(array(
					'Person_id' => $person['Person_id'],
					'BDZID' => null
				), $data);
				$publicatePackage('Query', 'PERS', $person);
			}
		}

		return $response;
	}

	/**
	 * @param string $procDataType
	 * @param array $package
	 * @param array $data
	 * @param ServiceListLog $log
	 * @param callback $publicatePackage
	 * @return array
	 * @throws Exception
	 */
	function import_package_CHECKPERS($procDataType, $package, $data, $log, $publicatePackage, $properties) {
		$BDZID = $package['BODY']['BDZID'];

		$response = array(array(
			'success' => true,
			'PERSON' => null
		));

		$resp = $this->searchPerson(array(
			'BDZID' => $BDZID,
		));
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при поиске человека по BDZID');
		}

		if (count($resp) == 0) {
			return $this->createError('','Пациент отсутствует в базе данных');
		} else if (count($resp) > 1) {
			foreach ($resp as $person) {
				$this->setPerson(array(
					'Person_id' => $person['Person_id'],
					'BDZID' => null
				), $data);
				$publicatePackage('Query', 'PERS', $person);
			}
			return $this->createError('','По данному БДЗ было найдено несколько пациентов. Пациенты отправлены на повторную идентификацию в ТФОМС');
		}

		foreach($resp as &$person) {
			unset($person['POLISTYPE']);
			unset($person['POLISSER']);
			unset($person['POLISNUM']);
			unset($person['POLISBEGDT']);
			unset($person['POLISENDDT']);

			$polisData = $this->getPolisData($person['Person_id']);
			if (!is_array($polisData)) {
				return $this->createError('','Ошибка при поиске полисных данных');
			}
			$person['POLIS_DATA'] = $polisData;
		}

		$response[0]['PERSON'] = $resp;

		return $response;
	}

	/**
	 * Импорт пакета данных о неработающем застрахованном человеке
	 * @param string $procDataType
	 * @param array $package
	 * @param array $data
	 * @param ServiceListLog $log
	 * @param callback $publicatePackage
	 * @return array
	 * @throws Exception
	 */
	function import_package_IDLEPERSONS($procDataType, $package, $data, $log, $publicatePackage, $properties) {
		$tfomsPerson = $package['BODY']['PERSON'];
		$BDZID = $package['BODY']['BDZ_ID'];

		if (!empty($tfomsPerson['SNILS'])) {
			$tfomsPerson['SNILS'] = str_replace(array(' ','-'), '', $tfomsPerson['SNILS']);
		}

		$response = array(array(
			'success' => true
		));

		$compare = function($arr1, $arr2, $keys) {
			foreach($keys as $key) {
				if ($arr1[$key] != $arr2[$key]) {
					return  false;
				}
			}
			return true;
		};

		$calcSocStatus = function($person) {
			$age = $person['AGE'];
			$sex = $person['W'];
			$socStatus = 6;

			if ($age < 7) {
				$socStatus = 1;
			} else if ($age < 18) {
				$socStatus = 3;
			} else if ($sex == 2 && $age < 60) {
				$socStatus = 5;
			} else if ($sex == 1 && $age < 65) {
				$socStatus = 5;
			}

			return $socStatus;
		};

		$resp = $this->searchPerson(array(
			'BDZID' => $BDZID
		));
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при поиске человека по BDZID');
		}

		if (count($resp) > 1) {
			//Найдено несколько человек по БДЗ, обнуляем BDZ_id у всех
			foreach($resp as $promedPerson) {
				$this->setPerson(array(
					'Person_id' => $promedPerson['Person_id'],
					'BDZID' => null
				), $data);
				$publicatePackage('Query', 'PERS', $promedPerson);
			}
		} else if (count($resp) == 0) {
			//Если не найден по BDZID, то ищем по кобинациям параметров человека,
			//и у каждой найденной записи обнуляем BDZ_id
			$combinations = array(
				array('FAM', 'IM', 'OT', 'DOCTYPE', 'DOCSER', 'DOCNUM'),
				array('FAM', 'IM', 'OT', 'SNILS'),
			);

			$sentPersonIds = [];

			foreach ($combinations as $combination) {
				$resp = $this->searchPerson($this->getParams($tfomsPerson, $combination));
				if (!is_array($resp)) {
					throw new Exception('Ошибка при поиске человека по полученным параметрам');
				}

				foreach($resp as $promedPerson) {
					if (in_array($promedPerson['Person_id'], $sentPersonIds)) {
						continue;
					}
					$this->setPerson(array(
						'Person_id' => $promedPerson['Person_id'],
						'BDZID' => null
					), $data);
					$publicatePackage('Query', 'PERS', $promedPerson);
					$sentPersonIds[] = $promedPerson['Person_id'];
				}
			}
		} else {
			$promedPerson = $resp[0];

			if (!$compare($promedPerson, $tfomsPerson, array('FAM', 'IM', 'OT'))) {
				$this->setPerson(array(
					'Person_id' => $promedPerson['Person_id'],
					'BDZID' => null
				), $data);
				$publicatePackage('Query', 'PERS', $promedPerson);
			} else {
				$data['Person_id'] = $promedPerson['Person_id'];
				$person = array();
				$document = array();
				$nationality = array();

				if (!$compare($promedPerson, $tfomsPerson, array('SNILS'))) {
					$person['SNILS'] = $tfomsPerson['SNILS'];
				}
				if (empty($promedPerson['SOC_STATUS']) || in_array($promedPerson['SOC_STATUS'], array(4,8,11,12,13,14,15,16,17,18,19,20))) {
					$person['SOC_STATUS'] = $calcSocStatus($promedPerson);
				}
				if (!$compare($promedPerson, $tfomsPerson, array('DOCTYPE', 'DOCSER', 'DOCNUM'))) {
					$document = $this->getParams($tfomsPerson, array('DOCTYPE', 'DOCSER', 'DOCNUM'));
				}
				if (!$compare($promedPerson, $tfomsPerson, array('OKSM_ALFA3'))) {
					$nationality['OKSM_ALFA3'] = $tfomsPerson['OKSM_ALFA3'];
				}

				/*print_r(array(
					'person' => $person,
					'document' => $document,
					'nationality' => $nationality
				));*/

				if (!empty($person)) {
					$this->setPerson($person, $data);
				}
				if (!empty($document)) {
					$this->setDocument($document, $data);
				}
				if (!empty($nationality)) {
					$this->setNationality($nationality, $data);
				}
			}
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function searchPerson($data) {
		$params = array();
		$filters = array();

		$has = function($field) use($data) {
			return array_key_exists($field, $data);
		};

		if ($has('BDZID')) {
			$filters[] = "P.BDZ_id = :BDZID";
			$params['BDZID'] = $data['BDZID'];
		}
		if ($has('FAM')) {
			$filters[] = "rtrim(PS.Person_SurName) like :FAM";
			$params['FAM'] = $data['FAM'];
		}
		if ($has('IM')) {
			$filters[] = "rtrim(PS.Person_FirName) like :IM";
			$params['IM'] = $data['IM'];
		}
		if ($has('OT')) {
			$filters[] = "isnull(rtrim(PS.Person_SecName), '') like :OT";
			$params['OT'] = $data['OT'];
		}
		if ($has('DR')) {
			$filters[] = "PS.Person_BirthDay = :DR";
			$params['DR'] = $data['DR'];
		}
		if ($has('W')) {
			$filters[] = "S.Sex_fedid = :W";
			$params['W'] = $data['W'];
		}
		if ($has('DOCTYPE')) {
			$filters[] = "DT.DocumentType_Code = :DOCTYPE";
			$params['DOCTYPE'] = $data['DOCTYPE'];
		}
		if ($has('DOCSER')) {
			$filters[] = "isnull(replace(replace(D.Document_Ser, ' ', ''), '-', ''), '') = isnull(replace(replace(:DOCSER, ' ', ''), '-', ''), '')";
			$params['DOCSER'] = $data['DOCSER'];
		}
		if ($has('DOCNUM')) {
			$filters[] = "D.Document_Num = :DOCNUM";
			$params['DOCNUM'] = $data['DOCNUM'];
		}
		if ($has('SNILS')) {
			$filters[] = "isnull(PS.Person_Snils, '') = isnull(:SNILS,'')";
			$params['SNILS'] = $data['SNILS'];
		}
		if (!empty($data['ENP'])) {
			$filters[] = "PS.Person_EdNum = :ENP";
			$params['ENP'] = $data['ENP'];
		}
		if (!empty($data['begPeriod']) && !empty($data['endPeriod'])) {
			$filters[] = "P.Person_insDT between :begPeriod and :endPeriod";
			$filters[] = "DT.DocumentType_id is not null";
			$filters[] = "PT.PolisType_CodeF008 is not null";
			$params['begPeriod'] = $data['begPeriod'];
			$params['endPeriod'] = $data['endPeriod'];
		}
		if (!empty($data['exportId'])) {
			$exportId_str = implode(',', $data['exportId']);
			$filters[] = "PS.Person_id in ({$exportId_str})";
		}

		if (count($filters) == 0) {
			return array();
		}

		$filters_str = implode("\nand ", $filters);

		$DTLen = in_array($this->regionNick,['perm','kareliya'])?19:10;
		$DTTyp = in_array($this->regionNick,['perm','kareliya'])?126:120;

		$query = "
			declare @date date = dbo.tzGetDate();
			select
				-- select
				PS.Person_id,
				PS.Person_id as ObjectID,
				PS.Person_id as PERSONID,
				P.BDZ_id as BDZID,
				upper(rtrim(PS.Person_SurName)) as FAM,
				upper(rtrim(PS.Person_FirName)) as IM,
				upper(rtrim(PS.Person_SecName)) as OT,
				S.Sex_fedid as W,
				PS.Person_Snils as SNILS,
				DT.DocumentType_Code as DOCTYPE,
				case when DT.DocumentType_Code = 14 and len(D.Document_Ser) = 4
					then substring(D.Document_Ser, 1, 2)+' '+substring(D.Document_Ser, 3, 2)
					else D.Document_Ser
				end as DOCSER,
				D.Document_Num as DOCNUM,
				convert(varchar(10), PS.Person_BirthDay, 120) as DR,
				convert(varchar(10), PS.Person_deadDT, 120) as DS,
				dbo.Age2(PS.Person_BirthDay, @date) as AGE,
				PT.PolisType_CodeF008 as POLISTYPE,
				Polis.Polis_Ser as POLISSER,
				Polis.Polis_Num as POLISNUM,
				convert(varchar({$DTLen}), Polis.Polis_begDate, {$DTTyp}) as POLISBEGDT,
				convert(varchar({$DTLen}), Polis.Polis_endDate, {$DTTyp}) as POLISENDDT,
				case when PT.PolisType_CodeF008 = 3 then PS.Person_EdNum end as ENP,
				SC.SocStatus_Code as SOC_STATUS,
				KLC.KLCountry_Code as OKSM_ALFA3
				-- end select
			from
				-- from
				v_PersonState PS with(nolock)
				inner join Person P with(nolock) on P.Person_id = PS.Person_id
				left join v_Sex S with(nolock) on S.Sex_id = PS.Sex_id
				left join v_Document D with(nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with(nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_PersonPolis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
				left join v_SocStatus SC with(nolock) on SC.SocStatus_id = PS.SocStatus_id
				left join v_KLCountry KLC with(nolock) on KLC.KLCountry_id = PS.KLCountry_id
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				PS.Person_id
				-- end order by
		";

		if ($has('start') && $has('limit')) {
			$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		}

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getPolisForClose($data) {
		$params = array(
			'BDZID' => $data['BDZID']
		);

		$query = "
			declare @date date = dbo.tzGetDate();
			select top 1
				PS.Person_id,
				Polis.PersonPolis_id,
				Polis.Polis_id,
				upper(rtrim(PS.Person_SurName)) as FAM,
				upper(rtrim(PS.Person_FirName)) as IM,
				upper(rtrim(PS.Person_SecName)) as OT,
				convert(varchar(10), PS.Person_BirthDay, 120) as DR,
				convert(varchar(10), @date, 120) as POLISENDDT
			from
				v_PersonState PS with(nolock)
				inner join Person P with(nolock) on P.Person_id = PS.Person_id
				inner join v_PersonPolis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
			where
				P.BDZ_id = :BDZID
				and Polis.Server_id in (0, 2)
				and (Polis.Polis_endDate is null or Polis.Polis_endDate > @date)
		";

		return $this->getFirstRowFromQuery($query, $params, true);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function searchPolis($data) {
		$params = array(
			'begPeriod' => null,
			'endPeriod' => null,
		);
		$filters = array('1=1');

		$filters[] = "P.Person_id = :Person_id";
		$params['Person_id'] = $data['Person_id'];

		if (!empty($data['POLISTYPE'])) {
			$filters[] = "PT.PolisType_CodeF008 = :POLISTYPE";
			$params['POLISTYPE'] = $data['POLISTYPE'];
		}
		if (!empty($data['POLISSER'])) {
			$filters[] = "P.Polis_Ser = :POLISSER";
			$params['POLISSER'] = $data['POLISSER'];
		}
		if (!empty($data['ENP']) && !empty($data['POLISNUM'])) {
			$filters[] = "(P.Polis_Num = :ENP or P.Polis_Num = :POLISNUM)";
			$params['ENP'] = $data['ENP'];
			$params['POLISNUM'] = $data['POLISNUM'];
		} else if (!empty($data['ENP'])) {
			$filters[] = "P.Polis_Num = :ENP";
			$params['ENP'] = $data['ENP'];
		} else if (!empty($data['POLISNUM'])) {
			$filters[] = "P.Polis_Num = :POLISNUM";
			$params['POLISNUM'] = $data['POLISNUM'];
		}
		if (!empty($data['POLISBEGDT'])) {
			$filters[] = "P.Polis_begDate = cast(:POLISBEGDT as date)";
			$params['POLISBEGDT'] = $data['POLISBEGDT'];
		}
		if (!empty($data['SMOCODE'])) {
			$filters[] = "(
				not exists(select * from v_OrgSmo with(nolock) where OrgSmo_f002smocod = :SMOCODE)
				or Smo.OrgSmo_f002smocod = :SMOCODE
			)";
			$params['SMOCODE'] = $data['SMOCODE'];
		}
		if (!empty($data['POLISCLOSECAUSE'])) {
			$filters[] = "PCC.PolisCloseCause_Code = :POLISCLOSECAUSE";
			$params['POLISCLOSECAUSE'] = $data['POLISCLOSECAUSE'];
		}

		if (!empty($data['begPeriod'])) {
			$filters[] = "P.Polis_begDate <= isnull(@endPeriod, @bigdate)";
			$filters[] = "isnull(P.Polis_endDate, @bigdate) >= @begPeriod";
			$params['begPeriod'] = $data['begPeriod'];
			$params['endPeriod'] = !empty($data['endPeriod'])?$data['endPeriod']:null;
		}

		$filters_str = implode("\nand ", $filters);

		$DTLen = ($this->regionNick == 'perm')?19:10;
		$DTTyp = ($this->regionNick == 'perm')?126:120;

		$query = "
			declare 
				@bigdate date = dateadd(year, 50, dbo.tzGetDate()),
				@begPeriod date = :begPeriod,
				@endPeriod date = :endPeriod;
			select
				P.Polis_id,
				P.PersonPolis_id,
				P.Server_id,
				P.Person_id,
				PT.PolisType_CodeF008 as POLISTYPE,
				P.Polis_Ser as POLISSER,
				P.Polis_Num as POLISNUM,
				case when PT.PolisType_CodeF008 = 3 then P.Polis_Num end as ENP,
				convert(varchar({$DTLen}), P.Polis_begDate, {$DTTyp}) as POLISBEGDT,
				convert(varchar({$DTLen}), P.Polis_endDate, {$DTTyp}) as POLISENDDT,
				Smo.OrgSmo_f002smocod as SMOCODE,
				PCC.PolisCloseCause_Code as POLISCLOSECAUSE
			from
				v_PersonPolis P with(nolock)
				inner join v_PolisType PT with(nolock) on PT.PolisType_id = P.PolisType_id
				left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = P.OrgSmo_id
				left join v_PolisCloseCause PCC with(nolock) on PCC.PolisCloseCause_id = P.PolisCloseCause_id
			where
				{$filters_str}
			order by
				P.PersonPolis_insDT
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param int $Person_id
	 * @return array|false
	 */
	function getPolisData($Person_id) {
		$params = array(
			'Person_id' => $Person_id
		);

		$DTLen = ($this->regionNick == 'perm')?19:10;
		$DTTyp = ($this->regionNick == 'perm')?126:120;

		$query = "
			select
				PT.PolisType_CodeF008 as POLISTYPE,
				P.Polis_Ser as POLISSER,
				isnull(P.Polis_Num, ENP.PersonPolisEdNum_EdNum) as POLISNUM,
				convert(varchar({$DTLen}), P.Polis_begDate, {$DTTyp}) as POLISBEGDT,
				convert(varchar({$DTLen}), P.Polis_endDate, {$DTTyp}) as POLISENDDT
			from
				v_PersonPolis P with(nolock)
				inner join v_PolisType PT with(nolock) on PT.PolisType_id = P.PolisType_id
				outer apply (
					select top 1 ENP.PersonPolisEdNum_EdNum
					from v_PersonPolisEdNum ENP with(nolock)
					where ENP.Person_id = P.Person_id
					and ENP.PersonPolisEdNum_insDT <= P.PersonPolis_insDT
					order by ENP.PersonPolisEdNum_insDT desc
				) ENP
			where
				P.Person_id = :Person_id
			order by
				P.PersonPolis_insDT
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $person
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	function setPerson($person, $data) {
		$params = array(
			'Person_id' => !empty($data['Person_id'])?$data['Person_id']:null,
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => 0,
		);

		if ($this->read_only) {
			return $params['Person_id']?$params['Person_id']:'readonly';
		}

		if (empty($params['Person_id']) && !empty($person['Person_id'])) {
			$params['Person_id'] = $person['Person_id'];
		}

		$map = array(
			'BDZID' => 'BDZ_id',
			'FAM' => 'PersonSurName_SurName',
			'IM' => 'PersonFirName_FirName',
			'OT' => 'PersonSecName_SecName',
			'DR' => 'PersonBirthDay_BirthDay',
			'DS' => 'Person_deadDT',
			'PHONE_NUMBER' => 'PersonPhone_Phone',
			'SNILS' => 'PersonSnils_Snils',
			'SOC_STATUS' => 'SocStatus_id',
			'W' => 'Sex_id',
		);

		$fields = array();
		foreach($map as $key => $dbKey) {
			if (array_key_exists($key, $person)) {
				$fields[] = "@{$dbKey} = :{$key}";
				$params[$key] = $person[$key];
			} else if (array_key_exists($dbKey, $person)) {
				$fields[] = "@{$dbKey} = :{$key}";
				$params[$key] = $person[$dbKey];
			}
		}

		if (array_key_exists('SOC_STATUS', $person) && $this->regionNick == 'khak') {
			$params['SOC_STATUS'] = null;
			if (0 == $person['SOC_STATUS']) {
				$params['SOC_STATUS'] = 4;
			} elseif (1 == $person['SOC_STATUS']) {
				$age = DateTime::createFromFormat('Y-m-d', $person['DR']);
				if ($age !== false) {
					$age = $age->diff(date_create('now'))->y;
					switch(true) {
						case ($age < 2):
							$params['SOC_STATUS'] = 2;
							break;
						case ($age >= 2 && $age < 6):
							$params['SOC_STATUS'] = 1;
							break;
						case ($age >= 7 && $age < 21):
							$params['SOC_STATUS'] = 3;
							break;
						case ($age >= 22 && $age < 64):
							$params['SOC_STATUS'] = 5;
							break;
						case ($age >= 65):
							$params['SOC_STATUS'] = 6;
							break;
					}
				}
			}
		} else if (array_key_exists('SOC_STATUS', $person)) {
			$params['SOC_STATUS'] = $this->getFirstResultFromQuery("
				select top 1 SocStatus_id
				from v_SocStatus with(nolock)
				where SocStatus_Code = :SocStatus_Code
			", array(
				'SocStatus_Code' => $person['SOC_STATUS']
			), true);
			if ($params['SOC_STATUS'] === false) {
				throw new Exception('Ошибка при получении идентификатора соц.статуса');
			}
		}

		if (!empty($params['SNILS'])) {
			$params['SNILS'] = str_replace(array(' ','-'), '', $params['SNILS']);
		}

		$fields_str = !empty($fields)?implode(",\n", $fields).',':'';

		$query = "
			declare
				@Res bigint = :Person_id,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_PersonAll_ins
				@Person_id = @Res output,
				{$fields_str}
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as Person_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при сохранении данных человека');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		if (array_key_exists('BDZID', $params) && empty($params['BDZID'])) {
			$this->db->query("
				update Person with(rowlock) 
				set BDZ_id = null 
				where Person_id = :Person_id
			", $params);
		}

		return $resp[0]['Person_id'];
	}


	/**
	 * @param string $nationality
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	function setNationality($nationality, $data) {
		if ($this->read_only) {
			return 'readonly';
		}

		$nationality['KLCountry_id'] = $this->getFirstResultFromQuery("
			select top 1 KLCountry_id from v_KLCountry with(nolock) where KLCountry_Code = :OKSM_ALFA3
		", $nationality);
		if (empty($nationality['KLCountry_id'])) {
			throw new Exception('Ошибка при поиске страны гражданства');
		}

		$prevNationality = $this->getFirstRowFromQuery("
			select top 1
				NS.NationalityStatus_id,
				NS.KLCountry_id,
				NS.NationalityStatus_IsTwoNation,
				NS.LegalStatusVZN_id
			from 
				v_PersonState PS with(nolock)
				inner join v_NationalityStatus NS with(nolock) on NS.NationalityStatus_id = PS.NationalityStatus_id
			where 
				PS.Person_id = :Person_id
		", array(
			'Person_id' => $data['Person_id']
		), true);
		if ($prevNationality === false) {
			throw new Exception('Ошибка при получении данных гражданства');
		}
		if (empty($prevNationality)) {
			$prevNationality = array();
		}

		$params = array_merge($prevNationality, $nationality, array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		$query = "
			declare
				@Res bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_PersonNationalityStatus_ins
				@NationalityStatus_id = @Res output,
				@Person_id = :Person_id,
				@KLCountry_id = :KLCountry_id,
				@NationalityStatus_IsTwoNation = :NationalityStatus_IsTwoNation,
				@LegalStatusVZN_id = :LegalStatusVZN_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as NationalityStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при сохранении данных гражданства');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return $resp[0]['NationalityStatus_id'];
	}

	/**
	 * @param array $document
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	function setDocument($document, $data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => 0,
			'DOCTYPE' => $document['DOCTYPE'],
			'DOCSER' => !empty($document['DOCSER'])?$document['DOCSER']:null,
			'DOCNUM' => $document['DOCNUM'],
			'D_PASP' => !empty($document['D_PASP'])?$document['D_PASP']:null,
		);

		if ($this->read_only) {
			return 'readonly';
		}

		if (!empty($params['DOCSER'])) {
			$params['DOCSER'] = str_replace(array(' ','-'), '', $params['DOCSER']);
		}

		$query = "
			declare
				@PersonDocument_id bigint,
				@Document_id bigint,
				@Error_Code int,
				@Error_Message varchar(400);
			declare @DocumentType_id bigint = (
				select top 1 DocumentType_id
				from v_DocumentType with(nolock)
				where DocumentType_Code = :DOCTYPE
			);
			exec p_PersonDocument_ins
				@PersonDocument_id = @PersonDocument_id output,
				@Document_id = @Document_id output,
				@DocumentType_id = @DocumentType_id,
				@Document_Ser = :DOCSER,
				@Document_Num = :DOCNUM,
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@Document_begDate = :D_PASP,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Document_id as Document_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при сохранении данных документа');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return $resp[0]['Document_id'];
	}

	/**
	 * @param array $polis
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	function createPolis($polis, $data) {
		$params = array(
			'Region_id' => getRegionNumber(),
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => 0,
			'POLISTYPE' => $polis['POLISTYPE'],
			'SMOCODE' => $polis['SMOCODE'],
			'POLISSER' => !empty($polis['POLISSER'])?$polis['POLISSER']:null,
			'POLISNUM' => !empty($polis['POLISNUM'])?$polis['POLISNUM']:null,
			'POLISBEGDT' => $polis['POLISBEGDT'],
			'POLISENDDT' => !empty($polis['POLISENDDT'])?$polis['POLISENDDT']:null,
			'POLISCLOSECAUSE' => !empty($polis['POLISCLOSECAUSE'])?$polis['POLISCLOSECAUSE']:null,
		);

		if (empty($params['POLISNUM']) && $params['POLISTYPE'] == 3 && !empty($polis['ENP'])) {
			$params['POLISNUM'] = $polis['ENP'];
		}

		if ($this->read_only) {
			return 'readonly';
		}

		$query = "
			declare
				@PersonEvn_id bigint,
				@Polis_id bigint,
				@Error_Code int,
				@Error_Message varchar(400);
			declare @OmsSprTerr_id bigint = (
				select top 1 OmsSprTerr_id
				from v_OmsSprTerr with(nolock)
				where KLRgn_id = :Region_id
			)
			declare @PolisType_id bigint = (
				select top 1 PolisType_id
				from v_PolisType with(nolock)
				where PolisType_CodeF008 = :POLISTYPE
			)
			declare @OrgSmo_id bigint = (
				select top 1 OrgSmo_id 
				from v_OrgSmo with(nolock)
				where OrgSmo_f002smocod = :SMOCODE
			)
			declare @PolisCloseCause_id bigint = (
				select top 1 PolisCloseCause_id
				from v_PolisCloseCause with(nolock)
				where PolisCloseCause_Code = :POLISCLOSECAUSE
			)
			exec p_PersonPolis_ins
				@PersonPolis_id = @PersonEvn_id output,
				@Polis_id = @Polis_id output,
				@OmsSprTerr_id = @OmsSprTerr_id,
				@PolisType_id = @PolisType_id,
				@OrgSmo_id = @OrgSmo_id,
				@Polis_Ser = :POLISSER,
				@Polis_Num = :POLISNUM,
				@PersonPolis_insDT = :POLISBEGDT,
				@Polis_begDate = :POLISBEGDT,
				@Polis_endDate = :POLISENDDT,
				@PolisCloseCause_id = @PolisCloseCause_id,
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Polis_id as Polis_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при сохранении данных полиса');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return $resp[0]['Polis_id'];
	}

	/**
	 * @param array $polis
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	function updatePolis($polis, $data) {
		$params = array(
			'PersonPolis_id' => !empty($data['PersonPolis_id'])?$data['PersonPolis_id']:$polis['PersonPolis_id'],
			'Polis_id' => !empty($data['Polis_id'])?$data['Polis_id']:$polis['Polis_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if ($this->read_only) {
			return $params['Polis_id']?$params['Polis_id']:'readonly';
		}

		$query = "
			select top 1
				PP.Server_id,
				PP.OmsSprTerr_id,
				PP.PolisType_id,
				PP.OrgSmo_id,
				PP.Polis_Ser,
				PP.Polis_Num,
				convert(varchar(10), PP.Polis_begDate, 120) as Polis_begDate,
				convert(varchar(10), PP.Polis_endDate, 120) as Polis_endDate,
				PP.PolisCloseCause_id
			from
				v_PersonPolis PP with(nolock)
			where
				PP.PersonPolis_id = :PersonPolis_id
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при получени данных полиса для обновления');
		}
		$params = array_merge($params, $resp);

		$PolisTypeQuery = "
			select top 1 PolisType_id
			from v_PolisType with(nolock)
			where PolisType_CodeF008 = :POLISTYPE
		";
		$OrgSmoQuery = "
			select top 1 OrgSmo_id 
			from v_OrgSmo with(nolock) 
			where OrgSmo_f002smocod = :SMOCODE			
		";
		$PolisCloseCauseQuery = "
			select top 1 PolisCloseCause_id
			from v_PolisCloseCause with(nolock)
			where PolisCloseCause_Code = :POLISCLOSECAUSE
		";

		$map = array(
			'POLISTYPE' => array(
				'field' => 'PolisType_id',
				'query' => $PolisTypeQuery
			),
			'SMOCODE' => array(
				'field' => 'OrgSmo_id',
				'query' => $OrgSmoQuery
			),
			'POLISSER' => 'Polis_Ser',
			'POLISNUM' => 'Polis_Num',
			'POLISBEGDT' => 'Polis_begDate',
			'POLISENDDT' => 'Polis_endDate',
			'POLISCLOSECAUSE' => array(
				'field' => 'PolisCloseCause_id',
				'query' => $PolisCloseCauseQuery
			)
		);

		$vars = array();
		$fields = array();

		foreach($map as $key => $dbKey) {
			$query = null;
			if (is_array($dbKey)) {
				$query = $dbKey['query'];
				$dbKey = $dbKey['field'];
			}
			if (!array_key_exists($key, $polis)) {
				$fields[] = "@{$dbKey} = :{$dbKey}";
			} else if (!empty($query)) {
				$vars[] = "declare @{$dbKey} bigint = ({$query});";
				$fields[] = "@{$dbKey} = @{$dbKey}";
				$params[$key] = $polis[$key];
			} else {
				$fields[] = "@{$dbKey} = :{$key}";
				$params[$key] = $polis[$key];
			}
		}

		$vars_str = implode("\n", $vars);
		$fields_str = implode(",\n", $fields);

		$query = "
			declare
				@PersonPolis_id bigint,
				@Polis_id bigint,
				@Error_Code int,
				@Error_Message varchar(400);
			set @PersonPolis_id = :PersonPolis_id;
			{$vars_str}
			exec p_PersonPolis_upd
				@PersonPolis_id = @PersonPolis_id output,
				@Polis_id = @Polis_id output,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@OmsSprTerr_id = :OmsSprTerr_id,
				{$fields_str},
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Polis_id as Polis_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при обновлении данных полиса');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return $resp[0]['Polis_id'];
	}

	/**
	 * @param array $polis
	 * @param array $data
	 * @throws Exception
	 */
	function deletePolis($polis, $data) {
		if ($this->read_only) return;

		$params = array(
			'PersonPolis_id' => $polis['PersonPolis_id'],
			'Server_id' => $polis['Server_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400);
			begin try
			exec xp_PersonRemovePersonEvn
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonPolis_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			end try
			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при удалении данных полиса');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		if ($polis['POLISTYPE'] == 3) {
			$this->deletePolisEdNum($polis, $data);
		}
	}

	/**
	 * @param array $polis
	 * @param array $data
	 * @throws Exception
	 */
	function deletePolisEdNum($polis, $data) {
		if ($this->read_only) return;

		$query = "
			select top 1 
				PersonPolisEdNum_id,
				Server_id
			from 
				v_PersonPolisEdNum with(nolock)
			where 
				Person_id = :Person_id
				and PersonPolisEdNum_EdNum = :ENP
				and PersonPolisEdNum_insDate <= :POLISBEGDT
			order by 
				PersonPolisEdNum_insDate desc
		";
		$resp = $this->getFirstRowFromQuery($query, $polis, true);
		if ($resp === false) {
			throw new Exception('Ошибка при получении данных ед.номера полиса для удаления');
		}

		if (empty($resp)) return;

		$params = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $resp['Server_id'],
			'PersonPolisEdNum_id' => $resp['PersonPolisEdNum_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400);
			begin try
			exec xp_PersonRemovePersonEvn
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonPolisEdNum_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			end try
			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при удалении ед.номера полиса');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}
	}

	/**
	 * @param array $polis
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	function setPolisEdNum($polis, $data) {
		if ($this->read_only) {
			return 'readonly';
		}

		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => 0,
			'ENP' => $polis['ENP'],
			'POLISBEGDT' => $polis['POLISBEGDT'],
		);

		$query = "
			select top 1 
				PersonPolisEdNum_id
			from 
				v_PersonPolisEdNum with(nolock)
			where 
				Person_id = :Person_id
				and PersonPolisEdNum_EdNum = :ENP
				and PersonPolisEdNum_insDate <= :POLISBEGDT
			order by 
				PersonPolisEdNum_insDate desc
		";
		$PersonPolisEdNum_id = $this->getFirstResultFromQuery($query, $params, true);
		if ($PersonPolisEdNum_id === false) {
			throw new Exception('Ошибка при проверке существования ед. номера полиса');
		}
		if (!empty($PersonPolisEdNum_id)) {
			return $PersonPolisEdNum_id;
		}

		$query = "
			declare
				@Res bigint,
				@Error_Code int,
				@Error_Message varchar(400);
			exec p_PersonPolisEdNum_ins
				@PersonPolisEdNum_id = @Res output,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@PersonPolisEdNum_EdNum = :ENP,
				@PersonPolisEdNum_begDT = :POLISBEGDT,
				@PersonPolisEdNum_insDT = :POLISBEGDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as PersonPolisEdNum_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при сохранении данных полиса');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return $resp[0]['PersonPolisEdNum_id'];
	}

	/**
	 * @param array $personCard
	 * @return false|int
	 */
	function getPersonCardCount($personCard) {
		$query = "
			select top 1 count(*) as cnt
			from v_PersonCard PC with(nolock)
			inner join v_Lpu L with(nolock) on L.Lpu_id = PC.Lpu_id
			where PC.Person_id = :Person_id and 
			((
				PC.PersonCard_begDate >= :ATTACHDT
			) or (
				PC.PersonCard_begDate <= :ATTACHDT
				and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > :ATTACHDT)
			))
		";

		return $this->getFirstResultFromQuery($query, $personCard);
	}

	/**
	 * @param array $personCard
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	function setPersonCard($personCard, $data) {
		if ($this->read_only) {
			return 'readonly';
		}

		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => 0,
			'ATTACHMO' => $personCard['ATTACHMO'],
			'ATTACHDT' => $personCard['ATTACHDT'],
		);

		$query = "
			declare
				@Res bigint,
				@Error_Code int,
				@Error_Message varchar(400);
			declare @Lpu_id bigint = (
				select top 1 Lpu_id
				from v_Lpu with(nolock)
				where Lpu_f003mcod = :ATTACHMO
			)
			exec p_PersonCard_ins
				@PersonCard_id = @Res output,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@Lpu_id = @Lpu_id,
				@LpuAttachType_id = 1,
				@PersonCard_begDate = :ATTACHDT,
				@PersonCard_IsAttachCondit = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as PersonCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при сохранении данных прикрепления');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return $resp[0]['PersonCard_id'];
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function publicateDopDispPlan($data) {
		set_time_limit(0);
		$this->setService('ExchInspectPlan');

		if (count($this->allowed_lpus) > 0 && !in_array($data['Lpu_id'], $this->allowed_lpus)) {
			return $this->createError('','Функция не доступна для Вашей МО');
		}

		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$log->start();

		$links = array();

		try {
			if (!empty($data['OrgSMO_id'])) {
				$orgsmo_ids = explode(',', $data['OrgSMO_id']);
				foreach ($orgsmo_ids as $orgsmo_id) {
					$data_one = $data;
					$data_one['OrgSMO_id'] = $orgsmo_id;
					$links = array_merge($links, $this->_publicateDopDispPlan($data_one, $log));
				}
			} else {
				$links = array_merge($links, $this->_publicateDopDispPlan($data, $log));
			}
		} catch(Exception $e) {
			$resp = $log->addPackage('DummyPackage', null);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$log->finish(false);
			$log->add(false, $e->getMessage(), $resp[0]['ServiceListPackage_id']);
			return $this->createError('',$e->getMessage());
		}

		$log->finish(true);

		return array(array(
			'success' => true,
			'Error_Msg' => '',
			'link' => $links
		));
	}

	/**
	 * @param int $PacketNumber
	 * @param int|null $MO
	 * @param int|null $SMO
	 * @return string
	 */
	function createFileName($PacketNumber, $MO = null, $SMO = null) {
		$X = 'DP';
		$Pi = 'M';
		$Ni = !empty($MO)?$MO:'';
		$R = sprintf('%02d', $this->regionNumber);

		$S = 'S';
		if (!empty($SMO)) $S .= $SMO;

		$date = date_create();
		$YY = $date->format('y');
		$MM = $date->format('m');
		$N = $PacketNumber;

		return $S.'_'.$X.$Pi.$Ni.'T'.$R.'_'.$YY.$MM.$N;
	}

	function mkpath($path) {
		$dir_arr = explode("/", $path);
		$tmp_dir = "";
		foreach($dir_arr as $dir) {
			if (empty($dir)) continue;
			$tmp_dir .= $dir.'/';
			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir);
			}
		}
	}

	/**
	 * @param array $data
	 * @param ServiceListLog $log
	 * @return array
	 * @throws Exception
	 */
	function _publicateDopDispPlan($data, $log) {
		$this->load->model('PersonDopDispPlan_model', 'dispplan');

		$this->load->library('textlog', array(
			'file' => 'TFOMSAutoInteractDisp_'.$log->begDT->format('Y-m-d').'.log',
			'format' => 'json',
			'parse_xml' => true
		));

		$zipfilelist = array();

		$LpuInfo = $this->getFirstRowFromQuery("
			select top 1 Lpu_f003mcod from v_Lpu (nolock) where Lpu_id = :Lpu_id
		", $data);
		if (!is_array($LpuInfo)) {
			throw new Exception('Ошибка при получении данных МО');
		}

		$fields = '';
		$filters = '';
		$params = array(
			'date' => $this->currentDT->format('Y-m-d'),
			'period' => null,
		);

		if (!empty($data['OrgSMO_id'])) {
			$filters .= "and Polis.OrgSmo_id = :OrgSMO_id";
			$params['OrgSMO_id'] = $data['OrgSMO_id'];
		}

		if (!in_array($this->regionNick, array('kareliya', 'ufa'))) {
			$filters .= " and P.BDZ_id is not null";
		}

		if ($this->regionNick == 'ufa') {
			$fields .= 'P.BDZ_Guid as BDZ_GUID,';
			$filters .= ' and P.BDZ_Guid is not null';
		} else {
			$fields .= 'P.BDZ_id as BDZ_ID,';
		}

		$PersonDopDispPlan_ids_str = implode(",", $data['PersonDopDispPlan_ids']);

		$query = "
			declare @date date = :date;
			select top 1
				convert(varchar(10), @date, 120) as DATA,
				PDDP.PersonDopDispPlan_Year as YEAR,
				'Insert' as OPERATIONTYPE,
				L.Lpu_f003mcod as CODE_MO,
				LPT.PassportToken_tid as ID_MO,
				null as QUART,
				0 as ZAP,
				DCP.DispCheckPeriod_Name,
				DCP.PeriodCap_id
			from
				v_PersonDopDispPlan PDDP with(nolock)
				inner join v_Lpu L with(nolock) on L.Lpu_id = PDDP.Lpu_id
				inner join fed.v_PassportToken LPT with(nolock) on LPT.Lpu_id = L.Lpu_id
				left join v_DispCheckPeriod DCP with(nolock) on DCP.DispCheckPeriod_id = PDDP.DispCheckPeriod_id
			where
				PDDP.PersonDopDispPlan_id in ({$PersonDopDispPlan_ids_str})
				and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
				and LPT.PassportToken_tid is not null
			order by
				PDDP.PersonDopDispPlan_Year desc
		";
		$header = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($header)) {
			throw new Exception('Ошибка при получении данных для заголовка файла');
		}

		$digitsMap = array(
			'I' => 1,
			'II' => 2,
			'III' => 3,
			'IV' => 4
		);
		$monthQuerterMap = array(
			'Январь' => 1,
			'Февраль' => 1,
			'Март' => 1,
			'Апрель' => 2,
			'Май' => 2,
			'Июнь' => 2,
			'Июль' => 3,
			'Август' => 3,
			'Сентябрь' => 3,
			'Октябрь' => 4,
			'Ноябрь' => 4,
			'Декабрь' => 4,
		);
		$months = array_flip(array_keys($monthQuerterMap));

		if (in_array($this->regionNick, array('perm'))) {
			if ($header['PeriodCap_id'] == 1) {
				$params['period'] = 4;
			}
			if ($header['PeriodCap_id'] == 2 && preg_match('/^([I]+)\sполугодие/', $header['DispCheckPeriod_Name'] , $matches)) {
				$params['period'] = $digitsMap[$matches[1]] * 2;
			}
			if ($header['PeriodCap_id'] == 3 && preg_match('/^([IV]+)\sквартал/', $header['DispCheckPeriod_Name'] , $matches)) {
				$params['period'] = $digitsMap[$matches[1]];
			}
			if ($header['PeriodCap_id'] == 4 && preg_match('/^(\S+)\s(\d+)$/', $header['DispCheckPeriod_Name'] , $matches)) {
				$params['period'] = $monthQuerterMap[$matches[1]];
			}
		}
		if (in_array($this->regionNick, array('kareliya'))) {
			if ($header['PeriodCap_id'] == 1) {
				$params['period'] = 12;
			}
			if ($header['PeriodCap_id'] == 2 && preg_match('/^([I]+)\sполугодие/', $header['DispCheckPeriod_Name'] , $matches)) {
				$params['period'] = $digitsMap[$matches[1]] * 6;
			}
			if ($header['PeriodCap_id'] == 3 && preg_match('/^([IV]+)\sквартал/', $header['DispCheckPeriod_Name'] , $matches)) {
				$params['period'] = $digitsMap[$matches[1]] * 3;
			}
			if ($header['PeriodCap_id'] == 4 && preg_match('/^(\S+)\s(\d+)$/', $header['DispCheckPeriod_Name'] , $matches)) {
				$params['period'] = $months[$matches[1]] + 1;
			}
		}
		if (in_array($this->regionNick, array('ufa'))) {
			if ($header['PeriodCap_id'] == 4 && preg_match('/^(\S+)\s(\d+)$/', $header['DispCheckPeriod_Name'] , $matches)) {
				$params['period'] = $months[$matches[1]] + 1;
			} else {
				$params['period'] = null;
			}
		}

		if ($header['PeriodCap_id'] == 3) {
			$header['QUART'] = $params['period'];
		}

		$query = "
			declare @date date = :date;
			declare @period int = :period;
			select
				PPL.PlanPersonList_id as ObjectID,
				PS.Person_id as ID_PAC,
				{$fields}
				isnull(PATIENT.GUID, newid()) as GUID,
				upper(rtrim(PS.Person_SurName)) as FAM,
				upper(rtrim(PS.Person_FirName)) as IM,
				upper(rtrim(PS.Person_SecName)) as OT,
				Sex.Sex_fedid as W,
				convert(varchar(10), PS.Person_BirthDay, 120) as DR,
				BA.Address_Address as MR,
				DT.DocumentType_Code as DOCTYPE,
				case when DT.DocumentType_Code = 14 and len(D.Document_Ser) = 4
					then substring(D.Document_Ser, 1, 2)+' '+substring(D.Document_Ser, 3, 2)
					else D.Document_Ser
				end as DOCSER,
				D.Document_Num as DOCNUM,
				null as DOCDATE,
				PS.Person_Snils as SNILS,
				null as KAT_LG,
				P.BDZ_Guid as COMENTP,
				case 
					when PolisType.PolisType_CodeF008 = 3 
					then PS.Person_EdNum 
				end as ENP,
				PolisType.PolisType_CodeF008 as VPOLIS,
				case 
					when PolisType.PolisType_CodeF008 = 3 
					then PS.Person_EdNum else Polis.Polis_Num
				end as NPOLIS,
				Polis.Polis_Ser as SPOLIS,
				Smo.Orgsmo_id as SMOID,
				Smo.Orgsmo_f002smocod as SMOCOD,
				MSF.Person_Snils as DOC_SNILS,
				convert(varchar(10), PC.PersonCard_begDate, 120) as PERSONATTACHDATE,
				case when IsAttachCondit.YesNo_Code = 1 then 1 else 2 end as PERSONATTACHMETHOD,
				0 as PERSONATTACHTYPE,
				LR.LpuRegion_Name as LPUREGION_NAME,
				LRT.LpuRegionType_Code as LPUREGIONTYPE,
				coalesce(SectionCode.Value, LS.LpuSection_Code) as LPUSECTION_CODETFOMS,
				coalesce(BuildingCode.Value, LB.LpuBuilding_Code) as LPUBUILDING_CODETFOMS,
				case when isnull(EPLDD13.EvnPLDispDop13_IsRefusal, 1) = 2
					then convert(varchar(10), EPLDD13.EvnPLDispDop13_setDT, 120)
				end as REJECT_DATE,
				@period as PERIOD,
				case
					when PDDP.DispClass_id = 1 then 'ДВ4'
					else 'ОПВ'
				end as DISP,
				nullif(rtrim(isnull(PS.Person_Phone, PInfo.PersonInfo_InternetPhone)),'') as TEL1,
				null as TEL2
			from
				v_PlanPersonList PPL with(nolock)
				inner join v_PersonDopDispPlan PDDP with(nolock) on PDDP.PersonDopDispPlan_id = PPL.PersonDopDispPlan_id
				inner join v_DispClass DC with(nolock) on DC.DispClass_id = PDDP.DispClass_id
				inner join v_PersonState PS with(nolock) on PS.Person_id = PPL.Person_id
				inner join Person P with(nolock) on P.Person_id = PS.Person_id
				left join v_PersonInfo PInfo with (nolock) on PInfo.Person_id = PS.Person_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_PersonBirthPlace PBP with(nolock) on PBP.Person_id = PS.Person_id
				left join v_Address BA with(nolock) on BA.Address_id = PBP.Address_id
				left join v_Document D with(nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with(nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PolisType with(nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
				left join v_PlanPersonListStatus PPLS with(nolock) on PPLS.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
				left join v_PersonPrivilegeWOW PPW with(nolock) on PPW.Person_id = P.Person_id
				outer apply (
					select top 1 PC.*
					from v_PersonCard PC with(nolock)
					where PC.Person_id = PS.Person_id
					and PC.PersonCard_endDate is null
					and PC.LpuAttachType_id = 1
					order by PC.PersonCard_begDate desc
				) PC
				outer apply (
					select top 1 PP.* 
					from v_PersonPrivilege PP with(nolock)
					where PP.Person_id = PS.Person_id
					and PP.PersonPrivilege_begDate <= cast(cast(PDDP.PersonDopDispPlan_Year as varchar)+'-12-31' as date)
					and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate > cast(cast(PDDP.PersonDopDispPlan_Year as varchar)+'-12-31' as date))
					and not exists (
						select * from v_PersonRefuse t with(nolock)
						where Person_id = PP.Person_id and PersonRefuse_IsRefuse = 2
						and PersonRefuse_Year = PDDP.PersonDopDispPlan_Year
					)
					order by PP.PersonPrivilege_begDate desc
				) PP
				outer apply (
					select dbo.Age2(
						PS.Person_BirthDay, 
						cast(cast(PDDP.PersonDopDispPlan_Year as varchar)+'-12-31' as date)
					) as Value
				) PersonAge
				left join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT with(nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_YesNo IsAttachCondit with(nolock) on IsAttachCondit.YesNo_id = isnull(PC.PersonCard_IsAttachCondit, 1)
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = LR.LpuSection_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
				outer apply (
					select top 1 MSF.*
					from v_MedStaffRegion MSFR with(nolock)
					inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
					where MSFR.LpuRegion_id = LR.LpuRegion_id
					and MSFR.MedStaffRegion_begDate <= isnull(PC.PersonCard_endDate, @date)
					and isnull(MSFR.MedStaffRegion_endDate, @date) >= PC.PersonCard_begDate
					order by
					MSFR.MedStaffRegion_isMain desc,
					MSFR.MedStaffRegion_begDate desc
				) MSF
				outer apply (
					select top 1 ASV.AttributeSignValue_id
					from v_AttributeSignValue ASV with(nolock)
					inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
					where ASign.AttributeSign_Code = 1
					and ASV.AttributeSignValue_TablePKey = LR.LpuSection_id
					and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
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
					select top 1 EPLDD13.*
					from v_EvnPLDispDop13 EPLDD13 with (nolock)
					where EPLDD13.Person_id = PS.Person_id 
					and YEAR(EPLDD13.EvnPLDispDop13_setDT) = PDDP.PersonDopDispPlan_Year
					and EPLDD13.DispClass_id = 1
				) EPLDD13
				outer apply (
					select top 1 OSL.Object_Guid as GUID
					from v_ObjectSynchronLog OSL with(nolock)
					inner join v_ObjectSynchronLogService OSLS with(nolock) on OSLS.ObjectSynchronLogService_id = OSL.ObjectSynchronLogService_id
					where OSLS.ObjectSynchronLogService_SysNick = 'TFOMSAutoInteract' 
					and OSL.Object_Name = 'PATIENT' and OSL.Object_id = PS.Person_id
					order by OSL.Object_setDT desc
				) PATIENT
			where
				PDDP.PersonDopDispPlan_id in ({$PersonDopDispPlan_ids_str})
				and isnull(PPLS.PlanPersonListStatusType_id, 1) in (1,5)
				and nullif(Smo.Orgsmo_f002smocod, '') is not null
				{$filters}
			order by
				Smo.OrgSmo_id
		";

		//echo getDebugSQL($query, $params);exit;
		$fullBodyList = $this->queryResult($query, $params);
		if (!is_array($fullBodyList)) {
			throw new Exception('Ошибка при получении данных записей для файла');
		}
		if (count($fullBodyList) == 0) {
			return $zipfilelist;
		}

		$bodyListBySMO = array();
		if (!empty($data['OrgSMO_id'])) {
			$bodyListBySMO[$fullBodyList[0]['SMOCOD']] = $fullBodyList;
		} else {
			$bodyListBySMO[0] = $fullBodyList;
		}

		//Подключаемся к очереди
		$packageType = 'DISPPLAN';
		list($queue, $channel) = $this->getQueue('publisher', 'common');

		$this->beginTransaction();

		foreach($bodyListBySMO as $SMO => $bodyList) {
			$filename = $this->createFileName($data['PacketNumber'], $LpuInfo['Lpu_f003mcod'], $SMO);
			$zipfilename = $filename . '.zip';
			$xmlfilename = $filename . '.xml';

			$out_dir = "pddp_xml_".time()."_".$data['Lpu_id'];
			$out_path = EXPORTPATH_REGISTRY.$out_dir;
			if (!is_dir($out_path)) $this->mkpath($out_path);

			$zipfilepath = $out_path."/".$zipfilename;
			$xmlfilepath = $out_path."/".$xmlfilename;

			// Создаём файл
			if (!$this->read_only) {
				$exportData = $this->dispplan->savePersonDopDispPlanExport(array(
					'PersonDopDispPlanExport_FileName' => $filename,
					'PersonDopDispPlanExport_PackNum' => $data['PacketNumber'],
					'OrgSmo_id' => !empty($SMO)?$bodyList[0]['SMOID']:null,
					'Lpu_id' => $data['Lpu_id'],
					'PersonDopDispPlanExport_Year' => $header['YEAR'],
					'PersonDopDispPlanExport_IsCreatedTFOMS' => 2,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!is_array($exportData) || empty($exportData[0]['PersonDopDispPlanExport_id'])) {
					throw new Exception('Ошибка сохранения данных экспорта');
				}

				// Блокируем файл
				$this->dispplan->setPersonDopDispPlanExportIsUsed(array(
					'PersonDopDispPlanExport_id' => $exportData[0]['PersonDopDispPlanExport_id'],
					'PersonDopDispPlanExport_IsUsed' => 1
				));
			}

			//Отправка в очередь ТФОМС
			$_bodyList = array();
			foreach ($bodyList as $body) {
				$package = $header;
				$package['MESSAGE_ID'] = $this->GUID();
				$package['SMOCOD'] = $body['SMOCOD'];
				$package['BODY'][] = $body;

				if ($this->allowSaveGUID) {
					$this->sync->saveObjectSynchronLog('PATIENT', $body['ID_PAC'], $body['GUID']);
				}

				$packageBody = $this->createPackageBody($packageType, $package);
				$properties = $this->createPackageProperties($packageType, $package['MESSAGE_ID']);
				$errors = $this->checkPackage($packageType, $packageBody);

				$packageTypesMap = $this->getPackageTypeMap();
				if(!empty($packageTypesMap) && isset($packageTypesMap[$packageType])){
					$packageTypesRegion = $packageTypesMap[$packageType];
				}else{
					$packageTypesRegion = $packageType;
				}

				$resp = $log->addPackage('PlanPersonList', $body['ObjectID'], $package['MESSAGE_ID'], $data['Lpu_id'], $packageTypesRegion, $package['OPERATIONTYPE'], $packageBody);
				if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
				$packageId = $resp[0]['ServiceListPackage_id'];

				if (count($errors) > 0) {
					$log->setPackageStatus($packageId, 'ErrFormed');
					$log->add(false, array_merge(["Пакет не сформирован:"], $errors), $packageId);

                    if (!$this->read_only) $this->dispplan->setPlanPersonListStatus(array(
                        'PlanPersonList_id' => $body['ObjectID'],
                        'PersonDopDispPlanExport_id' => $exportData[0]['PersonDopDispPlanExport_id'],
                        'PlanPersonList_ExportNum' => $body['ObjectID'],
                        'PlanPersonListStatusType_id' => 4, // Ошибки
                        'pmUser_id' => $data['pmUser_id']
                    ));
					continue;
				}
				if (!empty($_REQUEST['getDebug']) && !empty($_REQUEST['errors'])) {
					$packageBody = $this->injectErrorsInPackage($packageBody);
				}

				$log->setPackageStatus($packageId, 'Formed');

				try {
					$msg = new AMQPMessage($packageBody, $properties);
					$channel->basic_publish($msg, '', $queue);

					if (!$this->read_only) $this->dispplan->setPlanPersonListStatus(array(
						'PlanPersonList_id' => $body['ObjectID'],
						'PersonDopDispPlanExport_id' => $exportData[0]['PersonDopDispPlanExport_id'],
						'PlanPersonList_ExportNum' => $body['ObjectID'],
						'PlanPersonListStatusType_id' => 2, // Отправлена в ТФОМС
						'pmUser_id' => $data['pmUser_id']
					));

					$log->setPackageStatus($packageId, 'Sent');

					$_bodyList[] = $body;
				} catch(Exception $e) {
					$log->setPackageStatus($packageId, 'ErrSent');
					$log->add(false, ["Ошибка отпавки пакета:", $e->getMessage()], $packageId);
				}
			}

			$header['SMOCOD'] = isset($_bodyList[0])?$_bodyList[0]['SMOCOD']:'';
			$header['ZAP'] = count($_bodyList);

			$xml = $this->createPackageBody('DISPPLAN', array_merge($header, array('BODY' => $_bodyList)));
			file_put_contents($xmlfilepath, $xml);

			// запаковываем
			$zip = new ZipArchive();
			$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
			$zip->AddFile($xmlfilepath, $xmlfilename);
			$zip->close();

			if (!$this->read_only) {
				foreach ($data['PersonDopDispPlan_ids'] as $PersonDopDispPlan_id) {
					// Сохраняем линки
					$this->dispplan->savePersonDopDispPlanLink(array(
						'PersonDopDispPlan_id' => $PersonDopDispPlan_id,
						'PersonDopDispPlanExport_id' => $exportData[0]['PersonDopDispPlanExport_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}

				// Пишем ссылку
				$query = "
					update PersonDopDispPlanExport with(rowlock)
					set PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink 
					where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
				";
				$this->db->query($query, array(
					'PersonDopDispPlanExport_id' => $exportData[0]['PersonDopDispPlanExport_id'],
					'PersonDopDispPlanExport_DownloadLink' => $zipfilepath
				));

				// Снимаем блокировку
				$this->dispplan->setPersonDopDispPlanExportIsUsed(array(
					'PersonDopDispPlanExport_id' => $exportData[0]['PersonDopDispPlanExport_id'],
					'PersonDopDispPlanExport_IsUsed' => null
				));
			}

			$zipfilelist[] = $zipfilepath;
		}

		//Отключение от очереди
		$this->closeConnections();

		$this->commitTransaction();

		return $zipfilelist;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function consumeDopDospPlan($data) {
		set_time_limit(0);

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$this->setService('ExchInspectPlan');

		$log = new ServiceListLog($this->ServiceList_id, $pmUser_id);

		$log->start();

		$result = $this->runConsumer($data, 'answer', $log);
		if (!$this->isSuccessful($result)) {
			return $result;
		}

		$log->finish(true);

		return array(array(
			'success' => true,
			'ServiceList_id' => $log->getId()
		));
	}

    /**
     * @param array $package
     * @param string $body
     * @param array $properties
     * @param array $data
     * @param ServiceListLog $log
     * @throws Exception
     */
	function import_package_ANSWER_DISPPLAN($package, $body, $properties, $data, $log) {
		$this->load->model('PersonDopDispPlan_model', 'dispplan');

		$messageId = $package['HEADER']['MESSAGE_ID'];

		$params = array(
			'ServiceList_id' => $this->ServiceList_id,
			'ServiceListPackage_ObjectName' => 'PlanPersonList',
			'ServiceListPackage_GUID' => $messageId,
		);
		$query = "
			select top 1
				SLP.ServiceListPackage_id,
				PPL.PlanPersonList_id,
				PPL.PersonDopDispPlanExport_id
			from stg.v_ServiceListPackage SLP with(nolock)
			inner join stg.v_ServiceListLog SLL with(nolock) on SLL.ServiceListLog_id = SLP.ServiceListLog_id
			inner join v_PlanPersonList PPL with(nolock) on PPL.PlanPersonList_id = SLP.ServiceListPackage_ObjectID
			where SLL.ServiceList_id = :ServiceList_id
			and SLP.ServiceListPackage_ObjectName like :ServiceListPackage_ObjectName
			and SLP.ServiceListPackage_GUID = :ServiceListPackage_GUID
		";
		$resp = $this->getFirstRowFromQuery($query, $params, true);
		if (!is_array($resp)) {
			$resp = $log->addPackage('DummyPackage', null, $messageId);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$log->add(false, 'Не найден идентификатор объекта из ответа', $resp[0]['ServiceListPackage_id']);
			$log->addPackageData($resp[0]['ServiceListPackage_id'], $body);
			return;
		}
		$PlanPersonList_id = $resp['PlanPersonList_id'];
		$PersonDopDispPlanExport_id = $resp['PersonDopDispPlanExport_id'];
		$packageId = $resp['ServiceListPackage_id'];

		$resp = $log->addPackageData($packageId, $body);
		if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);

		$errors = $this->checkPackage('ANSWER', $body);
		if (count($errors) > 0) {
			$this->textlog->add(['properties' => $properties, 'body' => $body, 'errors' => $errors]);
			$log->add(false, array_merge(["Пакет отклонен:"], $errors), $packageId);
			$log->setPackageStatus($packageId, 'Rejected');
			return false;
		}

		if ($package['BODY']['RESULT'] == 'OK') {
			$log->add(true, "Принят ТФОМС", $packageId);
			$log->setPackageStatus($packageId, 'AcceptedTFOMS');

			if (!$this->read_only) $this->dispplan->setPlanPersonListStatus(array(
				'PlanPersonList_id' => $PlanPersonList_id,
				'PlanPersonListStatusType_id' => 3, // Принята ТФОМС
				'pmUser_id' => $data['pmUser_id']
			));
		} else {
			if (isset($package['BODY']['RESULT']['ERROR_RESULT']['RESULT_NAME'])) {
				$errorCode = $package['BODY']['RESULT']['ERROR_RESULT']['RESULT_CODE'];
				$errorMsg = $package['BODY']['RESULT']['ERROR_RESULT']['RESULT_NAME'];
				$errors = [
					$package['BODY']['RESULT']['ERROR_RESULT']['RESULT_NAME']
				];
			} else {
				$errorCode = $package['BODY']['RESULT']['ERROR_RESULT'][0]['RESULT_CODE'];
				$errorMsg = $package['BODY']['RESULT']['ERROR_RESULT'][0]['RESULT_NAME'];
				$errors = array_map(function($item) {
					return $item['RESULT_NAME'];
				}, $package['BODY']['RESULT']['ERROR_RESULT']);
			}

			$log->add(true, array_merge(["Получен ответ с ошибкой:"], $errors), $packageId);
			$log->setPackageStatus($packageId, 'RejectedTFOMS');

			if (!$this->read_only) $this->dispplan->setPlanPersonListStatus([
				'PlanPersonList_id' => $PlanPersonList_id,
				'PlanPersonListStatusType_id' => 4, // Ошибка
				'pmUser_id' => $data['pmUser_id']
			]);

			$ExportErrorPlanDDType_id = $this->getFirstResultFromQuery("
				select ExportErrorPlanDDType_id
				from v_ExportErrorPlanDDType (nolock)
				where ExportErrorPlanDDType_Code = :ExportErrorPlanDDType_Code
			", [
				'ExportErrorPlanDDType_Code' => $errorCode
			]);

			if (!$this->read_only) $this->saveExportErrorPlanDD([
				'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
				'ExportErrorPlanDDType_id' => $ExportErrorPlanDDType_id,
				'ExportErrorPlanDD_Description' => $errorMsg,
				'PlanPersonList_id' => $PlanPersonList_id,
				'pmUser_id' => $data['pmUser_id']
			]);
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function publicatePers($data) {
		if (empty($data['begPeriod']) && empty($data['exportId'])) {
			return $this->createError('','Нужно передать период выгрузки или идентификатор записи');
		}
		if (!empty($data['begPeriod']) && empty($data['endPeriod'])) {
			return $this->createError('','Не передана дата окончания периода для выгрузки');
		}
		if (!empty($data['exportId']) && !is_array($data['exportId'])) {
			$data['exportId'] = array($data['exportId']);
		}

		$this->setService('TFOMSAutoInteract');
		$packageType = 'PERS';
		$disableSend = !empty($data['disableSend']);
		$objectMap = $this->getObjectMap();

		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);

		$log->start();

		$params = [
			'begPeriod' => !empty($data['begPeriod'])?$data['begPeriod']:null,
			'endPeriod' => !empty($data['endPeriod'])?$data['endPeriod']:null,
			'exportId' => !empty($data['exportId'])?$data['exportId']:null,
		];

		if (!empty($data['start']) && !empty($data['limit'])) {
			$params['start'] = $data['start'];
			$params['limit'] = $data['limit'];
		}

		$persList = $this->searchPerson($params);

		$connection = $this->connectToAMQP();
		$channel = $connection->channel();

		list($queue,,) = $channel->queue_declare($this->publisher_queues['common'], false, true, false, false);

		$count = 0;
		foreach($persList as $package) {
			$package['MESSAGE_ID'] = $this->GUID();
			$package['OPERATIONTYPE'] = 'Query';
			$package['DATA'] = $this->currentDT->format('Y-m-d');

			$body = $this->createPackageBody($packageType, $package);
			$properties = $this->createPackageProperties($packageType, $package['MESSAGE_ID']);
			$errors = $this->checkPackage($packageType, $body);

			$resp = $log->addPackage($objectMap[$packageType], $package['ObjectID'], $package['MESSAGE_ID'], $package['Lpu_id'] ?? null, $packageType, $package['OPERATIONTYPE'], $body);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			$packageId = $resp[0]['ServiceListPackage_id'];

			if (count($errors) > 0) {
				$log->add(false, array_merge(["Пакет не сформирован:"], $errors), $packageId);
				$log->setPackageStatus($packageId, 'ErrFormed');
				continue;
			}
			if (!empty($_REQUEST['getDebug']) && !empty($_REQUEST['errors'])) {
				$body = $this->injectErrorsInPackage($body);
			}

			$log->setPackageStatus($packageId, 'Formed');

			if (!$disableSend) {
				try {
					$msg = new AMQPMessage($body, $properties);
					$channel->basic_publish($msg, '', $queue);
					$log->setPackageStatus($packageId, 'Sent');
				} catch(Exception $e) {
					$log->add(false, ["Ошибка отпавки пакета:", $e->getMessage()], $packageId);
					$log->setPackageStatus($packageId, 'ErrSent');
				}
			}
			$count++;
		}

		$channel->close();
		$connection->close();

		$log->finish(true);

		return array(array('success' => true, 'count' => $count));
	}

	/**
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_GEBT($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and PR.PersonRegister_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and PR.PersonRegister_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		if ($procDataType == 'Delete') {
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PL.Lpu_id,
					PL.ObjectID,
					PL.ID as REG_ID,
					PL.GUID,
					'Delete' as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_PersonRegister PR with(nolock) on PR.PersonRegister_id = PL.ObjectID
					left join v_Morbus M (nolock) on M.Morbus_id = PR.Morbus_id
					left join v_MorbusGEBT MG (nolock) on MG.Morbus_id = M.Morbus_id
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'GEBT'
					and (
						PR.PersonRegister_id is null or 
						PR.PersonRegister_disDate is not null or 
						not exists(
							select top 1 MorbusGEBTPlan_id
							from v_MorbusGEBTPlan (nolock) 
							where MorbusGEBT_id = MG.MorbusGEBT_id
						)
					)
					{$filters_del}
					-- end where
				order by
					-- order by
					PL.Lpu_id
					-- end order by
			";
		} else {
			if ($procDataType == 'Insert') {
				$mgp_filter = "
					where mgp.MorbusGEBT_id = MG.MorbusGEBT_id and MorbusGEBTPlan_Treatment = 1
					order by MorbusGEBTPlan_updDT desc
				";
			} else {
				$mgp_filter = "
					where mgp.MorbusGEBT_id = MG.MorbusGEBT_id
					order by 
						MorbusGEBTPlan_Treatment asc, 
						MorbusGEBTPlan_updDT desc
				";
			}
			$query = "
				-- variables
				declare @dt datetime = dbo.tzGetDate();
				declare @date date = cast(@dt as date);
				-- end variables
				select
					-- select
					PR.PersonRegister_id as ObjectID,
					PR.PersonRegister_id as REG_ID,
					isnull(GEBT.GUID, newid()) as GUID,
					ProcDataType.Value as OPERATIONTYPE,
					convert(varchar(10), @date, 120) as DATA,
					P.BDZ_id as BDZ_ID,
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					mgp.MorbusGEBTPlan_Year as YEAR,
					mgp.MorbusGEBTPlan_Month as MONTH,
					mct.MedicalCareType_Code as MCTYPE,
					null as GEBTCOMMENT
					-- end select
				from
					-- from
					v_PersonRegister PR (nolock)
					inner join v_Morbus M (nolock) on M.Morbus_id = PR.Morbus_id
					inner join v_MorbusGEBT MG (nolock) on MG.Morbus_id = M.Morbus_id
					cross apply (
						select top 1 
							Lpu_id,
							MorbusGEBTPlan_Year,
							MorbusGEBTPlan_Month,
							MedicalCareType_id,
							MorbusGEBTPlan_updDT
						from v_MorbusGEBTPlan mgp (nolock) 
						{$mgp_filter}
					) mgp
					inner join v_Lpu L (nolock) on L.Lpu_id = mgp.Lpu_id
					inner join v_PersonState PS (nolock) on PS.Person_id = PR.Person_id
					inner join Person P (nolock) on P.Person_id = PS.Person_id
					inner join fed.v_MedicalCareType mct (nolock) on mct.MedicalCareType_id = mgp.MedicalCareType_id
					left join {$tmpTable} GEBT with(nolock) on GEBT.PACKAGE_TYPE = 'GEBT' 
						and GEBT.ObjectID = PR.PersonRegister_id
					outer apply (
						select case
							when GEBT.ID is null then 'Insert'
							when GEBT.ID is not null and GEBT.DATE <= mgp.MorbusGEBTPlan_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from
				where
					-- where
					PR.PersonRegisterType_id = 70
					and ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and PR.PersonRegister_disDate is null
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
	 * Отправка полисных данных для тестирования
	 */
	function publicateTestPolis() {
		$this->setService('TFOMSAutoInteract');

		$polis = array(
			'HEADER' => array(
				'OPERATIONTYPE' => 'Update',
				'BDZID' => '5922529',
			),
			'BODY' => array(
				'PERSON' => array(
					'FAM' => 'ЯРЕВ',
					'IM' => 'ТЕСТ',
					'OT' => 'ТФОМС',
					'DR' => '1990-01-03',
					'W' => '1',
					'DOCTYPE' => '14',
					'DOCSER' => '9999',
					'DOCNUM' => '999999',
					'SNILS' => '00000000000',
					'pADR' => '',
					'uADR' => '',
				),
				'POLIS_DATA' => array(
					'NEWPERSDATA' => '1',
					'PERSONPOLISID' => '951',
					'SMOCODE' => '10003',
					'POLISTYPE' => '3',
					'POLISSER' => '',
					'POLISNUM' => '1234567890124000',
					'ENP' => '1234567890124000',
					'POLISBEGDT' => '2018-06-11 00:00:00',
					'POLISENDDT' => '',
					'POLISEDITDT' => '2018-11-10 00:00:00',
					'POLISCLOSECAUSE' => '',
					'BDZID_END' => '',
					'BDZID_CLOSECAUSE' => '',
				),
				'ATTACH' => array(
					'ATTACHMO' => '100012',
					'ATTACHDT' => '2018-11-10 00:00:00',
				)
			)
		);

		$process = function($data) use(&$process) {
			$_data = array();
			foreach($data as $field => $value) {
				if (is_array($value)) {
					$_data[$field] = $process($value);
				} else if (!empty($value)) {
					$_data[$field] = $value;
				}
			}
			return $_data;
		};

		$xml = ArrayToXml($process($polis), 'POLIS');

		$properties = array(
			'app_id' => 'Promed',			//Имя сервера
			'user_id' => $this->user,		//Имя пользователя
			'type' => 'POLIS',				//Тип пакета (ServiceListPackageType_Name)
			'message_id' => GUID(),			//Идентификатор пакета
			'content_encoding' => 'utf-8',	//Кодировка
			'content_type' => 'Xml',		//Тип контента
			'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
		);

		$connection = $this->connectToAMQP();
		$channel = $connection->channel();

		list($queue,,) = $channel->queue_declare($this->consumer_queues['common'], false, true, false, false);

		$msg = new AMQPMessage($xml, $properties);

		$channel->basic_publish($msg, '', $queue);

		$channel->close();
		$connection->close();

		echo '<pre>';
		echo htmlentities($xml);
	}
}
