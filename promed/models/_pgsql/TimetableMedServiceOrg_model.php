<?php
/**
 * TimetableMedServiceOrg_model - модель для работы с расписанием службы
 *
 * Загрузка базовой модели для работы с расписанием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      28.12.2011
 *
 * @property CI_DB_driver $db
 */
require_once("Timetable_model.php");

class TimetableMedServiceOrg_model extends Timetable_model
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение расписания службы для редактирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTimetableMedServiceOrgForEdit($data)
	{
		if (!isset($data["MedService_id"])) {
			throw new Exception("Не указана служба, для которой показывать расписание");
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata = [
			"StartDay" => $StartDay,
			"header" => [],
			"descr" => [],
			"data" => [],
			"occupied" => []
		];
		$param = [
			"StartDay" => TimeToDay($StartDay),
			"EndDay" => TimeToDay(strtotime("+14 days", $StartDay)),
			"MedService_id" => $data["MedService_id"],
			"StartDate" => date("Y-m-d", $StartDay),
			"EndDate" => date("Y-m-d", strtotime("+14 days", $StartDay))
		];
		$nTime = $StartDay;
		for ($nCol = 0; $nCol < 14; $nCol++) {
			$nWeekDay = date("w", $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class=\"{$sClass}\"><b>{$this->arShortWeekDayName[$nWeekDay]}</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;
			$nTime = strtotime("+1 day", $nTime);
		}
		$sql = "
			select 
				p.Org_Phone as \"Org_Phone\",
				t.pmUser_updID as \"pmUser_updID\",
				to_char(t.TimetableMedServiceOrg_updDT, 'dd.mm.yyyy hh24:mi:ss') as \"TimetableMedServiceOrg_updDT\",
				t.TimetableMedServiceOrg_id as \"TimetableMedServiceOrg_id\",
				t.Org_id as \"Org_id\",
				t.TimetableMedServiceOrg_Day as \"TimetableMedServiceOrg_Day\",
				to_char(t.TimetableMedServiceOrg_begTime, 'dd.mm.yyyy hh24:mi:ss') as \"TimetableMedServiceOrg_begTime\",
				1 as \"TimetableType_id\",
				p.Org_Nick as \"Org_Nick\",
				t.PMUser_UpdID as \"PMUser_UpdID\",
				u.PMUser_Name as \"PMUser_Name\",
				u.Lpu_id as \"pmUser_Lpu_id\",
				null as \"Address_Address\"
			from
				TimetableMedServiceOrg t
				left join v_MedService ms on ms.MedService_id = t.MedService_id
				left join Org p on t.Org_id = p.Org_id
				left join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			where t.TimetableMedServiceOrg_Day >= :StartDay
			  and t.TimetableMedServiceOrg_Day < :EndDay
			  and t.MedService_id = :MedService_id
			  and TimetableMedServiceOrg_begTime between :StartDate and :EndDate
			order by t.TimetableMedServiceOrg_begTime
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $param);
		$ttgdata = $result->result("array");
		foreach ($ttgdata as $ttg) {
			$outdata["data"][$ttg["TimetableMedServiceOrg_Day"]][] = $ttg;
			if (isset($ttg["Org_id"])) {
				$outdata["occupied"][$ttg["TimetableMedServiceOrg_Day"]] = true;
			}
		}
		$sql = "
			select TimetableMedService_id as \"TimetableMedServiceOrg_id\"
			from TimetableLock
			where TimetableMedService_id is not null
		";
		$result = $this->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $result->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableMedServiceOrg_id"];
		}
		return $outdata;
	}

	/**
	 * Создание расписания для службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function createTTMSOSchedule($data)
	{
		$data["StartDay"] = TimeToDay(strtotime($data["CreateDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CreateDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][0]) < strtotime($archive_database_date)) {
				throw new Exception("Нельзя создать расписание на архивные даты.");
			}
		}
		if (true !== ($res = $this->checkTimetableMedServiceOrgTimeNotOccupied($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableMedServiceOrgTimeNotExists($data))) {
			return $res;
		}
		$nStartTime = StringToTime($data["StartTime"]);
		$nEndTime = StringToTime($data["EndTime"]);
		for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
			$data["Day"] = $day;
			$selectString = "
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			";
			$sql = "
				select {$selectString}
				from p_TimetableMedServiceOrg_fill(
					MedService_id := :MedService_id,
					TimetableMedServiceOrg_Day := :TimetableMedServiceOrg_Day,
					TimetableMedServiceOrg_Time := :TimetableMedServiceOrg_Time,
					StartTime := :StartTime,
					EndTime := :EndTime,
					pmUser_id := :pmUser_id
				)
			";
			$sqlParams = [
				"MedService_id" => $data["MedService_id"],
				"TimetableMedServiceOrg_Day" => $day,
				"TimetableMedServiceOrg_Time" => $data["Duration"],
				"pmUser_id" => $data["pmUser_id"],
				"StartTime" => $data["StartTime"],
				"EndTime" => $data["EndTime"],
			];
			$this->db->query($sql, $sqlParams);
		}
		// отправка STOMP-сообщения
		$funcParams = [
			"timeTable" => "TimetableMedServiceOrg",
			"action" => "AddTicket",
			"setDate" => date("c"),
			"begDate" => date("c", DayMinuteToTime($data["StartDay"], $nStartTime)),
			"endDate" => date("c", DayMinuteToTime($data["EndDay"], $nEndTime)),
			"MedStaffFact_id" => $data["session"]["CurMedStaffFact_id"]
		];
		sendFerStompMessage($funcParams, "RulePeriod");
		return ["Error_Msg" => ""];
	}

	/**
	 * Создание расписания для услуги
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function createTTMSOScheduleUslugaComplex($data)
	{
		$data["StartDay"] = TimeToDay(strtotime($data["CreateDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CreateDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][0]) < strtotime($archive_database_date)) {
				throw new Exception("Нельзя создать расписание на архивные даты.");
			}
		}
		if (true !== ($res = $this->checkTimetableMedServiceOrgTimeNotOccupiedUslugaComplex($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableMedServiceOrgTimeNotExistsUslugaComplex($data))) {
			return $res;
		}
		$nStartTime = StringToTime($data["StartTime"]);
		$nEndTime = StringToTime($data["EndTime"]);
		for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
			$data["Day"] = $day;
			$selectString = "
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			";
			$sql = "
				select {$selectString}
				from p_TimetableMedServiceOrg_fill(
					UslugaComplexMedService_id := :UslugaComplexMedService_id,
					TimetableMedServiceOrg_Day := :TimetableMedServiceOrg_Day,
					TimetableMedServiceOrg_Time := :TimetableMedServiceOrg_Time,
					StartTime := :StartTime,
					EndTime := :EndTime,
					pmUser_id := :pmUser_id
				)
			";
			$sqlParams = [
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
				"TimetableMedServiceOrg_Day" => $day,
				"TimetableMedServiceOrg_Time" => $data["Duration"],
				"pmUser_id" => $data["pmUser_id"],
				"TimetableType_id" => $data["TimetableType_id"],
				"TimetableExtend_Descr" => $data["TimetableExtend_Descr"],
				"StartTime" => $data["StartTime"],
				"EndTime" => $data["EndTime"],
			];
			$this->db->query($sql, $sqlParams);
		}
		// отправка STOMP-сообщения
		$funcParams = [
			"timeTable" => "TimetableMedServiceOrg",
			"action" => "AddTicket",
			"setDate" => date("c"),
			"begDate" => date("c", DayMinuteToTime($data["StartDay"], $nStartTime)),
			"endDate" => date("c", DayMinuteToTime($data["EndDay"], $nEndTime)),
			"MedStaffFact_id" => $data["session"]["CurMedStaffFact_id"]
		];
		sendFerStompMessage($funcParams, "RulePeriod");
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для службы
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOrgTimeNotOccupied($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where MedService_id = :MedService_id
				  and Org_id is not null
				  and (
					(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
				  )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"MedService_id" => $data["MedService_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$result = $result->result("array");
			}
			if ($result[0]["cnt"] > 0) {
				throw new Exception("Нельзя очистить расписание, так как есть занятые бирки.");
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from TimetableMedServiceOrg
					where MedService_id = :MedService_id
					  and Org_id is not null
					  and (
						(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"MedService_id" => $data["MedService_id"]
				];
				$result = $this->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$result = $result->result("array");
				}
				if ($result[0]["cnt"] > 0) {
					throw new Exception("Нельзя очистить расписание, так как есть занятые бирки.");
				}
			}
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для услуги
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOrgTimeNotOccupiedUslugaComplex($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and Org_id is not null
				  and (
					(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
				  )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$result = $result->result("array");
			}
			if ($result[0]["cnt"] > 0) {
				throw new Exception("Нельзя очистить расписание, так как есть занятые бирки.");
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from TimetableMedServiceOrg
					where UslugaComplexMedService_id = :UslugaComplexMedService_id
					  and Org_id is not null
					  and (
						(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
				];
				$result = $this->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$result = $result->result("array");
				}
				if ($result[0]["cnt"] > 0) {
					throw new Exception("Нельзя очистить расписание, так как есть занятые бирки.");
				}
			}
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени созданные бирки для службы
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOrgTimeNotExists($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where MedService_id = :MedService_id
				  and (
					(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
				  )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"MedService_id" => $data["MedService_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$result = $result->result("array");
			}
			if ($result[0]["cnt"] > 0) {
				throw new Exception("В заданном интервале времени уже существуют бирки.");
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from TimetableMedServiceOrg
					where MedService_id = :MedService_id
					  and (
						(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"MedService_id" => $data["MedService_id"]
				];
				$result = $this->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$result = $result->result("array");
				}
				if ($result[0]["cnt"] > 0) {
					throw new Exception("В заданном интервале времени уже существуют бирки.");
				}
			}
		}
		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOrgTimeNotExistsUslugaComplex($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and (
					(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
					(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
				  )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$result = $result->result("array");
			}
			if ($result[0]["cnt"] > 0) {
				throw new Exception("В заданном интервале времени уже существуют бирки.");
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from TimetableMedServiceOrg
					where UslugaComplexMedService_id = :UslugaComplexMedService_id
					  and (
						(TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' < :EndTime) or
						(TimetableMedServiceOrg_begTime + TimetableMedServiceOrg_Time*interval '1 minute' > :StartTime and TimetableMedServiceOrg_begTime < :StartTime)
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
				];
				$result = $this->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$result = $result->result("array");
				}
				if ($result[0]["cnt"] > 0) {
					throw new Exception("В заданном интервале времени уже существуют бирки.");
				}
			}
		}
		return true;
	}

	/**
	 * Копирование расписания для службы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function copyTTMSOSchedule($data)
	{
		if (empty($data["CopyToDateRange"][0])) {
			throw new Exception("Не указан диапазон для вставки расписания.");
		}
		$data["StartDay"] = TimeToDay(strtotime($data["CopyToDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CopyToDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][1]) < strtotime($archive_database_date)) {
				throw new Exception("Нельзя скопировать расписание на архивные даты.");
			}
		}
		if (true !== ($res = $this->checkTimetableMedServiceOrgDayNotOccupied($data))) {
			throw new Exception("Нельзя скопировать расписание на промежуток, так как на нем занятые бирки.");
		}
		$n = 0;
		$nShift = TimeToDay(strtotime($data["CreateDateRange"][1])) - TimeToDay(strtotime($data["CreateDateRange"][0])) + 1;
		$nTargetEnd = 0;
		while ($nTargetEnd < $data["EndDay"]) {
			$nTargetStart = $data["StartDay"] + $nShift * $n;
			$nTargetEnd = $data["StartDay"] + $nShift * ($n + 1) - 1;
			$nTargetEnd = min($nTargetEnd, $data["EndDay"]);

			$SourceStartDay = TimeToDay(strtotime($data["CreateDateRange"][0]));
			$SourceEndDay = TimeToDay(strtotime($data["CreateDateRange"][1]));
			$SourceEndDay = min($SourceEndDay, (TimeToDay(strtotime($data["CreateDateRange"][0])) + $nTargetEnd - $nTargetStart));

			$selectString = "
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			";
			$sql = "
				select {$selectString}
				from p_TimetableMedServiceOrg_copy(
					MedService_id := :MedService_id,
					SourceStartDay := :SourceStartDay,
					SourceEndDay := :SourceEndDay,
					TargetStartDay := :TargetStartDay,
					TargetEndDay := :TargetEndDay,
					CopyTimetableExtend_Descr := :CopyTimetableExtend_Descr,
					pmUser_id := :pmUser_id
				)
			";
			$sqlParams = [
				"MedService_id" => $data["MedService_id"],
				"SourceStartDay" => $SourceStartDay,
				"SourceEndDay" => $SourceEndDay,
				"TargetStartDay" => $nTargetStart,
				"TargetEndDay" => $nTargetEnd,
				"CopyTimetableExtend_Descr" => ($data["CopyTTMSOComments"] == 1) ? 1 : null,
				"pmUser_id" => $data["pmUser_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					$funcParams = [
						"timeTable" => "TimetableMedServiceOrg",
						"action" => "AddTicket",
						"setDate" => date("c"),
						"begDate" => date("c", DayMinuteToTime($nTargetStart, 0)),
						"endDate" => date("c", DayMinuteToTime($nTargetEnd, 0)),
						"MedStaffFact_id" => $data["session"]["CurMedStaffFact_id"]
					];
					sendFerStompMessage($funcParams, "RulePeriod");
				}
			}
			for ($i = 0; $i <= $nTargetEnd - $nTargetStart; $i++) {
				// Пересчет теперь прямо в хранимке
				if ($data["CopyDayComments"] == 1) {
					$sql = "
						select
							MedServiceDay_id as \"MedServiceDay_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_MedServiceDay_setDescr(
							Day_id := :TargetDay_id,
							MedService_id := :MedService_id,
							UslugaComplexMedService_id := :UslugaComplexMedService_id,
							MedServiceDay_Descr := (
								select MedServiceDay_Descr
								from MedServiceDay
								where MedService_id = :MedService_id
								  and Day_id = :SourceDay_id
							),
							pmUser_id := :pmUser_id
						)
					";
					$sqlParams = [
						"MedService_id" => $data["MedService_id"],
						"TargetDay_id" => $nTargetStart + $i,
						"SourceDay_id" => TimeToDay(strtotime($data["CreateDateRange"][0])) + $i,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($sql, $sqlParams);
				}
			}
			$n++;
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Копирование расписания для услуги службы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function copyTTMSOScheduleUslugaComplex($data)
	{
		$data["StartDay"] = TimeToDay(strtotime($data["CreateDateRange"][1])) + 1;
		$data["EndDay"] = $data["StartDay"] + (TimeToDay(strtotime($data["CreateDateRange"][1])) - TimeToDay(strtotime($data["CreateDateRange"][0]))) * $data["CopyTimes"];
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][1]) < strtotime($archive_database_date)) {
				throw new Exception("Нельзя скопировать расписание на архивные даты.");
			}
		}
		if (true !== ($res = $this->checkTimetableMedServiceOrgDayNotOccupiedUslugaComplex($data))) {
			throw new Exception("Нельзя скопировать расписание на промежуток, так как на нем занятые бирки.");
		}
		for ($n = 1; $n <= $data["CopyTimes"]; $n++) {
			$nShift = $n * (TimeToDay(strtotime($data["CreateDateRange"][1])) - TimeToDay(strtotime($data["CreateDateRange"][0])) + 1);
			$nTargetStart = TimeToDay(strtotime($data["CreateDateRange"][0])) + $nShift;
			$nTargetEnd = TimeToDay(strtotime($data["CreateDateRange"][1])) + $nShift;
			for ($i = 0; $i <= $nTargetEnd - $nTargetStart; $i++) {
				// Пересчет теперь прямо в хранимке
				if ($data["CopyDayComments"] == 1) {
					$sql = "
						select
							MedServiceDay_id as \"MedServiceDay_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_MedServiceDay_setDescr(
							Day_id := :TargetDay_id,
							UslugaComplexMedService_id := :UslugaComplexMedService_id,
							MedServiceDay_Descr := (
								select MedServiceDay_Descr
								from MedServiceDay
								where UslugaComplexMedService_id = :UslugaComplexMedService_id
								  and Day_id = :SourceDay_id
							),
							pmUser_id := :pmUser_id
						)
					";
					$sqlParams = [
						"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
						"TargetDay_id" => $nTargetStart + $i,
						"SourceDay_id" => TimeToDay(strtotime($data["CreateDateRange"][0])) + $i,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($sql, $sqlParams);
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для службы
	 * @param $data
	 * @return bool
	 */
	function checkTimetableMedServiceOrgDayNotOccupied($data)
	{
		/**@var CI_DB_result $result */
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where TimetableMedServiceOrg_Day = :Day
				  and MedService_id = :MedStaffFact_id
				  and Person_id is not null
				  and TimetableMedServiceOrg_begTime is not null
			";
			$sqlParams = [
				"Day" => $data["Day"],
				"MedService_id" => $data["MedService_id"],
			];
			$result = $this->db->query($sql, $sqlParams);
		}
		if (isset($data["StartDay"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where TimetableMedServiceOrg_day between :StartDay and :EndDay
				  and MedService_id = :MedService_id
				  and Person_id is not null
				  and TimetableMedServiceOrg_begTime is not null
			";
			$sqlParams = [
				"StartDay" => $data["StartDay"],
				"EndDay" => $data["EndDay"],
				"MedService_id" => $data["MedService_id"],
			];
			$result = $this->db->query($sql, $sqlParams);
		}
		if (is_object($result)) {
			$result = $result->result("array");
		}
		return ($result[0]["cnt"] > 0) ? false : true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для службы
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOrgDayNotOccupiedUslugaComplex($data)
	{
		/**@var CI_DB_result $result */
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where TimetableMedServiceOrg_Day = :Day
				  and UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and Person_id is not null
				  and TimetableMedServiceOrg_begTime is not null
			";
			$sqlParams = [
				"Day" => $data["Day"],
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			];
			$result = $this->db->query($sql, $sqlParams);
		}
		if (isset($data["StartDay"])) {
			$sql = "
				select count(*) as \"cnt\"
				from TimetableMedServiceOrg
				where TimetableMedServiceOrg_day between :StartDay and :EndDay
				  and UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and Person_id is not null
				  and TimetableMedServiceOrg_begTime is not null
			";
			$sqlParams = [
				"StartDay" => $data["StartDay"],
				"EndDay" => $data["EndDay"],
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			];
			$result = $this->db->query($sql, $sqlParams);
		}
		if (is_object($result)) {
			$result = $result->result("array");
		}
		if ($result[0]["cnt"] > 0) {
			throw new Exception("Нельзя очистить расписание, так как есть занятые бирки.");
		}
		return true;
	}

	/**
	 * Очистка дня для службы
	 * @param $data
	 * @return array
	 */
	function ClearDay($data)
	{
		/**@var CI_DB_result $result */
		$selectString = "
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$sql = "
			select {$selectString}
			from p_TimetableMedServiceOrg_clearDay(
				TimetableMedServiceOrg_Day := :TimetableMedServiceOrg_Day,
				MedService_id := :MedService_id,
				pmUser_id := :pmUser_id
			)
		";
		$sqlParams = [
			"MedService_id" => $data["MedService_id"],
			"TimetableMedServiceOrg_Day" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($sql, $sqlParams);

		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
				// отправка STOMP-сообщения
				$funcParams = [
					"timeTable" => "TimetableMedServiceOrg",
					"action" => "DelTicket",
					"setDate" => date("c"),
					"begDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"endDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"MedStaffFact_id" => $data["session"]["CurMedStaffFact_id"]
				];
				sendFerStompMessage($funcParams, "RulePeriod");
			}
		}
		// Пересчет теперь прямо в хранимке
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение истории изменения бирки службы
	 * @param $data
	 * @return array|bool
	 */
	function getTTMSOHistory($data)
	{
		/**@var CI_DB_result $result */
		//TODO 111
		if (!isset($data["ShowFullHistory"])) {
			$sql = "
				select
					to_char(TimetableMedServiceOrgHist_insDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableHist_insDT\",
					rtrim(PMUser_Name) as \"PMUser_Name\",
					TimetableActionType_Name as \"TimetableActionType_Name\",
					TimetableType_Name as \"TimetableType_Name\",
					rtrim(rtrim(p.Person_Surname)||' '||rtrim(Person_Firname)||' '||coalesce(rtrim(Person_Secname), '')) as \"Person_FIO\",
					to_char(Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
				from
					TimetableMedServiceOrgHist ttsh
					left join v_pmUser pu on ttsh.TimetableMedServiceOrgHist_userID = pu.pmuser_id
					left join TimetableActionType ttat on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
					left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(ttsh.TimetableType_id, 1)
					left join v_Person_ER p on ttsh.Person_id = p.Person_id
				where TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id";
		} else {
			$sql = "
				select
					to_char(TimetableMedServiceOrgHist_insDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableHist_insDT\",
					rtrim(PMUser_Name) as \"PMUser_Name\",
					TimetableActionType_Name as \"TimetableActionType_Name\",
					TimetableType_Name as \"TimetableType_Name\",
					rtrim(rtrim(p.Person_Surname)||' '||rtrim(Person_Firname)||' '||coalesce(rtrim(Person_Secname), '')) as \"Person_FIO\",
					to_char(Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
				from
					TimetableMedServiceOrgHist ttsh
					left join v_pmUser pu on ttsh.TimetableMedServiceOrgHist_userID = pu.pmuser_id
					left join TimetableActionType ttat on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
					left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(ttsh.TimetableType_id, 1)
					left join v_Person_ER p on ttsh.Person_id = p.Person_id
				where  MedService_id = (
						select MedService_id
						from TimetableMedServiceOrg
						where TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id
					)
				  and TimetableMedServiceOrg_begTime = (
						select TimetableMedServiceOrg_begTime
						from TimetableMedServiceOrg
						where TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id
				    )
			";
		}
		$sqlParams = ["TimetableMedServiceOrg_id" => $data["TimetableMedServiceOrg_id"]];
		$result = $this->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Проверка, что бирка существует и занята
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOrgOccupied($data)
	{
		$sql = "
			select
				TimetableMedServiceOrg_id as \"TimetableMedServiceOrg_id\",
				Org_id as \"Org_id\"
			from TimetableMedServiceOrg
			where TimetableMedServiceOrg_id = :Id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, ["Id" => $data["TimetableMedServiceOrg_id"]]);
		if (is_object($result)) {
			$result = $result->result("array");
		}
		if (!isset($result[0]) || !isset($result[0]["TimetableMedServiceOrg_id"])) {
			throw new Exception("Бирка с таким идентификатором не существует.");
		}
		if (!isset($result[0]["Org_id"])) {
			throw new Exception("Выбранная вами бирка уже свободна.");
		}
		return true;
	}


	/**
	 * Проверка, что бирка существует и свободна
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOrgFree($data)
	{
		$sql = "
			select
				TimetableMedServiceOrg_id as \"TimetableMedServiceOrg_id\",
				Org_id as \"Org_id\"
			from TimetableMedServiceOrg
			where TimetableMedServiceOrg_id = :Id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, ["Id" => $data["TimetableMedServiceOrg_id"]]);
		if (is_object($result)) {
			$result = $result->result("array");
		}
		if (!isset($result[0]) || $result[0]["TimetableMedServiceOrg_id"] == null) {
			throw new Exception("Бирка с таким идентификатором не существует.");
		}
		if ($result[0]["Org_id"] != null) {
			throw new Exception("Выбранная вами бирка уже занята.");
		}
		return true;
	}


	/**
	 * Удаление бирки для службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function Delete($data)
	{
		$data["object"] = "TimetableMedServiceOrg";
		$data["TimetableMedServiceOrgGroup"] = (isset($data["TimetableMedServiceOrg_id"])) ? [$data["TimetableMedServiceOrg_id"]] : json_decode($data["TimetableMedServiceOrgGroup"]);
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем службу и список дней, на которые мы выделили бирки
		$TimetableMedServiceOrgGroupString = implode(",", $data["TimetableMedServiceOrgGroup"]);
		$query = "
			select
				TimetableMedServiceOrg_id as \"TimetableMedServiceOrg_id\",
				MedService_id as \"MedService_id\",
				TimetableMedServiceOrg_Day as \"TimetableMedServiceOrg_Day\"
			from TimetableMedServiceOrg
			where TimetableMedServiceOrg_id in ({$TimetableMedServiceOrgGroupString})
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $resultDelete
		 */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ($result as $row) {
			//Удаляем бирку
			$sql = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_TimetableMedServiceOrg_del(TimetableMedServiceOrg_id := :TimetableMedServiceOrg_id)
			";
			$resultDelete = $this->db->query($sql, ["TimetableMedServiceOrg_id" => $row["TimetableMedServiceOrg_id"]]);
			if (is_object($resultDelete)) {
				$resp = $resultDelete->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					$funcParams = [
						"id" => $row["TimetableMedServiceOrg_id"],
						"timeTable" => "TimetableMedServiceOrg",
						"action" => "DelTicket",
						"setDate" => date("c")
					];
					sendFerStompMessage($funcParams, "Rule");
				}
			}
			// Пересчет теперь прямо в хранимке
		}
		return ["success" => true];
	}


	/**
	 * Проверка прав на очистку бирки
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkHasRightsToClearRecord($data)
	{
		$sql = "
			select
				t.TimetableMedServiceOrg_id as \"TimetableMedServiceOrg_id\",
				t.pmUser_updId as \"pmUser_updId\",
				pu.Lpu_id as \"Lpu_id\",
				l.Org_id as \"Org_id\"
			from
				TimetableMedServiceOrg t
				left join v_pmUser pu on t.pmUser_updId = pu.pmUser_id
				left join v_Lpu l on l.Lpu_id = pu.Lpu_id
			where t.TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, ['TimetableMedServiceOrg_id' => $data['TimetableMedServiceOrg_id']]);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД");
		}
		$result = $result->result("array");
		$resultRow = $result[0];
		if ($resultRow["TimetableMedServiceOrg_id"] == null) {
			throw new Exception("Бирка с таким идентификатором не существует.");
		}
		if (!(($resultRow["pmUser_updId"] == $data["session"]["pmuser_id"]) || isCZAdmin() || isLpuRegAdmin($resultRow["Org_id"]) || isInetUser($resultRow["pmUser_updId"]))) {
			throw new Exception("У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.");
		}
		return true;
	}

	/**
	 * Получение расписания на один день на службу
	 * @param $data
	 * @return array
	 */
	function getTimetableMedServiceOrgOneDay($data)
	{
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param = [
			"StartDay" => TimeToDay($StartDay),
			"MedService_id" => $data["MedService_id"]
		];
		$outdata = [
			"StartDay" => $StartDay,
			"day_comment" => null,
			"data" => []
		];
		$sql = "
			select
				p.Org_Phone as \"Org_Phone\",
				case when a1.Address_id is not null
					then  a1.Address_Address
					else a.Address_Address
				end as \"Address_Address\",
				t.pmUser_updID as \"pmUser_updID\",
				to_char(t.TimetableMedServiceOrg_updDT, 'dd.mm.yyyy hh24:mi:ss') as \"TimetableMedServiceOrg_updDT\",
				t.TimetableMedServiceOrg_id as \"TimetableMedServiceOrg_id\",
				t.Org_id as \"Org_id\",
				t.TimetableMedServiceOrg_Day as \"TimetableMedServiceOrg_Day\",
				to_char(TimetableMedServiceOrg_begTime, 'dd.mm.yyyy hh24:mi:ss') as \"TimetableMedServiceOrg_begTime\",
				p.Org_Nick as \"Org_Nick\",
				t.PMUser_UpdID as \"PMUser_UpdID\",
				u.PMUser_Name as \"PMUser_Name\",
				u.Lpu_id as \"pmUser_Lpu_id\"
			from
				TimetableMedServiceOrg t
				left outer join v_MedService ms on ms.MedService_id = t.MedService_id
				left outer join Org p on t.Org_id = p.Org_id
				left outer join Address a on p.UAddress_id = a.Address_id
				left outer join Address a1 on p.PAddress_id = a1.Address_id
				left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			where t.TimetableMedServiceOrg_Day = :StartDay
			  and t.MedService_id = :MedService_id
			  and TimetableMedServiceOrg_begTime is not null
			order by t.TimetableMedServiceOrg_begTime
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $param);
		$ttsdata = $result->result("array");
		foreach ($ttsdata as $tts) {
			$outdata["data"][] = $tts;
		}
		$sql = "select TimetableMedService_id as \"TimetableMedServiceOrg_id\" from TimetableLock";
		$result = $this->db->query($sql);
		$outdata["reserved"] = $result->result("array");
		return $outdata;
	}


	/**
	 * Получение первой даты записи МО на защиту в МЗ
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getFirstTimetableMedServiceOrgDate($data)
	{
		$query = "
			select to_char(TTMSO.TimetableMedServiceOrg_begTime, 'dd.mm.yyyy hh24:mi:ss') as \"TimetableMedServiceOrg_begTime\"
			from
				v_TimetableMedServiceOrg TTMSO
				inner join v_Lpu L on TTMSO.Org_id = L.Org_id
			where L.Lpu_id = :Lpu_id
			order by TimetableMedServiceOrg_begTime
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД при получении даты первой записи МО на защиту.");
		}
		return $result->result("array");
	}
}
