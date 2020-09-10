<?php


class EmergencyTeam_model4E_save
{
	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @param bool $update_duty_time
	 * @return array|bool|false
	 * @throws Exception
	 */
	public static function saveEmergencyTeam(EmergencyTeam_model4E $callObject, $data, $update_duty_time = true)
	{
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		//Каждый шаблон должен обладать уникальным именем
		if ((!empty($data["EmergencyTeam_isTemplate"]) && $data["EmergencyTeam_isTemplate"] == 2) && !empty($data["EmergencyTeam_TemplateName"])) {
			$checkEmergencyTeamUniqueTemplateNameWhere = ["EmergencyTeam_TemplateName = :EmergencyTeam_TemplateName"];
			if ($data["EmergencyTeam_id"]) {
				$checkEmergencyTeamUniqueTemplateNameWhere[] = "EmergencyTeam_id != :EmergencyTeam_id";
			}
			$checkEmergencyTeamUniqueTemplateNameWhere[] = $callObject->getNestedLpuBuildingsForRequests($data);
			$whereString = "where ".implode(" and ", $checkEmergencyTeamUniqueTemplateNameWhere);
			$checkEmergencyTeamUniqueTemplateName = "
				select EmergencyTeam_id as \"EmergencyTeam_id\" 
				from v_EmergencyTeam ET 
				{$whereString}
			";
			$checkEmergencyTeamUniqueTemplateNameParams = [
				"EmergencyTeam_TemplateName" => $data["EmergencyTeam_TemplateName"],
				"EmergencyTeam_id" => empty($data["EmergencyTeam_id"]) ? null : $data["EmergencyTeam_id"]
			];
			$res_checkEmergencyTeamUniqueTemplateName = $callObject->queryResult($checkEmergencyTeamUniqueTemplateName, $checkEmergencyTeamUniqueTemplateNameParams);
			if (!empty($res_checkEmergencyTeamUniqueTemplateName[0]["EmergencyTeam_id"])) {
				throw new Exception("Имя шаблона должно быть уникальным");
			}
		}
		if (empty($data["LpuBuilding_id"])) {
			$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
			$lpuBuilding = $callObject->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0])) {
				throw new Exception("Не определена подстанция");
			}
			$data["LpuBuilding_id"] = $lpuBuilding[0]["LpuBuilding_id"];
		}
		// 1. Сохраняем бригаду
		$sqlArr = [
			"EmergencyTeam_Num" => $data["EmergencyTeam_Num"],
			"EmergencyTeam_CarNum" => $data["EmergencyTeam_CarNum"],
			"EmergencyTeam_CarBrand" => $data["EmergencyTeam_CarBrand"],
			"EmergencyTeam_CarModel" => (!empty($data["EmergencyTeam_CarModel"])) ? $data["EmergencyTeam_CarModel"] : null,
			"EmergencyTeam_PortRadioNum" => (!empty($data["EmergencyTeam_PortRadioNum"])) ? $data["EmergencyTeam_PortRadioNum"] : null,
			"EmergencyTeam_GpsNum" => (!empty($data["EmergencyTeam_GpsNum"])) ? $data["EmergencyTeam_GpsNum"] : null,
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"EmergencyTeamSpec_id" => (!empty($data["EmergencyTeamSpec_id"])) ? $data["EmergencyTeamSpec_id"] : null,
			"EmergencyTeam_HeadShift" => (!empty($data["EmergencyTeam_HeadShift"])) ? $data["EmergencyTeam_HeadShift"] : null,
			"EmergencyTeam_HeadShiftWorkPlace" => (!empty($data["EmergencyTeam_HeadShiftWorkPlace"])) ? $data["EmergencyTeam_HeadShiftWorkPlace"] : null,
			"EmergencyTeam_HeadShift2" => (!empty($data["EmergencyTeam_HeadShift2"])) ? $data["EmergencyTeam_HeadShift2"] : null,
			"EmergencyTeam_HeadShift2WorkPlace" => (!empty($data["EmergencyTeam_HeadShift2WorkPlace"])) ? $data["EmergencyTeam_HeadShift2WorkPlace"] : null,
			"EmergencyTeam_Driver" => (!empty($data["EmergencyTeam_Driver"])) ? $data["EmergencyTeam_Driver"] : null,
			"EmergencyTeam_DriverWorkPlace" => (!empty($data["EmergencyTeam_DriverWorkPlace"])) ? $data["EmergencyTeam_DriverWorkPlace"] : null,
			"EmergencyTeam_Driver2" => (!empty($data["EmergencyTeam_Driver2"])) ? $data["EmergencyTeam_Driver2"] : null,
			"EmergencyTeam_Assistant1" => (!empty($data["EmergencyTeam_Assistant1"])) ? $data["EmergencyTeam_Assistant1"] : null,
			"EmergencyTeam_Assistant1WorkPlace" => (!empty($data["EmergencyTeam_Assistant1WorkPlace"])) ? $data["EmergencyTeam_Assistant1WorkPlace"] : null,
			"EmergencyTeam_Assistant2" => (!empty($data["EmergencyTeam_Assistant2"])) ? $data["EmergencyTeam_Assistant2"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"CMPTabletPC_id" => (!empty($data["CMPTabletPC_id"])) ? $data["CMPTabletPC_id"] : null,
			"MedProductCard_id" => (!empty($data["MedProductCard_id"])) ? $data["MedProductCard_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
			"EmergencyTeam_DutyTime" => round($data["EmergencyTeam_DutyTime"]),
			"EmergencyTeam_isTemplate" => (!empty($data["EmergencyTeam_isTemplate"])) ? $data["EmergencyTeam_isTemplate"] : null,
			"EmergencyTeam_TemplateName" => (!empty($data["EmergencyTeam_TemplateName"])) ? $data["EmergencyTeam_TemplateName"] : null,
			"EmergencyTeam_Phone" => (!empty($data["EmergencyTeam_Phone"])) ? $data["EmergencyTeam_Phone"] : null
		];
		$sqlArr["EmergencyTeamStatus_id"] = isset($data["EmergencyTeamStatus_id"]) && $data["EmergencyTeamStatus_id"] ? $data["EmergencyTeamStatus_id"] : null;
		if (empty($sqlArr["EmergencyTeamStatus_id"]) && !empty($data["EmergencyTeam_id"])) {
			$queryStatus = "
				select ET.EmergencyTeamStatus_id
				from EmergencyTeam ET
				where ET.EmergencyTeam_id = :EmergencyTeam_id
			";
			$queryStatusParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
			$resp_ts = $callObject->queryResult($queryStatus, $queryStatusParams);
			if (!empty($resp_ts[0]["EmergencyTeamStatus_id"])) {
				$sqlArr["EmergencyTeamStatus_id"] = $resp_ts[0]["EmergencyTeamStatus_id"];
			}
		}
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			$procedure = "p_EmergencyTeam_ins";
			$sqlArr["EmergencyTeam_id"] = null;
		} else {
			$procedure = "p_EmergencyTeam_upd";
			$sqlArr["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
		}
		$selectString = "
		    emergencyteam_id as \"EmergencyTeam_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$sql = "
			select {$selectString}
			from {$procedure}(
			    emergencyteam_id := :EmergencyTeam_id,
			    emergencyteam_num := :EmergencyTeam_Num,
			    emergencyteam_carnum := :EmergencyTeam_CarNum,
			    emergencyteam_carbrand := :EmergencyTeam_CarBrand,
			    emergencyteam_carmodel := :EmergencyTeam_CarModel,
			    emergencyteam_portradionum := :EmergencyTeam_PortRadioNum,
			    emergencyteam_gpsnum := :EmergencyTeam_GpsNum,
			    lpubuilding_id := :LpuBuilding_id,
			    emergencyteamspec_id := :EmergencyTeamSpec_id,
			    emergencyteam_headshift := :EmergencyTeam_HeadShift,
			    emergencyteam_driver := :EmergencyTeam_Driver,
			    emergencyteam_assistant1 := :EmergencyTeam_Assistant1,
			    emergencyteam_assistant2 := :EmergencyTeam_Assistant2,
			    emergencyteamstatus_id := :EmergencyTeamStatus_id,
			    lpu_id := :Lpu_id,
			    emergencyteam_headshift2 := :EmergencyTeam_HeadShift2,
			    emergencyteam_driver2 := :EmergencyTeam_Driver2,
			    emergencyteam_dutytime := :EmergencyTeam_DutyTime,
			    emergencyteam_istemplate := :EmergencyTeam_isTemplate,
			    cmptabletpc_id := :CMPTabletPC_id,
			    medproductcard_id := :MedProductCard_id,
			    emergencyteam_headshiftworkplace := :EmergencyTeam_HeadShiftWorkPlace,
			    emergencyteam_driverworkplace := :EmergencyTeam_DriverWorkPlace,
			    emergencyteam_assistant1workplace := :EmergencyTeam_Assistant1WorkPlace,
			    emergencyteam_headshift2workplace := :EmergencyTeam_HeadShift2WorkPlace,
			    emergencyteam_templatename := :EmergencyTeam_TemplateName,
			    emergencyteam_phone := :EmergencyTeam_Phone,
			    pmuser_id := :pmUser_id
			);
		";
		$callObject->beginTransaction();
		$result = $callObject->queryResult($sql, $sqlArr);
		if (!$callObject->isSuccessful($result) || empty($result[0]["EmergencyTeam_id"])) {
			$callObject->rollbackTransaction();
			return $result;
		}
		// 2. Сохраняем связь с геосервисом
		if (!empty($data["GeoserviceTransport_id"])) {
			$save_geoservice_rel = $callObject->_saveEmergencyTeamGeoserviceTransportRel(array_merge($data, $result[0]));
			if (!$callObject->isSuccessful($save_geoservice_rel)) {
				$callObject->rollbackTransaction();
				return $save_geoservice_rel;
			}
		} else {
			if (getRegionNick() != "ufa") {
				$callObject->load->model("Wialon_model", "wmodel");
				$callObject->wmodel->deleteMergeEmergencyTeam([
					"EmergencyTeam_id" => $data["EmergencyTeam_id"],
					"pmUser_id" => $data["pmUser_id"],
				]);
			}
		}
		// 3. Сохраняем смену
		if (!$update_duty_time || (empty($data["EmergencyTeamDuty_DTStart"]) || empty($data["EmergencyTeamDuty_DTFinish"]))) {
			$callObject->commitTransaction();
			return $result;
		}
		$exist_dt_query_result = $callObject->_getEmergencyTeamDutyIdByEmergencyTeamId($data);
		$save_duty_result = $callObject->editEmergencyTeamDutyTime([
			"EmergencyTeamDuty_id" => ((empty($exist_dt_query_result[0]["EmergencyTeamDuty_id"])) ? null : $exist_dt_query_result[0]["EmergencyTeamDuty_id"]),
			"EmergencyTeamDuty_DateStart" => $data["EmergencyTeamDuty_DTStart"],
			"EmergencyTeamDuty_DateFinish" => $data["EmergencyTeamDuty_DTFinish"],
			"EmergencyTeam_id" => $result[0]["EmergencyTeam_id"],
			"pmUser_id" => $data["pmUser_id"]
		]);
		if (!$callObject->isSuccessful($save_duty_result)) {
			$callObject->rollbackTransaction();
			return $save_duty_result;
		}
		if (!empty($save_duty_result[0]["EmergencyTeamDuty_id"])) {
			$result[0]["EmergencyTeamDuty_id"] = $save_duty_result[0]["EmergencyTeamDuty_id"];
		}
		$callObject->commitTransaction();
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 */
	public static function _saveEmergencyTeamGeoserviceTransportRel(EmergencyTeam_model4E $callObject, $data)
	{
		$region = getRegionNick();
		if ($region == "ufa") {
			$callObject->load->model("TNC_model", "tncmodel");
			$result = $callObject->tncmodel->mergeEmergencyTeam([
				"EmergencyTeam_id" => $data["EmergencyTeam_id"],
				"TNCTransport_id" => $data["GeoserviceTransport_id"],
				"pmUser_id" => $data["pmUser_id"],
			]);
		} else {
			$callObject->load->model("Wialon_model", "wmodel");
			$result = $callObject->wmodel->mergeEmergencyTeam([
				"EmergencyTeam_id" => $data["EmergencyTeam_id"],
				"WialonEmergencyTeamId" => $data["GeoserviceTransport_id"],
				"pmUser_id" => $data["pmUser_id"],
			]);
		}
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveEmergencyTeams(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeams", $data)) {
			throw new Exception("Не удалось получить список бригад.");
		}
		$result = [];
		foreach ($data["EmergencyTeams"] as $emergency_team) {
			$res = $callObject->checkMedPersonalBusy($emergency_team);
			//при копировании наряда надо проверять активно ли рабочее место сотрудников бригады
			$isWorking = $callObject->checkActiveMedStaffFact($emergency_team);
			if (!$isWorking) {
				throw new Exception("Рабочее место сотрудника на текущую дату неактивно");
			}
			if (!empty($res[0]) && !empty($res[0]["EmergencyTeam_id"])) {
				$EmergencyTeamDuty_DTStart = DateTime::createFromFormat("Y-m-d H:i:s", $res[0]["EmergencyTeamDuty_DTStart"]);
				$EmergencyTeamDuty_DTFinish = DateTime::createFromFormat("Y-m-d H:i:s", $res[0]["EmergencyTeamDuty_DTFinish"]);
				$EmergencyTeamDuty_DTStart = $EmergencyTeamDuty_DTStart->format("d.m.Y H:i");
				$EmergencyTeamDuty_DTFinish = $EmergencyTeamDuty_DTFinish->format("d.m.Y H:i");
				$str_msg = "
					Состав бригады имеет пересечение по составу с бригадой №{$res[0]["EmergencyTeam_Num"]}.</br>
					С датой работы c {$EmergencyTeamDuty_DTStart} по {$EmergencyTeamDuty_DTFinish}.</br>
					Подстанция: {$res[0]["LpuBuilding_Name"]}
				";
				return $callObject->createError(false, $str_msg);
			}
			if ($emergency_team["MedProductCard_id"]) {
				$res = $callObject->checkCarByDate($emergency_team);
				if (empty($res[0])) {
					throw new Exception("Выбранный автомобиль закрыт на дату наряда. Сохранение невозможно");
				}
				if (!empty($emergency_team["checkCarByDutyDate"]) && $emergency_team["checkCarByDutyDate"] == "true") {
					$res = $callObject->checkCarByDutyDate($emergency_team);
					if (!empty($res[0])) {
						$teamNums = [];
						foreach ($res as $emergencyTeamAuto) {
							$teamNums[] = ($emergencyTeamAuto["EmergencyTeam_Num"]);
						};
						$teamNumsString = implode(", ", $teamNums);
						throw new Exception("Автомобиль уже включен в наряд {$teamNumsString}, с которыми есть пересечение.");
					}
				}
			}
			$save_emergency_team_result = $callObject->saveEmergencyTeam(array_merge($data, $emergency_team), false);
			if (!isset($save_emergency_team_result[0]["EmergencyTeam_id"])) {
				continue;
			}
			if ($emergency_team["EmergencyTeamDuty_DTStart"] && $emergency_team["EmergencyTeamDuty_DTFinish"]) {
				//Если идентификатор бригады был передан с параметрами с клиента, значит редактируем смену тоже
				$emergency_team["EmergencyTeamDuty_id"] = ($emergency_team["EmergencyTeam_id"]) ? $emergency_team["EmergencyTeamDuty_id"] : null;
				$emergency_team["EmergencyTeam_id"] = $save_emergency_team_result[0]["EmergencyTeam_id"];
				$callObject->saveEmergencyTeamDutyTime([
					"EmergencyTeamsDutyTimes" => json_encode([$emergency_team]),
					"pmUser_id" => $data["pmUser_id"]
				]);
			}
			$save_emergency_team_result[0]["EmergencyTeam_Num"] = $emergency_team["EmergencyTeam_Num"];
			$result[] = $save_emergency_team_result;
		}
		if (!empty($result[0])) {
			$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
			$operDpt = $callObject->CmpCallCard_model4E->getOperDepartament($data);
			$sql = "
				select *
				from v_SmpUnitParam
				where LpuBuilding_id = :LpuBuilding_pid
				order by SmpUnitParam_id desc
				limit 1
			";
			$operDptParams = $callObject->getFirstRowFromQuery($sql, ["LpuBuilding_pid" => $operDpt["LpuBuilding_pid"]]);
			$result[0]["SmpUnitParam_IsAutoEmergDuty"] = $operDptParams["SmpUnitParam_IsAutoEmergDuty"];
			$result[0]["SmpUnitParam_IsAutoEmergDutyClose"] = $operDptParams["SmpUnitParam_IsAutoEmergDutyClose"];
		}
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function saveEmergencyTeamDrugPack(EmergencyTeam_model4E $callObject, $data)
	{
		if (empty($data["pmUser_id"])) {
			$session_params = getSessionParams();
			$data["pmUser_id"] = $session_params["pmUser_id"];
		}
		$rules = [
			["field" => "EmergencyTeam_id", "label" => "Бригада", "rules" => "", "type" => "id"],
			["field" => "Drug_id", "label" => "Медикамент", "rules" => "", "type" => "id"],
			["field" => "EmergencyTeamDrugPack_Total", "label" => "Количество доз", "rules" => "", "type" => "float", "default" => 0],
			["field" => "EmergencyTeamDrugPack_id", "label" => "Укладка", "rules" => "", "type" => "id", "default" => null],
			["field" => "pmUser_id", "rules" => "required", "label" => "Идентификатор пользователя", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$procedure = (empty($queryParams["EmergencyTeamDrugPack_id"])) ? "p_EmergencyTeamDrugPack_ins" : "p_EmergencyTeamDrugPack_upd";
		$selectString = "
		    emergencyteamdrugpack_id as \"EmergencyTeamDrugPack_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    emergencyteamdrugpack_id := :EmergencyTeamDrugPack_id,
			    emergencyteam_id := :EmergencyTeam_id,
			    drug_id := :Drug_id,
			    emergencyteamdrugpack_total := :EmergencyTeamDrugPack_Total,
			    pmuser_id := :pmUser_id
			);
		";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function saveEmergencyTeamDrugPackMove(EmergencyTeam_model4E $callObject, $data)
	{
		// Получаем идентификатор движения
		if (!empty($data["DocumentUcStr_id"]) && empty($data["EmergencyTeamDrugPackMove_id"])) {
			//Если передан идентификатор строки учетного документа, значит строка пополнения склада (EmergencyTeamDrugPackMove_id)
			//уже существует, необходимо получить её идентификатор и дальнейшие действия проводить с ней
			$ETDrugPackMove_data = $callObject->getEmergencyTeamDrugPackMoveIdByDocumentUcStr($data);
			if (!$callObject->isSuccessful($ETDrugPackMove_data)) {
				return $ETDrugPackMove_data;
			}
			$data["EmergencyTeamDrugPackMove_id"] = (!empty($ETDrugPackMove_data[0]["EmergencyTeamDrugPackMove_id"])) ? $ETDrugPackMove_data[0]["EmergencyTeamDrugPackMove_id"] : null;
		}
		// Получаем идентификатор укладки
		if (empty($data["EmergencyTeamDrugPack_id"])) {
			if (!empty($data["EmergencyTeamDrugPackMove_id"])) {
				$record_result = $callObject->_getDrugPackByDrugPackMove($data);
			} elseif (!empty($data["EmergencyTeam_id"]) && !empty($data["Drug_id"])) {
				$record_result = $callObject->getDrugPackByDrugAndEmergencyTeam($data);
			}
			if (!empty($record_result) && !$callObject->isSuccessful($record_result)) {
				return $record_result;
			}
			// Если укладки не существует, создаём её
			if (empty($record_result[0]["EmergencyTeamDrugPack_id"])) {
				$newDrugPack_result = $callObject->saveEmergencyTeamDrugPack($data);
				if (!$callObject->isSuccessful($newDrugPack_result)) {
					return $newDrugPack_result;
				}
				if (empty($newDrugPack_result[0]["EmergencyTeamDrugPack_id"])) {
					throw new Exception("Ошибка создания записи укладки");
				}
				$data["EmergencyTeamDrugPack_id"] = $newDrugPack_result[0]["EmergencyTeamDrugPack_id"];
			} else {
				$data["EmergencyTeamDrugPack_id"] = $record_result[0]["EmergencyTeamDrugPack_id"];
			}
		}
		$rules = [
			["field" => "EmergencyTeamDrugPackMove_id", "label" => "Идентификатор движения медикамента в укладке", "rules" => "", "type" => "id", "default" => null],
			["field" => "EmergencyTeamDrugPack_id", "label" => "Идентификатор укладки", "rules" => "required", "type" => "id"],
			["field" => "DocumentUcStr_id", "label" => "Идентификатор строки документа", "rules" => "", "type" => "id"],
			["field" => "CmpCallCard_id", "label" => "Идентификатор строки документа", "rules" => "", "type" => "id"],
			["field" => "EmergencyTeamDrugPackMove_Quantity", "label" => "Количество доз", "rules" => "required", "type" => "float"],
			["field" => "pmUser_id", "rules" => "required", "label" => "Идентификатор пользователя", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$callObject->beginTransaction();
		// Сохраняем приход медикаментов на укладку бригады
		$procedure = (empty($data["EmergencyTeamDrugPackMove_id"])) ? "p_EmergencyTeamDrugPackMove_ins" : "p_EmergencyTeamDrugPackMove_upd";
		$selectString = "
		    emergencyteamdrugpackmove_id as \"EmergencyTeamDrugPackMove_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    emergencyteamdrugpackmove_id := :EmergencyTeamDrugPackMove_id,
			    emergencyteamdrugpack_id := :EmergencyTeamDrugPack_id,
			    emergencyteamdrugpackmove_quantity := :EmergencyTeamDrugPackMove_Quantity,
			    cmpcallcard_id := :CmpCallCard_id,
			    documentucstr_id := :DocumentUcStr_id,
			    pmuser_id := :pmUser_id
			);
		";
		$ETDPM_result = $callObject->queryResult($query, $queryParams);
		if (!$callObject->isSuccessful($ETDPM_result)) {
			$callObject->rollbackTransaction();
			return $ETDPM_result;
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
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveEmergencyTeamDutyTime(EmergencyTeam_model4E $callObject, $data)
	{
		/**@var CI_DB_result $query */
		if (!array_key_exists("EmergencyTeamsDutyTimes", $data)) {
			throw new Exception("Отсутствуют необходимые данные.");
		}
		$return = [];
		$arr = json_decode($data["EmergencyTeamsDutyTimes"], true);
		foreach ($arr as $key => $value) {
			if (!empty($value["EmergencyTeamDuty_id"])) {
				$sqlETD = "
					select
						EmergencyTeamDuty_isComesToWork AS \"EmergencyTeamDuty_isComesToWork\",
						to_char(ETD.EmergencyTeamDuty_factToWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factToWorkDT\",
						to_char(ETD.EmergencyTeamDuty_factEndWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factEndWorkDT\",
						EmergencyTeamDuty_isClose AS \"EmergencyTeamDuty_isClose\",
						to_char(ETD.EmergencyTeamDuty_comesToWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_comesToWorkDT\"
					from EmergencyTeamDuty etd
					where EmergencyTeamDuty_id = :EmergencyTeamDuty_id
					limit 1
				";
				$query = $callObject->db->query($sqlETD, ["EmergencyTeamDuty_id" => $value["EmergencyTeamDuty_id"]]);
				if (!is_object($query)) {
					return false;
				}
				$resp = $query->result("array");
				$value = array_merge($value, $resp[0]);
			}
			$sqlArr = [
				"EmergencyTeamDuty_DTStart" => $value["EmergencyTeamDuty_DTStart"],
				"EmergencyTeamDuty_DTFinish" => $value["EmergencyTeamDuty_DTFinish"],
				"EmergencyTeam_id" => $value["EmergencyTeam_id"],
				"EmergencyTeamDuty_ChangeComm" => !empty($value["EmergencyTeamDuty_ChangeComm"]) ? $value["EmergencyTeamDuty_ChangeComm"] : null,
				"EmergencyTeamDuty_Comm" => !empty($value["EmergencyTeamDuty_Comm"]) ? $value["EmergencyTeamDuty_Comm"] : null,
				"EmergencyTeamDuty_isComesToWork" => !empty($value["EmergencyTeamDuty_isComesToWork"]) ? $value["EmergencyTeamDuty_isComesToWork"] : null,
				"EmergencyTeamDuty_isClose" => !empty($value["EmergencyTeamDuty_isClose"]) ? $value["EmergencyTeamDuty_isClose"] : null,
				"EmergencyTeamDuty_comesToWorkDT" => !empty($value["EmergencyTeamDuty_comesToWorkDT"]) ? $value["EmergencyTeamDuty_comesToWorkDT"] : null,
				"EmergencyTeamDuty_factToWorkDT" => !empty($value["EmergencyTeamDuty_factToWorkDT"]) ? $value["EmergencyTeamDuty_factToWorkDT"] : null,
				"EmergencyTeamDuty_factEndWorkDT" => !empty($value["EmergencyTeamDuty_factEndWorkDT"]) ? $value["EmergencyTeamDuty_factEndWorkDT"] : null,
				"EmergencyTeam_Head1StartTime" => !empty($value["EmergencyTeam_Head1StartTime"]) ? $value["EmergencyTeam_Head1StartTime"] : null,
				"EmergencyTeam_Head1FinishTime" => !empty($value["EmergencyTeam_Head1FinishTime"]) ? $value["EmergencyTeam_Head1FinishTime"] : null,
				"EmergencyTeam_Head2StartTime" => !empty($value["EmergencyTeam_Head2StartTime"]) ? $value["EmergencyTeam_Head2StartTime"] : null,
				"EmergencyTeam_Head2FinishTime" => !empty($value["EmergencyTeam_Head2FinishTime"]) ? $value["EmergencyTeam_Head2FinishTime"] : null,
				"EmergencyTeam_Assistant1StartTime" => !empty($value["EmergencyTeam_Assistant1StartTime"]) ? $value["EmergencyTeam_Assistant1StartTime"] : null,
				"EmergencyTeam_Assistant1FinishTime" => !empty($value["EmergencyTeam_Assistant1FinishTime"]) ? $value["EmergencyTeam_Assistant1FinishTime"] : null,
				"EmergencyTeam_Assistant2StartTime" => !empty($value["EmergencyTeam_Assistant2StartTime"]) ? $value["EmergencyTeam_Assistant2StartTime"] : null,
				"EmergencyTeam_Assistant2FinishTime" => !empty($value["EmergencyTeam_Assistant2FinishTime"]) ? $value["EmergencyTeam_Assistant2FinishTime"] : null,
				"EmergencyTeam_Driver1StartTime" => !empty($value["EmergencyTeam_Driver1StartTime"]) ? $value["EmergencyTeam_Driver1StartTime"] : null,
				"EmergencyTeam_Driver1FinishTime" => !empty($value["EmergencyTeam_Driver1FinishTime"]) ? $value["EmergencyTeam_Driver1FinishTime"] : null,
				"EmergencyTeam_Driver2StartTime" => !empty($value["EmergencyTeam_Driver2StartTime"]) ? $value["EmergencyTeam_Driver2StartTime"] : null,
				"EmergencyTeam_Driver2FinishTime" => !empty($value["EmergencyTeam_Driver2FinishTime"]) ? $value["EmergencyTeam_Driver2FinishTime"] : null,
				"EmergencyTeamDuty_IsCancelledStart" => !empty($value["EmergencyTeamDuty_IsCancelledStart"]) ? $value["EmergencyTeamDuty_IsCancelledStart"] : null,
				"EmergencyTeamDuty_IsCancelledClose" => !empty($value["EmergencyTeamDuty_IsCancelledClose"]) ? $value["EmergencyTeamDuty_IsCancelledClose"] : null,
				"pmUser_id" => $data["pmUser_id"]
			];
			$procedure = (!isset($value["EmergencyTeamDuty_id"]) || !$value["EmergencyTeamDuty_id"])?"p_emergencyteamduty_ins":"p_emergencyteamduty_upd";
			if (isset($value["EmergencyTeamDuty_id"]) && $value["EmergencyTeamDuty_id"] != 0) {
				$sqlArr["EmergencyTeamDuty_id"] = (int)$value["EmergencyTeamDuty_id"];
			}
			$selectString = "
			    emergencyteamduty_id as \"EmergencyTeamDuty_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			";
			$sql = "
				select {$selectString}
				from {$procedure}(
				    emergencyteamduty_id := :EmergencyTeamDuty_id,
				    emergencyteamduty_dtstart := :EmergencyTeamDuty_DTStart,
				    emergencyteamduty_dtfinish := :EmergencyTeamDuty_DTFinish,
				    emergencyteam_id := :EmergencyTeam_id,
				    emergencyteamduty_iscomestowork := :EmergencyTeamDuty_isComesToWork,
				    emergencyteamduty_comestoworkdt := to_timestamp('1900-01-01 ' || :EmergencyTeamDuty_comesToWorkDT,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteamduty_facttoworkdt := to_timestamp('1900-01-01 ' || :EmergencyTeamDuty_factToWorkDT,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_head1starttime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Head1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_head1finishtime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Head1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_head2starttime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Head2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_head2finishtime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Head2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_assistant1starttime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_assistant1finishtime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_assistant2starttime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_assistant2finishtime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_driver1finishtime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_driver1starttime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_driver2starttime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteam_driver2finishtime := to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
				    emergencyteamduty_changecomm := :EmergencyTeamDuty_ChangeComm,
				    emergencyteamduty_iscancelledstart := :EmergencyTeamDuty_IsCancelledStart,
				    emergencyteamduty_iscancelledclose := :EmergencyTeamDuty_IsCancelledClose,
				    pmuser_id := :pmUser_id
				);
			";
			$query = $callObject->db->query($sql, $sqlArr);
			if (!is_object($query)) {
				return false;
			}
			$return[] = $query->result("array");
		}
		return $return;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveEmergencyTeamProposalLogicRule(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			$data["EmergencyTeamProposalLogic_id"] = 0;
			$procedure = "p_EmergencyTeamProposalLogic_ins";
		} else {
			$procedure = "p_EmergencyTeamProposalLogic_upd";
		}
		if (!array_key_exists("CmpReason_id", $data) || !$data["CmpReason_id"]) {
			throw new Exception("Не указан повод.");
		}
		if (!isset($data["EmergencyTeamProposalLogic_AgeFrom"]) && (!isset($data["EmergencyTeamProposalLogic_AgeTo"]))) {
			throw new Exception("Хотя бы одно из полей (Возраст С, Возраст ПО) должно быть заполнено.");
		}
		if (isset($data["EmergencyTeamProposalLogic_AgeFrom"]) && isset($data["EmergencyTeamProposalLogic_AgeTo"]) && ($data["EmergencyTeamProposalLogic_AgeFrom"] > $data["EmergencyTeamProposalLogic_AgeTo"])) {
			throw new Exception("Значение поля \"Возраст С\" не может быть больше значения поля \"Возраст ПО\"");
		}
		$consistencyQueryParams = [
			"CmpReason_id" => $data["CmpReason_id"],
			"Sex_id" => (isset($data["Sex_id"])) ? $data["Sex_id"] : null,
			"EmergencyTeamProposalLogic_AgeFrom" => (isset($data["EmergencyTeamProposalLogic_AgeFrom"])) ? $data["EmergencyTeamProposalLogic_AgeFrom"] : 120,
			"EmergencyTeamProposalLogic_AgeTo" => (isset($data["EmergencyTeamProposalLogic_AgeTo"])) ? $data["EmergencyTeamProposalLogic_AgeTo"] : 0,
			"Lpu_id" => $data["Lpu_id"]
		];
		$consistencyCheckQuery = "
			select ETPL.EmergencyTeamProposalLogic_id as \"EmergencyTeamProposalLogic_id\"
			from v_EmergencyTeamProposalLogic ETPL
			where
				not (coalesce(:EmergencyTeamProposalLogic_AgeFrom, 0) > coalesce(ETPL.EmergencyTeamProposalLogic_AgeTo, 120) or coalesce(:EmergencyTeamProposalLogic_AgeTo, 120) < coalesce(ETPL.EmergencyTeamProposalLogic_AgeFrom, 0))
				and ETPL.CmpReason_id = :CmpReason_id
				and (coalesce(ETPL.Sex_id, 0) = 0 or coalesce(:Sex_id, 0) = 0 or coalesce(ETPL.Sex_id, 0 = coalesce(:Sex_id, 0)))
				and ETPL.Lpu_id = :Lpu_id
		";
		$resultConsistencyCheckQuery = $callObject->db->query($consistencyCheckQuery, $consistencyQueryParams);
		if (!is_object($resultConsistencyCheckQuery)) {
			throw new Exception("Введенное правило противоречит одному из существующих правил с соответствующим поводом вызова.");
		}
		$resultConsistencyCheckQueryArray = $resultConsistencyCheckQuery->result('array');
		if (count($resultConsistencyCheckQueryArray) > 0) {
			throw new Exception("Введенное правило противоречит одному из существующих правил с соответствующим поводом вызова.");
		}
		$selectString = "
		    emergencyteamproposallogic_id as \"EmergencyTeamProposalLogic_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    emergencyteamproposallogic_id := :EmergencyTeamProposalLogic_id,
			    cmpreason_id := :CmpReason_id,
			    sex_id := :Sex_id,
			    emergencyteamproposallogic_agefrom := :EmergencyTeamProposalLogic_AgeFrom,
			    emergencyteamproposallogic_ageto := :EmergencyTeamProposalLogic_AgeTo,
			    lpu_id := :Lpu_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"],
			"CmpReason_id" => $data["CmpReason_id"],
			"Sex_id" => isset($data["Sex_id"]) ? $data["Sex_id"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"EmergencyTeamProposalLogic_AgeFrom" => isset($data["EmergencyTeamProposalLogic_AgeFrom"]) ? $data["EmergencyTeamProposalLogic_AgeFrom"] : null,
			"EmergencyTeamProposalLogic_AgeTo" => isset($data["EmergencyTeamProposalLogic_AgeTo"]) ? $data["EmergencyTeamProposalLogic_AgeTo"] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.");
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveEmergencyTeamProposalLogicRuleSequence(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			throw new Exception("Не указан идентификатор правила.");
		}
		if (!array_key_exists("EmergencyTeamSpec_id", $data) || !$data["EmergencyTeamSpec_id"]) {
			throw new Exception("Не указан идентификатор профиля бригады.");
		}
		if (!array_key_exists("EmergencyTeamProposalLogicRule_SequenceNum", $data)) {
			throw new Exception("Не указан порядок профиля");
		}
		if (isset($data["EmergencyTeamProposalLogicRule_id"]) && $data["EmergencyTeamProposalLogicRule_id"] != 0) {
			$procedure = "p_EmergencyTeamProposalLogicRule_upd";
		} else {
			$data["EmergencyTeamProposalLogicRule_id"] = 0;
			$procedure = "p_EmergencyTeamProposalLogicRule_ins";
		}
		$selectString = "
		    emergencyteamproposallogicrule_id as \"EmergencyTeamProposalLogicRule_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    emergencyteamproposallogicrule_id := :EmergencyTeamProposalLogicRule_id,
			    emergencyteamproposallogic_id := :EmergencyTeamProposalLogic_id,
			    emergencyteamspec_id := :EmergencyTeamSpec_id,
			    emergencyteamproposallogicrule_sequencenum := :EmergencyTeamProposalLogicRule_SequenceNum,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EmergencyTeamProposalLogicRule_id" => $data["EmergencyTeamProposalLogicRule_id"],
			"EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"],
			"EmergencyTeamSpec_id" => $data["EmergencyTeamSpec_id"],
			"EmergencyTeamProposalLogicRule_SequenceNum" => $data["EmergencyTeamProposalLogicRule_SequenceNum"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.");
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveEmergencyTeamVigil(EmergencyTeam_model4E $callObject, $data)
	{
		$procedure = (empty($data["CmpEmTeamDuty_id"])) ? "p_CmpEmTeamDuty_ins" : "p_CmpEmTeamDuty_upd";
		if (empty($data["CmpEmTeamDuty_id"])) {
			$data["CmpEmTeamDuty_id"] = null;
		}
		//предварительные проверки сохранения дежурства
		$presql = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				case when ETD.EmergencyTeamDuty_DTStart > :CmpEmTeamDuty_PlanBegDT or ETD.EmergencyTeamDuty_DTFinish < :CmpEmTeamDuty_PlanBegDT
				    then 1
					else
					    case when ETD.EmergencyTeamDuty_DTStart > :CmpEmTeamDuty_PlanEndDT or ETD.EmergencyTeamDuty_DTFinish < :CmpEmTeamDuty_PlanEndDT
					        then 2
							else
							    case when ETD.EmergencyTeamDuty_factToWorkDT > :CmpEmTeamDuty_FactBegDT
							        then 3
									else
									    case when CETD.CmpEmTeamDuty_PlanBegDT <= :CmpEmTeamDuty_PlanBegDT and CETD.CmpEmTeamDuty_PlanEndDT >= :CmpEmTeamDuty_PlanEndDT
									        then 4
											else 0
										end
								end
						end
				end as \"errCode\"
			from
				v_EmergencyTeam as ET 
				left join v_CmpEmTeamDuty CETD on CETD.EmergencyTeam_id = ET.EmergencyTeam_id				
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			WHERE ET.EmergencyTeam_id = :EmergencyTeam_id
			  and CETD.CmpEmTeamDuty_id != :CmpEmTeamDuty_id
		";
		$query = $callObject->db->query($presql, $data);
		if (is_object($query)) {
			$preRes = $query->result_array();
			if (!empty($preRes[0]) && !empty($preRes[0]["errCode"])) {
				$errMsg = "Ошибка при проверке пересечений дат";
				switch ($preRes[0]["errCode"]) {
					case 1:
					{
						$errMsg = "Плановое время начала дежурства должно входить в плановый период работы наряда";
						break;
					}
					case 2:
					{
						$errMsg = "Плановое время окончания дежурства должно входить в плановый период работы наряда";
						break;
					}
					case 3:
					{
						$errMsg = "Фактическое время окончания дежурства должно входить в фактичекий период работы наряда";
						break;
					}
					case 4:
					{
						$errMsg = "Плановый период дежурства не должен пересекаться с плановым периодом другого дежурства";
						break;
					}
				}
				throw new Exception($errMsg);
			}
		}
		$params = [
			"emergencyteam_id",
			"unformalizedaddressdirectory_id",
			"klrgn_id",
			"klsubrgn_id",
			"klcity_id",
			"kltown_id",
			"klstreet_id",
			"cmpemteamduty_house",
			"cmpemteamduty_corpus",
			"cmpemteamduty_flat",
			"cmpemteamduty_planbegdt",
			"cmpemteamduty_planenddt",
			"cmpemteamduty_factbegdt",
			"cmpemteamduty_factenddt",
			"cmpemteamduty_description",
			"pmuser_id"
		];
		$fieldsList = [];
		if(!empty($data["CmpEmTeamDuty_id"])) {
			$fieldsList[] = "cmpemteamduty_id := :CmpEmTeamDuty_id";
		}
		$queryParams = ["CmpEmTeamDuty_id" => (!empty($data["CmpEmTeamDuty_id"]))?$data["CmpEmTeamDuty_id"]:null];
		foreach ($params as $param) {
			if(@$data[$param] != null) {
				$fieldsList[] = "{$param} := :{$param}";
				$queryParams["{$param}"] = $data[$param];
			}
		}
		$selectString = "
		    cmpemteamduty_id as \"CmpEmTeamDuty_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$fieldsListString = implode(",", $fieldsList);
		$sql = "
			select {$selectString}
			from {$procedure}(
			    {$fieldsListString}
			);
		";
		/**@var CI_DB_result $query */
		$query = $callObject->db->query($sql, $queryParams);
		if (!is_object($query)) {
			return false;
		}
		return $query->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return mixed|bool
	 * @throws Exception
	 */
	public static function editEmergencyTeamDutyTime(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !array_key_exists("EmergencyTeamDuty_DateStart", $data) || !array_key_exists("EmergencyTeamDuty_DateFinish", $data)) {
			throw new Exception("Отсутствуют необходимые данные. Возможно вы не указали ни одной смены или не выбрали бригаду.");
		}
		$sqlArr = [
			"EmergencyTeamDuty_id" => array_key_exists("EmergencyTeamDuty_id", $data) && $data["EmergencyTeamDuty_id"] ? $data["EmergencyTeamDuty_id"] : null,
			"EmergencyTeamDuty_DTStart" => $data["EmergencyTeamDuty_DateStart"],
			"EmergencyTeamDuty_DTFinish" => $data["EmergencyTeamDuty_DateFinish"],
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$result = $callObject->saveEmergencyTeamDutyTime([
			"EmergencyTeamsDutyTimes" => json_encode([$sqlArr]),
			"pmUser_id" => $data["pmUser_id"]
		]);
		if (sizeof($result)) {
			return $result[0];
		}
		return false;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function editEmergencyTeamVigilTimes(EmergencyTeam_model4E $callObject, $data)
	{
		$presql = "
			select
				cmpemteamduty_id as \"CmpEmTeamDuty_id\",
				emergencyteam_id as \"EmergencyTeam_id\",
				unformalizedaddressdirectory_id as \"UnformalizedAddressDirectory_id\",
				klrgn_id as \"KLRgn_id\",
				klsubrgn_id as \"KLSubRgn_id\",
				klcity_id as \"KLCity_id\",
				kltown_id as \"KLTown_id\",
				klstreet_id as \"KLStreet_id\",
				cmpemteamduty_house as \"CmpEmTeamDuty_House\",
				cmpemteamduty_corpus as \"CmpEmTeamDuty_Corpus\",
				cmpemteamduty_flat as \"CmpEmTeamDuty_Flat\",
				cmpemteamduty_planbegdt as \"CmpEmTeamDuty_PlanBegDT\",
				cmpemteamduty_planenddt as \"CmpEmTeamDuty_PlanEndDT\",
				cmpemteamduty_factbegdt as \"CmpEmTeamDuty_FactBegDT\",
				cmpemteamduty_factenddt as \"CmpEmTeamDuty_FactEndDT\",
				pmuser_insid as \"pmUser_insID\",
				pmuser_updid as \"pmUser_updID\",
				cmpemteamduty_insdt as \"cmpemteamduty_insdt\",
				cmpemteamduty_upddt as \"cmpemteamduty_upddt\",
				cmpemteamduty_description as \"CmpEmTeamDuty_Description\"
			from v_CmpEmTeamDuty
			where CmpEmTeamDuty_id = :CmpEmTeamDuty_id
		";
		/**@var CI_DB_result $query */
		$query = $callObject->db->query($presql, $data);
		$result = $query->first_row("array");
		foreach ($data as $key => $value) {
			if (!empty($value)) {
				$result[$key] = $value;
			}
		}
		$res = $callObject->saveEmergencyTeamVigil($result);
		return $res;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function setEmergencyTeamsCloseList(EmergencyTeam_model4E $callObject, $data)
	{
		$arr = json_decode($data["EmergencyTeamsClose"]);
		foreach ($arr as $key => $value) {
			$EmergencyTeam_id = $value->EmergencyTeam_id;
			$EmergencyTeamDuty_id = $value->EmergencyTeamDuty_id;
			$closed = ($value->closed) ? 2 : 1;
			$sqlArr = [
				"EmergencyTeam_id" => $EmergencyTeam_id,
				"EmergencyTeamDuty_id" => $EmergencyTeamDuty_id,
				"EmergencyTeamDuty_isClose" => $closed,
				"pmUser_id" => $data["pmUser_id"]
			];
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_emergencyteamduty_setclose(
				    emergencyteamduty_id := :EmergencyTeamDuty_id,
				    emergencyteam_id := :EmergencyTeam_id,
				    emergencyteamduty_isclose := :EmergencyTeamDuty_isClose,
				    pmuser_id := :pmUser_id
				);
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $sqlArr);
			if (!is_object($result)) {
				throw new Exception("Ошибка при сохранении");
			}
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function setEmergencyTeamStatus(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			return false;
		}
		if ((!array_key_exists("EmergencyTeamStatus_id", $data) || !$data["EmergencyTeamStatus_id"]) && isset($data["EmergencyTeamStatus_Code"])) {
			$data["EmergencyTeamStatus_id"] = $callObject->getEmergencyTeamStatusIdByCode($data["EmergencyTeamStatus_Code"]);
			if (!isset($data["EmergencyTeamStatus_id"])) {
				return false;
			}
		}
		//проверка на текущий статус
		$sqlStat = "
			select
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
			    ET.EmergencyTeam_id as \"EmergencyTeam_id\"
			from v_EmergencyTeam as ET
			where ET.EmergencyTeam_id = :EmergencyTeam_id
			limit 1
		";
		/**
		 * @var CI_DB_result $resultStat
		 * @var CI_DB_result $result
		 */
		$resultStat = $callObject->db->query($sqlStat, $data);
		$resultStat = $resultStat->result("array");
		if (empty($resultStat[0])) {
			return false;
		}
		$resultCall = $callObject->getCallOnEmergencyTeam($data);
		//уходим если статус аналогичный и вызов идентичный
		if (!empty($resultStat[0]) && $resultStat[0]["EmergencyTeamStatus_id"]) {
			$newCard_id = (!empty($data["CmpCallCard_id"])) ? $data["CmpCallCard_id"] : null;
			$curCard_id = !empty($resultCall[0]) && $resultCall[0]["CmpCallCard_id"] > 0 ? $resultCall[0]["CmpCallCard_id"] : null;
			if ($resultStat[0]["EmergencyTeamStatus_id"] == $data["EmergencyTeamStatus_id"] && $newCard_id && $newCard_id == $curCard_id) {
				return false;
			}
		}
		$CmpCallCard_IsUpd = "";
		if (isset($data["CmpCallCard_IsUpd"])) {
			$CmpCallCard_IsUpd = "cmpcallcard_isupd := :CmpCallCard_IsUpd,";
		}
		$query = "
			select
			    :EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_emergencyteam_setstatus(
			    emergencyteam_id := :EmergencyTeam_id,
			    emergencyteamstatus_id := :EmergencyTeamStatus_id,
			    armtype_id := :ARMType_id,
			    cmpcallcard_id := :CmpCallCard_id,
			    {$CmpCallCard_IsUpd}
			    pmuser_id := :pmUser_id
			);
		";
		$sqlArr = [
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"EmergencyTeamStatus_id" => $data["EmergencyTeamStatus_id"],
			"pmUser_id" => $data["pmUser_id"],
			"ARMType_id" => (!empty($data["ARMType_id"])) ? $data["ARMType_id"] : null,
			"CmpCallCard_id" => (!empty($data["CmpCallCard_id"])) ? $data["CmpCallCard_id"] : null,
			"CmpCallCard_IsUpd" => (isset($data["CmpCallCard_IsUpd"])) ? $data["CmpCallCard_IsUpd"] : null
		];
		$result = $callObject->db->query($query, $sqlArr);
		if (!is_object($result)) {
			return false;
		}
		$resp_save = $result->result("array");
		$data["EmergencyTeamStatus_Code"] = $callObject->getEmergencyTeamStatusCodeById($data["EmergencyTeamStatus_id"]);
		if (isset($data["CmpCallCard_id"]) && $data["CmpCallCard_id"] > 0) {
			//установка временных параметров карты в зависимости от статуса бригады
			$callObject->setTimesCardFromEmergencyTeam([
				"EmergencyTeamStatus_id" => $data["EmergencyTeamStatus_id"],
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"pmUser_id" => $data["pmUser_id"]
			]);
			$callObject->load->model("CmpCallCard_model", "CmpCallCard_model");
			$CmpCallCardEventType_id = $callObject->CmpCallCard_model->getCmpCallCardEventTypeIdByEmergencyTeamStatusId($data["EmergencyTeamStatus_id"]);
			if ($CmpCallCardEventType_id) {
				$data["CmpCallCardEventType_id"] = $CmpCallCardEventType_id;
				$callObject->CmpCallCard_model->setCmpCallCardEvent($data);
			}
			// отправляем сообщение в ActiveMQ
			if (!empty($resp_save[0]["EmergencyTeamStatus_id"]) && defined("STOMPMQ_MESSAGE_DESTINATION_EMERGENCY")) {
				$callObject->CmpCallCard_model->checkSendReactionToActiveMQ([
					"EmergencyTeamStatus_id" => $resp_save[0]["EmergencyTeamStatus_id"],
					"EmergencyTeam_id" => $data["EmergencyTeam_id"],
					"CmpCallCard_id" => $data["CmpCallCard_id"]
				]);
			}
			//Статусы "Выехал на вызов" и "Приезд на вызов" дублируем для всех попутных
			if (in_array($data["EmergencyTeamStatus_Code"], [1, 2])) {
				$callssql = "
					select C.cmpcallcard_id as \"CmpCallCard_id\"
					from
						v_CmpCallCard C
						left join v_CmpCallType CT on CT.CmpCallType_id = C.CmpCallType_id
					where CmpCallCard_rid = :CmpCallCard_rid
					  and CT.CmpCallType_Code = 4
					  and C.CmpCallCardStatusType_id = 2
					order by C.CmpCallCard_prmDT
				";
				$callssqlParams = ["CmpCallCard_rid" => $data["CmpCallCard_id"]];
				$callsres = $callObject->db->query($callssql, $callssqlParams);
				$calls = $callsres->result("array");
				if (is_array($calls) && count($calls) > 0) {
					foreach ($calls as $call) {
						$prms = [
							"EmergencyTeam_id" => $data["EmergencyTeam_id"],
							"EmergencyTeamStatus_id" => $data["EmergencyTeamStatus_id"],
							"CmpCallCard_id" => $call["CmpCallCard_id"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$callObject->setEmergencyTeamStatus($prms);
					}
				}
			}
		}
		if (!isset($data["EmergencyTeamStatus_id"])) {
			return false;
		}
		if ($data["EmergencyTeamStatus_Code"] == 4) {
			$resultStat = $callObject->getCallOnEmergencyTeam($sqlArr);
			if ($resultStat && $resultStat[0]) {
				$sqlArr["EmergencyTeamStatus_id"] = $resultStat[0]["EmergencyTeamStatus_id"];
				$sqlArr["EmergencyTeamStatus_Code"] = null;
				//чтобы не плодить одинаковые статусы
				if (($resultStat[0]["EmergencyTeamStatus_id"] != $data["EmergencyTeamStatus_id"]) && $sqlArr["CmpCallCard_id"] != $resultStat[0]["CmpCallCard_id"]) {
					$callObject->setEmergencyTeamStatus($sqlArr);
				}
			} else {
				//установка статуса на "свободно", если нет других вызовов
				$sqlArr["EmergencyTeamStatus_Code"] = 13;
				$sqlArr["EmergencyTeamStatus_id"] = null;
				$sqlArr["CmpCallCard_id"] = null;
				$callObject->setEmergencyTeamStatus($sqlArr);
			}
		}
		return $resp_save;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return bool
	 */
	public static function setTimesCardFromEmergencyTeam(EmergencyTeam_model4E $callObject, $data)
	{
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpCallCard_Tper as \"CmpCallCard_Tper\",
				CCC.CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
				CCC.CmpCallCard_Przd as \"CmpCallCard_Przd\",
				CCC.CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
				CCC.CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
				CCC.CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
				CCC.CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
				CCC.CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
				CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
				ETSH.EmergencyTeamStatusHistory_insDT as \"EmergencyTeamStatusHistory_insDT\",
				ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\"
			from
				v_CmpCallCard CCC
				left join v_EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_EmergencyTeamStatus as ETS  on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
				left join v_EmergencyTeamStatusHistory ETSH on ETSH.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id and  ETSH.EmergencyTeam_id = ET.EmergencyTeam_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
			order by ETSH.EmergencyTeamStatusHistory_insDT desc
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$cardParams = $callObject->db->query($query, $queryParams);
		if (!is_object($cardParams)) {
			return false;
		}
		$cardParams = $cardParams->result("array");
		if (!is_array($cardParams) || count($cardParams) == 0) {
			return false;
		}
		$cardParams = $cardParams[0];
		switch ($cardParams["EmergencyTeamStatus_Code"]) {
			case 1:
				//выехал на вызов
				$cardParams["CmpCallCard_Vyez"] = (isset($cardParams["CmpCallCard_Vyez"])) ? $cardParams["CmpCallCard_Vyez"] : $cardParams["EmergencyTeamStatusHistory_insDT"];
				break;
			case 2:
				//приезд на вызов
				$cardParams["CmpCallCard_Przd"] = (isset($cardParams["CmpCallCard_Przd"])) ? $cardParams["CmpCallCard_Przd"] : $cardParams["EmergencyTeamStatusHistory_insDT"];
				break;
			case 3:
			case 53:
				//Госпитализация (перевозка)
				$cardParams["CmpCallCard_Tgsp"] = (isset($cardParams["CmpCallCard_Tgsp"])) ? $cardParams["CmpCallCard_Tgsp"] : $cardParams["EmergencyTeamStatusHistory_insDT"];
				break;
			case 17:
				//Прибытие в МО
				$cardParams["CmpCallCard_HospitalizedTime"] = (isset($cardParams["CmpCallCard_HospitalizedTime"])) ? $cardParams["CmpCallCard_HospitalizedTime"] : $cardParams["EmergencyTeamStatusHistory_insDT"];
				break;
			case 13:
			case 4:
				//Конец обслуживания
				$cardParams["CmpCallCard_Tisp"] = (isset($cardParams["CmpCallCard_Tisp"])) ? $cardParams["CmpCallCard_Tisp"] : $cardParams["EmergencyTeamStatusHistory_insDT"];
				break;
			default:
				return false;
				break;
		}
		$cardParams["pmUser_id"] = $data["pmUser_id"];
		$callObject->load->model("CmpCallCard_model4E", "cardModel");
		$result = $callObject->cardModel->saveCmpCallCardTimes($cardParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function setEmergencyTeamsWorkComingList(EmergencyTeam_model4E $callObject, $data)
	{
		$arr = json_decode($data["EmergencyTeamsDutyTimesAndComing"]);
		$res_array = [];
		foreach ($arr as $key => $value) {
			$EmergencyTeam_id = $value->EmergencyTeam_id;
			$EmergencyTeamDuty_id = $value->EmergencyTeamDuty_id;
			$Date_start = $value->EmergencyTeamDuty_DTStart;
			$Date_finish = $value->EmergencyTeamDuty_DTFinish;
			$FactDate_start = $value->EmergencyTeamDuty_factToWorkDT;
			$FactDate_end = $value->EmergencyTeamDuty_factEndWorkDT;
			$ComesToWork = null;
			if ($value->ComesToWork != null) {
				$ComesToWork = ($value->ComesToWork) ? 2 : 1;
			}
			$sqlArr = [
				"EmergencyTeam_id" => $EmergencyTeam_id,
				"EmergencyTeamDuty_id" => $EmergencyTeamDuty_id,
				"EmergencyTeamDuty_DTStart" => DateTime::createFromFormat("Y-m-d H:i:s", $Date_start),
				"EmergencyTeamDuty_DTFinish" => DateTime::createFromFormat("Y-m-d H:i:s", $Date_finish),
				"EmergencyTeamDuty_factToWorkDT" => !empty($FactDate_start) ? DateTime::createFromFormat("Y-m-d H:i:s", $FactDate_start) : null,
				"EmergencyTeamDuty_factEndWorkDT" => !empty($FactDate_end) ? DateTime::createFromFormat("Y-m-d H:i:s", $FactDate_end) : null,
				"EmergencyTeamDuty_isClose" => ($value->closed) ? 2 : 1,
				"ComesToWork" => $ComesToWork,
				"pmUser_id" => $data["pmUser_id"],
				"EmergencyTeamDuty_Comm" => !empty($value->EmergencyTeamDuty_Comm) ? $value->EmergencyTeamDuty_Comm : null,
				"EmergencyTeamDuty_ChangeComm" => !empty($value->EmergencyTeamDuty_ChangeComm) ? $value->EmergencyTeamDuty_ChangeComm : null,
				"EmergencyTeamDuty_IsCancelledStart" => !empty($value->EmergencyTeamDuty_IsCancelledStart) ? 2 : null,
				"EmergencyTeamDuty_IsCancelledClose" => !empty($value->EmergencyTeamDuty_IsCancelledClose) ? 2 : null,
			];
			$query = "
				select
				    emergencyteamduty_id as \"EmergencyTeamDuty_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_emergencyteamduty_upd(
				    emergencyteamduty_id := :EmergencyTeamDuty_id,
				    emergencyteamduty_dtstart := :EmergencyTeamDuty_DTStart,
				    emergencyteamduty_dtfinish := :EmergencyTeamDuty_DTFinish,
				    emergencyteam_id := :EmergencyTeam_id,
				    emergencyteamduty_iscomestowork := :ComesToWork,
				    emergencyteamduty_comestoworkdt := tzgetdate(),
				    emergencyteamduty_facttoworkdt := :EmergencyTeamDuty_factToWorkDT,
				    emergencyteamduty_isclose := :EmergencyTeamDuty_isClose,
				    emergencyteamduty_comm := :EmergencyTeamDuty_Comm,
				    emergencyteamduty_changecomm := :EmergencyTeamDuty_ChangeComm,
				    emergencyteamduty_factendworkdt := :EmergencyTeamDuty_factEndWorkDT,
				    emergencyteamduty_iscancelledstart := :EmergencyTeamDuty_IsCancelledStart,
				    emergencyteamduty_iscancelledclose := :EmergencyTeamDuty_IsCancelledClose,
				    pmuser_id := :pmUser_id
				);
			";
			$result = $callObject->db->query($query, $sqlArr);
			if (!is_object($result)) {
				throw new Exception("Ошибка при сохранении");
			}
			$object_res = $result->result("array");
			array_push($res_array, $object_res[0]["res"]);
			if ($ComesToWork == 2) {
				if ($EmergencyTeamStatus_id = $callObject->getEmergencyTeamStatusIdByCode(13)) {
					$callObject->setEmergencyTeamStatus(array_merge($sqlArr, [
						"EmergencyTeamStatus_id" => $EmergencyTeamStatus_id,
						"ARMType_id" => isset($data["ARMType"]) ? (int)$data["ARMType"] : null
					]));
				}
			}
		}
		return $res_array;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public static function setEmergencyTeamWorkComing(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeamDuty_id", $data) || !$data["EmergencyTeamDuty_id"]) {
			throw new Exception("Не указана смена бригады.");
		}
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			throw new Exception("Не указана бригада.");
		}
		if (!array_key_exists("EmergencyTeamDuty_isComesToWork", $data) || !$data["EmergencyTeamDuty_isComesToWork"]) {
			throw new Exception("Не указан флаг выхода на смену бригады.");
		}
		$sql = "
			select
			    emergencyteamduty_id as \"EmergencyTeamDuty_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_emergencyteamduty_upd(
			    emergencyteamduty_id := :EmergencyTeamDuty_id,
			    emergencyteam_id := :EmergencyTeam_id,
			    emergencyteamduty_iscomestowork := :EmergencyTeamDuty_isComesToWork,
			    emergencyteamduty_comestoworkdt := getdate(),
			    pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"EmergencyTeamDuty_id" => $data["EmergencyTeamDuty_id"],
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"EmergencyTeamDuty_isComesToWork" => $data["EmergencyTeamDuty_isComesToWork"] == 2 ? 2 : 1,
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = $callObject->db->query($sql, $sqlParams);
		if (!is_object($query)) {
			throw new Exception("Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.");
		}
		return $query->result_array();
	}
}