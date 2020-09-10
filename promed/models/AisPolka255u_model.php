<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AisPolka255u_model - модель
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
class AisPolka255u_model extends swModel {
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

		$this->load->library('textlog', array('file'=>'AisPolka255u_'.date('Y-m-d').'.log'));

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('AispKZ');

		$this->_config = $this->config->item('AisPolka255u');

		$AisPolkaEvnPLloadConfig = $this->config->item('AisPolkaEvnPLsync');

		$this->lpu259list = $AisPolkaEvnPLloadConfig['lpu259list'];
		$this->lpu255and259list = $AisPolkaEvnPLloadConfig['lpu255and259list'];
	}

	/**
	 * Выполнение запросов к сервису РПН и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceKZ', $this->config->item('AisPolka255u'), 'swserviceAisPolka255u');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->swserviceAisPolka255u->data($method, $type, $data);
		$this->textlog->add("result: ".print_r($result,true));
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
			if(in_array($table, array('finans2', 'sp_fin2', 'sp_fin3', 'sp_cause', 'sp_napr_ber', 'sp_travm', 'sp_ishod_screen', 'sp_stat_Pivo', 'sp_stat_Vino', 'sp_stat_Vodka'))) {
				$idField = "link.{$table}_id";
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

			if (isset($resp[0]['id'])) {
				if (in_array($table, array('sp_ishod_screen', 'sp_travm'))) {
					$resp[0]['id'] = str_pad($resp[0]['id'], 2, '0', STR_PAD_LEFT);
				}
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
				EPL.Lpu_id,
				EPL.EvnPL_NumCard as NumCard,
				p.BDZ_id,
				air.AISResponse_id,
				air.AISResponse_uid,
				gph.MOID,
				case when sc.SocStatus_SysNick = 'oralman' then 'true' else 'false' end as Oralman,
				isnull(Urban.Urban, 'false') as Urban,
				EVPLFIRST.PayType_id,
				isnull(pp.sp_lgot_id, 15) as Lgot,
				isnull(pd.PrehospDirect_id, 14) as PrehospDirect_id,
				EVPLFIRST.VizitType_id,
				EPL.ResultClass_id,
				EPL.PrehospTrauma_id,
				case when EVPLFIRST.PayType_id = 151 then '14' else '' end as HelpKind,
				case 
					when ppsoc.PrivilegeType_id is not null then 'true' 
					when eugobmp.EvnUsluga_id is not null then 'true'
				else 'false' end as SocialDisadv,
				isnull(ppsoc.SubCategoryPrivType_id, 9) as SocDisGroup,
				case 
					when 
						EPL.Person_Age < 1 and 
						EVPLMotherCheck.EvnVizitPL_id is not null and 
						EVPLMotherCheckST.EvnVizitPL_id is not null
					then mother.Person_Inn
					else null
				end as IINMom,
				case 
					when 
						EPL.Person_Age < 1 and 
						EVPLMotherCheck.EvnVizitPL_id is not null and 
						EVPLMotherCheckST.EvnVizitPL_id is not null and 
						mother.Person_Inn is not null
					then 1
					when 
						EPL.Person_Age < 1 and 
						EVPLMotherCheck.EvnVizitPL_id is not null and 
						EVPLMotherCheckST.EvnVizitPL_id is not null
					then 2
					else null
				end as findInformMom,
				convert(varchar(10), dateadd(day, 3, ps.Person_BirthDay), 120) as Dt_discharge_from_hospital,
				case 
					when ps.KLCountry_id != 398 then 1
					when ps.Document_Num is null then 2
					else 3
				end as sp_lack_of_mom_inf_id,
				RTRIM(RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Firname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Secname, ''))) as Person_Fio
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
				left join r101.AISResponse air (nolock) on air.Evn_id = epl.EvnPL_id
				left join v_PersonState ps (nolock) on ps.Person_id = epl.Person_id
				left join v_Person p (nolock) on p.Person_id = epl.Person_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				left join v_PrehospDirect pd (nolock) on pd.PrehospDirect_id = epl.PrehospDirect_id
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
					case 
						when KLAreaType_id = 1 then 'true'
						else 'false'
					end as Urban
					from v_Address with (nolock)
					where Address_id = isnull(ps.UAddress_id,ps.PAddress_id)
				) Urban
				outer apply (
					select top 1 
						case when pp.PrivilegeType_Code = '6' then 10
						else ll.sp_lgot_id end as sp_lgot_id
					from 
						v_PersonPrivilege pp (nolock) 
						left join r101.sp_lgotLink ll (nolock) on ll.PrivilegeType_id = pp.PrivilegeType_id
					where pp.Person_id = ps.Person_id
					order by pp.PersonPrivilege_begDate desc
				) pp
				outer apply (
					select top 1 
						eu.EvnUsluga_id
					from 
						v_EvnUsluga eu (nolock) 
					inner join UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
					inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where
						eu.EvnUsluga_id = EPL.EvnPL_id and 
						ucat.UslugaComplexAttributeType_SysNick = 'gobmp' and 
						uca.UslugaComplexAttribute_Int = 2
				) eugobmp
				outer apply (
					select top 1 
						pt.PrivilegeType_id,
						SCPT.SubCategoryPrivType_id
					from 
						v_PersonPrivilege pp (nolock) 
						inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						inner join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT (nolock) on PPSCPT.PersonPrivilege_id = pp.PersonPrivilege_id
						inner join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
					where pp.Person_id = ps.Person_id and pt.PrivilegeType_Code = '18'
				) ppsoc
				left join PersonDeputy PDEP with (nolock) on PDEP.Person_id = ps.Person_id and PDEP.DeputyKind_id = 2
				outer apply (
					select top 1 
						es.Person_id
					from 
						v_PersonNewBorn pnb (nolock) 
						inner join v_EvnSection es (nolock) on es.EvnSection_id = pnb.EvnSection_mid
					where
						pnb.Person_id = ps.Person_id 
				) esmother
				outer apply (
					select top 1
						evpl.EvnVizitPL_id
					from
						v_EvnVizitPL evpl with (nolock)
					where
						EVPL.EvnVizitPL_pid = EPL.EvnPL_id and
						EVPL.VizitType_id = 120
					order by
						EVPL.EvnVizitPL_setDT asc
				) EVPLMotherCheck
				outer apply (
					select top 1
						evpl.EvnVizitPL_id
					from
						v_EvnVizitPL evpl with (nolock)
					where
						EVPL.EvnVizitPL_pid = EPL.EvnPL_id and
						EVPL.ServiceType_id in (2,3,5)
					order by
						EVPL.EvnVizitPL_setDT asc
				) EVPLMotherCheckST
				left join v_BirthSvid bs with (nolock) on bs.Person_id = ps.Person_id
				left join v_PersonState mother (nolock) on mother.Person_id = coalesce(PDEP.Person_pid, esmother.Person_id, bs.Person_rid) and mother.Sex_id = 2
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
	 * Получение данных скриннингово исследования
	 */
	function getEvnPLDispScreenChildInfo($data) {
		$params = array('EvnPLDispScreenChild_id' => $data['EvnPLDispScreenChild_id']);

		$query = "
			select top 1
				EPLDSC.EvnPLDispScreenChild_id,
				p.BDZ_id,
				air.AISResponse_id,
				air.AISResponse_uid,
				gph.MOID,
				case when sc.SocStatus_SysNick = 'oralman' then 'true' else 'false' end as Oralman,
				isnull(Urban.Urban, 'false') as Urban,
				isnull(pp.sp_lgot_id, 15) as Lgot,
				convert(varchar(10), eudd.EvnUslugaDispDop_setDT, 120) as EvnUslugaDispDop_setDT,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				d.Diag_Code,
				dt.DeseaseType_SysNick
			from
				v_EvnPLDispScreenChild EPLDSC with (nolock)
				cross apply (
					select top 1
						eudd.EvnUslugaDispDop_setDT,
						eudd.MedStaffFact_id,
						eudd.Diag_id,
						eudd.DeseaseType_id
					from
						v_EvnUslugaDispDop eudd (nolock)
					where
						eudd.EvnUslugaDispDop_pid = EPLDSC.EvnPLDispScreenChild_id
						and eudd.SurveyType_id = 118 -- педиатр
				) eudd
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.MOID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = eudd.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = eudd.DeseaseType_id
				left join v_Diag d (nolock) on d.Diag_id = eudd.Diag_id
				left join r101.AISResponse air (nolock) on air.Evn_id = EPLDSC.EvnPLDispScreenChild_id
				left join v_PersonState ps (nolock) on ps.Person_id = EPLDSC.Person_id
				left join v_Person p (nolock) on p.Person_id = EPLDSC.Person_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				outer apply (
					select top 1 
					case 
						when KLAreaType_id = 1 then 'true'
						else 'false'
					end as Urban
					from v_Address with (nolock)
					where Address_id = isnull(ps.UAddress_id,ps.PAddress_id)
				) Urban
				outer apply (
					select top 1 
						case when pp.PrivilegeType_Code = '6' then 10
						else ll.sp_lgot_id end as sp_lgot_id
					from 
						v_PersonPrivilege pp (nolock) 
						left join r101.sp_lgotLink ll (nolock) on ll.PrivilegeType_id = pp.PrivilegeType_id
					where pp.Person_id = ps.Person_id
					order by pp.PersonPrivilege_begDate desc
				) pp
			where
				EPLDSC.EvnPLDispScreenChild_id = :EvnPLDispScreenChild_id
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные ТАП', 400);
		}
	}

	/**
	 * Получение данных скриннингово исследования для взрослых
	 */
	function getEvnPLDispScreenInfo($data) {
		$params = array('EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']);

		$query = "
			select top 1
				EPLDS.EvnPLDispScreen_id,
				p.BDZ_id,
				air.AISResponse_id,
				air.AISResponse_uid,
				gph.MOID,
				case when sc.SocStatus_SysNick = 'oralman' then 'true' else 'false' end as Oralman,
				isnull(Urban.Urban, 'false') as Urban,
				isnull(pp.sp_lgot_id, 15) as Lgot,
				convert(varchar(10), eudd.EvnUslugaDispDop_setDT, 120) as EvnUslugaDispDop_setDT,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				d.Diag_Code,
				dt.DeseaseType_SysNick
			from
				v_EvnPLDispScreen EPLDS with (nolock)
				cross apply (
					select top 1
						eudd.EvnUslugaDispDop_setDT,
						eudd.MedStaffFact_id,
						eudd.Diag_id,
						eudd.DeseaseType_id
					from
						v_EvnUslugaDispDop eudd (nolock)
					where
						eudd.EvnUslugaDispDop_pid = EPLDS.EvnPLDispScreen_id
				) eudd
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.MOID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = eudd.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = eudd.DeseaseType_id
				left join v_Diag d (nolock) on d.Diag_id = eudd.Diag_id
				left join r101.AISResponse air (nolock) on air.Evn_id = EPLDS.EvnPLDispScreen_id
				left join v_PersonState ps (nolock) on ps.Person_id = EPLDS.Person_id
				left join v_Person p (nolock) on p.Person_id = EPLDS.Person_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				outer apply (
					select top 1
					case 
						when KLAreaType_id = 1 then 'true'
						else 'false'
					end as Urban
					from v_Address with (nolock)
					where Address_id = isnull(ps.UAddress_id,ps.PAddress_id)
				) Urban
				outer apply (
					select top 1 
						case when pp.PrivilegeType_Code = '6' then 10
						else ll.sp_lgot_id end as sp_lgot_id
					from 
						v_PersonPrivilege pp (nolock) 
						left join r101.sp_lgotLink ll (nolock) on ll.PrivilegeType_id = pp.PrivilegeType_id
					where pp.Person_id = ps.Person_id
					order by pp.PersonPrivilege_begDate desc
				) pp
			where
				EPLDS.EvnPLDispScreen_id = :EvnPLDispScreen_id
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные ТАП', 400);
		}
	}

	/**
	 * Получение данных услуги
	 */
	function getEvnUslugaParInfo($data) {
		$params = array('EvnUslugaPar_id' => $data['EvnUslugaPar_id']);

		$query = "				
			select top 1
				eup.EvnUslugaPar_id,
				eup.Lpu_id,
				null as NumCard,
				p.BDZ_id,
				air.AISResponse_id,
				air.AISResponse_uid,
				gph.MOID,
				case when sc.SocStatus_SysNick = 'oralman' then 'true' else 'false' end as Oralman,
				isnull(Urban.Urban, 'false') as Urban,
				eup.PayType_id,
				isnull(pp.sp_lgot_id, 15) as Lgot,
				isnull(pd.PrehospDirect_id, 14) as PrehospDirect_id,
				123 as VizitType_id, -- Прочее
				78 as ResultClass_id, -- Не определен
				null as PrehospTrauma_id,
				case when eup.PayType_id = 151 then '14' else '' end as HelpKind,
				case 
					when ppsoc.PrivilegeType_id is not null then 'true' 
					when eugobmp.EvnUsluga_id is not null then 'true'
				else 'false' end as SocialDisadv,
				isnull(ppsoc.SubCategoryPrivType_id, 9) as SocDisGroup,
				RTRIM(RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Firname, '')) + ' ' + RTRIM(ISNULL(ps.Person_Secname, ''))) as Person_Fio
			from
				v_EvnUslugaPar eup with (nolock)
				left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eup.EvnDirection_id
				left join r101.AISResponse air (nolock) on air.Evn_id = eup.EvnUslugaPar_id
				left join v_PersonState ps (nolock) on ps.Person_id = eup.Person_id
				left join v_Person p (nolock) on p.Person_id = eup.Person_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				left join v_PrehospDirect pd (nolock) on pd.PrehospDirect_id = eup.PrehospDirect_id
				outer apply (
					select top 1
						msf.MedStaffFact_id 
					from v_MedStaffFact msf (nolock) 
					where 
						ed.Post_id = msf.Post_id and 
						ed.MedPersonal_id = msf.MedPersonal_id and 
						ed.LpuSection_id = msf.LpuSection_id
				) as msf
				left join v_MedStaffFact smsf (nolock) on 
					smsf.MedPersonal_id = eup.MedPersonal_sid and 
					smsf.LpuSection_id = eup.LpuSection_uid
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
						gphwp.WorkPlace_id = isnull(eup.MedStaffFact_id, smsf.MedStaffFact_id)
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				outer apply (
					select top 1 
					case 
						when KLAreaType_id = 1 then 'true'
						else 'false'
					end as Urban
					from v_Address with (nolock)
					where Address_id = isnull(ps.UAddress_id,ps.PAddress_id)
				) Urban
				outer apply (
					select top 1 
						case when pp.PrivilegeType_Code = '6' then 10
						else ll.sp_lgot_id end as sp_lgot_id
					from 
						v_PersonPrivilege pp (nolock) 
						left join r101.sp_lgotLink ll (nolock) on ll.PrivilegeType_id = pp.PrivilegeType_id
					where pp.Person_id = ps.Person_id
					order by pp.PersonPrivilege_begDate desc
				) pp
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
						SCPT.SubCategoryPrivType_id
					from 
						v_PersonPrivilege pp (nolock) 
						inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						inner join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT (nolock) on PPSCPT.PersonPrivilege_id = pp.PersonPrivilege_id
						inner join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
					where pp.Person_id = ps.Person_id and pt.PrivilegeType_Code = '18'
				) ppsoc
			where
				eup.EvnUslugaPar_id = :EvnUslugaPar_id
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные услуги', 400);
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
	 * Отправка ТАП в сервис
	 */
	function syncEvnPL($EvnPL_id, $EvnClass_id) {
		if ($EvnClass_id == 47) {
			$evnPLInfo = $this->getEvnUslugaParInfo(array(
				'EvnUslugaPar_id' => $EvnPL_id
			));
		} else {
			$evnPLInfo = $this->getEvnPLInfo(array(
				'EvnPL_id' => $EvnPL_id
			));
		}
		
		// lastVisit заполняется только если два последних посещения в одну дату
		$visitsResult = $this->getCard5YVisit($EvnPL_id, $EvnClass_id);

		$params = json_encode(array(
			'PersonId' => $evnPLInfo['BDZ_id'],
			'Uid' => $evnPLInfo['AISResponse_uid'],
			'Organization' => $evnPLInfo['MOID'],
			'LastVisit' => $visitsResult['lastVisit'],
			'Oralman' => $evnPLInfo['Oralman'],
			'Urban' => $evnPLInfo['Urban'],
			'Confirmed' => true,
			'DayAtHome' => null,
			'DayAtHospital' => null,
			'Budget' => $this->getSyncSpr('finans2', $evnPLInfo['PayType_id'], true),
			'Finans' => $this->getSyncSpr('sp_fin3', $evnPLInfo['PayType_id'], true),
			'Lgot' => $evnPLInfo['Lgot'],
			'DirectedBy' => $this->getSyncSpr('sp_napr_ber', $evnPLInfo['PrehospDirect_id'], true),
			'Cause' => $this->getSyncSpr('sp_cause', $evnPLInfo['VizitType_id'], true),
			'Injure' => $this->getSyncSpr('sp_travm', $evnPLInfo['PrehospTrauma_id'], true),
			'Diagnose' => null,
			'Result' => ($EvnClass_id == 47) ? '11' : $this->getSyncSpr('sp_ishod_screen', $evnPLInfo['ResultClass_id'], true),
			'Status' => 2,
			'Services' => $this->getServices($EvnPL_id, $evnPLInfo['Lpu_id']),
			'Diagnoses' => $this->getDiagnoses($EvnPL_id, $evnPLInfo['Lpu_id']),
			'Visits' => $visitsResult['visits'],
			'Surgeries' => $this->getOperations($EvnPL_id, $evnPLInfo['Lpu_id']),
			'Dispancers' => $this->getDispancers($EvnPL_id),
			'Card7Y' => null,
			'Card8Y' => null,
			'HelpKind' => '14', // $evnPLInfo['HelpKind'],
			'KomuSurId' => null,
			'IINMom' => isset($evnPLInfo['IINMom']) ? $evnPLInfo['IINMom'] : null,
			'Dt_discharge_from_hospital' => isset($evnPLInfo['Dt_discharge_from_hospital']) && $evnPLInfo['findInformMom'] == 1 ? $evnPLInfo['Dt_discharge_from_hospital'] : null,
			'findInformMom' => isset($evnPLInfo['findInformMom']) ? $evnPLInfo['findInformMom'] : null,
			'sp_lack_of_mom_inf_id' => isset($evnPLInfo['sp_lack_of_mom_inf_id']) && $evnPLInfo['findInformMom'] == 2 ? $evnPLInfo['sp_lack_of_mom_inf_id'] : null,
			'SocialDisadv' => $evnPLInfo['SocialDisadv'],
			'SocDisGroup' => (string)$evnPLInfo['SocDisGroup'] // '9' // ($evnPLInfo['SocialDisadv'] == 'true' && $evnPLInfo['Lgot'] == 15) ? '14' : '9'
		));

		$this->textlog->add("/Card5Y/Insert: " . $EvnPL_id . ' / № ' . $evnPLInfo['NumCard'] . ' / ' . $evnPLInfo['Person_Fio']);
		$result = $this->exec('/Card5Y/Insert', 'post', $params);
		//var_dump($result); exit;
		if (is_array($result) && $result['success'] == false) {
			if ($result['errorMsg'] == 'Ошибка в работе сервиса РПН: Врач и дата завершения СПО повторяются') {
				$this->saveAISResponse(array(
					'Evn_id' => $EvnPL_id,
					'AISResponse_id' => $evnPLInfo['AISResponse_id'],
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 2, // Дубль
					'AISFormLoad_id' => 1,
					'pmUser_id' => 1
				));
			}
			return false;
		}
		$this->saveAISResponse(array(
			'Evn_id' => $EvnPL_id,
			'AISResponse_id' => $evnPLInfo['AISResponse_id'],
			'AISResponse_uid' => $result->Response->kart_uid,
			'AISResponse_IsSuccess' => 1,
			'AISFormLoad_id' => 1,
			'pmUser_id' => 1
		));
	}

	/**
	 * Отправка скрининга детей в сервис
	 */
	function syncEvnPLDispScreenChild($EvnPLDispScreenChild_id) {
		$evnPLInfo = $this->getEvnPLDispScreenChildInfo(array(
			'EvnPLDispScreenChild_id' => $EvnPLDispScreenChild_id
		));

		$diagtype = null;
		switch($evnPLInfo['DeseaseType_SysNick']) {
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

		$params = json_encode(array(
			'PersonId' => $evnPLInfo['BDZ_id'],
			'Uid' => $evnPLInfo['AISResponse_uid'],
			'Organization' => $evnPLInfo['MOID'],
			'LastVisit' => null,
			'Oralman' => $evnPLInfo['Oralman'],
			'Urban' => $evnPLInfo['Urban'],
			'Confirmed' => true,
			'DayAtHome' => null,
			'DayAtHospital' => null,
			'Budget' => 2, // 2. Бюджет
			'Finans' => 16, // 16. Субвенции
			'Lgot' => $evnPLInfo['Lgot'],
			'DirectedBy' => 4, // 4. Самостоятельно
			'Cause' => 2, // 2. Проф. осмотр
			'Injure' => null, // Не заполнять
			'Diagnose' => null, // Не заполнять
			'Result' => 11, // 11. Прочие
			'Status' => 2, // 2. Завершен
			'Services' => $this->getCard5YService($EvnPLDispScreenChild_id),
			'Diagnoses' => array(
				array(
					'Uid' => null,
					'Diagnose' => trim($evnPLInfo['Diag_Code'], " \t\n\r\0\x0B\."),
					'Doctor' => $evnPLInfo['PersonalID'],
					'Type' => $diagtype
				)
			),
			'Visits' => array(
				array(
					'Uid' => null,
					'Date' => $evnPLInfo['EvnUslugaDispDop_setDT'],
					'Doctor' => $evnPLInfo['PersonalID'],
					'Speciality' => $evnPLInfo['SpecialityID'],
					'Department' => $evnPLInfo['FPID'],
					'Type' => 'П' // в поликлинике
				)
			),
			'Surgeries' => null, // Не заполнять
			'Dispancers' => null, // Не заполнять
			'Card7Y' => $this->getCard7Y($EvnPLDispScreenChild_id),
			'Card8Y' => null, // Не заполнять
			'HelpKind' => '18', // $evnPLInfo['HelpKind'],
			'KomuSurId' => null,
			'SocialDisadv' => false, // TODO: Пока без учёта социально незащищенных, сделать после https://redmine.swan.perm.ru/issues/120163
			'SocDisGroup' => '9' // TODO: Пока без учёта социально незащищенных
		));

		$result = $this->exec('/Card5Y/Insert', 'post', $params);
		//var_dump($result); exit;
		if (is_array($result) && $result['success'] == false) {
			return false;
		}
		$this->saveAISResponse(array(
			'Evn_id' => $EvnPLDispScreenChild_id,
			'AISResponse_id' => $evnPLInfo['AISResponse_id'],
			'AISResponse_uid' => $result->Response->kart_uid,
			'AISResponse_IsSuccess' => 1,
			'AISFormLoad_id' => 1,
			'pmUser_id' => 1
		));
	}

	/**
	 * Отправка скрининга взрослых в сервис
	 */
	function syncEvnPLDispScreen($EvnPLDispScreen_id) {
		$evnPLInfo = $this->getEvnPLDispScreenInfo(array(
			'EvnPLDispScreen_id' => $EvnPLDispScreen_id
		));

		$visitsResult = $this->getCard5YVisit($EvnPLDispScreen_id, 183);

		$params = json_encode(array(
			'PersonId' => $evnPLInfo['BDZ_id'],
			'Uid' => $evnPLInfo['AISResponse_uid'],
			'Organization' => $evnPLInfo['MOID'],
			'LastVisit' => $visitsResult['lastVisit'],
			'Oralman' => $evnPLInfo['Oralman'],
			'Urban' => $evnPLInfo['Urban'],
			'Confirmed' => true,
			'DayAtHome' => null,
			'DayAtHospital' => null,
			'Budget' => 2, // 2. Бюджет
			'Finans' => 16, // 16. Субвенции
			'Lgot' => $evnPLInfo['Lgot'],
			'DirectedBy' => 4, // 4. Самостоятельно
			'Cause' => 2, // 2. Проф. осмотр
			'Injure' => null, // Не заполнять
			'Diagnose' => null, // Не заполнять
			'Result' => 11, // 11. Прочие
			'Status' => 2, // 2. Завершен
			'Services' => $this->getCard5YServiceForScreen($EvnPLDispScreen_id),
			'Diagnoses' => $this->getDiagnosesCard8Y($EvnPLDispScreen_id),
			'Visits' => $visitsResult['visits'],
			'Surgeries' => null, // Не заполнять
			'Dispancers' => null, // Не заполнять
			'Card7Y' => null, // Не заполнять
			'Card8Y' => $this->getCard8Y($EvnPLDispScreen_id),
			'HelpKind' => 18,
			'KomuSurId' => null,
			'SocialDisadv' => false,
			'SocDisGroup' => '9'
		));


		$result = $this->exec('/Card5Y/Insert', 'post', $params);
		//var_dump($result); exit;
		if (is_array($result) && $result['success'] == false) {
			return false;
		}
		$this->saveAISResponse(array(
			'Evn_id' => $EvnPLDispScreen_id,
			'AISResponse_id' => $evnPLInfo['AISResponse_id'],
			'AISResponse_uid' => $result->Response->kart_uid,
			'AISResponse_IsSuccess' => 1,
			'AISFormLoad_id' => 1,
			'pmUser_id' => 1
		));
	}

	/**
	 * Получение посещений ТАП
	 */
	function saveAISResponse($data) {
		$proc = empty($data['AISResponse_id']) ? 'p_AISResponse_ins' : 'p_AISResponse_upd';
		return $this->queryResult("
			declare
				@AISResponse_id bigint = :AISResponse_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec r101.{$proc}
				@AISResponse_id = @AISResponse_id output,
				@Evn_id = :Evn_id,
				@AISResponse_uid = :AISResponse_uid,
				@AISResponse_IsSuccess = :AISResponse_IsSuccess,
				@AISFormLoad_id = :AISFormLoad_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AISResponse_id as AISResponse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}

	/**
	 * Получение посещений ТАП и паракл услуг
	 */
	function getVisitsForEvnPlAndUslugaPar($Evn_id)
	{
		$resp = $this->queryResult("
			select * from (
				select
					convert(varchar(10), evpl.EvnVizitPL_setDT, 120) as setDT,
					evpl.EvnVizitPL_setDT as setDT2,
					gph.PersonalID,
					gph.SpecialityID,
					gph.FPID,
					vp.code as vidpos_code
				from
					v_EvnVizitPL evpl (nolock)
					left join r101.sp_vidposLink vpl with (nolock) on vpl.ServiceType_id = evpl.ServiceType_id
					left join r101.sp_vidpos vp with (nolock) on vp.id = vpl.sp_vidpos_id
					left join v_MedStaffFact smsf (nolock) on 
						smsf.MedPersonal_id = evpl.MedPersonal_sid and 
						smsf.LpuSection_id = evpl.LpuSection_id
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
							gphwp.WorkPlace_id = isnull(evpl.MedStaffFact_id,smsf.MedStaffFact_id)
						order by
							gphwp.GetPersonalHistoryWP_insDT desc
					) gph
					left join v_EvnPL EPL (nolock) on EPL.EvnPL_id = :Evn_id
				where
					evpl.EvnVizitPL_pid = :Evn_id and
					evpl.Lpu_id = EPL.Lpu_id
					
				union all
				
				select top 1
					convert(varchar(10), eup.EvnUslugaPar_setDT, 120) as setDT,
					eup.EvnUslugaPar_setDT as setDT2,
					gph.PersonalID,
					gph.SpecialityID,
					gph.FPID,
					'П' as vidpos_code
				from
					v_EvnUslugaPar eup with (nolock)
					left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eup.EvnDirection_id
					left join v_MedStaffFact smsf (nolock) on 
						smsf.MedPersonal_id = eup.MedPersonal_sid and 
						smsf.LpuSection_id = eup.LpuSection_uid
					outer apply (
						select top 1
							msf.MedStaffFact_id 
						from v_MedStaffFact msf (nolock) 
						where 
							ed.Post_id = msf.Post_id and 
							ed.MedPersonal_id = msf.MedPersonal_id and 
							ed.LpuSection_id = msf.LpuSection_id
					) as msf
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
							left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
							left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
						where
							gphwp.WorkPlace_id = isnull(eup.MedStaffFact_id, smsf.MedStaffFact_id)
						order by
							gphwp.GetPersonalHistoryWP_insDT desc
					) gph
				where
					eup.EvnUslugaPar_id = :Evn_id
			) as t 
			order by setDT2 asc
		", array(
			'Evn_id' => $Evn_id
		));

		return $resp;
	}

	/**
	 * Посещения для скрининговых исследований детей
	 *
	 * @param $EvnPL_id
	 * @return array
	 */
	function getVisitsForEvnPlDispScreenChild($Evn_id)
	{
		$resp = $this->queryResult("
			select distinct
				convert(varchar(10), eudd.EvnUslugaDispDop_setDT, 120) as setDT,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				'П' as vidpos_code
			from
				v_EvnUslugaDispDop eudd (nolock)
				left join v_EvnPLDispScreenChild EPLDS on EPLDS.EvnPLDispScreenChild_id = :Evn_id
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.MOID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = eudd.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				eudd.SurveyType_id in (118,119,120,121,122,123,124,125) and
				(eudd.EvnUslugaDispDop_pid = :Evn_id or eudd.EvnUslugaDispDop_rid = :Evn_id)
				and eudd.Lpu_id = EPLDS.Lpu_id
			order by setDT asc
		", array(
			'Evn_id' => $Evn_id
		));

		return $resp;
	}

	/**
	 * Посещения для скрининговых исследований. Данные берутся из услуг посещения терапевта и акушера SurveyType_id in (44,19)
	 *
	 * @param $EvnPL_id
	 * @return array
	 */
	function getVisitsForEvnPlDispScreen($Evn_id)
	{
		$resp = $this->queryResult("
			select distinct
				convert(varchar(10), eudd.EvnUslugaDispDop_setDT, 120) as setDT,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				'П' as vidpos_code
			from
				v_EvnUslugaDispDop eudd (nolock)
				left join v_EvnPLDispScreen EPLDS on EPLDS.EvnPLDispScreen_id = :Evn_id
				
			outer apply (
				select top 1
					gpw.PersonalID,
					gp.SpecialityID,
					gpw.MOID,
					gpw.FPID
				from
					r101.v_GetPersonalHistoryWP gphwp (nolock)
					inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
					left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
				where
					gphwp.WorkPlace_id = eudd.MedStaffFact_id
				order by
					gphwp.GetPersonalHistoryWP_insDT desc
			) gph
			
			where
				eudd.SurveyType_id in (44,19) and -- только терапевт или акушер-гинеколог
				(eudd.EvnUslugaDispDop_pid = :Evn_id or eudd.EvnUslugaDispDop_rid = :Evn_id)
				and eudd.Lpu_id = EPLDS.Lpu_id
				
			
			
			order by setDT asc
		", array(
			'Evn_id' => $Evn_id
		));

		return $resp;
	}

	/**
	 * Общий метод получения посещений
	 */
	function getCard5YVisit($Evn_id, $EvnClass_id)
	{
		$visits = array();

		switch ($EvnClass_id)
		{
			case 187: // скрининг детей
				$resp = $this->getVisitsForEvnPlDispScreenChild($Evn_id);
				break;
			case 183: // скрининг взрослых
				$resp = $this->getVisitsForEvnPlDispScreen($Evn_id);
				break;
			default: // тап и паракл усл
				$resp = $this->getVisitsForEvnPlAndUslugaPar($Evn_id);
				break;
		}

		foreach($resp as $respone) {

			$visits[] = array(
				'Uid' => null,
				'Date' => $respone['setDT'],
				'Doctor' => $respone['PersonalID'],
				'Speciality' => $respone['SpecialityID'],
				'Department' => $respone['FPID'],
				'Type' => $respone['vidpos_code']
			);
		}

		$lastVisit = null;
		$visits_cnt = count($visits);
		if ($visits_cnt >= 2 && $visits[$visits_cnt-1]['Date'] == $visits[$visits_cnt-2]['Date']) {
			$lastVisit = $visits[count($visits)-1];
		}

		return array('visits' => $visits, 'lastVisit' => $lastVisit);
	}

	/**
	 * Получение диагнозов ТАП
	 */
	function getDiagnoses($EvnPL_id, $Lpu_id) {
		$diagnoses = array();

		$resp = $this->queryResult("
			select * from (
				select
					gph.PersonalID,
					d.Diag_Code,
					dt.DeseaseType_SysNick,
					EvnVizitPL_setDT as setDT
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
					evpl.EvnVizitPL_pid = :EvnPL_id and 
					evpl.Lpu_id = :Lpu_id
					
				union
					
				select
					gph.PersonalID,
					d.Diag_Code,
					dt.DeseaseType_SysNick,
					EvnDiagPLSop_setDT as setDT
				from
					v_EvnVizitPL evpl (nolock)
					left join v_EvnDiagPLSop edpls (nolock) on edpls.EvnDiagPLSop_pid = evpl.EvnVizitPL_id
					left join v_EvnDiagPLStomSop edplss (nolock) on edplss.EvnDiagPLStomSop_rid = evpl.EvnVizitPL_pid
					inner join v_Diag d (nolock) on d.Diag_id = edpls.Diag_id or d.Diag_id = edplss.Diag_id
					left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = edpls.DeseaseType_id
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
					evpl.EvnVizitPL_pid = :EvnPL_id and 
					evpl.Lpu_id = :Lpu_id
			) as t 
			order by setDT asc
		", array(
			'EvnPL_id' => $EvnPL_id,
			'Lpu_id' => $Lpu_id
		));
		
		$diags = array();
		foreach($resp as $k => $respone) {
			if(in_array($respone['Diag_Code'], $diags)) {
				unset($resp[$k]);
			} else {
				$diags[] = $respone['Diag_Code'];
			}
		}

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
			// Если код диагноза V01-Z99, то НЕ заполняется.
			if(preg_match("#[V-Z]#", substr($respone['Diag_Code'], 0, 1))) {
				$diagtype = null;
			}
			// Если код диагноза от S00 до T88, то 1 Острое
			if(substr($respone['Diag_Code'], 0, 3) >= 'S00' && substr($respone['Diag_Code'], 0, 3) <= 'T88') {
				$diagtype = 1;
			}
			$diagnoses[] = array(
				'Uid' => null,
				'Diagnose' => trim($respone['Diag_Code'], " \t\n\r\0\x0B\."),
				'Doctor' => $respone['PersonalID'],
				'Type' => $diagtype
			);
		}

		return $diagnoses;
	}

	/**
	 * Получение диагнозов скрининга
	 */
	function getDiagnosesCard8Y($EvnPL_id) {
		$diagnoses = array();

		$resp = $this->queryResult("
			select
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				d.Diag_Code,
				dt.DeseaseType_SysNick
			from
				v_EvnUslugaDispDop eudd (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = eudd.Diag_id
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = eudd.DeseaseType_id
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.MOID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = eudd.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				eudd.EvnUslugaDispDop_pid = :EvnPL_id
				and (eudd.SurveyType_id = 19 or d.Diag_Code not like 'Z%')
		", array(
			'EvnPL_id' => $EvnPL_id
		));
		
		$diags = array();
		foreach($resp as $k => $respone) {
			if(in_array($respone['Diag_Code'], $diags)) {
				unset($resp[$k]);
			} else {
				$diags[] = $respone['Diag_Code'];
			}
		}

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
			// Если код диагноза V01-Z99, то НЕ заполняется.
			if(preg_match("#[V-Z]#", substr($respone['Diag_Code'], 0, 1))) {
				$diagtype = null;
			}
			// Если код диагноза от S00 до T88, то 1 Острое
			if(substr($respone['Diag_Code'], 0, 3) >= 'S00' && substr($respone['Diag_Code'], 0, 3) <= 'T88') {
				$diagtype = 1;
			}
			$diagnoses[] = array(
				'Uid' => null,
				'Diagnose' => trim($respone['Diag_Code'], " \t\n\r\0\x0B\."),
				'Doctor' => $respone['PersonalID'],
				'Type' => $diagtype
			);
		}

		return $diagnoses;
	}

	/**
	 * Получение диагнозов
	 */
	function getEvnPLDispScreenDiagnoses($EvnPL_id) {
		$diagnoses = array();

		$resp = $this->queryResult("
			select * from (
				select
					gph.PersonalID,
					d.Diag_Code,
					dt.DeseaseType_SysNick,
					EvnVizitPL_setDT as setDT
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
					
				union
					
				select
					gph.PersonalID,
					d.Diag_Code,
					dt.DeseaseType_SysNick,
					EvnDiagPLSop_setDT as setDT
				from
					v_EvnVizitPL evpl (nolock)
					left join v_EvnDiagPLSop edpls (nolock) on edpls.EvnDiagPLSop_pid = evpl.EvnVizitPL_id
					left join v_EvnDiagPLStomSop edplss (nolock) on edplss.EvnDiagPLStomSop_rid = evpl.EvnVizitPL_pid
					inner join v_Diag d (nolock) on d.Diag_id = edpls.Diag_id or d.Diag_id = edplss.Diag_id
					left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = edpls.DeseaseType_id
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
			) as t 
			order by setDT asc
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		$diags = array();
		foreach($resp as $k => $respone) {
			if(in_array($respone['Diag_Code'], $diags)) {
				unset($resp[$k]);
			} else {
				$diags[] = $respone['Diag_Code'];
			}
		}

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
				'Uid' => null,
				'Diagnose' => trim($respone['Diag_Code'], " \t\n\r\0\x0B\."),
				'Doctor' => $respone['PersonalID'],
				'Type' => $diagtype
			);
		}

		return $diagnoses;
	}

	/**
	 * Получение операций ТАП
	 */
	function getOperations($EvnPL_id, $Lpu_id) {
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
				euo.EvnUslugaOper_rid = :EvnPL_id and 
				euo.Lpu_id = :Lpu_id
		", array(
			'EvnPL_id' => $EvnPL_id,
			'Lpu_id' => $Lpu_id
		));

		foreach($resp as $respone) {
			$operations[] = array(
				'Uid' => null,
				'Date' => $respone['EvnUslugaOper_setDT'],
				'Doctor' => $respone['PersonalID'],
				'Surgery' => $respone['UslugaComplex_Code']
			);
		}

		return $operations;
	}

	/**
	 * Получение диспансеризаций
	 */
	function getDispancers($EvnPL_id) {
		$dispancers = array();

		// делить на два метода и запроса нет смысла, т.к. всё плотно связано
		$resp = $this->queryResult("
			select top 1
				epl.ResultClass_id,
				pd.PersonDisp_begDate,
				pd.PersonDisp_endDate,
				pd.DispGroup_Code,
				ms.p_ID as MedicalStatus_Code,
				pd.DeseaseDispType_id,
				pd.Diag_Code,
				pd.PersonalID,
				pd.DispOutType_Code,
				dt.DeseaseType_SysNick
			from
				v_EvnVizitPL evpl (nolock)
				inner join v_EvnPL epl (nolock) on epl.EvnPL_id = evpl.EvnVizitPL_pid
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = evpl.DeseaseType_id
				left join r101.EvnPlMedicalStatusLink msl (nolock) on msl.EvnPL_id = epl.EvnPL_id
				left join r101.MedicalStatus ms (nolock) on ms.MedicalStatus_id = msl.MedicalStatus_id
				outer apply (
					select top 1
						convert(varchar(10), pd.PersonDisp_begDate, 120) as PersonDisp_begDate,
						convert(varchar(10), pd.PersonDisp_endDate, 120) as PersonDisp_endDate,
						dg.p_code as DispGroup_Code,
						case 
							when pd.DeseaseDispType_id = 1 then 2
							when pd.DeseaseDispType_id = 2 then 1
							else null
						end as DeseaseDispType_id,
						d.Diag_Code,
						gph.PersonalID,
						dotl.p_ID as DispOutType_Code
					from
						v_PersonDisp pd (nolock)
						inner join v_Diag d (nolock) on d.Diag_id = pd.Diag_id
						left join r101.PersonDispGroupLink dgl (nolock) on dgl.PersonDisp_id = pd.PersonDisp_id
						left join r101.DispGroup dg (nolock) on dg.DispGroup_id = dgl.DispGroup_id
						left join r101.DispOutTypeLink dotl (nolock) on dotl.DispOutType_id = pd.DispOutType_id
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
						evpl.PersonDisp_id = pd.PersonDisp_id or
						(
							pd.Person_id = evpl.Person_id and 
							pd.Diag_id = evpl.Diag_id and 
							cast(evpl.EvnVizitPL_setDT as date) between pd.PersonDisp_begDate and isnull(pd.PersonDisp_endDate, dbo.tzGetDate())
						) 
				) pd
			where
				evpl.EvnVizitPL_pid = :EvnPL_id and
				evpl.VizitType_id = 118
			order by 
				evpl.EvnVizitPL_setDT asc
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		foreach($resp as $respone) {
			$diagtype = null;
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
			// Если код диагноза V01-Z99, то НЕ заполняется.
			if(preg_match("#[V-Z]#", substr($respone['Diag_Code'], 0, 1))) {
				$diagtype = null;
			}
			// Если код диагноза от S00 до T88, то 1 Острое
			if(substr($respone['Diag_Code'], 0, 3) >= 'S00' && substr($respone['Diag_Code'], 0, 3) <= 'T88') {
				$diagtype = 1;
			}
			$dispancers[] = array(
				'RdbUid' => null,
				'AisUid' => null,
				'Diagnose' => (object)array(
					'Diagnose' => trim($respone['Diag_Code'], " \t\n\r\0\x0B\."),
					'Doctor' => $respone['PersonalID'],
					'DispGroup' => $respone['DispGroup_Code'],
					'Type' => $diagtype,
					'DateBegin' => $respone['PersonDisp_begDate'],
					'TakingType' => $respone['DeseaseDispType_id'],
					'Reason' => $respone['DispOutType_Code'],
					'DateEnd' => $respone['PersonDisp_endDate']
				),
				'Place' => 1,
				'State' => $respone['MedicalStatus_Code'], // состояние здоровья
				'NextDate' => null,
			);
		}

		return $dispancers;
	}

	/**
	 * Получение скрининговых осмотров детей
	 */
	function getCard7Y($EvnPLDispScreenChild_id) {
		$card7y = null;

		$resp = $this->queryResult("
			with eudd_all as ( -- достаем услуги сразу с результатами исследований и помещаем в отдельную таблицу, чтобы потом использовать ее, а не писать каждый раз запросы
				select
					eudd_all.EvnUslugaDispDop_id,
					eudd_all.SurveyType_id,
					RT.RateType_SysNick,
					R.Rate_ValueStr as value,
					eudd_all.EvnUslugaDispDop_setDT,
					eudd_all.MedStaffFact_id,
					eudd_all.Diag_id,
					eudd_all.DeseaseType_id
				from
					v_EvnUslugaDispDop eudd_all (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = eudd_all.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where
					eudd_all.EvnUslugaDispDop_pid = :EvnPLDispScreenChild_id
			)
			
			select top 1
				epldsc.EvnPLDispScreenChild_id,
				case when agd.AgeGroupDisp_From < 3 then epldsc.EvnPLDispScreenChild_Head end as EvnPLDispScreenChild_Head,
				case when agd.AgeGroupDisp_From < 3 then epldsc.EvnPLDispScreenChild_Breast end as EvnPLDispScreenChild_Breast,
				case when agd.AgeGroupDisp_From >= 7 then case when epldsc.EvnPLDispScreenChild_IsSmoking = 2 then 'true' else 'false' end end as EvnPLDispScreenChild_IsSmoking,
				case when agd.AgeGroupDisp_From >= 7 then case when epldsc.EvnPLDispScreenChild_IsAlco = 2 then 'true' else 'false' end end as EvnPLDispScreenChild_IsAlco,
				case when agd.AgeGroupDisp_From >= 3 then case when epldsc.EvnPLDispScreenChild_IsActivity = 2 then 'true' else 'false' end end as EvnPLDispScreenChild_IsActivity,
				case when agd.AgeGroupDisp_From >= 7 then epldsc.EvnPLDispScreenChild_ArteriaSistolPress end as EvnPLDispScreenChild_ArteriaSistolPress,
				case when agd.AgeGroupDisp_From >= 7 then epldsc.EvnPLDispScreenChild_ArteriaDiastolPress end as EvnPLDispScreenChild_ArteriaDiastolPress,
				ISNULL(epldsc.EvnPLDispScreenChild_IsDecreaseEar, 1) as EvnPLDispScreenChild_IsDecreaseEar,
				ISNULL(epldsc.EvnPLDispScreenChild_IsDecreaseEye, 1) as EvnPLDispScreenChild_IsDecreaseEye,
				case when agd.AgeGroupDisp_From >= 5 then ISNULL(epldsc.EvnPLDispScreenChild_IsFlatFoot, 1) end as EvnPLDispScreenChild_IsFlatFoot,
				epldsc.PsychicalConditionType_id,
				case when agd.AgeGroupDisp_From >= 7 then epldsc.SexualConditionType_id end as SexualConditionType_id,
				case when epldsc.EvnPLDispScreenChild_IsAbuse = 2 then 'true' else 'false' end as EvnPLDispScreenChild_IsAbuse,
				ph.PersonHeight_Height,
				pw.PersonWeight_Weight,
				case when epldsc.EvnPLDispScreenChild_IsHealth = 2 then 'true' else 'false' end as EvnPLDispScreenChild_IsHealth,
				epldsc.HealthKind_id,
				case when epldsc.EvnPLDispScreenChild_IsPMSP = 2 then 'true' else 'false' end as EvnPLDispScreenChild_IsPMSP,
				case when agd.AgeGroupDisp_From >= 16 then igl.sp_InvalidGroup_id end as InvalidGroup_id,
				case when epldsc.EvnPLDispScreenChild_IsInvalid = 2 then 'true' else 'false' end as EvnPLDispScreenChild_IsInvalid,
				epldsc.EvnPLDispScreenChild_YearInvalid,
				epldsc.EvnPLDispScreenChild_InvalidPeriod,
				id.Diag_Code as iDiag_Code
			from
				v_EvnPLDispScreenChild epldsc (nolock)
				left join v_PersonHeight ph (nolock) on ph.Evn_id = epldsc.EvnPLDispScreenChild_id
				left join v_PersonWeight pw (nolock) on pw.Evn_id = epldsc.EvnPLDispScreenChild_id
				left join v_AgeGroupDisp agd (nolock) on agd.AgeGroupDisp_id = epldsc.AgeGroupDisp_id
				left join r101.InvalidGroupLink igl (nolock) on igl.EvnPLDispScreenChild_id = epldsc.EvnPLDispScreenChild_id
				left join v_Diag id (nolock) on id.Diag_id = epldsc.InvalidDiag_id
				
				-- достаем результаты исследований, которые проводились с услугами	
				outer apply (select value from eudd_all where RateType_SysNick = 'el_cardiography') el_cardiography
				outer apply (select value from eudd_all where RateType_SysNick = 'fec_occult_blood') fec_occult_blood
				outer apply (select value from eudd_all where RateType_SysNick = 'blood_cholest_lvl') blood_cholest_lvl
				outer apply (select value from eudd_all where RateType_SysNick = 'blood_sugar_lvl') blood_sugar_lvl
				outer apply (select value from eudd_all where RateType_SysNick = 'uteri_carvix_scrning') uteri_carvix_scrning
				outer apply (select value from eudd_all where RateType_SysNick = 'mammography_scrning') mammography_scrning
				outer apply (select value from eudd_all where RateType_SysNick = 'intraocular_tens') intraocular_tens
				outer apply (select value from eudd_all where RateType_SysNick = 'colposcopy_res') colposcopy_res
				outer apply (select value from eudd_all where RateType_SysNick = 'biopsy_res') biopsy_res
				outer apply (select value from eudd_all where RateType_SysNick = 'coloscopy_res') coloscopy_res
				outer apply (select value from eudd_all where RateType_SysNick = 'mammography_scrning_conducted') mammography_scrning_conducted
				outer apply (select value from eudd_all where RateType_SysNick = 'fec_occult_blood_conducted') fec_occult_blood_conducted
				--
			
			where
				epldsc.EvnPLDispScreenChild_id = :EvnPLDispScreenChild_id
				and epldsc.EvnPLDispScreenChild_IsEndStage = 2
			order by
				epldsc.EvnPLDispScreenChild_setDT desc
		", array(
			'EvnPLDispScreenChild_id' => $EvnPLDispScreenChild_id
		));

		foreach($resp as $respone) {
			$PsychologicalLevel = null;
			switch ($respone['PsychicalConditionType_id']) {
				case 3:
					$PsychologicalLevel = 1;
					break;
				case 4:
					$PsychologicalLevel = 2;
					break;
				case 5:
					$PsychologicalLevel = 3;
					break;
			}

			$Pubescence = null;
			switch ($respone['SexualConditionType_id']) {
				case 3:
					$Pubescence = 1;
					break;
				case 4:
					$Pubescence = 2;
					break;
				case 5:
					$Pubescence = 3;
					break;
			}

			$RiskFactorsTypes = array();
			$resp_rft = $this->queryResult("
				select top 1
					pc.RiskFactorType_id
				from
					v_ProphConsult pc (nolock)
				where
					pc.EvnPLDisp_id = :EvnPLDispScreenChild_id
			", array(
				'EvnPLDispScreenChild_id' => $respone['EvnPLDispScreenChild_id']
			));
			foreach($resp_rft as $one_rft) {
				$RiskFactorsTypes[] = $one_rft['RiskFactorType_id'];
			}


			$HealthGroup = null;
			switch ($respone['HealthKind_id']) {
				case 12:
					$HealthGroup = 1;
					break;
				case 13:
					$HealthGroup = 2;
					break;
				case 14:
					$HealthGroup = 3;
					break;
				case 15:
					$HealthGroup = 4;
					break;
				case 16:
					$HealthGroup = 5;
					break;
			}

			$card7y = array(
				'InvalidGroup' => $respone['InvalidGroup_id'],
				'HeadCircumference' => $respone['EvnPLDispScreenChild_Head'],
				'ChestCircumference' => $respone['EvnPLDispScreenChild_Breast'],
				'ChildSmoking' => $respone['EvnPLDispScreenChild_IsSmoking'],
				'ChildAlcoholism' => $respone['EvnPLDispScreenChild_IsAlco'],
				'DailyActivity' => $respone['EvnPLDispScreenChild_IsActivity'],
				'BloodPressureDown' => $respone['EvnPLDispScreenChild_ArteriaSistolPress'],
				'BloodPressureUp' => $respone['EvnPLDispScreenChild_ArteriaDiastolPress'],
				'BloodPressureDown2' => $respone['EvnPLDispScreenChild_ArteriaSistolPress'],
				'BloodPressureUp2' => $respone['EvnPLDispScreenChild_ArteriaDiastolPress'],
				'Otoacoustic' => null, // не заполнять
				'HearingAcuity' => $respone['EvnPLDispScreenChild_IsDecreaseEar'], // 2 - снижение, 1 - нет
				'VisualAcuity' => $respone['EvnPLDispScreenChild_IsDecreaseEye'], // 2 - снижение, 1 - нет
				'PlantogramResult' => $respone['EvnPLDispScreenChild_IsFlatFoot'], // 2 - плоскостопие, 1 - нет
				'PsychologicalLevel' => $PsychologicalLevel,
				'Pubescence' => $Pubescence,
				'AbuseSign' => $respone['EvnPLDispScreenChild_IsAbuse'],
				'P_frisk1' => in_array('3', $RiskFactorsTypes) ? 'true' : 'false',
				'P_frisk2' => in_array('4', $RiskFactorsTypes) ? 'true' : 'false',
				'P_frisk3' => in_array('7', $RiskFactorsTypes) ? 'true' : 'false',
				'P_frisk4' => in_array('6', $RiskFactorsTypes) ? 'true' : 'false',
				'Uid' => null, // не заполнять
				'Invalid' => $respone['EvnPLDispScreenChild_IsInvalid'],
				'InvalidYear' => $respone['EvnPLDispScreenChild_YearInvalid'],
				'InvalidPeriod' => $respone['EvnPLDispScreenChild_InvalidPeriod'],
				'InvalidDiagnose' => !empty($respone['iDiag_Code']) ? trim($respone['iDiag_Code'], " \t\n\r\0\x0B\.") : null,
				'Height' => !empty($respone['PersonHeight_Height']) ? round($respone['PersonHeight_Height']) : null,
				'Weight' => !empty($respone['PersonWeight_Weight']) ? round($respone['PersonWeight_Weight']) : null,
				'VisitType' => 'П', // в поликлинике (справочник r101.sp_vidpos)
				'ScreenResult' => $respone['EvnPLDispScreenChild_IsHealth'],
				'HealthGroup' => $HealthGroup,
				'NaprKVrach' => $respone['EvnPLDispScreenChild_IsPMSP']
			);
		}

		return $card7y;
	}

	/**
	 * Получение скрининговых осмотров взрослых
	 */
	function getCard8Y($EvnPLDispScreen_id) {
		$card8y = null;

		$resp = $this->queryResult("
			with eudd_all as ( -- достаем услуги сразу с результатами исследований и помещаем в отдельную таблицу, чтобы потом использовать ее, а не писать каждый раз запросы
				select
					eudd_all.EvnUslugaDispDop_id,
					eudd_all.SurveyType_id,
					RT.RateType_SysNick,
					R.Rate_ValueStr as value,
					eudd_all.EvnUslugaDispDop_setDT,
					eudd_all.MedStaffFact_id,
					eudd_all.Diag_id,
					eudd_all.DeseaseType_id
				from
					v_EvnUslugaDispDop eudd_all (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = eudd_all.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where
					eudd_all.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
			)

			select top 1
				eplds.EvnPLDispScreen_id,
				
				-- группа да/нет
				case when eplds.EvnPLDispScreen_IsSmoking = 2 then 'true' when eplds.EvnPLDispScreen_IsSmoking = 1 then 'false' else '' end as EvnPLDispScreen_IsSmoking,
				case when eplds.EvnPLDispScreen_IsAlco = 2 then 'true' when eplds.EvnPLDispScreen_IsAlco = 1 then 'false' else '' end as EvnPLDispScreen_IsAlco,
				case when eplds.EvnPLDispScreen_IsDailyPhysAct = 2 then 'true' when eplds.EvnPLDispScreen_IsDailyPhysAct = 1 then 'false' else '' end as EvnPLDispScreen_IsDailyPhysAct,
				case when eplds.EvnPLDispScreen_IsVisImpair = 2 then 'true' when eplds.EvnPLDispScreen_IsVisImpair = 1 then 'false' else '' end as EvnPLDispScreen_IsVisImpair,
				case when eplds.EvnPLDispScreen_IsBlurVision = 2 then 'true' when eplds.EvnPLDispScreen_IsBlurVision = 1 then 'false' else '' end as EvnPLDispScreen_IsBlurVision,
				case when eplds.EvnPLDispScreen_IsGlaucoma = 2 then 'true' when eplds.EvnPLDispScreen_IsGlaucoma = 1 then 'false' else '' end as EvnPLDispScreen_IsGlaucoma,
				case when eplds.EvnPLDispScreen_IsHighMyopia = 2 then 'true' when eplds.EvnPLDispScreen_IsHighMyopia = 1 then 'false' else '' end as EvnPLDispScreen_IsHighMyopia,
				case when eplds.EvnPLDispScreen_IsAlcoholAbuse = 2 then 'true' when eplds.EvnPLDispScreen_IsAlcoholAbuse = 1 then 'false' else '' end as EvnPLDispScreen_IsAlcoholAbuse,
				case when eplds.EvnPLDispScreen_IsOverweight = 2 then 'true' when eplds.EvnPLDispScreen_IsOverweight = 1 then 'false' else '' end as EvnPLDispScreen_IsOverweight,
				case when eplds.EvnPLDispScreen_IsLowPhysAct = 2 then 'true' when eplds.EvnPLDispScreen_IsLowPhysAct = 1 then 'false' else '' end as EvnPLDispScreen_IsLowPhysAct,
				case when eplds.EvnPLDispScreen_IsGenPredisposed = 2 then 'true' when eplds.EvnPLDispScreen_IsGenPredisposed = 1 then 'false' else '' end as EvnPLDispScreen_IsGenPredisposed,
				case when eplds.EvnPLDispScreen_IsHyperlipidemia = 2 then 'true' when eplds.EvnPLDispScreen_IsHyperlipidemia = 1 then 'false' else '' end as EvnPLDispScreen_IsHyperlipidemia,
				case when eplds.EvnPLDispScreen_IsHyperglycaemia = 2 then 'true' when eplds.EvnPLDispScreen_IsHyperglycaemia = 1 then 'false' else '' end as EvnPLDispScreen_IsHyperglycaemia,
				
				case when eplds.EvnPLDispScreen_IsDisability = 2 then 'true' when eplds.EvnPLDispScreen_IsDisability = 1 then 'false' else '' end as EvnPLDispScreen_IsDisability,
				case when eplds.EvnPLDispScreen_IsParCoronary = 2 then 'true' when eplds.EvnPLDispScreen_IsParCoronary = 1 then 'false' else '' end as EvnPLDispScreen_IsParCoronary,
				case when eplds.EvnPLDispScreen_IsHeartache = 2 then 'true' when eplds.EvnPLDispScreen_IsHeartache = 1 then 'false' else '' end as EvnPLDispScreen_IsHeartache,
				case when eplds.EvnPLDispScreen_IsHeadache = 2 then 'true' when eplds.EvnPLDispScreen_IsHeadache = 1 then 'false' else '' end as EvnPLDispScreen_IsHeadache,
				case when eplds.EvnPLDispScreen_IsHighPressure = 2 then 'true' when eplds.EvnPLDispScreen_IsHighPressure = 1 then 'false' else '' end as EvnPLDispScreen_IsHighPressure,
				case when eplds.EvnPLDispScreen_IsBleeding = 2 then 'true' when eplds.EvnPLDispScreen_IsBleeding = 1 then 'false' else '' end as EvnPLDispScreen_IsBleeding,
				case when eplds.EvnPLDispScreen_IsDirectedPMSP = 2 then 'true' when eplds.EvnPLDispScreen_IsDirectedPMSP = 1 then 'false' else '' end as EvnPLDispScreen_IsDirectedPMSP,
				--
		
				-- инвалидность
				eplds.EvnPLDispScreen_DisabilityYear,
				eplds.EvnPLDispScreen_DisabilityPeriod,
				DD.Diag_Code as DisabilityDiag_Code,
				--
				
				-- результаты исследований
				el_cardiography.value as ElectrocardiographyResult_Code, -- на деле в value лежат id, а не коды, но пока они имеют одинаковые значения
				blood_cholest_lvl.value as BloodCholesterolLevelResult_Code, -- возможно позже надо будет переделать
				blood_sugar_lvl.value as BloodSugarLevelResult_Code,
				intraocular_tens.value as IntraocularTensionRes_Code,
				uteri_carvix_scrning.value as UteriCervixCytoScreening_Code,
				case when colposcopy_res.value = '1'  then 'true' else 'false' end  as ColposcopyResType_Code, -- это не YesNo справочник
				biopsy_res.value as BiopsyResType_Code,
				mammography_scrning.value as MammographyResType_Code,
				mammography_scrning_conducted.value as M_SurveyConductionWay_Code,
				fec_occult_blood.value as FecalOccultBloodResult_Code,
				fec_occult_blood_conducted.value as FOBR_SurveyConductionWay_Code,
				coloscopy_res.value as ColoscopyResType_Code,
				--
				
				-- алкоголь
				eplds.AlcoholIngestType_bid,
				eplds.AlcoholIngestType_wid,
				eplds.AlcoholIngestType_vid,
				--
				
				eplds.EvnPLDispScreen_ArteriaDiastolPress,
				eplds.EvnPLDispScreen_ArteriaSistolPress,
				
				CASE PS.Sex_id
					WHEN 1 THEN CASE WHEN ISNULL(eplds.EvnPLDispScreen_PersonWaist, 93) < 94 THEN 1 ELSE 2 END
					WHEN 2 THEN CASE WHEN ISNULL(eplds.EvnPLDispScreen_PersonWaist, 79) < 80 THEN 1 ELSE 2 END
					END
				as Waist,
				
				-- id 1 или 2 Объём талии. Значение из справочника sp_stat_ObemTaliiMuzh
				eplds.HealthKind_id,
				FC.FecalCasts_Code,
				convert(varchar(10), eudd.EvnUslugaDispDop_setDT, 120) as EvnUslugaDispDop_setDT,
				gph.PersonalID,
				CAST(ph.PersonHeight_Height as INT) as Height,
				CAST(pw.PersonWeight_Weight as INT) as Weight,
				case when eplds.EvnPLDispScreen_IsHealthy = 2 then 'true' else 'false' end as ScreenResult,
				dt.DeseaseType_Code,
				d.Diag_Code
				
			from
				v_EvnPLDispScreen eplds (nolock)
				
				inner join v_PersonState PS (nolock) on PS.Person_id = eplds.Person_id
				
				cross apply (
					select top 1
						EvnUslugaDispDop_setDT,
						MedStaffFact_id,
						Diag_id,
						DeseaseType_id
					from
						eudd_all
					order by EvnUslugaDispDop_setDT asc
				) eudd
				
				outer apply (
					select top 1
						gpw.PersonalID,
						gp.SpecialityID,
						gpw.MOID,
						gpw.FPID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp (nolock) on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = eudd.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				
			-- достаем результаты исследований, которые проводились с услугами	
			outer apply (select value from eudd_all where RateType_SysNick = 'el_cardiography') el_cardiography
			outer apply (select value from eudd_all where RateType_SysNick = 'fec_occult_blood') fec_occult_blood
			outer apply (select value from eudd_all where RateType_SysNick = 'blood_cholest_lvl') blood_cholest_lvl
			outer apply (select value from eudd_all where RateType_SysNick = 'blood_sugar_lvl') blood_sugar_lvl
			outer apply (select value from eudd_all where RateType_SysNick = 'uteri_carvix_scrning') uteri_carvix_scrning
			outer apply (select value from eudd_all where RateType_SysNick = 'mammography_scrning') mammography_scrning
			outer apply (select value from eudd_all where RateType_SysNick = 'intraocular_tens') intraocular_tens
			outer apply (select value from eudd_all where RateType_SysNick = 'colposcopy_res') colposcopy_res
			outer apply (select value from eudd_all where RateType_SysNick = 'biopsy_res') biopsy_res
			outer apply (select value from eudd_all where RateType_SysNick = 'coloscopy_res') coloscopy_res
			outer apply (select value from eudd_all where RateType_SysNick = 'mammography_scrning_conducted') mammography_scrning_conducted
			outer apply (select value from eudd_all where RateType_SysNick = 'fec_occult_blood_conducted') fec_occult_blood_conducted
			--
			
			
			left join v_PersonHeight ph (nolock) on ph.Evn_id = eplds.EvnPLDispScreen_id
			left join v_PersonWeight pw (nolock) on pw.Evn_id = eplds.EvnPLDispScreen_id
			left join v_Diag d (nolock) on d.Diag_id = eudd.Diag_id
			left join v_Diag DD (nolock) on DD.Diag_id = eplds.Diag_disid
			left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = eudd.DeseaseType_id
			left join v_FecalCasts FC (nolock) on FC.FecalCasts_id = eplds.FecalCasts_id
			
			where
				eplds.EvnPLDispScreen_id = :EvnPLDispScreen_id
				and eplds.EvnPLDispScreen_IsEndStage = 2
			order by
				eplds.EvnPLDispScreen_setDT desc
		", array(
			'EvnPLDispScreen_id' => $EvnPLDispScreen_id
		));

		foreach ($resp as $key => $response)
		{
			// стыкуем значения id 8 - 1, 9 - 2 и тд в таблицах sp_stat_GruppadDispNabl и HealthKind
			switch ($response['HealthKind_id'])
			{
				case 8:
				case 9:
				case 10:
				case 11:
					$resp[$key]['HealthKind_id'] = $response['HealthKind_id'] - 7;
					break;
			}
				
		}

		foreach($resp as $respone) {
			$card8y = array(
				'Waist' => $respone['Waist'], // id 1 или 2 Значение из справочника sp_stat_ObemTaliiMuzh
				'Smoking1' => $respone['EvnPLDispScreen_IsSmoking'],
				'Drinking' => $respone['EvnPLDispScreen_IsAlco'],
				'DrinkingBeer' => $this->getSyncSpr('sp_stat_Pivo', $respone['AlcoholIngestType_bid'], true),
				'DrinkingWine' => $this->getSyncSpr('sp_stat_Vino', $respone['AlcoholIngestType_wid'], true),
				'DrinkingAlcohols' => $this->getSyncSpr('sp_stat_Vodka', $respone['AlcoholIngestType_vid'], true),
				'PhysicalActivity' => $respone['EvnPLDispScreen_IsDailyPhysAct'],
				'ParentHeartDisease' => $respone['EvnPLDispScreen_IsParCoronary'],
				'DiseaseAppearance10minut' => $respone['EvnPLDispScreen_IsHeartache'],
				'HasHeadache' => $respone['EvnPLDispScreen_IsHeadache'],
				'HasHypertension' => $respone['EvnPLDispScreen_IsHighPressure'],
				'BloodPressureUp' => $respone['EvnPLDispScreen_ArteriaDiastolPress'],
				'BloodPressureDown' => $respone['EvnPLDispScreen_ArteriaSistolPress'],
				'HasReducedVisualAcuity' => $respone['EvnPLDispScreen_IsVisImpair'],
				'HasBlurring' => $respone['EvnPLDispScreen_IsBlurVision'],
				'HasGlaucoma' => $respone['EvnPLDispScreen_IsGlaucoma'],
				'HasMyopia' => $respone['EvnPLDispScreen_IsHighMyopia'],
				'HasPathologicalStool' => $respone['FecalCasts_Code'],
				'HasContactBleeding' => $respone['EvnPLDispScreen_IsBleeding'],
				'EKG' => $respone['ElectrocardiographyResult_Code'],
				'CholesterolLevel' => $respone['BloodCholesterolLevelResult_Code'],
				'Glucose' => $respone['BloodSugarLevelResult_Code'],
				'GlaucomaResult' => $respone['IntraocularTensionRes_Code'],
				'CytologicalTestResult' => !empty($respone['UteriCervixCytoScreening_Code']) ? str_pad($respone['UteriCervixCytoScreening_Code'], 2, '0', STR_PAD_LEFT) : '', // просто по другому не работает. Обнаружила Маша Пермякова
				'Colposcopy' => $respone['ColposcopyResType_Code'],
				'CervicalBiopsyResult' => !empty($respone['BiopsyResType_Code']) ? str_pad($respone['BiopsyResType_Code'], 2, '0', STR_PAD_LEFT) : '', // просто по другому не работает
				'Mammography' => !empty($respone['MammographyResType_Code']) ? str_pad($respone['MammographyResType_Code'], 2, '0', STR_PAD_LEFT) : '', // просто по другому не работает
				'MammographyIteration' => $respone['M_SurveyConductionWay_Code'],
				'GemokultTestResult' => $respone['FecalOccultBloodResult_Code'],
				'GemokultTestIteration' => $respone['FOBR_SurveyConductionWay_Code'],
				'ColonoscopyResult' => !empty($respone['ColoscopyResType_Code']) ? str_pad($respone['ColoscopyResType_Code'], 2, '0', STR_PAD_LEFT) : '',
				'Esophagoscopy' => null, // todo не передавать
				'FGDS' => null, // todo не передавать
				'PSA1' => null, // todo не передавать
				'PSA2' => null, // todo не передавать
				'ProstateIndex' => null, // todo не передавать
				'ProstateResult' => null, // todo не передавать
				'AlcoholAbuse' => $respone['EvnPLDispScreen_IsAlcoholAbuse'],
				'Overweight' => $respone['EvnPLDispScreen_IsOverweight'],
				'LowPhysicalActivity' => $respone['EvnPLDispScreen_IsLowPhysAct'],
				'GeneticPredisposition' => $respone['EvnPLDispScreen_IsGenPredisposed'],
				'Hyperlipidemia' => $respone['EvnPLDispScreen_IsHyperlipidemia'],
				'Hyperglycemia' => $respone['EvnPLDispScreen_IsHyperglycaemia'],
				'DispancerMonitoringGroup' => $respone['HealthKind_id'],
				'Results' => array(
					array(
						'DatOsm' => $response['EvnUslugaDispDop_setDT'],
						'Doctor' => $response['PersonalID'],
						'Diagnose' => $response['Diag_Code'],
						'Type' => null, //$respone['DeseaseType_Code'],
						'DispancerType' => null, // todo
						'DispancerGroup' => null, // todo
						'DateNextCome' => null, // todo
						'healthState' => null, // todo
						'Place' => null, // todo
						'Reason' => null // todo
					)
				),
				'Uid' => null,
				'Invalid' => $respone['EvnPLDispScreen_IsDisability'],
				'InvalidYear' => $respone['EvnPLDispScreen_DisabilityYear'],
				'InvalidPeriod' => $respone['EvnPLDispScreen_DisabilityPeriod'],
				'InvalidDiagnose' => !empty($respone['DisabilityDiag_Code']) ? trim($respone['DisabilityDiag_Code'], " \t\n\r\0\x0B\.") : null,
				'Height' => $respone['Height'],
				'Weight' => $respone['Weight'],
				'VisitType' => 'П', // в поликлинике (справочник r101.sp_vidpos)
				'ScreenResult' => $respone['ScreenResult'],
				'HealthGroup' => $respone['HealthKind_id'], // используется повторно
				'NaprKVrach' => $respone['EvnPLDispScreen_IsDirectedPMSP']
			);
		}

		return $card8y;
	}

	/**
	 * Получение услуг ТАП
	 */
	function getServices($EvnPL_id, $Lpu_id) {
		$services = array();

		$resp = $this->queryResult("
			declare @curDT date = dbo.tzGetdate();
			select top 50
				convert(varchar(10), isnull(eup.EvnUsluga_setDT, euc.EvnUsluga_setDT), 120) as EvnUslugaCommon_setDT,
				uc.UslugaComplex_Code,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				coalesce(eupar.EvnUslugaPar_NumUsluga, euc.EvnUsluga_Kolvo, 1) as EvnUslugaCommon_Count,
				tc.TreatmentClass_Code,
				st.ServiceType_SysNick,
				sfl.sp_fin2_id,
				euc.EvnClass_id,
				euc.PayType_id
			from
				v_EvnUsluga euc (nolock)
				outer apply (
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
				left join v_EvnUsluga eup (nolock) on eup.EvnUsluga_id = euc.EvnUsluga_pid
				left join v_EvnUslugaPar eupar (nolock) on eupar.EvnUslugaPar_id = euc.EvnUsluga_id
				left join v_Evn EvnParent with(nolock) on EvnParent.Evn_id = euc.EvnUsluga_pid
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = evplfirst.TreatmentClass_id
				left join v_ServiceType st (nolock) on st.ServiceType_id = evplfirst.ServiceType_id
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id
				left join v_MedStaffFact smsf (nolock) on 
					smsf.MedPersonal_id = isnull(euc.MedPersonal_sid,eup.MedPersonal_sid) and 
					smsf.LpuSection_id = isnull(euc.LpuSection_uid,eup.LpuSection_uid)
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
						gphwp.WorkPlace_id = coalesce(euc.MedStaffFact_id,eup.MedStaffFact_id,smsf.MedStaffFact_id)
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				left join r101.sp_fin2Link sfl (nolock) on sfl.PayType_id = euc.PayType_id and 
					isnull(sfl.sp_fin2Link_endDate, @curDT) >= @curDT and
					sfl.sp_fin2Link_begDate <= @curDT
			where
				euc.EvnClass_id in(22,29,47) and 
				(EvnParent.EvnClass_SysNick <> 'EvnUslugaPar' or EvnParent.EvnClass_SysNick is null) and
				euc.EvnUsluga_setDT is not null and
				year(euc.EvnUsluga_setDT) >= 2018 and
				(euc.EvnUsluga_rid = :EvnPL_id or euc.EvnUsluga_pid = :EvnPL_id) and
				euc.Lpu_id = :Lpu_id
		", array(
			'EvnPL_id' => $EvnPL_id,
			'Lpu_id' => $Lpu_id
		));

		foreach($resp as $respone) {
			$VidposKod = 1; // Стат. карта
			// меняется в зависимости от вида посещения и места приёма
			// параклиника только в поликлинике
			if ($respone['EvnClass_id'] == 47) {
				$VidposKod = 1;
			} else if (in_array($respone['ServiceType_SysNick'], array('polka', 'polnmp', 'other'))) {
				$VidposKod = 1; // В поликлинике
			} else if (in_array($respone['ServiceType_SysNick'], array('neotl', 'home', 'ahome'))) {
				$VidposKod = 2; // На дому
			}

			$services[] = array(
				"Uid" => null,
				'Date' => $respone['EvnUslugaCommon_setDT'],
				'Service' => $respone['UslugaComplex_Code'],
				'Doctor' => $respone['PersonalID'],
				'Speciality' => $respone['SpecialityID'],
				'Department' => $respone['FPID'],
				'Count' => $respone['EvnUslugaCommon_Count'],
				'VisitType' => $VidposKod,
				'Leasing' => null,
				'Finans' => $respone['sp_fin2_id'],
				'Confirmed' => true,
				'PaymentType' => $respone['PayType_id'] == 151 ? '1' : '',
				'IsPaid' => false,
				'Result' => "оказана услуга {$respone['UslugaComplex_Code']}"
			);
		}

		return $services;
	}

	/**
	 * Получение услуг осмотра
	 */
	function getCard5YService($EvnPL_id) {
		$services = array();

		$resp = $this->queryResult("
			select top 50
				convert(varchar(10), isnull(eup.EvnUsluga_setDT, euc.EvnUsluga_setDT), 120) as EvnUslugaCommon_setDT,
				uc.UslugaComplex_Code,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID,
				coalesce(eupar.EvnUslugaPar_NumUsluga, euc.EvnUsluga_Kolvo, 1) as EvnUslugaCommon_Count,
				tc.TreatmentClass_Code,
				st.ServiceType_SysNick,
				euc.PayType_id,
				euc.EvnClass_id
			from
				v_EvnUsluga euc (nolock)
				outer apply (
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
				left join v_EvnUsluga eup (nolock) on eup.EvnUsluga_id = euc.EvnUsluga_pid
				left join v_EvnUslugaPar eupar (nolock) on eupar.EvnUslugaPar_id = euc.EvnUsluga_id
				left join v_Evn EvnParent with(nolock) on EvnParent.Evn_id = euc.EvnUsluga_pid
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = evplfirst.TreatmentClass_id
				left join v_ServiceType st (nolock) on st.ServiceType_id = evplfirst.ServiceType_id
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id
				left join v_MedStaffFact smsf (nolock) on 
					smsf.MedPersonal_id = isnull(euc.MedPersonal_sid,eup.MedPersonal_sid) and 
					smsf.LpuSection_id = isnull(euc.LpuSection_uid,eup.LpuSection_uid)
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
						gphwp.WorkPlace_id = coalesce(euc.MedStaffFact_id,eup.MedStaffFact_id,smsf.MedStaffFact_id)
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
			where
				euc.EvnUsluga_setDT is not null and
				year(euc.EvnUsluga_setDT) >= 2018 and
				(euc.EvnUsluga_rid = :EvnPL_id or euc.EvnUsluga_pid = :EvnPL_id) and
				uc.UslugaComplex_id in(4560015, 4560018, 4560019, 4560023, 4560026, 4560028, 4560031, 4560062)
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		foreach($resp as $respone) {
			$services[] = array(
				"Uid" => null,
				'Date' => $respone['EvnUslugaCommon_setDT'],
				'Service' => $respone['UslugaComplex_Code'],
				'Doctor' => $respone['PersonalID'],
				'Speciality' => $respone['SpecialityID'],
				'Department' => $respone['FPID'],
				'Count' => 1,
				'VisitType' => 3,
				'Leasing' => null,
				'PaymentType' => 3,
				'Finans' => 5000,
				'Confirmed' => true,
				'IsPaid' => false,
				'Result' => "оказана услуга {$respone['UslugaComplex_Code']}"
			);
		}

		return $services;
	}


	/**
	 * Получение услуг осмотра для скрининговых исследований
	 */
	function getCard5YServiceForScreen($EvnPL_id) {
		$services = array();

		$resp = $this->queryResult("
			select top 50
				convert(varchar(10), euc.EvnUsluga_setDT, 120) as EvnUslugaCommon_setDT,
				uc.UslugaComplex_Code,
				gph.PersonalID,
				gph.SpecialityID,
				gph.FPID
			from
				v_EvnUsluga euc (nolock)
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
				euc.EvnUsluga_setDT is not null and
				year(euc.EvnUsluga_setDT) >= 2018 and
				(euc.EvnUsluga_rid = :EvnPL_id or euc.EvnUsluga_pid = :EvnPL_id) and
				uc.UslugaComplex_Code <> 'A03.18.001'
		", array(
			'EvnPL_id' => $EvnPL_id
		));

		foreach($resp as $respone) {
			$services[] = array(
				"Uid" => null,
				'Date' => $respone['EvnUslugaCommon_setDT'],
				'Service' => $respone['UslugaComplex_Code'],
				'Doctor' => $respone['PersonalID'],
				'Speciality' => $respone['SpecialityID'],
				'Department' => $respone['FPID'],
				'Count' => 1,
				'VisitType' => 3,
				'Leasing' => null,
				'PaymentType' => 3,
				'Finans' => 5000,
				'Confirmed' => true,
				'IsPaid' => false,
				'Result' => "оказана услуга {$respone['UslugaComplex_Code']}"
			);
		}

		return $services;
	}

	/**
	 * Отправка всех закрытых ТАП
	 */
	function syncAll($data) {
		$this->load->model('ServiceList_model');
		$ServiceList_id = 20;
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
		$ais_reporting_period = $this->Options_model->getOptionsGlobals($data, 'ais_reporting_period');

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			$queryParams = array();
			$filter = "";
			$filter2 = "";
			$filter3 = "";
			$filter4 = "";
			$allowedLpuIdsList = '' . implode(',', $this->getAllowedLpuIds());
			$notAllowedLpuIdsList = '' . implode(',', $this->getNotAllowedLpuIds());

			$allowedLpuIdsList = mb_strlen($allowedLpuIdsList) > 0 ? $allowedLpuIdsList : 'NULL';
			$notAllowedLpuIdsList = mb_strlen($notAllowedLpuIdsList) > 0 ? $notAllowedLpuIdsList : '';

			if (!empty($data['Evn_id'])) {
				$queryParams = array(
					'Evn_id' => $data['Evn_id']
				);
				$filter = " and epl.EvnPL_id = :Evn_id";
				$filter2 = " and eup.EvnUslugaPar_id = :Evn_id";
				$filter3 = " and epldsc.EvnPLDispScreenChild_id = :Evn_id";
				$filter4 = ' and eplds.EvnPLDispScreen_id = :Evn_id';
			}
			
			// https://redmine.swan.perm.ru/issues/124443#note-8
			// Решено на начальном этапе запустить сервис только на двух МО: ГКП на ПХВ "Городская поликлиника №3" УЗ акимата ЗКО и ГКП на ПХВ «Областная клиническая больница» УЗ акимата ЗКО
			//$filter .= " and epl.Lpu_id in(13004176,102)";
			//$filter2 .= " and eup.Lpu_id in(13004176,102)";
			//$filter3 .= " and epldsc.Lpu_id in(13004176,102)";
			//$filter4 .= ' and eplds.Lpu_id in(13004176,102)';

			if (mb_strlen($notAllowedLpuIdsList) > 0)
			{
				$filter .= " and epl.Lpu_id not in ($notAllowedLpuIdsList)";
				$filter2 .= " and eup.Lpu_id not in ($notAllowedLpuIdsList)";
				$filter3 .= " and epldsc.Lpu_id not in ($notAllowedLpuIdsList)";
				$filter4 .= " and eplds.Lpu_id not in ($notAllowedLpuIdsList)";
			}

			// МО не находится в списке только для 25-9, и находится в разрешенном для выгрузки списке или не существует направление из другой МО
				$filter .= " and (epl.Lpu_id in($allowedLpuIdsList) )"; //or not exists ({$this->checkDirectionFromAnotherMo()}) )";
				$filter2 .= " and (eup.Lpu_id in($allowedLpuIdsList) )"; //or not exists ({$this->checkDirectionFromAnotherMo()}) )";
				$filter3 .= " and (epldsc.Lpu_id in($allowedLpuIdsList) )"; //or not exists ({$this->checkDirectionFromAnotherMo()}) )";
				$filter4 .= " and (eplds.Lpu_id in($allowedLpuIdsList) )"; //or not exists ({$this->checkDirectionFromAnotherMo()}) )";

			$queryParams['period'] = empty($ais_reporting_period) ? 1 : intval($ais_reporting_period);

			$query = "
				declare @date date = dbo.tzGetDate();
				declare @day int = DAY(@date);
				declare @datestart date;

				if @day < 6	set @datestart = dateadd(month, -:period, @date);
				else set @datestart = dateadd(month, -:period+1, @date);

				set @datestart = dateadd(month, datediff(month, 0, @datestart), 0);
				if(year(@datestart) < year(@date)) set @datestart = DATEADD(yy, DATEDIFF(yy, 0, @date), 0);
				
				select
					epl.EvnPL_id as Evn_id,
					epl.EvnClass_id
				from
					v_EvnPL epl (nolock)
					left join r101.AISResponse air (nolock) on air.Evn_id = epl.EvnPL_id
					inner join v_Person p (nolock) on p.Person_id = epl.Person_id
				where
					epl.EvnPL_IsFinish = 2 and
					epl.EvnClass_id in(3,6) and 
					p.BDZ_id is not null and
					air.AISResponse_id is null and
					year(epl.EvnPL_setDate) >= 2018 and
					epl.EvnPL_setDate >= @datestart
					{$filter}
					
				union all
				
				select
					eup.EvnUslugaPar_id as Evn_id,
					eup.EvnClass_id
				from
					v_EvnUslugaPar eup (nolock)
					left join EvnUsluga epl on epl.EvnUsluga_id = eup.EvnUslugaPar_id
					left join r101.AISResponse air (nolock) on air.Evn_id = eup.EvnUslugaPar_id
					inner join v_Person p (nolock) on p.Person_id = eup.Person_id
				where
					eup.EvnUslugaPar_setDate is not null and 
					eup.EvnUslugaPar_pid is null and
					p.BDZ_id is not null and
					air.AISResponse_id is null and
					year(eup.EvnUslugaPar_setDate) >= 2018 and
					eup.EvnUslugaPar_setDate >= @datestart
					{$filter2}
					
				union all
				
				select
					epldsc.EvnPLDispScreenChild_id as Evn_id,
					epldsc.EvnClass_id
				from
					v_EvnPLDispScreenChild epldsc (nolock)
					left join EvnPL epl on epl.EvnPL_id = epldsc.EvnPLDispScreenChild_id
					left join r101.AISResponse air (nolock) on air.Evn_id = epldsc.EvnPLDispScreenChild_id
					inner join v_Person p (nolock) on p.Person_id = epldsc.Person_id
				where
					epldsc.EvnPLDispScreenChild_IsEndStage = 2 and
					epldsc.EvnPLDispScreenChild_setDate is not null and 
					p.BDZ_id is not null and
					air.AISResponse_id is null and
					year(epldsc.EvnPLDispScreenChild_setDate) >= 2018 and
					epldsc.EvnPLDispScreenChild_setDate >= @datestart
					{$filter3}
					
				union all
				
				select
					eplds.EvnPLDispScreen_id as Evn_id,
					eplds.EvnClass_id
				from
					v_EvnPLDispScreen eplds (nolock)
					left join r101.AISResponse air (nolock) on air.Evn_id = eplds.EvnPLDispScreen_id
					left join EvnPL epl on epl.EvnPL_id = eplds.EvnPLDispScreen_id
					inner join v_Person p (nolock) on p.Person_id = eplds.Person_id
				where
					eplds.EvnPLDispScreen_IsEndStage = 2 and
					eplds.EvnPLDispScreen_setDate is not null and 
					p.BDZ_id is not null and
					air.AISResponse_id is null and
					year(eplds.EvnPLDispScreen_setDate) >= 2018 and
					eplds.EvnPLDispScreen_setDate >= @datestart
					{$filter4}
			";
			$resp = $this->queryResult($query, $queryParams);
			foreach ($resp as $respone) {
				try {
					switch($respone['EvnClass_id']) {
						case 187:
							$this->syncEvnPLDispScreenChild($respone['Evn_id']);
							break;
						case 183:
							$this->syncEvnPLDispScreen($respone['Evn_id']);
							break;
						default:
							$this->syncEvnPL($respone['Evn_id'], $respone['EvnClass_id']);
					}
				} catch (Exception $e) {
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
				ED.EvnDirection_id = epl.EvnDirection_id AND
				ISNULL(ED.Lpu_sid, ED.Lpu_id) <> ED.Lpu_did AND
				EXISTS (
				
					SELECT TOP 1
						LpuDispContract_id
					FROM
						v_LpuDispContract LDC with (nolock)
					WHERE
							( ISNULL(ED.Lpu_sid, ED.Lpu_id) = LDC.Lpu_id AND ED.Lpu_did = LDC.Lpu_oid) OR
							( ISNULL(ED.Lpu_sid, ED.Lpu_id) = LDC.Lpu_oid AND ED.Lpu_did = LDC.Lpu_id)
				)
		";

		return $query;
	}

	/**
	 * Метод возвращаев массив с id разрашенных для выгрузки МО
	 *
	 * @return array
	 */
	function getAllowedLpuIds()
	{
		return $this->lpu255and259list;
	}

	/**
	 * Метод возвращаев массив с id МО, из которых ТАП не должны выгружаться в 25-5
	 *
	 * @return array
	 */
	function getNotAllowedLpuIds()
	{
		return $this->lpu259list;
	}
}