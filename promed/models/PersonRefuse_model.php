<?php defined('BASEPATH') or die ('No direct script access allowed');

class PersonRefuse_model extends swModel {
	/**
	 * Method comment
	 */
	public function deletePersonRefuse($data) {
		$query = "update PersonRefuse 
			set PersonRefuse_IsRefuse=1,
			PersonRefuse_updDT=GETDATE(),
			pmUser_updID = :pmUser_id
			where  PersonRefuse_id = :PersonRefuse_id";
		$params = array(
			"pmUser_id"=>$data["pmUser_id"],
			"PersonRefuse_id"=>$data["PersonRefuse_id"]
		);
		//echo getDebugSQL($query, $params);exit();
		$result = $this->db->query($query, $params);
		return array(array("success"=>"true"));
	}

	/**
	 * Method comment
	 */
	public function getPersonRefuseId($data){
		$query = "
			select top 1 
				PersonRefuse_id
			from 
				v_PersonRefuse with (nolock)
			where  
				PersonRefuse_IsRefuse=2 and
				Person_id = :Person_id and
				PersonRefuse_Year = YEAR(dbo.tzGetDate())
		";
		$params = array(
			"Person_id"=>$data["Person_id"]
		);
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}else{
			return array(array("success"=>"true"));
		}
	}
}
