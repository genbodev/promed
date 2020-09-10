<?php
class EvnUslugaOperAnest_model extends SwPgModel {

	public $inputRules = array(
		'loadEvnUslugaOperAnestList' => array(
			array(
				'field' => 'EvnUslugaOper_id', 
				'label' => 'Идентификатор оказания оперативной услуги', 
				'rules' => 'required', 
				'type' => 'id'
			)
		),
		'loadEvnUslugaOperAnest' => array(
			array(
				'field' => 'EvnUslugaOper_id', 
				'label' => 'Идентификатор оказания оперативной услуги', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaOperAnest_id', 
				'label' => 'Идентификатор использования анестезии', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'AnesthesiaClass_id', 
				'label' => 'Тип анестезии', 
				'rules' => '', 
				'type' => 'id'
			)
		),
		'createEvnUslugaOperAnest' => array(
			array(
				'field' => 'EvnUslugaOper_id', 
				'label' => 'Идентификатор оказания оперативной услуги', 
				'rules' => 'required', 
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'AnesthesiaClass_id', 
				'label' => 'Тип анестезии', 
				'rules' => 'required', 
				'type' => 'id'
			)
		),
		'updateEvnUslugaOperAnest' => array(
			array(
				'field' => 'EvnUslugaOper_id', 
				'label' => 'Идентификатор оказания оперативной услуги', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaOperAnest_id', 
				'label' => 'Идентификатор использования анестезии', 
				'rules' => 'required', 
				'type' => 'id'
			),
			array(
				'field' => 'AnesthesiaClass_id', 
				'label' => 'Тип анестезии', 
				'rules' => '', 
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление
	 */
	function deleteEvnUslugaOperAnest($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnUslugaOperAnest_del(
				EvnUslugaOperAnest_id := :EvnUslugaOperAnest_id
			)
		";
		$result = $this->db->query($query, array('EvnUslugaOperAnest_id' => $data['EvnUslugaOperAnest_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление применяемого вида анестезии)'));
		}
	}

	/**
	 * Получение данных для грида
	 */
	function loadEvnUslugaOperAnestGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			select
				case when EUO.Lpu_id = :Lpu_id " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EUO.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as \"accessType\",
				EUOA.EvnUslugaOperAnest_id as \"EvnUslugaOperAnest_id\",
				EUOA.EvnUslugaOper_id as \"EvnUslugaOperAnest_pid\",
				AC.AnesthesiaClass_id as \"AnesthesiaClass_id\",
				AC.AnesthesiaClass_Code as \"AnesthesiaClass_Code\",
				RTRIM(AC.AnesthesiaClass_Name) as \"AnesthesiaClass_Name\"
			from v_EvnUslugaOperAnest EUOA
				inner join v_EvnUslugaOper EUO on EUO.EvnUslugaOper_id = EUOA.EvnUslugaOper_id
				left join AnesthesiaClass AC on AC.AnesthesiaClass_id = EUOA.AnesthesiaClass_id
			where EUOA.EvnUslugaOper_id = :EvnUslugaOperAnest_pid
				and (EUO.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		$result = $this->db->query($query, array(
			'EvnUslugaOperAnest_pid' => $data['EvnUslugaOperAnest_pid'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function saveEvnUslugaOperAnest($data) {
		$procedure = '';

		if ( (!isset($data['EvnUslugaOperAnest_id'])) || ($data['EvnUslugaOperAnest_id'] <= 0) ) {
			$procedure = 'p_EvnUslugaOperAnest_ins';
		}
		else {
			$procedure = 'p_EvnUslugaOperAnest_upd';
		}

		$query = "
			select
				EvnUslugaOperAnest_id as \"EvnUslugaOperAnest_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				EvnUslugaOperAnest_id := :EvnUslugaOperAnest_id,
				EvnUslugaOper_id := :EvnUslugaOperAnest_pid,
				AnesthesiaClass_id := :AnesthesiaClass_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnUslugaOperAnest_id' => (!empty($data['EvnUslugaOperAnest_id']) ? $data['EvnUslugaOperAnest_id'] : null),
			'EvnUslugaOperAnest_pid' => $data['EvnUslugaOperAnest_pid'],
			'AnesthesiaClass_id' => $data['AnesthesiaClass_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Получение списка анестезии по услуге
	 */
	function loadEvnUslugaOperAnest($data) {
		$where = "EUO.Lpu_id = :Lpu_id";

		if(!empty($data['EvnUslugaOper_id'])){
			$where .= " and EUOA.EvnUslugaOper_id = :EvnUslugaOper_id";
		}
		if(!empty($data['EvnUslugaOperAnest_id'])){
			$where .= " and EUOA.EvnUslugaOperAnest_id = :EvnUslugaOperAnest_id";
		}
		if(!empty($data['AnesthesiaClass_id'])){
			$where .= " and EUOA.AnesthesiaClass_id = :AnesthesiaClass_id";
		}
		
		$query = "
			select
				EUOA.EvnUslugaOperAnest_id as \"EvnUslugaOperAnest_id\",
				EUOA.EvnUslugaOper_id as \"EvnUslugaOper_id\",
				EUOA.AnesthesiaClass_id as \"AnesthesiaClass_id\"
			from v_EvnUslugaOperAnest EUOA
				inner join v_EvnUslugaOper EUO on EUO.EvnUslugaOper_id = EUOA.EvnUslugaOper_id
			where 
				{$where}
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}