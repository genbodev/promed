<?php

class Drug_model_save
{
	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveDrugMnnLatinName(Drug_model $callObject, $data)
	{
		$queryParams = [
			"DrugMnn_id" => $data["DrugMnn_id"],
			"DrugMnn_NameLat" => $data["DrugMnn_NameLat"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = "
			select
			    drugmnn_id as \"DrugMnn_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugmnnlat_upd(
			    drugmnn_id := :DrugMnn_id,
			    drugmnn_namelat := :DrugMnn_NameLat,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result("array");
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveDrugTorgLatinName(Drug_model $callObject, $data)
	{
		$queryParams = [
			"DrugTorg_id" => $data["DrugTorg_id"],
			"DrugTorg_NameLat" => $data["DrugTorg_NameLat"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = "
			select
			    drugtorg_id as \"DrugTorg_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugtorglat_upd(
			    drugtorg_id := :DrugTorg_id,
			    drugtorg_namelat := :DrugTorg_NameLat,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result("array");
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveDrugState(Drug_model $callObject, $data)
	{
		$queryParams = [
			"DrugState_id" => $data["DrugState_id"],
			"DrugRequestPeriod_id" => $data["DrugRequestPeriod_id"],
			"ReceptFinance_id" => $data["ReceptFinance_id"],
			"DrugProto_id" => $data["DrugProto_id"],
			"DrugProtoMnn_id" => $data["DrugProtoMnn_id"],
			"Drug_id" => $data["Drug_id"],
			"DrugState_Price" => $data["DrugState_Price"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$procedure = (!isset($data["DrugState_id"]) || $data["DrugState_id"] <= 0) ? "p_DrugState_ins" : "p_DrugState_upd";
		if (!isset($data["DrugState_id"]) || $data["DrugState_id"] <= 0) {
			$queryParams["DrugState_id"] = null;
		}
		$selectString = "
		    drugstate_id as \"DrugState_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    drugstate_id := :DrugState_id,
			    drug_id := :Drug_id,
			    drugproto_id := :DrugProto_id,
			    drugprotomnn_id := :DrugProtoMnn_id,
			    drugstate_price := :DrugState_Price,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result("array");
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveLpuBuildingLinkDataFromJSON(Drug_model $callObject, $data)
	{
		$result = [];
		try {
			$callObject->beginTransaction();
			foreach ($data["LinkDataJSON"] as $record) {
				$ofi_add_list = [];
				$ofi_del_list = [];
				//получение данных о текущих прикреплениях подраздаеления МО к аптеке
				$query = "
					select
						ofi.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
						ofi.OrgFarmacyIndex_IsNarko as \"OrgFarmacyIndex_IsNarko\"
					from v_OrgFarmacyIndex ofi
					where ofi.Lpu_id = :Lpu_id
					  and ofi.OrgFarmacy_id = :OrgFarmacy_id
					  and coalesce(ofi.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
					  and ofi.LpuBuilding_id = :LpuBuilding_id
				";
				$queryParams = [
					"Lpu_id" => $data["Lpu_id"],
					"OrgFarmacy_id" => $data["OrgFarmacy_id"],
					"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"],
					"LpuBuilding_id" => $record->LpuBuilding_id
				];
				$ofi_list = $callObject->queryResult($query, $queryParams);
				$isnarko_list = [];
				switch ($record->state) {
					case "add":
					case "edit":
						switch ($record->LsGroup_id) {
							case 1:
								//Все ЛП
								$isnarko_list = [1, 2];
								break;
							case 2:
								//Все кроме НС и ПВ
								$isnarko_list = [1];
								break;
							case 3:
								//НС и ПВ
								$isnarko_list = [2];
								break;
						}
						break;
					case "delete":
						$isnarko_list = [];
						break;
				}
				//ищем лишние записи
				foreach ($ofi_list as $ofi_data) {
					if (!in_array($ofi_data["OrgFarmacyIndex_IsNarko"], $isnarko_list)) {
						$ofi_del_list[] = $ofi_data["OrgFarmacyIndex_id"];
					}
				}
				//ищем отсутствующие записи
				foreach ($isnarko_list as $isnarko_id) {
					$id_exists = false;
					foreach ($ofi_list as $ofi_data) {
						if ($ofi_data["OrgFarmacyIndex_IsNarko"] == $isnarko_id) {
							$id_exists = true;
						}
					}
					if (!$id_exists) {
						$ofi_add_list[] = $isnarko_id;
					}
				}
				//добавление записей
				if (is_array($ofi_add_list) && count($ofi_add_list)) {
					foreach ($ofi_add_list as $ofi_isnarko_id) {
						//проверка наличия прикрепления с данными параметрами к другой аптеке
						$save_result = $callObject->saveObject("OrgFarmacyIndex", [
							"Server_id" => $callObject->sessionParams["server_id"],
							"Lpu_id" => $data["Lpu_id"],
							"OrgFarmacy_id" => $data["OrgFarmacy_id"],
							"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"],
							"LpuBuilding_id" => $record->LpuBuilding_id,
							"OrgFarmacyIndex_IsNarko" => $ofi_isnarko_id,
							"OrgFarmacyIndex_IsEnabled" => 1,
							"OrgFarmacyIndex_Index" => '0',
							"Storage_id" => null
						]);
						if (empty($save_result["OrgFarmacyIndex_id"])) {
							throw new Exception(!empty($save_result["Error_Msg"]) ? $save_result["Error_Msg"] : "При сохранении данных о прикреплении произошла ошибка");
						}
					}
				}
				//удаление записей
				if (is_array($ofi_del_list) && count($ofi_del_list)) {
					foreach ($ofi_del_list as $ofi_id) {
						$query = "
							select
							    error_code as \"Error_Code\",
							    error_message as \"Error_Msg\"
							from p_orgfarmacyindex_del(
							    orgfarmacyindex_id := :OrgFarmacyIndex_id,
							    isremove := :IsRemove
							);
						";
						$queryParams = [
							"OrgFarmacyIndex_id" => $ofi_id,
							"IsRemove" => 2
						];
						$delete_result = $callObject->getFirstRowFromQuery($query, $queryParams);
						if (!$delete_result || !is_array($delete_result)) {
							throw new Exception("При удалении произошла ошибка");
						}
						if (!empty($delete_result["Error_Msg"])) {
							throw new Exception($delete_result["Error_Msg"]);
						}
					}
				}
			}
			$result["success"] = true;
			$callObject->commitTransaction();
		} catch (Exception $e) {
			$callObject->rollbackTransaction();
			throw new Exception($e->getMessage());
		}
		return $result;
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveLpuBuildingStorageLinkDataFromJSON(Drug_model $callObject, $data)
	{
		$result = [];
		$edited_data = [];
		//шаблон данных содержит часть неизменяемых данных
		$ofi_data = [
			"OrgFarmacyIndex_id" => null,
			"Server_id" => $callObject->sessionParams["server_id"],
			"Lpu_id" => $data["Lpu_id"],
			"OrgFarmacy_id" => $data["OrgFarmacy_id"],
			"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"],
			"LpuBuilding_id" => null,
			"OrgFarmacyIndex_IsNarko" => null,
			"OrgFarmacyIndex_Index" => 0,
			"OrgFarmacyIndex_IsEnabled" => 1,
			"Storage_id" => null
		];
		try {
			$callObject->beginTransaction();
			foreach ($data["LinkDataJSON"] as $record) {
				//по умолчанию действие определяется через state
				$action = $record->state;
				//определяем изменяемую часть данных
				$ofi_data["OrgFarmacyIndex_id"] = !empty($record->OrgFarmacyIndex_id) ? $record->OrgFarmacyIndex_id : null;
				$ofi_data["LpuBuilding_id"] = $record->LpuBuilding_id;
				$ofi_data["OrgFarmacyIndex_IsNarko"] = ($record->LsGroup_id == 1 || $record->LsGroup_id == 2) ? $record->LsGroup_id : null;
				$ofi_data["Storage_id"] = !empty($record->Storage_id) ? $record->Storage_id : null;
				//ищем среди данных уже существующую запись с заданными параметрами и исходя из этого определяем действие
				$query = "
					select count(ofi.OrgFarmacyIndex_id) as \"cnt\"
					from v_OrgFarmacyIndex ofi
					where ofi.Lpu_id = :Lpu_id
					  and ofi.OrgFarmacy_id = :OrgFarmacy_id
					  and coalesce(ofi.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
					  and coalesce(ofi.LpuBuilding_id, 0) = coalesce(:LpuBuilding_id, 0)
					  and coalesce(ofi.OrgFarmacyIndex_IsNarko, 0) = coalesce(:OrgFarmacyIndex_IsNarko, 0)
					  and coalesce(ofi.Storage_id, 0) = coalesce(:Storage_id, 0);
				";
				$ofi_cnt = $callObject->getFirstResultFromQuery($query, $ofi_data);
				if ($ofi_cnt > 0) {
					//если обнаружено дублирование
					if ($action == "add") {
						//при попытке добавления дубля, пропускаем запись
						$action = null;
					}
					if ($action == "edit") {
						//если редактирование записи приведет к дублированию, заменяем редактирование на удаление
						$action = "delete";
					}
				}
				switch ($action) {
					case "add":
						$ofi_data["OrgFarmacyIndex_id"] = null;
						break;
					case "edit":
						$save_result = $callObject->saveObject("OrgFarmacyIndex", $ofi_data);
						if (empty($save_result["OrgFarmacyIndex_id"])) {
							throw new Exception(!empty($save_result["Error_Msg"]) ? $save_result["Error_Msg"] : "При сохранении данных о прикреплении произошла ошибка");
						}
						break;
					case "delete":
						if (!empty($ofi_data["OrgFarmacyIndex_id"])) {
							$query = "
								select
								    error_code as \"Error_Code\",
								    error_message as \"Error_Msg\"
								from p_orgfarmacyindex_del(
								    orgfarmacyindex_id := :OrgFarmacyIndex_id,
								    isremove := :IsRemove
								);
							";
							$queryParams = [
								"OrgFarmacyIndex_id" => $ofi_data["OrgFarmacyIndex_id"],
								"IsRemove" => 2
							];
							$delete_result = $callObject->getFirstRowFromQuery($query, $queryParams);
							if (!$delete_result || !is_array($delete_result)) {
								throw new Exception("При удалении произошла ошибка");
							}
							if (!empty($delete_result["Error_Msg"])) {
								throw new Exception($delete_result["Error_Msg"]);
							}
						}
						break;
				}
				//сбор массива редактируемых наборов
				if (!empty($action)) {
					if (empty($edited_data[$ofi_data["LpuBuilding_id"]])) {
						$edited_data[$ofi_data["LpuBuilding_id"]] = [];
						$edited_data[$ofi_data["LpuBuilding_id"]][$record->LsGroup_id] = 1;
					}
				}
			}
			//контроль наличия дефолтной записи для каждого набора отредактированных данных
			foreach ($edited_data as $lb_id => $lsg_arr) {
				foreach ($lsg_arr as $lsg_id => $itm) {
					$ofi_data["LpuBuilding_id"] = $record->LpuBuilding_id;
					$is_narko_id = ($lsg_id == 1 || $lsg_id == 2) ? $lsg_id : null;
					//подсчет обычных и дефолтных записей для набора данных
					$query = "
						select
							sum(case when ofi.Storage_id is not null then 1 else 0 end) as \"stg_cnt\",
							sum(case when ofi.Storage_id is null then 1 else 0 end) as \"null_cnt\",
							max(case when ofi.Storage_id is null then ofi.OrgFarmacyIndex_id else 0 end) as \"max_null_id\"
						from v_OrgFarmacyIndex ofi
						where ofi.Lpu_id = :Lpu_id
						  and ofi.OrgFarmacy_id = :OrgFarmacy_id
						  and coalesce(ofi.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
						  and coalesce(ofi.LpuBuilding_id, 0) = coalesce(:LpuBuilding_id, 0)
						  and coalesce(ofi.OrgFarmacyIndex_IsNarko, 0) = coalesce(:OrgFarmacyIndex_IsNarko, 0)
					";
					$queryParams = [
						"Lpu_id" => $ofi_data["Lpu_id"],
						"OrgFarmacy_id" => $ofi_data["OrgFarmacy_id"],
						"WhsDocumentCostItemType_id" => $ofi_data["WhsDocumentCostItemType_id"],
						"LpuBuilding_id" => $lb_id,
						"OrgFarmacyIndex_IsNarko" => $is_narko_id
					];
					$cnt_data = $callObject->getFirstRowFromQuery($query, $queryParams);
					//корректировка
					if ($cnt_data['stg_cnt'] > 0) {
						if ($cnt_data['null_cnt'] > 0 && $cnt_data['max_null_id'] > 0) {
							//нужно удалить дефолтную запись (считаем что такая может быть только одна в пределах набора)
							$query = "
								select
								    error_code as \"Error_Code\",
								    error_message as \"Error_Msg\"
								from p_orgfarmacyindex_del(
								    orgfarmacyindex_id := :OrgFarmacyIndex_id,
								    isremove := :IsRemove
								);
							";
							$queryParams = [
								"OrgFarmacyIndex_id" => $ofi_data["OrgFarmacyIndex_id"],
								"IsRemove" => 2
							];
							$delete_result = $callObject->getFirstRowFromQuery($query, $queryParams);
							if (!$delete_result || !is_array($delete_result)) {
								throw new Exception("При удалении произошла ошибка");
							}
							if (!empty($delete_result["Error_Msg"])) {
								throw new Exception($delete_result["Error_Msg"]);
							}
						}
					} else {
						if ($cnt_data["null_cnt"] == 0) {
							//нужно добавить дефолтную запись
							$ofi_data["OrgFarmacyIndex_id"] = null;
							$ofi_data["LpuBuilding_id"] = $lb_id;
							$ofi_data["OrgFarmacyIndex_IsNarko"] = $is_narko_id;
							$ofi_data["Storage_id"] = null;

							$save_result = $callObject->saveObject("OrgFarmacyIndex", $ofi_data);
							if (empty($save_result["OrgFarmacyIndex_id"])) {
								throw new Exception(!empty($save_result["Error_Msg"]) ? $save_result["Error_Msg"] : "При сохранении данных о прикреплении произошла ошибка");
							}
						}
					}
				}
			}

			$result["success"] = true;
			$callObject->commitTransaction();
		} catch (Exception $e) {
			$result["successs"] = false;
			$result["Error_Msg"] = $e->getMessage();
			$callObject->rollbackTransaction();
		}
		return $result;
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveMoByFarmacy3(Drug_model $callObject, $data)
	{
		$arr_data = json_decode($data["arr"], 1);
		$xml = "<RD>";
		$pmUser = (isset($data["pmuser_id"])) ? $data["pmuser_id"] : "";
		foreach ($arr_data as $item) {
			$LpuBuilding_id = "";
			$Lpu_id = "";
			$OrgFarmacyIndex_id = "";
			$typeLs = "";
			$action = "";
			$OrgFarmacy_id = "";
			if (isset($item["LpuBuilding_id"])) {
				$LpuBuilding_id = $item["LpuBuilding_id"];
			}
			if (isset($item["Lpu_id"])) {
				$Lpu_id = $item["Lpu_id"];
			}
			if (isset($item["OrgFarmacyIndex_id"])) {
				$OrgFarmacyIndex_id = $item["OrgFarmacyIndex_id"];
			}
			if (isset($item["typeLs"])) {
				$typeLs = $item["typeLs"];
			}
			if (isset($item["action"])) {
				$action = $item["action"];
			}
			if (isset($item["OrgFarmacy_id"])) {
				$OrgFarmacy_id = $item["OrgFarmacy_id"];
			}
			$xml .= '<R|*|v1="' . $LpuBuilding_id . '" 
				 |*|v2="' . $Lpu_id . '"
				 |*|v3="' . $OrgFarmacyIndex_id . '"
				 |*|v4="' . $typeLs . '"
				 |*|v5="' . $action . '" 
				 |*|v6="' . $pmUser . '"
				 |*|v7="' . $OrgFarmacy_id . '" ></R>';

		}
		$xml .= "</RD>";
		$xml = strtr($xml, [PHP_EOL => "", " " => ""]);
		$xml = str_replace("|*|", " ", $xml);
		$params = ["xml" => (string)$xml];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from r2.p_savemobyfarmacy(xml := :xml);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return [["success" => true]];
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function saveReceptWrong(Drug_model $callObject, $data)
	{
		$callObject->beginTransaction();
		$callObject->load->model("Dlo_EvnRecept_model", "ermodel");
		$callObject->isAllowTransaction = false;
		$resp = $callObject->ermodel->deleteEvnReceptDrugOstReg($data);
		$callObject->isAllowTransaction = true;
		if (!$callObject->isSuccessful($resp)) {
			$callObject->rollbackTransaction();
			return $resp;
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_receptwrong_ins(
			    evnrecept_id := :EvnRecept_id,
			    orgfarmacy_id := :OrgFarmacy_id,
			    org_id := :Org_id,
			    receptwrong_decr := :ReceptWrong_decr,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"ReceptWrong_id" => !empty($data["ReceptWrong_id"]) ? $data["ReceptWrong_id"] : null,
			"EvnRecept_id" => $data["EvnRecept_id"],
			"OrgFarmacy_id" => !empty($data["OrgFarmacy_id"]) ? $data["OrgFarmacy_id"] : null,
			"Org_id" => $data["Org_id"],
			"ReceptWrong_decr" => $data["ReceptWrong_decr"],
			"pmUser_id" => $_SESSION["pmuser_id"]
		];
		$response = $callObject->queryResult($query, $queryParams);
		if (!is_array($response)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (Сохранение записи о признании рецепта недействительным)");
		}
		if (!empty($response[0]["Error_Msg"])) {
			$callObject->rollbackTransaction();
			return $response;
		}
		$callObject->commitTransaction();
		return $response;
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveMoByFarmacy(Drug_model $callObject, $data)
	{
		$arr_data = json_decode($data["arr"], 1);
		$xml = "<RD>";
		$pmUser = (isset($data["pmUser_id"])) ? $data["pmUser_id"] : "";
		foreach ($arr_data as $item) {
			$LpuBuilding_id = "";
			$Lpu_id = "";
			$OrgFarmacyIndex_id = "";
			$typeLs = "";
			$action = "";
			$OrgFarmacy_id = "";
			if (isset($item["LpuBuilding_id"])) {
				$LpuBuilding_id = $item["LpuBuilding_id"];
			}
			if (isset($item["Lpu_id"])) {
				$Lpu_id = $item["Lpu_id"];
			}
			if (isset($item["OrgFarmacyIndex_id"])) {
				$OrgFarmacyIndex_id = $item["OrgFarmacyIndex_id"];
			}
			if (isset($item["typeLs"])) {
				$typeLs = $item["typeLs"];
			}
			if (isset($item["action"])) {
				$action = $item["action"];
			}
			if (isset($item["OrgFarmacy_id"])) {
				$OrgFarmacy_id = $item["OrgFarmacy_id"];
			}
			$xml .= '<R|*|v1="' . $LpuBuilding_id . '" 
                 |*|v2="' . $Lpu_id . '"
                 |*|v3="' . $OrgFarmacyIndex_id . '"
                 |*|v4="' . $typeLs . '"
                 |*|v5="' . $action . '" 
                 |*|v6="' . $pmUser . '"
                 |*|v7="' . $OrgFarmacy_id . '" ></R>';;
		}
		$xml .= "</RD>";
		$xml = strtr($xml, [PHP_EOL => "", " " => ""]);
		$xml = str_replace("|*|", " ", $xml);
		$params = ["xml" => (string)$xml];
		$params['WhsDocumentCostItemType_id'] = null;
		if (isset($data['WhsDocumentCostItemType_id'])) {
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		$query = "
			Select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from r2.p_saveMoByFarmacy(:xml, :WhsDocumentCostItemType_id)  
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return [["success" => true]];
	}

	/**
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function deleteDrugState(Drug_model $callObject, $data)
	{
		if (!array_key_exists("DrugState_id", $data) || !$data["DrugState_id"]) {
			return false;
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugstate_del(drugstate_id := :DrugState_id);
		";
		$queryParams = ["DrugState_id" => $data["DrugState_id"]];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время удаления тарифа произошла ошибка.");
		}
		return $result->result("array");
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function deleteLpuBuildingLinkData(Drug_model $callObject, $data)
	{
		$result = [];
		try {
			$callObject->beginTransaction();
			//получение данных о текущих прикреплениях подраздаеления МО к аптеке
			$query = "
				select
					ofi.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
					ofi.OrgFarmacyIndex_IsNarko as \"OrgFarmacyIndex_IsNarko\"
				from v_OrgFarmacyIndex ofi
				where ofi.Lpu_id = :Lpu_id
				  and ofi.OrgFarmacy_id = :OrgFarmacy_id
				  and coalesce(ofi.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
				  and ofi.LpuBuilding_id is not null
			";
			$queryParams = [
				"Lpu_id" => $data["Lpu_id"],
				"OrgFarmacy_id" => $data["OrgFarmacy_id"],
				"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"]
			];
			$ofi_list = $callObject->queryResult($query, $queryParams);
			//удаление записей
			if (is_array($ofi_list) && count($ofi_list)) {
				foreach ($ofi_list as $ofi_data) {
					$query = "
						select
						    error_code as \"Error_Code\",
						    error_message as \"Error_Msg\"
						from p_orgfarmacyindex_del(
						    orgfarmacyindex_id := :OrgFarmacyIndex_id,
						    isremove := :IsRemove
						);
					";
					$delete_result = $callObject->getFirstRowFromQuery($query, [
						"OrgFarmacyIndex_id" => $ofi_data["OrgFarmacyIndex_id"],
						"IsRemove" => 2
					]);
					if (!$delete_result || !is_array($delete_result)) {
						throw new Exception("При удалении произошла ошибка");
					}
					if (!empty($delete_result["Error_Msg"])) {
						throw new Exception($delete_result["Error_Msg"]);
					}
				}
			}
			$result["success"] = true;
			$callObject->commitTransaction();
		} catch (Exception $e) {
			$callObject->rollbackTransaction();
			throw new Exception($e->getMessage());
		}
		return $result;
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkDrugOstatOnSklad(Drug_model $callObject, $data)
	{
		$queryParams = [
			"Drug_id" => $data["Drug_id"],
			"ReceptFinance_Code" => $data["ReceptFinance_Code"]
		];
		$query = "
			select
				case when coalesce(sum(DD.DrugOstat_Kolvo), 0) <= 0
				    then 0
					else sum(DD.DrugOstat_Kolvo)
				end as \"DrugOstat_Kolvo\"
			from
				v_DrugOstat DD
				inner join v_ReceptFinance RF on RF.ReceptFinance_id = DD.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
			where DD.OrgFarmacy_id = 1
			  and DD.Drug_id = :Drug_id
			group by DD.OrgFarmacy_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkOrgFarmacyDoubles(Drug_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"OrgFarmacy_id" => $data["OrgFarmacy_id"],
			"Server_id" => $data["Server_id"]
		];
		$query = "
			select OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\"
			from v_OrgFarmacyIndex
			where Lpu_id = :Lpu_id
			  and OrgFarmacy_id = :OrgFarmacy_id
			  and Server_id = :Server_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function orgFarmacyReplace(Drug_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"OrgFarmacy_id" => $data["OrgFarmacy_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$queryParams["OrgFarmacy_NewIndex"] = ($data["direction"] == "down") ? 1 : 0;
		$sql = "
			select 
			    OrgFarmacy_NewIndex as \"OrgFarmacy_NewIndex\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_orgfarmacyindex_setindex(
			    orgfarmacy_id := :OrgFarmacy_id,
			    lpu_id := :Lpu_id,
			    orgfarmacy_newindex := :OrgFarmacy_NewIndex,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function vklOrgFarmacy(Drug_model $callObject, $data)
	{
		$queryParams = [];
		/**@var CI_DB_result $result */
		if ($data["vkl"] == 1) {
			$dbl = $callObject->checkOrgFarmacyDoubles($data);
			if (is_array($dbl) && count($dbl)) {
				throw new Exception("Данная аптека уже включена");
			}
			$sql = "
				select max(OrgFarmacyIndex_Index) as \"max_index\"
				from OrgFarmacyIndex
				where Lpu_id = {$data['Lpu_id']}
			";

			$result = $callObject->db->query($sql);
			$selection = $result->result('array');
			
            $org_farmacy_index = 0;

			if ($selection[0]['max_index'] >= 0) {
				$org_farmacy_index = $selection[0]['max_index'] + 1;
			}			
			
			$sql = "
				select
				    orgfarmacyindex_id as \"OrgFarmacyIndex_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_orgfarmacyindex_ins(
				    server_id := :Server_id,
				    orgfarmacy_id := :OrgFarmacy_id,
				    lpu_id := :Lpu_id,
				    orgfarmacyindex_index := :OrgFarmacyIndex_Index,
				    orgfarmacyindex_isenabled := 1,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
			$queryParams["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
			$queryParams["OrgFarmacyIndex_Index"] = $org_farmacy_index;
			$queryParams["pmUser_id"] = $data["pmUser_id"];
			$queryParams["Server_id"] = $data["Server_id"];
		} else {
			$sql = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_orgfarmacyindex_del(
				    orgfarmacyindex_id := :OrgFarmacyIndex_id,
				    isremove := 2
				);
			";
			$queryParams["OrgFarmacyIndex_id"] = $data["OrgFarmacyIndex_id"];
		}
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$selection = $result->result("array");
		if ($data["vkl"] == 1) {
			$selection[0]["OrgFarmacyIndex_Index"] = $org_farmacy_index;
		}
		return $selection;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public static function SearchDrugRlsList(Drug_model $callObject, $data)
	{
		$queryParams = ["query" => $data["query"] . "%"];
		$query = "
			select
                D.Drug_id as \"Drug_id\",
                D.Drug_Name as \"Drug_Name\",
                D.Drug_Code as \"Drug_Code\"
            from rls.v_Drug D
			where D.Drug_Name iLIKE :query
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Поиск МНН по всему справочнику без учета даты
	 * Используется в фильтре в окне поиске рецепта
	 * @param $data
	 * @return array|bool
	 */
	public static function searchFullDrugMnnList(Drug_model $callObject, $data)
	{
		$queryParams = [];
		if ($data["DrugMnn_id"] > 0) {
			$queryParams["DrugMnn_id"] = $data["DrugMnn_id"];
			$where = "DrugMnn.DrugMnn_id = :DrugMnn_id";
		} else {
			$queryParams["query"] = $data["query"] . "%";
			$where = "DrugMnn.DrugMnn_Name iLIKE :query";
		}
		$query = "
            SELECT * FROM (
			select distinct
				DrugMnn.DrugMnn_id as \"DrugMnn_id\",
				DrugMnn.DrugMnn_Code as \"DrugMnn_Code\",
				rtrim(DrugMnn.DrugMnn_Name) as \"DrugMnn_Name\"
			from v_DrugMnn DrugMnn
			where {$where}
			) t
			order by \"DrugMnn_Name\"
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Поиск медикаментов по всему справочнику
	 * Используется в фильтре в окне поиске рецепта
	 */
	public static function searchFullDrugList(Drug_model $callObject, $data)
	{
		$queryParams = ["ReceptFinance_Code" => $data["ReceptFinance_Code"]];
		$whereArray = [];
		if ($data["Drug_id"] > 0) {
			$queryParams["Drug_id"] = $data["Drug_id"];
			$whereArray[] = "Drug.Drug_id = :Drug_id";
			if ($data["ReceptFinance_Code"] == 3) {
				$data["ReceptFinance_Code"] = 1;
			}
		} else {
			if (strlen($data["query"]) > 0) {
				$queryParams["query"] = $data["query"] . "%";
				$whereArray[] = "Drug.Drug_Name iLIKE :query";
			}
			if ($data["DrugMnn_id"] > 0) {
				$queryParams["DrugMnn_id"] = $data["DrugMnn_id"];
				$whereArray[] = "Drug.DrugMnn_id = :DrugMnn_id";
			}
		}
		$whereString = (count($whereArray) != 0) ? "where " . implode(" and ", $whereArray) : "";
		$query = "
            SELECT * FROM (
			select distinct
				Drug.Drug_id as \"Drug_id\",
				Drug.Drug_Code as \"Drug_Code\",
				rtrim(Drug.Drug_Name) as \"Drug_Name\",
				Drug.DrugMnn_id as \"DrugMnn_id\"
			from
			    v_Drug Drug
				left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
					and DrugPrice.DrugProto_begDate = (
						select max(DrugProto_begDate)
						from
							v_DrugPrice DP
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DP.Drug_id = Drug.Drug_id
					)
				left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
				left join v_ReceptFinance ReceptFinance on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id
			{$whereString}
			) t
			order by \"Drug_Name\"
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

}