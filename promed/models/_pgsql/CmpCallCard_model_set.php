<?php

class CmpCallCard_model_set
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setAnotherPersonForCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			throw new Exception("Невозможно сохранить. Карта вызова редактируется другим пользователем");
		}
		$query = "
			update CmpCallCard
			set
				Person_id = :Person_id,
				pmUser_updID = :pmUser_id,
				CmpCallCard_updDT = getdate(),
				CmpCallCard_IsInReg = 1
			where CmpCallCard_id = :CmpCallCard_id;
			select '' as Error_Code, '' as Error_Msg
		";
		$queryParams = [
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"Person_id" => $data["Person_id"],
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
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function setCmpCloseCardTimetable(CmpCallCard_model $callObject, $data)
	{
		if (!(isset($data["CmpCallCard_id"]) && isset($data["TimetableStac_id"]) && ($data["CmpCallCard_id"] > 0) && ($data["TimetableStac_id"] > 0))) {
			return false;
		}
		if (!isset($data["CmpCloseCardTimetable_id"])) {
			$data["CmpCloseCardTimetable_id"] = null;
		}
		$query = "
			select
			    cmpclosecardtimetable_id as \"CmpCloseCardTimetable_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpclosecardtimetable_ins(
			    cmpclosecardtimetable_id := :CmpCloseCardTimetable_id,
			    cmpcallcard_id := :CmpCallCard_id,
			    timetablestac_id := :TimetableStac_id,
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
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool|mixed
	 */
	public static function setCmpCallCardEvent(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"])) {
			return false;
		}
		//необходимая информация о вторичной карте
		$cardInfo = $callObject->getCardParamsForEvent($data);
		if ($cardInfo === false) {
			return false;
		}
		$CmpCallCardEventType_id = !empty($data["CmpCallCardEventType_id"]) ? $data["CmpCallCardEventType_id"] : null;
		//если приходит код события, записываем по коду события
		//тк бывают случаи, что событие должно сохраняться без смены статуса
		if (!empty($data["CmpCallCardEventType_Code"])) {
			//возможность проставлять событие по коду
			$evtTypeCardQuery = "select * from v_CmpCallCardEventType where CmpCallCardEventType_Code = :CmpCallCardEventType_Code limit 1";
			$evtTypeCard = $callObject->db->query($evtTypeCardQuery, $data)->row_array();
			if (!empty($evtTypeCard["CmpCallCardEventType_id"])) {
				$data["CmpCallCardEventType_id"] = $evtTypeCard["CmpCallCardEventType_id"];
				$CmpCallCardEventType_id = $evtTypeCard["CmpCallCardEventType_id"];
			}
		}
		$comment = "";
		if (!empty($data["CmpCallCardEvent_Comment"])) {
			$comment = $data["CmpCallCardEvent_Comment"];
		}
		if (empty($CmpCallCardEventType_id)) {
			//определяем CmpCallCardEventType_id
			switch ($cardInfo["CmpCallCardStatusType_id"]) {
				case null:
				case 1:
				{
					//Передан на подстанцию
					if (isset($cardInfo["Lpu_ppdid"])) {
						//НМП
						$CmpCallCardEventType_id = 2;
					} else {
						//СМП
						$CmpCallCardEventType_id = 1;
					}
					break;
				}
				case 2:
				{
					if (isset($cardInfo["Lpu_ppdid"])) {
						//Принято НМП
						$CmpCallCardEventType_id = 6;
					} else {
						//Назначена бригада
						$CmpCallCardEventType_id = 4;

						if (isset($cardInfo["EmergencyTeamStatus_Code"])) {
							if ($cardInfo["EmergencyTeamStatus_Code"] == 48) {
								//Статус бригады, назначенной на вызов, изменился на «Принял вызов»
								$CmpCallCardEventType_id = 5;
							};
							if ($cardInfo["EmergencyTeamStatus_Code"] == 14 || $cardInfo["EmergencyTeamStatus_Code"] == 1) {
								//Статус бригады, назначенной на вызов, изменился на «Выехал на вызов»
								$CmpCallCardEventType_id = 7;
							};
							if ($cardInfo["EmergencyTeamStatus_Code"] == 15 || $cardInfo["EmergencyTeamStatus_Code"] == 2) {
								//Статус бригады, назначенной на вызов, изменился на «Прибыл на место вызова»
								$CmpCallCardEventType_id = 8;
							};
							if ($cardInfo["EmergencyTeamStatus_Code"] == 3) {
								//Статус бригады, назначенной на вызов, изменился на «Госпитализация/Перевозка»
								$CmpCallCardEventType_id = 9;
							};
							if ($cardInfo["EmergencyTeamStatus_Code"] == 4) {
								//Статус бригады, назначенной на вызов, изменился на «Конец обслуживания»
								$CmpCallCardEventType_id = 10;
							};
							if ($cardInfo["EmergencyTeamStatus_Code"] == 17) {
								//Статус бригады, назначенной на вызов, изменился на «Прибытие в МО»
								$CmpCallCardEventType_id = 11;
							};
						}
					}
					break;
				}
				case 4:
				{
					//Вызов принял статус «4. Обслужено»
					$CmpCallCardEventType_id = 13;
					break;
				}
				case 5:
				{
					//Вызов принял статус «5. Отказ»
					$CmpCallCardEventType_id = 15;
					break;
				}
				case 6:
				{
					//Закрытие карты
					//Возвращение бригады
					//При сохранении даты и времени в поле «Возвращения на станцию» формы «Информация о вызове»
					$dparams = [
						"CmpCallCardEventType_id" => 12,
						"CmpCallCardStatus_id" => (!empty($cardInfo["CmpCallCardStatus_id"]) && $cardInfo["CmpCallCardStatus_id"] != "") ? $cardInfo["CmpCallCardStatus_id"] : null,
						"EmergencyTeamStatusHistory_id" => (!empty($cardInfo["EmergencyTeamStatusHistory_id"]) && $cardInfo["EmergencyTeamStatusHistory_id"] != "") ? $cardInfo["EmergencyTeamStatusHistory_id"] : null,
						"LpuBuilding_id" => (!empty($cardInfo["LpuBuilding_id"]) && $cardInfo["LpuBuilding_id"] != "") ? $cardInfo["LpuBuilding_id"] : null,
						"LpuSection_id" => (!empty($cardInfo["LpuSection_id"]) && $cardInfo["LpuSection_id"] != "") ? $cardInfo["LpuSection_id"] : null,
						"EmergencyTeam_id" => (!empty($cardInfo["EmergencyTeam_id"]) && $cardInfo["EmergencyTeam_id"] != "") ? $cardInfo["EmergencyTeam_id"] : null,
						"CmpCallCardEvent_Comment" => $comment,
						"pmUser_id" => $data["pmUser_id"],
						"CmpCallCardEvent_setDT" => (!empty($data["BackTime"]) && $data["BackTime"] != "") ? $data["BackTime"] : null,
						"CmpCallCard_id" => (!empty($cardInfo["CmpCallCard_id"]) && $cardInfo["CmpCallCard_id"] != "") ? $cardInfo["CmpCallCard_id"] : null
					];
					$callObject->saveCmpCallCardEvent($dparams);
					//Вызов принял статус «6. Закрыто»
					$CmpCallCardEventType_id = 14;
					break;
				}
				case 16:
				{
					//Дублирующее обращение, регистрация
					//Здесь регистрируем события, произошедшие с повторным вызовом, на первичный вызов
					//Чтобы потом не запутаться - событие произошло с дублирующим а мы регистрируем событие для первичного
					$CmpCallCardEventType_id = 20;
					break;
				}
				case 18:
				{
					//Дублирующий вызов - Передан для решения старшего врача
					$CmpCallCardEventType_id = 3;
					//Первичный - Дублирующее обращение, регистрация
					//Здесь регистрируем события, произошедшие с повторным вызовом, на первичный вызов
					//Чтобы потом не запутаться - событие произошло с дублирующим а мы регистрируем событие для первичного
					if ($cardInfo["CmpCallCard_rid"]) {
						//по умолчанию
						$ParentCmpCallCardEventType_id = 16;
						//а тут идет ранжирование статусов по типу обращения
						if ($cardInfo["CmpCallType_Code"] == 14) {
							//Дублирующее обращение, регистрация
							$ParentCmpCallCardEventType_id = 16;
						};
						if ($cardInfo["CmpCallType_Code"] == 17) {
							//Отмена вызова, регистрация
							$ParentCmpCallCardEventType_id = 17;
						};
						if ($cardInfo["CmpCallType_Code"] == 9) {
							//Создание вызова спец. бригады, регистрация
							$ParentCmpCallCardEventType_id = 18;
						};
						if ($cardInfo["CmpCallType_Code"] == 4) {
							//Создание попутного вызова
							$ParentCmpCallCardEventType_id = 19;
						};
						//сохранение события для первичного вызова
						$parentCardInfo = $callObject->getCardParamsForEvent(array("CmpCallCard_id" => $data["CmpCallCard_rid"]));
						$parentParams = [
							"CmpCallCardEventType_id" => $ParentCmpCallCardEventType_id,
							"CmpCallCardStatus_id" => (!empty($parentCardInfo["CmpCallCardStatus_id"]) && $parentCardInfo["CmpCallCardStatus_id"] != "") ? $parentCardInfo["CmpCallCardStatus_id"] : null,
							"EmergencyTeamStatusHistory_id" => (!empty($parentCardInfo["EmergencyTeamStatusHistory_id"]) && $parentCardInfo["EmergencyTeamStatusHistory_id"] != "") ? $parentCardInfo["EmergencyTeamStatusHistory_id"] : null,
							"LpuBuilding_id" => (!empty($parentCardInfo["LpuBuilding_id"]) && $parentCardInfo["LpuBuilding_id"] != "") ? $parentCardInfo["LpuBuilding_id"] : null,
							"LpuSection_id" => (!empty($parentCardInfo["LpuSection_id"]) && $parentCardInfo["LpuSection_id"] != "") ? $parentCardInfo["LpuSection_id"] : null,
							"EmergencyTeam_id" => (!empty($parentCardInfo["EmergencyTeam_id"]) && $parentCardInfo["EmergencyTeam_id"] != "") ? $parentCardInfo["EmergencyTeam_id"] : null,
							"pmUser_id" => $data["pmUser_id"],
							"CmpCallCard_id" => $data["CmpCallCard_rid"],
							"CmpCallCard_cid" => $data["CmpCallCard_id"],
							"CmpCallCardEvent_Comment" => $comment
						];
						$callObject->saveCmpCallCardEvent($parentParams);
					};
					break;
				}
			}
		}
		$params = [
			"CmpCallCardEventType_id" => $CmpCallCardEventType_id,
			"CmpCallCardStatus_id" => (!empty($cardInfo["CmpCallCardStatus_id"]) && $cardInfo["CmpCallCardStatus_id"] != "") ? $cardInfo["CmpCallCardStatus_id"] : null,
			"EmergencyTeamStatusHistory_id" => (!empty($cardInfo["EmergencyTeamStatusHistory_id"]) && $cardInfo["EmergencyTeamStatusHistory_id"] != "") ? $cardInfo["EmergencyTeamStatusHistory_id"] : null,
			"LpuBuilding_id" => (!empty($cardInfo["LpuBuilding_id"]) && $cardInfo["LpuBuilding_id"] != "") ? $cardInfo["LpuBuilding_id"] : null,
			"LpuSection_id" => (!empty($cardInfo["LpuSection_id"]) && $cardInfo["LpuSection_id"] != "") ? $cardInfo["LpuSection_id"] : null,
			"EmergencyTeam_id" => (!empty($cardInfo["EmergencyTeam_id"]) && $cardInfo["EmergencyTeam_id"] != "") ? $cardInfo["EmergencyTeam_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
			"CmpCallCardEvent_Comment" => $comment,
			"CmpCallCard_id" => (!empty($cardInfo["CmpCallCard_id"]) && $cardInfo["CmpCallCard_id"] != "") ? $cardInfo["CmpCallCard_id"] : null,
		];
		return $callObject->saveCmpCallCardEvent($params);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setEmergencyTeam(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]["CmpCallCard_id"])) {
			throw new Exception("Карта вызова редактируется другим пользователем");
		}
		if ((int)$data["EmergencyTeam_id"] == 0) {
			$data["EmergencyTeam_id"] = null;
		}
		$query = "
			select
			    :CmpCallCard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_setemergencyteam(
			    cmpcallcard_id := :CmpCallCard_id,
			    emergencyteam_id := :EmergencyTeam_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $callObject
		 */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$setCmpCallCardTper = "
			update CmpCallCard
			set CmpCallCard_Tper = tzgetdate()
			where CmpCallCard_id = :CmpCallCard_id
		";
		$callObject->db->query($setCmpCallCardTper, $data);
		$resp = $result->result("array");
		if (!empty($resp[0]["Error_Msg"])) {
			return $resp;
		}
		// отправляем PUSH
		$callObject->sendPushOnSetMergencyTeam([
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"pmUser_id" => $data["pmUser_id"]
		]);
		return $resp;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setLpuTransmit(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			throw new Exception("Карта вызова редактируется другим пользователем");
		}
		if ((int)$data['Lpu_ppdid'] == 0) {
			$data['Lpu_ppdid'] = null;
		}
		$query = "
			select
				CmpCallCardStatusType_id as \"Status\",
			    coalesce(Person_id, 0) as \"PersonIdentifyed\",
			    coalesce(Person_Age, 1) as \"PersonAge\"
			from v_CmpCallCard
			where CmpCallCard_id = :CmpCallCard_id;
		";
		$response = $callObject->queryResult($query, $data);
		if (!is_array($response)) {
			return false;
		}
		$responseRow = $response[0];
		if ($responseRow["PersonIdentifyed"] == 0) {
			throw new Exception("Вызов не может быть принят в НМП. Пациент не идентифицирован", 0);
		}
		if ($responseRow["PersonAge"] == 0) {
			throw new Exception("Вызов не может быть передан в НМП. Пациенты до года обслуживаются в СМП", 0);
		}
		if (in_array($responseRow["Status"], [2, 4])) {
			throw new Exception("Вызов принят или обслужен", 0);
		}
		$query = "
			select
			    :CmpCallCard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_setlpuppd(
			    cmpcallcard_id := :CmpCallCard_id,
			    lpu_ppdid := :Lpu_ppdid,
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
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setLpuId(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			throw new Exception("Карта вызова редактируется другим пользователем");
		}
		$query = "
			select
				:CmpCallCard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_setlpu(
			    cmpcallcard_id := :CmpCallCard_id,
			    lpu_id := :Lpu_id,
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
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setPerson(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			throw new Exception("Карта вызова редактируется другим пользователем");
		}
		$query = "
			select
			    :CmpCallCard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_setperson(
			    cmpcallcard_id := :CmpCallCard_id,
			    person_id := :Person_id,
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
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function setPPDWaitingTime(CmpCallCard_model $callObject, $data)
	{
		if (!$data["PPD_WaitingTime"]) {
			throw new Exception("Не введено время ожидания");
		} elseif (!$data["Password"]) {
			throw new Exception("Не введен пароль от учётной записи");
		} elseif (!$data["session"]["login"]) {
			throw new Exception("Пользователь не идентифицирован");
		}
		$user = pmAuthUser::find($data["session"]["login"]);
		if (substr($data["Password"], 0, 5) <> "{MD5}")
			$data["Password"] = "{MD5}" . base64_encode(md5($data["Password"], TRUE));
		if ($user->pass !== $data["Password"]) {
			throw new Exception("Пароль от учётной записи введён неверно");
		}
		$SetValueQuery = "
			select
			    datastorage_id as \"DataStorage_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_datastorage_set(
			    datastorage_id := null,
			    lpu_id := 0,
			    datastorage_name := 'cmp_waiting_ppd_time',
			    datastorage_value := :PPD_WaitingTime,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($SetValueQuery, $data);
		$result_arr = $result->result("array");
		return $result_arr;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @param string $status
	 * @return bool
	 */
	public static function setEmergencyTeamStatus(CmpCallCard_model $callObject, $data, $status = "Свободна")
	{
		$TeamStatusID = "";
		if (empty($data["pmUser_id"])) $data["pmUser_id"] = $_SESSION["pmuser_id"];
		if (empty($data["ARMType_id"])) {
			$callObject->load->database();
			$callObject->load->model("User_model", "User_model");
			$m = $callObject->User_model->getARMList();
			$result = $callObject->User_model->getARMinDB(["ARMType_Code" => $m[$data["ARMType"]]["Arm_id"]]);
			$data["ARMType_id"] = $result[0]["ARMType_id"];
		}
		$callObject->load->model("EmergencyTeam_model4E", "EmergencyTeam_model4E");
		// получим список возможных статусов
		$statuses = $callObject->ETModel->loadEmergencyTeamStatuses($data);
		foreach ($statuses as $n) {
			if (mb_strtolower($n["EmergencyTeamStatus_Name"]) == mb_strtolower($status)) {
				$TeamStatusID = (int)$n["EmergencyTeamStatus_id"];
				break;
			}
		}
		if (!$TeamStatusID) {
			return false;
		}
		$data["EmergencyTeamStatus_id"] = $TeamStatusID;
		return $callObject->ETModel->setEmergencyTeamStatus($data);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setIsOpenCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]["CmpCallCard_id"])) {
			throw new Exception("Карта вызова редактируется другим пользователем");
		}
		$query = "
			select
				:CmpCallCard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_setisopen(
			    cmpcallcard_id := :CmpCallCard_id,
			    cmpcallcard_isopen := :CmpCallCard_IsOpen,
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
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setResult(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]["CmpCallCard_id"])) {
			throw new Exception("Карта вызова редактируется другим пользователем");
		}
		if ((int)$data["CmpPPDResult_id"] == 0) {
			$data["CmpPPDResult_id"] = null;
		}
		//возможность проставлять статус по коду
		if (empty($data["CmpPPDResult_id"]) && !empty($data["CmpPPDResult_Code"])) {
			$statusQuery = "select * from v_CmpPPDResult where CmpPPDResult_Code = :CmpPPDResult_Code limit 1";
			$status = $callObject->db->query($statusQuery, $data)->row_array();
			if (empty($status["CmpPPDResult_id"])) {
				throw new Exception("Не код или id статуса карты");
			}
			$data["CmpPPDResult_id"] = $status["CmpPPDResult_id"];
		}
		$query = "
			select
				:CmpCallCard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_setppdresult(
			    cmpcallcard_id := :CmpCallCard_id,
			    cmpppdresult_id := :CmpPPDResult_id,
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
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function setSmoQueryCallCards(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from r2.p_cmpsmoquerycardnumbers_ins(
			    cmpsmoquerycardnumbers_cardnumber := :CardNumber,
			    cmpsmoquerycardnumbers_smoid := :OrgSmo_id,
			    cmpsmoquerycardnumbers_inslpuid := :Lpu_id,
			    pmuser_insid := :pmUser_id,
			    cmpsmoquerycardnumbers_insdt := :insDT
			);
		";
		$response = $callObject->queryResult($query, $data);
		return $response;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function setStatusCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]["CmpCallCard_id"])) {
			throw new Exception("Карта вызова редактируется другим пользователем");
		}
		if (!isset($data["CmpCallCardStatusType_id"]) || $data["CmpCallCardStatusType_id"] == 0) {
			$data["CmpCallCardStatusType_id"] = null;
		}
		if (!isset($data["CmpCallCard_IsReceivedInPPD"])) {
			$data["CmpCallCard_IsReceivedInPPD"] = null;
		}
		if (isset($data["CmpCallCardStatusType_id"]) && $data["CmpCallCardStatusType_id"] == 3) {
			$data["CmpCallCard_IsReceivedInPPD"] = 1;
		}
		if (!isset($data["CmpCallCard_isNMP"])) {
			$prequery = "select CmpCallCard_isNMP from v_CmpCallCard where CmpCallCard_id = :CmpCallCard_id";
			$preres = $callObject->db->query($prequery, $data);
			$preres = $preres->row_array();
			$data["CmpCallCard_isNMP"] = (!empty($preres["CmpCallCard_isNMP"])) ? $preres["CmpCallCard_isNMP"] : 1;
		}
		if (isset($data["CmpReason_id"])) {
			if ($data["CmpReason_id"] == 0) {
				$data["CmpReason_id"] = null;
			}
		} else {
			$data["CmpReason_id"] = null;
		}
		if (!isset($data["CmpCallCardStatus_Comment"])) {
			$data["CmpCallCardStatus_Comment"] = null;
		}
		if (!isset($data["CmpMoveFromNmpReason_id"])) {
			$data["CmpMoveFromNmpReason_id"] = null;
		}
		if (!isset($data["CmpReturnToSmpReason_id"])) {
			$data["CmpReturnToSmpReason_id"] = null;
		}
		$query = "
			select
				:CmpCallCard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_setstatus(
			    cmpcallcard_id := :CmpCallCard_id,
			    cmpcallcardstatustype_id := :CmpCallCardStatusType_id,
			    cmpcallcardstatus_comment := :CmpCallCardStatus_Comment,
			    cmpreason_id := :CmpReason_id,
			    cmpcallcard_isreceivedinppd := :CmpCallCard_IsReceivedInPPD,
			    cmpmovefromnmpreason_id := :CmpMoveFromNmpReason_id,
			    cmpreturntosmpreason_id := :CmpReturnToSmpReason_id,
			    pmuser_id := :pmUser_id,
			    cmpcallcard_isnmp := :CmpCallCard_isNMP
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		//установка службы нмп
		if (!empty($data["MedService_id"])) {
			$postSql = "
				update CmpCallCard
				set
					MedService_id = :MedService_id,
					pmUser_updID = :pmUser_id,
					CmpCallCard_updDT = getdate()
				where CmpCallCard_id  = :CmpCallCard_id
			";
			$callObject->db->query($postSql, $data);
		}
		if (!is_object($result)) {
			return false;
		}
		if (defined("STOMPMQ_MESSAGE_ENABLE") && STOMPMQ_MESSAGE_ENABLE === TRUE) {
			$callObject->checkSendReactionToActiveMQ(array("CmpCallCard_id" => $data["CmpCallCard_id"]));
		}
		return $result->result("array");
	}
}