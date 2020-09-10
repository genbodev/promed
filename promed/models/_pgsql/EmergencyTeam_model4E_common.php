<?php

class EmergencyTeam_model4E_common
{
	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteEmergencyTeam(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			return false;
		}
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$query = "
			update EmergencyTeam
			set
				pmUser_delID = :pmUser_delID,
				EmergencyTeam_delDT = now(),
				EmergencyTeam_Deleted = 2
			where EmergencyTeam_id = :EmergencyTeam_id
			  and Lpu_id = :Lpu_id;
		";
		$queryParams = [
			"pmUser_delID" => (isset($data["pmUser_id"])) ? $data["pmUser_id"] : "DEFAULT",
			"EmergencyTeam_id" => (isset($data["EmergencyTeam_id"])) ? $data["EmergencyTeam_id"] : "DEFAULT",
			"Lpu_id" => (isset($data["Lpu_id"])) ? $data["Lpu_id"] : "DEFAULT"
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время удаления бригады СМП произошла ошибка в базе данных.");
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteEmergencyTeamList(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeamsList", $data) || !is_array($data["EmergencyTeamsList"]) || !array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			return false;
		}
		$deleteEmergencyTeam = $data;
		unset($deleteEmergencyTeam["EmergencyTeamsList"]);
		$result = [];
		foreach ($data["EmergencyTeamsList"] as $emergemcy_id) {
			$deleteEmergencyTeam["EmergencyTeam_id"] = $emergemcy_id;
			$n = $callObject->deleteEmergencyTeam($deleteEmergencyTeam);
			$result[$emergemcy_id] = (isset($n[0]["Error_Msg"]) || isset($n[0]["Err_Msg"]) || !$n[0]) ? false : true;
		}
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteEmergencyTeamDutyTime(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeamDuty_id", $data)) {
			throw new Exception("Отсутствуют необходимые данные.");
		}
		$sql = "delete from EmergencyTeamDuty where EmergencyTeamDuty_id = :EmergencyTeamDuty_id";
		$sqlParams = ["EmergencyTeamDuty_id" => $data["EmergencyTeamDuty_id"]];
		$query = $callObject->db->query($sql, $sqlParams);
		/**@var CI_DB_result $query */
		if (!is_object($query)) {
			return false;
		}
		return $query->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function deleteEmergencyTeamDutyTimeList(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeamDutyList", $data)) {
			throw new Exception("Отсутствуют необходимые данные.");
		}
		$deleteEmergencyTeamDutyTime = $data;
		unset($deleteEmergencyTeamDutyTime["EmergencyTeamDutyList"]);
		$result = [];
		foreach ($data["EmergencyTeamDutyList"] as $emergemcy_id) {
			$deleteEmergencyTeamDutyTime["EmergencyTeamDuty_id"] = $emergemcy_id;
			$n = $callObject->deleteEmergencyTeamDutyTime($deleteEmergencyTeamDutyTime);
			$result[$emergemcy_id] = (isset($n[0]["Error_Msg"]) || !$n[0]) ? false : true;
		}
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function deleteEmergencyTeamPackMove(EmergencyTeam_model4E $callObject, $data)
	{
		$rules = [
			["field" => "EmergencyTeamDrugPackMove_id", "label" => "Идентификатор подстанции", "rules" => "required", "type" => "int"]
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		//Получаем идентификатор укладки
		$drug_pack_id_result = $callObject->_getDrugPackByDrugPackMove($queryParams);
		if (!$callObject->isSuccessful($drug_pack_id_result)) {
			return $drug_pack_id_result;
		}
		if (empty($drug_pack_id_result[0]["EmergencyTeamDrugPack_id"])) {
			throw new Exception("Не удалось получить укладку по записи.");
		}
		$data["EmergencyTeamDrugPack_id"] = $drug_pack_id_result[0]["EmergencyTeamDrugPack_id"];
		$callObject->beginTransaction();
		//Удаляем запись об изменении укладки
		$delete_query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_emergencyteamdrugpackmove_del(
			    emergencyteamdrugpackmove_id := :EmergencyTeamDrugPackMove_id
			);
		";
		$delete_result = $callObject->queryResult($delete_query, $queryParams);
		if (!$callObject->isSuccessful($delete_result)) {
			$callObject->rollbackTransaction();
			return $delete_result;
		}
		// Получаем суммарное количество медикаментов в укладке
		$count_result = $callObject->getDrugCountFromDrugPackMoveByDrugPackId($data);
		if (!$callObject->isSuccessful($count_result)) {
			$callObject->rollbackTransaction();
			return $count_result;
		}
		if (empty($count_result[0]["DrugCount"]) || empty($count_result[0]["EmergencyTeam_id"]) || empty($count_result[0]["Drug_id"])) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка получения суммарного остатка медикамента в укладке");
		}
		$data["EmergencyTeamDrugPack_Total"] = $count_result[0]["DrugCount"];
		$data["EmergencyTeam_id"] = $count_result[0]["EmergencyTeam_id"];
		$data["Drug_id"] = $count_result[0]["Drug_id"];
		// Обновляем остатки медикамента в укладке
		$updateDrugPackResult = $callObject->saveEmergencyTeamDrugPack($data);
		if (!$callObject->isSuccessful($updateDrugPackResult)) {
			$callObject->rollbackTransaction();
			return $updateDrugPackResult;
		}
		$callObject->commitTransaction();
		return $updateDrugPackResult;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function deleteEmergencyTeamPackMoveByDocumentUcStr(EmergencyTeam_model4E $callObject, $data)
	{
		// Поулчаем идентификатор движения медикамента по идетификатору строки документа списания
		$EmergencyTeamDrugPackMoveId_result = $callObject->getEmergencyTeamDrugPackMoveIdByDocumentUcStr($data);
		if (!$callObject->isSuccessful($EmergencyTeamDrugPackMoveId_result)) {
			return $EmergencyTeamDrugPackMoveId_result;
		} elseif (empty($EmergencyTeamDrugPackMoveId_result[0]["EmergencyTeamDrugPackMove_id"])) {
			// Если запись не найдена, значит уже была удалена
			return [["success" => true, "Error_Msg" => ""]];
		}
		// Пополняем входные параметры данными об укладке: EmergencyTeamDrugPack_id, EmergencyTeam_id, Drug_id
		$data = array_merge($data, $EmergencyTeamDrugPackMoveId_result[0]);
		// Удаляем запись о движении
		return $callObject->deleteEmergencyTeamPackMove($data);
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteEmergencyTeamProposalLogicRule(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			throw new Exception("Не указан идентификатор правила.");
		}
		if (@$data["EmergencyTeamProposalLogic_id"] == null || @$data["EmergencyTeamProposalLogic_id"] == 0) {
			throw new Exception("Не задан идентификатор правила");
		}
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result1
		 */
		$query = "
			select count(ETPLR.EmergencyTeamProposalLogicRule_id) as EmergencyTeamProposalLogicRule_id
			from v_EmergencyTeamProposalLogicRule ETPLR
			where ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
		";
		$queryParams = ["EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"]];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if ($result[0]["EmergencyTeamProposalLogicRule_id"] > 0) {
			$work = true;
			$countBlock = 0;
			while ($work) {
				$countBlock++;
				$query1 = "
					select distinct
						ETPLR.EmergencyTeamProposalLogicRule_id
					from v_EmergencyTeamProposalLogicRule ETPLR
					where ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
				";
				$result1 = $callObject->db->query($query1, $queryParams);
				$result1 = $result1->result("array");
				$EmergencyTeamProposalLogic_id = $result1[0]["EmergencyTeamProposalLogic_id"];
				$query1 = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_emergencyteamproposallogicrule_del(
				    	emergencyteamproposallogicrule_id := {$EmergencyTeamProposalLogic_id}
					);
				";
				$result1 = $callObject->db->query($query1);
				$result1 = $result1->result("array");
				if ($countBlock > 50 || trim($result1[0]["Error_Message"]) != "") {
					$work = false;
				} else {
					$result = $callObject->db->query($query, $queryParams);
					if (!is_object($result)) {
						return false;
					}
					$result = $result->result("array");
					if ($result[0]["EmergencyTeamProposalLogicRule_id"] <= 0) {
						$work = false;
					}
				}
			}
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_emergencyteamproposallogic_del(
			    emergencyteamproposallogic_id := :EmergencyTeamProposalLogic_id
			);
		";
		$queryParams = ["EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	public static function deleteEmergencyTeamProposalLogicRuleSequence($data)
	{
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			throw new Exception("Не указан идентификатор правила.");
		}
		/*
		$query = "
			select ETPLR.EmergencyTeamProposalLogicRule_id
			from v_EmergencyTeamProposalLogicRule ETPLR
			where ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
		";
		 */
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteEmergencyTeamVigil(EmergencyTeam_model4E $callObject, $data)
	{
		$sql = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpemteamduty_del(
			    cmpemteamduty_id := :CmpEmTeamDuty_id
			);
		";
		$params = array("CmpEmTeamDuty_id" => $data["CmpEmTeamDuty_id"]);
		
		/**@var CI_DB_result $query */
		$query = $callObject->db->query($sql, $params);
		if (!is_object($query)) {
			return false;
		}
		return $query->result_array();
	}

	/**
	 * @desc Изменяет статус бригады СМП на предыдущий у вызова
	 * @param array $data
	 * @return array|false
	 */
	public static function cancelEmergencyTeamFromCall(EmergencyTeam_model4E $callObject, $data)
	{
		//получаем текущий статус бригады
		$sql = "
			select ets.EmergencyTeamStatus_Code
			from
				v_EmergencyTeamStatusHistory etsh
				left join v_EmergencyTeamStatus ets on etsh.EmergencyTeamStatus_id = ets.EmergencyTeamStatus_id
			where etsh.EmergencyTeam_id= :EmergencyTeam_id
			  and etsh.CmpCallCard_id = :CmpCallCard_id
			order by etsh.EmergencyTeamStatusHistory_id desc
			limit 1
		";
		$sqlParams = [
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"CmpCallCard_id" => $data["CmpCallCard_id"]
		];
		$res = $callObject->db->query($sql, $sqlParams);
		$EmergencyTeamStatus = $res->result("array");
		$callObject->load->model("CmpCallCard_model4E", "cardModel");
		//если установлен статус и он "в ожидании принятия" либо "принял вызов"
		if (count($EmergencyTeamStatus) > 0 && ($EmergencyTeamStatus[0]["EmergencyTeamStatus_Code"] == 36 || $EmergencyTeamStatus[0]["EmergencyTeamStatus_Code"] == 48)) {
			$params["pmUser_id"] = $data["pmUser_id"];
			$params["CmpCallCard_id"] = $data["CmpCallCard_id"];
			$params["CmpCallCard_Tper"] = null;
			$callObject->swUpdate("CmpCallCard", $params);
			$callObject->cardModel->setEmergencyTeam([
				"EmergencyTeam_id" => 0,
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"pmUser_id" => $data["pmUser_id"]
			]);
		} else {
			$setTisp = "
				update dbo.CmpCallCard
				set CmpCallCard_Tisp = tzgetdate(),
					pmUser_updID = :pmUser_id,
					CmpCallCard_updDT = tzgetdate()
				where CmpCallCard_id=:CmpCallCard_id
			";
			$setTispParams = [
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->db->query($setTisp, $setTispParams);
			$callObject->cardModel->setStatusCmpCallCard([
				"CmpCallCard_id" => $data['CmpCallCard_id'],
				"CmpCallCardStatusType_Code" => 4,
				"pmUser_id" => $data["pmUser_id"]
			]);
			$callObject->cardModel->copyCmpCallCard($data);
		}
		$params = [
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"EmergencyTeamStatus_Code" => 4,
			"ARMType_id" => $data["ARMType_id"],
			"CmpCallCard_IsUpd" => 1,
			"pmUser_id" => $data["pmUser_id"],
		];
		$result = $callObject->setEmergencyTeamStatus($params);
		// отправляем сообщение в ActiveMQ
		if (!empty($data["EmergencyTeam_id"]) && defined("STOMPMQ_MESSAGE_DESTINATION_EMERGENCY")) {
			$callObject->CmpCallCard_model->checkSendReactionToActiveMQ(array(
				"EmergencyTeam_id" => $data["EmergencyTeam_id"],
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"resetTeam" => true
			));
		}
		if (!is_array($result)) {
			return false;
		}
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkLunchTimeOut(EmergencyTeam_model4E $callObject, $data)
	{
		if (!$data["EmergencyTeam_id"]) {
			return false;
		}
		$sql = "
			select
				case when datediff('mi', ETSH.EmergencyTeamStatusHistory_insDT, tzgetdate()) > coalesce(PSUT.LunchTimeET, SUT.LunchTimeET)
					then 2
					else 1
				end as \"LunchTimeOut\"
			from
				v_LpuBuilding LB
				left join EmergencyTeam ET on ET.LpuBuilding_id = LB.LpuBuilding_id
				left join EmergencyTeamStatusHistory ETSH on ETSH.EmergencyTeamStatusHistory_id = ET.EmergencyTeamStatusHistory_id
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
				left join lateral (
					select *
					from v_SmpUnitTimes
					where LpuBuilding_id = SUP.LpuBuilding_pid
				    limit 1
				) as PSUT on true
			where ET.EmergencyTeam_id = :EmergencyTeam_id
			limit 1
		";
		$sqlParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
		/**@var CI_DB_result $query */
		$query = $callObject->db->query($sql, $sqlParams);
		return $query->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $EmergencyTeam_id
	 * @return bool
	 * @throws Exception
	 */
	public static function checkOpenEmergencyTeam(EmergencyTeam_model4E $callObject, $EmergencyTeam_id)
	{
		$params["EmergencyTeam_id"] = $EmergencyTeam_id;
		$sql = "
			select 1
			from v_EmergencyTeamDuty
			where EmergencyTeam_id = :EmergencyTeam_id
			  and EmergencyTeamDuty_isClose = 2
		";
		/**@var CI_DB_result $query */
		$query = $callObject->db->query($sql, $params);
		$resultarr = $query->result_array();
		if (is_array($resultarr) && (count($resultarr) > 0)) {
			throw new Exception("Редактирование невозможно. В отметке о выходе бригад установлено значение закрыто.");
		}
		return true;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkActiveMedStaffFact(EmergencyTeam_model4E $callObject, $data)
	{
		$dataArr = [
			"EmergencyTeam_HeadShiftWorkPlace" => (!empty($data["EmergencyTeam_HeadShiftWorkPlace"])) ? $data["EmergencyTeam_HeadShiftWorkPlace"] : null,
			"EmergencyTeam_HeadShift2WorkPlace" => (!empty($data["EmergencyTeam_HeadShift2WorkPlace"])) ? $data["EmergencyTeam_HeadShift2WorkPlace"] : null,
			"EmergencyTeam_DriverWorkPlace" => (!empty($data["EmergencyTeam_DriverWorkPlace"])) ? $data["EmergencyTeam_DriverWorkPlace"] : null,
			"EmergencyTeam_Assistant1WorkPlace" => (!empty($data["EmergencyTeam_Assistant1WorkPlace"])) ? $data["EmergencyTeam_Assistant1WorkPlace"] : null,
		];
		foreach ($dataArr as $MedStaffFact_id) {
			if ($MedStaffFact_id) {
				$query = "
					select MedStaffFact_id
					from v_MedStaffFact
					where MedStaffFact_id = {$MedStaffFact_id}
					  and WorkData_begDate <= tzgetdate()
					  and (WorkData_endDate > tzgetdate() or WorkData_endDate  is null)
					limit 1
				";
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($query);
				if (is_object($result)) {
					$result = $result->result("array");
					if (!count($result)) {
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * @return array
	 */
	public static function _defineGeoserviceTransportRelQueryParams()
	{
		$region = getRegionNick();
		if (isset($_GET["dbg"]) && $_GET["dbg"] == "1") {
			var_dump($region);
		}
		if ($region == "ufa") {
			$result = [
				"GeoserviceTransport_id_field" => "TNCTransport_id",
				"EmergencyTeam_id_field" => "EmergencyTeam_id",
				"GeoserviceTransportRel_object" => "v_EmergencyTeamTNCRel",
			];
		} else {
			$result = [
				"GeoserviceTransport_id_field" => "WialonEmergencyTeamId",
				"EmergencyTeam_id_field" => "EmergencyTeam_id",
				"GeoserviceTransportRel_object" => "v_EmergencyTeamWialonRel",
			];
		}
		foreach ($result as $key => $value) {
			$result["$key"] = $value;
		}
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkCarByDate(EmergencyTeam_model4E $callObject, $data)
	{
		$query = "
			SELECT
				MPC.MedProductCard_id as \"MedProductCard_id\",
                LB.LpuBuilding_id as \"LpuBuilding_id\",
                LB.LpuBuilding_Name as \"LpuBuilding_Name\",
                MPC.MedProductCard_BoardNumber as \"MedProductCard_BoardNumber\",
				AD.AccountingData_RegNumber as \"AccountingData_RegNumber\",
				MPCl.MedProductClass_Name as \"MedProductClass_Name\",
				MPCl.MedProductClass_Model as \"MedProductClass_Model\",
				MPT.MedProductType_Code as \"MedProductType_Code\",
                MPC.MedProductCard_Glonass as \"GeoserviceTransport_id\",
				to_char(AD.AccountingData_setDate, '{$callObject->dateTimeForm120}') as \"AccountingData_setDate\",
                to_char(AD.AccountingData_endDate, '{$callObject->dateTimeForm120}') as \"AccountingData_endDate\"
			from
				passport.v_MedProductCard MPC				
				left join passport.v_MedProductClass MPCl on MPCl.MedProductClass_id = MPC.MedProductClass_id
                left join passport.v_AccountingData AD on MPC.MedProductCard_id = AD.MedProductCard_id
                left join passport.v_MedProductType MPT on MPT.MedProductType_id = MPCl.MedProductType_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = MPC.LpuBuilding_id
			where MPC.MedProductCard_id = :MedProductCard_id
			  and (
			  		(AD.AccountingData_setDate is null or AD.AccountingData_setDate <= :EmergencyTeamDuty_DTStart) and
			  		(AD.AccountingData_endDate is null or AD.AccountingData_endDate >= :EmergencyTeamDuty_DTFinish)
				)
		";
		$sqlArr = [
			"MedProductCard_id" => $data["MedProductCard_id"],
			"EmergencyTeamDuty_DTStart" => $data["EmergencyTeamDuty_DTStart"],
			"EmergencyTeamDuty_DTFinish" => $data["EmergencyTeamDuty_DTFinish"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $sqlArr);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkCarByDutyDate(EmergencyTeam_model4E $callObject, $data)
	{
		$query = "
			 select
                ET.EmergencyTeam_id as \"EmergencyTeam_id\",
                EmergencyTeam_Num as \"EmergencyTeam_Num\",
                to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') AS EmergencyTeamDuty_DTStart,
                to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') AS EmergencyTeamDuty_DTFinish
            from
            	v_EmergencyTeam ET
                left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
                left join passport.v_MedProductCard MPC on ET.MedProductCard_id = MPC.MedProductCard_id
			where MPC.MedProductCard_id = :MedProductCard_id
              and coalesce(ET.EmergencyTeam_isTemplate, 1) = 1
              and
                (
                	(ETD.EmergencyTeamDuty_DTStart as datetime >= :EmergencyTeamDuty_DTStart and ETD.EmergencyTeamDuty_DTStart <= :EmergencyTeamDuty_DTStart) or
                	(ETD.EmergencyTeamDuty_DTFinish >= :EmergencyTeamDuty_DTStart and ETD.EmergencyTeamDuty_DTFinish <= :EmergencyTeamDuty_DTFinish)
                )
		";
		$queryParams = [
			"MedProductCard_id" => $data["MedProductCard_id"],
			"EmergencyTeamDuty_DTStart" => $data["EmergencyTeamDuty_DTStart"],
			"EmergencyTeamDuty_DTFinish" => $data["EmergencyTeamDuty_DTFinish"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkMedPersonalBusy(EmergencyTeam_model4E $callObject, $data)
	{
		$query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
                LB.LpuBuilding_Name as \"LpuBuilding_Name\",
                to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
				to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\"
			from
				v_EmergencyTeamDuty ETD
				left join v_EmergencyTeam as ET on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id =  ET.LpuBuilding_id
			where 
				(
					ET.EmergencyTeam_id != :EmergencyTeam_id and
					(
						:EmergencyTeam_Assistant1 in (ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver) or
						:EmergencyTeam_HeadShift in (ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver) or
						:EmergencyTeam_HeadShift2 in (ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver) or
						:EmergencyTeam_Driver in (ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver)
					)
				)			
				and EmergencyTeamDuty_DTStart < :EmergencyTeamDuty_DTFinish
				and :EmergencyTeamDuty_DTStart < EmergencyTeamDuty_DTFinish
		";
		$queryParams = [
			"EmergencyTeam_id" => ($data["EmergencyTeam_id"]) ? $data["EmergencyTeam_id"] : "",
			"EmergencyTeam_Assistant1" => !empty($data["EmergencyTeam_Assistant1"]) ? $data["EmergencyTeam_Assistant1"] : null,
			"EmergencyTeam_HeadShift" => !empty($data["EmergencyTeam_HeadShift"]) ? $data["EmergencyTeam_HeadShift"] : null,
			"EmergencyTeam_HeadShift2" => !empty($data["EmergencyTeam_HeadShift2"]) ? $data["EmergencyTeam_HeadShift2"] : null,
			"EmergencyTeam_Driver" => !empty($data["EmergencyTeam_Driver"]) ? $data["EmergencyTeam_Driver"] : null,
			"EmergencyTeamDuty_DTStart" => $data["EmergencyTeamDuty_DTStart"],
			"EmergencyTeamDuty_DTFinish" => $data["EmergencyTeamDuty_DTFinish"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}