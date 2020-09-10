<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/MzDrugRequest_model.php');

class Ufa_MzDrugRequest_model extends MzDrugRequest_model
{
	/**
	 * construct
	 */
	function __construct()
	{
		//parent::__construct();
		parent::__construct();
	}

	/**
	 * Сохранение списка персональной разнарядки
	 */
	function saveDrugRequestPersonOrderList($data)
	{
		$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				KolAll as \"KolAll\",
				Kol as \"Kol\"
			from r2.p_DrugRequestPersonOrderList_ins (
				DrugRequest_id := :DrugRequest_id,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id,
				Persons := :Persons
			);
		";

		$queryParams = array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Persons' => $data['Persons'],
			'pmUser_id' => $data['pmUser_id']
		);

		$response = $this->db->query($query, $queryParams);

		if (is_object($response)) {
			//var_dump($result);
			$response = $response->result('array');
			$result = $response[0];
			$result['success'] = true;
			return $result;//->result('array');
		} else {
			return false;
		}
		throw new Exception(!empty($result['Error_Msg']) ? $result['Error_Msg'] : 'При сохранении строки разнарядки произошла ошибка');
	}


}