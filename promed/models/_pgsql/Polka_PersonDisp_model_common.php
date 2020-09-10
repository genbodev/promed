<?php

class Polka_PersonDisp_model_common
{
	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function InviteInMonitoring(Polka_PersonDisp_model $callObject, $data)
	{
		$callObject->load->helper("Notify");
		if ($data["Persons"]) {
			foreach ($data["Persons"] as $person) {
				$msg = $data["MessageText"];
				if ($data["isSingle"] == "false") {
					$msg = str_replace("<ФИО>", "{$person->Person_SurName} {$person->Person_FirName} {$person->Person_SecName}", $msg);
					$msg = str_replace("<Имя>", $person->Person_FirName, $msg);
					$msg = str_replace("<Первые буквы фамилии и отчества без пробела>", mb_substr($person->Person_SurName, 0, 1) . mb_substr($person->Person_SecName, 0, 1), $msg);
				}
				$title = empty($data["MessageTitle"]) ? "Программа дистанционного мониторинга здоровья" : $data["MessageTitle"];
				switch ($data["FeedbackMethod"]) {
					case 3:
						if (!empty($person->email))
							sendNotifyEmail(["EMail" => $person->email, "title" => $title, "body" => $msg]);
						break;
					case 1:
					case 2:
						if (!empty($person->phone)) {
							sendNotifySMS(["UserNotify_Phone" => $callObject->getPhoneNumber($person->phone), "text" => $msg, "User_id" => $data["pmUser_id"]]);
						}
						break;
					case 4:
					case 5:
						sendPushNotification(["Person_id" => $person->Person_id, "message" => $msg, "PushNoticeType_id" => 3, "action" => "call"]);
						break;
				}
				//уведомление пациенту отправлено.
				//теперь запишем в таблицу для хранения приглашений:
				$params = [
					"PersonLabel_id" => $person->PersonLabel_id,
					"MedStaffFact_id" => $data["MedStaffFact_id"],
					"FeedbackMethod_id" => $data["FeedbackMethod_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				//но сначала сбросим статусы у имеющихся приглашений по метке
				$sql = "
					update LabelInvite
					set LabelInviteStatus_id = null
					where PersonLabel_id = :PersonLabel_id
				";
				$callObject->db->query($sql, $params);
				$sql = "
					select
        				LabelInvite_id as \"LabelInvite_id\",
        				Error_Code as \"Error_Code\",
        				Error_Message as \"Error_Msg\"
					from p_LabelInvite_ins(
						PersonLabel_id := :PersonLabel_id,
						MedStaffFact_id := :MedStaffFact_id,
						FeedbackMethod_id := :FeedbackMethod_id,
						LabelInviteStatus_id := 1,
						LabelInvite_RefuseCause := '',
						pmUser_id := :pmUser_id
					)
				";
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($sql, $params);
				if (is_object($result)) {
					$result = $result->first_row();
					$params = [
						"LabelInvite_id" => $result->LabelInvite_id,
						"LabelInviteStatus_id" => 1,
						"MedStaffFact_id" => $data["MedStaffFact_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$sql = "
						select
        					LabelInviteHistory_id as \"LabelInviteHistory_id\",
        					Error_Code as \"Error_Code\",
        					Error_Message as \"Error_Msg\"
						from p_LabelInviteHistory_ins(
							LabelInvite_id := :LabelInvite_id,
							LabelInviteStatus_id := :LabelInviteStatus_id,
							LabelInviteHistory_setDT := dbo.tzGetDate(),
							MedStaffFact_id := :MedStaffFact_id,
							pmUser_id := :pmUser_id
						)
					";
					$callObject->db->query($sql, $params);
				}
			}
		}
		return true;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function RemindToMonitoring(Polka_PersonDisp_model $callObject, $data)
	{
		//Запасаемся справочником шаблонов текстов напоминаний по методам отправки
		$sql = "
			select
				LabelMessageText_Text as \"LabelMessageText_Text\",
				FeedbackMethod_id as \"FeedbackMethod_id\",
				Label_id as \"Label_id\"
			from LabelMessageText 
			where LabelMessageType_id=2
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		$result = $result->result_array();
		$MsgAtFeedback = [];
		$MsgAtFeedback7 = [];
		$title = "";
		foreach ($result as $r) {
			$FeedbackMethod_id = $r["FeedbackMethod_id"];
			$s = $r["LabelMessageText_Text"];
			if ($FeedbackMethod_id == 3) {
				//email
				$title = (preg_match("/ТЕМА\:(.+)/iu", $s, $matches)) ? trim($matches[1]) : "";
				if (preg_match("/ТЕМА\:(.+)/iu", $s, $matches)) {
					$s = substr($s, strlen($matches[0]));
				}
				$body = trim($s);
				if ($r["Label_id"] == "7") {
					$MsgAtFeedback7[$FeedbackMethod_id] = $body;
				} else {
					$MsgAtFeedback[$FeedbackMethod_id] = $body;
				}
			} else {
				if ($r["Label_id"] == "7") {
					$MsgAtFeedback7[$FeedbackMethod_id] = $s;
				} else {
					$MsgAtFeedback[$FeedbackMethod_id] = $s;
				}
			}
		}
		//Запасаемся контактами кабинета здоровья
		if (empty($data["session"]) || empty($data["LpuSection_id"])) {
			return false;
		}
		$params = ["LpuSection_id" => $data["LpuSection_id"]];
		$sql = "
			select 
				trim(coalesce(LBH.LpuBuildingHealth_Phone, '')) as \"phone\",
				trim(coalesce(LBH.LpuBuildingHealth_Email, '')) as \"email\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_LpuSection LS 
				left join v_LpuUnit LU on LS.LpuUnit_id = LU.LpuUnit_id
				left join v_Lpu L on L.Lpu_id = LU.Lpu_id
				left join v_LpuBuilding LB on LU.LpuBuilding_id = LB.LpuBuilding_id
				left join v_LpuBuildingHealth LBH on LBH.LpuBuilding_id = LB.LpuBuilding_id
			where LS.LpuSection_id = :LpuSection_id
		";
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		$cab = $result->first_row();
		$callObject->load->helper("Notify");
		if ($data["Persons"]) {
			foreach ($data["Persons"] as $person) {
				$FeedbackMethod_id = $person->FeedbackMethod_id;
				if ($FeedbackMethod_id == 1){ $FeedbackMethod_id = 2;}
				if ($FeedbackMethod_id == 4){ $FeedbackMethod_id = 5;}
				if ($FeedbackMethod_id) {
					$msg = ($person->Label_id == "7")?$MsgAtFeedback7[$FeedbackMethod_id]:$MsgAtFeedback[$FeedbackMethod_id];
					$msg = str_replace("<ФИО>", "{$person->Person_SurName} {$person->Person_FirName} {$person->Person_SecName}", $msg);
					$msg = str_replace("<Имя>", $person->Person_FirName, $msg);
					$msg = str_replace("<Первые буквы фамилии и отчества без пробела>", mb_substr($person->Person_SurName, 0, 1) . mb_substr($person->Person_SecName, 0, 1), $msg);
					$msg = str_replace("<номер телефона кабинета здоровья>", $cab->phone, $msg);
					$msg = str_replace("<адрес электронной почты кабинета здоровья>", $cab->email, $msg);
					$msg = str_replace("<краткое наименование МО>", $cab->Lpu_Nick, $msg);
					$title = $title == "" ? "Программа дистанционного мониторинга здоровья" : $title;
					switch ($person->FeedbackMethod_id) {
						case 3:
							if (!empty($person->email)) {
								sendNotifyEmail(["EMail" => $person->email, "title" => $title, "body" => $msg]);
							}
							break;
						case 1:
						case 2:
							if (!empty($person->phone)) {
								sendNotifySMS(["UserNotify_Phone" => $callObject->getPhoneNumber($person->phone), "text" => $msg, "User_id" => $data["pmUser_id"]]);
							}
							break;
						case 4:
						case 5:
							sendPushNotification(["Person_id" => $person->Person_id, "message" => $msg, "PushNoticeType_id" => 3, "action" => "call"]);
							break;
					}
					//уведомление пациенту отправлено.
					//Сделать запись об отправке
					$params = [
						"MessageText" => $msg,
						"Chart_id" => $person->Chart_id,
						"FeedbackMethod_id" => $FeedbackMethod_id,
						"pmUser_id" => $data["pmUser_id"]
					];
					$sql = "
						select
	                        LabelMessage_id as \"LabelMessage_id\",
	                        Error_Code as \"Error_Code\",
	                        Error_Message as \"Error_Msg\"
						from p_LabelMessage_ins(
							LabelObserveChart_id := :Chart_id,
							LabelMessage_Text := :MessageText,
							LabelMessageType_id := 2,
							LabelMessage_sendDate := dbo.tzgetdate(),
							FeedbackMethod_id := :FeedbackMethod_id,
							pmUser_id := :pmUser_id
						)
					";
					$callObject->db->query($sql, $params);
				}
			}
		}
		return true;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function removePersonFromMonitoring(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"Chart_id" => $data["Chart_id"],
			"DispOutType_id" => $data["DispOutType_id"],
			"endDate" => $data["endDate"]
		];
		$sql = "
			update LabelObserveChart
			set LabelObserveChart_endDate = :endDate,
			    DispOutType_id = :DispOutType_id
			where LabelObserveChart_id = :Chart_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if ($result !== false) {
			if ($data["Label_id"] == "7") {
				$sql = "
					update PersonLabel
					set PersonLabel_disDate = :endDate
					where Person_id = :Person_id
					  and Label_id = 7
					  and PersonLabel_disDate is null
				";
				$res = $callObject->db->query($sql, $params);
				return ($res !== false) ? true : false;
			}
			return true;
		}
		return false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function sendLabelMessage(Polka_PersonDisp_model $callObject, $data)
	{
		$callObject->load->helper("Notify");
		$msg = $data["MessageText"];
		$title = "Программа дистанционного мониторинга";
		switch ($data["FeedbackMethod_id"]) {
			case 3:
				if (!empty($data["email"]))
					sendNotifyEmail(["EMail" => $data["email"], "title" => $title, "body" => $msg]);
				break;
			case 1:
			case 2:
				if (!empty($data["phone"]))
					sendNotifySMS(["UserNotify_Phone" => $callObject->getPhoneNumber($data["phone"]), "text" => $msg, "User_id" => $data["pmUser_id"]]);
				break;
			case 4:
			case 5:
				sendPushNotification(["Person_id" => $data["Person_id"], "message" => $msg, "PushNoticeType_id" => 3, "action" => "call"]);
				break;
		}
		//уведомление пациенту отправлено.
		//Сделать запись об отправке
		$params = [
			"MessageText" => $msg,
			"Chart_id" => $data["Chart_id"],
			"FeedbackMethod_id" => $data["FeedbackMethod_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$sql = "
			select
				LabelMessage_id as \"LabelMessage_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LabelMessage_ins(
				LabelObserveChart_id := :Chart_id,
				LabelMessage_Text := :MessageText,
				LabelMessageType_id := 1,
				LabelMessage_sendDate := dbo.tzgetdate(),
				FeedbackMethod_id := :FeedbackMethod_id,
				pmUser_id := :pmUser_id
			)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $userId
	 * @return bool
	 */
	public static function setLabels(Polka_PersonDisp_model $callObject, $userId)
	{
		ini_set("memory_limit", "2048M");
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");
		session_set_cookie_params(86400);
		ini_set("session.gc_maxlifetime", 86400);
		ini_set("session.cookie_lifetime", 86400);
		$callObject->load->library("textlog", ["file" => "setLabels_" . date("Y-m-d") . ".log"]);
		$sql = "
			select to_char(Log_Time, '{$callObject->dateTimeFormUnixDate}')
			from log 
			where Log_Object = 'PersonLabelUpdate'
			order by Log_Time desc
			limit 1
		";
		$last_exec_DT = $callObject->getFirstResultFromQuery($sql);
		if (!empty($last_exec_DT)){
			$callObject->textlog->add("setLabels: previous start script at {$last_exec_DT}");
		}
		$callObject->textlog->add("setLabels: start script.");
		$sql = "
			select LD.Label_id as \"Label_id\"
			from v_LabelDiag LD
			where LD.UslugaComplex_id is not null
		";
		/**@var CI_DB_result $resp */
		$resp = $callObject->db->query($sql);
		if (!is_object($resp)) {return false;}
		$labels = $resp->result_array();
		foreach ($labels as $label) {
			$callObject->textlog->add("setLabels.add: start add for Label_id = {$label["Label_id"]}");
			$sql = "
				select PS.Person_id as \"Person_id\"
				from
					v_labelDiag LD
					inner join v_PersonState PS on (PS.Sex_id = LD.Sex_id or LD.Sex_id is null)
					    and PS.Person_deadDT is null
						and (LD.LabelDiag_From is null or date_part('year', tzGetDate() - PS.Person_BirthDay) > LD.LabelDiag_From)
						and (LD.LabelDiag_To is null or date_part('year', tzGetDate() - PS.Person_BirthDay) < LD.LabelDiag_To)
				where LD.Label_id=:Label_id
				  and not exists (
						select EUP.EvnUslugaPar_id 
						from v_EvnUslugaPar EUP 
						where EUP.UslugaComplex_id = LD.UslugaComplex_id 
						  and EUP.Person_id = PS.Person_id 
						  and date_part('year', tzGetDate() - EUP.EvnUslugaPar_disDT) < LD.LabelDiag_Period
				  )
				  and not exists(
						select PL.PersonLabel_id 
						from v_PersonLabel PL 
						where PL.Person_id=PS.Person_id and PL.Label_id=:Label_id and PL.PersonLabel_disDate is null
				  )
			";
			$sqlParams = ["Label_id" => $label["Label_id"]];
			$resp = $callObject->db->query($sql, $sqlParams);
			if (!is_object($resp)) {
				return false;
			}
			$persons = $resp->result_array();
			$totalcount = count($persons);
			$i = 0;
			foreach ($persons as $row) {
				$i++;
				$sql = "
					select
        				PersonLabel_id as \"PersonLabel_id\",
        				Error_Code as \"Error_Code\",
        				Error_Message as \"Error_Msg\"
        			from p_PersonLabel_ins(
        				Label_id := :Label_id,
						Person_id := :Person_id,
						PersonLabel_setDate := dbo.tzGetDate(),
						PersonLabel_disDate := null,
						Diag_id := null,
						pmUser_id := :pmUser_id
        			)
				";
				$sqlParams = [
					"Label_id" => $label["Label_id"],
					"Person_id" => $row["Person_id"],
					"pmUser_id" => $userId
				];
				$resp = $callObject->db->query($sql, $sqlParams);
				if ($i % 1000 == 0)
					$callObject->textlog->add("setLabels.add: progress $i / $totalcount");
			}//foreach person
			unset($persons);
			$resp->free_result();
			$callObject->textlog->add("setLabels.add: count = [{$totalcount}] . Finish add for Label_id = {$label["Label_id"]}");
		}
		$callObject->textlog->add("setLabels.add: finish.");
		// Проверка меток за время с последнего запуска
		if (!empty($last_exec_DT)) {
			$callObject->textlog->add("setLabels.upd: start update Labels.");
			foreach ($labels as $label) {
				$callObject->textlog->add("setLabels.upd: update for Label_id = {$label["Label_id"]}");
				$sql = "
					select
						PS.Person_id as \"Person_id\",
						l_usluga.EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
						l_usluga.EvnUslugaPar_id as \"EvnUslugaPar_id\"
					from
						v_LabelDiag LD
						inner join v_PersonState PS on (PS.Sex_id = LD.Sex_id or LD.Sex_id is null)
						    and PS.Person_deadDT is null
							and (LD.LabelDiag_From is null or date_part('year', tzgetdate() - PS.Person_BirthDay) > LD.LabelDiag_From)
							and (LD.LabelDiag_To is null or date_part('year', tzgetdate() - PS.Person_BirthDay) < LD.LabelDiag_To)
						left join lateral(
							select
								EUP.EvnUslugaPar_id,
							    EUP.EvnUslugaPar_setDT,
							    PL.PersonLabel_id
							from
								v_EvnUslugaPar EUP 
								inner join v_PersonLabel PL on LD.Label_id = PL.Label_id and PL.Person_id = PS.Person_id and PL.PersonLabel_disDate is null
							where EUP.UslugaComplex_id = LD.UslugaComplex_id 
							  and EUP.Person_id = PS.Person_id 
							  and EUP.EvnUslugaPar_setDT is not null
							  and EUP.EvnUslugaPar_disDT > :last_exec_dt
							order by EUP.EvnUslugaPar_setDT desc
							limit 1
						) as l_usluga on true
					where LD.Label_id=:Label_id
					  and l_usluga.EvnUslugaPar_id is not null
					  and l_usluga.PersonLabel_id is not null
				";
				$sqlParams = [
					"Label_id" => $label["Label_id"],
					"last_exec_dt" => $last_exec_DT
				];
				$resp = $callObject->db->query($sql, $sqlParams);
				if (!is_object($resp)){ return false;}
				$persons = $resp->result_array();
				$totalcount = count($persons);
				$i = 0;
				foreach ($persons as $row) {
					$i++;
					$sql = "
						update PersonLabel
						set PersonLabel_disDate = :EvnUslugaPar_setDT 
						where PersonLabel_id = :PersonLabel_id
					";
					$sqlParams = [
						"EvnUslugaPar_setDT" => $label["EvnUslugaPar_setDT"],
						"PersonLabel_id" => $row["PersonLabel_id"]
					];
					$resp = $callObject->db->query($sql, $sqlParams);
					if ($i % 1000 == 0) {
						$callObject->textlog->add("setLabels.upd: progress ". $i / $totalcount);
					}
				}
				unset($persons);
				$resp->free_result();
				$callObject->textlog->add("setLabels.upd: count = [{$totalcount}] . Finish update for Label_id = {$label["Label_id"]}");
			}
		}
		$callObject->textlog->add("setLabels: script finished.");
		$sql = "
			select *
			from p_Log_set(
				Log_Object := 'PersonLabelUpdate',
				Log_Message := 'Обновление меток',
				Log_Type := 'debug'
			)
		";
		$callObject->db->query($sql);
		return true;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkPersonDispExists(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"PersonDisp_id" => $data["PersonDisp_id"],
			"Person_id" => $data["Person_id"],
			"Diag_id" => $data["Diag_id"],
			"Lpu_id" => $data["Lpu_id"],
			"PersonDisp_begDate" => $data["PersonDisp_begDate"],
			"PersonDisp_endDate" => $data["PersonDisp_endDate"]
		];
		$query = "select D.Diag_Code as \"Diag_Code\" from v_Diag D where D.Diag_id = :Diag_id limit 1";
		$params["Diag_Code"] = $callObject->getFirstResultFromQuery($query, $params);
		if (!$params["Diag_Code"]) {
			return false;
		}
		$query = "
			select
				PD.PersonDisp_id as \"PersonDisp_id\",
				D.Diag_FullName as \"Diag_FullName\",
				to_char(PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\"
			from
				v_PersonDisp PD
				inner join v_Diag D on D.Diag_id = PD.Diag_id
			where
				PD.Person_id = :Person_id
				and PD.Lpu_id = :Lpu_id
				and PD.PersonDisp_id != coalesce(:PersonDisp_id::bigint, 0)
				and (
					PD.PersonDisp_endDate is null or
					:PersonDisp_begDate between PD.PersonDisp_begDate and PD.PersonDisp_endDate
				)
				and substring(D.Diag_Code, 1, 2) ilike substring(:Diag_Code, 1, 2)
			order by PD.PersonDisp_begDate desc
			limit 1
		";
		$response = $callObject->queryResult($query, $params);
		return (count($response) != 0) ? ["existsPersonDisp" => $response[0], "success" => true] : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 */
	public static function deletePersonDispMedicament(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonDispMedicament_del(PersonDispMedicament_id := :PersonDispMedicament_id)
		";
		$sqlParams = ["PersonDispMedicament_id" => $data["PersonDispMedicament_id"]];
		$callObject->db->query($sql, $sqlParams);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result|false
	 */
	public static function deletePersonDisp(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				PD.Diag_id as \"Diag_id\",
				PD2.Person_id as \"Person_id\"
			from
				v_PersonDisp PD 
				left join v_PersonDisp PD2 on PD2.Diag_id = PD.Diag_id and PD2.Person_id = PD.Person_id
				inner join v_PersonLabel PL on PL.Person_id = PD.Person_id and PL.Diag_id = PD.Diag_id
			where PD.PersonDisp_id = :PersonDisp_id
			  and PL.Label_id = 1
		";
		$params = ["PersonDisp_id" => $data["PersonDisp_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result_array();
		$sql = "
			select
				Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_PersonDisp_del(
				PersonDisp_id := :PersonDisp_id,
				pmUser_id := :pmUser_id
			)
		";
		$sqlParams = [
			"PersonDisp_id" => $data["PersonDisp_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$result = $callObject->queryResult($sql, $sqlParams);
		if ($callObject->isSuccessful($result)) {
			if (is_array($result) && count($result) > 0) {
				if (trim($result[0]["Error_Msg"]) == "") {
					//все нормально удалилось => проверяем метки
					if (is_array($response) && count($response) == 1) {
						//если у пациента это была единственная диспансерная карта по этому диагнозу
						//нужно удалить метку у пациента
						$sql = "
							delete from PersonLabel
							where Person_id = :Person_id
							  and Diag_id = :Diag_id
							  and Label_id = 1
						";
						$params = [
							"Diag_id" => $response[0]["Diag_id"],
							"Person_id" => $response[0]["Person_id"]
						];
						$callObject->db->query($sql, $params);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteDiagDispCard(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_DiagDispCard_del(DiagDispCard_id := :DiagDispCard_id)
		";
		$sqlParams = ["DiagDispCard_id" => $data["DiagDispCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		//Обновим диспансерную карту - проставим ей последний диагноз из истории. Чтобы не наворотить много писанины, сделал пока через прямой апдейт.
		$query_set_diag = "
        	update PersonDisp
            set Diag_id = (
                select DDC.Diag_id
                from v_DiagDispCard DDC
                where DDC.PersonDisp_id = :PersonDisp_id
                order by DDC.DiagDispCard_insDT desc
                limit 1
            )
            where PersonDisp_id = :PersonDisp_id
		";
		$sqlParams = ["PersonDisp_id" => $data["PersonDisp_id"]];
		$callObject->db->query($query_set_diag, $sqlParams);
		return $result->result_array();
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $PersonDisp_id
	 * @param $PersonDispVizit_NextDate
	 * @param $PersonDispVizit_id
	 * @return bool
	 */
	public static function checkVisitDoubleNextdate(Polka_PersonDisp_model $callObject, $PersonDisp_id, $PersonDispVizit_NextDate, $PersonDispVizit_id)
	{
		if (empty($PersonDispVizit_NextDate)) {
			return false;
		}
		$where = (!empty($PersonDispVizit_id)) ? " and PDV.PersonDispVizit_id <> :PersonDispVizit_id" : "";
		$sql = "
			select PDV.PersonDispVizit_id as \"PersonDispVizit_id\"
			from v_PersonDispVizit PDV
			where PDV.PersonDisp_id = :PersonDisp_id
			  and PersonDispVizit_NextDate = :PersonDispVizit_NextDate
			  {$where}
		";
		$sqlParams = [
			"PersonDisp_id" => $PersonDisp_id,
			"PersonDispVizit_NextDate" => $PersonDispVizit_NextDate,
			"PersonDispVizit_id" => $PersonDispVizit_id
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$visitArray = $result->result_array();
		return (!empty($visitArray[0]));
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function delPersonDispVizit(Polka_PersonDisp_model $callObject, $data)
	{
		$query = "
			select
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_PersonDispVizit_del(PersonDispVizit_id := :PersonDispVizit_id)
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
	public static function delPersonDispSopDiag(Polka_PersonDisp_model $callObject, $data)
	{
		$query = "
			select
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_PersonDispSopDiag_del(PersonDispSopDiag_id := :PersonDispSopDiag_id)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function checkPersonDispHistDates(Polka_PersonDisp_model $callObject, $data)
	{
		$personDispHist_begDate = strtotime($data["PersonDispHist_begDate"]);
		$personDispHist_endDate = (!empty($data["PersonDispHist_endDate"])) ? strtotime($data["PersonDispHist_endDate"]) : null;
		$query = "
			select
				to_char(PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\", 
				to_char(PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\"
			from v_PersonDisp 
			where PersonDisp_id = :PersonDisp_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		if (empty($result[0]["PersonDisp_begDate"])) {
			throw new Exception("Ошибка при получении данных по диспансерной карте.");
		}
		$personDisp_begDate = strtotime($result[0]["PersonDisp_begDate"]);
		$personDisp_endDate = null;
		if (!empty($result[0]["PersonDisp_endDate"])) {
			$personDisp_endDate = strtotime($result[0]["PersonDisp_endDate"]);
		}
		if ($personDispHist_begDate < $personDisp_begDate) {
			throw new Exception("Дата начала не может быть раньше даты взятия под наблюдение");
		}
		if (!empty($personDisp_endDate) && $personDispHist_begDate > $personDisp_endDate) {
			throw new Exception("Дата начала не может быть позже даты снятия с наблюдения");
		}
		$where = "";
		if (!empty($data["PersonDispHist_id"])) {
			$where = " and PersonDispHist_id <> :PersonDispHist_id";
		}
		$query = "
			select
				to_char(PersonDispHist_begDate, '{$callObject->dateTimeForm104}') as \"PersonDispHist_begDate\", 
				to_char(PersonDispHist_endDate, '{$callObject->dateTimeForm104}') as \"PersonDispHist_endDate\"
			from v_PersonDispHist 
			where PersonDisp_id = :PersonDisp_id {$where}
		";
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		if (count($result) == 0) {
			return false;
		}
		$error = false;
		foreach ($result as $value) {
			$begDate = strtotime($value["PersonDispHist_begDate"]);
			$endDate = null;
			if (!empty($value["PersonDispHist_endDate"])) {
				$endDate = strtotime($value["PersonDispHist_endDate"]);
				if (!empty($personDispHist_endDate)) {
					if ($personDispHist_begDate <= $begDate && $personDispHist_endDate >= $endDate) {
						$error = true;
						break;
					}
					if ($personDispHist_begDate >= $begDate && $personDispHist_endDate <= $endDate) {
						$error = true;
						break;
					}
					if ($personDispHist_begDate <= $begDate && $personDispHist_endDate <= $endDate && $personDispHist_endDate >= $begDate) {
						$error = true;
						break;
					}
					if ($personDispHist_begDate >= $begDate && $personDispHist_endDate >= $endDate && $personDispHist_begDate <= $endDate) {
						$error = true;
						break;
					}
				} else {
					if ($personDispHist_begDate <= $begDate) {
						$error = true;
						break;
					}
					if ($personDispHist_begDate > $begDate && $personDispHist_begDate <= $endDate) {
						$error = true;
						break;
					}
				}
			} else {
				if ($personDispHist_begDate >= $begDate) {
					$error = true;
					break;
				} else {
					if (!empty($personDispHist_endDate)) {
						if ($personDispHist_begDate < $begDate && $personDispHist_endDate >= $begDate) {
							$error = true;
							break;
						}
					} else {
						// Два бесконечных периода в любом случае пересекаются
						$error = true;
						break;
					}
				}
			}
		}
		if ($error == true) {
			throw new Exception("Периоды ответственности не должны пересекаться");
		}
		return true;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deletePersonDispHist(Polka_PersonDisp_model $callObject, $data)
	{
		$query = "
			select
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_PersonDispHist_del(PersonDispHist_id := :PersonDispHist_id)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function deletePersonDispFactRate(Polka_PersonDisp_model $callObject, $data)
	{
		$query = "
			select
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_PersonDispFactRate_del(PersonDispFactRate_id := :PersonDispFactRate_id)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (удаление фактического показателя)");
		}
		$query = "
			select
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_Rate_del(Rate_id := :Rate_id)
		";
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (удаление фактического показателя)");
		}
		return true;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function exportPersonDispCard(Polka_PersonDisp_model $callObject, $data)
	{
		$region = $callObject->getRegionNumber();
		$VizitType_SysNick = $callObject->getVizitTypeSysNick();
		if ($data["Year"] < 1753) {
			$data["Year"] = 1753;
		}
		$periodMonthYear = $data["Year"] . str_pad($data["Month"], 2, "0", STR_PAD_LEFT);
		if ($data["Month"] != 12) {
			$endMonth = str_pad($data["Month"] + 1, 2, "0", STR_PAD_LEFT);
			$endYear = $data["Year"];
			$begMonth = str_pad($data["Month"], 2, "0", STR_PAD_LEFT);
			$begYear = $endYear;
		} else {
			$endMonth = "01";
			$endYear = $data["Year"] + 1;
			$begMonth = "12";
			$begYear = $data["Year"];
		}
		$allresult = [];
		// Все СМО региона http://redmine.swan.perm.ru/issues/87551#note-30
		$query = "
			select
				OSmo.OrgSMO_id as \"OrgSMO_id\",
				OSmo.Orgsmo_f002smocod as \"Orgsmo_f002smocod\"
			from v_OrgSMO OSmo
			where OSmo.KLRgn_id = :Region
			  and (OSmo.OrgSmo_endDate is null or (OSmo.OrgSmo_endDate) >= :endDate) 
			  and coalesce(OSmo.OrgSMO_isDMS, 1) = 1
		";
		$queryParams = [
			"endDate" => "{$endYear}-{$endMonth}-01",
			"Region" => $region
		];
		/**@var CI_DB_result $allOrgSmo */
		$allOrgSmo = $callObject->db->query($query, $queryParams);
		if (!is_object($allOrgSmo)) {
			throw new Exception("Ошибка получения данных по СМО");
		}
		$allOrgSmo = $allOrgSmo->result_array();
		if ("09" == $begMonth) {
			$filter = "
				(pd.PersonDisp_begDate is null or pd.PersonDisp_begDate < :endDate) and
				(pd.PersonDisp_endDate is null or pd.PersonDisp_endDate >= :endDate)
			";
		} else {
			$filter = "
				(
					(
						date_part('year', pd.PersonDisp_begDate) = :Year
						and date_part('month', pd.PersonDisp_begDate) = :Month
					) 
					or (
						date_part('year', pd.PersonDisp_endDate) = :Year
						and date_part('month', pd.PersonDisp_endDate) = :Month
					)
					or exists (
						select
							PersonDispVizit_id
						from 
							v_PersonDispVizit
						where
							PersonDisp_id = pd.PersonDisp_id
							and date_part('year', PersonDispVizit_NextFactDate) = :Year
							and date_part('month', PersonDispVizit_NextFactDate) = :Month
						limit 1
					)
				)
			";
		}
		if ($callObject->GetRegionNick() == "kareliya") {
			$filter .= "
				and pls.Polis_begDate <= (:endDate::date - interval '1 day')
				and coalesce(pls.Polis_endDate, '2100-01-01') >= :begDate
			";
		}
		foreach ($allOrgSmo as $orgsmo) {
			$query = "
				select
					ps.Person_id as \"ID_PAC\",
					rtrim(ps.Person_Surname) as \"FAM\",
					rtrim(ps.Person_Firname) as \"IM\",
					rtrim(ps.Person_Secname) as \"OT\",
					ps.Person_Birthday as \"DR\",
					sx.Sex_fedid as \"W\",
					ps.Person_Snils as \"SNILS\",
					pt.PolisType_CodeF008 as \"VPOLIS\",
					pls.Polis_Ser as \"SPOLIS\",
					pls.Polis_Num as \"NPOLIS\",
					pd.PersonDisp_begDate as \"DATE_IN\",
					dg.Diag_Code as \"DS_DISP\",
					case when pd.PersonDisp_endDate is not null and to_char(pd.PersonDisp_endDate, '{$callObject->dateTimeForm104}') <= :periodMonthYear then pd.PersonDisp_endDate else null end as \"DATE_OUT\",
					case when pd.PersonDisp_endDate is not null and to_char(pd.PersonDisp_endDate, '{$callObject->dateTimeForm104}') <= :periodMonthYear then dot.DispOutType_Code else null end as \"RESULT_OUT\",
					coalesce(
					    case when LD.PersonDisp_LastDate is not null and lapdv.PersonDispVizit_NextFactDate is not null
						then
							case when LD.PersonDisp_LastDate > lapdv.PersonDispVizit_NextFactDate
							    then LD.PersonDisp_LastDate
							    else lapdv.PersonDispVizit_NextFactDate
							end
						else coalesce(lapdv.PersonDispVizit_NextFactDate, LD.PersonDisp_LastDate)
						end, pd.PersonDisp_begDate)
					as \"DATE_POC\",
					mp.Person_Snils as \"SNILS_VR\",
					pd.PersonDisp_id as \"PersonDisp_id\",
					pls.OrgSMO_id as \"OrgSMO_id\",
					pd.Lpu_id as \"Lpu_id\"
				from
					v_PersonDisp pd
					left join dbo.v_PersonState ps on pd.Person_id = ps.Person_id
					left join lateral(
						select pdv.PersonDispVizit_NextFactDate
						from v_PersonDispVizit pdv
						where pd.PersonDisp_id = pdv.PersonDisp_id
						order by pdv.PersonDispVizit_NextFactDate desc
						limit 1	
					) as lapdv on true
					left join lateral(
						select EVPL.EvnVizitPL_setDT as PersonDisp_LastDate
						from
							v_EvnVizitPL EVPL
							left join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
						where VT.VizitType_SysNick='{$VizitType_SysNick}'
						  and PD.PersonDisp_begDate::date <= EVPL.EvnVizitPL_setDT::date
						  and PD.Diag_id = EVPL.Diag_id
						  and EVPL.Person_id = PD.Person_id
						order by EVPL.EvnVizitPL_setDT desc
						limit 1	
					) as LD on true
					left join v_DispOutType dot on pd.DispOutType_id = dot.DispOutType_id
					left join lateral(
						select mpp.Person_Snils
						from
							v_MedPersonal mpp
							inner join v_PersonDispHist pdhist on pdhist.MedPersonal_id = mpp.MedPersonal_id
						where pdhist.PersonDisp_id = pd.PersonDisp_id
						  and pdhist.PersonDispHist_begDate < :endDate
						  and (pdhist.PersonDispHist_endDate is null or pdhist.PersonDispHist_endDate >= :begDate)
						order by pdhist.PersonDispHist_begDate desc
						limit 1	
					) as mp on true
					left join v_Sex sx on sx.Sex_id = ps.Sex_id
					left join v_Polis pls on pls.Polis_id = ps.Polis_id
					left join v_PolisType pt on pt.PolisType_id = pls.PolisType_id
					left join v_Diag dg on dg.Diag_id = pd.Diag_id
				LEFT JOIN LATERAL (
						select
							dsd.DispSickDiag_id
						from
							r10.v_DispSickDiag dsd
						where
							dsd.Diag_id = pd.Diag_id
							and coalesce(dsd.DispSickDiag_begDT, (:endDate::date - interval '1 day')) <= (:endDate::date - interval '1 day')
							and coalesce(dsd.DispSickDiag_endDT, (:endDate::date - interval '1 day')) >= (:endDate::date - interval '1 day')
							limit 1
					) dsd on true
				where
					{$filter}
					and pd.Lpu_id = :Lpu_id
					and pls.OrgSMO_id = :OrgSMO_id
					and (dg.Diag_Code between 'C00' and 'C97.9' or dbo.Age2(ps.Person_BirthDay, (:endDate::date - interval '1 day')) >= 18) -- пациентов старше 18 лет или диагноз из диапазона С00 – С97
					and (
						(dg.Diag_Code not between 'C00' and 'C97.9' and dsd.DispSickDiag_id  is not null )
						or
						dg.Diag_Code between 'C00' and 'C97.9'
					)
			";
			$queryParams = [
				"endDate" => "{$endYear}-{$endMonth}-01",
				"begDate" => "{$begYear}-{$begMonth}-01",
                "Year" => $data["Year"],
                "Month" => $data["Month"],
				"periodMonthYear" => $periodMonthYear,
				"Lpu_id" => $data["Lpu_id"],
				"OrgSMO_id" => $orgsmo["OrgSMO_id"],
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$response = [];
			while ($row = $result->_fetch_assoc()) {
				$row["DS_DISP"] = trim($row["DS_DISP"], ".");
				$row["OrgSMO_id"] = $orgsmo["Orgsmo_f002smocod"];
				switch ($row["RESULT_OUT"]) {
					case 4:
					case 5:
					case 6:
					case 7:
						$row["RESULT_OUT"] = 4;
						break;
				}
				foreach ($row as $key => $value) {
					if ($value instanceof DateTime) {
						$row[$key] = $value->format("Y-m-d");
					}
				}
				if (!isset($response[$row["ID_PAC"]])) {
					$response[$row["ID_PAC"]] = [
						"ID_PAC" => $row["ID_PAC"],
						"FAM" => $row["FAM"],
						"IM" => $row["IM"],
						"OT" => $row["OT"],
						"DR" => $row["DR"],
						"W" => $row["W"],
						"SNILS" => $row["SNILS"],
						"VPOLIS" => $row["VPOLIS"],
						"SPOLIS" => $row["SPOLIS"],
						"NPOLIS" => $row["NPOLIS"],
						"DN_FACT" => [],
					];
				}

				$response[$row["ID_PAC"]]["DN_FACT"][] = [
					"DATE_IN" => $row["DATE_IN"],
					"DS_DISP" => $row["DS_DISP"],
					"SNILS_VR" => $row["SNILS_VR"],
					"DATE_POC" => $row["DATE_POC"],
					"DATE_OUT" => $row["DATE_OUT"],
					"RESULT_OUT" => $row["RESULT_OUT"],
				];
			}
			$allresult[$orgsmo["Orgsmo_f002smocod"]] = $response;
		}
		return $allresult;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteLabelObserveChartMeasure(Polka_PersonDisp_model $callObject, $data)
	{
		$params = ["ChartInfo_id" => $data["ChartInfo_id"]];
		$query = "
			delete from LabelObserveChartMeasure 
			where LabelObserveChartInfo_id = :ChartInfo_id
		";
		$callObject->db->query($query, $params);
		$query = "
			select
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_LabelObserveChartInfo_del(LabelObserveChartInfo_id := :ChartInfo_id)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool|float|int|string
	 */
	public static function checkOpenedLabelObserveChart(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"PersonLabel_id" => $data["PersonLabel_id"],
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"Label_id" => $data["Label_id"]
		];
		$query = "
			select count(*)
			from
				v_PersonLabel PL2 
				inner join v_PersonLabel PL on PL2.Person_id = PL.Person_id AND PL.Label_id = :Label_id
				inner join v_LabelObserveChart LOC on LOC.PersonLabel_id = PL.PersonLabel_id
				inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = LOC.MedStaffFact_id
				inner join v_MedStaffFact MSF2 on MSF.Lpu_id = MSF2.Lpu_id and MSF2.MedStaffFact_id = :MedStaffFact_id
			where PL2.PersonLabel_id = :PersonLabel_id 
			  and PL2.Label_id = :Label_id
			  and LOC.LabelObserveChart_endDate is null
		";
		return $callObject->getFirstResultFromQuery($query, $params);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool|float|int|string
	 */
	public static function checkOpenedLabelObserveChartByPerson(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"Label_id" => $data["Label_id"]
		];
		$query = "
			select count(*)
			from
				v_PersonLabel PL
				inner join v_LabelObserveChart LOC on LOC.PersonLabel_id = PL.PersonLabel_id
				inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = LOC.MedStaffFact_id
				inner join v_MedStaffFact MSF2 on MSF.Lpu_id = MSF2.Lpu_id and MSF2.MedStaffFact_id = :MedStaffFact_id
			where LOC.LabelObserveChart_endDate is null 
			  and PL.Label_id = :Label_id
			  and PL.Person_id = :Person_id
		";
		return $callObject->getFirstResultFromQuery($query, $params);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function createLabelObserveChart(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"PersonDisp_id" => $data["PersonDisp_id"],
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"PersonLabel_id" => $data["PersonLabel_id"],
			"pmUser_id" => $data["pmUser_id"],
			"dateConsent" => $data["dateConsent"],
			"allowMailing" => $data["allowMailing"],
			"Person_Phone" => $data["Person_Phone"]
		];
		$params["MailingConsDT"] = (!empty($data["allowMailing"]) && $data["allowMailing"])?$data["dateConsent"]:null;

		$callObject->beginTransaction();
		
		$query = "
			select
				LabelObserveChart_id as \"LabelObserveChart_id\",
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
			from p_LabelObserveChart_ins(
				PersonDisp_id := :PersonDisp_id,
				MedStaffFact_id := :MedStaffFact_id,
				PersonLabel_id := :PersonLabel_id,
				LabelObserveChart_begDate := :dateConsent,
				LabelObserveChart_Phone := :Person_Phone,
				LabelObserveChart_consDT := :MailingConsDT,
				pmUser_id := :pmUser_id
			)
		";
		
		$saveResult = $callObject->getFirstRowFromQuery($query, $params);
		if (empty($saveResult['LabelObserveChart_id']) || !empty($saveResult['Error_Msg'])) {

			$callObject->rollbackTransaction();
			$err = "";

			if (!empty($result['Error_Msg'])) {
				$err = ": ".$result['Error_Msg'];
			}
			return array('Error_Msg' => 'Не удалось сохранить карту наблюдения'.$err);
		}

		$updateStatusResult = $callObject->updateLabelInviteStatus($params);
		if (!empty($updateStatusResult['Error_Msg'])) {
			$callObject->rollbackTransaction();
			return array('Error_Msg' => 'Не удалось сохранить карту наблюдения: Не удалось сбросить статусы приглашений по метке'.$err);
		}

		$params['LabelObserveChart_id'] = $saveResult['LabelObserveChart_id'];
		$saveRateResult = $callObject->addLabelObserveChartRate($params);
		if (!empty($saveRateResult['Error_Msg'])) {

			$callObject->rollbackTransaction();
			$err = "";

			if (!empty($result['Error_Msg'])) {
				$err = ": ".$result['Error_Msg'];
			}
			return array('Error_Msg' => 'Не удалось сохранить карту наблюдения'.$err);
		}

		$callObject->commitTransaction();
		return $saveResult;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $params
	 * @return bool
	 */
	public static function updateLabelInviteStatus(Polka_PersonDisp_model $callObject, $data) {
		//сброс статусов всех приглашений по метке пациента

		$result = $callObject->getFirstRowFromQuery("
			update LabelInvite
			set LabelInviteStatus_id = null
			where PersonLabel_id = :PersonLabel_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		", array(
			'PersonLabel_id' => $data['PersonLabel_id']
		));
		
		return $result;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $params
	 * @return array
	 */
	public static function addLabelObserveChartRate(Polka_PersonDisp_model $callObject, $data)
	{
		$labelRates = $callObject->queryResult("
			select
				LR.LabelRate_id as \"LabelRate_id\",
				LR.LabelRate_Min as \"LabelRate_Min\",
				LR.LabelRate_Max as \"LabelRate_Max\",
				rt.RateType_SysNick as \"RateType_SysNick\",
				rt.RateType_Name as \"RateType_Name\",
				rt.RateType_id as \"RateType_id\"
			from v_PersonLabel PL
			left join v_LabelRate LR on LR.Label_id = PL.Label_id
			left join v_RateType rt on rt.RateType_id = lr.RateType_id
			where PersonLabel_id = :PersonLabel_id
		", array(
			'PersonLabel_id' => $data['PersonLabel_id']
		));

		$labelRatesFiltered = array();

		// здесь придется только по одному брать первому попавшемуся,
		// чтобы избежать коллизий с позателями созданными из портала
		foreach ($labelRates as $lr) {
			if (!isset($labelRatesFiltered[$lr['RateType_id']])) {
				$labelRatesFiltered[$lr['RateType_id']] = $lr;
			} else {
				continue;
			}
		}

		$labelRates = array_values($labelRatesFiltered);

		$Person_id = $callObject->getFirstResultFromQuery("
			select Person_id
			from v_PersonDisp
			where PersonDisp_id = :PersonDisp_id
			limit 1
		", array('PersonDisp_id' => $data['PersonDisp_id']));

		if (empty($Person_id)) {
			return array('Error_Msg' => 'Не удалось определить пациента');
		}

		// проверяем возможно показатели уже связаны с пользователем
		$personRates = $callObject->getPersonLabelObserveChartRates(array(
			'Person_id' => $Person_id
		));
		
		// если показатель уже связан, то мы его не добавляем
		if (!empty($labelRates) && !empty($personRates)) {
			foreach ($labelRates as $key => $lrate) {
				foreach ($personRates as $pkey => $prate) {
					if ($lrate['RateType_SysNick'] === $prate['RateType_SysNick']) {
						unset($labelRates[$key]);
						unset($personRates[$pkey]);
						break;
					}
				}
			}
		}
		
		// создаем только те показатели которые остались в $labelRates
		$resp = array();
		if (!empty($labelRates)) {
			foreach($labelRates as $rate) {
				
				$params = array(
					'LabelRate_id' => $rate['LabelRate_id'],
					'RateType_id' => $rate['RateType_id'],
					'LabelRate_Min' => $rate['LabelRate_Min'],
					'LabelRate_Max' => $rate['LabelRate_Max'],
					'pmUser_id' => $data['pmUser_id'],
					'Person_id' => $Person_id
				);

				$result = $callObject->getFirstRowFromQuery("
					select
						LabelObserveChartRate_id as \"LabelObserveChartRate_id\",
        				Error_Code as \"Error_Code\",
        				Error_Message as \"Error_Msg\"
					from p_LabelObserveChartRate_ins(
						LabelRate_id := :LabelRate_id,
						RateType_id := :RateType_id,
						Person_id := :Person_id,
						LabelObserveChartRate_Min := :LabelRate_Min,
						LabelObserveChartRate_Max := :LabelRate_Max,
						LabelObserveChartRate_IsShowEMK := 1,
						LabelObserveChartRate_IsShowValue := 2,
						LabelObserveChartSource_id := 1,
						pmUser_id := :pmUser_id
					)
				", $params);
				
				if (empty($result['LabelObserveChartRate_id']) || !empty($result['Error_Msg'])) {
					$err = "";
					if (!empty($result['Error_Msg'])) {
						$err = ": ".$result['Error_Msg'];
					}
					return array('Error_Msg' => 'Не удалось сохранить целевой показатель'.$err);
					break;
				}

				$resp['LabelObserveChartRate_id'][] = $result['LabelObserveChartRate_id'];
			}
		}
		
		return $resp;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function ChangeLabelInviteStatus(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"LabelInvite_id" => $data["LabelInvite_id"],
			"LabelInviteStatus_id" => $data["LabelInviteStatus_id"],
			"RefuseCause" => $data["RefuseCause"],
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$sql = "
			update LabelInvite 
			set LabelInviteStatus_id = :LabelInviteStatus_id,
			    LabelInvite_RefuseCause = :RefuseCause
			where LabelInvite_id = :LabelInvite_id
		";
		$callObject->db->query($sql, $params);
		$sql = "
			select
				LabelInviteHistory_id as \"LabelInviteHistory_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LabelInviteHistory_ins(
				LabelInvite_id := :LabelInvite_id,
				LabelInviteStatus_id := :LabelInviteStatus_id,
				LabelInviteHistory_setDT := dbo.tzgetdate(),
				MedStaffFact_id := :MedStaffFact_id,
				pmUser_id := :pmUser_id
			)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function createPersonLabel(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
        		PersonLabel_id as \"PersonLabel_id\",
        		Error_Code as \"Error_Code\",
        		Error_Message as \"Error_Msg\"
        	from p_PersonLabel_ins(
        		Label_id := :Label_id,
				Person_id := :Person_id,
				PersonLabel_setDate := :setDate,
				Diag_id := null,
				pmUser_id := :pmUser_id
        	)
		";
		$sqlParams = [
			"Label_id" => $data["Label_id"],
			"Person_id" => $data["Person_id"],
			"Diag_id" => $data["Diag_id"],
			"setDate" => $data["dateConsent"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);

		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return $result[0];
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function checkPersonLabelObserveChartRates($callObject, $data) {
		$result = $callObject->queryResult("
			select
				locr.LabelObserveChartRate_id
			from v_LabelObserveChartRate locr
			left join v_ratetype rt  on rt.RateType_id = locr.RateType_id
			where (1=1)
				and locr.Person_id = :Person_id
			limit 1
		", array('Person_id' => $data['Person_id']));

		return $result;
	}
}