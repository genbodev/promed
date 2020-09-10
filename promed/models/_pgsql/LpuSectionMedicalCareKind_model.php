<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Вид медицинской помощи на отделении
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Aleksandr Chebukin
 * @version
 *
 * @property CI_DB_driver $db
 */
class LpuSectionMedicalCareKind_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает список
	 * @param $data
	 * @return array|bool
	 */
	function loadList($data)
	{
		$query = "
			select
				LSMCK.LpuSectionMedicalCareKind_id as \"LpuSectionMedicalCareKind_id\",
			    MCK.MedicalCareKind_id as \"MedicalCareKind_id\",
			    MCK.MedicalCareKind_Code as \"MedicalCareKind_Code\",
			    MCK.MedicalCareKind_Name as \"MedicalCareKind_Name\",
			    to_char(LSMCK.LpuSectionMedicalCareKind_begDate, '{$this->dateTimeForm104}') as \"LpuSectionMedicalCareKind_begDate\",
			    to_char(LSMCK.LpuSectionMedicalCareKind_endDate, '{$this->dateTimeForm104}') as \"LpuSectionMedicalCareKind_endDate\",
			    1 \"RecordStatus_Code\"
			from
				v_LpuSectionMedicalCareKind LSMCK
				inner join fed.v_MedicalCareKind MCK on MCK.MedicalCareKind_id = LSMCK.MedicalCareKind_id
			where LpuSection_id = :LpuSection_id
		";
		$queryParams = ["LpuSection_id" => $data["LpuSection_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}