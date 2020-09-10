<?php

class Person_model_check
{
	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkChildrenDuplicates(Person_model $callObject, $data)
	{
		if (!isset($data["Person_pid"])) {
			return true;
		}
		$children = "";
		$query = "
			select 
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				case when PS.Sex_id = 1 then 'муж' else 'жен' end as \"Sex\"
			from
				v_PersonDeputy PD 
            	left join v_PersonState PS on PS.Person_id = PD.Person_id
            where PD.Person_pid=:Person_pid
              and PD.DeputyKind_id = :DeputyKind_id
              and PS.Sex_id = :Sex_id
              and ps.Person_BirthDay::date = :Person_BirthDay::date
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			return true;
		}
		$result = $result->result_array();
		if (count($result) == 0) {
			return true;
		}
		foreach ($result as $item) {
			$children .= "{$item["Person_SurName"]} {$item["Person_FirName"]} {$item["Person_SecName"]}; д/р:{$item["Person_BirthDay"]}; пол:{$item["Sex"]}<br>";
		}
		$query = "
			select 
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\"
			from v_PersonState PS  
	           where Person_id = :Person_pid
	        limit 1
		";
		$result = $callObject->db->query($query, $data);
		$result = $result->result_array();
		$parent = "{$result[0]["Person_SurName"]} {$result[0]["Person_FirName"]} {$result[0]["Person_SecName"]}; д/р:{$result[0]["Person_BirthDay"]}";
		$text = "В качестве родителя {$parent} указана у пациентов:<br>{$children}Отменить добавление пациента {$data["Person_SurName"]} {$data["Person_FirName"]} {$data["Person_SecName"]}, д/р: {$data["Person_BirthDay"]}?";
		return ["warning" => $text];
	}

	/**
	 * @param Person_model $callObject
	 * @param $Person_id
	 * @param $PersonEvnClass_id
	 * @param $PersonEvnClass_begDate
	 * @throws Exception
	 */
	public static function checkPeriodicBegDate(Person_model $callObject, $Person_id, $PersonEvnClass_id, $PersonEvnClass_begDate)
	{
		$sql = "
			select count(pe.PersonEvn_id) as \"cnt\"
			from
				v_PersonEvn pe 
				inner join PersonEvnClass pec on pe.Person_id = :Person_id
					and pe.PersonEvnClass_id = :PersonEvnClass_id
					and pe.PersonEvnClass_id = pec.personevnclass_id
					and (pe.PersonEvn_insDT = pec.PersonEvnClass_begDT or pec.PersonEvnClass_begDT is null or pec.PersonEvnClass_begDT = :PersonEvnClass_begDate)
		";
		$sqlParams = [
			"Person_id" => $Person_id,
			"PersonEvnClass_id" => $PersonEvnClass_id,
			"PersonEvnClass_begDate" => $PersonEvnClass_begDate
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (is_object($result)) {
			throw new Exception("Непонятная ошибка при сохранении данных формы.");
		}
		$result = $result->result_array();
		if ($result[0]["cnt"]) {
			foreach ($result as $row) {
				if (!empty($row["ErrMsg"])) {
					$err = addslashes($row["ErrMsg"]);
					throw new Exception("Произошла ошибка при сохранении данных формы. <p style=\"color: red\">{$err}</p>");
				}
			}
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return string
	 */
	public static function checkDocument(Person_model $callObject, $data)
	{
		$error = "";
		if (!empty($data["DocumentType_id"])) {
			$query = "
				select 
					DocumentType_MaskSer as \"DocumentType_MaskSer\",
					DocumentType_MaskNum as \"DocumentType_MaskNum\"
				from v_DocumentType
				where DocumentType_id = :DocumentType_id
			    limit 1
			";
			$queryParams = ["DocumentType_id" => $data["DocumentType_id"]];
			$resp_dt = $callObject->queryResult($query, $queryParams);
			if (!empty($resp_dt[0]["DocumentType_MaskSer"])) {
				if (!preg_match("/{$resp_dt[0]["DocumentType_MaskSer"]}/ui", $data["Document_Ser"])) {
					$error .= "серия документа не удовлетворяет маске: {$resp_dt[0]["DocumentType_MaskSer"]}";
				}
			}
			if (!empty($resp_dt[0]["DocumentType_MaskNum"])) {
				if (!preg_match("/{$resp_dt[0]["DocumentType_MaskNum"]}/ui", $data["Document_Num"])) {
					if (!empty($error)) {
						$error .= ", ";
					}
					$error .= "номер документа не удовлетворяет маске: {$resp_dt[0]["DocumentType_MaskNum"]}";
				}
			}
		}
		return $error;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkPesonEdNumOnDate(Person_model $callObject, $data)
	{
		$sql = "
			select
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from v_Person_all 
			where PersonEvnClass_id = 16
			  and Person_id = :Person_id
			  and PersonEvn_insDT = :begdate
			order by
				PersonEvn_insDT desc,
				PersonEvn_TimeStamp desc
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return true;
		}
		$result = $result->result_array();
		return (count($result) == 1) ? false : true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @param bool $attr
	 * @return array|bool|mixed|null
	 * @throws Exception
	 */
	public static function checkPolisIntersection(Person_model $callObject, $data, $attr = false)
	{
		if (getRegionNick() == "ufa") {
			return true;
		}
		$isAstra = (isset($data["session"]["region"]) && $data["session"]["region"]["nick"] == "astra");
		$isKareliya = (isset($data["session"]["region"]) && $data["session"]["region"]["nick"] == "kareliya");
		$isEkb = (isset($data["session"]["region"]) && $data["session"]["region"]["nick"] == "ekb");
		$isBuryatiya = (isset($data["session"]["region"]) && $data["session"]["region"]["nick"] == "buryatiya");
		$isKrym = (isset($data["session"]["region"]) && $data["session"]["region"]["nick"] == "krym");
		$isPskov = (isset($data["session"]["region"]) && $data["session"]["region"]["nick"] == "pskov");
		$Polis_begDate = empty($data["Polis_begDate"]) ? null : date("Y-m-d", strtotime($data["Polis_begDate"]));
		$Polis_endDate = empty($data["Polis_endDate"]) ? null : date("Y-m-d", strtotime($data["Polis_endDate"]));
		$Polis_closeDate = empty($data["Polis_begDate"]) ? null : date("Y-m-d", strtotime($data["Polis_begDate"] . "-1 days"));
		$Polis_openDate = empty($data["Polis_endDate"]) ? null : date("Y-m-d", strtotime($data["Polis_endDate"] . "+1 days"));
		$OrgSMO_id = empty($data["OrgSMO_id"]) ? null : $data["OrgSMO_id"];
		$Polis_Num = empty($data["Polis_Num"]) ? null : $data["Polis_Num"];
		$Federal_Num = empty($data["Federal_Num"]) ? null : $data["Federal_Num"];
		$PersonEvn_id = empty($data["PersonEvn_id"]) ? null : $data["PersonEvn_id"];
		$Polis_id = empty($data["Polis_id"]) ? null : $data["Polis_id"];
		$isIdent = ((isset($data["PersonIdentState_id"]) && $data["PersonIdentState_id"] != 0) || (isset($data["Person_IsInErz"]) && $data["Person_IsInErz"] == 2));
		if (!empty($PersonEvn_id)) {
			// PersonEvn_id - редактируемая периодика..
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				select
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\",
					Polis_id as \"Polis_id\"
				from v_Person_all
				where PersonEvnClass_id = 8
				  and PersonEvn_insDT <= (select PersonEvn_insDT from v_PersonEvn  where PersonEvn_id = :PersonEvn_id limit 1)
				  and Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
				limit 1
			";
			$queryParams = [
				"PersonEvn_id" => $PersonEvn_id,
				"Person_id" => $data["Person_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $queryParams);
			$result = $result->result_array();
			$PersonEvn_id = (count($result) > 0) ? $result[0]["PersonEvn_id"] : null;
		}
		// запрос проверяющй пересечения периодов.
		$queryParams = [
			"Person_id" => $data["Person_id"],
			"PersonEvn_id" => $PersonEvn_id,
			"Polis_begDate" => $Polis_begDate,
			"Polis_endDate" => $Polis_endDate,
			"Polis_closeDate" => $Polis_closeDate,
			"Polis_openDate" => $Polis_openDate,
			"Polis_id" => $Polis_id,
		];
		$query = "
			select
				pa.Server_id as \"Server_id\",
				pa.PersonEvn_id as \"PersonEvn_id\",
				pol.Polis_id as \"Polis_id\",
				pol.PolisType_id as \"PolisType_id\",
				to_char(pol.Polis_begDate, '{$callObject->dateTimeForm104}') as \"Polis_begDate\",
				to_char(pol.Polis_endDate, '{$callObject->dateTimeForm104}') as \"Polis_endDate\",
				pol.Polis_Ser as \"Polis_Ser\",
				case when pol.PolisType_id = 4 then pa.Person_EdNum else pol.Polis_Num end as \"Polis_Num\",
				pol.OrgSMO_id as \"OrgSMO_id\",
				EdNum.PersonEvn_id as \"EdNumPersonEvn_id\",
				EdNum.Server_id as \"EdNumServer_id\"
			from
				v_Person_all pa
				inner join v_Polis pol on pa.Polis_id = pol.Polis_id
				left join lateral (
					select
						pped.PersonPolisEdNum_id as PersonEvn_id,
						pped.Server_id
					from v_PersonPolisEdNum pped
					where pped.Person_id = pa.Person_id
					  and pped.PersonPolisEdNum_EdNum = pa.Person_EdNum
					  and pped.PersonPolisEdNum_insDate <= pa.PersonEvn_insDT
					order by pped.PersonPolisEdNum_insDate desc
                    limit 1
				) as EdNum ON true
			where pa.Person_id = :Person_id
			  and pa.PersonEvnClass_id = 8
			  and pol.Polis_id <> coalesce(:Polis_id::bigint, 0)
			  and (pol.Polis_begDate::date < :Polis_endDate or :Polis_endDate is null)
			  and (pol.Polis_endDate::date > :Polis_begDate or pol.Polis_endDate is null)
		";
		if (!$attr) {
			$query .= "  and (pa.PersonEvn_id != :PersonEvn_id or :PersonEvn_id is null)";
		}
		$response = $callObject->queryResult($query, $queryParams);
		if (!is_array($response)) {
			return false;
		}
		if (count($response) == 0) {
			return true;
		}
		if ($isIdent) {
			//С идентификацией
			if (count($response) > 0 && ($isEkb || $isBuryatiya || $isPskov || $isKrym)) {
				$listForClose = [];//Изменить дату закрытие
				$listForUpdate = [];//Изменить дату начала
				$listForDelete = [];//Удалить полис
				$updatePolis = null;//Обновить полис пришедшеми данными
				//Разбираем масив с пересечениями
				foreach ($response as $polis) {
					if (strtotime($polis["Polis_begDate"]) == strtotime($Polis_begDate) && $polis["OrgSMO_id"] == $OrgSMO_id && ($polis["Polis_Num"] == $Polis_Num || $polis["Polis_Num"] == $Federal_Num)) {
						$updatePolis = $polis;
						continue;
					}
					if (empty($Polis_endDate)) {
						if (strtotime($polis["Polis_begDate"]) >= strtotime($Polis_begDate)) {
							$listForDelete[] = $polis;
						} else {
							$listForClose[] = $polis;
						}
					} else {
						if (strtotime($polis["Polis_begDate"]) >= strtotime($Polis_begDate) && strtotime($polis["Polis_begDate"]) <= strtotime($Polis_endDate) && strtotime($polis["Polis_endDate"]) >= strtotime($Polis_begDate) && strtotime($polis["Polis_endDate"]) <= strtotime($Polis_endDate)) {
							//Полностью попадет в период действия - удалить
							$listForDelete[] = $polis;
						} else if (strtotime($polis["Polis_begDate"]) >= strtotime($Polis_begDate) && strtotime($polis["Polis_begDate"]) <= strtotime($Polis_endDate) && (empty($polis["Polis_endDate"]) || strtotime($polis["Polis_endDate"]) > strtotime($Polis_endDate))) {
							//Дата начала попадет в период действия - изменить дату начала
							$listForUpdate[] = $polis;
						} else {
							//Закрыть
							$listForClose[] = $polis;
						}
					}
				}
				foreach ($listForUpdate as $polis) {
					$sql = "
						update Polis
						set Polis_begDate = :Polis_openDate
						where Polis_id = :Polis_id
					";
					$queryParams["Polis_id"] = $polis["Polis_id"];
					$callObject->db->query($sql, $queryParams);
				}
				foreach ($listForClose as $polis) {
					$sql = "
						update Polis
						set Polis_endDate = :Polis_closeDate
						where Polis_id = :Polis_id
					";
					$queryParams["Polis_id"] = $polis["Polis_id"];
					$callObject->db->query($sql, $queryParams);
				}
				$minDate = null;
				$maxDate = null;
				$deletedPersonEvnList = [];
				foreach ($listForDelete as $polis) {
					if (getRegionNick() == "buryatiya") {
						// необходимо проверять связана ли удаляемая периодика со случаями лечения, которые входят в реестры
						// в бурятии нет реестровой БД, да и хранимка ругается на форин по основной БД, поэтому на реестровую специально не перключаемся.
						$query = "
							select
								to_char(MIN(Evn_disDate), '{$callObject->dateTimeForm120}') as \"Evn_minDate\",
								to_char(MAX(Evn_disDate), '{$callObject->dateTimeForm120}') as \"Evn_maxDate\"
							from r3.RegistryData 
							where PersonEvn_id = :PersonEvn_id
						";
						$queryParams = ["PersonEvn_id" => $polis["PersonEvn_id"]];
						$resp = $callObject->queryResult($query, $queryParams);
						if (!empty($resp[0]["Evn_minDate"])) {
							// Если периодика используется, то
							// Периодика не удаляется, а редактируется
							// o Дата начала = дата окончания случая, который входит в реестр (если таких случаев больше одного, то используется с наименьшей датой (самый ранний случай).
							// o Дата окончания = дата окончания случая, который входит в реестр (если таких случаев больше одного, то используется с наибольшей датой (последний случай).
							$sql = "
								update Polis
								set Polis_endDate = :Polis_endDate,
								    Polis_begDate = :Polis_begDate
								where Polis_id = :Polis_id
							";
							$queryParams = [
								"Polis_begDate" => $resp[0]["Evn_minDate"],
								"Polis_endDate" => $resp[0]["Evn_maxDate"],
								"Polis_id" => $polis["Polis_id"]
							];
							$callObject->db->query($sql, $queryParams);
							$sql = "
								update PersonEvn
								set PersonEvn_begDT = :Polis_begDate,
								    PersonEvn_insDT = :Polis_begDate
								where PersonEvn_id = :PersonEvn_id
							";
							$queryParams = [
								"Polis_begDate" => $resp[0]["Evn_minDate"],
								"PersonEvn_id" => $polis["PersonEvn_id"]
							];
							$callObject->db->query($sql, $queryParams);
							if (empty($minDate) || $resp[0]["Evn_minDate"] < $minDate) {
								$minDate = $resp[0]["Evn_minDate"];
							}
							if (empty($maxDate) || $resp[0]["Evn_maxDate"] > $maxDate) {
								$maxDate = $resp[0]["Evn_maxDate"];
							}
							continue;
						}
					}
					$deletedPersonEvnList[] = $polis["PersonEvn_id"];
					$queryParams = [
						"Person_id" => $data["Person_id"],
						"PersonEvn_id" => $polis["PersonEvn_id"],
						"PersonEvnClass_id" => 8,
						"pmUser_id" => $data["pmUser_id"],
						"Server_id" => $polis["Server_id"]
					];
					$callObject->deletePersonEvnAttribute($queryParams);
					if (!empty($polis["EdNumPersonEvn_id"])) {
						$query = "
							select count(*) as \"cnt\"
							from v_Person_all 
							where Person_id = :Person_id
							  and Person_EdNum = :Polis_Num
						";
						$queryParams = [
							"Person_id" => $data["Person_id"],
							"Polis_Num" => $polis["Polis_Num"]
						];
						$count = $callObject->getFirstResultFromQuery($query, $queryParams);
						if ($count === 0) {
							$queryParams = [
								"Person_id" => $data["Person_id"],
								"PersonEvn_id" => $polis["EdNumPersonEvn_id"],
								"PersonEvnClass_id" => 16,
								"pmUser_id" => $data["pmUser_id"],
								"Server_id" => $polis["EdNumServer_id"]
							];
							$callObject->deletePersonEvnAttribute($queryParams);
						}
					}
				}
				if (is_array($updatePolis)) {
					return $updatePolis;
				} else if (getRegionNick() == "buryatiya" && !empty($minDate)) {
					// возвращаем даты для которых надо создать полисы (2 шт.)
					return ["deletedPersonEvnList" => $deletedPersonEvnList, "minDate" => $minDate, "maxDate" => $maxDate];
				} else {
					return ["deletedPersonEvnList" => $deletedPersonEvnList];
				}
			} else if (count($response) == 1) {
				// если одно пересечение и сохранение после идентификации, то проставляем дату закрытия предыдущему полису.
				// если у пересекающегося полиса пустая дата конца и есть дата начала проставляем дату конца.
				if ($isAstra) {
					if ($response[0]["Server_id"] !== 0) {
						if (!empty($response[0]["Polis_begDate"])) {
							if (strtotime($response[0]["Polis_begDate"]) >= strtotime($Polis_begDate)) {
								return $response[0];
							}
							$sql = "
								update Polis
								set Polis_endDate = :Polis_closeDate
								where Polis_id = :Polis_id
							";
							$queryParams["Polis_id"] = $response[0]["Polis_id"];
							$callObject->db->query($sql, $queryParams);
							return true;
						} else {
							$sql = "
								update Polis
								set Polis_endDate = :Polis_closeDate,
								    Polis_begDate = :Polis_begDate
								where Polis_id = :Polis_id
							";
							$queryParams["Polis_begDate"] = "2000-01-02";
							$queryParams["Polis_id"] = $response[0]["Polis_id"];
							$callObject->db->query($sql, $queryParams);
							return true;
						}
					}
				} else if ($isKareliya) {
					if (strtotime($response[0]["Polis_begDate"]) >= strtotime($Polis_begDate) && !empty($response[0]["Polis_endDate"]) && strtotime($response[0]["Polis_endDate"]) <= strtotime($Polis_endDate)) {
						return $response[0];
					} elseif (strtotime($response[0]["Polis_begDate"]) >= strtotime($Polis_begDate)) {
						$sql = "
							update Polis
							set Polis_begDate = :Polis_openDate
							where Polis_id = :Polis_id
						";
						$queryParams["Polis_id"] = $response[0]["Polis_id"];
						$callObject->db->query($sql, $queryParams);
						return true;
					} else {
						$sql = "
							update Polis
							set Polis_endDate = :Polis_closeDate
							where Polis_id = :Polis_id
						";
						if (strtotime($Polis_closeDate) < strtotime($response[0]["Polis_begDate"])) {
							$queryParams["Polis_closeDate"] = date("Y-m-d", strtotime($response[0]["Polis_begDate"]));
						}
						$queryParams["Polis_id"] = $response[0]["Polis_id"];
						$callObject->db->query($sql, $queryParams);
						return true;
					}
				} else if (!empty($response[0]["Polis_begDate"]) && (empty($response[0]["Polis_endDate"]) || $response[0]["PolisType_id"] == 3)) {
					$sql = "
						update Polis
						set Polis_endDate = :Polis_closeDate
						where Polis_id = :Polis_id
					";
					if (strtotime($Polis_closeDate) < strtotime($response[0]["Polis_begDate"])) {
						if ($isKrym) {
							$ts = strtotime($response[0]["Polis_begDate"]);
							$queryParams["Polis_closeDate"] = date_create("@$ts")->modify("-1 day")->format("Y-m-d");
						} else {
							$queryParams["Polis_closeDate"] = date("Y-m-d", strtotime($response[0]["Polis_begDate"]));
						}
					}
					$queryParams["Polis_id"] = $response[0]["Polis_id"];
					$callObject->db->query($sql, $queryParams);
					return true;
				}
			}
		} else {
			//Без идентификации
			if (count($response) == 1) {
				$polis = $response[0];
				if ($isBuryatiya || $isKrym) {
					if ($polis["PolisType_id"] == 3 && strtotime($polis["Polis_begDate"]) < strtotime($Polis_begDate)) {
						$sql = "
							update Polis
							set Polis_endDate = :Polis_closeDate
							where Polis_id = :Polis_id
						";
						$queryParams["Polis_id"] = $polis["Polis_id"];
						$callObject->db->query($sql, $queryParams);
						return true;
					}
				}
				if (empty($polis["Polis_endDate"]) && strtotime($polis["Polis_begDate"]) < strtotime($Polis_begDate)) {
					$sql = "
						update Polis
						set Polis_endDate = :Polis_closeDate
						where Polis_id = :Polis_id
					";
					$queryParams["Polis_id"] = $polis["Polis_id"];
					$callObject->db->query($sql, $queryParams);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkOMSSprTerrDate(Person_model $callObject, $data)
	{
		if (empty($data["OMSSprTerr_id"])) {
			return true;
		}
		$queryParams = [
			"OMSSprTerr_id" => $data["OMSSprTerr_id"],
			"Polis_begDate" => empty($data["Polis_begDate"]) ? null : date("Y-m-d", strtotime($data["Polis_begDate"])),
			"Polis_endDate" => empty($data["Polis_endDate"]) ? null : date("Y-m-d", strtotime($data["Polis_endDate"]))
		];
		$query = "
			select OMSSprTerr_id as \"OMSSprTerr_id\"
			from v_OMSSprTerr
			where OMSSprTerr_id = :OMSSprTerr_id
			  and (OMSSprTerr_begDate <= :Polis_begDate or OMSSprTerr_begDate is null)
			  and (OMSSprTerr_endDate >= :Polis_begDate or OMSSprTerr_endDate is null)
			  and (OMSSprTerr_begDate <= :Polis_endDate or OMSSprTerr_begDate is null or :Polis_endDate is null)
			  and (OMSSprTerr_endDate >= :Polis_endDate or OMSSprTerr_endDate is null or :Polis_endDate is null)
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result_array();
		if (is_array($response) && count($response) > 0) {
			return true;
		}
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public static function checkFederalNumUnique()
	{
		return true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool|false
	 */
	public static function checkPersonDoubles(Person_model $callObject, $data)
	{
		$queryParams = [
			"Person_SurName" => preg_replace("/[ё]/iu", "Е", trim($data["Person_SurName"])),
			"Person_FirName" => preg_replace("/[ё]/iu", "Е", trim($data["Person_FirName"])),
			"Person_id" => ($data["Person_id"] > 0) ? $data["Person_id"] : null,
			"Person_SecName" => (!empty($queryParams["Person_SecName"])) ? ":Person_SecName, -- отчество" : "null,",
			"Person_BirthDay" => (isset($data["Person_BirthDay"])) ? $data["Person_BirthDay"] : null,
			"Person_IsUnknown" => (isset($data["Person_IsUnknown"])) ? $data["Person_IsUnknown"] : null,
			"Polis_Ser" => (isset($data["Polis_Ser"]) && mb_strlen($data["Polis_Ser"]) > 0) ? trim($data["Polis_Ser"]) : "",
			"Polis_Num" => (isset($data["Polis_Num"]) && mb_strlen($data["Polis_Num"]) > 0) ? trim($data["Polis_Num"]) : "",
			"Federal_Num" => (!in_array($data["session"]["region"]["nick"], ["kz", "ufa"]) && isset($data["Federal_Num"]) && mb_strlen($data["Federal_Num"]) > 0) ? trim($data["Federal_Num"]) : "",
		];
		if (isset($data["OMSSprTerr_id"])) {
			$queryParams["OMSSprTerr_id"] = $data["OMSSprTerr_id"];
		}
		$query = "
			select doubletype_id as \"DoubleType_id\"
			from xp_persondoublescheck(
			    person_id := :Person_id,
			    person_surname := :Person_SurName,
			    person_firname := :Person_FirName,
			    person_secname := :Person_SecName,
			    person_birthday := :Person_BirthDay,
			    polis_ser := :Polis_Ser,
			    polis_num := :Polis_Num,
			    federal_num := :Federal_Num,
			    isshowdouble := null,
			    person_isunknown := :Person_IsUnknown
			);		
		";
		$resp = $callObject->queryResult($query, $queryParams);
		return (is_array($resp)) ? $resp : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool|false
	 */
	public static function checkSnilsDoubles(Person_model $callObject, $data)
	{
		$queryParams = [];
		$queryParams["Person_SNILS"] = (!empty($data["Person_SNILS"])) ? preg_replace("/\-/iu", "", $data["Person_SNILS"]) : "";
		$query = "
			select
				Person_SurName as \"PersonSurName_SurName\", 
				Person_FirName as \"PersonFirName_FirName\", 
				Person_SecName as \"PersonSecName_SecName\", 
				to_char(Person_BirthDay, '{$callObject->dateTimeForm104}') as \"PersonBirthDay\",
				Person_Snils as \"PersonSnils_Snils\",
				Person_id as \"Person_id\"
			from v_PersonState 
			where Person_Snils = :Person_SNILS
			
		";
		if (!empty($data["Person_id"])) {
			$query .= "  and Person_id <> :Person_id";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		$query .= " limit 1";
		$resp = $callObject->queryResult($query, $queryParams);
		return (is_array($resp)) ? $resp : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $params
	 * @return bool
	 */
	public static function check_ENP(Person_model $callObject, $params)
	{
		$and = "";
		if ($params["Check_Type"] == 1) {
			$and .= " and PS.Person_SurName = :Person_SurName";
		} elseif ($params["Check_Type"] == 2) {
			$and .= " and PS.Person_FirName = :Person_FirName";
			$and .= " and PS.Person_SecName = :Person_SecName";
		}
		$query = "
			with cte as (
                select
                    case when :Person_id is not null
                        then Server_pid
                        else null
                    end as Server_pid
				from v_PersonState
				where Person_id = :Person_id
            )
			select 
	            person_id as \"person_id\",
	            server_pid as \"server_pid\",
	            person_isinerz as \"person_isinerz\",
	            server_id as \"server_id\",
	            personevn_id as \"personevn_id\",
	            person_surname as \"person_surname\",
	            person_firname as \"person_firname\",
	            person_secname as \"person_secname\",
	            person_surnamer as \"person_surnamer\",
	            person_firnamer as \"person_firnamer\",
	            person_secnamer as \"person_secnamer\",
	            person_birthday as \"person_birthday\",
	            sex_id as \"sex_id\",
	            person_snils as \"person_snils\",
	            socstatus_id as \"socstatus_id\",
	            polis_id as \"polis_id\",
	            polis_ser as \"polis_ser\",
	            polis_num as \"polis_num\",
	            polistype_id as \"polistype_id\",
	            document_id as \"document_id\",
	            document_ser as \"document_ser\",
	            document_num as \"document_num\",
	            uaddress_id as \"uaddress_id\",
	            paddress_id as \"paddress_id\",
	            job_id as \"job_id\",
	            person_ednum as \"person_ednum\",
	            person_phone as \"person_phone\",
	            person_inn as \"person_inn\",
	            personsoccardnum_id as \"personsoccardnum_id\",
	            person_soccardnum as \"person_soccardnum\",
	            lpu_id as \"lpu_id\",
	            personfamilystatus_id as \"personfamilystatus_id\",
	            familystatus_id as \"familystatus_id\",
	            personfamilystatus_ismarried as \"personfamilystatus_ismarried\",
	            person_isunknown as \"person_isunknown\",
	            person_isdead as \"person_isdead\",
	            personclosecause_id as \"personclosecause_id\",
	            person_deaddt as \"person_deaddt\",
	            person_closedt as \"person_closedt\",
	            personidentstate_id as \"personidentstate_id\",
	            person_identdt as \"person_identdt\",
	            person_isencryphiv as \"person_isencryphiv\",
	            personphone_comment as \"personphone_comment\",
	            personnationalitystatus_id as \"personnationalitystatus_id\",
	            nationalitystatus_id as \"nationalitystatus_id\",
	            klcountry_id as \"klcountry_id\",
	            nationalitystatus_istwonation as \"nationalitystatus_istwonation\",
	            legalstatusvzn_id as \"legalstatusvzn_id\",
	            person_isnotinn as \"person_isnotinn\",
	            pmuser_insid as \"pmuser_insid\",
	            pmuser_updid as \"pmuser_updid\",
	            personstate_insdt as \"personstate_insdt\",
	            personstate_upddt as \"personstate_upddt\"
  			from v_PersonState PS
			where PS.Person_EdNum = :Federal_Num
				and date_part('YEAR', PS.Person_BirthDay) = date_part('YEAR', cast(:Person_BirthDay as date))
				{$and}
				and (PS.Person_id <> :Person_id or :Person_id is null)
				and ((PS.Server_pid <> 0 and (select Server_pid FROM cte) = 0)
					or :Person_id is null
					or (select Server_pid FROM cte) <> 0
				)
            limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return true;
		}
		$result = $result->result_array();
		return (count($result) > 0) ? false : true;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkPersonPolisDoubles(Person_model $callObject, $data)
	{
		$join_str = "";
		if (!(isset($data["Polis_Ser"]) && mb_strlen($data["Polis_Ser"]) > 0 && isset($data["Polis_Num"]) && mb_strlen($data["Polis_Num"]) > 0)) {
			return [["cnt" => 0]];
		}
		$filter = "
				PS.Polis_Ser = :Polis_Ser
			and PS.Polis_Num = :Polis_Num
		";
		$queryParams = [
			"Polis_Ser" => trim($data["Polis_Ser"]),
			"Polis_Num" => trim($data["Polis_Num"])
		];
		if ($data["Person_id"] > 0) {
			$filter .= " and Person_id <> :Person_id";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		if (isset($data["OMSSprTerr_id"])) {
			$filter .= " and Polis.OMSSprTerr_id = :OMSSprTerr_id";
			$join_str .= "left join Polis on Polis.Polis_id = PS.Polis_id";
			$queryParams["OMSSprTerr_id"] = $data["OMSSprTerr_id"];
		}
		$query = "
			select
				count(*) as \"cnt\",
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\"
			from v_PersonState PS 
				{$join_str}
			where {$filter}
			group by PS.Server_id, PS.Person_id
		    limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function CheckSpecifics(Person_model $callObject, $data)
	{
		$records = json_decode($data["Records"], true);
		if (!is_array($records) || count($records) < 2) {
			return false;
		}
		$whereString = implode(",", $records);
		$query = "
			select 
				Server_id as \"Server_id\",
				PersonChild_id as \"PersonChild_id\",
				Person_id as \"Person_id\",
				ResidPlace_id as \"ResidPlace_id\",
				PersonChild_IsManyChild as \"PersonChild_IsManyChild\",
				PersonChild_IsBad as \"PersonChild_IsBad\",
				PersonChild_IsIncomplete as \"PersonChild_IsIncomplete\",
				PersonChild_IsTutor as \"PersonChild_IsTutor\",
				PersonChild_IsMigrant as \"PersonChild_IsMigrant\",
				HealthKind_id as \"HealthKind_id\",
				PersonChild_IsYoungMother as \"PersonChild_IsYoungMother\",
				FeedingType_id as \"FeedingType_id\",
				InvalidKind_id as \"InvalidKind_id\",
				PersonChild_invDate as \"PersonChild_invDate\",
				HealthAbnorm_id as \"HealthAbnorm_id\",
				HealthAbnormVital_id as \"HealthAbnormVital_id\",
				Diag_id as \"Diag_id\",
				PersonChild_IsInvalid as \"PersonChild_IsInvalid\",
				PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				PersonChild_insDT as \"PersonChild_insDT\",
				PersonChild_updDT as \"PersonChild_updDT\",
				ChildTermType_id as \"ChildTermType_id\",
				PersonChild_IsAidsMother as \"PersonChild_IsAidsMother\",
				PersonChild_IsBCG as \"PersonChild_IsBCG\",
				PersonChild_BCGSer as \"PersonChild_BCGSer\",
				PersonChild_BCGNum as \"PersonChild_BCGNum\",
				BirthSvid_id as \"BirthSvid_id\",
				PersonChild_CountChild as \"PersonChild_CountChild\",
				ChildPositionType_id as \"ChildPositionType_id\",
				PersonChild_IsRejection as \"PersonChild_IsRejection\",
				BirthSpecStac_id as \"BirthSpecStac_id\",
				EvnPS_id as \"EvnPS_id\",
				PersonChild_IsBreath as \"PersonChild_IsBreath\",
				PersonChild_IsHeart as \"PersonChild_IsHeart\",
				PersonChild_IsPulsation as \"PersonChild_IsPulsation\",
				PersonChild_IsMuscle as \"PersonChild_IsMuscle\"
			from v_PersonChild 
			where Person_id in ({$whereString})
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$arr = [
			"ResidPlace_id",
			"PersonChild_id",
			"PersonChild_IsManyChild",
			"PersonChild_IsBad",
			"PersonChild_IsIncomplete",
			"PersonChild_IsTutor",
			"PersonChild_IsMigrant",
			"HealthKind_id",
			"PersonChild_IsYoungMother",
			"FeedingType_id",
			"PersonChild_CountChild",
			"InvalidKind_id",
			"PersonChild_invDate",
			"HealthAbnorm_id",
			"HealthAbnormVital_id",
			"Diag_id",
			"PersonChild_IsInvalid",
			"PersonSprTerrDop_id"
		];
		$res = $result->result_array();
		$cnt = 0;
		$personArray = [];
		foreach ($res as $item) {
			if (in_array($item["Person_id"], $personArray)) {
				continue;
			}
			foreach ($item as $key => $val) {
				if (in_array($key, $arr) && !empty($val)) {
					$personArray[] = $item["Person_id"];
					$cnt++;
					break;
				}
			}
		}
		if ($cnt > 1) {
			throw new Exception("У нескольких пациентов есть специфика детства. Объединение невозможно!");
		}
		return ["success" => true, "Error_Msg" => null];
	}

	/**
	 * @param Person_model $callObject
	 * @param $Person_id
	 * @param $Person_did
	 * @return bool
	 */
	public static function checkExistPersonDouble(Person_model $callObject, $Person_id, $Person_did)
	{
		$sql = "
			select COUNT(*) as \"cnt\"
			from pd.PersonDoubles PD 
			where (PD.Person_id=:Person_id and PD.Person_did = :Person_did) or
			      (PD.Person_id=:Person_did and PD.Person_did = :Person_id)
		";
		$queryParams = [
			"Person_id" => $Person_id,
			"Person_did" => $Person_did
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return ($result[0]["cnt"] > 0) ? true : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkPersonSnilsDoubles(Person_model $callObject, $data)
	{
		$query = "
			select count(Person_id) as \"cnt\"
			from v_PersonState ps 
			where ps.Person_Snils = :Person_SNILS
			  and (:Person_id is null or ps.Person_id <> :Person_id)
			  and (Person_closeDT is null or Person_closeDT > dbo.tzGetDate());
		";
		return ($callObject->getFirstResultFromQuery($query, $data) < 1);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function checkAnonimCodeUnique(Person_model $callObject, $data)
	{
		$select = ($data["Person_id"]) ? "  and PS.Person_id != {$data["Person_id"]}" : "";
		$query = "
			select PS.Person_SurName as \"Person_SurName\"
			from
				v_Person P 
				inner join v_PersonState PS on PS.Person_id = P.Person_id
			where P.Person_IsAnonym = 2
			  and PS.Person_SurName = :CheckName
			  {$select}
			limit 1
		";
		$queryParams = ["CheckName" => $data["Person_SurName"]];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка поиска федерального реестрого кода МО");
		}
		if ($result->row()) {
			// код уже существует
			$response = $callObject->getPersonAnonymCodeExt($data);
			$response[0]["success"] = false;
			$response[0]["Error_Msg"] = "Обнаружен дубль по коду, сгенерирован новый код, повторите сохранение";
			return $response;
		}
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function checkPersonPhoneStatus(Person_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"MedStaffFact_id" => !empty($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null,
		];
		$query = "
			select
				PP.PersonPhone_id as \"PersonPhone_id\",
				PP.PersonPhone_Phone as \"PersonPhone_Phone\",
				PPH.PersonPhoneHist_insDT::date as \"PersonPhoneHist_Date\",
				coalesce(PPS.PersonPhoneStatus_Code, 1) as \"PersonPhoneStatus_Code\",
				dbo.tzGetDate() as \"today\"
			from
				Person P 
				left join lateral (
					select PP.*
					from v_PersonPhone PP 
					where PP.Person_id = P.Person_id
					order by PP.PersonPhone_insDT desc
                    limit 1
				) as PP ON true
				left join lateral (
					select PPH.*
					from v_PersonPhoneHist PPH 
					where PPH.PersonPhone_id = PP.PersonPhone_id
					order by PPH.PersonPhoneHist_insDT desc
                    limit 1
				) as PPH ON true
				left join v_PersonPhoneStatus PPS  on PPS.PersonPhoneStatus_id = PPH.PersonPhoneStatus_id
			where P.Person_id = :Person_id
			limit 1
		";
		$resp = $callObject->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении статуса подтверждения телефонного номера человека");
		}
		return [[
			"success" => true,
			"PersonPhone_Phone" => $resp["PersonPhone_Phone"],
			"isVerified" => (!empty($resp["PersonPhone_Phone"]) && $resp["PersonPhoneStatus_Code"] != 1),
			"wasVerificationToday" => ($resp["PersonPhoneHist_Date"] == $resp["today"])
		]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool|float|int|string
	 */
	public static function checkToDelPerson(Person_model $callObject, $data)
	{
		if (empty($data["Person_id"])) {
			return false;
		}
		$where[] = "P.Person_id = :Person_id";
		if (!empty($data["CmpCallCard_id"])) {
			$where[] = "C.CmpCallCard_id != :CmpCallCard_id";
		}
		if (!empty($data["Evn_id"])) {
			$where[] = "E.Evn_id != :Evn_id";
		}
		if (!empty($data["TimetableGraf_id"])) {
			$where[] = "TG.TimetableGraf_id != :TimetableGraf_id";
		}
		$whereString = implode(" and ", $where);
		$query = "
			select 
				coalesce(E.Person_id, C.Person_id, TG.Person_id, TS.Person_id, TP.Person_id, HV.Person_id) as \"Person_id\"
			from
				v_Person P 
				left join v_Evn E on P.Person_id = E.Person_id
				left join v_CmpCallCard C on P.Person_id = C.Person_id
				left join v_TimeTableGraf_lite TG on P.Person_id = TG.Person_id
				left join v_TimeTableStac_lite TS on P.Person_id = TS.Person_id
				left join v_TimeTablePar TP on P.Person_id = TP.Person_id
				left join v_HomeVisit HV on P.Person_id = HV.Person_id
			where {$whereString}
			limit 1
		";
		return $callObject->getFirstResultFromQuery($query, $data);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkEvnZNO_last(Person_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"Evn_id" => $data["Evn_id"]
		];
		$if1 = ($data["object"] == "EvnVizitPL") ? "and EVPL.EvnVizitPL_id != COALESCE(:Evn_id::numeric, 0)" : "";
		$if2 = ($data["object"] == "EvnPS" || $data["object"] == "EvnSection") ? "and EPS.EvnPS_id != COALESCE(:Evn_id::numeric, 0)" : "";
		$if3 = ($data["object"] == "EvnDiagPLStom") ? "and STOM.EvnDiagPLStom_pid != COALESCE(:Evn_id::numeric, 0)" : "";
		$if4 = ($data["object"] == "EvnSection") ? " AND ES.EvnSection_pid != COALESCE(:Evn_id::numeric, 0)" : "";
		$sql = "
			with evnz as (
				select
					EVPL.EvnVizitPL_IsZNO as iszno,
					EVPL.Diag_spid,
					to_char(EVPL.EvnVizitPL_setDT, '{$callObject->dateTimeForm120}') as evn_date,
					EVPL.EvnVizitPL_id as evn_id
				from v_EvnVizitPL EVPL
				where EVPL.EvnClass_id = 11 and EVPL.Person_id = :Person_id {$if1}
				union
				select 
					EPS.EvnPS_IsZNO as iszno,
					EPS.Diag_spid,
					to_char(EPS.EvnPS_setDT, '{$callObject->dateTimeForm120}') as evn_date,
					EPS.EvnPS_id as evn_id
				from v_EvnPS EPS
				where EPS.Person_id = :Person_id {$if2}
				union
				select
					STOM.EvnDiagPLStom_IsZNO as iszno,
					STOM.Diag_spid,
					to_char(STOM.EvnDiagPLStom_setDT, '{$callObject->dateTimeForm120}') as evn_date,
					STOM.EvnDiagPLStom_id as evn_id
				from v_EvnDiagPLStom STOM
				where STOM.Person_id = :Person_id {$if3}
				union
				select
					ES.EvnSection_IsZNO as iszno,
					ES.Diag_spid,
					to_char(ES.EvnSection_setDT, '{$callObject->dateTimeForm120}') as evn_date,
					ES.EvnSection_id as evn_id
				from v_EvnSection ES
				where ES.Person_id = :Person_id {$if4}
			)
			select 
			    evnz.iszno as \"iszno\", 
			    evnz.Diag_spid as \"Diag_spid\", 
			    D.Diag_Code as \"Diag_Code\", 
			    D.Diag_Name as \"Diag_Name\"
			from
				evnz 
				left join v_Diag D on D.Diag_id = evnz.Diag_spid
			order by
				evnz.evn_date desc,
				iszno desc,
				evnz.evn_id desc
            limit 1
		";
		$res = $callObject->getFirstRowFromQuery($sql, $params);
		return $res;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function isReceptElectronicStatus(Person_model $callObject, $data)
	{
		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$query = "
			select
				ReceptElectronic_id as \"ReceptElectronic_id\",
				case when ReceptElectronic_endDT is null or ReceptElectronic_endDT > dbo.tzGetDate()
					then 2
					else 1
				end as \"ReceptElectronic_IsAgree\"
			from
				v_ReceptElectronic re
				left join v_Lpu l on l.Lpu_id = re.Lpu_id
			where
				Person_id = :Person_id and re.Lpu_id = :Lpu_id 
			order by ReceptElectronic_id desc
			limit 1
		";

		$response = $callObject->queryResult($query, $params);

		if (empty($response)){
			return array(array('ReceptElectronic_IsAgree' => 0));				//нету информации о согласии на электронный рецепт
		}
		return $response;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkPersonAgeIsLess(Person_model $callObject, $data) {
		return $callObject->queryResult("
			SELECT
			ps.Person_id as \"Person_id\"
			FROM v_PersonState ps
			WHERE Person_id = :Person_id
				AND dbo.Age(Person_BirthDay, dbo.tzGetDate()) < :age
			limit 1
		", $data);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkPersonDeathDate(Person_model $callObject, $data) {
		return $callObject->queryResult("
			select
				Person_id as \"Person_id\"
			from v_PersonState
			where Person_id = :Person_id
				and coalesce(Person_BirthDay, CAST(:DeathDate as timestamp)) <= CAST(:DeathDate as timestamp)
		", $data);
	}
}
