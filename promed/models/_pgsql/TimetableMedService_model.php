<?php
/**
 * TimetableMedService_model - модель для работы с расписанием службы
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
 */

// Загрузка базовой модели для работы с расписанием
require_once("Timetable_model.php");

class TimetableMedService_model extends Timetable_model
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI:SS";
	private $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";
	private $dateTimeForm112 = "YYYYMMDD";

	/**
	 * TimetableMedService_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->helper("Reg");
	}

	/**
	 * Возвращает информацию по использованию примечаний
	 * @param $data
	 * @return array
	 */
	function getTimeTableExtendData($data)
	{
		// Для Карелии и других менее нагруженных регионов 
		$extend = [
			"source" => "v_TimetableMedService_lite",
			"fields" =>
				"null as \"TimetableExtend_Descr\",
					null as \"TimetableExtend_updDT\",
					null as \"TimetableExtend_pmUser_Name\"",
			"join" => ""
		];
		$isExtend = (isset($data["session"]["region"]) && in_array($data["session"]["region"]["nick"], ["kareliya"]));
		if ($isExtend) {
			$extend = [
				"source" => "v_TimetableMedService",
				"fields" => "
					t.TimetableExtend_Descr as \"TimetableExtend_Descr\",
					to_char(cast(t.TimetableExtend_updDT  as timestamp), 'dd.mm.yyyy HH24:MI:SS') as \"TimetableExtend_updDT\",
					ud.pmUser_Name as \"TimetableExtend_pmUser_Name\"
				",
				"join" => "left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id"
			];
		}
		return $extend;
	}

	/**
	 * Получение расписания службы для редактирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTimetableMedServiceForEdit($data)
	{
		$outdata = [];
		$filter = "";
		if (!isset($data["MedService_id"])) {
			return [
				"success" => false,
				"Error_Msg" => "Не указана служба, для которой показывать расписание"
			];
		}
		if (!empty($data["withoutUslugaComplexTimetable"])) {
			// без бирок на услугу службы
			$filter = " and t.UslugaComplexMedService_id is null ";
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;

		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+14 days", $StartDay));
		$param["MedService_id"] = $data["MedService_id"];
		$param["StartDate"] = date("Y-m-d", $StartDay);
		$param["EndDate"] = date("Y-m-d", strtotime("+14 days", $StartDay));
		$nTime = $StartDay;
		if (empty($data["dntUseFilterMaxDayRecord"]) || $data["dntUseFilterMaxDayRecord"] != true) {
			$msflpu = $this->getFirstRowFromQuery("select Lpu_id as \"Lpu_id\" from v_MedService where MedService_id = :MedService_id", ["MedService_id" => $data["MedService_id"]]);
			$maxDays = GetMedServiceDayCount(@$msflpu["Lpu_id"]);
			if (date("H:i") >= getShowNewDayTime() && $maxDays) {
				$maxDays++;
			}
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param["EndDate"];
		}
		$outdata["header"] = [];
		$outdata["descr"] = [];
		$outdata["data"] = [];
		$outdata["occupied"] = [];
		for ($nCol = 0; $nCol < 14; $nCol++) {
			$nWeekDay = date("w", $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class='$sClass'>" . "<b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;
			$nTime = strtotime("+1 day", $nTime);
		}

		$sql = "
			select
				msd.Day_id as \"Day_id\",
			    rtrim(msd.MedServiceDay_Descr) as \"MedServiceDay_Descr\",
			    rtrim(u.pmUser_Name) as \"pmUser_Name\",
			    msd.MedServiceDay_updDT as \"MedServiceDay_updDT\"
			from
				MedServiceDay msd
				left join v_pmUser u on u.pmUser_id = msd.pmUser_updID
			where MedService_id = :MedService_id
			  and Day_id >= :StartDay
			  and Day_id < :EndDay
		";
		$res = $this->db->query($sql, $param);
		$daydescrdata = $res->result("array");
		foreach ($daydescrdata as $day) {
			$outdata["descr"][$day["Day_id"]] = [
				"MedServiceDay_Descr" => $day["MedServiceDay_Descr"],
				"pmUser_Name" => $day["pmUser_Name"],
				"MedServiceDay_updDT" => isset($data["MedServiceDay_updDT"]) ? ConvertDateFormat($data["MedServiceDay_updDT"], "d.m.Y H:i") : ""
			];
		}
		$ext = $this->getTimeTableExtendData($data);
		$selectPersonData = "
			to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then to_char(p.Person_BirthDay, 'dd.mm.yyyy') else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		$selectString = "
			t.pmUser_updID as \"pmUser_updID\",
			to_char(t.TimetableMedService_updDT, 'dd.mm.yyyy HH24:MI:SS') as\"TimetableMedService_updDT\",
			t.TimetableMedService_id as \"TimetableMedService_id\",
			t.Person_id as \"Person_id\",
			t.TimetableMedService_Day as \"TimetableMedService_Day\",
			to_char(t.TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableMedService_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
			{$selectPersonData}
			case 
				when t.pmUser_updid = 999000
				then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000
				then 'Запись через интернет'
				else u.PMUser_Name 
			end as \"PMUser_Name\",
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			d.EvnDirection_TalonCode as \"Direction_TalonCode\",
			to_char(d.EvnDirection_setDate, '{$this->dateTimeForm104}') as \"Direction_Date\",
			d.evn_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			epd.EvnPrescr_id as \"EvnPrescr_id\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			{$ext['fields']}
		";
		$fromString = "
			{$ext['source']} t
			left join v_MedService ms on ms.MedService_id = t.MedService_id
			--left join v_Person_ER2 p on t.Person_id = p.Person_id
			left join lateral(select * from v_Person_ER2 p where p.Person_id = t.Person_id limit 1) p on true
			left join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			{$ext['join']}
			left join lateral (
				select
		            d.evn_id,
		            d.Diag_id,
		            d.EvnDirection_Num,
		            d.EvnDirection_TalonCode,
					Evn.Evn_setDT as EvnDirection_setDate,
					Evn.Lpu_id
				from EvnDirection d
					 inner join Evn on Evn.Evn_id = d.evn_id and Evn.Evn_deleted = 1
				where t.TimetableMedService_id = d.TimetableMedService_id
				  and d.DirFailType_id is null
				limit 1
			) as d on true
			left join lateral (
				select epd.EvnPrescr_id
				from v_EvnPrescrDirection epd
				where epd.EvnDirection_id = d.evn_id
				limit 1
			) as epd on true
			left join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
			left join lateral (
				select
					Evn.pmUser_updId,
					Evn.Evn_insDT as EvnQueue_insDT
				from EvnQueue q 
					 inner join Evn on Evn.Evn_id = q.Evn_id and Evn.Evn_deleted = 1 
				where t.TimetableMedService_id = q.TimetableMedService_id
			) as q on true
			left join v_pmUser qp on q.pmUser_updId = qp.pmUser_id
			left join v_Diag dg on dg.Diag_id = d.Diag_id
			{$joinPersonEncrypHIV}
		";
		$whereString = "
				t.TimetableMedService_Day >= :StartDay
			and t.TimetableMedService_Day < :EndDay
			and t.MedService_id = :MedService_id
			and cast(TimetableMedService_begTime as date) between :StartDate and :EndDate
			{$filter}		
		";
		$orderByString = "t.TimetableMedService_begTime";
		$sql = "
			select {$selectString} 
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		$res = $this->db->query($sql, $param);
		$ttgdata = $res->result("array");
		foreach ($ttgdata as $ttg) {
			$outdata["data"][$ttg["TimetableMedService_Day"]][] = $ttg;
			if (isset($ttg["Person_id"])) {
				$outdata["occupied"][$ttg["TimetableMedService_Day"]] = true;
			}
		}
		$sql = "
			select TimetableMedService_id as \"TimetableMedService_id\"
			from TimetableLock
			where TimetableMedService_id is not null
		";
		$res = $this->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $res->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableMedService_id"];
		}
		return $outdata;
	}

	/**
	 * Проверка есть ли бирки на услуге службы
	 * @param $data
	 * @return array|bool|float|int|string
	 * @throws Exception
	 */
	function getTimetableUslugaComplexCount($data)
	{
		if (!isset($data["UslugaComplexMedService_id"])) {
			return [
				"success" => false,
				"Error_Msg" => "Не указана услуга, для которой показывать расписание"
			];
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$params = [
			"StartDay" => TimeToDay($StartDay),
			"EndDay" => TimeToDay(strtotime("+14 days", $StartDay)),
			"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			"StartDate" => date("Y-m-d", $StartDay),
			"EndDate" => date("Y-m-d", strtotime("+14 days", $StartDay))
		];
		$sql = "
			select count(t.TimetableMedService_id) as \"cnt\"
			from v_TimetableMedService_lite t
			where t.TimetableMedService_Day >= :StartDay
			  and t.TimetableMedService_Day < :EndDay
			  and t.UslugaComplexMedService_id = :UslugaComplexMedService_id
			  and t.TimetableMedService_begTime between :StartDate and :EndDate
		";
		$result = $this->getFirstResultFromQuery($sql, $params);
		return $result;
	}

	/**
	 * Получение расписания услуги для редактирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTimetableUslugaComplexForEdit($data)
	{
		$outdata = [];
		if (!isset($data["UslugaComplexMedService_id"])) {
			return [
				"success" => false,
				"Error_Msg" => "Не указана услуга, для которой показывать расписание"
			];
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;
		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+14 days", $StartDay));
		$param["UslugaComplexMedService_id"] = $data["UslugaComplexMedService_id"];
		$param["StartDate"] = date("Y-m-d", $StartDay);
		$param["EndDate"] = date("Y-m-d", strtotime("+14 days", $StartDay));

		if (empty($data["dntUseFilterMaxDayRecord"]) || $data["dntUseFilterMaxDayRecord"] != true) {
			$sql = "
				select ms.Lpu_id as \"Lpu_id\",
				       ms.MedService_id as \"MedService_id\"
				from v_UslugaComplexMedService ucms
				     left join v_MedService ms on ucms.MedService_id = ms.MedService_id where ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
			";
			$sqlParams = [
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
			];
			$msflpu = $this->getFirstRowFromQuery($sql, $sqlParams);
			$maxDays = GetMedServiceDayCount($msflpu["Lpu_id"]);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) {
				$maxDays++;
			}
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param["EndDate"];
		}
		$nTime = $StartDay;

		$outdata["header"] = [];
		$outdata["descr"] = [];
		$outdata["data"] = [];
		$outdata["occupied"] = [];
		for ($nCol = 0; $nCol < 14; $nCol++) {
			$nWeekDay = date("w", $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class='$sClass'>" . "<b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;

			$nTime = strtotime("+1 day", $nTime);
		}

		$sql = "
			select
				msd.Day_id as \"Day_id\",
				rtrim(msd.MedServiceDay_Descr) as \"MedServiceDay_Descr\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				msd.MedServiceDay_updDT as \"MedServiceDay_updDT\"
			from MedServiceDay msd
				 left join v_pmUser u on u.pmUser_id = msd.pmUser_updID
			where UslugaComplexMedService_id = :UslugaComplexMedService_id
			  and Day_id >= :StartDay
			  and Day_id < :EndDay
		";
		$res = $this->db->query($sql, $param);
		$daydescrdata = $res->result("array");
		foreach ($daydescrdata as $day) {
			/**@var DateTime $MedServiceDay_updDT */
			$MedServiceDay_updDT = $day["MedServiceDay_updDT"];
			$outdata["descr"][$day["Day_id"]] = [
				"MedServiceDay_Descr" => $day["MedServiceDay_Descr"],
				"pmUser_Name" => $day["pmUser_Name"],
				"MedServiceDay_updDT" => ($MedServiceDay_updDT instanceof DateTime) ? $MedServiceDay_updDT->format("d.m.Y H:i") : ""
			];
		}
		$ext = $this->getTimeTableExtendData($data);
		$selectPersonData = "
				to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				p.Person_Phone as \"Person_Phone\",
				p.PrivilegeType_id as \"PrivilegeType_id\",
				rtrim(p.Person_Firname) as \"Person_Firname\",
				rtrim(p.Person_Surname) as \"Person_Surname\",
				rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then to_char(p.Person_BirthDay, 'dd.mm.yyyy') else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		$selectString = "
			t.pmUser_updID as \"pmUser_updID\",
			to_char(t.TimetableMedService_updDT, 'dd.mm.yyyy HH24:MI:SS') as\"TimetableMedService_updDT\",
			t.TimetableMedService_id as \"TimetableMedService_id\",
			t.Person_id as \"Person_id\",
			t.TimetableMedService_Day as \"TimetableMedService_Day\",
			to_char(t.TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableMedService_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
			{$selectPersonData}
			case 
				when t.pmUser_updid = 999000
				then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000
				then 'Запись через интернет'
				else u.PMUser_Name 
			end as \"PMUser_Name\",
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			d.EvnDirection_TalonCode as \"Direction_TalonCode\",
			to_char(d.EvnDirection_setDate, '{$this->dateTimeForm104}') as \"Direction_Date\",
			d.evn_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			epd.EvnPrescr_id as \"EvnPrescr_id\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			{$ext['fields']}
		";
		$fromString = "
			{$ext['source']} t
			--left outer join v_Person_ER2 p on t.Person_id = p.Person_id
			left join lateral(select * from v_Person_ER2 p where p.Person_id = t.Person_id limit 1) p on true
			left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			{$ext['join']}
			left join lateral (
				select
		            d.evn_id,
		            d.Diag_id,
		            d.EvnDirection_Num,
		            d.EvnDirection_TalonCode,
					Evn.Evn_setDT as EvnDirection_setDate,
					Evn.Lpu_id
				from EvnDirection d  
					 inner join Evn on Evn.Evn_id = d.evn_id and Evn.Evn_deleted = 1 
				where t.TimetableMedService_id = d.TimetableMedService_id
				  and d.DirFailType_id is null
				limit 1
			) as d on true
			left join lateral (
				select epd.EvnPrescr_id
				from v_EvnPrescrDirection epd
				where epd.EvnDirection_id = d.evn_id
				limit 1
			) as epd on true
			left join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
			left join lateral (
				select
					Evn.pmUser_updId,
					Evn.Evn_insDT as EvnQueue_insDT 
				from EvnQueue q 
					 inner join Evn on Evn.Evn_id = q.Evn_id and Evn.Evn_deleted = 1 
				where  t.TimetableMedService_id = q.TimetableMedService_id
				limit 1
			) as q on true
			left join v_pmUser qp on q.pmUser_updId = qp.pmUser_id
			left join Diag dg on dg.Diag_id = d.Diag_id
			{$joinPersonEncrypHIV}
		";
		$whereString = "
				t.TimetableMedService_Day >= :StartDay
			and t.TimetableMedService_Day < :EndDay
			and t.UslugaComplexMedService_id = :UslugaComplexMedService_id
			and cast(TimetableMedService_begTime as date) between :StartDate and :EndDate
		";
		$orderByString = "t.TimetableMedService_begTime";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		$res = $this->db->query($sql, $param);
		$ttgdata = $res->result("array");

		foreach ($ttgdata as $ttg) {
			$outdata["data"][$ttg["TimetableMedService_Day"]][] = $ttg;
			if (isset($ttg["Person_id"])) {
				$outdata["occupied"][$ttg["TimetableMedService_Day"]] = true;
			}
		}
		$sql = "
			select TimetableMedService_id as \"TimetableMedService_id\"
			from TimetableLock
			where TimetableMedService_id is not null
		";
		$res = $this->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $res->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableMedService_id"];
		}
		return $outdata;
	}

	/**
	 * @param $data
	 * Запись пациента на дополнительную бирку при приему пациента врачем службы консультативного приема без записи
	 */

	function acceptPerson($data){
		$params = array(
			'Person_id' => $data['Person_id'],
			'MedService_id' => $data['MedService_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Timetable_Day' => $data['Day'],
			'pmUser_id' => $data['pmUser_id'],
			'Fact_DT' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime'])))
		);

		$sql = "
			select
				TimetableMedService_id as \"TimetableMedService_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_TimetableMedService_ins (
				TimetableMedService_id := null,
				Person_id := :Person_id,
				MedService_id := :MedService_id,
				TimetableMedService_factTime := :Fact_DT,
				TimetableMedService_begTime := :Fact_DT,
				EvnDirection_id := :EvnDirection_id,
				TimetableMedService_Day := :Timetable_Day,
				TimetableMedService_Time := 0,
				TimetableMedService_IsDop := 1,
				TimetableExtend_Descr := 'Прием c pfgbcm.',
				RecClass_id := 3,
				RecMethodType_id := 1,
				pmUser_id := :pmUser_id
			)
		";

		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			$resp = $res->result( 'array' );
			return $resp;
		} else {
			return array(array('Error_Msg' => 'Ошибка БД при создании бирки экстренного посещения пациентом врача!'));
		}
	}


	/**
	 * Приём из очереди без записи
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function acceptWithoutRecord($data)
	{
		if (!empty($data["EvnDirection_id"])) {
			// если уже записано на бирку то ещё запись выполняться не должна
			$query = "
				select TimetableMedService_id as \"TimetableMedService_id\"
				from v_TimetableMedService_lite
				where EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				return [
					"Error_Msg" => "Ошибка проверки наличия бирки"
				];
			}
			$resp = $result->result("array");
			if (!empty($resp[0]["TimetableMedService_id"])) {
				return [
					"Error_Msg" => "Пациент уже принят"
				];
			}
			$query = "
				select
					MedService_id as \"MedService_id\",
					Person_id as \"Person_id\",
					to_char(tzgetdate(), '{$this->dateTimeForm104}') as \"date\"
				from v_EvnDirection_all
				where EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (!empty($resp[0]["date"])) {
					$data["date"] = $resp[0]["date"];
					$data["MedService_id"] = $resp[0]["MedService_id"];
					$data["Person_id"] = $resp[0]["Person_id"];
				}
			}
		}
		if (empty($data['Person_id'])) {
			return [
				"Error_Msg" => "Ошибка получения данных по направлению"
			];
		}
		$Timetable_Day = empty($data["Timetable_Day"]) ? TimeToDay(strtotime($data["date"])) : $data["Timetable_Day"];
		$params = [
			"Person_id" => $data["Person_id"],
			"MedService_id" => $data["MedService_id"],
			"EvnDirection_id" => $data["EvnDirection_id"],
			"Timetable_Day" => $Timetable_Day,
			"pmUser_id" => $data["pmUser_id"]
		];
		$sql = "
			select
				timetablemedservice_id as \"TimetableMedService_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablemedservice_ins(
			    person_id => :Person_id,
			    medservice_id => :MedService_id,
			    timetablemedservice_day => :Timetable_Day,
			    recclass_id => 3,
			    evndirection_id => :EvnDirection_id,
			    timetablemedservice_facttime => tzGetDate(),
			    recmethodtype_id => 1,
				pmuser_id => :pmUser_id
			);
		";
		$res = $this->db->query($sql, $params);
		if (!is_object($res)) {
			return [
				[
					"Error_Msg" => "Ошибка БД при создании бирки экстренного посещения пациентом врача!"
				]
			];
		}
		$resp = $res->result("array");
		if (!empty($resp[0]["TimetableMedService_id"])) {
			// отправка STOMP-сообщения
			sendFerStompMessage([
				"id" => $resp[0]["TimetableMedService_id"],
				"timeTable" => "TimetableMedService",
				"action" => "AddTicket",
				"setDate" => date("c")
			], "Rule");
		}
		return $resp;
	}

	/**
	 * Создание расписания для службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function createTTMSSchedule($data)
	{
		$data["StartDay"] = TimeToDay(strtotime($data["CreateDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CreateDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][0]) < strtotime($archive_database_date)) {
				return [
					"Error_Msg" => "Нельзя создать расписание на архивные даты."
				];
			}
		}
		if (strtotime($data["CreateDateRange"][0]) < strtotime(date("d.m.Y"))) {
			return [
				"Error_Msg" => "Создание расписания на прошедшие периоды невозможно"
			];
		}
		if (true !== ($res = $this->checkTimetableMedServiceTimeNotOccupied($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableMedServiceTimeNotExists($data))) {
			return $res;
		}
		$nStartTime = StringToTime($data["StartTime"]);
		$nEndTime = StringToTime($data["EndTime"]);
		for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
			$data["Day"] = $day;
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablemedservice_fill(
				    medservice_id => :MedService_id,
				    timetablemedservice_day => :TimetableMedService_Day,
				    timetablemedservice_time => :TimetableMedService_Time,
				    timetabletype_id => :TimetableType_id,
				    timetableextend_descr => :TimetableExtend_Descr,
				    starttime => :StartTime,
				    endtime => :EndTime,
				    pmuser_id => :pmUser_id
				);
			";
			$sqlParams = [
				"MedService_id" => $data["MedService_id"],
				"TimetableMedService_Day" => $day,
				"TimetableMedService_Time" => $data["Duration"],
				"pmUser_id" => $data["pmUser_id"],
				"TimetableType_id" => $data["TimetableType_id"],
				"TimetableExtend_Descr" => $data["TimetableExtend_Descr"],
				"StartTime" => $data["StartTime"],
				"EndTime" => $data["EndTime"],
			];
			$this->db->query($sql, $sqlParams);
		}
		// отправка STOMP-сообщения
		sendFerStompMessage([
			"timeTable" => "TimetableMedService",
			"action" => "AddTicket",
			"setDate" => date("c"),
			"begDate" => date("c", DayMinuteToTime($data["StartDay"], $nStartTime)),
			"endDate" => date("c", DayMinuteToTime($data["EndDay"], $nEndTime)),
			"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
		], "RulePeriod");
		return ["Error_Msg" => ""];
	}

	/**
	 * Создание расписания для услуги
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function createTTMSScheduleUslugaComplex($data)
	{
		$data["StartDay"] = TimeToDay(strtotime($data["CreateDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CreateDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][0]) < strtotime($archive_database_date)) {
				return [
					"Error_Msg" => "Нельзя создать расписание на архивные даты."
				];
			}
		}
		if (true !== ($res = $this->checkTimetableMedServiceTimeNotOccupiedUslugaComplex($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableMedServiceTimeNotExistsUslugaComplex($data))) {
			return $res;
		}
		$nStartTime = StringToTime($data["StartTime"]);
		$nEndTime = StringToTime($data["EndTime"]);
		for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
			$data["Day"] = $day;
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablemedservice_fill(
				    uslugacomplexmedservice_id => :UslugaComplexMedService_id,
				    timetablemedservice_day => :TimetableMedService_Day,
				    timetablemedservice_time => :TimetableMedService_Time,
				    timetabletype_id => :TimetableType_id,
				    timetableextend_descr => :TimetableExtend_Descr,
				    starttime => :StartTime,
				    endtime => :EndTime,
				    pmuser_id => :pmUser_id
				);
			";
			$sqlParams = [
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
				"TimetableMedService_Day" => $day,
				"TimetableMedService_Time" => $data["Duration"],
				"pmUser_id" => $data["pmUser_id"],
				"TimetableType_id" => $data["TimetableType_id"],
				"TimetableExtend_Descr" => $data["TimetableExtend_Descr"],
				"StartTime" => $data["StartTime"],
				"EndTime" => $data["EndTime"],
			];
			$this->db->query($sql, $sqlParams);
		}
		// отправка STOMP-сообщения
		sendFerStompMessage([
			"timeTable" => "TimetableMedService",
			"action" => "AddTicket",
			"setDate" => date("c"),
			"begDate" => date("c", DayMinuteToTime($data["StartDay"], $nStartTime)),
			"endDate" => date("c", DayMinuteToTime($data["EndDay"], $nEndTime)),
			"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
		], "RulePeriod");
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для службы
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceTimeNotOccupied($data)
	{
		if (isset($data['Day'])) {
			$sql = "
				select count(*) as \"cnt\"
				from v_TimetableMedService_lite
				where MedService_id = :MedService_id
				  and Person_id is not null
				  and
				    (
				      	(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
				      	((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
				      	((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime)
				    )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"MedService_id" => $data["MedService_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$res = $res->result("array");
			}
			if ($res[0]["cnt"] > 0) {
				return [
					"Error_Msg" => "Нельзя очистить расписание, так как есть занятые бирки."
				];
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from v_TimetableMedService_lite
					where MedService_id = :MedService_id
					  and Person_id is not null
					  and
					    ( 
							(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime ) 
					    )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"MedService_id" => $data["MedService_id"]
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
				}
				if ($res[0]["cnt"] > 0) {
					return [
						"Error_Msg" => "Нельзя очистить расписание, так как есть занятые бирки."
					];
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
	function checkTimetableMedServiceTimeNotOccupiedUslugaComplex($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from v_TimetableMedService_lite
				where UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and Person_id is not null
				  and ( 
						(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
						((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
						((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime) 
					)
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$res = $res->result("array");
			}
			if ($res[0]["cnt"] > 0) {
				return [
					"Error_Msg" => "Нельзя очистить расписание, так как есть занятые бирки."
				];
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from v_TimetableMedService_lite
					where UslugaComplexMedService_id = :UslugaComplexMedService_id
					  and Person_id is not null
					  and ( 
							(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime) 
						)
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
				}
				if ($res[0]["cnt"] > 0) {
					return [
						"Error_Msg" => "Нельзя очистить расписание, так как есть занятые бирки."
					];
				}
			}
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени созданные бирки для службы
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceTimeNotExists($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from v_TimetableMedService_lite
				where MedService_id = :MedService_id
				  and ( 
						(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
						((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
						((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime ) 
					)
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"MedService_id" => $data["MedService_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$res = $res->result("array");
			}
			if ($res[0]["cnt"] > 0) {
				return [
					"Error_Msg" => "В заданном интервале времени уже существуют бирки."
				];
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from v_TimetableMedService_lite
					where
						MedService_id = :MedService_id
						and ( 
							(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime) 
						)
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"MedService_id" => $data["MedService_id"]
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
				}
				if ($res[0]["cnt"] > 0) {
					return [
						"Error_Msg" => "В заданном интервале времени уже существуют бирки."
					];
				}
			}
		}
		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function checkTimetableMedServiceTimeNotExistsUslugaComplex($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from v_TimetableMedService_lite
				where UslugaComplexMedService_id = :UslugaComplexMedService_id
					and ( 
						(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
						((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
						((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime)
					)
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$res = $res->result("array");
			}
			if ($res[0]["cnt"] > 0) {
				return [
					"Error_Msg" => "В заданном интервале времени уже существуют бирки."
				];
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from v_TimetableMedService_lite
					where UslugaComplexMedService_id = :UslugaComplexMedService_id
					  and ( 
							(TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and (TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) < :EndTime) or
							((TimetableMedService_begTime::timestamp + (TimetableMedService_Time||' minutes')::interval) > :StartTime and TimetableMedService_begTime < :StartTime) 
						)
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"]
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
				}
				if ($res[0]["cnt"] > 0) {
					return [
						"Error_Msg" => "В заданном интервале времени уже существуют бирки."
					];
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
	function copyTTMSSchedule($data)
	{
		if (empty($data["CopyToDateRange"][0])) {
			return [
				"Error_Msg" => "Не указан диапазон для вставки расписания."
			];
		}
		$data["StartDay"] = TimeToDay(strtotime($data["CopyToDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CopyToDateRange"][1]));

		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][1]) < strtotime($archive_database_date)) {
				return [
					"Error_Msg" => "Нельзя скопировать расписание на архивные даты."
				];
			}
		}
		if (true !== ($res = $this->checkTimetableMedServiceDayNotOccupied($data))) {
			return [
				"Error_Msg" => "Нельзя скопировать расписание на промежуток, так как на нем занятые бирки."
			];
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
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablemedservice_copy(
				    medservice_id => :MedService_id,
				    sourcestartday => :SourceStartDay,
				    sourceendday => :SourceEndDay,
				    targetstartday => :TargetStartDay,
				    targetendday => :TargetEndDay,
				    copytimetableextend_descr => :CopyTimetableExtend_Descr,
				    pmuser_id => :pmUser_id
				);
			";
			$sqlParams = [
				"MedService_id" => $data["MedService_id"],
				"SourceStartDay" => $SourceStartDay,
				"SourceEndDay" => $SourceEndDay,
				"TargetStartDay" => $nTargetStart,
				"TargetEndDay" => $nTargetEnd,
				"CopyTimetableExtend_Descr" => ($data["CopyTTMSComments"] == 1) ? 1 : NULL,
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"timeTable" => "TimetableMedService",
						"action" => "AddTicket",
						"setDate" => date("c"),
						"begDate" => date("c", DayMinuteToTime($nTargetStart, 0)),
						"endDate" => date("c", DayMinuteToTime($nTargetEnd, 0)),
						"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
					], "RulePeriod");
				}
			}
			for ($i = 0; $i <= $nTargetEnd - $nTargetStart; $i++) {
				// Пересчет теперь прямо в хранимке
				if ($data["CopyDayComments"] == 1) {
					$sql = "
						select
							medserviceday_id as \"MedServiceDay_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_medserviceday_setdescr(
						    day_id => :TargetDay_id,
						    medservice_id => :MedService_id,
						    medserviceday_descr => (select MedServiceDay_Descr from MedServiceDay where MedService_id = :MedService_id and Day_id = :SourceDay_id),
						    pmuser_id => :pmUser_id
						);
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
	function copyTTMSScheduleUslugaComplex($data)
	{
		if (empty($data["CopyToDateRange"][0])) {
			return [
				"Error_Msg" => "Не указан диапазон для вставки расписания."
			];
		}
		$data["StartDay"] = TimeToDay(strtotime($data["CopyToDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CopyToDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][1]) < strtotime($archive_database_date)) {
				return [
					"Error_Msg" => "Нельзя скопировать расписание на архивные даты."
				];
			}
		}
		if (true !== ($res = $this->checkTimetableMedServiceDayNotOccupiedUslugaComplex($data))) {
			return [
				"Error_Msg" => "Нельзя скопировать расписание на промежуток, так как на нем занятые бирки."
			];
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
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablemedservice_copyuslugacomplex(
				    uslugacomplexmedservice_id => :UslugaComplexMedService_id,
				    sourcestartday => :SourceStartDay,
				    sourceendday => :SourceEndDay,
				    targetstartday => :TargetStartDay,
				    targetendday => :TargetEndDay,
				    pmuser_id => :pmUser_id
				);
			";
			$sqlParams = [
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
				"SourceStartDay" => $SourceStartDay,
				"SourceEndDay" => $SourceEndDay,
				"TargetStartDay" => $nTargetStart,
				"TargetEndDay" => $nTargetEnd,
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"timeTable" => "TimetableMedService",
						"action" => "AddTicket",
						"setDate" => date("c"),
						"begDate" => date("c", DayMinuteToTime($nTargetStart, 0)),
						"endDate" => date("c", DayMinuteToTime($nTargetEnd, 0)),
						"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
					], "RulePeriod");
				}
			}
			for ($i = 0; $i <= $nTargetEnd - $nTargetStart; $i++) {
				// Пересчет теперь прямо в хранимке
				if ($data["CopyDayComments"] == 1) {
					$sql = "
						select
							medserviceday_id as \"MedServiceDay_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_medserviceday_setdescr(
						    day_id => :TargetDay_id,
						    uslugacomplexmedservice_id => :UslugaComplexMedService_id,
						    medserviceday_descr => (select MedServiceDay_Descr from MedServiceDay where UslugaComplexMedService_id = :UslugaComplexMedService_id and Day_id = :SourceDay_id),
						    pmuser_id => :pmUser_id
						);
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
			$n++;
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для службы
	 * @param $data
	 * @return bool
	 */
	function checkTimetableMedServiceDayNotOccupied($data)
	{
		$res = null;
		if (isset($data["Day"])) {
			$sql = "
				SELECT count(*) as \"cnt\"
				FROM v_TimetableMedService_lite
				WHERE TimetableMedService_Day = :Day
				  and MedService_id = :MedService_id
				  and Person_id is not null
				  and TimetableMedService_begTime is not null
			";
			$sqlParams = [
				"Day" => $data["Day"],
				"MedService_id" => $data["MedService_id"],
			];
			$res = $this->db->query($sql, $sqlParams);
		}
		if (isset($data["StartDay"])) {
			$sql = "
				SELECT count(*) as \"cnt\"
				FROM v_TimetableMedService_lite
				WHERE TimetableMedService_day between :StartDay and :EndDay
				  and MedService_id = :MedService_id
				  and Person_id is not null
				  and TimetableMedService_begTime is not null
			";
			$sqlParams = [
				"StartDay" => $data["StartDay"],
				"EndDay" => $data["EndDay"],
				"MedService_id" => $data["MedService_id"],
			];
			$res = $this->db->query($sql, $sqlParams);
		}
		if (is_object($res)) {
			$res = $res->result("array");
			if ($res[0]["cnt"] > 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для службы
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceDayNotOccupiedUslugaComplex($data)
	{
		$res = null;
		if (isset($data["Day"])) {
			$sql = "
				SELECT count(*) as \"cnt\"
				FROM v_TimetableMedService_lite
				WHERE TimetableMedService_Day = :Day
				  and UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and Person_id is not null
				  and TimetableMedService_begTime is not null
			";
			$sqlParams = [
				"Day" => $data["Day"],
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			];
			$res = $this->db->query($sql, $sqlParams);
		}
		if (isset($data["StartDay"])) {
			$sql = "
				SELECT count(*) as \"cnt\"
				FROM v_TimetableMedService_lite
				WHERE TimetableMedService_day between :StartDay and :EndDay
				  and UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and Person_id is not null
				  and TimetableMedService_begTime is not null
			";
			$sqlParams = [
				"StartDay" => $data["StartDay"],
				"EndDay" => $data["EndDay"],
				"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			];
			$res = $this->db->query($sql, $sqlParams);
		}
		if (is_object($res)) {
			$res = $res->result("array");
			if ($res[0]["cnt"] > 0) {
				return array(
					"Error_Msg" => "Нельзя очистить расписание, так как есть занятые бирки."
				);
			}
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
		$sql = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablemedservice_clearday(
			    timetablemedservice_day => :TimetableMedService_Day,
			    medservice_id => :MedService_id,
			    pmuser_id => :pmUser_id
			);
		";
		$sqlParams = [
			"MedService_id" => $data["MedService_id"],
			"TimetableMedService_Day" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$resp = $res->result("array");
			if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"timeTable" => "TimetableMedService",
					"action" => "DelTicket",
					"setDate" => date("c"),
					"begDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"endDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
				], "RulePeriod");
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Очистка дня для услуги
	 * @param $data
	 * @return array
	 */
	function ClearDayUslugaComplex($data)
	{
		$sql = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablemedservice_cleardayuslugacomplex(
			    timetablemedservice_day => :TimetableMedService_Day,
			    uslugacomplexmedservice_id => :UslugaComplexMedService_id,
			    pmuser_id => :pmUser_id
			);
		";
		$sqlParams = [
			"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			"TimetableMedService_Day" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$resp = $res->result("array");
			if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"timeTable" => "TimetableMedService",
					"action" => "DelTicket",
					"setDate" => date("c"),
					"begDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"endDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
				], "RulePeriod");
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение истории изменения бирки службы
	 * @param $data
	 * @return array|bool
	 */
	function getTTMSHistory($data)
	{
		$selectPersonData = "
			rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), '')) as \"Person_FIO\",
			to_char(Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null
					then rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), ''))
					else rtrim(peh.PersonEncrypHIV_Encryp)
				end as \"Person_FIO\",
				case when peh.PersonEncrypHIV_Encryp is null then to_char(Person_BirthDay, '{$this->dateTimeForm104}') else null end as \"Person_BirthDay\"
			";
		}
        $sqlParams = ["TimetableMedService_id" => $data["TimetableMedService_id"]];
		if (!isset($data["ShowFullHistory"])) {
			$selectString = "
				to_char(TimetableMedServiceHist_insDT, '$this->dateTimeForm104')||' '||to_char(TimetableMedServiceHist_insDT, '$this->dateTimeForm108') as \"TimetableHist_insDT\",
				rtrim(PMUser_Name) as \"PMUser_Name\",
				TimetableActionType_Name as \"TimetableActionType_Name\",
				TimetableType_Name as \"TimetableType_Name\",
				{$selectPersonData}
			";
			$fromString = "
				TimetableMedServiceHist ttsh
				left join v_pmUser pu on ttsh.TimetableMedServiceHist_userID = pu.pmuser_id
				left join TimetableActionType ttat on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
				left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(ttsh.TimetableType_id, 1)
				left join v_Person_ER p on ttsh.Person_id = p.Person_id
				{$joinPersonEncrypHIV}
			";
			$whereString = "TimetableMedService_id = :TimetableMedService_id";
			$sql = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
			";
		} else {
		    $query = "
                select
                    MedService_id as \"MedService_id\",
                    TimetableMedService_begTime as \"TimetableMedService_begTime\"
                from
                    v_TimetableMedService_lite
                where
                    TimetableMedService_id = :TimetableMedService_id
                limit 1";
		    $res = $this->db->query($query, $sqlParams);
		    if(!is_object($res)){
		        return  false;
            }
		    
		    $res = $res->result('array');
            $filter = ""; 
		    
		    if(!count($res)) {
		        $filter .= "MedService_id is null and TimetableMedService_begTime is null";
            } else {
		        if(is_null($res[0]['MedService_id'])) {
		            $filter .= "MedService_id is null";
                } else {
		            $sqlParams['MedService_id'] = $res[0]['MedService_id'];
                    $filter .= "MedService_id = :MedService_id";
                }
		        $sqlParams['TimetableMedService_begTime'] = $res[0]['TimetableMedService_begTime'];
            }
		    
			$sql = "
				select
					to_char(TimetableMedServiceHist_insDT, 'dd.mm.yyyy H24:MI:SS') as \"TimetableHist_insDT\",
					rtrim(PMUser_Name) as \"PMUser_Name\",
					TimetableActionType_Name as \"TimetableActionType_Name\",
					TimetableType_Name as \"TimetableType_Name\",
					{$selectPersonData}
				from TimetableMedServiceHist ttsh
					left join v_pmUser pu on ttsh.TimetableMedServiceHist_userID = pu.pmuser_id
					left join TimetableActionType ttat on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
					left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(ttsh.TimetableType_id, 1)
					left join v_Person_ER p on ttsh.Person_id = p.Person_id
					{$joinPersonEncrypHIV}
				where {$filter}
				  and TimetableMedService_begTime = :TimetableMedService_begTime
			";
		}
		
		/**@var CI_DB_result $res */
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		return $res->result("array");
	}

	/**
	 * Проверка, что бирка существует и занята
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceOccupied($data)
	{
		$sql = "
			select
				TimetableMedService_id as \"TimetableMedService_id\",
			    Person_id as \"Person_id\"
			from v_TimetableMedService_lite
			where TimetableMedService_id = :Id 
		";
		$res = $this->db->query(
			$sql, array(
				'Id' => $data['TimetableMedService_id']
			)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( !isset($res[0]) || !isset($res[0]['TimetableMedService_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if ( !isset($res[0]['Person_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже свободна.'
			);
		}
		return true;
	}

	/**
	 * Проверка, что бирка существует и свободна
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableMedServiceFree($data)
	{
		$sql = "
			select
				TimetableMedService_id as \"TimetableMedService_id\",
				Person_id as \"Person_id\"
			from TimetableMedService
			where TimetableMedService_id = :Id
		";
		$sqlParams = ["Id" => $data["TimetableMedService_id"]];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			throw new Exception("Ошибка выполнения запроса к БД.");
		}
		$res = $res->result("array");
		if (!isset($res[0]) || $res[0]["TimetableMedService_id"] == null) {
			return [
				"success" => false,
				"Error_Msg" => "Бирка с таким идентификатором не существует."
			];
		}
		if ($res[0]["Person_id"] != null) {
			return [
				"success" => false,
				"Error_Msg" => "Выбранная вами бирка уже свободна."
			];
		}
		return true;
	}

	/**
	 * Определение действия по типу расписания
	 * @param $TimetableType_SysNick
	 * @return string
	 */
	function defineActionTypeByTimetableType($TimetableType_SysNick)
	{
		$action = "";
		switch ($TimetableType_SysNick) {
			case "free":
				$action = "ChType_NormalTicket";
				break;
			case "reserved":
				$action = "ChType_ReservTicket";
				break;
			case "pay":
				$action = "ChType_PaidTicket";
				break;
			case "vet":
				$action = "ChType_VeteranTicket";
				break;
			case "extr":
				$action = "ChType_OutTicket";
				break;
			case "emerg":
				$action = "ChType_ExtraBed";
				break;
			case "bed":
				$action = "ChType_NormalBed";
				break;
		}
		return $action;
	}

	/**
	 * Изменение типа бирки у службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function setTTMSType($data)
	{
		$data["object"] = "TimetableMedService";
		if (isset($data["TimetableMedServiceGroup"])) {
			$data["TimetableMedServiceGroup"] = json_decode($data["TimetableMedServiceGroup"]);
		}
		if (isset($data["TimetableMedServiceGroup"]) && count($data["TimetableMedServiceGroup"]) > 0) {
			// Обработка группы бирок в отдельном методе
			return $this->setTTMSTypeGroup($data);
		} else {
			if (true === ($res = $this->checkTimetableMedServiceOccupied($data))) {
				return array(
					"Error_Msg" => "Бирка занята, изменение типа невозможно."
				);
			}
		}
		// Получаем службу и день, а также заодно проверяем, что бирка существует
		$sql = "
			select
				MedService_id as \"MedService_id\",
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				TimetableMedService_Day as \"TimetableMedService_Day\"
			from v_TimetableMedService_lite
			where TimetableMedService_id = :TimetableMedService_id
		";
		$sqlParams = ["TimetableMedService_id" => $data["TimetableMedService_id"]];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		$res = $res->result("array");
		if (!isset($res[0])) {
			return [
				"Error_Msg" => "Бирка с таким идентификатором не существует."
			];
		}
		$tttype = $this->getFirstRowFromQuery("select TimetableType_Name as \"TimetableType_Name\" from v_TimetableType where TimeTableType_id=?", [$data["TimetableType_id"]]);
		$sql = "
			select
				(select TimetableType_SysNick from v_TimetableType where TimetableType_id = :TimetableType_id limit 1) as \"TimetableType_SysNick\",
				timetablemedservice_id as \"TimetableMedService_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablemedservice_settype(
			    timetablemedservice_id => :TimetableMedService_id,
			    timetabletype_id => :TimetableType_id,
			    pmuser_id => :pmUser_id
			);
		";
		$sqlParams = [
			"TimetableMedService_id" => $data["TimetableMedService_id"],
			"TimetableType_id" => $data["TimetableType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$resp = $res->result("array");
			if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
				$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
				if (!empty($action)) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"id" => $data["TimetableMedService_id"],
						"timeTable" => "TimetableMedService",
						"action" => $action,
						"setDate" => date("c")
					], "Rule");
				}
			}
		}
		return [
			"TimetableType_Name" => $tttype["TimetableType_Name"],
			"Error_Msg" => ""
		];
	}

	/**
	 * Изменение типа бирок у службы для группы бирок
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function setTTMSTypeGroup($data)
	{
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем службу и список дней, на которые мы выделили бирки
		$TimetableMedServiceGroupString = implode(", ", $data["TimetableMedServiceGroup"]);
		$sql = "
			select
				TimetableMedService_id as \"TimetableMedService_id\",
				MedService_id as \"MedService_id\",
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				TimetableMedService_Day as \"TimetableMedService_Day\"
			from v_TimetableMedService_lite
			where TimetableMedService_id in ({$TimetableMedServiceGroupString})
		";
		$res = $this->db->query($sql);
		if (!is_object($res)) {
			return false;
		}
		$res = $res->result("array");
		// Меняем тип у каждой бирки по отдельности. Не лучший вариант конечно
		foreach ($res as $row) {
			$sql = "
				select
					(select TimetableType_SysNick from v_TimetableType where TimetableType_id = :TimetableType_id limit 1) as \"TimetableType_SysNick\",
					timetablemedservice_id as \"TimetableMedService_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablemedservice_settype(
				    timetablemedservice_id => :TimetableMedService_id,
				    timetabletype_id => :TimetableType_id,
				    pmuser_id => :pmUser_id
				);
			";
			$sqlParams = [
				"TimetableMedService_id" => $row["TimetableMedService_id"],
				"TimetableType_id" => $data["TimetableType_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
					$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
					if (!empty($action)) {
						// отправка STOMP-сообщения
						sendFerStompMessage([
							"id" => $row["TimetableMedService_id"],
							"timeTable" => "TimetableMedService",
							"action" => $action,
							"setDate" => date("c")
						], "Rule");
					}
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Удаление бирки для службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function Delete($data)
	{
		$data["object"] = "TimetableMedService";
		$data["TimetableMedServiceGroup"] = (isset($data["TimetableMedService_id"])) ? [$data["TimetableMedService_id"]] : json_decode($data["TimetableMedServiceGroup"]);
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем врача и список дней, на которые мы выделили бирки
		$TimetableMedServiceGroupString = implode(", ", $data["TimetableMedServiceGroup"]);
		$sql = "
			select
				TimetableMedService_id as \"TimetableMedService_id\",
			    MedService_id as \"MedService_id\",
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				TimetableMedService_Day as \"TimetableMedService_Day\"
			from v_TimetableMedService_lite
			where TimetableMedService_id in ({$TimetableMedServiceGroupString})
		";
		$res = $this->db->query($sql);
		if (!is_object($res)) {
			return false;
		}
		$res = $res->result("array");
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ($res as $row) {
			//Удаляем бирку
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablemedservice_del(
					timetablemedservice_id => :TimetableMedService_id,
				    pmuser_id => :pmUser_id
				);
			";
			$sqlParams = [
				"TimetableMedService_id" => $row["TimetableMedService_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"id" => $row["TimetableMedService_id"],
						"timeTable" => "TimetableMedService",
						"action" => "DelTicket",
						"setDate" => date("c")
					], "Rule");
				}
			}
		}
		return ["success" => true];
	}

	/**
	 * Добавление дополнительной бирки для службы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function addTTMSDop($data)
	{
		if (empty($data["Day"])) {
			$data["Day"] = TimeToDay(time());
		}
		if (empty($data["StartTime"])) {
			$data["StartTime"] = date("H:i");
		}
		$sql = "
			select
				timetablemedservice_id as \"TimetableMedService_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablemedservice_ins(
			    medservice_id => :MedService_id,
			    timetablemedservice_day => :TimetableMedService_Day,
			    timetablemedservice_begtime => :TimetableMedService_begTime,
			    timetablemedservice_time => :TimetableMedService_Time,
			    timetabletype_id => 1,
			    timetablemedservice_isdop => 1,
			    timetableextend_descr => :TimetableExtend_Descr,
			    pmuser_id => :pmUser_id
			);
		";
		$sqlParams = [
			"MedService_id" => $data["MedService_id"],
			"TimetableMedService_Day" => $data["Day"],
			"TimetableMedService_begTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
			"TimetableMedService_Time" => 0,
            "TimetableExtend_Descr" => !empty($data['TimetableExtend_Descr']) ? $data['TimetableExtend_Descr'] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			throw new Exception("Ошибка запроса к БД.");
		}
		$resp = $res->result("array");
		if (count($resp) == 0 || empty($resp[0]["TimetableMedService_id"])) {
			return [
				"Error_Msg" => "Дополнительная бирка не создана."
			];
		}
		// отправка STOMP-сообщения
		sendFerStompMessage([
			"id" => $resp[0]["TimetableMedService_id"],
			"timeTable" => "TimetableMedService",
			"action" => "AddTicket",
			"setDate" => date("c")
		], "Rule");
		return [
			"TimetableMedService_id" => $resp[0]["TimetableMedService_id"],
			"TimetableMedService_begTime" => $sqlParams["TimetableMedService_begTime"],
			"Error_Msg" => ""
		];
	}

	/**
	 * Добавление дополнительной бирки для службы
	 * @param $data
	 * @return array
	 */
	function addTTMSDopUslugaComplex($data)
	{
		if (empty($data["Day"])) {
			$data["Day"] = TimeToDay(time());
		}
		if (empty($data["StartTime"])) {
			$data["StartTime"] = date("H:i");
		}
		$sql = "
			select
				timetablemedservice_id as \"TimetableMedService_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablemedservice_ins(
			    timetablemedservice_day => :TimetableMedService_Day,
			    timetablemedservice_begtime => :TimetableMedService_begTime,
			    timetablemedservice_time => :TimetableMedService_Time,
			    timetabletype_id => 1,
			    timetablemedservice_isdop => 1,
			    uslugacomplexmedservice_id => :UslugaComplexMedService_id,
			    pmuser_id => :pmUser_id
			);
		";
		$sqlParams = [
			"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			"TimetableMedService_Day" => $data["Day"],
			"TimetableMedService_begTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
			"TimetableMedService_Time" => 0,
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$resp = $res->result("array");
			if (count($resp) > 0 && !empty($resp[0]["TimetableMedService_id"])) {
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"id" => $resp[0]["TimetableMedService_id"],
					"timeTable" => "TimetableMedService",
					"action" => "AddTicket",
					"setDate" => date("c")
				], "Rule");
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение комментария на день для службы
	 * @param $data
	 * @return array|bool
	 */
	function getTTMSDayComment($data)
	{
		$where = (!isset($data["UslugaComplexMedService_id"])) ? "MedService_id = :MedService_id" : "UslugaComplexMedService_id = :UslugaComplexMedService_id";
		$sql = "
			select
				mpd.MedServiceDay_Descr as \"MedServiceDay_Descr\",
				mpd.MedServiceDay_id as \"MedServiceDay_id\"
			from MedServiceDay mpd
			where {$where}
				and Day_id = :Day_id
		";
		$sqlParams = [
			"MedService_id" => $data["MedService_id"],
			"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			"Day_id" => $data["Day"]
		];
		/**@var CI_DB_result $res */
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		return $res = $res->result("array");
	}

	/**
	 * Сохранение комментария на день для службы
	 * @param $data
	 * @return array
	 */
	function saveTTMSDayComment($data)
	{
		$sql = "
			select
				medserviceday_id as \"MedServiceDay_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_medserviceday_setdescr(
			    day_id => :Day_id,
			    medservice_id => :MedService_id,
			    uslugacomplexmedservice_id => :UslugaComplexMedService_id,
			    medserviceday_descr => :MedServiceDay_Descr,
			    pmuser_id => :pmUser_id
			);
		";
		$sqlParams = [
			"MedServiceDay_Descr" => $data["MedServiceDay_Descr"],
			"MedService_id" => $data["MedService_id"],
			"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			"Day_id" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$this->db->query($sql, $sqlParams);
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка прав на очистку бирки
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkHasRightsToClearRecord($data)
	{
		$objectName = $data["object"];
		$objectNameId = $data["object"] . "_id";
		$fields = "";
		if ($data["object"] == "TimetableMedService") {
			$fields .= ",MedService_id as \"MedService_id\"";
		}
		$selectString = "
			t.{$objectNameId} as \"{$objectNameId}\",
			t.pmUser_updId as \"pmUser_updId\",
			pu.Lpu_id as \"Lpu_id\",
			l.Org_id as \"Org_id\"
			{$fields}
		";
		$fromString = "
			{$objectName} t
			left join v_pmUser pu on t.pmUser_updId = pu.pmUser_id
			left join v_Lpu l on l.Lpu_id = pu.Lpu_id
		";
		$whereString = "
			t.{$objectNameId} = :obj
		";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$sqlParams = [
			"obj" => $data[$objectNameId]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$res = $res->result("array");
		}
		if ($res[0][$objectNameId] == null) {
			return [
				"success" => false,
				"Error_Msg" => "Бирка с таким идентификатором не существует."
			];
		}
		if (
		!(
			($res[0]["pmUser_updId"] == $data["session"]["pmuser_id"]) ||
			isCZAdmin() ||
			isLpuRegAdmin($res[0]["Org_id"]) ||
			isInetUser($res[0]["pmUser_updId"]) ||
			(
				!empty($res[0]["MedService_id"]) &&
				!empty($data["session"]["CurMedService_id"]) &&
				$res[0]["MedService_id"] == $data["session"]["CurMedService_id"]
			) // служба бирки равна текущей службе врача
		)
		) {
			return [
				"success" => false,
				"Error_Msg" => "У вас нет прав отменить запись на прием, <br/>так как она сделана не вами."
			];
		}
		return true;
	}

	/**
	 * Получение расписания на один день на службу
	 * @param $data
	 * @return array
	 */
	function getTimetableMedServiceOneDay($data)
	{
		$outdata = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;
		$param["StartDay"] = TimeToDay($StartDay);
		$param["MedService_id"] = $data["MedService_id"];
		$param["EndDate"] = date("Y-m-d", $StartDay);
		if (empty($data["dntUseFilterMaxDayRecord"]) || $data["dntUseFilterMaxDayRecord"] != true) {
			$msflpu = $this->getFirstRowFromQuery("select Lpu_id as \"Lpu_id\" from v_MedService where MedService_id = ?", [$data["MedService_id"]]);
			$maxDays = GetMedServiceDayCount($msflpu["Lpu_id"]);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param["EndDate"];
		}
		$outdata["day_comment"] = null;
		$outdata["data"] = [];
		$sql = "
			select
				msd.Day_id as \"Day_id\",
				rtrim(msd.MedServiceDay_Descr) as \"MedServiceDay_Descr\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				msd.MedServiceDay_updDT as \"MedServiceDay_updDT\"
			from MedServiceDay msd
				 left join v_pmUser u on u.pmUser_id = msd.pmUser_updID
			where MedService_id = :MedService_id
			  and Day_id = :StartDay
		";
		$res = $this->db->query($sql, $param);
		$daydescrdata = $res->result("array");

		if (isset($daydescrdata[0]["MedServiceDay_Descr"])) {
			$outdata["day_comment"] = [
				"MedServiceDay_Descr" => $daydescrdata[0]["MedServiceDay_Descr"],
				"pmUser_Name" => $daydescrdata[0]["pmUser_Name"],
				"MedServiceDay_updDT" => $daydescrdata[0]["MedServiceDay_updDT"]
			];
		}
		$ext = $this->getTimeTableExtendData($data);
		$selectPersonData = "
			to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			p.PersonInfo_InternetPhone as \"Person_InetPhone\",
			case when a1.Address_id is not null
				then  a1.Address_Address
				else a.Address_Address
			end as \"Address_Address\",
			case when a1.Address_id is not null
				then a1.KLTown_id
				else a.KLTown_id
			end as \"KLTown_id\",
			case when a1.Address_id is not null
				then a1.KLStreet_id
				else a.KLStreet_id
			end as \"KLStreet_id\",
			case when a1.Address_id is not null
				then a1.Address_House
				else a.Address_House
			end as \"Address_House\",
			j.Job_Name as \"Job_Name\",
			lpu.Lpu_Nick as \"Lpu_Nick\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then to_char(p.Person_BirthDay, 'dd.mm.yyyy') else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PersonInfo_InternetPhone else null end as \"Person_InetPhone\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.Address_Address else a.Address_Address
				end as \"Address_Address\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
				end as \"KLTown_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
				end as \"KLStreet_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.Address_House else a.Address_House
				end as \"Address_House\",
				case when peh.PersonEncrypHIV_Encryp is null then j.Job_Name else null end as \"Job_Name\",
				case when peh.PersonEncrypHIV_Encryp is null then lpu.Lpu_Nick else null end as \"Lpu_Nick\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		$selectString = "
			t.pmUser_updID as \"pmUser_updID\",
			to_char(t.TimetableMedService_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableMedService_updDT\",
			t.TimetableMedService_id as \"TimetableMedService_id\",
			t.Person_id as \"Person_id\",
			t.TimetableMedService_Day as \"TimetableMedService_Day\",
			to_char(TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableMedService_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
			{$selectPersonData}
			case 
				when t.pmUser_updid=999000 then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000 then 'Запись через интернет'
				else u.PMUser_Name 
			end as \"PMUser_Name\", 
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			d.EvnDirection_TalonCode as \"Direction_TalonCode\",
			to_char(d.EvnDirection_setDate, '{$this->dateTimeForm104}') as \"Direction_Date\",
			d.evn_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			epd.EvnPrescr_id as \"EvnPrescr_id\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			ms.Lpu_id as \"Lpu_id\",
			{$ext['fields']}
		";
		$fromString = "
			{$ext['source']} t
			left outer join v_MedService ms on ms.MedService_id = t.MedService_id
			left outer join v_Person_ER p on t.Person_id = p.Person_id
			left outer join Address a on p.UAddress_id = a.Address_id
			left outer join Address a1 on p.PAddress_id = a1.Address_id
			left outer join KLStreet pas on a.KLStreet_id = pas.KLStreet_id
			left outer join KLStreet pas1 on a1.KLStreet_id = pas1.KLStreet_id
			left outer join v_Job_ER j on p.Job_id=j.Job_id
			left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			{$ext['join']}
			left outer join v_Lpu lpu on lpu.Lpu_id = p.Lpu_id
			left join lateral (
				select
		            d.evn_id,
		            d.Diag_id,
		            d.EvnDirection_Num,
		            d.EvnDirection_TalonCode,
					Evn.Evn_setDT as EvnDirection_setDate,
					Evn.Lpu_id
				from
					EvnDirection d  
					inner join Evn on Evn.Evn_id = d.evn_id and Evn.Evn_deleted = 1 
				where t.TimetableMedService_id = d.TimetableMedService_id
				  and d.DirFailType_id is null
			    limit 1
			) as d on true
			left join lateral (
				select epd.EvnPrescr_id
				from v_EvnPrescrDirection epd
				where epd.EvnDirection_id = d.evn_id
			    limit 1
			) as epd on true
			left outer join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
			left join lateral (
				select
					Evn.pmUser_updId,
					Evn.Evn_insDT as EvnQueue_insDT 
				from
					EvnQueue q 
					inner join Evn on Evn.Evn_id = q.Evn_id and Evn.Evn_deleted = 1 
				where t.TimetableMedService_id = q.TimetableMedService_id
			) as q on true
			left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
			left join Diag dg on dg.Diag_id=d.Diag_id
			{$joinPersonEncrypHIV}
		";
		$whereString = "
				t.TimetableMedService_Day = :StartDay
			and t.TimetableMedService_begTime <= :EndDate
			and t.MedService_id = :MedService_id
			and TimetableMedService_begTime is not null
		";
		$orderByString = "
			t.TimetableMedService_begTime
		";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		$res = $this->db->query($sql, $param);
		$ttsdata = $res->result("array");

		// Получаем данные по коду брони в направлениях для ЛИС
		// если служба связана с ЭО и имеет тип ЛАБОРАТОРИИ или ПЗ
		$getLisElectronicQueueData = false;
		if ($this->usePostgreLis) {

			$this->load->swapi('lis');
			$ms_data = $this->getFirstRowFromQuery("
				select
					mst.MedServiceType_SysNick as \"mst.MedServiceType_SysNick\" 
				from v_MedService ms
				inner join v_ElectronicQueueInfo eqi on eqi.MedService_id = ms.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				where (1=1) 
					and ms.MedService_id = :MedService_id
				limit 1					
			", array('MedService_id' => $data['MedService_id']));

			if (!empty($ms_data) && in_array($ms_data['MedServiceType_SysNick'],array('lab','pzm'))) {
				$getLisElectronicQueueData = true;
			}
		}

		if ($getLisElectronicQueueData) {

			$ed_list = array();
			foreach ($ttsdata as $tts) {
				$outdata['data'][$tts['TimetableMedService_id']] = $tts;
				if (!empty($tts['ttEvnDirection_id'])) {
					$ed_list[] = $tts['ttEvnDirection_id'];
				}
			}

			$tc_params = array('list' => implode(',', $ed_list));
			$talon_code_data = $this->lis->GET('EvnDirection/getTalonCodeByEvnDirectionList', $tc_params);
			if (
				empty($talon_code_data['Error_Msg'])
				&& !empty($talon_code_data['data'])
				&& is_array($talon_code_data['data'])
			) {
				foreach ($talon_code_data['data'] as $lisItem) {
					if (isset($outdata['data'][$lisItem['TimetableMedService_id']])) {
						$outdata['data'][$lisItem['TimetableMedService_id']]['Direction_TalonCode'] = $lisItem['EvnDirection_TalonCode'];
					}
				}
				$outdata['data'] = array_values($outdata['data']);
			}

		} else {
			foreach ($ttsdata as $tts) {
				$outdata['data'][] = $tts;
			}
		}

		$sql = "
			select TimetableMedService_id as \"TimetableMedService_id\" from TimetableLock";

		$res = $this->db->query($sql);

		$outdata['reserved'] = $res->result('array');

		return $outdata;
	}

	/**
	 * Получение расписания на один день на услугу
	 * @param $data
	 * @return array
	 */
	function getTimetableUslugaComplexOneDay($data)
	{
		$outdata = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;
		$param["StartDay"] = TimeToDay($StartDay);
		$param["UslugaComplexMedService_id"] = $data["UslugaComplexMedService_id"];
		$outdata["day_comment"] = null;
		$outdata["data"] = [];
		$sql = "
			select
				msd.Day_id as \"Day_id\",
				rtrim(msd.MedServiceDay_Descr) as \"MedServiceDay_Descr\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				msd.MedServiceDay_updDT as \"MedServiceDay_updDT\"
			from MedServiceDay msd
				 left join v_pmUser u on u.pmUser_id = msd.pmUser_updID
			where UslugaComplexMedService_id = :UslugaComplexMedService_id
				and Day_id = :StartDay
		";
		$res = $this->db->query($sql, $param);
		$daydescrdata = $res->result("array");
		if (isset($daydescrdata[0]["MedServiceDay_Descr"])) {
			$outdata["day_comment"] = [
				"MedServiceDay_Descr" => $daydescrdata[0]["MedServiceDay_Descr"],
				"pmUser_Name" => $daydescrdata[0]["pmUser_Name"],
				"MedServiceDay_updDT" => $daydescrdata[0]["MedServiceDay_updDT"]
			];
		}
		$ext = $this->getTimeTableExtendData($data);
		$selectPersonData = "
			to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			p.PersonInfo_InternetPhone as \"Person_InetPhone\",
			case when a1.Address_id is not null
				then a1.Address_Address
				else a.Address_Address
			end as \"Address_Address\",
			case when a1.Address_id is not null
				then a1.KLTown_id
				else a.KLTown_id
			end as \"KLTown_id\",
			case when a1.Address_id is not null
				then a1.KLStreet_id
				else a.KLStreet_id
			end as \"KLStreet_id\",
			case when a1.Address_id is not null
				then a1.Address_House
				else a.Address_House
			end as \"Address_House\",
			j.Job_Name as \"Job_Name\",
			lpu.Lpu_Nick as \"Lpu_Nick\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then to_char(p.Person_BirthDay, 'dd.mm.yyyy') else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PersonInfo_InternetPhone else null end as \"Person_InetPhone\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.Address_Address else a.Address_Address
				end as \"Address_Address\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
				end as \"KLTown_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
				end as \"KLStreet_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.Address_House else a.Address_House
				end as \"Address_House\",
				case when peh.PersonEncrypHIV_Encryp is null then j.Job_Name else null end as \"Job_Name\",
				case when peh.PersonEncrypHIV_Encryp is null then lpu.Lpu_Nick else null end as \"Lpu_Nick\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		$selectString = "
			t.pmUser_updID as \"pmUser_updID\",
			to_char(t.TimetableMedService_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableMedService_updDT\",
			t.TimetableMedService_id as \"TimetableMedService_id\",
			t.Person_id as \"Person_id\",
			t.TimetableMedService_Day as \"TimetableMedService_Day\",
			to_char(TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableMedService_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
			{$selectPersonData}
			case 
				when t.pmUser_updid=999000 then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000 then 'Запись через интернет'
				else u.PMUser_Name 
			end as \"PMUser_Name\", 
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			d.EvnDirection_TalonCode as \"Direction_TalonCode\",
			to_char(d.EvnDirection_setDate, '{$this->dateTimeForm104}') as \"Direction_Date\",
			d.evn_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			epd.EvnPrescr_id as \"EvnPrescr_id\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			ms.Lpu_id as \"Lpu_id\",
			{$ext['fields']}
		";
		$fromString = "
			{$ext['source']} t
			left outer join v_MedService ms on ms.MedService_id = t.MedService_id
			left outer join v_Person_ER p on t.Person_id = p.Person_id
			left outer join Address a on p.UAddress_id = a.Address_id
			left outer join Address a1 on p.PAddress_id = a1.Address_id
			left outer join KLStreet pas on a.KLStreet_id = pas.KLStreet_id
			left outer join KLStreet pas1 on a1.KLStreet_id = pas1.KLStreet_id
			left outer join v_Job_ER j on p.Job_id=j.Job_id
			left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			{$ext['join']}
			left outer join v_Lpu lpu on lpu.Lpu_id = p.Lpu_id
			left join lateral (
				select
		            d.evn_id,
		            d.Diag_id,
		            d.EvnDirection_Num,
		            d.EvnDirection_TalonCode,
					Evn.Evn_setDT as EvnDirection_setDate,
					Evn.Lpu_id
				from
					EvnDirection d
					inner join Evn on Evn.Evn_id = d.evn_id and Evn.Evn_deleted = 1
				where t.TimetableMedService_id = d.TimetableMedService_id
				  and d.DirFailType_id is null
			    limit 1
			) as d on true
			left join lateral (
				select epd.EvnPrescr_id
				from v_EvnPrescrDirection epd
				where epd.EvnDirection_id = d.evn_id
			    limit 1
			) as epd on true
			left outer join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
			left join lateral (
				select
					Evn.pmUser_updId,
					Evn.Evn_insDT as EvnQueue_insDT 
				from
					EvnQueue q
					inner join Evn on Evn.Evn_id = q.Evn_id and Evn.Evn_deleted = 1 
				where t.TimetableMedService_id = q.TimetableMedService_id
			    limit 1
			) as q on true
			left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
			left join Diag dg on dg.Diag_id=d.Diag_id
			{$joinPersonEncrypHIV}		
		";
		$whereString = "
				t.TimetableMedService_Day = :StartDay
			and t.UslugaComplexMedService_id = :UslugaComplexMedService_id
			and TimetableMedService_begTime is not null
		";
		$orderByString = "
			t.TimetableMedService_begTime
		";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		$res = $this->db->query($sql, $param);
		$ttsdata = $res->result("array");
		foreach ($ttsdata as $tts) {
			$outdata["data"][] = $tts;
		}
		$sql = "select TimetableMedService_id as \"TimetableMedService_id\" from TimetableLock";
		$res = $this->db->query($sql);
		$outdata["reserved"] = $res->result("array");
		return $outdata;
	}

	/**
	 * Редактирование переданного набора бирок
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function editTTMSSet($data)
	{
		$TTMSSet = json_decode($data["selectedTTMS"]);
		if ($this->checkTTMSOccupied($TTMSSet)) {
			return [
				"success" => false,
				"Error_Msg" => "Одна из выбранных бирок занята. Операция невозможна."
			];
		}
		// Пустая строка передается как NULL, надо как пустую строку передавать
		$data["TimetableExtend_Descr"] = ($data["ChangeTTMSDescr"])
			? (isset($data["TimetableExtend_Descr"])
				? $data["TimetableExtend_Descr"]
				: ""
			)
			: null;
		$data["TimetableType_id"] = ($data["ChangeTTMSType"])
			? (isset($data["TimetableType_id"])
				? $data["TimetableType_id"]
				: 1)
			: null;
		foreach ($TTMSSet as $TTMS) {
			$query = "
				select
					(select TimetableType_SysNick from v_TimetableType where TimetableType_id = :TimetableType_id limit 1) as \"TimetableType_SysNick\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablemedservice_edit(
				    timetablemedservice_id => :TimetableMedService_id,
				    timetabletype_id => :TimetableType_id,
				    timetableextend_descr => :TimetableExtend_Descr,
				    pmuser_id => :pmUser_id
				);
			";
			$queryParams = [
				"TimetableMedService_id" => $TTMS,
				"TimetableType_id" => $data["TimetableType_id"],
				"TimetableExtend_Descr" => $data["TimetableExtend_Descr"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($query, $queryParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
					$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
					if (!empty($action)) {
						// отправка STOMP-сообщения
						sendFerStompMessage([
							"id" => $TTMS,
							"timeTable" => "TimetableMedService",
							"action" => $action,
							"setDate" => date("c")
						], "Rule");
					}
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, что хоть одна из набора переданных бирок занята
	 * @param $TTMSSet
	 * @return bool
	 */
	function checkTTMSOccupied($TTMSSet)
	{
		if (count($TTMSSet) == 0) {
			return false;
		}
		$TTMSSetString = implode(",", $TTMSSet);
		$sql = "
			SELECT count(*) as \"cnt\"
			FROM v_TimetableMedService_lite
			WHERE TimetableMedService_id in ({$TTMSSetString})
			  and Person_id is not null
		";
		$res = $this->db->query($sql);
		if (is_object($res)) {
			$res = $res->result("array");
		}
		return $res[0]["cnt"] > 0;
	}

	/**
	 * Получение идентификатора направления выписанного на бирку
	 * @param $data
	 * @return array|bool|false
	 */
	function load($data)
	{
		$params = [];
		$filter = "";
		if (!empty($data["TimetableMedService_id"])) {
			$params["TimetableMedService_id"] = $data["TimetableMedService_id"];
			$filter = "TimetableMedService_id = :TimetableMedService_id";
		} else if (!empty($data["EvnDirection_id"])) {
			$params["EvnDirection_id"] = $data["EvnDirection_id"];
			$filter = "EvnDirection_id = :EvnDirection_id";
		}
		$query = "
			select
				TimeTableMedService_id as \"TimeTableMedService_id\",
				Person_id as \"Person_id\",
				MedService_id as \"MedService_id\",
				TimetableMedService_Day as \"TimetableMedService_Day\",
				TimetableMedService_begTime as \"TimetableMedService_begTime\",
				TimetableMedService_Time as \"TimetableMedService_Time\",
				TimetableType_id as \"TimetableType_id\",
				TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
				RecClass_id as \"RecClass_id\",
				Evn_id as \"Evn_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				TimeTableMedService_insDT as \"TimeTableMedService_insDT\",
				TimeTableMedService_updDT as \"TimeTableMedService_updDT\",
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				EvnDirection_id as \"EvnDirection_id\",
				TimeTableMedService_factTime as \"TimeTableMedService_factTime\",
				RecMethodType_id as \"RecMethodType_id\"
				--TimetableExtend_Descr as \"TimetableExtend_Descr\", --пока полей нет в pg
				--TimetableExtend_updDT as \"TimetableExtend_updDT\",
				--TimetableExtend_pmUser_updID as \"TimetableExtend_pmUser_updID\"
			from v_TimetableMedService
			where {$filter}
			limit 1
		";
		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return false;
		}
		if (isset($resp[0])) {
			unset($resp[0]["TimetableMedService_Rowversion"]);
		}
		array_walk_recursive($resp, function (&$value) {
			if ($value instanceof DateTime) {
				$value = $value->format("Y-m-d H:i:s");
			}
		});
		return $resp;
	}

	/**
	 * Возвращает список бирок на 2 недели по идентификаторам различных типов служб
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function loadAllUslugaTTList($data)
	{
		$MSList = [];
		$MStypes = ["arrRes", "arrMS", "arrUsl"];
		$ResourceTTList = [];
		$MedServiceTTList = [];
		$UslugaComplexTTList = [];
		$result = [];
		foreach ($MStypes as $type) {
			if (!empty($data[$type])) {
				$MSList[$type] = json_decode($data[$type]);
			}
		}
		if (empty($MSList)) {
			return [
				"success" => false,
				"Error_Msg" => "Необходим список идентификаторов служб, ресурсов или услуг"
			];
		}
		// Тянем бирки для каждого типа места оказания
		if (!empty($MSList["arrRes"])) {
			$ResourceTTList = $this->loadResourceTTList($data, $MSList["arrRes"]);
		}
		if (!empty($MSList["arrMS"])) {
			$MedServiceTTList = $this->loadMedServiceTTList($data, $MSList["arrMS"]);
		}
		if (!empty($MSList["arrUsl"])) {
			$UslugaComplexTTList = $this->loadUslugaComplexTTList($data, $MSList["arrUsl"]);
		}
		// Формируем массив расписания для ресурсов по идентификаторам Resource_id
		if (!empty($ResourceTTList)) {
			foreach ($ResourceTTList as $resTT) {
				$result["arrRes"][$resTT["Resource_id"]][$resTT["dataIndex"]] = $resTT;
			}
		}
		// Формируем массив расписания для служб по идентификаторам MedService_id
		if (!empty($MedServiceTTList)) {
			foreach ($MedServiceTTList as $msTT) {
				$result["arrMS"][$msTT["MedService_id"]][$msTT["dataIndex"]] = $msTT;
			}
		}
		// Формируем массив расписания для услуг по идентификаторам UslugaComplexMedService_id
		if (!empty($UslugaComplexTTList)) {
			foreach ($UslugaComplexTTList as $uslTT) {
				$result["arrUsl"][$uslTT["UslugaComplexMedService_id"]][$uslTT["dataIndex"]] = $uslTT;
			}
		}
		return ["success" => true, "data" => $result];
	}

	/**
	 * Возвращает список бирок для ресурсов
	 * @param $data
	 * @param $MSList
	 * @return array|bool
	 */
	function loadResourceTTList($data, $MSList)
	{
		if (empty($MSList)) {
			return false;
		}
		$param = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+14 days", $StartDay));
		$param["nulltime"] = "00:00:00";
		$query = "
			with AllTimetable as (
			    select t.TimetableResource_id,
			           t.TimetableResource_Day,
			           to_char(t.TimetableResource_begTime, '{$this->dateTimeForm108}') AS formatTime,
			           to_char(t.TimetableResource_begTime, '{$this->dateTimeForm112}') as dataIndex,
			           to_char(t.TimetableResource_begTime, '{$this->dateTimeForm104} {$this->dateTimeForm108}') as TimetableResource_begTime,
			           t.TimetableType_id,
			           t.Resource_id
			    FROM v_TimetableResource_lite t
			    WHERE t.TimetableResource_Day >= :StartDay
			      AND t.TimetableResource_Day < :EndDay
			      AND t.Resource_id IN (" . implode(',', $MSList) . ")
			      AND t.Person_id IS NULL
			      AND (CAST(t.TimetableResource_begTime AS DATE) <= (getdate() + (14||' days')::interval) AND CAST(t.TimetableResource_begTime AS DATE) >= getdate())
			    ORDER BY t.TimetableResource_begTime
			), allDay as (
			    select distinct
			        TimetableResource_Day,
			        Resource_id
			    from AllTimetable
			)
			select
			    alltt.TimetableResource_id as \"TimetableResource_id\",
			    alltt.TimetableResource_Day as \"TimetableResource_Day\",
			    alltt.formatTime as \"formatTime\",
			    alltt.dataIndex as \"dataIndex\",
			    alltt.TimetableResource_begTime as \"TimetableResource_begTime\",
			    alltt.TimetableType_id as \"TimetableType_id\",
				alltt.Resource_id as \"Resource_id\"
			    ann.TimetableResource_Day as \"TimetableResource_Day\",
			    ann.Resource_id as \"Resource_id\"
			from
			    allDay allt
			        left join lateral (
			            select *
			            from AllTimetable tt
			            where tt.Resource_id = allt.Resource_id
			              and allt.TimetableResource_Day = tt.TimetableResource_Day
			            order by tt.TimetableResource_begTime
			            limit 1
			        ) as alltt on true
			        left join lateral (
			            select rtrim(A.Annotation_Comment) as annotate
			            from
			                v_Day D
			                left join v_Annotation A on
			                    (cast(A.Annotation_begDate as date) <= cast(D.day_date as date)) and
			                    (cast(A.Annotation_endDate as date) >= cast(D.day_date as date) or A.Annotation_endDate is null) and
			                    (A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) and
			                    (A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
			                left join v_Resource r on r.Resource_id = A.Resource_id
			                left join v_MedService ms on ms.MedService_id = r.MedService_id
			            where A.Resource_id = allt.Resource_id
			                and D.Day_id = allt.TimetableResource_Day
			            limit 1
			        ) as ann on true
			order by \"Resource_id\", \"TimetableResource_Day\"
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $param);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает список бирок для служб
	 * @param $data
	 * @param $MSList
	 * @return array|bool
	 */
	function loadMedServiceTTList($data, $MSList)
	{
		if (empty($MSList)) {
			return false;
		}
		$param = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+14 days", $StartDay));
		$query = "
			WITH cte AS (
            	SELECT
            			GETDATE() as cur_dt,
						DATEADD('DAY', 14, GETDATE()) as upper_dt,
						CAST(GETDATE() AS DATE) as cur_date
            ),
			AllTimetable AS (
			SELECT t.TimetableMedService_id,
				   t.TimetableMedService_Day,
				   to_char(t.TimetableMedService_begTime, 'HH24:MI') AS formatTime,
				   to_char(t.TimetableMedService_begTime, 'YYYYMMDD') as dataIndex,
				   to_char(t.TimetableMedService_begTime, 'DD.MM.YYYY HH24:MI') as TimetableMedService_begTime,
				   t.TimetableType_id,
				   t.TimetableMedService_IsDop,
				   t.MedService_id
			FROM v_TimetableMedService_lite t 
			WHERE t.TimetableMedService_Day >= :StartDay
					AND t.TimetableMedService_Day < :EndDay
				  AND t.MedService_id IN (" . implode(',', $MSList) . ")
				  AND t.Person_id IS NULL
				  AND
				  (
					  CAST(t.TimetableMedService_begTime AS DATE) <= (SELECT upper_dt FROM cte)
					  AND CAST(t.TimetableMedService_begTime AS DATE) >= (SELECT cur_date FROM cte)
				  )
				  /* AND t.TimetableResource_begTime
					BETWEEN '2019-05-13' AND '2019-05-27'*/
			ORDER BY t.TimetableMedService_begTime
            )
		
			SELECT 
                allTT.TimetableMedService_id as \"TimetableMedService_id\",
                allTT.TimetableMedService_Day as \"TimetableMedService_Day\",
                allTT.formatTime as \"formatTime\",
                allTT.dataIndex as \"dataIndex\",
                allTT.TimetableMedService_begTime as \"TimetableMedService_begTime\",
                allTT.TimetableType_id as \"TimetableType_id\",
                allTT.TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
                allTT.MedService_id as \"MedService_id\",
				ann.annotate as \"annotate\"
			FROM (SELECT DISTINCT TimetableMedService_Day, MedService_id FROM AllTimetable) alld
			LEFT JOIN LATERAL
			(
				SELECT 
					   *
				FROM AllTimetable temp
				WHERE temp.TimetableMedService_Day = alld.TimetableMedService_Day
				AND  temp.MedService_id = alld.MedService_id
				ORDER BY temp.TimetableMedService_begTime
                LIMIT 1
			) allTT ON true
			LEFT JOIN LATERAL
			(
				SELECT 
					RTRIM(msd.MedServiceDay_Descr) as annotate
				from MedServiceDay msd 
				where MedService_id = alld.MedService_id
				and Day_id = alld.TimetableMedService_Day
                LIMIT 1
			) ann ON true
			ORDER BY allTT.MedService_id, \"TimetableMedService_Day\"
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $param);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает список бирок для ресурса за день
	 * @param $data
	 * @return array|bool
	 */
	function loadResourceTTListByDay($data)
	{
		$param = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param["StartDay"] = TimeToDay($StartDay);
		$param["Resource_id"] = $data["Resource_id"];
		$query = "
			select
				t.TimetableResource_id as \"TimetableResource_id\",
				t.Person_id as \"Person_id\",
				case when t.Person_id is null
				    then 'empty'
				    else 'full'
				end as \"class\",
				t.TimetableResource_Day as \"TimetableResource_Day\",
				to_char(t.TimetableResource_begTime, '{$this->dateTimeForm120}') as \"TimetableResource_begTime\",
				to_char(t.TimetableResource_begTime, '{$this->dateTimeForm108}') as \"formatTime\",
				t.TimetableType_id as \"TimetableType_id\",
				t.TimetableResource_IsDop as \"IsDop\"
			from v_TimetableResource_lite t
			where t.TimetableResource_Day = :StartDay
			  and t.Resource_id = :Resource_id
			order by t.TimetableResource_begTime
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $param);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает список бирок для службы за день
	 * @param $data
	 * @return array|bool
	 */
	function loadMedServiceTTListByDay($data)
	{
		$param = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param["StartDay"] = TimeToDay($StartDay);
		$param['MedService_id'] = !empty($data['pzm_MedService_id']) ? $data['pzm_MedService_id'] : $data['MedService_id'];
		$UslugaFilter = ' and t.MedService_id = :MedService_id';
		if(!empty($data['UslugaComplexMedService_id']) || !empty($data['pzm_UslugaComplexMedService_id'])){
			$UslugaFilter = ' and t.UslugaComplexMedService_id = :UslugaComplexMedService_id';
			$param['UslugaComplexMedService_id'] = !empty($data['pzm_UslugaComplexMedService_id']) ? $data['pzm_UslugaComplexMedService_id'] : $data['UslugaComplexMedService_id'];
		}
		$query = "
			select 
				t.TimetableMedService_id as \"TimetableMedService_id\",
				t.Person_id as \"Person_id\",
				t.TimetableMedService_Day as \"TimetableMedService_Day\",
				to_char(t.TimetableMedService_begTime, '{$this->dateTimeForm120}') as \"TimetableMedService_begTime\",
				to_char(t.TimetableMedService_begTime, '{$this->dateTimeForm108}') as \"formatTime\",
				t.TimetableType_id as \"TimetableType_id\",
				t.TimetableMedService_IsDop as \"IsDop\"
			from v_TimetableMedService_lite t
			where t.TimetableMedService_Day = :StartDay
			  {$UslugaFilter}
			  and t.MedService_id = :MedService_id
			order by t.TimetableMedService_begTime
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $param);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает примечание врача для службы за день
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function loadMedServiceAnnotateByDay($data)
	{
		$param = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param["StartDay"] = TimeToDay($StartDay);
		$param["MedService_id"] = $data["MedService_id"];

		$UslugaFilter = ' and msd.MedService_id = :MedService_id';
		if(!empty($data['UslugaComplexMedService_id'])){
			$UslugaFilter = ' and msd.UslugaComplexMedService_id = :UslugaComplexMedService_id';
			$param['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];
		}
		
		$query = "
			select
				'' as \"Error_Msg\",
				rtrim(msd.MedServiceDay_Descr) as \"annotate\"
			from MedServiceDay msd
			where MedService_id = :MedService_id
			  and Day_id = :StartDay
			  {$UslugaFilter}
		";
		$res = $this->db->query($query, $param);
		if (!is_object($res)) {
			return [
				"success" => false,
				"Error_Msg" => "Ошибка загрузки примечания"
			];
		}
		$resp = $res->result("array");
		if (!empty($resp)) {
			return $resp[0];
		} else {
			return ["Error_Msg" => ""];
		}
	}

	/**
	 * Возвращает примечания врача для ресурса за день
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function loadResourceAnnotateByDay($data)
	{
		$param = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param["StartDay"] = TimeToDay($StartDay);
		$param["Resource_id"] = $data["Resource_id"];
		$param["Lpu_id"] = $data["Lpu_id"];
		$param["nulltime"] = "00:00:00";
		$query = "
			select
				'' as \"Error_Msg\",
				rtrim(A.Annotation_Comment) as \"annotate\"
			from v_Day D
				 left join v_Annotation A on
					cast(A.Annotation_begDate as date) <= cast(D.day_date as date) AND
					(cast(A.Annotation_endDate as date) >= cast(D.day_date as date) OR A.Annotation_endDate is null) AND
					(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
					(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
				left join v_Resource r on r.Resource_id = A.Resource_id
				left join v_MedService ms on ms.MedService_id = r.MedService_id
			where A.Resource_id = :Resource_id
			  and D.Day_id = :StartDay
			  and (A.AnnotationVison_id != 3 or ms.Lpu_id = :Lpu_id)
		";
		$res = $this->db->query($query, $param);
		if (!is_object($res)) {
			return [
				"success" => false,
				"Error_Msg" => "Ошибка загрузки примечания"
			];
		}
		$resp = $res->result("array");
		if (!empty($resp)) {
			return $resp[0];
		} else {
			return ["Error_Msg" => ""];
		}
	}

	/**
	 * Возвращает список бирок на услуге
	 * @param $data
	 * @param $UslList
	 * @return array|bool
	 */
	function loadUslugaComplexTTList($data, $UslList)
	{
		if (empty($UslList)) {
			return false;
		}
		$param = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+14 days", $StartDay));
		$query = "
			-- расписание на услугах
			WITH AllTimetable AS (
			SELECT
				t.TimetableMedService_updDT,
				t.TimetableMedService_id,
				t.Person_id,
				t.TimetableMedService_Day,
				to_char(t.TimetableMedService_begTime, 'HH24:MI') AS formatTime,
				to_char(t.TimetableMedService_begTime, 'YYYYMMDD') as dataIndex,
				to_char(t.TimetableMedService_begTime, 'DD.MM.YYYY HH24:MI') as TimetableMedService_begTime,
				t.TimetableType_id,
				t.TimetableMedService_IsDop,
				t.UslugaComplexMedService_id
			from v_TimetableMedService_lite t
			WHERE t.TimetableMedService_Day >= :StartDay
				  AND t.TimetableMedService_Day < :EndDay
				  AND t.Person_id IS NULL
				and t.UslugaComplexMedService_id IN (" . implode(',', $UslList) . ")
				 AND
				  (
					  CAST(t.TimetableMedService_begTime AS DATE) <= DATEADD('DAY', 14, GETDATE())
					  AND CAST(t.TimetableMedService_begTime AS DATE) >= CAST(GETDATE() AS DATE)
				  )
			order by t.UslugaComplexMedService_id, t.TimetableMedService_begTime
            )
		
			SELECT 
                allTT.TimetableMedService_updDT as \"TimetableMedService_updDT\",
                allTT.TimetableMedService_id as \"TimetableMedService_id\",
                allTT.Person_id as \"Person_id\",
                allTT.TimetableMedService_Day as \"TimetableMedService_Day\",
                allTT.formatTime as \"formatTime\",
                allTT.dataIndex as \"dataIndex\",
                allTT.TimetableMedService_begTime as \"TimetableMedService_begTime\",
                allTT.TimetableType_id as \"TimetableType_id\",
                allTT.TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
                allTT.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				ann.annotate as \"annotate\"
			FROM
			(SELECT DISTINCT TimetableMedService_Day, UslugaComplexMedService_id FROM AllTimetable) alld
			LEFT JOIN LATERAL
			(
				SELECT 
					   *
				FROM AllTimetable temp
				WHERE temp.TimetableMedService_Day = alld.TimetableMedService_Day
				AND  temp.UslugaComplexMedService_id = alld.UslugaComplexMedService_id
				ORDER BY temp.TimetableMedService_begTime
                LIMIT 1
			) allTT ON true
			LEFT JOIN LATERAL
			(
				SELECT 
					RTRIM(msd.MedServiceDay_Descr) as annotate
				from MedServiceDay msd 
				where UslugaComplexMedService_id  = alld.UslugaComplexMedService_id
				and Day_id = alld.TimetableMedService_Day
                LIMIT 1
			) ann ON true
			ORDER BY allTT.UslugaComplexMedService_id, \"TimetableMedService_Day\"
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $param);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

    function getPzmRecordData($data){

        $params['TimetableMedService_id'] = $data['TimetableMedService_id'];

        $query = "
			select
				ttms.TimetableMedService_id as \"TimetableMedService_id\",
				ms_pzm.MedService_id as \"MedService_pzid\",
				to_char (ttms.TimetableMedService_begTime, 'yyyy-mm-dd hh24:mm:ss') as \"EvnLabRequest_prmTime\"
			from
				v_TimetableMedService_lite ttms
				left join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
				LEFT JOIN LATERAL(
					select
						ms.MedService_id as MedService_id
					from
						v_MedService ms
						inner join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
					where
						ms.MedService_id = COALESCE(ucms.MedService_id, ttms.MedService_id)
						and mst.MedServiceType_SysNick = 'pzm'
                    limit 1
				) ms_pzm ON TRUE
			where
				ttms.TimetableMedService_id = :TimetableMedService_id
            limit 1
		";

        $result = $this->queryResult($query, $params);
        return $result;
	}
	
	function getMedPesonalZid($data){
		$sql = "
				select
					msf.MedPersonal_id
				from
					v_MedStaffFact msf
					inner join v_PostMed ps on ps.PostMed_id = msf.Post_id
				where
					ps.PostMed_Name like '%заведующ%' -- запрос для заведующих
					and
					msf.LpuSection_id = :LpuSection_id
					order by msf.WorkData_begDate desc
				limit 1
			";
		$MedPersonal_zid = $this->db->query($sql, array(
				'LpuSection_id' => $data['LpuSection_id']
		))->row_array();
		if (!empty($MedPersonal_zid['MedPersonal_id'])) {
			return $MedPersonal_zid['MedPersonal_id'];
		}
		return null;
	}
}
