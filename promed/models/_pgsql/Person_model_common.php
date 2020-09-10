<?php

class Person_model_common
{
    /**
     * @param Person_model $callObject
     * @param $data
     * @return array|false
     * @throws Exception
     */
    public static function addPersonPhoneHist(Person_model $callObject, $data)
    {
        $params = ["Person_id" => $data["Person_id"]];
        $query = "
			select  
				PP.PersonPhone_id as \"PersonPhone_id\",
				PP.PersonPhone_Phone as \"PersonPhone_Phone\"
			from v_PersonPhone PP 
			where PP.Person_id = :Person_id
			order by PP.PersonPhone_insDT desc
			limit 1
		";
        $PersonPhone = $callObject->getFirstRowFromQuery($query, $params, true);
        if ($PersonPhone === false) {
            throw new Exception("Ошибка при получении последний периодики по номеру телефона");
        }
        $callObject->beginTransaction();
        //Если номер изменился, то добавляется периодика
        if (!empty($PersonPhone) && (empty($data["PersonPhone_Phone"]) || $PersonPhone["PersonPhone_Phone"] == $data["PersonPhone_Phone"])) {
            $data["PersonPhone_id"] = $PersonPhone["PersonPhone_id"];
        } else {
            $params = [
                "PersonPhone_Phone" => $data["PersonPhone_Phone"],
                "Person_id" => $data["Person_id"],
                "Server_id" => $data["Server_id"],
                "pmUser_id" => $data["pmUser_id"]
            ];
            $query = "
				select PersonPhone_id as \"PersonPhone_id\",
				       Error_Code as \"Error_Code\",
				       Error_Message as \"Error_Msg\"
				from dbo.p_PersonPhone_ins(
					PersonPhone_Phone := :PersonPhone_Phone,
					Person_id := :Person_id,
					Server_id := :Server_id,
					pmUser_id := :pmUser_id
				);
			";
            $resp = $callObject->queryResult($query, $params);
            if (!is_array($resp)) {
                $callObject->rollbackTransaction();
                throw new Exception("Ошибка при сохранении номера телефона");
            }
            if (!$callObject->isSuccessful($resp)) {
                $callObject->rollbackTransaction();
                return $resp;
            }
            $data["PersonPhone_id"] = $resp[0]["PersonPhone_id"];
        }
        $params = [
            "Person_id" => $data["Person_id"],
            "PersonPhone_id" => $data["PersonPhone_id"],
            "MedStaffFact_id" => !empty($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null,
            "PersonPhoneStatus_id" => $data["PersonPhoneStatus_id"],
            "PersonPhoneFailCause_id" => !empty($data["PersonPhoneFailCause_id"]) ? $data["PersonPhoneFailCause_id"] : null,
            "pmUser_id" => $data["pmUser_id"],
        ];
        $query = "
			select PersonPhoneHist_id as \"PersonPhoneHist_id\",
			       Error_Code as \"Error_Code\",
			       Error_Message as \"Error_Msg\"
			from dbo.p_PersonPhoneHist_ins(
				Person_id := :Person_id,
				PersonPhone_id := :PersonPhone_id,
				MedStaffFact_id := :MedStaffFact_id,
				PersonPhoneStatus_id := :PersonPhoneStatus_id,
				PersonPhoneFailCause_id := :PersonPhoneFailCause_id,
				pmUser_id := :pmUser_id
			);
		";
        $response = $callObject->queryResult($query, $params);
        if (!is_array($response)) {
            $callObject->rollbackTransaction();
            throw new Exception("Ошибка при сохранении статуса номера телефона");
        }
        if (!$callObject->isSuccessful($response)) {
            $callObject->rollbackTransaction();
            return $response;
        }
        $callObject->commitTransaction();
        return $response;
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array|false
     * @throws Exception
     */
    public static function addPersonRequestData(Person_model $callObject, $data)
    {
        $fromClient = (isset($data["fromClient"]) && $data["fromClient"]);
        $identImmediately = (isset($data["identImmediately"]) && $data["identImmediately"]);
        $params = [
            "Person_id" => $data["Person_id"],
            "PersonRequestSourceType_id" => $data["PersonRequestSourceType_id"],
            "Person_identDT" => !empty($data["Person_identDT"]) ? $data["Person_identDT"] : null,
        ];
        if (empty($params["Person_identDT"])) {
            $params["Person_identDT"] = $callObject->getFirstResultFromQuery("select to_char (dbo.tzGetDate(), '{$callObject->dateTimeFormUnixFull}')");
            if ($params["Person_identDT"] === false) {
                throw new Exception("Ошибка при получении текущей даты");
            }
        }
        if ($params["Person_identDT"] instanceof DateTime) {
            $params["Person_identDT"] = $params["Person_identDT"]->format("Y-m-d H:i:s");
        }
        //Получение идентификатора предыдущего запроса на идентификацию
        $query = "
			select PRD.PersonRequestData_id as \"PersonRequestData_id\"
			from erz.v_PersonRequestData PRD 
			where PRD.Person_id = :Person_id
			  and PRD.Evn_disDT::date = :Person_identDT::date
			  and PRD.PersonRequestDataStatus_id <> 7
			order by PRD.PersonRequestData_insDT desc
            limit 1
		";
        $PersonRequestData_id = $callObject->getFirstResultFromQuery($query, $params, true);
        if ($PersonRequestData_id === false) {
            throw new Exception("Ошибка при поиске человека в пакетах на идентификацию");
        }
        if ($fromClient && !empty($PersonRequestData_id)) {
            throw new Exception("Уже существует запись в пакете на идентификацию человека", 302);
        }
        $query = "
			select 
				Person_IsInErz as \"Person_IsInErz\",
				PersonIdentState_id as \"PersonIdentState_id\",
				to_char (Person_identDT, '{$callObject->dateTimeFormUnixFull}') as \"Person_identDT\"
			from v_Person 
			where Person_id = :Person_id
			limit 1
		";
        $lastIdent = $callObject->getFirstRowFromQuery($query, $params);
        if (!is_array($lastIdent)) {
            throw new Exception("Ошибка при получении статуса последней идентификации");
        }
        $fields = [
            "Person_Surname" => "Фамилия",
            "Person_Firname" => "Имя",
            "Person_Secname" => "Отчество",
            "Person_Sex" => "Пол",
            "Person_Birthday" => "Дата рождения",
            "Person_ENP" => "Единый номер",
            "Person_Snils" => "СНИЛС",
            "DocumType_Code" => "Тип документа",
            "Docum_Ser" => "Серия документа",
            "Docum_Num" => "Номер документа",
            "PolisType_id" => "Тип полиса",
            "Polis_Ser" => "Серия",
            "Polis_Num" => "Номер",
        ];
        $person = [];
        if ($fromClient) {
            foreach ($fields as $nick => $name) {
                $person[$nick] = !empty($data[$nick]) ? $data[$nick] : null;
            }
        } else {
            $query = "
				select 
					PersonAll.Person_id as \"Person_id\",
					PersonAll.Person_SurName as \"Person_Surname\",
					PersonAll.Person_FirName as \"Person_Firname\",
					PersonAll.Person_SecName as \"Person_Secname\",
					PersonAll.Sex_id as \"Person_Sex\",
					to_char(PersonAll.Person_Birthday, '{$callObject->dateTimeForm120}') as \"Person_Birthday\",
					PersonAll.Person_EdNum as \"Person_ENP\",
					PersonAll.Person_Snils as \"Person_Snils\",
					DocumentType.DocumentType_Code as \"DocumType_Code\",
					Document.Document_Ser as \"Docum_Ser\",
					Document.Document_Num as \"Docum_Num\",
					Polis.PolisType_id as \"PolisType_id\",
					Polis.Polis_Ser as \"Polis_Ser\",
					Polis.Polis_Num as \"Polis_Num\"
				from
					v_Person_bdz PersonAll 
					left join v_Polis Polis on Polis.Polis_id =PersonAll.Polis_id
					left join dbo.v_Document Document on Document.Document_id = PersonAll.Document_id
					left join dbo.v_DocumentType DocumentType on DocumentType.DocumentType_id = Document.DocumentType_id
				where PersonAll.Person_id = :Person_id
				  and PersonAll.PersonEvn_insDT <= :Person_identDT
				order by PersonAll.PersonEvn_insDT desc
                limit 1
			";
            $person = $callObject->getFirstRowFromQuery($query, $params);
            if (!is_array($person)) {
                throw new Exception("Ошибка при получении данных человека для идентификации");
            }
        }
        $requireFieldError = null;
        if ($fromClient) {
            $requiresList = [];
            $requiredEveryFields = in_array(getRegionNick(), ['msk', 'vologda'])
                ? ["Person_Surname", "Person_Firname", "Person_Birthday"]
                : ["Person_Surname", "Person_Firname", "Person_Birthday", "Person_Sex"];
            $requireField = null;
            foreach ($requiredEveryFields as $field) {
                if (empty($person[$field])) {
                    $requiresList[] = $fields[$field];
                    break;
                }
            }
            if (getRegionNick() == "vologda" && empty($person["Docum_Num"]) && empty($person["Person_Snils"]) && empty($person["Polis_Num"]) && empty($person["Person_ENP"])) {
                $requiresList[] = "ДУДЛ или СНИЛС или ДПФС или ЕНП";
            }
			if (getRegionNick() == 'msk' &&
				empty($person['Docum_Num']) && empty($person['Person_Snils'])
			) {
				$requiresList[] = 'ДУДЛ или СНИЛС';
			}
			if (count($requiresList) > 0) {
                $requiresListStr = implode(" и ", $requiresList);
                $requireFieldError = "Идентификация не может быть проведена, т.к. не заполнены обязательные поля. Необходимо заполнить \"{$requiresListStr}\"";
            }
        }
        $callObject->beginTransaction();
        if (!empty($PersonRequestData_id)) {
            //Проставление причины отказа от идентификации "В процессе идентификации были изменены данные человека" для предыдущего запроса
            $funcParams = [
                "PersonRequestData_id" => $PersonRequestData_id,
                "PersonNoIdentCause_id" => 3,
            ];
            $resp = $callObject->setPersonRequestDataStatus($funcParams);
            if (!$callObject->isSuccessful($resp)) {
                $callObject->rollbackTransaction();
                return $resp;
            }
        }
        //Добавление записи для идентификации службой
        $query = "
			select
				PersonRequestData_id as \"PersonRequestData_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from erz.p_PersonRequestData_ins_old(
				PersonRequestDataStatus_id := :PersonRequestDataStatus_id,
				PersonRequestData_ReqGUID := cast(newid() as varchar),
				Evn_id := null,
				Evn_disDT := :Evn_disDT,
				Person_id := :Person_id,
				Person_Surname := :Person_Surname,
				Person_Firname := :Person_Firname,
				Person_Secname := :Person_Secname,
				Person_Sex := :Person_Sex,
				Person_Birthday := :Person_Birthday,
				Person_ENP := :Person_ENP,
				Person_Snils := :Person_Snils,
				DocumType_Code := :DocumType_Code,
				Docum_Ser := :Docum_Ser,
				Docum_Num := :Docum_Num,
				PolisType_id := :PolisType_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				PersonRequestData_csDT := null,
				PersonRequestData_flcDT := :PersonRequestData_flcDT,
				PersonRequestData_Error := :PersonRequestData_Error,
				PersonRequestSourceType_id := :PersonRequestSourceType_id,
				PersonRequest_id := null,
				pmUser_id := :pmUser_id
			);
		";
        $queryParams = [
            "PersonRequestDataStatus_id" => 1,
            "Evn_disDT" => $params["Person_identDT"],
            "Person_id" => $params["Person_id"],
            "PersonRequestSourceType_id" => $params["PersonRequestSourceType_id"],
            "Person_Surname" => $person["Person_Surname"],
            "Person_Firname" => $person["Person_Firname"],
            "Person_Secname" => $person["Person_Secname"],
            "Person_Sex" => $person["Person_Sex"],
            "Person_Birthday" => $person["Person_Birthday"],
            "Person_ENP" => $person["Person_ENP"],
            "Person_Snils" => $person["Person_Snils"],
            "DocumType_Code" => $person["DocumType_Code"],
            "Docum_Ser" => $person["Docum_Ser"],
            "Docum_Num" => $person["Docum_Num"],
            "PolisType_id" => $person["PolisType_id"],
            "Polis_Ser" => $person["Polis_Ser"],
            "Polis_Num" => $person["Polis_Num"],
            "PersonRequestData_flcDT" => null,
            "PersonRequestData_Error" => null,
            "pmUser_id" => $data["pmUser_id"],
        ];
        if (!empty($requireFieldError)) {
            $queryParams["PersonRequestDataStatus_id"] = 7;
            $queryParams["PersonRequestData_flcDT"] = $callObject->currentDT->format("Y-m-d H:i:s");
            $queryParams["PersonRequestData_Error"] = $requireFieldError;
        }
        $resp = $callObject->queryResult($query, $queryParams);
        if (!is_array($resp)) {
            $callObject->rollbackTransaction();
            throw new Exception("Ошибка при добавлении человека на идентификацию");
        }
        if (!$callObject->isSuccessful($resp)) {
            $callObject->rollbackTransaction();
            return $resp;
        }
        $PersonRequestData_id = $resp[0]["PersonRequestData_id"];
        if (empty($lastIdent["Person_IsInErz"]) || date_create($lastIdent["Person_identDT"]) <= date_create($params["Person_identDT"])) {
            if (!empty($requireFieldError)) {
                $lastIdent["PersonIdentState_id"] = 5;
                $lastIdent["Person_identDT"] = $params["Person_identDT"];
            } else {
                $lastIdent["Person_IsInErz"] = null;
                $lastIdent["PersonIdentState_id"] = 4;
                $lastIdent["Person_identDT"] = $params["Person_identDT"];
            }
            $funcParams = [
                "Person_id" => $params["Person_id"],
                "Person_IsInErz" => $lastIdent["Person_IsInErz"],
                "PersonIdentState_id" => $lastIdent["PersonIdentState_id"],
                "Person_identDT" => $lastIdent["Person_identDT"],
                "pmUser_id" => $data["pmUser_id"]
            ];
            $resp = $callObject->updatePerson($funcParams);
            if (!is_array($resp)) {
                $callObject->rollbackTransaction();
                throw new Exception("Ошибка при изменении статуса идентификации человека");
            }
            if (!$callObject->isSuccessful($resp)) {
                $callObject->rollbackTransaction();
                return $resp;
            }
        }
        $callObject->commitTransaction();
        $response = (!empty($requireFieldError))
            ? array_merge($lastIdent, ["success" => false, "Error_Msg" => $requireFieldError])
            : array_merge($lastIdent, ["success" => true]);
        $response = [$response];
        if (!$callObject->isSuccessful($response)) {
            return $response;
        }
        if ($identImmediately) {
            $callObject->load->model("PersonIdentRequest_model");
            $funcParams = [
                "PersonRequestData_id" => $PersonRequestData_id,
                "pmUser_id" => $data["pmUser_id"],
            ];
            $response = $callObject->PersonIdentRequest_model->identPerson($funcParams);
        }
        return $response;
    }

    /**
     * @param Person_model $callObject
     * @param $value
     * @param $pmUser_id
     * @return mixed|bool
     */
    public static function addSpecObject(Person_model $callObject, $value, $pmUser_id)
    {
        $query = "
            select AddressSpecObject_id as \"AddressSpecObject_id\"
			from AddressSpecObject 
			where AddressSpecObject_Name = :value
        ";
        /**@var CI_DB_result $result */
        $result = $callObject->db->query($query, ["value" => $value]);
        if (!is_object($result)) {
            return false;
        }
        $result = $result->result_array();
        if (is_array($result) && count($result) > 0) {
            return $result[0]["AddressSpecObject_id"];
        }
        $query = "
			select AddressSpecObject_id as \"AddressSpecObject_id\",
			       Error_Code as \"Error_Code\",
			       Error_Message as \"Error_Msg\"
			from dbo.p_AddressSpecObject_ins(
				AddressSpecObject_Name := :value,
			    pmUser_id := :pmUser_id
			);
		";
        $queryParams = [
            "value" => $value,
            "pmUser_id" => $pmUser_id
        ];
        $result = $callObject->db->query($query, $queryParams);
        if (!is_object($result)) {
            return false;
        }
        $result = $result->result_array();
        return $result[0]["AddressSpecObject_id"];
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array
     */
    public static function changeEvnZNO(Person_model $callObject, $data)
    {
        $params = [
            "Evn_id" => $data["Evn_id"],
            "isZNO" => $data["isZNO"]
        ];
        $object = $data["object"];
        switch ($object) {
            case "EvnPS":
            case "EvnVizitPL":
            case "EvnSection":
            case "EvnDiagPLStom":
                $zno_remove = ($params["isZNO"] == "2")
                    ? ", {$object}_IsZNORemove = 1"
                    : ", {$object}_IsZNORemove = 2";
                $sql = "
					update {$object}
					set {$object}_IsZNO = :isZNO {$zno_remove}
					where {$object}_id = :Evn_id
				";
                $callObject->db->query($sql, $params);
                break;
            default:
                return [["success" => true, "ErrorMsg" => "Неверный параметр \"объект\""]];
        }
        return [["success" => true, "ErrorMsg" => ""]];
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array|false
     * @throws Exception
     */
    public static function updatePerson(Person_model $callObject, $data)
    {
        $params = [
            "Person_id" => $data["Person_id"],
            "pmUser_id" => $data["pmUser_id"]
        ];
        $query = "
            select 
				P.Server_id as \"Server_id\",
				P.Person_IsUnknown as \"Person_IsUnknown\",
				P.Person_IsAnonym as \"Person_IsAnonym\",
				p.Person_IsNotINN as \"Person_IsNotINN\",
				P.Person_IsDead as \"Person_IsDead\",
				P.BDZ_id as \"BDZ_id\",
				P.Lgot_id as \"Lgot_id\",
				P.ProMed_id as \"ProMed_id\",
				P.Person_Guid as \"Person_Guid\",
				P.Person_IsInErz as \"Person_IsInErz\",
				to_char(P.Person_deadDT, '{$callObject->dateTimeFormUnixFull}') as \"Person_deadDT\",
				P.PersonCloseCause_id as \"PersonCloseCause_id\",
				to_char(P.Person_closeDT, '{$callObject->dateTimeFormUnixFull}') as \"Person_closeDT\",
				to_char(P.Person_MaxEvnDT, '{$callObject->dateTimeFormUnixFull}') as \"Person_MaxEvnDT\",
				to_char(P.Person_identDT, '{$callObject->dateTimeFormUnixFull}') as \"Person_identDT\",
				P.PersonIdentState_id as \"PersonIdentState_id\",
				P.Person_IsEncrypHIV as \"Person_IsEncrypHIV\",
				P.Person_Comment as \"Person_Comment\"
			from Person P 
			where P.Person_id = :Person_id
			limit 1";
        $person = $callObject->getFirstRowFromQuery($query, $params);
        if ($person === false) {
            throw new Exception("Ошибка при получении данных человека");
        }
        foreach ($person as $field => $value) {
            $params[$field] = (isset($data[$field])) ? $data[$field] : $value;
        }
        $query = "
			select Person_id as \"Person_id\",
			       Error_Code as \"Error_Code\",
			       Error_Message as \"Error_Msg\"
			from dbo.p_Person_upd(
				Server_id := :Server_id,
				Person_IsUnknown := :Person_IsUnknown,
				Person_IsAnonym := :Person_IsAnonym,
				Person_IsNotINN := :Person_IsNotINN,
				Person_IsDead := :Person_IsDead,
				BDZ_id := :BDZ_id,
				Lgot_id := :Lgot_id,
				Person_IsInErz:=:Person_IsInErz,
				ProMed_id := :ProMed_id,
				Person_Guid := :Person_Guid,
				Person_deadDT := :Person_deadDT,
				PersonCloseCause_id := :PersonCloseCause_id,
				Person_closeDT := :Person_closeDT,
				Person_MaxEvnDT := :Person_MaxEvnDT,
				Person_identDT := :Person_identDT,
				PersonIdentState_id := :PersonIdentState_id,
				Person_IsEncrypHIV := :Person_IsEncrypHIV,
				Person_Comment := :Person_Comment,
				pmUser_id := :pmUser_id
			);
		";
        $resp = $callObject->queryResult($query, $params);
        if (!is_array($resp)) {
            throw new Exception("Ошибка при обновлении данных человека");
        }
        return $resp;
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array
     */
    public static function serverByPersonEvn(Person_model $callObject, $data)
    {
        $query = "
			select Server_id as \"Server_id\"
			from v_PersonEvn
			where PersonEvn_id = :PersonEvn_id
		";
        $res = $callObject->getFirstResultFromQuery($query, $data);
        return ["Server_id" => $res];
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array|false
     * @throws Exception
     */
    public static function setPersonRequestDataStatus(Person_model $callObject, $data)
    {
        $params = ["PersonRequestData_id" => $data["PersonRequestData_id"]];
        $set_status = [];
        if (!empty($data["PersonRequestDataStatus_id"])) {
            $set_status[] = "PersonRequestDataStatus_id = " . pg_escape_string($data["PersonRequestDataStatus_id"]);
        }
        if (!empty($data["PersonNoIdentCause_id"])) {
            $set_status[] = "PersonNoIdentCause_id = " . pg_escape_string($data["PersonNoIdentCause_id"]);
        }
        if (count($set_status) == 0) {
            throw new Exception("Не переданы статусы запроса на идентификацию для обновления");
        }
        $set_status_str = implode(", ", $set_status);
        $query = "
		    select 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
			from dbo.p_personrequestdata_upd(
			    :PersonRequestData_id, 
			    '{$set_status_str}'
			    );
		";
        $resp = $callObject->queryResult($query, $params);
        if (!is_array($resp)) {
            throw new Exception("Ошибка при обновлении причины отказа от идентификации");
        }
        return $resp;
    }

    /**
     * @param $value
     * @param array $symbols
     * @return string|string[]|null
     */
    public static function prepareSearchSymbol($value, $symbols = [])
    {
        if (empty($symbols)) {
            $symbols = [
                'ё' => "е", "ә" => "а", "і" => "э", "ң" => "н", "ғ" => "г", "ү" => "у", "ұ" => "у", "қ" => "к", "ө" => "о", "һ" => "х", '(?!\[)\'(?!\])' => '[`\']', '(?!\[)\`(?!\'\])' => '[`\']'
            ];
        }
        $value = trim($value);
        foreach ($symbols as $symbol => $replace) {
            $value = preg_replace("/{$symbol}/iu", $replace, $value);
        }
        return $value;
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array
     * @throws Exception
     */
    public static function revivePerson(Person_model $callObject, $data)
    {
        $params = [
            "Person_id" => $data["Person_id"],
            "pmUser_id" => $data["pmUser_id"]
        ];
        $query = "
			select count(DeathSvid_id) as \"Count\"
			from v_DeathSvid DS 
			where DS.Person_id = :Person_id
			  and coalesce(DS.DeathSvid_IsBad, 1) = 1
            limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса свидетельства о смерти!");
		}
		$resp_arr = $result->result_array();
		if (!is_array($resp_arr) || count($resp_arr) == 0) {
			throw new Exception("Ошибка запроса свидетельства о смерти!");
		} else if ($resp_arr[0]["Count"] > 0) {
			throw new Exception("Удаление признака смерти невозможно, т.к. имеется свидетельство о смерти!");
		}
		$query = "
			select Error_Code as \"Error_Code\",
			       Error_Message as \"Error_Message\"
			from dbo.p_Person_revive(
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
			);
		";
        $result = $callObject->db->query($query, $params);
        if (!is_object($result)) {
            throw new Exception("Ошибка при снятии признака смерти у человека");
        }
        return [["success" => true, "Error_Msg" => ""]];
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array|false
     * @throws Exception
     */
    public static function killPerson(Person_model $callObject, $data)
    {
        $params = [
            "Person_id" => $data["Person_id"],
            "Person_deadDT" => $data["Person_deadDT"],
            "pmUser_id" => $data["pmUser_id"],
        ];
        $query = "
			select Error_Code as \"Error_Code\",
			       Error_Message as \"Error_Message\"
			from dbo.p_Person_kill(
				Person_id := :Person_id,
				PersonCloseCause_id := (
					select PersonCloseCause_id
					from v_PersonCloseCause 
					where PersonCloseCause_SysNick iLIKE 'death'
					limit 1
				),
				Person_deadDT := :Person_deadDT,
				pmUser_id := :pmUser_id
			);
		";
        $response = $callObject->queryResult($query, $params);
        if (!is_array($response)) {
            throw new Exception("Ошибка при сохранении признака смерти человека");
        }
        return $response;
    }

    /**
     * @param Person_model $callObject
     * @param $data
     * @return array
     */
    public static function extendPersonHistory(Person_model $callObject, $data)
    {
        $params = ["Person_id" => $data["Person_id"]];
        $query = "select dbo.xp_PersonHistoryExtend(Person_id := :Person_id)";
        $result = $callObject->db->query($query, $params);
        return (is_object($result))
            ? ["success" => true]
            : ["success" => false];
    }

    /**
     * @param Person_model $callObject
     * @param $paramType
     * @param $data
     * @return array|bool|false
     * @throws Exception
     */
    public static function findPersonByParams(Person_model $callObject, $paramType, $data)
    {
        $join = "";
        switch ($paramType) {
            case "Polis":
                if (empty($data["Polis_EdNum"]) && empty($data["Polis_Num"])) {
                    return false;
                }
                $join = "
					left join Polis pls on pls.Polis_id = ps.Polis_id
					left join OrgSmo os on pls.OrgSmo_id = os.OrgSmo_id
					left join Org o on os.Org_id = o.Org_id
				";
                if (!empty($data["Polis_EdNum"])) {
                    $filters[] = "ps.Person_edNum = :Polis_EdNum";
                }
                if (!empty($data["Polis_Num"])) {
                    $filters[] = "pls.Polis_Num = :Polis_Num";
                }
                if (!empty($data["Polis_Ser"])) {
                    $filters[] = "pls.Polis_Ser = :Polis_Ser";
                }
                if (!empty($data["OrgSmo_id"])) {
                    $filters[] = "os.OrgSmo_id = :OrgSmo_id";
                }
                if (!empty($data["Org_Code"])) {
                    $filters[] = "o.Org_Code = :Org_Code";
                }
                if (!empty($data["Orgsmo_f002smocod"])) {
                    $filters[] = "os.Orgsmo_f002smocod = :Orgsmo_f002smocod";
                }
                break;
            case "Document":
                if (empty($data["Document_Num"])) {
                    return false;
                }
                $join = "
					left join v_Document d on d.Document_id = ps.Document_id
					left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
					left join v_OrgDep od on od.OrgDep_id = d.OrgDep_id
				";
                if (!empty($data["Document_Ser"])) {
                    $filters[] = "d.Document_Ser = :Document_Ser";
                }
                if (!empty($data["Document_Num"])) {
                    $filters[] = "d.Document_Num = :Document_Num";
                }
                if (!empty($data["DocumentType_Code"])) {
                    $filters[] = "dt.DocumentType_Code = :DocumentType_Code";
                }
                if (!empty($data["OrgDep_id"])) {
                    $filters[] = "od.OrgDep_id = :OrgDep_id";
                }
                if (!empty($data["Org_Code"])) {
                    $filters[] = "od.Org_Code = :Org_Code";
                }
                break;
            case "Snils":
                if (empty($data["Person_Snils"])) {
                    return false;
                }
                $filters[] = "ps.Person_Snils = :Person_Snils";
                break;
            Case "Fio":
                if (!empty($data["Person_SurName"])) {
                    $filters[] = "ps.Person_SurName = :Person_SurName";
                }
                if (!empty($data["Person_FirName"])) {
                    $filters[] = "ps.Person_FirName = :Person_FirName";
                }
                if (!empty($data["Person_SecName"])) {
                    $filters[] = "ps.Person_SecName = :Person_SecName";
                }
                if (!empty($data["Person_BirthDay"])) {
                    $filters[] = "ps.Person_BirthDay = :Person_BirthDay";
                    $data["Person_BirthDay"] = new DateTime($data["Person_BirthDay"]);
                    $data["Person_BirthDay"] = $data["Person_BirthDay"]->format("Y-m-d H:i:s");
                }
                if (!empty($data["Sex_id"])) {
                    $filters[] = "ps.Sex_id = :Sex_id";
                }
                break;

            default:
                return false;
        }
        $whereString = implode(" and ", $filters);
        $sql = "
			select ps.Person_id as \"Person_id\"
			from
				v_PersonState ps 
				{$join}
			where {$whereString}
			limit 10
		";
        return $callObject->queryResult($sql, $data);
    }

	/**
	 * @param $data
	 * вызов пациента(отправка уведомления)
	 */
	public static function mSendPersonCallNotify(Person_model $callObject, $data) {

		if (!in_array($data['ARMType_id'], array(5,19))) {
			return array('Error_Msg' => 'Вызов пацента из данного АРМа не поддерживается.');
		}

		$phoneData = $callObject->checkPersonPhoneStatus($data);
		if (!empty($phoneData[0])) $phoneData = $phoneData[0];

		if (!empty($phoneData['Error_Msg'])) {
			return array('Error_Msg' => $phoneData['Error_Msg']);
		}

		if (!empty($phoneData['PersonPhone_Phone']) && !empty($phoneData['isVerified'])) {

			$text = 'Просьба подойти';

			if ($data['ARMType_id'] === 5) {
				$text .= ' к  постовой медсестре.';
			}

			if ($data['ARMType_id'] === 19) {
				$text .= ' в процедурный кабинет.';
			}

			$callObject->load->helper('notify');
			sendNotifySMS(array(
				'UserNotify_Phone' => $phoneData['PersonPhone_Phone'],
				'text' => $text,
				'User_id' => $data['pmUser_id']
			));

			return array();

		} else {
			return array('Error_Msg' => 'Номер пациента не подтвержден.');
		}
	}
}