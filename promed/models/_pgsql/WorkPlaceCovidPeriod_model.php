<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * WorkPlaceCovidPeriod_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 */

class WorkPlaceCovidPeriod_model extends swPgModel {

	/**
	 * Загрузка
	 */
	function load($data) {
		
		$result = $this->queryResult("
			select 
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Person_Fio || ' (' || to_char(msf.Person_BirthDay, 'DD.MM.YYYY') || ')' as \"Person_Fio\", 
				coalesce(ls.LpuSection_FullName, lu.LpuUnit_Name, lb.LpuBuilding_Name, Lpu.Lpu_Nick) || ' - ' || ps.PostMed_Name as \"PostMed_Name\"
			from v_MedStaffFact msf
				LEFT JOIN v_Lpu Lpu on Lpu.Lpu_id = msf.Lpu_id
				LEFT JOIN v_LpuBuilding lb on lb.LpuBuilding_id = msf.LpuBuilding_id
				LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id
				LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				LEFT JOIN v_PostMed ps on ps.PostMed_id = msf.Post_id
			where msf.MedStaffFact_id = :MedStaffFact_id
		", $data);
		
		$result[0]['WorkPlaceCovidPeriodData'] = $this->queryResult("
			select 
				WorkPlaceCovidPeriod_id as \"WorkPlaceCovidPeriod_id\"
				,to_char(WorkPlaceCovidPeriod_begDate, 'DD.MM.YYYY') || ' - ' ||
					coalesce(to_char(WorkPlaceCovidPeriod_endDate, 'DD.MM.YYYY'), '') as \"WorkPlaceCovidPeriod_DateRange\"
				,1 as \"RecordStatus_Code\"
			from v_WorkPlaceCovidPeriod
			where WorkPlace_id = :MedStaffFact_id
			order by WorkPlaceCovidPeriod_begDate
		", $data);
		
		return $result;
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		foreach($data['WorkPlaceCovidPeriodData'] as $WorkPlaceCovidPeriod) {
			$WorkPlaceCovidPeriod = (array)$WorkPlaceCovidPeriod;
			$WorkPlaceCovidPeriod['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$WorkPlaceCovidPeriod['pmUser_id'] = $data['pmUser_id'];
			switch($WorkPlaceCovidPeriod['RecordStatus_Code']) {
				case 0:
				case 2:
					$resp = $this->saveWorkPlaceCovidPeriod($WorkPlaceCovidPeriod);
					break;
				case 3:
					$resp = $this->deleteWorkPlaceCovidPeriod($WorkPlaceCovidPeriod);
			}
		}
		return ['success' => true];
	}
	
	/**
	 * Сохранение 
	 */
	function saveWorkPlaceCovidPeriod($data) {
		
		$proc = empty($data['WorkPlaceCovidPeriod_id']) ? 'p_WorkPlaceCovidPeriod_ins' : 'p_WorkPlaceCovidPeriod_upd';

		return $this->execCommonSP($proc, [
			'WorkPlaceCovidPeriod_id' => $data['WorkPlaceCovidPeriod_id'],
			'WorkPlace_id' => $data['MedStaffFact_id'],
			'WorkPlaceCovidPeriod_begDate' => $data['WorkPlaceCovidPeriod_begDate'],
			'WorkPlaceCovidPeriod_endDate' => $data['WorkPlaceCovidPeriod_endDate'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}

	/**
	 * Удаление
	 */
	function deleteWorkPlaceCovidPeriod($data) {
		
		if (empty($data['WorkPlaceCovidPeriod_id'])) {
			return false;
		}

		return $this->execCommonSP('p_WorkPlaceCovidPeriod_del', [
			'WorkPlaceCovidPeriod_id' => $data['WorkPlaceCovidPeriod_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}
}