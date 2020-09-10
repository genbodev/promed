<?php

/**
 * Class GeoserviceTransport_model
 *
 * @property CI_DB_driver $db
 */
class GeoserviceTransport_model extends swPgModel
{
	/**
	 * Метод получения типа геосервиса
	 * @param array $data
	 * @return array|bool
	 */
	public function getGeoserviceType($data = [])
	{
		if (empty($_SESSION["CurMedService_id"]) && empty($data["MedService_id"])) {
			return false;
		}
		$query = "
			select ast.ApiServiceType_Name as \"ApiServiceType_Name\"
			from
				v_MedService ms
				inner join v_ApiServiceType ast on ast.ApiServiceType_id = ms.ApiServiceType_id
			where ms.MedService_id = :MedService_id
		";
		$params = [
			"MedService_id" => !empty($_SESSION["CurMedService_id"]) ? $_SESSION["CurMedService_id"] : $data["MedService_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}
}