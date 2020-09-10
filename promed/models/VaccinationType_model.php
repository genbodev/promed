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
			$filterList[] = "( select count(1) from vc.Vaccination vac with (nolock) where vac.VaccinationType_id = vt.VaccinationType_id and vac.Vaccination_isNacCal = 2 ) <> 0 ";

		if (!empty($data["Vaccination_isEpidemic"]))
			$filterList[] = "( select count(1) from vc.Vaccination vac with (nolock) where vac.VaccinationType_id = vt.VaccinationType_id and vac.Vaccination_isEpidemic = 2 ) <> 0 ";

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
				vt.VaccinationType_id,
				vt.VaccinationType_isReaction,
				vt.VaccinationType_Code,
				vt.VaccinationType_Name,
				vt.VaccinationType_SortCode,
				IIF((
					select count(1) 
					from vc.Vaccination vac with (nolock) 
					where 
						vac.VaccinationType_id = vt.VaccinationType_id and
						vac.Vaccination_isNacCal   = 2
					) <> 0, 'true', 'false') as Vaccination_isNacCal,
				IIF((
					select count(1) 
					from vc.Vaccination vac with (nolock) 
					where 
						vac.VaccinationType_id = vt.VaccinationType_id and 
						vac.Vaccination_isEpidemic = 2
					) <> 0, 'true', 'false') as Vaccination_isEpidemic,
				stuff((
					select 
						',	' + vet.VaccinationExamType_Name 
					from vc.v_VaccinationExamType vet with (nolock), vc.v_VaccinationExamTypeLink vetl
					where 
						vetl.VaccinationExamType_id = vet.VaccinationExamType_id and
						vetl.VaccinationType_id = vt.VaccinationType_id
					order by vet.VaccinationExamType_SortCode asc
					for xml path('')
					), 1, 2, '') as ExamString,
				(
					select top 1
						cast(v.Vaccination_begAge as varchar(5)) +
						case 
							when v.VaccinationAgeType_bid = 1 
								and (right(v.Vaccination_begAge,2) >= 11 and right(v.Vaccination_begAge,2) <= 14 or right(v.Vaccination_begAge,1) >= 5 and right(v.Vaccination_begAge,1) <= 9 or right(v.Vaccination_begAge,1) = 0) then ' дней' 
							when v.VaccinationAgeType_bid = 1 
								and (right(v.Vaccination_begAge,1) >= 2  and right(v.Vaccination_begAge,1) <= 4) then ' дня' 
							when v.VaccinationAgeType_bid = 1 
								and (right(v.Vaccination_begAge,1) = 1) then ' день' 
		
							when v.VaccinationAgeType_bid = 2 
								and (right(v.Vaccination_begAge,2) >= 11 and right(v.Vaccination_begAge,2) <= 14 or right(v.Vaccination_begAge,1) >= 5 and right(v.Vaccination_begAge,1) <= 9 or right(v.Vaccination_begAge,1) = 0) then ' месяцев' 
							when v.VaccinationAgeType_bid = 2 
								and (right(v.Vaccination_begAge,1) >= 2  and right(v.Vaccination_begAge,1) <= 4) then ' месяца' 
							when v.VaccinationAgeType_bid = 2 
								and (right(v.Vaccination_begAge,1) = 1) then ' месяц' 
		
							when v.VaccinationAgeType_bid = 3 
								and (right(v.Vaccination_begAge,2) >= 11 and right(v.Vaccination_begAge,2) <= 14 or right(v.Vaccination_begAge,1) >= 5 and right(v.Vaccination_begAge,1) <= 9 or right(v.Vaccination_begAge,1) = 0) then ' лет' 
							when v.VaccinationAgeType_bid = 3 
								and (right(v.Vaccination_begAge,1) >= 2  and right(v.Vaccination_begAge,1) <= 4) then ' года' 
							when v.VaccinationAgeType_bid = 3 
								and (right(v.Vaccination_begAge,1) = 1) then ' год' 
						end
					from vc.Vaccination v with (nolock)
					where 
						v.VaccinationType_id = vt.VaccinationType_id
					order by (case when v.VaccinationAgeType_bid = 2 then v.Vaccination_begAge * 30 when v.VaccinationAgeType_bid = 3 then v.Vaccination_begAge * 365 else v.Vaccination_begAge * 1 end ) asc
				) as minAge,
				convert(varchar(10), vt.VaccinationType_begDate, 104) as VaccinationType_begDate,
				convert(varchar(10), vt.VaccinationType_endDate, 104) as VaccinationType_endDate
			from vc.VaccinationType vt with(nolock)
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :VaccinationType_id;
			exec vc.{$procedure}
				@VaccinationType_id = @Res output,
				@VaccinationType_Code = :VaccinationType_Code,
				@VaccinationType_Name = :VaccinationType_Name,
				@VaccinationType_isReaction = :VaccinationType_isReaction,
				@VaccinationType_begDate = :VaccinationType_begDate,
				@VaccinationType_endDate = :VaccinationType_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = :VaccinationType_id;
			exec vc.p_VaccinationType_del
				@VaccinationType_id = @Res,
				@IsRemove = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				v.Vaccination_id
			from vc.v_Vaccination v with(nolock)
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
				vrgl.VaccinationRiskGroup_id
			from vc.v_VaccinationRiskGroupLink vrgl with(nolock)
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
					vetl.VaccinationExamType_id
				from vc.v_VaccinationExamTypeLink vetl with(nolock)
				where 
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
				vtp.VaccinationTypePrep_id
			from vc.v_VaccinationTypePrep vtp with(nolock)
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
				v.Vaccination_id,
				v.Vaccination_Name,
				v.Vaccination_Code,
				v.Vaccination_Nick,
				v.Vaccination_isNacCal,
				isNacCal.YesNo_Name as Vaccination_isNacCal_Value,
				v.Vaccination_isEpidemic,
				isEpidemic.YesNo_Name as Vaccination_isEpidemic_Value,
				v.VaccinationRiskGroupAccess_id,
				RiskGroupAccess.VaccinationRiskGroupAccess_Name,
				vatp.Vaccination_Name as Vaccination_LastName,
				cast(v.Vaccination_begAge as varchar(5)) +
				CASE 
					when v.VaccinationAgeType_bid = 1 
						and (right(v.Vaccination_begAge,2) >= 11 and right(v.Vaccination_begAge,2) <= 14 or right(v.Vaccination_begAge,1) >= 5 and right(v.Vaccination_begAge,1) <= 9 or right(v.Vaccination_begAge,1) = 0) then ' дней' 
					when v.VaccinationAgeType_bid = 1 
						and (right(v.Vaccination_begAge,1) >= 2  and right(v.Vaccination_begAge,1) <= 4) then ' дня' 
					when v.VaccinationAgeType_bid = 1 
						and (right(v.Vaccination_begAge,1) = 1) then ' день' 

					when v.VaccinationAgeType_bid = 2 
						and (right(v.Vaccination_begAge,2) >= 11 and right(v.Vaccination_begAge,2) <= 14 or right(v.Vaccination_begAge,1) >= 5 and right(v.Vaccination_begAge,1) <= 9 or right(v.Vaccination_begAge,1) = 0) then ' месяцев' 
					when v.VaccinationAgeType_bid = 2 
						and (right(v.Vaccination_begAge,1) >= 2  and right(v.Vaccination_begAge,1) <= 4) then ' месяца' 
					when v.VaccinationAgeType_bid = 2 
						and (right(v.Vaccination_begAge,1) = 1) then ' месяц' 

					when v.VaccinationAgeType_bid = 3 
						and (right(v.Vaccination_begAge,2) >= 11 and right(v.Vaccination_begAge,2) <= 14 or right(v.Vaccination_begAge,1) >= 5 and right(v.Vaccination_begAge,1) <= 9 or right(v.Vaccination_begAge,1) = 0) then ' лет' 
					when v.VaccinationAgeType_bid = 3 
						and (right(v.Vaccination_begAge,1) >= 2  and right(v.Vaccination_begAge,1) <= 4) then ' года' 
					when v.VaccinationAgeType_bid = 3 
						and (right(v.Vaccination_begAge,1) = 1) then ' год' 
				end as Vaccination_minAge,
				cast(v.Vaccination_endAge as varchar(5)) +
				case 
					when v.VaccinationAgeType_eid = 1
						and (right(v.Vaccination_endAge,2) >= 11 and right(v.Vaccination_endAge,2) <= 14 or right(v.Vaccination_endAge,1) >= 5 and right(v.Vaccination_endAge,1) <= 9 or right(v.Vaccination_endAge,1) = 0) then ' дней'
					when v.VaccinationAgeType_eid = 1
						and (right(v.Vaccination_endAge,1) >= 2  and right(v.Vaccination_endAge,1) <= 4) then ' дня'
					when v.VaccinationAgeType_eid = 1
						and (right(v.Vaccination_endAge,1) = 1) then ' день'
					
					when v.VaccinationAgeType_eid = 2
						and (right(v.Vaccination_endAge,2) >= 11 and right(v.Vaccination_endAge,2) <= 14 or right(v.Vaccination_endAge,1) >= 5 and right(v.Vaccination_endAge,1) <= 9 or right(v.Vaccination_endAge,1) = 0) then ' месяцев'
					when v.VaccinationAgeType_eid = 2
						and (right(v.Vaccination_endAge,1) >= 2  and right(v.Vaccination_endAge,1) <= 4) then ' месяца'
					when v.VaccinationAgeType_eid = 2
						and (right(v.Vaccination_endAge,1) = 1) then ' месяц'
					
					when v.VaccinationAgeType_eid = 3
						and (right(v.Vaccination_endAge,2) >= 11 and right(v.Vaccination_endAge,2) <= 14 or right(v.Vaccination_endAge,1) >= 5 and right(v.Vaccination_endAge,1) <= 9 or right(v.Vaccination_endAge,1) = 0) then ' лет'
					when v.VaccinationAgeType_eid = 3
						and (right(v.Vaccination_endAge,1) >= 2  and right(v.Vaccination_endAge,1) <= 4) then ' года'
					when v.VaccinationAgeType_eid = 3
						and (right(v.Vaccination_endAge,1) = 1) then ' год'
				end as Vaccination_maxAge,
				v.Vaccination_isSingle,
				convert(varchar,cast(v.Vaccination_begDate as datetime),104) as Vaccination_begDate,
				convert(varchar,cast(v.Vaccination_endDate as datetime),104) as Vaccination_endDate
			from
				vc.v_Vaccination v with(nolock)
				left join YesNo isNacCal with(nolock) on isNacCal.YesNo_id = v.Vaccination_isNacCal
				left join YesNo isEpidemic with(nolock) on isEpidemic.YesNo_id = v.Vaccination_isEpidemic
				left join vc.v_Vaccination vatp with(nolock) on vatp.Vaccination_id = v.Vaccination_pid
				left join vc.v_VaccinationAgeType AgeType_bid with(nolock) on AgeType_bid.VaccinationAgeType_id = v.VaccinationAgeType_bid
				left join vc.v_VaccinationAgeType AgeType_eid with(nolock) on AgeType_eid.VaccinationAgeType_id = v.VaccinationAgeType_eid
				left join YesNo isSingle with(nolock) on isSingle.YesNo_id = v.Vaccination_isSingle
				left join vc.v_VaccinationRiskGroupAccess RiskGroupAccess with(nolock) on RiskGroupAccess.VaccinationRiskGroupAccess_id = v.VaccinationRiskGroupAccess_id
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
				v.Vaccination_id, 
				v.Vaccination_Code + ' ' + v.Vaccination_Name AS Vaccination_Name,
				v.Vaccination_isNacCal,
				v.Vaccination_isEpidemic
			from vc.v_Vaccination v with(nolock)
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
		$result = $this->db->query(" select vrga.VaccinationRiskGroupAccess_id, vrga.VaccinationRiskGroupAccess_Name from vc.v_VaccinationRiskGroupAccess vrga with(nolock)");
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
				v.Vaccination_id,
				v.Vaccination_Name,
				v.Vaccination_Code,
				v.Vaccination_Nick,
				v.Vaccination_pid,
				v.Vaccination_isNacCal,
				v.Vaccination_isEpidemic,
				v.Vaccination_begAge,
				v.VaccinationAgeType_bid,
				v.Vaccination_endAge,
				v.VaccinationAgeType_eid,
				v.VaccinationRiskGroupAccess_id,
				v.Vaccination_isSingle,
				convert(varchar,cast(v.Vaccination_begDate as datetime),104) as Vaccination_begDate,
				convert(varchar,cast(v.Vaccination_endDate as datetime),104) as Vaccination_endDate
			from vc.v_Vaccination v with (nolock) 
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
					pv.Vaccination_id
				from vc.v_Vaccination v with(nolock) 
					left join vc.v_Vaccination pv with(nolock) on v.Vaccination_pid = pv.Vaccination_id
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :Vaccination_id;
			exec vc.{$procedure}
				@Vaccination_id = @Res output,
				@VaccinationType_id = :VaccinationType_id,
				@Vaccination_Name = :Vaccination_Name,
				@Vaccination_Code = :Vaccination_Code,
				@Vaccination_Nick = :Vaccination_Nick,
				@Vaccination_pid = :Vaccination_pid,
				@Vaccination_isNacCal = :Vaccination_isNacCal,
				@Vaccination_isEpidemic = :Vaccination_isEpidemic,
				@Vaccination_begAge = :Vaccination_begAge,
				@VaccinationAgeType_bid = :VaccinationAgeType_bid,
				@Vaccination_endAge = :Vaccination_endAge,
				@VaccinationAgeType_eid = :VaccinationAgeType_eid,
				@VaccinationRiskGroupAccess_id = :VaccinationRiskGroupAccess_id,
				@Vaccination_isSingle = :Vaccination_isSingle,
				@Vaccination_begDate = :Vaccination_begDate,
				@Vaccination_endDate = :Vaccination_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as Vaccination_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
					v.VaccinationType_id 
				from vc.v_Vaccination v with(nolock) 
				where 
					v.Vaccination_id = :Vaccination_id 
 			";
			$result = $this->db->query($query, array("Vaccination_id" => $data["Vaccination_id"]));
			$result = $result->result('array');
			$data['VaccinationType_id'] = $result[0]["VaccinationType_id"];
		}

		$query = "
			select 
				top 1 vd.VaccinationData_id
			from vc.v_Vaccination v with(nolock)
				left join vc.v_VaccinationData vd with(nolock) on v.Vaccination_id = vd.Vaccination_id
			where 
				v.VaccinationType_id = :VaccinationType_id and 
				vd.VaccinationData_id is not null
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = :Vaccination_id;
			exec vc.p_Vaccination_del
				@Vaccination_id = @Res,
				@IsRemove = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as Vaccination_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				vrg.VaccinationRiskGroup_id,
				vrg.VaccinationRiskGroup_Name, 
				vrgl.VaccinationType_id
			from vc.v_VaccinationRiskGroup vrg  with(nolock)
				left join  vc.v_VaccinationRiskGroupLink vrgl  with(nolock) on vrgl.VaccinationRiskGroup_id = vrg.VaccinationRiskGroup_id
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
				vrg.VaccinationRiskGroup_id, 
				vrg.VaccinationRiskGroup_Name, 
				vrgl.VaccinationRiskGroupLink_id
			from vc.v_VaccinationRiskGroup vrg with(nolock) 
				left join vc.VaccinationRiskGroupLink vrgl with(nolock) on vrg.VaccinationRiskGroup_id = vrgl.VaccinationRiskGroup_id and vrgl.VaccinationType_id = :VaccinationType_id
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = :VaccinationRiskGroupLink_id;
			exec vc.p_VaccinationRiskGroupLink_ins
				@VaccinationRiskGroupLink_id = @Res,
				@VaccinationRiskGroup_id = :VaccinationRiskGroup_id,
				@VaccinationType_id = :VaccinationType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationRiskGroupLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
 				vetl.VaccinationRiskGroupLink_id 
 			from vc.v_VaccinationRiskGroupLink vetl with (nolock) 
 			where 
 				vetl.VaccinationType_id = :VaccinationType_id and
 				vetl.VaccinationRiskGroup_id = :VaccinationRiskGroup_id 
 		";

		$result = $this->db->query($query, array("VaccinationRiskGroup_id" => $data["VaccinationRiskGroup_id"],"VaccinationType_id" => $data["VaccinationType_id"]));
		$result = $result->result('array');

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = :VaccinationRiskGroupLink_id;
			exec vc.p_VaccinationRiskGroupLink_del
				@VaccinationRiskGroupLink_id = @Res,
				@IsRemove = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationRiskGroupLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
					vet.VaccinationExamType_id,
					vet.VaccinationExamType_Name,
					vetl.VaccinationType_id
				from vc.v_VaccinationExamType vet  with(nolock)
				left join  vc.v_VaccinationExamTypeLink vetl  with(nolock) on vetl.VaccinationExamType_id = vet.VaccinationExamType_id
				where vetl.VaccinationType_id = :VaccinationType_id
				order by vetl.VaccinationExamTypeLink_id
			";
		/**@var CI_DB_result $result */
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
				vet.VaccinationExamType_id, 
				vet.VaccinationExamType_Name, 
				vetl.VaccinationExamTypeLink_id
			from vc.v_VaccinationExamType vet with(nolock) 
			left join vc.VaccinationExamTypeLink vetl with(nolock) on vet.VaccinationExamType_id = vetl.VaccinationExamType_id and vetl.VaccinationType_id = :VaccinationType_id
			where 1=1
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = :VaccinationExamTypeLink_id;
			exec vc.p_VaccinationExamTypeLink_ins
				@VaccinationExamTypeLink_id = @Res,
				@VaccinationExamType_id = :VaccinationExamType_id,
				@VaccinationType_id = :VaccinationType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationExamTypeLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				vetl.VaccinationExamTypeLink_id 
			from vc.v_VaccinationExamTypeLink vetl with(nolock) 
			where 
				vetl.VaccinationType_id = :VaccinationType_id and
				vetl.VaccinationExamType_id = :VaccinationExamType_id
			";

		$result = $this->db->query($query, array("VaccinationExamType_id" => $data["VaccinationExamType_id"],"VaccinationType_id" => $data["VaccinationType_id"]));
		$result = (is_object($result)) ? $result->result_array() : [];

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = :VaccinationExamTypeLink_id;
			exec vc.p_VaccinationExamTypeLink_del
				@VaccinationExamTypeLink_id = @Res,
				@IsRemove = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationExamTypeLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				vtp.VaccinationTypePrep_id,
				vtp.Prep_id,
				tn.NAME as Prep_Name,
				firmsnames.NAME + ' ' + firms.ADRMAIN as VaccinationTypePrep_FirmName,
				convert(varchar(10), vtp.VaccinationTypePrep_begDate, 104) as VaccinationTypePrep_begDate,
				convert(varchar(10), vtp.VaccinationTypePrep_endDate, 104) as VaccinationTypePrep_endDate
			from vc.v_VaccinationTypePrep vtp with(nolock)
				left join rls.v_PREP prep with(nolock) on vtp.Prep_id = prep.Prep_id
				left join rls.v_DrugNonpropNames dnn with(nolock) on dnn.DrugNonpropNames_id = prep.DrugNonpropNames_id
				left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = prep.TRADENAMEID
				left join rls.v_FIRMS firms with(nolock) on firms.FIRMS_ID = prep.FIRMID
				left join rls.v_FIRMNAMES firmsnames with(nolock) on firms.NAMEID = firmsnames.FIRMNAMES_ID
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
				p.Prep_id, 
				tn.NAME + ' ' + fn.NAME as Prep_Name
			from rls.v_PREP p with(nolock) 
				left join rls.v_PREP_FTGGRLS pfg with(nolock) on p.Prep_id = pfg.PREP_ID
				left join rls.v_FTGGRLS fg with(nolock) on pfg.FTGGRLS_ID = fg.FTGGRLS_ID
				left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_FIRMS f with(nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.v_FIRMNAMES fn with(nolock) on f.NAMEID = fn.FIRMNAMES_ID
			where fg.FTGGRLS_ID = :FTGGRLS_ID
			order by p.Prep_id asc
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
				v.Prep_id,
				convert(varchar,cast(v.VaccinationTypePrep_begDate as datetime),104) as VaccinationTypePrep_begDate,
				convert(varchar,cast(v.VaccinationTypePrep_endDate as datetime),104) as VaccinationTypePrep_endDate
			from vc.v_VaccinationTypePrep v with(nolock) 
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
			select top 1
				v.Prep_id,
				v.VaccinationType_id,
				v.VaccinationTypePrep_id,
				v.VaccinationTypePrep_begDate,
				v.VaccinationTypePrep_endDate
			from vc.v_VaccinationTypePrep v with(nolock) 
			where 
				{$where}
				v.VaccinationType_id = :VaccinationType_id and
				v.Prep_id = :Prep_id and 
				{$datewhere}
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :VaccinationTypePrep_id;
			exec vc.{$procedure}
				@VaccinationTypePrep_id = @Res output,
				@Prep_id = :Prep_id,
				@VaccinationType_id = :VaccinationType_id,
				@VaccinationTypePrep_begDate = :VaccinationTypePrep_begDate,
				@VaccinationTypePrep_endDate = :VaccinationTypePrep_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationTypePrep_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = :VaccinationTypePrep_id;
			exec vc.p_VaccinationTypePrep_del
				@VaccinationTypePrep_id = @Res,
				@IsRemove = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VaccinationTypePrep_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array("VaccinationTypePrep_id" => $data["VaccinationTypePrep_id"],"pmUser_id" => $data["pmUser_id"]));
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
	}

}

