<?php

class EvnLabSample_model_common
{
	/**
	 * Массовое одобрение результатов проб
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function approveEvnLabSampleResults(EvnLabSample_model $callObject, $data)
	{
		$response = ["Error_Msg" => ""];
		$EvnUslugaParChanged = [];
		$approvedCount = 0;
		// 1. идём по пробам
		if (!empty($data["EvnLabSamples"])) {
			$arrayId = json_decode($data["EvnLabSamples"]);
			$one = (count($arrayId) == 1) ? true : false;
			foreach ($arrayId as $id) {
				// 2. достаём результаты пробы
				$query = "
					select
						eup.UslugaTest_id as \"UslugaTest_id\",
						eup.UslugaTest_Result as \"UslugaTest_Result\",
						eup.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
						eup.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
						eup.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
						eup.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
						eup.UslugaTest_pid as \"UslugaTest_pid\"
					from v_UslugaTest eup
					where EvnLabSample_id = :EvnLabSample_id
					  and UslugaTest_ResultValue is not null
					  and UslugaTest_ResultValue <> ''
				";
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($query, ["EvnLabSample_id" => $id]);
				$operated = false;
				$resultCount = 0;
				if (is_object($result)) {
					$resp = $result->result_array();
					foreach ($resp as $respone) {
						$resultCount++;
						$data["UslugaTest_ResultApproved"] = 1;
						if (isset($data["onlyNormal"]) && $data["onlyNormal"] == 2) {
							$data["UslugaTest_ResultApproved"] = 2;
						} else {
							if (!empty($respone["UslugaTest_ResultQualitativeNorms"])) {
								$UslugaTest_ResultQualitativeNorms = json_decode($respone["UslugaTest_ResultQualitativeNorms"], true);
								array_walk_recursive($UslugaTest_ResultQualitativeNorms, "ConvertFromUTF8ToWin1251");
								if ((is_array($UslugaTest_ResultQualitativeNorms) && in_array($respone["UslugaTest_ResultValue"], $UslugaTest_ResultQualitativeNorms)) || $one) {
									$data["UslugaTest_ResultApproved"] = 2;
								}
							} else {
								// только числовые нормы можно сравнить
								if (is_numeric(trim(str_replace(",", ".", $respone["UslugaTest_ResultValue"]))) && !$one) {
									if (
										(floatval(str_replace(",", ".", $respone["UslugaTest_ResultValue"])) >= floatval(str_replace(",", ".", $respone["UslugaTest_ResultLower"])) || !isset($respone["UslugaTest_ResultLower"])) &&
										(floatval(str_replace(",", ".", $respone["UslugaTest_ResultValue"])) <= floatval(str_replace(",", ".", $respone["UslugaTest_ResultUpper"])) || !isset($respone["UslugaTest_ResultUpper"]))
									) {
										$data["UslugaTest_ResultApproved"] = 2;
									}
								} else {
									$data["UslugaTest_ResultApproved"] = 2;
								}
							}
						}
						if ($data["UslugaTest_ResultApproved"] == 2) {
							if (!in_array($respone["UslugaTest_pid"], $EvnUslugaParChanged)) {
								$EvnUslugaParChanged[] = $respone["UslugaTest_pid"];
							}
							$query = "
								update UslugaTest
								set UslugaTest_ResultApproved = 2
								where UslugaTest_id = :UslugaTest_id
							";
							/**@var CI_DB_result $res */
							$res = $callObject->db->query($query, ["UslugaTest_id" => $respone["UslugaTest_id"]]);
							if ($res) {
								collectEditedData("upd", "UslugaTest", $respone["UslugaTest_id"]);
							}
							$operated = true;
						}
					}
				}
				if ($operated) {
					$approvedCount++;
					if (!empty($EvnUslugaParChanged)) {
						$funcParams = [
							"EvnUslugaParChanged" => $EvnUslugaParChanged,
							"session" => $data["session"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$callObject->onChangeApproveResults($funcParams);
					}
					$funcParams = ["EvnLabSample_id" => $id];
					$callObject->ReCacheLabSampleStatus($funcParams);
					$funcParams = [
						"EvnLabSample_id" => $id,
						"session" => $data["session"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->ReCacheLabRequestByLabSample($funcParams);
					// если проба стала одобренной и в ней не заполнены данные о враче, то заполняем.
					$funcParams = [
						"EvnLabSample_id" => $id,
						"session" => $data["session"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->setEvnLabSampleDone($funcParams);
				} elseif ($resultCount > 0) {
					$response["Error_Msg"] = "Нельзя одобрить пробу, т.к. отсутствуют результаты в пределах нормальных значений";
				} else {
					$response["Error_Msg"] = "Нельзя одобрить пробу, т.к. отсутствуют результаты";
				}
			}
		}
		return ($approvedCount < 1) ? $response : ["Error_Msg" => ""];
	}

	/**
	 * Одобрение результатов
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function approveResults(EvnLabSample_model $callObject, $data)
	{
		// только где есть результат
		$filter = "
			and ut.UslugaTest_ResultValue is not null
			and ut.UslugaTest_ResultValue <> ''
		";
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"EvnLabSample_id" => $data["EvnLabSample_id"]
		];
		if (isset($data["UslugaTest_id"])) {
			$filter .= " and ut.UslugaTest_id = :UslugaTest_id";
			$params["UslugaTest_id"] = $data["UslugaTest_id"];
		}
		if (!empty($data["UslugaTest_ids"])) {
			$UslugaTest_ids = json_decode($data["UslugaTest_ids"]);
			if (!empty($UslugaTest_ids)) {
				$filter .= " and ut.UslugaTest_id in (" . implode(",", $UslugaTest_ids) . ")";
			}
		}
		$query = "
			select
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_pid as \"UslugaTest_pid\",
				ls.Person_id as \"Person_id\"
			from
				v_UslugaTest ut
				inner join v_EvnLabSample ls on ut.EvnLabSample_id = ls.EvnLabSample_id
			where ls.EvnLabSample_id = :EvnLabSample_id
			  {$filter}
		";
		$resp_eup = $callObject->queryResult($query, $params);
		$EvnUslugaParChanged = [];
		foreach ($resp_eup as $respone) {
			if (!in_array($respone["UslugaTest_pid"], $EvnUslugaParChanged)) {
				$EvnUslugaParChanged[] = $respone["UslugaTest_pid"];
			}
			$query = "
				update UslugaTest
				set UslugaTest_ResultApproved = 2
				where UslugaTest_id = :UslugaTest_id
			";
			$res = $callObject->db->query($query, ["UslugaTest_id" => $respone["UslugaTest_id"]]);
			if ($res) {
				collectEditedData("upd", "UslugaTest", $respone["UslugaTest_id"]);
			}
			if (in_array($callObject->getRegionNick(), ["perm", "ufa"])) {
				$query = "
					select 
						ls.Person_id as \"Person_id\",
						ut.UslugaTest_pid as \"UslugaTest_pid\",
						ut.PersonEvn_id as \"PersonEvn_id\",
						ut.Server_id as \"Server_id\",
						ut.Lpu_id as \"Lpu_id\",
						eup.MedPersonal_id as \"MedPersonal_id\",
						COALESCE(t.Diag_id, d.Diag_id) as \"Diag_id\",
						uc.UslugaComplex_Code as \"UslugaComplex_Code\",
						ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
						ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\"
					from v_UslugaTest ut
						inner join v_EvnLabSample ls on ut.EvnLabSample_id = ls.EvnLabSample_id
						inner join v_EvnUslugaPar eup on eup.EvnUslugaPar_id = ut.UslugaTest_pid
						left join v_EvnLabRequest t on t.EvnLabRequest_id = ls.EvnLabRequest_id
						left join v_EvnDirection_all d on d.EvnDirection_id = t.EvnDirection_id
						left join v_UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
					where UslugaTest_id = :UslugaTest_id
				";
				/**@var CI_DB_result $resp */
				$resp = $callObject->db->query($query, ["UslugaTest_id" => $respone["UslugaTest_id"]]);
				$resp = (is_object($resp)) ? $resp->result_array() : [];
				if (count($resp) > 0) {
					$UslugaTest_ResultValue = trim(str_replace(",", ".", $resp[0]['UslugaTest_ResultValue']));
					if (is_numeric($UslugaTest_ResultValue)) {
						$UslugaTest_ResultValue = floatval($UslugaTest_ResultValue);
						if (
							($resp[0]['UslugaComplex_Code'] == 'A09.28.006.001' && $resp[0]['UslugaTest_ResultUnit'] == 'мкмоль/л' && $UslugaTest_ResultValue > 97) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.28.009.001' && $resp[0]['UslugaTest_ResultUnit'] == 'ммоль/л' && $UslugaTest_ResultValue > 8.2) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.28.001.006' && $UslugaTest_ResultValue > 5) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.028.006.002' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.28.006.002' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.028.006.003' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.28.006.003' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.05.020.001' && $resp[0]['UslugaTest_ResultUnit'] == 'мкмоль/л' && $UslugaTest_ResultValue > 97) ||
							($resp[0]['UslugaComplex_Code'] == 'A09.28.003' && $resp[0]['UslugaTest_ResultUnit'] == 'г/л' && $UslugaTest_ResultValue > 0.033)
						) {
							$callObject->load->library("swMorbus");
							$res = [
								"data" => $resp[0],
								"session" => $data["session"],
								"pmUser_id" => $data["pmUser_id"]
							];
							swMorbus::checkAndSaveEvnNotifyNephroFromLab($res);
						}
					}
				}
			}
		}
		if (!empty($EvnUslugaParChanged)) {
			$funcParams = [
				"EvnUslugaParChanged" => $EvnUslugaParChanged,
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->onChangeApproveResults($funcParams);
		}
		$callObject->ReCacheLabSampleStatus($data);
		// если проба стала одобренной и в ней не заполнены данные о враче, то заполняем.
		$funcParams = [
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"session" => $data["session"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->setEvnLabSampleDone($funcParams);
		$funcParams = [
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"session" => $data["session"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$resultReCache = $callObject->ReCacheLabRequestByLabSample($funcParams);
		return ["Error_Msg" => "", "UslugaExecutionType_id" => $resultReCache["UslugaExecutionType_id"]];
	}

	/**
	 * Отмена взятия пробы
	 * @param EvnLabSample_model $callObject
	 * @param array $data
	 * @return array
	 */
	public static function cancel(EvnLabSample_model $callObject, $data = [])
	{
		$EvnLabSample_ids = [];
		if (!empty($data["EvnLabSample_id"])) {
			$EvnLabSample_ids[] = $data["EvnLabSample_id"];
		} elseif (!empty($data["EvnLabSample_ids"])) {
			$EvnLabSample_ids = json_decode($data["EvnLabSample_ids"], true);
		}
		foreach ($EvnLabSample_ids as $EvnLabSample_id) {
			$data["EvnLabSample_id"] = $EvnLabSample_id;
			// 1. получаем идентификатор заявки
			$query = "
				select EvnLabRequest_id as \"EvnLabRequest_id\"
				from v_EvnLabSample
				where EvnLabSample_id = :EvnLabSample_id
				limit 1
			";
			$data["EvnLabRequest_id"] = $callObject->getFirstResultFromQuery($query, ["EvnLabSample_id" => $data["EvnLabSample_id"]]);
			$query = "
				select LabSampleStatus_id as \"LabSampleStatus_id\"
				from v_EvnLabSample
				where EvnLabSample_id = :EvnLabSample_id
				limit 1
			";

			// Нельзя отменять пробы с тестами в лунке планшета
			if($callObject->isLabSampleTestInHole($EvnLabSample_id)) {
				throw new Exception('Тест пробы находится в планшете');
			}

			$data["LabSampleStatus_id"] = $callObject->getFirstResultFromQuery($query, ["EvnLabSample_id" => $data["EvnLabSample_id"]]);
			// Нельзя отменять одобренные пробы
			if (empty($data["LabSampleStatus_id"]) || !in_array($data["LabSampleStatus_id"], [4, 6])) {
				// новая хранимка для отмены взятия пробы, refs https://redmine.swan.perm.ru/issues/117750
				$query = "
					select 
                    	error_code as \"Error_Code\",
                    	error_message as \"Error_Msg\"
                    from p_uslugatest_delall(
                    	EvnLabSample_id := :EvnLabSample_id,
                    	pmUser_id := :pmUser_id
                    )
				";
				$queryParams = [
					"EvnLabSample_id" => $data["EvnLabSample_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$callObject->db->query($query, $queryParams);
				$callObject->ReCacheLabSampleStatus(["EvnLabSample_id" => $data["EvnLabSample_id"]]);
				// 3. рекэшируем статус паявки
				if (!empty($data["EvnLabRequest_id"])) {
					// кэшируем статус заявки
					$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
					$funcParams = [
						"EvnLabRequest_id" => $data["EvnLabRequest_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->EvnLabRequest_model->ReCacheLabRequestStatus($funcParams);
					// кэшируем количество тестов
					$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaCount($funcParams);
					// кэшируем статус проб в заявке
					$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Отмена исследования
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 */
	public static function cancelResearch(EvnLabSample_model $callObject, $data)
	{
		$queryParams = [
			"EvnDirection_id" => $data["EvnDirection_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"]
		];
		$query = "
			select EvnUsluga_id as \"EvnUslugaPar_id\"
			from v_EvnUsluga
			where EvnDirection_id = :EvnDirection_id
			  and UslugaComplex_id = :UslugaComplex_id
		";
		$resp = $callObject->queryResult($query, $queryParams);
		foreach ($resp as $respone) {
			// удаляем записи из EvnLabRequestUslugaComplex
			$query = "
				select EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
				from v_EvnLabRequestUslugaComplex
				where EvnUslugaPar_id = :EvnUslugaPar_id
			";
			$resp_elruc = $callObject->queryResult($query, ["EvnUslugaPar_id" => $respone["EvnUslugaPar_id"]]);
			foreach ($resp_elruc as $one_elruc) {
				$query = "
                    select
                        error_code as \"Error_Code\",
                        error_message as \"Error_Msg\"
                    from p_evnlabrequestuslugacomplex_del(
                        evnlabrequestuslugacomplex_id := :EvnLabRequestUslugaComplex_id
                    )
				";
				$queryParams = [
					"EvnLabRequestUslugaComplex_id" => $one_elruc["EvnLabRequestUslugaComplex_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$callObject->db->query($query, $queryParams);
			}
			// удаляем исследование
			$query = "
				select 
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_evnuslugapar_del(
					evnuslugapar_id := :EvnUslugaPar_id,
					pmUser_id := :pmUser_id
				)
			";
			$queryParams = [
				"EvnUslugaPar_id" => $respone["EvnUslugaPar_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->db->query($query, $queryParams);
		}
		$query = "
			select EvnLabRequest_id as \"EvnLabRequest_id\"
			from v_EvnLabRequest
			where EvnDirection_id = :EvnDirection_id
		";
		$data["EvnLabRequest_id"] = $callObject->getFirstResultFromQuery($query, ["EvnDirection_id" => $data["EvnDirection_id"]]);
		if (!empty($data["EvnLabRequest_id"])) {
			$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
			// кэшируем количество тестов
			$funcParams = [
				"EvnLabRequest_id" => $data["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"],
				"EvnDirection_id" => $data["EvnDirection_id"]
			];
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaCount($funcParams);
			// кэшируем названия услуг
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaName($funcParams);
		}
	}

	/**
	 * Отмена теста
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function cancelTest(EvnLabSample_model $callObject, $data)
	{
		if (!empty($data["tests"])) {
			// меняем состав в заявке
			foreach ($data["tests"] as $test) {
				$query = "
					select EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
					from v_EvnLabRequestUslugaComplex elruc
					where elruc.EvnLabRequest_id = :EvnLabRequest_id
					  and elruc.EvnLabSample_id = :EvnLabSample_id
					  and elruc.EvnUslugaPar_id = :EvnUslugaPar_id
					  and elruc.UslugaComplex_id = :UslugaComplex_id
				";
				$queryParams = [
					"EvnLabRequest_id" => $data["EvnLabRequest_id"],
					"EvnLabSample_id" => $data["EvnLabSample_id"],
					"EvnUslugaPar_id" => $test["UslugaTest_pid"],
					"UslugaComplex_id" => $test["UslugaComplex_id"]
				];
				$data["EvnLabRequestUslugaComplex_id"] = $callObject->getFirstResultFromQuery($query, $queryParams);
				if (!empty($data["EvnLabRequestUslugaComplex_id"])) {
					$query = "
                        select
                        	error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
                        from p_evnlabrequestuslugacomplex_del(evnlabrequestuslugacomplex_id := :EvnLabRequestUslugaComplex_id)
					";
					$callObject->db->query($query, $data);
				}
			}
			// 1. если проба взята, то меняем тесты в самой пробе
			foreach ($data["tests"] as $test) {
				$query = "
					select UslugaTest_id as \"UslugaTest_id\"
					from v_UslugaTest
					where EvnLabSample_id = :EvnLabSample_id
					  and UslugaTest_pid = :UslugaTest_pid
					  and UslugaComplex_id = :UslugaComplex_id
				";
				$queryParams = [
					"EvnLabSample_id" => $data["EvnLabSample_id"],
					"UslugaTest_pid" => $test["UslugaTest_pid"],
					"UslugaComplex_id" => $test["UslugaComplex_id"]
				];
				$data["UslugaTest_id"] = $callObject->getFirstResultFromQuery($query, $queryParams);
				if (!empty($data["UslugaTest_id"])) {
					// проверить статус (можно удалять только в статусе "Новый")
					if (!$callObject->checkTestCanBeCanceled($data)) {
						throw new Exception("Нельзя удалить тест, находящийся не в статусе \"Новый\"");
					}

					// тест не должен быть в лунке планшета
					if($callObject->testInHole($data['UslugaTest_id'])) {
						throw new Exception('Тест находится в лунке');
					}

					$query = "
						select 
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_uslugatest_del(
							uslugatest_id := :UslugaTest_id,
							pmUser_id := :pmUser_id
						)  
					";
					$callObject->db->query($query, $data);
				}
			}
			$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
			// кэшируем количество тестов
			$funcParams = [
				"EvnLabRequest_id" => $data["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaCount($funcParams);
			// кэшируем статус проб в заявке
			$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
			// если по исследованию не осталось ни одного сохранённого/назначенного теста, то удаляем его.
			// получаем список затронутых исследований
			$EvnUslugaParPList = [];
			foreach ($data["tests"] as $test) {
				if (!in_array($test["UslugaTest_pid"], $EvnUslugaParPList)) {
					$EvnUslugaParPList[] = $test["UslugaTest_pid"];
				}
			}
			foreach ($EvnUslugaParPList as $EvnUslugaPar_pid) {
				// проверяем есть ли ещё тесты у данного исследования
				$query = "
					(
						select eup.EvnUslugaPar_id as \"id\"
						from v_EvnUslugaPar eup
						where eup.EvnUslugaPar_pid = :EvnUslugaPar_pid
						limit 1
					) union
					(
						select EvnLabRequestUslugaComplex_id as \"id\"
						from v_EvnLabRequestUslugaComplex elruc
						where elruc.EvnUslugaPar_id = :EvnUslugaPar_pid
                        limit 1
					)
				";
				$resp_research = $callObject->queryResult($query, ["EvnUslugaPar_pid" => $EvnUslugaPar_pid]);
				// если тестов нет то удаляем
				if (empty($resp_research[0]["id"])) {
					$query = "
						select 
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_evnuslugapar_del(
							EvnUslugaPar_id :=:EvnUslugaPar_id,
							pmUser_id :=:pmUser_id
					)
					";
					$queryParams = [
						"EvnUslugaPar_id" => $EvnUslugaPar_pid,
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->db->query($query, $queryParams);
				}
			}
			// кэшируем названия услуг
			$funcParams = [
				"EvnLabRequest_id" => $data["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaName($funcParams);
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка 12-ти значного штрих-кода пробы на уникальность
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkEvnLabSampleBarCodeUnique(EvnLabSample_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"EvnLabSample_BarCode" => $data["EvnLabSample_BarCode"]
		];
		$filter = "";
		if (!empty($data["EvnLabSample_id"])) {
			$queryParams["EvnLabSample_id"] = $data["EvnLabSample_id"];
			$filter .= "and EvnLabSample_id <> :EvnLabSample_id";
		}
		$query = "
   			select EvnLabSample_id as \"EvnLabSample_id\"
   			from v_EvnLabSample
	        where EvnLabSample_BarCode = :EvnLabSample_BarCode
		      and Lpu_id = :Lpu_id               
		      {$filter}
		    limit 1 
   		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		return (!empty($resp[0]["EvnLabSample_id"])) ? ["Error_Msg" => "Проба с штрих-кодом {$data["EvnLabSample_BarCode"]} уже существует"] : true;
	}

	/**
	 * Проверка 12-ти значного номера пробы на уникальность
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkEvnLabSampleNumUnique(EvnLabSample_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"EvnLabSample_Num" => $data["EvnLabSample_Num"]
		];
		$filter = "";
		if (!empty($data["EvnLabSample_id"])) {
			$queryParams["EvnLabSample_id"] = $data["EvnLabSample_id"];
			$filter .= "and EvnLabSample_id <> :EvnLabSample_id";
		}
		$query = "
   			select count(EvnLabSample_Num) as \"count\"
   			from v_EvnLabSample
	        where EvnLabSample_Num = :EvnLabSample_Num
		      and Lpu_id = :Lpu_id
		      {$filter}
   		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		return ($resp[0]["count"] > 0) ? ["Error_Msg" => "Проба с номером " . mb_substr($data["EvnLabSample_Num"], -4) . " уже существует"] : true;
	}

	/**
	 * Проверка 12-ти значного списка пробы на уникальность
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkEvnLabSampleUnique(EvnLabSample_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"EvnLabSample_Num" => $data["EvnLabSample_Num"],
			"MedService_id" => $data["MedService_id"]
		];
		$query = "
   			select count(EvnLabSample_Num) as \"count\"
   			from v_EvnLabSample
	        where LabSampleStatus_id in (1,2,3,7)
	          and length(EvnLabSample_Num) = 12
		      and substring(EvnLabSample_Num from 5 for 8) = substring(:EvnLabSample_Num from 5 for 8)
		      and Lpu_id = :Lpu_id
   		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		return ($resp[0]["count"] > 0) ? ["Error_Msg" => "Проба с суточным номером " . substr($data["EvnLabSample_Num"], -4) . " уже существует"] : ["success" => true];
	}

	/**
	 * Проверка на возможность отмены теста
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkTestCanBeCanceled(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				COALESCE(ut.UslugaTest_ResultApproved,1) as \"UslugaTest_ResultApproved\"
			from
				v_EvnLabSample ls
				LEFT JOIN v_UslugaTest ut ON ut.EvnLabSample_id = ls.EvnLabSample_id
			where ut.UslugaTest_id = :UslugaTest_id
			  and (COALESCE(ut.UslugaTest_ResultApproved, 1) = 2 or (ut.UslugaTest_ResultValue is not null and ut.UslugaTest_ResultValue <> ''))
			limit 1	
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return true;
		}
		$resp = $result->result_array();
		return (count($resp) > 0) ? false : true;
	}

	/**
	 * добавление результатов взятия пробы. Метод для API
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool|string
	 * @throws Exception
	 */
	public static function createEvnLabSampleAPI(EvnLabSample_model $callObject, $data)
	{
		$EvnLabRequest_id = null;
		$EvnLabSample_id = null;
		$data['methodAPI'] = true;
		// ищем заявку на лабораторное исследование по EvnDirection_id
		$query = "
			select
				d.EvnDirection_id as \"EvnDirection_id\",
				es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.MedService_id as \"MedService_id\"
			from
				v_EvnDirection_all d
				left join v_EvnStatus es on es.EvnStatus_id = d.EvnStatus_id
				left join v_EvnLabRequest elr on elr.EvnDirection_id = d.EvnDirection_id
			where d.EvnDirection_id = :EvnDirection_id
			limit 1";
		$res = $callObject->getFirstRowFromQuery($query, $data);
		if (empty($res["EvnDirection_id"])) {
			throw new Exception("Не найдено направление на исследование");
		}
		//проверяем не отменили ли направление
		if (!empty($res["EvnStatus_SysNick"]) && in_array($res["EvnStatus_SysNick"], array("Declined", "Canceled"))) {
			$query = "
				select
					es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
					elr.EvnLabRequest_id as \"EvnLabRequest_id\",
					elr.MedService_id as \"MedService_id\",
					ESH.EvnStatusHistory_Cause as \"EvnStatusHistory_Cause\",
					ESC.EvnStatusCause_Name as \"EvnStatusCause_Name\",
					LCID.Lpu_Nick as \"LpuCID_Nick\"
				from
					v_EvnDirection_all d
					left join v_EvnStatus es on es.EvnStatus_id = d.EvnStatus_id
					left join v_EvnLabRequest elr on elr.EvnDirection_id = d.EvnDirection_id
					left join v_Lpu LCID on d.Lpu_cid = LCID.Lpu_id
					left join v_EvnStatusHistory ESH on ESH.Evn_id = d.EvnDirection_id
					left join v_EvnStatusCause ESC on ESC.EvnStatusCause_id = ESH.EvnStatusCause_id
				where
					d.EvnDirection_id = :EvnDirection_id
					and ESH.EvnStatusCause_id is not null
				limit 1
			";
			$resCancel = $callObject->getFirstRowFromQuery($query, $data);
			$msg = "Направление отменено";
			if (!empty($resCancel["LpuCID_Nick"])) {
				$msg .= " МО {$resCancel["LpuCID_Nick"]}";
			}
			if (!empty($resCancel["LpuCID_Nick"])) {
				$msg .= " по причине: {$resCancel["EvnStatusCause_Name"]}";
			}
			if (!empty($resCancel["EvnStatusHistory_Cause"])) {
				$msg .= ", комментарий: {$resCancel["EvnStatusHistory_Cause"]}";
			}
			throw new Exception($msg);
		}
		if (empty($res['EvnLabRequest_id'])) {
			return array('Error_Msg' => 'Не найдена заявка на исследование');
		}
		$EvnLabRequest_id = $res["EvnLabRequest_id"];
		// сохраняем пробу в которую потом запишем услуги
		$funcParams = [
			"pmUser_id" => $data["pmUser_id"],
			"RefSample_id" => null,
			"EvnLabRequest_id" => $EvnLabRequest_id,
			"MedService_id" => $res["MedService_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$resp = $callObject->saveLabSample($funcParams);
		if (empty($resp[0]["EvnLabSample_id"])) {
			throw new Exception("Ошибка сохранения пробы" . (!empty($resp[0]["Error_Msg"]) ? ": " . $resp[0]["Error_Msg"] : ""));
		}
		$EvnLabSample_id = $resp[0]["EvnLabSample_id"];
		$UslugaComplexListString = implode(",", $data["UslugaComplexList"]);
		$query = "
			select
				EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				EvnLabRequestUslugaComplex_insDT as \"EvnLabRequestUslugaComplex_insDT\",
				EvnLabRequestUslugaComplex_updDT as \"EvnLabRequestUslugaComplex_updDT\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnLabSample_id as \"EvnLabSample_id\"
			from
				v_EvnLabRequestUslugaComplex
			where
				EvnLabRequest_id = :EvnLabRequest_id
				and UslugaComplex_id in ({$UslugaComplexListString})
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($query, ["EvnLabRequest_id" => $EvnLabRequest_id]);
		if (!is_object($res)) {
			throw new Exception("Не найдены переданные услуги в заявке");
		}
		$EvnLabRequestUslugaComplexs = $res->result_array();
		if (count($EvnLabRequestUslugaComplexs) == 0) {
			throw new Exception("Не найдены переданные услуги в заявке");
		}
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\",
				evnlabrequestuslugacomplex_id as \"EvnLabRequestUslugaComplex_id\"
			from p_EvnLabRequestUslugaComplex_upd(
				EvnLabRequestUslugaComplex_id := :EvnLabRequestUslugaComplex_id ,
				EvnLabRequest_id := :EvnLabRequest_id,
				EvnLabSample_id := :EvnLabSample_id,
				EvnUslugaPar_id := :EvnUslugaPar_id,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id
			)
		";
		$params = [
			"EvnLabRequest_id" => $EvnLabRequest_id,
			"EvnLabSample_id" => $EvnLabSample_id,
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = [];

		$oldSamples = [];
		foreach ($EvnLabRequestUslugaComplexs as $key => $value) {
			$params["EvnLabRequestUslugaComplex_id"] = $value["EvnLabRequestUslugaComplex_id"];
			$params["EvnUslugaPar_id"] = $value["EvnUslugaPar_id"];
			$params["UslugaComplex_id"] = $value["UslugaComplex_id"];
			if (!in_array($value["EvnLabSample_id"], $oldSamples)) {
				$oldSamples[] = $value["EvnLabSample_id"];
			}
			$res = $callObject->queryResult($query, $params);
			if ($res) {
				collectEditedData("upd", "EvnLabRequestUslugaComplex", $params["EvnLabRequestUslugaComplex_id"]);
			}
			$result[] = $res;
		}
		//убираем из заявки пустые пробы
		foreach ($oldSamples as $id) {
			$callObject->queryResult("
				select 
                	error_code as \"Error_Code\",
                	error_message as \"Error_Msg\"
                from p_EvnLabSample_del(
                	EvnLabSample_id := :EvnLabSample_id,
                    pmUser_id := :pmUser_id
                )
            ", [
            	'EvnLabSample_id' => $id,
				'pmUser_id' => $data['pmUser_id']
			]);
		}
		//берем пробу
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				MedService_did as \"MedService_did\"
			from
				v_EvnLabSample
			where
				EvnLabSample_id = :EvnLabSample_id
		";
		$res = $callObject->getFirstRowFromQuery($query, ["EvnLabSample_id" => $EvnLabSample_id]);
		if (empty($res["EvnLabSample_id"])) {
			throw new Exception("Ошибка при взятии пробы");
		}
		$data["EvnLabRequest_id"] = $EvnLabRequest_id;
		$data["EvnLabSample_id"] = $EvnLabSample_id;
		$data["MedServiceType_SysNick"] = "lab";
		$data["RefSample_id"] = null;
		if (empty($data["MedService_did"])) {
			$data["MedService_did"] = $res["MedService_did"];
		}
		$result = $callObject->takeLabSample($data);
		$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		// кэшируем количество тестов
		$funcParams = [
			"EvnLabRequest_id" => $data["EvnLabRequest_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaCount($funcParams);
		// кэшируем статус заявки
		$callObject->EvnLabRequest_model->ReCacheLabRequestStatus($funcParams);
		// кэшируем названия услуг
		$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaName($funcParams);
		// кэшируем статус проб в заявке
		$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
		return $result;
	}

	/**
	 * Добавление результатов лабароторного исследования. Метод для API
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function createUslugaTestAPI(EvnLabSample_model $callObject, $data)
	{
		if (empty($data["EvnLabSample_id"]) || empty($data["UslugaComplex_id"])) {
			throw new Exception("Не переданы обязательные параметры");
		}
		// найдем UslugaTest_id т.к. она должна существовать для метода добавления, этот UslugaTest_id мы возвращаем пользователю, типа мы ее создали (не я придумал такую кухню)
		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				UslugaTest_pid as \"UslugaTest_pid\",
				UslugaTest_rid as \"UslugaTest_rid\",
				UslugaTest_setDT as \"UslugaTest_setDT\",
				UslugaTest_disDT as \"UslugaTest_disDT\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnDirection_id as \"EvnDirection_id\",
				Usluga_id as \"Usluga_id\",
				PayType_id as \"PayType_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				UslugaTest_ResultAppDate as \"UslugaTest_ResultAppDate\",
				UslugaTest_ResultCancelReason as \"UslugaTest_ResultCancelReason\",
				UslugaTest_Comment as \"UslugaTest_Comment\",
				UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				UslugaTest_ResultQualitativeText as \"UslugaTest_ResultQualitativeText\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				UslugaTest_Kolvo as \"UslugaTest_Kolvo\",
				UslugaTest_Result as \"UslugaTest_Result\",
				EvnLabsample_id as \"EvnLabsample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				UslugaTest_insDT as \"UslugaTest_insDT\",
				UslugaTest_updDT as \"UslugaTest_updDT\"
			from v_UslugaTest
			where EvnLabSample_id = :EvnLabSample_id
			  and UslugaComplex_id = :UslugaComplex_id
		";
		$res = $callObject->getFirstRowFromQuery($query, $data);
		if (empty($res["UslugaTest_id"])) {
			throw new Exception("Услуга не создана");
		}
		$data["UslugaTest_id"] = $res["UslugaTest_id"];
		$data["approveEvnLabSampleResults"] = true;
		$result = $callObject->updateUslugaTestAPI($data);
		return $result;
	}

	/**
	 * Удаление
	 * @param EvnLabSample_model $callObject
	 * @param array $data
	 * @return array
	 */
	public static function delete(EvnLabSample_model $callObject, $data = [])
	{
		$EvnLabSample_ids = [];
		if (!empty($data['EvnLabSample_id'])) {
			$EvnLabSample_ids[] = $data['EvnLabSample_id'];
		} elseif (!empty($data['EvnLabSample_ids'])) {
			$EvnLabSample_ids = json_decode($data['EvnLabSample_ids'], true);
		}
		foreach ($EvnLabSample_ids as $EvnLabSample_id) {
			$data["EvnLabSample_id"] = $EvnLabSample_id;
			// 1. получаем идентификатор заявки
			$query = "
				select EvnLabRequest_id as \"EvnLabRequest_id\"
				from v_EvnLabSample
				where EvnLabSample_id = :EvnLabSample_id
				limit 1
			";
			$data["EvnLabRequest_id"] = $callObject->getFirstResultFromQuery($query, ["EvnLabSample_id" => $data["EvnLabSample_id"]]);

			$query = "
				select LabSampleStatus_id as \"LabSampleStatus_id\"
				from v_EvnLabSample
				where EvnLabSample_id = :EvnLabSample_id
				limit 1
			";
			$data["LabSampleStatus_id"] = $callObject->getFirstResultFromQuery($query, ["EvnLabSample_id" => $data["EvnLabSample_id"]]);
			// Нельзя удалять одобренные пробы
			if (empty($data["LabSampleStatus_id"]) || !in_array($data["LabSampleStatus_id"], [4, 6])) {
				// 2.1 удаляем все результаты по пробе из UslugaTest
				$query = "
					select UslugaTest_id as \"UslugaTest_id\"
					from v_UslugaTest
					where EvnLabSample_id = :EvnLabSample_id
				";
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result_array();
					foreach ($resp as $respone) {
						$query = "
							select 
                    			error_code as \"Error_Code\",
                    			error_message as \"Error_Msg\"
                   			from p_uslugatest_del(
								UslugaTest_id := :UslugaTest_id,
								pmUser_id := :pmUser_id
							)
						";
						$queryParams = [
							"UslugaTest_id" => $respone["UslugaTest_id"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$callObject->db->query($query, $queryParams);
					}
				}
				// 2.2 удаляем пробу
				$query = "
					select 
                    	error_code as \"Error_Code\",
                    	error_message as \"Error_Msg\"
                    from p_EvnLabSample_del(
                    	EvnLabSample_id := :EvnLabSample_id,
                        pmUser_id := :pmUser_id
                    )
				";
				$callObject->db->query($query, $data);
				// 3. рекэшируем статус заявки
				if (!empty($data["EvnLabRequest_id"])) {
					// кэшируем статус заявки
					$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
					$funcParams = [
						"EvnLabRequest_id" => $data["EvnLabRequest_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->EvnLabRequest_model->ReCacheLabRequestStatus($funcParams);
					// кэшируем количество тестов
					$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaCount($funcParams);
					// кэшируем статус проб в заявке
					$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Удаление отбраковки
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function deleteEvnLabSampleDefect(EvnLabSample_model $callObject, $data)
	{
		$query = "
			update EvnLabSample
			set
				DefectCauseType_id = null,
				EvnLabSample_IsLIS = 1
			where Evn_id = :EvnLabSample_id
		";
		$res = $callObject->db->query($query, $data);
		if ($res) {
			collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
		}
		// рекэш статуса
		$callObject->ReCacheLabSampleStatus(["EvnLabSample_id" => $data["EvnLabSample_id"]]);
		return ["Error_Msg" => ""];
	}

	/**
	 * Генерация номера пробы и проверка отсту
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @param $in_work_samples_array
	 * @param int $count
	 * @param int $beginningOfNumbering
	 * @return array|string
	 * @throws Exception
	 */
	public static function generateLabSampleNum(EvnLabSample_model $callObject, $data, $in_work_samples_array, $count = 0, $beginningOfNumbering = 0)
	{
		// генерируем номер пробы
		$callObject->load->library("swMongoExt");
		$callObject->elslog->add("Подключили swMongoExt");
		// count для защиты от зацикливания.
		$count_cycle = 0;
		$min = ($beginningOfNumbering) ? (int)$beginningOfNumbering : 1;
		do {
			$k = $callObject->swmongoext->generateCode("Samples", "day", ["Lpu_id" => $data["Lpu_id"], "MedService_id" => $data["MedService_id"]], $min);
			$callObject->elslog->add("Выполнили swmongoext->generateCode | Lpu_id=" . $data["Lpu_id"] . " | MedService_id=" . $data["MedService_id"]);
			$LabSample_Num = substr($k, -4);
			if (!$beginningOfNumbering) $LabSample_Num = intval($LabSample_Num) + 1000;
			// увеличиваем счётчик локально без обращения к монго
			$currentNum = $LabSample_Num;
			while (in_array(str_pad($data["MedService_Code"], 4, 0, STR_PAD_LEFT) . $currentNum, $in_work_samples_array) && $count_cycle < 10000) {
				$k++;
				$currentNum = substr($k, -4);
				$currentNum = intval($currentNum) + 1000;
				$min = $k;
				$count_cycle++;
			}
		} while (in_array(str_pad($data["MedService_Code"], 4, 0, STR_PAD_LEFT) . $LabSample_Num, $in_work_samples_array) && $count_cycle < 10000);
		if ($count_cycle == 10000) {
			$callObject->elslog->add("Ошибка генерации номера пробы");
			throw new Exception("Ошибка генерации номера пробы");
		}
		$EvnLabSample_Num = substr(Date("Y"), -1) . str_pad((Date("z") + 1), 3, 0, STR_PAD_LEFT) . str_pad($data["MedService_Code"], 4, 0, STR_PAD_LEFT) . str_pad($LabSample_Num, 4, 0, STR_PAD_LEFT);
		// проверяем чтоб не было за сегодня пробы с таким же номером, если есть генерируем новый
		$sql_check = "
			select EvnLabSample_id as \"EvnLabSample_id\"
			from v_EvnLabSample 
			where EvnLabSample_Num = :EvnLabSample_Num 
			  and EvnLabSample_setDT = dbo.tzgetdate()
            limit 1
		";
		/**@var CI_DB_result $res_check */
		$res_check = $callObject->db->query($sql_check, ["EvnLabSample_Num" => $EvnLabSample_Num]);
		$callObject->elslog->add("Проверили что нет за сегодня пробы с таким же номером");
		if (!is_object($res_check)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих записей номеров пробы)");
		}
		$resp_check = $res_check->result_array();
		if (is_array($resp_check) && count($resp_check) > 0 && !empty($resp_check[0]["EvnLabSample_id"])) {
			// есть уже проба с таким номером, генерируем новый номер
			$count++;
			if ($count > 10) {
				$callObject->elslog->add("Ошибка получения номера пробы");
				throw new Exception("Ошибка получения номера пробы");
			}
			return $callObject->generateLabSampleNum($data, $in_work_samples_array, $count);
		}
		return $EvnLabSample_Num;
	}

	/**
	 * Проставление данных о выполнении услуги
	 * (должно выполняться после любого одобрения результатов, либо снятия одобрения, только для тех услуг по которым производились данные действия)
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return mixed|bool
	 * @throws Exception
	 */
	public static function onChangeApproveResults(EvnLabSample_model $callObject, $data)
	{
		if (!empty($data["EvnUslugaParChanged"])) {
			foreach ($data["EvnUslugaParChanged"] as $EvnUslugaPar_id) {
				$query = "
					select
						eupp.EvnUslugaPar_id as \"EvnUslugaPar_pid\",
						eupp.UslugaComplex_id as \"UslugaComplex_id\",
						ut.UslugaTest_id as \"UslugaTest_id\",
						eupp.EvnDirection_id as \"EvnDirection_id\",
						eupp.EvnPrescr_id as \"EvnPrescr_id\",
						elr.EvnLabRequest_id as \"EvnLabRequest_id\",
						eupp.EvnUslugaPar_pid as \"Evn_pid\",
						e.EvnClass_SysNick as \"EvnClass_SysNick\"
					from
						v_EvnUslugaPar eupp
						inner join v_EvnLabRequest elr on elr.EvnDirection_id = eupp.EvnDirection_id 
						left join v_UslugaTest ut on ut.UslugaTest_pid = eupp.EvnUslugaPar_id and ut.UslugaTest_ResultApproved = 2
					 	left join v_Evn e on e.Evn_id = eupp.EvnUslugaPar_pid
					where eupp.EvnUslugaPar_id = :EvnUslugaPar_id
					limit 1	
				";
				$resp_eup = $callObject->queryResult($query, ["EvnUslugaPar_id" => $EvnUslugaPar_id]);
				if (!empty($resp_eup[0]["EvnUslugaPar_pid"])) {
					// апдейтим дату выполнения услуги, а так же связь с родительским событием
					$Evn_setDT = "null";
					$data["EvnUslugaPar_pid"] = null;
					//костыль для изменений по задаче #160128 - начало
					$res = $callObject->common->GET("EvnMediaData/byEvn", ["Evn_id" => $EvnUslugaPar_id], "single");
					if (!$callObject->isSuccessful($res)) {
						return $res;
					}
					$resp_eup[0]["EvnMediaData_id"] = (!empty($res) && !empty($res["EvnMediaData_id"])) ? $res["EvnMediaData_id"] : null;
					//костыль для изменений по задаче #160128 - конец
					if (!empty($resp_eup[0]["UslugaTest_id"]) || !empty($resp_eup[0]["EvnMediaData_id"])) {
						// Если есть хотя бы один одобренный тест или один прикреплённый к исследованию файл
						$Evn_setDT = "dbo.tzGetDate()";
						$dt = [
							"EvnDirection_id" => $resp_eup[0]["EvnDirection_id"],
							"EvnPrescr_id" => $resp_eup[0]["EvnPrescr_id"],
							"UslugaComplex_id" => $resp_eup[0]["UslugaComplex_id"],
							"EvnUslugaPar_pid" => $data["EvnUslugaPar_pid"],
							"EvnUslugaPar_setDT" => "curdate"
						];
						// оперделяем Evn_pid
						$uslugaParams = $callObject->common->GET("EvnPrescr/defineUslugaParams", $dt, "single");
						if (!$callObject->isSuccessful($uslugaParams)) {
							throw new Exception($uslugaParams["Error_Msg"], 400);
						}
						$data["EvnUslugaPar_pid"] = $uslugaParams["EvnUslugaPar_pid"];
					} else {
						$uslugaParams = [
							"EvnUslugaPar_pid" => $resp_eup[0]["Evn_pid"],
							"needRecalcKSGKPGKOEF" => ($resp_eup[0]["EvnClass_SysNick"] == "EvnSection") ? true : false
						];
					}
					// определяем открытое рабочее место врача, без врача услугу нельзя выполнить (refs #83806)
					if (!empty($data["session"]["medpersonal_id"])) {
						$params = ["MedPersonal_id" => $data["session"]["medpersonal_id"]];
						if (!empty($data["session"]["CurLpuSection_id"])) {
							// если задано отделение, фильтруем по отделению
							$params["LpuSection_id"] = $data["session"]["CurLpuSection_id"];
						}
						$resp_msf = $callObject->common->GET("MedStaffFact/msfData", $params, "single");
						if (!$callObject->isSuccessful($resp_msf)) {
							return $resp_msf;
						}
						$data["MedStaffFact_id"] = null;
						if (!empty($resp_msf[0]["MedStaffFact_id"])) {
							$data["MedStaffFact_id"] = $resp_msf[0]["MedStaffFact_id"];
						}
						$data["LpuSection_uid"] = null;
						if (!empty($data["session"]["CurLpuSection_id"])) {
							$data["LpuSection_uid"] = $data["session"]["CurLpuSection_id"];
						} else if (!empty($resp_msf[0]["LpuSection_id"])) {
							$data["LpuSection_uid"] = $resp_msf[0]["LpuSection_id"];
						}
						$funcParams = [
							"EvnLabRequest_id" => $resp_eup[0]["EvnLabRequest_id"],
							"EvnUslugaPar_pid" => $data["EvnUslugaPar_pid"],
							"Evn_setDT" => $Evn_setDT,
							"EvnUslugaPar_id" => $EvnUslugaPar_id,
							"Lpu_id" => $data["session"]["lpu_id"],
							"LpuSection_uid" => $data["LpuSection_uid"],
							"MedPersonal_id" => $data["session"]["medpersonal_id"],
							"MedStaffFact_id" => $data["MedStaffFact_id"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$callObject->saveEvnUslugaDone($funcParams);
						if (in_array(getRegionNick(), ["perm", "kaluga", "ufa", "astra"]) && !empty($uslugaParams["needRecalcKSGKPGKOEF"])) {
							$res = $callObject->common->POST("EvnSection/recalcKSGKPGKOEF", ["EvnSection_id" => $uslugaParams["EvnUslugaPar_pid"]], "single");
							if (!$callObject->isSuccessful($res)) {
								throw new Exception($res["Error_Msg"], 400);
							}
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Назначение теста
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function prescrTest(EvnLabSample_model $callObject, $data)
	{
		if (!empty($data["tests"])) {
			// 1. меняем состав в заявке
			$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
			foreach ($data["tests"] as $test) {
				$funcParams = [
					"EvnLabRequest_id" => $data["EvnLabRequest_id"],
					"EvnLabSample_id" => $data["EvnLabSample_id"],
					"EvnUslugaPar_id" => $test["UslugaTest_pid"],
					"UslugaComplex_id" => $test["UslugaComplex_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$callObject->EvnLabRequest_model->saveEvnLabRequestUslugaComplex($funcParams);
			}
			// кэшируем количество тестов
			$funcParams = [
				"EvnLabRequest_id" => $data["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaCount($funcParams);
			// 2. если проба взята, то меняем тесты в самой пробе
			$query = "
				select
					els.RefSample_id as \"RefSample_id\",
					elr.EvnDirection_id as \"EvnDirection_id\",
					elr.MedService_id as \"MedService_id\",
					elr.Person_id as \"Person_id\",
					els.EvnLabSample_setDT as \"EvnLabSample_setDT\",
					elr.PersonEvn_id as \"PersonEvn_id\",
					elr.Server_id as \"Server_id\",
					ms.Lpu_id as \"Lpu_id\",
					els.Analyzer_id as \"Analyzer_id\",
					ms.LpuSection_id as \"LpuSection_id\",
					elr.PayType_id as \"PayType_id\",
					elr.Mes_id as \"Mes_id\",
					elr.Diag_id as \"Diag_id\"
				from
					v_EvnLabSample els
					inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
					inner join v_MedService ms on ms.MedService_id = elr.MedService_id
				where els.EvnLabSample_id = :EvnLabSample_id
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (!empty($resp[0]["EvnLabSample_setDT"])) {
					$lrdata = [
						"EvnDirection_id" => $resp[0]["EvnDirection_id"],
						"MedService_id" => $resp[0]["MedService_id"],
						"Person_id" => $resp[0]["Person_id"],
						"PersonEvn_id" => $resp[0]["PersonEvn_id"],
						"Server_id" => $resp[0]["Server_id"],
						"Lpu_id" => $resp[0]["Lpu_id"],
						"LpuSection_id" => $resp[0]["LpuSection_id"],
						"PayType_id" => $resp[0]["PayType_id"],
						"MedPersonal_id" => $data["session"]["medpersonal_id"],
						"Mes_id" => $resp[0]["Mes_id"],
						"Diag_id" => $resp[0]["Diag_id"],
					];
					$data["Analyzer_id"] = $resp[0]["Analyzer_id"];
					$data["EvnLabSample_setDT"] = $resp[0]["EvnLabSample_setDT"];
					// 2. сохраняем нужные тесты
					$data["ingorePrescr"] = true;
					$callObject->saveLabSampleTests($data, $lrdata, $data["tests"]);
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Кэш статуса проб в заявку и создание протокола если надо
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function ReCacheLabRequestByLabSample(EvnLabSample_model $callObject, $data)
	{
		$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		// 1. смотрим одобрены ли в заявке все пробы
		$query = "
			select
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnDirection_id as \"EvnDirection_id\",
				ms.LpuSection_id as \"LpuSection_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_MedService ms on ms.MedService_id = els.MedService_id
			where els.EvnLabSample_id = :EvnLabSample_id
			limit 1	
		";
		$resp_elr = $callObject->queryResult($query, ["EvnLabSample_id" => $data["EvnLabSample_id"]]);
		if (empty($resp_elr[0]["EvnLabRequest_id"])) {
			return false;
		}
		$data["EvnLabRequest_id"] = $resp_elr[0]["EvnLabRequest_id"];
		$data["EvnDirection_id"] = $resp_elr[0]["EvnDirection_id"];
		$data["LpuSection_id"] = $resp_elr[0]["LpuSection_id"];
		// Получаем список родительских услуг для данной пробы
		$EvnUslugaPars = $callObject->getEvnUslugasRoot(["EvnLabSample_id" => $data["EvnLabSample_id"]]);
		foreach ($EvnUslugaPars as $EvnUslugaPar) {
			// Если есть хотя бы один одобренный тест
			$query = "
				select UslugaTest_id as \"UslugaTest_id\"
				from v_UslugaTest
				where UslugaTest_pid = :EvnUslugaPar_id
				  and UslugaTest_ResultApproved = 2
				limit 1
			";
			$test = $callObject->getFirstResultFromQuery($query, ["EvnUslugaPar_id" => $EvnUslugaPar["EvnUslugaPar_id"]]);
			if (!empty($test)) {
				if (!empty($EvnUslugaPar["EvnPrescr_id"])) {
					$funcParams = [
						"EvnPrescr_id" => $EvnUslugaPar["EvnPrescr_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$res = $callObject->common->POST("EvnPrescr/Execution/exec", $funcParams, "single");
					if (!$callObject->isSuccessful($res)) {
						throw new Exception($res["Error_Msg"], 400);
					}
				}
				// Создаем или пересоздаем документ (протокол)
				$callObject->pmUser_id = $data["pmUser_id"];
				$funcParams = ["EvnLabRequest_id" => $data["EvnLabRequest_id"], "pmUser_id" => $callObject->pmUser_id];
				$callObject->EvnLabRequest_model->assign($funcParams);
				$callObject->EvnLabRequest_model->load();
				$callObject->EvnLabRequest_model->EvnUslugaPar_oid = $EvnUslugaPar["EvnUslugaPar_id"];
				$samples = $callObject->loadList(["EvnLabRequest_id" => $data["EvnLabRequest_id"]]);
				// Еще нужно заполнить объект $callObject->EvnLabRequest_model->EvnLabSample
				$callObject->EvnLabRequest_model->EvnLabSample->setItems($samples);
				// Сохраняем протокол
				$callObject->EvnLabRequest_model->saveEvnXml();
			} else {
				// нет одобренного теста
				if (!empty($EvnUslugaPar["EvnPrescr_id"])) {
					$funcParams = [
						"EvnPrescr_id" => $EvnUslugaPar["EvnPrescr_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$callObject->common->PUT("EvnPrescr/Execution/rollback", $funcParams, "single");
				}
				// Удаляем протокол( поиск документа вынесен в api)
				//evnxml создаются/лежат на основной, оттуда и стоит удалять
				$res = $callObject->common->DELETE("EvnXml/byEvn", ["Evn_id" => $EvnUslugaPar["EvnUslugaPar_id"]], "single");
				if (!$callObject->isSuccessful($res)) {
					throw new Exception($res["Error_Msg"], 500);
				}
			}
		}
		if (!empty($data["EvnLabRequest_id"])) {
			$counts = $callObject->EvnLabRequest_model->countTests(["EvnLabRequest_id" => $data["EvnLabRequest_id"]]);
			$data["UslugaExecutionType_id"] = null;
			if ($counts["approved_count"] > 0) {
				$data["UslugaExecutionType_id"] = 2;
				if ($counts["test_count"] == $counts["approved_count"]) {
					$data["UslugaExecutionType_id"] = 1;
				}
			}
			if ($counts["sample_bad_count"] > 0 && $counts["sample_bad_count"] == $counts["sample_count"]) {
				// все пробы забракованы = не выполнена (refs #54735)
				$data["UslugaExecutionType_id"] = 3;
			}
			// сохраняем выполнение
			$callObject->EvnLabRequest_model->saveUslugaExecutionType($data);
			// кэшируем статус заявки
			$funcParams = [
				"EvnLabRequest_id" => $data["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->EvnLabRequest_model->ReCacheLabRequestStatus($funcParams);
			// кэшируем названия услуг
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaName($funcParams);
			// кэшируем статус проб в заявке
			$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
		}
		return ["UslugaExecutionType_id" => (isset($data["UslugaExecutionType_id"])) ? $data["UslugaExecutionType_id"] : null];
	}

	/**
	 * Функция кэширования нормальности результатов в пробе
	 * Должна вызываться при изменении объектов участвующих в запросе, связанных с данной пробой
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function ReCacheLabSampleIsOutNorm(EvnLabSample_model $callObject, $data)
	{
		// достаём результаты пробы
		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				UslugaTest_Result as \"UslugaTest_Result\",
				UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\"
			from v_UslugaTest
			where EvnLabSample_id = :EvnLabSample_id
			  and UslugaTest_ResultValue is not null
			  and UslugaTest_ResultValue <> ''
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, ["EvnLabSample_id" => $data["EvnLabSample_id"]]);
		$EvnLabSample_IsOutNorm = 1;
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				if (!empty($respone["UslugaTest_ResultQualitativeNorms"])) {
					$UslugaTest_ResultQualitativeNorms = json_decode($respone["UslugaTest_ResultQualitativeNorms"], true);
					array_walk_recursive($UslugaTest_ResultQualitativeNorms, "ConvertFromUTF8ToWin1251");
					if (!(is_array($UslugaTest_ResultQualitativeNorms) && in_array($respone["UslugaTest_ResultValue"], $UslugaTest_ResultQualitativeNorms))) {
						$EvnLabSample_IsOutNorm = 2;
					}
				} else {
					// только числовые нормы можно сравнить
					if (!is_numeric(trim(str_replace(",", ".", $respone["UslugaTest_ResultLower"])))) {
						$respone["UslugaTest_ResultLower"] = null;
					}
					if (!is_numeric(trim(str_replace(",", ".", $respone["UslugaTest_ResultUpper"])))) {
						$respone["UslugaTest_ResultUpper"] = null;
					}
					if (is_numeric(trim(str_replace(",", ".", $respone["UslugaTest_ResultValue"])))) {
						if (
						!(
							(!isset($respone["UslugaTest_ResultLower"]) || floatval(str_replace(",", ".", $respone["UslugaTest_ResultValue"])) >= floatval(str_replace(",", ".", $respone["UslugaTest_ResultLower"]))) &&
							(!isset($respone["UslugaTest_ResultUpper"]) || floatval(str_replace(",", ".", $respone["UslugaTest_ResultValue"])) <= floatval(str_replace(",", ".", $respone["UslugaTest_ResultUpper"])))
						)
						) {
							$EvnLabSample_IsOutNorm = 2;
						}
					}
				}
			}
		}
		$query = "
			update EvnLabSample
			set EvnLabSample_IsOutNorm = :EvnLabSample_IsOutNorm
			where Evn_id = :EvnLabSample_id
		";
		$queryParams = [
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"EvnLabSample_IsOutNorm" => $EvnLabSample_IsOutNorm
		];
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
			return $result->result_array();
		}
		return false;
	}

	/**
	 * Функция кэширования состава пробы
	 * Должна вызываться при изменении объектов участвующих в запросе, связанных с данной пробой
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function ReCacheLabSampleTest(EvnLabSample_model $callObject, $data)
	{
		$query = "
            with myvars as (
            	select substring(Tests.EvnLabSample_Nums from 1 for (length(Tests.EvnLabSample_Nums)-1)) as v_EvnLabSample_Test
				from
					v_EvnLabSample els
					left join lateral (
                        select string_agg(uc.UslugaComplex_Name, ',') as EvnLabSample_Nums
                        from
                            v_UslugaComplex uc
							inner join v_UslugaTest ut on ut.UslugaComplex_id = uc.UslugaComplex_id
						where ut.EvnLabSample_id = els.EvnLabSample_id
					) as Tests on true
				where els.EvnLabSample_id = :EvnLabSample_id
                limit 1
            )
			update EvnLabSample
			set EvnLabSample_Test = (select v_EvnLabSample_Test from myvars)
			where Evn_id = :EvnLabSample_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
			return $result->result_array();
		}
		return false;
	}

	/**
	 * Функция кэширования статуса пробы
	 * Должна вызываться при изменении объектов участвующих в запросе, связанных с данной пробой
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function ReCacheLabSampleStatus(EvnLabSample_model $callObject, $data)
	{
		$join = "";
		$whereClause = "";
		if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'microbiolab') {
			if (!empty($data['action']) && $data['action'] == 'approve') {
				$join .= 'left join v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id
							left join v_BactMicroProbeAntibiotic bmpa on bmpa.UslugaTest_id = ut.UslugaTest_id';
				$whereClause .= ' and (bmp.BactMicroProbe_id is not null or bmpa.BactMicroProbeAntibiotic_id is not null)';
			} else {
				$join .= " inner join v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id";
			}
		}
		$params = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$query = "
			with myvar1 as (
				select
				    CASE
				        WHEN els.EvnLabSample_setDT is null then null
				        WHEN els.DefectCauseType_id IS NOT NULL then 5
				        WHEN EUP_EvnUslugaPar_id > 0 and NOTAPP_EvnUslugaPar_id = 0 then 4
				        WHEN EUP_EvnUslugaPar_id > 0 then 6 
				        WHEN EUP_HAS_RESULT_EvnUslugaPar_id > 0 then 3
				        WHEN AN.Link_id IS NOT NULL then 2
				        WHEN a.Analyzer_Code = '000' and a.pmUser_insID = 1 and COALESCE(mstdid.MedServiceType_SysNick,'lab') <> 'pzm' then 7
				        ELSE 1
				    END as LabSampleStatus_id
				from
				    v_EvnLabSample els
				    left join v_MedService msdid on msdid.MedService_id = els.MedService_did
				    left join v_MedServiceType mstdid on mstdid.MedServiceType_id = msdid.MedServiceType_id
				    left join lis.v_Analyzer a on a.Analyzer_id = els.Analyzer_id
				    left join lateral(
				        select Link_id
				        from lis.v_Link
				        where link_object = 'EvnLabSample'
				          and object_id = els.EvnLabSample_id
				        limit 1
				    ) as AN on true
				    left join lateral (
				        select
				            max(case when ut.UslugaTest_ResultApproved = 2 then 1 else 0 end) as EUP_EvnUslugaPar_id,
				            max(case when COALESCE(ut.UslugaTest_ResultApproved, 1) = 1 then 1 else 0 end) as NOTAPP_EvnUslugaPar_id,
				            max(case when ut.UslugaTest_ResultValue IS NOT NULL and ut.UslugaTest_ResultValue <> '' then 1 else 0 end) as EUP_HAS_RESULT_EvnUslugaPar_id
				        from v_UslugaTest ut
				        {$join}
				        where ut.EvnLabSample_id = els.EvnLabSample_id
				          and ut.EvnDirection_id is null
				          {$whereClause}
				    ) as EUP on true
				where els.EvnLabSample_id = :EvnLabSample_id
				limit 1    
            ),      
            myvar2 as (
            	select EvnLabSample_StudyDT
                from v_EvnLabSample 
                where EvnLabSample_id = :EvnLabSample_id
            ),
            myvar3 as (
            	select
                	case
                    	when (select LabSampleStatus_id from myvar1) in (1,2,7)
                    	    then null
                    	when ((select LabSampleStatus_id from myvar1) in (3,4,6) and (select EvnLabSample_StudyDT from myvar2) is null)
                    	    then dbo.tzgetdate()
                        else (select EvnLabSample_StudyDT from myvar2)
                    end as EvnLabSample_StudyDT 
            )
			update EvnLabSample
			set
				LabSampleStatus_id = (select LabSampleStatus_id from myvar1),
				EvnLabSample_StudyDT = (select EvnLabSample_StudyDT from myvar3)
			where Evn_id = :EvnLabSample_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		collectEditedData("upd", "EvnLabSample", $params["EvnLabSample_id"]);
		return true;
	}

	/**
	 * Установка комментария
	 * @param EvnLabSample_model $callObject
	 * @param $EvnLabSample_id
	 * @param $EvnLabSample_Comment
	 * @return mixed
	 */
	public static function setComment(EvnLabSample_model $callObject, $EvnLabSample_id, $EvnLabSample_Comment)
	{
		$query = "
			update EvnLabSample
			set EvnLabSample_Comment = :EvnLabSample_Comment
			where Evn_id = :EvnLabSample_id
		";
		$queryParams = [
			"EvnLabSample_id" => $EvnLabSample_id,
			"EvnLabSample_Comment" => $EvnLabSample_Comment
		];
		$res = $callObject->db->query($query, $queryParams);
		if ($res) {
			collectEditedData("upd", "EvnLabSample", $EvnLabSample_id);
		}
		return $res;
	}

	/**
	 * Проставляем дату доставки пробы
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function setDelivDT(EvnLabSample_model $callObject, $data)
	{
		$query = "
			update EvnLabSample
			set EvnLabSample_DelivDT = dbo.tzGetDate()
			where Evn_id = :EvnLabSample_id
			  and EvnLabSample_DelivDT is null
		";
		$res = $callObject->db->query($query, $data);
		if ($res) {
			collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
			return true;
		}
		return false;
	}

	/**
	 * Проставление данных о выполнении пробы
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 */
	public static function setEvnLabSampleDone(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				els.LabSampleStatus_id as \"LabSampleStatus_id\",
				els.MedPersonal_aid as \"MedPersonal_aid\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				ms.Lpu_id as \"Lpu_id\",
				ms.LpuSection_id as \"LpuSection_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_MedService ms on ms.MedService_id = elr.MedService_id
			where els.EvnLabSample_id = :EvnLabSample_id
			limit 1	
		";
		/**@var CI_DB_result $result_els */
		$result_els = $callObject->db->query($query, $data);
		if (is_object($result_els)) {
			$resp_els = $result_els->result_array();
			if (!empty($resp_els[0]["LabSampleStatus_id"]) && in_array($resp_els[0]["LabSampleStatus_id"], [4, 6])) {
				if (!empty($data["session"]["CurLpuSection_id"])) {
					$data["Lpu_aid"] = $data["session"]["lpu_id"];
					$data["LpuSection_aid"] = $data["session"]["CurLpuSection_id"];
				} else {
					$data["Lpu_aid"] = $resp_els[0]["Lpu_id"];
					$data["LpuSection_aid"] = $resp_els[0]["LpuSection_id"];
				}
				$data["MedPersonal_aid"] = $data["session"]["medpersonal_id"];
				$query = "
					update EvnLabSample
					set
						Lpu_aid = :Lpu_aid,
						LpuSection_aid = :LpuSection_aid,
						MedPersonal_aid = :MedPersonal_aid
					where Evn_id = :EvnLabSample_id
				";
				$res = $callObject->db->query($query, $data);
				if ($res) {
					collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
				}
			} else {
				// если не одобрена очищаем поля
				$query = "
					update EvnLabSample
					set
						Lpu_aid = null,
						LpuSection_aid = null,
						MedPersonal_aid = null
					where Evn_id = :EvnLabSample_id
				";
				$res = $callObject->db->query($query, $data);
				if ($res) {
					collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
				}
			}
		}
	}

	/**
	 * Установка врача выполнившего анализ
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return bool|mixed
	 */
	public static function setMedPersonalAID(EvnLabSample_model $callObject, $data)
	{
		if (empty($data["EvnLabSample_id"]) || empty($data["MedPersonal_aid"])) {
			return false;
		}
		$query = "
			update EvnLabSample
			set MedPersonal_aid = :MedPersonal_aid
			where evn_id = :EvnLabSample_id
		";
		return $callObject->db->query($query, $data);
	}

	/**
	 * Взятие пробы
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool|string
	 * @throws Exception
	 */
	public static function takeLabSample(EvnLabSample_model $callObject, $data)
	{
		$resp = false;
		$callObject->elslog->add("Берем пробы | EvnLabSample_id={$data["EvnLabSample_id"]}");
		// 0. для начала надо проверить, а не создана ли уже проба
		$sql_check = "
			select EvnLabSample_setDT  as \"EvnLabSample_setDT\"
			from v_EvnLabSample
			where EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		/**@var CI_DB_result $res_check */
		$res_check = $callObject->db->query($sql_check, $data);
		if (!is_object($res_check)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих записей)");
		}
		$resp_check = $res_check->result_array();
		if (is_array($resp_check) && count($resp_check) > 0 && !empty($resp_check[0]["EvnLabSample_setDT"])) {
			throw new Exception("Проба уже взята");
		}
		$callObject->elslog->add("Проверили не создана ли проба ранее | EvnLabRequest_id={$data["EvnLabRequest_id"]}");
		// чтобы не запустили ещё раз взятие пробы, апдейтим дату взятия сразу.
		$sql = "
			update evn
			set Evn_setDT = dbo.tzGetDate()
			where Evn_id = (
				select evnlabsample_pid
				from v_evnlabsample
				where evnlabsample_id = :EvnLabSample_id	
			)
		";
		$sqlParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$callObject->db->query($sql, $sqlParams);
		$callObject->elslog->add("Проапдейтили дату взятия пробы | EvnLabSample_id={$data["EvnLabSample_id"]}");
		// 1. получаем данные заявки
		$funcParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$lrdata = $callObject->getDataFromEvnLabRequest($funcParams);
		$lrdata["MedPersonal_id"] = $data["session"]["medpersonal_id"];
		$callObject->elslog->add("Получили данные заявки | EvnLabRequest_id={$data["EvnLabRequest_id"]}");
		// определяем Анализатор
		$data["Analyzer_id"] = null;
		$sendToLis = false;
		// получаем состав
		$funcParams = [
			"Lpu_id" => $lrdata["Lpu_id"],
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"EvnDirection_id" => $lrdata["EvnDirection_id"]
		];
		$tests = $callObject->getLabSampleResultGrid($funcParams);
		if (empty($tests)) {
			throw new Exception("Нельзя взять пробу, т.к. в ней отсутствуют исследования");
		}
		$callObject->elslog->add("Получили состав | EvnLabRequest_id={$data["EvnLabRequest_id"]} | Всего тестов = " . count($tests));
		if (empty($data["Analyzer_id"])) {
			$uccodes = [];
			foreach ($tests as $test) {
				if (!in_array($test["UslugaComplex_id"], $uccodes) && $test["UslugaTest_Status"] != "Не назначен") {
					$uccodes[] = [
						"UslugaComplexTest_id" => $test["UslugaComplex_id"],
						"UslugaComplexTarget_id" => $test["UslugaComplexTarget_id"]
					];
				}
			}
			// получаем список возможных анализаторов
			$callObject->load->model("Analyzer_model");
			$funcParams = [
				"Analyzer_IsNotActive" => 1,
				"EvnLabSample_id" => $data["EvnLabSample_id"],
				"uccodes" => $uccodes,
				"MedService_id" => $lrdata["MedService_id"]
			];
			$resp_analyzer = $callObject->Analyzer_model->loadList($funcParams);
			if (count($resp_analyzer) == 1) {
				$data["Analyzer_id"] = $resp_analyzer[0]["Analyzer_id"];
				if ($resp_analyzer[0]["pmUser_insID"] != 1 || $resp_analyzer[0]["Analyzer_Code"] != "000") { // если не ручные методики
					$sendToLis = true;
				}
			} elseif (count($resp_analyzer) > 1) {
				if ($resp_analyzer[0]["pmUser_insID"] == 1 && $resp_analyzer[0]["Analyzer_Code"] == "000") { // если первый ручные методики
					$data["Analyzer_id"] = $resp_analyzer[1]["Analyzer_id"]; // тогда берём второй
					$sendToLis = true;
				} else { // иначе берём первый
					$data["Analyzer_id"] = $resp_analyzer[0]["Analyzer_id"]; // тогда берём первый
					$sendToLis = true;
				}
			}
			$callObject->elslog->add("Получили список возможных анализаторов и выбрали анализатор | EvnLabRequest_id={$data["EvnLabRequest_id"]} | Analyzer_id={$data["Analyzer_id"]}");
		}
		$EvnLabSample_DelivDTField = "(select curtime from myvars)";
		if (!empty($data["MedServiceType_SysNick"]) && in_array($data["MedServiceType_SysNick"], ["pzm"])) {
			$EvnLabSample_DelivDTField = "null";
		}
		$UslugaComplexTargetIds = [];
		foreach ($tests as $test) {
			if (!in_array($test["UslugaComplexTarget_id"], $UslugaComplexTargetIds)) {
				$UslugaComplexTargetIds[] = $test["UslugaComplexTarget_id"];
			}
		}
		if (!empty($UslugaComplexTargetIds)) {
			$UslugaComplexTargetIdsString = implode("','", $UslugaComplexTargetIds);
			$sql = "
				select COALESCE(ucms_child.RefSample_id, ucms.RefSample_id) as \"RefSample_id\"
				from
					v_UslugaComplexMedService ucms
					left join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
				where ucms.UslugaComplex_id in ('{$UslugaComplexTargetIdsString}')
				  and ucms.MedService_id = :MedService_id
				  and ucms.UslugaComplexMedService_pid is null
				  and COALESCE(ucms_child.RefSample_id, ucms.RefSample_id) is not null
				limit 1	
			";
			$sqlParams = [
				"MedService_id" => $lrdata["MedService_id"],
				"UslugaComplex_id" => $lrdata["UslugaComplex_id"]
			];
			$resp_rs = $callObject->queryResult($sql, $sqlParams);
			if (!empty($resp_rs[0]["RefSample_id"])) {
				$data["RefSample_id"] = $resp_rs[0]["RefSample_id"];
			}
		}
		$callObject->elslog->add("Получили биоматериал | EvnLabRequest_id={$data["EvnLabRequest_id"]}");
		$funcParams = [
			"Lpu_id" => $lrdata["Lpu_id"],
			"MedService_id" => $lrdata["MedService_id"]
		];
		$resp_num = $callObject->getNewLabSampleNum($funcParams);
		if (is_array($resp_num)) {
			return $resp_num;
		}
		$data["EvnLabSample_Num"] = $resp_num;
		$callObject->elslog->add("Сгенерировали номер | EvnLabRequest_id={$data["EvnLabRequest_id"]} | EvnLabSample_Num={$resp_num}");
		// создаём пробу в заявке и проставляем врача/отделение/дату взятия
		$query = "
			with myvars as (
				select
					dbo.tzgetdate() as curtime,
					MedService_id
				from v_EvnLabSample
				where EvnLabSample_id = :EvnLabSample_id
				limit 1		
			)
			select
				evnlabsample_id as \"EvnLabSample_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\",
				to_char ((select curtime from myvars), 'YYYY-MM-DD HH24:MI:SS') as \"EvnLabSample_setDTForBD\", 
            	to_char((select curtime from myvars), 'HH24:MI dd.mm.yyyy') as \"EvnLabSample_setDT\"
			from p_EvnLabSample_upd(
				evnlabsample_id := :EvnLabSample_id,
				evnlabrequest_id := :EvnLabRequest_id,
				refsample_id := :RefSample_id,
				lpu_id := :Lpu_id,
				server_id := :Server_id,
				personevn_id := :PersonEvn_id,
				evnlabsample_num := :EvnLabSample_Num,
				evnlabsample_barcode := :EvnLabSample_BarCode,
				medservice_id := (select MedService_id from myvars),
				medservice_did := :MedService_did,
				evnlabsample_setdt := (select curtime from myvars),
				evnlabsample_delivdt := {$EvnLabSample_DelivDTField},
				lpu_did := :Lpu_did,
				analyzer_id := :Analyzer_id,
				lpusection_did := :LpuSection_did,
				medpersonal_did := :MedPersonal_did,
				pmuser_id := :pmUser_id
			)	
		";
		if (!empty($data["session"]["CurLpuSection_id"])) {
			$data["Lpu_did"] = $data["session"]["lpu_id"];
			$data["LpuSection_did"] = $data["session"]["CurLpuSection_id"];
			$data["MedPersonal_did"] = (!empty($data["methodAPI"]) && !empty($data["MedPersonal_did"])) ? $data["MedPersonal_did"] : $data["session"]["medpersonal_id"];
		} else {
			$data["Lpu_did"] = $lrdata["Lpu_id"];
			$data["LpuSection_did"] = $lrdata["LpuSection_id"];
			$data["MedPersonal_did"] = (!empty($data["methodAPI"]) && !empty($data["MedPersonal_did"])) ? $data["MedPersonal_did"] : $lrdata["MedPersonal_id"];
		}
		$params = [
			"pmUser_id" => $data["pmUser_id"],
			"MedPersonal_id" => $lrdata["MedPersonal_id"],
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"RefSample_id" => $data["RefSample_id"],
			"EvnLabRequest_id" => $data["EvnLabRequest_id"],
			"EvnLabSample_Num" => $data["EvnLabSample_Num"],
			"EvnLabSample_BarCode" => $data["EvnLabSample_Num"],
			"MedService_did" => $data["MedService_did"],
			"Lpu_id" => $lrdata["Lpu_id"],
			"Lpu_did" => $data["Lpu_did"],
			"LpuSection_did" => $data["LpuSection_did"],
			"MedPersonal_did" => $data["MedPersonal_did"],
			"Analyzer_id" => $data["Analyzer_id"],
			"LpuSection_id" => $lrdata["LpuSection_id"],
			"PersonEvn_id" => $lrdata["PersonEvn_id"],
			"Server_id" => $lrdata["Server_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		// проверяем чтоб не было за сегодня пробы с таким же номером
		$sql = "
			select EvnLabSample_id as \"EvnLabSample_id\"
			from v_EvnLabSample 
			where EvnLabSample_id <> :EvnLabSample_id 
			  and EvnLabSample_Num = :EvnLabSample_Num 
			  and EvnLabSample_setDT = dbo.tzgetdate()
            limit 1
		";
		$sqlParams = [
			"EvnLabSample_Num" => $data["EvnLabSample_Num"],
			"EvnLabSample_id" => $data["EvnLabSample_id"]
		];
		$res_check = $callObject->db->query($sql, $sqlParams);
		$callObject->elslog->add("Повторно проверили что нет за сегодня пробы с таким же номером | EvnLabSample_id={$data["EvnLabSample_id"]}");
		if (!is_object($res_check)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих записей номеров пробы)");
		}
		$resp_check = $res_check->result_array();
		if (is_array($resp_check) && count($resp_check) > 0 && !empty($resp_check[0]["EvnLabSample_id"])) {
			$callObject->elslog->add("Обнаружен дубль | EvnLabSample_id={$data["EvnLabSample_id"]}");
			throw new Exception("Обнаружено дублирование номеров проб, необходимо повторить взятие пробы");
		}
		if (is_object($result)) {
			$resp = $result->result_array();
			if (!is_array($resp)) {
				throw new Exception("Ошибка при изменение пробы");
			}
			if (!$callObject->isSuccessful($resp)) {
				return $resp;
			}
			collectEditedData("upd", "EvnLabSample", $data["EvnLabSample_id"]);
			$callObject->elslog->add("Создали пробу в заявке и проставили врача/отделение/дату взятия | EvnLabRequest_id={$data["EvnLabRequest_id"]}");
			if (empty($resp[0]["EvnLabSample_id"])) {
				return $resp;
			}
			//проверяем на дубли по штрих-кодам - такое редко, но встречается
			$sql = "
				select els.Evn_id as \"EvnLabSample_id\"
				from
					EvnLabSample els
					inner join v_Evn e on e.Evn_id = els.Evn_id 
				where els.EvnLabSample_Num = :EvnLabSample_Num
				  and els.Evn_id != :EvnLabSample_id
				  and cast(e.Evn_insDT as date) = cast(dbo.tzgetdate() as date)
				limit 1	  
			";
			$sqlParams = [
				"EvnLabSample_id" => $resp[0]["EvnLabSample_id"],
				"EvnLabSample_Num" => $data["EvnLabSample_Num"]
			];
			$EvnLabSampleNumCheck = $callObject->getFirstResultFromQuery($sql, $sqlParams);
			if (!empty($EvnLabSampleNumCheck)) {
				throw new Exception("У пробы не уникальный штрих-код, пожалуйста возьмите пробу ещё раз.");
			}
			// сохраняем в пробе список тестов (с минимальным набором полей)
			$data["EvnLabSample_id"] = $resp[0]["EvnLabSample_id"];
			$data["EvnLabSample_setDT"] = $resp[0]["EvnLabSample_setDTForBD"];
			$callObject->saveLabSampleTests($data, $lrdata, null, $tests);
			$callObject->elslog->add("Сохранили все тесты по созданной пробе (saveLabSampleTests) | EvnLabRequest_id={$data["EvnLabRequest_id"]} | EvnLabSample_id={$data["EvnLabSample_id"]}");
			$resp[0]["EvnLabSample_ShortNum"] = substr($data["EvnLabSample_Num"], -4);
			$resp[0]["EvnLabSample_BarCode"] = $data["EvnLabSample_Num"];
			$callObject->ReCacheLabSampleStatus(["EvnLabSample_id" => $resp[0]["EvnLabSample_id"]]);
			$callObject->elslog->add("Перекэшировали статусы (ReCacheLabSampleStatus) | EvnLabRequest_id={$data["EvnLabRequest_id"]} | EvnLabSample_id={$data["EvnLabSample_id"]}");
		}
		// кэшируем статус заявки
		$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		$funcParams = [
			"EvnLabRequest_id" => $data["EvnLabRequest_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->EvnLabRequest_model->ReCacheLabRequestStatus($funcParams);
		$callObject->elslog->add("Закэшировали статус заявки (ReCacheLabRequestStatus) | EvnLabRequest_id={$data["EvnLabRequest_id"]}");
		// кэшируем статус проб в заявке
		$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
		$callObject->elslog->add("Закэшировали статус проб в заявке (ReCacheLabRequestSampleStatusType) | EvnLabRequest_id={$data["EvnLabRequest_id"]}");
		// а это вне транзакцию можно делать, раз проба уже сохранена.
		if (!empty($data["sendToLis"]) && !empty($data["Analyzer_id"]) && $sendToLis) {
			$callObject->load->helper("Xml");
			// отправляем в АС МЛО
			$callObject->load->model("AsMlo_model", "lab_model");
			$creation = $callObject->lab_model->createRequest2($data, true);
			if (is_array($creation) && !empty($creation["Error_Msg"])) {
				$resp[0]["Alert_Msg"] = $creation["Error_Msg"];
			}
			$callObject->elslog->add("Отправили в АС МЛО | EvnLabRequest_id={$data["EvnLabRequest_id"]}");
		}
		$callObject->elslog->add("Финиш | EvnLabRequest_id={$data["EvnLabRequest_id"]}\n");
		return $resp;
	}

	/**
	 * Перенос тестов из одной пробы в другую
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function transferLabSampleResearches(EvnLabSample_model $callObject, $data)
	{
		$researchData = [];
		// получаем данные заявки
		$lrdata = $callObject->getDataFromEvnLabRequest(["EvnLabSample_id" => $data["EvnLabSample_oldid"]]);
		foreach ($data["tests"] as $test) {
			// 1. получаем данные по исследованию
			if (empty($researchData[$test["UslugaTest_pid"]])) {
				$query = "
					select
						EvnUsluga_id as \"EvnUslugaPar_id\",
						UslugaComplex_id as \"UslugaComplex_id\"
					from v_EvnUsluga
					where EvnUsluga_id = :UslugaTest_pid
				";
				$resp = $callObject->queryResult($query, ["UslugaTest_pid" => $test["UslugaTest_pid"]]);
				if (!empty($resp[0])) {
					$researchData[$test["UslugaTest_pid"]] = $resp[0];
				}
			}
			if (empty($researchData[$test["UslugaTest_pid"]])) {
				throw new Exception("Ошибка получения данных по исследованию");
			}
			// назначем тест в новой пробе
			$data["EvnLabRequest_id"] = $lrdata["EvnLabRequest_id"];
			$data["tests"] = [
				[
					"UslugaTest_pid" => $test["UslugaTest_pid"],
					"UslugaComplex_id" => $test["UslugaComplex_id"]
				]
			];
			$data["EvnLabSample_id"] = $data["EvnLabSample_newid"];
			$callObject->prescrTest($data);
			// отменяем тест в старой пробе
			$data["tests"] = [
				[
					"UslugaTest_pid" => $test["UslugaTest_pid"],
					"UslugaComplex_id" => $test["UslugaComplex_id"]
				]
			];
			$data["EvnLabSample_id"] = $data["EvnLabSample_oldid"];
			$callObject->cancelTest($data);
		}
		// после переноса, если проба взята надо переопределить анализтор
		$query = "
			select EvnLabSample_setDT as \"EvnLabSample_setDT\"
			from v_EvnLabSample
			where EvnLabSample_id = :EvnLabSample_id
		";
		$resp = $callObject->queryResult($query, ["EvnLabSample_id" => $data["EvnLabSample_oldid"]]);
		if (!empty($resp[0]["EvnLabSample_setDT"])) {
			$data["Analyzer_id"] = null;
			// получаем состав
			$funcParams = [
				"Lpu_id" => $lrdata["Lpu_id"],
				"EvnLabSample_id" => $data["EvnLabSample_oldid"],
				"EvnDirection_id" => $lrdata["EvnDirection_id"]
			];
			$tests = $callObject->getLabSampleResultGrid($funcParams);
			$uccodes = [];
			foreach ($tests as $test) {
				if (!in_array($test["UslugaComplex_id"], $uccodes) && $test["UslugaTest_Status"] != "Не назначен") {
					$uccodes[] = [
						"UslugaComplexTest_id" => $test["UslugaComplex_id"],
						"UslugaComplexTarget_id" => $test["UslugaComplexTarget_id"]
					];
				}
			}
			// получаем список возможных анализаторов
			$callObject->load->model("Analyzer_model");
			$funcParams = [
				"Analyzer_IsNotActive" => 1,
				"EvnLabSample_id" => $data["EvnLabSample_oldid"],
				"uccodes" => $uccodes,
				"MedService_id" => $lrdata["MedService_id"]
			];
			$resp_analyzer = $callObject->Analyzer_model->loadList($funcParams);
			if (count($resp_analyzer) == 1) {
				$data["Analyzer_id"] = $resp_analyzer[0]["Analyzer_id"];
			} elseif (count($resp_analyzer) > 1) {
				$data["Analyzer_id"] = ($resp_analyzer[0]["pmUser_insID"] == 1 && $resp_analyzer[0]["Analyzer_Code"] == "000")
					? $resp_analyzer[1]["Analyzer_id"]
					: $resp_analyzer[0]["Analyzer_id"];
			}
			$funcParams = [
				"EvnLabSample_id" => $data["EvnLabSample_oldid"],
				"Analyzer_id" => $data["Analyzer_id"],
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->saveLabSampleAnalyzer($funcParams);
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Снятие одобрения результатов
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function unapproveResults(EvnLabSample_model $callObject, $data)
	{
		// только где есть результат
		$filter = " and ut.UslugaTest_ResultValue is not null and ut.UslugaTest_ResultValue <> ''";
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"EvnLabSample_id" => $data["EvnLabSample_id"]
		];
		if (isset($data["UslugaTest_id"])) {
			$filter .= " and ut.UslugaTest_id = :UslugaTest_id";
			$params["UslugaTest_id"] = $data["UslugaTest_id"];
		}
		if (!empty($data["UslugaTest_ids"])) {
			$UslugaTest_ids = json_decode($data["UslugaTest_ids"]);
			if (!empty($UslugaTest_ids)) {
				$UslugaTest_idsString = implode(",", $UslugaTest_ids);
				$filter .= " and ut.UslugaTest_id IN ({$UslugaTest_idsString})";
			}
		}
		$query = "
			select
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_pid as \"UslugaTest_pid\"
			from
				v_UslugaTest ut
				inner join v_EvnLabSample ls on ut.EvnLabSample_id = ls.EvnLabSample_id
			where ls.EvnLabSample_id = :EvnLabSample_id
			  {$filter}
		";
		$resp_eup = $callObject->queryResult($query, $params);
		$EvnUslugaParChanged = [];
		foreach ($resp_eup as $respone) {
			if (!in_array($respone["UslugaTest_pid"], $EvnUslugaParChanged)) {
				$EvnUslugaParChanged[] = $respone["UslugaTest_pid"];
			}
			$query = "
				update UslugaTest
				set UslugaTest_ResultApproved = 1
				where UslugaTest_id = :UslugaTest_id
			";
			$res = $callObject->db->query($query, ["UslugaTest_id" => $respone["UslugaTest_id"]]);
			if ($res) {
				collectEditedData("upd", "UslugaTest", $data["UslugaTest_id"]);
			}
		}
		if (!empty($EvnUslugaParChanged)) {
			$funcParams = [
				"EvnUslugaParChanged" => $EvnUslugaParChanged,
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->onChangeApproveResults($funcParams);
		}
		$callObject->ReCacheLabSampleStatus($data);
		// если проба стала не одобренной очищаем данные о выполнившем враче.
		$funcParams = [
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"session" => $data["session"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$callObject->setEvnLabSampleDone($funcParams);
		$resultReCache = $callObject->ReCacheLabRequestByLabSample($funcParams);
		return ["Error_Msg" => "", "UslugaExecutionType_id" => $resultReCache["UslugaExecutionType_id"]];
	}

	/**
	 * Изменение информации о взятии пробы. Метод для API
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	public static function updateEvnLabSampleAPI(EvnLabSample_model $callObject, $data)
	{
		$callObject->assign($data);
		$callObject->EvnLabSample_id = $data["EvnLabSample_id"];
		$callObject->load();
		// служба и служба взятия не приходят с формы, должны остаться теми же, что и были.
		$data["MedService_id"] = (!empty($data["MedService_id"])) ? $data["MedService_id"] : $callObject->fields["MedService_id"];
		$data["MedService_did"] = (!empty($data["MedService_did"])) ? $data["MedService_did"] : $callObject->fields["MedService_did"];
		$data["Server_id"] = (!empty($callObject->fields["Server_id"])) ? $callObject->fields["Server_id"] : $data["Server_id"];
		$callObject->assign($data);
		$result = $callObject->save($data);
		return $result;
	}

	/**
	 * Обновление результата
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	public static function updateResult(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				ut.UslugaTest_Result as \"UslugaTest_Result\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				ut.Usluga_id as \"Usluga_id\",
				ut.PayType_id as \"PayType_id\",
				ut.UslugaPlace_id as \"UslugaPlace_id\",
				ut.UslugaTest_pid as \"UslugaTest_pid\",
				ut.UslugaTest_rid as \"UslugaTest_rid\",
				ut.UslugaTest_setDT as \"UslugaTest_setDT\",
				ut.Lpu_id as \"Lpu_id\",
				ut.Server_id as \"Server_id\",
				ut.PersonEvn_id as \"PersonEvn_id\",
				ut.EvnDirection_id as \"EvnDirection_id\",
				ut.EvnLabSample_id as \"EvnLabSample_id\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				ut.UslugaTest_CheckDT as \"UslugaTest_CheckDT\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				ut.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				ut.UslugaTest_ResultQualitativeText as \"UslugaTest_ResultQualitativeText\",
				ut.RefValues_id as \"RefValues_id\",
				ut.Unit_id as \"Unit_id\",
				ut.UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				ut.UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				a.Analyzer_IsAutoOk as \"Analyzer_IsAutoOk\"
			from
				v_UslugaTest ut
				left join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
				left join lis.v_Analyzer a on a.Analyzer_id = els.Analyzer_id
			where ut.UslugaTest_id = :UslugaTest_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		$EvnUslugaParChanged = [];
		$updDate = ':UslugaTest_setDT';
        $chkDate = 'null';
		if (count($resp) == 0) {
			return false;
		}
		// подменяем результат
		if ($data["updateType"] == "fromLISwithRefValues") {
			$resp[0]["UslugaTest_ResultValue"] = $data["UslugaTest_ResultValue"];
			// при изменении результата убираем одобрение
			if (!in_array($resp[0]["UslugaTest_pid"], $EvnUslugaParChanged)) {
				$EvnUslugaParChanged[] = $resp[0]["UslugaTest_pid"];
			}
			$resp[0]["UslugaTest_ResultApproved"] = 1;
            $resp[0]["UslugaTest_CheckDT"] = null;
            $resp[0]["RefValues_id"] = $data["RefValues_id"];
			$resp[0]["UslugaTest_ResultLower"] = $data["UslugaTest_ResultLower"];
			$resp[0]["UslugaTest_ResultUpper"] = $data["UslugaTest_ResultUpper"];
			$resp[0]["UslugaTest_ResultUnit"] = $data["UslugaTest_ResultUnit"];
			if ($data["UslugaTest_ResultValue"] != "") {
				if (!empty($data["UslugaTest_setDT"])) {
					$resp[0]["UslugaTest_setDT"] = $data["UslugaTest_setDT"];
				} else {
					$updDate = "(dbo.tzGetDate())";
				}
			}
		}
		if ($data["updateType"] == "fromLIS") {
			$resp[0]["UslugaTest_ResultValue"] = $data["UslugaTest_ResultValue"];
			// при изменении результата убираем одобрение
			if (!in_array($resp[0]["UslugaTest_pid"], $EvnUslugaParChanged)) {
				$EvnUslugaParChanged[] = $resp[0]["UslugaTest_pid"];
			}
			$resp[0]["UslugaTest_ResultApproved"] = 1;
            $resp[0]["UslugaTest_CheckDT"] = null;
            if ($data["UslugaTest_ResultValue"] != "") {
				if (!empty($data["UslugaTest_setDT"])) {
					$resp[0]["UslugaTest_setDT"] = $data["UslugaTest_setDT"];
				} else {
					$updDate = "(dbo.tzGetDate())";
				}
			}
		}

		if ($data["updateType"] == "value") {
			$resp[0]["UslugaTest_ResultValue"] = $data["UslugaTest_ResultValue"];
			// при изменении результата убираем одобрение
			if (!in_array($resp[0]["UslugaTest_pid"], $EvnUslugaParChanged)) {
				$EvnUslugaParChanged[] = $resp[0]["UslugaTest_pid"];
			}
			$resp[0]["UslugaTest_ResultApproved"] = 1;
            $resp[0]["UslugaTest_CheckDT"] = null;
			$updDate = ($data["UslugaTest_ResultValue"] != "") ? "(dbo.tzGetDate())" : "null";
		}
		if ($data["updateType"] == "comment") {
			$resp[0]["UslugaTest_Comment"] = $data["UslugaTest_Comment"];
		}
		if (
			!empty($data["isAutoApprove"]) ||
			(isset($resp[0]["Analyzer_IsAutoOk"]) && $resp[0]["Analyzer_IsAutoOk"] == 2) ||
			(isset($data["sourceName"]) && $data["sourceName"] === "form250")
		) {

            if (!empty($data['UslugaTest_ResultValue'])) {
                $resp[0]['UslugaTest_ResultApproved'] = 2;
                $chkDate = '(dbo.tzgetdate())';
            } else {
                $resp[0]['UslugaTest_ResultApproved'] = 1;
                $resp[0]['UslugaTest_CheckDT'] = null;
            }
		}
		if (!empty($data["UslugaTest_RefValues"])) {
			$data["UslugaTest_RefValues"] = toUtf($data["UslugaTest_RefValues"]);
			$UslugaTest_RefValues = json_decode($data["UslugaTest_RefValues"], true);
			array_walk($UslugaTest_RefValues, "ConvertFromUTF8ToWin1251");
			$resp[0]["UslugaTest_ResultQualitativeNorms"] = $UslugaTest_RefValues["UslugaTest_ResultQualitativeNorms"];
			$UslugaTest_ResultQualitativeText = "";
			if (!empty($resp[0]["UslugaTest_ResultQualitativeNorms"])) {
				$UslugaTest_ResultQualitativeNorms = json_decode($resp[0]["UslugaTest_ResultQualitativeNorms"], true);
				if (is_array($UslugaTest_ResultQualitativeNorms)) {
					foreach ($UslugaTest_ResultQualitativeNorms as $UslugaTest_ResultQualitativeNorm) {
						if (!empty($UslugaTest_ResultQualitativeText)) {
							$UslugaTest_ResultQualitativeText .= ", ";
						}
						$UslugaTest_ResultQualitativeText .= $UslugaTest_ResultQualitativeNorm;
					}
				}
			}
			$resp[0]["UslugaTest_ResultQualitativeText"] = $UslugaTest_ResultQualitativeText;
			$resp[0]["UslugaTest_ResultNorm"] = $UslugaTest_RefValues["UslugaTest_ResultNorm"];
			$resp[0]["UslugaTest_ResultCrit"] = $UslugaTest_RefValues["UslugaTest_ResultCrit"];
			$resp[0]["UslugaTest_ResultLower"] = $UslugaTest_RefValues["UslugaTest_ResultLower"];
			$resp[0]["UslugaTest_ResultUpper"] = $UslugaTest_RefValues["UslugaTest_ResultUpper"];
			$resp[0]["UslugaTest_ResultLowerCrit"] = $UslugaTest_RefValues["UslugaTest_ResultLowerCrit"];
			$resp[0]["UslugaTest_ResultUpperCrit"] = $UslugaTest_RefValues["UslugaTest_ResultUpperCrit"];
			$resp[0]["UslugaTest_ResultUnit"] = $UslugaTest_RefValues["UslugaTest_ResultUnit"];
			$resp[0]["UslugaTest_Comment"] = $UslugaTest_RefValues["UslugaTest_Comment"];
			$resp[0]["RefValues_id"] = $UslugaTest_RefValues["RefValues_id"];
			$resp[0]["Unit_id"] = $UslugaTest_RefValues["Unit_id"];
		}
		if (!empty($data["UslugaTest_Comment"]) && $data["updateType"] != "comment") {
			if (!empty($resp[0]["UslugaTest_Comment"])) $resp[0]["UslugaTest_Comment"] .= " | ";
			//пишем комментарий для теста, полученный от анализатора (внешней службы)
			$resp[0]["UslugaTest_Comment"] .= $data["UslugaTest_Comment"];
		}
		$ResultDataJson = json_encode([
			"EUD_value" => toUtf(trim($resp[0]["UslugaTest_ResultValue"])),
			"EUD_lower_bound" => toUtf(trim($resp[0]["UslugaTest_ResultLower"])),
			"EUD_upper_bound" => toUtf(trim($resp[0]["UslugaTest_ResultUpper"])),
			"EUD_unit_of_measurement" => toUtf(trim($resp[0]["UslugaTest_ResultUnit"]))
		]);
		// сохраняем
		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_uslugatest_upd
			(
				UslugaTest_id := :UslugaTest_id,
				UslugaTest_pid := :UslugaTest_pid,
				UslugaTest_rid := :UslugaTest_rid,
				UslugaTest_setDT := {$updDate},
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				UslugaComplex_id := :UslugaComplex_id,
				EvnDirection_id := :EvnDirection_id,
				Usluga_id := :Usluga_id,
				PayType_id := :PayType_id,
				UslugaPlace_id := :UslugaPlace_id,
				UslugaTest_Result := :UslugaTest_Result,
				UslugaTest_ResultApproved := :UslugaTest_ResultApproved,
				UslugaTest_CheckDT := {$chkDate},
				UslugaTest_Comment := :UslugaTest_Comment,
				UslugaTest_ResultLower := :UslugaTest_ResultLower, 
				UslugaTest_ResultUpper := :UslugaTest_ResultUpper, 
				UslugaTest_ResultQualitativeNorms := :UslugaTest_ResultQualitativeNorms, 
				UslugaTest_ResultQualitativeText := :UslugaTest_ResultQualitativeText,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				UslugaTest_ResultLowerCrit := :UslugaTest_ResultLowerCrit,
				UslugaTest_ResultUpperCrit := :UslugaTest_ResultUpperCrit,
				UslugaTest_ResultUnit := :UslugaTest_ResultUnit,
				UslugaTest_ResultValue := :UslugaTest_ResultValue::varchar,
				EvnLabSample_id := :EvnLabSample_id,
				pmUser_id := :pmUser_id
			)
		";
		$params = [
			"UslugaTest_id" => $data["UslugaTest_id"],
			"UslugaTest_pid" => $resp[0]["UslugaTest_pid"],
			"UslugaTest_rid" => $resp[0]["UslugaTest_rid"],
			"UslugaTest_setDT" => $resp[0]["UslugaTest_setDT"],
			"Lpu_id" => $resp[0]["Lpu_id"],
			"Server_id" => $resp[0]["Server_id"],
			"PersonEvn_id" => $resp[0]["PersonEvn_id"],
			"UslugaComplex_id" => $resp[0]["UslugaComplex_id"],
			"Usluga_id" => $resp[0]["Usluga_id"],
			"PayType_id" => $resp[0]["PayType_id"],
			"UslugaPlace_id" => $resp[0]["UslugaPlace_id"],
			"EvnDirection_id" => $resp[0]["EvnDirection_id"],
			"UslugaTest_Result" => $ResultDataJson,
			"EvnLabSample_id" => $resp[0]["EvnLabSample_id"],
			"pmUser_id" => $data["pmUser_id"],
			"UslugaTest_ResultApproved" => $resp[0]["UslugaTest_ResultApproved"],
			"UslugaTest_Comment" => $resp[0]["UslugaTest_Comment"],
			"UslugaTest_ResultLower" => $resp[0]["UslugaTest_ResultLower"],
			"UslugaTest_ResultUpper" => $resp[0]["UslugaTest_ResultUpper"],
            "UslugaTest_CheckDT" => $resp[0]["UslugaTest_CheckDT"],
			"UslugaTest_ResultQualitativeNorms" => $resp[0]["UslugaTest_ResultQualitativeNorms"],
			"UslugaTest_ResultQualitativeText" => $resp[0]["UslugaTest_ResultQualitativeText"],
			"RefValues_id" => !empty($resp[0]["RefValues_id"]) ? $resp[0]["RefValues_id"] : null,
			"Unit_id" => !empty($resp[0]["Unit_id"]) ? $resp[0]["Unit_id"] : null,
			"UslugaTest_ResultLowerCrit" => $resp[0]["UslugaTest_ResultLowerCrit"],
			"UslugaTest_ResultUpperCrit" => $resp[0]["UslugaTest_ResultUpperCrit"],
			"UslugaTest_ResultUnit" => $resp[0]["UslugaTest_ResultUnit"],
			"UslugaTest_ResultValue" => ($resp[0]["UslugaTest_ResultValue"])
		];
		$response = $callObject->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при изменении результата");
		}
		if (!$callObject->isSuccessful($response)) {
			return $response;
		}
		collectEditedData("upd", "UslugaTest", $data["UslugaTest_id"]);
		if (!empty($EvnUslugaParChanged)) {
			$funcParams = [
				"EvnUslugaParChanged" => $EvnUslugaParChanged,
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->onChangeApproveResults($funcParams);
		}
		if (empty($data["disableRecache"])) {
			if ($data["updateType"] != "fromLIS") {
				$callObject->ReCacheLabSampleIsOutNorm(["EvnLabSample_id" => $resp[0]["EvnLabSample_id"]]);
			}
			$callObject->ReCacheLabSampleStatus(["EvnLabSample_id" => $resp[0]["EvnLabSample_id"]]);
			// кэшируем статус заявки
			$callObject->load->model("EvnLabRequest_model", "EvnLabRequest_model");
			$funcParams = [
				"EvnLabRequest_id" => $resp[0]["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->EvnLabRequest_model->ReCacheLabRequestStatus($funcParams);
			// кэшируем названия услуг
			$callObject->EvnLabRequest_model->ReCacheLabRequestUslugaName($funcParams);
			// кэшируем статус проб в заявке
			$callObject->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($funcParams);
			// создаём/обновляем протокол
			$funcParams = [
				"EvnLabSample_id" => $resp[0]["EvnLabSample_id"],
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->ReCacheLabRequestByLabSample($funcParams);
		}
		collectEditedData("upd", "UslugaTest", $data["UslugaTest_id"]);
		return $response;
	}

	/**
	 * Изменение результатов лабораторного исследования. Метод для API
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function updateUslugaTestAPI(EvnLabSample_model $callObject, $data)
	{
		if (empty($data["UslugaTest_id"])) {
			throw new Exception("Не передан параметр UslugaTest_id");
		}
		//проверка существования извещения
		$arrUsluga = $callObject->getUslugaTest(["UslugaTest_id" => $data["UslugaTest_id"]]);
		if (!$arrUsluga) {
			throw new Exception("Услуга не найдена");
		}
		//удостоверимся, что UslugaComplex_id соответствует тесту (приходит через POST)
		if (!empty($data["UslugaComplex_id"]) && $data["UslugaComplex_id"] != $arrUsluga[0]["UslugaComplex_id"]) {
			throw new Exception("Услуга не найдена");
		}
		$params = $arrUsluga[0];
		$params["pmUser_id"] = $data["pmUser_id"];
		if (!empty($data["MedPersonal_aid"])) {
			$params["MedPersonal_aid"] = $data["MedPersonal_aid"];
		}
		foreach ($params as $key => $value) {
			if (!empty($data[$key])) {
				$params[$key] = $data[$key];
			}
		}
		// меняю server_id на server_id  соответстствующей записи в v_personevn (иначе процедура может выкинуть ошибки)
		if (!empty($params["PersonEvn_id"])) {
			$res = $callObject->common->GET("Person/serverByPersonEvn", [
				"PersonEvn_id" => $params["personEvn_id"]
			]);
			if (!$callObject->isSuccessful($res)) {
				return $res;
			}
			$res = $res["Server_id"]["data"][0];
			$params["Server_id"] = $res > 0 ? $res : $params["Server_id"];
		}
		$result = $callObject->saveUslugaTest($params);
		if ($result && !empty($data["UslugaTest_deleted"]) && $data["UslugaTest_deleted"] == 2 && !empty($data["UslugaTest_delDT"])) {
			//отменяем отдельно
			$params["tests"] = [[
				"UslugaTest_pid" => $params["UslugaTest_pid"],
				"UslugaComplex_id" => $params["UslugaComplex_id"]
			]];
			$query = "
				select EvnLabRequest_id as \"EvnLabRequest_id\"
				from v_EvnLabSample
				where EvnLabSample_id = :EvnLabSample_id
				limit 1
			";
			$res = $callObject->getFirstRowFromQuery($query, $params);
			if (!empty($res["EvnLabRequest_id"])) {
				$params["EvnLabRequest_id"] = $res["EvnLabRequest_id"];
				$query = "
					select EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
					from v_EvnLabRequestUslugaComplex 
					where EvnLabSample_id = :EvnLabSample_id 
					  and EvnLabRequest_id = :EvnLabRequest_id
					  and UslugaComplex_id = :UslugaComplex_id
					limit 1
				";
				$res = $callObject->getFirstRowFromQuery($query, $params);
				if (!empty($res["EvnLabRequestUslugaComplex_id"])) {
					$params["EvnLabRequestUslugaComplex_id"] = $res["EvnLabRequestUslugaComplex_id"];
					$callObject->cancelTest($params);
				}
			}
		}
		if ($result && !empty($data["approveEvnLabSampleResults"])) {
			// помечаем как одобренные
			$params["EvnLabSamples"] = json_encode([$params["EvnLabSample_id"]]);
			$params["session"] = $data["session"];
			$callObject->approveEvnLabSampleResults($params);
		}
		// устанавливаем врача выполнившего анализ
		$callObject->setMedPersonalAID($params);
		return $result;
	}
}