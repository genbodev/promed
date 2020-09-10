<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ElectronicTalon
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 *
 * @property CI_DB_driver $db
 * @property Timetable_model $tt_model
 * @property TimetableGraf_model $ttg_model
 * @property ElectronicQueue_model $ElectronicQueue_model
 * @property TimetableGraf_model $TimetableGraf_model
 * @property EvnDirection_model $edmodel
 * @property TimetableMedService_model $TimetableMedService_model
 */

class ElectronicTalon_model extends swPgModel
{
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получить статус эл. талона по айди статуса
	 * @param $data
	 * @return array|false
	 */
	function getElectronicTalonStatusById($data)
	{
		$params["ElectronicTalonStatus_id"] = $data["ElectronicTalonStatus_id"];
		$query = "
			select
				ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				ElectronicTalonStatus_Code as \"ElectronicTalonStatus_Code\",
				ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\"
			from v_ElectronicTalonStatus
			where ElectronicTalonStatus_id = :ElectronicTalonStatus_id
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		return $resp;
	}

	/**
	 * Получить код ПО (как бы альтернатива КАБИНЕТА)
	 * @param $data
	 * @return mixed|null
	 */
	function getElectronicServiceCodeById($data)
	{
		$params["ElectronicService_id"] = $data["ElectronicService_id"];
		$query = "
			select
				ElectronicService_id as \"ElectronicService_id\",
				ElectronicService_Code as \"ElectronicService_Code\"
			from v_ElectronicService
			where ElectronicService_id = :ElectronicService_id
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]) && !empty($resp[0]["ElectronicService_Code"]) ? $resp[0]["ElectronicService_Code"] : null);
	}

	/**
	 * Получение инф. по талону
	 * @param $data
	 * @return array|false
	 */
	function getElectronicTalonById($data)
	{
		$params["ElectronicTalon_id"] = $data["ElectronicTalon_id"];
		$query = "
			select
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
			  	et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				et.ElectronicTalon_OrderNum as \"ElectronicTalon_OrderNum\",
				et.ElectronicService_id as \"ElectronicService_id\",
				es.ElectronicService_Num as \"ElectronicService_Num\",
				es.ElectronicService_Name as \"ElectronicService_Name\",
				es.ElectronicService_Code as \"ElectronicService_Code\",
				et.EvnDirection_id as \"EvnDirection_id\",
				et.EvnDirection_uid as \"EvnDirection_uid\",
				et.ElectronicTreatment_id as \"ElectronicTreatment_id\",
				et.Person_id as \"Person_id\",
				et.pmUser_insID as \"pmUser_insID\",
				et.pmUser_updID as \"pmUser_updID\",
				ets.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				ets.ElectronicTalonStatus_Code as \"ElectronicTalonStatus_Code\",
				mseq.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_ElectronicTalon et
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicService es on es.ElectronicService_id = et.ElectronicService_id
				left join v_MedServiceElectronicQueue mseq on mseq.ElectronicService_id = et.ElectronicService_id
			where ElectronicTalon_id = :ElectronicTalon_id
		";
		$resp = $this->queryResult($query, $params);
		return $resp;
	}

	/**
	 * Установка статуса электронного талона при неявке пациента (в авто режиме)
	 * (усовершенствованный вариант)
	 * @param $data
	 * @return bool
	 */
	function doCancelTalonCheck($data)
	{
		$params = ["ElectronicTalon_id" => $data["ElectronicTalon_id"]];
		if (empty($data["cancelCallCount"])) {
			return false;
		}
		$rowCount = $data["cancelCallCount"] * 2;
		$query = "
			select sum(ElectronicTalonStatus_id) as \"statesSum\"
			from (
				select ElectronicTalonStatus_id
				from v_ElectronicTalonHist
				where ElectronicTalon_id = :ElectronicTalon_id
				order by ElectronicTalonHist_id desc
				limit {$rowCount}
			) as s
		";
		$response = $this->getFirstRowFromQuery($query, $params);
		if (empty($response)) {
			return false;
		}
		$equalSum = 3 * (int)$data["cancelCallCount"];
		$querySum = (int)$response["statesSum"];
		return ($equalSum == $querySum) ? true : false;
	}

	/**
	 * отправка пуш уведомления
	 * @param $data
	 * @param $changedData
	 */
	function sendCallPushNotification($data, $changedData)
	{
		$pushNotificationEnabled = false; // пока заготовка для настройки
		if (!empty($data["disablePush"])) {
			$pushNotificationEnabled = false;
		}
		// отправляем пуш, только если нажато "Вызвать" в АРМ врача
		if ($pushNotificationEnabled && $data["ElectronicTalonStatus_id"] == 2) {
			$query = "
				select
					Person_id as \"Person_id\",
					rtrim(p.Person_Surname) as \"Person_Surname\",
					left(p.Person_Firname, 1) as \"Person_FirnameLetter\",
					left(p.Person_Secname, 1) as \"Person_SecnameLetter\",
					dbo.Age2(p.Person_BirthDay, dbo.tzGetdate()) as \"Person_Age\"
				from v_PersonState p
				where p.Person_id = :Person_id
				limit 1
			";
			$queryParams = ["Person_id" => $data["Person_id"]];
			$personData = $this->queryResult($query, $queryParams);
			// по возможности определяем пользователя портала
			$query = "
				select ttg.pmUser_updID as \"pmUser_did\"
				from
					v_TimetableGraf_lite ttg
					inner join v_ElectronicTalon et on et.EvnDirection_id = ttg.EvnDirection_id
				where ttg.Person_id = :Person_id
				  and et.ElectronicTalon_id = :ElectronicTalon_id
				order by ttg.TimetableGraf_id desc
				limit 1
			";
			$queryParams = [
				"ElectronicTalon_id" => $data["ElectronicTalon_id"],
				"Person_id" => $data["Person_id"]
			];
			$pmUser_did = $this->getFirstResultFromQuery($query, $queryParams);
			if (!empty($personData[0]["Person_id"])) {
				$Person_FullNameDots =
					$personData[0]["Person_Surname"] .
					(!empty($personData[0]["Person_FirnameLetter"]) ? " " . $personData[0]["Person_FirnameLetter"] . "." : "") .
					(!empty($personData[0]["Person_SecnameLetter"]) ? $personData[0]["Person_SecnameLetter"] . "." : "");
			}
			// если место работы указано, вычисляем кабинет
			if (!empty($data["MedStaffFact_id"])) {
				$this->load->model("TimetableGraf_model", "ttg_model");
				$funcParams = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
				$room = $this->ttg_model->getDoctorRoom($funcParams);
			}
			// если кабинет вычислить по врачу не удалось, записываем в кабинет код ПО
			if (empty($room)) {
				$funcParams = ["ElectronicService_id" => $changedData["ElectronicService_id"]];
				$room = $this->getElectronicServiceCodeById($funcParams);
			}
			$this->load->helper("Notify");
			$funcParams = [
				"Person_id" => $data["Person_id"], // персона которая заходит
				"Person_Age" => (!empty($personData[0]) && !empty($personData[0]["Person_Age"]) ? $personData[0]["Person_Age"] : null),
				"pmUser_did" => (!empty($pmUser_did) ? $pmUser_did : null), // тот кто записал на бирку
				"message" =>
					"Пациент " .
					(!empty($Person_FullNameDots) ? $Person_FullNameDots : "") .
					" приглашается в кабинет" .
					(!empty($room) ? " №" . $room : ""),
				"PushNoticeType_id" => 4,
				"action" => "call"
			];
			sendPushNotification($funcParams);
		}
	}

	/**
	 * Установка статуса электронного талона
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function setElectronicTalonStatus($data)
	{
		// если указан параметр "Число отмены вызовов(вызов-ожидание) для отмены талона"
		if (!empty($data["cancelCallCount"])) {
			//проверяем можно ли отменить талон
			$canCancel = $this->doCancelTalonCheck($data);
			// выставляем статус отмены
			if ($canCancel) $data["ElectronicTalonStatus_id"] = 5;
		}
		$electronicTalonStatus = $this->getElectronicTalonStatusById($data);
		if (empty($electronicTalonStatus[0]["ElectronicTalonStatus_Code"])) {
			throw new Exception("Ошибка получения кода статуса талона");
		}
		$electronicTalonStatus = $electronicTalonStatus[0];
		// начитываем всё из ElectronicTalon
		$oldData = $this->getElectronicTalonById($data);
		if (empty($oldData[0]["ElectronicTalon_id"])) {
			throw new Exception("Указанный идентификатор талона не существует");
		}
		$oldData = $oldData[0];
		$changedData = $oldData;
		$this->load->model("ElectronicQueue_model");
		if (!empty($data["ElectronicQueueInfo_id"])) {
			$changedData["ElectronicQueueInfo_id"] = $data["ElectronicQueueInfo_id"];
		}
		$funcParams = ["ElectronicQueueInfo_id" => $changedData["ElectronicQueueInfo_id"]];
		$is_linear_eq = $this->ElectronicQueue_model->getElectronicQueueType($funcParams);
		//если очредь нелинейная
		if (!$is_linear_eq) {
			// определим мультисервисность если не установлено
			$data["isMultiserviceElectronicQueue"] = true;
			// если статус установлен в "ожидает", нужно так же сбросить ПО
			if ($data["ElectronicTalonStatus_id"] == 1 && array_key_exists("ElectronicService_id", $data) && (!empty($oldData["ElectronicService_Num"]) && $oldData["ElectronicService_Num"] != 1)) {
				// для ПО чей порядковый номер 1, не сбрасываем ПО
				unset($data["ElectronicService_id"]);
			}
			// если статус изменен и ПО уже занят
			// то нужно отказать в этом действии для нового ПО
			if (!empty($oldData["ElectronicService_id"]) && !empty($data["ElectronicService_id"]) && $data["ElectronicService_id"] != $oldData["ElectronicService_id"]) {
				throw new Exception("Данный талон уже обслуживается другим специалистом");
			}
		}
		if ($oldData["ElectronicTalonStatus_id"] != $data["ElectronicTalonStatus_id"] || (array_key_exists("ElectronicService_id", $data) && $oldData["ElectronicService_id"] != $data["ElectronicService_id"])) {
			// меняем статус
			$changedData["ElectronicTalonStatus_id"] = $data["ElectronicTalonStatus_id"];
			if (array_key_exists("ElectronicService_id", $data) && !empty($data["ElectronicService_id"])) {
				$changedData["ElectronicService_id"] = $data["ElectronicService_id"];
			} else {
				if (!$is_linear_eq) {
					$changedData["ElectronicService_id"] = null; // сбрасываем ПО для нелинейной работы ЭО
				}
			}
			// т.к. метод без авторизации может работать
			$changedData["pmUser_id"] = (!empty($data["pmUser_id"]))?$data["pmUser_id"]:1;

            if (!empty($data['clearRedirectLink'])) {
                $changedData['EvnDirection_uid'] = null;
            }

			$this->beginTransaction();
			// обновляем талон ЭО
			$updateTalon = $this->updateElectronicTalon($changedData);
			if (!empty($updateTalon[0]["Error_Msg"])) {
				$this->rollbackTransaction();
				return $updateTalon[0];
			}
			// сохраняем изменения в историю ElectronicTalonHist
			if (empty($data["alreadyInTalonHistory"])) {
				$updateTalonHistory = $this->updateElectronicTalonHistory($changedData);
			}
			if (!empty($updateTalonHistory[0]["Error_Msg"])) {
				$this->rollbackTransaction();
				return $updateTalonHistory[0];
			}
			$this->commitTransaction();
			$this->sendCallPushNotification($data, $changedData);
			// шлем изменения по талону в текущий пункт и следующий пункт если есть
			if (!empty($changedData["ElectronicService_id"])) {
				// нагребаем параметры для НОДА
                if (!empty($changedData['ElectronicQueueInfo_id'])) {
                    $electronictreatment_id = $this->getFirstResultFromQuery("
						select ElectronicTreatment_id
						from v_ElectronicTreatmentLink etr
						where ElectronicQueueInfo_id = :ElectronicQueueInfo_id
						order by ElectronicTreatment_id desc
						limit 1
					", array('ElectronicQueueInfo_id' => $changedData['ElectronicQueueInfo_id']));

                    $medservice_id = $this->getFirstResultFromQuery("
						select MedService_id
						from v_ElectronicQueueInfo eqi
						where ElectronicQueueInfo_id = :ElectronicQueueInfo_id
						limit 1
					", array('ElectronicQueueInfo_id' => $changedData['ElectronicQueueInfo_id']));
                }

                if (!empty($changedData['ElectronicService_id'])) {
                    $sourceElectronicQueueInfo_id = $this->getFirstResultFromQuery("
						select ElectronicQueueInfo_id
						from v_ElectronicService es
						where ElectronicService_id = :ElectronicService_id
						limit 1
					", array('ElectronicService_id' => $changedData['ElectronicService_id']));
                }

				$nodeParams = [
					"ElectronicQueueInfo_id" => $changedData["ElectronicQueueInfo_id"],
					"ElectronicTalon_id" => $changedData["ElectronicTalon_id"],
                    'ElectronicTreatment_id' => !empty($electronictreatment_id) ? $electronictreatment_id : null,
                    'MedService_id' => !empty($medservice_id) ? $medservice_id : null,
                    'sourceElectronicQueueInfo_id' => !empty($sourceElectronicQueueInfo_id) ? $sourceElectronicQueueInfo_id : null,
                    "ElectronicTalon_Num" => $this->convertTicketNum($changedData["ElectronicTalon_Num"]),
					"prevElectronicService_id" => $oldData["ElectronicService_id"],
					"nextElectronicService_id" => $changedData["ElectronicService_id"],
					"ElectronicService_id" => $changedData["ElectronicService_id"],
					"ElectronicService_Name" => $changedData["ElectronicService_Name"],
					"ElectronicService_Code" => $changedData["ElectronicService_Code"],
					"ElectronicTalonStatus_id" => $changedData["ElectronicTalonStatus_id"],
					"ElectronicTalonStatus_Name" => $electronicTalonStatus["ElectronicTalonStatus_Name"],
					"message" => "electronicTalonStatusHasChanged"
				];
				if (!empty($changedData["MedStaffFact_id"])) {
					// получаем кабинет
					$this->load->model("TimetableGraf_model");
					$funcParams = ["MedStaffFact_id" => $changedData["MedStaffFact_id"]];
					$room = $this->TimetableGraf_model->getDoctorRoom($funcParams);
					if (!empty($room)) {
						$nodeParams["MedStaffFact_Room"] = $room;
					}
					// и ФИО
					$full_name = $this->getFirstRowFromQuery("
						select
						    coalesce(msf.Person_SurName, '') as \"Doctor_SurName\",
						    coalesce(msf.Person_FirName, '') as \"Doctor_FirName\",
						    coalesce(msf.Person_SecName, '') as \"Doctor_SecName\"
						from v_MedStaffFact msf
						where MedStaffFact_id = :MedStaffFact_id
						limit 1
					", ["MedStaffFact_id" => $changedData["MedStaffFact_id"]]);
					$nodeParams["Doctor_Fin"] =
						mb_ucfirst(mb_strtolower($full_name["Doctor_SurName"])) .
						" " . (!empty($full_name["Doctor_FirName"]) ? mb_substr($full_name["Doctor_FirName"], 0, 1) . "." : "") .
						(!empty($full_name["Doctor_SecName"]) ? mb_substr($full_name["Doctor_SecName"], 0, 1) . "." : "");
				}
				$this->sendElectronicQueueNodeMessage($nodeParams);
			}
			// делаем переадресацию для ЭО где много ПО
			if (!empty($data["isMultiserviceElectronicQueue"])) {
				// нагребаем параметры для НОДА
				$nodeParams = [
					"ElectronicTalon_id" => $changedData["ElectronicTalon_id"],
					"ElectronicTalon_Num" => $changedData["ElectronicTalon_Num"],
					"fromElectronicService_id" => $oldData["ElectronicService_id"],
					"ElectronicTalonStatus_id" => $changedData["ElectronicTalonStatus_id"],
					"ElectronicTalonStatus_Name" => $electronicTalonStatus["ElectronicTalonStatus_Name"]
				];
				if (array_key_exists("ElectronicService_id", $data)) {
					if ($is_linear_eq) {
						// только если ПО изменился
						if ($oldData["ElectronicService_id"] != $changedData["ElectronicService_id"]) {
							$nodeParams["ElectronicService_id"] = $changedData["ElectronicService_id"];
							$nodeParams["message"] = "electronicTalonRedirected";
							$this->sendElectronicQueueNodeMessage($nodeParams);
						}
					} else {
						$nodeParams["ElectronicQueueInfo_id"] = $changedData["ElectronicQueueInfo_id"];
						$nodeParams["message"] = "electronicTalonIsBusy";
						$this->sendElectronicQueueNodeMessage($nodeParams);
					}
				} else {
					if (!$is_linear_eq) {
						// только если ПО изменился
						if ($oldData["ElectronicService_id"] != $changedData["ElectronicService_id"] && $changedData["ElectronicService_id"] == null) {
							$nodeParams["ElectronicService_id"] = 0;
							$nodeParams["message"] = "electronicTalonRedirected";
							$this->sendElectronicQueueNodeMessage($nodeParams);
						}
						// если ПО не пришел значит отправляем талон в общую кучу
						$nodeParams["ElectronicQueueInfo_id"] = $changedData["ElectronicQueueInfo_id"];
						$nodeParams["message"] = "electronicTalonIsFreeForCall";
						$this->sendElectronicQueueNodeMessage($nodeParams);
					}
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Добавляем нули к номеру талона
	 * @param $num
	 * @return string
	 */
	function convertTicketNum($num)
	{
		$talon_num = trim($num);
		$maxTalonDigits = 4;
		if ($talon_num && (mb_strlen($talon_num) < $maxTalonDigits)) {
			for ($i = 0; $i < $maxTalonDigits - mb_strlen($talon_num); $i++) {
				$talon_num = "0" . $talon_num;
			}
			$num = $talon_num;
		}
		return $num;
	}

	/**
	 * Обновление талона ЭО
	 * @param $data
	 * @return array|false
	 */
	function updateElectronicTalon($data)
	{
		$data["EvnDirection_uid"] = (!empty($data["EvnDirection_uid"])) ? $data["EvnDirection_uid"] : null;
		$query = "
			select
				ElectronicTalon_id as \"ElectronicTalon_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ElectronicTalon_upd (
				ElectronicTalon_id := :ElectronicTalon_id,
				ElectronicQueueInfo_id := :ElectronicQueueInfo_id,
				ElectronicTalon_Num := :ElectronicTalon_Num,
				ElectronicTalonStatus_id := :ElectronicTalonStatus_id,
				ElectronicTalon_OrderNum := :ElectronicTalon_OrderNum,
				ElectronicService_id := :ElectronicService_id,
				EvnDirection_id := :EvnDirection_id,
				EvnDirection_uid := :EvnDirection_uid,
				ElectronicTreatment_id := :ElectronicTreatment_id,
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
			)			
		";
		$response = $this->queryResult($query, $data);
		return $response;
	}

	/**
	 * Добавляем запись в историю талона
	 * @param $data
	 * @return array|false
	 */
	function updateElectronicTalonHistory($data)
	{
		$query = "
			select
				ElectronicTalonHist_id as \"ElectronicTalonHist_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ElectronicTalonHist_ins(
				ElectronicTalon_id := :ElectronicTalon_id,
				ElectronicTalonStatus_id := :ElectronicTalonStatus_id,
				ElectronicService_id := :ElectronicService_id,
				pmUser_id := :pmUser_id
			)
		";
		// сохраняем изменения в историю ElectronicTalonHist
		$response = $this->queryResult($query, $data);
		return $response;
	}

	/**
	 * Удаляем перенаправления талона ЭО
	 * @param $data
	 * @throws Exception
	 */
	function deleteElectronicTalonRedirect($data)
	{
		// удаляем запись перенаправления
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ElectronicTalonRedirect_del(
				ElectronicTalonRedirect_id := :ElectronicTalonRedirect_id
			)
		";
		$this->queryResult($query, $data);
		// перенаправляем талон в предыдущий пункт обслуживания
		$this->redirectElectronicTalon($data);
	}

	/**
	 * Отмена электронного талона по направлению
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function cancelElectronicTalonByEvnDirection($data)
	{
		// Если текущий статус Талона ЭО не равен "Отменен" или "Обслужен",
		// то Талону ЭО присваивается статус «Отменен».
		$query = "
			select et.ElectronicTalon_id as \"ElectronicTalon_id\"
			from v_ElectronicTalon et
			where et.EvnDirection_id = :EvnDirection_id
			  and et.ElectronicTalonStatus_id not in (4, 5)
		";
		$queryParams = ["EvnDirection_id" => $data["EvnDirection_id"]];
		$resp_et = $this->queryResult($query, $queryParams);
		if (!is_array($resp_et)) {
			throw new Exception("Ошибка при получении списка талонов для отмены");
		}
		foreach ($resp_et as $one_et) {
			$funcParams = [
				"ElectronicTalon_id" => $one_et["ElectronicTalon_id"],
				"ElectronicTalonStatus_id" => 5, // Изменяется текущий статус на Отменен
				"pmUser_id" => $data["pmUser_id"]
			];
			$this->setElectronicTalonStatus($funcParams);
		}
		// смотрим есть ли доп. напаравления у талона ЭО
		$query = "
			select
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				etr.ElectronicTalonRedirect_id as \"ElectronicTalonRedirect_id\",
				etr.ElectronicService_uid as \"ElectronicService_uid\"
			from
				v_ElectronicTalon et
				inner join v_ElectronicTalonRedirect etr on etr.EvnDirection_uid = et.EvnDirection_uid
			where et.EvnDirection_uid = :EvnDirection_id
			limit 1
		";
		$queryParams = ["EvnDirection_id" => $data["EvnDirection_id"]];
		$electronicTalonRedirect = $this->getFirstRowFromQuery($query, $queryParams, true);
		if ($electronicTalonRedirect === false) {
			throw new Exception("Ошибка при получении данных перенаправления");
		}
		if ($electronicTalonRedirect) {
			// если есть связанное перенаправление удаляем его
			// и перенаправляем талон обратно где он был до этого
			$funcParams = [
				"ElectronicTalon_id" => $electronicTalonRedirect["ElectronicTalon_id"],
				"EvnDirection_uid" => $data["EvnDirection_id"],
				"ElectronicTalonRedirect_id" => $electronicTalonRedirect["ElectronicTalonRedirect_id"],
				"ElectronicService_id" => $electronicTalonRedirect["ElectronicService_uid"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$this->deleteElectronicTalonRedirect($funcParams);
		}
		return [["success" => true]];
	}

	/**
	 * Создать направление в регистратуру, поместить в очередь EvnQueue,
	 * получить код брони, получить номер талона
	 * @param $data
	 * @return array|false
	 */
	function ApplyElectronicQueue($data)
	{
		$this->load->model("EvnDirection_model", "edmodel");
		$data["year"] = ($data["EvnDirection_setDT"] instanceof DateTime)
			? $data["EvnDirection_setDT"]->format("Y")
			: substr($data["EvnDirection_setDT"], 0, 4);
		// генерим номер EvnDirection_Num
		$funcParams = [
			"Lpu_id" => $data["Lpu_id"],
			"year" => $data["year"]
		];
		$edNumReq = $this->edmodel->getEvnDirectionNumber($funcParams);
		if (is_array($edNumReq) && isset($edNumReq[0]["EvnDirection_Num"])) {
			$data["EvnDirection_Num"] = $edNumReq[0]["EvnDirection_Num"];
		}
		if (empty($data["EvnDirection_IsGenTalonCode"])) {
			$data["EvnDirection_IsGenTalonCode"] = null;
		}
		if (empty($data["MedService_did"]) && !empty($data["MedService_id"])) {
			$data["MedService_did"] = $data["MedService_id"];
		}
		$sql = "
			select
				EvnDirection_id as \"EvnDirection_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnDirection_insToQueue(
				Lpu_id := :Lpu_id,
				Lpu_did := :Lpu_did,
				MedService_did := :MedService_did,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				pmUser_id := :pmUser_id,
				EvnDirection_Num := :EvnDirection_Num,
				EvnDirection_IsAuto := :EvnDirection_IsAuto,
				EvnDirection_setDT := :EvnDirection_setDT,
				DirType_id := :DirType_id,
				EvnDirection_IsGenTalonCode := :EvnDirection_IsGenTalonCode
		)";
		$result = $this->queryResult($sql, $data);
		return $result;
	}

	/**
	 * Создаем бирку и записываем на нее, результат в виде направления возращаем
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function applyOnUnscheduledTimetable($data)
	{
		$this->load->helper("Reg");
		$this->load->model("Timetable_model", "tt_model");
		$result = ["EvnDirection_id" => null];
		$data["Day"] = TimeToDay(time());
		$data["TimetableObject_id"] = 1;

		$data["Unscheduled"] = true; // признак того что создаем бирку незапланированную
		$data["ignoreCanRecord"] = true; // признак игнора записи на бирку
		$data["OverrideWarning"] = true; // предупреждения не показываем
		$data["isElectronicQueueRedirect"] = true; // для того чтобы не генерился код брони

		// для направления
		$query = "
			select
                PersonEvn_id as \"PersonEvn_id\",
                Server_id as \"Server_id\"
            from v_PersonState
            where Person_id = :Person_id
            limit 1
		";
		$personEvn = $this->getFirstRowFromQuery($query, $data);

		$data["PersonEvn_id"] = (!empty($personEvn["PersonEvn_id"]) ? $personEvn["PersonEvn_id"] : null);
		$data["Server_id"] = (!empty($personEvn["Server_id"]) ? $personEvn["Server_id"] : null);
		$data["EvnDirection_IsAuto"] = 2; // направление системное

		$currentDate = $this->getFirstResultFromQuery("select dbo.tzGetDate() as date");
		$data["EvnDirection_setDate"] = ConvertDateFormat($currentDate, 'Y-m-d'); // направление системное

		$data["EvnDirection_setDT"] = $data["EvnDirection_setDate"];
		$data["EvnDirection_Num"] = "0"; // хз почему генерится номер по такому условию...
		$data["LpuSection_did"] = $data["LpuSection_id"];
		$data["Lpu_did"] = $data["Lpu_id"];
		$data["EvnDirection_Descr"] = "";
		$data["MedPersonal_zid"] = $data["MedPersonal_id"];
		$data["Diag_id"] = null;
		switch ($data["object"]) {
			case "TimetableGraf":
                $this->load->model('TimetableGraf_model');
				$response = $this->TimetableGraf_model->addTTGUnscheduled($data);
				if (empty($response["TimetableGraf_id"])) {
					throw new Exception("Ошибка создания дополнительной бирки");
				}
				$data["TimetableGraf_id"] = $response["TimetableGraf_id"];
				$data["DirType_id"] = 3; // на консультацию
				$response = $this->tt_model->Apply($data);
				if (empty($response["EvnDirection_id"])) {
					throw new Exception('Ошибка создания направления и записи на бирку');
				}
				$result["EvnDirection_id"] = $response["EvnDirection_id"];
				break;
			case "TimetableMedService":
                $this->load->model('TimetableMedService_model');
                $response = $this->TimetableMedService_model->addTTMSDop($data);

                if (!empty($response['TimetableMedService_id'])) {
                    $data['TimetableMedService_id'] = $response['TimetableMedService_id'];
                } else {
                    throw new Exception('Ошибка создания дополнительной бирки');
                }

                $data['DirType_id'] = 10; // на исследование
                $response = $this->tt_model->Apply($data);

                if (!empty($response['EvnDirection_id'])) {
                    $result['EvnDirection_id'] = $response['EvnDirection_id'];
                } else {
                    $err_msg = !empty($response['Error_Msg']) ? ': '.$response['Error_Msg'] : '';
                   throw new Exception('Ошибка создания направления и записи на бирку'.$err_msg);
                }
                break;

            case "TimetableResource":
                $data['DirType_id'] = 10; // на исследование
                break;
			case "NotTimetable":
				$data["DirType_id"] = 24; // Регистратура
				$data["EvnDirection_IsGenTalonCode"] = 1; // не генерим код брони
				$response = $this->ApplyElectronicQueue($data);
				if (!(!empty($response) && !empty($response[0]["EvnDirection_id"]))) {
					throw new Exception("Ошибка создания направления в регистратуру");
				}
				$result["EvnDirection_id"] = $response[0]["EvnDirection_id"];
				break;
		}
		return $result;
	}

	/**
	 * Запись на бирку и создание направления для нашего объекта
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function makeEvnDirection($data)
	{
		// необходимо записать на бирку
		if (empty($data["MedStaffFact_id"]) && empty($data["MedServiceType_SysNick"])) {
			throw new Exception("Не определен тип объекта перенаправления");
		}
		if (!empty($data["MedStaffFact_id"])) {
			$data["object"] = "TimetableGraf";
		} else if (!empty($data["MedServiceType_SysNick"]) && $data['MedServiceType_SysNick'] == 'regpol') {
            $data['object'] = "NotTimetable";
        } else if (!empty($data['MedService_id'])) {
            $ms_type = $this->getFirstResultFromQuery("
				select
					mst.MedServiceType_SysNick
				from 
				    v_MedService ms
				    inner join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				where
				    ms.MedService_id = :MedService_id
				limit 1
			", ['MedService_id' => $data['MedService_id']]);


            switch ($ms_type) {
                case "func":
                    $data['object'] = "TimetableResource";
                    break;
                case "pzm":
                case "lab":
                    $data['object'] = "TimetableMedService";
                    break;
            }
		}
		$result = $this->applyOnUnscheduledTimetable($data);
		if (empty($result["EvnDirection_id"])) {
            $err_msg = !empty($result['Error_Msg']) ? ': '.$result['Error_Msg'] : '';
			throw new Exception("Не удалось создать направление" .': '. $err_msg);
		}
		return ["EvnDirection_id" => $result["EvnDirection_id"]];
	}

	/**
	 * Перенаправляем талон ЭО
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function redirectElectronicTalon($data)
	{
		$electronicTalon = $this->getElectronicTalonById($data);
		if (!empty($data["redirectBack"])) {
			$data["redirectBack"] = ($data["redirectBack"] === "true" || $data["redirectBack"] == 1);
		}
		if (empty($electronicTalon[0]["ElectronicTalon_id"])) {
			throw new Exception("Указанный талон ЭО не существует");
		}
		$electronicTalon = $electronicTalon[0];
		$data["Person_id"] = $electronicTalon["Person_id"];
		// где сейчас талон
		$fromElectronicService_id = $electronicTalon["ElectronicService_id"];
		$params = [
			"ElectronicTalon_id" => $data["ElectronicTalon_id"],
			"fromElectronicService_id" => $fromElectronicService_id,
			"toElectronicService_id" => $data["ElectronicService_id"],
			"pmUser_id" => $data["pmUser_id"],
			"EvnDirection_uid" => null
		];
		if (empty($data["EvnDirection_id"]) && empty($data["redirectBack"])) {
			$resp_ed = $this->makeEvnDirection($data);
			if (empty($resp_ed["EvnDirection_id"])) {
				return $resp_ed; // возвращаем ошибку
			}
			$data["EvnDirection_id"] = $resp_ed["EvnDirection_id"];
		}
		// смотрим направление, если он есть, он будет  сохранен как EvnDirection_uid
		if (!empty($data["EvnDirection_id"]) && empty($data["redirectBack"])) {
			// направление которое пришло из записи на бирку
			$params["EvnDirection_uid"] = $data["EvnDirection_id"];
		} else if (!empty($data["redirectBack"])) {
			// возвращаем
			$primaryElectronicService = $this->getPrimaryElectronicService($data);
			if (empty($primaryElectronicService[0]["fromElectronicService_id"])) {
				throw new Exception("Невозможно определить первоначальный пункт обслуживания, возврат талона не возможен");
			}
			// если это возврат не в первоначальный пункт обслуживания
			// то пытаемся найти доп. направление этого пункта обслуживания
			$primaryElectronicService_id = $primaryElectronicService[0]["fromElectronicService_id"];
			if ($primaryElectronicService_id != $params["toElectronicService_id"]) {
				// возвращаем доп. направление этого пункта обслуживания
				$electronicServiceEvnDirection = $this->getRedirectedElectronicServiceEvnDirection($data);
				if (empty($electronicServiceEvnDirection[0]["EvnDirection_uid"])) {
					// возможно что мы сюда еще не перенаправляли, поэтому создаим талон и перенаправим туда
					$resp_ed = $this->makeEvnDirection($data);
					if (empty($resp_ed["EvnDirection_id"])) {
						return $resp_ed; // возвращаем ошибку
					}
					$data["EvnDirection_id"] = $resp_ed["EvnDirection_id"];
					if (!empty($data["EvnDirection_id"])) {
						$params["EvnDirection_uid"] = $data["EvnDirection_id"];
					}
				} else {
					// возвращаем на это доп. направление
					$params["EvnDirection_uid"] = $electronicServiceEvnDirection[0]["EvnDirection_uid"];
				}
			}
		} else {
			throw new Exception("Не указано направление для перенаправления");
		}
		// добавляем запись в кладезь перенаправлений
		$query = "
			select
				ElectronicTalonRedirect_id as \"ElectronicTalonRedirect_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ElectronicTalonRedirect_ins(
				ElectronicTalon_id := :ElectronicTalon_id,
				ElectronicService_id := :toElectronicService_id,
				ElectronicService_uid := :fromElectronicService_id,
				EvnDirection_uid := :EvnDirection_uid,
				pmUser_id := :pmUser_id
			)
		";
		$response = $this->queryResult($query, $params);
		// добавляем запись в историю талона, меняем статус для предыдущего пункта
		$electronicTalon["pmUser_id"] = $data["pmUser_id"];
		$electronicTalon["ElectronicTalonStatus_id"] = 4; // Обслужен
		$this->updateElectronicTalonHistory($electronicTalon);
		// обновляем талон, указав новый пункт и статус
		$electronicTalon["ElectronicTalonStatus_id"] = 1; // Ожидает
		$electronicTalon["ElectronicService_id"] = $data["ElectronicService_id"]; // новый пункт
		$electronicTalon["EvnDirection_uid"] = $params["EvnDirection_uid"]; // новое направление
		// если изменяется ЭО, так же меняет идентификатор ЭО в талоне
		if (!empty($data["ElectronicQueueInfo_id"])) {
			$electronicTalon["ElectronicQueueInfo_id"] = $data["ElectronicQueueInfo_id"];
		}
		$this->updateElectronicTalon($electronicTalon);
		// добавляем запись в историю талона, что он переведен в другой пункт со статусом "Ожидает"
		$this->updateElectronicTalonHistory($electronicTalon);
		$electronicTalonStatus = $this->getElectronicTalonStatusById($electronicTalon);
		if (empty($electronicTalonStatus[0])) {
			throw new Exception("Невозможно определить статус талона");
		}
		$electronicTalonStatus = $electronicTalonStatus[0];
		// нагребаем параметры для НОДА
		$nodeParams = [
			"message" => "electronicTalonRedirected",
			"ElectronicTalon_id" => $electronicTalon["ElectronicTalon_id"],
			"ElectronicTalon_Num" => $electronicTalon["ElectronicTalon_Num"],
			"ElectronicService_id" => $electronicTalon["ElectronicService_id"],
			"ElectronicQueueInfo_id" => $electronicTalon["ElectronicQueueInfo_id"],
			"ElectronicTalonStatus_id" => $electronicTalon["ElectronicTalonStatus_id"],
			"ElectronicTalonStatus_Name" => $electronicTalonStatus["ElectronicTalonStatus_Name"],
		];
		// отправляем сообщение пункту обслуживания, кому переадресован талон
		$this->sendElectronicQueueNodeMessage($nodeParams);
		$query = "
            select
                ElectronicService_id as \"ElectronicService_id\",
                ElectronicService_Name as \"ElectronicService_Name\"
            from v_ElectronicService
            where ElectronicService_id = :toElectronicService_id
            limit 1
		";
		$queryParams = ["toElectronicService_id" => $data["ElectronicService_id"]];
		$electronicServiceInfo = $this->queryResult($query, $queryParams);
		if (!empty($response[0]["ElectronicTalonRedirect_id"]) && !empty($electronicServiceInfo[0]["ElectronicService_Name"])) {
			$response[0]["ElectronicService_Name"] = $electronicServiceInfo[0]["ElectronicService_Name"];
		}
		return $response;
	}

	/**
	 * Отправка информационного сообщения в АРМы, через нод
	 * @param $data
	 * @return array
	 */
	function sendElectronicQueueNodeMessage($data)
	{
		// инициализируем настройки соединения
		$config = null;
		if (defined("NODEJS_PORTAL_PROXY_HOSTNAME") && defined("NODEJS_PORTAL_PROXY_HTTPPORT")) {
			// берём хост и порт из конфига, если есть
			$config = [
				"host" => NODEJS_PORTAL_PROXY_HOSTNAME,
				"port" => NODEJS_PORTAL_PROXY_HTTPPORT
			];
			$this->load->helper("NodeJS");
			$response = NodePostRequest($data, $config);
			if (!empty($response[0]["Error_Msg"])) {
				return $response[0];
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Подгрузка комбо с пунктами обслуживания по текущему подразделению ЭО или ЛПУ (для перенаправления)
	 * @param $data
	 * @return array
	 */
	public function loadLpuBuildingElectronicServices($data)
	{
		$filter = "";
		$apply = "";
		$select = "";
		$params = ["Lpu_id" => $data["Lpu_id"]];
		if (!empty($data["CurrentElectronicService_id"])) {
			$params["CurrentElectronicService_id"] = $data["CurrentElectronicService_id"];
			$filter .= " and es.ElectronicService_id != :CurrentElectronicService_id ";
		}
		if (!empty($data["ElectronicTalon_id"])) {
			$params["ElectronicTalon_id"] = $data["ElectronicTalon_id"];
			$apply .= "
				left join lateral(
					select case when coalesce(etr.ElectronicTalonRedirect_id, 0) > 0 then 1 else 0 end as wasRedirectedTo
					from
						v_ElectronicTalonRedirect etr
						inner join v_ElectronicTalon et on et.ElectronicTalon_id = etr.ElectronicTalon_id
					where etr.ElectronicTalon_id = :ElectronicTalon_id
					  and etr.ElectronicService_uid = es.ElectronicService_id
					  and et.ElectronicTalon_insDT::date = tzGetdate()::date
				) as etroa on true
			";
			$select .= "
				, etroa.wasRedirectedTo as \"wasRedirectedTo\"
			";
		}
		if (empty($data["noLoad"])) {
			$apply .= "
				left join lateral(
					select count(et.ElectronicTalon_id) as cnt
					from v_ElectronicTalon et
					where et.ElectronicTalonStatus_id in (1, 2, 3)
					  and et.ElectronicService_id = es.ElectronicService_id
					  and et.ElectronicTalon_insDT::date = dbo.tzGetDate()::date
				) as load on true
			";
			$select .= " , load.cnt as \"ElectronicService_Load\" ";
		}
		if (!empty($data["LpuBuilding_id"])) {
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
			$filter .= "
				and (
					eqi.LpuBuilding_id = :LpuBuilding_id or
					eqi.MedService_id in (select ms2.MedService_id from v_MedService ms2 where ms2.LpuBuilding_id = :LpuBuilding_id) or
					eqi.MedService_id in (select ms3.MedService_id from v_MedService ms3 where ms3.Lpu_id = :Lpu_id and ms3.LpuBuilding_id is null)
				)
			";
		}
		$query = "
			select distinct
				es.ElectronicService_id as \"ElectronicService_id\",
				eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				eqi.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\",
				coalesce(lb.LpuBuilding_id, lbms.LpuBuilding_id) as \"LpuBuilding_id\",
				coalesce(lb.LpuBuilding_Name, lbms.LpuBuilding_Name) as \"LpuBuilding_Name\",
				es.ElectronicService_Code as \"ElectronicService_Code\",
				es.ElectronicService_Name as \"ElectronicService_Name\",
				mseq.MedStaffFact_id as \"MedStaffFact_id\",
				mseq.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				coalesce(ucms.MedService_id, eqi.MedService_id) as \"MedService_id\"
				{$select}
			from
				v_ElectronicQueueInfo eqi
				inner join v_ElectronicService es on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
				left join v_MedServiceElectronicQueue mseq on mseq.ElectronicService_id = es.ElectronicService_id
				left join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = mseq.UslugaComplexMedService_id
				left join v_MedService ms on ms.MedService_id = ucms.MedService_id or ms.MedService_id = eqi.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = eqi.LpuBuilding_id
				left join v_LpuBuilding lbms on lbms.LpuBuilding_id = ms.LpuBuilding_id
				{$apply}
			where eqi.ElectronicQueueInfo_IsOff = 1
			  and eqi.Lpu_id = :Lpu_id
			  {$filter}
			order by
			    \"LpuBuilding_id\" desc,
			    \"MedService_id\"
		";
		$resp = $this->queryResult($query, $params);
		$output = [];
		if (!empty($resp)) {
			$lastGroupName = $resp[0]["LpuBuilding_Name"];
			$output[0]["GroupName"] = $lastGroupName;
			$output[0]["ElectronicService_id"] = 0;
			foreach ($resp as $key => $svc) {
				$svc["GroupName"] = null;
				if ($svc["LpuBuilding_Name"] !== $lastGroupName) {
					$lastGroupName = $svc["LpuBuilding_Name"];
					$output[] = [
						"GroupName" => (empty($lastGroupName)) ? "Подразделение не указано" : $lastGroupName,
						"ElectronicService_id" => 0
					];
				}
				$output[] = $svc;
			}
		}
		return $output;
	}


	/**
	 * Подгрузка комбо с пунктами обслуживания для редиректа талона
	 * @param $data
	 * @return array|false
	 */
	public function loadRedirectedTalonServices($data)
	{
		$params = [
			"ElectronicTalon_id" => $data["ElectronicTalon_id"],
			"currentElectronicService_id" => $data["currentElectronicService_id"]
		];
		$query = "
			select
				es.ElectronicService_id as \"ElectronicService_id\",
				es.ElectronicService_Code as \"ElectronicService_Code\",
				es.ElectronicService_Name as \"ElectronicService_Name\"
			from
				v_ElectronicTalonRedirect etr
				inner join v_ElectronicService es on es.ElectronicService_id = etr.ElectronicService_id
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = etr.EvnDirection_uid
			where etr.ElectronicTalon_id = :ElectronicTalon_id
			  and etr.ElectronicService_id != :currentElectronicService_id -- не показываем ПО текущий
			  and ed.EvnStatus_id != 12 -- отмененные назначения не показываем
			order by etr.ElectronicTalonRedirect_id desc
		";
		$resp = $this->queryResult($query, $params);
		$primaryElectronicService = $this->getPrimaryElectronicService($data);
		if (!empty($primaryElectronicService[0]["ElectronicService_id"])) {
			$resp = (!empty($resp[0]["ElectronicService_id"])) ? array_merge($resp, $primaryElectronicService) : $primaryElectronicService;
		}
		return $resp;
	}

	/**
	 * Получаем самый первый пункт обcлуживания при перенаправлении
	 * @param $data
	 * @return array|false
	 */
	function getPrimaryElectronicService($data)
	{
		$params["ElectronicTalon_id"] = $data["ElectronicTalon_id"];
		$query = "
			select
				etr.ElectronicService_uid as \"fromElectronicService_id\",
				etr.ElectronicService_uid as \"ElectronicService_id\",
				es.ElectronicService_Code as \"ElectronicService_Code\",
				es.ElectronicService_Name as \"ElectronicService_Name\"
			from
				v_ElectronicTalonRedirect etr
				inner join v_ElectronicService es on es.ElectronicService_id = etr.ElectronicService_uid
				left join lateral (
					select min(etra.ElectronicTalonRedirect_id) as ElectronicTalonRedirect_id
					from
						v_ElectronicTalonRedirect etra
						inner join v_EvnDirection_all eda on eda.EvnDirection_id = etra.EvnDirection_uid
					where etra.ElectronicTalon_id = :ElectronicTalon_id
					  and eda.EvnStatus_id != 12 -- отмененные назначения не показываем
				) as min on true
			where etr.ElectronicTalonRedirect_id = min.ElectronicTalonRedirect_id
			limit 1
		";
		$response = $this->queryResult($query, $params);
		return $response;
	}

	/**
	 * Получаем доп. направление пункта обслуживания куда перенаправили талон ЭО
	 * @param $data
	 * @return array|false
	 */
	function getRedirectedElectronicServiceEvnDirection($data)
	{
		$params = [
			"ElectronicTalon_id" => $data["ElectronicTalon_id"],
			"ElectronicService_id" => $data["ElectronicService_id"]
		];
		$query = "
			select etr.EvnDirection_uid as \"EvnDirection_uid\"
			from
				v_ElectronicTalonRedirect etr
				left join lateral (
					select min(etra.ElectronicTalonRedirect_id) as ElectronicTalonRedirect_id
					from
						v_ElectronicTalonRedirect etra
						inner join v_EvnDirection_all eda on eda.EvnDirection_id = etra.EvnDirection_uid
					where etra.ElectronicTalon_id = :ElectronicTalon_id
					  and etra.ElectronicService_id = :ElectronicService_id
					  and eda.EvnStatus_id != 12 -- отмененные назначения не показываем
				) as min on true
			where etr.ElectronicTalonRedirect_id = min.ElectronicTalonRedirect_id
			limit 1
		";
		$response = $this->queryResult($query, $params);
		return $response;
	}

	/**
	 * История талона электронной очереди
	 * @param $data
	 * @return array|false
	 */
	function getElectronicTalonHistory($data)
	{
		$params = ["ElectronicTalon_id" => $data["ElectronicTalon_id"]];
		$query = "
			select
				eth.ElectronicTalon_id as \"ElectronicTalon_id\",
				eth.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				eth.ElectronicService_id as \"ElectronicService_id\",
				eth.pmUser_insID as \"pmUser_insID\",
				to_char(eth.ElectronicTalonHist_insDT, '{$this->dateTimeForm120}') as \"ElectronicTalonHist_insDT\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				case
					when eth.pmUser_insID = 1 then 'Система'
					when eth.pmUser_insID = 999901 then 'Инфомат'
					when eth.pmUser_insID > 1000000 and eth.pmUser_insID < 5000000 then 'Пользователь портала'
			 		else pmuc.PMUser_Name 
				end as \"PMUser_Name\"
			from
				v_ElectronicTalonHist eth
				left join v_ElectronicTalon et on et.ElectronicTalon_id = eth.ElectronicTalon_id
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = eth.ElectronicTalonStatus_id
				left join v_ElectronicService es on es.ElectronicService_id = eth.ElectronicService_id
				left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
				left join v_pmUserCache pmuc on pmuc.PMUser_id = eth.pmUser_insID
			where eth.ElectronicTalon_id = :ElectronicTalon_id
		";
		$response = $this->queryResult($query, $params);
		return $response;
	}

	/**
	 * @param $data
	 */
	function sendElectronicTalonMessage($data)
	{
		$query = "
			select
				PersonInfo_InternetPhone as \"PersonInfo_InternetPhone\",
				PersonInfo_Email as \"PersonInfo_Email\"
			from v_PersonInfo
			where Person_id = :Person_id
			limit 1
		";
		$notification = $this->getFirstRowFromQuery($query, $data);
		if (is_array($notification)) {
			$this->load->helper("Notify");
			if (!empty($notification["PersonInfo_Email"])) {
				$funcParams = [
					"EMail" => $notification["PersonInfo_Email"],
					"title" => "Код бронирования",
					"body" => "Ваш код бронирования: {$data["EvnDirection_TalonCode"]} для регистрации в электронной очереди."
				];
				sendNotifyEmail($funcParams);
			}
			if (!empty($notification["PersonInfo_InternetPhone"])) {
				$funcParams = [
					"UserNotify_Phone" => $notification["PersonInfo_InternetPhone"],
					"text" => "Ваш код бронирования: {$data["EvnDirection_TalonCode"]} для регистрации в электронной очереди.",
					"User_id" => $data["pmUser_id"]
				];
				sendNotifySMS($funcParams);
			}
		}
	}


    /**
     * @param $data
     * @return array|false
     */
    function getGridElectronicQueueData($data)
    {

        if (empty($data['DirectionList']) || !is_array($data['DirectionList'])) {
            return [];
        }

        $filter = "";
        $params = [];

        if (!empty($data['ElectronicTalon_Num'])) {
            $filter .= " and et.ElectronicTalon_Num ilike '%' || :ElectronicTalon_Num || '%' ";
            $params['ElectronicTalon_Num'] = $data['ElectronicTalon_Num'];
        }

        if (empty($data['ElectronicTalonPseudoStatus_id'])) {
            $filter .= " and et.ElectronicTalonStatus_id in (1,2,3) ";
        }

        $list = implode(',',$data['DirectionList']);

        $query = "				
			select
				case when et.EvnDirection_uid is not null
					then et.EvnDirection_uid
					else et.EvnDirection_id
				end as \"EvnDirection_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.EvnDirection_uid as \"EvnDirection_uid\",
				etr.ElectronicService_id as \"toElectronicService_id\",
				etr.ElectronicService_uid as \"fromElectronicService_id\",
				et.ElectronicTreatment_id as \"ElectronicTreatment_id\",
				etre.ElectronicTreatment_Name as \"ElectronicTreatment_Name\",
				DATEDIFF('ss', et.ElectronicTalon_insDT, getdate()) as \"ElectronicTalon_TimeHasPassed\",
				coalesce(ttg.TimetableGraf_begTime, ttms.TimetableMedService_begTime, ttr.TimetableResource_begTime) as \"Timetable_begTime\"
			from v_ElectronicTalon et
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicTreatment etre on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
				left join v_TimetableGraf ttg on ttg.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableMedService ttms on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableResource ttr on ttr.EvnDirection_id = ed.EvnDirection_id
			left join lateral (
				select * 
                from v_ElectronicTalonRedirect etr
				where etr.EvnDirection_uid = et.EvnDirection_uid
				limit 1
			) etr
			where (1=1)
				and (et.EvnDirection_id in ({$list}) or et.EvnDirection_uid in ({$list}))
				{$filter}
		";

        $response = $this->queryResult($query, $params);
        return $response;
    }
}
