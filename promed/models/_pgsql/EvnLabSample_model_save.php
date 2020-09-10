<?php

class EvnLabSample_model_save
{
	/**
	 * Сохранение пробы
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function saveLabSample(EvnLabSample_model $callObject, $data)
	{
		// 1. получаем данные заявки
		$funcParams = ["EvnLabRequest_id" => $data["EvnLabRequest_id"]];
		$lrdata = $callObject->getDataFromEvnLabRequest($funcParams);
		// создаём пробу в заявке
		$query = "          
            select
            	evnlabsample_id as \"EvnLabSample_id\",
            	error_code as \"Error_Code\",
                error_message as \"Error_Msg\"
			from p_EvnLabSample_ins(
				EvnLabRequest_id := :EvnLabRequest_id,
				RefSample_id := :RefSample_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnLabSample_Num := null,
				EvnLabSample_BarCode := null,
				MedService_id := :MedService_id,
				MedService_did := null,
				Evnlabsample_setDT := null,
				EvnLabSample_DelivDT := null,
				Lpu_did := null,
				Analyzer_id := null,
				LpuSection_did := null,
				MedPersonal_did := null,
				Lpu_aid := null,
				LpuSection_aid := null,
				MedPersonal_aid := null,
				MedPersonal_said := null,
				EvnLabSample_StudyDT := null,
				pmUser_id := :pmUser_id
            )
		";
		if (!empty($data["MedService_id"])) {
			// Lpu_id должно быть равно МО лаборатории, иначе в лаборатории не отобразится и может некорректно считаться номер пробы
			$sql = "
				select Lpu_id as \"Lpu_id\"
				from v_MedService 
				where MedService_id = :MedService_id
			";
			$sqlParams = ["MedService_id" => $data["MedService_id"]];
			$data["Lpu_id"] = $callObject->getFirstResultFromQuery($sql, $sqlParams);
			if (empty($data["Lpu_id"])) {
				throw new Exception("Ошибка при получении МО службы");
			}
		}
		$sqlParams = [
			"pmUser_id" => $data["pmUser_id"],
			"RefSample_id" => $data["RefSample_id"],
			"EvnLabRequest_id" => $data["EvnLabRequest_id"],
			"MedService_id" => $data["MedService_id"],
			"Lpu_id" => $data["Lpu_id"],
			"PersonEvn_id" => $lrdata["PersonEvn_id"],
			"Server_id" => $lrdata["Server_id"]
		];
		$result = $callObject->queryResult($query, $sqlParams);
		if (!is_array($result)) {
			throw new Exception("Ошибка при создании заявки");
		}
		if (!$callObject->isSuccessful($result)) {
			return $result;
		}
		collectEditedData("ins", "EvnLabSample", $result[0]["EvnLabSample_id"]);
		return $result;
	}

	/**
	 * Загрузка списка тестов для пробы и сохранение их во взятой пробе
	 * @param $data
	 * @param $lrdata
	 * @param null $needtests
	 * @param null $tests
	 * @return bool
	 * @throws Exception
	 */
	public static function saveLabSampleTests(EvnLabSample_model $callObject, $data, $lrdata, $needtests = null, $tests = null)
	{
		// 1. загружаем список тестов
		if (empty($tests)) {
			$funcParams = [
				"Lpu_id" => $data["Lpu_id"],
				"EvnDirection_id" => $lrdata["EvnDirection_id"],
				"EvnLabSample_id" => $data["EvnLabSample_id"],
				"needtests" => $needtests,
				"ingorePrescr" => !empty($data["ingorePrescr"]) ? true : false
			];
			$tests = $callObject->getLabSampleResultGrid($funcParams);
		}
		// 2. получаем родительские услуги пробы
		$funcParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$EvnUslugaPars = $callObject->getEvnUslugasRoot($funcParams);
		foreach ($EvnUslugaPars as $EvnUslugaPar) {
			$UslugaTest_pid = $EvnUslugaPar["EvnUslugaPar_id"];
			if (is_array($tests)) {
				$count = 0;
				$EvnUslugaRootXmlDataArray = [];
				$data['EvnPrescr_id'] = null;
				$data['PersonData'] = null;
				if (count($tests) > 0 && !empty($data["Analyzer_id"])) {
					// получаем необходимые данные для получения реф.значений
					$res = $callObject->common->GET("EvnPrescr/PrescrByDirection", [
						'EvnDirection_id' => $lrdata['EvnDirection_id']
					], "single");
					if (!$callObject->isSuccessful($res)) {
						throw new Exception($res["Error_Msg"]);
					}
					$data["EvnPrescr_id"] = $res["EvnPrescr_id"];
					if (empty($data["EvnPrescr_id"])) {
						$data["EvnPrescr_id"] = null;
					}
					if (!empty($data["EvnLabSample_setDT"])) {
						$data["EvnLabSample_setDT"] = explode(" ", $data["EvnLabSample_setDT"])[0];
					}
					$dt = [
						"Person_id" => $lrdata["Person_id"],
						"EvnPrescr_id" => $data["EvnPrescr_id"],
						"EvnLabSample_setDT" => $data["EvnLabSample_setDT"]
					];
					$person = $callObject->common->GET("Person/PersonDataForRefValues", $dt, "single");
					if (!$callObject->isSuccessful($person)) {
						throw new Exception($person["Error_Msg"]);
					}
					$data["PersonData"] = $person;
				}
				$UslugaComplex_ids = [];
				foreach ($tests as $test) {
					if ($test["UslugaTest_pid"] == $UslugaTest_pid) {
						$UslugaComplex_ids[] = $test["UslugaComplex_id"];
					}
				}
				$refvalues = [];
				$funcParams = [
					"EvnLabSample_setDT" => $data["EvnLabSample_setDT"],
					"MedService_id" => $lrdata["MedService_id"],
					"EvnDirection_id" => $lrdata["EvnDirection_id"],
					"Person_id" => $lrdata["Person_id"],
					"UslugaComplexTarget_id" => $EvnUslugaPar["UslugaComplex_id"],
					"UslugaComplex_ids" => json_encode($UslugaComplex_ids),
					"Analyzer_id" => $data["Analyzer_id"],
					"EvnPrescr_id" => $data["EvnPrescr_id"],
					"PersonData" => $data["PersonData"]
				];
				$resp_refvalues = $callObject->loadRefValues($funcParams);
				foreach ($resp_refvalues as $refvalue) {
					$refvalues[$refvalue["UslugaComplex_id"]] = $refvalue;
				}
				foreach ($tests as $test) {
					if ($test["UslugaTest_pid"] == $UslugaTest_pid) {
						if (empty($test["UslugaTest_id"]) && $test["inPrescr"] == 2) {
							$data["RefValues_id"] = null;
							$data["Unit_id"] = null;
							$data["UslugaTest_ResultQualitativeNorms"] = null;
							$data["UslugaTest_ResultQualitativeText"] = null;
							$data["UslugaTest_ResultLower"] = null;
							$data["UslugaTest_ResultUpper"] = null;
							$data["UslugaTest_ResultLowerCrit"] = null;
							$data["UslugaTest_ResultUpperCrit"] = null;
							$data["UslugaTest_ResultUnit"] = null;
							$data["UslugaTest_Comment"] = null;
							if (!empty($data["Analyzer_id"])) {
								if (!empty($refvalues[$test["UslugaComplex_id"]]["AnalyzerTestRefValues_id"])) {
									$data["RefValues_id"] = $refvalues[$test["UslugaComplex_id"]]["RefValues_id"];
									$data["Unit_id"] = $refvalues[$test["UslugaComplex_id"]]["Unit_id"];
									$data["UslugaTest_ResultQualitativeNorms"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultQualitativeNorms"];
									$UslugaTest_ResultQualitativeText = "";
									if (!empty($refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultQualitativeNorms"])) {
										$UslugaTest_ResultQualitativeNorms = json_decode($refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultQualitativeNorms"], true);
										if (is_array($UslugaTest_ResultQualitativeNorms)) {
											foreach ($UslugaTest_ResultQualitativeNorms as $UslugaTest_ResultQualitativeNorm) {
												if (!empty($UslugaTest_ResultQualitativeText)) {
													$UslugaTest_ResultQualitativeText .= ", ";
												}
												$UslugaTest_ResultQualitativeText .= $UslugaTest_ResultQualitativeNorm;
											}
										}
									}
									$data["UslugaTest_ResultQualitativeText"] = $UslugaTest_ResultQualitativeText;
									$data["UslugaTest_ResultLower"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultLower"];
									$data["UslugaTest_ResultUpper"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultUpper"];
									$data["UslugaTest_ResultLowerCrit"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultLowerCrit"];
									$data["UslugaTest_ResultUpperCrit"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultUpperCrit"];
									$data["UslugaTest_ResultUnit"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultUnit"];
									$data["UslugaTest_Comment"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_Comment"];
								} else {
									// получаем базовую единицу измерения
									$callObject->load->model("LisSpr_model");
									$funcParams = [
										"UslugaComplexTest_id" => $test["UslugaComplex_id"],
										"UslugaComplexTarget_id" => $EvnUslugaPar["UslugaComplex_id"],
										"UnitOld_id" => null,
										"MedService_id" => $lrdata["MedService_id"],
										"Analyzer_id" => $data["Analyzer_id"],
										"QuantitativeTestUnit_IsBase" => 2
									];
									$resp_unit = $callObject->LisSpr_model->loadTestUnitList($funcParams);
									if (!empty($resp_unit[0]["Unit_id"])) {
										$data["Unit_id"] = $resp_unit[0]["Unit_id"];
										$data["UslugaTest_ResultUnit"] = $resp_unit[0]["Unit_Name"];
									}
								}
							}
							$count++;
							$funcParams = [
								"UslugaTest_id" => null,
								"PersonEvn_id" => $lrdata["PersonEvn_id"],
								"Server_id" => $lrdata["Server_id"],
								"Lpu_id" => $lrdata["Lpu_id"],
								"UslugaComplex_id" => $test["UslugaComplex_id"],
								"PayType_id" => $lrdata["PayType_id"],
								"UslugaTest_pid" => $UslugaTest_pid,
								"EvnLabSample_id" => $data["EvnLabSample_id"],
								"EvnDirection_id" => null,
								"ResultDataJson" => json_encode([
									"EUD_value" => null,
									"EUD_lower_bound" => toUtf(trim($data["UslugaTest_ResultLower"])),
									"EUD_upper_bound" => toUtf(trim($data["UslugaTest_ResultUpper"])),
									"EUD_unit_of_measurement" => toUtf(trim($data["UslugaTest_ResultUnit"]))
								]),
								"UslugaTest_ResultLower" => $data["UslugaTest_ResultLower"],
								"UslugaTest_ResultUpper" => $data["UslugaTest_ResultUpper"],
								"UslugaTest_ResultLowerCrit" => $data["UslugaTest_ResultLowerCrit"],
								"UslugaTest_ResultUpperCrit" => $data["UslugaTest_ResultUpperCrit"],
								"UslugaTest_ResultQualitativeNorms" => $data["UslugaTest_ResultQualitativeNorms"],
								"UslugaTest_ResultQualitativeText" => $data["UslugaTest_ResultQualitativeText"],
								"RefValues_id" => $data["RefValues_id"],
								"Unit_id" => $data["Unit_id"],
								"UslugaTest_ResultValue" => null,
								"UslugaTest_ResultUnit" => $data["UslugaTest_ResultUnit"],
								"UslugaTest_ResultApproved" => null,
								"UslugaTest_Comment" => $data["UslugaTest_Comment"],
								"pmUser_id" => $data["pmUser_id"]
							];
							$callObject->saveUslugaTest($funcParams);
							if (!empty($test["UslugaComplex_ACode"])) {
								$EvnUslugaRootXmlDataArray[$test["UslugaComplex_ACode"]] = null;
							}
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Сохранение нового штрих-кода пробы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveNewEvnLabSampleBarCode(EvnLabSample_model $callObject, $data)
	{
		// 1. достаём данные из пробы
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				Lpu_id as \"Lpu_id\"
			from v_EvnLabSample
			where EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		$resp = $callObject->getFirstRowFromQuery($query, $data);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении данных пробы");
		}
		// 2. заменяем последние 4 на новый штрих код
		$data["Lpu_id"] = $resp["Lpu_id"];
		// 3. проверяем уникальность
		$response = $callObject->checkEvnLabSampleBarCodeUnique($data);
		if (!empty($response["Error_Msg"])) {
			return [$response];
		}
		// 4. обновляем в EvnLabSample
		$query = "
			update EvnLabSample
			set evnlabsample_barcode = :EvnLabSample_BarCode
			where evn_id = :EvnLabSample_id
		";
		$callObject->db->query($query, $data);
		collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
		// кэшируем статус проб в заявке
		$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		$funcParams = [
			"EvnLabRequest_id" => $resp["EvnLabRequest_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
		return [["success" => true]];
	}

	/**
	 * Сохранение нового номера пробы
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function saveNewEvnLabSampleNum(EvnLabSample_model $callObject, $data)
	{
		if (!($data["EvnLabSample_ShortNum"] >= 1000 && $data["EvnLabSample_ShortNum"] <= 9999)) {
			throw new Exception("Штрих-код должен быть 4-значным");
		}
		// 1. достаём данные из пробы
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				Lpu_id as \"Lpu_id\",
				EvnLabSample_Num as \"EvnLabSample_Num\"
			from v_EvnLabSample
			where EvnLabSample_id = :EvnLabSample_id
		";
		$resp = $callObject->getFirstRowFromQuery($query, $data);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении данных пробы");
		}
		$data["Lpu_id"] = $resp["Lpu_id"];
		// заменяем последние 4 цифры на новый номер
		$data["EvnLabSample_Num"] = mb_substr($resp["EvnLabSample_Num"], 0, mb_strlen($resp["EvnLabSample_Num"]) - 4) . $data["EvnLabSample_ShortNum"];
		// 3. проверяем уникальность
		$response = $callObject->checkEvnLabSampleNumUnique($data);
		if (!empty($response["Error_Msg"])) {
			return [$response];
		}
		// 4. обновляем в EvnLabSample
		$query = "
			update EvnLabSample
			set evnlabsample_num = :EvnLabSample_Num
			where evn_id = :EvnLabSample_id
		";
		$callObject->db->query($query, $data);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при изменении номера пробы");
		}
		if (!$callObject->isSuccessful($resp)) {
			return $resp;
		}
		collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
		// кэшируем статус проб в заявке
		$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		$funcParams = [
			"EvnLabRequest_id" => $resp["EvnLabRequest_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
		return [["success" => true]];
	}

	/**
	 * Сохранение отбраковки
	 * @param $data
	 * @return array
	 */
	public static function saveEvnLabSampleDefect(EvnLabSample_model $callObject, $data)
	{
		if (empty($data["EvnLabSample_id"])) {
			$query = "
				select EvnLabSample_id as \"EvnLabSample_id\"
				from v_EvnLabSample
				where EvnLabSample_BarCode = :EvnLabSample_BarCode
				  and Lpu_id = :Lpu_id
			";
			$data["EvnLabSample_id"] = $callObject->getFirstResultFromQuery($query, $data);
		}
		if (empty($data["EvnLabSample_id"])) {
			$query = "
				select MedService_Name as \"MedService_Name\"
				from v_MedService
				where MedService_id = :MedService_sid
				  and Lpu_id = :Lpu_id
			";
			$MedService_Name = $callObject->getFirstResultFromQuery($query, $data);
			return [
				"Error_Msg" => "Проба с указанным штрих кодом не найдена в списке проб пункта забора {$MedService_Name}. Проверьте, корректно ли указан штрих код пробы.",
				"YesNo" => true
			];
		}
		$MedService_sid = "";
		if ($callObject->regionNick == "vologda" && $data["MedServiceType_SysNick"] == "pzm") {
			$query = "
				select 
					els.LabSampleStatus_id as \"Status\",
					ms.MedService_Name as \"MedService_Name\"
				from
					v_EvnLabSample els
					left join v_MedService ms on ms.MedService_id = els.MedService_did
				where els.EvnLabSample_BarCode = :EvnLabSample_BarCode 
				  and els.Lpu_id = :Lpu_id
				  and els.MedService_did = :MedService_sid
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $data);
			$result = $result->result_array();
			if (empty($result[0]) || $result[0]["Status"] != 1) {
				$query = "
					select MedService_Name as \"MedService_Name\"
					from v_MedService
					where MedService_id = :MedService_sid
					  and Lpu_id = :Lpu_id
				";
				$MedService_Name = $callObject->getFirstResultFromQuery($query, $data);
				return [
					"Error_Msg" => "Проба с указанным штрих кодом не найдена в списке проб пункта забора {$MedService_Name}. Проверьте, корректно ли указан штрих код пробы.",
					"YesNo" => true
				];
			} else {
				$MedService_sid = "MedService_sid = :MedService_sid,";
			}
		}
		$query = "
			update EvnLabSample els
			set
				DefectCauseType_id = :DefectCauseType_id,
				{$MedService_sid}
				EvnLabSample_IsLIS = 1
			where els.Evn_id = :EvnLabSample_id
			  and els.Lpu_id = :Lpu_id
		";
		$res = $callObject->db->query($query, $data);
		if ($res) {
			collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
		}
		$funcParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$callObject->ReCacheLabSampleStatus($funcParams);
		return ["Error_Msg" => "", "EvnLabSample_id" => $data["EvnLabSample_id"], "success" => true];
	}

	/**
	 * Выбор анализатора для пробы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveLabSamplesAnalyzer(EvnLabSample_model $callObject, $data)
	{
		if (!empty($data["EvnLabSamples"])) {
			$EvnLabSamples = json_decode($data["EvnLabSamples"], true);
			if (count($EvnLabSamples) > 0) {
				// получаем только пробы по которым анализатор отличается от устанавливаемого, иначе будем делать ненужную работу.
				$EvnLabSamplesString = implode("','", $EvnLabSamples);
				$query = "
					select EvnLabSample_id as \"EvnLabSample_id\"
					from v_EvnLabSample
					where EvnLabSample_id in ('{$EvnLabSamplesString}')
					  and COALESCE(Analyzer_id, 0) != COALESCE(:Analyzer_id, 0)
				";
				$queryParams = ["Analyzer_id" => $data["Analyzer_id"]];
				$result = $callObject->queryResult($query, $queryParams);
				if (is_array($result)) {
					foreach ($result as $EvnLabSample) {
						$data["EvnLabSample_id"] = $EvnLabSample["EvnLabSample_id"];
						$callObject->saveLabSampleAnalyzer($data);
					}
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Выбор анализатора для пробы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveLabSampleAnalyzer(EvnLabSample_model $callObject, $data)
	{
		$query = "
			update EvnLabSample
			set Analyzer_id = :Analyzer_id
			where Evn_id = :EvnLabSample_id;
		";
		$response = $callObject->db->query($query, $data);
		if ($response) {
			collectEditedData("upd", "Analyzer", $data["Analyzer_id"]);
		}
		$lrdata = $callObject->getInfoLabSample($data);
		$data["RefSample_id"] = $lrdata["RefSample_id"];
		$data["EvnDirection_id"] = $lrdata["EvnDirection_id"];
		$tests = $callObject->getLabSampleTestsForChangeAnalyzerValues($data);

		$data["EvnPrescr_id"] = null;
		$data["PersonData"] = null;
		if (count($tests) > 0 && !empty($data["Analyzer_id"])) {
			// получаем необходимые данные для получения реф.значений
			$funcParams = ["EvnDirection_id" => $lrdata["EvnDirection_id"]];
			$res = $callObject->common->GET("EvnPrescr/PrescrByDirection", $funcParams, "single");
			if (!$callObject->isSuccessful($res)) {
				return $res;
			}
			$data["EvnPrescr_id"] = $res["EvnPrescr_id"];
			if (!$data["EvnPrescr_id"] > 0) {
				$data["EvnPrescr_id"] = null;
			}
			if (!empty($lrdata["EvnLabSample_setDT"])) {
				$lrdata["EvnLabSample_setDT"] = explode(" ", $lrdata["EvnLabSample_setDT"])[0];
			}
			$funcParams = [
				"Person_id" => $lrdata["Person_id"],
				"EvnPrescr_id" => $data["EvnPrescr_id"],
				"EvnLabSample_setDT" => $lrdata["EvnLabSample_setDT"]
			];
			$person = $callObject->common->GET("Person/PersonDataForRefValues", $funcParams, "single");
			if (!$callObject->isSuccessful($person)) {
				return $person;
			}
			$data["PersonData"] = $person;
		}
		$UslugaComplex_ids = [];
		foreach ($tests as $test) {
			$UslugaComplex_ids[$test["UslugaComplexTarget_id"]][] = $test["UslugaComplex_id"];
		}
		foreach (array_keys($UslugaComplex_ids) as $UslugaComplexTarget_id) {
			$refvalues = [];
			$funcParams = [
				"EvnLabSample_setDT" => $lrdata["EvnLabSample_setDT"],
				"MedService_id" => $lrdata["MedService_id"],
				"EvnDirection_id" => $lrdata["EvnDirection_id"],
				"Person_id" => $lrdata["Person_id"],
				"UslugaComplexTarget_id" => $UslugaComplexTarget_id,
				"UslugaComplex_ids" => json_encode($UslugaComplex_ids[$UslugaComplexTarget_id]),
				"Analyzer_id" => $data["Analyzer_id"],
				"EvnPrescr_id" => $data["EvnPrescr_id"],
				"PersonData" => $data["PersonData"]
			];
			$resp_refvalues = $callObject->loadRefValues($funcParams);
			foreach ($resp_refvalues as $refvalue) {
				$refvalues[$refvalue["UslugaComplex_id"]] = $refvalue;
			}
			foreach ($tests as $test) {
				if ($test["UslugaTest_ResultValue"] != null && $test["UslugaTest_ResultValue"] != "") {
					continue;
				}
				if ($test["UslugaComplexTarget_id"] == $UslugaComplexTarget_id) {
					$saveRefValues = [];
					$saveRefValues["RefValues_id"] = null;
					$saveRefValues["Unit_id"] = null;
					$saveRefValues["UslugaTest_ResultQualitativeNorms"] = null;
					$saveRefValues["UslugaTest_ResultLower"] = null;
					$saveRefValues["UslugaTest_ResultUpper"] = null;
					$saveRefValues["UslugaTest_ResultLowerCrit"] = null;
					$saveRefValues["UslugaTest_ResultUpperCrit"] = null;
					$saveRefValues["UslugaTest_ResultNorm"] = null;
					$saveRefValues["UslugaTest_ResultCrit"] = null;
					$saveRefValues["UslugaTest_ResultUnit"] = null;
					$saveRefValues["UslugaTest_Comment"] = null;
					if (!empty($data["Analyzer_id"])) {
						if (!empty($refvalues[$test["UslugaComplex_id"]]["AnalyzerTestRefValues_id"])) {
							$saveRefValues["RefValues_id"] = $refvalues[$test["UslugaComplex_id"]]["RefValues_id"];
							$saveRefValues["Unit_id"] = $refvalues[$test["UslugaComplex_id"]]["Unit_id"];
							$saveRefValues["UslugaTest_ResultQualitativeNorms"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultQualitativeNorms"];
							$saveRefValues["UslugaTest_ResultLower"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultLower"];
							$saveRefValues["UslugaTest_ResultUpper"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultUpper"];
							$saveRefValues["UslugaTest_ResultNorm"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultLower"];
							$saveRefValues["UslugaTest_ResultCrit"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultLowerCrit"];
							$saveRefValues["UslugaTest_ResultLowerCrit"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultLowerCrit"];
							$saveRefValues["UslugaTest_ResultUpperCrit"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultUpperCrit"];
							$saveRefValues["UslugaTest_ResultUnit"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_ResultUnit"];
							$saveRefValues["UslugaTest_Comment"] = $refvalues[$test["UslugaComplex_id"]]["UslugaTest_Comment"];
						} else {
							// получаем базовую единицу измерения
							$callObject->load->model("LisSpr_model");
							$funcParams = [
								"UslugaComplexTest_id" => $test["UslugaComplex_id"],
								"UslugaComplexTarget_id" => $test["UslugaComplexTarget_id"],
								"UnitOld_id" => null,
								"MedService_id" => $lrdata["MedService_id"],
								"Analyzer_id" => $data["Analyzer_id"],
								"QuantitativeTestUnit_IsBase" => 2
							];
							$resp_unit = $callObject->LisSpr_model->loadTestUnitList($funcParams);
							if (!empty($resp_unit[0]["Unit_id"])) {
								$saveRefValues["Unit_id"] = $resp_unit[0]["Unit_id"];
								$saveRefValues["UslugaTest_ResultUnit"] = $resp_unit[0]["Unit_Name"];
							}
						}
					}
					$data["UslugaTest_id"] = $test["UslugaTest_id"];
					$data["UslugaTest_ResultValue"] = "";
					$data["UslugaTest_Unit"] = "";
					$data["updateType"] = "";
					$data["UslugaTest_RefValues"] = json_encode($saveRefValues);
					$data["disableRecache"] = true;
					$callObject->updateResult($data);
				}
			}
		}
		$funcParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$callObject->ReCacheLabSampleIsOutNorm($funcParams);
		$callObject->ReCacheLabSampleStatus($funcParams);
		// кэшируем статус заявки
		$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		$funcParams = [
			"EvnLabRequest_id" => $lrdata["EvnLabRequest_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->EvnLabRequest_model->ReCacheLabRequestStatus($funcParams);
		// кэшируем статус проб в заявке
		$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
		return ["Error_Msg" => ""];
	}

	/**
	 * Функция создает родительскую услугу для остальных в EvnUslugaPar
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveEvnUslugaRoot(EvnLabSample_model $callObject, $data)
	{
		// проверяем если уже создано такое исследование, то новое не создаём
		$query = "
			select EvnUsluga_id as \"EvnUslugaPar_id\"
			from v_EvnUsluga
			where EvnDirection_id = :EvnDirection_id
			  and UslugaComplex_id = :UslugaComplex_id
		";
		$queryParams = [
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"EvnDirection_id" => $data["EvnDirection_id"]
		];
		$resp = $callObject->queryResult($query, $queryParams);
		if (!empty($resp[0]["EvnUslugaPar_id"])) {
			return [
				"new" => false,
				"EvnUslugaPar_id" => $resp[0]["EvnUslugaPar_id"]
			];
		}
		$funcParams = [
			"EvnUslugaPar_id" => null,
			"EvnUslugaPar_setDT" => null,
			"PersonEvn_id" => $data["PersonEvn_id"],
			"Server_id" => $data["Server_id"],
			"Lpu_id" => $data["Lpu_id"],
			"MedPersonal_id" => null,
			"LpuSection_uid" => null,
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"Mes_id" => !empty($data["Mes_id"]) ? $data["Mes_id"] : null,
			"Diag_id" => !empty($data["Diag_id"]) ? $data["Diag_id"] : null,
			"Usluga_id" => null,
			"PayType_id" => $data["PayType_id"],
			"UslugaPlace_id" => 1,
			"EvnUslugaPar_pid" => null,
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"EvnDirection_id" => $data["EvnDirection_id"],
			"EvnPrescr_id" => !empty($data["EvnPrescr_id"]) ? $data["EvnPrescr_id"] : null,
			"ResultDataJson" => "",
			"RefValues_id" => null,
			"Unit_id" => null,
			"EvnUslugaPar_Comment" => null,
			"isReloadCount" => null,
			"pmUser_id" => $data["pmUser_id"]
		];
		$resp = $callObject->saveEvnUslugaData($funcParams);
		if (!empty($data["EvnLabRequest_id"])) {
			// кэшируем названия услуг на заявке
			$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
			$funcParams = [
				"EvnLabRequest_id" => $data["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"],
				"EvnDirection_id" => $data["EvnDirection_id"]
			];
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaName($funcParams);
		}
		if (!empty($resp)) {
			return [
				"new" => true,
				"EvnUslugaPar_id" => $resp
			];
		}
		return [];
	}

	/**
	 * Сохранение параметров исследования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveResearch(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				EvnClass_id as \"EvnClass_id\",
				EvnClass_Name as \"EvnClass_Name\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_setDate as \"EvnUslugaPar_setDate\",
				EvnUslugaPar_setTime as \"EvnUslugaPar_setTime\",
				EvnUslugaPar_didDate as \"EvnUslugaPar_didDate\",
				EvnUslugaPar_didTime as \"EvnUslugaPar_didTime\",
				EvnUslugaPar_disDate as \"EvnUslugaPar_disDate\",
				EvnUslugaPar_disTime as \"EvnUslugaPar_disTime\",
				EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
				EvnUslugaPar_disDT as \"EvnUslugaPar_disDT\",
				EvnUslugaPar_didDT as \"EvnUslugaPar_didDT\",
				EvnUslugaPar_insDT as \"EvnUslugaPar_insDT\",
				EvnUslugaPar_updDT as \"EvnUslugaPar_updDT\",
				EvnUslugaPar_Index as \"EvnUslugaPar_Index\",
				EvnUslugaPar_Count as \"EvnUslugaPar_Count\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Person_id as \"Person_id\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				EvnUslugaPar_signDT as \"EvnUslugaPar_signDT\",
				EvnUslugaPar_IsArchive as \"EvnUslugaPar_IsArchive\",
				EvnUslugaPar_Guid as \"EvnUslugaPar_Guid\",
				EvnUslugaPar_IndexMinusOne as \"EvnUslugaPar_IndexMinusOne\",
				EvnStatus_id as \"EvnStatus_id\",
				EvnUslugaPar_statusDate as \"EvnUslugaPar_statusDate\",
				EvnUslugaPar_IsTransit as \"EvnUslugaPar_IsTransit\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				EvnUslugaPar_IsVizitCode as \"EvnUslugaPar_IsVizitCode\",
				EvnUslugaPar_IsInReg as \"EvnUslugaPar_IsInReg\",
				EvnUslugaPar_IsPaid as \"EvnUslugaPar_IsPaid\",
				MedStaffFact_id as \"MedStaffFact_id\",
				MedSpecOms_id as \"MedSpecOms_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				EvnUslugaPar_IndexRep as \"EvnUslugaPar_IndexRep\",
				EvnUslugaPar_IndexRepInReg as \"EvnUslugaPar_IndexRepInReg\",
				DiagSetClass_id as \"DiagSetClass_id\",
				Diag_id as \"Diag_id\",
				LpuDispContract_id as \"LpuDispContract_id\",
				EvnUslugaPar_IsMinusUsluga as \"EvnUslugaPar_IsMinusUsluga\",
				Mes_id as \"Mes_id\",
				UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
				UslugaExecutionType_id as \"UslugaExecutionType_id\",
				Registry_sid as \"Registry_sid\",
				Lpu_oid as \"Lpu_oid\",
				PrehospDirect_id as \"PrehospDirect_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				MedPersonal_did as \"MedPersonal_did\",
				TimeTablePar_id as \"TimeTablePar_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				Study_uid as \"Study_uid\",
				EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				EvnUslugaPar_ResultAppDate as \"EvnUslugaPar_ResultAppDate\",
				EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				EvnDirection_Num as \"EvnDirection_Num\",
				EvnDirection_setDT as \"EvnDirection_setDT\",
				MedProductCard_id as \"MedProductCard_id\",
				EvnRequest_id as \"EvnRequest_id\",
				EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\",
				DeseaseType_id as \"DeseaseType_id\",
				EvnUslugaPar_NumUsluga as \"EvnUslugaPar_NumUsluga\",
				StudyResult_id as \"StudyResult_id\",
				EvnUslugaPar_AnalyzerDate as \"EvnUslugaPar_AnalyzerDate\",
				TumorStage_id as \"TumorStage_id\",
				EvnUslugaPar_UslugaNum as \"EvnUslugaPar_UslugaNum\"
			from
				v_EvnUslugaPar
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";
		$queryParams = ["EvnUslugaPar_id" => $data["EvnUslugaPar_id"]];
		$EvnUslugaParData = $callObject->queryResult($query, $queryParams);
		if (empty($EvnUslugaParData[0]["EvnUslugaPar_id"])) {
			throw new Exception("Ошибка получения данных по услуге");
		}
		if (empty($data["MedPersonal_aid"]) && empty($data["MedPersonal_said"])) {
			throw new Exception("Должен быть выбран врач или ср. медперсонал");
		}
		$params = $EvnUslugaParData[0];
		$params["pmUser_id"] = $data["pmUser_id"];
		if (strtotime($data["EvnUslugaPar_setDate"]) > strtotime(date("Y-m-d")) + 24 * 60 * 60) {
			throw new Exception("Дата выполнения исследования не может быть позже текущей");
		}
		$EvnPrescr_Date = $callObject->common->GET("EvnPrescr/setDateByUslugaPar", ["EvnUslugaPar_id" => $data["EvnUslugaPar_id"]], "single");
		if (!$callObject->isSuccessful($EvnPrescr_Date)) {
			return $EvnPrescr_Date;
		}
		$EvnPrescr_Date = $EvnPrescr_Date["EvnPrescr_Date"];
		if (!empty($EvnPrescr_Date) && strtotime($data["EvnUslugaPar_setDate"]) < strtotime($EvnPrescr_Date)) {
			throw new Exception("Дата выполнения исследования не может быть раньше даты назначения");
		}
		$params["EvnUslugaPar_setDT"] = $data["EvnUslugaPar_setDate"];
		if (!empty($data["EvnUslugaPar_setTime"])) {
			$params["EvnUslugaPar_setDT"] .= " " . $data["EvnUslugaPar_setTime"];
		}
		$params["LpuSection_uid"] = $data["LpuSection_aid"];
		$params["MedPersonal_id"] = $data["MedPersonal_aid"];
		$params["MedPersonal_sid"] = $data["MedPersonal_said"];
		$params["MedStaffFact_id"] = null;
		$params["EvnUslugaPar_Comment"] = $data["EvnUslugaPar_Comment"];
		$params["EvnUslugaPar_IndexRep"] = $data["EvnUslugaPar_IndexRep"];
		$params["EvnUslugaPar_IndexRepInReg"] = $data["EvnUslugaPar_IndexRepInReg"];
		$callObject->saveEvnUslugaData($params);
		$funcParams = [
			"EvnLabSample_id" => $params["EvnLabSample_id"],
			"session" => $data["session"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->ReCacheLabRequestByLabSample($funcParams);
		return ["Error_Msg" => "", "EvnUslugaPar_id" => $data["EvnUslugaPar_id"], "success" => true];
	}

	/**
	 * Обновление только комментария исследования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveComment(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				EvnClass_id as \"EvnClass_id\",
				EvnClass_Name as \"EvnClass_Name\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_setDate as \"EvnUslugaPar_setDate\",
				EvnUslugaPar_setTime as \"EvnUslugaPar_setTime\",
				EvnUslugaPar_didDate as \"EvnUslugaPar_didDate\",
				EvnUslugaPar_didTime as \"EvnUslugaPar_didTime\",
				EvnUslugaPar_disDate as \"EvnUslugaPar_disDate\",
				EvnUslugaPar_disTime as \"EvnUslugaPar_disTime\",
				EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
				EvnUslugaPar_disDT as \"EvnUslugaPar_disDT\",
				EvnUslugaPar_didDT as \"EvnUslugaPar_didDT\",
				EvnUslugaPar_insDT as \"EvnUslugaPar_insDT\",
				EvnUslugaPar_updDT as \"EvnUslugaPar_updDT\",
				EvnUslugaPar_Index as \"EvnUslugaPar_Index\",
				EvnUslugaPar_Count as \"EvnUslugaPar_Count\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Person_id as \"Person_id\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				EvnUslugaPar_signDT as \"EvnUslugaPar_signDT\",
				EvnUslugaPar_IsArchive as \"EvnUslugaPar_IsArchive\",
				EvnUslugaPar_Guid as \"EvnUslugaPar_Guid\",
				EvnUslugaPar_IndexMinusOne as \"EvnUslugaPar_IndexMinusOne\",
				EvnStatus_id as \"EvnStatus_id\",
				EvnUslugaPar_statusDate as \"EvnUslugaPar_statusDate\",
				EvnUslugaPar_IsTransit as \"EvnUslugaPar_IsTransit\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				EvnUslugaPar_IsVizitCode as \"EvnUslugaPar_IsVizitCode\",
				EvnUslugaPar_IsInReg as \"EvnUslugaPar_IsInReg\",
				EvnUslugaPar_IsPaid as \"EvnUslugaPar_IsPaid\",
				MedStaffFact_id as \"MedStaffFact_id\",
				MedSpecOms_id as \"MedSpecOms_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				EvnUslugaPar_IndexRep as \"EvnUslugaPar_IndexRep\",
				EvnUslugaPar_IndexRepInReg as \"EvnUslugaPar_IndexRepInReg\",
				DiagSetClass_id as \"DiagSetClass_id\",
				Diag_id as \"Diag_id\",
				LpuDispContract_id as \"LpuDispContract_id\",
				EvnUslugaPar_IsMinusUsluga as \"EvnUslugaPar_IsMinusUsluga\",
				Mes_id as \"Mes_id\",
				UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
				UslugaExecutionType_id as \"UslugaExecutionType_id\",
				Registry_sid as \"Registry_sid\",
				Lpu_oid as \"Lpu_oid\",
				PrehospDirect_id as \"PrehospDirect_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				MedPersonal_did as \"MedPersonal_did\",
				TimeTablePar_id as \"TimeTablePar_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				Study_uid as \"Study_uid\",
				EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				EvnUslugaPar_ResultAppDate as \"EvnUslugaPar_ResultAppDate\",
				EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				EvnDirection_Num as \"EvnDirection_Num\",
				EvnDirection_setDT as \"EvnDirection_setDT\",
				MedProductCard_id as \"MedProductCard_id\",
				EvnRequest_id as \"EvnRequest_id\",
				EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\",
				DeseaseType_id as \"DeseaseType_id\",
				EvnUslugaPar_NumUsluga as \"EvnUslugaPar_NumUsluga\",
				StudyResult_id as \"StudyResult_id\",
				EvnUslugaPar_AnalyzerDate as \"EvnUslugaPar_AnalyzerDate\",
				TumorStage_id as \"TumorStage_id\",
				EvnUslugaPar_UslugaNum as \"EvnUslugaPar_UslugaNum\"
			from
				v_EvnUslugaPar
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";
		$queryParams = ["EvnUslugaPar_id" => $data["EvnUslugaPar_id"]];
		$EvnUslugaParData = $callObject->queryResult($query, $queryParams);
		if (empty($EvnUslugaParData[0]["EvnUslugaPar_id"])) {
			throw new Exception("Ошибка получения данных по услуге");
		}
		$params = $EvnUslugaParData[0];
		$params["pmUser_id"] = $data["pmUser_id"];
		$params["EvnUslugaPar_Comment"] = $data["EvnUslugaPar_Comment"];
		$callObject->saveEvnUslugaData($params);
		// обновляем протокол
		$funcParams = [
			"EvnLabSample_id" => $params["EvnLabSample_id"],
			"session" => $data["session"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->ReCacheLabRequestByLabSample($funcParams);
		return ["Error_Msg" => "", "EvnUslugaPar_id" => $data["EvnUslugaPar_id"]];
	}

	/**
	 * Обновление только выполения исследования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveEvnUslugaDone(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				eup.EvnClass_id as \"EvnClass_id\",
				eup.EvnClass_Name as \"EvnClass_Name\",
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eup.EvnUslugaPar_setDate as \"EvnUslugaPar_setDate\",
				eup.EvnUslugaPar_setTime as \"EvnUslugaPar_setTime\",
				eup.EvnUslugaPar_didDate as \"EvnUslugaPar_didDate\",
				eup.EvnUslugaPar_didTime as \"EvnUslugaPar_didTime\",
				eup.EvnUslugaPar_disDate as \"EvnUslugaPar_disDate\",
				eup.EvnUslugaPar_disTime as \"EvnUslugaPar_disTime\",
				eup.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				eup.EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				eup.Lpu_id as \"Lpu_id\",
				eup.Server_id as \"Server_id\",
				eup.PersonEvn_id as \"PersonEvn_id\",
				eup.EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
				eup.EvnUslugaPar_disDT as \"EvnUslugaPar_disDT\",
				eup.EvnUslugaPar_didDT as \"EvnUslugaPar_didDT\",
				eup.EvnUslugaPar_insDT as \"EvnUslugaPar_insDT\",
				eup.EvnUslugaPar_updDT as \"EvnUslugaPar_updDT\",
				eup.EvnUslugaPar_Index as \"EvnUslugaPar_Index\",
				eup.EvnUslugaPar_Count as \"EvnUslugaPar_Count\",
				eup.pmUser_insID as \"pmUser_insID\",
				eup.pmUser_updID as \"pmUser_updID\",
				eup.Person_id as \"Person_id\",
				eup.Morbus_id as \"Morbus_id\",
				eup.EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				eup.pmUser_signID as \"pmUser_signID\",
				eup.EvnUslugaPar_signDT as \"EvnUslugaPar_signDT\",
				eup.EvnUslugaPar_IsArchive as \"EvnUslugaPar_IsArchive\",
				eup.EvnUslugaPar_Guid as \"EvnUslugaPar_Guid\",
				eup.EvnUslugaPar_IndexMinusOne as \"EvnUslugaPar_IndexMinusOne\",
				eup.EvnStatus_id as \"EvnStatus_id\",
				eup.EvnUslugaPar_statusDate as \"EvnUslugaPar_statusDate\",
				eup.EvnUslugaPar_IsTransit as \"EvnUslugaPar_IsTransit\",
				eup.PayType_id as \"PayType_id\",
				eup.Usluga_id as \"Usluga_id\",
				eup.MedPersonal_id as \"MedPersonal_id\",
				eup.UslugaPlace_id as \"UslugaPlace_id\",
				eup.Lpu_uid as \"Lpu_uid\",
				eup.LpuSection_uid as \"LpuSection_uid\",
				eup.EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				eup.Org_uid as \"Org_uid\",
				eup.UslugaComplex_id as \"UslugaComplex_id\",
				eup.EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				eup.MedPersonal_sid as \"MedPersonal_sid\",
				eup.EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				eup.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				eup.EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				eup.MesOperType_id as \"MesOperType_id\",
				eup.EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				eup.EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				eup.EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				eup.EvnPrescr_id as \"EvnPrescr_id\",
				eup.EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
				eup.EvnCourse_id as \"EvnCourse_id\",
				eup.EvnUslugaPar_IsVizitCode as \"EvnUslugaPar_IsVizitCode\",
				eup.EvnUslugaPar_IsInReg as \"EvnUslugaPar_IsInReg\",
				eup.EvnUslugaPar_IsPaid as \"EvnUslugaPar_IsPaid\",
				eup.MedStaffFact_id as \"MedStaffFact_id\",
				eup.MedSpecOms_id as \"MedSpecOms_id\",
				eup.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				eup.EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				eup.EvnUslugaPar_IndexRep as \"EvnUslugaPar_IndexRep\",
				eup.EvnUslugaPar_IndexRepInReg as \"EvnUslugaPar_IndexRepInReg\",
				eup.DiagSetClass_id as \"DiagSetClass_id\",
				eup.Diag_id as \"Diag_id\",
				eup.LpuDispContract_id as \"LpuDispContract_id\",
				eup.EvnUslugaPar_IsMinusUsluga as \"EvnUslugaPar_IsMinusUsluga\",
				eup.Mes_id as \"Mes_id\",
				eup.UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
				eup.UslugaExecutionType_id as \"UslugaExecutionType_id\",
				eup.Registry_sid as \"Registry_sid\",
				eup.Lpu_oid as \"Lpu_oid\",
				eup.PrehospDirect_id as \"PrehospDirect_id\",
				eup.LpuSection_did as \"LpuSection_did\",
				eup.Lpu_did as \"Lpu_did\",
				eup.Org_did as \"Org_did\",
				eup.MedPersonal_did as \"MedPersonal_did\",
				eup.TimeTablePar_id as \"TimeTablePar_id\",
				eup.EvnLabSample_id as \"EvnLabSample_id\",
				eup.Study_uid as \"Study_uid\",
				eup.EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				eup.EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				eup.EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				eup.EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				eup.EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				eup.EvnUslugaPar_ResultAppDate as \"EvnUslugaPar_ResultAppDate\",
				eup.EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				eup.EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				eup.EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				eup.EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				eup.RefValues_id as \"RefValues_id\",
				eup.Unit_id as \"Unit_id\",
				eup.EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				eup.EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				eup.EvnDirection_Num as \"EvnDirection_Num\",
				eup.EvnDirection_setDT as \"EvnDirection_setDT\",
				eup.MedProductCard_id as \"MedProductCard_id\",
				eup.EvnRequest_id as \"EvnRequest_id\",
				eup.EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\",
				eup.DeseaseType_id as \"DeseaseType_id\",
				eup.EvnUslugaPar_NumUsluga as \"EvnUslugaPar_NumUsluga\",
				eup.StudyResult_id as \"StudyResult_id\",
				eup.EvnUslugaPar_AnalyzerDate as \"EvnUslugaPar_AnalyzerDate\",
				eup.TumorStage_id as \"TumorStage_id\",
				eup.EvnUslugaPar_UslugaNum as \"EvnUslugaPar_UslugaNum\",
				{$data['Evn_setDT']} as \"Evn_setDT\",
				ms.LpuSection_id as \"LpuSection_uid\"
			from
				v_EvnUslugaPar eup
				left join v_EvnLabRequest elr on eup.EvnDirection_id = elr.EvnDirection_id
				left join v_MedService ms on elr.MedService_id = ms.MedService_id
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";
		$queryParams = ["EvnUslugaPar_id" => $data["EvnUslugaPar_id"]];
		$EvnUslugaParData = $callObject->queryResult($query, $queryParams);
		if (empty($EvnUslugaParData[0]["EvnUslugaPar_id"])) {
			throw new Exception("Ошибка получения данных по услуге");
		}
		$params = $EvnUslugaParData[0];
		$params["Lpu_id"] = $data["Lpu_id"];
		$params["pmUser_id"] = $data["pmUser_id"];
		$params["EvnUslugaPar_setDT"] = $params["Evn_setDT"];
		$params["EvnUslugaPar_pid"] = $data["EvnUslugaPar_pid"];
		$params["LpuSection_uid"] = $data["LpuSection_uid"];
		$params["MedPersonal_id"] = $data["MedPersonal_id"];
		$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		$params["noNeedDefineUslugaParams"] = true;
		$callObject->saveEvnUslugaData($params);
		return ["Error_Msg" => "", "EvnUslugaPar_id" => $data["EvnUslugaPar_id"]];
	}

	/**
	 * Сохраняет результаты анализов в услугу (с созданием услуги, если она еще не существует)
	 * Возвращает идентификатор услуги EvnUslugaPar_id
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function saveEvnUslugaData(EvnLabSample_model $callObject, $data)
	{
		$procedure = ($data["EvnUslugaPar_id"] > 0) ? "p_EvnUslugaPar_upd" : "p_EvnUslugaPar_ins";
		if (empty($data["PayType_id"])) {
			$PayType_SysNick = $callObject->getPayTypeSysNick();
			$query = "
				select PayType_id as \"PayType_id\"
				from v_PayType
				where PayType_SysNick = '{$PayType_SysNick}'
				limit 1
			";
			$data["PayType_id"] = $callObject->getFirstResultFromQuery($query);
		}
		if (empty($data["MedStaffFact_id"]) && !empty($data["MedPersonal_id"]) && !empty($data["LpuSection_uid"])) {
			$funcParams = [
				"MedPersonal_id" => $data["MedPersonal_id"],
				"LpuSection_id" => $data["LpuSection_uid"]
			];
			$MedStaffFact = $callObject->common->GET("MedStaffFact/Id", $funcParams, "single");
			if (!$callObject->isSuccessful($MedStaffFact)) {
				throw new Exception($MedStaffFact["Error_Msg"]);
			}
			$data["MedStaffFact_id"] = (isset($MedStaffFact["MedStaffFact_id"]) && $MedStaffFact["MedStaffFact_id"] > 0) ? $MedStaffFact["MedStaffFact_id"] : null;
		}
		$data["EvnUslugaPar_setDT"] = (!empty($data["EvnUslugaPar_setDT"])) ? explode(" ", $data["EvnUslugaPar_setDT"])[0] : null;
		$funcParams = [
			"EvnDirection_id" => $data["EvnDirection_id"],
			"EvnPrescr_id" => !empty($data["EvnPrescr_id"]) ? $data["EvnPrescr_id"] : null,
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"EvnUslugaPar_pid" => $data["EvnUslugaPar_pid"],
			"EvnUslugaPar_setDT" => $data["EvnUslugaPar_setDT"]
		];
		if (!empty($data["noNeedDefineUslugaParams"])) {
			$uslugaParams = $callObject->common->GET("EvnPrescr/defineUslugaParams", $funcParams, "single");
			if (!$callObject->isSuccessful($uslugaParams)) {
				$err = $uslugaParams["Error_Msg"];
				throw new Exception($err, 400);
			}
			$data["EvnPrescr_id"] = ($uslugaParams["EvnPrescr_id"] > 0) ? $uslugaParams["EvnPrescr_id"] : null;
			$data["EvnUslugaPar_pid"] = ($uslugaParams["EvnUslugaPar_pid"] > 0) ? $uslugaParams["EvnUslugaPar_pid"] : null;
		}

		//доработки по https://redmine.swan.perm.ru/issues/119069 - добавим TumorStage_id, взяв его из EvnLabRequest
		$TumorStage_id = null;
		$query_get_Tumor = "
			select ELR.TumorStage_id as \"TumorStage_id\"
			from
				v_EvnLabSample ELS
				inner join v_EvnLabRequest ELR on ELR.EvnLabRequest_id = ELS.EvnLabRequest_id
			where ELS.EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		if (!empty($data["EvnLabSample_id"])) {
			$result_get_Tumor = $callObject->db->query($query_get_Tumor, ["EvnLabSample_id" => $data["EvnLabSample_id"]]);
			if (is_object($result_get_Tumor)) {
				$result_get_Tumor = $result_get_Tumor->result("array");
				if (is_array($result_get_Tumor) && count($result_get_Tumor) > 0) {
					$TumorStage_id = $result_get_Tumor[0]["TumorStage_id"];
				}
			}
		}
		if (!empty($data["EvnDirection_id"]) && empty($data["EvnUslugaPar_pid"])) {
			$query = "
				select EvnDirection_pid as \"EvnUslugaPar_pid\"
				from v_EvnDirection_all
				where EvnDirection_id = :EvnDirection_id
				limit 1
			";
			$pid = $callObject->queryResult($query, $data);
			if (isset($pid[0]["EvnUslugaPar_pid"])) {
				$data["EvnUslugaPar_pid"] = $pid[0]["EvnUslugaPar_pid"];
			}
		}
		$selectString = "
			evnuslugapar_id as \"EvnUslugaPar_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
				Evnuslugapar_id := :EvnUslugaPar_id,
				Evnuslugapar_pid := :EvnUslugaPar_pid,
				Evnuslugapar_setDT := :EvnUslugaPar_setDT,
				Lpu_id := :Lpu_id,
				MedPersonal_id := :MedPersonal_id,
				MedPersonal_sid := :MedPersonal_sid,
				MedStaffFact_id := :MedStaffFact_id,
				LpuSection_uid := :LpuSection_uid,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				UslugaComplex_id := :UslugaComplex_id,
				Mes_id := :Mes_id,
				Diag_id := :Diag_id,
				TumorStage_id := :TumorStage_id,
				EvnDirection_id := :EvnDirection_id,
				Usluga_id := :Usluga_id,
				PayType_id := :PayType_id,
				UslugaPlace_id := :UslugaPlace_id,
				EvnUslugaPar_Kolvo := 1,
				EvnUslugaPar_Result := :EvnUslugaPar_Result,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				EvnUslugaPar_Comment := :EvnUslugaPar_Comment,
				EvnUslugaPar_IndexRep := :EvnUslugaPar_IndexRep,
				EvnUslugaPar_IndexRepInReg := :EvnUslugaPar_IndexRepInReg,
				EvnLabSample_id := :EvnLabSample_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := :EvnPrescr_id,
				PrehospDirect_id := :PrehospDirect_id,
				pmUser_id := :pmUser_id,
				EvnUslugaPar_IsSigned := :EvnUslugaPar_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnUslugaPar_signDT := :EvnUslugaPar_signDT
			)
		";
		if (!empty($data["EvnUslugaPar_IsSigned"]) && $data["EvnUslugaPar_IsSigned"] == 2) {
			// раз обновляем, значит подпись становится не актуальной
			$data["EvnUslugaPar_IsSigned"] = 1;
		}
		$params = [
			"EvnUslugaPar_id" => $data["EvnUslugaPar_id"],
			"EvnUslugaPar_pid" => $data["EvnUslugaPar_pid"],
			"EvnUslugaPar_setDT" => !empty($data["EvnUslugaPar_setDT"]) ? $data["EvnUslugaPar_setDT"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"MedPersonal_id" => $data["MedPersonal_id"],
			"MedPersonal_sid" => !empty($data["MedPersonal_sid"]) ? $data["MedPersonal_sid"] : null,
			"MedStaffFact_id" => !empty($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null,
			"LpuSection_uid" => $data["LpuSection_uid"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"Mes_id" => !empty($data["Mes_id"]) ? $data["Mes_id"] : null,
			"Diag_id" => !empty($data["Diag_id"]) ? $data["Diag_id"] : null,
			"TumorStage_id" => $TumorStage_id,
			"Usluga_id" => $data["Usluga_id"],
			"PayType_id" => $data["PayType_id"],
			"UslugaPlace_id" => $data["UslugaPlace_id"],
			"EvnDirection_id" => $data["EvnDirection_id"],
			"EvnUslugaPar_Result" => null,
			"RefValues_id" => !empty($data["RefValues_id"]) ? $data["RefValues_id"] : null,
			"Unit_id" => !empty($data["Unit_id"]) ? $data["Unit_id"] : null,
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"pmUser_id" => $data["pmUser_id"],
			"isReloadCount" => !empty($data["isReloadCount"]) ? $data["isReloadCount"] : null,
			"EvnUslugaPar_Index" => isset($data["EvnUslugaPar_Index"]) ? $data["EvnUslugaPar_Index"] : null,
			"EvnUslugaPar_Comment" => $data["EvnUslugaPar_Comment"],
			"EvnUslugaPar_IndexRep" => (!empty($data["EvnUslugaPar_IndexRep"])) ? $data["EvnUslugaPar_IndexRep"] : 0,
			"EvnUslugaPar_IndexRepInReg" => (!empty($data["EvnUslugaPar_IndexRepInReg"])) ? $data["EvnUslugaPar_IndexRepInReg"] : 1,
			"EvnPrescr_id" => (!empty($data["EvnPrescr_id"])) ? $data["EvnPrescr_id"] : null,
			"PrehospDirect_id" => (isset($data["PrehospDirect_id"]) && !empty($data["PrehospDirect_id"])) ? $data["PrehospDirect_id"] : null,
			"EvnUslugaPar_IsSigned" => (!empty($data["EvnUslugaPar_IsSigned"])) ? $data["EvnUslugaPar_IsSigned"] : null,
			"pmUser_signID" => (!empty($data["pmUser_signID"])) ? $data["pmUser_signID"] : null,
			"EvnUslugaPar_signDT" => (!empty($data["EvnUslugaPar_signDT"])) ? $data["EvnUslugaPar_signDT"] : null
		];
		/**@var CI_DB_result $dbresponse */
		$dbresponse = $callObject->db->query($query, $params);
		if (!is_object($dbresponse)) {
			throw new Exception("При создании факта оказания услуги произошла ошибка: " . implode(";", $callObject->db->error()));
		}
		$result = $dbresponse->result_array();
		$save_ok = EvnLabSample_model::save_ok($result);
		if (!$save_ok) {
			throw new Exception("При создании факта оказания услуги произошла ошибка: {$result[0]["Error_Code"]} {$result[0]["Error_Msg"]}");
		}
		if (isset($result[0])) {
			if (in_array(getRegionNick(), ["perm", "kaluga", "ufa", "astra"]) && !empty($uslugaParams["needRecalcKSGKPGKOEF"])) {
				$res = $callObject->common->POST("EvnSection/recalcKSGKPGKOEF", ["EvnSection_id" => $uslugaParams["EvnUslugaPar_pid"]], "single");
				if (!$callObject->isSuccessful($res)) {
					throw new Exception($res["Error_Msg"], 400);
				}
			}
			collectEditedData($procedure, "EvnUslugaPar", $data["EvnUslugaPar_id"]);
			return $result[0]["EvnUslugaPar_id"];
		}
		return true;
	}

	/**
	 * Сохраняет тесты
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function saveUslugaTest(EvnLabSample_model $callObject, $data)
	{
		$procedure = ($data["UslugaTest_id"] > 0) ? "dbo.p_uslugatest_upd" : "dbo.p_uslugatest_ins";
		if (empty($data["PayType_id"])) {
			$PayType_SysNick = $callObject->getPayTypeSysNick();
			$query = "
				select PayType_id as \"PayType_id\"
				from v_PayType
				where PayType_SysNick = '{$PayType_SysNick}'
				limit 1
			";
			$data["PayType_id"] = $callObject->getFirstResultFromQuery($query);
		}
		$data["UslugaTest_setDT"] = (empty($data["UslugaTest_setDT"])) ? null : $data["UslugaTest_setDT"];
		if (empty($data["ResultDataJson"])) {
			$data["ResultDataJson"] = json_encode([
				"EUD_value" => null,
				"EUD_lower_bound" => toUtf(trim($data["UslugaTest_ResultLower"])),
				"EUD_upper_bound" => toUtf(trim($data["UslugaTest_ResultUpper"])),
				"EUD_unit_of_measurement" => toUtf(trim($data["UslugaTest_ResultUnit"]))
			]);
		}
		$selectString = "
			uslugatest_id as \"UslugaTest_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
		    select {$selectString}
			from {$procedure}(
				UslugaTest_id := :UslugaTest_id, -- bigint
				UslugaTest_pid := :UslugaTest_pid,       -- bigint
				UslugaTest_rid := :UslugaTest_pid,       -- bigint
				UslugaTest_setDT := :UslugaTest_setDT,                   -- date
				Lpu_id := :Lpu_id,                           -- bigint
				Server_id := :Server_id,                     -- bigint
				PersonEvn_id := :PersonEvn_id,               -- bigint
				UslugaComplex_id := :UslugaComplex_id,
				EvnDirection_id := :EvnDirection_id,
				PayType_id := :PayType_id,                   -- bigint
				UslugaTest_Kolvo := 1,
				UslugaTest_Result := :UslugaTest_Result, -- bigint
				UslugaTest_ResultLower := :UslugaTest_ResultLower,
				UslugaTest_ResultUpper := :UslugaTest_ResultUpper,
				UslugaTest_ResultLowerCrit := :UslugaTest_ResultLowerCrit,
				UslugaTest_ResultUpperCrit := :UslugaTest_ResultUpperCrit,
				UslugaTest_ResultQualitativeNorms := :UslugaTest_ResultQualitativeNorms,
				UslugaTest_ResultQualitativeText := :UslugaTest_ResultQualitativeText,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				UslugaTest_ResultValue := cast(:UslugaTest_ResultValue as varchar),
				UslugaTest_ResultUnit := :UslugaTest_ResultUnit,
				UslugaTest_ResultApproved := :UslugaTest_ResultApproved,
				UslugaTest_Comment := :UslugaTest_Comment,
				EvnLabSample_id := :EvnLabSample_id,
				pmUser_id := :pmUser_id
			)
		";
		$params = [
			"UslugaTest_id" => $data["UslugaTest_id"],
			"UslugaTest_pid" => $data["UslugaTest_pid"],
			"UslugaTest_setDT" => !empty($data["UslugaTest_setDT"]) ? $data["UslugaTest_setDT"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"PayType_id" => $data["PayType_id"],
			"EvnDirection_id" => $data["EvnDirection_id"],
			"UslugaTest_Result" => $data["ResultDataJson"],
			"UslugaTest_ResultLower" => $data["UslugaTest_ResultLower"],
			"UslugaTest_ResultUpper" => $data["UslugaTest_ResultUpper"],
			"UslugaTest_ResultLowerCrit" => $data["UslugaTest_ResultLowerCrit"],
			"UslugaTest_ResultUpperCrit" => $data["UslugaTest_ResultUpperCrit"],
			"UslugaTest_ResultQualitativeNorms" => $data["UslugaTest_ResultQualitativeNorms"],
			"UslugaTest_ResultQualitativeText" => $data["UslugaTest_ResultQualitativeText"],
			"RefValues_id" => !empty($data["RefValues_id"]) ? $data["RefValues_id"] : null,
			"Unit_id" => !empty($data["Unit_id"]) ? $data["Unit_id"] : null,
			"UslugaTest_ResultValue" => ($data["UslugaTest_ResultValue"]),
			"UslugaTest_ResultUnit" => $data["UslugaTest_ResultUnit"],
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"pmUser_id" => $data["pmUser_id"],
			"isReloadCount" => !empty($data["isReloadCount"]) ? $data["isReloadCount"] : null,
			"UslugaTest_Index" => isset($data["UslugaTest_Index"]) ? $data["UslugaTest_Index"] : null,
			"UslugaTest_ResultApproved" => $data["UslugaTest_ResultApproved"],
			"UslugaTest_Comment" => $data["UslugaTest_Comment"]
		];
		/**@var CI_DB_result $dbresponse */
		$dbresponse = $callObject->db->query($query, $params);
		if (!is_object($dbresponse)) {
			throw new Exception("При создании факта оказания услуги произошла ошибка: " . implode(";", $callObject->db->error()));
		}
		$result = $dbresponse->result_array();
		$save_ok = EvnLabSample_model::save_ok($result);
		if (!$save_ok) {
			throw new Exception("При создании факта оказания услуги произошла ошибка: {$result[0]["Error_Code"]} {$result[0]["Error_Msg"]}");
		}
		if (isset($result[0])) {
			collectEditedData($procedure, "UslugaTest", $result[0]["UslugaTest_id"]);
			return $result[0]["UslugaTest_id"];
		}
		return true;
	}

	/**
	 * Сохранение исселодвания
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function saveLabSampleResearches(EvnLabSample_model $callObject, $data)
	{
		$labSamples = [];
		$callObject->elslog->add("");
		// получаем данные заявки
		$lrdata = $callObject->getDataFromEvnLabRequest(["EvnLabSample_id" => $data["EvnLabSample_id"]]);
		// получаем биоматериалы из текущей пробы
		$query = "
			select distinct
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				rs.RefMaterial_id as \"RefMaterial_id\"
			from
				v_EvnUslugaPar eup
				left join lateral (
					select rs.RefMaterial_id
					from
						v_UslugaComplexMedService ucms
						left join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
						inner join v_RefSample rs on rs.RefSample_id = COALESCE(ucms_child.RefSample_id, ucms.RefSample_id)
					where ucms.UslugaComplex_id = eup.UslugaComplex_id
					  and ucms.MedService_id = :MedService_id
					  and ucms.UslugaComplexMedService_pid is null
                    limit 1    
				) as rs on true
			where eup.EvnLabSample_id = :EvnLabSample_id
			  and eup.EvnDirection_id = :EvnDirection_id
		";
		$queryParams = [
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"EvnDirection_id" => $lrdata["EvnDirection_id"],
			"MedService_id" => $lrdata["MedService_id"]
		];
		$resp = $callObject->queryResult($query, $queryParams);
		$labSamples[$data["EvnLabSample_id"]] = [
			"RefMaterials" => []
		];
		if (empty($resp[0]["EvnUslugaPar_id"])) {
			$labSamples[$data["EvnLabSample_id"]]["empty"] = true;
		}
		foreach ($resp as $respone) {
			if (!in_array($respone["RefMaterial_id"], $labSamples[$data["EvnLabSample_id"]]["RefMaterials"])) {
				$labSamples[$data["EvnLabSample_id"]]["RefMaterials"][] = $respone["RefMaterial_id"];
			}
		}

		if (is_string($data['researches'])) {
			$data['researches'] = json_decode($data['researches'], true);
		}

		foreach ($data["researches"] as $UslugaComplexMedService_id) {
			$needNewEvnLabSample = false;
			$lrdata["EvnLabSample_id"] = null;
			$RefMats = [];
			// пробуем получить биоматериал по заказываемому исследоваию
			$query = "
				select distinct rs.RefMaterial_id as \"RefMaterial_id\"
				from
					v_UslugaComplexMedService ucms
					left join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
					inner join v_RefSample rs on rs.RefSample_id = COALESCE(ucms_child.RefSample_id, ucms.RefSample_id)
				where ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
			";
			$queryParams = ["UslugaComplexMedService_id" => $UslugaComplexMedService_id];
			$resp = $callObject->queryResult($query, $queryParams);
			if (!empty($resp)) {
				foreach ($resp as $respone) {
					$RefMats[] = $respone["RefMaterial_id"];
				}
				// проверяем, что в пробе есть такой биоматериал, или проба пустая
				foreach ($labSamples as $key => $value) {
					if (!empty($value["empty"])) {
						// если пустая берём её
						$lrdata["EvnLabSample_id"] = $key;
						break;
					}
					foreach ($value["RefMaterials"] as $refMaterial) {
						if (in_array($refMaterial, $RefMats)) {
							// подходит
							$lrdata["EvnLabSample_id"] = $key;
							break;
						}
						if (!empty($lrdata["EvnLabSample_id"])) {
							// нашлась
							break;
						}
					}
				}
				if (empty($lrdata["EvnLabSample_id"])) {
					$needNewEvnLabSample = true;
				}
			} else {
				// проверяем, что в пробе нет биоматериалов
				foreach ($labSamples as $key => $value) {
					if (count($value["RefMaterials"]) == 0) {
						// подходит
						$lrdata["EvnLabSample_id"] = $key;
						break;
					}
				}
				if (empty($lrdata["EvnLabSample_id"])) {
					$needNewEvnLabSample = true;
				}
			}
			if ($needNewEvnLabSample) {
				// создаём пробу, запоминаем биоматериалы, которые есть в пробе
				$funcParams = [
					"EvnLabRequest_id" => $data["EvnLabRequest_id"],
					"MedService_id" => $data["MedService_id"],
					"RefSample_id" => $data["RefSample_id"],
					"Lpu_id" => $data["Lpu_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$resp = $callObject->saveLabSample($funcParams);
				if (empty($resp[0]["EvnLabSample_id"])) {
					throw new Exception("Ошибка сохранения пробы" . (!empty($resp[0]["Error_Msg"]) ? ": " . $resp[0]["Error_Msg"] : ""));
				}
				$lrdata["EvnLabSample_id"] = $resp[0]["EvnLabSample_id"];
				$labSamples[$lrdata["EvnLabSample_id"]] = [
					"RefMaterials" => [],
					"created" => true
				];
			}
			// запоминаем биоматериалы в пробе
			unset($labSamples[$lrdata["EvnLabSample_id"]]["empty"]);
			foreach ($RefMats as $RefMatOne) {
				if (!in_array($RefMatOne, $labSamples[$lrdata["EvnLabSample_id"]]["RefMaterials"])) {
					$labSamples[$lrdata["EvnLabSample_id"]]["RefMaterials"][] = $RefMatOne;
				}
			}
			// сохраяняем корневую услугу, связанную с пробой
			// получаем услугу и службу
			$query = "
				select
					MedService_id as \"MedService_id\",
					UslugaComplex_id as \"UslugaComplex_id\"
				from v_UslugaComplexMedService
				where UslugaComplexMedService_id = :UslugaComplexMedService_id
			";
			$queryParams = ["UslugaComplexMedService_id" => $UslugaComplexMedService_id];
			$uslugaData = $callObject->queryResult($query, $queryParams);
			if (!empty($uslugaData[0]["UslugaComplex_id"])) {
				$lrdata["UslugaComplex_id"] = $uslugaData[0]["UslugaComplex_id"];
			}
			$lrdata["pmUser_id"] = $data["pmUser_id"];
			// сохраняем выполнение услуги
			$uslugaRoot = $callObject->saveEvnUslugaRoot($lrdata);
			if ($uslugaRoot["new"] == false) {
				continue; // пропускаем добавление исследования, раз исследование уже есть
			}
			$EvnUslugaPar_id = $uslugaRoot["EvnUslugaPar_id"];
			// сохраняем состав
			$query = "
				select distinct
					COALESCE(ucms_usluga.UslugaComplex_id, ucms_usluga_parent.UslugaComplex_id) as \"UslugaComplex_id\"
				from
					v_UslugaComplexMedService ucms_usluga_parent
					left join v_UslugaComplexMedService ucms_usluga on ucms_usluga.UslugaComplexMedService_pid = ucms_usluga_parent.UslugaComplexMedService_id
					inner join lis.v_AnalyzerTest at_child on at_child.UslugaComplexMedService_id = COALESCE(ucms_usluga.UslugaComplexMedService_id, ucms_usluga_parent.UslugaComplexMedService_id)
					left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
					inner join lis.v_Analyzer a on a.Analyzer_id = COALESCE(at.Analyzer_id, at_child.Analyzer_id)
					left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
				where ucms_usluga_parent.UslugaComplexMedService_id = :UslugaComplexMedService_id
				  and COALESCE(at.UslugaComplexMedService_id, 0) = COALESCE(ucms_usluga.UslugaComplexMedService_pid, 0)
				  and COALESCE(at_child.AnalyzerTest_IsNotActive, 1) = 1
				  and COALESCE(at.AnalyzerTest_IsNotActive, 1) = 1
				  and COALESCE(a.Analyzer_IsNotActive, 1) = 1
				  and (at_child.AnalyzerTest_endDT >= dbo.tzgetdate() or at_child.AnalyzerTest_endDT is null)
				  and (uctest.UslugaComplex_endDT >= dbo.tzgetdate() or uctest.UslugaComplex_endDT is null)
			";
			$queryParams = ["UslugaComplexMedService_id" => $UslugaComplexMedService_id];
			$tests = $callObject->queryResult($query, $queryParams);
			foreach ($tests as &$test) {
				$test["UslugaTest_pid"] = $EvnUslugaPar_id;
			}
			$funcParams = [
				"EvnLabRequest_id" => $lrdata["EvnLabRequest_id"],
				"EvnLabSample_id" => $lrdata["EvnLabSample_id"],
				"tests" => $tests,
				"Lpu_id" => $data["Lpu_id"],
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->prescrTest($funcParams);
		}
		$newLabSamples = [];
		foreach ($labSamples as $key => $value) {
			// возвращаем все созданные пробы
			if (!empty($value["created"])) {
				$newLabSamples[] = $key;
			}
		}
		return ["Error_Msg" => "", "newLabSamples" => $newLabSamples, "success" => true];
	}

	/**
	 * Сохранение комментария к пробе
	 * @param $data
	 * @return array
	 */
	public static function saveEvnLabSampleComment(EvnLabSample_model $callObject, $data)
	{
		$query = "
			update els
			set EvnLabSample_Comment = :EvnLabSample_Comment
			from EvnLabSample els
			where els.Evn_id = :EvnLabSample_id
		";
		$res = $callObject->db->query($query, $data);
		if ($res) {
			collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
		}
		return ["Error_Msg" => "", "EvnLabSample_id" => $data["EvnLabSample_id"]];
	}

	/**
	 * Сохранение результата контрольного измерения
	 */
	function saveQcSampleTest(EvnLabSample_model $callObject, $data) {

		$params = [
			'UslugaTest_ResultValue' => $data['UslugaTest_ResultValue'],
			'EvnLabSample_setDT' => $data['EvnLabSample_setDT'],
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $callObject->getPromedUserId()
		];

		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_UslugaTest_ins(
				UslugaTest_ResultValue := :UslugaTest_ResultValue,
				UslugaTest_setDT := :EvnLabSample_setDT,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $callObject->getFirstRowFromQuery($query, $params);
		return $result;
	}

	public static function saveUslugaMedTypeLink(EvnLabSample_model $callObject, $Evn_id, $UslugaMedType_id, $pmUser_id)
	{
		if (getRegionNick() === 'kz') {
			$callObject->load->model('UslugaMedType_model');

			$result = $callObject->UslugaMedType_model->saveUslugaMedTypeLink([
				'UslugaMedType_id' => $UslugaMedType_id,
				'Evn_id' => $Evn_id,
				'pmUser_id' => $pmUser_id
			]);

			if (!$callObject->isSuccessful($result)) {
				throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
			}
		}
	}
}