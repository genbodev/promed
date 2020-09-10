<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс для справок о стоимости лечения
 *
 * @package				CostPrint
 * @copyright			Copyright (c) 2014 Swan Ltd.
 * @author				Dmitriy Vlasenko
 * @link				http://swan.perm.ru/PromedWeb
 *
 * @property CI_DB_driver $db
 */
class CostPrint_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $numericForm17_2 = "FM99999999999999999.00";

	/**
	 * Получение данных для справки
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public function getCostPrintData($data)
	{
		$ddata = ["Evn_setDate" => null];
		if (!empty($data["Evn_id"])) {
			// проверяем, что случай закончен
			$EvnClass_SysNick = $this->getFirstResultFromQuery("select EvnClass_SysNick from v_Evn where Evn_id = :Evn_id", $data);
			switch ($EvnClass_SysNick) {
				case "EvnPL":
					$query = "
						select e.EvnPL_id as \"Evn_id\"
						from v_EvnPL e
						where e.EvnPL_IsFinish = 2
						  and e.EvnPL_id = :Evn_id
					";
					break;
				case "EvnPLStom":
					$query = "
						select
							e.EvnPLStom_id as \"Evn_id\",
							to_char(e.EvnPLStom_setDate, '{$this->dateTimeForm104}') as \"Evn_setDate\"
						from v_EvnPLStom e
						where e.EvnPLStom_IsFinish = 2
						  and e.EvnPLStom_id = :Evn_id
					";
					break;
				case "EvnPS":
					$addquery = "";
					if ($data["session"]["region"]["nick"] != "perm") {
						$addquery .= " and LT.LeaveType_Code not in (5, 104, 204)";
					}
					$query = "
						select e.EvnPS_id as \"Evn_id\"
						from
							v_EvnPS e
							inner join v_LeaveType LT on e.LeaveType_id = LT.LeaveType_id
						where e.EvnPS_id = :Evn_id
						{$addquery}
						union all
					";
					// https://redmine.swan.perm.ru/issues/52840
					// https://redmine.swan.perm.ru/issues/76713
					if (in_array($data["session"]["region"]["nick"], ["buryatiya", "pskov"])) {
						$unionAll = "union all";
						$query .= "
							select e.EvnSection_pid as \"Evn_id\"
							from
								v_EvnSection e
								inner join v_LeaveType lt on lt.LeaveType_id = e.LeaveType_prmid
							where e.EvnSection_pid = :Evn_id
							  and coalesce(e.EvnSection_IsPriem, 1) = 2
							  and lt.LeaveType_SysNick in ('osmpp', 'otk')
							{$unionAll}
						";
					}
					$query .= "
						select e.EvnPS_id as \"Evn_id\"
						from v_EvnPS e
						where e.PrehospWaifRefuseCause_id is not null
						  and e.EvnPS_id = :Evn_id
					";
					break;
			}
			if (!empty($query)) {
				/**@var CI_DB_result $result */
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result("array");
					if (empty($resp[0]["Evn_id"])) {
						throw new Exception("Нельзя напечатать справку о стоимости лечения, т.к. случай не закончен");
					}
					if (isset($resp[0]["Evn_setDate"])) {
						$ddata["Evn_setDate"] = $resp[0]["Evn_setDate"];
					}
				}
			}

			$query = "
				select
					dps.Person_id as \"Person_pid\",
					case when dps.Person_id is not null
					    then coalesce(dps.Person_SurName, '')||' '||coalesce(dps.Person_FirName, '')||' '||coalesce(dps.Person_SecName, '')
					    else ''
					end as \"Person_Pred\",
					to_char(coalesce(ECP.EvnCostPrint_setDT, tzgetdate()), '{$this->dateTimeForm104}') as \"CostPrint_setDT\",
					extract(year from coalesce(e.Evn_disDate, e.Evn_setDate)) as \"Cost_Year\"
				from
					v_Evn e
					left join lateral (
						select
							EvnCostPrint_setDT,
							Person_id
						from v_EvnCostPrint
						where Evn_id = e.Evn_id
						limit 1
					) as ECP on true
					left join v_PersonDeputy pd on pd.Person_id = e.Person_id
					left join v_PersonState dps on dps.Person_id = (case when ECP.EvnCostPrint_setDT is not null then ECP.Person_id else pd.Person_pid end)
				where e.Evn_id = :Evn_id
				limit 1
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result("array");
				$resp[0]["Error_Msg"] = "";
				$resp[0]["Evn_setDate"] = $ddata["Evn_setDate"];
				return $resp[0];
			}
		} elseif (!empty($data["CmpCallCard_id"])) {
			$query = "
				select
					dps.Person_id as \"Person_pid\",
					case when dps.Person_id is not null
					    then coalesce(dps.Person_SurName, '')||' '||coalesce(dps.Person_FirName, '')||' '||coalesce(dps.Person_SecName, '')
					    else ''
					end as \"Person_Pred\",
					to_char(coalesce(CCP.CmpCallCardCostPrint_setDT, tzgetdate())::date, '{$this->dateTimeForm104}') as \"CostPrint_setDT\",
					extract(year from ccc.CmpCallCard_prmDT) as \"Cost_Year\"
				from
					v_CmpCallCard ccc
					left join v_PersonDeputy pd on pd.Person_id = ccc.Person_id
					left join v_PersonState dps on dps.Person_id = pd.Person_pid
					left join lateral (
						select CmpCallCardCostPrint_setDT
						from v_CmpCallCardCostPrint
						where CmpCallCard_id = ccc.CmpCallCard_id
					    limit 1
					) as CCP on true
				where ccc.CmpCallCard_id = :CmpCallCard_id
				limit 1
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result("array");
				$resp[0]["Error_Msg"] = "";
				return $resp[0];
			}
		} elseif (!empty($data["Person_id"])) {
			$query = "
				select
					dps.Person_id as \"Person_pid\",
					case when dps.Person_id is not null
					    then coalesce(dps.Person_SurName, '')||' '||coalesce(dps.Person_FirName, '')||' '||coalesce(dps.Person_SecName, '')
					    else ''
					end as \"Person_Pred\",
					to_char(tzgetdate(), '{$this->dateTimeForm104}') as \"CostPrint_setDT\"
				from
					v_PersonState ps
					left join v_PersonDeputy pd on pd.Person_id = ps.Person_id
					left join v_PersonState dps on dps.Person_id = pd.Person_pid
				where ps.Person_id = :Person_id
				limit 1
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result("array");
				$resp[0]["Error_Msg"] = "";
				$resp[0]["Evn_setDate"] = $ddata["Evn_setDate"];
				return $resp[0];
			}
		}
		return false;
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function saveCostPrint($data)
	{
		if (empty($data["Evn_id"]) && empty($data["CmpCallCard_id"]) && (empty($data["Person_id"]) || empty($data["CostPrint_begDate"]) || empty($data["CostPrint_endDate"]))) {
			throw new Exception("Неверно указаны параметры печати справки");
		}
		if (!empty($data["Evn_id"])) {
			// сохранение выдачи справки для конкретного случая
			$this->saveEvnCostPrint($data);
			return ["Error_Msg" => ""];
		} else if (!empty($data["CmpCallCard_id"])) {
			// сохранение выдачи справки для конкретной карты СМП
			$this->saveCmpCallCardCostPrint($data);
			return ["Error_Msg" => ""];
		} else if (!empty($data["Person_id"]) && !empty($data["CostPrint_begDate"]) && !empty($data["CostPrint_endDate"])) {
			// сохранение выдачи справки для случаев из периода
			$query = "
				select e.EvnPS_id as \"Evn_id\"
				from
					v_EvnPS e
					inner join v_LeaveType LT on e.LeaveType_id = LT.LeaveType_id
					inner join v_PayType pt on pt.PayType_id = e.PayType_id and pt.PayType_SysNick = 'oms'
				where e.Lpu_id = :Lpu_id
				  and e.Person_id = :Person_id
				  and e.EvnPS_disDate between :CostPrint_begDate and :CostPrint_endDate
				  and LT.LeaveType_Code not in (5, 104, 204)
				union
				select e.EvnPL_id as \"Evn_id\"
				from
					v_EvnPL e
					inner join v_EvnVizitPL ev on ev.EvnVizitPL_pid = e.EvnPL_id
					inner join v_PayType pt on pt.PayType_id = ev.PayType_id and pt.PayType_SysNick = 'oms'
				where e.Lpu_id = :Lpu_id
				  and e.EvnPL_IsFinish = 2
				  and e.Person_id = :Person_id
				  and e.EvnPL_disDate between :CostPrint_begDate and :CostPrint_endDate
				union
				select e.EvnPLStom_id as \"Evn_id\"
				from
					v_EvnPLStom e
					inner join v_EvnVizitPLStom ev on ev.EvnVizitPLStom_pid = e.EvnPLStom_id
					inner join v_PayType pt on pt.PayType_id = ev.PayType_id and pt.PayType_SysNick = 'oms'
				where e.Lpu_id = :Lpu_id
				  and e.EvnPLStom_IsFinish = 2
				  and e.Person_id = :Person_id
				  and e.EvnPLStom_disDate between :CostPrint_begDate and :CostPrint_endDate
				union
				select e.EvnPLDisp_id as \"Evn_id\"
				from v_EvnPLDisp e
				where e.Lpu_id = :Lpu_id
				  and e.EvnPLDisp_IsFinish = 2
				  and e.Person_id = :Person_id
				  and e.EvnPLDisp_disDate between :CostPrint_begDate and :CostPrint_endDate
			";
			if ($data["session"]["region"]["nick"] == "perm") {
				$query .= "
					union
					select e.EvnUslugaPar_id as \"Evn_id\"
					from v_EvnUslugaPar e
					where e.Lpu_id = :Lpu_id
					  and e.Person_id = :Person_id
					  and e.EvnUslugaPar_setDate between :CostPrint_begDate and :CostPrint_endDate
				";
			}
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result("array");
				foreach ($resp as $respone) {
					$data["Evn_id"] = $respone["Evn_id"];
					$this->saveEvnCostPrint($data);
				}
			}
			$addfilter = "";
			if ($data["session"]["region"]["nick"] != "ufa") {
				$addfilter = " and ccc.MedPersonal_id is not null";
			}
			$query = "
				select ccc.CmpCallCard_id as \"CmpCallCard_id\"
				from v_CmpCallCard ccc
				where ccc.Lpu_id = :Lpu_id
				  and ccc.Person_id = :Person_id
				  and ccc.CmpCallCard_prmDT between :CostPrint_begDate and :CostPrint_endDate
				  and ccc.Person_id is not null
				  {$addfilter}
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result("array");
				foreach ($resp as $respone) {
					$data["CmpCallCard_id"] = $respone["CmpCallCard_id"];
					$this->saveCmpCallCardCostPrint($data);
				}
			}
			return ["Error_Msg" => ""];
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Установка параметра
	 * @param $data
	 * @return array|bool
	 */
	public function setCostParameter($data)
	{
		if (!in_array($data["object"], ["EvnCostPrint", "CmpCallCardCostPrint"])) {
			return false;
		}
		if (!in_array($data["param_name"], ["EvnCostPrint_setDT", "EvnCostPrint_IsNoPrint", "CmpCallCardCostPrint_setDT", "CmpCallCardCostPrint_IsNoPrint"])) {
			return false;
		}
		if (in_array($data["param_name"], ["EvnCostPrint_setDT", "CmpCallCardCostPrint_setDT"])) {
			$data["param_value"] = date("Y-m-d", strtotime($data["param_value"]));
		}
		$query = "
			update {$data['object']}
			set {$data['param_name']} = :param_value
			where {$data['object']}_id = :id
		";
		$this->db->query($query, $data);
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение номера справки
	 * @param $data
	 * @return array|bool
	 */
	public function getEvnCostPrintNumber($data)
	{
		$query = "
			select *
			from xp_genpmid(
			    objectname := 'EvnCostPrint',
			    lpu_id := :Lpu_id
			);    
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение факта печати справки о стоимости для случаев
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public function saveEvnCostPrint($data)
	{
		if (in_array(getRegionNumber(), [19])) {
			//Если справке не присвоен номер - присваиваем, пока только для Хакасии
			$query = "
				select EvnCostPrint_Number as \"EvnCostPrint_Number\"
				from v_EvnCostPrint
				where Evn_id = :Evn_id
			";
			$data["EvnCostPrint_Number"] = $this->getFirstResultFromQuery($query, $data);
			if (empty($data["EvnCostPrint_Number"])) {
				$evncostprintnum = $this->getEvnCostPrintNumber($data);
				if (!is_array($evncostprintnum) || count($evncostprintnum) != 1 || empty($evncostprintnum[0]["EvnCostPrint_Number"])) {
					return false;
				}
				$data["EvnCostPrint_Number"] = $evncostprintnum[0]["EvnCostPrint_Number"];
			}
		} else {
			$data["EvnCostPrint_Number"] = null;
		}
		if (empty($data["CostPrint_IsNoPrint"])) {
			$data["CostPrint_IsNoPrint"] = 1;
		}
		if (empty($data["Person_IsPred"]) || $data["Person_IsPred"] != 1 || empty($data["Person_pid"])) {
			$data["Person_pid"] = null;
		}
		$data["CostPrint_Cost"] = 0;
		// получаем стоимость лечения
		$regionnumber = getRegionNumber();
		$query = "select EvnClass_SysNick from v_Evn where Evn_id = :Evn_id";
		$data["EvnClass_SysNick"] = $this->getFirstResultFromQuery($query, $data);
		$query = "
			select extract(year from coalesce(Evn_disDate, Evn_setDate)) as \"Cost_Year\"
			from v_Evn
			where Evn_id = :Evn_id
		";
		$data["Cost_Year"] = $this->getFirstResultFromQuery($query, $data);
		$sumfield = "";
		switch ($data["EvnClass_SysNick"]) {
			case "EvnFuncRequest":
				$and = "";
				if ($data["session"]["region"]["nick"] == "kareliya") {
					$and = "
						and eup.EvnUslugaPar_setDT is not null
						and ecp.EvnCostPrint_setDT is null
					";
				}
				// сохраняем справки для всех дочерних услуг.
				$query = "
					select eup.EvnUslugaPar_id as \"EvnUslugaPar_id\"
					from
						v_EvnFuncRequest efr
						inner join v_EvnUslugaPar eup on efr.EvnFuncRequest_pid = eup.EvnUslugaPar_id
						left join v_EvnCostPrint ecp on ecp.Evn_id = eup.EvnUslugaPar_id
					where efr.EvnFuncRequest_id = :Evn_id
						{$and}
					union
					select eup.EvnUslugaPar_id as \"EvnUslugaPar_id\"
					from
						v_EvnFuncRequest efr
						inner join v_EvnUslugaPar eup on efr.EvnFuncRequest_pid = eup.EvnDirection_id
						left join v_EvnCostPrint ecp on ecp.Evn_id = eup.EvnUslugaPar_id
					where efr.EvnFuncRequest_id = :Evn_id
						{$and}}
				";
				$result = $this->db->query($query, $data);
				if (!is_object($result)) {
					return true;
				}
				$resp = $result->result("array");
				foreach ($resp as $respone) {
					$data["Evn_id"] = $respone["EvnUslugaPar_id"];
					$this->saveEvnCostPrint($data);
				}
				break;
			case "EvnPL":
			case "EvnPLStom":
				$sumfield = "EvnPL_Sum";
				if (in_array($regionnumber, [2, 3, 10, 19, 60, 66])) {
					$sumfield = "ItogSum";
				} else if (in_array($regionnumber, [30])) {
					$sumfield = "Itog";
				} else if (in_array($regionnumber, [40, 58])) {
					$sumfield = "RegistryData_ItogSum";
				}
				$procsum = "pan_Spravka_PL";
				if (!empty($data["Cost_Year"]) && $data["Cost_Year"] >= 2015 && in_array($regionnumber, [59])) {
					$procsum = "pan_Spravka_PL_2015";
				}
				break;
			case "EvnPS":
				$sumfield = "RegistryData_ItogSum";
				$procsum = "hosp_Spravka_KSG";
				if (!empty($data["Cost_Year"]) && $data["Cost_Year"] >= 2015 && in_array($regionnumber, [59])) {
					$procsum = "hosp_Spravka_KSG_2015";
				}
				break;
			case "EvnUslugaPar":
				$sumfield = "RegistryData_ItogSum";
				if (in_array($regionnumber, [66])) {
					$sumfield = "ItogSum";
				}
				$procsum = "pan_Spravka_ParUsl";
				break;
			case "EvnPLDispDop13":
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLDD";
				if (in_array($regionnumber, [2, 3, 10, 60, 66])) {
					$sumfield = "ItogSum";
				} else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}
				if (in_array($regionnumber, array(59))) {
					$procsum = "pan_Spravka_PLDD_2015";
				}
				break;
			case "EvnPLDispOrp":
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLOrp";
				if (in_array($regionnumber, [2, 3, 10, 60, 66])) {
					$sumfield = "ItogSum";
				} else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}

				if (in_array($regionnumber, [59])) {
					$procsum = "pan_Spravka_PLOrp_2015";
				}
				break;
			case "EvnPLDispProf":
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLProf";
				if (in_array($regionnumber, [2, 3, 10, 60, 66])) {
					$sumfield = "ItogSum";
				} else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}

				if (in_array($regionnumber, [59])) {
					$procsum = "pan_Spravka_PLProf_2015";
				}
				break;
			case "EvnPLDispTeenInspection":
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLProfTeen";
				if (in_array($regionnumber, [2, 3, 10, 60, 66])) {
					$sumfield = "ItogSum";
				} else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}

				if (in_array($regionnumber, [59])) {
					$procsum = "pan_Spravka_PLProfTeen_2015";
				}
				break;
		}
		if (!empty($procsum) && !empty($sumfield)) {
			// не считаем сумму
			$doNotSum = in_array($regionnumber, [30, 40, 59, 66]);
			$params = ":Evn_id,''";
			if ($regionnumber == 40) {
				$params = ":Evn_id";
			}
			if ($regionnumber == 30 && in_array($procsum, ["hosp_Spravka_KSG", "pan_Spravka_PL"])) {
				$params = $data["Lpu_id"] . "," . $params;
			}
			if ($regionnumber == 101 && in_array($procsum, ["hosp_Spravka_KSG"])) {
				$params = ":Evn_id";
			}
			if($doNotSum === true) {
				$summStr = "coalesce({$sumfield}, 0)::varchar";
			} else {
				$summStr = "to_char(SUM((coalesce({$sumfield}, 0)::money))::float8, '{$this->numericForm17_2}')";
			}
			$query = "
				select
					{$summStr} as \"CostPrint\"
				from
					rpt{$regionnumber}.{$procsum}({$params})
				" . ($doNotSum === true ? "limit 1" : "") . "
			";
			$result_costprint = $this->db->query($query, $data);
			if (is_object($result_costprint)) {
				$resp_costprint = $result_costprint->result("array");
				if (!empty($resp_costprint[0]["CostPrint"])) {
					$data["CostPrint_Cost"] = $resp_costprint[0]["CostPrint"];
				}
			}
		}
		$data["EvnCostPrint_id"] = null;
		$proc = "p_EvnCostPrint_ins";
		// проверяем, а не печаталась ли уже справка.
		$sql = "
			select EvnCostPrint_id as \"EvnCostPrint_id\"
			from v_EvnCostPrint
			where Evn_id = :Evn_id
		";
		$sqlParams = ["Evn_id" => $data["Evn_id"]];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (проверка печати справки)");
		}
		$resp = $res->result("array");
		if (!empty($resp[0]["EvnCostPrint_id"])) {
			$proc = "p_EvnCostPrint_upd";
			$data["EvnCostPrint_id"] = $resp[0]["EvnCostPrint_id"];
		}
		if (getRegionNick() == "kareliya" && $proc == "p_EvnCostPrint_ins") {
			$data["CostPrint_setDT"] = date("Y-m-d");
		}
		$selectString = "
			evncostprint_id as \"EvnCostPrint_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    evncostprint_id := :EvnCostPrint_id,
			    evn_id := :Evn_id,
			    evncostprint_setdt := :CostPrint_setDT,
			    evncostprint_isnoprint := :CostPrint_IsNoPrint,
			    evncostprint_cost := :CostPrint_Cost,
			    person_id := :Person_pid,
			    evncostprint_number := :EvnCostPrint_Number,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, $data);
		$result = $result->result("array");
		$ECP_id = $result[0]["EvnCostPrint_id"];
		//И еще раз проверим на дубли - если они есть, то удаляем последнее
		if ($proc != "p_EvnCostPrint_ins") {
			return true;
		}
		$params_check = [];
		$params_check["Evn_id"] = $data["Evn_id"];
		$params_check["EvnCostPrint_id"] = $ECP_id;
		$query_check = "
				select EvnCostPrint_id as \"EvnCostPrint_id\"
				from v_EvnCostPrint
				where Evn_id = :Evn_id
				  and EvnCostPrint_id <> :EvnCostPrint_id
			";
		$res_check = $this->db->query($query_check, $params_check);
		$result_check = $res_check->result("array");
		if (!empty($result_check[0]["EvnCostPrint_id"])) {
			$query_del = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Message\"
				from p_evncostprint_del(evncostprint_id := :EvnCostPrint_id);
			";
			$queryParams = ["EvnCostPrint_id" => $ECP_id];
			$this->db->query($query_del, $queryParams);
			return true;
		}
		return true;
	}

	/**
	 * Сохранение факта печати справки о стоимости для СМП
	 * @param $data
	 * @return bool
	 */
	public function saveCmpCallCardCostPrint($data)
	{
		// 1. проверяем, а не печаталась ли уже справка.
		$query = "
			select CmpCallCardCostPrint_id as \"CmpCallCardCostPrint_id\"
			from v_CmpCallCardCostPrint
			where CmpCallCard_id = :CmpCallCard_id
		";
		$data["CmpCallCardCostPrint_id"] = $this->getFirstResultFromQuery($query, $data);
		// 2. сохраняем
		$proc = "p_CmpCallCardCostPrint_upd";
		if (empty($data["CmpCallCardCostPrint_id"])) {
			$data["CmpCallCardCostPrint_id"] = null;
			$proc = "p_CmpCallCardCostPrint_ins";
		}
		if (empty($data["CostPrint_IsNoPrint"])) {
			$data["CostPrint_IsNoPrint"] = 1;
		}
		$data["CostPrint_Cost"] = 0;
		// получаем стоимость лечения
		$query = "
			select extract(year from CmpCallCard_prmDT) as \"Cost_Year\"
			from v_CmpCallCard
			where CmpCallCard_id = :CmpCallCard_id
		";
		$data['Cost_Year'] = $this->getFirstResultFromQuery($query, $data);
		$regionnumber = getRegionNumber();
		$sumfield = "RegistryData_ItogSum";
		if (in_array($regionnumber, [2, 3, 10, 30, 60, 66])) {
			$sumfield = "ItogSum";
		}
		$procsum = "pan_Spravka_SMP";
		if (!empty($data["Cost_Year"]) && $data["Cost_Year"] >= 2015 && in_array($regionnumber, [59])) {
			$procsum = "pan_Spravka_SMP_2015";
		}
		if ($regionnumber == 19) {
			$procsum = ""; // не считаем сумму
		}
		if (!empty($procsum)) {
			$query = "
				select
					to_char(SUM((coalesce({$sumfield}, 0)::money))::float8, '{$this->numericForm17_2}') as \"CostPrint\"
				from
					rpt{$regionnumber}.{$procsum}(:CmpCallCard_id, '')
			";
			$result_costprint = $this->db->query($query, $data);
			if (is_object($result_costprint)) {
				$resp_costprint = $result_costprint->result("array");
				if (!empty($resp_costprint[0]["CostPrint"])) {
					$data["CostPrint_Cost"] = $resp_costprint[0]["CostPrint"];
				}
			}
		}
		$selectString = "
			cmpcallcardcostprint_id as \"CmpCallCardCostPrint_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    cmpcallcardcostprint_id := :CmpCallCardCostPrint_id,
			    cmpcallcard_id := :CmpCallCard_id,
			    cmpcallcardcostprint_setdt := :CostPrint_setDT,
			    cmpcallcardcostprint_isnoprint := :CostPrint_IsNoPrint,
			    cmpcallcardcostprint_cost := :CostPrint_Cost,
			    pmuser_id := :pmUser_id
			);
		";
		$this->db->query($query, $data);
		return true;
	}

	/**
	 * Возвращаяет данные для вывода списка открытых ЛВН в сигнальной информации ЭМК
	 * @param $data
	 * @return array|bool
	 */
	public function getEvnCostPrintViewData($data)
	{
		$query = "
			select
				ECP.EvnCostPrint_id as \"EvnCostPrint_id\",
				ECP.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\",
				ECP.EvnCostPrint_Number as \"EvnCostPrint_Number\",
				YN.YesNo_Name as EvnCostPrint_IsNoPrintText,
				to_char(ECP.EvnCostPrint_setDT, '{$this->dateTimeForm104}') as \"EvnCostPrint_setDate\",
				case
					when ECP.Person_id is null then 'Лично'
					else 'Представитель ' + rtrim(rtrim(coalesce(PS2.Person_Surname, ''))||' '||rtrim(coalesce(PS2.Person_Firname, ''))||' '||rtrim(coalesce(PS2.Person_Secname, '')))
				end as \"EvnCostPrint_DeliveryType\",
				to_char(ECP.EvnCostPrint_Cost::float8, '{$this->numericForm17_2}') as \"EvnCostPrint_Cost\"
			from
				v_EvnCostPrint ECP
				left join v_PersonState PS2 on ecp.Person_id = PS2.Person_id
				left join v_YesNo yn on yn.YesNo_id = ecp.EvnCostPrint_IsNoPrint
			where ECP.EvnCostPrint_id = :EvnCostPrint_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращаяет данные для вывода списка открытых ЛВН в сигнальной информации ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getCmpCallCardCostPrintViewData($data)
	{
		$query = "
			select
				CCP.CmpCallCardCostPrint_id as \"CmpCallCardCostPrint_id\",
				CCP.CmpCallCardCostPrint_IsNoPrint as \"CmpCallCardCostPrint_IsNoPrint\",
				YN.YesNo_Name as \"CmpCallCardCostPrint_IsNoPrintText\",
				to_char(CCP.CmpCallCardCostPrint_setDT, '{$this->dateTimeForm104}') as \"CmpCallCardCostPrint_setDate\",
				to_char(CCP.CmpCallCardCostPrint_Cost::float8, '{$this->numericForm17_2}') as \"CmpCallCardCostPrint_Cost\"
			from
				v_CmpCallCardCostPrint CCP
				left join v_YesNo yn on yn.YesNo_id = ccp.CmpCallCardCostPrint_IsNoPrint
			where CCP.CmpCallCardCostPrint_id = :CmpCallCardCostPrint_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}