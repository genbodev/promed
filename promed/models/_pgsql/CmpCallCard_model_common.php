<?php

class CmpCallCard_model_common
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $action
	 * @param $params
	 * @return bool
	 */
	public static function actionCmpCloseCardDiag(CmpCallCard_model $callObject, $action, $params)
	{
		/**@var CI_DB_result $result */
		$query = "";
		switch ($action) {
			case "add":
				if (empty($params["Diag_id"]) || empty($params["CmpCloseCard_id"]) || empty($params["DiagSetClass_id"])) {
					return false;
				}
				$query = "
					select
					    cmpclosecarddiag_id as \"CmpCloseCardDiag_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_cmpclosecarddiag_ins(
					    cmpclosecard_id := :CmpCloseCard_id,
					    diagsetclass_id := :DiagSetClass_id,
					    diag_id := :Diag_id,
					    pmuser_id := :pmUser_id
					);
				";
				break;
			case "del":
				if (empty($params["CmpCloseCardDiag_id"])) {
					return false;
				}
				$query = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_cmpclosecarddiag_del(cmpclosecarddiag_id := :CmpCloseCardDiag_id);
				";
				break;
		}
		if ($query == "") {
			return false;
		}
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return true;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function addHomeVisitFromSMP(CmpCallCard_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$Error_Msg = "Редактирование активного посещения невозможно, т.к. параметры посещения были изменены в МО передачи актива";
		//сохранение актива СМП
		$query = "
			select
				hv.HomeVisit_id as \"HomeVisit_id\",
				hv.HomeVisitStatus_id as \"HomeVisitStatus_id\",
				hv.Address_Flat as \"Address_Flat\",
				hv.Address_House as \"Address_House\",
				to_char(hv.HomeVisit_setDT, '{$callObject->dateTimeForm104}')||' '||to_char(hv.HomeVisit_setDT, '{$callObject->dateTimeForm108}') as \"HomeVisit_setDT\",
				hv.KLCity_id as \"KLCity_id\",
				hv.KLRgn_id as \"KLRgn_id\",
				hv.KLStreet_id as \"KLStreet_id\",
				hv.Lpu_id as \"Lpu_id\",
				hv.Address_Address as \"Address_Address\",
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
			from
				v_HomeVisit hv
				left join v_CmpCloseCard CLC on hv.CmpCallCard_id = CLC.CmpCallCard_id
			where hv.CmpCallCard_id = :CmpCallCard_id
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$result = $callObject->db->query($query, $queryParams);
		$ret_arr = $result->result("array");
		$callObject->load->model("HomeVisit_model", "HomeVisit_model");
		$query = "
			select
				CCC.KLCity_id as \"KLCity_id\",
			    CCC.KLTown_id as \"KLTown_id\",
			    CCC.KLTown_id as \"KLTown_id\",
			    CCC.KLStreet_id as \"KLStreet_id\",
			    CCC.KLRgn_id as \"KLRgn_id\",
			    CCC.Person_id as \"Person_id\",
			    CCC.CmpCallCard_Telf as \"HomeVisit_Phone\",
			    CCC.CmpCallCard_Dom as \"Address_House\",
			    CCC.CmpCallCard_Kvar as \"Address_Flat\",
			    CCC.Lpu_ppdid as \"Lpu_id\",
			    CCC.MedService_id as \"MedService_id\",
			    CCC.pmUser_insID as \"pmUser_id\",
			    HV.HomeVisit_id as \"HomeVisit_id\",
			    case when CCT.CmpCallType_Code = 3 then 2 else 1 end as \"HomeVisitCallType_id\",
			    CCC.Person_Age as \"Person_Age\",
			    case when CCC.Person_Age >= 18 then 1 else 2 end as \"HomeVisitWhoCall_id\",
			    CCC.CmpCallCard_Comm as \"HomeVisit_Comment\",
			    CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
			    case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
					case when Town.KLTown_FullName is not null then
						case when (City.KLCity_Name is not null) then ', '||lower(Town.KLSocr_Nick)||'. '||Town.KLTown_Name else lower(Town.KLSocr_Nick)||'. '||Town.KLTown_Name end
					else '' end||
					case when Street.KLStreet_FullName is not null then ', '||lower(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else '' end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else ''
				end as \"Address_Address\",
			    coalesce(CR.CmpReason_Code||'. ', '')||CR.CmpReason_Name as \"HomeVisit_Symptoms\",
			    HV.KLStreet_id as \"HVKLStreet_id\"
			from
				v_CmpCallCard CCC
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_HomeVisit HV on HV.CmpCallCard_id = CCC.CmpCallCard_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
			where CCC.CmpCallCard_id = {$data['CmpCallCard_id']}
			limit 1
		";
		$result = $callObject->db->query($query, $data);
		$arrayRes = $result->result("array");
		if (count($ret_arr) > 0 && $ret_arr[0]["HomeVisitStatus_id"] != 1) {
			$comboParams = [
				"ComboCheck_Patient_id" => 111,
				"ComboValue_710" => $ret_arr[0]["Address_Flat"],
				"ComboValue_708" => $ret_arr[0]["Address_House"],
				"ComboValue_694" => $ret_arr[0]["HomeVisit_setDT"],
				"ComboValue_705" => $ret_arr[0]["KLCity_id"],
				"ComboValue_703" => $ret_arr[0]["KLRgn_id"],
				"ComboValue_707" => $ret_arr[0]["KLStreet_id"],
				"ComboValue_693" => $ret_arr[0]["Lpu_id"],
				"ComboValue_711" => $ret_arr[0]["Address_Address"],
				"ComboValue_695" => $ret_arr[0]["Address_Address"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->saveCmpCloseCardComboValues($comboParams, "edit", ["CmpCloseCard_id" => $ret_arr[0]["CmpCloseCard_id"]], [], $ret_arr[0]["CmpCloseCard_id"], ";", "{$callObject->schema}.p_CmpCloseCardRel_ins");
			return ["success" => true, "Error_Msg" => (string)$Error_Msg];
		}
		$paramsToActive = [
			"HomeVisit_id" => (count($ret_arr) > 0) ? $ret_arr[0]["HomeVisit_id"] : null,
			"Address_Flat" => !empty($data["ComboValue_710"]) ? $data["ComboValue_710"] : null,
			"Address_House" => !empty($data["ComboValue_708"]) ? $data["ComboValue_708"] : null,
			"HomeVisitStatus_id" => 1,
			"HomeVisitSource_id" => 10,
			"HomeVisit_Phone" => !empty($data["Phone"]) ? $data["Phone"] : null,
			"HomeVisit_setDT" => !empty($data["ComboValue_694"]) ? $data["ComboValue_694"] : null,
			"KLCity_id" => !empty($data["ComboValue_705"]) ? $data["ComboValue_705"] : null,
			"KLRgn_id" => !empty($data["ComboValue_703"]) ? $data["ComboValue_703"] : null,
			"KLStreet_id" => !empty($data["ComboValue_707"]) ? $data["ComboValue_707"] : null,
			"Lpu_id" => !empty($data["ComboValue_693"]) ? $data["ComboValue_693"] : null,
			"CallProfType_id" => 1,
			"pmUser_id" => !empty($data["pmUser_id"]) ? $data["pmUser_id"] : null,
			"CmpCallCard_id" => !empty($data["CmpCallCard_id"]) ? $data["CmpCallCard_id"] : null,
			"Person_id" => !empty($data["Person_id"]) ? $data["Person_id"] : null,
			"Address_Address" => !empty($data["ComboValue_711"]) ? $data["ComboValue_711"] : null,
			"HomeVisit_Comment" => !empty($data["HomeVisit_Comment"]) ? $data["HomeVisit_Comment"] : null,
			"Person_Age" => !empty($data["Age"]) ? $data["Age"] : null
		];
		//Соединим массивы, но удалим пустые параметры
		$mergedArray = !empty($arrayRes[0]) ? array_merge($arrayRes[0], array_diff($paramsToActive, [""])) : $paramsToActive;
		$mergedArray["HomeVisit_setDT"] = !empty($mergedArray["HomeVisit_setDT"]) ? DateTime::createFromFormat("d.m.Y H:i", $mergedArray["HomeVisit_setDT"]) : $mergedArray["CmpCallCard_prmDT"];
		//Этот метод сохраняет так же активы из 110у
		if (empty($data["saveActive"])) {
			$nearestDateToHomeVisit = $callObject->HomeVisit_model->getHomeVisitNearestWorkDay(
				["Lpu_id" => $mergedArray["Lpu_id"]],
				$mergedArray["HomeVisit_setDT"]
			);
			if (!isset($nearestDateToHomeVisit["DateInPeriod"])) {
				throw new Exception("Не удалось определить ближайшую дату записи");
			}
			if (!$nearestDateToHomeVisit["DateInPeriod"]) {
				/**@var DateTime $nearestDateToHomeVisitNearestDate */
				$nearestDateToHomeVisitNearestDate = $nearestDateToHomeVisit["NearestDate"];
				return [["Error_Msg" => $nearestDateToHomeVisitNearestDate->format("d.m.Y H:i"), "success" => false]];
			}
		}
		$LpuRegionTypeFilter = ($mergedArray['Person_Age'] >= 18) ? "and LRT.LpuRegionType_SysNick in ('ter', 'vop')" : "and LRT.LpuRegionType_SysNick in ('ped', 'vop')";
		//поиск участка в мо передачи
		$sql = "
			select
				LR.LpuRegion_id as \"LpuRegion_id\",
			    MSR.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_LpuRegionStreet LRS
				left join v_LpuRegion LR on LR.LpuRegion_id = LRS.LpuRegion_id
				left join lateral (
					select MedStaffFact_id
					from v_MedStaffRegion msf
					where msf.LpuRegion_id = LR.LpuRegion_id
					  and msf.MedStaffRegion_begDate <= tzgetdate()
					  and (msf.MedStaffRegion_endDate is null or msf.MedStaffRegion_endDate >= tzgetdate())
					order by msf.MedStaffRegion_isMain desc
					limit 1
				) as MSR on true
				left join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
			where LRS.KLCountry_id = 643
			  and coalesce(LRS.KLCity_id, '') = coalesce(:KLCity_id, '')
			  and LRS.KLStreet_id = :KLStreet_id
			  and LR.Lpu_id = :Lpu_id
			  and (gethouse(LRS.LpuRegionStreet_HouseSet, :Address_House) = 1)
			  {$LpuRegionTypeFilter}
		";
		$result = $callObject->db->query($sql, $mergedArray);
		$res = $result->result("array");
		//нашли участок в мо передачи
		if (is_array($res) && count($res) > 0) {
			if (!empty($res[0]["LpuRegion_id"])) {
				$mergedArray["LpuRegion_cid"] = $res[0]["LpuRegion_id"];
			}
		}
		$r = $callObject->HomeVisit_model->addHomeVisit($mergedArray, true);
		if ($callObject->regionNick != "ufa" && !empty($res[0]["MedStaffFact_id"]) && !empty($r[0]["HomeVisit_id"])) {
			//назначение врача
			$callObject->HomeVisit_model->takeMP([
				"HomeVisit_id" => $r[0]["HomeVisit_id"],
				"MedStaffFact_id" => $res[0]["MedStaffFact_id"],
				"MedPersonal_id" => isset($_SESSION["medpersonal_id"]) ? $_SESSION["medpersonal_id"] : null,
				"pmUser_id" => $data["pmUser_id"]
			]);
		}
		if (!is_array($r) || !isset($r["success"]) || $r["success"] != true) {
			return false;
		}
		return [$r];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function autoCreateCmpPerson(CmpCallCard_model $callObject, $data)
	{
		set_time_limit(0);
		try {
			//Попытка подключиться к основной БД. Если не удасться, то выполнение фунцкии прекратится.
			$callObject->load->database("main", true);
			$callObject->load->model("Person_model");
			$socstatus_Ids = [
				"ufa" => 2,
				"buryatiya" => 10000083,
				"kareliya" => 51,
				"krasnoyarsk" => 10000173,
				"yaroslavl" => 10000266,
				"khak" => 32,
				"astra" => 10000053,
				"kaluga" => 231,
				"penza" => 224,
				"perm" => 2,
				"pskov" => 25,
				"saratov" => 10000035,
				"ekb" => 10000072,
				"msk" => 60,
				"krym" => 262,
				"kz" => 91,
				"by" => 201
			];
			$query = "
				select
					CCC.CmpCallCard_id as \"CmpCallCard_id\",
					IsUnknown.YesNo_Code as \"Person_IsUnknown\",
					CCC.Person_SurName as \"Person_SurName\",
					CCC.Person_SecName as \"Person_SecName\",
					CCC.Person_FirName as \"Person_FirName\",
					to_char(CCC.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"Person_BirthDay\",
					CCC.Sex_id as \"Sex_id\"
				from
					v_CmpCallCard CCC
					left join v_YesNo IsUnknown on IsUnknown.YesNo_id = CCC.Person_IsUnknown
				where CCC.Person_id is null
				  and CCC.Person_IsUnknown is not null
				order by CCC.CmpCallCard_id
			";
			$limit = 200;
			$count = $callObject->getFirstResultFromQuery(getCountSQLPH($query));
			if ($count === false) {
				throw new Exception("Ошибка при получении данных пациентов из карт СМП");
			}
			for ($start = 0; $start <= $count; $start += $limit) {
				$PersonData = $callObject->queryResult(getLimitSQLPH($query, $start, $limit));
				if (!is_array($PersonData)) {
					throw new Exception("Ошибка при получении данных пациентов из карт СМП");
				}
				foreach ($PersonData as $person) {
					$resp = $callObject->Person_model->savePersonEditWindow([
						"Server_id" => $data["Server_id"],
						"NationalityStatus_IsTwoNation" => false,
						"Polis_CanAdded" => 0,
						"Person_SurName" => $person["Person_SurName"],
						"Person_FirName" => $person["Person_FirName"],
						"Person_SecName" => $person["Person_SecName"],
						"Person_BirthDay" => $person["Person_BirthDay"],
						"Person_IsUnknown" => $person["Person_IsUnknown"],
						"PersonSex_id" => $person["Sex_id"],
						"SocStatus_id" => $socstatus_Ids[getRegionNick()],
						"session" => $data["session"],
						"mode" => "add",
						"pmUser_id" => $data["pmUser_id"],
						"Person_id" => null,
						"Polis_begDate" => null
					]);
					if (!$callObject->isSuccessful($resp)) {
						throw new Exception($resp[0]["Error_Msg"]);
					}
					$query = "
						update CmpCallCard
						set Person_id = :Person_id
						where CmpCallCard_id = :CmpCallCard_id
					";
					$queryParams = [
						"Person_id" => $resp[0]["Person_id"],
						"CmpCallCard_id" => $person["CmpCallCard_id"]
					];
					$callObject->db->query($query, $queryParams);
				}
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
		return [["success" => true]];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @return array|bool
	 */
	public static function clearCmpCallCardList(CmpCallCard_model $callObject)
	{
		$queryDeleteList = "
			select CCCLL.CmpCallCardLockList_id
			from v_CmpCallCardLockList CCCLL
			where (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) <= 0
		";
		$resultDeleteList = $callObject->db->query($queryDeleteList);
		if (!is_object($resultDeleteList)) {
			return false;
		}
		$resultDeleteList = $resultDeleteList->result("array");
		if (count($resultDeleteList) == 0) {
			return [["success" => true, "Error_Msg" => ""]];
		}
		foreach ($resultDeleteList as $value) {
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_cmpcallcardlocklist_del(cmpcallcardlocklist_id := :CmpCallCardLockList_id);
			";
			$callObject->db->query($query, $value);
		}
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function defineAccessoryGroupCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		// Проверяем тип арма из которого была запрошена смена статуса и состоит ли пользователь в соответствующей группе
		$user = pmAuthUser::find($_SESSION["login"]);
		// Для диспетчера направлений СМП
		if (array_key_exists("armtype", $data) && $data["armtype"] == "smpdispatchdirect" && $user->havingGroup("SMPDispatchDirections")) {
			$query = "
				select
					case when coalesce(CmpCallCard_IsOpen, 1) = 2
						then
							case
								when Lpu_id is null then
									case
										when CmpCallCardStatusType_id in (1, 2) then CmpCallCardStatusType_id
										when CmpCallCardStatusType_id = 4 then 3
										when CmpCallCardStatusType_id = 3 then 7
										else CmpCallCardStatusType_id + 3
									end
								else CmpCallCardStatusType_id + 3
							end
						else 9
					end as \"CmpGroup_id\"
				from v_CmpCallCard
				where CmpCallCard_id = :CmpCallCard_id
				limit 1
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
			$cccData = $result[0];
			$outData = [
				"success" => true,
				"CmpGroup_id" => $cccData["CmpGroup_id"],
			];
			return $outData;
		} elseif (array_key_exists("armtype", $data) && (($data["armtype"] == "smpdispatchcall" && $user->havingGroup("SMPCallDispath")) || ($data["armtype"] == "smpadmin" && $user->havingGroup("SMPAdmin")))) {
			// Для диспетчера вызовов СМП
			$query = "
				select
					CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
					Lpu_id as \"Lpu_id\",
					CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
					CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\"
				from v_CmpCallCard
				where CmpCallCard_id = :CmpCallCard_id
				limit 1
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
			$cccData = $result[0];
			$outData = [];
			if ($cccData["CmpCallCard_IsOpen"] == 2) {
				if ($cccData["CmpCallCardStatusType_id"] == null) {
					$outData["CmpGroup_id"] = 1;
				} elseif (empty($cccData["Lpu_id"])) {
					switch ($cccData["CmpCallCardStatusType_id"]) {
						case 1:
						case 2:
							$outData["CmpGroup_id"] = $cccData["CmpCallCardStatusType_id"] + 1;
							break;
						case 4:
							$outData["CmpGroup_id"] = 4;
							break;
						case 5:
							$outData["CmpGroup_id"] = 9;
							break;
					}
				} elseif (!empty($cccData["Lpu_id"])) {
					$outData["CmpGroup_id"] = $cccData["CmpCallCardStatusType_id"] + 4;
				}
			} else {
				$outData["CmpGroup_id"] = 10;
			}
			$outData["success"] = true;
			return $outData;
		} elseif (array_key_exists('armtype', $data) && $data['armtype'] == 'slneotl' && $user->havingGroup('PPDMedServiceOper')) {
			//Для оператора ППД
			$query = "
				select
					case when coalesce(CmpCallCard_IsOpen, 1) = 2 then
						case when CmpCallCard_IsReceivedInPPD = 2
						    then
						    	case when CmpCallCardStatusType_id in (1, 2) then CmpCallCardStatusType_id + 3 when CmpCallCardStatusType_id = 4 then 3 + 3 else 7 end
							else
								case when CmpCallCardStatusType_id in (1, 2) then CmpCallCardStatusType_id when CmpCallCardStatusType_id = 4 then 3 else 7 end end
						else 7
					end as \"CmpGroup_id\"
				from v_CmpCallCard
				where CmpCallCard_id = :CmpCallCard_id
				  and Lpu_id is not null
				limit 1
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
			$cccData = $result[0];
			$outData = [
				"success" => true,
				"CmpGroup_id" => $cccData["CmpGroup_id"],
			];
			return $outData;
			// Для всего остального
		} else {
			$query = "
				select
					CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
				    Lpu_id as \"Lpu_id\",
				    CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				    CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\"
				from v_CmpCallCard
				where CmpCallCard_id = :CmpCallCard_id
				limit 1
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
			$cccData = $result[0];
			$outData = [];
			if ($cccData["CmpCallCard_IsOpen"] == 2) {
				if (in_array((int)$cccData["CmpCallCardStatusType_id"], [1, 2, 3, 4, 5])) {
					switch ($cccData["CmpCallCardStatusType_id"]) {
						case 1:
							$outData["CmpGroup_id"] = 2;
							break;
						case 2:
							$outData["CmpGroup_id"] = 3;
							break;
						case 3:
							$outData["CmpGroup_id"] = 4;
							break;
						case 4:
							$outData["CmpGroup_id"] = 5;
							break;
						case 5:
							$outData["CmpGroup_id"] = 7;
							break;
					}
				} else {
					$outData["CmpGroup_id"] = 1;
				}
			} else {
				$outData["CmpGroup_id"] = 6;
			}
			$outData["success"] = true;
			return $outData;
		}
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function existenceNumbersDayYear(CmpCallCard_model $callObject, $data)
	{
		$dolog = (defined("DOLOGSAVECARD") && DOLOGSAVECARD === true) ? true : false;
		if ($dolog !== true) {
			$dolog = false;
		}
		if ($dolog) $callObject->load->library("textlog", ["file" => "saveCmpCallCardNumbers_" . date("Y-m-d") . ".log"]);
		if (!$data["Day_num"] || !$data["Year_num"] || !$data["AcceptTime"]) {
			return false;
		}
		$existenceNumbersDay = false;
		$existenceNumbersYear = false;
		$Double_insDT = false;
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"CmpCallCard_Numv" => $data["Day_num"],
			"CmpCallCard_Ngod" => $data["Year_num"],
			"CmpCallCard_id" => !empty($data["CmpCallCard_id"]) ? $data["CmpCallCard_id"] : null
		];
		$armType = "";
		if (!empty($data["ARMType"])) {
			$armType = $data["ARMType"];
		}
		if ($dolog) {
			$callObject->addLog("проверка:" . $params["CmpCallCard_id"] . " / " . $params["CmpCallCard_Numv"] . " / " . $params["CmpCallCard_Ngod"] . " arm:" . $armType);
		}
		$where = [
			"CCC.Lpu_id = :Lpu_id",
			"CCC.CmpCallCard_Numv = :CmpCallCard_Numv",
			"CCC.CmpCallCard_prmDT >= :startDateTime",
			"CCC.CmpCallCard_prmDT < :endDateTime"
		];
		if (!empty($data["CmpCallCard_id"])) {
			$where[] = "CCC.CmpCallCard_id <> :CmpCallCard_id";
		}
		$timestamp = strtotime($data["AcceptTime"]);
		if ($timestamp === false) {
			return false;
		}
		$data["CmpCallCard_prmDate"] = $data["AcceptTime"];
		$params = array_merge($params, $callObject->getDatesToNumbersDayYear($data));
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$sql = "
			select
				CmpCallCard_id as \"CmpCallCard_id\",
				CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CmpCallCard_insDT as \"CmpCallCard_insDT\"
			from v_CmpCallCard CCC
			{$whereString}
			limit 1
		";
		$query = $callObject->db->query($sql, $params);
		if (!is_object($query)) {
			return false;
		}
		$resNumv = $query->result_array();
		if (!empty($resNumv[0])) {
			if ($dolog) {
				$callObject->addLog("дубль по номеру за день:" . $resNumv[0]["CmpCallCard_id"] . " / " . $resNumv[0]["CmpCallCard_Numv"] . " / " . date_format($resNumv[0]["CmpCallCard_insDT"], "Y-m-d H:i:s:u"));
			}
			$Double_insDT = $resNumv[0]["CmpCallCard_insDT"];
			$existenceNumbersDay = true;
		}
		$where = [
			"CCC.Lpu_id = :Lpu_id",
			"CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod",
			"CCC.CmpCallCard_prmDT >= :firstDayCurrentYearDateTime",
			"CCC.CmpCallCard_prmDT < :firstDayNextYearDateTime"
		];
		if (!empty($data["CmpCallCard_id"])) {
			$where[] = "CCC.CmpCallCard_id <> :CmpCallCard_id";
		} else {
			$data["CmpCallCard_id"] = null;
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$sql = "
			select
				CmpCallCard_id as \"CmpCallCard_id\",
				CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CmpCallCard_insDT as \"CmpCallCard_insDT\"
			from v_CmpCallCard CCC
			{$whereString}
			limit 1
		";
		$query = $callObject->db->query($sql, $params);
		if (!is_object($query)) {
			return false;
		}
		$resNgod = $query->result_array();
		if (!empty($resNgod[0])) {
			if ($dolog) {
				$callObject->addLog("дубль по номеру за год:" . $resNgod[0]["CmpCallCard_id"] . " / " . $resNgod[0]["CmpCallCard_Ngod"] . " / " . date_format($resNgod[0]["CmpCallCard_insDT"], "Y-m-d H:i:s:u"));
			}
			if ($Double_insDT && $Double_insDT > $resNumv[0]["CmpCallCard_insDT"]) {
				$Double_insDT = $resNgod[0]["CmpCallCard_insDT"];
			}
			$existenceNumbersYear = true;
		}
		if ($existenceNumbersDay || $existenceNumbersYear) {
			$newNumValues = $callObject->getCmpCallCardNumber($data);
			if (empty($newNumValues[0])) {
				return false;
			}
		}
		$res_arr = [
			"success" => true,
			"existenceNumbersDay" => $existenceNumbersDay,
			"existenceNumbersYear" => $existenceNumbersYear,
			"nextNumberDay" => $existenceNumbersDay ? $newNumValues[0]["CmpCallCard_Numv"] : $data["Day_num"],
			"nextNumberYear" => $existenceNumbersYear ? $newNumValues[0]["CmpCallCard_Ngod"] : $data["Year_num"],
			"double_insDT" => $Double_insDT
		];
		if ($dolog) {
			$callObject->addLog("проверка окончена:" . $data["CmpCallCard_id"] . " / " . $res_arr["nextNumberDay"] . " / " . $res_arr["nextNumberYear"]);
		}
		return $res_arr;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function findCmpIllegalAct(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		if (empty($data["Address_House"]) && empty($data["Person_id"])) {
			return false;
		}
		if (!empty($data["Person_id"])) {
			$queryParams["Person_id"] = $data["Person_id"];
			$filterArray[] = "Person_id = :Person_id";
		} else {
			if (empty($data["Address_Flat"])) {
				$filterArray[] = "Address_Flat is null";
			}
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
			    cmpillegalact_id as \"CmpIllegalAct_id\",
			    lpu_id as \"Lpu_id\",
			    to_char(CmpIllegalAct_prmDT, '{$callObject->dateTimeForm104}') as \"CmpIllegalAct_prmDT\",
			    address_zip as \"Address_Zip\",
			    klcountry_id as \"KLCountry_id\",
			    klrgn_id as \"KLRgn_id\",
			    klsubrgn_id as \"KLSubRgn_id\",
			    klcity_id as \"KLCity_id\",
			    personsprterrdop_id as \"PersonSprTerrDop_id\",
			    kltown_id as \"KLTown_id\",
			    klstreet_id as \"KLStreet_id\",
			    address_house as \"Address_House\",
			    address_corpus as \"Address_Corpus\",
			    address_flat as \"Address_Flat\",
			    cmpcallcard_id as \"CmpCallCard_id\",
			    CmpIllegalAct_Comment as \"CmpIllegalAct_Comment\"
			from v_CmpIllegalAct
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
	 * Вспомогательная функция преобразования формата даты
	 * Получает строку c датой в формате d.m.Y, возвращает строку с датой в формате Y-m-d
	 * @param $date
	 * @return string|null
	 */
	public static function formatDate($date)
	{
		$d_str = null;
		if (!empty($date)) {
			$date = preg_replace('/\//', '.', $date);
			$d_arr = explode('.', $date);
			if (is_array($d_arr)) {
				$d_arr = array_reverse($d_arr);
			}
			if (count($d_arr) == 3) {
				$d_str = join('-', $d_arr);
			}
		}
		return $d_str;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function identifiPerson(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [];
		if (!empty($data["Person_Surname"])) {
			$filterArray[] = "Person_SurName like :Person_Surname||'%'";
		}
		if (!empty($data["Person_Firname"])) {
			$filterArray[] = "Person_FirName like :Person_Firname||'%'";
		}
		if (!empty($data["Person_Secname"])) {
			$filterArray[] = "Person_SecName like :Person_Secname||'%'";
		}
		if (!empty($data["Person_Birthday"])) {
			$filterArray[] = "Person_BirthDay = :Person_Birthday";
		}
		if (!empty($data["Person_Age"])) {
			$filterArray[] = "age(Person_BirthDay, tzgetdate()) = :Person_Age";
		}
		if (!empty($data["Polis_Ser"])) {
			$filterArray[] = "Polis_Ser like :Polis_Ser";
		}
		if (!empty($data["Polis_Num"])) {
			$filterArray[] = "Polis_Num = :Polis_Num";
		}
		if (!empty($data["Sex_id"])) {
			$filterArray[] = "Sex_id = :Sex_id";
		}
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$query = "
			select
				Person_id as \"Person_id\",
			    Person_SurName as \"Person_Surname\",
			    Person_FirName as \"Person_Firname\",
			    Person_SecName as \"Person_Secname\",
			    to_char(Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			    age(Person_BirthDay, tzgetdate()) as \"Person_Age\",
			    Polis_Ser as \"Polis_Ser\",
			    Polis_Num as \"Polis_Num\",
			    Sex_id as \"Sex_id\"
			from v_PersonState
			{$whereString}
			limit 101
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
	 */
	public static function importSMPCardsTest(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["Lpu_Name"])) {
			$data["Lpu_Name"] = "";
		}
		$query = "
			select count(*) as cnt
			from
				v_CmpCallCard C
				left join v_Lpu L on L.Lpu_id = C.Lpu_id
			where C.CmpCallCard_insDT between :CmpCallCard_insDT1 and :CmpCallCard_insDT2
			  and L.Lpu_Name like '%'||:Lpu_Name||'%'
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0) {
				return ["success" => true, "cnt" => $resp[0]["cnt"]];
			}
		}
		return false;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public static function initiateProposalLogicForLpu(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["Lpu_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор ЛПУ");
		}
		set_time_limit(3000);
		//Выбираем дефолтную логику
		$rulesQuery = "
			select
				CUPS.CmpUrgencyAndProfileStandart_id as \"CmpUrgencyAndProfileStandart_id\",
				CUPS.CmpReason_id as \"CmpReason_id\",
				CUPS.CmpUrgencyAndProfileStandart_Urgency as \"CmpUrgencyAndProfileStandart_Urgency\",
				CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf as \"CmpUrgencyAndProfileStandart_UntilAgeOf\"
			from v_CmpUrgencyAndProfileStandart CUPS
			WHERE coalesce(CUPS.Lpu_id, 0) = 0
		";
		$placesQuery = "
			select
				CUPSRP.CmpUrgencyAndProfileStandart_id as \"CmpUrgencyAndProfileStandart_id\",
				CUPSRP.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
			from
				v_CmpUrgencyAndProfileStandartRefPlace CUPSRP
				left join v_CmpUrgencyAndProfileStandart CUPS on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRP.CmpUrgencyAndProfileStandart_id
			where coalesce(CUPS.Lpu_id, 0) = 0
		";
		$ETSpecQuery = "
			select
				CUPSRSP.CmpUrgencyAndProfileStandart_id as \"CmpUrgencyAndProfileStandart_id\",
				CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority as \"CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority\",
				CUPSRSP.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\"
			from
				v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP
				left join v_CmpUrgencyAndProfileStandart CUPS on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRSP.CmpUrgencyAndProfileStandart_id
			where coalesce(CUPS.Lpu_id, 0) = 0
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $rulesResult
		 * @var CI_DB_result $placesResult
		 * @var CI_DB_result $ETSpecResult
		 */
		$rulesResult = $callObject->db->query($rulesQuery);
		$placesResult = $callObject->db->query($placesQuery);
		$ETSpecResult = $result = $callObject->db->query($ETSpecQuery);
		if (!is_object($rulesResult) || !is_object($placesResult) || !is_object($ETSpecResult)) {
			return false;
		}
		$rulesResult = $rulesResult->result("array");
		$placesResult = $placesResult->result("array");
		$ETSpecResult = $ETSpecResult->result("array");
		//Собираем логику в один многомерный массив для удобства
		$rules = [];
		foreach ($rulesResult as $rule) {
			$rules["{$rule["CmpUrgencyAndProfileStandart_id"]}"] = $rule;
		}
		foreach ($placesResult as $place) {
			if (!isset($rules["{$place["CmpUrgencyAndProfileStandart_id"]}"]["Places"])) {
				$rules["{$place["CmpUrgencyAndProfileStandart_id"]}"]["Places"] = [];
			}
			$rules["{$place["CmpUrgencyAndProfileStandart_id"]}"]["Places"][] = $place;
		}
		foreach ($ETSpecResult as $spec) {
			if (!isset($rules["{$spec["CmpUrgencyAndProfileStandart_id"]}"]["Spec"])) {
				$rules["{$spec["CmpUrgencyAndProfileStandart_id"]}"]["Spec"] = [];
			}
			$rules["{$spec["CmpUrgencyAndProfileStandart_id"]}"]["Spec"][] = $spec;
		}
		$queryInsertRule = "
			select
			    cmpurgencyandprofilestandart_id as \"CmpUrgencyAndProfileStandart_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpurgencyandprofilestandart_ins(
			    cmpreason_id := :CmpReason_id,
			    lpu_id := :Lpu_id,
			    cmpurgencyandprofilestandart_urgency := :CmpUrgencyAndProfileStandart_Urgency,
			    cmpurgencyandprofilestandart_untilageof := :CmpUrgencyAndProfileStandart_UntilAgeOf,
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
		//Сохраняем дефолтные правила с проставленным Lpu_id
		$callObject->beginTransaction();
		foreach ($rules as $rule) {
			//Сначала сохраняем само правило
			$queryParams = [
				"CmpReason_id" => $rule["CmpReason_id"],
				"CmpUrgencyAndProfileStandart_Urgency" => $rule["CmpUrgencyAndProfileStandart_Urgency"],
				"CmpUrgencyAndProfileStandart_UntilAgeOf" => $rule["CmpUrgencyAndProfileStandart_UntilAgeOf"],
				"Lpu_id" => $data["Lpu_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$resultInsertRule = $callObject->db->query($queryInsertRule, $queryParams);
			if (!is_object($resultInsertRule)) {
				$callObject->db->trans_rollback();
				return false;
			}
			$resultInsertRule = $resultInsertRule->result("array");
			if (!empty($resultInsertRule[0]["Error_msg"])) {
				$callObject->rollbackTransaction();
				return $resultInsertRule;
			}
			//Затим  сохраняем привязанные к правилу места
			foreach ($rule["Places"] as $place) {
				$queryParams = [
					"CmpUrgencyAndProfileStandart_id" => $resultInsertRule[0]["CmpUrgencyAndProfileStandart_id"],
					"CmpCallPlaceType_id" => $place["CmpCallPlaceType_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$resultInsertRuleRefPlace = $callObject->db->query($queryInsertRefPlace, $queryParams);
				if (!is_object($resultInsertRuleRefPlace)) {
					$callObject->rollbackTransaction();
					return false;
				}
				$resultInsertRuleRefPlace = $resultInsertRuleRefPlace->result("array");
				if (!empty($resultInsertRuleRefPlace[0]["Error_msg"])) {
					$callObject->rollbackTransaction();
					return $resultInsertRuleRefPlace;
				}
			}
			if (!empty($rule['Spec'])) {
				//Затим  сохраняем привязанные к правилу профили бригад и их приоритеты
				foreach ($rule["Spec"] as $spec) {
					$queryParams = [
						"CmpUrgencyAndProfileStandart_id" => $resultInsertRule[0]["CmpUrgencyAndProfileStandart_id"],
						"EmergencyTeamSpec_id" => $spec["EmergencyTeamSpec_id"],
						"CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority" => $spec["CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$resultInsertRuleRefSpec = $callObject->db->query($queryInsertRefETSpec, $queryParams);
					if (!is_object($resultInsertRuleRefSpec)) {
						$callObject->db->trans_rollback();
						return false;
					}
					$resultInsertRuleRefSpec = $resultInsertRuleRefSpec->result("array");
					if (!empty($resultInsertRuleRefSpec[0]["Error_msg"])) {
						$callObject->rollbackTransaction();
						return $resultInsertRuleRefSpec;
					}
				}
			}
		}
		$callObject->commitTransaction();
		return [["Error_Msg" => ""]];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function lockCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"])) {
			return false;
		};
		$data["CmpCallCardLockList_id"] = null;
		$queryForGettingId = "
			select
				CCCLL.CmpCallCardLockList_id as \"CmpCallCardLockList_id\",
				CCCLL.pmUser_insID as \"pmUser_insID\"
			from v_CmpCallCardLockList CCCLL
			where CCCLL.CmpCallCard_id = :CmpCallCard_id
			  and (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) > 0
		";
		/**@var CI_DB_result $resultGettingId */
		$resultGettingId = $callObject->db->query($queryForGettingId, $data);
		if (!is_object($resultGettingId)) {
			return false;
		}
		$isLockedResult = $resultGettingId->result("array");
		if (!$isLockedResult || !isset($isLockedResult[0]) || !isset($isLockedResult[0]["CmpCallCardLockList_id"]) || $isLockedResult[0]["CmpCallCardLockList_id"] == null) {
			$procedure = "p_CmpCallCardLockList_ins";
		} else {
			if ($isLockedResult[0]["pmUser_insID"] != $data["pmUser_id"]) {
				throw new Exception("Невозможно сохранить. Карта вызова редактируется другим пользователем");
			}
			$procedure = "p_CmpCallCardLockList_upd";
			$data["CmpCallCardLockList_id"] = $isLockedResult[0]["CmpCallCardLockList_id"];
		}
		$selectString = "
		    cmpcallcardlocklist_id as \"CmpCallCardLockList_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    cmpcallcardlocklist_id := :CmpCallCardLockList_id,
			    cmpcallcard_id := :CmpCallCard_id,
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
	 */
	public static function unlockCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $resultGettingId
		 */
		$queryForGettingId = "
			select CCCLL.CmpCallCardLockList_id as \"CmpCallCardLockList_id\"
			from v_CmpCallCardLockList CCCLL
			where CCCLL.CmpCallCard_id = :CmpCallCard_id
			  and (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) > 0
			  and CCCLL.pmUser_insID = :pmUser_id
			";
		$resultGettingId = $callObject->db->query($queryForGettingId, $data);
		if (!is_object($resultGettingId)) {
			return false;
		}
		$isLockedResult = $resultGettingId->result("array");
		if (!$isLockedResult || !$isLockedResult[0] || !$isLockedResult[0]["CmpCallCardLockList_id"] || $isLockedResult[0]["CmpCallCardLockList_id"] == null) {
			return false;
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpcallcardlocklist_del(cmpcallcardlocklist_id := :CmpCallCardLockList_id);
		";
		$queryParams = ["CmpCallCardLockList_id" => $isLockedResult[0]["CmpCallCardLockList_id"]];
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $str
	 * @return string
	 */
	public static function peopleDate($str)
	{
		$s = explode(" ", $str);
		$m = [
			"Jan" => "01",
			"Feb" => "02",
			"Mar" => "03",
			"Apr" => "04",
			"May" => "05",
			"Jun" => "06",
			"Jul" => "07",
			"Aug" => "08",
			"Sep" => "09",
			"Oct" => "10",
			"Nov" => "11",
			"Dec" => "12"
		];
		return $s[2] . "." . $m[$s[1]] . "." . $s[3] . " " . $s[4];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function RefuseOnTimeout(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [
			"(
				select coalesce(
					(
						select DS.DataStorage_Value
						from DataStorage DS
						where DS.DataStorage_Name = 'cmp_waiting_ppd_time'
						  and DS.Lpu_id = 0
						limit 1
					), 20
				)
			) - datediff('mi', CCC.CmpCallCard_updDT, tzGetDate()) < 0",
			"CCC.CmpCallCard_IsReceivedInPPD!=2",
			"CCC.CmpCallCardStatusType_id=1",
			"CCC.Lpu_ppdid is not null",
		];
		$queryParams = [];
		$checkLock = $callObject->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]["CmpCallCard_id"])) {
			return false;
		}
		if (!empty($data["begDate"])) {
			$filterArray[] = "CCC.CmpCallCard_updDT >= :begDate";
			$queryParams["begDate"] = $data["begDate"];
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select CCC.CmpCallCard_id
			from CmpCallCard CCC
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$RefuseData = [];
		$val = $result->result("array");
		for ($i = 0; $i < count($val); $i++) {
			$RefuseData[$i]["CmpCallCard_id"] = $val[$i]["CmpCallCard_id"];
			$RefuseData[$i]["pmUser_id"] = $data["pmUser_id"];
			$RefuseData[$i]["CmpCallCardStatusType_id"] = 3;
			$RefuseData[$i]["CmpCallCardStatus_Comment"] = "Время ожидания истекло";
			$callObject->setStatusCmpCallCard($RefuseData[$i]);
		}
		return true;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function removeSmpFarmacyDrug(CmpCallCard_model $callObject, $data)
	{
		$CmpFarmacyQuery = "
			select
			    cmpfarmacybalance_id as \"CmpFarmacyBalance_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpfarmacybalance_upd(
			    cmpfarmacybalance_id := :CmpFarmacyBalance_id,
			    lpu_id := :Lpu_id,
			    drug_id := :Drug_id,
			    cmpfarmacybalance_packrest := :CmpFarmacyBalance_PackRest,
			    cmpfarmacybalance_doserest := :CmpFarmacyBalance_DoseRest,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $CmpFarmacyResult */
		$CmpFarmacyResult = $callObject->db->query($CmpFarmacyQuery, $data);
		if (!is_object($CmpFarmacyResult)) {
			return false;
		}
		$CmpFarmacyResult = $CmpFarmacyResult->result("array");
		if (strlen($CmpFarmacyResult[0]["Error_Msg"]) > 0) {
			return false;
		}
		$data["CmpFarmacyBalanceRemoveHistory_id"] = null;
		$CmpFarmacyRemoveHistoryQuery = "
			select
			    cmpfarmacybalanceremovehistory_id as \"CmpFarmacyBalanceRemoveHistory_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_cmpfarmacybalanceremovehistory_ins(
			    cmpfarmacybalanceremovehistory_id := :CmpFarmacyBalanceRemoveHistory_id,
			    cmpfarmacybalanceremovehistory_dosecount := :CmpFarmacyBalanceRemoveHistory_DoseCount,
			    cmpfarmacybalanceremovehistory_packcount := :CmpFarmacyBalanceRemoveHistory_PackCount,
			    cmpfarmacybalance_id := :CmpFarmacyBalance_id,
			    emergencyteam_id := :EmergencyTeam_id,
			    cmpcallcard_id := null,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $CmpFarmacyRemoveHistory */
		$CmpFarmacyRemoveHistory = $callObject->db->query($CmpFarmacyRemoveHistoryQuery, $data);
		if (!is_object($CmpFarmacyRemoveHistory)) {
			return false;
		}
		return $CmpFarmacyRemoveHistory->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function reportBrig(CmpCallCard_model $callObject, $data)
	{
		//Показатели по заболеваниям
		$query = "
			select
				ET.EmergencyTeam_Num as \"Num\",
				ESP.EmergencyTeamSpec_Name as \"Spec\"
			from
				v_EmergencyTeamStatusHistory ETH
				left join v_EmergencyTeam ET on ET.EmergencyTeam_id = ETH.EmergencyTeam_id
				left join v_EmergencyTeamSpec ESP on ESP.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id
			where ETH.EmergencyTeamStatusHistory_insDT >= :Daydate1
			  and ETH.EmergencyTeamStatusHistory_insDT <= :Daydate2
			group by
				ET.EmergencyTeam_Num,
			    ESP.EmergencyTeamSpec_Name
		";
		$queryParams = [
			"Daydate1" => $data["daydate1"],
			"Daydate2" => $data["daydate2"]
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
	public static function reportDayDiag(CmpCallCard_model $callObject, $data)
	{
		//Показатели по заболеваниям
		$query = "
			select
				count(1) as \"cnt\",
				rtrim(coalesce(D.Diag_Code, '')||' '||coalesce(D.Diag_Name, '')) as \"CmpDiag_Name\"
			from
				dbo.v_CmpCloseCard CLC
				left join v_Diag D on D.Diag_id = CLC.Diag_id
			where CLC.CmpCloseCard_insDT >= :Daydate1
			  and CLC.CmpCloseCard_insDT <= :Daydate2
			group by
				CLC.Diag_id,
			    D.Diag_Code,
				D.Diag_Name
		";
		$query = ($callObject->schema != "dbo") ? str_replace("dbo", $callObject->schema, $query) : $query;
		$queryParams = [
			"Daydate1" => $data["daydate1"],
			"Daydate2" => $data["daydate2"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	public static function sendCmpCallCardToActiveMQ($data)
	{
		$params = [
			"id" => $data["CmpCallCard_id"],
			"rid" => !empty($data["CmpCallCard_rid"]) ? $data["CmpCallCard_rid"] : null,
			"numberYear" => !empty($data["CmpCallCard_Ngod"]) ? $data["CmpCallCard_Ngod"] : null,
			"number" => !empty($data["CmpCallCard_Numv"]) ? $data["CmpCallCard_Numv"] : null,
			"phone" => !empty($data["CmpCallCard_Telf"]) ? $data["CmpCallCard_Telf"] : null,
			"urgency" => !empty($data["CmpCallCard_Urgency"]) ? $data["CmpCallCard_Urgency"] : null,
			"comment" => !empty($data["CmpCallCard_Comm"]) ? $data["CmpCallCard_Comm"] : null,
			"callPlaceType" => [
				"id" => !empty($data["CmpCallPlaceType_id"]) ? $data["CmpCallPlaceType_id"] : null
			],
			"callType" => [
				"id" => !empty($data["CmpCallType_id"]) ? $data["CmpCallType_id"] : null
			],
			"whoCallType" => [
				"id" => !empty($data["CmpCallerType_id"]) ? $data["CmpCallerType_id"] : null
			],
			"reason" => [
				"id" => !empty($data["CmpReason_id"]) ? $data["CmpReason_id"] : null
			],
			"refusalReason" => [
				"id" => !empty($data["CmpRejectionReason_id"]) ? $data["CmpRejectionReason_id"] : null
			],
			"region" => [
				"id" => !empty($data["KLRgn_id"]) ? $data["KLRgn_id"] : null
			],
			"city" => [
				"id" => !empty($data["KLCity_id"]) ? $data["KLCity_id"] : null
			],
			"street" => [
				"id" => !empty($data["KLStreet_id"]) ? $data["KLStreet_id"] : null
			],
			"house" => !empty($data["CmpCallCard_Dom"]) ? $data["CmpCallCard_Dom"] : null,
			"corpus" => !empty($data["CmpCallCard_Korp"]) ? $data["CmpCallCard_Korp"] : null,
			"entrance" => !empty($data["CmpCallCard_Podz"]) ? $data["CmpCallCard_Podz"] : null,
			"entranceCode" => !empty($data["CmpCallCard_Kodp"]) ? $data["CmpCallCard_Kodp"] : null,
			"floor" => !empty($data["CmpCallCard_Etaj"]) ? $data["CmpCallCard_Etaj"] : null,
			"flat" => !empty($data["CmpCallCard_Kvar"]) ? $data["CmpCallCard_Kvar"] : null,
			"unformalizedAddress" => [
				"id" => !empty($data["UnformalizedAddressDirectory_id"]) ? $data["UnformalizedAddressDirectory_id"] : null
			],
			"lat" => !empty($data["CmpCallCard_CallLtd"]) ? $data["CmpCallCard_CallLtd"] : null,
			"lon" => !empty($data["CmpCallCard_CallLng"]) ? $data["CmpCallCard_CallLng"] : null,
			"acceptTime" => !empty($data["CmpCallCard_prmDT"]) ? $data["CmpCallCard_prmDT"] : null,
			"transferTime" => !empty($data["CmpCallCard_Tper"]) ? $data["CmpCallCard_Tper"] : null,
			"departureTime" => !empty($data["CmpCallCard_Vyez"]) ? $data["CmpCallCard_Vyez"] : null,
			"arrivalTime" => !empty($data["CmpCallCard_Przd"]) ? $data["CmpCallCard_Przd"] : null,
			"transportTime" => !empty($data["CmpCallCard_Tgsp"]) ? $data["CmpCallCard_Tgsp"] : null,
			"hospitalArrivalTime" => !empty($data["CmpCallCard_HospitalizedTime"]) ? $data["CmpCallCard_HospitalizedTime"] : null,
			"returnTime" => !empty($data["CmpCallCard_Tvzv"]) ? $data["CmpCallCard_Tvzv"] : null,
			"endingTime" => !empty($data["CmpCallCard_Tisp"]) ? $data["CmpCallCard_Tisp"] : null,
			"fillDuration" => !empty($data["CmpCallCard_DiffTime"]) ? $data["CmpCallCard_DiffTime"] : null,
			"callDuration" => !empty($data["CmpCallCard_Dlit"]) ? $data["CmpCallCard_Dlit"] : null,
			"isEmergency" => !empty($data["CmpCallCard_IsExtra"]) ? $data["CmpCallCard_IsExtra"] : null,
			"isUrgent" => !empty($data["CmpCallCard_IsNMP"]) ? $data["CmpCallCard_IsNMP"] : null,
			"isActiveToPolyclinic" => !empty($data["CmpCallCard_IsPoli"]) ? $data["CmpCallCard_IsPoli"] : null,
			"lpu" => [
				"id" => !empty($data["Lpu_id"]) ? $data["Lpu_id"] : null
			],
			"lpuBuilding" => [
				"id" => !empty($data["LpuBuilding_id"]) ? $data["LpuBuilding_id"] : null
			],
			"emergencyTeam" => [
				"id" => !empty($data["EmergencyTeam_id"]) ? $data["EmergencyTeam_id"] : null
			],
			"person" => [
				"id" => !empty($data["Person_id"]) ? $data["Person_id"] : null,
				"birthday" => !empty($data["Person_Birthday"]) ? $data["Person_Birthday"] : null,
				"firname" => !empty($data["Person_FirName"]) ? $data["Person_FirName"] : null,
				"secname" => !empty($data["Person_SecName"]) ? $data["Person_SecName"] : null,
				"surname" => !empty($data["Person_SurName"]) ? $data["Person_SurName"] : null,
				"sex" => [
					"id" => !empty($data["Sex_id"]) ? $data["Sex_id"] : null
				]
			],
			"age" => !empty($data["Person_Age"]) ? $data["Person_Age"] : null,
			"isOftenCaller" => !empty($data["Person_isOftenCaller"]) ? $data["Person_isOftenCaller"] : null,
			"diag" => [
				"id" => !empty($data["Diag_uid"]) ? $data["Diag_uid"] : null
			],
			"lpuTransferUrgent" => [
				"id" => !empty($data["Lpu_ppdid"]) ? $data["Lpu_ppdid"] : null
			],
			"status" => [
				"id" => !empty($data["CmpCallCardStatusType_id"]) ? $data["CmpCallCardStatusType_id"] : null,
				"comment" => !empty($data["CmpCallCardStatus_Comment"]) ? $data["CmpCallCardStatus_Comment"] : null,
			],
			"armType" => [
				"sysNick" => !empty($data["ARMType"]) ? $data["ARMType"] : null
			]
		];
		sendStompMQMessage($params, "Rule", "/queue/ru.swan.emergency.urgentCallCard");
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	public static function sendCmpCloseCardToActiveMQ($data)
	{
		$params = [
			"id" => !empty($data["CmpCloseCard_id"]) ? $data["CmpCloseCard_id"] : null,
			"emergencyCallCard" => [
				"id" => !empty($data["CmpCallCard_id"]) ? $data["CmpCallCard_id"] : null,
			],
			"lpu" => [
				"id" => !empty($data["Lpu_id"]) ? $data["Lpu_id"] : null,
			],
			"lpuBuilding" => [
				"id" => !empty($data["LpuBuilding_id"]) ? $data["LpuBuilding_id"] : null,
			],
			//"number" => !empty($data["Day_num"]) ? $data["Day_num"] : null,
			"numberYear" => !empty($data["Year_num"]) ? $data["Year_num"] : null,
			"acceptTime" => !empty($data["AcceptTime"]) ? $data["AcceptTime"] : null,
			"transferTime" => !empty($data["TransTime"]) ? $data["TransTime"] : null,
			"departureTime" => !empty($data["GoTime"]) ? $data["GoTime"] : null,
			"arrivalTime" => !empty($data["ArriveTime"]) ? $data["ArriveTime"] : null,
			"transportTime" => !empty($data["TransportTime"]) ? $data["TransportTime"] : null,
			"hospitalArrivalTime" => !empty($data["ToHospitalTime"]) ? $data["ToHospitalTime"] : null,
			"returnTime" => !empty($data["BackTime"]) ? $data["BackTime"] : null,
			"endingTime" => !empty($data["EndTime"]) ? $data["EndTime"] : null,
			"totalTime" => !empty($data["SummTime"]) ? $data["SummTime"] : null,
			"kilometrage" => !empty($data["Kilo"]) ? $data["Kilo"] : null,
			"subRegion" => [
				"id" => !empty($data["Area_id"]) ? $data["Area_id"] : null
			],
			"city" => [
				"id" => !empty($data["City_id"]) ? $data["City_id"] : null
			],
			"town" => [
				"id" => !empty($data["Town_id"]) ? $data["Town_id"] : null
			],
			"street" => [
				"id" => !empty($data["Street_id"]) ? $data["Street_id"] : null
			],
			"house" => !empty($data["House"]) ? $data["House"] : null,
			"corpus" => !empty($data["Korpus"]) ? $data["Korpus"] : null,
			"floor" => !empty($data["Level"]) ? $data["Level"] : null,
			"entrance" => !empty($data["Entrance"]) ? $data["Entrance"] : null,
			"entranceCode" => !empty($data["CodeEntrance"]) ? $data["CodeEntrance"] : null,
			"flat" => !empty($data["Office"]) ? $data["Office"] : null,
			"room" => !empty($data["Room"]) ? $data["Room"] : null,
			"phone" => !empty($data["Phone"]) ? $data["Phone"] : null,
			"reason" => [
				"id" => !empty($data["CallPovod_id"]) ? $data["CallPovod_id"] : null,
			],
			"callType" => [
				"id" => !empty($data["CallType_id"]) ? $data["CallType_id"] : null,
			],
			"whoCallType" => [
				"id" => !empty($data["CmpCallerType_id"]) ? $data["CmpCallerType_id"] : null,
			],
			"isEmergency" => !empty($data["CmpCloseCard_IsExtra"]) ? $data["CmpCloseCard_IsExtra"] : null,
			"acceptMedPersonal" => [
				"id" => !empty($data["FeldsherAccept"]) ? $data["FeldsherAccept"] : null,
			],
			"transferMedPersonal" => [
				"id" => !empty($data["FeldsherTrans"]) ? $data["FeldsherTrans"] : null,
			],
			"emergencyTeamNumber" => !empty($data["EmergencyTeamNum"]) ? $data["EmergencyTeamNum"] : null,
			"emergencyTeamSpec" => [
				"id" => !empty($data["EmergencyTeamSpec_id"]) ? $data["EmergencyTeamSpec_id"] : null,
			],
			"emergencyTeam" => [
				"id" => !empty($data["EmergencyTeam_id"]) ? $data["EmergencyTeam_id"] : null,
			],
			"medStaffFact" => [
				"id" => !empty($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null,
			],
			"person" => [
				"id" => !empty($data["Person_id"]) ? $data["Person_id"] : null,
			],
			"surname" => !empty($data["Fam"]) ? $data["Fam"] : null,
			"firname" => !empty($data["Name"]) ? $data["Name"] : null,
			"secname" => !empty($data["Middle"]) ? $data["Middle"] : null,
			"sex" => [
				"id" => !empty($data["Sex_id"]) ? $data["Sex_id"] : null,
			],
			"polis" => [
				"series" => !empty($data["Person_PolisSer"]) ? $data["Person_PolisSer"] : null,
			],
			"number" => !empty($data["Person_PolisNum"]) ? $data["Person_PolisNum"] : null,
			"federalNumber" => !empty($data["CmpCloseCard_PolisEdNum"]) ? $data["CmpCloseCard_PolisEdNum"] : null,
			"age" => !empty($data["Age"]) ? $data["Age"] : null,
			"document" => !empty($data["DocumentNum"]) ? $data["DocumentNum"] : null,
			"job" => !empty($data["Work"]) ? $data["Work"] : null,
			"respiratoryRate" => !empty($data["Chd"]) ? $data["Chd"] : null,
			"cardiacRate" => !empty($data["Chss"]) ? $data["Chss"] : null,
			"feces" => !empty($data["Shit"]) ? $data["Shit"] : null,
			"temperature" => !empty($data["Temperature"]) ? $data["Temperature"] : null,
			"urination" => !empty($data["Urine"]) ? $data["Urine"] : null,
			"arterialPressure" => !empty($data["AD"]) ? $data["AD"] : null,
			"arterialPressureWork" => !empty($data["WorkAD"]) ? $data["WorkAD"] : null,
			"glucometry" => !empty($data["Gluck"]) ? $data["Gluck"] : null,
			"pulse" => !empty($data["Pulse"]) ? $data["Pulse"] : null,
			"pulseOximetry" => !empty($data["Pulsks"]) ? $data["Pulsks"] : null,
			"complaints" => !empty($data["Complaints"]) ? $data["Complaints"] : null,
			"otherSign" => !empty($data["OtherSympt"]) ? $data["OtherSympt"] : null,
			"additionalInfo" => !empty($data["CmpCloseCard_AddInfo"]) ? $data["CmpCloseCard_AddInfo"] : null,
			"anamnesis" => !empty($data["Anamnez"]) ? $data["Anamnez"] : null,
			"note" => !empty($data["DescText"]) ? $data["DescText"] : null,
			"localStatus" => !empty($data["LocalStatus"]) ? $data["LocalStatus"] : null,
			"arterialPressureEfficiency" => !empty($data["EfAD"]) ? $data["EfAD"] : null,
			"respiratoryRateEfficiency" => !empty($data["EfChd"]) ? $data["EfChd"] : null,
			"cardiacRateEfficiency" => !empty($data["EfChss"]) ? $data["EfChss"] : null,
			"glucometryEfficiency" => !empty($data["EfGluck"]) ? $data["EfGluck"] : null,
			"pulseEfficiency" => !empty($data["EfPulse"]) ? $data["EfPulse"] : null,
			"pulseOximetryEfficiency" => !empty($data["EfPulsks"]) ? $data["EfPulsks"] : null,
			"temperatureEfficiency" => !empty($data["EfTemperature"]) ? $data["EfTemperature"] : null,
			"ecgBefore" => !empty($data["Ekg1"]) ? $data["Ekg1"] : null,
			"ecgBeforeDate" => !empty($data["Ekg1Time"]) ? $data["Ekg1Time"] : null,
			"ecgAfter" => !empty($data["Ekg2"]) ? $data["Ekg2"] : null,
			"ecgAfterDate" => !empty($data["Ekg2Time"]) ? $data["Ekg2Time"] : null,
			"helpOnAuto" => !empty($data["HelpAuto"]) ? $data["HelpAuto"] : null,
			"helpOnPlace" => !empty($data["HelpPlace"]) ? $data["HelpPlace"] : null,
			"isAcrocyanosis" => !empty($data["isAcro"]) ? $data["isAcro"] : null,
			"isAlco" => !empty($data["isAlco"]) ? $data["isAlco"] : null,
			"isAnisocoria" => !empty($data["isAnis"]) ? $data["isAnis"] : null,
			"isBreathingProcessing" => !empty($data["isHale"]) ? $data["isHale"] : null,
			"isLight" => !empty($data["isLight"]) ? $data["isLight"] : null,
			"isMeningeal" => !empty($data["isMenen"]) ? $data["isMenen"] : null,
			"isMottledSkin" => !empty($data["isMramor"]) ? $data["isMramor"] : null,
			"isNystagmus" => !empty($data["isNist"]) ? $data["isNist"] : null,
			"isPeritoneumIrritation" => !empty($data["isPerit"]) ? $data["isPerit"] : null,
			"isNonHospitalization" => !empty($data["isOtkazHosp"]) ? $data["isOtkazHosp"] : null,
			"isNonMedicalCare" => !empty($data["isOtkazMed"]) ? $data["isOtkazMed"] : null,
			"isConsentToMedicalIntervention" => !empty($data["isSogl"]) ? $data["isSogl"] : null,
			"diag" => [
				"id" => !empty($data["Diag_id"]) ? $data["Diag_id"] : null
			],
			"diagAccomp" => [
				"id" => !empty($data["Diag_sid"]) ? $data["Diag_sid"] : null
			],
			"diagExact" => [
				"id" => !empty($data["Diag_uid"]) ? $data["Diag_uid"] : null
			],
			"emergencyResult" => [
				"id" => !empty($data["CmpResult_id"]) ? $data["CmpResult_id"] : null
			]
		];
		sendStompMQMessage($params, "Rule", "/queue/ru.swan.emergency.emergencyCloseCard");
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	public static function sendStatusCmpCallCardToActiveMQ($data)
	{
		$params = [
			"id" => !empty($data["CmpCallCard_id"]) ? $data["CmpCallCard_id"] : null,
			"status" => [
				"id" => !empty($data["CmpCallCardStatusType_id"]) ? $data["CmpCallCardStatusType_id"] : null,
			],
			"comment" => !empty($data["CmpCallCardStatus_Comment"]) ? $data["CmpCallCardStatus_Comment"] : null,

			"reason" => [
				"id" => !empty($data["CmpReason_id"]) ? $data["CmpReason_id"] : null
			],
			"isUrgent" => [
				"id" => !empty($data["CmpCallCard_isNMP"]) ? $data["CmpCallCard_isNMP"] : null
			],
			"isReceivedInPPD" => !empty($data["CmpCallCard_IsReceivedInPPD "]) ? $data["CmpCallCard_IsReceivedInPPD "] : null,
			"transferFromUrgentReason" => [
				"id" => !empty($data["CmpMoveFromNmpReason_id"]) ? $data["CmpMoveFromNmpReason_id"] : null,
			],
			"returnToEmergencyReason" => [
				"id" => !empty($data["CmpReturnToSmpReason_id"]) ? $data["CmpReturnToSmpReason_id"] : null,
			]
		];
		sendStompMQMessage($params, "Rule", "/queue/ru.swan.emergency.urgentCallCard.status");
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	public static function sendLpuTransmitToActiveMQ($data)
	{
		$params = [
			"id" => !empty($data["CmpCallCard_id"]) ? $data["CmpCallCard_id"] : null,
			"lpuTransferUrgent" => [
				"id" => !empty($data["Lpu_ppdid"]) ? $data["Lpu_ppdid"] : null
			]
		];
		sendStompMQMessage($params, "Rule", "/queue/ru.swan.emergency.urgentCallCard.changeLpu");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 */
	public static function sendPushOnSetMergencyTeam(CmpCallCard_model $callObject, $data)
	{
		$callObject->load->library("textlog", ["file" => "sendPushOnSetMergencyTeam_" . date("Y-m-d") . ".log"]);
		$callObject->load->helper("Push");
		// получаем данные
		$query = "
			select
				ccc.CmpCallCard_id as \"CmpCallCard_id\",
				ccc.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				ccc.CmpCallCard_Telf as \"CmpCallCard_Telf\",
				coalesce(cct.CmpCallerType_Name, ccc.CmpCallCard_Ktov) as \"CmpCallCard_Ktov\",
				ccpt.CmpCallPlaceType_Name as \"CmpCallPlaceType_Name\",
				ccc.CmpCallCard_defCom as \"CmpCallCard_defCom\",
				UAD.UnformalizedAddressDirectory_lat as \"lat\",
				UAD.UnformalizedAddressDirectory_lng as \"lng\",
				case when ccc.CmpCallCard_IsExtra = 2 then 'true' else 'false' end as \"CmpCallCard_IsExtra\",
				ccc.KLCity_id as \"KLCity_id\",
				KLC.KLCity_Name as \"KLCity_Name\",
				ccc.KLStreet_id as \"KLStreet_id\",
				KLS.KLStreet_Name as \"KLStreet_Name\",
				ccc.KLTown_id as \"KLTown_id\",
				KLT.KLTown_Name as \"KLTown_Name\",
				ccc.CmpCallCard_Dom as \"CmpCallCard_Dom\",
				ccc.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
				ccc.CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
				ccc.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				cccst.CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\",
				cccst.CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\",
				ccc.Person_id as \"Person_id\",
				ccc.CmpReason_id as \"CmpReason_id\",
				cr.CmpReason_Code as \"CmpReason_Code\",
				cr.CmpReason_Name as \"CmpReason_Name\",
				ccc.CmpCallType_id as \"CmpCallType_id\",
				ccallt.CmpCallType_Code as \"CmpCallType_Code\",
				ccallt.CmpCallType_Name as \"CmpCallType_Name\",
				to_char(ccc.CmpCallCard_prmDT, '{$callObject->dateTimeForm120}') as \"CmpCallCard_prmDT\",
				to_char(trans.CmpCallCard_transDT, '{$callObject->dateTimeForm120}') as \"CmpCallCard_transDT\"
			from
				v_CmpCallCard ccc
				left join v_CmpCallerType cct on cct.CmpCallerType_id = ccc.CmpCallerType_id
				left join v_CmpCallPlaceType ccpt on ccpt.CmpCallPlaceType_id = ccc.CmpCallPlaceType_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_CmpCallCardStatusType cccst on cccst.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id
				left join v_CmpReason cr on cr.CmpReason_id = ccc.CmpReason_id
				left join v_CmpCallType ccallt on ccallt.CmpCallType_id = ccc.CmpCallType_id
				left join v_KLCity KLC on KLC.KLCity_id = CCC.KLCity_id
				left join v_KLTown KLT on KLT.KLTown_id = CCC.KLTown_id
				left join v_KLStreet KLS on KLS.KLStreet_id = CCC.KLStreet_id
				left join lateral (
					select CmpCallCardStatus_insDT as CmpCallCard_transDT
					from
						v_CmpCallCardStatus
						left join v_PmUser PU on PU.PMUser_id = pmUser_insID
					where CmpCallCardStatusType_id = 2
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as trans on true
			where ccc.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$resp_cc = $callObject->queryResult($query, $queryParams);
		if (!empty($resp_cc[0]['CmpCallCard_id'])) {
			// ищем последний токен у старшего бригады
			$query = "
				select pucd.pmUserCacheDevice_DeviceID as \"pmUserCacheDevice_DeviceID\"
				from
					v_EmergencyTeam et
					inner join v_pmUserCache puc on puc.MedPersonal_id = et.EmergencyTeam_HeadShift
					inner join v_pmUserCacheDevice pucd on puc.pmUser_id = pucd.PMUserCache_id
				where et.EmergencyTeam_id = :EmergencyTeam_id
				  and pucd.pmUserCacheDevice_DeviceID is not null
				order by pucd.pmUserCacheDevice_insDT
				limit 1
			";
			$queryParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
			$resp_pucd = $callObject->queryResult($query, $queryParams);
			foreach ($resp_pucd as $one_pucd) {
				$to = $one_pucd["pmUserCacheDevice_DeviceID"];
				$params = ["message" => json_encode($resp_cc[0])];
				$apiKey = $callObject->config->item("FCM_API_KEY");
				$result = sendPush($to, $params, $apiKey);
				$callObject->addLog("to: " . $to . " apiKey: " . $apiKey);
				$message_id = null;
				if (!empty($result) && mb_strpos($result, "message_id") !== false) {
					$push_result = json_decode($result, true);
					if (!empty($push_result["results"][0]["message_id"])) {
						$message_id = $push_result["results"][0]["message_id"];
					}
				}
				$callObject->saveCmpCallCardMessage([
					"Message_id" => $message_id,
					"CmpCallCard_id" => $data["CmpCallCard_id"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				$callObject->addLog("result: " . $result);
				
				$apiKey = $callObject->config->item("FCM_API_KEY_NMP");
				$result = sendPush($to, $params, $apiKey);
				$callObject->addLog("to: " . $to . " apiKey: " . $apiKey);
				$message_id = null;
				if (!empty($result) && mb_strpos($result, "message_id") !== false) {
					$push_result = json_decode($result, true);
					if (!empty($push_result["results"][0]["message_id"])) {
						$message_id = $push_result["results"][0]["message_id"];
					}
				}
				$callObject->saveCmpCallCardMessage([
					"Message_id" => $message_id,
					"CmpCallCard_id" => $data["CmpCallCard_id"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				$callObject->addLog("result: " . $result);
			}
		} else {
			$callObject->addLog("Ошибка получения данных по CmpCallCard_id=" . $data['CmpCallCard_id']);
		}
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @param $reactionType
	 * @return bool
	 */
	public static function sendReactionToActiveMQ(CmpCallCard_model $callObject, $data, $reactionType)
	{
		if (empty($data["CmpCallCard_id"]) || empty($data["Card112_Guid"])) {
			return false;
		}
		if (!in_array($reactionType, ["add", "finish"])) {
			return false;
		}
		switch ($reactionType) {
			case "add":
				$paramsMQ = [
					"id" => $data["CmpCallCard_id"],
					"guid" => $data["Card112_Guid"],
					"emergencyTeamStatus" => [
						"id" => !empty($data["EmergencyTeamStatus_id"]) ? $data["EmergencyTeamStatus_id"] : null,
						"name" => !empty($data["EmergencyTeamStatus_Name"]) ? $data["EmergencyTeamStatus_Name"] : null,
						"actionType" => $data["actionType"],
						"remark" => $data["remark"],
					],
					"emergencyTeam" => [
						"id" => !empty($data["EmergencyTeam_id"]) ? $data["EmergencyTeam_id"] : null,
						"number" => !empty($data["EmergencyTeam_Num"]) ? $data["EmergencyTeam_Num"] : " ",
					],
					"insDate" => !empty($data["EmergencyTeamStatusHistory_insDT"]) ? $data["EmergencyTeamStatusHistory_insDT"] : null,
					"operator" => $data["MedPersonal_TabCode"]
				];
				break;
			case "finish":
				$callObject->load->model("EmergencyTeam_model4E", "ETModel");
				$ETstatus_id = $callObject->ETModel->getEmergencyTeamStatusIdByCode(4);
				$paramsMQ = [
					"id" => $data["CmpCallCard_id"],
					"guid" => $data["Card112_Guid"],
					"emergencyTeamStatus" => [
						"id" => $ETstatus_id,
						"code" => 4,
						"name" => "Конец обслуживания"
					],
					"emergencyTeam" => [
						"id" => !empty($data["EmergencyTeam_id"]) ? $data["EmergencyTeam_id"] : null,
						"number" => !empty($data["EmergencyTeam_Num"]) ? $data["EmergencyTeam_Num"] : null,
					],
					"insDate" => !empty($data["EmergencyTeamStatusHistory_insDT"]) ? $data["EmergencyTeamStatusHistory_insDT"] : null,
					"operator" => !empty($data["operator"]) ? $data["operator"] : null
				];
				break;
		}
		if (defined("STOMPMQ_MESSAGE_DESTINATION_EMERGENCY")) {
			sendStompMQMessageOld($paramsMQ, "Rule", STOMPMQ_MESSAGE_DESTINATION_EMERGENCY);
		}
		return true;
	}

	public static function testAM()
	{
		$rand = rand(1, 3);
		switch ($rand) {
			case 1:
				sendStompMQMessage([
					"type" => "insert",
					"table" => "tmp._ck",
					"params" => [
						"CureStandart_id" => 2,
						"id" => 1,
						"DiagFedMes_FileName" => 1,
						"Duration" => 1
					],
					"keyParam" => null
				], "Rule", "/queue/ru.swan.emergency.tomaindb");
				break;
			case 2:
				sendStompMQMessage([
					"type" => "update",
					"table" => "tmp._ck",
					"params" => [
						"CureStandart_id" => 2,
						"id" => 1,
						"DiagFedMes_FileName" => 3,
						"Duration" => 1
					],
					"keyParam" => "CureStandart_id"
				], "Rule", "/queue/ru.swan.emergency.tomaindb");
				break;
			case 3:
				sendStompMQMessage([
					"type" => "delete",
					"table" => "tmp._ck",
					"params" => [
						"CureStandart_id" => 2
					],
					"keyParam" => "CureStandart_id"
				], "Rule", "/queue/ru.swan.emergency.tomaindb");
				break;
		}
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function unrefuseCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		// Находим последнюю запись о статусе
		$query = "
			select CmpCallCardStatus_id as \"CmpCallCardStatus_id\"
			from v_CmpCallCardStatus
			where CmpCallCard_id = :CmpCallCard_id
			order by CmpCallCardStatus_insDT desc
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (count($result) > 0) {
			if ($callObject->deleteCmpCallCardStatus($result[0]) === false) {
				throw new Exception("Ошибка при выполнении запроса к базе данных");
			}
		}
		$callObject->setStatusCmpCallCard([
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"CmpCallCardStatusType_id" => 0,
			"CmpCallCardStatus_Comment" => null,
			"pmUser_id" => $data["pmUser_id"]
		]);
		return [["success" => true]];
	}

	/**
	 * @desc Обновление параметров CmpCallCard при закрычии 110у
	 * @param array $data
	 * @return boolean
	 */
	public static function updateCmpCallCardByClose(CmpCallCard_model $callObject, $data)
	{
		$dolog = (defined("DOLOGSAVECARD") && DOLOGSAVECARD === true) ? true : false;
		if ($dolog) {
			$callObject->load->library("textlog", ["file" => "saveCmpCallCardNumbers_" . date("Y-m-d") . ".log"]);
		}
		if (!empty($data["CmpCallCard_id"]) && $data["CmpCallCard_id"] > 0) {
			$params = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
			$setAdd = "";
			if (!empty($data["AcceptTime"])) {
				$AcceptDate = DateTime::createFromFormat("d.m.Y H:i", $data["AcceptTime"]);
				$aDate = $AcceptDate->format("Y-m-d H:i");
				$setAdd .= ", CmpCallCard_prmDT = :CmpCallCard_prmDT";
				$params["CmpCallCard_prmDT"] = $aDate;
				if ($dolog) $callObject->addLog("update CmpCallCard_id=" . $data["CmpCallCard_id"] . " CmpCallCard_prmDT=" . $params["CmpCallCard_prmDT"]);
			}
			if (!empty($data["Person_id"]) && $data["Person_id"] > 0) {
				$setAdd .= ", Person_id = :Person_id";
				$params["Person_id"] = $data["Person_id"];
			}
			if (!empty($data["Diag_id"]) && $data["Diag_id"] > 0) {
				$setAdd .= ", Diag_uid = :Diag_uid";
				$params["Diag_uid"] = $data["Diag_id"];
			}
			if (!empty($data["CallPovod_id"]) && $data["CallPovod_id"] > 0) {
				$setAdd .= ", CmpReason_id = :CmpReason_id";
				$params["CmpReason_id"] = $data["CallPovod_id"];
			}
			if (!empty($data["CmpReasonNew_id"]) && $data["CmpReasonNew_id"] > 0) {
				$setAdd .= ", CmpReasonNew_id = :CmpReasonNew_id";
				$params["CmpReasonNew_id"] = $data["CmpReasonNew_id"];
			}
			if (!empty($data["Lpu_hid"]) && $data["Lpu_hid"] > 0) {
				$setAdd .= ", Lpu_hid = :Lpu_hid";
				$params["Lpu_hid"] = $data["Lpu_hid"];
			}
			if (!empty($data["MedPersonal_id"]) && $data["MedPersonal_id"] > 0) {
				$setAdd .= ", MedPersonal_id = :MedPersonal_id";
				$params["MedPersonal_id"] = $data["MedPersonal_id"];
			}
			if (!empty($data["MedStaffFact_id"]) && $data["MedStaffFact_id"] > 0) {
				$setAdd .= ", MedStaffFact_id = :MedStaffFact_id";
				$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
			}
			if (!empty($data["CmpCallCard_IndexRep"]) && $data["CmpCallCard_IndexRep"] > 0) {
				$setAdd .= ", CmpCallCard_IndexRep = :CmpCallCard_IndexRep";
				$params["CmpCallCard_IndexRep"] = $data["CmpCallCard_IndexRep"];
			}
			if (!empty($data["CmpCallCard_isControlCall"]) && $data["CmpCallCard_isControlCall"] > 0) {
				$setAdd .= ", CmpCallCard_isControlCall = :CmpCallCard_isControlCall";
				$params["CmpCallCard_isControlCall"] = $data["CmpCallCard_isControlCall"];
			}
			// Место вызова
			if (!empty($data["CallPlace_id"]) && $data["CallPlace_id"] > 0) {
				if ($data["CallPlace_id"] == "180") $params["CmpPlace_id"] = "3";
				if ($data["CallPlace_id"] == "181") $params["CmpPlace_id"] = "2";
				if ($data["CallPlace_id"] == "182") $params["CmpPlace_id"] = "5";
				if ($data["CallPlace_id"] == "183") $params["CmpPlace_id"] = "4";
				if ($data["CallPlace_id"] == "184") $params["CmpPlace_id"] = "7";
				if ($data["CallPlace_id"] == "185") $params["CmpPlace_id"] = "7";
				if ($data["CallPlace_id"] == "186") $params["CmpPlace_id"] = "7";
				if ($data["CallPlace_id"] == "187") $params["CmpPlace_id"] = "7";
				if ($data["CallPlace_id"] == "188") $params["CmpPlace_id"] = "4";
				if ($data["CallPlace_id"] == "189") $params["CmpPlace_id"] = "4";
				if ($data["CallPlace_id"] == "190") $params["CmpPlace_id"] = "8";
				$setAdd .= ", CmpPlace_id = :CmpPlace_id";
			}
			// Результат вызова
			if (!empty($data["ResultUfa_id"]) && $data["ResultUfa_id"] > 0) {
				if ($data["ResultUfa_id"] == "224") $params["CmpResult_id"] = "21";
				if ($data["ResultUfa_id"] == "225") $params["CmpResult_id"] = "13";
				if ($data["ResultUfa_id"] == "226") $params["CmpResult_id"] = "11";
				if ($data["ResultUfa_id"] == "227") $params["CmpResult_id"] = "26";
				if ($data["ResultUfa_id"] == "228") $params["CmpResult_id"] = "22";
				if ($data["ResultUfa_id"] == "229") {
					$params["LeaveType_id"] = "3";
					$setAdd .= ", LeaveType_id = :LeaveType_id";
					$params["CmpResult_id"] = "25";
				}
				if ($data["ResultUfa_id"] == "230") {
					$params["LeaveType_id"] = "3";
					$setAdd .= ", LeaveType_id = :LeaveType_id";
					$params["CmpResult_id"] = "19";
				}
				if (!empty($data["CallPlace_id"]) && $data["CallPlace_id"] > 0) {
					if ($data["CallPlace_id"] == "231") $params["CmpResult_id"] = "3";
				}
				if ($data["ResultUfa_id"] == "232") $params["CmpResult_id"] = "6";
				if ($data["ResultUfa_id"] == "233") $params["CmpResult_id"] = "4";
				if ($data["ResultUfa_id"] == "234") $params["CmpResult_id"] = "36";
				if ($data["ResultUfa_id"] == "235") {
					$params["LeaveType_id"] = "3";
					$setAdd .= ", LeaveType_id = :LeaveType_id";
					$params["CmpResult_id"] = "24";
				}
				if ($data["ResultUfa_id"] == "236") $params["CmpResult_id"] = "3";
				if ($data["ResultUfa_id"] == "237") $params["CmpResult_id"] = "7";
				if ($data["ResultUfa_id"] == "238") $params["CmpResult_id"] = "5";
				if ($data["ResultUfa_id"] == "239") $params["CmpResult_id"] = "21";
				$setAdd .= ", CmpResult_id = :CmpResult_id";
			}
			$query = "
				update CmpCallCard
				set CmpCallCard_updDT = dbo.tzGetDate(){$setAdd}
				where CmpCallCard_id = :CmpCallCard_id
			";
			$callObject->db->query($query, $params);
			$result = $callObject->swUpdate("CmpCallCard", $params);
			return $callObject->isSuccessful($result);
		}
		return false;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function validDiagFinance(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["Diag_id"]) || empty($data["PayType_id"])) {
			return true;
		}
		//если тип оплаты не ОМС, то проверять ничего не нужно
		$PayType_id = $callObject->getFirstResultFromQuery("select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1");
		if ($PayType_id != $data["PayType_id"]) {
			return true;
		}
		//проверим финансируемость диагноза по ОМС для СМП
		$params = ["Diag_id" => $data["Diag_id"]];
		$DiagFinance_isOmsSmp = $callObject->getFirstResultFromQuery("select DiagFinance_isOmsCmp from v_DiagFinance where Diag_id=:Diag_id limit 1", $params);
		return $DiagFinance_isOmsSmp == "2";
	}

	/**
	 * Создание структуры дерева
	 */
	public static function createDecigionTree(CmpCallCard_model $callObject, $data){

        $queryTreeRoot = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            AmbulanceDecigionTreeRoot_id as \"AmbulanceDecigionTreeRoot_id\"
        from p_AmbulanceDecigionTreeRoot_ins
            (
                AmbulanceDecigionTreeRoot_id := :AmbulanceDecigionTreeRoot_id,
				Lpu_id := :Lpu_id,
				LpuBuilding_id := :LpuBuilding_id,
				Region_id := :Region_id,
				pmUser_id := :pmUser_id
            )";


		$resultTreeRootQuery = $callObject->db->query($queryTreeRoot, array(
			'AmbulanceDecigionTreeRoot_id' => null,
			'Lpu_id' =>  !empty($data['Lpu_id']) ? $data['Lpu_id']: null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
			'Region_id' => $callObject->getRegionNumber(),
			'pmUser_id'=> $data['pmUser_id']
		));

		$resultTreeRoot = $resultTreeRootQuery ->result( 'array' )[0];


        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            AmbulanceDecigionTree_id as \"AmbulanceDecigionTree_id\"
        from p_AmbulanceDecigionTree_ins
            (
 				    AmbulanceDecigionTree_id := :AmbulanceDecigionTree_id,
					AmbulanceDecigionTree_nodeid := :AmbulanceDecigionTree_nodeid,
					AmbulanceDecigionTree_nodepid := :AmbulanceDecigionTree_nodepid,
					AmbulanceDecigionTree_Type := :AmbulanceDecigionTree_Type,
					AmbulanceDecigionTree_Text := :AmbulanceDecigionTree_Text,
					Lpu_id := :Lpu_id,
					AmbulanceDecigionTreeRoot_id := :AmbulanceDecigionTreeRoot_id,
					LpuBuilding_id := :LpuBuilding_id,
					pmUser_id := :pmUser_id
            )";



		$result = $callObject->db->query($query, array(
			'AmbulanceDecigionTree_id' => null,
			'AmbulanceDecigionTree_nodeid' => 1,
			'AmbulanceDecigionTree_nodepid' => 1,
			'AmbulanceDecigionTree_Type' => '1',
			'AmbulanceDecigionTree_Text' => 'ЧТО СЛУЧИЛОСЬ? БОЛЬНОЙ В СОЗНАНИИ?',
			'AmbulanceDecigionTreeRoot_id' => $resultTreeRoot['AmbulanceDecigionTreeRoot_id'],
			'pmUser_id' => $data['pmUser_id'],
			//Для поддержки старого фукнционала, после полного перехода - выпилить
			'Lpu_id' =>  isset($data['Lpu_id']) ? $data['Lpu_id']: null,
			'LpuBuilding_id' => isset($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
			'Region_id' => $callObject->getRegionNumber()
		));

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}

	}

	/**
     * Копирование структуры дерева
     */
	public static function copyDecigionTree(CmpCallCard_model $callObject, $data){
		$tree = $callObject->getConcreteDecigionTree($data);

        $queryTreeRoot = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\",
                AmbulanceDecigionTreeRoot_id as \"AmbulanceDecigionTreeRoot_id\"
            from  p_AmbulanceDecigionTreeRoot_ins
                (
 				    AmbulanceDecigionTreeRoot_id := :AmbulanceDecigionTreeRoot_id,
				    Lpu_id := :Lpu_id,
				    LpuBuilding_id := :LpuBuilding_id,
				    Region_id := :Region_id,
				    pmUser_id := :pmUser_id
                )";


		$resultTreeRootQuery = $callObject->db->query($queryTreeRoot, array(
			'AmbulanceDecigionTreeRoot_id' => null,
			'Lpu_id' =>  !empty($data['Lpu_id']) ? $data['Lpu_id']: null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
			'Region_id' => $callObject->getRegionNumber(),
			'pmUser_id'=> $data['pmUser_id']
		));

		$resultTreeRoot = $resultTreeRootQuery ->result( 'array' )[0];

		foreach($tree as $value){

            $query = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\",
                    AmbulanceDecigionTree_id as \"AmbulanceDecigionTree_id\"
                from p_AmbulanceDecigionTree_ins
                    (
 					AmbulanceDecigionTree_id := :AmbulanceDecigionTree_id,
					AmbulanceDecigionTree_nodeid := :AmbulanceDecigionTree_nodeid,
					AmbulanceDecigionTree_nodepid := :AmbulanceDecigionTree_nodepid,
					AmbulanceDecigionTree_Type := :AmbulanceDecigionTree_Type,
					AmbulanceDecigionTree_Text := :AmbulanceDecigionTree_Text,
					CmpReason_id := :CmpReason_id,
					Lpu_id := :Lpu_id,
					AmbulanceDecigionTreeRoot_id := :AmbulanceDecigionTreeRoot_id,
					LpuBuilding_id := :LpuBuilding_id,
					pmUser_id := :pmUser_id
                    )";


			$result = $callObject->db->query($query, array(
				'AmbulanceDecigionTree_id' => null,
				'AmbulanceDecigionTree_nodeid' => $value['AmbulanceDecigionTree_nodeid'],
				'AmbulanceDecigionTree_nodepid' => $value['AmbulanceDecigionTree_nodepid'],
				'AmbulanceDecigionTree_Type' => $value['AmbulanceDecigionTree_Type'],
				'AmbulanceDecigionTree_Text' => $value['AmbulanceDecigionTree_Text'],
				'CmpReason_id'=> !empty($value['CmpReason_id']) ? $value['CmpReason_id']:null,
				'AmbulanceDecigionTreeRoot_id' => $resultTreeRoot['AmbulanceDecigionTreeRoot_id'],
				'pmUser_id' => $data['pmUser_id'],
				//Для поддержки старого фукнционала, после полного перехода - выпилить
				'Lpu_id' =>  isset($data['Lpu_id']) ? $data['Lpu_id']: null,
				'LpuBuilding_id' => isset($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
				'Region_id' => $callObject->getRegionNumber()
			));
		}


		return $result;
	}	
	
}