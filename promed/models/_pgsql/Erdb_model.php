<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Erdb_model - модель
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

class Erdb_model extends swPgModel {
	protected $_erdbConfig = array();
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

		$this->load->library('textlog', array('file'=>'Erdb_'.date('Y-m-d').'.log'));

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('RSBKZ');

		$this->_erdbConfig = $this->config->item('Erdb');
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
			$url = $this->_erdbConfig['url'].'?wsdl';

			if (!empty($_REQUEST['getDebug'])) {
				var_dump($url);
				echo '<br>';
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

			if (!empty($this->_erdbConfig['proxy_host'])) {
				$soapOptions['proxy_host'] = $this->_erdbConfig['proxy_host'];
			}
			if (!empty($this->_erdbConfig['proxy_port'])) {
				$soapOptions['proxy_port'] = $this->_erdbConfig['proxy_port'];
			}
			if (!empty($this->_erdbConfig['proxy_login'])) {
				$soapOptions['proxy_login'] = $this->_erdbConfig['proxy_login'];
			}
			if (!empty($this->_erdbConfig['proxy_password'])) {
				$soapOptions['proxy_password'] = $this->_erdbConfig['proxy_password'];
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
					ERSBRefbook_id as \"ERSBRefbook_id\",
					ERSBRefbook_Code as \"ERSBRefbook_Code\",
					ERSBRefbook_Name as \"ERSBRefbook_Name\",
					ERSBRefbook_MapName as \"ERSBRefbook_MapName\",
					Refbook_TableName as \"Refbook_TableName\"
				from
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
				select
					{$idField} as id,
					their.{$field} as code
				from
					{$mapTable} link 
					inner join {$theirTable} their on their.P_ID = link.P_ID
				where
					link.{$advancedKey} = :{$advancedKey}
				limit 1
			";

			if(in_array($table, array('hTreatmentType'))) {
				$query = "
					select
						{$idField} as id,
						their.{$table}_SysNick as code
					from
						{$mapTable} link 
						left join {$theirTable} their on their.{$table}_id = link.{$table}_id
					where
						link.{$advancedKey} = :{$advancedKey}
					limit 1
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
		$response = $this->exec('Erdb', 'GetAccess', array(
			'username' => $this->_erdbConfig['username'],
			'password' => $this->_erdbConfig['password']
		));

		if (!empty($response->GetAccessResult->ErrorMessage)) {
			throw new Exception('Ошибка при выполнении Login: ' . $response->GetAccessResult->ErrorMessage, 400);
		} else {
			$this->_ticket = $response->GetAccessResult->Ticket;
		}
	}

	/**
	 * Получение данных карты
	 */
	function getPersonDispInfo($data) {
		$params = array('PersonDisp_id' => $data['PersonDisp_id']);

		$query = "
			select 
				pd.PersonDisp_id as \"PersonDisp_id\",
				pd.Person_id as \"Person_id\",
				p.Person_GUID as \"Person_GUID\",
				p.BDZ_id as \"BDZ_id\",
				p.BDZ_Guid as \"BDZ_Guid\",
				ps.Person_Inn as \"Person_Inn\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				to_char(ps.Person_BirthDay, 'yyyy-mm-ddThh24:MI:SS') as \"Person_BirthDay\",
				to_char(ps.Person_deadDT, 'yyyy-mm-ddThh24:MI:SS') as \"Person_deadDT\",
				ps.Sex_id as \"Sex_id\",
				E.Ethnos_Code as \"Ethnos_Code\",
				case when ps.KLCountry_id = 398 then 1 else 0 end as \"Resident_RK\",
				hSocType.id as \"Social_ID\",
				PBG.BloodGroupType_id as \"BloodGroupType_id\",
				case 
					when PBG.RhFactorType_id = 1 then 1
					when PBG.RhFactorType_id = 2 then 0
					else null
				end as Rh_blood_ID,
				case 
					when ps.Person_deadDT is null then null
					when ds.DeathCause_id = 1 then 9
					when ds.DeathCause_id = 2 then 10
					when ds.DeathCause_id = 3 then 11
					when ds.DeathCause_id = 5 then 7
					else 8
				end as \"Prich_death_ID\",
				pud.UIDGuid as \"UIDGuid\",
				-- 
				to_char(pd.PersonDisp_begDate, 'yyyy-mm-ddThh24:MI:SS') as \"Dt_beg\",
				to_char(pd.PersonDisp_endDate, 'yyyy-mm-ddThh24:MI:SS') as \"Dt_end\",
				d.Diag_Code as \"Icd10\",
				pd.PersonDisp_NumCard as \"Nomkart\",
				case 
					when pd.PersonDisp_endDate is null then null
					else coalesce(dotl.p_ID, 0)
				end as \"Prich_End_ID\",
				gph.MedCode as \"MedCode\",
				l.Org_OKPO as \"Org_OKPO\",
				pd.PersonDisp_deleted as \"RemoveData\",
				case when ps.PAddress_id is not null then 1 else 0 end as \"Citizen\",
				-- 
				to_char(pd.PersonDisp_DiagDate, 'yyyy-mm-ddThh24:MI:SS') as \"Dt_beg_diag\",
				coalesce(pd.DiagDetectType_id, 4) as \"Tip_obnar_id\",
				j.Org_id as \"M_work\",
				j.Post_id as \"W_dol\",
				gph.ID_Sur as \"ID_Sur\",
				gph.ID_Post as \"ID_Post\",
				gph.ID_RPN as \"ID_RPN\",
				gph.INN as \"INN\",
				gph.LastName as \"LastName\",
				gph.FirstName as \"FirstName\",
				gph.SecondName as \"SecondName\"
			from PersonDisp pd
				inner join v_PersonState ps on ps.Person_id = pd.Person_id
				inner join v_Person p on p.Person_id = pd.Person_id
				left join r101.PersonDispUIDLink pud on pud.PersonDisp_id = pd.PersonDisp_id
				left join v_PersonInfo pi on pi.Person_id = PS.Person_id
				left join v_Ethnos E on E.Ethnos_id = pi.Ethnos_id				
				left join r101.hSocTypeSocStatus on hSocTypeSocStatus.SocStatus_id = ps.SocStatus_id
				left join r101.hSocType on hSocType.id = hSocTypeSocStatus.id
				left join v_PersonBloodGroup pbg on pbg.Person_id = pd.Person_id
				left join lateral(
					select
						ds.DeathCause_id
					from v_DeathSvid ds
					where ds.Person_id = pd.Person_id and ds.DeathSvid_IsActual = 2
					limit 1
				) ds on true
				left join v_Diag d on d.Diag_id = pd.Diag_id
				left join r101.DispOutTypeLink dotl on dotl.DispOutType_id = pd.DispOutType_id
				left join v_Job j on ps.Job_id = j.Job_id
				left join v_MedStaffFact msf on msf.MedPersonal_id = pd.MedPersonal_id and msf.LpuSection_id = pd.LpuSection_id
				left join v_Lpu_all l on l.Lpu_id = pd.Lpu_id
				left join lateral(
					select
						gp.LastName,
						gp.FirstName,
						gp.SecondName,
						gp.PersonalID as ID_Sur,
						gp.PostID as ID_Post,
						gph.RpnId as ID_RPN,
						gp.IIN as INN,
						gp.MOID,
						gmo.medcode
					from
						r101.v_GetPersonalHistoryWP gphwp
						inner join r101.GetPersonalHistory gph on gphwp.GetPersonalHistory_id = gph.GetPersonalHistory_id
						inner join r101.GetPersonal gp on gp.PersonalID = gph.PersonalID
						inner join r101.v_GetMO gmo (nolock) on gmo.id = gp.moid
					where
						gphwp.WorkPlace_id = msf.MedStaffFact_id
						and gmo.lpu_id = pd.lpu_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
					limit 1
				) gph on true
				--left join r101.v_GetMO MO on MO.ID = gph.MOID
			where 
				pd.PersonDisp_id = :PersonDisp_id
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные карты', 400);
		}
	}

	/**
	 * Получение прикрепления
	 */
	function getAttachment($Person_id) {

		$query = "
			select
				null as \"UID\",
				Org_ID as \"Prik_kod\",
				to_char(GetAttachment_begDate, 'yyyy-mm-ddThh24:MI:SS') as \"Dt_beg\",
				to_char(GetAttachment_endDate, 'yyyy-mm-ddThh24:MI:SS') as \"Dt_end\",
				GetAttachmentCase_id as \"Prich_End_ID\",
				case when GetAttachmentStatus_id = 9 then 1 else 0 end as \"RemoveData\"
			from r101.GetAttachment 
			where Person_id = :Person_id
			order by 
				case when GetAttachment_endDate is null then 0 else 1 end, 
				GetAttachment_begDate desc
		";

		return $this->queryResult($query, ['Person_id' => $Person_id]);
	}

	/**
	 * Получение адреса
	 */
	function getAddress($Person_id) {

		$query = "
			select
				null as \"UID\",
				case when adr.KLTown_id is not null then 400 else 300 end as \"Adr_ID\",
				adr.Address_Address as \"Adr\",
				null as \"Home\",
				null as \"Apart\",
				case when ps.PAddress_id is not null then 2 else 1 end as \"AddresstypeID\",
				adr.Address_Zip as \"Post\",
				case when adr.KLCity_id is not null then 1 else 0 end as \"IsCity\",
				ps.Person_Phone as \"Phone\",
				to_char(adr.Address_insDT, 'yyyy-mm-ddThh24:MI:SS') as \"Dt\",
				null as \"RemoveData\"
			from v_PersonState ps
				inner join v_Address adr on adr.Address_id = coalesce(ps.PAddress_id, ps.UAddress_id)
			where 
				ps.Person_id = :Person_id
		";

		return $this->queryResult($query, ['Person_id' => $Person_id]);
	}

	/**
	 * Получение списка посещений
	 */
	function getVisitSet($PersonDisp_id) {

		$query = "
			select
				null as \"UID\",
				to_char(pdv.PersonDispVizit_NextDate, 'yyyy-mm-ddThh24:MI:SS') as \"Dt_nazn\",
				to_char(pdv.PersonDispVizit_NextFactDate, 'yyyy-mm-ddThh24:MI:SS') as \"Dt_visit\"
			from v_PersonDispVizit pdv
			where 
				pdv.PersonDisp_id = :PersonDisp_id
		";

		return $this->queryResult($query, ['PersonDisp_id' => $PersonDisp_id]);
	}

	/**
	 * Отправка карты
	 */
	function setHuman($PersonDisp_id, $data) {
		$this->textlog->add("setHuman: ".$PersonDisp_id);

		$PersonDispInfo = $this->getPersonDispInfo(array(
			'PersonDisp_id' => $PersonDisp_id
		));
		
		$visits = $this->getVisitSet($PersonDisp_id);

		$Spis_vraSet = [];

		$Spis_vraSet['Name'] =  ((empty($PersonDispInfo['LastName']))?null:($PersonDispInfo['LastName'].' ')).
			((empty($PersonDispInfo['FirstName']))?null:($PersonDispInfo['FirstName'].' ')).
			((empty($PersonDispInfo['SecondName']))?null:($PersonDispInfo['SecondName']));

		$Spis_vraSet['ID_Sur'] = $PersonDispInfo['ID_Sur'];
		$Spis_vraSet['ID_Post'] = $PersonDispInfo['ID_Post'];
		$Spis_vraSet['ID_Kod'] = $PersonDispInfo['MedCode'];
		$Spis_vraSet['INN'] = $PersonDispInfo['INN'];
		$Spis_vraSet['ID_RPN'] = $PersonDispInfo['ID_RPN'];

		$params = [
			'RequestHumanSet' => [
				'Ticket' => $this->_ticket,
				'Human' => [
					'HumanSet' => [
						'UID' => null,
						'SexID' => ($this->getSyncSpr('hBIOSex', $PersonDispInfo['Sex_id'])=='300')?'3':'2',
						'Birthdate' => $PersonDispInfo['Person_BirthDay'],
						'Rpn_ID' => $PersonDispInfo['BDZ_id'],
						'Nationality_ID' => null, // $PersonDispInfo['Ethnos_Code'],
						'Resident_RK' => $PersonDispInfo['Resident_RK'],
						'Social_ID' => null, // $PersonDispInfo['Social_ID'],
						'Blood_ID' => null, // $this->getSyncSpr('hBloodGroups', $PersonDispInfo['BloodGroupType_id'], true, 'p_ID'),
						'Dt_death' => $PersonDispInfo['Person_deadDT'],
						'Prich_death_ID' => $PersonDispInfo['Prich_death_ID'],
						'Lastname' => $PersonDispInfo['Person_SurName'],
						'Firstname' => $PersonDispInfo['Person_FirName'],
						'Secondname' => $PersonDispInfo['Person_SecName'],
						'Rh_blood_ID' => $PersonDispInfo['Rh_blood_ID'],
						'IIN' => $PersonDispInfo['Person_Inn'],
						//'Human_Prik' => $this->getAttachment($PersonDispInfo['Person_id']),
						'Human_Diag' => [[
							'UID' => $PersonDispInfo['UIDGuid'],
							'Dt_beg' => $PersonDispInfo['Dt_beg'],
							'Dt_end' => $PersonDispInfo['Dt_end'],
							'Icd10' => $PersonDispInfo['Icd10'],
							'Disp' => 1,
							'Vra_ID' => $Spis_vraSet,
							'Kodorg' => $PersonDispInfo['MedCode'],
							'Nomkart' => $PersonDispInfo['Nomkart'],
							'Prich_End_ID' => $PersonDispInfo['Prich_End_ID'],
							'Dgroup_kod' => 1,
							'Vra_add' => $Spis_vraSet,
							'RemoveData' => $PersonDispInfo['RemoveData'],
							'Citizen' =>  $PersonDispInfo['Citizen'],
							'Human_Disp' => [
								'Dt_beg_diag' => $PersonDispInfo['Dt_beg_diag'],
								'Tip_obnar_id' => $PersonDispInfo['Tip_obnar_id'],
								'M_work' => $PersonDispInfo['M_work'],
								'W_dol' => $PersonDispInfo['W_dol'],
								'Ps_visit' => $visits,
								'RemoveData' => $PersonDispInfo['RemoveData']
							]
						]],
						//'Human_Address' => $this->getAddress($PersonDispInfo['Person_id']),
						//'Human_Address' => null
					]
				]
			]
		];
		
		$HumanSet = $params['RequestHumanSet']['Human']['HumanSet'];
		
		$params['RequestHumanSet']['SetRequiredInformation'] = [
			'HumanSet' => true,
			'Human_PrikSet' => false, // count($HumanSet['Human_Prik']) ? true : false,
			'Human_DiagSet' => true,
			'Human_AddressSet' => false, // count($HumanSet['Human_Address']) ? true : false,
			'Spis_vraSet' => !empty($HumanSet['Human_Diag'][0]['Vra_add']) ? true : false,
			'Human_DispSet' => true
		];
		
		$response = $this->exec('Erdb', 'SetHuman', $params);
		
		$ResponseHumanSet = $response->SetHumanResult->ResponseHumanSet;
		
		if ($ResponseHumanSet->Success == false && !isset($ResponseHumanSet->ResponseHuman_DiagSet)) {
			throw new Exception('Ошибка при выполнении SetHuman: ' . $ResponseHumanSet->Message, 400);
		}
		
		$ResponseHuman_DiagSet = $ResponseHumanSet->ResponseHuman_DiagSet->ResponseHuman_DiagSet;

		if ($ResponseHuman_DiagSet->Success == false) {
			throw new Exception('Ошибка при выполнении SetHuman: ' . $ResponseHuman_DiagSet->Message, 400);
		}
		
		$PersonDispUIDLink = $this->getFirstResultFromQuery("select UIDGuid as \"UIDGuid\" from r101.PersonDispUIDLink where PersonDisp_id = ?", [$PersonDisp_id]);
		
		if (!$PersonDispUIDLink) {
			$uid = $ResponseHuman_DiagSet->UID;

			$this->queryResult("
				select
					PersonDispUIDLink_id as \"PersonDispUIDLink_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from r101.p_PersonDispUIDLink_ins(
					PersonDisp_id := :PersonDisp_id,
					UIDGuid := :UIDGuid,
					pmUser_id := :pmUser_id
				)
			", [
				'PersonDisp_id' => $PersonDisp_id,
				'UIDGuid' => $uid,
				'pmUser_id' => $data['pmUser_id']
			]);
		}
		
		$this->saveSyncObject('PersonDisp', $PersonDisp_id, null); // идентификатор никакой не возвращается.
	}

	/**
	 * Отправка всех карт за прошедшие сутки
	 */
	function syncAll($data) {
		$this->load->model('ServiceList_model');
		$ServiceList_id = $this->ServiceList_model->getServiceListId('ServiceERDB');
		$begDT = date('Y-m-d H:i:s');
		$has_errors = false;
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
			
			$filter  = '';
			$queryParams  = [];

			if (!empty($data['id'])) {				
				$queryParams = ['PersonDisp_id' => $data['id']];
				$filter = " pd.PersonDisp_id = :PersonDisp_id";
				
			} elseif(!empty($data['start'])) {				
				$queryParams = [
					'start' => $data['start'],
					'end' => $data['end']
				];
				
				if (!empty($data['end'])) {
					$filter = "
						cast(pd.PersonDisp_insDT as date) between cast(:start as date) and cast(:end as date) or 
						cast(pd.PersonDisp_updDT as date) between cast(:start as date) and cast(:end as date) or 
						cast(pd.PersonDisp_delDT as date) between cast(:start as date) and cast(:end as date)
					";
				} else {
					$filter = "
						cast(pd.PersonDisp_insDT as date) = cast(:end as date) or 
						cast(pd.PersonDisp_updDT as date) = cast(:end as date) or 
						cast(pd.PersonDisp_delDT as date) = cast(:end as date)
					";
				}
				
			} else {				
				$filter = "
					cast(pd.PersonDisp_insDT as date) = (select yday from mv) or 
					cast(pd.PersonDisp_updDT as date) = (select yday from mv) or 
					cast(pd.PersonDisp_delDT as date) = (select yday from mv)
				";
			}

			$query = "
					with mv as (
						select
							dbo.tzgetdate() - interval '1 day' as yday
					)

					select
						PersonDisp_id as \"PersonDisp_id\",
						PersonDisp_NumCard as \"PersonDisp_NumCard\"
					from PersonDisp pd
					where pd.Diag_id not in (
							select D.Diag_id from Diag D
							where
								(SUBSTRING(D.Diag_Code,1,3) >= 'F00' and SUBSTRING(D.Diag_Code,1,3) <= 'F99')
								or (SUBSTRING(D.Diag_Code,1,3) >= 'C00' and SUBSTRING(D.Diag_Code,1,3) <= 'C99')
								or (SUBSTRING(D.Diag_Code,1,3) >= 'E10' and SUBSTRING(D.Diag_Code,1,3) <= 'E14')
								or (SUBSTRING(D.Diag_Code,1,3) >= 'N00' and SUBSTRING(D.Diag_Code,1,3) <= 'N08')
								or (SUBSTRING(D.Diag_Code,1,3) >= 'N17' and SUBSTRING(D.Diag_Code,1,3) <= 'N19')
								or (SUBSTRING(D.Diag_Code,1,3) >= 'A15' and SUBSTRING(D.Diag_Code,1,3) <= 'A19')
								or D.Diag_Code ilike '%E16.9%' or D.Diag_Code ilike '%K73.0%' or D.Diag_Code ilike '%O24.4%'
								or D.Diag_Code ilike '%O24.9%' or D.Diag_Code ilike '%Z95.0%' or D.Diag_Code ilike '%Z20.1%'
								or D.Diag_Code ilike '%Z20.10%' or D.Diag_Code ilike '%Z20.11%' or D.Diag_Code ilike '%Y58.0%'
								or D.Diag_Code like '%Z20.8%' or D.Diag_Code like '%Z20.9%'
						) and
						{$filter}
			";
			$resp = $this->queryResult($query, $queryParams);
			foreach ($resp as $respone) {
				try {
					if (!empty($_REQUEST['getDebug'])) {
						echo "<b>Отправка талона №{$respone['PersonDisp_NumCard']}</b><br>";
					}
					$this->setHuman($respone['PersonDisp_id'], $data);
				} catch (Exception $e) {
					if (!empty($_REQUEST['getDebug'])) {
						var_dump($e->getMessage());
					}
					// падать не будем, просто пишем в лог инфу и идем дальше
					$has_errors = true;
					$this->textlog->add("syncAll error: code: " . $e->getCode() . " message: " . $e->getMessage());
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => $e->getMessage() . " (PersonDisp_id={$respone['PersonDisp_id']})",
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
				'ServiceListResult_id' => $has_errors ? 3 : 1,
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

	function getHuman($data) {
		try {

			$this->login();

			$ourMOCode = $this->getFirstResultFromQuery("select MedCode as \"MedCode\" from r101.v_GetMO where Lpu_id = :Lpu_id limit 1", [ 'Lpu_id' => $data['Lpu_id'] ] );

			$params = [
				'RequestHuman' => [
					'Ticket' => $this->_ticket,
					'Iin' => $data['Person_Inn'],
					'GetRequiredInformation' => [
						'Human_Prik' => 1,
						'Human_Diag' => 1,
						'Ber_karta' => 1,
						'Human_Address' => 1,
						'Tb_Patient' => 1,
						'Human_Disp' => 1,
						'Tb_Napr' => 1,
						'Human_Flur' => 1
					]
				]
			];

			$response = $this->exec('Erdb', 'GetHuman', $params);
			$all = $response;

			if (empty($response->GetHumanResult->Human->Human)) {
				return [
					'all' => $all,
					'success' => $all->GetHumanResult->Success,
					'message' => $all->GetHumanResult->Message,
					'count' => 0
				];
			}

			$result = [];
			$response = [];

			if (is_object($all->GetHumanResult->Human->Human->Human_Diag->Human_Diag)) {
				$response[0] = $all->GetHumanResult->Human->Human->Human_Diag->Human_Diag;
			} else {
				$response = $all->GetHumanResult->Human->Human->Human_Diag->Human_Diag;
			}

			foreach ($response as $key=>$res) {
				//Не наша карта. Выгоняем с пляжа.
				if ($res->Kodorg->Kod != $ourMOCode) continue;

				$tmp = $res;

				$tmp->Diag_id = null;
				$tmp->Vra_UID_MedStaffFact_id = null;
				$tmp->Vra_UID_LpuSection_id = null;
				$tmp->PersonDispHist_MedPersonalFio = null;

				$tmp->action = 'add';

				if (!empty($res->Icd10) && !empty($res->Icd10->ID)){
					$tmp->Diag_id = $this->getFirstResultFromQuery(
						"select Diag_id as \"Diag_id\" from Diag where Diag_Code = :Diag_Code limit 1",
						[ 'Diag_Code' => $res->Icd10->ID ], true);
				}

				$sql = "
					select PS.PersonDisp_id from r101.PersonDispUIDLink PDUIDL
					inner join PersonDisp PS on PDUIDL.PersonDisp_id = PS.PersonDisp_id
					where PS.Diag_id = :Diag_id and PS.PersonDisp_begDate = :PersonDisp_begDate
					and coalesce(PS.PersonDisp_endDate,1) = coalesce(:PersonDisp_endDate,1)
					and PDUIDL.UIDGuid = :UIDGuid
					limit 100
				";

				$isAlready = $this->getFirstResultFromQuery($sql,[
					'Diag_id'=>$tmp->Diag_id,
					'PersonDisp_begDate'=>$tmp->Dt_beg,
					'PersonDisp_endDate'=>$tmp->Dt_end,
					'UIDGuid'=>$all->GetHumanResult->Human->Human->UID
				], true);

				$tmp->PersonDisp_id = null;

				if (!empty($isAlready)) {
					$tmp->action = 'edit';
					$tmp->PersonDisp_id = $isAlready;
				}

				$sql = "
					select
						MS.MedStaffFact_id as \"MedStaffFact_id\",
						MS.LpuSection_id as \"LpuSection_id\",
						MS.Person_Fio as \"Person_Fio\"
					from r101.GetPersonal GP
					inner join r101.GetPersonalHistory GPH on GP.PostID = GPH.PostId
					inner join r101.GetPersonalHistoryWP GPHWP on GPHWP.GetPersonalHistory_id = GPH.GetPersonalHistory_id
					inner join v_MedStaffFact MS on GPHWP.WorkPlace_id = MS.MedStaffFact_id
					where GP.PersonalID = :ID_Sur and gp.PostTypeID = 1
					limit 1
				";

				if (!empty($res->Vra_UID) && !empty($res->Vra_UID->ID_Sur)){
					$params = [ 'ID_Sur' => number_format( $res->Vra_UID->ID_Sur, 0, '', '' ) ];

					$tmpResult = $this->getFirstRowFromQuery($sql,$params);

					if (!empty($tmpResult) && is_array($tmpResult)) {
						$tmp->Vra_UID_MedStaffFact_id = $tmpResult['MedStaffFact_id'];
						$tmp->Vra_UID_LpuSection_id = $tmpResult['LpuSection_id'];
					}
				}

				if (!empty($res->Vra_add) && !empty($res->Vra_add->ID_Sur)){
					$params = [ 'ID_Sur' => number_format( $res->Vra_add->ID_Sur, 0, '', '' ) ];

					$tmpResult = $this->getFirstRowFromQuery($sql,$params);

					if (!empty($tmpResult) && is_array($tmpResult)) {
						$tmp->PersonDispHist_MedPersonalFio = $tmpResult['Person_Fio'];
					}
				}

				$result[] = $tmp;
			}

			return [
				'all' => $all,
				'success' => true,
				'DispCards' => $result,
				'count' => count($result)
			];
		} catch(Exception $e) {
			if (!empty($_REQUEST['getDebug'])) {
				var_dump($e->getMessage());
			}
			throw new Exception($e->getMessage());
		}
	}
}