<?php


class LpuStructure_model_load
{
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDBedOperation(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$queryParams = [
			"LpuSectionBedState_id" => $data["LpuSectionBedState_id"]
		];
		if (isset($data["LpuSectionBedStateOper_id"])) {
			$filter .= " and LpuSectionBedStateOper_id = :LpuSectionBedStateOper_id";
			$queryParams["LpuSectionBedStateOper_id"] = $data["LpuSectionBedStateOper_id"];
		}
		$sql = "
			SELECT
			    LSBS.LpuSectionBedStateOper_id as \"LpuSectionBedStateOper_id\",
			    LSBS.LpuSectionBedState_id as \"LpuSectionBedState_id\",
                LSBS.DBedOperation_id as \"DBedOperation_id\",
                DBO.DBedOperation_Name as \"DBedOperation_Name\",
                to_char(LSBS.LpuSectionBedStateOper_OperDT::date, '{$callObject->dateTimeForm104}') as \"LpuSectionBedStateOper_OperDT\"
			FROM
				fed.v_LpuSectionBedStateOper LSBS 
				left join fed.DBedOperation DBO  on DBO.DBedOperation_id = LSBS.DBedOperation_id
			WHERE LSBS.LpuSectionBedState_id = :LpuSectionBedState_id {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function loadForenCorpServingMedServices(LpuStructure_model $callObject, $data)
	{
		if (empty($data['MedService_id'])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор обслуживаемого отделения");
		}
		$callObject->load->model("MedServiceLink_model", "msl_model");
		$resultArray = ["success" => true];
		$MedServiceLinkParams = [
			["field" => "MedService_ForenCrim_id", "MedServiceLinkType_id" => 7],
			["field" => "MedService_ForenChem_id", "MedServiceLinkType_id" => 11],
			["field" => "MedService_ForenHist_id", "MedServiceLinkType_id" => 13],
			["field" => "MedService_ForenBio_id", "MedServiceLinkType_id" => 12],
		];
		foreach ($MedServiceLinkParams as $param) {
			$selectResult = $callObject->msl_model->loadList([
				"MedService_id" => $data["MedService_id"],
				"MedServiceLinkType_id" => $param["MedServiceLinkType_id"]
			]);
			if (is_array($selectResult) && sizeof($selectResult) == 0) {
				$check = true;
			} else {
				$check = !(!$selectResult || (is_array($selectResult) && isset($selectResult[0]) && isset($selectResult[0]["Error_Msg"]) && !empty($selectResult[0]["Error_Msg"])));
			}
			if (!$check) {
				return $selectResult;
			}
			$resultArray["{$param['field']}"] = (isset($selectResult[0])) ? $selectResult[0]["MedService_lid"] : null;

		}
		return [$resultArray];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function loadForenHistServingMedServices(LpuStructure_model $callObject, $data)
	{
		if (empty($data["MedService_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор обслуживаемого отделения");
		}
		$callObject->load->model("MedServiceLink_model", "msl_model");
		$resultArray = ["success" => true];
		$MedServiceLinkParams = [
			["field" => "MedService_ForenChem_id", "MedServiceLinkType_id" => 11],
		];
		foreach ($MedServiceLinkParams as $param) {
			$selectResult = $callObject->msl_model->loadList([
				"MedService_id" => $data["MedService_id"],
				"MedServiceLinkType_id" => $param["MedServiceLinkType_id"]
			]);
			if (is_array($selectResult) && sizeof($selectResult) == 0) {
				$check = true;
			} else {
				$check = !(!$selectResult || (is_array($selectResult) && isset($selectResult[0]) && isset($selectResult[0]["Error_Msg"]) && !empty($selectResult[0]["Error_Msg"])));
			}
			if (!$check) {
				return $selectResult;
			}
			$resultArray["{$param['field']}"] = (isset($selectResult[0])) ? $selectResult[0]["MedService_lid"] : null;
		}
		return [$resultArray];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadSectionAverageDurationGrid(LpuStructure_model $callObject, $data)
	{
		$query = "
			SELECT
				sad.SectionAverageDuration_id as \"SectionAverageDuration_id\",
				sad.LpuSection_id as \"LpuSection_id\",
				sad.SectionAverageDuration_Duration as \"SectionAverageDuration_Duration\",
				to_char(sad.SectionAverageDuration_begDate::date, '{$callObject->dateTimeForm104}') as \"SectionAverageDuration_begDate\",
				to_char(sad.SectionAverageDuration_endDate::date, '{$callObject->dateTimeForm104}') as \"SectionAverageDuration_endDate\"
			from r10.v_SectionAverageDuration SAD 
			where sad.LpuSection_id = :LpuSection_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
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
	public static function loadLpuBuildingType(LpuStructure_model $callObject, $data)
	{
		$query = "
			SELECT LpuBuildingType_id as \"LpuBuildingType_id\"
			from v_LpuBuilding 
			where LpuBuilding_id = :LpuBuilding_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadLpuMseLinkForm(LpuStructure_model $callObject, $data)
	{
		$params = ["LpuMseLink_id" => $data["LpuMseLink_id"]];
		$query = "
			select 
				LML.LpuMseLink_id as \"LpuMseLink_id\",
				LML.Lpu_id as \"Lpu_oid\",
				LML.Lpu_bid as \"Lpu_bid\",
				LML.MedService_id as \"MedService_id\",
				to_char(LML.LpuMseLink_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuMseLink_begDate\",
				to_char(LML.LpuMseLink_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuMseLink_endDate\"
			from v_LpuMseLink LML 
			where LML.LpuMseLink_id = :LpuMseLink_id
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLpuMseLinkGrid(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		$params = [];
		if (!empty($data["Lpu_bid"])) {
			$filterArray[] = "bL.Lpu_id = :Lpu_bid";
			$params["Lpu_bid"] = $data["Lpu_bid"];
		}
		if (!empty($data["Lpu_oid"])) {
			$filterArray[] = "L.Lpu_id = :Lpu_oid";
			$params["Lpu_oid"] = $data["Lpu_oid"];
		}
		if (!empty($data["LpuMseLink_begDate"])) {
			$filterArray[] = "LML.LpuMseLink_begDate = :LpuMseLink_begDate";
			$params["LpuMseLink_begDate"] = $data["LpuMseLink_begDate"];
		}
		if (!empty($data["LpuMseLink_endDate"])) {
			$filterArray[] = "LML.LpuMseLink_endDate = :LpuMseLink_endDate";
			$params["LpuMseLink_endDate"] = $data["LpuMseLink_endDate"];
		}
		if (!empty($data["MedService_id"])) {
			$filterArray[] = "LML.MedService_id = :MedService_id";
			$params["MedService_id"] = $data["MedService_id"];
		}
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filterArray[] = "(LML.LpuMseLink_endDate is null or LML.LpuMseLink_endDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filterArray[] = "LML.LpuMseLink_endDate <= tzgetdate()";
		}
		$whereString = (count($filterArray) != 0) ? "and " .implode(" and ", $filterArray) : "";
		$query = "
			select
			    -- select
				LML.LpuMseLink_id as \"LpuMseLink_id\",
				LML.MedService_id as \"MedService_id\",
				L.Lpu_id as \"Lpu_oid\",
				L.Lpu_Nick as \"Lpu_Nick\",
				bL.Lpu_id as \"Lpu_bid\",
				bL.Lpu_Nick as \"Lpu_bNick\",
				MS.MedService_Nick as \"MedService_Nick\",
				to_char(LML.LpuMseLink_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuMseLink_begDate\",
				to_char(LML.LpuMseLink_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuMseLink_endDate\"
			    --end select
			from
			    --from
				v_LpuMseLink LML 
				inner join v_Lpu bL  on bL.Lpu_id = LML.Lpu_bid
				inner join v_Lpu L  on L.Lpu_id = LML.Lpu_id
				left join v_MedService MS  on LML.MedService_id = MS.MedService_id
				--end from
			where
			    --where
                (1 = 1)
                {$whereString}
                --end where
			order by 
			    --order by
			    LML.LpuMseLink_begDate
			    --end order by
		";
		$result = $callObject->queryResult(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->queryResult(getCountSQLPH($query), $params);
		if (!is_array($result) || !is_array($result_count)) {
			return false;
		}
		return [
			"totalCount" => $result_count[0]["cnt"],
			"data" => $result
		];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLpuRegionInfo(LpuStructure_model $callObject, $data)
	{
		$params = ["LpuRegion_id" => $data["LpuRegion_id"]];
		$query = "
			select
				coalesce(LR.LpuSection_id, 0) as \"LpuSection_id\",
				coalesce(LS.LpuBuilding_id, 0) as \"LpuBuilding_id\",
				coalesce(MSR.MedStaffRegion_id, 0) as \"MedStaffRegion_id\"
			from v_LpuRegion LR
			left join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
			left join v_MedStaffRegion MSR on (
				MSR.LpuRegion_id = LR.LpuRegion_id and
				(MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate > tzgetdate()) and
				coalesce(MSR.MedStaffRegion_isMain, 0) = 2
			)
			where LR.LpuRegion_id = :LpuRegion_id
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
	public static function loadLpuSectionCodeList(LpuStructure_model $callObject, $data)
	{
		$filterList = [];
		$queryParams = [];
		if (!empty($data["LpuUnitType_id"]) || !empty($data["LpuSectionProfile_id"])) {
			switch ($data["session"]["region"]["nick"]) {
				case "pskov":
					$scheme = "r{$data['session']['region']['number']}";
					break;
				default:
					$scheme = $callObject->_scheme;
					break;
			}
			$filter = "
				exists (
					select LpuSectionCodeLink_id
					from {$scheme}.LpuSectionCodeLink 
					where LpuSectionCode_id = lsc.LpuSectionCode_id
			";
			if (!empty($data["LpuUnitType_id"])) {
				$filter .= " and LpuUnitType_id = :LpuUnitType_id";
				$queryParams["LpuUnitType_id"] = $data["LpuUnitType_id"];
			}
			if (!empty($data["LpuSectionProfile_id"])) {
				$filter .= " and coalesce(LpuSectionProfile_id, :LpuSectionProfile_id) = :LpuSectionProfile_id";
				$queryParams["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
			}
			$filter .= " limit 1)";
			$filterList[] = $filter;
		}
		if (!empty($data['LpuSectionCode_id'])) {
			$filterList[] = "lsc.LpuSectionCode_id = :LpuSectionCode_id";
			$queryParams['LpuSectionCode_id'] = $data['LpuSectionCode_id'];
		}

		if ( !empty($data['LpuSectionCode_begDate']) ) {
			$filterList[] = "(lsc.LpuSectionCode_endDT is null or lsc.LpuSectionCode_endDT >= :LpuSectionCode_begDate)";
			$queryParams['LpuSectionCode_begDate'] = $data['LpuSectionCode_begDate'];
		}

		if ( !empty($data['LpuSectionCode_endDate']) ) {
			$filterList[] = "(lsc.LpuSectionCode_begDT is null or lsc.LpuSectionCode_begDT <= :LpuSectionCode_endDate)";
			$queryParams['LpuSectionCode_endDate'] = $data['LpuSectionCode_endDate'];
		}

		$whereString = (count($filterList) != 0) ? "where " . implode(' and ', $filterList) : "";
		$query = "
			select
				lsc.LpuSectionCode_id as \"LpuSectionCode_id\",
				lsc.LpuSectionCode_Code as \"LpuSectionCode_Code\",
				lsc.LpuSectionCode_Name as \"LpuSectionCode_Name\",
				to_char(lsc.LpuSectionCode_begDT, 'DD.MM.YYYY') AS \"puSectionCode_begDT\",
				to_char(lsc.LpuSectionCode_endDT, 'DD.MM.YYYY') AS \"puSectionCode_endDT\"
			from
				v_LpuSectionCode lsc 
			{$whereString}
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
	public static function loadLpuSectionLpuSectionProfileGrid(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				lslsp.LpuSectionLpuSectionProfile_id as \"LpuSectionLpuSectionProfile_id\",
			    lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			    lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
			    lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			    to_char(lslsp.LpuSectionLpuSectionProfile_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionLpuSectionProfile_begDate\",
			    to_char(lslsp.LpuSectionLpuSectionProfile_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionLpuSectionProfile_endDate\",
			    1 as \"RecordStatus_Code\"
			from
				dbo.v_LpuSectionLpuSectionProfile lslsp 
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
			where lslsp.LpuSection_id = :LpuSection_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
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
	public static function loadLpuSectionProfileList(LpuStructure_model $callObject, $data)
	{
		$params = [];
		$list = [];
		if (!empty($data["LpuSection_ids"])) {
			$list = json_decode($data["LpuSection_ids"]);
		}
		if (!empty($data["LpuSection_id"])) {
			$list = array($data["LpuSection_id"]);
		}
		if (count($list) == 0) {
			return [];
		}
		$list_str = implode(",", $list);
		$query = "
			select
				lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\"
			from
				v_LpuSection ls 
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			where ls.LpuSection_id in ({$list_str})
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
	public static function loadLpuSectionServiceGrid(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				LSS.LpuSectionService_id as \"LpuSectionService_id\",
				1 as \"RecordStatus_Code\",
				LSS.LpuSection_id as \"LpuSection_id\",
				LSS.LpuSection_did as \"LpuSection_did\",
				dLS.LpuSection_FullName||', '||dLUT.LpuUnitType_Name as \"LpuSection_Name\",
				dLB.LpuBuilding_Code||'. '||dLB.LpuBuilding_Name as \"LpuBuilding_Name\"
			from
				v_LpuSectionService LSS 
				left join v_LpuSection dLS on dLS.LpuSection_id = LSS.LpuSection_did
				left join v_LpuUnit dLU on dLU.LpuUnit_id = dLS.LpuUnit_id
				left join v_LpuUnitType dLUT on dLUT.LpuUnitType_id = dLU.LpuUnitType_id
				left join v_LpuBuilding dLB on dLB.LpuBuilding_id = dLU.LpuBuilding_id
			where LSS.LpuSection_id = :LpuSection_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
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
	public static function loadLpuSectionWardComfortLink(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$queryParams = [
			"LpuSectionWard_id" => $data["LpuSectionWard_id"]
		];
		if (isset($data["LpuSectionWardComfortLink_id"])) {
			$filter .= " and LpuSectionWardComfortLink_id = :LpuSectionWardComfortLink_id";
			$queryParams["LpuSectionWardComfortLink_id"] = $data["LpuSectionWardComfortLink_id"];
		}
		$sql = "
			SELECT
			    LSWCL.DChamberComfort_id as \"DChamberComfort_id\",
			    DCC.DChamberComfort_Name as \"DChamberComfort_Name\",
                LSWCL.LpuSectionWard_id as \"LpuSectionWard_id\",
                LSWCL.LpuSectionWardComfortLink_Count as \"LpuSectionWardComfortLink_Count\",
                LSWCL.LpuSectionWardComfortLink_id as \"LpuSectionWardComfortLink_id\"
			FROM
				fed.v_LpuSectionWardComfortLink LSWCL 
				left join fed.DChamberComfort DCC on DCC.DChamberComfort_id = LSWCL.DChamberComfort_id
			WHERE LSWCL.LpuSectionWard_id = :LpuSectionWard_id {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}


	//LpuStructure_model $callObject, 
}