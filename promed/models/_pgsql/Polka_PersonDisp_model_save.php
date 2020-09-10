<?php

class Polka_PersonDisp_model_save
{
	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonDispMedicament(Polka_PersonDisp_model $callObject, $data)
	{
		$procedure = ($data["PersonDispMedicament_id"] > 0) ? "p_PersonDispMedicament_ins" : "p_PersonDispMedicament_upd";
		$res = ($data["PersonDispMedicament_id"] > 0) ? $data["PersonDispMedicament_id"] : null;
		if (strtolower($data["Course_begDate"]) == "null") {
			$data["Course_begDate"] = null;
		}
		if (strtolower($data["Course_endDate"]) == "null") {
			$data["Course_endDate"] = null;
		}
		$selectString = "
       		PersonDispMedicament_id as \"PersonDispMedicament_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$query = "
	    	select {$selectString}
        	from {$procedure}(
        		PersonDispMedicament_id := :PersonDispMedicament_id,
				Server_id := :Server_id,
				PersonDisp_id := :PersonDisp_id,
				Drug_id := :Drug_id,
				PersonDispMedicament_Norma := :Course,
				PersonDispMedicament_begDate := :Course_begDate,
				PersonDispMedicament_endDate := :Course_endDate,
				pmUser_id := :pmUser_id
        	)
		";
		$queryParams = [
			"PersonDispMedicament_id" => $res,
			"Server_id" => $data["Server_id"],
			"PersonDisp_id" => $data["PersonDisp_id"],
			"Drug_id" => $data["Drug_id"],
			"Course" => $data["Course"],
			"Course_begDate" => $data["Course_begDate"],
			"Course_endDate" => $data["Course_endDate"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result|mixed
	 * @throws Exception
	 */
	public static function savePersonDisp(Polka_PersonDisp_model $callObject, $data)
	{
		$data["PersonDisp_id"] = ($data["PersonDisp_id"] == 0) ? null : $data["PersonDisp_id"];
		$query = "
			select 
				PersonDisp_id as \"PersonDisp_id\",
				to_char(PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\"
			from v_PersonDisp
			where Person_id = :Person_id
			  and Diag_id = :Diag_id
			  and Lpu_id = :Lpu_id
			  and PersonDisp_id != coalesce(:PersonDisp_id::bigint, 0)
			  and (PersonDisp_endDate is null or :PersonDisp_begDate between PersonDisp_begDate and PersonDisp_endDate)
		";
		$chk = $callObject->getFirstRowFromQuery($query, $data);
		if (is_array($chk) && !empty($chk["PersonDisp_id"])) {
			if (!empty($chk["PersonDisp_endDate"])) {
				$nextday = date("j.m.Y", strtotime($chk["PersonDisp_endDate"] . "+1 days"));
				throw new Exception("У пациента уже есть закрытая карта с указанным диагнозом, действующая на {$chk["PersonDisp_endDate"]}. Дата открытия новой карты должна быть не раньше {$nextday}).");
			} else {
				throw new Exception("У пациента уже есть действующая карта с указанным диагнозом");
			}
		}
		if (!$data["ignoreExistsPersonDisp"]) {
			$response = $callObject->checkPersonDispExists($data);
			if (!empty($response["Error_Msg"]) || !empty($response["existsPersonDisp"])) {
				return $response;
			}
		}
		if (!empty($data["PersonDisp_endDate"]) && empty($data["DispOutType_id"])) {
			throw new Exception("При снятии пациента с учета должна быть указана причина снятия");
		}
		$LabelDiag_id = null;//диагноз до изменения дисп.карты, по которому есть метка у пациента
		$LabelDispOutType_id = null;//причина снятия до изменения дисп.карты, для соотв.метки
		$PersonLabel_id = null;
		$LabelResp = [];
		$procedure = (!isset($data["PersonDisp_id"])) ? "p_PersonDisp_ins" : "p_PersonDisp_upd";
		if (isset($data["PersonDisp_id"])) {
			//ищем открытую метку того же пациента и диагноза, что был в этой дисп.карте
			//кол-во строк - кол-во дисп.карт, подходящих по параметрам найденной метки
			$sql = "
				select
					PD.PersonDisp_id as \"PersonDisp_id\",
					PD.Diag_id as \"Diag_id\",
					PD.DispOutType_id as \"DispOutType_id\",
					PL.PersonLabel_id as \"PersonLabel_id\"
				from
					v_PersonDisp PDD
					inner join v_PersonDisp PD on PD.Person_id = PDD.Person_id and PD.Diag_id=PDD.Diag_id
					inner join v_PersonLabel PL on PL.Person_id = PD.Person_id and PL.Diag_id = PD.Diag_id
				where PDD.PersonDisp_id = :PersonDisp_id
			";
			$params = ["PersonDisp_id" => $data["PersonDisp_id"]];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $params);
			if (is_object($result)) {
				$LabelResp = $result->result_array();
				if (is_array($LabelResp) && count($LabelResp) > 0) {
					$LabelDiag_id = $LabelResp[0]["Diag_id"];
					$LabelDispOutType_id = $LabelResp[0]["DispOutType_id"];
					$PersonLabel_id = $LabelResp[0]["PersonLabel_id"];
				}
			}
		}
		// стартуем транзакцию
		$callObject->beginTransaction();
		$query = "
            with mv as (select :PersonDisp_id::bigint as PersonDisp_id),
			mv2 as (
				select
					case when (select PersonDisp_id from mv) is not null then 1 else PersonDisp_IsSignedEP end as PersonDisp_IsSignedEP,
					pmUser_signID as pmUser,
					PersonDisp_signDate as PersonDisp_sDate
				from v_PersonDisp
				where PersonDisp_id = (select PersonDisp_id from mv)
			)
			select
        		PersonDisp_id as \"PersonDisp_id\",
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
            from {$procedure}(
				PersonDisp_id := (select PersonDisp_id from mv),
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonDisp_NumCard := :PersonDisp_NumCard,
				PersonDisp_begDate := :PersonDisp_begDate,
				PersonDisp_endDate := :PersonDisp_endDate,
				PersonDisp_NextDate := :PersonDisp_NextDate,
				LpuSection_id := :LpuSection_id,
				MedPersonal_id := :MedPersonal_id,
				Diag_id := :Diag_id,
				Diag_nid := :Diag_nid,
				Diag_pid := :Diag_pid,
				DispOutType_id := :DispOutType_id,
				Sickness_id := :Sickness_id,
				PersonPrivilege_id := NULL,
				PersonDisp_IsDop := :PersonDisp_IsDop,
				PersonDisp_DiagDate := :PersonDisp_DiagDate,
				DiagDetectType_id := :DiagDetectType_id,
				PersonDisp_IsTFOMS := :PersonDisp_IsTFOMS,
				PersonDisp_IsSignedEP := coalesce((select PersonDisp_IsSignedEP from mv2), null),
				pmUser_signID := coalesce((select pmUser from mv2), null),
				PersonDisp_signDate := coalesce((select PersonDisp_sDate from mv2), null),
				DeseaseDispType_id := :DeseaseDispType_id,
				pmUser_id := :pmUser_id
			)
        ";
		$data['MedPersonal_id'] = $data['MedPersonal_id'] ?? null;
		
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$old_result = $result->result_array();
		$response = $result->result_array();
		//добавляем связь со сведениями о беременности, если карта была открыта оттуда
		if(isset($data['PersonPregnancy_id'])){
			$callObject->db->query("update PersonPregnancy set PersonDisp_id = :PersonDisp_id where PersonPregnancy_id = :PersonPregnancy_id", array(
				'PersonDisp_id' => $response[0]['PersonDisp_id'],
				'PersonPregnancy_id' => $data['PersonPregnancy_id']
			));
		}
		// нам надо записать медикаменты
		if (is_array($response) && count($response) > 0) {
			if (trim($response[0]["Error_Msg"]) != "") {
				return false;
			}
			// если пришли медикаменты, то надо записать
			$person_disp_id = $response[0]["PersonDisp_id"];
			if (isset($data["medicaments"]) && !empty($data["medicaments"]) && $data["medicaments"] != "[]") {
				// запоминаем PersonDisp_id
				$sql = "select PersonDispMedicament_id as \"PersonDispMedicament_id\" from PersonDispMedicament where PersonDisp_id = :PersonDisp_id";
				$result = $callObject->db->query($sql, $response[0]);
				$result = $result->result_array();
				// приходится удалять медикаменты
				foreach ($result as $medicament) {
					$sql = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PersonDispMedicament_del(PersonDispMedicament_id := {$medicament["PersonDispMedicament_id"]})
					";
					$callObject->db->query($sql);
				}
				// записываем новые
				$new_medicaments = json_decode($data["medicaments"], true);
				foreach ($new_medicaments as $inserting_medicament) {
					$beg_date = empty($inserting_medicament["PersonDispMedicament_begDate"]) ? null : substr(trim($inserting_medicament["PersonDispMedicament_begDate"]), 6, 4) . "-" . substr(trim($inserting_medicament["PersonDispMedicament_begDate"]), 3, 2) . "-" . substr(trim($inserting_medicament["PersonDispMedicament_begDate"]), 0, 2);
					$end_date = empty($inserting_medicament["PersonDispMedicament_endDate"]) ? null : substr(trim($inserting_medicament["PersonDispMedicament_endDate"]), 6, 4) . "-" . substr(trim($inserting_medicament["PersonDispMedicament_endDate"]), 3, 2) . "-" . substr(trim($inserting_medicament["PersonDispMedicament_endDate"]), 0, 2);
					$sql = "
	    				select
        					PersonDispMedicament_id as \"PersonDispMedicament_id\",
        					Error_Code as \"Error_Code\",
        					Error_Message as \"Error_Msg\"
						from p_PersonDispMedicament_ins(
							Server_id := :Server_id, 
							PersonDisp_id := :PersonDisp_id, 
							Drug_id := :Drug_id, 
							PersonDispMedicament_Norma := :PersonDispMedicament_Norma, 
							PersonDispMedicament_begDate := :PersonDispMedicament_begDate, 
							PersonDispMedicament_endDate := :PersonDispMedicament_endDate, 
							pmUser_id := ?
						)
					";
					$sqlParams = [
						"Server_id" => $data["Server_id"],
						"PersonDisp_id" => $person_disp_id,
						"Drug_id" => $inserting_medicament["Drug_id"],
						"PersonDispMedicament_Norma" => $inserting_medicament["Drug_Count"],
						"PersonDispMedicament_begDate" => $beg_date,
						"PersonDispMedicament_endDate" => $end_date,
						"pmUser_id" => $data["pmUser_id"]
					];
					$result = $callObject->db->query($sql, $sqlParams);
					if (!is_object($result)) {
						$callObject->rollbackTransaction();
						return false;
					}
					$response = $result->result_array();
					if (!is_array($response) || count($response) == 0) {
						$callObject->rollbackTransaction();
						return false;
					}
					if (trim($response[0]["Error_Msg"]) != "") {
						$callObject->rollbackTransaction();
						return $result;
					}
				}
			}
		}
		//проверка наличия связанных карт наблюдения (из дистанционного мониторинга)
		$sql = "
			select 
				LOC.PersonDisp_id as \"PersonDisp_id\",
				LOC.DispOutType_id as \"DispOutType_id\",
				LOC.PersonLabel_id as \"PersonLabel_id\"
			from v_LabelObserveChart LOC 
			where LOC.PersonDisp_id = :PersonDisp_id
		";
		$params = ["PersonDisp_id" => $data["PersonDisp_id"]];
		/**@var CI_DB_result $LOCresp */
		$LOCresp = $callObject->db->query($sql, $params);
		$LOC = [];
		$chart_ids = [];
		if (is_object($LOCresp)) {
			$LOC = $LOCresp->result_array();
			if (count($LOC) > 0) {
				//есть связанные карты наблюдения
				foreach ($LOC as $L) {
					$chart_ids[] = $L["LabelObserveChart_id"];
				}
				$chart_idsString = implode(", ", $chart_ids);
				if (!empty($data["PersonDisp_endDate"])) {
					//поле "снят" заполнено
					//закрываем карты наблюдения
					$sql = "
						update LabelObserveChart
						set LabelObserveChart_endDate = :endDate,
						    DispOutType_id = :DispOutType_id,
						    LabelObserveChart_IsAutoClose = 1
						where LabelObserveChart_id in ({$chart_idsString})
					";
					$params = [
						"endDate" => $data["PersonDisp_endDate"],
						"DispOutType_id" => $data["DispOutType_id"]
					];
					$callObject->db->query($sql, $params);
				} else {
					//открываем закрытые карты наблюдения
					$sql = "
						update LabelObserveChart
						set LabelObserveChart_endDate = null,
							DispOutType_id = null,
							LabelObserveChart_IsAutoClose = null
						where LabelObserveChart_id in ({$chart_idsString}) 
						  and LabelObserveChart_endDate is not null
					";
					$callObject->db->query($sql);
				}
			}
		}
		//Метки
		$needUpdateLabelDiag = false;//необходимость обновить диагноз в той же метке
		if ($LabelDiag_id) {
			//есть метка по диагнозу (который был до сохранения)
			$needCloseLabel = false;//необходимость закрыть метку (или открыть если disDate = null)
			$sql = "
				update PersonLabel
				set PersonLabel_disDate = :disDate
				where Person_id = :Person_id
				  and Diag_id = :Diag_id
				  and Label_id = 1
			";
			$params = [
				"disDate" => $data["PersonDisp_endDate"],
				"Diag_id" => $LabelDiag_id,
				"Person_id" => $data["Person_id"]
			];
			if ($LabelDiag_id != $data["Diag_id"]) {
				//диагноз изменился
				//если до смены диагноза у пациента это была единственная диспансерная карта по этому диагнозу
				//нужно снять метку у пациента
				if (is_array($LabelResp) && count($LabelResp) == 1) {
					//но если новый диагноз тоже из АГ, то нужно только обновить
					if (in_array($data["Diag_id"], [5378, 5379, 5380, 5381, 5382, 5383, 5384, 5385, 5386, 5387, 5388, 5389, 5390, 11742])) {
						$needUpdateLabelDiag = true;
					} else {
						//новый диагноз не из АГ
						//лучше бы удалить метку, но если есть карта наблюдения к метке, то удалить не получится
						if (count($LOC) == 0) {
							$sql = "
								delete from PersonLabel
								where Person_id = :Person_id
								  and Diag_id = :Diag_id
								  and Label_id = 1
							";
						} else {
							//на открытии формы дисп.карты реализовано ограничение диагнозов в комбо,
							//если есть карта наблюдения по метке АГ. Поэтому сюда попасть маловероятно.
							$needCloseLabel = true;
							if (count($chart_ids) > 0) {
								$chart_idsString = implode(", ", $chart_ids);
								$sql = "
									update LabelObserveChart
									set LabelObserveChart_endDate = :endDate,
										DispOutType_id = :DispOutType_id,
										LabelObserveChart_IsAutoClose = 1
									where LabelObserveChart_id in ({$chart_idsString})
								";
								$params = [
									"endDate" => $data["PersonDisp_endDate"],
									"DispOutType_id" => $data["DispOutType_id"]
								];
								$callObject->db->query($sql, $params);
							}
						}
					}
				}
			} else {
				if (in_array($data["DispOutType_id"], [1, 4])) {
					//снят по причине выздоровление или смерть
					//закрываем метку
					$needCloseLabel = true;
				} else if ($data["DispOutType_id"] != $LabelDispOutType_id) {
					//причина снятия изменилась (не выздоровление и не смерть)
					//открываем обратно метку
					$params["disDate"] = null;
					$params["PersonLabel_id"] = (count($LOC) > 0)?$LOC[0]["PersonLabel_id"]:$PersonLabel_id;
					$sql .= " and PersonLabel_id = :PersonLabel_id";
					$needCloseLabel = true;
				}
			}
			if ($needCloseLabel) {
				$callObject->db->query($sql, $params);
			}
		}
		//проверяем необходимость добавить метку для дистанционного мониторинга (артериального давления)
		if (in_array($data['Diag_id'], [5378, 5379, 5380, 5381, 5382, 5383, 5384, 5385, 5386, 5387, 5388, 5389, 5390, 11742])) {
			if (empty($data['PersonDisp_endDate'])) {
				//поле "снят" пусто
				$sql = "
					select count(*)
					from v_PersonLabel PL
					where PL.Person_id = :Person_id
					  and PL.Diag_id = :Diag_id
					  and Label_id = 1
					  and PersonLabel_disDate is null
				";
				$sqlParams = [
					"Person_id" => $data["Person_id"],
					"Diag_id" => $data["Diag_id"]
				];
				$PLcount = $callObject->getFirstResultFromQuery($sql, $sqlParams);
				if ($PLcount == 0 and !$needUpdateLabelDiag) {
					//метку не нашли - нужно создать
					$sql = "
						select
        					PersonLabel_id as \"PersonLabel_id\",
        					Error_Code as \"Error_Code\",
        					Error_Message as \"Error_Msg\"
        				from p_PersonLabel_ins(
        					Label_id := 1,
							Person_id := :Person_id,
							Diag_id := :Diag_id,
							pmUser_id := :pmUser_id,
							PersonLabel_setDate := :setDate
        				)
					";
					$sqlParams = [
						"Person_id" => $data["Person_id"],
						"Diag_id" => $data["Diag_id"],
						"setDate" => $data["PersonDisp_begDate"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->db->query($sql, $sqlParams);
				} elseif ($needUpdateLabelDiag) {
					//метка есть и диагноз сменился - обновить диагноз в метке
					$sql = "
						update PersonLabel
						set Diag_id = :NewDiag_id
						where Person_id=:Person_id
						  and Diag_id=:Diag_id
					";
					$sqlParams = [
						"Person_id" => $data["Person_id"],
						"Diag_id" => $LabelDiag_id,
						"NewDiag_id" => $data["Diag_id"]
					];
					$callObject->db->query($sql, $sqlParams);
				}
			}
		}
		//Если все прошло нормально, добавляем новый диагноз в DiagDispCard (только для беременностей и родов (sickness_id = 9)
		if ($data["Sickness_id"] == 9) {
			//Проверим последний диагноз в истории. Если не совпадает с нововведенным, то добавим его
			$params_check["PersonDisp_id"] = $person_disp_id;
			$query_check = "
                select D.Diag_id as \"Diag_id\"
                from v_DiagDispCard D
                where D.PersonDisp_id = :PersonDisp_id
                order by D.DiagDispCard_insDT desc
                limit 1
            ";
			/**@var CI_DB_result $result_check */
			$result_check = $callObject->db->query($query_check, $params_check);
			if (is_object($result_check)) {
				$resp_check = $result_check->result_array();
				if (is_array($resp_check) && count($resp_check) > 0) {
					if ($resp_check[0]["Diag_id"] != $data["Diag_id"]) {
						$data["DiagDispCard_Date"] = date("Y-m-d");
						$callObject->saveDiagDispCard($data);
					}
				} else {
					$data["DiagDispCard_Date"] = date("Y-m-d");
					$data["PersonDisp_id"] = $person_disp_id;
					$callObject->saveDiagDispCard($data);
				}
			}
		}
		if (!empty($data["PersonRegister_id"])) {
			$params_check = [
				"PersonDisp_id" => $person_disp_id,
				"PersonRegister_id" => $data["PersonRegister_id"]
			];
			$query_check = "
                select PRDL.PersonRegister_id
                from v_PersonRegisterDispLink PRDL
                where PRDL.PersonRegister_id = :PersonRegister_id and PRDL.PersonDisp_id = :PersonDisp_id
                limit 1
            ";
            $error = false;
			if ($callObject->getFirstResultFromQuery($query_check, $params_check) === false) {
				$resp = $callObject->execCommonSP('p_PersonRegisterDispLink_ins', array(
					'PersonRegisterDispLink_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
					'PersonRegister_id' => $data['PersonRegister_id'],
					'PersonDisp_id' => $person_disp_id,
					'pmUser_id' => $data['pmUser_id']
				), 'array_assoc');
				if(empty($resp['PersonRegisterDispLink_id']) || !empty($resp['Error_Msg'])) {
					$error = (!empty($resp['Error_Msg'])?$resp['Error_Msg']:true);
				}
				if ($error) {
					$callObject->rollbackTransaction();
					throw new Exception((gettype($error) == "string") ? $error : "Ошибка при сохранении карты.");
				}
			}
		}
		if ($callObject->getRegionNick() == "kz") {
			$callObject->db->query("delete from r101.PersonDispGroupLink where PersonDisp_id = ?", [$person_disp_id]);
			$sql = "
				select
					persondispgrouplink_id as \"persondispgrouplink_id\",
					Error_Code as \"Error_Code\",
        			Error_Message as \"Error_Msg\"
				from r101.p_PersonDispGroupLink_ins(
					PersonDisp_id := :PersonDisp_id,
					DispGroup_id := :DispGroup_id,
					pmUser_id := :pmUser_id
				)
			";
			$sqlParams = [
				"PersonDisp_id" => $person_disp_id,
				"DispGroup_id" => $data["DispGroup_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->db->query($sql, $sqlParams);
			if (!empty($data['HumanUID']) && $data['action'] == 'add') {
				$callObject->queryResult("
					select
						PersonDispUIDLink_id as \"PersonDispUIDLink_id\",
						Error_Code as \"Error_Code\",
        				Error_Message as \"Error_Msg\"
					from r101.p_PersonDispUIDLink_ins(
						PersonDisp_id := :PersonDisp_id,
						UIDGuid := :UIDGuid,
						PersonDisp_NumCard := :PersonDisp_NumCard,
						pmUser_id := :pmUser_id
					)
				", [
					'PersonDisp_id' => $person_disp_id,
					'UIDGuid' => $data['HumanUID'],
					'pmUser_id' => $data['pmUser_id'],
					'PersonDisp_NumCard' => $data['PersonDisp_NumCard']
				]);
			}
		}
		$callObject->commitTransaction();
		return $old_result;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveDiagDispCard(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"DiagDispCard_Date" => $data["DiagDispCard_Date"],
			"Diag_id" => $data["Diag_id"],
			"PersonDisp_id" => $data["PersonDisp_id"]
		];
		$DiagDispCard_id = null;
		$procedure = (isset($data["DiagDispCard_id"]) && ($data["DiagDispCard_id"] <> 0)) ? "p_DiagDispCard_upd" : "p_DiagDispCard_ins";
		$params["DiagDispCard_id"] = (isset($data["DiagDispCard_id"]) && ($data["DiagDispCard_id"] <> 0)) ? $data["DiagDispCard_id"] : null;
		$selectString = "
           	DiagDispCard_id as \"DiagDispCard_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$query = "
	        select {$selectString}
        	from {$procedure} (
           		DiagDispCard_id := :DiagDispCard_id,
           		PersonDisp_id := :PersonDisp_id,
           		Diag_id := :Diag_id,
           		PersonDisp_begDate := :DiagDispCard_Date,
           		PersonDisp_endDate := null,
           		pmUser_id := :pmUser_id
           	)
		";
		$queryParams = [
			"DiagDispCard_id" => $params["DiagDispCard_id"],
			"PersonDisp_id" => $params["PersonDisp_id"],
			"Diag_id" => $params["Diag_id"],
			"DiagDispCard_Date" => $params["DiagDispCard_Date"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		//Обновим диспансерную карту - проставим ей последний диагноз из истории. Чтобы не наворотить много писанины, сделал пока через прямой апдейт.
		$query_set_diag = "
        	update PersonDisp
            set Diag_id = (
            	select DDC.Diag_id
            	from v_DiagDispCard DDC
            	where DDC.PersonDisp_id = {$params["PersonDisp_id"]}
            	order by DDC.DiagDispCard_insDT desc
            	limit 1
            )
            where PersonDisp_id = {$params["PersonDisp_id"]}
		";
		$callObject->db->query($query_set_diag, $params["PersonDisp_id"]);
		return $resp;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function savePersonDispVizit(Polka_PersonDisp_model $callObject, $data)
	{
		if ($callObject->checkVisitDoubleNextdate($data["PersonDisp_id"], $data["PersonDispVizit_NextDate"], $data["PersonDispVizit_id"])) {
			throw new Exception("Назначенная дата явки уже существует в списке контроля посещений. Укажите другую дату в поле \"Назначено явиться\"", 666);
		}
        $data['PersonDispVizit_IsHomeDN'] = $data['PersonDispVizit_IsHomeDN'] ? 2 : 1;
		$procedure = $data["PersonDispVizit_id"] ? "p_PersonDispVizit_upd" : "p_PersonDispVizit_ins";
		$selectString = "
       		PersonDispVizit_id as \"PersonDispVizit_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
				PersonDispVizit_id := :PersonDispVizit_id,
				PersonDisp_id := :PersonDisp_id,
				PersonDispVizit_NextDate := :PersonDispVizit_NextDate,
				PersonDispVizit_NextFactDate := :PersonDispVizit_NextFactDate,
				pmUser_id := :pmUser_id,
				PersonDispVizit_IsHomeDN := :PersonDispVizit_IsHomeDN
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonDispEvnVizitPL(Polka_PersonDisp_model $callObject, $data)
	{
		if (empty($data["PersonDispVizit_NextFactDate"]) || empty($data["EvnVizitPL_id"]) || empty($data["pmUser_id"])) {
			return false;
		}
		$procedure = "p_PersonDispVizit_ins";
		$data["PersonDispVizit_id"] = null;
		$data["PersonDispVizit_NextDate"] = null;
		$where = "";
		//смотрим есть ли запись, если есть то обновляем
		if (!empty($data["PersonDisp_id"])) {
			$where = " and PersonDisp_id = :PersonDisp_id";
		}
		$query = "
			select 
				PersonDispVizit_id as \"PersonDispVizit_id\",
				PersonDispVizit_NextDate as \"PersonDispVizit_NextDate\",
				PersonDisp_id as \"PersonDisp_id\"
			from v_PersonDispVizit PDV
			where EvnVizitPL_id = :EvnVizitPL_id {$where}
		";
		$result = $callObject->dbmodel->getFirstRowFromQuery($query, $data);
		if (!empty($result["PersonDispVizit_id"])) {
			//обновляем запись
			$data["PersonDispVizit_id"] = $result["PersonDispVizit_id"];
			$data["PersonDispVizit_NextDate"] = $result["PersonDispVizit_NextDate"];
			if (empty($data["PersonDisp_id"])) {
				$data["EvnVizitPL_id"] = null;
				$data["PersonDispVizit_NextFactDate"] = null;
				$data["PersonDisp_id"] = $result["PersonDisp_id"];
			}
			$procedure = "p_PersonDispVizit_upd";
		}
		if (empty($data["PersonDispVizit_id"]) && !empty($data["PersonDisp_id"])) {
			$query = "
				select 
					PersonDispVizit_id as \"PersonDispVizit_id\",
					PersonDispVizit_NextDate as \"PersonDispVizit_NextDate\",
					PersonDisp_id as \"PersonDisp_id\"
				from v_PersonDispVizit PDV 
				where PDV.PersonDispVizit_NextDate = :PersonDispVizit_NextFactDate
				  and PersonDisp_id = :PersonDisp_id
			";
			$result = $callObject->dbmodel->getFirstRowFromQuery($query, $data);
			if (!empty($result["PersonDispVizit_id"])) {
				//обновляем запись
				$data["PersonDispVizit_id"] = $result["PersonDispVizit_id"];
				$data["PersonDispVizit_NextDate"] = $result["PersonDispVizit_NextDate"];
				$procedure = "p_PersonDispVizit_upd";
			}
		}
		if (empty($data["PersonDisp_id"])) {
			//то создавать нечего, выходим
			return false;
		}
		$selectString = "
       		PersonDispVizit_id as \"PersonDispVizit_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
				PersonDispVizit_id := :PersonDispVizit_id,
				PersonDisp_id := :PersonDisp_id,
				PersonDispVizit_NextDate := :PersonDispVizit_NextDate,
				PersonDispVizit_NextFactDate := :PersonDispVizit_NextFactDate,
				EvnVizitPL_id := :EvnVizitPL_id,
				pmUser_id := :pmUser_id
			)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonDispSopDiag(Polka_PersonDisp_model $callObject, $data)
	{
		$procedure = $data["PersonDispSopDiag_id"] ? "p_PersonDispSopDiag_upd" : "p_PersonDispSopDiag_ins";
		$selectString = "
       		PersonDispSopDiag_id as \"PersonDispSopDiag_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
				PersonDispSopDiag_id := :PersonDispSopDiag_id,
				PersonDisp_id := :PersonDisp_id,
				Diag_id := :Diag_id,
				DopDispDiagType_id := :DopDispDiagType_id,
				pmUser_id := :pmUser_id
			)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonDispHist(Polka_PersonDisp_model $callObject, $data)
	{
		$check = $callObject->checkPersonDispHistDates($data);
		if (!empty($check) && !empty($check["Error_Msg"])) {
			return $check;
		}
		$procedure = $data["PersonDispHist_id"] ? "p_PersonDispHist_upd" : "p_PersonDispHist_ins";
		$selectString = "
       		PersonDispHist_id as \"PersonDispHist_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
				PersonDispHist_id := :PersonDispHist_id,
				PersonDisp_id := :PersonDisp_id,
				MedPersonal_id := :MedPersonal_id,
				LpuSection_id := :LpuSection_id,
				PersonDispHist_begDate := :PersonDispHist_begDate,
				PersonDispHist_endDate := :PersonDispHist_endDate,
				pmUser_id := :pmUser_id
			)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function savePersonDispTargetRate(Polka_PersonDisp_model $callObject, $data)
	{
		$callObject->beginTransaction();
		$sql = "
			select
				case
					when RVT.RateValueType_SysNick = 'int' THEN R.Rate_ValueInt::varchar
					when RVT.RateValueType_SysNick = 'float' THEN R.Rate_ValueFloat::decimal(16,3)::varchar
					when RVT.RateValueType_SysNick = 'string' THEN R.Rate_ValueStr
				end as \"RateValue\"
			from
				v_PersonDispTargetRate PDTR
				inner join v_Rate R on R.Rate_id = PDTR.Rate_did
				inner join v_RateType RT on RT.RateType_id = R.RateType_id
				inner join RateValueType RVT on RVT.RateValueType_id = RT.RateValueType_id
			where PDTR.PersonDisp_id = :PersonDisp_id
			  and R.RateType_id = :RateType_id
			order by PersonDispTargetRate_setDT desc
			limit 1
		";
		$sqlParams = [
			"PersonDisp_id" => $data["PersonDisp_id"],
			"RateType_id" => $data["RateType_id"]
		];
		/**@var CI_DB_result $result */
		$res = $callObject->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		$res = $res->result_array();
		if (!count($res) || $res[0]["RateValue"] != $data["TargetRate_Value"]) {
			$sql = "
				select
        			Rate_id as \"Rate_id\",
        			Error_Code as \"Error_Code\",
        			Error_Message as \"Error_Msg\"
				from p_Rate_ins(
					Rate_id := :Rate_id,
					RateType_id := :RateType_id,
					Rate_ValueInt := :Rate_ValueInt,
					Rate_ValueFloat := :Rate_ValueFloat,
					Rate_ValueStr := :Rate_ValueStr,
					Server_id := :Server_id,
					pmUser_id := :pmUser_id
				)
			";
			$queryParams = [
				"Rate_id" => null,
				"RateType_id" => $data["RateType_id"],
				"Rate_ValueInt" => null,
				"Rate_ValueFloat" => null,
				"Rate_ValueStr" => null,
				"Server_id" => $data["Server_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			switch ($data["RateValueType_SysNick"]) {
				case "int":
					$queryParams["Rate_ValueInt"] = $data["TargetRate_Value"];
					break;
				case "float":
					$queryParams["Rate_ValueFloat"] = $data["TargetRate_Value"];
					break;
				case "string":
					$queryParams["Rate_ValueStr"] = $data["TargetRate_Value"];
					break;
			}
			$result = $callObject->db->query($sql, $queryParams);
			if (!is_object($result)) {
				$callObject->rollbackTransaction();
				throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение целевого показателя)");
			}
			$resp = $result->result_array();
			$sql = "
				select
        			persondisptargetrate_id as \"PersonDispTargetRate_id\",
        			Error_Code as \"Error_Code\",
        			Error_Message as \"Error_Msg\"
				from p_PersonDispTargetRate_ins(
					PersonDispTargetRate_id := :PersonDispTargetRate_id,
					PersonDisp_id := :PersonDisp_id,
					PersonDispTargetRate_setDT := dbo.tzGetDate(),
					Rate_did := :Rate_id,
					pmUser_id := :pmUser_id
				)
			";
			$queryParams = [
				"PersonDispTargetRate_id" => null,
				"PersonDisp_id" => $data["PersonDisp_id"],
				"Rate_id" => $resp[0]["Rate_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($sql, $queryParams);
			if (!is_object($result)) {
				$callObject->rollbackTransaction();
				throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение целевого показателя)");
			}
		}
		$PersonDispFactRateData = json_decode($data["PersonDispFactRateData"], true);
		if (is_array($PersonDispFactRateData) && count($PersonDispFactRateData)) {
			foreach ($PersonDispFactRateData as $prfr) {
				$prfr = array_merge($data, $prfr);
				switch ($prfr["RecordStatus_Code"]) {
					case 1:
						break;
					case 0:
					case 2:
						$callObject->savePersonDispFactRate($prfr);
						break;
					case 3:
						$callObject->deletePersonDispFactRate($prfr);
						break;
				}
			}
		}
		$callObject->commitTransaction();
		return [["success" => true, "Error_Code" => "", "Error_Msg" => ""]];
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function savePersonDispFactRate(Polka_PersonDisp_model $callObject, $data)
	{
		$procedure = (!empty($data["PersonDispFactRate_id"]) && $data["PersonDispFactRate_id"] > 0) ? "upd" : "ins";
		$selectString = "
       		Rate_id as \"Rate_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$sql = "
			select {$selectString}
			from p_Rate_{$procedure}(
				Rate_id := :Rate_id,
				RateType_id := :RateType_id,
				Rate_ValueInt := :Rate_ValueInt,
				Rate_ValueFloat := :Rate_ValueFloat,
				Rate_ValueStr := :Rate_ValueStr,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = [
			"Rate_id" => ($procedure == "p_Rate_upd") ? $data["Rate_id"] : null,
			"RateType_id" => $data["RateType_id"],
			"Rate_ValueInt" => null,
			"Rate_ValueFloat" => null,
			"Rate_ValueStr" => null,
			"Server_id" => $data["Server_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		switch ($data["RateValueType_SysNick"]) {
			case "int":
				$queryParams["Rate_ValueInt"] = $data["PersonDispFactRate_Value"];
				break;
			case "float":
				$queryParams["Rate_ValueFloat"] = $data["PersonDispFactRate_Value"];
				break;
			case "string":
				$queryParams["Rate_ValueStr"] = $data["PersonDispFactRate_Value"];
				break;
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение фактического показателя)");
		}
		$resp = $result->result_array();
		$selectString = "
       		PersonDispFactRate_id as \"PersonDispFactRate_id\",
       		Error_Code as \"Error_Code\",
       		Error_Message as \"Error_Msg\"
		";
		$sql = "
			select {$selectString}
			from p_PersonDispFactRate_{$procedure}(
				PersonDispFactRate_id := :PersonDispFactRate_id,
				PersonDisp_id := :PersonDisp_id,
				PersonDispFactRate_setDT := :PersonDispFactRate_setDT,
				Rate_id := :Rate_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = [
			"PersonDispFactRate_id" => ($procedure == "p_Rate_upd") ? $data["PersonDispFactRate_id"] : NULL,
			"PersonDisp_id" => $data["PersonDisp_id"],
			"Rate_id" => ($procedure == "p_Rate_upd") ? $data["Rate_id"] : $resp[0]["Rate_id"],
			"PersonDispFactRate_setDT" => $data["PersonDispFactRate_setDT"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение фактического показателя)");
		}
		return true;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function savePersonChartFeedback(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Chart_id" => $data["Chart_id"],
			"FeedbackMethod_id" => $data["FeedbackMethod_id"]
		];
		$sql = "
			update LabelObserveChart
			set FeedbackMethod_id = :FeedbackMethod_id
			where LabelObserveChart_id = :Chart_id
		";
		$callObject->db->query($sql, $params);
		$sql = "
			select FeedbackMethod_Name as \"FeedbackMethod_Name\"
			from FeedbackMethod
			where FeedbackMethod_id = :FeedbackMethod_id
		";
		$feedback_name = $callObject->getFirstResultFromQuery($sql, $params);
		return [[
			"success" => true,
			"FeedbackMethod_Name" => $feedback_name,
			"FeedbackMethod_id" => $data["FeedbackMethod_id"],
			"Error_Msg" => ""
		]];
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveLabelObserveChartRate(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			update LabelObserveChartRate
			set LabelObserveChartRate_Min = :LabelRateMin,
				LabelObserveChartRate_Max = :LabelRateMax
			where LabelObserveChart_id = :Chart_id
			  and LabelRate_id = :LabelRate_id
		";
		$callObject->db->query($sql, $data);
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $measure
	 * @param $data
	 * @return array|bool
	 */
	public static function saveLabelObserveChartRateMeasure(Polka_PersonDisp_model $callObject, $data)
	{
		$params = array(
			'LabelObserveChartInfo_id' => $data['LabelObserveChartInfo_id'],
			'LabelObserveChartRate_id' => $data['ChartRate_id'],
			'LabelObserveChartMeasure_Value' => $data['Measure_value'],
			'pmUser_id' => $data['pmUser_id'],
			'LabelObserveChartMeasure_id' => null
		);

		$action = 'ins';

		if (!empty($measure['Measure_id'])) {
			$params['LabelObserveChartMeasure_id'] = $data['Measure_id'];
			$action = 'upd';
		}

		$query = "
			select 
				LabelObserveChartMeasure_id as \"LabelObserveChartMeasure_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LabelObserveChartMeasure_{$action}(
				LabelObserveChartMeasure_id := :LabelObserveChartMeasure_id,
				LabelObserveChartInfo_id := :LabelObserveChartInfo_id,
				LabelObserveChartRate_id := :LabelObserveChartRate_id,
				LabelObserveChartMeasure_Value := cast(:LabelObserveChartMeasure_Value as varchar),
				pmUser_id := :pmUser_id
			)
		";

		return $callObject->getFirstResultFromQuery($query, $params);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function saveLabelObserveChartMeasure(Polka_PersonDisp_model $callObject, $data)
	{
		$params = array(
			'LabelObserveChart_id' => $data['Chart_id'],
			'LabelObserveChartInfo_ObserveDate' => $data['ObserveDate'],
			'TimeOfDay_id' => $data['ObserveTime_id'],
			'FeedbackMethod_id' => $data['FeedbackMethod_id'],
			'LabelObserveChartInfo_Complaint' => $data['Complaint'],
			'LabelObserveChartSource_id' => !empty($data['LabelObserveChartSource_id']) ? $data['LabelObserveChartSource_id'] : 1,
			'pmUser_id' => $data['pmUser_id'],
			'LabelObserveChartInfo_id' => null
		);

		$action = 'ins';

		if (!empty($data['ChartInfo_id'])) {
			$params['LabelObserveChartInfo_id'] = $data['ChartInfo_id'];
			$action = 'upd';

			// проверяем замер
			$chartSource = $callObject->getFirstRowFromQuery("
				select
					LabelObserveChartInfo_id,
					LabelObserveChartSource_id
				from v_LabelObserveChartInfo
				where LabelObserveChartInfo_id = :LabelObserveChartInfo_id
				limit 1
			", array(
				'LabelObserveChartInfo_id' => $data['ChartInfo_id']
			));

			// сохраняем тот же первоначальный источник, если есть
			$params['LabelObserveChartSource_id'] = !empty($chartSource['LabelObserveChartSource_id']) ? $chartSource['LabelObserveChartSource_id'] : 1;
		}
		
		$query = "
			select 
				LabelObserveChartInfo_id as \"LabelObserveChartInfo_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LabelObserveChartInfo_{$action}(
				LabelObserveChartInfo_id := :LabelObserveChartInfo_id,
				LabelObserveChart_id := :LabelObserveChart_id,
				LabelObserveChartInfo_ObserveDate := :LabelObserveChartInfo_ObserveDate,
				LabelObserveChartSource_id := :LabelObserveChartSource_id,
				TimeOfDay_id := :TimeOfDay_id,
				LabelObserveChartInfo_Complaint := :LabelObserveChartInfo_Complaint,
				FeedbackMethod_id := :FeedbackMethod_id,
				pmUser_id := :pmUser_id
			)
		";

		$callObject->beginTransaction();
		$result = $callObject->getFirstRowFromQuery($query, $params);

		if (empty($result['LabelObserveChartInfo_id']) || !empty($result['Error_Msg'])) {

			$callObject->rollbackTransaction();
			$err_msg = (!empty($result['Error_Msg'])) ? ': '.$result['Error_Msg'] : '';

			return array(
				'Error_Msg' => 'Ошибка сохранения общей информации по замеру'.$err_msg
			);
		}

		//Сохранение замеров
		$result['measure'] = array();
		if (!empty($data['RateMeasures'])) {
			foreach($data['RateMeasures'] as $RateMeasure) {

				$RateMeasure->LabelObserveChartInfo_id = $result['LabelObserveChartInfo_id'];
				$RateMeasure->pmUser_id = $data['pmUser_id'];

				$measure = $callObject->saveLabelObserveChartRateMeasure((array)$RateMeasure);

				if (!empty($measure['Error_Msg'])) {

					$callObject->rollbackTransaction();
					$err_msg = (!empty($measure['Error_Msg'])) ? ': '.$measure['Error_Msg'] : '';

					return array(
						'Error_Msg' => 'Ошибка сохранения измерения'.$err_msg
					);
				}

				$result['measure'][] = array($measure, $RateMeasure);
			}
		}

		$callObject->commitTransaction();
		return $result;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonChartInfo(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Chart_id" => $data["Chart_id"],
			"Chart_begDate" => $data["Chart_begDate"],
			"PersonModel_id" => $data["PersonModel_id"],
			"email" => $data["email"],
			"sms" => $data["sms"],
			"voice" => $data["voice"]
		];
		$set = "";
		if (!empty($data["Chart_begDate"])) {
			$set = "LabelObserveChart_begDate = :Chart_begDate";
		}
		if (!empty($data["PersonModel_id"])) {
			$set = "PersonModel_id = :PersonModel_id";
		}
		if (!empty($data["email"])) {
			$set = "LabelObserveChart_Email = :email";
		}
		if (!empty($data["sms"])) {
			$set = "LabelObserveChart_Phone = :sms";
		}
		if (!empty($data["voice"])) {
			$set = "LabelObserveChart_Phone = :voice";
		}
		if (empty($set)) {
			return true;
		}
		$sql = "
			update LabelObserveChart
			set {$set}
			where LabelObserveChart_id = :Chart_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}
}