<?php


class LpuStructure_model_delete
{
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 *
	 * @throws Exception
	 */
	public static function deleteLpuSectionBedState(LpuStructure_model $callObject, $data)
	{
		$sp = $callObject->getLpuSectionBedState($data);
		if ($sp === false || !is_array($sp) || count($sp) == 0) {
			return [[
				'Error_Code' => 6,
				'Error_Msg' => 'Ошибка при получении данных'
			]];
		}
		if (!empty($sp[0]['Lpu_id']) && $data['Lpu_id'] != $sp[0]['Lpu_id']) {
			return [[
				'Error_Code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			]];
		}
		$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_lpusectionbedstate_del(
			    lpusectionbedstate_id := :LpuSectionBedState_id
			);
		";
		return $callObject->queryResult($query, $data);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public static function deleteLinkFDServiceToRCCService(LpuStructure_model $callObject, $data)
	{
		$queryParams = [];
		if (!isset($data["MedServiceLink_id"])) {
			return [['success' => false, 'Error_Code' => '','Error_Msg' => 'Не указан идентификатор связи служб.']];
		} else {
			$queryParams["MedServiceLink_id"] = $data["MedServiceLink_id"];
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_medservicelink_del(
				medservicelink_id := :MedServiceLink_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function deleteLpuMseLink(LpuStructure_model $callObject, $data)
	{
		$params = ["LpuMseLink_id" => $data["LpuMseLink_id"]];
		$query = "
			select
			 	Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_lpumselink_del(
				lpumselink_id := :LpuMseLink_id
			);
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteLpuSectionLpuSectionProfile(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_lpusectionlpusectionprofile_del(
				lpusectionlpusectionprofile_id := :LpuSectionLpuSectionProfile_id
			);
		";
		$queryParams = [
			"LpuSectionLpuSectionProfile_id" => $data["LpuSectionLpuSectionProfile_id"]
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
	public static function deleteLpuSectionMedicalCareKind(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_lpusectionmedicalcarekind_del(
				lpusectionmedicalcarekind_id := :LpuSectionMedicalCareKind_id
			);
		";
		$queryParams = ["LpuSectionMedicalCareKind_id" => $data["LpuSectionMedicalCareKind_id"]];
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
	public static function deleteLpuSectionService(LpuStructure_model $callObject, $data)
	{
		$queryParams = ["LpuSectionService_id" => $data["LpuSectionService_id"]];
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_lpusectionservice_del(
				lpusectionservice_id := :LpuSectionService_id
			);";
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
	 * 
	 * @throws Exception
	 */
	public static function deleteLpuSectionWard(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["LpuSectionWard_id"])) {
			return [['LpuSectionWard_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Нельзя удалить запись без идентификатора.']];
		}
		$sp = $callObject->getLpuSectionWardByIdData($data);
		if ($sp && isset($sp[0]["Lpu_id"]) && $data["Lpu_id"] != $sp[0]["Lpu_id"]) {
			return [[
				'error_code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			]];
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_lpusectionward_del(
				lpusectionward_id := :LpuSectionWard_id
			);";
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
	public static function deleteMedStaffRegion(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["MedStaffRegion_id"]) && $data["MedStaffRegion_id"] == 0) {
			return true;
		}
		$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_medstaffregion_del(
				medstaffregion_id := :MedStaffRegion_id,
				pmuser_id := :pmUser_id
			);";
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
	 *
	 * @throws Exception
	 */
	public static function deleteSectionBedStateOper(LpuStructure_model $callObject, $data)
	{
		if (!isset($data['LpuSectionBedStateOper_id'])) {
			return [['LpuSectionBedStateOper_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Нельзя удалить запись без идентификатора.']];
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from fed.p_lpusectionbedstateoper_del(
				lpusectionbedstateoper_id := :LpuSectionBedStateOper_id
			);";
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
	 * 
	 * @throws Exception
	 */
	public static function deleteSectionWardComfortLink(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["LpuSectionWardComfortLink_id"])) {
			return [['LpuSectionWardComfortLink_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Нельзя удалить запись без идентификатора.']];
		}
		$sp = $callObject->getLpuSectionWardComfortLinkForAPI($data);
		if (isset($sp[0]["Lpu_id"]) && $data["Lpu_id"] != $sp[0]["Lpu_id"]) {
			return [[
				'Error_Code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			]];
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from fed.p_lpusectionwardcomfortlink_del(
				lpusectionwardcomfortlink_id := :LpuSectionWardComfortLink_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}