<?php
require_once("Timetable_model.php");
/**
 * TimetableResource_model - модель для работы с расписанием ресурса
 * Загрузка базовой модели для работы с расписанием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      28.12.2011
 *
 * @property Annotation_model $Annotation_model
 */

class TimetableResource_model extends Timetable_model
{
	function __construct()
	{
		parent::__construct();
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
			"source" => "v_TimetableResource_lite",
			"fields" => "
				null as \"TimetableExtend_Descr\",
				null as \"TimetableExtend_updDT\",
				null as \"TimetableExtend_pmUser_Name\"
			",
			"join" => ""
		];
		$isExtend = in_array(getRegionNick(), ["kareliya"]);
		if ($isExtend) {
			$extend = [
				"source" => "v_TimetableResource",
				"fields" => "
					t.TimetableExtend_Descr as \"TimetableExtend_Descr\",
					t.TimetableExtend_updDT as \"TimetableExtend_updDT\",
					ud.pmUser_Name as \"TimetableExtend_pmUser_Name\"
				",
				"join" => "left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id"
			];
		}
		return $extend;
	}

	/**
	 * Получение расписания ресурса для редактирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTimetableResourceForEdit($data)
	{
		if (!isset($data["Resource_id"])) {
			throw new Exception("Не указана услуга, для которой показывать расписание");
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata = [
			"StartDay" => $StartDay,
			"header" => [],
			"descr" => [],
			"data" => [],
			"occupied" => [],
		];
		$param = [
			"StartDay" => TimeToDay($StartDay),
			"EndDay" => TimeToDay(strtotime("+14 days", $StartDay)),
			"Resource_id" => $data["Resource_id"],
			"StartDate" => date("Y-m-d", $StartDay),
			"EndDate" => date("Y-m-d", strtotime("+14 days", $StartDay)),
			"nulltime" => "00:00:00",
			"StartDayA" => TimeToDay(strtotime("-1 day", $StartDay)),
			"EndDayA" => TimeToDay(strtotime("+13 days", $StartDay)),
			"Lpu_id" => $data["Lpu_id"]
		];
		//https://redmine.swan.perm.ru/issues/72692:
		if ($data["PanelID"] == "TTRRecordPanel" || $data["PanelID"] == "TTRDirectionPanel") {
			//Одна и та же функция используется как для редактирования расписания, так и для записи. Поэтому добавил условие на парент.
            $msflpu = $this->getFirstRowFromQuery("
                    select 
                        ms.Lpu_id as \"Lpu_id\", 
                        ms.MedService_id as \"MedService_id\" 
                    from 
                        v_MedService ms
                        inner join v_Resource r  on ms.MedService_id = r.MedService_id 
                    where Resource_id = ?", array($data['Resource_id']));
            $maxDays = GetMedServiceDayCount($msflpu['Lpu_id'], $msflpu['MedService_id']);
			if (date("H:i") >= getShowNewDayTime() && $maxDays) {
				$maxDays++;
			}
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+{$maxDays} days", time())) : $param["EndDate"];
		}
		$nTime = $StartDay;
		for ($nCol = 0; $nCol < 14; $nCol++) {
			$nWeekDay = date("w", $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class=\"{$sClass}\"><b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;
			$nTime = strtotime("+1 day", $nTime);
		}
		$sql = "
			select
				D.Day_id as \"Day_id\",
				rtrim(A.Annotation_Comment) as \"Annotation_Comment\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				A.Annotation_updDT as \"Annotation_updDT\"
			from
				v_Day D
				left join v_Annotation A on A.Annotation_begDate::date <= D.day_date::date
					and (A.Annotation_endDate::date >= D.day_date::date or A.Annotation_endDate is null)
				    and (A.Annotation_begTime is null or A.Annotation_begTime = :nulltime)
					and (A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
				left join v_pmUser u on u.pmUser_id = A.pmUser_updID
				left join v_Resource r on r.Resource_id = A.Resource_id
				left join v_MedService ms on ms.MedService_id = r.MedService_id
			where A.Resource_id = :Resource_id
			  and D.Day_id >= :StartDayA
			  and D.Day_id < :EndDayA
			  and (A.AnnotationVison_id != 3 or ms.Lpu_id = :Lpu_id)
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $param);
		$daydescrdata = $result->result("array");

		foreach ($daydescrdata as $day) {
			/**@var DateTime $Annotation_updDT */
			$Annotation_updDT = $day["Annotation_updDT"];
			$outdata["descr"][++$day["Day_id"]][] = [
				"Annotation_Comment" => $day["Annotation_Comment"],
				"pmUser_Name" => $day["pmUser_Name"],
				"Annotation_updDT" => ($Annotation_updDT != null) ? ConvertDateFormat($Annotation_updDT,"d.m.Y H:i") : ""
			];
		}
		// Получаем примечания к биркам за период
		// @task https://redmine.swan.perm.ru/issues/128771
		$param["CurrentLpu_id"] = $data["session"]["lpu_id"];
		$query = "
			select
				to_char(A.Annotation_begDate, 'YYYY-MM-DD HH24:MI:SS') as \"Annotation_begDate\",
				to_char(A.Annotation_endDate, 'YYYY-MM-DD HH24:MI:SS') as \"Annotation_endDate\",
				to_char(A.Annotation_begTime, 'HH24:MI:SS') as \"Annotation_begTime\",
				to_char(A.Annotation_endTime, 'HH24:MI:SS') as \"Annotation_endTime\",
				rtrim(A.Annotation_Comment) as \"Annotation_Comment\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				A.Annotation_updDT as \"Annotation_updDT\"
			from
				v_Annotation A
				left join v_pmUser u on u.pmUser_id = A.pmUser_updID
				left join v_Resource r on r.Resource_id = A.Resource_id
				left join v_MedService ms on ms.MedService_id = r.MedService_id
			where A.Resource_id = :Resource_id
			  and (A.Annotation_begDate is null or A.Annotation_begDate <= :EndDate::date)
			  and (A.Annotation_endDate is null or :StartDate::date <= A.Annotation_endDate)
			  and (A.Annotation_begTime is not null or A.Annotation_endTime is not null)
			  and (A.AnnotationVison_id != 3 or ms.Lpu_id = :CurrentLpu_id)
		";
		$annotationdata = $this->queryResult($query, $param);
		if ($annotationdata === false) {
			$annotationdata = [];
		}
		$ext = $this->getTimeTableExtendData($data);
		$selectPersonData = "
			to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			priv.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",";
		}
		$selectString = "
			t.pmUser_updID as \"pmUser_updID\",
			to_char(t.TimetableResource_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableResource_updDT\",
			t.TimetableResource_id as \"TimetableResource_id\",
			t.Person_id as \"Person_id\",
			t.TimetableResource_Day as \"TimetableResource_Day\",
			to_char(t.TimetableResource_begTime, 'yyyy-mm-dd HH24:MI:SS') as \"TimetableResource_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableResource_IsDop as \"TimetableResource_IsDop\",
			{$selectPersonData}
			t.PMUser_UpdID \"PMUser_UpdID\",
			case
			    when t.pmUser_updid = 999000 then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000 then 'Запись через интернет'
				else u.PMUser_Name 
			end as \"PMUser_Name\",
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			d.EvnDirection_TalonCode as \"Direction_TalonCode\",
			to_char(d.EvnDirection_setDate, 'DD.MM.YYYY') as \"Direction_Date\",
			d.Evn_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			epd.EvnPrescr_id as \"EvnPrescr_id\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			t.Resource_id as \"Diag_Code\",
			{$ext["fields"]}
		";
		$fromString = "
			{$ext["source"]} t
            left join v_Personstate p on t.Person_id = p.Person_id 
	        left join person ON person.person_id = p.person_id AND person.person_deleted = 1
            left join lateral ( 
                select 
                    personprivilege.privilegetype_id
                from personprivilege
                where 
                    personprivilege.person_id = p.person_id 
                and (personprivilege.personprivilege_enddate IS NULL 
                or personprivilege.personprivilege_enddate > getdate()) 
                and (personprivilege.privilegetype_id = any (ARRAY[11::bigint, 20::bigint, 40::bigint, 50::bigint, 140::bigint, 150::bigint]))
                order by personprivilege.privilegetype_id
                limit 1
            ) priv ON true
			left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			{$ext["join"]}
			left join lateral (
				select
					Evn.Evn_setDT as EvnDirection_setDate,
				    Evn.Lpu_id as Evn_Lpu_id,
				    d.Diag_id,
				    d.Evn_id,
				    d.EvnDirection_Num,
				    d.EvnDirection_TalonCode
				from
					EvnDirection d
					inner join Evn on Evn.Evn_id = d.Evn_id and Evn.Evn_deleted = 1 
				where t.TimetableResource_id = d.TimetableResource_id
				  and d.DirFailType_id is null
				limit 1
			) as d on true
			left join lateral (
				select epd.EvnPrescr_id
				from v_EvnPrescrDirection epd
				where epd.EvnDirection_id = d.Evn_id
				limit 1
			) as epd on true
			left join v_Lpu lpud ON lpud.Lpu_id = d.Evn_Lpu_id
			left join lateral  (
				select Evn.pmUser_updId as Evn_pmUser_updId, q.Evn_insDT as EvnQueue_insDT 
				from
					EvnQueue q
					inner join Evn on Evn.Evn_id = q.Evn_id and Evn.Evn_deleted = 1 
				where t.TimetableResource_id = q.TimetableResource_id
				limit 1
			) as q on true
			left join v_pmUser qp on q.Evn_pmUser_updId = qp.pmUser_id
			left join Diag dg on dg.Diag_id = d.Diag_id
			{$joinPersonEncrypHIV}
		";
		$whereString = "
				t.TimetableResource_Day >= :StartDay
			and t.TimetableResource_Day < :EndDay
			and t.Resource_id = :Resource_id
			and TimetableResource_begTime between :StartDate and :EndDate
		";
		$orderByString = "t.TimetableResource_begTime";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		$result = $this->db->query($sql, $param);
		$ttrdata = $result->result("array");
		foreach ($ttrdata as $ttr) {
			$ttrannotation = [];
			foreach ($annotationdata as $annotation) {
				/**@var DateTime $TimetableResource_begTime */
				$TimetableResource_begTime = DateTime::createFromFormat('Y-m-d H:i:s', $ttr["TimetableResource_begTime"]);
				if (
					(empty($annotation["Annotation_begDate"]) || $annotation["Annotation_begDate"] <= ConvertDateFormat($TimetableResource_begTime,"Y-m-d")) &&
					(empty($annotation["Annotation_endDate"]) || $annotation["Annotation_endDate"] >= ConvertDateFormat($TimetableResource_begTime,"Y-m-d")) &&
					(empty($annotation["Annotation_begTime"]) || $annotation["Annotation_begTime"] <= ConvertDateFormat($TimetableResource_begTime,"H:i")) &&
					(empty($annotation["Annotation_endTime"]) || $annotation["Annotation_endTime"] >= ConvertDateFormat($TimetableResource_begTime,"H:i"))
				) {
					$ttrannotation[] = $annotation;
				}
			}
			$ttr["annotation"] = $ttrannotation;
			$outdata["data"][$ttr["TimetableResource_Day"]][] = $ttr;
			if (isset($ttr["Person_id"])) {
				$outdata["occupied"][$ttr["TimetableResource_Day"]] = true;
			}
		}
		$outdata["reserved"] = [];
		$reserved = [];
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableResource_id"];
		}
		return $outdata;
	}

	/**
	 * Получение расписания на один день на ресурс
	 * @param $data
	 * @return array
	 */
	function getTimetableResourceOneDay($data)
	{
		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$outdata = [
			"StartDay" => $StartDay,
			"day_comment" => null,
			"data" => []
		];
		$param = [
			"StartDay" => TimeToDay($StartDay),
			"Resource_id" => $data["Resource_id"],
			"EndDate" => date("Y-m-d", $StartDay)
		];
		if ($data["PanelID"] == "TTGRecordOneDayPanel") {
            $msflpu = $this->getFirstRowFromQuery("
                    select   
                        ms.Lpu_id as \"Lpu_id\", 
                        ms.MedService_id as \"MedService_id\" 
                    from 
                        v_MedService ms
                        inner join v_Resource r on ms.MedService_id = r.MedService_id 
                    where Resource_id = ?", array($data['Resource_id']));
            $maxDays = GetMedServiceDayCount($msflpu['Lpu_id'], $msflpu['MedService_id']);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) {
				$maxDays++;
			}
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+{$maxDays} days", time())) : $param["EndDate"];
		}
		$sql = "
			select
				rd.Day_id as \"Day_id\",
				rtrim(rd.ResourceDay_Descr) as \"ResourceDay_Descr\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				rd.ResourceDay_updDT as \"ResourceDay_updDT\"
			from
				ResourceDay rd
				left join v_pmUser u on u.pmUser_id = rd.pmUser_updID
			where Resource_id = :Resource_id
			  and Day_id = :StartDay
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $param);
		$daydescrdata = $result->result("array");
		if (isset($daydescrdata[0]["ResourceDay_Descr"])) {
			$outdata["day_comment"] = [
				"Resource_Descr" => $daydescrdata[0]["ResourceDay_Descr"],
				"pmUser_Name" => $daydescrdata[0]["pmUser_Name"],
				"ResourceDay_updDT" => $daydescrdata[0]["ResourceDay_updDT"]
			];
		}
		$ext = $this->getTimeTableExtendData($data);
		$selectPersonData = "
				to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				p.Person_Phone as \"Person_Phone\",
				p.PersonInfo_InternetPhone as \"Person_InetPhone\",
				case when a1.Address_id is not null then a1.Address_Address else a.Address_Address end as \"Address_Address\",
				case when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id end as \"KLTown_id\",
				case when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id end as \"KLStreet_id\",
				case when a1.Address_id is not null then a1.Address_House else a.Address_House end as \"Address_House\",
				j.Job_Name as \"Job_name\",
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
				case when peh.PersonEncrypHIV_Encryp is not null then null when a1.Address_id is not null then a1.Address_Address else a.Address_Address end as \"Address_Address\",
				case when peh.PersonEncrypHIV_Encryp is not null then null when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id end as \"KLTown_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id end as \"KLStreet_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null when a1.Address_id is not null then a1.Address_House else a.Address_House end as \"Address_House\",
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
			to_char(t.TimetableResource_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableResource_updDT\",
			t.TimetableResource_id as \"TimetableResource_id\",
			t.Person_id as \"Person_id\",
			t.TimetableResource_Day as \"TimetableResource_Day\",
			to_char(TimetableResource_begTime, 'yyyy-mm-dd HH24:MI:SS') as \"TimetableResource_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableResource_IsDop as \"TimetableResource_IsDop\",
			{$selectPersonData}
			t.PMUser_UpdID as \"PMUser_UpdID\",
			case
			    when t.pmUser_updid=999000 then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000 then 'Запись через интернет'
				else u.PMUser_Name
			end as \"PMUser_Name\",
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			d.EvnDirection_TalonCode as \"Direction_TalonCode\",
			to_char(d.EvnDirection_setDate, 'DD.MM.YYYY') as \"Direction_Date\",
			d.Evn_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			epd.EvnPrescr_id as \"EvnPrescr_id\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			ms.Lpu_id as \"Lpu_id\",
			{$ext["fields"]}
		";
		$fromString = "
			{$ext["source"]} t
			left outer join v_Resource r on r.Resource_id = t.Resource_id
			left outer join v_MedService ms on ms.MedService_id = r.MedService_id
			left join lateral ( select * from v_Person_ER p where t.Person_id = p.Person_id limit 1) p on true
			left outer join Address a on p.UAddress_id = a.Address_id
			left outer join Address a1 on p.PAddress_id = a1.Address_id
			left outer join KLStreet pas on a.KLStreet_id = pas.KLStreet_id
			left outer join KLStreet pas1 on a1.KLStreet_id = pas1.KLStreet_id
			left outer join v_Job_ER j on p.Job_id=j.Job_id
			left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			{$ext["join"]}
			left outer join v_Lpu lpu on lpu.Lpu_id = p.Lpu_id
			left join lateral(
				select
					d.Evn_setDT as EvnDirection_setDate,
					d.Lpu_id as Evn_Lpu_id,
					d.Diag_id,
					d.EvnDirection_Num,
					d.EvnDirection_TalonCode,
					d.Evn_id
				from
					EvnDirection d  
				where t.TimetableResource_id = d.TimetableResource_id
				  and d.DirFailType_id is null
				  and d.Evn_deleted = 1 
				limit 1
			) as d on true
			left join lateral(
				select epd.EvnPrescr_id
				from v_EvnPrescrDirection epd
				where epd.EvnDirection_id = d.Evn_id
				limit 1
			) epd on true
			left outer join v_Lpu lpud ON lpud.Lpu_id = d.Evn_Lpu_id
			left join lateral (
				select
					q.pmUser_updId as Evn_pmUser_updId,
					q.Evn_insDT as EvnQueue_insDT 
				from
					EvnQueue q
				where  t.TimetableResource_id = q.TimetableResource_id
				 and Evn_deleted = 1 
				limit 1
			) as q on true
			left join v_pmUser qp on q.Evn_pmUser_updId = qp.pmUser_id
			left join Diag dg on dg.Diag_id = d.Diag_id
			{$joinPersonEncrypHIV}
		";
		$whereString = "
				t.TimetableResource_Day = :StartDay
			and t.TimetableResource_begTime::date <= :EndDate
			and t.Resource_id = :Resource_id
			and TimetableResource_begTime is not null
		";
		$orderByString = "t.TimetableResource_begTime";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		$result = $this->db->query($sql, $param);
		$ttsdata = $result->result("array");
		foreach ($ttsdata as $tts) {
			$outdata["data"][] = $tts;
		}
		$sql = "
			select TimetableResource_id as \"TimetableResource_id\"
			from TimetableLock
		";
		$result = $this->db->query($sql);
		$outdata["reserved"] = $result->result("array");
		return $outdata;
	}

	/**
	 * Приём из очереди без записи
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function acceptWithoutRecord($data)
	{
		$this->load->helper("Reg");
		if (!empty($data["EvnDirection_id"])) {
			// если уже записано на бирку то ещё запись выполняться не должна
			$query = "
				select TimetableResource_id as \"TimetableResource_id\"
				from v_TimetableResource_lite
				where EvnDirection_id = :EvnDirection_id
			";
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception("Ошибка проверки наличия бирки");
			}
			$resp = $result->result("array");
			if (!empty($resp[0]["TimetableResource_id"])) {
				throw new Exception("Пациент уже принят");
			}
			$query = "
				select
					TTR.Resource_id as \"Resource_id\",
					ED.Person_id as \"Person_id\",
					to_char(tzgetdate(), 'DD.MM.YYYY') as date
				from
					v_EvnDirection_all ED
					inner join v_TimetableResource_lite TTR on TTR.TimetableResource_id = ED.TimetableResource_id
				where ED.EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (!empty($resp[0]["date"])) {
					$data["date"] = $resp[0]["date"];
					$data["Resource_id"] = $resp[0]["Resource_id"];
					$data["Person_id"] = $resp[0]["Person_id"];
				}
			}
		}
		if (empty($data["Person_id"])) {
			throw new Exception("Ошибка получения данных по направлению");
		}
		$Timetable_Day = empty($data["Timetable_Day"]) ? TimeToDay(strtotime($data["date"])) : $data["Timetable_Day"];
		$params = [
			"Person_id" => $data["Person_id"],
			"Resource_id" => $data["Resource_id"],
			"EvnDirection_id" => $data["EvnDirection_id"],
			"Timetable_Day" => $Timetable_Day,
			"pmUser_id" => $data["pmUser_id"]
		];
		$sql = "
		    select
		    	TimetableResource_id as \"TimetableResource_id\",
               	Error_Code as \"Error_Code\",
               	Error_Message as \"Error_Msg\"
            from p_TimetableResource_ins(
            	Person_id := :Person_id,
            	Resource_id := :Resource_id,
            	TimetableResource_begTime := dbo.tzGetDate()::timestamp ,
            	Evn_id := :EvnDirection_id,
            	TimetableResource_Day := :Timetable_Day,
            	RecClass_id := 3,
            	RecMethodType_id := 1,
            	pmUser_id := :pmUser_id
            )
		";
		$result = $this->db->query($sql, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка БД при создании бирки экстренного посещения пациентом врача!");
		}
		$resp = $result->result("array");
		if (!empty($resp[0]["TimetableResource_id"])) {
			// отправка STOMP-сообщения
			$funcParams = [
				"id" => $resp[0]["TimetableResource_id"],
				"timeTable" => "TimetableResource",
				"action" => "AddTicket",
				"setDate" => date("c")
			];
			sendFerStompMessage($funcParams, "Rule");
		}
		return $resp;
	}

	/**
	 * Создание расписания для ресурса
	 * @param $data
	 * @return array|bool|false|mixed
	 * @throws Exception
	 */
	function createTTRSchedule($data)
	{
		$this->beginTransaction();
		if ($data["CreateAnnotation"] == 1) {
			$this->load->model("Annotation_model");
			$annotation_data = $data;
			$annotation_data["Annotation_id"] = null;
			$annotation_data["MedService_id"] = null;
			$annotation_data["MedStaffFact_id"] = null;
			$annotation_data["Annotation_begDate"] = $data["CreateDateRange"][0];
			$annotation_data["Annotation_endDate"] = $data["CreateDateRange"][1];
			$annotation_data["Annotation_begTime"] = $data["StartTime"];
			$annotation_data["Annotation_endTime"] = $data["EndTime"];
			$res = $this->Annotation_model->save($annotation_data);
			if (!empty($res["Error_Msg"])) {
				$this->rollbackTransaction();
				return $res;
			}
		}
		$data["StartDay"] = TimeToDay(strtotime($data["CreateDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CreateDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][0]) < strtotime($archive_database_date)) {
				throw new Exception("Нельзя создать расписание на архивные даты.");
			}
		}
		if (strtotime($data["CreateDateRange"][0]) < strtotime(date("d.m.Y"))) {
			throw new Exception("Создание расписания на прошедшие периоды невозможно");
		}
		if (true !== ($res = $this->checkTimetableResourceTimeNotOccupied($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableResourceTimeNotExists($data))) {
			return $res;
		}
		$nStartTime = StringToTime($data["StartTime"]);
		$nEndTime = StringToTime($data["EndTime"]);
		for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
			$data["Day"] = $day;
			$sql = "
			    select
			    	Error_Code as \"Error_code\",
			    	Error_Message as \"Error_Message\"
			    from p_TimetableResource_fill (
			        Resource_id := :Resource_id,
					TimetableResource_Day := :TimetableResource_Day,
					TimetableResource_Time := :TimetableResource_Time,
					TimetableType_id := :TimetableType_id,
					StartTime := :StartTime,
					EndTime := :EndTime,
					pmUser_id := :pmUser_id
			    )
			";
			$sqlParams = [
				"Resource_id" => $data["Resource_id"],
				"TimetableResource_Day" => $day,
				"TimetableResource_Time" => $data["Duration"],
				"pmUser_id" => $data["pmUser_id"],
				"TimetableType_id" => $data["TimetableType_id"],
				"StartTime" => $data["StartTime"],
				"EndTime" => $data["EndTime"],
			];
			$resp = $this->queryResult($sql, $sqlParams);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
			// Пересчета для ресурса в хранимке нет, вызываем вручную
			$funcParams = [
				"Resource_id" => $data["Resource_id"],
				"Day_id" => $day,
				"pmUser_id" => $data["pmUser_id"],
			];
			$resp = $this->execCommonSP("p_ResourceDay_recount", $funcParams);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		$this->commitTransaction();
		// отправка STOMP-сообщения
		$funcParams = [
			"timeTable" => "TimetableResource",
			"action" => "AddTicket",
			"setDate" => date("c"),
			"begDate" => date("c", DayMinuteToTime($data["StartDay"], $nStartTime)),
			"endDate" => date("c", DayMinuteToTime($data["EndDay"], $nEndTime)),
			"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
		];
		sendFerStompMessage($funcParams, "RulePeriod");
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для ресурса
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableResourceTimeNotOccupied($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as cnt
				from v_TimetableResource_lite
				where Resource_id = :Resource_id
				  and Person_id is not null
				  and (
					TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime or
					((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) > :StartTime and (TimetableResource_begTime + (TimetableResource_Time|| ' minutes')::interval ) < :EndTime) or
					((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) > :StartTime and TimetableResource_begTime < :StartTime) 
				  )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"Resource_id" => $data["Resource_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$result = $result->result("array");
				if ($result[0]["cnt"] > 0) {
					throw new Exception("Нельзя очистить расписание, так как есть занятые бирки.");
				}
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as cnt
					from v_TimetableResource_lite
					where Resource_id = :Resource_id
					  and Person_id is not null
					  and (
						TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime or
						((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) > :StartTime and (TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) < :EndTime) or
						((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) > :StartTime and TimetableResource_begTime < :StartTime) 
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"Resource_id" => $data["Resource_id"]
				];
				$result = $this->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$result = $result->result("array");
					if ($result[0]["cnt"] > 0) {
						throw new Exception("Нельзя очистить расписание, так как есть занятые бирки.");
					}
				}
			}
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени созданные бирки для услуги
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableResourceTimeNotExists($data)
	{
		if (isset($data['Day'])) {
			$sql = "
				select count(*) as cnt
				from v_TimetableResource_lite
				where Resource_id = :Resource_id
				  and (
				      TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime or
				      ((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) > :StartTime and (TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) < :EndTime) or
				      ((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval)> :StartTime and TimetableResource_begTime < :StartTime) 
				  )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"UslugaComplexResource_id" => $data["UslugaComplexResource_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$result = $result->result("array");
				if ($result[0]["cnt"] > 0) {
					throw new Exception("В заданном интервале времени уже существуют бирки.");
				}
			}
		}
		//Если задано несколько дней - проходим в цикле
		if (isset($data["StartDay"])) {
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as cnt
					from v_TimetableResource_lite
					where Resource_id = :Resource_id
					  and (
					    TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime or
					    ((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) > :StartTime and (TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) < :EndTime) or
					    ((TimetableResource_begTime::date + (TimetableResource_Time||' minutes')::interval) > :StartTime and TimetableResource_begTime < :StartTime) 
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"Resource_id" => $data["Resource_id"]
				];
				$result = $this->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$result = $result->result("array");
					if ($result[0]["cnt"] > 0) {
						throw new Exception("В заданном интервале времени уже существуют бирки.");
					}
				}
			}
		}
		return true;
	}

	/**
	 * Копирование расписания для ресурса
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function copyTTRSchedule($data)
	{
		if (empty($data['CopyToDateRange'][0])) {
			return ['Error_Msg' => 'Не указан диапазон для вставки расписания.'];
		}
		$this->beginTransaction();
		if (count($data["copyAnnotationGridData"])) {
			$this->load->model("Annotation_model");
			$annotation_data = $data;
		}

		$data['StartDay'] = TimeToDay( strtotime( $data['CopyToDateRange'][0] ) );
		$data['EndDay'] = TimeToDay( strtotime( $data['CopyToDateRange'][1] ) );

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item('archive_database_date');
			if (strtotime( $data['CreateDateRange'][1] ) < strtotime($archive_database_date)) {
				return ['Error_Msg' => 'Нельзя скопировать расписание на архивные даты.'];
			}
		}
		if (true !== ($res = $this->checkTimetableResourceDayNotOccupied($data))) {
			return ['Error_Msg' => 'Нельзя скопировать расписание на промежуток, так как на нем занятые бирки.'];
		}
		$n = 0;
		$nShift = TimeToDay(strtotime($data["CreateDateRange"][1])) - TimeToDay(strtotime($data["CreateDateRange"][0])) + 1;
		$nTargetEnd = 0;
		while ($nTargetEnd < $data['EndDay']) {
			$nTargetStart = $data['StartDay'] + $nShift * $n;
			$nTargetEnd = $data['StartDay'] + $nShift * ($n+1) - 1;
			$nTargetEnd = min($nTargetEnd, $data['EndDay']);

			$SourceStartDay = TimeToDay(strtotime($data['CreateDateRange'][0]));
			$SourceEndDay = TimeToDay(strtotime($data['CreateDateRange'][1]));
			$SourceEndDay = min($SourceEndDay, (TimeToDay(strtotime($data['CreateDateRange'][0])) + $nTargetEnd - $nTargetStart));

			if (count($data['copyAnnotationGridData'])) {
				$annotation_data['Annotation_copyFromDate'] = date('Y-m-d', strtotime($data['CreateDateRange'][0])); // Начало копируемого интервала
				$annotation_data['Annotation_begDate'] = date('Y-m-d', strtotime( $data['CopyToDateRange'][0] ) + 86400 * $nShift * $n); // Начало целевого интервала
				$annotation_data['Annotation_endDate'] = date('Y-m-d', strtotime( $data['CopyToDateRange'][0] ) + 86400 * $nShift * ($n+1) - 86400); // Окончание целевого интервала
				$annotation_data['Annotation_endDate'] = min($annotation_data['Annotation_endDate'], date('Y-m-d', strtotime($data['CopyToDateRange'][1])));
				foreach ($data['copyAnnotationGridData'] as $annotation_id) {
					$annotation_data['Annotation_id'] = $annotation_id;
					$res = $this->Annotation_model->copy($annotation_data);
					if (!empty($res["Error_Msg"])) {
						$this->rollbackTransaction();
						return $res;
					}
				}
			}
			$sql = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_TimetableResource_copy(
					Resource_id := :Resource_id,
					SourceStartDay := :SourceStartDay,
					SourceEndDay := :SourceEndDay,
					TargetStartDay := :TargetStartDay,
					TargetEndDay := :TargetEndDay,
					CopyTimetableExtend_Descr := :CopyTimetableExtend_Descr,
					pmUser_id := :pmUser_id
				)
				";

			$res = $this->db->query(
				$sql, [
					'Resource_id' => $data['Resource_id'],
					'SourceStartDay' => $SourceStartDay,
					'SourceEndDay' => $SourceEndDay,
					'TargetStartDay' => $nTargetStart,
					'TargetEndDay' => $nTargetEnd,
					'CopyTimetableExtend_Descr' => NULL,
					'pmUser_id' => $data['pmUser_id']
				]
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				$this->commitTransaction();
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					$funcParams = [
						"timeTable" => "TimetableResource",
						"action" => "AddTicket",
						"setDate" => date("c"),
						"begDate" => date("c", DayMinuteToTime($nTargetStart, 0)),
						"endDate" => date("c", DayMinuteToTime($nTargetEnd, 0)),
						"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
					];
					sendFerStompMessage($funcParams, "RulePeriod");
				}
			}
			$n++;
		}
		return ["Error_Msg" => ""];
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
	            Error_Code as \"Error_Code\",
	            Error_Message as \"Error_Msg\"
	        from p_TimetableResource_clearDay(
	            TimetableResource_Day := :TimetableResource_Day,
				Resource_id := :Resource_id,
				pmUser_id := :pmUser_id
	        )
	    ";
		$sqlParams = [
			"Resource_id" => $data["Resource_id"],
			"TimetableResource_Day" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
				// отправка STOMP-сообщения
				$funcParams = [
					"timeTable" => "TimetableResource",
					"action" => "DelTicket",
					"setDate" => date("c"),
					"begDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"endDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"MedStaffFact_id" => (!empty($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : null)
				];
				sendFerStompMessage($funcParams, "RulePeriod");
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение истории изменения бирки службы
	 * @param $data
	 * @return array|bool
	 */
	function getTTRHistory($data)
	{
		$selectPersonData = "
			rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), '')) as Person_FIO,
			to_char(Person_BirthDay, 'DD.MM.YYYY') as Person_BirthDay
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), '')) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_FIO\",
				case when peh.PersonEncrypHIV_Encryp is null then to_char(Person_BirthDay, 'DD.MM.YYYY') else null end as \"Person_BirthDay\"
			";
		}
		$sql = "
			select
				to_char(TimetableResourceHist_insDT, 'DD.MM.YYYY')||' '||to_char(TimetableResourceHist_insDT, 'HH24:MI:SS') as \"TimetableHist_insDT\",
				rtrim(PMUser_Name) as \"PMUser_Name\",
				TimetableActionType_Name as \"TimetableActionType_Name\",
				TimetableType_Name as \"TimetableType_Name\",
				{$selectPersonData}
			from
				TimetableResourceHist ttsh
				left join v_pmUser pu on ttsh.TimetableResourceHist_userID = pu.pmuser_id
				left join TimetableActionType ttat on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
				left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(ttsh.TimetableType_id, 1)
				left join v_Person_ER p on ttsh.Person_id = p.Person_id
				{$joinPersonEncrypHIV}
			where TimetableResource_id = :TimetableResource_id
		";
		$sqlParams = ["TimetableResource_id" => $data["TimetableResource_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableResourceOccupied($data)
	{
		$sql = "
			select
				TimetableResource_id as \"TimetableResource_id\",
				Person_id as \"Person_id\"
			from v_TimetableResource_lite
			where TimetableResource_id = :Id
		";

		$res = $this->db->query(
			$sql, [
				'Id' => $data['TimetableResource_id']
			]
		);
		if (is_object($res)) {
			$res = $res->result('array');
		}
		if ( !isset($res[0]) || !isset($res[0]['TimetableResource_id']) ) {
			return [
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			];
		}
		if ( !isset($res[0]['Person_id']) ) {
			return [
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже свободна.'
			];
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для ресурса
	 * @param $data
	 * @return bool
	 */
	function checkTimetableResourceDayNotOccupied($data)
	{
		/**@var CI_DB_result $result */
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as cnt
				from v_TimetableResource_lite
				where TimetableResource_Day = :Day
				  and Resource_id = :Resource_id
				  and Person_id is not null
				  and TimetableResource_begTime is not null
			";
			$sqlParams = [
				"Day" => $data["Day"],
				"Resource_id" => $data["Resource_id"],
			];
			$result = $this->db->query($sql, $sqlParams);
		}
		if (isset($data["StartDay"])) {
			$sql = "
				select count(*) as cnt
				from v_TimetableResource_lite
				where TimetableResource_day between :StartDay and :EndDay
				  and Resource_id = :Resource_id
				  and Person_id is not null
				  and TimetableResource_begTime is not null
			";
			$sqlParams = [
				"StartDay" => $data["StartDay"],
				"EndDay" => $data["EndDay"],
				"Resource_id" => $data["Resource_id"],
			];
			$result = $this->db->query($sql, $sqlParams);
		}
		if (is_object($result)) {
			$result = $result->result("array");
			if ($result[0]["cnt"] > 0) {
				return false;
			}
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
	function setTTRType($data)
	{
		$data["object"] = "TimetableResource";
		if (isset($data["TimetableResourceGroup"])) {
			$data["TimetableResourceGroup"] = json_decode($data["TimetableResourceGroup"]);
		}
		if (isset($data["TimetableResourceGroup"]) && count($data["TimetableResourceGroup"]) > 0) {
			// Обработка группы бирок в отдельном методе
			return $this->setTTRTypeGroup($data);
		} else {
			if (true === ($res = $this->checkTimetableResourceOccupied($data))) {
				throw new Exception("Бирка занята, изменение типа невозможно.");
			}
		}
		// Получаем службу и день, а также заодно проверяем, что бирка существует
		$sql = "
			select
				Resource_id as \"Resource_id\",
				TimetableResource_Day as \"TimetableResource_Day\"
			from v_TimetableResource_lite
			where TimetableResource_id = :TimetableResource_id
		";
		$sqlParams = ["TimetableResource_id" => $data["TimetableResource_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			throw new Exception("Бирка с таким идентификатором не существует.");
		}
		$sql = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\",
                (
	                select TimetableType_SysNick 
	                from v_TimetableType 
	                where TimetableType_id = :TimetableType_id
	                limit 1
                ) as \"TimetableType_SysNick\"
            from p_TimetableResource_setType (
				TimetableResource_id := :TimetableResource_id,
				TimetableType_id := :TimetableType_id,
				pmUser_id := :pmUser_id
			)                    
		";
		$sqlParams = [
			"TimetableResource_id" => $data["TimetableResource_id"],
			"TimetableType_id" => $data["TimetableType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($sql, $sqlParams);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
				$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
				if (!empty($action)) {
					// отправка STOMP-сообщения
					$funcParams = [
						"id" => $data["TimetableResource_id"],
						"timeTable" => "TimetableResource",
						"action" => $action,
						"setDate" => date("c")
					];
					sendFerStompMessage($funcParams, "Rule");
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Изменение типа бирок у службы для группы бирок
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function setTTRTypeGroup($data)
	{
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем службу и список дней, на которые мы выделили бирки
		$TimetableResourceGroupString = implode(",", $data["TimetableResourceGroup"]);
		$sql = "
			select
				TimetableResource_id as \"TimetableResource_id\",
				Resource_id as \"Resource_id\",
				TimetableResource_Day as \"TimetableResource_Day\"
			from v_TimetableResource_lite
			where TimetableResource_id in ({$TimetableResourceGroupString})
		";
		$sqlParams = ["TimetableResource_id" => $data["TimetableResource_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		// Меняем тип у каждой бирки по отдельности. Не лучший вариант конечно
		foreach ($result as $row) {
			$sql = "
                select 
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\",
                    (
	                    select TimetableType_SysNick 
	                    from v_TimetableType 
	                    where TimetableType_id = :TimetableType_id
	                    limit 1
                    ) as \"TimetableType_SysNick\"
                from p_TimetableResource_setType(
					TimetableResource_id := :TimetableResource_id,
					TimetableType_id := :TimetableType_id,
					pmUser_id := :pmUser_id
				)                    
            ";
			$sqlParams = [
				"TimetableResource_id" => $row["TimetableResource_id"],
				"TimetableType_id" => $data["TimetableType_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
					$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
					if (!empty($action)) {
						// отправка STOMP-сообщения
						$funcParams = [
							"id" => $row["TimetableResource_id"],
							"timeTable" => "TimetableResource",
							"action" => $action,
							"setDate" => date("c")
						];
						sendFerStompMessage($funcParams, "Rule");
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
		$data["object"] = "TimetableResource";
		$data["TimetableResourceGroup"] = (isset($data["TimetableResource_id"])) ? [$data["TimetableResource_id"]] : json_decode($data["TimetableResourceGroup"]);
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем врача и список дней, на которые мы выделили бирки
		$TimetableResourceGroupString = implode(",", $data["TimetableResourceGroup"]);
		$sql = "
			select
				TimetableResource_id as \"TimetableResource_id\",
				TimetableResource_Day as \"TimetableResource_Day\"
			from v_TimetableResource_lite
			where TimetableResource_id in ({$TimetableResourceGroupString})
		";
		/**
		 * @var CI_DB_result $result
		 */
		$result = $this->db->query($sql);
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
                	Error_Message as \"Error_Message\"
                from p_TimetableResource_del (
                	TimetableResource_id := :TimetableResource_id,
					pmUser_id := :pmUser_id
                )
            ";
			$sqlParams = [
				"TimetableResource_id" => $row["TimetableResource_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					$funcParams = [
						"id" => $row["TimetableResource_id"],
						"timeTable" => "TimetableResource",
						"action" => "DelTicket",
						"setDate" => date("c")
					];
					sendFerStompMessage($funcParams, "Rule");
				}
			}
		}
		return ["success" => true];
	}

	/**
	 * Получение комментария на день для службы
	 * @param $data
	 * @return array|bool
	 */
	function getTTRDayComment($data)
	{
		$sql = "
			select
				mpd.ResourceDay_Descr as \"ResourceDay_Descr\",
				mpd.ResourceDay_id as \"ResourceDay_id\"
			from ResourceDay mpd
			where Resource_id = :Resource_id and Day_id = :Day_id
		";
		$sqlParams = [
			"Resource_id" => $data["Resource_id"],
			"Day_id" => $data["Day"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Сохранение комментария на день для службы
	 * @param $data
	 * @return array
	 */
	function saveTTRDayComment($data)
	{
		$sql = "
	        select
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
	        from p_ResourceDay_setDescr(
				Day_id := :Day_id,
				Resource_id := :Resource_id,
				ResourceDay_Descr := :ResourceDay_Descr,
				pmUser_id := :pmUser_id
	        )
	    ";
		$sqlParams = [
			"ResourceDay_Descr" => $data["ResourceDay_Descr"],
			"Resource_id" => $data["Resource_id"],
			"Day_id" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
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
		$objName = $data["object"];
		$obj = "{$objName}_id";

		$selectString = "
			t.{$obj} as \"{$obj}\",
			t.pmUser_updId as \"pmUser_updId\",
			pu.Lpu_id as \"Lpu_id\",
			l.Org_id as \"Org_id\"
		";
		$fromString = "
			{$objName} t
			left join v_pmUser pu on t.pmUser_updId = pu.pmUser_id
			left join v_Lpu l on l.Lpu_id = pu.Lpu_id
		";
		$whereString = "t.{$obj} = :obj";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$sqlParams = ["obj" => $data[$obj]];
		/**
		 * @var CI_DB_result $result
		 */
		$result = $this->db->query($sql, $sqlParams);
		if (is_object($result)) {
			$result = $result->result("array");
			if ($result[0][$obj] == null) {
				throw new Exception("Бирка с таким идентификатором не существует.");
			}
			if (!(($result[0]["pmUser_updId"] == $data["session"]["pmuser_id"]) || isCZAdmin() || isLpuRegAdmin($result[0]["Org_id"]) || isInetUser($result[0]["pmUser_updId"]))) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.");
			}
		}
		return true;
	}

	/**
	 * Редактирование переданного набора бирок
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function editTTRSet($data)
	{
		$TTRSet = json_decode($data["selectedTTR"]);
		if ($this->checkTTROccupied($TTRSet)) {
			throw new Exception("Одна из выбранных бирок занята. Операция невозможна.");
		}
		// Пустая строка передается как NULL, надо как пустую строку передавать
		$data["TimetableExtend_Descr"] = ($data["ChangeTTRDescr"])
			? isset($data["TimetableExtend_Descr"])
				? $data["TimetableExtend_Descr"]
				: ""
			: null;
		$data["TimetableType_id"] = ($data["ChangeTTRType"])
			? isset($data["TimetableType_id"])
				? $data["TimetableType_id"]
				: 1
			: null;
		$query = "
		    select 
		       (select TimetableType_SysNick from v_TimetableType where TimetableType_id = :TimetableType_id limit 1) as \"TimetableType_SysNick\",
		       Error_Code as \"Error_Code\",
		       Error_Message as \"Error_Msg\"
			from p_TimetableResource_edit(
				TimetableResource_id := :TimetableResource_id,
            	TimetableType_id := :TimetableType_id,
            	TimetableExtend_Descr := :TimetableExtend_Descr,
            	pmUser_id := :pmUser_id
			)
		";
		foreach ($TTRSet as $TTR) {
			/**@var CI_DB_result $result */
			$sqlParams = [
				"TimetableResource_id" => $TTR,
				"TimetableType_id" => $data["TimetableType_id"],
				"TimetableExtend_Descr" => $data["TimetableExtend_Descr"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $this->db->query($query, $sqlParams);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
					$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
					if (!empty($action)) {
						// отправка STOMP-сообщения
						$funcParams = [
							"id" => $TTR,
							"timeTable" => "TimetableResource",
							"action" => $action,
							"setDate" => date("c")
						];
						sendFerStompMessage($funcParams, "Rule");
					}
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, что хоть одна из набора переданных бирок занята
	 * @param $TTRSet
	 * @return bool
	 */
	function checkTTROccupied($TTRSet)
	{
		if (count($TTRSet) == 0) {
			return false;
		}
		$TTRSetString = implode(",", $TTRSet);
		$sql = "
			select count(*) as cnt
			from v_TimetableResource_lite
			where TimetableResource_id in ({$TTRSetString})
			  and Person_id is not null
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql);
		if (is_object($result)) {
			$result = $result->result("array");
			return $result[0]["cnt"] > 0;
		}
		return false;
	}

	/**
	 * Добавление дополнительной бирки для службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function addTTRDop($data)
	{
		if (empty($data["Day"])) {
			$data["Day"] = TimeToDay(time());
		}
		if (empty($data["StartTime"])) {
			$data["StartTime"] = date("H:i");
		}
		if (empty($data["Resource_id"])) {
			$sql = "
				select Resource_id as \"Resource_id\"
				from v_Resource
				where MedService_id = :MedService_id
				  and Resource_begDT <= :begDate
				  and (Resource_endDT is null or Resource_endDT > :begDate)
				limit 1
			";
			$sqlParams = [
				"MedService_id" => $data["MedService_id"],
				"begDate" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
			];
			$Resource_id = $this->getFirstResultFromQuery($sql, $sqlParams);
			if (!$Resource_id) {
				return false;
			}
			$data["Resource_id"] = $Resource_id;
		}

		$this->beginTransaction();
		if (!empty($data["CreateAnnotation"]) && $data["CreateAnnotation"] == 1) {
			$this->load->model("Annotation_model");
			$annotation_data = $data;
			$annotation_data["Annotation_id"] = null;
			$annotation_data["MedService_id"] = null;
			$annotation_data["MedStaffFact_id"] = null;
			$annotation_data["Annotation_begDate"] = date("Y-m-d", DayMinuteToTime($data["Day"], 0));
			$annotation_data["Annotation_endDate"] = date("Y-m-d", DayMinuteToTime($data["Day"], 0));
			$annotation_data["Annotation_begTime"] = $data["StartTime"];
			$annotation_data["Annotation_endTime"] = date("H:i", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"])) + 60);
			$res = $this->Annotation_model->save($annotation_data);
			if (!empty($res["Error_Msg"])) {
				$this->rollbackTransaction();
				return $res;
			}
		}
		if (empty($data["ignoreTTRExist"])) {
			$sql = "
				select count(*) as cnt
				from v_TimetableResource_lite
				where Resource_id = :Resource_id
				  and TimetableResource_begTime > dateadd('minute', -2, :StartTime)
			  	  and TimetableResource_begTime < dateadd('minute', 2, :StartTime)
				  and TimetableResource_begTime is not null
			";
			$sqlParams = [
				"Resource_id" => $data["Resource_id"],
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"])))
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
		}
		if (!(!empty($data["ignoreTTRExist"]) || empty($result[0]["cnt"]))) {
		    return $this->createError(null, "Дополнительная бирка должна отстоять не менее чем на 2 минуты от существующих. Выберите другое время или удалите бирки.");
		}
		$sql = "
			select
				TimetableResource_id as \"TimetableResource_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_TimetableResource_ins (
				Resource_id := :Resource_id,
				TimetableResource_Day := :TimetableResource_Day,
				TimetableResource_begTime := :TimetableResource_begTime,
				TimetableResource_Time := :TimetableResource_Time,
				TimetableType_id := 1,
				TimetableResource_IsDop := 1,
				pmUser_id := :pmUser_id
		    )
		";
		$sqlParams = [
			"Resource_id" => $data["Resource_id"],
			"TimetableResource_Day" => $data["Day"],
			"TimetableResource_begTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
			"TimetableResource_Time" => 0,
			"pmUser_id" => $data["pmUser_id"]
		];
		if (!empty($data["withoutRecord"])) {
			$sqlParams["TimetableResource_begTime"] = null; // доп. бирка б/з
		}
		$result = $this->db->query($sql, $sqlParams);
		if (is_object($result)) {
			$resp = $result->result("array");
			$this->commitTransaction();
			if (count($resp) > 0 && !empty($resp[0]["TimetableResource_id"])) {
				// отправка STOMP-сообщения
				$funcParams = [
					"id" => $resp[0]["TimetableResource_id"],
					"timeTable" => "TimetableResource",
					"action" => "AddTicket",
					"setDate" => date("c")
				];
				sendFerStompMessage($funcParams, "Rule");
				return [
					"TimetableResource_id" => $resp[0]["TimetableResource_id"],
					"TimetableResource_begTime" => $sqlParams["TimetableResource_begTime"],
					"Error_Msg" => ""
				];
			}
		}
		return $this->createError(null, "Дополнительная бирка не создана.");
	}

	/**
	 * Проверка времени записи перед блокировкой бирки
	 * Если добавляемое назначение имеет разницу по времени менее 15 минут бирки с каким-либо уже имеющимся в списке справа, выдавать предупреждение "Существует назначение, близкое по времени записи к создаваемому."
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkBeforeLock($data)
	{
		$sql = "
            with cte as (
				select
					TimetableResource_begTime as setTime,
					TimetableResource_Day as day
				from v_TimetableResource_lite
                where TimetableResource_id = :TimetableResource_id
            )
            select ttms.TimetableResource_id as \"TimetableResource_id\"
            from v_TimetableResource_lite ttms 
            where ttms.TimetableResource_Day = (select day from cte)
              and ttms.TimetableResource_begTime between DATEADD('minute', -14, (select setTime from cte)) 
              and DATEADD('minute', 14, (select setTime from cte))
              and ttms.Person_id = :Person_id
        ";
		$queryParams = [
			"TimetableResource_id" => $data["TimetableResource_id"],
			"Person_id" => $data["Person_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		$response = [["Error_Msg" => null, "Error_Code" => null]];
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД");
		}
		$result = $result->result("array");
		if (count($result) > 0) {
			$response[0]["Alert_Msg"] = "Существует назначение, близкое по времени записи к создаваемому";
		}
		return $response;
	}

	/**
	 * Получение информации по бирке
	 * @param $data
	 * @return array|bool
	 */
	function getTTRInfo($data)
	{
		$query = "
	        select 
	            R.Resource_id as \"Resource_id\",
                to_char(TTR.TimetableResource_begTime, 'YYYY-MM-DD HH24:MI:SS') as \"TimetableResource_abegTime\",
                to_char(TTRN.TimetableResource_begTime, 'YYYY-MM-DD HH24:MI:SS') as \"TimetableResource_nextTime\"
	        from
	        	v_TimetableResource_lite TTR
	        	inner join v_Resource R on R.Resource_id = TTR.Resource_id
	        	left join lateral (
		            select TimetableResource_begTime 
		            from v_TimetableResource_lite 
		            where Resource_id = TTR.Resource_id 
		              and TimetableResource_Day = TTR.TimetableResource_Day 
				      and TimetableResource_begTime > TTR.TimetableResource_begTime
					limit 1
	        	) as TTRN on true
	        where TTR.TimetableResource_id = :TimetableResource_id
	        limit 1
	    ";
		$queryParams = ["TimetableResource_id" => $data["TimetableResource_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Добавление расписания на ресурс
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function addTimetableResource($data)
	{
		$resp_tt = [];
		if (!empty($data["TimeTableResourceCreate"])) {
			foreach ($data["TimeTableResourceCreate"] as $one) {
				if (!isset($one["TimeTableResource_begTime"])) {
					throw new Exception("Не указана дата/время начала приёма");
				}
				if (!isset($one["TimeTableResource_Time"])) {
					throw new Exception("Не указана длительность приёма");
				}
				if (!isset($one["TimeTableType_id"])) {
					throw new Exception("Не указан тип бирки");
				}
				if (isset($one["TimeTableResource_IsDop"]) && $one["TimeTableResource_IsDop"] !== 1 && $one["TimeTableResource_IsDop"] !== 0 && $one["TimeTableResource_IsDop"] !== "1" && $one["TimeTableResource_IsDop"] !== "0") {
					throw new Exception("Неверное значение в поле TimeTableResource_IsDop");
				} else if (!isset($one["TimeTableResource_IsDop"])) {
					throw new Exception("Не указан признак дополнительной бирки");
				}
				$query = "
					select
					    TimetableResource_id as \"TimetableResource_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_timetableresource_ins(
					    resource_id := :Resource_id,
					    timetableresource_day := :TimetableResource_Day,
					    timetableresource_begtime := :TimetableResource_begTime,
					    timetableresource_time := :TimetableResource_Time,
					    timetabletype_id := :TimeTableType_id,
					    timetableresource_isdop := :TimetableResource_IsDop,
					    pmuser_id := :pmUser_id
					);				
				";
				$queryParams = [
					"Resource_id" => $data["Resource_id"],
					"TimetableResource_Day" => TimeToDay(strtotime($one["TimetableResource_begTime"])),
					"TimetableResource_begTime" => date("Y-m-d H:i:s", strtotime($one["TimetableResource_begTime"])),
					"TimetableResource_Time" => $one["TimetableResource_Time"],
					"TimeTableType_id" => $one["TimeTableType_id"],
					"TimetableResource_IsDop" => !empty($one["TimetableResource_IsDop"]) ? $one["TimetableResource_IsDop"] : null,
					"pmUser_id" => $data["pmUser_id"]
				];
				$resp = $this->queryResult($query, $queryParams);
				if (!empty($resp[0]["TimetableResource_id"])) {
					$resp_tt[] = ["TimetableResource_id" => $resp[0]["TimetableResource_id"]];
				}
				if (!empty($resp[0]["Error_Msg"])) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
			}
		}
		return $resp_tt;
	}

	/**
	 * Редактирование расписания на ресурс
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function editTimetableResource($data)
	{
		if (!empty($data["TimeTableResourceEdit"])) {
			foreach ($data["TimeTableResourceEdit"] as $one) {
				if (!isset($one["TimeTableResource_id"])) {
					throw new Exception("Не указан идентификатор бирки");
				}
				if (!empty($one["TimeTableType_id"])) {
					// смена типа бирки
					$query = "
					    select 
					        TimetableResource_id as \"TimetableResource_id\",
					        Error_Code as \"Error_Code\",
					    	Error_Message as \"Error_Msg\"
					    from p_TimetableResource_setType (
							timetabletype_id :=  :TimetableType_id,
							pmUser_id := :pmUser_id
					    )
					";
					$queryParams = [
						"TimetableResource_id" => $one["TimeTableResource_id"],
						"TimetableType_id" => $one["TimeTableType_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$tmp = $this->queryResult($query, $queryParams);
					if (empty($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (isset($tmp["Error_Msg"])) {
						throw new Exception($tmp["Error_Msg"], 500);
					}
				}
				if (isset($one["TimeTableResource_IsDop"])) {
					// проставление признака дополнительной бирки
					$query = "
						update TimetableResource
						set
							TimeTableResource_IsDop = :TimeTableResource_IsDop,
							pmUser_updID = :pmUser_id,
							TimeTableResource_updDT = getdate()
						where TimetableResource_id = :TimetableResource_id
					";
					$queryParams = [
						"TimetableResource_id" => $one["TimeTableResource_id"],
						"TimeTableResource_IsDop" => $one["TimeTableResource_IsDop"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
				}
				if (!empty($one["TimeTableResourceDelStatus"])) {
					// удаление бирки
					$funcParams = [
						"pmUser_id" => $data["pmUser_id"],
						"TimetableResource_id" => $one["TimeTableResource_id"]
					];
					$tmp = $this->execCommonSP("p_TimetableResource_del", $funcParams, "array_assoc");
					if (empty($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (isset($tmp["Error_Msg"])) {
						throw new Exception($tmp["Error_Msg"], 500);
					}
				}
			}
		}
		return [];
	}
}