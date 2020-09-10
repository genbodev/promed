<?php


class LpuStructure_model_check
{
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return string
	 */
	public static function checkStaff(LpuStructure_model $callObject, $data)
	{
		if (empty($data["LpuSection_id"])) {
			//При удалении отделения передается не "LpuSection_id", а "id"
			$data["LpuSection_id"] = $_POST["id"];
			//При удалении проверяем, есть ли уже дата закрытия у отделения
			$query_section = "
				select LS.LpuSection_disDate as \"LpuSection_disDate\"
			 	from v_LpuSection LS
				where LS.LpuSection_id = :LpuSection_id
			";
			$querysection_result = $callObject->db->query($query_section, ["LpuSection_id" => $data["LpuSection_id"]]);
			$section_disDate = $querysection_result->result("array");
			//Если у отделения уже стоит дата закрытия, то используем ее в качестве параметра для поиска по штатному расписанию
			$data["LpuSection_disDate"] = $section_disDate[0]["LpuSection_disDate"];
		}
		$params = ["LpuSection_id" => $data["LpuSection_id"]];
		if (empty($data["LpuSection_disDate"])) {
			//Если это поле пустое, то в качестве параметра берем текущую дату
			$filter_date = " or cast(st.EndDate as date) > tzgetdate())";
		} else {
			$filter_date = " or cast(st.EndDate as date) > :LpuSection_disDate)";
			$params["LpuSection_disDate"] = $data["LpuSection_disDate"];
		}
		//Проверяем, есть ли позиции штатного расписания с пустой датой закрытия или с большей, чем дата закрытия (удаления) отделения:
		$query = "
			select
				BeginDate as \"BeginDate\",
				EndDate as \"EndDate\",
				LpuBuilding_id as \"LpuBuilding_id\",
				LpuSection_id as \"LpuSection_id\",
				LpuUnit_id as \"LpuUnit_id\",
				Lpu_id as \"Lpu_id\",
				MedicalCareKind_id as \"MedicalCareKind_id\",
				Post_id as \"Post_id\",
				Rate as \"Rate\",
				id as \"id\",
				Comments as \"Comments\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Staff_insDT as \"Staff_insDT\",
				Staff_updDT as \"Staff_updDT\",
				IsDummyStaff as \"IsDummyStaff\",
				LpuUnitType_id as \"LpuUnitType_id\",
				Staff_FRMOSendDT as \"Staff_FRMOSendDT\"
			from persis.v_Staff st 
			where st.LpuSection_id = :LpuSection_id
			and (st.EndDate is null {$filter_date}
		";
		$query_result = $callObject->db->query($query, $params);
		//Если такие позиции есть, то выводим ошибку о невозможности закрытия/удаления отделения:
		$result = $query_result->result("array");
		if (is_array($result) && count($result) > 0) {
			return "Для удаления/закрытия отделения закройте все строки штатного расписания датой меньше или равной дате окончания работы отделения";
		} else {
			return "";
		}
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $ids
	 * @return string
	 */
	public static function checkLpuSectionHasChildObjects(LpuStructure_model $callObject, $ids)
	{
		$params = [];
		$inid = implode(",", $ids);
		$sql = "
			(select 'MedService' as \"object_name\" from v_MedService where LpuSection_id in ({$inid}) limit 1)
			union all
			(select 'LpuSection' as \"object_name\" from v_LpuSection where LpuSection_pid in ({$inid}) limit 1)
		";
		$result = $callObject->queryResult($sql, $params, true);
		if ($result === false) {
			return "Ошибка запроса к БД (проверка наличия дочерних объектов)";
		}
		if (is_array($result) && count($result) > 0) {
			return "Существуют дочерние объекты в БД, ссылающиеся на " . (count($ids) == 1 ? "указанное отделение" : "указанные отделения") . ".";
		}
		return "";
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $ids
	 * @return string
	 */
	public static function checkLpuSectionLinksExists(LpuStructure_model $callObject, $ids)
	{
		$params = [];
		$inid = implode(",", $ids);
		$query = "
			select coalesce(
			    (select 1 from v_EvnSection t where t.LpuSection_id in ({$inid}) limit 1),
			    (select 1 from v_EvnPS t where t.LpuSection_id in ({$inid}) limit 1),
			    (select 1 from v_EvnPS t where t.LpuSection_eid in ({$inid}) limit 1),
			    (select 1 from v_EvnPS t where t.LpuSection_pid in ({$inid}) limit 1),
			    (select 1 from v_EvnPL t where t.LpuSection_id in ({$inid}) limit 1),
			    (select 1 from v_EvnPL t where t.LpuSection_did in ({$inid}) limit 1),
			    (select 1 from v_EvnPL t where t.LpuSection_oid in ({$inid}) limit 1),
			    (select 1 from v_EvnVizit t where t.LpuSection_id in ({$inid}) limit 1)
			) as \"useLpuSection\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return "Ошибка запроса к БД (проверка ссылок на отделения в документах)";
		}
		$resp = $result->result("array");
		if (is_array($resp) && count($resp) > 0 && !empty($resp[0]["useLpuSection"])) {
			return "Существуют объекты в БД, ссылающиеся на " . (count($ids) == 1 ? "указанное отделение" : "указанные отделения") . ".";
		}
		return "";
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @param $Lpu_id
	 * @param $LpuRegion_id
	 * @param $LpuSection_id
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public static function checkMedStaffRegionDelAvailable(LpuStructure_model $callObject, $data, $Lpu_id, $LpuRegion_id, $LpuSection_id)
	{
		if (!is_array($data) || count($data) == 0) {
			return [['Error_Code' => '996', 'Error_Msg' => 'На участке должен быть хотя бы один врач.']];
		}

		/**
		 * Проверяет отсутствие даты окончания работы, т.е. равенство искусственной максимальной величине
		 *
		 * @param $date
		 * @return bool
		 */
		function isEmptyDate($date)
		{
			return $date === "2038-01-01";
		}

		/**
		 * Возвращает пересечение переданных периодов
		 *
		 * @param $date
		 * @return mixed
		 */
		function dateLineIntersect($date)
		{
			$count = 1;
			$ids = 1;
			$tmp_date = &$date;
			$res_array = [];
			$BEGDATE = "MedStaffRegion_begDate";
			$ENDDATE = "MedStaffRegion_endDate";
			foreach ($date as &$kv) {
				$kv["id"] = $ids;
				$ids += 1;
			}
			unset($kv);
			foreach ($date as $r) {
				foreach ($date as $r2) {
					//если это два разных пересекающихся участка - добавляем элемент в массив пересечений
					if ($r["id"] != $r2["id"] && !empty($r[$BEGDATE]) && !empty($r2[$BEGDATE]) && $r[$BEGDATE] <= $r2[$ENDDATE] && $r[$ENDDATE] >= $r2[$BEGDATE]) {
						if ($r[$BEGDATE] <= $r2[$BEGDATE] && $r[$ENDDATE] <= $r2[$ENDDATE]) {
							array_push($res_array, ["id" => "", $BEGDATE => $r2[$BEGDATE], $ENDDATE => $r[$ENDDATE]]);
						} else if ($r[$BEGDATE] >= $r2[$BEGDATE] && $r[$ENDDATE] >= $r2[$ENDDATE]) {
							array_push($res_array, ["id" => "", $BEGDATE => $r[$BEGDATE], $ENDDATE => $r2[$ENDDATE]]);
						} else if ($r[$BEGDATE] != $r2[$BEGDATE] && $r[$ENDDATE] != $r2[$ENDDATE]) {
							array_push($res_array, ["id" => "", $BEGDATE => $r2[$BEGDATE], $ENDDATE => $r2[$ENDDATE]]);
						}
						$count += 1;
					}
				}
			}
			if ($count > 1) {
				//удаляем дубли и сново ищем пересечения
				$res_array = array_map("unserialize", array_unique(array_map("serialize", $res_array)));
				return dateLineIntersect($res_array);
			} else {
				return $tmp_date;
			}
		}

		$existDelRecs = false;
		$BEGDATE = "MedStaffRegion_begDate";
		$ENDDATE = "MedStaffRegion_endDate";
		$ID = "MedStaffRegion_id";
		//массив с записями на удаление
		$del_array = [];
		//массив с записями на обновление/добавление
		$upd_array = [];
		foreach ($data as &$row) {
			//если период открыт то проставляем максимальную дату для удобства
			if (empty($row[$ENDDATE])) {
				$row[$ENDDATE] = "2038-01-01";
			}
			$row[$BEGDATE] = date("Y-m-d", strtotime($row[$BEGDATE]));
			$row[$ENDDATE] = date("Y-m-d", strtotime($row[$ENDDATE]));
		}
		unset($row);
		foreach ($data as $row) {
			if (!empty($row["status"]) && $row["status"] === 3) {
				$existDelRecs = true;
				array_push($del_array, $row);
			} else {
				array_push($upd_array, $row);
			}
		}
		//Если в изменяемых врачах есть удаляемые - то проверяем возможность их удаления
		if ($existDelRecs) {
			if (!empty($LpuSection_id)) {
				$medStaffFactList = [];
				foreach ($del_array as $drow) {
					if (!empty($drow["MedStaffFact_id"]) && !in_array($drow["MedStaffFact_id"], array_keys($medStaffFactList))) {
						$medStaffFactList[$drow["MedStaffFact_id"]] = 0;
					}
				}
				if (count($medStaffFactList) > 0) {
					$medStaffFactListString = implode(",", array_keys($medStaffFactList));
					$query = "
						select MedStaffFact_id as \"MedStaffFact_id\"
						from v_MedStaffFact 
						where MedStaffFact_id in ({$medStaffFactListString})
							and LpuSection_id = :LpuSection_id
					";
					$queryParams = [
						"LpuSection_id" => $LpuSection_id
					];
					$result = $callObject->db->query($query, $queryParams);
					if (!is_object($result)) {
						return [['Error_Code' => '990', 'Error_Msg' => 'Ошибка при проверке актуальности мест работы.']];
					}
					$resp = $result->result("array");
					if (is_array($resp) && count($resp) > 0) {
						foreach ($resp as $row) {
							$medStaffFactList[$row["MedStaffFact_id"]] = 1;
						}
					}
				}
				foreach ($del_array as $key => $drow) {
					if (!empty($drow["MedStaffFact_id"]) && in_array($drow["MedStaffFact_id"], array_keys($medStaffFactList)) && $medStaffFactList[$drow["MedStaffFact_id"]] === 0) {
						unset($del_array[$key]);
					}
				}
			}
			//Если в удаляемых врачах есть врач с открытым концом периода, а в добавляемых/обновляемых - нет, то выводим ошибку
			//Дублируем проверку на клиенте, здесь эта ошибка всплыть не должна
			foreach ($del_array as $drow) {
				$existEndDate = false;
				if (empty($drow[$ENDDATE])) {
					$existEndDate = true;
					foreach ($upd_array as $urow) {
						if (empty($urow[$ENDDATE])) {
							$existEndDate = false;
							break;
						}
					}
				}
				if ($existEndDate) {
					return [['Error_Code' => '995', 'Error_Msg' => 'Нельзя удалять врача без даты окончания работы, если при этом на участке нет другого врача без даты окончания работы.']];
				}
			}
			//не должно быть прикреплённого населения на участке, в период когда на нём не работало ни одного врача
			//проверяем наличие участков на добавление/обновление, пересекающихся с удаляемым
			foreach ($del_array as $dr) {
				//обновляемые участки, пересекающиеся с удаляемым
				$susp_array = [];
				foreach ($upd_array as $ur) {
					if ((isEmptyDate($dr[$ENDDATE]) && isEmptyDate($ur[$ENDDATE])) ||
						(!isEmptyDate($dr[$ENDDATE]) && isEmptyDate($ur[$ENDDATE]) && $ur[$BEGDATE] <= $dr[$ENDDATE]) ||
						(!isEmptyDate($ur[$ENDDATE]) && isEmptyDate($ur[$ENDDATE]) && $dr[$BEGDATE] <= $ur[$ENDDATE]) ||
						($dr[$BEGDATE] <= $ur[$ENDDATE] && $dr[$ENDDATE] >= $ur[$BEGDATE])
					) {
						array_push($susp_array, $ur);
					}
				}
				if (count($susp_array) > 0) {
					//Для всех участков в $susp_array если даты начала/окончания выходят за границы удаляемого участка - приравниваем их к датам удаляемого участка
					foreach ($susp_array as &$r) {
						if ($r[$BEGDATE] < $dr[$BEGDATE]) {
							$r[$BEGDATE] = $dr[$BEGDATE];
						}
						if ($r[$ENDDATE] > $dr[$ENDDATE]) {
							$r[$ENDDATE] = $dr[$ENDDATE];
						}
					}
					unset($r);
					//удаляем вложенные периоды, т.е. те, которые целиком попадают в другой
					$counter = 0;
					foreach ($susp_array as &$rec) {
						foreach ($susp_array as $r_vloj) {
							//Если период целиком лежит в другом - удаляем его
							if ($rec[$ID] != $r_vloj[$ID] && $r_vloj[$BEGDATE] <= $rec[$BEGDATE] && $r_vloj[$ENDDATE] >= $rec[$ENDDATE]) {
								unset($susp_array[$counter]);
							}
						}
						$counter += 1;
					}
					unset($rec);
					foreach ($susp_array as &$rec) {
						foreach ($susp_array as $r_vloj) {
							//Если период пересекается с другим - корректируем даты так что бы они не пересекались, а соприкасались
							if ($rec[$ID] != $r_vloj[$ID] && $rec[$BEGDATE] <= $r_vloj[$ENDDATE] && $rec[$ENDDATE] >= $r_vloj[$BEGDATE]) {
								if ($rec[$BEGDATE] > $r_vloj[$BEGDATE]) {
									$rec[$BEGDATE] = $r_vloj[$ENDDATE];
								} else {
									$rec[$ENDDATE] = $r_vloj[$BEGDATE];
								}
							}
						}
					}
					unset($rec);
					$empty_dates_array = [];
					//идентификатор, что бы в дальнейшем отличать записи друг от друга
					$empt_id = 1;
					//создаём массив с датами каждого периода, на которые небыло врачей на участке
					foreach ($susp_array as $rec) {
						if ($dr[$BEGDATE] == $rec[$BEGDATE]) {
							array_push($empty_dates_array, ["id" => $empt_id, $BEGDATE => $rec[$ENDDATE], $ENDDATE => $dr[$ENDDATE]]);
						} else if ($dr[$ENDDATE] == $rec[$ENDDATE]) {
							array_push($empty_dates_array, ["id" => $empt_id, $BEGDATE => $dr[$BEGDATE], $ENDDATE => $rec[$BEGDATE]]);
						} else {
							//период целиком лежит в периоде удаляемого, добавляем два участка, получающиеся от их пересечения
							array_push($empty_dates_array, ["id" => $empt_id, $BEGDATE => $dr[$BEGDATE], $ENDDATE => $rec[$BEGDATE]]);
							$empt_id += 1;
							array_push($empty_dates_array, ["id" => $empt_id, $BEGDATE => $rec[$ENDDATE], $ENDDATE => $dr[$ENDDATE]]);
						}
						$empt_id += 1;
					}
					//Получаем массив с периодами, когда на участке не работал ни один врач
					$intersect_empty_dates_array = dateLineIntersect($empty_dates_array);
					//если есть даты, на которые на участке небыло врачей - проверяем есть ли на этот период прикрепления
					if (is_array($intersect_empty_dates_array) && count($intersect_empty_dates_array) > 0) {
						$checkAttach = $callObject->checkAttachOnDates(["dates" => $intersect_empty_dates_array, "Lpu_id" => $Lpu_id, "LpuRegion_id" => $LpuRegion_id]);
						if (is_array($checkAttach)) {
							return $checkAttach;
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public static function checkAttachOnDates(LpuStructure_model $callObject, $data)
	{
		if (empty($data) || !is_array($data) || count($data) == 0 || empty($data["dates"]) || !is_array($data["dates"]) || empty($data["Lpu_id"]) || empty($data["LpuRegion_id"])) {
			return [['Error_Code' => '995', 'Error_Msg' => 'Переданы неправильные параметры для проверки.']];
		}
		$queryParams = [
			"LpuRegion_id" => $data["LpuRegion_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$dateFilter = "";
		foreach ($data["dates"] as $row) {
			if (!empty($dateFilter)) {
				$dateFilter .= " or ";
			}
			$dateFilter .= " (PersonCard_begDate >= '{$row['MedStaffRegion_begDate']}' and (PersonCard_endDate is null or PersonCard_endDate <= '{$row['MedStaffRegion_endDate']}')) ";
		}
		$query = "
			select Person_id as \"Person_id\"
			from v_PersonCard 
			where LpuRegion_id = :LpuRegion_id
			  and Lpu_id = :Lpu_id
			  and ({$dateFilter})
			limit 1
		";
		$resp = $callObject->queryResult($query, $queryParams);
		if (!empty($resp[0]["Person_id"])) {
			return [['Error_Code' => '995', 'Error_Msg' => 'На период работы удаляемого врача найдены прикрепленные к участку пациенты. Удаление невозможно.']];
		}
		return true;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkSaveMedStaffRegion(LpuStructure_model $callObject, $data)
	{
		if ((!isset($data["MedPersonal_id"])) || ($data["MedPersonal_id"] == 0)) {
			return false;
		}
		if ((!isset($data["LpuRegion_id"])) || ($data["LpuRegion_id"] == 0)) {
			return false;
		}
		$params = [
			"MedPersonal_id" => $data["MedPersonal_id"],
			"LpuRegion_id" => $data["LpuRegion_id"]
		];
		if ((isset($data["MedStaffRegion_id"])) && ($data["MedStaffRegion_id"] > 0)) {
			$medstaffregion = "MSR.MedStaffRegion_id != :MedStaffRegion_id";
			$params["MedStaffRegion_id"] = $data["MedStaffRegion_id"];
		} else {
			$medstaffregion = "(1=1)";
		}

		if ((isset($data["session"]["lpu_id"])) && ($data["session"]["lpu_id"] > 0)) {
			$lpu = "MSR.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["session"]["lpu_id"];
		} else {
			$lpu = "(1<>1)";
		}
		$sql = "
			select count(*) as record_count
			from v_MedStaffRegion MSR 
			where MSR.MedPersonal_id = :MedPersonal_id
			  and MSR.LpuRegion_id = :LpuRegion_id
			  and {$medstaffregion}
			  and {$lpu}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkLpuSectionFinans(LpuStructure_model $callObject, $data)
	{
		$filterList = [];
		$queryParams = [];
		if (isset($data["Server_id"]) && $data["Server_id"] > 0) {
			$filterList[] = "Server_id = :Server_id";
			$queryParams["Server_id"] = $data["Server_id"];
		} else if ($data["session"]["server_id"] > 0) {
			$filterList[] = "Server_id = :Server_id";
			$queryParams["Server_id"] = $data["session"]["server_id"];
		}
		if (!empty($data["LpuSectionFinans_id"]) && is_numeric($data["LpuSectionFinans_id"])) {
			$filterList[] = "LpuSectionFinans_id != :LpuSectionFinans_id";
			$queryParams["LpuSectionFinans_id"] = $data["LpuSectionFinans_id"];
		}
		if (!empty($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterList[] = "LpuSection_id = :LpuSection_id";
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["LpuSectionFinans_begDate"])) {
			$filterList[] = "LpuSectionFinans_begDate = :LpuSectionFinans_begDate";
			$queryParams["LpuSectionFinans_begDate"] = $data["LpuSectionFinans_begDate"];
		}
		$whereString = (count($filterList) != 0) ? "where " . implode(" and ", $filterList) : "";
		$query = "
			select LpuSectionFinans_id as \"LpuSectionFinans_id\"
			from LpuSectionFinans 
			{$whereString}
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function checkLpuSectionIsVMP(LpuStructure_model $callObject, $data)
	{
		$query = "
			select 
				LS.LpuSection_IsHTMedicalCare as \"LpuSection_IsHTMedicalCare\",
				LS.LpuSection_Code as \"LpuSection_Code\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_LpuSection LS 
				left join v_Lpu l on l.Lpu_id = ls.Lpu_id
			where LS.LpuSection_id = :LpuSection_id
			limit 1
		";
		$queryParams = [
			"LpuSection_id" => $data["LpuSection_id"]
		];
		$response = $callObject->queryResult($query, $queryParams);
		if (!empty($response[0]) && $response[0]["LpuSection_IsHTMedicalCare"] != 2) {
			throw new Exception("В отделении {$response[0]['Lpu_Nick']} {$response[0]['LpuSection_Code']} {$response[0]['LpuSection_Name']} не предусмотрено выполнение высокотехнологичной помощи");
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkLpuSectionLicence(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$filterArray = [];
		if (!isset($data["LpuSection_id"]) || !is_numeric($data["LpuSection_id"])) {
			return false;
		}
		if (empty($data["LpuSectionLicence_begDate"])) {
			return false;
		}
		if (isset($data["Server_id"]) && $data["Server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['Server_id']}";
		} elseif ($data["session"]["server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['session']['server_id']}";
		}
		if (isset($data["LpuSectionLicence_id"]) && is_numeric($data["LpuSectionLicence_id"])) {
			$filterArray[] = "LpuSectionLicence_id != {$data['LpuSectionLicence_id']}";
		}
		$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		$filterArray[] = "LpuSectionLicence_begDate = '" . ConvertDateFormat(trim($data['LpuSectionLicence_begDate'])) . "'";
		$whereString = implode(" and ", $filterArray);
		$sql = "
			select 1
			from LpuSectionLicence 
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return true;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkLpuSectionPlan(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$filterArray = ["LpuSectionPlan_PlanHosp is null"];
		if (!isset($data["LpuSection_id"]) || !is_numeric($data["LpuSection_id"])) {
			return false;
		}
		if (empty($data["LpuSectionPlan_setDate"])) {
			return false;
		}
		if (isset($data["LpuSectionPlan_id"]) && is_numeric($data["LpuSectionPlan_id"])) {
			$filterArray[] = "LpuSectionPlan_id != {$data['LpuSectionPlan_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		}
		$filterArray[] = "LpuSectionPlan_setDate='" . ConvertDateFormat(trim($data["LpuSectionPlan_setDate"])) . "'";
		$whereString = implode(" and ", $filterArray);
		$sql = "
			select 1
			from LpuSectionPlan 
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return true;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkUslugaSection(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$filterArray = [];
		if (!isset($data["LpuSection_id"]) || !is_numeric($data["LpuSection_id"])) {
			return false;
		}
		if (!isset($data["Usluga_id"]) || !is_numeric($data["Usluga_id"])) {
			return false;
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "LpuSection_id = :LpuSection_id";
		}
		if (isset($data["Usluga_id"]) && is_numeric($data["Usluga_id"])) {
			$filterArray[] = "Usluga_id = :Usluga_id";
		}
		if ((isset($data["UslugaSection_id"])) && (is_numeric($data["UslugaSection_id"]))) {
			$filterArray[] = "UslugaSection_id != :UslugaSection_id";
		}
		if ((isset($data["UslugaSection_Code"])) && (is_numeric($data["UslugaSection_Code"]))) {
			$filterArray[] = "UslugaSection_Code = :UslugaSection_Code";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select count(*) as rec
			from UslugaSection 
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return true;
		}
		$result = $result->result("array");
		if (count($result) == 0) {
			return true;
		}
		return ($result[0]["rec"] == 0);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkLpuSectionTariffMes(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$filterArray = [];
		if (!isset($data["LpuSection_id"]) || !is_numeric($data["LpuSection_id"])) {
			return false;
		}
		if (empty($data["LpuSectionTariffMes_setDate"])) {
			return false;
		}
		if (empty($data["Mes_id"])) {
			return false;
		}
		if (isset($data["LpuSectionTariffMes_id"]) && is_numeric($data["LpuSectionTariffMes_id"])) {
			$filterArray[] = "LpuSectionTariffMes_id != {$data['LpuSectionTariffMes_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		}
		$filterArray[] = "LpuSectionTariffMes_setDate = '" . ConvertDateFormat(trim($data["LpuSectionTariffMes_setDate"])) . "'";
		$filterArray[] = "Mes_id = '" . ConvertDateFormat(trim($data["Mes_id"])) . "'";
		$whereString = implode(" and ", $filterArray);
		$sql = "
			select 1
			from LpuSectionTariffMes 
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return true;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public static function checkLpuSectionShift(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$filterArray = [];
		if (!isset($data["LpuSection_id"]) || !is_numeric($data["LpuSection_id"])) {
			throw new Exception("Не указано отделение");
		}
		if (empty($data["LpuSectionShift_setDate"])) {
			throw new Exception("Не указана дата начала смены коек");
		}
		if ((isset($data["Server_id"])) && ($data["Server_id"] > 0)) {
			$filterArray[] = "Server_id = {$data['Server_id']}";
		} elseif ($data["session"]["server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['session']['server_id']}";
		}
		if (isset($data["LpuSectionShift_id"]) && is_numeric($data["LpuSectionShift_id"])) {
			$filterArray[] = "LpuSectionShift_id != {$data['LpuSectionShift_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		}
		$filterArray[] = "LpuSectionShift_setDate = '" . ConvertDateFormat(trim($data["LpuSectionShift_setDate"])) . "'";
		$whereString = implode(" and ", $filterArray);
		$sql = "
			select 1
			from LpuSectionShift as LSS 
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД");
		}
		$result = $result->result('array');

		if (!empty($result[0]) && count($result[0]) > 0) {
			throw new Exception("Существуют ранее введенное количество смен по этому отделению с той же датой начала");
		}
		return false;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkLpuSectionTariff(LpuStructure_model $callObject, $data)
	{
		$callObject->load->helper("Date");
		$filterArray = [];
		if (empty($data["LpuSectionTariff_setDate"])) {
			return false;
		}
		if (!isset($data["LpuSection_id"]) || !is_numeric($data["LpuSection_id"])) {
			return false;
		}
		if ((isset($data["Server_id"])) && ($data["Server_id"] > 0)) {
			$filterArray[] = "Server_id = {$data['Server_id']}";
		} elseif ($data["session"]["server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['session']['server_id']}";
		}
		if (isset($data["LpuSectionTariff_id"]) && is_numeric($data["LpuSectionTariff_id"])) {
			$filterArray[] = "LpuSectionTariff_id != {$data['LpuSectionTariff_id']}";
		}
		if (isset($data["TariffClass_id"]) && is_numeric($data["TariffClass_id"])) {
			$filterArray[] .= "TariffClass_id = {$data['TariffClass_id']}";
		}
		$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		$filterArray[] = "LpuSectionTariff_setDate = '" . ConvertDateFormat(trim($data["LpuSectionTariff_setDate"])) . "'";
		$whereString = implode(" and ", $filterArray);
		$sql = "
			select 1
			from LpuSectionTariff 
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return true;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @param $Lpu_id
	 * @return array
	 */
	public static function checkMainMedPersonal(LpuStructure_model $callObject, $data, $Lpu_id)
	{
		$result = ["count" => -1, "fio" => ""];
		if (!is_array($data) || count($data) == 0 || empty($Lpu_id)) {
			return $result;
		}
		$MedPersonal_id = 0;
		$MedStaffRegion_id = 0;
		$MedStaffRegionToDelete = [];
		for ($j = 0; $j < count($data); $j++) {
			if (!empty($data[$j]["status"]) && $data[$j]["status"] == "3") {
				if (!empty($data[$j]["MedStaffRegion_id"]) && $data[$j]["MedStaffRegion_id"] > 0) {
					$MedStaffRegionToDelete[] = $data[$j]["MedStaffRegion_id"];
				}
			} else if (!empty($data[$j]["MedStaffRegion_isMain"]) && $data[$j]["MedStaffRegion_isMain"] == 2 && !empty($data[$j]["MedPersonal_id"])) {
				$MedPersonal_id = $data[$j]["MedPersonal_id"];
				$MedStaffRegion_id = $data[$j]["MedStaffRegion_id"];
			}
		}
		if (!empty($MedPersonal_id)) {
			$MedStaffRegionToDeleteString = implode(",", $MedStaffRegionToDelete);
			$whereString = (count($MedStaffRegionToDelete) > 0) ? "and MedStaffRegion_id not in ({$MedStaffRegionToDeleteString})" : "";
			$query = "
				select mp.Person_Fio as \"Person_Fio\"
				from
					v_MedStaffRegion msr 
					LEFT JOIN LATERAL (
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = :MedPersonal_id
						limit 1
					) as mp on true
				where MedStaffRegion_id != coalesce(:MedStaffRegion_id, 0)
				  and Lpu_id = :Lpu_id
				  and MedPersonal_id = :MedPersonal_id
				  and coalesce(MedStaffRegion_isMain, 1) = 2
				  {$whereString}
			";
			$queryParams = [
				"Lpu_id" => $Lpu_id,
				"MedStaffRegion_id" => $MedStaffRegion_id,
				"MedPersonal_id" => $MedPersonal_id
			];
			/**@var CI_DB_result $res */
			$res = $callObject->db->query($query, $queryParams);
			if (is_object($res)) {
				$response = $res->result("array");
				if (is_array($response)) {
					$result["count"] = count($response);
					if (count($response) > 0) {
						$result["fio"] = $response[0]["Person_Fio"];
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $method
	 * @param $data
	 * @return string
	 */
	public static function _checkOpenChildStruct(LpuStructure_model $callObject, $method, $data)
	{
		$query = "";
		if ($method == "saveLpuSection") {
			//При закрытии отделений проверять подотделения, службы и склады.
			//При закрытии подотделений проверять есть ли не закрытые склады.
			$data["endDate"] = $data["LpuSection_disDate"];
			$query = "
				(select LpuSection_id as \"hasChild\" from v_LpuSection where LpuSection_pid = :LpuSection_id and (LpuSection_disDate is null or LpuSection_disDate > :endDate) limit 1)
				union all
				(
				    select S.Storage_id as \"hasChild\"
				    from v_Storage S
				         inner join StorageStructLevel SSL on S.Storage_id = SSL.Storage_id
				    where SSL.LpuSection_id = :LpuSection_id
				      and (S.Storage_endDate is null or S.Storage_endDate > :endDate)
				    limit 1
				)
				union all
				(select MedService_id as \"hasChild\" from v_MedService where LpuSection_id = :LpuSection_id and (MedService_endDT is null or MedService_endDT > :endDate) limit 1)
			";
		} elseif ($method == "saveLpuUnit") {
			//При закрытии групп отделений проверять отделения, службы и склады.
			$data["endDate"] = $data["LpuUnit_endDate"];
			$query = "
				(
                select 
				    LpuSection_id as \"hasChild\" 
                from 
				    v_LpuSection 
				where 
				    LpuUnit_id = :LpuUnit_id 
				and 
				    (LpuSection_disDate is null or LpuSection_disDate > :endDate) 
                limit 1
                )
				union all
				(
				    select 
				        S.Storage_id as \"hasChild\"
				    from 
				        v_Storage S
				        inner join StorageStructLevel SSL on S.Storage_id = SSL.Storage_id
				    where 
				        SSL.LpuUnit_id = :LpuUnit_id
                    and 
				        (S.Storage_endDate is null or S.Storage_endDate > :endDate)
				    limit 1
				)
				union all
				(
				    select 
				        MedService_id as \"hasChild\"
				    from 
				        v_MedService 
				    where 
				        LpuUnit_id = :LpuUnit_id
				    and 
				        (MedService_endDT is null or MedService_endDT > :endDate) 
				    limit 1
				)
			";
		} elseif ($method == "saveLpuBuilding") {
			//При закрытии подразделения проверять группы отделений, службы и склады.
			$data["endDate"] = $data["LpuBuilding_endDate"];
			$query = "
				(
				    select 
				        LpuUnit_id as \"hasChild\" 
				    from 
				        v_LpuUnit 
				    where 
				        LpuBuilding_id = :LpuBuilding_id 
				    and 
				        (LpuUnit_endDate is null or LpuUnit_endDate > :endDate) 
				    limit 1
				)
				union all
				(
				    select S.Storage_id as \"hasChild\"
				    from 
				        v_Storage S
				        inner join StorageStructLevel SSL on S.Storage_id = SSL.Storage_id
				    where 
				        SSL.LpuBuilding_id = :LpuBuilding_id
				    and 
				        (S.Storage_endDate is null or S.Storage_endDate > :endDate)
				    limit 1
				)
				union all
				(
				    select 
				        MedService_id as \"hasChild\" 
				    from 
				        v_MedService
				    where 
				        LpuBuilding_id = :LpuBuilding_id 
				    and 
				        (MedService_endDT is null or MedService_endDT > :endDate) 
				    limit 1
				)
			";
		}
		if (empty($query) || empty($data["endDate"])) {
			return "";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return "Ошибка запроса к БД (проверка ссылок на отделения в документах).";
		}
		$resp = $result->result("array");
		if (is_array($resp) && count($resp) > 0 && !empty($resp[0]["hasChild"])) {
			return "Закрытие невозможно, есть незакрытые подчинённые элементы структуры.";
		} else {
			return "";
		}
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function _checkLpuUnitType(LpuStructure_model $callObject, $data)
	{
		$params = [
			"LpuUnit_id" => $data["LpuUnit_id"],
			"pmUser_id" => $data["pmUser_id"],
			"Server_id" => $data["Server_id"]
		];
		$query = "
			SELECT 
				LU.LpuUnitType_id as \"LpuUnitType_id\", 
				StaffCount as \"StaffCount\"
			FROM
			    v_LpuUnit LU
				LEFT JOIN LATERAL (
					select COUNT(*) as StaffCount
					from persis.v_Staff ST 
					where ST.LpuUnit_id = LU.LpuUnit_id
				) as StaffTotal on true
			WHERE LU.LpuUnit_id = :LpuUnit_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return string
	 */
	public static function _checkLpuSectionAttributeSignValue(LpuStructure_model $callObject, $data)
	{
		$callObject->load->model("Attribute_model");
		//Проверка соответсвия дат атрибутов и отделения
		$query = "
			select to_char(ASV.AttributeSignValue_begDate, 'YYYY-MM-DD') as \"AttributeSignValue_begDate\"
			from
				v_AttributeSignValue ASV 
				inner join v_AttributeSign AST on AST.AttributeSign_id = ASV.AttributeSign_id
			where AST.AttributeSign_TableName = 'dbo.LpuSection'
			  and ASV.AttributeSignValue_TablePKey = :LpuSection_id
		";
		$queryParams = [
			"LpuSection_id" => $data["LpuSection_id"]
		];
		$resp = $callObject->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			return "Ошибка при запросе сохраненных признаков атрибутов";
		}
		$LpuSection_setDate = DateTime::createFromFormat("Y-m-d H:i", $data["LpuSection_setDate"] . " 00:00");
		$LpuSection_disDate = !empty($data["LpuSection_disDate"]) ? DateTime::createFromFormat("Y-m-d H:i", $data["LpuSection_disDate"] . " 00:00") : null;
		foreach ($resp as $item) {
			$AttributeSignValue_begDate = DateTime::createFromFormat("Y-m-d H:i", $item["AttributeSignValue_begDate"] . " 00:00");
			if ($AttributeSignValue_begDate < $LpuSection_setDate) {
				return "Начало действия значений атрибутов не может быть раньше даты создания отделения";
			}
			if (!empty($LpuSection_disDate) && !empty($item["AttributeSignValue_endDate"])) {
				$AttributeSignValue_endDate = DateTime::createFromFormat("Y-m-d H:i", $item["AttributeSignValue_endDate"] . " 00:00");
				if ($AttributeSignValue_endDate > $LpuSection_disDate) {
					return "Окончание действия значений атрибутов не может быть позже даты закрытия отделения";
				}
			}
		}
		//Проверка наличия обязательных атрибутов отделения
		$requiredAttributeSysNickList = [];
		if ($callObject->regionNick == "perm") {
			$requiredAttributeSysNickList = ["Section_Code", "Building_Code", "Section_Name", "Building_Name", "StructureUnitNomen"];
		}
		if (count($requiredAttributeSysNickList) > 0) {
			$requiredAttributeSysNickList_str = "'" . implode("','", $requiredAttributeSysNickList) . "'";
			$query = "
				select
					Attribute_id as \"Attribute_id\",
					Attribute_Code as \"Attribute_Code\",
					Attribute_Name as \"Attribute_Name\"
				from v_Attribute 
				where Attribute_SysNick in ({$requiredAttributeSysNickList_str})
			";
			$resp = $callObject->queryResult($query);
			if (!is_array($resp)) {
				return "Ошибка при запросе данных обязательных атрибутов";
			}
			$missedAttributes = [];
			foreach ($resp as $item) {
				$key = $item["Attribute_id"];
				$missedAttributes[$key] = $item;
			}
			$query = "
				select AV.Attribute_id as \"Attribute_id\"
				from
				    v_AttributeValue AV
				    inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
				    inner join v_AttributeSign AST on AST.AttributeSign_id = ASV.AttributeSign_id
				where AST.AttributeSign_TableName = 'dbo.LpuSection'
				  and ASV.AttributeSignValue_TablePKey = :LpuSection_id
				  and ASV.AttributeSignValue_begDate <= (
				      case
				          when :LpuSection_disDate is not null then :LpuSection_disDate
				          when tzgetdate() <= :LpuSection_setDate then :LpuSection_setDate
				          else tzgetdate()
				      end
				  )
				  and (ASV.AttributeSignValue_endDate is null or ASV.AttributeSignValue_endDate >= (
				    case
				        when :LpuSection_disDate is not null then :LpuSection_disDate
				        when tzgetdate() <= :LpuSection_setDate then :LpuSection_setDate
				        else tzgetdate()
				    end)
				  )
			";
			$queryParams = [
				"LpuSection_id" => $data["LpuSection_id"],
				"LpuSection_setDate" => $data["LpuSection_setDate"],
				"LpuSection_disDate" => !empty($data["LpuSection_disDate"]) ? $data["LpuSection_disDate"] : null
			];
			$resp = $callObject->queryResult($query, $queryParams);
			if (!is_array($resp)) {
				return "Ошибка при запросе сохраненных атрибутов";
			}
			foreach ($resp as $item) {
				$key = $item["Attribute_id"];
				if (isset($missedAttributes[$key])) {
					unset($missedAttributes[$key]);
				}
			}
			if (count($missedAttributes) > 0) {
				$attributeNameList = [];
				foreach ($missedAttributes as $item) {
					$attributeNameList[] = $item["Attribute_Name"];
				}
				$attributeNameList_str = implode(", ", $attributeNameList);
				return "На отделении отсутствуют обязательные атрибуты – {$attributeNameList_str}.";
			}
		}
		return "";
	}

}