<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Ersb_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 */
class SoapClientExt extends SoapClient
{
	public $customXml = null;
	public $lastRequest = null;

	/**
	 * Устаналивает кастомную XML для отправки в сервис
	 */
	public function setCustomXml($customXml) {
		$this->customXml = $customXml;
		return $this;
	}

	/**
	 * Получение последнего запроса
	 */
	public function __getLastRequest() {
		return $this->lastRequest;
	}

	/**
	 * Выполнение SOAP запроса
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		if (!empty($this->customXml)) {
			$request = $this->customXml;
		}

		$this->lastRequest = $request;

		return parent::__doRequest($request, $location, $action, $version, $one_way);
	}
}

class Ersb_model extends swPgModel {
	protected $_esrbConfig = array();
	protected $_soapClients = array();
	protected $_syncSprList = array(); // список синхронизированных справочников
	protected $_syncSprTables = array(); // список таблиц для синхронизации справочников
	protected $_ticket = ""; // токен авторизованного пользователя

	protected $_execIteration = 0;
	protected $_maxExecIteration = 1;
	protected $_execIterationDelay = 300;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		ini_set("default_socket_timeout", 600);

		$this->load->library('textlog', array('file'=>'Ersb_'.date('Y-m-d').'.log'));

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('RSBKZ');

		$this->_esrbConfig = $this->config->item('Ersb');
	}

	/**
	 * Выполнение запроса к сервису
	 */
	function exec($serviceType, $command, $params = null, $xml = null) {
		$this->_execIteration++;

		$this->textlog->add("exec: {$command}, try {$this->_execIteration} of {$this->_maxExecIteration}");

		try {
			if (!empty($xml)) {
				$response = $this->getSoapClient($serviceType)->setCustomXml($xml)->$command($params);
			} else if (!empty($params)) {
				$response = $this->getSoapClient($serviceType)->$command($params);
			} else {
				$response = $this->getSoapClient($serviceType)->$command();
			}

			$this->textlog->add("Запрос: ".$this->getSoapClient($serviceType)->__getLastRequest());
			$this->textlog->add("Ответ: ".$this->getSoapClient($serviceType)->__getLastResponse());
			if (!empty($_REQUEST['getDebug'])) {
				echo "<textarea cols=150 rows=20>" . $this->getSoapClient($serviceType)->__getLastRequest() . "</textarea><br><br>";
				echo "<textarea cols=150 rows=20>" . $this->getSoapClient($serviceType)->__getLastResponse() . "</textarea><br><br>";
			}
		} catch(Exception $e) {
			$this->textlog->add("Запрос: ".$this->getSoapClient($serviceType)->__getLastRequest());
			$this->textlog->add("Ответ: ".$this->getSoapClient($serviceType)->__getLastResponse());
			if (!empty($_REQUEST['getDebug'])) {
				echo "<textarea cols=150 rows=20>".$this->getSoapClient($serviceType)->__getLastRequest()."</textarea><br><br>";
				echo "<textarea cols=150 rows=20>".$this->getSoapClient($serviceType)->__getLastResponse()."</textarea><br><br>";
			}
			$this->textlog->add("exec fail: {$serviceType}.{$command}, try {$this->_execIteration} of {$this->_maxExecIteration}. Exception: ".$e->getCode()." ".$e->getMessage()." ".(!empty($e->detail)?var_export($e->detail, true):''));
			$errorCode = isset($e->faultcode)?$e->faultcode:$e->getMessage();

			$httpCode = null;
			if (in_array($errorCode, array(401))) {
				$httpCode = $e->getCode();
			} else if ($errorCode == 'HTTP') {
				switch($e->getMessage()) {
					case 'Forbidden': $httpCode = 403;break;
				}
			}

			//Ошибка на сервере. Её можно выводить сразу. Некоторые http-ошибки тоже.
			$errorOnServer = (
				in_array($httpCode, array(401,403)) || in_array($errorCode, array('Client','soap:Client','soap:Server'))
			);
			//Пробуем выполнить запрос ещё n-ое кол-во раз
			if (!$errorOnServer && $this->_execIteration < $this->_maxExecIteration) {
				sleep($this->_execIterationDelay);
				$response = $this->exec($serviceType, $command, $params);
			} else {
				$this->_execIteration = 0;
				throw $e;	//Посылаем ошибку на вывод
			}
		}

		$this->_execIteration = 0;

		return $response;
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
	 * @param $serviceType
	 * @return mixed
	 */
	function getSoapClient($serviceType) {
		if (!isset($this->_soapClients[$serviceType])) {
			$url = $this->_esrbConfig['url'].'?wsdl';

			if (!empty($_REQUEST['getDebug'])) {
				var_dump($url);
			}

			$context = stream_context_create(array(
				'ssl' => array(
					// set some SSL/TLS specific options
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			));

			$soapOptions = array(
				'encoding' => 'UTF-8',
				'soap_version' => SOAP_1_1,
				'exceptions' => 1, // обработка ошибок
				'trace' => 1, // трассировка
				'connection_timeout' => 5,
				'stream_context' => $context
			);

			if (!empty($this->_esrbConfig['proxy_host'])) {
				$soapOptions['proxy_host'] = $this->_esrbConfig['proxy_host'];
			}
			if (!empty($this->_esrbConfig['proxy_port'])) {
				$soapOptions['proxy_port'] = $this->_esrbConfig['proxy_port'];
			}
			if (!empty($this->_esrbConfig['proxy_login'])) {
				$soapOptions['proxy_login'] = $this->_esrbConfig['proxy_login'];
			}
			if (!empty($this->_esrbConfig['proxy_password'])) {
				$soapOptions['proxy_password'] = $this->_esrbConfig['proxy_password'];
			}

			try {
				set_error_handler(array($this, 'exceptionErrorHandler'));
				$this->_soapClients[$serviceType] = new SoapClientExt($url, $soapOptions);
				restore_error_handler();
			} catch(Exception $e) {
				restore_error_handler();
				$this->textlog->add('SoapFault: '.$e->getCode().' '.$e->getMessage());
				throw new Exception("Не удалось установить соединение с сервисом", 500, $e);
			}
		}

		$this->_soapClients[$serviceType]->setCustomXml(null);

		return $this->_soapClients[$serviceType];
	}

	/**
	 * Получние данных из справочника. Метод для API.
	 */
	function getRefbookMapForAPI($data) {
		$filter = "";
		$queryParams = array(
			'Refbook_Code' => $data['Refbook_Code']
		);
		if (!empty($data['Refbook_MapName'])) {
			$filter .= " AND ERSBRefbook_MapName = :Refbook_MapName";
			$queryParams['Refbook_MapName'] = $data['Refbook_MapName'];
		}
		$resp = $this->queryResult("
			SELECT
				ERSBRefbook_id AS \"ERSBRefbook_id\",
				ERSBRefbook_Code AS \"ERSBRefbook_Code\",
				ERSBRefbook_Name AS \"ERSBRefbook_Name\",
				ERSBRefbook_MapName AS \"ERSBRefbook_MapName\",
				Refbook_TableName AS \"Refbook_TableName\"
			FROM
				r101.v_ERSBRefbook
			WHERE
				ERSBRefbook_Code = :Refbook_Code
				{$filter}
		", $queryParams);

		if (empty($resp[0]['ERSBRefbook_MapName'])) {
			return array();
		}

		$mapTable = $resp[0]['ERSBRefbook_MapName'];
		$theirTable = preg_replace('/Link$/ui', '', $mapTable);

		$ourTable = $resp[0]['Refbook_TableName'];
		$ourKey = preg_replace('/.*\./ui', '', $ourTable).'_id';
		$ourCode = preg_replace('/.*\./ui', '', $ourTable).'_Code';
		$mapTableWithoutLink = $resp[0]['ERSBRefbook_MapName'];

		$resp = $this->queryResult("
			SELECT
				our.{$ourKey} AS \"id\",
				our.{$ourCode} AS \"Code\",
				their.P_ID AS \"ERSB_id\",
				their.p_publCod AS \"ERSB_Code\"
			FROM
				{$mapTable} link
				INNER JOIN {$ourTable} our ON our.{$ourKey} = link.{$ourKey}
				INNER JOIN {$theirTable} their ON their.P_ID = link.P_ID
		");

		if (!empty($data['Column_Name']) && !empty($data['Column_Value'])) {
			foreach($resp as $one => $key) {
				if (!empty($key[$data['Column_Name']]) && $key[$data['Column_Name']] == $data['Column_Value']) {
					// удовлетворяет фильтру
				} else {
					// не удовлетворяет фильтру
					unset($resp[$one]);
				}
			}
			$resp = array_values($resp);
		}

		return $resp;
	}

	/**
	 * Получние данных из справочника
	 */
	function getSyncSpr($table, $id, $allowBlank = false, $field = 'p_publCod') {
		if (empty($id)) {
			return null;
		}

		// ищем в памяти
		if (isset($this->_syncSprList[$table][$field]) && isset($this->_syncSprList[$table][$field][$id])) {
			return $this->_syncSprList[$table][$field][$id];
		}

		if (empty($this->_syncSprTables)) {
			$resp = $this->queryResult("
				SELECT
					ERSBRefbook_id AS \"ERSBRefbook_id\",
					ERSBRefbook_Code AS \"ERSBRefbook_Code\",
					ERSBRefbook_Name AS \"ERSBRefbook_Name\",
					ERSBRefbook_MapName AS \"ERSBRefbook_MapName\",
					Refbook_TableName AS \"Refbook_TableName\"
				FROM
					r101.v_ERSBRefbook
			");

			foreach($resp as $respone) {
				$this->_syncSprTables[$respone['ERSBRefbook_Code']] = array(
					'MapName' => $respone['ERSBRefbook_MapName'],
					'TableName' => $respone['Refbook_TableName']
				);
			}
		}

		if (!empty($this->_syncSprTables[$table])) {
			// good
			$mapTable = $this->_syncSprTables[$table]['MapName'];
			$theirTable = preg_replace('/Link$/ui', '', $mapTable);

			$ourTable = $this->_syncSprTables[$table]['TableName'];
			$advancedKey = preg_replace('/.*\./', '', "{$ourTable}_id");

			$idField = "link.P_id";
			if(in_array($table, array('hTreatmentType'))) {
				$idField = "link.{$table}_id";
			}

			// ищем в бд
			$query = "
				SELECT
					{$idField} AS \"id\",
					their.{$field} AS \"code\"
				FROM
					{$mapTable} link
					INNER JOIN {$theirTable} their ON their.P_ID = link.P_ID
				WHERE
					link.{$advancedKey} = :{$advancedKey}
				LIMIT 1
			";

			if(in_array($table, array('hTreatmentType'))) {
				$query = "
					SELECT
						{$idField} AS \"id\",
						their.{$table}_SysNick AS \"code\"
					FROM
						{$mapTable} link
						LEFT JOIN {$theirTable} their ON their.{$table}_id = link.{$table}_id
					WHERE
						link.{$advancedKey} = :{$advancedKey} 
					LIMIT 1
				";
			}

			$resp = $this->queryResult($query, array(
				$advancedKey => $id
			));

			if (!empty($resp[0]['code'])) {
				$this->_syncSprList[$table][$field][$id] = $resp[0]['code'];
				return $resp[0]['code'];
			}

			if (!$allowBlank) {
				throw new Exception('Не найдена запись в '.$mapTable.' с идентификатором '.$id.' ('.$advancedKey.')', 400);
			}
		} else {
			throw new Exception('Не найдена стыковочная таблица для ' . $table, 400);
		}

		return null;
	}

	/**
	 * Сохранение данных синхронизации объекта
	 */
	function saveSyncObject($table, $id, $value, $ins = false) {
		// сохраняем в БД
		$this->ObjectSynchronLog_model->saveObjectSynchronLog($table, $id, $value, $ins);
	}

	/**
	 * Логин
	 */
	function login() {
		// пробуем залогиниться
		$response = $this->exec('Esrb', 'Login', array(
			'username' => $this->_esrbConfig['username'],
			'password' => $this->_esrbConfig['password']
		));

		if (!empty($response->LoginResult->ErrorMessage)) {
			throw new Exception('Ошибка при выполнении Login: ' . $response->LoginResult->ErrorMessage, 400);
		} else {
			$this->_ticket = $response->LoginResult->Ticket;
		}
	}

	/**
	 * Получение данных КВС
	 */
	function getEvnPSInfo($data) {
		$params = array('EvnPS_id' => $data['EvnPS_id']);

		$params['AttributeVision_TablePKey'] = $this->getFirstResultFromQuery("SELECT TariffClass_id AS \"TariffClass_id\" FROM v_TariffClass WHERE TariffClass_SysNick = 'bazstac'");

		$query = "
			SELECT 
				EPS.EvnPS_id AS \"EvnPS_id\",
				EPS.EvnPS_NumCard AS \"EvnPS_NumCard\",
				EPS.PayType_id AS \"PayType_id\",
				PBG.BloodGroupType_id AS \"BloodGroupType_id\",
				PBG.RhFactorType_id AS \"RhFactorType_id\",
				ESLAST.LpuSectionBedProfile_id AS \"LpuSectionBedProfile_id\",
				EPS.PrehospType_id AS \"PrehospType_id\",
				TO_CHAR(EPS.EvnPS_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnPS_setDT\",
				TO_CHAR(EPS.EvnPS_disDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnPS_disDT\",
				ESLAST.LeaveType_id AS \"LeaveType_id\",
				ESLAST.ResultDesease_id AS \"ResultDesease_id\",
				ESLAST.LpuUnitType_id AS \"LpuUnitType_id\",
				pnb.PersonNewborn_Weight AS \"PersonNewborn_Weight\",
				pnb.PersonNewborn_Height AS \"PersonNewborn_Height\",
				ESLAST.MesTariff_Value AS \"MesTariff_Value\",
				av.AttributeValue_ValueFloat AS \"AttributeValue_ValueFloat\",
				P.BDZ_id AS \"BDZ_id\",
				ps.Person_Inn AS \"Person_Inn\",
				ps.Person_SurName AS \"Person_SurName\",
				ps.Person_FirName AS \"Person_FirName\",
				ps.Person_SecName AS \"Person_SecName\",
				TO_CHAR(ps.Person_BirthDay, 'yyyy-mm-ddThh24:mi:ss') AS \"Person_BirthDay\",
				ps.Sex_id AS \"Sex_id\",
				ps.SocStatus_id AS \"SocStatus_id\",
				ua.Address_Address AS \"UAddress_Address\",
				ua.Address_House AS \"UAddress_House\",
				ukls.KLStreet_Name AS \"UKLStreet_Name\",
				pa.Address_Address AS \"PAddress_Address\",
				pa.Address_House AS \"PAddress_House\",
				pkls.KLStreet_Name AS \"PKLStreet_Name\",
				ba.Address_Address AS \"BAddress_Address\",
				ba.Address_House AS \"BAddress_House\",
				bkls.KLStreet_Name AS \"BKLStreet_Name\",
				gph.PersonalID AS \"PersonalID\",
				gph.PostID AS \"PostID\",
				COALESCE(FPLAST.FPID, gph.FPID) AS \"FPID\",
				COALESCE(FPFIRST.FPID, firstgph.FPID) AS \"FirstFPID\",
				gph.MOID AS \"MOID\",
				gmp.ID AS \"PRIK_MOID\", 
				CASE WHEN epslast.EvnPS_id IS NOT NULL THEN 200 else 100 END AS \"DiseaseCountPublicCode\",
				CASE WHEN pw.Okei_id = 36 THEN
					CAST(pw.PersonWeight_Weight AS FLOAT)
				else
					CAST(pw.PersonWeight_Weight AS FLOAT) * 1000
				END AS \"PersonWeight_Weight\",
				CAST(PH.PersonHeight_Height AS FLOAT) AS \"PersonHeight_Height\",
				ua.KLTown_id AS \"KLTown_id\",
				COALESCE(gph_did.MOID, gmd.ID) AS \"NAP_MOID\",
				hbp.p_publCod AS \"BedProfile_Code\",
				ESFIRST.BedProfile_Code AS \"FirstBedProfile_Code\",
				htsf.p_publCod AS \"FinanceSourcePublicCode\",
				hipht.p_publCod AS \"InPatientHelpTypePublicCode\",
				eps.PrehospDirect_id AS \"PrehospDirect_id\",
				eps.PrehospArrive_id AS \"PrehospArrive_id\",
				EPL.Hospitalization_id AS \"Hospitalization_id\",
				EDL.Referral_Code AS \"Referral_Code\",
				E.EthnosRPN_id AS \"EthnosRPN_id\",
				ESLAST.LeaveType_SysNick AS \"LeaveType_SysNick\",
				ESLAST.InPatientPayType AS \"InPatientPayType\",
				hSocType.code AS \"SocType_Code\",
				COALESCE(pp.SocialStatusesPublicCode, 2700) AS \"BenefitsPublicCodes\",
				dbo.Age2(ps.Person_BirthDay, EPS.EvnPS_setDT) AS \"Person_Age\",
				CASE
					WHEN ESFIRST.EvnSection_IsAdultEscort = 2 AND dbo.Age2(ps.Person_BirthDay, EPS.EvnPS_setDT) < 1 THEN 300
					WHEN ESFIRST.EvnSection_IsAdultEscort = 2 THEN 200
					else 100
				END AS \"HospitalizedPublicCode\",
				CASE 
					WHEN ESLAST.LpuUnitType_id not IN (6,7,9) THEN NULL
					else COALESCE(dhctd.publCod, dhctucl.publCod, dhctuc.publCod) 
				END AS \"DayHospitalCardTypePublicCode\"
			FROM
				v_EvnPS EPS
				LEFT JOIN r101.v_EvnPSLink epl ON epl.EvnPS_id = eps.EvnPS_id
				LEFT JOIN r101.v_EvnDirectionLink edl ON edl.EvnDirection_id = eps.EvnDirection_id
				INNER JOIN LATERAL (
					SELECT
						es.EvnSection_id,
						ls.LpuSectionBedProfile_id,
						es.LeaveType_id,
						COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EOS.ResultDesease_id, ED.ResultDesease_id) AS ResultDesease_id,
						lu.LpuUnitType_id,
						mt.MesTariff_Value,
						es.MedStaffFact_id,
						es.LpuSection_id,
						ptersb.PayTypeERSB_publCod AS InPatientPayType,
						lt.LeaveType_SysNick
					FROM
						v_EvnSection ES
						LEFT JOIN v_LpuSection ls ON ls.LpuSection_id = es.LpuSection_id
						LEFT JOIN v_LpuUnit lu ON lu.LpuUnit_id = ls.LpuUnit_id
						LEFT JOIN v_EvnLeave EL ON EL.EvnLeave_pid = ES.EvnSection_id
						LEFT JOIN v_EvnDie ED ON ED.EvnDie_pid = ES.EvnSection_id
						LEFT JOIN v_EvnOtherLpu EOL ON EOL.EvnOtherLpu_pid = ES.EvnSection_id
						LEFT JOIN v_EvnOtherStac EOST ON EOST.EvnOtherStac_pid = ES.EvnSection_id
						LEFT JOIN v_EvnOtherSection EOS ON EOS.EvnOtherSection_pid = ES.EvnSection_id
						LEFT JOIN v_ResultDesease RD ON RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EOS.ResultDesease_id, ED.ResultDesease_id)
						LEFT JOIN v_MesTariff mt ON mt.MesTariff_id = es.MesTariff_id
						LEFT JOIN v_MesOld mo ON mo.Mes_id = mt.Mes_id
						LEFT JOIN v_LeaveType lt ON lt.LeaveType_id = es.LeaveType_id
						LEFT JOIN v_PayTypeERSB ptersb ON ptersb.PayTypeERSB_id = es.PayTypeERSB_id
					WHERE
						ES.EvnSection_pid = EPS.EvnPS_id
						AND COALESCE(ES.EvnSection_IsPriem, 1) = 1
					ORDER BY
						ES.EvnSection_setDT DESC
					LIMIT 1
				) ESLAST
				ON TRUE
				INNER JOIN LATERAL (
					SELECT
						es.EvnSection_id,
						es.LpuSection_id,
						es.MedStaffFact_id,
						es.EvnSection_IsAdultEscort,
						hbp.p_publCod AS BedProfile_Code
					FROM
						v_EvnSection ES
						LEFT JOIN r101.GetBedEvnLink gbel ON gbel.Evn_id = ES.EvnSection_id
						LEFT JOIN r101.GetBed gb ON gb.GetBed_id = gbel.GetBed_id
						LEFT JOIN r101.hBedProfile hbp ON hbp.p_ID = gb.BedProfile
					WHERE
						ES.EvnSection_pid = EPS.EvnPS_id
						AND COALESCE(ES.EvnSection_IsPriem, 1) = 1
					ORDER BY
						ES.EvnSection_setDT ASC
					LIMIT 1
				) ESFIRST
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						FPID
					FROM
						r101.v_LpuSectionFPIDLink
					WHERE
						LpuSection_id = ESLAST.LpuSection_id
					LIMIT 1
				) FPLAST
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						FPID
					FROM
						r101.v_LpuSectionFPIDLink
					WHERE
						LpuSection_id = ESFIRST.LpuSection_id
					LIMIT 1
				) FPFIRST
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						pnb.PersonNewborn_Weight,
						pnb.PersonNewborn_Height
					FROM
						v_EvnSection es
						INNER JOIN v_BirthSpecStac bss ON bss.EvnSection_id = es.EvnSection_id
						INNER JOIN v_PersonNewBorn PNB ON PNB.BirthSpecStac_id = bss.BirthSpecStac_id
					WHERE
						es.EvnSection_pid = eps.EvnPS_id
					LIMIT 1
				) pnb
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						av.AttributeValue_ValueFloat
					FROM
						v_AttributeVision avis
						INNER JOIN v_AttributeValue av ON av.AttributeVision_id = avis.AttributeVision_id
					WHERE
						avis.AttributeVision_TableName = 'dbo.TariffClass'
						AND avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
						AND avis.AttributeVision_IsKeyValue = 2
						AND COALESCE(av.AttributeValue_begDate, EPS.EvnPS_setDT) <= EPS.EvnPS_setDT
						AND COALESCE(av.AttributeValue_endDate, EPS.EvnPS_setDT) >= EPS.EvnPS_setDT
					LIMIT 1
				) av
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						gpw.PersonalID,
						gpw.FPID,
						gpw.MOID,
						gpw.PostID
					FROM
						r101.v_GetPersonalHistoryWP gphwp
						INNER JOIN r101.v_GetPersonalWork gpw ON gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					WHERE
						gphwp.WorkPlace_id = ESLAST.MedStaffFact_id
					ORDER BY
						gphwp.GetPersonalHistoryWP_insDT DESC
					LIMIT 1
				) gph
				ON TRUE
				LEFT JOIN r101.GetBedEvnLink gbel ON gbel.Evn_id = ESLAST.EvnSection_id
				LEFT JOIN r101.GetBed gb ON gb.GetBed_id = gbel.GetBed_id
				LEFT JOIN r101.hBedProfile hbp ON hbp.p_ID = gb.BedProfile
				LEFT JOIN r101.hInPatientHelpTypes hipht ON hipht.p_ID = gb.StacType
				LEFT JOIN r101.hTypSrcFin htsf ON htsf.p_ID = gb.TypeSrcFin
				LEFT JOIN LATERAL (
					SELECT
						gpw.FPID
					FROM
						r101.v_GetPersonalHistoryWP gphwp
						INNER JOIN r101.v_GetPersonalWork gpw ON gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					WHERE
						gphwp.WorkPlace_id = ESFIRST.MedStaffFact_id
					ORDER BY
						gphwp.GetPersonalHistoryWP_insDT DESC
					LIMIT 1
				) firstgph
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						eps2.EvnPS_id
					FROM
						v_EvnPS eps2
					WHERE
						eps2.EvnPS_setDT < eps.EvnPS_setDT
					LIMIT 1
				) epslast
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						gpw.MOID
					FROM
						r101.v_GetPersonalHistoryWP gphwp
						INNER JOIN r101.v_GetPersonalWork gpw ON gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						LEFT JOIN r101.v_GetMO gm ON gm.ID = gpw.MOID
					WHERE
						gphwp.WorkPlace_id = EPS.MedStaffFact_did
					ORDER BY
						gphwp.GetPersonalHistoryWP_insDT DESC
					LIMIT 1
				) gph_did
				ON TRUE
				LEFT JOIN v_Lpu LD ON LD.Org_id = EPS.Org_did
				LEFT JOIN r101.GetMO gmd ON gmd.Lpu_id = LD.Lpu_id
				LEFT JOIN v_PersonBloodGroup PBG ON PBG.Person_id = EPS.Person_id
				LEFT JOIN v_PersonState PS ON ps.Person_id = EPS.Person_id
				LEFT JOIN r101.GetMO gmp ON gmp.Lpu_id = PS.Lpu_id
				LEFT JOIN v_Person P ON p.Person_id = EPS.Person_id
				LEFT JOIN v_PersonInfo PI ON PI.Person_id = PS.Person_id
				LEFT JOIN v_Ethnos E ON E.Ethnos_id = PI.Ethnos_id
				LEFT JOIN v_Address_all ua ON ua.Address_id = ps.UAddress_id
				LEFT JOIN v_Address_all pa ON pa.Address_id = ps.PAddress_id
				LEFT JOIN v_PersonBirthPlace pbp ON ps.Person_id = pbp.Person_id
				LEFT JOIN v_Address_all ba ON ba.Address_id = pbp.Address_id
				LEFT JOIN v_KLStreet ukls ON ukls.KLStreet_id = ua.KLStreet_id
				LEFT JOIN v_KLStreet pkls ON pkls.KLStreet_id = pa.KLStreet_id
				LEFT JOIN v_KLStreet bkls ON bkls.KLStreet_id = ba.KLStreet_id
				LEFT JOIN LATERAL (
					SELECT * FROM v_PersonHeight ph WHERE ph.Person_id = EPS.Person_id ORDER BY ph.PersonHeight_setDT DESC LIMIT 1
				) ph
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT * FROM v_PersonWeight pw WHERE pw.Person_id = EPS.Person_id ORDER BY pw.PersonWeight_setDT DESC LIMIT 1
				) pw
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						hSocType.code AS SocialStatusesPublicCode
					FROM 
						v_PersonPrivilege pp
						LEFT JOIN r101.hSocTypeLink hSocTypeLink ON hSocTypeLink.PrivilegeType_id = pp.PrivilegeType_id
						LEFT JOIN r101.hSocType hSocType ON hSocType.id = hSocTypeLink.id
					WHERE pp.Person_id = EPS.Person_id
						AND EPS.EvnPS_setDT between pp.PersonPrivilege_begDate AND COALESCE(pp.PersonPrivilege_endDate, '2099-01-01')
					ORDER BY pp.PersonPrivilege_begDate DESC
					LIMIT 1
				) pp
				ON TRUE
				LEFT JOIN r101.hSocTypeSocStatus ON hSocTypeSocStatus.SocStatus_id = ps.SocStatus_id
				LEFT JOIN r101.hSocType ON hSocType.id = hSocTypeSocStatus.id
				LEFT JOIN LATERAL (
					SELECT dhct.publCod
					FROM r101.hDayHospitalCardTypesDiag dhctd
					INNER JOIN r101.hDayHospitalCardTypes dhct ON dhct.ID = dhctd.hDayHospitalCardTypes_id
					INNER JOIN v_EvnUsluga eu ON eu.UslugaComplex_id = dhctd.UslugaComplex_id AND eu.EvnUsluga_rid = EPS.EvnPS_id
					WHERE dhctd.Diag_id IN (EPS.Diag_id, EPS.Diag_pid)
						AND EPS.EvnPS_setDT between dhctd.hDayHospitalCardTypesDiag_begDT AND COALESCE(dhctd.hDayHospitalCardTypesDiag_endDT, '2099-01-01')
					ORDER BY COALESCE(dhctd.hDayHospitalCardTypesDiag_IsOper,1) ASC
					LIMIT 1
				) dhctd
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT dhct.publCod
					FROM r101.hDayHospitalCardTypesUslugaComplexLink dhctucl
					INNER JOIN r101.hDayHospitalCardTypes dhct ON dhct.ID = dhctucl.hDayHospitalCardTypes_id
					INNER JOIN v_EvnUsluga eu ON eu.UslugaComplex_id = dhctucl.UslugaComplex_id AND eu.EvnUsluga_rid = EPS.EvnPS_id
					INNER JOIN v_EvnUslugaOper euo ON euo.UslugaComplex_id = dhctucl.UslugaComplex_oid AND euo.EvnUslugaOper_rid = EPS.EvnPS_id
					WHERE EPS.EvnPS_setDT between dhctucl.hDayHospitalCardTypesUslugaComplexLink_begDT AND COALESCE(dhctucl.hDayHospitalCardTypesUslugaComplexLink_endDT, '2099-01-01')
					LIMIT 1
				) dhctucl
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT dhct.publCod
					FROM r101.hDayHospitalCardTypesUslugaComplex dhctuc
					INNER JOIN r101.hDayHospitalCardTypes dhct ON dhct.ID = dhctuc.hDayHospitalCardTypes_id
					INNER JOIN v_EvnUsluga eu ON eu.UslugaComplex_id = dhctuc.UslugaComplex_id AND eu.EvnUsluga_rid = EPS.EvnPS_id
					WHERE EPS.EvnPS_setDT between dhctuc.hDayHospitalCardTypesUslugaComplex_begDT AND COALESCE(dhctuc.hDayHospitalCardTypesUslugaComplex_endDT, '2099-01-01')
					LIMIT 1
				) dhctuc
				ON TRUE
			WHERE
				EPS.EvnPS_id = :EvnPS_id
			LIMIT 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные КВС', 400);
		}
	}

	/**
	 * Получение информации по выписному эпикризу
	 */
	function getInPatientOutEpicrisis($EvnPS_id) {
		$resp = $this->queryResult("
			SELECT
				ex.EvnXml_id AS \"EvnXml_id\",
				eps.Person_id AS \"Person_id\",
				COALESCE(ex.EvnXml_id, eps.EvnPS_id) AS \"EpicrisisNumber\",
				XTH.XmlTemplateHtml_HtmlTemplate AS \"EpicrisisData\",
				TO_CHAR(ES.EvnSection_disDT, 'yyyy-mm-ddThh24:mi:ss') AS \"IssueDate\",
				RTRIM(puc.pmUser_Name) AS \"UserFIO\",
				RTRIM(puc.pmUser_Login) AS \"UserLogin\",
				RTRIM(puc.pmUser_Email) AS \"UserPost\"
			FROM
				v_EvnPS eps
				LEFT JOIN v_EvnSection es ON es.EvnSection_pid = eps.EvnPS_id
				LEFT JOIN v_EvnXml ex ON ex.Evn_id = es.EvnSection_id AND ex.XmlType_id = 10
				LEFT JOIN v_XmlTemplateHtml XTH ON XTH.XmlTemplateHtml_id = EX.XmlTemplateHtml_id
				LEFT JOIN v_pmUserCache puc ON puc.pmUser_id = COALESCE(ex.pmUser_insID, eps.pmUser_insID)
			WHERE
				eps.EvnPS_id = :EvnPS_id
			LIMIT 1
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		$AllergicHistory = "Не указано";
		if (!empty($resp[0]['Person_id'])) {
			// AllergicHistory - Данные в сигнальной информации человека«Аллергологический анамнез».

			$this->load->model('PersonAllergicReaction_model');
			$this->PersonAllergicReaction_model->getPersonAllergicReactionViewData(array(
				'Person_id' => $resp[0]['Person_id']
			));
		}

		$ExternalEvidence = "Не указано";
		$AtDischarge = "Не указано";
		$ComplaintsOnAdmission = "Не указано";
		$LaboratoryAndDiagnosticTests = "Не указано";
		$LifeHistory = "Не указано";
		$MedicalAndEmploymentAdvice = "Не указано";
		$MedicalHistory = "Не указано";
		$SurgProcs = "Не указано";
		$TheTreatment = "Не указано";
		if (!empty($resp[0]['EvnXML_id'])) {
			$resp_ex = $this->queryResult("
				SELECT
					CAST(XPATH('data/objectivestatus/text()', EvnXml_Data))[1] AS VARCHAR) AS \"objectivestatus\",
					CAST(XPATH('data/outcomeOFdisease/text()', EvnXml_Data))[1] AS VARCHAR) AS \"outcomeOFdisease\",
					CAST(XPATH('data/complaint/text()', EvnXml_Data))[1] AS VARCHAR) AS \"complaint\",
					CAST(XPATH('data/Surveys/text()', EvnXml_Data))[1] AS VARCHAR) AS \"Surveys\",
					CAST(XPATH('data/anamnesvitae/text()', EvnXml_Data))[1] AS VARCHAR) AS \"anamnesvitae\",
					CAST(XPATH('data/recommendations/text()', EvnXml_Data))[1] AS VARCHAR) AS \"recommendations\",
					CAST(XPATH('data/anamnesmorbi/text()', EvnXml_Data))[1] AS VARCHAR) AS \"anamnesmorbi\",
					CAST(XPATH('data/OLDoperations/text()', EvnXml_Data))[1] AS VARCHAR) AS \"OLDoperations\",
					CAST(XPATH('data/treatment/text()', EvnXml_Data))[1] AS VARCHAR) AS \"treatment\"
				FROM 
					v_EvnXml
				WHERE
					EvnXml_id = :EvnXml_id
				LIMIT 1
			", array(
				'EvnXml_id' => $resp[0]['EvnXml_id']
			));
			// необходимо получить части этого документа
			$ExternalEvidence = !empty($resp_ex[0]['objectivestatus'])?htmlspecialchars_decode($resp_ex[0]['objectivestatus']):"Не указано"; // Данные раздела «Объективный статус» выписного эпикриза
			$AtDischarge = !empty($resp_ex[0]['outcomeOFdisease'])?htmlspecialchars_decode($resp_ex[0]['outcomeOFdisease']):"Не указано"; // Данные раздела «Исход заболевания» (id="outcomeOFdisease") выписного эпикриза.
			$ComplaintsOnAdmission = !empty($resp_ex[0]['complaint'])?htmlspecialchars_decode($resp_ex[0]['complaint']):"Не указано"; // Данные раздела «Жалобы» (id="complaint") выписного эпикриза
			$LaboratoryAndDiagnosticTests = !empty($resp_ex[0]['Surveys'])?htmlspecialchars_decode($resp_ex[0]['Surveys']):"Не указано"; // Данные раздела «Проведенные обследования» (id="Surveys") выписного эпикриза.
			$LifeHistory = !empty($resp_ex[0]['anamnesvitae'])?htmlspecialchars_decode($resp_ex[0]['anamnesvitae']):"Не указано"; // Данные раздела «Анамнез жизни» (id="anamnesvitae") выписного эпикриза.
			$MedicalAndEmploymentAdvice = !empty($resp_ex[0]['recommendations'])?htmlspecialchars_decode($resp_ex[0]['recommendations']):"Не указано"; // Данные раздела «Рекомендации» выписного эпикриза.
			$MedicalHistory = !empty($resp_ex[0]['anamnesmorbi'])?htmlspecialchars_decode($resp_ex[0]['anamnesmorbi']):"Не указано"; //  Данные раздела «Анамнез заболевания» (id="anamnesmorbi") выписного эпикриза
			$SurgProcs = !empty($resp_ex[0]['OLDoperations'])?htmlspecialchars_decode($resp_ex[0]['OLDoperations']):"Не указано"; // Данные раздела «Проведенные операции» выписного эпикриза
			$TheTreatment = !empty($resp_ex[0]['treatment'])?htmlspecialchars_decode($resp_ex[0]['treatment']):"Не указано"; // Данные раздела «Проведенное лечение» (id="treatment "выписного эпикриза
		}

		$resp = array(
			'Version' => null,
			'InPatientID' => null,
			'EpicrisisNumber' => $resp[0]['EpicrisisNumber'],
			'ExternalEvidence' => $ExternalEvidence,
			'EpicrisisData' => !empty($resp[0]['EpicrisisData']) ? $resp[0]['EpicrisisData'] : "Не указано",
			'IssueDate' => $resp[0]['IssueDate'],
			'ModifiedDate' => null,
			'AllergicHistory' => $AllergicHistory,
			'AtDischarge' => $AtDischarge,
			'ComplaintsOnAdmission' => $ComplaintsOnAdmission,
			'InstrumentalStudies' => null,
			'LaboratoryAndDiagnosticTests' => $LaboratoryAndDiagnosticTests,
			'LifeHistory' => $LifeHistory,
			'MedicalAndEmploymentAdvice' => $MedicalAndEmploymentAdvice,
			'MedicalHistory' => $MedicalHistory,
			'SpecialistConsultations' => null,
			'SurgProcs' => $SurgProcs,
			'TheTreatment' => $TheTreatment,
			'UserFIO' => $resp[0]['UserFIO'],
			'UserLogin' => $resp[0]['UserLogin'],
			'UserPost' => !empty($resp[0]['UserPost']) ? $resp[0]['UserPost'] : "test@test.ru"
		);

		return $resp;
	}

	/**
	 * Получение информации по истории движения пациентов
	 */
	function getMovementInStacionarHistory($EvnPS_id) {
		$MovementInStacionarHistory = array();

		$resp = $this->queryResult("
			SELECT
				TO_CHAR(ES.EvnSection_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"CriteriaDBeg\",
				TO_CHAR(ES.EvnSection_disDT, 'yyyy-mm-ddThh24:mi:ss') AS \"CriteriaDEnd\",
				TO_CHAR(ps.Person_BirthDay, 'yyyy-mm-ddThh24:mi:ss') AS \"DateOfBirth,
				TO_CHAR(ES.EvnSection_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"DtBeg\",
				TO_CHAR(ES.EvnSection_disDT, 'yyyy-mm-ddThh24:mi:ss') AS \"DtEnd\",
				TO_CHAR(EPS.EvnPS_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnPS_setDT\",
				eps.EvnPS_NumCard AS \"HospitalHistoryNumber\",
				es.EvnSection_id AS \"EvnSection_id\",
				ps.Person_FirName AS \"PatientFirstName\",
				ps.Person_Inn AS \"PatientIIN\",
				ps.Person_SecName AS \"PatientLastNam\",
				ps.Person_SurName AS \"PatientSecondName\",
				ps.Sex_id AS \"Sex_id\",
				ls.LpuSectionBedProfile_id AS \"LpuSectionBedProfile_id\",
				gph.PersonalID AS \"PersonalID\",
				gph.FPID AS \"FPID\",
				gph.MOID AS \"MOID\",
				hbp.p_publCod AS \"BedProfile_Code\",
				hbp.p_nameru AS \"BedProfile_RuName\",
				hbp.p_namekz AS \"BedProfile_KzName\"
			FROM
				v_EvnSection es
				LEFT JOIN v_EvnPS eps ON eps.EvnPS_id = es.EvnSection_pid
				LEFT JOIN v_LpuSection ls ON ls.LpuSection_id = es.LpuSection_id
				LEFT JOIN v_LpuSectionBedProfile lsbp ON lsbp.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
				LEFT JOIN v_PersonState ps ON ps.Person_id = es.Person_id
				LEFT JOIN LATERAL (
					SELECT
						gpw.PersonalID,
						gpw.FPID,
						gpw.MOID
					FROM
						r101.v_GetPersonalHistoryWP gphwp
						INNER JOIN r101.v_GetPersonalWork gpw ON gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					WHERE
						gphwp.WorkPlace_id = ES.MedStaffFact_id
					ORDER BY
						gphwp.GetPersonalHistoryWP_insDT DESC
					LIMIT 1
				) gph
				ON TRUE
				LEFT JOIN r101.GetBedEvnLink gbel ON gbel.Evn_id = es.EvnSection_id
				LEFT JOIN r101.GetBed gb ON gb.GetBed_id = gbel.GetBed_id
				LEFT JOIN r101.hBedProfile hbp ON hbp.p_ID = gb.BedProfile
			WHERE
				es.EvnSection_pid = :EvnPS_id
			ORDER BY
				es.EvnSection_setDT ASC
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		$NextMoveInStacID = null; // идентификатор следующего движения

		$i = 0;
		foreach($resp as $respone) {
			if ($i > 0) {
				$MovementInStacionarHistory[$i - 1]['NextMoveInStacID'] = $respone['EvnSection_id']; // заполняем в предыдущем идентификатор следующего движения
			}

			if ($i == 0) {
				// для первого дата поступления из КВС
				$respone['DtBeg'] = $respone['EvnPS_setDT'];
			}

			$MovementInStacionarHistory[$i] = array(
				'BedProfileNameKZ' => $respone['BedProfile_KzName'],
				'BedProfileNameRU' => $respone['BedProfile_RuName'],
				'CriteriaDBeg' => $respone['CriteriaDBeg'],
				'CriteriaDEnd' => $respone['CriteriaDEnd'],
				'DateOfBirth' => $respone['DateOfBirth'],
				'DepartmentName' => '',
				'DtBeg' => $respone['DtBeg'],
				'DtEnd' => $respone['DtEnd'],
				'HospBedProfileID' => $respone['BedProfile_Code'],
				'HospFuncStrucureID' => $respone['FPID'],
				'HospitalHistoryNumber' => $respone['HospitalHistoryNumber'],
				'InPatientID' => null,
				'ModifiedDate' => null,
				'MoveInStacID' => $respone['EvnSection_id'],
				'MovementHistory' => null,
				'NextMoveInStacID' => null,
				'OutStatusID' => null,
				'OutTypeID' => null,
				'OutTypeNameKZ' => null,
				'OutTypeNameRU' => null,
				'PatientFirstName' => $respone['PatientFirstName'],
				'PatientIIN' => $respone['PatientIIN'],
				'PatientLastNam' => $respone['PatientLastNam'],
				'PatientSecondName' => $respone['PatientSecondName'],
				'PatientSexID' => $this->getSyncSpr('hBIOSex', $respone['Sex_id']),
				'PlacementStatusID' => null,
				'PlacementTypeID' => null,
				'SortNumber' => null
			);

			$i++;
		}

		return $MovementInStacionarHistory;
	}

	/**
	 * Получение информации об онко
	 */
	function getOnkoInfo($EvnPS_id) {
		$OnkoInfo = array();

		$resp = $this->queryResult("
			SELECT
				TO_CHAR(euoc.EvnUslugaOnkoChem_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnUslugaOnkoChem_setDT\",
				TO_CHAR(euob.EvnUslugaOnkoBeam_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnUslugaOnkoBeam_setDT\",
				euob.EvnUslugaOnkoBeam_TotalDoseRegZone AS \"EvnUslugaOnkoBeam_TotalDoseRegZone\",
				euob.EvnUslugaOnkoBeam_TotalDoseTumor AS \"EvnUslugaOnkoBeam_TotalDoseTumor\",
				d.Diag_Code AS \"Diag_Code\",
				od.Diag_Code AS \"OnkoDiag_Code\",
				euob.OnkoUslugaBeamRadioModifType_id AS \"OnkoUslugaBeamRadioModifType_id\",
				mo.TumorStage_id AS \"TumorStage_id\",
				euoc.OnkoTreatType_id AS \"OnkoTreatType_id\",
				euoc.OnkoUslugaChemKindType_id AS \"OnkoUslugaChemKindType_id\",
				euog.EvnUslugaOnkoGormun_IsDrug AS \"EvnUslugaOnkoGormun_IsDrug\",
				euog.EvnUslugaOnkoGormun_IsBeam AS \"EvnUslugaOnkoGormun_IsBeam\",
				euog.EvnUslugaOnkoGormun_IsSurg AS \"EvnUslugaOnkoGormun_IsSurg\",
				euog.EvnUslugaOnkoGormun_IsOther AS \"EvnUslugaOnkoGormun_IsOther\",
				TO_CHAR(mo.MorbusOnko_setDiagDT, 'yyyy-mm-ddThh24:mi:ss') AS \"MorbusOnko_setDiagDT\",
				TO_CHAR(es.EvnSection_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnSection_setDT\",
				TO_CHAR(es.EvnSection_disDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnSection_disDT\",
				mo.OnkoM_id AS \"OnkoM_id\",
				mo.OnkoN_id AS \"OnkoN_id\",
				mo.OnkoT_id AS \"OnkoT_id\",
				mo.MorbusOnko_IsTumorDepoLympha AS \"MorbusOnko_IsTumorDepoLympha\",
				mo.MorbusOnko_IsTumorDepoBones AS \"MorbusOnko_IsTumorDepoBones\",
				mo.MorbusOnko_IsTumorDepoLiver AS \"MorbusOnko_IsTumorDepoLiver\",
				mo.MorbusOnko_IsTumorDepoLungs AS \"MorbusOnko_IsTumorDepoLungs\",
				mo.MorbusOnko_IsTumorDepoBrain AS \"MorbusOnko_IsTumorDepoBrain\",
				mo.MorbusOnko_IsTumorDepoSkin AS \"MorbusOnko_IsTumorDepoSkin\",
				mo.MorbusOnko_IsTumorDepoKidney AS \"MorbusOnko_IsTumorDepoKidney\",
				mo.MorbusOnko_IsTumorDepoOvary AS \"MorbusOnko_IsTumorDepoOvary\",
				mo.MorbusOnko_IsTumorDepoPerito AS \"MorbusOnko_IsTumorDepoPerito\",
				mo.MorbusOnko_IsTumorDepoMarrow AS \"MorbusOnko_IsTumorDepoMarrow\",
				mo.MorbusOnko_IsTumorDepoOther AS \"MorbusOnko_IsTumorDepoOther\",
				mo.MorbusOnko_IsTumorDepoMulti AS \"MorbusOnko_IsTumorDepoMulti\",
				mo.OnkoDiagConfType_id AS \"OnkoDiagConfType_id\",
				euob.OnkoUslugaBeamMethodType_id AS \"OnkoUslugaBeamMethodType_id\",
				euob.OnkoUslugaBeamKindType_id AS \"OnkoUslugaBeamKindType_id\",
				euob.OnkoUslugaBeamIrradiationType_id AS \"OnkoUslugaBeamIrradiationType_id\"
			FROM
				v_EvnSection es
				INNER JOIN v_MorbusBase mb ON mb.Evn_pid = es.EvnSection_id
				INNER JOIN v_Morbus m ON m.MorbusBase_id = mb.MorbusBase_id
				INNER JOIN v_MorbusOnko mo ON mo.Morbus_id = m.Morbus_id
				LEFT JOIN v_EvnUslugaOnkoChem euoc ON euoc.Morbus_id = m.Morbus_id
				LEFT JOIN v_EvnUslugaOnkoBeam euob ON euob.Morbus_id = m.Morbus_id
				LEFT JOIN v_EvnUslugaOnkoGormun euog ON euog.Morbus_id = m.Morbus_id
				LEFT JOIN v_Diag d ON d.Diag_id = m.Diag_id
				LEFT JOIN v_Diag od ON od.Diag_id = mo.OnkoDiag_mid
			WHERE
				es.EvnSection_pid = :EvnPS_id
			LIMIT 1
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		foreach($resp as $respone) {
			$TypeHRTPublicCode = 100;
			if ($respone['EvnUslugaOnkoGormun_IsDrug'] == 2) {
				$TypeHRTPublicCode = 200;
			} else if ($respone['EvnUslugaOnkoGormun_IsBeam'] == 2 || $respone['EvnUslugaOnkoGormun_IsSurg'] == 2 || $respone['EvnUslugaOnkoGormun_IsOther'] == 2) {
				$TypeHRTPublicCode = 200;
			}

			$IsIdentifiedFirstTime = false;
			if (strtotime($respone['MorbusOnko_setDiagDT']) >= strtotime($respone['EvnSection_setDT']) && strtotime($respone['MorbusOnko_setDiagDT']) <= strtotime($respone['EvnSection_disDT'])) {
				$IsIdentifiedFirstTime = true;
			}

			$oneOnkoInfo = array(
				'BeginDateCT' => $respone['EvnUslugaOnkoChem_setDT'],
				'BeginDateRT' => $respone['EvnUslugaOnkoBeam_setDT'],
				'FocalDoseMetastasRT' => $respone['EvnUslugaOnkoBeam_TotalDoseRegZone'],
				'FocalDoseTumorRT' => $respone['EvnUslugaOnkoBeam_TotalDoseTumor'],
				'ComplicationRTPublicCode' => null, // Не заполняется
				'LocalTumorPublicCode' => $respone['Diag_Code'],
				'MorphTypeTumorPublicCode' => $respone['OnkoDiag_Code'],
				'NatureHeldTreatPublicCode' => null, // Не заполняется
				'OtherTypeSpecTreatPublicCode' => null, // Не заполняется
				'PostRTID' => null, // Не заполняется
				'RadiomodifierPublicCode' => $this->getSyncSpr('hRadiomodifier', $respone['OnkoUslugaBeamRadioModifType_id']),
				'ReasonPartTreatPublicCode' => null, // Не заполняется
				'RTPublicCode' => null, // Не заполняется
				'StagesCTPublicCode' => null, // Не заполняется
				'StageTumorProcPublicCode' => $this->getSyncSpr('hCancerStage', $respone['TumorStage_id']),
				'TotalSizeTreatPublicCode' => $this->getSyncSpr('hTotalSizeTreat', $respone['OnkoTreatType_id']),
				'TypeCTPublicCode' => $this->getSyncSpr('hTypeCT', $respone['OnkoUslugaChemKindType_id']),
				'TypeHRTPublicCode' => $TypeHRTPublicCode,
				'TypeTreatPublicCode' => null, // Не заполняется
				'WayRTPublicCode' => $this->getSyncSpr('HWayRT', $respone['OnkoUslugaBeamIrradiationType_id']),
				'IsIdentifiedFirstTime' => $IsIdentifiedFirstTime,
				'SchemeCT' => null, // Не заполняется
				'StageSystemM' => $respone['OnkoM_id'],
				'StageSystemN' => $respone['OnkoN_id'],
				'StageSystemT' => $respone['OnkoT_id'],
				'VariantsPublicCode' => null, // Не заполняется
				'RiskGroupPublicCode' => null, // Не заполняется
				'RezistennostPublicCode' => null, // Не заполняется
				'PeriodConcomitantDiseasesPublicCode' => null, // Не заполняется
				'ForTherapeuticPatientsPublicCode' => null, // Не заполняется
				'LocalizationMethostazPublicCodes' => array(),
				'MethodConfirmationDiagsPublicCodes' => array(
					$this->getSyncSpr('HConfirmationType', $respone['OnkoDiagConfType_id'])
				),
				'MethodRadioationTerapyPublicCodes' => array(
					$this->getSyncSpr('hMethodRT', $respone['OnkoUslugaBeamMethodType_id'])
				),
				'SideEffectHimioTerapiPublicCodes' => null, // Не заполняется
				'SideEffectHormonoTerapiPublicCodes' => null, // Не заполняется
				'SideEffectImmunoTerapiPublicCodes' => null, // Не заполняется
				'SideEffectsTTPublicCodes' => null, // Не заполняется
				'SideEffectsTIBRPublicCodes' => null, // Не заполняется
				'TypeRadioationTerapiPublicCodes' => array(
					$this->getSyncSpr('hTypeRT', $respone['OnkoUslugaBeamKindType_id'])
				),
				'UsedDrugsPublicCodes' => null // Не заполняется
			);

			if ($respone['MorbusOnko_IsTumorDepoLympha'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 100,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoBones'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 200,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoLiver'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 500,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoLungs'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 300,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoBrain'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 400,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoSkin'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 800,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoKidney'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 700,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoOvary'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 1000,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoPerito'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 900,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoMarrow'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 600,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoOther'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 1300,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}

			if ($respone['MorbusOnko_IsTumorDepoMulti'] == 2) {
				$oneOnkoInfo['LocalizationMethostazPublicCodes'][] = array(
					'MetastasisLocalizationPublicCode' => 1100,
					'IsForPrimaryTumorRunningProcess' => null, // Не заполняется
					'IsProgressingProcess' => null // Не заполняется
				);
			}



			$OnkoInfo = $oneOnkoInfo;
		}

		return $OnkoInfo;
	}

	/**
	 * Получение информации о псих
	 */
	function getPsihInfo($EvnPS_id) {
		$PsihInfo = array();

		$resp = $this->queryResult("
			SELECT
				mcp.CrazyEducationType_id AS \"CrazyEducationType_id\",
				mcp.CrazySourceLivelihoodType_id AS \"CrazySourceLivelihoodType_id\",
				mcp.MorbusCrazyPerson_CompleteClassCount AS \"MorbusCrazyPerson_CompleteClassCount\",
				TO_CHAR(PR.PersonRegister_setDate, 'yyyy-mm-ddThh24:mi:ss') AS \"PersonRegister_setDate\",
				mcpi.InvalidGroupType_id AS \"InvalidGroupType_id\"
			FROM
				v_EvnSection es
				INNER JOIN v_MorbusBase mb ON mb.Evn_pid = es.EvnSection_id
				INNER JOIN v_Morbus m ON m.MorbusBase_id = mb.MorbusBase_id
				INNER JOIN v_Diag d ON d.Diag_id = m.Diag_id
				INNER JOIN v_MorbusCrazy mc ON mc.Morbus_id = m.Morbus_id
				LEFT JOIN LATERAL (
					SELECT * FROM v_MorbusCrazyPerson MCP WHERE ES.Person_id = MCP.Person_id ORDER BY MorbusCrazyPerson_insDT ASC LIMIT 1
				) mcp
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT * FROM v_MorbusCrazyPersonInvalid MCPI WHERE MCPI.MorbusCrazyPerson_id = mcp.MorbusCrazyPerson_id ORDER BY MorbusCrazyPersonInvalid_setDT DESC LIMIT 1
				) mcpi
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						PR.PersonRegister_setDate
					FROM
					 	v_PersonRegister PR
					WHERE
						PR.Person_id = MB.Person_id
						AND PR.Morbus_id = M.Morbus_id
					ORDER BY
						PersonRegister_disDate ASC,
						PersonRegister_setDate DESC
					LIMIT 1
				) PR
				ON TRUE
			WHERE
				es.EvnSection_pid = :EvnPS_id
				AND m.MorbusType_id = 4
				AND (
					(d.Diag_Code >= 'F00.0' AND d.Diag_Code < 'F10.0')
					or
					(d.Diag_Code >= 'F20.0' AND d.Diag_Code <= 'F99.9')
				)
			LIMIT 1
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		foreach($resp as $respone) {
			$GroupInvalidityDischarge = null;
			if ($respone['InvalidGroupType_id'] == 2) {
				$GroupInvalidityDischarge = 1;
			} else if ($respone['InvalidGroupType_id'] == 3) {
				$GroupInvalidityDischarge = 2;
			} else if ($respone['InvalidGroupType_id'] == 4) {
				$GroupInvalidityDischarge = 3;
			}

			$PsihInfo = array(
				'EducationPublicCode' => $this->getSyncSpr('Heducation', $respone['CrazyEducationType_id']),
				'SourceOfLivelihoodPublicCode' => $this->getSyncSpr('hSourceOfLivelihood', $respone['CrazySourceLivelihoodType_id']),
				'NumberOfGradesCompleted' => $respone['MorbusCrazyPerson_CompleteClassCount'],
				'NumberOfPreviousHospitalizations' => null, // Не заполняется
				'YearOfTakingOnRecord' => $respone['PersonRegister_setDate'],
				'DateOfPreviousStatements' => null, // Не заполняется
				'WhereReceivedPublicCode' => null, // Не заполняется
				'DurationOfDiseaseAdmission' => null, // Не заполняется
				'SyndromeAdmission' => null, // Не заполняется
				'DisabledDischargePublicCode' => null, // Не заполняется
				'GroupInvalidityDischarge' => $GroupInvalidityDischarge,
				'InvalidityDischargePublicCode' => null, // Не заполняется
				'PsychicAndBehavioralDisordersDueUseSurfactantsPublicCode' => null, // Не заполняется
				'Treatment' => null // Не заполняется
			);
		}

		return $PsihInfo;
	}

	/**
	 * Получение информации о нарко
	 */
	function getNarkoInfo($EvnPS_id) {
		$NarkoInfo = array();

		$resp = $this->queryResult("
			SELECT
				mcp.CrazyEducationType_id AS \"CrazyEducationType_id\",
				mcp.CrazySourceLivelihoodType_id AS \"CrazySourceLivelihoodType_id\",
				mcp.MorbusCrazyPerson_CompleteClassCount AS \"MorbusCrazyPerson_CompleteClassCount\",
				TO_CHAR(PR.PersonRegister_setDate, 'yyyy-mm-ddThh24:mi:ss') AS \"PersonRegister_setDate\",
				mcb.MorbusCrazyBase_id AS \"MorbusCrazyBase_id\"
			FROM
				v_EvnSection es
				INNER JOIN v_MorbusBase mb ON mb.Evn_pid = es.EvnSection_id
				INNER JOIN v_MorbusCrazyBase mcb ON mcb.MorbusBase_id = mb.MorbusBase_id
				INNER JOIN v_Morbus m ON m.MorbusBase_id = mb.MorbusBase_id
				INNER JOIN v_Diag d ON d.Diag_id = m.Diag_id
				INNER JOIN v_MorbusCrazy mc ON mc.Morbus_id = m.Morbus_id
				LEFT JOIN LATERAL (
					SELECT * FROM v_MorbusCrazyPerson MCP WHERE ES.Person_id = MCP.Person_id ORDER BY MorbusCrazyPerson_insDT ASC LIMIT 1
				) mcp
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						PR.PersonRegister_setDate
					FROM
					 	v_PersonRegister PR
					WHERE
						PR.Person_id = MB.Person_id
						AND PR.Morbus_id = M.Morbus_id
					ORDER BY
						PersonRegister_disDate ASC,
						PersonRegister_setDate DESC
					LIMIT 1
				) PR
				ON TRUE
			WHERE
				es.EvnSection_pid = :EvnPS_id
				AND m.MorbusType_id = 4
				AND d.Diag_Code >= 'F10.0'
				AND d.Diag_Code <= 'F19.9'
			LIMIT 1
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		foreach($resp as $respone) {
			$NarkoInfo = array(
				'EducationPublicCode' => $this->getSyncSpr('Heducation', $respone['CrazyEducationType_id']),
				'DateOfPreviousStatements' => null, // Не заполняется
				'NumberOfGradesCompleted' => $respone['MorbusCrazyPerson_CompleteClassCount'],
				'NumberOfPreviousHospitalizations' => null, // Не заполняется
				'WhoLivePastThirtyDaysPublicCode' => null, // Не заполняется
				'FrequencyUseCommonToolsPublicCode' => null, // Не заполняется
				'UsedNarkotics' => $this->getUsedNarkotics($respone['MorbusCrazyBase_id']),
				'GepatitB' => null, // Не заполняется
				'GepatitC' => null, // Не заполняется
				'Tuberkulioz' => null // Не заполняется
			);
		}

		return $NarkoInfo;
	}

	/**
	 * Получение информации о наркотиках
	 */
	function getUsedNarkotics($MorbusCrazyBase_id) {
		$UsedNarkotics = array();

		$resp = $this->queryResult("
			SELECT
				mcd.CrazyDrugType_id AS \"CrazyDrugType_id\",
				mcd.CrazyDrugReceptType_id AS \"CrazyDrugReceptType_id\"
			FROM
				v_MorbusCrazyDrug mcd
			WHERE
				mcd.MorbusCrazyBase_id = :MorbusCrazyBase_id
		", array(
			'MorbusCrazyBase_id' => $MorbusCrazyBase_id
		));

		foreach($resp as $respone) {
			$UsedNarkotics[] = array(
				'AgeFirstDrugSample' => null, // Не заполняется
				'FrequencyUsePublicCode' => null, // Не заполняется
				'IsMain' => ($respone['CrazyDrugType_id'] == 1)?true:false,
				'MethodUsingPublicCode' => $this->getSyncSpr('hMethodUsingNarko', $respone['CrazyDrugReceptType_id']),
				'TermRegularUse' => null, // Не заполняется
				'TypeDrugsPublicCode' => null // Не заполняется
			);
		}

		return $UsedNarkotics;
	}

	/**
	 * Получение информации о родильнице
	 */
	function getObstetrics($EvnPS_id) {
		$Obstetrics = array(
			'Obstetrics' => null, // данные для XML
			'Data' => array() // дополнительные данные
		);

		$resp = $this->queryResult("
			SELECT
				es.EvnSection_id AS \"EvnSection_id\",
				TO_CHAR(bss.BirthSpecStac_OutcomDT, 'yyyy-mm-ddThh24:mi:ss') AS \"BirthSpecStac_OutcomDT\",
				bss.BirthSpecStac_CountPregnancy AS \"BirthSpecStac_CountPregnancy\",
				bss.BirthSpecStac_OutcomPeriod AS \"BirthSpecStac_OutcomPeriod\",
				bss.BirthSpecStac_CountBirth AS \"BirthSpecStac_CountBirth\",
				bss.BirthCharactType_id AS \"BirthCharactType_id\",
				bss.BirthSpecStac_BloodLoss AS \"BirthSpecStac_BloodLoss\",
				bss.BirthSpecStac_CountChild AS \"BirthSpecStac_CountChild\",
				bss.BirthPlace_id AS \"BirthPlace_id\",
				bss.PregnancyResult_id AS \"PregnancyResult_id\",
				bss.AbortMethod_id AS \"AbortMethod_id\",
				Did.Diag_Code AS \"Diag_Code\",
				bss.BirthSpecStac_IsHIVtest AS \"BirthSpecStac_IsHIVtest\",
				bss.BirthSpecStac_IsHIV AS \"BirthSpecStac_IsHIV\",
				bss.BirthSpecStac_IsRWtest AS \"BirthSpecStac_IsRWtest\",
				bss.BirthSpecStac_IsRW AS \"BirthSpecStac_IsRW\"
			FROM
				v_EvnPS eps
				INNER JOIN v_EvnSection es ON es.EvnSection_pid = eps.EvnPS_id
				INNER JOIN v_BirthSpecStac bss ON bss.EvnSection_id = es.EvnSection_id
				LEFT JOIN v_Diag Did ON Did.Diag_id = eps.Diag_id
			WHERE
				eps.EvnPS_id = :EvnPS_id
			LIMIT 1
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		foreach($resp as $respone) {
			$AbortionTypePublicCode = null;
			if (in_array($respone['PregnancyResult_id'], array(2,3))) {
				$AbortionTypePublicCode = 100;
				// Если заключительный основной диагноз в диапазоне O04.0 - O04.9, то 300 «мед. аборт до 12 недель»
				if (!empty($respone['Diag_Code']) && $respone['Diag_Code'] >= 'O04.0' && $respone['Diag_Code'] <= 'O04.9') {
					$AbortionTypePublicCode = 300;
				} else {
					switch ($respone['AbortMethod_id']) {
						case 1:
							$AbortionTypePublicCode = 100;
							break;
						case 2:
						case 3:
						case 4:
							$AbortionTypePublicCode = 300;
							break;
						case 5:
						case 6:
							$AbortionTypePublicCode = 600;
							break;
					}
				}
			}
			$Obstetrics['Obstetrics'] = array(
				'BirthDateTime' => $respone['BirthSpecStac_OutcomDT'],
				'BirthType' => $this->getSyncSpr('hLocation', $respone['BirthPlace_id']),
				'PregnancyNumber' => $respone['BirthSpecStac_CountPregnancy'],
				'PregnancyDate' => $respone['BirthSpecStac_OutcomPeriod'],
				'DeliveryNumber' => !empty($respone['BirthSpecStac_CountBirth'])?$respone['BirthSpecStac_CountBirth']:1,
				'DeliveryTypePublicCode' => $this->getSyncSpr('HBirthTypes', $respone['BirthCharactType_id']),
				'AbortionTypePublicCode' => $AbortionTypePublicCode,
				'Haemorrhage' => $respone['BirthSpecStac_BloodLoss'],
				'IsConsulted' => null, // Не заполняется
				'ConsultCount' => null, // Не заполняется
				'BirthComplicationPublicCode' => null, // Не заполняется
				'BdeathTypePublicCode' => null, // Не заполняется
				'FetalCount' => !empty($respone['BirthSpecStac_CountChild'])?$respone['BirthSpecStac_CountChild']:null,
				'NewBornData' => $this->getNewBornData($respone['EvnSection_id'])
			);

			$Obstetrics['Data'] = $respone;
		}

		return $Obstetrics;
	}

	/**
	 * Получение информации о новорождённых
	 */
	function getNewBornData($EvnSection_id) {
		$NewBornData = array();

		$resp = $this->queryResult("
			SELECT
				TO_CHAR(bss.BirthSpecStac_OutcomDT, 'yyyy-mm-ddThh24:mi:ss') AS \"BirthSpecStac_OutcomDT\",
				bss.BirthPlace_id AS \"BirthPlace_id\",
				pnb.PersonNewborn_Weight AS \"Weight\",
				pnb.PersonNewborn_Height AS \"Height\",
				pnb.PersonNewborn_Head AS \"PersonNewborn_Head\",
				pnb.PersonNewborn_Breast AS \"PersonNewborn_Breast\",
				pnb.ChildTermType_id AS \"ChildTermType_id\",
				bss.BirthSpecStac_CountChild AS \"BirthSpecStac_CountChild\",
				pnb.PersonNewborn_CountChild AS \"CountChild\",
				ps.Sex_id AS \"Sex_id\",
				pnb.ChildTermType_id AS \"ChildTermType_id\",
				gm.ID AS \"MOID\",
				TO_CHAR(pnb.PersonNewborn_BCGDate, 'yyyy-mm-ddThh24:mi:ss') AS \"PersonNewborn_BCGDate\",
				TO_CHAR(pnb.PersonNewborn_HepatitDate, 'yyyy-mm-ddThh24:mi:ss') AS \"PersonNewborn_HepatitDate\",
				TO_CHAR(EPS.EvnPS_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnPS_setDT\",
				Did.Diag_Code AS \"Diag_Code\",
				eps.EvnPS_id AS \"EvnPS_id\",
				NULL AS \"PntDeathTime_id\",
				1 AS \"type\"
			FROM
				v_EvnSection es
				INNER JOIN v_BirthSpecStac bss ON bss.EvnSection_id = es.EvnSection_id
				INNER JOIN v_PersonNewBorn PNB ON PNB.BirthSpecStac_id = bss.BirthSpecStac_id
				LEFT JOIN v_PersonState ps ON ps.Person_id = pnb.Person_id
				LEFT JOIN r101.GetMO gm ON gm.Lpu_id = es.Lpu_id
				LEFT JOIN v_EvnPS eps ON eps.EvnPS_id = pnb.EvnPS_id
				LEFT JOIN v_Diag Did ON Did.Diag_id = eps.Diag_id
			WHERE
				es.EvnSection_id = :EvnSection_id
				
			UNION ALL
			
			SELECT
				TO_CHAR(bss.BirthSpecStac_OutcomDT, 'yyyy-mm-ddThh24:mi:ss') AS \"BirthSpecStac_OutcomDT\",
				bss.BirthPlace_id AS \"BirthPlace_id\",
				cd.ChildDeath_Weight AS \"Weight\",
				cd.ChildDeath_Height AS \"Height\",
				30 AS \"PersonNewborn_Head\",
				30 AS \"PersonNewborn_Breast\",
				cd.ChildTermType_id AS \"ChildTermType_id\",
				bss.BirthSpecStac_CountChild AS \"BirthSpecStac_CountChild\",
				cd.ChildDeath_Count AS \"CountChild\",
				cd.Sex_id AS \"Sex_id\",
				cd.ChildTermType_id AS \"ChildTermType_id\",
				gm.ID AS \"MOID\",
				NULL AS \"PersonNewborn_BCGDate\",
				NULL AS \"PersonNewborn_HepatitDate\",
				NULL AS \"EvnPS_setDT\",
				Did.Diag_Code AS \"Diag_Code\",
				NULL AS \"EvnPS_id\",
				cd.PntDeathTime_id AS \"PntDeathTime_id\",
				2 AS \"type\"
			FROM
				v_EvnSection es
				INNER JOIN v_BirthSpecStac bss ON bss.EvnSection_id = es.EvnSection_id
				INNER JOIN v_ChildDeath CD ON CD.BirthSpecStac_id = bss.BirthSpecStac_id
				LEFT JOIN r101.GetMO gm ON gm.Lpu_id = es.Lpu_id
				LEFT JOIN v_Diag Did ON Did.Diag_id = cd.Diag_id
			WHERE
				es.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $EvnSection_id
		));

		foreach($resp as $respone) {
			$MaturiryTypePublicCode = null;
			switch($respone['ChildTermType_id']) {
				case 1: // Доношенный
					$MaturiryTypePublicCode = 200;
					break;
				case 2: // Недоношенный
					$MaturiryTypePublicCode = 300;
					break;
				case 3: // Переношенный
					$MaturiryTypePublicCode = 400;
					break;
			}

			$Diagnoses = array();
			if ($respone['type'] == 1) {
				if (!empty($respone['EvnPS_id'])) {
					if (!empty($respone['Diag_Code'])) {
						$Diagnoses[] = array(
							'DiagnosePublicCode' => str_replace('.', '', $respone['Diag_Code']),
							'RegisterDate' => $respone['EvnPS_setDT'],
							'DiagnosisTypePublicCode' => 500, // 500. Заключительный
							'DiagnosTypePublicCode' => 200,
							'TraumeTypePublicCode' => null, // Не заполняется
							'NotOpening' => null // Не заполняется
						);
					} else {
						$Diagnoses[] = array(
							'DiagnosePublicCode' => 'Z380',
							'RegisterDate' => $respone['BirthSpecStac_OutcomDT'],
							'DiagnosisTypePublicCode' => 500, // 500. Заключительный
							'DiagnosTypePublicCode' => 200,
							'TraumeTypePublicCode' => null, // Не заполняется
							'NotOpening' => null // Не заполняется
						);
					}
				} else {
					$DiagnosePublicCode = 'Z386';
					if ($respone['BirthSpecStac_CountChild'] == 1) {
						$DiagnosePublicCode = 'Z380';
					} else if ($respone['BirthSpecStac_CountChild'] == 2) {
						$DiagnosePublicCode = 'Z383';
					}
					$Diagnoses[] = array(
						'DiagnosePublicCode' => $DiagnosePublicCode,
						'RegisterDate' => $respone['BirthSpecStac_OutcomDT'],
						'DiagnosisTypePublicCode' => 500, // 500. Заключительный
						'DiagnosTypePublicCode' => 200,
						'TraumeTypePublicCode' => null, // Не заполняется
						'NotOpening' => null // Не заполняется
					);
				}
			} else {
				if (!empty($respone['Diag_Code'])) {
					$Diagnoses[] = array(
						'DiagnosePublicCode' => str_replace('.', '', $respone['Diag_Code']),
						'RegisterDate' => $respone['BirthSpecStac_OutcomDT'],
						'DiagnosisTypePublicCode' => 500, // 500. Заключительный
						'DiagnosTypePublicCode' => 200,
						'TraumeTypePublicCode' => null, // Не заполняется
						'NotOpening' => null // Не заполняется
					);
				}
			}

			$OutcomePublicCode = null;
			$ChildDeathTypePublicCode = null;
			$PolioDate = null;
			if ($respone['type'] == 1) {
				$PolioDate = $respone['BirthSpecStac_OutcomDT'];
				if (empty($respone['EvnPS_id'])) {
					// Если живорожденный и Специфика родов НЕ связана с КВС ребенка, то «100. Выписан».
					$OutcomePublicCode = 100;
				} else {
					// Если живорожденный и Специфика родов связана с КВС ребенка, то «200. Госпитализирован на 2 этапе выхаживания».
					$OutcomePublicCode = 200;
				}
			} else {
				if ($respone['PntDeathTime_id'] == 1) {
					// Если мертворожденный и значение поля «Наступление смерти» формы «Сведения о мертворожденном» = «1. До начала родовой деятельности», то «400. Мёртворожденный антенатальный период».
					$OutcomePublicCode = 400;
					$ChildDeathTypePublicCode = 100;
				} else if ($respone['PntDeathTime_id'] == 2) {
					// Если мертворожденный и значение поля «Наступление смерти» формы «Сведения о мертворожденном» = «2. Во время родов», то «500. Мёртворожденный интранатальный период».
					$OutcomePublicCode = 500;
					$ChildDeathTypePublicCode = 200;
				}
			}

			$isStillborn = null;
			if ($respone['type'] == 2) {
				$isStillborn = true;
			}

			$NewBornData[] = array(
				'Id' => 0, // всегда 0
				'BirthDateTime' => $respone['BirthSpecStac_OutcomDT'],
				'BirthPlacePublicCode' => $this->getSyncSpr('hLocation', $respone['BirthPlace_id']),
				'Weight' => $respone['Weight'],
				'Height' => $respone['Height'],
				'HeadCircumference' => $respone['PersonNewborn_Head'],
				'ChestCircumference' => $respone['PersonNewborn_Breast'],
				'MaturiryTypePublicCode' => $MaturiryTypePublicCode,
				'FetalCount' => !empty($respone['BirthSpecStac_CountChild'])?$respone['BirthSpecStac_CountChild']:null,
				'FetalNumber' => $respone['CountChild'],
				'isStillborn' => $isStillborn,
				'OutcomePublicCode' => $OutcomePublicCode,
				'OutcomeDate' => $respone['BirthSpecStac_OutcomDT'],
				'BcgDate' => $respone['PersonNewborn_BCGDate'],
				'PolioDate' => $PolioDate,
				'HepatitisDate' => $respone['PersonNewborn_HepatitDate'],
				'MaternityId' => $respone['MOID'],
				'SexPublicCode' => $this->getSyncSpr('hBIOSex', $respone['Sex_id']),
				'ChildDeathTypePublicCode' => $ChildDeathTypePublicCode,
				'WasTreatedInHospital' => null, // Не заполняется
				'Diagnoses' => $Diagnoses,
				'TransferalMo' => null // Не заполняется
			);
		}

		return $NewBornData;
	}

	/**
	 * Получение информации об операциях
	 */
	function getSurgProcs($EvnPS_id) {
		$SurgProcs = array();

		$resp = $this->queryResult("
			SELECT
				uc.UslugaComplex_Code AS \"UslugaComplex_Code\",
				euo.EvnUslugaOper_IsEndoskop AS \"EvnUslugaOper_IsEndoskop\",
				euo.EvnUslugaOper_IsLazer AS \"EvnUslugaOper_IsLazer\",
				euo.EvnUslugaOper_IsKriogen AS \"EvnUslugaOper_IsKriogen\",
				euo.EvnUslugaOper_IsRadGraf AS \"EvnUslugaOper_IsRadGraf\",
				COALESCE(euo.AnesthesiaClass_id, euoa.AnesthesiaClass_id) AS \"AnesthesiaClass_id\",
				ea.AggType_id AS \"AggType_id\",
				euo.OperType_id AS \"OperType_id\",
				TO_CHAR(euo.EvnUslugaOper_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnUslugaOper_setDT\",
				datediff(day, es.EvnSection_setDT, euo.EvnUslugaOper_setDT) AS \"BeforeDaysCount\",
				datediff(day, es.EvnSection_setDT, es.EvnSection_disDT) AS \"FuncDaysCount\",
				gph.PostID AS \"PostID\"
			FROM
				v_EvnSection es
				INNER JOIN v_EvnUslugaOper euo ON euo.EvnUslugaOper_pid = es.EvnSection_id
				LEFT JOIN v_UslugaComplex uc ON uc.UslugaComplex_id = euo.UslugaComplex_id
				LEFT JOIN LATERAL (
					SELECT
						euoa.AnesthesiaClass_id
					FROM
						v_EvnUslugaOperAnest euoa
					WHERE
						euoa.EvnUslugaOper_id = euo.EvnUslugaOper_id
					LIMIT 1
				) euoa
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						ea.AggType_id
					FROM
						v_EvnAgg ea
					WHERE
						ea.EvnAgg_pid = euo.EvnUslugaOper_id
					LIMIT 1
				) ea
				ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						gpw.PersonalID,
						gpw.PostID
					FROM
						r101.v_GetPersonalHistoryWP gphwp
						INNER JOIN r101.v_GetPersonalWork gpw ON gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					WHERE
						gphwp.WorkPlace_id = euo.MedStaffFact_id
					ORDER BY
						gphwp.GetPersonalHistoryWP_insDT DESC
					LIMIT 1
				) gph
				ON TRUE
			WHERE
				es.EvnSection_pid = :EvnPS_id
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		$IsMain = true;
		foreach($resp as $respone) {
			$EquipmentPublicCode = 600; // Прочее
			if ($respone['EvnUslugaOper_IsEndoskop'] == 2) {
				$EquipmentPublicCode = 300;
			} else if ($respone['EvnUslugaOper_IsLazer'] == 2) {
				$EquipmentPublicCode = 400;
			} else if ($respone['EvnUslugaOper_IsKriogen'] == 2) {
				$EquipmentPublicCode = 500;
			}

			$hComplication = null;
			if (!empty($respone['AggType_id'])) {
				$hComplication = $this->getSyncSpr('hComplication', $respone['AggType_id'], true);
			}
			if (empty($hComplication)) {
				$hComplication = '100';
			}

			$SurgProcs[] = array(
				'Id' => 0,
				'SurgProcPublicCode' => $respone['UslugaComplex_Code'],
				'IsMain' => $IsMain, // Для первой в списке операции (в рамках данного случая) «true». Для всех прочих операций – «false»
				'AnestheticPublicCode' => !empty($respone['AnesthesiaClass_id']) ? $this->getSyncSpr('hAnaesthesia', $respone['AnesthesiaClass_id']) : '1400', // 1400 - не указано
				'MepFactSurgProcDrugs' => null, // Не заполняется
				'ComplicationsPublicCodes' => array(
					$hComplication
				),
				'IsExtra' => ($respone['OperType_id'] == 2)?true:false,
				'OperationDate' => $respone['EvnUslugaOper_setDT'],
				'BeforeDaysCount' => $respone['BeforeDaysCount'],
				'FuncDaysCount' => $respone['FuncDaysCount'],
				'EquipmentPublicCode' => $EquipmentPublicCode,
				'AnaesthesiologPostID' => $respone['PostID'],
				'AssistantPostID' => null, // Не заполняется
				'SurgeonPostID' => $respone['PostID']
			);

			if ($IsMain) {
				$IsMain = false; // для первой было true, для остальных false.
			}
		}

		return $SurgProcs;
	}

	/**
	 * Получение информации об общих услугах
	 */
	function getServices($EvnPS_id) {
		$Services = array();

		$resp = $this->queryResult("
			SELECT
				uc.UslugaComplex_Code AS \"UslugaComplex_Code\",
				euc.EvnUslugaCommon_KolVo AS \"EvnUslugaCommon_KolVo\"
			FROM
				v_EvnSection es
				INNER JOIN v_EvnUslugaCommon euc ON euc.EvnUslugaCommon_pid = es.EvnSection_id
				LEFT JOIN v_UslugaComplex uc ON uc.UslugaComplex_id = euc.UslugaComplex_id
			WHERE
				es.EvnSection_pid = :EvnPS_id
		", array(
			'EvnPS_id' => $EvnPS_id
		));

		foreach($resp as $respone) {
			$Services[] = array(
				'MepServPublicCode' => $respone['UslugaComplex_Code'],
				'FactCount' => $respone['EvnUslugaCommon_KolVo'],
				'IsCalculated' => null // Не заполняется
			);
		}

		return $Services;
	}

	/**
	 * Получение информации о диагнозах
	 */
	function getDiagnoses($EvnPS_id) {
		$Diagnoses = array();

		$diag_list = array();

		$query = "
			SELECT 
				Did.Diag_Code AS \"Diag_Code\",
				Did.Diag_Name AS \"Diag_Name\",
				COALESCE(Dpid.Diag_Code, Did.Diag_Code) AS \"Diag_pCode\",
				COALESCE(Dpid.Diag_Name, Did.Diag_Name) AS \"Diag_pName\",
				TO_CHAR(EPS.EvnPS_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnPS_setDT\"
			FROM v_EvnPS eps
				LEFT JOIN v_Diag Did ON Did.Diag_id = eps.Diag_id
				LEFT JOIN v_Diag Dpid ON Dpid.Diag_id = eps.Diag_pid
			WHERE eps.EvnPS_id = :EvnPS_id
		";
		$diag = $this->getFirstRowFromQuery($query, array(
			'EvnPS_id' => $EvnPS_id
		));

		// Основной заключительный
		if (!empty($diag['Diag_Code'])){
			$Diagnoses[] = array(
				'DiagnosePublicCode' => str_replace('.', '', $diag['Diag_Code']),
				'RegisterDate' => $diag['EvnPS_setDT'],
				'DiagnosisTypePublicCode' => 500, // 500. Заключительный
				'DiagnosTypePublicCode' => 200,
				'TraumeTypePublicCode' => null, // Не заполняется
				'NotOpening' => null // Не заполняется
			);
		}

		// Основной предварительный
		if (!empty($diag['Diag_pCode'])){
			$Diagnoses[] = array(
				'DiagnosePublicCode' => str_replace('.', '', $diag['Diag_pCode']),
				'RegisterDate' => $diag['EvnPS_setDT'],
				'DiagnosisTypePublicCode' => 300, // 300. Предварительный
				'DiagnosTypePublicCode' => 200,
				'TraumeTypePublicCode' => null, // Не заполняется
				'NotOpening' => null // Не заполняется
			);
		}

		// Прочие
		$query = "
			SELECT 
				Diag.Diag_Code AS \"Diag_Code\",
				Diag.Diag_Name AS \"Diag_Name\",
				DT.p_publCod AS \"diagTypeIdCode\",
				DT.p_nameRU AS \"diagTypeName\",
				TO_CHAR(EDPS.EvnDiagPS_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnDiagPS_setDT\"
			FROM v_EvnDiagPS EDPS
				LEFT JOIN Diag ON Diag.Diag_id = EDPS.Diag_id
				LEFT JOIN r101.hDiagTypeLink DTL ON DTL.DiagSetClass_id = EDPS.DiagSetClass_id
				LEFT JOIN r101.hDiagType DT ON DT.p_ID = DTL.p_ID
			WHERE EDPS.EvnDiagPS_pid = :EvnPS_id
		";
		$sop = $this->queryResult($query, array(
			'EvnPS_id' => $EvnPS_id
		));
		foreach($sop as $diag) {
			$Diagnoses[] = array(
				'DiagnosePublicCode' => str_replace('.', '', $diag['Diag_Code']),
				'RegisterDate' => $diag['EvnDiagPS_setDT'],
				'DiagnosisTypePublicCode' => 500, // 500. Заключительный
				'DiagnosTypePublicCode' => $diag['diagTypeIdCode'],
				'TraumeTypePublicCode' => null, // Не заполняется
				'NotOpening' => null // Не заполняется
			);
		}

		return $Diagnoses;
	}

	/**
	 * Отправка КВС
	 */
	function syncEvnPS($EvnPS_id) {
		$this->textlog->add("syncEvnPS: ".$EvnPS_id);

		$evnPSInfo = $this->getEvnPSInfo(array(
			'EvnPS_id' => $EvnPS_id
		));

		$TypeCasePublicCode = 100; // Обычный случай
		if (!empty($evnPSInfo['Diag_Code']) && mb_substr($evnPSInfo['Diag_Code'], 0, 3) >= 'С00' && mb_substr($evnPSInfo['Diag_Code'], 0, 3) <= 'C97') {
			$TypeCasePublicCode = 200; // Онкологический случай
		} else if (!empty($evnPSInfo['Diag_Code']) && mb_substr($evnPSInfo['Diag_Code'], 0, 3) >= 'D00' && mb_substr($evnPSInfo['Diag_Code'], 0, 3) <= 'D09') {
			$TypeCasePublicCode = 200; // Онкологический случай
		} else if (!empty($evnPSInfo['Diag_Code']) && mb_substr($evnPSInfo['Diag_Code'], 0, 3) >= 'F10' && mb_substr($evnPSInfo['Diag_Code'], 0, 3) <= 'F19') {
			$TypeCasePublicCode = 300; // Наркологический случай
		} else if (!empty($evnPSInfo['Diag_Code']) && mb_substr($evnPSInfo['Diag_Code'], 0, 1) == 'F') {
			$TypeCasePublicCode = 400; // Психологический случай
		}

		$datediff = strtotime($evnPSInfo['EvnPS_disDT']) - strtotime($evnPSInfo['EvnPS_setDT']);
		$evnPSInfo['Duration'] = floor($datediff/(60*60*24));
		if ($evnPSInfo['Duration'] == 0) {
			$evnPSInfo['Duration'] = 1;
		}

		if ($evnPSInfo['LeaveType_SysNick'] == 'die') {
			$TreatmentOutcomePublicCode = 600; // Смерть
		} else {
			$TreatmentOutcomePublicCode = $this->getSyncSpr('hTherapyResult', $evnPSInfo['ResultDesease_id']);
		}

		// Костыль для подсчёта дней в реанимации по профилям коек в движении
		$IntensiveCareBedDaysCount = null;
		$resp_nb = $this->queryResult("
			SELECT
				TO_CHAR(esnb.EvnSectionNarrowBed_setDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnSectionNarrowBed_setDT\",
				TO_CHAR(esnb.EvnSectionNarrowBed_disDT, 'yyyy-mm-ddThh24:mi:ss') AS \"EvnSectionNarrowBed_disDT\"
			FROM
				v_EvnSectionNarrowBed esnb
				INNER JOIN v_EvnSection es ON es.EvnSection_id = esnb.EvnSectionNarrowBed_pid
			WHERE
				es.EvnSection_pid = :EvnPS_id
				AND esnb.EvnSectionNarrowBed_setDT IS NOT NULL
				AND esnb.EvnSectionNarrowBed_disDT IS NOT NULL
		", array(
			'EvnPS_id' => $EvnPS_id
		));
		if (!empty($resp_nb[0])) {
			$IntensiveCareBedDaysCount = 0;
			foreach($resp_nb as $one_nb) {
				$datediff = strtotime($one_nb['EvnSectionNarrowBed_disDT']) - strtotime($one_nb['EvnSectionNarrowBed_setDT']);
				$duration = floor($datediff/(60*60*24));
				if ($duration == 0) {
					$duration = 1;
				}
				$IntensiveCareBedDaysCount += $duration;
			}
		}

		if (empty($evnPSInfo['SocType_Code'])) {
			$SocialStatusesPublicCode = '4900';
			if ($evnPSInfo['Person_Age'] >= 18 && $evnPSInfo['Person_Age'] <= 65) {
				$SocialStatusesPublicCode = '4000';
			} else if ($evnPSInfo['Person_Age'] > 65) {
				$SocialStatusesPublicCode = '4100';
			}
		} else {
			$SocialStatusesPublicCode = $evnPSInfo['SocType_Code'];
		}
		
		$RefTypePublicCode = $this->getSyncSpr('hTreatmentType', $evnPSInfo['PrehospDirect_id'], true);
		if (empty($RefTypePublicCode)) {
			$RefTypePublicCode = ($evnPSInfo['PrehospArrive_id'] == 1) ? 'MNO_200' : 100;
		}

		$params = array(
			'persReg' => array(
				'Version' => null, // Не заполняется
				'InPatientID' => null, // Не заполняется
				'OrgHealthCareId' => $evnPSInfo['MOID'],
				'PatientsAdmissionRegisterId' => $evnPSInfo['Hospitalization_id'],
				'ModifiedDate' => null, // Не заполняется
				'OriginalID' => $evnPSInfo['EvnPS_id'],
				'CardNumber' => $evnPSInfo['EvnPS_NumCard'],
				'DoctorPostID' => $evnPSInfo['PostID'],
				'ExternalSystem' => 13, //  Пьянкова Наталья (10:48:52 22/02/2018) сделай 'ExternalSystem' => 13 // идентификатор внешней системы, передавайте 9 говорят %)
				'FinanceSourcePublicCode' => $evnPSInfo['FinanceSourcePublicCode'],
				'RefTypePublicCode' => $RefTypePublicCode,
				'TypeCasePublicCode' => $TypeCasePublicCode,
				'SendedToMO' => null, // Не заполняется
				'IsVSMP' => null, // Не заполняется
				'IsMedicalAbortion' => null, // Не заполняется
				'NationalityPublicCode' => !empty($evnPSInfo['EthnosRPN_id']) ? $evnPSInfo['EthnosRPN_id'] : 100, // 100 Не указан
				'IsErobLeasing' => true,
				'BloodGroup' => $this->getSyncSpr('hBloodGroups', $evnPSInfo['BloodGroupType_id']),
				'RhFactor' => $this->getSyncSpr('hRhFactors', $evnPSInfo['RhFactorType_id']),
				'DocTypePublicCode' => 100, // Всегда «100. медицинская карта стационарного больного»
				'SvaId' => $evnPSInfo['PRIK_MOID'],
				'DepartmentID' => $evnPSInfo['FPID'],
				'BedProfilePublicCode' => $evnPSInfo['BedProfile_Code'],
				'Forms1ID' => null, // Не заполняется
				'FromMedicalOrgID' => !empty($evnPSInfo['NAP_MOID']) ? $evnPSInfo['NAP_MOID'] : $evnPSInfo['MOID'],
				'HospTypePublicCode' => $this->getSyncSpr('hExtrenType', $evnPSInfo['PrehospType_id']),
				'DiseaseCountPublicCode' => $evnPSInfo['DiseaseCountPublicCode'],
				'HospitalDate' => $evnPSInfo['EvnPS_setDT'],
				'HospitalCode' => $evnPSInfo['Referral_Code'],
				'OutDate' => $evnPSInfo['EvnPS_disDT'],
				'BedDaysCount' => $evnPSInfo['Duration'],
				'IntensiveCareBedDaysCount' => $IntensiveCareBedDaysCount,
				'Autopsy' => null, // Не заполняется
				'BranchManagerPostID' => $evnPSInfo['PostID'],
				'OutcomePublicCode' => $this->getSyncSpr('hHospitalStayResult', $evnPSInfo['LeaveType_id']),
				'TreatmentOutcomePublicCode' => $TreatmentOutcomePublicCode,
				'PoliclinicOrMO' => in_array($evnPSInfo['LpuUnitType_id'], array(6, 9)) ? true : false,
				'HospitalizedPublicCode' => $evnPSInfo['HospitalizedPublicCode'],
				'RecommendationPublicCode' => null, // Не заполняется
				'PerposeResearchPublicCode' => null, // Не заполняется
				'InPatientHelpTypePublicCode' => $evnPSInfo['InPatientHelpTypePublicCode'],
				'AutopsyCode' => null, // Не заполняется
				'AutopsyDate' => null, // Не заполняется
				'HospBedProfilePublicCode' => $evnPSInfo['FirstBedProfile_Code'],
				'DayHospitalCardTypePublicCode' => $evnPSInfo['DayHospitalCardTypePublicCode'],
				'HospFuncStrucureID' => $evnPSInfo['FirstFPID'],
				'BirthWeight' => $evnPSInfo['PersonNewborn_Weight'] / 1000,
				'BirthHeight' => $evnPSInfo['PersonNewborn_Height'],
				'Called' => null, // Не заполняется
				'DrgPublicCode' => null, // Не заполняется
				'drgWeightTypePublicCode' => null, // Не заполняется
				'drgWeight' => null, // Не заполняется
				'drgBaseRate' => $evnPSInfo['AttributeValue_ValueFloat'],
				'PersonRpnId' => $evnPSInfo['BDZ_id'],
				'Weight' => !empty($evnPSInfo['PersonWeight_Weight']) ? $evnPSInfo['PersonWeight_Weight'] : 100000,
				'Height' => !empty($evnPSInfo['PersonHeight_Height']) ? $evnPSInfo['PersonHeight_Height'] : 100,
				'personIIN' => $evnPSInfo['Person_Inn'],
				'surname' => $evnPSInfo['Person_SurName'],
				'firstname' => $evnPSInfo['Person_FirName'],
				'patronymic' => $evnPSInfo['Person_SecName'],
				'birthDate' => $evnPSInfo['Person_BirthDay'],
				'SexPublicCode' => $this->getSyncSpr('hBIOSex', $evnPSInfo['Sex_id']),
				'ResidenseTypePublicCode' => !empty($evnPSInfo['KLTown_id']) ? 400 : 300, // 300 город
				'RWTestDate' => null, // Не заполняется
				'RWIsPositive' => null, // Не заполняется
				'HIVTestDate' => null, // Не заполняется
				'HIVIsPositive' => null, // Не заполняется
				'InCaseOfDeathPublicCode' => null, // Не заполняется
				'EliminatedPublicCode' => null, // Не заполняется
				'NumberReleased' => null, // Не заполняется
				'AdmissionToThisHospitalPublicCode' => null, // Не заполняется
				'Wages' => null, // Не заполняется
				'SocialSecurityTax' => null, // Не заполняется
				'Food' => null, // Не заполняется
				'UtilitiesOtherEpenses' => null, // Не заполняется
				'SurgProcs' => array(),
				'Drugs' => null, // Не заполняется
				'Services' => array(),
				'Diagnoses' => array(),
				'DiagnosComplications' => null, // Не заполняется
				'SocialStatusesPublicCodes' => array(
					$SocialStatusesPublicCode
				),
				'BenefitsPublicCodes' => $evnPSInfo['BenefitsPublicCodes'],
				'Addresses' => array(),
				'addrressRPN' => null, // Не заполняется
				'AddressesFromRPN' => null,
				'OnkoInfo' => null,
				'PsihInfo' => null,
				'NarkoInfo' => null,
				'InPatientAdditionalInfo' => null, // Не заполняется
				'Obstetrics' => null,
				'InPatientOutEpicrisis' => array(),
				'InPatientPayType' => $evnPSInfo['InPatientPayType'],
				'MovementInStacionarHistory' => array(),
				'Regions' => null, // Не заполняется
				'ReportPeriodId' => null // Не заполняется
			),
			'ticket' => $this->_ticket
		);

		$params['persReg']['SurgProcs'] = $this->getSurgProcs($EvnPS_id);
		$params['persReg']['Services'] = $this->getServices($EvnPS_id);
		$params['persReg']['Diagnoses'] = $this->getDiagnoses($EvnPS_id);

		$AddressTypeID = null;
		$RPNBuildingID = null;
		if (!empty($evnPSInfo['UAddress_Address'])) {
			$AddressTypeID = 2;
			$RPNBuildingID = $this->getRPNBuildingID(array(
				'GetBuilding_Number' => $evnPSInfo['UAddress_House'],
				'GetAddressTerr_Name' => $evnPSInfo['UKLStreet_Name']
			));
			$params['persReg']['Addresses'][] = array(
				'RpnBuildingId' => $RPNBuildingID, // Не заполняется
				'RpnApartmentId' => null, // Не заполняется
				'RpnAddressId' => null, // Не заполняется
				'AddressTypePublicCode' => 200,
				'AddressText' => $evnPSInfo['UAddress_Address']
			);
		}
		if (!empty($evnPSInfo['PAddress_Address'])) {
			$AddressTypeID = 4;
			$RPNBuildingID = $this->getRPNBuildingID(array(
				'GetBuilding_Number' => $evnPSInfo['PAddress_House'],
				'GetAddressTerr_Name' => $evnPSInfo['PKLStreet_Name']
			));
			$params['persReg']['Addresses'][] = array(
				'RpnBuildingId' => $RPNBuildingID, // Не заполняется
				'RpnApartmentId' => null, // Не заполняется
				'RpnAddressId' => null, // Не заполняется
				'AddressTypePublicCode' => 400,
				'AddressText' => $evnPSInfo['PAddress_Address']
			);
		}
		if (!empty($evnPSInfo['BAddress_Address'])) {
			$AddressTypeID = 31001300500;
			$RPNBuildingID = $this->getRPNBuildingID(array(
				'GetBuilding_Number' => $evnPSInfo['BAddress_House'],
				'GetAddressTerr_Name' => $evnPSInfo['BKLStreet_Name']
			));
			$params['persReg']['Addresses'][] = array(
				'RpnBuildingId' => $RPNBuildingID, // Не заполняется
				'RpnApartmentId' => null, // Не заполняется
				'RpnAddressId' => null, // Не заполняется
				'AddressTypePublicCode' => 31001300500,
				'AddressText' => $evnPSInfo['BAddress_Address']
			);
		}

		if (false && !empty($AddressTypeID)) { // данный тег не выгружать (с) Пьянкова Наталья
			$params['persReg']['AddressesFromRPN'] = array(
				array(
					'AddressTypeID' => $AddressTypeID,
					'DemographicData' => null, // Не заполняется
					'Elements' => array(
						array(
							'ElementTypeID' => 10, // 10 - Республика
							'ElementTypeNameKZ' => 'РЕСПУБЛИКАСЫ',
							'ElementTypeNameRU' => 'РЕСПУБЛИКА',
							'ElementValueKZ' => 'Казахстан',
							'ElementValueRU' => 'Казахстан',
							'OrderIndex' => null // Не заполняется
						)
					),
					'RPNAddressID' => null, // Не заполняется
					'RPNApartmentID' => null, // Не заполняется
					'RPNBuildingID' => $RPNBuildingID, // Не заполняется
					'RegionID' => 1, // 1 - Казахстан
					'kato' => null // Не заполняется
				)
			);
		}

		$params['persReg']['OnkoInfo'] = $this->getOnkoInfo($EvnPS_id);
		$params['persReg']['PsihInfo'] = $this->getPsihInfo($EvnPS_id);
		$params['persReg']['NarkoInfo'] = $this->getNarkoInfo($EvnPS_id);

		// специфика беременности
		$Obstetrics = $this->getObstetrics($EvnPS_id);
		$params['persReg']['Obstetrics'] = $Obstetrics['Obstetrics'];
		if (!empty($Obstetrics['Obstetrics']) && empty($Obstetrics['Obstetrics']['AbortionTypePublicCode'])) { // если есть специфика и не аборт
			$params['persReg']['DocTypePublicCode'] = 200; // 200 - История родов
		}
		if (!empty($Obstetrics['Data']['BirthSpecStac_IsHIVtest']) && $Obstetrics['Data']['BirthSpecStac_IsHIVtest'] == 2) { // если есть тест на ВИЧ в специфике
			$params['persReg']['HIVTestDate'] = $evnPSInfo['EvnPS_setDT'];
			$params['persReg']['HIVIsPositive'] = (!empty($Obstetrics['Data']['BirthSpecStac_IsHIV']) && $Obstetrics['Data']['BirthSpecStac_IsHIV'] == 2);
		}
		if (!empty($Obstetrics['Data']['BirthSpecStac_IsRWtest']) && $Obstetrics['Data']['BirthSpecStac_IsRWtest'] == 2) { // если есть тест на сифилис в специфике
			$params['persReg']['RWTestDate'] = $evnPSInfo['EvnPS_setDT'];
			$params['persReg']['RWIsPositive'] = (!empty($Obstetrics['Data']['BirthSpecStac_IsRW']) && $Obstetrics['Data']['BirthSpecStac_IsRW'] == 2);
		}

		$params['persReg']['InPatientOutEpicrisis'] = $this->getInPatientOutEpicrisis($EvnPS_id);
		$params['persReg']['MovementInStacionarHistory'] = $this->getMovementInStacionarHistory($EvnPS_id);

		$response = $this->exec('Esrb', 'SaveExternalPersonifiedRegisterTicket', $params);

		if (!empty($response->SaveExternalPersonifiedRegisterTicketResult->ErrorMessage)) {
			throw new Exception('Ошибка при выполнении SaveExternalPersonifiedRegisterTicket: ' . $response->SaveExternalPersonifiedRegisterTicketResult->ErrorMessage, 400);
		}

		$this->saveSyncObject('EvnPS', $EvnPS_id, null); // идентификатор никакой не возвращается.
	}

	/**
	 * Получение RPNBuidlingID
	 */
	function getRPNBuildingID($data) {
		$RPNBuildingID = null;
		$resp = $this->queryResult("
			SELECT
				gb.GetBuilding_id AS \"GetBuilding_id\"
			FROM
				r101.GetBuilding gb
				INNER JOIN r101.GetAddressTerr gat ON gat.GetAddressTerr_id = gb.GetAddressTerr_id
			WHERE
				gat.GetAddressTerr_Name = :GetAddressTerr_Name
				AND gb.GetBuilding_Number = :GetBuilding_Number
			LIMIT 1
		", array(
			'GetAddressTerr_Name' => $data['GetAddressTerr_Name'],
			'GetBuilding_Number' => $data['GetBuilding_Number']
		));
		if (!empty($resp[0]['GetBuilding_id'])) {
			$RPNBuildingID = $resp[0]['GetBuilding_id'];
		}
		return $RPNBuildingID;
	}

	/**
	 * Отправка всех закрытых КВС за прошедшие сутки
	 */
	function syncAll($data) {
		$this->load->model('ServiceList_model');
		$ServiceList_id = 4;
		$begDT = date('Y-m-d H:i:s');
		$resp = $this->ServiceList_model->saveServiceListLog(array(
			'ServiceListLog_id' => null,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListResult_id' => 2,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
		}
		$ServiceListLog_id = $resp[0]['ServiceListLog_id'];

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			$this->login();
			// получаем список КВС, закрытых вчера, пихаем каждую в сервис
			$EvnPS_disDT1 = date('Y-m-d', time() - 3 * 24 * 60 * 60);
			$EvnPS_disDT2 = date('Y-m-d', time() - 24 * 60 * 60);

			$queryParams = array(
				'EvnPS_disDT1' => $EvnPS_disDT1,
				'EvnPS_disDT2' => $EvnPS_disDT2
			);
			$filter = " AND CAST(eps.EvnPS_disDT AS DATE) between :EvnPS_disDT1 AND :EvnPS_disDT2";

			if (!empty($this->_esrbConfig['Lpu_ids'])) {
				$filter .= " AND eps.Lpu_id IN ('".implode("','", $this->_esrbConfig['Lpu_ids'])."')";
			}

			if (!empty($data['EvnPS_id'])) {
				$queryParams = array(
					'EvnPS_id' => $data['EvnPS_id']
				);
				$filter = " AND eps.EvnPS_id = :EvnPS_id";
			}

			$query = "
				SELECT
					eps.EvnPS_id AS \"EvnPS_id\",
					eps.EvnPS_NumCard AS \"EvnPS_NumCard\"
				FROM
					v_EvnPS eps
					INNER JOIN v_PayType pt ON pt.PayType_id = eps.PayType_id -- только с видами оплаты региона
					INNER JOIN v_Lpu l ON l.Lpu_id = eps.Lpu_id -- только с МО региона
					-- INNER JOIN v_Person P ON p.Person_id = EPS.Person_id AND p.BDZ_id IS NOT NULL -- пусть валится в ошибки (Это руководство пользователю - что нужно провести идентификацию пациента в РПН.)
					LEFT JOIN LATERAL (
						SELECT
							es.EvnSection_id
						FROM
							v_EvnSection ES
						WHERE
							ES.EvnSection_pid = EPS.EvnPS_id
							AND COALESCE(ES.EvnSection_IsPriem, 1) = 1
						LIMIT 1
					) ESLAST -- есть движения
					ON TRUE
				WHERE
					pt.PayType_SysNick IN ('bud', 'Resp')
					{$filter}
			";
			$resp = $this->queryResult($query, $queryParams);
			foreach ($resp as $respone) {
				try {
					if (!empty($_REQUEST['getDebug'])) {
						echo "<b>Отправка КВС №{$respone['EvnPS_NumCard']}</b>";
					}
					$this->syncEvnPS($respone['EvnPS_id']);
				} catch (Exception $e) {
					if (!empty($_REQUEST['getDebug'])) {
						var_dump($e->getMessage());
					}
					// падать не будем, просто пишем в лог инфу и идем дальше
					$this->textlog->add("syncAll error: code: " . $e->getCode() . " message: " . $e->getMessage());
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => $e->getMessage() . " (EvnPS_id={$respone['EvnPS_id']})",
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			$endDT = date('Y-m-d H:i:s');
			$resp = $this->ServiceList_model->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 1,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
		} catch(Exception $e) {
			if (!empty($_REQUEST['getDebug'])) {
				var_dump($e->getMessage());
			}
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $e->getMessage(),
				'pmUser_id' => $data['pmUser_id']
			));

			$endDT = date('Y-m-d H:i:s');
			$this->ServiceList_model->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 3,
				'pmUser_id' => $data['pmUser_id']
			));
		}
		restore_exception_handler();
	}

	/**
	 * Получение данных. Метод для API
	 */
	function getGetPersonalHistoryWPForAPI($data) {
		$filter = "msf.Lpu_id = :Lpu_id";
		$queryParams = array('Lpu_id' => $data['Lpu_id']);
		$fields = "";
		if (!empty($data['WorkPlace_id'])) {
			$fields .= "
				gpw.PersonalID AS \"PersonalID\",
				gpw.FPID AS \"FPID\",
			";
			$filter .= " AND gphwp.WorkPlace_id = :WorkPlace_id";
			$queryParams['WorkPlace_id'] = $data['WorkPlace_id'];
		}

		return $this->queryResult("
			SELECT
				{$fields}
				gpw.MOID AS \"MOID\",
				gpw.SpecialityID AS \"SpecialityID\"
			FROM
				r101.v_GetPersonalHistoryWP gphwp
				INNER JOIN r101.v_GetPersonalWork gpw ON gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
				INNER JOIN v_MedStaffFact msf ON msf.MedStaffFact_id = gphwp.WorkPlace_id 
			WHERE
				{$filter}
			ORDER BY
				gphwp.GetPersonalHistoryWP_insDT DESC
		", $queryParams);
	}

	/**
	 * COVID-19
	 */
	function savePatientAdmissionMoveData() {
		$start = (empty($_REQUEST['start']))?date('Y-m-d'):$_REQUEST['start'];

		$filterForEnvPsID = (empty($_REQUEST['Evn_id']))?'':"and eps.EvnPS_id = {$_REQUEST['Evn_id']}";

		$this->login();

		$sql = "
			with EvnPSList as (
				select
					ps.Person_SurNameR as \"Person_SurNameR\"
					,p.BDZ_id as \"BDZ_id\"
					,es.EvnSection_id as \"EvnSection_id\"					
					,utES.UslugaTest_id as \"ESUslugaTest_id\"
					,utES.UslugaTest_CheckDT as \"ESUslugaTest_CheckDT\"
					,utES.UslugaTest_ResultValue as \"ESUslugaTest_ResultValue\"
					,utES.UslugaTest_ResultApproved as \"ESUslugaTest_ResultApproved\"
					,utEPS.UslugaTest_id as \"EPSUslugaTest_id\"
					,utEPS.UslugaTest_CheckDT as \"EPSUslugaTest_CheckDT\"
					,utEPS.UslugaTest_ResultValue as \"EPSUslugaTest_ResultValue\"
					,utEPS.UslugaTest_ResultApproved as \"EPSUslugaTest_ResultApproved\"
					,erp.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\"
					,erp.EvnReanimatPeriod_setDT as \"EvnReanimatPeriod_setDT\"
					,erp.EvnReanimatPeriod_disDT as \"EvnReanimatPeriod_disDT\"
					,era.EvnReanimatAction_id as \"EvnReanimatAction_id\"
					,era.EvnReanimatAction_setDT as \"EvnReanimatAction_setDT\"
					,era.EvnReanimatAction_disDT as \"EvnReanimatAction_disDT\"
					,uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					,epsl.Hospitalization_id as \"Hospitalization_id\"
				from v_EvnPS eps
					inner join r101.v_EvnPSLink epsl on epsl.EvnPS_id = eps.EvnPS_id --случай есть в БГ
					inner join v_PersonState ps on eps.Person_id = ps.Person_id
					inner join v_Person p on eps.Person_id = p.Person_id
					inner join v_EvnSection es on es.EvnSection_pid = eps.EvnPS_id
					left join r101.EvnLinkAPP edla on edla.Evn_id = es.EvnSection_id
					left join v_Diag dcid on dcid.Diag_id = edla.Diag_cid
					inner join v_Diag did on did.Diag_id = es.Diag_id
					
					left join v_EvnUslugaPar eupES on eupES.EvnUslugaPar_pid = es.EvnSection_id
					left join v_EvnUslugaPar eupEPS on eupEPS.EvnUslugaPar_pid = eps.EvnPS_id
					left join v_UslugaTest utES on utES.UslugaTest_pid = eupES.EvnUslugaPar_id
					left join v_UslugaTest utEPS on utEPS.UslugaTest_pid = eupEPS.EvnUslugaPar_id
					left join UslugaComplex ucES on eupES.UslugaComplex_id = ucES.UslugaComplex_id
					left join UslugaComplex ucEPS on eupEPS.UslugaComplex_id = ucEPS.UslugaComplex_id
					
					left join v_EvnReanimatPeriod erp on erp.EvnReanimatPeriod_pid = es.EvnSection_id
					left join v_EvnReanimatAction era on era.EvnReanimatAction_pid = erp.EvnReanimatPeriod_id
				where
					p.BDZ_id is not null
					and
					( 
						(to_char(utES.UslugaTest_CheckDT,'yyyy-mm-dd') = :start and ucES.UslugaComplex_Code like '%B09.863.020%' and utES.UslugaTest_ResultApproved = 2) or
						(to_char(utEPS.UslugaTest_CheckDT,'yyyy-mm-dd') = :start and ucEPS.UslugaComplex_Code like '%B09.863.020%' and utEPS.UslugaTest_ResultApproved = 2) or
						(to_char(erp.EvnReanimatPeriod_disDT,'yyyy-mm-dd') = :start or to_char(erp.EvnReanimatPeriod_setDT,'yyyy-mm-dd') = :start) or
						(to_char(era.EvnReanimatAction_disDT,'yyyy-mm-dd') = :start or to_char(era.EvnReanimatAction_setDT,'yyyy-mm-dd') = :start)		
					)
					and 
					( 
						did.Diag_Code like 'B34.2'
						or did.Diag_Code like 'Z20.8'
						or did.Diag_Code like 'Z20.9'
						or (
							dcid.Diag_Code like 'B97.2' and (
								did.Diag_Code like 'J20.9'
								or did.Diag_Code like 'J80'
								or did.Diag_Code like 'J12.8'
								or did.Diag_Code like 'J06.8'
								or did.Diag_Code like 'O99.5'
							)
						)
						or (
							dcid.Diag_Code like 'Z20.8' and (
								did.Diag_Code like 'O99.5'
								or (SUBSTRING(did.Diag_Code,1,3) >= 'J00' and SUBSTRING(did.Diag_Code,1,3) <= 'J99')
							)
						)
						{$filterForEnvPsID}
					)
			)
			select 
				EvnPSList.Person_SurNameR
				,EvnPSList.BDZ_id as \"BDZ_id\"
				,EvnPSList.Hospitalization_id as \"Hospitalization_id\"
				,EvnPSList.ESUslugaTest_id as id
				,'100' as \"PatientsAdmissionMoveTypePublicCode\"
				,to_char(EvnPSList.ESUslugaTest_CheckDT,'yyyy-mm-ddThh24:mi:ss') as \"RegDate\"
				,case when EvnPSList.ESUslugaTest_ResultValue like '%пол%' then 1 else 0 end as \"Value\" 
			from 
				EvnPSList
			where
				to_char(EvnPSList.ESUslugaTest_CheckDT,'yyyy-mm-dd') = :start--Дата и время одобрения результата теста
			union all
			select 
				EvnPSList.Person_SurNameR
				,EvnPSList.BDZ_id as \"BDZ_id\"
				,EvnPSList.Hospitalization_id as \"Hospitalization_id\"
				,EvnPSList.EPSUslugaTest_id as id
				,'100' as \"PatientsAdmissionMoveTypePublicCode\"
				,to_char(EvnPSList.EPSUslugaTest_CheckDT,'yyyy-mm-ddThh24:mi:ss') as \"RegDate\"
				,case when EvnPSList.EPSUslugaTest_ResultValue like '%пол%' then 1 else 0 end as \"Value\" 
			from 
				EvnPSList
			where
				to_char(EvnPSList.EPSUslugaTest_CheckDT,'yyyy-mm-dd') = :start--Дата и время одобрения результата теста
			union all
			select 
				EvnPSList.Person_SurNameR
				,EvnPSList.BDZ_id as \"BDZ_id\"
				,EvnPSList.Hospitalization_id as \"Hospitalization_id\"
				,EvnPSList.EvnReanimatPeriod_id as id
				,'200' as \"PatientsAdmissionMoveTypePublicCode\"
				,to_char(EvnPSList.EvnReanimatPeriod_setDT,'yyyy-mm-ddThh24:mi:ss') as \"RegDate\"
				,1 as \"Value\"
			from 
				EvnPSList
			where 
				to_char(EvnPSList.EvnReanimatPeriod_setDT,'yyyy-mm-dd') = :start
			union all
			select 
				EvnPSList.Person_SurNameR
				,EvnPSList.BDZ_id as \"BDZ_id\"
				,EvnPSList.Hospitalization_id as \"Hospitalization_id\"
				,EvnPSList.EvnReanimatPeriod_id as id
				,'200' as \"PatientsAdmissionMoveTypePublicCode\"
				,to_char(EvnPSList.EvnReanimatPeriod_disDT,'yyyy-mm-ddThh24:mi:ss') as \"RegDate\"
				,0 as \"Value\"
			from 
				EvnPSList
			where 
				to_char(EvnPSList.EvnReanimatPeriod_disDT,'yyyy-mm-dd') = :start
			union all
			select
				EvnPSList.Person_SurNameR
				,EvnPSList.BDZ_id as \"BDZ_id\"
				,EvnPSList.Hospitalization_id as \"Hospitalization_id\"
				,EvnPSList.EvnReanimatAction_id as id
				,'300' as \"PatientsAdmissionMoveTypePublicCode\"
				,to_char(EvnPSList.EvnReanimatAction_setDT,'yyyy-mm-ddThh24:mi:ss') as \"RegDate\"
				,1 as \"Value\"
			from 
				EvnPSList	
			where	
				to_char(EvnPSList.EvnReanimatAction_setDT,'yyyy-mm-dd') = :start
			union all
			select
				EvnPSList.Person_SurNameR
				,EvnPSList.BDZ_id as \"BDZ_id\"
				,EvnPSList.Hospitalization_id as \"Hospitalization_id\"
				,EvnPSList.EvnReanimatAction_id as id
				,'300' as \"PatientsAdmissionMoveTypePublicCode\"
				,to_char(EvnPSList.EvnReanimatAction_disDT,'yyyy-mm-ddThh24:mi:ss') as \"RegDate\"
				,0 as \"Value\"
			from 
				EvnPSList
			where
				to_char(EvnPSList.EvnReanimatAction_disDT,'yyyy-mm-dd') = :start
		";

		$result = $this->queryResult($sql,['start'=>$start]);
		
		foreach ($result as $res) {
			try {
				$xml = "
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:tem=\"http://tempuri.org/\" xmlns:mss=\"http://schemas.datacontract.org/2004/07/MSS.Business.Data.AcReg.ForExternalService\">
	<soapenv:Header>
		<TokenKey>{$this->_ticket}</TokenKey>
	</soapenv:Header>
	<soapenv:Body>
		<tem:SavePatientAdmissionMoveData>
			<tem:extData>
				<mss:PatientsAdmissionMoveTypePublicCode>{$res['PatientsAdmissionMoveTypePublicCode']}</mss:PatientsAdmissionMoveTypePublicCode>
				<mss:PatientsAdmissionRegisterId>{$res['Hospitalization_id']}</mss:PatientsAdmissionRegisterId>
				<mss:PersonId>{$res['BDZ_id']}</mss:PersonId>
				<mss:RegDate>{$res['RegDate']}</mss:RegDate>
				<mss:Value>{$res['Value']}</mss:Value>
			</tem:extData>
		</tem:SavePatientAdmissionMoveData>
	</soapenv:Body>
</soapenv:Envelope>";

				$this->exec('Esrb', 'SavePatientAdmissionMoveData', null, $xml);
			} catch (Exception $e) {
				//Чтобы не падать
			}
		}

		return ['success' => true];
	}
}