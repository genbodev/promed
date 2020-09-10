<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/MzDrugRequest_model.php');

class Ufa_MzDrugRequest_model extends MzDrugRequest_model {
	/**
	 * construct
	 */
	function __construct() {
		//parent::__construct();
            parent::__construct();
	}

   /**
	 * Сохранение списка персональной разнарядки
	 */	
	function saveDrugRequestPersonOrderList($data) {
		$query = "
			Declare
			@DrugRequest_id bigint = :DrugRequest_id,
			@MedPersonal_id bigint = :MedPersonal_id,
			@pmUser_id bigint = :pmUser_id,
			@Persons varchar(max) = :Persons,
			@KolAll int,
			@Kol int,
			@Error_Code int = null,
			@Error_Message varchar(4000) = null
			
			exec r2.p_DrugRequestPersonOrderList_ins
				@DrugRequest_id = @DrugRequest_id,
				@MedPersonal_id = @MedPersonal_id,
				@pmUser_id = @pmUser_id,
				@Persons = @Persons,
				@KolAll = @KolAll output,  -- Количество пациентов для добавления
				@Kol = @Kol output,  -- Количество добавленных пациентов
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message  output;

			Select @KolAll as KolAll, @Kol as Kol, @Error_Code as Error_Code, @Error_Message as Error_Mess

			";
		
		$queryParams = array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Persons' => $data['Persons'],
			'pmUser_id' => $data['pmUser_id']
			);
		
		$response = $this->db->query($query, $queryParams);
					
		if ( is_object($response) ) {
			//var_dump($result);
			$response = $response->result('array');
			$result = $response[0];
			$result['success'] = true;
			return $result;//->result('array');
		}
		else {
			return false;
		}
			throw new Exception(!empty($result['Error_Msg']) ? $result['Error_Msg'] : 'При сохранении строки разнарядки произошла ошибка');
	}
        
 
}        