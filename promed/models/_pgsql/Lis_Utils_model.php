<?php
/**
* Utils - модель для вспомогательных операций
* 1. Объединение записей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      15.07.2009
*/

class Lis_Utils_model extends SwPgModel {
	/**
	 *	Конструктор
	 */
    function __construct() {
        parent::__construct();
    }

	/**
	 * Проверка, установлен ли для данной мед. службы флаг "файловая интеграция"
	 */
	function withFileIntegration($data)
	{
		$query = "
			select
				coalesce(MedService_IsFileIntegration, 0) as result
			from
				v_MedService
			where
				MedService_id = :MedService_id		
		";

		$result = $this->db->query($query, $data);

		$result = $result->result('array');

		if (isset($result[0]))
			return ($result[0]['result'] == 2);
		else return false;
	}

	/**
	 * Список МО
	*/
	function getLpuList($data)
	{
		$query = "
			SELECT
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Code as \"LpuSection_Code\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LS.Lpu_id as \"Lpu_id\",
				LS.LpuBuilding_id as \"LpuBuilding_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				LUT.LpuUnitType_id as \"LpuUnitType_id\",
				LUT.LpuUnitType_Code as \"LpuUnitType_Code\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
			FROM
				v_LpuSection LS
				inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
			WHERE
				LS.Lpu_id = :filterLpu_id
			ORDER BY
				LS.LpuSection_Code
		";

		return $this->queryResult($query, $data);
	}
}
