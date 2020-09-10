<?php
defined("BASEPATH") or die ("No direct script access allowed");

/**
 * LpuIndividualPeriod - модель для работы с настройками индивидуальных периодов записи
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Timofeev
 * @version			30052019
 */
class LpuIndividualPeriod_model extends SwPgModel
{
	/**
	 * Получение списка МО, имеющих доступ к индивидуальной настройке периодов записи
	 * @return mixed
	 */
	function getLpuIndividualPeriodList()
	{
		$query = "
			select 
				LIP.LpuIndividualPeriod_id as \"LpuIndividualPeriod_id\",
				LIP.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_LpuIndividualPeriod LIP
			    inner join v_Lpu L on L.Lpu_id = LIP.Lpu_id
			order by L.Lpu_Nick
		";
		$response["data"] = $this->queryResult($query);
		return $response;
	}

	/**
	 * Добавление МО в список МО, имеющих доступ к индивидуальной настройке периодов записи
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function saveLpuIndividualPeriod($data)
	{
		//Проверяем наличие добавляемой МО в списке
		$query = "
			select LpuIndividualPeriod_id as \"LpuIndividualPeriod_id\"
			from v_LpuIndividualPeriod
			where Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		$checkExist = $this->getFirstRowFromQuery($query, $queryParams);
		if (!empty($checkExist)) {
			throw new Exception("Данной МО уже предоставлен доступ к настройке");
		}
		$query = "
			select
			    lpuindividualperiod_id as \"LpuIndividualPeriod_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_lpuindividualperiod_ins(
			    lpu_id := :Lpu_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Удаление МО из списка МО, имеющих доступ к индивидуальной настройке периодов записи
	 * @param $data
	 * @return array|false
	 */
	function deleteLpuIndividualPeriod($data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_lpuindividualperiod_del(
			    lpuindividualperiod_id := :LpuIndividualPeriod_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuIndividualPeriod_id" => $data["LpuIndividualPeriod_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение списка индивидуальных периодов записи для текущей МО(для таблицы)
	 *
	 * @param $data
	 * @return array
	 */
	function getIndividualPeriodList($data)
	{
		$query = "
			select
				IP.IndividualPeriod_id as \"IndividualPeriod_id\",
				IP.IndividualPeriodType_id as \"IndividualPeriodType_id\",
				IPT.IndividualPeriodType_Name as \"IndividualPeriodType_Name\",
				IP.MedStaffFact_id as \"MedStaffFact_id\",
				IP.LpuSection_id as \"LpuSection_id\",
				IP.MedService_id as \"MedService_id\",
				IP.IndividualPeriod_value as \"IndividualPeriod_value\",
				case
					when IP.IndividualPeriodType_id = 1 then MSF.Person_FIO||' ('||LSMS.LpuSection_FullName||')'
					when IP.IndividualPeriodType_id = 2 then LSIP.LpuSection_FullName||' ('||LU.LpuUnit_Name||')' 
					when IP.IndividualPeriodType_id = 3 then MS.MedService_Name||MS.MedService_Section
				end as \"IndividualPeriodObject_Name\"
			from 
				v_IndividualPeriod IP
				inner join v_IndividualPeriodType IPT on IPT.IndividualPeriodType_id = IP.IndividualPeriodType_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = IP.MedStaffFact_id
				left join v_LpuSection LSMS on LSMS.LpuSection_id = MSF.LpuSection_id
				left join v_LpuSection LSIP on LSIP.LpuSection_id = IP.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LSIP.LpuUnit_id
				left join lateral (
				    select MSs.MedService_Name,
						case
							when LSs.LpuSection_Name is not null or LUs.LpuUnit_Name is not null or LUs.LpuBuilding_Name is not null
							then ' ('||coalesce(LUs.LpuBuilding_Name, '')||' '||coalesce(LUs.LpuUnit_Name, '')||' '||coalesce(LSs.LpuSection_Name, '')||')'
							else ''
						end as MedService_Section
					from
						v_MedService MSs
						left join v_LpuSection LSs on LSs.LpuSection_id = MSs.LpuSection_id
						left join v_LpuUnit LUs on LUs.LpuUnit_id = MSs.LpuUnit_id
					where MSs.MedService_id = IP.MedService_id
					limit 1
				) as MS on true
			where IP.Lpu_id = :Lpu_id
		";
		$queryParams = ["Lpu_id" => $data["session"]["lpu_id"]];
		$response = $this->queryResult($query, $queryParams);
		return ["data" => $response];
	}

	/**
	 * Получение индвидуального периода записи объектов
	 * @param $data
	 * @param $object_type
	 * @return array
	 */
	function getObjectIndividualPeriod($data, $object_type)
	{
		$queryParams = [
			"Lpu_id" => (empty($data["Lpu_id"])) ? $data["session"]["lpu_id"] : $data["Lpu_id"]
		];
		switch ($object_type) {
			case "MedStaffFact":
				$queryParams["IndividualPeriodType_id"] = 1;
				break;
			case "LpuSection":
				$queryParams["IndividualPeriodType_id"] = 2;
				break;
			case "MedService":
				$queryParams["IndividualPeriodType_id"] = 3;
				break;
		}
		$query = "
			select
				case
					when IP.IndividualPeriodType_id = 1 then IP.MedStaffFact_id
					when IP.IndividualPeriodType_id = 2 then IP.LpuSection_id
					when IP.IndividualPeriodType_id = 3 then IP.MedService_id
				end as \"IndividualPeriodObject_id\",
				IP.IndividualPeriod_value as \"IndividualPeriod_value\"
			from
				v_IndividualPeriod IP
				inner join v_LpuIndividualPeriod LIP on LIP.Lpu_id = IP.Lpu_id
			where IP.Lpu_id = :Lpu_id
			  and IP.IndividualPeriodType_id = :IndividualPeriodType_id
		";
		$objects = $this->queryResult($query, $queryParams);
		$result = [];
		if (!empty($objects)) {
			foreach ($objects as $object) {
				$result[$object["IndividualPeriodObject_id"]] = $object["IndividualPeriod_value"];
			}
		}
		return $result;
	}

	/**
	 * Загрузка формы редактирования индивидуального периода
	 * @param $data
	 * @return array|false
	 */
	function loadIndividualPeriodEditForm($data)
	{
		$query = "
			select
				IndividualPeriod_id as \"IndividualPeriod_id\",
				IndividualPeriodType_id as \"IndividualPeriodType_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				MedService_id as \"MedService_id\",
				LpuSection_id as \"LpuSection_id\",
				IndividualPeriod_value as \"IndividualPeriod_value\"
			from v_IndividualPeriod IP
			where IP.IndividualPeriod_id = :IndividualPeriod_id
		";
		$queryParams = ["IndividualPeriod_id" => $data["IndividualPeriod_id"]];
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение индивидуального периода записи для текущей МО
	 * @param $data
	 * @return array|false
	 */
	function saveIndividualPeriod($data)
	{
		if (!empty($data["IndividualPeriod_id"])) {
			$proc = "upd";
		} else {
			$query = "
				select 
					IndividualPeriod_id as \"IndividualPeriod_id\",
				    IndividualPeriodType_id as \"IndividualPeriodType_id\"
				from v_IndividualPeriod
				where
					(IndividualPeriodType_id = 1 and MedStaffFact_id = :MedStaffFact_id) or
				    (IndividualPeriodType_id = 2 and LpuSection_id = :LpuSection_id) or
				    (IndividualPeriodType_id = 3 and MedService_id = :MedService_id)
				limit 1
			";
			$response = $this->getFirstRowFromQuery($query, $data);
			if (!empty($response)) {
				$Alert_Msg = "";
				switch ($response["IndividualPeriodType_id"]) {
					case "1":
						$Alert_Msg = "Для данного врача уже установлен период записи регистратором. Изменить период?";
						break;
					case "2":
						$Alert_Msg = "Для отделения стационара уже установлен период записи регистратором. Изменить период?";
						break;
					case "3":
						$Alert_Msg = "Для отделения стационара уже установлен период записи регистратором. Изменить период? ";
						break;
				}
				return [
					"Error_Msg" => "YesNo",
					"Alert_Msg" => $Alert_Msg,
					"Error_Code" => 101,
					"IndividualPeriod_id" => $response["IndividualPeriod_id"],
					"success" => false
				];
			}
			$proc = "ins";
		}
		$selectString = "
			individualperiod_id as \"IndividualPeriod_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from p_individualperiod_{$proc}(
			    individualperiod_id := :IndividualPeriod_id,
			    lpu_id := :Lpu_id,
			    individualperiodtype_id := :IndividualPeriodType_id,
			    medstafffact_id := :MedStaffFact_id,
			    medservice_id := :MedService_id,
			    lpusection_id := :LpuSection_id,
			    individualperiod_value := :IndividualPeriod_value,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"IndividualPeriod_id" => !empty($data["IndividualPeriod_id"]) ? $data["IndividualPeriod_id"] : null,
			"Lpu_id" => $data["session"]["lpu_id"],
			"IndividualPeriodType_id" => $data["IndividualPeriodType_id"],
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"MedService_id" => $data["MedService_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"IndividualPeriod_value" => $data["IndividualPeriod_value"],
			"pmUser_id" => $data["pmUser_id"]
		];
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Удаление индивидуального периода
	 * @param $data
	 * @return array|false
	 */
	function deleteIndividualPeriod($data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_individualperiod_del(
			    individualperiod_id := :IndividualPeriod_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"IndividualPeriod_id" => $data["IndividualPeriod_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		return $this->queryResult($query, $queryParams);
	}
}