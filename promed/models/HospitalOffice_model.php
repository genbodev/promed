<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * HospitalOffice_model - модель
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

class HospitalOffice_model extends swModel {
	protected $_hoConfig = array();
	protected $_soapClients = array();
	protected $_syncSprList = array(); // список синхронизированных справочников
	protected $_syncSprTables = array(); // список таблиц для синхронизации справочников
	protected $_ticket = ""; // токен авторизованного пользователя

	protected $_execIteration = 0;
	protected $_maxExecIteration = 1;
	protected $_execIterationDelay = 300;
	
	protected $data;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		ini_set("default_socket_timeout", 120);

		$this->load->library('textlog', array('file'=>'HospitalOffice_'.date('Y-m-d').'.log'));

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('HOKZ');

		$this->_hoConfig = $this->config->item('HospitalOffice');
	}

	/**
	 * Выполнение запроса к сервису
	 */
	function exec($method, $type = 'get', $data = null) {
		$data = json_encode($data);
		$config = $this->config->item('HospitalOffice');
		if (!empty($this->data['user'])) {
			$config['user'] = $this->data['user'];
		}
		if (!empty($this->data['pass'])) {
			$config['password'] = $this->data['pass'];
		}
		$this->load->library('swServiceKZ', $config, 'swserviceho');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->swserviceho->data($method, $type, $data);
		$this->textlog->add("result: ".print_r($result,true));
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса БГ: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса БГ: '.$result->ExceptionMessage
			);
		}
		return $result;
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
					left join {$theirTable} their on their.P_ID = link.P_ID
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
			elseif (!empty($resp[0]['id'])) {
				$this->_syncSprList[$table][$id] = $resp[0]['id'];
				return $resp[0]['id'];
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
		// сохраняем в памяти
		$this->_syncObjectList[$table][$id] = $value;

		// сохраняем в БД
		$this->ObjectSynchronLog_model->saveObjectSynchronLog($table, $id, $value, $ins);
	}
	
	/**
	 * Получение данных о пациенте
	 */
	function getPatientModel($Person_id) {
		
		if (!$Person_id) {
			return false;
		}
		
		$query = "
			declare @curDT datetime = dbo.tzGetdate();
			select 
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_Phone,
				ps.Sex_id,
				p.Person_IsInErz,
				--convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
				convert(varchar(19), ps.Person_BirthDay, 127) as Person_BirthDay,
				dbo.Age2(ps.Person_BirthDay, @curDT) as PersonAge,
				ps.SocStatus_id,
				coalesce(pa.referenceTypeIdCode,ua.referenceTypeIdCode,'300') as referenceTypeIdCode,
				aMOID.ID as svaId,
				org.Org_Name as workPlace,
				isnull(pa.Address_Address,ua.Address_Address) as adressRu,
				case 
					when ua.Address_Address is not null then 400 
					when pa.Address_Address is not null then 200
					else null
				end as addressTypeID,
				p.BDZ_id,
				ps.Person_Inn,
				hBIOSex.p_publCod as sexIdCode,
				case
					when ISNULL(D.DocumentType_id, 0) in (24,25,26) then 100
					when ns.KLCountry_id = 398 then 100
					when ns.KLCountry_id is not null then 200
					else 600
				end as categoryCitizensIdCode,
				Citizenship.publcode as citizenIdCode,
				prsnE.EthnosRPN_id as nationalityIdCode,
				ps.Document_id,
				case
					when isnull(p.Person_IsInErz,1) != 2 and ps.Document_id is null then
						case
							when dbo.Age2(ps.Person_BirthDay, @curDT) < 1 then '300'
							when dbo.Age2(ps.Person_BirthDay, @curDT) >=1 and dbo.Age2(ps.Person_BirthDay, @curDT) < 14 then '400'
							when dbo.Age2(ps.Person_BirthDay, @curDT) >=14 and dbo.Age2(ps.Person_BirthDay, @curDT) < 18 then '500'
							else '600'
						end
					else null
				end as ageUnknownPatientsIdCode
			from v_PersonState ps
				inner join v_Person p (nolock) on p.Person_id = ps.Person_id
				left join v_PersonInfo prsnI (nolock) on p.Person_id = prsnI.Person_id
				left join v_Ethnos prsnE (nolock) on prsnI.Ethnos_id = prsnE.Ethnos_id
				outer apply (
					select top 1 
					Address_Address, 
					Address_Flat,
					Address_House, 
					KLCountry_id,
					case 
						when KLTown_id is not null then 400
						else 300
					end as referenceTypeIdCode
					from v_PersonUAddress with (nolock)
					where Person_id = ps.Person_id
				) ua
				outer apply (
					select top 1 
					Address_Address, 
					Address_Flat,
					Address_House, 
					KLCountry_id,
					case 
						when KLTown_id is not null then 400
						else 300
					end as referenceTypeIdCode
					from v_PersonPAddress with (nolock)
					where Person_id = ps.Person_id
				) pa
				outer apply (
					select top 1 MO.ID
					from v_PersonCard pc with (nolock)
					inner join r101.GetMO MO (nolock) on MO.Lpu_id = pc.Lpu_id
					where pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
				) aMOID
				left join v_Job job (nolock) on job.Job_id = ps.Job_id
				left join v_Org org (nolock) on org.Org_id = job.Org_id
				left join r101.hBIOSexLink hBIOSexLink (nolock) on hBIOSexLink.Sex_id = ps.Sex_id
				left join r101.hBIOSex hBIOSex (nolock) on hBIOSex.p_ID = hBIOSexLink.p_ID
				left join NationalityStatus ns with (nolock) on ns.NationalityStatus_id = ps.NationalityStatus_id
				left join r101.CitizenshipLink CitizenshipLink (nolock) on CitizenshipLink.KLCountry_id = ns.KLCountry_id
				left join r101.Citizenship Citizenship (nolock) on Citizenship.p_ID = CitizenshipLink.p_ID
				left join v_Document D (nolock) on PS.Document_id = D.Document_id
			where ps.Person_id = :Person_id
		";
		$resp = $this->getFirstRowFromQuery($query, array(
			'Person_id' => $Person_id
		));
		
		if (is_array($resp)) {
			$result = array(
				'isUnknowPatient' => ($resp['Person_IsInErz'] != 2 && empty($resp['Document_id'])),
				'isAnonymouspatient' => false,
				//'isHandMadePerson' => false,
				'isHandMadePerson' => (empty($resp['BDZ_id']))?true:false,
				//'isChild' => ($resp['PersonAge'] <= 14),
				'isOrganized' => ($resp['PersonAge'] <= 14) ? ($resp['SocStatus_id'] == 79) : null,
				'organizedTypeIdCode' => '',
				'benefitTypeIdCode' => '2700', // придется переопределять в других методах, т.к. учитывается дата госпитализации
				'categoryCitizensIdCode' => (string)$resp['categoryCitizensIdCode'],
				'referenceTypeIdCode' => (string)$resp['referenceTypeIdCode'],
				//'ageUnknownPatientsIdCode' => null,
				'ageUnknownPatientsIdCode' => $resp['ageUnknownPatientsIdCode'],
				'svaId' => (!empty($resp['svaId']))?(string)$resp['svaId']:'0',
				'note' => '',
				'workPlace' => $resp['workPlace'],
				'parentWorkPlace' => '',
				'anonymousAreaId' => null,
				'organizedLastIncomeDate' => '',
				'adressKz' => '',
				'adressRu' => (string)$resp['adressRu'],
				'RPNApartmentID' => null,
				'RPNBuildingID' => null,
				'pAddressID' => null,
				'addressTypeID' => $resp['addressTypeID'],
				'kato' => null,
				'arBuildingId' => null,
				'arApartmentId' => null,
				'selectedPatient' => array(
					'id' => null,
					'birthDate' => $resp['Person_BirthDay'].'+06:00',
					'rpnID' => $resp['BDZ_id'],
					'hGBD' => null,
					'personin' => $resp['Person_Inn'],
					'lastName' => $resp['Person_SurName'],
					'firstName' => $resp['Person_FirName'],
					'secondName' => $resp['Person_SecName'],
					'sexIdCode' => $resp['sexIdCode'],
					'nationalityIdCode' => $resp['nationalityIdCode'],
					'citizenIdCode' => $resp['citizenIdCode']
				),
				'phones' => empty($resp['Person_Phone']) ? array() : array(array(
					'phoneNumber' => $resp['Person_Phone'],
					'phoneTypeIdCode' => null
				)),
				'files' => array()
			);
			if ($resp['PersonAge'] > 50) {
				unset($result['parentWorkPlace']);
			}
			return $result;
		}
		
		return false;
	}

	/**
	 * Получаем areaID
	 */
	function getAreaID($Lpu_id){
		$areaIDList = $this->config->item('areaID');
		$areaId = null;
		$areaIDDefault = null;

		foreach ($areaIDList as $arID=>$LpuListID) {
			if (sizeof($LpuListID) == 0){
				$areaIDDefault = $arID;
			} elseif (in_array($Lpu_id,$LpuListID)){
				$areaId = $arID;
			}
		}

		if (empty($areaId)) $areaId = $areaIDDefault;

		return $areaId;
	}

	/**
	 * Получение данных об организации
	 */
	function getOrgSchemeInfo($Lpu_id) {
		
		if (!$Lpu_id) {
			return false;
		}
		
		$query = "
			select top 1 ID as MOID
			from r101.GetMO (nolock)
			where Lpu_id = :Lpu_id
		";

		$resp = $this->getFirstRowFromQuery($query, array(
			'Lpu_id' => $Lpu_id
		));

		if (is_array($resp)) {
			return array(
				'orgHealthCareID' => (string)$resp['MOID'],
				//'funcStructureOrgID' => '',
				'areaID' => $this->getAreaID($Lpu_id)
			);
		}
		
		return false;
		
	}
	
	/**
	 * Добавление направления на госпитализацию
	 */
	function saveReferral($data) {
		$filter = "";
		$result = false;
		$isForm = $data['isForm'] ?? false;

		if (!empty($this->_hoConfig['Lpu_ids'])) {
			$filter .= " and ed.Lpu_id IN ('".implode("','", $this->_hoConfig['Lpu_ids'])."')";
			$filter .= " and ls.Lpu_id IN ('".implode("','", $this->_hoConfig['Lpu_ids'])."')";
		}

		if (!empty($data['Evn_id'])) {
			$queryParams = array(
				'Evn_id' => $data['Evn_id']
			);
			$filter .= " and ed.EvnDirection_id = :Evn_id";
		}
		
		// сюда попадают все ранее не отправленные направления на госпитализацию
		$query = "
			declare @yesterday date = DATEADD(day, -14, dbo.tzGetdate()),
					@curDT date = dbo.tzGetdate();
			select
				ed.Person_id,
				ed.EvnDirection_id,
				ed.EvnDirection_Descr,
				ed.MedStaffFact_id,
				ls.LpuSectionBedProfile_id,
				ls.Lpu_id as Lpu_did,
				ls.LpuUnit_id as LpuUnit_did,
				ed.Lpu_id,
				ls2.LpuUnit_id,
				isnull(lu.LpuUnitType_id, ed.LpuUnitType_id) as LpuUnitType_id,
				pac.PersonAmbulatCard_Num,
				convert(varchar(19), isnull(tts.TimeTableStac_setDate ,ed.EvnDirection_setDT), 126) as EvnDirection_setDate,
				convert(varchar(19), ed.EvnDirection_desDT, 126) as EvnDirection_desDT,
				diag.Diag_Code,
				diag.Diag_Name,
				gph.PostID,
				gph.FPID,
				gph.MOID,
				isnull(pp.benefitTypeIdCode, 2700) as benefitTypeIdCode,
				case 
					when hbp.p_publCod is not null then hbp.p_publCod
					when lu.LpuUnitType_id in (7,9) then isnull(bp.BedProfile_Code, 700)
					else isnull(bp.BedProfile_Code, 800)
				end as BedProfile_Code,
				--gb.StacType,
				--ipht.p_publCod as StacType,
				case 
					when isnull(lu.LpuUnitType_id, ed.LpuUnitType_id) = 7 then 'HospitalAtHome'
					else ipht.p_publCod
				end as StacType,
				isnull(gb.BedProfile, bp.BedProfile_id) as BedProfile_id,
				--ptl.PayTypeLink_SUR,
				ptl.PayType_id,
				ptl.PayTypeLink_PubCOD,
				ed.DirType_id,
				ELA.EvnLinkAPP_StageRecovery,
				uc.UslugaComplex_Code,
				ph.PurposeHospital_Code,
				convert(varchar(19), isnull(lastevnps.EvnPS_disDate, ed.EvnDirection_setDT), 126) as outDate,
				cdiag.Diag_Code as Diag_CodeC,
				cdiag.Diag_Name as Diag_NameC
			from
				v_EvnDirection ed (nolock)
				inner join r101.GetMO MO (nolock) on MO.Lpu_id = ed.Lpu_id
				left join r101.EvnDirectionLink edl (nolock) on edl.EvnDirection_id = ed.EvnDirection_id
				left join v_TimeTableStac_lite tts (nolock) on tts.TimeTableStac_id = ed.TimeTableStac_id
				left join v_Diag diag with (nolock) on diag.Diag_id = ed.Diag_id
				inner join v_PayType pt (nolock) on pt.PayType_id = ed.PayType_id
				inner join r101.PayTypeLink ptl on pt.PayType_id = ptl.PayType_id 
				left join v_LpuSection ls2 (nolock) on ls2.LpuSection_id = ed.LpuSection_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = ed.LpuSection_did
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				inner join v_Person p (nolock) on p.Person_id = ed.Person_id
				inner join v_PersonState ps (nolock) on p.Person_id = ps.Person_id
				outer apply (
					select top 1 PersonAmbulatCard_Num
					from v_PersonAmbulatCard pac with (nolock)
					where pac.Person_id = ed.Person_id /*and pac.Lpu_id = ed.Lpu_id*/
				) pac
				outer apply (
					select top 1
						gpw.PostID,
						gpw.ID,
						gpw.PersonalID,
						gpw.FPID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = ed.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = ed.EvnDirection_id
				left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
				left join r101.hInPatientHelpTypes ipht (nolock) on ipht.p_ID = gb.StacType
				left join r101.EvnLinkAPP ELA on ELA.Evn_id = ed.EvnDirection_id
				left join r101.hBedProfile hbp (nolock) on hbp.p_ID = gb.BedProfile
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ed.UslugaComplex_did
				left join r101.v_PurposeHospital ph (nolock) on ph.PurposeHospital_id = ELA.PurposeHospital_id
				left join v_Diag cdiag with (nolock) on cdiag.Diag_id = ELA.Diag_cid
				outer apply (
					select top 1
						hbp.p_publCod as BedProfile_Code,
						hbp.p_ID as BedProfile_id
					from
						v_MedStaffFact msf (nolock)
						inner join r101.v_GetPersonalHistoryWP gphwp (nolock) on gphwp.WorkPlace_id = msf.MedStaffFact_id
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						inner join r101.v_GetFP fp (nolock) on fp.FPID = gpw.FPID
						inner join r101.v_GetRoom gr (nolock) on gr.FPID = fp.FPID
						inner join r101.v_GetBed gb (nolock) on gb.ROOMid = gr.ID
						inner join r101.hBedProfile hbp (nolock) on hbp.p_ID = gb.BedProfile
					where
						msf.LpuSection_id = ed.LpuSection_did and
						hbp.CureStandartAgeGroupType_id in((case when dbo.Age2(ps.Person_BirthDay, dbo.tzGetdate()) >= 18 then 1 else 2 end),3) and 
						isnull(hbp.p_dend, '2099-01-01') >= dbo.tzGetDate() and
						gb.LastAction = 1
				) bp
				outer apply (
					select top 1 
						hSocType.code as benefitTypeIdCode
					from 
						v_PersonPrivilege pp (nolock)
						left join r101.hSocTypeLink hSocTypeLink (nolock) on hSocTypeLink.PrivilegeType_id = pp.PrivilegeType_id
						left join r101.hSocType hSocType (nolock) on hSocType.id = hSocTypeLink.id
					where pp.Person_id = ps.Person_id
						and isnull(tts.TimeTableStac_setDate,ed.EvnDirection_setDT) between pp.PersonPrivilege_begDate and isnull(pp.PersonPrivilege_endDate, '2099-01-01')
					order by pp.PersonPrivilege_begDate desc
				) pp
				outer apply (
					select top 1 eps.EvnPS_disDate
					from v_EvnPS eps (nolock)
					where eps.Person_id = ps.Person_id and eps.EvnPS_disDate is not null
					order by eps.EvnPS_disDate desc
				) lastevnps
			where
				edl.EvnDirectionLink_id is null
				and ed.DirType_id in (1,4,5)
				and isnull(lu.LpuUnitType_id, ed.LpuUnitType_id) in (1,6,7,9)
				--and pt.PayType_SysNick not in ('money')
				and cast(ed.EvnDirection_setDT as date) >= @yesterday
				and cast(isnull(tts.TimetableStac_setDate, @curDT) as date) >= @curDT
				{$filter}
		";
		$resp = $this->queryResult($query, $data);

		foreach ($resp as $response) {
			try {
				if ($response['PayType_id'] == 238 || $response['PayType_id'] == 151){
					$response['finSrcTypeIdCode'] = '200';
				} elseif ($response['PayType_id'] == 150) {
					$response['finSrcTypeIdCode'] = '6900';
				} elseif ($response['PayType_id'] == 153) {
					$response['finSrcTypeIdCode'] = '7100';
				} else {
					$response['finSrcTypeIdCode'] = $response['PayTypeLink_PubCOD'];
				}

				if (!empty($response['DirType_id']) && in_array(intval($response['DirType_id']), array(1,4)) && in_array(intval($response['LpuUnitType_id']), array(6,9,7))) {
					$response['referralTypeIdCode'] = ($response['DirType_id']==1)?'1600':'1700';
				} elseif (!empty($response['LpuUnitType_id']) && !in_array(intval($response['LpuUnitType_id']), array(6,9,7))) {//elseif (!empty($response['DirType_id']) && $response['DirType_id'] != 1) {
					$response['referralTypeIdCode'] = $this->getFirstResultFromQuery("
						select ht.publcode from r101.HospitalType HT
						inner join r101.HospitalTypeLink HTL on HT.p_ID = HTL.p_ID 
						where htl.DirType_id = :DirType_id",[ 'DirType_id' => $response['DirType_id'] ]);
					if (empty($response['referralTypeIdCode'])) $response['referralTypeIdCode'] = '500';
				} else {
					$response['referralTypeIdCode'] = '500';
				}

				$response['rehabilitationTypeIdCode'] = '';
				if (!empty($response['DirType_id']) && !empty($response['EvnLinkAPP_StageRecovery']) && $response['DirType_id'] == 4){
					if ($response['EvnLinkAPP_StageRecovery'] == 2) $response['rehabilitationTypeIdCode'] = '100';
					if ($response['EvnLinkAPP_StageRecovery'] == 3) $response['rehabilitationTypeIdCode'] = '200';
				}

				if (!empty($response['PurposeHospital_Code'])) {
					$response['referralTargetIdCode'] = $response['PurposeHospital_Code'];
				} else {
					$response['referralTargetIdCode'] = in_array($response['BedProfile_id'], array(3,81)) ? '200' : '100';
				}

				if (!empty($data['bookingDateReserveId'])) $response['bookingDateReserveId'] = $data['bookingDateReserveId'];

				if (!empty($data['EvnDirectionFiles'])) $response['EvnDirectionFiles'] = $data['EvnDirectionFiles'];

				$res = $this->saveReferralSend($response);
				if (is_array($res) && $res['error'] != 0) {
					$result = $res;
				} else {
					$result = true;
				}
			} catch (Exception $e) {
				/*if (!empty($_REQUEST['getDebug'])) {
					var_dump($e);
				}*/
				// падать не будем, просто пишем в лог инфу и идем дальше
				$this->textlog->add("saveReferral error: code: " . $e->getCode() . " message: " . $e->getMessage());
				if (!$isForm) {
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $this->ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => $e->getMessage() . " (EvnDirection_id={$response['EvnDirection_id']})",
						'pmUser_id' => 1
					));
				}
				$result = [
					'error' => 1,
					'msg' => 'Сервис БГ не ответил на запрос',
				];
			}
		}

		return $result;
	}
	
	/**
	 * Добавление направления на госпитализацию: отправка
	 */
	function saveReferralSend($data) {
		$this->textlog->add("saveReferral: ".$data['EvnDirection_id']);

		$pt = $this->getFinSrcByCritery($data);
		
		$params = array(
			'externalSystemCode' => 'kazmedinform',
			'patient' => $this->getPatientModel($data['Person_id']),
			'orgSchemeDirect' => $this->getOrgSchemeInfo($data['Lpu_id']),
			'orgSchemeRef' => $this->getOrgSchemeInfo($data['Lpu_did']),
			'referralTypeIdCode' => $data['referralTypeIdCode'],
			//'bedProfileIdCode' => (string)$data['BedProfile_Code'],
			'referralTargetIdCode' => $data['referralTargetIdCode'],
			'surgProcIdCode' => (in_array($data['referralTargetIdCode'],[200,1000,1100]))?$data['UslugaComplex_Code']:null,
			'inPatientHelpType' => $data['StacType'],
			'doctorPostId' => (string)$data['PostID'],
			//'rehabilitationTypeIdCode' => '',
			'rehabilitationTypeIdCode' => $data['rehabilitationTypeIdCode'],
			'outDate' => ($data['DirType_id'] == 4) ? $data['outDate'] : '',
			'cardNumber' => $data['PersonAmbulatCard_Num'],
			'diagnosesList' => array(array(
				'sickIdCode' => str_replace('.', '', $data['Diag_Code']),
				'sickName' => $data['Diag_Name'],
				'diagnosisTypeIdCode' => ($data['DirType_id'] == 5) ? '300' : '200',
				'diagnosisTypeName' => ($data['DirType_id'] == 5) ? 'Предварительный' : 'Направительный',
				'diagTypeIdCode' => '200',
				'diagTypeName' => 'Основное',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => '',
			)),
			'planDate' => ((!empty($data['EvnDirection_desDT']))?$data['EvnDirection_desDT']:$data['EvnDirection_setDate']).'+06:00',
			'bookingDateReserveId' => (!empty($data['bookingDateReserveId']))?$data['bookingDateReserveId']:'',
			'additionalInformation' => $data['EvnDirection_Descr'],
			'vsmpData' => array(),
			'isValidatedRemainingCount' => null,
			'isValidatedBedProfile' => true,
			'referralsValidateExceptions' => array(),
			'hospitalizationId' => null,
			'referralBaseId' => null,
			'removalJustification' => '',
			'hasIndicationsForHospital' => null,
			'files' => $this->getEvnDirectionFiles($data),
			'rehabilitationFiles' => ($data['referralTypeIdCode']=='1700')?$this->getEvnDirectionFiles($data):array(),
			'isAccessVTMU' => null,
			'finSrcTypeIdCode' => (string)$data['finSrcTypeIdCode'],
			'finSrcReserveId' => !in_array($pt[1], [0, 300]) ? $pt[0] : null
		);

		if (!empty($data['bookingDateReserveId'])) {
			$params['isAutoBookin'] = true;
			$params['isBooking'] = true;
		}

		if ($data['StacType'] != 'Hospital') {
			//$params['bedProfileIdCode'] = (in_array($data['referralTypeIdCode'],['1600','1700']))?null:'';
			if (!in_array($data['referralTypeIdCode'],['1600','1700'])) $params['bedProfileIdCode'] = '';
			$params['dayHospitalAttach'] = $data['LpuUnitType_id'] == 6 ? '200' : '100';
		} else {
			$params['bedProfileIdCode'] = (string)$data['BedProfile_Code'];
		}

		if (!empty($data['Diag_CodeC'])) {
			$params['diagnosesList'][] = [
				'sickIdCode' => str_replace('.', '', $data['Diag_CodeC']),
				'sickName' => $data['Diag_NameC'],
				'diagnosisTypeIdCode' => ($data['DirType_id'] == 5) ? '300' : '200',
				'diagnosisTypeName' => ($data['DirType_id'] == 5) ? 'Предварительный' : 'Направительный',
				'diagTypeIdCode' => '800',
				'diagTypeName' => 'Уточняющее',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => '',
			];
		}

		if ($data['referralTypeIdCode']=='1700' || $data['referralTypeIdCode']=='700') {
			$params['diagnosesList'][] = [
				'sickIdCode' => str_replace('.', '', $data['Diag_Code']),
				'sickName' => $data['Diag_Name'],
				'diagnosisTypeIdCode' => '500',
				'diagnosisTypeName' => 'Заключительный',
				'diagTypeIdCode' => '200',
				'diagTypeName' => 'Основное',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => ''
			];
		}

		$params['patient']['benefitTypeIdCode'] = (string)$data['benefitTypeIdCode'];

		$response = $this->exec('/referrals/saveReferral', 'post', $params);

		if (is_array($response) && isset($response[0])) {
			$response = $response[0];
		}

		if (is_object($response) && !empty($response->message)) {
			return [
				'error' => 1,
				'msg' => $response->message,
			];
		} elseif (is_object($response) && !empty($response->questionMessage)) {
			return [
				'error' => 2,
				'msg' => $response->questionMessage,
			];
		} elseif (is_object($response) && !empty($response->referralId)) {
			$query = "
				declare
					@EvnDirectionLink_id bigint,
					@ErrCode int,
					@ErrMsg varchar(4000);
				exec r101.p_EvnDirectionLink_ins
					@EvnDirectionLink_id = @EvnDirectionLink_id output,
					@EvnDirection_id = :EvnDirection_id,
					@Referral_id = :Referral_id,
					@Referral_Code = :Referral_Code,
					@Cancel_id = null,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @EvnDirectionLink_id as EvnDirectionLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$this->db->query($query, array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'Referral_id' => $response->referralId,
				'Referral_Code' => $response->referralCode ?? null,
				'pmUser_id' => 1
			));
		} else {
			throw new Exception('Ошибка при выполнении saveReferral', 400);
		}

		return [
			'error' => 0
		];
	}

	/**
	 * Определение ИФ для направления
	 * @param $data
	 * @return array
	 */
	function getFinSrcByCritery($data) {
		$this->textlog->add("getFinSrcByCritery: ".$data['EvnDirection_id']);

		$params = array(
			'sicks' => [[
				'sickIdCode' => str_replace('.', '', $data['Diag_Code']),
				'sickName' => $data['Diag_Name'],
				'diagnosisTypeIdCode' => ($data['DirType_id'] == 5) ? '300' : '200',
				'diagnosisTypeName' => ($data['DirType_id'] == 5) ? 'Предварительный' : 'Направительный',
				'diagTypeIdCode' => '200',
				'diagTypeName' => 'Основное',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => '',
			]],
			'isRehabilitation' => ($data['DirType_id'] == 4),
			'helpType' => $data['StacType'],
			'patient' => $this->getPatientModel($data['Person_id'])
		);
		
		if ($data['DirType_id'] == 5) {
			$params['surgProcIdCode'] = $data['UslugaComplex_Code'];
			$params['noticeTarget'] = $data['PurposeHospital_Code'];
			$params['externType'] = $data['DirType_id'] == 5 ? '200' : '100';
		}

		if (!empty($data['Diag_CodeC'])) {
			$params['sicks'][] = [
				'sickIdCode' => str_replace('.', '', $data['Diag_CodeC']),
				'sickName' => $data['Diag_NameC'],
				'diagnosisTypeIdCode' => ($data['DirType_id'] == 5) ? '300' : '200',
				'diagnosisTypeName' => ($data['DirType_id'] == 5) ? 'Предварительный' : 'Направительный',
				'diagTypeIdCode' => '800',
				'diagTypeName' => 'Уточняющее',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => '',
			];
		}

		$response = $this->exec('/finSrc/getFinSrcByCritery', 'post', $params);

		if (is_object($response) && !empty($response->errors)) {
			return [
				0, 0,
				$response->errors[0]->message
			];
		} elseif (is_object($response) && !empty($response->finSrcReserveId)) {
			return [
				$response->finSrcReserveId,
				$response->finSrc
			];
		} else {
			return [0, 0];
		}
	}

	/**
	 * Рассчитать плановую дату госпитализации, используя АОДПГ
	 * @param $data
	 * @return array
	 */
	function calculateAutoBookingDate($data) {
		$this->textlog->add("calculateAutoBookingDate: ".$data['EvnDirection_id']);

		if (!empty($data['PurposeHospital_Code'])) {
			$data['referralTargetIdCode'] = $data['PurposeHospital_Code'];
		} else {
			$data['referralTargetIdCode'] = in_array($data['BedProfile_id'], array(3,81)) ? '200' : '100';
		}

		$params = [
			'orgSchemeRef' => $this->getOrgSchemeInfo($data['Lpu_did']),
			'bedProfileIdCode' => (string)$data['BedProfile_Code'],
			'directSickIdCode' => str_replace('.', '', $data['Diag_Code']),
			'patientBirthDate' => $data['Person_BirthDay'].'+06:00',
			'surgProcIdCode' => (in_array($data['referralTargetIdCode'],[200,1000,1100]) && !empty($data['UslugaComplex_Code']))?$data['UslugaComplex_Code']:null,
			'referralTargetIdCode' => $data['referralTargetIdCode'],
			'beginDate' => ((!empty($data['EvnDirection_desDT']))?$data['EvnDirection_desDT']:$data['EvnDirection_setDate']).'T00:00:00+06:00',
			'bookingDateReserveId' => (!empty($data['bookingDateReserveId']))?$data['bookingDateReserveId']:''
		];

		$response = $this->exec('/booking/calculateAutoBookingDate', 'post', $params);

		if (is_array($response) && isset($response[0])) {
			$response = $response[0];
		}

		if (is_object($response) && !empty($response->message)) {
			return ['success' => false, 'msg' => $response->message];
		} elseif (is_object($response) && !empty($response->hospitalDate)) {
			return [
				'success' => true,
				'date' => $response->hospitalDate,
				'bookingDateReserveId' => (!empty($response->bookingDateReserveId))?$response->bookingDateReserveId:null
			];
		} else {
			return ['success' => false];
		}
	}
	
	/**
	 * Сохранение отказа из направления
	 */
	function SaveHospitalRefuse($data) {
		
		$filter = "";

		if (!empty($this->_hoConfig['Lpu_ids'])) {
			$filter .= " and ed.Lpu_id IN ('".implode("','", $this->_hoConfig['Lpu_ids'])."')";
		}

		if (!empty($data['Evn_id'])) {
			$queryParams = array(
				'Evn_id' => $data['Evn_id']
			);
			$filter .= " and ed.EvnDirection_id = :Evn_id";
		}
		
		$query = "
			declare @yesterday date = DATEADD(day, -14, dbo.tzGetdate());
			select
				edl.Referral_id,
				d.Diag_Code,
				d.Diag_Name,
				esc.EvnStatusCause_id,
				esc.EvnStatusCause_Name,
				hrr.Code as removalReasonIdCode,
				d.Diag_Code,
				d.Diag_Name,
				gph.PostID,
				gph.MOID,
				ed.EvnDirection_id,
				ed.Lpu_id
			from
				v_EvnDirection ed (nolock)
				inner join r101.v_EvnDirectionLink edl (nolock) on edl.EvnDirection_id = ed.EvnDirection_id
				cross apply (
					select top 1
						esh.EvnStatusCause_id,
						esh.pmUser_updID
					from
						v_EvnStatusHistory esh with (nolock)
					where
						esh.Evn_id = ed.EvnDirection_id
						and esh.EvnStatus_id = ed.EvnStatus_id
						and esh.EvnStatusCause_id is not null
					order by
						esh.EvnStatusHistory_id desc
				) esh
				inner join v_EvnStatusCause esc (nolock) on esc.EvnStatusCause_id = esh.EvnStatusCause_id
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = ed.LpuSection_did
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
				left join v_PayType pt (nolock) on pt.PayType_id = ed.PayType_id
				left join r101.hospitalizationRefusalReasonsLink hrrl (nolock) on hrrl.EvnStatusCause_id = esh.EvnStatusCause_id
				left join r101.hospitalizationRefusalReasons hrr (nolock) on hrr.id = hrrl.hospitalizationRefusalReasons_id
				outer apply (
					select top 1
						gpw.PostID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = gphwp.WorkPlace_id
						inner join v_pmUserCache puc (nolock) on puc.MedPersonal_id = msf.MedPersonal_id
					where
						puc.PMUser_id = esh.pmUser_updID
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				ed.EvnStatus_id in (12,13)
				and lu.LpuUnitType_id in (1,6,7,9)
				--and pt.PayType_SysNick in ('bud', 'Resp')
				--and pt.PayType_SysNick in ('bud', 'Resp', 'selo')
				and edl.Cancel_id is null -- Событие отмены направления НЕ передано в сервис БГ
				and cast(ed.EvnDirection_statusDate as date) >= @yesterday
				{$filter}
		";

		$resp = $this->queryResult($query, $data);
		foreach ($resp as $response) {
			try {
				$this->SaveHospitalRefuseSend($response);
			} catch (Exception $e) {
				/*if (!empty($_REQUEST['getDebug'])) {
					var_dump($e);
				}*/
				// падать не будем, просто пишем в лог инфу и идем дальше
				$this->textlog->add("SaveHospitalRefuse error: code: " . $e->getCode() . " message: " . $e->getMessage());
				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => $e->getMessage() . " (EvnDirection_id={$response['EvnDirection_id']})",
					'pmUser_id' => 1
				));
			}
		}
	}
	
	/**
	 * Направление отказа от госпитализации: отправка
	 */
	function SaveHospitalRefuseSend($data) {
		$this->textlog->add("SaveHospitalRefuse: ".$data['EvnDirection_id']);
		
		$params = array(
			'externalSystemCode' => 'kazmedinform',
			'referralId' => $data['Referral_id'],
			'removalReasonIdCode' => $data['removalReasonIdCode'],
			'removalJustification' => $data['EvnStatusCause_Name'],
			'diagnosesList' => array(
				array(
					'sickIdCode' => str_replace('.', '', $data['Diag_Code']),
					'sickName' => $data['Diag_Name'],
					'diagnosisTypeIdCode' => '200',
					'diagnosisTypeName' => 'Направительный',
					'diagTypeIdCode' => '200',
					'diagTypeName' => 'Основное',
					'traumaTypeIdCode' => '',
					'traumaTypeName' => ''
				)
			),
			'ad' => '0/0',
			'temperature' => '0',
			'doctorPostId' => $data['PostID']
		);

		$OrgSchemeInfo = $this->getOrgSchemeInfo($data['Lpu_id']);

		$params['orgScheme'] = [
			//'orgHealthCareID' => $OrgSchemeInfo['orgHealthCareID'],
			'orgHealthCareID' => $data['MOID'],
			'areaID' => $OrgSchemeInfo['areaID']
		];

		$response = $this->exec('/hospitalizations/saveHospitalRefusal', 'post', $params);

		if (!empty($response->SaveHospitalRefuseResult->ErrorMessage)) {
			throw new Exception('Ошибка при выполнении SaveHospitalRefuse: ' . $response->SaveHospitalRefuseResult->ErrorMessage, 400);
		}
		else {
			$query = "update r101.EvnDirectionLink set Cancel_id = :Cancel_id where ExternalHospInfo_id = :ExternalHospInfo_id";
			$this->db->query($query, array('Cancel_id' => $response->SaveHospitalRefuseResult->Id, 'Referral_id' => $data['Referral_id']));
		}

		//$this->saveSyncObject('EvnDirection', $data['EvnDirection_id'], null);
	}

	/**
	 * Сохранение факта госпитализации/отказа
	 */
	function SaveHospitalOrReject($data) {
		
		$filter = "";

		if (!empty($this->_hoConfig['Lpu_ids'])) {
			$filter .= " and eps.Lpu_id IN ('".implode("','", $this->_hoConfig['Lpu_ids'])."')";
		}

		if (!empty($data['Evn_id'])) {
			$queryParams = array(
				'Evn_id' => $data['Evn_id']
			);
			$filter .= " and eps.EvnPS_id = :Evn_id";
		}
		
		$query = "
			declare @yesterday date = DATEADD(day, -3, dbo.tzGetdate()),
					@curDT datetime = dbo.tzGetdate();
			select
				eps.EvnPS_id,
				eps.Lpu_id,
				eps.Lpu_did,
				lu.LpuUnit_id,
				eps.EvnPS_NumCard,
				convert(varchar(10), DATEADD(day, -14, eps.EvnPS_setDate), 120) as DateFrom,
				convert(varchar(10), eps.EvnPS_setDate, 120) as dateTo,
				convert(varchar(19), eps.EvnPS_setDate + ' ' + eps.EvnPS_setTime, 126) as EvnPS_setDate,
				convert(varchar(19), eps.EvnPS_disDate + ' ' + eps.EvnPS_disTime, 126) as EvnPS_disDate,
				case when EPS.PrehospWaifRefuseCause_id is not null then convert(varchar(19), eps.EvnPS_OutcomeDT, 126) else null end as EvnPS_OutcomeDate,
				coalesce(gphEsFirst.PostID, gphEPS.PostID) as PostID,
				COALESCE(gphEsFirst.FPID, gphEPS.FPID, LSFPIDL_eid.FPID, lspFirstNotPriem.FPID) as FPID,
				eps.EvnDirection_Num,
				hbp.p_publCod as BedProfile_Code,
				hbp.p_ID as BedProfile_id,
				EPS.PrehospType_id,
				EPS.PayType_id,
				EPS.PrehospWaifRefuseCause_id,
				EPS.PrehospDirect_id,
				EPS.PrehospArrive_id,
				lu.LpuUnitType_id,
				EPS.Person_id,
				case when EPS.Okei_id = '100'
				  then
				    case
					when (EPS.EvnPS_TimeDesease <= 6) then '200' -- Экстренно в первые 6 часов от начала заболевания
					when (EPS.EvnPS_TimeDesease > 24) then '400' -- Экстренно свыше 24 часов
					when EPS.EvnPS_TimeDesease is not null then '300' -- Экстренно в течение 7-24 часов
				 	else '400' -- если время НЕ уазано, то 400 Экстренно свыше 24 часов
				  end
				  else '400' -- Экстренно свыше 24 часов
				end as EvnPS_TimeDeseaseType,
				tsf.p_publCod as finSrcTypeIdCode,
				tsfp.p_publCod as finSrcIdCode,
				--ipht.p_publCod as StacType,
				case
					when ipht.p_publCod = 'DayHospital' and lut.LpuUnitType_id = 7 then 'HospitalAtHome'
					when ipht.p_publCod = 'DayHospital' and lut.LpuUnitType_id != 7 then 'DayHospital'
					else ipht.p_publCod
				end as StacType,
				hrrk.Code as refuseReasonIdCode,
				pt.PayType_SysNick,
				isnull(pp.benefitTypeIdCode, 2700) as benefitTypeIdCode,
				p.BDZ_id,
				case 
					when dbo.Age2(ps.Person_BirthDay, @curDT) < 1 and ESFIRST.EvnSection_IsAdultEscort = 2 then '300'
					when ESFIRST.EvnSection_IsAdultEscort = 2 then '200'
					else '100'
				end as hospitalizedIdCode,
				
				ph.PurposeHospital_Code,
				elaF.Diag_cid,
				D.Diag_Code
			from
				v_EvnPS eps (nolock)
				
				left join Diag D on D.Diag_id = EPS.Diag_pid
				
				left join r101.EvnLinkAPP ELA (nolock) on ELA.Evn_id = EPS.EvnPS_id
				left join r101.v_PurposeHospital ph (nolock) on ph.PurposeHospital_id = ELA.PurposeHospital_id

				left join r101.v_EvnPSLink epsl (nolock) on epsl.EvnPS_id = eps.EvnPS_id
				outer apply (
					select top 1
						es.EvnSection_id,
						es.LpuSection_id,
						es.MedPersonal_id,
						--es.MedStaffFact_id,
						es.EvnSection_IsAdultEscort
					from
						v_EvnSection ES with (nolock)
					where
						ES.EvnSection_pid = EPS.EvnPS_id and 
						es.LpuSection_id is not null and 
						es.MedPersonal_id is not null
					order by
						ES.EvnSection_setDT asc
				) ESFIRST
				left join v_MedStaffFact MSFesfirst with (nolock) on MSFesfirst.MedPersonal_id = esfirst.MedPersonal_id and MSFesfirst.LpuSection_id = esfirst.LpuSection_id
				outer apply (
					select top 1
						gpw.PostID,
						gpw.FPID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = MSFesfirst.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gphEsFirst
				outer apply (
					select top 1
						gpw.PostID,
						gpw.FPID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = EPS.MedStaffFact_pid
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gphEPS
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = isnull(ESFIRST.LpuSection_id, eps.LpuSection_pid)
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				inner join v_LpuUnitType lut (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
				left join r101.EvnLinkAPP elaF (nolock) on elaF.Evn_id = isnull(ESFIRST.EvnSection_id, eps.EvnPS_id) 
				inner join v_PersonState ps (nolock) on ps.Person_id = eps.Person_id
				inner join v_Person p (nolock) on p.Person_id = ps.Person_id
				left join v_PayType pt (nolock) on pt.PayType_id = eps.PayType_id
				left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = esfirst.EvnSection_id
				left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
				left join r101.hInPatientHelpTypes ipht (nolock) on ipht.p_ID = gb.StacType
				left join r101.hBedProfile hbp (nolock) on hbp.p_ID = gb.BedProfile
				outer apply (
					select top 1 
						hSocType.code as benefitTypeIdCode
					from 
						v_PersonPrivilege pp (nolock)
						left join r101.hSocTypeLink hSocTypeLink (nolock) on hSocTypeLink.PrivilegeType_id = pp.PrivilegeType_id
						left join r101.hSocType hSocType (nolock) on hSocType.id = hSocTypeLink.id
					where pp.Person_id = ps.Person_id
						and eps.EvnPS_setDate between pp.PersonPrivilege_begDate and isnull(pp.PersonPrivilege_endDate, '2099-01-01')
					order by pp.PersonPrivilege_begDate desc
				) pp
				left join r101.hTypSrcFin tsf (nolock) on tsf.p_ID = gb.TypeSrcFin
				left join r101.hTypSrcFin tsfp (nolock) on tsfp.p_ID = tsf.p_parent
				left join r101.hospitalizationRefusalReasonsKVSLink hrrkl (nolock) on hrrkl.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
				left join r101.hospitalizationRefusalReasonsKVS hrrk (nolock) on hrrk.id = hrrkl.hospitalizationRefusalReasonsKVS_id
				left join r101.LpuSectionFPIDLink LSFPIDL_eid on LSFPIDL_eid.LpuSection_id = eps.LpuSection_eid
				outer apply (
					select top 1
						FPID
					from v_EvnSection ES with (nolock)
					left join r101.LpuSectionFPIDLink LSFPIDL (nolock) on LSFPIDL.LpuSection_id = ES.LpuSection_id
					where
						ES.EvnSection_pid = EPS.EvnPS_id and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					order by ES.EvnSection_setDT asc
				) lspFirstNotPriem -- FPID первого профильного (не приемного отделения)
			where
				lu.LpuUnitType_id in (1,6,7,9)
				--and pt.PayType_SysNick in ('bud', 'Resp')
				--and pt.PayType_SysNick in ('bud', 'Resp', 'selo')
				and (EPS.EvnDirection_id is null) -- КВС НЕ связано с направлением ИЛИ (КВС связано с направлением);
				and epsl.Hospitalization_id is null -- КВС НЕ передано в сервис БГ, т.е. у КВС НЕ сохранен идентификатор БГ
				and cast(eps.EvnPS_setDate as date) >= @yesterday
				{$filter}
		";

		$resp = $this->queryResult($query, $data);
		foreach ($resp as $response) {
			try {
				// Если платное, значит однозначно передаём SaveHospitalOrReject
				/*if ($response['PayType_SysNick'] == 'money') {*/
					$this->SaveHospitalOrRejectSend($response);
				/*} else {
					// Иначе пробуем найти направление в БГ
					// Если нашлось, значит отправляем SaveHospitalConfirm
					$dir = $this->searchReferrals(array(
						'dateFrom' => $response['DateFrom'],
						'dateTo' => $response['dateTo'],
						'patientRpnID' => $response['BDZ_id'],
						'orgHealthCareID' => $this->getOrgSchemeInfo($response['Lpu_id']),
						'orgHealthCareRID' => $this->getOrgSchemeInfo($response['Lpu_did']),
					));
					if ($dir !== false) {
						$this->SaveHospitalConfirm(array(
							'EvnPS_id' => $response['EvnPS_id']
						));
					} else {
						$this->SaveHospitalOrRejectSend($response);
					}
				}*/
			} catch (Exception $e) {
				/*if (!empty($_REQUEST['getDebug'])) {
					var_dump($e);
				}*/
				// падать не будем, просто пишем в лог инфу и идем дальше
				$this->textlog->add("SaveHospitalOrReject error: code: " . $e->getCode() . " message: " . $e->getMessage());
				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => $e->getMessage() . " (EvnPS_id={$response['EvnPS_id']})",
					'pmUser_id' => 1
				));
			}
		}
	}

	/**
	 * Направление отказа от госпитализации: отправка
	 */
	function SaveHospitalOrRejectSend($data) {
		$this->textlog->add("SaveHospitalOrReject: ".$data['EvnPS_id']);
		
		$refTypeIdCode = $this->getSyncSpr('hTreatmentType', $data['PrehospDirect_id'], true);
		if (empty($refTypeIdCode)) {
			$refTypeIdCode = ($data['PrehospArrive_id'] == 1) ? 'MNO_200' : 100;
		}

		$params = array(
			'externalSystemCode' => 'kazmedinform',
			'orgScheme' => $this->getOrgSchemeInfo($data['Lpu_id']),
			'isHospital' => (!empty($data['EvnPS_OutcomeDate']))?'false':'true',
			//'hospitalHistoryNumber' => $data['EvnPS_NumCard'],
			'diseaseDate' => $data['EvnPS_setDate'],
			'doctorPostID' => $data['PostID'],
			//'referralNumber' => $data['EvnDirection_Num'],
			//'funcStructureId' => $data['FPID'],
			//'hospitalDate' => $data['EvnPS_setDate'],
			'otkazDate' => $data['EvnPS_OutcomeDate'],
			'temperature' => '0',
			'ad' => '0/0',
			'outDate' => (!empty($data['EvnPS_OutcomeDate']))?$data['EvnPS_OutcomeDate']:$data['EvnPS_disDate'],
			//'rehabilitationType' => '',
			//'HasIndicationsForHospital' => '',
			'DirectionsValidateExceptions' => '',
			//'isValidatedBedProfile' => true,
			//'isAccessVTMU' => '',
			//'bedProfileIdCode' => (string)$data['BedProfile_Code'],
			//'extrenTypeIdCode' => ($data['PrehospType_id'] == 2 ? '100' : $data['EvnPS_TimeDeseaseType']),
			//'finSrcIdCode' => $data['finSrcIdCode'],
			//'finSrcTypeIdCode' => $data['finSrcTypeIdCode'],
			//'noticeTargetIdCode' => (!empty($data['PurposeHospital_Code']))?$data['PurposeHospital_Code']:(in_array($data['BedProfile_id'], array(3,81)) ? '200' : '100'),
			'refuseReasonIdCode' => $data['refuseReasonIdCode'],
			//'hospitalizedIdCode' => $data['hospitalizedIdCode'],
			'refTypeIdCode' => !empty($refTypeIdCode) ? $refTypeIdCode : 100,
			'surgProcIdCode' => '',
			'helpTypeIdCode' => (!empty($data['EvnPS_OutcomeDate']))?'Hospital':$data['StacType'],
			'patient' => $this->getPatientModel($data['Person_id']),
			'diagnosesList' => $this->getDiagnosesList($data),
			'usedDrugs' => array(),
			'usedServices' => $this->getUsedServices($data),
			'usedSurgProces' => $this->getUsedSurgProces($data),
			'files' => $this->getEvnDirectionFiles($data)
		);
		
		if (empty($data['EvnPS_OutcomeDate'])) {
			$params['hospitalHistoryNumber'] = $data['EvnPS_NumCard'];
			$params['referralNumber'] = $data['EvnDirection_Num'];
			$params['funcStructureId'] = $data['FPID'];
			$params['extrenTypeIdCode'] = ($data['PrehospType_id'] == 2 ? '100' : $data['EvnPS_TimeDeseaseType']);
			$params['noticeTargetIdCode'] = (!empty($data['PurposeHospital_Code']))?$data['PurposeHospital_Code']:(in_array($data['BedProfile_id'], array(3,81)) ? '200' : '100');
			$params['hospitalizedIdCode'] = $data['hospitalizedIdCode'];

			$params['hospitalDate'] = $data['EvnPS_setDate'];

			$params['rehabilitationType'] = '';
			$params['HasIndicationsForHospital'] = '';
			$params['isValidatedBedProfile'] = true;
			$params['isAccessVTMU'] = '';
			$params['finSrcIdCode'] = $data['finSrcIdCode'];
			$params['finSrcTypeIdCode'] = $data['finSrcTypeIdCode'];
		} else {
			$params['hospitalDate'] = 'NaN-aN-aNTaN:aN:aN';
		}

		if ($data['StacType'] == 'Hospital') $params['bedProfileIdCode'] = (string)$data['BedProfile_Code'];
		
		$params['patient']['benefitTypeIdCode'] = (string)$data['benefitTypeIdCode'];

		if (!empty($data['EvnPS_OutcomeDate'])) {
			$tmp_req = [
				'patientBirthDate' => $params['patient']['selectedPatient']['birthDate'],
				'sickIdCode' => str_replace('.', '', $data['Diag_Code']),
				'orgSchemeRef' => [
					'orgHealthCareID' => $params['orgScheme']['orgHealthCareID'],
					'funcStructureOrgID' => '',
					'areaID' => $params['orgScheme']['areaID']
				]
			];
			$tmp_res = $this->exec('/hospitalizations/calculateDrugsSum', 'post', $tmp_req);

			if(is_object($tmp_res)){
				$params['otkazSumId'] = $tmp_res->id;
				$params['otkazDrugsSum'] = $tmp_res->sum;
			}
		}

		$response = $this->exec('/hospitalizations/saveHospitalOrReject', 'post', $params);

		if (!empty($response) && !empty($response->id)) {
			$query = "
				declare
					@EvnPSLink_id bigint = :EvnPSLink_id,
					@ErrCode int,
					@ErrMsg varchar(4000);
				exec r101.p_EvnPSLink_ins
					@EvnPSLink_id = @EvnPSLink_id output,
					@EvnPS_id = :EvnPS_id,
					@Hospitalization_id = :Hospitalization_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @EvnPSLink_id as EvnPSLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$this->db->query($query, array(
				'EvnPSLink_id' => null,
				'EvnPS_id' => $data['EvnPS_id'],
				'Hospitalization_id' => $response->id,
				'pmUser_id' => 1
			));
		} else {
			throw new Exception('Ошибка при выполнении saveHospitalOrReject', 400);
		}

		//$this->saveSyncObject('EvnDirection', $data['EvnDirection_id'], null);
	}
	
	/**
	 * Список диагнозов по КВС
	 */
	function getDiagnosesList($data) {
		$diag_list = array();
		
		$query = "
			select 
				Did.Diag_Code,
				Did.Diag_Name,
				Ddid.Diag_Code as Diag_dCode,
				Ddid.Diag_Name as Diag_dName,
				Dpid.Diag_Code as Diag_pCode,
				Dpid.Diag_Name as Diag_pName
			from v_EvnPS eps with (nolock) 
				left join v_Diag Did with (nolock) on Did.Diag_id = eps.Diag_id
				left join v_Diag Ddid with (nolock) on Ddid.Diag_id = eps.Diag_did
				left join v_Diag Dpid with (nolock) on Dpid.Diag_id = eps.Diag_pid
			where eps.EvnPS_id = :EvnPS_id
		";
		$diag = $this->getFirstRowFromQuery($query, array(
			'EvnPS_id' => $data['EvnPS_id']
		));
		
		// Основной заключительный
		if (!empty($diag['Diag_dCode'])){
			$diag_list[] = array(
				'sickIdCode' => str_replace('.', '', $diag['Diag_dCode']),
				'sickName' => $diag['Diag_dName'],
				'diagnosisTypeIdCode' => 200,
				'diagnosisTypeName' => 'Направительный',
				'diagTypeIdCode' => 200,
				'diagTypeName' => 'Основной',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => ''
			);
		}
		
		// Основной предварительный
		if (!empty($diag['Diag_Code'])){
			$diag_list[] = array(
				'sickIdCode' => str_replace('.', '', $diag['Diag_Code']),
				'sickName' => $diag['Diag_Name'],
				'diagnosisTypeIdCode' => 300,
				'diagnosisTypeName' => 'Предварительный',
				'diagTypeIdCode' => 200,
				'diagTypeName' => 'Основной',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => ''
			);
		} else if (!empty($diag['Diag_pCode'])) {
			$diag_list[] = array(
				'sickIdCode' => str_replace('.', '', $diag['Diag_pCode']),
				'sickName' => $diag['Diag_pName'],
				'diagnosisTypeIdCode' => 300,
				'diagnosisTypeName' => 'Предварительный',
				'diagTypeIdCode' => 200,
				'diagTypeName' => 'Основной',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => ''
			);
		}

		//Уточняющий диагноз
		if (!empty($data['Diag_cid'])) {
			$diag_cid = $this->getFirstRowFromQuery("select Diag_Code,Diag_Name from v_Diag (nolock) where Diag_id = :Diag_id",['Diag_id'=>$data['Diag_cid']]);
			$diag_list[] = array(
				'sickIdCode' => str_replace('.', '', $diag_cid['Diag_Code']),
				'sickName' => $diag_cid['Diag_Name'],
				'diagnosisTypeIdCode' => 300,
				'diagnosisTypeName' => 'Предварительный',
				'diagTypeIdCode' => 800,
				'diagTypeName' => 'Уточняющее',
				'traumaTypeIdCode' => '',
				'traumaTypeName' => ''
			);
		}

		// Прочие
		$query = "
			select 
				Diag.Diag_Code,
				Diag.Diag_Name,
				DT.p_publCod as diagTypeIdCode,
				DT.p_nameRU as diagTypeName
			from v_EvnDiagPS EDPS with (nolock)
				left join Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
				left join r101.hDiagTypeLink DTL with (nolock) on DTL.DiagSetClass_id = EDPS.DiagSetClass_id
				left join r101.hDiagType DT with (nolock) on DT.p_ID = DTL.p_ID
			where EDPS.EvnDiagPS_pid = :EvnPS_id
		";
		$sop = $this->queryResult($query, array(
			'EvnPS_id' => $data['EvnPS_id']
		));
		foreach($sop as $diag) {
			$diag_list[] = array(
				'sickIdCode' => str_replace('.', '', $diag['Diag_Code']),
				'sickName' => $diag['Diag_Name'],
				'diagnosisTypeIdCode' => 200,
				'diagnosisTypeName' => 'Направительный',
				'diagTypeIdCode' => $diag['diagTypeIdCode'],
				'diagTypeName' => $diag['diagTypeName'],
				'traumaTypeIdCode' => '',
				'traumaTypeName' => ''
			);
		}
		return $diag_list;
	}

	/**
	 * Список услуг по КВС
	 */
	function getUsedServices($data) {
		/*$query = "
			select 
				uc.UslugaComplex_Code as serviceIdCode,
				ISNULL(EUC.EvnUslugaCommon_KolVo, 1) as serviceCount
			from v_EvnSection es (nolock)
				inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = es.EvnSection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id
			where
				ES.EvnSection_pid = :EvnPS_id
		";*/
		$query = "
			select top 100 
				EUC.EvnUslugaCommon_Kolvo as serviceCount,
				UC.UslugaComplex_Code as serviceIdCode,
				UC.UslugaComplex_Name as serviceName
			from v_EvnUsluga ES with (nolock)
				inner join v_EvnUslugaCommon EUC with (nolock) on EUC.EvnUslugaCommon_id = ES.EvnUsluga_id
				inner join UslugaComplex UC with (nolock) on UC.UslugaComplex_id = ES.UslugaComplex_id
			where ES.EvnUsluga_pid = :EvnPS_id and ES.EvnClass_SysNick = 'EvnUslugaCommon'
		";
		return $this->queryResult($query, array(
			'EvnPS_id' => $data['EvnPS_id']
		));
	}

	/**
	 * Список опер. услуг по КВС
	 */
	function getUsedSurgProces($data) {
		/*$query = "
			select 
				uc.UslugaComplex_Code as surgProcIdCode,
				ISNULL(euo.EvnUslugaOper_KolVo, 1) as surgProcCount
			from v_EvnSection es (nolock)
				inner join v_EvnUslugaOper euo (nolock) on euo.EvnUslugaOper_pid = es.EvnSection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euo.UslugaComplex_id
			where
				ES.EvnSection_pid = :EvnPS_id
		";*/
		$query = "
			select top 100 
				EUO.EvnUslugaOper_Kolvo as surgProcCount,
				UC.UslugaComplex_Code as surgProcIdCode,
				UC.UslugaComplex_Name as surgProcName
			from v_EvnUsluga ES with (nolock)
				inner join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = ES.EvnUsluga_id
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = ES.UslugaComplex_id
			where ES.EvnUsluga_pid = :EvnPS_id and ES.EvnClass_SysNick = 'EvnUslugaOper'
			";
		return $this->queryResult($query, array(
			'EvnPS_id' => $data['EvnPS_id']
		));
	}
	
	/**
	 * Сохранение плановой даты
	 */
	function SaveHospitalPlanningDate($data) {

		$params = array( 'EvnDirection_id' => $data['EvnDirection_id'] );

		if (isset($data['Evn_id']) && !empty($data['Evn_id'])) {
			$params = array( 'EvnDirection_id' => $data['Evn_id'] );
		}
		
		$query = "			
			select top 1
				edl.Referral_id
				--convert(varchar(19), ed.EvnDirection_setDate, 126) as EvnDirection_setDate
			from
				v_EvnDirection ed (nolock)
				inner join r101.EvnDirectionLink edl (nolock) on edl.EvnDirection_id = ed.EvnDirection_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		";
		$resp = $this->queryResult($query, $params);
		foreach ($resp as $response) {
			try {
				$this->SaveHospitalPlanningDateSend($response['Referral_id'],$data['EvnPS_setDate']);
			} catch (Exception $e) {
				/*if (!empty($_REQUEST['getDebug'])) {
					var_dump($e);
				}*/
				// падать не будем, просто пишем в лог инфу и идем дальше
				$this->textlog->add("SaveHospitalPlanningDate error: code: " . $e->getCode() . " message: " . $e->getMessage());
				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => $e->getMessage() . " (EvnDirection_id={$response['EvnDirection_id']})",
					'pmUser_id' => 1
				));
			}
		}
	}
	
	/**
	 * Сохранение плановой даты: отправка
	 */
	function SaveHospitalPlanningDateSend($Referral_id,$EvnPS_setDate) {
		$this->textlog->add("saveHospitalPlanningDate: ".$Referral_id);
		
		$params = array(
			'recordID' => $Referral_id,
			'hospitalPlanningDate' => $EvnPS_setDate
		);

		$response = $this->exec('/referrals/saveHospitalPlanningDate', 'post', $params);
		//$this->saveSyncObject('EvnDirection', $data['EvnDirection_id'], null);
	}
	
	/**
	 * Подтверждение госпитализации
	 */
	function SaveHospitalConfirm($data) {

		$filter = "";

		if (!empty($this->_hoConfig['Lpu_ids'])) {
			$filter .= " and ed.Lpu_id IN ('".implode("','", $this->_hoConfig['Lpu_ids'])."')";
			$filter .= " and eps.Lpu_id IN ('".implode("','", $this->_hoConfig['Lpu_ids'])."')";
		}

		if (!empty($data['Evn_id'])) {
			$queryParams = array(
				'Evn_id' => $data['Evn_id']
			);
			$filter .= " and ed.EvnDirection_id = :Evn_id";
		}

		if (isset($data['EvnPS_id']) && !empty($data['EvnPS_id'])) {
			$queryParams = array(
				'EvnPS_id' => $data['EvnPS_id']
			);
			$filter .= " and eps.EvnPS_id = :EvnPS_id";
		}
		
		// направления, отправленные ранее в saveReferral, связанные с КВС
		$query = "			
			declare @yesterday date = DATEADD(day, -3, dbo.tzGetdate()),
					@curDT datetime = dbo.tzGetdate();
			select 
				eps.EvnPS_id,
				eps.Lpu_id,
				isnull(edl.Referral_id,edlbg.referralId) as Referral_id,
				ed.EvnDirection_id,
				ls.LpuSectionBedProfile_id,
				eps.PayType_id,
				convert(varchar(19), eps.EvnPS_setDT, 126) as EvnPS_setDate,
				eps.EvnPS_NumCard,
				diag.Diag_Code,
				eps.PrehospTrauma_id,
				COALESCE(gph.FPID, LSFPIDL_eid.FPID, lspFirstNotPriem.FPID) as RefFPID,
				eps.PrehospDirect_id,
				eps.PrehospArrive_id,
				gph.PersonalID,
				gph.PostID,
				isnull(tsf.p_publCod, 5000) as FinanceSourcePublicCode,
				--isnull(ipht.p_ID, 1) as StacType,
				case 
					when dbo.Age2(ps.Person_BirthDay, @curDT) < 1 and esfirst.EvnSection_IsAdultEscort = 2 then '300'
					when esfirst.EvnSection_IsAdultEscort = 2 then '200'
					else '100'
				end as hospitalizedIdCode,
				lu.LpuUnitType_id,
				case
					when lu.LpuUnitType_id = 7 then 3
					else isnull(ipht.p_ID, 1)
				end as StacType,
				ed.DirType_id,
				ELA.EvnLinkAPP_StageRecovery,
				ph.PurposeHospital_Code,
				ddiag.Diag_Name as DDiag_Name,
				ddiag.Diag_Code as DDiag_Code,
				cdiag.Diag_Name as CDiag_Name,
				cdiag.Diag_Code as CDiag_Code
			from
				v_EvnPS eps (nolock)
				left join v_EvnDirection ed (nolock) on eps.EvnDirection_id = ed.EvnDirection_id
				left join r101.EvnDirectionLink edl (nolock) on edl.EvnDirection_id = ed.EvnDirection_id
				left join r101.EvnDirectionLinkBG edlbg (nolock) on edlbg.EvnPS_id = eps.EvnPS_id
				left join r101.EvnPSLink epl (nolock) on epl.EvnPS_id = eps.EvnPS_id
				inner join v_PersonState ps (nolock) on ps.Person_id = eps.Person_id
				left join v_Diag diag with (nolock) on diag.Diag_id = eps.Diag_id
				outer apply (
					select top 1 ES.*
					from v_EvnSection ES with (nolock)
					where ES.EvnSection_pid = eps.EvnPS_id
					order by ES.EvnSection_Index desc
				) eslast
				outer apply (
					select top 1 ES.*
					from v_EvnSection ES with (nolock)
					where ES.EvnSection_pid = eps.EvnPS_id
					order by ES.EvnSection_Index asc
				) esfirst
				left join v_MedStaffFact MSFesfirst with (nolock) on MSFesfirst.MedPersonal_id = esfirst.MedPersonal_id and MSFesfirst.LpuSection_id = esfirst.LpuSection_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = eslast.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join r101.v_GetPersonalHistoryWP gphwpes with (nolock) on gphwpes.WorkPlace_id = eslast.MedStaffFact_id
				left join r101.v_GetPersonalHistory gphes (nolock) on gphes.GetPersonalHistory_id = gphwpes.GetPersonalHistory_id
				left join r101.LpuSectionFPIDLink LSFPIDL_eid on LSFPIDL_eid.LpuSection_id = eps.LpuSection_eid
				left join r101.EvnLinkAPP ELA on ELA.Evn_id = ed.EvnDirection_id
				left join r101.v_PurposeHospital ph (nolock) on ph.PurposeHospital_id = ELA.PurposeHospital_id
				left join v_Diag ddiag with (nolock) on ddiag.Diag_id = ed.Diag_id
				left join v_Diag cdiag with (nolock) on cdiag.Diag_id = ELA.Diag_cid
				outer apply (
					select top 1
						gpw.PostID,
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id in (MSFesfirst.MedStaffFact_id, EPS.MedStaffFact_pid)
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				
				left join r101.GetBedEvnLink gbelps (nolock) on gbelps.Evn_id = eps.EvnPS_id
				left join r101.GetBedEvnLink gbeles (nolock) on gbeles.Evn_id = esfirst.EvnSection_id
				left join r101.GetBed gb (nolock) on gb.GetBed_id = isnull(gbelps.GetBed_id, gbeles.GetBed_id)
				left join r101.hTypSrcFin tsf (nolock) on tsf.p_ID = gb.TypeSrcFin
				
				left join r101.GetBedEvnLink edgbel (nolock) on edgbel.Evn_id = ed.EvnDirection_id
				left join r101.GetBed edgb (nolock) on edgb.GetBed_id = edgbel.GetBed_id
				left join r101.hInPatientHelpTypes ipht (nolock) on ipht.p_ID = gb.StacType
				
				outer apply (
					select top 1
						FPID
					from v_EvnSection ES with (nolock)
						left join r101.LpuSectionFPIDLink LSFPIDL (nolock) on LSFPIDL.LpuSection_id = ES.LpuSection_id
					where
						ES.EvnSection_pid = EPS.EvnPS_id and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					order by ES.EvnSection_setDT asc
				) lspFirstNotPriem -- FPID первого профильного (не приемного отделения)
			where
				lu.LpuUnitType_id in (1,6,7,9)
				--and pt.PayType_SysNick in ('bud', 'Resp')
				--and pt.PayType_SysNick in ('bud', 'Resp', 'selo')
				and epl.EvnPSLink_id is null
				and (edl.EvnDirectionLink_id is not null or edlbg.EvnDirectionLinkBG_id is not null)
				and cast(eps.EvnPS_setDT as date) >= @yesterday
				{$filter}
		";
		$resp = $this->queryResult($query, $data);
		foreach ($resp as $response) {
			try {
				// перед отправкой SaveHospitalConfirm нужно отправить SaveHospitalPlanningDate
				$this->SaveHospitalPlanningDate($response);

				$response['rehabilitationTypeIdCode'] = '';
				if (!empty($response['DirType_id']) && !empty($response['EvnLinkAPP_StageRecovery']) && $response['DirType_id'] == 4){
					if ($response['EvnLinkAPP_StageRecovery'] == 2) $response['rehabilitationTypeIdCode'] = '100';
					if ($response['EvnLinkAPP_StageRecovery'] == 3) $response['rehabilitationTypeIdCode'] = '200';
				}
				
				$this->SaveHospitalConfirmSend($response);
			} catch (Exception $e) {
				/*if (!empty($_REQUEST['getDebug'])) {
					var_dump($e);
				}*/
				// падать не будем, просто пишем в лог инфу и идем дальше
				$this->textlog->add("SaveHospitalConfirm error: code: " . $e->getCode() . " message: " . $e->getMessage());
				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => $e->getMessage() . " (EvnDirection_id={$response['EvnDirection_id']})",
					'pmUser_id' => 1
				));
			}
		}
	}
	
	/**
	 * Подтверждение госпитализации: отправка
	 */
	function SaveHospitalConfirmSend($data) {
		$this->textlog->add("saveHospitalConfirm: ".$data['Referral_id']);
		
		$refTypeIdCode = $this->getSyncSpr('hTreatmentType', $data['PrehospDirect_id'], true);
		if (empty($refTypeIdCode)) {
			$refTypeIdCode = ($data['PrehospArrive_id'] == 1) ? 200 : 100;
		}
		$OrgScheme = $this->getOrgSchemeInfo($data['Lpu_id']);
		//$OrgScheme['funcStructureOrgID'] = $data['RefFPID'];

		if ($data['PayType_id'] == 246) {
			$data['FinanceSourcePublicCode'] = 7000;
		} elseif ($data['PayType_id'] == 248) {
			$data['FinanceSourcePublicCode'] = 7200;
		} elseif ($data['PayType_id'] == 152) {
			$data['FinanceSourcePublicCode'] = 800;
		}

		$params = array(
			'externalSystemCode' => 'kazmedinform',
			'referralId' => $data['Referral_id'],
			'hospitalDate' => $data['EvnPS_setDate'],
			'hospitalHistoryNumber' => $data['EvnPS_NumCard'],
			'refTypeIdCode' => !empty($refTypeIdCode) ? $refTypeIdCode : 100,
			'diagnosesList' => $this->getDiagnosesList($data),
			'rehabilitationTypeIdCode' => $data['rehabilitationTypeIdCode'],
			'note' => '',
			'hospitalizedIdCode' => $data['hospitalizedIdCode'],
			'funcStructureId' => $data['RefFPID'],
			'financeSourceIdCode' => $data['FinanceSourcePublicCode'],
			'ad' => '0/0',
			'temperature' => 0,
			'doctorPostId' => $data['PostID'],
			'usedServices' => $this->getUsedServices($data),
			'usedSurgProces' => $this->getUsedSurgProces($data),
			'usedDrugs' => array(),
			//'hasIndicationsForHospital' => '',
			'helpType' => $data['StacType'],
			'directionsValidateExceptions' => array(),
			'orgScheme' => $OrgScheme,
			//'areaID' => $this->getAreaID($data['Lpu_id'])
		);

		if ($data['PurposeHospital_Code']==600) {
			if(!empty($data['DDiag_Code'])) {
				$params['diagnosesList'][] = [
					"sickIdCode" => str_replace('.', '', $data['DDiag_Code']),
					"sickName" => $data['DDiag_Name'],
					"diagnosisTypeIdCode" => "500",
					"diagnosisTypeName" => "Заключительный",
					"diagTypeIdCode" => "200",
					"diagTypeName" => "Основное",
					"traumaTypeIdCode" => "",
					"traumaTypeName" => ""
				];
			}

			if(!empty($data['CDiag_Code'])) {
				$params['diagnosesList'][] = [
					"sickIdCode" => str_replace('.', '', $data['CDiag_Code']),
					"sickName" => $data['CDiag_Name'],
					"diagnosisTypeIdCode" => "200",
					"diagnosisTypeName" => "Направительный",
					"diagTypeIdCode" => "800",
					"diagTypeName" => "Уточняющее",
					"traumaTypeIdCode" => "",
					"traumaTypeName" => ""
				];
			}
		}

		$response = $this->exec('/hospitalizations/saveHospitalConfirm', 'post', $params);

		if (count($response) && !empty($response->id)) {
			$query = "
				declare
					@EvnPSLink_id bigint = :EvnPSLink_id,
					@ErrCode int,
					@ErrMsg varchar(4000);
				exec r101.p_EvnPSLink_ins
					@EvnPSLink_id = @EvnPSLink_id output,
					@EvnPS_id = :EvnPS_id,
					@Hospitalization_id = :Hospitalization_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @EvnPSLink_id as EvnPSLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$this->db->query($query, array(
				'EvnPSLink_id' => null,
				'EvnPS_id' => $data['EvnPS_id'],
				'Hospitalization_id' => $response->id,
				'pmUser_id' => 1
			));
		}

		//$this->saveSyncObject('EvnDirection', $data['EvnDirection_id'], null);
	}
	
	/**
	 * Найти направления, соответствующие критериям поиска
	 */
	function searchReferrals($data) {
		
		$params = array(
			'sortBy' => null,
			'patientIINOrFIO' => null,
			'referralCode' => null, 
			'dateFrom' => $data['dateFrom'],
			'dateTo' => $data['dateTo'],
			'orgSchemeDirect' => $data['orgHealthCareID'],
			'orgSchemeRef' => $data['orgHealthCareRID'],
			'bedProfileId' => null,
			'withoutHospPlanDate' => null,
			'withHospPlanDate' => null,
			'hospPlanDate' => null,
			'searchHospitalized' => null,
			'searchRefusal' => null,
			'searchDirections' => true,
			'searchKC' => null,
			'searchDS' => null,
			'id' => null,
			'patientID' => null,
			'patientRpnID' => $data['patientRpnID'],
			'exceptIdentifiers' => null,
			'showShortOrgHealthCareNames' => null,
			'recCount' => null,
			'strtsWith' => null,
			'byLastUpdateDate' => null,
		);

		$response = $this->exec('/referrals/searchReferrals', 'post', $params);
		
		if (count($response) && !empty($response->id)) {
			$this->saveReferralsFromHO($response,$data);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Сохраняет найденные направления
	 */
	function saveReferralsFromHO($response,$data) {
		
		$query = "
			declare
				@EvnDirectionLinkBG_id bigint = :EvnDirectionLinkBG_id,
				@ErrCode int,
				@ErrMsg varchar(4000);
			exec r101.p_EvnDirectionLinkBG_ins
				@EvnDirectionLinkBG_id = @EvnDirectionLinkBG_id output,
				@EvnDirection_id = :EvnDirection_id,
				@EvnPS_id = :EvnPS_id,
				@HospitalType_id = :HospitalType_id,
				@orgHealthCare_sID = :orgHealthCare_sID,
				@orgHealthCare_dID = :orgHealthCare_dID,
				@hospitalDate = :hospitalDate,
				@hospitalPlanDate = :hospitalPlanDate,
				@polyclinicPlanDate = :polyclinicPlanDate,
				@currentPlanDate = :currentPlanDate,
				@rpnID = :rpnID,
				@personin = :personin,
				@referralId = :referralId,
				@hospitalCode = :hospitalCode,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @EvnDirectionLinkBG_id as EvnDirectionLinkBG_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$this->db->query($query, array(
			'EvnDirectionLinkBG_id' => null,
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnPS_id' => $data['EvnPS_id'],
			'HospitalType_id' => $data['HospitalType_id'],
			'orgHealthCare_sID' => $data['orgHealthCare_sID'],
			'orgHealthCare_dID' => $data['orgHealthCare_dID'],
			'hospitalDate' => $response->hospitalDate,
			'hospitalPlanDate' => $response->hospitalPlanDate,
			'polyclinicPlanDate' => $response->polyclinicPlanDate,
			'currentPlanDate' => $response->currentPlanDate,
			'rpnID' => $response->PersonInfo->rpnID,
			'personin' => $response->PersonInfo->personin,
			'referralId' => $response->id,
			'hospitalCode' => $response->hospitalCode,
			'pmUser_id' => 1
		));
	}

	/**
	 * Отправка всего
	 */
	function syncAll($data) {
		$this->data = $data;
		$this->load->model('ServiceList_model');
		$ServiceList_id = 8;
		$begDT = date('Y-m-d H:i:s');
		$resp = $this->ServiceList_model->saveServiceListLog(array(
			'ServiceListLog_id' => null,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListResult_id' => 2,
			'pmUser_id' => 1
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
		}
		$this->ServiceListLog_id = $resp[0]['ServiceListLog_id'];

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {			

			// Добавление направления на госпитализацию
			if(empty($data['mt']) || $data['mt'] == 1)
				$this->saveReferral($data);
			
			// Подтверждение госпитализации
			if($data['mt'] == 2)
				$this->SaveHospitalConfirm($data);
			
			// Сохранение отказа из направления
			if(empty($data['mt']) || $data['mt'] == 3)
				$this->SaveHospitalRefuse($data);

			// Сохранение факта госпитализации/отказа
			if(empty($data['mt']) || $data['mt'] == 4)
				$this->SaveHospitalOrReject($data);

			// Сохранение плановой даты
			if($data['mt'] == 5)
				$this->SaveHospitalPlanningDate($data);

			$endDT = date('Y-m-d H:i:s');
			$resp = $this->ServiceList_model->saveServiceListLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 1,
				'pmUser_id' => 1
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
		} catch(Exception $e) {
			/*if (!empty($_REQUEST['getDebug'])) {
				var_dump($e);
			}*/
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $e->getMessage(),
				'pmUser_id' => 1
			));

			$endDT = date('Y-m-d H:i:s');
			$this->ServiceList_model->saveServiceListLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 3,
				'pmUser_id' => 1
			));
		}
		restore_exception_handler();
	}
	/**
	 * Получение данных об организации
	 */
	function getEvnDirectionFiles($data) {

		$Evn_id = null;

		if (!empty($data['EvnDirection_id'])) {
			$Evn_id = $data['EvnDirection_id'];
		} elseif (!empty($data['EvnPS_id'])) {
			$Evn_id = $data['EvnPS_id'];
		}

		if (empty($Evn_id) && empty($data['EvnDirectionFiles'])) {
			return array();
		}

		if (empty($data['EvnDirectionFiles'])) {
			$query = "
				SELECT
					emd.EvnMediaData_FileName,
					emd.EvnMediaData_FilePath
				from
					v_EvnMediaData emd (nolock)
				where
					Evn_id = :EvnDirection_id
			";

			$files = $this->queryResult($query, array(
				'EvnDirection_id' => $Evn_id
			));
		} else {
			$files = [];
			foreach ($data['EvnDirectionFiles'] as $file) {
				$files[] = [
					'EvnMediaData_FileName' => $file->EvnMediaData_FileName,
					'EvnMediaData_FilePath' => $file->EvnMediaData_FilePath
				];
			}
		}

		$formatFilesArr = array();
		if (is_array($files)) {
			foreach ($files as $key => $file){
				if (file_exists(EVNMEDIAPATH . $file['EvnMediaData_FilePath'])) {
					$formatFilesArr[$key]['file'] = base64_encode(file_get_contents(EVNMEDIAPATH . $file['EvnMediaData_FilePath']));
					$formatFilesArr[$key]['name'] = $file['EvnMediaData_FileName'];
				}
			}
		}

		return $formatFilesArr;
	}
}