<?php defined('BASEPATH') or die ('No direct script access allowed');
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
 */
class LpuSectionMedicalCareKind_model extends swModel {

	/**
	 * Конструктор, например
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 * Возвращает список
	 */
	function loadList($data) {

		$q = "
			SELECT
				LSMCK.LpuSectionMedicalCareKind_id
				,MCK.MedicalCareKind_id
				,MCK.MedicalCareKind_Code
				,MCK.MedicalCareKind_Name
				,convert(varchar(10), LSMCK.LpuSectionMedicalCareKind_begDate, 104) as LpuSectionMedicalCareKind_begDate
				,convert(varchar(10), LSMCK.LpuSectionMedicalCareKind_endDate, 104) as LpuSectionMedicalCareKind_endDate
				,1 RecordStatus_Code
			FROM
				dbo.v_LpuSectionMedicalCareKind LSMCK WITH (NOLOCK)
				INNER JOIN fed.v_MedicalCareKind MCK WITH (NOLOCK) ON MCK.MedicalCareKind_id = LSMCK.MedicalCareKind_id
			WHERE
				LpuSection_id = ?
		";
		$result = $this->db->query($q, array($data['LpuSection_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


}