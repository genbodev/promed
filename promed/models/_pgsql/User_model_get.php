<?php

class User_model_get
{
	/**
	 * @param $data
	 * @return bool
	 */
	public static function getStacPriemAdditionalCondition($data)
	{
		return false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @param $response
	 * @return array
	 */
	public static function getArmsForMedStaffFactList(User_model $callObject, $data, $response)
	{
		if (!isset($response) || !is_array($response)) {
			$response = [];
		}
		if (empty($data["Groups"])) {
			$data["Groups"] = null;
		}
		// Получаем список всех АРМов
		$arms = $callObject->getARMList();
		if (is_array($response) && sizeof($response)) {
			$doubles = [];
			for ($i = 0, $cnt = sizeof($response); $i < $cnt; $i++) {
				// Определяем тип АРМа для полученного места работы / службы
				$type = $callObject->defineARMType($response[$i], $data["Groups"]);
				if (empty($type) || (is_array($type) && count($type) == 0) || (!is_array($type) && !array_key_exists($type, $arms))) {
					unset($response[$i]);
					continue;
				}
				if (!is_array($type)) {
					$type = [$type];
				}
				//Создаем дубли из записи по количеству возможных АРМов (если defineARMType вернул массив)
				foreach ($type as $k => $v) {
					$dbl = $response[$i];
					$dbl["ARMType"] = strtolower($v);
					// Определяем по типу допполя АРМов (наименования)
					if (isset($arms[$dbl["ARMType"]])) {
						// Если тип АРМа описан
						$arm_name = $arms[$dbl["ARMType"]]["Arm_Name"];
						if ($dbl["ARMType"] === "operblock") {
							if (havingGroup("operblock_head", $data["Groups"])) {
								$arm_name = "АРМ заведующего оперблоком";
							} else if (havingGroup("operblock_surg", $data["Groups"])) {
								$arm_name = "АРМ хирурга оперблока";
							} else {
								unset($response[$i]); // если нет группы то АРМ оперблока не нужен.
								continue;
							}
						} else if ($dbl["ARMType"] === "paidservice") {
							if (havingGroup("DrivingCommissionReg", $data["Groups"])) {
								$arm_name = "АРМ регистратора платных услуг";
							} else if (havingGroup(["DrivingCommissionOphth", "DrivingCommissionPsych", "DrivingCommissionPsychNark", "DrivingCommissionTherap"], $data["Groups"])) {
								$arm_name = "АРМ врача платных услуг";
							} else {
								unset($response[$i]); // если нет группы то АРМ не нужен.
								continue;
							}
						} else if ($dbl["ARMType"] === "phys") {
							//появились армы требующие как службы так и врача
							if (isset($data["ARMType"])) {
								$physsql = "
									select
										ms.MedService_id as \"MedService_id\",
										ms.MedService_Nick as \"MedService_Nick\",
										ms.MedService_Name as \"MedService_Name\",
										ms.MedServiceType_id as \"MedServiceType_id\",
										ms.MedService_IsExternal as \"MedService_IsExternal\",
										ms.MedService_IsLocalCMP as \"MedService_IsLocalCMP\",
										ms.MedService_LocalCMPPath as \"MedService_LocalCMPPath\",
										mst.MedServiceType_SysNick as \"MedServiceType_SysNick\"
									from
										v_MedService ms
										left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
										left join v_MedServiceMedPersonal msmp on msmp.MedService_id = ms.MedService_id and msmp.MedPersonal_id = :MedPersonal_id
										left join v_MedStaffFact msmpp on msmpp.MedPersonal_id = :MedPersonal_id
									where ms.LpuSection_id = :LpuSection_id and msmpp.MedStaffFact_id = :MedStaffFact_id
									limit 1
								";
								$physparams = [
									"MedPersonal_id" => $dbl["MedPersonal_id"],
									"LpuSection_id" => $dbl["LpuSection_id"],
									"MedStaffFact_id" => $dbl["MedStaffFact_id"]
								];
								/**@var CI_DB_result $physres */
								$physres = $callObject->db->query($physsql, $physparams);
								if (is_object($physres)) {
									$physres = $physres->result("array");
									if (count($physres)) {
										foreach ($physres[0] as $k1 => $v1) {
											$dbl[$k1] = $v1;
										}
									}
								}
							}
						}
						$dbl["ARMId"] = $arms[$dbl["ARMType"]]["Arm_id"]; // Ид АРМа
						$dbl["ARMType_id"] = $arms[$dbl["ARMType"]]["ARMType_id"]; // Название АРМа
						$dbl["ARMName"] = $arm_name; // Название АРМа
						$dbl["ARMNameLpu"] = "{$dbl["ARMName"]}<div style=\"color:#666;\">{$dbl["Lpu_Nick"]}&nbsp;</div>"; // Название АРМа + ЛПУ
						$dbl["ARMForm"] = $arms[$dbl["ARMType"]]["Arm_Form"]; // Форма АРМа
						$dbl["client"] = $arms[$dbl["ARMType"]]["client"]; // Тип клиента
						$dbl["ShowMainMenu"] = $arms[$dbl["ARMType"]]["ShowMainMenu"];
					}
					// Место работы одной строкой: подразделение и отделение
					$dbl["Name"] = ((!empty($dbl["LpuUnit_Name"])) ? "<div>{$dbl["LpuUnit_Name"]}</div>" : "") . ((!empty($dbl["LpuSection_Name"])) ? "<div>{$dbl["LpuSection_Name"]}</div>" : "");
					if ($dbl["MedService_id"] > 0) { // Если служба
						$dbl["Name"] = "{$dbl["Name"]}<div style=\"color:darkblue;\">{$dbl["MedService_Name"]}&nbsp;</div>" . (empty($dbl["Name"]) ? "<br/>" : "");
					}
					$dbl["id"] = "{$dbl["ARMType"]}_{$dbl["Lpu_id"]}_{$dbl["MedStaffFact_id"]}_{$dbl["LpuSection_id"]}_{$dbl["LpuSectionProfile_id"]}_{$dbl["MedService_id"]}";
					if ($k > 0) {
						// Если больше первой записи
						$doubles[] = $dbl;
					} else {
						// Иначе (первая запись)
						$response[$i] = $dbl;
					}
				}
			}
			// Если были дубли, то надо их включить в список
			foreach ($doubles as $k => $v) {
				$response[] = $v;
			}
		}
		$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = $callObject->config->item("LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS");
		$ALLOW_EXTJS6_ARMS_FOR_ALL = $callObject->config->item('ALLOW_EXTJS6_ARMS_FOR_ALL');

		if (!is_array($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS)) {
			$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = [];
		}
		// Добавляем армы которые не привязаны ни к месту работы, ни к службе
		// Это армы администратора, администратора ЛПУ и прочие
		foreach ($arms as $k => $v) {
			if (in_array($k, ["callcenter"]) && !empty($data["session"]["lpu_id"]) && $_SESSION["region"]["nick"] != "saratov") {
				if (havingGroup(["CallCenterAdmin", "OperatorCallCenter"], $data["Groups"])) {
					$r = $callObject->getOtherARMList($data);
					$r["ARMType"] = $k;
					$r["ARMType_id"] = $v["ARMType_id"];
					$r["ARMName"] = $v["Arm_Name"];
					$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
					$r["ARMForm"] = $v["Arm_Form"];
					$r["client"] = $v["client"];
					$r["id"] = "{$k}_{$r["Lpu_id"]}____";
					$r["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $r;
				}
			}
			else if ($k == 'regpolprivate6') {

				if (havingGroup('PrivateReg', $data['Groups'])) {

					$r = $callObject->getOtherOrgARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					$r['ARMNameLpu'] = $v['Arm_Name'] . '<div style="color:#666;">' . $r['Org_Nick'] . '&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k . '_' . $r['Org_id'] . '____';
					$r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if (($k == "smo" && havingGroup("SMOUser", $data["Groups"])) || ($k == "tfoms" && havingGroup("TFOMSUser", $data["Groups"]))) {
				$r = $callObject->getOtherOrgARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Org_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Org_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} else if ($k == "hn") {
				if (!empty($data["MedPersonal_id"])) {
					$hnp = $callObject->getHeadNursePost($data);
					if (is_array($hnp)) {
						$r = $callObject->getOtherOrgARMList($data);
						$r["ARMType"] = $k;
						$r["ARMType_id"] = $v["ARMType_id"];
						$r["ARMName"] = $v["Arm_Name"];
						$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Org_Nick"]}&nbsp;</div>";
						$r["ARMForm"] = $v["Arm_Form"];
						$r["client"] = $v["client"];
						$r["id"] = "{$k}_{$r["Org_id"]}____";
						$r["ShowMainMenu"] = $v["ShowMainMenu"];
						$response[] = $r;
					}
				}
			} else if ($k == "zags" && havingGroup("ZagsUser", $data["Groups"])) {
				$r = $callObject->getOtherOrgARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Org_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Org_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} //Специалист МЗ
			else if ($k == "spec_mz" && havingGroup("OuzSpec", $data["Groups"])) {
				if ($data["session"]["orgtype"] == "touz" || $_SESSION["region"]["nick"] <> "perm") {
					$r = $callObject->getOtherOrgARMList($data);
					$r["ARMType"] = $k;
					$r["ARMType_id"] = $v["ARMType_id"];
					$r["ARMName"] = $v["Arm_Name"];
					$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Org_Nick"]}&nbsp;</div>";
					$r["ARMForm"] = $v["Arm_Form"];
					$r["client"] = $v["client"];
					$r["id"] = "{$k}_{$r["Org_id"]}____";
					$r["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $r;
				}
			} else if (in_array($k, ["lpuadmin", "superadmin", "epidem"]) && !empty($data["session"]["lpu_id"])) {
				if (havingGroup($k, $data["Groups"]) || ($k == "epidem" && havingGroup("epidem_ufa", $data["Groups"]))) {
					$r = $callObject->getOtherARMList($data);
					$r["ARMType"] = $k;
					$r["ARMType_id"] = $v["ARMType_id"];
					$r["ARMName"] = $v["Arm_Name"];
					$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
					$r["ARMForm"] = $v["Arm_Form"];
					$r["client"] = $v["client"];
					$r["id"] = "{$k}_{$r["Lpu_id"]}____";
					$r["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $r;
				}
			} else if (in_array($k, ["lpuadmin6"]) && !empty($data["session"]["lpu_id"])) {
				if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($data["session"]["lpu_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS)) && havingGroup("lpuadmin", $data["Groups"])) {
					$r = $callObject->getOtherARMList($data);
					$r["ARMType"] = $k;
					$r["ARMType_id"] = $v["ARMType_id"];
					$r["ARMName"] = $v["Arm_Name"];
					$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
					$r["ARMForm"] = $v["Arm_Form"];
					$r["client"] = $v["client"];
					$r["id"] = "{$k}_{$r["Lpu_id"]}____";
					$r["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $r;
				}
			} else if (in_array($k, ["zakup"]) && !empty($data["session"]["lpu_id"])) {
				if (havingGroup($k, $data["Groups"]) || ($k == "zakup" && havingGroup("zakup", $data["Groups"]))) {
					$r = $callObject->getOtherARMList($data);
					$r["ARMType"] = $k;
					$r["ARMType_id"] = $v["ARMType_id"];
					$r["ARMName"] = $v["Arm_Name"];
					$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
					$r["ARMForm"] = $v["Arm_Form"];
					$r["client"] = $v["client"];
					$r["id"] = "{$k}_{$r["Lpu_id"]}____";
					$r["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $r;
				}
			} else if (in_array($k, ["orgadmin"]) && !empty($data["session"]["org_id"])) {
				if (havingGroup($k, $data["Groups"])) {
					$r = $callObject->getOtherOrgARMList($data);
					$r["ARMType"] = $k;
					$r["ARMType_id"] = $v["ARMType_id"];
					$r["ARMName"] = $v["Arm_Name"];
					$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Org_Nick"]}&nbsp;</div>";
					$r["ARMForm"] = $v["Arm_Form"];
					$r["client"] = $v["client"];
					$r["id"] = "{$k}_{$r["Org_id"]}____";
					$r["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $r;
				}
			} else if (in_array($k, ["polkallo"]) && havingGroup("OperLLO", $data["Groups"])) {
				$llo = false;
				if (!empty($data["MedPersonal_id"])) {
					$llo = $callObject->getLLOData($data);
				}
				if (is_array($llo)) {
					$llo["ARMType"] = $k;
					$llo["ARMType_id"] = $v["ARMType_id"];
					$llo["ARMName"] = $v["Arm_Name"];
					$llo["ARMNameLpu"] = $v["Arm_Name"] . "<div style=\"color:#666;\">" . $llo["Lpu_Nick"] . "&nbsp;</div>";
					$llo["ARMForm"] = $v["Arm_Form"];
					$llo["client"] = $v["client"];
					$llo["id"] = $k . "_" . $llo["Lpu_id"] . "____";
					$llo["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $llo;
				} elseif (!empty($data["session"]["lpu_id"])) {
					$llo = $callObject->getOtherARMList($data);
					$llo["ARMType"] = $k;
					$llo["ARMType_id"] = $v["ARMType_id"];
					$llo["ARMName"] = $v["Arm_Name"];
					$llo["ARMNameLpu"] = $v["Arm_Name"] . "<div style=\"color:#666;\">" . $llo["Lpu_Nick"] . "&nbsp;</div>";
					$llo["ARMForm"] = $v["Arm_Form"];
					$llo["client"] = $v["client"];
					$llo["id"] = $k . "_" . $llo["Lpu_id"] . "____";
					$llo["ShowMainMenu"] = $v["ShowMainMenu"];
					$response[] = $llo;
				}
			} else if ($k == "mzchieffreelancer" && $callObject->isHeadMedSpecMedPersonal()) {
				$r = $callObject->getOtherOrgARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Org_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Org_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} else if ($k == "rpo") {
				if (!empty($data["MedPersonal_id"])) {
					$r = $callObject->isHeadWithMedService();
					if (is_array($r)) {
						$r["ARMType"] = $k;
						$r["ARMType_id"] = $v["ARMType_id"];
						$r["ARMName"] = $v["Arm_Name"];
						$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
						$r["ARMForm"] = $v["Arm_Form"];
						$r["client"] = $v["client"];
						$r["id"] = "{$k}_{$r["Lpu_id"]}____";
						$r["ShowMainMenu"] = $v["ShowMainMenu"];
						$r["id"] = $r["ARMType"] . "_" . $r["Lpu_id"] . "_" . $r["MedStaffFact_id"] . "_" . $r["LpuSection_id"] . "_" . $r["LpuSectionProfile_id"] . "_" . $r["MedService_id"];
						if (!empty($data["MedStaffFact_id"]) && $data["ARMType"] == "rpo") {
							$response[0] = $r;
						} else {
							$response[] = $r;
						}
					}
				}
			} else if (in_array($k, ["lpupharmacyhead"]) && !empty($data["session"]["lpu_id"]) && havingGroup("zavapt", $data["Groups"])) {
				$r = $callObject->getOtherARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Lpu_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} else if ($k == "communic" && havingGroup("Communic", $data["Groups"])) {
				$r = $callObject->getOtherARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Lpu_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} else if ($k == "dispcallnmp" && havingGroup("DispCallNMP", $data["Groups"])) {
				$r = $callObject->getOtherARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Lpu_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} else if ($k == "dispdirnmp" && havingGroup("DispDirNMP", $data["Groups"])) {
				$r = $callObject->getOtherARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Lpu_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} else if ($k == "nmpgranddoc" && havingGroup("NMPGrandDoc", $data["Groups"])) {
				$r = $callObject->getOtherARMList($data);
				$r["ARMType"] = $k;
				$r["ARMType_id"] = $v["ARMType_id"];
				$r["ARMName"] = $v["Arm_Name"];
				$r["ARMNameLpu"] = "{$v["Arm_Name"]}<div style=\"color:#666;\">{$r["Lpu_Nick"]}&nbsp;</div>";
				$r["ARMForm"] = $v["Arm_Form"];
				$r["client"] = $v["client"];
				$r["id"] = "{$k}_{$r["Lpu_id"]}____";
				$r["ShowMainMenu"] = $v["ShowMainMenu"];
				$response[] = $r;
			} else if(in_array($k, array('lpuuser', 'lpuuser6')) && havingGroup('LpuUser',$data['Groups']) && havingGroup('RegistryUserReadOnly',$data['Groups']) && $_SESSION['region']['nick'] == 'yaroslavl') {
				$r = $callObject->getOtherARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Lpu_id'].'____';
				$r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
			}
		}
		$IsMainServer = $callObject->config->item("IsMainServer");
		$IsSMPServer = $callObject->config->item("IsSMPServer");
		$armsByType = [];
		foreach ($response as $arm) {
			if ($IsSMPServer === true) {
				$arm["ShowMainMenu"] = 1; // На сервере СМП меню для всех АРМ скрыто
			}
			if (!array_key_exists($arm["ARMType"], $armsByType)) {
				$armsByType[$arm["ARMType"]] = array();
			}
			$armsByType[$arm["ARMType"]][] = $arm;
		}
		$response = [];
		$smpArms = ["smpadmin", "smpdispatchcall", "smpdispatchdirect", "smpdispatchstation", "smpheadduty", "smpheadbrig", "smpheaddoctor", "zmk", "smpinteractivemap"];
		$additionalSMPArms = [];
		if ($callObject->config->item("ADDITIONAL_SMP_ARMS") && is_array($callObject->config->item("ADDITIONAL_SMP_ARMS"))) {
			$additionalSMPArms = $callObject->config->item("ADDITIONAL_SMP_ARMS");
		}

		$excArms = array();
		$DISABLED_ARMS = $callObject->config->item("DISABLED_ARMS");
		$DISABLED_ARMS_EXCEPT_LPU = $callObject->config->item("DISABLED_ARMS_EXCEPT_LPU");
		if (
			!empty($DISABLED_ARMS)
			&& (
				empty($DISABLED_ARMS_EXCEPT_LPU)
				|| !is_array($DISABLED_ARMS_EXCEPT_LPU)
				|| empty($data["session"]["lpu_id"])
				|| !in_array($data["session"]["lpu_id"], $DISABLED_ARMS_EXCEPT_LPU)
			)
		) {
			$excArms = array_merge($excArms, $DISABLED_ARMS); // отключаем АРМы, указанные в конифге
		}

		foreach ($armsByType as $key => $arm) {
			if (
				isset($data['Need_all'])
				|| (
					($IsSMPServer !== true || in_array($key, array_merge($smpArms, array('superadmin', 'communic'), $additionalSMPArms)))
					&& ($IsMainServer !== true || !in_array($key, $smpArms))
					&& (!in_array($key, $excArms))
				)
			) {
				$response = array_merge($response, $arm);
			}
		}
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @return array
	 */
	public static function getARMList(User_model $callObject)
	{
		$ARMList = $callObject->loadARMList();
		$_REGION = $callObject->config->item($_SESSION["region"]["nick"]);
		if (isset($_REGION["DENIED_ARM_TYPES"]) && is_array($_REGION["DENIED_ARM_TYPES"]) && count($_REGION["DENIED_ARM_TYPES"]) > 0) {
			foreach ($_REGION["DENIED_ARM_TYPES"] as $ARMType) {
				if (array_key_exists($ARMType, $ARMList)) {
					unset($ARMList[$ARMType]);
				}
			}
		}
		if (isset($_REGION["ALLOWED_ARM_TYPES"]) && is_array($_REGION["ALLOWED_ARM_TYPES"]) && count($_REGION["ALLOWED_ARM_TYPES"]) > 0) {
			foreach ($ARMList as $ARMType => $ARM) {
				if (!in_array($ARMType, $_REGION["ALLOWED_ARM_TYPES"])) {
					unset($ARMList[$ARMType]);
				}
			}
		}
		$ids = [];
		$callObject->load->library("swCache", [], 'swcache');
		$ARMTypeList = $callObject->swcache->get("ARMType");
		//Если в кэше нет ARMType, то запрашиваем из базы
		if (!is_array($ARMTypeList) || count($ARMTypeList) == 0) {
			$ARMTypeList = $callObject->getARMTypeList();
		}
		if (is_array($ARMTypeList)) {
			foreach ($ARMTypeList as $item) {
				$ids[$item["ARMType_Code"]] = $item["ARMType_id"];
			}
		}
		foreach ($ARMList as $index => &$item) {
			$item["ARMType_id"] = isset($ids[$item["Arm_id"]]) ? $ids[$item["Arm_id"]] : null;
		}
		return $ARMList;
	}

	/**
	 * @param User_model $callObject
	 * @return array|false
	 */
	public static function getARMTypeList(User_model $callObject)
	{
		$query = "
			select
				ARMType_id as \"ARMType_id\",
				ARMType_Code as \"ARMType_Code\",
				ARMType_Name as \"ARMType_Name\"
			from v_ARMType
		";
		return $callObject->queryResult($query);
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPHPARMTypeList(User_model $callObject, $data)
	{
		$ARMList = $callObject->loadARMList();
		$ARMList["_noarm_"] = [
			"Arm_id" => -1,
			"Arm_Name" => "Пользователи, работающие без АРМ"
		];
		$response = [];
		foreach ($ARMList as $key => $array) {
			if (empty($data["query"]) || mb_stripos($array["Arm_Name"], $data["query"]) !== false) {
				$response[] = [
					"ARMType_id" => $array["Arm_id"],
					"ARMType_Code" => $array["Arm_id"],
					"ARMType_Name" => $array["Arm_Name"],
					"ARMType_SysNick" => $key,
				];
			}
		}
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @param $lpu_nick
	 * @return bool
	 */
	public static function getLpuIdFromLpuNick(User_model $callObject, $lpu_nick)
	{
		$sql = "select Lpu_id as \"Lpu_id\" from v_Lpu where Lpu_Nick ilike '%{$lpu_nick}%'";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (isset($result[0])) ? $result[0]["Lpu_id"] : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $lpu_id
	 * @return bool
	 */
	public static function getLpuNickFromLpuId(User_model $callObject, $lpu_id)
	{
		if (!empty($lpu_id)) {
			$sql = "select Lpu_Nick as \"Lpu_Nick\" from v_Lpu where Lpu_id = {$lpu_id}";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
			return (isset($result[0])) ? $result[0]["Lpu_Nick"] : false;
		}
		return false;
	}

	/**
	 * @param User_model $callObject
	 * @param $org_id
	 * @return string
	 */
	public static function getOrgNickFromOrgId(User_model $callObject, $org_id)
	{
		$orgNick = "";
		if (!empty($org_id)) {
			$sql = "
				select coalesce(Org_Nick, Org_Name) as \"Lpu_Nick\"
				from v_Org
				where Org_id = {$org_id}
				limit 1
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql);
			if (is_object($result)) {
				$result = $result->result("array");
				if (is_array($result) && count($result) == 1) {
					$orgNick = $result[0]["Lpu_Nick"];
				}
			}
		}
		return $orgNick;
	}

	/**
	 * @param User_model $callObject
	 * @param $lpu_name
	 * @return bool
	 */
	public static function getLpuIdFromLpuName(User_model $callObject, $lpu_name)
	{
		$sql = "select Lpu_id as \"Lpu_id\" from v_Lpu where Lpu_Nick ilike '%{$lpu_name}%' ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (isset($result[0])) ? $result[0]["Lpu_id"] : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getOrgTypeTree(User_model $callObject, $data)
	{
		$callObject->load->library("swCache");
		if ($resCache = $callObject->swcache->get("getOrgTypeTree")) {
			return $resCache;
		}
		$query = "
			select
				ot.OrgType_id as \"OrgType_id\",
				ot.OrgType_Name as \"OrgType_Name\"
			from v_OrgType ot
			where exists(
					select o.Org_id
					from v_Org o
					where o.OrgType_id = ot.OrgType_id
					  and o.Org_isAccess = 2
			)
			union
			select
				ot.OrgType_id as \"OrgType_id\",
				ot.OrgType_Name as \"OrgType_Name\"
			from v_OrgType ot
			where exists(
					select o.Org_id
					from
						v_Org o
						inner join v_pmUserCacheOrg puco on puco.Org_id = o.Org_id
					where o.OrgType_id = ot.OrgType_id
			)
			order by \"OrgType_Name\"
		";
		$resp = $callObject->queryResult($query);
		if (!empty($resp)) {
			$callObject->swcache->set("getOrgTypeTree", $resp, ["ttl" => 3600]);
		}
		return $resp;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @param $superadmin
	 * @return array|bool
	 */
	public static function getOrgUsersTree(User_model $callObject, $data, $superadmin)
	{
		$where = [];
		$queryParams = [];
		if ($superadmin) {
			if (!empty($data["node"]) && $data["node"] != "other") {
				$where[] = "OrgType_id = :OrgType_id";
				$queryParams["OrgType_id"] = $data["node"];
			} else {
				$where[] = "OrgType_id is null";
			}
			$where[] = "
				(
					coalesce(o.Org_isAccess,1) = 2 or
					exists (
						select puco.pmUserCacheOrg_id
						from
							v_pmUserCacheOrg puco
							inner join pmUserCache puc on puc.pmUser_id = puco.pmUserCache_id
						where puco.Org_id = o.Org_id
						  and coalesce(puc.pmUser_deleted, 1) = 1
					)
				)
			";
		} else {
			$where[] = "Org_id in (" . implode(",", $data["session"]["orgs"]) . ")";
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$sql = "
			select
				o.Org_id as \"Org_id\",
				o.Org_Nick as \"Org_Nick\",
				coalesce(o.Org_isAccess, 1) as \"Org_isAccess\"
			from v_Org o
			{$whereString}
			order by o.Org_Nick
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @param $superadmin
	 * @return array|bool
	 */
	public static function getLpuUsersTree(User_model $callObject, $data, $superadmin)
	{
		$where = "";
		$queryParams = [];
		if (!$superadmin && !empty($data["Lpu_id"])) {
			$where = "where Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$sql = "
			select
				Lpu_id as \"Lpu_id\",
				Org_id as \"Org_id\",
				Lpu_Nick as \"Lpu_Nick\"
			from v_Lpu
			{$where}
			order by Lpu_Nick
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @param $superadmin
	 * @param bool $farmacynetadmin
	 * @return array|bool
	 */
	public static function getOrgFarmacyUsersTree(User_model $callObject, $data, $superadmin, $farmacynetadmin = false)
	{
		$where = "";
		$queryParams = [];
		if (!$superadmin) {
			if (!empty($data["OrgFarmacy_id"])) {
				$where = "where OrgFarmacy_id = :OrgFarmacy_id";
				$queryParams["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
			} else {
				$where = "where (1 = 0)";
			}
		}
		if (!$farmacynetadmin) {
			$sql = "
				select
					OrgFarmacy_id as \"OrgFarmacy_id\",
					Org_id as \"Org_id\",
					OrgFarmacy_Nick as \"OrgFarmacy_Nick\"
				from v_OrgFarmacy
				{$where}
				order by OrgFarmacy_Nick
			";
		} else {
			$sql = "
				select	distinct
					ct.OrgFarmacy_id as \"OrgFarmacy_id\",
					ofr.Org_id as \"Org_id\",
					ofr.OrgFarmacy_Nick as \"OrgFarmacy_Nick\"
				from
					v_Contragent ct
					inner join	v_OrgFarmacy ofr on ct.OrgFarmacy_id = ofr.OrgFarmacy_id
				where ct.Org_pid = :OrgFarmacy_id
				order by OrgFarmacy_Nick
			";
			$queryParams["OrgFarmacy_id"] = $data["OrgNet_id"];
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCurrentLpuData(User_model $callObject, $data)
	{
		//TODO 111
		$sql = "
			with Attributes as (
				select
					ASV.AttributeSignValue_TablePKey as Lpu_id,
					AS1.AttributeSign_Code as SignCode,
					A.Attribute_SysNick as SysNick,
					coalesce(
						AV.AttributeValue_ValueIdent::varchar,
						AV.AttributeValue_ValueInt::varchar,
						AV.AttributeValue_ValueFloat::varchar,
						AV.AttributeValue_ValueString,
						AV.AttributeValue_ValueBoolean::varchar,
						to_char(AV.AttributeValue_ValueDate, '{$callObject->dateTimeForm108}')
					) as Value
				from
					v_AttributeSign AS1
					inner join v_AttributeSignValue ASV on ASV.AttributeSign_id = AS1.AttributeSign_id
					inner join v_AttributeValue AV on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
					inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
				where (AS1.AttributeSign_TableName ilike 'dbo.Lpu' or AS1.AttributeSign_TableName ilike 'dbo.v_Lpu')
				  and ASV.AttributeSignValue_TablePKey = :Lpu_id
				  and ASV.AttributeSignValue_begDate <= tzGetDate()
				  and (ASV.AttributeSignValue_endDate is null or ASV.AttributeSignValue_endDate > tzGetDate())
				  and A.Attribute_begDate <= tzGetDate()
				  and (A.Attribute_endDate is null or A.Attribute_endDate > tzGetDate())
			)
			select
				rtrim(coalesce(Lpu.Lpu_Name, '')) as \"Lpu_Name\",
				rtrim(coalesce(Lpu.Lpu_Nick, '')) as \"Lpu_Nick\",
				rtrim(coalesce(Lpu.Lpu_SysNick, '')) as \"Lpu_SysNick\",
				rtrim(coalesce(Lpu.Lpu_Email, '')) as \"Lpu_Email\",
				coalesce(LpuLevel.LpuLevel_id, 0) as \"LpuLevel_id\",
				coalesce(LpuLevel.LpuLevel_Code, 0) as \"LpuLevel_Code\",
				coalesce(Lpu.Lpu_RegNomC::int, 0) as \"Lpu_RegNomC\",
				coalesce(Lpu.Lpu_IsLab, 0) as \"Lpu_IsLab\",
				Lpu.Org_id as \"Org_id\",
				coalesce(Lpu_IsDMS, 1) as \"Lpu_IsDMS\",
				coalesce(Lpu_IsSecret, 1) as \"Lpu_IsSecret\",
				OST.KLRgn_id as \"KLRgn_id\",
				OST.KLSubRgn_id as \"KLSubRgn_id\",
				OST.KLCity_id as \"KLCity_id\",
				OST.KLTown_id as \"KLTown_id\",
				rtrim(coalesce(Lpu.Lpu_Name, '')) as \"Org_Name\",
				rtrim(coalesce(Lpu.Lpu_Nick, '')) as \"Org_Nick\",
				rtrim(coalesce(Lpu.Lpu_Email, '')) as \"Org_Email\",
				coalesce(LpuType.LpuType_id, 0) as \"LpuType_id\",
				coalesce(LpuType.LpuType_Code, 0) as \"LpuType_Code\",
				BirthMesLevel.MesLevel_id as \"BirthMesLevel_id\",
				BirthMesLevel.MesLevel_Code as \"BirthMesLevel_Code\"
			from
				v_Lpu Lpu
				left join LpuLevel on LpuLevel.LpuLevel_id = Lpu.LpuLevel_id
				left join v_LpuType LpuType on LpuType.LpuType_id = Lpu.LpuType_id
				left join lateral (
					select
						KLRgn_id,
						KLSubRgn_id,
						KLCity_id,
						KLTown_id
					from v_OrgServiceTerr
					where Org_id = Lpu.Org_id
					limit 1
				) as OST on true
				left join lateral (
					select (
						select Value
						from Attributes
						where SignCode = 6
						  and SysNick ilike 'LevelROD'
						limit 1
					) as LevelROD
				) as A on true
				left join v_MesLevel BirthMesLevel on BirthMesLevel.MesLevel_id = A.LevelROD::bigint
			where Lpu.Lpu_id = :Lpu_id
		";
		$sqlParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCurrentOrgData(User_model $callObject, $data)
	{
		$sql = "
			select
				rtrim(coalesce(Org.Org_Name, '')) as \"Org_Name\",
				rtrim(coalesce(Org.Org_Nick, '')) as \"Org_Nick\",
				rtrim(coalesce(Org.Org_Email, '')) as \"Org_Email\",
				Org_id as \"Org_id\",
				OST.KLRgn_id as \"KLRgn_id\",
				OST.KLSubRgn_id as \"KLSubRgn_id\",
				OST.KLCity_id as \"KLCity_id\",
				OST.KLTown_id as \"KLTown_id\"
			from
				v_Org Org
				left join lateral (
					select
						KLRgn_id,
						KLSubRgn_id,
						KLCity_id,
						KLTown_id
					from v_OrgServiceTerr
					where Org_id = Org.Org_id
					limit 1
				) as OST on true
			where Org.Org_id = :Org_id
		";
		$sqlParams = ["Org_id" => $data["Org_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getOtherARMList(User_model $callObject, $data)
	{
		$response = [
			"MedStaffFact_id" => null,
			"LpuSection_id" => null,
			"MedPersonal_id" => $data["session"]["medpersonal_id"],
			"LpuSection_Name" => null,
			"LpuSection_Nick" => null,
			"PostMed_Name" => null,
			"PostMed_Code" => null,
			"PostMed_id" => null,
			"LpuUnit_Name" => null,
			"Timetable_isExists" => null,
			"LpuUnitType_SysNick" => null,
			"LpuUnitType_id" => null,
			"LpuSectionProfile_SysNick" => null,
			"LpuSectionProfile_Code" => null,
			"LpuSectionProfile_id" => null,
			"MedService_id" => null,
			"MedService_Nick" => null,
			"MedService_Name" => null,
			"MedServiceType_id" => null,
			"MedServiceType_SysNick" => null,
			"MedPersonal_FIO" => null,
			"Org_id" => $data["session"]["org_id"],
			"Lpu_id" => $data["session"]["lpu_id"],
			"Lpu_Nick" => $callObject->getLpuNickFromLpuId($data["session"]["lpu_id"]),
		];
		$response["Org_Nick"] = $response["Lpu_Nick"];
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getOtherOrgARMList(User_model $callObject, $data)
	{
		$response = [
			"MedStaffFact_id" => null,
			"LpuSection_id" => null,
			"MedPersonal_id" => null,
			"LpuSection_Name" => null,
			"LpuSection_Nick" => null,
			"PostMed_Name" => null,
			"PostMed_Code" => null,
			"PostMed_id" => null,
			"LpuUnit_Name" => null,
			"Timetable_isExists" => null,
			"LpuUnitType_SysNick" => null,
			"LpuUnitType_id" => null,
			"LpuSectionProfile_SysNick" => null,
			"LpuSectionProfile_Code" => null,
			"LpuSectionProfile_id" => null,
			"MedService_id" => null,
			"MedService_Nick" => null,
			"MedService_Name" => null,
			"MedServiceType_id" => null,
			"MedServiceType_SysNick" => null,
			"MedPersonal_FIO" => null,
			"Org_id" => $data["session"]["org_id"],
			"Org_Nick" => $callObject->getOrgNickFromOrgId($data["session"]["org_id"])
		];
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUserMedStaffFactList(User_model $callObject, $data)
	{
		$orgmsfilter = "Org_id = :Org_id ";
		if ($data["session"]["orgtype"] != "lpu") {
			$filter = "";
			$params = [
				"Org_id" => $data["session"]["org_id"],
				"pmUser_id" => $data["pmUser_id"],
				"pmUser_Name" => toAnsi($data["session"]["user"])
			];
			if ($data["MedService_id"] > 0) {
				$params["MedService_id"] = $data["MedService_id"];
				$filter = " and MS.MedService_id = :MedService_id";
			}
			if (havingGroup("orgadmin")) {
				//Админу организации доступны все службы его организации
				$sql = "
					select
						null::bigint as \"MedStaffFact_id\",
						MS.LpuSection_id as \"LpuSection_id\",
						:pmUser_id as \"MedPersonal_id\",
						null as \"LpuSection_Name\",
						null as \"LpuSection_Nick\",
						null as \"PostMed_Name\",
						null as \"PostMed_Code\",
						null::bigint as \"PostMed_id\",
						null::bigint as \"LpuBuilding_id\",
						null as \"LpuBuilding_Name\",
						null::bigint as \"LpuUnit_id\",
						null::bigint as \"LpuUnitSet_id\",
						null as \"LpuUnit_Name\",
						null as \"Timetable_isExists\",
						null as \"LpuUnitType_SysNick\",
						MS.LpuUnitType_id as \"LpuUnitType_id\",
						null as \"LpuSectionProfile_SysNick\",
						null as \"LpuSectionProfile_Code\",
						null::bigint as \"LpuSectionProfile_id\",
						MS.MedService_id as \"MedService_id\",
						MS.MedService_Nick as \"MedService_Nick\",
						MS.MedService_Name as \"MedService_Name\",
						MS.MedServiceType_id as \"MedServiceType_id\",
						mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
						ms.MedService_IsExternal as \"MedService_IsExternal\",
						ms.MedService_IsLocalCMP as \"MedService_IsLocalCMP\",
						ms.MedService_LocalCMPPath as \"MedService_LocalCMPPath\",
						:pmUser_Name as \"MedPersonal_FIO\",
						Org.Org_id as \"Org_id\",
						null::bigint as \"Lpu_id\",
						Org.Org_Nick as \"Org_Nick\",
						null as \"Lpu_Nick\",
						null::bigint as \"MedicalCareKind_id\",
						null::bigint as \"PostKind_id\",
						null as \"SmpUnitType_Code\",
						null as \"SmpUnitParam_IsKTPrint\"
					from
						v_MedService MS
						left join v_Org Org on Org.Org_id = MS.Org_id
						left join v_MedServiceType mst on mst.MedServiceType_id = MS.MedServiceType_id
					where MS.Org_id = :Org_id
					  and MS.MedService_begDT <= tzGetDate()
					  and (MS.MedService_endDT >= tzGetDate() or MS.MedService_endDT is null)
					  {$filter}
				";
			} else {
				$sql = "
					select
						null::bigint as \"MedStaffFact_id\",
						MS.LpuSection_id as \"LpuSection_id\",
						:pmUser_id as \"MedPersonal_id\",
						null as \"LpuSection_Name\",
						null as \"LpuSection_Nick\",
						null as \"PostMed_Name\",
						null as \"PostMed_Code\",
						null::bigint as \"PostMed_id\",
						null::bigint as \"LpuBuilding_id\",
						null as \"LpuBuilding_Name\",
						null::bigint as \"LpuUnit_id\",
						null::bigint as \"LpuUnitSet_id\",
						null as \"LpuUnit_Name\",
						null as \"Timetable_isExists\",
						null as \"LpuUnitType_SysNick\",
						MS.LpuUnitType_id as \"LpuUnitType_id\",
						null as LpuSectionProfile_SysNick,
						null as LpuSectionProfile_Code,
						null as LpuSectionProfile_id,
						MS.MedService_id as \"MedService_id\",
						MS.MedService_Nick as \"MedService_Nick\",
						MS.MedService_Name as \"MedService_Name\",
						MS.MedServiceType_id as \"MedServiceType_id\",
						mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
						ms.MedService_IsExternal as \"MedService_IsExternal\",
						ms.MedService_IsLocalCMP as \"MedService_IsLocalCMP\",
						ms.MedService_LocalCMPPath as \"MedService_LocalCMPPath\",
						:pmUser_Name as \"MedPersonal_FIO\",
						Org.Org_id as \"Org_id\",
						null::bigint as \"Lpu_id\",
						Org.Org_Nick as \"Org_Nick\",
						null as \"Lpu_Nick\",
						null::bigint as \"MedicalCareKind_id\",
						null::bigint as \"PostKind_id\",
						null as \"SmpUnitType_Code\",
						null as \"SmpUnitParam_IsKTPrint\"
					from
						v_pmUserCacheOrg PUO
						inner join v_PersonWork PW on PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
						inner join v_Org Org on Org.Org_id = PW.Org_id
						left join v_OrgStruct OS on OS.OrgStruct_id = PW.OrgStruct_id
						inner join v_MedService MS on MS.Org_id = Org.Org_id and coalesce(MS.OrgStruct_id, 0) = coalesce(OS.OrgStruct_id, 0)
						left join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
					where PUO.Org_id = :Org_id
					  and PUO.pmUserCache_id = :pmUser_id
					  and MS.MedService_begDT <= tzGetDate()
					  and (MS.MedService_endDT >= tzGetDate() or MS.MedService_endDT is null)
					  {$filter}
				";
			}
		} else {
			$filter_medstafffact = "";
			$filter_medservicemedpersonal = "";
			$filter_medservice = "";
			$use_date = true;
			if ($use_date) {
				$filter_medstafffact = "
					and msf.WorkData_begDate::date <= tzGetDate()
					and (msf.WorkData_endDate::date >= tzGetDate() or msf.WorkData_endDate is null)
				";
				$filter_medservicemedpersonal = "
					and msmp.MedServiceMedPersonal_begDT::date <= tzGetDate()
					and (msmp.MedServiceMedPersonal_endDT::date >= tzGetDate() or msmp.MedServiceMedPersonal_endDT is null)
				";
				$filter_medservice = "
					and MS.MedService_begDT::date <= tzGetDate()
					and (MS.MedService_endDT::date >= tzGetDate() or MS.MedService_endDT is null)
				";
			}
			$filter = "";
			$params = [
				"MedPersonal_id" => $data["MedPersonal_id"],
				"Lpu_id" => $data["Lpu_id"],
				"pmUser_id" => $data["pmUser_id"],
				"pmUser_Name" => toAnsi($data["session"]["user"]),
				"Org_id" => $data["session"]["org_id"]
			];
			if ($data["MedService_id"] > 0) {
				$params["MedService_id"] = $data["MedService_id"];
				$filter = " and MedService_id = :MedService_id";
			} elseif ($data["MedStaffFact_id"] > 0) {
				$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
				$filter = " and msf.MedStaffFact_id = :MedStaffFact_id";
				if ($data["LpuSection_id"] > 0) {
					// если передано отделение, то фильтруем и по отделению
					$params["LpuSection_id"] = $data["LpuSection_id"];
					$filter .= " and ls.LpuSection_id = :LpuSection_id";
				}
			}
			if (isset($data["LpuSection_id"]) && $data["LpuSection_id"] > 0) {
				// если передано отделение, то фильтруем и по отделению
				$params["LpuSection_id"] = $data["LpuSection_id"];
			}
			if ($data["session"]["region"]["nick"] == "ufa") {
				$persisFields = "
					,null::bigint as \"MedicalCareKind_id\"
					,null::bigint as \"PostKind_id\"
				";
			} else {
				$persisFields = "
					,msf.MedicalCareKind_id as \"MedicalCareKind_id\"
					,msf.PostKind_id as \"PostKind_id\"
				";
			}
			if (empty($data["MedPersonal_id"])) {
				// для пользователей, которые не связаны с врачами, нет смысла выполнять запрос
				return false;
			} else if (!empty($data["StacPriemOnly"]) && $data["StacPriemOnly"] == 2) {
				// здесь нет выборки дополнительной информации по приемным отделениям, поэтому также нет смысла выполнять запрос
				return false;
			}
			$localFilter = "
				where msf.MedPersonal_id = :MedPersonal_id
				  and msf.MedStaffFact_Stavka > 0
				  and lpu.{$orgmsfilter}
				  {$filter_medstafffact} {$filter}
			";
			$sql_medstafffact = "
				select
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					msf.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					coalesce(ls.LpuSection_FullName, '') as \"LpuSection_Name\",
					coalesce(ls.LpuSection_Name, '') as \"LpuSection_Nick\",
					coalesce(ps.PostMed_Name, '') as \"PostMed_Name\",
					ps.PostMed_Code as \"PostMed_Code\",
					ps.PostMed_id as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					coalesce(lb.LpuBuilding_Name, '') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					coalesce(lu.LpuUnit_Name, '') as \"LpuUnit_Name\",
					case when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap')
					    then case when (select count(*) from v_TimeTableGraf_lite tt where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0
							    then 'true'
							    else 'false'
							end
						when lut.LpuUnitType_SysNick = 'parka' then case when (select count(*) from v_TimetablePar tt where msf.LpuSection_id = tt.LpuSection_id) > 0
						        then 'true'
						        else 'false'
						    end
						when lut.LpuUnitType_SysNick in ('stac', 'hstac', 'pstac', 'dstac') then
							case when (select count(*) from v_TimetableStac_lite tt where msf.LpuSection_id = tt.LpuSection_id) > 0
							    then 'true'
							    else 'false'
							end
						else 'false'
					end as \"Timetable_isExists\",
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					ls.LpuSectionAge_id as \"LpuSectionAge_id\",
					null::bigint as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null::bigint as \"MedServiceType_id\",
					null::bigint as \"MedService_IsExternal\",
					null as \"MedServiceType_SysNick\",
					msf.Person_FIO as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
					eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
					eq.ElectronicService_id as \"ElectronicService_id\",
					eq.ElectronicService_Num as \"ElectronicService_Num\",
					eq.ElectronicQueueInfo_CallTimeSec as \"ElectronicQueueInfo_CallTimeSec\",
					eq.ElectronicQueueInfo_PersCallDelTimeMin as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					eq.ElectronicQueueInfo_CallCount as \"ElectronicQueueInfo_CallCount\",
					eq.ElectronicService_isShownET as \"ElectronicService_isShownET\",
					(
						select
							string_agg(coalesce(CAST(etl.ElectronicTreatment_id as VARCHAR),''), ',')
						from v_ElectronicTreatmentLink etl
							inner join v_ElectronicQueueInfo eqio on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
					) as \"ElectronicTreatment_ids\",
					eboard.ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
					eboard.ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\",
					eboard.ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\",
					null::bigint as \"SmpUnitType_Code\",
					null::bigint as \"SmpUnitParam_IsKTPrint\",
					null::bigint as \"Storage_id\",
					null::bigint as \"Storage_pid\"
					{$persisFields}
				from
					v_MedStaffFact msf
					left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps on ps.PostMed_id = msf.Post_id
					left join lateral (
						select
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq
							left join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where mseq.MedStaffFact_id = msf.MedStaffFact_id
			    		  and eqi.ElectronicQueueInfo_IsOff = 1
					    limit 1
					) as eq on true
					left join lateral (
						select
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from
					    	v_ElectronicScoreboard ebd
							left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicService_id = eq.ElectronicService_id
						where ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
						  and ebd.ElectronicScoreboard_IsLED = 2
					    limit 1
					) as eboard on true
				{$localFilter}
			";
			$sql_workgraph = "
				select
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					ls.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					coalesce(ls.LpuSection_FullName, '') as \"LpuSection_Name\",
					coalesce(ls.LpuSection_Name, '') as \"LpuSection_Nick\",
					coalesce(ps.PostMed_Name, '') as \"PostMed_Name\",
					ps.PostMed_Code as \"PostMed_Code\",
					ps.PostMed_id as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					coalesce(lb.LpuBuilding_Name, '') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					coalesce(lu.LpuUnit_Name, '') as \"LpuUnit_Name\",
					case
						when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then
							case when (select count(*) from v_TimeTableGraf_lite tt where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick = 'parka' then
							case when (select count(*) from v_TimetablePar tt where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac') then
							case when (select count(*) from v_TimetableStac_lite tt where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						else 'false'
					end as \"Timetable_isExists\",
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					ls.LpuSectionAge_id as \"LpuSectionAge_id\",
					null::bigint as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null::bigint as \"MedServiceType_id\",
					null::bigint as \"MedService_IsExternal\",
					null as \"MedServiceType_SysNick\",
					msf.Person_FIO as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
					null::bigint as \"ElectronicQueueInfo_id\",
					null::bigint as \"ElectronicService_id\",
					null as \"ElectronicService_Num\",
					null as \"ElectronicQueueInfo_CallTimeSec\",
					null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					null as \"ElectronicQueueInfo_CallCount\",
					null as \"ElectronicService_isShownET\",
					null as \"ElectronicTreatment_ids\",
					null::bigint as \"ElectronicScoreboard_id\",
					null as \"ElectronicScoreboard_IPaddress\",
					null as \"ElectronicScoreboard_Port\",
					null::bigint as \"SmpUnitType_Code\",
					null::bigint as \"SmpUnitParam_IsKTPrint\",
					null::bigint as \"Storage_id\",
					null::bigint as \"Storage_pid\"
					{$persisFields}
				from
					v_MedStaffFact msf
					left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
					inner join v_WorkGraph WG on WG.MedStaffFact_id = msf.MedStaffFact_id and WG.WorkGraph_begDT::date <= tzGetDate() and WG.WorkGraph_endDT::date >= tzGetDate()
					left join v_WorkGraphLpuSection WGLS on WGLS.WorkGraph_id = WG.WorkGraph_id
					left join v_LpuSection ls on ls.LpuSection_id = WGLS.LpuSection_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps on ps.PostMed_id = msf.Post_id
				where msf.MedPersonal_id = :MedPersonal_id
				  and msf.Lpu_id = :Lpu_id
				  {$filter}
			";
			$localFilter = "
				where
					lpu.{$orgmsfilter}
					{$filter_medservice} {$filter}
					and msmp.MedPersonal_id = :MedPersonal_id
					and mst.MedServiceType_SysNick in ('HTM', 'vk', 'mse', 'lab', 'pzm', 'func', 'patb', 'mstat', 'prock', 'dpoint', 'ooa', 'merch', 'pmllo', 'regpol', 'sprst', 'okadr', 'minzdravdlo', 'leadermo', 'mekllo', 'spesexpertllo', 'adminllo', 'touz', 'reglab', 'oper_block', 'smp', 'slneotl', 'konsult', 'foodserv', 'vac', 'epidem_mo', 'remoteconsultcenter','smpdispatchstation','forenbiodprtwithmolgenlab','forenchemdprt','medforendprt', 'forenhistdprt', 'organmethdprt', 'forenmedcorpsexpdprt', 'forenmedexppersdprt', 'commcomplexp', 'forenareadprt', 'lvn', 'smpheaddoctor', 'zmk', 'rpo', 'spec_mz', 'medosv', 'reanimation', 'microbiolab')
			";
			$sql_medservice = "
				select
					case
						when mst.MedServiceType_SysNick = 'reanimation' then (
							select t1.MedStaffFact_id
							from
								v_MedStaffFact t1
								inner join dbo.v_LpuUnit t2 on t2.LpuUnit_id = t1.LpuUnit_id
							where t1.MedPersonal_id = msmp.MedPersonal_id
							  and t1.WorkData_endDate is null
							  and t2.LpuUnitType_SysNick in ('stac', 'dstac', 'hstac', 'pstac', 'priem')
							limit 1
						) else null
					end as \"MedStaffFact_id\",
					MS.LpuSection_id as \"LpuSection_id\",
					msmp.MedPersonal_id as \"MedPersonal_id\",
					coalesce(ls.LpuSection_FullName, '') as \"LpuSection_Name\",
					coalesce(ls.LpuSection_Name, '') as \"LpuSection_Nick\",
					null as \"PostMed_Name\",
					null as \"PostMed_Code\",
					null::bigint as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					coalesce(lb.LpuBuilding_Name, '') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					coalesce(lu.LpuUnit_Name, '') as \"LpuUnit_Name\",
					null as \"Timetable_isExists\",
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					MS.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					ls.LpuSectionAge_id as \"LpuSectionAge_id\",
					MS.MedService_id as \"MedService_id\",
					MS.MedService_Nick as \"MedService_Nick\",
					MS.MedService_Name as \"MedService_Name\",
					MS.MedServiceType_id as \"MedServiceType_id\",
					ms.MedService_IsExternal as \"MedService_IsExternal\",
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
					mp.Person_FIO as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					null as \"MedStaffFactCache_IsDisableInDoc\",
					eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
					eq.ElectronicService_id as \"ElectronicService_id\",
					eq.ElectronicService_Num as \"ElectronicService_Num\",
					eq.ElectronicQueueInfo_CallTimeSec as \"ElectronicQueueInfo_CallTimeSec\",
					eq.ElectronicQueueInfo_PersCallDelTimeMin as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					eq.ElectronicQueueInfo_CallCount as \"ElectronicQueueInfo_CallCount\",
					eq.ElectronicService_isShownET as \"ElectronicService_isShownET\",
					(
						select
							string_agg(coalesce(CAST(etl.ElectronicTreatment_id as VARCHAR), ''), ',')
						from v_ElectronicTreatmentLink etl
							inner join v_ElectronicQueueInfo eqio on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
					) as \"ElectronicTreatment_ids\",
					eboard.ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
					eboard.ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\",
					eboard.ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\",
					sut.SmpUnitType_Code as \"SmpUnitType_Code\",
					sup.SmpUnitParam_IsKTPrint as \"SmpUnitParam_IsKTPrint\",
					strg.Storage_id as \"Storage_id\",
					strg.Storage_pid as \"Storage_pid\",
					null::bigint as \"MedicalCareKind_id\",
					msf.PostKind_id as \"PostKind_id\"
				from
					v_MedService MS
					inner join lateral (
						select msmp.MedPersonal_id
						from v_MedServiceMedPersonal msmp
						where msmp.MedService_id = MS.MedService_id
						  and msmp.MedPersonal_id = :MedPersonal_id
						  {$filter_medservicemedpersonal}
						limit 1
					) as msmp on true
					left join lateral (
						select msf.PostKind_id
						from v_MedStaffFact msf
						where msf.MedPersonal_id = msmp.MedPersonal_id
						  and msf.LpuSection_id = ms.LpuSection_id
					    limit 1
					) as msf on true
					left join lateral (
						select
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq
							left join v_MedServiceMedPersonal msmp2 on msmp2.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
							left join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where msmp2.MedPersonal_id = msmp.MedPersonal_id
						  and msmp2.MedService_id = MS.MedService_id
			    		  and eqi.ElectronicQueueInfo_IsOff = 1
						limit 1
					) as eq on true
					left join lateral (
						select
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from
							v_ElectronicScoreboard ebd
							left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicService_id = eq.ElectronicService_id
						where ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
						  and ebd.ElectronicScoreboard_IsLED = 2
						limit 1
					) as eboard on true
					left join v_MedPersonal mp on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = MS.Lpu_id
					left join v_Lpu lpu on lpu.Lpu_id = MS.Lpu_id
					left join v_LpuSection ls on ls.LpuSection_id = MS.LpuSection_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb on lb.LpuBuilding_id = coalesce(ls.LpuBuilding_id, MS.LpuBuilding_id)
					left join lateral (
						select *
						from v_SmpUnitParam sup
						where sup.LpuBuilding_id = lb.LpuBuilding_id
						order by sup.SmpUnitParam_id desc
					    limit 1
					) as sup on true
					left join v_SmpUnitType sut on sut.SmpUnitType_id = sup.SmpUnitType_id
					left join v_LpuUnit lu on lu.LpuUnit_id = coalesce(ls.LpuUnit_id, MS.LpuUnit_id)
					left join v_LpuUnitType lut on lut.LpuUnitType_id = coalesce(lu.LpuUnitType_id, MS.LpuUnitType_id)
					left join v_MedServiceType mst on mst.MedServiceType_id = MS.MedServiceType_id
					left join lateral (
						select
							i_s.Storage_id,
							i_s.Storage_pid
						from
							v_StorageStructLevel i_ssl
							left join v_Storage i_s on i_s.Storage_id = i_ssl.Storage_id
						where i_ssl.MedService_id = MS.MedService_id
						order by i_ssl.StorageStructLevel_id
						limit 1
					) as strg on true
				{$localFilter}
			";
			$sql_medstafffact_linked = "
				select
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					msf.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					coalesce(ls.LpuSection_FullName, '') as \"LpuSection_Name\",
					coalesce(ls.LpuSection_Name, '') as \"LpuSection_Nick\",
					coalesce(ps.PostMed_Name, '') as \"PostMed_Name\",
					ps.PostMed_Code as \"PostMed_Code\",
					ps.PostMed_id as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					coalesce(lb.LpuBuilding_Name, '') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					coalesce(lu.LpuUnit_Name, '') as \"LpuUnit_Name\",
					case when (select count(*) from v_TimeTableGraf_lite tt where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0
					    then 'true'
						else 'false'
					end as \"Timetable_isExists\",
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					ls.LpuSectionAge_id as \"LpuSectionAge_id\",
					null::bigint as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null::bigint as \"MedServiceType_id\",
					null::bigint as \"MedService_IsExternal\",
					null as \"MedServiceType_SysNick\",
					msf.Person_FIO as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					msfl.MedStaffFactLink_id as \"MedStaffFactLink_id\",
					to_char(msfl.MedStaffFactLink_begDT, '{$callObject->dateTimeForm104}') as \"MedStaffFactLink_begDT\",
					to_char(msfl.MedStaffFactLink_endDT, '{$callObject->dateTimeForm104}') as \"MedStaffFactLink_endDT\",
					msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
					null::bigint as \"ElectronicQueueInfo_id\",
					null::bigint as \"ElectronicService_id\",
					null as \"ElectronicService_Num\",
					null as \"ElectronicQueueInfo_CallTimeSec\",
					null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					null as \"ElectronicQueueInfo_CallCount\",
					null as \"ElectronicService_isShownET\",
					null as \"ElectronicTreatment_ids\",
					null::bigint as \"ElectronicScoreboard_id\",
					null as \"ElectronicScoreboard_IPaddress\",
					null as \"ElectronicScoreboard_Port\",
					null::bigint as \"SmpUnitType_Code\",
					null::bigint as \"SmpUnitParam_IsKTPrint\",
					null::bigint as \"Storage_id\",
					null::bigint as \"Storage_pid\"
					{$persisFields}
				from
					v_MedStaffFactLink msfl
					inner join v_MedStaffFact msf on msf.MedStaffFact_id = msfl.MedStaffFact_id
					inner join v_MedStaffFact mmsf on mmsf.MedStaffFact_id = msfl.MedStaffFact_sid
					left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps on ps.PostMed_id = msf.Post_id
				where
					mmsf.MedPersonal_id = :MedPersonal_id
					and msf.Lpu_id = :Lpu_id
					and msf.MedStaffFact_Stavka > 0
					and lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap')
					{$filter_medstafffact} {$filter}
			";
			if ($data["MedService_id"] > 0) {
				$sql = $sql_medservice;
			} elseif ($data["MedStaffFact_id"] > 0) {
				$sql = "{$sql_medstafffact} union all {$sql_workgraph} union all {$sql_medstafffact_linked}";
			} else {
				$sql = "{$sql_medstafffact} union all {$sql_workgraph} union all {$sql_medservice} union all {$sql_medstafffact_linked}";
			}
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMedStaffFact(User_model $callObject, $data)
	{
		if (!isset($data['MedPersonal_id'])) {
			return true;
		}
		$sql = "
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				coalesce(ls.LpuSection_FullName, '')||', '||coalesce(lu.LpuUnit_Name, '')||' ) ' as \"MedStaffFact_Name\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as \"mp_is_zav\",
				case when (select count(*) from v_MedStaffRegion msr where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as \"mp_is_uch\"
			from
				v_MedStaffFact msf
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_PostMed ps on ps.PostMed_id = msf.Post_id
			where msf.MedPersonal_id = :MedPersonal_id
			  and msf.Lpu_id = :Lpu_id
		";
		$sqlParams = [
			"MedPersonal_id" => $data["MedPersonal_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $pmUser_id
	 * @return array
	 */
	public static function getMedStaffFactsBypmUser(User_model $callObject, $pmUser_id)
	{
		$res = [];
		$sql = "
			select msf.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_MedStaffFact msf
				inner join v_pmUser pu on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
			where pu.pmUser_id = :pmUser_id
			union
			select msf.MedStaffFact_id as \"MedStaffFact_id\"
			from v_MedStaffFact msf
			where MedPersonal_id in (
				select msf1.MedPersonal_id
				from
					v_MedStaffFact msf
					inner join MedStaffFactLink msfl on msf.MedStaffFact_id = msfl.MedStaffFact_sid
					inner join v_MedStaffFact msf1 on msf1.MedStaffFact_id = msfl.MedStaffFact_id
					inner join v_pmUser pu on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
				where pu.pmUser_id = :pmUser_id
			)
		";
		$sqlParams = ["pmUser_id" => $pmUser_id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (is_object($result)) {
			$result = $result->result("array");
			foreach ($result as $row) {
				$res[] = $row["MedStaffFact_id"];
			}
		}
		return $res;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getMedStaffFactData(User_model $callObject, $data)
	{
		if (!isset($data["MedStaffFact_id"]) || $data["MedStaffFact_id"] == "") {
			return true;
		}
		$sql = "
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				pm.PostMed_id as \"PostMed_id\",
				pm.PostMed_Code as \"PostMed_Code\",
				LpuSection_FullName||' ( '||LpuUnit_Name||' ) ' as \"MedStaffFact_Name\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
				lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				msf.Person_FIO as \"MedPersonal_FIO\"
			from
				v_MedStaffFact msf
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
				left join v_PostMed pm on pm.PostMed_id = msf.Post_id
			where msf.MedStaffFact_id = :MedStaffFact_id
		";
		/**@var CI_DB_result $result */
		$sqlParams = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return $result[0];
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function getHeadNursePost(User_model $callObject, $data)
	{
		$params = [
			"MedPersonal_id" => $data["MedPersonal_id"],
			"Lpu_id" => $data["Lpu_id"],
		];
		$query = "
			select 1 as \"true\"
			from v_MedStaffFact msf
			where msf.Lpu_id = :Lpu_id
			  and (msf.WorkData_endDate is null or msf.WorkData_endDate >= tzgetdate())
			  and msf.MedPersonal_id = :MedPersonal_id
			  and msf.Post_id in (10501, 4, 10261)
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (count($result) > 0) ? $result[0] : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getLLOData(User_model $callObject, $data)
	{
		$params = [
			"MedPersonal_id" => $data["MedPersonal_id"],
			"Lpu_id" => $data["Lpu_id"],
		];
		$filter = "
				mp.Lpu_id = :Lpu_id
			and (mp.WorkData_endDate is null or mp.WorkData_endDate >= dbo.tzGetDate())
			and mp.MedPersonal_id = :MedPersonal_id
		";
		switch ($data["session"]["region"]["nick"]) {
			case "saratov":
				$hasActiveDlo = false;
				break;
			case "perm":
				$hasActiveDlo = true;
				break;
			default:
				$filter .= " and mp.WorkData_IsDlo = 2";
				$hasActiveDlo = false;
				break;
		}
		if ($hasActiveDlo) {
			$query = "
				select
					null as \"MedStaffFact_id\",
					msf.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					ls.LpuSection_FullName as \"LpuSection_Name\",
					ls.LpuSection_Name as \"LpuSection_Nick\",
					PostMed.PostMed_Name as \"PostMed_Name\",
					PostMed.PostMed_Code as \"PostMed_Code\",
					PostMed.PostMed_id as \"PostMed_id\",
					LpuUnit.LpuUnit_Name as \"LpuUnit_Name\",
					null as \"Timetable_isExists\",
					LpuUnit.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					LpuUnit.LpuUnitType_id as \"LpuUnitType_id\",
					ls.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					null as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null as \"MedServiceType_id\",
					null as \"MedServiceType_SysNick\",
					msf.Person_Fio as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					msf.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					Lpu.Lpu_Nick as \"Org_Nick\"
				from
					v_MedStaffFact msf
					inner join v_Lpu Lpu on msf.Lpu_id = Lpu.Lpu_id
					inner join v_MedPersonal mp on msf.MedPersonal_id = mp.MedPersonal_id and msf.Lpu_id = mp.Lpu_id
					inner join v_LpuSection ls on msf.LpuSection_id = ls.LpuSection_id
					left join v_PostMed PostMed on msf.Post_id = PostMed.PostMed_id
					left join v_LpuUnit LpuUnit on msf.LpuUnit_id = LpuUnit.LpuUnit_id
				where msf.Lpu_id = :Lpu_id
				  and msf.MedPersonal_id = :MedPersonal_id
				  and mp.WorkData_IsDlo = 2
				  and msf.WorkData_dlobegDate <= dbo.tzGetDate()
				  and (msf.WorkData_dloendDate is null or msf.WorkData_dloendDate >= dbo.tzGetDate())
				limit 1
			";
		} else {
			$query = "
				select
					null as \"MedStaffFact_id\",
					null as \"LpuSection_id\",
					mp.MedPersonal_id as \"MedPersonal_id\",
					null as \"LpuSection_Name\",
					null as \"LpuSection_Nick\",
					null as \"PostMed_Name\",
					null as \"PostMed_Code\",
					null as \"PostMed_id\",
					null as \"LpuUnit_Name\",
					null as \"Timetable_isExists\",
					null as \"LpuUnitType_SysNick\",
					null as \"LpuUnitType_id\",
					null as \"LpuSectionProfile_SysNick\",
					null as \"LpuSectionProfile_Code\",
					null as \"LpuSectionProfile_id\",
					null as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null as \"MedServiceType_id\",
					null as \"MedServiceType_SysNick\",
					mp.Person_Fio as \"MedPersonal_FIO\",
					mp.Lpu_id as \"Lpu_id\",
					lpu.Org_id as \"Org_id\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					Lpu.Lpu_Nick as \"Org_Nick\"
				from
					v_MedPersonal mp
					inner join v_Lpu Lpu on mp.Lpu_id = Lpu.Lpu_id
				where {$filter}
				limit 1
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (count($result) > 0) ? $result[0] : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMedPersonalBySocCardNum(User_model $callObject, $data)
	{
		if (!isset($data["soccard_id"]) || strlen($data["soccard_id"]) < 25) {
			return true;
		}
		$sql = "
			select MedPersonal_id as \"MedPersonal_id\"
			from MedPersonalCache
			where left(MedPersonal_SocCardNum, 19) = ?
			order by WorkData_begdate
			limit 1
		";
		$sqlParams = [substr($data["soccard_id"], 0, 19)];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getMedPersonalByIin(User_model $callObject, $data)
	{
		$query = "
			select mp.MedPersonal_id as \"MedPersonal_id\"
			from
				v_MedPersonal mp
				inner join v_PersonState ps on ps.Person_id = mp.Person_id
			where ps.Person_Inn = :Person_Inn
			order by WorkData_begdate
			limit 1
		";
		$queryParams = ["Person_Inn" => $data["Person_Inn"]];
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMedPersonalByFIODR(User_model $callObject, $data)
	{
		if (!isset($data["surName"]) || !isset($data["firName"]) || !isset($data["secName"]) || !isset($data["birthDay"]) || !isset($data["polisNum"])) {
			return true;
		}
		$sql = "
			select MedPersonal_id as \"MedPersonal_id\"
			from
				MedPersonalCache
				left join v_PersonState ps on ps.Person_id = MedPersonalCache.Person_id
			where ps.Person_surName = :surName
			  and ps.Person_firName = :firName
			  and ps.Person_secName = :secName
			  and ps.Person_birthDay = :birthDay
			  and ps.polis_num = :polisNum
			order by WorkData_begdate
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCurrentOrgFarmacyData(User_model $callObject, $data)
	{
		$sql = "
			select
				rtrim(ofr.OrgFarmacy_Nick) as \"OrgFarmacy_Nick\",
				ofr.Org_id as \"Org_id\",
				o.Org_Name as \"Org_Name\"
			from
				v_OrgFarmacy ofr
				left join v_Org o on o.Org_id = ofr.Org_id
			where ofr.OrgFarmacy_id = :OrgFarmacy_id
		";
		$sqlParams = ["OrgFarmacy_id" => $data["session"]["OrgFarmacy_id"]];
		if (isset($data["session"]["OrgFarmacy_id"])) {
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $sqlParams);
			return (is_object($result)) ? $result->result("array") : false;
		}
		return true;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCurrentOrgFarmacyContragent(User_model $callObject, $data)
	{
		$query = "
			select
				Contragent_id as \"Contragent_id\",
				rtrim(Contragent_Name) as \"Contragent_Name\",
				Org_id as \"Org_id\",
				Org_pid as \"Org_pid\",
				ContragentType_SysNick as \"ContragentType_SysNick\"
			from
				v_Contragent c
				left join v_ContragentType ct on ct.ContragentType_id = c.ContragentType_id
			where c.OrgFarmacy_id = :OrgFarmacy_id
			  and (
			      	(
			      	    select
			      	    	case when OrgType_Code = 11 then 5
			      	    	     when OrgType_Code = 5 then 6
			      	    	     when OrgType_Code = 16 then 1
			      	    	     when OrgType_Code = 4 then 3
			      	    	     else null
			      	    	end
			      		from
							v_OrgFarmacy ofr
							left join v_Org o on o.Org_id = ofr.Org_id
							left join v_OrgType ot on ot.OrgType_id = o.OrgType_id
						where ofr.OrgFarmacy_id = :OrgFarmacy_id
					) is null or
			      	ct.ContragentType_Code = (
				        select
							case when OrgType_Code = 11 then 5
								 when OrgType_Code = 5 then 6
								 when OrgType_Code = 16 then 1
								 when OrgType_Code = 4 then 3
								 else null
							end
						from
							v_OrgFarmacy ofr
							left join v_Org o on o.Org_id = ofr.Org_id
							left join v_OrgType ot on ot.OrgType_id = o.OrgType_id
						where ofr.OrgFarmacy_id = :OrgFarmacy_id
			    	)
			  )
			order by Lpu_id, Contragent_id
			limit 1
		";
		$queryParams = ["OrgFarmacy_id" => $data["session"]["OrgFarmacy_id"]];
		if (empty($data["session"]["OrgFarmacy_id"])) {
			return true;
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return ((is_object($result))) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCurrentOrgContragent(User_model $callObject, $data)
	{
		$query = "
			select
				Contragent_id as \"Contragent_id\",
				rtrim(Contragent_Name) as \"Contragent_Name\",
				Org_id as \"Org_id\",
				Org_pid as \"Org_pid\",
                ContragentType_SysNick as \"ContragentType_SysNick\"
			from
				v_Contragent c
				left join v_ContragentType ct on ct.ContragentType_id = c.ContragentType_id
			where c.Org_id = :Org_id
			  and (
			      	(
						select
							case
								when OrgType_Code in (11, 14) then 5
								when OrgType_Code = 5 then 6
								when OrgType_Code = 16 then 1
								when OrgType_Code = 4 then 3
								else null
							end
						from
							v_Org o
							left join v_OrgType ot on ot.OrgType_id = o.OrgType_id
						where o.Org_id = :Org_id
					) is null or
					ct.ContragentType_Code = (
						select
							case
								when OrgType_Code in (11, 14) then 5
								when OrgType_Code = 5 then 6
								when OrgType_Code = 16 then 1
								when OrgType_Code = 4 then 3
								else null
							end
						from
							v_Org o
							left join v_OrgType ot on ot.OrgType_id = o.OrgType_id
						where o.Org_id = :Org_id
					)
				)
			order by
				Lpu_id, Contragent_id
			limit 1
		";
		$queryParams = ["Org_id" => $data["session"]["org_id"]];
		if (empty($data["session"]["org_id"])) {
			return true;
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getNetAdminFarmacies(User_model $callObject, $data)
	{
		$sql = "
			select distinct
				ct.OrgFarmacy_id as \"OrgFarmacy_id\"
			from
				v_Contragent ct
				inner join	v_OrgFarmacy ofr on ct.OrgFarmacy_id = ofr.OrgFarmacy_id
			where ct.Org_pid = :Org_pid
		";
		$sqlParams = ["Org_pid" => $data["session"]["OrgNet_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		/**@var CI_DB_result $result */
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCurrentLpuName(User_model $callObject, $data)
	{
		$sql = "
			select
				rtrim(L.Lpu_Nick) as \"Lpu_Nick\",
				rtrim(L.Lpu_Name) as \"Lpu_Name\",
				coalesce(L.PAddress_Address, '') as \"Lpu_Address\",
				coalesce(L.UAddress_Address, '') as \"Lpu_UAddress\",
				coalesce(RTRIM(PS.Person_SurName), '') as \"Person_Surname\",
				coalesce(RTRIM(PS.Person_FirName), '') as \"Person_Firname\",
				coalesce(RTRIM(PS.Person_SecName), '') as \"Person_Secname\"
			from
				v_Lpu L
				left join v_OrgHead OH on OH.Lpu_id = L.Lpu_id and OH.OrgHeadPost_id = 1
				left join v_PersonState PS on PS.Person_id = OH.Person_id
			where L.Lpu_id = :Lpu_id
		";
		$sqlParams = ["Lpu_id" => $data["Lpu_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		/**@var CI_DB_result $result */
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $org
	 * @return array|bool
	 */
	public static function getUsersInOrg(User_model $callObject, $org)
	{
		$filter = "1 = 1";
		if (isset($org) && count($org) > 0) {
			$orgString = implode(",", $org);
			$filter = "puco.Org_id in ({$orgString})";
		}
		$query = "
			select distinct
				puc.PMUser_id as \"PMUser_id\"
			from
				pmUserCache puc
				left join pmUserCacheOrg puco on puco.pmUserCache_id = puc.pmUser_id
			where {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение данных для дерева фильтрации в форме просмотра групп
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getGroupTree(User_model $callObject, $data)
	{
		switch ($data["level"]) {
			case 0:
				$response = [
					["id" => "All", "text" => "Все", "iconCls" => "group_all16", "leaf" => true],
					["id" => "Blocked", "text" => "Заблокированные", "iconCls" => "group_blocked16", "leaf" => true],
				];
				return $response;
				break;
			case 1:
				if ($data["node"] == "Org") {
					$query = "
						select
							Lpu_id as \"id\",
							'lpu16' as \"iconCls\",
							'true' as \"leaf\",
							Lpu_Nick as \"text\"
						from v_Lpu
						where Lpu_id = :Lpu_id or :Lpu_id is null
						order by Lpu_Nick
					";
					if (isSuperAdmin()) {
						$data["Lpu_id"] = null;
					}
					$result = $callObject->db->query($query, $data);
					if (!is_object($result)) {
						return false;
					}
					$response = $result->result("array");
					return $response;
				}
				return false;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Получение списка объектов и ролей для определенного типа объекта
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getObjectRoleList(User_model $callObject, $data)
	{
		$list = $callObject->getObjectList($data, true);
		$role = pmAuthGroups::loadRole($data["Role_id"]);
		if ((count($role) > 0) && isset($role[$data["node"]])) {
			$c = $callObject->getObjectActionsList($data);
			for ($i = 0; $i < count($list); $i++) {
				if (isset($role[$data["node"]][$list[$i]["id"]])) {
					$actions = $role[$data["node"]][$list[$i]["id"]];
					foreach ($actions as $key => $val) {
						if (isset($list[$i]["actions"]) && (!in_array($key, ["view", "edit"])) && !in_array($key, $list[$i]["actions"])) {
							$list[$i][$key] = "hidden";
						} else {
							if ($val === "hidden") {
								$val = 0;
							}
							$list[$i][$key] = $val;
						}
					}
				}
				for ($ii = 0; $ii < count($c); $ii++) {
					$key = $c[$ii]["id"];
					if (!array_key_exists($key, $list[$i])) {
						$list[$i][$key] = (isset($list[$i]["actions"]) && (!in_array($key, ["view", "edit"])) && !in_array($key, $list[$i]["actions"])) ? "hidden" : false;
					}
				}
				unset($list[$i]["actions"]);
			}
		} else {
			$c = $callObject->getObjectActionsList($data);
			for ($i = 0; $i < count($list); $i++) {
				for ($ii = 0; $ii < count($c); $ii++) {
					$key = $c[$ii]["id"];
					$list[$i][$key] = (isset($list[$i]["actions"]) && (!in_array($key, ["view", "edit"])) && !in_array($key, $list[$i]["actions"])) ? "hidden" : false;
				}
				unset($list[$i]["actions"]);
			}
		}
		return $list;
	}

	/**
	 * Возвращает простой массив разрешенных акшенов по общему массиву всех групп из LDAP (пример формата возвращаемого файла: array('swAboutAction', 'swExitAction'))
	 * @param $roles
	 * @return array
	 */
	public static function getSimpleMenuActions($roles)
	{
		$simple = [];
		foreach ($roles as $k => $v) {
			if (isset($v["access"]) && ($v["access"] == 1)) {
				$simple[] = $k;
			}
		}
		return $simple;
	}

	/**
	 * Возвращает список всех акшенов меню для формы просмотра и редактирования роли
	 * @param User_model $callObject
	 * @return array|string
	 */
	public static function getMenusList(User_model $callObject)
	{
		/**
		 * @param $menu
		 * @param $lvl
		 * @param $actions
		 * @param $group
		 * @return array|string
		 */
		function get($menu, $lvl, $actions, $group)
		{
			foreach ($menu as $k => $v) {
				if (is_array($v)) {
					if (isset($v["action"])) {
						$actions[] = ["id" => $v["action"], "code" => $v["action"], "name" => $v["text"], "group" => $group];
					} else {
						if (isset($v["text"]) && ($v["text"] != "-")) {
							$newgroup = "";
							if (strlen($group) > 0) {
								$newgroup = " / ";
							}
							$newgroup = $group . $newgroup . $v["text"];
						}
						if (isset($v["menu"])) {
							$actions = get($v["menu"], $lvl + 1, $actions, $newgroup);
						}
						if (in_array($k, ["menu_normal", "menu_advanced"])) {
							$actions = get($v, $lvl + 1, $actions, $newgroup);
						}
					}
				}
			}
			return $actions;
		}

		$callObject->load->helper("Config");
		$callObject->load->helper("Options");
		// выбираем установленное меню
		$menu = filetoarray(APPPATH . "config/menu.php");
		$menu = $menu["menu_normal"];
		if (count($menu) > 0) {
			// Формирование меню с наложением прав на существующее меню
			return get($menu, 0, [], "");

		}
		return [];
	}

	/**
	 * Получение списка объектов для определенного типа объекта
	 * @param User_model $callObject
	 * @param $data
	 * @param bool $list
	 * @return array|bool
	 */
	public static function getObjectList(User_model $callObject, $data, $list = true)
	{
		switch ($data["node"]) {
			case "menus":
				$menu = $callObject->getMenusList();
				if (count($menu) > 0) {
					return $menu;
				}
				return false;
				break;
			case "windows":
				$callObject->load->helper("Config");
				$files = filetoarray(APPPATH . "config/files.php");
				if (count($files) > 0) {
					$r = [];
					$f = [];
					foreach ($files as $key => $value) {
						if (isset($files[$key]["path"])) {
							$f = $value;
						} elseif (isset($value[$_SESSION["region"]["nick"]])) {
							$f = $value[$_SESSION["region"]["nick"]];
						} elseif (isset($value["default"])) {
							$f = $value["default"];
						}
						if ($list) {
							$r[] = [
								"id" => $key,
								"code" => $key,
								"name" => (isset($f["title"])) ? $f["title"] : [],
								"actions" => (isset($f["actions"])) ? $f["actions"] : [],
								"group" => (isset($f["group"])) ? $f["group"] : null,
								"region" => (isset($f["region"])) ? $f["region"] : null,
								"iconCls" => "windows16",
								"path" => $f["path"]
							];
						} else
							$r[] = [
								"id" => $key,
								"code" => $key,
								"text" => $f["title"],
								"iconCls" => "windows16",
								"path" => $f["path"],
								"leaf" => false
							];
					}
					return $r;
				}
				return false;
				break;
			default:
				break;
		}
		return false;
	}

	/**
	 * Получение данных для дерева выбора типа объекта для фильтрации
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getObjectTree(User_model $callObject, $data)
	{
		// В зависимости от уровня получаем разные данные
		switch ($data["level"]) {
			case 0:
				// Для первого запроса это просто список
				$response = $callObject->getObjectType($data);
				foreach ($response as $key => $val) {
					$response[$key]["leaf"] = true;
				}
				return $response;
				break;
			case 1:
				return $callObject->getObjectList($data, false);
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Получение списка всех доступных типов объектов
	 * @param $data
	 * @return array
	 */
	public static function getObjectType($data)
	{
		return [
			["id" => "menus", "text" => "Меню", "iconCls" => "object16"],
			["id" => "windows", "text" => "Окна", "iconCls" => "object16"],
			["id" => "actions", "text" => "Действия", "iconCls" => "object16"]
		];
	}

	/**
	 * Получение списка всех доступных типов акшенов
	 * @return array
	 */
	public static function getActionType()
	{
		return [
			["id" => "access", "text" => "Доступ"],
			["id" => "view", "text" => "Просмотр"],
			["id" => "add", "text" => "Добавление"],
			["id" => "edit", "text" => "Изменение"],
			["id" => "delete", "text" => "Удаление"],
			["id" => "import", "text" => "Импорт"],
			["id" => "export", "text" => "Экспорт"],
			["id" => "run", "text" => "Запуск"],
			["id" => "sign", "text" => "Подписание"],
			["id" => "print", "text" => "Печать"]
		];
	}

	/**
	 * Возвращает список разрешенных типов акшенов для определенного типа оъекта
	 * @param $objecttype
	 * @return array|bool
	 */
	public static function getObjectActionType($objecttype)
	{
		if ($objecttype == "menus") {
			return ["access"];
		} elseif ($objecttype == "windows") {
			return ["view", "add", "edit", "delete", "import", "export"];
		} elseif ($objecttype == "actions") {
			return ["access"];
		}
		return false;
	}

	/**
	 * Возвращает список разрешенных типов акшенов для определенного оъекта
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getObjectActionsList(User_model $callObject, $data)
	{
		$approved = $callObject->getObjectActionType($data["node"]);
		$actions = [];
		foreach ($actiontypes = $callObject->getActionType() as $key => $val) {
			if (in_array($actiontypes[$key]["id"], $approved)) {
				$actions[] = $actiontypes[$key];
			}
		}
		return $actions;
	}

	/**
	 * Формирует заголовок для грида для определенного типа оъекта
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getObjectHeaderList(User_model $callObject, $data)
	{
		$callObject->load->helper("Config");
		$actions = $callObject->getObjectActionsList($data);
		switch ($data["node"]) {
			case "menus":
				$r = ["id" => "Id", "code" => "Код", "name" => "Название пункта меню"];
				foreach ($actions as $key => $val) {
					$r[$val["id"]] = $val["text"];
				}
				return [$r];
				break;
			case "windows":
				$r = ["id" => "Id", "code" => "Код", "name" => "Название формы ввода"];
				foreach ($actions as $key => $val) {
					$r[$val["id"]] = $val["text"];
				}
				return [$r];
				break;
			default:
				break;
		}
		return false;
	}

	/**
	 * выводит список организаций пользователя через запятую..
	 * @param User_model $callObject
	 * @param $pmUser_id
	 * @return string
	 */
	public static function getOrgsByUser(User_model $callObject, $pmUser_id)
	{
		$query = "
			select string_agg(o.Org_Nick, ', ') as orgs
			from
				v_pmUserCacheOrg puco
				left join v_Org o on o.Org_id = puco.Org_id
			where puco.pmUserCache_id = :pmUser_id
			group by o.Org_Nick
			order by o.Org_Nick
		";
		$queryParams = ["pmUser_id" => $pmUser_id];
		$res = $callObject->db->query($query, $queryParams);
		if (is_object($res)) {
			$resp = $res->result("array");
			if (count($resp) > 0) {
				return $resp[0]["orgs"];
			}
		}
		return "";
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUsersList(User_model $callObject, $data)
	{
		$filter = [];
		$params = [];
		if (!empty($data["OrgType_id"])) {
			$filter[] = "exists(
					select puco.pmUserCacheOrg_id
					from
						v_pmUserCacheOrg puco
						inner join v_Org o on o.Org_id = puco.Org_id
					where o.OrgType_id = :OrgType_id
					  and puco.pmUserCache_id = puc.PMUser_id
				)
			";
			$params["OrgType_id"] = rtrim($data["OrgType_id"]);
		}
		if ($data["org"] !== null) {
			if ($data["org"] == "deleted" && !isSuperadmin() && !isLpuAdmin()) {
				return false;
			} else if ($data["org"] == "0" && !isSuperadmin()) {
				return false;
			}
			if ($data["org"] == "deleted" || (isset($data["pmUser_deleted"]) && $data["pmUser_deleted"] == "deleted")) {
				$filter[] = "coalesce(pmUser_deleted, 1) = 2";
			} else {
				$filter[] = "coalesce(pmUser_deleted, 1) = 1";
			}
			if ($data["org"] == "0") {
				$filter[] = "
					not exists(
						select puco.pmUserCacheOrg_id
						from v_pmUserCacheOrg puco
						where puco.pmUserCache_id = puc.PMUser_id
					)
				";
			}
			if ($data["org"] == "farmnetadmin") {
				$filter[] = "puc.pmUser_groups ilike '%\"FarmacyNetAdmin\"%'";
			}
			if (is_numeric($data["org"]) && $data["org"] > 0) {
				$filter[] = "
					exists(
						select puco.pmUserCacheOrg_id
						from v_pmUserCacheOrg puco
						where puco.Org_id = {$data["org"]}
						  and puco.pmUserCache_id = puc.PMUser_id
					)
				";
			}
		}
		if (!isSuperadmin() && !defined("CRON")) {
			$sessionOrgsString = implode(",", $data["session"]["orgs"]);
			$filter[] = "
				exists(
					select puco.pmUserCacheOrg_id
					from v_pmUserCacheOrg puco
					where puco.Org_id in ({$sessionOrgsString})
					  and puco.pmUserCache_id = puc.PMUser_id
				)
			";
		}
		if (!empty($data["login"])) {
			$filter[] = "puc.PMUser_Login ilike :PMUser_Login||'%'";
			$params["PMUser_Login"] = $data["login"];
		}
		if (!empty($data["group"])) {
			$filter[] = "puc.pmUser_groups ilike '%\"'||:group||'\"%'";
			$params["group"] = $data["group"];
		}
		if (!empty($data["pmUser_surName"])) {
			$filter[] = "puc.PMUser_surName ilike :pmUser_surName||'%'";
			$params["pmUser_surName"] = rtrim($data["pmUser_surName"]);
		}
		if (!empty($data["pmUser_firName"])) {
			$filter[] = "puc.PMUser_firName ilike :pmUser_firName||'%'";
			$params["pmUser_firName"] = rtrim($data["pmUser_firName"]);
		}
		if (!empty($data["pmUser_desc"])) {
			$filter[] = "puc.pmUser_desc ilike '%'||:pmUser_desc||'%'";
			$params["pmUser_desc"] = rtrim($data["pmUser_desc"]);
		}
		if (!empty($data["pmUser_Blocked"])) {
			$filter[] = "coalesce(puc.pmUser_Blocked, 0) = :pmUser_Blocked";
			$params["pmUser_Blocked"] = $data["pmUser_Blocked"] == 2 ? 1 : 0;
		}
		$filterString = implode(" and ", $filter);
		if(trim($filterString) != "") {
			$filterString = "
				where
				-- where
					{$filterString}
				-- end where
			";
		}
		$query = "
			select
			-- select
				puc.PMUser_id as \"pmUser_id\",
				rtrim(puc.PMUser_Login) as \"login\",
				rtrim(puc.PMUser_surName) as \"PMUser_surName\",
				rtrim(puc.PMUser_firName) as \"PMUser_firName\",
				rtrim(puc.PMUser_secName) as \"PMUser_secName\",
				rtrim(puc.PMUser_Name) as \"PMUser_Name\",
				case when coalesce(puc.PMUser_Blocked, 0) = 1
					then 'true'
					else 'false'
				end as \"PMUser_Blocked\",
				case when puc.MedPersonal_id is not null
					then 'true'
					else 'false'
				end as \"IsMedPersonal\",
				puc.pmUser_groups as \"groups\",
				puc.pmUser_desc as \"pmUser_desc\",
				puc.Lpu_id as \"Lpu_id\"
			-- end select
			from
			-- from
				pmUserCache puc
			-- end from
			{$filterString}
			order by
			-- order by
				puc.PMUser_Login
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		if (!empty($data["withoutPaging"]) && $data["withoutPaging"]) {
			$result = $callObject->db->query($query, $params);
			return (is_object($result)) ? $result->result("array") : [];
		} else {
			$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
			$result_count = $callObject->db->query(getCountSQLPH($query), $params);
			if (is_object($result_count)) {
				$cnt_arr = $result_count->result("array");
				$count = $cnt_arr[0]["cnt"];
				unset($cnt_arr);
			} else {
				$count = 0;
			}
			if (!is_object($result)) {
				return false;
			}
			$response = [];
			$response["data"] = $result->result("array");
			foreach ($response["data"] as &$oneres) {
				if (!empty($oneres["groups"])) {
					$arr = [];
					$groups = json_decode($oneres["groups"]);
					foreach ($groups as $group) {
						$arr[] = $group->name;
					}
					$oneres["groups"] = implode(",", $arr);
				}
				$oneres["orgs"] = $callObject->getOrgsByUser($oneres["pmUser_id"]);
			}
			$response["totalCount"] = $count;
			return $response;
		}
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUsersListOfCache(User_model $callObject, $data)
	{
		$filter = [];
		$params = [];
		if (!empty($data["Org_id"])) {
			$filter[] = "
				exists(
					select puco.pmUserCacheOrg_id
					from v_pmUserCacheOrg puco
					where puco.Org_id = :Org_id
					  and puco.pmUserCache_id = puc.PMUser_id
				)
			";
			$params["Org_id"] = $data["Org_id"];
		}
		if (!empty($data["login"])) {
			$filter[] = "puc.PMUser_Login ilike :PMUser_Login||'%'";
			$params["PMUser_Login"] = $data["login"];
		}
		if (!empty($data["desc"])) {
			$filter[] = "puc.PMUser_desc ilike '%'||:PMUser_desc||'%'";
			$params["PMUser_desc"] = $data["desc"];
		}
		if (!empty($data["Person_SurName"])) {
			$filter[] = "puc.PMUser_surName ilike :Person_SurName||'%'";
			$params["Person_SurName"] = rtrim($data["Person_SurName"]);
		}
		if (!empty($data["Person_FirName"])) {
			$filter[] = "puc.PMUser_firName ilike :Person_FirName||'%'";
			$params["Person_FirName"] = rtrim($data["Person_FirName"]);
		}
		if (!empty($data["Person_SecName"])) {
			$filter[] = "puc.PMUser_secName ilike :Person_SecName||'%'";
			$params["Person_SecName"] = rtrim($data["Person_SecName"]);
		}
		if (!empty($data["group"])) {
			$query = "
				select pmUserCacheGroup_id
				from v_pmUserCacheGroup
				where pmUserCacheGroup_SysNick = :pmUserCacheGroup_SysNick
			";
			$queryParams = ["pmUserCacheGroup_SysNick" => $data["group"]];
			$params["pmUserCacheGroup_id"] = $callObject->getFirstResultFromQuery($query, $queryParams, true);
			$filter[] = "
				exists(
					select pucgl.pmUserCacheGroupLink_id
					from v_pmUserCacheGroupLink pucgl
					where pucgl.pmUserCacheGroup_id = :pmUserCacheGroup_id
					  and pucgl.pmUserCache_id = puc.PMUser_id
				)
			";
			$params["group"] = $data["group"];
		}
		$filter[] = "coalesce(pmUser_deleted, 1) <> 2";
		$filterString = implode(" and ", $filter);
		if(trim($filterString) != "") {
			$filterString = "
				where
				-- where
					{$filterString}
				-- end where
			";
		}
		$query = "
			select
			-- select
				puc.PMUser_id as \"pmUser_id\",
				puc.PMUser_Login as \"login\",
				puc.PMUser_surName as \"surname\",
				puc.PMUser_firName as \"name\",
				puc.PMUser_secName as \"secname\",
				case when puc.MedPersonal_id is not null
					then 1
					else 0
				end as \"IsMedPersonal\",
				puc.pmUser_groups as \"groups\",
				puc.pmUser_desc as \"desc\"
			-- end select
			from
			-- from
				pmUserCache puc
			-- end from
			{$filterString}
			order by
			-- order by
				puc.PMUser_surName
			-- end order by
		";
		return $callObject->getPagingResponse($query, $params, $data["start"], $data["limit"], true);
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetARMSOnReport(User_model $callObject, $data)
	{
		$params = [];
		if ($data["action"] == "add") {
			if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
				$subQuery = "
					select RCPL.ReportContentParameterLink_id
					from 
						rpt.v_ReportContentParameterLink RCPL
					where 
						RCPL.ReportContentParameter_id = :ReportContentParameter_id
						and RCPL.ARMType_id = AT.ARMType_id
				";
				$params['ReportContentParameter_id'] = $data['ReportContentParameter_id'];
			} else {
				$subQuery = "
			        select RA.ReportARM_id
                    from 
                    	rpt.v_ReportARM RA
                    where 
                    	RA.ARMType_id = AT.ARMType_id
                    	and RA.Report_id = :Report_id
			    ";
			    $params['Report_id'] = $data["Report_id"];
        	}
        		
            $query = "
                select AT.ARMType_id as \"ARMType_id\"
                from v_ARMType AT
                where not exists ({$subQuery})
            ";
		} else if ($data["action"] == "remove") {


        	if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
				$query = "
					select RCPL.ReportContentParameterLink_id as \"ReportContentParameterLink_id\"
					from rpt.v_ReportContentParameterLink RCPL
					where RCPL.ReportContentParameter_id = :ReportContentParameter_id
				";
				$params['ReportContentParameter_id'] = $data['ReportContentParameter_id'];
			} else {
				$query = "
			        select RA.ReportARM_id as \"ReportARM_id\"
			        from rpt.v_ReportARM RA
			        where RA.Report_id = :Report_id
			    ";
			    $params['Report_id'] = $data["Report_id"];
        	}
		} else {
			return false;
		}
		$params["Report_id"] = $data["Report_id"];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getARMsAccessOnReport(User_model $callObject, $data)
	{

		$fields = '';
		$join = '';
		$queryParams = array();
		if (!empty($data['ReportContentParameter_id'])) {
			$query = "
				select
					AT.ARMType_id as \"ARMType_id\",
					AT.ARMType_Code as \"ARMType_Code\",
					AT.ARMType_Name || '(' || AT.ARMType_SysNick || ')' as \"ARMType_Name\",
					:ReportContentParameter_id as \"ReportContentParameter_id\",
					R.ReportContentParameterLink_id as \"ReportContentParameterLink_id\",
					null as \"ReportARM_id\",
					null as \"Report_id\",
					case when R.ReportContentParameterLink_id is not null then 1 else 0 end as \"isAccess\"
				from
					v_ARMType AT
					left join rpt.v_ReportContentParameterLink R on R.ARMType_id = AT.ARMType_id and R.ReportContentParameter_id = :ReportContentParameter_id
			";

			$queryParams['ReportContentParameter_id'] = $data['ReportContentParameter_id'];

		} else {
			$query = "
				select
					AT.ARMType_id as \"ARMType_id\",
					AT.ARMType_Code as \"ARMType_Code\",
					AT.ARMType_Name || '(' || AT.ARMType_SysNick || ')' as \"ARMType_Name\",
					:Report_id as \"Report_id\",
					R.ReportARM_id as \"ReportARM_id\",
					null as \"ReportContentParameterLink_id\",
					null as \"ReportContentParameter_id\",
					case when R.ReportARM_id is not null then 1 else 0 end as \"isAccess\"
				from
					v_ARMType AT
					left join rpt.v_ReportARM R on R.ARMType_id = AT.ARMType_id and R.Report_id = :Report_id
			";
			$queryParams['Report_id'] = $data['Report_id'];
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result))?$result->result("array"):false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getARMinDB(User_model $callObject, $data)
	{
		$filter = [];
		$queryParams = [];
		if (isset($data["ARMType_id"]) && $data["ARMType_id"] > 0) {
			$queryParams["ARMType_id"] = $data["ARMType_id"];
			$filter[] = "ARMType_id = :ARMType_id";
		}
		if (isset($data["ARMType_Code"]) && $data["ARMType_Code"] > 0) {
			$queryParams["ARMType_Code"] = $data["ARMType_Code"];
			$filter[] = "ARMType_Code = :ARMType_Code";
		}
		$filterString = (count($filter) != 0) ? "where " . implode(" and ", $filter) : "";
		$query = "
			select
				ARMType_id as \"ARMType_id\",
			    ARMType_Code as \"ARMType_Code\",
			    ARMType_Name||' ('||ARMType_SysNick||')' as \"ARMType_Name\",
			    ARMType_SysNick as \"ARMType_SysNick\"
			from v_ARMType
			{$filterString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUsersWithInvalidMedPersonalId(User_model $callObject, $data)
	{
		$query = "
			select
				PMUser_Login as \"PMUser_Login\",
			    mold.medpersonal_id as \"medpersonal_id\",
			    m.id as \"id\"
			from
				pmUserCache
				inner join tmp.MedPersonalD mold on mold.MedPersonal_id = pmUserCache.MedPersonal_id
				inner join persis.MedWorker m on m.person_id=mold.person_id
			where m.id <>mold.MedPersonal_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param null $Org_id
	 * @return array|bool
	 */
	public static function getCurrentOrgUsersList(User_model $callObject, $Org_id = null)
	{
		$query = "
			select distinct
				uc.pmUser_id as \"pmUser_id\",
			    uc.pmUser_Name as \"pmUser_Fio\",
			    uc.pmUser_Login as \"pmUser_Login\"
			from
				pmUserCache uc
				inner join pmUserCacheOrg uco on uco.pmUserCache_id = uc.pmUser_id
			where uco.Org_id = :Org_id
		";
		$queryParams = ["Org_id" => $Org_id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUserSessions(User_model $callObject, $data)
	{
		$params = [];
		$filter = [];
		if (isset($data["Login_Range"][0])) {
			$filter[] = "to_char(us.LoginTime, '{$callObject->dateTimeForm120}') >= :Login_Range_0";
			$params["Login_Range_0"] = $data["Login_Range"][0];
		}
		if (isset($data["Login_Range"][1])) {
			$filter[] = "to_char(us.LoginTime, '{$callObject->dateTimeForm120}') <= :Login_Range_1";
			$params["Login_Range_1"] = $data["Login_Range"][1];
		}
		if (isset($data["Logout_Range"][0])) {
			$filter[] = "to_char(us.LogoutTime, '{$callObject->dateTimeForm120}') >= :Logout_Range_0";
			$params["Logout_Range_0"] = $data["Logout_Range"][0];
		}
		if (isset($data["Logout_Range"][1])) {
			$filter[] = "to_char(us.LogoutTime, '{$callObject->dateTimeForm120}') <= :Logout_Range_1";
			$params["Logout_Range_1"] = $data["Logout_Range"][1];
		}
		if (isset($data["PMUser_Name"])) {
			$filter[] = "pu.PMUser_Name ilike '%{$data["PMUser_Name"]}%'";
		}
		if (isset($data["PMUser_Login"])) {
			$filter[] = "(pu.PMUser_Login like '%{$data["PMUser_Login"]}%' or us.Login like '%{$data["PMUser_Login"]}%')";
		}
		if (isset($data["IsMedPersonal"])) {
			$filter[] = ($data["IsMedPersonal"] == 2) ? "pu.MedPersonal_id is not null" : "pu.MedPersonal_id is null";
		}
		if (isset($data["IP"])) {
			$filter[] = "us.IP = :IP";
			$params["IP"] = $data["IP"];
		}
		if (isset($data["AuthType_id"])) {
			$filter[] = "us.AuthType_id = :AuthType_id";
			$params["AuthType_id"] = $data["AuthType_id"];
		}
		if (isset($data["Status"])) {
			$filter[] = "us.Status = :Status";
			$params["Status"] = $data["Status"];
		}
		if (isset($data["onlyActive"]) && $data["onlyActive"]) {
			$filter[] = "us.LogoutTime is null";
		}
		//$defaultdatabase = $callObject->load->database("default", true)->database;
		$defaultdatabase = 'promed';
		$mainDb = "{$defaultdatabase}";
		$dblink = $callObject->config->item("UserSessionDBLink");
		if (!empty($dblink)) {
			$mainDb = "{$dblink}.{$defaultdatabase}.dbo";
		}
		else{
			if(getRegionNick() == 'ufa'){
				$mainDb = "{$defaultdatabase}.dbo";
			}
		}
		if (isSuperadmin()) {
			if (!empty($data["Org_id"])) {
				$filter[] = "
					pu.PMUser_id in (
						select puco.pmUserCache_id
						from promed.pmUserCacheOrg puco
						where puco.Org_id = :Org_id
					)
				";
				$params["Org_id"] = $data["Org_id"];
			}
		} else {
			if (!empty($data["userOrg_id"])) {
				$filter[] = "
					pu.PMUser_id in (
						select puco.pmUserCache_id
						from promed.pmUserCacheOrg puco
						where puco.Org_id = :userOrg_id
					)
				";
				$params["userOrg_id"] = $data["userOrg_id"];
			}
		}
		if (!empty($data["PMUserGroup_Name"])) {
			$filter[] = "pu.PMUser_groups ilike '%\"'||:PMUserGroup_Name||'\"%'";
			$params["PMUserGroup_Name"] = $data["PMUserGroup_Name"];
		}
		$whereString = (count($filter) != 0)?implode(" and ", $filter):"";
		if(trim($whereString) != "") {
			$whereString = "
	            where
	            -- where
					{$whereString}
				-- end where
			";
		}

		$params['day'] = date('Y-m-d');

		$query = "
			select
            	-- select
              	replace(
              	    replace(
              	        replace(
              	            replace(to_char(us.LoginTime, '{$callObject->dateTimeForm121}'), '-', ''),
              	            ':', ''),
              	        '.', ''),
              	    ' ', ''
              	) as \"Unic_id\",
            	us.Session_id as \"Session_id\",
            	us.IP as \"IP\",
              	pu.PMUser_id as \"PMUser_id\",
              	LTRIM(RTRIM(pu.PMUser_Name)) as \"PMUser_Name\",
              	to_char(us.LoginTime, '{$callObject->dateTimeForm120}') as \"LoginTime\",
              	to_char(us.LogoutTime, '{$callObject->dateTimeForm120}') as \"LogoutTime\",
              	case
              	    when us.Status = 1 THEN datediff('ss', us.LoginTime, coalesce(us.LogoutTime, getdate()))
              	    else null
              	end as \"WorkTime\",
              	case when us.Status = '1' then 'удачный вход' else 'неудачный вход' end as \"Status\",
              	case when us.Status = '1' then 1 else 2 end as \"Status_id\",
				case
               		when us.AuthType_id = '1' then 'по логину/паролю'
               		when us.AuthType_id = '2' then 'по соцкарте'
               		when us.AuthType_id = '3' then 'через УЭК'
                   	when us.AuthType_id = '4' then 'через ЭЦП'
                   	when us.AuthType_id = '5' then 'через ЕСИА'
					else ''
               	end as \"AuthType_id\",
              	pu.PMUser_Login as \"PMUser_Login\",
              	case when pu.MedPersonal_id is not null
              	    then 'true' else 'false'
              	end as \"IsMedPersonal\",
              	(SELECT COUNT(*) FROM UserSessions
					WHERE pmUser_id = us.pmUser_id
					AND LoginTime > :day
					AND LogoutTime IS NULL) as \"ParallelSessions\"
            	-- end select
        	from
           	-- from
				UserSessions us
           		left join promed.pmUserCache pu on us.pmUser_id = pu.pmUser_id
        	-- end from
			{$whereString}
        	order by
        	-- order by
            us.LoginTime desc
			-- end order by
        ";
		$response = $callObject->getPagingResponse($query, $params, $data["start"], $data["limit"], true);
		if (is_array($response) && array_key_exists("data", $response)) {
			foreach ($response["data"] as $k => $row) {
				if (!empty($row["WorkTime"])) {
					$wt = "";
					$s = $row["WorkTime"];
					$m = 0;
					$h = 0;
					$d = 0;
					if ($row["WorkTime"] < 60) {
						$wt = "меньше минуты";
					} else {
						// дни
						$d = floor($s / 86400);
						$s = $s - ($d * 86400);
						// часы
						$h = floor($s / 3600);
						$s = $s - ($h * 3600);
						// минуты
						$m = floor($s / 60);

						if ($d > 0) {
							$wt .= $d . "д ";
						}
						if ($h > 0) {
							$wt .= $h . "ч ";
						}
						if ($m > 0) {
							$wt .= $m . "м ";
						}
					}
					$response["data"][$k]["WorkTime"] = $wt;
				}
			}
		}
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @param $login
	 * @return int
	 */
	public static function getBlockedFromUserCache(User_model $callObject, $login)
	{
		$params = ["login" => $login];
		$query = "
			select coalesce(PMUser_Blocked, 0) as \"PMUser_Blocked\"
			from pmUserCache
			where PMUser_Login = :login
			limit 1
		";
		$rs = 0;
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (is_object($result)) {
			$response = $result->result("array");
			if (count($response) > 0) {
				$rs = $response[0]["PMUser_Blocked"];
			}
		}
		return $rs;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUserGroups(User_model $callObject, $data)
	{
		$params = ["pmUser_id" => $data["pmUser_id"]];
		$query = "
			select
				pmUserCacheGroup.pmUserCacheGroup_id as \"Group_id\",
				pmUserCacheGroup_Name as \"Group_Desc\",
				pmUserCacheGroup_SysNick as \"Group_Name\"
			from
				pmUserCacheGroup
				left join pmUserCacheGroupLink on pmUserCacheGroupLink.pmUserCacheGroup_id = pmUserCacheGroup.pmUserCacheGroup_id
			where pmUserCacheGroupLink.pmUserCache_id = :pmUser_id
			  and pmUserCacheGroup.pmUserCacheGroup_id != 19
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @return array|bool
	 */
    public static function getGroupsDB(User_model $callObject)
	{
		$query = "
			select
				pmUserCacheGroup_id as id,
				pmUserCacheGroup_SysNick as name,
				trim(pmUserCacheGroup_Name) as desc,
                pmUserCacheGroup_isonly as isonly,
                pmUserCacheGroup_IsBlocked as isblocked
			from
			    pmUserCacheGroup
			where
			    pmUserCacheGroup_id != 19
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		return (is_object($result)) ? $result->result() : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuList(User_model $callObject, $data = []){
		$filter = array();
		$join = array();

		if(!empty($data['MedServiceType_SysNick'])) {
			$filter[] = "MST.MedServiceType_SysNick = :MedServiceType_SysNick";

			$join[] = "left join v_MedService MS on MS.Lpu_id = Lpu.Lpu_id
					left join v_MedServiceType MST on MS.MedServiceType_id = MST.MedServiceType_id";
		}

		$sql = "
			with lpu_ids as (
				SELECT distinct
					Lpu.Lpu_id
				FROM v_Lpu Lpu
					". (count($join) > 0 ? implode(' ', $join) : "") ."
				where ".(count($filter) > 0 ? implode(' and ', $filter) : " 1=1"). "
			)
			select
				Lpu.Lpu_id as \"Lpu_id\",
				Lpu.Org_id as \"Org_id\",
				Lpu.Org_tid as \"Org_tid\",
				Lpu.Lpu_IsOblast as \"Lpu_IsOblast\",
				RTRIM(Lpu.Lpu_Name) as \"Lpu_Name\",
				RTRIM(Lpu.Lpu_Nick) as \"Lpu_Nick\",
				Lpu.Lpu_Ouz as \"Lpu_Ouz\",
				Lpu.Lpu_RegNomC as \"Lpu_RegNomC\",
				Lpu.Lpu_RegNomC2 as \"Lpu_RegNomC2\",
				Lpu.Lpu_RegNomN2 as \"Lpu_RegNomN2\",
				Lpu.Lpu_isDMS as \"Lpu_isDMS\",
				adr.Address_Nick as \"Address\",
				to_char(Lpu.Lpu_DloBegDate, 'dd.mm.yyyy') as \"Lpu_DloBegDate\",
				to_char(Lpu.Lpu_DloEndDate, 'dd.mm.yyyy') as \"Lpu_DloEndDate\",
				to_char(Lpu.Lpu_BegDate, 'dd.mm.yyyy') as \"Lpu_BegDate\",
				to_char(Lpu.Lpu_EndDate, 'dd.mm.yyyy') as \"Lpu_EndDate\",
				coalesce(LpuLevel.LpuLevel_Code, 0) as \"LpuLevel_Code\",
				coalesce(Org.Org_IsAccess, 1) as \"Lpu_IsAccess\",
				coalesce(Org.Org_IsNotForSystem, 1) as \"Lpu_IsNotForSystem\",
				coalesce(Lpu.Lpu_IsMse, 1) as \"Lpu_IsMse\"
			from lpu_ids
				inner join v_Lpu Lpu on Lpu.Lpu_id = lpu_ids.Lpu_id
				inner join v_Org Org on Org.Org_id = Lpu.Org_id
				left join LpuLevel on LpuLevel.LpuLevel_id = Lpu.LpuLevel_id
				left join v_Address adr on Org.UAddress_id = adr.Address_id
			where
				Lpu.Lpu_endDate is null
		";

		$result = $callObject->db->query($sql,$data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
}
