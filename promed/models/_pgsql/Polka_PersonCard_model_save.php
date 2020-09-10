<?php

class Polka_PersonCard_model_save
{
	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @param bool $api
	 * @return array|bool
	 * @throws Exception
	 */
	public static function savePersonCard(Polka_PersonCard_model $callObject, $data, $api = false)
	{
		$lpuRegion_check = 0;
		$query_check_lpuRegion = "
			select LpuRegion_id as \"LpuRegion_id\"
			from LpuRegion 
			where Lpu_id = :Lpu_id
			and LpuRegion_id = :LpuRegion_id
		";
		if (isset($data["LpuRegion_id"]) && $data["LpuRegion_id"] > 0) {
			$quury_check_lpuRegionParams = [
				"Lpu_id" => $data["Lpu_id"],
				"LpuRegion_id" => $data["LpuRegion_id"]
			];
			$result_check_lpuRegion = $callObject->db->query($query_check_lpuRegion, $quury_check_lpuRegionParams);
			if (is_object($result_check_lpuRegion)) {
				$result_check_lpuRegion = $result_check_lpuRegion->result("array");
				if (count($result_check_lpuRegion) > 0)
					$lpuRegion_check = 1;
			}
			if ($lpuRegion_check == 0) {
				$callObject->rollbackTransaction();
				throw new Exception("Прикрепление невозможно. Данный участок не относится к выбранной Вами МО.");
			}
		}
		if (!$api && $data["PersonCard_id"] > 0) {//Если открываем карту на изменение
			if ($data["PersonCardAttach_id"] > 0) {
				$params = [
					"PersonCard_id" => $data["PersonCard_id"],
					"PersonCard_Code" => $data["PersonCard_Code"],
					"LpuRegion_id" => $data["LpuRegion_id"],
					"LpuRegion_Fapid" => $data["LpuRegion_Fapid"],
					"LpuAttachType_id" => $data["LpuAttachType_id"],
					"PersonCard_begDate" => $data["PersonCard_begDate"],
					"PersonCard_endDate" => $data["PersonCard_endDate"]
				];
				$sql = "
					select
						Server_id as \"Server_id\",
						PersonCard_id as \"PersonCard_id\",
						Person_id as \"Person_id\",
						Lpu_id as \"Lpu_id\",
						LpuRegion_id as \"LpuRegion_id\",
						LpuAttachType_id as \"LpuAttachType_id\",
						PersonCard_Code as \"PersonCard_Code\",
						PersonCard_begDate as \"PersonCard_begDate\",
						PersonCard_endDate as \"PersonCard_endDate\",
						CardCloseCause_id as \"CardCloseCause_id\",
						PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
						PersonCard_IsAttachAuto as \"PersonCard_IsAttachAuto\",
						PersonCard_AttachAutoDT as \"PersonCard_AttachAutoDT\",
						pmUser_insID as \"pmUser_insID\",
						pmUser_updID as \"pmUser_updID\",
						PersonCard_insDT as \"PersonCard_insDT\",
						PersonCard_updDT as \"PersonCard_updDT\",
						PersonCard_DmsPolisNum as \"PersonCard_DmsPolisNum\",
						PersonCard_DmsBegDate as \"PersonCard_DmsBegDate\",
						PersonCard_DmsEndDate as \"PersonCard_DmsEndDate\",
						OrgSMO_id as \"OrgSMO_id\",
						PersonCardAttach_id as \"PersonCardAttach_id\",
						LpuRegion_fapid as \"LpuRegion_fapid\",
						LpuRegionType_id as \"LpuRegionType_id\",
						MedStaffFact_id as \"MedStaffFact_id\"
					from PersonCard 
					where PersonCard_id = :PersonCard_id
					  and PersonCard_Code = :PersonCard_Code
					  and LpuRegion_id = :LpuRegion_id
					  and LpuRegion_fapid = :LpuRegion_Fapid
					  and LpuAttachType_id = :LpuAttachType_id
					  and PersonCardAttach_id is null
					  and coalesce(to_char(PersonCard_begDate ,'{$callObject->dateTimeFormUnixDate}'), '') = coalesce(:PersonCard_begDate,'')
					  and coalesce(to_char(PersonCard_endDate ,'{$callObject->dateTimeFormUnixDate}'), '') = coalesce(:PersonCard_endDate,'')
				";
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($sql, $params);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$queryParams = [
							"PersonCard_id" => $resp[0]["PersonCard_id"],
							"PersonCardAttach_id" => $data["PersonCardAttach_id"]
						];
						$query = "
							update PersonCard
							set PersonCardAttach_id = :PersonCardAttach_id
							where PersonCard_id = :PersonCard_id
						";
						$result = $callObject->db->query($query, $queryParams);
						if ($result) {
							$callObject->commitTransaction();
							return [["PersonCard_id" => $queryParams["PersonCard_id"], "Error_Msg" => ""]];
						}
					}
				}
			}
		}
		if (in_array($data['LpuAttachType_id'], array(1,2,3)) && empty($data['LpuRegionType_id']) && $callObject->regionNick != 'vologda') {
			return array(array('Error_Code' => 1, 'Error_Msg' => 'Не определен тип участка прикрепления'));
		}
		if (in_array($data['LpuAttachType_id'], array(1)) && empty($data['LpuRegion_id']) && $callObject->regionNick != 'vologda') {
			return array(array('Error_Code' => 1, 'Error_Msg' => 'Не определен участок прикрепления'));
		}
		// стартуем транзакцию
		$callObject->beginTransaction();
		$pc_id = null;
		$LpuRegion_fapid = null;
		$PersonCard_idFilter = "";
		$queryParams = [];
		if (isset($data["PersonCard_id"])) {
			$PersonCard_idFilter = " and PC_all.PersonCard_id <> :PersonCard_id";
			$queryParams["PersonCard_id"] = $data["PersonCard_id"];
		}
		$queryParams["Person_id"] = $data["Person_id"];
		$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
		$sql = "
			select
				PC_all.PersonCard_id as \"PersonCard_id\",
				PC_all.Person_id as \"Person_id\",
				PC_all.Server_id as \"Server_id\",
				PC_all.Lpu_id as \"Lpu_id\",
				PC_all.LpuRegion_id as \"LpuRegion_id\",
				PC_all.LpuRegion_fapid as \"LpuRegion_fapid\",
				PC_all.LpuAttachType_id as \"LpuAttachType_id\",
				PC_all.PersonCard_Code as \"PersonCard_Code\",
				PC_all.PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
				to_char(PC_all.PersonCard_begDate ,'{$callObject->dateTimeFormUnixDate}') as \"PersonCard_begDate\",
				PC_all.CardCloseCause_id as \"CardCloseCause_id\",
				PC_all.PersonCardAttach_id as \"PersonCardAttach_id\"
			from v_PersonCard_all PC_all
			where PC_all.Person_id = :Person_id
			  and PC_all.LpuAttachType_id = :LpuAttachType_id
			  and (PC_all.PersonCard_endDate is null or PC_all.PersonCard_endDate::date > tzgetdate()::date)
			  {$PersonCard_idFilter}
		";
		$result = $callObject->db->query($sql, $queryParams);
		$checkPolisChanged = false;
		$CardCloseCause_new = null;
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			throw new Exception("Не удалось проверить наличие уже существующего прикрепления, попробуйте сохранить еще раз.");
		}
		$sel = $result->result('array');
		if (count($sel) > 0) {
			if (($sel[0]["PersonCard_begDate"] == date('Ymd')) && !in_array($queryParams['LpuAttachType_id'], [4])) {
				$callObject->rollbackTransaction();
				throw new Exception("Новое прикрепление пациента можно добавлять не чаще одного раза в день. Если пациент прикреплен к Вашему ЛПУ, то прикрепление может быть удалено или изменен участок только в течение даты прикрепления.");
			}
			if (($sel[0]["Lpu_id"] == $data["Lpu_id"]) && (empty($data["PersonCard_id"]))) {
				if ($sel[0]["LpuRegion_id"] == $data["LpuRegion_id"]) {
					if ($callObject->getRegionNick() != "perm" && !in_array($queryParams["LpuAttachType_id"], [4])) {
						if ($sel[0]["PersonCard_IsAttachCondit"] != 2) {
							$callObject->rollbackTransaction();
							throw new Exception("Пациент уже прикреплен к данному участку.");
						}
					} else {
						if (!empty($data["LpuRegion_Fapid"]) && !empty($sel[0]["LpuRegion_fapid"]) && $data["LpuRegion_Fapid"] == $sel[0]["LpuRegion_fapid"]) {
							if ($callObject->getRegionNick() != "perm" && $callObject->getRegionNick() != "penza") {
								if ($sel[0]["PersonCard_IsAttachCondit"] != 2 && !in_array($queryParams["LpuAttachType_id"], [4])) {
									$callObject->rollbackTransaction();
									throw new Exception("Пациент уже прикреплен к данному участку.");
								}
							} else {
								$checkPolisChanged = true;
							}
						}
					}

				}
			}
			$pc_id = $sel[0]["PersonCard_id"];
			if ($checkPolisChanged) {
				$CardCloseCause_new = 8;
				$query_change = "
	        		select PP.PersonPolis_id as \"PersonPolis_id\"
	        		from v_PersonPolis PP
	        		where PP.Person_id = :Person_id
	        		  and PP.PersonPolis_begDT > :begDate
	        	";
				$query_changeParams = [
					"Person_id" => $data["Person_id"],
					"begDate" => $sel[0]["PersonCard_begDate"]
				];
				/**@var CI_DB_result $result_change */
				$result_change = $callObject->db->query($query_change, $query_changeParams);
				if (is_object($result_change)) {
					$result_change = $result_change->result('array');
					if (count($result_change) > 0) {
						$CardCloseCause_new = 10;
					}
				}
			}
		}
		if (isset($data["PersonCard_id"]) && !in_array($queryParams["LpuAttachType_id"], [4])) {
			$sql = "
				select LpuRegion_id as \"LpuRegion_id\"
				from v_PersonCard_all
				where Person_id = :Person_id
				  and LpuAttachType_id = :LpuAttachType_id
				  and PersonCard_id <> :PersonCard_id
				  and (PersonCard_endDate is null or PersonCard_endDate::date > tzgetdate()::date)
				order by PersonCard_begDate desc
				limit 1
			";
			$sqlParams = [
				"Person_id" => $data["Person_id"],
				"LpuAttachType_id" => $data["LpuAttachType_id"],
				"PersonCard_id" => $data["PersonCard_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$sel = $result->result('array');
				if (count($sel) > 0) {
					if ($sel[0]["LpuRegion_id"] == $data["LpuRegion_id"]) {
						$callObject->rollbackTransaction();
						throw new Exception("Пациент прикреплен к данному участку в предыдущей карте.");
					}
				}
			}
		}
		$allowCheckLpuRegion = (isset($data["PersonCard_id"]) && !in_array($data["LpuAttachType_id"], [4]));
		if (isset($data["PersonCard_id"])) {
			$sql = "
				select
					pc.PersonCard_id as \"PersonCard_id\",
					pc.Person_id as \"Person_id\",
					pc.Server_id as \"Server_id\",
					pc.Lpu_id as \"Lpu_id\",
					pc.LpuRegionType_id as \"LpuRegionType_id\",
					pc.LpuRegion_id as \"LpuRegion_id\",
					pc.LpuRegion_fapid as \"LpuRegion_fapid\",
					pc.LpuAttachType_id as \"LpuAttachType_id\",
					pc.PersonCard_Code as \"PersonCard_Code\",
					to_char(pc.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
					to_char(pc.PersonCardBeg_insDT, '{$callObject->dateTimeForm104}') as \"PersonCardBeg_insDT\",
					to_char(pc.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
					p.Person_IsBDZ as \"Person_IsBDZ\",
					pc.PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
					pc.CardCloseCause_id as \"CardCloseCause_id\",
					pc.PersonCardAttach_id as \"PersonCardAttach_id\",
					coalesce(pc.PersonCard_IsAttachAuto, null) as \"PersonCard_IsAttachAuto\"
				from
					v_PersonCard_all pc 
					INNER JOIN LATERAL(select * from v_PersonState_all p where  p.person_id = pc.person_id limit 1) p on true
				where pc.PersonCard_id = :PersonCard_id
			";
			$sqlParams = ["PersonCard_id" => $data["PersonCard_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$sel = $result->result('array');
				if (count($sel) > 0) {
					$is_change_only_personcard_code = false;
					$is_change_other = false;
					$is_auto_attach = false;
					if ($callObject->getRegionNick() != "ekb") {
						$data["PersonCard_begDate"] = ConvertDateFormat($sel[0]["PersonCard_begDate"]);
					}
					if ($sel[0]["PersonCard_Code"] != $data["PersonCard_Code"]) {
						$is_change_only_personcard_code = true;
					}
					if ($sel[0]["LpuRegion_id"] == $data["LpuRegion_id"] && $sel[0]["LpuRegionType_id"] == $data["LpuRegionType_id"]) {
						$allowCheckLpuRegion = false;
					} else {
						$is_change_other = true;
						$is_change_only_personcard_code = false;
					}
					if ((empty($sel[0]["Person_IsBDZ"]) || isSuperadmin() || ($callObject->getRegionNick() == "ufa" && isLpuAdmin($data["Lpu_id"])) || ($callObject->getRegionNick() != "perm" && havingGroup("CardCloseUser"))) && !empty($data["PersonCard_endDate"])) {
						$is_change_other = true;
						$is_change_only_personcard_code = false;
						$data["CardCloseCause_id"] = (empty($data["CardCloseCause_id"]) ? null : $data["CardCloseCause_id"]);
					}
					if (($sel[0]["PersonCard_Code"] == $data["PersonCard_Code"]) && ($sel[0]["LpuRegionType_id"] == $data["LpuRegionType_id"]) && ($sel[0]["LpuRegion_id"] == $data["LpuRegion_id"]) && ($sel[0]["LpuAttachType_id"] == $data["LpuAttachType_id"])) {
						$is_change_other = false;
					}
					$is_change_attach_id = ($sel[0]["PersonCardAttach_id"] == $data["PersonCardAttach_id"]) ? false : true;
					$is_change_endDate = false;
					if ($sel[0]["PersonCard_endDate"] <> $data["PersonCard_endDate"]) {
						$is_change_endDate = true;
					}
					$is_change_begDate = false;
					if (ConvertDateFormat($sel[0]["PersonCard_begDate"]) <> $data["PersonCard_begDate"] && ($callObject->getRegionNick() == "ekb"))
						$is_change_begDate = true;
					$is_change_fap = false;
					if ($sel[0]["LpuRegion_fapid"] <> $data["LpuRegion_Fapid"]) {
						$is_change_fap = true;
					}
					if ($sel[0]["PersonCard_IsAttachAuto"] && $sel[0]["PersonCard_IsAttachAuto"] == 2 && $callObject->getRegionNick() == "astra")
						$is_auto_attach = true;
					if (!$is_change_only_personcard_code && !$is_change_other && !$is_change_attach_id && !$is_change_endDate && !$is_change_fap && !$is_change_begDate && !$is_auto_attach) {
						$callObject->rollbackTransaction();
						return [["PersonCard_id" => $sel[0]["PersonCard_id"], "success" => true, "Error_Code" => null, "Error_Msg" => ""]];
					}
					if (!$is_change_only_personcard_code && $is_change_other && !$is_auto_attach) {
						if ($sel[0]["PersonCardBeg_insDT"] != date("d.m.Y") && !in_array($queryParams["LpuAttachType_id"], [4]) && empty($data["PersonCard_endDate"])) {
							$callObject->rollbackTransaction();
							throw new Exception("Редактирование карты возможно только в течение даты открытия.");
						}
					}
				}
			}
		} else {
			if (empty($data["ignorePersonDead"]) || !$data["ignorePersonDead"]) {
				$sql = "
					SELECT  P.Person_id as \"Person_id\"
					FROM v_Person P 
					WHERE P.Person_id = :Person_id
					  and (P.Person_IsDead = 2 or p.Person_deadDT is not null)
					limit 1
				";
				$sqlParams = ["Person_id" => $data["Person_id"]];
				$result = $callObject->db->query($sql, $sqlParams);
				if (is_object($result)) {
					$sel = $result->result('array');
					if (count($sel) > 0) {
						$callObject->rollbackTransaction();
						throw new Exception("Пациент умер, прикрепление невозможно!");
					}
				}
			}
		}
		if ($data["action"] != "edit" && $allowCheckLpuRegion) {
			$sql = "
				select LpuRegion_id as \"LpuRegion_id\"
				from v_PersonCard_all 
				where Person_id = :Person_id
				  and LpuAttachType_id = :LpuAttachType_id
				  and PersonCard_id <> :PersonCard_id
				order by PersonCard_begDate desc
				limit 1
			";
			$sqlParams = [
				"Person_id" => $data["Person_id"],
				"LpuAttachType_id" => $data["LpuAttachType_id"],
				"PersonCard_id" => $data["PersonCard_id"]
			];
			$result = $callObject->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$sel = $result->result('array');
				if (count($sel) > 0) {
					if ($sel[0]["LpuRegion_id"] == $data["LpuRegion_id"]) {
						$callObject->rollbackTransaction();
						throw new Exception("Пациент уже прикреплен к данному участку в предыдущей карте.");
					}
				}
			}
		}
		if (isset($data["PersonCard_id"]) && $data["PersonCard_id"] > 0) {
			$procedure = "p_PersonCard_upd";
			if (!empty($data['PersonCard_endDate'])) //Если закрываем прикрепление
			{
				$sqlParams = [
					"PersonCard_Code" => $data["PersonCard_Code"],
					"PersonCard_id" => $data["PersonCard_id"]
				];
				$query_upd_pc = "
					update PersonCard
					set PersonCard_Code = :PersonCard_Code
					where PersonCard_id = :PersonCard_id
				";
				$callObject->db->query($query_upd_pc, $sqlParams);
				$query_upd_pc = "
					update PersonCardState
					set PersonCardState_Code = :PersonCard_Code
					where PersonCard_id = :PersonCard_id
				";
				$callObject->db->query($query_upd_pc, $sqlParams);
				if (!empty($data["PersonCardAttach_id"])) {
					$sqlParams = [
						"PersonCardAttach_id" => $data["PersonCardAttach_id"],
						"PersonCard_id" => $data["PersonCard_id"]
					];
					$query_upd_pc = "
						update PersonCard
						set PersonCardAttach_id = :PersonCardAttach_id
						where PersonCard_id = :PersonCard_id
					";
					$callObject->db->query($query_upd_pc, $sqlParams);
					$query_upd_pc = "
						update PersonCardState
						set PersonCardAttach_id = :PersonCardAttach_id
						where PersonCard_id = :PersonCard_id
					";
					$callObject->db->query($query_upd_pc, $sqlParams);
				}
			}
		} else {
			$procedure = (isset($pc_id) && $pc_id > 0) ? "p_PersonCard_upd" : "p_PersonCard_ins";
			$data["PersonCard_id"] = (isset($pc_id) && $pc_id > 0) ? $pc_id : null;
		}
		if ($data["action"] == "add" && getRegionNick() != "kz") {
			$query_check_date = "
				select 
					to_char(PersonCard_begDate, 'YYYY-MM-DD\"T\"HH24:MI:SS') as \"PersonCard_begDate\",
					COALESCE(to_char(PersonCard_endDate, 'YYYY-MM-DD\"T\"HH24:MI:SS'),'') as \"PersonCard_endDate\"
				from v_PersonCard
				where LpuAttachType_id = :LpuAttachType_id
				  and Person_id = :Person_id
				order by PersonCard_begDate desc
				limit 1
			";
			$sqlParams = [
				"LpuAttachType_id" => $data["LpuAttachType_id"],
				"Person_id" => $data["Person_id"]
			];
			$result_check_date = $callObject->db->query($query_check_date, $sqlParams);
			if (is_object($result_check_date)) {
				$result_check_date = $result_check_date->result("array");
				if (is_array($result_check_date) && count($result_check_date) > 0) {
					$date_checked = (isset($result_check_date[0]["PersonCard_endDate"]) && $result_check_date[0]["PersonCard_endDate"] <> "")
						? $result_check_date[0]["PersonCard_endDate"]
						: $result_check_date[0]["PersonCard_begDate"];
					if ($data["PersonCard_begDate"] < $date_checked) {
						$callObject->rollbackTransaction();
						throw new Exception("Дата начала должна быть позднее " . date("d.m.Y", strtotime($date_checked)), 8);
					}
				}
			}
		}
		$queryParams = [];
		if ($data["LpuAttachType_id"] == 4 && $data["action"] == "add") {
			$procedure = "p_PersonCard_ins";
			$data["PersonCard_id"] = null;
			$query_check_slug = "
				select PersonCard_id as \"PersonCard_id\"
				from v_PersonCard 
				where Person_id = :Person_id
				  and Lpu_id = :Lpu_id
				  and LpuAttachType_id = 4
				limit 1
			";
			$params_check_slug = [
				"Person_id" => $data["Person_id"],
				"Lpu_id" => $data["Lpu_id"]
			];
			/**@var CI_DB_result $result_check_slug */
			$result_check_slug = $callObject->db->query($query_check_slug, $params_check_slug);
			if (is_object($result_check_slug)) {
				$result_check_slug = $result_check_slug->result('array');
				if (count($result_check_slug) > 0) {
					$data["PersonCard_id"] = $result_check_slug[0]["PersonCard_id"];
					$procedure = "p_PersonCard_upd";
				}
			}
		}

		$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['Server_id'] = $data['Server_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['PersonCard_begDate'] = $data['PersonCard_begDate'];
		$queryParams['PersonCard_endDate'] = !empty($data['PersonCard_endDate'])?$data['PersonCard_endDate']:null;
		$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		$queryParams['PersonCard_IsAttachCondit'] = !empty($data['setIsAttachCondit'])?$data['setIsAttachCondit']:1;//После редактирования карты с условным прикреплением она должна перестать быть условной.
		$queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
		$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		$queryParams['MedStaffFact_id'] = !empty($data['MedStaffFact_id'])?$data['MedStaffFact_id']:null;
		$queryParams['LpuRegion_fapid'] = !empty($data['LpuRegion_Fapid'])?$data['LpuRegion_Fapid']:null;
		$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
		$queryParams['CardCloseCause_id'] = !empty($data['CardCloseCause_id'])?$data['CardCloseCause_id']:null;
		$queryParams['PersonCardAttach_id'] = !empty($data['PersonCardAttach_id'])?$data['PersonCardAttach_id']:null;
		$queryParams['PersonCard_IsAttachAuto'] = !empty($data['PersonCard_IsAttachAuto'])?$data['PersonCard_IsAttachAuto']:null;
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		$select = "
	        PersonCard_id as \"PersonCard_id\",
	        error_code as \"Error_Code\",
	        error_message as \"Error_Msg\"
        ";
		if ($procedure == "p_PersonCard_upd") {
			$select = "
	            coalesce(PersonCard_nid, :PersonCard_id) as \"PersonCard_id\",
	            PersonCard_cid as \"cid\",
	            error_code as \"Error_Code\",
	            error_message as \"Error_Msg\"
            ";
		}
		if ($checkPolisChanged) {
			$queryParams["CardCloseCause_id"] = $CardCloseCause_new;
			$select .= ", 1 as \"disable_print\" ";
		} else {
			if ($callObject->getRegionNick() == "astra" && $data["LpuAttachType_id"] == 1) {
				$select .= ", 1 as \"disable_print\" ";
			} else {
				$select .= ", 0 as \"disable_print\" ";
			}
		}
		$query = "
	        select {$select}
	        from {$procedure}(
                PersonCard_id := :PersonCard_id, 
                Lpu_id := :Lpu_id,
                Server_id := :Server_id,
                Person_id := :Person_id,
                PersonCard_begDate := :PersonCard_begDate,
                PersonCard_endDate := :PersonCard_endDate,
                PersonCard_Code := :PersonCard_Code,
                PersonCard_IsAttachAuto := :PersonCard_IsAttachAuto,
				PersonCard_IsAttachCondit := :PersonCard_IsAttachCondit,
				LpuRegionType_id := :LpuRegionType_id,
                LpuRegion_id := :LpuRegion_id,
                MedStaffFact_id := :MedStaffFact_id,
                LpuRegion_fapid := :LpuRegion_fapid,
				LpuAttachType_id := :LpuAttachType_id,
                CardCloseCause_id := :CardCloseCause_id,
				PersonCardAttach_id := :PersonCardAttach_id,
                pmUser_id := :pmUser_id
	        )
        ";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			$callObject->rollbackTransaction();
			return false;
		}
		$sel = $result->result('array');
		if (strlen($sel[0]["Error_Msg"]) > 0) {
			$callObject->rollbackTransaction();
			return $sel;
		}
		$callObject->commitTransaction();
		$query_get_PACL = "
			select PersonAmbulatCardLink_id as \"PersonAmbulatCardLink_id\"
			from v_PersonAmbulatCardLink
			where PersonCard_id = :PersonCard_id
			limit 1
		";
		$response = $callObject->getFirstRowFromQuery($query_get_PACL, ["PersonCard_id" => $data["PersonCard_id"]]);
		if (isset($data["PersonAmbulatCard_id"]) && $data["PersonAmbulatCard_id"] > 0) {
			if ($procedure == "p_PersonCard_ins" || !isset($response["PersonAmbulatCardLink_id"])) {
				$query = "
					select
                        PersonAmbulatCardLink_id as \"PersonAmbulatCardLink_id\",
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\" 
                    from p_PersonAmbulatCardLink_ins(
						PersonAmbulatCardLink_id => null, 
						PersonAmbulatCard_id => :PersonAmbulatCard_id,
						PersonCard_id => :PersonCard_id,
						pmUser_id => :pmUser_id							
                    )
				";
				$queryParams = [
					"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"],
					"PersonCard_id" => $sel[0]["PersonCard_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$callObject->db->query($query, $queryParams);
				$sql = "
					select Lpu_id as \"Lpu_id\"
					from PersonAmbulatCard
					where PersonAmbulatCard_id = :PersonAmbulatCard_id
				";
				$sqlParams = ["PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"]];
				/**@var CI_DB_result $res */
				$res = $callObject->db->query($sql, $sqlParams);
				$res = $res->result('array');
				if (count($res) > 0 && @$res[0]["Lpu_id"] != @$data["Lpu_id"]) {
					$sql = "
						update PersonAmbulatCard
						set Lpu_id = :Lpu_id
						where PersonAmbulatCard_id = :PersonAmbulatCard_id
					";
					$sqlParams = [
						"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"],
						"Lpu_id" => @$data["Lpu_id"]
					];
					$callObject->db->query($sql, $sqlParams);
				}
			} else {
				$query = "
                    select
                        PersonAmbulatCardLink_id as \"PersonAmbulatCardLink_id\",
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
                    from p_PersonAmbulatCardLink_upd(
                        PersonAmbulatCardLink_id := :PersonAmbulatCardLink_id,
						PersonAmbulatCard_id := :PersonAmbulatCard_id,
						PersonCard_id := :PersonCard_id,
						pmUser_id := :pmUser_id
                    )
				";
				$queryParams = [
					"PersonAmbulatCardLink_id" => $response["PersonAmbulatCardLink_id"],
					"PersonAmbulatCard_id" => $data["PersonAmbulatCard_id"],
					"PersonCard_id" => isset($sel[0]["PersonCard_id"]) ? $sel[0]["PersonCard_id"] : $data["PersonCard_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$callObject->db->query($query, $queryParams);
			}
		}
		return $sel;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonCardLpuRegion(Polka_PersonCard_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"LpuRegionType_id" => $data["LpuRegionType_id"],
			"LpuRegion_id" => $data["LpuRegion_id"],
			"PersonCard_id" => $data["PersonCard_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$query = "
            update PersonCard
            set LpuRegion_id = :LpuRegion_id,
                pmUser_updID=:pmUser_id,
                PersonCard_updDT = getdate()
            where PersonCard_id = :PersonCard_id
        ";
		/**@var CI_DB_result $result_pc */
		$callObject->db->query($query, $params);
		$query = "
			update PersonCardState
			set LpuRegion_id = :LpuRegion_id,
			    pmUser_updID=:pmUser_id,
			    PersonCardState_updDT = getdate()
			where PersonCard_id = :PersonCard_id
		";
		$callObject->db->query($query, $params);
		return [["PersonCard_id" => $params["PersonCard_id"], "Error_Msg" => ""]];
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function savePersonCardDms(Polka_PersonCard_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
	    if (isset($data["PersonCard_id"]) && $data["PersonCard_id"]== 0){ 
			throw new Exception("Неверное значение PersonCard_id");
		}
		if (!isset($data["PersonCard_id"])) {
			$data["PersonCard_id"] = null;
		}
		if (!($data["PersonCard_id"] > 0)) {
			$sql = "
				select count(*) as cnt
				from v_PersonCard 
				where Person_id = :Person_id
				  and LpuAttachType_id = 5
				  and PersonCard_endDate > tzgetdate()
				  and CardCloseCause_id is null
			";
			$sqlParams = ["Person_id" => $data["Person_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			$res = $result->result('array');
			if ($res[0]["cnt"] > 0) {
				throw new Exception("Невозможно создать новое ДМС прикрепление, так как уже существует ДМС прикрепление с действующим договором.", 666);
			}
			$sql = "
				select
					PersonCard_id as \"PersonCard_id\",
					rtrim(PersonCard_Code) as \"PersonCard_Code\",
					Person_id as \"Person_id\",
					Server_id as \"Server_id\",
					LpuAttachType_id as \"LpuAttachType_id\",
					LpuRegionType_id as \"LpuRegionType_id\",
					to_char(PersonCard_begDate,'{$callObject->dateTimeFormUnixDate}') as \"PersonCard_begDate\",					
					to_char(PersonCard_endDate,'{$callObject->dateTimeFormUnixDate}') as \"PersonCard_endDate\",
					CardCloseCause_id as \"CardCloseCause_id\",
					Lpu_id as \"Lpu_id\",
					LpuRegion_id as \"LpuRegion_id\",
					PersonCard_DmsPolisNum as \"PersonCard_DmsPolisNum\",					
					to_char(PersonCard_DmsBegDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_DmsBegDate\",					
					to_char(PersonCard_DmsEndDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_DmsEndDate\",
					OrgSMO_id as \"OrgSMO_id\"
				from v_PersonCard
				where Person_id = :Person_id
				  and LpuAttachType_id = 5
				  and PersonCard_endDate <= tzgetdate()
				  and CardCloseCause_id is null
			";
			$result = $callObject->db->query($sql, $sqlParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка редактирования прикрепления.");
			}
			$res = $result->result('array');
			if (count($res) > 0) {
				$queryParams = [
					"PersonCard_id" => $res[0]["PersonCard_id"],
					"Lpu_id" => $res[0]["Lpu_id"],
					"Server_id" => $res[0]["Server_id"],
					"Person_id" => $res[0]["Person_id"],
					"PersonCard_begDate" => $res[0]["PersonCard_begDate"],
					"PersonCard_endDate" => $res[0]["PersonCard_endDate"],
					"PersonCard_Code" => $res[0]["PersonCard_Code"],
					"LpuRegion_id" => $res[0]["LpuRegion_id"],
					"LpuAttachType_id" => 5,
					"CardCloseCause_id" => 6,
					"PersonCard_DmsPolisNum" => $res[0]["PersonCard_DmsPolisNum"],
					"PersonCard_DmsBegDate" => $res[0]["PersonCard_DmsBegDate"],
					"PersonCard_DmsEndDate" => $res[0]["PersonCard_DmsEndDate"],
					"OrgSMO_id" => $res[0]["OrgSMO_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$sql = "
                    select
	                    PersonCard_id as \"PersonCard_id\",
	                    Error_Code as \"Error_Code\",
	                    Error_Message as \"Error_Msg\"
	                from p_PersonCard_upd(
	                    PersonCard_id := :PersonCard_id, 
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						Person_id := :Person_id,
						PersonCard_begDate := :PersonCard_begDate,
						PersonCard_endDate := :PersonCard_endDate,
						PersonCard_Code := :PersonCard_Code,
						LpuRegion_id := :LpuRegion_id,
						LpuAttachType_id := :LpuAttachType_id,
						CardCloseCause_id := :CardCloseCause_id,
						PersonCard_DmsPolisNum := :PersonCard_DmsPolisNum,
						PersonCard_DmsBegDate := :PersonCard_DmsBegDate,
						PersonCard_DmsEndDate := :PersonCard_DmsEndDate,
						OrgSMO_id := :OrgSMO_id,
						pmUser_id := :pmUser_id					
                    )
				";
				$result = $callObject->db->query($sql, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка редактирования прикрепления.");
				}
			}
			// создаем прикрепление
			$queryParams = [
				"PersonCard_id" => null,
				"Lpu_id" => $data["Lpu_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"],
				"PersonCard_begDate" => $data["PersonCard_begDate"],
				"PersonCard_endDate" => $data["PersonCard_endDate"],
				"PersonCard_Code" => null,
				"PersonCard_DmsPolisNum" => $data["PersonCard_DmsPolisNum"],
				"PersonCard_DmsBegDate" => $data["PersonCard_DmsBegDate"],
				"PersonCard_DmsEndDate" => $data["PersonCard_DmsEndDate"],
				"OrgSMO_id" => $data["OrgSMO_id"],
				"LpuRegion_id" => null,
				"LpuAttachType_id" => 5,
				"CardCloseCause_id" => null,
				"pmUser_id" => $data["pmUser_id"]
			];
			$query = "
	            select
		            PersonCard_id as \"PersonCard_id\",
		            Error_Code as \"Error_Code\",
		            Error_Message as \"Error_Msg\"
	            from p_PersonCard_ins(
	                PersonCard_id => :PersonCard_id, 
					Lpu_id => :Lpu_id,
					Server_id => :Server_id,
					Person_id => :Person_id,
					PersonCard_begDate => :PersonCard_begDate,
					PersonCard_endDate => :PersonCard_endDate,
					PersonCard_Code => :PersonCard_Code,
					PersonCard_DmsPolisNum => :PersonCard_DmsPolisNum,
					PersonCard_DmsBegDate => :PersonCard_DmsBegDate,
					PersonCard_DmsEndDate => :PersonCard_DmsEndDate,
					OrgSMO_id => :OrgSMO_id,
					PersonCard_IsAttachCondit => null,
					LpuRegion_id => :LpuRegion_id,
					LpuAttachType_id => :LpuAttachType_id,
					CardCloseCause_id => :CardCloseCause_id,
					pmUser_id => :pmUser_id
	            )
            ";
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result('array');
		} else {
			$sql = "
				select
					PersonCard_id as \"PersonCard_id\",
					rtrim(PersonCard_Code) as \"PersonCard_Code\",
					Person_id as \"Person_id\",
					Server_id as \"Server_id\",
					LpuAttachType_id as \"LpuAttachType_id\",
					LpuRegionType_id as \"LpuRegionType_id\",					
					to_char(PersonCard_begDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_begDate\",					
					to_char(PersonCard_endDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_endDate\",
					CardCloseCause_id as \"CardCloseCause_id\",
					Lpu_id as \"Lpu_id\",
					LpuRegion_id as \"LpuRegion_id\",
					PersonCard_DmsPolisNum as \"PersonCard_DmsPolisNum\",					
					to_char(PersonCard_DmsBegDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_DmsBegDate\",					
					to_char(PersonCard_DmsEndDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_DmsEndDate\",
					OrgSMO_id as \"OrgSMO_id\"
				from v_PersonCard_all
				where PersonCard_id = :PersonCard_id
			";
			$sqlParams = ["PersonCard_id" => $data["PersonCard_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка редактирования прикрепления.");
			}
			$res = $result->result('array');
			$queryParams = [
				"PersonCard_id" => $res[0]["PersonCard_id"],
				"Lpu_id" => $res[0]["Lpu_id"],
				"Server_id" => $res[0]["Server_id"],
				"Person_id" => $res[0]["Person_id"],
				"PersonCard_begDate" => $res[0]["PersonCard_begDate"],
				"PersonCard_endDate" => $data["PersonCard_endDate"],
				"PersonCard_Code" => $res[0]["PersonCard_Code"],
				"LpuRegion_id" => $res[0]["LpuRegion_id"],
				"LpuAttachType_id" => 5,
				"CardCloseCause_id" => isset($data["CardCloseCause_id"]) && $data["CardCloseCause_id"] ? $data["CardCloseCause_id"] : null,
				"PersonCard_DmsPolisNum" => $data["PersonCard_DmsPolisNum"],
				"PersonCard_DmsBegDate" => $data["PersonCard_DmsBegDate"],
				"PersonCard_DmsEndDate" => $data["PersonCard_DmsEndDate"],
				"OrgSMO_id" => $data["OrgSMO_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$sql = "
                select
	                PersonCard_id as \"PersonCard_id\",
	                Error_Code as \"Error_Code\",
	                Error_Message as \"Error_Msg\"
                from p_PersonCard_upd(
					PersonCard_id := :PersonCard_id,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					Person_id := :Person_id,
					PersonCard_begDate := :PersonCard_begDate,
					PersonCard_endDate := :PersonCard_endDate,
					PersonCard_Code := :PersonCard_Code,
					LpuRegion_id := :LpuRegion_id,
					LpuAttachType_id := :LpuAttachType_id,
					CardCloseCause_id := :CardCloseCause_id,
					PersonCard_IsAttachCondit := null,
					PersonCard_IsAttachAuto := null,
					PersonCard_AttachAutoDT := null,
					PersonCard_DmsPolisNum := :PersonCard_DmsPolisNum,
					PersonCard_DmsBegDate := :PersonCard_DmsBegDate,
					PersonCard_DmsEndDate := :PersonCard_DmsEndDate,
					OrgSMO_id := :OrgSMO_id,
					pmUser_id := :pmUser_id
                )
			";
			$result = $callObject->db->query($sql, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка редактирования прикрепления.");
			}
		}
		$response = $result->result('array');
		return [["PersonCard_id" => $response[0]["PersonCard_id"], "Error_Code" => 0, "Error_Msg" => ""]];
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function SavePersonCardAuto(Polka_PersonCard_model $callObject, $data)
	{
		$query_pers = "
            select coalesce(PS.Person_SurName, '')||' '||coalesce(PS.Person_FirName, '')||' '||coalesce(PS.Person_SecName, '') as \"Person_FIO\"
            from v_PersonState PS
            where Person_id = :Person_id
        ";
		$query_lpu = "
            select Lpu_Nick as \"Lpu_Nick\"
            from v_Lpu 
            where Lpu_id = :Lpu_id
        ";
		$query_lpuregion = "
            select LpuRegion_Name||' ('||LpuRegionType_Name||')' as \"LpuRegion_Name\"
            from v_LpuRegion 
            where LpuRegion_id = :LpuRegion_id
        ";
		$query_lpuregiontype = "
			select LpuRegionType_SysNick as \"LpuRegionType_SysNick\"
			from v_LpuRegionType 
			where LpuRegionType_id = :LpuRegionType_id
		";
		$query_personage = "
			select coalesce(age2(Person_Birthday, tzgetdate()), 0) as \"Person_Age\"
			from v_PersonState 
			where Person_id = :Person_id
		";
		$params = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuRegion_id" => $data["LpuRegion_id"],
			"LpuRegionType_id" => $data["LpuRegionType_id"]
		];
		/**
		 * @var CI_DB_result $pers_name_resp
		 * @var CI_DB_result $lpu_nick_resp
		 * @var CI_DB_result $lpuregion_name_resp
		 * @var CI_DB_result $lpuregion_Fapname_resp
		 * @var CI_DB_result $lpuregion_type
		 * @var CI_DB_result $result_check_lpuRegion
		 * @var CI_DB_result $person_age
		 * @var CI_DB_result $result_check
		 */
		$pers_name_resp = $callObject->db->query($query_pers, $params);
		$lpu_nick_resp = $callObject->db->query($query_lpu, $params);
		$lpuregion_name_resp = $callObject->db->query($query_lpuregion, $params);

		$params["LpuRegion_id"] = $data["LpuRegion_Fapid"];
		$lpuregion_Fapname_resp = $callObject->db->query($query_lpuregion, $params);
		$pers = $pers_name_resp->result('array');
		$lpu = $lpu_nick_resp->result('array');
		$lpuregion = $lpuregion_name_resp->result('array');
		$lpuregionfap = $lpuregion_Fapname_resp->result('array');
		$pers_name = $pers[0]["Person_FIO"];

		$lpuregion_type = $callObject->db->query($query_lpuregiontype, $params);
		$lpuregion_type = $lpuregion_type->result('array');
		$lpuregion_type_nick = $lpuregion_type[0]["LpuRegionType_SysNick"];
		$lpu_nick = $lpu[0]["Lpu_Nick"];
		$lpuregion_name = $lpuregion[0]["LpuRegion_Name"];
		if (in_array($callObject->getRegionNick(), ["perm", "buryatiya", "kareliya", "khak", "krym", "ekb", "ufa", "penza"])) {
			if (count($lpuregionfap) > 0 && isset($lpuregionfap[0]["LpuRegion_Name"]))
				$lpuregion_name .= " ФАП - {$lpuregionfap[0]["LpuRegion_Name"]}";
		}
		$lpuRegion_check = 0;
		$quury_check_lpuRegion = "
			select LpuRegion_id as \"LpuRegion_id\"
			from v_LpuRegion
			where Lpu_id = :Lpu_id
			and LpuRegion_id = :LpuRegion_id
		";
		$sqlParams = [
			"Lpu_id" => $data["Lpu_id"],
			"LpuRegion_id" => $data["LpuRegion_id"]
		];
		$result_check_lpuRegion = $callObject->db->query($quury_check_lpuRegion, $sqlParams);
		if (is_object($result_check_lpuRegion)) {
			$result_check_lpuRegion = $result_check_lpuRegion->result('array');
			if (count($result_check_lpuRegion) > 0) {
				$lpuRegion_check = 1;
			}
		}
		if ($lpuRegion_check == 0) {
			$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - данный участок не относится к выбранной Вами МО.";
			return $personcard_result;
		}
		$person_age = $callObject->db->query($query_personage, $params);
		$person_age = $person_age->result('array');
		$person_age = $person_age[0]["Person_Age"];
		if ($lpuregion_type_nick == "ped" && $person_age >= 18) {
			$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - нельзя прикреплять к педиатрическому участку пациентов 18 лет и старше.";
			return $personcard_result;
		} else if ($lpuregion_type_nick == "ter" && $person_age < 18) {
			$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - нельзя прикреплять к терапевтическому участку пациентов младше 18.";
			return $personcard_result;
		}
		$query_check = "
            select
	            PersonCard_id as \"PersonCard_id\",
	            LpuRegion_id as \"LpuRegion_id\",
	            LpuRegion_fapid as \"LpuRegion_fapid\",
	            PersonCard_begDate as \"PersonCard_begDate\",
	            PersonCard_Code as \"PersonCard_Code\",
	            Lpu_id as \"Lpu_id\"
            from v_PersonCard 
            where Person_id = :Person_id
            and LpuAttachType_id = 1
        ";
		$personcard_result = [];
		$result_check = $callObject->db->query($query_check, $params);
		$PersonAmbulatCard_id = null;
		$change_lpu = 0;
		$checkPolisChanged = false;
		$CardCloseCause_new = null;
		if (!is_object($result_check)) {
			$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - ошибка проверки наличия прикрепления у пациента.";
			return $personcard_result;
		}
        $res_check = $result_check->result('array');
        $date = new DateTime($res_check[0]["PersonCard_begDate"]);
        if (is_array($res_check) && count($res_check) > 0 && isset($res_check[0]["PersonCard_id"])) {
            if ($data["LpuRegion_id"] == $res_check[0]["LpuRegion_id"] && $data["LpuRegion_Fapid"] == $res_check[0]["LpuRegion_fapid"]) {
                if ($callObject->getRegionNick() == "perm" && !empty($res_check[0]["PersonCard_begDate"]) && date_format($date, "Ymd") == date("Ymd")) {
                    $personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - новое прикрепление пациента можно добавлять не чаще одного раза в день.";
                    return $personcard_result;
                } elseif ($callObject->getRegionNick() != "perm") {
                    $personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - пациент уже прикреплен к данному участку.";
                    return $personcard_result;
                } else {
                    $checkPolisChanged = true;
                }
            }
			$personCard_Code = $res_check[0]["PersonCard_Code"];
			if ($res_check[0]["Lpu_id"] != $data["Lpu_id"]) {
				$params_PersonAmbulatCard = [];
				$params_PersonAmbulatCard["PersonAmbulatCard_id"] = null;
				$params_PersonAmbulatCard["Server_id"] = $data["Server_id"];
				$params_PersonAmbulatCard["Person_id"] = $data["Person_id"];
				$params_PersonAmbulatCard["PersonAmbulatCard_Num"] = $callObject->getPersonCardCode($data);
				$params_PersonAmbulatCard["PersonAmbulatCard_Num"] = $params_PersonAmbulatCard["PersonAmbulatCard_Num"][0]["PersonCard_Code"];
				$personCard_Code = $params_PersonAmbulatCard["PersonAmbulatCard_Num"];
				$params_PersonAmbulatCard["Lpu_id"] = $data["Lpu_id"];
				$params_PersonAmbulatCard["PersonAmbulatCard_CloseCause"] = null;
				$params_PersonAmbulatCard["PersonAmbulatCard_endDate"] = null;
				$params_PersonAmbulatCard["pmUser_id"] = $data["pmUser_id"];
				$params_PersonAmbulatCard["tzGetDate"] = $callObject->tzGetDate();

				$query_PersonAmbulatCard = "
                	select
	                    PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
	                    Error_Code as \"Error_Code\",
	                    Error_Message as \"Error_Msg\"
                    from p_PersonAmbulatCard_ins(
                    	Server_id := :Server_id,
                        PersonAmbulatCard_id := :PersonAmbulatCard_id,
                        Person_id := :Person_id,
						PersonAmbulatCard_Num := :PersonAmbulatCard_Num,
                        Lpu_id := :Lpu_id,
                        PersonAmbulatCard_CloseCause := :PersonAmbulatCard_CloseCause,
                        PersonAmbulatCard_endDate := :PersonAmbulatCard_endDate,
                        PersonAmbulatCard_begDate := :tzGetDate,
                        pmUser_id := :pmUser_id
                    )
				";
				/**@var CI_DB_result $result_PersonAmbulatCard */
				$result_PersonAmbulatCard = $callObject->db->query($query_PersonAmbulatCard, $params_PersonAmbulatCard);
				if (!is_object($result_PersonAmbulatCard)) {
					$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - ошибка добавления амбулаторной карты.";
					return $personcard_result;
				}
				$result_PersonAmbulatCard = $result_PersonAmbulatCard->result('array');
				$change_lpu = 1;
				$PersonAmbulatCard_id = $result_PersonAmbulatCard[0]["PersonAmbulatCard_id"];
				$params_PersonAmbulatCardLocat = [];
				$params_PersonAmbulatCardLocat["PersonAmbulatCardLocat_id"] = null;
				$params_PersonAmbulatCardLocat["Server_id"] = $data["Server_id"];
				$params_PersonAmbulatCardLocat["PersonAmbulatCard_id"] = $PersonAmbulatCard_id;
				$params_PersonAmbulatCardLocat["AmbulatCardLocatType_id"] = 1;
				$params_PersonAmbulatCardLocat["MedStaffFact_id"] = null;
				$params_PersonAmbulatCardLocat["PersonAmbulatCardLocat_begDate"] = date("Y-m-d H:i");
				$params_PersonAmbulatCardLocat["PersonAmbulatCardLocat_Desc"] = null;
				$params_PersonAmbulatCardLocat["PersonAmbulatCardLocat_OtherLocat"] = null;
				$params_PersonAmbulatCardLocat["pmUser_id"] = $data["pmUser_id"];
				$query_PersonAmbulatCardLocat = "
                    select
	                	PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\",
	                    Error_Code as \"Error_Code\",
	                    Error_Message as \"Error_Msg\"
                    from p_PersonAmbulatCardLocat_ins(
                        Server_id := :Server_id,
                        PersonAmbulatCardLocat_id := :PersonAmbulatCardLocat_id,
                        PersonAmbulatCard_id := :PersonAmbulatCard_id,
                        AmbulatCardLocatType_id := :AmbulatCardLocatType_id,
                        MedStaffFact_id := :MedStaffFact_id,
                        PersonAmbulatCardLocat_begDate := :PersonAmbulatCardLocat_begDate,
                        PersonAmbulatCardLocat_Desc := :PersonAmbulatCardLocat_Desc,
                        PersonAmbulatCardLocat_OtherLocat := :PersonAmbulatCardLocat_OtherLocat,
                        pmUser_id := :pmUser_id
                    )
				";
				$result_PersonAmbulatCardLocat = $callObject->db->query($query_PersonAmbulatCardLocat, $params_PersonAmbulatCardLocat);
				if (!is_object($result_PersonAmbulatCardLocat)) {
					$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - ошибка добавления движения амбулаторной карты.";
					return $personcard_result;
				}
			}
			if ($checkPolisChanged) {
				$CardCloseCause_new = 8;
				$query_change = "
		        	select PP.PersonPolis_id as \"PersonPolis_id\"
		        	from v_PersonPolis PP
		        	where PP.Person_id = :Person_id
		        	  and PP.PersonPolis_begDT > :begDate
		        ";
				$sqlParams = [
					"Person_id" => $res_check[0]["Person_id"],
					"begDate" => $res_check[0]["PersonCard_begDate"]
				];
				/**@var CI_DB_result $result_change */
				$result_change = $callObject->db->query($query_change, $sqlParams);
				if (is_object($result_change)) {
					$result_change = $result_change->result('array');
					if (count($result_change) > 0) {
						$CardCloseCause_new = 10;
					}
				}
			}
			$upd_params = [];
			$beg_date = date("Y-m-d H:i:00.000");
			$upd_params["PersonCard_id"] = $res_check[0]["PersonCard_id"];
			$upd_params["Lpu_id"] = $data["Lpu_id"];
			$upd_params["Server_id"] = $data["Server_id"];
			$upd_params["Person_id"] = $data["Person_id"];
			$upd_params["PersonCard_IsAttachCondit"] = (isset($data["IsAttachCondit"]) && $data["IsAttachCondit"] == 1) ? 2 : null;
			$upd_params["BegDate"] = $beg_date;
			$upd_params["EndDate"] = null;
			$upd_params["CardCloseCause_id"] = null;
			$upd_params["pmUser_id"] = $data["pmUser_id"];
			$upd_params["PersonCard_Code"] = $personCard_Code;
			$upd_params["LpuRegion_id"] = $data["LpuRegion_id"];
			$upd_params["LpuRegion_Fapid"] = $data["LpuRegion_Fapid"];
			$upd_params["PersonCardAttach_id"] = $data["PersonCardAttach_id"];
			if ($checkPolisChanged) {
				$upd_params["CardCloseCause_id"] = $CardCloseCause_new;
			}
			$sql = "
				select
	                PersonCard_id as \"PersonCard_id\",
	                Error_Code as \"Error_Code\",
	                Error_Message as \"Error_Msg\"
                from p_PersonCard_upd(
                    PersonCard_id := :PersonCard_id,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					Person_id := :Person_id,
					PersonCard_begDate := :BegDate,
					PersonCard_endDate := :EndDate,
					PersonCard_Code := :PersonCard_Code,
					PersonCard_IsAttachCondit := :PersonCard_IsAttachCondit,
					LpuRegion_id := :LpuRegion_id,
					LpuRegion_fapid := :LpuRegion_Fapid,
					LpuAttachType_id := 1,
					CardCloseCause_id := :CardCloseCause_id,
					PersonCardAttach_id := :PersonCardAttach_id,
					pmUser_id := :pmUser_id
                )
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $upd_params);
			$sel = $result->result('array');
			if (strlen($sel[0]["Error_Msg"]) > 0) {
				if ($change_lpu == 1) {
					$params_PersonAmbulatCardLink = [
						"PersonAmbulatCard_id" => $PersonAmbulatCard_id,
						"PersonCard_id" => $sel[0]["PersonCard_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$query_PersonAmbulatCardLink = "
                        select
	                        Error_Code as \"Error_Code\",
	                        Error_Message as \"Error_Msg\"
                        from p_PersonAmbulatCardLink_ins(
                        	PersonAmbulatCardLink_id := null,
                            PersonAmbulatCard_id := :PersonAmbulatCard_id,
                            PersonCard_id := :PersonCard_id,
                            pmUser_id := :pmUser_id
                        )
					";
					$result_PersonAmbulatCardLink = $callObject->db->query($query_PersonAmbulatCardLink, $params_PersonAmbulatCardLink);
					if (!is_object($result_PersonAmbulatCardLink)) {
						$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - ошибка добавления связи амбулаторной карты с прикреплением.";
						return $personcard_result;
					}
				}
				$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - {$sel[0]["Error_Msg"]}.";
				return $personcard_result;
			}
			$personcard_result[0]["string"] = "Пациент {$pers_name} успешно прикреплен к ЛПУ {$lpu_nick} к участку {$lpuregion_name}";
			if ($checkPolisChanged) {
				$personcard_result[0]["string"] .= " prev_params";
			}
			return $personcard_result;
		} else {
			$params_PersonAmbulatCard = [];
			$params_PersonAmbulatCard["PersonAmbulatCard_id"] = null;
			$params_PersonAmbulatCard["Server_id"] = $data["Server_id"];
			$params_PersonAmbulatCard["Person_id"] = $data["Person_id"];
			$params_PersonAmbulatCard["PersonAmbulatCard_Num"] = $callObject->getPersonCardCode($data);
			$params_PersonAmbulatCard["PersonAmbulatCard_Num"] = $params_PersonAmbulatCard["PersonAmbulatCard_Num"][0]["PersonCard_Code"];
			$personCard_Code = $params_PersonAmbulatCard["PersonAmbulatCard_Num"];
			$params_PersonAmbulatCard["Lpu_id"] = $data["Lpu_id"];
			$params_PersonAmbulatCard["PersonAmbulatCard_CloseCause"] = null;
			$params_PersonAmbulatCard["PersonAmbulatCard_endDate"] = null;
			$params_PersonAmbulatCard["pmUser_id"] = $data["pmUser_id"];
			$params_PersonAmbulatCard["tzGetDate"] = $callObject->tzGetDate();
			$query_PersonAmbulatCard = "
                select
	                PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
	                Error_Code as \"Error_Code\",
	                Error_Message as \"Error_Msg\"
                from p_PersonAmbulatCard_ins(
                    Server_id => :Server_id,
                    PersonAmbulatCard_id => :PersonAmbulatCard_id,
                    Person_id => :Person_id,
                    PersonAmbulatCard_Num => :PersonAmbulatCard_Num,
                    Lpu_id => :Lpu_id,
                    PersonAmbulatCard_CloseCause => :PersonAmbulatCard_CloseCause,
                    PersonAmbulatCard_endDate => :PersonAmbulatCard_endDate,
                    PersonAmbulatCard_begDate => :tzGetDate,
                    pmUser_id => :pmUser_id
                )
			";
			/**@var CI_DB_result $result_PersonAmbulatCard */
			$result_PersonAmbulatCard = $callObject->db->query($query_PersonAmbulatCard, $params_PersonAmbulatCard);
			if (!is_object($result_PersonAmbulatCard)) {
				$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - ошибка добавления амбулаторной карты.";
				return $personcard_result;
			}
			$result_PersonAmbulatCard = $result_PersonAmbulatCard->result('array');
			$PersonAmbulatCard_id = $result_PersonAmbulatCard[0]["PersonAmbulatCard_id"];
			$params_PersonAmbulatCardLocat = [
				"PersonAmbulatCardLocat_id" => null,
				"Server_id" => $data["Server_id"],
				"PersonAmbulatCard_id" => $PersonAmbulatCard_id,
				"AmbulatCardLocatType_id" => 1,
				"MedStaffFact_id" => null,
				"PersonAmbulatCardLocat_begDate" => date("Y-m-d H:i"),
				"PersonAmbulatCardLocat_Desc" => null,
				"PersonAmbulatCardLocat_OtherLocat" => null,
				"pmUser_id" => $data["pmUser_id"]
			];
			$query_PersonAmbulatCardLocat = "
                select
                    PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_PersonAmbulatCardLocat_ins(
                	Server_id := :Server_id,
                    PersonAmbulatCardLocat_id := :PersonAmbulatCardLocat_id,
                    PersonAmbulatCard_id := :PersonAmbulatCard_id,
                    AmbulatCardLocatType_id := :AmbulatCardLocatType_id,
                    MedStaffFact_id := :MedStaffFact_id,
                    PersonAmbulatCardLocat_begDate := :PersonAmbulatCardLocat_begDate,
                    PersonAmbulatCardLocat_Desc := :PersonAmbulatCardLocat_Desc,
                    PersonAmbulatCardLocat_OtherLocat := :PersonAmbulatCardLocat_OtherLocat,
                    pmUser_id := :pmUser_id
                )
			";
			/**@var CI_DB_result $result_PersonAmbulatCardLocat */
			$result_PersonAmbulatCardLocat = $callObject->db->query($query_PersonAmbulatCardLocat, $params_PersonAmbulatCardLocat);
			if (!is_object($result_PersonAmbulatCardLocat)) {
				$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - ошибка добавления движения амбулаторной карты.";
				return $personcard_result;
			}
			$ins_params = [
				"Lpu_id" => $data["Lpu_id"],
				"Server_id" => $data["Server_id"],
				"Person_id" => $data["Person_id"],
				"PersonCard_IsAttachCondit" => (isset($data["IsAttachCondit"]) && $data["IsAttachCondit"] == 1) ? 2 : null,
				"PersonCard_begDate" => date("Y-m-d H:i:00.000"),
				"PersonCard_Code" => $personCard_Code,
				"EndDate" => null,
				"pmUser_id" => $data["pmUser_id"],
				"LpuRegion_id" => $data["LpuRegion_id"],
				"LpuRegion_Fapid" => $data["LpuRegion_Fapid"],
				"PersonCardAttach_id" => $data["PersonCardAttach_id"]
			];
			$sql = "
                select
	                PersonCard_id as \"PersonCard_id\",
	                Error_Code as \"Error_Code\",
	                Error_Message as \"Error_Msg\"
                from p_PersonCard_ins(
                    Lpu_id := :Lpu_id,
                    Server_id := :Server_id,
                    Person_id := :Person_id,
                    PersonCard_begDate := :PersonCard_begDate,
                    PersonCard_Code := :PersonCard_Code,
                    PersonCard_IsAttachCondit := :PersonCard_IsAttachCondit,
                    LpuRegion_id := :LpuRegion_id,
                    LpuRegion_fapid := :LpuRegion_Fapid,
                    LpuAttachType_id := 1,
                    CardCloseCause_id := null,
                    PersonCardAttach_id := :PersonCardAttach_id,
                    pmUser_id := :pmUser_id
                )
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $ins_params);
			$sel = $result->result('array');
			if (strlen($sel[0]["Error_Msg"]) > 0) {
				$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - {$sel[0]["Error_Msg"]}.";
				return $personcard_result;
			}
			$params_PersonAmbulatCardLink = [
				"PersonAmbulatCard_id" => $PersonAmbulatCard_id,
				"PersonCard_id" => $sel[0]["PersonCard_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$query_PersonAmbulatCardLink = "
                select
	                Error_Code as \"Error_Code\",
	                Error_Message as \"Error_Msg\"
                from p_PersonAmbulatCardLink_ins(
                	PersonAmbulatCardLink_id := null,
                    PersonAmbulatCard_id := :PersonAmbulatCard_id,
                    PersonCard_id := :PersonCard_id,
                    pmUser_id := :pmUser_id
                )
			";
			$result_PersonAmbulatCardLink = $callObject->db->query($query_PersonAmbulatCardLink, $params_PersonAmbulatCardLink);
			if (!is_object($result_PersonAmbulatCardLink)) {
				$personcard_result[0]["string"] = "Пациент {$pers_name} НЕ ПРИКРЕПЛЕН к ЛПУ {$lpu_nick} к участку {$lpuregion_name}! Причина - ошибка добавления связи амбулаторной карты с прикреплением.";
				return $personcard_result;
			}
			$personcard_result[0]["string"] = "Пациент {$pers_name} успешно прикреплен к ЛПУ {$lpu_nick} к участку {$lpuregion_name}";
			return $personcard_result;
		}
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function savePersonCardAttach(Polka_PersonCard_model $callObject, $data)
	{
		$procedure = (empty($data["PersonCardAttach_id"]) ? "p_PersonCardAttach_ins" : "p_PersonCardAttach_upd");

		$data["PersonCardAttach_IsSMS"] = $data["PersonCardAttach_IsSMS"] + 1;
		$data["PersonCardAttach_IsEmail"] = $data["PersonCardAttach_IsEmail"] + 1;
		$data["PersonCardAttach_SMS"] = str_replace(" ", "", substr($data["PersonCardAttach_SMS"], 3));

		$selectString = "
	        PersonCardAttach_id as \"PersonCardAttach_id\",
	        Error_Code as \"Error_Code\",
	        Error_Message as \"Error_Msg\"
		";
		$query = "
	        select {$selectString}
	        from {$procedure}(
	            PersonCardAttach_id := :PersonCardAttach_id,
				PersonCardAttach_setDate := :PersonCardAttach_setDate,
				Lpu_id := :Lpu_id,
				Lpu_aid := :Lpu_aid,
				Address_id := :Address_id,
				Polis_id := :Polis_id,
				Person_id := :Person_id,
				PersonCardAttach_IsSMS := :PersonCardAttach_IsSMS,
				PersonCardAttach_SMS := :PersonCardAttach_SMS,
				PersonCardAttach_IsEmail := :PersonCardAttach_IsEmail,
				PersonCardAttach_Email := :PersonCardAttach_Email,
				PersonCardAttach_IsHimself := :PersonCardAttach_IsHimself,
				pmUser_id := :pmUser_id
	        )
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result('array');
		$statusTypes = [1, 2];
		foreach ($statusTypes as $statusType) {
			$funcParams = [
				"PersonCardAttachStatus_id" => null,
				"PersonCardAttach_id" => $result[0]["PersonCardAttach_id"],
				"PersonCardAttachStatusType_id" => $statusType,
				"PersonCardAttachStatus_setDate" => $data["PersonCardAttach_setDate"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$callObject->savePersonCardAttachStatus($funcParams);
		}
		return $result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function savePersonCardAttachStatus(Polka_PersonCard_model $callObject, $data)
	{
		if (!empty($data['PersonCardAttachStatusType_Code'])) {
			$data['PersonCardAttachStatusType_id'] = $callObject->getFirstResultFromQuery("
				select
					PersonCardAttachStatusType_id as \"PersonCardAttachStatusType_id\"
				from v_PersonCardAttachStatusType
				where PersonCardAttachStatusType_Code = :PersonCardAttachStatusType_Code
			", $data);
			if (empty($data['PersonCardAttachStatusType_id'])) {
				return $callObject->createError('','Ошибка при получении идентификатора статуса заявления о выборе МО');
			}
		}

		$params = [
			"PersonCardAttachStatus_id" => !empty($data["PersonCardAttachStatus_id"]) ? $data["PersonCardAttachStatus_id"] : null,
			"PersonCardAttach_id" => $data["PersonCardAttach_id"],
			"PersonCardAttachStatusType_id" => $data["PersonCardAttachStatusType_id"],
			"PersonCardAttachStatus_setDate" => !empty($data["PersonCardAttachStatus_setDate"]) ? $data["PersonCardAttachStatus_setDate"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$query = "
	        select
		        PersonCardAttachStatus_id as \"PersonCardAttachStatus_id\",
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
	        from p_PersonCardAttachStatus_ins(
	            PersonCardAttachStatus_id := :PersonCardAttachStatus_id,
				PersonCardAttach_id := :PersonCardAttach_id,
				PersonCardAttachStatusType_id := :PersonCardAttachStatusType_id,
				PersonCardAttachStatus_setDate := :PersonCardAttachStatus_setDate,
				pmUser_id := :pmUser_id
	        )
        ";
		$result = $callObject->queryResult($query, $params);
		if (!is_array($result)) {
			return $callObject->createError('','Ошибка при сохраняении статуса заявления о выборе МО');
		}
		return $result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function savePersonCardMedicalInterventData(Polka_PersonCard_model $callObject, $data)
	{
		$PersonCardMedicalInterventData = json_decode($data["PersonCardMedicalInterventData"], true);
		foreach ($PersonCardMedicalInterventData as $record) {
			$params = [
				"PersonCardMedicalIntervent_id" => null,
				"PersonCard_id" => $data["PersonCard_id"],
				"MedicalInterventType_id" => $record["MedicalInterventType_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			if ($record["PersonCardMedicalIntervent_id"] <= 0 && $record["PersonMedicalIntervent_IsRefuse"]) {
				$response = $callObject->savePersonCardMedicalIntervent($params);
			} elseif ($record["PersonCardMedicalIntervent_id"] > 0 && !$record["PersonMedicalIntervent_IsRefuse"]) {
				$params["PersonCardMedicalIntervent_id"] = $record["PersonCardMedicalIntervent_id"];
				$response = $callObject->deletePersonCardMedicalIntervent($params);
			} elseif ($record["PersonCardMedicalIntervent_id"] > 0 && $record["PersonMedicalIntervent_IsRefuse"]) {
				$params["PersonCardMedicalIntervent_id"] = $record["PersonCardMedicalIntervent_id"];
				$response = $callObject->deletePersonCardMedicalIntervent($params);
				if (empty($response[0]["Error_Msg"])) {
					$params["PersonCardMedicalIntervent_id"] = null;
					$response = $callObject->savePersonCardMedicalIntervent($params);
				}
			}
			if (!empty($response[0]["Error_Msg"])) {
				return [$response];
			}
		}
		return [["Error_Msg" => ""]];
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function savePersonCardMedicalIntervent(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
	        select
		        PersonCardMedicalIntervent_id as \"PersonCardMedicalIntervent_id\",
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
	        from p_PersonCardMedicalIntervent_ins(
	            PersonCardMedicalIntervent_id := :PersonCardMedicalIntervent_id,
				PersonCard_id := :PersonCard_id,
				MedicalInterventType_id := :MedicalInterventType_id,
				pmUser_id := :pmUser_id
        	)
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при сохранении отказа от видов медицинского вмешательства!");
		}
		return $result->result('array');
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @param bool $api
	 * @return array|bool
	 */
	public static function deletePersonCard(Polka_PersonCard_model $callObject, $data, $api = false)
	{
		$godMode = 0;
		if (isSuperadmin() || isLpuAdmin() || (isset($data["isLastAttach"]) && $data["isLastAttach"] == 2)) {
			$godMode = 1;
		}
		if (!$api && $godMode == 0) {
			$sql = "
				select case when (to_char(PersonCard_insDT, '{$callObject->dateTimeForm104}')) = (to_char(tzgetdate(), '{$callObject->dateTimeForm104}'))
					then 1
					else 0
				end as \"IsToday\"
				from PersonCard
				where PersonCard_id = :PersonCard_id
			";
			$sqlParams = ["PersonCard_id" => $data["PersonCard_id"]];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $sqlParams);
			if (!is_object($result)) {
				return false;
			}
			$sel = $result->result('array');
			if (!(count($sel) > 0) || $sel[0]["IsToday"] != 1) {
				return false;
			}
		}
		$god = "";
		if ($api || $godMode == 1) {
			$god = "
				,del_GodMode := 1
			";
		}
		$sql = "
	        select
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
	        from p_PersonCard_del(
	              PersonCard_id := :PersonCard_id
				  {$god}
	        )
        ";
		$sqlParams = ["PersonCard_id" => $data["PersonCard_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteDmsPersonCard(Polka_PersonCard_model $callObject, $data)
	{
		$sql = "
	        select
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
	        from p_PersonCard_del(
	          PersonCard_id := :PersonCard_id
	        )
        ";
		/**@var CI_DB_result $result */
		$sqlParams = ["PersonCard_id" => $data["PersonCard_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function deleteAllPersonCardMedicalIntervent(Polka_PersonCard_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$query = "
			select PCMI.PersonCardMedicalIntervent_id as \"PersonCardMedicalIntervent_id\"
			from v_PersonCardMedicalIntervent PCMI 
			where PCMI.PersonCard_id = :PersonCard_id
		";
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при удалении отказа от видов медицинского вмешательства!");
		}
		$resp = $result->result('array');
		if (count($resp) > 0) {
			foreach ($resp as $item) {
				$funcParams = ["PersonCardMedicalIntervent_id" => $item["PersonCardMedicalIntervent_id"]];
				$response = $callObject->deletePersonCardMedicalIntervent($funcParams);
				if (!empty($response[0]["Error_Msg"])) {
					throw new Exception($response[0]["Error_Msg"]);
				}
			}
		}
		return [["Error_Msg" => ""]];
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function deletePersonCardMedicalIntervent(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
	        select
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
	        from p_PersonCardMedicalIntervent_del(
	          PersonCardMedicalIntervent_id := :PersonCardMedicalIntervent_id
	        )
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при удалении отказа от видов медицинского вмешательства!");
		}
		return $result->result('array');
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deletePersonCardAttach(Polka_PersonCard_model $callObject, $data)
	{
		$statuses = $callObject->getPersonCardAttachStatusesHistory($data);
		if (is_array($statuses) && count($statuses) > 0) {
			foreach ($statuses as $status) {
				$callObject->deletePersonCardAttachStatus($status);
			}
		}
		$query_check = "
			select count(PersonCard_id) as \"cntPC\"
			from PersonCard
			where PersonCardAttach_id = :PersonCardAttach_id
		";
		/**
		 * @var CI_DB_result $result_check
		 * @var CI_DB_result $result
		 */
		$result_check = $callObject->db->query($query_check, $data);
		$result_check = $result_check->result('array');
		if (isset($result_check[0]) && $result_check[0]["cntPC"] > 0) {
			return [["Error_Code" => 0, "Error_Msg" => ""]];
		}
		$query = "
            select
	            error_code as \"Error_Code\",
	            error_message as \"Error_Msg\"
            from p_PersonCardAttach_del(
            	PersonCardAttach_id := :PersonCardAttach_id
            )
		";
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deletePersonCardAttachStatus(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
	        select
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
	        from p_PersonCardAttachStatus_del(
	        	PersonCardAttachStatus_id := :PersonCardAttachStatus_id
	        )
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool|int
	 */
	public static function delDummyStrCharacters(Polka_PersonCard_model $callObject, $data)
	{
		if (!$callObject->csvFrameIsQuote) {
			return true;
		}
		if (empty($data["attached_list_file_path"]) || !file_exists($data["attached_list_file_path"])) {
			return false;
		}
		$dummyStrCharacters = $callObject->csvDummyStrCharacters;
		$filename = $data["attached_list_file_path"];
		$contents = file_get_contents($filename);
		if (!$contents) {
			return false;
		}
		$contents = str_replace($dummyStrCharacters, "", $contents);
		return file_put_contents($filename, $contents);
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkPersonCard(Polka_PersonCard_model $callObject, $data)
	{
		$CloseDT = "";
		$params = [
			"Person_id" => $data["Person_id"],
			"LPU_CODE" => $data["LPU_CODE"],
			"DT" => $callObject->tzGetDate()
		];
		if (isset($data["LPUDX"])) {
			$CloseDT = " or COALESCE(to_char(PersonCard_endDate, 'yyyy-mm-dd'), '') = COALESCE(:LPUDX, '')";
			$params["LPUDX"] = $data["LPUDX"];
		}
		$query = "			
			select
				lp.Lpu_Nick as \"Lpu_Nick\",
			    pc.PersonCard_id as \"PersonCard_id\"
			from
				v_Lpu lp 
				left join lateral (
					select PersonCard_id
					from v_PersonCard 
					where Person_id = :Person_id
					  and (PersonCard_endDate is null or PersonCard_endDate <=:DT {$CloseDT})
					  and Lpu_id = lp.Lpu_id
					  and LpuAttachType_id = 1 
					order by PersonCard_begDate desc
					limit 1
				) pc on true
				where lp.Lpu_f003mcod = :LPU_CODE
				limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function checkPersonCardUniqueness(Polka_PersonCard_model $callObject, $data)
	{
		$queryParams = [];
		$PersonCard_idFilter = "";
		if ($data["PersonCard_id"] != null) {
			$PersonCard_idFilter = " and PersonCard_id <> :PersonCard_id";
			$queryParams["PersonCard_id"] = $data["PersonCard_id"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		$queryParams["PersonCard_Code"] = $data["PersonCard_Code"];
		$queryParams["Person_id"] = $data["Person_id"];
		$sql = "
			select count(*) as chck
			from PersonCardState 
			where Lpu_id = :Lpu_id
			  and PersonCardState_Code = :PersonCard_Code
			  and Person_id != :Person_id
			  {$PersonCard_idFilter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Не удалось проверить номер карты, попробуйте сохранить еще раз.", 666);
		}
		$sel = $result->result('array');
		if ($sel[0]["chck"] > 0) {
			throw new Exception("Номер карты совпадает с номером уже существующей карты.", 7);
		}
		return true;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkIfPersonCardIsExists(Polka_PersonCard_model $callObject, $data)
	{
		$sql = "
			select count(*) as cnt
			from v_PersonCard
			where Person_id = :Person_id
			  and Lpu_id = :Lpu_id
			  and to_char(PersonCard_begDate, '{$callObject->dateTimeFormUnixDate}') <= :PersonDisp_begDate
			  and LpuRegionType_id in (1, 2, 4)
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"],
			"PersonDisp_begDate" => str_replace("'", "", $data["PersonDisp_begDate"])
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkAttachExists(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
			select PersonCard_id as \"PersonCard_id\"
			from PersonCardState 
			where Person_id = :Person_id
			  and Lpu_id <> :Lpu_id
			  and LpuAttachType_id = :LpuAttachType_id
			  and CardCloseCause_id is null
			limit 1
		";
		$params = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuAttachType_id" => $data["LpuAttachType_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result('array');
		return (count($res) > 0) ? true : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkLpuFondHolder(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
            select LFH.LpuPeriodFondHolder_id as \"LpuPeriodFondHolder_id\"
            from v_LpuPeriodFondHolder LFH
            where LFH.Lpu_id = :Lpu_id
              and LpuRegionType_id = :LpuRegionType_id
              and (LFH.LpuPeriodFondHolder_endDate is null or LFH.LpuPeriodFondHolder_endDate > :PersonCard_date)
            limit 1
        ";
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"PersonCard_date" => $data["PersonCard_begDate"],
			"LpuRegionType_id" => $data["LpuRegionType_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result('array');
		return (count($res) > 0) ? true : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public static function checkAttachPosible(Polka_PersonCard_model $callObject, $data)
	{
		$isPersonBaby = $data['PersonAge'] < 18;
		// Проверка на тип ЛПУ по возрасту (детское, взрослое или смешанное)
		$lpuAgeType = $callObject->getLpuAgeType($data);
		if ($lpuAgeType === false) {
			throw new Exception("Не удалось определить тип ЛПУ по возрасту!");
		}
		if (in_array($lpuAgeType, [1, 2])) {
			if ($lpuAgeType == 1 && $isPersonBaby) {
				throw new Exception("Нельзя прикрепить пациентов детского возраста к взрослому ЛПУ!");
			}
			if ($lpuAgeType == 2 && !$isPersonBaby) {
				throw new Exception("Нельзя прикрепить пациентов взрослого возраста к детскому ЛПУ!");
			}
		}
		if ($data["LpuRegionType_id"] == 1 && $isPersonBaby) {
			throw new Exception("Нельзя прикрепить пациентов детского возраста к терапевтическому участку!");
		}
		if ($data["LpuRegionType_id"] == 2 && !$isPersonBaby) {
			throw new Exception("Нельзя прикрепить пациентов взрослого возраста к педиатрическому участку!");
		}
		$sql = "
			select
				datediff('YEAR',pc.PersonCard_begDate,dbo.tzGetDate()) as \"YearCount\",
				pc.Lpu_id as \"Lpu_id\"
			from v_PersonCard_all pc 
			where pc.Person_id = :Person_id
			  and LpuAttachType_id = :LpuAttachType_id
			  and coalesce(PersonCard_IsAttachCondit, 1) = 1
			order by pc.PersonCard_begDate desc
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"LpuAttachType_id" => $data["LpuAttachType_id"]
		];
		$result = $callObject->db->query($sql, $sqlParams);
		/**@var CI_DB_result $result */
		if (!is_object($result)) {
			throw new Exception("Не удалось проверить дату последнего прикрепления.", 666);
		}
		$sel = $result->result('array');
		if (count($sel) > 0) {
			$lpu_prev = $sel[0]["Lpu_id"];
			$years_count = $sel[0]["YearCount"];
			$continue = 1;
			if ($sel[0]["Lpu_id"] == $data["Lpu_id"]) {
				$year_check = true;
			} else {
				if (count($sel) == 1) {
					$years_count = $sel[0]["YearCount"];
				} else {
					for ($i = 1; $i < count($sel) && $continue == 1; $i++) {
						if ($sel[$i]["Lpu_id"] <> $lpu_prev) {
							$continue = 0;
							$years_count = $sel[$i - 1]["YearCount"];
						} else {
							$continue = 1;
							$years_count = $sel[$i]["YearCount"];
						}
					}
				}
				$year_check = ($years_count > 0) ? true : false;
			}
		} else {
			$year_check = true;
		}
		if ($year_check === true) {
			return true;
		}
		$query = "
			select LpuRegionType_id as \"LpuRegionType_id\"
			from v_PersonCard
			where Person_id = :Person_id
			order by PersonCard_LpuBegDate desc
			limit 1
		";
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Не удалось проверить переход из детской сети во взрослую.");
		}
		$result = $result->result('array');
		if (count($result) > 0) {
			if ($result[0]["LpuRegionType_id"] == 2 && $data["LpuRegionType_id"] == 1) {
				return true;
			}
		}
		$sql = "
			select  
				a.Address_id as \"Address_id\",
				a.KLCountry_id as \"KLCountry_id\",
				a.KLRgn_id as \"KLRgn_id\",
				a.KLSubRgn_id as \"KLSubRgn_id\",
				a.KLCity_id as \"KLCity_id\",
				a.KLTown_id as \"KLTown_id\",
				a.KLStreet_id as \"KLStreet_id\",
				a.Address_House as \"Address_House\",
				a.Address_Corpus as \"Address_Corpus\",
				a.Address_Flat as \"Address_Flat\"
			from
				v_PersonPAddress a
				inner join v_PersonCard pc  on pc.Person_id = :Person_id
					and LpuAttachType_id = :LpuAttachType_id
					and PersonCard_IsAttachCondit != 2
					and PersonCard_begDate >= PersonPAddress_insDate
			where a.Person_id = :Person_id
			order by PersonPAddress_insDate desc
			limit 1
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"LpuAttachType_id" => $data["LpuAttachType_id"]
		];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			throw new Exception("Не удалось проверить изменение адреса проживания.");
		}
		$addr_old = $result->result('array');
		$sql = "
			select  
				a.Address_id as \"Address_id\",
				a.KLCountry_id as \"KLCountry_id\",
				a.KLRgn_id as \"KLRgn_id\",
				a.KLSubRgn_id as \"KLSubRgn_id\",
				a.KLCity_id as \"KLCity_id\",
				a.KLTown_id as \"KLTown_id\",
				a.KLStreet_id as \"KLStreet_id\",
				a.Address_House as \"Address_House\",
				a.Address_Corpus as \"Address_Corpus\",
				a.Address_Flat as \"Address_Flat\"
			from 
				v_PersonState p
				left join Address a  on a.Address_id = p.PAddress_id
			where Person_id = :Person_id
			limit 1
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"LpuAttachType_id" => $data["LpuAttachType_id"]
		];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			throw new Exception("Не удалось проверить изменение адреса проживания.");
		}
		$addr_new = $result->result('array');
		if (count($addr_new) == 0 || empty($addr_new[0]['Address_id'])) {
			throw new Exception("У пациента не указан адрес!");
		}
		if (count($addr_old) == 1 &&
			count($addr_new) == 1 &&
			$addr_old[0]["KLCountry_id"] == $addr_new[0]["KLCountry_id"] &&
			$addr_old[0]["KLRgn_id"] == $addr_new[0]["KLRgn_id"] &&
			$addr_old[0]["KLSubRgn_id"] == $addr_new[0]["KLSubRgn_id"] &&
			$addr_old[0]["KLCity_id"] == $addr_new[0]["KLCity_id"] &&
			$addr_old[0]["KLTown_id"] == $addr_new[0]["KLTown_id"] &&
			$addr_old[0]["KLStreet_id"] == $addr_new[0]["KLStreet_id"] &&
			$addr_old[0]["Address_House"] == $addr_new[0]["Address_House"] &&
			$addr_old[0]["Address_Corpus"] == $addr_new[0]["Address_Corpus"] &&
			$addr_old[0]["Address_Flat"] == $addr_new[0]["Address_Flat"]
		) {
			throw new Exception("Нельзя прикреплять пациента чаще 1 раза в год.");
		}
		return true;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkPersonCardCode(Polka_PersonCard_model $callObject, $data)
	{
		$sql = "
			select case when count(*) = 0 then 'true' else 'false' end as chck
			from v_PersonCard 
			where Lpu_id = :Lpu_id
			  and PersonCard_Code = :PersonCard_Code
			  and PersonCard_id <> :PersonCard_id
		";
		$sqlParams = [
			"Lpu_id" => $data["Lpu_id"],
			"PersonCard_Code" => $data["PersonCard_Code"],
			"PersonCard_id" => $data["PersonCard_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkPersonDisp(Polka_PersonCard_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"Person_id" => $data["Person_id"]
		];
		$sql = "
			select count(PersonDisp_id) as ctn
			from
				PersonDisp PD
				inner join v_MedStaffFact MSF on MSF.LpuSection_id = PD.LpuSection_id and MSF.MedPersonal_id = PD.MedPersonal_id
				inner join v_MedSpecOms MSO on MSO.MedSpecOms_id = MSF.MedSpecOms_id and MSO.MedSpec_id=10
			where PD.Person_id = :Person_id
			  and PD.PersonDisp_endDate is null
			  and PD.Lpu_id <> :Lpu_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}
}