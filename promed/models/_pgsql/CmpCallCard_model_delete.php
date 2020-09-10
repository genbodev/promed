<?php

class CmpCallCard_model_delete
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function delCmpCloseCardExpertResponse(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["CMPCloseCardExpertResponse_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор оценки");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpclosecardexpertresponse_del(cmpclosecardexpertresponse_id := :CMPCloseCardExpertResponse_id);
		";
		return $callObject->queryResult($query, $data);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function deleteCmpCallCardDrug(CmpCallCard_model $callObject, $data)
	{
		$result = [];
		$error = [];
		$callObject->load->model("DocumentUc_model", "du_model");
		if (empty($data["DocumentUcStr_id"])) {
			$query = "
                select DocumentUcStr_id as \"DocumentUcStr_id\"
                from v_CmpCallCardDrug
                where CmpCallCardDrug_id = :CmpCallCardDrug_id;
            ";
			$queryParams = ["CmpCallCardDrug_id" => $data["CmpCallCardDrug_id"]];
			$cccd_data = $callObject->getFirstRowFromQuery($query, $queryParams);
			if (!empty($cccd_data["DocumentUcStr_id"])) {
				$data["DocumentUcStr_id"] = $cccd_data["DocumentUcStr_id"];
			}
		}
		if (!empty($data["DocumentUcStr_id"])) {
			//снятие резерва по строке документа учета
			$query = "
                select ddt.DrugDocumentType_Code as \"DrugDocumentType_Code\"
                from
                    v_DocumentUcStr dus
                    inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
                    inner join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                where dus.DocumentUcStr_id = :DocumentUcStr_id
				limit 1
            ";
			$queryParams = ["DocumentUcStr_id" => $data["DocumentUcStr_id"]];
			$ddt_data = $callObject->getFirstRowFromQuery($query, $queryParams);
			if (!empty($ddt_data["DrugDocumentType_Code"]) && $ddt_data["DrugDocumentType_Code"] == 26) {
				// Тип документа 26 - Списание медикаментов из укладки на пациента - вернуть медикаменты в резерв (в укладку)
				$response = $callObject->du_model->returnDrugsToPack([
					"DocumentUcStr_id" => $data["DocumentUcStr_id"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (!empty($response["Error_Msg"])) {
					$error[] = $response["Error_Msg"];
				}
			} else {
				$response = $callObject->du_model->removeReserve([
					"DocumentUcStr_id" => $data["DocumentUcStr_id"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (!empty($response["Error_Msg"])) {
					$error[] = $response["Error_Msg"];
				}
			}
		}
		$response = $callObject->deleteObject("CmpCallCardDrug", ["CmpCallCardDrug_id" => $data["CmpCallCardDrug_id"]]);
		if (!empty($response["Error_Msg"])) {
			$error[] = $response["Error_Msg"];
		} elseif (!empty($data["DocumentUcStr_id"])) {
			$response = $callObject->deleteObject("DocumentUcStr", ["DocumentUcStr_id" => $data["DocumentUcStr_id"]]);
			if (!empty($response["Error_Msg"])) {
				$error[] = $response["Error_Msg"];
			}
		}
		if (count($error) > 0) {
			$result["Error_Msg"] = $error[0];
		}
		return $result;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteCmpCallCardEvnDrug(CmpCallCard_model $callObject, $data)
	{
		$error = [];
		//не стал использовать стандартный метод, т.к. там нет использования pmUser
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evndrug_del(
			    evndrug_id := :EvnDrug_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->getFirstRowFromQuery($query, $data);
		if (!$result || !is_array($result)) {
			throw new Exception("При удалении произошла ошибка");
		}
		if (empty($result["Error_Msg"])) {
			$result["success"] = true;
		}
		if (!empty($result["Error_Msg"])) {
			$error[] = $result["Error_Msg"];
		}
		if (count($error) > 0) {
			$result["Error_Msg"] = $error[0];
			$result["success"] = false;
		}
		return $result;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param array $data
	 * @param bool $ignoreRegistryCheck
	 * @param bool $delCallCard
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteCmpCallCard(CmpCallCard_model $callObject, $data = [], $ignoreRegistryCheck = false, $delCallCard = true)
	{
		$response = false;
		$doc_array = [];
		$isCloseCard = false;
		if (!array_key_exists("CmpCallCard_id", $data) || !$data["CmpCallCard_id"]) {
			throw new Exception("Не указан идентификатор карты вызова.");
		}
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if (isset($checkLock[0]["CmpCallCard_id"])) {
			throw new Exception("Карта вызова редактируется и не может быть удалена.");
		}
		//признак источника
		$callCardInputTypeCode = $callObject->getCallCardInputTypeCode($data["CmpCallCard_id"]);
		if ($ignoreRegistryCheck === false) {
			$checkRegistryParam = $data;
			$query = "
                select CmpCloseCard_id as \"CmpCloseCard_id\"
                from v_CmpCloseCard
                where CmpCallCard_id = :CmpCallCard_id;
            ";
			$cclc_array = $callObject->queryResult($query, ["CmpCallCard_id" => $data["CmpCallCard_id"]]);
			if (is_array($cclc_array) && count($cclc_array) > 0) {
				$isCloseCard = (int)$cclc_array[0]["CmpCloseCard_id"];
				$checkRegistryParam["CmpCloseCard_id"] = $cclc_array[0]["CmpCloseCard_id"];
				$checkRegistryParam["CmpCallCard_id"] = null;
			}
			// Проверку наличия карты вызова в реестре
			$callObject->load->model("Registry" . (getRegionNick() == "ufa" ? "Ufa" : "") . "_model", "Reg_model");
			$registryData = $callObject->Reg_model->checkEvnAccessInRegistry($checkRegistryParam);
			if (is_array($registryData)) {
				if (isset($registryData["Error_Msg"])) {
					$registryData["Error_Msg"] = str_replace("Удаление записи невозможно", " Удалите Карту вызова из реестра и повторите действие", $registryData["Error_Msg"]);
				}
				return $registryData;
			}
			unset($checkRegistryParam);
		}
		//удаление информации о использовании медикаментов
		$query = "
			select
				CmpCallCardDrug_id as \"CmpCallCardDrug_id\",
				DocumentUcStr_id as \"DocumentUcStr_id\"
            from v_CmpCallCardDrug
            where CmpCallCard_id = :CmpCallCard_id;
		";
		$cccd_array = $callObject->queryResult($query, ["CmpCallCard_id" => $data["CmpCallCard_id"]]);
		if (is_array($cccd_array)) {
			foreach ($cccd_array as $cccd_data) {
				if (!empty($cccd_data["DocumentUc_id"]) && !in_array($cccd_data["DocumentUc_id"], $doc_array)) {
					//сбор идентификаторов документов
					$doc_array[] = $cccd_data["DocumentUc_id"];
				}
				$response = $callObject->deleteCmpCallCardDrug([
					"CmpCallCardDrug_id" => $cccd_data["CmpCallCardDrug_id"],
					"DocumentUcStr_id" => $cccd_data["DocumentUcStr_id"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (!empty($response["Error_Msg"])) {
					throw new Exception($response["Error_Msg"]);
				}
			}
		}
		//удаление пустых документов учета
		if (count($doc_array) > 0) {
			$response = $callObject->deleteEmptyDocumentUc($doc_array);
			if (!empty($response["Error_Msg"])) {
				throw new Exception($response["Error_Msg"]);
			}
		}
		if ($delCallCard) {
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_cmpcallcard_del(
				    cmpcallcard_id := :CmpCallCard_id,
				    pmuser_id := :pmUser_id
				);
            ";
			$queryParams = [
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$response = $callObject->getFirstRowFromQuery($query, $queryParams);
			if ($response === false) {
				throw new Exception("Во время удаления талона вызова произошла ошибка. При повторении ошибки обратитесь к администратору.");
			} else {
				if (!empty($response["Error_Msg"])) {
					throw new Exception($response["Error_Msg"]);
				}
			}
		}
		if ($isCloseCard && !in_array($callCardInputTypeCode, [1, 2])) {
			//TODO 111
			$query = "
                select 
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_CmpCloseCard_del
                (
                    CmpCloseCard_id := :CmpCloseCard_id,
                    pmUser_id := :pmUser_id
                )
            ";
			$queryParams = [
				"CmpCloseCard_id" => $isCloseCard,
				"pmUser_id" => $data["pmUser_id"]
			];
			$response = $callObject->getFirstRowFromQuery($query, $queryParams);
			if ($response === false) {
				throw new Exception("Во время удаления карты вызова произошла ошибка. При повторении ошибки обратитесь к администратору.");
			} elseif (!empty($response["Error_Msg"])) {
				throw new Exception($response["Error_Msg"]);
			}
		}
		return $response;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteCmpCallCardStatus(CmpCallCard_model $callObject, $data)
	{

		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]["CmpCallCard_id"])) {
			throw new Exception("Карта вызова редактируется");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcardstatus_del(cmpcallcardstatus_id := :CmpCallCardStatus_id);
		";
		$queryParams = ["CmpCallCardStatus_id" => $data["CmpCallCardStatus_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function deleteCmpCallCardUsluga(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["CmpCallCardUsluga_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор услуги");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcardusluga_del(cmpcallcardusluga_id := :CmpCallCardUsluga_id);
		";
		return $callObject->queryResult($query, $data);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteUnformalizedAddress(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_unformalizedaddressdirectory_del(unformalizedaddressdirectory_id := :UnformalizedAddressDirectory_id);
		";
		$queryParams = ["UnformalizedAddressDirectory_id" => $data["UnformalizedAddressDirectory_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteCmpIllegalAct(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpillegalact_del(cmpillegalact_id := :CmpIllegalAct_id);
		";
		$queryParams = ['CmpIllegalAct_id' => $data['CmpIllegalAct_id']];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteDecigionTreeNode(CmpCallCard_model $callObject, $data)
	{
		$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
		$params = [
			"AmbulanceDecigionTree_id" => !empty($data["AmbulanceDecigionTree_id"]) ? $data["AmbulanceDecigionTree_id"] : null
		];
		$prequery = "
			select ac.AmbulanceDecigionTree_id as \"AmbulanceDecigionTree_id\"
			from
				v_AmbulanceDecigionTree ap
				left join v_AmbulanceDecigionTree ac on ac.AmbulanceDecigionTree_nodepid = ap.AmbulanceDecigionTree_nodeid and ac.Lpu_id = ap.Lpu_id
			where ap.AmbulanceDecigionTree_id = :AmbulanceDecigionTree_id
		";
		$preresult = $callObject->db->query($prequery, $params);
		if (is_object($preresult)) {
			$preresult = $preresult->result("array");
			if (count($preresult) && $preresult[0]["AmbulanceDecigionTree_id"]) {
				throw new Exception("Элемент содержит дочерние значения. Удаление невозможно.");
			}
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_ambulancedecigiontree_del(ambulancedecigiontree_id := :AmbulanceDecigionTree_id);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteCmpUrgencyAndProfileStandartRule(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["CmpUrgencyAndProfileStandart_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор правила");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpurgencyandprofilestandart_delwithrefs(cmpurgencyandprofilestandart_id := :CmpUrgencyAndProfileStandart_id);
		";
		$queryParams = ["CmpUrgencyAndProfileStandart_id" => $data["CmpUrgencyAndProfileStandart_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $object_name
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteObject(CmpCallCard_model $callObject, $object_name, $data)
	{
		$schema = "dbo";
		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode(".", $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}
		$selectString = "
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$schema}.p_{$object_name}_del({$object_name}_id := :{$object_name}_id);
		";
		$result = $callObject->getFirstRowFromQuery($query, $data);
		if (!$result || !is_array($result)) {
			throw new Exception("При удалении произошла ошибка");
		}
		if (empty($result["Error_Msg"])) {
			$result["success"] = true;
		}
		return $result;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $doc_id
	 * @return array
	 * @throws Exception
	 */
	public static function deleteEmptyDocumentUc(CmpCallCard_model $callObject, $doc_id)
	{
		$result = [];
		$doc_id_array = is_array($doc_id) ? $doc_id : [$doc_id];
		if (count($doc_id_array) > 0) {
			$doc_id_arrayString = implode(",", $doc_id_array);
			$query = "
                select du.DocumentUc_id as \"DocumentUc_id\"
                from
                	DocumentUc du
                    left join DrugDocumentStatus dds on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
                    left join DocumentUcStr dus on dus.DocumentUc_id = du.DocumentUc_id
                where du.DocumentUc_id in ({$doc_id_arrayString})
                  and (dds.DrugDocumentStatus_Code = 1 or (du.DrugDocumentType_id = 26 and dds.DrugDocumentStatus_Code = 4))
                  and  dus.DocumentUcStr_id is null;
            ";
			$del_array = $callObject->queryResult($query);
			if (is_array($del_array)) {
				foreach ($del_array as $del_data) {
					$response = $callObject->deleteObject("DocumentUc", $del_data);
					if (!empty($response["Error_Msg"])) {
						throw new Exception($response["Error_Msg"]);
					}
				}
			}
		}
		$result["success"] = true;
		return $result;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function delSmoQueryCallCards(CmpCallCard_model $callObject, $data)
	{
		$query = "
			delete from r2.CmpSmoQueryCardNumber
			where CmpSmoQueryCardNumbers_SmoID = :OrgSmo_id;
		";
		$callObject->db->query($query, $data);
		return [[]];
	}
}