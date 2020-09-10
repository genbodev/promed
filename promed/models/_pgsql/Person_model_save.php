<?php

class Person_model_save
{
	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function savePersonSmoData(Person_model $callObject, $data)
	{
		$sql = "
            select Error_Message as \"ErrMsg\"
			from dbo.p_PersonSocStatus_ins(
				Server_id := ?,
				Person_id := ?,
				SocStatus_id := ?,
				pmUser_id := ?
			);
		";
		$sqlParams = [$data["Server_id"], $data["ID"], $data["ID_STATUS"], $data["pmUser_id"]];
		$res = $callObject->db->query($sql, $sqlParams);
		$callObject->ValidateInsertQuery($res);

		// получаем идешник страховой
		$sql = "
			select OrgSmo_id as \"OrgSmo_id\"
			from
				OrgSmo osm 
				inner join Org og on og.Org_id = osm.Org_id and Org_Code = ?
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, [$data["SMO"]]);
		$sel = $res->result_array();
		if (count($sel) == 0) {
			return false;
		}
		$OrgSmo_id = $sel[0]["OrgSmo_id"];
		// но сначала получаем данные текущего полиса
		$sql = "
			select
				pls.OMSSprTerr_id as \"OMSSprTerr_id\",
				pls.PolisType_id as \"PolisType_id\",
				pls.OrgSMO_id as \"OrgSMO_id\",
				pls.Polis_Ser as \"Polis_Ser\",
				pls.PolisFormType_id as \"PolisFormType_id\",
				pls.Polis_Num as \"Polis_Num\",
				to_char(pls.Polis_begDate::timestamp, 'YYYYMMDD') as \"Polis_begDate\",
				to_char(pls.Polis_endDate::timestamp, 'YYYYMMDD') as \"Polis_endDate\"
			from
				Polis pls
				inner join v_PersonState ps on ps.Polis_id = pls.Polis_id
			where ps.Person_id = ?
		";
		$res = $callObject->db->query($sql, [$data["ID"]]);
		$sel = $res->result_array();
		if (count($sel) == 0) {
			return false;
		}
		// сохраняем полис
		$OmsSprTerr_id = (empty($sel[0]["OMSSprTerr_id"]) ? null : $sel[0]["OMSSprTerr_id"]);
		$PolisType_id = (empty($sel[0]["PolisType_id"]) ? null : $sel[0]["PolisType_id"]);
		$Polis_Ser = (empty($sel[0]["Polis_Ser"]) ? "" : $sel[0]["Polis_Ser"]);
		$PolisFormType_id = (empty($sel[0]["PolisFormType_id"]) ? null : $sel[0]["PolisFormType_id"]);
		$Polis_Num = (empty($data["POL_NUM"]) ? "" : $data["POL_NUM"]);
		$Polis_begDate = empty($sel[0]["Polis_begDate"]) ? null : $sel[0]["Polis_begDate"];
		$Polis_endDate = empty($sel[0]["Polis_endDate"]) ? null : $sel[0]["Polis_endDate"];
		if (isset($OmsSprTerr_id) && (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate)) {
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_PersonPolis_ins(
					Server_id := ?,
					Person_id := ?,
					PersonPolis_insDT := ?,
					OmsSprTerr_id := ?,
					PolisType_id := ?,
					OrgSmo_id := ?,
					Polis_Ser := ?,
					PolisFormType_id :=?,
					PolisFormType_id := ?,
					Polis_Num := ?,
					Polis_begDate := ?,
					Polis_endDate := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["ID"], $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser, $PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data["pmUser_id"]];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		}
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function savePersonEvnSimpleAttr(Person_model $callObject, $data)
	{
		if (!(isset($data["AllowEmpty"]) && $data["AllowEmpty"] === true) && (!isset($data["ObjectData"]) || $data["ObjectData"] == "")) {
			return false;
		}
		$sel = [];
		$insDT = (isset($data["insDT"])) ? $data["insDT"] : null;
		if (isset($data["cancelCheckEvn"]) && $data["cancelCheckEvn"] == true) {
			$sel[0]["Server_id"] = $data["Server_id"];
			$sel[0]["PersonEvn_id"] = $data["PersonEvn_id"];
		} else {
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				select
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\"
				from v_PersonEvn
				where PersonEvnClass_id = {$data["PersonEvnClass_id"]}
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
			/**@var CI_DB_result $res */
			$res = $callObject->db->query($sql, $sqlParams);
			$sel = $res->result_array();
		}
		// если не было, то добавляем атрибут на дату
		$ObjectName = $data["ObjectName"];
		if (count($sel) == 0 || (isset($data["insPeriodic"]) && $data["insPeriodic"] == true)) {
			if ($insDT == null) {
				$insDT = "2000-01-01 00:00:00.000";
			}
			if (isset($data["insPeriodic"]) && $data["insPeriodic"] == true) {
				// проверяем наличие периодики
				$sql = "
					select {$ObjectName}_id as \"{$ObjectName}_id\"
					from v_{$ObjectName}
					where {$data["ObjectField"]} = :ObjectData
					  and {$ObjectName}_insDT = :insDT
                    limit 1
				";
				$sqlParams = [
					"ObjectData" => $data["ObjectData"],
					"insDT" => $insDT
				];
				$resp_check = $callObject->queryResult($sql, $sqlParams);
				if (!empty($resp_check[0]["{$ObjectName}_id"])) {
					// дубль периодики нам не нужен.
					return true;
				}
			}
			$selectString = "Error_Message as \"ErrMsg\"";
			$sql = "
				select {$selectString}
				from dbo.p_{$ObjectName}_ins(
					Server_id := ?,
					Person_id := ?,
					{$ObjectName}_insDT := ?,
					{$data["ObjectField"]} := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], $insDT, $data["ObjectData"], $data["pmUser_id"]];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		} else {
			// иначе редактируем этот атрибут
			$AdditFields = "";
			$AdditValues = [];
			if (isset($data["AdditFields"])) {
				$arr = array_keys($data["AdditFields"]);
				$AdditFields = $arr[count($arr) - 1] . " := ?, ";
				$AdditValues = array_values($data["AdditFields"]);
			}
			if ($sel[0]["PersonEvn_id"] <= 0) {
				return false;
			}
			if ($insDT != null) {
				$selectString = "Error_Message as \"ErrMsg\"";
				$sql = "
					select {$selectString}
					from dbo.p_{$ObjectName}_upd(
						Server_id := ?,
						Person_id := ?,
						{$ObjectName}_insDT := ?,
						{$ObjectName}_id := ?,
						{$data["ObjectField"]} := ?,
						{$AdditFields}
						pmUser_id := ?
					);
				";
				$sqlParams = array_merge([$sel[0]["Server_id"], $data["Person_id"], $insDT, $sel[0]["PersonEvn_id"], $data["ObjectData"], $data["pmUser_id"]], $AdditValues);
				$res = $callObject->db->query($sql, $sqlParams);
			} else {
				$selectString = "Error_Message as \"ErrMsg\"";
				$sql = "
					select {$selectString}
					from dbo. p_{$ObjectName}_upd(
						Server_id := ?,
						Person_id := ?,
						{$ObjectName}_id := ?,
						{$data["ObjectField"]} := ?,
						{$AdditFields}
						pmUser_id := ?
					);
				";
				$sqlParams = array_merge([$sel[0]["Server_id"], $data["Person_id"], $sel[0]["PersonEvn_id"], $data["ObjectData"], $data["pmUser_id"]], $AdditValues);
				$res = $callObject->db->query($sql, $sqlParams);
			}
			$callObject->ValidateInsertQuery($res);
		}
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonEvalEditWindow(Person_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		if ($data["EvalType"]) {
			$type = $data["EvalType"];
		}
		$p_type = "p_Person{$type}_ins";
		if (!empty($data["PersonEval_id"])) {
			$p_type = "p_Person{$type}_upd";
		} elseif ($data["EvalMeasureType_id"] == 1) {
			$whereString = "where {$type}MeasureType_id = 1 and Person_id = :Person_id";
			$query = "
				select Person{$type}_id as \"PersonEval_id\"
				from v_Person{$type}
				{$whereString}
			";
			$result = $callObject->db->query($query, ["Person_id" => $data["Person_id"]]);
			if (is_object($result)) {
				$res = $result->result_array();
				if (count($res) > 0) {
					$p_type = "p_Person{$type}_upd";
					$data["PersonEval_id"] = $res[0]["PersonEval_id"];
				}
			}
		}
		$selectString = "Error_Message as \"ErrMsg\"";
		$sql = "
			select {$selectString}
			from dbo.{$p_type}(
				Person{$type}_id := :PersonEval_id,
				Person_id := :Person_id,
				Person{$type}_setDT := :PersonEval_setDT,
				Person{$type}_{$type} := :PersonEval_Value,
				Person{$type}_IsAbnorm := :PersonEval_IsAbnorm,
				{$type}AbnormType_id := :EvalAbnormType_id,
				{$type}MeasureType_id := :EvalMeasureType_id,
				Okei_id := :Okei_id,
				Evn_id := :Evn_id,
				pmUser_id := :pmUser_id,
				Server_id := :Server_id
			);
		";
		$sqlParams = [
			"PersonEval_id" => $data["PersonEval_id"],
			"Person_id" => $data["Person_id"],
			"PersonEval_setDT" => $data["PersonEval_setDT"],
			"PersonEval_Value" => $data["PersonEval_Value"],
			"PersonEval_IsAbnorm" => $data["PersonEval_IsAbnorm"],
			"EvalAbnormType_id" => $data["EvalAbnormType_id"],
			"EvalMeasureType_id" => $data["EvalMeasureType_id"],
			"Okei_id" => $data["Okei_id"],
			"pmUser_id" => $data["pmUser_id"],
			"Server_id" => $data["Server_id"],
			"Evn_id" => !empty($data["Evn_id"]) ? $data["Evn_id"] : null,
		];
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function savePersonEvnSimpleAttrNew(Person_model $callObject, $data)
	{
		if (!(isset($data["AllowEmpty"]) && $data["AllowEmpty"] === true) && (!isset($data["ObjectData"]) || $data["ObjectData"] == "")) {
			return false;
		}
		// получаем последний атрибут, который был до этого Evn
		$sql = "
			select
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from v_PersonEvn 
			where PersonEvnClass_id = {$data["PersonEvnClass_id"]}
			  and Person_id = :Person_id
			  and PersonEvn_insDT <= :insDT
			order by
				PersonEvn_insDT desc,
				PersonEvn_TimeStamp desc
            limit 1					
		";
		$sqlParams = ["Person_id" => $data["Person_id"], "insDT" => $data["insDT"]];
		/**
		 * @var CI_DB_result $res
		 * @var CI_DB_result $resob
		 */
		$res = $callObject->db->query($sql, $sqlParams);
		$sel = $res->result_array();
		$add = false;
		// если отсутсвует или не совпадает со значением то добавляем новое
		if (count($sel) == 0) {
			$add = true;
		} else {
			$sel = $sel[0];
			$sqlob = "
				select {$data["ObjectField"]} as \"{$data["ObjectField"]}\"
				from {$data["ObjectName"]} 
				where {$data["ObjectName"]}_id = {$sel["PersonEvn_id"]}
			    limit 1
			";
			$resob = $callObject->db->query($sqlob);
			$selob = $resob->result_array();
			if (count($selob) > 0) {
				$selob = $selob[0];
				if ($selob[$data["ObjectField"]] != $data["ObjectData"]) {
					$add = true;
				}
			} else {
				$add = true;
			}
		}
		if ($add) {
			$selectString = "ErrMsg as \"ErrMsg\"";
			$sql = "
				select {$selectString}
				from dbo.p_{$data["ObjectName"]}_ins(
					Server_id := ?,
					Person_id := ?,
					{$data["ObjectName"]}_insDT := ?,
					{$data["ObjectField"]} := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$data["Server_id"], $data["Person_id"], $data["insDT"], $data["ObjectData"], $data["pmUser_id"]];
			$res = $callObject->db->query($sql, $sqlParams);
			$callObject->ValidateInsertQuery($res);
		}
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveAttributeOnDate(Person_model $callObject, $data)
	{
		// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан,
		// чтобы лишний раз в сессию не писать, экономим на спичках
		if (!isset($_SESSION)) {
			session_start();
		}
		if (isset($data["session"]["person"]) && isset($data["session"]["person"]["Person_id"]) && isset($data["Person_id"]) && $data["Person_id"] == $data["session"]["person"]["Person_id"]) {
			unset($_SESSION["person"]);
		}
		if (isset($data["session"]["person_short"]) && isset($data["session"]["person_short"]["Person_id"]) && isset($data["Person_id"]) && $data["Person_id"] == $data["session"]["person_short"]["Person_id"]) {
			unset($_SESSION["person_short"]);
		}
		session_write_close();
		$oldMainFields = [];
		if (getRegionNick() == "penza") {
			$oldMainFields = $callObject->getMainFields($data);
			if (!is_array($oldMainFields)) {
				throw new Exception("Ошибка при получении актуальных атрибутов человека");
			}
		}
		$is_superadmin = isSuperadmin();
		$server_id = $data["Server_id"];
		$pid = $data["Person_id"];
		$ins_dt = substr(trim($data["Date"]), 6, 4) . "-" . substr(trim($data["Date"]), 3, 2) . "-" . substr(trim($data["Date"]), 0, 2) . " " . $data["Time"] . ":00.000";
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
					$Polis_begDate = empty($data["Polis_begDate"]) ? null : substr(trim($data["Polis_begDate"]), 6, 4) . "-" . substr(trim($data["Polis_begDate"]), 3, 2) . "-" . substr(trim($data["Polis_begDate"]), 0, 2);
					$Polis_endDate = empty($data["Polis_endDate"]) ? null : substr(trim($data["Polis_endDate"]), 6, 4) . "-" . substr(trim($data["Polis_endDate"]), 3, 2) . "-" . substr(trim($data["Polis_endDate"]), 0, 2);
					// для прав суперадмина
					$Federal_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
					if ($PolisType_id == 4) {
						$Polis_Num = $Federal_Num;
						$data["Polis_Num"] = $Federal_Num;
					}
					if (!empty($Federal_Num)) {
						$fsql = "
							select
								PersonEvn_id as \"PersonEvn_id\",
								Server_id as \"Server_id\"
							from v_Person_all
							where PersonEvnClass_id = 16
							  and Person_id = :Person_id
							  and person_Ednum = :edNum
							  and PersonEvn_insDT <= :begdate
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
							limit 1
						";
						$sqlParams = [
							"Person_id" => $data["Person_id"],
							"edNum" => $Federal_Num,
							"begdate" => $Polis_begDate
						];
						/**@var CI_DB_result $fres */
						$fres = $callObject->db->query($fsql, $sqlParams);
						$fsel = $fres->result_array();
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
							$sqlParams = [$server_id, $pid, $Polis_begDate, $Federal_Num, $data["pmUser_id"]];
							$res = $callObject->db->query($sql, $sqlParams);
							$callObject->ValidateInsertQuery($res);
						}
					}
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonPolis_ins(
							Server_id := ?,
							Person_id := ?,
							PersonPolis_insDT := ?,
							OmsSprTerr_id := ?,
							PolisType_id := ?,
							OrgSmo_id := ?,
							Polis_Ser := ?,
							PolisFormType_id :=?,
							Polis_Num := ?,
							Polis_begDate := ?,
							Polis_endDate := ?,
							pmUser_id := ?
						);
					";
					if (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate) {
						$sqlParams = [$server_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser, $PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data["pmUser_id"]];
						$res = $callObject->db->query($sql, $sqlParams);
						$callObject->ValidateInsertQuery($res);
					}
					break;
				case "NationalityStatus":
					$KLCountry_id = (empty($data["KLCountry_id"]) ? null : $data["KLCountry_id"]);
					$NationalityStatus_IsTwoNation = (!empty($data["NationalityStatus_IsTwoNation"]) ? 2 : 1);
					$LegalStatusVZN_id = (empty($data["LegalStatusVZN_id"]) ? null : $data["LegalStatusVZN_id"]);

					$funcParams = [
						"KLCountry_id" => $KLCountry_id,
						"Person_id" => $pid,
						"PersonEvn_insDT" => $ins_dt
					];
					$resp = $callObject->validateNationalityStatus($funcParams);
					if (!$callObject->isSuccessful($resp)) {
						return $resp;
					}
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonNationalityStatus_ins(
							Server_id := ?,
							Person_id := ?,
							PersonNationalityStatus_insDT := ?,
							KLCountry_id := ?,
							NationalityStatus_IsTwoNation := ?,
							LegalStatusVZN_id := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $KLCountry_id, $NationalityStatus_IsTwoNation, $LegalStatusVZN_id, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Document":
					$DocumentType_id = (empty($data["DocumentType_id"]) ? null : $data["DocumentType_id"]);
					$OrgDep_id = (empty($data["OrgDep_id"]) ? null : $data["OrgDep_id"]);
					$Document_Ser = (empty($data["Document_Ser"]) ? "" : $data["Document_Ser"]);
					$Document_Num = (empty($data["Document_Num"]) ? "" : $data["Document_Num"]);
					$Document_begDate = empty($data["Document_begDate"]) ? null : substr(trim($data["Document_begDate"]), 6, 4) . "-" . substr(trim($data["Document_begDate"]), 3, 2) . "-" . substr(trim($data["Document_begDate"]), 0, 2);

					$funcParams = [
						"DocumentType_id" => $DocumentType_id,
						"Document_begDate" => $Document_begDate,
						"Person_id" => $pid,
						"PersonEvn_insDT" => $ins_dt,
						"Server_id" => $server_id,
						"pmUser_id" => $data["pmUser_id"],
						"type" => "ins",
					];
					$resp = $callObject->validateDocument($funcParams);
					if (!$callObject->isSuccessful($resp)) {
						return $resp;
					}
					$sql = "
						select Error_Message as \"ErrMsg\"
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
					$sqlParams = [$server_id, $pid, $ins_dt, $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data["pmUser_id"]];
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
					$PersonSprTerrDop_id = (empty($data["UPersonSprTerrDop_id"]) ? null : $data["UPersonSprTerrDop_id"]);
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
					$sqlParams = [$server_id, $pid, $ins_dt, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, null, $data["pmUser_id"]];
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
					$Address_Flat = (empty($data["PAddress_Flat"]) ? "" : $data["PAddress_Flat"]);
					$PersonSprTerrDop_id = (empty($data["PPersonSprTerrDop_id"]) ? null : $data["PPersonSprTerrDop_id"]);
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
					$sqlParams = [$server_id, $pid, $ins_dt, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, null, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Job":
					$Post_id = (empty($data["Post_id"]) ? null : $data["Post_id"]);
					$Org_id = (empty($data["Org_id"]) ? null : $data["Org_id"]);
					$OrgUnion_id = (empty($data["OrgUnion_id"]) ? null : $data["OrgUnion_id"]);
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
					$sqlParams = [$server_id, $pid, $ins_dt, $Org_id, $OrgUnion_id, $Post_id, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_SurName":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSurName_ins(
							Server_id := ?,
							Person_id := ?,
							PersonSurName_insDT := ?,
							PersonSurName_SurName := ?,
							pmUser_id := ?);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $data["Person_SurName"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_SecName":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSecName_ins(
							Server_id := ?,
							Person_id := ?,
							PersonSecName_insDT := ?,
							PersonSecName_SecName := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $data["Person_SecName"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_FirName":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonFirName_ins(
							Server_id := ?,
							Person_id := ?,
							PersonFirName_insDT := ?,
							PersonFirName_FirName := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $data["Person_FirName"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonPhone_Phone":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonPhone_ins(
							Server_id := ?,
							Person_id := ?,
							PersonPhone_insDT := ?,
							PersonPhone_Phone := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $data["PersonPhone_Phone"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonInn_Inn":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonInn_ins(
							Server_id := ?,
							Person_id := ?,
							PersonInn_insDT := ?,
							PersonInn_Inn := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $data["PersonInn_Inn"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonSocCardNum_SocCardNum":
					if ($is_superadmin) {
						$sql = "
							select Error_Message as \"ErrMsg\"
							from dbo.p_PersonSocCardNum_ins(
								Server_id := ?,
								Person_id := ?,
								PersonSocCardNum_insDT := ?,
								PersonSocCardNum_SocCardNum := ?,
								pmUser_id := ?
							);
						";
						$sqlParams = [$server_id, $pid, $ins_dt, $data["PersonSocCardNum_SocCardNum"], $data["pmUser_id"]];
						$res = $callObject->db->query($sql, $sqlParams);
						$callObject->ValidateInsertQuery($res);
					}
					break;
				case "PersonRefuse_IsRefuse":
					if ($is_superadmin) {
						$sql = "
							select Error_Message as \"ErrMsg\"
							from dbo.p_PersonRefuse_ins(
								Person_id := CAST(? as bigint),
								PersonRefuse_IsRefuse := CAST(? as bigint),
								PersonRefuse_Year := CAST(to_char(getdate(), 'YYYY') as integer),
								pmUser_id := ?
							);
						";
						$sqlParams = [$pid, $data["PersonRefuse_IsRefuse"], $data["pmUser_id"]];
						$res = $callObject->db->query($sql, $sqlParams);
						$callObject->ValidateInsertQuery($res);
					}
					break;
				case "PersonChildExist_IsChild":
					$PersonChildExist_IsChild = (empty($data["PersonChildExist_IsChild"]) ? null : $data["PersonChildExist_IsChild"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonChildExist_ins(
							Server_id := ?,
							Person_id := ?,
							PersonChildExist_setDT := ?,
							PersonChildExist_IsChild := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $PersonChildExist_IsChild, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonCarExist_IsCar":
					$PersonCarExist_IsCar = (empty($data["PersonCarExist_IsCar"]) ? null : $data["PersonCarExist_IsCar"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonChildExist_ins(
							Server_id := ?,
							Person_id := ?,
							PersonChildExist_setDT := ?,
							PersonChildExist_IsChild := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $PersonCarExist_IsCar, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonHeight_Height":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonHeight_ins(
							Server_id := ?,
							Person_id := ?,
							PersonHeight_setDT := ?,
							PersonHeight_Height := ?,
							Okei_id := 2,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $data["PersonHeight_Height"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonWeight_Weight":
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonWeight_ins(
							Server_id := ?,
							Person_id := ?,
							PersonWeight_setDT := ?,
							PersonWeight_Weight := ?,
							Okei_id := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $data["PersonWeight_Weight"], $data["Okei_id"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_BirthDay":
					$date = empty($data["Person_BirthDay"]) ? null : $data["Person_BirthDay"];
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonBirthDay_ins(
							Server_id := ?,
							Person_id := ?,
							PersonBirthDay_insDT := ?,
							PersonBirthDay_BirthDay := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $date, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Person_SNILS":
					$serv_id = ($is_superadmin) ? 1 : $server_id;
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSnils_ins(
							Server_id := ?,
							Person_id := ?,
							PersonSnils_insDT := ?,
							PersonSnils_Snils := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$serv_id, $pid, $ins_dt, $data["Person_SNILS"], $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "PersonSex_id":
					$Sex_id = (!isset($data["PersonSex_id"]) || !is_numeric($data["PersonSex_id"]) ? null : $data["PersonSex_id"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSex_ins(
							Server_id := ?,
							Person_id := ?,
							PersonSex_insDT := ?,
							Sex_id := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $Sex_id, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "SocStatus_id":
					$SocStatus_id = (empty($data["SocStatus_id"]) ? null : $data["SocStatus_id"]);
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonSocStatus_ins(
							Server_id := ?,
							Person_id := ?,
							PersonSocStatus_insDT := ?,
							SocStatus_id := ?,
							pmUser_id := ?);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $SocStatus_id, $data["pmUser_id"]];
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
						from dbo.p_PersonFamilyStatus_ins(
							Server_id := ?,
							Person_id := ?,
							PersonFamilyStatus_insDT := ?,
							FamilyStatus_id := ?,
							PersonFamilyStatus_IsMarried := ?,
							pmUser_id := ?
						);
					";
					$sqlParams = [$server_id, $pid, $ins_dt, $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				case "Federal_Num":
					$Federal_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
					$checkEdNum = $callObject->checkPesonEdNumOnDate(["Person_id" => $pid, "begdate" => $ins_dt]);
					if ($checkEdNum === false) {
						$date = ConvertDateFormat($ins_dt, "d.m.Y");
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
					$sqlParams = [$server_id, $pid, $ins_dt, $Federal_Num, $data["pmUser_id"]];
					$res = $callObject->db->query($sql, $sqlParams);
					$callObject->ValidateInsertQuery($res);
					break;
				default:
					break;
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
		return [["success" => true]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function savePersonUAddress(Person_model $callObject, $data, $adressType)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			'Person'.$adressType.'Address_insDT' 	=> !empty($data['insDT']) ? $data['insDT']:'2000-01-01 00:00:00.000',
			'Person'.$adressType.'address_id' 	=> $data['PersonEvn_id'],
			"KLAreaType_id" => null,
			"KLCountry_id" => $data["KLCountry_id"],
			"KLRgn_id" => $data["KLRgn_id"],
			"KLSubRgn_id" => $data["KLSubRgn_id"],
			"KLCity_id" => $data["KLCity_id"],
			"KLTown_id" => $data["KLTown_id"],
			"KLStreet_id" => $data["KLStreet_id"],
			"Address_Zip" => $data["Address_Zip"],
			"Address_House" => $data["Address_House"],
			"Address_Corpus" => $data["Address_Corpus"],
			"Address_Flat" => $data["Address_Flat"],
			"PersonSprTerrDop_id" => $data["PersonSprTerrDop_id"],
			"Address_Address" => $data["Address_Address"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$sql = "
			select 
				PS.Server_id as \"Server_id\",
				PUA.PersonUAddress_id as \"PersonUAddress_id\",
				PPA.PersonPAddress_id as \"PersonPAddress_id\"
			from
				v_PersonState PS
				left join v_PersonUAddress PUA on PUA.Address_id = PS.UAddress_id
				left join v_PersonPAddress PPA on PPA.Address_id = PS.PAddress_id
			where PS.Person_id = :Person_id
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, $params);
		$sel = $res->result_array();
		if(empty($sel[0]['Person'.$adressType.'Address_id'])){
			$sql = "
				select
					Error_Code as \"Error_Code\",
				    Error_Message as \"ErrMsg\"
				from p_Person{$adressType}Address_ins(
					Server_id := :Server_id,
					Person_id := :Person_id,
					Person{$adressType}Address_insDT = :Person{$adressType}Address_insDT,
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
					pmUser_id := :pmUser_id
				);
			";
		} else {
			$params['Server_id'] = $sel[0]['Server_id'];
			$params['Person'.$adressType.'address_id'] = $sel[0]['Person'.$adressType.'Address_id'];
			$sql = "
				select
					Error_Code as \"Error_Code\",
				    Error_Message as \"ErrMsg\"
				from dbo.p_Person{$adressType}Address_upd(
					Person{$adressType}Address_id := :Person{$adressType}address_id,
					Server_id := :Server_id,
					Person_id := :Person_id,
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
		}
		return $callObject->queryResult($sql, $params);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function savePersonInfo(Person_model $callObject, $data)
	{
		$query = "
			select 
				PI.Server_id as \"Server_id\",
				PI.PersonInfo_id as \"PersonInfo_id\",
				PI.UPersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
				PI.PPersonSprTerrDop_id as \"PPersonSprTerrDop_id\",
				PI.BPersonSprTerrDop_id as \"BPersonSprTerrDop_id\",
				PI.PersonInfo_InternetPhone as \"PersonInfo_InternetPhone\",
				PI.Nationality_id as \"Nationality_id\",
				PI.Ethnos_id as \"Ethnos_id\",
				PI.PersonInfo_IsSetDeath as \"PersonInfo_IsSetDeath\",
				PI.PersonInfo_IsParsDeath as \"PersonInfo_IsParsDeath\",
				PI.Person_BDZCode as \"Person_BDZCode\",
				PI.PersonInfo_Email as \"PersonInfo_Email\"
			from PersonInfo PI
			where PI.Person_id = :Person_id
			limit 1
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		$resp = $callObject->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении информации о человеке");
		}
		$procedure = (count($resp) == 0) ? "p_PersonInfo_ins" : "p_PersonInfo_upd";
		if (count($resp) != 0) {
			foreach ($resp[0] as $field => $value) {
				if (!isset($data[$field])) {
					$data[$field] = $value;
				}
			}
		}
		$selectString = "
			PersonInfo_id as \"PersonInfo_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from dbo.{$procedure}(
				Person_id := :Person_id,
				Server_id := :Server_id,
				UPersonSprTerrDop_id := :UPersonSprTerrDop_id,
				PPersonSprTerrDop_id := :PPersonSprTerrDop_id,
				BPersonSprTerrDop_id := :BPersonSprTerrDop_id,
				PersonInfo_InternetPhone := :PersonInfo_InternetPhone,
				Nationality_id := :Nationality_id,
				Ethnos_id := :Ethnos_id,
				PersonInfo_IsSetDeath := :PersonInfo_IsSetDeath,
				PersonInfo_IsParsDeath := :PersonInfo_IsParsDeath,
				PersonInfo_Email := :PersonInfo_Email,
				Person_BDZCode := :Person_BDZCode,
				pmUser_id := :pmUser_id
			);
		";
		$queryParams = [
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"],
			"PersonInfo_id" => !empty($data["PersonInfo_id"]) ? $data["PersonInfo_id"] : null,
			"UPersonSprTerrDop_id" => !empty($data["UPersonSprTerrDop_id"]) ? $data["UPersonSprTerrDop_id"] : null,
			"PPersonSprTerrDop_id" => !empty($data["PPersonSprTerrDop_id"]) ? $data["PPersonSprTerrDop_id"] : null,
			"BPersonSprTerrDop_id" => !empty($data["BPersonSprTerrDop_id"]) ? $data["BPersonSprTerrDop_id"] : null,
			"PersonInfo_InternetPhone" => !empty($data["PersonInfo_InternetPhone"]) ? $data["PersonInfo_InternetPhone"] : null,
			"Nationality_id" => !empty($data["Nationality_id"]) ? $data["Nationality_id"] : null,
			"Ethnos_id" => !empty($data["Ethnos_id"]) ? $data["Ethnos_id"] : null,
			"PersonInfo_IsSetDeath" => !empty($data["PersonInfo_IsSetDeath"]) ? $data["PersonInfo_IsSetDeath"] : null,
			"PersonInfo_IsParsDeath" => !empty($data["PersonInfo_IsParsDeath"]) ? $data["PersonInfo_IsParsDeath"] : null,
			"PersonInfo_Email" => !empty($data["PersonInfo_Email"]) ? $data["PersonInfo_Email"] : null,
			"Person_BDZCode" => !empty($data["Person_BDZCode"]) ? $data["Person_BDZCode"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function savePersonChild(Person_model $callObject, $data)
	{
		// проверяем наличие записи о PersonChild
		$query = "
			select 
				server_id as \"server_id\",
        		personchild_id as \"personchild_id\",
            	person_id as \"person_id\",
            	residplace_id as \"residplace_id\",
            	personchild_ismanychild as \"personchild_ismanychild\",
            	personchild_isbad as \"personchild_isbad\",
            	personchild_isincomplete as \"personchild_isincomplete\",
            	personchild_istutor as \"personchild_istutor\",
            	personchild_ismigrant as \"personchild_ismigrant\",
            	healthkind_id as \"healthkind_id\",
            	personchild_isyoungmother as \"personchild_isyoungmother\",
              	feedingtype_id as \"feedingtype_id\",
	            invalidkind_id as \"invalidkind_id\",
	        	personchild_invdate as \"personchild_invdate\",
	            healthabnorm_id as \"healthabnorm_id\",
	            healthabnormvital_id as \"healthabnormvital_id\",
	            diag_id as \"diag_id\",
	            personchild_isinvalid as \"personchild_isinvalid\",
	            personsprterrdop_id as \"personsprterrdop_id\",
	            pmuser_insid as \"pmuser_insid\",
	            pmuser_updid as \"pmuser_updid\",
	            personchild_insdt as \"personchild_insdt\",
	            personchild_upddt as \"personchild_upddt\",
	            childtermtype_id as \"childtermtype_id\",
	            personchild_isaidsmother as \"personchild_isaidsmother\",
	            personchild_isbcg as \"personchild_isbcg\",
	            personchild_bcgser as \"personchild_bcgser\",
	            personchild_bcgnum as \"personchild_bcgnum\",
	            birthsvid_id as \"birthsvid_id\",
	            personchild_countchild as \"personchild_countchild\",
	            childpositiontype_id as \"childpositiontype_id\",
	            personchild_isrejection as \"personchild_isrejection\",
	            birthspecstac_id as \"birthspecstac_id\",
	            evnps_id as \"evnps_id\",
	            personchild_isbreath as \"personchild_isbreath\",
	            personchild_isheart as \"personchild_isheart\",
	            personchild_ispulsation as \"personchild_ispulsation\",
	            personchild_ismuscle as \"personchild_ismuscle\"
  			from v_PersonChild
			where Person_id = :Person_id
			order by PersonChild_id desc
			limit 1
		";
		$PersonChild = $callObject->getFirstRowFromQuery($query, $data, true);
		if ($PersonChild === false) {
			throw new Exception("Ошибка при поиске специфики детства");
		}
		$queryParams = [
			"PersonChild_id" => null,
			"Server_id" => $data["Server_id"],
			"Person_id" => $data["Person_id"],
			"ResidPlace_id" => !empty($data["ResidPlace_id"]) ? $data["ResidPlace_id"] : null,
			"PersonChild_IsManyChild" => !empty($data["PersonChild_IsManyChild"]) ? $data["PersonChild_IsManyChild"] : null,
			"PersonChild_IsBad" => !empty($data["PersonChild_IsBad"]) ? $data["PersonChild_IsBad"] : null,
			"PersonChild_IsYoungMother" => !empty($data["PersonChild_IsYoungMother"]) ? $data["PersonChild_IsYoungMother"] : null,
			"PersonChild_IsIncomplete" => !empty($data["PersonChild_IsIncomplete"]) ? $data["PersonChild_IsIncomplete"] : null,
			"PersonChild_IsTutor" => !empty($data["PersonChild_IsTutor"]) ? $data["PersonChild_IsTutor"] : null,
			"PersonChild_IsMigrant" => !empty($data["PersonChild_IsMigrant"]) ? $data["PersonChild_IsMigrant"] : null,
			"HealthKind_id" => !empty($data["HealthKind_id"]) ? $data["HealthKind_id"] : null,
			"FeedingType_id" => !empty($data["FeedingType_id"]) ? $data["FeedingType_id"] : null,
			"PersonChild_CountChild" => !empty($data["PersonChild_CountChild"]) ? $data["PersonChild_CountChild"] : null,
			"InvalidKind_id" => !empty($data["InvalidKind_id"]) ? $data["InvalidKind_id"] : null,
			"PersonChild_IsInvalid" => !empty($data["PersonChild_IsInvalid"]) ? $data["PersonChild_IsInvalid"] : null,
			"PersonChild_invDate" => !empty($data["PersonChild_invDate"]) ? $data["PersonChild_invDate"] : null,
			"HealthAbnorm_id" => !empty($data["HealthAbnorm_id"]) ? $data["HealthAbnorm_id"] : null,
			"HealthAbnormVital_id" => !empty($data["HealthAbnormVital_id"]) ? $data["HealthAbnormVital_id"] : null,
			"Diag_id" => !empty($data["Diag_id"]) ? $data["Diag_id"] : null,
			"PersonSprTerrDop_id" => !empty($data["PersonSprTerrDop_id"]) ? $data["PersonSprTerrDop_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
			"ChildTermType_id" => null,
			"PersonChild_IsAidsMother" => null,
			"PersonChild_IsBCG" => null,
			"PersonChild_BCGSer" => null,
			"PersonChild_BCGNum" => null,
			"BirthSvid_id" => null,
			"ChildPositionType_id" => null,
			"PersonChild_IsRejection" => null,
			"BirthSpecStac_id" => null,
		];
		$procedure = (empty($PersonChild)) ? "p_PersonChild_ins" : "p_PersonChild_upd";
		if (!empty($PersonChild)) {
			foreach ($queryParams as $key => &$value) {
				if (!key_exists($key, $data) && !empty($PersonChild[$key])) {
					$value = $PersonChild[$key];
				}
			}
		}
		$selectString = "
			PersonChild_id as \"PersonChild_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from dbo.{$procedure}(
				Server_id := :Server_id,
				Person_id := :Person_id,
				ResidPlace_id := :ResidPlace_id,
				BirthSpecStac_id := :BirthSpecStac_id,
				PersonChild_IsManyChild := :PersonChild_IsManyChild,
				PersonChild_IsBad := :PersonChild_IsBad,
				PersonChild_IsYoungMother := :PersonChild_IsYoungMother,
				PersonChild_IsIncomplete := :PersonChild_IsIncomplete,
				PersonChild_IsTutor := :PersonChild_IsTutor,
				PersonChild_IsMigrant := :PersonChild_IsMigrant,
				HealthKind_id := :HealthKind_id,
				FeedingType_id := :FeedingType_id,
				PersonChild_IsInvalid := :PersonChild_IsInvalid,
				InvalidKind_id := :InvalidKind_id,
				PersonChild_invDate := :PersonChild_invDate,
				HealthAbnorm_id := :HealthAbnorm_id,
				HealthAbnormVital_id := :HealthAbnormVital_id,
				Diag_id := :Diag_id,
				PersonSprTerrDop_id := :PersonSprTerrDop_id,
				pmUser_id := :pmUser_id,
				ChildTermType_id := :ChildTermType_id,
				PersonChild_IsAidsMother := :PersonChild_IsAidsMother,
				PersonChild_IsBCG := :PersonChild_IsBCG,
				PersonChild_BCGSer := :PersonChild_BCGSer,
				PersonChild_BCGNum := :PersonChild_BCGNum,
				BirthSvid_id := :BirthSvid_id,
				PersonChild_CountChild := :PersonChild_CountChild,
				ChildPositionType_id := :ChildPositionType_id,
				PersonChild_IsRejection := :PersonChild_IsRejection
			);
		";
		$response = $callObject->queryResult($query, $queryParams);
		if (!is_array($response)) {
			throw new Exception("Ошибка при сохранении специфики детства");
		}
		return $response;
	}

	/**
	 * @param Person_model $callObject
	 * @param $srv_id
	 * @param $user_id
	 * @param $country
	 * @param $level
	 * @param $name
	 * @param $pid
	 * @param $socr_id
	 * @return mixed|null
	 * @throws Exception
	 */
	public static function saveAddressPart(Person_model $callObject, $srv_id, $user_id, $country, $level, $name, $pid, $socr_id)
	{
		if ($level < 5) {
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_KLArea_ins(
					KLCountry_id := ?,
					KLAreaLevel_id := ?,
					KLArea_pid := ?,
					KLArea_Name := ?,
					KLAdr_Actual := ?,
					KLSocr_id := ?,
					Server_id := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$country, $level, $pid, $name, 0, $socr_id, $srv_id, $user_id];
			$res = $callObject->db->query($sql, $sqlParams);
		} else {
			$sql = "
				select Error_Message as \"ErrMsg\"
				from dbo.p_KLStreet_ins(
					KLArea_id := ?,
					KLSocr_id := ?,
					KLStreet_Name := ?,
					KLAdr_Code := ?,
					KLAdr_Actual := ?,
					Server_id := ?,
					pmUser_id := ?
				);
			";
			$sqlParams = [$pid, $socr_id, $name, null, 0, $srv_id, $user_id];
			$res = $callObject->db->query($sql, $sqlParams);
		}
		$callObject->ValidateInsertQuery($res);
		return (is_object($res)) ? $callObject->db->insert_id() : null;
	}

	/**
	 * @param Person_model $callObject
	 * @param $srv_id
	 * @param $user_id
	 * @param $country
	 * @param $region
	 * @param $subregion
	 * @param $city
	 * @param $town
	 * @param $street
	 * @param $region_socr
	 * @param $subregion_socr
	 * @param $city_socr
	 * @param $town_socr
	 * @param $street_socr
	 * @return array
	 * @throws Exception
	 */
	public static function saveAddressAll(Person_model $callObject, $srv_id, $user_id, $country, $region, $subregion, $city, $town, $street, $region_socr, $subregion_socr, $city_socr, $town_socr, $street_socr)
	{
		if (isset($region) && !is_numeric($region)) {
			$region = $callObject->saveAddressPart($srv_id, $user_id, $country, 1, $region, $country, $region_socr);
		}
		if (isset($subregion) && !is_numeric($subregion)) {
			$subregion = $callObject->saveAddressPart($srv_id, $user_id, $country, 2, $subregion, (isset($region) ? $region : $country), $subregion_socr);
		}
		if (isset($city) && !is_numeric($city)) {
			$city = $callObject->saveAddressPart($srv_id, $user_id, $country, 3, $city, (isset($subregion) ? $subregion : (isset($region) ? $region : $country)), $city_socr);
		}
		if (isset($town) && !is_numeric($town)) {
			$town = $callObject->saveAddressPart($srv_id, $user_id, $country, 4, $town, (isset($city) ? $city : (isset($subregion) ? $subregion : (isset($region) ? $region : $country))), $town_socr);
		}
		if (isset($street) && !is_numeric($street)) {
			$street = $callObject->saveAddressPart($srv_id, $user_id, $country, 5, $street, (isset($town) ? $town : (isset($city) ? $city : (isset($subregion) ? $subregion : (isset($region) ? $region : $country)))), $street_socr);
		}
		return [$region, $subregion, $city, $town, $street];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonPhoneInfo(Person_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$procedure = (!empty($data["PersonPhone_id"])) ? "p_PersonPhone_upd" : "p_PersonPhone_ins";
		if (!empty($data["PersonPhone_id"])) {
			$query = "
                select Server_id as \"Server_id\"
                from v_PersonPhone
                where PersonPhone_id = :PersonPhone_id
                limit 1
            ";
			$queryParams = ["PersonPhone_id" => $data["PersonPhone_id"]];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$response = $result->result_array();
			if (is_array($response) && count($response) > 0) {
				$data["Server_id"] = $response[0]["Server_id"];
			}
		}
		$selectString = "
			PersonPhone_id as \"Person_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from dbo.{$procedure}(
				PersonPhone_id := :PersonPhone_id,
				PersonPhone_Phone := :Phone_Promed,
				Person_id := :Person_id,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			);
		";
		$queryParams = [
			"Phone_Promed" => $data["Phone_Promed"],
			"PersonPhone_id" => $data["PersonPhone_id"],
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function savePersonLpuInfo(Person_model $callObject, $data)
	{
		$query = "
			select
				PersonLpuInfo_id as \"PersonLpuInfo_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo.p_PersonLpuInfo_ins(
				Person_id := :Person_id,
				Lpu_id := :Lpu_id,
				PersonLpuInfo_IsAgree := :PersonLpuInfo_IsAgree,
				PersonLpuInfo_setDT := dbo.tzGetDate(),
				pmUser_id := :pmUser_id
			);
		";
		$resp = $callObject->queryResult($query, $data);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при согласия/отзыва согласия на обработку перс.данных");
		}
		return $resp;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function savePersonAttributeForApi(Person_model $callObject, $data)
	{
		if ($data["EvnType"] === "Polis") {
			if ($data["apiAction"] === "create") {
				$query = "
					select PersonEvn_id  as \"PersonEvn_id\"
					from v_PersonState PS
					where PS.Person_id = :Person_id
					limit 1
				";
				$data["PersonEvn_id"] = $callObject->dbmodel->getFirstResultFromQuery($query, $data);
				if (empty($data['PersonEvn_id'])) {
					return array('Error_Msg' => 'Пациент не найден в системе');
				}
				$query = "
					select polis_id as \"polis_id\"
					from v_personpolis
					where Person_id = :Person_id
					  and PersonPolis_insDate >= :Polis_begDate
				";
				$result = $callObject->dbmodel->getFirstRowFromQuery($query, $data);
				if (!empty($result["polis_id"])) {
					throw new Exception("Период полиса Polis_id={$result["polis_id"]} пересекается с передаваемой датой Polis_begDate");
				}

				if ($data['PolisType_id'] == 1 && !empty($data['OrgSmo_id'])) {
					$region = $callObject->dbmodel->getFirstResultFromQuery("
						select KLRgn_id from v_OrgSMO PS where OrgSmo_id = :OrgSmo_id limit 1
					", $data);
					$regex = $region == $callObject->getRegionNumber() ? '/^\d+$/' : '/^[\d\.\/]+$/';
					if (!preg_match($regex, $data['Polis_Num'])) {
						return array('Error_Msg' => 'Неверный формат поля Polis_Num');
					}
				}

				if ($data['PolisType_id'] == 3) {
					if (empty($data['Polis_Ser']) && strlen($data['Polis_Num']) == 9) {
						$data['Polis_Ser'] = substr($data['Polis_Num'], 0, 3);
						$data['Polis_Num'] = substr($data['Polis_Num'], 3, 6);
					}

					if (!empty($data['Polis_Ser']) && strlen($data['Polis_Ser']) != 3) {
						return array('Error_Msg' => 'Неверный формат данных в поле Polis_Ser для переданного PolisType');
					}

					if (!empty($data['Polis_Ser']) && !empty($data['Polis_Num']) && strlen($data['Polis_Num']) != 6) {
						return array('Error_Msg' => 'Неверный формат поля Polis_Ser, Polis_Num');
					}
				}
			}
			if ($data["apiAction"] === "update") {
				$resp = $callObject->getPolisForAPI($data);
				if (
					!is_array($resp)
					|| !count($resp)
					|| (!empty($data['Person_id']) && $data['Person_id'] != $resp[0]['Person_id'])
				) {
					return array('Error_Msg' => 'Нет данных о полисе пациента с указанным Polis_id');
				}

				$polis = $resp[0];
				unset($data["Server_id"]); // берётся из периодики
				$data = array_replace($polis, $data);
				foreach ($data as $key => $value) {
					if (empty($data[$key])) {
						if (isset($polis[$key])) {
							$data[$key] = $polis[$key];
						} else {
							unset($data[$key]);
						}
					}
				}
				$data["PersonEvn_id"] = $data["PersonPolis_id"];
			}
			if (!empty($data["PolisType_id"]) && $data["PolisType_id"] == 4 && empty($data["Federal_Num"])) {
				$data["Federal_Num"] = (!empty($data["Polis_EdNum"]) ? $data["Polis_EdNum"] : $data["Polis_Num"]);
			}
			$data["OMSSprTerr_id"] = !empty($data["OMSSprTerr_id"]) ? $data["OMSSprTerr_id"] : null;
			$data["OrgSMO_id"] = !empty($data["OrgSMO_id"]) ? $data["OrgSMO_id"] : null;
			$data["EvnType"] = "Polis";
		}
		$data["cancelCheckEvn"] = true;
		try {
			$callObject->dbmodel->exceptionOnValidation = true;
			$resp = $callObject->dbmodel->editPersonEvnAttributeNew($data);
			if (isset($resp[0]) && !empty($resp[0]["Error_Msg"])) {
				throw new Exception($resp[0]["Error_Msg"]);
			}
			$callObject->dbmodel->exceptionOnValidation = false;
		} catch (Exception $e) {
			$callObject->dbmodel->exceptionOnValidation = false;
			throw new Exception($e->getMessage());
		}
		$response = $callObject->dbmodel->getSaveResponse();
		return $response;
	}

	/**
	 * Сохранение СНИЛС
	 */
	public static function savePersonSnils(Person_model $callObject, $data) {
		$params = [
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'Person_Snils' => $data['Person_Snils'],
			'pmUser_id' => $data['pmUser_id']
		];

		$sql = "
			select
				Error_Code as \"Error_Code\"
				Error_Message as \"Error_Msg\"
			from p_PersonSnils_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonSnils_Snils := :Person_Snils,
				pmUser_id := :pmUser_id
			)
		";

		return $callObject->queryResult($sql, $params);
	}
}
