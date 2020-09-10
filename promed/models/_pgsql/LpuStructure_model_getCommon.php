<?php

class LpuStructure_model_getCommon
{
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getAllowedMedServiceTypes(LpuStructure_model $callObject, $data)
	{
		$queryLpuType = "
			select L.LpuType_id as \"LpuType_id\"
			from v_Lpu_all L 
			where L.Lpu_id = :Lpu_id
		";
		$queryLpuTypeParams = [
			"Lpu_id" => $data["Lpu_id"]
		];
		$resultLpuType = $callObject->db->query($queryLpuType, $queryLpuTypeParams);
		if (!is_object($resultLpuType) || sizeof($resultLpuType->result("array")) < 1) {
			return false;
		}
		$resultLpuType = $resultLpuType->result("array");
		$additionalLpuTypeWhereClause = ($resultLpuType[0]["LpuType_id"] == 111) ? "OR COALESCE(LpuType_id,0) = :LpuType_id" : "";
		$query = "
			select MedServiceType_id as \"MedServiceType_id\"
			from v_MedServiceLevel 
			where MedServiceLevelType_id = :MedServiceLevelType_id
			  and (coalesce(LpuType_id, 0) = 0 $additionalLpuTypeWhereClause)
		";
		$queryParams = [
			"MedServiceLevelType_id" => $data["MedServiceLevelType_id"],
			"LpuType_id" => $resultLpuType[0]["LpuType_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resultArray = $result->result("array");
		$response = [];
		foreach ($resultArray as $array) {
			$response[] = $array["MedServiceType_id"];
		}
		return $response;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuStructureElementList(LpuStructure_model $callObject, $data)
	{
		$whereArray = [];
		$params = [];
		$union_arr = [];
		if (!empty($data["LpuSection_id"])) {
			$whereArray[] = "t.LpuSection_pid = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
			$level = "LpuSection";
		} elseif (!empty($data["LpuUnit_id"])) {
			$whereArray[] = "t.LpuUnit_id = :LpuUnit_id";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
			$level = "LpuUnit";
		} else if (!empty($data["LpuBuilding_id"])) {
			$whereArray[] = "t.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
			$level = "LpuBuilding";
		} else if (!empty($data["Lpu_id"])) {
			$whereArray[] = "t.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
			$level = "Lpu";
		} else {
			return false;
		}
		$whereString = implode(" and ", $whereArray);
		$queryLpuBuilding = "
			select
				LpuBuilding_id as LpuStructureElement_id,
				LpuBuilding_Code as LpuStructureElement_Code,
				LpuBuilding_Name as LpuStructureElement_Name,
				'LpuBuilding' as LpuStructure_Nick,
				1 as LpuStructure_Order,
				'LpuBuilding_'||LpuBuilding_id::varchar as LpuStructure_id,
				Lpu_id,
				LpuBuilding_id,
				null as LpuUnit_id,
				null as LpuSection_id
			from v_LpuBuilding t 
			where LpuBuilding_endDate is null and {$whereString}
		";
		$queryLpuUnit = "
			select
				LpuUnit_id as LpuStructureElement_id,
				LpuUnit_Code as LpuStructureElement_Code,
				LpuUnit_Name as LpuStructureElement_Name,
				'LpuUnit' as LpuStructure_Nick,
				2 as LpuStructure_Order,
				'LpuUnit_'||LpuUnit_id::varchar as LpuStructure_id,
				Lpu_id,
				LpuBuilding_id,
				LpuUnit_id,
				null as LpuSection_id
			from v_LpuUnit t 
			where LpuUnit_endDate is null and {$whereString}
		";
		$queryLpuSection = "
			select
				t.LpuSection_id as LpuStructureElement_id,
				t.LpuSection_Code as LpuStructureElement_Code,
				t.LpuSection_Name as LpuStructureElement_Name,
				'LpuSection' as LpuStructure_Nick,
				(case when t.LpuSection_pid is null then 3 else 4 end) as LpuStructure_Order,
				'LpuSection_'||t.LpuSection_id::varchar as LpuStructure_id,
				lu.Lpu_id,
				lu.LpuBuilding_id,
				lu.LpuUnit_id,
				t.LpuSection_id
			from
				v_LpuSection t 
				inner join v_LpuUnit lu on lu.LpuUnit_id = t.LpuUnit_id
			where (1=1) and {$whereString}
		";
		if ($level == "Lpu") {
			$union_arr = [ $queryLpuSection,$queryLpuBuilding, $queryLpuUnit];
		} elseif ($level == "LpuBuilding") {
			$union_arr = [$queryLpuUnit, $queryLpuSection];
		} elseif ($level == "LpuUnit" || $level == "LpuSection") {
			$union_arr = [$queryLpuSection];
		}
		$union = implode(" union ", $union_arr);

		$selectString = "
			LSE.LpuStructure_id as \"LpuStructure_id\",
			LSE.LpuStructure_Nick as \"LpuStructure_Nick\",
			LSE.LpuStructureElement_id as \"LpuStructureElement_id\",
			LSE.LpuStructureElement_Code as \"LpuStructureElement_Code\",
			LSE.LpuStructureElement_Name as \"LpuStructureElement_Name\",
			LSE.Lpu_id as \"Lpu_id\",
			LSE.LpuBuilding_id as \"LpuBuilding_id\",
			LSE.LpuUnit_id as \"LpuUnit_id\",
			LSE.LpuSection_id as \"LpuSection_id\"
		";
		$orderByString = "LSE.LpuStructure_Order";
		$query = "
			select {$selectString}
			from
				({$union}) LSE
			order by {$orderByString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuWithUnservedDiagMedService(LpuStructure_model $callObject, $data)
	{
		$queryParams = [];
		//3 - код службы функциональной диагностики
		$queryParams["MedServiceType_Code"] = 3;
		//3 - тип связи служб
		$queryParams["MedServiceLinkType_Code"] = 3;
		$query = "
			select distinct
				MS.Lpu_id as \"Lpu_id\",
                coalesce(L.Lpu_Nick, L.Lpu_Name, L.Org_Nick, L.Org_Name, 'Наименование МО не определено') as \"Lpu_Nick\"
			from
				v_MedService MS 
				left join v_MedServiceType MST ON MST.MedServiceType_id = MS.MedServiceType_id
                left join v_Lpu_all L ON L.Lpu_id = MS.Lpu_id
			where MST.MedServiceType_Code = :MedServiceType_Code
			  and MS.MedService_id not in
				(
					select MSL.MedService_lid
					from
						v_MedServiceLink MSL 
						left join v_MedServiceLinkType MSLT  on MSLT.MedServiceLinkType_id = MSL.MedServiceLinkType_id
					where MSLT.MedServiceLinkType_id = :MedServiceLinkType_Code
				)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getUnservedDiagMedService(LpuStructure_model $callObject, $data)
	{
		$queryParams = [];
		//3 - код службы функциональной диагностики
		$queryParams["MedServiceType_Code"] = 3;
		//3 - тип связи служб
		$queryParams["MedServiceLinkType_Code"] = 3;
		if (!isset($data["Lpu_id"])) {
			return [['success' => false, 'Error_Code' => '', 'Error_Msg' => 'Не указан идентификатор МО.']];
		} else {
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
			select
				MS.MedService_id as \"MedService_id\",
			    ltrim(coalesce(LU.LpuUnit_Name, '')||' '||coalesce(LS.LpuSection_Name, '')||' '||coalesce(LB.LpuBuilding_Name, '')||' '||coalesce(MS.MedService_Name, '')) as \"MedService_FullName\"
			from
				v_MedService MS 
				left join v_MedServiceType MST ON MST.MedServiceType_id = MS.MedServiceType_id
				left join v_LpuUnit LU ON LU.LpuUnit_id = MS.LpuUnit_id
				left join v_LpuSection_all LS ON LS.LpuSection_id = MS.LpuSection_id
				left join v_LpuBuilding LB ON LB.LpuBuilding_id = MS.LpuBuilding_id
			where MS.Lpu_id = :Lpu_id
			  and MST.MedServiceType_Code = :MedServiceType_Code
			  and coalesce(MS.LpuUnit_id, 0) !=0
			  and MS.MedService_id not in
				(
					select MSL.MedService_lid
					from 
						v_MedServiceLink MSL 
						left join v_MedServiceLinkType MSLT on MSLT.MedServiceLinkType_id = MSL.MedServiceLinkType_id
					where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code
				)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getFDServicesConnectedToRCCService(LpuStructure_model $callObject, $data)
	{
		$queryParams = [];
		if (!isset($data["MedService_id"])) {
			throw new Exception("Не указан идентификатор службы ЦУК");
		}
		$queryParams["MedService_id"] = $data["MedService_id"];
		//3 - тип связи служб
		$queryParams["MedServiceLinkType_Code"] = 3;
		$query = "
			select
				MSL.MedServiceLink_id as \"MedServiceLink_id\",
				MSL.MedService_lid as \"MedService_lid\",
				MS.MedService_Name as \"MedService_Name\",
				MS.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				coalesce(LU.LpuUnit_Name, '') as \"LpuUnit_Name\",
				coalesce(LS.LpuSection_Name, '') as \"LpuSection_Name\",
				coalesce(LB.LpuBuilding_Name, '') as \"LpuBuilding_Name\"
			from
				v_MedServiceLink MSL 
				left join v_MedService MS on MSL.MedService_lid = MS.MedService_id
				left join v_MedServiceLinkType MSLT on MSLT.MedServiceLinkType_id = MSL.MedServiceLinkType_id
				left join v_Lpu L on L.Lpu_id = MS.Lpu_id
				left join v_LpuSection LS on LS.LpuSection_id = MS.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = MS.LpuUnit_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = MS.LpuBuilding_id
			where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code
			  and MSL.MedService_id = :MedService_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuListByAddress(LpuStructure_model $callObject, $data)
	{
		$whereArray = [];
		$params = [];

		if (!empty($data["KLCity_id"])) {
			$whereArray[] = "KLCity_id = :KLCity_id";
			$params["KLCity_id"] = $data["KLCity_id"];
		}
		if (!empty($data["KLTown_id"])) {
			$whereArray[] = "KLTown_id = :KLTown_id";
			$params["KLTown_id"] = $data["KLTown_id"];
		}
		$selectString = "
			L.Lpu_id as \"Lpu_id\",
			L.Lpu_Nick as \"Lpu_Nick\",
			L.Lpu_Name as \"Lpu_Name\",
			L.Lpu_BegDate as \"Lpu_BegDate\",
			L.Lpu_EndDate as \"Lpu_EndDate\"
		";
		$fromString = "
			v_Lpu L 
			inner join v_Address PA on PA.Address_id = L.PAddress_id
		";
		$whereString = (count($whereArray) != 0) ? "where " . implode(" and ", $whereArray) : "";
		$query = "
			select {$selectString}
			from {$fromString}
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getSmpUnitTypes(LpuStructure_model $callObject, $data)
	{
		$isHeadSmpUnit_result = $callObject->_lpuBuildingIsHeadSmpUnit($data);
		$queryParams = [];
		$filterArray = [];
		$filterArray[] = "SUT.SmpUnitType_Code in (2,4,5)";
		if ($isHeadSmpUnit_result && !empty($isHeadSmpUnit_result[0]) && empty($isHeadSmpUnit_result[0]["SmpUnitParam_id"])) {
			$filterArray[] = "SUT.SmpUnitType_id != :SmpUnitType_id";
			$queryParams["SmpUnitType_id"] = 6;
		}
		$whereString = implode(" and ", $filterArray);
		$query = "
			select
				SUT.SmpUnitType_id as \"SmpUnitType_id\",
				SUT.SmpUnitType_Name as \"SmpUnitType_Name\",
				SUT.SmpUnitType_Code as \"SmpUnitType_Code\"
			from v_SmpUnitType SUT 
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getLpuBuildingsForFilials(LpuStructure_model $callObject, $data)
	{
		$LpuBuildingType = $callObject->loadLpuBuildingType($data);
		if (!$LpuBuildingType || empty($LpuBuildingType[0]["LpuBuildingType_id"])) {
			throw new Exception("Ошибка при определении типа текущей подстнции");
		}
		$data["LpuBuildingType_id"] = $LpuBuildingType[0]["LpuBuildingType_id"];
		$sql = "
			SELECT
				lb.LpuBuilding_id as \"LpuBuilding_id\",
				CASE WHEN l.Lpu_id IS NULL THEN lb.LpuBuilding_Name ELSE lb.LpuBuilding_Name||' ('||l.Lpu_Nick||')' END as \"LpuBuilding_Name\",
				lb.LpuBuilding_Code as \"LpuBuilding_Code\"
			FROM
				v_LpuBuilding lb 
				LEFT JOIN v_Lpu l ON(l.Lpu_id=lb.Lpu_id)
				INNER JOIN v_SmpUnitParam sup ON(sup.LpuBuilding_id=lb.LpuBuilding_id)
				INNER JOIN v_SmpUnitType sut ON(sut.SmpUnitType_id=sup.SmpUnitType_id AND sut.SmpUnitType_Code=4)
			WHERE lb.LpuBuildingType_id = :LpuBuildingType_id
			ORDER BY l.Lpu_Nick, lb.LpuBuilding_Name
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getSmpUnitData(LpuStructure_model $callObject, $data)
	{
		$sql = "
			select 
				SUP.SmpUnitParam_id as \"SmpUnitParam_id\",
				SUP.LpuBuilding_id as \"LpuBuilding_id\",
				SUP.SmpUnitType_id as \"SmpUnitType_id\",
				SUT.SmpUnitType_Code as \"SmpUnitType_Code\",
				SUP.LpuBuilding_pid as \"LpuBuilding_pid\",
				LB.Lpu_id as \"Lpu_id\",
				CASE WHEN COALESCE(LB.LpuBuilding_IsUsingMicrophone, 1) = 1 THEN 'false' else 'true' END as \"LpuBuilding_IsUsingMicrophone\"
			from
				v_SmpUnitParam SUP 
				left join v_SmpUnitType SUT ON(SUT.SmpUnitType_id=SUP.SmpUnitType_id)
				left join v_LpuBuilding LB on LB.LpuBuilding_id = SUP.LpuBuilding_id
			where SUP.LpuBuilding_id = :LpuBuilding_id
			limit 1
		";
		$sqlParams = [
			"LpuBuilding_id" => $data["LpuBuilding_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		$res = $result->result_array();
		if (empty($result)) {
			$res = [["LpuBuilding_id" => $data["LpuBuilding_id"], "SmpUnitParam_id" => null, "SmpUnitType_id" => null, "LpuBuilding_pid" => null]];
		}
		return $res;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getLpuBuildingData(LpuStructure_model $callObject, $data)
	{
		$sql = "
			select 
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_IsPrint as \"LpuBuilding_IsPrint\",
				LB.LpuBuildingType_id as \"LpuBuildingType_id\",
				LB.Lpu_id as \"Lpu_id\",
				SUP.LpuBuilding_pid as \"LpuBuilding_pid\",
				SUP.SmpUnitParam_id as \"SmpUnitParam_id\",
				SUP.SmpUnitType_id as \"SmpUnitType_id\",
				T.SmpUnitType_Code as \"SmpUnitType_Code\",
				SUP.Lpu_eid as \"Lpu_eid\",
				SUP.LpuBuilding_eid as \"LpuBuilding_eid\",
				case when coalesce(SUP.SmpUnitParam_IsAutoBuilding, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsAutoBuilding\",
				case when coalesce(SUP.SmpUnitParam_IsCall112, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsCall112\",
				case when coalesce(SUP.SmpUnitParam_IsSignalBeg, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsSignalBeg\",
				case when coalesce(SUP.SmpUnitParam_IsSignalEnd, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsSignalEnd\",
				case when coalesce(SUP.SmpUnitParam_IsOverCall, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsOverCall\",
				case when coalesce(SUP.SmpUnitParam_IsCallSenDoc, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsCallSenDoc\",
				case when coalesce(SUP.SmpUnitParam_IsKTPrint, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsKTPrint\",
				case when coalesce(SUP.SmpUnitParam_IsAutoEmergDuty, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsAutoEmergDuty\",
				case when coalesce(SUP.SmpUnitParam_IsAutoEmergDutyClose, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsAutoEmergDutyClose\",
				case when coalesce(SUP.SmpUnitParam_IsSendCall, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsSendCall\",
				case when coalesce(SUP.SmpUnitParam_IsViewOther, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsViewOther\",
				case when coalesce(SUP.SmpUnitParam_IsCancldCall, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsCancldCall\",
				case when coalesce(SUP.SmpUnitParam_IsCallControll, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsCallControll\",
				case when coalesce(SUP.SmpUnitParam_IsSaveTreePath, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsSaveTreePath\",
				case when coalesce(SUP.SmpUnitParam_IsNoMoreAssignCall, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsNoMoreAssignCall\",
				case when coalesce(SUP.SmpUnitParam_IsCallApproveSend, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsCallApproveSend\",
				case when coalesce(SUP.SmpUnitParam_IsNoTransOther, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsNoTransOther\",
				case when coalesce(SUP.SmpUnitParam_IsDenyCallAnswerDisp, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsDenyCallAnswerDisp\",
				case when coalesce(SUP.SmpUnitParam_IsDispNoControl, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsDispNoControl\",
				case when coalesce(SUP.SmpUnitParam_IsDocNoControl, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsDocNoControl\",
				case when coalesce(SUP.SmpUnitParam_IsDispOtherControl, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsDispOtherControl\",
				case when coalesce(SUP.SmpUnitParam_IsGroupSubstation, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsGroupSubstation\",
				case when SUP.SmpUnitParam_IsShowAllCallsToDP is null and getregion() in (30, 59) then 'true' else
					case when coalesce(SUP.SmpUnitParam_IsShowAllCallsToDP, 1) = 1 then 'false' else 'true' end
				end as \"SmpUnitParam_IsShowAllCallsToDP\",
				case when SUP.SmpUnitParam_IsShowCallCount is null and getregion() not in (91) then 'true' else
					case when coalesce(SUP.SmpUnitParam_IsShowCallCount, 1) = 1 then 'false' else 'true' end
				end as \"SmpUnitParam_IsShowCallCount\",
				SUP.SmpUnitParam_MaxCallCount as \"SmpUnitParam_MaxCallCount\",
				coalesce(LB.LpuBuildingSmsType_id, 1) as \"LpuBuildingSmsType_id\",
				case when coalesce(LB.LpuBuilding_setDefaultAddressCity, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_setDefaultAddressCity\",
				case when coalesce(LB.LpuBuilding_IsEmergencyTeamDelay, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsEmergencyTeamDelay\",
				case when coalesce(LB.LpuBuilding_IsUsingMicrophone, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsUsingMicrophone\",
				case when coalesce(LB.LpuBuilding_IsWithoutBalance, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsWithoutBalance\",
				case when coalesce(LB.LpuBuilding_IsCallCancel, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsCallCancel\",
				case when coalesce(LB.LpuBuilding_IsCallDouble, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsCallDouble\",
				case when coalesce(LB.LpuBuilding_IsCallSpecTeam, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsCallSpecTeam\",
				case when coalesce(LB.LpuBuilding_IsCallReason, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsCallReason\",
				case when coalesce(LB.LpuBuilding_IsDenyCallAnswerDoc, 1) = 1 then 'false' else 'true' end as \"LpuBuilding_IsDenyCallAnswerDoc\",
				coalesce(PSUT.minTimeSMP, SUT.minTimeSMP, 0) as \"minTimeSMP\",
				coalesce(PSUT.maxTimeSMP, SUT.maxTimeSMP, 0) as \"maxTimeSMP\",
				coalesce(PSUT.minTimeNMP, SUT.minTimeNMP, 0) as \"minTimeNMP\",
				coalesce(PSUT.maxTimeNMP, SUT.maxTimeNMP, 0) as \"maxTimeNMP\",
				coalesce(PSUT.minResponseTimeNMP, SUT.minResponseTimeNMP, 0) as \"minResponseTimeNMP\",
				coalesce(PSUT.maxResponseTimeNMP, SUT.maxResponseTimeNMP, 0) as \"maxResponseTimeNMP\",
				coalesce(PSUT.minResponseTimeET, SUT.minResponseTimeET, 0.25) as \"minResponseTimeET\",
				coalesce(PSUT.maxResponseTimeET, SUT.maxResponseTimeET, 2) as \"maxResponseTimeET\",
				coalesce(PSUT.ArrivalTimeET, SUT.ArrivalTimeET, 20) as \"ArrivalTimeET\",
				coalesce(PSUT.ServiceTimeET, SUT.ServiceTimeET, 40) as \"ServiceTimeET\",
				coalesce(PSUT.DispatchTimeET, SUT.DispatchTimeET, 15) as \"DispatchTimeET\",
				coalesce(PSUT.LunchTimeET, SUT.LunchTimeET) as \"LunchTimeET\"
			from
				v_LpuBuilding LB 
				left join lateral (
					select *
					from v_SmpUnitTimes 
					where LpuBuilding_id = LB.LpuBuilding_id
					limit 1
				) as SUT on true
				left join lateral (
					select *
					from v_SmpUnitParam 
					where LpuBuilding_id = LB.LpuBuilding_id
					order by SmpUnitParam_id desc
					limit 1
				) as SUP on true
				left join v_SmpUnitType T  ON(T.SmpUnitType_id=SUP.SmpUnitType_id)
				left join lateral (
					select *
					from v_SmpUnitTimes 
					where LpuBuilding_id = SUP.LpuBuilding_pid
					limit 1
				) as PSUT on true
			where LB.LpuBuilding_id = :LpuBuilding_id
			limit 1
		";
		$sqlParams = [
			"LpuBuilding_id" => $data["LpuBuilding_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		$res = $result->result_array();
		return $res;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getAddressByLpuStructure(LpuStructure_model $callObject, $data)
	{
		$params = array();

		if (!empty($data['LpuUnit_id'])) {
			$object = "LpuUnit";
			$params['object_value'] = $data['LpuUnit_id'];
			$address = "Address";
		} else if (!empty($data['LpuBuilding_id'])) {
			$object = "LpuBuilding";
			$params['object_value'] = $data['LpuBuilding_id'];
			$address = "Address";
		} else if (!empty($data['Lpu_id'])) {
			$object = "Lpu";
			$params['object_value'] = $data['Lpu_id'];
			$address = "PAddress";
		} else if (!empty($data['Org_id'])) {
			$object = "Org";
			$params['object_value'] = $data['Org_id'];
			$address = "PAddress";
		}

		$selectString = "
			A.Address_Zip as \"Address_Zip\",
			A.KLCountry_id as \"KLCountry_id\",
			A.KLRgn_id as \"KLRgn_id\",
			A.KLSubRgn_id as \"KLSubRgn_id\",
			A.KLCity_id as \"KLCity_id\",
			A.KLTown_id as \"KLTown_id\",
			A.KLStreet_id as \"KLStreet_id\",
			A.Address_House as \"Address_House\",
			A.Address_Corpus as \"Address_Corpus\",
			A.Address_Flat as \"Address_Flat\",
			A.Address_Address as \"Address_Address\"
		";
		$fromString = "
				v_{$object} Obj 
				left join v_Address A  on A.Address_id = Obj.{$address}_id
		";
		$whereString = "
			Obj.{$object}_id = :object_value
		";
		$query = "
			select {$selectString} 
			from {$fromString}
			where {$whereString}
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);

		if (!is_object($result)) {
			throw new Exception("Ошибка при получении адреса");
		}
		$response = $result->result("array");
		return ["data" => $response[0]];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getRowLpuSectionService(LpuStructure_model $callObject, $data)
	{
		$params = [
			"LpuSectionService_id" => $data["LpuSectionService_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSection_did" => $data["LpuSection_did"],
			"RecordStatus_Code" => $data["RecordStatus_Code"]
		];
		$query = "
			select 
				:LpuSectionService_id as \"LpuSectionService_id\",
				:LpuSection_id as \"LpuSection_id\",
				:RecordStatus_Code as \"RecordStatus_Code\",
				dLS.LpuSection_id as \"LpuSection_did\",
				dLS.LpuSection_FullName||', '||dLUT.LpuUnitType_Name as \"LpuSection_Name\",
				dLB.LpuBuilding_Code::varchar||'. '||dLB.LpuBuilding_Name as \"LpuBuilding_Name\"
			from
				v_LpuSection dLS 
				left join v_LpuUnit dLU on dLU.LpuUnit_id = dLS.LpuUnit_id
				left join v_LpuUnitType dLUT on dLUT.LpuUnitType_id = dLU.LpuUnitType_id
				left join v_LpuBuilding dLB on dLB.LpuBuilding_id = dLU.LpuBuilding_id
			where dLS.LpuSection_id = :LpuSection_did
			limit 1
		";
		$response = $callObject->getFirstRowFromQuery($query, $params);
		return ($response) ? ["success" => true, "data" => $response] : ["success" => false];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuUnitCountByType(LpuStructure_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"LpuUnitType_SysNick" => $data["LpuUnitType_SysNick"],
		];
		if (empty($data["Lpu_id"])) {
			return [["LpuUnitCount" => 0]];
		}
		$query = "
			select count(LU.LpuUnit_id) as \"LpuUnitCount\"
			from v_LpuUnit LU 
			where LU.Lpu_id = :Lpu_id
			  and LU.LpuUnitType_SysNick ilike :LpuUnitType_SysNick
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionServiceCount(LpuStructure_model $callObject, $data)
	{
		$query = "
			select count(LSS.LpuSectionService_id) as \"Count\"
			from v_LpuSectionService LSS 
			where LSS.LpuSection_id = :LpuSection_id
			  and LSS.LpuSection_did is not null
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result("array");
		return ["success" => true, "Count" => $response[0]["Count"]];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getLpuWithMedServiceList(LpuStructure_model $callObject, $data)
	{
		if (empty($data["MedServiceType_Code"])) {
			throw new Exception("Не задан обязательный параметр: Тип службы");
		}
		$query = "
			SELECT DISTINCT
				MS.MedService_id as \"MedService_id\",
				coalesce(
				    coalesce(L.Lpu_Nick, coalesce(L.Lpu_Name, ''))||
				    ' \ '||
				    coalesce(LB.LpuBuilding_Nick, coalesce(LB.LpuBuilding_Name, ''))||
				    ' \ '||
				    coalesce(MS.MedService_Nick, ''), 'Наименование не определено'
				) as \"MedService_Nick\"
			FROM
				v_MedService MS 
				left join v_MedServiceType MST ON MST.MedServiceType_id = MS.MedServiceType_id
				left join v_LpuBuilding LB on MS.LpuBuilding_id = LB.LpuBuilding_id
				left join v_Lpu_all L ON L.Lpu_id = MS.Lpu_id
			WHERE MST.MedServiceType_Code = :MedServiceType_Code
		";
		$queryParams = [
			"MedServiceType_Code" => $data["MedServiceType_Code"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return bool|string
	 */
	public static function getOrgPhoto($data)
	{
		if (!defined("ORGSPHOTOPATH")) {
			return false;
		}
		$orgDir = ORGSPHOTOPATH . $data["Lpu_id"] . "/"; // Директория конкретной организации, где будут лежать фотографии
		if ($data["Lpu_id"] > 0) {
			$name = $data["Lpu_id"];
			if (isset($data["LpuSection_id"]) && $data["LpuSection_id"] > 0) {
				$orgDir .= "LpuSection/";
				$name = $data["LpuSection_id"];
			} elseif (isset($data["LpuUnit_id"]) && $data["LpuUnit_id"] > 0) {
				$orgDir .= "LpuUnit/";
				$name = $data["LpuUnit_id"];
			} elseif (isset($data["LpuBuilding_id"]) && $data["LpuBuilding_id"] > 0) {
				$orgDir .= "LpuBuilding/";
				$name = $data["LpuBuilding_id"];
			}
			// ищем файл с нужным расширением и берем первый попавшися
			foreach (glob($orgDir . $name . ".*") as $fn) {
				$ext = pathinfo($fn, PATHINFO_EXTENSION);
				break;
			}
			$name .= "." . (isset($ext) ? $ext : "jpg");
			$orgDir .= "thumbs/";
			if (file_exists($orgDir . $name)) {
				return $orgDir . $name . "?t=" . time(); // добавляем параметр, чтобы не застывал в кеше
			}
		}
		return false;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuListByRegion(LpuStructure_model $callObject, $data)
	{
		$params = ["Region_id" => $data["Region_id"]];
		$join = "";
		$fields = "";
		$crossApply = '';
		if (!empty($data['extended'])) {
			$join .= "
				left join v_Address ua on ua.Address_id = O.UAddress_id
				left join v_Address pa on pa.Address_id = O.PAddress_id
			";
			$fields .= "
				,ua.Address_Address as \"Address_Address\"
				,pa.Address_Address as \"PAddress_Address\"
				,case when (
					L.Lpu_IsTest IS NULL AND
					COALESCE(L.Lpu_endDate, '2030-01-01') >= tzgetdate() AND
					CA_LB.LpuHasRegAvailableBuildings IS NOT NULL AND
					CA_MSF.LpuHasRegAvailableMedStaff IS NOT NULL
				) then 1 else 0 end as \"Can_Record\"
			";
			/**
			 * Если одновременно выполняются условия:
			 * Не тестовая МО (Lpu_IsTest принимает значения null или 1);
			 * МО открыта на текущую дату;
			 * Тип хотя бы одного подразделения МО (LpuUnitType_id) отличен от 1, 3, 6, 7, 9;
			 * Хотя бы в одной из групп отделений, открытой на текущую дату:
			 * Установлен флаг «Включить запись операторами» (LpuUnit_Enabled = 1);
			 * Тип записи (RecType_id) хотя бы одного места работы (MedStaffFact) не равен null, 5, 6,
			 * то Can_Record = 1, иначе 0.
			 */
			$crossApply .= "
				inner join lateral (
					(
						select case when count(LpuBuilding_id) > 0 then 1 else NULL end as LpuHasRegAvailableBuildings
						from v_LpuBuilding LB
						limit 1
					)
					inner join lateral (
						select case when count(LpuUnitType_id) > 0 then 1 else NULL end as BuildingHasRegAvailableUnits
						from v_LpuUnit LU
						where LU.LpuBuilding_id = LB.LpuBuilding_id
						  and LU.LpuUnitType_id NOT IN (1,3,6,7,9)
						  and LU.LpuUnit_IsEnabled = 2
						limit 1
					) as CA_LU on true
					where LB.Lpu_id = L.Lpu_id
					  and CA_LU.BuildingHasRegAvailableUnits IS NOT NULL
				) as CA_LB on true
				inner join lateral (
					select case when count(MedStaffFact_id) > 0 then 1 else NULL end as LpuHasRegAvailableMedStaff
					from v_MedStaffFact MSF
					where L.Lpu_id = MSF.Lpu_id
					  and MSF.RecType_id NOT IN (5,6)
					  and COALESCE(MSF.WorkData_endDate, '2030-01-01') > tzgetdate()
					  and COALESCE(msf.MedStaffFactCache_IsNotShown, 0) <> 2
					limit 1
				) as CA_MSF on true
			";
		}
		$selectString = "
			L.Lpu_id as \"Lpu_id\",
			O.Org_Name as \"Org_Name\",
			O.Org_Nick as \"Org_Nick\"
			{$fields}
		";
		$fromString = "
			Lpu L 
			inner join Org O on O.Org_id = L.Org_id
			{$join}
			{$crossApply}
		";
		$whereString = "L.Region_id = :Region_id";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuListBySubRgn(LpuStructure_model $callObject, $data)
	{
		$params = ["KLSubRgn_id" => $data["SubRgn_id"]];
		$fieldsList = [
			"L.Lpu_id as \"Lpu_id\"",
			"L.Lpu_Name as \"Org_Name\"",
			"L.Lpu_Nick as \"Org_Nick\"",
		];
		$joinList = [];
		if (!empty($data["Extended"]) && $data["Extended"] == 1) {
			/**
			 * Если одновременно выполняются условия:
			 * Не тестовая МО (Lpu_IsTest принимает значения null или 1);
			 * МО открыта на текущую дату;
			 * Тип хотя бы одного подразделения МО (LpuUnitType_id) отличен от 1, 3, 6, 7, 9;
			 * Хотя бы в одной из групп отделений, открытой на текущую дату, установлен флаг «Включить запись операторами» (LpuUnit_IsEnabled = 2);
			 * Тип записи (RecType_id) хотя бы одного места работы (MedStaffFact) не равен null, 5, 6,
			 * то Can_Record = 1, иначе 0.
			 */
			$joinList[] = "left join v_Address ua on ua.Address_id = L.UAddress_id";
			$joinList[] = "left join v_Address pa on pa.Address_id = L.PAddress_id";
			$joinList[] = "
				left join lateral (
					select LpuUnit_id
					from v_LpuUnit 
					where Lpu_id = L.Lpu_id
					  and LpuUnitType_id not in (1, 3, 6, 7, 9)
					  and (LpuUnit_begDate is null or LpuUnit_begDate <= tzgetdate())
					  and (LpuUnit_endDate is null or LpuUnit_endDate >= tzgetdate())
					limit 1
				) as LU on true
			";
			$joinList[] = "
				left join lateral (
					select LpuUnit_id
					from v_LpuUnit 
					where Lpu_id = L.Lpu_id
					  and LpuUnit_IsEnabled = 2
					  and (LpuUnit_begDate is null or LpuUnit_begDate <= tzgetdate())
					  and (LpuUnit_endDate is null or LpuUnit_endDate >= tzgetdate())
					limit 1
				) as LUIE on true
			";
			$joinList[] = "
				left join lateral (
					select MedStaffFact_id
					from v_MedStaffFact 
					where Lpu_id = L.Lpu_id
					  and coalesce(RecType_id, 0) not in (0, 5, 6)
					  and (WorkData_begDate is null or WorkData_begDate <= tzgetdate())
					  and (WorkData_endDate is null or WorkData_endDate >= tzgetdate())
					limit 1
				) MSF
			";

			$fieldsList[] = "ua.Address_Address as \"Address_Address\"";
			$fieldsList[] = "pa.Address_Address as \"PAddress_Address\"";
			$fieldsList[] = "
				case when
					coalesce(L.Lpu_IsTest, 1) = 1
					and coalesce(L.Lpu_endDate, tzgetdate() + 1) >= tzgetdate()
					and LU.LpuUnit_id is not null
					and LUIE.LpuUnit_id is not null
					and MSF.MedStaffFact_id is not null
				then 1
				else 0
			end as \"Can_Record\"
			";
		}
		$selectString = implode(", ", $fieldsList);
		$fromString = "
			v_Lpu L
		".implode(" ", $joinList);
		$whereString = "
			exists (
				select OrgServiceTerr_id
				from v_OrgServiceTerr 
				where Org_id = L.Org_id
				  and KLSubRgn_id = :KLSubRgn_id
			)
		";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuRegionByID(LpuStructure_model $callObject, $data)
	{
		$params = [
			"LpuRegion_id" => $data["LpuRegion_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$query = "
			select 
				lr.Lpu_id as \"Lpu_id\", 
				lr.LpuSection_id as \"LpuSection_id\",
				ls.LpuBuilding_id as \"LpuBuilding_id\",
				lr.LpuRegionType_id as \"LpuRegionType_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\", 
				to_char(lr.LpuRegion_begDate::date, '{$callObject->dateTimeForm120}') as \"LpuRegion_begDate\", 
				to_char(lr.LpuRegion_endDate::date, '{$callObject->dateTimeForm120}') as \"LpuRegion_endDate\"
			from
				v_LpuRegion LR 
				left join v_LpuSection ls on ls.LpuSection_id = lr.LpuSection_id
			where LR.LpuRegion_id = :LpuRegion_id
			  and LR.Lpu_id = :Lpu_id
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuRegionByMO(LpuStructure_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"]
		];
		$query = "
			select
				lr.Lpu_id as \"Lpu_id\", 
				lr.LpuSection_id as \"LpuSection_id\",
				ls.LpuBuilding_id as \"LpuBuilding_id\",
				lr.LpuRegionType_id as \"LpuRegionType_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\", 
				to_char(lr.LpuRegion_begDate::date, '{$callObject->dateTimeForm120}') as \"LpuRegion_begDate\", 
				to_char(lr.LpuRegion_endDate::date, '{$callObject->dateTimeForm120}') as \"LpuRegion_endDate\"
			from
				v_LpuRegion LR 
				left join v_LpuSection ls on ls.LpuSection_id = lr.LpuSection_id
			where LR.Lpu_id = :Lpu_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuRegionWorkerPlaceByID(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$params = [
			"MedStaffRegion_id" => $data["MedStaffRegion_id"]
		];
		if (!empty($data["Lpu_id"])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
			select 
				to_char(MedStaffRegion_begDate::date, '{$callObject->dateTimeForm120}') as \"MedStaffRegion_begDate\",
				to_char(MedStaffRegion_endDate::date, '{$callObject->dateTimeForm120}') as \"MedStaffRegion_endDate\",
				case when MedStaffRegion_isMain = 2 then 1 else 0 end as \"MedStaffRegion_isMain\"
			from v_MedStaffRegion 
			where MedStaffRegion_id = :MedStaffRegion_id
			{$filter}
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuRegionWorkerPlaceList(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		$params = [];
		if (!empty($data["LpuRegion_id"])) {
			$filterArray[] = "LpuRegion_id = :LpuRegion_id";
			$params["LpuRegion_id"] = $data["LpuRegion_id"];
		}
		if (!empty($data["MedStaffFact_id"])) {
			$filterArray[] = "MedStaffFact_id = :MedStaffFact_id";
			$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		}
		if (!empty($data["MedStaffRegion_begDate"])) {
			$filterArray[] = "MedStaffRegion_begDate = :MedStaffRegion_begDate";
			$params["MedStaffRegion_begDate"] = $data["MedStaffRegion_begDate"];
		}
		if (!empty($data["MedStaffRegion_endDate"])) {
			$filterArray[] = "MedStaffRegion_endDate = :MedStaffRegion_endDate";
			$params["MedStaffRegion_endDate"] = $data["MedStaffRegion_endDate"];
		}
		if (!empty($data["Lpu_id"])) {
			$filterArray[] = "Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if (empty($filter)) {
			return [];
		}
		$whereString = implode(" and ", $filterArray);
		$query = "
			select MedStaffRegion_id as \"MedStaffRegion_id\"
			from v_MedStaffRegion 
			where {$whereString}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuBuildingById(LpuStructure_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"LpuBuilding_id" => $data["LpuBuilding_id"]
		];
		$query = "
			select 
				LB.Lpu_id as \"Lpu_id\",
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.Server_id as \"Server_id\",
				to_char(LB.LpuBuilding_begDate::date, '{$callObject->dateTimeForm120}') as \"LpuBuilding_begDate\",
				to_char(LB.LpuBuilding_endDate::date, '{$callObject->dateTimeForm120}') as \"LpuBuilding_endDate\",
				LB.LpuBuilding_Code as \"LpuBuilding_Code\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				LB.LpuBuildingType_id as \"LpuBuildingType_id\",
				LB.LpuBuilding_CmpStationCode as \"LpuBuilding_CmpStationCode\",
				LB.LpuBuilding_CmpSubstationCode as \"LpuBuilding_CmpSubstationCode\",
				LB.PAddress_id as \"PAddress_id\",
				LB.Address_id as \"Address_id\",
				LB.LpuBuilding_Latitude as \"LpuBuilding_Latitude\",
				LB.LpuBuilding_Longitude as \"LpuBuilding_Longitude\",
				LB.LpuBuilding_RoutePlan as \"LpuBuilding_RoutePlan\",
				LpuBuilding_WorkTime as \"LpuBuilding_WorkTime\",
				IsExport.YesNo_Code as \"LpuBuilding_IsExport\"
			from
				v_LpuBuilding LB 
				left join v_YesNo IsExport on IsExport.YesNo_id = COALESCE(LpuBuilding_IsExport,1)
			where LB.Lpu_id = :Lpu_id
			  and LB.LpuBuilding_id = :LpuBuilding_id
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuBuildingListForAPI(LpuStructure_model $callObject, $data)
	{
		$params = [];
		$filters = [];
		if (!empty($data["Lpu_id"])) {
			$filters[] = "LB.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["LpuBuilding_Code"])) {
			$filters[] = "LB.LpuBuilding_Code ilike :LpuBuilding_Code||'%'";
			$params["LpuBuilding_Code"] = $data["LpuBuilding_Code"];
		}
		if (!empty($data["LpuBuilding_Name"])) {
			$filters[] = "LB.LpuBuilding_Name ilike :LpuBuilding_Name||'%'";
			$params["LpuBuilding_Name"] = $data["LpuBuilding_Name"];
		}
		if (!empty($data["LpuBuildingType_id"])) {
			$filters[] = "LB.LpuBuildingType_id = :LpuBuildingType_id";
			$params["LpuBuildingType_id"] = $data["LpuBuildingType_id"];
		}
		$join = "";
		$fields = "";
		$crossApply = '';
		if (!empty($data['extended'])) {
			$join .= "
				left join v_Address ua on ua.Address_id = LB.Address_id
				left join v_Address pa on pa.Address_id = LB.PAddress_id
			";
			$fields .= "
				,ua.Address_Address as \"UAddress_Address\"
				,pa.Address_Address as \"PAddress_Address\"
				,case when (CA_LU.BuildingHasRegAvailableUnits IS NOT NULL AND CA_MSF.LpuHasRegAvailableMedStaff IS NOT NULL) then 1 else 0 end as \"Can_Record\"
			";
			/**
			 * Если одновременно выполняются условия:
			 * Тип подразделения МО (LpuUnitType_id) отличен от 1, 3, 6, 7, 9;
			 * Хотя бы в одной из групп отделений, открытой на текущую дату подразделения МО:
			 * Установлен флаг «Включить запись операторами» (LpuUnit_Enabled = 1);
			 * Тип записи (RecType_id) хотя бы одного места работы (MedStaffFact) не равен null, 5, 6,
			 * то Can_Record = 1, иначе 0.
			 */
			$crossApply .= "
				inner join lateral (
					select case when count(LpuUnitType_id) > 0 then 1 else NULL end as BuildingHasRegAvailableUnits
					from v_LpuUnit LU
					where LU.LpuBuilding_id = LB.LpuBuilding_id
					  and LU.LpuUnitType_id NOT IN (1,3,6,7,9)
					  and LU.LpuUnit_IsEnabled = 2
					limit 1
				) as CA_LU on true
				inner join lateral (
					select case when count(MedStaffFact_id) > 0 then 1 else NULL end as LpuHasRegAvailableMedStaff
					from v_MedStaffFact MSF
					where LB.LpuBuilding_id = MSF.LpuBuilding_id
					  and MSF.RecType_id NOT IN (5,6)
					  and coalesce(MSF.WorkData_endDate, '2030-01-01') > tzgetdate()
					  and coalesce(msf.MedStaffFactCache_IsNotShown, 0) <> 2
					limit 1
				) as CA_MSF on true
			";
		}
		$selectString = "
			LB.LpuBuilding_id as \"LpuBuilding_id\",
			LB.LpuBuilding_Code as \"LpuBuilding_Code\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			LB.LpuBuildingType_id as \"LpuBuildingType_id\"
			{$fields}
		";
		$fromString = "
			v_LpuBuilding LB 
			{$join}
			{$crossApply}
		";
		$filtersString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select {$selectString}
			from {$fromString}
			{$filtersString}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionListForAPI(LpuStructure_model $callObject, $data)
	{
		$params = [];
		$filters = [];
		if (!empty($data["Lpu_id"])) {
			$filters[] = "LS.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["LpuBuilding_id"])) {
			$filters[] = "LS.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		if (!empty($data["LpuSection_id"])) {
			$filters[] = "LS.LpuSection_id = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["LpuSection_Code"])) {
			$filters[] = "LS.LpuSection_Code = :LpuSection_Code";
			$params["LpuSection_Code"] = $data["LpuSection_Code"];
		}
		if (!empty($data["LpuSectionCode_Code"])) {
			$filters[] = "LSC.LpuSectionCode_Code = :LpuSectionCode_Code";
			$params["LpuSectionCode_Code"] = $data["LpuSectionCode_Code"];
		}
		if (!empty($data["LpuSection_Name"])) {
			$filters[] = "LS.LpuSection_Name = :LpuSection_Name";
			$params["LpuSection_Name"] = $data["LpuSection_Name"];
		}
		if (!empty($data["LpuSectionOuter_id"])) {
			$filters[] = "LS.LpuSectionOuter_id = :LpuSectionOuter_id";
			$params["LpuSectionOuter_id"] = $data["LpuSectionOuter_id"];
		}
		$filtersString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuBuilding_id as \"LpuBuilding_id\",
				LS.LpuUnit_id as \"LpuUnit_id\",
				LU.LpuUnit_Code as \"LpuUnit_Code\",
				LUT.LpuUnitType_Code as \"LpuUnitType_Code\",
				LU.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(LS.LpuSection_setDate::date, '{$callObject->dateTimeForm120}') as \"LpuSection_setDate\",
				to_char(LS.LpuSection_disDate::date, '{$callObject->dateTimeForm120}') as \"LpuSection_disDate\",
				LS.LpuSectionProfile_Code as \"LpuSectionProfile_Code\", 
				LS.LpuSection_Code as \"LpuSection_Code\", 
				LS.LpuSectionCode_id as \"LpuSectionCode_id\", 
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSectionOuter_id as \"LpuSectionOuter_id\"
			from
				v_LpuSection LS 
				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_LpuSectionCode LSC  on LSC.LpuSectionCode_id = LS.LpuSectionCode_id
			{$filtersString}
			order by
				LS.LpuSection_setDate,
				LS.LpuSection_Code,
				LS.LpuSection_Name
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionByIdForAPI(LpuStructure_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"]
		];
		$filters = ["LS.Lpu_id = :Lpu_id"];
		if (!empty($data["LpuSection_id"])) {
			$filters[] = "LS.LpuSection_id = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["LpuSectionOuter_id"])) {
			$filters[] = "LS.LpuSectionOuter_id = :LpuSectionOuter_id";
			$params["LpuSectionOuter_id"] = $data["LpuSectionOuter_id"];
		}
		$filtersString = implode(" and ", $filters);
		$query = "
			select
				LS.LpuSection_id as \"LpuSection_id\",
				to_char(LS.LpuSection_setDate::date, '{$callObject->dateTimeForm120}') as \"LpuSection_setDate\",
				to_char(LS.LpuSection_disDate::date, '{$callObject->dateTimeForm120}') as \"LpuSection_disDate\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LS.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LS.LpuUnit_id as \"LpuUnit_id\",
				LS.LpuSection_Code as \"LpuSection_Code\",
				LS.LpuSectionCode_id as \"LpuSectionCode_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.MesAgeGroup_id as \"MesAgeGroup_id\",
				LS.MESLevel_id as \"MESLevel_id\",
				case when LS.LpuSection_IsHTMedicalCare = 2 then 1 else 0 end as \"LpuSection_IsHTMedicalCare\",
				LS.LpuSection_KolAmbul as \"LpuSection_KolAmbul\",
				LS.LpuSection_KolJob as \"LpuSection_KolJob\",
				LS.LpuSection_PlanAutopShift as \"LpuSection_PlanAutopShift\",
				LS.LpuSection_PlanResShift as \"LpuSection_PlanResShift\",
				LS.LpuSection_PlanTrip as \"LpuSection_PlanTrip\",
				LS.LpuSection_PlanVisitDay as \"LpuSection_PlanVisitDay\",
				LS.LpuSection_PlanVisitShift as \"LpuSection_PlanVisitShift\",
				LS.LpuSectionAge_id as \"LpuSectionAge_id\",
				LS.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				LS.LpuSectionOuter_id as \"LpuSectionOuter_id\",
				LU.LpuUnitType_id as \"LpuUnitType_id\",
				LS.FRMPSubdivision_id as \"FRMPSubdivision_id\",
				LU.LpuBuildingPass_id as \"LpuBuildingPass_id\"
			from
				v_LpuSection LS 
				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			where {$filtersString}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionForAPI(LpuStructure_model $callObject, $data)
	{
		$params = [
			"LpuSection_id" => $data["LpuSection_id"]
		];
		$query = "
			select
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuBuilding_id as \"LpuBuilding_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				LU.LpuUnit_Code as \"LpuUnit_Code\",
				LUT.LpuUnitType_Code as \"LpuUnitType_Code\",
				LU.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(LS.LpuSection_setDate::date, '{$callObject->dateTimeForm120}') as \"LpuSection_setDate\",
				to_char(LS.LpuSection_disDate::date, '{$callObject->dateTimeForm120}') as \"LpuSection_disDate\",
				LS.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LS.LpuSection_Code as \"LpuSection_Code\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.MesAgeGroup_id as \"MesAgeGroup_id\",
				LS.MESLevel_id as \"MESLevel_id\",
				LS.LpuSection_IsHTMedicalCare as \"LpuSection_IsHTMedicalCare\",
				LS.LpuSection_KolAmbul as \"LpuSection_KolAmbul\",
				LS.LpuSection_KolJob as \"LpuSection_KolJob\",
				LS.LpuSection_PlanAutopShift as \"LpuSection_PlanAutopShift\",
				LS.LpuSection_PlanResShift as \"LpuSection_PlanResShift\",
				LS.LpuSection_PlanTrip as \"LpuSection_PlanTrip\",
				LS.LpuSection_PlanVisitDay as \"LpuSection_PlanVisitDay\",
				LS.LpuSection_PlanVisitShift as \"LpuSection_PlanVisitShift\",
				LS.LpuSectionAge_id as \"LpuSectionAge_id\",
				LS.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				LS.LpuSectionOuter_id as \"LpuSectionOuter_id\",
				LS.FRMPSubdivision_id as \"FRMPSubdivision_id\",
				LU.LpuBuildingPass_id as \"LpuBuildingPass_id\"
			from
				v_LpuSection LS
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
			where LS.LpuSection_id = :LpuSection_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuUnitByIdForAPI(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				LU.LpuBuilding_id as \"LpuBuilding_id\",
				LU.LpuUnitType_id as \"LpuUnitType_id\",
				LU.LpuUnitTypeDop_id as \"LpuUnitTypeDop_id\",
				LU.Address_id as \"Address_id\",
				LU.LpuUnit_Code as \"LpuUnit_Code\",
				LU.LpuUnit_Name as \"LpuUnit_Name\",
				LU.LpuUnit_Descr as \"LpuUnit_Descr\",
				LU.LpuUnit_Phone as \"LpuUnit_Phone\",
				LU.LpuUnit_IsEnabled as \"LpuUnit_IsEnabled\",
				LU.LpuUnit_IsDirWithRec as \"LpuUnit_IsDirWithRec\",
				LU.LpuUnit_ExtMedCnt as \"LpuUnit_ExtMedCnt\",
				LU.LpuUnit_Email as \"LpuUnit_Email\",
				LU.LpuUnit_IP as \"LpuUnit_IP\",
				LU.LpuUnitSet_id as \"LpuUnitSet_id\",
				LU.LpuUnit_Guid as \"LpuUnit_Guid\",
				to_char(LU.LpuUnit_begDate::date, '{$callObject->dateTimeForm120}') as \"LpuUnit_begDate\",
				to_char(LU.LpuUnit_endDate::date, '{$callObject->dateTimeForm120}') as \"LpuUnit_endDate\",
				LU.LpuUnit_IsOMS as \"LpuUnit_IsOMS\",
				LU.UnitDepartType_fid as \"UnitDepartType_fid\",
				LU.LpuUnitProfile_fid as \"LpuUnitProfile_fid\",
				LU.LpuUnit_isStandalone as \"LpuUnit_isStandalone\",
				LU.LpuBuildingPass_id as \"LpuBuildingPass_id\",
				LU.LpuUnit_isHomeVisit as \"LpuUnit_isHomeVisit\",
				LU.LpuUnit_isCMP as \"LpuUnit_isCMP\",
				LU.LpuUnit_FRMOUnitID as \"LpuUnit_FRMOUnitID\",
				LU.LpuUnit_FRMOid as \"LpuUnit_FRMOid\"
			from
				v_LpuUnit LU
			where
				LU.Lpu_id = :Lpu_id
				and LU.LpuUnit_id = :LpuUnit_id
		";
		return $callObject->queryResult($query, [
			'Lpu_id' => $data['Lpu_id'],
			'LpuUnit_id' => $data['LpuUnit_id'],
		]);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuUnitListForAPI(LpuStructure_model $callObject, $data)
	{
		$query = "
			select LpuUnit_id as \"LpuUnit_id\"
			from v_LpuUnit 
			where LpuBuilding_id = :LpuBuilding_id
		";
		$queryParams = [
			'LpuBuilding_id' => $data['LpuBuilding_id']
		];
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionLpuSectionProfileListForAPI(LpuStructure_model $callObject, $data)
	{
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id']
		);

		$query = "
			select
				LSLSP.LpuSectionLpuSectionProfile_id as \"LpuSectionLpuSectionProfile_id\",
				LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				to_char(LSLSP.LpuSectionLpuSectionProfile_begDate::date, '{$callObject->dateTimeForm120}') as \"LpuSectionLpuSectionProfile_begDate\",
				to_char(LSLSP.LpuSectionLpuSectionProfile_endDate::date, '{$callObject->dateTimeForm120}') as \"LpuSectionLpuSectionProfile_endDate\"
			from
				v_LpuSectionLpuSectionProfile LSLSP
				inner join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LSLSP.LpuSectionProfile_id
				inner join v_LpuSection LS on LS.LpuSection_id = LSLSP.LpuSection_id
			where
				LSLSP.LpuSection_id = :LpuSection_id
				and Ls.Lpu_id = :Lpu_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuRegionListForAPI(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$params = [
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		if (!empty($data["LpuSection_id"])) {
			$filter .= " and LR.LpuSection_id = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["LpuRegion_Name"])) {
			$filter .= " and LR.LpuRegion_Name = :LpuRegion_Name";
			$params["LpuRegion_Name"] = $data["LpuRegion_Name"];
		}
		$query = "
			select LR.LpuRegion_id as \"LpuRegion_id\"
			from
				v_LpuRegion LR
				inner join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
			where LS.LpuBuilding_id = :LpuBuilding_id
			  and LS.Lpu_id = :Lpu_id
			  {$filter}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuRegionListByMOForAPI(LpuStructure_model $callObject, $data)
	{
		$params = ["Lpu_id" => $data["Lpu_id"]];
		$query = "
			select
				lr.Lpu_id as \"Lpu_id\", 
				lr.LpuSection_id as \"LpuSection_id\",
				ls.LpuBuilding_id as \"LpuBuilding_id\",
				lr.LpuRegionType_id as \"LpuRegionType_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\", 
				to_char(lr.LpuRegion_begDate::date, '{$callObject->dateTimeForm120}') as \"LpuRegion_begDate\", 
				to_char(lr.LpuRegion_endDate::date, '{$callObject->dateTimeForm120}') as \"LpuRegion_endDate\"
			from
				v_LpuRegion LR
				left join v_LpuSection ls on ls.LpuSection_id = lr.LpuSection_id
			where LR.Lpu_id = :Lpu_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionWardListForAPI(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$params = [
			"LpuSection_id" => $data["LpuSection_id"]
		];
		if (!empty($data["LpuSectionWard_Name"])) {
			$filter .= " and LSW.LpuSectionWard_Name = :LpuSectionWard_Name";
			$params["LpuSectionWard_Name"] = $data["LpuSectionWard_Name"];
		}
		if (!empty($data['LpuSectionWard_Floor'])) {
			$filter .= " and LSW.LpuSectionWard_Floor = :LpuSectionWard_Floor";
			$params['LpuSectionWard_Floor'] = $data['LpuSectionWard_Floor'];
		}

		if (!empty($data["Lpu_id"])) {
			$filter .= " and LS.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
			select LSW.LpuSectionWard_id as \"LpuSectionWard_id\"
			from
				v_LpuSectionWard LSW
				left join v_LpuSection LS on LS.LpuSection_id = LSW.LpuSection_id
			where LSW.LpuSection_id = :LpuSection_id
			{$filter}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionWardByIdForAPI(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$params = [
			"LpuSectionWard_id" => $data["LpuSectionWard_id"]
		];
		if (!empty($data["Lpu_id"])) {
			$filter .= " and LS.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
			select
				LSW.LpuSection_id as \"LpuSection_id\",
				LSW.LpuSectionWard_Name as \"LpuSectionWard_Name\",
				LSW.LpuSectionWard_Floor as \"LpuSectionWard_Floor\",
				LSW.LpuWardType_id as \"LpuWardType_id\", 
				LSW.Sex_id as \"Sex_id\", 
				LSW.LpuSectionWard_MainPlace as \"LpuSectionWard_MainPlace\",
				LSW.LpuSectionWard_DopPlace as \"LpuSectionWard_DopPlace\",
				LSW.LpuSectionWard_BedRepair as \"LpuSectionWard_BedRepair\",
				LSW.LpuSectionWard_Square as \"LpuSectionWard_Square\", 
				LSW.LpuSectionWard_DayCost as \"LpuSectionWard_DayCost\",
				LSW.LpuSectionWard_Views as \"LpuSectionWard_Views\",
				LS.Lpu_id as \"Lpu_id\",
				to_char(LSW.LpuSectionWard_setDate::date, '{$callObject->dateTimeForm120}') as \"LpuSectionWard_setDate\",
				to_char(LSW.LpuSectionWard_disDate::date, '{$callObject->dateTimeForm120}') as \"LpuSectionWard_disDate\"
			from
				v_LpuSectionWard LSW 
				left join v_LpuSection LS on LS.LpuSection_id = LSW.LpuSection_id
			where LSW.LpuSectionWard_id = :LpuSectionWard_id
			{$filter}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionWardComfortLinkListForAPI(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$params = [
			"LpuSectionWard_id" => $data["LpuSectionWard_id"]
		];
		if (!empty($data["DChamberComfort_id"])) {
			$filter .= " and LSWCL.DChamberComfort_id = :DChamberComfort_id";
			$params["DChamberComfort_id"] = $data["DChamberComfort_id"];
		}
		if (!empty($data["Lpu_id"])) {
			$params["Lpu_id"] = $data["Lpu_id"];
			$filter .= " and LS.Lpu_id = :Lpu_id";
		}
		$query = "
			select LSWCL.LpuSectionWardComfortLink_id as \"LpuSectionWardComfortLink_id\"
			from
				fed.v_LpuSectionWardComfortLink LSWCL
				left join dbo.v_LpuSectionWard LSW on LSW.LpuSectionWard_id = LSWCL.LpuSectionWard_id
				left join v_LpuSection LS on LS.LpuSection_id = LSW.LpuSection_id
			where LSWCL.LpuSectionWard_id = :LpuSectionWard_id
			{$filter}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionWardComfortLinkForAPI(LpuStructure_model $callObject, $data)
	{
		$params = [
			"LpuSectionWardComfortLink_id" => $data["LpuSectionWardComfortLink_id"]
		];
		$query = "
			select
				LSWCL.LpuSectionWardComfortLink_id as \"LpuSectionWardComfortLink_id\",
				LSWCL.LpuSectionWard_id as \"LpuSectionWard_id\",
				LSWCL.DChamberComfort_id as \"DChamberComfort_id\",
				LSWCL.LpuSectionWardComfortLink_Count as \"LpuSectionWardComfortLink_Count\",
				LS.Lpu_id as \"Lpu_id\"
			from
				fed.v_LpuSectionWardComfortLink LSWCL
				left join dbo.v_LpuSectionWard LSW on LSW.LpuSectionWard_id = LSWCL.LpuSectionWard_id
				left join v_LpuSection LS on LS.LpuSection_id = LSW.LpuSection_id
			where LSWCL.LpuSectionWardComfortLink_id = :LpuSectionWardComfortLink_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $LpuUnitType_Code
	 * @return bool|float|int|string
	 */
	public static function getLpuUnitTypeId(LpuStructure_model $callObject, $LpuUnitType_Code)
	{
		$query = "
			select LpuUnitType_id as \"LpuUnitType_id\"
			from v_LpuUnitType 
			where LpuUnitType_Code = :LpuUnitType_Code
			limit 1
		";
		$queryParams = [
			"LpuUnitType_Code" => $LpuUnitType_Code
		];
		return $callObject->getFirstResultFromQuery($query, $queryParams, true);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $LpuSectionProfile_Code
	 * @return bool|float|int|string
	 */
	public static function getLpuSectionProfileId(LpuStructure_model $callObject, $LpuSectionProfile_Code)
	{
		$query = "
			select LpuSectionProfile_id as \"LpuSectionProfile_id\"
			from v_LpuSectionProfile  
			where LpuSectionProfile_Code = :LpuSectionProfile_Code
			limit 1
		";
		$queryParams = [
			"LpuSectionProfile_Code" => $LpuSectionProfile_Code
		];
		return $callObject->getFirstResultFromQuery($query, $queryParams, true);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	public static function getNmpParams(LpuStructure_model $callObject, $data)
	{
		$params = ["MedService_id" => $data["MedService_id"]];
		$query = "
			select 
				WT.MedService_id as \"MedService_id\",
				to_char(WT.LpuHMPWorkTime_MoFrom::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_MoFrom\",
				to_char(WT.LpuHMPWorkTime_MoTo::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_MoTo\",
				to_char(WT.LpuHMPWorkTime_TuFrom::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_TuFrom\",
				to_char(WT.LpuHMPWorkTime_TuTo::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_TuTo\",
				to_char(WT.LpuHMPWorkTime_WeFrom::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_WeFrom\",
				to_char(WT.LpuHMPWorkTime_WeTo::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_WeTo\",
				to_char(WT.LpuHMPWorkTime_ThFrom::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_ThFrom\",
				to_char(WT.LpuHMPWorkTime_ThTo::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_ThTo\",
				to_char(WT.LpuHMPWorkTime_FrFrom::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_FrFrom\",
				to_char(WT.LpuHMPWorkTime_FrTo::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_FrTo\",
				to_char(WT.LpuHMPWorkTime_SaFrom::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_SaFrom\",
				to_char(WT.LpuHMPWorkTime_SaTo::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_SaTo\",
				to_char(WT.LpuHMPWorkTime_SuFrom::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_SuFrom\",
				to_char(WT.LpuHMPWorkTime_SuTo::date, '{$callObject->dateTimeFormTime}') as \"LpuHMPWorkTime_SuTo\"
			from v_LpuHMPWorkTime WT 
			where WT.MedService_id = :MedService_id
			limit 1
		";
		$response = $callObject->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}
		$callObject->setParams(["session" => $data["session"]]);
		$callObject->resetGlobalOptions();
		$options = $callObject->globalOptions['globals'];
		if (count($response) == 0) {
			$response[0] = [
				"MedService_id" => $data["MedService_id"],
				"LpuHMPWorkTime_MoFrom" => $options["nmp_monday_beg_time"],
				"LpuHMPWorkTime_MoTo" => $options["nmp_monday_end_time"],
				"LpuHMPWorkTime_TuFrom" => $options["nmp_tuesday_beg_time"],
				"LpuHMPWorkTime_TuTo" => $options["nmp_tuesday_end_time"],
				"LpuHMPWorkTime_WeFrom" => $options["nmp_wednesday_beg_time"],
				"LpuHMPWorkTime_WeTo" => $options["nmp_wednesday_end_time"],
				"LpuHMPWorkTime_ThFrom" => $options["nmp_thursday_beg_time"],
				"LpuHMPWorkTime_ThTo" => $options["nmp_thursday_end_time"],
				"LpuHMPWorkTime_FrFrom" => $options["nmp_friday_beg_time"],
				"LpuHMPWorkTime_FrTo" => $options["nmp_friday_end_time"],
				"LpuHMPWorkTime_SaFrom" => $options["nmp_saturday_beg_time"],
				"LpuHMPWorkTime_SaTo" => $options["nmp_saturday_end_time"],
				"LpuHMPWorkTime_SuFrom" => $options["nmp_sunday_beg_time"],
				"LpuHMPWorkTime_SuTo" => $options["nmp_sunday_end_time"],
			];
		}
		return $response;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getMOById(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				Lpu_Nick as \"Org_Nick\",
				Lpu_Name as \"Org_Name\"
			from v_Lpu 
			where Lpu_id = :Lpu_id
		";
		return $callObject->queryResult($query, $data);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuUnitProfile(LpuStructure_model $callObject, $data)
	{
		$queryParams = [];
		$query = "
			SELECT
				LUT.LpuUnitProfile_id as \"LpuUnitProfile_fid\",
			    LUT.LpuUnitProfile_Name as \"LpuUnitProfile_Name\",
			    LUT.LpuUnitProfile_pid as \"LpuUnitProfile_pid\",
			    LUT.LpuUnitProfile_Form30 as \"LpuUnitProfile_Form30\",
			    LUT.UnitDepartType_id as \"UnitDepartType_id\"
			FROM fed.LpuUnitProfile LUT 
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getFRMPSubdivisionType(LpuStructure_model $callObject, $data)
	{
		$query = "
			SELECT
				id,
				name,
				fullname,
				parent
			FROM
				persis.v_FRMPSubdivision FRMPS
			ORDER BY
				FRMPS.parent
			";
		$result = $callObject->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuAddress(LpuStructure_model $callObject, $data)
	{
		if (empty($data["KLTown_id"]) && empty($data["KLCity_id"])) {
			return false;
		}
		$params = [];
		$whereArray = [];
		$whereArray[] = "(lr.LpuRegion_endDate is null or lr.LpuRegion_endDate > tzgetdate())";
		if (isset($data["Person_Age"])) {
			$whereArray[] = (($data["Person_Age"]) < 18) ? "LRT.LpuRegionType_SysNick in ('ped','vop')" : "LRT.LpuRegionType_SysNick in ('ter','vop')";
		}
		if (isset($data["Lpu_id"])) {
			$whereArray[] = "LR.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if (isset($data["KLTown_id"])) {
			$whereArray[] = "(LRS.KLTown_id = :KLTown_id or LRS.KLCity_id = :KLTown_id)";
			$params["KLTown_id"] = $data["KLTown_id"];
		} elseif (isset($data["KLCity_id"])) {
			$whereArray[] = "LRS.KLCity_id = :KLCity_id";
			$params["KLCity_id"] = $data["KLCity_id"];
		}
		if (isset($data["KLHome"])) {
			$whereArray[] = "GetHouse(LRS.LpuRegionStreet_HouseSet, :KLHome) = 1";
			$params["KLHome"] = $data["KLHome"];
		}
		if (isset($data["KLStreet_id"])) {
			$whereArray[] = "LRS.KLStreet_id = :KLStreet_id";
			$params["KLStreet_id"] = $data["KLStreet_id"];
		}
		$whereString = (count($whereArray) != 0) ? "where " . implode(" and ", $whereArray) : "";
		$query = "
			select 
				LRS.Server_id as \"Server_id\",
				LR.Lpu_id as \"Lpu_id\",
				LR.LpuRegionType_id as \"LpuRegionType_id\",
				LRT.LpuRegionType_SysNick as \"LpuRegionType_SysNick\",
				LRS.LpuRegionStreet_id as \"LpuRegionStreet_id\",
				LRS.LpuRegion_id as \"LpuRegion_id\",
				LRS.KLCountry_id as \"KLCountry_id\",
				LRS.KLRGN_id as \"KLRGN_id\",
				LRS.KLSubRGN_id as \"KLSubRGN_id\",
				LRS.KLCity_id as \"KLCity_id\",
				LRS.KLTown_id as \"KLTown_id\",
				case COALESCE(LRS.KLTown_id, 0)
					when 0 then rtrim(c.KLArea_Name)||' '||COALESCE(cs.KLSocr_Nick, '')
					else rtrim(t.KLArea_Name)||' '||COALESCE(ts.KLSocr_Nick, '')
				end as \"KLTown_Name\",
				LRS.KLStreet_id as \"KLStreet_id\",
				rtrim(KLStreet_FullName) as \"KLStreet_Name\",
				LRS.LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\",
				COALESCE(LU.LpuUnit_Phone, LPU.Lpu_Phone, '') as \"phone\"
			from LpuRegionStreet LRS  
				left join v_LpuRegion LR on LR.LpuRegion_id = LRS.LpuRegion_id
				left join LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join KLArea t on t.KLArea_id = LRS.KLTown_id
				left join KLSocr ts on ts.KLSocr_id = t.KLSocr_id
				left join v_KLStreet KLStreet on KLStreet.KLStreet_id = LRS.KLStreet_id
				left join KLArea c on c.Klarea_id = LRS.KLCity_id
				left join KLSocr cs on cs.KLSocr_id = c.KLSocr_id
				left join v_Lpu LPU on LR.Lpu_id = LPU.Lpu_id
				left join v_LpuSection LS on LR.LpuSection_id = LS.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
			{$whereString}
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuPhoneMO(LpuStructure_model $callObject, $data)
	{
		if (empty($data["Lpu_id"])) {
			return false;
		}
		$params = [
			"Lpu_id" => $data["Lpu_id"]
		];
		$query = "
			SELECT 
				DISTINCT
				rtrim(lu.LpuUnit_Phone) as \"Phone\"
			FROM
				v_Lpu LL 
				left join v_LpuUnit lu on lu.Lpu_id = LL.Lpu_id
			WHERE lu.LpuUnit_Phone is not null
			  and lu.LpuUnitType_id=2   -- Тип группы отделений (Поликлиника)
			  and lu.LpuUnit_IsEnabled = 2   -- Признак активности
			  and LL.Lpu_id = :Lpu_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function getLpuStaffGridDetail(LpuStructure_model $callObject, $data)
	{
		if (empty($data['LpuStaff_id']) && empty($data['Lpu_id'])) {
			return [['LpuSectionBedState_cid' => null, 'Error_Code' => 1, 'Error_Msg' => 'Отсутсвтуют необходимые параметры.']];
		}
		$queryParams = [];
		if (!empty($data["LpuStaff_id"])) {
			$filterArray[] = "LpuStaff_id = :LpuStaff_id";
			$queryParams["LpuStaff_id"] = $data["LpuStaff_id"];
		} else {
			$filterArray[] = "Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select
				LpuStaff_id as \"LpuStaff_id\",
				to_char(LpuStaff_ApprovalDT::date, '{$callObject->dateTimeForm104}') as \"LpuStaff_ApprovalDT\",
				to_char(LpuStaff_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuStaff_begDate\",
				to_char(LpuStaff_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuStaff_endDate\",
				LpuStaff_Descript as \"LpuStaff_Descript\",
				LpuStaff_Num as \"LpuStaff_Num\",
				Staff_id as \"Staff_id\",
				Lpu_id as \"Lpu_id\"
			from dbo.v_LpuStaff
			{$whereString}
		";
		return $callObject->queryResult($sql, $queryParams);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @return mixed
	 */
	public static function getLpuListWithSmp(LpuStructure_model $callObject)
	{
		$sql = "
			SELECT DISTINCT
				l.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\"
			FROM
				v_Lpu l 
				LEFT JOIN v_LpuBuilding lb on( l.Lpu_id=lb.Lpu_id)
			WHERE l.Lpu_begDate <= tzgetdate()
			  and (l.Lpu_endDate is null or l.Lpu_endDate > tzgetdate())
			  and lb.LpuBuildingType_id = 27
			  and lb.LpuBuilding_begDate <= tzgetdate()
			  and (lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > tzgetdate())
			ORDER BY l.Lpu_Nick
		";
		return $callObject->db->query($sql)->result_array();
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getFpList(LpuStructure_model $callObject, $data)
	{
		$sql = "
			SELECT
				GetFP.FPID as \"FPID\",
				GetFP.CodeRu as \"CodeRu\",
				GetFP.NameRU as \"NameRU\"
			FROM
				r101.GetMO 
				INNER JOIN r101.GetFP on GetFP.MOID = GetMO.ID
			WHERE GetMO.Lpu_id = :Lpu_id
		";
		$sqlParams = [
			"Lpu_id" => $data["Lpu_id"]
		];
		return $callObject->queryResult($sql, $sqlParams);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @return array|false
	 */
	public static function getLpuSectionBedProfileLinkFed(LpuStructure_model $callObject)
	{
		$query = "
			select
				LpuSectionBedProfileLink_id as \"LpuSectionBedProfileLink_id\",
				LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				LpuSectionBedProfile_fedid as \"LpuSectionBedProfile_fedid\"
			from fed.LpuSectionBedProfileLink 
		";
		return $callObject->queryResult($query);
	}

    /**
     * Загрузка атрибутов отделения
     * @param $data
     * @return bool
     */
    public static function getLpuSectionAttributes(LpuStructure_model $callObject, $data) {
        if(empty($data['LpuSection_id'])) return false;
        $params = array('LpuSection_id' => $data['LpuSection_id']);
        $sql = "
			select
				AttributeSign_Code as \"AttributeSign_Code\"
			from AttributeSignValue ASV 
				inner join AttributeSign ATS on ATS.AttributeSign_id = ASV.AttributeSign_id
			where
				AttributeSignValue_TablePKey = :LpuSection_id
				and ATS.AttributeSign_TableName = 'dbo.LpuSection'
				and ATS.AttributeSign_Code = 13
		";
        $resp = $callObject->queryResult($sql, $params);
        return $resp;
    }
}