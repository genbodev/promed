<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Rmis_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2015 Swan Ltd.
 * @author            Valery Bondarev
 * @version            20.12.2019
 *
 * @property ObjectSynchronLog_model $SyncLog_model
 * @property-read array $soapOptions настройки для подключения к soap-сервису
 */
class Rmis_model extends swPgModel
{

	protected $_rmisConfig = array();
	protected $_soapClients = array();
	protected $_syncObjectList = array();
	protected $_syncSprList = array();
	protected $_soapOptions = null;

	protected $_execIteration = 0;
	protected $_maxExecIteration = 3;
	protected $_execIterationDelay = 300;

	public $Registry_id = null;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		ini_set("default_socket_timeout", "15");

		$this->load->library('textlog', array('file' => 'RMIS_' . date('Y-m-d') . '.log'));

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('RmisEkb');

		$this->_rmisConfig = $this->config->item('RMIS');
	}

	/**
	 * Изменение задержки между попытками выполнения запроса
	 */
	function setExecIterationDelay($delay)
	{
		$this->_execIterationDelay = $delay;
	}

	/**
	 * Инициализация настроек для подключения к soap-сервису
	 */
	function initSoapOptions($session, $tryConnection = false)
	{
		$this->load->helper('Options');
		$this->setSessionParams($session);
		$opt = $this->allOptions;
		//print_r($opt['rmis']);exit;
		if (!isset($opt['rmis']) || empty($opt['rmis']['rmis_login']) || empty($opt['rmis']['rmis_password'])) {
			return array('Error_Code' => 401, 'Error_Msg' => 'Отсутствуют логин и пароль. Обратитесь к администратору МО.');
		}

		$this->_soapOptions = array(
			'soap_version' => SOAP_1_1,
			'exceptions' => 1, // обработка ошибок
			'trace' => 1, // трассировка
			'connection_timeout' => 15,
			//'login' => $this->_rmisConfig['login'],
			//'password' => $this->_rmisConfig['password'],
			'login' => $opt['rmis']['rmis_login'],
			'password' => $opt['rmis']['rmis_password']
		);

		if ($tryConnection) {
			try {
				$this->exec('users', 'getVersion');
			} catch (Exception $e) {
				if ($e->getMessage() == 'Forbidden') {
					return array('Error_Code' => 403, 'Error_Msg' => 'Отказано в доступе к сервису РМИС!');
				} else {
					return array('Error_Code' => $e->getCode(), 'Error_Msg' => $e->getMessage());
				}
			}
		}

		return array('Error_Code' => null, 'Error_Msg' => '');
	}

	/**
	 * Получение настроек для подключения к soap-сервису
	 */
	protected function getSoapOptions()
	{
		if (!$this->_soapOptions) {
			$resp = $this->initSoapOptions($_SESSION);
			if (!empty($resp['Error_Msg'])) {
				throw new Exception($resp['Error_Msg'], $resp['Error_Code']);
			}
		}
		return $this->_soapOptions;
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
	{
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
	 * Возвращает идентификатор текущего сервиса, установленного в модели
	 */
	function getServiceId()
	{
		return $this->ObjectSynchronLog_model->getServiceId();
	}

	/**
	 * Выполнение запроса к сервису
	 */
	function exec($serviceType, $command, $params = null)
	{
		if (!empty($this->Registry_id)) {
			$RegistryCheckStatus_id = $this->getFirstResultFromQuery("
				select
					RegistryCheckStatus_id as \"RegistryCheckStatus_id\"
				from
					r66.Registry
				where
					Registry_id = :Registry_id
			", array(
				'Registry_id' => $this->Registry_id
			));
			if ($RegistryCheckStatus_id == 46) {
				// если отменили, то надо прерваться
				$this->textlog->add("Экспорт отменён пользователем");
				throw new Exception('Экспорт отменён', 400);
			}
		}
		$this->_execIteration++;

		$this->textlog->add("exec: {$serviceType}.{$command}, try {$this->_execIteration} of {$this->_maxExecIteration}, params:" . print_r($params, true));

		try {
			if (!empty($params)) {
				$response = $this->getSoapClient($serviceType)->$command($params);
			} else {
				$response = $this->getSoapClient($serviceType)->$command();
			}
		} catch (Exception $e) {
			$this->textlog->add("exec fail: {$serviceType}.{$command}, try {$this->_execIteration} of {$this->_maxExecIteration}. Exception: " . $e->getCode() . " " . $e->getMessage());
			$errorCode = isset($e->faultcode) ? $e->faultcode : $e->getMessage();

			$httpCode = null;
			if (in_array($errorCode, array(401))) {
				$httpCode = $e->getCode();
			} else if ($errorCode == 'HTTP') {
				switch ($e->getMessage()) {
					case 'Forbidden':
						$httpCode = 403;
						break;
				}
			}

			//Ошибка на сервере. Её можно выводить сразу. Некоторые http-ошибки тоже.
			$errorOnServer = (
				in_array($httpCode, array(401, 403)) || in_array($errorCode, array('Client', 'soap:Client', 'soap:Server'))
			);
			//Пробуем выполнить запрос ещё n-ое кол-во раз
			if (!$errorOnServer && $this->_execIteration < $this->_maxExecIteration) {
				sleep($this->_execIterationDelay);
				$response = $this->exec($serviceType, $command, $params);
			} else {
				$this->_execIteration = 0;
				throw $e;    //Посылаем ошибку на вывод
			}
		}

		$this->_execIteration = 0;

		$this->textlog->add("response: {$serviceType}.{$command}, data:" . print_r($response, true));

		return $response;
	}

	/**
	 * @param $serviceType
	 * @return mixed
	 */
	function getSoapClient($serviceType)
	{
		if (!isset($this->_soapClients[$serviceType])) {
			$rmis_path = $this->_rmisConfig['url'];

			$search_rmis_path = array('https://rmis66.mis66.ru');

			if (!in_array($rmis_path, $search_rmis_path) && preg_match('/^https:\/\/(.+)(:\d+)?$/U', $rmis_path, $matches)) {
				$protocol = 'http://';
				$host = $matches[1];
				$socket = !empty($matches[2]) ? $matches[2] : ':443';

				$search_rmis_path[] = $protocol . $host . $socket;
			}

			$url = '';
			switch ($serviceType) {
				case 'refbooksWS':
					$url = $rmis_path . '/refbooks-ws/refbooksWS?wsdl';
					break;
				case 'individuals':
					$url = $rmis_path . '/individuals-ws/individuals?wsdl';
					break;
				case 'patient':
					$url = $rmis_path . '/patients-ws/patient?wsdl';
					break;
				case 'users':
					$url = $rmis_path . '/users-ws/users?wsdl';
					break;
				case 'cases':
					$url = $rmis_path . '/cases-ws/cases?wsdl';
					break;
				case 'visits':
					$url = $rmis_path . '/visits-ws/visits?wsdl';
					break;
				case 'hsp-records':
					$url = $rmis_path . '/hsp-records-ws/hspRecords?wsdl';
					break;
				case 'clinics':
					$url = $rmis_path . '/clinics-ws/clinics?wsdl';
					break;
				case 'departments':
					$url = $rmis_path . '/departments-ws/departments?wsdl';
					break;
				case 'services':
					$url = $rmis_path . '/services-ws/services?wsdl';
					break;
				case 'prototypes':
					$url = $rmis_path . '/services-ws/prototypes?wsdl';
					break;
				case 'resources':
					$url = $rmis_path . '/locations-ws/resources?wsdl';
					break;
				case 'employees':
					$url = $rmis_path . '/employees-ws/service?wsdl';
					break;
				case 'organizations':
					$url = $rmis_path . '/organization-ws/organizationSync?wsdl';
					break;
				case 'patients':
					$url = $rmis_path . '/patients-ws/patientSync?wsdl';
					break;
				case 'renderedServices':
					$url = $rmis_path . '/medservices-ws/renderedServices?wsdl';
					break;
				case 'locations':
					$url = $rmis_path . '/locations-ws/resources?wsdl';
					break;
				case 'addresses':
					$url = $rmis_path . '/addresses-ws/addresses-ws?wsdl';
					break;
				case 'referrals':
					$url = $rmis_path . '/referrals-ws/referrals?wsdl';
					break;
				default:
					die('Неизвестный сервис');
			}

			list($status) = @get_headers($url);
			if (empty($status) || strpos($status, '404') !== false) {
				throw new Exception("Не удалось установить соединение с сервисом", 500);
			}

			//Сохранение wsdl на сервер с обновлением раз в день
			//Нужно для проставление правильных ссылок внутри wsdl
			$wsdl_path = IMPORTPATH_ROOT . 'rmis_wsdl/';
			if (!is_dir($wsdl_path)) {
				mkdir($wsdl_path, 0777, true);
			}
			$wsdl_filepath = $wsdl_path . $serviceType . '.wsdl';
			if (!file_exists($wsdl_filepath) || date('Y-m-d', filemtime($wsdl_filepath)) != date('Y-m-d')) {
				$wsdl = file_get_contents($url);
				if (!$wsdl) {
					throw new Exception("Не удалось получить wsdl файл сервиса $serviceType", 500);
				}
				$wsdl = str_replace($search_rmis_path, $rmis_path, $wsdl);
				file_put_contents($wsdl_filepath, $wsdl);
			}
			$url = $wsdl_filepath;

			$soapOptions = $this->getSoapOptions();
			try {
				set_error_handler(array($this, 'exceptionErrorHandler'));
				$this->_soapClients[$serviceType] = new SoapClient($url, $soapOptions);
				restore_error_handler();
			} catch (Exception $e) {
				restore_error_handler();
				$this->textlog->add('SoapFault: ' . $e->getCode() . ' ' . $e->getMessage());
				throw new Exception("Не удалось установить соединение с сервисом", 500, $e);
			}
		}

		return $this->_soapClients[$serviceType];
	}

	/**
	 * Получние данных синхронизации объекта
	 */
	function getSyncObject($table, $id, $field = 'Object_id')
	{
		if (empty($id) || !in_array($field, array('Object_id', 'Object_sid'))) {
			return null;
		}

		$nick = $field;
		if (in_array($field, array('Object_sid'))) {
			$nick = 'Object_Value';
		}

		// ищем в памяти
		if (isset($this->_syncObjectList[$table]) && isset($this->_syncObjectList[$table][$nick]) && isset($this->_syncObjectList[$table][$nick][$id])) {
			if ($field == 'Object_id') {
				return $this->_syncObjectList[$table][$nick][$id]['Object_Value'];
			}
			if ($field == 'Object_sid') {
				return $this->_syncObjectList[$table][$nick][$id]['Object_id'];
			}
		}

		// ищем в бд
		$ObjectSynchronLogData = $this->ObjectSynchronLog_model->getObjectSynchronLog($table, $id, $field);
		if (!empty($ObjectSynchronLogData)) {
			$key = $ObjectSynchronLogData['Object_id'];
			$this->_syncObjectList[$table]['Object_id'][$key] = &$ObjectSynchronLogData;

			$key = $ObjectSynchronLogData['Object_Value'];
			$this->_syncObjectList[$table]['Object_Value'][$key] = &$ObjectSynchronLogData;

			if ($field == 'Object_id') {
				return $ObjectSynchronLogData['Object_Value'];
			}
			if ($field == 'Object_sid') {
				return $ObjectSynchronLogData['Object_id'];
			}
		}

		return null;
	}

	/**
	 * Получние данных из справочника
	 */
	function getSyncSpr($table, $id, $advancedKey = '', $useLinkTable = false, $allowBlank = false)
	{
		if (empty($id)) {
			return null;
		}

		// ищем в памяти
		if (isset($this->_syncSprList[$table]) && isset($this->_syncSprList[$table][$id])) {
			return $this->_syncSprList[$table][$id];
		}

		if (empty($advancedKey)) {
			$advancedKey = "{$table}_id";
		}

		// ищем в бд
		if ($useLinkTable) {
			$query = "
				select
					RMIS{$table}_id as \"id\"
				from
					r66.RMIS{$table}Link
				where
					{$advancedKey} = :{$advancedKey}
			";
		} else {
			$query = "
				select
					RMIS{$table}_id as \"id\"
				from
					r66.RMIS{$table}
				where
					{$advancedKey} = :{$advancedKey}
			";
		}

		$resp = $this->queryResult($query, array(
			$advancedKey => $id
		));

		if (!empty($resp[0]['id'])) {
			if (empty($advancedKey)) {
				$this->_syncSprList[$table][$id] = $resp[0]['id'];
			}
			return $resp[0]['id'];
		}

		if (!$allowBlank) {
			throw new Exception('Не найдена запись в RMIS' . $table . ' с идентификатором ' . $id . ' (' . $advancedKey . ')', 400);
		}

		return null;
	}

	/**
	 * Сохранение данных синхронизации объекта
	 */
	function saveSyncObject($table, $id, $value, $ins = false)
	{
		// сохраняем в памяти
		$this->_syncObjectList[$table][$id] = $value;

		// сохраняем в БД
		$this->ObjectSynchronLog_model->saveObjectSynchronLog($table, $id, $value, $ins);
	}

	/**
	 * Получение данных по человеку
	 */
	function getPersonInfo($data)
	{
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			select
				PS.Person_id as \"Person_id\",
				rtrim(PS.Person_SurName) as \"Person_SurName\",
				rtrim(PS.Person_FirName) as \"Person_FirName\",
				rtrim(PS.Person_SecName) as \"Person_SecName\",
				rtrim(PS.Person_SurName)||' '||rtrim(PS.Person_FirName)+coalesce(' '||rtrim(PS.Person_SecName),'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"Person_BirthDay\",
				to_char(PS.Person_deadDT, 'yyyy-mm-dd') as \"Person_deadDT\",
				(substring(PS.Person_Snils,0,3)
					||'-'||substring(PS.Person_Snils,3,3)
					||'-'||substring(PS.Person_Snils,6,3)
					||' '||substring(PS.Person_Snils,9,2)
				) as \"Person_Snils\",
				PS.Sex_id as \"Sex_id\",
				PS.SocStatus_id as \"SocStatus_id\",
				NS.KLCountry_id as \"KLCountry_id\",
				PS.UAddress_id as \"UAddress_id\",
				PS.PAddress_id as \"PAddress_id\",
				PDP.Address_id as \"BAddress_id\",
				D.DocumentType_id as \"DocumentType_id\",
				PS.Document_Ser as \"Document_Ser\",
				PS.Document_Num as \"Document_Num\",
				P.PolisType_id as \"PolisType_id\",
				case when rtrim(P.Polis_Ser) = '' then null else rtrim(P.Polis_Ser) end as \"Polis_Ser\",
				rtrim(P.Polis_Num) as \"Polis_Num\"
			from v_PersonState PS
			left join v_Document D on D.Document_id = PS.Document_id
			left join v_NationalityStatus NS on NS.NationalityStatus_id = PS.NationalityStatus_id
			left join v_Polis P on P.Polis_id = PS.Polis_id
			left join PersonBirthPlace PDP on PS.Person_id = PDP.Person_id
			where PS.Person_id = :Person_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные по человеку', 400);
		}
	}

	/**
	 * Получение информации об адресе
	 */
	function getAddressInfo($Address_id)
	{
		$params = array('Address_id' => $Address_id);

		$query = "
			select
				A.Address_id as \"Address_id\",
				A.Address_Address as \"Address_Address\",
				--Level 1 Страна
				Country.KLCountry_id as \"KLCountry_id\",
				coalesce(Country.KLCountry_Nick, Country.KLCountry_Name) as \"KLCountry_Name\",
				--Level 2 Регион
				RgnSocr.KLSocr_id as \"KLRgnSocr_id\",
				RgnSocr.KLSocr_Nick as \"KLRgnSocr_Nick\",
				Rgn.KLRgn_id as \"KLRgn_id\",
				Rgn.KLRgn_Name as \"KLRgn_Name\",
				--Level 3 Район
				SubRgnSocr.KLSocr_id as \"KLSubRgnSocr_id\",
				SubRgnSocr.KLSocr_Nick as \"KLSubRgnSocr_Nick\",
				SubRgn.KLSubRgn_id as \"KLSubRgn_id\",
				SubRgn.KLSubRgn_Name as \"KLSubRgn_Name\",
				--Level 4 Город
				CitySocr.KLSocr_id as \"KLCitySocr_id\",
				CitySocr.KLSocr_Nick as \"KLCitySocr_Nick\",
				City.KLCity_id as \"KLCity_id\",
				City.KLCity_Name as \"KLCity_Name\",
				--Level 5 Нселенный пункт
				TownSocr.KLSocr_id as \"KLTownSocr_id\",
				TownSocr.KLSocr_Nick as \"KLTownSocr_Nick\",
				Town.KLTown_id as \"KLTown_id\",
				Town.KLTown_Name as \"KLTown_Name\",
				--Level 6 Улица
				StreetSocr.KLSocr_id as \"KLStreetSocr_id\",
				StreetSocr.KLSocr_Nick as \"KLStreetSocr_Nick\",
				Street.KLStreet_id as \"KLStreet_id\",
				Street.KLStreet_Name as \"KLStreet_Name\",
				--Level 7 Дом+Корпус
				HouseSocr.KLSocr_id as \"KLHouseSocr_id\",
				HouseSocr.KLSocr_Nick as \"KLHouseSocr_Nick\",
				A.Address_House||coalesce(A.Address_Corpus, '') as \"Address_House\",
				--Level 8 Квартира
				A.Address_Flat as \"Address_Flat\"
			from
				v_Address A 
				left join v_KLCountry Country  on Country.KLCountry_id = A.KLCountry_id
				left join v_KLRgn Rgn  on Rgn.KLRgn_id = A.KLRgn_id
				left join v_KLSocr RgnSocr  on RgnSocr.KLSocr_id = Rgn.KLSocr_id
				left join v_KLSubRgn SubRgn  on SubRgn.KLSubRgn_id = A.KLSubRgn_id
				left join v_KLSocr SubRgnSocr  on SubRgnSocr.KLSocr_id = SubRgnSocr.KLSocr_id
				left join v_KLCity City  on City.KLCity_id = A.KLCity_id
				left join v_KLSocr CitySocr  on CitySocr.KLSocr_id = City.KLSocr_id
				left join v_KLTown Town  on Town.KLTown_id = A.KLTown_id
				left join v_KLSocr TownSocr  on TownSocr.KLSocr_id = Town.KLSocr_id
				left join v_KLStreet Street  on Street.KLStreet_id = A.KLStreet_id
				left join v_KLSocr StreetSocr  on StreetSocr.KLSocr_id = Street.KLSocr_id
				left join v_KLSocr HouseSocr  on HouseSocr.KLSocr_Name = 'ДОМ'
			where
				A.Address_id = :Address_id
			limit 1
		";

		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Получение данных о рабочем месте врача
	 */
	function getMedStaffFactInfo($data)
	{
		$params = array('MedStaffFact_id' => $data['MedStaffFact_id']);

		$query = "
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Lpu_id as \"Lpu_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				to_char(msf.WorkData_begDate, 'yyyy-mm-dd') as \"WorkData_begDate\",
				to_char(msf.WorkData_endDate, 'yyyy-mm-dd') as \"WorkData_endDate\"
			from
				v_MedStaffFact msf
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			where
				msf.MedStaffFact_id = :MedStaffFact_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			if (in_array($resp[0]['LpuSectionProfile_Code'], array('0', '0000'))) {    //Если "0. Не определен", то берем профиль с отделения
				$query = "
					select
						ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
						ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
						1 as \"NumType\"
					from v_LpuSection ls
					where ls.LpuSection_id = :LpuSection_id
					union
					select
						lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
						lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
						2 as \"NumType\"
					from dbo.v_LpuSectionLpuSectionProfile lslsp
					inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
					where lslsp.LpuSection_id = :LpuSection_id
					and lslsp.LpuSectionLpuSectionProfile_begDate <= dbo.tzGetDate()
					and (lslsp.LpuSectionLpuSectionProfile_endDate is null or lslsp.LpuSectionLpuSectionProfile_endDate > dbo.tzGetDate())
					order by NumType
				";
				$params = array('LpuSection_id' => $resp[0]['LpuSection_id']);
				$profiles = $this->queryResult($query, $params);
				if (!is_array($profiles)) {
					throw new Exception('Ошибка получение профиля отделения для рабочего места врача', 400);
				}
				foreach ($profiles as $profile) {
					if (!in_array($profile['LpuSectionProfile_Code'], array('0', '0000'))) {
						$resp[0]['LpuSectionProfile_id'] = $profile['LpuSectionProfile_id'];
						$resp[0]['LpuSectionProfile_Code'] = $profile['LpuSectionProfile_Code'];
					}
				}
			}

			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные о рабочем месте врача', 400);
		}
	}

	/**
	 * Получение данных о враче
	 */
	function getMedPersonalInfo($data)
	{
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select
				MP.Person_id, as \"Person_id\"
				MP.MedPersonal_TabCode as \"MedPersonal_TabCode\"
			from
				v_MedPersonal MP
			where
				MP.MedPersonal_id = :MedPersonal_id
				and MP.Lpu_id = :Lpu_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные о враче', 400);
		}
	}

	/**
	 * Получение данных направления
	 */
	function getEvnDirectionInfo($data)
	{
		$params = array('EvnDirection_id' => $data['EvnDirection_id']);

		$query = "
			select
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'yyyy-mm-dd') as \"EvnDirection_setDate\",
				PE.Evn_id as \"ParentEvn_id\",
				PE.EvnClass_SysNick as \"ParentEvnClass_SysNick\",
				ED.Person_id as \"Person_id\",
				ED.DirType_id as \"DirType_id\",
				ED.MedStaffFact_id as \"MedStaffFact_id\",
				ED.MedPersonal_id as \"MedPersonal_id\",
				ED.Lpu_id as \"Lpu_id\",
				ED.LpuSection_id as \"LpuSection_id\",
				ED.Lpu_did as \"Lpu_did\",
				ED.LpuSection_did as \"LpuSection_did\",
				ED.MedPersonal_did as \"MedPersonal_did\",
				dMSF.MedStaffFact_id as \"MedStaffFact_did\",
				ED.PayType_id as \"PayType_id\",
				ED.EvnDirection_Descr as \"EvnDirection_Descr\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				FailUser.Lpu_id as \"Lpu_fid\",
				DFT.DirFailType_id as \"DirFailType_id\",
				DFT.DirFailType_Name as \"DirFailType_Name\"
			from
				v_EvnDirection_all ED
				left join v_Evn PE on PE.Evn_id = ED.EvnDirection_pid
				left join v_pmUserCache FailUser on FailUser.pmUser_id = ED.pmUser_failID
				left join v_DirFailType DFT on DFT.DirFailType_id = ED.DirFailType_id
				left join v_Diag D on D.Diag_id = ED.Diag_id
				left join lateral (
					select
						MedStaffFact_id
					from v_MedStaffFact
					where MedPersonal_id = ED.MedPersonal_did and LpuSection_id = ED.LpuSection_did
				    limit 1
				) dMSF on true
			where ED.EvnDirection_id = :EvnDirection_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		return !empty($resp[0]) ? $resp[0] : null;
	}

	/**
	 * Получение данных диагнозов в КВС
	 */
	function getEvnPSDiags($data)
	{
		$params = array('EvnPS_id' => $data['EvnPS_id']);
		$EvnDiagPSFilters = "";
		if (!empty($data['onlyPrehosp']) && $data['onlyPrehosp']) {
			$EvnDiagPSFilters .= " and DST.DiagSetType_SysNick = 'priem'";
		}

		$query = "
			(select
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				DSC.DiagSetClass_SysNick as \"DiagSetClass_SysNick\",
				DST.DiagSetType_id as \"DiagSetType_id\",
				DST.DiagSetType_SysNick as \"DiagSetType_SysNick\",
				to_char(EPS.EvnPS_setDate, 'yyyy-mm-dd') as \"EvnDiagPS_setDate\",
				EPS.MedStaffFact_pid as \"MedStaffFact_id\",
				1 as \"main\"
			from
				v_EvnPS EPS
				inner join v_Diag D on D.Diag_id = EPS.Diag_pid
				left join v_DiagSetClass DSC on DSC.DiagSetClass_SysNick = 'osn'
				left join v_DiagSetType DST on DST.DiagSetType_SysNick = 'priem'
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1)
			union
			(select
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				DSC.DiagSetClass_SysNick as \"DiagSetClass_SysNick\",
				DST.DiagSetType_id as \"DiagSetType_id\",
				DST.DiagSetType_SysNick as \"DiagSetType_SysNick\",
				to_char(EDPS.EvnDiagPS_setDate, 'yyyy-mm-dd') as \"EvnDiagPS_setDate\",
				coalesce(EPS.MedStaffFact_pid, ES.MedStaffFact_id) as \"MedStaffFact_id\",
				case when DSC.DiagSetClass_SysNick = 'osn' then 1 else 0 end as \"main\"
			from
				v_EvnDiagPS EDPS
				inner join v_Diag D on D.Diag_id = EDPS.Diag_id
				left join v_EvnPS EPS on EPS.EvnPS_id = EDPS.EvnDiagPS_pid
				left join v_EvnSection ES on ES.EvnSection_id = EDPS.EvnDiagPS_pid
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
				left join v_DiagSetType DST on DST.DiagSetType_id = EDPS.DiagSetType_id
			where
				EDPS.EvnDiagPS_rid = :EvnPS_id
				{$EvnDiagPSFilters}
			order by
				EvnDiagPS_setDate desc
				)
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		} else {
			return null;
		}
	}

	/**
	 * Получение данных диагнозов движения
	 */
	function getEvnSectionDiags($data)
	{
		$params = array('EvnSection_id' => $data['EvnSection_id']);

		$query = "
			(select
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSС.DiagSetClass_id as \"DiagSetClass_id\",
				DST.DiagSetType_id as \"DiagSetType_id\",
				to_char(ES.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnDiagPS_setDate\",
				1 as \"main\"
			from
				v_EvnSection ES
				left join v_Diag D on D.Diag_id = ES.Diag_id
				left join v_DiagSetClass DSС on DSС.DiagSetClass_SysNick = 'osn'
				left join v_DiagSetType DST on DST.DiagSetType_SysNick = 'klin'
			where
				ES.EvnSection_id = :EvnSection_id
				limit 1
			)
			union
			select
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				DST.DiagSetType_id as \"DiagSetType_id\",
				to_char(EDPS.EvnDiagPS_setDate, 'yyyy-mm-dd') as \"EvnDiagPS_setDate\",
				case when DSC.DiagSetClass_SysNick = 'osn' then 1 else 0 end as \"main\"
			from
				v_EvnDiagPS EDPS
				left join v_Diag D on D.Diag_id = EDPS.Diag_id
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
				left join v_DiagSetType DST on DST.DiagSetType_id = EDPS.DiagSetType_id
			where
				EDPS.EvnDiagPS_pid = :EvnSection_id
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		} else {
			return null;
		}
	}

	/**
	 * Получение данных по КВС
	 */
	function getEvnPSInfo($data)
	{
		$params = array('EvnPS_id' => $data['EvnPS_id']);

		$query = "
			select
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.Lpu_id as \"Lpu_id\",
				EPS.Person_id as \"Person_id\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				EPS.Person_Age as \"Person_Age\",
				PS.SocStatus_id as \"SocStatus_id\",
				PS.Sex_id as \"Sex_id\",
				EPS.EvnDirection_id as \"EvnDirection_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\",
				EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\",
				EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\",
				EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\",
				EPS.PrehospType_id as \"PrehospType_id\",
				EPS.PrehospTrauma_id as \"PrehospTrauma_id\",
				EPS.PrehospToxic_id as \"PrehospToxic_id\",
				EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EPS.EvnPS_NumConv as \"EvnPS_NumConv\",
				EPS.MedStaffFact_pid as \"MedStaffFact_pid\",
				O.Okei_id as \"Okei_id\",
				O.Okei_Name as \"Okei_Name\",
				EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\",
				to_char(EPS.EvnPS_setDate, 'yyyy-mm-dd') as \"EvnPS_setDate\",
				coalesce(ESFIRST.PayType_id, EPS.PayType_id) as \"PayType_id\",
				ESLAST.Diag_id as \"ESLASTDiag_id\",
				ESLAST.Diag_Code as \"ESLASTDiag_Code\",
				ESLAST.Diag_Name as \"ESLASTDiag_Name\",
				ESLAST.Mes_sid as \"Mes_sid\",
				PMT.PayMedType_id as \"PayMedType_id\",
				DeathSvid.Diag_id as \"deathDiag_id\",
				DeathSvid.Diag_Code as \"deathDiag_Code\",
				DeathSvid.Diag_Name as \"deathDiag_Name\",
				case
					when ESLAST.LpuUnitType_SysNick is null then null
					when ESLAST.LpuUnitType_SysNick = 'polka' then 1
					when ESLAST.LpuUnitType_SysNick = 'stac' then 2
					when ESLAST.LpuUnitType_SysNick = 'pstac' then 3
					when ESLAST.LpuUnitType_SysNick = 'dstac' then 4
					when ESLAST.LpuUnitType_SysNick = 'hstac' then 5
					else 7
				end as \"MedicalCareType_id\",
				case when
					MCK.MedicalCareKind_Code = 31 then 3
					else MCK.MedicalCareKind_Code
				end as \"MedicalCareKind_Code\"
			from
				v_EvnPS EPS
				left join v_Diag D on D.Diag_id = EPS.Diag_id
				left join lateral (
					select 
						ES.EvnSection_id,
						ES.PayType_id
					from
						v_EvnSection ES 
					where
						ES.EvnSection_pid = EPS.EvnPS_id
						--and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					order by
						ES.EvnSection_setDate asc
				    limit 1
				) ESFIRST on true
				left join lateral(
					select 
						ES.EvnSection_id,
						ES.Mes_sid,
						ESD.Diag_id,
						ESD.Diag_Code,
						ESD.Diag_Name,
						LS.LpuSection_id,
						LU.LpuUnitType_SysNick,
						case
							when ES.HTMedicalCareClass_id is not null then 13
							else case when LU.LpuUnitType_SysNick = 'stac' then 5 else 6 end
						end as PayMedType_Code
					from
						v_EvnSection ES
						left join v_Diag ESD on ESD.Diag_id = ES.Diag_id
						left join v_LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
						left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
					where
						EvnSection_pid = EPS.EvnPS_id
						--and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					order by
						EvnSection_setDate desc
					limit 1
				) ESLAST on true
				left join lateral (
					select 
						UslugaComplex.UslugaComplex_id
					from
						v_EvnUsluga EvnUsluga
						inner join UslugaComplex on UslugaComplex.UslugaComplex_id = EvnUsluga.UslugaComplex_id
						inner join r66.UslugaComplexPartitionLink UCPL on UCPL.UslugaComplex_id = UslugaComplex.UslugaComplex_id
						inner join r66.UslugaComplexPartition UCP on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					where
						EvnUsluga.EvnUsluga_rid = EPS.EvnPS_id
						and UslugaComplexPartition_code='350'
					order by EvnUsluga_id
					limit 1
				) EvnUsl on true
				left join lateral(
					select 
						 D.Diag_id,
						 D.Diag_Code,
						 D.Diag_Name
					from
						v_DeathSvid t
						left join v_Diag DSD on DSD.Diag_id = coalesce(Diag_iid,Diag_tid,Diag_mid,Diag_eid,Diag_oid)
					where
						Person_id = EPS.Person_id
						and DeathSvid_IsActual = 2
					limit 1
				) DeathSvid on true
				left join lateral (
					select  MedicalCareKind_id
					from r66.LpuSectionLink 
					where LpuSection_id = ESLAST.LpuSection_id
					order by LpuSectionLink_insdt desc
				    limit 1
				) LSL on true
				left join nsi.MedicalCareKind MCK  on MCK.MedicalCareKind_id = LSL.MedicalCareKind_id
				left join v_PersonState ps on ps.Person_id = EPS.Person_id
				left join v_PayMedType PMT on PMT.PayMedType_Code = ESLAST.PayMedType_Code
				left join v_Okei O on O.Okei_id = EPS.Okei_id
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные по КВС', 400);
		}
	}

	/**
	 * Получение данных движения
	 */
	function getEvnSectionInfo($data)
	{
		$params = array('EvnSection_id' => $data['EvnSection_id']);

		$query = "
			select
				EPS.EvnPS_id as \"EvnPS_id\",
				ES.EvnSection_id as \"EvnSection_id\",
				ES.EvnSection_pid as \"EvnSection_pid\",
				ES.MedPersonal_id as \"MedPersonal_id\",
				ES.MedStaffFact_id as \"MedStaffFact_id\",
				coalesce(ES.EvnSection_IsPriem, 1) as \"EvnSection_IsPriem\",
				to_char(ES.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnSection_setDate\",
				to_char(ES.EvnSection_setTime, 'hh24:mi') as \"EvnSection_setTime\",
				to_char(ES.EvnSection_disDate, 'yyyy-mm-dd') as \"EvnSection_disDate\",
				to_char(ES.EvnSection_disTime, 'hh24:mi') as \"EvnSection_disTime\",
				EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				ES.LeaveType_id as \"LeaveType_id\",
				COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id) as \"ResultDesease_id\",
				COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOS.LeaveCause_id, EOSBP.LeaveCause_id, EOST.LeaveCause_id) as \"LeaveCause_id\",
				coalesce(EOS.LpuSection_oid,EOSBP.LpuSection_oid,EOST.LpuSection_oid) as \"LpuSection_oid\",
				ES.Lpu_id as \"Lpu_id\",
				PS.Person_id as \"Person_id\",
				to_char(PS.Person_deadDT, 'yyyy-mm-dd') as \"Person_deadDate\",
				to_char(PS.Person_deadDT, 'hh24:mi') as \"Person_deadTime\",
				--DeathSvid.MedPersonal_id as deathMedPersonal_id,
				ED.MedPersonal_id as \"deathMedPersonal_id\",
				--MCT.MedicalCareType_id,
				case
					when LU.LpuUnitType_SysNick is null then null
					when LU.LpuUnitType_SysNick = 'polka' then 1
					when LU.LpuUnitType_SysNick = 'stac' then 2
					when LU.LpuUnitType_SysNick = 'pstac' then 3
					when LU.LpuUnitType_SysNick = 'dstac' then 4
					when LU.LpuUnitType_SysNick = 'hstac' then 5
					else 7
				end as \"MedicalCareType_id\",
				ES.Mes_id as \"Mes_id\",
				ES.Mes_sid as \"Mes_sid\",
				ES.PayType_id as \"PayType_id\",
				ES.EvnSection_Index as \"EvnSection_Index\",
				ES.EvnSection_Count as \"EvnSection_Count\",
				case when ES.EvnSection_Index = ES.EvnSection_Count-1 then 1 else 0 end as \"isContinue\",
				PrevES.EvnSection_id as \"EvnSection_previd\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				LS.LpuSection_id as \"LpuSection_id\",
				coalesce(WP.LpuSectionProfile_id,ES.LpuSectionProfile_id,LS.LpuSection_id) as \"LpuSectionProfile_id\",
				NextES.EvnSection_id as \"nextEvnSection_id\",
				NextES.LpuSection_id as \"nextLpuSection_id\",
				NextES.LpuSectionProfile_id as \"nextLpuSectionProfile_id\"
			from
				v_EvnSection ES 
				inner join v_EvnPS EPS  on EPS.EvnPS_id = ES.EvnSection_pid
				inner join v_PersonState PS  on PS.Person_id = ES.Person_id
				left join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_EvnLeave EL  on EL.EvnLeave_pid = ES.EvnSection_id
				left join v_EvnDie ED  on ED.EvnDie_pid = ES.EvnSection_id
				left join v_EvnOtherLpu EOL  on EOL.EvnOtherLpu_pid = ES.EvnSection_id
				left join v_EvnOtherSection EOS  on EOS.EvnOtherSection_pid = ES.EvnSection_id
				left join v_EvnOtherSectionBedProfile EOSBP  on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
				left join v_EvnOtherStac EOST  on EOST.EvnOtherStac_pid = ES.EvnSection_id
				left join persis.WorkPlace WP on WP.id = ES.MedStaffFact_id
				left join lateral (
					select 
						EvnSection_id
					from
						v_EvnSection 
					where
						EvnSection_pid = ES.EvnSection_pid
						and EvnSection_Index = ES.EvnSection_Index-1
				    limit 1
				) PrevES on true
				left join lateral (
					select 
						NES.EvnSection_id,
						LS.LpuSection_id,
						coalesce(NWP.LpuSectionProfile_id,NES.LpuSectionProfile_id,NLS.LpuSectionProfile_id) as LpuSectionProfile_id
					from
						v_EvnSection NES
						left join v_LpuSection NLS on NLS.LpuSection_id = NES.LpuSection_id
						left join persis.WorkPlace NWP on NWP.id = NES.MedStaffFact_id
					where
						EvnSection_pid = ES.EvnSection_pid
						and EvnSection_Index = ES.EvnSection_Index+1
				    limit 1
				) NextES on true
			where
				ES.EvnSection_id = :EvnSection_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные по движению', 400);
		}
	}

	/**
	 * Получение данных о должности
	 */
	function getStaffInfo($data)
	{
		$params = array('Staff_id' => $data['Staff_id']);

		$query = "
			select
				S.id as \"Staff_id\",
				S.Rate as \"Rate\",
				p.code as \"Post_Code\",
				p.name as \"Post_Name\",
				s.Post_id as \"Post_id\",
				to_char(s.BeginDate, 'yyyy-mm-dd') as \"BeginDate\",
				to_char(s.EndDate, 'yyyy-mm-dd') as \"EndDate\",
				MSO.MedSpecOms_id as \"MedSpecOms_id\",
				MSO.MedSpecOms_Code as \"MedSpecOms_Code\"
			from
				persis.v_Staff S
				left join persis.v_Post p on p.id = s.Post_id
				left join lateral(
					select  MedSpecOms_id
					from v_MedStaffFactCache
					where Staff_id = S.id
					group by MedSpecOms_id
					order by count(MedStaffFact_id) desc
				    limit 1
				) MSF on true --Получение наиболее распространенной специальности
				left join v_MedSpecOms MSO on MSO.MedSpecOms_id = MSF.MedSpecOms_id
			where
				S.id = :Staff_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные о должности', 400);
		}
	}

	/**
	 * Получение идентификатора диагноза из РМИС
	 */
	function getRMISDiagId($diag_code)
	{
		$diagnosisId = $this->getSyncSpr('Diag', $diag_code, 'RMISDiag_Code', false, true);
		if (empty($diagnosisId) && preg_match('/^\D\d\d\./', $diag_code)) {
			$diag_code = substr($diag_code, 0, 3);    //Подменяется код диагноза на неуточненный
			$diag_code .= ($diag_code == 'K85') ? '.' : '';
			$diagnosisId = $this->getSyncSpr('Diag', $diag_code, 'RMISDiag_Code', false, true);
		}
		return $diagnosisId;
	}

	/**
	 * Получение идентификатора вида посещения из РМИС
	 */
	function getRMISVizitTypeId($data)
	{
		$vizitTypeId = null;
		if (!empty($data['VizitType_id'])) {
			$VizitType_Code = $this->getFirstResultFromQuery("
				select VizitType_Code as \"VizitType_Code\" from v_VizitType where VizitType_id = :VizitType_id limit 1
			", array('VizitType_id' => $data['VizitType_id']));

			if ($VizitType_Code == 4) {
				$vizitTypeId = 2; //Профосмотр -> Профилактика
			} else {
				$vizitTypeId = $this->getSyncSpr('VizitType', $data['VizitType_id']);
			}
		} else {
			//todo: можно все эти условия по полям прописать в таблице r66.RMISVizitType
			$DispClass_Code = !empty($data['DispClass_Code']) ? $data['DispClass_Code'] : null;
			$EducationInstitutionType_SysNick = !empty($data['EducationInstitutionType_SysNick']) ? $data['EducationInstitutionType_SysNick'] : null;
			$AgeGroupDisp_id = !empty($data['AgeGroupDisp_id']) ? $data['AgeGroupDisp_id'] : null;

			switch ($DispClass_Code) {
				case 1:
					$vizitTypeId = 10;
					break;
				case 2:
					$vizitTypeId = 11;
					break;
				case 3:
					$vizitTypeId = 12;
					break;
				case 4:
					$vizitTypeId = 13;
					break;
				case 5:
					$vizitTypeId = 16;
					break;
				case 6:
					switch ($EducationInstitutionType_SysNick) {
						case 'PreSchool':
							$vizitTypeId = 25;
							break;
						case 'School':
							$vizitTypeId = 26;
							break;
						case 'SpecSchool':
							$vizitTypeId = 27;
							break;
					}
					break;
				case 7:
					$vizitTypeId = 14;
					break;
				case 8:
					$vizitTypeId = 15;
					break;
				case 9:
					switch ($EducationInstitutionType_SysNick) {
						case 'PreSchool':
							$vizitTypeId = 19;
							break;
						case 'School':
							$vizitTypeId = 20;
							break;
						case 'SpecSchool':
							$vizitTypeId = 21;
							break;
					}
					break;
				case 10:
					if ($AgeGroupDisp_id == 153) {
						$vizitTypeId = 17;
					} else {
						$vizitTypeId = 18;
					}
					break;
				case 10:
					switch ($EducationInstitutionType_SysNick) {
						case 'PreSchool':
							$vizitTypeId = 22;
							break;
						case 'School':
							$vizitTypeId = 23;
							break;
						case 'SpecSchool':
							$vizitTypeId = 24;
							break;
					}
					break;
				case 12:
					$vizitTypeId = 18;
					break;
			}
		}
		return $vizitTypeId;
	}

	/**
	 * @return array
	 */
	function getRefbookList()
	{
		$result = $this->exec('refbooksWS', 'getRefbookList');

		$response = array();
		$index = 0;
		foreach ($result->refbook as $row) {
			$response[$index] = array();
			foreach ($row->column as $column) {
				$response[$index][$column->name] = $column->data;
			}
			$index++;
		}
		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getRefbook($data)
	{
		$refbookList = $this->getRefbookList();

		$code = '';

		foreach ($refbookList as $item) {
			if ($item['TABLE_NAME'] == $data['tableName']) {
				$code = $item['CODE'];
				break;
			}
		}

		$result = array();
		if ($code) {
			$result = $this->exec('refbooksWS', 'getRefbookPartial', array(
				'refbookCode' => $code,
				'version' => 'CURRENT',
				'partNumber' => !empty($data['partNumber']) ? $data['partNumber'] : 1
			));
		}

		$response = array();
		$index = 0;
		if (isset($result->row)) {
			foreach ($result->row as $row) {
				$response[$index] = array();
				foreach ($row->column as $column) {
					$response[$index][$column->name] = $column->data;
				}
				$index++;
			}
		}

		return $response;
	}

	/**
	 * Получение списка версий справочника из РМИС
	 */
	function getRefbookVersionList($data)
	{
		$refbookList = $this->getRefbookList();

		$code = '';

		foreach ($refbookList as $item) {
			if ($item['TABLE_NAME'] == $data['tableName']) {
				$code = $item['CODE'];
				break;
			}
		}

		$result = array();
		if ($code) {
			$result = $this->exec('refbooksWS', 'getVersionList', array(
				'refbookCode' => $code
			));
		}

		$response = array();
		$index = 0;
		if (isset($result->row)) {
			foreach ($result->row as $row) {
				$response[$index] = array();
				foreach ($row->column as $column) {
					$response[$index][$column->name] = $column->data;
				}
				$index++;
			}
		}

		return $response;
	}

	/**
	 * Получение данных из справочников РМИС
	 */
	function getRefbooks($list)
	{
		$response = array();
		$refbookList = $this->getRefbookList();
		foreach ($refbookList as $item) {
			if (!in_array($item['TABLE_NAME'], $list)) {
				continue;
			}

			$parts = $this->exec('refbooksWS', 'getRefbookParts', array(
				'refbookCode' => $item['CODE'],
				'version' => 'CURRENT'
			));

			for ($i = 1; $i <= $parts->count; $i++) {
				$result = $this->exec('refbooksWS', 'getRefbookPartial', array(
					'refbookCode' => $item['CODE'],
					'version' => 'CURRENT',
					'partNumber' => $i
				));
				$arr = array();
				$index = 0;
				if (isset($result->row)) {
					foreach ($result->row as $row) {
						$arr[$index] = array();
						foreach ($row->column as $column) {
							$arr[$index][$column->name] = $column->data;
						}
						$index++;
					}
				}
				if ($parts->count > 1) {
					$response[$item['TABLE_NAME'] . '_' . $i] = $arr;
				} else {
					$response[$item['TABLE_NAME']] = $arr;
				}
			}
		}

		return $response;
	}

	/**
	 * Создание врача
	 */
	function createEmployee($individualId, $MedPersonalInfo, $clinicId)
	{
		$params = array(
			'organization' => $clinicId,
			'employee' => array(
				'individual' => $individualId,
				'number' => $MedPersonalInfo['MedPersonal_TabCode'],
				'dissmised' => false
			)
		);

		$result = $this->exec('employees', 'createEmployee', $params);

		return $result->id;
	}

	/**
	 * Создание должности врача
	 */
	function createEmployeePosition($positionId, $employeeId, $MedStaffFactInfo)
	{
		$params = array(
			'employeePosition' => array(
				'employee' => $employeeId,
				'position' => $positionId, // справочник—pim_position—должности по штатному расписанию организации
				'fromDate' => $MedStaffFactInfo['WorkData_begDate'], // дата начала действия
				'toDate' => $MedStaffFactInfo['WorkData_endDate'] // дата окончания действия
			)
		);

		$employeePosition = $this->exec('employees', 'createEmployeePosition', $params);

		return $employeePosition->employeePosition;
	}

	/**
	 * Создание должности
	 */
	function createPosition($clinicId, $departmentId, $StaffInfo)
	{
		$roleId = $this->getSyncSpr('Post', $StaffInfo['Post_id']);

		$specialityId = null;
		if (!empty($StaffInfo['MedSpecOms_Code'])) {
			$res = $this->getFirstResultFromQuery("
				select RMISSertificateSpeciality_id as \"RMISSertificateSpeciality_id\"
				from r66.v_RMISSertificateSpeciality 
				where RMISSertificateSpeciality_Code = :MedSpecOms_Code
				limit 1
			", array(
				'MedSpecOms_Code' => $StaffInfo['MedSpecOms_Code']
			));
			if ($res) {
				$specialityId = $res;
			}
		}

		$params = array(
			'organization' => $clinicId,
			'position' => array(
				'role' => $roleId, // стыковка со справочником Persis.Post (pim_position_role)
				'rate' => $StaffInfo['Rate'],
				'code' => $StaffInfo['Post_Code'],
				'name' => $StaffInfo['Post_Name'],
				'fromDate' => $StaffInfo['BeginDate'],
				'toDate' => $StaffInfo['EndDate'],
				'department' => $departmentId,
				'speciality' => $specialityId
			)
		);

		$position = $this->exec('employees', 'createPosition', $params);

		return $position->position;
	}

	/**
	 * Создание специалиста, ведущего прием
	 */
	function createLocation($clinicId, $departmentId, $employeePositionId, $MedStaffFactInfo)
	{
		$profileId = $this->getSyncSpr('LpuSectionProfile', $MedStaffFactInfo['LpuSectionProfile_id']);

		$params = array(
			'location' => array(
				'employeePosition' => $employeePositionId,
				'department' => $departmentId,
				'organization' => $clinicId,
				'specialization' => $profileId,
				'beginDate' => $MedStaffFactInfo['WorkData_begDate'],
				'endDate' => $MedStaffFactInfo['WorkData_endDate'],
				'resourceRole' => '1',    //Врач
				//'system' => true,
			)
		);

		$location = $this->exec('locations', 'createLocation', $params);

		return $location->location;
	}

	/**
	 * Редактирование специалиста, ведущего прием
	 */
	function editLocation($locationId, $clinicId, $departmentId, $employeePositionId, $MedStaffFactInfo, $system)
	{
		$profileId = $this->getSyncSpr('LpuSectionProfile', $MedStaffFactInfo['LpuSectionProfile_id']);

		$params = array(
			'locationId' => $locationId,
			'locationData' => array(
				'employeePosition' => $employeePositionId,
				'department' => $departmentId,
				'organization' => $clinicId,
				'specialization' => $profileId,
				'beginDate' => $MedStaffFactInfo['WorkData_begDate'],
				'endDate' => $MedStaffFactInfo['WorkData_endDate'],
				'resourceRole' => '1',    //Врач
				'system' => $system,
			)
		);

		$location = $this->exec('locations', 'editLocation', $params);

		return $location->location;
	}

	/**
	 * Создание физ. лица
	 */
	function createIndividual($person)
	{
		$params = array(
			'surname' => $person['Person_SurName'],
			'name' => $person['Person_FirName'],
			'birthDate' => $person['Person_BirthDay'],
			'gender' => $person['Sex_id']
		);
		if (!empty($person['Person_SecName'])) {
			$params['patrName'] = $person['Person_SecName'];
		}
		if (!empty($person['Person_deadDT'])) {
			$params['deathDate'] = $person['Person_deadDT'];
		}

		$individualId = $this->exec('individuals', 'createIndividual', $params);

		return $individualId;
	}

	/**
	 * Создание адреса физ. лица
	 */
	function createIndividualAddress($params)
	{
		$individualAddressId = $this->exec('individuals', 'createIndividualAddress', $params);

		return $individualAddressId;
	}

	/**
	 * Добавление вида адреса к адресу физ. лица
	 */
	function addTypeToIndividualAddress($individualAddressId, $addressTypeId)
	{
		$params = array(
			'individualAddress' => $individualAddressId,
			'addressType' => $addressTypeId
		);

		$this->exec('individuals', 'addTypeToIndividualAddress', $params);

		return true;
	}

	/**
	 * Создание пациента
	 */
	function createPatient($data)
	{
		$params = array(
			'patientId' => $data['patientId'],
			'patientData' => array()
		);
		if (!empty($data['socialGroup'])) {
			$params['patientData']['socialGroup'] = $data['socialGroup'];
		}
		if (!empty($data['citizenship'])) {
			$params['patientData']['citizenship'] = $data['citizenship'];
		}

		$Person_PatientCode = $this->exec('patient', 'createPatient', $params);

		return $Person_PatientCode;
	}

	/**
	 * Получение мед. работников по физ. лицу
	 */
	function getEmployeesByIndividual($individualId)
	{
		$params = array(
			'uid' => $individualId
		);

		$result = $this->exec('employees', 'getEmployeesByIndividual', $params);

		if (!isset($result->employee)) {
			return array();
		}

		$employees = array();
		if (is_array($result->employee)) {
			$employees = $result->employee;
		} else {
			$employees[] = $result->employee;
		}

		return $employees;
	}

	/**
	 * Получение пациентов по физ. лицу
	 */
	function getPatientByIndividualEuid($individualId)
	{
		$result = $this->exec('patients', 'getPatientByIndividualEuid', $individualId);

		if (!isset($result->patient)) {
			return array();
		}

		$patients = array();
		if (is_array($result->patient)) {
			$patients = $result->patient;
		} else {
			$patients[] = $result->patient;
		}

		return $patients;
	}

	/**
	 * Получение пациента
	 */
	function getPatient($patientId)
	{
		$result = $this->exec('patients', 'getPatient', $patientId);

		if (!isset($result->patient)) {
			return array();
		}

		$patients = array();
		if (is_array($result->patient)) {
			$patients = $result->patient;
		} else {
			$patients[] = $result->patient;
		}

		return $patients;
	}

	/**
	 * Получение данных мед. работника
	 */
	function getEmployee($employeeId)
	{
		$result = $this->exec('employees', 'getEmployee', array(
			'id' => $employeeId
		));
		return (array)$result->employee;
	}

	/**
	 * Получение мед. работников организации
	 */
	function getEmployees($organizationId)
	{
		$params = array(
			'organization' => $organizationId
		);

		$result = $this->exec('employees', 'getEmployees', $params);

		if (!isset($result->employee)) {
			return array();
		}

		$employees = array();
		if (is_array($result->employee)) {
			$employees = $result->employee;
		} else {
			$employees[] = $result->employee;
		}

		return $employees;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getIndividual($data)
	{
		return (array)$this->exec('individuals', 'getIndividual', $data['Person_Code']);
	}

	/**
	 * Получение данных МО из сервиса
	 */
	function getPlace($clinicId)
	{
		$clinic = array();
		$addressTypeArr = array('legalAddress', 'actualAddress', 'postAddress');

		$result = $this->exec('clinics', 'getPlace', array('clinic' => $clinicId));

		$clinic['clinicId'] = $clinicId;
		foreach ($result->clinic as $field => $value) {
			if (in_array($field, $addressTypeArr)) {
				$clinic[$field . 'Id'] = $value->addressId;
				$clinic[$field . 'Text'] = $value->addressText;
			} else {
				$clinic[$field] = $value;
			}
		}
		foreach ($addressTypeArr as $addressType) {
			if (empty($clinic[$addressType . 'Id'])) {
				$clinic[$addressType . 'Id'] = null;
				$clinic[$addressType . 'Text'] = null;
			}
		}
		return $clinic;
	}

	/**
	 * Получение данных организации из сервиса
	 */
	function getOrganization($organizationId)
	{
		$organization = array();
		$addressTypeArr = array('legalAddress', 'actualAddress', 'postAddress');

		$result = $this->exec('organizations', 'getOrganization', $organizationId);

		$organization['organizationId'] = $organizationId;
		foreach ($result->organization as $field => $value) {
			if (in_array($field, $addressTypeArr)) {
				$organization[$field . 'Id'] = $value->addressId;
				$organization[$field . 'Text'] = $value->addressText;
			} else {
				$organization[$field] = $value;
			}
		}
		foreach ($addressTypeArr as $addressType) {
			if (empty($organization[$addressType . 'Id'])) {
				$organization[$addressType . 'Id'] = null;
				$organization[$addressType . 'Text'] = null;
			}
		}
		return $organization;
	}

	/**
	 * Получение данных МО
	 */
	function getLpuInfo($data)
	{
		$params = array('Lpu_id' => $data['Lpu_id']);

		$query = "
			select
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				L.Org_OGRN as \"Lpu_OGRN\",
				L.Org_INN as \"Lpu_INN\",
				L.Org_OKPO as \"Lpu_OKPO\",
				L.Lpu_OKATO as \"Lpu_OKATO\",
				L.Lpu_f003mcod as \"Lpu_f003mcod\"
			from v_Lpu_all L
			where L.Lpu_id = :Lpu_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные МО', 400);
		}
	}

	/**
	 * Поиск МО
	 */
	function getPlaces($data)
	{
		$params = array();

		if (!empty($data['Lpu_id'])) {
			$lpu = $this->getLpuInfo($data);
			if (empty($lpu) || empty($lpu['Lpu_id'])) {
				return $this->createError('Ошибка получения данных МО из БД');
			}
			$data = array_merge($data, $lpu);
		}

		if (!empty($data['Lpu_OGRN'])) {
			$params['ogrn'] = $data['Lpu_OGRN'];
		}
		if (!empty($data['Lpu_INN'])) {
			$params['inn'] = $data['Lpu_INN'];
		}
		if (!empty($data['Lpu_OKATO'])) {
			$params['okato'] = $data['Lpu_OKATO'];
		}
		if (!empty($data['Lpu_f003mcod'])) {
			$params['mkod'] = $data['Lpu_f003mcod'];
		}

		$result = $this->exec('clinics', 'getPlaces', $params);

		if (!isset($result->clinic)) {
			return array();
		}

		$clinicIds = array();
		if (is_array($result->clinic)) {
			foreach ($result->clinic as $clinicId) {
				$clinicIds[] = $clinicId;
			}
		} else {
			$clinicIds[] = $result->clinic;
		}
		return $clinicIds;
	}

	/**
	 * Получение данных отделения из РМИС
	 */
	function getDepartment($departmentId)
	{
		$department = array();
		$plainFields = array('clinic', 'name', 'code', 'availableDiagnosis', 'fromDate', 'toDate', 'departmentType',
			'fundingSourceType', 'accountingCenter', 'sphere');
		$arrayFields = array('profiles');

		$result = $this->exec('departments', 'getDepartment', array('departmentId' => $departmentId));

		$department['departmentId'] = $departmentId;
		foreach ($plainFields as $field) {
			$department[$field] = null;
		}
		foreach ($arrayFields as $field) {
			$department[$field] = array();
		}
		foreach ($result->department as $field => $value) {
			switch ($field) {
				case 'profiles':
					$profiles = is_array($value) ? $value : array($value);
					$i = 0;
					foreach ($profiles as $profile) {
						$department[$field][$i]['profile'] = $profile->profile;
						$department[$field][$i]['fromDt'] = isset($profile->fromDt) ? $profile->fromDt : null;
						$department[$field][$i]['toDt'] = isset($profile->toDt) ? $profile->toDt : null;
						$i++;
					}
					break;
				case 'portalDepartment':
					continue 2;
					break;
				default:
					$department[$field] = $value;
			}
		}
		return $department;
	}

	/**
	 * Получение данных должности из РМИС
	 */
	function getPosition($positionId)
	{
		$position = array();
		$plainFields = array('role', 'name', 'rate', 'code', 'fromDate', 'department');

		$result = $this->exec('employees', 'getPosition', array('id' => $positionId));

		$position['positionId'] = $positionId;
		foreach ($plainFields as $field) {
			$position[$field] = null;
		}
		foreach ($result->position as $field => $value) {
			switch ($field) {
				default:
					$position[$field] = $value;
			}
		}
		return $position;
	}

	/**
	 * Получение данных должности сотрудника из РМИС
	 */
	function getEmployeePosition($employeePositionId)
	{
		$employeePosition = array();
		$plainFields = array('employee', 'position', 'fromDate', 'toDate');

		$result = $this->exec('employees', 'getEmployeePosition', array('id' => $employeePositionId));

		$employeePosition['employeePositionId'] = $employeePositionId;
		foreach ($plainFields as $field) {
			$employeePosition[$field] = null;
		}
		foreach ($result->employeePosition as $field => $value) {
			switch ($field) {
				default:
					$employeePosition[$field] = $value;
			}
		}
		return $employeePosition;
	}

	/**
	 * Получение списка специалистов, ведущих прием в МО
	 */
	function getLocations($clinicId)
	{
		$params = array(
			'clinic' => $clinicId
		);

		$result = $this->exec('locations', 'getLocations', $params);

		if (!isset($result->location)) {
			return array();
		}

		$locations = array();
		if (is_array($result->location)) {
			$locations = $result->location;
		} else {
			$locations[] = $result->location;
		}

		return $locations;
	}

	/**
	 * Получение специалиста, ведущего прием
	 */
	function getLocation($locationId)
	{
		$location = array();

		$plainFields = array('employeePosition', 'department', 'organization', 'specialization', 'beginDate', 'endDate');

		$result = $this->exec('locations', 'getLocation', array('location' => $locationId));

		$location['locationId'] = $locationId;
		foreach ($plainFields as $field) {
			$location[$field] = null;
		}
		foreach ($result->location as $field => $value) {
			switch ($field) {
				default:
					$location[$field] = $value;
			}
		}
		return $location;
	}

	/**
	 * Синхронизация специалиста, ведущего прием, для события
	 */
	function syncLocationForEvn($EvnClass_SysNick, $Evn_id, $MedStaffFact_id)
	{
		$this->textlog->add("syncLocationForEvn $EvnClass_SysNick, $Evn_id, $MedStaffFact_id");
		$locationId = null;
		$step = null;
		$stepId = $this->getSyncObject($EvnClass_SysNick, $Evn_id);
		if (!empty($stepId)) {
			switch ($EvnClass_SysNick) {
				case 'EvnUsluga':
					$step = $this->getServiceRend($stepId);
					break;
				case 'EvnSection':
					$step = $this->getHspRecord($stepId);
					break;
				case 'EvnVizitPL':
				case 'EvnVizitDisp':
					$step = $this->getVisit($stepId);
					break;
			}
		}
		if (!$step || empty($step->resourceGroupId)) {
			//несистемный ресурс
			$locationId = $this->getSyncObject('MedStaffFact_Location', $MedStaffFact_id);
		} else {
			//системный ресурс
			$locationId = $step->resourceGroupId;
			$tmp_msf_id = $this->getSyncObject("MedStaffFact_Location_Evn_$Evn_id", $locationId, 'Object_sid');
			//Если другое рабочее место, редактируем системный ресурс
			if ($tmp_msf_id != $MedStaffFact_id) {
				$MedStaffFactInfo = $this->getMedStaffFactInfo(array('MedStaffFact_id' => $MedStaffFact_id));
				if (empty($MedStaffFactInfo['MedStaffFact_id'])) {
					throw new Exception('Не удалось получить данные о рабочем месте врача', 400);
				}
				$departmentId = $this->getSyncObject('LpuSection', $MedStaffFactInfo['LpuSection_id']);
				if (empty($departmentId)) {
					throw new Exception('Не найдено отделение для поиска/создания специалиста, ведущего прием', 400);
				}
				$employeePositionId = $this->getSyncObject('MedStaffFact', $MedStaffFact_id);
				if (empty($departmentId)) {
					throw new Exception('Не найдено рабочее место врача для поиска/создания специалиста, ведущего прием', 400);
				}
				$clinicId = $this->getSyncSpr('Lpu', $MedStaffFactInfo['Lpu_id']);
				$this->editLocation($locationId, $clinicId, $departmentId, $employeePositionId, $MedStaffFactInfo, true);
				$this->saveSyncObject("MedStaffFact_Location_Evn_$Evn_id", $MedStaffFact_id, $locationId);
			}
		}
		$this->textlog->add("syncLocationForEvn return $locationId");
		return $locationId;
	}

	/**
	 * Получение данных отделения
	 */
	function getLpuSectionInfo($data)
	{
		$params = array('LpuSection_id' => $data['LpuSection_id']);

		$query = "
			select
				LpuSection_id as \"LpuSection_id\",
				Lpu_id as \"Lpu_id\",
				LpuSection_Code as \"LpuSection_Code\",
				LpuSection_Name as \"LpuSection_Name\",
				to_char(LpuSection_setDate, 'yyyy-mm-dd') as \"LpuSection_setDate\",
				to_char(LpuSection_disDate, 'yyyy-mm-dd') as \"LpuSection_disDate\"
			from v_LpuSection_all
			where LpuSection_id = :LpuSection_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные отделения', 400);
		}
	}

	/**
	 * Получение списка идентификаторов отделений в МО из РМИС
	 */
	function getDepartments($clinicId)
	{
		$result = $this->exec('departments', 'getDepartments', array('clinic' => $clinicId));

		if (!isset($result->department)) {
			return array();
		}

		$departmentIds = array();
		if (is_array($result->department)) {
			foreach ($result->department as $departmentId) {
				$departmentIds[] = $departmentId;
			}
		} else {
			$departmentIds[] = $result->department;
		}

		return $departmentIds;
	}

	/**
	 * Идентификация человека. Возращается идентификатор из РМИС
	 */
	function identIndividual($data)
	{
		$params = array();

		if (!empty($data['Person_id'])) {
			$person = $this->getPersonInfo($data);
			if (empty($person) || empty($person['Person_id'])) {
				return $this->createError('Ошибка получения данных человека из БД');
			}
			$data = array_merge($data, $person);
		}

		$params['surname'] = $data['Person_SurName'];
		$params['name'] = $data['Person_FirName'];
		$params['birthDate'] = $data['Person_BirthDay'];

		if (!empty($data['Person_SecName'])) {
			$params['patrName'] = $data['Person_SecName'];
		}
		if (!empty($data['Sex_id'])) {
			$params['gender'] = $data['Sex_id'];
		}
		if (!empty($data['Person_Snils'])) {
			$params['searchCode'] = array(
				'codeTypeId' => 1,
				'codeValue' => $data['Person_Snils']
			);
		}
		if (!empty($data['DocumentType_id']) && !empty($data['Document_Num'])) {
			$typeId = $this->getSyncSpr('DocumentType', $data['DocumentType_id'], '', false, true);
			if (empty($typeId)) {
				$typeId = $this->getFirstResultFromQuery("
					select RMISDocumentType_id as \"RMISDocumentType_id\"
					from r66.v_RMISDocumentType
					where RMISDocumentType_Code = 'EDU_DOCS'
					limit 1
				");
			}
			if (!empty($typeId)) {
				$params['searchDocumentDoc'] = array(
					'docTypeId' => $typeId,
					'docSeries' => !empty($data['Document_Ser']) ? $data['Document_Ser'] : null,
					'docNumber' => $data['Document_Num']
				);
			}
		}
		if (!empty($data['PolisType_id']) && !empty($data['Polis_Num'])) {
			$typeId = $this->getSyncSpr('DocumentType', $data['PolisType_id']);
			$params['searchDocumentPolis'] = array(
				'docTypeId' => $typeId,
				'docSeries' => !empty($data['Polis_Ser']) ? $data['Polis_Ser'] : null,
				'docNumber' => $data['Polis_Num']
			);
		}

		$combinations = array(
			array('surname', 'name', 'patrName', 'birthDate', 'gender', 'searchCode', 'searchDocumentDoc'),
			array('surname', 'name', 'patrName', 'birthDate', 'searchDocumentPolis'),
			array('surname', 'name', 'patrName', 'birthDate', 'searchDocumentDoc'),
			array('surname', 'name', 'patrName', 'birthDate', 'searchCode'),
			array('name', 'patrName', 'birthDate', 'gender', 'searchCode', 'searchDocumentDoc'),
			array('name', 'patrName', 'birthDate', 'searchDocumentPolis'),
			array('name', 'patrName', 'birthDate', 'searchDocumentDoc'),
			array('name', 'patrName', 'birthDate', 'searchCode'),
			array('surname', 'name', 'patrName', 'birthDate'),
		);

		$str_comb_arr = array();
		$is_multi_record = false;
		foreach ($combinations as $combination) {
			$f_params = array();
			$comb = array();
			foreach ($combination as $key) {
				if (!empty($params[$key])) {
					$comb[] = $key;
					if (in_array($key, array('searchDocumentDoc', 'searchDocumentPolis'))) {
						$f_params['searchDocument'] = $params[$key];
					} else {
						$f_params[$key] = $params[$key];
					}
				}
			}

			$str_comb = implode('-', $comb);
			if (!empty($str_comb) && !in_array($str_comb, $str_comb_arr)) {
				$str_comb_arr[] = $str_comb;
				$result = $this->exec('individuals', 'searchIndividual', $f_params);

				$individuals = array();
				if (isset($result->individual)) {
					if (is_array($result->individual)) {
						foreach ($result->individual as $individualId) {
							$individuals[] = $individualId;
						}
					} else {
						$individuals[] = $result->individual;
					}
				}

				if (!empty($data['isMedPersonal']) && count($individuals) > 1) {
					$tmp = $individuals;
					$individuals = array();
					foreach ($tmp as $individualId) {
						$employeeIds = $this->getEmployeesByIndividual($individualId);
						if (!is_array($employeeIds)) {
							throw new Exception('Ошибка при определении врача по физ. лицу', 500);
						}
						if (count($employeeIds) > 0 && $data['isMedPersonal'] || count($employeeIds) == 0 && !$data['isMedPersonal']) {
							$individuals[] = $individualId;
						}
					}
					if (count($individuals) == 0) {
						$individuals = $tmp;
					}
				}

				if (count($individuals) > 1) {
					$is_multi_record = true;
				}
				if (count($individuals) == 1) {
					return $individuals[0];
				}
			}
		}
		if ($is_multi_record) {
			$snils = !empty($data['Person_Snils']) ? $data['Person_Snils'] : 'отсутствует';
			$polis = 'отсутствует';
			if (!empty($data['Polis_Num'])) {
				$polis = (!empty($data['Polis_Ser']) ? $data['Polis_Ser'] : '') . ' ' . $data['Polis_Num'];
			}
			$birthday = $data['Person_BirthDay'];
			$fio = $data['Person_SurName'] . ' ' . $data['Person_FirName'] . (!empty($data['Person_SecName']) ? ' ' . $data['Person_SecName'] : '');
			throw new Exception("{$fio} др - {$birthday} СНИЛС - {$snils} Полис - {$polis} не удалось однозначно определить физ. лицо");
		}
		return null;
	}

	/**
	 * Поиск физ.лиц
	 */
	function searchIndividual($data)
	{
		$params = array();

		if (!empty($data['Person_id'])) {
			$person = $this->getPersonInfo($data);
			if (empty($person) || empty($person['Person_id'])) {
				return $this->createError('Ошибка получения данных человека из БД');
			}
			$data = array_merge($data, $person);
		}

		if (!empty($data['Person_SurName'])) {
			$params['surname'] = $data['Person_SurName'];
		}
		if (!empty($data['Person_FirName'])) {
			$params['name'] = $data['Person_FirName'];
		}
		if (!empty($data['Person_SecName'])) {
			$params['patrName'] = $data['Person_SecName'];
		}
		if (!empty($data['Person_BirthDay'])) {
			$params['birthDate'] = $data['Person_BirthDay'];
		}
		if (!empty($data['Sex_id'])) {
			$params['gender'] = $data['Sex_id'];
		}
		if (!empty($data['Person_deadDT'])) {
			$params['deathDate'] = $data['Person_deadDT'];
		}
		if (!empty($data['DocumentType_id']) && !empty($data['Document_Num'])) {
			$typeId = $this->getSyncSpr('DocumentType', $data['DocumentType_id'], '', false, true);
			if (empty($typeId)) {
				$typeId = $this->getFirstResultFromQuery("
					select RMISDocumentType_id as \"RMISDocumentType_id\"
					from r66.v_RMISDocumentType 
					where RMISDocumentType_Code = 'EDU_DOCS'
					limit 1
				");
			}
			if (!empty($typeId)) {
				$params['searchDocument'] = array(
					'docTypeId' => $typeId,
					'docSeries' => $data['Document_Ser'],
					'docNumber' => $data['Document_Num']
				);
			}
		}

		$result = $this->exec('individuals', 'searchIndividual', $params);

		if (!isset($result->individual)) {
			return array();
		}

		$individuals = array();
		if (is_array($result->individual)) {
			foreach ($result->individual as $individualId) {
				$individuals[] = $individualId;
			}
		} else {
			$individuals[] = $result->individual;
		}

		//Фильтрация записей о физ.лицах, являющихся врачом
		if (!empty($data['isMedPersonal'])) {
			$tmp = $individuals;
			$individuals = array();
			foreach ($tmp as $individualId) {
				$employeeIds = $this->getEmployeesByIndividual($individualId);
				if (!is_array($employeeIds)) {
					throw new Exception('Ошибка при определении врача по физ. лицу', 500);
				}
				if (count($employeeIds) > 0 && $data['isMedPersonal'] || count($employeeIds) == 0 && !$data['isMedPersonal']) {
					$individuals[] = $individualId;
				}
			}
		}

		return $individuals;
	}

	/**
	 * Создание отделения в РМИС
	 */
	function createDepartment($clinicId, $data)
	{
		$params = array(
			'department' => array(
				'clinic' => $clinicId,
				'name' => $data['LpuSection_Name'],
				'code' => $data['LpuSection_Code'],
				//'parent' => $lpu_section['LpuSection_Code'],	//todo: search parent
				'availableDiagnosis' => true,
				'fromDate' => $data['LpuSection_setDate'],
				'toDate' => $data['LpuSection_disDate'],
				'departmentType' => '1',
			)
		);

		$department = $this->exec('departments', 'createDepartment', $params);

		return $department->department;
	}

	/**
	 * Синхронизация отделения
	 */
	function syncDepartment($Lpu_id, $LpuSection_id)
	{
		$departmentId = $this->getSyncObject('LpuSection', $LpuSection_id);
		if (empty($departmentId)) {
			$clinicId = $this->getSyncSpr('Lpu', $Lpu_id);
			$LpuSectionInfo = $this->getLpuSectionInfo(array(
				'LpuSection_id' => $LpuSection_id
			));

			$departments = $this->getDepartments($clinicId);
			if (!is_array($departments)) {
				throw new Exception('Ошибка запроса отделений для синхронизации', 500);
			}
			if (count($departments) > 0) {
				foreach ($departments as $key => $value) {
					$flag = true;
					$department = $this->getDepartment($value);
					//Синхронизация только по коду
					if (!empty($LpuSectionInfo['LpuSection_Code']) && $LpuSectionInfo['LpuSection_Code'] != $department['code']) {
						$flag = false;
					}
					/*if (!empty($LpuSectionInfo['LpuSection_Name']) && $LpuSectionInfo['LpuSection_Name'] != $department['name']) {
						$flag = false;
					}*/

					if ($flag) {
						// если нашли, дальше не продолжаем
						$departmentId = $value;
						break;
					}
				}
			}

			if (empty($departmentId)) {
				$departmentId = $this->createDepartment($clinicId, array(
					'LpuSection_id' => $LpuSection_id,
					'LpuSection_Code' => $LpuSectionInfo['LpuSection_Code'],
					'LpuSection_Name' => $LpuSectionInfo['LpuSection_Name'],
					'LpuSection_setDate' => $LpuSectionInfo['LpuSection_setDate'],
					'LpuSection_disDate' => $LpuSectionInfo['LpuSection_disDate']
				));
			}

			$this->saveSyncObject('LpuSection', $LpuSection_id, $departmentId);
		}

		return $departmentId;
	}

	/**
	 * Синхронизация пациента
	 */
	function syncPatient($Person_id)
	{
		$patientId = $this->getSyncObject('Person_Patient', $Person_id);

		if (empty($patientId)) {
			$personInfo = $this->getPersonInfo(array('Person_id' => $Person_id));
			// синхронизируем человека
			$individualId = $this->syncIndividual($personInfo);
			if (empty($individualId)) {
				throw new Exception('Не удалось синхронизировать физ. лицо', 400);
			}

			// не нашёл другого способа проверять создан ли уже пациент
			try {
				$socialGroupId = $this->getSyncSpr('SocStatus', $personInfo['SocStatus_id']);

				$patientId = $this->createPatient(array(
					'patientId' => $individualId,
					'socialGroup' => $socialGroupId
				));
			} catch (Exception $e) {
				if (mb_strpos($e->getMessage(), 'already exists') !== false) {
					$patientId = $individualId;
				}
			}

			$this->saveSyncObject('Person_Patient', $Person_id, $patientId);
		}

		return $patientId;
	}

	/**
	 * Создание
	 */
	function createAddress($params)
	{
		$address = $this->exec('addresses', 'createAddress', $params);

		return $address->id;
	}

	/**
	 * Синхронизация адреса
	 */
	function syncAddress($addressInfo)
	{
		$addressId = $this->getSyncObject('Address', $addressInfo['Address_id']);
		if (!empty($addressId)) {
			return $addressId;    //todo: обновление информации в РМИС если Address в промед изменился
		}

		$levels_map = array(
			'KLCountry' => array('num' => 1, 'socr_id' => null, 'socr_name' => null, 'el_id' => 'KLCountry_id', 'el_name' => 'KLCountry_Name'),
			'KLRgn' => array('num' => 2, 'socr_id' => 'KLRgnSocr_id', 'socr_name' => 'KLRgnSocr_Nick', 'el_id' => 'KLRgn_id', 'el_name' => 'KLRgn_Name'),
			'KLSubRgn' => array('num' => 3, 'socr_id' => 'KLSubRgnSocr_id', 'socr_name' => 'KLSubRgnSocr_Nick', 'el_id' => 'KLSubRgn_id', 'el_name' => 'KLSubRgn_Name'),
			'KLCity' => array('num' => 4, 'socr_id' => 'KLCitySocr_id', 'socr_name' => 'KLCitySocr_Nick', 'el_id' => 'KLCity_id', 'el_name' => 'KLCity_Name'),
			'KLTown' => array('num' => 5, 'socr_id' => 'KLTownSocr_id', 'socr_name' => 'KLTownSocr_Nick', 'el_id' => 'KLTown_id', 'el_name' => 'KLTown_Name'),
			'KLStreet' => array('num' => 6, 'socr_id' => 'KLStreetSocr_id', 'socr_name' => 'KLStreetSocr_Nick', 'el_id' => 'KLStreet_id', 'el_name' => 'KLStreet_Name'),
			'House' => array('num' => 7, 'socr_id' => 'KLHouseSocr_id', 'socr_name' => 'KLHouseSocr_Nick', 'el_id' => null, 'el_name' => 'Address_House'),
			'Flat' => array('num' => 8, 'socr_id' => null, 'socr_name' => null, 'el_id' => null, 'el_name' => 'Address_Flat'),
		);

		$levels = array();
		foreach ($levels_map as $object => $fields) {
			if (empty($addressInfo[$fields['el_name']])) {
				$levels[$object] = null;
			} else {
				foreach ($fields as $nick => $name) {
					if (in_array($nick, array('num'))) {
						$levels[$object][$nick] = $name;
					} else if ($nick == 'el_id' && empty($name)) {
						$levels[$object][$nick] = $addressInfo['Address_id'];
					} else {
						$levels[$object][$nick] = !empty($name) ? $addressInfo[$name] : null;
					}
				}
			}
		}

		$last_sid = null;
		foreach ($levels as $object => $level) {
			if (!$level) {
				continue;
			}
			$id = $level['el_id'];
			$sid = $this->getSyncObject($object, $id);
			if (empty($sid) || in_array($object, array('House', 'Flat'))) {
				if ($object == 'Flat') {
					$type = $this->getSyncSpr('KLSocr', 'Квартира', 'RMISKLSocr_Name');
				} else {
					$type = $this->getSyncSpr('KLSocr', $level['socr_id'], 'KLSocr_id', true);
				}

				$params = array(
					'name' => $level['el_name'],
					'type' => $type,
					'page' => 1
				);
				if ($last_sid) {
					$params['parent'] = $last_sid;
				}

				$result = $this->exec('addresses', 'getAddresses', $params);
				if (isset($result->getAddressesResponse) && is_object($result->getAddressesResponse)) {
					if (is_array($result->getAddressesResponse)) {
						$addressId = $result->getAddressesResponse[0]->id;
					} else {
						$addressId = $result->getAddressesResponse->id;
					}
				} else {
					//todo: Метод createAddress не работает. Если починят, то можно вернуть создание адреса
					break;
					/*$addressId = $this->createAddress(array(
						'name' => $level['el_name'],
						'type' => $type,
						'parent' => $last_sid,
						'level' => $level['num']
					));*/
				}
				if (empty($sid) || $addressId != $sid) {
					$sid = $addressId;
					$this->saveSyncObject($object, $id, $sid);
				}
			}
			$last_sid = $sid;
		}
		if (!empty($last_sid)) {
			$this->saveSyncObject('Address', $addressInfo['Address_id'], $last_sid);
		}
		return $last_sid;
	}

	/**
	 * Получение списка адресов физ. лица
	 */
	function getIndividualAddresses($individualId)
	{
		$result = $this->exec('individuals', 'getIndividualAddresses', $individualId);

		if (!isset($result->individualAddress)) {
			return array();
		}

		$individualAddressList = array();
		$individualAddressIds = array();
		if (is_array($result->individualAddress)) {
			foreach ($result->individualAddress as $individualAddressId) {
				$individualAddressIds[] = $individualAddressId;
			}
		} else {
			$individualAddressIds[] = $result->individualAddress;
		}
		foreach ($individualAddressIds as $individualAddressId) {
			$address = $this->exec('individuals', 'getIndividualAddress', $individualAddressId);
			if (isset($address->addressId)) {
				$address->individualAddressId = $individualAddressId;

				$addressTypes = $this->exec('individuals', 'getIndividualAddressTypes', $individualAddressId);
				if (!is_object($addressTypes) || !isset($addressTypes->individualAddressType)) {
					$address->registerType = array();
				} else if (!is_array($addressTypes->individualAddressType)) {
					$address->registerType = array($addressTypes->individualAddressType);
				} else {
					$address->registerType = $addressTypes->individualAddressType;
				}

				$individualAddressList[] = $address;
			}
		}

		return $individualAddressList;
	}

	/**
	 * Синхронизация адреса физ. лица
	 */
	function syncIndividualAddress($personInfo, $individualId = null)
	{
		if (empty($individualId)) {
			$individualId = $this->getSyncObject('Person', $personInfo['Person_id']);
		}
		if (empty($individualId)) {
			throw new Exception('Синхронизация адреса: отсутвует идентификатор физ. лица', 500);
		}

		$individualAddressList = $this->getIndividualAddresses($individualId);
		$individualAddressByAddressId = array();
		foreach ($individualAddressList as $individualAddress) {
			$key = $individualAddress->addressId;
			$individualAddressByAddressId[$key] = $individualAddress;
		}

		$addressTypeConfigs = array();
		if (!empty($personInfo['UAddress_id'])) {
			$addressTypeConfigs['4'] = $this->getAddressInfo($personInfo['UAddress_id']);
		}
		if (!empty($personInfo['PAddress_id'])) {
			$addressTypeConfigs['3'] = $this->getAddressInfo($personInfo['PAddress_id']);
		}
		if (!empty($personInfo['BAddress_id'])) {
			$addressTypeConfigs['5'] = $this->getAddressInfo($personInfo['BAddress_id']);
		}

		foreach ($addressTypeConfigs as $addressTypeId => $addressInfo) {
			$addressId = $this->syncAddress($addressInfo);

			if (isset($individualAddressByAddressId[$addressId])) {
				if (!in_array($addressTypeId, $individualAddressByAddressId[$addressId]->registerType)) {
					$this->addTypeToIndividualAddress($individualAddressByAddressId[$addressId]->individualAddressId, $addressTypeId);
					$individualAddressByAddressId[$addressId]->registerType[] = $addressTypeId;
				}
			} else {
				$individualAddressId = $this->createIndividualAddress(array(
					'individual' => $individualId,
					'addressId' => $addressId,
					//'fromDate' => $minDate,
					//'valid' => true
				));
				$this->addTypeToIndividualAddress($individualAddressId, $addressTypeId);

				$individualAddressByAddressId[$addressId] = (object)array(
					'individualAddressId' => $individualAddressId,
					'addressId' => $addressId,
					'individual' => $individualId,
					'registerType' => array($addressTypeId)
				);
			}
		}

		return true;
	}

	/**
	 * Синхронизация физ.лица
	 */
	function syncIndividual($personInfo)
	{
		$individualId = $this->getSyncObject('Person', $personInfo['Person_id']);

		if (empty($individualId)) {
			$individualId = $this->identIndividual($personInfo);
			/*$individuals = $this->searchIndividual($personInfo);
			if (!array($individuals)) {
				throw new Exception('Ошибка при поиске человека', 500);
			}
			if (count($individuals) > 1) {
				throw new Exception('На сервисе найдено более одного человека', 400);
			}
			if (count($individuals) == 1) {
				$individualId = $individuals[0];
			}*/

			if (empty($individualId)) {
				$individualId = $this->createIndividual($personInfo);
			}

			$this->syncIndividualAddress($personInfo, $individualId);

			$this->saveSyncObject('Person', $personInfo['Person_id'], $individualId);
		}

		return $individualId;
	}

	/**
	 * Создание сотрудника мед. организации
	 */
	function syncEmployee($Lpu_id, $MedPersonal_id)
	{
		$employeeId = $this->getSyncObject('MedPersonalOnLpu_' . $Lpu_id, $MedPersonal_id);

		if (empty($employeeId)) {
			$clinicId = $this->getSyncSpr('Lpu', $Lpu_id);
			$MedPersonalInfo = $this->getMedPersonalInfo(array(
				'MedPersonal_id' => $MedPersonal_id,
				'Lpu_id' => $Lpu_id
			));

			// синхронизируем человека
			$PersonInfo = $this->getPersonInfo($MedPersonalInfo);
			$PersonInfo['isMedPersonal'] = true;
			$individualId = $this->syncIndividual($PersonInfo);
			if (empty($individualId)) {
				throw new Exception('Не удалось синхронизировать физ. лицо', 400);
			}

			// получение списка врачей по организации
			$employeeIdsByOrganization = $this->getEmployees($clinicId);
			if (!is_array($employeeIdsByOrganization)) {
				throw new Exception('Ошибка при получении списка сотрудников МО из РМИС', 500);
			}

			// поиск сотрудника по физ. лицу
			$employeeIds = $this->getEmployeesByIndividual($individualId);
			if (!is_array($employeeIds)) {
				throw new Exception('Ошибка при получении данных о сотруднике по физ. лицу', 500);
			}
			foreach ($employeeIds as $checkEmployeeId) {
				if (in_array($checkEmployeeId, $employeeIdsByOrganization)) {
					$employee = $this->getEmployee($checkEmployeeId);
					if (is_array($employee) && $employee['number'] == $MedPersonalInfo['MedPersonal_TabCode']) {
						$employeeId = $checkEmployeeId;
					}
				}
			}

			if (empty($employeeId)) {
				$employeeId = $this->createEmployee($individualId, $MedPersonalInfo, $clinicId);
			}

			$this->saveSyncObject('MedPersonalOnLpu_' . $Lpu_id, $MedPersonal_id, $employeeId);
		}

		return $employeeId;
	}

	/**
	 * Получение должностей врача
	 */
	function getEmployeePositions($employeeId)
	{
		$params = array(
			'employee' => $employeeId
		);

		$result = $this->exec('employees', 'getEmployeePositions', $params);

		if (!isset($result->employeePosition)) {
			return array();
		}

		$employeePositions = array();
		if (is_array($result->employeePosition)) {
			$employeePositions = $result->employeePosition;
		} else {
			$employeePositions[] = $result->employeePosition;
		}

		return $employeePositions;
	}

	/**
	 * Синхронизация сотрудника мед. организации
	 */
	function syncEmployeePosition($Lpu_id, $Staff_id, $MedPersonal_id, $MedStaffFact_id)
	{
		$employeePositionId = $this->getSyncObject('MedStaffFact', $MedStaffFact_id);

		if (empty($employeePositionId)) {
			$employeeId = $this->getSyncObject('MedPersonalOnLpu_' . $Lpu_id, $MedPersonal_id);
			if (empty($employeeId)) {
				throw new Exception('Не найден врач для поиска/создания должностей сотрудника', 400);
			}

			$positionId = $this->getSyncObject('Staff', $Staff_id);
			if (empty($positionId)) {
				throw new Exception('Не найдена должность для поиска/создания должностей сотрудника', 400);
			}

			$MedStaffFactInfo = $this->getMedStaffFactInfo(array('MedStaffFact_id' => $MedStaffFact_id));
			if (empty($MedStaffFactInfo['MedStaffFact_id'])) {
				throw new Exception('Не удалось получить данные о рабочем месте врача', 400);
			}

			// поиск должности
			$employeePositions = $this->getEmployeePositions($employeeId);
			if (count($employeePositions) > 0) {
				foreach ($employeePositions as $key => $value) {
					$flag = true;
					$employeePosition = $this->getEmployeePosition($value);
					if (!empty($employeeId) && $employeeId != $employeePosition['employee']) {
						$flag = false;
					}
					if (!empty($positionId) && $positionId != $employeePosition['position']) {
						$flag = false;
					}

					if ($flag) {
						// если нашли, дальше не продолжаем
						$employeePositionId = $value;
						break;
					}
				}
			}

			if (empty($employeePositionId)) {
				$employeePositionId = $this->createEmployeePosition($positionId, $employeeId, $MedStaffFactInfo);
			}

			$this->saveSyncObject('MedStaffFact', $MedStaffFact_id, $employeePositionId);
		}

		return $employeePositionId;
	}

	/**
	 * Синхронизация всех данных сотрудника мед. организации
	 */
	function syncEmployeePositionFull($MedStaffFact_id)
	{
		$query = "
			select
				MSF.Lpu_id as \"Lpu_id\",
				MSF.LpuSection_id as \"LpuSection_id\",
				MSF.Staff_id as \"Staff_id\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_MedStaffFactCache MSF
			where
				MSF.MedStaffFact_id = :MedStaffFact_id
		";
		$item = $this->getFirstRowFromQuery($query, array('MedStaffFact_id' => $MedStaffFact_id));
		if ($item === false) {
			throw new Exception('Не удалось получить данные сотрудника мед. организации', 400);
		}

		$response = array(
			'departmentId' => null,
			'positionId' => null,
			'employeeId' => null,
			'employeePositionId' => null,
			'locationId' => null,
			'syncEmployeeSpecialitiesResult' => null
		);

		//синхронизируем отделение
		if (!empty($item['LpuSection_id'])) {
			$response['departmentId'] = $this->syncDepartment($item['Lpu_id'], $item['LpuSection_id']);
		}

		//синхронизируем должность
		if (!empty($item['LpuSection_id']) && !empty($item['Staff_id'])) {
			$response['positionId'] = $this->syncPosition($item['Lpu_id'], $item['LpuSection_id'], $item['Staff_id']);
		}

		//синхронизируем сотрудника
		if (!empty($item['MedPersonal_id'])) {
			$response['employeeId'] = $this->syncEmployee($item['Lpu_id'], $item['MedPersonal_id']);
		}

		//синхронизируем должность сотрудника
		if (!empty($item['Staff_id']) && !empty($item['MedPersonal_id']) && !empty($item['MedStaffFact_id'])) {
			$response['employeePositionId'] = $this->syncEmployeePosition($item['Lpu_id'], $item['Staff_id'], $item['MedPersonal_id'], $item['MedStaffFact_id']);
		}

		//синхронизируем образование сотрудника createEmployeeEducation TODO пока решили не делать, хотя кое что уже сделано
		/*if (!empty($item['MedPersonal_id'])) {
			$response['syncEmployeeEducationsResult'] = $this->syncEmployeeEducations($item['MedPersonal_id']);
		}*/

		//синхронизируем сертификаты сотрудника createEmployeeCertificate TODO пока решили не делать

		//синхронизируем специалиста, ведущего прием
		if (!empty($item['LpuSection_id']) && !empty($item['MedStaffFact_id'])) {
			$response['locationId'] = $this->syncLocation($item['Lpu_id'], $item['LpuSection_id'], $item['MedStaffFact_id']);
		}

		//синхронизируем специальности сотрудника addSpecialityToEmployee
		if (!empty($item['MedPersonal_id'])) {
			$response['syncEmployeeSpecialitiesResult'] = $this->syncEmployeeSpecialities($item['Lpu_id'], $item['MedPersonal_id']);
		}

		return $response;
	}

	/**
	 * Синхронизация специалиста, ведущего прием
	 */
	function syncLocation($Lpu_id, $LpuSection_id, $MedStaffFact_id)
	{
		$locationId = $this->getSyncObject('MedStaffFact_Location', $MedStaffFact_id);

		if (empty($locationId)) {
			$MedStaffFactInfo = $this->getMedStaffFactInfo(array('MedStaffFact_id' => $MedStaffFact_id));
			if (empty($MedStaffFactInfo['MedStaffFact_id'])) {
				throw new Exception('Не удалось получить данные о рабочем месте врача', 400);
			}

			$departmentId = $this->getSyncObject('LpuSection', $LpuSection_id);
			if (empty($departmentId)) {
				throw new Exception('Не найдено отделение для поиска/создания специалиста, ведущего прием', 400);
			}

			$employeePositionId = $this->getSyncObject('MedStaffFact', $MedStaffFact_id);
			if (empty($departmentId)) {
				throw new Exception('Не найдено рабочее место врача для поиска/создания специалиста, ведущего прием', 400);
			}

			$clinicId = $this->getSyncSpr('Lpu', $Lpu_id);

			if ($MedStaffFactInfo['LpuSectionProfile_Code'] == '0000') {
				throw new Exception('Для приемного отделения должен быть дополнительный профиль', 400);
			}
			$profileId = $this->getSyncSpr('LpuSectionProfile', $MedStaffFactInfo['LpuSectionProfile_id']);


			// поиск специалиста, ведущего прием
			$locations = $this->getLocations($clinicId);
			if (!is_array($locations)) {
				throw new Exception('Ошибка при получении данных о специалисте, ведущем прием', 500);
			}
			if (count($locations) > 0) {
				foreach ($locations as $key => $value) {
					$flag = true;
					try {
						$location = $this->getLocation($value);
					} catch (Exception $e) {
						//Игнорирование некоторых ошибок, чтобы продолжить поиск ресурса
						if (!in_array($e->getMessage(), array(
							'java.lang.NullPointerException',
							'java.lang.RuntimeException: У ресурса не найдена должность'
						))
						) {
							throw $e;
						}
					}

					if (!empty($employeePositionId) && $employeePositionId != $location['employeePosition']) {
						$flag = false;
					}
					if (!empty($departmentId) && $departmentId != $location['department']) {
						$flag = false;
					}
					if (!empty($profileId) && $profileId != $location['specialization']) {
						$flag = false;
					}

					if ($flag) {
						// если нашли, дальше не продолжаем
						$locationId = $value;
						break;
					}
				}
			}

			if (empty($locationId)) {
				$locationId = $this->createLocation($clinicId, $departmentId, $employeePositionId, $MedStaffFactInfo);
			}

			$this->saveSyncObject('MedStaffFact_Location', $MedStaffFact_id, $locationId);
		}

		return $locationId;
	}

	/**
	 * Синхронизация образования сотрудника мед. организации
	 */
	function syncEmployeeEducation($MedPersonal_id, $EmployeeEducationInfo)
	{
		// TODO пока решили не передавать
	}

	/**
	 * Синхронизация образований сотрудника мед. организации
	 */
	function syncEmployeeEducations($MedPersonal_id)
	{
		// получаем все данные по обучению мед.сотрудников для синхроинзации
		// persis.SpecialityDiploma, persis.PostgraduateEducation, persis.QualificationImprovementCourse, persis.RetrainingCourse
		$query = "
			select
				id as \"id\",
				YearOfGraduation as \"YearOfGraduation\",
				DiplomaSpeciality_id as \"DiplomaSpeciality_id\",
				EducationType_id as \"EducationType_id\"
			from
				persis.SpecialityDiploma
			where
				MedWorker_id = :MedWorker_id
		";

		$resp = $this->queryResult($query, array(
			'MedWorker_id' => $MedPersonal_id
		));

		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных об образовании', 500);
		}

		foreach ($resp as $respone) {
			$specialityId = $this->getSyncSpr('DiplomaSpeciality', $respone['DiplomaSpeciality_id']);
			$educationTypeId = $this->getSyncSpr('EducationType', $respone['EducationType_id'], 'FRMPEducationType_id');

			$params = array(
				'Object_id' => $respone['id'],
				'Object_Name' => 'SpecialityDiploma',
				'fromDate' => null,
				'toDate' => null,
				'hours' => null,
				'speciality' => $specialityId,
				'educationLevel' => null,
				'educationType' => $educationTypeId,
				'educationalOrganization' => null,
				'seriesName' => null
			);
			if (!empty($respone['YearOfGraduation'])) {
				$respone['toDate'] = $respone['YearOfGraduation'] . '-01-01';
			}
			$this->syncEmployeeEducation($MedPersonal_id, $params);
		}

		return true;
	}

	/**
	 * Получение специальностей врача из РМИС
	 */
	function getEmployeeSpecialities($employeeId)
	{
		$params = array('employee' => $employeeId);

		$result = $this->exec('employees', 'getEmployeeSpecialities', $params);

		if (!isset($result->speciality)) {
			return array();
		}

		$specialities = array();
		if (is_array($result->speciality)) {
			$specialities = $result->speciality;
		} else {
			$specialities[] = $result->speciality;
		}

		return $specialities;
	}

	/**
	 * Добавление специальности врачу
	 */
	function addSpecialityToEmployee($employeeId, $specialityId)
	{
		$params = array(
			'employee' => $employeeId,
			'speciality' => $specialityId,
		);

		$result = $this->exec('employees', 'addSpecialityToEmployee', $params);

		return true;
	}

	/**
	 * Синронизация специальностей врача
	 */
	function syncEmployeeSpecialities($Lpu_id, $MedPersonal_id)
	{
		//Получение специальностей по сертификатам
		$query = "
			select
				SS.id as \"FRMPSertificateSpeciality_id\"
			from
				persis.Certificate C 
				inner join persis.Speciality S  on S.id = C.Speciality_id
				inner join persis.FRMPSertificateSpeciality SS  on SS.id = S.frmpEntry_id
			where C.MedWorker_id = :MedWorker_id
		";
		$resp = $this->queryResult($query, array(
			'MedWorker_id' => $MedPersonal_id
		));
		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных специальностях врача', 500);
		}

		$employeeId = $this->getSyncObject('MedPersonalOnLpu_' . $Lpu_id, $MedPersonal_id);
		if (empty($employeeId)) {
			throw new Exception('Не найден врач для синхронизации специальностей', 400);
		}

		$synchronizedSpecialities = $this->getEmployeeSpecialities($employeeId);

		foreach ($resp as $item) {
			$specialityId = $this->getSyncSpr('SertificateSpeciality', $item['FRMPSertificateSpeciality_id'], 'FRMPSertificateSpeciality_id', true);

			if (!in_array($specialityId, $synchronizedSpecialities)) {
				$result = $this->addSpecialityToEmployee($employeeId, $specialityId);
			}
		}

		return true;
	}

	/**
	 * Создание документа
	 */
	function createDocument($individualId, $DocumentInfo)
	{
		$params = array(
			'individualUid' => $individualId,
			'type' => $DocumentInfo['type'],
			'issuerText' => $DocumentInfo['issuerText'],
			'series' => $DocumentInfo['series'],
			'number' => $DocumentInfo['number'],
			'issueDate' => $DocumentInfo['issueDate'],
			'expireDate' => $DocumentInfo['expireDate'],
			'active' => $DocumentInfo['active']
		);

		$documentId = $this->exec('individuals', 'createDocument', $params);

		return $documentId;
	}

	/**
	 * Получение документа
	 */
	function getDocument($documentId)
	{
		$document = array();
		$plainFields = array('individualUid', 'type', 'issuerText', 'series', 'number', 'issueDate', 'expireDate', 'active');

		$result = $this->exec('individuals', 'getDocument', $documentId);

		$document['documentId'] = $documentId;
		foreach ($plainFields as $field) {
			$document[$field] = null;
		}
		foreach ($result as $field => $value) {
			switch ($field) {
				default:
					$document[$field] = $value;
			}
		}
		return $document;
	}

	/**
	 * Синхронизация документа физ. лица
	 */
	function syncDocument($Person_id, $DocumentInfo)
	{
		$documentId = $this->getSyncObject($DocumentInfo['Object_Name'], $DocumentInfo['Object_id']);

		if (empty($documentId)) {
			$individualId = $this->getSyncObject('Person', $Person_id);
			if (empty($individualId)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания документов', 400);
			}

			// поиск документа
			$documents = $this->getIndividualDocuments($individualId);
			if (!is_array($documents)) {
				throw new Exception('Ошибка при поиске документов физ. лица', 500);
			}
			if (count($documents) > 0) {
				foreach ($documents as $key => $value) {
					$flag = true;
					$document = $this->getDocument($value);
					if (!empty($DocumentInfo['type']) && $DocumentInfo['type'] != $document['type']) {
						$flag = false;
					}
					/*if (!empty($DocumentInfo['issuerText']) && $DocumentInfo['issuerText'] != $document['issuerText']) {
						$flag = false;
					}*/
					if (!empty($DocumentInfo['series']) && $DocumentInfo['series'] != $document['series']) {
						$flag = false;
					}
					if (!empty($DocumentInfo['number']) && $DocumentInfo['number'] != $document['number']) {
						$flag = false;
					}
					/*if (!empty($DocumentInfo['issueDate']) && date('Y-m-d', strtotime($DocumentInfo['issueDate'].'+05:00')) != date('Y-m-d', strtotime($document['issueDate']))) {
						$flag = false;
					}
					if (!empty($DocumentInfo['expireDate']) && date('Y-m-d', strtotime($DocumentInfo['expireDate'].'+05:00')) != date('Y-m-d', strtotime($document['expireDate']))) {
						$flag = false;
					}*/
					/*if (!empty($individualId) && $individualId != $document['individualUid']) {
						$flag = false;
					}*/

					if ($flag) {
						// если нашли, дальше не продолжаем
						$documentId = $value;
						break;
					}
				}
			}

			if (empty($documentId)) {
				$documentId = $this->createDocument($individualId, $DocumentInfo);
			}

			$this->saveSyncObject($DocumentInfo['Object_Name'], $DocumentInfo['Object_id'], $documentId);
		}

		return $documentId;
	}

	/**
	 * Синхронизация документов физ. лица
	 */
	function syncDocuments($Person_id)
	{
		// получаем все данные по документам
		// Document, Polis
		$query = "
			select
				ps.Document_id as \"Document_id\",
				d.DocumentType_id as \"DocumentType_id\",
				od.OrgDep_Nick as \"OrgDep_Nick\",
				ps.Document_Num as \"Document_Num\",
				ps.Document_Ser as \"Document_Ser\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"Document_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"Document_endDate\",
				ps.Polis_id as \"Polis_id\",
				ps.PolisType_id as \"PolisType_id\",
				os.OrgSMO_Nick as \"OrgSMO_Nick\",
				case when p.PolisType_id = 4
					then ps.Person_edNum else ps.Polis_Num
				end as \"Polis_Num\",
				ps.Polis_Ser as \"Polis_Ser\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"Polis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"Polis_endDate\"
			from
				v_PersonState ps
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
			where
				ps.Person_id = :Person_id
		";

		$resp = $this->queryResult($query, array(
			'Person_id' => $Person_id
		));

		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных о документах', 500);
		}

		if (!empty($resp[0]['Document_id'])) {
			$typeId = $this->getSyncSpr('DocumentType', $resp[0]['DocumentType_id'], '', false, true);
			if (empty($typeId)) {
				$typeId = $this->getFirstResultFromQuery("
					select RMISDocumentType_id as \"RMISDocumentType_id\"
					from r66.v_RMISDocumentType
					where RMISDocumentType_Code = 'EDU_DOCS'
					limit 1
				");
			}

			$params = array(
				'Object_id' => $resp[0]['Document_id'],
				'Object_Name' => 'Document',
				'type' => $typeId,
				'issuer' => null,
				'issuerText' => $resp[0]['OrgDep_Nick'],
				'series' => $resp[0]['Document_Ser'],
				'number' => $resp[0]['Document_Num'],
				'issueDate' => $resp[0]['Document_begDate'],
				'expireDate' => $resp[0]['Document_endDate'],
				'active' => true
			);
			$this->syncDocument($Person_id, $params);
		}

		if (!empty($resp[0]['Polis_id'])) {
			$typeId = null;
			$typeId = $this->getSyncSpr('DocumentType', $resp[0]['PolisType_id'], 'PolisType_id');

			$params = array(
				'Object_id' => $resp[0]['Polis_id'],
				'Object_Name' => 'Polis',
				'type' => $typeId,
				'issuer' => null,
				'issuerText' => $resp[0]['OrgSMO_Nick'],
				'series' => $resp[0]['Polis_Ser'],
				'number' => $resp[0]['Polis_Num'],
				'issueDate' => $resp[0]['Polis_begDate'],
				'expireDate' => $resp[0]['Polis_endDate'],
				'active' => true
			);
			$this->syncDocument($Person_id, $params);
		}

		return true;
	}

	/**
	 * Получение документов
	 */
	function getIndividualDocuments($individualId)
	{
		$result = $this->exec('individuals', 'getIndividualDocuments', $individualId);

		if (!isset($result->document)) {
			return array();
		}

		$documents = array();
		if (is_array($result->document)) {
			$documents = $result->document;
		} else {
			$documents[] = $result->document;
		}

		return $documents;
	}

	/**
	 * Получение должностей
	 */
	function getPositions($departmentId)
	{
		$params = array(
			'departmentId' => $departmentId
		);

		$result = $this->exec('employees', 'getPositions', $params);

		if (!isset($result->positionId)) {
			return array();
		}

		$positions = array();
		if (is_array($result->positionId)) {
			$positions = $result->positionId;
		} else {
			$positions[] = $result->positionId;
		}

		return $positions;
	}

	/**
	 * Синхронизация должности
	 */
	function syncPosition($Lpu_id, $LpuSection_id, $Staff_id)
	{
		$positionId = $this->getSyncObject('Staff', $Staff_id);

		if (empty($positionId)) {
			$clinicId = $this->getSyncSpr('Lpu', $Lpu_id);
			$departmentId = $this->getSyncObject('LpuSection', $LpuSection_id);
			if (empty($departmentId)) {
				throw new Exception('Не найдено отделение для поиска/создания должностей', 400);
			}

			$StaffInfo = $this->getStaffInfo(array('Staff_id' => $Staff_id));
			$roleId = $this->getSyncSpr('Post', $StaffInfo['Post_id']);

			// поиск должности
			$positions = $this->getPositions($departmentId);
			if (!is_array($positions)) {
				throw new Exception('Ошибка запроса должностей', 500);
			}
			if (count($positions) > 0) {
				foreach ($positions as $key => $value) {
					$flag = true;
					$position = $this->getPosition($value);
					if (!empty($roleId) && $roleId != $position['role']) {
						$flag = false;
					}
					if (!empty($StaffInfo['Rate']) && $StaffInfo['Rate'] != $position['rate']) {
						$flag = false;
					}
					if (!empty($StaffInfo['Post_Code']) && $StaffInfo['Post_Code'] != $position['code']) {
						$flag = false;
					}
					if (!empty($StaffInfo['Post_Name']) && $StaffInfo['Post_Name'] != $position['name']) {
						$flag = false;
					}
					if (!empty($StaffInfo['BeginDate']) && $StaffInfo['BeginDate'] != date('Y-m-d', strtotime($position['fromDate']))) {
						$flag = false;
					}
					if (!empty($departmentId) && $departmentId != $position['department']) {
						$flag = false;
					}

					if ($flag) {
						// если нашли, дальше не продолжаем
						$positionId = $value;
						break;
					}
				}
			}

			if (empty($positionId)) {
				$positionId = $this->createPosition($clinicId, $departmentId, $StaffInfo);
			}

			$this->saveSyncObject('Staff', $Staff_id, $positionId);
		}

		return $positionId;
	}

	/**
	 * Получение данных по прикреплению
	 */
	function getPersonCardInfo($data)
	{
		$params = array('PersonCard_id' => $data['PersonCard_id']);

		$query = "
			select
				PC.Person_id as \"Person_id\",
				PC.PersonCard_id as \"PersonCard_id\",
				PC.Lpu_id as \"Lpu_id\",
				to_char(PC.PersonCard_begDate, 'yyyy-mm-dd') as \"PersonCard_begDate\",
				to_char(PC.PersonCard_endDate, 'yyyy-mm-dd') as \"PersonCard_endDate\",
				PC.CardCloseCause_id as \"CardCloseCause_id\"
			from
				v_PersonCard_all PC
			where
				PC.PersonCard_id = :PersonCard_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные по прикреплению', 400);
		}
	}

	/**
	 * Поиск прикреплений
	 */
	function searchPatientReg($data)
	{
		$result = $this->exec('patients', 'searchPatientReg', array(
			'patientEuid' => $data['individualId'],
			'regType' => $data['regTypeId'],
			'clinicCode' => $data['clinicId'],
			'regState' => $data['regState']
		));

		if (!isset($result->EUID)) {
			return array();
		}

		$patientRegs = array();
		if (is_array($result->EUID)) {
			$patientRegs = $result->EUID;
		} else {
			$patientRegs[] = $result->EUID;
		}

		return $patientRegs;
	}

	/**
	 * Поиск случаев лечения
	 */
	function searchCase($params)
	{
		$result = $this->exec('cases', 'searchCase', $params);

		if (!isset($result->ids)) {
			return array();
		}

		$cases = array();
		if (is_array($result->ids)) {
			$cases = $result->ids;
		} else {
			$cases[] = $result->ids;
		}

		return $cases;
	}

	/**
	 * Поиск пациентов
	 */
	function searchPatient($params)
	{
		$result = $this->exec('patients', 'searchPatient', $params);

		if (!isset($result->ids)) {
			return array();
		}

		$cases = array();
		if (is_array($result->ids)) {
			$cases = $result->ids;
		} else {
			$cases[] = $result->ids;
		}

		return $cases;
	}

	/**
	 * Получение прикреплений пациента
	 */
	function getPatientRegs($individualId)
	{
		$result = $this->exec('patients', 'getPatientRegs', $individualId);

		if (!isset($result->patientReg)) {
			return array();
		}

		$patientRegs = array();
		if (is_array($result->patientReg)) {
			$patientRegs = $result->patientReg;
		} else {
			$patientRegs[] = $result->patientReg;
		}

		return $patientRegs;
	}

	/**
	 * Создание прикрепления
	 */
	function createPatientReg($params)
	{
		$patientRegId = $this->exec('patient', 'createPatientReg', $params);

		return $patientRegId;
	}

	/**
	 * Создание вида услуги
	 */
	function createService($params)
	{
		$service = $this->exec('services', 'createService', array('service' => $params));

		return $service->service;
	}

	/**
	 * Отправка направления
	 */
	function sendReferral($params)
	{
		$referral = $this->exec('referrals', 'sendReferral', $params);

		return $referral->id;
	}

	/**
	 * Отправка случая лечения
	 */
	function sendCase($params)
	{
		$case = $this->exec('cases', 'sendCase', $params);

		return $case->id;
	}

	/**
	 * Отправка услуги
	 */
	function sendServiceRend($params)
	{
		$case = $this->exec('renderedServices', 'sendServiceRend', $params);

		return $case->id;
	}

	/**
	 * Отправка движения
	 */
	function sendHspRecord($params)
	{
		return $this->exec('hsp-records', 'sendHspRecord', $params);
	}

	/**
	 * Отправка посещения
	 */
	function sendVisit($params)
	{
		$case = $this->exec('visits', 'sendVisit', $params);

		return $case->id;
	}

	/**
	 * Синхронизация прикрепления
	 */
	function syncPatientReg($PersonCard_id)
	{
		$patientRegId = $this->getSyncObject('PersonCard', $PersonCard_id);
		if (empty($patientRegId)) {
			$PersonCardInfo = $this->getPersonCardInfo(array(
				'PersonCard_id' => $PersonCard_id
			));

			$individualId = $this->getSyncObject('Person', $PersonCardInfo['Person_id']);
			if (empty($individualId)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания прикреплений', 400);
			}

			$clinicId = $this->getSyncSpr('Lpu', $PersonCardInfo['Lpu_id']);

			$unregCauseId = null;
			if (!empty($PersonCardInfo['CardCloseCause_id'])) {
				$unregCauseId = $this->getSyncSpr('CardCloseCause', $PersonCardInfo['CardCloseCause_id'], null, true);
			}

			$regStateId = 1;
			if (!empty($PersonCardInfo['PersonCard_endDate'])) {
				$regStateId = 2;
			}

			if (empty($patientRegId)) {
				$patientRegId = $this->createPatientReg(array(
					'patientUid' => $individualId,
					'clinicCode' => $clinicId,
					'regType' => 1,
					'regState' => $regStateId,
					'requestUid' => $PersonCardInfo['PersonCard_id'],
					'requestDate' => $PersonCardInfo['PersonCard_begDate'],
					'unregDate' => $PersonCardInfo['PersonCard_endDate'],
					'regDate' => $PersonCardInfo['PersonCard_begDate'],
					'unregCause' => $unregCauseId
				));
			}

			$this->saveSyncObject('PersonCard', $PersonCard_id, $patientRegId);
		}

		return $patientRegId;
	}

	/**
	 * Получение данных для синхронизации медосмотров/диспансеризаций
	 */
	function getEvnPLDispInfo($data)
	{
		$params = array('EvnPLDisp_id' => $data['EvnPLDisp_id']);

		$query = "
			select
				EPLD.EvnPLDisp_id as \"EvnPLDisp_id\",
				EPLD.Lpu_id as \"Lpu_id\",
				EPLD.Person_id as \"Person_id\",
				PS.SocStatus_id as \"SocStatus_id\",
				DC.DispClass_Code as \"DispClass_Code\",
				EvnVizit.EvnVizitDisp_id as \"EvnVizitDisp_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EPLD.PayType_id as \"PayType_id\",
				EIT.EducationInstitutionType_SysNick as \"EducationInstitutionType_SysNick\",
				EPLDTT.AgeGroupDisp_id as \"AgeGroupDisp_id\",
				coalesce(AH.HealthKind_id, EPLDP.HealthKind_id, EPLDD13.HealthKind_id) as \"HealthKind_id\",
				to_char(EPLD.EvnPLDisp_setDT, 'yyyy-mm-dd') as \"EvnPLDisp_setDate\"
			from
				v_EvnPLDisp EPLD 
				left join v_EvnPLDispTeenInspection EPLDTT on EPLDTT.EvnPLDispTeenInspection_id = EPLD.EvnPLDisp_id
				left join v_EvnPLDispProf EPLDP on EPLDP.EvnPLDispProf_id = EPLD.EvnPLDisp_id
				left join v_EvnPLDispDop13 EPLDD13 on EPLDD13.EvnPLDispDop13_id = EPLD.EvnPLDisp_id
				left join v_PersonState ps  on ps.Person_id = EPLD.Person_id
				left join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
				left join v_EducationInstitutionClass EIC on EIC.EducationInstitutionClass_id = EPLDTT.EducationInstitutionClass_id
				left join v_EducationInstitutionType  EIT on EIT.EducationInstitutionType_id = EIC.EducationInstitutionType_id
				left join v_AssessmentHealth AH  on AH.EvnPLDisp_id = EPLD.EvnPLDisp_id
				left join lateral(
					select
						EVD.EvnVizitDisp_id,
						EVD.Diag_id
					from
						v_DopDispInfoConsent DDIC
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						inner join v_EvnVizitDisp EVD on EVD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					where
						DDIC.EvnPLDisp_id = EPLD.EvnPLDisp_id
						and ST.SurveyType_Code in (19,27) -- Осмотр педиатром или терапевтом
				    limit 1
				) EvnVizit on true
				left join v_Diag D on D.Diag_id = EvnVizit.Diag_id
			where
				EPLD.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные по карте осмотра/диспансеризации', 400);
		}
	}

	/**
	 * Получение данных диагнозов для синхронизации медосмотров/диспансеризаций
	 */
	function getEvnPLDispDiags($data)
	{
		$params = array('EvnPLDisp_id' => $data['EvnPLDisp_id']);
		$filters = "";

		if (!empty($data['EvnVizitDisp_id'])) {
			$filters .= " and EVD.EvnVizitDisp_id = :EvnVizitDisp_id";
			$params['EvnVizitDisp_id'] = $data['EvnVizitDisp_id'];
		}

		$query = "
			select
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				DSC.DiagSetClass_SysNick as \"DiagSetClass_SysNick\",
				DST.DiagSetType_id as \"DiagSetType_id\",
				DST.DiagSetType_SysNick as \"DiagSetType_SysNick\",
				null as DeseaseType_id as \"DeseaseType_id\",
				null as DeseaseType_SysNick as \"DeseaseType_SysNick\",
				to_char(EVD.EvnVizitDisp_setDT, 'yyyy-mm-dd') as \"EvnDiag_setDate\",
				EVD.MedStaffFact_id as \"MedStaffFact_id\",
				1 as \"main\"
			from
				v_EvnVizitDisp EVD
				inner join v_Diag D on D.Diag_id = EVD.Diag_id
				left join v_DiagSetClass DSC  on DSC.DiagSetClass_SysNick = 'osn'
				left join v_DiagSetType DST on DST.DiagSetType_SysNick = 'klin'
			where
				EVD.EvnVizitDisp_rid = :EvnPLDisp_id
				{$filters}
			union
			select
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				DSC.DiagSetClass_SysNick as \"DiagSetClass_SysNick\",
				DST.DiagSetType_id as \"DiagSetType_id\",
				DST.DiagSetType_SysNick as \"DiagSetType_SysNick\",
				DT.DeseaseType_id as \"DeseaseType_id\",
				DT.DeseaseType_SysNick as \"DeseaseType_SysNick\",
				to_char(EDDD.EvnDiagDopDisp_setDT, 'yyyy-mm-dd') as \"EvnDiag_setDate\",
				EVD.MedStaffFact_id as \"MedStaffFact_id\",
				0 as \"main\"
			from
				v_EvnDiagDopDisp EDDD
				inner join v_Diag D on D.Diag_id = EDDD.Diag_id
				left join v_EvnUsluga EU on EU.EvnUsluga_id = EDDD.EvnDiagDopDisp_pid
				left join v_EvnVizitDisp EVD on EVD.EvnVizitDisp_id in (EDDD.EvnDiagDopDisp_pid, EU.EvnUsluga_pid)
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
				left join v_DiagSetType DST on DST.DiagSetType_SysNick = 'klin'
				left join v_DeseaseDispType DDT on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
				left join v_DeseaseType DT on DT.DeseaseType_Code = (case
					when DDT.DeseaseDispType_Code = 1 then 3
					when DDT.DeseaseDispType_Code = 2 then 2
				end)
			where
				EDDD.EvnDiagDopDisp_rid = :EvnPLDisp_id
				{$filters}
			order by
				EvnDiag_setDate
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		} else {
			return null;
		}
	}

	/**
	 * Синхронизация медосмотров/диспансеризий
	 */
	function syncCaseEvnPLDisp($EvnPLDisp_id)
	{
		$caseId = $this->getSyncObject('EvnPLDisp', $EvnPLDisp_id);

		// синхронизируем в любом случае
		if (true || empty($caseId)) {
			$EvnPLDispInfo = $this->getEvnPLDispInfo(array(
				'EvnPLDisp_id' => $EvnPLDisp_id,
			));

			$EvnPLDispDiags = $this->getEvnPLDispDiags($EvnPLDispInfo);

			$clinicId = $this->getSyncSpr('Lpu', $EvnPLDispInfo['Lpu_id']);
			$individualId = null;
			$individualId = $this->getSyncObject('Person', $EvnPLDispInfo['Person_id']);
			if (empty($individualId)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания случая лечения', 400);
			}

			$fundingSourceTypeId = $this->getSyncSpr('PayType', $EvnPLDispInfo['PayType_id']);
			$socialGroupId = $this->getSyncSpr('SocStatus', $EvnPLDispInfo['SocStatus_id']);

			if (empty($caseId)) {
				// поиск случая лечения
				$cases = $this->searchCase(array(
					'caseTypeId' => 1, // Случай поликлинического обслуживания
					'uid' => $EvnPLDispInfo['EvnPLDisp_id'],
					'patientUid' => $individualId,
					'medicalOrganizationId' => $clinicId,
					'openedFromDate' => $EvnPLDispInfo['EvnPLDisp_setDate'],
					'openedToDate' => $EvnPLDispInfo['EvnPLDisp_setDate']
				));

				if (!is_array($cases)) {
					throw new Exception('Ошибка поиска случаев лечения', 500);
				}
				if (count($cases) > 0) {
					$caseId = $cases[0];
					$this->saveSyncObject('EvnPLDisp', $EvnPLDisp_id, $caseId);
				}
			}

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($caseId)) {
				$initGoalId = $this->getRMISVizitTypeId($EvnPLDispInfo);
				$paymentMethodId = $this->getSyncSpr('PayMedType', 44);

				//Диагноз карты диспансеризации берется из услуги "Прием (осмотр) врача терапевта/педиатра"
				$diagnosisId = null;
				if (!empty($EvnPLDispInfo['Diag_Code'])) {
					$diagnosisId = $this->getRMISDiagId($EvnPLDispInfo['Diag_Code']);
					if (empty($diagnosisId)) {
						throw new Exception("Диагноз {$EvnPLDispInfo['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
					}
				}

				$diagArray = array();
				foreach ($EvnPLDispDiags as $diag) {
					$_diagnosId = $this->getRMISDiagId($diag['Diag_Code']);
					if (empty($_diagnosId)) {
						throw new Exception("Диагноз {$diag['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
					}

					$_diseaseTypeId = null;
					if (!empty($diag['Diag_Code']) && $diag['Diag_Code'][0] == 'Z') {
						$_diseaseTypeId = 8; //40 Осмотр (Для Z00 - Z99)
					} else if (!empty($diag['DeseaseType_id'])) {
						$_diseaseTypeId = $this->getSyncSpr('DeseaseType', $diag['DeseaseType_id']);
					}

					$_doctorId = null;
					if (!empty($diag['MedStaffFact_id'])) {
						$_doctor = $this->syncEmployeePositionFull($diag['MedStaffFact_id']);
						$_doctorId = !empty($_doctor['employeePositionId']) ? $_doctor['employeePositionId'] : null;
					}

					$diagArray[] = array(
						'stageId' => $diag['DiagSetType_id'],
						'typeId' => $this->getSyncSpr('DiagSetClass', $diag['DiagSetClass_id']),
						'diagnosId' => $_diagnosId,
						'diseaseTypeId' => $_diseaseTypeId,
						'establishmentDate' => $diag['EvnDiag_setDate'],
						'doctorId' => $_doctorId,
						'main' => $diag['main']
					);
				}

				$caseId = $this->sendCase(array(
					'id' => $caseId,
					'uid' => $EvnPLDispInfo['EvnPLDisp_id'],
					'patientUid' => $individualId,
					'medicalOrganizationId' => $clinicId,
					'caseTypeId' => 1, // Случай поликлинического обслуживания
					'careLevelId' => 1,
					'fundingSourceTypeId' => $fundingSourceTypeId,
					'socialGroupId' => $socialGroupId,
					'paymentMethodId' => 29,
					'careRegimenId' => 1, // Амбулаторная медицинская помощь
					'diagnoses' => $diagArray,
					'initGoalId' => !empty($initGoalId) ? $initGoalId : 1,
					'initTypeId' => 1, // неизвестный справочник mc_case_init_type (не удалось его получить)
					'repeatCountId' => 1, // Первично
					//'referralId' => $referralId,
					'stateId' => 1,    //1. Случай завершен
					'careProvidingFormId' => 3,    //3. Плановая
					'caseResult' => array(
						'careRegimenId' => 1, // Амбулаторная медицинская помощь
						'healthGroupId' => $EvnPLDispInfo['HealthKind_id'],
						'clinicId' => $clinicId,
						//'dispensaryGroupId' => ''
						'diagnosisId' => $diagnosisId,
						'createdDate' => $EvnPLDispInfo['EvnPLDisp_setDate'],
					)
				));
			}

			$this->saveSyncObject('EvnPLDisp', $EvnPLDisp_id, $caseId);
		}

		return $caseId;
	}

	/**
	 * Получение данных для синхронизации посещений по медосмотрам/диспансеризациям
	 */
	function getEvnVizitDispInfo($data)
	{
		$params = array('EvnVizitDisp_id' => $data['EvnVizitDisp_id']);

		$query = "
			select
				EVD.EvnVizitDisp_id, as \"EvnVizitDisp_id\",
				EVD.EvnVizitDisp_pid as \"EvnVizitDisp_pid\",
				EVD.EvnDirection_id as \"EvnDirection_id\",
				EVD.MedPersonal_id as \"MedPersonal_id\",
				EVD.MedStaffFact_id as \"MedStaffFact_id\",
				to_char(EVD.EvnVizitDisp_setDT, 'yyyy-mm-dd') as \"EvnVizitDisp_setDate\",
				to_char(EVD.EvnVizitDisp_setDT, 'hh24:mi:ss') as \"EvnVizitDisp_setTime\",
				LS.LpuSection_id as \"LpuSection_id\",
				coalesce(WP.LpuSectionProfile_id,LS.LpuSectionProfile_id) as \"LpuSectionProfile_id\",
				DC.DispClass_Code as \"DispClass_Code\",
				EIT.EducationInstitutionType_SysNick as \"EducationInstitutionType_SysNick\",
				EPLDTT.AgeGroupDisp_id as \"AgeGroupDisp_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				DSC.DiagSetClass_SysNick as \"DiagSetClass_SysNick\",
				DST.DiagSetType_id as \"DiagSetType_id\",
				DST.DiagSetType_SysNick as \"DiagSetType_SysNick\",
				null as \"DeseaseType_id\",
				null as \"DeseaseType_SysNick\",
				to_char(EVD.EvnVizitDisp_setDT, 'yyyy-mm-dd') as \"EvnDiag_setDate\",
				1 as \"main\"
			from
				v_EvnVizitDisp EVD 
				left join v_EvnPLDisp EPLD  on EPLD.EvnPLDisp_id = EVD.EvnVizitDisp_pid
				left join v_DispClass DC  on DC.DispClass_id = EPLD.DispClass_id
				left join v_EvnPLDispTeenInspection EPLDTT  on EPLDTT.EvnPLDispTeenInspection_id = EPLD.EvnPLDisp_id
				left join v_EducationInstitutionClass  EIC  on EIC.EducationInstitutionClass_id = EPLDTT.EducationInstitutionClass_id
				left join v_EducationInstitutionType  EIT  on EIT.EducationInstitutionType_id = EIC.EducationInstitutionType_id
				left join v_LpuSection LS  on LS.LpuSection_id = EVD.LpuSection_id
				left join persis.WorkPlace WP  on WP.id = EVD.MedStaffFact_id
				left join v_Diag D on D.Diag_id = EVD.Diag_id
				left join v_DiagSetClass DSC on DSC.DiagSetClass_SysNick = 'osn'
				left join v_DiagSetType DST on DST.DiagSetType_SysNick = 'klin'
			where
				EVD.EvnVizitDisp_id = :EvnVizitDisp_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные посещения', 400);
		}
	}

	/**
	 * Синхронизация посещений по медосмотрам/диспансеризациям
	 */
	function syncVisitDisp($EvnVizitDisp_id)
	{
		$visitId = $this->getSyncObject('EvnVizitDisp', $EvnVizitDisp_id);

		// синхронизируем в любом случае
		if (true || empty($visitId)) {
			$EvnVizitDispInfo = $this->getEvnVizitDispInfo(array(
				'EvnVizitDisp_id' => $EvnVizitDisp_id
			));

			$caseId = $this->getSyncObject('EvnPLDisp', $EvnVizitDispInfo['EvnVizitDisp_pid']);
			if (empty($caseId)) {
				throw new Exception('Не найден случай лечения для поиска/создания посещения', 400);
			}

			if (empty($visitId)) {
				// поиск случая лечения
				$visitIds = $this->searchVisit(array(
					'medicalCaseId' => $caseId,
					'visitFromDate' => $EvnVizitDispInfo['EvnVizitDisp_setDate']
				));

				if (!is_array($visitIds)) {
					throw new Exception('Ошибка поиска посещений', 500);
				}
				foreach ($visitIds as $id) {
					$checkEvnVizitDisp_id = $this->getSyncObject('EvnVizitDisp', $id, 'Object_sid');
					if (empty($checkEvnVizitPL_id)) {
						$visitId = $id;
						$this->saveSyncObject('EvnVizitDisp', $EvnVizitDisp_id, $visitId);
						break;
					}
				}
			}

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($visitId)) {
				$goalId = $this->getRMISVizitTypeId($EvnVizitDispInfo);
				$profileId = $this->getSyncSpr('LpuSectionProfile', $EvnVizitDispInfo['LpuSectionProfile_id']);

				$employeeId = $this->getSyncObject('MedStaffFact', $EvnVizitDispInfo['MedStaffFact_id']);
				if (empty($employeeId)) {
					throw new Exception('Не найдено рабочее место врача для передачи посещения', 400);
				}

				$diagArray = array();
				if (!empty($EvnVizitDispInfo['Diag_Code'])) {
					$_diagnosId = $this->getRMISDiagId($EvnVizitDispInfo['Diag_Code']);
					if (empty($_diagnosId)) {
						throw new Exception("Диагноз {$EvnVizitDispInfo['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
					}

					$_diseaseTypeId = null;
					if (!empty($EvnVizitDispInfo['DeseaseType_id'])) {
						$_diseaseTypeId = $this->getSyncSpr('DeseaseType', $EvnVizitDispInfo['DeseaseType_id']);
					}

					$_doctor = $this->syncEmployeePositionFull($EvnVizitDispInfo['MedStaffFact_id']);
					$_doctorId = !empty($_doctor['employeePositionId']) ? $_doctor['employeePositionId'] : null;

					$diagArray[] = array(
						'stageId' => $EvnVizitDispInfo['DiagSetType_id'],
						'typeId' => $this->getSyncSpr('DiagSetClass', $EvnVizitDispInfo['DiagSetClass_id']),
						'diagnosId' => $_diagnosId,
						'diseaseTypeId' => $_diseaseTypeId,
						'establishmentDate' => $EvnVizitDispInfo['EvnDiag_setDate'],
						'doctorId' => $_doctorId,
						'main' => $EvnVizitDispInfo['main']
					);
				}

				$resourceGroupId = $this->syncLocationForEvn('EvnVizitDisp', $EvnVizitDisp_id, $EvnVizitDispInfo['MedStaffFact_id']);
				if (empty($resourceGroupId)) {
					throw new Exception('Не найден специалист, ведущий прием, для передачи посещения', 400);
				}

				$params = array(
					'id' => $visitId,
					'caseId' => $caseId,
					'diagnoses' => $diagArray,
					'admissionDate' => $EvnVizitDispInfo['EvnVizitDisp_setDate'],
					'admissionTime' => $EvnVizitDispInfo['EvnVizitDisp_setTime'],
					'goalId' => $goalId,
					'typeId' => 2,        //2. Профилактика/патронаж
					'placeId' => 1,        //1. В АПУ
					'profileId' => $profileId,
					//'visitResultId' => $visitResultId,
					//'deseaseResultId' => $deseaseResultId,
					'outcomeRegimenId' => 1,
					'regimenId' => 1,
					'resourceGroupId' => $resourceGroupId
				);

				//Отправка данных посещения
				$visitId = $this->sendVisit($params);

				//Сохранение идентификатора системного ресурса
				$locationId = $this->getSyncObject("MedStaffFact_Location_Evn_$EvnVizitDisp_id", $EvnVizitDispInfo['MedStaffFact_id']);
				if (empty($locationId)) {
					$visit = $this->getVisit($visitId);
					if (is_object($visit) && !empty($visit->resourceGroupId)) {
						$this->saveSyncObject("MedStaffFact_Location_Evn_$EvnVizitDisp_id", $EvnVizitDispInfo['MedStaffFact_id'], $visit->resourceGroupId);
					}
				}
			}

			$this->saveSyncObject('EvnVizitDisp', $EvnVizitDisp_id, $visitId);
		}

		return $visitId;
	}

	/**
	 * Получение данных по ТАП
	 */
	function getEvnPLInfo($data)
	{
		$params = array('EvnPL_id' => $data['EvnPL_id']);

		$query = "
			select
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Lpu_id as \"Lpu_id\",
				EPL.Person_id as \"Person_id\",
				EPL.EvnPL_NumCard as \"EvnPL_NumCard\",
				PS.SocStatus_id as \"SocStatus_id\",
				EPL.EvnDirection_id as \"EvnDirection_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EVPLFIRST.PayType_id as \"PayType_id\",
				EVPLFIRST.VizitType_id as \"VizitType_id\",
				EVPLFIRST.VizitClass_id as \"VizitClass_id\",
				EVPLLAST.Diag_id as \"Diag_id\",
				case when EvnUsl.UslugaComplex_id is not null then 44 else 2 end as \"PayMedType_id\"
			from
				v_EvnPL EPL 
				left join lateral(
					select 
						PayType_id,
						VizitType_id,
						VizitClass_id
					from
						v_EvnVizitPL
					where
						EvnVizitPL_pid = EPL.EvnPL_id
					order by
						EvnVizitPL_setDate asc
					limit 1
				) EVPLFIRST on true
				left join lateral(
					select 
						Diag_id
					from
						v_EvnVizitPL
					where
						EvnVizitPL_pid = EPL.EvnPL_id
					order by
						EvnVizitPL_setDate desc
					limit 1
				) EVPLLAST on true
				left join lateral (
					select 
						UslugaComplex.UslugaComplex_id
					from
						v_EvnUsluga EvnUsluga 
						inner join UslugaComplex  on UslugaComplex.UslugaComplex_id = EvnUsluga.UslugaComplex_id
						inner join r66.UslugaComplexPartitionLink UCPL  on UCPL.UslugaComplex_id = UslugaComplex.UslugaComplex_id
						inner join r66.UslugaComplexPartition UCP  on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					where
						EvnUsluga.EvnUsluga_rid = EPL.EvnPL_id
						and UslugaComplexPartition_code='350'
					order by EvnUsluga_id
					limit 1
				) as EvnUsl on true
				left join v_PersonState ps  on ps.Person_id = EPL.Person_id
				left join v_Diag D  on D.Diag_id = EPL.Diag_id
			where
				EPL.EvnPL_id = :EvnPL_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные по ТАП', 400);
		}
	}

	/**
	 * Синхронизация случая лечения
	 */
	function syncCaseEvnPL($EvnPL_id)
	{
		$caseId = $this->getSyncObject('EvnPL', $EvnPL_id);

		// синхронизируем в любом случае
		if (true || empty($caseId)) {
			$EvnPLInfo = $this->getEvnPLInfo(array(
				'EvnPL_id' => $EvnPL_id
			));

			$clinicId = $this->getSyncSpr('Lpu', $EvnPLInfo['Lpu_id']);
			$individualId = $this->getSyncObject('Person', $EvnPLInfo['Person_id']);
			if (empty($individualId)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания случая лечения', 400);
			}

			$fundingSourceTypeId = $this->getSyncSpr('PayType', $EvnPLInfo['PayType_id']);
			$socialGroupId = $this->getSyncSpr('SocStatus', $EvnPLInfo['SocStatus_id']);

			if (empty($caseId)) {
				// поиск случая лечения
				$cases = $this->searchCase(array(
					'caseTypeId' => 1, // Случай поликлинического обслуживания
					'uid' => $EvnPLInfo['EvnPL_NumCard'],
					'patientUid' => $individualId,
					'medicalOrganizationId' => $clinicId
				));

				if (!is_array($cases)) {
					throw new Exception('Ошибка поиска случаев лечения', 500);
				}
				if (count($cases) > 0) {
					$caseId = $cases[0];
					$this->saveSyncObject('EvnPL', $EvnPL_id, $caseId);
				}
			}

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($caseId)) {
				$initGoalId = $this->getSyncSpr('VizitType', $EvnPLInfo['VizitType_id']);
				$paymentMethodId = $this->getSyncSpr('PayMedType', $EvnPLInfo['PayMedType_id']);

				$diagnosisId = $this->getRMISDiagId($EvnPLInfo['Diag_Code']);
				if (empty($diagnosisId)) {
					throw new Exception("Диагноз {$EvnPLInfo['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
				}

				$referralId = null;
				if (!empty($EvnPLInfo['EvnDirection_id'])) {
					$referralId = $this->getSyncObject('EvnDirection', $EvnPLInfo['EvnDirection_id']);
					if (empty($referralId)) {
						throw new Exception('Не найдено направление для создания случая лечения', 400);
					}
				}

				$diagArray = array();

				$caseId = $this->sendCase(array(
					'id' => $caseId,
					'uid' => $EvnPLInfo['EvnPL_NumCard'],
					'patientUid' => $individualId,
					'medicalOrganizationId' => $clinicId,
					'caseTypeId' => 1, // Случай поликлинического обслуживания
					'careLevelId' => 1, // Случай поликлинического обслуживания
					'fundingSourceTypeId' => $fundingSourceTypeId,
					'socialGroupId' => $socialGroupId,
					'paymentMethodId' => $paymentMethodId,
					'careRegimenId' => 1, // Амбулаторная медицинская помощь
					'diagnoses' => $diagArray,
					'initGoalId' => $initGoalId,
					'initTypeId' => 1, // неизвестный справочник mc_case_init_type (не удалось его получить)
					'repeatCountId' => $EvnPLInfo['VizitClass_id'],
					'referralId' => $referralId,
					'caseResult' => array(
						'careRegimenId' => 1, // Амбулаторная медицинская помощь
						'clinicId' => $clinicId,
						'diagnosisId' => $diagnosisId
					)
				));
			}

			$this->saveSyncObject('EvnPL', $EvnPL_id, $caseId);
		}

		return $caseId;
	}

	/**
	 * Получение данных по посещению
	 */
	function getEvnVizitPLInfo($data)
	{
		$params = array('EvnVizitPL_id' => $data['EvnVizitPL_id']);

		$query = "
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.EvnVizitPL_pid as \"EvnVizitPL_pid\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				coalesce(WP.LpuSectionProfile_id,EVPL.LpuSectionProfile_id,LS.LpuSectionProfile_id) as \"LpuSectionProfile_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EVPL.DeseaseType_id as \"DeseaseType_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedStaffFact_id as \"MedStaffFact_id\",
				to_char(EVPL.EvnVizitPL_setDT, 'yyyy-mm-dd') as \"EvnVizitPL_setDate\",
				to_char(EVPL.EvnVizitPL_setDT, 'hh24:mi:ss') as \"EvnVizitPL_setTime\",
				case when EVPL.EvnVizitPL_Index+1 = EVPL.EvnVizitPL_Count then LT.LeaveType_id end as \"LeaveType_id\",	--Выгружать только в последнем
				case when EVPL.EvnVizitPL_Index+1 = EVPL.EvnVizitPL_Count then EPL.ResultDeseaseType_id end as \"ResultDeseaseType_id\", --Выгружать только в последнем
				EVPL.Mes_id as \"Mes_id\"
			from
				v_EvnVizitPL EVPL
				left join v_EvnPL EPL  on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_ResultClass RC  on RC.ResultClass_id = EPL.ResultClass_id
				left join v_LeaveType LT  on LT.LeaveType_fedid = RC.LeaveType_fedid
				left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_Diag D on D.Diag_id = EVPL.Diag_id
				left join persis.WorkPlace WP  on WP.id = EVPL.MedStaffFact_id
			where
				EVPL.EvnVizitPL_id = :EvnVizitPL_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные по посещению', 400);
		}
	}

	/**
	 * Поиск услуг
	 */
	function searchServiceRend($params)
	{
		$result = $this->exec('renderedServices', 'searchServiceRend', $params);

		if (!isset($result->ids)) {
			return array();
		}

		$serviceRends = array();
		if (is_array($result->ids)) {
			$serviceRends = $result->ids;
		} else {
			$serviceRends[] = $result->ids;
		}

		return $serviceRends;
	}

	/**
	 * Поиск видов услуг
	 */
	function getServices($params)
	{
		$result = $this->exec('services', 'getServices', $params);

		if (!isset($result->services)) {
			return array();
		}

		$services = array();
		if (is_array($result->services)) {
			$services = $result->services;
		} else {
			$services[] = $result->services;
		}

		return $services;
	}

	/**
	 * Поиск посещений
	 */
	function searchVisit($params)
	{
		$result = $this->exec('visits', 'searchVisit', $params);

		if (!isset($result->ids)) {
			return array();
		}

		$visits = array();
		if (is_array($result->ids)) {
			$visits = $result->ids;
		} else {
			$visits[] = $result->ids;
		}

		return $visits;
	}

	/**
	 * Поиск движений
	 */
	function searchHspRecord($params)
	{
		$result = $this->exec('hsp-records', 'searchHspRecord', $params);

		if (!isset($result->ids)) {
			return array();
		}

		$hspRecords = array();
		if (is_array($result->ids)) {
			$hspRecords = $result->ids;
		} else {
			$hspRecords[] = $result->ids;
		}

		return $hspRecords;
	}

	/**
	 * Поиск направлений
	 */
	function searchReferral($params)
	{
		$result = $this->exec('referrals', 'searchReferral', $params);

		if (!isset($result->ids)) {
			return array();
		}

		$referrals = array();
		if (is_array($result->ids)) {
			$referrals = $result->ids;
		} else {
			$referrals[] = $result->ids;
		}

		return $referrals;
	}

	/**
	 * Получение посещения
	 */
	function getVisit($visitId)
	{
		return $this->exec('visits', 'getVisitById', array('id' => $visitId));
	}

	/**
	 * Получение движения
	 */
	function getHspRecord($hspRecordId)
	{
		return $this->exec('hsp-records', 'getHspRecordById', array('id' => $hspRecordId));
	}

	/**
	 * Получение услуги
	 */
	function getServiceRend($serviceRendId)
	{
		return $this->exec('renderedServices', 'getServiceRendById', array('id' => $serviceRendId));
	}

	/**
	 * Синхронизация услуг
	 */
	function syncEvnUslugas($EvnUsluga_pid)
	{
		// получаем все данные по услугам
		$query = "
			select
				eu.EvnUsluga_id as \"EvnUsluga_id\",
				eu.EvnDirection_id as \"EvnDirection_id\",
				case
					when PT.PayType_SysNick = 'dopdisp' then epld.PayType_id
					else PT.PayType_id
				end as \"PayType_id\",
				eu.EvnUsluga_Summa as \"EvnUsluga_Summa\",
				coalesce(eu.MedStaffFact_id, evd.MedStaffFact_id, MainEvnVizitDisp.MedStaffFact_id, es.MedStaffFact_id, evpl.MedStaffFact_id) as \"MedStaffFact_id\",
				d.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				to_char(coalesce(eu.EvnUsluga_setDT, evd.EvnVizitDisp_setDT), 'yyyy-mm-dd') as \"EvnUsluga_setDate\",
				to_char(coalesce(eu.EvnUsluga_setDT, evd.EvnVizitDisp_setDT), 'hh24:mi:ss') as \"EvnUsluga_setTime\",
				to_char(coalesce(eu.EvnUsluga_disDT, evd.EvnVizitDisp_disDT), 'yyyy-mm-dd') as \"EvnUsluga_disDate\",
				eu.EvnUsluga_Kolvo as \"EvnUsluga_Kolvo\",
				eu.EvnUsluga_isCito as \"EvnUsluga_isCito\",
				eu.Person_id as \"Person_id\",
				eu.Lpu_id as \"Lpu_id\",
				eu.UslugaComplex_id as \"UslugaComplex_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				ParentEvn.Evn_id as \"ParentEvn_id\",
				case
					when ParentEvn.EvnClass_SysNick in ('EvnPLDispOrp','EvnPLDispDop13','EvnPLDispTeenInspection','EvnPLDispProf') then 'EvnPLDisp'
					when ParentEvn.EvnClass_SysNick in ('EvnVizitDispDop','EvnVizitDispOrp') then 'EvnVizitDisp'
					when ParentEvn.EvnClass_SysNick in ('EvnVizitPLStom') then 'EvnVizitPL'
					else ParentEvn.EvnClass_SysNick
				end as \"ParentEvnClass_SysNick\",
				RootEvn.Evn_id as \"RootEvn_id\",
				case
					when RootEvn.EvnClass_SysNick in ('EvnPLDispOrp','EvnPLDispDop13','EvnPLDispTeenInspection','EvnPLDispProf') then 'EvnPLDisp'
					when RootEvn.EvnClass_SysNick in ('EvnVizitDispDop','EvnVizitDispOrp') then 'EvnVizitDisp'
					when RootEvn.EvnClass_SysNick in ('EvnPLStom') then 'EvnPL'
					else RootEvn.EvnClass_SysNick
				end as \"RootEvnClass_SysNick\",
				UCP.UslugaComplexPartition_Code as \"UslugaComplexPartition_Code\"
			from
				v_EvnUsluga eu
				inner join v_UslugaComplex uc  on uc.UslugaComplex_id = eu.UslugaComplex_id
				inner join v_Evn ParentEvn on ParentEvn.Evn_id = eu.EvnUsluga_pid
				inner join v_Evn RootEvn  on RootEvn.Evn_id = eu.EvnUsluga_rid
				left join v_EvnSection es on es.EvnSection_id = ParentEvn.Evn_id
				left join v_EvnVizitPL evpl  on evpl.EvnVizitPL_id = ParentEvn.Evn_id
				left join v_EvnVizitDisp evd on evd.EvnVizitDisp_id = ParentEvn.Evn_id
				left join v_EvnPLDisp epld on epld.EvnPLDisp_id = RootEvn.Evn_id
				left join v_Diag d on d.Diag_id = coalesce(eu.Diag_id, evd.Diag_id)
				left join v_LpuSection LS  on LS.LpuSection_id = eu.LpuSection_uid
				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_PayType PT  on PT.PayType_id = eu.PayType_id
				left join lateral(
					selectUCP.UslugaComplexPartition_Code
					from r66.UslugaComplexPartitionLink UCPL
					inner join r66.UslugaComplexPartition UCP on UCPL.UslugaComplexPartition_id=UCP.UslugaComplexPartition_id
						and ((medicalCareType_id=1 and LU.LpuUnitType_SysNick = 'stac') or ( MedicalCareType_id=2 and LU.LpuUnitType_SysNick in ('dstac','hstac','pstac')))
					where eu.UslugaComplex_id = UCPL.UslugaComplex_id
						and UCPL.UslugaComplexPartitionLink_begDT < = eu.EvnUsluga_disDate
						and (UCPL.UslugaComplexPartitionLink_endDT > eu.EvnUsluga_disDate or UCPL.UslugaComplexPartitionLink_endDT is null)
					limit 1
				) UCP on true
				left join lateral(
					select
						EVD.MedStaffFact_id
					from
						v_DopDispInfoConsent DDIC
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						inner join v_EvnVizitDisp EVD on EVD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					where
						DDIC.EvnPLDisp_id = epld.EvnPLDisp_id
						and ST.SurveyType_Code in (19,27) -- Осмотр педиатром или терапевтом
				    limit 1
				) MainEvnVizitDisp on true
			where
				eu.EvnUsluga_pid = :EvnUsluga_pid
				and ParentEvn.EvnClass_SysNick in (
					'EvnSection','EvnVizitPL','EvnVizitPLStom',
					'EvnVizitDispDop','EvnVizitDispOrp','EvnPLDispOrp',
					'EvnPLDispDop13','EvnPLDispTeenInspection','EvnPLDispProf'
				)
		";

		$resp = $this->queryResult($query, array(
			'EvnUsluga_pid' => $EvnUsluga_pid
		));

		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных о услугах', 500);
		}

		foreach ($resp as $respone) {
			$fundingSourceTypeId = $this->getSyncSpr('PayType', $respone['PayType_id']);
			$orgId = $this->getSyncSpr('Lpu', $respone['Lpu_id']);

			$diagnosisId = null;
			if (!empty($respone['Diag_Code'])) {
				$diagnosisId = $this->getRMISDiagId($respone['Diag_Code']);
				if (empty($diagnosisId)) {
					throw new Exception("Диагноз {$respone['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
				}
			}

			$medicalCaseId = $this->getSyncObject($respone['RootEvnClass_SysNick'], $respone['RootEvn_id']);
			if (empty($medicalCaseId)) {
				throw new Exception('Не найдено ТАП/КВС для поиска/создания услуг', 400);
			}

			//Если родитель - движение или посещение, то передавать stepId
			$stepId = null;
			if (in_array($respone['ParentEvnClass_SysNick'], array('EvnSection', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnVizitDisp'))) {
				$stepId = $this->getSyncObject($respone['ParentEvnClass_SysNick'], $respone['ParentEvn_id']);
				if (empty($stepId)) {
					throw new Exception('Не найдено посещение/движение для поиска/создания услуг', 400);
				}
			}

			$referralId = null;
			if (!empty($respone['EvnDirection_id'])) {
				$referralId = $this->syncReferral($respone['EvnDirection_id']);
				if (empty($referralId)) {
					throw new Exception('Не найдено направление для создания услуги', 400);
				}
			}

			$patientUid = $this->getSyncObject('Person', $respone['Person_id']);
			if (empty($patientUid)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания услуги', 400);
			}

			$serviceId = $this->syncService($respone['UslugaComplex_id'], array(
				'partition' => $respone['UslugaComplexPartition_Code'],
				'code' => $respone['UslugaComplex_Code'],
				'name' => $respone['UslugaComplex_Name'],
				'clinic' => $orgId
			));

			$whollyRendered = null;
			if (in_array($respone['ParentEvnClass_SysNick'], array('EvnVizitDispDop', 'EvnVizitDispOrp', 'EvnPLDispOrp'))) {
				$whollyRendered = true;
			}

			if (empty($serviceId)) {
				throw new Exception('Не удалось синхронизировать вид услуги медицинской организации', 400);
			}

			$params = array(
				'serviceId' => $serviceId,
				'medicalCaseId' => $medicalCaseId,
				'stepId' => $stepId,
				'referralId' => $referralId,
				'diagnosisId' => $diagnosisId,
				'dateFrom' => $respone['EvnUsluga_setDate'],
				'timeFrom' => $respone['EvnUsluga_setTime'],
				'dateTo' => $respone['EvnUsluga_disDate'],
				'isRendered' => 'true',
				'quantity' => $respone['EvnUsluga_Kolvo'],
				'fundingSourceTypeId' => $fundingSourceTypeId,
				'cost' => $respone['EvnUsluga_Summa'],
				'totalCost' => $respone['EvnUsluga_Summa'],
				'isUrgent' => ($respone['EvnUsluga_isCito'] == 2) ? 'true' : 'false',
				'patientUid' => $patientUid,
				'orgId' => $orgId,
				'whollyRendered' => $whollyRendered
				//'resourceGroupId' => $resourceGroupId
			);
			$this->syncServiceRend($respone['EvnUsluga_id'], $respone['MedStaffFact_id'], $params);
		}

		return true;
	}

	/**
	 * Синхронизация выполненной услуги
	 */
	function syncServiceRend($EvnUsluga_id, $MedStaffFact_id, $params)
	{
		$serviceRendId = $this->getSyncObject('EvnUsluga', $EvnUsluga_id);

		// синхронизируем в любом случае
		if (true || empty($serviceRendId)) {
			if (empty($serviceRendId)) {
				// поиск случая лечения
				$serviceRends = $this->searchServiceRend(array(
					'medicalCaseId' => $params['medicalCaseId'],
					'patientUid' => $params['patientUid'],
					'serviceId' => $params['serviceId'],
					'dateFrom' => $params['dateFrom']
				));

				if (!is_array($serviceRends)) {
					throw new Exception('Ошибка поиска услуг', 500);
				}
				if (count($serviceRends) > 0) {
					$serviceRendId = $serviceRends[0];
					$this->saveSyncObject('EvnUsluga', $EvnUsluga_id, $serviceRendId);
				}
			}

			//Синхронизация системного ресурса
			$params['resourceGroupId'] = $this->syncLocationForEvn('EvnUsluga', $EvnUsluga_id, $MedStaffFact_id);
			if (empty($params['resourceGroupId'])) {
				//Вероятно врач ещё не был синхронизован. Запускаем полную синхронизацию
				$result = $this->syncEmployeePositionFull($MedStaffFact_id);
				$params['resourceGroupId'] = $result['locationId'];
			}
			if (empty($params['resourceGroupId'])) {
				throw new Exception('Не найден специалист, ведущий прием, для передачи услуги', 400);
			}

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($serviceRendId)) {
				$serviceRendId = $this->sendServiceRend(array(
					'id' => $serviceRendId,
					'serviceId' => $params['serviceId'],
					'medicalCaseId' => $params['medicalCaseId'],
					'referralId' => $params['referralId'],
					'diagnosisId' => $params['diagnosisId'],
					'dateFrom' => $params['dateFrom'],
					'timeFrom' => $params['timeFrom'],
					'dateTo' => $params['dateTo'],
					'isRendered' => 'true',
					'quantity' => $params['quantity'],
					'fundingSourceTypeId' => $params['fundingSourceTypeId'],
					'cost' => $params['cost'],
					'totalCost' => $params['totalCost'],
					'isUrgent' => $params['isUrgent'],
					'patientUid' => $params['patientUid'],
					'orgId' => $params['orgId'],
					'resourceGroupId' => $params['resourceGroupId'],
					'whollyRendered' => $params['whollyRendered'],
				));

				//Сохранение идентификатора системного ресурса
				$locationId = $this->getSyncObject("MedStaffFact_Location_Evn_$EvnUsluga_id", $MedStaffFact_id);
				if (empty($locationId)) {
					$serviceRend = $this->getServiceRend($serviceRendId);
					if (is_object($serviceRend) && !empty($serviceRend->resourceGroupId)) {
						$this->saveSyncObject("MedStaffFact_Location_Evn_$EvnUsluga_id", $MedStaffFact_id, $serviceRend->resourceGroupId);
					}
				}
			}

			$this->saveSyncObject('EvnUsluga', $EvnUsluga_id, $serviceRendId);
		}

		return $serviceRendId;
	}

	/**
	 * Синхронизация услуги
	 */
	function syncService($UslugaComplex_id, $params)
	{
		$serviceId = $this->getSyncObject('UslugaComplex', $UslugaComplex_id);
		if (empty($serviceId)) {
			if (empty($serviceId)) {
				// поиск случая лечения
				$services = $this->getServices(array(
					'clinic' => $params['clinic'],
					'code' => $params['code']
				));

				$filtered_services = array_filter($services, function ($service) use ($params) {
					$substr = !empty($params['partition']) ? '(' . $params['partition'] . ')' : '';
					return ($service->code == $params['code'] && (empty($substr) || substr_count($service->name, $substr) > 0));
				});

				if (isset($filtered_services[0])) {
					$serviceId = $filtered_services[0]->id;
				}
			}

			if (empty($serviceId)) {
				$serviceId = $this->createService(array(
					'clinic' => $params['clinic'],
					'code' => $params['code'],
					'name' => $params['name'],
					'type' => 0 // default
				));
			}

			$this->saveSyncObject('UslugaComplex', $UslugaComplex_id, $serviceId);
		}

		return $serviceId;
	}

	/**
	 * Получение идентификатора из справочника услуг РМИС по МЭС
	 */
	function syncServiceIdByMes($Mes_id, $Lpu_id, $LpuUnitType_SysNick)
	{
		$Mes = $this->getFirstRowFromQuery("
			select
				Mes_Code as \"Mes_Code\",
				Mes_Name as \"Mes_Name\",
				to_char(Mes_begDT, 'yyyy-mm-dd') as \"Mes_begDate\",
				to_char(Mes_endDT, 'yyyy-mm-dd') as \"Mes_endDate\"
			from v_MesOld where Mes_id = :Mes_id
			limit 1
		", array('Mes_id' => $Mes_id));
		if (!is_array($Mes)) {
			throw new Exception('Ошибка поиска МЭС', 500);
		}

		$clinicId = $this->getSyncSpr('Lpu', $Lpu_id);
		$services = $this->getServices(array(
			'clinic' => $clinicId,
			'code' => $Mes['Mes_Code']
		));

		$partition = $LpuUnitType_SysNick == 'stac' ? 101 : 201;
		$filtered_services = array_values(array_filter($services, function ($service) use ($Mes, $partition) {
			$substr = !empty($partition) ? '(' . $partition . ')' : '';
			return (
				$service->code == $Mes['Mes_Code']
				&& (empty($substr) || substr_count($service->name, $substr) > 0)
				&& substr_count($service->name, $Mes['Mes_Name']) > 0
			);
		}));
		$serviceId = (isset($filtered_services[0])) ? $filtered_services[0]->id : null;

		if (empty($serviceId)) {
			$name = "{$Mes['Mes_Code']} ({$partition}) {$Mes['Mes_Name']}";

			$params = array(
				'clinic' => $clinicId,
				'category' => $partition,
				'code' => $Mes['Mes_Code'],
				'name' => $name,
				'type' => 0, // default
				'fromDate' => $Mes['Mes_begDate'],
				'toDate' => $Mes['Mes_endDate'],
				'finType' => array(        //Zero or more repetitions
					array('id' => 8)    //ОМС
				)
			);

			$serviceId = $this->createService($params);
		}

		return $serviceId;
	}

	/**
	 * Синхронизация МЭС(КСГ) как услуги в РМИС
	 */
	function syncServiceRendByMes($data)
	{
		$serviceRendId = $this->getSyncObject('MesUsluga', $data['EvnSection_id']); // для КВС может быть несколько КСГ (в каждом движении, поэтому вяжем к движению)

		// синхронизируем в любом случае
		if (true || empty($serviceRendId)) {
			$serviceId = $this->syncServiceIdByMes($data['Mes_id'], $data['Lpu_id'], $data['LpuUnitType_SysNick']);
			if (empty($serviceId)) {
				throw new Exception("В сервисе РМИС не найдена услуга по МЭС с id={$data['Mes_id']}", 400);
			}

			$medicalCaseId = $this->getSyncObject($data['EvnClass_SysNick'], $data['Evn_id']);

			$patientUid = $this->getSyncObject('Person', $data['Person_id']);
			if (empty($patientUid)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания услуги', 400);
			}

			$orgId = $this->getSyncSpr('Lpu', $data['Lpu_id']);
			$fundingSourceTypeId = $this->getSyncSpr('PayType', $data['PayType_id']);

			if (empty($serviceRendId)) {
				// поиск случая лечения
				$serviceRends = $this->searchServiceRend(array(
					'medicalCaseId' => $medicalCaseId,
					'serviceId' => $serviceId
				));

				if (!is_array($serviceRends)) {
					throw new Exception('Ошибка поиска услуг', 500);
				}
				if (count($serviceRends) > 0) {
					$serviceRendId = $serviceRends[0];
					$this->saveSyncObject('MesUsluga', $data['EvnSection_id'], $serviceRendId);
				}
			}

			//Синхронизация системного ресурса
			/*$params['resourceGroupId'] = $this->syncLocationForEvn('CsgUsluga', $Evn_id, $data['MedStaffFact_id']);
			if (empty($params['resourceGroupId'])) {
				throw new Exception('Не найден специалист, ведущий прием, для передачи услуги', 400);
			}*/

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($serviceRendId)) {
				$serviceRendId = $this->sendServiceRend(array(
					'id' => $serviceRendId,
					'serviceId' => $serviceId,
					'medicalCaseId' => $medicalCaseId,
					//'referralId' => $params['referralId'],
					//'diagnosisId' => $params['diagnosisId'],
					'dateFrom' => $data['Evn_setDate'],
					'dateTo' => $data['Evn_disDate'],
					'isRendered' => 'true',
					'quantity' => 1,
					'fundingSourceTypeId' => $fundingSourceTypeId,
					//'cost' => $params['cost'],
					//'totalCost' => $params['totalCost'],
					'isUrgent' => false,
					'patientUid' => $patientUid,
					'orgId' => $orgId,
					//'resourceGroupId' => $params['resourceGroupId'],
					//'whollyRendered' => $params['whollyRendered'],
				));

				//Сохранение идентификатора системного ресурса
				/*$locationId = $this->getSyncObject("MedStaffFact_Location_Evn_$EvnUsluga_id", $MedStaffFact_id);
				if (empty($locationId)) {
					$serviceRend = $this->getServiceRend($serviceRendId);
					if (is_object($serviceRend) && !empty($serviceRend->resourceGroupId)) {
						$this->saveSyncObject("MedStaffFact_Location_Evn_$EvnUsluga_id", $MedStaffFact_id, $serviceRend->resourceGroupId);
					}
				}*/
			}

			$this->saveSyncObject('MesUsluga', $data['EvnSection_id'], $serviceRendId);
		}

		return $serviceRendId;
	}

	/**
	 * @param $EvnDirection_id
	 */
	function syncReferral($EvnDirection_id)
	{
		$referralId = $this->getSyncObject('EvnDirection', $EvnDirection_id);

		if (true || empty($referralId)) {
			$EvnDirectionInfo = $this->getEvnDirectionInfo(array(
				'EvnDirection_id' => $EvnDirection_id
			));

			$referralOrganizationId = $this->getSyncSpr('Lpu', $EvnDirectionInfo['Lpu_id']);
			$receivingOrganizationId = $this->getSyncSpr('Lpu', $EvnDirectionInfo['Lpu_did']);
			$cancelSourceOrganizationId = $this->getSyncSpr('Lpu', $EvnDirectionInfo['Lpu_fid'], null, false, true);
			$typeId = $this->getSyncSpr('DirType', $EvnDirectionInfo['DirType_id'], 'DirType_id', true);
			$fundingSourceTypeId = $this->getSyncSpr('PayType', $EvnDirectionInfo['PayType_id']);
			$referringDepartmentId = null;
			$receivingDepartmentId = null;
			$referralSpecialistId = null;
			$receivingSpecialistId = null;

			if (!empty($EvnDirectionInfo['Lpu_id']) && !empty($EvnDirectionInfo['LpuSection_id'])) {
				$referringDepartmentId = $this->syncDepartment($EvnDirectionInfo['Lpu_id'], $EvnDirectionInfo['LpuSection_id']);
			}
			if (!empty($EvnDirectionInfo['Lpu_did']) && !empty($EvnDirectionInfo['LpuSection_did'])) {
				$receivingDepartmentId = $this->syncDepartment($EvnDirectionInfo['Lpu_did'], $EvnDirectionInfo['LpuSection_did']);
			}

			if ($EvnDirectionInfo['MedStaffFact_id']) {
				$referralSpecialistId = $this->getSyncObject('MedStaffFact', $EvnDirectionInfo['MedStaffFact_id']);
				if (empty($referralSpecialistId)) {
					$referralSpecialist = $this->syncEmployeePositionFull($EvnDirectionInfo['MedStaffFact_id']);
					$referralSpecialistId = !empty($referralSpecialist['employeePositionId']) ? $referralSpecialist['employeePositionId'] : null;
				}
			}

			if (!empty($EvnDirectionInfo['MedStaffFact_did'])) {
				$receivingSpecialistId = $this->getSyncObject('MedStaffFact', $EvnDirectionInfo['MedStaffFact_did']);
				if (empty($receivingSpecialistId)) {
					$receivingSpecialist = $this->syncEmployeePositionFull($EvnDirectionInfo['MedStaffFact_did']);
					$receivingSpecialistId = !empty($receivingSpecialist['employeePositionId']) ? $receivingSpecialist['employeePositionId'] : null;
				}
			}

			$diagnosisId = $this->getRMISDiagId($EvnDirectionInfo['Diag_Code']);

			$individualId = $this->getSyncObject('Person', $EvnDirectionInfo['Person_id']);
			if (empty($individualId)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания направления', 400);
			}

			$stepId = null;
			if (!empty($EvnDirectionInfo['ParentEvn_id']) && in_array($EvnDirectionInfo['ParentEvnClass_SysNick'], array('EvnVizitPL', 'EvnSection'))) {
				$stepId = $this->getSyncObject($EvnDirectionInfo['ParentEvnClass_SysNick'], $EvnDirectionInfo['ParentEvn_id']);
				switch ($EvnDirectionInfo['ParentEvnClass_SysNick']) {
					//Выбросит исключение, если не найдет посещение/движение
					case 'EvnVizitPL':
						$this->getVisit($stepId);
						break;
					case 'EvnSection':
						$this->getHspRecord($stepId);
						break;
				}
			}

			if (empty($referralId)) {
				// поиск движения
				$searchParams = array(
					'patientUid' => $individualId,
					'referralFromDate' => $EvnDirectionInfo['EvnDirection_setDate'],
					'referralToDate' => $EvnDirectionInfo['EvnDirection_setDate'],
					'referralOrganizationId' => $referralOrganizationId,
					'referringDepartmentId' => $referringDepartmentId,
					'referralSpecialistId' => $referralSpecialistId,
					'receivingOrganizationId' => $receivingOrganizationId,
					'receivingDepartmentId' => $receivingDepartmentId,
					'receivingSpecialistId' => $receivingSpecialistId,
					'diagnosisId' => $diagnosisId,
				);
				if (!empty($stepId)) {
					$searchParams['stepId'] = $stepId;
				}
				$referrals = $this->searchReferral($searchParams);

				if (!is_array($referrals)) {
					throw new Exception('Ошибка поиска направлений', 500);
				}
				if (count($referrals) > 0) {
					$referralId = $referrals[0];
					$this->saveSyncObject('EvnDirection', $EvnDirection_id, $referralId);
				}
			}

			$params = array(
				'id' => $referralId,
				'patientUid' => $individualId,
				'number' => $EvnDirectionInfo['EvnDirection_Num'],
				'stepId' => $stepId,    //посещение/движение
				'typeId' => $typeId,
				'referralDate' => $EvnDirectionInfo['EvnDirection_setDate'],
				'referralOrganizationId' => $referralOrganizationId,
				'referringDepartmentId' => $referringDepartmentId,
				'referralSpecialistId' => $referralSpecialistId,
				'diagnosisId' => $diagnosisId,
				'receivingOrganizationId' => $receivingOrganizationId,
				'receivingDepartmentId' => $receivingDepartmentId,
				'receivingSpecialistId' => $receivingSpecialistId,
				//'receivingResourceId' => '',
				'fundingSourceTypeId' => $fundingSourceTypeId,
				//'note' => $EvnDirectionInfo['EvnDirection_Descr'],
				'cancelReasonDetail' => $EvnDirectionInfo['DirFailType_Name'],
				'cancelSourceOrganizationId' => $cancelSourceOrganizationId,
				//'refStatus' => false,	//Статус направления: оказано или нет
				//'orderNumber' => $EvnDirectionInfo['EvnDirection_Num'],	//номер очереди
				//'isUrgent' => '', //срочность
				//'diagnosisNote' => '',
				//'serviceId' => '',	//услуга
				//'refServiceId' => '', //вид услуги
				//'refServicePrototypeId' => '',
				//'goalId' => '',
			);

			$referralId = $this->sendReferral($params);

			$this->saveSyncObject('EvnDirection', $EvnDirection_id, $referralId);
		}

		return $referralId;
	}

	/**
	 * Синхронизация посещения
	 */
	function syncVisit($EvnVizitPL_id)
	{
		$visitId = $this->getSyncObject('EvnVizitPL', $EvnVizitPL_id);

		// синхронизируем в любом случае
		if (true || empty($visitId)) {
			$EvnVizitPLInfo = $this->getEvnVizitPLInfo(array(
				'EvnVizitPL_id' => $EvnVizitPL_id
			));

			$caseId = $this->getSyncObject('EvnPL', $EvnVizitPLInfo['EvnVizitPL_pid']);
			if (empty($caseId)) {
				throw new Exception('Не найден случай лечения для поиска/создания посещения', 400);
			}

			if (empty($visitId)) {
				// поиск случая лечения
				$visitIds = $this->searchVisit(array(
					'medicalCaseId' => $caseId,
					'visitFromDate' => $EvnVizitPLInfo['EvnVizitPL_setDate'],
					'visitToDate' => $EvnVizitPLInfo['EvnVizitPL_setDate'],
				));

				if (!is_array($visitIds)) {
					throw new Exception('Ошибка поиска посещений', 500);
				}
				foreach ($visitIds as $id) {
					$checkEvnVizitPL_id = $this->getSyncObject('EvnVizitPL', $id, 'Object_sid');
					if (empty($checkEvnVizitPL_id)) {
						$visitId = $id;
						$this->saveSyncObject('EvnVizitPL', $EvnVizitPL_id, $visitId);
						break;
					}
				}
			}

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($visitId)) {
				$goalId = $this->getRMISVizitTypeId($EvnVizitPLInfo);

				$typeId = $this->getSyncSpr('VizitTypePL', $EvnVizitPLInfo['VizitType_id'], 'VizitType_id', false, true);
				$placeId = $this->getSyncSpr('ServiceType', $EvnVizitPLInfo['ServiceType_id'], null, true);
				$profileId = $this->getSyncSpr('LpuSectionProfile', $EvnVizitPLInfo['LpuSectionProfile_id']);
				$diseaseTypeId = $this->getSyncSpr('DeseaseType', $EvnVizitPLInfo['DeseaseType_id']);
				$visitResultId = $this->getSyncSpr('LeaveType', $EvnVizitPLInfo['LeaveType_id']);
				$deseaseResultId = $this->getSyncSpr('ResultDeseaseType', $EvnVizitPLInfo['ResultDeseaseType_id']);
				$mesId = $this->getSyncSpr('MesOld', $EvnVizitPLInfo['Mes_id'], null, false, true);

				$diagnosisId = $this->getRMISDiagId($EvnVizitPLInfo['Diag_Code']);
				if (empty($diagnosisId)) {
					throw new Exception("Диагноз {$EvnVizitPLInfo['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
				}

				/*$employeeId = $this->getSyncObject('MedStaffFact', $EvnVizitPLInfo['MedStaffFact_id']);
				if (empty($employeeId)) {
					throw new Exception('Не найдено рабочее место врача для передачи посещения', 400);
				}*/

				$diagArray = array();

				if (!empty($diagnosisId)) {
					$_doctor = $this->syncEmployeePositionFull($EvnVizitPLInfo['MedStaffFact_id']);
					$_doctorId = !empty($_doctor['employeePositionId']) ? $_doctor['employeePositionId'] : null;

					$diagArray[] = array(
						'stageId' => 2,
						'typeId' => 1,    //Основной диагноз
						'diagnosId' => $diagnosisId,
						'diseaseTypeId' => $diseaseTypeId,
						//'injuryTypeId' => '',
						'establishmentDate' => $EvnVizitPLInfo['EvnVizitPL_setDate'],
						'doctorId' => $_doctorId,
						'main' => true
					);
				}

				$resourceGroupId = $this->syncLocationForEvn('EvnVizitPL', $EvnVizitPL_id, $EvnVizitPLInfo['MedStaffFact_id']);
				if (empty($resourceGroupId)) {
					throw new Exception('Не найден специалист, ведущий прием, для передачи посещения', 400);
				}

				$params = array(
					'id' => $visitId,
					'caseId' => $caseId,
					'diagnoses' => $diagArray,
					'admissionDate' => $EvnVizitPLInfo['EvnVizitPL_setDate'],
					'admissionTime' => $EvnVizitPLInfo['EvnVizitPL_setTime'],
					'goalId' => $goalId,
					'typeId' => $typeId,
					'placeId' => $placeId,
					'initiatorId' => 1, // Самообращение пациента
					'mesId' => $mesId,
					'profileId' => $profileId,
					'visitResultId' => $visitResultId,
					'deseaseResultId' => $deseaseResultId,
					'resourceGroupId' => $resourceGroupId
				);

				//Отправка данных посещения
				$visitId = $this->sendVisit($params);

				//Сохранение идентификатора системного ресурса
				$locationId = $this->getSyncObject("MedStaffFact_Location_Evn_$EvnVizitPL_id", $EvnVizitPLInfo['MedStaffFact_id']);
				if (empty($locationId)) {
					$visit = $this->getVisit($visitId);
					if (is_object($visit) && !empty($visit->resourceGroupId)) {
						$this->saveSyncObject("MedStaffFact_Location_Evn_$EvnVizitPL_id", $EvnVizitPLInfo['MedStaffFact_id'], $visit->resourceGroupId);
					}
				}
			}

			$this->saveSyncObject('EvnVizitPL', $EvnVizitPL_id, $visitId);
		}

		return $visitId;
	}

	/**
	 * Метод для отправки ТАП в РМИС
	 */
	function syncEvnPL($EvnPL_id)
	{
		$caseId = null;
		try {
			$query = "
				select
					EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
					EVPL.EvnVizitPL_pid as \"EvnVizitPL_pid\",
					EVPL.Lpu_id as \"Lpu_id\",
					EVPL.LpuSection_id as \"LpuSection_id\",
					MSF.MedPersonal_id as \"MedPersonal_id\",
					MSF.MedStaffFact_id as \"MedStaffFact_id\",
					EVPL.Person_id as \"Person_id\",
					MSF.Staff_id as \"Staff_id\",
					PC.PersonCard_id as \"PersonCard_id\",
					EPL.EvnDirection_id as \"EvnDirection_id\"
				from
					v_EvnVizitPL EVPL
					inner join v_EvnPL EPL on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
					left join v_MedStaffFactCache MSF on MSF.MedStaffFact_id = EVPL.MedStaffFact_id
					left join lateral(
						select 
							PersonCard_id
						from
							v_PersonCard_all
						where
							PersonCard_begDate <= EVPL.EvnVizitPL_setDate
							and coalesce(PersonCard_endDate, EVPL.EvnVizitPL_setDate) >= EVPL.EvnVizitPL_setDate
							and Person_id = EVPL.Person_id
							and LpuAttachType_id = 1
						order by
							PersonCard_begDate desc
						limit 1
					) PC on true
				where EvnVizitPL_pid = :EvnPL_id
			";
			$resp = $this->queryResult($query, array('EvnPL_id' => $EvnPL_id));

			if (!empty($resp[0])) {
				// синхронизируем то что одинаково для всех посещений

				$item = $resp[0];
				// 1.4. синхронизируем пациента
				$patientId = $this->syncPatient($item['Person_id']);
				if (is_array($patientId)) {
					return $patientId;
				}

				// 1.4.1. синхронизируем документы физ. лица createDocument
				$syncDocumentsResult = $this->syncDocuments($item['Person_id']);
				if (is_array($syncDocumentsResult)) {
					return $syncDocumentsResult;
				}

				// 1.4.2. синхронизируем действующее прикрепление createPatientReg
				if (!empty($item['PersonCard_id'])) {
					$patientRegId = $this->syncPatientReg($item['PersonCard_id']);
					if (is_array($patientRegId)) {
						return $patientRegId;
					}
				}

				if (!empty($item['EvnDirection_id'])) {
					$referralId = $this->syncReferral($item['EvnDirection_id']);
					if (is_array($referralId)) {
						return $referralId;
					}
				}

				// 1.5.1. синхронизируем случай лечения sendCase
				$caseId = $this->syncCaseEvnPL($item['EvnVizitPL_pid']);
				if (is_array($caseId)) {
					return $caseId;
				}
			}

			foreach ($resp as $item) {
				// 1.1. синхронизируем отделение.
				$deparmentId = $this->syncDepartment($item['Lpu_id'], $item['LpuSection_id']);
				if (is_array($deparmentId)) {
					return $deparmentId;
				}

				// 1.2. синхронизируем должность
				$positionId = $this->syncPosition($item['Lpu_id'], $item['LpuSection_id'], $item['Staff_id']);
				if (is_array($positionId)) {
					return $positionId;
				}

				// 1.3. синхронизируем сотрудника
				$employeeId = $this->syncEmployee($item['Lpu_id'], $item['MedPersonal_id']);
				if (is_array($employeeId)) {
					return $employeeId;
				}

				// 1.3.1. синхронизируем должность сотрудника
				$employeePositionId = $this->syncEmployeePosition($item['Lpu_id'], $item['Staff_id'], $item['MedPersonal_id'], $item['MedStaffFact_id']);
				if (is_array($employeePositionId)) {
					return $employeePositionId;
				}

				// 1.3.2. синхронизируем специалиста, ведущего прием
				$locationId = $this->syncLocation($item['Lpu_id'], $item['LpuSection_id'], $item['MedStaffFact_id']);
				if (is_array($locationId)) {
					return $locationId;
				}

				/*// 1.3.3. синхронизируем образование сотрудника createEmployeeEducation TODO пока решили не делать, хотя кое что уже сделано
				$syncEmployeeEducationsResult = $this->syncEmployeeEducations($item['MedPersonal_id']);
				if (is_array($syncEmployeeEducationsResult)) {
					return $syncEmployeeEducationsResult;
				}*/

				// 1.3.4. синхронизируем сертификаты сотрудника createEmployeeCertificate TODO пока решили не делать

				// 1.3.5 синхронизируем специальности сотрудника addSpecialityToEmployee
				$syncEmployeeSpecialitiesResult = $this->syncEmployeeSpecialities($item['Lpu_id'], $item['MedPersonal_id']);

				// 1.5.2. синхронизируем посещение sendVisit
				$visitId = $this->syncVisit($item['EvnVizitPL_id']);

				// 1.5.3. синхронизируем оказанные услуги sendServiceRend
				$evnUslugasResult = $this->syncEvnUslugas($item['EvnVizitPL_id']);
			}
		} catch (Exception $e) {
			$this->saveSyncObject('SyncedEvnFail', $EvnPL_id, $caseId, true); //Сохраняем, что событие синхронизовалось с ошибкой
			$this->textlog->add("SyncEvnPL {$EvnPL_id} Exception: " . $e->getCode() . " " . $e->getMessage());
			$this->textlog->add($e->getTraceAsString());

			return array('Error_Msg' => $e->getMessage(), 'Error_Code' => $e->getCode());
		}

		$this->saveSyncObject('SyncedEvn', $EvnPL_id, $caseId, true); //Сохраняем, что все данные по событию синхронизованы

		return array('Error_Msg' => '');
	}


	/**
	 * Синхронизация случая лечения
	 */
	function syncCaseEvnPS($EvnPS_id)
	{
		$caseId = $this->getSyncObject('EvnPS', $EvnPS_id);

		// синхронизируем в любом случае
		if (true || empty($caseId)) {
			$EvnPSInfo = $this->getEvnPSInfo(array(
				'EvnPS_id' => $EvnPS_id
			));
			$EvnPSDiags = $this->getEvnPSDiags(array(
				'EvnPS_id' => $EvnPS_id
			));

			$clinicId = $this->getSyncSpr('Lpu', $EvnPSInfo['Lpu_id']);
			$individualId = $this->getSyncObject('Person', $EvnPSInfo['Person_id']);
			if (empty($individualId)) {
				throw new Exception('Не найдено физ.лицо для поиска/создания случая лечения', 400);
			}

			$fundingSourceTypeId = $this->getSyncSpr('PayType', $EvnPSInfo['PayType_id']);
			$socialGroupId = $this->getSyncSpr('SocStatus', $EvnPSInfo['SocStatus_id']);

			if (empty($caseId)) {
				// поиск случая лечения
				$cases = $this->searchCase(array(
					'caseTypeId' => 2, // Случай госпитализации
					'uid' => $EvnPSInfo['EvnPS_NumCard'],
					'patientUid' => $individualId,
					'medicalOrganizationId' => $clinicId
				));

				if (!is_array($cases)) {
					throw new Exception('Ошибка поиска случаев лечения', 500);
				}
				if (count($cases) > 0) {
					$caseId = $cases[0];
					$this->saveSyncObject('EvnPS', $EvnPS_id, $caseId);
				}
			}

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($caseId)) {
				$paymentMethodId = $this->getSyncSpr('PayMedType', $EvnPSInfo['PayMedType_id']);
				$csgId = $this->getSyncSpr('MesOld', $EvnPSInfo['Mes_sid'], null, false, true);
				$admissionTypeId = $this->getSyncSpr('PrehospType', $EvnPSInfo['PrehospType_id'], null, true);
				$admissionReasonId = $this->getSyncSpr('PrehospTrauma', $EvnPSInfo['PrehospTrauma_id']);
				$drunkennessTypeId = $this->getSyncSpr('PrehospToxic', $EvnPSInfo['PrehospToxic_id']);
				//$whoDelivered = $this->getSyncSpr('PrehospArrive', $EvnPSInfo['PrehospArrive_id']);

				$diagnosisId = $this->getRMISDiagId($EvnPSInfo['Diag_Code']);
				if (empty($diagnosisId)) {
					throw new Exception("Диагноз {$EvnPSInfo['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
				}
				$deathReasonDiagnId = $this->getRMISDiagId($EvnPSInfo['deathDiag_Code']);
				if (!empty($EvnPSInfo['deathDiag_Code']) && empty($deathReasonDiagnId)) {
					throw new Exception("Диагноз {$EvnPSInfo['deathDiag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
				}

				$referralId = null;
				if (!empty($EvnPLInfo['EvnDirection_id'])) {
					$referralId = $this->getSyncObject('EvnDirection', $EvnPSInfo['EvnDirection_id']);
					if (empty($referralId)) {
						throw new Exception('Не найдено направление для создания случая лечения', 400);
					}
				}

				$prehosStageDefId = null;
				switch (2) {
					case 'EvnPS_IsImperHosp':
						$prehosStageDefId = 1;
						break;
					case 'EvnPS_IsShortVolume':
						$prehosStageDefId = 2;
						break;
					case 'EvnPS_IsWrongCure':
						$prehosStageDefId = 3;
						break;
					case 'EvnPS_IsDiagMismatch':
						$prehosStageDefId = 4;
						break;
				}

				$timeGoneId = null;
				if (!empty($EvnPSInfo['EvnPS_TimeDesease'])) {
					if ($EvnPSInfo['Okei_Name'] == 'Час') {
						if ($EvnPSInfo['EvnPS_TimeDesease'] < 6) {
							$timeGoneId = 1;
						} else if ($EvnPSInfo['EvnPS_TimeDesease'] < 12) {
							$timeGoneId = 4;
						} else if ($EvnPSInfo['EvnPS_TimeDesease'] >= 12 && $EvnPSInfo['EvnPS_TimeDesease'] < 24) {
							$timeGoneId = 2;
						}
					} else {
						$timeGoneId = 3;
					}
				}

				$diagArray = array();
				if (is_array($EvnPSDiags)) {
					$prehospEmployeePositionId = $this->getSyncObject('MedStaffFact', $EvnPSInfo['MedStaffFact_pid']);

					foreach ($EvnPSDiags as $diag) {
						//$_doctorId = 94904;
						$_doctorId = null;
						if ($diag['DiagSetType_SysNick'] == 'priem' && !empty($prehospEmployeePositionId)) {
							$_doctorId = $prehospEmployeePositionId;
						} else if (!empty($diag['MedStaffFact_id'])) {
							$_doctor = $this->syncEmployeePositionFull(coalesce($diag['MedStaffFact_id'], $EvnPSInfo['MedStaffFact_pid']));
							$_doctorId = !empty($_doctor['employeePositionId']) ? $_doctor['employeePositionId'] : null;
						}

						$_diagnosId = $this->getRMISDiagId($diag['Diag_Code']);
						if (empty($_diagnosId)) {
							throw new Exception("Диагноз {$diag['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
						}

						$diagArray[] = array(
							'stageId' => $diag['DiagSetType_id'],
							'typeId' => $this->getSyncSpr('DiagSetClass', $diag['DiagSetClass_id']),
							'diagnosId' => $_diagnosId,
							'establishmentDate' => $diag['EvnDiagPS_setDate'],
							'doctorId' => $_doctorId,
							'main' => $diag['main']
						);
					}
				}

				$caseId = $this->sendCase(array(
					'id' => $caseId,
					'uid' => $EvnPSInfo['EvnPS_NumCard'],
					'patientUid' => $individualId,
					'medicalOrganizationId' => $clinicId,
					'caseTypeId' => 2, // Случай госпитализации в отделении
					'careLevelId' => $EvnPSInfo['MedicalCareKind_Code'],
					'fundingSourceTypeId' => $fundingSourceTypeId,
					'socialGroupId' => $socialGroupId,
					'paymentMethodId' => $paymentMethodId,
					'careRegimenId' => $EvnPSInfo['MedicalCareType_id'],
					'diagnoses' => $diagArray,
					'initGoalId' => /*$initGoalId*/ 1,
					'initTypeId' => 1, // неизвестный справочник mc_case_init_type (не удалось его получить)
					//'repeatCountId' => $repeatCountId,
					'refferalId' => $referralId,
					'caseResult' => array(
						'careRegimenId' => $EvnPSInfo['MedicalCareType_id'],
						'clinicId' => $clinicId,
						'diagnosisId' => $diagnosisId,
						'prehosStageDefId' => $prehosStageDefId,
						'deathReasonDiagnId' => $deathReasonDiagnId,
						'forCare' => false,
						'careGenderId' => $EvnPSInfo['Sex_id'],
						'careFullYears' => $EvnPSInfo['Person_Age'],
						'csgId' => $csgId,
						'admissionTypeId' => $admissionTypeId,
						'admissionReasonId' => !empty($admissionReasonId) ? $admissionReasonId : 1,
						//'admissionStateId' => 1,
						'drunkennessTypeId' => $drunkennessTypeId,
						'timeGoneId' => $timeGoneId,
						//'emergencyTeamNumber' => '',
						'createdDate' => $EvnPSInfo['EvnPS_setDate'],
						'careProvidingFormId' => empty($EvnPSInfo['EvnDirection_id']) ? 2 : 3,
						//'whoDelivered' => $whoDelivered,
						'whoDeliveredCode' => $EvnPSInfo['EvnPS_CodeConv'],
						'whoDeliveredTeamNumber' => $EvnPSInfo['EvnPS_NumConv'],
					)
				));
			}

			$this->saveSyncObject('EvnPS', $EvnPS_id, $caseId);
		}

		return $caseId;
	}

	/**
	 * Синхронизация данных дивжения
	 */
	function syncHspRecord($EvnSection_id, $hspRecordId = null)
	{
		if (empty($hspRecordId)) {
			$hspRecordId = $this->getSyncObject('EvnSection', $EvnSection_id);
		}
		$nextHspRecordId = null;

		// синхронизируем в любом случае
		if (true || empty($hspRecordId)) {
			$EvnSectionInfo = $this->getEvnSectionInfo(array(
				'EvnSection_id' => $EvnSection_id
			));
			if ($EvnSectionInfo['EvnSection_IsPriem'] == 2) {
				$EvnSectionDiags = $this->getEvnPSDiags(array(
					'EvnPS_id' => $EvnSectionInfo['EvnPS_id'],
					'onlyPrehosp' => true
				));
			} else {
				$EvnSectionDiags = $this->getEvnSectionDiags(array(
					'EvnSection_id' => $EvnSection_id
				));
			}

			$caseId = $this->getSyncObject('EvnPS', $EvnSectionInfo['EvnSection_pid']);
			if (empty($caseId)) {
				throw new Exception('Не найден случай лечения для поиска/создания записи отделения госпитализации', 400);
			}

			if (empty($hspRecordId)) {
				// поиск случая лечения
				$hspRecords = $this->searchHspRecord(array(
					'medicalCaseId' => $caseId,
					'openedFromDate' => $EvnSectionInfo['EvnSection_setDate'],
					'openedToDate' => $EvnSectionInfo['EvnSection_setDate'],
					'closedFromDate' => $EvnSectionInfo['EvnSection_disDate'],
					'closedToDate' => $EvnSectionInfo['EvnSection_disDate'],
				));

				if (!is_array($hspRecords)) {
					throw new Exception('Ошибка поиска записей отделения госпитализации', 500);
				}
				if (count($hspRecords) > 0) {
					$hspRecordId = $hspRecords[0];
					$this->saveSyncObject('EvnSection', $EvnSection_id, $hspRecordId);
				}
			}

			// отправляем случай лечения в любом случае, т.к. в нём могло что то измениться
			if (true || empty($hspRecordId)) {
				$clinicId = $this->getSyncSpr('Lpu', $EvnSectionInfo['Lpu_id']);
				$mesId = $this->getSyncSpr('MesOld', $EvnSectionInfo['Mes_id'], null, false, true);
				//$csgId = $this->getSyncSpr('MesOld', $EvnSectionInfo['Mes_sid'], null, false, true);
				$fundingSourceTypeId = $this->getSyncSpr('PayType', $EvnSectionInfo['PayType_id']);
				$departmentId = $this->getSyncObject('LpuSection', $EvnSectionInfo['LpuSection_id']);
				$employeePositionId = $this->getSyncObject('MedStaffFact', $EvnSectionInfo['MedStaffFact_id']);
				//$employeePositionId = 94904;

				//$resourceGroupId = 67671307;
				$resourceGroupId = $this->syncLocationForEvn('EvnSection', $EvnSection_id, $EvnSectionInfo['MedStaffFact_id']);
				if (empty($resourceGroupId)) {
					throw new Exception('Не найден специалист, ведущий прием, для передачи движения', 400);
				}

				$diagArray = array();
				if (is_array($EvnSectionDiags)) {
					foreach ($EvnSectionDiags as $diag) {
						$diagnosId = $this->getRMISDiagId($diag['Diag_Code']);
						if (empty($diagnosId)) {
							throw new Exception("Диагноз {$diag['Diag_Code']} отсутсвует в справочнике диагнозов РМИС.", 400);
						}
						$diagArray[] = array(
							'stageId' => $diag['DiagSetType_id'],
							'typeId' => $this->getSyncSpr('DiagSetClass', $diag['DiagSetClass_id']),
							'diagnosId' => $diagnosId,
							'establishmentDate' => $diag['EvnDiagPS_setDate'],
							'doctorId' => $employeePositionId,
							'main' => $diag['main']
						);
					}
				}

				$params = array(
					'id' => $hspRecordId,
					'caseId' => $caseId,
					'diagnoses' => $diagArray,
					'hspRecordResultId' => null,
					'deseaseResultId' => null,
					'admissionDate' => $EvnSectionInfo['EvnSection_setDate'],
					'admissionTime' => $EvnSectionInfo['EvnSection_setTime'] . ':00',
					'outcomeDate' => $EvnSectionInfo['EvnSection_disDate'],
					'outcomeTime' => !empty($EvnSectionInfo['EvnSection_disTime']) ? $EvnSectionInfo['EvnSection_disTime'] . ':00' : null,
					'deathDate' => $EvnSectionInfo['Person_deadDate'],
					'deathTime' => !empty($EvnSectionInfo['Person_deadTime']) ? $EvnSectionInfo['Person_deadTime'] . ':00' : null,
					'outcomeMedicalOrganizationId' => $clinicId,
					'outcomeDepartmentId' => null,
					'outcomeRegimenId' => $EvnSectionInfo['MedicalCareType_id'],
					'regimenId' => $EvnSectionInfo['MedicalCareType_id'],
					'mesId' => $mesId,
					//'csgId' => $csgId,
					'fundingSourceTypeId' => $fundingSourceTypeId,
					'isContinue' => $EvnSectionInfo['isContinue'],
					'departamentId' => $departmentId,
					'isAdmissionDayCounts' => true,
					'profileId' => null,
					'isSetDiagnosis' => true,
					'isContinueEditable' => false,
					'isDiagnosisCannotBeEqual' => true,
					'resourceGroupId' => $resourceGroupId
				);

				if ($EvnSectionInfo['EvnSection_IsPriem'] == 2 && $EvnSectionInfo['EvnSection_Count'] > 1 && !empty($EvnSectionInfo['nextLpuSection_id'])) {
					$params['hspRecordResultId'] = 8;        //Перевод в другое отделение
					$params['deseaseResultId'] = 17;    //Без перемен

					if (!empty($EvnSectionInfo['nextLpuSectionProfile_id'])) {
						$params['profileId'] = $this->getSyncSpr('LpuSectionProfile', $EvnSectionInfo['nextLpuSectionProfile_id']);
					}
					if (!empty($EvnSectionInfo['nextLpuSection_id'])) {
						$params['outcomeDepartmentId'] = $this->syncDepartment($EvnSectionInfo['Lpu_id'], $EvnSectionInfo['nextLpuSection_id']);
					}
				} else {
					if (!empty($EvnSectionInfo['LpuSection_oid'])) {
						//$params['outcomeDepartmentId'] = $this->getSyncObject('LpuSection', $EvnSectionInfo['LpuSection_oid']);
						$params['outcomeDepartmentId'] = $this->syncDepartment($EvnSectionInfo['Lpu_id'], $EvnSectionInfo['LpuSection_oid']);
					}
					if (!empty($EvnSectionInfo['LeaveType_id'])) {
						$params['hspRecordResultId'] = $this->getSyncSpr('LeaveType', $EvnSectionInfo['LeaveType_id']);
					}
					if (!empty($EvnSectionInfo['ResultDesease_id'])) {
						$params['deseaseResultId'] = $this->getSyncSpr('ResultDesease', $EvnSectionInfo['ResultDesease_id']);
					}
					$params['profileId'] = $this->getSyncSpr('LpuSectionProfile', $EvnSectionInfo['LpuSectionProfile_id']);
				}

				if (!empty($EvnSectionInfo['PrehospWaifRefuseCause_id'])) {
					//Отказ от госпитализции в приемном
					$params['reasonId'] = $this->getSyncSpr('PrehospWaifRefuseCause', $EvnSectionInfo['PrehospWaifRefuseCause_id'], null, false, true);
					if (empty($params['reasonId'])) {
						$params['reasonId'] = 2;    //2. Отсутвие показаний
					}
					if (!empty($params['deathDate'])) {
						$params['hspRecordResultId'] = $EvnSectionInfo['LpuUnitType_SysNick'] == 'stac' ? 47 : 15;    //106/206. Умер в приемном покое
					}

					if (empty($params['hspRecordResultId'])) {
						$params['hspRecordResultId'] = 13;    //13. Отказ от госпитализации
					}
					if (empty($params['deseaseResultId'])) {
						$params['deseaseResultId'] = $EvnSectionInfo['LpuUnitType_SysNick'] == 'stac' ? 17 : 10;    //103/203. Без перемен
					}
				}

				/*if (!empty($EvnSectionInfo['deathMedPersonal_id'])) {
					$deathDoctorId = $this->getSyncObject('MedPersonalOnLpu_'.$EvnSectionInfo['Lpu_id'], $EvnSectionInfo['deathMedPersonal_id']);
					if (!$deathDoctorId) {
						$deathDoctorId = $this->syncEmployee($EvnSectionInfo['Lpu_id'], $EvnSectionInfo['deathMedPersonal_id']);
					}
					$params['deathRecordSpecialistId'] = $deathDoctorId;
				}*/

				if (!empty($EvnSectionInfo['EvnSection_previd'])) {
					$params['previousHospitalRecordId'] = $this->getSyncObject('EvnSection', $EvnSectionInfo['EvnSection_previd']);
				}

				//Отправка данных движения
				$result = $this->sendHspRecord($params);
				$hspRecordId = $result->id;
				$nextHspRecordId = !empty($result->nextId) ? $result->nextId : null;

				//Сохранение идентификатора системеного ресурса
				$locationId = $this->getSyncObject("MedStaffFact_Location_Evn_$EvnSection_id", $EvnSectionInfo['MedStaffFact_id']);
				if (empty($locationId)) {
					$hspRecord = $this->getHspRecord($hspRecordId);
					if (is_object($hspRecord) && !empty($hspRecord->resourceGroupId)) {
						$this->saveSyncObject("MedStaffFact_Location_Evn_$EvnSection_id", $EvnSectionInfo['MedStaffFact_id'], $hspRecord->resourceGroupId);
					}
				}
			}

			$this->saveSyncObject('EvnSection', $EvnSection_id, $hspRecordId);
			if (!empty($EvnSectionInfo['nextEvnSection_id']) && !empty($nextHspRecordId)) {
				$this->saveSyncObject('EvnSection', $EvnSectionInfo['nextEvnSection_id'], $nextHspRecordId);
			}

			//Отправка КСГ как услугу
			if (!empty($EvnSectionInfo['Mes_sid'])) {
				$serviceRendMesResult = $this->syncServiceRendByMes(array(
					'Mes_id' => $EvnSectionInfo['Mes_sid'],
					'EvnSection_id' => $EvnSection_id,
					'Evn_id' => $EvnSectionInfo['EvnPS_id'],
					'EvnClass_SysNick' => 'EvnPS',
					'Evn_setDate' => $EvnSectionInfo['EvnSection_setDate'],
					'Evn_disDate' => $EvnSectionInfo['EvnSection_disDate'],
					'PayType_id' => $EvnSectionInfo['PayType_id'],
					'MedStaffFact_id' => $EvnSectionInfo['MedStaffFact_id'],
					//'Diag_id' => $EvnSectionInfo['Diag_id'],
					'Person_id' => $EvnSectionInfo['Person_id'],
					'Lpu_id' => $EvnSectionInfo['Lpu_id'],
					'LpuUnitType_SysNick' => $EvnSectionInfo['LpuUnitType_SysNick'],
				));
			}
		}

		return array(
			'hspRecordId' => $hspRecordId,
			'nextHspRecordId' => $nextHspRecordId
		);
	}

	/**
	 * Запускает синхронизацию данных по КВС
	 */
	function syncEvnPS($EvnPS_id)
	{
		$caseId = null;
		try {
			$query = "
				select
					ES.EvnSection_id as \"EvnSection_id\",
					ES.EvnSection_pid as \"EvnSection_pid\",
					ES.Lpu_id as \"Lpu_id\",
					ES.LpuSection_id as \"LpuSection_id\",
					MSF.MedPersonal_id as \"MedPersonal_id\",
					MSF.MedStaffFact_id as \"MedStaffFact_id\",
					ES.Person_id as \"Person_id\",
					MSF.Staff_id as \"Staff_id\",
					PC.PersonCard_id as \"PersonCard_id\",
					EPS.EvnDirection_id as \"EvnDirection_id\"
				from
					v_EvnSection ES
					inner join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnSection_pid
					left join v_MedStaffFactCache MSF on MSF.MedStaffFact_id = ES.MedStaffFact_id
					left join lateral(
						select 
							PersonCard_id
						from
							v_PersonCard_all
						where
							PersonCard_begDate <= ES.EvnSection_setDate
							and coalesce(PersonCard_endDate, ES.EvnSection_setDate) >= ES.EvnSection_setDate
							and Person_id = ES.Person_id
							and LpuAttachType_id = 1
						order by
							PersonCard_begDate desc
						limit 1
					) PC on true
				where
					EvnSection_pid = :EvnPS_id
					and (coalesce(ES.EvnSection_isPriem, 1) = 1 or ES.LpuSection_id is not null)
			";
			$resp = $this->queryResult($query, array('EvnPS_id' => $EvnPS_id));

			if (!empty($resp[0])) {
				// синхронизируем то что одинаково для всех посещений

				$item = $resp[0];
				// 1.4. синхронизируем пациента
				$patientId = $this->syncPatient($item['Person_id']);
				if (is_array($patientId)) {
					return $patientId;
				}

				// 1.4.1. синхронизируем документы физ. лица createDocument
				$syncDocumentsResult = $this->syncDocuments($item['Person_id']);
				if (is_array($syncDocumentsResult)) {
					return $syncDocumentsResult;
				}

				// 1.4.2. синхронизируем действующее прикрепление createPatientReg
				if (!empty($item['PersonCard_id'])) {
					$patientRegId = $this->syncPatientReg($item['PersonCard_id']);
					if (is_array($patientRegId)) {
						return $patientRegId;
					}
				}

				if (!empty($item['EvnDirection_id'])) {
					$referralId = $this->syncReferral($item['EvnDirection_id']);
					if (is_array($referralId)) {
						return $referralId;
					}
				}

				// 1.5.1. синхронизируем случай лечения sendCase
				$caseId = $this->syncCaseEvnPS($item['EvnSection_pid']);
				if (is_array($caseId)) {
					return $caseId;
				}
			}

			$nextHspRecordId = null;
			foreach ($resp as $item) {
				// 1.1. синхронизируем отделение.
				if (!empty($item['LpuSection_id'])) {
					$departmentId = $this->syncDepartment($item['Lpu_id'], $item['LpuSection_id']);
				}

				// 1.2. синхронизируем должность
				if (!empty($item['LpuSection_id']) && !empty($item['Staff_id'])) {
					$positionId = $this->syncPosition($item['Lpu_id'], $item['LpuSection_id'], $item['Staff_id']);
				}

				// 1.3. синхронизируем сотрудника
				if (!empty($item['MedPersonal_id'])) {
					$employeeId = $this->syncEmployee($item['Lpu_id'], $item['MedPersonal_id']);
				}

				// 1.3.1. синхронизируем должность сотрудника
				if (!empty($item['Staff_id']) && !empty($item['MedPersonal_id']) && !empty($item['MedStaffFact_id'])) {
					$employeePositionId = $this->syncEmployeePosition($item['Lpu_id'], $item['Staff_id'], $item['MedPersonal_id'], $item['MedStaffFact_id']);
				}

				// 1.3.2. синхронизируем специалиста, ведущего прием
				if (!empty($item['LpuSection_id']) && !empty($item['MedStaffFact_id'])) {
					$locationId = $this->syncLocation($item['Lpu_id'], $item['LpuSection_id'], $item['MedStaffFact_id']);
				}

				/*// 1.3.3. синхронизируем образование сотрудника createEmployeeEducation TODO пока решили не делать, хотя кое что уже сделано
				$syncEmployeeEducationsResult = $this->syncEmployeeEducations($item['MedPersonal_id']);
				if (is_array($syncEmployeeEducationsResult)) {
					return $syncEmployeeEducationsResult;
				}*/

				// 1.3.4. синхронизируем сертификаты сотрудника createEmployeeCertificate TODO пока решили не делать

				// 1.3.5 синхронизируем специальности сотрудника addSpecialityToEmployee
				if (!empty($item['MedPersonal_id'])) {
					$syncEmployeeSpecialitiesResult = $this->syncEmployeeSpecialities($item['Lpu_id'], $item['MedPersonal_id']);
				}

				// 1.5.2. синхронизируем запись отделения госпитализации sendHspRecord
				$resp = $this->syncHspRecord($item['EvnSection_id'], $nextHspRecordId);
				$nextHspRecordId = !empty($resp['nextHspRecordId']) ? $resp['nextHspRecordId'] : null;

				// 1.5.3. синхронизируем оказанные услуги sendServiceRend
				$evnUslugasResult = $this->syncEvnUslugas($item['EvnSection_id']);
			}
		} catch (Exception $e) {
			$this->saveSyncObject('SyncedEvnFail', $EvnPS_id, $caseId, true); //Сохраняем, что событие синхронизовалось с ошибкой
			$this->textlog->add("SyncEvnPS {$EvnPS_id} Exception: " . $e->getCode() . " " . $e->getMessage());
			$this->textlog->add($e->getTraceAsString());

			return array('Error_Msg' => $e->getMessage(), 'Error_Code' => $e->getCode());
		}

		$this->saveSyncObject('SyncedEvn', $EvnPS_id, $caseId, true); //Сохраняем, что все данные по событию синхронизованы

		return array('Error_Msg' => '');
	}

	/**
	 * Запускает синхронизацию данных по медосмотрам/диспансеризациям
	 */
	function syncEvnPLDisp($EvnPLDisp_id)
	{
		$caseId = null;
		try {
			$query = "
				select
					EVD.EvnVizitDisp_id as \"EvnVizitDisp_id\",
					EVD.EvnVizitDisp_pid as \"EvnVizitDisp_pid\",
					EVD.Lpu_id as \"Lpu_id\",
					EVD.LpuSection_id as \"LpuSection_id\",
					EVD.Person_id as \"Person_id\",
					MSF.MedPersonal_id as \"MedPersonal_id\",
					MSF.MedStaffFact_id as \"MedStaffFact_id\",
					MSF.Staff_id as \"Staff_id\",
					PC.PersonCard_id as \"PersonCard_id\",
					EVD.EvnDirection_id as \"EvnDirection_id\"
				from
					v_DopDispInfoConsent DDIC
					inner join v_EvnPLDisp EPLD on EPLD.EvnPLDisp_id = DDIC.EvnPLDisp_id
					inner join v_EvnVizitDisp EVD on EVD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_MedStaffFactCache MSF on MSF.MedStaffFact_id = EVD.MedStaffFact_id
					left join lateral(
						select
							PersonCard_id
						from
							v_PersonCard_all
						where
							PersonCard_begDate <= EPLD.EvnPLDisp_setDate
							and coalesce(PersonCard_endDate, EPLD.EvnPLDisp_setDate) >= EPLD.EvnPLDisp_setDate
							and Person_id = EVD.Person_id
							and LpuAttachType_id = 1
						order by
							PersonCard_begDate desc
					    limit 1
					) PC on true
				where
					DDIC.EvnPLDisp_id = :EvnPLDisp_id
			";
			$resp = $this->queryResult($query, array('EvnPLDisp_id' => $EvnPLDisp_id));

			if (!empty($resp[0])) {
				// синхронизируем то что одинаково для всех посещений

				$item = $resp[0];
				// 1.4. синхронизируем пациента
				$patientId = $this->syncPatient($item['Person_id']);
				if (is_array($patientId)) {
					return $patientId;
				}

				// 1.4.1. синхронизируем документы физ. лица createDocument
				$syncDocumentsResult = $this->syncDocuments($item['Person_id']);
				if (is_array($syncDocumentsResult)) {
					return $syncDocumentsResult;
				}

				// 1.4.2. синхронизируем действующее прикрепление createPatientReg
				if (!empty($item['PersonCard_id'])) {
					$patientRegId = $this->syncPatientReg($item['PersonCard_id']);
					if (is_array($patientRegId)) {
						return $patientRegId;
					}
				}

				if (!empty($item['EvnDirection_id'])) {
					$referralId = $this->syncReferral($item['EvnDirection_id']);
					if (is_array($referralId)) {
						return $referralId;
					}
				}

				// 1.5.1. синхронизируем случай лечения sendCase
				$caseId = $this->syncCaseEvnPLDisp($item['EvnVizitDisp_pid']);
				if (is_array($caseId)) {
					return $caseId;
				}

				$evnUslugasResult = $this->syncEvnUslugas($item['EvnVizitDisp_pid']);
			}

			$nextHspRecordId = null;
			foreach ($resp as $item) {
				// 1.1. синхронизируем отделение.
				if (!empty($item['LpuSection_id'])) {
					$departmentId = $this->syncDepartment($item['Lpu_id'], $item['LpuSection_id']);
				}

				// 1.2. синхронизируем должность
				if (!empty($item['LpuSection_id']) && !empty($item['Staff_id'])) {
					$positionId = $this->syncPosition($item['Lpu_id'], $item['LpuSection_id'], $item['Staff_id']);
				}

				// 1.3. синхронизируем сотрудника
				if (!empty($item['MedPersonal_id'])) {
					$employeeId = $this->syncEmployee($item['Lpu_id'], $item['MedPersonal_id']);
				}

				// 1.3.1. синхронизируем должность сотрудника
				if (!empty($item['Staff_id']) && !empty($item['MedPersonal_id']) && !empty($item['MedStaffFact_id'])) {
					$employeePositionId = $this->syncEmployeePosition($item['Lpu_id'], $item['Staff_id'], $item['MedPersonal_id'], $item['MedStaffFact_id']);
				}

				// 1.3.2. синхронизируем специалиста, ведущего прием
				if (!empty($item['LpuSection_id']) && !empty($item['MedStaffFact_id'])) {
					$locationId = $this->syncLocation($item['Lpu_id'], $item['LpuSection_id'], $item['MedStaffFact_id']);
				}

				// 1.3.4. синхронизируем сертификаты сотрудника createEmployeeCertificate TODO пока решили не делать

				// 1.3.5 синхронизируем специальности сотрудника addSpecialityToEmployee
				if (!empty($item['MedPersonal_id'])) {
					$syncEmployeeSpecialitiesResult = $this->syncEmployeeSpecialities($item['Lpu_id'], $item['MedPersonal_id']);
				}

				// 1.5.2. синхронизируем запись отделения госпитализации sendVisit
				$resp = $this->syncVisitDisp($item['EvnVizitDisp_id']);

				// 1.5.3. синхронизируем оказанные услуги sendServiceRend
				$evnUslugasResult = $this->syncEvnUslugas($item['EvnVizitDisp_id']);
			}
		} catch (Exception $e) {
			$this->saveSyncObject('SyncedEvnFail', $EvnPLDisp_id, $caseId, true); //Сохраняем, что событие синхронизовалось с ошибкой
			$this->textlog->add("SyncEvnPLDisp {$EvnPLDisp_id} Exception: " . $e->getCode() . " " . $e->getMessage());
			$this->textlog->add($e->getTraceAsString());

			return array('Error_Msg' => $e->getMessage(), 'Error_Code' => $e->getCode());
		}

		$this->saveSyncObject('SyncedEvn', $EvnPLDisp_id, $caseId, true); //Сохраняем, что все данные по событию синхронизованы

		return array('Error_Msg' => '');
	}

	/*function test() {
		echo '<pre>';
		$result = $this->syncEvnPS('730022509230324');
		print_r(array(
			$result
		));
	}*/
}