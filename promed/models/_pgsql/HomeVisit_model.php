<?php
/**
 * HomeVisit - модель для вызовов врачей на дом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      20.09.2013
 *
 * @property Person_model $Person_model
 * @property MedPersonal_model $MedPersonal_model
 * @property CI_DB_driver $db
 * @property CI_Config $config
 * @property Replicator_model $Replicator_model
 * @property Numerator_model $Numerator_model
 */

class HomeVisit_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI";
	private $dateTimeForm120 = "YYYY-MM-DD HH24:MI";
	private $dateTimeFormUnix = "YYYY-MM-DD";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка вызовов на дом на дату по ЛПУ
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getHomeVisitList($data)
	{
		$params = [];
		$filter = "(1=1)";
		if (isset($data["begDate"]) && !empty($data["begDate"])) {
			$filter .= " and cast(hv.HomeVisit_setDT as date) >= cast(:begDate as date)";
			$params["begDate"] = $data["begDate"];
		}
		if (isset($data["endDate"]) && !empty($data["endDate"])) {
			$filter .= " and cast(hv.HomeVisit_setDT as date) <= cast(:endDate as date)";
			$params["endDate"] = $data["endDate"];
		}
		if (!empty($data["Lpu_id"]) && empty($data["allLpu"])) {
			$filter .= " and hv.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
			$lpuJoin = " left join v_Lpu l on hv.Lpu_id = l.Lpu_id ";
		} else {
			$lpuJoin = " inner join v_Lpu l on hv.Lpu_id = l.Lpu_id ";
		}
		if (!empty($data["Person_Surname"])) {
			$filter .= " and p.Person_SurName ilike (:Person_Surname||'%')";
			$params["Person_Surname"] = rtrim($data["Person_Surname"]);
		}
		if (!empty($data["Person_Firname"])) {
			$filter .= " and p.Person_FirName ilike (:Person_Firname||'%')";
			$params["Person_Firname"] = rtrim($data["Person_Firname"]);
		}
		if (!empty($data["Person_Secname"])) {
			$filter .= " and p.Person_SecName ilike (:Person_Secname||'%')";
			$params["Person_Secname"] = rtrim($data["Person_Secname"]);
		}
		if (!empty($data["Person_BirthDay"])) {
			$filter .= " and p.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if (!empty($data["HomeVisitStatus_id"])) {
			if ($data["HomeVisitStatus_id"] == -1) {
				$filter .= " and hv.HomeVisitStatus_id = 1 and hv.CmpCallCard_id is not null";
			} elseif ($data["HomeVisitStatus_id"] == -2) {
				$filter .= " and hv.HomeVisitStatus_id is null";
			} elseif ($data["HomeVisitStatus_id"] == 1) {
				$filter .= " and hv.HomeVisitStatus_id = 1 and hv.CmpCallCard_id is null";
			} else {
				$filter .= " and hv.HomeVisitStatus_id = :HomeVisitStatus_id";
				$params["HomeVisitStatus_id"] = $data["HomeVisitStatus_id"];
			}
		}
		if (!empty($data["HomeVisitCallType_id"])) {
			$filter .= " and hv.HomeVisitCallType_id = :HomeVisitCallType_id";
			$params["HomeVisitCallType_id"] = $data["HomeVisitCallType_id"];
		}
		if (!empty($data["HomeVisit_setTimeFrom"])) {
			$filter .= " and cast(hv.HomeVisit_setDT as time) >= cast(:HomeVisit_setTimeFrom as time)";
			$params["HomeVisit_setTimeFrom"] = $data["HomeVisit_setTimeFrom"];
		}
		if (!empty($data["HomeVisit_setTimeTo"])) {
			$filter .= " and cast(hv.HomeVisit_setDT as time) <= cast(:HomeVisit_setTimeTo as time)";
			$params["HomeVisit_setTimeTo"] = $data["HomeVisit_setTimeTo"];
		}
		if (!empty($data["MedStaffFact_id"])) {
			$tmpFilterMedPersonal_id = "";
			if (!empty($data["MedPersonal_id"]) && $data["MedPersonal_id"] != -1) {
				$tmpFilterMedPersonal_id = " OR (msf.MedStaffFact_id IS NULL and mp.MedPersonal_id = :MedPersonal_id)";
				$params["MedPersonal_id"] = $data["MedPersonal_id"];
			}
			$filter .= " and ( msf.MedStaffFact_id = :MedStaffFact_id $tmpFilterMedPersonal_id)";
			$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		} else if (!empty($data["MedPersonal_id"])) {
			if ($data["MedPersonal_id"] == -1) {
				$filter .= " and mp.MedPersonal_id is null";
			} else {
				$filter .= " and mp.MedPersonal_id = :MedPersonal_id";
				$params["MedPersonal_id"] = $data["MedPersonal_id"];
			}
		}
		//Подразделение - врач из вызова на дом должен быть с выбранного участка
		if (!empty($data["LpuRegion_id"]) || !empty($data["LpuBuilding_id"])) {
			$exists_filter = "";
			if (!empty($data["LpuRegion_id"])) {
				$params["LpuRegion_id"] = $data["LpuRegion_id"];
				$filter .= " and hv.LpuRegion_cid = :LpuRegion_id";
				$exists_filter .= " and t3.LpuSection_id = lr.LpuSection_id";
			}
			if (!empty($data["LpuBuilding_id"])) {
				$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
				$exists_filter .= " and t3.LpuBuilding_id = :LpuBuilding_id";
			}
			$main_exists_filter = "
				exists (
					select t1.MedStaffRegion_id
					from
						v_MedStaffRegion t1
						inner join v_LpuRegion t2 on t2.LpuRegion_id = t1.LpuRegion_id
						inner join v_LpuSection t3 on t3.LpuSection_id = t2.LpuSection_id
						left join v_MedStaffFact t4 on t4.MedStaffFact_id = t1.MedStaffFact_id
					where
						coalesce(t1.MedPersonal_id, t4.MedPersonal_id) = hv.MedPersonal_id and
						(t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate > tzgetdate())
						{$exists_filter}
					limit 1
				)
			";
			if (!empty($data["LpuBuilding_id"])) {
				$filter .= " and {$main_exists_filter}";
			} else {
				$filter .= " and (hv.MedPersonal_id is null or lr.LpuSection_id is null or ({$main_exists_filter}))";
			}
		}
		if (!empty($data["LpuRegion_cid"])) {
			$params["LpuRegion_cid"] = $data["LpuRegion_cid"];
			$filter .= " and hv.LpuRegion_cid = :LpuRegion_cid ";
		}
		if (!empty($data["CallProfType_id"])) {
			$params["CallProfType_id"] = $data["CallProfType_id"];
			$filter .= " and hv.CallProfType_id = :CallProfType_id ";
		}
		$selectString = "
			msf.MedStaffFact_id as \"MedStaffFact_id\",
			hv.HomeVisit_id as \"HomeVisit_id\",
			hv.Person_id as \"Person_id\",
			hv.HomeVisit_Num as \"HomeVisit_Num\",
			to_char(hv.HomeVisit_setDT, '{$this->dateTimeForm104}') as \"HomeVisit_setDate\",
			p.PersonEvn_id as \"PersonEvn_id\",
			p.Server_id as \"Server_id\",
			p.Person_Surname as \"Person_Surname\",
			p.Person_Firname as \"Person_Firname\",
			p.Person_Secname as \"Person_Secname\",
			to_char(p.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_Birthday\",
			rtrim(hv.Address_Address) as \"Address_Address\",
			mp.Person_FIO as \"MedPersonal_FIO\",
			coalesce(lr.LpuRegion_id, lrc.LpuRegion_id) as \"LpuRegion_id\",
			coalesce(lrc.LpuRegion_Name, '')||' '||coalesce(lrc.LpuRegionType_Name, '') as \"LpuRegion_Name\",
			lb.LpuBuilding_id as \"LpuBuilding_id\",
			lb.LpuBuilding_Name as \"LpuBuilding_Name\",
			case
				when hv.HomeVisitStatus_id is null then '0. Требует подтверждения'
				when hv.HomeVisitStatus_id = 1 and hvsrc.HomeVisitSource_Code = 10  and hv.CmpCallCard_id is not null then '1.	Актив из СМП'
				else cast((1 + hvs.HomeVisitStatus_Code::integer) as varchar)||'. '||hvs.HomeVisitStatus_Name
			end as \"HomeVisitStatus_Nameg\",
			case
				when hv.HomeVisitStatus_id is null then 'Требует подтверждения'
				when hv.HomeVisitStatus_id = 1 and hvsrc.HomeVisitSource_Code = 10  and hv.CmpCallCard_id is not null then 'Актив из СМП'
				else hvs.HomeVisitStatus_Name
			end as \"HomeVisitStatus_Name\",
			age2(p.Person_Birthday, getdate()) as \"Person_Age\",
			hv.HomeVisit_Phone as \"HomeVisit_Phone\",
			hv.HomeVisitStatus_id as \"HomeVisitStatus_id\",
			hv.HomeVisitCallType_id as \"HomeVisitCallType_id\",
			hvwc.HomeVisitWhoCall_Name as \"HomeVisitWhoCall_Name\",
			hvct.HomeVisitCallType_Name as \"HomeVisitCallType_Name\",
			HomeVisit_Symptoms as \"HomeVisit_Symptoms\",
			HomeVisit_Comment as \"HomeVisit_Comment\",
			HomeVisit_LpuComment as \"HomeVisit_LpuComment\",
			coalesce(hv.CallProfType_id, 1) as \"CallProfType_id\",
			coalesce(cpt.CallProfType_Name, 'Терапевтический/педиатрический') as \"CallProfType_Name\",
			to_char(hv.HomeVisit_setDT, '{$this->dateTimeForm108}') as \"HomeVisit_setTime\",
			hv.Lpu_id as \"Lpu_id\",
			hv.HomeVisitSource_id as \"HomeVisitSource_id\",
			l.Lpu_Nick as \"Lpu_Nick\",
			ccc.CmpCallCard_id as \"CmpCallCard_id\",
			case when hvsrc.HomeVisitSource_Code <> 5 then ccc.CmpCallCard_Ngod end as \"CmpCallCard_Ngod\",
			case when ccc.CmpCallCard_id is not null and hvsrc.HomeVisitSource_Code <> 5
			    then to_char(htsh.HomeVisitStatusHist_setDT, '{$this->dateTimeForm104}') 
			    else null
			end as \"HomeVisitStatusHist_setDT\",
			case when lat.Lpu_Nick is null
			    then ''
			    else coalesce(lat.Lpu_Nick, '')||'/'||coalesce(pc.LpuRegion_Name, '')||coalesce(pc.LpuRegionType_Name, '')
			end as \"LpuRegionAttach\",
			rtrim(coalesce(ltrim(rtrim(msf.MedPersonal_TabCode)||' '), '')||coalesce(ltrim(rtrim(mp.Person_FIO)||' '), '')||coalesce(ltrim(rtrim('['||ltrim(rtrim(msfls.LpuSection_Code))||'. '||ltrim(rtrim(msfls.LpuSection_Name))||']')||' '), '')||coalesce(ltrim(rtrim(post.name)), '')) as \"MedStaff_Comp\",
			hv.HomeVisit_isQuarantine as \"HomeVisit_isQuarantine\"
		";
		$fromString = "
			v_HomeVisit hv
			{$lpuJoin}
			left join v_PersonState p on hv.Person_id = p.Person_id
			left join lateral (
				select *
				from v_MedStaffFact
				where MedStaffFact_id = hv.MedStaffFact_id
			    limit 1
			) as msf on true
			left join persis.Post post on post.id = msf.Post_id
			left join v_LpuSection msfls on msfls.LpuSection_id = msf.LpuSection_id
			left join lateral (
				select MedPersonal_id, Person_FIO
				from v_MedPersonal
				where MedPersonal_id = coalesce(msf.MedPersonal_id, hv.MedPersonal_id)
				  and Lpu_id = hv.Lpu_id
			    limit 1
			) as mp on true
			left join lateral (
				select *
				from v_PersonCard_all
				where Person_id = hv.Person_id
				  and LpuAttachType_id = 1 
				  and hv.HomeVisit_insdt >= PersonCard_begDate
				  and (hv.HomeVisit_insdt <= PersonCard_endDate or PersonCard_endDate IS NULL)
			    limit 1
			) as pc on true
			left join v_Lpu lat on lat.Lpu_id = pc.Lpu_id
			left join v_LpuRegion lr on lr.LpuRegion_id = hv.LpuRegion_id and lr.Lpu_id = l.Lpu_id
			left join v_LpuRegion lrc on lrc.LpuRegion_id = hv.LpuRegion_cid
			left join v_LpuSection ls on ls.LpuSection_id = lr.LpuSection_id
			left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
			left join v_HomeVisitStatus hvs on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
			left join v_HomeVisitWhoCall hvwc on hv.HomeVisitWhoCall_id = hvwc.HomeVisitWhoCall_id
			left join v_HomeVisitCallType hvct on hv.HomeVisitCallType_id = hvct.HomeVisitCallType_id
			left join v_CmpCallCard ccc on ccc.CmpCallCard_id = hv.CmpCallCard_id
			left join v_CallProfType cpt on cpt.CallProfType_id = hv.CallProfType_id
			left join v_HomeVisitSource hvsrc on hvsrc.HomeVisitSource_id = hv.HomeVisitSource_id
			left join lateral (
				select htsh.HomeVisitStatusHist_setDT
				from v_HomeVisitStatusHist htsh
				where htsh.HomeVisit_id = hv.HomeVisit_id
			      and HomeVisitStatus_id = 1
			    limit 1
			) as htsh on true
		";
		$orderByString = "
			hv.HomeVisitStatus_id,
			hv.HomeVisit_id
		";
		$sql = "
			select
			-- select
			{$selectString}
			-- end select
			from
			-- from
			{$fromString}
			-- end from
			where
			-- where
			{$filter}
			-- end where
			order by
			-- order by
			{$orderByString}
			-- end order by
		";
		return $this->getPagingResponse($sql, $params, $data["start"], $data["limit"], true);
	}

	/**
	 * Одобрить вызов на дом
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function confirmHomeVisit($data)
	{
		$info = $this->getHomeVisitEditWindow($data);
		$data["MedPersonal_id"] = $info[0]["MedPersonal_id"];
		$data["MedStaffFact_id"] = $info[0]["MedStaffFact_id"];
		$data["HomeVisit_LpuComment"] = $info[0]["HomeVisit_LpuComment"];
		$queryParams = getArrayElements(
				$data, [
					"HomeVisit_id", "MedPersonal_id", "MedStaffFact_id", "HomeVisit_LpuComment", "pmUser_id"
				]
			) + [
				"HomeVisitStatus_id" => 3 // статус Одобрено
			];
		if (!key_exists("HomeVisitSource_id", $data)) {
			// если не передан источник, то берем существующий
			$sql = "
				select HomeVisitSource_id as \"HomeVisitSource_id\"
				from HomeVisit
				where HomeVisit_id = :HomeVisit_id
				limit 1
			";
			$sqlParams = [
				"HomeVisit_id" => $data["HomeVisit_id"]
			];
			if ($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery($sql, $sqlParams)) {
				$queryParams["HomeVisitSource_id"] = $homeVisitSourceId;
			}
		} else {
			$queryParams["HomeVisitSource_id"] = $data["HomeVisitSource_id"];
		}
		if (($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams))) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function setStatusNew($data)
	{
		$info = $this->getHomeVisitEditWindow($data);
		$data["MedPersonal_id"] = null;
		$data["MedStaffFact_id"] = null;
		$data["HomeVisit_LpuComment"] = $info[0]["HomeVisit_LpuComment"];
		$queryParams = getArrayElements(
				$data, [
					"HomeVisit_id", "MedPersonal_id", "MedStaffFact_id", "HomeVisit_LpuComment", "pmUser_id"
				]
			) + [
				"HomeVisitStatus_id" => 1 // статус Одобрено
			];
		if (!key_exists("HomeVisitSource_id", $data)) {
			// если не передан источник, то берем существующий
			$sql = "
				select HomeVisitSource_id as \"HomeVisitSource_id\"
				from HomeVisit
				where HomeVisit_id = :HomeVisit_id
				limit 1
			";
			$sqlParams = [
				"HomeVisit_id" => $data["HomeVisit_id"]
			];
			if ($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery($sql, $sqlParams)) {
				$queryParams["HomeVisitSource_id"] = $homeVisitSourceId;
			}
		} else {
			$queryParams["HomeVisitSource_id"] = $data["HomeVisitSource_id"];
		}
		if (($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams))) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function takeMP($data)
	{
		$queryParams = getArrayElements(
				$data, [
					"HomeVisit_id", "MedPersonal_id", "MedStaffFact_id", "HomeVisit_LpuComment", "pmUser_id"
				]
			) + [
				"HomeVisitStatus_id" => 6 // статус назначен врач
			];
		if (!key_exists('HomeVisitSource_id', $data)) {
			//если не передан источник, то берем существующий
			$sql = "
				select HomeVisitSource_id as \"HomeVisitSource_id\"
				from HomeVisit
				where HomeVisit_id = :HomeVisit_id
				limit 1
			";
			$sqlParams = [
				"HomeVisit_id" => $data["HomeVisit_id"]
			];
			if ($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery($sql, $sqlParams)) {
				$queryParams["HomeVisitSource_id"] = $homeVisitSourceId;
			}
		} else {
			$queryParams["HomeVisitSource_id"] = $data["HomeVisitSource_id"];
		}
		if (($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams))) {
			$this->saveHomeVisitStatusHist($queryParams);
			// Отправляем пуш уведомление
			$homevisit = $this->getHomeVisitForAPI($data);
			if (!empty($homevisit[0])) {
				$homevisit = $homevisit[0];
				$this->load->model("Person_model");
				$this->load->model("MedPersonal_model");

				$person = $this->Person_model->getPersonCombo($homevisit);
				$ms = $this->MedPersonal_model->getMedPersonInfo(["MedStaffFact_id" => $homevisit["MedStaffFact_id"]]);
				if (!empty($person[0]["Person_Fio"]) && !empty($ms[0]["MedPersonal_FIO"])) {
					$this->load->helper("Notify");
					sendPushNotification([
						"Person_id" => $homevisit["Person_id"], // персона которая заходит
						"message" => "Пациенту " . $person[0]["Person_Fio"] . " назначен врач " . $ms[0]["MedPersonal_FIO"] . " по адресу " . $homevisit["Address_Address"],
						"PushNoticeType_id" => 2,
						"action" => "call"
					]);
				}
			}
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * Отказать в вызове на дом
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function denyHomeVisit($data)
	{
		$queryParams = getArrayElements(
				$data, [
					"HomeVisit_id", "HomeVisit_LpuComment", "pmUser_id"
				]
			) + [
				"HomeVisitStatus_id" => 2 // статус Отказ
			];
		if (!key_exists("HomeVisitSource_id", $data)) {
			// если не передан источник, то берем существующий
			$sql = "
				select HomeVisitSource_id as \"HomeVisitSource_id\"
				from HomeVisit
				where HomeVisit_id = :HomeVisit_id
				limit 1
			";
			$sqlParams = [
				"HomeVisit_id" => $data["HomeVisit_id"]
			];
			if ($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery($sql, $sqlParams)) {
				$queryParams["HomeVisitSource_id"] = $homeVisitSourceId;
			}
		} else {
			$queryParams["HomeVisitSource_id"] = $data["HomeVisitSource_id"];
		}
		if (($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams))) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * Отменить вызов на дом
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function cancelHomeVisit($data)
	{
		$queryParams = [
			"HomeVisit_id" => $data["HomeVisit_id"],
			"HomeVisit_LpuComment" => !empty($data["HomeVisit_LpuComment"]) ? $data["HomeVisit_LpuComment"] : null,
			"HomeVisitStatus_id" => 5,
			"pmUser_id" => $data["pmUser_id"]
		];
		if (!key_exists('HomeVisitSource_id', $data)) {
			//если не передан источник, то берем существующий
			$sql = "
				select HomeVisitSource_id as \"HomeVisitSource_id\"
				from HomeVisit
				where HomeVisit_id = :HomeVisit_id
				limit 1
			";
			$sqlParams = [
				"HomeVisit_id" => $data["HomeVisit_id"]
			];
			if ($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery($sql, $sqlParams)) {
				$queryParams["HomeVisitSource_id"] = $homeVisitSourceId;
			}
		} else {
			$queryParams["HomeVisitSource_id"] = $data["HomeVisitSource_id"];
		}
		if (($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams))) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * Получение улицы
	 * @param $guid
	 * @return array|bool
	 */
	function getKLStreetByGUID($guid)
	{
		$params = ["KLStreet_AOGUID" => $guid];
		$query = "
			select
				KLStreet_id as \"KLStreet_id\",
				KLArea_id as \"KLArea_id\",
				KLSocr_id as \"KLSocr_id\",
				KLStreet_Name as \"KLStreet_Name\",
				KLStreet_FullName as \"KLStreet_FullName\",
				KLAdr_Code as \"KLAdr_Code\",
				KLAdr_Index as \"KLAdr_Index\",
				KLAdr_Gninmb as \"KLAdr_Gninmb\",
				KLAdr_Uno as \"KLAdr_Uno\",
				KLAdr_Ocatd as \"KLAdr_Ocatd\",
				KLAdr_Actual as \"KLAdr_Actual\",
				KLStreet_oid as \"KLStreet_oid\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				KLStreet_insDT as \"KLStreet_insDT\",
				KLStreet_updDT as \"KLStreet_updDT\",
				Server_id as \"Server_id\",
				KLStreet_LocalName as \"KLStreet_LocalName\",
				KLStreet_FullLocalName as \"KLStreet_FullLocalName\",
				KLStreet_AOGUID as \"KLStreet_AOGUID\",
				KLStreet_AOID as \"KLStreet_AOID\",
				KLStreet_PGUID as \"KLStreet_PGUID\",
				KLStreet_OKTMO as \"KLStreet_OKTMO\",
				KLStreet_begDT as \"KLStreet_begDT\",
				KLStreet_endDT as \"KLStreet_endDT\"
			from v_KLStreet
			where KLStreet_AOGUID = :KLStreet_AOGUID
			  and KLAdr_Actual = 0
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Получение улицы
	 * @param $klstreet_id
	 * @return array|bool
	 */
	function getGUIDByKLStreet($klstreet_id)
	{
		$params = ["KLStreet_id" => $klstreet_id];
		$query = "
			select KLStreet_AOGUID as \"KLStreet_AOGUID\"
			from v_KLStreet
			where KLStreet_id = :KLStreet_id
			  and KLAdr_Actual = 0
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Поиск участков по адресу
	 * @param $address
	 * @return array|bool
	 */
	function searchRegionsByAddress($address)
	{
		/**
		 * Определение входит ли номер дома в диапазон (3 функции, вся эта жесть взята без изменений из старой версии сайта "к врачу")
		 * Главная функция HouseMatchRange
		 * @param $arr
		 * @return array
		 */
		function getHouseArray($arr)
		{
			$arr = trim(mb_strtoupper($arr));
			$matches = [];
			$matches2 = [];
			$pregMatchValue1 = "/^([Ч|Н])\((\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)\)$/ui";
			$pregMatchValue2 = "/^([\s]?)(\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)$/ui";
			$pregMatchValue3 = "/^(\d+[а-яА-Я]?[\/]?\d{0,2}+[а-яА-Я]?)$/ui";
			$pregMatchValue4 = "/^(\d+)/i";
			if (preg_match($pregMatchValue1, $arr, $matches)) {
				// Четный или нечетный
				$matches[count($matches)] = 1;
				return $matches;
			} elseif (preg_match($pregMatchValue2, $arr, $matches)) {
				// Обычный диапазон
				$matches[count($matches)] = 2;
				return $matches;
			} elseif (preg_match($pregMatchValue3, $arr, $matches)) {
				//print $arr." ";
				if (preg_match($pregMatchValue4, $matches[1], $matches2)) {
					$matches[count($matches)] = $matches2[1];
				} else {
					$matches[count($matches)] = '';
				}
				$matches[count($matches)] = 3;
				return $matches;
			}
			return [];
		}

		/**
		 * Возвращает признак вхождения в диапазон домов
		 *
		 * @param $h_arr
		 * @param $houses
		 * @return bool|string
		 */
		function HouseExist($h_arr, $houses)
		{
			// Сначала разбираем h_arr и определяем:
			// 1. Обычный диапазон
			// 2. Четный диапазон
			// 3. Нечетный диапазон
			// 4. Перечисление
			// Разбиваем на номера домов и диапазоны с которым будем проверять
			$pregSplitValue = "[,|;]";
			$hs_arr = preg_split($pregSplitValue, $houses, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($h_arr as $row_arr) {
				$ch = getHouseArray($row_arr); // сохраняемый
				if (count($ch) > 0) {
					foreach ($hs_arr as $rs_arr) {
						$chn = getHouseArray($rs_arr); // выбранный
						if (count($chn) > 0) {
							// Проверка на правильность указания диапазона
							if ((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($ch[2] > $ch[4])) {
								return false;
							}
							if ((($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == "Ч") && ($chn[1] == "Ч")) || // сверяем четный с четным
								(($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == "Н") && ($chn[1] == "Н")) || // сверяем нечетный с нечетным
								((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 2))) {        // или любой диапазон с обычным
								if (($ch[2] <= $chn[4]) && ($ch[4] >= $chn[2])) {
									return true; // Перечесение (С) и (В) диапазонов
								}
							}
							if ((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 3)) { // Любой диапазон с домом
								if ((($ch[1] == "Ч") && ($chn[2] % 2 == 0)) || // если четный
									(($ch[1] == "Н") && ($chn[2] % 2 <> 0)) || // нечетный
									($ch[count($ch) - 1] == 2)) { // обычный
									if (($ch[2] <= $chn[2]) && ($ch[4] >= $chn[2])) {
										return true; // Перечесение диапазона с конкретным домом
									}
								}
							}
							if ((($chn[count($chn) - 1] == 1) || ($chn[count($chn) - 1] == 2)) && ($ch[count($ch) - 1] == 3)) { // Любой дом с диапазоном
								if ((($chn[1] == "Ч") && ($ch[2] % 2 == 0)) || // если четный
									(($chn[1] == "Н") && ($ch[2] % 2 <> 0)) || // нечетный
									($chn[count($chn) - 1] == 2)) { // обычный
									if (($chn[2] <= $ch[2]) && ($chn[4] >= $ch[2])) {
										return true; // Перечесение дома с каким-либо диапазоном
									}
								}
							}
							if (($ch[count($ch) - 1] == 3) && ($chn[count($chn) - 1] == 3)) { // Дом с домом
								if (strtolower($ch[0]) == strtolower($chn[0])) {
									return true; // Перечесение дома с домом
								}
							}
						}
					}
				} else {
					return false; // Перечесение дома с домом
				}
			}
			return "";
		}

		/**
		 * Проверка попадания номера дома в список домов
		 *
		 * @param $sHouse
		 * @param $sRange
		 * @return bool|string
		 */
		function HouseMatchRange($sHouse, $sRange)
		{
			if ($sRange == "") {
				return true;
			}
			return HouseExist([$sHouse], $sRange);
		}

		$subSelectValue = "
			cast(msf2.MedStaffFact_id as varchar)||'='||msf2.Person_FIO||';'||cast(msf2.LpuUnit_id as varchar)||';'||cast(msf2.Lpu_id as varchar)||';'||cast(ls2.LpuSectionProfile_id as varchar)||'|' as 'data()'
		";
		$sql = "
			select distinct
				l.Lpu_id as \"Lpu_id\",
				l.Lpu_Phone as \"Lpu_Phone\",
				lr.LpuRegion_id as \"LpuRegion_id\",
                lr.LpuRegion_Name as \"LpuRegion_Name\",
                lrt.LpuRegionType_Name as \"LpuRegionType_Name\",
				lrt.LpuRegionType_SysNick as \"LpuRegionType_SysNick\",
                l.Lpu_Name as \"Lpu_Name\",
                l.Lpu_Nick as \"Lpu_Nick\",
                MedStaffFact_List as \"MedStaffFact_List\",
                lrs.LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
			from LpuRegionStreet lrs
				inner join v_KLStreet kls on (kls.KLStreet_id = lrs.KLStreet_id and kls.KLStreet_AOGUID = :KLStreet_AOGUID)
				left join v_LpuRegion lr on lr.LpuRegion_id = lrs.LpuRegion_id
				left join v_LpuRegionType lrt on lr.LpuRegionType_id = lrt.LpuRegionType_id
				left join lateral ( 
					select ERTerr_id
					from ERTerr Terr
					where
					      ((lrs.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
					      ((lrs.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
					      ((lrs.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
					      ((lrs.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
					      ((lrs.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
					limit 1
				) as Terr on true
				inner join lateral (
				    select 
						(
						    select {$subSelectValue}
							from v_MedStaffRegion msr2 
								inner join v_MedStaffFact msf2 on msr2.MedStaffFact_id = msf2.MedStaffFact_id and coalesce(msr2.MedStaffRegion_endDate, '2030-01-01') > getdate() 
								inner join v_LpuSection ls2 on ls2.LpuSection_id = msf2.LpuSection_id
								inner join v_LpuSectionProfile lsp2 on lsp2.LpuSectionProfile_id = ls2.LpuSectionProfile_id and lsp2.LpuSectionProfile_IsArea = 2
								inner join v_LpuUnit lu2 on ls2.LpuUnit_id = lu2.LpuUnit_id and coalesce(lu2.LpuUnit_IsEnabled, 1) = 2
							where msr2.LpuRegion_id = msr.LpuRegion_id
							  and msr.Lpu_id = msr2.Lpu_id
							order by msf2.Person_FIO
						) as MedStaffFact_List,
						msf.LpuUnit_id,
						ls.LpuSectionProfile_id
					from v_MedStaffRegion msr
					inner join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id and coalesce(msr.MedStaffRegion_endDate, '2030-01-01') > getdate()
					inner join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id and LpuSectionProfile_IsArea = 2
					where msf.Lpu_id = lr.Lpu_id
					  and lr.LpuRegion_id = msr.LpuRegion_id
				) as msf on true
				left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_Lpu l on lu.Lpu_id = l.Lpu_id
				left join LpuSectionProfile lsp on msf.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			where lu.LpuUnitType_id = 2
              and l.Lpu_id = lr.Lpu_id
			  and coalesce(l.Lpu_IsTest, 1) = 1
			  and (l.Lpu_endDate is null or l.Lpu_endDate >= tzgetdate())
			order by l.Lpu_id, LpuRegion_Name
		";
		$sqlParams = ["KLStreet_AOGUID" => $address->KLStreet_AOGUID];
		$result = $this->db->query($sql, $sqlParams);
		$result = $result->result("object");
		if (count($result) == 0) {
			return false;
		}
		$result_grouped = [];
		// Дополнительно группируем для случая если на участке несколько раз заведена одна улица
		foreach ($result as $data) {
			if (!(count($result_grouped) > 0 && $result_grouped[count($result_grouped) - 1]->MedStaffFact_List == $data->MedStaffFact_List && $result_grouped[count($result_grouped) - 1]->LpuRegion_id == $data->LpuRegion_id)) {
				$result_grouped[] = $data;
			}
		}
		$res = [];
		foreach ($result_grouped as $data) {
			if (empty($address->Address_House) || HouseMatchRange($address->Address_House, trim($data->LpuRegionStreet_HouseSet))) {
				if (!isset($hospital) || $hospital->Lpu_id != $data->Lpu_id) {
					$hospital = new StdClass();
					$hospital->Lpu_id = $data->Lpu_id;
					$hospital->Lpu_Name = $data->Lpu_Name;

					$hospital->regions = [];
					$res[] = $hospital;
				}
				$data->MedStaffFact_List = preg_replace('/[|]+$/', '', trim($data->MedStaffFact_List));
				$data->doctors = [];
				if (!empty($data->MedStaffFact_List)) {
					$doctors = explode('|', $data->MedStaffFact_List);
					foreach ($doctors as $doctor) {
						list($k, $v) = explode('=', $doctor);
						$data->doctors[$k] = $v;
					}
				}
				$hospital->regions[] = $data;
			}
		}
		return $res;
	}

	/**
	 * Получение доступности услуги по времени работы
	 * @param $lpu_id
	 * @return bool
	 */
	function getAllowTimeHomeVisit($lpu_id)
	{
		$sql = "select getAllowHomeVisitDay(:Lpu_id) as allow";
		$sqlParams = ["Lpu_id" => $lpu_id];
		$result = $this->queryResult($sql, $sqlParams);
		if (!empty($result[0]["allow"]) && $result[0]["allow"] == "1") {
			return true;
		}
		return false;
	}

	/**
	 * Получение времени работы
	 * @param $lpu_id
	 * @return bool
	 */
	function getHomeVisitDayWorkTime($lpu_id)
	{
		$sql = "SELECT getHomeVisitDayWorkTime(:Lpu_id) as datebetween";
		$sqlParams = ["Lpu_id" => $lpu_id];
		$result = $this->queryResult($sql, $sqlParams);
		if (!empty($result[0]["datebetween"])) {
			$result[0]["datebetween"];
		}
		return false;
	}

	/**
	 * Завершить обслуживание вызова на дом
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function completeHomeVisit($data)
	{
		$queryParams = getArrayElements($data, ["HomeVisit_id", "MedPersonal_id", "MedStaffFact_id", "HomeVisit_LpuComment", "pmUser_id"]) + [
				"HomeVisitStatus_id" => 4 // статус Обслужено
			];

		if (!key_exists("HomeVisitSource_id", $data)) {
			// если не передан источник, то берем существующий
			$sql = "
				select HomeVisitSource_id as \"HomeVisitSource_id\"
				from HomeVisit
				where HomeVisit_id = :HomeVisit_id
				limit 1
			";
			$sqlParams = [
				"HomeVisit_id" => $data["HomeVisit_id"]
			];
			if ($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery($sql, $sqlParams)) {
				$queryParams["HomeVisitSource_id"] = $homeVisitSourceId;
			}
		} else {
			$queryParams["HomeVisitSource_id"] = $data["HomeVisitSource_id"];
		}
		if (($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams))) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * Получение атрибутов вызова на дом. Метод для API.
	 * @param $data
	 * @return array|false
	 */
	function getHomeVisitForAPI($data)
	{
		$sql = "
			select
				hv.Person_id as \"Person_id\",
				hv.CallProfType_id as \"CallProfType_id\",
				hv.Address_Address as \"Address_Address\",
				hv.HomeVisitCallType_id as \"HomeVisitCallType_id\",
				to_char(hv.HomeVisit_setDT, '{$this->dateTimeForm120}') as \"HomeVisit_setDT\",
				hv.HomeVisit_Num as \"HomeVisit_Num\",
				hv.MedStaffFact_id as \"MedStaffFact_id\",
				hv.HomeVisit_Phone as \"HomeVisit_Phone\",
				hv.HomeVisitWhoCall_id as \"HomeVisitWhoCall_id\",
				hv.HomeVisit_Symptoms as \"HomeVisit_Symptoms\",
				hv.HomeVisit_Comment as \"HomeVisit_Comment\",
				hv.HomeVisitStatus_id as \"HomeVisitStatus_id\",
				hv.HomeVisit_LpuComment as \"HomeVisit_LpuComment\",
				l.Lpu_Name as \"Lpu_Name\",
				l.PAddress_Address as \"PAddress_Address\",
				o.Org_Phone as \"Org_Phone\",
				msf.Person_Fio as \"Person_Fio\",
				mso.MedSpecOms_Name as \"MedSpecOms_Name\"
			from
				v_HomeVisit hv
				left join v_Lpu l on l.Lpu_id = hv.Lpu_id
				left join v_Org o on o.Org_id = l.Org_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = hv.MedStaffFact_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
			where hv.HomeVisit_id = :HomeVisit_id
		";
		$sqlParams = ["HomeVisit_id" => $data["HomeVisit_id"]];
		$resp = $this->queryResult($sql, $sqlParams);
		return $resp;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getHomeVisitEditWindow($data)
	{
		$sql = "
			select
				hv.HomeVisit_id as \"HomeVisit_id\",
				hv.HomeVisit_Num as \"HomeVisit_Num\",
				hv.Person_id as \"Person_id\",
				hv.CallProfType_id as \"CallProfType_id\",
				hv.HomeVisitSource_id as \"HomeVisitSource_id\",
				p.PersonEvn_id as \"PersonEvn_id\",
				p.Server_id as \"Server_id\",
				hv.Lpu_id as \"Lpu_id\",
				hv.KLRgn_id as \"KLRgn_id\",
				hv.KLSubRgn_id as \"KLSubRgn_id\",
				hv.KLCity_id as \"KLCity_id\",
				hv.KLTown_id as \"KLTown_id\",
				hv.KLStreet_id as \"KLStreet_id\",
				hv.Address_House as \"Address_House\",
				hv.Address_Corpus as \"Address_Corpus\",
				hv.Address_Flat as \"Address_Flat\",
				rtrim(hv.Address_Address) as \"Address_Address\",
				mp.MedPersonal_id as \"MedPersonal_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				hv.LpuRegion_cid as \"LpuRegion_cid\",
				hv.LpuRegion_id as \"LpuRegion_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				hvs.HomeVisitStatus_Code||'. '||hvs.HomeVisitStatus_Name as \"HomeVisitStatus_Name\",
				age2(p.Person_Birthday, getdate()) as \"Person_Age\",
				l.Lpu_Nick||coalesce(', участок: '||lr.LpuRegion_Name, '') as \"Person_Attach\",
				hv.HomeVisit_Phone as \"HomeVisit_Phone\",
				hv.HomeVisitStatus_id as \"HomeVisitStatus_id\",
				hv.HomeVisitWhoCall_id as \"HomeVisitWhoCall_id\",
				hv.HomeVisitCallType_id as \"HomeVisitCallType_id\",
				hv.CmpCallCard_id as \"CmpCallCard_id\",
				to_char(hv.HomeVisit_setDT, '{$this->dateTimeForm104}') as \"HomeVisit_setDate\",
				to_char(hv.HomeVisit_setDT, '{$this->dateTimeForm108}') as \"HomeVisit_setTime\",
				HomeVisit_Symptoms as \"HomeVisit_Symptoms\",
				HomeVisit_Comment as \"HomeVisit_Comment\",
				HomeVisit_LpuComment as \"HomeVisit_LpuComment\",
				hv.HomeVisit_isQuarantine as \"HomeVisit_isQuarantine\"
			from
			    v_HomeVisit hv
				left join v_PersonState p on hv.Person_id = p.Person_id
				left join v_MedStaffFact msf on hv.MedStaffFact_id = msf.MedStaffFact_id
				left join lateral (
					select MedPersonal_id, Person_FIO
					from v_MedPersonal
					where MedPersonal_id = coalesce(hv.MedPersonal_id, msf.MedPersonal_id)
				  	  and Lpu_id = hv.Lpu_id
			    	limit 1
				) as mp on true
				left join v_PersonCard pc on p.Person_id = pc.Person_id and pc.LpuAttachType_id = 1
				left join v_Lpu l on l.Lpu_id = pc.Lpu_id
				left join v_LpuRegion lr on hv.LpuRegion_id = lr.LpuRegion_id
				left join v_HomeVisitStatus hvs on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
				left join v_HomeVisitWhoCall hvwc on hv.HomeVisitWhoCall_id = hvwc.HomeVisitWhoCall_id
			where HomeVisit_id =  :HomeVisit_id
			limit 1
		";
		$sqlParams = ["HomeVisit_id" => $data["HomeVisit_id"]];
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение информации о вызове на дом
	 * @param $HomeVisit_id
	 * @return bool
	 */
	function getHomeVisitInfo($HomeVisit_id)
	{
		$sql = "
			select
				hv.HomeVisit_id as \"HomeVisit_id\",
				hv.Person_id as \"Person_id\",
				p.PersonEvn_id as \"PersonEvn_id\",
				p.Server_id as \"Server_id\",
				p.Person_Surname as \"Person_Surname\",
				p.Person_Firname as \"Person_Firname\",
				p.Person_Secname as \"Person_Secname\",
				p.Person_Surname||' '||p.Person_Firname||coalesce(' '||p.Person_Secname, '') as \"Person_FIO\",
				to_char(p.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_Birthday\",
				rtrim(hv.Address_Address) as \"Address_Address\",
				coalesce(mp.Person_FIO, '') as \"MedPersonal_FIO\",
				hv.LpuRegion_id as \"LpuRegion_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				hvs.HomeVisitStatus_Code||'. '||hvs.HomeVisitStatus_Name as \"HomeVisitStatus_Name\",
				age2(p.Person_Birthday, getdate()) as \"Person_Age\",
				hv.HomeVisit_Phone as \"HomeVisit_Phone\",
				hv.HomeVisitStatus_id as \"HomeVisitStatus_id\",
				hvwc.HomeVisitWhoCall_Name as \"HomeVisitWhoCall_Name\",
				HomeVisit_Symptoms as \"HomeVisit_Symptoms\",
				HomeVisit_Comment as \"HomeVisit_Comment\",
				HomeVisit_LpuComment as \"HomeVisit_LpuComment\",
				hv.pmUser_insId as \"pmUser_insId\",
				to_char(hv.HomeVisit_insDT, '{$this->dateTimeForm104}') as \"HomeVisit_date\"
			from
				v_HomeVisit hv
				left join v_PersonState p on hv.Person_id = p.Person_id
				left join v_MedStaffFact msf on hv.MedStaffFact_id = msf.MedStaffFact_id
				left join lateral (
					select MedPersonal_id, Person_FIO
					from v_MedPersonal
					where MedPersonal_id = coalesce(hv.MedPersonal_id, msf.MedPersonal_id)
					  and Lpu_id = hv.Lpu_id
				) as mp on true
				left join v_LpuRegion lr on hv.LpuRegion_id = lr.LpuRegion_id
				left join v_HomeVisitStatus hvs on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
				left join v_HomeVisitWhoCall hvwc on hv.HomeVisitWhoCall_id = hvwc.HomeVisitWhoCall_id
			where HomeVisit_id = :HomeVisit_id
			limit 1
		";
		$sqlParams = ["HomeVisit_id" => $HomeVisit_id];
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		return $res[0];
	}

	/**
	 * Получение информации для оформления вызова на дом
	 * @param $data
	 * @return array|bool
	 */
	function getHomeVisitAddData($data)
	{
		$sql = "
			select
				p.Person_id as \"Person_id\",
				a.Address_id as \"Address_id\",
				a.KLCountry_id as \"KLCountry_id\",
				a.KLRgn_id as \"KLRgn_id\",
				a.KLSubRgn_id as \"KLSubRgn_id\",
				a.KLCity_id as \"KLCity_id\",
				a.KLTown_id as \"KLTown_id\",
				a.KLStreet_id as \"KLStreet_id\",
				a.Address_House as \"Address_House\",
				a.Address_Corpus as \"Address_Corpus\",
				a.Address_Flat as \"Address_Flat\",
				a.Address_Address as \"Address_Address\",
				l.Lpu_Nick||coalesce(', участок: '||lr.LpuRegion_Name, '') as \"Person_Attach\",
				lS.Lpu_Nick||coalesce(', участок: '||lrS.LpuRegion_Name, '') as \"Person_AttachS\",
				lr.LpuRegion_id as \"LpuRegion_id\",
				l.Lpu_id as \"Lpu_id\",
				coalesce(pi.PersonInfo_InternetPhone, p.Person_Phone) as \"HomeVisit_Phone\",
			    (extract(year from p.Person_BirthDay) - extract(year from tzgetdate())) as \"Person_Age\"
			from
				v_PersonState p
				left join v_Address a on coalesce(p.PAddress_id, p.UAddress_id) = a.Address_id
				left join v_PersonCard pc on p.Person_id = pc.Person_id and pc.LpuAttachType_id = 1
				left join v_Lpu l on l.Lpu_id = pc.Lpu_id
				left join v_LpuRegion lr on lr.LpuRegion_id = pc.LpuRegion_id
				left join v_PersonCard pcS on p.Person_id = pc.Person_id and pc.LpuAttachType_id = 3
				left join v_Lpu lS on lS.Lpu_id = pcS.Lpu_id
				left join v_LpuRegion lrS on lrS.LpuRegion_id = pcS.LpuRegion_id
				left join v_PersonInfo pi on p.Person_id = pi.Person_id
			where p.Person_id = :Person_id
			limit 1
		";
		$sqlParams = ["Person_id" => $data["Person_id"]];
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		if (
			is_array($res) &&
			count($res) > 0 &&
			!empty($res[0]["Address_id"]) &&
			empty($res[0]["LpuRegion_id"]) &&
			!empty($res[0]["Address_House"])
		) {
			if (!empty($res[0]["Person_Age"]) && $res[0]["Person_Age"] < 18) {
				$where = " and lr.LpuRegionType_SysNick in ('ped','vop')";
			} else {
				$where = " and lr.LpuRegionType_SysNick in ('ter','vop')";
			}
			$lr_params = array("Address_id" => $res[0]["Address_id"]);
			if (!empty($data["Lpu_id"])) {
				$lr_params["Lpu_id"] = $data["Lpu_id"];
				$where .= " and lr.Lpu_id = :Lpu_id";
			}
			$sql = "
				select
					lrs.LpuRegion_id as \"LpuRegion_id\",
					lrs.LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
				from
					v_Address a
					inner join LpuRegionStreet lrs on
						lrs.KLCountry_id = a.KLCountry_id
						and coalesce(lrs.KLRGN_id, 0) = coalesce(a.KLRgn_id, 0)
						and coalesce(lrs.KLSubRGN_id, 0) = coalesce(a.KLSubRgn_id, 0)
						and coalesce(lrs.KLCity_id, 0) = coalesce(a.KLCity_id, 0)
						and coalesce(lrs.KLTown_id, 0) = coalesce(a.KLTown_id, 0)
						and coalesce(lrs.KLStreet_id, 0) = coalesce(a.KLStreet_id, 0)
					inner join v_LpuRegion lr on lr.LpuRegion_id = lrs.LpuRegion_id
				where a.Address_id = :Address_id {$where}
			";
			$result = $this->db->query($sql, $lr_params);
			if (is_object($result)) {
				$resl = $result->result("array");
				if (is_array($resl) && count($resl) > 0) {
					// Ищем по вхождению дома в список домов
					foreach ($resl as $value) {
						if (!empty($value["LpuRegionStreet_HouseSet"])) {
							if (strpos($value["LpuRegionStreet_HouseSet"], ",") > 0) {
								$houses = explode(",", $value["LpuRegionStreet_HouseSet"]);
								if (is_array($houses) && count($houses) > 0) {
									if (in_array($res[0]["Address_House"], $houses)) {
										$res[0]["LpuRegion_id"] = $value["LpuRegion_id"];
										break;
									}
									// Если не нашли, то проверим на периоды (дома могут быть заданы периодами по типу {номер_дома}-{номер_дома})
									foreach ($houses as $house) {
										if (strpos($house, "-") > 0) {
											$house_set = explode("-", $house);
											if (is_array($house_set) && count($house_set) > 0) {
												if ($res[0]["Address_House"] > $house_set[0] && $res[0]["Address_House"] < $house_set[0]) {
													$res[0]["LpuRegion_id"] = $value["LpuRegion_id"];
													break;
												}
											}
										}
									}
								}
							} else if (strpos($value["LpuRegionStreet_HouseSet"], "-") > 0) {
								// указан только период домов
								$house_set = explode("-", $value["LpuRegionStreet_HouseSet"]);
								if (is_array($house_set) && count($house_set) > 0) {
									if ($res[0]["Address_House"] > $house_set[0] && $res[0]["Address_House"] < $house_set[0]) {
										$res[0]["LpuRegion_id"] = $value["LpuRegion_id"];
									}
								}
							} else if (trim($value["LpuRegionStreet_HouseSet"]) == $res[0]["Address_House"]) {
								$res[0]["LpuRegion_id"] = $value["LpuRegion_id"];
							}
						} else {
							// не указаны дома - участок обслуживает все дома на улице
							$res[0]["LpuRegion_id"] = $value["LpuRegion_id"];
						}
						// нашли участок - выходим из цикла по списку домов
						if (!empty($res[0]["LpuRegion_id"])) {
							break;
						}
					}
				}
			}
		}
		return $res;
	}

	/**
	 * Получение справочника симптомов в виде иерархической структуры
	 *
	 * @return array|bool
	 */
	function getSymptoms()
	{
		/**
		 * Генерация дерева симптомов
		 *
		 * @param array $elements
		 * @param int $parentId
		 * @return array
		 */
		function buildTree(array $elements, $parentId = 0)
		{
			$branch = [];
			foreach ($elements as $element) {
				if ($element["pid"] == $parentId) {
					$children = buildTree($elements, $element["id"]);
					if ($children) {
						$element["children"] = $children;
					}
					$branch[] = $element;
				}
			}
			return $branch;
		}

		$sql = "
			select
				HomeVisitSymptom_id as \"id\",
                HomeVisitSymptom_pid as \"pid\",
                HomeVisitSymptom_Name as \"name\",
                HomeVisitSymptom_IsRadioGroup as \"radio\",
                case when HomeVisitSymptomType_id = 2
                	then 'stom'
                	else 'ther'
                end as \"visittype\"
            from HomeVisitSymptom
		";
		$result = $this->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		$symptoms_arr = [];
		foreach ($result as $row) {
			$symptoms_arr[$row["id"]] = [
				"id" => $row["id"],
				"name" => $row["name"],
				"radio" => $row["radio"],
				"pid" => $row["pid"],
				"visittype" => $row["visittype"]
			];
			$symptoms_arr[$row["id"]]["type"] = (isset($row["pid"]))
				? (isset($symptoms_arr[$row["pid"]]["radio"]))
					? "radio"
					: "check"
				: "maingroup";
		}
		$tree = buildTree($symptoms_arr);
		return $tree;
	}

	/**
	 * Сохранение вызова на дом
	 * @param $data
	 * @param bool $callSMP
	 * @return array|mixed
	 * @throws Exception
	 */
	function addHomeVisit($data, $callSMP = false)
	{
		$status = $data["HomeVisitStatus_id"];
		$old_status = 0;
		$proc = "p_HomeVisit_ins";
		if ($data["HomeVisit_id"] > 0) {
			$proc = "p_HomeVisit_upd";
			$sql = "
				select
					HomeVisitStatus_id as \"HomeVisitStatus_id\",
					HomeVisitSource_id as \"HomeVisitSource_id\",
					HomeVisit_PhoneCall as \"HomeVisit_PhoneCall\",
					HomeVisit_GUID as \"HomeVisit_GUID\"
				from v_HomeVisit
				where HomeVisit_id = :HomeVisit_id
			";
			$tmp = $this->getFirstRowFromQuery($sql, $data);
			if (is_array($tmp) && count($tmp) > 0) {
				$old_status = $tmp["HomeVisitStatus_id"];
				$data["HomeVisitSource_id"] = $tmp["HomeVisitSource_id"];
				$data["HomeVisit_PhoneCall"] = $tmp["HomeVisit_PhoneCall"];
				$data["HomeVisit_GUID"] = $tmp["HomeVisit_GUID"];
			}
		} else {
			$data["HomeVisitStatus_id"] = 1;
		}
		if ($status == 1 && !empty($data["MedPersonal_id"])) {
			$status = 6;
		}
		if ($status == 1 && !empty($data["MedStaffFact_id"])) {
			$status = 6;
		}
		if (empty($data["HomeVisit_setDT"]) && !empty($data["HomeVisit_setDate"])) {
			$data["HomeVisit_setDT"] = $data["HomeVisit_setDate"] . " " . (!empty($data["HomeVisit_setTime"]) ? $data["HomeVisit_setTime"] : "00:00");
		}
		if (empty($data["HomeVisitSource_id"]) && !empty($data["session"]["CurArmType"])) {
			if ($data["session"]["CurArmType"] == "regpol") {
				$data["HomeVisitSource_id"] = 8;
			} elseif ($data["session"]["CurArmType"] == "callcenter") {
				$data["HomeVisitSource_id"] = 9;
			} elseif ($data["session"]["CurArmType"] == "common") {
				$data["HomeVisitSource_id"] = 1;
			}
		} elseif (empty($data["HomeVisitSource_id"]) && $callSMP) {
			$data["HomeVisitSource_id"] = 10;
		}
		$queryParams = getArrayElements(
			$data, [
				"Person_id", "Lpu_id", "LpuRegion_id", "LpuRegion_cid", "MedPersonal_id", "MedStaffFact_id", "KLRgn_id", "KLSubRgn_id", "KLCity_id", "KLTown_id", "KLStreet_id", "Address_House",
				"Address_Flat", "Address_Corpus", "Address_Address", "HomeVisit_Phone", "HomeVisitWhoCall_id", "HomeVisit_Symptoms", "HomeVisit_Comment", "HomeVisit_LpuComment",
				"HomeVisitStatus_id", "HomeVisitCallType_id", "HomeVisit_setDT", "pmUser_id", "HomeVisit_id", "CallProfType_id", "HomeVisit_Num", "CmpCallCard_id",
				"HomeVisitSource_id", "HomeVisit_PhoneCall", "HomeVisit_GUID", "HomeVisit_isQuarantine"
			]
		);
		if (empty($queryParams["HomeVisit_id"])) {
			$queryParams["HomeVisit_id"] = [
				"value" => null,
				"out" => true,
				"type" => "bigint",
			];
		}
		if (($resp = $this->execCommonSP($proc, $queryParams))) {
			$IsSMPServer = $this->config->item("IsSMPServer");
			if ($IsSMPServer && !empty($resp[0]["HomeVisit_id"]) && defined("STOMPMQ_MESSAGE_ENABLE") && STOMPMQ_MESSAGE_ENABLE === TRUE) {
				$this->load->model("Replicator_model");
				$this->Replicator_model->sendRecordToActiveMQ([
					"table" => "HomeVisit",
					"type" => empty($data["HomeVisit_id"]) ? "insert" : "update",
					"keyParam" => "HomeVisit_id",
					"keyValue" => $resp[0]["HomeVisit_id"]
				], "/queue/dbReplicator.HomeVisit.ProMed.Emergency.HomeVisit");
			}
			if (!$data["HomeVisit_id"] > 0) {
				$data["HomeVisit_id"] = $resp[0]["HomeVisit_id"];
			}
			if ($status != $data["HomeVisitStatus_id"]) {
				$data["HomeVisitStatus_id"] = $status;
				$this->updateStatus($data);
			} elseif ($status != $old_status) {
				$this->saveHomeVisitStatusHist($data);
			}
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function updateStatus($data)
	{
		$queryParams = getArrayElements($data, ["HomeVisit_id", "MedPersonal_id", "MedStaffFact_id", "HomeVisit_LpuComment", "pmUser_id"]) + [
				"HomeVisitStatus_id" => $data["HomeVisitStatus_id"], // статус Одобрено
			];
		if (!key_exists("HomeVisitSource_id", $data)) {
			// если не передан источник, то берем существующий
			$sql = "
				select HomeVisitSource_id as \"HomeVisitSource_id\"
				from HomeVisit
				where HomeVisit_id = :HomeVisit_id
				limit 1
			";
			$sqlParams = [
				"HomeVisit_id" => $data["HomeVisit_id"]
			];
			if ($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery($sql, $sqlParams)) {
				$queryParams["HomeVisitSource_id"] = $homeVisitSourceId;
			}
		} else {
			$queryParams["HomeVisitSource_id"] = $data["HomeVisitSource_id"];
		}
		if (($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams))) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		} else {
			throw new Exception("Ошибка запроса к БД.");
		}
	}

	/**
	 * Проверка, что вызов на дом уже не существует
	 * @param $data
	 * @return bool
	 */
	function checkHomeVisitExists($data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		if (!empty($data["HomeVisit_setDate"])) {
			$params["setDate"] = $data["HomeVisit_setDate"];
		} else if (!empty($data["HomeVisit_setDT"])) {
			$params["setDate"] = $data["HomeVisit_setDT"];
		} else {
			$params["setDate"] = date("Y-m-d");
		}
		$sql = "
			select 
				hv.Lpu_id as \"Lpu_id\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				coalesce(hv.HomeVisit_Num, '') as \"HomeVisit_Num\",
				to_char(hv.HomeVisit_setDT, '{$this->dateTimeForm104}') as \"HomeVisit_setDT\"
			from v_HomeVisit hv
				left join v_Lpu lpu on lpu.Lpu_id = hv.Lpu_id
			where hv.Person_id = :Person_id
			  and hv.HomeVisitStatus_id in (1,3,6)
			  and cast(hv.HomeVisit_setDT as date) = cast(:setDate as date)
		";
		$result = $this->db->query($sql, $params);
		if (!is_object($result) || isset($data["HomeVisit_id"])) {
			return false;
		}
		$res = $result->result("array");
		return (isset($res[0]) && $res[0]["Lpu_id"] > 0) ? $res : null;
	}

	/**
	 * История статусов вызова
	 * @param $data
	 * @return array|bool
	 */
	function loadHomeVisitStatusHist($data)
	{
		$sql = "
			SELECT
				HVSH.HomeVisitStatusHist_id as \"HomeVisitStatusHist_id\",
			    HVS.HomeVisitStatus_Name as \"HomeVisitStatus_Name\",
			    to_char(HVSH.HomeVisitStatusHist_setDT, '{$this->dateTimeForm104}')||' '||to_char(HVSH.HomeVisitStatusHist_setDT, '{$this->dateTimeForm108}') as \"HomeVisitStatusHist_setDT\",
			    U.pmUser_Name as \"pmUser_Name\"
			FROM
				v_HomeVisitStatusHist HVSH
				INNER JOIN v_HomeVisitStatus HVS ON HVS.HomeVisitStatus_id = HVSH.HomeVisitStatus_id
				LEFT JOIN v_pmUser U on U.pmUser_id = HVSH.pmUser_insID and (U.Kind != '1' or U.Kind is null)
			WHERE HVSH.HomeVisit_id = :HomeVisit_id
		";
		$sqlParams = ["HomeVisit_id" => $data["HomeVisit_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка грида с доп временем или формы
	 * @param $data
	 * @return array|bool
	 */
	function loadHomeVisitAdditionalSettings($data)
	{
		$where = "HVAS.Lpu_id = :Lpu_id";
		$params = [
			"Lpu_id" => $data["Lpu_id"]
		];
		if (!empty($data["HomeVisitAdditionalSettings_id"])) {
			$where .= " and HVAS.HomeVisitAdditionalSettings_id = :HomeVisitAdditionalSettings_id";
			$params["HomeVisitAdditionalSettings_id"] = $data["HomeVisitAdditionalSettings_id"];
		}
		$sql = "
			SELECT
				HVAS.HomeVisitAdditionalSettings_id as \"HomeVisitAdditionalSettings_id\",
				to_char(HVAS.HomeVisitAdditionalSettings_begDate, '{$this->dateTimeForm104}') as \"HomeVisitAdditionalSettings_begDate\",
				to_char(HVAS.HomeVisitAdditionalSettings_endDate, '{$this->dateTimeForm104}') as \"HomeVisitAdditionalSettings_endDate\",
				to_char(HVAS.HomeVisitAdditionalSettings_begTime, '{$this->dateTimeForm108}') as \"HomeVisitAdditionalSettings_begTime\",
				to_char(HVAS.HomeVisitAdditionalSettings_endTime, '{$this->dateTimeForm108}') as \"HomeVisitAdditionalSettings_endTime\",
				HVAS.Lpu_id as \"Lpu_id\",
				HVAS.HomeVisitPeriodType_id as \"HomeVisitPeriodType_id\",
				HVPT.HomeVisitPeriodType_Name as \"HomeVisitPeriodType_Name\"
			FROM
				v_HomeVisitAdditionalSettings HVAS
				INNER JOIN v_HomeVisitPeriodType HVPT ON HVAS.HomeVisitPeriodType_id = HVPT.HomeVisitPeriodType_id
			WHERE {$where}
		";
		$result = $this->db->query($sql, $params);
		/**@var CI_DB_result $result */
		if (!is_object($result)) {
			return false;
		}
		return ["data" => $result->result("array")];
	}

	/**
	 * Удаление доп времени
	 * @param $data
	 * @return array|bool
	 */
	function deleteHomeVisitAdditionalSettings($data)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from p_homevisitadditionalsettings_del(
				homevisitadditionalsettings_id := :HomeVisitAdditionalSettings_id
			);
		";
		$result = $this->db->query($query, $data);
		/**@var CI_DB_result $result */
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение истории статусов вызова
	 * @param $data
	 * @return array|bool
	 */
	function saveHomeVisitStatusHist($data)
	{
		$params = [
			"HomeVisitStatusHist_id" => isset($data["HomeVisitStatusHist_id"]) ? $data["HomeVisitStatusHist_id"] : null,
			"HomeVisit_id" => $data["HomeVisit_id"],
			"HomeVisitStatus_id" => $data["HomeVisitStatus_id"],
			"MedPersonal_id" => isset($_SESSION["medpersonal_id"]) ? $_SESSION["medpersonal_id"] : null,
			"MedStaffFact_id" => !empty($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = "
			select
				homevisitstatushist_id as \"HomeVisitStatusHist_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from p_homevisitstatushist_ins(
			    homevisit_id := :HomeVisit_id,
			    homevisitstatus_id := :HomeVisitStatus_id,
			    homevisitstatushist_setdt := tzgetdate(),
			    medpersonal_id := :MedPersonal_id,
			    medstafffact_id := :MedStaffFact_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		$IsSMPServer = $this->config->item("IsSMPServer");
		if ($IsSMPServer && !empty($resp[0]["HomeVisitStatusHist_id"]) && defined("STOMPMQ_MESSAGE_ENABLE") && STOMPMQ_MESSAGE_ENABLE === TRUE) {
			$this->load->model("Replicator_model");
			$this->Replicator_model->sendRecordToActiveMQ([
				"table" => "HomeVisitStatusHist",
				"type" => "insert",
				"keyParam" => "HomeVisitStatusHist_id",
				"keyValue" => $resp[0]["HomeVisitStatusHist_id"]
			], "/queue/dbReplicator.HomeVisit.ProMed.Emergency.HomeVisitStatusHistory");
		}
		return $resp;
	}

	/**
	 * Сохранение режима работы
	 * @param $data
	 * @return bool
	 */
	function saveHomeVisitWorkMode($data)
	{
		// Удаляем все что относится к ЛПУ		
		$query = "
			delete from HomeVisitWorkMode
			where Lpu_id = :Lpu_id
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		// Вставляем
		if (!empty($data["homevizit_isallowed"]) && $data["homevizit_isallowed"] == "on") {
			for ($i = 1; $i <= 7; $i++) {
				if (isset($data["homevizit_day" . $i]) && $data["homevizit_day" . $i] == "on") {
					$params = [
						"week_id" => $i,
						"lpu_id" => $data["Lpu_id"],
						"homevizit_begtime" => isset($data["homevizit_begtime" . $i]) ? $data["homevizit_begtime" . $i] : null,
						"homevizit_endtime" => isset($data["homevizit_endtime" . $i]) ? $data["homevizit_endtime" . $i] : null,
						"pmUser_id" => $data["pmUser_id"]
					];
					$query = "
						select
							homevisitworkmode_id as \"HomeVisitWorkMode_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Message\"
						from p_homevisitworkmode_ins(
							lpu_id := :lpu_id,
						    calendarweek_id := :week_id,
						    homevisitworkmode_begdate := cast(dbo.tzgetdate() as date) + cast(:homevizit_begtime as time),
						    homevisitworkmode_enddate := cast(dbo.tzgetdate() as date) + cast(:homevizit_endtime as time),
						    pmuser_id := :pmUser_id
						);
					";
					$result = $this->db->query($query, $params);
				}
			}
		}
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение доп времени работы вызова врача
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveHomeVisitAdditionalSettings($data)
	{
		$preQuery = "
			select
				HomeVisitAdditionalSettings_begDate + HomeVisitAdditionalSettings_begTime as \"HomeVisitAdditionalSettings_begDate\",
				HomeVisitAdditionalSettings_endDate + HomeVisitAdditionalSettings_endTime as \"HomeVisitAdditionalSettings_endDate\"
			from v_HomeVisitAdditionalSettings
			where
				Lpu_id = :Lpu_id AND
				HomeVisitPeriodType_id = :HomeVisitPeriodType_id AND
				HomeVisitAdditionalSettings_id != coalesce(:HomeVisitAdditionalSettings_id::bigint, 0)AND
			    (
					(:HomeVisitAdditionalSettings_begDateTime BETWEEN HomeVisitAdditionalSettings_begDate + HomeVisitAdditionalSettings_begTime AND HomeVisitAdditionalSettings_endDate + HomeVisitAdditionalSettings_endTime) or
					(:HomeVisitAdditionalSettings_endDateTime BETWEEN HomeVisitAdditionalSettings_begDate + HomeVisitAdditionalSettings_begTime AND HomeVisitAdditionalSettings_endDate + HomeVisitAdditionalSettings_endTime) or
					(HomeVisitAdditionalSettings_begDate + HomeVisitAdditionalSettings_begTime BETWEEN :HomeVisitAdditionalSettings_begDateTime AND :HomeVisitAdditionalSettings_endDateTime)
				)
		";
		$preParams = [
			"HomeVisitAdditionalSettings_id" => !empty($data["HomeVisitAdditionalSettings_id"]) ? $data["HomeVisitAdditionalSettings_id"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"HomeVisitPeriodType_id" => $data["HomeVisitPeriodType_id"],
			"HomeVisitAdditionalSettings_begDateTime" => $data["HomeVisitAdditionalSettings_begDate"] . " " . $data["HomeVisitAdditionalSettings_begTime"],
			"HomeVisitAdditionalSettings_endDateTime" => $data["HomeVisitAdditionalSettings_endDate"] . " " . $data["HomeVisitAdditionalSettings_endTime"]
		];
		$result = $this->db->query($preQuery, $preParams);
		$result = $result->result("array");
		if (count($result) > 0) {
			throw new Exception("Пересечение даты периода работы или выходных. Проверьте корректность введенной даты");
		}

        $selectString = "
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\",
		";

        if (empty($data["HomeVisitAdditionalSettings_id"])) {
            $procedure = "ins";
            $selectId = "HomeVisitAdditionalSettings_id as \"HomeVisitAdditionalSettings_id\"";
            $data["HomeVisitAdditionalSettings_id"] = null;
        } else {
            $procedure = "upd";
            $selectId = ":HomeVisitAdditionalSettings_id as \"HomeVisitAdditionalSettings_id\"";
        }
		 
		$query = "
			select
                {$selectString}
                {$selectId}
			from p_HomeVisitAdditionalSettings_{$procedure}(
			    HomeVisitAdditionalSettings_id := :HomeVisitAdditionalSettings_id,
			    lpu_id := :Lpu_id,
			    homevisitperiodtype_id := :HomeVisitPeriodType_id,
			    homevisitadditionalsettings_begdate := :HomeVisitAdditionalSettings_begDate,
			    homevisitadditionalsettings_enddate := :HomeVisitAdditionalSettings_endDate,
			    homevisitadditionalsettings_begtime := :HomeVisitAdditionalSettings_begTime,
			    homevisitadditionalsettings_endtime := :HomeVisitAdditionalSettings_endTime,
				pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение режима работы
	 * @param $data
	 * @return array|bool
	 */
	function getHomeVisitWorkMode($data)
	{
		$query = "
            select
            	HomeVisitWorkMode_id as \"HomeVisitWorkMode_id\",
				Lpu_id as \"Lpu_id\",
				CalendarWeek_id as \"CalendarWeek_id\",
				to_char(HomeVisitWorkMode_begDate, 'dd.mm.yyyy HH24:MI:SS') as \"HomeVisitWorkMode_begDate\",
				to_char(HomeVisitWorkMode_endDate, 'dd.mm.yyyy HH24:MI:SS') as \"HomeVisitWorkMode_endDate\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				HomeVisitWorkMode_insDT as \"HomeVisitWorkMode_insDT\",
				HomeVisitWorkMode_updDT as \"HomeVisitWorkMode_updDT\"
            from v_HomeVisitWorkMode HVWM
            where HVWM.Lpu_id = :Lpu_id
        ";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает список ЛПУ с действующими периодом, в котором МО может производить обслуживание населения на дому по стоматологическим профилям
	 * @param $data
	 * @return array|bool
	 */
	function getLpuPeriodStomMOList($data)
	{
		$query = "
            select LPS.Lpu_id as \"Lpu_id\"
            from v_LpuPeriodStom LPS
            where LPS.LpuPeriodStom_begDate <= tzgetdate()
             and (LPS.LpuPeriodStom_endDate is null or LPS.LpuPeriodStom_endDate > tzgetdate())
        ";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает номер активного нумератора
	 * @param $data
	 * @param null $numerator
	 * @return array
	 * @throws Exception
	 */
	function getHomeVisitNum($data, $numerator = null)
	{
		$params = [
			"NumeratorObject_SysName" => "HomeVisit",
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $data["pmUser_id"],
			"onDate" => $data["onDate"],
			"Numerator_id" => $data["Numerator_id"]
		];
		$name = "Вызов врача на дом";
		$this->load->model("Numerator_model");
		$resp = $this->Numerator_model->getNumeratorNum($params, $numerator);
		if (!empty($resp["Numerator_Num"])) {
			return $resp;
		} else {
			if (!empty($resp["Error_Msg"])) {
				throw new Exception($resp["Error_Msg"]);
			}
			throw new Exception("Не задан активный нумератор для [" . $name . "]. Обратитесь к администратору системы.");
		}
	}

	/**
	 * Получение количества вызовов с назначенным врачем за день
	 * @param $data
	 * @return array
	 */
	function getHomeVisitCount($data)
	{
		$params = [
			"MedPersonal_id" => $data["MedPersonal_id"],
			"date" => $data["date"],
		];
		$query = "
			select count(1) as \"HomeVisitCount\"
			from v_HomeVisit HV
			where HV.HomeVisitStatus_id = 6
			  and HV.MedPersonal_id = :MedPersonal_id
			  and cast(HV.HomeVisit_setDT as date) = :date
			limit 1
		";
		$HomeVisitCount = $this->getFirstResultFromQuery($query, $params);
		if ($HomeVisitCount === false) {
			return $this->createError('', 'Ошибка при получении количества вызовов');
		}
		return [['success' => true, 'Error_Msg' => '', 'HomeVisitCount' => $HomeVisitCount]];
	}

	/**
	 * Установка статуса
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function setHomeVisitStatus($data)
	{
		$sql = "
			select
				homevisit_id as \"HomeVisit_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from p_homevisit_setstatus(
			    homevisit_id := :HomeVisit_id,
			    medstafffact_id = :MedStaffFact_id,
			    homevisitstatus_id := :HomeVisitStatus_id,
			    homevisit_lpucomment := :HomeVisit_LpuComment,
			    homevisitsource_id := :HomeVisitSource_id,
			    pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"HomeVisit_id" => $data["HomeVisit_id"],
			"HomeVisitStatus_id" => $data["HomeVisitStatus_id"],
			"pmUser_id" => $data["pmUser_id"],
			"HomeVisitSource_id" => $data["HomeVisitSource_id"],
			"HomeVisit_LpuComment" => !empty($data["HomeVisit_LpuComment"]) ? $data["HomeVisit_LpuComment"] : null,
			"MedStaffFact_id" => !empty($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null
		];
		$resp = $this->queryResult($sql, $sqlParams);
		if (!empty($resp[0]["Error_Msg"])) {
			throw new Exception($resp[0]["Error_Msg"]);
		}
		return $resp;
	}

	/**
	 * Получение ближайшего возможного времени для вызова на дом
	 * @param $data
	 * @param bool $desiredDate
	 * @return array|bool
	 * @throws Exception
	 */
	function getHomeVisitNearestWorkDay($data, $desiredDate = false)
	{
		if (empty($data["Lpu_id"])) {
			return false;
		}
		$desiredDate = ($desiredDate && ($desiredDate instanceof DateTime)) ? $desiredDate : $this->getCurrentDT();
		$respDate = $desiredDate;
		$CalendarWeek_id = intval($desiredDate->format("N"));
		//Расписание работы сервиса ПН - ВС
		$arrHomeVisitWorkMode = $this->getHomeVisitWorkMode(["Lpu_id" => $data["Lpu_id"]]);
		$dateInWorkPeriod = null;
		for ($i = 0; $i < 7; $i++) {
			// перебираем неделю начиная с сегодня на 7 дней вперед
			$calcDay = (($CalendarWeek_id + $i) % 7);
			if ($calcDay == 0) $calcDay = 7;
			//перебираем массив сохраненного расписания
			if(is_array($arrHomeVisitWorkMode)) {
				foreach ($arrHomeVisitWorkMode as $workDay) {
					//нашли тот день по порядку в цикле(начиная с сегодня)
					if ($workDay["CalendarWeek_id"] == $calcDay) {
						$begDate = new DateTime($desiredDate->format("Y-m-d") . " " . DateTime::createFromFormat('d.m.Y H:i:s', $workDay["HomeVisitWorkMode_begDate"])->format("H:i:s"));
						$endDate = new DateTime($desiredDate->format("Y-m-d") . " " . DateTime::createFromFormat('d.m.Y H:i:s', $workDay["HomeVisitWorkMode_endDate"])->format("H:i:s"));
						//Если день совпал с нашим (если заведен - будет первым)
						if ($workDay["CalendarWeek_id"] == $CalendarWeek_id) {
							//Проверяем время
							if ($begDate <= $desiredDate && $endDate > $desiredDate) {
								//Время вовпало - возвращаем желаемую дату
								$respDate = $desiredDate;
								$dateInWorkPeriod = true;
								goto jumpOut;
							} else {
								//Время не наступило - возвращаем ближ. дату
								if ($begDate > $desiredDate) {
									$respDate = $begDate;
									$dateInWorkPeriod = false;
									goto jumpOut;
								}
							}
						} else {
							//нет? - просто берем первый попавшийся и уходим
							$begDate->add(new DateInterval('P' . $i . 'D'));
							$respDate = $begDate;
							$dateInWorkPeriod = false;
							goto jumpOut;
						}
					}
				}
			}
		}
		jumpOut:;
		//Проверим входит ли этот день в список дополнительных выходных
		$selectString = "
			to_char(HomeVisitAdditionalSettings_endDate, '{$this->dateTimeFormUnix}')||' '||to_char(HomeVisitAdditionalSettings_endTime, '{$this->dateTimeForm108}')) as \"HomeVisitAdditionalSettings_endDate\"
		";
		$fromString = "
			v_HomeVisitAdditionalSettings
		";
		$whereString = "
				Lpu_id=:Lpu_id AND HomeVisitPeriodType_id = 2
			AND (:Date BETWEEN HomeVisitAdditionalSettings_begDate AND HomeVisitAdditionalSettings_endDate)
			AND (:Time >= CAST(HomeVisitAdditionalSettings_begTime AS TIME) AND :Time < CAST(HomeVisitAdditionalSettings_endTime AS TIME))
		";
		$weekendQuery = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			limit 1
		";
		$weekendParams = [
			"Lpu_id" => $data["Lpu_id"],
			"Date" => $respDate->format("Y-m-d"),
			"Time" => $respDate->format("H:i:s")
		];
		$weekendSettings = $this->queryResult($weekendQuery, $weekendParams);
		if (count($weekendSettings) > 0) {
			$this->getHomeVisitNearestWorkDay($data, DateTime::createFromFormat("Y-m-d H:i:s", $weekendSettings[0]["HomeVisitAdditionalSettings_endDate"]));
			$dateInWorkPeriod = false;
		}
		//Проверим есть ли дополнительные рабочие дни ранее
		//(больше желаемой даты и меньше итоговой)
		$workQuery = "
			select
				HomeVisitAdditionalSettings_begDate + HomeVisitAdditionalSettings_begTime as \"HomeVisitAdditionalSettings_begDT\",
                case when :desiredDate >= HomeVisitAdditionalSettings_begDate + HomeVisitAdditionalSettings_begTime then 2 else 1 end as \"dateInWorkPeriod\"
			from v_HomeVisitAdditionalSettings
			where Lpu_id=:Lpu_id
			  and HomeVisitPeriodType_id = 1
			  and (
			      	(HomeVisitAdditionalSettings_begDate + HomeVisitAdditionalSettings_begTime BETWEEN :desiredDate and :respDate) or
			      	(HomeVisitAdditionalSettings_endDate + HomeVisitAdditionalSettings_endTime BETWEEN :desiredDate and :respDate)
			      )
			order by HomeVisitAdditionalSettings_begTime
			limit 1
		";
		$workParams = [
			"Lpu_id" => $data["Lpu_id"],
			"desiredDate" => $desiredDate->format("Y-m-d H:i:s"),
			"respDate" => $respDate->format("Y-m-d H:i:s")
		];
		$workSettings = $this->queryResult($workQuery, $workParams);
		//Если есть то берем первый
		if (count($workSettings) > 0) {
			if ($workSettings[0]["dateInWorkPeriod"] == 2) {
				$respDate = $desiredDate;
				$dateInWorkPeriod = true;
			} else {
				$respDate = new DateTime($workSettings[0]["HomeVisitAdditionalSettings_begDT"]);
				$dateInWorkPeriod = false;
			}
		}
		return [
			"NearestDate" => $respDate,
			"DateInPeriod" => $dateInWorkPeriod
		];
	}

	/**
	 * Проверка на наличие обслуженного вызова на дом
	 *
	 * @param $data
	 * @return bool|float|int|string
	 */
	function checkHomeVizit($data)
	{
		if ($data["EvnClass_SysNick"] == "EvnVizitPL") {
			$from = "v_EvnVizitPL";
			$where = "EvnVizitPL_id";
		} else {
			$from = "v_EvnVizitPLStom";
			$where = "EvnVizitPLStom_id";
		}
		$whereString = "
				{$where} = :Evn_id
			and HomeVisit_id is not null
		";
		$query = "
			select 1
			from {$from}
			where {$whereString}
			limit 1
		";
		$res = $this->getFirstResultFromQuery($query, $data);
		return $res;
	}

	/**
	 * Изменение статуса вызова на дом в посещении
	 *
	 * @param $data
	 * @return bool|mixed
	 */
	function revertHomeVizitStatus($data)
	{
		$selectString = "HomeVisit_id as \"HomeVisit_id\"";
		$from = "v_{$data['EvnClass_SysNick']}";
		$where = "{$data['EvnClass_SysNick']}_id";
		$query = "
			select {$selectString}
			from {$from} hv
			where {$where} = :Evn_id
			limit 1
		";
		$res = $this->getFirstResultFromQuery($query, $data);
		if ($res) {
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Message\"
				from p_homevisitstatushist_del(
				    homevisitstatushist_id := (
						select HomeVisitStatusHist_id
						from v_HomeVisitStatusHist
						where HomeVisit_id = :HomeVizit_id
						order by HomeVisitStatusHist_setDT desc
						limit 1
					)
				);
			";
			$queryParams = [
				"HomeVizit_id" => $res,
				"pmUser_id" => $data["pmUser_id"]
			];
			$this->db->query($query, $queryParams);
			$query = "
				select
					homevisit_id as \"HomeVisit_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Message\"
				from p_homevisit_setstatus(
				    homevisit_id := :HomeVizit_id,
				    homevisitstatus_id := (
						select HomeVisitStatus_id
						from (
						        select HomeVisitStatus_id,
						               row_number() over (order by HomeVisitStatusHist_updDT) as rownum
						        from v_HomeVisitStatusHist
						        where HomeVisit_id = :HomeVizit_id
						        order by HomeVisitStatusHist_setDT desc
						        limit 2
						    ) as t
						where rownum = 2
						limit 1
				    ),
				    medstafffact_id := (
						select MedStaffFact_id
						from v_HomeVisitStatusHist
						where HomeVisit_id = :HomeVizit_id
						order by HomeVisitStatusHist_setDT desc
				        limit 1
				    ),
				    pmuser_id := :pmUser_id
				);
			";
			$result = $this->db->query($query, $queryParams);
			$result = $result->result("array");
			if (isset($result[0])) {
				return $result;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * * Получение МО по адресу
	 */
	function getMO($data)
	{
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['KLTown_id'])) {
			$filters .= " and lrs.KLTown_id = :KLTown_id";
			$params['KLTown_id'] = $data['KLTown_id'];
		}

		if (!empty($data['KLStreet_id'])) {
			$filters .= " and lrs.KLStreet_id = :KLStreet_id";
			$params['KLStreet_id'] = $data['KLStreet_id'];
		}

		if (!empty($data['KLCity_id'])) {
			$filters .= " and lrs.KLCity_id = :KLCity_id";
			$params['KLCity_id'] = $data['KLCity_id'];
		}

		$query = "
			SELECT
				KLCity_id as \"KLCity_id\", 
				KLTown_id as \"KLTown_id\", 
				KLStreet_id as \"KLStreet_id\", 
				RTRIM(LpuRegionStreet_HouseSet) as \"LpuRegionStreet_HouseSet\",
				lr.LpuRegion_id as \"LpuRegion_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				lr.LpuRegionType_SysNick as \"LpuRegionType_SysNick\",
				lr.Lpu_id as \"Lpu_id\"
			FROM 
				LpuRegionStreet lrs
				inner join v_LpuRegion lr
					on lrs.LpuRegion_id = lr.LpuRegion_id and 
					lr.LpuRegionType_SysNick in ('ter','ped') 
					and 
					( (lrs.KLTown_id is not null) or (lrs.KLStreet_id is not null) or (lrs.KLCity_id is not null) )
			where
				{$filters}
		";

		//echo(getDebugSQL($query, $params));die;
		$res = $this->db->query($query, $params);

		if (is_object($res)) {
			$lpuregions_data = $res->result('array');
			if (is_array($lpuregions_data) && count($lpuregions_data) > 0) {
				foreach ($lpuregions_data as $lpuregion_area) { 
					if (!empty($lpuregion_area['LpuRegionStreet_HouseSet'])) {
						if (strlen($data['Address_House']) > 0) {
							$this->load->model('AutoAttach_model', 'autoattachmodel');
							if ($this->autoattachmodel->HouseExist(array($data['Address_House']), $lpuregion_area['LpuRegionStreet_HouseSet']) === true) {
								if ($data['Person_Age'] <= 17 && $lpuregion_area['LpuRegionType_SysNick'] == 'ped')
									return array('Lpu_id'=>$lpuregion_area['Lpu_id']);
								if ($data['Person_Age'] > 17 && $lpuregion_area['LpuRegionType_SysNick'] != 'ped')
									return array('Lpu_id'=>$lpuregion_area['Lpu_id']);
							}
						}
					}
				}
			}
		}
	
		return array('Lpu_id'=>null);
	}
}
