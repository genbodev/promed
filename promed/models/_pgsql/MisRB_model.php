<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * MisRB_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2015 Swan Ltd.
 * @author            Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version            15.04.2015
 *
 * @property ObjectSynchronLog_model $SyncLog_model
 * @property-read array $soapOptions настройки для подключения к soap-сервису
 */
class SoapClientExt extends SoapClient
{
	public $customXml = null;
	public $lastRequest = null;

	/**
	 * Устаналивает кастомную XML для отправки в сервис
	 */
	public function setCustomXml($customXml)
	{
		$this->customXml = $customXml;
		return $this;
	}

	/**
	 * Получение последнего запроса
	 */
	public function __getLastRequest()
	{
		return $this->lastRequest;
	}

	/**
	 * Выполнение SOAP запроса
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0)
	{
		if (!empty($this->customXml)) {
			$request = $this->customXml;
		}

		$this->lastRequest = $request;

		return parent::__doRequest($request, $location, $action, $version, $one_way);
	}
}

class MisRB_model extends swPgModel
{

	protected $_rmisConfig = array();
	protected $_soapClients = array();
	protected $_syncObjectList = array();
	protected $_syncSprList = array();
	protected $_soapOptions = null;

	protected $_execIteration = 0;
	protected $_maxExecIteration = 1;
	protected $_execIterationDelay = 300;
	protected $_idLPU = null;
	protected $lastQueryType = null;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		ini_set("default_socket_timeout", 120);

		$this->load->library('textlog', array('file' => 'MisRB_' . date('Y-m-d') . '.log'));

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('MisRB');

		$this->_rmisConfig = $this->config->item('MisRB');
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

		$this->_soapOptions = array(
			'encoding' => 'UTF-8',
			'soap_version' => SOAP_1_1,
			'exceptions' => 1, // обработка ошибок
			'trace' => 1, // трассировка
			'connection_timeout' => 5
		);

		if ($tryConnection) {
			try {
				$this->exec('emk', 'getVersion');
			} catch (Exception $e) {
				if ($e->getMessage() == 'Forbidden') {
					return array('Error_Code' => 403, 'Error_Msg' => 'Отказано в доступе к сервису МИС РБ!');
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
	function exec($serviceType, $command, $params = null, $xml = null)
	{
		$this->_execIteration++;

		$this->textlog->add("exec: {$serviceType}.{$command}, try {$this->_execIteration} of {$this->_maxExecIteration}");

		$guid = '';
		switch ($serviceType) {
			case 'patient':
				$guid = $this->_rmisConfig['PixService']['guid'];
				break;
			case 'emk':
				$guid = $this->_rmisConfig['EMKService']['guid'];
				break;
			case 'hub':
				$guid = $this->_rmisConfig['HubService']['guid'];
				break;
		}

		if (is_array($params) && array_key_exists('guid', $params)) {
			$params['guid'] = $guid;
		}

		try {
			if (!empty($xml)) {
				if (mb_strpos($xml, '<guid>???</guid>') !== false) {
					$xml = str_replace('<guid>???</guid>', '<guid>' . $guid . '</guid>', $xml);
				}
				$response = $this->getSoapClient($serviceType)->setCustomXml($xml)->$command($params);
			} else if (!empty($params)) {
				$response = $this->getSoapClient($serviceType)->$command($params);
			} else {
				$response = $this->getSoapClient($serviceType)->$command();
			}

			if (!empty($_REQUEST['getDebug'])) {
				echo "<textarea cols=150 rows=20>" . $this->getSoapClient($serviceType)->__getLastRequest() . "</textarea><br><br>";
				echo "<textarea cols=150 rows=20>" . $this->getSoapClient($serviceType)->__getLastResponse() . "</textarea><br><br>";
			}
		} catch (Exception $e) {
			$this->textlog->add("exec fail: __getLastRequest: " . $this->getSoapClient($serviceType)->__getLastRequest());
			$this->textlog->add("exec fail: __getLastResponse: " . $this->getSoapClient($serviceType)->__getLastResponse());
			if (!empty($_REQUEST['getDebug'])) {
				echo "<textarea cols=150 rows=20>" . $this->getSoapClient($serviceType)->__getLastRequest() . "</textarea><br><br>";
				echo "<textarea cols=150 rows=20>" . $this->getSoapClient($serviceType)->__getLastResponse() . "</textarea><br><br>";
			}
			$this->textlog->add("exec fail: {$serviceType}.{$command}, try {$this->_execIteration} of {$this->_maxExecIteration}. Exception: " . $e->getCode() . " " . $e->getMessage() . " " . (!empty($e->detail) ? var_export($e->detail, true) : ''));
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

		return $response;
	}

	/**
	 * @param $serviceType
	 * @return mixed
	 */
	function getSoapClient($serviceType)
	{
		if (!isset($this->_soapClients[$serviceType])) {
			switch ($serviceType) {
				case 'patient':
					$url = $this->_rmisConfig['PixService']['url'] . '?singleWsdl';
					break;
				case 'emk':
					$url = $this->_rmisConfig['EMKService']['url'] . '?singleWsdl';
					break;
				case 'hub':
					$url = $this->_rmisConfig['HubService']['url'] . '?singleWsdl';
					break;
				default:
					die('Неизвестный сервис');
			}

			if (!empty($_REQUEST['getDebug'])) {
				var_dump($url);
			}

			list($status) = @get_headers($url);
			if (empty($status) || strpos($status, '404') !== false) {
				throw new Exception("Не удалось установить соединение с сервисом", 500);
			}

			$soapOptions = $this->getSoapOptions();
			try {
				set_error_handler(array($this, 'exceptionErrorHandler'));
				$this->_soapClients[$serviceType] = new SoapClientExt($url, $soapOptions);
				restore_error_handler();
			} catch (Exception $e) {
				restore_error_handler();
				$this->textlog->add('SoapFault: ' . $e->getCode() . ' ' . $e->getMessage());
				throw new Exception("Не удалось установить соединение с сервисом", 500, $e);
			}
		}

		$this->_soapClients[$serviceType]->setCustomXml(null);

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
	function getSyncSpr($table, $id, $allowBlank = false, $withoutLink = false)
	{
		if (empty($id)) {
			return null;
		}

		// ищем в памяти
		if (isset($this->_syncSprList[$table]) && isset($this->_syncSprList[$table][$id])) {
			return $this->_syncSprList[$table][$id];
		}

		$advancedKey = "{$table}_id";

		// ищем в бд
		if ($withoutLink) {
			$query = "
				select
					mt.MIS{$table}_Code as \"code\"
				from
					r3.MIS{$table} mt
				where
					mt.{$advancedKey} = :{$advancedKey} 
				limit 1
			";
		} else {
			$query = "
				select
					mt.MIS{$table}_Code as \"code\"
				from
					r3.MIS{$table}Link mtl
					inner join r3.MIS{$table} mt on mtl.MIS{$table}_id = mt.MIS{$table}_id 
				where
					mtl.{$advancedKey} = :{$advancedKey} 
				limit 1
			";
		}

		$resp = $this->queryResult($query, array(
			$advancedKey => $id
		));

		if (!empty($resp[0]['code'])) {
			$this->_syncSprList[$table][$id] = $resp[0]['code'];
			return $resp[0]['code'];
		}

		if (!$allowBlank) {
			throw new Exception('Не найдена запись в r3.MIS' . $table . ' с идентификатором ' . $id . ' (' . $advancedKey . ')', 400);
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
	 * Получение идентификатора МО
	 */
	function getIdLpu($Lpu_id)
	{
		// return '1.2.643.5.1.13.3.25.78.118'; // это временно, т.к. тестовый сервис нормальный бурятский идешник МО воспринимать не хочет
		$resp = $this->queryResult("
			select
				PassportToken_tid as \"PassportToken_tid\"
			from
				fed.v_PassportToken
			where
				Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $Lpu_id
		));

		if (!empty($resp[0]['PassportToken_tid'])) {
			return $resp[0]['PassportToken_tid'];
		}

		return null;
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
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"Person_BirthDay\",
				s.Sex_Code as \"Sex_Code\",
				case when ua.KLCity_id is not null or ua.KLTown_Socr = 'ПГТ' then 1 else 2 end as \"IdLivingAreaType\",
				ps.SocStatus_id as \"SocStatus_id\",
				o.Org_Name as \"Org_Name\",
				ps.Person_Phone as \"Person_Phone\",
				ua.Address_Address as \"UAddress_Address\",
				pa.Address_Address as \"PAddress_Address\",
				ba.Address_Address as \"BAddress_Address\",
				ps.Person_SNILS as \"Person_SNILS\",
				dt.DocumentType_Code as \"DocumentType_Code\",
				ps.Document_Ser as \"Document_Ser\",
				ps.Document_Num as \"Document_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"Document_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"Document_endDate\",
				do.Org_Name as \"DOrg_Name\",
				ps.PolisType_id as \"PolisType_id\",
				ps.Polis_Ser as \"Polis_Ser\",
				ps.Polis_Num as \"Polis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"Polis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"Polis_endDate\",
				os.OrgSMO_f002smocod as \"OrgSMO_f002smocod\",
				os.OrgSMO_Name as \"OrgSMO_Name\",
				ost.OmsSprTerr_Code as \"OmsSprTerr_Code\"
			from
				v_PersonState PS
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_Address_all ua on ua.Address_id = ps.UAddress_id
				left join v_Address_all pa on pa.Address_id = ps.PAddress_id
				left join v_PersonBirthPlace pbp on ps.Person_id = pbp.Person_id
				left join v_Address_all ba on ba.Address_id = pbp.Address_id
				left join v_Job j on j.Job_id = ps.Job_id
				left join v_Org o on o.Org_id = j.Org_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
			where
				PS.Person_id = :Person_id
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
	 * Получение данных ТАП
	 */
	function getEvnPLInfo($data)
	{
		$params = array('EvnPL_id' => $data['EvnPL_id']);

		$query = "
			select
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Person_id as \"Person_id\",
				to_char(EPL.EvnPL_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnPL_setDate\",
				case
					when EPL.EvnPL_IsFinish = 2 then to_char(EPL.EvnPL_disDate, 'yyyy-mm-ddThh24:mi:ss')
					else null
				end as \"EvnPL_disDate\",
				EPL.EvnPL_NumCard as \"EvnPL_NumCard\",
				EVPLLAST.PayType_id as \"PayType_id\",
				EPL.Lpu_id as \"Lpu_id\",
				rdt.ResultDeseaseType_fedid as \"ResultDeseaseType_fedid\",
				EPL.ResultClass_id as \"ResultClass_id\",
				EPL.EvnDirection_id as \"EvnDirection_id\",
				EVPLLAST.MPLpu_id as \"MPLpu_id\",
				EVPLLAST.MPSex_Code as \"MPSex_Code\",
				EVPLLAST.MPPerson_BirthDay as \"MPPerson_BirthDay\",
				EVPLLAST.MPPerson_id as \"MPPerson_id\",
				EVPLLAST.MPPerson_SurName as \"MPPerson_SurName\",
				EVPLLAST.MPPerson_FirName as \"MPPerson_FirName\",
				EVPLLAST.MPPerson_SecName as \"MPPerson_SecName\",
				EVPLLAST.MPMedSpecOms_Code as \"MPMedSpecOms_Code\",
				EVPLLAST.MPPost_Code as \"MPPost_Code\",
				EVPLLAST.MPPerson_SNILS as \"MPPerson_SNILS\",
				EVPLLAST.MPPolisType_id as \"MPPolisType_id\",
				EVPLLAST.MPPolis_Ser as \"MPPolis_Ser\",
				EVPLLAST.MPPolis_Num as \"MPPolis_Num\",
				EVPLLAST.MPPolis_endDate as \"MPPolis_endDate\",
				EVPLLAST.MPPolis_begDate as \"MPPolis_begDate\",
				EVPLLAST.MPOrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				EVPLLAST.MPOrgSMO_Name as \"MPOrgSMO_Name\",
				EVPLLAST.MPOmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				EVPLLAST.MPDocumentType_Code as \"MPDocumentType_Code\",
				EVPLLAST.MPDocument_Ser as \"MPDocument_Ser\",
				EVPLLAST.MPDocument_Num as \"MPDocument_Num\",
				EVPLLAST.MPDocument_endDate as \"MPDocument_endDate\",
				EVPLLAST.MPDocument_begDate as \"MPDocument_begDate\",
				EVPLLAST.MPDOrg_Name as \"MPDOrg_Name\",
				EVPLLAST.Diag_Code as \"Diag_Code\",
				EVPLLAST.DeseaseType_id as \"DeseaseType_id\"
			from
				v_EvnPL epl
				left join v_ResultDeseaseType rdt on rdt.ResultDeseaseType_id = epl.ResultDeseaseType_id
				left join lateral (
					select
						evpl.PayType_id,
						msf.Lpu_id as MPLpu_id,
						s.Sex_Code as MPSex_Code,
						to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as MPPerson_BirthDay,
						ps.Person_id as MPPerson_id,
						rtrim(PS.Person_SurName) as MPPerson_SurName,
						rtrim(PS.Person_FirName) as MPPerson_FirName,
						rtrim(PS.Person_SecName) as MPPerson_SecName,
						mso.MedSpecOms_Code as MPMedSpecOms_Code,
						post.code as MPPost_Code,
						ps.Person_SNILS as MPPerson_SNILS,
						dt.DocumentType_Code as MPDocumentType_Code,
						ps.Document_Ser as MPDocument_Ser,
						ps.Document_Num as MPDocument_Num,
						to_char(d.Document_begDate, 'yyyy-mm-dd') as MPDocument_begDate,
						to_char(d.Document_endDate, 'yyyy-mm-dd') as MPDocument_endDate,
						do.Org_Name as MPDOrg_Name,
						ps.PolisType_id as MPPolisType_id,
						ps.Polis_Ser as MPPolis_Ser,
						ps.Polis_Num as MPPolis_Num,
						to_char(p.Polis_begDate, 'yyyy-mm-dd') as MPPolis_begDate,
						to_char(p.Polis_endDate, 'yyyy-mm-dd') as MPPolis_endDate,
						os.OrgSMO_f002smocod as MPOrgSMO_f002smocod,
						os.OrgSMO_Name as MPOrgSMO_Name,
						ost.OmsSprTerr_Code as MPOmsSprTerr_Code,
						evpl.DeseaseType_id,
						di.Diag_Code
					from
						v_EvnVizitPL evpl
						left join v_MedStaffFact msf on msf.MedStaffFact_id = evpl.MedStaffFact_id
						left join v_PersonState ps on ps.Person_id = msf.Person_id
						left join v_Document d on d.Document_id = ps.Document_id
						left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
						left join v_Org do on do.Org_id = od.Org_id
						left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
						left join v_Polis p on p.Polis_id = ps.Polis_id
						left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
						left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
						left join v_Sex s on s.Sex_id = ps.Sex_id
						left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
						left join persis.Post post on post.id = msf.Post_id
						left join v_Diag di on di.Diag_id = evpl.Diag_id
					where
						evpl.EvnVizitPL_pid = epl.EvnPL_id
					order by
						evpl.EvnVizitPL_setDate desc
					limit 1
				) EVPLLAST on true
			where
				epl.EvnPL_id = :EvnPL_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные ТАП', 400);
		}
	}

	/**
	 * Получение данных талона диспансеризации
	 */
	function getEvnPLDispDop13Info($data)
	{
		$params = array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']);

		$query = "
			select
				EPLDD13.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				EPLDD13.Person_id as \"Person_id\",
				to_char(EPLDD13.EvnPLDispDop13_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnPLDispDop13_setDate\",
				to_char(EPLDD13.EvnPLDispDop13_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnPLDispDop13_disDate\",
				EPLDD13.PayType_id as \"PayType_id\",
				EPLDD13.Lpu_id as \"Lpu_id\",
				EPLDD13.EvnPLDispDop13_IsMobile as \"EvnPLDispDop13_IsMobile\",
				EPLDD13.EvnPLDispDop13_IsDisp as \"EvnPLDispDop13_IsDisp\",
				EPLDD13.EvnPLDispDop13_IsSanator as \"EvnPLDispDop13_IsSanator\",
				EPLDD13.EvnPLDispDop13_IsTwoStage as \"EvnPLDispDop13_IsTwoStage\",
				EPLDD13.HealthKind_id as \"HealthKind_id\",
				EVDDTER.EvnVizitDispDop_setDate as \"EvnVizitDispDop_setDate\",
				EVDDTER.MPLpu_id as \"MPLpu_id\",
				EVDDTER.MPSex_Code as \"MPSex_Code\",
				EVDDTER.MPPerson_BirthDay as \"MPPerson_BirthDay\",
				EVDDTER.MPPerson_id as \"MPPerson_id\",
				EVDDTER.MPPerson_SurName as \"MPPerson_SurName\",
				EVDDTER.MPPerson_FirName as \"MPPerson_FirName\",
				EVDDTER.MPPerson_SecName as \"MPPerson_SecName\",
				EVDDTER.MPMedSpecOms_Code as \"MPMedSpecOms_Code\",
				EVDDTER.MPPost_Code as \"MPPost_Code\",
				EVDDTER.MPPerson_SNILS as \"MPPerson_SNILS\",
				EVDDTER.MPPolisType_id as \"MPPolisType_id\",
				EVDDTER.MPPolis_Ser as \"MPPolis_Ser\",
				EVDDTER.MPPolis_Num as \"MPPolis_Num\",
				EVDDTER.MPPolis_endDate as \"MPPolis_endDate\",
				EVDDTER.MPPolis_begDate as \"MPPolis_begDate\",
				EVDDTER.MPOrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				EVDDTER.MPOrgSMO_Name as \"MPOrgSMO_Name\",
				EVDDTER.MPOmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				EVDDTER.MPDocumentType_Code as \"MPDocumentType_Code\",
				EVDDTER.MPDocument_Ser as \"MPDocument_Ser\",
				EVDDTER.MPDocument_Num as \"MPDocument_Num\",
				EVDDTER.MPDocument_endDate as \"MPDocument_endDate\",
				EVDDTER.MPDocument_begDate as \"MPDocument_begDate\",
				EVDDTER.MPDOrg_Name as \"MPDOrg_Name\",
				EVDDTER.Diag_Code as \"Diag_Code\"
			from
				v_EvnPLDispDop13 EPLDD13
				left join lateral (
					select
						msf.Lpu_id as MPLpu_id,
						s.Sex_Code as MPSex_Code,
						to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as MPPerson_BirthDay,
						ps.Person_id as MPPerson_id,
						rtrim(PS.Person_SurName) as MPPerson_SurName,
						rtrim(PS.Person_FirName) as MPPerson_FirName,
						rtrim(PS.Person_SecName) as MPPerson_SecName,
						mso.MedSpecOms_Code as MPMedSpecOms_Code,
						post.code as MPPost_Code,
						ps.Person_SNILS as MPPerson_SNILS,
						dt.DocumentType_Code as MPDocumentType_Code,
						ps.Document_Ser as MPDocument_Ser,
						ps.Document_Num as MPDocument_Num,
						to_char(d.Document_begDate, 'yyyy-mm-dd') as MPDocument_begDate,
						to_char(d.Document_endDate, 'yyyy-mm-dd') as MPDocument_endDate,
						do.Org_Name as MPDOrg_Name,
						ps.PolisType_id as MPPolisType_id,
						ps.Polis_Ser as MPPolis_Ser,
						ps.Polis_Num as MPPolis_Num,
						to_char(p.Polis_begDate, 'yyyy-mm-dd') as MPPolis_begDate,
						to_char(p.Polis_endDate, 'yyyy-mm-dd') as MPPolis_endDate,
						os.OrgSMO_f002smocod as MPOrgSMO_f002smocod,
						os.OrgSMO_Name as MPOrgSMO_Name,
						ost.OmsSprTerr_Code as MPOmsSprTerr_Code,
						di.Diag_Code,
						to_char(evdd.EvnVizitDispDop_setDT, 'yyyy-mm-ddThh24:mi:ss') as EvnVizitDispDop_setDate
					from
						v_EvnVizitDispDop evdd
						inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = evdd.MedStaffFact_id
						left join v_PersonState ps on ps.Person_id = msf.Person_id
						left join v_Document d on d.Document_id = ps.Document_id
						left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
						left join v_Org do on do.Org_id = od.Org_id
						left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
						left join v_Polis p on p.Polis_id = ps.Polis_id
						left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
						left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
						left join v_Sex s on s.Sex_id = ps.Sex_id
						left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
						left join persis.Post post on post.id = msf.Post_id
						left join v_Diag di on di.Diag_id = evdd.Diag_id
					where
						evdd.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id
						and st.SurveyType_Code = '19' -- терапевт
					order by
						evdd.EvnVizitDispDop_setDate desc
					limit 1
				) EVDDTER on true
			where
				EPLDD13.EvnPLDispDop13_id = :EvnPLDispDop13_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные карты ДД', 400);
		}
	}

	/**
	 * Получение данных талона диспансеризации
	 */
	function getEvnPLDispProfInfo($data)
	{
		$params = array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']);

		$query = "
			select
				EPLDP.EvnPLDispProf_id as \"EvnPLDispProf_id\",
				EPLDP.Person_id as \"Person_id\",
				to_char(EPLDP.EvnPLDispProf_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnPLDispProf_setDate\",
				to_char(EPLDP.EvnPLDispProf_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnPLDispProf_disDate\",
				EPLDP.PayType_id as \"PayType_id\",
				EPLDP.Lpu_id as \"Lpu_id\",
				EPLDP.EvnPLDispProf_IsMobile as \"EvnPLDispProf_IsMobile\",
				EPLDP.EvnPLDispProf_IsDisp as \"EvnPLDispProf_IsDisp\",
				EPLDP.EvnPLDispProf_IsSanator as \"EvnPLDispProf_IsSanator\",
				EPLDP.HealthKind_id as \"HealthKind_id\",
				EVDDTER.EvnVizitDispDop_setDate as \"EvnVizitDispDop_setDate\",
				EVDDTER.MPLpu_id as \"MPLpu_id\",
				EVDDTER.MPSex_Code as \"MPSex_Code\",
				EVDDTER.MPPerson_BirthDay as \"MPPerson_BirthDay\",
				EVDDTER.MPPerson_id as \"MPPerson_id\",
				EVDDTER.MPPerson_SurName as \"MPPerson_SurName\",
				EVDDTER.MPPerson_FirName as \"MPPerson_FirName\",
				EVDDTER.MPPerson_SecName as \"MPPerson_SecName\",
				EVDDTER.MPMedSpecOms_Code as \"MPMedSpecOms_Code\",
				EVDDTER.MPPost_Code as \"MPPost_Code\",
				EVDDTER.MPPerson_SNILS as \"MPPerson_SNILS\",
				EVDDTER.MPPolisType_id as \"MPPolisType_id\",
				EVDDTER.MPPolis_Ser as \"MPPolis_Ser\",
				EVDDTER.MPPolis_Num as \"MPPolis_Num\",
				EVDDTER.MPPolis_endDate as \"MPPolis_endDate\",
				EVDDTER.MPPolis_begDate as \"MPPolis_begDate\",
				EVDDTER.MPOrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				EVDDTER.MPOrgSMO_Name as \"MPOrgSMO_Name\",
				EVDDTER.MPOmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				EVDDTER.MPDocumentType_Code as \"MPDocumentType_Code\",
				EVDDTER.MPDocument_Ser as \"MPDocument_Ser\",
				EVDDTER.MPDocument_Num as \"MPDocument_Num\",
				EVDDTER.MPDocument_endDate as \"MPDocument_endDate\",
				EVDDTER.MPDocument_begDate as \"MPDocument_begDate\",
				EVDDTER.MPDOrg_Name as \"MPDOrg_Name\",
				EVDDTER.Diag_Code as \"Diag_Code\"
			from
				v_EvnPLDispProf EPLDP
				left join lateral (
					select 
						msf.Lpu_id as MPLpu_id,
						s.Sex_Code as MPSex_Code,
						to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as MPPerson_BirthDay,
						ps.Person_id as MPPerson_id,
						rtrim(PS.Person_SurName) as MPPerson_SurName,
						rtrim(PS.Person_FirName) as MPPerson_FirName,
						rtrim(PS.Person_SecName) as MPPerson_SecName,
						mso.MedSpecOms_Code as MPMedSpecOms_Code,
						post.code as MPPost_Code,
						ps.Person_SNILS as MPPerson_SNILS,
						dt.DocumentType_Code as MPDocumentType_Code,
						ps.Document_Ser as MPDocument_Ser,
						ps.Document_Num as MPDocument_Num,
						to_char(d.Document_begDate, 'yyyy-mm-dd') as MPDocument_begDate,
						to_char(d.Document_endDate, 'yyyy-mm-dd') as MPDocument_endDate,
						do.Org_Name as MPDOrg_Name,
						ps.PolisType_id as MPPolisType_id,
						ps.Polis_Ser as MPPolis_Ser,
						ps.Polis_Num as MPPolis_Num,
						to_char(p.Polis_begDate, 'yyyy-mm-dd') as MPPolis_begDate,
						to_char(p.Polis_endDate, 'yyyy-mm-dd') as MPPolis_endDate,
						os.OrgSMO_f002smocod as MPOrgSMO_f002smocod,
						os.OrgSMO_Name as MPOrgSMO_Name,
						ost.OmsSprTerr_Code as MPOmsSprTerr_Code,
						di.Diag_Code,
						to_char(evdd.EvnVizitDispDop_setDT, 'yyyy-mm-ddThh24:mi:ss') as EvnVizitDispDop_setDate
					from
						v_EvnVizitDispDop evdd
						inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = evdd.MedStaffFact_id
						left join v_PersonState ps on ps.Person_id = msf.Person_id
						left join v_Document d on d.Document_id = ps.Document_id
						left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
						left join v_Org do on do.Org_id = od.Org_id
						left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
						left join v_Polis p on p.Polis_id = ps.Polis_id
						left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
						left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
						left join v_Sex s on s.Sex_id = ps.Sex_id
						left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
						left join persis.Post post on post.id = msf.Post_id
						left join v_Diag di on di.Diag_id = evdd.Diag_id
					where
						evdd.EvnVizitDispDop_pid = EPLDP.EvnPLDispProf_id
						and st.SurveyType_Code = '19' -- терапевт
					order by
						evdd.EvnVizitDispDop_setDate desc
				    limit 1
				) EVDDTER on true
			where
				EPLDP.EvnPLDispProf_id = :EvnPLDispProf_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные профосмотра', 400);
		}
	}

	/**
	 * Получение данных КВС
	 */
	function getEvnPSInfo($data)
	{
		$params = array('EvnPS_id' => $data['EvnPS_id']);

		$query = "
			select
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.Person_id as \"Person_id\",
				to_char(EPS.EvnPS_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnPS_disDate\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				ESLAST.PayType_id as \"PayType_id\",
				EPS.Lpu_id as \"Lpu_id\",
				ESLAST.ResultDesease_fedid as \"ResultDeseaseType_fedid\",
				EPS.ResultClass_id as \"ResultClass_id\",
				EPS.EvnDirection_id as \"EvnDirection_id\",
				ESLAST.MPLpu_id as \"MPLpu_id\",
				ESLAST.MPSex_Code as \"MPSex_Code\",
				ESLAST.MPPerson_BirthDay as \"MPPerson_BirthDay\",
				ESLAST.MPPerson_id as \"MPPerson_id\",
				ESLAST.MPPerson_SurName as \"MPPerson_SurName\",
				ESLAST.MPPerson_FirName as \"MPPerson_FirName\",
				ESLAST.MPPerson_SecName as \"MPPerson_SecName\",
				ESLAST.MPMedSpecOms_Code as \"MPMedSpecOms_Code\",
				ESLAST.MPPost_Code as \"MPPost_Code\",
				ESLAST.MPPerson_SNILS as \"MPPerson_SNILS\",
				ESLAST.MPPolisType_id as \"MPPolisType_id\",
				ESLAST.MPPolis_Ser as \"MPPolis_Ser\",
				ESLAST.MPPolis_Num as \"MPPolis_Num\",
				ESLAST.MPPolis_endDate as \"MPPolis_endDate\",
				ESLAST.MPPolis_begDate as \"MPPolis_begDate\",
				ESLAST.MPOrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				ESLAST.MPOrgSMO_Name as \"MPOrgSMO_Name\",
				ESLAST.MPOmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				ESLAST.MPDocumentType_Code as \"MPDocumentType_Code\",
				ESLAST.MPDocument_Ser as \"MPDocument_Ser\",
				ESLAST.MPDocument_Num as \"MPDocument_Num\",
				ESLAST.MPDocument_endDate as \"MPDocument_endDate\",
				ESLAST.MPDocument_begDate as \"MPDocument_begDate\",
				ESLAST.MPDOrg_Name as \"MPDOrg_Name\",
				ESLAST.Diag_Code as \"Diag_Code\",
				ESD.Diag_deathCode as \"Diag_deathCode\",
				EPS.LeaveType_id as \"LeaveType_id\"
			from
				v_EvnPS EPS
				left join lateral (
					select 
						ES.PayType_id,
						msf.Lpu_id as MPLpu_id,
						s.Sex_Code as MPSex_Code,
						to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as MPPerson_BirthDay,
						ps.Person_id as MPPerson_id,
						rtrim(PS.Person_SurName) as MPPerson_SurName,
						rtrim(PS.Person_FirName) as MPPerson_FirName,
						rtrim(PS.Person_SecName) as MPPerson_SecName,
						mso.MedSpecOms_Code as MPMedSpecOms_Code,
						post.code as MPPost_Code,
						ps.Person_SNILS as MPPerson_SNILS,
						dt.DocumentType_Code as MPDocumentType_Code,
						ps.Document_Ser as MPDocument_Ser,
						ps.Document_Num as MPDocument_Num,
						to_char(d.Document_begDate, 'yyyy-mm-dd') as MPDocument_begDate,
						to_char(d.Document_endDate, 'yyyy-mm-dd') as MPDocument_endDate,
						do.Org_Name as MPDOrg_Name,
						ps.PolisType_id as MPPolisType_id,
						ps.Polis_Ser as MPPolis_Ser,
						ps.Polis_Num as MPPolis_Num,
						to_char(p.Polis_begDate, 'yyyy-mm-dd') as MPPolis_begDate,
						to_char(p.Polis_endDate, 'yyyy-mm-dd') as MPPolis_endDate,
						os.OrgSMO_f002smocod as MPOrgSMO_f002smocod,
						os.OrgSMO_Name as MPOrgSMO_Name,
						ost.OmsSprTerr_Code as MPOmsSprTerr_Code,
						di.Diag_Code,
						RD.ResultDesease_fedid
					from
						v_EvnSection ES
						left join v_MedStaffFact msf on msf.MedStaffFact_id = ES.MedStaffFact_id
						left join v_PersonState ps on ps.Person_id = msf.Person_id
						left join v_Document d on d.Document_id = ps.Document_id
						left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
						left join v_Org do on do.Org_id = od.Org_id
						left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
						left join v_Polis p on p.Polis_id = ps.Polis_id
						left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
						left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
						left join v_Sex s on s.Sex_id = ps.Sex_id
						left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
						left join persis.Post post on post.id = msf.Post_id
						left join v_Diag di on di.Diag_id = ES.Diag_id
						left join v_EvnLeave EL on EL.EvnLeave_pid = ES.EvnSection_id
						left join v_EvnDie ED on ED.EvnDie_pid = ES.EvnSection_id
						left join v_EvnOtherLpu EOL on EOL.EvnOtherLpu_pid = ES.EvnSection_id
						left join v_EvnOtherStac EOST on EOST.EvnOtherStac_pid = ES.EvnSection_id
						left join v_ResultDesease RD on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id)
					where
						ES.EvnSection_pid = EPS.EvnPS_id
						and COALESCE(ES.EvnSection_IsPriem, 1) = 1
					order by
						ES.EvnSection_setDate desc
					limit 1
				) ESLAST on true
				left join lateral (
					select
						diag.Diag_Code as Diag_deathCode
					from
						v_EvnSection es
						inner join v_Diag diag on diag.Diag_id = es.Diag_id
						inner join v_LeaveType lt on lt.LeaveType_id = es.LeaveType_id
					where
						es.EvnSection_pid = eps.EvnPS_id
						and lt.LeaveType_SysNick in ('ksdie', 'ksdiepp', 'dsdie', 'dsdiepp')
				    limit 1
				) esd on true
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные КВС', 400);
		}
	}

	/**
	 * Получение данных посещений
	 */
	function getEvnVizitPLInfo($data)
	{
		$params = array('EvnPL_id' => $data['EvnPL_id']);

		$query = "
			select
				evpl.EvnVizitPL_id as \"EvnVizitPL_id\",
				evpl.ServiceType_id as \"ServiceType_id\",
				evpl.VizitType_id as \"VizitType_id\",
				to_char(EVPL.EvnVizitPL_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnVizitPL_setDate\",
				to_char(EVPL.EvnVizitPL_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnVizitPL_disDate\",
				evpl.PayType_id as \"PayType_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				diag.Diag_Code as \"Diag_Code\",
				evpl.DeseaseType_id as \"DeseaseType_id\"
			from
				v_EvnVizitPL evpl
				left join v_MedStaffFact msf on msf.MedStaffFact_id = evpl.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join v_Diag diag on diag.Diag_id = evpl.Diag_id
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
			order by
				evpl.EvnVizitPL_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных услуг
	 */
	function getEvnUslugaInfo($data)
	{
		$params = array('EvnUsluga_pid' => $data['EvnUsluga_pid']);

		$query = "
			select
				eu.EvnUsluga_id as \"EvnUsluga_id\",
				to_char(eu.EvnUsluga_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnUsluga_setDate\",
				to_char(eu.EvnUsluga_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnUsluga_disDate\",
				eu.PayType_id as \"PayType_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				ucgost.UslugaComplex_Code as \"UslugaComplex_Code\",
				ucgost.UslugaComplex_Name as \"UslugaComplex_Name\",
				eu.EvnUsluga_KolVo as \"EvnUsluga_KolVo\"
			from
				v_EvnUsluga eu
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				inner join v_UslugaComplex ucgost on ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = eu.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
			where
				eu.EvnUsluga_pid = :EvnUsluga_pid
				and eu.EvnUsluga_setDate is not null
				and COALESCE(eu.EvnUsluga_IsVizitCode, 1) = 1
				and eu.EvnClass_SysNick <> 'EvnUslugaPar' -- параклинические передаются отдельно
			order by
				eu.EvnUsluga_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение сопутствующих диагнозов
	 */
	function getEvnDiagPLSopInfo($data)
	{
		$params = array('EvnDiagPLSop_pid' => $data['EvnDiagPLSop_pid']);

		$query = "
			select
				edpls.EvnDiagPLSop_id as \"EvnDiagPLSop_id\",
				edpls.DeseaseType_id as \"DeseaseType_id\",
				diag.Diag_Code as \"Diag_Code\",
				to_char(edpls.EvnDiagPLSop_setDate, 'yyyy-mm-dd') as \"EvnDiagPLSop_setDate\",
				edpls.DiagSetClass_id as \"DiagSetClass_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\"
			from
				v_EvnDiagPLSop edpls
				inner join v_EvnVizitPL evnpl on evnpl.EvnVizitPL_id = edpls.EvnDiagPLSop_pid
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = evnpl.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join v_Diag diag on diag.Diag_id = edpls.Diag_id
			where
				edpls.EvnDiagPLSop_pid = :EvnDiagPLSop_pid
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение сопутствующих диагнозов
	 */
	function getEvnDiagPSInfo($data)
	{
		$params = array('EvnDiagPS_pid' => $data['EvnDiagPS_pid']);

		$query = "
			select
				edps.EvnDiagPS_id as \"EvnDiagPS_id\",
				diag.Diag_Code as \"Diag_Code\",
				to_char(edps.EvnDiagPS_setDate, 'yyyy-mm-dd') as \"EvnDiagPS_setDate\",
				edps.DiagSetClass_id as \"DiagSetClass_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\"
			from
				v_EvnDiagPS edps
				inner join v_EvnSection es on es.EvnSection_id = edps.EvnDiagPS_pid
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = es.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join v_Diag diag on diag.Diag_id = edps.Diag_id
			where
				edps.EvnDiagPS_pid = :EvnDiagPS_pid
				and EDPS.DiagSetType_id = 3
				and EDPS.DiagSetClass_id != 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных направлений
	 */
	function getEvnDirectionInfo($data)
	{
		$params = array('EvnDirection_rid' => $data['EvnDirection_rid']);

		$query = "
			select
				ed.EvnDirection_id as \"EvnDirection_id\",
				to_char(ed.EvnDirection_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnDirection_setDate\",
				to_char(ed.EvnDirection_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnDirection_disDate\",
				ed.Lpu_id as \"Lpu_id\",
				ed.Lpu_did as \"Lpu_did\",
				ed.DirType_id as \"DirType_id\",
				diag.Diag_Code as \"Diag_Code\",
				ed.EvnDirection_Descr as \"EvnDirection_Descr\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\"
			from
				v_EvnDirection ed
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ed.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join v_Diag diag on diag.Diag_id = ed.Diag_id
			where
				ed.EvnDirection_rid = :EvnDirection_rid
				and DirType_id in (1,2,3,5)
			order by
				ed.EvnDirection_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных ЛВН
	 */
	function getEvnStickInfo($data)
	{
		$params = array('EvnStick_pid' => $data['EvnStick_pid']);

		$query = "
			select
				es.EvnStick_id as \"EvnStick_id\",
				to_char(es.EvnStick_setDT, 'yyyy-mm-ddThh24:mi:ss') as \"EvnStick_setDate\",
				to_char(eswr.EvnStickWorkRelease_begDT, 'yyyy-mm-ddThh24:mi:ss') as \"EvnStickWorkRelease_begDate\",
				to_char(eswr.EvnStickWorkRelease_endDT, 'yyyy-mm-ddThh24:mi:ss') as \"EvnStickWorkRelease_endDate\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				es.EvnStick_Num as \"EvnStick_Num\"
			from
				v_EvnStick es
				left join lateral (
					select 
						MedStaffFact_id
					from
						v_EvnStickWorkRelease
					where
						EvnStickBase_id = es.EvnStick_id
					order by
						EvnStickWorkRelease_begDT asc
				    limit 1
				) eswr_first on true
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = COALESCE(es.MedStaffFact_id, eswr_first.MedStaffFact_id)
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join lateral (
					select
						MIN(EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begDT,
						MAX(EvnStickWorkRelease_endDT) as EvnStickWorkRelease_endDT
					from
						v_EvnStickWorkRelease
					where
						EvnStickBase_id = es.EvnStick_id
				) eswr on true
			where
				es.EvnStick_pid = :EvnStick_pid
			order by
				es.EvnStick_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных лабораторных услуг
	 */
	function getEvnUslugaLabInfo($data)
	{
		$params = array('EvnUsluga_pid' => $data['EvnUsluga_pid']);

		$query = "
			select
				eu.EvnUsluga_id as \"EvnUsluga_id\",
				to_char(eu.EvnUsluga_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnUsluga_setDate\",
				to_char(eu.EvnUsluga_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnUsluga_disDate\",
				eu.PayType_id as \"PayType_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				ucgost.UslugaComplex_Code as \"UslugaComplex_Code\",
				ucgost.UslugaComplex_Name as \"UslugaComplex_Name\",
				eu.EvnUsluga_KolVo as \"EvnUsluga_KolVo\"
			from
				v_EvnUsluga eu
				inner join v_EvnLabRequest elr on elr.EvnDirection_id = eu.EvnDirection_id -- лабораторные только
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				inner join v_UslugaComplex ucgost on ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = eu.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
			where
				eu.EvnUsluga_pid = :EvnUsluga_pid
				and eu.EvnUsluga_setDate is not null
			order by
				eu.EvnUsluga_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных консультационных услуг
	 */
	function getEvnUslugaConsultInfo($data)
	{
		$params = array('EvnUsluga_pid' => $data['EvnUsluga_pid']);

		$query = "
			select
				evpl.EvnVizitPL_id as \"EvnVizitPL_id\",
				to_char(evpl.EvnVizitPL_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnVizitPL_setDate\",
				to_char(evpl.EvnVizitPL_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnVizitPL_disDate\",
				evpl.PayType_id as \"PayType_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\"
			from
				v_EvnPrescrConsUsluga epcu
				inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = epcu.EvnPrescrConsUsluga_id 
				inner join v_EvnVizitPL evpl on evpl.EvnDirection_id = epd.EvnDirection_id -- консультативный приём
				left join v_MedStaffFact msf on msf.MedStaffFact_id = evpl.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
			where
				epcu.EvnPrescrConsUsluga_pid = :EvnUsluga_pid
			order by
				evpl.EvnVizitPL_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных эпикризов
	 */
	function getEvnXmlInfo($data)
	{
		$params = array('EvnXml_rid' => $data['EvnXml_rid']);
		$filter = "";

		if (!empty($data['notXmlTypeKind_id'])) {
			$filter .= " and xt.XmlTypeKind_id not in (" . implode(",", $data['notXmlTypeKind_id']) . ")";
		}
		if (!empty($data['XmlTypeKind_id'])) {
			$filter .= " and xt.XmlTypeKind_id in (" . implode(",", $data['XmlTypeKind_id']) . ")";
		}

		$query = "
			select
				ex.EvnXml_id as \"EvnXml_id\",
				to_char(ex.EvnXml_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"EvnXml_insDate\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				ex.EvnXml_Data as \"EvnXml_Data\",
				xtd.XmlTemplateData_Data as \"XmlTemplate_Data\"
			from
				v_EvnXml ex
				left join v_XmlTemplate xt on xt.XmlTemplate_id = ex.XmlTemplate_id
				left join v_EvnSection es on es.EvnSection_id = ex.Evn_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = es.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join XmlTemplateData xtd on xtd.XmlTemplateData_id = ex.XmlTemplateData_id
			where
				es.EvnSection_pid = :EvnXml_rid
				and ex.XmlType_id = 10
				{$filter}
			order by
				ex.EvnXml_insDT asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных о выполненных стандартах лечения
	 */
	function getMesInfo($data)
	{
		$params = array('Evn_pid' => $data['Evn_pid']);
		$filter = "";

		$query = "		
			select
				m.Mes_Code as \"Mes_Code\",
				count(e.Evn_id) as \"Mes_Count\"
			from
				v_Evn e
				left join v_EvnVizitPL ev on ev.EvnVizitPL_id = e.Evn_id
				left join v_EvnSection es on es.EvnSection_id = e.Evn_id
				left join coalesce (
					select
						m.Mes_Code
					from
						v_Mes m
					where
						m.Diag_id = COALESCE(ev.Diag_id, es.Diag_id)
						and (
							m.MesAgeGroup_id is null
							or ((
								select
									dbo.Age2(ps.Person_BirthDay, e.Evn_setDT) 
								from v_Evn e 
									inner join v_PersonState ps on ps.Person_id = e.Person_id where e.Evn_id = :Evn_pid
									limit 1
									) >= 18 and m.MesAgeGroup_id = 1)
							or ((
								select 
									dbo.Age2(ps.Person_BirthDay, e.Evn_setDT) 
								from v_Evn e 
									inner join v_PersonState ps on ps.Person_id = e.Person_id where e.Evn_id = :Evn_pid
									limit 1
									) < 18 and m.MesAgeGroup_id = 2)
						)
					limit 1	
				) m	on true
			where
				e.Evn_pid = :Evn_pid
			group by
				m.Mes_Code
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных лабораторных услуг
	 */
	function getEvnUslugaFuncInfo($data)
	{
		$params = array('EvnUsluga_pid' => $data['EvnUsluga_pid']);

		$query = "
			select
				eu.EvnUsluga_id as \"EvnUsluga_id\",
				to_char(eu.EvnUsluga_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnUsluga_setDate\",
				to_char(eu.EvnUsluga_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnUsluga_disDate\",
				eu.PayType_id as \"PayType_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				ucgost.UslugaComplex_Code as \"UslugaComplex_Code\",
				ucgost.UslugaComplex_Name as \"UslugaComplex_Name\",
				eu.EvnUsluga_KolVo as \"EvnUsluga_KolVo\"
			from
				v_EvnUsluga eu
				inner join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = eu.EvnDirection_id -- лабораторные только
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				inner join v_UslugaComplex ucgost on ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = eu.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
			where
				eu.EvnUsluga_pid = :EvnUsluga_pid
				and eu.EvnUsluga_setDate is not null
			order by
				eu.EvnUsluga_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных рецептов
	 */
	function getEvnReceptInfo($data)
	{
		$params = array('EvnRecept_pid' => $data['EvnRecept_pid']);

		$query = "
			select
				er.EvnRecept_id as \"EvnRecept_id\",
				to_char(er.EvnRecept_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnRecept_setDate\",
				to_char(er.EvnRecept_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnRecept_disDate\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				er.EvnRecept_Num as \"EvnRecept_Num\",
				er.EvnRecept_Ser as \"EvnRecept_Ser\",
				dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				CLSATC.NAME as \"CLSATC_NAME\"
			from
				v_EvnRecept er
				left join lateral (
					select
						MedSpecOms_id,
						Post_id,
						Person_id,
						Lpu_id
					from
						v_MedStaffFact
					where
						MedPersonal_id = er.MedPersonal_id
				    limit 1
				) msf on true
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = er.DrugComplexMnn_id
				left join lateral (
					select
						CLSATC.NAME
					from
						rls.v_Drug rd
						left join rls.PREP Prep on Prep.Prep_id = rd.DrugPrep_id
						LEFT JOIN rls.v_PREP_ATC PREP_ATC on PREP_ATC.PREPID = Prep.Prep_id
						LEFT JOIN rls.v_CLSATC CLSATC on CLSATC.CLSATC_ID = PREP_ATC.UNIQID
					where
						rd.DrugComplexMnn_id = dcm.DrugComplexMnn_id
					limit 1
				) CLSATC on true
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
			where
				er.EvnRecept_pid = :EvnRecept_pid
			order by
				er.EvnRecept_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных посещений по ДД
	 */
	function getEvnVizitDispDopInfo($data)
	{
		$params = array('EvnPLDisp_id' => $data['EvnPLDisp_id']);

		$query = "
			select
				EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				to_char(EVDD.EvnVizitDispDop_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnVizitDispDop_setDate\",
				to_char(EVDD.EvnVizitDispDop_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnVizitDispDop_disDate\",
				EPLD.PayType_id as \"PayType_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				diag.Diag_Code as \"Diag_Code\",
				evdd.DopDispDiagType_id as \"DopDispDiagType_id\"
			from
				v_EvnVizitDispDop EVDD
				left join v_EvnPLDisp epld on epld.EvnPLDisp_id = evdd.EvnVizitDispDop_pid
				left join v_MedStaffFact msf on msf.MedStaffFact_id = EVDD.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join v_Diag diag on diag.Diag_id = evdd.Diag_id
			where
				EVDD.EvnVizitDispDop_pid = :EvnPLDisp_id
			order by
				EVDD.EvnVizitDispDop_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных патологогистологических исследований
	 */
	function getEvnHistologicProtoInfo($data)
	{
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			select
				EHP.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				to_char(EHP.EvnHistologicProto_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnHistologicProto_setDate\",
				to_char(EHP.EvnHistologicProto_didDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnHistologicProto_didDate\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				diag.Diag_Code as \"Diag_Code\"
			from
				v_EvnHistologicProto EHP
				left join lateral (
					select 
						MedSpecOms_id,
						Post_id,
						Person_id,
						Lpu_id
					from
						v_MedStaffFact
					where
						MedPersonal_id = EHP.MedPersonal_id
				    limit 1
				) msf on true
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join v_Diag diag on diag.Diag_id = EHP.Diag_id
			where
				EHP.Person_id = :Person_id
			order by
				EHP.EvnHistologicProto_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение данных движений
	 */
	function getEvnSectionInfo($data)
	{
		$params = array('EvnPS_id' => $data['EvnPS_id']);

		$query = "
			select
				ES.EvnSection_id as \"EvnSection_id\",
				to_char(ES.EvnSection_setDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnSection_setDate\",
				to_char(ES.EvnSection_disDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnSection_disDate\",
				ES.PayType_id as \"PayType_id\",
				msf.Lpu_id as \"MPLpu_id\",
				s.Sex_Code as \"MPSex_Code\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"MPPerson_BirthDay\",
				ps.Person_id as \"MPPerson_id\",
				rtrim(PS.Person_SurName) as \"MPPerson_SurName\",
				rtrim(PS.Person_FirName) as \"MPPerson_FirName\",
				rtrim(PS.Person_SecName) as \"MPPerson_SecName\",
				mso.MedSpecOms_Code as \"MPMedSpecOms_Code\",
				post.code as \"MPPost_Code\",
				ps.Person_SNILS as \"MPPerson_SNILS\",
				dt.DocumentType_Code as \"MPDocumentType_Code\",
				ps.Document_Ser as \"MPDocument_Ser\",
				ps.Document_Num as \"MPDocument_Num\",
				to_char(d.Document_begDate, 'yyyy-mm-dd') as \"MPDocument_begDate\",
				to_char(d.Document_endDate, 'yyyy-mm-dd') as \"MPDocument_endDate\",
				do.Org_Name as \"MPDOrg_Name\",
				ps.PolisType_id as \"MPPolisType_id\",
				ps.Polis_Ser as \"MPPolis_Ser\",
				ps.Polis_Num as \"MPPolis_Num\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"MPPolis_begDate\",
				to_char(p.Polis_endDate, 'yyyy-mm-dd') as \"MPPolis_endDate\",
				os.OrgSMO_f002smocod as \"MPOrgSMO_f002smocod\",
				os.OrgSMO_Name as \"MPOrgSMO_Name\",
				ost.OmsSprTerr_Code as \"MPOmsSprTerr_Code\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				ls.LpuSection_Code as \"LpuSection_Code\",
				cast(lsbp.LpuSectionBedProfile_Code as int) as \"LpuSectionBedProfile_Code\",
				diag.Diag_Code as \"Diag_Code\"
			from
				v_EvnSection ES
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ES.MedStaffFact_id
				left join v_PersonState ps on ps.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				left join v_Org do on do.Org_id = od.Org_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = p.OmsSprTerr_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join persis.Post post on post.id = msf.Post_id
				left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				left join v_Diag diag on diag.Diag_id = es.Diag_id
				left join lateral (
					select 
						bs.LpuSectionBedProfile_Code
					from
						v_LpuSectionBedProfileLink ss
						inner join v_LpuSectionBedProfile bs ON bs.LpuSectionBedProfile_id = ss.LpuSectionBedProfile_id
					where
						ss.LpuSectionProfile_id = es.LpuSectionProfile_id
					order by
						case
							when bs.LpuSectionBedProfile_IsChild = 2 and dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) < 18 then 0 -- если < 18 лет и детский профиль, то берём его в первую очередь
							when bs.LpuSectionBedProfile_IsChild = 1 and dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) >= 18 then 0 -- если >= 18 лет и не детский профиль, то берём его в первую очередь
							else 1 -- иначе берём первый попавшийся
						end asc
				    limit 1
				) lsbp on true
			where
				ES.EvnSection_pid = :EvnPS_id
				and COALESCE(ES.EvnSection_IsPriem, 1) = 1
			order by
				ES.EvnSection_setDate asc
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Получение льгот по человеку
	 */
	function getPersonPrivilege($data)
	{
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			select
				to_char(pp.PersonPrivilege_begDate, 'yyyy-mm-dd') as \"PersonPrivilege_begDate\",
				to_char(pp.PersonPrivilege_endDate, 'yyyy-mm-dd') as \"PersonPrivilege_endDate\",
				mpt.MISPrivilegeType_Code
			from
				v_PersonPrivilege pp
				inner join r3.MISPrivilegeType mpt on mpt.PrivilegeType_id = pp.PrivilegeType_id
			where
				PP.Person_id = :Person_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp)) {
			return $resp;
		}

		return array();
	}

	/**
	 * Синхронизация пациента
	 */
	function syncPerson($Person_id)
	{
		$this->textlog->add("syncPerson: " . $Person_id);

		$patientId = $this->getSyncObject('Person', $Person_id);

		if (true /* && empty($patientId) */) {
			$personInfo = $this->getPersonInfo(array(
				'Person_id' => $Person_id
			));

			$patientParams = array(
				'guid' => '???',
				'idLPU' => $this->_idLPU,
				'patient' => array(
					'BirthDate' => $personInfo['Person_BirthDay'],
					'FamilyName' => $personInfo['Person_SurName'],
					'GivenName' => $personInfo['Person_FirName'],
					'IdLivingAreaType' => $personInfo['IdLivingAreaType'],
					'IdPatientMIS' => $personInfo['Person_id'],
					'MiddleName' => $personInfo['Person_SecName'],
					'Sex' => $personInfo['Sex_Code'],
					'SocialStatus' => $this->getSyncSpr('SocStatus', $personInfo['SocStatus_id']),
					'Documents' => array(),
					'Addresses' => array(),
					'ContactDto' => array(
						'IdContactType' => 1,
						'ContactValue' => $personInfo['Person_Phone']
					)
				)
			);

			if (!empty($personInfo['Person_SNILS'])) {
				$patientParams['patient']['Documents'][] = array(
					'IdDocumentType' => 223,
					'DocS' => '',
					'DocN' => $personInfo['Person_SNILS'],
					'ProviderName' => 'Не указан'
				);
			}

			if (!empty($personInfo['PolisType_id']) && in_array($personInfo['PolisType_id'], array(1, 4))) {
				$patientParams['patient']['Documents'][] = array(
					'IdDocumentType' => $personInfo['PolisType_id'] == 1 ? 226 : 228,
					'DocS' => $personInfo['Polis_Ser'],
					'DocN' => $personInfo['Polis_Num'],
					'ExpiredDate' => $personInfo['Polis_endDate'],
					'IssuedDate' => $personInfo['Polis_begDate'],
					'IdProvider' => $personInfo['OrgSMO_f002smocod'],
					'ProviderName' => !empty($personInfo['OrgSMO_Name']) ? $personInfo['OrgSMO_Name'] : 'Не указан',
					'RegionCode' => $personInfo['OmsSprTerr_Code']
				);
			}

			if (!empty($personInfo['DocumentType_Code'])) {
				$patientParams['patient']['Documents'][] = array(
					'IdDocumentType' => $personInfo['DocumentType_Code'] == 14 ? 14 : 18,
					'DocS' => $personInfo['Document_Ser'],
					'DocN' => $personInfo['Document_Num'],
					'ExpiredDate' => $personInfo['Document_endDate'],
					'IssuedDate' => $personInfo['Document_begDate'],
					'ProviderName' => !empty($personInfo['DOrg_Name']) ? $personInfo['DOrg_Name'] : 'Не указан'
				);
			}

			if (!empty($personInfo['UAddress_Address'])) {
				$patientParams['patient']['Addresses'][] = array(
					'IdAddressType' => 1,
					'StringAddress' => $personInfo['UAddress_Address']
				);
			}

			if (!empty($personInfo['PAddress_Address'])) {
				$patientParams['patient']['Addresses'][] = array(
					'IdAddressType' => 2,
					'StringAddress' => $personInfo['PAddress_Address']
				);
			}

			if (!empty($personInfo['BAddress_Address'])) {
				$patientParams['patient']['Bddresses'][] = array(
					'IdAddressType' => 3,
					'StringAddress' => $personInfo['BAddress_Address']
				);
			}

			if (!empty($personInfo['Org_Name'])) {
				$patientParams['patient']['Job']['CompanyName'] = $personInfo['Org_Name'];
			}

			$personPrivilege = $this->getPersonPrivilege(array(
				'Person_id' => $Person_id
			));

			if (!empty($personPrivilege[0])) {
				$patientParams['patient']['Privilege'] = array(
					'DateStart' => $personPrivilege[0]['PersonPrivilege_begDate'],
					'DateEnd' => !empty($personPrivilege[0]['PersonPrivilege_endDate']) ? $personPrivilege[0]['PersonPrivilege_endDate'] : '2999-01-01', // обязательное поле, которое не может быть пустым
					'IdPrivilegeType' => $personPrivilege[0]['MISPrivilegeType_Code']
				);
			}

			if (!empty($patientId)) {
				$function = 'UpdatePatient'; // обновление данных о пациенте
			} else {
				$function = 'AddPatient'; // передача данных о новом пациенте
			}

			try {
				$this->exec('patient', $function, $patientParams);
			} catch (Exception $e) {
				if (!empty($e->detail->RequestFault->ErrorCode) && $e->detail->RequestFault->ErrorCode == 23) {
					// попытка добавления повторного пациента, пропускаем дальше
				} else {
					throw $e;
				}
			}

			$patientId = -1; // идентификатор никакой не возвращается.
			$this->saveSyncObject('Person', $Person_id, $patientId);
		}

		return $patientId;
	}

	/**
	 * Синхронизация ТАП
	 */
	function syncEvnPL($EvnPL_id)
	{
		$this->lastQueryType = 'syncEvnPL';
		$this->textlog->add("syncEvnPL: " . $EvnPL_id);

		$caseId = $this->getSyncObject('EvnPL', $EvnPL_id);

		if (true /* && empty($caseId) */) {
			$evnPLInfo = $this->getEvnPLInfo(array(
				'EvnPL_id' => $EvnPL_id
			));

			if (!empty($evnPLInfo['Person_id'])) {
				$this->syncPerson($evnPLInfo['Person_id']);
			}

			$caseParams = array(
				'guid' => '???',
				'OpenDate' => $evnPLInfo['EvnPL_setDate'],
				'CloseDate' => $evnPLInfo['EvnPL_disDate'],
				'HistoryNumber' => $evnPLInfo['EvnPL_NumCard'],
				'IdCaseMis' => $evnPLInfo['EvnPL_id'],
				'IdPaymentType' => $this->getSyncSpr('PayType', $evnPLInfo['PayType_id']),
				'Confidentiality' => 1, // todo по ТЗ надо смотреть ограничение доступа, что весьма сомнительное решение
				'DoctorConfidentiality' => 1, // todo по ТЗ надо смотреть ограничение доступа, что весьма сомнительное решение
				'CuratorConfidentiality' => 1, // todo по ТЗ надо смотреть ограничение доступа, что весьма сомнительное решение
				'IdLpu' => $this->getIdLpu($evnPLInfo['Lpu_id']),
				'IdCaseResult' => $this->getSyncSpr('ResultDeseaseType', $evnPLInfo['ResultDeseaseType_fedid']),
				'Comment' => 'Амбулаторный случай',
				'IdCaseType' => 2, // Амбулаторный случай
				'IdPatientMis' => $evnPLInfo['Person_id'],
				'IdAmbResult' => $this->getSyncSpr('ResultClass', $evnPLInfo['ResultClass_id']),
				'IsActive' => !empty($evnPLInfo['EvnDirection_id']) ? "true" : "false",
				'DoctorInCharge/IdLpu' => $this->getIdLpu($evnPLInfo['MPLpu_id']),
				'DoctorInCharge/IdSpeciality' => $evnPLInfo['MPMedSpecOms_Code'],
				'DoctorInCharge/IdPosition' => $evnPLInfo['MPPost_Code'],
				'DoctorInCharge/Sex' => $evnPLInfo['MPSex_Code'],
				'DoctorInCharge/Birthdate' => $evnPLInfo['MPPerson_BirthDay'],
				'DoctorInCharge/IdPersonMis' => $evnPLInfo['MPPerson_id'],
				'DoctorInCharge/FamilyName' => $evnPLInfo['MPPerson_SurName'],
				'DoctorInCharge/GivenName' => $evnPLInfo['MPPerson_FirName'],
				'DoctorInCharge/MiddleName' => $evnPLInfo['MPPerson_SecName'],
				'DoctorInCharge/Documents' => array(),
				'Authenticator/IdLpu' => $this->getIdLpu($evnPLInfo['MPLpu_id']),
				'Authenticator/IdSpeciality' => $evnPLInfo['MPMedSpecOms_Code'],
				'Authenticator/IdPosition' => $evnPLInfo['MPPost_Code'],
				'Authenticator/Sex' => $evnPLInfo['MPSex_Code'],
				'Authenticator/Birthdate' => $evnPLInfo['MPPerson_BirthDay'],
				'Authenticator/IdPersonMis' => $evnPLInfo['MPPerson_id'],
				'Authenticator/FamilyName' => $evnPLInfo['MPPerson_SurName'],
				'Authenticator/GivenName' => $evnPLInfo['MPPerson_FirName'],
				'Authenticator/MiddleName' => $evnPLInfo['MPPerson_SecName'],
				'Authenticator/Documents' => array(),
				'Author/IdLpu' => $this->getIdLpu($evnPLInfo['MPLpu_id']),
				'Author/IdSpeciality' => $evnPLInfo['MPMedSpecOms_Code'],
				'Author/IdPosition' => $evnPLInfo['MPPost_Code'],
				'Author/Sex' => $evnPLInfo['MPSex_Code'],
				'Author/Birthdate' => $evnPLInfo['MPPerson_BirthDay'],
				'Author/IdPersonMis' => $evnPLInfo['MPPerson_id'],
				'Author/FamilyName' => $evnPLInfo['MPPerson_SurName'],
				'Author/GivenName' => $evnPLInfo['MPPerson_FirName'],
				'Author/MiddleName' => $evnPLInfo['MPPerson_SecName'],
				'Author/Documents' => array(),
				'Steps' => array()
			);

			$documents = array();

			if (!empty($evnPLInfo['MPPerson_SNILS'])) {
				$doc = array(
					'IdDocumentType' => 223,
					'DocS' => '',
					'DocN' => $evnPLInfo['MPPerson_SNILS'],
					'ExpiredDate' => null,
					'IssuedDate' => null,
					'ProviderName' => 'Не указан'
				);
				$documents[] = $doc;
			}

			if (!empty($evnPLInfo['MPPolisType_id']) && in_array($evnPLInfo['MPPolisType_id'], array(1, 4))) {
				$doc = array(
					'IdDocumentType' => $evnPLInfo['MPPolisType_id'] == 1 ? 226 : 228,
					'DocS' => $evnPLInfo['MPPolis_Ser'],
					'DocN' => $evnPLInfo['MPPolis_Num'],
					'ExpiredDate' => $evnPLInfo['MPPolis_endDate'],
					'IssuedDate' => $evnPLInfo['MPPolis_begDate'],
					'IdProvider' => $evnPLInfo['MPOrgSMO_f002smocod'],
					'ProviderName' => !empty($evnPLInfo['MPOrgSMO_Name']) ? $evnPLInfo['MPOrgSMO_Name'] : 'Не указан',
					'RegionCode' => $evnPLInfo['MPOmsSprTerr_Code']
				);
				$documents[] = $doc;
			}

			if (!empty($evnPLInfo['MPDocumentType_Code'])) {
				$doc = array(
					'IdDocumentType' => $evnPLInfo['MPDocumentType_Code'] == 14 ? 14 : 18,
					'DocS' => $evnPLInfo['MPDocument_Ser'],
					'DocN' => $evnPLInfo['MPDocument_Num'],
					'ExpiredDate' => $evnPLInfo['MPDocument_endDate'],
					'IssuedDate' => $evnPLInfo['MPDocument_begDate'],
					'ProviderName' => !empty($evnPLInfo['MPDOrg_Name']) ? $evnPLInfo['MPDOrg_Name'] : 'Не указан'
				);
				$documents[] = $doc;
			}

			$caseParams['DoctorInCharge/Documents'] = $documents;
			$caseParams['Authenticator/Documents'] = $documents;
			$caseParams['Author/Documents'] = $documents;

			$evnVizitPLInfos = $this->getEvnVizitPLInfo(array(
				'EvnPL_id' => $EvnPL_id
			));

			// встроенным PHP-SOAP решением отправить случай не получается, вставляем данные в шаблон XML и отправляем готовую XML
			$this->load->library('parser');

			foreach ($evnVizitPLInfos as $evnVizitPLInfo) {
				$step = array(
					'DateStart' => $evnVizitPLInfo['EvnVizitPL_setDate'],
					'DateEnd' => !empty($evnVizitPLInfo['EvnVizitPL_disDate']) ? $evnVizitPLInfo['EvnVizitPL_disDate'] : $evnVizitPLInfo['EvnVizitPL_setDate'],
					'IdStepMis' => $evnVizitPLInfo['EvnVizitPL_id'],
					'IdPaymentType' => $this->getSyncSpr('PayType', $evnVizitPLInfo['PayType_id']),
					'IdVisitPlace' => $this->getSyncSpr('ServiceType', $evnVizitPLInfo['ServiceType_id'], false, true),
					'IdVisitPurpose' => $this->getSyncSpr('VizitType', $evnVizitPLInfo['VizitType_id'], false, true),
					'Doctor/IdLpu' => $this->getIdLpu($evnVizitPLInfo['MPLpu_id']),
					'Doctor/IdSpeciality' => $evnVizitPLInfo['MPMedSpecOms_Code'],
					'Doctor/IdPosition' => $evnVizitPLInfo['MPPost_Code'],
					'Doctor/Sex' => $evnVizitPLInfo['MPSex_Code'],
					'Doctor/Birthdate' => $evnVizitPLInfo['MPPerson_BirthDay'],
					'Doctor/IdPersonMis' => $evnVizitPLInfo['MPPerson_id'],
					'Doctor/FamilyName' => $evnVizitPLInfo['MPPerson_SurName'],
					'Doctor/GivenName' => $evnVizitPLInfo['MPPerson_FirName'],
					'Doctor/MiddleName' => $evnVizitPLInfo['MPPerson_SecName'],
					'Doctor/Documents' => array()
				);

				$evpl_documents = array();

				if (!empty($evnVizitPLInfo['MPPerson_SNILS'])) {
					$doc = array(
						'IdDocumentType' => 223,
						'DocS' => '',
						'DocN' => $evnVizitPLInfo['MPPerson_SNILS'],
						'ExpiredDate' => null,
						'IssuedDate' => null,
						'ProviderName' => 'Не указан'
					);
					$evpl_documents[] = $doc;
				}

				if (!empty($evnVizitPLInfo['MPPolisType_id']) && in_array($evnVizitPLInfo['MPPolisType_id'], array(1, 4))) {
					$doc = array(
						'IdDocumentType' => $evnVizitPLInfo['MPPolisType_id'] == 1 ? 226 : 228,
						'DocS' => $evnVizitPLInfo['MPPolis_Ser'],
						'DocN' => $evnVizitPLInfo['MPPolis_Num'],
						'ExpiredDate' => $evnVizitPLInfo['MPPolis_endDate'],
						'IssuedDate' => $evnVizitPLInfo['MPPolis_begDate'],
						'IdProvider' => $evnVizitPLInfo['MPOrgSMO_f002smocod'],
						'ProviderName' => !empty($evnVizitPLInfo['MPOrgSMO_Name']) ? $evnVizitPLInfo['MPOrgSMO_Name'] : 'Не указан',
						'RegionCode' => $evnVizitPLInfo['MPOmsSprTerr_Code']
					);
					$evpl_documents[] = $doc;
				}

				if (!empty($evnVizitPLInfo['MPDocumentType_Code'])) {
					$doc = array(
						'IdDocumentType' => $evnVizitPLInfo['MPDocumentType_Code'] == 14 ? 14 : 18,
						'DocS' => $evnVizitPLInfo['MPDocument_Ser'],
						'DocN' => $evnVizitPLInfo['MPDocument_Num'],
						'ExpiredDate' => $evnVizitPLInfo['MPDocument_endDate'],
						'IssuedDate' => $evnVizitPLInfo['MPDocument_begDate'],
						'ProviderName' => !empty($evnVizitPLInfo['MPDOrg_Name']) ? $evnVizitPLInfo['MPDOrg_Name'] : 'Не указан'
					);
					$evpl_documents[] = $doc;
				}

				$step['Doctor/Documents'] = $evpl_documents;

				$step['MedRecords'] = "";
				// ClinicMainDiagnosis (основной диагноз)
				$step['MedRecords'] .= $this->parser->parse('export_xml/misrb_clinicmaindiagnosis', array(
					'IdDiseaseType' => $this->getSyncSpr('DeseaseType', $evnVizitPLInfo['DeseaseType_id'], false, true),
					'DiagnosedDate' => $evnVizitPLInfo['EvnVizitPL_setDate'],
					'IdDiagnosisType' => 1, // Основной
					'Comment' => "Основной диагноз",
					'IdTraumaType' => '',
					'MkbCode' => $evnVizitPLInfo['Diag_Code'],
					'IdLpu' => $this->getIdLpu($evnVizitPLInfo['MPLpu_id']),
					'IdSpeciality' => $evnVizitPLInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnVizitPLInfo['MPPost_Code'],
					'Sex' => $evnVizitPLInfo['MPSex_Code'],
					'Birthdate' => $evnVizitPLInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnVizitPLInfo['MPPerson_id'],
					'FamilyName' => $evnVizitPLInfo['MPPerson_SurName'],
					'GivenName' => $evnVizitPLInfo['MPPerson_FirName'],
					'MiddleName' => $evnVizitPLInfo['MPPerson_SecName'],
					'Documents' => $evpl_documents
				), true);
				// ClinicMainDiagnosis (сопутствующее диагнозы)
				$step['MedRecords'] .= $this->getClinicMainDiagnosis(array(
					'EvnVizitPL_id' => $evnVizitPLInfo['EvnVizitPL_id']
				));
				// Service (данные о выполненных услугах)
				$step['MedRecords'] .= $this->getService(array(
					'EvnUsluga_pid' => $evnVizitPLInfo['EvnVizitPL_id']
				));
				// AppointedMedication (данные о выписанных рецептах)
				$step['MedRecords'] .= $this->getAppointedMedication(array(
					'EvnRecept_pid' => $evnVizitPLInfo['EvnVizitPL_id']
				));
				// LaboratoryReport (Лабораторные исследования)
				$step['MedRecords'] .= $this->getLaboratoryReport(array(
					'EvnUsluga_pid' => $evnVizitPLInfo['EvnVizitPL_id']
				));
				// PacsResult (Инструментальное исследование)
				$step['MedRecords'] .= $this->getPacsResult(array(
					'EvnUsluga_pid' => $evnVizitPLInfo['EvnVizitPL_id']
				));

				$caseParams['Steps'][] = $step;
			}

			$caseParams['MedRecords'] = "";
			// ClinicMainDiagnosis (основной диагноз, сопутствующее диагнозы) осмотра терапевта / педиатра / ВОП
			if (!empty($evnPLInfo['EvnPL_disDate'])) {
				$caseParams['MedRecords'] .= $this->parser->parse('export_xml/misrb_clinicmaindiagnosis', array(
					'IdDiseaseType' => $this->getSyncSpr('DeseaseType', $evnPLInfo['DeseaseType_id'], false, true),
					'DiagnosedDate' => $evnPLInfo['EvnPL_disDate'],
					'IdDiagnosisType' => 1, // Основной
					'Comment' => "Основной диагноз",
					'IdTraumaType' => '',
					'MkbCode' => $evnPLInfo['Diag_Code'],
					'IdLpu' => $this->getIdLpu($evnPLInfo['MPLpu_id']),
					'IdSpeciality' => $evnPLInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnPLInfo['MPPost_Code'],
					'Sex' => $evnPLInfo['MPSex_Code'],
					'Birthdate' => $evnPLInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnPLInfo['MPPerson_id'],
					'FamilyName' => $evnPLInfo['MPPerson_SurName'],
					'GivenName' => $evnPLInfo['MPPerson_FirName'],
					'MiddleName' => $evnPLInfo['MPPerson_SecName'],
					'Documents' => $documents
				), true);
			}
			// SickList (ЛВН)
			$caseParams['MedRecords'] .= $this->getSickList(array(
				'EvnStick_pid' => $evnPLInfo['EvnPL_id']
			));
			// LaboratoryReport (Лабораторные исследования)
			$caseParams['MedRecords'] .= $this->getLaboratoryReport(array(
				'EvnUsluga_pid' => $evnPLInfo['EvnPL_id']
			));
			// PacsResult (Инструментальное исследование)
			$caseParams['MedRecords'] .= $this->getPacsResult(array(
				'EvnUsluga_pid' => $evnPLInfo['EvnPL_id']
			));
			// Referral (данные об электронном направлении)
			$caseParams['MedRecords'] .= $this->getReferral(array(
				'EvnDirection_rid' => $evnPLInfo['EvnPL_id']
			));
			// TFomsInfo (данные о выполненных МЭС)
			$caseParams['MedRecords'] .= $this->getTFomsInfo(array(
				'Evn_pid' => $evnPLInfo['EvnPL_id']
			));
			// ConsultNote (Консультационные услуги)
			$caseParams['MedRecords'] .= $this->getConsultNote(array(
				'EvnUsluga_pid' => $evnPLInfo['EvnPL_id']
			));

			$template = 'export_xml/misrb_evnpl';
			if (!empty($caseParams['CloseDate'])) {
				// передача данных о закрытом случае
				if (!empty($caseId)) {
					$caseParams['function'] = 'UpdateCase';
				} else {
					$caseParams['function'] = 'AddCase';
				}
				$caseParams['casedto'] = 'caseDto';
			} else {
				if (!empty($caseId)) {
					$template .= '_addstep';
					// передаём каждое посещение
					foreach ($caseParams['Steps'] as $step) {
						$step['guid'] = $caseParams['guid'];
						$step['IdLpu'] = $caseParams['IdLpu'];
						$step['IdPatientMis'] = $caseParams['IdPatientMis'];
						$step['IdCaseMis'] = $caseParams['IdCaseMis'];
						$xml = $this->parser->parse($template, $step, true);
						// удаляем пустые теги
						$xml = preg_replace('/<.*?><\/.*?>/ui', '', $xml);

						try {
							$this->exec('emk', 'AddStepToCase', null, $xml);
						} catch (Exception $e) {
							if (!empty($e->detail->RequestFault->ErrorCode) && $e->detail->RequestFault->ErrorCode == 31) {
								// Случай обслуживания уже существует, пропускаем дальше
							} else {
								throw $e;
							}
						}
					}
					return -1;
				} else {
					// передача данных о новом случае
					$caseParams['function'] = 'CreateCase';
					$caseParams['Comment'] = '';
					$caseParams['casedto'] = 'createCaseDto';
				}
			}

			$xml = $this->parser->parse($template, $caseParams, true);
			// удаляем пустые теги
			$xml = preg_replace('/<.*?><\/.*?>/ui', '', $xml);
			/*if (preg_match_all('/<.*?><\/.*?>/ui', $xml, $matches)) {
				foreach($matches[0] as $match) {
					if (mb_strpos($match, 'i:nil="true"') == false) {
						$xml = str_replace($match, '', $xml);
					}
				}
			}*/

			try {
				$this->exec('emk', $caseParams['function'], null, $xml);
			} catch (Exception $e) {
				if (!empty($e->detail->RequestFault->ErrorCode) && $e->detail->RequestFault->ErrorCode == 31) {
					// Случай обслуживания уже существует, пропускаем дальше
				} else {
					throw $e;
				}
			}

			$caseId = -1; // идентификатор никакой не возвращается.
			$this->saveSyncObject('EvnPL', $EvnPL_id, $caseId);
		}

		return $caseId;
	}

	/**
	 * Синхронизация карты диспансеризации EvnPLDispDop13
	 */
	function syncEvnPLDispDop13($EvnPLDispDop13_id)
	{
		$this->lastQueryType = 'syncEvnPLDispDop13';
		$this->textlog->add("syncEvnPLDispDop13: " . $EvnPLDispDop13_id);

		$caseId = $this->getSyncObject('EvnPLDispDop13', $EvnPLDispDop13_id);

		if (true /* && empty($caseId) */) {
			$evnPLDispDop13Info = $this->getEvnPLDispDop13Info(array(
				'EvnPLDispDop13_id' => $EvnPLDispDop13_id
			));

			if (!empty($evnPLDispDop13Info['Person_id'])) {
				$this->syncPerson($evnPLDispDop13Info['Person_id']);
			}

			$caseParams = array(
				'guid' => '???', // заполнится позже, при отправке.
				'OpenDate' => $evnPLDispDop13Info['EvnPLDispDop13_setDate'],
				'CloseDate' => $evnPLDispDop13Info['EvnPLDispDop13_disDate'],
				'HistoryNumber' => $evnPLDispDop13Info['EvnPLDispDop13_id'],
				'IdCaseMis' => $evnPLDispDop13Info['EvnPLDispDop13_id'],
				'IdPaymentType' => $this->getSyncSpr('PayType', $evnPLDispDop13Info['PayType_id']),
				'Confidentiality' => 1, // 1. Не ограничен
				'DoctorConfidentiality' => 1, // 1. Не ограничен
				'CuratorConfidentiality' => 1, // 1. Не ограничен
				'IdLpu' => $this->getIdLpu($evnPLDispDop13Info['Lpu_id']),
				'IdCaseResult' => 3, // Выгружать «3. Без перемен»
				'Comment' => 'Диспансеризация',
				'IdCaseType' => 4, // Диспансеризация
				'IdPatientMis' => $evnPLDispDop13Info['Person_id'],
				'DoctorInCharge/IdLpu' => $this->getIdLpu($evnPLDispDop13Info['MPLpu_id']),
				'DoctorInCharge/IdSpeciality' => $evnPLDispDop13Info['MPMedSpecOms_Code'],
				'DoctorInCharge/IdPosition' => $evnPLDispDop13Info['MPPost_Code'],
				'DoctorInCharge/Sex' => $evnPLDispDop13Info['MPSex_Code'],
				'DoctorInCharge/Birthdate' => $evnPLDispDop13Info['MPPerson_BirthDay'],
				'DoctorInCharge/IdPersonMis' => $evnPLDispDop13Info['MPPerson_id'],
				'DoctorInCharge/FamilyName' => $evnPLDispDop13Info['MPPerson_SurName'],
				'DoctorInCharge/GivenName' => $evnPLDispDop13Info['MPPerson_FirName'],
				'DoctorInCharge/MiddleName' => $evnPLDispDop13Info['MPPerson_SecName'],
				'DoctorInCharge/Documents' => array(),
				'Authenticator/IdLpu' => $this->getIdLpu($evnPLDispDop13Info['MPLpu_id']),
				'Authenticator/IdSpeciality' => $evnPLDispDop13Info['MPMedSpecOms_Code'],
				'Authenticator/IdPosition' => $evnPLDispDop13Info['MPPost_Code'],
				'Authenticator/Sex' => $evnPLDispDop13Info['MPSex_Code'],
				'Authenticator/Birthdate' => $evnPLDispDop13Info['MPPerson_BirthDay'],
				'Authenticator/IdPersonMis' => $evnPLDispDop13Info['MPPerson_id'],
				'Authenticator/FamilyName' => $evnPLDispDop13Info['MPPerson_SurName'],
				'Authenticator/GivenName' => $evnPLDispDop13Info['MPPerson_FirName'],
				'Authenticator/MiddleName' => $evnPLDispDop13Info['MPPerson_SecName'],
				'Authenticator/Documents' => array(),
				'Author/IdLpu' => $this->getIdLpu($evnPLDispDop13Info['MPLpu_id']),
				'Author/IdSpeciality' => $evnPLDispDop13Info['MPMedSpecOms_Code'],
				'Author/IdPosition' => $evnPLDispDop13Info['MPPost_Code'],
				'Author/Sex' => $evnPLDispDop13Info['MPSex_Code'],
				'Author/Birthdate' => $evnPLDispDop13Info['MPPerson_BirthDay'],
				'Author/IdPersonMis' => $evnPLDispDop13Info['MPPerson_id'],
				'Author/FamilyName' => $evnPLDispDop13Info['MPPerson_SurName'],
				'Author/GivenName' => $evnPLDispDop13Info['MPPerson_FirName'],
				'Author/MiddleName' => $evnPLDispDop13Info['MPPerson_SecName'],
				'Author/Documents' => array(),
				'Steps' => array()
			);

			$documents = array();

			if (!empty($evnPLDispDop13Info['MPPerson_SNILS'])) {
				$doc = array(
					'IdDocumentType' => 223,
					'DocS' => '',
					'DocN' => $evnPLDispDop13Info['MPPerson_SNILS'],
					'ExpiredDate' => null,
					'IssuedDate' => null,
					'ProviderName' => 'Не указан'
				);
				$documents[] = $doc;
			}

			if (!empty($evnPLDispDop13Info['MPPolisType_id']) && in_array($evnPLDispDop13Info['MPPolisType_id'], array(1, 4))) {
				$doc = array(
					'IdDocumentType' => $evnPLDispDop13Info['MPPolisType_id'] == 1 ? 226 : 228,
					'DocS' => $evnPLDispDop13Info['MPPolis_Ser'],
					'DocN' => $evnPLDispDop13Info['MPPolis_Num'],
					'ExpiredDate' => $evnPLDispDop13Info['MPPolis_endDate'],
					'IssuedDate' => $evnPLDispDop13Info['MPPolis_begDate'],
					'IdProvider' => $evnPLDispDop13Info['MPOrgSMO_f002smocod'],
					'ProviderName' => !empty($evnPLDispDop13Info['MPOrgSMO_Name']) ? $evnPLDispDop13Info['MPOrgSMO_Name'] : 'Не указан',
					'RegionCode' => $evnPLDispDop13Info['MPOmsSprTerr_Code']
				);
				$documents[] = $doc;
			}

			if (!empty($evnPLDispDop13Info['MPDocumentType_Code'])) {
				$doc = array(
					'IdDocumentType' => $evnPLDispDop13Info['MPDocumentType_Code'] == 14 ? 14 : 18,
					'DocS' => $evnPLDispDop13Info['MPDocument_Ser'],
					'DocN' => $evnPLDispDop13Info['MPDocument_Num'],
					'ExpiredDate' => $evnPLDispDop13Info['MPDocument_endDate'],
					'IssuedDate' => $evnPLDispDop13Info['MPDocument_begDate'],
					'ProviderName' => !empty($evnPLDispDop13Info['MPDOrg_Name']) ? $evnPLDispDop13Info['MPDOrg_Name'] : 'Не указан'
				);
				$documents[] = $doc;
			}

			$caseParams['DoctorInCharge/Documents'] = $documents;
			$caseParams['Authenticator/Documents'] = $documents;
			$caseParams['Author/Documents'] = $documents;

			$evnVizitDispDopInfos = $this->getEvnVizitDispDopInfo(array(
				'EvnPLDisp_id' => $EvnPLDispDop13_id
			));

			foreach ($evnVizitDispDopInfos as $evnVizitDispDopInfo) {
				$step = array(
					'DateStart' => $evnVizitDispDopInfo['EvnVizitDispDop_setDate'],
					'DateEnd' => !empty($evnVizitDispDopInfo['EvnVizitDispDop_disDate']) ? $evnVizitDispDopInfo['EvnVizitDispDop_disDate'] : $evnVizitDispDopInfo['EvnVizitDispDop_setDate'],
					'IdStepMis' => $evnVizitDispDopInfo['EvnVizitDispDop_id'],
					'IdPaymentType' => $this->getSyncSpr('PayType', $evnVizitDispDopInfo['PayType_id']),
					'IdVisitPlace' => 1, // Выгружать «1. амбулаторно»
					'IdVisitPurpose' => 10, // Выгружать «10. доп. Диспансеризация»
					'Doctor/IdLpu' => $this->getIdLpu($evnVizitDispDopInfo['MPLpu_id']),
					'Doctor/IdSpeciality' => $evnVizitDispDopInfo['MPMedSpecOms_Code'],
					'Doctor/IdPosition' => $evnVizitDispDopInfo['MPPost_Code'],
					'Doctor/Sex' => $evnVizitDispDopInfo['MPSex_Code'],
					'Doctor/Birthdate' => $evnVizitDispDopInfo['MPPerson_BirthDay'],
					'Doctor/IdPersonMis' => $evnVizitDispDopInfo['MPPerson_id'],
					'Doctor/FamilyName' => $evnVizitDispDopInfo['MPPerson_SurName'],
					'Doctor/GivenName' => $evnVizitDispDopInfo['MPPerson_FirName'],
					'Doctor/MiddleName' => $evnVizitDispDopInfo['MPPerson_SecName'],
					'Doctor/Documents' => array()
				);

				if (!empty($evnVizitDispDopInfo['MPPerson_SNILS'])) {
					$doc = array(
						'IdDocumentType' => 223,
						'DocS' => '',
						'DocN' => $evnVizitDispDopInfo['MPPerson_SNILS'],
						'ExpiredDate' => null,
						'IssuedDate' => null,
						'ProviderName' => 'Не указан'
					);
					$step['Doctor/Documents'][] = $doc;
				}

				if (!empty($evnVizitDispDopInfo['MPPolisType_id']) && in_array($evnVizitDispDopInfo['MPPolisType_id'], array(1, 4))) {
					$doc = array(
						'IdDocumentType' => $evnVizitDispDopInfo['MPPolisType_id'] == 1 ? 226 : 228,
						'DocS' => $evnVizitDispDopInfo['MPPolis_Ser'],
						'DocN' => $evnVizitDispDopInfo['MPPolis_Num'],
						'ExpiredDate' => $evnVizitDispDopInfo['MPPolis_endDate'],
						'IssuedDate' => $evnVizitDispDopInfo['MPPolis_begDate'],
						'IdProvider' => $evnVizitDispDopInfo['MPOrgSMO_f002smocod'],
						'ProviderName' => !empty($evnVizitDispDopInfo['MPOrgSMO_Name']) ? $evnVizitDispDopInfo['MPOrgSMO_Name'] : 'Не указан',
						'RegionCode' => $evnVizitDispDopInfo['MPOmsSprTerr_Code']
					);
					$step['Doctor/Documents'][] = $doc;
				}

				if (!empty($evnVizitDispDopInfo['MPDocumentType_Code'])) {
					$doc = array(
						'IdDocumentType' => $evnVizitDispDopInfo['MPDocumentType_Code'] == 14 ? 14 : 18,
						'DocS' => $evnVizitDispDopInfo['MPDocument_Ser'],
						'DocN' => $evnVizitDispDopInfo['MPDocument_Num'],
						'ExpiredDate' => $evnVizitDispDopInfo['MPDocument_endDate'],
						'IssuedDate' => $evnVizitDispDopInfo['MPDocument_begDate'],
						'ProviderName' => !empty($evnVizitDispDopInfo['MPDOrg_Name']) ? $evnVizitDispDopInfo['MPDOrg_Name'] : 'Не указан'
					);
					$step['Doctor/Documents'][] = $doc;
				}

				$caseParams['Steps'][] = $step;
			}

			if (!empty($caseParams['CloseDate'])) {
				// передача данных о закрытом случае
				if (!empty($caseId)) {
					$caseParams['function'] = 'UpdateCase';
				} else {
					$caseParams['function'] = 'AddCase';
				}
				$caseParams['casedto'] = 'caseDto';
			} else {
				// передача данных о новом случае
				$caseParams['function'] = 'CreateCase';
				$caseParams['Comment'] = '';
				$caseParams['casedto'] = 'createCaseDto';
			}

			// встроенным PHP-SOAP решением отправить случай не получается, вставляем данные в шаблон XML и отправляем готовую XML
			$this->load->library('parser');

			$caseParams['MedRecords'] = "";
			// ClinicMainDiagnosis (основной диагноз, сопутствующее диагнозы) осмотра терапевта / педиатра / ВОП
			$caseParams['MedRecords'] .= $this->parser->parse('export_xml/misrb_clinicmaindiagnosis', array(
				'IdDiseaseType' => 1,
				'DiagnosedDate' => !empty($evnPLDispDop13Info['EvnPLDispDop13_disDate']) ? $evnPLDispDop13Info['EvnPLDispDop13_disDate'] : $evnPLDispDop13Info['EvnPLDispDop13_setDate'],
				'IdDiagnosisType' => 1, // Основной
				'Comment' => "Диагноз, установленный врачами- специалистами (кроме терапевта / педиатра / ВОП) в рамках карты диспансреизации / профосмотра",
				'IdTraumaType' => '',
				'MkbCode' => $evnPLDispDop13Info['Diag_Code'],
				'IdLpu' => $this->getIdLpu($evnPLDispDop13Info['MPLpu_id']),
				'IdSpeciality' => $evnPLDispDop13Info['MPMedSpecOms_Code'],
				'IdPosition' => $evnPLDispDop13Info['MPPost_Code'],
				'Sex' => $evnPLDispDop13Info['MPSex_Code'],
				'Birthdate' => $evnPLDispDop13Info['MPPerson_BirthDay'],
				'IdPersonMis' => $evnPLDispDop13Info['MPPerson_id'],
				'FamilyName' => $evnPLDispDop13Info['MPPerson_SurName'],
				'GivenName' => $evnPLDispDop13Info['MPPerson_FirName'],
				'MiddleName' => $evnPLDispDop13Info['MPPerson_SecName'],
				'Documents' => $documents
			), true);
			// Diagnosis (диагнозы остальных осмотров врачей-специалистов)
			foreach ($evnVizitDispDopInfos as $evnVizitDispDopInfo) {
				$caseParams['MedRecords'] .= PHP_EOL . $this->parser->parse('export_xml/misrb_diagnosis', array(
						'IdDiseaseType' => ($evnVizitDispDopInfo['DopDispDiagType_id'] == 2) ? 1 : 3, // Выгружать «3. хроническое, диагностированное ранее», если характер заболевания «1. Ранее известное хроническое» Выгружать «1. острое», если характер заболевания «2. Выявленное во время дополнительной диспансеризации (профосмотра)»
						'DiagnosedDate' => $evnVizitDispDopInfo['EvnVizitDispDop_setDate'],
						'IdDiagnosisType' => 1, // Основной
						'Comment' => "Диагноз, установленный врачами- специалистами (кроме терапевта / педиатра / ВОП) в рамках карты диспансреизации / профосмотра",
						'MkbCode' => $evnVizitDispDopInfo['Diag_Code'],
						'IdLpu' => $this->getIdLpu($evnVizitDispDopInfo['MPLpu_id']),
						'IdSpeciality' => $evnVizitDispDopInfo['MPMedSpecOms_Code'],
						'IdPosition' => $evnVizitDispDopInfo['MPPost_Code'],
						'Sex' => $evnVizitDispDopInfo['MPSex_Code'],
						'Birthdate' => $evnVizitDispDopInfo['MPPerson_BirthDay'],
						'IdPersonMis' => $evnVizitDispDopInfo['MPPerson_id'],
						'FamilyName' => $evnVizitDispDopInfo['MPPerson_SurName'],
						'GivenName' => $evnVizitDispDopInfo['MPPerson_FirName'],
						'MiddleName' => $evnVizitDispDopInfo['MPPerson_SecName'],
						'Documents' => $documents
					), true);
			}
			// DispensaryOne (данные заключения первого этапа диспансеризации / профосмотра)
			$caseParams['MedRecords'] .= PHP_EOL . $this->parser->parse('export_xml/misrb_dispensaryone', array(
					'CreationDate' => $evnPLDispDop13Info['EvnPLDispDop13_setDate'],
					'Header' => 'Информация по диспансеризации / профосмотру',
					'IsGuested' => ($evnPLDispDop13Info['EvnPLDispDop13_IsMobile'] == 2) ? 'true' : 'false',
					'HasExtraResearchRefferal' => 'false',
					'IsUnderObservation' => ($evnPLDispDop13Info['EvnPLDispDop13_IsDisp'] == 2) ? 'true' : 'false',
					'HasExpertCareRefferal' => 'false',
					'HasPrescribeCure' => 'false',
					'HasHealthResortRefferal' => ($evnPLDispDop13Info['EvnPLDispDop13_IsSanator'] == 2) ? 'true' : 'false',
					'HasSecondStageRefferal' => ($evnPLDispDop13Info['EvnPLDispDop13_IsTwoStage'] == 2) ? 'true' : 'false',
					'IdLpu' => $this->getIdLpu($evnPLDispDop13Info['MPLpu_id']),
					'IdSpeciality' => $evnPLDispDop13Info['MPMedSpecOms_Code'],
					'IdPosition' => $evnPLDispDop13Info['MPPost_Code'],
					'Sex' => $evnPLDispDop13Info['MPSex_Code'],
					'Birthdate' => $evnPLDispDop13Info['MPPerson_BirthDay'],
					'IdPersonMis' => $evnPLDispDop13Info['MPPerson_id'],
					'FamilyName' => $evnPLDispDop13Info['MPPerson_SurName'],
					'GivenName' => $evnPLDispDop13Info['MPPerson_FirName'],
					'MiddleName' => $evnPLDispDop13Info['MPPerson_SecName'],
					'Documents' => $documents,
					'IdHealthGroup' => $this->getSyncSpr('HealthKind', $evnPLDispDop13Info['HealthKind_id']),
					'Date' => $evnPLDispDop13Info['EvnVizitDispDop_setDate']
				), true);

			$xml = $this->parser->parse('export_xml/misrb_evnpldispdop13', $caseParams, true);
			// удаляем пустые теги
			$xml = preg_replace('/<.*?><\/.*?>/ui', '', $xml);
			/*if (preg_match_all('/<.*?><\/.*?>/ui', $xml, $matches)) {
				foreach($matches[0] as $match) {
					if (mb_strpos($match, 'i:nil="true"') == false) {
						$xml = str_replace($match, '', $xml);
					}
				}
			}*/

			try {
				$this->exec('emk', $caseParams['function'], null, $xml);
			} catch (Exception $e) {
				if (!empty($e->detail->RequestFault->ErrorCode) && $e->detail->RequestFault->ErrorCode == 31) {
					// Случай обслуживания уже существует, пропускаем дальше
				} else {
					throw $e;
				}
			}

			$caseId = -1; // идентификатор никакой не возвращается.
			$this->saveSyncObject('EvnPLDispDop13', $EvnPLDispDop13_id, $caseId);
		}

		return $caseId;
	}

	/**
	 * Синхронизация карты профосмотра EvnPLDispProf
	 */
	function syncEvnPLDispProf($EvnPLDispProf_id)
	{
		$this->lastQueryType = 'syncEvnPLDispProf';
		$this->textlog->add("syncEvnPLDispProf: " . $EvnPLDispProf_id);

		$caseId = $this->getSyncObject('EvnPLDispProf', $EvnPLDispProf_id);

		if (true /* && empty($caseId) */) {
			$evnPLDispProfInfo = $this->getEvnPLDispProfInfo(array(
				'EvnPLDispProf_id' => $EvnPLDispProf_id
			));

			if (!empty($evnPLDispProfInfo['Person_id'])) {
				$this->syncPerson($evnPLDispProfInfo['Person_id']);
			}

			$caseParams = array(
				'guid' => '???', // заполнится позже, при отправке.
				'OpenDate' => $evnPLDispProfInfo['EvnPLDispProf_setDate'],
				'CloseDate' => $evnPLDispProfInfo['EvnPLDispProf_disDate'],
				'HistoryNumber' => $evnPLDispProfInfo['EvnPLDispProf_id'],
				'IdCaseMis' => $evnPLDispProfInfo['EvnPLDispProf_id'],
				'IdPaymentType' => $this->getSyncSpr('PayType', $evnPLDispProfInfo['PayType_id']),
				'Confidentiality' => 1, // 1. Не ограничен
				'DoctorConfidentiality' => 1, // 1. Не ограничен
				'CuratorConfidentiality' => 1, // 1. Не ограничен
				'IdLpu' => $this->getIdLpu($evnPLDispProfInfo['Lpu_id']),
				'IdCaseResult' => 3, // Выгружать «3. Без перемен»
				'Comment' => 'Диспансеризация',
				'IdCaseType' => 4, // Диспансеризация
				'IdPatientMis' => $evnPLDispProfInfo['Person_id'],
				'DoctorInCharge/IdLpu' => $this->getIdLpu($evnPLDispProfInfo['MPLpu_id']),
				'DoctorInCharge/IdSpeciality' => $evnPLDispProfInfo['MPMedSpecOms_Code'],
				'DoctorInCharge/IdPosition' => $evnPLDispProfInfo['MPPost_Code'],
				'DoctorInCharge/Sex' => $evnPLDispProfInfo['MPSex_Code'],
				'DoctorInCharge/Birthdate' => $evnPLDispProfInfo['MPPerson_BirthDay'],
				'DoctorInCharge/IdPersonMis' => $evnPLDispProfInfo['MPPerson_id'],
				'DoctorInCharge/FamilyName' => $evnPLDispProfInfo['MPPerson_SurName'],
				'DoctorInCharge/GivenName' => $evnPLDispProfInfo['MPPerson_FirName'],
				'DoctorInCharge/MiddleName' => $evnPLDispProfInfo['MPPerson_SecName'],
				'DoctorInCharge/Documents' => array(),
				'Authenticator/IdLpu' => $this->getIdLpu($evnPLDispProfInfo['MPLpu_id']),
				'Authenticator/IdSpeciality' => $evnPLDispProfInfo['MPMedSpecOms_Code'],
				'Authenticator/IdPosition' => $evnPLDispProfInfo['MPPost_Code'],
				'Authenticator/Sex' => $evnPLDispProfInfo['MPSex_Code'],
				'Authenticator/Birthdate' => $evnPLDispProfInfo['MPPerson_BirthDay'],
				'Authenticator/IdPersonMis' => $evnPLDispProfInfo['MPPerson_id'],
				'Authenticator/FamilyName' => $evnPLDispProfInfo['MPPerson_SurName'],
				'Authenticator/GivenName' => $evnPLDispProfInfo['MPPerson_FirName'],
				'Authenticator/MiddleName' => $evnPLDispProfInfo['MPPerson_SecName'],
				'Authenticator/Documents' => array(),
				'Author/IdLpu' => $this->getIdLpu($evnPLDispProfInfo['MPLpu_id']),
				'Author/IdSpeciality' => $evnPLDispProfInfo['MPMedSpecOms_Code'],
				'Author/IdPosition' => $evnPLDispProfInfo['MPPost_Code'],
				'Author/Sex' => $evnPLDispProfInfo['MPSex_Code'],
				'Author/Birthdate' => $evnPLDispProfInfo['MPPerson_BirthDay'],
				'Author/IdPersonMis' => $evnPLDispProfInfo['MPPerson_id'],
				'Author/FamilyName' => $evnPLDispProfInfo['MPPerson_SurName'],
				'Author/GivenName' => $evnPLDispProfInfo['MPPerson_FirName'],
				'Author/MiddleName' => $evnPLDispProfInfo['MPPerson_SecName'],
				'Author/Documents' => array(),
				'Steps' => array(),
				'ClinicMainDiagnosis/IdDiseaseType' => 1,
				'ClinicMainDiagnosis/DiagnosedDate' => $evnPLDispProfInfo['EvnPLDispProf_disDate'],
				'ClinicMainDiagnosis/IdDiagnosisType' => 1, // Основной
				'ClinicMainDiagnosis/Comment' => "Диагноз, установленный врачами- специалистами (кроме терапевта / педиатра / ВОП) в рамках карты диспансреизации / профосмотра",
				'ClinicMainDiagnosis/MkbCode' => $evnPLDispProfInfo['Diag_Code'],
				'ClinicMainDiagnosis/IdLpu' => $this->getIdLpu($evnPLDispProfInfo['MPLpu_id']),
				'ClinicMainDiagnosis/IdSpeciality' => $evnPLDispProfInfo['MPMedSpecOms_Code'],
				'ClinicMainDiagnosis/IdPosition' => $evnPLDispProfInfo['MPPost_Code'],
				'ClinicMainDiagnosis/Sex' => $evnPLDispProfInfo['MPSex_Code'],
				'ClinicMainDiagnosis/Birthdate' => $evnPLDispProfInfo['MPPerson_BirthDay'],
				'ClinicMainDiagnosis/IdPersonMis' => $evnPLDispProfInfo['MPPerson_id'],
				'ClinicMainDiagnosis/FamilyName' => $evnPLDispProfInfo['MPPerson_SurName'],
				'ClinicMainDiagnosis/GivenName' => $evnPLDispProfInfo['MPPerson_FirName'],
				'ClinicMainDiagnosis/MiddleName' => $evnPLDispProfInfo['MPPerson_SecName'],
				'ClinicMainDiagnosis/Documents' => array()
			);

			$documents = array();

			if (!empty($evnPLDispProfInfo['MPPerson_SNILS'])) {
				$doc = array(
					'IdDocumentType' => 223,
					'DocS' => '',
					'DocN' => $evnPLDispProfInfo['MPPerson_SNILS'],
					'ExpiredDate' => null,
					'IssuedDate' => null,
					'ProviderName' => 'Не указан'
				);
				$documents[] = $doc;
			}

			if (!empty($evnPLDispProfInfo['MPPolisType_id']) && in_array($evnPLDispProfInfo['MPPolisType_id'], array(1, 4))) {
				$doc = array(
					'IdDocumentType' => $evnPLDispProfInfo['MPPolisType_id'] == 1 ? 226 : 228,
					'DocS' => $evnPLDispProfInfo['MPPolis_Ser'],
					'DocN' => $evnPLDispProfInfo['MPPolis_Num'],
					'ExpiredDate' => $evnPLDispProfInfo['MPPolis_endDate'],
					'IssuedDate' => $evnPLDispProfInfo['MPPolis_begDate'],
					'IdProvider' => $evnPLDispProfInfo['MPOrgSMO_f002smocod'],
					'ProviderName' => !empty($evnPLDispProfInfo['MPOrgSMO_Name']) ? $evnPLDispProfInfo['MPOrgSMO_Name'] : 'Не указан',
					'RegionCode' => $evnPLDispProfInfo['MPOmsSprTerr_Code']
				);
				$documents[] = $doc;
			}

			if (!empty($evnPLDispProfInfo['MPDocumentType_Code'])) {
				$doc = array(
					'IdDocumentType' => $evnPLDispProfInfo['MPDocumentType_Code'] == 14 ? 14 : 18,
					'DocS' => $evnPLDispProfInfo['MPDocument_Ser'],
					'DocN' => $evnPLDispProfInfo['MPDocument_Num'],
					'ExpiredDate' => $evnPLDispProfInfo['MPDocument_endDate'],
					'IssuedDate' => $evnPLDispProfInfo['MPDocument_begDate'],
					'ProviderName' => !empty($evnPLDispProfInfo['MPDOrg_Name']) ? $evnPLDispProfInfo['MPDOrg_Name'] : 'Не указан'
				);
				$documents[] = $doc;
			}

			$caseParams['DoctorInCharge/Documents'] = $documents;
			$caseParams['Authenticator/Documents'] = $documents;
			$caseParams['Author/Documents'] = $documents;

			$evnVizitDispDopInfos = $this->getEvnVizitDispDopInfo(array(
				'EvnPLDisp_id' => $EvnPLDispProf_id
			));

			foreach ($evnVizitDispDopInfos as $evnVizitDispDopInfo) {
				$step = array(
					'DateStart' => $evnVizitDispDopInfo['EvnVizitDispDop_setDate'],
					'DateEnd' => !empty($evnVizitDispDopInfo['EvnVizitDispDop_disDate']) ? $evnVizitDispDopInfo['EvnVizitDispDop_disDate'] : $evnVizitDispDopInfo['EvnVizitDispDop_setDate'],
					'IdStepMis' => $evnVizitDispDopInfo['EvnVizitDispDop_id'],
					'IdPaymentType' => $this->getSyncSpr('PayType', $evnVizitDispDopInfo['PayType_id']),
					'IdVisitPlace' => 1, // Выгружать «1. амбулаторно»
					'IdVisitPurpose' => 10, // Выгружать «10. доп. Диспансеризация»
					'Doctor/IdLpu' => $this->getIdLpu($evnVizitDispDopInfo['MPLpu_id']),
					'Doctor/IdSpeciality' => $evnVizitDispDopInfo['MPMedSpecOms_Code'],
					'Doctor/IdPosition' => $evnVizitDispDopInfo['MPPost_Code'],
					'Doctor/Sex' => $evnVizitDispDopInfo['MPSex_Code'],
					'Doctor/Birthdate' => $evnVizitDispDopInfo['MPPerson_BirthDay'],
					'Doctor/IdPersonMis' => $evnVizitDispDopInfo['MPPerson_id'],
					'Doctor/FamilyName' => $evnVizitDispDopInfo['MPPerson_SurName'],
					'Doctor/GivenName' => $evnVizitDispDopInfo['MPPerson_FirName'],
					'Doctor/MiddleName' => $evnVizitDispDopInfo['MPPerson_SecName'],
					'Doctor/Documents' => array()
				);

				if (!empty($evnVizitDispDopInfo['MPPerson_SNILS'])) {
					$doc = array(
						'IdDocumentType' => 223,
						'DocS' => '',
						'DocN' => $evnVizitDispDopInfo['MPPerson_SNILS'],
						'ExpiredDate' => null,
						'IssuedDate' => null,
						'ProviderName' => 'Не указан'
					);
					$step['Doctor/Documents'][] = $doc;
				}

				if (!empty($evnVizitDispDopInfo['MPPolisType_id']) && in_array($evnVizitDispDopInfo['MPPolisType_id'], array(1, 4))) {
					$doc = array(
						'IdDocumentType' => $evnVizitDispDopInfo['MPPolisType_id'] == 1 ? 226 : 228,
						'DocS' => $evnVizitDispDopInfo['MPPolis_Ser'],
						'DocN' => $evnVizitDispDopInfo['MPPolis_Num'],
						'ExpiredDate' => $evnVizitDispDopInfo['MPPolis_endDate'],
						'IssuedDate' => $evnVizitDispDopInfo['MPPolis_begDate'],
						'IdProvider' => $evnVizitDispDopInfo['MPOrgSMO_f002smocod'],
						'ProviderName' => !empty($evnVizitDispDopInfo['MPOrgSMO_Name']) ? $evnVizitDispDopInfo['MPOrgSMO_Name'] : 'Не указан',
						'RegionCode' => $evnVizitDispDopInfo['MPOmsSprTerr_Code']
					);
					$step['Doctor/Documents'][] = $doc;
				}

				if (!empty($evnVizitDispDopInfo['MPDocumentType_Code'])) {
					$doc = array(
						'IdDocumentType' => $evnVizitDispDopInfo['MPDocumentType_Code'] == 14 ? 14 : 18,
						'DocS' => $evnVizitDispDopInfo['MPDocument_Ser'],
						'DocN' => $evnVizitDispDopInfo['MPDocument_Num'],
						'ExpiredDate' => $evnVizitDispDopInfo['MPDocument_endDate'],
						'IssuedDate' => $evnVizitDispDopInfo['MPDocument_begDate'],
						'ProviderName' => !empty($evnVizitDispDopInfo['MPDOrg_Name']) ? $evnVizitDispDopInfo['MPDOrg_Name'] : 'Не указан'
					);
					$step['Doctor/Documents'][] = $doc;
				}

				$caseParams['Steps'][] = $step;
			}

			if (!empty($caseParams['CloseDate'])) {
				// передача данных о закрытом случае
				if (!empty($caseId)) {
					$caseParams['function'] = 'UpdateCase';
				} else {
					$caseParams['function'] = 'AddCase';
				}
				$caseParams['casedto'] = 'caseDto';
			} else {
				// передача данных о новом случае
				$caseParams['function'] = 'CreateCase';
				$caseParams['Comment'] = '';
				$caseParams['casedto'] = 'createCaseDto';
			}

			// встроенным PHP-SOAP решением отправить случай не получается, вставляем данные в шаблон XML и отправляем готовую XML
			$this->load->library('parser');

			$caseParams['MedRecords'] = "";
			// ClinicMainDiagnosis (основной диагноз, сопутствующее диагнозы) осмотра терапевта / педиатра / ВОП
			$caseParams['MedRecords'] .= $this->parser->parse('export_xml/misrb_clinicmaindiagnosis', array(
				'IdDiseaseType' => 1,
				'DiagnosedDate' => $evnPLDispProfInfo['EvnPLDispProf_disDate'],
				'IdDiagnosisType' => 1, // Основной
				'Comment' => "Диагноз, установленный врачами- специалистами (кроме терапевта / педиатра / ВОП) в рамках карты диспансреизации / профосмотра",
				'IdTraumaType' => '',
				'MkbCode' => $evnPLDispProfInfo['Diag_Code'],
				'IdLpu' => $this->getIdLpu($evnPLDispProfInfo['MPLpu_id']),
				'IdSpeciality' => $evnPLDispProfInfo['MPMedSpecOms_Code'],
				'IdPosition' => $evnPLDispProfInfo['MPPost_Code'],
				'Sex' => $evnPLDispProfInfo['MPSex_Code'],
				'Birthdate' => $evnPLDispProfInfo['MPPerson_BirthDay'],
				'IdPersonMis' => $evnPLDispProfInfo['MPPerson_id'],
				'FamilyName' => $evnPLDispProfInfo['MPPerson_SurName'],
				'GivenName' => $evnPLDispProfInfo['MPPerson_FirName'],
				'MiddleName' => $evnPLDispProfInfo['MPPerson_SecName'],
				'Documents' => $documents
			), true);
			// Diagnosis (диагнозы остальных осмотров врачей-специалистов)
			foreach ($evnVizitDispDopInfos as $evnVizitDispDopInfo) {
				$caseParams['MedRecords'] .= PHP_EOL . $this->parser->parse('export_xml/misrb_diagnosis', array(
						'IdDiseaseType' => ($evnVizitDispDopInfo['DopDispDiagType_id'] == 2) ? 1 : 3, // Выгружать «3. хроническое, диагностированное ранее», если характер заболевания «1. Ранее известное хроническое» Выгружать «1. острое», если характер заболевания «2. Выявленное во время дополнительной диспансеризации (профосмотра)»
						'DiagnosedDate' => $evnVizitDispDopInfo['EvnVizitDispDop_setDate'],
						'IdDiagnosisType' => 1, // Основной
						'Comment' => "Диагноз, установленный врачами- специалистами (кроме терапевта / педиатра / ВОП) в рамках карты диспансреизации / профосмотра",
						'MkbCode' => $evnVizitDispDopInfo['Diag_Code'],
						'IdLpu' => $this->getIdLpu($evnVizitDispDopInfo['MPLpu_id']),
						'IdSpeciality' => $evnVizitDispDopInfo['MPMedSpecOms_Code'],
						'IdPosition' => $evnVizitDispDopInfo['MPPost_Code'],
						'Sex' => $evnVizitDispDopInfo['MPSex_Code'],
						'Birthdate' => $evnVizitDispDopInfo['MPPerson_BirthDay'],
						'IdPersonMis' => $evnVizitDispDopInfo['MPPerson_id'],
						'FamilyName' => $evnVizitDispDopInfo['MPPerson_SurName'],
						'GivenName' => $evnVizitDispDopInfo['MPPerson_FirName'],
						'MiddleName' => $evnVizitDispDopInfo['MPPerson_SecName'],
						'Documents' => $documents
					), true);
			}
			// DispensaryOne (данные заключения первого этапа диспансеризации / профосмотра)
			$caseParams['MedRecords'] .= PHP_EOL . $this->parser->parse('export_xml/misrb_dispensaryone', array(
					'CreationDate' => $evnPLDispProfInfo['EvnPLDispProf_setDate'],
					'Header' => 'Информация по диспансеризации / профосмотру',
					'IsGuested' => ($evnPLDispProfInfo['EvnPLDispProf_IsMobile'] == 2) ? 'true' : 'false',
					'HasExtraResearchRefferal' => 'false',
					'IsUnderObservation' => ($evnPLDispProfInfo['EvnPLDispProf_IsDisp'] == 2) ? 'true' : 'false',
					'HasExpertCareRefferal' => 'false',
					'HasPrescribeCure' => 'false',
					'HasHealthResortRefferal' => ($evnPLDispProfInfo['EvnPLDispProf_IsSanator'] == 2) ? 'true' : 'false',
					'HasSecondStageRefferal' => 'false',
					'IdLpu' => $this->getIdLpu($evnPLDispProfInfo['MPLpu_id']),
					'IdSpeciality' => $evnPLDispProfInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnPLDispProfInfo['MPPost_Code'],
					'Sex' => $evnPLDispProfInfo['MPSex_Code'],
					'Birthdate' => $evnPLDispProfInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnPLDispProfInfo['MPPerson_id'],
					'FamilyName' => $evnPLDispProfInfo['MPPerson_SurName'],
					'GivenName' => $evnPLDispProfInfo['MPPerson_FirName'],
					'MiddleName' => $evnPLDispProfInfo['MPPerson_SecName'],
					'Documents' => $documents,
					'IdHealthGroup' => $this->getSyncSpr('HealthKind', $evnPLDispProfInfo['HealthKind_id']),
					'Date' => $evnPLDispProfInfo['EvnVizitDispDop_setDate']
				), true);

			$xml = $this->parser->parse('export_xml/misrb_evnpldispprof', $caseParams, true);
			// удаляем пустые теги
			$xml = preg_replace('/<.*?><\/.*?>/ui', '', $xml);
			/*if (preg_match_all('/<.*?><\/.*?>/ui', $xml, $matches)) {
				foreach($matches[0] as $match) {
					if (mb_strpos($match, 'i:nil="true"') == false) {
						$xml = str_replace($match, '', $xml);
					}
				}
			}*/

			try {
				$this->exec('emk', $caseParams['function'], null, $xml);
			} catch (Exception $e) {
				if (!empty($e->detail->RequestFault->ErrorCode) && $e->detail->RequestFault->ErrorCode == 31) {
					// Случай обслуживания уже существует, пропускаем дальше
				} else {
					throw $e;
				}
			}

			$caseId = -1; // идентификатор никакой не возвращается.
			$this->saveSyncObject('EvnPLDispProf', $EvnPLDispProf_id, $caseId);
		}

		return $caseId;
	}

	/**
	 * Синхронизация КВС
	 */
	function syncEvnPS($EvnPS_id)
	{
		$this->lastQueryType = 'syncEvnPS';
		$this->textlog->add("syncEvnPS: " . $EvnPS_id);

		$caseId = $this->getSyncObject('EvnPS', $EvnPS_id);

		if (true /* && empty($caseId) */) {
			$evnPSInfo = $this->getEvnPSInfo(array(
				'EvnPS_id' => $EvnPS_id
			));

			if (!empty($evnPSInfo['Person_id'])) {
				$this->syncPerson($evnPSInfo['Person_id']);
			}

			$caseParams = array(
				'guid' => '???', // заполнится позже, при отправке.
				'OpenDate' => $evnPSInfo['EvnPS_setDate'],
				'CloseDate' => $evnPSInfo['EvnPS_disDate'],
				'HistoryNumber' => $evnPSInfo['EvnPS_NumCard'],
				'IdCaseMis' => $evnPSInfo['EvnPS_id'],
				'IdPaymentType' => $this->getSyncSpr('PayType', $evnPSInfo['PayType_id']),
				'Confidentiality' => 1, // todo по ТЗ надо смотреть ограничение доступа, что весьма сомнительное решение
				'DoctorConfidentiality' => 1, // todo по ТЗ надо смотреть ограничение доступа, что весьма сомнительное решение
				'CuratorConfidentiality' => 1, // todo по ТЗ надо смотреть ограничение доступа, что весьма сомнительное решение
				'IdLpu' => $this->getIdLpu($evnPSInfo['Lpu_id']),
				'IdCaseResult' => $this->getSyncSpr('ResultDeseaseType', $evnPSInfo['ResultDeseaseType_fedid']),
				'Comment' => 'Текст заключения из эпикриза', // todo текст заключения из эпикриза
				'HospResult' => $this->getSyncSpr('LeaveType', $evnPSInfo['LeaveType_id']),
				'IdHospChannel' => 1, // по ТЗ не выгружать, но сервис требует
				'IdCaseType' => 2, // Амбулаторный случай
				'IdPatientMis' => $evnPSInfo['Person_id'],
				'IdAmbResult' => $this->getSyncSpr('ResultClass', $evnPSInfo['ResultClass_id']),
				'IsActive' => !empty($evnPSInfo['EvnDirection_id']) ? "true" : "false",
				'DoctorInCharge/IdLpu' => $this->getIdLpu($evnPSInfo['MPLpu_id']),
				'DoctorInCharge/IdSpeciality' => $evnPSInfo['MPMedSpecOms_Code'],
				'DoctorInCharge/IdPosition' => $evnPSInfo['MPPost_Code'],
				'DoctorInCharge/Sex' => $evnPSInfo['MPSex_Code'],
				'DoctorInCharge/Birthdate' => $evnPSInfo['MPPerson_BirthDay'],
				'DoctorInCharge/IdPersonMis' => $evnPSInfo['MPPerson_id'],
				'DoctorInCharge/FamilyName' => $evnPSInfo['MPPerson_SurName'],
				'DoctorInCharge/GivenName' => $evnPSInfo['MPPerson_FirName'],
				'DoctorInCharge/MiddleName' => $evnPSInfo['MPPerson_SecName'],
				'DoctorInCharge/Documents' => array(),
				'Authenticator/IdLpu' => $this->getIdLpu($evnPSInfo['MPLpu_id']),
				'Authenticator/IdSpeciality' => $evnPSInfo['MPMedSpecOms_Code'],
				'Authenticator/IdPosition' => $evnPSInfo['MPPost_Code'],
				'Authenticator/Sex' => $evnPSInfo['MPSex_Code'],
				'Authenticator/Birthdate' => $evnPSInfo['MPPerson_BirthDay'],
				'Authenticator/IdPersonMis' => $evnPSInfo['MPPerson_id'],
				'Authenticator/FamilyName' => $evnPSInfo['MPPerson_SurName'],
				'Authenticator/GivenName' => $evnPSInfo['MPPerson_FirName'],
				'Authenticator/MiddleName' => $evnPSInfo['MPPerson_SecName'],
				'Authenticator/Documents' => array(),
				'Author/IdLpu' => $this->getIdLpu($evnPSInfo['MPLpu_id']),
				'Author/IdSpeciality' => $evnPSInfo['MPMedSpecOms_Code'],
				'Author/IdPosition' => $evnPSInfo['MPPost_Code'],
				'Author/Sex' => $evnPSInfo['MPSex_Code'],
				'Author/Birthdate' => $evnPSInfo['MPPerson_BirthDay'],
				'Author/IdPersonMis' => $evnPSInfo['MPPerson_id'],
				'Author/FamilyName' => $evnPSInfo['MPPerson_SurName'],
				'Author/GivenName' => $evnPSInfo['MPPerson_FirName'],
				'Author/MiddleName' => $evnPSInfo['MPPerson_SecName'],
				'Author/Documents' => array(),
				'Steps' => array()
			);

			$documents = array();

			if (!empty($evnPSInfo['MPPerson_SNILS'])) {
				$doc = array(
					'IdDocumentType' => 223,
					'DocS' => '',
					'DocN' => $evnPSInfo['MPPerson_SNILS'],
					'ExpiredDate' => null,
					'IssuedDate' => null,
					'ProviderName' => 'Не указан'
				);
				$documents[] = $doc;
			}

			if (!empty($evnPSInfo['MPPolisType_id']) && in_array($evnPSInfo['MPPolisType_id'], array(1, 4))) {
				$doc = array(
					'IdDocumentType' => $evnPSInfo['MPPolisType_id'] == 1 ? 226 : 228,
					'DocS' => $evnPSInfo['MPPolis_Ser'],
					'DocN' => $evnPSInfo['MPPolis_Num'],
					'ExpiredDate' => $evnPSInfo['MPPolis_endDate'],
					'IssuedDate' => $evnPSInfo['MPPolis_begDate'],
					'IdProvider' => $evnPSInfo['MPOrgSMO_f002smocod'],
					'ProviderName' => !empty($evnPSInfo['MPOrgSMO_Name']) ? $evnPSInfo['MPOrgSMO_Name'] : 'Не указан',
					'RegionCode' => $evnPSInfo['MPOmsSprTerr_Code']
				);
				$documents[] = $doc;
			}

			if (!empty($evnPSInfo['MPDocumentType_Code'])) {
				$doc = array(
					'IdDocumentType' => $evnPSInfo['MPDocumentType_Code'] == 14 ? 14 : 18,
					'DocS' => $evnPSInfo['MPDocument_Ser'],
					'DocN' => $evnPSInfo['MPDocument_Num'],
					'ExpiredDate' => $evnPSInfo['MPDocument_endDate'],
					'IssuedDate' => $evnPSInfo['MPDocument_begDate'],
					'ProviderName' => !empty($evnPSInfo['MPDOrg_Name']) ? $evnPSInfo['MPDOrg_Name'] : 'Не указан'
				);
				$documents[] = $doc;
			}

			$caseParams['DoctorInCharge/Documents'] = $documents;
			$caseParams['Authenticator/Documents'] = $documents;
			$caseParams['Author/Documents'] = $documents;

			// встроенным PHP-SOAP решением отправить случай не получается, вставляем данные в шаблон XML и отправляем готовую XML
			$this->load->library('parser');

			$evnSectionInfos = $this->getEvnSectionInfo(array(
				'EvnPS_id' => $EvnPS_id
			));

			foreach ($evnSectionInfos as $evnSectionInfo) {
				$step = array(
					'DateStart' => $evnSectionInfo['EvnSection_setDate'],
					'DateEnd' => !empty($evnSectionInfo['EvnSection_disDate']) ? $evnSectionInfo['EvnSection_disDate'] : $evnSectionInfo['EvnSection_setDate'],
					'IdStepMis' => $evnSectionInfo['EvnSection_id'],
					'HospitalDepartmentName' => $evnSectionInfo['LpuSection_Name'],
					'IdHospitalDepartment' => $evnSectionInfo['LpuSection_Code'],
					'BedProfile' => $evnSectionInfo['LpuSectionBedProfile_Code'],
					'IdPaymentType' => $this->getSyncSpr('PayType', $evnSectionInfo['PayType_id']),
					'Doctor/IdLpu' => $this->getIdLpu($evnSectionInfo['MPLpu_id']),
					'Doctor/IdSpeciality' => $evnSectionInfo['MPMedSpecOms_Code'],
					'Doctor/IdPosition' => $evnSectionInfo['MPPost_Code'],
					'Doctor/Sex' => $evnSectionInfo['MPSex_Code'],
					'Doctor/Birthdate' => $evnSectionInfo['MPPerson_BirthDay'],
					'Doctor/IdPersonMis' => $evnSectionInfo['MPPerson_id'],
					'Doctor/FamilyName' => $evnSectionInfo['MPPerson_SurName'],
					'Doctor/GivenName' => $evnSectionInfo['MPPerson_FirName'],
					'Doctor/MiddleName' => $evnSectionInfo['MPPerson_SecName'],
					'Doctor/Documents' => array()
				);

				$es_documents = array();

				if (!empty($evnSectionInfo['MPPerson_SNILS'])) {
					$doc = array(
						'IdDocumentType' => 223,
						'DocS' => '',
						'DocN' => $evnSectionInfo['MPPerson_SNILS'],
						'ExpiredDate' => null,
						'IssuedDate' => null,
						'ProviderName' => 'Не указан'
					);
					$es_documents[] = $doc;
				}

				if (!empty($evnSectionInfo['MPPolisType_id']) && in_array($evnSectionInfo['MPPolisType_id'], array(1, 4))) {
					$doc = array(
						'IdDocumentType' => $evnSectionInfo['MPPolisType_id'] == 1 ? 226 : 228,
						'DocS' => $evnSectionInfo['MPPolis_Ser'],
						'DocN' => $evnSectionInfo['MPPolis_Num'],
						'ExpiredDate' => $evnSectionInfo['MPPolis_endDate'],
						'IssuedDate' => $evnSectionInfo['MPPolis_begDate'],
						'IdProvider' => $evnSectionInfo['MPOrgSMO_f002smocod'],
						'ProviderName' => !empty($evnSectionInfo['MPOrgSMO_Name']) ? $evnSectionInfo['MPOrgSMO_Name'] : 'Не указан',
						'RegionCode' => $evnSectionInfo['MPOmsSprTerr_Code']
					);
					$es_documents[] = $doc;
				}

				if (!empty($evnSectionInfo['MPDocumentType_Code'])) {
					$doc = array(
						'IdDocumentType' => $evnSectionInfo['MPDocumentType_Code'] == 14 ? 14 : 18,
						'DocS' => $evnSectionInfo['MPDocument_Ser'],
						'DocN' => $evnSectionInfo['MPDocument_Num'],
						'ExpiredDate' => $evnSectionInfo['MPDocument_endDate'],
						'IssuedDate' => $evnSectionInfo['MPDocument_begDate'],
						'ProviderName' => !empty($evnSectionInfo['MPDOrg_Name']) ? $evnSectionInfo['MPDOrg_Name'] : 'Не указан'
					);
					$es_documents[] = $doc;
				}

				$step['Doctor/Documents'] = $es_documents;

				$step['MedRecords'] = "";
				// ClinicMainDiagnosis (основной диагноз, сопутствующее диагнозы)
				$step['MedRecords'] .= $this->parser->parse('export_xml/misrb_clinicmaindiagnosis', array(
					'IdDiseaseType' => 1, // острое
					'DiagnosedDate' => !empty($evnSectionInfo['EvnSection_disDate']) ? $evnSectionInfo['EvnSection_disDate'] : $evnSectionInfo['EvnSection_setDate'],
					'IdDiagnosisType' => 1, // Основной
					'Comment' => "Основной диагноз",
					'IdTraumaType' => '',
					'MkbCode' => $evnSectionInfo['Diag_Code'],
					'IdLpu' => $this->getIdLpu($evnSectionInfo['MPLpu_id']),
					'IdSpeciality' => $evnSectionInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnSectionInfo['MPPost_Code'],
					'Sex' => $evnSectionInfo['MPSex_Code'],
					'Birthdate' => $evnSectionInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnSectionInfo['MPPerson_id'],
					'FamilyName' => $evnSectionInfo['MPPerson_SurName'],
					'GivenName' => $evnSectionInfo['MPPerson_FirName'],
					'MiddleName' => $evnSectionInfo['MPPerson_SecName'],
					'Documents' => $es_documents
				), true);
				// ClinicMainDiagnosis (сопутствующее диагнозы)
				$step['MedRecords'] .= $this->getClinicMainDiagnosis(array(
					'EvnSection_id' => $evnSectionInfo['EvnSection_id']
				));
				// Service (данные о выполненных услугах)
				$step['MedRecords'] .= $this->getService(array(
					'EvnUsluga_pid' => $evnSectionInfo['EvnSection_id']
				));
				// LaboratoryReport (Лабораторные исследования)
				$step['MedRecords'] .= $this->getLaboratoryReport(array(
					'EvnUsluga_pid' => $evnSectionInfo['EvnSection_id']
				));
				// PacsResult (Инструментальное исследование)
				$step['MedRecords'] .= $this->getPacsResult(array(
					'EvnUsluga_pid' => $evnSectionInfo['EvnSection_id']
				));

				$caseParams['Steps'][] = $step;
			}

			$template = 'export_xml/misrb_evnps';
			if (!empty($caseParams['CloseDate'])) {
				// передача данных о закрытом случае
				if (!empty($caseId)) {
					$caseParams['function'] = 'UpdateCase';
				} else {
					$caseParams['function'] = 'AddCase';
				}
				$caseParams['casedto'] = 'caseDto';
			} else {
				if (!empty($caseId)) {
					$template .= '_addstep';
					// передаём каждое посещение
					foreach ($caseParams['Steps'] as $step) {
						$step['guid'] = $caseParams['guid'];
						$step['IdLpu'] = $caseParams['IdLpu'];
						$step['IdPatientMis'] = $caseParams['IdPatientMis'];
						$step['IdCaseMis'] = $caseParams['IdCaseMis'];
						$xml = $this->parser->parse($template, $step, true);
						// удаляем пустые теги
						$xml = preg_replace('/<.*?><\/.*?>/ui', '', $xml);

						try {
							$this->exec('emk', 'AddStepToCase', null, $xml);
						} catch (Exception $e) {
							if (!empty($e->detail->RequestFault->ErrorCode) && $e->detail->RequestFault->ErrorCode == 31) {
								// Случай обслуживания уже существует, пропускаем дальше
							} else {
								throw $e;
							}
						}
					}
					return -1;
				} else {
					// передача данных о новом случае
					$caseParams['function'] = 'CreateCase';
					$caseParams['Comment'] = '';
					$caseParams['casedto'] = 'createCaseDto';
				}
			}

			$caseParams['MedRecords'] = "";
			// SickList (ЛВН)
			$caseParams['MedRecords'] .= $this->getSickList(array(
				'EvnStick_pid' => $evnPSInfo['EvnPS_id']
			));
			// LaboratoryReport (Лабораторные исследования)
			$caseParams['MedRecords'] .= $this->getLaboratoryReport(array(
				'EvnUsluga_pid' => $evnPSInfo['EvnPS_id']
			));
			// PacsResult (Инструментальное исследование)
			$caseParams['MedRecords'] .= $this->getPacsResult(array(
				'EvnUsluga_pid' => $evnPSInfo['EvnPS_id']
			));
			// Referral (данные об электронном направлении)
			$caseParams['MedRecords'] .= $this->getReferral(array(
				'EvnDirection_rid' => $evnPSInfo['EvnPS_id']
			));
			// DeathInfo (Летальный исход)
			if (!empty($evnPSInfo['Diag_deathCode'])) {
				$caseParams['MedRecords'] .= PHP_EOL . $this->parser->parse('export_xml/misrb_deathinfo', array(
						'MkbCode' => $evnPSInfo['Diag_deathCode']
					), true);

				// AnatomopathologicalMainDiagnosis (Патологоанатомический диагноз)
				$caseParams['MedRecords'] .= $this->getAnatomopathologicalMainDiagnosis(array(
					'Person_id' => $evnPSInfo['Person_id']
				));
			}

			// DischargeSummary (Эпикризы)
			$caseParams['MedRecords'] .= $this->getDischargeSummary(array(
				'EvnXml_rid' => $evnPSInfo['EvnPS_id']
			));
			// Form027U (Выписной эпикриз)
			$caseParams['MedRecords'] .= $this->getForm027U(array(
				'EvnXml_rid' => $evnPSInfo['EvnPS_id']
			));
			// TFomsInfo (данные о выполненных МЭС)
			$caseParams['MedRecords'] .= $this->getTFomsInfo(array(
				'Evn_pid' => $evnPSInfo['EvnPS_id']
			));
			// ConsultNote (Консультационные услуги)
			$caseParams['MedRecords'] .= $this->getConsultNote(array(
				'EvnUsluga_pid' => $evnPSInfo['EvnPS_id']
			));

			$xml = $this->parser->parse($template, $caseParams, true);
			// удаляем пустые теги
			$xml = preg_replace('/<.*?><\/.*?>/ui', '', $xml);
			/*if (preg_match_all('/<.*?><\/.*?>/ui', $xml, $matches)) {
				foreach($matches[0] as $match) {
					if (mb_strpos($match, 'i:nil="true"') == false) {
						$xml = str_replace($match, '', $xml);
					}
				}
			}*/

			try {
				$this->exec('emk', $caseParams['function'], null, $xml);
			} catch (Exception $e) {
				if (!empty($e->detail->RequestFault->ErrorCode) && $e->detail->RequestFault->ErrorCode == 31) {
					// Случай обслуживания уже существует, пропускаем дальше
				} else {
					throw $e;
				}
			}

			$caseId = -1; // идентификатор никакой не возвращается.
			$this->saveSyncObject('EvnPS', $EvnPS_id, $caseId);
		}

		return $caseId;
	}

	/**
	 * Тест
	 */
	function test($data)
	{
		try {
			$this->runSyncAll($data);
		} catch (Exception $e) {
			if (!empty($e->detail)) {
				var_export($e->detail);
			} else {
				var_dump($e);
			}
		}
	}

	/**
	 * Отправка всех изменённых ТАП/КВС/ДД после последней отпрваки.
	 */
	function syncAll($data)
	{
		// получаем настройки
		$this->load->model('Options_model');
		$options = $this->Options_model->getOptionsGlobals($data);

		// получаем время последнего запуска
		switch (checkMongoDb()) { // будем юзать монгу под хранение времени последнего запуска, а может и ещё чего либо..
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
			default:
				return array('Error_Msg' => 'The MongoDB PECL extension has not been installed or enabled.');
				break;
		}
		$rows = $this->mongo_db->where(array('code' => 'last_time'))->get('sysMisRb');
		// $rows = null; // для тестов

		$data['lastTime'] = time() - 2 * 24 * 60 * 60; // за неделю
		if (!empty($rows[0]['value'])) {
			$data['lastTime'] = $rows[0]['value'];
		}

		if (!empty($data['ignoreSettings'])) {
			$this->runSyncAll($data);
			return true;
		}

		if (!empty($options['globals']['misrb_transfer_type'])) { // способ передачи
			switch ($options['globals']['misrb_transfer_type']) {
				case 2: // ежедневно
					$misrb_transfer_time = !empty($options['globals']['misrb_transfer_time']) ? $options['globals']['misrb_transfer_time'] : '00:00'; // время
					if (time() > strtotime(date('Y-m-d ' . $misrb_transfer_time))) {
						// если текущее время больше чем время запуска и ещё сегодня не запускалось, то запускаем
						if (empty($rows[0]['value']) || date('d.m.Y', $rows[0]['value']) != date('d.m.Y')) {
							// сохраняем время последнего запуска
							if (!empty($rows[0]['_id'])) {
								$this->mongo_db->where(array('_id' => $rows[0]['_id']))->update('sysMisRb', array(
									'code' => 'last_time',
									'value' => time()
								));
							} else {
								$this->mongo_db->insert('sysMisRb', array(
									'code' => 'last_time',
									'value' => time()
								));
							}

							$this->runSyncAll($data);
						}
					}
					break;
				case 3: // еженедельно
					$misrb_transfer_time = !empty($options['globals']['misrb_transfer_time']) ? $options['globals']['misrb_transfer_time'] : '00:00'; // время
					$misrb_transfer_day = !empty($options['globals']['misrb_transfer_day']) ? $options['globals']['misrb_transfer_day'] : '7'; // день недели
					if (time() > strtotime(date('Y-m-0' . $misrb_transfer_day . ' ' . $misrb_transfer_time))) {
						// если текущее время больше чем время запуска и ещё на этой неделе не запускалось, то запускаем
						if (empty($rows[0]['value']) || date('w.m.Y', $rows[0]['value']) != date('w.m.Y')) {
							// сохраняем время последнего запуска
							if (!empty($rows[0]['_id'])) {
								$this->mongo_db->where(array('_id' => $rows[0]['_id']))->update('sysMisRb', array(
									'code' => 'last_time',
									'value' => time()
								));
							} else {
								$this->mongo_db->insert('sysMisRb', array(
									'code' => 'last_time',
									'value' => time()
								));
							}

							$this->runSyncAll($data);
						}
					}
					break;
			}
		}
	}

	/**
	 * запуск синхронизации всего
	 */
	function runSyncAll($data)
	{
		$this->textlog->add("runSyncAll begin");
		// поиск всех ТАП/КВС/ДД изменившихся с момента lastTime

		$filter = "";
		$queryParams = array();
		if (!empty($data['Evn_id'])) {
			$filter .= " and Evn_id = :Evn_id";
			$queryParams['Evn_id'] = $data['Evn_id'];
		} else {
			$filter .= " and Evn_updDT >= :lastDate";
			$queryParams['lastDate'] = date('Y-m-d H:i:s', $data['lastTime']);
		}

		$query = "
			select
				Evn_id as \"Evn_id\",
				EvnClass_SysNick as \"EvnClass_SysNick\",
				Lpu_id as \"Lpu_id\"
			from
				v_Evn
			where
				EvnClass_id in (3, 30, 101, 103) -- ТАП, КВС, карта ДД, карта профосмотра
				{$filter}
		";

		$resp = $this->queryResult($query, $queryParams);

		$this->textlog->add("runSyncAll count: " . count($resp));

		foreach ($resp as $respone) {
			$this->_idLPU = $this->getIdLpu($respone['Lpu_id']);
			if (empty($this->_idLPU)) {
				continue;
			}

			try {
				switch ($respone['EvnClass_SysNick']) {
					case 'EvnPS':
						$this->syncEvnPS($respone['Evn_id']);
						break;
					case 'EvnPL':
						$this->syncEvnPL($respone['Evn_id']);
						break;
					case 'EvnPLDispDop13':
						$this->syncEvnPLDispDop13($respone['Evn_id']);
						break;
					case 'EvnPLDispProf':
						$this->syncEvnPLDispProf($respone['Evn_id']);
						break;
				}
			} catch (Exception $e) {
				// падать не будем, просто пишем в лог инфу и идем дальше
				$this->textlog->add("runSyncAll error: code: " . $e->getCode() . " message: " . $e->getMessage());

				$code = $e->getCode();
				$message = $e->getMessage();
				if (!empty($e->detail)) {
					$detail = serialize($e->detail);
					preg_match_all("/PropertyName\";s:[0-9]+:\"(.*?)\".*?Message\";s:[0-9]+:\"(.*?)\"/ui", $detail, $matches);
					if (!empty($matches[2])) {
						foreach ($matches[2] as $key => $match) {
							if ($match != "Поле содержит ошибки") {
								$message .= ", '{$matches[1][$key]}': {$matches[2][$key]}";
							}
						}
					}
				}

				if (!empty($_REQUEST['getDebug'])) {
					var_dump($e);
				}

				$selectString = "
						miserror_id as \"MISError_id\",
                        error_code as \"Error_Code\", 
                        error_message as \"Error_Msg\"
                    ";

				$this->db->query("
					select {$selectString}
					from r3.p_MISError_ins (						
						MISError_setDT := :MISError_setDT,
						Lpu_id := :Lpu_id,
						MISError_QueryName := :MISError_QueryName,
						MISError_ErrorCode := :MISError_ErrorCode,
						MISError_ErrorMessage := :MISError_ErrorMessage,
						Evn_id := :Evn_id,
						pmUser_id := :pmUser_id
				);
				", array(
					'Lpu_id' => $respone['Lpu_id'],
					'MISError_setDT' => date('Y-m-d H:i:s'),
					'MISError_QueryName' => $this->lastQueryType,
					'MISError_ErrorCode' => $code,
					'MISError_ErrorMessage' => $message,
					'Evn_id' => $respone['Evn_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				// $this->textlog->add("runSyncAll error: code: ".$e->getCode()." message: ".$e->getMessage()." trace: ".$e->getTraceAsString() );
			}
		}

		$this->textlog->add("runSyncAll end");
	}

	/**
	 * Получение списка ошибок
	 */
	function loadMISErrorGrid($data)
	{
		$queryParams = array();
		$filter = "";

		if (!empty($data['MISError_setDT_From'])) {
			$queryParams['MISError_setDT_From'] = $data['MISError_setDT_From'];
			$filter .= " and cast(me.MISError_setDT as date) >= :MISError_setDT_From";
		}

		if (!empty($data['MISError_setDT_To'])) {
			$queryParams['MISError_setDT_To'] = $data['MISError_setDT_To'];
			$filter .= " and cast(me.MISError_setDT as date) <= :MISError_setDT_To";
		}

		$query = "
			select
				-- select
				me.MISError_id as \"MISError_id\",
				to_char(me.MISError_setDT, 'dd.mm.yyyy') || ' ' || to_char(me.MISError_setDT, 'hh24:mi') as \"MISError_setDT\",
				l.Lpu_Nick as \"Lpu_Nick\",
				me.MISError_QueryName as \"MISError_QueryName\",
				me.MISError_ErrorCode as \"MISError_ErrorCode\",
				me.MISError_ErrorMessage as \"MISError_ErrorMessage\",
				me.Evn_id as \"Evn_id\"
				-- end select
			from
				-- from
				r3.v_MISError me
				left join v_Lpu l on l.Lpu_id = me.Lpu_id
				-- end from
			where
				-- where
				1=1
				{$filter}
				-- end where
			order by
				-- order by
				me.MISError_id desc
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');

			$count = count($response['data']);
			if (intval($data['start']) != 0 || $count >= intval($data['limit'])) { // считаем каунт только если записей не меньше, чем лимит или не первая страничка грузится
				$result_count = $this->db->query(getCountSQLPH($query), $queryParams);

				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			}

			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение пациента из ИМЭК
	 */
	function getPersonFromIEMK($data)
	{
		$this->_idLPU = $this->getIdLpu($data['Lpu_id']);

		$IdSource = 'Reg';
		if ($data['type'] == 'fed') {
			$IdSource = 'Fed';
		}

		$personInfo = $this->getPersonInfo(array(
			'Person_id' => $data['Person_id']
		));

		$patientParams = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLPU' => $this->_idLPU,
			'idSource' => $IdSource,
			'patient' => array(
				'idPatientMIS' => $personInfo['Person_id'],
				'FamilyName' => $personInfo['Person_SurName'],
				'GivenName' => $personInfo['Person_FirName'],
				'MiddleName' => $personInfo['Person_SecName'],
				'Sex' => $personInfo['Sex_Code']
			)
		);

		$resp = $this->exec('patient', 'GetPatient', $patientParams);
		if (isset($resp->GetPatientResult->PatientDto)) {
			if (is_array($resp->GetPatientResult->PatientDto)) {
				// если нашли более 1-го, то
				return array('Error_Msg' => 'По указанным данным не удалось идентифицировать пациента в ИЭМК.');
			} else {
				// Если по указанному пациенту метод вернул единственную запись и данные в Промеде не совпадают с данными в «ИЭМК»,
				// то выводить сообщение: «По указанному пациенту из ИЭМК полученные следующие данные: %Список данных из ИЭМК, несовпадающих с данными Промеда (%соответсвующее значение из Промеда).
				// чтобы понять что приходит в ответ нужен пример реального человека %) в ТЗ одно написано, в WSDL другое todo
				var_dump($resp->GetPatientResult->PatientDto);
			}
		}

		return array('Error_Msg' => 'По указанным данным пациент в ИЭМК не найден или данные найденного пациента совпадают с данными в МИС.');
	}

	/**
	 * Получение направлений из ИМЭК
	 */
	function loadEvnDirectionIEMKList($data)
	{
		$this->_idLPU = $this->getIdLpu($data['Lpu_id']);

		$patientParams = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $this->_idLPU,
			'idReferralType' => $data['DirectionType_id'],
			'startDate' => $data['EvnDirectionIEMK_setDT_From'],
			'endDate' => $data['EvnDirectionIEMK_setDT_To']
		);

		$resp = $this->exec('emk', 'GetReferralList', $patientParams);
		// чтобы понять что приходит в ответ нужен пример реальной МО у которой есть направления
		// var_dump($resp->GetReferralListResult);

		return array();
	}

	/**
	 * Получение регионов
	 */
	function getDistrictList($data)
	{
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
		);

		$response = $this->exec('hub', 'GetDistrictList', $params);

		$resp = array();

		if (!empty($response->GetDistrictListResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->GetDistrictListResult->ErrorList->Error->ErrorDescription));
		}

		foreach ($response->GetDistrictListResult->District as $one) {
			$resp[] = array(
				'IdDistrict' => $one->IdDistrict,
				'DistrictName' => $one->DistrictName,
				'Okato' => $one->Okato
			);
		}

		return $resp;
	}

	/**
	 * Получение МО
	 */
	function getLpuList($data)
	{
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
		);

		if (!empty($data['IdDistrict'])) {
			$params['IdDistrict'] = $data['IdDistrict'];
		}

		$response = $this->exec('hub', 'GetLPUList', $params);

		$resp = array();

		$LpuTypeArray = array();
		$resp_lputype = $this->queryResult("
			select
				MISLpuType_id as \"MISLpuType_id\",
				MISLpuType_Name as \"MISLpuType_Name\"
			from
				r3.MISLpuType
		");
		foreach ($resp_lputype as $resp_lputype_one) {
			$LpuTypeArray[$resp_lputype_one['MISLpuType_id']] = $resp_lputype_one['MISLpuType_Name'];
		}

		if (!empty($response->GetLPUListResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->GetLPUListResult->ErrorList->Error->ErrorDescription));
		}

		if (isset($response->GetLPUListResult->ListLPU->Clinic)) {
			$result = $response->GetLPUListResult->ListLPU->Clinic;
			if (!is_array($result)) {
				$result = array($result);
			}

			foreach ($result as $one) {
				$resp[] = array(
					'IdLPU' => $one->IdLPU,
					'LPUFullName' => $one->LPUFullName,
					'LPUShortName' => $one->LPUShortName,
					'LPUType' => (!empty($one->LPUType) && !empty($LpuTypeArray[$one->LPUType])) ? $LpuTypeArray[$one->LPUType] : ''
				);
			}
		}

		return $resp;
	}

	/**
	 * Получение специальностей
	 */
	function getSpesialityList($data)
	{
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['IdLPU']
		);

		$response = $this->exec('hub', 'GetSpesialityList', $params);

		$resp = array();

		if (!empty($response->GetSpesialityListResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->GetSpesialityListResult->ErrorList->Error->ErrorDescription));
		}

		if (isset($response->GetSpesialityListResult->ListSpesiality->Spesiality)) {
			$result = $response->GetSpesialityListResult->ListSpesiality->Spesiality;
			if (!is_array($result)) {
				$result = array($result);
			}

			foreach ($result as $one) {
				$resp[] = array(
					'IdSpesiality' => $one->IdSpesiality,
					'NameSpesiality' => $one->NameSpesiality,
					'CountFreeTicket' => $one->CountFreeTicket,
					'CountFreeParticipantIE' => $one->CountFreeParticipantIE,
					'LastDate' => $one->LastDate,
					'NearestDate' => $one->NearestDate
				);
			}
		}

		return $resp;
	}

	/**
	 * Получение врачей
	 */
	function getDoctorList($data)
	{
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idSpesiality' => $data['IdSpesiality'],
			'idLpu' => $data['IdLPU']
		);

		$response = $this->exec('hub', 'GetDoctorList', $params);

		$resp = array();

		if (!empty($response->GetDoctorListResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->GetDoctorListResult->ErrorList->Error->ErrorDescription));
		}

		if (isset($response->GetDoctorListResult->Docs->Doctor)) {
			$result = $response->GetDoctorListResult->Docs->Doctor;
			if (!is_array($result)) {
				$result = array($result);
			}

			foreach ($result as $one) {
				$resp[] = array(
					'IdDoc' => $one->IdDoc,
					'Snils' => $one->Snils,
					'Name' => $one->Name,
					'CountFreeTicket' => $one->CountFreeTicket,
					'CountFreeParticipantIE' => $one->CountFreeParticipantIE,
					'LastDate' => $one->LastDate,
					'NearestDate' => $one->NearestDate
				);
			}
		}

		return $resp;
	}

	/**
	 * Получение дат
	 */
	function getDateList($data)
	{
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idDoc' => $data['IdDoc'],
			'idLpu' => $data['IdLPU'],
			'visitStart' => date('Y-m-d', time() + 24 * 60 * 60),
			'visitEnd' => date('Y-m-d', time() + 15 * 24 * 60 * 60)
		);

		$response = $this->exec('hub', 'GetAvailableDates', $params);

		$resp = array();
		$IdDate = 1;

		if (!empty($response->GetAvailableDatesResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->GetAvailableDatesResult->ErrorList->Error->ErrorDescription));
		}

		if (isset($response->GetAvailableDatesResult->AvailableDateList->dateTime)) {
			$result = $response->GetAvailableDatesResult->AvailableDateList->dateTime;
			if (!is_array($result)) {
				$result = array($result);
			}

			foreach ($result as $one) {
				$resp[] = array(
					'IdDate' => $IdDate++,
					'Date' => date('d.m.Y', strtotime($one))
				);
			}
		}

		return $resp;
	}

	/**
	 * Получение времени
	 */
	function getAppointmentList($data)
	{
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idDoc' => $data['IdDoc'],
			'idLpu' => $data['IdLPU'],
			'visitStart' => date('Y-m-d', strtotime($data['Date'])),
			'visitEnd' => date('Y-m-d', strtotime($data['Date']))
		);

		$response = $this->exec('hub', 'GetAvaibleAppointments', $params);

		$resp = array();

		if (!empty($response->GetAvaibleAppointmentsResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->GetAvaibleAppointmentsResult->ErrorList->Error->ErrorDescription));
		}

		if (isset($response->GetAvaibleAppointmentsResult->ListAppointments->Appointment)) {
			$result = $response->GetAvaibleAppointmentsResult->ListAppointments->Appointment;
			if (!is_array($result)) {
				$result = array($result);
			}

			foreach ($result as $one) {
				$resp[] = array(
					'IdAppointment' => $one->IdAppointment,
					'VisitStart' => date('H:i', strtotime($one->VisitStart))
				);
			}
		}

		return $resp;
	}

	/**
	 * Идентификация пациента
	 */
	function identPerson($data)
	{
		// получаем необходимые данные по человеку
		$resp = $this->queryResult("
			select
				ps.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				to_char(ps.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				ps.Document_Num as \"Document_Num\",
				ps.Document_Ser as \"Document_Ser\",
				ps.PolisType_id as \"PolisType_id\",
				case when ps.PolisType_id = 4
					then ps.Person_edNum else ps.Polis_Num
				end as \"Polis_Num\",
				ps.Polis_Ser as \"Polis_Ser\"
			from
				v_PersonState ps
			where
				Person_id = :Person_id
		", array(
			'Person_id' => $data['Person_id']
		));

		if (empty($resp[0]['Person_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		if (empty($resp[0]['Person_SurName'])) {
			return array('Error_Msg' => 'Нет значения обязательного поля "Фамилия пациента". Запись пациента в стороннюю МИС невозможна. Укажите значения для поля "Фамилия пациента" и повторите попытку записи пациента.');
		}

		if (empty($resp[0]['Person_FirName'])) {
			return array('Error_Msg' => 'Нет значения обязательного поля "Имя пациента". Запись пациента в стороннюю МИС невозможна. Укажите значения для поля "Имя пациента" и повторите попытку записи пациента.');
		}

		if (empty($resp[0]['Person_Birthday'])) {
			return array('Error_Msg' => 'Нет значения обязательного поля "Дата рождения". Запись пациента в стороннюю МИС невозможна. Укажите значения для поля "Дата рождения" и повторите попытку записи пациента.');
		}

		if (empty($resp[0]['Polis_Num'])) {
			return array('Error_Msg' => 'Запись пациента невозможна. Отсутствует информация о полисе. Укажите данные полиса и повторите попытку записи пациента.');
		}

		$found = false;

		// 1 запрос ФИО/ДР/документ/полис
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'SecondName' => $resp[0]['Person_SecName'],
				'Document_N' => $resp[0]['Document_Num'],
				'Document_S' => $resp[0]['Document_Ser'],
				'Polis_N' => $resp[0]['Polis_Num'],
				'Polis_S' => $resp[0]['Polis_Ser']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}
		// 2 запрос ФИ/ДР/документ/полис
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'Document_N' => $resp[0]['Document_Num'],
				'Document_S' => $resp[0]['Document_Ser'],
				'Polis_N' => $resp[0]['Polis_Num'],
				'Polis_S' => $resp[0]['Polis_Ser']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}
		// 3 запрос ФИО/ДР/полис
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'SecondName' => $resp[0]['Person_SecName'],
				'Polis_N' => $resp[0]['Polis_Num'],
				'Polis_S' => $resp[0]['Polis_Ser']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}
		// 4 запрос ФИ/ДР/полис
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'Polis_N' => $resp[0]['Polis_Num'],
				'Polis_S' => $resp[0]['Polis_Ser']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}
		// 5 запрос ФИО/ДР/документ
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'SecondName' => $resp[0]['Person_SecName'],
				'Document_N' => $resp[0]['Document_Num'],
				'Document_S' => $resp[0]['Document_Ser']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}
		// 6 запрос ФИ/ДР/документ
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'Document_N' => $resp[0]['Document_Num'],
				'Document_S' => $resp[0]['Document_Ser']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}
		// 7 запрос ФИО/ДР
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'SecondName' => $resp[0]['Person_SecName']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}
		// 8 запрос ФИ/ДР
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'pat' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName']
			)
		);
		$response = $this->exec('hub', 'CheckPatient', $params);
		if ($response->CheckPatientResult->Success) {
			if (!is_array($response->CheckPatientResult->IdPat)) {
				return $response->CheckPatientResult->IdPat;
			} else {
				$found = true;
			}
		}

		// если нашли, но не идентифицировали
		if ($found) {
			return array('Error_Msg' => 'Пациент ' . $resp[0]['Person_SurName'] . ' ' . $resp[0]['Person_FirName'] . ' ' . $resp[0]['Person_SecName'] . ' ' . ' не идентифицирован в сторонней МИС. Проверьте данные пациента и повторите процедура записи на прием.');
		}

		// не нашли, значит создаём нового
		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idLpu' => $data['idLpu'],
			'patient' => array(
				'Birthday' => date('Y-m-d', strtotime($resp[0]['Person_Birthday'])),
				'Name' => $resp[0]['Person_FirName'],
				'Surname' => $resp[0]['Person_SurName'],
				'SecondName' => $resp[0]['Person_SecName'],
				'Document_N' => $resp[0]['Document_Num'],
				'Document_S' => $resp[0]['Document_Ser'],
				'Polis_N' => $resp[0]['Polis_Num'],
				'Polis_S' => $resp[0]['Polis_Ser']
			)
		);
		$response = $this->exec('hub', 'AddNewPatient', $params);

		if (!empty($response->AddNewPatientResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->AddNewPatientResult->ErrorList->Error->ErrorDescription, 'AddNewPatient'));
		}

		if (isset($response->AddNewPatientResult->IdPat)) {
			return $response->AddNewPatientResult->IdPat;
		} else {
			return array('Error_Msg' => 'Ошибка добавления нового пациента');
		}
	}

	/**
	 * Постановка в очередь
	 */
	function setWaitingList($data)
	{
		// определяем пациента
		$idPat = $this->identPerson($data);
		if (!empty($idPat['Error_Msg'])) {
			return array('Error_Msg' => $idPat['Error_Msg']);
		}

		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idSpesiality' => $data['idSpesiality'],
			'nameSpesiality' => $data['nameSpesiality'],
			'idLpu' => $data['idLpu'],
			'idPat' => $idPat,
			'rule' => array(
				'Start' => date('Y-m-d', time() + 24 * 60 * 60),
				'End' => date('Y-m-d', time() + 15 * 24 * 60 * 60)
			)
		);

		if (!empty($data['idDoc'])) {
			$params['idDoc'] = $data['idDoc'];
			$params['nameDoc'] = $data['nameDoc'];
		}

		$response = $this->exec('hub', 'SetWaitingList', $params);

		if (!empty($response->SetWaitingListResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->SetWaitingListResult->ErrorList->Error->ErrorDescription));
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Запись
	 */
	function setAppointment($data)
	{
		// определяем пациента
		$idPat = $this->identPerson($data);
		if (!empty($idPat['Error_Msg'])) {
			return array('Error_Msg' => $idPat['Error_Msg']);
		}

		$params = array(
			'guid' => '???', // заполнится позже, при отправке.
			'idAppointment' => $data['idAppointment'],
			'idLpu' => $data['idLpu'],
			'idPat' => $idPat
		);

		$response = $this->exec('hub', 'SetAppointment', $params);

		if (!empty($response->SetAppointmentResult->ErrorList->Error->ErrorDescription)) {
			return array('Error_Msg' => $this->processErrorDescription($response->SetAppointmentResult->ErrorList->Error->ErrorDescription, 'SetAppointment'));
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Обработка ошибок
	 */
	function processErrorDescription($errorMsg, $method = null)
	{
		switch ($errorMsg) {
			case 'Талон с указанным номером не существует или уже отменен':
				return 'Выбранная бирка уже занята, выберете другое время для приема.';
				break;
			case 'Указан недопустимый идентификатор специальности':
				return 'Необходимо выбрать другую специальность.';
				break;
			case 'Указан недопустимый идентификатор врача':
				return 'Необходимо выбрать другого врача.';
				break;
			case 'Пациент уже имеет запись на это время к другому врачу':
				return 'Пациент записан на это время к другому врачу. Выберите другую дату и время приема.';
				break;
			case 'Талон к врачу занят/заблокирован':
				return 'Выбранная бирка уже занята, выберете другое время для приема.';
				break;
			case 'Указан недопустимый идентификатор талона на запись':
				return 'Необходимо уточнить параметры записи пациента – перевыбрать дату и время приема.';
				break;
			case 'Несоответствие сроков действия полиса ОМС':
				return 'Несоответствие сроков действия полиса ОМС. Проверьте данные пациента и повторите попытку записи.';
				break;
			case 'Запись запрещена':
				switch ($method) {
					case 'SetAppointment':
						return 'Запись на бирку не возможна, выберете другие параметры записи.';
						break;
					case 'AddNewPatient':
						return 'Запись в МИС невозможна.';
						break;
				}
				break;
			case 'Учреждение с данным идентификатором отсутствует в справочнике':
				return 'Не найден идентификатор МО. Запись пациента на прием невозможна. Обратитесь к администратору системы.';
				break;
			case 'Отсутствует доступ или не найдена конечная точка':
				return 'Необходимо обратиться к администратору системы. Запись пациента невозможна.';
				break;
			case 'Не был указан/указан неверно guid при вызове метода':
				return 'Необходимо обратиться к администратору системы. Запись пациента невозможна.';
				break;
		}
		return $errorMsg;
	}

	/**
	 * Получение объектов MedRecord типа SickList (ЛВН)
	 */
	function getSickList($data)
	{
		$result = "";

		$evnStickInfos = $this->getEvnStickInfo(array(
			'EvnStick_pid' => $data['EvnStick_pid']
		));
		foreach ($evnStickInfos as $evnStickInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_sicklist', array(
					'CreationDate' => $evnStickInfo['EvnStick_setDate'],
					'Header' => 'Инструментальное исследование',
					'Author/IdLpu' => $this->getIdLpu($evnStickInfo['MPLpu_id']),
					'Author/IdSpeciality' => $evnStickInfo['MPMedSpecOms_Code'],
					'Author/IdPosition' => $evnStickInfo['MPPost_Code'],
					'Author/Sex' => $evnStickInfo['MPSex_Code'],
					'Author/Birthdate' => $evnStickInfo['MPPerson_BirthDay'],
					'Author/IdPersonMis' => $evnStickInfo['MPPerson_id'],
					'Author/FamilyName' => $evnStickInfo['MPPerson_SurName'],
					'Author/GivenName' => $evnStickInfo['MPPerson_FirName'],
					'Author/MiddleName' => $evnStickInfo['MPPerson_SecName'],
					'Author/Documents' => array(),
					'Number' => $evnStickInfo['EvnStick_Num'],
					'DateStart' => $evnStickInfo['EvnStickWorkRelease_begDate'],
					'DateEnd' => $evnStickInfo['EvnStickWorkRelease_endDate'],
					'IsPatientTaker' => 'true',
					'Guardian/IdLpu' => $this->getIdLpu($evnStickInfo['MPLpu_id']),
					'Guardian/IdSpeciality' => $evnStickInfo['MPMedSpecOms_Code'],
					'Guardian/IdPosition' => $evnStickInfo['MPPost_Code'],
					'Guardian/Sex' => $evnStickInfo['MPSex_Code'],
					'Guardian/Birthdate' => $evnStickInfo['MPPerson_BirthDay'],
					'Guardian/IdPersonMis' => $evnStickInfo['MPPerson_id'],
					'Guardian/FamilyName' => $evnStickInfo['MPPerson_SurName'],
					'Guardian/GivenName' => $evnStickInfo['MPPerson_FirName'],
					'Guardian/MiddleName' => $evnStickInfo['MPPerson_SecName'],
					'Guardian/Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа PacsResult (Инструментальное исследование)
	 */
	function getPacsResult($data)
	{
		$result = "";

		$evnUslugaInfos = $this->getEvnUslugaFuncInfo(array(
			'EvnUsluga_pid' => $data['EvnUsluga_pid']
		));
		foreach ($evnUslugaInfos as $evnUslugaInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_pacsresult', array(
					'CreationDate' => $evnUslugaInfo['EvnUsluga_setDate'],
					'IdDocumentMis' => $evnUslugaInfo['EvnUsluga_id'],
					'Code' => $evnUslugaInfo['UslugaComplex_Code'],
					'Header' => 'Инструментальное исследование',
					'UID' => '',
					'PACS' => '',
					'Report' => '',
					'Description' => '',
					'Conclusion' => 'Заключение отсутствует',
					'Data' => '',
					'MIMEType' => 'text/html',
					'IdLpu' => $this->getIdLpu($evnUslugaInfo['MPLpu_id']),
					'IdSpeciality' => $evnUslugaInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnUslugaInfo['MPPost_Code'],
					'Sex' => $evnUslugaInfo['MPSex_Code'],
					'Birthdate' => $evnUslugaInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnUslugaInfo['MPPerson_id'],
					'FamilyName' => $evnUslugaInfo['MPPerson_SurName'],
					'GivenName' => $evnUslugaInfo['MPPerson_FirName'],
					'MiddleName' => $evnUslugaInfo['MPPerson_SecName'],
					'Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа LaboratoryReport (Лабораторные исследования)
	 */
	function getLaboratoryReport($data)
	{
		$result = "";

		$evnUslugaInfos = $this->getEvnUslugaLabInfo(array(
			'EvnUsluga_pid' => $data['EvnUsluga_pid']
		));
		foreach ($evnUslugaInfos as $evnUslugaInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_laboratoryreport', array(
					'CreationDate' => $evnUslugaInfo['EvnUsluga_setDate'],
					'Header' => 'Лабораторное исследование',
					'IdLpu' => $this->getIdLpu($evnUslugaInfo['MPLpu_id']),
					'IdSpeciality' => $evnUslugaInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnUslugaInfo['MPPost_Code'],
					'Sex' => $evnUslugaInfo['MPSex_Code'],
					'Birthdate' => $evnUslugaInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnUslugaInfo['MPPerson_id'],
					'FamilyName' => $evnUslugaInfo['MPPerson_SurName'],
					'GivenName' => $evnUslugaInfo['MPPerson_FirName'],
					'MiddleName' => $evnUslugaInfo['MPPerson_SecName'],
					'Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа ConsultNote (Консультационные услуги)
	 */
	function getConsultNote($data)
	{
		$result = "";

		$evnUslugaInfos = $this->getEvnUslugaConsultInfo(array(
			'EvnUsluga_pid' => $data['EvnUsluga_pid']
		));
		foreach ($evnUslugaInfos as $evnUslugaInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_consultnote', array(
					'CreationDate' => $evnUslugaInfo['EvnUsluga_setDate'],
					'Header' => 'Выполнение консультационной услуги',
					'IdLpu' => $this->getIdLpu($evnUslugaInfo['MPLpu_id']),
					'IdSpeciality' => $evnUslugaInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnUslugaInfo['MPPost_Code'],
					'Sex' => $evnUslugaInfo['MPSex_Code'],
					'Birthdate' => $evnUslugaInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnUslugaInfo['MPPerson_id'],
					'FamilyName' => $evnUslugaInfo['MPPerson_SurName'],
					'GivenName' => $evnUslugaInfo['MPPerson_FirName'],
					'MiddleName' => $evnUslugaInfo['MPPerson_SecName'],
					'Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа AppointedMedication (данные о выписанных рецептах)
	 */
	function getAppointedMedication($data)
	{
		$result = "";

		$evnReceptInfos = $this->getEvnReceptInfo(array(
			'EvnRecept_pid' => $data['EvnRecept_pid']
		));
		foreach ($evnReceptInfos as $evnReceptInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_appointedmedication', array(
					'AnatomicTherapeuticChemicalClassification' => preg_replace('/([A-Z0-9]*)\s.*/ui', '$1', $evnReceptInfo['CLSATC_NAME']),
					'IssuedDate' => $evnReceptInfo['EvnRecept_setDate'],
					'MedicineIssueType' => 'PRE', // Рецепт
					'MedicineName' => $evnReceptInfo['DrugComplexMnn_RusName'],
					'Number' => $evnReceptInfo['EvnRecept_Num'],
					'Seria' => $evnReceptInfo['EvnRecept_Ser'],
					'IdLpu' => $this->getIdLpu($evnReceptInfo['MPLpu_id']),
					'IdSpeciality' => $evnReceptInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnReceptInfo['MPPost_Code'],
					'Sex' => $evnReceptInfo['MPSex_Code'],
					'Birthdate' => $evnReceptInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnReceptInfo['MPPerson_id'],
					'FamilyName' => $evnReceptInfo['MPPerson_SurName'],
					'GivenName' => $evnReceptInfo['MPPerson_FirName'],
					'MiddleName' => $evnReceptInfo['MPPerson_SecName'],
					'Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа Service (данные о выполненных услугах)
	 */
	function getService($data)
	{
		$result = "";

		$evnUslugaInfos = $this->getEvnUslugaInfo(array(
			'EvnUsluga_pid' => $data['EvnUsluga_pid']
		));
		foreach ($evnUslugaInfos as $evnUslugaInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_service', array(
					'DateEnd' => !empty($evnUslugaInfo['EvnUsluga_disDate']) ? $evnUslugaInfo['EvnUsluga_disDate'] : $evnUslugaInfo['EvnUsluga_setDate'],
					'DateStart' => $evnUslugaInfo['EvnUsluga_setDate'],
					'IdServiceType' => $evnUslugaInfo['UslugaComplex_Code'],
					'ServiceName' => $evnUslugaInfo['UslugaComplex_Name'],
					'IdRole' => 3, // Врач
					'IdLpu' => $this->getIdLpu($evnUslugaInfo['MPLpu_id']),
					'IdSpeciality' => $evnUslugaInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnUslugaInfo['MPPost_Code'],
					'Sex' => $evnUslugaInfo['MPSex_Code'],
					'Birthdate' => $evnUslugaInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnUslugaInfo['MPPerson_id'],
					'FamilyName' => $evnUslugaInfo['MPPerson_SurName'],
					'GivenName' => $evnUslugaInfo['MPPerson_FirName'],
					'MiddleName' => $evnUslugaInfo['MPPerson_SecName'],
					'Documents' => array(),
					'IdPaymentType' => $this->getSyncSpr('PayType', $evnUslugaInfo['PayType_id']),
					'PaymentState' => 0, // не принято решение об оплате
					'HealthCareUnit' => 17, // Медицинская услуга
					'Quantity' => $evnUslugaInfo['EvnUsluga_KolVo'],
					'Tariff' => !empty($evnUslugaInfo['UslugaComplexTariff_Tariff']) ? $evnUslugaInfo['UslugaComplexTariff_Tariff'] : '0.0' // Выгружать значение тарифа. Если тариф не указан, то выгружать «0.0»
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа ClinicMainDiagnosis (данные о сопутствующих диагнозах)
	 */
	function getClinicMainDiagnosis($data)
	{
		$result = "";

		if (!empty($data['EvnVizitPL_id'])) {
			$evnDiagPLSopInfos = $this->getEvnDiagPLSopInfo(array(
				'EvnDiagPLSop_pid' => $data['EvnVizitPL_id']
			));
			foreach ($evnDiagPLSopInfos as $evnDiagPLSopInfo) {
				$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_clinicmaindiagnosis', array(
						'IdDiseaseType' => $this->getSyncSpr('DeseaseType', $evnDiagPLSopInfo['DeseaseType_id'], true, true),
						'DiagnosedDate' => $evnDiagPLSopInfo['EvnDiagPLSop_setDate'],
						'IdDiagnosisType' => $this->getSyncSpr('DiagSetClass', $evnDiagPLSopInfo['DiagSetClass_id'], false, true),
						'Comment' => "Сопутствующий диагноз",
						'IdTraumaType' => '',
						'MkbCode' => $evnDiagPLSopInfo['Diag_Code'],
						'IdLpu' => $this->getIdLpu($evnDiagPLSopInfo['MPLpu_id']),
						'IdSpeciality' => $evnDiagPLSopInfo['MPMedSpecOms_Code'],
						'IdPosition' => $evnDiagPLSopInfo['MPPost_Code'],
						'Sex' => $evnDiagPLSopInfo['MPSex_Code'],
						'Birthdate' => $evnDiagPLSopInfo['MPPerson_BirthDay'],
						'IdPersonMis' => $evnDiagPLSopInfo['MPPerson_id'],
						'FamilyName' => $evnDiagPLSopInfo['MPPerson_SurName'],
						'GivenName' => $evnDiagPLSopInfo['MPPerson_FirName'],
						'MiddleName' => $evnDiagPLSopInfo['MPPerson_SecName'],
						'Documents' => array()
					), true);
			}
		}

		if (!empty($data['EvnSection_id'])) {
			$evnDiagPSInfos = $this->getEvnDiagPSInfo(array(
				'EvnDiagPS_pid' => $data['EvnSection_id']
			));
			foreach ($evnDiagPSInfos as $evnDiagPSInfo) {
				$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_clinicmaindiagnosis', array(
						'IdDiseaseType' => '',
						'DiagnosedDate' => $evnDiagPSInfo['EvnDiagPS_setDate'],
						'IdDiagnosisType' => $this->getSyncSpr('DiagSetClass', $evnDiagPSInfo['DiagSetClass_id'], false, true),
						'Comment' => "Сопутствующий диагноз",
						'IdTraumaType' => '',
						'MkbCode' => $evnDiagPSInfo['Diag_Code'],
						'IdLpu' => $this->getIdLpu($evnDiagPSInfo['MPLpu_id']),
						'IdSpeciality' => $evnDiagPSInfo['MPMedSpecOms_Code'],
						'IdPosition' => $evnDiagPSInfo['MPPost_Code'],
						'Sex' => $evnDiagPSInfo['MPSex_Code'],
						'Birthdate' => $evnDiagPSInfo['MPPerson_BirthDay'],
						'IdPersonMis' => $evnDiagPSInfo['MPPerson_id'],
						'FamilyName' => $evnDiagPSInfo['MPPerson_SurName'],
						'GivenName' => $evnDiagPSInfo['MPPerson_FirName'],
						'MiddleName' => $evnDiagPSInfo['MPPerson_SecName'],
						'Documents' => array()
					), true);
			}
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа Referral (данные об электронном направлении)
	 */
	function getReferral($data)
	{
		$result = "";

		$evnDirectionInfos = $this->getEvnDirectionInfo(array(
			'EvnDirection_rid' => $data['EvnDirection_rid']
		));
		foreach ($evnDirectionInfos as $evnDirectionInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_referral', array(
					'CreationDate' => $evnDirectionInfo['EvnDirection_setDate'],
					'Header' => 'Электронное направление',
					'IdSourceLpu' => $this->getIdLpu($evnDirectionInfo['Lpu_id']),
					'IdTargetLpu' => $this->getIdLpu($evnDirectionInfo['Lpu_did']),
					'Author/IdLpu' => $this->getIdLpu($evnDirectionInfo['MPLpu_id']),
					'Author/IdSpeciality' => $evnDirectionInfo['MPMedSpecOms_Code'],
					'Author/IdPosition' => $evnDirectionInfo['MPPost_Code'],
					'Author/Sex' => $evnDirectionInfo['MPSex_Code'],
					'Author/Birthdate' => $evnDirectionInfo['MPPerson_BirthDay'],
					'Author/IdPersonMis' => $evnDirectionInfo['MPPerson_id'],
					'Author/FamilyName' => $evnDirectionInfo['MPPerson_SurName'],
					'Author/GivenName' => $evnDirectionInfo['MPPerson_FirName'],
					'Author/MiddleName' => $evnDirectionInfo['MPPerson_SecName'],
					'Author/Documents' => array(),
					'Reason' => !empty($evnDirectionInfo['EvnDirection_Descr']) ? $evnDirectionInfo['EvnDirection_Descr'] : 'Обоснование направления',
					'IdReferralMIS' => $evnDirectionInfo['EvnDirection_id'],
					'IdReferralType' => $this->getSyncSpr('DirType', $evnDirectionInfo['DirType_id']),
					'IssuedDateTime' => $evnDirectionInfo['EvnDirection_setDate'],
					'HospitalizationOrder' => ($evnDirectionInfo['DirType_id'] == 1) ? 2 : ($evnDirectionInfo['DirType_id'] == 5 ? 1 : ''),
					'MkbCode' => $evnDirectionInfo['Diag_Code'],
					'DepartmentHead/IdLpu' => $this->getIdLpu($evnDirectionInfo['MPLpu_id']),
					'DepartmentHead/IdSpeciality' => $evnDirectionInfo['MPMedSpecOms_Code'],
					'DepartmentHead/IdPosition' => $evnDirectionInfo['MPPost_Code'],
					'DepartmentHead/Sex' => $evnDirectionInfo['MPSex_Code'],
					'DepartmentHead/Birthdate' => $evnDirectionInfo['MPPerson_BirthDay'],
					'DepartmentHead/IdPersonMis' => $evnDirectionInfo['MPPerson_id'],
					'DepartmentHead/FamilyName' => $evnDirectionInfo['MPPerson_SurName'],
					'DepartmentHead/GivenName' => $evnDirectionInfo['MPPerson_FirName'],
					'DepartmentHead/MiddleName' => $evnDirectionInfo['MPPerson_SecName'],
					'DepartmentHead/Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа AnatomopathologicalMainDiagnosis (Патологоанатомический диагноз)
	 */
	function getAnatomopathologicalMainDiagnosis($data)
	{
		$result = "";

		$evnHistologicProtoInfos = $this->getEvnHistologicProtoInfo(array(
			'Person_id' => $data['Person_id']
		));
		foreach ($evnHistologicProtoInfos as $evnHistologicProtoInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_anatomopathologicalmaindiagnosis', array(
					'IdDiseaseType' => 1, // Острое
					'DiagnosedDate' => $evnHistologicProtoInfo['EvnHistologicProto_didDate'],
					'IdDiagnosisType' => 1, // Основной
					'Comment' => "Основной диагноз",
					'MkbCode' => $evnHistologicProtoInfo['Diag_Code'],
					'IdLpu' => $this->getIdLpu($evnHistologicProtoInfo['MPLpu_id']),
					'IdSpeciality' => $evnHistologicProtoInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnHistologicProtoInfo['MPPost_Code'],
					'Sex' => $evnHistologicProtoInfo['MPSex_Code'],
					'Birthdate' => $evnHistologicProtoInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnHistologicProtoInfo['MPPerson_id'],
					'FamilyName' => $evnHistologicProtoInfo['MPPerson_SurName'],
					'GivenName' => $evnHistologicProtoInfo['MPPerson_FirName'],
					'MiddleName' => $evnHistologicProtoInfo['MPPerson_SecName'],
					'Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа DischargeSummary (Эпикризы)
	 */
	function getDischargeSummary($data)
	{
		$this->load->library('swXmlTemplate');
		$this->load->model('EvnXmlBase_model');

		$result = "";

		$evnXmlInfos = $this->getEvnXmlInfo(array(
			'EvnXml_rid' => $data['EvnXml_rid'],
			'notXmlTypeKind_id' => array(1) // не выписной
		));
		foreach ($evnXmlInfos as $evnXmlInfo) {
			$xml_data = $this->EvnXmlBase_model->doLoadPrintData(array('EvnXml_id' => $evnXmlInfo['EvnXml_id']));
			$html = swXmlTemplate::getHtmlDoc($xml_data[0], true);

			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_dischargesummary', array(
					'CreationDate' => $evnXmlInfo['EvnXml_insDate'],
					'Header' => 'Эпикриз',
					'Data' => base64_encode($html),
					'MIMEType' => 'text/html',
					'IdLpu' => $this->getIdLpu($evnXmlInfo['MPLpu_id']),
					'IdSpeciality' => $evnXmlInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnXmlInfo['MPPost_Code'],
					'Sex' => $evnXmlInfo['MPSex_Code'],
					'Birthdate' => $evnXmlInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnXmlInfo['MPPerson_id'],
					'FamilyName' => $evnXmlInfo['MPPerson_SurName'],
					'GivenName' => $evnXmlInfo['MPPerson_FirName'],
					'MiddleName' => $evnXmlInfo['MPPerson_SecName'],
					'Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа Form027U (Выписной эпикриз)
	 */
	function getForm027U($data)
	{
		$this->load->library('swXmlTemplate');

		$result = "";

		$evnXmlInfos = $this->getEvnXmlInfo(array(
			'EvnXml_rid' => $data['EvnXml_rid'],
			'XmlTypeKind_id' => array(1) // выписной
		));
		foreach ($evnXmlInfos as $evnXmlInfo) {
			$xml_data = $this->EvnXmlBase_model->doLoadPrintData(array('EvnXml_id' => $evnXmlInfo['EvnXml_id']));
			$html = swXmlTemplate::getHtmlDoc($xml_data[0], true);

			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_form027u', array(
					'CreationDate' => $evnXmlInfo['EvnXml_insDate'],
					'IdDocumentMis' => $evnXmlInfo['EvnXml_id'],
					'Header' => 'Выписной эпикриз',
					'Data' => base64_encode($html),
					'MIMEType' => 'text/html',
					'IdLpu' => $this->getIdLpu($evnXmlInfo['MPLpu_id']),
					'IdSpeciality' => $evnXmlInfo['MPMedSpecOms_Code'],
					'IdPosition' => $evnXmlInfo['MPPost_Code'],
					'Sex' => $evnXmlInfo['MPSex_Code'],
					'Birthdate' => $evnXmlInfo['MPPerson_BirthDay'],
					'IdPersonMis' => $evnXmlInfo['MPPerson_id'],
					'FamilyName' => $evnXmlInfo['MPPerson_SurName'],
					'GivenName' => $evnXmlInfo['MPPerson_FirName'],
					'MiddleName' => $evnXmlInfo['MPPerson_SecName'],
					'Documents' => array()
				), true);
		}

		return $result;
	}

	/**
	 * Получение объектов MedRecord типа TFomsInfo (данные о выполненных МЭС)
	 */
	function getTFomsInfo($data)
	{
		$result = "";

		$mesInfos = $this->getMesInfo(array(
			'Evn_pid' => $data['Evn_pid']
		));
		$mesInfos = array();
		foreach ($mesInfos as $mesInfo) {
			$result .= PHP_EOL . $this->parser->parse('export_xml/misrb_tfomsinfo', array(
					'IdTfomsType' => $mesInfo['Mes_Code'],
					'Count' => $mesInfo['Mes_Count']
				), true);
		}

		return $result;
	}
}