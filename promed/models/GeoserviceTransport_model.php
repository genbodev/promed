<?php
class GeoserviceTransport_model extends swModel {
	
	/**
	 * Метод получения типа геосервиса
	 * @return array
	 */
	public function getGeoserviceType($data = array()){

		if(empty($_SESSION['CurMedService_id']) && empty($data['MedService_id'])){
			return false;
		}

		$query = "
			SELECT 
				ast.ApiServiceType_Name
			FROM v_MedService ms with(nolock)
			INNER JOIN v_ApiServiceType ast on ast.ApiServiceType_id = ms.ApiServiceType_id
			WHERE ms.MedService_id = :MedService_id
		";
		
		$params = array(
			'MedService_id' => !empty($_SESSION['CurMedService_id']) ? $_SESSION['CurMedService_id'] : $data['MedService_id']
		);
		
		$result = $this->db->query($query, $params);
		return ( is_object($result) )? ($result->result('array')) : false;
	}
}