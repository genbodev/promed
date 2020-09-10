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

class Ersb_model extends swModel {
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
			$filter .= " and ERSBRefbook_MapName = :Refbook_MapName";
			$queryParams['Refbook_MapName'] = $data['Refbook_MapName'];
		}
		$resp = $this->queryResult("
			select
				ERSBRefbook_id,
				ERSBRefbook_Code,
				ERSBRefbook_Name,
				ERSBRefbook_MapName,
				Refbook_TableName
			from
				r101.v_ERSBRefbook with (nolock)
			where
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
			select
				our.{$ourKey} as id,
				our.{$ourCode} as Code,
				their.P_ID as ERSB_id,
				their.p_publCod as ERSB_Code
			from
				{$mapTable} link
				inner join {$ourTable} our on our.{$ourKey} = link.{$ourKey}
				inner join {$theirTable} their on their.P_ID = link.P_ID
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
				select
					ERSBRefbook_id,
					ERSBRefbook_Code,
					ERSBRefbook_Name,
					ERSBRefbook_MapName,
					Refbook_TableName
				from
					r101.v_ERSBRefbook with (nolock)
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
				select top 1
					{$idField} as id,
					their.{$field} as code
				from
					{$mapTable} link with (nolock) 
					inner join {$theirTable} their on their.P_ID = link.P_ID
				where
					link.{$advancedKey} = :{$advancedKey} 
			";

			if(in_array($table, array('hTreatmentType'))) {
				$query = "
					select top 1
						{$idField} as id,
						their.{$table}_SysNick as code
					from
						{$mapTable} link with (nolock) 
						left join {$theirTable} their on their.{$table}_id = link.{$table}_id
					where
						link.{$advancedKey} = :{$advancedKey} 
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

		$query = "
			declare
				@AttributeVision_TablePKey bigint = (select TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'bazstac');
				
			select top 1
				EPS.EvnPS_id,
				EPS.EvnPS_NumCard,
				EPS.PayType_id,
				PBG.BloodGroupType_id,
				PBG.RhFactorType_id,
				ESLAST.LpuSectionBedProfile_id,
				EPS.PrehospType_id,
				convert(varchar(19), EPS.EvnPS_setDT, 126) as EvnPS_setDT,
				convert(varchar(19), EPS.EvnPS_disDT, 126) as EvnPS_disDT,
				ESLAST.LeaveType_id,
				ESLAST.ResultDesease_id,
				ESLAST.LpuUnitType_id,
				pnb.PersonNewborn_Weight,
				pnb.PersonNewborn_Height,
				ESLAST.MesTariff_Value,
				av.AttributeValue_ValueFloat,
				P.BDZ_id,
				ps.Person_Inn,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				convert(varchar(19), ps.Person_BirthDay, 126) as Person_BirthDay,
				ps.Sex_id,
				ps.SocStatus_id,
				ua.Address_Address as UAddress_Address,
				ua.Address_House as UAddress_House,
				ukls.KLStreet_Name as UKLStreet_Name,
				pa.Address_Address as PAddress_Address,
				pa.Address_House as PAddress_House,
				pkls.KLStreet_Name as PKLStreet_Name,
				ba.Address_Address as BAddress_Address,
				ba.Address_House as BAddress_House,
				bkls.KLStreet_Name as BKLStreet_Name,
				gph.PersonalID,
				gph.PostID,
				ISNULL(FPLAST.FPID, gph.FPID) as FPID,
				ISNULL(FPFIRST.FPID, firstgph.FPID) as FirstFPID,
				gph.MOID,
				gmp.ID as PRIK_MOID, 
				case when epslast.EvnPS_id is not null then 200 else 100 end as DiseaseCountPublicCode,
				case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float)
				else
					cast(pw.PersonWeight_Weight as float) * 1000
				end as PersonWeight_Weight,
				cast(PH.PersonHeight_Height as float) as PersonHeight_Height,
				ua.KLTown_id,
				ISNULL(gph_did.MOID, gmd.ID) as NAP_MOID,
				hbp.p_publCod as BedProfile_Code,
				ESFIRST.BedProfile_Code as FirstBedProfile_Code,
				htsf.p_publCod as FinanceSourcePublicCode,
				hipht.p_publCod as InPatientHelpTypePublicCode,
				eps.PrehospDirect_id,
				eps.PrehospArrive_id,
				EPL.Hospitalization_id,
				EDL.Referral_Code,
				E.EthnosRPN_id,
				ESLAST.LeaveType_SysNick,
				ESLAST.InPatientPayType,
				hSocType.code as SocType_Code,
				isnull(pp.SocialStatusesPublicCode, 2700) as BenefitsPublicCodes,
				dbo.Age2(ps.Person_BirthDay, EPS.EvnPS_setDT) as Person_Age,
				case
					when ESFIRST.EvnSection_IsAdultEscort = 2 and dbo.Age2(ps.Person_BirthDay, EPS.EvnPS_setDT) < 1 then 300
					when ESFIRST.EvnSection_IsAdultEscort = 2 then 200
					else 100
				end as HospitalizedPublicCode,
				case 
					when ESLAST.LpuUnitType_id not in (6,7,9) then null
					else coalesce(dhctd.publCod, dhctucl.publCod, dhctuc.publCod) 
				end as DayHospitalCardTypePublicCode
			from
				v_EvnPS EPS with (nolock)
				left join r101.v_EvnPSLink epl with (nolock) on epl.EvnPS_id = eps.EvnPS_id
				left join r101.v_EvnDirectionLink edl with (nolock) on edl.EvnDirection_id = eps.EvnDirection_id
				cross apply (
					select top 1
						es.EvnSection_id,
						ls.LpuSectionBedProfile_id,
						es.LeaveType_id,
						COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EOS.ResultDesease_id, ED.ResultDesease_id) as ResultDesease_id,
						lu.LpuUnitType_id,
						mt.MesTariff_Value,
						es.MedStaffFact_id,
						es.LpuSection_id,
						ptersb.PayTypeERSB_publCod as InPatientPayType,
						lt.LeaveType_SysNick
					from
						v_EvnSection ES with (nolock)
						left join v_LpuSection ls with (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
						left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ES.EvnSection_id
						left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ES.EvnSection_id
						left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ES.EvnSection_id
						left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ES.EvnSection_id
						left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ES.EvnSection_id
						left join v_ResultDesease RD with (nolock) on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EOS.ResultDesease_id, ED.ResultDesease_id)
						left join v_MesTariff mt with (nolock) on mt.MesTariff_id = es.MesTariff_id
						left join v_MesOld mo with (nolock) on mo.Mes_id = mt.Mes_id
						left join v_LeaveType lt with (nolock) on lt.LeaveType_id = es.LeaveType_id
						left join v_PayTypeERSB ptersb with (nolock) on ptersb.PayTypeERSB_id = es.PayTypeERSB_id
					where
						ES.EvnSection_pid = EPS.EvnPS_id
						and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					order by
						ES.EvnSection_setDT desc
				) ESLAST
				cross apply (
					select top 1
						es.EvnSection_id,
						es.LpuSection_id,
						es.MedStaffFact_id,
						es.EvnSection_IsAdultEscort,
						hbp.p_publCod as BedProfile_Code
					from
						v_EvnSection ES with (nolock)
						left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = ES.EvnSection_id
						left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
						left join r101.hBedProfile hbp (nolock) on hbp.p_ID = gb.BedProfile
					where
						ES.EvnSection_pid = EPS.EvnPS_id
						and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					order by
						ES.EvnSection_setDT asc
				) ESFIRST
				outer apply(
					select top 1
						FPID
					from
						r101.v_LpuSectionFPIDLink (nolock)
					where
						LpuSection_id = ESLAST.LpuSection_id
				) FPLAST
				outer apply(
					select top 1
						FPID
					from
						r101.v_LpuSectionFPIDLink (nolock)
					where
						LpuSection_id = ESFIRST.LpuSection_id
				) FPFIRST
				outer apply (
					select top 1
						pnb.PersonNewborn_Weight,
						pnb.PersonNewborn_Height
					from
						v_EvnSection es with (nolock)
						inner join v_BirthSpecStac bss (nolock) on bss.EvnSection_id = es.EvnSection_id
						inner join v_PersonNewBorn PNB (nolock) on PNB.BirthSpecStac_id = bss.BirthSpecStac_id
					where
						es.EvnSection_pid = eps.EvnPS_id
				) pnb
				outer apply (
					SELECT top 1
						av.AttributeValue_ValueFloat
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					WHERE
						avis.AttributeVision_TableName = 'dbo.TariffClass'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, EPS.EvnPS_setDT) <= EPS.EvnPS_setDT
						and ISNULL(av.AttributeValue_endDate, EPS.EvnPS_setDT) >= EPS.EvnPS_setDT
				) av
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.FPID,
						gpw.MOID,
						gpw.PostID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = ESLAST.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = ESLAST.EvnSection_id
				left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
				left join r101.hBedProfile hbp (nolock) on hbp.p_ID = gb.BedProfile
				left join r101.hInPatientHelpTypes hipht (nolock) on hipht.p_ID = gb.StacType
				left join r101.hTypSrcFin htsf (nolock) on htsf.p_ID = gb.TypeSrcFin
				outer apply (
					select top 1
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = ESFIRST.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) firstgph
				outer apply (
					select top 1
						eps2.EvnPS_id
					from
						v_EvnPS eps2 (nolock)
					where
						eps2.EvnPS_setDT < eps.EvnPS_setDT
				) epslast
				outer apply (
					select top 1
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = EPS.MedStaffFact_did
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph_did
				left join v_Lpu LD with (nolock) on LD.Org_id = EPS.Org_did
				left join r101.GetMO gmd with (nolock) on gmd.Lpu_id = LD.Lpu_id
				left join v_PersonBloodGroup PBG with (nolock) on PBG.Person_id = EPS.Person_id
				left join v_PersonState PS with (nolock) on ps.Person_id = EPS.Person_id
				left join r101.GetMO gmp with (nolock) on gmp.Lpu_id = PS.Lpu_id
				left join v_Person P with (nolock) on p.Person_id = EPS.Person_id
				left join v_PersonInfo PI with (nolock) on PI.Person_id = PS.Person_id
				left join v_Ethnos E with (nolock) on E.Ethnos_id = PI.Ethnos_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Address_all pa with (nolock) on pa.Address_id = ps.PAddress_id
				left join v_PersonBirthPlace pbp with (nolock) on ps.Person_id = pbp.Person_id
				left join v_Address_all ba with (nolock) on ba.Address_id = pbp.Address_id
				left join v_KLStreet ukls with (nolock) on ukls.KLStreet_id = ua.KLStreet_id
				left join v_KLStreet pkls with (nolock) on pkls.KLStreet_id = pa.KLStreet_id
				left join v_KLStreet bkls with (nolock) on bkls.KLStreet_id = ba.KLStreet_id
				outer apply (
					select top 1 * from v_PersonHeight ph (nolock) where ph.Person_id = EPS.Person_id order by ph.PersonHeight_setDT desc
				) ph
				outer apply (
					select top 1 * from v_PersonWeight pw (nolock) where pw.Person_id = EPS.Person_id order by pw.PersonWeight_setDT desc
				) pw
				outer apply (
					select top 1 
						hSocType.code as SocialStatusesPublicCode
					from 
						v_PersonPrivilege pp (nolock)
						left join r101.hSocTypeLink hSocTypeLink (nolock) on hSocTypeLink.PrivilegeType_id = pp.PrivilegeType_id
						left join r101.hSocType hSocType (nolock) on hSocType.id = hSocTypeLink.id
					where pp.Person_id = EPS.Person_id
						and EPS.EvnPS_setDT between pp.PersonPrivilege_begDate and isnull(pp.PersonPrivilege_endDate, '2099-01-01')
					order by pp.PersonPrivilege_begDate desc
				) pp
				left join r101.hSocTypeSocStatus (nolock) on hSocTypeSocStatus.SocStatus_id = ps.SocStatus_id
				left join r101.hSocType (nolock) on hSocType.id = hSocTypeSocStatus.id
				outer apply (
					select top 1 dhct.publCod
					from r101.hDayHospitalCardTypesDiag dhctd (nolock)
					inner join r101.hDayHospitalCardTypes dhct (nolock) on dhct.ID = dhctd.hDayHospitalCardTypes_id
					inner join v_EvnUsluga eu (nolock) on eu.UslugaComplex_id = dhctd.UslugaComplex_id and eu.EvnUsluga_rid = EPS.EvnPS_id
					where dhctd.Diag_id in (EPS.Diag_id, EPS.Diag_pid)
						and EPS.EvnPS_setDT between dhctd.hDayHospitalCardTypesDiag_begDT and isnull(dhctd.hDayHospitalCardTypesDiag_endDT, '2099-01-01')
					order by isnull(dhctd.hDayHospitalCardTypesDiag_IsOper,1) asc
				) dhctd
				outer apply (
					select top 1 dhct.publCod
					from r101.hDayHospitalCardTypesUslugaComplexLink dhctucl (nolock)
					inner join r101.hDayHospitalCardTypes dhct (nolock) on dhct.ID = dhctucl.hDayHospitalCardTypes_id
					inner join v_EvnUsluga eu (nolock) on eu.UslugaComplex_id = dhctucl.UslugaComplex_id and eu.EvnUsluga_rid = EPS.EvnPS_id
					inner join v_EvnUslugaOper euo (nolock) on euo.UslugaComplex_id = dhctucl.UslugaComplex_oid and euo.EvnUslugaOper_rid = EPS.EvnPS_id
					where EPS.EvnPS_setDT between dhctucl.hDayHospitalCardTypesUslugaComplexLink_begDT and isnull(dhctucl.hDayHospitalCardTypesUslugaComplexLink_endDT, '2099-01-01')
				) dhctucl
				outer apply (
					select top 1 dhct.publCod
					from r101.hDayHospitalCardTypesUslugaComplex dhctuc (nolock)
					inner join r101.hDayHospitalCardTypes dhct (nolock) on dhct.ID = dhctuc.hDayHospitalCardTypes_id
					inner join v_EvnUsluga eu (nolock) on eu.UslugaComplex_id = dhctuc.UslugaComplex_id and eu.EvnUsluga_rid = EPS.EvnPS_id
					where EPS.EvnPS_setDT between dhctuc.hDayHospitalCardTypesUslugaComplex_begDT and isnull(dhctuc.hDayHospitalCardTypesUslugaComplex_endDT, '2099-01-01')
				) dhctuc
			where
				EPS.EvnPS_id = :EvnPS_id
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
			select top 1
				ex.EvnXml_id,
				eps.Person_id,
				ISNULL(ex.EvnXml_id, eps.EvnPS_id) as EpicrisisNumber,
				XTH.XmlTemplateHtml_HtmlTemplate as EpicrisisData,
				convert(varchar(19), ES.EvnSection_disDT, 126) as IssueDate,
				RTRIM(puc.pmUser_Name) as UserFIO,
				RTRIM(puc.pmUser_Login) as UserLogin,
				RTRIM(puc.pmUser_Email) as UserPost
			from
				v_EvnPS eps (nolock)
				left join v_EvnSection es (nolock) on es.EvnSection_pid = eps.EvnPS_id
				left join v_EvnXml ex (nolock) on ex.Evn_id = es.EvnSection_id and ex.XmlType_id = 10
				left join v_XmlTemplateHtml XTH (nolock) on XTH.XmlTemplateHtml_id = EX.XmlTemplateHtml_id
				left join v_pmUserCache puc (nolock) on puc.pmUser_id = ISNULL(ex.pmUser_insID, eps.pmUser_insID)
			where
				eps.EvnPS_id = :EvnPS_id
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
				select top 1
					cast(EvnXml_Data.query('data/objectivestatus/text()') as varchar(max)) as objectivestatus,
					cast(EvnXml_Data.query('data/outcomeOFdisease/text()') as varchar(max)) as outcomeOFdisease,
					cast(EvnXml_Data.query('data/complaint/text()') as varchar(max)) as complaint,
					cast(EvnXml_Data.query('data/Surveys/text()') as varchar(max)) as Surveys,
					cast(EvnXml_Data.query('data/anamnesvitae/text()') as varchar(max)) as anamnesvitae,
					cast(EvnXml_Data.query('data/recommendations/text()') as varchar(max)) as recommendations,
					cast(EvnXml_Data.query('data/anamnesmorbi/text()') as varchar(max)) as anamnesmorbi,
					cast(EvnXml_Data.query('data/OLDoperations/text()') as varchar(max)) as OLDoperations,
					cast(EvnXml_Data.query('data/treatment/text()') as varchar(max)) as treatment
				from 
					v_EvnXml with (nolock) 
				where
					EvnXml_id = :EvnXml_id
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
			select
				convert(varchar(19), ES.EvnSection_setDT, 126) as CriteriaDBeg,
				convert(varchar(19), ES.EvnSection_disDT, 126) as CriteriaDEnd,
				convert(varchar(19), ps.Person_BirthDay, 126) as DateOfBirth,
				convert(varchar(19), ES.EvnSection_setDT, 126) as DtBeg,
				convert(varchar(19), ES.EvnSection_disDT, 126) as DtEnd,
				convert(varchar(19), EPS.EvnPS_setDT, 126) as EvnPS_setDT,
				eps.EvnPS_NumCard as HospitalHistoryNumber,
				es.EvnSection_id,
				ps.Person_FirName as PatientFirstName,
				ps.Person_Inn as PatientIIN,
				ps.Person_SecName as PatientLastNam,
				ps.Person_SurName as PatientSecondName,
				ps.Sex_id,
				ls.LpuSectionBedProfile_id,
				gph.PersonalID,
				gph.FPID,
				gph.MOID,
				hbp.p_publCod as BedProfile_Code,
				hbp.p_nameru as BedProfile_RuName,
				hbp.p_namekz as BedProfile_KzName
			from
				v_EvnSection es (nolock)
				left join v_EvnPS eps (nolock) on eps.EvnPS_id = es.EvnSection_pid
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuSectionBedProfile lsbp (nolock) on lsbp.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
				left join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.FPID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = ES.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = es.EvnSection_id
				left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
				left join r101.hBedProfile hbp (nolock) on hbp.p_ID = gb.BedProfile
			where
				es.EvnSection_pid = :EvnPS_id
			order by
				es.EvnSection_setDT asc
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
			select top 1
				convert(varchar(19), euoc.EvnUslugaOnkoChem_setDT, 126) as EvnUslugaOnkoChem_setDT,
				convert(varchar(19), euob.EvnUslugaOnkoBeam_setDT, 126) as EvnUslugaOnkoBeam_setDT,
				euob.EvnUslugaOnkoBeam_TotalDoseRegZone,
				euob.EvnUslugaOnkoBeam_TotalDoseTumor,
				d.Diag_Code,
				od.Diag_Code as OnkoDiag_Code,
				euob.OnkoUslugaBeamRadioModifType_id,
				mo.TumorStage_id,
				euoc.OnkoTreatType_id,
				euoc.OnkoUslugaChemKindType_id,
				euog.EvnUslugaOnkoGormun_IsDrug,
				euog.EvnUslugaOnkoGormun_IsBeam,
				euog.EvnUslugaOnkoGormun_IsSurg,
				euog.EvnUslugaOnkoGormun_IsOther,
				convert(varchar(19), mo.MorbusOnko_setDiagDT, 126) as MorbusOnko_setDiagDT,
				convert(varchar(19), es.EvnSection_setDT, 126) as EvnSection_setDT,
				convert(varchar(19), es.EvnSection_disDT, 126) as EvnSection_disDT,
				mo.OnkoM_id,
				mo.OnkoN_id,
				mo.OnkoT_id,
				mo.MorbusOnko_IsTumorDepoLympha,
				mo.MorbusOnko_IsTumorDepoBones,
				mo.MorbusOnko_IsTumorDepoLiver,
				mo.MorbusOnko_IsTumorDepoLungs,
				mo.MorbusOnko_IsTumorDepoBrain,
				mo.MorbusOnko_IsTumorDepoSkin,
				mo.MorbusOnko_IsTumorDepoKidney,
				mo.MorbusOnko_IsTumorDepoOvary,
				mo.MorbusOnko_IsTumorDepoPerito,
				mo.MorbusOnko_IsTumorDepoMarrow,
				mo.MorbusOnko_IsTumorDepoOther,
				mo.MorbusOnko_IsTumorDepoMulti,
				mo.OnkoDiagConfType_id,
				euob.OnkoUslugaBeamMethodType_id,
				euob.OnkoUslugaBeamKindType_id,
				euob.OnkoUslugaBeamIrradiationType_id
			from
				v_EvnSection es (nolock)
				inner join v_MorbusBase mb (nolock) on mb.Evn_pid = es.EvnSection_id
				inner join v_Morbus m (nolock) on m.MorbusBase_id = mb.MorbusBase_id
				inner join v_MorbusOnko mo (nolock) on mo.Morbus_id = m.Morbus_id
				left join v_EvnUslugaOnkoChem euoc (nolock) on euoc.Morbus_id = m.Morbus_id
				left join v_EvnUslugaOnkoBeam euob (nolock) on euob.Morbus_id = m.Morbus_id
				left join v_EvnUslugaOnkoGormun euog (nolock) on euog.Morbus_id = m.Morbus_id
				left join v_Diag d (nolock) on d.Diag_id = m.Diag_id
				left join v_Diag od (nolock) on od.Diag_id = mo.OnkoDiag_mid
			where
				es.EvnSection_pid = :EvnPS_id
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
			select top 1
				mcp.CrazyEducationType_id,
				mcp.CrazySourceLivelihoodType_id,
				mcp.MorbusCrazyPerson_CompleteClassCount,
				convert(varchar(19), PR.PersonRegister_setDate, 126) as PersonRegister_setDate,
				mcpi.InvalidGroupType_id
			from
				v_EvnSection es (nolock)
				inner join v_MorbusBase mb (nolock) on mb.Evn_pid = es.EvnSection_id
				inner join v_Morbus m (nolock) on m.MorbusBase_id = mb.MorbusBase_id
				inner join v_Diag d (nolock) on d.Diag_id = m.Diag_id
				inner join v_MorbusCrazy mc (nolock) on mc.Morbus_id = m.Morbus_id
				outer apply (
					select top 1 * from v_MorbusCrazyPerson MCP with (nolock) where ES.Person_id = MCP.Person_id order by MorbusCrazyPerson_insDT asc
				) mcp
				outer apply (
					select top 1 * from v_MorbusCrazyPersonInvalid MCPI with (nolock) where MCPI.MorbusCrazyPerson_id = mcp.MorbusCrazyPerson_id order by MorbusCrazyPersonInvalid_setDT desc
				) mcpi
				outer apply (
					select top 1
						PR.PersonRegister_setDate
					from
					 	v_PersonRegister PR with (nolock)
					where
						PR.Person_id = MB.Person_id
						and PR.Morbus_id = M.Morbus_id
					order by
						PersonRegister_disDate ASC,
						PersonRegister_setDate DESC
				) PR
			where
				es.EvnSection_pid = :EvnPS_id
				and m.MorbusType_id = 4
				and (
					(d.Diag_Code >= 'F00.0' and d.Diag_Code < 'F10.0')
					or
					(d.Diag_Code >= 'F20.0' and d.Diag_Code <= 'F99.9')
				)
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
			select top 1
				mcp.CrazyEducationType_id,
				mcp.CrazySourceLivelihoodType_id,
				mcp.MorbusCrazyPerson_CompleteClassCount,
				convert(varchar(19), PR.PersonRegister_setDate, 126) as PersonRegister_setDate,
				mcb.MorbusCrazyBase_id
			from
				v_EvnSection es (nolock)
				inner join v_MorbusBase mb (nolock) on mb.Evn_pid = es.EvnSection_id
				inner join v_MorbusCrazyBase mcb (nolock) on mcb.MorbusBase_id = mb.MorbusBase_id
				inner join v_Morbus m (nolock) on m.MorbusBase_id = mb.MorbusBase_id
				inner join v_Diag d (nolock) on d.Diag_id = m.Diag_id
				inner join v_MorbusCrazy mc (nolock) on mc.Morbus_id = m.Morbus_id
				outer apply (
					select top 1 * from v_MorbusCrazyPerson MCP with (nolock) where ES.Person_id = MCP.Person_id order by MorbusCrazyPerson_insDT asc
				) mcp
				outer apply (
					select top 1
						PR.PersonRegister_setDate
					from
					 	v_PersonRegister PR with (nolock)
					where
						PR.Person_id = MB.Person_id
						and PR.Morbus_id = M.Morbus_id
					order by
						PersonRegister_disDate ASC,
						PersonRegister_setDate DESC
				) PR
			where
				es.EvnSection_pid = :EvnPS_id
				and m.MorbusType_id = 4
				and d.Diag_Code >= 'F10.0'
				and d.Diag_Code <= 'F19.9'
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
			select
				mcd.CrazyDrugType_id,
				mcd.CrazyDrugReceptType_id
			from
				v_MorbusCrazyDrug mcd (nolock)
			where
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
			select top 1
				es.EvnSection_id,
				convert(varchar(19), bss.BirthSpecStac_OutcomDT, 126) as BirthSpecStac_OutcomDT,
				bss.BirthSpecStac_CountPregnancy,
				bss.BirthSpecStac_OutcomPeriod,
				bss.BirthSpecStac_CountBirth,
				bss.BirthCharactType_id,
				bss.BirthSpecStac_BloodLoss,
				bss.BirthSpecStac_CountChild,
				bss.BirthPlace_id,
				bss.PregnancyResult_id,
				bss.AbortMethod_id,
				Did.Diag_Code,
				bss.BirthSpecStac_IsHIVtest,
				bss.BirthSpecStac_IsHIV,
				bss.BirthSpecStac_IsRWtest,
				bss.BirthSpecStac_IsRW
			from
				v_EvnPS eps (nolock)
				inner join v_EvnSection es (nolock) on es.EvnSection_pid = eps.EvnPS_id
				inner join v_BirthSpecStac bss (nolock) on bss.EvnSection_id = es.EvnSection_id
				left join v_Diag Did with (nolock) on Did.Diag_id = eps.Diag_id
			where
				eps.EvnPS_id = :EvnPS_id
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
			select
				convert(varchar(19), bss.BirthSpecStac_OutcomDT, 126) as BirthSpecStac_OutcomDT,
				bss.BirthPlace_id,
				pnb.PersonNewborn_Weight as Weight,
				pnb.PersonNewborn_Height as Height,
				pnb.PersonNewborn_Head,
				pnb.PersonNewborn_Breast,
				pnb.ChildTermType_id,
				bss.BirthSpecStac_CountChild,
				pnb.PersonNewborn_CountChild as CountChild,
				ps.Sex_id,
				pnb.ChildTermType_id,
				gm.ID as MOID,
				convert(varchar(19), pnb.PersonNewborn_BCGDate, 126) as PersonNewborn_BCGDate,
				convert(varchar(19), pnb.PersonNewborn_HepatitDate, 126) as PersonNewborn_HepatitDate,
				convert(varchar(19), EPS.EvnPS_setDT, 126) as EvnPS_setDT,
				Did.Diag_Code,
				eps.EvnPS_id,
				null as PntDeathTime_id,
				1 as type
			from
				v_EvnSection es (nolock)
				inner join v_BirthSpecStac bss (nolock) on bss.EvnSection_id = es.EvnSection_id
				inner join v_PersonNewBorn PNB (nolock) on PNB.BirthSpecStac_id = bss.BirthSpecStac_id
				left join v_PersonState ps (nolock) on ps.Person_id = pnb.Person_id
				left join r101.GetMO gm with (nolock) on gm.Lpu_id = es.Lpu_id
				left join v_EvnPS eps (nolock) on eps.EvnPS_id = pnb.EvnPS_id
				left join v_Diag Did with (nolock) on Did.Diag_id = eps.Diag_id
			where
				es.EvnSection_id = :EvnSection_id
				
			union all
			
			select
				convert(varchar(19), bss.BirthSpecStac_OutcomDT, 126) as BirthSpecStac_OutcomDT,
				bss.BirthPlace_id,
				cd.ChildDeath_Weight as Weight,
				cd.ChildDeath_Height as Height,
				30 as PersonNewborn_Head,
				30 as PersonNewborn_Breast,
				cd.ChildTermType_id,
				bss.BirthSpecStac_CountChild,
				cd.ChildDeath_Count as CountChild,
				cd.Sex_id,
				cd.ChildTermType_id,
				gm.ID as MOID,
				null as PersonNewborn_BCGDate,
				null as PersonNewborn_HepatitDate,
				null as EvnPS_setDT,
				Did.Diag_Code,
				null as EvnPS_id,
				cd.PntDeathTime_id,
				2 as type
			from
				v_EvnSection es (nolock)
				inner join v_BirthSpecStac bss (nolock) on bss.EvnSection_id = es.EvnSection_id
				inner join v_ChildDeath CD (nolock) on CD.BirthSpecStac_id = bss.BirthSpecStac_id
				left join r101.GetMO gm with (nolock) on gm.Lpu_id = es.Lpu_id
				left join v_Diag Did with (nolock) on Did.Diag_id = cd.Diag_id
			where
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
			select
				uc.UslugaComplex_Code,
				euo.EvnUslugaOper_IsEndoskop,
				euo.EvnUslugaOper_IsLazer,
				euo.EvnUslugaOper_IsKriogen,
				euo.EvnUslugaOper_IsRadGraf,
				ISNULL(euo.AnesthesiaClass_id, euoa.AnesthesiaClass_id) as AnesthesiaClass_id,
				ea.AggType_id,
				euo.OperType_id,
				convert(varchar(19), euo.EvnUslugaOper_setDT, 126) as EvnUslugaOper_setDT,
				datediff(day, es.EvnSection_setDT, euo.EvnUslugaOper_setDT) as BeforeDaysCount,
				datediff(day, es.EvnSection_setDT, es.EvnSection_disDT) as FuncDaysCount,
				gph.PostID
			from
				v_EvnSection es (nolock)
				inner join v_EvnUslugaOper euo (nolock) on euo.EvnUslugaOper_pid = es.EvnSection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euo.UslugaComplex_id
				outer apply (
					select top 1
						euoa.AnesthesiaClass_id
					from
						v_EvnUslugaOperAnest euoa (nolock)
					where
						euoa.EvnUslugaOper_id = euo.EvnUslugaOper_id
				) euoa
				outer apply (
					select top 1
						ea.AggType_id
					from
						v_EvnAgg ea (nolock)
					where
						ea.EvnAgg_pid = euo.EvnUslugaOper_id
				) ea
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.PostID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = euo.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
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
			select
				uc.UslugaComplex_Code,
				euc.EvnUslugaCommon_KolVo
			from
				v_EvnSection es (nolock)
				inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = es.EvnSection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id
			where
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
			select 
				Did.Diag_Code,
				Did.Diag_Name,
				isnull(Dpid.Diag_Code, Did.Diag_Code) as Diag_pCode,
				isnull(Dpid.Diag_Name, Did.Diag_Name) as Diag_pName,
				convert(varchar(19), EPS.EvnPS_setDT, 126) as EvnPS_setDT
			from v_EvnPS eps with (nolock) 
				left join v_Diag Did with (nolock) on Did.Diag_id = eps.Diag_id
				left join v_Diag Dpid with (nolock) on Dpid.Diag_id = eps.Diag_pid
			where eps.EvnPS_id = :EvnPS_id
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
			select 
				Diag.Diag_Code,
				Diag.Diag_Name,
				DT.p_publCod as diagTypeIdCode,
				DT.p_nameRU as diagTypeName,
				convert(varchar(19), EDPS.EvnDiagPS_setDT, 126) as EvnDiagPS_setDT
			from v_EvnDiagPS EDPS with (nolock)
				left join Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
				left join r101.hDiagTypeLink DTL with (nolock) on DTL.DiagSetClass_id = EDPS.DiagSetClass_id
				left join r101.hDiagType DT with (nolock) on DT.p_ID = DTL.p_ID
			where EDPS.EvnDiagPS_pid = :EvnPS_id
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
			select
				convert(varchar(19), esnb.EvnSectionNarrowBed_setDT, 126) as EvnSectionNarrowBed_setDT,
				convert(varchar(19), esnb.EvnSectionNarrowBed_disDT, 126) as EvnSectionNarrowBed_disDT
			from
				v_EvnSectionNarrowBed esnb with (nolock)
				inner join v_EvnSection es with (nolock) on es.EvnSection_id = esnb.EvnSectionNarrowBed_pid
			where
				es.EvnSection_pid = :EvnPS_id
				and esnb.EvnSectionNarrowBed_setDT is not null
				and esnb.EvnSectionNarrowBed_disDT is not null
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
			select top 1
				gb.GetBuilding_id
			from
				r101.GetBuilding gb with (nolock)
				inner join r101.GetAddressTerr gat with (nolock) on gat.GetAddressTerr_id = gb.GetAddressTerr_id
			where
				gat.GetAddressTerr_Name = :GetAddressTerr_Name
				and gb.GetBuilding_Number = :GetBuilding_Number
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
			$filter = " and cast(eps.EvnPS_disDT as date) between :EvnPS_disDT1 and :EvnPS_disDT2";

			if (!empty($this->_esrbConfig['Lpu_ids'])) {
				$filter .= " and eps.Lpu_id IN ('".implode("','", $this->_esrbConfig['Lpu_ids'])."')";
			}

			if (!empty($data['EvnPS_id'])) {
				$queryParams = array(
					'EvnPS_id' => $data['EvnPS_id']
				);
				$filter = " and eps.EvnPS_id = :EvnPS_id";
			}

			$query = "
				select
					eps.EvnPS_id,
					eps.EvnPS_NumCard
				from
					v_EvnPS eps (nolock)
					inner join v_PayType pt (nolock) on pt.PayType_id = eps.PayType_id -- только с видами оплаты региона
					inner join v_Lpu l (nolock) on l.Lpu_id = eps.Lpu_id -- только с МО региона
					-- inner join v_Person P (nolock) on p.Person_id = EPS.Person_id and p.BDZ_id is not null -- пусть валится в ошибки (Это руководство пользователю - что нужно провести идентификацию пациента в РПН.)
					cross apply (
						select top 1
							es.EvnSection_id
						from
							v_EvnSection ES with (nolock)
						where
							ES.EvnSection_pid = EPS.EvnPS_id
							and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					) ESLAST -- есть движения
				where
					1=1
					and pt.PayType_SysNick in ('bud', 'Resp')
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
				gpw.PersonalID,
				gpw.FPID,
			";
			$filter .= " and gphwp.WorkPlace_id = :WorkPlace_id";
			$queryParams['WorkPlace_id'] = $data['WorkPlace_id'];
		}

		return $this->queryResult("
			select
				{$fields}
				gpw.MOID,
				gpw.SpecialityID
			from
				r101.v_GetPersonalHistoryWP gphwp (nolock)
				inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
				inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = gphwp.WorkPlace_id 
			where
				{$filter}
			order by
				gphwp.GetPersonalHistoryWP_insDT desc
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
					ps.Person_SurNameR
					,p.BDZ_id
					,es.EvnSection_id
					,utES.UslugaTest_id as ESUslugaTest_id
					,utES.UslugaTest_CheckDT as ESUslugaTest_CheckDT
					,utES.UslugaTest_ResultValue as ESUslugaTest_ResultValue
					,utES.UslugaTest_ResultApproved as ESUslugaTest_ResultApproved
					,utEPS.UslugaTest_id as EPSUslugaTest_id
					,utEPS.UslugaTest_CheckDT as EPSUslugaTest_CheckDT
					,utEPS.UslugaTest_ResultValue as EPSUslugaTest_ResultValue
					,utEPS.UslugaTest_ResultApproved as EPSUslugaTest_ResultApproved
					,erp.EvnReanimatPeriod_id
					,EvnReanimatPeriod_setDT
					,EvnReanimatPeriod_disDT
					,era.EvnReanimatAction_id
					,EvnReanimatAction_setDT
					,EvnReanimatAction_disDT
					,epsl.Hospitalization_id
				from v_EvnPS eps with (nolock)
					inner join r101.v_EvnPSLink epsl (nolock) on epsl.EvnPS_id = eps.EvnPS_id --случай есть в БГ
					inner join v_PersonState ps with (nolock) on eps.Person_id = ps.Person_id
					inner join v_Person p with (nolock) on eps.Person_id = p.Person_id
					inner join v_EvnSection es with (nolock) on es.EvnSection_pid = eps.EvnPS_id
					left join r101.EvnLinkAPP edla with (nolock) on edla.Evn_id = es.EvnSection_id
					left join v_Diag dcid with (nolock) on dcid.Diag_id = edla.Diag_cid
					inner join v_Diag did with (nolock) on did.Diag_id = es.Diag_id
					
					left join v_EvnUslugaPar eupES with (nolock) on eupES.EvnUslugaPar_pid = es.EvnSection_id
					left join v_EvnUslugaPar eupEPS with (nolock) on eupEPS.EvnUslugaPar_pid = eps.EvnPS_id
					left join v_UslugaTest utES with (nolock) on utES.UslugaTest_pid = eupES.EvnUslugaPar_id
					left join v_UslugaTest utEPS with (nolock) on utEPS.UslugaTest_pid = eupEPS.EvnUslugaPar_id
					left join UslugaComplex ucES with (nolock) on eupES.UslugaComplex_id = ucES.UslugaComplex_id
					left join UslugaComplex ucEPS with (nolock) on eupEPS.UslugaComplex_id = ucEPS.UslugaComplex_id
					
					left join v_EvnReanimatPeriod erp with (nolock) on erp.EvnReanimatPeriod_pid = es.EvnSection_id
					left join v_EvnReanimatAction era with (nolock) on era.EvnReanimatAction_pid = erp.EvnReanimatPeriod_id
				where
					p.BDZ_id is not null
					and 
					( 
						(convert(date,utES.UslugaTest_CheckDT) = :start and ucES.UslugaComplex_Code like '%B09.863.020%' and utES.UslugaTest_ResultApproved = 2) or
						(convert(date,utEPS.UslugaTest_CheckDT) = :start and ucEPS.UslugaComplex_Code like '%B09.863.020%' and utEPS.UslugaTest_ResultApproved = 2) or 
						(convert(date,erp.EvnReanimatPeriod_disDT) = :start or convert(date,erp.EvnReanimatPeriod_setDT) = :start) or 
						(convert(date,era.EvnReanimatAction_disDT) = :start or convert(date,era.EvnReanimatAction_setDT) = :start)
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
					)
					{$filterForEnvPsID}
			)
			select 
				EvnPSList.Person_SurNameR,EvnPSList.BDZ_id,EvnPSList.Hospitalization_id
				,EvnPSList.ESUslugaTest_id as id
				,'100' as PatientsAdmissionMoveTypePublicCode
				,convert(varchar(19),EvnPSList.ESUslugaTest_CheckDT,127) as RegDate
				,case when EvnPSList.ESUslugaTest_ResultValue like '%пол%' then 1 else 0 end as Value 
			from 
				EvnPSList
			where
				convert(date,EvnPSList.ESUslugaTest_CheckDT) = :start--Дата и время одобрения результата теста
			union all
			select 
				EvnPSList.Person_SurNameR,EvnPSList.BDZ_id,EvnPSList.Hospitalization_id
				,EvnPSList.EPSUslugaTest_id as id
				,'100' as PatientsAdmissionMoveTypePublicCode
				,convert(varchar(19),EvnPSList.EPSUslugaTest_CheckDT,127) as RegDate
				,case when EvnPSList.EPSUslugaTest_ResultValue like '%пол%' then 1 else 0 end as Value 
			from 
				EvnPSList
			where
				convert(date,EvnPSList.EPSUslugaTest_CheckDT) = :start--Дата и время одобрения результата теста
			union all
			select 
				EvnPSList.Person_SurNameR,EvnPSList.BDZ_id,EvnPSList.Hospitalization_id
				,EvnPSList.EvnReanimatPeriod_id as id
				,'200' as PatientsAdmissionMoveTypePublicCode,
				convert(varchar(19),EvnPSList.EvnReanimatPeriod_setDT,127) as RegDate,
				1 as Value
			from 
				EvnPSList
			where 
				convert(date,EvnPSList.EvnReanimatPeriod_setDT) = :start
			union all
			select 
				EvnPSList.Person_SurNameR,EvnPSList.BDZ_id,EvnPSList.Hospitalization_id
				,EvnPSList.EvnReanimatPeriod_id as id
				,'200' as PatientsAdmissionMoveTypePublicCode
				,convert(varchar(19),EvnPSList.EvnReanimatPeriod_disDT,127) as RegDate
				,0 as Value
			from 
				EvnPSList
			where 
				convert(date,EvnPSList.EvnReanimatPeriod_disDT) = :start
			union all
			select
				EvnPSList.Person_SurNameR,EvnPSList.BDZ_id,EvnPSList.Hospitalization_id
				,EvnPSList.EvnReanimatAction_id as id
				,'300' as PatientsAdmissionMoveTypePublicCode
				,convert(varchar(19),EvnPSList.EvnReanimatAction_setDT,127) as RegDate
				,1 as Value
			from 
				EvnPSList	
			where	
				convert(date,EvnPSList.EvnReanimatAction_setDT) = :start
			union all
			select
				EvnPSList.Person_SurNameR,EvnPSList.BDZ_id,EvnPSList.Hospitalization_id
				,EvnPSList.EvnReanimatAction_id as id
				,'300' as PatientsAdmissionMoveTypePublicCode
				,convert(varchar(19),EvnPSList.EvnReanimatAction_disDT,127) as RegDate
				,0 as Value
			from 
				EvnPSList
			where
				convert(date,EvnPSList.EvnReanimatAction_disDT) = :start
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