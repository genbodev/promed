<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * LpuOrgServed_model - модель для работы с обслуживаемыми организациями
 * http://redmine.swan.perm.ru/projects/promedweb-dlo/repository/revisions/10303
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      30.05.2012
 *
 * @property CI_DB_driver $db
 */
class LpuOrgServed_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение конкретной обслуживаемой организации
	 * @param $data
	 * @return array|bool
	 */
	function getCurrentLpuOrgServed($data)
	{
		$queryParams = [];
		$queryParams['LpuOrgServed_id'] = $data['LpuOrgServed_id'];
		$query = "
			select
				LOS.Org_id as \"Org_id\",
				to_char(LOS.LpuOrgServed_begDate, '{$this->dateTimeForm104}') as \"LpuOrgServed_begDate\",
		 		to_char(LOS.LpuOrgServed_endDate, '{$this->dateTimeForm104}') as \"LpuOrgServed_endDate\",
				LpuOrgServiceType_id as \"LpuOrgServiceType_id\"
			from v_LpuOrgServed LOS
			where LOS.LpuOrgServed_id = :LpuOrgServed_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка обслуживаемых организаций в ЛПУ
	 * @param $data
	 * @return array|bool
	 */
	function getLpuOrgServed($data)
	{
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		$filter = "";
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filter .= " and (LOS.LpuOrgServed_endDate is null or LOS.LpuOrgServed_endDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filter .= " and LOS.LpuOrgServed_endDate <= tzgetdate()";
		}
		$query = "
			select
				LOS.LpuOrgServed_id as \"LpuOrgServed_id\",
		 		LOS.Org_id as \"Org_id\",
		 		O.Org_Name as \"Org_Name\",
		 		O.Org_Nick as \"Org_Nick\",
				to_char(LOS.LpuOrgServed_begDate, '{$this->dateTimeForm104}') as \"LpuOrgServed_begDate\",
		 		to_char(LOS.LpuOrgServed_endDate, '{$this->dateTimeForm104}') as \"LpuOrgServed_endDate\",
				LOST.LpuOrgServiceType_Name as \"LpuOrgServiceType_Name\"
  			from
  				v_LpuOrgServed LOS
  				inner join Org O ON O.Org_id = LOS.Org_id
				left join LpuOrgServiceType LOST on LOST.LpuOrgServiceType_id = LOS.LpuOrgServiceType_id
  			where LOS.Lpu_id = :Lpu_id {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return CI_DB_result
	 */
	function saveLpuOrgServed($data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"LpuOrgServed_id" => $data["LpuOrgServed_id"],
			"Org_id" => $data["Org_id"],
			"LpuOrgServed_begDate" => $data["LpuOrgServed_begDate"],
			"LpuOrgServed_endDate" => $data["LpuOrgServed_endDate"],
			"LpuOrgServiceType_id" => $data["LpuOrgServiceType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$procedure = (!isset($data["LpuOrgServed_id"])) ? "p_LpuOrgServed_ins" : "p_LpuOrgServed_upd";
		$selectString = "
		    lpuorgserved_id as \"LpuOrgServed_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    lpuorgserved_id := :LpuOrgServed_id,
			    lpu_id := :Lpu_id,
			    org_id := :Org_id,
			    lpuorgserved_begdate := :LpuOrgServed_begDate,
			    lpuorgserved_enddate := :LpuOrgServed_endDate,
			    lpuorgservicetype_id := :LpuOrgServiceType_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->queryResult($query, $queryParams);
		return $result;
	}

	/**
	 * Удаление
	 * @param $data
	 * @return mixed
	 */
	function deleteLpuOrgServed($data)
	{
		$queryparams = ["LpuOrgServed_id" => $data["LpuOrgServed_id"]];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_lpuorgserved_del(lpuorgserved_id := :LpuOrgServed_id);
		";
		$result = $this->db->query($query, $queryparams);
		return $result;
	}
}