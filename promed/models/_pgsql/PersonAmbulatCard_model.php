<?php
defined("BASEPATH") or die("No direct script access allowed");
/**
 * Class PersonAmbulatCard_model
 *
 * @property CI_DB_driver $db
 * @property Polka_PersonCard_model $Polka_PersonCard_model
 */
class PersonAmbulatCard_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI:SS";
	private $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkPersonAmbulatCard($data)
	{
		$query = "
			select
				pac.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
			    pac.PersonAmbulatCard_Num as \"PersonCard_Code\"
			from v_PersonAmbulatCard pac 
			where pac.Lpu_id = :Lpu_id
			  and pac.Person_id = :Person_id
			  and tzgetdate() between pac.PersonAmbulatCard_begDate and coalesce(PersonAmbulatCard_endDate, tzgetdate())
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		$result = $result->result("array");
		if ($data["getCount"]) {
			return [["count" => count($result)]];
		}
		if (count($result)) {
			return [["PersonAmbulatCard_id" => $result[0]["PersonAmbulatCard_id"], "PersonCard_Code" => $result[0]["PersonCard_Code"]?  $result[0]["PersonCard_Code"] : "", "PersonAmbulatCard_Count" => count($result)]];
		} elseif (!in_array(getRegionNick(), ["ufa", "pskov", "hakasiya", "kaluga"])) {
			$this->load->model("Polka_PersonCard_model");
			if (empty($data["PersonAmbulatCard_Num"])) {
				$resp = $this->Polka_PersonCard_model->getPersonCardCode($data);
				$data["PersonAmbulatCard_Num"] = @$resp[0]["PersonCard_Code"];
			}
			if (getRegionNick() == "ufa") {
				$data["firstAdd"] = 1;
			}
			$resp = $this->savePersonAmbulatCard($data);
			if (!is_array($resp) || count($resp) == 0) {
				throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение амбулаторной карты)");
			} else if (!empty($resp[0]["Error_Msg"])) {
				return $resp;
			}
			$data["PersonAmbulatCard_id"] = $resp[0]["PersonAmbulatCard_id"];
			$this->savePersonAmbulatCardLocat($data);
			return [["PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"], "PersonCard_Code" => $data["PersonAmbulatCard_Num"], "PersonAmbulatCard_Count" => 1, "newPersonAmbulatCard_id" => $data["PersonAmbulatCard_id"]]];
		} else {
			return [["PersonAmbulatCard_id" => null, "PersonCard_Code" => "", "PersonAmbulatCard_Count" => 0, "newPersonAmbulatCard_id" => null]];
		}
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deletePersonAmbulatCard($data)
	{
		/**@var CI_DB_result $result */
		$query = "select COUNT(1) as cnt from v_PersonAmbulatCardLink where PersonAmbulatCard_id = :PersonAmbulatCard_id";
		$result = $this->db->query($query, $data);
		$res = $result->result("array");
		if ($res[0]["cnt"] > 0) {
			throw new Exception("Оригинал АК имеет связь с прикреплением");
		}
		//удаляем прикрепления амбулаторной карты к картохранилищу
		$this->deleteAttachmentAmbulatoryCardToCardStore($data);
		$query = "select PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\" from PersonAmbulatCardLocat where PersonAmbulatCard_id = :PersonAmbulatCard_id";
		$result = $this->db->query($query, $data);
		$result = $result->result("array");
		if (count($result) > 0) {
			foreach ($result as $item) {
				$query = "
					select
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					from p_personambulatcardlocat_del(personambulatcardlocat_id := :PersonAmbulatCardLocat_id);
				";
				$queryParams = ["PersonAmbulatCardLocat_id" => $item["PersonAmbulatCardLocat_id"]];
				$this->db->query($query, $queryParams);
			}
		}
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_personambulatcard_del(personambulatcard_id := :PersonAmbulatCard_id);
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение результатов измерения массы пациента)");
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonCard($data)
	{
		$params = [];
		$filterArray = ["tzgetdate() between s.PersonAmbulatCard_begDate and coalesce(s.PersonAmbulatCard_endDate, tzgetdate())"];
		if (isset($data["Person_id"])) {
			$params["Person_id"] = $data["Person_id"];
			$filterArray[] = "Person_id = :Person_id";
		}
		if (isset($data["Lpu_id"])) {
			$params["Lpu_id"] = $data["Lpu_id"];
			$filterArray[] = "Lpu_id = :Lpu_id";
		}
		$filterString = implode(" and ", $filterArray);
		$query = "
			select PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
			       PersonAmbulatCard_id as \"PersonAmbulatCard_id\"
			from v_PersonAmbulatCard s
			where {$filterString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @param string $ad
	 * @return array|bool
	 */
	function getPersonAmbulatCardList($data, $ad = "ASC")
	{
		$sql = "
			select 
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PAC.Person_id as \"Person_id\",
				PACLink.PersonCard_id as \"PersonCard_id\",
				PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
				to_char(PACL.PersonAmbulatCardLocat_begDate, '{$this->dateTimeForm104}') as \"PersonAmbulatCardLocat_begDate\",
				ACLT.AmbulatCardLocatType_Name as \"AmbulatCardLocatType_Name\",
				ACLT.AmbulatCardLocatType_id as \"AmbulatCardLocatType_id\",
				PACL.LpuBuilding_Name as \"LpuBuilding_Name\",
				ACLB_LB.LpuBuilding_Name as \"AttachmentLpuBuilding_Name\",
				to_char(PAC.PersonAmbulatCard_endDate, '{$this->dateTimeForm104}') as \"PersonAmbulatCard_endDate\",
				PACL.MedStaffFact_id as \"CardLocationMedStaffFact_id\",
				coalesce(AmbulatCardLocatType_Name, '')||coalesce(', '||PACL.LpuBuilding_Name, '')||coalesce(', '||PACL.FIO, '') as \"MapLocation\",
				PAC.PersonAmbulatCard_CloseCause as \"PersonAmbulatCard_CloseCause\",
				case when PACLink.PersonAmbulatCardLink_id is not null then 'true' else 'false' end as \"isAttach\"
			from v_PersonAmbulatCard PAC
				left join lateral (
				    select PersonAmbulatCardLink_id, PersonCard_id
				    from v_PersonAmbulatCardLink PACLink
				    where PAC.PersonAmbulatCard_id=PACLink.PersonAmbulatCard_id
				    limit 1
				) as PACLink on true
				left join lateral (
					select vPACL.PersonAmbulatCardLocat_begDate, vPACL.PersonAmbulatCardLocat_id, vPACL.AmbulatCardLocatType_id, LB.LpuBuilding_Name, MSF.MedStaffFact_id, MSF.Person_Fio as FIO
					from 
						v_PersonAmbulatCardLocat vPACL 
						left join v_LpuBuilding LB on LB.LpuBuilding_id = vPACL.LpuBuilding_id
						left join v_MedStaffFact MSF on MSF.MedStaffFact_id = vPACL.MedStaffFact_id
					where PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
					order by PersonAmbulatCardLocat_begDate desc
				    limit 1
				) as PACL on true
				left join AmbulatCardLocatType ACLT on PACL.AmbulatCardLocatType_id = ACLT.AmbulatCardLocatType_id
				left join v_AmbulatCardLpuBuilding ACLB on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				left join v_LpuBuilding ACLB_LB on ACLB_LB.LpuBuilding_id = ACLB.LpuBuilding_id
			where PAC.Person_id = :Person_id
			  and PAC.Lpu_id = :Lpu_id
			  and tzgetdate() between PAC.PersonAmbulatCard_begDate and coalesce(PAC.PersonAmbulatCard_endDate, tzgetdate())
			order by PAC.PersonAmbulatCard_id {$ad}
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var $result CI_DB_result */
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonAmbulatCard($data)
	{
		if (getRegionNick() != "astra") {
			$checkResult = $this->checkUniqCard($data);
			if ($checkResult != false && isset($checkResult[0]["PersonAmbulatCard_id"]) && !isset($data["ignoreUniq"])) {
				if (getRegionNick() == "ufa") {
					return [["Error_Msg" => "", "Alert_Msg" => "Карта с таким номером уже существует. Продолжить сохранение?"]];
				} else {
					throw new Exception("Амбулаторная карта с номером {$checkResult[0]['PersonAmbulatCard_Num']} уже создана для пациента {$checkResult[0]['Person_FIO']} (д/р {$checkResult[0]['Person_BirthDay']}). Для сохранения необходимо указать уникальный номер карты.");
				}
			}
		}
		$procedure = "p_PersonAmbulatCard_ins";
		if (isset($data["PersonAmbulatCard_id"]) && $data["PersonAmbulatCard_id"] > 0) {
			$procedure = "p_PersonAmbulatCard_upd";
		}
		$selectString = "
			personambulatcard_id as \"PersonAmbulatCard_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    server_id := :Server_id,
			    personambulatcard_id := :PersonAmbulatCard_id,
			    person_id := :Person_id,
			    lpu_id := :Lpu_id,
			    personambulatcard_num := :PersonAmbulatCard_Num,
			    personambulatcard_begdate := tzgetdate(),
			    personambulatcard_enddate := :PersonAmbulatCard_endDate,
			    personambulatcard_closecause := :PersonAmbulatCard_CloseCause,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"Server_id" => (isset($data["Server_id"]) ? $data["Server_id"] : $data["Lpu_id"]),
			"PersonAmbulatCard_id" => ((isset($data["PersonAmbulatCard_id"]) && $data["PersonAmbulatCard_id"] > 0) ? $data["PersonAmbulatCard_id"] : NULL),
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"],
			"PersonAmbulatCard_Num" => $data["PersonAmbulatCard_Num"],
			"PersonAmbulatCard_CloseCause" => (isset($data["PersonAmbulatCard_CloseCause"])) ? $data["PersonAmbulatCard_CloseCause"] : NULL,
			"PersonAmbulatCard_endDate" => (isset($data["PersonAmbulatCard_endDate"])) ? $data["PersonAmbulatCard_endDate"] : NULL,
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение результатов измерения массы пациента)");
		}
		$resp = $result->result("array");
		if (!empty($res[0]["Error_Msg"])) {
			throw new Exception($res[0]["Error_Msg"], $res[0]["Error_Code"]);
		}
		if (!empty($resp[0]["PersonAmbulatCard_id"])) {
			$query = "
				select pc.LpuAttachType_id
				from
					PersonCard pc
					inner join v_PersonAmbulatCardLink PACL on PACL.PersonCard_id = pc.PersonCard_id
				where pc.Person_id = :Person_id
				  and PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id
				limit 1
			";
			$queryParams = [
				"PersonAmbulatCard_id" => $resp[0]["PersonAmbulatCard_id"],
				"Person_id" => $data["Person_id"]
			];
			$data["LpuAttachType_id"] = $this->getFirstResultFromQuery($query, $queryParams);
			if (empty($data["LpuAttachType_id"])) {
				$data["LpuAttachType_id"] = 1;
			}
			// надо обновить номер в прикреплении
			$query = "
				UPDATE PersonCard
				SET PersonCard_Code = :PersonCard_Code
				WHERE Person_id = :Person_id
				  and PersonCard_id in (
				    select PACL.PersonCard_id
				    from v_PersonAmbulatCardLink PACL
				    where PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id
				  );
			";
			$queryParams = [
				"PersonAmbulatCard_id" => $resp[0]["PersonAmbulatCard_id"],
				"LpuAttachType_id" => $data["LpuAttachType_id"],
				"PersonCard_Code" => $data["PersonAmbulatCard_Num"],
				"Person_id" => $data["Person_id"]
			];
			$this->db->query($query, $queryParams);
			$query = "select * from xp_update_personcardstate(person_id := :Person_id, lpuattachtype_id := :LpuAttachType_id);";
			$this->db->query($query, $queryParams);
		}
		return $resp;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function checkPersonSex($data)
	{
		if ($data["AmbulatCardType_id"] == 2) {
			$query = "select Sex_id as \"Sex_id\" from v_PersonState where Person_id=:Person_id limit 1";
			$params = ["Person_id" => $data["Person_id"]];
			$response = $this->getFirstRowFromQuery($query, $params);
			if ($response["Sex_id"] != 2) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	private function checkUniqCard($data)
	{
		$filterArray = [
			"PAC.PersonAmbulatCard_Num = :PersonAmbulatCard_Num",
			"PAC.Lpu_id = :Lpu_id"
		];
		if (isset($data["PersonAmbulatCard_id"]) && $data["PersonAmbulatCard_id"] > 0) {
			$filterArray[] = "PAC.PersonAmbulatCard_id != :PersonAmbulatCard_id";
		}
		if (getRegionNick() != "ufa") {
			$filterArray[] = "(PAC.PersonAmbulatCard_endDate is null or PAC.PersonAmbulatCard_endDate <= tzgetdate())";
		}
		$filterString = implode(" and ", $filterArray);
		$query = "
			select
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
				coalesce(PS.Person_Surname, '')||' '||coalesce(PS.Person_FirName, '')||' '||coalesce(PS.Person_SecName, '') as \"Person_FIO\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"
			from
				v_PersonAmbulatCard PAC
				left join v_PersonState PS on PS.Person_id = PAC.Person_id
			where {$filterString}
			limit 1
		";
		/**@var CI_DB_result $response */
		$response = $this->db->query($query, $data);
		if (!is_object($response)) {
			return false;
		}
		$res = $response->result("array");
		return (is_array($res) && count($res) > 0) ? $res : false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getPersonAmbulatCardLocatList($data)
	{
		$query = "
			select
			-- select
				PACL.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PACL.PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\",
				to_char(PACL.PersonAmbulatCardLocat_begDate, '{$this->dateTimeForm120}') as \"PersonAmbulatCardLocat_begDate\",
				ACLT.AmbulatCardLocatType_Name as \"AmbulatCardLocatType\",
				MSF.Person_Fio as \"FIO\",
				post.PostMed_Name as \"MedStaffFact\",
				PACL.PersonAmbulatCardLocat_Desc as \"PersonAmbulatCardLocat_Desc\",
				PACL.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				0 as \"isSave\"
			-- end select
			from
			-- from
				v_PersonAmbulatCardLocat PACL
				left join AmbulatCardLocattype ACLT on PACL.AmbulatCardLocatType_id =ACLT.AmbulatCardLocatType_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PACL.MedStaffFact_id
				left join v_PostMed post on MSF.Post_id = post.PostMed_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = PACL.LpuBuilding_id
			-- end from
			where
			-- where
				PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id
			-- end where
			order by
			-- order by
			PACL.PersonAmbulatCardLocat_id
			-- end order by
		";
		$queryParams = ["PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"]];
		$response = [];
		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);
		if (!is_object($get_count_result)) {
			return false;
		}
		$response["totalCount"] = $get_count_result->result("array");
		$response["totalCount"] = $response["totalCount"][0]["cnt"];
		if ($data["start"] >= 0 && $data["limit"] >= 0) {
			$limit_query = getLimitSQLPH($query, $data["start"], $data["limit"]);
			$result = $this->db->query($limit_query, $queryParams);
		} else {
			$result = $this->db->query($query, $queryParams);
		}
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		if (!is_array($res)) {
			return false;
		}
		if ($data["start"] == 0 && count($res) < $data["limit"]) {
			$response["data"] = $res;
			$response["totalCount"] = count($res);
		} else {
			$response["data"] = $res;
			$get_count_query = getCountSQLPH($query);
			$get_count_result = $this->db->query($get_count_query, $queryParams);
			if (!is_object($get_count_result)) {
				return false;
			}
			$response["totalCount"] = $get_count_result->result("array");
			$response["totalCount"] = $response["totalCount"][0]["cnt"];
		}
		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonAmbulatCardLocat($data)
	{
		$procedure = "p_PersonAmbulatCardLocat_ins";
		if (isset($data['PersonAmbulatCardLocat_id']) && $data['PersonAmbulatCardLocat_id'] > 0) {
			$procedure = "p_PersonAmbulatCardLocat_upd";
		}
		$selectString = "
			personambulatcardlocat_id as \"PersonAmbulatCardLocat_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    server_id := :Server_id,
			    personambulatcardlocat_id := :PersonAmbulatCardLocat_id,
			    personambulatcard_id := :PersonAmbulatCard_id,
			    personambulatcardlocat_begdate := :PersonAmbulatCardLocat_begDate,
			    ambulatcardlocattype_id := :AmbulatCardLocatType_id,
			    personambulatcardlocat_otherlocat := :PersonAmbulatCardLocat_OtherLocat,
			    medstafffact_id := :MedStaffFact_id,
			    personambulatcardlocat_desc := :PersonAmbulatCardLocat_Desc,
			    lpubuilding_id := :LpuBuilding_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"Server_id" => $data["Server_id"],
			"PersonAmbulatCardLocat_id" => ((isset($data["PersonAmbulatCardLocat_id"]) && $data["PersonAmbulatCardLocat_id"] > 0) ? $data["PersonAmbulatCardLocat_id"] : NULL),
			"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"],
			"AmbulatCardLocatType_id" => (isset($data["AmbulatCardLocatType_id"]) ? $data["AmbulatCardLocatType_id"] : 1),
			"MedStaffFact_id" => ((isset($data["MedStaffFact_id"]) && $data["MedStaffFact_id"] > 0) ? $data["MedStaffFact_id"] : NULL),
			"PersonAmbulatCardLocat_begDate" => ((isset($data["PersonAmbulatCardLocat_begD"]) && isset($data["PersonAmbulatCardLocat_begT"])) ? $data["PersonAmbulatCardLocat_begD"] . " " . $data["PersonAmbulatCardLocat_begT"] : date("Y-m-d H:i")),
			"PersonAmbulatCardLocat_Desc" => (isset($data["PersonAmbulatCardLocat_Desc"]) ? $data["PersonAmbulatCardLocat_Desc"] : NULL),
			"pmUser_id" => $data["pmUser_id"],
			"PersonAmbulatCardLocat_OtherLocat" => (isset($data["PersonAmbulatCardLocat_OtherLocat"]) ? $data["PersonAmbulatCardLocat_OtherLocat"] : NULL),
			"LpuBuilding_id" => (!empty($data["LpuBuilding_id"]) ? $data["LpuBuilding_id"] : NULL)
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса БД");
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deletePersonAmbulatCardLocat($data)
	{
		if (empty($data["PersonAmbulatCardLocat_id"])) {
			return false;
		}
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_personambulatcardlocat_del(personambulatcardlocat_id := :PersonAmbulatCardLocat_id);
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result("array");
			if (isset($result[0]) && empty($result[0]["Error_Msg"])) {
				return ["success" => true];
			}
		}
		return ["success" => false];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonAmbulatCard($data)
	{
		$sql = "
		    select 
			    PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
			    PAC.Person_id as \"Person_id\",
			    PAC.Server_id as \"Server_id\",
			    PAC.Lpu_id as \"Lpu_id\",
				to_char(PAC.PersonAmbulatCard_endDate, '{$this->dateTimeForm104}') as \"PersonAmbulatCard_endDate\",
			    PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
				PAC.PersonAmbulatCard_CloseCause as \"PersonAmbulatCard_CloseCause\",
			    ps.Person_Surname||' '||left(ps.Person_FirName, 1)||' '||left(ps.Person_secName, 1) as \"PersonFIO\"
		    from
		    	v_PersonAmbulatCard PAC
		    	left join v_PersonState ps on ps.Person_id=PAC.Person_id
		    where PersonAmbulatCard_id = :PersonAmbulatCard_id
	    ";
		$params = ["PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonAmbulatCardLocat($data)
	{
		$sql = "
		    select
				ACL.PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\",
				ACL.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				to_char(ACL.PersonAmbulatCardLocat_begDate, '{$this->dateTimeForm104}') as \"PersonAmbulatCardLocat_begD\",
				to_char(ACL.PersonAmbulatCardLocat_begDate, '{$this->dateTimeForm108}') as \"PersonAmbulatCardLocat_begT\",
				ACL.AmbulatCardLocatType_id as \"AmbulatCardLocatType_id\",
				ACL.PersonAmbulatCardLocat_OtherLocat as \"PersonAmbulatCardLocat_OtherLocat\",
				ACL.MedStaffFact_id as \"MedStaffFact_id\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				ACL.LpuBuilding_id as \"LpuBuilding_id\",
				ACL.PersonAmbulatCardLocat_Desc as \"PersonAmbulatCardLocat_Desc\"
			from
				PersonAmbulatCardLocat ACL
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id=ACL.MedStaffFact_id
		    where ACL.PersonAmbulatCardLocat_id=:PersonAmbulatCardLocat_id
	    ";
		$params = ["PersonAmbulatCardLocat_id" => $data["PersonAmbulatCardLocat_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение данных об амбулаторных картах человека. Метод для API.
	 * @param $data
	 * @return array|false
	 */
	function loadPersonAmbulatCardListForAPI($data)
	{
		$filter = "";
		$queryParams = ["Person_id" => $data["Person_id"]];
		if (!empty($data["Lpu_id"])) {
			$filter .= " and PAC.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$sql = "
			select
				PAC.Lpu_id as \"Lpu_id\",
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
				to_char(PAC.PersonAmbulatCard_begDate, '{$this->dateTimeForm120}') as \"PersonAmbulatCard_begDate\",
				to_char(PAC.PersonAmbulatCard_endDate, '{$this->dateTimeForm120}') as \"PersonAmbulatCard_endDate\",
				PC.LpuAttachType_id as \"LpuAttachType_id\"
			from
				v_PersonAmbulatCard PAC
				left join v_PersonAmbulatCardLink PACL on PACL.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				left join v_PersonCard PC on PC.PersonCard_id = PACL.PersonCard_id 
			where PAC.Person_id = :Person_id {$filter}
		";
		return $this->queryResult($sql, $queryParams);
	}

	/**
	 * Получение данных об амбулаторных картах человека. Метод для API.
	 * @param $data
	 * @return array|false
	 */
	function getPersonAmbulatCardForAPI($data)
	{
		$filterArray = [];
		$queryParams = [];
		if (!empty($data["Lpu_id"])) {
			$filterArray[] = "PAC.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["Person_id"])) {
			$filterArray[] = "PAC.Person_id = :Person_id";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		if (!empty($data["PersonAmbulatCard_Num"])) {
			$filterArray[] = "PAC.PersonAmbulatCard_Num = :PersonAmbulatCard_Num";
			$queryParams["PersonAmbulatCard_Num"] = $data["PersonAmbulatCard_Num"];
		}
		if (!empty($data["Date_DT"])) {
			$filterArray[] = "
				(
					(cast(:Date_DT as date) between PAC.PersonAmbulatCard_begDate and PAC.PersonAmbulatCard_endDate)  or 
					(PAC.PersonAmbulatCard_begDate <= cast(:Date_DT as date) and PAC.PersonAmbulatCard_endDate is null)
				)
			";
			$queryParams["Date_DT"] = $data["Date_DT"];
		}
		$filterString = implode(" and ", $filterArray);
		$sql = "
			select
				PAC.Lpu_id as \"Lpu_id\",
				PAC.Person_id as \"Person_id\",
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
				to_char(PAC.PersonAmbulatCard_begDate, '{$this->dateTimeForm120}') as \"PersonAmbulatCard_begDate\",
				to_char(PAC.PersonAmbulatCard_endDate, '{$this->dateTimeForm120}') as \"PersonAmbulatCard_endDate\",
				PAC.PersonAmbulatCard_CloseCause as \"PersonAmbulatCard_CloseCause\"
			from
				v_PersonAmbulatCard PAC
			where (1=1) {$filterString}
		";
		return $this->queryResult($sql, $queryParams);
	}

	/**
	 * Cохранение движения карты из рабочего места сотрудника картохранилища (доставить карту)
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonAmbulatDeliverCard($data)
	{
		//создаем движение
		$data["Server_id"] = $data["session"]["server_id"];
		$data["PersonAmbulatCardLocat_Desc"] = "отметка о доставке АК сотрудником картохранилища";
		$res = $this->savePersonAmbulatCardLocat($data);
		if (!empty($res[0]["Error_Msg"])) {
			throw new Exception($res[0]["Error_Msg"], $res[0]["Error_Code"]);
		}
		if (empty($res[0]["PersonAmbulatCardLocat_id"])) {
			throw new Exception("Ошибка при создании движения карты");
		}
		if (!empty($data["AmbulatCardRequest_id"]) && !empty($data["AmbulatCardRequestStatus_id"]) && $data["AmbulatCardRequestStatus_id"] == 1) {
			// если был запрос от врача, то убираем запрос
			$query = "
				select
					ambulatcardrequest_id as \"AmbulatCardRequest_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_ambulatcardrequest_upd(
				    ambulatcardrequest_id := :AmbulatCardRequest_id,
				    personambulatcard_id := :PersonAmbulatCard_id,
				    ambulatcardrequeststatus_id := :AmbulatCardRequestStatus_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"AmbulatCardRequestStatus_id" => 2,
				"AmbulatCardRequest_id" => $data["AmbulatCardRequest_id"],
				"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при запросе амб. карты у картохранилища");
			}
		}
		return $res;
	}

	/**
	 * Привязываем амбулаторную карту к бирке
	 * @param $data
	 * @return array|bool
	 */
	function savePersonAmbulatCardInTimetableGraf($data)
	{
		if (empty($data["TimetableGraf_id"])) {
			return false;
		}
		// записываем в TimeTableGraf ИД амбулаторной карты без использования p_TimetableGraf_upd, т.к. нам не нужно изменять историю бирки
		$swUpdateParams = [
			"TimetableGraf_id" => $data["TimetableGraf_id"],
			"pmUser_id" => $data["pmUser_id"],
			"PersonAmbulatCard_id" => (!empty($data["PersonAmbulatCard_id"])) ? $data["PersonAmbulatCard_id"] : null
		];
		$res = $this->swUpdate("TimetableGraf", $swUpdateParams, true);
		return $res;
	}

	/**
	 * Прикрепление амбулаторной карты к картохранилищу
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveAttachmentAmbulatoryCardToCardStore($data)
	{
		if (empty($data["PersonAmbulatCard_id"])) {
			return false;
		}
		$currentDate = date("Y-m-d H:i:s");
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		//получим полседнюю запись прикрепления
		$lastRecord = $this->getAttachmentAmbulatoryCardToCardStore($data, true);
		//узнаем расположение службы пользователя
		$serviceLocation = $this->getServiceLocationsUser([
			"MedPersonal_id" => $data["session"]["medpersonal_id"],
			"pmUser_id" => $data["pmUser_id"],
			"Lpu_id" => $data["Lpu_id"]
		]);
		if (!empty($serviceLocation[0]["LpuBuilding_id"])) {
			$queryParams["LpuBuilding_id"] = $serviceLocation[0]["LpuBuilding_id"];
		}
		if ($lastRecord && is_array($lastRecord) && count($lastRecord) > 0) {
			if ($serviceLocation && is_array($serviceLocation) && count($serviceLocation) > 0) {
				if ($lastRecord[0]["LpuBuilding_id"] == $serviceLocation[0]["LpuBuilding_id"] && $serviceLocation[0]["Lpu_id"] == $lastRecord[0]["Lpu_id"]) {
					//если данные совпадают, то ничего не меняем
					return true;
				}
				//иначе закрываем предыдущее прикрепление
				$params = $queryParams;
				$params["AmbulatCardLpuBuilding_id"] = $lastRecord[0]["AmbulatCardLpuBuilding_id"];
				$params["AmbulatCardLpuBuilding_endDate"] = $currentDate;
				$this->closeAttachmentAmbulatoryCardToCardStore($params);
			}
		}
		$result = $this->addAttachmentAmbulatoryCardToCardStore($queryParams);
		return $result;
	}

	/**
	 * Получение прикреплений амбулаторной карты к картохранилищу
	 * @param $data
	 * @param bool $lastRecord
	 * @return array|bool|false
	 */
	function getAttachmentAmbulatoryCardToCardStore($data, $lastRecord = false)
	{
		if (empty($data["PersonAmbulatCard_id"]) || empty($data["Lpu_id"])) {
			return false;
		}
		$limit1 = ($lastRecord) ? "limit 1" : "";
		$sql = "
			select
				ACLB.AmbulatCardLpuBuilding_id as \"AmbulatCardLpuBuilding_id\",
				coalesce(ACLB.LpuBuilding_id, null) as \"LpuBuilding_id\",
				ACLB.Lpu_id as \"Lpu_id\",
				ACLB.AmbulatCardLpuBuilding_begDate as \"AmbulatCardLpuBuilding_begDate\",
				ACLB.AmbulatCardLpuBuilding_endDate as \"AmbulatCardLpuBuilding_endDate\"
			from v_AmbulatCardLpuBuilding ACLB
			where ACLB.Lpu_id = :Lpu_id
			  and ACLB.PersonAmbulatCard_id = :PersonAmbulatCard_id
			order by ACLB.AmbulatCardLpuBuilding_begDate desc
			{$limit1}
		";
		$result = $this->queryResult($sql, $data);
		return $result;
	}

	/**
	 * Удаление прикреплений амбулаторной карты к картохранилищу по PersonAmbulatCard_id
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function deleteAttachmentAmbulatoryCardToCardStore($data)
	{
		if (empty($data["PersonAmbulatCard_id"])) return false;
		$PersonID = null;
		$query = "
			select Person_id as \"Person_id\"
			from v_PersonAmbulatCard
			where PersonAmbulatCard_id = :PersonAmbulatCard_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		$res = $result->result("array");
		if (!empty($res[0]["Person_id"])) {
			$PersonID = $res[0]["Person_id"];
		}
		$res = $this->getAttachmentAmbulatoryCardToCardStore($data);
		if (count($res) == 0) {
			return true;
		}
		foreach ($res as $val) {
			if (!empty($val["AmbulatCardLpuBuilding_id"])) {
				$params = ["AmbulatCardLpuBuilding_id" => $val["AmbulatCardLpuBuilding_id"]];
				$this->deleteAmbulatCardLpuBuildingID($params);
			}
		}
		if ($PersonID) {
			// смотрим предшествующую карту
			$sql = "
				select
					PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
					PAC.Person_id as \"Person_id\",
					PAC.PersonAmbulatCard_endDate as \"PersonAmbulatCard_endDate\",
					PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
					ACLB.AmbulatCardLpuBuilding_id as \"AmbulatCardLpuBuilding_id\",
					ACLB.AmbulatCardLpuBuilding_endDate as \"AmbulatCardLpuBuilding_endDate\"
				from
					v_PersonAmbulatCard PAC
					left join v_AmbulatCardLpuBuilding ACLB on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				where tzgetdate() between PAC.PersonAmbulatCard_begDate and coalesce(PAC.PersonAmbulatCard_endDate, tzgetdate())
				  and PAC.Person_id = :Person_id and PAC.Lpu_id = :Lpu_id
				  and PAC.PersonAmbulatCard_id <> :PersonAmbulatCard_id
				order by PAC.PersonAmbulatCard_id desc
				limit 1
			";
			$sqlParams = [
				"Lpu_id" => $data["Lpu_id"],
				"Person_id" => $PersonID,
				"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"]
			];
			$res = $this->queryResult($sql, $sqlParams);
			if (!empty($res[0]["PersonAmbulatCard_id"])) {
				//если существует прикреплене этой АК и она закрыта, то открываем ее
				$attachmentAC = $this->getAttachmentAmbulatoryCardToCardStore([
					"Lpu_id" => $data["Lpu_id"],
					"Person_id" => $PersonID,
					"PersonAmbulatCard_id" => $res[0]["PersonAmbulatCard_id"]
				], true);
				if (!empty($attachmentAC[0]["AmbulatCardLpuBuilding_id"]) && !empty($data["AmbulatCardLpuBuilding_endDate"])) {
					$data["AmbulatCardLpuBuilding_id"] = $attachmentAC[0]["AmbulatCardLpuBuilding_id"];
					$data["PersonAmbulatCard_id"] = $res[0]["PersonAmbulatCard_id"];
					$this->saveAttachmentAmbulatoryCardToCardStore($data);
				}
			}
		}
		return true;
	}

	/**
	 * Удаление прикрепления амбулаторной карты к картохранилищу по AmbulatCardLpuBuilding_id
	 * @param $data
	 * @return array|bool
	 */
	function deleteAmbulatCardLpuBuildingID($data)
	{
		if (empty($data["AmbulatCardLpuBuilding_id"])) {
			return false;
		}
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_ambulatcardlpubuilding_del(ambulatcardlpubuilding_id := :AmbulatCardLpuBuilding_id);
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return ["success" => false];
		}
		$result = $result->result("array");
		return [
			"success" =>
				(isset($result[0]) && empty($result[0]["Error_Msg"]))
					? true
					: false
		];
	}

	/**
	 * Добавление записи прикрепления амбулаторной карты к картохранилищу
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function addAttachmentAmbulatoryCardToCardStore($data)
	{
		if (empty($data["PersonAmbulatCard_id"]) || empty($data["Lpu_id"]) || empty($data["pmUser_id"])) {
			return false;
		}
		$currentDate = date("Y-m-d H:i:s");
		$procedure = (empty($data["AmbulatCardLpuBuilding_id"])) ? "p_AmbulatCardLpuBuilding_ins" : "p_AmbulatCardLpuBuilding_upd";
		$selectString = "
			ambulatcardlpubuilding_id as \"AmbulatCardLpuBuilding_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    ambulatcardlpubuilding_id := :AmbulatCardLpuBuilding_id,
			    lpu_id := :Lpu_id,
			    personambulatcard_id := :PersonAmbulatCard_id,
			    lpubuilding_id := :LpuBuilding_id,
			    ambulatcardlpubuilding_begdate := :AmbulatCardLpuBuilding_begDate,
			    ambulatcardlpubuilding_enddate := :AmbulatCardLpuBuilding_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"AmbulatCardLpuBuilding_id" => (!empty($data["AmbulatCardLpuBuilding_id"])) ? $data["AmbulatCardLpuBuilding_id"] : null,
			"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"],
			"LpuBuilding_id" => (!empty($data["LpuBuilding_id"])) ? $data["LpuBuilding_id"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $data["pmUser_id"],
			"AmbulatCardLpuBuilding_begDate" => (empty($data["AmbulatCardLpuBuilding_begDate"])) ? $currentDate : $data["AmbulatCardLpuBuilding_begDate"],
			"AmbulatCardLpuBuilding_endDate" => (empty($data["AmbulatCardLpuBuilding_endDate"])) ? null : $data["AmbulatCardLpuBuilding_endDate"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при сохранении прикрепления амб. карты к картохранилищу");
		}
		return $result->result("array");
	}

	/**
	 * Закрыть прикрепление амбулаторной карты к картохранилищу
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function closeAttachmentAmbulatoryCardToCardStore($data)
	{
		if (empty($data["AmbulatCardLpuBuilding_id"])) {
			return false;
		}
		$query = "
			select
				ambulatcardlpubuilding_id as \"AmbulatCardLpuBuilding_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_ambulatcardlpubuilding_upd(
			    ambulatcardlpubuilding_id := :AmbulatCardLpuBuilding_id,
			    ambulatcardlpubuilding_enddate := :AmbulatCardLpuBuilding_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"AmbulatCardLpuBuilding_id" => (!empty($data["AmbulatCardLpuBuilding_id"])) ? $data["AmbulatCardLpuBuilding_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
			"AmbulatCardLpuBuilding_endDate" => (empty($data["AmbulatCardLpuBuilding_endDate"])) ? null : $data["AmbulatCardLpuBuilding_endDate"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при закрытии прикрепления амб. карты к картохранилищу");
		}
		return $result->result("array");
	}

	/**
	 * Получение идентификатора подразделения LpuBuilding_id по идентификатору службы(MedService_id)
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function getLpuBuildingByMedServiceId($data)
	{
		$MedService_id =
			(!empty($data["MedService_id"]))
				? $data["MedService_id"]
				: (!empty($data["session"]["CurMedService_id"]) ? $data["session"]["CurMedService_id"] : false);
		if (!$MedService_id) {
			throw new Exception("Не определен идентификатор службы");
		}
		$queryParams = ["MedService_id" => $MedService_id];
		$sql = "
			select coalesce(MS.LpuBuilding_id, 0) as \"LpuBuilding_id\"
			from v_MedService MS
			where MS.MedService_id = :MedService_id
		";
		$result = $this->queryResult($sql, $queryParams);
		return $result;
	}

	/**
	 * Форма поиска амбулаторных карт
	 * @param $data
	 * @return array|bool
	 */
	function loadInformationAmbulatoryCards($data)
	{
		if (empty($data["Lpu_id"])) {
			return false;
		}
		$params = ["Lpu_id" => $data["Lpu_id"]];
		$filterArray = [
			"PAC.Person_id is not null",
			"p.Person_Surname is not null",
			"PAC.Lpu_id = :Lpu_id"
		];
		if (!empty($data["Person_SurName"])) {
			$filterArray[] = "p.Person_SurName ilike (:Person_SurName||'%')";
			$params["Person_SurName"] = rtrim($data["Person_SurName"]);
		}
		if (!empty($data["Person_id"])) {
			$filterArray[] = "p.Person_id = :Person_id";
			$params["Person_id"] = rtrim($data["Person_id"]);
		}
		if (!empty($data["PersonAmbulatCard_id"])) {
			$filterArray[] = "PAC.PersonAmbulatCard_id = :PersonAmbulatCard_id";
			$params["PersonAmbulatCard_id"] = rtrim($data["PersonAmbulatCard_id"]);
		}
		if (!empty($data["Person_FirName"])) {
			$filterArray[] = "p.Person_FirName ilike (:Person_FirName||'%')";
			$params["Person_FirName"] = rtrim($data["Person_FirName"]);
		}
		if (!empty($data["Person_SecName"])) {
			$filterArray[] = "p.Person_SecName ilike (:Person_SecName||'%')";
			$params["Person_SecName"] = rtrim($data["Person_SecName"]);
		}
		if (!empty($data["Person_Birthday"])) {
			$filterArray[] = "p.Person_BirthDay = :Person_BirthDay";
			$params["Person_BirthDay"] = $data["Person_Birthday"];
		}
		if (!empty($data["LpuBuilding_id"])) {
			$filterArray[] = "ACLB_LB.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		if (!empty($data["MedStaffFact_id"])) {
			$filterArray[] = "ambulatCard.MedStaffFact_id = :MedStaffFact_id";
			$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		}
		if (!empty($data["MedPersonal_id"])) {
			$filterArray[] = "MSF.MedPersonal_id = :MedPersonal_id";
			$params["MedPersonal_id"] = $data["MedPersonal_id"];
		}
		if (!empty($data["AmbulatCardLocatType_id"])) {
			$filterArray[] = "ambulatCard.AmbulatCardLocatType_id = :AmbulatCardLocatType_id";
			$params["AmbulatCardLocatType_id"] = $data["AmbulatCardLocatType_id"];
		}
		if (!empty($data["field_numberCard"])) {
			$filterArray[] = "PAC.PersonAmbulatCard_Num = :PersonAmbulatCard_Num";
			$params["PersonAmbulatCard_Num"] = $data["field_numberCard"];
		}
		if (!empty($data["CardAttachment"])) {
			$filterArray[] = "ACLB.Lpu_id = :Lpu_id";
			if (!empty($data["AttachmentLpuBuilding_id"])) {
				$params["AttachmentLpuBuilding_id"] = $data["AttachmentLpuBuilding_id"];
				switch ($data["CardAttachment"]) {
					case "currentStorage":
						//Текущее картохранилище (карты, прикреплённых к текущему картохранилищу)
						$filterArray[] = "ACLB.LpuBuilding_id = :AttachmentLpuBuilding_id";
						break;
					case "otherStorage":
						//Другие картохранилища 
						//карт, которые прикреплены к другому картохранилищу текущей МО, 
						//но в качестве местонахождения имеют текущее картохранилище (подразделение) либо врача текущего подразделения
						$filterArray[] = "ACLB.LpuBuilding_id <> :AttachmentLpuBuilding_id ";
						$filterArray[] = "(ambulatCard.LpuBuilding_id = :AttachmentLpuBuilding_id or ambulatCard.MSF_LpuBuilding_id = :AttachmentLpuBuilding_id)";

						break;
					case "allStorage":
						//Все (карты, прикреплённых к текущему картохранилищу)
						//карт, прикреплённых к текущему картохранилищу;
						//карт, которые прикреплены к другому картохранилищу текущей МО, 
						//но в качестве местонахождения имеют текущее картохранилище (подразделение) либо врача текущего подразделения.
						$filterArray[] = "(ACLB.LpuBuilding_id = :AttachmentLpuBuilding_id or ambulatCard.MSF_LpuBuilding_id = :AttachmentLpuBuilding_id)";
						break;
				}
			}
		}
		if (!empty($data["CardIsOpenClosed"])) {
			if ($data["CardIsOpenClosed"] == "openCard") {
				//Открытые карты
				$filterArray[] = "tzgetdate() between PAC.PersonAmbulatCard_begDate and coalesce(PAC.PersonAmbulatCard_endDate, tzgetdate()) ";
			} elseif ($data["CardIsOpenClosed"] == "closeCard") {
				//Закрытые карты
				$filterArray[] = "tzgetdate() not between PAC.PersonAmbulatCard_begDate and coalesce(PAC.PersonAmbulatCard_endDate, tzgetdate()) ";
			}
		}
		$filterString = implode(" and ", $filterArray);
		$query = "
			select
				-- select
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				rtrim(rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '')) as \"Person_FIO\",
				rtrim(p.Person_Surname) as \"Person_SurName\",
				rtrim(p.Person_Firname) as \"Person_FirName\",
				rtrim(p.Person_Secname) as \"Person_SecName\",
				to_char(p.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_Birthday\",
				coalesce(pcMain.Lpu_Nick, '') as \"MainLpu_Nick\",
				coalesce(pcMain.LpuRegion_Name, '') as \"LpuRegion_Name\",
				coalesce(pcGin.Lpu_Nick, '') as \"GinLpu_Nick\",
				coalesce(pcStom.Lpu_Nick, '') as \"StomLpu_Nick\",
				ACLB.LpuBuilding_id as \"LpuBuilding_id\",
				ACLB_LB.LpuBuilding_Name as \"AttachmentLpuBuilding_Name\",
				coalesce(RTrim(PAC.PersonAmbulatCard_Num), '') as \"PersonAmbulatCard_Num\",
				ambulatCard.MapLocation as \"Location_Amb_Cards\", 
				to_char(ambulatCard.PersonAmbulatCardLocat_begDate, '{$this->dateTimeForm104}') as \"PersonAmbulatCardLocat_begDate\",
				ambulatCard.FIO as \"EmployeeFIO\",
				ambulatCard.MedStaffFact as \"MedStaffFact\",
				ambulatCard.PersonAmbulatCardLocat_Desc as \"PersonAmbulatCardLocat_Desc\",
				cast(dbo.tzgetdate() as date) as \"curDT\"
				-- end select
			from
				-- from
				v_PersonAmbulatCard PAC
				left join v_AmbulatCardLpuBuilding ACLB on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				left join v_LpuBuilding ACLB_LB on ACLB_LB.LpuBuilding_id = ACLB.LpuBuilding_id --прикрепление к подразделению
				left join v_PersonState p on p.Person_id = PAC.Person_id
				left join lateral (
					select
						vpc.PersonCard_id, 
						vpc.PersonCard_Code, 
						vpc.LpuAttachType_id, 
						vpc.LpuRegion_id, 
						rtrim(l.Lpu_Nick) as Lpu_Nick,
						LR.LpuRegion_Name||coalesce(' ('||LpuRegion_Descr||')', '') as LpuRegion_Name
					from
					    v_PersonCard VPC
					    inner join v_PersonAmbulatCardLink PACLink on PACLink.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id and VPC.PersonCard_id = PACLink.PersonCard_id
						left join v_LpuRegion LR on LR.LpuRegion_id = vpc.LpuRegion_id
						left join v_Lpu l on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id = 1
					  and Person_id = p.Person_id
					order by VPC.PersonCard_id desc
					limit 1
				) as pcMain on true --МО прикрепления (осн.)
				left join lateral (
					select VPC.PersonCard_id, LpuAttachType_id, rtrim(l.Lpu_Nick) as Lpu_Nick
					from
					    v_PersonCard VPC
					    inner join v_PersonAmbulatCardLink PACLink on PACLink.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id and VPC.PersonCard_id = PACLink.PersonCard_id
						left join v_Lpu l on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id = 2
					  and VPC.PersonCard_id = PACLink.PersonCard_id
					  and Person_id = p.Person_id
					order by VPC.PersonCard_id desc
				    limit 1
				) as pcGin on true --МО прикрепления (гин.)
				left join lateral (
					select VPC.PersonCard_id, LpuAttachType_id, rtrim(l.Lpu_Nick) as Lpu_Nick
					from
					     v_PersonCard VPC
						 inner join v_PersonAmbulatCardLink PACLink on PACLink.PersonAmbulatCard_id =PAC.PersonAmbulatCard_id and VPC.PersonCard_id = PACLink.PersonCard_id
						 left join v_Lpu l on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id = 3
					  and VPC.PersonCard_id = PACLink.PersonCard_id
					  and Person_id = p.Person_id
					order by VPC.PersonCard_id desc
					limit 1
				) as pcStom on true ----МО прикрепления (стом.)	
				left join lateral (
					select
						PAC.PersonAmbulatCard_id,
						PAC.PersonAmbulatCard_Num,
						PACL.LpuBuilding_id,
						LB.LpuBuilding_Name,
						PACL.PersonAmbulatCardLocat_begDate,
						MSF.MedStaffFact_id,
						MSF.Person_Fio as FIO,
						MSF.LpuBuilding_id AS MSF_LpuBuilding_id,
						post.PostMed_Name as MedStaffFact,
						PACL.AmbulatCardLocatType_id,
						PACL.PersonAmbulatCardLocat_Desc,
						coalesce(ACLT.AmbulatCardLocatType_Name, '')||coalesce(', '||LB.LpuBuilding_Name, '')||coalesce(', '||MSF.Person_Fio, '') as MapLocation
					from v_PersonAmbulatCardLocat PACL
						left join AmbulatCardLocatType ACLT on PACL.AmbulatCardLocatType_id = ACLT.AmbulatCardLocatType_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = PACL.LpuBuilding_id
						left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PACL.MedStaffFact_id
						left join v_PostMed post on MSF.Post_id = post.PostMed_id
					where PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
					order by PACL.PersonAmbulatCardLocat_begDate desc
					limit 1
				) as ambulatCard on true -- амбулаторная карта
				-- end from
			where
				-- where
				{$filterString}
				-- end where
			order by
				-- order by
				p.Person_Surname
				-- end order by
		";
		return $this->getPagingResponse($query, $params, $data["start"], $data["limit"], true);
	}

	/**
	 * Запросить или отклонить врачем амб. карту у картохранилища
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setAmbulatCardRequest($data)
	{
		if (empty($data["TimetableGraf_id"]) || empty($data["PersonAmbulatCard_id"])) {
			return false;
		}
		$procedure = "p_AmbulatCardRequest_ins";
		$queryParams = [];
		if (!empty($data["AmbulatCardRequest_id"])) {
			$procedure = "p_AmbulatCardRequest_upd";
			$queryParams["AmbulatCardRequest_id"] = $data["AmbulatCardRequest_id"];
		} else {
			$queryParams["AmbulatCardRequest_id"] = null;
		}
		$queryParams["TimetableGraf_id"] = $data["TimetableGraf_id"];
		$queryParams["PersonAmbulatCard_id"] = $data["PersonAmbulatCard_id"];
		$queryParams["AmbulatCardRequestStatus_id"] = (!empty($data["AmbulatCardRequestStatus_id"])) ? $data["AmbulatCardRequestStatus_id"] : null;
		$queryParams["MedStaffFact_id"] = (!empty($data["MedStaffFact_id"])) ? $data["MedStaffFact_id"] : null;
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		if ($queryParams['AmbulatCardRequestStatus_id'] == 2) {
			if (empty($queryParams['MedStaffFact_id'])) {
				throw new Exception("Не указан врач");
			}
			//если пришел запрос врача, что карта на приеме, то проверим последнее движение карты
			//и если в последнем движении нет этого врача, то создадим движение амб.карты
			$sql = "
				select
					PACL.PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\",
					PACL.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
					PACL.MedStaffFact_id as \"MedStaffFact_id\",
					PACL.LpuBuilding_id as \"LpuBuilding_id\",
					PACL.AmbulatCardLocatType_id as \"AmbulatCardLocatType_id\",
					PACL.PersonAmbulatCardLocat_begDate as \"PersonAmbulatCardLocat_begDate\",
					PACL.PersonAmbulatCardLocat_Desc as \"PersonAmbulatCardLocat_Desc\",
					PACL.PersonAmbulatCardLocat_OtherLocat as \"PersonAmbulatCardLocat_OtherLocat\"
				from v_PersonAmbulatCardLocat PACL
				where PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id
				order by PACL.PersonAmbulatCardLocat_begDate desc
				limit 1
			";
			$resLocatCard = $this->queryResult($sql, $queryParams);
			$locatCard = (is_array($resLocatCard) && count($resLocatCard) > 0) ? $resLocatCard[0] : false;
			//AmbulatCardLocatType_id
			if (!$locatCard || ($locatCard["AmbulatCardLocatType_id"] != 2 || $locatCard["MedStaffFact_id"] != $queryParams["MedStaffFact_id"])) {
				//создаем движение амбулаторной карты
				$locatCard["pmUser_id"] = $data["pmUser_id"];
				$locatCard["Server_id"] = $data["Server_id"];
				$locatCard["MedStaffFact_id"] = $queryParams["MedStaffFact_id"];
				$locatCard["PersonAmbulatCard_id"] = $queryParams["PersonAmbulatCard_id"];
				$locatCard["PersonAmbulatCardLocat_id"] = (!empty($locatCard["PersonAmbulatCardLocat_id"])) ? $locatCard["PersonAmbulatCardLocat_id"] : null;
				$locatCard["AmbulatCardLocatType_id"] = 2;
				$locatCard["PersonAmbulatCardLocat_Desc"] = "врач отметил, что карта у него на приёме ";
				$this->savePersonAmbulatCardLocat($locatCard);
			}
			//проверим статус запроса
			$sql = "
				select
					ACR.AmbulatCardRequestStatus_id as \"AmbulatCardRequestStatus_id\",
					ACR.TimeTableGraf_id as \"TimeTableGraf_id\"
				from v_AmbulatCardRequest ACR
				where ACR.PersonAmbulatCard_id = :PersonAmbulatCard_id
				  and ACR.TimetableGraf_id = :TimetableGraf_id
				order by ACR.AmbulatCardRequest_updDT desc
				limit 1
			";
			$resStatusCard = $this->queryResult($sql, $queryParams);
			$statusCard = (is_array($resStatusCard) && count($resStatusCard) > 0) ? $resStatusCard[0] : false;
			if (!$statusCard || $statusCard["AmbulatCardRequestStatus_id"] != 1) {
				//если запроса не было, то ничего дальше не делаем
				return [["success" => true]];
			}
		}
		$selectString = "
			ambulatcardrequest_id as \"AmbulatCardRequest_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    ambulatcardrequest_id := :AmbulatCardRequest_id,
			    timetablegraf_id := :TimetableGraf_id,
			    personambulatcard_id := :PersonAmbulatCard_id,
			    ambulatcardrequeststatus_id := :AmbulatCardRequestStatus_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при запросе амб. карты у картохранилища");
		}
		if ($queryParams["AmbulatCardRequestStatus_id"] == 1) {
			//привязываем амбулаторную карту к бирке
			$this->savePersonAmbulatCardInTimetableGraf($data);
		}
		return $result->result("array");
	}

	/**
	 * Расположения службы регистратуры пол-ки в МО, к которому имеет доступ пользователь, выполнивший действие
	 * @param $data
	 * @return array|bool|false
	 */
	function getServiceLocationsUser($data)
	{
		if (empty($data["pmUser_id"]) || empty($data["Lpu_id"])) {
			return false;
		}
		$queryParams = [
			"MedPersonal_id" => $data["MedPersonal_id"],
			"pmUser_id" => $data["pmUser_id"],
			"Lpu_id" => $data["Lpu_id"],
			"MedServiceType_SysNick" => "regpol",
			"pmUserCacheGroup_Code" => "StorageCard"
		];
		$sql = "
			select distinct
				MS.MedService_id as \"MedService_id\",
				MS.Lpu_id as \"Lpu_id\",
				coalesce(MS.LpuBuilding_id, null) as \"LpuBuilding_id\"
			from
				v_MedService MS
				left join v_MedServiceType MST on MS.MedServiceType_id = MST.MedServiceType_id
				left join v_MedServiceMedPersonal MSMP on MS.MedService_id = MSMP.MedService_id
				left join pmUserCache puc on puc.MedPersonal_id = MSMP.MedPersonal_id
				left join pmUserCacheGroupLink PCGL on PCGL.pmUserCache_id = puc.PMUser_id
				left join pmUserCacheGroup PCG on PCG.pmUserCacheGroup_id = PCGL.pmUserCacheGroup_id
			where MST.MedServiceType_SysNick = :MedServiceType_SysNick
			  and MS.Lpu_id = :Lpu_id
			  and puc.Lpu_id = :Lpu_id
			  and MSMP.MedPersonal_id = :MedPersonal_id
			  and PCG.pmUserCacheGroup_Code = :pmUserCacheGroup_Code
		";
		$result = $this->queryResult($sql, $queryParams);
		return $result;
	}

	/**
	 * Получаем информацию об прикреплении амбулаторной карты последнего существующего прикрепления пациента
	 * @param $data
	 * @return array|bool|false
	 */
	function getAttachmentAmbulatoryCardToPersonCard($data)
	{
		if (empty($data["Lpu_id"]) && (empty($data["PersonCard_id"]) || empty($data["Person_id"]))) {
			return false;
		}
		$whereArray = [
			"PC.Lpu_id = :Lpu_id",
			"PC.PersonCard_endDate is null"
		];
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		if (!empty($data["PersonCard_id"])) {
			$whereArray[] = "PC.PersonCard_id = :PersonCard_id";
			$queryParams["PersonCard_id"] = $data["PersonCard_id"];
		}
		if (!empty($data["Person_id"])) {
			$whereArray[] = "PC.Person_id = :Person_id";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		if (!empty($data["LpuAttachType_id"])) {
			$whereArray[] = "PC.LpuAttachType_id = :LpuAttachType_id";
			$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
		} else {
			$whereArray[] = "LAT.LpuAttachType_SysNick in ('main', 'gin', 'stom')";
		}
		$whereString = implode(" and ", $whereArray);
		$sql = "
			select
				PC.PersonCard_id as \"PersonCard_id\",
				PC.PersonCard_Code as \"PersonCard_Code\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				PC.PersonCard_begDate as \"PersonCard_begDate\",
				PC.PersonCard_endDate as \"PersonCard_endDate\",
				coalesce(PAC.PersonAmbulatCard_Num, PACnum.PersonAmbulatCard_Num) as \"PersonAmbulatCard_Num\",
				coalesce(PAC.PersonAmbulatCard_id, PACnum.PersonAmbulatCard_id) as \"PersonAmbulatCard_id\",
				ACLB.AmbulatCardLpuBuilding_id as \"AmbulatCardLpuBuilding_id\",
				ACLB.AmbulatCardLpuBuilding_begDate as \"AmbulatCardLpuBuilding_begDate\",
				ACLB.AmbulatCardLpuBuilding_endDate as \"AmbulatCardLpuBuilding_endDate\",
				PC.Person_id as \"Person_id\"
			from v_PersonCard PC
				left join LpuAttachType LAT on PC.LpuAttachType_id = LAT.LpuAttachType_id
				left join PersonAmbulatCardLink PACL on PACL.PersonCard_id = PC.PersonCard_id
				left join PersonAmbulatCard PAC on PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
				left join PersonAmbulatCard PACnum on PACnum.PersonAmbulatCard_Num = PC.PersonCard_Code and PC.Lpu_id = PACnum.Lpu_id and PACnum.Person_id = PC.Person_id
				left join lateral (
					SELECT AmbulatCardLpuBuilding_id, ACLB.AmbulatCardLpuBuilding_begDate, ACLB.AmbulatCardLpuBuilding_endDate
					FROM v_AmbulatCardLpuBuilding ACLB
					where ACLB.PersonAmbulatCard_id = coalesce(PAC.PersonAmbulatCard_id, PACnum.PersonAmbulatCard_id) 
					order by ACLB.AmbulatCardLpuBuilding_begDate desc
					limit 1
				) as ACLB on true
			where {$whereString}
			order by PC.PersonCard_begDate desc
			limit 1
		";
		$result = $this->queryResult($sql, $queryParams);
		return $result;
	}
}