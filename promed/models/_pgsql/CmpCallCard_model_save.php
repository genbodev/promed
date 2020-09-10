<?php

class CmpCallCard_model_save
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @param null $cccConfig
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveCmpCallCard(CmpCallCard_model $callObject, $data, $cccConfig = null)
	{
		$dolog = (defined("DOLOGSAVECARD") && DOLOGSAVECARD === true) ? true : false;
		if ($dolog) {
			$callObject->load->library("textlog", ["file" => "saveCmpCallCardNumbers_" . date("Y-m-d") . ".log"]);
		}
		$data["CmpCallCard_prmDT"] = date("Y-m-d H:i", strtotime($data["CmpCallCard_prmDate"] . " " . $data["CmpCallCard_prmTime"])) . ":00";
		$CmpCallCard_prmDT = DateTime::createFromFormat("Y-m-d H:i", $data["CmpCallCard_prmDate"] . " " . $data["CmpCallCard_prmTime"]);
		//при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
		//перенес из контроллера
		//не сохранять неизвестного из карт с признаком АДИС #108064
		if (empty($data["CmpCallCardInputType_id"])) {
			$Person_id = $callObject->checkUnknownPerson($data);
			if ($Person_id) {
				$data["Person_IsUnknown"] = 2;
				$data["Person_id"] = ($Person_id !== true) ? $Person_id : null;
			}
		}
		//если карта вызова пришла сохраняться из поточного ввода то тип вызова берем из параметров
		if (!empty($data["ComboCheck_CallPlace_id"]) && $data["ComboCheck_CallPlace_id"] > 0) {
			$CallPlace_Code = null;
			if ($data["ComboCheck_CallPlace_id"] == "180") $CallPlace_Code = "2";
			if ($data["ComboCheck_CallPlace_id"] == "181") $CallPlace_Code = "1";
			if ($data["ComboCheck_CallPlace_id"] == "182") $CallPlace_Code = "4";
			if ($data["ComboCheck_CallPlace_id"] == "183") $CallPlace_Code = "3";
			if ($data["ComboCheck_CallPlace_id"] == "184") $CallPlace_Code = "6";
			if ($data["ComboCheck_CallPlace_id"] == "185") $CallPlace_Code = "6";
			if ($data["ComboCheck_CallPlace_id"] == "186") $CallPlace_Code = "6";
			if ($data["ComboCheck_CallPlace_id"] == "187") $CallPlace_Code = "6";
			if ($data["ComboCheck_CallPlace_id"] == "188") $CallPlace_Code = "10";
			if ($data["ComboCheck_CallPlace_id"] == "189") $CallPlace_Code = "11";
			if ($data["ComboCheck_CallPlace_id"] == "190") $CallPlace_Code = "8";
			if ($data["ComboCheck_CallPlace_id"] == "191") $CallPlace_Code = "9";
			if (!empty($CallPlace_Code)) {
				$CallPlacesql = "select CmpCallPlaceType_id from v_CmpCallPlaceType where CmpCallPlaceType_Code = :CmpCallPlaceType_Code";
				$CallPlaceparams = [];
				$CallPlaceparams["CmpCallPlaceType_Code"] = $CallPlace_Code;
				$CallPlaceId = $callObject->getFirstResultFromQuery($CallPlacesql, $CallPlaceparams);
				if (!empty($CallPlaceId)) {
					$data["CmpCallPlaceType_id"] = $CallPlaceId;
				}
			}
		}
		if (getRegionNick() != "krym") {
			/* определяем степень срочности */
			$Ufilter = ["CCCUAPS.Lpu_id = :Lpu_id"];
			$UqueryParams = ["Lpu_id" => $data["Lpu_id"]];
			if (!empty($data["CmpReason_id"])) {
				$Ufilter[] = "CCCUAPS.CmpReason_id = :CmpReason_id";
				$UqueryParams["CmpReason_id"] = $data["CmpReason_id"];
			}
			if (!empty($data["Person_Age"])) {
				$Ufilter[] = "(CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf > :Person_Age or CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf is null)";
				$UqueryParams["Person_Age"] = $data["Person_Age"];
			}
			if (!empty($data["CmpCallPlaceType_id"])) {
				$Ufilter[] = "CUAPSRP.CmpCallPlaceType_id = :CmpCallPlaceType_id";
				$UqueryParams["CmpCallPlaceType_id"] = $data["CmpCallPlaceType_id"];
			} else {
				$Ufilter[] = "CUAPSRP.CmpCallPlaceType_id is null";
			}
			$UfilterString = (count($Ufilter) != 0)? "where ".implode(" and ", $Ufilter) : "";
			$Uquery = "
				select
					CCCUAPS.cmpurgencyandprofilestandart_id as \"CmpUrgencyAndProfileStandart_id\",
					CCCUAPS.cmpreason_id as \"CmpReason_id\",
					CCCUAPS.lpu_id as \"Lpu_id\",
					CCCUAPS.cmpurgencyandprofilestandart_urgency as \"CmpUrgencyAndProfileStandart_Urgency\",
					CCCUAPS.cmpurgencyandprofilestandart_untilageof as \"CmpUrgencyAndProfileStandart_UntilAgeOf\",
					CCCUAPS.pmuser_insid as \"pmUser_insID\",
					CCCUAPS.pmuser_updid as \"pmUser_updID\",
					CCCUAPS.cmpcallcardacceptor_id as \"CmpCallCardAcceptor_id\",
					CCCUAPS.cmpurgencyandprofilestandart_headdoctorobserv as \"CmpUrgencyAndProfileStandart_HeadDoctorObserv\",
					CCCUAPS.cmpurgencyandprofilestandart_multivictims as \"CmpUrgencyAndProfileStandart_MultiVictims\",
					CUAPSRP.cmpurgencyandprofilestandartrefplace_id as \"CmpUrgencyAndProfileStandartRefPlace_id\",
					CUAPSRP.cmpcallplacetype_id as \"CmpCallPlaceType_id\"
				from
					v_CmpUrgencyAndProfileStandart as CCCUAPS
					left join v_CmpUrgencyAndProfileStandartRefPlace CUAPSRP on CUAPSRP.CmpUrgencyAndProfileStandart_id = CCCUAPS.CmpUrgencyAndProfileStandart_id
				{$UfilterString}
			";
			/**@var CI_DB_result $Uresult */
			$Uresult = $callObject->db->query($Uquery, $UqueryParams);
			if (!is_object($Uresult)) {
				return false;
			}
			$res = $Uresult->result("array");
			if (isset($res[0]["CmpUrgencyAndProfileStandart_Urgency"])) {
				$urgency = $res[0]["CmpUrgencyAndProfileStandart_Urgency"];
				if (isset($urgency) && $urgency > 0) {
					$data["CmpCallCard_Urgency"] = $urgency;
				}
			}
		}
		$callObject->beginTransaction();
		//проверка на совпадение номера за год и номера за день
		if (!empty($data["setDay_num"]) && !empty($data["setYear_num"])) {
			// если устанавливаем значения введенные пользователем
			$data["CmpCallCard_Numv"] = $data["setDay_num"];
			$data["CmpCallCard_Ngod"] = $data["setYear_num"];
		} else {
			if (empty($data["CmpCallCard_Numv"]) || empty($data["CmpCallCard_Ngod"])) {
				$newNumValues = $callObject->getCmpCallCardNumber($data);
				if (empty($newNumValues[0])) {
					throw new Exception("Ошибка при определении номера вызова");
				}
				$data["CmpCallCard_Numv"] = $newNumValues[0]["CmpCallCard_Numv"];
				$data["CmpCallCard_Ngod"] = $newNumValues[0]["CmpCallCard_Ngod"];
			} else {
				//#119325 (Регион:Пенза)
				//Контроль уникальности номера вызова не осуществляется
				$data["Day_num"] = $data["CmpCallCard_Numv"];
				$data["Year_num"] = $data["CmpCallCard_Ngod"];
				$data["AcceptTime"] = $data["CmpCallCard_prmDate"];
				$nums = $callObject->existenceNumbersDayYear($data);
				if (!is_array($nums)) {
					throw new Exception("Ошибка при определении номера вызова");
				}
				$data["CmpCallCard_Numv"] = $nums["nextNumberDay"];
				$data["CmpCallCard_Ngod"] = $nums["nextNumberYear"];
			}
		}
		if (!empty($cccConfig["CmpCallCard_Numv"]) && !empty($cccConfig["CmpCallCard_Ngod"])) {
			$data["CmpCallCard_Numv"] = $cccConfig["CmpCallCard_Numv"];
			$data["CmpCallCard_Ngod"] = $cccConfig["CmpCallCard_Ngod"];
		}
		if (!empty($data["action"]) && $data["action"] == "edit" && !empty($data["CmpCallCard_id"])) {
			//редактирование
			$procedure = "p_CmpCallCard_upd";
			//проверка на блокировку карты
			$checkLock = $callObject->checkLockCmpCallCard($data);
			if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]["CmpCallCard_id"])) {
				throw new Exception("Невозможно сохранить. Карта вызова редактируется другим пользователем");
			}
			// Если случай закрыт и задана дата справки, то сохраняем справку.
			if (!empty($data["CmpResult_id"]) && !empty($data["CmpCallCardCostPrint_setDT"])) {
				// сохраняем справку
				$callObject->load->model("CostPrint_model");
				$callObject->CostPrint_model->saveCmpCallCardCostPrint([
					"CmpCallCard_id" => $data["CmpCallCard_id"],
					"CostPrint_IsNoPrint" => $data["CmpCallCardCostPrint_IsNoPrint"],
					"CostPrint_setDT" => $data["CmpCallCardCostPrint_setDT"],
					"pmUser_id" => $data["pmUser_id"]
				]);
			}
			$exceptedFields = [];
		} else {
			//добавление
			$procedure = "p_CmpCallCard_ins";
			$exceptedFields = ["CmpCallCard_id"];
		}
		if (!empty($data["CmpCallCard_id"])) {
			$selectOldquery = "select * from v_CmpCallCard CCC where CCC.CmpCallCard_id = :CmpCallCard_id";
			$result = $callObject->db->query($selectOldquery, $data);
			if (!is_object($result)) {
				return false;
			}
			$oldCard = $result->row_array("array");
			if (empty($oldCard["CmpCallCard_id"])) {
				$procedure = "p_CmpCallCard_ins";
				$exceptedFields = ["CmpCallCard_id"];
			}
		}

		//автоматический сбор полей с таблицы
		$jsonParams = $callObject->getJsonParamsForTable($data, 'dbo', 'CmpCallCard');
		
		foreach ($jsonParams as $key => $value) {
			if (!empty($value) && in_array($key, ["CmpCallCard_Tper", "CmpCallCard_Vyez", "CmpCallCard_Przd", "CmpCallCard_Tgsp", "CmpCallCard_Tsta", "CmpCallCard_Tisp", "CmpCallCard_Tvzv", "CmpCallCard_Tiz1"])) {
				$parsed = DateTime::createFromFormat("Y-m-d H:i", $data["CmpCallCard_prmDate"] . " " . $value);
				if (is_object($parsed)) {
					if ($parsed < $CmpCallCard_prmDT) {
						$parsed->add(new DateInterval("P1D"));
					}
					$jsonParams[$key] = $parsed->format("Y-m-d H:i:s");
				} else {
					if (DateTime::createFromFormat("Y-m-d H:i:s", $value)) {
						$jsonParams[$key] = $value;
					} else {
						$jsonParams[$key] = $data["CmpCallCard_prmDate"] . " " . $value;
					}
				}
			}
		}
		//продолжение
		if (!empty($data["action"]) && $data["action"] == "edit" && !empty($oldCard["cmpcallcard_id"])) {
			//редактирование
			//1 - выбираем старую запись
			//Делаем копию исходной записи, а измененную копию сохраняем на место старой
			//Дас ист версионность
			//2 - сохраняем старую запись в новую и отмечаем удаленной
			$oldCard["pmUser_id"] = $oldCard["pmuser_insID"];

			$oldJsonParams = $callObject->getJsonParamsForTable($oldCard, 'dbo', 'CmpCallCard');
			
			$saveOldQueryParams = [
				'CmpCloseCard_id' => null,
				'params' => json_encode(array_change_key_case($oldJsonParams, CASE_LOWER)),
				'pmUser_id' => $oldCard['pmUser_id'],
			];

            $query = "
			    SELECT
			        cmpclosecard_id as \"CmpCloseCard_id\",
			        error_code as \"Error_Code\",
			        error_message as \"Error_Msg\"
			    FROM {$callObject->schema}.p_CmpCloseCard_ins (
			        params := :params,
			        pmUser_id := :pmUser_id
			    )
			";
            $oldCardInNewRec = $callObject->getFirstRowFromQuery($query, $saveOldQueryParams);
            if (!$callObject->isSuccessful($oldCardInNewRec)) {
                $callObject->rollbackTransaction();
				return $oldCardInNewRec;
            }
            $saveOldQueryParams['CmpCloseCard_id'] = $oldCardInNewRec[0]["CmpCloseCard_id"];
            $query = "
			    SELECT 
			        :CmpCloseCard_id as \"CmpCloseCard_id\",
			        error_code as \"Error_Code\", 
			        error_message as \"Error_Msg\"
			    FROM {$callObject->schema}.p_CmpCloseCard_del (
			        CmpCloseCard_id := :CmpCloseCard_id,
			        pmUser_id := :pmUser_id
			    )
			";
            $oldCardInNewRec = $callObject->queryResult($query, $saveOldQueryParams);
            if (!$callObject->isSuccessful($oldCardInNewRec)) {
                $callObject->rollbackTransaction();
				return $oldCardInNewRec;
            }
            $query = "
			    SELECT 
			        :CmpCloseCard_id as \"CmpCloseCard_id\",
			        error_code as \"Error_Code\", 
			        error_message as \"Error_Msg\"
			    FROM p_CmpCallCard_setFirstVersion (
			        CmpCloseCard_id := :CmpCloseCard_id,
			        pmUser_id := :pmUser_id,
			        CmpCloseCard_firstVersion := " . $oldCard['CmpCallCard_id']  . "
			    )
			";
            $oldCardInNewRec = $callObject->queryResult($query, $saveOldQueryParams);
            if (!$callObject->isSuccessful($oldCardInNewRec)) {
                $callObject->rollbackTransaction();
				return $oldCardInNewRec;
            }
			//смысл значения CmpCallCard_firstVersion в том, чтобы хранить ид карты предыдущей версии,
			//те при сохранении копии карты (данных старой карты) она должна содержать ссылку на действующую карту (на ее место)
			//таким самым у нас есть 1 активная карта и несколько ссылающихся на нее (с разными ид) с признаками удаления и ссылками на активную
			//пришлось переделать логику, из-за того что карты стали отображаться как удаленные
			//не забываем забрать id бригады СМП из старой записи
			$jsonParams["EmergencyTeam_id"] = (isset($data["EmergencyTeam_id"]))
				? $data["EmergencyTeam_id"]
				: (isset($oldCard["EmergencyTeam_id"])
					? $oldCard["EmergencyTeam_id"]
					: null);
			//не забываем добавить поле для бригады СМП
			// строка уже может быть, исключим дублирование ее
			if (stristr($cccQueryFields, "EmergencyTeam_id") === false) {
				$cccQueryFields .= "EmergencyTeam_id = :EmergencyTeam_id,";
			}
			$jsonParams["CmpCallCardStatusType_id"] = $oldCard["CmpCallCardStatusType_id"];
			$jsonParams["CmpCallCard_GUID"] = $oldCard["CmpCallCard_GUID"];
			/*
			 * 3 - заменяем старую запись текущими изменениями
			 * пояснение: теперь у нас 2 одинаковые записи, на место старой записи вставляем новые данные
			 * */
			if (!empty($cccConfig)) {
				$jsonParams["CmpCallCard_GUID"] = $cccConfig["CmpCallCard_GUID"];
			}
			
			$cccQueryParams = [
				'CmpCallCard_id' => !empty($data['CmpCallCard_id'])?$data['CmpCallCard_id']:null,
				'params' => json_encode(array_change_key_case($jsonParams, CASE_LOWER)),
				'pmUser_id' => $data['pmUser_id'],
			];
			
			if (!empty($cccConfig)) {
				$cccQueryParams["CmpCallCard_id"] = $cccConfig["CmpCallCard_id"];
			} elseif (!empty($data["CmpCallCard_insID"])) {
				$cccQueryParams["CmpCallCard_id"] = $data["CmpCallCard_insID"];
			}
			
			$query = "
			    SELECT 
			        CmpCallCard_id as \"CmpCallCard_id\", 
			        :CmpCallCard_GUID as \"CmpCallCard_GUID\",
			        error_code as \"Error_Code\", 
			        error_message as \"Error_Msg\"
			    FROM {$procedure} (
			        CmpCallCard_id := :CmpCallCard_id,
			        params := :params,
			        pmUser_id := :pmUser_id
			    )
			";
			//при редактировании карты не надо постоянно дергать p_CmpCallCard_setStatus, тк на него завязана история статусов
			$result = $callObject->db->query($query, $cccQueryParams);
			if (!empty($oldCard)) {
				$callObject->checkChangesCmpCallCard($oldCard, $jsonParams);
				if ($oldCard["LpuBuilding_id"] != $data["LpuBuilding_id"]) {
					//поменяли подстанцию
					$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
					$callObject->CmpCallCard_model4E->sendCmpCallCardToLpuBuilding($data);
				};
				if ($oldCard["CmpCallCardStatusType_id"] != $jsonParams["CmpCallCardStatusType_id"]) {
					$callObject->setStatusCmpCallCard($jsonParams);
				}
			};
			$CmpCallCard_Numv = $jsonParams["CmpCallCard_Numv"];
			$CmpCallCard_Ngod = $jsonParams["CmpCallCard_Ngod"];
			if (!is_object($result)) {
				$callObject->rollbackTransaction();
				return false;
			}
			$resp = $result->result("array");
			$callObject->commitTransaction();

			//если указали отказ при редактировании
			if (!empty($data['CmpRejectionReason_id'])) {
				$statusParams = array(
					'CmpCallCard_id' => $resp[0]['CmpCallCard_id'],
					'CmpCallCardStatusType_id' => '5',
					'CmpCallCardStatus_Comment' => $data['CmpCallCardRejection_Comm'],
					'armtype' => $data['ARMType'],
					'CmpReason_id' => $data['CmpRejectionReason_id'],
					'CmpCallCard_isNMP' => $data['CmpCallCard_IsExtra'],
					'pmUser_id' => $data['pmUser_id']
				);

				$callObject->setStatusCmpCallCard($statusParams);
			}

			$ccc_id = "";
			if (!empty($jsonParams["CmpCallCard_insID"])) {
				$ccc_id = $jsonParams["CmpCallCard_insID"];
			}
			if ($dolog) {
				$callObject->textlog->add("ccc_m_3 сохранение:" . $resp[0]["CmpCallCard_id"] . " / " . $CmpCallCard_Numv . " / " . $CmpCallCard_Ngod . " / " . $ccc_id . " proc:" . $procedure);
			}
			$resp["Person_id"] = !empty($data["Person_id"]) ? $data["Person_id"] : null;
			$resp["CmpCallCard_Numv"] = !empty($CmpCallCard_Numv) ? $CmpCallCard_Numv : null;
			$resp["CmpCallCard_Ngod"] = !empty($CmpCallCard_Ngod) ? $CmpCallCard_Ngod : null;
			return $resp;
		} else {
			//добавление
			$jsonParams["CmpCallCard_insID"] = null;
			if (!empty($data["CmpCallCard_insID"])) {
				$jsonParams["CmpCallCard_insID"] = $data["CmpCallCard_insID"];
			}
			$jsonParams["CmpCallCard_GUID"] = null;
			if (!empty($cccConfig)) {
				$jsonParams["CmpCallCard_GUID"] = $cccConfig["CmpCallCard_GUID"];
				$jsonParams["CmpCallCard_insID"] = $cccConfig["CmpCallCard_id"];
				$jsonParams["CmpCallCard_prmDT"] = $cccConfig["CmpCallCard_prmDT"];
			} else if (!empty($data["CmpCallCard_insID"])) {
				$jsonParams["CmpCallCard_insID"] = $data["CmpCallCard_insID"];
			}
			
			$cccQueryParams = [
				'CmpCallCard_id' => !empty($data['CmpCallCard_id'])?$data['CmpCallCard_id']:null,
				'params' => json_encode(array_change_key_case($jsonParams, CASE_LOWER)),
				'pmUser_id' => $data['pmUser_id'],
			];
			
			$query = "
				select
					CmpCallCard_id as \"CmpCallCard_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from " . $procedure . " (
					CmpCallCard_id := :CmpCallCard_id,
					params := :params,
					pmUser_id := :pmUser_id
				)
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $cccQueryParams);
		}
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			return false;
		}
		/**@var CI_DB_result $resultforstatus */
		$resultforstatus = $result->result("array");
		$armType = "";
		if (!empty($data["ARMType"])) {
			$armType = $data["ARMType"];
		}
        $CmpCallCard_Numv = isset($jsonParams["CmpCallCard_Numv"]) ? $jsonParams["CmpCallCard_Numv"] : null;
        $CmpCallCard_Ngod = isset($jsonParams["CmpCallCard_Ngod"]) ? $jsonParams["CmpCallCard_Ngod"] : null;
		if ($dolog) {
			$callObject->addLog("ccc_m_1 сохранение:" . $resultforstatus[0]["CmpCallCard_id"] . " / " . $CmpCallCard_Numv . " / " . $CmpCallCard_Ngod . " / " . $jsonParams["CmpCallCard_insID"] . " arm:" . $armType . " proc:" . $procedure);
			$callObject->addLog("повтор проверки для CmpCallCard_id " . $resultforstatus[0]["CmpCallCard_id"]);
		}
		//повторная проверка на уникальность номеров карты
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.CmpCallCard_insDT as \"CmpCallCard_insDT\",
				CCC.Lpu_id as \"Lpu_id\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"CmpCallCard_prmDT\"
			from v_CmpCallCard CCC
			where CCC.CmpCallCard_id = :CmpCallCard_id;
		";
		$newcardresult = $callObject->db->query($query, ["CmpCallCard_id" => $resultforstatus[0]["CmpCallCard_id"]]);
		$newcard = $newcardresult->result("array");
        $newcard[0]["Day_num"] = isset($newcard[0]["CmpCallCard_Numv"]) ? $newcard[0]["CmpCallCard_Numv"] : null;
        $newcard[0]["Year_num"] = isset($newcard[0]["CmpCallCard_Ngod"]) ? $newcard[0]["CmpCallCard_Ngod"] : null;
        $newcard[0]["AcceptTime"] = isset($newcard[0]["CmpCallCard_prmDT"]) ? $newcard[0]["CmpCallCard_prmDT"] : null;
		$nums = $callObject->existenceNumbersDayYear($newcard[0]);
		if (
			is_array($nums) &&
			($nums["existenceNumbersDay"] || $nums["existenceNumbersYear"]) &&
			(!empty($nums["Double_insDT"]) && $newcard[0]["CmpCallCard_insDT"] > $nums["double_insDT"])
		) {
			$updateParams = [
				"CmpCallCard_id" => $resultforstatus[0]["CmpCallCard_id"],
				"CmpCallCard_Numv" => $nums["nextNumberDay"],
				"CmpCallCard_Ngod" => $nums["nextNumberYear"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->swUpdate("CmpCallCard", $updateParams, false);
			if ($dolog) {
				$callObject->addLog("ccc_m_2 smp обновление дубл.парам:" . $resultforstatus[0]["CmpCallCard_id"] . " / " . $nums["nextNumberDay"] . " / " . $nums["nextNumberYear"]);
			}
			// По задаче #137883 после смены номера на СМП, нужно обновить также на основном сервере
			if (!empty($cccConfig)) {
				//значит мы на основной БД main, нужно пересохранить и на СМП
				$IsMainServer = $callObject->config->item("IsMainServer");
				$IsSMPServer = $callObject->config->item("IsSMPServer");
				unset($callObject->db);
				try {
					if ($IsSMPServer) {
						$callObject->load->database();
					} else {
						$callObject->load->database('smp');
					}
				} catch (Exception $e) {
					$callObject->load->database();
					throw new Exception("Нет связи с сервером: создание нового вызова недоступно");
				}
				//сохраняем на СМП
				$callObject->swUpdate("CmpCallCard", $updateParams, false);
				if ($dolog) {
					$callObject->addLog("ccc_m_2 main обновление дубл.парам:" . $resultforstatus[0]["CmpCallCard_id"] . " / " . $nums["nextNumberDay"] . " / " . $nums["nextNumberYear"]);
				}
				unset($callObject->db);
				//возвращаемся на рабочую (она main на СМП сервере или default на основном
				if ($IsMainServer === true) {
					$callObject->load->database();
				} else {
					$callObject->load->database("main");
				}
			}
			$CmpCallCard_Numv = $nums["nextNumberDay"];
			$CmpCallCard_Ngod = $nums["nextNumberYear"];
		}
		$data["CmpCallCard_id"] = $resultforstatus[0]["CmpCallCard_id"];
		$callObject->checkCallStatusOnSave($data);
		$out = $result->result("array");
		$out["Person_id"] = !empty($data["Person_id"]) ? $data["Person_id"] : null;
		$out["CmpCallCard_Numv"] = !empty($CmpCallCard_Numv) ? $CmpCallCard_Numv : null;
		$out["CmpCallCard_Ngod"] = !empty($CmpCallCard_Ngod) ? $CmpCallCard_Ngod : null;
		$out["CmpCallCard_prmDT"] = $data["CmpCallCard_prmDT"];
		$callObject->commitTransaction();
		return $out;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveUnformalizedAddress(CmpCallCard_model $callObject, $data)
	{
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$procedure = ((!isset($data["UnformalizedAddressDirectory_id"])) || ($data["UnformalizedAddressDirectory_id"] <= 0)) ? "p_UnformalizedAddressDirectory_ins" : "p_UnformalizedAddressDirectory_upd";
		if ((!isset($data["UnformalizedAddressDirectory_id"])) || ($data["UnformalizedAddressDirectory_id"] <= 0)) {
			$query = "
				select count(*)
				from v_UnformalizedAddressDirectory UAD
				where UAD.UnformalizedAddressDirectory_Name = :UnformalizedAddressDirectory_Name
			";
			$result_count = $callObject->db->query(getCountSQLPH($query), $data);
			if (is_object($result_count)) {
				$cnt_arr = $result_count->result("array");
				$count = $cnt_arr[0]["cnt"];
				unset($cnt_arr);
			}
			if ($count && $count > 0) {
				throw new Exception("Неформализованный адрес с таким названием уже существует");
			}
		}
		$selectString = "
		    unformalizedaddressdirectory_id as \"UnformalizedAddressDirectory_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    unformalizedaddressdirectory_id := :UnformalizedAddressDirectory_id,
			    unformalizedaddressdirectory_dom := :UnformalizedAddressDirectory_Dom,
			    unformalizedaddressdirectory_name := :UnformalizedAddressDirectory_Name,
			    unformalizedaddressdirectory_lat := :UnformalizedAddressDirectory_lat,
			    unformalizedaddressdirectory_lng := :UnformalizedAddressDirectory_lng,
			    klrgn_id := :KLRgn_id,
			    klsubrgn_id := :KLSubRgn_id,
			    klcity_id := :KLCity_id,
			    kltown_id := :KLTown_id,
			    klstreet_id := :KLStreet_id,
			    lpu_id := :Lpu_id,
			    pmuser_id := :pmUser_id
			);
		";
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
	 */
	public static function saveCmpIllegalActForm(CmpCallCard_model $callObject, $data)
	{
		$procedure = (empty($data["CmpIllegalAct_id"])) ? "p_CmpIllegalAct_ins" : "p_CmpIllegalAct_upd";
		$exceptedFields = ["CmpIllegalAct_id"];
		$genQuery = $callObject->getParamsForSQLQuery($procedure, $data, $exceptedFields, false);
		$queryParams = $genQuery["paramsArray"];
		$queryFields = $genQuery["sqlParams"];
		$selectString = "
		    cmpillegalact_id as \"CmpIllegalAct_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    cmpillegalact_id := :CmpIllegalAct_id,
			    {$queryFields}
			);
		";
		if (empty($data["CmpIllegalAct_id"])) {
			$queryParams["CmpIllegalAct_id"] = null;
		}
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
	public static function saveSmpFarmacyDrug(CmpCallCard_model $callObject, $data)
	{
		$checkQuery = "
			select
				CFB.CmpFarmacyBalance_id,
				CFB.CmpFarmacyBalance_PackRest,
				CFB.CmpFarmacyBalance_DoseRest
			from v_CmpFarmacyBalance CFB
			where CFB.Drug_id = :Drug_id
			  and CFB.Lpu_id = :Lpu_id
		";
		/**
		 * @var CI_DB_result $checkResult
		 * @var CI_DB_result $CmpFarmacyResult
		 * @var CI_DB_result $CmpFarmacyAddHistory
		 */
		$checkResult = $callObject->db->query($checkQuery, $data);
		if (!is_object($checkResult)) {
			return false;
		}
		$checkResult = $checkResult->result("array");
		switch (count($checkResult)) {
			case 0:
				$procedure = "p_CmpFarmacyBalance_ins";
				$data["CmpFarmacyBalance_id"] = null;
				$data["CmpFarmacyBalance_PackRest"] = $data["CmpFarmacyBalanceAddHistory_RashCount"];
				$data["CmpFarmacyBalance_DoseRest"] = $data["CmpFarmacyBalanceAddHistory_RashEdCount"];
				break;
			case 1:
				$procedure = "p_CmpFarmacyBalance_upd";
				$data["CmpFarmacyBalance_id"] = $checkResult[0]["CmpFarmacyBalance_id"];
				$data["CmpFarmacyBalance_PackRest"] = $checkResult[0]["CmpFarmacyBalance_PackRest"] + $data["CmpFarmacyBalanceAddHistory_RashCount"];
				$data["CmpFarmacyBalance_DoseRest"] = $checkResult[0]["CmpFarmacyBalance_DoseRest"] + $data["CmpFarmacyBalanceAddHistory_RashEdCount"];
				break;
			default:
				return false;
				break;
		}
		$selectString = "
		    cmpfarmacybalance_id as \"CmpFarmacyBalance_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$CmpFarmacyQuery = "
			select {$selectString}
			from {$procedure}(
			    cmpfarmacybalance_id := :CmpFarmacyBalance_id,
			    lpu_id := :Lpu_id,
			    drug_id := :Drug_id,
			    cmpfarmacybalance_packrest := :CmpFarmacyBalance_PackRest,
			    cmpfarmacybalance_doserest := :CmpFarmacyBalance_DoseRest,
			    pmuser_id := :pmUser_id
			);
		";
		$CmpFarmacyResult = $callObject->db->query($CmpFarmacyQuery, $data);
		if (!is_object($CmpFarmacyResult)) {
			return false;
		}
		$CmpFarmacyResult = $CmpFarmacyResult->result("array");
		if (!empty($CmpFarmacyResult[0]["Error_Msg"])) {
			return false;
		}
		$data["CmpFarmacyBalance_id"] = $CmpFarmacyResult[0]["CmpFarmacyBalance_id"];
		$data["CmpFarmacyBalanceAddHistory_id"] = null;
		$CmpFarmacyAddHistoryQuery = "
			select
			    cmpfarmacybalanceaddhistory_id as \"CmpFarmacyBalanceAddHistory_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpfarmacybalanceaddhistory_ins(
			    cmpfarmacybalanceaddhistory_id := :CmpFarmacyBalanceAddHistory_id,
			    cmpfarmacybalanceaddhistory_dosecount := :CmpFarmacyBalanceAddHistory_RashEdCount,
			    cmpfarmacybalanceaddhistory_adddate := :CmpFarmacyBalanceAddHistory_AddDate,
			    cmpfarmacybalanceaddhistory_packcount := :CmpFarmacyBalanceAddHistory_RashCount,
			    cmpfarmacybalance_id := :CmpFarmacyBalance_id,
			    pmuser_id := :pmUser_id
			);
		";
		$CmpFarmacyAddHistory = $callObject->db->query($CmpFarmacyAddHistoryQuery, $data);
		if (!is_object($CmpFarmacyAddHistory)) {
			return false;
		}
		return $CmpFarmacyAddHistory->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @param null $cccConfig
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveCmpStreamCard(CmpCallCard_model $callObject, $data, $cccConfig = null)
	{
		//person инсертится при создании талона
		//собираем поля (которые расходятся по именам) из 110 в карту
		//слева - значение карты, справа 110
		$translateFromCloseCardToCallCardFields = [
			["Lpu_id_forUnicNumRequest", "Lpu_id"],
			["CmpCallCard_Numv", "Day_num"],
			["CmpCallCard_Ngod", "Year_num"],
			["CmpCallCard_City", "City_id"],
			["CmpCallCard_Ulic", "CmpCloseCard_Street"],
			["CmpCallCard_Dom", "House"],
			["CmpCallCard_Korp", "Korpus"],
			["CmpCallCard_Room", "Room"],
			["CmpCallCard_Kvar", "Office"],
			["CmpCallCard_Podz", "Entrance"],
			["CmpCallCard_Etaj", "Level"],
			["CmpCallCard_Kodp", "CodeEntrance"],
			["CmpCallCard_Telf", "Phone"],
			["CmpReason_id", "CallPovod_id"],
			["Person_SurName", "Fam"],
			["Person_FirName", "Name"],
			["Person_SecName", "Middle"],
			["Person_Age", "Age"],
			["Person_PolisSer", "Polis_Ser"],
			["Person_PolisNum", "Polis_Num"],
			["CmpCallCard_Ktov", "Ktov"],
			["CmpCallType_id", "CallType_id"],
			["KLRgn_id", "KLRgn_id"],
			["KLSubRgn_id", "Area_id"],
			["KLCity_id", "City_id"],
			["KLTown_id", "Town_id"],
			["KLStreet_id", "Street_id"],
			["MedStaffFact_id", "MedStaffFact_id", "MedStaffFact_uid"],
			["CmpCallCard_prmDT", "AcceptTime"],
			["Lpu_hid", "ComboValue_241"],
			["Person_BirthDay", "Person_BirthDay", null],
			["Diag_uid", "Diag_id"],
		];
		foreach ($translateFromCloseCardToCallCardFields as $fieldName) {
			if (isset($data[$fieldName[1]]) && !empty($data[$fieldName[1]])) {
				$data[$fieldName[0]] = $data[$fieldName[1]];
			} elseif (isset($fieldName[2]) && isset($data[$fieldName[2]]) && !empty($data[$fieldName[2]])) {
				$data[$fieldName[0]] = $data[$fieldName[2]];
			}
		};
		$acceptDate = DateTime::createFromFormat("d.m.Y H:i", $data["AcceptTime"]);
		$data["CmpCallCard_IsReceivedInPPD"] = (array_key_exists("CmpCallCard_IsReceivedInPPD", $data) && $data["CmpCallCard_IsReceivedInPPD"] == "on") ? 2 : 1;
		$data["CmpCallCard_prmDate"] = $acceptDate->format("Y-m-d");
		$data["CmpCallCard_prmTime"] = $acceptDate->format("H:i");
		$data["CmpCallCard_IsOpen"] = 2;
		$data["CmpCallCardStatusType_id"] = 6;
		$data["CmpCloseCard_Street"] = !empty($data["CmpCallCard_Ulic"]) ? $data["CmpCallCard_Ulic"] : null;
		if ((!isset($data["LpuBuilding_id"])) || (empty($data["LpuBuilding_id"]))) {
			$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
			$lpuBuilding = $callObject->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (!empty($lpuBuilding[0]) && !empty($lpuBuilding[0]["LpuBuilding_id"])) {
				$data["LpuBuilding_id"] = $lpuBuilding[0]["LpuBuilding_id"];
			}
		}
		$existenceNums = $callObject->existenceNumbersDayYear($data);
		if (is_array($existenceNums)) {
			// если нет такого номера, то позволим пользователю установить номер вызова введенный вручную
			$data["setDay_num"] = ($existenceNums["existenceNumbersDay"] == 1) ? $existenceNums["nextNumberDay"] : $data["Day_num"];
			$data["setYear_num"] = ($existenceNums["existenceNumbersYear"] == 1) ? $existenceNums["nextNumberYear"] : $data["Year_num"];
		}
		$callObject->beginTransaction();
		$data["action"] = "add";
		$resultCallCard = $callObject->saveCmpCallCard($data, $cccConfig);
		if (isset($resultCallCard) && $resultCallCard[0] && $resultCallCard[0]["CmpCallCard_id"] > 0) {
			$data["Person_id"] = !empty($data["Person_id"]) ? $data["Person_id"] : $resultCallCard["Person_id"];
			$result110 = $callObject->saveCmpCloseCard110(array_merge($data, ["CmpCallCard_id" => $resultCallCard[0]["CmpCallCard_id"]]));
			if (!$callObject->isSuccessful($result110)) {
				$callObject->rollbackTransaction();
				return false;
			}
			$callObject->commitTransaction();

			$result = array_merge($resultCallCard[0], $result110[0]);
			$result["Person_id"] = $resultCallCard["Person_id"];
			return [$result];
		}
		$callObject->rollbackTransaction();
		return false;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveCmpCloseCard110(CmpCallCard_model $callObject, $data)
	{
		//#162427 проверка на корректность вида оплаты и диагноза
		if (getRegionNick() == "ufa" && !$callObject->validDiagFinance($data)) {
			throw new Exception("Внимание! Введенный диагноз для данного случая не оплачивается по ОМС. Измените диагноз или вид оплаты.");
		}
		$dolog = (defined("DOLOGSAVECARD") && DOLOGSAVECARD === true) ? true : false;
		if ($dolog) {
			$callObject->load->library("textlog", array("file" => "saveCmpCallCardNumbers_" . date("Y-m-d") . ".log"));
		}
		$action = (!empty($data["action"])) ? $data["action"] : null;
		$oldresult = null;
		$NewCmpCloseCard_id = null;
		if (!empty($data["setDay_num"]) && !empty($data["setYear_num"])) {
			// значения setDay_num и setYear_num создаются при поточном вводе талонов вызова
			// для сохранения номера вызовов за день и год одинаковыми
			$data["Day_num"] = $data["setDay_num"];
			$data["Year_num"] = $data["setYear_num"];
		} else {
			//существуют ли номера вызовов за день и за год
			$existenceNums = $callObject->existenceNumbersDayYear($data);
			if (is_array($existenceNums)) {
				// если нет такого номера, то позволим пользователю установить номер вызова введенный вручную
				$data["Day_num"] = ($existenceNums["existenceNumbersDay"]) ? $existenceNums["nextNumberDay"] : $data["Day_num"];
				$data["Year_num"] = ($existenceNums["existenceNumbersYear"]) ? $existenceNums["nextNumberYear"] : $data["Year_num"];
			}
		}
		if (!empty($data["PayType_Code"])) {
			$payTypesql = "select PayType_id as \"PayType_id\" from v_PayType where PayType_Code = :PayType_Code";
			$payTypeId = $callObject->getFirstResultFromQuery($payTypesql, ["PayType_Code" => $data["PayType_Code"]]);
			if (!empty($payTypeId)) {
				$data["PayType_id"] = $payTypeId;
			}
		}
		if (isset($data["CmpCloseCard_id"]) && $data["CmpCloseCard_id"] && $action != "add") {
			$action = "edit";
			$procedure = "{$callObject->schema}.p_CmpCloseCard_upd";
			$relProcedure = "{$callObject->schema}.p_CmpCloseCardRel_ins";
		} else {
			$selectString = "CLC.CmpCloseCard_id as as \"CmpCloseCard_id\"";
			$fromString = "{$callObject->schema}.v_CmpCloseCard CLC";
			$whereString = "CLC.CmpCallCard_id = :CmpCallCard_id";
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
			";
			$result = $callObject->db->query($query, ["CmpCallCard_id" => $data["CmpCallCard_id"]]);
			$return = $result->result("array");
			if (sizeof($return) && !empty($return[0]) && !empty($return[0]["CmpCloseCard_id"])) {
				$data["CmpCloseCard_id"] = $return[0]["CmpCloseCard_id"];
				$action = "edit";
				$procedure = "{$callObject->schema}.p_CmpCloseCard_upd";
				$relProcedure = "{$callObject->schema}.p_CmpCloseCardRel_ins";
			} else {
				$action = "add";
				$procedure = "{$callObject->schema}.p_CmpCloseCard_ins";
				$relProcedure = "{$callObject->schema}.p_CmpCloseCardRel_ins";
			}
		}
		$UnicNums = ";";
		if (isset($data["CmpCloseCard_prmTime"])) {
			$data["CmpCloseCard_prmDate"] .= " " . $data["CmpCloseCard_prmTime"] . ":00.000";
		}
		if (empty($data["MedPersonal_id"]) && !empty($data["MedStaffFact_id"])) {
			$query = "
				select MedPersonal_id as \"MedPersonal_id\"
				from v_MedStaffFact
				where MedStaffFact_id = :MedStaffFact_id
				limit 1
			";
			$queryParams = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
			$data["MedPersonal_id"] = $callObject->getFirstResultFromQuery($query, $queryParams);
			if (!$data["MedPersonal_id"]) {
				throw new Exception("Ошибка при определении врача");
			}
		} elseif ($callObject->regionNick == "ufa" && empty($data["MedPersonal_id"]) && empty($data["MedStaffFact_id"]) && $data["EmergencyTeam_id"]) {
			$query = "
				select
					coalesce(msf.MedStaffFact_id, null) as \"MedStaffFact_id\",
					msf.MedPersonal_id
				from
					v_EmergencyTeam EMT 
					left join v_MedStaffFact msf on (msf.MedStaffFact_id = EMT.EmergencyTeam_HeadShiftWorkPlace)
				where EMT.EmergencyTeam_id = :EmergencyTeam_id
				limit 1
			";
			$resStaffPerson = $callObject->getFirstRowFromQuery($query, ["EmergencyTeam_id" => $data["EmergencyTeam_id"]], true);
			if ($resStaffPerson["MedStaffFact_id"]) {
				$data["MedStaffFact_id"] = $resStaffPerson["MedStaffFact_id"];
			}
			if ($resStaffPerson["MedPersonal_id"]) {
				$data["MedPersonal_id"] = $resStaffPerson["MedPersonal_id"];
			}
		}
		if ($callObject->regionNick == "ufa" && !empty($data["MedStaffFact_id"]) && !empty($data["EmergencyTeam_id"])) {
			//Проверка совпадения МО вызова и МО врача
			$query = "
				select MF.Lpu_id as \"Lpu_id\"
				from v_MedStaffFact MF
				where MedStaffFact_id = :MedStaffFact_id
			";
			$resStaff = $callObject->getFirstRowFromQuery($query, ["MedStaffFact_id" => $data["MedStaffFact_id"]], true);
			if (!empty($resStaff["Lpu_id"]) && $resStaff["Lpu_id"] != $data["Lpu_id"]) {
				$query = "
					select ET.EmergencyTeam_HeadShiftWorkPlace as \"EmergencyTeam_HeadShiftWorkPlace\"
					from v_EmergencyTeam ET
					where EmergencyTeam_id = :EmergencyTeam_id
				";
				$resStaff = $callObject->getFirstRowFromQuery($query, ["EmergencyTeam_id" => $data["EmergencyTeam_id"]], true);
				if ($dolog) {
					$callObject->addLog("change MedStaffFact_id old = " . $data["MedStaffFact_id"] . " new = " . $resStaff["EmergencyTeam_HeadShiftWorkPlace"] . "/ET=" . $data["EmergencyTeam_id"]);
				}
				$data["MedStaffFact_id"] = $resStaff["EmergencyTeam_HeadShiftWorkPlace"];
			}
		}
		//переделка автоматического сбора полей с таблицы
		$jsonParams = $callObject->getJsonParamsForTable($data, $callObject->schema, 'CmpCloseCard');
		
		foreach ($jsonParams as $key => $value) {
			if (!empty($value) && in_array($key, ["AcceptTime", "ArriveTime", "BackTime", "EndTime", "GoTime", "ToHospitalTime", "TransTime", "TransportTime", "CmpCloseCard_TranspEndDT", "Birthday", "NextTime", "ServiceDT", "Bad_DT", "Mensis_DT", "CmpCloseCard_PassTime"])) {
				$jsonParams[$key] = date("Y-m-d H:i:s", strtotime($value));
			}
			if (!empty($value) && in_array($key, array("Diag_id"))) {
				if (!preg_match('/^[0-9]+$/ui', $value)) {
					throw new Exception("Неверный идентификатор в поле Diag_id");
				}
			}
		}
		
		$cccQueryParams = [
			'CmpCloseCard_id' => !empty($data['CmpCloseCard_id'])?$data['CmpCloseCard_id']:null,
			'params' => json_encode(array_change_key_case($jsonParams, CASE_LOWER)),
			'pmUser_id' => $data['pmUser_id'],
		];
		
		if ($callObject->regionNick == "kz") {
            $query = "
                SELECT 
                    CmpCloseCard_id as \"CmpCloseCard_id\",
                    error_code as \"Error_Code\",
                    error_message as \"Error_Msg\"
                FROM {$procedure} (
                    CmpCloseCard_id := :CmpCloseCard_id,
                    params := :params,
                    pmUser_id := :pmUser_id
                )
            ";
		} else {
			$query = "
			    SELECT 
			         CmpCloseCard_id as \"CmpCloseCard_id\",
			         CmpCloseCard_GUID as \"CmpCloseCard_GUID\",
			         error_code as \"Error_Code\",
			         error_message as \"Error_Msg\"
			    FROM {$procedure} (
			        CmpCloseCard_id := :CmpCloseCard_id,
                    params := :params,
                    pmUser_id := :pmUser_id
			    )
			";
		}
		if ($action == "edit") {
			$NewCmpCloseCard_id = null;
			/* Делаем копию исходной записи, а измененную копию сохраняем на место старой */
			/* 1 - выбираем старую запись */
			$fromString = "{$callObject->schema}.v_CmpCloseCard CLC";
			$whereString = "CLC.CmpCloseCard_id = {$data["CmpCloseCard_id"]}";
			$squery = "
				select 
					CmpCloseCard_id as \"CmpCloseCard_id\",
					CmpCallCard_id as \"CmpCallCard_id\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					CmpCloseCard_insDT as \"CmpCloseCard_insDT\",
					CmpCloseCard_updDT as \"CmpCloseCard_updDT\",
					Feldsher_id as \"Feldsher_id\",
					StationNum as \"StationNum\",
					EmergencyTeamNum as \"EmergencyTeamNum\",
					AcceptTime as \"AcceptTime\",
					TransTime as \"TransTime\",
					GoTime as \"GoTime\",
					ArriveTime as \"ArriveTime\",
					TransportTime as \"TransportTime\",
					ToHospitalTime as \"ToHospitalTime\",
					EndTime as \"EndTime\",
					BackTime as \"BackTime\",
					SummTime as \"SummTime\",
					Area_id as \"Area_id\",
					Town_id as \"Town_id\",
					City_id as \"City_id\",
					Street_id as \"Street_id\",
					House as \"House\",
					Office as \"Office\",
					Entrance as \"Entrance\",
					CodeEntrance as \"CodeEntrance\",
					Level as \"Level\",
					Ktov as \"Ktov\",
					Phone as \"Phone\",
					Fam as \"Fam\",
					Name as \"Name\",
					Middle as \"Middle\",
					Age as \"Age\",
					Sex_id as \"Sex_id\",
					Work as \"Work\",
					DocumentNum as \"DocumentNum\",
					FeldsherAccept as \"FeldsherAccept\",
					FeldsherTrans as \"FeldsherTrans\",
					CallPovod_id as \"CallPovod_id\",
					CallType_id as \"CallType_id\",
					isAlco as \"isAlco\",
					Complaints as \"Complaints\",
					Anamnez as \"Anamnez\",
					isMenen as \"isMenen\",
					isAnis as \"isAnis\",
					isNist as \"isNist\",
					isLight as \"isLight\",
					isAcro as \"isAcro\",
					isMramor as \"isMramor\",
					isHale as \"isHale\",
					isPerit as \"isPerit\",
					Urine as \"Urine\",
					Shit as \"Shit\",
					OtherSympt as \"OtherSympt\",
					WorkAD as \"WorkAD\",
					AD as \"AD\",
					Pulse as \"Pulse\",
					Chss as \"Chss\",
					Chd as \"Chd\",
					Temperature as \"Temperature\",
					Pulsks as \"Pulsks\",
					Gluck as \"Gluck\",
					LocalStatus as \"LocalStatus\",
					Ekg1Time as \"Ekg1Time\",
					Ekg1 as \"Ekg1\",
					Ekg2Time as \"Ekg2Time\",
					Ekg2 as \"Ekg2\",
					Diag_id as \"Diag_id\",
					EfAD as \"EfAD\",
					EfChss as \"EfChss\",
					EfPulse as \"EfPulse\",
					EfTemperature as \"EfTemperature\",
					EfChd as \"EfChd\",
					EfPulsks as \"EfPulsks\",
					EfGluck as \"EfGluck\",
					Kilo as \"Kilo\",
					Lpu_id as \"Lpu_id\",
					HelpPlace as \"HelpPlace\",
					HelpAuto as \"HelpAuto\",
					DescText as \"DescText\",
					CmpCloseCard_IsInReg as \"CmpCloseCard_IsInReg\",
					Korpus as \"Korpus\",
					Room as \"Room\",
					CmpCloseCard_firstVersion as \"CmpCloseCard_firstVersion\",
					CmpCloseCard_IsPaid as \"CmpCloseCard_IsPaid\",
					Day_num as \"Day_num\",
					Year_num as \"Year_num\",
					CallPovodNew_id as \"CallPovodNew_id\",
					EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					MedPersonal_id as \"MedPersonal_id\",
					SocStatus_id as \"SocStatus_id\",
					isSogl as \"isSogl\",
					isOtkazMed as \"isOtkazMed\",
					isOtkazHosp as \"isOtkazHosp\",
					LpuSection_id as \"LpuSection_id\",
					CmpCloseCard_IndexRep as \"CmpCloseCard_IndexRep\",
					CmpCloseCard_IndexRepInReg as \"CmpCloseCard_IndexRepInReg\",
					CmpCloseCard_IsArchive as \"CmpCloseCard_IsArchive\",
					PayType_id as \"PayType_id\",
					Lpu_CodeSMO as \"Lpu_CodeSMO\",
					CmpCloseCardCause_id as \"CmpCloseCardCause_id\",
					CmpCloseCardWhereReported_id as \"CmpCloseCardWhereReported_id\",
					CmpCloseCardWhere_DT as \"CmpCloseCardWhere_DT\",
					MessageNum as \"MessageNum\",
					AcceptFio as \"AcceptFio\",
					MedStaffFact_id as \"MedStaffFact_id\",
					RankinScale_id as \"RankinScale_id\",
					RankinScale_sid as \"RankinScale_sid\",
					isOtkazSign as \"isOtkazSign\",
					OtkazSignWhy as \"OtkazSignWhy\",
					DisStart as \"DisStart\",
					CmpCallerType_id as \"CmpCallerType_id\",
					LpuBuilding_id as \"LpuBuilding_id\",
					EmergencyTeam_id as \"EmergencyTeam_id\",
					Person_id as \"Person_id\",
					Diag_uid as \"Diag_uid\",
					CmpCloseCard_Epid as \"CmpCloseCard_Epid\",
					CmpCloseCard_Glaz as \"CmpCloseCard_Glaz\",
					CmpCloseCard_e1 as \"CmpCloseCard_e1\",
					CmpCloseCard_m1 as \"CmpCloseCard_m1\",
					CmpCloseCard_v1 as \"CmpCloseCard_v1\",
					CmpCloseCard_e2 as \"CmpCloseCard_e2\",
					CmpCloseCard_m2 as \"CmpCloseCard_m2\",
					CmpCloseCard_v2 as \"CmpCloseCard_v2\",
					CmpCloseCard_Topic as \"CmpCloseCard_Topic\",
					Diag_sid as \"Diag_sid\",
					CmpCloseCard_GlazAfter as \"CmpCloseCard_GlazAfter\",
					CmpCloseCard_TranspEndDT as \"CmpCloseCard_TranspEndDT\",
					CmpCloseCard_Street as \"CmpCloseCard_Street\",
					CmpCloseCard_IsExtra as \"CmpCloseCard_IsExtra\",
					CmpCloseCard_IsProfile as \"CmpCloseCard_IsProfile\",
					CmpCloseCard_IsNMP as \"CmpCloseCard_IsNMP\",
					CmpCloseCard_AcceptBrigadeDT as \"CmpCloseCard_AcceptBrigadeDT\",
					CmpCloseCard_IsIntestinal as \"CmpCloseCard_IsIntestinal\",
					CmpCloseCard_IsHeartNoise as \"CmpCloseCard_IsHeartNoise\",
					CmpCloseCard_Sat as \"CmpCloseCard_Sat\",
					CmpCloseCard_AfterSat as \"CmpCloseCard_AfterSat\",
					CmpCloseCard_Rhythm as \"CmpCloseCard_Rhythm\",
					CmpCloseCard_AfterRhythm as \"CmpCloseCard_AfterRhythm\",
					CmpCloseCard_IsRattle as \"CmpCloseCard_IsRattle\",
					CmpCloseCard_IsVomit as \"CmpCloseCard_IsVomit\",
					CmpCloseCard_IsDiuresis as \"CmpCloseCard_IsDiuresis\",
					CmpCloseCard_IsDefecation as \"CmpCloseCard_IsDefecation\",
					CmpCloseCard_IsTrauma as \"CmpCloseCard_IsTrauma\",
					CmpCloseCard_BegTreatDT as \"CmpCloseCard_BegTreatDT\",
					CmpCloseCard_EndTreatDT as \"CmpCloseCard_EndTreatDT\",
					Org_mid as \"Org_mid\",
					CmpCloseCard_HelpDT as \"CmpCloseCard_HelpDT\",
					CmpCloseCard_LethalDT as \"CmpCloseCard_LethalDT\",
					CmpLethalType_id as \"CmpLethalType_id\",
					CmpCloseCard_AddInfo as \"CmpCloseCard_AddInfo\",
					MedStaffFact_cid as \"MedStaffFact_cid\",
					CmpCloseCard_ClinicalEff as \"CmpCloseCard_ClinicalEff\",
					CmpCloseCard_MenenAddiction as \"CmpCloseCard_MenenAddiction\",
					CmpCloseCard_GUID as \"CmpCloseCard_GUID\",
					CmpCloseCard_firstVersionGUID as \"CmpCloseCard_firstVersionGUID\",
					EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
					CmpCallCard_GUID as \"CmpCallCard_GUID\",
					Person_PolisSer as \"Person_PolisSer\",
					Person_PolisNum as \"Person_PolisNum\",
					CmpCloseCard_PolisEdNum as \"CmpCloseCard_PolisEdNum\",
					CmpResult_id as \"CmpResult_id\",
					UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
					CmpCloseCard_Ulic as \"CmpCloseCard_Ulic\",
					CmpCloseCard_IsSignList as \"CmpCloseCard_IsSignList\",
					LeaveType_id as \"LeaveType_id\",
					CmpCloseCard_UserKilo as \"CmpCloseCard_UserKilo\",
					Person_Snils as \"Person_Snils\",
					CmpCloseCard_PassTime as \"CmpCloseCard_PassTime\",
					CmpCloseCard_CallBackTime as \"CmpCloseCard_CallBackTime\",
					CmpCloseCard_DopInfo as \"CmpCloseCard_DopInfo\",
					CmpCloseCard_UlicSecond as \"CmpCloseCard_UlicSecond\",
					CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
					Alerg as \"Alerg\",
					Epid as \"Epid\",
					isVac as \"isVac\",
					isKupir as \"isKupir\",
					Zev as \"Zev\",
					Perk as \"Perk\",
					CmpCloseCard_DayNumPr as \"CmpCloseCard_DayNumPr\",
					CmpCloseCard_YearNumPr as \"CmpCloseCard_YearNumPr\",
					CmpCallSignType_id as \"CmpCallSignType_id\",
					CmpCloseCard_Comm as \"CmpCloseCard_Comm\",
					Diag_add as \"Diag_add\",
					CmpCloseCard_StatusLocalis as \"CmpCloseCard_StatusLocalis\",
					Bad_DT as \"Bad_DT\",
					Mensis_DT as \"Mensis_DT\",
					CmpCloseCard_IsInRegZNO as \"CmpCloseCard_IsInRegZNO\",
					Registry_sid as \"Registry_sid\",
					CmpCloseCard_UserKiloCommon as \"CmpCloseCard_UserKiloCommon\"
				from {$fromString}
				where {$whereString}
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($squery, $data);
			if (!is_object($result)) {
				return false;
			}
			$oldresult = $result->result("array");
			$oldresult = $oldresult[0];
			/* 2 - сохраняем страую запись в новую */
			$oldresult = array_merge($data, $oldresult);
			
			$oldJsonParams = $callObject->getJsonParamsForTable($oldresult, $callObject->schema, 'CmpCloseCard');

			$squeryParams = [
				'params' => json_encode(array_change_key_case($oldJsonParams, CASE_LOWER)),
				'pmUser_id' => $data['pmUser_id'],
			];

			$squery = "
			    SELECT 
			        cmpclosecard_id as \"CmpCloseCard_id\",
			        error_code as \"Error_Code\", 
			        error_message as \"Error_Msg\"
			    FROM {$callObject->schema}.p_CmpCloseCard_ins (
			        params := :params,
			        pmUser_id := :pmUser_id
			    )
			";
            $result = $callObject->queryResult($squery, $squeryParams);
			if (!$callObject->isSuccessful($result)) {
			    return $result;
            }
            $squeryParams['CmpCloseCard_id'] = $result[0]["CmpCloseCard_id"];
			$squery = "
			    SELECT 
			        :CmpCloseCard_id as \"CmpCloseCard_id\",
			        error_code as \"Error_Code\", 
			        error_message as \"Error_Msg\"
			    FROM {$callObject->schema}.p_CmpCloseCard_del (
			        CmpCloseCard_id := :CmpCloseCard_id,
			        pmUser_id := :pmUser_id
			    )
			";
            $result = $callObject->queryResult($squery, $squeryParams);
            if (!$callObject->isSuccessful($result)) {
                return $result;
            }
            $squery = "
			    SELECT 
			        :CmpCloseCard_id as \"CmpCloseCard_id\",
			        error_code as \"Error_Code\", 
			        error_message as \"Error_Msg\"
			    FROM {$callObject->schema}.p_CmpCloseCard_setFirstVersion (
			        CmpCloseCard_id := :CmpCloseCard_id,
			        pmUser_id := :pmUser_id,
			        CmpCloseCard_firstVersion := " . $oldresult['CmpCloseCard_id'] . "
			    )
			";
            $result = $callObject->queryResult($squery, $squeryParams);
            if (!$callObject->isSuccessful($result)) {
                return $result;
            }
			$NewCmpCloseCard_id = $result[0]["CmpCloseCard_id"];
			// 3 - заменяем старую запись текущими изменениями*/
			if ((!isset($newParams["CmpCloseCard_id"])) || ($newParams["CmpCloseCard_id"] == null)) {
				$jsonParams["CmpCallCard_id"] = $oldresult["CmpCallCard_id"];
			}
			
			$newParams = [
				'CmpCloseCard_id' => $oldresult["CmpCloseCard_id"],
				'CmpCloseCard_GUID' => $oldresult["CmpCloseCard_GUID"],
				'params' => json_encode(array_change_key_case($jsonParams, CASE_LOWER)),
				'pmUser_id' => $data['pmUser_id'],
			];

			$squery = "
			    SELECT 
			        cmpclosecard_id as \"CmpCloseCard_id\", 
			        :CmpCloseCard_GUID as \"CmpCloseCard_GUID\",
			        error_code as \"Error_Code\", 
			        error_message as \"Error_Msg\"
			    FROM {$callObject->schema}.p_CmpCloseCard_upd (
			        CmpCloseCard_id := :CmpCloseCard_id,
			        params := :params,
			        pmUser_id := :pmUser_id
			    );
			";
			$result = $callObject->db->query($squery, $newParams);
			$resArray = $result->result("array");
			//Проверим на дубли карты вызова
			$fromString = "{$callObject->schema}.v_CmpCloseCard CLC";
			$whereString = "
					CLC.CmpCallCard_id = :CmpCallCard_id
				and CLC.CmpCloseCard_id != :CmpCloseCard_id
			";
			$dupquery = "
				select 
					CmpCloseCard_id as \"CmpCloseCard_id\",
					CmpCallCard_id as \"CmpCallCard_id\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					CmpCloseCard_insDT as \"CmpCloseCard_insDT\",
					CmpCloseCard_updDT as \"CmpCloseCard_updDT\",
					Feldsher_id as \"Feldsher_id\",
					StationNum as \"StationNum\",
					EmergencyTeamNum as \"EmergencyTeamNum\",
					AcceptTime as \"AcceptTime\",
					TransTime as \"TransTime\",
					GoTime as \"GoTime\",
					ArriveTime as \"ArriveTime\",
					TransportTime as \"TransportTime\",
					ToHospitalTime as \"ToHospitalTime\",
					EndTime as \"EndTime\",
					BackTime as \"BackTime\",
					SummTime as \"SummTime\",
					Area_id as \"Area_id\",
					Town_id as \"Town_id\",
					City_id as \"City_id\",
					Street_id as \"Street_id\",
					House as \"House\",
					Office as \"Office\",
					Entrance as \"Entrance\",
					CodeEntrance as \"CodeEntrance\",
					Level as \"Level\",
					Ktov as \"Ktov\",
					Phone as \"Phone\",
					Fam as \"Fam\",
					Name as \"Name\",
					Middle as \"Middle\",
					Age as \"Age\",
					Sex_id as \"Sex_id\",
					Work as \"Work\",
					DocumentNum as \"DocumentNum\",
					FeldsherAccept as \"FeldsherAccept\",
					FeldsherTrans as \"FeldsherTrans\",
					CallPovod_id as \"CallPovod_id\",
					CallType_id as \"CallType_id\",
					isAlco as \"isAlco\",
					Complaints as \"Complaints\",
					Anamnez as \"Anamnez\",
					isMenen as \"isMenen\",
					isAnis as \"isAnis\",
					isNist as \"isNist\",
					isLight as \"isLight\",
					isAcro as \"isAcro\",
					isMramor as \"isMramor\",
					isHale as \"isHale\",
					isPerit as \"isPerit\",
					Urine as \"Urine\",
					Shit as \"Shit\",
					OtherSympt as \"OtherSympt\",
					WorkAD as \"WorkAD\",
					AD as \"AD\",
					Pulse as \"Pulse\",
					Chss as \"Chss\",
					Chd as \"Chd\",
					Temperature as \"Temperature\",
					Pulsks as \"Pulsks\",
					Gluck as \"Gluck\",
					LocalStatus as \"LocalStatus\",
					Ekg1Time as \"Ekg1Time\",
					Ekg1 as \"Ekg1\",
					Ekg2Time as \"Ekg2Time\",
					Ekg2 as \"Ekg2\",
					Diag_id as \"Diag_id\",
					EfAD as \"EfAD\",
					EfChss as \"EfChss\",
					EfPulse as \"EfPulse\",
					EfTemperature as \"EfTemperature\",
					EfChd as \"EfChd\",
					EfPulsks as \"EfPulsks\",
					EfGluck as \"EfGluck\",
					Kilo as \"Kilo\",
					Lpu_id as \"Lpu_id\",
					HelpPlace as \"HelpPlace\",
					HelpAuto as \"HelpAuto\",
					DescText as \"DescText\",
					CmpCloseCard_IsInReg as \"CmpCloseCard_IsInReg\",
					Korpus as \"Korpus\",
					Room as \"Room\",
					CmpCloseCard_firstVersion as \"CmpCloseCard_firstVersion\",
					CmpCloseCard_IsPaid as \"CmpCloseCard_IsPaid\",
					Day_num as \"Day_num\",
					Year_num as \"Year_num\",
					CallPovodNew_id as \"CallPovodNew_id\",
					EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					MedPersonal_id as \"MedPersonal_id\",
					SocStatus_id as \"SocStatus_id\",
					isSogl as \"isSogl\",
					isOtkazMed as \"isOtkazMed\",
					isOtkazHosp as \"isOtkazHosp\",
					LpuSection_id as \"LpuSection_id\",
					CmpCloseCard_IndexRep as \"CmpCloseCard_IndexRep\",
					CmpCloseCard_IndexRepInReg as \"CmpCloseCard_IndexRepInReg\",
					CmpCloseCard_IsArchive as \"CmpCloseCard_IsArchive\",
					PayType_id as \"PayType_id\",
					Lpu_CodeSMO as \"Lpu_CodeSMO\",
					CmpCloseCardCause_id as \"CmpCloseCardCause_id\",
					CmpCloseCardWhereReported_id as \"CmpCloseCardWhereReported_id\",
					CmpCloseCardWhere_DT as \"CmpCloseCardWhere_DT\",
					MessageNum as \"MessageNum\",
					AcceptFio as \"AcceptFio\",
					MedStaffFact_id as \"MedStaffFact_id\",
					RankinScale_id as \"RankinScale_id\",
					RankinScale_sid as \"RankinScale_sid\",
					isOtkazSign as \"isOtkazSign\",
					OtkazSignWhy as \"OtkazSignWhy\",
					DisStart as \"DisStart\",
					CmpCallerType_id as \"CmpCallerType_id\",
					LpuBuilding_id as \"LpuBuilding_id\",
					EmergencyTeam_id as \"EmergencyTeam_id\",
					Person_id as \"Person_id\",
					Diag_uid as \"Diag_uid\",
					CmpCloseCard_Epid as \"CmpCloseCard_Epid\",
					CmpCloseCard_Glaz as \"CmpCloseCard_Glaz\",
					CmpCloseCard_e1 as \"CmpCloseCard_e1\",
					CmpCloseCard_m1 as \"CmpCloseCard_m1\",
					CmpCloseCard_v1 as \"CmpCloseCard_v1\",
					CmpCloseCard_e2 as \"CmpCloseCard_e2\",
					CmpCloseCard_m2 as \"CmpCloseCard_m2\",
					CmpCloseCard_v2 as \"CmpCloseCard_v2\",
					CmpCloseCard_Topic as \"CmpCloseCard_Topic\",
					Diag_sid as \"Diag_sid\",
					CmpCloseCard_GlazAfter as \"CmpCloseCard_GlazAfter\",
					CmpCloseCard_TranspEndDT as \"CmpCloseCard_TranspEndDT\",
					CmpCloseCard_Street as \"CmpCloseCard_Street\",
					CmpCloseCard_IsExtra as \"CmpCloseCard_IsExtra\",
					CmpCloseCard_IsProfile as \"CmpCloseCard_IsProfile\",
					CmpCloseCard_IsNMP as \"CmpCloseCard_IsNMP\",
					CmpCloseCard_AcceptBrigadeDT as \"CmpCloseCard_AcceptBrigadeDT\",
					CmpCloseCard_IsIntestinal as \"CmpCloseCard_IsIntestinal\",
					CmpCloseCard_IsHeartNoise as \"CmpCloseCard_IsHeartNoise\",
					CmpCloseCard_Sat as \"CmpCloseCard_Sat\",
					CmpCloseCard_AfterSat as \"CmpCloseCard_AfterSat\",
					CmpCloseCard_Rhythm as \"CmpCloseCard_Rhythm\",
					CmpCloseCard_AfterRhythm as \"CmpCloseCard_AfterRhythm\",
					CmpCloseCard_IsRattle as \"CmpCloseCard_IsRattle\",
					CmpCloseCard_IsVomit as \"CmpCloseCard_IsVomit\",
					CmpCloseCard_IsDiuresis as \"CmpCloseCard_IsDiuresis\",
					CmpCloseCard_IsDefecation as \"CmpCloseCard_IsDefecation\",
					CmpCloseCard_IsTrauma as \"CmpCloseCard_IsTrauma\",
					CmpCloseCard_BegTreatDT as \"CmpCloseCard_BegTreatDT\",
					CmpCloseCard_EndTreatDT as \"CmpCloseCard_EndTreatDT\",
					Org_mid as \"Org_mid\",
					CmpCloseCard_HelpDT as \"CmpCloseCard_HelpDT\",
					CmpCloseCard_LethalDT as \"CmpCloseCard_LethalDT\",
					CmpLethalType_id as \"CmpLethalType_id\",
					CmpCloseCard_AddInfo as \"CmpCloseCard_AddInfo\",
					MedStaffFact_cid as \"MedStaffFact_cid\",
					CmpCloseCard_ClinicalEff as \"CmpCloseCard_ClinicalEff\",
					CmpCloseCard_MenenAddiction as \"CmpCloseCard_MenenAddiction\",
					CmpCloseCard_GUID as \"CmpCloseCard_GUID\",
					CmpCloseCard_firstVersionGUID as \"CmpCloseCard_firstVersionGUID\",
					EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
					CmpCallCard_GUID as \"CmpCallCard_GUID\",
					Person_PolisSer as \"Person_PolisSer\",
					Person_PolisNum as \"Person_PolisNum\",
					CmpCloseCard_PolisEdNum as \"CmpCloseCard_PolisEdNum\",
					CmpResult_id as \"CmpResult_id\",
					UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
					CmpCloseCard_Ulic as \"CmpCloseCard_Ulic\",
					CmpCloseCard_IsSignList as \"CmpCloseCard_IsSignList\",
					LeaveType_id as \"LeaveType_id\",
					CmpCloseCard_UserKilo as \"CmpCloseCard_UserKilo\",
					Person_Snils as \"Person_Snils\",
					CmpCloseCard_PassTime as \"CmpCloseCard_PassTime\",
					CmpCloseCard_CallBackTime as \"CmpCloseCard_CallBackTime\",
					CmpCloseCard_DopInfo as \"CmpCloseCard_DopInfo\",
					CmpCloseCard_UlicSecond as \"CmpCloseCard_UlicSecond\",
					CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
					Alerg as \"Alerg\",
					Epid as \"Epid\",
					isVac as \"isVac\",
					isKupir as \"isKupir\",
					Zev as \"Zev\",
					Perk as \"Perk\",
					CmpCloseCard_DayNumPr as \"CmpCloseCard_DayNumPr\",
					CmpCloseCard_YearNumPr as \"CmpCloseCard_YearNumPr\",
					CmpCallSignType_id as \"CmpCallSignType_id\",
					CmpCloseCard_Comm as \"CmpCloseCard_Comm\",
					Diag_add as \"Diag_add\",
					CmpCloseCard_StatusLocalis as \"CmpCloseCard_StatusLocalis\",
					Bad_DT as \"Bad_DT\",
					Mensis_DT as \"Mensis_DT\",
					CmpCloseCard_IsInRegZNO as \"CmpCloseCard_IsInRegZNO\",
					Registry_sid as \"Registry_sid\",
					CmpCloseCard_UserKiloCommon as \"CmpCloseCard_UserKiloCommon\"
				from {$fromString}
				where {$whereString}
			";
			$queryParams = [
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"CmpCloseCard_id" => $newParams["CmpCloseCard_id"]
			];
			$dupresult = $callObject->db->query($dupquery, $queryParams);
			$dupArray = $dupresult->result("array");
			if (count($dupArray) > 0) {
				foreach ($dupArray as $dup) {
					$callObject->deleteCmpCallCard([
						"CmpCallCard_id" => $dup["CmpCallCard_id"],
						"CmpCloseCard_id" => $dup["CmpCloseCard_id"],
						"Lpu_id" => $dup["Lpu_id"],
						"pmUser_id" => $data["pmUser_id"]
					], false, false);
				}
			}
			// сохраним номера вызова за год и день в CmpCallCard, чтоб совпадали
			if (!empty($data["Day_num"]) && !empty($data["Year_num"])) {
				$numYearDayQuery = "
					select *
					from p_cmpcallcard_setngodnumv(
					    cmpcallcard_id := {$data["CmpCallCard_id"]},
					    cmpcallcard_numv := {$data["Day_num"]},
					    cmpcallcard_ngod := {$data["Year_num"]},
					    pmuser_id := {$data["pmUser_id"]}
					);
				";
				$callObject->db->query($numYearDayQuery);
			}
		} else {
			// add
			$result = $callObject->db->query($query, $cccQueryParams);
			$resArray = $result->result("array");
		}
		if (isset($data["DocumentUc_id"]) && $data["DocumentUc_id"]) {
			// Связь документа списания медикаментов на пациента и талона закрытия вызова
			$callObject->saveCmpCloseCardDocumentUcRel(array_merge($data, ["CmpCloseCard_id" => $resArray[0]["CmpCloseCard_id"]]));
		}
		if (isset($data["CmpEquipment"]) && $data["CmpEquipment"]) {
			// Использованное оборудование
			$callObject->saveCmpCloseCardEquipmentRel(array_merge($data, ["CmpCloseCard_id" => $resArray[0]["CmpCloseCard_id"]]));
		}
		if (!empty($data["CmpCallCard_id"])) {
			$pars = [
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			if (!empty($data["AcceptTime"])) {
				$pars["CmpCallCard_prmDT"] = ConvertDateTimeFormat($data["AcceptTime"] . ":00");
				if ($dolog) {
					$callObject->addLog("update CmpCallCard_id " . $data["CmpCallCard_id"] . " CmpCallCard_prmDT " . $pars["CmpCallCard_prmDT"]);
				}
			}
			if (!empty($data["TransTime"])) {
				$pars["CmpCallCard_Tper"] = ConvertDateTimeFormat($data["TransTime"] . ":00");
			}
			if (!empty($data["GoTime"])) {
				$pars["CmpCallCard_Vyez"] = ConvertDateTimeFormat($data["GoTime"] . ":00");
			}
			if (!empty($data["ArriveTime"])) {
				$pars["CmpCallCard_Przd"] = ConvertDateTimeFormat($data["ArriveTime"] . ":00");
			}
			if (!empty($data["TransportTime"])) {
				$pars["CmpCallCard_Tgsp"] = ConvertDateTimeFormat($data["TransportTime"] . ":00");
			}
			if (!empty($data["ToHospitalTime"])) {
				$pars["CmpCallCard_HospitalizedTime"] = ConvertDateTimeFormat($data["ToHospitalTime"] . ":00");
			}
			if (!empty($data["EndTime"])) {
				$pars["CmpCallCard_Tisp"] = ConvertDateTimeFormat($data["EndTime"] . ":00");
			}
			if (!empty($data["EmergencyTeam_id"])) {
				$pars["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
			}
			if (!empty($data["CmpCloseCard_IsExtra"])) {
				$pars["CmpCallCard_IsExtra"] = $data["CmpCloseCard_IsExtra"];
			}
			$update_CmpCallCard_prmDT_result = $callObject->swUpdate("CmpCallCard", $pars, false);
			if (!$callObject->isSuccessful($update_CmpCallCard_prmDT_result)) {
				return $update_CmpCallCard_prmDT_result;
			}
		}
		//сохранение person_id в CmpCallCard
		$callObject->savePersonToCmpCallCard($data);
		//унес сохранение комбос в функцию
		$res = $callObject->saveCmpCloseCardComboValues($data, $action, $oldresult, $resArray, $NewCmpCloseCard_id, $UnicNums, $relProcedure);
		//установка статуса закрытой карты
		$statusData = [
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"CmpCallCardStatusType_id" => 6,
			"CmpCallCard_IsOpen" => 1,
			"armtype" => $data["ARMType"],
			"CmpReason_id" => 0,
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->setStatusCmpCallCard($statusData);
		return $res;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function savePersonToCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		$selectOldquery = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.CmpCallCard_Prty as \"CmpCallCard_Prty\",
				CCC.CmpCallCard_Sect as \"CmpCallCard_Sect\",
				CCC.CmpArea_id as \"CmpArea_id\",
				CCC.CmpCallCard_City as \"CmpCallCard_City\",
				CCC.CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
				CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
				CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
				CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
				CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
				CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
				CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
				CCC.CmpPlace_id as \"CmpPlace_id\",
				CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
				CCC.CmpReason_id as \"CmpReason_id\",
				CCC.Person_id as \"Person_id\",
				CCC.Person_SurName as \"Person_SurName\",
				CCC.Person_FirName as \"Person_FirName\",
				CCC.Person_SecName as \"Person_SecName\",
				CCC.Person_Age as \"Person_Age\",
				CCC.Person_BirthDay as \"Person_BirthDay\",
				CCC.Person_PolisSer as \"Person_PolisSer\",
				CCC.Person_PolisNum as \"Person_PolisNum\",
				CCC.Sex_id as \"Sex_id\",
				CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
				CCC.CmpCallType_id as \"CmpCallType_id\",
				CCC.CmpProfile_cid as \"CmpProfile_cid\",
				CCC.CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
				CCC.CmpCallCard_Stan as \"CmpCallCard_Stan\",
				CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
				CCC.CmpCallCard_Line as \"CmpCallCard_Line\",
				CCC.CmpResult_id as \"CmpResult_id\",
				CCC.CmpArea_gid as \"CmpArea_gid\",
				CCC.CmpLpu_id as \"CmpLpu_id\",
				CCC.CmpDiag_oid as \"CmpDiag_oid\",
				CCC.CmpDiag_aid as \"CmpDiag_aid\",
				CCC.CmpTrauma_id as \"CmpTrauma_id\",
				CCC.CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
				CCC.Diag_uid as \"Diag_uid\",
				CCC.CmpCallCard_Numb as \"CmpCallCard_Numb\",
				CCC.CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
				CCC.CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
				CCC.CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
				CCC.CmpProfile_bid as \"CmpProfile_bid\",
				CCC.CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
				CCC.CmpCallCard_RCod as \"CmpCallCard_RCod\",
				CCC.CmpCallCard_TabN as \"CmpCallCard_TabN\",
				CCC.CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
				CCC.CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
				CCC.CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
				CCC.CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
				CCC.Diag_sid as \"Diag_sid\",
				CCC.CmpTalon_id as \"CmpTalon_id\",
				CCC.CmpCallCard_Expo as \"CmpCallCard_Expo\",
				CCC.CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
				CCC.CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
				CCC.CmpCallCard_D201 as \"CmpCallCard_D201\",
				CCC.CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
				CCC.CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
				CCC.CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
				CCC.CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
				CCC.CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
				CCC.CmpCallCard_Tper as \"CmpCallCard_Tper\",
				CCC.CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
				CCC.CmpCallCard_Przd as \"CmpCallCard_Przd\",
				CCC.CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
				CCC.CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
				CCC.CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
				CCC.CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
				CCC.CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
				CCC.CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
				CCC.CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
				CCC.CmpArea_pid as \"CmpArea_pid\",
				CCC.CmpCallCard_PCity as \"CmpCallCard_PCity\",
				CCC.CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
				CCC.CmpCallCard_PDom as \"CmpCallCard_PDom\",
				CCC.CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
				CCC.CmpLpu_aid as \"CmpLpu_aid\",
				CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
				CCC.cmpCallCard_Medc as \"cmpCallCard_Medc\",
				CCC.CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
				CCC.CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
				CCC.CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
				CCC.CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
				CCC.CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
				CCC.CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
				CCC.CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
				CCC.CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
				CCC.pmUser_insID as \"pmUser_insID\",
				CCC.pmUser_updID as \"pmUser_updID\",
				CCC.CmpCallCard_insDT as \"CmpCallCard_insDT\",
				CCC.CmpCallCard_updDT as \"CmpCallCard_updDT\",
				CCC.KLRgn_id as \"KLRgn_id\",
				CCC.KLSubRgn_id as \"KLSubRgn_id\",
				CCC.KLCity_id as \"KLCity_id\",
				CCC.KLTown_id as \"KLTown_id\",
				CCC.KLStreet_id as \"KLStreet_id\",
				CCC.Lpu_ppdid as \"Lpu_ppdid\",
				CCC.CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
				CCC.CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
				CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				CCC.CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
				CCC.CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
				CCC.CmpPPDResult_id as \"CmpPPDResult_id\",
				CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
				CCC.CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
				CCC.Lpu_id as \"Lpu_id\",
				CCC.CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
				CCC.MedPersonal_id as \"MedPersonal_id\",
				CCC.ResultDeseaseType_id as \"ResultDeseaseType_id\",
				CCC.CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
				CCC.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
				CCC.CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
				CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\",
				CCC.CmpCallCard_Room as \"CmpCallCard_Room\",
				CCC.CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
				CCC.UslugaComplex_id as \"UslugaComplex_id\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				CCC.CmpCallerType_id as \"CmpCallerType_id\",
				CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCC.CmpCallCard_rid as \"CmpCallCard_rid\",
				CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
				CCC.CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
				CCC.CmpSecondReason_id as \"CmpSecondReason_id\",
				CCC.CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
				CCC.CmpCallReasonType_id as \"CmpCallReasonType_id\",
				CCC.CmpReasonNew_id as \"CmpReasonNew_id\",
				CCC.CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
				CCC.CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
				CCC.CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
				CCC.CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
				CCC.MedStaffFact_id as \"MedStaffFact_id\",
				CCC.RankinScale_id as \"RankinScale_id\",
				CCC.RankinScale_sid as \"RankinScale_sid\",
				CCC.LeaveType_id as \"LeaveType_id\",
				CCC.CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
				CCC.LpuSection_id as \"LpuSection_id\",
				CCC.CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
				CCC.CmpCallCard_Condition as \"CmpCallCard_Condition\",
				CCC.Lpu_cid as \"Lpu_cid\",
				CCC.CmpCallCard_Tend as \"CmpCallCard_Tend\",
				CCC.CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
				CCC.CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
				CCC.CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
				CCC.CmpRejectionReason_id as \"CmpRejectionReason_id\",
				CCC.CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
				CCC.CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
				CCC.CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
				CCC.CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
				CCC.CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
				CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
				CCC.CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
				CCC.CmpCallCard_GUID as \"CmpCallCard_GUID\",
				CCC.CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
				CCC.CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
				CCC.CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
				CCC.EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
				CCC.CmpCallCard_storDT as \"CmpCallCard_storDT\",
				CCC.CmpCallCard_defCom as \"CmpCallCard_defCom\",
				CCC.MedService_id as \"MedService_id\",
				CCC.CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
				CCC.CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
				CCC.Diag_sopid as \"Diag_sopid\",
				CCC.CmpLeaveType_id as \"CmpLeaveType_id\",
				CCC.CmpLeaveTask_id as \"CmpLeaveTask_id\",
				CCC.CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
				CCC.CmpTransportType_id as \"CmpTransportType_id\",
				CCC.CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
				CCC.CmpCallCardResult_id as \"CmpCallCardResult_id\",
				CCC.Person_IsUnknown as \"Person_IsUnknown\",
				CCC.CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
				CCC.Lpu_smpid as \"Lpu_smpid\",
				CCC.Lpu_hid as \"Lpu_hid\",
				CCC.UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
				CCC.PayType_id as \"PayType_id\",
				CCC.CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
				CCC.CmpCallCard_sid as \"CmpCallCard_sid\",
				CCC.CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
				CCC.CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
				CCC.CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
				CCC.CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
				CCC.CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
				CCC.CmpCallSignType_id as \"CmpCallSignType_id\",
				CCC.Lpu_CodeSMO as \"Lpu_CodeSMO\",
				CCC.Registry_sid as \"Registry_sid\",
				CCC.Diag_gid as \"Diag_gid\",
				CCC.MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
				CCC.CmpCommonState_id as \"CmpCommonState_id\",
			 	P.Person_IsUnknown,
			 	PS.PersonEvn_id,
			 	PS.Server_id
			from
				v_CmpCallCard CCC
				left join v_person P on P.Person_id = CCC.Person_id
				left join v_personstate PS on PS.Person_id = P.Person_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($selectOldquery, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!$result[0]) {
			return false;
		}
		$oldCard = $result[0];
		if (!empty($data["Person_id"]) && $oldCard["Person_id"] != $data["Person_id"]) {
			if (empty($data["Person_IsUnknown"])) {
				$data["Person_IsUnknown"] = null;
			}
			$personQuery = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_cmpcallcard_setperson(
				    cmpcallcard_id := :CmpCallCard_id,
				    person_id := :Person_id,
				    person_isunknown := :Person_IsUnknown,
				    pmuser_id := :pmUser_id
				);
			";
			$callObject->db->query($personQuery, $data);
			if ($data["Person_IsUnknown"] != 2 && $oldCard["Person_IsUnknown"] == 2) {
				$query = "
					select
						PersonEvn_id as \"PersonEvn_id\",
					    Server_id as \"Server_id\"
					from v_PersonState
					where Person_id = :Person_id
					limit 1
				";
				$queryParams = ["Person_id" => $data["Person_id"]];
				$personRes = $callObject->db->query($query, $queryParams);
				$personState = $personRes->result("array");
				$evnQuery = "
					select Evn_id as \"Evn_id\"
					from v_Evn
					where Person_id = :Person_id
					limit 1
				";
				$evnRes = $callObject->getFirstRowFromQuery($evnQuery, ["Person_id" => $oldCard["Person_id"]]);
				$callObject->load->model("Common_model", "Common_model");
				if (!empty($evnRes["Evn_id"])) {
					//Меняем пациента в докуменах
					$callObject->Common_model->setAnotherPersonForDocument([
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $personState[0]["PersonEvn_id"],
						"Server_id" => $personState[0]["Server_id"],
						"pmUser_id" => $data["pmUser_id"],
						"Evn_id" => $evnRes["Evn_id"]
					]);
					//и в событиях
					$mergeDataSql = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Message\"
						from pd.xp_personmergedata(
						    person_id := :Person_id,
						    person_did := :Person_did,
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"Person_id" => $data["Person_id"],
						"Person_did" => $oldCard["Person_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->db->query($mergeDataSql, $queryParams);
				}
				$stacQuery = "
					select TimeTableStac_id as \"TimeTableStac_id\"
					from v_TimeTableStac_lite
					where Person_id = :Person_id
					limit 1
				";
				$stacRes = $callObject->getFirstRowFromQuery($stacQuery, ["Person_id" => $oldCard["Person_id"]]);
				if (!empty($stacRes["TimeTableStac_id"])) {
					//Обновляем пациента в бирке, если госпитализировали неизвестного
					$updateParams = [
						"TimeTableStac_id" => $stacRes["TimeTableStac_id"],
						"Person_id" => $data["Person_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->swUpdate("TimeTableStac", $updateParams, false);
				}
				//если был неизвестный человек и пришел идентифицированный
				//то удаляем того неизвестного
				//если у него нет других учетных документов
				$callObject->load->model("Person_model", "Person_model");
				$toDel = $callObject->Person_model->checkToDelPerson(["Person_id" => $oldCard["Person_id"]]);
				if (empty($toDel["Person_id"])) {
					$callObject->Person_model->deletePerson([
						"Person_id" => $oldCard["Person_id"],
						"pmUser_id" => $data["pmUser_id"]
					]);
				}
			}
		}
		return true;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @param $action
	 * @param $oldresult
	 * @param $resArray
	 * @param $NewCmpCloseCard_id
	 * @param $UnicNums
	 * @param $relProcedure
	 * @return mixed
	 * @throws Exception
	 */
	public static function saveCmpCloseCardComboValues(CmpCallCard_model $callObject, $data, $action, $oldresult, $resArray, $NewCmpCloseCard_id, $UnicNums, $relProcedure)
	{
		$relComboFields = [];
		$queryRelParams = [
			"CmpCloseCard_id" => ($action == "add") ? ($resArray[0]["CmpCloseCard_id"]) : ($oldresult["CmpCloseCard_id"]),
			"pmUser_id" => $data["pmUser_id"]
		];
		if (!array_key_exists('CmpCloseCardRel_id', $queryRelParams)) {
			$queryRelParams['CmpCloseCardRel_id'] = NULL;
		}
		$relResult = [];
		$comboFields = [];
		foreach ($data as $cName => $cValue) {
			if (strstr($cName, "ComboCheck_")) {
				$comboFields[$cName] = $cValue;
			}
		}
		// собираем значения в relComboFields
		foreach ($comboFields as $cName => $cValue) {
			if (isset($data[$cName])) {
				//Если значений несколько, собираем значения отмеченных
				if (is_array($data[$cName])) {
					foreach ($data[$cName] as $dataField) {
						if ((int)$dataField) {
							$relComboFields[] = $callObject->getComboIdByCode($dataField);
						}
					}
				} elseif ((int)$data[$cName] && ($data[$cName] > 0)) {
					$relComboFields[] = $callObject->getComboIdByCode($data[$cName]);
				}
			}
		}
		if ($action == "add") {
			//здесь магия сохранения
			foreach ($relComboFields as $relComboField) {
				$queryRelParams["relComboField"] = $relComboField;
				$selectString = "
				    cmpclosecardrel_id as \"CmpCloseCardRel_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				";
				$query = "
					select {$selectString}
					from {$relProcedure}(
					    cmpclosecardrel_id := :CmpCloseCardRel_id,
					    cmpclosecard_id := :CmpCloseCard_id,
					    cmpclosecardcombo_id := :relComboField,
					    pmuser_id := :pmUser_id
					);
				";
				$relResult[$relComboField] = $callObject->db->query($query, $queryRelParams);
			}
			if (isset($resArray[0]) && $resArray[0]['CmpCloseCard_id']) {
				$query = "
					select *
					from p_registry_cmp_storage(
					    cmpclosecard_id := :CmpCloseCard_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"CmpCloseCard_id" => $resArray[0]["CmpCloseCard_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$callObject->db->query($query, $queryParams);
			}
		} else {
			// action edit
			//заменяем id комбобоксов на свежий
			foreach ($relComboFields as $relComboField) {
				$selectString = "
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				";
				$query = "
					select {$selectString}
					from {$callObject->schema}.p_cmpclosecardrel_updversion(
					    cmpclosecard_oldid := :CmpCloseCard_id,
					    cmpclosecard_newid := {$NewCmpCloseCard_id},
					    pmuser_id := {$data["pmUser_id"]}
					);
				";
				$queryRelParams["relComboField"] = $relComboField;
				$relResult[$relComboField] = $callObject->db->query($query, $queryRelParams);
			}
			//записываем новые значения комбиков в стрый id
			foreach ($relComboFields as $relComboField) {
				$selectString = "
				    cmpclosecardrel_id as \"CmpCloseCardRel_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				";
				$query = "
					select {$selectString}
					from {$relProcedure}(
					    cmpclosecardrel_id := :CmpCloseCardRel_id,
					    cmpclosecard_id := {$oldresult["CmpCloseCard_id"]},
					    cmpclosecardcombo_id := :relComboField,
					    pmuser_id := :pmUser_id
					);
				";
				$queryRelParams["relComboField"] = $relComboField;
				$relResult[$relComboField] = $callObject->db->query($query, $queryRelParams);
			}
		}
		//ч2 обработка остальных полей
		//обработка текстовых полей
		$txtFields = array();
		foreach ($data as $cName => $cValue) {
			if (strstr($cName, "ComboValue")) {
				$txtFields[$cName] = strval($cValue);
			}
		}
		if (is_array($txtFields)) {
			$relFieldsResult[] = $callObject->saveOtherFields($txtFields, $UnicNums, $relProcedure, $queryRelParams, $relResult);
		};
		//обработка комбо
		$cmbFields = array();
		foreach ($data as $cName => $cValue) {
			if (strstr($cName, "ComboCmp")) {
				$cmbFields[$cName] = $cValue;
			}
		}
		if (is_array($cmbFields)) {
			$relFieldsResult[] = $callObject->saveOtherFields($cmbFields, $UnicNums, $relProcedure, $queryRelParams);
		};
		return $resArray;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $Fields
	 * @param $UnicNums
	 * @param $relProcedure
	 * @param $queryRelParams
	 * @param null $relResult
	 * @throws Exception
	 */
	public static function saveOtherFields(CmpCallCard_model $callObject, $Fields, $UnicNums, $relProcedure, $queryRelParams, $relResult = null)
	{
		foreach ($Fields as $cName => $cValue) {
			$code = preg_replace("/[^0-9]/", '', $cName);
			if ($cValue != "") {
				try {
					$queryRelParams["cKey"] = $callObject->getComboIdByCode($code);
					$queryRelParams["cValue"] = $cValue;
					$relProcedure = "{$callObject->schema}.p_CmpCloseCardRel_ins";
					$selectString = "
					    cmpclosecardrel_id as \"CmpCloseCardRel_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					";
					$query = "
						select {$selectString}
						from {$relProcedure}(
						    cmpclosecardrel_id := 0,
						    cmpclosecard_id := :CmpCloseCard_id,
						    cmpclosecardcombo_id := :cKey,
						    localize := :cValue,
						    pmuser_id := :pmUser_id
						);
					";
					//m.sysolin: если текстовое поле привязано к компоненту,
					//то обновляем а не инсертим, иначе создается дубликат поля
					//не отрабатывает сохранение - разобраться на досуге
					if ($relResult) {
						foreach ($relResult as $relComboFieldKey => $relResultOutput) {
							//если у компонента есть поле со значением
							if ($relComboFieldKey == $queryRelParams["cKey"]) {
								//получаем результат выполнения из хранимки выше (CmpCloseCardRel_id)
								//и обновляем компонент
								$x = $relResultOutput->result();
								$queryRelParams["CmpCloseCardRel_id"] = $x[0]->CmpCloseCardRel_id;
								$query = "
									select
									    cmpclosecardrel_id as \"CmpCloseCardRel_id\",
									    error_code as \"Error_Code\",
									    error_message as \"Error_Message\"
									from p_cmpclosecardrel_upd(
									    cmpclosecardrel_id := :CmpCloseCardRel_id,
									    cmpclosecard_id := :CmpCloseCard_id,
									    cmpclosecardcombo_id := :cKey,
									    localize := :cValue,
									    pmuser_id := :pmUser_id
									);
								";
							}
						}
					}
					$callObject->db->query($query, $queryRelParams);
				} catch (Exception $e) {
					throw new Exception("При сохранении произошла ошибка");
				}
			}
		}
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function saveCmpCloseCardEquipmentRel(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpEquipment"]) || !sizeof($data["CmpEquipment"])) {
			return false;
		}
		$sql = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpclosecardequipmentrel_delbycmpclosecardid(cmpclosecard_id := :CmpCloseCard_id);
		";
		$callObject->db->query($sql, ["CmpCloseCard_id" => $data["CmpCloseCard_id"]]);
		foreach ($data["CmpEquipment"] as $CmpEquipment_id => $item) {
			$sql = "
				select
				    cmpclosecardequipmentrel_id as \"CmpCloseCardEquipmentRel_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_cmpclosecardequipmentrel_ins(
				    cmpclosecard_id := :CmpCloseCard_id,
				    cmpequipment_id := :CmpEquipment_id,
				    cmpclosecardequipmentrel_usedonspotcnt := :CmpCloseCardEquipmentRel_UsedOnSpotCnt,
				    cmpclosecardequipmentrel_usedincarcnt := :CmpCloseCardEquipmentRel_UsedInCarCnt,
				    pmuser_id := :pmUser_id
				);
			";
			$callObject->db->query($sql, [
				"CmpCloseCard_id" => $data["CmpCloseCard_id"],
				"CmpEquipment_id" => $CmpEquipment_id,
				"CmpCloseCardEquipmentRel_UsedOnSpotCnt" => $item["UsedOnSpotCnt"],
				"CmpCloseCardEquipmentRel_UsedInCarCnt" => $item["UsedInCarCnt"],
				"pmUser_id" => $data["pmUser_id"],
			]);
		}
		return true;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function saveCmpCloseCardDocumentUcRel(CmpCallCard_model $callObject, $data)
	{
		$sql = "
			select CmpCloseCardDocumentUcRel_id
			from v_CmpCloseCardDocumentUcRel
			where CmpCloseCard_id = :CmpCloseCard_id
			  and DocumentUc_id = :DocumentUc_id
		";
		$query = $callObject->db->query($sql, [
			"CmpCloseCard_id" => $data["CmpCloseCard_id"],
			"DocumentUc_id" => $data["DocumentUc_id"],
		]);
		if ($query->num_rows()) {
			return false;
		}
		$sql = "
			select
			    cmpclosecarddocumentucrel_id as \"CmpCloseCardDocumentUcRel_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpclosecarddocumentucrel_ins(
			    cmpclosecard_id := :CmpCloseCard_id,
			    documentuc_id := :DocumentUc_id,
			    pmuser_id := :pmUser_id
			);
		";
		$callObject->db->query($sql, [
			"CmpCloseCard_id" => $data["CmpCloseCard_id"],
			"DocumentUc_id" => $data["DocumentUc_id"],
			"pmUser_id" => $data["pmUser_id"],
		]);
		return true;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 * @deprecated
	 *
	 */
	public static function saveCmpCallCloseCard(CmpCallCard_model $callObject, $data)
	{
		if (isset($data["CmpCallCard_prmTime"])) {
			$data["CmpCallCard_prmDate"] .= " " . $data["CmpCallCard_prmTime"] . ":00.000";
		}
		$queryParams = [
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"CmpCallCard_rid" => (!empty($data["CmpCallCard_rid"]) ? $data["CmpCallCard_rid"] : NULL),
			"CmpCallCard_Numv" => $data["CmpCallCard_Numv"],
			"CmpCallCard_Ngod" => $data["CmpCallCard_Ngod"],
			"CmpCallCard_Prty" => $data["CmpCallCard_Prty"],
			"CmpCallCard_Sect" => $data["CmpCallCard_Sect"],
			"CmpArea_id" => $data["CmpArea_id"],
			"CmpCallCard_City" => $data["CmpCallCard_City"],
			"CmpCallCard_Ulic" => $data["CmpCallCard_Ulic"],
			"CmpCallCard_Dom" => $data["CmpCallCard_Dom"],
			"CmpCallCard_Kvar" => $data["CmpCallCard_Kvar"],
			"CmpCallCard_Podz" => $data["CmpCallCard_Podz"],
			"CmpCallCard_Etaj" => $data["CmpCallCard_Etaj"],
			"CmpCallCard_Kodp" => $data["CmpCallCard_Kodp"],
			"CmpCallCard_Telf" => $data["CmpCallCard_Telf"],
			"CmpPlace_id" => $data["CmpPlace_id"],
			"CmpCallCard_Comm" => $data["CmpCallCard_Comm"],
			"CmpReason_id" => $data["CmpReason_id"],
			"CmpReasonNew_id" => $data["CmpReasonNew_id"],
			"Person_id" => $data["Person_id"],
			"Person_Surname" => $data["Person_Surname"],
			"Person_Firname" => $data["Person_Firname"],
			"Person_Secname" => $data["Person_Secname"],
			"Person_Age" => $data["Person_Age"],
			"Person_Birthday" => $data["Person_Birthday"],
			"Person_PolisSer" => $data["Polis_Ser"],
			"Person_PolisNum" => $data["Polis_Num"],
			"Sex_id" => $data["Sex_id"],
			"CmpCallCard_Ktov" => $data["CmpCallCard_Ktov"],
			"CmpCallType_id" => $data["CmpCallType_id"],
			"CmpProfile_cid" => $data["CmpProfile_cid"],
			"CmpCallCard_Smpt" => $data["CmpCallCard_Smpt"],
			"CmpCallCard_Stan" => $data["CmpCallCard_Stan"],
			"CmpCallCard_prmDT" => $data["CmpCallCard_prmDate"],
			"CmpCallCard_Line" => $data["CmpCallCard_Line"],
			"CmpResult_id" => $data["CmpResult_id"],
			"ResultDeseaseType_id" => $data["ResultDeseaseType_id"],
			"LeaveType_id" => $data["LeaveType_id"],
			"CmpArea_gid" => $data["CmpArea_gid"],
			"CmpLpu_id" => $data["CmpLpu_id"],
			"CmpDiag_oid" => $data["CmpDiag_oid"],
			"CmpDiag_aid" => $data["CmpDiag_aid"],
			"CmpTrauma_id" => $data["CmpTrauma_id"],
			"CmpCallCard_IsAlco" => $data["CmpCallCard_IsAlco"],
			"Diag_uid" => $data["Diag_uid"],
			"CmpCallCard_Numb" => $data["CmpCallCard_Numb"],
			"CmpCallCard_Smpb" => $data["CmpCallCard_Smpb"],
			"CmpCallCard_Stbr" => $data["CmpCallCard_Stbr"],
			"CmpCallCard_Stbb" => $data["CmpCallCard_Stbb"],
			"CmpProfile_bid" => $data["CmpProfile_bid"],
			"CmpCallCard_Ncar" => $data["CmpCallCard_Ncar"],
			"CmpCallCard_RCod" => $data["CmpCallCard_RCod"],
			"CmpCallCard_TabN" => $data["CmpCallCard_TabN"],
			"CmpCallCard_Dokt" => $data["CmpCallCard_Dokt"],
			"CmpCallCard_Tab2" => $data["CmpCallCard_Tab2"],
			"CmpCallCard_Tab3" => $data["CmpCallCard_Tab3"],
			"CmpCallCard_Tab4" => $data["CmpCallCard_Tab4"],
			"Diag_sid" => $data["Diag_sid"],
			"CmpTalon_id" => $data["CmpTalon_id"],
			"CmpCallCard_Expo" => $data["CmpCallCard_Expo"],
			"CmpCallCard_Smpp" => $data["CmpCallCard_Smpp"],
			"CmpCallCard_Vr51" => $data["CmpCallCard_Vr51"],
			"CmpCallCard_D201" => $data["CmpCallCard_D201"],
			"CmpCallCard_Dsp1" => $data["CmpCallCard_Dsp1"],
			"CmpCallCard_Dsp2" => $data["CmpCallCard_Dsp2"],
			"CmpCallCard_Dspp" => $data["CmpCallCard_Dspp"],
			"CmpCallCard_Dsp3" => $data["CmpCallCard_Dsp3"],
			"CmpCallCard_Kakp" => $data["CmpCallCard_Kakp"],
			"CmpCallCard_Tper" => $data["CmpCallCard_Tper"],
			"CmpCallCard_Vyez" => $data["CmpCallCard_Vyez"],
			"CmpCallCard_Przd" => $data["CmpCallCard_Przd"],
			"CmpCallCard_Tgsp" => $data["CmpCallCard_Tgsp"],
			"CmpCallCard_Tsta" => $data["CmpCallCard_Tsta"],
			"CmpCallCard_Tisp" => $data["CmpCallCard_Tisp"],
			"CmpCallCard_Tvzv" => $data["CmpCallCard_Tvzv"],
			"CmpCallCard_Kilo" => $data["CmpCallCard_Kilo"],
			"CmpCallCard_Dlit" => $data["CmpCallCard_Dlit"],
			"CmpCallCard_Prdl" => $data["CmpCallCard_Prdl"],
			"CmpArea_pid" => $data["CmpArea_pid"],
			"CmpCallCard_PCity" => $data["CmpCallCard_PCity"],
			"CmpCallCard_PUlic" => $data["CmpCallCard_PUlic"],
			"CmpCallCard_PDom" => $data["CmpCallCard_PDom"],
			"CmpCallCard_PKvar" => $data["CmpCallCard_PKvar"],
			"CmpCallCard_Izv1" => $data["CmpCallCard_Izv1"],
			"CmpCallCard_Tiz1" => $data["CmpCallCard_Tiz1"],
			"CmpCallCard_Inf1" => $data["CmpCallCard_Inf1"],
			"CmpCallCard_Inf2" => $data["CmpCallCard_Inf2"],
			"CmpCallCard_Inf3" => $data["CmpCallCard_Inf3"],
			"CmpCallCard_Inf4" => $data["CmpCallCard_Inf4"],
			"CmpCallCard_Inf5" => $data["CmpCallCard_Inf5"],
			"CmpCallCard_Inf6" => $data["CmpCallCard_Inf6"],
			"KLRgn_id" => $data["KLRgn_id"],
			"KLSubRgn_id" => $data["KLSubRgn_id"],
			"KLCity_id" => $data["KLCity_id"],
			"KLTown_id" => $data["KLTown_id"],
			"KLStreet_id" => $data["KLStreet_id"],
			"Lpu_id" => $data["Lpu_id"],
			"Lpu_ppdid" => $data["Lpu_ppdid"],
			"LpuBuilding_id" => (!empty($data["LpuBuilding_id"])) ? $data["LpuBuilding_id"] : null,
			"CmpCallCard_IsOpen" => $data["CmpCallCard_IsOpen"],
			"CmpCallCard_IsReceivedInPPD" => array_key_exists("CmpCallCard_IsReceivedInPPD", $data) ? $data["CmpCallCard_IsReceivedInPPD"] : 1,
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = "
			select
			    cmpcallcard_id as \"CmpCallCard_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcard_copy(
			    cmpcallcard_id := 0,
			    pmuser_id := 0
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$query = "
			update CmpCallCard set
				CmpCallCard_rid = :CmpCallCard_rid,
				CmpCallCard_Numv = :CmpCallCard_Numv,
				CmpCallCard_Ngod = :CmpCallCard_Ngod,
				CmpCallCard_Prty = :CmpCallCard_Prty,
				CmpCallCard_Sect = :CmpCallCard_Sect,
				CmpArea_id = :CmpArea_id,
				CmpCallCard_City = :CmpCallCard_City,
				CmpCallCard_Ulic = :CmpCallCard_Ulic,
				CmpCallCard_Dom = :CmpCallCard_Dom,
				CmpCallCard_Kvar = :CmpCallCard_Kvar,
				CmpCallCard_Podz = :CmpCallCard_Podz,
				CmpCallCard_Etaj = :CmpCallCard_Etaj,
				CmpCallCard_Kodp = :CmpCallCard_Kodp,
				CmpCallCard_Telf = :CmpCallCard_Telf,
				CmpPlace_id = :CmpPlace_id,
				CmpCallCard_Comm = :CmpCallCard_Comm,
				CmpReason_id = :CmpReason_id,
				CmpReasonNew_id = :CmpReasonNew_id,
				Person_id = :Person_id,
				Person_SurName = :Person_Surname,
				Person_FirName = :Person_Firname,
				Person_SecName = :Person_Secname,
				Person_Age = :Person_Age,
				Person_BirthDay = :Person_Birthday,
				Person_PolisNum = :Person_PolisNum,
				Person_PolisSer = :Person_PolisSer,
				Sex_id = :Sex_id,
				CmpCallCard_Ktov = :CmpCallCard_Ktov,
				CmpCallType_id = :CmpCallType_id,
				CmpProfile_cid = :CmpProfile_cid,
				CmpCallCard_Smpt = :CmpCallCard_Smpt,
				CmpCallCard_Stan = :CmpCallCard_Stan,
				CmpCallCard_prmDT = :CmpCallCard_prmDT,
				CmpCallCard_Line = :CmpCallCard_Line,
				CmpResult_id = :CmpResult_id,
				LeaveType_id=:LeaveType_id,
				ResultDeseaseType_id = :ResultDeseaseType_id,
				CmpArea_gid = :CmpArea_gid,
				CmpLpu_id = :CmpLpu_id,
				CmpDiag_oid = :CmpDiag_oid,
				CmpDiag_aid = :CmpDiag_aid,
				CmpTrauma_id = :CmpTrauma_id,
				CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
				Diag_uid = :Diag_uid,
				CmpCallCard_Numb = :CmpCallCard_Numb,
				CmpCallCard_Smpb = :CmpCallCard_Smpb,
				CmpCallCard_Stbr = :CmpCallCard_Stbr,
				CmpCallCard_Stbb = :CmpCallCard_Stbb,
				CmpProfile_bid = :CmpProfile_bid,
				CmpCallCard_Ncar = :CmpCallCard_Ncar,
				CmpCallCard_RCod = :CmpCallCard_RCod,
				CmpCallCard_TabN = :CmpCallCard_TabN,
				CmpCallCard_Dokt = :CmpCallCard_Dokt,
				CmpCallCard_Tab2 = :CmpCallCard_Tab2,
				CmpCallCard_Tab3 = :CmpCallCard_Tab3,
				CmpCallCard_Tab4 = :CmpCallCard_Tab4,
				Diag_sid = :Diag_sid,
				CmpTalon_id = :CmpTalon_id,
				CmpCallCard_Expo = :CmpCallCard_Expo,
				CmpCallCard_Smpp = :CmpCallCard_Smpp,
				CmpCallCard_Vr51 = :CmpCallCard_Vr51,
				CmpCallCard_D201 = :CmpCallCard_D201,
				CmpCallCard_Dsp1 = :CmpCallCard_Dsp1,
				CmpCallCard_Dsp2 = :CmpCallCard_Dsp2,
				CmpCallCard_Dspp = :CmpCallCard_Dspp,
				CmpCallCard_Dsp3 = :CmpCallCard_Dsp3,
				CmpCallCard_Kakp = :CmpCallCard_Kakp,
				CmpCallCard_Tper = :CmpCallCard_Tper,
				CmpCallCard_Vyez = :CmpCallCard_Vyez,
				CmpCallCard_Przd = :CmpCallCard_Przd,
				CmpCallCard_Tgsp = :CmpCallCard_Tgsp,
				CmpCallCard_Tsta = :CmpCallCard_Tsta,
				CmpCallCard_Tisp = :CmpCallCard_Tisp,
				CmpCallCard_Tvzv = :CmpCallCard_Tvzv,
				CmpCallCard_Kilo = :CmpCallCard_Kilo,
				CmpCallCard_Dlit = :CmpCallCard_Dlit,
				CmpCallCard_Prdl = :CmpCallCard_Prdl,
				CmpArea_pid = :CmpArea_pid,
				CmpCallCard_PCity = :CmpCallCard_PCity,
				CmpCallCard_PUlic = :CmpCallCard_PUlic,
				CmpCallCard_PDom = :CmpCallCard_PDom,
				CmpCallCard_PKvar = :CmpCallCard_PKvar,
				CmpCallCard_Izv1 = :CmpCallCard_Izv1,
				CmpCallCard_Tiz1 = :CmpCallCard_Tiz1,
				CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
				CmpCallCard_Inf1 = :CmpCallCard_Inf1,
				CmpCallCard_Inf2 = :CmpCallCard_Inf2,
				CmpCallCard_Inf3 = :CmpCallCard_Inf3,
				CmpCallCard_Inf4 = :CmpCallCard_Inf4,
				CmpCallCard_Inf5 = :CmpCallCard_Inf5,
				CmpCallCard_Inf6 = :CmpCallCard_Inf6,
				KLRgn_id = :KLRgn_id,
				KLSubRgn_id = :KLSubRgn_id,
				KLCity_id = :KLCity_id,
				KLTown_id = :KLTown_id,
				KLStreet_id = :KLStreet_id,
				LpuBuilding_id = :LpuBuilding_id,
				Lpu_id = :Lpu_id,
				Lpu_ppdid = :Lpu_ppdid,
				CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
			    pmUser_updID = :pmUser_id
			where CmpCallCard_id = :CmpCallCard_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resultforstatus = $result->result("array");
		if (($data["ARMType"] == "smpreg") || ($data["ARMType"] == "smpdispatchdirect")) {
			$data["CmpCallCard_id"] = $resultforstatus[0]["CmpCallCard_id"];
			$data["CmpCallCardStatusType_id"] = 1;
			$data["CmpCallCardStatus_Comment"] = "";
			$callObject->setStatusCmpCallCard($data);
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $qparams
	 * @return CI_DB_result
	 */
	public static function saveCmpCallCardEvent(CmpCallCard_model $callObject, $qparams)
	{
		$fields = "";
		if (isset($qparams["LpuSection_id"]) && $qparams["LpuSection_id"] != "" && $qparams["LpuSection_id"] > 0) {
			$fields .= " LpuSection_id = :LpuSection_id, ";
		} else {
			$fields .= " LpuSection_id = null, ";
		}
		if (!empty($qparams["CmpCallCardEvent_Comment"])) {
			$fields .= " CmpCallCardEvent_Comment = :CmpCallCardEvent_Comment, ";
		} else {
			$fields .= " CmpCallCardEvent_Comment = null, ";
		}
		$query = "
			select
			    cmpcallcardevent_id as \"CmpCallCardEvent_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcardevent_ins(
			    cmpcallcardeventtype_id := :CmpCallCardEventType_id,
			    cmpcallcardstatus_id := :CmpCallCardStatus_id,
			    emergencyteamstatushistory_id := :EmergencyTeamStatusHistory_id,
			    lpubuilding_id := :LpuBuilding_id,
			    {$fields}
			    emergencyteam_id := :EmergencyTeam_id,
			    cmpcallcard_id := :CmpCallCard_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $qparams);
		return $result;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function saveCmpCallCardMessage(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
			    cmpcallcardmessage_id as \"CmpCallCardMessage_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcardmessage_ins(
			    message_id := :Message_id,
			    cmpcallcard_id := :CmpCallCard_id,
			    cmpcallcardmessage_webdt := tzGetDate(),
			    cmpcallcardmessage_tabletdt := null,
			    pmuser_id := :pmUser_id
			);
		";
		return $callObject->queryResult($query, $data);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public static function saveDecigionTree(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["data"]) || !isset($data["Lpu_id"])) {
			return false;
		}
		set_time_limit(3000);
		$decigionTreeData = json_decode($data['data'], true);
		if ($decigionTreeData == null || !is_array($decigionTreeData) || sizeof($decigionTreeData) == 0) {
			throw new Exception("Неверный формат данных дерева");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_ambulancedecigiontree_delallbylpuid(lpu_id := :Lpu_id);
		";
		$result = $callObject->db->query($query, ["Lpu_id" => $data["Lpu_id"]]);
		if (!is_object($result)) {
			return false;
		}
		//Удаляем элементы предыдущего дерева решений, если оно существовало
		foreach ($decigionTreeData as $treeItem) {
			//Сохраняем элементы нового дерева решений
			$saveItemQuery = "
				select
				    ambulancedecigiontree_id as \"AmbulanceDecigionTree_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_ambulancedecigiontree_ins(
				    ambulancedecigiontree_nodeid := :AmbulanceDecigionTree_nodeid,
				    ambulancedecigiontree_nodepid := :AmbulanceDecigionTree_nodepid,
				    ambulancedecigiontree_type := :AmbulanceDecigionTree_Type,
				    ambulancedecigiontree_text := :AmbulanceDecigionTree_Text,
				    cmpreason_id := :CmpReason_id,
				    lpu_id := :Lpu_id,
				    ambulancedecigiontree_guid := null,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"AmbulanceDecigionTree_id" => null,
				"AmbulanceDecigionTree_nodeid" => $treeItem["AmbulanceDecigionTree_nodeid"],
				"AmbulanceDecigionTree_nodepid" => $treeItem["AmbulanceDecigionTree_nodepid"],
				"AmbulanceDecigionTree_Type" => $treeItem["AmbulanceDecigionTree_Type"],
				"AmbulanceDecigionTree_Text" => toAnsi($treeItem["AmbulanceDecigionTree_Text"]),
				"CmpReason_id" => ($treeItem["CmpReason_id"] > 1) ? $treeItem["CmpReason_id"] : null,
				"Lpu_id" => $data["Lpu_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$saveItemResult = $callObject->db->query($saveItemQuery, $queryParams);
			if (!is_object($saveItemResult)) {
				return false;
			}
			$saveItemResult = $saveItemResult->result("array");
			if (is_array($saveItemResult) && isset($saveItemResult[0]) && isset($saveItemResult[0]["Error_Msg"]) && $saveItemResult[0]["Error_Msg"] != "") {
				return $saveItemResult;
			}
		}
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function saveDecigionTreeNode(CmpCallCard_model $callObject, $data)
	{

		$procedure = (empty($data["AmbulanceDecigionTree_id"])) ? "p_AmbulanceDecigionTree_ins" : "p_AmbulanceDecigionTree_upd";
		$selectString = "
		    ambulancedecigiontree_id as \"AmbulanceDecigionTree_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    ambulancedecigiontree_id := :AmbulanceDecigionTree_id,
			    ambulancedecigiontree_nodeid := :AmbulanceDecigionTree_nodeid,
			    ambulancedecigiontree_nodepid := :AmbulanceDecigionTree_nodepid,
			    ambulancedecigiontree_type := :AmbulanceDecigionTree_Type,
			    ambulancedecigiontree_text := :AmbulanceDecigionTree_Text,
				AmbulanceDecigionTreeRoot_id := :AmbulanceDecigionTreeRoot_id,
			    cmpreason_id := :CmpReason_id,
			    lpu_id := :Lpu_id,
		    	LpuBuilding_id := :LpuBuilding_id,
				Region_id := :Region_id,			    
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"AmbulanceDecigionTree_id" => !empty($data["AmbulanceDecigionTree_id"]) ? $data["AmbulanceDecigionTree_id"] : null,
			"AmbulanceDecigionTree_nodeid" => $data["AmbulanceDecigionTree_nodeid"],
			"AmbulanceDecigionTree_nodepid" => $data["AmbulanceDecigionTree_nodepid"],
			"AmbulanceDecigionTree_Type" => $data["AmbulanceDecigionTree_Type"],
			"AmbulanceDecigionTree_Text" => $data["AmbulanceDecigionTree_Text"],
		    "AmbulanceDecigionTreeRoot_id" => $data["AmbulanceDecigionTreeRoot_id"],			
			"CmpReason_id" => !empty($data["CmpReason_id"]) ? $data["CmpReason_id"] : null,
			"Lpu_id" => !empty($data["Lpu_id"]) ? $data["Lpu_id"]:null,
			"LpuBuilding_id" => !empty($data["LpuBuilding_id"]) ? $data["LpuBuilding_id"]:null,
			"Region_id" => $data["session"]["region"]["number"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public static function saveCmpUrgencyAndProfileStandartRule(CmpCallCard_model $callObject, $data)
	{
		$queryParams = [
			"pmUser_id" => $data["pmUser_id"],
			"CmpCallCardAcceptor_id" => !empty($data["CmpCallCardAcceptor_id"]) ? $data["CmpCallCardAcceptor_id"] : null,
			"CmpUrgencyAndProfileStandart_HeadDoctorObserv" => !empty($data["CmpUrgencyAndProfileStandart_HeadDoctorObserv"]) ? $data["CmpUrgencyAndProfileStandart_HeadDoctorObserv"] : null,
			"CmpUrgencyAndProfileStandart_MultiVictims" => !empty($data["CmpUrgencyAndProfileStandart_MultiVictims"]) ? $data["CmpUrgencyAndProfileStandart_MultiVictims"] : null,
		];
		if (empty($data["Lpu_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор ЛПУ");
		}
		if (empty($data["CmpReason_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор повода вызова");
		}
		if (empty($data["CmpUrgencyAndProfileStandart_Urgency"])) {
			throw new Exception("Не задан обязательный параметр: базовая срочность");
		}
		if (!isset($data["CmpCallPlaceType_Array"]) || !is_array($data["CmpCallPlaceType_Array"]) || sizeof($data["CmpCallPlaceType_Array"]) == 0) {
			throw new Exception("Не задан обязательный параметр: массив мест вызова");
		}
		if (!isset($data["CmpUrgencyAndProfileStandartRefSpecPriority_Array"]) || !is_array($data["CmpUrgencyAndProfileStandartRefSpecPriority_Array"]) || sizeof($data["CmpUrgencyAndProfileStandartRefSpecPriority_Array"]) == 0) {
			throw new Exception("Не задан обязательный параметр: массив профилей бригад и их приоритетов");
		}
		$checkResult = $callObject->checkRuleUniqueness($data);
		if (!is_array($checkResult)) {
			return false;
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		$queryParams["CmpReason_id"] = $data["CmpReason_id"];
		$queryParams["CmpUrgencyAndProfileStandart_Urgency"] = $data["CmpUrgencyAndProfileStandart_Urgency"];
		//Если размер возвращаемого массива = 0, значит не найдено ни одно конфликтующее правило
		if (sizeof($checkResult) != 0) {
			//Если в результате проверки на конфликт с другими правилами произошла ошибка, возвращаем результат
			if (isset($checkResult[0]["success"]) && $checkResult[0]["success"] == false) {
				return $checkResult;
			}
			//Если в результате проверки на конфликт с другими правилами нашли конфликтующее правило, возвращаем его идентификатор и соответствующий код ошибки
			return [["success" => false, "Error_Code" => "ruleconflict", "CmpUrgencyAndProfileStandart_id" => $checkResult[0]["CmpUrgencyAndProfileStandart_id"]]];
		}
		$queryParams["CmpUrgencyAndProfileStandart_UntilAgeOf"] = (!empty($data["CmpUrgencyAndProfileStandart_UntilAgeOf"])) ? $data["CmpUrgencyAndProfileStandart_UntilAgeOf"] : NULL;
		$callObject->beginTransaction();
		$procedure = (empty($data["CmpUrgencyAndProfileStandart_id"])) ? "p_CmpUrgencyAndProfileStandart_ins" : "p_CmpUrgencyAndProfileStandart_upd";
		$queryParams["CmpUrgencyAndProfileStandart_id"] = (empty($data["CmpUrgencyAndProfileStandart_id"])) ? null : $data["CmpUrgencyAndProfileStandart_id"];
		if (!empty($data["CmpUrgencyAndProfileStandart_id"])) {
			$deleteRefsQuery = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_cmpurgencyandprofilestandart_delrefsplacesandspec(cmpurgencyandprofilestandart_id := :CmpUrgencyAndProfileStandart_id);
			";
			$deleteRefsResult = $callObject->db->query($deleteRefsQuery, ["CmpUrgencyAndProfileStandart_id" => $data["CmpUrgencyAndProfileStandart_id"]]);
			if (!is_object($deleteRefsResult)) {
				$callObject->rollbackTransaction();
				return false;
			}
			$deleteRefsResult = $deleteRefsResult->result("array");
			if (strlen($deleteRefsResult[0]["Error_Msg"]) != 0) {
				$callObject->rollbackTransaction();
				return $deleteRefsResult;
			}
		}
		$selectString = "
		    cmpurgencyandprofilestandart_id as \"CmpUrgencyAndProfileStandart_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$querySaveRule = "
			select {$selectString}
			from {$procedure}(
			    cmpurgencyandprofilestandart_id := :CmpUrgencyAndProfileStandart_id,
			    cmpreason_id := :CmpReason_id,
			    lpu_id := :Lpu_id,
			    cmpurgencyandprofilestandart_urgency := :CmpUrgencyAndProfileStandart_Urgency,
			    cmpurgencyandprofilestandart_untilageof := :CmpUrgencyAndProfileStandart_UntilAgeOf,
			    cmpcallcardacceptor_id := :CmpCallCardAcceptor_id,
			    cmpurgencyandprofilestandart_headdoctorobserv := :CmpUrgencyAndProfileStandart_HeadDoctorObserv,
			    cmpurgencyandprofilestandart_multivictims := :CmpUrgencyAndProfileStandart_MultiVictims,
			    pmuser_id := :pmUser_id
			);
		";
		$queryInsertRefPlace = "
			select
			    cmpurgencyandprofilestandartrefplace_id as \"CmpUrgencyAndProfileStandartRefPlace_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpurgencyandprofilestandartrefplace_ins(
			    cmpurgencyandprofilestandart_id := :CmpUrgencyAndProfileStandart_id,
			    cmpcallplacetype_id := :CmpCallPlaceType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryInsertRefETSpec = "
			select
			    cmpurgencyandprofilestandartrefspecpriority_id as \"CmpUrgencyAndProfileStandartRefSpecPriority_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpurgencyandprofilestandartrefspecpriority_ins(
			    cmpurgencyandprofilestandart_id := :CmpUrgencyAndProfileStandart_id,
			    cmpurgencyandprofilestandartrefspecpriority_profilepriority := :CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,
			    emergencyteamspec_id := :EmergencyTeamSpec_id,
			    pmuser_id := :pmUser_id
			);
		";
		//Сначала сохраняем само правило
		/**@var CI_DB_result $resultSaveRule */
		$resultSaveRule = $callObject->db->query($querySaveRule, $queryParams);
		if (!is_object($resultSaveRule)) {
			$callObject->rollbackTransaction();
			return false;
		}
		$resultSaveRule = $resultSaveRule->result("array");
		if (!empty($resultSaveRule[0]["Error_msg"])) {
			$callObject->rollbackTransaction();
			return $resultSaveRule;
		}
		//Затим  сохраняем привязанные к правилу места
		foreach ($data["CmpCallPlaceType_Array"] as $place) {
			if (empty($place["CmpCallPlaceType_id"])) {
				$callObject->rollbackTransaction();
				throw new Exception("Не задан обязательный параметр: идентификатор места вызова");
			}
			$queryParams = [
				"CmpUrgencyAndProfileStandart_id" => $resultSaveRule[0]["CmpUrgencyAndProfileStandart_id"],
				"CmpCallPlaceType_id" => $place["CmpCallPlaceType_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			/**@var CI_DB_result $resultInsertRuleRefPlace */
			$resultInsertRuleRefPlace = $callObject->db->query($queryInsertRefPlace, $queryParams);
			if (!is_object($resultInsertRuleRefPlace)) {
				$callObject->db->trans_rollback();
				return false;
			}
			$resultInsertRuleRefPlace = $resultInsertRuleRefPlace->result("array");
			if (!empty($resultInsertRuleRefPlace[0]["Error_msg"])) {
				$callObject->rollbackTransaction();
				return $resultInsertRuleRefPlace;
			}
		}
		//Затим  сохраняем привязанные к правилу профили бригад и их приоритеты
		foreach ($data["CmpUrgencyAndProfileStandartRefSpecPriority_Array"] as $spec) {
			if (empty($spec["EmergencyTeamSpec_id"])) {
				$callObject->rollbackTransaction();
				throw new Exception("Не задан обязательный параметр: идентификатор профиля бригады");
			}
			if (!array_key_exists("CmpUrgencyAndProfileStandartRefSpecPriority_Priority", $spec)) {
				$callObject->rollbackTransaction();
				throw new Exception("Не задан обязательный параметр: приоритет назначения профиля на вызов");
			}
			$queryParams = [
				"CmpUrgencyAndProfileStandart_id" => $resultSaveRule[0]["CmpUrgencyAndProfileStandart_id"],
				"EmergencyTeamSpec_id" => $spec["EmergencyTeamSpec_id"],
				"CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority" => $spec["CmpUrgencyAndProfileStandartRefSpecPriority_Priority"],
				"pmUser_id" => $data["pmUser_id"]
			];
			/**@var CI_DB_result $resultInsertRuleRefSpec */
			$resultInsertRuleRefSpec = $callObject->db->query($queryInsertRefETSpec, $queryParams);
			if (!is_object($resultInsertRuleRefSpec)) {
				$callObject->rollbackTransaction();
				return false;
			}
			$resultInsertRuleRefSpec = $resultInsertRuleRefSpec->result("array");
			if (!empty($resultInsertRuleRefSpec[0]["Error_msg"])) {
				$callObject->rollbackTransaction();
				return $resultInsertRuleRefSpec;
			}
		}
		$callObject->commitTransaction();
		return $resultSaveRule;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	public static function saveCmpCallCardUsluga(CmpCallCard_model $callObject, $data)
	{
		$params = [
			"CmpCallCardUsluga_id" => (empty($data["CmpCallCardUsluga_id"]) || $data["CmpCallCardUsluga_id"] < 0) ? null : $data["CmpCallCardUsluga_id"],
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"CmpCallCardUsluga_setDate" => $data["CmpCallCardUsluga_setDate"],
			"CmpCallCardUsluga_setTime" => $data["CmpCallCardUsluga_setTime"],
			"MedStaffFact_id" => !empty($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null,
			"PayType_id" => !empty($data["PayType_id"]) ? $data["PayType_id"] : null,
			"PayType_Code" => !empty($data["PayType_Code"]) ? $data["PayType_Code"] : null,
			"UslugaCategory_id" => $data["UslugaCategory_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"UslugaComplexTariff_id" => empty($data["UslugaComplexTariff_id"]) ? null : $data["UslugaComplexTariff_id"],
			"CmpCallCardUsluga_Cost" => empty($data["CmpCallCardUsluga_Cost"]) ? null : $data["CmpCallCardUsluga_Cost"],
			"CmpCallCardUsluga_Kolvo" => empty($data["CmpCallCardUsluga_Kolvo"]) ? null : $data["CmpCallCardUsluga_Kolvo"],
			"pmUser_id" => $data["pmUser_id"]
		];
		if (!empty($data["PayType_Code"])) {
			$payTypesql = "select PayType_id from v_PayType where PayType_Code = :PayType_Code";
			$payTypeId = $callObject->getFirstResultFromQuery($payTypesql, $params);
			if (!empty($payTypeId)) {
				$params["PayType_id"] = $payTypeId;
			}
		}
		$regionNick = $callObject->regionNick;
		//Проверяем на Перми, что услуга выполнена в ближайшие 24 часа ПОСЛЕ вызова
		if (in_array($regionNick, ["perm", "ekb"])) {
			$CmpCallCard_data = $callObject->loadCmpCallCardEditForm(["CmpCallCard_id" => $data["CmpCallCard_id"]]);
			if (!$callObject->isSuccessful($CmpCallCard_data)) {
				return $CmpCallCard_data;
			}
			$CmpCallCardUsluga_setDT = DateTime::createFromFormat("Y-m-d H:i", $data["CmpCallCardUsluga_setDate"] . " " . $data["CmpCallCardUsluga_setTime"]);
			// текстовая выборка, ибо затесавшиеся в CmpCallCard_prmDT мешают проверке при сохранении услуг
			// @task https://redmine.swan.perm.ru/issues/100697
			$CmpCallCard_prmDT = DateTime::createFromFormat("Y-m-d H:i", $CmpCallCard_data[0]["CmpCallCard_prmDT"]);
			if ($CmpCallCardUsluga_setDT === false) {
				throw new Exception("Неверные значения в поле дата/время вызова");
			}
			$date_diff = $CmpCallCard_prmDT->diff($CmpCallCardUsluga_setDT);
			if (($date_diff === FALSE) || ($CmpCallCard_prmDT > $CmpCallCardUsluga_setDT) || (($date_diff->y * 24 * 30 * 12 + $date_diff->m * 24 * 30 + $date_diff->d * 24 + $date_diff->h) > 24)) {
				throw new Exception("Услуга должна быть выполнена не раньше даты и времени приема и не позднее, чем 24 часа.");
			}
		}
		if (in_array($regionNick, ["perm", "ekb"])) {
			$query = "
				select count(UCT.UslugaComplexTariff_id) as Count
				from v_UslugaComplexTariff UCT
				where UCT.UslugaComplex_id = :UslugaComplex_id
				  and UCT.UslugaComplexTariff_begDate <= :CmpCallCardUsluga_setDate
				  and (UCT.UslugaComplexTariff_endDate > :CmpCallCardUsluga_setDate or UCT.UslugaComplexTariff_endDate is null)
				limit 1
			";
			$tariff_count = $callObject->getFirstResultFromQuery($query, $params);
			if ($tariff_count === false) {
				throw new Exception("Ошибка при проверке наличия тарифов");
			}
			if ($tariff_count == 0) {
				$callObject->addWarningMsg("Карта СМП: На данную услугу нет тарифа!");
			}
		}
		if (empty($data["MedStaffFact_id"])) {
			//При пустом месте работы пробуем достать из карты
			$selectString = "MedStaffFact_id";
			$fromString = "{$callObject->schema}.v_CmpCloseCard";
			$whereString = "CmpCallCard_id = :CmpCallCard_id";
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
				limit 1
			";
			$params["MedStaffFact_id"] = $callObject->getFirstResultFromQuery($query, $data);
		}
		$procedure = (!empty($data["CmpCallCardUsluga_id"]) && $data["CmpCallCardUsluga_id"] > 0) ? "p_CmpCallCardUsluga_upd" : "p_CmpCallCardUsluga_ins";
		$selectString = "
		    cmpcallcardusluga_id as \"CmpCallCardUsluga_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    cmpcallcardusluga_id := :CmpCallCardUsluga_id,
			    cmpcallcard_id := :CmpCallCard_id,
			    cmpcallcardusluga_setdate := :CmpCallCardUsluga_setDate,
			    cmpcallcardusluga_settime := :CmpCallCardUsluga_setTime,
			    medstafffact_id := :MedStaffFact_id,
			    paytype_id := :PayType_id,
			    uslugacategory_id := :UslugaCategory_id,
			    uslugacomplex_id := :UslugaComplex_id,
			    uslugacomplextariff_id := :UslugaComplexTariff_id,
			    cmpcallcardusluga_cost := :CmpCallCardUsluga_Cost,
			    cmpcallcardusluga_kolvo := :CmpCallCardUsluga_Kolvo,
			    pmuser_id := :pmUser_id
			);
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	public static function saveCmpCallCardUslugaList(CmpCallCard_model $callObject, $data)
	{
		$rules = [
			["field" => "CmpCallCard_id", "label" => "Идентификатор карты вызова СМП", "rules" => "required", "type" => "id"],
			["field" => "usluga_array", "label" => "Список услуг", "rules" => "", "type" => "array", "default" => []],
			["field" => "pmUser_id", "rules" => "required", "label" => "Идентификатор пользователя", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err, true);
		if (!$queryParams || !empty($err)) {
			return $err;
		}
		$queryParams["usluga_array"] = $data["usluga_array"];
		$existUslugaList = $callObject->loadCmpCallCardUslugaGrid($data);
		//пробегаемся по услугам, которые пришли с формы
		for ($i = 0; $i < sizeof($queryParams["usluga_array"]); $i++) {
			$usluga_data = (array)$queryParams["usluga_array"][$i];
			if (!empty($usluga_data["status"]) && ($usluga_data["status"] == "deleted")) {
				continue;
			}
			$unchangedUsluga = false;
			//если услуга в списке существующих в базе, то удаляем из списка элемент $existUslugaList
			//статус записи проставляем на редактирование, если нет - на удаление
			foreach ($existUslugaList as $key => $value) {
				if ($value["UslugaComplex_id"] == $usluga_data["UslugaComplex_id"]) {
					$usluga_data["CmpCallCardUsluga_id"] = $value["CmpCallCardUsluga_id"];
					if ($usluga_data["CmpCallCardUsluga_Kolvo"] == $value["CmpCallCardUsluga_Kolvo"]) {
						$unchangedUsluga = true;
					}
					unset($existUslugaList[$key]);
					break;
				}
				//для Адиса
				if (!empty($usluga_data["status"]) && ($usluga_data["status"] == "edited")) {
					unset($existUslugaList[$key]);
				}
			}
			if ($unchangedUsluga) {
				//если услуга не была изменена пропускаем ее
				continue;
			}
			$usluga_data["CmpCallCardUsluga_id"] = empty($usluga_data["CmpCallCardUsluga_id"]) ? null : $usluga_data["CmpCallCardUsluga_id"];
			$usluga_data["pmUser_id"] = $queryParams["pmUser_id"];
			$usluga_data["CmpCallCard_id"] = $queryParams["CmpCallCard_id"];
			//Преобразуем формат даты для метода сохранения услуги в модели
			$CmpCallCardUsluga_setDate = DateTime::createFromFormat("d.m.Y", $usluga_data["CmpCallCardUsluga_setDate"]);
			if ($CmpCallCardUsluga_setDate === false) {
				throw new Exception("Ошибка преобразования даты выполнения услуги. Дата выполнения услуги должна передаваться в формате дд.мм.гггг");
			}
			$usluga_data["CmpCallCardUsluga_setDate"] = $CmpCallCardUsluga_setDate->format("Y-m-d");
			$save_usluga_response = $callObject->saveCmpCallCardUsluga($usluga_data);
			if (!$callObject->isSuccessful($save_usluga_response)) {
				return $save_usluga_response;
			}
		}
		//те услуги которые не пришли удаляются
		foreach ($existUslugaList as $value) {
			$callObject->deleteCmpCallCardUsluga($value);
		}
		return [["success" => true, "Error_Msg" => null]];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $object_name
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveObject(CmpCallCard_model $callObject, $object_name, $data)
	{
		$schema = "dbo";
		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode(".", $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}
		$key_field = !empty($data["key_field"]) ? $data["key_field"] : "{$object_name}_id";
		if (!isset($data[$key_field])) {
			$data[$key_field] = null;
		}
		$action = $data[$key_field] > 0 ? "upd" : "ins";
		$proc_name = "p_{$object_name}_{$action}";
		$params_list = $callObject->getStoredProcedureParamsList($proc_name, $schema);
		foreach ($params_list as $key => $param) {
			$params_list[$key] = mb_strtolower($param);
		}
		$save_data = [];
		$query_part = "";
		//получаем существующие данные если апдейт
		if ($action == "upd") {
			$query = "
				select *
				from {$schema}.{$object_name}
				where {$key_field} = :id;
			";
			$result = $callObject->getFirstRowFromQuery($query, ["id" => $data[$key_field]]);
			if (is_array($result)) {
				foreach ($result as $key => $value) {
					if (in_array($key, $params_list)) {
						$save_data[$key] = $value;
					}
				}
			} else {
				$proc_name = "p_{$object_name}_ins";
			}
		}
		$returnDocumentUc = "";
		$declareDocumentUc = "";
		$executeDocumentUc = ";";
		if ($proc_name == "p_EvnDrug_ins") {
			//TODO 111
			$returnDocumentUc = ", @DocumentUcStr_cid as DocumentUcStr_cid, @DocumentUc_cid as DocumentUc_cid";
			if (isset($data['DocumentUcStr_cid']) && $data['DocumentUcStr_cid'] > 0) {
				$declareDocumentUc .= "@DocumentUcStr_cid bigint = '" . $data['DocumentUcStr_cid'] . "',";
				unset($data["DocumentUcStr_cid"]);
			} else {
				$declareDocumentUc .= "@DocumentUcStr_cid bigint = NULL, ";
			}
			if (isset($data["DocumentUc_cid"]) && $data["DocumentUc_cid"] > 0) {
				$declareDocumentUc .= "@DocumentUc_cid bigint = '" . $data['DocumentUc_cid'] . "',";
				unset($data["DocumentUc_cid"]);
			} else {
				$declareDocumentUc .= "@DocumentUc_cid bigint = NULL, ";
			}
			foreach ($data as $key => $value) {
				if (!is_object($value) && in_array(mb_strtolower($value), array("documentuc_cid", "documentucstr_cid"))) {
					unset($data[$key]);
				}
			}
			$executeDocumentUc = ", @DocumentUcStr_cid = @DocumentUcStr_cid output,
			@DocumentUc_cid = @DocumentUc_cid output;";
		}
		foreach ($data as $key => $value) {
			if (in_array(mb_strtolower($key), $params_list)) {
				$save_data[$key] = $value;
			}
		}
		foreach ($save_data as $key => $value) {
			if (in_array(mb_strtolower($key), $params_list) && $key != $key_field) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == "DateTime") {
					$save_data[$key] = $save_data[$key]->format("Y-m-d H:i:s");
				}
				$query_part .= "{$key} = :{$key}, ";
			}
		}
		$save_data["pmUser_id"] = isset($data["pmUser_id"]) ? $data["pmUser_id"] : null;
		$selectString = "
			{$key_field} as \"{$key_field}\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$schema}.{$proc_name}(
			    {$key_field} := :{$key_field},
			    {$query_part}
			    pmUser_id = :pmUser_id
			);
		";
		if (isset($data["debug_query"])) {
			print getDebugSQL($query, $save_data);
		}
		$result = $callObject->getFirstRowFromQuery($query, $save_data);
		if (!$result || !is_array($result)) {
			throw new Exception("При сохранении произошла ошибка");
		}
		if ($result[$key_field] > 0) {
			$result["success"] = true;
		}
		return $result;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveCmpCallCardDrugFromJSON(CmpCallCard_model $callObject, $data)
	{
		$result = [];
		$error = [];
		$doc_array_check = [];
		$doc_array = [];
		$callObject->load->model("DocumentUc_model", "du_model");
		$callObject->load->model("StorageZone_model", "sz_model");
		if (!empty($data["json_str"]) && $data["CmpCallCard_id"] > 0) {
			ConvertFromWin1251ToUTF8($data["json_str"]);
			$dt = (array)json_decode($data["json_str"]);
			foreach ($dt as $record) {
				if (!empty($record->DocumentUc_id) && !in_array($record->DocumentUc_id, $doc_array_check)) {
					//собираем идентификаторы документов которые участвуют в изменении данных
					$doc_array_check[] = $record->DocumentUc_id;
					$doc_array[] = ["doc_id" => $record->DocumentUc_id];
				}
				if (!empty($record->DocumentUc_id)) {
					//проверка статуса документа учета, редактирвоать можно только документы со статусом Новый
					$query = "
                        select dds.DrugDocumentStatus_Code
                        from
                            v_DocumentUc du
                            left join v_DrugDocumentStatus dds on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
                        where du.DocumentUc_id = :DocumentUc_id;
                    ";
					$status_code = $callObject->getFirstResultFromQuery($query, ["DocumentUc_id" => $record->DocumentUc_id]);
					if (empty($status_code)) {
						$error[] = "Не удалось получить статус документа списания";
					}
					if ($status_code != 1) {
						$error[] = "Редактирование запрещено";
					}
					if (count($error) > 0) {
						break;
					}
				}
				$drugDocumentType_Code = 25; // По умолчанию тип документа Списание медикаментов со склада на пациента. СМП
				$szLiable = false; // по дефолту считаем что укладка не передана на подотчет
				if (!empty($record->StorageZone_id)) {
					$drugDocumentType_Code = 26;// тип документа Списание медикаментов из укладки на пациента
				}
				if (!empty($record->StorageZone_id) && !empty($record->EmergencyTeam_id)) {
					//проверка - передано ли место хранения и связано ли оно с бригадой
					$query = "
                        select szl.StorageZoneLiable_id
                        from v_StorageZoneLiable szl
                        where szl.StorageZone_id = :StorageZone_id
                          and szl.StorageZoneLiable_ObjectId is not null
                          and szl.StorageZoneLiable_ObjectName = 'Бригада СМП'
                          and szl.StorageZoneLiable_endDate is null
						limit 1
                    ";
					$queryParams = [
						"StorageZone_id" => $record->StorageZone_id,
						"EmergencyTeam_id" => $record->EmergencyTeam_id
					];
					$szl = $callObject->queryResult($query, $queryParams);
					if (!is_array($szl)) {
						$error[] = "Не удалось проверить связь места хранения с бригадой";
					}
					if (count($error) > 0) {
						break;
					}
					if (count($szl) > 0) {
						$szLiable = true;
					}
				}
				//ищем подходящий документ учета
				$doc_id = $callObject->getDocSMPForCmpCallCardDrug([
					"Lpu_id" => !empty($record->Lpu_id) ? $record->Lpu_id : null,
					"Contragent_id" => !empty($record->Contragent_id) ? $record->Contragent_id : null,
					"Org_id" => !empty($record->Org_id) ? $record->Org_id : null,
					"Storage_id" => !empty($record->Storage_id) ? $record->Storage_id : null,
					"StorageZone_id" => !empty($record->StorageZone_id) ? $record->StorageZone_id : null,
					"EmergencyTeam_id" => !empty($record->EmergencyTeam_id) ? $record->EmergencyTeam_id : null,
					"DrugFinance_id" => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
					"WhsDocumentCostItemType_id" => !empty($record->WhsDocumentCostItemType_id) ? $record->WhsDocumentCostItemType_id : null,
					"DrugDocumentType_Code" => $drugDocumentType_Code,
					"StorageZoneLiable" => ($szLiable) ? 1 : null,
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (empty($doc_id)) {
					$error[] = "Не удалось определить данные документа списания.";
				}
				$exist = false;
				foreach ($doc_array as $doc) {
					if ($doc["doc_id"] == $doc_id) {
						$exist = true;
					}
				}
				if (!$exist) {
					$doc_array[] = ["doc_id" => $doc_id, "doc_type" => $drugDocumentType_Code, "szLiable" => $szLiable];
				}
				if (count($error) > 0) {
					break;
				}
				$str_id = !empty($record->DocumentUcStr_id) ? $record->DocumentUcStr_id : null;
				//сохранение или перезапись строки документа учета
				$docUcStrParams = [
					"DocumentUcStr_id" => $str_id,
					"DocumentUcStr_oid" => !empty($record->DocumentUcStr_oid) ? $record->DocumentUcStr_oid : null,
					"DocumentUc_id" => $doc_id,
					"Drug_id" => $record->Drug_id,
					"DrugFinance_id" => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
					"DrugNds_id" => 1, //1 - Без НДС
					"DocumentUcStr_Price" => !empty($record->CmpCallCardDrug_Cost) ? $record->CmpCallCardDrug_Cost : null,
					"DocumentUcStr_PriceR" => !empty($record->CmpCallCardDrug_Cost) ? $record->CmpCallCardDrug_Cost : null,
					"DocumentUcStr_EdCount" => !empty($record->CmpCallCardDrug_KolvoUnit) ? $record->CmpCallCardDrug_KolvoUnit : null,
					"DocumentUcStr_Count" => !empty($record->CmpCallCardDrug_Kolvo) ? $record->CmpCallCardDrug_Kolvo : null,
					"DocumentUcStr_Sum" => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
					"DocumentUcStr_SumR" => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
					"DocumentUcStr_SumNds" => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
					"DocumentUcStr_SumNdsR" => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
					"PrepSeries_id" => !empty($record->PrepSeries_id) ? $record->PrepSeries_id : null,
					"DocumentUcStr_IsNDS" => 1, //1 - Нет
					"GoodsUnit_id" => $record->GoodsUnit_id,
					"GoodsUnit_bid" => $record->GoodsUnit_bid,
					"DocumentUcStr_Reason" => "использование медикаментов на выезде бригады",
					"pmUser_id" => $data["pmUser_id"]
				];
				if ($drugDocumentType_Code == 26 && $szLiable) {
					// Статус строки - испонена
					$docUcStrParams["DrugDocumentStatus_id"] = $callObject->getObjectIdByCode("DrugDocumentStatus", 4);
				}
				$response = $callObject->saveObject("DocumentUcStr", $docUcStrParams);
				if (is_array($response)) {
					if (!empty($response["DocumentUcStr_id"])) {
						$str_id = $response["DocumentUcStr_id"];
					}
					if (!empty($response["Error_Msg"])) {
						$error[] = $response["Error_Msg"];
					}
				}
				if (empty($str_id)) {
					$error[] = "Не удалось определить данные строки документа списания.";
				}
				if (count($error) == 0) {
					switch ($record->state) {
						case "add":
						case "edit":
							$response = $callObject->saveObject("CmpCallCardDrug", [
								"CmpCallCardDrug_id" => $record->state == "add" ? 0 : $record->CmpCallCardDrug_id,
								"CmpCallCard_id" => $data["CmpCallCard_id"],
								"MedStaffFact_id" => !empty($record->MedStaffFact_id) ? $record->MedStaffFact_id : null,
								"CmpCallCardDrug_setDate" => !empty($record->CmpCallCardDrug_setDate) ? $callObject->formatDate($record->CmpCallCardDrug_setDate) : null,
								"CmpCallCardDrug_setTime" => !empty($record->CmpCallCardDrug_setTime) ? $record->CmpCallCardDrug_setTime : null,
								"LpuBuilding_id" => !empty($record->LpuBuilding_id) ? $record->LpuBuilding_id : null,
								"Storage_id" => !empty($record->Storage_id) ? $record->Storage_id : null,
								"DrugFinance_id" => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
								"WhsDocumentCostItemType_id" => !empty($record->WhsDocumentCostItemType_id) ? $record->WhsDocumentCostItemType_id : null,
								"Mol_id" => !empty($record->Mol_id) ? $record->Mol_id : null,
								"Drug_id" => $record->Drug_id,
								"CmpCallCardDrug_Cost" => !empty($record->CmpCallCardDrug_Cost) ? $record->CmpCallCardDrug_Cost : null,
								"CmpCallCardDrug_Kolvo" => !empty($record->CmpCallCardDrug_Kolvo) ? $record->CmpCallCardDrug_Kolvo : null,
								"GoodsUnit_id" => !empty($record->GoodsUnit_id) ? $record->GoodsUnit_id : null,
								"CmpCallCardDrug_KolvoUnit" => !empty($record->CmpCallCardDrug_KolvoUnit) ? $record->CmpCallCardDrug_KolvoUnit : null,
								"CmpCallCardDrug_Sum" => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
								"DocumentUcStr_oid" => !empty($record->DocumentUcStr_oid) ? $record->DocumentUcStr_oid : null,
								"DocumentUc_id" => $doc_id,
								"DocumentUcStr_id" => $str_id,
								"pmUser_id" => $data["pmUser_id"]
							]);
							break;
						case "delete":
							$response = $callObject->deleteCmpCallCardDrug([
								"CmpCallCardDrug_id" => $record->CmpCallCardDrug_id,
								"DocumentUcStr_id" => $record->DocumentUcStr_id,
								"pmUser_id" => $data["pmUser_id"]
							]);
							if (!empty($response["Error_Msg"])) {
								$error[] = $response["Error_Msg"];
							}
							break;
					}
					if (!empty($response["Error_Msg"])) {
						$error[] = $response["Error_Msg"];
					}
				}
				if (count($error) > 0) {
					break;
				}
			}
			if (count($error) == 0) {
				//резервируем медикаменты либо снимаем резерв
				foreach ($doc_array as $document) {
					if (!empty($document["doc_type"]) && $document["doc_type"] == 26) {
						$response = $callObject->du_model->removeDrugsFromPack([
							"DocumentUc_id" => $document["doc_id"],
							"pmUser_id" => $data["pmUser_id"]
						]);
						if (!empty($response["Error_Msg"])) {
							$error[] = $response["Error_Msg"];
							break;
						}
					} else {
						$response = $callObject->du_model->createReserve([
							"DocumentUc_id" => $document["doc_id"],
							"pmUser_id" => $data["pmUser_id"]
						]);
						if (!empty($response["Error_Msg"])) {
							$error[] = $response["Error_Msg"];
							break;
						}
					}
				}
			}
			if (count($error) == 0 && count($doc_array) > 0) {
				//удаляем пустые документы учета
				$docs_to_del = [];
				foreach ($doc_array as $doc) {
					$docs_to_del[] = $doc["doc_id"];
				}
				$response = $callObject->deleteEmptyDocumentUc($docs_to_del);
				if (!empty($response["Error_Msg"])) {
					$error[] = $response["Error_Msg"];
				}
			}
		}
		if (count($error) > 0) {
			$result["success"] = false;
			$result["Error_Msg"] = $error[0];
		} else {
			$result["success"] = true;
		}
		return array($result);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $record
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public static function saveCmpCallCardEvnOneDrugFromJSON(CmpCallCard_model $callObject, $record, $data)
	{
		$res = [];
		$IsSMPServer = $callObject->config->item("IsSMPServer");
		$needWorkWithMainDB = ($IsSMPServer) ? true : false;
		if ($IsSMPServer && !empty($data["useSMP"])) {
			$needWorkWithMainDB = false;
		}
		// сохраняем в основную базу если это СМП сервер (переключаемся на MAIN базу)
		if ($needWorkWithMainDB) {
			unset($callObject->db);
			try {
				//переключение базы в модели работает только так! так просто не работает: $callObject->load->database("main");
				$callObject->db = $callObject->load->database("main", true);
			} catch (Exception $e) {

			}
		}
		switch ($record->state) {
			case "add":
			case "edit":
				$EvnDrug_Lpu_id = $record->Lpu_id;
				if ($data["LpuBuilding_id"] > 0 && (!isset($EvnDrug_Lpu_id) || !($EvnDrug_Lpu_id > 0))) {
					$query = "
						select lb.Lpu_id
						from v_LpuBuilding lb
						where lb.LpuBuilding_id = :LpuBuilding_id
						limit 1
					";
					$LpuBuildingLpu = $callObject->queryResult($query, ["LpuBuilding_id" => $data["LpuBuilding_id"]]);
					if (!empty($LpuBuildingLpu[0]) && isset($LpuBuildingLpu[0]["Lpu_id"])) {
						$EvnDrug_Lpu_id = $LpuBuildingLpu[0]["Lpu_id"];
					}
				}
				$params = [
					"EvnDrug_id" => $record->state == "add" ? null : $record->EvnDrug_id,
					"EvnDrug_setDT" => DateTime::createFromFormat("d.m.Y H:i", $record->EvnDrug_setDate . " " . $record->EvnDrug_setTime),
					"Lpu_id" => $EvnDrug_Lpu_id,
					"LpuSection_id" => (!empty($record->LpuSection_id) && $record->LpuSection_id > 0) ? $record->LpuSection_id : null,
					"DrugNomen_id" => $record->DrugNomen_id,
					"Drug_id" => (!empty($record->Drug_id) && $record->Drug_id > 0) ? $record->Drug_id : null,
					"GoodsUnit_id" => $record->GoodsUnit_id,
					"CmpCallCard_id" => (!empty($record->CmpCallCard_id)) ? $record->CmpCallCard_id : $data["CmpCallCard_id"],
					"EmergencyTeam_id" => (!empty($record->EmergencyTeam_id) && $record->EmergencyTeam_id > 0) ? $record->EmergencyTeam_id : null,
					"EvnDrug_Comment" => $record->EvnDrug_Comment,
					"EvnDrug_Kolvo" => $record->EvnDrug_Kolvo,
					"EvnDrug_KolvoEd" => $record->EvnDrug_Kolvo, // копипаста по ТЗ #110814
					"EvnDrug_RealKolvo" => $record->EvnDrug_Kolvo, // копипаста по ТЗ #110814
					"pmUser_id" => $data["pmUser_id"],
					"Server_id" => $data["Server_id"]
				];
				if (isset($data["EvnDrug_id"]) && $data["EvnDrug_id"] > 0) {
					$params["EvnDrug_id"] = $data["EvnDrug_id"];
				}
				if (isset($data["DocumentUcStr_cid"]) && $data["DocumentUcStr_cid"] > 0) {
					$params["DocumentUcStr_cid"] = $data["DocumentUcStr_cid"];
				}
				if (isset($data["DocumentUc_cid"]) && $data["DocumentUc_cid"] > 0) {
					$params["DocumentUc_cid"] = $data["DocumentUc_cid"];
				}
				$response = $callObject->saveObject("EvnDrug", $params);
				break;
			case "delete":
				$response = $callObject->deleteCmpCallCardEvnDrug([
					"EvnDrug_id" => $record->EvnDrug_id,
					"pmUser_id" => $data["pmUser_id"]
				]);
				break;
		}
		if (!empty($response["Error_Msg"])) {
			$res["error"][] = $response["Error_Msg"];
			$res["success"] = false;
		}
		if ($needWorkWithMainDB) {
			unset($callObject->db);
			$callObject->db = $callObject->load->database("default", true);
			$callObject->db->throw_exception = false;
			switch ($record->state) {
				case "add":
				case "edit":
					// медикамент с таким же EvnDrug_id создаем в БД СМП.
					if (!empty($response["EvnDrug_id"])) {
						$data["useSMP"] = true;
						$data["EvnDrug_id"] = $response["EvnDrug_id"];
						$data["DocumentUcStr_cid"] = (isset($response["DocumentUcStr_cid"]) && !empty($response["DocumentUc_cid"])) ? $response["DocumentUcStr_cid"] : null;
						$data["DocumentUc_cid"] = (isset($response["DocumentUc_cid"]) && !empty($response["DocumentUc_cid"])) ? $response["DocumentUc_cid"] : null;
						$add_error = $callObject->saveCmpCallCardEvnOneDrugFromJSON($record, $data);
						if (!$add_error["success"]) {
							$res["success"] = false;
							foreach ($add_error["error"] as $err) {
								$res["error"][] = $err;
							}
						}
					}
					break;
				case "delete":
					// медикамент удаляем также в БД СМП.
					$data["useSMP"] = true;
					$add_error = $callObject->deleteCmpCallCardEvnDrug([
						"EvnDrug_id" => $record->EvnDrug_id,
						"pmUser_id" => $data["pmUser_id"]
					]);
					if (!$add_error["success"]) {
						$res["error"][] = $add_error["Error_Msg"];
						$res["success"] = false;
					}
					break;
			}
		}
		return $res;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveCmpCallCardEvnDrugFromJSON(CmpCallCard_model $callObject, $data)
	{
		$result = [];
		$error = [];
		if (!empty($data["json_str"]) && $data["CmpCallCard_id"] > 0) {
			ConvertFromWin1251ToUTF8($data["json_str"]);
			$dt = (array)json_decode($data["json_str"]);
			foreach ($dt as $record) {
				if (isset($record->EvnDrug_id) && $record->EvnDrug_id > 0) {
					$res = $callObject->saveCmpCallCardEvnOneDrugFromJSON($record, $data);
					if (!$res["success"]) {
						$error = $res["error"];
					}
					unset($data["useSMP"]);
					unset($data["EvnDrug_id"]);
				}
			}
		}
		if (count($error) > 0) {
			$result["success"] = false;
			$result["Error_Msg"] = $error[0];
		} else {
			$result["success"] = true;
		}
		return [$result];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public static function saveCmpCloseCardExpertResponseList(CmpCallCard_model $callObject, $data)
	{
		$rules = [
			["field" => "CmpCloseCard_id", "label" => "Идентификатор карты закрытия вызова СМП", "rules" => "required", "type" => "id"],
			["field" => "ExpertResponseList", "label" => "Список оценок", "rules" => "", "type" => "array", "default" => []],
			["field" => "pmUser_id", "rules" => "required", "label" => "Идентификатор пользователя", "type" => "id"],
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err, true);
		if (!empty($err)) {
			return $err;
		}
		foreach ($queryParams["ExpertResponseList"] as $expertResponse) {
			switch ($expertResponse["action"]) {
				case "add":
				case "edit":
					if ($expertResponse["value"]) {
						$expertResponse["CMPCloseCard_id"] = $queryParams["CmpCloseCard_id"];
						$expertResponse["pmUser_id"] = $queryParams["pmUser_id"];
						$callObject->saveCmpCloseCardExpertResponse($expertResponse);
					}
					break;
				case "del":
					$callObject->delCmpCloseCardExpertResponse($expertResponse);
					break;
			}
		}
		return true;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return mixed
	 */
	public static function saveCmpCloseCardExpertResponse(CmpCallCard_model $callObject, $data)
	{
		$procedure = (empty($data["CMPCloseCardExpertResponse_id"])) ? "p_CMPCloseCardExpertResponse_ins" : "p_CMPCloseCardExpertResponse_upd";
		$genQuery = $callObject->getParamsForSQLQuery($procedure, $data, false, false);
		$QueryParams = $genQuery["paramsArray"];
		$QueryFields = $genQuery["sqlParams"];
		$selectString = "
		    CMPCloseCardExpertResponse_id as \"CMPCloseCardExpertResponse_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$callObject->schema}.{$procedure}(
			    {$QueryFields}
			);
		";
		$result = $callObject->db->query($query, $QueryParams);
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveCmpCallCardSimpleDrugFromJSON(CmpCallCard_model $callObject, $data)
	{
		$result = [];
		$error = [];
		if (!empty($data["json_str"]) && $data["CmpCallCard_id"] > 0) {
			ConvertFromWin1251ToUTF8($data["json_str"]);
			$dt = (array)json_decode($data["json_str"]);
			foreach ($dt as $record) {
				if (isset($record->CmpCallCardDrug_id) && $record->CmpCallCardDrug_id > 0) {
					$res = $callObject->saveCmpCallCardSimpleOneDrugFromJSON($record, $data);
					if (!$res["success"]) {
						$error = $res["error"];
					}
					unset($data["useSMP"]);
					unset($data["EvnDrug_id"]);
				}
			}
		}
		if (count($error) > 0) {
			$result["success"] = false;
			$result["Error_Msg"] = $error[0];
		} else {
			$result["success"] = true;
		}
		return [$result];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $record
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function saveCmpCallCardSimpleOneDrugFromJSON(CmpCallCard_model $callObject, $record, $data)
	{
		$res["success"] = true;
		switch ($record->state) {
			case "add":
			case "edit":
				$response = $callObject->saveObject("CmpCallCardDrug", [
					"CmpCallCardDrug_id" => $record->state == "add" ? null : $record->CmpCallCardDrug_id,
					"CmpCallCard_id" => $data["CmpCallCard_id"],
					"CmpCallCardDrug_setDate" => !empty($record->CmpCallCardDrug_setDate) ? $callObject->formatDate($record->CmpCallCardDrug_setDate) : null,
					"CmpCallCardDrug_setTime" => !empty($record->CmpCallCardDrug_setTime) ? $record->CmpCallCardDrug_setTime : null,
					"LpuBuilding_id" => !empty($record->LpuBuilding_id) ? $record->LpuBuilding_id : null,
					"Drug_id" => !empty($record->Drug_id) ? $record->Drug_id : null,
					"DrugNomen_id" => $record->DrugNomen_id,
					"MedStaffFact_id" => !empty($record->MedStaffFact_id) ? $record->MedStaffFact_id : null,
					"CmpCallCardDrug_Comment" => $record->CmpCallCardDrug_Comment,
					"CmpCallCardDrug_Kolvo" => !empty($record->CmpCallCardDrug_Kolvo) ? $record->CmpCallCardDrug_Kolvo : null,
					"GoodsUnit_id" => !empty($record->GoodsUnit_id) ? $record->GoodsUnit_id : null,
					"CmpCallCardDrug_Sum" => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
					"pmUser_id" => $data["pmUser_id"]
				]);
				break;
			case "delete":
				if (empty($record->CmpCallCardDrug_id)) {
					return false;
				}
				$response = $callObject->deleteObject("CmpCallCardDrug", ["CmpCallCardDrug_id" => $record->CmpCallCardDrug_id]);
				if (!empty($response["Error_Msg"])) {
					$error[] = $response["Error_Msg"];
				}
				break;
		}
		if (!empty($response["Error_Msg"])) {
			$error[] = $response["Error_Msg"];
		}
		if (!empty($response["Error_Msg"])) {
			$res["error"][] = $response["Error_Msg"];
			$res["success"] = false;
		}
		return $res;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveCmpCallCardDiagArr(CmpCallCard_model $callObject, $data)
	{
		if ($callObject->regionNick == "kz" || !isset($data["CmpCloseCard_id"]) || empty($data["CmpCloseCard_id"])) {
			return false;
		}
		$res = ["success" => true];
		$selectString = "
			CLCD.Diag_id as \"Diag_id\",
			CLCD.CmpCloseCardDiag_id as \"CmpCloseCardDiag_id\",
			CLCD.DiagSetClass_id as \"DiagSetClass_id\"
		";
		$fromString = "{$callObject->schema}.CmpCloseCardDiag CLCD";
		$whereString = "
				CLCD.CmpCloseCard_id = :CmpCloseCard_id
			and (CLCD.DiagSetClass_id = 3 OR CLCD.DiagSetClass_id = 2)
		";
		$squery = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($squery, $data);
		if (!is_object($result)) {
			return false;
		}
		$oldDiags = $result->result("array");
		if ((count($data["arrDiag_sid"]) > 0) || (count($data["arrDiag_ooid"]) > 0)) {
			// диагнозы имеются? Добавляем  удаляем не найден
			$oldDiag_ids = [];
			$oldDelDiag_ids = [];
			foreach ($oldDiags as $Diag) {
				$oldDiag_ids[$Diag["Diag_id"]] = $Diag["Diag_id"]; // Имеющиеся дигнозы
				$oldDelDiag_ids[$Diag["Diag_id"]] = $Diag["CmpCloseCardDiag_id"];
			}
			foreach ($data["arrDiag_sid"] as $diag) {
				if (in_array($diag, $oldDiag_ids)) {
					unset($oldDelDiag_ids[$diag]);
				} else {
					$params = [
						"Diag_id" => $diag,
						"DiagSetClass_id" => 3, // сопутствующий
						"CmpCloseCard_id" => $data["CmpCloseCard_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$response = $callObject->actionCmpCloseCardDiag("add", $params);
				}
			}
			foreach ($data["arrDiag_ooid"] as $diag) {
				if (in_array($diag, $oldDiag_ids)) {
					unset($oldDelDiag_ids[$diag]);
				} else {
					$params = [
						"Diag_id" => $diag,
						"DiagSetClass_id" => 2, // осложнение основного
						"CmpCloseCard_id" => $data["CmpCloseCard_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$response = $callObject->actionCmpCloseCardDiag("add", $params);
				}
			}
			foreach ($oldDelDiag_ids as $Diag) {
				$params = ["CmpCloseCardDiag_id" => $Diag];
				$response = $callObject->actionCmpCloseCardDiag("del", $params);
				//Удаляем элементы предыдущего дерева решений, если оно существовало
			}
		} else {
			// диагнозы не пришли? удаляем все присутствующие
			foreach ($oldDiags as $Diag) {
				$params = ["CmpCloseCardDiag_id" => $Diag["CmpCloseCardDiag_id"]];
				$response = $callObject->actionCmpCloseCardDiag("del", $params);
				//Удаляем элементы предыдущего дерева решений, если оно существовало
			}
		}
		if (!empty($response["Error_Msg"])) {
			$error[] = $response["Error_Msg"];
		}
		if (!empty($response["Error_Msg"])) {
			$res["error"][] = $response["Error_Msg"];
			$res["success"] = false;
		}
		return $res;
	}

	public static function saveMedicalCareBudgTypeToCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		if (empty($data['CmpCallCard_id'])) {
			return false;
		}
		$selectString = "
			CCC.CmpCallCardInputType_id,
			PT.PayType_Code,
			CP.CmpProfile_Code,
			CLC.CmpCloseCard_id,
			ETS.EmergencyTeamSpec_Code,
			CCCU.CmpCallCardUsluga_id
		";
		$fromString = "
			v_CmpCallCard CCC
			left join v_PayType PT on PT.PayType_id = CCC.PayType_id
			left join {$callObject->schema}.v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
			left join v_CmpProfile CP on CCC.CmpProfile_bid = CP.CmpProfile_id
			left join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = CLC.EmergencyTeamSpec_id
			left join lateral (
				select CU.CmpCallCardUsluga_id
				from
			    	v_CmpCallCardUsluga CU
					left join v_UslugaComplex UC on CU.UslugaComplex_id = UC.UslugaComplex_id
				where CU.CmpCallCard_id = CCC.CmpCallCard_id
			      and UC.UslugaComplex_Code = 'A23.30.042.001'
			    limit 1
			) as CCCU on true
		";
		$whereString = "CCC.CmpCallCard_id = :CmpCallCard_id";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			limit 1
		";
		$cardData = $callObject->getFirstRowFromQuery($query, $data);
		if (!in_array($cardData["PayType_Code"], [3, 4])) {
			return false;
		}
		$code = 300;
		if (!empty($cardData["CmpCallCardUsluga_id"]) ||
			(!empty($cardData["CmpCallCardInputType_id"]) && $cardData["CmpProfile_Code"] == "П") ||
			(!empty($cardData["CmpCloseCard_id"]) && in_array($cardData["EmergencyTeamSpec_Code"], ["П", "ПТ", "Е"]))
		) {
			$code = 301;
		}
		$callObject->load->model("MedicalCareBudgType_model", "MedicalCareBudgType_model");
		$callObject->swUpdate("CmpCallCard", [
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"MedicalCareBudgType_id" => $callObject->MedicalCareBudgType_model->getMedicalCareBudgTypeIdByCode($code)
		], false);
		return true;
	}
}