<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AisPolka_model - модель
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
class AisPolka_model extends swModel {
	protected $_config = array();
	protected $_soapClients = array();
	protected $_syncObjectList = array();
	protected $_syncSprList = array(); // список синхронизированных справочников
	protected $_syncSprTables = array(); // список таблиц для синхронизации справочников
	protected $_ticket = ""; // токен авторизованного пользователя
	protected $lpu259list; // инициализируется в конструкторе. список id МО для выгрузки в формате 25-9,
	protected $lpu255and259list; // инициализируется в конструкторе. список id МО для выгрузки в формате 25-5 и 25-9

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		ini_set("default_socket_timeout", 600);

		$this->load->library('textlog', array('file'=>'AisPolka_'.date('Y-m-d').'.log'));

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('AispKZ');

		$this->_config = $this->config->item('AisPolka');

		$AisPolkaEvnPLloadConfig = $this->config->item('AisPolkaEvnPLsync');

		$this->lpu259list = $AisPolkaEvnPLloadConfig['lpu259list'];
		$this->lpu255and259list = $AisPolkaEvnPLloadConfig['lpu255and259list'];
		
		ini_set('precision', '24');
	}

	/**
	 * Выполнение запросов к сервису РПН и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceKZ', $this->config->item('AisPolka'), 'swserviceaispolka');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->swserviceaispolka->data($method, $type, $data);
		$this->textlog->add("result: ".print_r($result,true));

		/*
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса РПН: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса РПН: '.$result->ExceptionMessage
			);
		}
		*/

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
	function getSyncSpr($table, $id, $allowBlank = false) {
		if (empty($id)) {
			return null;
		}

		// ищем в памяти
		if (isset($this->_syncSprList[$table]) && isset($this->_syncSprList[$table][$id])) {
			return $this->_syncSprList[$table][$id];
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
			$ourTable = $this->_syncSprTables[$table]['TableName'];
			$advancedKey = preg_replace('/.*\./', '', "{$ourTable}_id");

			$idField = "link.P_id";
			if ($table == 'sp_fin') {
				$idField = "link.id";
			}

			// ищем в бд
			$query = "
				select top 1
					{$idField} as id
				from
					{$mapTable} link with (nolock) 
				where
					link.{$advancedKey} = :{$advancedKey} 
			";

			$resp = $this->queryResult($query, array(
				$advancedKey => $id
			));

			if (!empty($resp[0]['id'])) {
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
	 * Получение данных ТАП
	 */
	function getEvnPLInfo($data) {
		$params = array('EvnPL_id' => $data['EvnPL_id']);

		$query = "				
			select top 1
				EPL.EvnPL_id,
				EPL.EvnPL_NumCard,
				gph.MOID,
				gph.MedCode,
				vt.VizitType_Code,
				convert(varchar(10), epl.EvnPL_setDT, 120) as EvnPL_setDT,
				convert(varchar(10), epl.EvnDirection_setDT, 120) as EvnDirection_setDT,
				gph_did.MOID as NAP_MOID,
				gph_did.PersonalID as NAP_PersonalID,
				gph.PersonalID,
				EVPLFIRST.PayType_id,
				EPL.Lpu_id,
				PT.PayType_Code,
				sc.SocStatus_Code,
				p.BDZ_id,
				RTRIM(RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Firname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Secname, ''))) as Person_Fio,
				ps.Person_Inn as IIN,
				d.Diag_Code,
				dt.DeseaseType_SysNick,
				uc.UslugaComplex_Code,
				evplfirst.VizitClass_id,
				tc.TreatmentClass_Code,
				st.ServiceType_SysNick,
				EPL.PrehospTrauma_id,
				ed.EvnDirection_IsReceive,
				ed.Lpu_sid,
				ed.EvnDirection_id,
				ed.EvnDirection_Num,
				gph_zav.PersonalID as ZavCode,
				gph_zav.FPID as OtdIdZav,
				agm.ID as ATTACH_MOID,
				ldc.LpuDispContractUslugaComplexLink_id,
				l.Lpu_Nick,
				convert(varchar(10), ed.EvnDirection_setDT, 120) as DtAgreement
			from
				v_EvnPL EPL with (nolock)
				cross apply (
					select top 1
						evpl.EvnVizitPL_id,
						evpl.MedStaffFact_id,
						evpl.VizitType_id,
						evpl.PayType_id,
						evpl.Diag_id,
						evpl.DeseaseType_id,
						evpl.UslugaComplex_id,
						evpl.VizitClass_id,
						evpl.TreatmentClass_id,
						evpl.ServiceType_id
					from
						v_EvnVizitPL evpl with (nolock)
					where
						EVPL.EvnVizitPL_pid = EPL.EvnPL_id
					order by
						EVPL.EvnVizitPL_setDT asc
				) EVPLFIRST
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = EPL.EvnDirection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = evplfirst.UslugaComplex_id
				left join v_Diag d (nolock) on d.Diag_id = evplfirst.Diag_id
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = evplfirst.DeseaseType_id
				left join v_VizitType vt (nolock) on vt.VizitType_id = evplfirst.VizitType_id
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = evplfirst.TreatmentClass_id
				left join v_ServiceType st (nolock) on st.ServiceType_id = evplfirst.ServiceType_id
				left join v_PersonState ps (nolock) on ps.Person_id = epl.Person_id
				left join r101.GetMO agm (nolock) on agm.Lpu_id = ps.Lpu_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				left join v_Person p (nolock) on p.Person_id = epl.Person_id
				left join PayType PT (nolock) on PT.PayType_id = EVPLFIRST.PayType_id
				left join v_Lpu l (nolock) on l.Lpu_id = EPL.Lpu_id
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.FPID,
						gpw.MOID,
						gm.MedCode
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = EVPLFIRST.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = isnull(ed.MedStaffFact_id,EPL.MedStaffFact_did)
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph_did
				
				outer apply (
					select top 1
						MedStaffFact_id
					from
						v_MedStaffFact msfz (nolock)
					where
						msfz.MedPersonal_id = ed.MedPersonal_zid and msfz.LpuSection_id = ed.LpuSection_id
				) msfz
				
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID,
						gpw.MOID,
						gm.MedCode
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = msfz.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph_zav
				
				outer apply (
					select top 1 ldcuc.LpuDispContractUslugaComplexLink_id
					from v_LpuDispContract ldc (nolock)
					inner join LpuDispContractUslugaComplexLink ldcuc (nolock) on ldcuc.LpuDispContract_id = ldc.LpuDispContract_id
					where (
						(ldc.Lpu_id = ed.Lpu_sid and ldc.Lpu_oid = ed.Lpu_did and ldc.SideContractType_id = 1) or
						(ldc.Lpu_id = ed.Lpu_did and ldc.Lpu_oid = ed.Lpu_sid and ldc.SideContractType_id = 2)
					) and
					ldcuc.UslugaComplex_id = evplfirst.UslugaComplex_id and 
					epl.EvnPL_setDT between ldc.LpuDispContract_setDate and ISNULL(ldc.LpuDispContract_disDate, '2099-01-01')
				) ldc
			where
				EPL.EvnPL_id = :EvnPL_id
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные ТАП', 400);
		}
	}

	/**
	 * Получение данных параклинической услуги
	 */
	function getEvnUslugaParInfo($data) {
		$params = array('EvnUslugaPar_id' => $data['EvnUslugaPar_id']);

		$query = "				
			select top 1
				EUP.EvnUslugaPar_id,
				EUP.Person_id,
				EUP.Lpu_id,
				gph.MOID,
				gph.MedCode,
				convert(varchar(10), eup.EvnUslugaPar_setDT, 120) as EvnUslugaPar_setDT,
				convert(varchar(10), ed.EvnDirection_setDT, 120) as EvnDirection_setDT,
				coalesce(eup.EvnUslugaPar_NumUsluga, eup.EvnUslugaPar_Kolvo, 1) as EvnUslugaPar_Kolvo,
				gph_did.MOID as NAP_MOID,
				gph_did.PersonalID as NAP_PersonalID,
				gph.SpecialityID,
				gph.FPID,
				gph.PersonalID,
				EUP.PayType_id,
				sc.SocStatus_Code,
				p.BDZ_id,
				RTRIM(RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Firname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Secname, ''))) as Person_Fio,
				ps.Person_Inn as IIN,
				d.Diag_Code,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				agm.ID as ATTACH_MOID,
				ed.EvnDirection_IsReceive,
				ed.Lpu_sid,
				ed.EvnDirection_id,
				ed.EvnDirection_Num,
				PT.PayType_Code,
				case 
					when ppsoc.PrivilegeType_id is not null then 1
					when eugobmp.EvnUsluga_id is not null then 1
				else 0 end as SocialDisadv,
				isnull(ppsoc.SubCategoryPrivType_Code, 900) as SocDisGroupId,
				gph_zav.PersonalID as ZavCode,
				gph_zav.FPID as OtdIdZav,
				ldc.LpuDispContractUslugaComplexLink_id,
				l.Lpu_Nick,
				convert(varchar(10), ed.EvnDirection_setDT, 120) as DtAgreement
			from
				v_EvnUslugaPar EUP with (nolock)
				inner join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = eup.EvnDirection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eup.UslugaComplex_id
				left join v_Diag d (nolock) on d.Diag_id = ISNULL(eup.Diag_id, ed.Diag_id)
				left join v_PersonState ps (nolock) on ps.Person_id = eup.Person_id
				left join r101.GetMO agm (nolock) on agm.Lpu_id = ps.Lpu_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				left join v_Person p (nolock) on p.Person_id = eup.Person_id
				
				left join PayType PT (nolock) on PT.PayType_id = EUP.PayType_id
				left join v_PersonPrivilege PP (nolock) on PP.Person_id = eup.Person_id
				left join r101.PersonPrivilegeSubCategoryPrivType PPSP (nolock) on PPSP.PersonPrivilege_id = PP.PersonPrivilege_id  
				left join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSP.SubCategoryPrivType_id
				left join v_Lpu l (nolock) on l.Lpu_id = EUP.Lpu_id
				
				outer apply (
					select top 1
						MedStaffFact_id
					from
						v_MedStaffFact msfz (nolock)
					where
						msfz.MedPersonal_id = ed.MedPersonal_zid and msfz.LpuSection_id = ed.LpuSection_id
				) msfz
				outer apply (
					select top 1
						MedStaffFact_id
					from
						v_MedStaffFact msfs (nolock)
					where
						msfs.MedPersonal_id = eup.MedPersonal_sid and msfs.LpuSection_id = eup.LpuSection_uid
				) msfs
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID,
						gpw.MOID,
						gm.MedCode
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = ISNULL(eup.MedStaffFact_id, msfs.MedStaffFact_id)
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = ed.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph_did
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID,
						gpw.MOID,
						gm.MedCode
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = msfz.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph_zav
				outer apply (
					select top 1 
						eu.EvnUsluga_id
					from 
						v_EvnUsluga eu (nolock) 
					inner join UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
					inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where 
						eu.EvnUsluga_id = eup.EvnUslugaPar_id and 
						ucat.UslugaComplexAttributeType_SysNick = 'gobmp' and 
						uca.UslugaComplexAttribute_Int = 2
				) eugobmp
				outer apply (
					select top 1 
						pt.PrivilegeType_id,
						SCPT.SubCategoryPrivType_id,
						SCPT.SubCategoryPrivType_Code
					from 
						v_PersonPrivilege pp (nolock) 
						inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						inner join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT (nolock) on PPSCPT.PersonPrivilege_id = pp.PersonPrivilege_id
						inner join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
					where pp.Person_id = ps.Person_id and pt.PrivilegeType_Code = '18'
				) ppsoc
				outer apply (
					select top 1 ldcuc.LpuDispContractUslugaComplexLink_id
					from v_LpuDispContract ldc (nolock)
					inner join LpuDispContractUslugaComplexLink ldcuc (nolock) on ldcuc.LpuDispContract_id = ldc.LpuDispContract_id
					where (
						(ldc.Lpu_id = ed.Lpu_sid and ldc.Lpu_oid = ed.Lpu_did and ldc.SideContractType_id = 1) or
						(ldc.Lpu_id = ed.Lpu_did and ldc.Lpu_oid = ed.Lpu_sid and ldc.SideContractType_id = 2)
					) and
					ldcuc.UslugaComplex_id = ed.UslugaComplex_did and 
					EUP.EvnUslugaPar_setDT between ldc.LpuDispContract_setDate and ISNULL(ldc.LpuDispContract_disDate, '2099-01-01')
				) ldc
			where
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные ТАП', 400);
		}
	}

	/**
	 * Метод для получения информации о стоматологическом ТАП
	 */
	function getEvnPLStomInfo($EvnPLStom_id)
	{
		$query = "
			select top 1
				EPLS.EvnPLStom_id,
				EPLS.EvnPLStom_NumCard,
				EPLS.Person_id,
				EPLS.Lpu_id,
				gph.MOID,
				gph.MedCode,
				convert(varchar(10), EPLS.EvnPLStom_setDT, 120) as EvnPLStom_setDT,
				convert(varchar(10), ED.EvnDirection_setDT, 120) as EvnDirection_setDT,
				gph.SpecialityID,
				gph.FPID,
				gph.PersonalID,
				EVPLSTOMFIRST.PayType_id,
				gph_did.MOID as NAP_MOID,
				gph_did.PersonalID as NAP_PersonalID,
				--sc.SocStatus_Code,
				p.BDZ_id,
				RTRIM(RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Firname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Secname, ''))) as Person_Fio,
				ps.Person_Inn as IIN,
				d.Diag_Code,
				uc.UslugaComplex_Code,
				--uc.UslugaComplex_Name,
				agm.ID as ATTACH_MOID,
				PT.PayType_Code,
				case 
					when ppsoc.PrivilegeType_id is not null then 1
					when eugobmp.EvnUsluga_id is not null then 1
				else 0 end as SocialDisadv,
				isnull(ppsoc.SubCategoryPrivType_Code, 900) as SocDisGroupId,
				ED.Lpu_sid,
				ED.EvnDirection_id,
				ED.EvnDirection_Num,
				ED.EvnDirection_IsReceive,
				gph_zav.PersonalID as ZavCode,
				gph_zav.FPID as OtdIdZav,
				ldc.LpuDispContractUslugaComplexLink_id,
				l.Lpu_Nick,
				convert(varchar(10), ed.EvnDirection_setDT, 120) as DtAgreement
			from
				v_EvnPLStom EPLS with (nolock)
			cross apply (
				select top 1
					--EVPLS.EvnVizitPL_id,
					EVPLS.MedStaffFact_id,
					EVPLS.VizitType_id,
					EVPLS.PayType_id,
					EVPLS.Diag_id,
					EVPLS.DeseaseType_id,
					EVPLS.UslugaComplex_id,
					EVPLS.VizitClass_id,
					EVPLS.TreatmentClass_id,
					EVPLS.ServiceType_id
				from
					v_EvnVizitPLStom EVPLS with (nolock)
				where
					EVPLS.EvnVizitPLStom_pid = EPLS.EvnPlStom_id
				order by
					EVPLS.EvnVizitPLStom_setDT asc
			) EVPLSTOMFIRST
			
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPLS.EvnDirection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = EVPLSTOMFIRST.UslugaComplex_id
				left join v_Diag d (nolock) on d.Diag_id = ISNULL(EPLS.Diag_id, ED.Diag_id)
				left join PayType PT (nolock) on PT.PayType_id = EVPLSTOMFIRST.PayType_id
				left join v_PersonState ps (nolock) on ps.Person_id = EPLS.Person_id
				left join v_Person p (nolock) on p.Person_id = EPLS.Person_id
				left join r101.GetMO agm (nolock) on agm.Lpu_id = ps.Lpu_id
				left join v_Lpu l (nolock) on l.Lpu_id = EPLS.Lpu_id
				
				--left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				
				--left join v_PersonPrivilege PP (nolock) on PP.Person_id = EPLS.Person_id
				--left join r101.PersonPrivilegeSubCategoryPrivType PPSP (nolock) on PPSP.PersonPrivilege_id = PP.PersonPrivilege_id  
				--left join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSP.SubCategoryPrivType_id
				outer apply (
					select top 1
						MedStaffFact_id
					from
						v_MedStaffFact msfz (nolock)
					where
						msfz.MedPersonal_id = ed.MedPersonal_zid and msfz.LpuSection_id = ed.LpuSection_id
				) msfz
				
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID,
						gpw.MOID,
						gm.MedCode
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = EVPLSTOMFIRST.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.MOID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = ed.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph_did
				
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID,
						gpw.MOID,
						gm.MedCode
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = msfz.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph_zav
				
				outer apply (
					select top 1 
						eu.EvnUsluga_id
					from 
						v_EvnUsluga eu (nolock) 
					inner join UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
					inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where
						eu.EvnUsluga_rid = EPLS.EvnPLStom_id AND 
						ucat.UslugaComplexAttributeType_SysNick = 'gobmp' and 
						uca.UslugaComplexAttribute_Int = 2
				) eugobmp
				outer apply (
					select top 1 
						pt.PrivilegeType_id,
						SCPT.SubCategoryPrivType_id,
						SCPT.SubCategoryPrivType_Code
					from 
						v_PersonPrivilege pp (nolock) 
						inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						inner join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT (nolock) on PPSCPT.PersonPrivilege_id = pp.PersonPrivilege_id
						inner join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
					where pp.Person_id = ps.Person_id and pt.PrivilegeType_Code = '18'
				) ppsoc
				
				outer apply (
					select top 1 ldcuc.LpuDispContractUslugaComplexLink_id
					from v_LpuDispContract ldc (nolock)
					inner join LpuDispContractUslugaComplexLink ldcuc (nolock) on ldcuc.LpuDispContract_id = ldc.LpuDispContract_id
					where (
						(ldc.Lpu_id = ed.Lpu_sid and ldc.Lpu_oid = ed.Lpu_did and ldc.SideContractType_id = 1) or
						(ldc.Lpu_id = ed.Lpu_did and ldc.Lpu_oid = ed.Lpu_sid and ldc.SideContractType_id = 2)
					) and
					ldcuc.UslugaComplex_id = EVPLSTOMFIRST.UslugaComplex_id and 
					EPLS.EvnPLStom_setDT between ldc.LpuDispContract_setDate and ISNULL(ldc.LpuDispContract_disDate, '2099-01-01')
				) ldc
			where
				EPLS.EvnPLStom_id = :EvnPLStom_id
		";


		$result = $this->getFirstRowFromQuery($query, array('EvnPLStom_id' => $EvnPLStom_id));



		if ( empty($result))
		{
			throw new Exception('Не удалось получить данные ТАП', 400);
		} else
		{
			return $result;
		}
	}

	/**
	 * Получние данных синхронизации объекта
	 */
	function getSyncObject($table, $id, $field = 'Object_id') {
		if (empty($id) || !in_array($field, array('Object_id','Object_sid'))) {
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
	 * Отправка направления в сервис
	 */
	function syncDirection($dirParams) {
		// ищем направление в сервисе
		// https://redmine.swan.perm.ru/issues/100901#note-349
		/*$params = json_encode(array(
			'Number' => $dirParams['Number'],
			"FromOrgId" => $dirParams['OrgFromCode'],
			"OrgId" => $dirParams['OrgToCode'],
			"IIN" => $dirParams['IIN']
		));

		$result = $this->exec('/directions/search', 'post', $params);
		if (!empty($result)) {
			// если направления нашлись, ищем среди них нужное
			if (!empty($result[0]->directions)) {
				foreach($result[0]->directions as $direction) {
					if (!empty($direction->services[0]) && trim($direction->services[0]->serviceTar->kod) == trim($dirParams['Services'][0]['TarUslKod'])) {
						return $direction->id; // нашли
					}
				}
			}
		}*/
		
		if($dirParams['OrgFromCode'] == $dirParams['OrgToCode']) {
			return false;
		}
		
		// проверяем, не передавали ли направление ранее
		$savedDirection = $this->getFirstRowFromQuery("select AISResponse_usid, AISResponse_uid from r101.AISResponse (nolock) where Evn_id = :Evn_id and AISResponse_usid is not null", array(
			'Evn_id' => $dirParams['EvnDirection_id']
		));
		
		if($savedDirection) {
			// имитируем ответ сервиса
			$direction = new stdClass();
			$direction->id = $savedDirection['AISResponse_usid'];
			$direction->serviceList = array(new stdClass());
			$direction->serviceList[0]->item1 = !empty($savedDirection['AISResponse_uid']) ? $savedDirection['AISResponse_uid'] : null;
			return $direction;
		}

		// если направление не нашли, отправляем его в сервис
		$paramsArr = array(
			'OrgFromCode' => $dirParams['OrgFromCode'],
			'OrgToCode' => $dirParams['OrgToCode'],
			'DtSent' => $dirParams['DtSent'],
			'FinID' => $dirParams['FinID'],
			'SocialDisadv' => $dirParams['SocialDisadv'],
			'SocDisGroupId' => $dirParams['SocDisGroupId'],
			'DoctorCode' => $dirParams['DoctorCode'],
			'HumanRpnId' => $dirParams['HumanRpnId'],
			'Services' => $dirParams['Services'],
			'Diagnosis' => $dirParams['Diagnosis'],
			'HelpKindId' => 1400
		);
		/*if( isset($dirParams['PayType_Code']) && $dirParams['PayType_Code'] == 2){
			// Если в направлении вид бюджета: «Республиканский (пол-ка,стац,СКПН и другие)», то передавать «1400». В остальных случаях не передавать.
			$paramsArr['HelpKindId'] = 1400;
		}*/
		$params = json_encode($paramsArr);
		
		$this->textlog->add("/directions/add: " . $dirParams['EvnDirection_id'] . ' / №' . $dirParams['Number'] . ' / ' . $dirParams['Person_Fio'] . ' / ' . $dirParams['Lpu_Nick']);
		
		$result = $this->exec('/directions/add', 'post', $params);


		$logMessage = json_encode($result);
		$resultArray = objectToArray($result);
		
		if(empty($resultArray)) {
			return false;
		}

		$ServiceListLogType_id = $this->ServiceListLogHelper->getServiceListLogType($resultArray);

		$this->ServiceList_model->saveServiceListDetailLog(array(
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceListLogType_id' => $ServiceListLogType_id,
			'ServiceListDetailLog_Message' => $logMessage,
			'Evn_id' => $dirParams['EvnDirection_id'],
			'pmUser_id' => $this->pmUser_id
		));
		$this->ServiceList_model->addServiceListPackage(array(
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceListPackage_ObjectName' => 'EvnDirection',
			'ServiceListPackage_ObjectID' => $dirParams['EvnDirection_id'],
			'Lpu_oid' => $dirParams['Lpu_sid'],
			'pmUser_id' => $this->pmUser_id,
		));

		if (isset($result->id) && !empty($result->id)) {
			$this->saveAISResponse(array(
				'Evn_id' => $dirParams['EvnDirection_id'],
				'AISResponse_id' => null,
				'AISResponse_usid' => $result->id, // id направления (int)
				'AISResponse_uid' => count($result->serviceList) ? $result->serviceList[0]->item1 : null, // id услуги (uid)
				'AISResponse_IsSuccess' => 1,
				'AISFormLoad_id' => 2,
				'pmUser_id' => 1
			));
			return $result;
		}
			
		return false;
	}

	/**
	 * Отправка ТАП в сервис
	 */
	function syncEvnPL($EvnPL_id) {

		$evnPLInfo = $this->getEvnPLInfo(array(
			'EvnPL_id' => $EvnPL_id
		));
		
		if (
			!empty($evnPLInfo['EvnDirection_setDT']) && // если направление
			$evnPLInfo['EvnDirection_IsReceive'] != 2 && $evnPLInfo['NAP_MOID'] != $evnPLInfo['MOID'] && // в другую МО
			empty($evnPLInfo['LpuDispContractUslugaComplexLink_id']) // но нет договора
		) {
			return false; // не передаём вообще
		}

		if (!empty($evnPLInfo['EvnDirection_setDT']) && $evnPLInfo['EvnDirection_IsReceive'] != 2) { // если указано направление, передаем его
			$DiagType = 1;
			$VidposKod = 1; // Стат. карта

			$dirParams = array(
				'Lpu_sid' => $evnPLInfo['Lpu_sid'],
				'EvnDirection_id' => $evnPLInfo['EvnDirection_id'],
				'Number' => $evnPLInfo['EvnDirection_Num'],
				'OrgFromCode' => $evnPLInfo['NAP_MOID'],
				'OrgToCode' => $evnPLInfo['MOID'],
				'DtSent' => $evnPLInfo['EvnDirection_setDT'],
				'FinID' => intval($this->getSyncSpr('sp_fin', $evnPLInfo['PayType_id'])),
				'SocialDisadv' => ( isset($evnPLInfo['SocialDisadv']) && $evnPLInfo['SocialDisadv'] == 1) ? true : false,
				'SocDisGroupId' => ( isset($evnPLInfo['SocDisGroupId']) ) ? $evnPLInfo['SocDisGroupId'] : 900,
				'DoctorCode' => $evnPLInfo['NAP_PersonalID'],
				'HumanRpnId' => floatval($evnPLInfo['BDZ_id']),
				'IIN' => $evnPLInfo['IIN'],
				'Person_Fio' => $evnPLInfo['Person_Fio'],
				'Lpu_Nick' => $evnPLInfo['Lpu_Nick'],
				'Services' => array(
					array(
						'TarUslKod' => $evnPLInfo['UslugaComplex_Code'],
						'Iteration' => 1,
						'VidposKod' => $VidposKod,
						'ZavCode' => $evnPLInfo['ZavCode'],
						'OtdIdZav' => $evnPLInfo['OtdIdZav'],
						'DtAgreement' => $evnPLInfo['DtAgreement'],
						'PaymentType' => 1,
						'Kol' => 1,
						'PaymentType' => $evnPLInfo['PayType_id'] == 151 ? 1 : null
					)
				),
				'Diagnosis' => array(
					array(
						'SpmkbId' => $evnPLInfo['Diag_Code'],
						'DiagType' => $DiagType,
						'DoctorCode' => $evnPLInfo['PersonalID']
					)
				),
				'PayType_Code' => $evnPLInfo['PayType_Code']
			);

			$directionId = $this->syncDirection($dirParams);
		}

		if (!empty($directionId)) {
			$params = json_encode(array(
				'id' => $directionId->id,
				'OrgId' => $evnPLInfo['MOID'],
				'OrgKod' => $evnPLInfo['MedCode'],
				'dt_obrash' => $evnPLInfo['EvnPL_setDT'],
				'vra' => $evnPLInfo['PersonalID'],
				'IsConfirmed' => true,
				'IsCity' => 1,
				'PovObrash_id' => 3,
				'HelpKindId' => 1400,
				'SocDisGroupId' => 900, // "9" - по заболеванию
				'visits' =>$this->getVisits($EvnPL_id),
				'diagnoses' => $this->getDiagnoses($EvnPL_id),
				'operations' => $this->getOperations($EvnPL_id),
				'services' => $this->getServices($EvnPL_id)
			));

			$this->textlog->add("/directions/CreateCard: " . $evnPLInfo['EvnPL_id'] . ' / №' . $evnPLInfo['EvnPL_NumCard'] . ' / ' . $evnPLInfo['Person_Fio'] . ' / ' . $evnPLInfo['Lpu_Nick']);
			
			$result = $this->exec('/directions/CreateCard', 'post', $params);

			$logMessage = json_encode($result);

			if (isset($result->cardIsSaved) && $result->cardIsSaved == true) {
				$this->saveAISResponse(array(
					'Evn_id' => $EvnPL_id,
					'AISResponse_id' => null,
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 1,
					'AISFormLoad_id' => 2,
					'pmUser_id' => 1
				));
			}
		} else {

			$paramsArr = array(
				'HumanRpnId' => floatval($evnPLInfo['BDZ_id']),
				'OrgId' => $evnPLInfo['MOID'],
				'dt_obrash' => $evnPLInfo['EvnPL_setDT'],
				'vra' => $evnPLInfo['PersonalID'],
				'IsConfirmed' => true,
				'IsCity' => 1,
				'PovObrash_id' => 3,
				'OrgPmsp' => $evnPLInfo['ATTACH_MOID'],
				'FinID' => intval($this->getSyncSpr('sp_fin', $evnPLInfo['PayType_id'])),
				'HelpKindId' => 1400,
				'SocialDisadv' => (isset($evnPLInfo['SocialDisadv']) && $evnPLInfo['SocialDisadv'] == 1) ? true : false,
				'SocDisGroupId' => (isset($evnPLInfo['SocDisGroupId'])) ? $evnPLInfo['SocDisGroupId'] : 900,
				'visits' => $this->getVisits($EvnPL_id),
				'diagnoses' => $this->getDiagnoses($EvnPL_id),
				'operations' => $this->getOperations($EvnPL_id),
				'services' => $this->getServices($EvnPL_id)
			);


			$params = json_encode($paramsArr);

			$this->textlog->add("/directions/CreateCardWithoutDir: " . $evnPLInfo['EvnPL_id'] . ' / №' . $evnPLInfo['EvnPL_NumCard'] . ' / ' . $evnPLInfo['Person_Fio'] . ' / ' . $evnPLInfo['Lpu_Nick']);
			
			$result = $this->exec('/directions/CreateCardWithoutDir', 'post', $params);


			$logMessage = json_encode($result);
			$resultArray = objectToArray($result);

			$ServiceListLogType_id = $this->ServiceListLogHelper->getServiceListLogType($resultArray);

			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => $ServiceListLogType_id,
				'ServiceListDetailLog_Message' => $logMessage,
				'Evn_id' => $evnPLInfo['EvnPL_id'],
				'pmUser_id' => $this->pmUser_id
			));
			$this->ServiceList_model->addServiceListPackage(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListPackage_ObjectName' => 'EvnPL_id',
				'ServiceListPackage_ObjectID' => $evnPLInfo['EvnPL_id'],
				'Lpu_oid' => $evnPLInfo['Lpu_id'],
				'pmUser_id' => $this->pmUser_id,
			));


			if(isset($result->cardIsSaved) && $result->cardIsSaved == true) {
				$this->saveAISResponse(array(
					'Evn_id' => $EvnPL_id,
					'AISResponse_id' => null,
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 1,
					'AISFormLoad_id' => 2,
					'pmUser_id' => 1
				));
			}
		}
	}

	/**
	 * Отправка стоматологического ТАП в сервис
	 */
	function syncEvnPLStom($EvnPLStom_id)
	{
		$evnPLStomInfo = $this->getEvnPLStomInfo($EvnPLStom_id);

		if (
			!empty($evnPLStomInfo['EvnDirection_setDT']) && // если направление
			$evnPLStomInfo['EvnDirection_IsReceive'] != 2 && $evnPLStomInfo['NAP_MOID'] != $evnPLStomInfo['MOID'] && // в другую МО
			empty($evnPLStomInfo['LpuDispContractUslugaComplexLink_id']) // но нет договора
		) {
			return false; // не передаём вообще
		}


		if (!empty($evnPLStomInfo['EvnDirection_setDT']) && $evnPLStomInfo['EvnDirection_IsReceive'] != 2) { // если указано направление, передаем его
			$DiagType = 1;
			$VidposKod = 1; // Стат. карта

			$dirParams = array(
				'Lpu_sid' => $evnPLStomInfo['Lpu_sid'],
				'EvnDirection_id' => $evnPLStomInfo['EvnDirection_id'],
				'Number' => $evnPLStomInfo['EvnDirection_Num'],
				'OrgFromCode' => $evnPLStomInfo['NAP_MOID'],
				'OrgToCode' => $evnPLStomInfo['MOID'],
				'DtSent' => $evnPLStomInfo['EvnDirection_setDT'],
				'FinID' => intval($this->getSyncSpr('sp_fin', $evnPLStomInfo['PayType_id'])),
				'SocialDisadv' => ( isset($evnPLStomInfo['SocialDisadv']) && $evnPLStomInfo['SocialDisadv'] == 1) ? true : false,
				'SocDisGroupId' => ( isset($evnPLStomInfo['SocDisGroupId']) ) ? $evnPLStomInfo['SocDisGroupId'] : 900,
				'DoctorCode' => $evnPLStomInfo['NAP_PersonalID'],
				'HumanRpnId' => floatval($evnPLStomInfo['BDZ_id']),
				'IIN' => $evnPLStomInfo['IIN'],
				'Person_Fio' => $evnPLStomInfo['Person_Fio'],
				'Lpu_Nick' => $evnPLStomInfo['Lpu_Nick'],
				'Services' => array(
					array(
						'TarUslKod' => $evnPLStomInfo['UslugaComplex_Code'],
						'Iteration' => 1,
						'VidposKod' => $VidposKod,
						'ZavCode' => $evnPLStomInfo['ZavCode'],
						'OtdIdZav' => $evnPLStomInfo['OtdIdZav'],
						'DtAgreement' => $evnPLStomInfo['DtAgreement'],
						'PaymentType' => 1,
						'Kol' => 1
					)
				),
				'Diagnosis' => array(
					array(
						'SpmkbId' => $evnPLStomInfo['Diag_Code'],
						'DiagType' => $DiagType,
						'DoctorCode' => $evnPLStomInfo['PersonalID']
					)
				),
				'PayType_Code' => $evnPLStomInfo['PayType_Code']
			);

			$directionId = $this->syncDirection($dirParams);
		}

		if (!empty($directionId)) {
			$params = json_encode(array(
				'id' => $directionId->id,
				'OrgId' => $evnPLStomInfo['MOID'],
				'OrgKod' => $evnPLStomInfo['MedCode'],
				'dt_obrash' => $evnPLStomInfo['EvnPLStom_setDT'],
				'vra' => $evnPLStomInfo['PersonalID'],
				'IsConfirmed' => true,
				'IsCity' => 1,
				'PovObrash_id' => 3,
				'HelpKindId' => 1400,
				'SocDisGroupId' => 900, // "9" - по заболеванию
				'visits' =>$this->getVisits($EvnPLStom_id),
				'diagnoses' => $this->getStomDiagnoses($EvnPLStom_id),
				'operations' => $this->getOperations($EvnPLStom_id),
				'services' => array_merge($this->getStomServices($EvnPLStom_id), $this->getServices($EvnPLStom_id))
			));

			$this->textlog->add("/directions/CreateCardWithoutDir: " . $evnPLStomInfo['EvnPLStom_id'] . ' / №' . $evnPLStomInfo['EvnPLStom_NumCard'] . ' / ' . $evnPLStomInfo['Person_Fio'] . ' / ' . $evnPLStomInfo['Lpu_Nick']);

			$result = $this->exec('/directions/CreateCard', 'post', $params);

			$logMessage = json_encode($result);

			if (isset($result->cardIsSaved) && $result->cardIsSaved == true) {
				$this->saveAISResponse(array(
					'Evn_id' => $EvnPLStom_id,
					'AISResponse_id' => null,
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 1,
					'AISFormLoad_id' => 2,
					'pmUser_id' => 1
				));
			}
		} else {

			$paramsArr = array(
				'HumanRpnId' => floatval($evnPLStomInfo['BDZ_id']),
				'OrgId' => $evnPLStomInfo['MOID'],
				'dt_obrash' => $evnPLStomInfo['EvnPLStom_setDT'],
				'vra' => $evnPLStomInfo['PersonalID'],
				'IsConfirmed' => true,
				'IsCity' => 1,
				'PovObrash_id' => 3,
				'OrgPmsp' => $evnPLStomInfo['ATTACH_MOID'],
				'FinID' => intval($this->getSyncSpr('sp_fin', $evnPLStomInfo['PayType_id'])),
				'HelpKindId' => 1400,
				'SocialDisadv' => (isset($evnPLStomInfo['SocialDisadv']) && $evnPLStomInfo['SocialDisadv'] == 1) ? true : false,
				'SocDisGroupId' => (isset($evnPLStomInfo['SocDisGroupId'])) ? $evnPLStomInfo['SocDisGroupId'] : 900,
				'visits' => $this->getVisits($EvnPLStom_id),
				'diagnoses' => $this->getStomDiagnoses($EvnPLStom_id),
				'operations' => $this->getOperations($EvnPLStom_id),
				'services' => array_merge($this->getStomServices($EvnPLStom_id), $this->getServices($EvnPLStom_id))
			);


			$params = json_encode($paramsArr);

			$this->textlog->add("/directions/CreateCardWithoutDir: " . $evnPLStomInfo['EvnPLStom_id'] . ' / №' . $evnPLStomInfo['EvnPLStom_NumCard'] . ' / ' . $evnPLStomInfo['Person_Fio'] . ' / ' . $evnPLStomInfo['Lpu_Nick']);

			$result = $this->exec('/directions/CreateCardWithoutDir', 'post', $params);


			$logMessage = json_encode($result);
			$resultArray = objectToArray($result);

			$ServiceListLogType_id = $this->ServiceListLogHelper->getServiceListLogType($resultArray);

			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => $ServiceListLogType_id,
				'ServiceListDetailLog_Message' => $logMessage,
				'Evn_id' => $evnPLStomInfo['EvnPLStom_id'],
				'pmUser_id' => $this->pmUser_id
			));
			$this->ServiceList_model->addServiceListPackage(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListPackage_ObjectName' => 'EvnPLStom_id',
				'ServiceListPackage_ObjectID' => $evnPLStomInfo['EvnPLStom_id'],
				'Lpu_oid' => $evnPLStomInfo['Lpu_id'],
				'pmUser_id' => $this->pmUser_id,
			));


			if(isset($result->cardIsSaved) && $result->cardIsSaved == true) {
				$this->saveAISResponse(array(
					'Evn_id' => $EvnPLStom_id,
					'AISResponse_id' => null,
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 1,
					'AISFormLoad_id' => 2,
					'pmUser_id' => 1
				));
			}
		}


	}

	/**
	 * Отправка параклинической услуги в сервис
	 */
	function syncEvnUslugaPar($EvnUslugaPar_id) {
		$evnUslugaParInfo = $this->getEvnUslugaParInfo(array(
			'EvnUslugaPar_id' => $EvnUslugaPar_id
		));
		
		if (
			!empty($evnUslugaParInfo['EvnDirection_setDT']) && // если направление
			$evnUslugaParInfo['EvnDirection_IsReceive'] != 2 && $evnUslugaParInfo['NAP_MOID'] != $evnUslugaParInfo['MOID'] && // в другую МО
			empty($evnUslugaParInfo['LpuDispContractUslugaComplexLink_id']) // но нет договора
		) {
			return false; // не передаём вообще
		}

		$directionId = null;
		if (!empty($evnUslugaParInfo['EvnDirection_setDT']) && $evnUslugaParInfo['EvnDirection_IsReceive'] != 2) { // если указано направление, передаем его
			$DiagType = 1;
			$VidposKod = 1; // Стат. карта

			$dirParams = array(
				'Lpu_sid' => $evnUslugaParInfo['Lpu_sid'],
				'EvnDirection_id' => $evnUslugaParInfo['EvnDirection_id'],
				'Number' => $evnUslugaParInfo['EvnDirection_Num'],
				'OrgFromCode' => $evnUslugaParInfo['NAP_MOID'],
				'OrgToCode' => $evnUslugaParInfo['MOID'],
				'DtSent' => $evnUslugaParInfo['EvnDirection_setDT'],
				'FinID' => intval($this->getSyncSpr('sp_fin', $evnUslugaParInfo['PayType_id'])),
				//'SocialDisadv' => in_array($evnUslugaParInfo['SocStatus_Code'], array(18, 21, 22, 24)),
				'SocialDisadv' => ( isset($evnUslugaParInfo['SocialDisadv']) && $evnUslugaParInfo['SocialDisadv'] == 1) ? true : false,
				'SocDisGroupId' => ( isset($evnUslugaParInfo['SocDisGroupId']) ) ? $evnUslugaParInfo['SocDisGroupId'] : 900,
				'DoctorCode' => $evnUslugaParInfo['NAP_PersonalID'],
				'HumanRpnId' => floatval($evnUslugaParInfo['BDZ_id']),
				'IIN' => $evnUslugaParInfo['IIN'],
				'Person_Fio' => $evnUslugaParInfo['Person_Fio'],
				'Lpu_Nick' => $evnUslugaParInfo['Lpu_Nick'],
				'Services' => array(
					array(
						'TarUslKod' => $evnUslugaParInfo['UslugaComplex_Code'],
						'Iteration' => 1,
						'VidposKod' => $VidposKod,
						'ZavCode' => $evnUslugaParInfo['ZavCode'],
						'OtdIdZav' => $evnUslugaParInfo['OtdIdZav'],
						'DtAgreement' => $evnUslugaParInfo['DtAgreement'],
						'PaymentType' => 1,
						'Kol' => 1
					)
				),
				'Diagnosis' => array(
					array(
						'SpmkbId' => $evnUslugaParInfo['Diag_Code'],
						'DiagType' => $DiagType,
						'DoctorCode' => $evnUslugaParInfo['PersonalID']
					)
				),
				'PayType_Code' => $evnUslugaParInfo['PayType_Code']
			);

			$directionId = $this->syncDirection($dirParams);
		}

		$PovObrash_id = 3;
		
		// если услуга не выполнена, ограничиваемся направлением
		if (empty($evnUslugaParInfo['EvnUslugaPar_setDT'])) {
			return true;
		}

		if (!empty($directionId)) {
			$params = json_encode(array(
				'id' => $directionId->id,
				'OrgId' => $evnUslugaParInfo['MOID'],
				'OrgKod' => $evnUslugaParInfo['MedCode'],
				'dt_obrash' => $evnUslugaParInfo['EvnUslugaPar_setDT'],
				'vra' => $evnUslugaParInfo['PersonalID'],
				'IsConfirmed' => true,
				'IsCity' => 1,
				'PovObrash_id' => $PovObrash_id,
				'HelpKindId' => 1400, 
				'SocDisGroupId' => 900, // "9" - по заболеванию
				'visits' => array(
					array(
						'Date' => $evnUslugaParInfo['EvnUslugaPar_setDT'],
						'VidPos' => 2,
						'vra_sur_id' => $evnUslugaParInfo['PersonalID'],
						'vra_spec_id' => $evnUslugaParInfo['SpecialityID'],
						'otd_id' => $evnUslugaParInfo['FPID']
					)
				),
				'diagnoses' => array(),
				'operations' => array(),
				'services' => array(
					array(
						"guid" => $directionId->serviceList[0]->item1,
						'Date' => $evnUslugaParInfo['EvnUslugaPar_setDT'],
						'vra_sur_id' => $evnUslugaParInfo['PersonalID'],
						'vra_spec_id' => $evnUslugaParInfo['SpecialityID'],
						'otd_id' => $evnUslugaParInfo['FPID'],
						'vid_pos' => 1,
						'Code' => $evnUslugaParInfo['UslugaComplex_Code'],
						'Count' => $evnUslugaParInfo['EvnUslugaPar_Kolvo'],
						'Iteration' => 1, // Первично
						'Type' => 3, 
						'DocType' => 2,
						'Confirm' => true,
						'Rejection' => false,
						'PaymentType' => $evnUslugaParInfo['PayType_id'] == 151 ? 1 : null,
						'TextResult' => "Услуга оказана {$evnUslugaParInfo['UslugaComplex_Code']} {$evnUslugaParInfo['UslugaComplex_Name']}"
					)
				)
			));

			$this->textlog->add("/directions/CreateCard: " . $evnUslugaParInfo['EvnUslugaPar_id'] . ' / ' . $evnUslugaParInfo['Person_Fio'] . ' / ' . $evnUslugaParInfo['Lpu_Nick']);

			$result = $this->exec('/directions/CreateCard', 'post', $params);
			
			$logMessage = json_encode($result);

			if(isset($result->cardIsSaved) && $result->cardIsSaved == true) {
				$this->saveAISResponse(array(
					'Evn_id' => $EvnUslugaPar_id,
					'AISResponse_id' => null,
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 1,
					'AISFormLoad_id' => 2,
					'pmUser_id' => 1
				));
			}
			
		} elseif(empty($evnUslugaParInfo['EvnDirection_setDT']) || $evnUslugaParInfo['EvnDirection_IsReceive'] == 2) {
			$paramsArr = array(
				'HumanRpnId' => floatval($evnUslugaParInfo['BDZ_id']),
				'OrgId' => $evnUslugaParInfo['MOID'],
				'dt_obrash' => $evnUslugaParInfo['EvnUslugaPar_setDT'],
				'vra' => $evnUslugaParInfo['PersonalID'],
				'IsConfirmed' => true,
				'IsCity' => 1,
				'PovObrash_id' => $PovObrash_id,
				'OrgPmsp' => $evnUslugaParInfo['ATTACH_MOID'],
				'FinID' => intval($this->getSyncSpr('sp_fin', $evnUslugaParInfo['PayType_id'])),
				//'SocialDisadv' => false,
				'HelpKindId' => 1400,
				//'SocDisGroupId' => 900, // "9" - по заболеванию
				'SocialDisadv' => ( isset($evnUslugaParInfo['SocialDisadv']) && $evnUslugaParInfo['SocialDisadv'] == 1) ? true : false,
				'SocDisGroupId' => ( isset($evnUslugaParInfo['SocDisGroupId']) ) ? $evnUslugaParInfo['SocDisGroupId'] : 900,
				'visits' => array(
					array(
						'Date' => $evnUslugaParInfo['EvnUslugaPar_setDT'],
						'VidPos' => 2,
						'vra_sur_id' => $evnUslugaParInfo['PersonalID'],
						'vra_spec_id' => $evnUslugaParInfo['SpecialityID'],
						'otd_id' => $evnUslugaParInfo['FPID']
					)
				),
				'diagnoses' => array(),
				'operations' => array(),
				'services' => array(
					array(
						"guid" => "", // не заполняется
						'Date' => $evnUslugaParInfo['EvnUslugaPar_setDT'],
						'vra_sur_id' => $evnUslugaParInfo['PersonalID'],
						'vra_spec_id' => $evnUslugaParInfo['SpecialityID'],
						'otd_id' => $evnUslugaParInfo['FPID'],
						'vid_pos' => 1,
						'Code' => $evnUslugaParInfo['UslugaComplex_Code'],
						'Count' => $evnUslugaParInfo['EvnUslugaPar_Kolvo'],
						'Iteration' => 1, // Первично
						'Type' => 3, 
						'DocType' => 2,
						'Confirm' => true,
						'Rejection' => false,
						'PaymentType' => $evnUslugaParInfo['PayType_id'] == 151 ? 1 : null,
						'TextResult' => "Услуга оказана {$evnUslugaParInfo['UslugaComplex_Code']} {$evnUslugaParInfo['UslugaComplex_Name']}"
					)
				)
			);
			/*if( isset($evnUslugaParInfo['PayType_Code']) && $evnUslugaParInfo['PayType_Code'] == 2){
				// Если в направлении вид бюджета: «Республиканский (пол-ка,стац,СКПН и другие)», то передавать «1400». В остальных случаях не передавать.
				$paramsArr['HelpKindId'] = 1400;
			}*/
			$params = json_encode($paramsArr);

			$this->textlog->add("/directions/CreateCardWithoutDir: " . $evnUslugaParInfo['EvnUslugaPar_id'] . ' / ' . $evnUslugaParInfo['Person_Fio'] . ' / ' . $evnUslugaParInfo['Lpu_Nick']);
			
			$result = $this->exec('/directions/CreateCardWithoutDir', 'post', $params);


			$logMessage = json_encode($result);
			$resultArray = objectToArray($result);

			$ServiceListLogType_id = $this->ServiceListLogHelper->getServiceListLogType($resultArray);

			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => $ServiceListLogType_id,
				'ServiceListDetailLog_Message' => $logMessage,
				'Evn_id' => $evnUslugaParInfo['EvnUslugaPar_id'],
				'pmUser_id' => $this->pmUser_id
			));
			$this->ServiceList_model->addServiceListPackage(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListPackage_ObjectName' => 'EvnUslugaPar',
				'ServiceListPackage_ObjectID' => $evnUslugaParInfo['EvnUslugaPar_id'],
				'Lpu_oid' => $evnUslugaParInfo['Lpu_id'],
				'pmUser_id' => $this->pmUser_id,
			));

			
			if(isset($result->cardIsSaved) && $result->cardIsSaved == true) {
				$this->saveAISResponse(array(
					'Evn_id' => $EvnUslugaPar_id,
					'AISResponse_id' => null,
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 1,
					'AISFormLoad_id' => 2,
					'pmUser_id' => 1
				));
			}
		}
		// var_dump($result);
	}

	/**
	 * Получение посещений ТАП
	 */
	function getVisits($EvnPL_id) {
		$visits = array();

		$resp = $this->queryResult("
			select
				convert(varchar(10), evpl.EvnVizitPL_setDT, 120) as EvnVizitPL_setDT,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				lut.LpuUnitType_SysNick
			from
				v_EvnVizitPL evpl (nolock)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = evpl.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = evpl.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		foreach($resp as $respone) {
			$VidPos = 2;
			// Если тип группы отделения «Дневной стационар при поликлинике», то передавать 1
			if ($respone['LpuUnitType_SysNick'] == 'pstac') {
				$VidPos = 1;
			}

			$visits[] = array(
				'Date' => $respone['EvnVizitPL_setDT'],
				'VidPos' => $VidPos,
				'vra_sur_id' => $respone['PersonalID'],
				'vra_spec_id' => $respone['SpecialityID'],
				'otd_id' => $respone['FPID']
			);
		}

		return $visits;
	}

	/**
	 * Получение диагнозов ТАП
	 */
	function getDiagnoses($EvnPL_id) {
		$diagnoses = array();

		$resp = $this->queryResult("
			select
				gph.PersonalID,
				d.Diag_Code,
				dt.DeseaseType_SysNick
			from
				v_EvnVizitPL evpl (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = evpl.Diag_id
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = evpl.DeseaseType_id
				outer apply (
					select top 1
						gpw.PersonalID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = evpl.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		foreach($resp as $respone) {
			$diagtype = 1;
			switch($respone['DeseaseType_SysNick']) {
				case 'sharp':
					$diagtype = 1;
					break;
				case 'new':
					$diagtype = 2;
					break;
				case 'before':
					$diagtype = 3;
					break;
			}
			$diagnoses[] = array(
				'spmkb' => $respone['Diag_Code'],
				'diagtype' => $diagtype,
				'vra_sur_id' => $respone['PersonalID']
			);
		}

		return $diagnoses;
	}

	/**
	 * Получение диагнозов ТАП
	 */
	function getStomDiagnoses($EvnPLStom_id) {
		$diagnoses = array();

		$resp = $this->queryResult("
			SELECT DISTINCT -- только неповторяющиеся комбинации
				gph.PersonalID,
				d.Diag_Code,
				dt.DeseaseType_SysNick
			from
				v_EvnDiagPLStom EDPLS (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = EDPLS.Diag_id
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = EDPLS.DeseaseType_id
				left join v_EvnVizitPL EVPLS (nolock) on EVPLS.EvnVizitPL_pid = EDPLS.EvnDiagPLStom_rid
				outer apply (
					select top 1
						gpw.PersonalID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = EVPLS.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				EDPLS.EvnDiagPLStom_rid = :EvnPLStom_id
		", array(
			'EvnPLStom_id' => $EvnPLStom_id
		));

		foreach($resp as $respone) {
			$diagtype = 1;
			switch($respone['DeseaseType_SysNick']) {
				case 'sharp':
					$diagtype = 1;
					break;
				case 'new':
					$diagtype = 2;
					break;
				case 'before':
					$diagtype = 3;
					break;
			}
			$diagnoses[] = array(
				'spmkb' => $respone['Diag_Code'],
				'diagtype' => $diagtype,
				'vra_sur_id' => $respone['PersonalID']
			);
		}

		return $diagnoses;
	}

	/**
	 * Получение операций ТАП
	 */
	function getOperations($EvnPL_id) {
		$operations = array();

		$resp = $this->queryResult("
			select
				convert(varchar(10), euo.EvnUslugaOper_setDT, 120) as EvnUslugaOper_setDT,
				uc.UslugaComplex_Code,
				gph.PersonalID
			from
				v_EvnUslugaOper euo (nolock)
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euo.UslugaComplex_id
				outer apply (
					select top 1
						gpw.PersonalID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					where
						gphwp.WorkPlace_id = euo.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				euo.EvnUslugaOper_rid = :EvnPL_id
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		foreach($resp as $respone) {
			$operations[] = array(
				'Date' => $respone['EvnUslugaOper_setDT'],
				'OperationCode' => $respone['UslugaComplex_Code'],
				'vra_sur_id' => $respone['PersonalID']
			);
		}

		return $operations;
	}

	/**
	 * Получение услуг ТАП
	 */
	function getServices($EvnPL_id) {
		$services = array();

		$resp = $this->queryResult("
			select
				convert(varchar(10), euc.EvnUslugaCommon_setDT, 120) as EvnUslugaCommon_setDT,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				ISNULL(euc.EvnUslugaCommon_Count, 1) as EvnUslugaCommon_Count,
				tc.TreatmentClass_Code,
				st.ServiceType_SysNick,
				euc.PayType_id
			from
				v_EvnUslugaCommon euc (nolock)
				cross apply (
					select top 1
						evpl.TreatmentClass_id,
						evpl.ServiceType_id
					from
						v_EvnVizitPL evpl with (nolock)
					where
						EVPL.EvnVizitPL_pid = :EvnPL_id
					order by
						EVPL.EvnVizitPL_setDT asc
				) EVPLFIRST
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = evplfirst.TreatmentClass_id
				left join v_ServiceType st (nolock) on st.ServiceType_id = evplfirst.ServiceType_id
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = euc.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				euc.EvnUslugaCommon_rid = :EvnPL_id
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		foreach($resp as $respone) {
			$VidposKod = 1; // Стат. карта
			// меняется в зависимости от вида посещения и места приёма
			if (in_array($respone['TreatmentClass_Code'], array('2.1','2.2','2.3','2.4','2.6'))) {
				$VidposKod = 3; // Скриннинг
			} else if (
				in_array($respone['TreatmentClass_Code'], array('1.2','2.5'))
				|| ($respone['TreatmentClass_Code'] == '1' && $respone['ServiceType_SysNick'] == 'home')
				|| ($respone['TreatmentClass_Code'] == '1.1' && $respone['ServiceType_SysNick'] == 'neotl')
			) {
				$VidposKod = 2; // На дому
			}

			$services[] = array(
				"guid" => "", // не заполняется
				'Date' => $respone['EvnUslugaCommon_setDT'],
				'vra_sur_id' => $respone['PersonalID'],
				'vra_spec_id' => $respone['SpecialityID'],
				'otd_id' => $respone['FPID'],
				'vid_pos' => $VidposKod,
				'Code' => $respone['UslugaComplex_Code'],
				'Count' => $respone['EvnUslugaCommon_Count'],
				'Iteration' => 1, // Первично
				'Type' => 3,
				'DocType' => 2,
				'Confirm' => true,
				'Rejection' => false,
				'TextResult' => "Услуга оказана {$respone['UslugaComplex_Code']} {$respone['UslugaComplex_Name']}",
				'PaymentType' => $respone['PayType_id'] == 151 ? 1 : null
			);
		}

		return $services;
	}

	/**
	 * Получение услуг ТАП
	 */
	function getStomServices($EvnPLStom_id) {
		$services = array();

		$resp = $this->queryResult("
			select
				convert(varchar(10), EUS.EvnUslugaStom_setDT, 120) as EvnUslugaStom_setDT,
				uc.UslugaComplex_Code,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				ISNULL(EUS.EvnUslugaStom_Count, 1) as EvnUslugaStom_Count
			from
				v_EvnUslugaStom EUS (nolock)
			inner join 
				v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = EUS.UslugaComplex_id
			outer apply (
				select top 1
					gpw.PersonalID,
					gp.SpecialityID,
					gpw.FPID
				from
					r101.v_GetPersonalHistoryWP gphwp (nolock)
					inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
				where
					gphwp.WorkPlace_id = EUS.MedStaffFact_id
				order by
					gphwp.GetPersonalHistoryWP_insDT desc
			) gph
			where
				EUS.EvnUslugaStom_rid = :EvnPLStom_id
		", array(
			'EvnPLStom_id' => $EvnPLStom_id
		));

		foreach($resp as $respone) {

			$services[] = array(
				"guid" => "", // не заполняется
				'Date' => $respone['EvnUslugaStom_setDT'],
				'vra_sur_id' => $respone['PersonalID'],
				'vra_spec_id' => $respone['SpecialityID'],
				'otd_id' => $respone['FPID'],
				'vid_pos' => 1,
				'Code' => $respone['UslugaComplex_Code'],
				'Count' => $respone['EvnUslugaStom_Count'],
				'Iteration' => 1, // Первично
				'DocType' => 0,
				'Confirm' => true,
				'Rejection' => false
			);
		}

		return $services;
	}

	/**
	 * Сохранение ответа
	 */
	function saveAISResponse($data) {
		$proc = empty($data['AISResponse_id']) ? 'p_AISResponse_ins' : 'p_AISResponse_upd';
		if (!isset($data['AISResponse_usid'])) $data['AISResponse_usid'] = null;
		return $this->queryResult("
			declare
				@AISResponse_id bigint = :AISResponse_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec r101.{$proc}
				@AISResponse_id = @AISResponse_id output,
				@Evn_id = :Evn_id,
				@AISResponse_uid = :AISResponse_uid,
				@AISResponse_usid = :AISResponse_usid,
				@AISResponse_IsSuccess = :AISResponse_IsSuccess,
				@AISFormLoad_id = :AISFormLoad_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AISResponse_id as AISResponse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}

	/**
	 * Отправка всех закрытых ТАП
	 */
	function syncAll($data) {
		$this->load->model('ServiceList_model');
		$ServiceList_id = 21;
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
		
		$this->load->model('Options_model');
		$ais_reporting_period = $this->Options_model->getOptionsGlobals($data, 'ais_reporting_period25_9y');

		
		$this->ServiceListLog_id = $ServiceListLog_id;
		$this->pmUser_id = $data['pmUser_id'];

		$this->load->helper('ServiceListLog');
		$this->ServiceListLogHelper = new ServiceListLog(21, $this->pmUser_id);

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			$queryParams = array();
			$filter = "";
			$filter2 = '';

			if ( ! empty($data['EvnUslugaPar_id']) || ! empty($data['EvnPLStom_id']) || ! empty($data['EvnPL_id']))
			{
				$filter .= " and eup.EvnUslugaPar_id = :Evn_id";
				$filter2 .= " and EPL.EvnPL_id = :Evn_id";
			}

			if (!empty($data['EvnUslugaPar_id'])) {
				$queryParams['Evn_id'] = $data['EvnUslugaPar_id'];
			}
			else if (!empty($data['EvnPL_id'])) {
				$queryParams['Evn_id'] = $data['EvnPL_id'];
			}
			else if ( ! empty($data['EvnPLStom_id']))
			{
				$queryParams['Evn_id'] = $data['EvnPLStom_id'];
			}

			$allowedLpuIdsList = implode(',', $this->getAllowedLpuIds());
			$allowedLpuIdsList = mb_strlen($allowedLpuIdsList) > 0 ? $allowedLpuIdsList : 'NULL';

			// МО в разрешенном для выгрузки списке или существует направление из другой МО, а также договор между этими МО
			$filter .= " and (L.Lpu_id in ($allowedLpuIdsList) )"; //or exists ({$this->checkDirectionFromAnotherMo()}) )";
			$filter2 .= " and (L.Lpu_id in ($allowedLpuIdsList) )"; //or exists ({$this->checkDirectionFromAnotherMo()}) )";

			$queryParams['period'] = empty($ais_reporting_period) ? 1 : intval($ais_reporting_period);

			$query = "
				--declare @lastMonth date = cast(dateadd(month, -1, dbo.tzGetDate()) as date);
				declare @date date = dbo.tzGetDate();
				declare @day int = DAY(@date);
				declare @datestart date;

				if @day < 6	set @datestart = dateadd(month, -:period, @date);
				else set @datestart = dateadd(month, -:period+1, @date);

				set @datestart = dateadd(month, datediff(month, 0, @datestart), 0);
				if(year(@datestart) < year(@date)) set @datestart = DATEADD(yy, DATEDIFF(yy, 0, @date), 0);
				
				-- получаем список параклинических услуг, пихаем каждую в сервис
				select top 10
					eup.EvnUslugaPar_id as Evn_id,
					eup.EvnClass_id
				from
					v_EvnUslugaPar eup (nolock)
					inner join v_Lpu l (nolock) on l.Lpu_id = eup.Lpu_id -- только с МО региона
					inner join v_PayType pt (nolock) on pt.PayType_id = eup.PayType_id
					inner join r101.AISUslugaComplexLink ucl (nolock) on ucl.UslugaComplex_id = eup.UslugaComplex_id
					left join r101.AISResponse air (nolock) on air.Evn_id = eup.EvnUslugaPar_id and air.AISFormLoad_id = 2
				where
					eup.EvnUslugaPar_setDT is not null
					and eup.EvnDirection_id is not null
					and eup.EvnUslugaPar_updDT >= @datestart
					and air.AISResponse_id is null
					--and ucl.AISFormLoad_id = 2
					{$filter}
					
				union all
				
				-- список направлений (услуга не выполнена)
				select
					eup.EvnUslugaPar_id as Evn_id,
					eup.EvnClass_id
				from
					v_EvnUslugaPar eup (nolock)
					inner join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = eup.EvnDirection_id
					inner join v_Lpu l (nolock) on l.Lpu_id = eup.Lpu_id -- только с МО региона
					inner join v_PayType pt (nolock) on pt.PayType_id = eup.PayType_id
					inner join r101.AISUslugaComplexLink ucl (nolock) on ucl.UslugaComplex_id = eup.UslugaComplex_id
					left join r101.AISResponse air (nolock) on air.Evn_id = eup.EvnUslugaPar_id and air.AISFormLoad_id = 2
				where
					eup.EvnUslugaPar_setDT is null
					and eup.EvnDirection_id is not null
					and eup.EvnUslugaPar_updDT >= @datestart
					and ed.EvnDirection_IsReceive != 2
					and air.AISResponse_id is null
					--and ucl.AISFormLoad_id = 2
					{$filter}
					
				union all
				
				SELECT
					EPL.EvnPL_id as Evn_id,
					EPL.EvnClass_id
				FROM
					v_EvnPL EPL
					left join r101.AISResponse air (nolock) on air.Evn_id = EPL.EvnPL_id and air.AISFormLoad_id = 2 
				INNER JOIN 
					v_Lpu L (nolock) on L.Lpu_id = EPL.Lpu_id -- только с МО региона
				WHERE
					EPL.EvnPL_disDT >= @datestart
					and year(EPL.EvnPL_setDate) >= 2018
					and air.AISResponse_id is null
					{$filter2}
					";


			$resp = $this->queryResult($query, $queryParams);
			foreach ($resp as $respone)
			{
				try
				{
					switch ($respone['EvnClass_id'])
					{
						case 47:
							$this->syncEvnUslugaPar($respone['Evn_id']);
							break;
						case 6:
							$this->syncEvnPLStom($respone['Evn_id']);
							break;
						case 3:
							$this->syncEvnPL($respone['Evn_id']);
							break;
					}

				} catch (Exception $e)
				{
					/*if (!empty($_REQUEST['getDebug'])) {
						var_dump($e);
					}*/
					// падать не будем, просто пишем в лог инфу и идем дальше
					$this->textlog->add("syncAll error: code: " . $e->getCode() . " message: " . $e->getMessage());
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => $e->getMessage() . " (Evn_id={$respone['Evn_id']})",
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
			/*if (!empty($_REQUEST['getDebug'])) {
				var_dump($e);
			}*/
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
	 * Метод возвращаев массив с id разрашенных для выгрузки МО
	 *
	 * @return array
	 */
	function getAllowedLpuIds()
	{
		return array_merge($this->lpu259list, $this->lpu255and259list);
	}


	/**
	 * Метод возвращает запрос, который вызывается в другом запросе в методе SyncAll
	 *
	 * @return string
	 */
	function checkDirectionFromAnotherMo()
	{
		$query = "

			SELECT TOP 1
				EvnDirection_id
			FROM
				v_EvnDirection ED with (nolock)
			WHERE
				ED.EvnDirection_id = eup.EvnDirection_id AND
				ISNULL(ED.Lpu_sid, ED.Lpu_id) <> ED.Lpu_did /*AND
				EXISTS (
				
					SELECT TOP 1
						LpuDispContract_id
					FROM
						v_LpuDispContract LDC with (nolock)
					WHERE
							( ISNULL(ED.Lpu_sid, ED.Lpu_id) = LDC.Lpu_id AND ED.Lpu_did = LDC.Lpu_oid) OR
							( ISNULL(ED.Lpu_sid, ED.Lpu_id) = LDC.Lpu_oid AND ED.Lpu_did = LDC.Lpu_id)
				)*/
		";

		return $query;
	}
}