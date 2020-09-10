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
 * Модель назначения "Оперативное лечение"
 *
 * Назначения с типом "Оперативное лечение" хранятся в таблицах EvnPrescrOper, EvnPrescrOperUsluga
 * В назначении должна быть указана одна услуга или более.
 * Для каждой услуги создается запись в таблице EvnPrescrOperUsluga.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property CI_DB_driver $db
 */
class EvnPrescrOper_model extends EvnPrescrAbstract_model
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
		return 7;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName()
	{
		return "EvnPrescrOper";
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario)
	{
		$rules = array();
		switch ($scenario) {
			case "doSave":
				$rules = [
					["field" => "parentEvnClass_SysNick", "label" => "Системное имя род.события", "rules" => "", "default" => "EvnSection", "type" => "string"],
					["field" => "signature", "label" => "Признак для подписания", "rules" => "", "type" => "int"],
					["field" => "EvnPrescrOper_id", "label" => "Идентификатор назначения", "rules" => "", "type" => "id"],
					["field" => "EvnPrescrOper_pid", "label" => "Идентификатор род.события", "rules" => "required", "type" => "id"],
					["field" => "EvnPrescrOper_uslugaList", "label" => "Выбранные услуги", "rules" => "required", "type" => "string"],
					["field" => "EvnPrescrOper_setDate", "label" => "Плановая дата", "rules" => "", "type" => "date"],
					["field" => "EvnPrescrOper_IsCito", "label" => "Cito", "rules" => "", "type" => "string"],
					["field" => "EvnPrescrOper_Descr", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "PersonEvn_id", "label" => "Идентификатор", "rules" => "required", "type" => "id"],
					["field" => "Server_id", "label" => "Идентификатор сервера", "rules" => "required", "type" => "int"]
				];
				break;
			case "doLoad":
				$rules[] = [
					"field" => "EvnPrescrOper_id",
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
		$action = (empty($data["EvnPrescrOper_id"])) ? "ins" : "upd";
		if (empty($data["EvnPrescrOper_id"])) {
			$data["EvnPrescrOper_id"] = null;
			$data["PrescriptionStatusType_id"] = 1;
		} else {
			$o_data = $this->getAllData($data["EvnPrescrOper_id"]);
			if (!empty($o_data["Error_Msg"])) {
				return [$o_data];
			}
			foreach ($o_data as $k => $v) {
				if (!array_key_exists($k, $data)) {
					$data[$k] = $v;
				}
			}
		}
		$selectString = "
			evnprescroper_id as \"EvnPrescrOper_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from p_evnprescroper_{$action}(
			    evnprescroper_id := :EvnPrescrOper_id,
			    evnprescroper_pid := :EvnPrescrOper_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnprescroper_setdt := :EvnPrescrOper_setDT,
			    prescriptiontype_id := :PrescriptionType_id,
			    evnprescroper_iscito := :EvnPrescrOper_IsCito,
			    prescriptionstatustype_id := :PrescriptionStatusType_id,
			    evnprescroper_descr := :EvnPrescrOper_Descr,
			    pmuser_id := :pmUser_id
			);
		";
		$data["EvnPrescrOper_setDT"] = null;
		if (!empty($data["EvnPrescrOper_setDate"])) {
			$data["EvnPrescrOper_setDT"] = $data["EvnPrescrOper_setDate"];
		}
		$data["EvnPrescrOper_IsCito"] = (empty($data["EvnPrescrOper_IsCito"]) || $data["EvnPrescrOper_IsCito"] != "on") ? 1 : 2;
		$data["PrescriptionType_id"] = $this->getPrescriptionTypeId();
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$trans_result = $result->result("array");
		if (empty($trans_result) || empty($trans_result[0]) || empty($trans_result[0]["EvnPrescrOper_id"]) || !empty($trans_result[0]["Error_Msg"])) {
			return $trans_result;
		}
		$uslugalist = [];
		if (!empty($data['EvnPrescrOper_uslugaList'])) {
			$uslugalist = explode(",", $data["EvnPrescrOper_uslugaList"]);
			if (empty($uslugalist) || !is_numeric($uslugalist[0])) {
				$trans_result[0]["Error_Msg"] = "Ошибка формата списка услуг";
			} else {
				$res = $this->clearEvnPrescrOperUsluga(["EvnPrescrOper_id" => $trans_result[0]["EvnPrescrOper_id"]]);
				if (empty($res)) {
					$trans_result[0]["Error_Msg"] = "Ошибка запроса при списка выбранных услуг";
				}
				if (!empty($res) && !empty($res[0]) && !empty($res[0]["Error_Msg"])) {
					$trans_result[0]["Error_Msg"] = $res[0]["Error_Msg"];
				}
			}
		}
		if (!empty($uslugalist)) {
			foreach ($uslugalist as $d) {
				$res = $this->saveEvnPrescrOperUsluga([
					"UslugaComplex_id" => $d,
					"EvnPrescrOper_id" => $trans_result[0]["EvnPrescrOper_id"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (empty($res)) {
					$trans_result[0]["Error_Msg"] = "Ошибка запроса при сохранении услуги";
					break;
				}
				if (!empty($res) && !empty($res[0]) && !empty($res[0]["Error_Msg"])) {
					$trans_result[0]["Error_Msg"] = $res[0]["Error_Msg"];
					break;
				}
			}
		}
		return $trans_result;
	}

	/**
	 * Метод очистки списка услуг
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function clearEvnPrescrOperUsluga($data)
	{
		return $this->clearEvnPrescrTable([
			"object" => "EvnPrescrOperUsluga",
			"fk_pid" => "EvnPrescrOper_id",
			"pid" => $data["EvnPrescrOper_id"]
		]);
	}

	/**
	 * Метод сохранения услуг списка
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnPrescrOperUsluga($data)
	{
		$code = !empty($data['EvnPrescrOperUsluga_id']) ? "upd" : "ins";
		$selectString = "
			evnprescroperusluga_id as \"EvnPrescrOperUsluga_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from p_evnprescroperusluga_{$code}(
			    evnprescroperusluga_id := :EvnPrescrOperUsluga_id,
			    evnprescroper_id := :EvnPrescrOper_id,
			    uslugacomplex_id := :UslugaComplex_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPrescrOperUsluga_id" => (empty($data["EvnPrescrOperUsluga_id"]) ? NULL : $data["EvnPrescrOperUsluga_id"]),
			"EvnPrescrOper_id" => $data["EvnPrescrOper_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data)
	{
		$query = "
			select
				case when EP.PrescriptionStatusType_id = 1 then 'edit' else 'view' end as \"accessType\",
				EP.EvnPrescrOper_id as \"EvnPrescrOper_id\",
				EP.EvnPrescrOper_pid as \"EvnPrescrOper_pid\",
				EPU.UslugaComplex_id as \"UslugaComplex_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				to_char(EP.EvnPrescrOper_setDT, '{$this->dateTimeForm104}') as \"EvnPrescrOper_setDate\",
				EP.EvnPrescrOper_Descr as \"EvnPrescrOper_Descr\",
				case when coalesce(EP.EvnPrescrOper_IsCito, 1) = 1 then 'off' else 'on' end as \"EvnPrescrOper_IsCito\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Server_id as \"Server_id\"
			from
				v_EvnPrescrOper EP
				inner join v_EvnPrescrOperUsluga EPU on EP.EvnPrescrOper_id = EPU.EvnPrescrOper_id
				left join lateral (
					select ED.EvnDirection_id
					from
				    	v_EvnPrescrDirection epd
						inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
							and ED.EvnDirection_failDT is null
							and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrOper_id
					order by epd.EvnPrescrDirection_insDT desc
				    limit 1
				) as ED on true
			where EP.EvnPrescrOper_id = :EvnPrescrOper_id
		";
		$queryParams = ["EvnPrescrOper_id" => $data["EvnPrescrOper_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");;
		if (count($tmp_arr) == 0) {
			return $tmp_arr;
		}
		$response = [];
		$uslugalist = [];
		foreach ($tmp_arr as $row) {
			if (!empty($row["UslugaComplex_id"])) {
				$uslugalist[] = $row["UslugaComplex_id"];
			}
		}
		$response[0] = $tmp_arr[0];
		$response[0]["EvnPrescrOper_uslugaList"] = implode(",", $uslugalist);
		return $response;
	}

	/**
	 * Возвращает данные для шаблона print_evnprescroper_list
	 * @param $data
	 * @return array
	 */
	function getPrintData($data)
	{
		$query = "
			select
				Oper.EvnPrescrOper_id as \"EvnPrescrOper_id\",
			    UC.UslugaComplex_Name as \"UslugaComplex_Name\",
			    (
			        select count(EvnPrescrOperUsluga_id)
			        from v_EvnPrescrOperUsluga
			        where EvnPrescrOper_id = Oper.EvnPrescrOper_id
			    ) as \"cntUsluga\"
			from
				v_EvnPrescrOper Oper
				inner join v_EvnPrescrOperUsluga EPOU on EPOU.EvnPrescrOper_id = Oper.EvnPrescrOper_id
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EPOU.UslugaComplex_id
			where Oper.EvnPrescrOper_pid = :Evn_pid and Oper.PrescriptionStatusType_id != 3
			order by Oper.EvnPrescrOper_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		$response = [];
		if (is_object($result)) {
			$tmp = $result->result("array");
			$cnt = 0;
			foreach ($tmp as $row) {
				if ($cnt == 0) {
					$usluga_list = [];
				}
				$usluga_list[] = $row["UslugaComplex_Name"];
				$cnt++;
				if ($cnt == $row["cntUsluga"]) {
					$response[] = ["UslugaComplex_Name_List" => implode(", ", $usluga_list)];
					$cnt = 0;
				}
			}
		}
		return $response;
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
		$addJoin = "";
		if ($sysnick) {
			$accessType = "
				case when {$sysnick}.Lpu_id = :Lpu_id and coalesce({$sysnick}.{$sysnick}_IsSigned, 1) = 1 then 'edit' else 'view' end as accessType
			";
			$addJoin = "
				left join v_{$sysnick} {$sysnick} on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid
			";
		} else {
			$accessType = "
				'view' as accessType
			";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id as \"EvnPrescr_id\",
			    EP.EvnPrescr_pid as \"EvnPrescr_pid\",
			    EP.EvnPrescr_rid as \"EvnPrescr_rid\",
			    to_char(EP.EvnPrescr_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
			    null as \"EvnPrescr_setTime\",
			    coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\",
			    case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
			    case when 2 = EP.EvnPrescr_IsExec
					then to_char(EP.EvnPrescr_updDT, '{$this->dateTimeForm104}')||' '||to_char(EP.EvnPrescr_updDT, '{$this->dateTimeForm108}') else null
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
							when MS.MedService_id is not null and MS.LpuSection_id is null and MS.LpuUnit_id is null
								then coalesce(MS.MedService_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							else coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
						end||' / '||coalesce(Lpu.Lpu_Nick, '')
				else '' end as \"RecTo\",
				case
					when TTMS.TimetableMedService_id is not null then coalesce(to_char(TTMS.TimetableMedService_begTime::date, '{$this->dateTimeForm104}'), '')||' '||coalesce(to_char(TTMS.TimetableMedService_begTime::date, '{$this->dateTimeForm108}'), '')
					when EQ.EvnQueue_id is not null then 'В очереди с '||coalesce(to_char(EQ.EvnQueue_setDate::date, '{$this->dateTimeForm104}'), '')
				else '' end as \"RecDate\",
				case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as \"timetable\",
			    coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id, 0) as \"timetable_id\",
				EP.EvnPrescr_pid as \"timetable_pid\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				DT.DirType_Code as \"DirType_Code\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_2011id as \"UslugaComplex_2011id\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				EPOU.EvnPrescrOperUsluga_id as \"TableUsluga_id\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from
				v_EvnPrescr EP
				inner join EvnPrescrOperUsluga EPOU on EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPOU.UslugaComplex_id
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
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					  and EQ.EvnQueue_recDT is null
					union
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnQueue_id = ED.EvnQueue_id
					  and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					  and EQ.EvnQueue_failDT is null
				    limit 1
				) as EQ on true
				left join lateral (
					select ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
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
			  and EP.PrescriptionType_id = 7
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
		$last_ep = null;
		$is_exe = null;
		$is_sign = null;
		$uslugaIdList = [];
		$uslugaList = [];
		foreach ($tmp_arr as $i => $row) {
			if ($last_ep != $row["EvnPrescr_id"]) {
				//это первая итерация с другим назначением
				$last_ep = $row["EvnPrescr_id"];
				$is_exe = false;
				$is_sign = false;
				$uslugaIdList = [];
				$uslugaList = [];
			}
			if (empty($uslugaList[$row["TableUsluga_id"]])) {
				$uslugaIdList[] = $row["UslugaComplex_id"];
				if ($this->options["prescription"]["enable_show_service_code"]) {
					$uslugaList[$row["TableUsluga_id"]] = $row["UslugaComplex_Code"] . " " . $row["UslugaComplex_Name"];
				} else {
					$uslugaList[$row["TableUsluga_id"]] = $row["UslugaComplex_Name"];
				}
			}
			if ($is_exe == false) $is_exe = ($row["EvnPrescr_IsExec"] == 2);
			if ($is_sign == false) $is_sign = ($row["PrescriptionStatusType_id"] == 2);
			if (empty($tmp_arr[$i + 1]) || $last_ep != $tmp_arr[$i + 1]["EvnPrescr_id"]) {
				if ($is_exe) {
					$row["EvnPrescr_IsExec"] = 2;
				}
				if ($is_sign) {
					$row["PrescriptionStatusType_id"] = 2;
				}
				$row["UslugaId_List"] = implode(",", $uslugaIdList);
				$row["Usluga_List"] = implode("<br />", $uslugaList);
				$row[$section . "_id"] = $row["EvnPrescr_id"] . "-" . $row["TableUsluga_id"];
				$response[] = $row;
			}
		}
		return $response;
	}
}