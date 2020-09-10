<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnPrescrAbstract_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 * 
 * Модель назначения "Консультационная услуга"
 *
 * Назначения с типом "Консультационная услуга" хранятся в таблицах Evn, EvnPrescr, EvnPrescrConsUsluga
 * В назначении должна быть указана только одна услуга.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property CI_DB_driver $db
 */
class EvnPrescrConsUsluga_model extends EvnPrescrAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId()
	{
		return 13;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName()
	{
		return "EvnPrescrConsUsluga";
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario)
	{
		$rules = [];
		switch ($scenario) {
			case "doSave":
				$rules = [
					["field" => "parentEvnClass_SysNick", "label" => "Системное имя учетного документа", "rules" => "", "default" => "EvnSection", "type" => "string"],
					["field" => "signature", "label" => "Признак для подписания", "rules" => "", "type" => "int"],
					["field" => "EvnPrescrConsUsluga_id", "label" => "Идентификатор назначения", "rules" => "", "type" => "id"],
					["field" => "EvnPrescrConsUsluga_pid", "label" => "Идентификатор учетного документа", "rules" => "required", "type" => "id"],
					["field" => "UslugaComplex_id", "label" => "Услуга", "rules" => "required", "type" => "id"],
					["field" => "EvnPrescrConsUsluga_setDate", "label" => "Плановая дата", "rules" => "required", "type" => "date"],
					["field" => "EvnPrescrConsUsluga_IsCito", "label" => "Cito", "rules" => "", "type" => "string"],
					["field" => "EvnPrescrConsUsluga_Descr", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "DopDispInfoConsent_id", "label" => "Идентификатор согласия карты диспансеризации", "rules" => "", "type" => "id"],
					["field" => "PersonEvn_id", "label" => "Идентификатор человека", "rules" => "required", "type" => "id"],
					["field" => "Server_id", "label" => "Идентификатор сервера", "rules" => "required", "type" => "int"],
					["field" => "MedService_id", "label" => "Служба", "rules" => "", "type" => "id"],
				];
				break;
			case "doLoad":
				$rules[] = [
					"field" => "EvnPrescrConsUsluga_id",
					"label" => "Идентификатор назначения",
					"rules" => "required",
					"type" => "id"
				];
				break;
		}
		return $rules;
	}

	/**
	 * Сохранение назначения
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array|bool
	 * @throws Exception
	 */
	public function doSave($data = [], $isAllowTransaction = true)
	{
		$lData = [];
		//getAllData() возвращает данные в lower case
		//так что для корректных проверок проще ключи
		//входных данных тоже привести к lower case
		foreach ($data as $k => $v) {
			$lData[strtolower($k)] = $v;
		}

		$data = $lData;

		if (empty($data["evnprescrconsusluga_id"])) {
			$action = "ins";
			$data["evnprescrconsusluga_id"] = null;
			$data["prescriptionstatustype_id"] = 1;
		} else {
			$action = "upd";
			try {
				$o_data = $this->getAllData($data["evnprescrconsusluga_id"]);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
			foreach ($o_data as $k => $v) {
				if (!array_key_exists($k, $data)) {
					$data[$k] = $v;
				}
			}
		}
		$selectString = "
			evnprescrconsusluga_id as \"EvnPrescrConsUsluga_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from p_EvnPrescrConsUsluga_{$action}(
			    evnprescrconsusluga_id := :evnprescrconsusluga_id,
			    evnprescrconsusluga_pid := :evnprescrconsusluga_pid,
			    lpu_id := :lpu_id,
			    server_id := :server_id,
			    personevn_id := :personevn_id,
			    evnprescrconsusluga_setdt := :evnprescrconsusluga_setdate,
			    prescriptiontype_id := :prescriptiontype_id,
			    evnprescrconsusluga_iscito := :evnprescrconsusluga_iscito,
			    prescriptionstatustype_id := :prescriptionstatustype_id,
			    evnprescrconsusluga_descr := :evnprescrconsusluga_descr,
			    dopdispinfoconsent_id := :dopdispinfoconsent_id,
			    medservice_id := :medservice_id,
			    uslugacomplex_id := :uslugacomplex_id,
			    pmuser_id := :pmuser_id
			);		
		";
		$data["evnprescrconsusluga_iscito"] = (empty($data["evnprescrconsusluga_iscito"]) || $data["evnprescrconsusluga_iscito"] != "on") ? 1 : 2;
		$data["medservice_id"] = (!empty($data["medservice_id"])) ? $data["medservice_id"] : null;
		$data["prescriptiontype_id"] = $this->getPrescriptionTypeId();
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$trans_result = $result->result("array");
		if (!empty($trans_result) && !empty($trans_result[0]) && !empty($trans_result[0]["EvnPrescrConsUsluga_id"]) && empty($trans_result[0]["Error_Msg"])) {
			return $trans_result;
		}
		return false;
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data
	 * @return array|bool
	 */
	public function doLoad($data)
	{
		$query = "
			select
				case when ep.PrescriptionStatusType_id = 1 then 'edit' else 'view' end as \"accessType\",
				ep.EvnPrescrConsUsluga_Descr as \"EvnPrescrConsUsluga_Descr\",
				ep.EvnPrescrConsUsluga_id as \"EvnPrescrConsUsluga_id\",
				ep.EvnPrescrConsUsluga_pid as \"EvnPrescrConsUsluga_pid\",
				to_char(ep.EvnPrescrConsUsluga_setDT, '{$this->dateTimeForm104}') as \"EvnPrescrConsUsluga_setDate\",
				ep.UslugaComplex_id as \"UslugaComplex_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				null as \"EvnPrescrConsUsluga_uslugaList\",
				case when coalesce(ep.EvnPrescrConsUsluga_IsCito, 1) = 1 then 'off' else 'on' end as \"EvnPrescrConsUsluga_IsCito\",
				ep.PersonEvn_id as \"PersonEvn_id\",
				ep.Server_id as \"Server_id\"
			from
				v_EvnPrescrConsUsluga ep
				left join lateral (
					select ED.EvnDirection_id
					from
						v_EvnPrescrDirection epd
						inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
							and ED.EvnDirection_failDT is null
							and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrConsUsluga_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) as ED on true
			where EvnPrescrConsUsluga_id = :EvnPrescrConsUsluga_id
			limit 1
		";
		$queryParams = ["EvnPrescrConsUsluga_id" => $data["EvnPrescrConsUsluga_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 * @param $section
	 * @param $evn_pid
	 * @param $sessionParams
	 * @return array|bool
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams)
	{
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		if ($sysnick) {
			$accessType = "
				case when {$sysnick}.Lpu_id = :Lpu_id AND coalesce({$sysnick}.{$sysnick}_IsSigned, 1) = 1
					then 'edit'
					else 'view'
				end as \"accessType\"
			";
			$addJoin = "
				left join v_{$sysnick} {$sysnick} on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid
			";
		} else {
			$accessType = "
				'view' as \"accessType\"
			";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id as \"EvnPrescr_id\",
			    EP.EvnPrescr_pid as \"EvnPrescr_pid\",
			    EP.EvnPrescr_rid as \"EvnPrescr_rid\",
			    EP.MedService_id as \"MedService_id\",
			    to_char(EP.EvnPrescr_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
			    null as \"EvnPrescr_setTime\",
			    coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\",
			    case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
			    case when 2 = EP.EvnPrescr_IsExec
					then to_char(EP.EvnPrescr_updDT, '$this->dateTimeForm104')||' '||to_char(EP.EvnPrescr_updDT, '$this->dateTimeForm108') else null
				end as \"EvnPrescr_execDT\",
			    EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_Code\",
			    coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
			    coalesce(EP.EvnPrescr_Descr, '') as \"EvnPrescr_Descr\",
			    case when ED.EvnDirection_id is null or coalesce(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as \"EvnPrescr_IsDir\",
			    case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 or EQ.QueueFailCause_id > 0 ) then 12 else ED.EvnStatus_id end as \"EvnStatus_id\",
			    case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 or EQ.QueueFailCause_id > 0 ) then 'Отменено' else EvnStatus.EvnStatus_Name end as \"EvnStatus_Name\",
			    coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as \"EvnStatusCause_Name\",
			    to_char(coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), '{$this->dateTimeForm104}') as \"EvnDirection_statusDate\",
			    ESH.EvnStatusCause_id as \"EvnStatusCause_id\",
			    ED.DirFailType_id as \"DirFailType_id\",
			    EQ.QueueFailCause_id as \"QueueFailCause_id\",
			    ESH.EvnStatusHistory_Cause as \"EvnStatusHistory_Cause\",
			    ED.EvnDirection_id as \"EvnDirection_id\",
			    EQ.EvnQueue_id as \"EvnQueue_id\",
			    case when ED.EvnDirection_Num is null then '' else ED.EvnDirection_Num::varchar end as \"EvnDirection_Num\",
			    case
					when TTMS.TimetableMedService_id is not null then coalesce(MS.MedService_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
								then coalesce(MS.MedService_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							when MS.MedService_id is not null and MS.LpuSection_id is not null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							else coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
						end||' / '||coalesce(Lpu.Lpu_Nick, '')
				else '' end as \"RecTo\",
			    case
					when TTMS.TimetableMedService_id is not null then coalesce(to_char(TTMS.TimetableMedService_begTime, '{$this->dateTimeForm104}'), '')||' '||coalesce(to_char(TTMS.TimetableMedService_begTime, '{$this->dateTimeForm108}'), '')
					when EQ.EvnQueue_id is not null then 'В очереди с '||coalesce(to_char(EQ.EvnQueue_setDate, '{$this->dateTimeForm104}'), '')
					else ''
			    end as \"RecDate\",
				case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
					else ''
				end as \"timetable\",
			    coalesce(CAST(TTMS.TimetableMedService_id as text), CAST(EQ.EvnQueue_id as text), '') as \"timetable_id\",
			    EP.EvnPrescr_pid as \"timetable_pid\",
			    LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
			    DT.DirType_Code as \"DirType_Code\",
			    UC.UslugaComplex_id as \"UslugaComplex_id\",
			    UC.UslugaComplex_2011id as \"UslugaComplex_2011id\",
			    UC.UslugaComplex_Code as \"UslugaComplex_Code\",
			    UC.UslugaComplex_Name as \"UslugaComplex_Name\",
			    null as \"TableUsluga_id\",
			    case 
					when ((ED.Lpu_did is not null and ED.Lpu_did <> LpuSession.Lpu_id) or (EPCU.Lpu_id is not null and EPCU.Lpu_id <> LpuSession.Lpu_id)) then 2 else 1
				end as \"otherMO\",
			    EvnStatus.EvnStatus_SysNick as \"EvnStatus_SysNick\",
			    EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
			    EPCU.Lpu_id as \"Lpu_id\"
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrConsUsluga EPCU on EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPCU.UslugaComplex_id
				left join lateral (
					select
						ED.EvnDirection_id,
					    coalesce(ED.Lpu_sid, ED.Lpu_id) Lpu_id,
					    ED.EvnQueue_id,
					    ED.EvnDirection_Num,
					    ED.EvnDirection_IsAuto,
					    ED.LpuSection_did,
					    ED.LpuUnit_did,
					    ED.Lpu_did,
					    ED.MedService_id,
					    ED.LpuSectionProfile_id,
					    ED.DirType_id,
					    ED.EvnStatus_id,
					    ED.EvnDirection_statusDate,
					    ED.DirFailType_id,
					    ED.EvnDirection_failDT
					from
						v_EvnPrescrDirection epd
						inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when coalesce(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end,
						epd.EvnPrescrDirection_insDT desc
				    limit 1
				) as ED on true
				left join lateral (
					select
					       TimetableMedService_id,
					       TimetableMedService_begTime
					from v_TimetableMedService_lite TTMS
					where TTMS.EvnDirection_id = ED.EvnDirection_id
				    limit 1
				) as TTMS on true
				left join lateral (
					select
					       EQ.EvnQueue_id,
					       EQ.LpuUnit_did,
					       EQ.LpuSectionProfile_did,
					       Lpu_id,
					       EQ.EvnQueue_setDate,
					       EQ.EvnQueue_failDT,
					       EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					  and EQ.EvnQueue_recDT is null
					union all
					select
					       EQ.EvnQueue_id,
					       EQ.LpuUnit_did,
					       EQ.LpuSectionProfile_did,
					       Lpu_id,
					       EQ.EvnQueue_setDate,
					       EQ.EvnQueue_failDT,
					       EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnQueue_id = ED.EvnQueue_id
					  and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					  and EQ.EvnQueue_failDT is null
					limit 1
				) as EQ on true
				left join lateral (
					select
					    ESH.EvnStatus_id,
					    ESH.EvnStatusCause_id,
					    ESH.pmUser_insID,
					    ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH
					where ESH.Evn_id = ED.EvnDirection_id
					  and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
					limit 1
				) as ESH on true
				left join EvnStatus on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join EvnStatusCause on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join v_EvnUslugaPar EUP on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did, EQ.LpuUnit_did, MS.LpuUnit_id) = LU.LpuUnit_id
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id, EQ.LpuSectionProfile_did, LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Lpu LpuSession on LpuSession.Lpu_id = :Lpu_id
				left join lateral (
					select
						EvnUsluga_id,
					    EvnUsluga_setDT
					from v_EvnUsluga
					where EP.EvnPrescr_IsExec = 2
					  and UC.UslugaComplex_id is not null
					  and EvnPrescr_id = EP.EvnPrescr_id
					limit 1
				) as EU on true
				{$addJoin}
			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			  and EP.PrescriptionType_id = 13
			  and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
		";
		$queryParams = [
			"EvnPrescr_pid" => $evn_pid,
			"Lpu_id" => $sessionParams["lpu_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");
		$response = [];
		foreach ($tmp_arr as $i => $row) {
			$row["UslugaId_List"] = $row["UslugaComplex_id"];
			if ($this->options["prescription"]["enable_show_service_code"]) {
				$row["Usluga_List"] = $row["UslugaComplex_Code"] . " " . $row["UslugaComplex_Name"];
			} else {
				$row["Usluga_List"] = $row["UslugaComplex_Name"];
			}
			$row[$section . "_id"] = $row["EvnPrescr_id"] . "-0";
			$response[] = $row;
		}
		return $response;
	}
}