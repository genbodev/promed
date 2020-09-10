<?php


class LpuStructure_model_saveCommon
{
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveForenCorpServingMedServices(LpuStructure_model $callObject, $data)
	{
		if (empty($data["MedService_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор обслуживаемого отделения");
		}
		if (empty($data["MedService_ForenCrim_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор медико-криминалистической службы");
		}
		if (empty($data["MedService_ForenChem_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор судебно-химической службы");
		}
		if (empty($data["MedService_ForenHist_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор судебно-гистологической службы");
		}
		if (empty($data["MedService_ForenBio_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор судебно-гистологической службы");
		}
		$callObject->load->model("MedServiceLink_model", "msl_model");
		$callObject->beginTransaction();
		//
		// Сначала удалим предыдущие связи
		//
		$MedServiceLinkParams = [
			["field" => "MedService_ForenCrim_id", "MedServiceLinkType_id" => 7],
			["field" => "MedService_ForenChem_id", "MedServiceLinkType_id" => 11],
			["field" => "MedService_ForenHist_id", "MedServiceLinkType_id" => 13],
			["field" => "MedService_ForenBio_id", "MedServiceLinkType_id" => 12],
		];
		//Получаем все записи по каждому типу связи служб
		foreach ($MedServiceLinkParams as $param) {
			$callObject->msl_model->setMedServiceLinkType_id($param["MedServiceLinkType_id"]);
			$selectResult = $callObject->msl_model->loadList([
				"MedService_id" => $data["MedService_id"],
				"MedServiceLinkType_id" => $param["MedServiceLinkType_id"]
			]);
			if (is_array($selectResult) && sizeof($selectResult) == 0) {
				$checkResult = true;
			} else {
				$checkResult = !(!$selectResult || (is_array($selectResult) && isset($selectResult[0]) && isset($selectResult[0]["Error_Msg"]) && !empty($selectResult[0]["Error_Msg"])));
			}
			if (!$checkResult) {
				$callObject->rollbackTransaction();
				return $selectResult;
			} else {
				//Удаляем все полученные связи
				foreach ($selectResult as $key => $value) {
					$callObject->msl_model->setMedServiceLink_id($value['MedServiceLink_id']);
					$deleteResult = $callObject->msl_model->delete();
					if (is_array($deleteResult) && sizeof($deleteResult) == 0) {
						$checkResult = true;
					} else {
						$checkResult = !(!$deleteResult || (is_array($deleteResult) && isset($deleteResult[0]) && isset($deleteResult[0]["Error_Msg"]) && !empty($deleteResult[0]["Error_Msg"])));
					}
					if (!$checkResult) {
						$callObject->rollbackTransaction();
						return $deleteResult;
					}
				}
			}
		}
		$callObject->msl_model->setMedService_id($data["MedService_id"]);
		foreach ($MedServiceLinkParams as $param) {
			$callObject->msl_model->setMedServiceLinkType_id($param["MedServiceLinkType_id"]);
			$callObject->msl_model->setMedService_lid($data[$param["field"]]);
			$saveResult = $callObject->msl_model->save();
			if (is_array($saveResult) && sizeof($saveResult) == 0) {
				$checkResult = true;
			} else {
				$checkResult = !(!$saveResult || (is_array($saveResult) && isset($saveResult[0]) && isset($saveResult[0]["Error_Msg"]) && !empty($saveResult[0]["Error_Msg"])));
			}
			if (!$checkResult) {
				$callObject->rollbackTransaction();
				return $saveResult;
			}
			$callObject->msl_model->setMedServiceLink_id(null);
		}
		$callObject->commitTransaction();
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveForenHistServingMedServices(LpuStructure_model $callObject, $data)
	{
		$checkResult = function ($data = array()) {
			if (is_array($data) && sizeof($data) == 0) {
				return true;
			} else
				return !(!$data || (is_array($data) && isset($data[0]) && isset($data[0]["Error_Msg"]) && !empty($data[0]["Error_Msg"])));
		};
		if (empty($data["MedService_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор обслуживаемого отделения");
		}
		if (empty($data["MedService_ForenChem_id"])) {
			throw new Exception("Не задан обязательный параметр: Идентификатор судебно-химической службы");
		}
		$callObject->load->model("MedServiceLink_model", "msl_model");
		$callObject->beginTransaction();
		// Сначала удалим предыдущие связи
		$MedServiceLinkParams = [
			["field" => "MedService_ForenChem_id", "MedServiceLinkType_id" => 11],
		];
		//Получаем все записи по каждому типу связи служб
		foreach ($MedServiceLinkParams as $param) {
			$callObject->msl_model->setMedServiceLinkType_id($param["MedServiceLinkType_id"]);
			$selectResult = $callObject->msl_model->loadList([
				"MedService_id" => $data["MedService_id"],
				"MedServiceLinkType_id" => $param["MedServiceLinkType_id"]
			]);
			if (is_array($selectResult) && sizeof($selectResult) == 0) {
				$checkResult = true;
			} else {
				$checkResult = !(!$selectResult || (is_array($selectResult) && isset($selectResult[0]) && isset($selectResult[0]["Error_Msg"]) && !empty($selectResult[0]["Error_Msg"])));
			}
			if (!$checkResult) {
				$callObject->rollbackTransaction();
				return $selectResult;
			} else {
				//Удаляем все полученные связи
				foreach ($selectResult as $key => $value) {
					$callObject->msl_model->setMedServiceLink_id($value['MedServiceLink_id']);
					$deleteResult = $callObject->msl_model->delete();
					if (is_array($deleteResult) && sizeof($deleteResult) == 0) {
						$checkResult = true;
					} else {
						$checkResult = !(!$deleteResult || (is_array($deleteResult) && isset($deleteResult[0]) && isset($deleteResult[0]["Error_Msg"]) && !empty($deleteResult[0]["Error_Msg"])));
					}
					if (!$checkResult) {
						$callObject->rollbackTransaction();
						return $deleteResult;
					}
				}
			}
		}
		$callObject->msl_model->setMedService_id($data["MedService_id"]);
		foreach ($MedServiceLinkParams as $param) {
			$callObject->msl_model->setMedServiceLinkType_id($param["MedServiceLinkType_id"]);
			$callObject->msl_model->setMedService_lid($data[$param["field"]]);
			$saveResult = $callObject->msl_model->save();
			if (is_array($saveResult) && sizeof($saveResult) == 0) {
				$checkResult = true;
			} else {
				$checkResult = !(!$saveResult || (is_array($saveResult) && isset($saveResult[0]) && isset($saveResult[0]["Error_Msg"]) && !empty($saveResult[0]["Error_Msg"])));
			}
			if (!$checkResult) {
				$callObject->rollbackTransaction();
				return $saveResult;
			}
			$callObject->msl_model->setMedServiceLink_id(null);
		}
		$callObject->commitTransaction();
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveLinkFDServiceToRCCService(LpuStructure_model $callObject, $data)
	{
		$queryParams = [];
		if (!isset($data["MedService_FDid"])) {
			return [['success' => false, 'Error_Code' => '','Error_Msg' => 'Не указан идентификатор службы ФД.']];
		} else {
			$queryParams["MedService_FDid"] = $data["MedService_FDid"];
		}
		if (!isset($data["MedService_RCCid"])) {
			return [['success' => false, 'Error_Code' => '','Error_Msg' => 'Не указан идентификатор службы ЦУК.']];
		} else {
			$queryParams["MedService_RCCid"] = $data["MedService_RCCid"];
		}
		//3 - тип связи служб
		$queryParams["MedServiceLinkType_Code"] = 3;
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		$queryParams["MedServiceLink_id"] = (isset($data["MedServiceLink_id"])) ? $data["MedServiceLink_id"] : null;
		$procedure = is_null($queryParams["MedServiceLink_id"]) ? "p_MedServiceLink_ins" : "p_MedServiceLink_upd";
		//TODO 111
		$query = "
			DECLARE
			@Res bigint,
			@Error_Code bigint,
			@Error_Message varchar(4000),
			@MedServiceLinkType_id bigint,
			@SQLstring nvarchar(500),
			@ParamDefinition nvarchar(500);

			SET @SQLString =
				N'SELECT DISTINCT @MedServiceLinkType_id = MSLT.MedServiceLinkType_id
				FROM v_MedServiceLinkType MSLT 

				WHERE MSLT.MedServiceLinkType_Code = @MedServiceLinkType_Code ';

			SET @ParamDefinition = N'@MedServiceLinkType_id bigint OUTPUT, @MedServiceLinkType_Code bigint';

			exec sp_executesql
				@SQLString,
				@ParamDefinition,
				@MedServiceLinkType_Code = :MedServiceLinkType_Code,
				@MedServiceLinkType_id = @MedServiceLinkType_id OUTPUT;

			SET @Res = :MedServiceLink_id;

			EXEC  {$procedure}
				@MedServiceLink_id = @Res output,
				@MedServiceLinkType_id = @MedServiceLinkType_id,
				@MedService_id = :MedService_RCCid,
				@MedService_lid = :MedService_FDid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			SELECT	@Res as MedServiceLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;

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
	public static function saveLpuBuildingAdditionalParams(LpuStructure_model $callObject, $data)
	{
		if (!array_key_exists('LpuBuilding_id', $data) || !$data['LpuBuilding_id']) {
			throw new Exception("Не указан идентификатор подразделения");
		}
		$sql = "
	        select
				LpuBuilding_id as \"LpuBuilding_id\",
	            LpuBuilding_IsPrint as \"LpuBuilding_IsPrint\",
	            coalesce(LpuBuildingSmsType_id, 1) as \"LpuBuildingSmsType_id\",
	            LpuBuilding_setDefaultAddressCity as \"LpuBuilding_setDefaultAddressCity\",
	            LpuBuilding_IsEmergencyTeamDelay as \"LpuBuilding_IsEmergencyTeamDelay\",
	            LpuBuilding_IsCallCancel as \"LpuBuilding_IsCallCancel\",
	            LpuBuilding_IsCallDouble as \"LpuBuilding_IsCallDouble\",
	            LpuBuilding_IsCallSpecTeam as \"LpuBuilding_IsCallSpecTeam\",
	            LpuBuilding_IsCallReason as \"LpuBuilding_IsCallReason\",
	            LpuBuilding_IsWithoutBalance as \"LpuBuilding_IsWithoutBalance\",
	            LpuBuilding_IsDenyCallAnswerDoc as \"LpuBuilding_IsDenyCallAnswerDoc\",
	            Lpu_id as \"Lpu_id\",
	            LpuBuildingType_id as \"LpuBuildingType_id\",
	            LpuBuilding_Code as \"LpuBuilding_Code\",
	            LpuBuilding_begDate as \"LpuBuilding_begDate\",
	            LpuBuilding_endDate as \"LpuBuilding_endDate\",
	            LpuBuilding_Nick as \"LpuBuilding_Nick\",
	            LpuBuilding_Name as \"LpuBuilding_Name\",
	            LpuBuilding_WorkTime as \"LpuBuilding_WorkTime\",
	            LpuBuilding_RoutePlan as \"LpuBuilding_RoutePlan\",
	            Address_id as \"Address_id\",
	            PAddress_id as \"PAddress_id\",
	            LpuLevel_id as \"LpuLevel_id\",
	            LpuLevel_cid as \"LpuLevel_cid\",
	            Server_id as \"Server_id\",
	            LpuBuilding_IsExport as \"LpuBuilding_IsExport\",
	            LpuBuilding_CmpStationCode as \"LpuBuilding_CmpStationCode\",
	            LpuBuilding_CmpSubstationCode as \"LpuBuilding_CmpSubstationCode\",
	            LpuBuilding_Longitude as \"LpuBuilding_Longitude\",
	            LpuBuilding_Latitude as \"LpuBuilding_Latitude\"
	        from v_LpuBuilding
	        where LpuBuilding_id = :LpuBuilding_id
        ";
		$sqlParams = [
			"LpuBuilding_id" => $data["LpuBuilding_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		$res = $result->result('array');
		if (count($res) > 0) {
			$res = $res[0];
		}
		$queryParams = [
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"LpuBuilding_IsPrint" => $data["LpuBuilding_IsPrint"] == "true" ? 2 : 1,
			"LpuBuildingSmsType_id" => $data["LpuBuildingSmsType_id"],
			"LpuBuilding_setDefaultAddressCity" => $data["LpuBuilding_setDefaultAddressCity"] == "true" ? 2 : 1,
			"LpuBuilding_IsEmergencyTeamDelay" => $data["LpuBuilding_IsEmergencyTeamDelay"] == "true" ? 2 : 1,
			"LpuBuilding_IsCallCancel" => $data["LpuBuilding_IsCallCancel"] == "true" ? 2 : 1,
			"LpuBuilding_IsCallDouble" => $data["LpuBuilding_IsCallDouble"] == "true" ? 2 : 1,
			"LpuBuilding_IsCallSpecTeam" => $data["LpuBuilding_IsCallSpecTeam"] == "true" ? 2 : 1,
			"LpuBuilding_IsCallReason" => $data["LpuBuilding_IsCallReason"] == "true" ? 2 : 1,
			"LpuBuilding_IsUsingMicrophone" => $data["LpuBuilding_IsUsingMicrophone"] == "true" ? 2 : 1,
			"LpuBuilding_IsWithoutBalance" => $data["LpuBuilding_IsWithoutBalance"] == "true" ? 2 : 1,
			"LpuBuilding_IsDenyCallAnswerDoc" => $data["LpuBuilding_IsDenyCallAnswerDoc"] == "true" ? 2 : 1,
			"Lpu_id" => $res["Lpu_id"],
			"LpuBuildingType_id" => $res["LpuBuildingType_id"],
			"LpuBuilding_Code" => $res["LpuBuilding_Code"],
			"LpuBuilding_begDate" => $res["LpuBuilding_begDate"],
			"LpuBuilding_endDate" => $res["LpuBuilding_endDate"],
			"LpuBuilding_Nick" => $res["LpuBuilding_Nick"],
			"LpuBuilding_Name" => $res["LpuBuilding_Name"],
			"LpuBuilding_WorkTime" => $res["LpuBuilding_WorkTime"],
			"LpuBuilding_RoutePlan" => $res["LpuBuilding_RoutePlan"],
			"Address_id" => $res["Address_id"],
			"PAddress_id" => $res["PAddress_id"],
			"LpuLevel_id" => $res["LpuLevel_id"],
			"LpuLevel_cid" => $res["LpuLevel_cid"],
			"pmUser_id" => $data["pmUser_id"],
			"Server_id" => $res["Server_id"],
			"LpuBuilding_IsExport" => $res["LpuBuilding_IsExport"],
			"LpuBuilding_CmpStationCode" => $res["LpuBuilding_CmpStationCode"],
			"LpuBuilding_CmpSubstationCode" => $res["LpuBuilding_CmpSubstationCode"],
			"LpuBuilding_Longitude" => $res["LpuBuilding_Longitude"],
			"LpuBuilding_Latitude" => $res["LpuBuilding_Latitude"]
		];
		$query = "
			select
				LpuBuilding_id as \"LpuBuilding_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_lpubuilding_upd(
			    server_id := :Server_id,
			    lpubuilding_id := :LpuBuilding_id,
			    lpu_id := :Lpu_id,
			    lpubuildingtype_id := :LpuBuildingType_id,
			    address_id := :Address_id,
			    lpubuilding_code := :LpuBuilding_Code,
			    lpubuilding_name := :LpuBuilding_Name,
			    lpubuilding_nick := :LpuBuilding_Nick,
			    lpubuilding_worktime := :LpuBuilding_WorkTime,
			    lpubuilding_routeplan := :LpuBuilding_RoutePlan,
			    lpubuilding_begdate := :LpuBuilding_begDate,
			    lpubuilding_enddate := :LpuBuilding_endDate,
			    paddress_id := :PAddress_id,
			    lpulevel_id := :LpuLevel_id,
			    lpulevel_cid := :LpuLevel_cid,
			    lpubuilding_isexport := :LpuBuilding_IsExport,
			    lpubuilding_cmpstationcode := :LpuBuilding_CmpStationCode,
			    lpubuilding_cmpsubstationcode := :LpuBuilding_CmpSubstationCode,
			    lpubuilding_latitude := :LpuBuilding_Latitude,
			    lpubuilding_longitude := :LpuBuilding_Longitude,
			    lpubuilding_setdefaultaddresscity := :LpuBuilding_setDefaultAddressCity,
			    lpubuilding_iscallcancel := :LpuBuilding_IsCallCancel,
			    lpubuilding_iscalldouble := :LpuBuilding_IsCallDouble,
			    lpubuilding_iscallspecteam := :LpuBuilding_IsCallSpecTeam,
			    lpubuilding_isemergencyteamdelay := :LpuBuilding_IsEmergencyTeamDelay,
			    lpubuilding_iscallreason := :LpuBuilding_IsCallReason,
			    lpubuilding_isprint := :LpuBuilding_IsPrint,
			    lpubuilding_isusingmicrophone := :LpuBuilding_IsUsingMicrophone,
			    lpubuilding_iswithoutbalance := :LpuBuilding_IsWithoutBalance,
			    lpubuildingsmstype_id := :LpuBuildingSmsType_id,
			    lpubuilding_isdenycallanswerdoc := :LpuBuilding_IsDenyCallAnswerDoc,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $queryParams);
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
	public static function saveLpuMseLink(LpuStructure_model $callObject, $data)
	{
		$params = [
			"LpuMseLink_id" => !empty($data["LpuMseLink_id"]) ? $data["LpuMseLink_id"] : null,
			"Lpu_oid" => $data["Lpu_oid"],
			"Lpu_bid" => $data["Lpu_bid"],
			"MedService_id" => $data["MedService_id"],
			"LpuMseLink_begDate" => $data["LpuMseLink_begDate"],
			"LpuMseLink_endDate" => !empty($data["LpuMseLink_endDate"]) ? $data["LpuMseLink_endDate"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		if (!empty($params["LpuMseLink_endDate"]) && date_create($params["LpuMseLink_begDate"]) > date_create($params["LpuMseLink_endDate"])) {
			throw new Exception("Дата закрытия не может быть меньше даты отрытия");
		}
		$query = "
			select count(*) as cnt
			from v_LpuMseLink
			where LpuMseLink_id != coalesce(:LpuMseLink_id, 0)
			  and MedService_id = :MedService_id
			  and Lpu_id = :Lpu_oid
			  and LpuMseLink_begDate < coalesce(:LpuMseLink_endDate, (select tzgetdate() + (100||' years')::interval))
			  and coalesce(LpuMseLink_endDate, (select tzgetdate() + (100||' years')::interval)) > :LpuMseLink_begDate
			limit 1
		";
		$count = $callObject->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			throw new Exception("Ошибка при проверке существования связей");
		}
		if ($count > 0) {
			throw new Exception("Связь Бюро МСЭ с данной МО уже существует");
		}
		$procedure = (empty($params['LpuMseLink_id'])) ? "p_LpuMseLink_ins" : "p_LpuMseLink_upd";
		$query = "
			select 
				LpuMseLink_id as \"LpuMseLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
			    lpumselink_id := :LpuMseLink_id,
			    lpu_bid := :Lpu_bid,
			    lpu_id := :Lpu_oid,
			    lpumselink_begdate := :LpuMseLink_begDate,
			    lpumselink_enddate := :LpuMseLink_endDate,
			    medservice_id := :MedService_id,
			    pmuser_id := :pmUser_id
			);
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveLpuSectionLpuSectionProfile(LpuStructure_model $callObject, $data)
	{
		$procedure = (!empty($data['LpuSectionLpuSectionProfile_id']) && $data['LpuSectionLpuSectionProfile_id'] > 0)?"p_LpuSectionLpuSectionProfile_upd":"p_LpuSectionLpuSectionProfile_ins";
		$query = "
			select
				LpuSectionLpuSectionProfile_id as \"LpuSectionLpuSectionProfile_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
			    server_id := :Server_id,
			    lpusectionlpusectionprofile_id := :LpuSectionLpuSectionProfile_id,
			    lpusection_id := :LpuSection_id,
			    lpusectionprofile_id := :LpuSectionProfile_id,
			    lpusectionlpusectionprofile_begdate := :LpuSectionLpuSectionProfile_begDate,
			    lpusectionlpusectionprofile_enddate := :LpuSectionLpuSectionProfile_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionLpuSectionProfile_id" => (!empty($data["LpuSectionLpuSectionProfile_id"]) && $data["LpuSectionLpuSectionProfile_id"] > 0 ? $data["LpuSectionLpuSectionProfile_id"] : NULL),
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSectionProfile_id" => $data["LpuSectionProfile_id"],
			"LpuSectionLpuSectionProfile_begDate" => $data["LpuSectionLpuSectionProfile_begDate"],
			"LpuSectionLpuSectionProfile_endDate" => (!empty($data["LpuSectionLpuSectionProfile_endDate"]) ? $data["LpuSectionLpuSectionProfile_endDate"] : NULL),
			"pmUser_id" => $data["pmUser_id"]
		];
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
	public static function saveLpuSectionMedicalCareKind(LpuStructure_model $callObject, $data)
	{
		$procedure = (!empty($data['LpuSectionMedicalCareKind_id']) && $data['LpuSectionMedicalCareKind_id'] > 0)?"p_LpuSectionMedicalCareKind_upd":"p_LpuSectionMedicalCareKind_ins";
		$query = "
			select
				LpuSectionMedicalCareKind_id as \"LpuSectionMedicalCareKind_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
			    server_id := :Server_id,
			    lpusectionmedicalcarekind_id := :LpuSectionMedicalCareKind_id,
			    lpusection_id := :LpuSection_id,
			    medicalcarekind_id := :MedicalCareKind_id,
			    lpusectionmedicalcarekind_begdate := :LpuSectionMedicalCareKind_begDate,
			    lpusectionmedicalcarekind_enddate := :LpuSectionMedicalCareKind_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionMedicalCareKind_id" => (!empty($data["LpuSectionMedicalCareKind_id"]) && $data["LpuSectionMedicalCareKind_id"] > 0 ? $data["LpuSectionMedicalCareKind_id"] : NULL),
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"MedicalCareKind_id" => $data["MedicalCareKind_id"],
			"LpuSectionMedicalCareKind_begDate" => $data["LpuSectionMedicalCareKind_begDate"],
			"LpuSectionMedicalCareKind_endDate" => (!empty($data["LpuSectionMedicalCareKind_endDate"]) ? $data["LpuSectionMedicalCareKind_endDate"] : NULL),
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение таймеров подстанции
	 */
	public static function saveSmpUnitTimes(LpuStructure_model $callObject, $data)
	{
		$sql = "
			select 
				SmpUnitTimes_id as \"SmpUnitTimes_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_smpunittimes_insonduplicatekey(
			    smpunittimes_id := :SmpUnitTimes_id,
			    lpubuilding_id := :LpuBuilding_id,
			    mintimesmp := :minTimeSMP,
			    maxtimesmp := :maxTimeSMP,
			    mintimenmp := :minTimeNMP,
			    maxtimenmp := :maxTimeNMP,
			    minresponsetimenmp := :minResponseTimeNMP,
			    maxresponsetimenmp := :maxResponseTimeNMP,
			    minresponsetimeet := :minResponseTimeET,
			    maxresponsetimeet := :maxResponseTimeET,
			    arrivaltimeet := :ArrivalTimeET,
			    servicetimeet := :ServiceTimeET,
			    dispatchtimeet := :DispatchTimeET,
			    lunchtimeet := :LunchTimeET,
			    pmuser_id := :pmUser_id
			);
		";
		$params = [
			"SmpUnitTimes_id" => null,
			"pmUser_id" => $data["pmUser_id"],
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"minTimeSMP" => (int)$data["minTimeSMP"],
			"maxTimeSMP" => (int)$data["maxTimeSMP"],
			"minTimeNMP" => (int)$data["minTimeNMP"],
			"maxTimeNMP" => (int)$data["maxTimeNMP"],
			"minResponseTimeNMP" => (int)$data["minResponseTimeNMP"],
			"maxResponseTimeNMP" => (int)$data["maxResponseTimeNMP"],
			"minResponseTimeET" => (int)$data["minResponseTimeET"],
			"maxResponseTimeET" => (int)$data["maxResponseTimeET"],
			"ArrivalTimeET" => (int)$data["ArrivalTimeET"],
			"ServiceTimeET" => (int)$data["ServiceTimeET"],
			"DispatchTimeET" => (int)$data["DispatchTimeET"],
			"LunchTimeET" => (int)$data["LunchTimeET"]
		];
		return $callObject->db->query($sql, $params)->result_array();
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function saveSmpUnitParams(LpuStructure_model $callObject, $data)
	{
		$procedure = (!empty($data["SmpUnitParam_id"])) ? "p_SmpUnitParam_upd" : "p_SmpUnitParam_ins";
		if (empty($data["SmpUnitParam_id"])) {
			$data["SmpUnitParam_id"] = null;
		}
		if (empty($data["LpuBuilding_pid"])) {
			$data["LpuBuilding_pid"] = null;
		}
		$sql = "
			select
				SmpUnitParam_id as \"SmpUnitParam_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				smpunitparam_id := :SmpUnitParam_id,
			    lpubuilding_id := :LpuBuilding_id,
			    smpunittype_id := :SmpUnitType_id,
			    lpubuilding_pid := :LpuBuilding_pid,
			    smpunitparam_isautobuilding := :SmpUnitParam_IsAutoBuilding,
			    smpunitparam_iscall112 := :SmpUnitParam_IsCall112,
			    smpunitparam_issignalbeg := :SmpUnitParam_IsSignalBeg,
			    smpunitparam_issignalend := :SmpUnitParam_IsSignalEnd,
			    smpunitparam_isovercall := :SmpUnitParam_IsOverCall,
			    smpunitparam_isautoemergduty := :SmpUnitParam_IsAutoEmergDuty,
			    smpunitparam_isautoemergdutyclose := :SmpUnitParam_IsAutoEmergDutyClose,
			    smpunitparam_issendcall := :SmpUnitParam_IsSendCall,
			    smpunitparam_isviewother := :SmpUnitParam_IsViewOther,
			    smpunitparam_isktprint := :SmpUnitParam_IsKTPrint,
			    smpunitparam_iscallsendoc := :SmpUnitParam_IsCallSenDoc,
			    smpunitparam_iscancldcall := :SmpUnitParam_IsCancldCall,
			    smpunitparam_iscallcontroll := :SmpUnitParam_IsCallControll,
			    smpunitparam_isshowallcallstodp := :SmpUnitParam_IsShowAllCallsToDP,
			    smpunitparam_isshowcallcount := :SmpUnitParam_IsShowCallCount,
			    smpunitparam_isnomoreassigncall := :SmpUnitParam_IsNoMoreAssignCall,
			    smpunitparam_maxcallcount := :SmpUnitParam_MaxCallCount,
			    lpu_eid := :Lpu_eid,
			    lpubuilding_eid := :LpuBuilding_eid,
			    smpunitparam_isgroupsubstation := :SmpUnitParam_IsGroupSubstation,
			    smpunitparam_isdispnocontrol := :SmpUnitParam_IsDispNoControl,
			    smpunitparam_isdocnocontrol := :SmpUnitParam_IsDocNoControl,
			    smpunitparam_isdispothercontrol := :SmpUnitParam_IsDispOtherControl,
			    smpunitparam_issavetreepath := :SmpUnitParam_IsSaveTreePath,
			    smpunitparam_iscallapprovesend := :SmpUnitParam_IsCallApproveSend,
			    smpunitparam_isnotransother := :SmpUnitParam_IsNoTransOther,
			    smpunitparam_isdenycallanswerdisp := :SmpUnitParam_IsDenyCallAnswerDisp,
			    pmuser_id := :pmUser_id
			);
		";
		$params = [
			"pmUser_id" => $data["pmUser_id"],
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"LpuBuilding_pid" => $data["LpuBuilding_pid"],
			"SmpUnitType_id" => $data["SmpUnitType_id"],
			"SmpUnitParam_id" => $data["SmpUnitParam_id"],
			"SmpUnitParam_IsAutoBuilding" => $data["SmpUnitParam_IsAutoBuilding"],
			"SmpUnitParam_IsCall112" => $data["SmpUnitParam_IsCall112"],
			"SmpUnitParam_IsOverCall" => $data["SmpUnitParam_IsOverCall"],
			"SmpUnitParam_IsCallSenDoc" => $data["SmpUnitParam_IsCallSenDoc"],
			"SmpUnitParam_IsSignalBeg" => $data["SmpUnitParam_IsSignalBeg"],
			"SmpUnitParam_IsSignalEnd" => $data["SmpUnitParam_IsSignalEnd"],
			"SmpUnitParam_IsKTPrint" => $data["SmpUnitParam_IsKTPrint"],
			"SmpUnitParam_IsAutoEmergDuty" => $data["SmpUnitParam_IsAutoEmergDuty"],
			"SmpUnitParam_IsAutoEmergDutyClose" => $data["SmpUnitParam_IsAutoEmergDutyClose"],
			"SmpUnitParam_IsSendCall" => $data["SmpUnitParam_IsSendCall"],
			"SmpUnitParam_IsViewOther" => $data["SmpUnitParam_IsViewOther"],
			"SmpUnitParam_IsCallControll" => $data["SmpUnitParam_IsCallControll"],
			"SmpUnitParam_IsSaveTreePath" => $data["SmpUnitParam_IsSaveTreePath"],
			"SmpUnitParam_IsShowAllCallsToDP" => $data["SmpUnitParam_IsShowAllCallsToDP"],
			"SmpUnitParam_IsCancldCall" => $data["SmpUnitParam_IsCancldCall"],
			"SmpUnitParam_IsNoMoreAssignCall" => $data["SmpUnitParam_IsNoMoreAssignCall"],
			"SmpUnitParam_IsShowCallCount" => $data["SmpUnitParam_IsShowCallCount"],
			"SmpUnitParam_MaxCallCount" => $data["SmpUnitParam_MaxCallCount"],
			"Lpu_eid" => $data["Lpu_eid"],
			"LpuBuilding_eid" => $data["LpuBuilding_eid"],
			"SmpUnitParam_IsCallApproveSend" => $data["SmpUnitParam_IsCallApproveSend"],
			"SmpUnitParam_IsNoTransOther" => $data["SmpUnitParam_IsNoTransOther"],
			"SmpUnitParam_IsDenyCallAnswerDisp" => $data["SmpUnitParam_IsDenyCallAnswerDisp"],
			"SmpUnitParam_IsDispNoControl" => $data["SmpUnitParam_IsDispNoControl"],
			"SmpUnitParam_IsDocNoControl" => $data["SmpUnitParam_IsDocNoControl"],
			"SmpUnitParam_IsDispOtherControl" => $data["SmpUnitParam_IsDispOtherControl"],
			"SmpUnitParam_IsGroupSubstation" => $data["SmpUnitParam_IsGroupSubstation"]
		];
		return $callObject->queryResult($sql, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveLpuSectionService(LpuStructure_model $callObject, $data)
	{
		$params = $data;
		$procedure = ($params["LpuSectionService_id"] > 0)?"p_LpuSectionService_upd":"p_LpuSectionService_ins";
		if ($params["LpuSectionService_id"] = 0) {
			$params["LpuSectionService_id"] = null;
		}
		$query = "
			select
				LpuSectionService_id as \"LpuSectionService_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
			    lpusectionservice_id := :LpuSectionService_id,
			    lpusection_id := :LpuSection_id,
			    lpusection_did := :LpuSection_did,
			    pmuser_id := :pmUser_id
			);
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
	 * @throws Exception
	 */
	public static function saveLpuStaffGridDetail(LpuStructure_model $callObject, $data)
	{
		$queryParams = [
			"LpuStaff_id" => (isset($data["LpuStaff_id"])) ? $data["LpuStaff_id"] : null,
			"LpuStaff_Descript" => (isset($data["LpuStaff_Descript"])) ? $data["LpuStaff_Descript"] : null,
			"LpuStaff_Num" => (isset($data["LpuStaff_Num"])) ? $data["LpuStaff_Num"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"LpuStaff_ApprovalDT" => (isset($data["LpuStaff_ApprovalDT"])) ? $data["LpuStaff_ApprovalDT"] : null,
			"LpuStaff_begDate" => (isset($data["LpuStaff_begDate"])) ? $data["LpuStaff_begDate"] : null,
			"LpuStaff_endDate" => (isset($data["LpuStaff_endDate"])) ? $data["LpuStaff_endDate"] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		$where = (isset($data["LpuStaff_id"])) ? " and LpuStaff_id != :LpuStaff_id " : "";
		if ($queryParams["LpuStaff_endDate"]) {
			$where .= " 
				and (
					(LpuStaff_begDate <= :LpuStaff_endDate) 
					and 
					(LpuStaff_endDate >= :LpuStaff_begDate or LpuStaff_endDate is null)
				)
			";
		} else {
			$where .= " 
				and (LpuStaff_endDate >= :LpuStaff_begDate or LpuStaff_endDate is null)
			";
		}
		$query = "
			select count(1) as count
			from dbo.v_LpuStaff 
			where Lpu_id = :Lpu_id
			  {$where}
		";
		/**
		 * @var CI_DB_result $res
		 * @var CI_DB_result $result
		 */
		$res = $callObject->db->query($query, $queryParams);
		$response = $res->result("array");
		if ($response[0]["count"] > 0) {
			throw new Exception("Период действия штатного расписания пересекается с периодом действия другого штатного расписания. Сохранение невозможно.");
		}
		$procedure = (isset($data['LpuStaff_id'])) ? "p_LpuStaff_upd" : "p_LpuStaff_ins";
		$query = "
			select
				LpuStaff_id as \"LpuStaff_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
			    lpustaff_id := :LpuStaff_id,
			    lpu_id := :Lpu_id,
			    lpustaff_num := :LpuStaff_Num,
			    lpustaff_descript := :LpuStaff_Descript,
			    lpustaff_approvaldt := :LpuStaff_ApprovalDT,
			    lpustaff_begdate := :LpuStaff_begDate,
			    lpustaff_enddate := :LpuStaff_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return CI_DB_result
	 * @throws Exception
	 */
	public static function saveNmpParams(LpuStructure_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"MedService_id" => $data["MedService_id"],
			"LpuHMPWorkTime_MoFrom" => !empty($data["LpuHMPWorkTime_MoFrom"]) ? $data["LpuHMPWorkTime_MoFrom"] : null,
			"LpuHMPWorkTime_MoTo" => !empty($data["LpuHMPWorkTime_MoTo"]) ? $data["LpuHMPWorkTime_MoTo"] : null,
			"LpuHMPWorkTime_TuFrom" => !empty($data["LpuHMPWorkTime_TuFrom"]) ? $data["LpuHMPWorkTime_TuFrom"] : null,
			"LpuHMPWorkTime_TuTo" => !empty($data["LpuHMPWorkTime_TuTo"]) ? $data["LpuHMPWorkTime_TuTo"] : null,
			"LpuHMPWorkTime_WeFrom" => !empty($data["LpuHMPWorkTime_WeFrom"]) ? $data["LpuHMPWorkTime_WeFrom"] : null,
			"LpuHMPWorkTime_WeTo" => !empty($data["LpuHMPWorkTime_WeTo"]) ? $data["LpuHMPWorkTime_WeTo"] : null,
			"LpuHMPWorkTime_ThFrom" => !empty($data["LpuHMPWorkTime_ThFrom"]) ? $data["LpuHMPWorkTime_ThFrom"] : null,
			"LpuHMPWorkTime_ThTo" => !empty($data["LpuHMPWorkTime_ThTo"]) ? $data["LpuHMPWorkTime_ThTo"] : null,
			"LpuHMPWorkTime_FrFrom" => !empty($data["LpuHMPWorkTime_FrFrom"]) ? $data["LpuHMPWorkTime_FrFrom"] : null,
			"LpuHMPWorkTime_FrTo" => !empty($data["LpuHMPWorkTime_FrTo"]) ? $data["LpuHMPWorkTime_FrTo"] : null,
			"LpuHMPWorkTime_SaFrom" => !empty($data["LpuHMPWorkTime_SaFrom"]) ? $data["LpuHMPWorkTime_SaFrom"] : null,
			"LpuHMPWorkTime_SaTo" => !empty($data["LpuHMPWorkTime_SaTo"]) ? $data["LpuHMPWorkTime_SaTo"] : null,
			"LpuHMPWorkTime_SuFrom" => !empty($data["LpuHMPWorkTime_SuFrom"]) ? $data["LpuHMPWorkTime_SuFrom"] : null,
			"LpuHMPWorkTime_SuTo" => !empty($data["LpuHMPWorkTime_SuTo"]) ? $data["LpuHMPWorkTime_SuTo"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		/**@var CI_DB_result $result */
		$query = "
			select LpuHMPWorkTime_id as \"LpuHMPWorkTime_id\"
			from v_LpuHMPWorkTime
			where MedService_id = :MedService_id
			limit 1
		";
		$result = $callObject->queryResult($query, $params);
		$resultArray = $result->result("array");
		//TODO 111
		if(count($resultArray) == 0 || empty($resultArray[0]["LpuHMPWorkTime_id"])) {
			$query = "
				exec p_LpuHMPWorkTime_ins
				@LpuHMPWorkTime_id = @LpuHMPWorkTime_id output,
				@Lpu_id = :Lpu_id,
				@MedService_id = :MedService_id,
				@LpuHMPWorkTime_MoFrom = :LpuHMPWorkTime_MoFrom,
				@LpuHMPWorkTime_MoTo = :LpuHMPWorkTime_MoTo,
				@LpuHMPWorkTime_TuFrom = :LpuHMPWorkTime_TuFrom,
				@LpuHMPWorkTime_TuTo = :LpuHMPWorkTime_TuTo,
				@LpuHMPWorkTime_WeFrom = :LpuHMPWorkTime_WeFrom,
				@LpuHMPWorkTime_WeTo = :LpuHMPWorkTime_WeTo,
				@LpuHMPWorkTime_ThFrom = :LpuHMPWorkTime_ThFrom,
				@LpuHMPWorkTime_ThTo = :LpuHMPWorkTime_ThTo,
				@LpuHMPWorkTime_FrFrom = :LpuHMPWorkTime_FrFrom,
				@LpuHMPWorkTime_FrTo = :LpuHMPWorkTime_FrTo,
				@LpuHMPWorkTime_SaFrom = :LpuHMPWorkTime_SaFrom,
				@LpuHMPWorkTime_SaTo = :LpuHMPWorkTime_SaTo,
				@LpuHMPWorkTime_SuFrom = :LpuHMPWorkTime_SuFrom,
				@LpuHMPWorkTime_SuTo = :LpuHMPWorkTime_SuTo,
				@pmUser_id = :pmUser_id
			";
		} else {
			$query = "
				exec p_LpuHMPWorkTime_upd
				@LpuHMPWorkTime_id = @LpuHMPWorkTime_id output,
				@Lpu_id = :Lpu_id,
				@MedService_id = :MedService_id,
				@LpuHMPWorkTime_MoFrom = :LpuHMPWorkTime_MoFrom,
				@LpuHMPWorkTime_MoTo = :LpuHMPWorkTime_MoTo,
				@LpuHMPWorkTime_TuFrom = :LpuHMPWorkTime_TuFrom,
				@LpuHMPWorkTime_TuTo = :LpuHMPWorkTime_TuTo,
				@LpuHMPWorkTime_WeFrom = :LpuHMPWorkTime_WeFrom,
				@LpuHMPWorkTime_WeTo = :LpuHMPWorkTime_WeTo,
				@LpuHMPWorkTime_ThFrom = :LpuHMPWorkTime_ThFrom,
				@LpuHMPWorkTime_ThTo = :LpuHMPWorkTime_ThTo,
				@LpuHMPWorkTime_FrFrom = :LpuHMPWorkTime_FrFrom,
				@LpuHMPWorkTime_FrTo = :LpuHMPWorkTime_FrTo,
				@LpuHMPWorkTime_SaFrom = :LpuHMPWorkTime_SaFrom,
				@LpuHMPWorkTime_SaTo = :LpuHMPWorkTime_SaTo,
				@LpuHMPWorkTime_SuFrom = :LpuHMPWorkTime_SuFrom,
				@LpuHMPWorkTime_SuTo = :LpuHMPWorkTime_SuTo,
				@pmUser_id = :pmUser_id
			";
		}
		$query = "
			set @LpuHMPWorkTime_id = (
				select LpuHMPWorkTime_id  from v_LpuHMPWorkTime  where MedService_id = :MedService_id limit 1
			)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->queryResult($query, $params);
		if (!is_array($result)) {
			throw new Exception("Ошибка при сохранении параметров службы НМП");
		}
		return $result;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getLpuStructureProfileAll(LpuStructure_model $callObject, $data)
	{
		if(empty($data['LpuSection_id'])) return false;
		$sql = "
			select
				lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
				,lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from dbo.v_LpuSectionLpuSectionProfile lslsp
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
			where
				lslsp.LpuSection_id = :LpuSection_id
			UNION ALL
			select
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
				,lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from dbo.v_LpuSection ls
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			where
				ls.LpuSection_id = :LpuSection_id
		";
		
		return $callObject->queryResult($sql, array(
			'LpuSection_id' => $data['LpuSection_id']
		));
	}

}