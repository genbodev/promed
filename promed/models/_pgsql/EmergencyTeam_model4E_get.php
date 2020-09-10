<?php


class EmergencyTeam_model4E_get
{
	/**
	 * @param $data
	 * @param $where
	 * @param $params
	 * @return array|bool
	 */
	public static function getAutoFinishVigil(EmergencyTeam_model4E $callObject, $data, $where, $params)
	{
		$where[] = "coalesce(ETD.EmergencyTeamDuty_IsCancelledClose, 1) = 1";
		$where[] = "coalesce(ETD.EmergencyTeamDuty_isClose, 1) = 1";
		$where[] = "coalesce(ETD.EmergencyTeamDuty_isComesToWork, 1) = 2";
		$where[] = "ETD.EmergencyTeamDuty_DTFinish <= tzgetdate()";
		$where[] = "coalesce(ETD.EmergencyTeamDuty_factEndWorkDT::text, '1') = '1'";
		$where[] = "datediff('hour', ETD.EmergencyTeamDuty_DTFinish, tzgetdate()) < 24";
		$where[] = "ETS.EmergencyTeamStatus_Code in (13, 19, 21)";
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
            select
                ET.EmergencyTeam_id as \"EmergencyTeam_id\",
                ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
                ETD.EmergencyTeamDuty_isClose as \"EmergencyTeamDuty_isClose\",
                case when coalesce(ETD.EmergencyTeamDuty_isClose, 1) = 1 then '' else 'true' end as \"closed\",
                ETD.EmergencyTeamDuty_isComesToWork as \"EmergencyTeamDuty_isComesToWork\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') AS \"EmergencyTeamDuty_DTStart\",
                to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') AS \"EmergencyTeamDuty_DTFinish\",
                to_char(ETD.EmergencyTeamDuty_factToWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factToWorkDT\",
				to_char(ETD.EmergencyTeamDuty_factEndWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factEndWorkDT\",
				ETD.EmergencyTeamDuty_Comm as \"EmergencyTeamDuty_Comm\",
				ETD.EmergencyTeamDuty_ChangeComm as \"EmergencyTeamDuty_ChangeComm\"
			from
			    v_EmergencyTeam as ET
			    left join v_EmergencyTeamStatus AS ETS on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
				left join v_EmergencyTeamDuty AS ETD on ETD.EmergencyTeam_id=ET.EmergencyTeam_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = ET.LpuBuilding_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param $data
	 * @param $where
	 * @param $params
	 * @return array|bool
	 */
	public static function getAutoStartVigil(EmergencyTeam_model4E $callObject, $data, $where, $params)
	{
		$where[] = "coalesce(ETD.EmergencyTeamDuty_IsCancelledStart, 1) = 1";
		$where[] = "coalesce(ETD.EmergencyTeamDuty_isClose, 1) = 1";
		$where[] = "coalesce(ETD.EmergencyTeamDuty_isComesToWork, 1) = 1";
		$where[] = "ETD.EmergencyTeamDuty_DTStart <= tzgetdate()";
		$where[] = "coalesce(ETD.EmergencyTeamDuty_factToWorkDT::text, '1') = '1'";
		$where[] = "datediff('hour', ETD.EmergencyTeamDuty_DTStart, tzgetdate()) < 24";
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
            select
                ET.EmergencyTeam_id as \"EmergencyTeam_id\",
                ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
                ETD.EmergencyTeamDuty_isClose as \"EmergencyTeamDuty_isClose\",
                case when coalesce(ETD.EmergencyTeamDuty_isClose, 1) = 1 then '' else 'true' end as \"closed\",
                ETD.EmergencyTeamDuty_isComesToWork as \"EmergencyTeamDuty_isComesToWork\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') AS \"EmergencyTeamDuty_DTStart\",
                to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') AS \"EmergencyTeamDuty_DTFinish\",
                to_char(ETD.EmergencyTeamDuty_factToWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factToWorkDT\",
				to_char(ETD.EmergencyTeamDuty_factEndWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factEndWorkDT\",
				ETD.EmergencyTeamDuty_Comm as \"EmergencyTeamDuty_Comm\",
				ETD.EmergencyTeamDuty_ChangeComm as \"EmergencyTeamDuty_ChangeComm\"
			from
			    v_EmergencyTeam as ET
			    left join v_EmergencyTeamStatus AS ETS ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				left join v_EmergencyTeamDuty AS ETD ON( ETD.EmergencyTeam_id=ET.EmergencyTeam_id)
				left join v_LpuBuilding LB on LB.LpuBuilding_id = ET.LpuBuilding_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param $data
	 * @return array|CI_DB_result
	 */
	public static function getCallOnEmergencyTeam(EmergencyTeam_model4E $callObject, $data)
	{
		$query = "
			select
				ETSH.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				CCC.CmpCallCard_id as \"CmpCallCard_id\"
			from
				v_EmergencyTeamStatusHistory as ETSH
				LEFT JOIN v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id
				left join lateral (
					select
						c.CmpCallCard_id,
						c.CmpCallCardStatusType_id
					from
						v_CmpCallCardTeamsAssignmentHistory cctah
						left join v_CmpCallCard c on cctah.CmpCallCard_id = c.CmpCallCard_id
					where cctah.EmergencyTeam_id = ETSH.EmergencyTeam_id
					  and c.CmpCallCardStatusType_id = 2
					order by CmpCallCardTeamsAssignmentHistory_id
					limit 1
				) as CCC on true
			where ETSH.EmergencyTeam_id = :EmergencyTeam_id
			  and CCC.CmpCallCard_id = ETSH.CmpCallCard_id
			order by ETSH.EmergencyTeamStatusHistory_insDT desc
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		$result = $result->result("array");
		return $result;
	}

	/**
	 * Получение параметров для проигрывания трека виалон
	 * @param $data
	 * @return array
	 */
	public static function getCmpCallCardTrackPlayParams(EmergencyTeam_model4E $callObject, $data)
	{
		$rules = [
			["field" => "CmpCallCard_id", "label" => "Идентификатор вызова", "rules" => "required", "type" => "id"],
			["field" => "EmergencyTeam_id", "label" => "Идентификатор бригады", "rules" => "required", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err, false);
		if (!empty($err)) {
			return $err;
		}
		return [
			[
				"startTime" => $callObject->_getCmpCallCardStartTime($queryParams),
				"endTime" => $callObject->_getCmpCallCardEndTime($queryParams),
				"wialonId" => $callObject->_getEmergencyTeamGeoserviveTransportId($queryParams["EmergencyTeam_id"])
			]
		];
	}

	/**
	 * Получение времени назначения статуса карты вызова "принято"
	 * @param EmergencyTeam_model4E $callObject
	 * @param $param
	 * @return mixed|bool
	 */
	public static function _getCmpCallCardStartTime(EmergencyTeam_model4E $callObject, $param)
	{
		if (!isset($param["CmpCallCard_id"]) || !isset($param["EmergencyTeam_id"])) {
			return false;
		}
		$sql = "
			select to_char(CCCS.CmpCallCardStatus_insDT, '{$callObject->dateTimeForm120}') as \"EventDT\"
	        from
	        	v_CmpCallCardStatus CCCS
	        	left join v_CmpCallCardStatusType CCCST on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CCCS.CmpCallCard_id
	        where CCCS.CmpCallCard_id = :CmpCallCard_id
			  and CCCST.CmpCallCardStatusType_code = 2
			  and CCC.EmergencyTeam_id = :EmergencyTeam_id
	      	order by CCCS.CmpCallCardStatus_insDT desc
			limit 1
	    ";
		$queryParams = [
			"CmpCallCard_id" => $param["CmpCallCard_id"],
			"EmergencyTeam_id" => $param["EmergencyTeam_id"]
		];
		$result = $callObject->queryList($sql, $queryParams);
		return ($result) ? $result[0] : false;
	}

	/**
	 * Получение времени завершения вызова
	 * @param $param
	 * @return mixed|bool
	 */
	public static function _getCmpCallCardEndTime(EmergencyTeam_model4E $callObject, $param)
	{
		if (!isset($param["CmpCallCard_id"]) || !isset($param["EmergencyTeam_id"])) {
			return false;
		}
		$sql = "
			select to_char(CmpCallCard_Tisp, '{$callObject->dateTimeForm120}') as \"EventDT\"
	        from v_CmpCallCard
	        where CmpCallCard_Tisp is not null
	          and CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		$queryParams = [
			"CmpCallCard_id" => $param["CmpCallCard_id"],
			"EmergencyTeam_id" => $param["EmergencyTeam_id"]
		];
		$result = $callObject->queryList($sql, $queryParams);
		if (!$result) {
			$result = $callObject->queryList("select to_char(tzgetdate(), '{$callObject->dateTimeForm120}') as \"EventDT\"");
		}
		return ($result) ? $result[0] : false;
	}

	/**
	 * Получает идентификатор автомобиля в Виалон по идентификатору бригады
	 * @param EmergencyTeam_model4E $callObject
	 * @param null $EmergencyTeam_id
	 * @return mixed|bool
	 */
	public static function _getEmergencyTeamGeoserviveTransportId(EmergencyTeam_model4E $callObject, $EmergencyTeam_id = null)
	{
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams();
		$query_params = ["EmergencyTeam_id" => $EmergencyTeam_id];
		$query = "
			select GTR.{$GTR["GeoserviceTransport_id_field"]} as \"GeoserviceTransport_id\"
			from {$GTR["GeoserviceTransportRel_object"]} as GTR
			where GTR.{$GTR["EmergencyTeam_id_field"]} = :EmergencyTeam_id
			limit 1
		 ";
		$GeoserviceTransport_id = $callObject->queryList($query, $query_params);
		return ($GeoserviceTransport_id == false) ? false : $GeoserviceTransport_id[0];
	}

	/**
	 * получение идентификатор автомобиля в Виалон
	 * @param EmergencyTeam_model4E $callObject
	 * @param $param
	 * @return bool
	 */
	public static function getEmergencyTeamGeoserviveTransportId(EmergencyTeam_model4E $callObject, $param)
	{
		if (empty($param)) {
			return false;
		}
		return $callObject->_getEmergencyTeamGeoserviveTransportId($param);
	}

	/**
	 * Получает время передачи вызова на бригаду
	 * @param null $CmpCallCard_id
	 * @return mixed|bool
	 */
	public static function _getCmpCallCardPassToEmergencyTeamTimestamp(EmergencyTeam_model4E $callObject, $CmpCallCard_id = null)
	{
		$query_params = [
			"CmpCallCard_id" => $CmpCallCard_id,
			"CmpCallCardStatusType_code" => 2
		];
		$query = "
	        select datediff('ss','1970-01-01'::date, CCCS.CmpCallCardStatus_insDT) as \"CmpCallCardStatus_insDTStamp\"
	        from
	        	v_CmpCallCardStatus CCCS
	        	left join v_CmpCallCardStatusType CCCST on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
	        where CCCS.CmpCallCard_id = :CmpCallCard_id
	          and CCCST.CmpCallCardStatusType_code = :CmpCallCardStatusType_code 
	      	order by CCCS.CmpCallCardStatus_insDT desc
			limit 1
	    ";
		$timestamp = $callObject->queryList($query, $query_params);
		return ($timestamp == false) ? false : $timestamp[0];
	}

	/**
	 * Получает время окончания вызова бригадой
	 * @param EmergencyTeam_model4E $callObject
	 * @param null $CmpCallCard_id
	 * @param null $EmergencyTeam_id
	 * @return int
	 */
	public static function _getCmpCallCardEndTimestamp(EmergencyTeam_model4E $callObject, $CmpCallCard_id = null, $EmergencyTeam_id = null)
	{
		$query_params = [
			"CmpCallCard_id" => $CmpCallCard_id,
			"EmergencyTeam_id" => $EmergencyTeam_id,
			"EmergencyTeamStatus_code" => 4
		];
		$query = "
	        select datediff('ss','1970-01-01'::date, ETSH.EmergencyTeamStatusHistory_insDT) as \"EmergencyTeamStatusHistory_insDTStamp\"
	        from
	        	v_EmergencyTeamStatusHistory ETSH
	        	left join v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id
	        where ETSH.CmpCallCard_id = :CmpCallCard_id
	          and ETSH.EmergencyTeam_id = :EmergencyTeam_id
	          and  ETS.EmergencyTeamStatus_code = :EmergencyTeamStatus_code
	      	order by ETSH.EmergencyTeamStatusHistory_insDT desc
			limit 1
	    ";
		$timestamp = $callObject->queryList($query, $query_params);
		return ($timestamp == false) ? date_timestamp_get(date_create()) : $timestamp[0];
	}

	/**
	 * Метод получения идентификатора укладки по идентификатору движения укладки
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function _getDrugPackByDrugPackMove(EmergencyTeam_model4E $callObject, $data)
	{
		$rules = [
			["field" => "EmergencyTeamDrugPackMove_id", "label" => "Идентификатор подстанции", "rules" => "required", "type" => "int"]
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$query = "
			select ETDPM.EmergencyTeamDrugPack_id as \"EmergencyTeamDrugPack_id\"
			from v_EmergencyTeamDrugPackMove ETDPM
			where ETDPM.EmergencyTeamDrugPackMove_id = :EmergencyTeamDrugPackMove_id
		";
		return $callObject->queryResult($query, $queryParams);

	}

	/**
	 * Метод получения идентификатора смены по идентификатору бригады
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function _getEmergencyTeamDutyIdByEmergencyTeamId(EmergencyTeam_model4E $callObject, $data)
	{
		$rules = [
			["field" => "EmergencyTeam_id", "label" => "Идентификатор подстанции", "rules" => "required", "type" => "int"]
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$queryExistDT = "
			select ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\"
			from v_EmergencyTeamDuty ETD
			where ETD.EmergencyTeam_id = :EmergencyTeam_id
			limit 1
		";
		return $callObject->queryResult($queryExistDT, $queryParams);
	}

	/**
	 * Возвращает количество кол-во врачей, бригад, вызовов СМП для арма ЦМК
	 * Для списка подчиненных подстанций СМП
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCountsTeamsCallsAndDocsARMCenterDisaster(EmergencyTeam_model4E $callObject, $data)
	{
		$where = ["LB.LpuBuildingType_id = 27"];
		$params = [];
		$user = pmAuthUser::find($_SESSION['login']);
		if (!empty ($data["Lpu_ids"])) {
			$where[] = "L.Lpu_id in (" . $data["Lpu_ids"] . ")";
		} else {
			$Lpu_ids = $callObject->getSelectedLpuId();
			if (!$Lpu_ids) {
				return false;
			}
			$Lpu_idsString = implode(",", $Lpu_ids);
			$where[] = "L.Lpu_id in ({$Lpu_idsString})";
		}
		$whereString = (count($where) != 0)?"where ".implode(" and ", $where):"";
		$sql = "
			with LpuBuildingList as (
				select
					LB.LpuBuilding_id as \"LpuBuilding_id\",
					L.Lpu_Nick||' / '||coalesce(LB.LpuBuilding_Nick, LB.LpuBuilding_Name) as \"LpuBuilding_Name\"
				from 
					v_LpuBuilding LB
					inner join v_Lpu L on L.Lpu_id = LB.Lpu_id
				{$whereString}
			),
			CountEmergencyTeams as (
				select
					selEt.LpuBuilding_id as \"LpuBuilding_id\",
					COUNT(selET.EmergencyTeam_id) as \"CountEmergencyTeams\",
					sum(case when selETS.EmergencyTeamStatus_Code in (13, 21, 36) then 1 else 0 end) as \"TeamsStatusFree_Count\",
					sum(case when selETS.EmergencyTeamStatus_Code in (8, 9, 23) then 1 else 0 end) as \"TeamsStatusUnaccepted_Count\",
					sum(case when selETS.EmergencyTeamStatus_Code in (8, 9, 23, 13, 21, 36) then 0 else 1 end) as \"TeamsStatusDuty_Count\",
					sum((case when (selET.EmergencyTeam_HeadShift is not null and selET.EmergencyTeam_HeadShift != 0) then 1 else 0 end)+
						(case when (selET.EmergencyTeam_HeadShift2 is not null and selET.EmergencyTeam_HeadShift2 != 0) then 1 else 0 end)) as \"Team_HeadShiftCount\",
					sum((case when (selET.EmergencyTeam_Assistant1 is not null and selET.EmergencyTeam_Assistant1 != 0) then 1 else 0 end)+
						(case when (selET.EmergencyTeam_Assistant2 is not null and selET.EmergencyTeam_Assistant2 != 0) then 1 else 0 end)) as \"Team_AssistantCount\"
				from
					v_EmergencyTeam selET
					left join v_EmergencyTeamDuty selETD on selETD.EmergencyTeam_id = selET.EmergencyTeam_id
					left join v_EmergencyTeamStatus selETS on selETS.EmergencyTeamStatus_id=selET.EmergencyTeamStatus_id
				where selET.LpuBuilding_id in (select LpuBuilding_id from LpuBuildingList)
				  and coalesce(selET.EmergencyTeam_isTemplate, 1) = 1
				  and selETD.EmergencyTeamDuty_isComesToWork = 2
				  and tzgetdate() BETWEEN selETD.EmergencyTeamDuty_factToWorkDT and selETD.EmergencyTeamDuty_DTFinish
				group by selEt.LpuBuilding_id
			),
			CountCmpCallCards as (
				select
					selCC.LpuBuilding_id,
					COUNT(CmpCallCard_id) as CountCmpCallCards,
					sum(case when COALESCE(selCC.EmergencyTeam_id,0)!=0 then 1 else 0 end) as CallsAccepted,
					sum(case when COALESCE(selCC.EmergencyTeam_id,0)=0 then 1 else 0 end) as CallsNoAccepted
				from
					v_CmpCallCard selCC
					left join v_CmpCallCardStatusType CCCS on selCC.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
				where
					selCC.LpuBuilding_id in (select LpuBuilding_id from LpuBuildingList)
					and coalesce(selCC.CmpCallCard_IsReceivedInPPD, 1) != 2
					and coalesce(selCC.CmpCallCard_IsOpen, 1) = 2
					and selCC.CmpCallType_id = 2
					and CCCS.CmpCallCardStatusType_Code in (1, 2, 7, 8, 10)
				group by selCC.LpuBuilding_id
			)
			select
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				coalesce(CountEmergencyTeams, 0) as \"CountEmergencyTeams\",
				coalesce(TeamsStatusFree_Count, 0) as \"TeamsStatusFree_Count\",
				coalesce(TeamsStatusUnaccepted_Count, 0) as \"TeamsStatusUnaccepted_Count\",
				coalesce(TeamsStatusDuty_Count, 0) as \"TeamsStatusDuty_Count\",
				coalesce(Team_HeadShiftCount, 0) as \"Team_HeadShiftCount\",
				coalesce(Team_AssistantCount, 0) as \"Team_AssistantCount\",
				coalesce(CountCmpCallCards, 0) as \"CountCmpCallCards\",
				coalesce(CallsAccepted, 0) as \"CallsAccepted\",
				coalesce(CallsNoAccepted, 0) as \"CallsNoAccepted\"
			from
				LpuBuildingList LB
				left join CountEmergencyTeams cet on cet.LpuBuilding_id = LB.LpuBuilding_id
				left join CountCmpCallCards cccc on cccc.LpuBuilding_id = LB.LpuBuilding_id
		";
		if (isset($_GET["dbg"]) && $_GET["dbg"] == "1") {
			var_dump(getDebugSQL($sql, $params));
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * Номер телефона по умолчанию для наряда
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDefaultPhoneNumber(EmergencyTeam_model4E $callObject, $data)
	{
		$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
		$operDpt = $callObject->CmpCallCard_model4E->getOperDepartament($data);
		if (!empty($operDpt["LpuBuilding_pid"])) {
			$callObject->load->model("LpuStructure_model", "LpuStructure");
			$LpuBuildingData = $callObject->LpuStructure->getLpuBuildingData(["LpuBuilding_id" => $operDpt["LpuBuilding_pid"]]);
			if (!empty($LpuBuildingData[0]["LpuBuildingSmsType_id"])) {
				if ($LpuBuildingData[0]["LpuBuildingSmsType_id"] == 1) {
					$callObject->load->model("LpuPassport_model", "LpuPassport");
					$MedProductCardData = $callObject->LpuPassport->loadMedProductCardData(["MedProductCard_id" => $data["MedProductCard_id"]]);
					if (count($MedProductCardData) > 0) {
						return [$MedProductCardData[0]["MedProductCard_Phone"]];
					}
				} else {
					$query = "
						select MSF.MedStaffFactCache_PhoneNum
						from v_MedStaffFactCache MSF
						where MSF.MedStaffFact_id = :MedStaffFact_id
					";
					$queryParams = ["MedStaffFact_id" => $data["EmergencyTeam_HeadShift"]];
					$item = $callObject->getFirstRowFromQuery($query, $queryParams);
					if (is_array($item) && count($item) > 0) {
						return [$item["MedStaffFactCache_PhoneNum"]];
					}
				}
			}
		}
		return false;
	}

	/**
	 * Возвращает массив ID МО выбранных в АРМ
	 * @return array|bool
	 */
	public static function getSelectedLpuId()
	{
		$user = pmAuthUser::find($_SESSION["login"]);
		$settings = unserialize($user->settings);
		return (isset($settings["lpuWorkAccess"]) && is_array($settings["lpuWorkAccess"]) && $settings["lpuWorkAccess"][0] != "") ? $settings["lpuWorkAccess"] : false;
	}

	/**
	 * Информация о бригаде в АРМе ЦМК
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 */
	public static function getEmergencyTeam(EmergencyTeam_model4E $callObject, $data)
	{
		$params["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
		$query = "
			select
				MPH1.Person_Fio as \"EmergencyTeam_HeadShift\",
				MPH2.Person_Fio as \"EmergencyTeam_HeadShift2\",
				MPD.Person_Fio as \"EmergencyTeam_Driver\",
				MPA.Person_Fio as \"EmergencyTeam_Assistant1\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				count.countCateredCmpCallCards as \"countCateredCmpCallCards\",
				CC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				lpuHid.Lpu_Nick as \"LpuHid_Nick\"
			from
				v_EmergencyTeam ET
				left join v_LpuBuilding LB on LB.LpuBuilding_id =  ET.LpuBuilding_id
				left join v_EmergencyTeamStatus as ETS on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id 
				left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				left join v_MedPersonal MPH1 on MPH1.MedPersonal_id = ET.EmergencyTeam_HeadShift  and MPH1.Lpu_id = LB.Lpu_id
				left join v_MedPersonal MPH2 on MPH2.MedPersonal_id = ET.EmergencyTeam_HeadShift2 and MPH2.Lpu_id = LB.Lpu_id
				left join v_MedPersonal MPD on MPD.MedPersonal_id = ET.EmergencyTeam_Driver and MPD.Lpu_id = LB.Lpu_id
				left join v_MedPersonal MPA on MPA.MedPersonal_id = ET.EmergencyTeam_Assistant1 and MPA.Lpu_id = LB.Lpu_id
				left join lateral (
					select COUNT(*) as countCateredCmpCallCards
					from v_CmpCallCard ccc
					where ccc.EmergencyTeam_id = :EmergencyTeam_id
					  and CmpCallCardStatusType_id in (4, 6, 7, 8, 18)
				) as count on true
				left join lateral (
					select C2.* 
					from v_CmpCallCard C2
					where C2.EmergencyTeam_id = ET.EmergencyTeam_id 
					  and C2.CmpCallCardStatusType_id = 2
					order by C2.CmpCallCard_updDT desc
				    limit 1
				) as CC on true
				left join v_Lpu lpuHid on lpuHid.Lpu_id = CC.Lpu_hid
			where ET.EmergencyTeam_id = :EmergencyTeam_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return $result->result_array();
	}

	/**
	 * Получение суммарное количество медикамента по всем операциям списания и зачисления
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getDrugCountFromDrugPackMoveByDrugPackId(EmergencyTeam_model4E $callObject, $data)
	{
		$rules = [
			["field" => "EmergencyTeamDrugPack_id", "label" => "Укладка", "rules" => "required", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$query = "
			select
				coalesce(sum(ETDPM.EmergencyTeamDrugPackMove_Quantity), 0) as \"DrugCount\",
				ETDP.EmergencyTeam_id as \"EmergencyTeam_id\",
				ETDP.Drug_id as \"Drug_id\"
			from
				v_EmergencyTeamDrugPackMove ETDPM
				left join v_EmergencyTeamDrugPack ETDP on ETDP.EmergencyTeamDrugPack_id = ETDPM.EmergencyTeamDrugPack_id
			where ETDPM.EmergencyTeamDrugPack_id = :EmergencyTeamDrugPack_id::int8
			group by
				ETDP.EmergencyTeam_id,
				ETDP.Drug_id
		";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * Метод получения идентификатора движения медикамента для наряда СМП по идентификатору строки учетного документа списания
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getEmergencyTeamDrugPackMoveIdByDocumentUcStr(EmergencyTeam_model4E $callObject, $data)
	{
		$rules = [
			["field" => "DocumentUcStr_id", "label" => "Идентификатор строки документа", "rules" => "required", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$query = "
			select
				ETDPM.EmergencyTeamDrugPackMove_id as \"EmergencyTeamDrugPackMove_id\",
				ETDPM.EmergencyTeamDrugPackMove_Quantity as \"EmergencyTeamDrugPackMove_Quantity\",
				ETDPM.EmergencyTeamDrugPack_id as \"EmergencyTeamDrugPack_id\",
				ETDP.EmergencyTeam_id as \"EmergencyTeam_id\",
				ETDP.Drug_id as \"Drug_id\"
			from
				v_EmergencyTeamDrugPackMove ETDPM
				left join v_EmergencyTeamDrugPack ETDP on ETDP.EmergencyTeamDrugPack_id = ETDPM.EmergencyTeamDrugPack_id
			where ETDPM.DocumentUcStr_id = :DocumentUcStr_id
		";
		return $callObject->queryResult($query, $queryParams);

	}

	/**
	 * Получение записи остатков по идентификатору бригады и медикамента
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getDrugPackByDrugAndEmergencyTeam(EmergencyTeam_model4E $callObject, $data)
	{
		$rules = [
			["field" => "EmergencyTeam_id", "label" => "Бригада", "rules" => "required", "type" => "id"],
			["field" => "Drug_id", "label" => "Медикамент", "rules" => "required", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$query = "
			select ETDP.EmergencyTeamDrugPack_id as \"EmergencyTeamDrugPack_id\"
			from v_EmergencyTeamDrugPack ETDP
			where ETDP.EmergencyTeam_id = :EmergencyTeam_id
			  and ETDP.Drug_id = :Drug_id
		";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * Список бригад для комбобокса
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getEmergencyTeamCombo(EmergencyTeam_model4E $callObject, $data)
	{
		$query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				MP.Person_Fin as \"Person_Fin\"
			from
				v_EmergencyTeam  ET
				left join v_MedPersonal as MP on MP.MedPersonal_id = ET.EmergencyTeam_HeadShift
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * я не знаю что за функция, но от меня требуют ее описание
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getEmergencyTeamProposalLogic(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$filterArray = ["ETPL.Lpu_id = :Lpu_id"];
		if (!empty($data["EmergencyTeamProposalLogic_id"])) {
			$filterArray[] = "ETPL.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id";
		}
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray):"";
		$query = "
        	select
				ETPL.EmergencyTeamProposalLogic_id as \"EmergencyTeamProposalLogic_id\",
				ETPL.CmpReason_id as \"CmpReason_id\",
				ETPL.Sex_id as \"Sex_id\",
				CR.CmpReason_Code as \"CmpReason_Code\",
				coalesce(S.Sex_Name, 'Все') as \"Sex_Name\",
				coalesce(ETPL.EmergencyTeamProposalLogic_AgeFrom::varchar(10), '') as \"EmergencyTeamProposalLogic_AgeFrom\",
				coalesce(ETPL.EmergencyTeamProposalLogic_AgeTo::varchar(10), '') as \"EmergencyTeamProposalLogic_AgeTo\",
				Codes.Codes as \"EmergencyTeamProposalLogic_Sequence\"
			from
				v_EmergencyTeamProposalLogic ETPL
				left join v_Sex S on ETPL.Sex_id = S.Sex_id
				inner join v_CmpReason CR on ETPL.CmpReason_id = CR.CmpReason_id
				left join lateral (
					select distinct
					(
						select ETS2.EmergencyTeamSpec_Code||' '
						from
							v_EmergencyTeamProposalLogicRule ETPLR
							inner join v_EmergencyTeamSpec ETS2 on ETPLR.EmergencyTeamSpec_id = ETS2.EmergencyTeamSpec_id
						where ETPLR.EmergencyTeamProposalLogic_id = ETPL.EmergencyTeamProposalLogic_id
						order by ETPLR.EmergencyTeamProposalLogicRule_SequenceNum
					) AS Codes
				) as Codes on true
			{$whereString}
    	";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$arr = $result->result("array");
		return [
			"data" => $arr,
			"totalCount" => sizeof($arr)
		];
	}

	/**
	 * я не знаю что за функция, но от меня требуют ее описание
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getEmergencyTeamProposalLogicRuleSpecSequence(EmergencyTeam_model4E $callObject, $data)
	{
		if (!isset($data["EmergencyTeamProposalLogic_id"]) || $data["EmergencyTeamProposalLogic_id"] == 0) {
			$query = "
				select
					0 as \"EmergencyTeamProposalLogicRule_id\",
					ROW_NUMBER() OVER(ORDER BY ETS.EmergencyTeamSpec_id) as \"EmergencyTeamProposalLogicRule_SequenceNum\",
					ETS.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					ETS.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
					ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\"
				from v_EmergencyTeamSpec ETS
			";
		} else {
			$query = "
				select
					ETPLR.EmergencyTeamProposalLogicRule_id as \"EmergencyTeamProposalLogicRule_id\",
					ETPLR.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					ETPLR.EmergencyTeamProposalLogicRule_SequenceNum as \"EmergencyTeamProposalLogicRule_SequenceNum\",
					ETS.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
					ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\"
				from
					v_EmergencyTeamProposalLogicRule ETPLR
					inner join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = ETPLR.EmergencyTeamSpec_id
				where ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$arr = $result->result("array");
		return [
			"data" => $arr,
			"totalCount" => sizeof($arr)
		];
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 * @param EmergencyTeam_model4E $callObject
	 * @param $id
	 * @return mixed|bool
	 */
	public static function getEmergencyTeamStatusCodeById(EmergencyTeam_model4E $callObject, $id)
	{
		$sql = "
			select EmergencyTeamStatus_Code
			from v_EmergencyTeamStatus
			where EmergencyTeamStatus_id=:EmergencyTeamStatus_id
			limit 1
		";
		$sqlParams = ["EmergencyTeamStatus_id" => $id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->first_row("array");
		return $result["EmergencyTeamStatus_Code"];
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 * @param EmergencyTeam_model4E $callObject
	 * @param $code
	 * @return mixed|bool
	 */
	public static function getEmergencyTeamStatusIdByCode(EmergencyTeam_model4E $callObject, $code)
	{
		$sql = "
			select EmergencyTeamStatus_id
			from v_EmergencyTeamStatus
			where EmergencyTeamStatus_Code=:EmergencyTeamStatus_Code
			limit 1
		";
		$sqlParams = ["EmergencyTeamStatus_Code" => $code];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->first_row("array");
		return $result["EmergencyTeamStatus_id"];
	}

	/**
	 * Получаем список EmergencyTeam_TemplateName
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getEmergencyTeamTemplatesNames(EmergencyTeam_model4E $callObject, $data)
	{

		$where[] = "coalesce(ET.EmergencyTeam_isTemplate, 1) = 2";
		$where[] = $callObject->getNestedLpuBuildingsForRequests($data);
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
			select distinct
				ET.EmergencyTeam_TemplateName as \"EmergencyTeam_TemplateName\"
			from v_EmergencyTeam ET 
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение подчин. подстанций для запроса
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|string
	 * @throws Exception
	 */
	public static function getNestedLpuBuildingsForRequests(EmergencyTeam_model4E $callObject, $data)
	{
		// здесь мы получаем список доступных подстанций для работы из лдапа
		$user = pmAuthUser::find($_SESSION["login"]);
		$settings = @unserialize($user->settings);
		if (isset($settings["lpuBuildingsWorkAccess"]) && is_array($settings["lpuBuildingsWorkAccess"])) {
			$lpuBuildingsWorkAccess = $settings["lpuBuildingsWorkAccess"];
		}
		if (!(empty($lpuBuildingsWorkAccess))) {
			if ($lpuBuildingsWorkAccess[0] == "") {
				throw new Exception("Не настроен список доступных для работы подстанций");
			}
			// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
			$lpuBuildingIdList = $lpuBuildingsWorkAccess;
		} else {
			$callObject->load->model("CmpCallCard_Model4E", "CmpCallCard_Model4E");
			$smpUnitsNested = $callObject->CmpCallCard_model4E->loadSmpUnitsNested($data,
				in_array($_SESSION["region"]["nick"], ["ufa", "krym", "kz", "perm", "ekb", "astra"])
			);
			if ((empty($smpUnitsNested))) {
				throw new Exception("Не определена подстанция");
			}
			$lpuBuildingIdList = $smpUnitsNested;
		}
		return (!empty($lpuBuildingIdList)) ? "ET.LpuBuilding_id in (" . implode(",", $lpuBuildingIdList) . ")" : "1=0";
	}
}