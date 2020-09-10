<?php

class LpuStructure_model_save
{
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public static function saveLpuUnit(LpuStructure_model $callObject, $data)
	{
		if (!empty($data["LpuUnit_id"])) {
			$checkResult = $callObject->_checkOpenChildStruct('saveLpuUnit', $data);
			if (!empty($checkResult) && strlen($checkResult) > 0) {
				return [['Error_Msg' => $checkResult]];
			}
			$checkResult = $callObject->_checkLpuUnitType($data);
			if (is_array($checkResult) && (count($checkResult) > 0) && $checkResult[0]["StaffCount"] > 0 && $checkResult[0]["LpuUnitType_id"] != $data["LpuUnitType_id"]) {
				return [['Error_Code' => 100011, 'Error_Msg' => 'Изменение типа группы отделений невозможно, для некоторых отделений существуют строки штатного расписания.']];
			}
			// Проверяем меняется ли тип группы отделений.
			$getLpuUnitList = $callObject->getLpuUnitList(["LpuUnit_id" => $data["LpuUnit_id"]]);
			$oldLpuUnitType = (!empty($getLpuUnitList[0]["LpuUnitType_id"])) ? $getLpuUnitList[0]["LpuUnitType_id"] : "";
			$newLpuUnitType = (!empty($data["LpuUnitType_id"])) ? $data["LpuUnitType_id"] : "";
			if ($newLpuUnitType != $oldLpuUnitType) {
				$callObject->load->database("registry", true);
				$callObject->load->model("Registry_model", "Reg_model");
				$checkResult = $callObject->Reg_model->checkLpuSectionInRegistry($data);
				if (!empty($checkResult)) {
					throw new Exception($checkResult);
				}
			}
		}
		if (!empty($data["FRMOUnit_id"]) && !empty($data["LpuUnit_id"])) {
			$sql = "
				select ls.LpuSection_id as \"LpuSection_id\"
				from
					v_LpuSection ls 
					inner join nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					inner join nsi.v_FRMOUnit fu on fu.FRMOUnit_OID = fs.FRMOUnit_OID
				where ls.LpuUnit_id = :LpuUnit_id
				  and fu.FRMOUnit_id <> :FRMOUnit_id
			";
			$sqlParams = [
				"LpuUnit_id" => $data["LpuUnit_id"],
				"FRMOUnit_id" => $data["FRMOUnit_id"]
			];
			$resp = $callObject->queryResult($sql, $sqlParams);
			if (!empty($resp[0]["LpuSection_id"])) {
				if (empty($data["ignoreFRMOUnitCheck"])) {
					return [[
						"Error_Msg" => "YesNo",
						"Error_Code" => "101",
						"Alert_Msg" => "Найдены отделения из другого структурного подразделения по справочнику ФРМО. После сохранения, связанные данные ФРМО для отделений будут удалены. Продолжить?"
					]];
				} else {
					foreach ($resp as $respone) {
						$sql = "
							update LpuSection
							set FRMOSection_id = null
							where LpuSection_id = :LpuSection_id;
						";
						$sqlParams = [
							"LpuSection_id" => $respone["LpuSection_id"]
						];
						$resp_upd = $callObject->queryResult($sql, $sqlParams);
						if (!empty($resp_upd[0]["Error_Msg"])) {
							throw new Exception($resp_upd[0]["Error_Msg"]);
						}
					}
				}
			}
		}
		if (!empty($data["LpuUnit_id"])) {
			$proc = "p_LpuUnit_upd";
			if (!empty($data["source"]) && $data["source"] == "API") {
				$data["LpuBuilding_id"] = $callObject->getFirstResultFromQuery("select LpuBuilding_id from v_LpuUnit where LpuUnit_id = :LpuUnit_id limit 1", $data, true);
				if ($data["LpuBuilding_id"] === false || empty($data["LpuBuilding_id"])) {
					throw new Exception("Не удалось определить идентификатор подразделения");
				}
			}
		} else {
			$proc = "p_LpuUnit_ins";
		}
		$params = [
			"LpuUnit_id" => (!empty($data["LpuUnit_id"]) ? $data["LpuUnit_id"] : null),
			"LpuUnit_begDate" => $data["LpuUnit_begDate"],
			"LpuUnit_endDate" => $data["LpuUnit_endDate"],
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"LpuUnitType_id" => $data["LpuUnitType_id"],
			"LpuUnitTypeDop_id" => $data["LpuUnitTypeDop_id"],
			"LpuUnit_Code" => $data["LpuUnit_Code"],
			"LpuUnit_Name" => $data["LpuUnit_Name"],
			"LpuUnit_Phone" => $data["LpuUnit_Phone"],
			"LpuUnit_Descr" => $data["LpuUnit_Descr"],
			"LpuUnit_Email" => $data["LpuUnit_Email"],
			"LpuUnit_IP" => $data["LpuUnit_IP"],
			"LpuUnit_IsEnabled" => $data["LpuUnit_IsEnabled"],
			"LpuUnit_isPallCC" => ($data["LpuUnit_isPallCC"] == "on" || $data["LpuUnit_isPallCC"] == "2" ? 2 : 1),
			"LpuUnitProfile_fid" => $data["LpuUnitProfile_fid"],
			"LpuUnit_isStandalone" => $data["LpuUnit_isStandalone"],
			"LpuUnit_isCMP" => $data["LpuUnit_isCMP"],
			"LpuUnit_isHomeVisit" => $data["LpuUnit_isHomeVisit"],
			"UnitDepartType_fid" => $data["UnitDepartType_fid"],
			"LpuBuildingPass_id" => $data["LpuBuildingPass_id"],
			"LpuUnitSet_id" => (!empty($data["LpuUnitSet_id"]) ? $data["LpuUnitSet_id"] : null),
			"LpuUnit_IsOMS" => (!empty($data["LpuUnit_IsOMS"]) ? $data["LpuUnit_IsOMS"] : null),
			"FRMOUnit_id" => (!empty($data["FRMOUnit_id"]) ? $data["FRMOUnit_id"] : null),
			"pmUser_id" => $data["pmUser_id"],
			"Server_id" => $data["Server_id"],
		];
		if (!empty($data["source"]) && $data["source"] == "API") {
			$params["Address_id"] = $data["Address_id"];
			$params["LpuUnit_IsDirWithRec"] = $data["LpuUnit_IsDirWithRec"];
			$params["LpuUnit_ExtMedCnt"] = $data["LpuUnit_ExtMedCnt"];
			$params["LpuUnit_Guid"] = $data["LpuUnit_Guid"];
			$params["LpuUnit_FRMOUnitID"] = $data["LpuUnit_FRMOUnitID"];
			$params["LpuUnit_FRMOid"] = $data["LpuUnit_FRMOid"];
		} else {
			if (empty($data["LpuUnit_id"])) {
				if (!empty($data["LpuUnit_IsEnabled"]) &&
					(isSuperadmin() || ($data["session"]["region"]["nick"] == "kareliya" && isLpuAdmin($data["Lpu_id"])))
				) {
					$params["LpuUnit_IsEnabled"] = ($data["LpuUnit_IsEnabled"] == "on" || $data["LpuUnit_IsEnabled"] == "2") ? 2 : 1;
				} else {
					$params["LpuUnit_IsEnabled"] = 1;
				}
			} else {
				if (!empty($data["LpuUnit_IsEnabled"]) &&
					(isSuperadmin() || ($data["session"]["region"]["nick"] == "kareliya" && isLpuAdmin($data["Lpu_id"])))
				) {
					$params["LpuUnit_IsEnabled"] = ($data["LpuUnit_IsEnabled"] == "on" || $data["LpuUnit_IsEnabled"] == "2") ? 2 : 1;
				} else {
					$LpuUnit_IsEnabled = $callObject->getFirstResultFromQuery("SELECT LpuUnit_IsEnabled FROM v_LpuUnit  WHERE LpuUnit_id = :LpuUnit_id limit 1", $data, true);
					if ($LpuUnit_IsEnabled === false) {
						throw new Exception("Ошибка при определении признака записи на прием");
					}
					$params["LpuUnit_IsEnabled"] = $LpuUnit_IsEnabled;
				}
			}
			// Доп/справочник может быть добавлен / и кстати - почему Like
			if (!empty($data["DopNew"])) {
				//$dop_new = toAnsi($data["DopNew"]);
				$dop_new = $data["DopNew"];
				$sql = "
					select LpuUnitTypeDop_id as \"LpuUnitTypeDop_id\"
					from v_LpuUnitTypeDop 
					where LpuUnitTypeDop_Name = :dop_new
					  and Server_id=:Server_id
				";
				$sqlParams = [
					"dop_new" => $dop_new,
					"Server_id" => $data["Server_id"]
				];
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$sql = "
						select
							LpuUnitTypeDop_id as \"LpuUnitTypeDop_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_lpuunittypedop_ins(
						    server_id := :Server_id,
						    lpuunittypedop_name := :LpuUnitTypeDop_Name,
						    pmuser_id := :pmUser_id
						);
					";
					$sqlParams = [
						"LpuUnitTypeDop_Name" => $dop_new,
						"Server_id" => $data["Server_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$result = $callObject->db->query($sql, $sqlParams);
					if (is_object($result)) {
						$sel = $result->result("array");
						if ($sel[0]["LpuUnitTypeDop_id"] > 0) {
							$params["LpuUnitTypeDop_id"] = $sel[0]["LpuUnitTypeDop_id"];
						}
					}
				}
			}
			if (!empty($data["LpuUnit_id"])) {
				$sql = "
					select
						Address_id as \"Address_id\",
						LpuUnit_IsDirWithRec as \"LpuUnit_IsDirWithRec\",
						LpuUnit_ExtMedCnt as \"LpuUnit_ExtMedCnt\",
						LpuUnit_Guid as \"LpuUnit_Guid\",
						LpuUnit_FRMOUnitID as \"LpuUnit_FRMOUnitID\",
						LpuUnit_FRMOid as \"LpuUnit_FRMOid\"
					from v_LpuUnit 
					where LpuUnit_id = :LpuUnit_id
				";
				$dopParams = $callObject->getFirstRowFromQuery($sql, $data, true);
				if ($dopParams === false) {
					throw new Exception("Ошибка при получении доп. параметров группы отделений");
				}
				$params = array_merge($params, $dopParams);
			} else {
				$params["Address_id"] = null;
				$params["LpuUnit_IsDirWithRec"] = null;
				$params["LpuUnit_ExtMedCnt"] = null;
				$params["LpuUnit_Guid"] = null;
				$params["LpuUnit_FRMOUnitID"] = null;
				$params["LpuUnit_FRMOid"] = null;
			}
			if ($data["session"]["region"]["nick"] == "ufa") {
				$params["LpuUnit_IsOMS"] = $data["LpuUnit_IsOMS"] + 1;
			}
		}
		$selectString = "
			LpuUnit_id as \"LpuUnit_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpuunit_id := :LpuUnit_id,
			    lpubuilding_id := :LpuBuilding_id,
			    lpuunittype_id := :LpuUnitType_id,
			    lpuunittypedop_id := :LpuUnitTypeDop_id,
			    address_id := :Address_id,
			    lpuunit_code := :LpuUnit_Code,
			    lpuunit_name := :LpuUnit_Name,
			    lpuunit_descr := :LpuUnit_Descr,
			    lpuunit_phone := :LpuUnit_Phone,
			    lpuunit_isenabled := :LpuUnit_IsEnabled,
			    lpuunit_isdirwithrec := :LpuUnit_IsDirWithRec,
			    lpuunit_extmedcnt := :LpuUnit_ExtMedCnt,
			    lpuunit_email := :LpuUnit_Email,
			    lpuunit_ip := :LpuUnit_IP,
			    lpuunitset_id := :LpuUnitSet_id,
			    lpuunit_guid := :LpuUnit_Guid,
			    lpuunit_begdate := :LpuUnit_begDate,
			    lpuunit_enddate := :LpuUnit_endDate,
			    lpuunit_isoms := :LpuUnit_IsOMS,
			    unitdeparttype_fid := :UnitDepartType_fid,
			    lpuunitprofile_fid := :LpuUnitProfile_fid,
			    lpuunit_isstandalone := :LpuUnit_isStandalone,
			    lpubuildingpass_id := :LpuBuildingPass_id,
			    lpuunit_ishomevisit := :LpuUnit_isHomeVisit,
			    lpuunit_iscmp := :LpuUnit_isCMP,
			    lpuunit_frmounitid := :LpuUnit_FRMOUnitID,
			    lpuunit_frmoid := :LpuUnit_FRMOid,
			    lpuunit_ispallcc := :LpuUnit_isPallCC,
			    frmounit_id := :FRMOUnit_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		// Удаляем данные из кэша
		$callObject->load->library("swCache");
		$callObject->swcache->clear("LpuUnitList_" . $data["Lpu_id"]);
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public static function saveLpuBuilding(LpuStructure_model $callObject, $data)
	{
		if (!empty($data["LpuBuilding_id"])) {
			$checkResult = $callObject->_checkOpenChildStruct("saveLpuBuilding", $data);
			if (!empty($checkResult)) {
				$callObject->rollbackTransaction();
				throw new Exception($checkResult);
			}
		}
		$Address_id = null;
		$PAddress_id = null;
		$data["LpuLevel_id"] = null;
		$data["LpuLevel_cid"] = null;

		// Проверка уникальности кода среди подразделений, выгружаемых в ПМУ, в рамках одной МО
		// @task https://redmine.swan.perm.ru/issues/66399
		// Для Астрахани проверку отключаем
		// @task https://redmine.swan.perm.ru/issues/67816
		if (!in_array($callObject->getRegionNick(), ['astra']) && !empty($data['LpuBuilding_Code']) && $data['LpuBuilding_IsExport'] == 2 && empty($data['LpuBuilding_endDate'])) {
			$query = "
				select LpuBuilding_id as \"LpuBuilding_id\"
				from v_LpuBuilding 
				where Lpu_id = :Lpu_id
				  and LpuBuilding_id != :LpuBuilding_id
				  and LpuBuilding_Code = :LpuBuilding_Code
				  and LpuBuilding_endDate is null
				  and LpuBuilding_IsExport = 2
				limit 1
			";
			$queryParams = [
				"Lpu_id" => $data["Lpu_id"],
				"LpuBuilding_id" => $data["LpuBuilding_id"],
				"LpuBuilding_Code" => $data["LpuBuilding_Code"],
				"LpuBuilding_endDate" => $data["LpuBuilding_endDate"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$response = $result->result("array");
			if (is_array($response) && count($response) > 0 && !empty($response[0]["LpuBuilding_id"])) {
				throw new Exception("Код подразделения должен быть уникальным в рамках МО. Указанный код уже используется. Измените введенное значение в поле \"Код\".");
			}
		}
		if (!empty($data["fromAPI"]) || in_array($callObject->regionNick, ["krym"])) {
			$query = "
				select LpuBuilding_id as \"LpuBuilding_id\"
				from v_LpuBuilding 
				where Lpu_id = :Lpu_id
				  and LpuBuilding_id != coalesce(:LpuBuilding_id, 0)
				  and LpuBuilding_Code = :LpuBuilding_Code
				limit 1
			";
			$queryParams = [
				"Lpu_id" => $data["Lpu_id"],
				"LpuBuilding_id" => $data["LpuBuilding_id"],
				"LpuBuilding_Code" => $data["LpuBuilding_Code"]
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$response = $result->result("array");
			if (is_array($response) && count($response) > 0 && !empty($response[0]["LpuBuilding_id"])) {
				throw new Exception("Код подразделения должен быть уникальным в рамках МО. Указанный код уже используется. Измените введенное значение в поле \"Код\".");
			}
		}
		if (!isset($data["LpuBuilding_id"])) {
			$proc = "p_LpuBuilding_ins";
		} else {
			$proc = "p_LpuBuilding_upd";
			$query = "
				select 
					Address_id as \"Address_id\", 
					PAddress_id as \"PAddress_id\", 
					LpuLevel_id as \"LpuLevel_id\", 
					LpuLevel_cid as \"LpuLevel_cid\"
				from v_LpuBuilding
				where LpuBuilding_id = :LpuBuilding_id
			";
			$queryParams = [
				"LpuBuilding_id" => $data["LpuBuilding_id"]
			];
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$sel = $result->result("array");
				if (is_array($sel) && count($sel) > 0) {
					$Address_id = $sel[0]["Address_id"];
					$PAddress_id = $sel[0]["PAddress_id"];
					$data["LpuLevel_id"] = $sel[0]["LpuLevel_id"];
					$data["LpuLevel_cid"] = $sel[0]["LpuLevel_cid"];
				}
			}
		}
		if (empty($data["fromAPI"])) {
			$data["Address_id"] = $Address_id;
			$data["PAddress_id"] = $PAddress_id;
			// Сохранение адреса
			// возможные варианты:
			// 1. Удаление адреса   - если Address_id not null and другие поля пустые
			// 2. Добавление адреса - если Address_id null and другие поля заполнены
			// 3. Апдейт адреса - если Address_id not null and другие поля заполнены

			// создаем или редактируем адрес
			// Если строка адреса пустая
			if (!isset($data["Address_Address"])) {
				$Address_id = NULL;
			} else {
				// не было адреса
				$queryParams = [
					"Server_id" => $data["Server_id"],
					"KLCountry_id" => $data["KLCountry_id"],
					"KLRGN_id" => $data["KLRGN_id"],
					"KLSubRGN_id" => $data["KLSubRGN_id"],
					"KLCity_id" => $data["KLCity_id"],
					"KLTown_id" => $data["KLTown_id"],
					"KLStreet_id" => $data["KLStreet_id"],
					"Address_Zip" => $data["Address_Zip"],
					"Address_House" => $data["Address_House"],
					"Address_Corpus" => $data["Address_Corpus"],
					"Address_Flat" => $data["Address_Flat"],
					"Address_Address" => $data["Address_Address"],
					"pmUser_id" => $data["pmUser_id"],
				];
				if ((isset($data["Address_Address"])) && (!isset($Address_id))) {
					$procedure = "p_address_ins";
					$queryParams["Address_id"] = null;
				} else {
					$procedure = "p_address_upd";
					$queryParams["Address_id"] = $data["Address_id"];
				}
				$selectString = "
					Address_id as \"Address_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				";
				$query = "
					select {$selectString}
					from {$procedure}(
						Address_id := :Address_id,
						server_id := :Server_id,
						klareatype_id := Null,
						klcountry_id := :KLCountry_id,
						klrgn_id := :KLRGN_id,
						klsubrgn_id := :KLSubRGN_id,
						klcity_id := :KLCity_id,
						kltown_id := :KLTown_id,
						klstreet_id := :KLStreet_id,
						address_zip := :Address_Zip,
						address_house := :Address_House,
						address_corpus := :Address_Corpus,
						address_flat := :Address_Flat,
						address_address := :Address_Address,
						pmuser_id := :pmUser_id
					);
				";
				/**@var CI_DB_result $res */
				$res = $callObject->db->query($query, $queryParams);
				if ($procedure == "p_address_ins") {
					if (!is_object($res)) {
						return false;
					}
					$sel = $res->result("array");
					if (!empty($sel[0]["Error_Msg"])) {
						return $sel;
					}
					$Address_id = $sel[0]["Address_id"];
				}
			}
			// Фактический адрес
			// Если строка адреса пустая
			if (!isset($data["PAddress_Address"])) {
				$PAddress_id = NULL;
			} else {
				// не было адреса
				$queryParams = [
					"Server_id" => $data["Server_id"],
					"KLCountry_id" => $data["PKLCountry_id"],
					"KLRGN_id" => $data["PKLRGN_id"],
					"KLSubRGN_id" => $data["PKLSubRGN_id"],
					"KLCity_id" => $data["PKLCity_id"],
					"KLTown_id" => $data["PKLTown_id"],
					"KLStreet_id" => $data["PKLStreet_id"],
					"Address_Zip" => $data["PAddress_Zip"],
					"Address_House" => $data["PAddress_House"],
					"Address_Corpus" => $data["PAddress_Corpus"],
					"Address_Flat" => $data["PAddress_Flat"],
					"Address_Address" => $data["PAddress_Address"],
					"pmUser_id" => $data["pmUser_id"]
				];
				if ((isset($data['PAddress_Address'])) && (!isset($PAddress_id))) {
					$procedure = "p_address_ins";
					$queryParams["Address_id"] = null;
				} else {
					$procedure = "p_address_upd";
					$queryParams["Address_id"] = $data['PAddress_id'];
				}
				$selectString = "
					Address_id as \"Address_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				";
				$query = "
					select {$selectString}
					from {$procedure}(
						Address_id := :Address_id,
						server_id := :Server_id,
						klareatype_id := Null,
						klcountry_id := :KLCountry_id,
						klrgn_id := :KLRGN_id,
						klsubrgn_id := :KLSubRGN_id,
						klcity_id := :KLCity_id,
						kltown_id := :KLTown_id,
						klstreet_id := :KLStreet_id,
						address_zip := :Address_Zip,
						address_house := :Address_House,
						address_corpus := :Address_Corpus,
						address_flat := :Address_Flat,
						address_address := :Address_Address,
						pmuser_id := :pmUser_id
					);
				";
				$res = $callObject->db->query($query, $queryParams);
				if ((isset($data['PAddress_Address'])) && (!isset($PAddress_id))) {
					if (!is_object($res)) {
						return false;
					}
					$sel = $res->result('array');
					if (!empty($sel[0]['Error_Msg'])) {
						return $sel;
					}
					$PAddress_id = $sel[0]['Address_id'];
				}
			}
		} else {
			$Address_id = (!empty($data['Address_id']) ? $data['Address_id'] : null);
			$PAddress_id = (!empty($data['PAddress_id']) ? $data['PAddress_id'] : null);
		}
		$selectString = "
			LpuBuilding_id as \"LpuBuilding_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
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
			    lpubuilding_isprint := :LpuBuilding_IsPrint,
			    lpufilial_id := :LpuFilial_id,
			    lpubuilding_isaidscenter := :LpuBuilding_IsAIDSCenter,
			    pmuser_id := :pmUser_id
			);
		";
		$params = [
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuBuildingType_id" => $data["LpuBuildingType_id"],
			"LpuBuilding_Code" => $data["LpuBuilding_Code"],
			"LpuBuilding_begDate" => $data["LpuBuilding_begDate"],
			"LpuBuilding_endDate" => $data["LpuBuilding_endDate"],
			"LpuBuilding_Nick" => $data["LpuBuilding_Nick"],
			"LpuBuilding_Name" => $data["LpuBuilding_Name"],
			"LpuBuilding_WorkTime" => $data["LpuBuilding_WorkTime"],
			"LpuBuilding_RoutePlan" => $data["LpuBuilding_RoutePlan"],
			"Address_id" => $Address_id,
			"PAddress_id" => $PAddress_id,
			"LpuLevel_id" => $data["LpuLevel_id"],
			"LpuLevel_cid" => $data["LpuLevel_cid"],
			"Server_id" => $data["Server_id"],
			"LpuBuilding_IsExport" => $data["LpuBuilding_IsExport"],
			"LpuBuilding_CmpStationCode" => $data["LpuBuilding_CmpStationCode"],
			"LpuBuilding_CmpSubstationCode" => $data["LpuBuilding_CmpSubstationCode"],
			"pmUser_id" => $data["pmUser_id"],
			"LpuBuilding_Latitude" => $data["LpuBuilding_Latitude"],
			"LpuBuilding_Longitude" => $data["LpuBuilding_Longitude"],
			"LpuBuilding_IsPrint" => (empty($data["LpuBuilding_id"]) && $callObject->getRegionNick() == "krym") ? 1 : null,
			"LpuFilial_id" => $data["LpuFilial_id"],
			"LpuBuilding_IsAIDSCenter" => $data["LpuBuilding_IsAIDSCenter"]
		];
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		// Блок кабинета здоровья (данные в отдельной таблице LpuBuildingHealth )
		if (is_object($result->first_row())) {
			$LpuBuilding_id = $result->first_row()->LpuBuilding_id;
			//узнаем, что имеется на это подразделение в LpuBuildingHealth :
			$query = "
				SELECT LpuBuildingHealth_id
				FROM LpuBuildingHealth
				WHERE LpuBuilding_id = :LpuBuilding_id
			";
			$health_id = $callObject->getFirstResultFromQuery($query, ["LpuBuilding_id" => $LpuBuilding_id]);
			$query = "";
			$params = [
				"LpuBuilding_id" => $LpuBuilding_id,
				"LpuBuildingHealth_id" => $health_id ? $health_id : 0,
				"pmUser_id" => $data["pmUser_id"]
			];
			if (!empty($data["LpuBuildingHealth_Phone"]) or !empty($data["LpuBuildingHealth_Email"])) {
				$params["phone"] = $data["LpuBuildingHealth_Phone"] ? $data["LpuBuildingHealth_Phone"] : "";
				$params["email"] = $data["LpuBuildingHealth_Email"] ? $data["LpuBuildingHealth_Email"] : "";
				$proc = $health_id ? "p_LpuBuildingHealth_upd" : "p_LpuBuildingHealth_ins";
				$selectString = "
					LpuBuildingHealth_id as \"LpuBuildingHealth_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				";
				$query = "
					select {$selectString}
					from {$proc}(
					    lpubuildinghealth_id := :LpuBuildingHealth_id,
					    lpubuilding_id := :LpuBuilding_id,
					    lpubuildinghealth_phone := :phone,
					    lpubuildinghealth_email := :email,
					    pmuser_id := :pmUser_id
					);
				";
			} else {
				//все поля блока пусты,
				if ($health_id) {
					//и есть запись в таблице => удалить
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Message\"
						from p_lpubuildinghealth_del(lpubuildinghealth_id := :LpuBuildingHealth_id);
					";
				}
			}
			if ($query) {
				$callObject->db->query($query, $params);
			}
		}
		// Если вставка успешно завершилась, прибиваем кэш, если он конечно есть
		$callObject->load->library("swCache");
		$callObject->swcache->clear("LpuBuildingList_" . $data["Lpu_id"]);
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
 	public static function saveLpuSection(LpuStructure_model $callObject, $data)
	{
		if (empty($data['Lpu_id'])) {
			throw new Exception("Не указана МО");
		}
		$callObject->beginTransaction();
		if (!isSuperadmin() && !empty($data["LpuBuilding_id"])) {
			// МО подразделения должна совпадать с текущей МО пользователя
			$Lpu_id = $callObject->getFirstResultFromQuery("select Lpu_id from v_LpuBuilding where LpuBuilding_id = :LpuBuilding_id limit 1", $data, true);
			if ($Lpu_id === false || empty($Lpu_id)) {
				$callObject->rollbackTransaction();
				throw new Exception("Ошибка при получении данных по подразделению");
			} else if ($Lpu_id != $data['Lpu_id']) {
				$callObject->rollbackTransaction();
				throw new Exception("МО подразделения не соответствует Вашей текущей МО. Сохранение запрещено.");
			}
		}
		if (!empty($data['source']) && $data['source'] == 'API') {
			// Проверяем связку Lpu_id + LpuSectionOuter_id
			if (!empty($data['LpuSectionOuter_id'])) {
				$checkResult = $callObject->getFirstResultFromQuery("
					select LpuSectionOuter_id
					from v_LpuSection
					where LpuSectionOuter_id = :LpuSectionOuter_id
					  and Lpu_id = :Lpu_id
						" . (!empty($data['LpuSection_id']) ? "and LpuSection_id != :LpuSection_id" : "") . "
					limit 1
				", $data);
				if ($checkResult !== false && !empty($checkResult)) {
					$callObject->rollbackTransaction();
					throw new Exception("Дубль по идентификатору отделения МО в сторонней МИС");
				}
			}
			$data["LpuSectionProfile_id"] = $callObject->getLpuSectionProfileId($data["LpuSectionProfile_Code"]);
			if (empty($data["LpuSectionProfile_id"])) {
				$callObject->rollbackTransaction();
				throw new Exception("Ошибка при получении идентификатора профиля отделения");
			}
			// Проверяем соответствие LpuUnit_id и Lpu_id
			$Lpu_id = $callObject->getFirstResultFromQuery("select Lpu_id from v_LpuUnit where LpuUnit_id = :LpuUnit_id limit 1", $data, true);
			if ($Lpu_id === false || empty($Lpu_id)) {
				$callObject->rollbackTransaction();
				throw new Exception("Ошибка при получении данных по группе отделений");
			} else if ($Lpu_id != $data["Lpu_id"]) {
				$callObject->rollbackTransaction();
				throw new Exception("МО не соответствует группе отделений.");
			}
			// Проверяем наличие записи в persis.FRMPSubdivision
			$FRMPSubdivision_id = $callObject->getFirstResultFromQuery("select id from persis.FRMPSubdivision where id = :FRMPSubdivision_id limit 1", $data, true);
			if ($FRMPSubdivision_id === false || empty($FRMPSubdivision_id)) {
				$callObject->rollbackTransaction();
				throw new Exception("Ошибка при проверке значения FRMPSubdivision_id");
			}
		} else if (!empty($data["LpuSection_id"])) {
			// Тянем значение LpuSectionOuter_id из БД для случаев сохранения отделения не через API
			$dopParams = $callObject->getFirstRowFromQuery("
				select
					LpuSectionOuter_id as \"LpuSectionOuter_id\",
					LpuBuildingPass_id as \"LpuBuildingPass_id\"
				from v_LpuSection 
				where LpuSection_id = :LpuSection_id
			", $data, true);
			if ($dopParams === false) {
				throw new Exception("Ошибка при получении доп. параметров отделения");
			}
			$data = array_merge($data, $dopParams);
		}
		if (!empty($data["LpuSection_id"])) {
			$checkResult = $callObject->_checkOpenChildStruct("saveLpuSection", $data);
			if (!empty($checkResult)) {
				$callObject->rollbackTransaction();
				throw new Exception($checkResult);
			}
			// Проверяем штатное расписание, если при сохранении у отделения проставлена дата закрытия (задача http://redmine.swan.perm.ru/issues/17622)
			if (!empty($data["LpuSection_disDate"])) {
				$checkResult = $callObject->checkStaff($data);
				if (!empty($checkResult)) {
					$callObject->rollbackTransaction();
					throw new Exception($checkResult);
				}
			}
			// проверяем меняется ли профиль отделения.
			if ($data["session"]["region"]["nick"] != "ufa" || !isSuperadmin()) {
				$getLpuSectionList = $callObject->getLpuSectionList(["LpuSection_id" => $data["LpuSection_id"]]);
				$oldLpuSectionProfile = (!empty($getLpuSectionList[0]["LpuSectionProfile_id"])) ? $getLpuSectionList[0]["LpuSectionProfile_id"] : "";
				$newLpuSectionProfile = (!empty($data["LpuSectionProfile_id"])) ? $data["LpuSectionProfile_id"] : "";
				// Разобраться с подключением к реестровой БД
				if ($newLpuSectionProfile != $oldLpuSectionProfile) {
					$callObject->load->database("registry", true);
					$callObject->load->model("Registry_model", "Reg_model");
					$checkResult = $callObject->Reg_model->checkLpuSectionInRegistry($data);
					if (!empty($checkResult)) {
						$callObject->rollbackTransaction();
						throw new Exception($checkResult);
					}
				}
			}
		}
		if ($data["session"]["region"]["nick"] != "ufa") {
			//Доработки по #17064 не распространяются на Уфу, поскольку у сисадминов и статистов Уфы  - замешательство (#18313)
			//#17064 Указание уровня МЭС при добавлении поликлинических отделений
			if (empty($data["MESLevel_id"])) {
				$LpuUnitType_id = $callObject->getFirstResultFromQuery("SELECT LpuUnitType_id FROM v_LpuUnit t WHERE t.LpuUnit_id = :LpuUnit_id", ["LpuUnit_id" => $data["LpuUnit_id"]]);
				if (!empty($LpuUnitType_id)) {
					if ($LpuUnitType_id == 2) {
						$data["MESLevel_id"] = $callObject->getFirstResultFromQuery("SELECT MesLevel_id FROM v_MESLevel WHERE MesLevel_Code = :MesLevel_Code", ["MesLevel_Code" => 1]);
						if (empty($data["MESLevel_id"])) {
							$data["MESLevel_id"] = null;
						}
					}
				}
			}
		}
		$sqlquery = "
			SELECT 
				L.Lpu_IsLab as \"Lpu_IsLab\", 
				L.Lpu_id as \"Lpu_id\"
			FROM
				v_LpuUnit LU
				LEFT JOIN v_Lpu L on L.Lpu_id = LU.Lpu_id
			WHERE LU.LpuUnit_id = :LpuUnit_id
			limit 1
		";
		$UnitLpuInfo = $callObject->db->query($sqlquery, ["LpuUnit_id" => $data["LpuUnit_id"]])->result("array");
		if (!is_array($UnitLpuInfo) || count($UnitLpuInfo) == 0) {
			$callObject->rollbackTransaction();
			return false;
		}

		if ( !empty($UnitLpuInfo[0]["UnitDepartType_fid"]) && $UnitLpuInfo[0]["UnitDepartType_fid"] == 2 && !empty($data['FRMOSection_id']) ) {
			$checkResult = $callObject->getFirstResultFromQuery("
				select
					ls.LpuSection_id
				from v_LpuSection ls
				where ls.FRMOSection_id = :FRMOSection_id
					and ls.LpuSection_id != coalesce(:LpuSection_id, 0)
				limit 1
			", array(
				'FRMOSection_id' => $data['FRMOSection_id'],
				'LpuSection_id' => !empty($data['LpuSection_id']) ? $data['LpuSection_id'] : null,
			));

			if ( $checkResult !== false && empty($checkResult) ) {
				$callObject->rollbackTransaction();
				return [['Error_Msg' => 'ОИД ФРМО отделения/кабинета не уникален. Уникальность этого значения требуется  для передачи на ФРМО данных об отделениях стационаров. Исправьте ОИД ФРМО отделения.']];
			}
		}

		$data["Lpu_IsLab"] = $UnitLpuInfo[0]["Lpu_IsLab"];
		$data["UnitLpuInfo_id"] = $UnitLpuInfo[0]["Lpu_id"];
		$sqlquery = "
			SELECT
				LpuPeriodOMS_begDate as \"LpuPeriodOMS_begDate\",
				LpuPeriodOMS_endDate as \"LpuPeriodOMS_endDate\"
			FROM LpuPeriodOMS 
			WHERE Lpu_id = :Lpu_Id
			  and LpuPeriodOMS_pid is null
		";
		$queryParams = ["Lpu_Id" => $data["UnitLpuInfo_id"]];
		$datesOMS = $callObject->db->query($sqlquery, $queryParams)->result("array");
		$data["activeOMS"] = false;
		$today = new DateTime(date("d.m.Y"));
		if ($data["Lpu_IsLab"] == 2) {
			foreach ($datesOMS as $dates) {
				if ($dates["LpuPeriodOMS_begDate"] <= $today) {
					if (($dates["LpuPeriodOMS_endDate"] >= $today) || $dates["LpuPeriodOMS_endDate"] == null) {
						$data["activeOMS"] = true;
					}
				}
			}
		}
		if (!empty($data["LpuSectionCode_id"])) {
			$data["LpuSection_Code"] = $callObject->getFirstResultFromQuery("select LpuSectionCode_Code from v_LpuSectionCode where LpuSectionCode_id = :LpuSectionCode_id limit 1", $data, true);

		}
		$params = [
			"FRMPSubdivision_id" => $data["FRMPSubdivision_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"Server_id" => $data["Server_id"],
			"LpuUnit_id" => $data["LpuUnit_id"],
			"LpuSection_pid" => $data["LpuSection_pid"],
			"LpuSectionProfile_id" => $data["LpuSectionProfile_id"],
			"LpuSection_Code" => $data["LpuSection_Code"],
			"LpuSectionCode_id" => $data["LpuSectionCode_id"],
			"LpuSection_Name" => $data["LpuSection_Name"],
			"LpuSection_setDate" => $data["LpuSection_setDate"],
			"LpuSection_disDate" => $data["LpuSection_disDate"],
			"LpuSectionAge_id" => $data["LpuSectionAge_id"],
			"LpuSectionBedProfile_id" => $data["LpuSectionBedProfile_id"],
			"MESLevel_id" => $data["MESLevel_id"],
			"LpuSection_PlanVisitShift" => $data["LpuSection_PlanVisitShift"],
			"LpuSection_PlanTrip" => $data["LpuSection_PlanTrip"],
			"LpuSection_PlanVisitDay" => $data["LpuSection_PlanVisitDay"],
			"LpuSection_PlanAutopShift" => $data["LpuSection_PlanAutopShift"],
			"LpuSection_PlanResShift" => $data["LpuSection_PlanResShift"],
			"LpuSection_KolJob" => $data["LpuSection_KolJob"],
			"LpuSection_KolAmbul" => $data["LpuSection_KolAmbul"],
			"LpuSection_Descr" => $data["LpuSection_Descr"],
			"LpuSection_Contacts" => $data["LpuSection_Contacts"],
			"LpuSectionHospType_id" => $data["LpuSectionHospType_id"],
			"LpuSection_IsCons" => $data["LpuSection_IsCons"],
			"LpuSection_IsExportLpuRegion" => $data["LpuSection_IsExportLpuRegion"],
			"LpuSection_IsHTMedicalCare" => $data["LpuSection_IsHTMedicalCare"],
			"LpuSection_IsNoKSG" => $data["LpuSection_IsNoKSG"],
			"LevelType_id" => $data["LevelType_id"],
			"LpuSectionType_id" => $data["LpuSectionType_id"],
			"LpuSection_Area" => $data["LpuSection_Area"],
			"LpuSection_CountShift" => $data["LpuSection_CountShift"],
			"LpuSectionDopType_id" => $data["LpuSectionDopType_id"],
			"LpuCostType_id" => $data["LpuCostType_id"],
			"LpuSectionOuter_id" => (!empty($data["LpuSectionOuter_id"]) ? $data["LpuSectionOuter_id"] : null),
			"LpuBuildingPass_id" => (!empty($data["LpuBuildingPass_id"]) ? $data["LpuBuildingPass_id"] : null),
			"PalliativeType_id" => (!empty($data["PalliativeType_id"]) ? $data["PalliativeType_id"] : null),
			"FRMOUnit_id" => (!empty($data["FRMOUnit_id"]) ? $data["FRMOUnit_id"] : null),
			"FRMOSection_id" => (!empty($data["FRMOSection_id"]) ? $data["FRMOSection_id"] : null),
			"LpuSection_FRMOBuildingOid" => (!empty($data['LpuSection_FRMOBuildingOid']) ? $data['LpuSection_FRMOBuildingOid'] : null),
			"LpuSection_IsNotFRMO" => (!empty($data['LpuSection_IsNotFRMO']) ? $data['LpuSection_IsNotFRMO'] : null),
            "pmUser_id" => $data["pmUser_id"]
		];
		$proc = (empty($data["LpuSection_id"])) ? "p_LpuSection_ins" : "p_LpuSection_upd";
		if (!empty($data["LpuSection_pid"])) {
			// Подотделения и отделения
			$params["LpuUnit_id"] = null;
		}
		// On в YesNo
		if (!empty($data["LpuSection_F14"])) {
			$params["LpuSection_IsF14"] = ($data["LpuSection_F14"] == "on" || $data["LpuSection_F14"] == "2") ? 2 : 1;
		} else {
			$params["LpuSection_IsF14"] = 1;
		}
		if (!empty($data["LpuSection_IsDirRec"])) {
			$params["LpuSection_IsDirRec"] = ($data["LpuSection_IsDirRec"] == "on" || $data["LpuSection_IsDirRec"] == "2") ? 2 : 1;
		} else {
			$params["LpuSection_IsDirRec"] = 1;
		}
		if (!empty($data["LpuSection_IsQueueOnFree"])) {
			$params["LpuSection_IsQueueOnFree"] = ($data["LpuSection_IsQueueOnFree"] == "on" || $data["LpuSection_IsQueueOnFree"] == "2") ? 2 : 1;
		} else {
			$params["LpuSection_IsQueueOnFree"] = 1;
		}
		if (!empty($data["LpuSection_IsUseReg"])) {
			$params["LpuSection_IsUseReg"] = ($data["LpuSection_IsUseReg"] == "on" || $data["LpuSection_IsUseReg"] == "2") ? 2 : 1;
		} else {
			$params["LpuSection_IsUseReg"] = 1;
		}
		if (!empty($data["LpuSection_IsHTMedicalCare"])) {
			$params["LpuSection_IsHTMedicalCare"] = ($data["LpuSection_IsHTMedicalCare"] == "on" || $data["LpuSection_IsHTMedicalCare"] == "2") ? 2 : 1;
		} else {
			$params["LpuSection_IsHTMedicalCare"] = 1;
		}
		if (!empty($data["LpuSection_IsNoKSG"])) {
			$params["LpuSection_IsNoKSG"] = ($data["LpuSection_IsNoKSG"] == "on" || $data["LpuSection_IsNoKSG"] == "2") ? 2 : 1;
		} else {
			$params["LpuSection_IsNoKSG"] = 1;
		}
		$selectString = "
			LpuSection_id as \"LpuSection_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    LpuSection_id := :LpuSection_id,
				Server_id := :Server_id,
				MesAgeGroup_id := Null,
				LpuUnit_id := :LpuUnit_id,
				LpuSection_pid := :LpuSection_pid,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				LpuSection_Code := :LpuSection_Code,
				LpuSectionCode_id := :LpuSectionCode_id,
				LpuSection_Name := :LpuSection_Name,
				LpuSection_setDate := :LpuSection_setDate,
				LpuSection_disDate := :LpuSection_disDate,
				LpuSectionAge_id := :LpuSectionAge_id,
				LpuSectionBedProfile_id := :LpuSectionBedProfile_id,
				MESLevel_id := :MESLevel_id,
				LpuSection_IsF14 := :LpuSection_IsF14,
				LpuSection_Descr := :LpuSection_Descr,
				LpuSection_Contacts := :LpuSection_Contacts,
				LpuSectionHospType_id := :LpuSectionHospType_id,
				LpuSection_IsDirRec := :LpuSection_IsDirRec,
				LpuSection_IsQueueOnFree := :LpuSection_IsQueueOnFree,
				LpuSection_IsUseReg := :LpuSection_IsUseReg,
				FRMPSubdivision_id := :FRMPSubdivision_id,
				LpuSection_PlanVisitShift := :LpuSection_PlanVisitShift,
				LpuSection_PlanTrip := :LpuSection_PlanTrip,
				LpuSection_PlanVisitDay := :LpuSection_PlanVisitDay,
				LpuSection_PlanAutopShift := :LpuSection_PlanAutopShift,
				LpuSection_PlanResShift := :LpuSection_PlanResShift,
				LpuSection_KolJob := :LpuSection_KolJob,
				LpuSection_KolAmbul := :LpuSection_KolAmbul,
				LpuSection_IsCons := :LpuSection_IsCons,
				LpuSection_IsExportLpuRegion := :LpuSection_IsExportLpuRegion,
				LpuSection_IsHTMedicalCare := :LpuSection_IsHTMedicalCare,
				LpuSection_IsNoKSG := :LpuSection_IsNoKSG,
				LevelType_id := :LevelType_id,
				LpuSectionType_id := :LpuSectionType_id,
				LpuSection_Area := :LpuSection_Area,
				LpuSection_CountShift := :LpuSection_CountShift,
				LpuSectionDopType_id := :LpuSectionDopType_id,
				LpuCostType_id := :LpuCostType_id,
				LpuSectionOuter_id := :LpuSectionOuter_id,
				LpuBuildingPass_id := :LpuBuildingPass_id,
				PalliativeType_id := :PalliativeType_id,
				FRMOUnit_id := :FRMOUnit_id,
				FRMOSection_id := :FRMOSection_id,
				LpuSection_FRMOBuildingOid := :LpuSection_FRMOBuildingOid,
				LpuSection_IsNotFRMO := :LpuSection_IsNotFRMO,
				pmUser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			return false;
		}
		$mainResponse = $result->result("array");
		if (!is_array($mainResponse) || count($mainResponse) == 0) {
			$callObject->rollbackTransaction();
			return false;
		}
		$data["LpuSection_id"] = $mainResponse[0]["LpuSection_id"];
		if (!empty($data["lpuSectionProfileData"])) {
			$lpuSectionProfileData = json_decode($data["lpuSectionProfileData"], true);
			if (is_array($lpuSectionProfileData)) {
				for ($i = 0; $i < count($lpuSectionProfileData); $i++) {
					$lpuSectionProfile = [
						"pmUser_id" => $data["pmUser_id"],
						"Server_id" => $data["Server_id"],
						"LpuSection_id" => $mainResponse[0]["LpuSection_id"]
					];
					if (empty($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_id"]) || !is_numeric($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_id"])) {
						continue;
					}
					if (empty($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_begDate"])) {
						$callObject->rollbackTransaction();
						throw new Exception("Не указана дата начала действия дополнительного профиля отделения");
					} else if (CheckDateFormat($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_begDate"]) != 0) {
						$callObject->rollbackTransaction();
						throw new Exception("Неверный формат даты начала действия дополнительного профиля отделения");
					}
					if (!empty($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_endDate"]) && CheckDateFormat($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_endDate"]) != 0) {
						$callObject->rollbackTransaction();
						throw new Exception("Неверный формат даты окончания действия дополнительного профиля отделения");
					}
					if (!isset($lpuSectionProfileData[$i]["RecordStatus_Code"]) || !is_numeric($lpuSectionProfileData[$i]["RecordStatus_Code"]) || !in_array($lpuSectionProfileData[$i]["RecordStatus_Code"], [0, 2, 3])) {
						continue;
					}
					if (empty($lpuSectionProfileData[$i]["LpuSectionProfile_id"]) || !is_numeric($lpuSectionProfileData[$i]["LpuSectionProfile_id"])) {
						$callObject->rollbackTransaction();
						throw new Exception("Не указан профиль отделения в записи из списка дополнительных профилей отделения");
					}
					$lpuSectionProfile["LpuSectionLpuSectionProfile_id"] = $lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_id"];
					$lpuSectionProfile["LpuSectionProfile_id"] = $lpuSectionProfileData[$i]["LpuSectionProfile_id"];
					$lpuSectionProfile["LpuSectionLpuSectionProfile_begDate"] = ConvertDateFormat($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_begDate"]);

					if (!empty($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_endDate"])) {
						$lpuSectionProfile["LpuSectionLpuSectionProfile_endDate"] = ConvertDateFormat($lpuSectionProfileData[$i]["LpuSectionLpuSectionProfile_endDate"]);
					} else {
						$lpuSectionProfile["LpuSectionLpuSectionProfile_endDate"] = NULL;
					}
					switch ($lpuSectionProfileData[$i]["RecordStatus_Code"]) {
						case 0:
						case 2:
							$queryResponse = $callObject->saveLpuSectionLpuSectionProfile($lpuSectionProfile);
							break;
						case 3:
							$queryResponse = $callObject->deleteLpuSectionLpuSectionProfile($lpuSectionProfile);
							break;
					}
					if (!is_array($queryResponse)) {
						$callObject->rollbackTransaction();
						throw new Exception("Ошибка при " . ($lpuSectionProfileData[$i]["RecordStatus_Code"] == 3 ? "удалении" : "сохранении") . " дополнительного профиля отделения");
					} else if (!empty($queryResponse[0]["Error_Msg"])) {
						$callObject->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}
		if ( !empty($data['lpuSectionMedProductTypeLinkData']) ) {
			$lpuSectionMedProductTypeLinkData = json_decode($data['lpuSectionMedProductTypeLinkData'], true);

			if ( is_array($lpuSectionMedProductTypeLinkData) ) {
				for ( $i = 0; $i < count($lpuSectionMedProductTypeLinkData); $i++ ) {
					$lpuSectionProfile = array(
						'pmUser_id' => $data['pmUser_id']
					,'Server_id' => $data['Server_id']
					,'LpuSection_id' => $mainResponse[0]['LpuSection_id']
					);

					if ( empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_id']) || !is_numeric($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_id']) ) {
						continue;
					}

					if ( empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_begDT']) ) {
						$callObject->rollbackTransaction();
						return array(array('Error_Msg' => 'Не указана дата начала действия мед. оборудования'));
					}
					else if ( CheckDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_begDT']) != 0 ) {
						$callObject->rollbackTransaction();
						return array(array('Error_Msg' => 'Неверный формат даты начала действия мед. оборудования'));
					}

					if ( !empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']) && CheckDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']) != 0 ) {
						$callObject->rollbackTransaction();
						return array(array('Error_Msg' => 'Неверный формат даты окончания действия мед. оборудования'));
					}

					if ( !isset($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code']) || !is_numeric($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code']) || !in_array($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					if ( empty($lpuSectionMedProductTypeLinkData[$i]['MedProductType_id']) || !is_numeric($lpuSectionMedProductTypeLinkData[$i]['MedProductType_id']) ) {
						$callObject->rollbackTransaction();
						return array(array('Error_Msg' => 'Не указан тип оборудования в записи из списка мед. оборудования'));
					}

					$lpuSectionProfile['LpuSectionMedProductTypeLink_id'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_id'];
					$lpuSectionProfile['MedProductType_id'] = $lpuSectionMedProductTypeLinkData[$i]['MedProductType_id'];
					$lpuSectionProfile['LpuSectionMedProductTypeLink_TotalAmount'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_TotalAmount'] ?? 0;
					$lpuSectionProfile['LpuSectionMedProductTypeLink_IncludePatientKVI'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_IncludePatientKVI'] ?? 0;
					$lpuSectionProfile['LpuSectionMedProductTypeLink_IncludeReanimation'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_IncludeReanimation'] ?? 0;
					$lpuSectionProfile['LpuSectionMedProductTypeLink_begDT'] = ConvertDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_begDT']);

					if ( !empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']) ) {
						$lpuSectionProfile['LpuSectionMedProductTypeLink_endDT'] = ConvertDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']);
					}
					else {
						$lpuSectionProfile['LpuSectionMedProductTypeLink_endDT'] = NULL;
					}

					switch ( $lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $callObject->saveLpuSectionMedProductTypeLink($lpuSectionProfile);
							break;

						case 3:
							$queryResponse = $callObject->deleteLpuSectionMedProductTypeLink($lpuSectionProfile);
							break;
					}

					if ( !is_array($queryResponse) ) {
						$callObject->rollbackTransaction();
						return array(array('Error_Msg' => 'Ошибка при ' . ($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' мед. оборудования'));
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$callObject->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}
		if (!empty($data["LpuSectionServiceData"])) {
			$LpuSectionServiceData = json_decode($data["LpuSectionServiceData"], true);
			if (is_array($LpuSectionServiceData)) {
				foreach ($LpuSectionServiceData as $LpuSectionService) {
					$LpuSectionService["pmUser_id"] = $data["pmUser_id"];
					$LpuSectionService["LpuSection_id"] = $mainResponse[0]["LpuSection_id"];
					if (empty($LpuSectionService["LpuSection_did"])) {
						if (!empty($LpuSectionService["LpuSectionService_id"]) && $LpuSectionService["LpuSectionService_id"] > 0) {
							$LpuSectionService["RecordStatus_Code"] = 3;
						} else {
							continue;
						}
					}
					switch ($LpuSectionService["RecordStatus_Code"]) {
						case 0:
						case 2:
							$queryResponse = $callObject->saveLpuSectionService($LpuSectionService);
							break;
						case 3:
							$queryResponse = $callObject->deleteLpuSectionService($LpuSectionService);
							break;
					}
					if (isset($queryResponse) && !is_array($queryResponse)) {
						$callObject->rollbackTransaction();
						throw new Exception("Ошибка при " . ($LpuSectionService["RecordStatus_Code"] == 3 ? "удалении" : "сохранении") . " обслуживаемого отделения");
					} else if (!empty($queryResponse[0]["Error_Msg"])) {
						$callObject->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}
		if (!empty($data["LpuSectionMedicalCareKindData"])) {
			$LpuSectionMedicalCareKindData = json_decode($data["LpuSectionMedicalCareKindData"], true);
			if (is_array($LpuSectionMedicalCareKindData)) {
				for ($i = 0; $i < count($LpuSectionMedicalCareKindData); $i++) {
					$LpuSectionMedicalCareKind = [
						"pmUser_id" => $data["pmUser_id"],
						"Server_id" => $data["Server_id"],
						"LpuSection_id" => $mainResponse[0]["LpuSection_id"]
					];
					if (
						empty($LpuSectionMedicalCareKindData[$i]["LpuSectionMedicalCareKind_id"]) ||
						!is_numeric($LpuSectionMedicalCareKindData[$i]["LpuSectionMedicalCareKind_id"]) ||
						!isset($LpuSectionMedicalCareKindData[$i]["RecordStatus_Code"]) ||
						!is_numeric($LpuSectionMedicalCareKindData[$i]["RecordStatus_Code"]) ||
						!in_array($LpuSectionMedicalCareKindData[$i]["RecordStatus_Code"], [0, 1, 2, 3])
					) {
						continue;
					}
					$LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_id"] = $LpuSectionMedicalCareKindData[$i]["LpuSectionMedicalCareKind_id"];
					$LpuSectionMedicalCareKind["MedicalCareKind_id"] = $LpuSectionMedicalCareKindData[$i]["MedicalCareKind_id"];
					$LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_begDate"] = $LpuSectionMedicalCareKindData[$i]["LpuSectionMedicalCareKind_begDate"];
					$LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"] = $LpuSectionMedicalCareKindData[$i]["LpuSectionMedicalCareKind_endDate"];
					if (
						!empty($data["LpuSection_disDate"]) && (
							empty($LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"]) ||
							$LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"] > ConvertDateFormat($data["LpuSection_disDate"])
						)
					) {
						$LpuSectionMedicalCareKindData[$i] = 2;
						$LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"] = $data["LpuSection_disDate"];
					}
					if (
						!empty($LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"]) &&
						$LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_begDate"] > $LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"]
					) {
						$callObject->rollbackTransaction();
						throw new Exception("Вид МП: Дата начала периода не может быть меньше даты окончания периода");
					}
					if (
						($data["LpuSection_setDate"] > $LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_begDate"]) ||
						(!empty($data["LpuSection_disDate"]) && $data["LpuSection_disDate"] < $LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_begDate"])
					) {
						$callObject->rollbackTransaction();
						throw new Exception("Вид МП: Дата начала периода должна попадать в интервал дат работы отделения");
					}
					if (
						!empty($LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"]) &&
						($data["LpuSection_setDate"] > $LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"]) ||
						(!empty($data["LpuSection_disDate"]) && $data["LpuSection_disDate"] < $LpuSectionMedicalCareKind["LpuSectionMedicalCareKind_endDate"])
					) {
						$callObject->rollbackTransaction();
						throw new Exception("Вид МП: Дата окончания периода должна попадать в интервал дат работы отделения");
					}
					$queryResponse = array();
					switch ($LpuSectionMedicalCareKindData[$i]["RecordStatus_Code"]) {
						case 0:
						case 2:
							$queryResponse = $callObject->saveLpuSectionMedicalCareKind($LpuSectionMedicalCareKind);
							break;
						case 3:
							$queryResponse = $callObject->deleteLpuSectionMedicalCareKind($LpuSectionMedicalCareKind);
							break;
					}
					if (!is_array($queryResponse)) {
						$callObject->rollbackTransaction();
						throw new Exception("Ошибка при " . ($LpuSectionMedicalCareKindData[$i]["RecordStatus_Code"] == 3 ? "удалении" : "сохранении") . " вида оказания  МП");
					} else if (!empty($queryResponse[0]["Error_Msg"])) {
						$callObject->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}
		if (empty($data["LpuSection_pid"])) {
			if (!empty($data["AttributeSignValueData"])) {
				$callObject->load->model("Attribute_model");
				$AttributeSignValueData = json_decode($data["AttributeSignValueData"], true);
				if (is_array($AttributeSignValueData)) {
					foreach ($AttributeSignValueData as $AttributeSignValue) {
						$AttributeSignValue["pmUser_id"] = $data["pmUser_id"];
						$AttributeSignValue["AttributeSignValue_TablePKey"] = $mainResponse[0]["LpuSection_id"];
						$AttributeSignValue["AttributeSignValue_begDate"] = !empty($AttributeSignValue["AttributeSignValue_begDate"]) ? ConvertDateFormat($AttributeSignValue["AttributeSignValue_begDate"]) : null;
						$AttributeSignValue["AttributeSignValue_endDate"] = !empty($AttributeSignValue["AttributeSignValue_endDate"]) ? ConvertDateFormat($AttributeSignValue["AttributeSignValue_endDate"]) : null;
						$callObject->Attribute_model->isAllowTransaction = false;
						switch ($AttributeSignValue["RecordStatus_Code"]) {
							case 0:
							case 2:
								$AttributeSignValue_begDate = DateTime::createFromFormat("Y-m-d H:i", $AttributeSignValue["AttributeSignValue_begDate"] . " 00:00");
								$LpuSection_setDate = DateTime::createFromFormat("Y-m-d H:i", $data["LpuSection_setDate"] . " 00:00");
								if ($AttributeSignValue_begDate < $LpuSection_setDate) {
									$callObject->rollbackTransaction();
									throw new Exception("Начало действия значений атрибутов не может быть раньше даты создания отделения");
								}
								if (!empty($AttributeSignValue["AttributeSignValue_endDate"]) && !empty($data["LpuSection_disDate"])) {
									$AttributeSignValue_endDate = DateTime::createFromFormat("Y-m-d H:i", $AttributeSignValue["AttributeSignValue_endDate"] . " 00:00");
									$LpuSection_disDate = DateTime::createFromFormat("Y-m-d H:i", $data["LpuSection_disDate"] . " 00:00");
									if ($AttributeSignValue_endDate > $LpuSection_disDate) {
										$callObject->rollbackTransaction();
										throw new Exception("Окончание действия значений атрибутов не может быть позже даты закрытия отделения");
									}
								}
								$queryResponse = $callObject->Attribute_model->saveAttributeSignValue($AttributeSignValue);
								break;
							case 3:
								$queryResponse = $callObject->Attribute_model->deleteAttributeSignValue($AttributeSignValue);
								break;
						}
						$callObject->Attribute_model->isAllowTransaction = true;
						if (isset($queryResponse) && !is_array($queryResponse)) {
							$callObject->rollbackTransaction();
							throw new Exception("Ошибка при " . ($AttributeSignValue["RecordStatus_Code"] == 3 ? "удалении" : "сохранении") . " обслуживаемого отделения");
						} else if (!empty($queryResponse[0]["Error_Msg"])) {
							$callObject->rollbackTransaction();
							return $queryResponse;
						}
					}
				}
			}
			if (empty($data["ignoreLpuSectionAttributes"])) {
				$error_msg = "";
				//условие refs #85379
				if (($data["Lpu_IsLab"] != 2) || ($data["Lpu_IsLab"] == 2 && $data["activeOMS"])) {
					//Проверка сохраненных атрибутов
					$error_msg = $callObject->_checkLpuSectionAttributeSignValue([
						"LpuSection_id" => $mainResponse[0]["LpuSection_id"],
						"LpuSection_setDate" => $data["LpuSection_setDate"],
						"LpuSection_disDate" => $data["LpuSection_disDate"]
					]);
				}
				if (strlen($error_msg) > 0) {
					$callObject->rollbackTransaction();
					throw new Exception($error_msg);
				}
			}
			//Обновляем дату у всех атрибутов отделения, у которых дата закрытия больше чем дата закрытия отделения, либо дата закрытия не установлена
			if (!empty($data["LpuSection_disDate"])) {
				$LpuSection_disDate = DateTime::createFromFormat("Y-m-d H:i", $data["LpuSection_disDate"] . " 00:00");
				$lpuSectionId = $data["LpuSection_id"];
				$sqlEndDate = "
					update AttributeSignValue ASV 
					set AttributeSignValue_endDate =:endDate
					from AttributeSign AST 
					where AttributeSignValue_TablePKey =:tableKey    
					and AST.AttributeSign_TableName = 'dbo.LpuSection'   
					and (AttributeSignValue_endDate > :endDate OR AttributeSignValue_endDate is null)   
					and  AST.AttributeSign_id = ASV.AttributeSign_id
				";
				$queryParams = [
					"endDate" => $LpuSection_disDate,
					"tableKey" => $lpuSectionId
				];
				$result = $callObject->db->query($sqlEndDate, $queryParams);
				if (!$result) {
					$callObject->rollbackTransaction();
					throw new Exception("Ошибка базы данных " . $result[0]["Error_Msg"]);
				}
			}
		}
		if ($callObject->getRegionNick() == "kz") {
			$LpuSectionFPIDLink_id = $callObject->getFirstResultFromQuery(
				"select LpuSectionFPIDLink_id from r101.LpuSectionFPIDLink where LpuSection_id = :LpuSection_id",
				["LpuSection_id" => $data["LpuSection_id"]]
			);
			$proc = $LpuSectionFPIDLink_id ? "p_LpuSectionFPIDLink_upd" : "p_LpuSectionFPIDLink_ins";
			//TODO 111
			$query = "
				select
					LpuSectionFPIDLink_id as \"LpuSectionFPIDLink_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from r101.{$proc}(
					LpuSectionFPIDLink_id := :LpuSectionFPIDLink_id,
					LpuSection_id := :LpuSection_id,
					FPID := :FPID,
					pmUser_id := :pmUser_id
				);
			";
			$queryParams = [
				"LpuSectionFPIDLink_id" => $LpuSectionFPIDLink_id ? $LpuSectionFPIDLink_id : null,
				"LpuSection_id" => $data["LpuSection_id"],
				"FPID" => $data["FPID"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->queryResult($query, $queryParams);
		}
		$response = $callObject->saveOtherLpuSectionParams($data);
		if (!is_array($response) || count($response) == 0) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при сохранении дополнительных параметров отделения");
		} else if (!empty($response[0]["Error_Msg"])) {
			$callObject->rollbackTransaction();
			return $response;
		}
		$callObject->commitTransaction();
		// Удаляем данные из кэша
		$callObject->load->library("swCache");
		$callObject->swcache->clear("LpuSectionList_" . $data["Lpu_id"]);
		return $mainResponse;
	}
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public static function SaveLpuRegion(LpuStructure_model $callObject, $data)
	{
		$callObject->beginTransaction();
		if (!empty($data["LpuRegion_id"]) && !empty($data["LpuRegion_endDate"]) && empty($data["ignorePersonCardCheck"])) {
			$sql = "
				select 
					LpuAttachType_Name as \"LpuAttachType_Name\",
					to_char(Person_BirthDay::date, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
					to_char(PersonCard_begDate::date, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
					coalesce(Person_SurName, '')||' '||coalesce(Person_FirName, '')||' '||coalesce(Person_SecName, '') as \"Person_FIO\"
				from v_PersonCard 
				where LpuRegion_id = :LpuRegion_id
				  and (PersonCard_endDate is null or PersonCard_endDate > :LpuRegion_endDate)
				limit 1
			";
			$sqlParams = [
				"LpuRegion_id" => $data["LpuRegion_id"],
				"LpuRegion_endDate" => $data["LpuRegion_endDate"]
			];
			$RegionPersonCard = $callObject->getFirstRowFromQuery($sql, $sqlParams);
			if ($RegionPersonCard) {
				$callObject->rollbackTransaction();
				return [['Error_Code' => '997','Error_Msg' => 'Закрывать можно участки, не имеющие прикреплённого населения. Участок содержит открытые записи о прикреплении : '.$RegionPersonCard['Person_FIO'].', '.$RegionPersonCard['Person_BirthDay'].' г.р., тип прикрепления - '.$RegionPersonCard['LpuAttachType_Name'].' , дата прикрепления - '.$RegionPersonCard['PersonCard_begDate']]];
			}
		}
		//Проверка соответствия типа участка профилю отделения на участке
		if (isset($data["checkRegType"]) && $data["checkRegType"] == "true") {
			switch ($callObject->getRegionNick()) {
				case "perm":
					$reg_query = "
						select
							case when
								(LRT.LpuRegionType_SysNick = 'ter' and LSP.LpuSectionProfile_Code in ('1000', '1001', '1003', '1007', '1010', '1011', '97', '57')) or
								(LRT.LpuRegionType_SysNick = 'ped' and LSP.LpuSectionProfile_Code in ('0900', '0902', '0905', '0907', '1011', '68', '57')) or
								(LRT.LpuRegionType_SysNick = 'stom' and LSP.LpuSectionProfile_Code in ('1800', '1801', '1802', '1803', '1810', '1811', '1830', '85', '89', '86', '87', '171')) or
								(LRT.LpuRegionType_SysNick = 'gin' and LSP.LpuSectionProfile_Code in ('2500', '2509', '2510', '2514', '2517', '2518', '2519', '3', '136')) or
								(LRT.LpuRegionType_SysNick = 'vop' and LSP.LpuSectionProfile_Code in ('1000', '1001', '1003', '1007', '1010', '1011', '0900', '0902', '0905', '0907', '97', '68', '57') or
								 LRT.LpuRegionType_SysNick not in ('ter', 'ped', 'stom', 'gin', 'vop'))
								then 1 else 2
							end as \"LpuSectionCheck\"
						from
							v_LpuSection LS 
							left join v_LpuSectionProfile LSP on LS.LpuSectionProfile_id = LSP.LpuSectionProfile_id
							left join v_LpuRegionType LRT on LRT.LpuRegionType_id = :LpuRegionType_id
						where LpuSection_id = :LpuSection_id
					";
					break;
			}
			if (!empty($reg_query)) {
				$reg_queryParams = [
					"LpuSection_id" => $data["LpuSection_id"],
					"LpuRegionType_id" => $data["LpuRegionType_id"]
				];
				$result = $callObject->queryResult($reg_query, $reg_queryParams);
				if (count($result) > 0 && $result[0]["LpuSectionCheck"] == 2) {
					$callObject->rollbackTransaction();
					return [['Error_Code' => '994', 'Error_Msg' => 'Профиль указанного отделения участка не соответствует типу участка.']];
				}
			}
		}
		// Проверяем номер на уникальность
		// @task https://redmine.swan.perm.ru/issues/66328
		// Исключение для Астрахани и Казахстана
		// @task https://redmine.swan.perm.ru/issues/77134
		// @task https://redmine.swan.perm.ru/issues/81469
		if (!in_array($callObject->getRegionNick(), ["astra", "kz"])) {
			$query = "
				select LpuRegion_id as \"LpuRegion_id\"
				from v_LpuRegion 
				where Lpu_id = :Lpu_id
				  and LpuRegion_id != coalesce(:LpuRegion_id::bigint, 0)
				  and LpuRegionType_id = :LpuRegionType_id
				  and LpuRegion_Name = :LpuRegion_Name
				  and LpuRegion_begDate <= coalesce(cast(:LpuRegion_endDate as date), LpuRegion_begDate)
				  and coalesce(LpuRegion_endDate, cast(:LpuRegion_begDate as date)) >= :LpuRegion_begDate
				limit 1
			";
			$queryParams = [
				"LpuRegion_id" => $data["LpuRegion_id"],
				"Lpu_id" => $data["Lpu_id"],
				"LpuRegionType_id" => $data["LpuRegionType_id"],
				"LpuRegion_Name" => $data["LpuRegion_Name"],
				"LpuRegion_begDate" => $data["LpuRegion_begDate"],
				"LpuRegion_endDate" => (!empty($data["LpuRegion_endDate"]) ? $data["LpuRegion_endDate"] : NULL)
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				$callObject->rollbackTransaction();
				return false;
			}
			$resp = $result->result("array");
			if (is_array($resp) && count($resp) > 0) {
				$callObject->rollbackTransaction();
				return [['Error_Code' => '995', 'Error_Msg' => 'Участок с таким номером, типом и периодом действия уже существует в системе.']];
			}
		}
		$allowedRegion = false;
		$data["allowEmptyMedPersonalData"] = !empty($data["allowEmptyMedPersonalData"]) ? $data["allowEmptyMedPersonalData"] : false;
		//проверка наличия врачей на участке
		if (!$data["allowEmptyMedPersonalData"] && empty($data["LpuRegionMedPersonalData"]) && empty($data["LpuRegion_endDate"])) {
			if (empty($data["LpuRegionType_SysNick"])) {
				$query = "
					select LpuRegionType_SysNick as \"LpuRegionType_SysNick\"
					from v_LpuRegionType
					where LpuRegionType_id = :LpuRegionType_id
					limit 1
				";
				$data["LpuRegionType_SysNick"] = $callObject->getFirstResultFromQuery($query, $data);
			}
			$regionNick = $callObject->getRegionNick();
			if ($regionNick == "perm") {
				if (in_array($data["LpuRegionType_SysNick"], ["ter", "ped", "vop", "comp", "prip", "feld", "gin", "stom"])) {
					$allowedRegion = true;
				}
			} elseif ($regionNick == "khak") {
				if (in_array($data["LpuRegionType_SysNick"], ["ter", "ped", "gin", "stom", "vop"])) {
					return false;
				}
			} elseif ($regionNick == "buryatiya") {
				if (in_array($data["LpuRegionType_SysNick"], ["ter", "ped", "gin", "vop"])) {
					$allowedRegion = true;
				}
			} elseif ($regionNick == "ufa") {
				if (in_array($data["LpuRegionType_SysNick"], ["ter", "ped", "gin", "vop", "feld"])) {
					$allowedRegion = true;
				}
			} else {
				if (in_array($data["LpuRegionType_SysNick"], ["ter", "ped", "gin", "vop"])) {
					$allowedRegion = true;
				}
			}
			//Код 995 для сообщений без возможности дальнейшего сохранения
			if ($allowedRegion) {
				$callObject->rollbackTransaction();
				return [['Error_Code' => '995', 'Error_Msg' => 'На участке должен быть хотя бы один врач.']];
			}
		}
		$proc = (!isset($data["LpuRegion_id"])) ? "p_LpuRegion_ins" : "p_LpuRegion_upd";
		$selectString = "
			LpuRegion_id as \"LpuRegion_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpuregion_id := :LpuRegion_id,
			    lpu_id := :Lpu_id,
			    lpuregiontype_id := :LpuRegionType_id,
			    lpuregion_name := :LpuRegion_Name,
			    lpuregion_descr := :LpuRegion_Descr,
			    lpuregion_begdate := :LpuRegion_begDate,
			    lpuregion_enddate := :LpuRegion_endDate,
			    lpusection_id := :LpuSection_id,
			    lpuregion_tfoms := :LpuRegion_tfoms,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuRegion_id" => $data["LpuRegion_id"],
			"Server_id" => $data["Server_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuRegionType_id" => $data["LpuRegionType_id"],
			"LpuRegion_Name" => $data["LpuRegion_Name"],
			"LpuRegion_tfoms" => !empty($data["LpuRegion_tfoms"]) ? $data["LpuRegion_tfoms"] : null,
			"LpuRegion_Descr" => $data["LpuRegion_Descr"],
			"LpuRegion_begDate" => $data["LpuRegion_begDate"],
			"LpuRegion_endDate" => $data["LpuRegion_endDate"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			return false;
		}
		$resp = $result->result("array");
		// Обрабатываем список врачей на участке
		if (!empty($data["LpuRegionMedPersonalData"])) {
			$LpuRegionMedPersonalData = json_decode($data["LpuRegionMedPersonalData"], true);
			if (is_array($LpuRegionMedPersonalData)) {
				$countMainRec = 0;
				foreach ($LpuRegionMedPersonalData as $key => $value) {
					if (
						!empty($value["MedStaffRegion_isMain"]) &&
						($value["MedStaffRegion_isMain"] === true || $value["MedStaffRegion_isMain"] === "true" || $value["MedStaffRegion_isMain"] === "2") &&
						(empty($value["status"]) || $value["status"] != "3")
					) {
						$LpuRegionMedPersonalData[$key]["MedStaffRegion_isMain"] = 2;
						$countMainRec += 1;
					}
				}
				if ($countMainRec > 1) {
					$callObject->rollbackTransaction();
					return [['Error_Code' => '995', 'Error_Msg' => 'На участке не может быть больше одного основного врача.']];
				}
				$checkDel = $callObject->checkMedStaffRegionDelAvailable($LpuRegionMedPersonalData, $data["Lpu_id"], $resp[0]["LpuRegion_id"], $data["LpuSection_id"]);
				if (is_array($checkDel) && !empty($checkDel[0]["Error_Msg"])) {
					$callObject->rollbackTransaction();
					return $checkDel;
				}
				// Проверяем наличие записей главного врача в на других участках
				// https://redmine.swan.perm.ru/issues/74912
				if ($countMainRec > 0 && (empty($data["checkMainMPDoubles"]) || $data["checkMainMPDoubles"] === true || $data["checkMainMPDoubles"] == "true")) {
					$mainMedPersonalCheckData = $callObject->checkMainMedPersonal($LpuRegionMedPersonalData, $data["Lpu_id"]);
					if (!is_array($mainMedPersonalCheckData) || $mainMedPersonalCheckData["count"] == -1) {
						$callObject->rollbackTransaction();
						return [['Error_Code' => '998', 'Error_Msg' => 'Ошибка при проверке дублей главного врача участка.']];
					} else if ($mainMedPersonalCheckData["count"] > 2) {
						$callObject->rollbackTransaction();
						return [['Error_Code' => '999', 'Error_Msg' => 'Сотрудник ' . $mainMedPersonalCheckData['fio'] . ' отмечен как основной врач еще на ' . $mainMedPersonalCheckData['count'] . ' участк' . (substr($mainMedPersonalCheckData['count'], -1) == '1' ? 'е' : 'ах') . '.']];
					}
				}
				for ($j = 0; $j < count($LpuRegionMedPersonalData); $j++) {
					if (!empty($LpuRegionMedPersonalData[$j]["status"]) && $LpuRegionMedPersonalData[$j]["status"] == 1) {
						continue;
					}
					if (
						($callObject->regionNick == "perm" && !empty($LpuRegionMedPersonalData[$j]["MedStaffFact_id"])) ||
						($callObject->regionNick != "perm" && !empty($LpuRegionMedPersonalData[$j]["MedPersonal_id"]))
					) {
						$LpuRegionMedPersonal = [
							"Lpu_id" => $data["Lpu_id"],
							"Server_id" => $data["Server_id"],
							"pmUser_id" => $data["pmUser_id"],
							"LpuSection_id" => $data["LpuSection_id"],
							"checkPost" => !empty($data["checkPost"]) ? $data["checkPost"] : null,
							"checkStavka" => !empty($data["checkStavka"]) ? $data["checkStavka"] : null,
							"checkLpuSection" => !empty($data["checkLpuSection"]) ? $data["checkLpuSection"] : null,
							"LpuRegion_id" => $resp[0]["LpuRegion_id"]
						];
						$LpuRegionMedPersonal["MedStaffFact_id"] = (!empty($LpuRegionMedPersonalData[$j]["MedStaffFact_id"]) ? $LpuRegionMedPersonalData[$j]["MedStaffFact_id"] : null);
						$LpuRegionMedPersonal["MedPersonal_id"] = $LpuRegionMedPersonalData[$j]["MedPersonal_id"];
						$LpuRegionMedPersonal["MedStaffRegion_id"] = $LpuRegionMedPersonalData[$j]["MedStaffRegion_id"];
						$LpuRegionMedPersonal["MedStaffRegion_isMain"] = (!empty($LpuRegionMedPersonalData[$j]["MedStaffRegion_isMain"]) ? $LpuRegionMedPersonalData[$j]["MedStaffRegion_isMain"] : null);
						$LpuRegionMedPersonal["MedStaffRegion_begDate"] = $LpuRegionMedPersonalData[$j]["MedStaffRegion_begDate"];
						$LpuRegionMedPersonal["MedStaffRegion_endDate"] = $LpuRegionMedPersonalData[$j]["MedStaffRegion_endDate"];
						$LpuRegionMedPersonal["status"] = !empty($LpuRegionMedPersonalData[$j]["status"]) ? $LpuRegionMedPersonalData[$j]["status"] : 0;
						$response = null;
						if ($LpuRegionMedPersonal['status'] == 2 || $LpuRegionMedPersonal['status'] == 0) {
							$response = $callObject->saveMedStaffRegion($LpuRegionMedPersonal);
						} elseif ($LpuRegionMedPersonal['status'] == 3) {
							$response = $callObject->deleteMedStaffRegion($LpuRegionMedPersonal);
						}
						if (!$response && !is_array($response)) {
							$callObject->rollbackTransaction();
							$response['Error_Code'] = '2';
							$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение палаты)';
							return $response;
						} else if (!empty($response[0]["Error_Msg"])) {
							return $response;
						}
					}
				}
			}
		}
		// Проверка-сообщение на наличие открытого периода по фондодержанию (период фондодержания искать по типу участка, период работы участка должен попадать в период фондодержания).
		$query = "
			select 1
			from v_LpuPeriodFondHolder LpuPeriodFondHolder 
			where Lpu_id = :Lpu_id
			  and LpuRegionType_id = :LpuRegionType_id
			  and (LpuPeriodFondHolder_begDate <= :LpuRegion_endDate or :LpuRegion_endDate is null)
			  and (LpuPeriodFondHolder_endDate >= :LpuRegion_begDate or LpuPeriodFondHolder_endDate is null)
			limit 1
		";
		$result = $callObject->queryResult($query, $data);
		if (count($result) == 0) {
			$resp[0]["Alert_Msg"] = "Для данного типа участка нет открытого периода по фондодержанию.";
		}
		$callObject->commitTransaction();
		return $resp;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveMedStaffRegion(LpuStructure_model $callObject, $data)
	{
		if (isset($data["MedStaffRegion_id"]) && $data["MedStaffRegion_id"] > 0) {
			$procedure = "p_MedStaffRegion_upd";
		} else {
			$data["MedStaffRegion_id"] = 0;
			$procedure = "p_MedStaffRegion_ins";
		}
		$queryParams = [
			"MedStaffRegion_id" => $data["MedStaffRegion_id"],
			"MedStaffRegion_isMain" => (!empty($data["MedStaffRegion_isMain"]) && in_array($data["MedStaffRegion_isMain"], array("true", 2))) ? 2 : 1,
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"MedPersonal_id" => $data["MedPersonal_id"],
			"LpuSection_id" => !empty($data["LpuSection_id"]) ? $data["LpuSection_id"] : null,
			"MedStaffRegion_begDate" => !empty($data["MedStaffRegion_begDate"]) ? date("Y-m-d", strtotime($data["MedStaffRegion_begDate"])) : null,
			"MedStaffRegion_endDate" => !empty($data["MedStaffRegion_endDate"]) ? date("Y-m-d", strtotime($data["MedStaffRegion_endDate"])) : null,
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"pmUser_id" => $data["pmUser_id"],
			"LpuRegion_id" => $data["LpuRegion_id"]
		];
		$selectString = "
			MedStaffRegion_id as \"MedStaffRegion_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    server_id := :Server_id,
			    medstaffregion_id := :MedStaffRegion_id,
			    lpu_id := :Lpu_id,
			    medpersonal_id := :MedPersonal_id,
			    lpuregion_id := :LpuRegion_id,
			    medstaffregion_begdate := :MedStaffRegion_begDate,
			    medstaffregion_enddate := :MedStaffRegion_endDate,
			    medstafffact_id := :MedStaffFact_id,
			    medstaffregion_ismain := :MedStaffRegion_isMain,
			    pmuser_id := :pmUser_id
			);
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
	 *
	 * @throws Exception
	 */
	public static function SaveUslugaSection(LpuStructure_model $callObject, $data)
	{
		// предварительно надо проверить, может такая услуга на этом отделении уже есть
		if (!$callObject->checkUslugaSection($data)) {
			return [['UslugaSection_id'=>null, 'Error_Code'=>1,'Error_Msg' => 'Сохранение невозможно, поскольку выбранная услуга уже заведена на этом отделении!<br/>Для ввода или редактирования тарифа найдите ранее сохраненную услугу и измените ее.']];
		}
		$proc = (!isset($data["UslugaSection_id"])) ? "p_UslugaSection_ins" : "p_UslugaSection_upd";
		$selectString = "
			UslugaSection_id as \"UslugaSection_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    uslugasection_id := :UslugaSection_id,
			    lpusection_id := :LpuSection_id,
			    usluga_id := :Usluga_id,
			    uslugasection_code := :UslugaSection_Code,
			    uslugaprice_ue := :UslugaPrice_ue,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"UslugaSection_id" => $data["UslugaSection_id"],
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"Usluga_id" => $data["Usluga_id"],
			"UslugaSection_Code" => $data["Usluga_Code"],
			"UslugaPrice_ue" => $data["UslugaPrice_ue"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		/**@var CI_DB_result $result */
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
	public static function SaveUslugaSectionTariff(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$proc = (!isset($data["UslugaSectionTariff_id"])) ? "p_UslugaSectionTariff_ins" : "p_UslugaSectionTariff_upd";
		$selectString = "
			UslugaSectionTariff_id as \"UslugaSectionTariff_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    uslugasectiontariff_id := :UslugaSectionTariff_id,
			    uslugasection_id := :UslugaSection_id,
			    uslugasectiontariff_tariff := :UslugaSectionTariff_Tariff,
			    uslugasectiontariff_begdate := :UslugaSectionTariff_begDate,
			    uslugasectiontariff_enddate := :UslugaSectionTariff_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"UslugaSectionTariff_id" => $data["UslugaSectionTariff_id"],
			"Server_id" => $data["Server_id"],
			"UslugaSection_id" => $data["UslugaSection_id"],
			"UslugaSectionTariff_Tariff" => $data["UslugaSectionTariff_Tariff"],
			"UslugaSectionTariff_begDate" => $data["UslugaSectionTariff_begDate"],
			"UslugaSectionTariff_endDate" => $data["UslugaSectionTariff_endDate"],
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
	public static function SaveUslugaComplexTariff(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$proc = (!isset($data["UslugaComplexTariff_id"])) ? "p_UslugaComplexTariff_ins" : "p_UslugaComplexTariff_upd";
		$selectString = "
			UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    uslugacomplextariff_id := :UslugaComplexTariff_id,
			    uslugacomplex_id := :UslugaComplex_id,
			    uslugacomplextariff_tariff := :UslugaComplexTariff_Tariff,
			    uslugacomplextariff_begdate := :UslugaComplexTariff_begDate,
			    uslugacomplextariff_enddate := :UslugaComplexTariff_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"UslugaComplexTariff_id" => $data["UslugaComplexTariff_id"],
			"Server_id" => $data["Server_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"UslugaComplexTariff_Tariff" => $data["UslugaComplexTariff_Tariff"],
			"UslugaComplexTariff_begDate" => $data["UslugaComplexTariff_begDate"],
			"UslugaComplexTariff_endDate" => $data["UslugaComplexTariff_endDate"],
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
	public static function SaveLpuSectionTariff(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$proc = (empty($data["LpuSectionTariff_id"])) ? "p_LpuSectionTariff_ins" : "p_LpuSectionTariff_upd";
		$selectString = "
			LpuSectionTariff_id as \"LpuSectionTariff_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpusectiontariff_id := :LpuSectionTariff_id,
			    lpusection_id := :LpuSection_id,
			    tariffclass_id := :TariffClass_id,
			    lpusectiontariff_code := Null,
			    lpusectiontariff_tariff := :LpuSectionTariff_Tariff,
			    lpusectiontariff_setdate := :LpuSectionTariff_setDate,
			    lpusectiontariff_disdate := :LpuSectionTariff_disDate,
			    lpusectiontariff_totalfactor := :LpuSectionTariff_TotalFactor,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionTariff_id" => $data["LpuSectionTariff_id"],
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"TariffClass_id" => $data["TariffClass_id"],
			"LpuSectionTariff_Tariff" => $data["LpuSectionTariff_Tariff"],
			"LpuSectionTariff_TotalFactor" => $data["LpuSectionTariff_TotalFactor"],
			"LpuSectionTariff_setDate" => $data["LpuSectionTariff_setDate"],
			"LpuSectionTariff_disDate" => $data["LpuSectionTariff_disDate"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/** @var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function SaveLpuSectionShift(LpuStructure_model $callObject, $data)
	{
		$checkLpuSectionShiftFailure = $callObject->checkLpuSectionShift($data);
		if ($checkLpuSectionShiftFailure) {
			throw new Exception($checkLpuSectionShiftFailure["Error_Msg"]);
		}
		$callObject->load->helper('Date');
		$proc = (!isset($data['LpuSectionShift_id'])) ? 'p_LpuSectionShift_ins' : 'p_LpuSectionShift_upd';
		$selectString = "
			LpuSectionShift_id as \"LpuSectionShift_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpusectionshift_id := :LpuSectionShift_id,
			    lpusection_id := :LpuSection_id,
			    lpusectionshift_code := Null,
			    lpusectionshift_setdate := :LpuSectionShift_setDate,
			    lpusectionshift_disdate := :LpuSectionShift_disDate,
			    lpusectionshift_count := :LpuSectionShift_Count,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionShift_id" => $data["LpuSectionShift_id"],
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSectionShift_Count" => $data["LpuSectionShift_Count"],
			"LpuSectionShift_setDate" => $data["LpuSectionShift_setDate"],
			"LpuSectionShift_disDate" => $data["LpuSectionShift_disDate"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД");
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function UpdateLpuSectionStateWardBed(LpuStructure_model $callObject, $data = array())
	{
		//функция только для Москвы
		if (getRegionNick() != 'msk') {
			return array('Error_Msg' => 'Задание работает только для Москвы');
		}
		//получаем все профили койки для палаты
		$query = "
			select
				LSW.LpuSectionWard_id as \"LpuSectionWard_id\",
				coalesce(sum(LSBS.LpuSectionBedState_Fact), coalesce(LSW.LpuSectionWard_DopPlace, 0)) as \"totalBed\", --Общее кол-во мест
				coalesce(sum(LSBS.LpuSectionBedState_Fact)-coalesce(LSW.LpuSectionWard_DopPlace, 0), 0) as \"mainBed\" --Кол-во основных мест
			from
				v_LpuSectionWard LSW
				left join v_LpuSectionWardLink LSWL on LSWL.LpuSectionWard_id = LSW.LpuSectionWard_id
				left join v_LpuSectionBedState LSBS on LSBS.LpuSectionBedState_id = LSWL.LpuSectionBedState_id
			where
				(:LpuSectionWard_id is null or LSW.LpuSectionWard_id in (:LpuSectionWard_id, COALESCE(:oldLpuSectionWard_id, 0)))
				and (LSW.LpuSectionWard_disDate is null or LSBS.LpuSectionBedState_begDate <= LSW.LpuSectionWard_disDate)
				and (LSBS.LpuSectionBedState_endDate is null or LSBS.LpuSectionBedState_endDate > LSW.LpuSectionWard_setDate)
			group by LSW.LpuSectionWard_id, LSW.LpuSectionWard_DopPlace
		";
		
		$bedCountResult = $callObject->db->query($query, array(
			'LpuSectionWard_id' => !empty($data['LpuSectionWard_id']) ? $data['LpuSectionWard_id'] : null,
			'oldLpuSectionWard_id' => !empty($data['oldLpuSectionWard_id']) ? $data['oldLpuSectionWard_id'] : null
		));
		$bedCountResponse = $bedCountResult->result('array');
		
		foreach ($bedCountResponse as $ward) {
			$callObject->saveObject('LpuSectionWard', array(
				'LpuSectionWard_id' => $ward['LpuSectionWard_id'],
				'LpuSectionWard_BedCount' => $ward['totalBed'],
				'LpuSectionWard_MainPlace' => $ward['mainBed']
			));
		}
		
		return array('success' => true);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|mixed
	 *
	 * @throws Exception
	 */
	public static function SaveLpuSectionBedState(LpuStructure_model $callObject, $data)
	{
		$callObject->beginTransaction();
		if (!empty($data["source"]) && $data["source"] == "API") {
			// проверяем наличие переданных значений во внешних таблицах
			$checkResult = $callObject->checkForeignKeyValues("LpuSectionBedState", "dbo", $data);
			if ($checkResult !== true) {
				throw new Exception($checkResult);
			}
		}
		$callObject->load->helper("Date");
		// Проверка на пересечение
		if (!empty($data["LpuSectionBedProfileLink_id"])) {
			$query = "
				select
					LSBS.LpuSectionBedState_id as \"LpuSectionBedState_id\",
				    LSBP.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_Name\",
				    to_char(LSBS.LpuSectionBedState_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionBedState_begDate\",
				    to_char(LSBS.LpuSectionBedState_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionBedState_endDate\"
				from
					v_LpuSectionBedState LSBS 
					left join v_LpuSectionBedProfile LSBP on LSBS.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				where
					(coalesce(LpuSectionProfile_id, 0) = coalesce(:LpuSectionProfile_id::bigint, 0)) and
				    LpuSectionBedProfileLink_fedid = :LpuSectionBedProfileLink_fedid and
					((LpuSectionBedState_begDate <= :LpuSectionBedState_endDate or :LpuSectionBedState_endDate is null) and (LpuSectionBedState_endDate >= :LpuSectionBedState_begDate or LpuSectionBedState_endDate is null)) and
					LpuSection_id = :LpuSection_id and
				    LpuSectionBedState_id != coalesce(:LpuSectionBedState_id::bigint, 0)
				limit 1
			";
			$queryParams = [
				"LpuSectionBedState_id" => (!empty($data["LpuSectionBedState_id"]) ? $data["LpuSectionBedState_id"] : null),
				"LpuSectionProfile_id" => $data["LpuSectionProfile_id"],
				"LpuSectionBedProfile_id" => $data["LpuSectionBedProfile_id"],
				"LpuSection_id" => $data["LpuSection_id"],
				"LpuSectionBedState_begDate" => $data["LpuSectionBedState_begDate"],
				"LpuSectionBedState_endDate" => $data["LpuSectionBedState_endDate"],
				"LpuSectionBedProfileLink_fedid" => $data["LpuSectionBedProfileLink_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			$response = $result->result("array");
			if (!is_array($response)) {
				$callObject->rollbackTransaction();
				throw new Exception("Произошла ошибка при выполнении проверки на пересечение профилей коек.");
			} else if (count($response) > 0) {
				$callObject->rollbackTransaction();
				$endDate = ($response[0]["LpuSectionBedState_endDate"]) ? " по " . $response[0]["LpuSectionBedState_endDate"] : "";
				if (!empty($data["source"]) && $data["source"] == "API") {
					$errorMsg = "В структуре отделения уже внесена информация по выбранному профилю койки: {$response[0]['LpuSectionBedProfile_Name']}, период действия: с {$response[0]['LpuSectionBedState_begDate']}{$endDate}. Необходимо изменить профиль койки или период действия.";
				} else {
					$errorMsg = "В структуре отделения уже внесена информация по выбранному профилю койки: <b>{$response[0]['LpuSectionBedProfile_Name']}</b>, период действия: с {$response[0]['LpuSectionBedState_begDate']}{$endDate}.<br>".mb_strtoupper('Необходимо изменить профиль койки или период действия').".";
				}
				throw new Exception($errorMsg);
			}
		}
		if (!isset($data["LpuSectionBedState_id"])) {
			$proc = "p_LpuSectionBedState_ins";
		} else {
			$proc = "p_LpuSectionBedState_upd";

			if (!empty($data["source"]) && $data["source"] == "API") {
				$data["LpuSection_id"] = $callObject->getFirstResultFromQuery("select LpuSection_id from v_LpuSectionBedState where LpuSectionBedState_id = :LpuSectionBedState_id limit 1", $data, true);
				if ($data["LpuSection_id"] === false || empty($data["LpuSection_id"])) {
					throw new Exception("Не удалось определить идентификатор отделения");
				}
			}
		}
		$selectString = "
			LpuSectionBedState_id as \"LpuSectionBedState_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpusectionbedstate_id := :LpuSectionBedState_id,
			    lpusection_id := :LpuSection_id,
			    lpusectionbedstate_plan := :LpuSectionBedState_Plan,
			    lpusectionbedstate_fact := :LpuSectionBedState_Fact,
			    lpusectionbedstate_repair := :LpuSectionBedState_Repair,
			    lpusectionbedstate_begdate := :LpuSectionBedState_begDate,
			    lpusectionbedstate_enddate := :LpuSectionBedState_endDate,
			    lpusectionprofile_id := :LpuSectionProfile_id,
			    lpusectionbedprofile_id := :LpuSectionBedProfile_id,
			    lpusectionbedstate_profilename := :LpuSectionBedState_ProfileName,
			    lpusectionbedstate_countoms := :LpuSectionBedState_CountOms,
			    lpusectionbedstate_maleplan := :LpuSectionBedState_MalePlan,
			    lpusectionbedstate_malefact := :LpuSectionBedState_MaleFact,
			    lpusectionbedstate_femaleplan := :LpuSectionBedState_FemalePlan,
			    lpusectionbedstate_femalefact := :LpuSectionBedState_FemaleFact,
			    lpusectionbedprofilelink_fedid := :LpuSectionBedProfileLink_fedid,
			    pmuser_id := :pmUser_id
			);
		";
		$params = [
			"LpuSectionBedState_id" => (!empty($data["LpuSectionBedState_id"]) ? $data["LpuSectionBedState_id"] : null),
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSectionProfile_id" => $data["LpuSectionProfile_id"],
			"LpuSectionBedProfile_id" => $data["LpuSectionBedProfile_id"],
			"LpuSectionBedState_Plan" => $data["LpuSectionBedState_Plan"],
			"LpuSectionBedState_Repair" => $data["LpuSectionBedState_Repair"],
			"LpuSectionBedState_ProfileName" => $data["LpuSectionBedState_ProfileName"],
			"LpuSectionBedState_Fact" => $data["LpuSectionBedState_Fact"],
			"LpuSectionBedState_CountOms" => $data["LpuSectionBedState_CountOms"],
			"LpuSectionBedState_begDate" => $data["LpuSectionBedState_begDate"],
			"LpuSectionBedState_endDate" => $data["LpuSectionBedState_endDate"],
			"LpuSectionBedState_MalePlan" => $data["LpuSectionBedState_MalePlan"],
			"LpuSectionBedState_MaleFact" => $data["LpuSectionBedState_MaleFact"],
			"LpuSectionBedState_FemalePlan" => $data["LpuSectionBedState_FemalePlan"],
			"LpuSectionBedState_FemaleFact" => $data["LpuSectionBedState_FemaleFact"],
			"LpuSectionBedProfileLink_fedid" => (!empty($data["LpuSectionBedProfileLink_id"]) ? $data["LpuSectionBedProfileLink_id"] : null),
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение койки по профилю)");
		}
		$queryResponse = $result->result("array");
		if (!is_array($queryResponse)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при сохранение койки по профилю");
		} else if (!empty($queryResponse[0]['Error_Msg'])) {
			$callObject->rollbackTransaction();
			return $queryResponse;
		}
		
		if (getRegionNick() == 'msk') {
			//создаем или обновляем связь с палатой
			$sql = "
				select
					LpuSectionWardLink_id as \"LpuSectionWardLink_id\",
					LpuSectionBedState_id as \"LpuSectionBedState_id\",
					LpuSectionWard_id as \"LpuSectionWard_id\",
					LpuSectionWardLink_begDate as \"LpuSectionWardLink_begDate\",
					LpuSectionWardLink_endDate as \"LpuSectionWardLink_endDate\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					LpuSectionWardLink_insDT as \"LpuSectionWardLink_insDT\",
					LpuSectionWardLink_updDT as \"LpuSectionWardLink_updDT\"
				from
					v_LpuSectionWardLink
				where
					LpuSectionBedState_id = :LpuSectionBedState_id
				limit 1
			";
			$wardLinkRes = $callObject->getFirstRowFromQuery($sql, array(
				'LpuSectionBedState_id' => $queryResponse[0]['LpuSectionBedState_id']
			), true);
			
			if (!empty($wardLinkRes)) {
				$proc = 'p_LpuSectionWardLink_upd';
			}
			else {
				$proc = 'p_LpuSectionWardLink_ins';
			}
			
			$query = "
				select
					LpuSectionWardLink_id as \"LpuSectionWardLink_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$proc}(
					lpusectionwardlink_id := :LpuSectionWardLink_id,
					lpusectionbedstate_id := :LpuSectionBedState_id,
					lpusectionward_id := :LpuSectionWard_id,
					lpusectionwardlink_begdate := :LpuSectionWardLink_begDate,
					lpusectionwardlink_enddate := :LpuSectionWardLink_endDate,
					pmuser_id := :pmUser_id
				);
			";
			
			$params = array(
				'LpuSectionWardLink_id' => (!empty($wardLinkRes['LpuSectionWardLink_id']) ? $wardLinkRes['LpuSectionWardLink_id'] : null),
				'LpuSectionBedState_id' => $queryResponse[0]['LpuSectionBedState_id'],
				'LpuSectionWard_id' => !empty($data['LpuSectionWard_id']) ? $data['LpuSectionWard_id'] : null,
				'LpuSectionWardLink_begDate' => $data['LpuSectionBedState_begDate'],
				'LpuSectionWardLink_endDate' => $data['LpuSectionBedState_endDate'],
				'pmUser_id' => $data['pmUser_id']
			);
			
			$resultWardLink = $callObject->db->query($query, $params);
			
			if ( !is_object($resultWardLink) ) {
				$callObject->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение связи профиля и палаты)';
				return $response;
			}
			
			$queryResponseWardLink = $resultWardLink->result('array');
			
			if ( !is_array($queryResponseWardLink) ) {
				$callObject->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение связи профиля и палаты)';
				return $response;
			}
			else if ( !empty($queryResponseWardLink[0]['Error_Msg']) ) {
				$callObject->rollbackTransaction();
				return $queryResponseWardLink;
			}
			$paramsUpdateBeds = array(
				'LpuSectionWard_id' => !empty($data['LpuSectionWard_id']) ? $data['LpuSectionWard_id'] : null,
				'oldLpuSectionWard_id' => !empty($wardLinkRes['LpuSectionWard_id']) ? $wardLinkRes['LpuSectionWard_id'] : null,
				'Lpu_id' => $data['Lpu_id']
			);

			self::UpdateLpuSectionStateWardBed($callObject, $paramsUpdateBeds);
		}
		
		$response = $queryResponse[0];
		// Обрабатываем список операций над койками
		if (!empty($data["DBedOperationData"])) {
			$DBedOperationData = json_decode($data["DBedOperationData"], true);
			if (is_array($DBedOperationData)) {
				$isDBedOperationRepeat[] = [];
				for ($i = 0; $i < count($DBedOperationData); $i++) {
					$DBedOperation = [
						"pmUser_id" => $data["pmUser_id"],
						"LpuSectionBedState_id" => $response["LpuSectionBedState_id"]
					];
					if (empty($DBedOperationData[$i]["LpuSectionBedStateOper_id"]) || !is_numeric($DBedOperationData[$i]["LpuSectionBedStateOper_id"])) {
						continue;
					}
					if (empty($DBedOperationData[$i]["DBedOperation_id"]) || !is_numeric($DBedOperationData[$i]["DBedOperation_id"])) {
						continue;
					}
					if (empty($DBedOperationData[$i]['LpuSectionBedStateOper_OperDT'])) {
						continue;
					}
					$DBedOperation['LpuSectionBedStateOper_id'] = $DBedOperationData[$i]['LpuSectionBedStateOper_id'];
					$DBedOperation['DBedOperation_id'] = $DBedOperationData[$i]['DBedOperation_id'];
					$DBedOperation['LpuSectionBedStateOper_OperDT'] = $DBedOperationData[$i]['LpuSectionBedStateOper_OperDT'];
					$queryResponse = $callObject->saveDBedOperation($DBedOperation);
					if (!is_array($queryResponse)) {
						$callObject->rollbackTransaction();
						throw new Exception("Ошибка при " . ($DBedOperationData[$i]["RecordStatus_Code"] == 3 ? "удалении" : "сохранении") . " объекта комфортности");
					} else if (!empty($queryResponse[0]['Error_Msg'])) {
						$callObject->rollbackTransaction();
						return $queryResponse[0];
					}
				}
			}
		}
		$callObject->commitTransaction();
		return [$response];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function SaveLpuSectionFinans(LpuStructure_model $callObject, $data)
	{
		$proc = (empty($data["LpuSectionFinans_id"])) ? "p_LpuSectionFinans_ins" : "p_LpuSectionFinans_upd";
		$selectString = "
			LpuSectionFinans_id as \"LpuSectionFinans_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpusectionfinans_id := :LpuSectionFinans_id,
			    lpusection_id := :LpuSection_id,
			    lpusectionfinans_begdate := :LpuSectionFinans_begDate,
			    lpusectionfinans_enddate := :LpuSectionFinans_endDate,
			    paytype_id := :PayType_id,
			    lpusectionfinans_ismrc := :LpuSectionFinans_IsMRC,
			    lpusectionfinans_isquoteoff := :LpuSectionFinans_IsQuoteOff,
			    lpusectionfinans_planhosp := :LpuSectionFinans_PlanHosp,
			    lpusectionfinans_plan := :LpuSectionFinans_Plan,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionFinans_id" => $data["LpuSectionFinans_id"],
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"PayType_id" => $data["PayType_id"],
			"LpuSectionFinans_IsMRC" => $data["LpuSectionFinans_IsMRC"],
			"LpuSectionFinans_IsQuoteOff" => $data["LpuSectionFinans_IsQuoteOff"],
			"LpuSectionFinans_begDate" => $data["LpuSectionFinans_begDate"],
			"LpuSectionFinans_endDate" => $data["LpuSectionFinans_endDate"],
			"LpuSectionFinans_Plan" => $data["LpuSectionFinans_Plan"],
			"LpuSectionFinans_PlanHosp" => $data["LpuSectionFinans_PlanHosp"],
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
	public static function SaveLpuSectionLicence(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$proc = (!isset($data["LpuSectionLicence_id"])) ? "p_LpuSectionLicence_ins" : "p_LpuSectionLicence_upd";
		$selectString = "
			LpuSectionLicence_id as \"LpuSectionLicence_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpusectionlicence_id := :LpuSectionLicence_id,
			    lpusection_id := :LpuSection_id,
			    lpusectionlicence_num := :LpuSectionLicence_Num,
			    lpusectionlicence_begdate := :LpuSectionLicence_begDate,
			    lpusectionlicence_enddate := :LpuSectionLicence_endDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionLicence_id" => $data["LpuSectionLicence_id"],
			"Server_id" => $data["Server_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSectionLicence_Num" => $data["LpuSectionLicence_Num"],
			"LpuSectionLicence_begDate" => $data["LpuSectionLicence_begDate"],
			"LpuSectionLicence_endDate" => $data["LpuSectionLicence_endDate"],
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
	public static function SaveLpuSectionTariffMes(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$proc = (!isset($data["LpuSectionTariffMes_id"])) ? "p_LpuSectionTariffMes_ins" : "p_LpuSectionTariffMes_upd";
		$selectString = "
			LpuSectionTariffMes_id as \"LpuSectionTariffMes_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    lpusectiontariffmes_id := :LpuSectionTariffMes_id,
			    lpusection_id := :LpuSection_id,
			    mes_id := :Mes_id,
			    tariffmestype_id := :TariffMesType_id,
			    lpusectiontariffmes_tariff := :LpuSectionTariffMes_Tariff,
			    lpusectiontariffmes_setdate := :LpuSectionTariffMes_setDate,
			    lpusectiontariffmes_disdate := :LpuSectionTariffMes_disDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionTariffMes_id" => $data["LpuSectionTariffMes_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"Mes_id" => $data["Mes_id"],
			"TariffMesType_id" => $data["TariffMesType_id"],
			"LpuSectionTariffMes_Tariff" => $data["LpuSectionTariffMes_Tariff"],
			"LpuSectionTariffMes_setDate" => $data["LpuSectionTariffMes_setDate"],
			"LpuSectionTariffMes_disDate" => $data["LpuSectionTariffMes_disDate"],
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
	public static function SaveLpuSectionPlan(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$proc = (!isset($data["LpuSectionPlan_id"])) ? "p_LpuSectionPlan_ins" : "p_LpuSectionPlan_upd";
		$selectString = "
			LpuSectionPlan_id as \"LpuSectionPlan_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    lpusectionplan_id := :LpuSectionPlan_id,
			    lpusection_id := :LpuSection_id,
			    lpusectionplantype_id := :LpuSectionPlanType_id,
			    lpusectionplan_setdate := :LpuSectionPlan_setDate,
			    lpusectionplan_disdate := :LpuSectionPlan_disDate,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionPlan_id" => $data["LpuSectionPlan_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSectionPlanType_id" => $data["LpuSectionPlanType_id"],
			"LpuSectionPlan_setDate" => $data["LpuSectionPlan_setDate"],
			"LpuSectionPlan_disDate" => $data["LpuSectionPlan_disDate"],
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
	 * @return array
	 * @throws Exception
	 */
	public static function SavePersonDopDispPlan(LpuStructure_model $callObject, $data)
	{
		if (!isSuperadmin() && empty($data['LpuRegion_id'])) {
			throw new Exception("Поле \"Участок\" обязательно для заполнения");
		}
		// Проверяем уникальность записи с учетом всех параметров
		$query = "
			select count(PersonDopDispPlan_id) as cnt
			from v_PersonDopDispPlan 
			where PersonDopDispPlan_id != coalesce(:PersonDopDispPlan_id::bigint, 0)
			  and Lpu_id = :Lpu_id
			  and coalesce(LpuRegion_id, 0) = coalesce(:LpuRegion_id::bigint, 0)
			  and coalesce(PersonDopDispPlan_Year, 0) = coalesce(:PersonDopDispPlan_Year, 0)
			  and coalesce(PersonDopDispPlan_Month, 0) = coalesce(:PersonDopDispPlan_Month, 0)
			  and DispDopClass_id = :DispDopClass_id
		";
		if (in_array($data["DispDopClass_id"], [4, 5])) {
			$query .= "and EducationInstitutionType_id = :EducationInstitutionType_id";
		}
		$queryParams = [
			"PersonDopDispPlan_id" => $data["PersonDopDispPlan_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuRegion_id" => $data["LpuRegion_id"],
			"PersonDopDispPlan_Year" => $data["PersonDopDispPlan_Year"],
			"PersonDopDispPlan_Month" => $data["PersonDopDispPlan_Month"],
			"DispDopClass_id" => $data["DispDopClass_id"],
			"EducationInstitutionType_id" => $data["EducationInstitutionType_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (проверка уникальности записи) (строка " . __LINE__ . ")");
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			throw new Exception("Ошибка при проверке уникальности записи (строка " . __LINE__ . ")");
		} else if (!empty($response[0]["cnt"])) {
			throw new Exception("План диспансеризации взрослого населения с указанными годом, месяцем и номером участка уже существует в базе данных");
		}
		$proc = (!isset($data["PersonDopDispPlan_id"])) ? "p_PersonDopDispPlan_ins" : "p_PersonDopDispPlan_upd";
		$selectString = "
			PersonDopDispPlan_id as \"PersonDopDispPlan_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    persondopdispplan_id := :PersonDopDispPlan_id,
			    lpu_id := :Lpu_id,
			    dispdopclass_id := :DispDopClass_id,
			    persondopdispplan_year := :PersonDopDispPlan_Year,
			    persondopdispplan_month := :PersonDopDispPlan_Month,
			    persondopdispplan_plan := :PersonDopDispPlan_Plan,
			    lpuregion_id := :LpuRegion_id,
			    educationinstitutiontype_id := :EducationInstitutionType_id,
			    quoteunittype_id := :QuoteUnitType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"PersonDopDispPlan_id" => $data["PersonDopDispPlan_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuRegion_id" => $data["LpuRegion_id"],
			"PersonDopDispPlan_Year" => $data["PersonDopDispPlan_Year"],
			"PersonDopDispPlan_Month" => $data["PersonDopDispPlan_Month"],
			"PersonDopDispPlan_Plan" => $data["PersonDopDispPlan_Plan"],
			"DispDopClass_id" => $data["DispDopClass_id"],
			"EducationInstitutionType_id" => $data["EducationInstitutionType_id"],
			"QuoteUnitType_id" => $data["QuoteUnitType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение плана диспансеризации) (строка " . __LINE__ . ")");
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			throw new Exception("Ошибка при сохранении плана диспансеризации");
		}
		return $response;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function SaveLpuSectionQuote(LpuStructure_model $callObject, $data)
	{
		$data["LpuSectionQuote_Fact"] = null;
		$data["OrgSMO_id"] = null;
		$proc = (empty($data["LpuSectionQuote_id"])) ? "p_LpuSectionQuote_ins" : "p_LpuSectionQuote_upd";
		if (!empty($data["LpuSectionQuote_id"])) {
			// https://redmine.swan.perm.ru/issues/48193
			$query = "
				select 
					LpuSectionQuote_Fact as \"LpuSectionQuote_Fact\",
					OrgSMO_id as \"OrgSMO_id\"
				from v_LpuSectionQuote LSQ 
				where LpuSectionQuote_id = :LpuSectionQuote_id
				limit 1
			";
			$queryParams = [
				"LpuSectionQuote_id" => $data["LpuSectionQuote_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при выполнении запроса к базе данных (получение данных)");
			}
			$response = $result->result("array");
			if (is_array($response) && count($response) > 0) {
				$data["LpuSectionQuote_Fact"] = $response[0]["LpuSectionQuote_Fact"];
				$data["OrgSMO_id"] = $response[0]["OrgSMO_id"];
			}
		}
		$callObject->beginTransaction();

		// предварительно надо проверить на уникальность данных
		$query = "
			select count(*) as rec
			from v_LpuSectionQuote LSQ 
			where LSQ.Lpu_id = :Lpu_id
			  and LSQ.LpuSectionQuote_id <> COALESCE(:LpuSectionQuote_id::bigint, 0)
			  and LSQ.LpuSectionQuote_Year = :LpuSectionQuote_Year
			  and LSQ.LpuSectionQuote_begDate = :LpuSectionQuote_begDate
			  and LSQ.LpuUnitType_id = :LpuUnitType_id
			  and LSQ.LpuSectionProfile_id = :LpuSectionProfile_id
			  and LSQ.QuoteUnitType_id = :QuoteUnitType_id
			  and LSQ.PayType_id = :PayType_id
		";
		$queryParams = [
			"LpuSectionQuote_id" => $data["LpuSectionQuote_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuSectionQuote_Year" => $data["LpuSectionQuote_Year"],
			"LpuSectionQuote_begDate" => $data["LpuSectionQuote_begDate"],
			"LpuUnitType_id" => $data["LpuUnitType_id"],
			"LpuSectionProfile_id" => $data["LpuSectionProfile_id"],
			"QuoteUnitType_id" => $data["QuoteUnitType_id"],
			"PayType_id" => $data["PayType_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (контроль двойных записей при сохранении планирования МО)");
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при контроле двойных записей планирования");
		} else if ($response[0]["rec"] > 0) {
			$callObject->rollbackTransaction();
			throw new Exception("Невозможно сохранить планирование на указанный год, с указанным профилем <br/>и видом медицинской помощи, поскольку запись планирования с этими данными уже существует.");
		}
		$selectString = "
			LpuSectionQuote_id as \"LpuSectionQuote_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    lpusectionquote_id := :LpuSectionQuote_id,
			    lpusectionprofile_id := :LpuSectionProfile_id,
			    lpuunittype_id := :LpuUnitType_id,
			    lpusectionquote_count := :LpuSectionQuote_Count,
			    lpusectionquote_year := :LpuSectionQuote_Year,
			    lpu_id := :Lpu_id,
			    lpusectionquote_begdate := :LpuSectionQuote_begDate,
			    lpusectionquote_fact := :LpuSectionQuote_Fact,
			    paytype_id := :PayType_id,
			    orgsmo_id := :OrgSMO_id,
			    quoteunittype_id := :QuoteUnitType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionQuote_id" => $data["LpuSectionQuote_id"],
			"LpuSectionProfile_id" => $data["LpuSectionProfile_id"],
			"LpuUnitType_id" => $data["LpuUnitType_id"],
			"LpuSectionQuote_Count" => $data["LpuSectionQuote_Count"],
			"LpuSectionQuote_Year" => $data["LpuSectionQuote_Year"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuSectionQuote_begDate" => $data["LpuSectionQuote_begDate"],
			"LpuSectionQuote_Fact" => $data["LpuSectionQuote_Fact"],
			"PayType_id" => $data["PayType_id"],
			"OrgSMO_id" => $data["OrgSMO_id"],
			"QuoteUnitType_id" => $data["QuoteUnitType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при сохранении планирования МО!");
		}
		$response = $result->result('array');
		if (!is_array($response) || count($response) == 0) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при сохранении планирования МО!");
		}
		$callObject->commitTransaction();
		return $response;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function SaveLpuBuildingStreet(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["LpuBuildingStreet_id"])) {
			$street = $callObject->getKLStreetById($data["KLStreet_id"]);
			if (!empty($street)) {
				$query = "
					select
						KLHouse_id as \"KLHouse_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_klhouse_ins(
					    klhouse_id := :KLHouse_id,
					    klstreet_id := :KLStreet_id,
					    klsocr_id := :KLSocr_id,
					    klhouse_name := :KLHouse_Name,
					    klhouse_corpus := :KLHouse_Corpus,
					    kladr_code := :KLAdr_Code,
					    kladr_index := :KLAdr_Index,
					    kladr_gninmb := :KLAdr_Gninmb,
					    kladr_uno := :KLAdr_Uno,
					    kladr_ocatd := :KLAdr_Ocatd,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"KLHouse_id" => null,
					"KLStreet_id" => $data["KLStreet_id"],
					"KLHouse_Name" => $data["LpuBuildingStreet_HouseSet"],
					"KLSocr_id" => "78",
					"KLHouse_Corpus" => null,
					"KLAdr_Code" => $street["KLAdr_Code"],
					"KLAdr_Index" => $street["KLAdr_Index"],
					"KLAdr_Gninmb" => $street["KLAdr_Gninmb"],
					"KLAdr_Uno" => $street["KLAdr_Uno"],
					"KLAdr_Ocatd" => $street["KLAdr_Ocatd"],
					"pmUser_id" => $data["pmUser_id"]
				];
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$resHouse = $result->result("array");
				$KLHouse_id = $resHouse[0]["KLHouse_id"];
				$KLArea_id = ($data["KLCity_id"] > 0) ? $data["KLCity_id"] : (($data["KLTown_id"] > 0) ? $data["KLTown_id"] : null);

				$query = "
					select
						KLHouseCoords_id as \"KLHouseCoords_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					rom p_klhousecoords_ins(
					    klhousecoords_id := :KLHouseCoords_id,
					    klarea_id := :KLArea_id,
					    klstreet_id := :KLStreet_id,
					    klhouse_id := :KLHouse_id,
					    klhousecoords_name := :KLHouseCoords_Name,
					    klhousecoords_latlng := :KLHouseCoords_LatLng,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"KLHouseCoords_id" => null,
					"KLHouse_id" => $KLHouse_id,
					"KLStreet_id" => $data["KLStreet_id"],
					"KLArea_id" => $KLArea_id,
					"KLHouseCoords_Name" => $data["LpuBuildingStreet_HouseSet"],
					"KLHouseCoords_LatLng" => "0",
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $callObject->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$resHouseCoords = $result->result("array");
				$KLHouseCoords_id = $resHouseCoords[0]["KLHouseCoords_id"];
				$query = "
					select
						LpuBuildingKLHouseCoordsRel_id as \"LpuBuildingKLHouseCoordsRel_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_lpubuildingklhousecoordsrel_ins(
					    lpubuildingklhousecoordsrel_id := :LpuBuildingKLHouseCoordsRel_id,
					    lpubuilding_id := :LpuBuilding_id,
					    klhousecoords_id := :KLHouseCoords_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"LpuBuildingKLHouseCoordsRel_id" => null,
					"LpuBuilding_id" => $data["LpuBuilding_id"],
					"KLHouseCoords_id" => $KLHouseCoords_id,
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $callObject->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				return $result->result("array");
			}
		} else {
			$query = "
				select
					KHC.KLHouse_id as \"KLHouse_id\",
					REL.LpuBuilding_id as \"LpuBuilding_id\"
				from
					LpuBuildingKLHouseCoordsRel REL 
					inner join KLHouseCoords KHC on KHC.KLHouseCoords_id = REL.KLHouseCoords_id
				where REL.LpuBuilding_id = :LpuBuilding_id
				  and REL.LpuBuildingKLHouseCoordsRel_id = :Rel_id
			";
			$queryParams = [
				"LpuBuilding_id" => $data["LpuBuilding_id"],
				"Rel_id" => $data["LpuBuildingStreet_id"]
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$house_id = $result->result("array");
			$house_id = $house_id[0]["KLHouse_id"];
			if ($house_id > 0) {
				// Если по старому
				$query = "
					select 
						'{$data['LpuBuildingStreet_id']}' as \"LpuBuildingStreet_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_klhousename_upd(
					    klhouse_id := :KLHouse_id,
					    klhouse_name := :KLHouse_Name,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"KLHouse_id" => $house_id,
					"KLHouse_Name" => $data["LpuBuildingStreet_HouseSet"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $callObject->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				return $result->result("array");
			} else {
				//Если по новому работаем по спаршенному Виалон-у
				$query = "
						select KLHouseCoords_id as \"KLHouseCoords_id\"
						from LpuBuildingKLHouseCoordsRel 
						where LpuBuildingKLHouseCoordsRel_id = {$queryParams['Rel_id']}
				";
				$result = $callObject->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$housecoords_id = $result->result("array");
				$housecoords_id = $housecoords_id[0]["KLHouseCoords_id"];
				$query = "
						update KLHouseCoords
						set
							KLHouseCoords_Name = :KLHouseCoords_Name,
							pmUser_updID = :pmUser_id,
							KLHouseCoords_updDT = getdate()
						where KLHouseCoords_id = :KLHouseCoords_id;
				";
				$queryParams = [
					"KLHouseCoords_id" => $housecoords_id,
					"KLHouseCoords_Name" => $data["LpuBuildingStreet_HouseSet"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$callObject->db->query($query, $queryParams);
				$result = $callObject->db->query("select '{$data['LpuBuildingStreet_id']}' as \"LpuBuildingStreet_id\", null as Error_Code, null as Error_Msg;");
				return $result->result("array");
			}
		}
		return false;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return mixed
	 */
	public static function SaveMedServiceStreet(LpuStructure_model $callObject, $data)
	{
		if (isset($data["MedServiceStreet_id"])) {
			$callObject->load->database();
			$callObject->load->model("Utils_model", "umodel", true);
			$callObject->umodel->ObjectRecordDelete($data, "MedServiceKLHouseCoordsRel", true, $data["MedServiceStreet_id"]);
		}
		$res = [];
		$data["MedServiceStreet_isAll"] = (!empty($data["MedServiceStreet_isAll"]) && ($data["MedServiceStreet_isAll"] == "on" || $data["MedServiceStreet_isAll"] === "true")) ? 2 : 1;
		$data["MedServiceStreet_HouseSet"] = $data["MedServiceStreet_HouseSet"] ?: "";
		if (!empty($data["KLStreet_id"])) {
			$query = "
				select
					ST.KLAdr_Code as \"KLAdr_Code\",
					ST.KLAdr_Index as \"KLAdr_Index\",
					ST.KLAdr_Gninmb as \"KLAdr_Gninmb\",
					ST.KLAdr_Uno as \"KLAdr_Uno\",
					ST.KLAdr_Ocatd as \"KLAdr_Ocatd\"
				from KLStreet ST 
				where ST.KLStreet_id = :KLStreet_id
			";
			$queryParams = [
				"KLStreet_id" => $data["KLStreet_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$res = $result->result("array");
			}
		}
		$query = "
			select
				KLHouse_id as \"KLHouse_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_klhouse_ins(
			    klhouse_id := :KLHouse_id,
			    klstreet_id := :KLStreet_id,
			    klsocr_id := :KLSocr_id,
			    klhouse_name := :KLHouse_Name,
			    klhouse_corpus := :KLHouse_Corpus,
			    kladr_code := :KLAdr_Code,
			    kladr_index := :KLAdr_Index,
			    kladr_gninmb := :KLAdr_Gninmb,
			    kladr_uno := :KLAdr_Uno,
			    kladr_ocatd := :KLAdr_Ocatd,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"KLHouse_id" => null,
			"KLStreet_id" => !empty($data["KLStreet_id"]) ? $data["KLStreet_id"] : null,
			"KLHouse_Name" => $data["MedServiceStreet_HouseSet"],
			"KLSocr_id" => "78",
			"KLHouse_Corpus" => null,
			"KLAdr_Code" => !empty($res[0]["KLAdr_Code"]) ? $res[0]["KLAdr_Code"] : null,
			"KLAdr_Index" => !empty($res[0]["KLAdr_Index"]) ? $res[0]["KLAdr_Index"] : null,
			"KLAdr_Gninmb" => !empty($res[0]["KLAdr_Gninmb"]) ? $res[0]["KLAdr_Gninmb"] : null,
			"KLAdr_Uno" => !empty($res[0]["KLAdr_Uno"]) ? $res[0]["KLAdr_Uno"] : null,
			"KLAdr_Ocatd" => !empty($res[0]["KLAdr_Ocatd"]) ? $res[0]["KLAdr_Ocatd"] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resHouse = $result->result("array");
		$KLHouse_id = $resHouse[0]["KLHouse_id"];
		$KLArea_id = ($data["KLTown_id"] > 0) ? $data["KLTown_id"] : (($data["KLCity_id"] > 0) ? $data["KLCity_id"] : null);
		$query = "
			select
				KLHouseCoords_id as \"KLHouseCoords_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_klhousecoords_ins(
			    klhousecoords_id := :KLHouseCoords_id,
			    klarea_id := :KLArea_id,
			    klstreet_id := :KLStreet_id,
			    klhouse_id := :KLHouse_id,
			    klhousecoords_name := :KLHouseCoords_Name,
			    klhousecoords_latlng := :KLHouseCoords_LatLng,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"KLHouseCoords_id" => null,
			"KLHouse_id" => $KLHouse_id,
			"KLStreet_id" => !empty($data["KLStreet_id"]) ? $data["KLStreet_id"] : null,
			"KLArea_id" => $KLArea_id,
			"KLHouseCoords_Name" => $data["MedServiceStreet_HouseSet"],
			"KLHouseCoords_LatLng" => "0",
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resHouseCoords = $result->result("array");
		$KLHouseCoords_id = $resHouseCoords[0]["KLHouseCoords_id"];
		$query = "
			select 
				MedServiceKLHouseCoordsRel_id as \"MedServiceKLHouseCoordsRel_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_medserviceklhousecoordsrel_ins(
			    medserviceklhousecoordsrel_id := :MedServiceKLHouseCoordsRel_id,
			    medservice_id := :MedService_id,
			    klhousecoords_id := :KLHouseCoords_id,
			    medservicestreet_isall := :MedServiceStreet_isAll,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"MedServiceKLHouseCoordsRel_id" => null,
			"MedService_id" => $data["MedService_id"],
			"KLHouseCoords_id" => $KLHouseCoords_id,
			"MedServiceStreet_isAll" => $data["MedServiceStreet_isAll"],
			"pmUser_id" => $data["pmUser_id"]
		];
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
	public static function SaveLpuRegionStreet(LpuStructure_model $callObject, $data)
	{
		$is_all = !empty($data['LpuRegionStreet_IsAll']) && ($data['LpuRegionStreet_IsAll'] == $callObject::YES_ID || $data['LpuRegionStreet_IsAll'] == $callObject::CHECKBOX_VAL) ? true : false;
		$proc = (!isset($data["LpuRegionStreet_id"])) ? "p_LpuRegionStreet_ins" : "p_LpuRegionStreet_upd";
		$selectString = "
			LpuRegionStreet_id as \"LpuRegionStreet_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    server_id := :Server_id,
			    lpuregionstreet_id := :LpuRegionStreet_id,
			    lpuregion_id := :LpuRegion_id,
			    klcountry_id := :KLCountry_id,
			    klrgn_id := :KLRGN_id,
			    klsubrgn_id := :KLSubRGN_id,
			    klcity_id := :KLCity_id,
			    kltown_id := :KLTown_id,
			    klstreet_id := :KLStreet_id,
			    lpuregionstreet_houseset := :LpuRegionStreet_HouseSet,
			    lpuregionstreet_isall := :LpuRegionStreet_IsAll,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuRegionStreet_id" => $data["LpuRegionStreet_id"],
			"Server_id" => $data["Server_id"],
			"LpuRegion_id" => $data["LpuRegion_id"],
			"KLCountry_id" => $data["KLCountry_id"],
			"KLRGN_id" => $data["KLRGN_id"],
			"KLSubRGN_id" => $data["KLSubRGN_id"],
			"KLCity_id" => $data["KLCity_id"],
			"KLTown_id" => $data["KLTown_id"],
			"KLStreet_id" => $data["KLStreet_id"],
			"LpuRegionStreet_HouseSet" => $data["LpuRegionStreet_HouseSet"],
			"LpuRegionStreet_IsAll" => $is_all ? $callObject::YES_ID : $callObject::NO_ID,
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
	 * @return array|mixed
	 * @throws Exception
	 */
	public static function SaveLpuSectionWard(LpuStructure_model $callObject, $data)
	{
		$callObject->beginTransaction();
		$data["LpuSectionWard_BedCount"] = $data["LpuSectionWard_MainPlace"] + (empty($data["LpuSectionWard_DopPlace"]) ? 0 : $data["LpuSectionWard_DopPlace"]);
		$procedure = (!empty($data['LpuSectionWard_id'])) ? "p_LpuSectionWard_upd" : "p_LpuSectionWard_ins";
		$selectString = "
			LpuSectionWard_id as \"LpuSectionWard_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			   server_id := CAST(:Server_id as bigint),
               lpusectionward_id := CAST(:LpuSectionWard_id as bigint),
               lpusection_id := CAST(:LpuSection_id as bigint),
               lpusectionward_name := CAST(:LpuSectionWard_Name as varchar),
               LpuSectionWard_Floor := :LpuSectionWard_Floor,
               lpuwardtype_id := CAST(:LpuWardType_id as bigint),
               lpusectionward_bedcount := CAST(:LpuSectionWard_BedCount as integer),
               lpusectionward_bedrepair := CAST(:LpuSectionWard_BedRepair as integer),
               lpusectionward_daycost := CAST(:LpuSectionWard_DayCost as numeric),
               lpusectionward_setdate := CAST(:LpuSectionWard_setDate as timestamp),
               lpusectionward_disdate := CAST(:LpuSectionWard_disDate as timestamp),
               sex_id := CAST(:Sex_id as bigint),
               lpusectionward_countroom := CAST(:LpuSectionWard_CountRoom as bigint),
               lpusectionward_dopplace := CAST(:LpuSectionWard_DopPlace as bigint),
               lpusectionward_square := CAST(:LpuSectionWard_Square as numeric),
               lpusectionward_views := CAST(:LpuSectionWard_Views as varchar),
               lpusectionward_mainplace := CAST(:LpuSectionWard_MainPlace as integer),
               pmuser_id := CAST(:pmUser_id as bigint)
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение палаты)");
		}
		$queryResponse = $result->result("array");
		if (!is_array($queryResponse)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при сохранение палаты");
		} else if (!empty($queryResponse[0]["Error_Msg"])) {
			$callObject->rollbackTransaction();
			return $queryResponse;
		}
		$response = $queryResponse[0];
		// Обрабатываем список бъектов комфортности
		if (!empty($data["DChamberComfortData"])) {
			$DChamberComfortData = json_decode($data["DChamberComfortData"], true);
			if (is_array($DChamberComfortData)) {
				$isDChamberRepeat = [];
				for ($i = 0; $i < count($DChamberComfortData); $i++) {
					$DChamberComfort = [
						"pmUser_id" => $data["pmUser_id"],
						"LpuSectionWard_id" => $response["LpuSectionWard_id"]
					];
					if (empty($DChamberComfortData[$i]["LpuSectionWardComfortLink_id"]) || !is_numeric($DChamberComfortData[$i]["LpuSectionWardComfortLink_id"])) {
						continue;
					}
					if (empty($DChamberComfortData[$i]["DChamberComfort_id"]) || !is_numeric($DChamberComfortData[$i]["DChamberComfort_id"])) {
						continue;
					}
					if (empty($DChamberComfortData[$i]["LpuSectionWardComfortLink_Count"]) || !is_numeric($DChamberComfortData[$i]["LpuSectionWardComfortLink_Count"])) {
						continue;
					}
					$DChamberComfort["LpuSectionWardComfortLink_id"] = $DChamberComfortData[$i]["LpuSectionWardComfortLink_id"];
					$DChamberComfort["DChamberComfort_id"] = $DChamberComfortData[$i]["DChamberComfort_id"];
					$DChamberComfort["LpuSectionWardComfortLink_Count"] = $DChamberComfortData[$i]["LpuSectionWardComfortLink_Count"];

					$queryResponse = $callObject->saveLpuSectionWardComfortLink($DChamberComfort);
					if (!is_array($queryResponse)) {
						$callObject->rollbackTransaction();
						throw new Exception("Ошибка при " . ($DChamberComfortData[$i]["RecordStatus_Code"] == 3 ? "удалении" : "сохранении") . " объекта комфортности");
					} else if (!empty($queryResponse[0]["Error_Msg"])) {
						$callObject->rollbackTransaction();
						return $queryResponse[0];
					}
					if (in_array($DChamberComfortData[$i]['DChamberComfort_id'], $isDChamberRepeat)) {
						//В одной палате не может быть несколько объектов комфортности с одинаковыми наименованиями
						$callObject->rollbackTransaction();
						throw new Exception("Нельзя сохранить объекты комфортности с одинаковым наименованием.");
					} else {
						array_push($isDChamberRepeat, $DChamberComfortData[$i]["DChamberComfort_id"]);
					}
				}
			}
		}
		$callObject->commitTransaction();
		// Удаляем данные из кэша
		$callObject->load->library("swCache");
		$callObject->swcache->clear("LpuSectionWardList_" . $data["Lpu_id"]);
		return array($response);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveLpuSectionWardComfortLink(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["LpuSectionWard_id"])) {
			return [['LpuSectionWard_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Для добавления операции необходим идентификатор палаты.']];
		}

		$sp = $callObject->getLpuSectionWardByIdData($data);
		if ($sp && isset($sp[0]["Lpu_id"]) && isset($data["Lpu_id"]) && $data["Lpu_id"] != $sp[0]["Lpu_id"]) {
			return [[
				'Error_Code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			]];
		}
		if (!isset($data["LpuSectionWardComfortLink_Code"])) {
			$data["LpuSectionWardComfortLink_Code"] = 1;
		}
		$procedure = (isset($data['LpuSectionWardComfortLink_id']) && $data['LpuSectionWardComfortLink_id'] > 0) ? "fed.p_LpuSectionWardComfortLink_upd" : "fed.p_LpuSectionWardComfortLink_ins";
		$idString = ($data['LpuSectionWardComfortLink_id'] > 0) ? ":LpuSectionWardComfortLink_id" : "null";
		$selectString = "
			LpuSectionWardComfortLink_id as \"LpuSectionWardComfortLink_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    lpusectionwardcomfortlink_id := {$idString},
			    lpusectionward_id := :LpuSectionWard_id,
			    dchambercomfort_id := :DChamberComfort_id,
			    lpusectionwardcomfortlink_code := :LpuSectionWardComfortLink_Code,
			    lpusectionwardcomfortlink_count := :LpuSectionWardComfortLink_Count,
			    pmuser_id := :pmUser_id
			);
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
	 * @throws Exception
	 */
	public static function saveDBedOperation(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["LpuSectionBedState_id"])) {
			return [['LpuSectionBedState_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Для добавления операции необходим идентификатор койки.']];
		}
		if (!isset($data["LpuSectionBedStateOper_Code"])) {
			$data["LpuSectionBedStateOper_Code"] = 1;
		}
		$data["LpuSectionBedStateOper_OperDT"] = date_create($data["LpuSectionBedStateOper_OperDT"]);
		$queryParams = [
			"LpuSectionBedStateOper_id" => $data["LpuSectionBedStateOper_id"],
			"LpuSectionBedState_id" => $data["LpuSectionBedState_id"],
			"DBedOperation_id" => $data["DBedOperation_id"],
			"LpuSectionBedStateOper_Code" => $data["LpuSectionBedStateOper_Code"],
			"LpuSectionBedStateOper_OperDT" => $data["LpuSectionBedStateOper_OperDT"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$procedure = (isset($data["LpuSectionBedStateOper_id"]) && $data["LpuSectionBedStateOper_id"] > 0) ? "fed.p_LpuSectionBedStateOper_upd" : "fed.p_LpuSectionBedStateOper_ins";
		$idString = ($data["LpuSectionBedStateOper_id"] > 0) ? ":LpuSectionBedStateOper_id" : "null";
		$selectString = "
			LpuSectionBedStateOper_id as \"LpuSectionBedStateOper_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    lpusectionbedstateoper_id := {$idString},
			    lpusectionbedstate_id := :LpuSectionBedState_id,
			    dbedoperation_id := :DBedOperation_id,
			    lpusectionbedstateoper_operdt := :LpuSectionBedStateOper_OperDT,
			    lpusectionbedstateoper_code := :LpuSectionBedStateOper_Code,
			    pmuser_id := :pmUser_id
			);
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
	public static function saveStaffOSMGridDetail(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["Staff_Code"])) {
			//Неепонятный ненужный параметр
			$data["Staff_Code"] = 1;
		}
		if (!isset($data["Staff_Name"])) {
			//Неепонятный ненужный параметр
			$data["Staff_Name"] = 1;
		}
		$queryParams = [
			"Staff_id" => $data["Staff_id"],
			"Staff_Num" => $data["Staff_Num"],
			"Staff_OrgName" => $data["Staff_OrgName"],
			"Staff_OrgDT" => $data["Staff_OrgDT"],
			"Staff_OrgBasis" => $data["Staff_OrgBasis"],
			"Staff_Code" => $data["Staff_Code"],
			"Staff_Name" => $data["Staff_Name"],
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = "
            select COUNT (*) as count
            from fed.v_Staff 
            where
                Staff_Num = :Staff_Num 
            and
                Staff_OrgName = :Staff_OrgName 
            and
                Staff_OrgDT = :Staff_OrgDT 
            and
                Staff_id != coalesce(:Staff_id::bigint, 0) 
            and
				Lpu_id = :Lpu_id
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		$response = $result->result("array");
		if ($response[0]["count"] > 0) {
			throw new Exception("Запись с введенными данными уже существует.");
		}
		$procedure = (isset($data["Staff_id"])) ? "fed.p_Staff_upd" : "fed.p_Staff_ins";
		$selectString = "
			Staff_id as \"Staff_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}
			(
			    staff_id := :Staff_id,
			    staff_code := :Staff_Code,
			    staff_name := :Staff_Name::varchar,
			    staff_num := :Staff_Num,
			    staff_orgname := :Staff_OrgName,
			    staff_orgdt := :Staff_OrgDT,
			    staff_orgbasis := :Staff_OrgBasis,
			    lpu_id := :Lpu_id,
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
	 * @return array
	 */
	public static function saveLpuSectionComment(LpuStructure_model $callObject, $data)
	{
		$sql = "
			update LpuSection
			set
				LpuSection_Descr = :LpuSection_Descr,
				LpuSection_updDT = tzgetdate(),
				pmUser_updID = :pmUser_id
			where LpuSection_id = :LpuSection_id
		";
		$sqlParams = [
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSection_Descr" => $data["LpuSection_Descr"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->db->query($sql, $sqlParams);
		return [
			0 => ["Error_Msg" => ""]
		];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveSectionAverageDuration(LpuStructure_model $callObject, $data)
	{
		$procedure = (!empty($data["SectionAverageDuration_id"])) ? "p_SectionAverageDuration_upd" : "p_SectionAverageDuration_ins";
		$selectString = "
			SectionAverageDuration_id as \"SectionAverageDuration_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from r10.{$procedure}(
			    sectionaverageduration_id := :SectionAverageDuration_id,
			    sectionaverageduration_duration := :SectionAverageDuration_Duration,
			    sectionaverageduration_begdate := :SectionAverageDuration_begDate,
			    sectionaverageduration_enddate := :SectionAverageDuration_endDate,
			    lpusection_id := :LpuSection_id,
			    pmuser_id := :pmUser_id
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
