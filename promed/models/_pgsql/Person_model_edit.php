<?php

class Person_model_edit
{
	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function deletePerson(Person_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = "
			select Error_Code as \"Error_Code\",
			       Error_Message as \"Error_Msg\"
			from dbo.p_Person_del(
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
			);
		";
		$IsSMPServer = $callObject->config->item("IsSMPServer");
		if ($IsSMPServer) {
			// подключаем основную бд
			$db = $callObject->load->database("main", true);
			// удаляем на основной бд
			$db->query($query, $params);
		}
		$resp = $callObject->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при удалении человека");
		}
		return $resp;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deletePersonEval(Person_model $callObject, $data)
	{
		if (!$data["EvalType"])
			return false;
		$type = $data["EvalType"];

		$sql = "
			select
				Error_Message as \"ErrMsg\"
			from p_" . $type . "_del(
				" . $type . "_id := :PersonEval_id
			)
		";

		$result = $callObject->db->query($sql, $data);

		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * Удаление атрибута
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function deletePersonEvnAttribute(Person_model $callObject, $data)
	{
		if (!isset($_SESSION)) {
			session_start();
		}
		unset($_SESSION["person"]);
		unset($_SESSION["person_short"]);
		session_write_close();
		$callObject->load->library("textlog", ["file" => "xp_PersonRemovePersonEvn.log"]);
		$callObject->textlog->add("Дата и время " . date("Y-m-d H:i:s") . " ID пользователя {$data["pmUser_id"]} // ");
		if ($data["Person_id"] > 0 && $data["PersonEvn_id"] > 0) {
			$params = [
				"Person_id" => $data["Person_id"],
				"Server_id" => $data["Server_id"],
				"PersonEvn_id" => $data["PersonEvn_id"],
				"PersonEvnClass_id" => $data["PersonEvnClass_id"],
				'pmUser_id' => $data['pmUser_id'],
			];
			// Для периодик ФИО, ДР, пол, соцстатус проверим что она не одна
			if (in_array($data["PersonEvnClass_id"], [1, 2, 3, 4, 5, 7])) {
				$sql = "
					select
						Server_id as \"Server_id\",
					    PersonEvn_id  as \"PersonEvn_id\"
					from v_Person_all
					where Person_id = :Person_id
					  and (Server_id <> :Server_id or PersonEvn_id <> :PersonEvn_id)
					  and PersonEvnClass_id = :PersonEvnClass_id
					order by
						PersonEvn_insDT desc,
					    PersonEvn_TimeStamp desc
					limit 1
				";
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($sql, $params);
				if (!is_object($result)) {
					return false;
				}
				$response = $result->result_array();
				if (is_array($response) && count($response) == 0) {
					throw new Exception("Удаление периодики не возможно, так как тип удаляемой периодики обязательный и периодика этого типа последняя у выбранного человека.");
				}
			}
			$sql = "
				select Error_Code as \"Error_Code\",
				       Error_Message as \"Error_Msg\"
				from dbo.xp_PersonRemovePersonEvn(
                    Person_id := :Person_id,
                    Server_id := :Server_id,
                    PersonEvn_id := :PersonEvn_id,
                    pmUser_id := :pmUser_id
				);
			";
			$callObject->textlog->add("Запрос " . getDebugSQL($sql, $params));
			$result = $callObject->db->query($sql, $params);

        }
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result_array();
		
        if (is_array($response) && count($response) > 0 && (isset($response[0]["Error_Code"]) && $response[0]["Error_Code"] == 547)) {
			throw new Exception("Удаляемая периодика используется в реестрах, удаление невозможно");
		}
        
		return $response;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function deletePersonWork(Person_model $callObject, $data)
	{
		$params = ["PersonWork_id" => $data["PersonWork_id"]];
		$query = "
			select
				PMUser.Cnt as \"pmUserCount\",
				StorageWork.Cnt as \"StorageWorkCount\"
			from 
				v_PersonWork PW 
				left join lateral (
					select count(*) as Cnt
					from v_pmUserCacheOrg 
					where pmUserCacheOrg_id = PW.pmUserCacheOrg_id
				) as PMUser on true
				left join lateral (
					select count(*) as Cnt
					from v_DocumentUcStorageWork 
					where (Person_cid = PW.Person_id and Post_cid = PW.Post_id)
					   or (Person_eid = PW.Person_id and Post_eid = PW.Post_id)
				) as StorageWork on true
			where PW.PersonWork_id = :PersonWork_id
            limit 1
		";
		$resp = $callObject->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении данных сотрудника");
		}
		if ($resp["pmUserCount"] > 0 || $resp["StorageWorkCount"] > 0) {
			throw new Exception("В системе есть объекты, связанные с сотрудником. Удаление сотрудника не возможно");
		}
		$resp = $callObject->deleteObject("PersonWork", $params);
		return [$resp];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function editPersonEvnAttribute(Person_model $callObject, $data)
	{
		$server_id = $data["Server_id"];
		$pid = $data["Person_id"];
		$peid = $data["PersonEvn_id"];
		$peoid = $data["PersonEvnObject_id"];
		$evn_types = explode("|", $data["EvnType"]);
		for ($i = 0; $i < count($evn_types); $i++) {
			switch ($evn_types[$i]) {
				case "Polis":
					$OmsSprTerr_id = (empty($data["OMSSprTerr_id"]) ? null : $data["OMSSprTerr_id"]);
					$PolisType_id = (empty($data["PolisType_id"]) ? null : $data["PolisType_id"]);
					$OrgSmo_id = (empty($data["OrgSMO_id"]) ? null : $data["OrgSMO_id"]);
					$Polis_Ser = (empty($data["Polis_Ser"]) ? "" : $data["Polis_Ser"]);
					$PolisFormType_id = (empty($data["PolisFormType_id"]) ? null : $data["PolisFormType_id"]);
					$Polis_Num = (empty($data["Polis_Num"]) ? "" : $data["Polis_Num"]);
					$Polis_begDate = empty($data["Polis_begDate"]) ? null : $data["Polis_begDate"];
					$Polis_endDate = empty($data["Polis_endDate"]) ? null : $data["Polis_endDate"];
					// для прав суперадмина
					$serv_id = $server_id;
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_Polis_upd(
							Server_id := ?,
							Polis_id := ?,
							OmsSprTerr_id := ?,
							PolisType_id := ?,
							OrgSmo_id := ?,
							Polis_Ser := ?,
							PolisFormType_id:=?,
							Polis_Num := ?,
							Polis_begDate := ?,
							Polis_endDate := ?,
							pmUser_id := ?
						);
					";
					if (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate) {
						$sqlParams = [$serv_id, $peoid, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser, $PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data["pmUser_id"]];
						$res = $callObject->db->query($sql, $sqlParams);
						$callObject->ValidateInsertQuery($res);
						$funcParams = [
							"person_is_identified" => false,
							"session" => $data["session"],
							"PersonEvn_id" => $peoid,
							"Date" => $Polis_begDate,
							"Server_id" => $serv_id,
							"pmUser_id" => $data["pmUser_id"]
						];
						$callObject->editPersonEvnDate($funcParams);
					}
					break;
				case "Document":
					$DocumentType_id = (empty($data['DocumentType_id']) ? null : $data['DocumentType_id']);
					$OrgDep_id = (empty($data['OrgDep_id']) ? null : $data['OrgDep_id']);
					$Document_Ser = (empty($data['Document_Ser']) ? '' : $data['Document_Ser']);
					$Document_Num = (empty($data['Document_Num']) ? '' : $data['Document_Num']);
					$Document_begDate = empty($data['Document_begDate']) ? null : $data['Document_begDate'];
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_Document_upd(
							Server_id := ?,
							Document_id := ?,
							DocumentType_id := ?,
							OrgDep_id := ?,
							Document_Ser := ?,
							Document_Num := ?,
							Document_begDate := ?,
							Document_endDate := null,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $peoid, $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "UAddress":
					$KLCountry_id = (empty($data["UKLCountry_id"]) ? null : $data["UKLCountry_id"]);
					$KLRgn_id = (empty($data["UKLRGN_id"]) ? null : $data["UKLRGN_id"]);
					$KLSubRgn_id = (empty($data["UKLSubRGN_id"]) ? null : $data["UKLSubRGN_id"]);
					$KLCity_id = (empty($data["UKLCity_id"]) ? null : $data["UKLCity_id"]);
					$KLTown_id = (empty($data["UKLTown_id"]) ? null : $data["UKLTown_id"]);
					$KLStreet_id = (empty($data["UKLStreet_id"]) ? null : $data["UKLStreet_id"]);
					$Address_Zip = (empty($data["UAddress_Zip"]) ? "" : $data["UAddress_Zip"]);
					$Address_House = (empty($data["UAddress_House"]) ? "" : $data["UAddress_House"]);
					$Address_Corpus = (empty($data["UAddress_Corpus"]) ? "" : $data["UAddress_Corpus"]);
					$Address_Flat = (empty($data["UAddress_Flat"]) ? "" : $data["UAddress_Flat"]);
					$PersonSprTerrDop_id = (empty($data["PersonSprTerrDop_id"]) ? null : $data["PersonSprTerrDop_id"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_Address_upd(
							Server_id := ?,
							Address_id := ?,
							KLCountry_id := ?,
							KLAreaType_id := null,
							KLRgn_id := ?,
							KLSubRgn_id := ?,
							KLCity_id := ?,
							KLTown_id := ?,
							KLStreet_id := ?,
							Address_Zip := ?,
							Address_House := ?,
							Address_Corpus := ?,
							Address_Flat := ?,
							PersonSprTerrDop_id := ?,
							Address_Address := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $peoid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PAddress":
					$KLCountry_id = (empty($data["PKLCountry_id"]) ? null : $data["PKLCountry_id"]);
					$KLRgn_id = (empty($data["PKLRGN_id"]) ? null : $data["PKLRGN_id"]);
					$KLSubRgn_id = (empty($data["PKLSubRGN_id"]) ? null : $data["PKLSubRGN_id"]);
					$KLCity_id = (empty($data["PKLCity_id"]) ? null : $data["PKLCity_id"]);
					$KLTown_id = (empty($data["PKLTown_id"]) ? null : $data["PKLTown_id"]);
					$KLStreet_id = (empty($data["PKLStreet_id"]) ? null : $data["PKLStreet_id"]);
					$Address_Zip = (empty($data["PAddress_Zip"]) ? "" : $data["PAddress_Zip"]);
					$Address_House = (empty($data["PAddress_House"]) ? "" : $data["PAddress_House"]);
					$Address_Corpus = (empty($data["PAddress_Corpus"]) ? "" : $data["PAddress_Corpus"]);
					$PersonSprTerrDop_id = (empty($data["PPersonSprTerrDop_id"]) ? null : $data["PPersonSprTerrDop_id"]);
					$Address_Flat = (empty($data["PAddress_Flat"]) ? "" : $data["PAddress_Flat"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_Address_upd(
							Server_id := ?,
							Address_id := ?,
							KLAreaType_id := null,
							KLCountry_id := ?,
							KLRgn_id := ?,
							KLSubRgn_id := ?,
							KLCity_id := ?,
							KLTown_id := ?,
							KLStreet_id := ?,
							Address_Zip := ?,
							Address_House := ?,
							Address_Corpus := ?,
							Address_Flat := ?,
							PersonSprTerrDop_id := ?,
							Address_Address := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $peoid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Job":
					$Post_id = (empty($data["Post_id"]) ? null : $data["Post_id"]);
					$Org_id = (empty($data["Org_id"]) ? null : $data["Org_id"]);
					$OrgUnion_id = (empty($data["OrgUnion_id"]) ? null : $data["OrgUnion_id"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_Job_upd(
							Server_id := ?,
							Job_id := ?,
							Org_id := ?,
							OrgUnion_id := ?,
							Post_id := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $peoid, $Org_id, $OrgUnion_id, $Post_id, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_SurName":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSurName_upd(
							Server_id := ?,
							Person_id := ?,
							PersonSurName_id := ?,
							PersonSurName_SurName := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $data["Person_SurName"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_SecName":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSecName_upd(
							Server_id := ?,
							Person_id := ?,
							PersonSecName_id := ?,
							PersonSecName_SecName := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $data["Person_SecName"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_FirName":
					$sql = "
						select Error_Message as ErrMsg
						from dbo.p_PersonFirName_upd(
							Server_id := ?,
							Person_id := ?,
							PersonFirName_id := ?,
							PersonFirName_FirName := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $data["Person_FirName"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_BirthDay":
					$date = empty($data["Person_BirthDay"]) ? null : $data["Person_BirthDay"];
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonBirthDay_upd(
							Server_id := ?,
							Person_id := ?,
							PersonBirthDay_id := ?,
							PersonBirthDay_BirthDay := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $date, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_SNILS":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSnils_upd(
							Server_id := ?,
							Person_id := ?,
							PersonSnils_id := ?,
							PersonSnils_Snils := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $data["Person_SNILS"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonSex_id":
					$Sex_id = (!isset($data["PersonSex_id"]) || !is_numeric($data["PersonSex_id"]) ? null : $data["PersonSex_id"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSex_upd(
							Server_id := ?,
							Person_id := ?,
							PersonSex_id := ?,
							Sex_id := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $Sex_id, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "SocStatus_id":
					$SocStatus_id = (empty($data["SocStatus_id"]) ? null : $data["SocStatus_id"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSocStatus_upd(
							Server_id := ?,
							Person_id := ?,
							PersonSocStatus_id := ?,
							SocStatus_id := ?,
							pmUser_id := ?
						)
					";
					$sqlParams = [$server_id, $pid, $peid, $SocStatus_id, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "FamilyStatus_id":
					$PersonFamilyStatus_IsMarried = (empty($data["PersonFamilyStatus_IsMarried"]) ? null : $data["PersonFamilyStatus_IsMarried"]);
					$FamilyStatus_id = (empty($data["FamilyStatus_id"]) ? null : $data["FamilyStatus_id"]);
					if (empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id)) {
						throw new Exception("Хотя бы одно из полей \"Семейное положение\" или \"Состоит в зарегистрированном браке\" должно быть заполнено");
					}
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonFamilyStatus_upd(
							Server_id := ?,
							Person_id := ?,
							PersonFamilyStatus_id := ?,
							FamilyStatus_id := ?,
							PersonFamilyStatus_IsMarried := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Federal_Num":
					$Federal_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonPolisEdNum_upd(
							Server_id := ?,
							Person_id := ?,
							PersonPolisEdNum_id := ?,
							PersonPolisEdNum_EdNum := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $peid, $Federal_Num, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				default:
			}
		}
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew(Person_model $callObject, $data)
	{
		if (empty($data["PersonIdentState_id"]) || !in_array(intval($data["PersonIdentState_id"]), [1, 2, 3])) {
			$data["PersonIdentState_id"] = 0;
		}
		$person_is_identified = false;
		if ($data["PersonIdentState_id"] == 1) {
			$person_is_identified = true;
			$sql = "
				select error_code as \"Error_Code\",
				       error_message as \"Error_Msg\"
				from dbo.p_Person_ident(
					Person_id := :Person_id,
					Person_identDT := getdate(),
					PersonIdentState_id := :PersonIdentState_id,
					pmUser_id := :pmUser_id);
			";
			$sqlParams = [
				"Person_id" => $data["Person_id"],
				"PersonIdentState_id" => $data["PersonIdentState_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->db->query($sql, $sqlParams);
			$query = "
				select Person_BDZCode  as \"Person_BDZCode\"
				from v_PersonInfo
				where Person_id = :Person_id
			";
			$sqlParams = ["Person_id" => $data["Person_id"]];
			$result = $callObject->getFirstResultFromQuery($query, $sqlParams);
			if (empty($result) && isset($data["rz"]) && $data["rz"] != "") {
				$query = "
					update personinfo
					set Person_BDZCode = :rz
					where Person_id = :Person_id
				";
				$sqlParams = [
					"rz" => $data["rz"],
					"Person_id" => $data["Person_id"]
				];
				$callObject->db->query($query, $sqlParams);
			}
		}
		if (!empty($data["Person_IsInErz"]) && $data["Person_IsInErz"] == 2) {
			$person_is_identified = true;
		}
		$oldMainFields = [];
		if (getRegionNick() == "penza") {
			$oldMainFields = $callObject->getMainFields($data);
			if (!is_array($oldMainFields)) {
				throw new Exception("Ошибка при получении актуальных атрибутов человека");
			}
		}

		// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан,
		// чтобы лишний раз в сессию не писать, экономим на спичках
		if (!isset($_SESSION)) {
			session_start();
		}
		if (isset($data["session"]) && isset($data["session"]["person"]) && isset($data["session"]["person"]["Person_id"]) && isset($data["Person_id"]) && $data["Person_id"] == $data["session"]["person"]["Person_id"]) {
			unset($_SESSION["person"]);
		}
		if (isset($data["session"]) && isset($data["session"]["person_short"]) && isset($data["session"]["person_short"]["Person_id"]) && isset($data["Person_id"]) && $data["Person_id"] == $data["session"]["person_short"]["Person_id"]) {
			unset($_SESSION["person_short"]);
		}
		session_write_close();
		$server_id = $data["Server_id"];
		$pid = $data["Person_id"];
		$BDZaffected = false;
		$sel = [];
		$evn_types = explode("|", $data["EvnType"]);
		$count_evn_types = count($evn_types);
		for ($i = 0; $i < $count_evn_types; $i++) {
			switch ($evn_types[$i]) {
				case "Deputy":
					Person_model_edit::editPersonEvnAttributeNew_Deputy($callObject, $data);
					break;
				case "Polis":
					if (!isset($data["OMSSprTerr_id"]) || empty($data["OMSSprTerr_id"])) {
						continue 2;
					}
					/**@var CI_DB_result $result */
					$OmsSprTerr_id = (empty($data["OMSSprTerr_id"]) ? null : $data["OMSSprTerr_id"]);
					$PolisType_id = (empty($data["PolisType_id"]) ? null : $data["PolisType_id"]);
					$OrgSmo_id = (empty($data["OrgSMO_id"]) ? null : $data["OrgSMO_id"]);
					$Polis_Ser = (empty($data["Polis_Ser"]) ? "" : $data["Polis_Ser"]);
					$PolisFormType_id = (empty($data["PolisFormType_id"]) ? null : $data["PolisFormType_id"]);
					$Polis_Num = (empty($data["Polis_Num"]) ? "" : $data["Polis_Num"]);
					$Polis_begDate = empty($data["Polis_begDate"]) ? null : $data["Polis_begDate"];
					$Polis_endDate = empty($data["Polis_endDate"]) ? null : $data["Polis_endDate"];
					$Polis_Guid = empty($data["Polis_Guid"]) ? null : $data["Polis_Guid"];
					$Federal_Num = empty($data["Federal_Num"]) ? null : $data["Federal_Num"];
					$Evn_setDT = empty($data["Evn_setDT"]) ? null : $data["Evn_setDT"];
					if (!empty($Federal_Num) && strlen($Federal_Num) < 16) {
						throw new Exception("Единый номер полиса должен иметь длину в 16 цифр");
					}
					if ($PolisType_id == 4) {
						$Polis_Num = $Federal_Num;
						$data["Polis_Num"] = $Federal_Num;
					}
					if ((empty($data["apiAction"]) || $data["apiAction"] != "create") && (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true)) {
						$sel[0]["Server_id"] = $data["Server_id"];
						$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
					} else {
						$flt = ($Evn_setDT != null)
							?"Person.PersonPolis_insDT <= :Evn_setDT and"
							:"Person.PersonPolis_insDT <= (select PersonEvn_insDT from v_PersonEvn where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id limit 1) and";
						$sql = "
							select
								Person.PersonPolis_id as \"PersonEvn_id\",
								Person.Server_id as \"Server_id\",
								Person.Polis_id as \"Polis_id\"
							from
								v_PersonPolis Person 
								left join lateral (select Person_edNum from v_Person_all  where Person.Polis_id = Polis_id limit 1) as edNum on true
							where
								{$flt}
								Person.Person_id = :Person_id and
								:Polis_Num = case when Person.PolisType_id = 4 then edNum.Person_EdNum else Person.Polis_Num end and
								(coalesce(Person.Polis_Ser, '') = coalesce(:Polis_Ser, '')) and 
                                Person.PolisType_id = :PolisType_id and
                                Person.OrgSMO_id = :OrgSMO_id 
							order by
								Person.PersonPolis_insDT desc,
								Person.PersonPolis_TimeStamp desc
							limit 1
						";
						$sqlParams = [
							"Evn_setDT" => $Evn_setDT,
							"PersonEvn_id" => $data["PersonEvn_id"],
							"Server_id" => $data["Server_id"],
							"Person_id" => $data["Person_id"],
							"Polis_Num" => $data["Polis_Num"],
							"Polis_Ser" => $data["Polis_Ser"],
							"PolisType_id" => $data["PolisType_id"],
							"OrgSMO_id" => $data["OrgSMO_id"]
						];
						$result = $callObject->db->query($sql, $sqlParams);
						$sel = $result->result_array();
					}
					$check = $callObject->checkPolisIntersection($data, true);
					if (isset($check["PersonEvn_id"]) && isset($check["Server_id"])) {
						$sel[0]["Server_id"] = $check["Server_id"];
						$sel[0]["PersonEvn_id"] = $check["PersonEvn_id"];
					}
					if (count($sel) > 0 && isset($check["deletedPersonEvnList"]) && in_array($sel[0]["PersonEvn_id"], $check["deletedPersonEvnList"])) {
						$sel = [];
					}
					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0 && (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate)) {
						if ($check === false) {
							throw new Exception("Периоды полисов не могут пересекаться.");
						}
						// для прав суперадмина
						$serv_id = $server_id;
						$sql = "
							select Polis_id as \"Polis_id\", Error_Message as \"ErrMsg\"
							from dbo.p_PersonPolis_ins(
								Server_id := ?,
								Person_id := ?,
								PersonPolis_insDT := ?,
								OmsSprTerr_id := ?,
								PolisType_id := ?,
								OrgSmo_id := ?,
								Polis_Ser := ?,
								PolisFormType_id :=?,
								Polis_Guid:=?,
								Polis_Num := ?,
								Polis_begDate := ?,
								Polis_endDate := ?,
								pmUser_id := ?
							);
						";
						if ($person_is_identified) {
							$serv_id = 0;
						}
						$sqlParams = [$serv_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser, $PolisFormType_id, $Polis_Guid, $Polis_Num, $Polis_begDate, $Polis_endDate, $data["pmUser_id"]];
						$result = $callObject->db->query($sql, $sqlParams);
						$callObject->ValidateInsertQuery($result);
						$resp = $result->result_array();
						$callObject->_setSaveResponse("Polis_id", $resp[0]["Polis_id"]);
						if ($person_is_identified) {
							$fsql = "
								select PE.PersonPolisEdNum_id as \"PersonPolisEdNum_id\"
								from v_PersonPolisEdNum PE
								where PE.Person_id =:Person_id
								  and pe.PersonPolisEdNum_Ednum =:edNum
								order by
									PersonPolisEdNum_insDT desc,
									PersonPolisEdNum_TimeStamp desc
                                limit 1
							";
						} else {
							$flt = ($Evn_setDT != null)
								? ":Evn_setDT"
								: "(select PersonEvn_insDT from v_PersonEvn  where PersonEvn_id = :PersonEvn_id and Server_id = :serverId)";
							$fsql = "
								select PE.PersonPolisEdNum_id as \"PersonPolisEdNum_id\"
								from v_PersonPolisEdNum PE
								where PE.PersonPolisEdNum_insDT <= {$flt}
								  and PE.Person_id =:Person_id
								  and pe.PersonPolisEdNum_Ednum =:edNum
								  and PE.PersonPolisEdNum_insDT <= :begdate
								order by
									PersonPolisEdNum_insDT desc,
									PersonPolisEdNum_TimeStamp desc
								limit 1
							";
						}
						$sqlParams = [
							"Evn_setDT" => $Evn_setDT,
							"PersonEvn_id" => $data["PersonEvn_id"],
							"Person_id" => $data["Person_id"],
							"edNum" => $Federal_Num,
							"begdate" => $Polis_begDate,
							"serverId" => $serv_id
						];
						$result = $callObject->db->query($fsql, $sqlParams);
						$fsel = $result->result_array();
						// если не было, то добавляем атрибут на дату
						if (count($fsel) == 0) {
							$checkEdNum = $callObject->checkPesonEdNumOnDate(["Person_id" => $data["Person_id"], "begdate" => $Polis_begDate]);
							if ($checkEdNum === false) {
								$date = ConvertDateFormat($Polis_begDate, "d.m.Y");
								throw new Exception("На дату {$date} уже создан Ед. номер.");
							}
							$sql = "
								select Error_Message as \"ErrMsg\"
								from dbo.p_PersonPolisEdNum_ins(
									Server_id := ?,
									Person_id := ?,
									PersonPolisEdNum_insDT := ?,
									PersonPolisEdNum_EdNum := ?,
									pmUser_id := ?
								);
							";
							if ($Federal_Num != null) {
								$result = $callObject->db->query($sql, [$serv_id, $pid, $Polis_begDate, $Federal_Num, $data["pmUser_id"]]);
								$callObject->ValidateInsertQuery($result);
							}
						}
					} elseif (count($sel)) {
						// иначе редактируем этот атрибут
						$sql = "
							select
								1,
								to_char(Person.Polis_endDate, '{$callObject->dateTimeForm104}') as \"Polis_endDate\"
							from
								v_PersonPolis Person 
								left join lateral (select Person_edNum from v_Person_all where Person.Polis_id = Polis_id limit 1) as edNum on true
							where Person.OMSSprTerr_id = :OMSSprTerr_id
							  and Person.PersonPolis_id = :PersonEvn_id
							  and :Polis_Num = case when Person.PolisType_id = 4 then edNum.Person_EdNum else Person.Polis_Num end
							  and coalesce(Person.Polis_Ser, '') = coalesce(:Polis_Ser, '')
							  and Person.OrgSMO_id = 	:OrgSMO_id
                              and Person.Polis_begDate::date = :Polis_begDate
                        	limit 1
						";
						$sqlParams = [
							"PersonEvn_id" => $data["PersonEvn_id"],
							"OMSSprTerr_id" => $OmsSprTerr_id,
							"Person_id" => $data["Person_id"],
							"Polis_Num" => $Polis_Num,
							"Polis_Ser" => $Polis_Ser,
							"Polis_begDate" => $Polis_begDate,
							"OrgSMO_id" => $OrgSmo_id
						];
						$result = $callObject->db->query($sql, $sqlParams);
						$isChng = $result->result_array();
						if (count($isChng) == 0) {
							$BDZaffected = true;
							$data["Polis_Guid"] = null;
						} else if ($isChng[0]["Polis_endDate"] != $Polis_endDate) {
							$BDZaffected = true;
						}
						$serv_id = $sel[0]["Server_id"];
						$peid = $sel[0]["PersonEvn_id"];
						$sql = "
							select Polis_id as \"Polis_id\", Error_Message as \"ErrMsg\"
							from dbo.p_PersonPolis_upd(
								PersonPolis_id := :peid,
								Server_id := :serv_id,
								Person_id := :pid,
								OmsSprTerr_id := :OmsSprTerr_id,
								PolisType_id := :PolisType_id,
								OrgSmo_id := :OrgSmo_id,
								Polis_Ser := :Polis_Ser,
								PolisFormType_id:=:PolisFormType_id,
								Polis_Num := :Polis_Num,
								Polis_begDate := :Polis_begDate,
								Polis_endDate := :Polis_endDate,
								pmUser_id := :pmUser_id
							);
						";
						if (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate) {
							$sqlParams = [
								"peid" => $peid,
								"serv_id" => $serv_id,
								"pid" => $pid,
								"OmsSprTerr_id" => $OmsSprTerr_id,
								"PolisType_id" => $PolisType_id,
								"OrgSmo_id" => $OrgSmo_id,
								"Polis_Ser" => $Polis_Ser,
								"PolisFormType_id" => $PolisFormType_id,
								"Polis_Num" => $Polis_Num,
								"Polis_begDate" => $Polis_begDate,
								"Polis_endDate" => $Polis_endDate,
								"pmUser_id" => $data["pmUser_id"]
							];
							$result = $callObject->db->query($sql, $sqlParams);
							$funcParams = [
								"person_is_identified" => $person_is_identified,
								"session" => $data["session"],
								"PersonEvn_id" => $peid,
								"Date" => $Polis_begDate,
								"Server_id" => $serv_id,
								"pmUser_id" => $data["pmUser_id"]
							];
							$callObject->editPersonEvnDate($funcParams);
							$callObject->ValidateInsertQuery($result);
							$resp = $result->result_array();
							$callObject->_setSaveResponse("Polis_id", $resp[0]["Polis_id"]);
						}
						if ($person_is_identified || count($isChng) == 0) {
							$sql = "
								select error_code as \"Error_Code\",
								       error_message as \"Error_Msg\"
								from dbo.p_Polis_server(
									Polis_id := (select polis_id from v_PersonPolis where PersonPolis_id = :PersonEvn_id limit 1),
									Server_id := :Server_id,
									Polis_Guid := :Polis_Guid,
									pmUser_id := :pmUser_id
								);
							";
							$sqlParams = [
								"PersonEvn_id" => $peid,
								"Server_id" => 0,
								"Polis_Guid" => (isset($data["Polis_Guid"])) ? $data["Polis_Guid"] : null,
								"pmUser_id" => $data["pmUser_id"]
							];
							$result = $callObject->db->query($sql, $sqlParams);
							if (!is_object($result)) {
								throw new Exception("Ошибка при выполнении запроса к базе данных (проставление признака идентификации по сводной базе застрахованных)");
							}
							$response = $result->result_array();
							if (!is_array($response) || count($response) == 0) {
								throw new Exception("Ошибка при проставлении признака идентификации по сводной базе застрахованных");
							}
						}
						if (isset($data["Federal_Num"])) {
							if ($person_is_identified) {
								$fsql = "
									select PE.PersonPolisEdNum_id as \"PersonPolisEdNum_id\"
									from v_PersonPolisEdNum PE
									where PE.Person_id =:Person_id
									  and pe.PersonPolisEdNum_Ednum =:edNum
									order by
										PersonPolisEdNum_insDT desc,
										PersonPolisEdNum_TimeStamp desc
                                    limit 1
								";
								$sqlParams = [
									"Evn_setDT" => $Evn_setDT,
									"PersonEvn_id" => $data["PersonEvn_id"],
									"Person_id" => $data["Person_id"],
									"edNum" => $Federal_Num,
									"begdate" => $Polis_begDate,
									"serverId" => $serv_id
								];
								$result = $callObject->db->query($fsql, $sqlParams);
								$fsel = $result->result_array();
								if (count($fsel) == 0) {
									// если не было, то добавляем атрибут на дату
									$checkEdNum = $callObject->checkPesonEdNumOnDate(["Person_id" => $data["Person_id"], "begdate" => $Polis_begDate]);
									if ($checkEdNum === false) {
										$date = ConvertDateFormat($Polis_begDate, "d.m.Y");
										throw new Exception("На дату {$date} уже создан Ед. номер.");
									}
									$sql = "
										select Error_Message as \"ErrMsg\"
										from dbo.p_PersonPolisEdNum_ins(
											Server_id := ?,
											Person_id := ?,
											PersonPolisEdNum_insDT := ?,
											PersonPolisEdNum_EdNum := ?,
											pmUser_id := ?
										);
									";
									if ($Federal_Num != null) {
										$result = $callObject->db->query($sql, [$serv_id, $pid, $Polis_begDate, $Federal_Num, $data["pmUser_id"]]);
										$callObject->ValidateInsertQuery($result);
									}
								}
							} else {
								$funcParams = [
									"ObjectName" => "PersonPolisEdNum",
									"ObjectField" => "PersonPolisEdNum_EdNum",
									"ObjectData" => empty($data["Federal_Num"]) ? "" : $data["Federal_Num"],
									"Server_id" => $data["Server_id"],
									"Person_id" => $data["Person_id"],
									"PersonEvn_id" => $data["PersonEvn_id"],
									"pmUser_id" => $data["pmUser_id"],
									"PersonEvnClass_id" => 16
								];
								$callObject->savePersonEvnSimpleAttr($funcParams);
							}
						}
					}
					if (!empty($data["Person_id"]) && $data["Person_id"] != 0 && $data["Person_id"] != null) {
						$sql = "select dbo.xp_PersonTransferEvn(Person_id := ?)";
						$callObject->db->query($sql, [$data["Person_id"]]);
					}
					break;
				case "NationalityStatus":
					$v = Person_model_edit::editPersonEvnAttributeNew_NationalityStatus($callObject, $data);
					if($v != true) {
						return $v;
					}
					break;
				case "Document":
					if (!isset($data["DocumentType_id"]) || empty($data["DocumentType_id"])) {
						continue 2;
					}
					$v = Person_model_edit::editPersonEvnAttributeNew_Document($callObject, $data);
					if($v != true) {
						return $v;
					}
					break;
				case "BAddress":
					Person_model_edit::editPersonEvnAttributeNew_BAddress($callObject, $data);
					break;
				case "UAddress":
					if (empty($data["UKLCountry_id"]) && getRegionNick() != "kz") {
						continue 2;
					}
					Person_model_edit::editPersonEvnAttributeNew_UAddress($callObject, $data);
					break;
				case "PAddress":
					if (empty($data["PKLCountry_id"]) && getRegionNick() != "kz") {
						continue 2;
					}
					Person_model_edit::editPersonEvnAttributeNew_PAddress($callObject, $data);
					break;
				case "Job":
					Person_model_edit::editPersonEvnAttributeNew_Job($callObject, $data);
					break;
				case "Person_SurName":
					$BDZaffected = true;
					$funcParams = [
						"ObjectName" => "PersonSurName",
						"ObjectField" => "PersonSurName_SurName",
						"ObjectData" => $data["Person_SurName"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"insPeriodic" => (isset($data["insPeriodic"]) && $data["insPeriodic"]),
						"insDT" => empty($data["Polis_begDate"]) ? null : $data["Polis_begDate"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 1
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "Person_SecName":
					$BDZaffected = true;
					$funcParams = [
						"AllowEmpty" => true,
						"ObjectName" => "PersonSecName",
						"ObjectField" => "PersonSecName_SecName",
						"ObjectData" => $data["Person_SecName"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 3
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "Person_FirName":
					$BDZaffected = true;
					if (!empty($data["Person_FirName"])) {
						$funcParams = [
							"ObjectName" => "PersonFirName",
							"ObjectField" => "PersonFirName_FirName",
							"ObjectData" => $data["Person_FirName"],
							"Server_id" => $data["Server_id"],
							"Person_id" => $data["Person_id"],
							"PersonEvn_id" => $data["PersonEvn_id"],
							"pmUser_id" => $data["pmUser_id"],
							"PersonEvnClass_id" => 2
						];
						$callObject->savePersonEvnSimpleAttr($funcParams);
					}
					break;
				case "PersonPhone_Phone":
					$funcParams = [
						"AllowEmpty" => true,
						"ObjectName" => "PersonPhone",
						"ObjectField" => "PersonPhone_Phone",
						"ObjectData" => (!empty($data["PersonPhone_Phone"]))?str_replace(["-", "(", ")", " "], "", trim((string)$data["PersonPhone_Phone"])):"",
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 18
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "PersonInn_Inn":
					$funcParams = [
						"AllowEmpty" => true,
						"ObjectName" => "PersonInn",
						"ObjectField" => "PersonInn_Inn",
						"ObjectData" => $data["PersonInn_Inn"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 20
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "PersonSocCardNum_SocCardNum":
					$funcParams = [
						"AllowEmpty" => true,
						"ObjectName" => "PersonSocCardNum",
						"ObjectField" => "PersonSocCardNum_SocCardNum",
						"ObjectData" => $data["PersonSocCardNum_SocCardNum"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 21
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "PersonRefuse_IsRefuse":
					$funcParams = [
						"ObjectName" => "PersonRefuse",
						"ObjectField" => "PersonRefuse_IsRefuse",
						"ObjectData" => $data["PersonRefuse_IsRefuse"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 15,
						"AdditFields" => ["@PersonRefuse_Year" => date("Y")]
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "Person_BirthDay":
					$BDZaffected = true;
					$funcParams = [
						"ObjectName" => "PersonBirthDay",
						"ObjectField" => "PersonBirthDay_BirthDay",
						"ObjectData" => empty($data["Person_BirthDay"]) ? null : $data["Person_BirthDay"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 4
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "Person_SNILS":
					$funcParams = [
						"ObjectName" => "PersonSnils",
						"ObjectField" => "PersonSnils_Snils",
						"ObjectData" => $data["Person_SNILS"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 6
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "PersonSex_id":
					$BDZaffected = true;
					$funcParams = [
						"ObjectName" => "PersonSex",
						"ObjectField" => "Sex_id",
						"ObjectData" => !isset($data["PersonSex_id"]) || !is_numeric($data["PersonSex_id"]) ? null : $data["PersonSex_id"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 5
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "SocStatus_id":
					$BDZaffected = true;
					$funcParams = [
						"AllowEmpty" => $callObject->regionNick == "kz",
						"ObjectName" => "PersonSocStatus",
						"ObjectField" => "SocStatus_id",
						"ObjectData" => empty($data["SocStatus_id"]) ? null : $data["SocStatus_id"],
						"Server_id" => $data["Server_id"],
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"pmUser_id" => $data["pmUser_id"],
						"PersonEvnClass_id" => 7
					];
					$callObject->savePersonEvnSimpleAttr($funcParams);
					break;
				case "FamilyStatus_id":
					Person_model_edit::editPersonEvnAttributeNew_FamilyStatus_id($callObject, $data);
					break;
				case "Federal_Num":
					$BDZaffected = true;
					Person_model_edit::editPersonEvnAttributeNew_Federal_Num($callObject, $data, $evn_types);
					break;
				default:
			}
		}
		/**@var CI_DB_result $result */
		$not_evn_types = isset($data["NotEvnType"]) ? explode("|", $data["NotEvnType"]) : [];
		for ($i = 0; $i < count($not_evn_types); $i++) {
			switch ($not_evn_types[$i]) {
				case "Person":
					$params = [
						"Person_id" => $data["Person_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					if (array_key_exists("Person_Comment", $data)) {
						$params["Person_Comment"] = $data["Person_Comment"];
					}
					if (array_key_exists("Person_deadDT", $data)) {
						$params["Person_deadDT"] = $data["Person_deadDT"];
					}
					if (array_key_exists("Person_IsInErz", $data)) {
						$params["Person_IsInErz"] = $data["Person_IsInErz"];
					}
					if (array_key_exists("BDZ_id", $data)) {
						$params["BDZ_id"] = $data["BDZ_id"];
					}
					if (array_key_exists("Person_IsUnknown", $data)) {
						$params["Person_IsUnknown"] = !empty($data["Person_IsUnknown"]) ? 2 : 1;
					}
					$resp = $callObject->updatePerson($params);
					$callObject->ValidateInsertQuery($resp);
					break;
				case "PersonChild":
					$resp = $callObject->savePersonChild($data);
					$callObject->ValidateInsertQuery($resp);
					break;
			}
		}
		if (count($evn_types) > 0) {
			$sql = "
				select dbo.xp_PersonTransferEvn(Person_id := :Person_id, Evn_id := e.Evn_id)
                from v_evn  e 
                where e.person_id =:Person_id
			";
			$sqlParams = [
				"Person_id" => $data["Person_id"],
				"Server_id" => $data["Server_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД при удалении данных.");
			}
		}
		if ((!$person_is_identified && getRegionNick() == "kareliya") || getRegionNick() == "ufa") {
			// если ключевая периодика и не суперадмин и Карелия, то выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
			$query = "
				select 
					ps.Person_id as \"Person_id\",
					ps.Server_pid as \"Server_pid\",
					BDZ.BDZ_Guid as \"BDZ_Guid\"
				from
					v_PersonState ps 
					left join lateral (
					    select BDZ_Guid
					    from v_Person p
					    where p.Person_id = ps.Person_id
					    limit 1
					) as BDZ on true
				where ps.Person_id = :Person_id
				limit 1
			";
			$result = $callObject->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (!empty($resp[0]["Person_id"]) && ($BDZaffected && (((getRegionNick() == "ufa") && isSuperAdmin() && !$person_is_identified) || !isSuperAdmin()) && $resp[0]["Server_pid"] == 0)) {
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_Person_server(
							Server_id := :Server_id,
							Person_id := :Person_id,
							BDZ_Guid := :BDZ_Guid,
							pmUser_id := :pmUser_id
						);
					";
					$sqlParams = [
						"BDZ_Guid" => (isset($resp[0]["BDZ_Guid"])) ? $resp[0]["BDZ_Guid"] : null,
						"Person_id" => $resp[0]["Person_id"],
						"Server_id" => $data["session"]["server_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->db->query($sql, $sqlParams);
				}
			}
		}
		if ($person_is_identified && in_array(getRegionNick(), ["ekb", "buryatiya"])) {
			//Устанавливается признак БДЗ
			$BDZ_Guid = $callObject->getFirstResultFromQuery("select P.BDZ_Guid from v_Person P  where P.Person_id = :Person_id limit 1", $data);
			$sqlParams = [
				"Person_id" => $data["Person_id"],
				"BDZ_Guid" => $BDZ_Guid,
				"Server_id" => 0,
				"pmUser_id" => $data["pmUser_id"],
			];
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_Person_server(
					Server_id := :Server_id,
					Person_id := :Person_id,
					BDZ_Guid := :BDZ_Guid,
					pmUser_id := :pmUser_id
				);
			";
			$resp = $callObject->queryResult($sql, $sqlParams);
			$callObject->ValidateInsertQuery($resp);
		}
		if (getRegionNick() == "perm") {
			$query = "
				select 
					ps.Person_id as \"Person_id\",
					ps.Person_IsInErz as \"Person_IsInErz\"
				from v_PersonState ps 
				where ps.Person_id = :Person_id
				limit 1
			";
			$result = $callObject->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (!empty($resp[0]["Person_id"]) && !empty($resp[0]["Person_IsInErz"]) && $resp[0]["Person_IsInErz"] == 1) {
					$sql = "
						update Person
						set Person_IsInErz = null
						where Person_id = :Person_id
					";
					$sqlParams = ["Person_id" => $data["Person_id"]];
					$callObject->db->query($sql, $sqlParams);
				}
			}
		}
		if (getRegionNick() == "penza") {
			$newMainFields = $callObject->getMainFields($data);
			if (!is_array($newMainFields)) {
				throw new Exception("Ошибка при получении актуальных атрибутов человека");
			}
			$isInErz = ($newMainFields["Person_IsInErz"] == 2);
			if ($isInErz) {
				foreach ($newMainFields as $field => $value) {
					if ($field == "Person_IsInErz") {
						continue;
					}
					if ($oldMainFields[$field] != $value) {
						$isInErz = false;
						break;
					}
				}
				if (!$isInErz) {
					$sql = "
						update Person
						set Person_IsInErz = 1
						where Person_id = :Person_id
					";
					$sqlParams = ["Person_id" => $data["Person_id"]];
					$callObject->db->query($sql, $sqlParams);
				}
			}
		}
		if (!empty($data["BDZ_Guid"]) && $person_is_identified) {
			$bdzData = $callObject->getBDZPersonData($data);
			if ($bdzData) {
				if ($callObject->checkExistPersonDouble($bdzData["Person_id"], $data["Person_id"])) {
					throw new Exception("Человек уже находится в очереди на объединение двойников");
				}
				$sql = "
					select PersonDoubles_id as \"PersonDoubles_id\",
					       Error_Code as \"Error_Code\",
					       Error_Message as \"Error_Msg\"
					from pd.p_PersonDoubles_ins(
						Person_id := :Person_id,
						Person_did := :Person_did,
						pmUser_id := :pmUser_id
					);
				";
				$sqlParams = [
					"Person_id" => $bdzData["Person_id"],
					"Person_did" => $data["Person_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $callObject->db->query($sql, $sqlParams);
				return [[
					"Error_Msg" => (!$result) ? "Ошибка при выполнении запроса к базе данных (объединение)" : "Человек добавлен в очередь на объединение двойников"
				]];
			}
		}
		return [["success" => true]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function editPersonEvnDate(Person_model $callObject, $data)
	{
		$person_is_identified = false;
		if (!empty($data["person_is_identified"]) && $data["person_is_identified"] == true) {
			$person_is_identified = true;
		}
		if (!isset($_SESSION)) {
			session_start();
		}
		unset($_SESSION["person"]);
		unset($_SESSION["person_short"]);
		session_write_close();
		$data["Date"] = (isset($data["Time"]))
			? "{$data["Date"]} {$data["Time"]}"
			: "{$data["Date"]} 00:00:00";
		$sql = "
			select Error_Message as \"ErrMsg\"
			from dbo.xp_PersonTransferDate(
				Server_id := ?,
				PersonEvn_id := ?,
				PersonEvn_begDT := ?,
				pmUser_id := ?
			);
		";
		if ($data["PersonEvn_id"] > 0) {
			$sqlParams = [$data["Server_id"], $data["PersonEvn_id"], $data["Date"], $data["pmUser_id"]];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		}
		if (!$person_is_identified && getRegionNick() == "kareliya") {
			// если ключевая периодика и не суперадмин и Карелия, то выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
			$query = "
				select 
					pe.Person_id as \"Person_id\",
					pe.PersonEvnClass_SysNick as \"PersonEvnClass_SysNick\",
					ps.Server_pid as \"Server_pid\"
				from
					v_PersonEvn pe 
					inner join v_PersonState ps on ps.Person_id = pe.Person_id
				where pe.PersonEvn_id = :PersonEvn_id
				limit 1
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (!empty($resp[0]["Person_id"])) {
					$BDZaffected = in_array($resp[0]["PersonEvnClass_SysNick"], ["PersonSurName", "PersonFirName", "PersonSecName", "PersonBirthDay", "PersonSex", "PersonSocStatus", "PersonPolisEdNum", "PersonPolis"]);
					if ($BDZaffected && !isSuperAdmin() && $resp[0]["Server_pid"] == 0) {
						$sql = "
							select Error_Message as \"ErrMsg\"
							from dbo.p_Person_server(
								Server_id := :Server_id,
								Person_id := :Person_id,
								BDZ_Guid := :BDZ_Guid,
								pmUser_id := :pmUser_id
							);
						";
						$sqlParams = [
							"BDZ_Guid" => (isset($data["BDZ_Guid"])) ? $data["BDZ_Guid"] : null,
							"Person_id" => $resp[0]["Person_id"],
							"Server_id" => $data["session"]["server_id"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$callObject->db->query($sql, $sqlParams);
					}
				}
			}
		}
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_Deputy(Person_model $callObject, $data)
	{
		if (isset($data["DeputyKind_id"]) && isset($data["DeputyPerson_id"])) {
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonDeputy_del(
					PersonDeputy_id := (select PersonDeputy_id from PersonDeputy  where Person_id := ? limit 1)
				);
			";
			$callObject->db->query($sql, [$data["Person_id"]]);
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonDeputy_ins(
					Server_id := ?,
					Person_id := ?,
					Person_pid := ?,
					DeputyKind_id := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], $data["DeputyPerson_id"], $data["DeputyKind_id"], $data["pmUser_id"]];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		} else {
			// если ни один не задан, то удаляем
			if (!isset($data["DeputyKind_id"]) && !isset($data["DeputyPerson_id"])) {
				$sql = "
					select error_message as \"ErrMsg\"
					from dbo.p_PersonDeputy_del(
						PersonDeputy_id := (select PersonDeputy_id from PersonDeputy  where Person_id := ? limit 1)
					);
				";
				$callObject->db->query($sql, [$data["Person_id"]]);
			}
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @param $evn_types
	 * @return bool
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_Federal_Num(Person_model $callObject, $data, $evn_types)
	{
		if (in_array("Polis", $evn_types)) {
			return false;
		}
		$funcParams = [
			"ObjectName" => "PersonPolisEdNum",
			"ObjectField" => "PersonPolisEdNum_EdNum",
			"ObjectData" => empty($data["Federal_Num"]) ? "" : $data["Federal_Num"],
			"Server_id" => $data["Server_id"],
			"Person_id" => $data["Person_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"pmUser_id" => $data["pmUser_id"],
			"PersonEvnClass_id" => 16
		];
		$callObject->savePersonEvnSimpleAttr($funcParams);
		$cteSql = "
			select PersonEvn_insDT::date AS next
			from v_Person_all 
			where PersonEvnClass_id = 16
			  and Person_id = :Person_id
			  and PersonEvn_insDT > :Federal_begDate
			order by
				PersonEvn_insDT desc,
				PersonEvn_TimeStamp desc
			limit 1
		";
		$sql = "
			update Polis
			set Polis_Num = :Federal_Num
			where polis_id in (
				select Person.Polis_id
				from
					v_Person_all Person 
					left join v_Polis Polis  on Polis.Polis_id = Person.Polis_id
				where Person.PersonEvnClass_id = 8
				  and Person.Person_id = :Person_id
				  and Person.PersonEvn_insDT>=cast(:Federal_begDate as DATE)
				  and (({$cteSql}) is null or Person.PersonEvn_insDT < ({$cteSql}))
				  and Polis.PolisType_id = 4
			)
		";
		$params = [
			"Federal_begDate" => $data["Federal_begDate"],
			"Person_id" => $data["Person_id"],
			"Federal_Num" => empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]
		];
		if ($data["Person_id"] > 0) {
			$callObject->db->query($sql, $params);
		}
		if (!empty($data["Person_id"]) && $data["Person_id"] != 0 && $data["Person_id"] != null) {
			$sql = "select dbo.xp_PersonTransferEvn(Person_id := ?)";
			$callObject->db->query($sql, [$data["Person_id"]]);
		}
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_FamilyStatus_id(Person_model $callObject, $data)
	{
		$PersonFamilyStatus_IsMarried = (empty($data["PersonFamilyStatus_IsMarried"]) ? null : $data["PersonFamilyStatus_IsMarried"]);
		$FamilyStatus_id = (empty($data["FamilyStatus_id"]) ? null : $data["FamilyStatus_id"]);
		if (empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id)) {
			throw new Exception("Хотя бы одно из полей \"Семейное положение\" или \"Состоит в зарегистрированном браке\" должно быть заполнено");
		}
		if (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true) {
			$sel[0]["Server_id"] = $data["Server_id"];
			$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
		} else {
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				select
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\",
					FamilyStatus_id as \"FamilyStatus_id\"
				from v_Person_all 
				where PersonEvnClass_id = 22
				  and PersonEvn_insDT <= (select PersonEvn_insDT from v_PersonEvn  where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id limit 1)
				  and Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
				limit 1
			";
			$sqlParams = [
				"PersonEvn_id" => $data["PersonEvn_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $sqlParams);
			$sel = $result->result_array();
		}
		// если не было, то добавляем атрибут на дату
		if (count($sel) == 0) {
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonFamilyStatus_ins(
					Server_id := ?,
					Person_id := ?,
					PersonFamilyStatus_insDT := ?,
					FamilyStatus_id := ?,
					PersonFamilyStatus_IsMarried := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], "2000-01-01 00:00:00.000", $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data["pmUser_id"]];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		} else {
			// иначе редактируем этот атрибут
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonFamilyStatus_upd(
					PersonFamilyStatus_id := :peid,
					Server_id := :serv_id,
					Person_id := :pid,
					FamilyStatus_id := :FamilyStatus_id,
					PersonFamilyStatus_IsMarried := :PersonFamilyStatus_IsMarried,
					pmUser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"peid" => $sel[0]["PersonEvn_id"],
				"serv_id" => $sel[0]["Server_id"],
				"pid" => $data["Person_id"],
				"FamilyStatus_id" => $FamilyStatus_id,
				"PersonFamilyStatus_IsMarried" => $PersonFamilyStatus_IsMarried,
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_Job(Person_model $callObject, $data)
	{
		$Post_id = (empty($data["Post_id"]) ? null : $data["Post_id"]);
		$Org_id = (empty($data["Org_id"]) ? null : $data["Org_id"]);
		$OrgUnion_id = (empty($data["OrgUnion_id"]) ? null : $data["OrgUnion_id"]);
		if (isset($data["PostNew"]) && !empty($data["PostNew"])) {
			/**@var CI_DB_result $result */
			if (is_numeric($data["PostNew"])) {
				$numPostID = 1;
				$sql = "
					select Post_id as \"Post_id\"
					from v_Post 
					where Post_id = ?
				";
				$result = $callObject->db->query($sql, [$data["PostNew"]]);
			} else {
				$sql = "
					select Post_id as \"Post_id\"
					from v_Post 
					where Post_Name iLIKE ? and Server_id = ?
				";
				$result = $callObject->db->query($sql, [$data["PostNew"], $data["Server_id"]]);
			}
			if (is_object($result)) {
				$sel = $result->result_array();
				if (isset($sel[0])) {
					if ($sel[0]["Post_id"] > 0) {
						$Post_id = $sel[0]["Post_id"];
					}
				} else if (isset($numPostID)) {
					$Post_id = null;
				} else {
					$sql = "
						select post_id as \"Post_id\"
						from dbo.p_Post_ins(
							Post_Name := ?,
							pmUser_id := ?,
							Server_id := ?
						);
					";
					$result = $callObject->db->query($sql, [$data["PostNew"], $data["pmUser_id"], $data["Server_id"]]);
					if (is_object($result)) {
						$sel = $result->result_array();
						if ($sel[0]["Post_id"] > 0) {
							$Post_id = $sel[0]["Post_id"];
						}
					}
				}
			}
		}
		if (isset($data["OrgUnionNew"]) && !empty($data["OrgUnionNew"]) && !empty($data["Org_id"]) && is_numeric($data["Org_id"])) {
			if (is_numeric($data["OrgUnionNew"])) {
				$numOrgUnionID = 1;
				$sql = "
					select OrgUnion_id as \"OrgUnion_id\"
					from v_OrgUnion 
					where OrgUnion_id = ?
				";
				$result = $callObject->db->query($sql, [$data["OrgUnionNew"]]);
			} else {
				$sql = "
					select OrgUnion_id as \"OrgUnion_id\"
					from v_OrgUnion 
					where OrgUnion_Name iLIKE ? and Server_id = ? and Org_id = ?
				";
				$result = $callObject->db->query($sql, [$data["OrgUnionNew"], $data["Server_id"], $data["Org_id"]]);
			}
			if (is_object($result)) {
				$sel = $result->result_array();
				if (isset($sel[0])) {
					if ($sel[0]["OrgUnion_id"] > 0) {
						$OrgUnion_id = $sel[0]["OrgUnion_id"];
					}
				} else if (isset($numOrgUnionID)) {
					$OrgUnion_id = null;
				} else {
					$sql = "
						select OrgUnion_id as \"OrgUnion_id\"
						from dbo.p_OrgUnion_ins(
							OrgUnion_Name := ?,
							Org_id := ?,
							pmUser_id := ?,
							Server_id := ?
						);
					";
					$result = $callObject->db->query($sql, [$data["OrgUnionNew"], $data["Org_id"], $data["pmUser_id"], $data["Server_id"]]);
					if (is_object($result)) {
						$sel = $result->result_array();
						if ($sel[0]["OrgUnion_id"] > 0) {
							$OrgUnion_id = $sel[0]["OrgUnion_id"];
						}
					}
				}
			}
		}
		if (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true) {
			$sel[0]["Server_id"] = $data["Server_id"];
			$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
		} else {
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				select
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\",
					Job_id as \"Job_id\"
				from v_Person_all 
				where PersonEvnClass_id = 12
				  and PersonEvn_insDT <= (select PersonEvn_insDT from v_PersonEvn where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id limit 1)
				  and Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
				limit 1
			";
			$sqlParams = [
				"PersonEvn_id" => $data["PersonEvn_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$sel = $result->result_array();
		}
		if (count($sel) == 0) {
			// если не было, то добавляем атрибут на дату
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonJob_ins(
					Server_id := ?,
					Person_id := ?,
					PersonJob_insDT := ?,
					Org_id := ?,
					OrgUnion_id := ?,
					Post_id := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], "2000-01-01 00:00:00.000", $Org_id, $OrgUnion_id, $Post_id, $data["pmUser_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
		} else {
			// иначе редактируем этот атрибут
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonJob_upd(
					PersonJob_id := :peid,
					Server_id := :serv_id,
					Person_id := :pid,
					Org_id := :Org_id,
					OrgUnion_id := :OrgUnion_id,
					Post_id := :Post_id,
					pmUser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"peid" => $sel[0]["PersonEvn_id"],
				"serv_id" => $sel[0]["Server_id"],
				"pid" => $data["Person_id"],
				"Org_id" => $Org_id,
				"OrgUnion_id" => $OrgUnion_id,
				"Post_id" => $Post_id,
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_PAddress(Person_model $callObject, $data)
	{
		$KLCountry_id = (empty($data["PKLCountry_id"]) ? null : $data["PKLCountry_id"]);
		$KLRgn_id = (empty($data["PKLRGN_id"]) ? null : $data["PKLRGN_id"]);
		$KLSubRgn_id = (empty($data["PKLSubRGN_id"]) ? null : $data["PKLSubRGN_id"]);
		$KLCity_id = (empty($data["PKLCity_id"]) ? null : $data["PKLCity_id"]);
		$KLTown_id = (empty($data["PKLTown_id"]) ? null : $data["PKLTown_id"]);
		$KLStreet_id = (empty($data["PKLStreet_id"]) ? null : $data["PKLStreet_id"]);
		$Address_Zip = (empty($data["PAddress_Zip"]) ? "" : $data["PAddress_Zip"]);
		$Address_House = (empty($data["PAddress_House"]) ? "" : $data["PAddress_House"]);
		$Address_Corpus = (empty($data["PAddress_Corpus"]) ? "" : $data["PAddress_Corpus"]);
		$Address_Flat = (empty($data["PAddress_Flat"]) ? "" : $data["PAddress_Flat"]);
		$PersonSprTerrDop_id = (empty($data["PPersonSprTerrDop_id"]) ? null : $data["PPersonSprTerrDop_id"]);
		/**@var CI_DB_result $result */
		if (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true) {
			$sel[0]["Server_id"] = $data["Server_id"];
			$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
		} else {
			$sql = "
				select
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\",
					PAddress_id as \"PAddress_id\"
				from v_Person_all 
				where PersonEvnClass_id = 11
				  and PersonEvn_insDT <= (select PersonEvn_insDT from v_PersonEvn where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id limit 1)
				  and Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
				limit 1
			";
			$sqlParams = [
				"PersonEvn_id" => $data["PersonEvn_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$sel = $result->result_array();
		}
		// если не было, то добавляем атрибут на дату
		if (count($sel) == 0) {
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonPAddress_ins(
					Server_id := ?,
					Person_id := ?,
					PersonPAddress_insDT := ?,
					KLCountry_id := ?,
					KLRgn_id := ?,
					KLSubRgn_id := ?,
					KLCity_id := ?,
					KLTown_id := ?,
					KLStreet_id := ?,
					Address_Zip := ?,
					Address_House := ?,
					Address_Corpus := ?,
					Address_Flat := ?,
					PersonSprTerrDop_id := ?,
					Address_Address := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], "2000-01-01 00:00:00.000", $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data["pmUser_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
		} else {
			// иначе редактируем этот атрибут
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonPAddress_upd(
					PersonPAddress_id := :peid,
					Server_id := :serv_id,
					Person_id := :pid,
					KLCountry_id := :KLCountry_id,
					KLAreaType_id := null,
					KLRgn_id := :KLRgn_id,
					KLSubRgn_id := :KLSubRgn_id,
					KLCity_id := :KLCity_id,
					KLTown_id := :KLTown_id,
					KLStreet_id := :KLStreet_id,
					Address_Zip := :Address_Zip,
					Address_House := :Address_House,
					Address_Corpus := :Address_Corpus,
					Address_Flat := :Address_Flat,
					PersonSprTerrDop_id := :PersonSprTerrDop_id,
					Address_Address := null,
					pmUser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"peid" => $sel[0]["PersonEvn_id"],
				"serv_id" => $sel[0]["Server_id"],
				"pid" => $data["Person_id"],
				"KLCountry_id" => $KLCountry_id,
				"KLRgn_id" => $KLRgn_id,
				"KLSubRgn_id" => $KLSubRgn_id,
				"KLCity_id" => $KLCity_id,
				"KLTown_id" => $KLTown_id,
				"KLStreet_id" => $KLStreet_id,
				"Address_Zip" => $Address_Zip,
				"Address_House" => $Address_House,
				"Address_Corpus" => $Address_Corpus,
				"Address_Flat" => $Address_Flat,
				"PersonSprTerrDop_id" => $PersonSprTerrDop_id,
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_UAddress(Person_model $callObject, $data)
	{
		$KLCountry_id = (empty($data["UKLCountry_id"]) ? null : $data["UKLCountry_id"]);
		$KLRgn_id = (empty($data["UKLRGN_id"]) ? null : $data["UKLRGN_id"]);
		$KLSubRgn_id = (empty($data["UKLSubRGN_id"]) ? null : $data["UKLSubRGN_id"]);
		$KLCity_id = (empty($data["UKLCity_id"]) ? null : $data["UKLCity_id"]);
		$KLTown_id = (empty($data["UKLTown_id"]) ? null : $data["UKLTown_id"]);
		$KLStreet_id = (empty($data["UKLStreet_id"]) ? null : $data["UKLStreet_id"]);
		$Address_Zip = (empty($data["UAddress_Zip"]) ? "" : $data["UAddress_Zip"]);
		$Address_House = (empty($data["UAddress_House"]) ? "" : $data["UAddress_House"]);
		$Address_Corpus = (empty($data["UAddress_Corpus"]) ? "" : $data["UAddress_Corpus"]);
		$Address_Flat = (empty($data["UAddress_Flat"]) ? "" : $data["UAddress_Flat"]);
		$PersonSprTerrDop_id = (empty($data["UPersonSprTerrDop_id"]) ? null : $data["UPersonSprTerrDop_id"]);
		/**@var CI_DB_result $result */
		if (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true) {
			$sel[0]["Server_id"] = $data["Server_id"];
			$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
		} else {
			$sql = "
				select
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\",
					UAddress_id as \"UAddress_id\"
				from v_Person_all 
				where PersonEvnClass_id = 10
				  and PersonEvn_insDT <= (select PersonEvn_insDT from v_PersonEvn where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id limit 1)
				  and Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
				limit 1
			";
			$sqlParams = [
				"PersonEvn_id" => $data["PersonEvn_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$sel = $result->result_array();
		}
		// если не было, то добавляем атрибут на дату
		if (count($sel) == 0) {
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonUAddress_ins(
					Server_id := ?,
					Person_id := ?,
					PersonUAddress_insDT := ?,
					KLCountry_id := ?,
					KLRgn_id := ?,
					KLSubRgn_id := ?,
					KLCity_id := ?,
					KLTown_id := ?,
					KLStreet_id := ?,
					Address_Zip := ?,
					Address_House := ?,
					Address_Corpus := ?,
					Address_Flat := ?,
					PersonSprTerrDop_id := ?,
					Address_Address := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], "2000-01-01 00:00:00.000", $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data["pmUser_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
		} else {
			// иначе редактируем этот атрибут
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonUAddress_upd(
					PersonUAddress_id := :peid,
					Server_id := :serv_id,
					Person_id := :pid,
					KLCountry_id := :KLCountry_id,
					KLAreaType_id := null,
					KLRgn_id := :KLRgn_id,
					KLSubRgn_id := :KLSubRgn_id,
					KLCity_id := :KLCity_id,
					KLTown_id := :KLTown_id,
					KLStreet_id := :KLStreet_id,
					Address_Zip := :Address_Zip,
					Address_House := :Address_House,
					Address_Corpus := :Address_Corpus,
					Address_Flat := :Address_Flat,
					PersonSprTerrDop_id := :PersonSprTerrDop_id,
					Address_Address := null,
					pmUser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"peid" => $sel[0]["PersonEvn_id"],
				"serv_id" => $sel[0]["Server_id"],
				"pid" => $data["Person_id"],
				"KLCountry_id" => $KLCountry_id,
				"KLRgn_id" => $KLRgn_id,
				"KLSubRgn_id" => $KLSubRgn_id,
				"KLCity_id" => $KLCity_id,
				"KLTown_id" => $KLTown_id,
				"KLStreet_id" => $KLStreet_id,
				"Address_Zip" => $Address_Zip,
				"Address_House" => $Address_House,
				"Address_Corpus" => $Address_Corpus,
				"Address_Flat" => $Address_Flat,
				"PersonSprTerrDop_id" => $PersonSprTerrDop_id,
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_BAddress(Person_model $callObject, $data)
	{
		$Address_Address = trim(empty($data["BAddress_Address"]) ? null : $data["BAddress_Address"]);
		$KLCountry_id = (empty($data["BKLCountry_id"]) ? null : $data["BKLCountry_id"]);
		$KLRgn_id = (empty($data["BKLRGN_id"]) ? null : $data["BKLRGN_id"]);
		$KLRgnSocr_id = (empty($data["BKLRGNSocr_id"]) ? null : $data["BKLRGNSocr_id"]);
		$KLSubRgn_id = (empty($data["BKLSubRGN_id"]) ? null : $data["BKLSubRGN_id"]);
		$KLSubRgnSocr_id = (empty($data["BKLSubRGNSocr_id"]) ? null : $data["BKLSubRGNSocr_id"]);
		$KLCity_id = (empty($data["BKLCity_id"]) ? null : $data["BKLCity_id"]);
		$KLCitySocr_id = (empty($data["BKLCitySocr_id"]) ? null : $data["BKLCitySocr_id"]);
		$KLTown_id = (empty($data["BKLTown_id"]) ? null : $data["BKLTown_id"]);
		$KLTownSocr_id = (empty($data["BKLTownSocr_id"]) ? null : $data["BKLTownSocr_id"]);
		$KLStreet_id = (empty($data["BKLStreet_id"]) ? null : $data["BKLStreet_id"]);
		$KLStreetSocr_id = (empty($data["BKLStreetSocr_id"]) ? null : $data["BKLStreetSocr_id"]);
		$Address_Zip = (empty($data["BAddress_Zip"]) ? "" : $data["BAddress_Zip"]);
		$Address_House = (empty($data["BAddress_House"]) ? "" : $data["BAddress_House"]);
		$Address_Corpus = (empty($data["BAddress_Corpus"]) ? "" : $data["BAddress_Corpus"]);
		$Address_Flat = (empty($data["BAddress_Flat"]) ? "" : $data["BAddress_Flat"]);
		$PersonSprTerrDop_id = (empty($data["BPersonSprTerrDop_id"]) ? null : $data["BPersonSprTerrDop_id"]);
		// Сохранение данных стран кроме РФ, которые ранее отсутствовали
		list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) = $callObject->saveAddressAll($data["Server_id"], $data["pmUser_id"], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
		/**@var CI_DB_result $result */
		$sql = "
			select 
				Address_id as \"Address_id\",
				PersonBirthPlace_id as \"PersonBirthPlace_id\"
			from PersonBirthPlace 
			where Person_id = ?
		";
		$result = $callObject->db->query($sql, [$data["Person_id"]]);
		$sel = $result->result_array();
		if (!is_array($sel) || count($sel) == 0) {
			$sql = "
				select Error_Message as \"ErrMsg\", Address_id as \"Address_id\"
				from dbo.p_Address_ins(
					Server_id := ?,
					KLCountry_id := ?,
					KLRgn_id := ?,
					KLSubRgn_id := ?,
					KLCity_id := ?,
					KLTown_id := ?,
					KLStreet_id := ?,
					Address_Zip := ?,
					Address_House := ?,
					Address_Corpus := ?,
					Address_Flat := ?,
					PersonSprTerrDop_id := ?,
					Address_Address := ?,
					pmUser_id := ?
				); 
			";
			$sqlParams = [$data["Server_id"], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, $Address_Address, $data["pmUser_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
			$address_id = $result->result_array();
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonBirthPlace_ins(
					Person_id := ?,
					Address_id := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Person_id"], $address_id[0]["Address_id"], $data["pmUser_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
		} else {
			$arr = [$KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $Address_Address];
			$delete = true;
			foreach ($arr as $key) {
				if (!empty($key)) {
					$delete = false;
				}
			}
			if (!$delete) {
				$sql = "
					select Error_Message as \"ErrMsg\"
					from dbo.p_Address_upd(
						Server_id := :serv_id,
						Address_id := :Address_id,
						KLAreaType_id := null,
						KLCountry_id := :KLCountry_id,
						KLRgn_id := :KLRgn_id,
						KLSubRgn_id := :KLSubRgn_id,
						KLCity_id := :KLCity_id,
						KLTown_id := :KLTown_id,
						KLStreet_id := :KLStreet_id,
						Address_Zip := :Address_Zip,
						Address_House := :Address_House,
						Address_Corpus := :Address_Corpus,
						Address_Flat := :Address_Flat,
						PersonSprTerrDop_id := :PersonSprTerrDop_id,
						Address_Address := :Address_Address,
						KLAreaStat_id := null,
						pmUser_id := :pmUser_id
					); 
				";
				$sqlParams = [
					"serv_id" => $data["Server_id"],
					"Address_id" => $sel[0]["Address_id"],
					"KLCountry_id" => $KLCountry_id,
					"KLRgn_id" => $KLRgn_id,
					"KLSubRgn_id" => $KLSubRgn_id,
					"KLCity_id" => $KLCity_id,
					"KLTown_id" => $KLTown_id,
					"KLStreet_id" => $KLStreet_id,
					"Address_Zip" => $Address_Zip,
					"Address_House" => $Address_House,
					"Address_Corpus" => $Address_Corpus,
					"Address_Flat" => $Address_Flat,
					"PersonSprTerrDop_id" => $PersonSprTerrDop_id,
					"Address_Address" => $Address_Address,
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $callObject->db->query($sql, $sqlParams);
				$callObject->ValidateInsertQuery($result);
			} else {
				$sql = "
                    select Error_Message as \"ErrMsg\"
					from dbo.p_PersonBirthPlace_del(PersonBirthPlace_id := ?);
				";
				$result = $callObject->db->query($sql, [$sel[0]["PersonBirthPlace_id"]]);
				$callObject->ValidateInsertQuery($result);
			}
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_Document(Person_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$DocumentType_id = (empty($data["DocumentType_id"]) ? null : $data["DocumentType_id"]);
		$OrgDep_id = (empty($data["OrgDep_id"]) ? null : $data["OrgDep_id"]);
		$Document_Ser = (empty($data["Document_Ser"]) ? "" : $data["Document_Ser"]);
		$Document_Num = (empty($data["Document_Num"]) ? "" : $data["Document_Num"]);
		$Document_begDate = empty($data["Document_begDate"]) ? null : $data["Document_begDate"];
		// получаем последний атрибут, который был до этого Evn
		if (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true) {
			$sel[0]["Server_id"] = $data["Server_id"];
			$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
		} else {
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				select
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\",
					Document_id as \"Document_id\"
				from v_Person_all 
				where PersonEvnClass_id = 9
				  and PersonEvn_insDT <= (select PersonEvn_insDT from v_PersonEvn where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id limit 1)
				  and Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
                limit 1
			";
			$sqlParams = [
				"PersonEvn_id" => $data["PersonEvn_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$sel = $result->result_array();
		}
		$funcParams = [
			"DocumentType_id" => $DocumentType_id,
			"Document_begDate" => $Document_begDate,
			"Person_id" => $data["Person_id"],
			"PersonEvn_id" => isset($sel[0]) ? $sel[0]["PersonEvn_id"] : null,
			"Server_id" => $data["Server_id"],
			"pmUser_id" => $data["pmUser_id"],
			"type" => isset($sel[0]) ? "upd" : "ins",
		];
		$resp = $callObject->validateDocument($funcParams);
		if (!$callObject->isSuccessful($resp)) {
			return $resp;
		}
		if (count($sel) == 0) {
			// если не было, то добавляем атрибут на дату
			$sql = "
				select Document_id as \"Document_id\", Error_Message as \"ErrMsg\"
				from dbo.p_PersonDocument_ins(
					Server_id := ?,
					Person_id := ?,
					PersonDocument_insDT := ?,
					DocumentType_id := ?,
					OrgDep_id := ?,
					Document_Ser := ?,
					Document_Num := ?,
					Document_begDate := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], "2000-01-01 00:00:00.000", $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data["pmUser_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
			$resp = $result->result_array();
			$callObject->_setSaveResponse("Document_id", $resp[0]["Document_id"]);
		} else {
			// иначе редактируем этот атрибут
			$sql = "
				select Document_id as \"Document_id\", Error_Message as \"ErrMsg\"
				from dbo.p_PersonDocument_upd(
					PersonDocument_id := :peid,
					Server_id := :serv_id,
					Person_id := :pid,
					DocumentType_id := :DocumentType_id,
					OrgDep_id := :OrgDep_id,
					Document_Ser := :Document_Ser,
					Document_Num := :Document_Num,
					Document_begDate := :Document_begDate,
					Document_endDate := null,
					pmUser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"peid" => $sel[0]["PersonEvn_id"],
				"serv_id" => $sel[0]["Server_id"],
				"pid" => $data["Person_id"],
				"DocumentType_id" => $DocumentType_id,
				"OrgDep_id" => $OrgDep_id,
				"Document_Ser" => $Document_Ser,
				"Document_Num" => $Document_Num,
				"Document_begDate" => $Document_begDate,
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
			$resp = $result->result_array();
			$callObject->_setSaveResponse("Document_id", $resp[0]["Document_id"]);
		}
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function editPersonEvnAttributeNew_NationalityStatus(Person_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$KLCountry_id = empty($data["KLCountry_id"]) ? null : $data["KLCountry_id"];
		$NationalityStatus_IsTwoNation = !empty($data["NationalityStatus_IsTwoNation"]) && $data["NationalityStatus_IsTwoNation"] ? 2 : 1;
		$LegalStatusVZN_id = empty($data["LegalStatusVZN_id"]) ? null : $data["LegalStatusVZN_id"];
		// получаем последний атрибут, который был до этого Evn
		if (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true) {
			$sel[0]["Server_id"] = $data["Server_id"];
			$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
		} else {
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				select 
					P.PersonEvn_id as \"PersonEvn_id\",
					P.Server_id as \"Server_id\",
					NS.NationalityStatus_id as \"NationalityStatus_id\",
					NS.LegalStatusVZN_id as \"LegalStatusVZN_id\",
					P.PersonEvn_insDT as \"PersonEvn_insDT\"
				from
					v_Person_all P 
					left join v_NationalityStatus NS  on NS.NationalityStatus_id = P.NationalityStatus_id
				where P.PersonEvnClass_id = 23
				  and P.PersonEvn_insDT <= (select PersonEvn_insDT from v_PersonEvn where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id limit 1)
				  and P.Person_id = :Person_id
				order by
					P.PersonEvn_insDT desc,
					P.PersonEvn_TimeStamp desc
				limit 1
			";
			$sqlParams = [
				"PersonEvn_id" => $data["PersonEvn_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$sel = $result->result_array();
		}
		$funcParams = [
			"KLCountry_id" => $KLCountry_id,
			"Person_id" => $data["Person_id"],
			"PersonEvn_id" => isset($sel[0]) ? $sel[0]["PersonEvn_id"] : null
		];
		$resp = $callObject->validateNationalityStatus($funcParams);
		if (!$callObject->isSuccessful($resp)) {
			return $resp;
		}
		if (count($sel) == 0) {
			// если не было, то добавляем атрибут на дату
			$sql = "
				select NationalityStatus_id as \"NationalityStatus_id\", Error_Message as \"ErrMsg\"
				from dbo.p_PersonNationalityStatus_ins(
					Server_id := :serv_id,
					Person_id := :pid,
					PersonNationalityStatus_insDT := :ins_dt,
					KLCountry_id := :KLCountry_id,
					NationalityStatus_IsTwoNation := :NationalityStatus_IsTwoNation,
					LegalStatusVZN_id := :LegalStatusVZN_id,
					pmUser_id := :pmUser_id
				)
			";
			$sqlParams = [
				"serv_id" => $data["Server_id"],
				"pid" => $data["Person_id"],
				"ins_dt" => "2000-01-01 00:00:00.000",
				"KLCountry_id" => $KLCountry_id,
				"NationalityStatus_IsTwoNation" => $NationalityStatus_IsTwoNation,
				"LegalStatusVZN_id" => $LegalStatusVZN_id,
				"pmUser_id" => $data["pmUser_id"],
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
			$resp = $result->result_array();
			$callObject->_setSaveResponse("NationalityStatus_id", $resp[0]["NationalityStatus_id"]);
		} else {
			// иначе редактируем этот атрибут
			$sql = "
				select NationalityStatus_id as \"NationalityStatus_id\", Error_Message as \"ErrMsg\"
				from dbo.p_PersonNationalityStatus_upd(
					PersonNationalityStatus_id := :peid,
					Server_id := :serv_id,
					Person_id := :pid,
					KLCountry_id := :KLCountry_id,
					NationalityStatus_IsTwoNation := :NationalityStatus_IsTwoNation,
					LegalStatusVZN_id := :LegalStatusVZN_id,
					pmUser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"peid" => $sel[0]["PersonEvn_id"],
				"serv_id" => $sel[0]["Server_id"],
				"pid" => $data["Person_id"],
				"KLCountry_id" => $KLCountry_id,
				"NationalityStatus_IsTwoNation" => $NationalityStatus_IsTwoNation,
				"LegalStatusVZN_id" => $LegalStatusVZN_id,
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($result);
			$resp = $result->result_array();
			$callObject->_setSaveResponse("NationalityStatus_id", $resp[0]["NationalityStatus_id"]);
		}
		return true;
	}
}