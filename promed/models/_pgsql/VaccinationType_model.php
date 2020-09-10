<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");
/**
 * Класс модели "Виды профилактических прививок"
 *
 * @package Common
 * @author Melentyev Anatoliy
 */

class VaccinationType_model extends swModel
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получаем список видов вакцинации
	 * @param $data
	 * @return array|bool
	 */
	function loadVaccinationTypes($data)
	{
		$filterList = [];
		$queryParams = [];

		if (!empty($data["Vaccination_isNacCal"]))
			$filterList[] = "( select count(1) from vc.Vaccination vac where vac.VaccinationType_id = vt.VaccinationType_id and vac.Vaccination_isNacCal = 2) <> 0 ";

		if (!empty($data["Vaccination_isEpidemic"]))
			$filterList[] = "( select count(1) from vc.Vaccination vac where vac.VaccinationType_id = vt.VaccinationType_id and vac.Vaccination_isEpidemic = 2 ) <> 0 ";

		if (!empty($data["VaccinationType_DateRange"][0]) && !empty($data["VaccinationType_DateRange"][1])) {
			$filterList[] = "
				vt.VaccinationType_begDate >= :Vaccination_begDate and (
					(vt.VaccinationType_endDate is null and vt.VaccinationType_begDate <= :Vaccination_endDate) or
					vt.VaccinationType_endDate <= :Vaccination_endDate
				)
			";
			$queryParams = array('Vaccination_begDate' => $data["VaccinationType_DateRange"][0],'Vaccination_endDate' => $data["VaccinationType_DateRange"][1]);
		}

		$whereString = (count($filterList) != 0) ? "where " . implode(" and ", $filterList) : "";

		$query = "
			select 
				vt.VaccinationType_id as \"VaccinationType_id\",
				vt.VaccinationType_isReaction as \"VaccinationType_isReaction\",
				vt.VaccinationType_Code as \"VaccinationType_Code\",
				vt.VaccinationType_Name as \"VaccinationType_Name\",
				vt.VaccinationType_SortCode as \"VaccinationType_SortCode\",
				case when ( select count(1) from vc.Vaccination vac where vac.VaccinationType_id = vt.VaccinationType_id and vac.Vaccination_isNacCal   = 2) <> 0 then 'true' else 'false' end as \"Vaccination_isNacCal\",
				case when ( select count(1) from vc.Vaccination vac where vac.VaccinationType_id = vt.VaccinationType_id and vac.Vaccination_isEpidemic = 2) <> 0 then 'true' else 'false' end as \"Vaccination_isEpidemic\",
				( select 
					string_agg(vet.VaccinationExamType_Name,', ') 
				from vc.v_VaccinationExamType vet, vc.v_VaccinationExamTypeLink vetl
				where 
					vetl.VaccinationExamType_id = vet.VaccinationExamType_id and vetl.VaccinationType_id = vt.VaccinationType_id
				) as \"ExamString\",
				( select
						v.Vaccination_begAge::varchar ||
						case 
							when v.VaccinationAgeType_bid = 1 
								and (right(v.Vaccination_begAge::varchar,2)::integer >= 11 and right(v.Vaccination_begAge::varchar,2)::integer <= 14 or right(v.Vaccination_begAge::varchar,1)::integer >= 5 and right(v.Vaccination_begAge::varchar,1)::integer <= 9 or right(v.Vaccination_begAge::varchar,1)::integer = 0) then ' дней' 
							when v.VaccinationAgeType_bid = 1 
								and (right(v.Vaccination_begAge::varchar,1)::integer >= 2  and right(v.Vaccination_begAge::varchar,1)::integer <= 4) then ' дня' 
							when v.VaccinationAgeType_bid = 1 
								and (right(v.Vaccination_begAge::varchar,1)::integer = 1) then ' день' 
							when v.VaccinationAgeType_bid = 2 
								and (right(v.Vaccination_begAge::varchar,2)::integer >= 11 and right(v.Vaccination_begAge::varchar,2)::integer <= 14 or right(v.Vaccination_begAge::varchar,1)::integer >= 5 and right(v.Vaccination_begAge::varchar,1)::integer <= 9 or right(v.Vaccination_begAge::varchar,1)::integer = 0) then ' месяцев' 
							when v.VaccinationAgeType_bid = 2 
								and (right(v.Vaccination_begAge::varchar,1)::integer >= 2  and right(v.Vaccination_begAge::varchar,1)::integer <= 4) then ' месяца' 
							when v.VaccinationAgeType_bid = 2 
								and (right(v.Vaccination_begAge::varchar,1)::integer = 1) then ' месяц' 
							when v.VaccinationAgeType_bid = 3 
								and (right(v.Vaccination_begAge::varchar,2)::integer >= 11 and right(v.Vaccination_begAge::varchar,2)::integer <= 14 or right(v.Vaccination_begAge::varchar,1)::integer >= 5 and right(v.Vaccination_begAge::varchar,1)::integer <= 9 or right(v.Vaccination_begAge::varchar,1)::integer = 0) then ' лет' 
							when v.VaccinationAgeType_bid = 3 
								and (right(v.Vaccination_begAge::varchar,1)::integer >= 2  and right(v.Vaccination_begAge::varchar,1)::integer <= 4) then ' года' 
							when v.VaccinationAgeType_bid = 3 
								and (right(v.Vaccination_begAge::varchar,1)::integer = 1) then ' год' 
						end
					from vc.Vaccination v
					where 
						v.VaccinationType_id = vt.VaccinationType_id
					order by (case when v.VaccinationAgeType_bid = 2 then v.Vaccination_begAge * 30 when v.VaccinationAgeType_bid = 3 then v.Vaccination_begAge * 365 else v.Vaccination_begAge * 1 end ) asc
					limit 1
				) as \"minAge\",
				to_char(vt.VaccinationType_begDate, 'dd.mm.yyyy') as \"VaccinationType_begDate\",
				to_char(vt.VaccinationType_endDate, 'dd.mm.yyyy') as \"VaccinationType_endDate\"
			from vc.VaccinationType vt
			{$whereString}
		";

		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Сохраняем/создаем вид вакцинации
	 * @param $data
	 * @throws Exception
	 */
	function saveVaccinationType($data)
	{
		$procedure = (empty($data["VaccinationType_id"])) ? "p_VaccinationType_ins" : "p_VaccinationType_upd";
		$ins = (empty($data["VaccinationType_id"])) ? "" : ", VaccinationType_id := :VaccinationType_id";

		$queryParams = [
			"VaccinationType_id" => !empty($data["VaccinationType_id"]) ? $data["VaccinationType_id"] : null,
			"VaccinationType_Name" => $data["VaccinationType_Name"],
			"VaccinationType_Code" => $data["VaccinationType_Code"],
			"VaccinationType_isReaction" => $data["VaccinationType_isReaction"],
			"VaccinationType_begDate" => $data["VaccinationType_begDate"],
			"VaccinationType_endDate" => !empty($data["VaccinationType_endDate"]) ? $data["VaccinationType_endDate"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\",
				VaccinationType_id as \"VaccinationType_id\"
			from 
				vc.{$procedure} (  
				VaccinationType_Code := :VaccinationType_Code,
				VaccinationType_Name := :VaccinationType_Name,
				VaccinationType_isReaction := :VaccinationType_isReaction,
				VaccinationType_begDate := :VaccinationType_begDate,
				VaccinationType_endDate := :VaccinationType_endDate,
				pmUser_id := :pmUser_id {$ins}
				);
		";



		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}else{
			$result = $result->result('array');
			$this->addVaccinationExam(array("VaccinationType_id" => $result[0]["VaccinationType_id"], "VaccinationExamType_id" => "1","pmUser_id" => $data["pmUser_id"]));
			return $result;
		}
	}

	/**
	 * Удаляем вид вакцинации
	 * @param $data
	 * @throws Exception
	 */
	function deleteVaccinationType($data)
	{
		if (empty($data["VaccinationType_id"])) {
			throw new Exception("Не указан обязательный параметр [Идентификатор вида профилактической прививки]");
		}

		$this->checkVaccinationBeforeDelete($data); //Проверяем все прививки типа на исполненость

		$this->deleteVaccinationsFromVaccinationType($data); //Удаляем все прививки из типа вакцинации
		$this->deleteVaccinationRiskGroupsFromVaccinationType($data); //Удаляем все группы риска из типа вакцинации
		$this->deleteVaccinationExamsFromVaccinationType($data); //Удаляем все осмотры из типа вакцинации
		$this->deleteVaccinationPrepsFromVaccinationType($data); //Удаляем все препараты из типа вакцинации

		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\"
			from 
			 	vc.p_VaccinationType_del(
				VaccinationType_id := :VaccinationType_id,
				IsRemove := 2,
				pmUser_id := :pmUser_id
			);
		";

		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

	/**
	 * Удаляем все прививки/реакции из вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function deleteVaccinationsFromVaccinationType($data)
	{
		$query = "
			select
				v.Vaccination_id as \"Vaccination_id\"
			from vc.v_Vaccination v 
			where 
				v.VaccinationType_id = :VaccinationType_id
		";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		$result = (is_object($result)) ? $result->result_array() : [];

		foreach($result as $value) {
			$this->deleteVaccination([
				'Vaccination_id' => $value['Vaccination_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

	}

	/**
	 * Удаляем все группы риска из вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function deleteVaccinationRiskGroupsFromVaccinationType($data)
	{
		$query = "
			select 
				vrgl.VaccinationRiskGroup_id as \"VaccinationRiskGroup_id\"
			from vc.v_VaccinationRiskGroupLink vrgl
			where 
				vrgl.VaccinationType_id = :VaccinationType_id
		";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		$result = (is_object($result)) ? $result->result_array() : [];

		foreach($result as $value) {
			$this->deleteVaccinationRiskGroup([
				'VaccinationRiskGroup_id' => $value['VaccinationRiskGroup_id'],
				'VaccinationType_id' => $data['VaccinationType_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}
	}

	/**
	 * Удаляем все осмотры после вакцинации из вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function deleteVaccinationExamsFromVaccinationType($data)
	{
		$query = "
				select 
					vetl.VaccinationExamType_id as \"VaccinationExamType_id\"
				from vc.v_VaccinationExamTypeLink vetl
				WHERE 
					vetl.VaccinationType_id = :VaccinationType_id
			";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		$result = (is_object($result)) ? $result->result_array() : [];

		foreach($result as $value) {
			$this->deleteVaccinationExam([
				'VaccinationExamType_id' => $value['VaccinationExamType_id'],
				'VaccinationType_id' => $data['VaccinationType_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}
	}

	/**
	 * Удаляем все препараты для вакцинации из вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function deleteVaccinationPrepsFromVaccinationType($data)
	{

		$query = "
			select 
				vtp.VaccinationTypePrep_id as \"VaccinationTypePrep_id\"
			from vc.v_VaccinationTypePrep vtp
			where 
				vtp.VaccinationType_id = :VaccinationType_id
		";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		$result = (is_object($result)) ? $result->result_array() : [];

		foreach($result as $value) {
			$this->deleteVaccinationPrep([
				'VaccinationTypePrep_id' => $value['VaccinationTypePrep_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}
	}

	/**
	 * Получаем список прививок/реакций для вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function loadVaccinationList($data)
	{
		$query = "
			select
				v.Vaccination_id as \"Vaccination_id\",
				v.Vaccination_Name as \"Vaccination_Name\",
				v.Vaccination_Code as \"Vaccination_Code\",
				v.Vaccination_Nick as \"Vaccination_Nick\",
				v.Vaccination_isNacCal as \"Vaccination_isNacCal\",
				isNacCal.YesNo_Name as \"Vaccination_isNacCal_Value\",
				v.Vaccination_isEpidemic as \"Vaccination_isEpidemic\",
				isEpidemic.YesNo_Name as \"Vaccination_isEpidemic_Value\",
				v.VaccinationRiskGroupAccess_id as \"VaccinationRiskGroupAccess_id\",
				RiskGroupAccess.VaccinationRiskGroupAccess_Name as \"VaccinationRiskGroupAccess_Name\",
				vatp.Vaccination_Name as \"Vaccination_LastName\",
				v.Vaccination_begAge::varchar ||
				case 
					when v.VaccinationAgeType_bid = 1 
						and (right(v.Vaccination_begAge::varchar,2)::integer >= 11 and right(v.Vaccination_begAge::varchar,2)::integer <= 14 or right(v.Vaccination_begAge::varchar,1)::integer >= 5 and right(v.Vaccination_begAge::varchar,1)::integer <= 9 or right(v.Vaccination_begAge::varchar,1)::integer = 0) then ' дней' 
					when v.VaccinationAgeType_bid = 1 
						and (right(v.Vaccination_begAge::varchar,1)::integer >= 2  and right(v.Vaccination_begAge::varchar,1)::integer <= 4) then ' дня' 
					when v.VaccinationAgeType_bid = 1 
						and (right(v.Vaccination_begAge::varchar,1)::integer = 1) then ' день' 

					when v.VaccinationAgeType_bid = 2 
						and (right(v.Vaccination_begAge::varchar,2)::integer >= 11 and right(v.Vaccination_begAge::varchar,2)::integer <= 14 or right(v.Vaccination_begAge::varchar,1)::integer >= 5 and right(v.Vaccination_begAge::varchar,1)::integer <= 9 or right(v.Vaccination_begAge::varchar,1)::integer = 0) then ' месяцев' 
					when v.VaccinationAgeType_bid = 2 
						and (right(v.Vaccination_begAge::varchar,1)::integer >= 2  and right(v.Vaccination_begAge::varchar,1)::integer <= 4) then ' месяца' 
					when v.VaccinationAgeType_bid = 2 
						and (right(v.Vaccination_begAge::varchar,1)::integer = 1) then ' месяц' 

					when v.VaccinationAgeType_bid = 3 
						and (right(v.Vaccination_begAge::varchar,2)::integer >= 11 and right(v.Vaccination_begAge::varchar,2)::integer <= 14 or right(v.Vaccination_begAge::varchar,1)::integer >= 5 and right(v.Vaccination_begAge::varchar,1)::integer <= 9 or right(v.Vaccination_begAge::varchar,1)::integer = 0) then ' лет' 
					when v.VaccinationAgeType_bid = 3 
						and (right(v.Vaccination_begAge::varchar,1)::integer >= 2  and right(v.Vaccination_begAge::varchar,1)::integer <= 4) then ' года' 
					when v.VaccinationAgeType_bid = 3 
						and (right(v.Vaccination_begAge::varchar,1)::integer = 1) then ' год' 
				end as \"Vaccination_minAge\",
				v.Vaccination_endAge::varchar ||
				case 
					when v.VaccinationAgeType_eid = 1
						and (right(v.Vaccination_endAge::varchar,2)::integer >= 11 and right(v.Vaccination_endAge::varchar,2)::integer <= 14 or right(v.Vaccination_endAge::varchar,1)::integer >= 5 and right(v.Vaccination_endAge::varchar,1)::integer <= 9 or right(v.Vaccination_endAge::varchar,1)::integer = 0) then ' дней'
					when v.VaccinationAgeType_eid = 1
						and (right(v.Vaccination_endAge::varchar,1)::integer >= 2  and right(v.Vaccination_endAge::varchar,1)::integer <= 4) then ' дня'
					when v.VaccinationAgeType_eid = 1
						and (right(v.Vaccination_endAge::varchar,1)::integer = 1) then ' день'
					
					when v.VaccinationAgeType_eid = 2
						and (right(v.Vaccination_endAge::varchar,2)::integer >= 11 and right(v.Vaccination_endAge::varchar,2)::integer <= 14 or right(v.Vaccination_endAge::varchar,1)::integer >= 5 and right(v.Vaccination_endAge::varchar,1)::integer <= 9 or right(v.Vaccination_endAge::varchar,1)::integer = 0) then ' месяцев'
					when v.VaccinationAgeType_eid = 2
						and (right(v.Vaccination_endAge::varchar,1)::integer >= 2  and right(v.Vaccination_endAge::varchar,1)::integer <= 4) then ' месяца'
					when v.VaccinationAgeType_eid = 2
						and (right(v.Vaccination_endAge::varchar,1)::integer = 1) then ' месяц'
					
					when v.VaccinationAgeType_eid = 3
						and (right(v.Vaccination_endAge::varchar,2)::integer >= 11 and right(v.Vaccination_endAge::varchar,2)::integer <= 14 or right(v.Vaccination_endAge::varchar,1)::integer >= 5 and right(v.Vaccination_endAge::varchar,1)::integer <= 9 or right(v.Vaccination_endAge::varchar,1)::integer = 0) then ' лет'
					when v.VaccinationAgeType_eid = 3
						and (right(v.Vaccination_endAge::varchar,1)::integer >= 2  and right(v.Vaccination_endAge::varchar,1)::integer <= 4) then ' года'
					when v.VaccinationAgeType_eid = 3
						and (right(v.Vaccination_endAge::varchar,1)::integer = 1) then ' год'
				end as \"Vaccination_maxAge\",
				v.Vaccination_isSingle as \"Vaccination_isSingle\",
				to_char(v.Vaccination_begDate, 'dd.mm.yyyy') as \"Vaccination_begDate\",
				to_char(v.Vaccination_endDate, 'dd.mm.yyyy') as \"Vaccination_endDate\"
			from
				vc.v_Vaccination v
				left join YesNo isNacCal on isNacCal.YesNo_id = v.Vaccination_isNacCal
				left join YesNo isEpidemic on isEpidemic.YesNo_id = v.Vaccination_isEpidemic
				left join vc.v_Vaccination vatp on vatp.Vaccination_id = v.Vaccination_pid
				left join vc.v_VaccinationAgeType AgeType_bid on AgeType_bid.VaccinationAgeType_id = v.VaccinationAgeType_bid
				left join vc.v_VaccinationAgeType AgeType_eid on AgeType_eid.VaccinationAgeType_id = v.VaccinationAgeType_eid
				left join YesNo isSingle on isSingle.YesNo_id = v.Vaccination_isSingle
				left join vc.v_VaccinationRiskGroupAccess RiskGroupAccess on RiskGroupAccess.VaccinationRiskGroupAccess_id = v.VaccinationRiskGroupAccess_id
			where v.VaccinationType_id = :VaccinationType_id
		";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Получаем список предыдущих прививок для формы препараты для вакцинации
	 * @param $VaccinationType_id
	 * @return array
	 */
	function loadVaccinationPrevComboList($data)
	{
		$query = "
			select 
				v.Vaccination_id as \"Vaccination_id\", 
				v.Vaccination_Code || ' ' || v.Vaccination_Name as \"Vaccination_Name\",
				v.Vaccination_isNacCal as \"Vaccination_isNacCal\",
				v.Vaccination_isEpidemic as \"Vaccination_isEpidemic\"
			from vc.v_Vaccination v
			where 
				v.VaccinationType_id = :VaccinationType_id
			order by VaccinationType_id
		";
		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Получаем список доступности прививок для групп риска
	 * @return array
	 */
	function loadVaccinationRiskGroupAccessComboList()
	{
		$result = $this->db->query(" 
 			select 
 				vrga.VaccinationRiskGroupAccess_id as \"VaccinationRiskGroupAccess_id\", 
 				vrga.VaccinationRiskGroupAccess_Name as \"VaccinationRiskGroupAccess_Name\" 
 			from vc.v_VaccinationRiskGroupAccess vrga
 			");
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Получаем прививку/реакцию
	 * @param $data
	 * @throws Exception
	 */
	function getVaccination($data)
	{
		$queryParams = ["Vaccination_id" => $data["Vaccination_id"]];
		$query = "
			select 
				v.Vaccination_id as \"Vaccination_id\",
				v.Vaccination_Name as \"Vaccination_Name\",
				v.Vaccination_Code as \"Vaccination_Code\",
				v.Vaccination_Nick as \"Vaccination_Nick\",
				v.Vaccination_pid as \"Vaccination_pid\",
				v.Vaccination_isNacCal as \"Vaccination_isNacCal\",
				v.Vaccination_isEpidemic as \"Vaccination_isEpidemic\",
				v.Vaccination_begAge as \"Vaccination_begAge\",
				v.VaccinationAgeType_bid as \"VaccinationAgeType_bid\",
				v.Vaccination_endAge as \"Vaccination_endAge\",
				v.VaccinationAgeType_eid as \"VaccinationAgeType_eid\",
				v.VaccinationRiskGroupAccess_id as \"VaccinationRiskGroupAccess_id\",
				v.Vaccination_isSingle as \"Vaccination_isSingle\",
				to_char(v.Vaccination_begDate, 'dd.mm.yyyy') as \"Vaccination_begDate\",
				to_char(v.Vaccination_endDate, 'dd.mm.yyyy') as \"Vaccination_endDate\"
			from vc.v_Vaccination v 
			where 
				v.Vaccination_id = :Vaccination_id
		";

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}else{
			return $result->result('array');
		}
	}

	/**
	 * Проверяем прививку/реакцию на исполненность перед сохранением
	 * @param $data
	 * @throws Exception
	 */
	function checkVaccinationBeforeSave($data)
	{
		if(in_array($data["VaccinationRiskGroupAccess_id"],['2','3'])) {
			$query = "
				select 
					pv.Vaccination_id as \"Vaccination_id\"
				from vc.v_Vaccination v 
					left join vc.v_Vaccination pv on v.Vaccination_pid = pv.Vaccination_id
				where 
					v.Vaccination_id = :Vaccination_pid and 
					pv.VaccinationRiskGroupAccess_id <> :VaccinationRiskGroupAccess_id
			";
			$result = $this->db->query($query, $data);
			$result = (is_object($result)) ? $result->result_array() : [];

			if(!empty($result[0])) {
				throw new Exception("Указана не корректная схема вакцинации.");
			}
		}

	}

	/**
	 * Сохраняем/создаем прививку/реакцию
	 * @param $data
	 * @throws Exception
	 */
	function saveVaccination($data)
	{
		$this->checkVaccinationBeforeSave($data);

		$procedure = (empty($data["Vaccination_id"])) ? "p_Vaccination_ins" : "p_Vaccination_upd";
		$ins = (empty($data["Vaccination_id"])) ? "" : ", Vaccination_id := :Vaccination_id";
		$queryParams = [
			"Vaccination_id" => !empty($data["Vaccination_id"]) ? $data["Vaccination_id"] : null,
			"VaccinationType_id" => $data["VaccinationType_id"],
			"Vaccination_Name" => $data["Vaccination_Name"],
			"Vaccination_Code" => !empty($data["Vaccination_Code"]) ? $data["Vaccination_Code"] : null,
			"Vaccination_Nick" => !empty($data["Vaccination_Nick"]) ? $data["Vaccination_Nick"] : null,
			"Vaccination_pid" => !empty($data["Vaccination_pid"]) ? $data["Vaccination_pid"] : null,
			"Vaccination_isNacCal" => !empty($data["Vaccination_isNacCal"]) ? $data["Vaccination_isNacCal"] : null,
			"Vaccination_isEpidemic" => !empty($data["Vaccination_isEpidemic"]) ? $data["Vaccination_isEpidemic"] : null,
			"Vaccination_begAge" => !empty($data["Vaccination_begAge"]) ? $data["Vaccination_begAge"] : null,
			"VaccinationAgeType_bid" => !empty($data["VaccinationAgeType_bid"]) ? $data["VaccinationAgeType_bid"] : null,
			"Vaccination_endAge" => !empty($data["Vaccination_endAge"]) ? $data["Vaccination_endAge"] : null,
			"VaccinationAgeType_eid" => !empty($data["VaccinationAgeType_eid"]) ? $data["VaccinationAgeType_eid"] : null,
			"VaccinationRiskGroupAccess_id" => !empty($data["VaccinationRiskGroupAccess_id"]) ? $data["VaccinationRiskGroupAccess_id"] : null,
			"Vaccination_isSingle" => !empty($data["Vaccination_isSingle"]) ? $data["Vaccination_isSingle"] : null,
			"Vaccination_isReactionLevel" => !empty($data["Vaccination_isReactionLevel"]) ? $data["Vaccination_isReactionLevel"] : null,
			"Vaccination_begDate" => $data["Vaccination_begDate"],
			"Vaccination_endDate" => !empty($data["Vaccination_endDate"]) ? $data["Vaccination_endDate"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\",
				Vaccination_id as \"Vaccination_id\"
			from  vc.{$procedure} (
				VaccinationType_id := :VaccinationType_id,
				Vaccination_Name := :Vaccination_Name,
				Vaccination_Code := :Vaccination_Code,
				Vaccination_Nick := :Vaccination_Nick,
				Vaccination_pid := :Vaccination_pid,
				Vaccination_isNacCal := :Vaccination_isNacCal,
				Vaccination_isEpidemic := :Vaccination_isEpidemic,
				Vaccination_begAge := :Vaccination_begAge,
				VaccinationAgeType_bid := :VaccinationAgeType_bid,
				Vaccination_endAge := :Vaccination_endAge,
				VaccinationAgeType_eid := :VaccinationAgeType_eid,
				VaccinationRiskGroupAccess_id := :VaccinationRiskGroupAccess_id,
				Vaccination_isSingle := :Vaccination_isSingle,
				Vaccination_begDate := :Vaccination_begDate,
				Vaccination_endDate := :Vaccination_endDate,
				pmUser_id := :pmUser_id {$ins}
				);
		";

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}else{
			return $result->result('array');
		}
	}

	/**
	 * Проверяем прививку/реакцию на исполненность перед удалением
	 * @param $data
	 * @throws Exception
	 */
	function checkVaccinationBeforeDelete($data)
	{
		if(empty($data['VaccinationType_id'])){
			$query = " 
				select 
					v.VaccinationType_id as \"VaccinationType_id\"
				from vc.v_Vaccination v
				where 
					v.Vaccination_id = :Vaccination_id 
 			";
			$result = $this->db->query($query, array("Vaccination_id" => $data["Vaccination_id"]));
			$result = $result->result('array');
			$data['VaccinationType_id'] = $result[0]["VaccinationType_id"];
		}

		$query = "
			select 
				vd.VaccinationData_id as \"VaccinationType_id\"
			from vc.v_Vaccination v
				left join vc.v_VaccinationData vd on v.Vaccination_id = vd.Vaccination_id
			where 
				v.VaccinationType_id = :VaccinationType_id and 
				vd.VaccinationData_id is not null
			limit 1
			";
		$result = $this->db->query($query, $data);
		$result = (is_object($result)) ? $result->result_array() : [];

		if(!empty($result)){
			throw new Exception("В системе есть исполненные прививки данного вида. Удаление невозможно.");
		}
	}

	/**
	 * Удаляем прививку/реакцию
	 * @param $data
	 * @throws Exception
	 */
	function deleteVaccination($data)
	{
		$this->checkVaccinationBeforeDelete($data); //Проверяем прививки типа на исполненость

		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\"
			from vc.p_Vaccination_del(
				Vaccination_id := :Vaccination_id,
				IsRemove := 2,
				pmUser_id := :pmUser_id
				);
		";

		$result = $this->db->query($query, array("Vaccination_id" => $data["Vaccination_id"],"pmUser_id" => $data["pmUser_id"]));
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

	/**
	 * Получаем список групп риска для вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function loadVaccinationRiskGroupList($data)
	{
		$query = "
			select 
				vrg.VaccinationRiskGroup_id as \"VaccinationRiskGroup_id\",
				vrg.VaccinationRiskGroup_Name as \"VaccinationRiskGroup_Name\", 
				vrgl.VaccinationType_id as \"VaccinationType_id\"
			from vc.v_VaccinationRiskGroup vrg
				left join  vc.v_VaccinationRiskGroupLink vrgl on vrgl.VaccinationRiskGroup_id = vrg.VaccinationRiskGroup_id
			where 
				vrgl.VaccinationType_id = :VaccinationType_id
			order by vrgl.VaccinationRiskGroup_id
		";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Получаем меню групп риска
	 * @return array
	 */
	function loadVaccinationRiskGroupMenuList($data)
	{
		$query = " 
			select 
				vrg.VaccinationRiskGroup_id as \"VaccinationRiskGroup_id\", 
				vrg.VaccinationRiskGroup_Name as \"VaccinationRiskGroup_Name\", 
				vrgl.VaccinationRiskGroupLink_id as \"VaccinationRiskGroupLink_id\"
			from vc.v_VaccinationRiskGroup vrg
				left join vc.VaccinationRiskGroupLink vrgl on vrg.VaccinationRiskGroup_id = vrgl.VaccinationRiskGroup_id and vrgl.VaccinationType_id = :VaccinationType_id
			order by vrg.VaccinationRiskGroup_id  asc 
		";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Добавляем группу риска вида профилактических прививок
	 * @param $data
	 * @throws Exception
	 */
	function addVaccinationRiskGroup($data)
	{
		$queryParams = [
			"VaccinationRiskGroupLink_id" => null,
			"VaccinationRiskGroup_id" => $data["VaccinationRiskGroup_id"],
			"VaccinationType_id" => $data["VaccinationType_id"],
			"pmUser_id" => $data["pmUser_id"],
		];

		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\",
				VaccinationRiskGroupLink_id as \"VaccinationRiskGroupLink_id\"
			from vc.p_VaccinationRiskGroupLink_ins(
				VaccinationRiskGroup_id := :VaccinationRiskGroup_id,
				VaccinationType_id := :VaccinationType_id,
				pmUser_id := :pmUser_id
				);
		";

		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

	/**
	 * Удаляем группу риска вида профилактических прививок
	 * @param $data
	 * @throws Exception
	 */
	function deleteVaccinationRiskGroup($data)
	{
		$query = " 
 			select 
 				vetl.VaccinationRiskGroupLink_id as \"VaccinationRiskGroupLink_id\"
 			from vc.v_VaccinationRiskGroupLink vetl 
 			where 
 				vetl.VaccinationType_id = :VaccinationType_id and
 				vetl.VaccinationRiskGroup_id = :VaccinationRiskGroup_id 
 		";

		$result = $this->db->query($query, array("VaccinationRiskGroup_id" => $data["VaccinationRiskGroup_id"],"VaccinationType_id" => $data["VaccinationType_id"]));
		$result = $result->result('array');

		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\"
			from vc.p_VaccinationRiskGroupLink_del(
				VaccinationRiskGroupLink_id := :VaccinationRiskGroupLink_id,
				IsRemove := 2,
				pmUser_id := :pmUser_id
				);
		";

		$result = $this->db->query($query, array("VaccinationRiskGroupLink_id" => $result[0]["VaccinationRiskGroupLink_id"],"pmUser_id" => $data["pmUser_id"]));
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

	/**
	 * Получаем список осмотров после вакцинации для вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function loadVaccinationExamList($data)
	{
		$query = "
				select 
					vet.VaccinationExamType_id as \"VaccinationExamType_id\",
					vet.VaccinationExamType_Name as \"VaccinationExamType_Name\",
					vetl.VaccinationType_id as \"VaccinationType_id\"
				from vc.v_VaccinationExamType vet
				left join  vc.v_VaccinationExamTypeLink vetl on vetl.VaccinationExamType_id = vet.VaccinationExamType_id
				where vetl.VaccinationType_id = :VaccinationType_id
				order by vetl.VaccinationExamTypeLink_id
			";

		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Получаем меню осмотров после вакцинации
	 * @return array
	 */
	function loadVaccinationExamMenuList($data)
	{
		$query = " 
 			select 
				vet.VaccinationExamType_id as \"VaccinationExamType_id\", 
				vet.VaccinationExamType_Name as \"VaccinationExamType_Name\", 
				vetl.VaccinationExamTypeLink_id as \"VaccinationExamTypeLink_id\"
			from vc.v_VaccinationExamType vet 
			left join vc.VaccinationExamTypeLink vetl on vet.VaccinationExamType_id = vetl.VaccinationExamType_id and vetl.VaccinationType_id = :VaccinationType_id
			order by vet.VaccinationExamType_id asc 
		";
		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Добавляем осмотр после вакцинации для вида профилактических прививок
	 * @param $data
	 * @throws Exception
	 */
	function addVaccinationExam($data)
	{
		$queryParams = [
			"VaccinationExamTypeLink_id" => null,
			"VaccinationExamType_id" => $data["VaccinationExamType_id"],
			"VaccinationType_id" => $data["VaccinationType_id"],
			"pmUser_id" => $data["pmUser_id"],
		];

		$query = "
			select
				 error_message as \"Error_Message\",
				 error_code as \"Error_Code\",
				 VaccinationExamTypeLink_id as \"VaccinationExamTypeLink_id\"
			from vc.p_VaccinationExamTypeLink_ins (
				VaccinationExamType_id := :VaccinationExamType_id,
				VaccinationType_id := :VaccinationType_id,
				pmUser_id := :pmUser_id
			);
		";

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

	/**
	 * Удаляем осмотр после вакцинации для вида профилактических прививок
	 * @param $data
	 * @throws Exception
	 */
	function deleteVaccinationExam($data)
	{
		$query = "
			select 
				vetl.VaccinationExamTypeLink_id as \"VaccinationExamTypeLink_id\"
			from vc.v_VaccinationExamTypeLink vetl
			where 
				vetl.VaccinationType_id = :VaccinationType_id and
				vetl.VaccinationExamType_id = :VaccinationExamType_id
			";

		$result = $this->db->query($query, array("VaccinationExamType_id" => $data["VaccinationExamType_id"],"VaccinationType_id" => $data["VaccinationType_id"]));
		$result = $result->result('array');

		$query = "
			select
				 error_message as \"Error_Message\",
				 error_code as \"Error_Code\"
			from vc.p_VaccinationExamTypeLink_del(
				VaccinationExamTypeLink_id := :VaccinationExamTypeLink_id,
				IsRemove := 2,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, array("VaccinationExamTypeLink_id" => $result[0]["VaccinationExamTypeLink_id"],"pmUser_id" => $data["pmUser_id"]));
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

	/**
	 * Получаем список препаратов для вакцинации для вида профилактических прививок
	 * @param $VaccinationType_id
	 * @return array
	 */
	function loadVaccinationPrepList($data)
	{

		$query = "
			select 
				vtp.VaccinationTypePrep_id as \"VaccinationTypePrep_id\",
				vtp.Prep_id::integer as \"Prep_id\",
				tn.NAME as \"Prep_Name\",
				firmsnames.NAME || ' ' || firms.ADRMAIN as \"VaccinationTypePrep_FirmName\",
				to_char(vtp.VaccinationTypePrep_begDate, 'dd.mm.yyyy') as \"VaccinationTypePrep_begDate\",
				to_char(vtp.VaccinationTypePrep_endDate, 'dd.mm.yyyy') as \"VaccinationTypePrep_endDate\"
			from vc.v_VaccinationTypePrep vtp
				left join rls.v_PREP prep on vtp.Prep_id = prep.Prep_id
				left join rls.v_DrugNonpropNames dnn on dnn.DrugNonpropNames_id = prep.DrugNonpropNames_id
				left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = prep.TRADENAMEID
				left join rls.v_FIRMS firms on firms.FIRMS_ID = prep.FIRMID
				left join rls.v_FIRMNAMES firmsnames on firms.NAMEID = firmsnames.FIRMNAMES_ID
			where vtp.VaccinationType_id = :VaccinationType_id
			order by vtp.VaccinationTypePrep_id asc
				";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, array("VaccinationType_id" => $data['VaccinationType_id']));
		return (is_object($result)) ? $result->result_array() : [];

	}

	/**
	 * Получаем список препаратов для вакцинации для комбобокса
	 * @param $VaccinationType_id
	 * @return array
	 */
	function loadVaccinationTypePrepComboList($data)
	{
		$FTGGRLS_ID = ($data['VaccinationType_isReaction'] == '2') ? '377' : '380';
		$query = "
			select 
				p.Prep_id as \"Prep_id\", 
				tn.NAME || ' ' || fn.NAME as \"Prep_Name\"
			from rls.v_PREP p 
				left join rls.v_PREP_FTGGRLS pfg on p.Prep_id = pfg.PREP_ID
				left join rls.v_FTGGRLS fg on pfg.FTGGRLS_ID = fg.FTGGRLS_ID
				left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_FIRMS f on f.FIRMS_ID = p.FIRMID
				left join rls.v_FIRMNAMES fn on f.NAMEID = fn.FIRMNAMES_ID
			--where fg.FTGGRLS_ID = :FTGGRLS_ID
			order by p.Prep_id asc
			limit 100
				";

		$result = $this->db->query($query, array("FTGGRLS_ID" => $FTGGRLS_ID));
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Получаем препарат
	 * @param $data
	 * @throws Exception
	 */
	function getVaccinationTypePrep($data)
	{
		$queryParams = ["VaccinationTypePrep_id" => $data["VaccinationTypePrep_id"]];
		$query = "
			select 
				v.Prep_id as \"Prep_id\",
				to_char(v.VaccinationTypePrep_begDate, 'dd.mm.yyyy') as \"VaccinationTypePrep_begDate\",
				to_char(v.VaccinationTypePrep_endDate, 'dd.mm.yyyy') as \"VaccinationTypePrep_endDate\"
			from vc.v_VaccinationTypePrep v
			where 
				v.VaccinationTypePrep_id = :VaccinationTypePrep_id
		";

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}else{
			return $result->result('array');
		}
	}

	/**
	 * Проверяем препарат на дублирование перед сохранением
	 * @param $data
	 * @throws Exception
	 */
	function checkVaccinationPrepBeforeSave($data)
	{
		$where = (!empty($data["VaccinationTypePrep_id"])) ? 'v.VaccinationTypePrep_id <> :VaccinationTypePrep_id and' : '';
		$datewhere = "(
			v.VaccinationTypePrep_begDate >= :VaccinationTypePrep_begDate and v.VaccinationTypePrep_begDate <= :VaccinationTypePrep_endDate or
			v.VaccinationTypePrep_endDate >= :VaccinationTypePrep_begDate and v.VaccinationTypePrep_endDate <= :VaccinationTypePrep_endDate or
			v.VaccinationTypePrep_begDate >= :VaccinationTypePrep_begDate and v.VaccinationTypePrep_endDate <= :VaccinationTypePrep_endDate or
			v.VaccinationTypePrep_begDate <= :VaccinationTypePrep_begDate and v.VaccinationTypePrep_endDate >= :VaccinationTypePrep_endDate or
			v.VaccinationTypePrep_begDate <= :VaccinationTypePrep_endDate and v.VaccinationTypePrep_endDate is null 
		) ";
		if(empty($data['VaccinationTypePrep_endDate'])){
			$datewhere = "( v.VaccinationTypePrep_endDate >= :VaccinationTypePrep_begDate or v.VaccinationTypePrep_endDate is null	) ";
		}


		$query = "
			select
				v.Prep_id as \"Prep_id\",
				v.VaccinationType_id as \"VaccinationType_id\",
				v.VaccinationTypePrep_id as \"VaccinationTypePrep_id\",
				v.VaccinationTypePrep_begDate as \"VaccinationTypePrep_begDate\",
				v.VaccinationTypePrep_endDate as \"VaccinationTypePrep_endDate\"
			from vc.v_VaccinationTypePrep v 
			where 
				{$where}
				v.VaccinationType_id = :VaccinationType_id and
				v.Prep_id = :Prep_id and 
				{$datewhere}
			limit 1
			";
		$result = $this->db->query($query, $data);
		$result = (is_object($result)) ? $result->result_array() : [];

		if(!empty($result[0])){
			throw new Exception("Препарат уже указан как допустимый для данного вида прививки в данном периоде. Сохранение невозможно.");
		}
	}

	/**
	 * Сохранение препарата для вакцинации
	 * @param $VaccinationTypePrep_id
	 * @return array
	 */
	function saveVaccinationPrep($data)
	{
		$procedure = (empty($data["VaccinationTypePrep_id"])) ? "p_VaccinationTypePrep_ins" : "p_VaccinationTypePrep_upd";
		$ins = (empty($data["VaccinationTypePrep_id"])) ? "" : ", VaccinationTypePrep_id := :VaccinationTypePrep_id ";
		$queryParams = [
			"VaccinationTypePrep_id" => !empty($data["VaccinationTypePrep_id"]) ? $data["VaccinationTypePrep_id"] : null,
			"VaccinationType_id" => !empty($data["VaccinationType_id"]) ? $data["VaccinationType_id"] : null,
			"Prep_id" => $data["Prep_id"],
			"VaccinationTypePrep_begDate" => $data["VaccinationTypePrep_begDate"],
			"VaccinationTypePrep_endDate" => !empty($data["VaccinationTypePrep_endDate"]) ? $data["VaccinationTypePrep_endDate"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];

		$this->checkVaccinationPrepBeforeSave($queryParams);

		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\",
				VaccinationTypePrep_id as \"VaccinationTypePrep_id\"
			from vc.{$procedure} (
				Prep_id := :Prep_id,
				VaccinationType_id := :VaccinationType_id,
				VaccinationTypePrep_begDate := :VaccinationTypePrep_begDate,
				VaccinationTypePrep_endDate := :VaccinationTypePrep_endDate,
				pmUser_id := :pmUser_id {$ins}
				);
		";

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

	/**
	 * Удаляем препарат для вакцинации
	 * @param $data
	 * @throws Exception
	 */
	function deleteVaccinationPrep($data)
	{

		$query = "
			select
				error_message as \"Error_Message\",
				error_code as \"Error_Code\"
			from vc.p_VaccinationTypePrep_del (
				VaccinationTypePrep_id := :VaccinationTypePrep_id,
				IsRemove := 2,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, array("VaccinationTypePrep_id" => $data["VaccinationTypePrep_id"],"pmUser_id" => $data["pmUser_id"]));
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

}

