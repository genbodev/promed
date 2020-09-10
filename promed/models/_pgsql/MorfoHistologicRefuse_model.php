<?php
/**
 * MorfoHistologicRefuse_model - модель для работы с отказами от вскрытия тел умерших (АРМ Патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 */

class MorfoHistologicRefuse_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных списка типов отказа от вскрытия
	 * @return array | bool
	 * Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function getMorfoHistologicRefuseTypeList() {

		$query = "
			SELECT 
			MorfoHistologicRefuseType_id as \"MorfoHistologicRefuseType_id\"
			,MorfoHistologicRefuseType_code as \"MorfoHistologicRefuseType_code\"
			,MorfoHistologicRefuseType_name as \"MorfoHistologicRefuseType_name\"
			FROM
				v_MorfoHistologicRefuseType
		";
		$result = $this->db->query($query, array());

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования сведений об отказе от вскрытия тел умерших
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function loadMorfoHistologicRefuseEditForm($data) {

		$query = "
			SELECT 
			MorfoHistologicRefuse_id as \"MorfoHistologicRefuse_id\"
			,EvnDirectionMorfoHistologic_id as \"EvnDirectionMorfoHistologic_id\"
			,to_char(MorfoHistologicRefuse_setDT, 'DD.MM.YYYY') as \"MorfoHistologic_refuseDate\"
			,MorfoHistologicRefuseType_id as \"RefuseType_id\"
			,Person_id as \"Person_id\"
			FROM
				v_MorfoHistologicRefuse
			WHERE 
				MorfoHistologicRefuse_id = :MorfoHistologicRefuse_id
			LIMIT 1
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicRefuse_id' => $data['MorfoHistologicRefuse_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение сведений об отказе от вскрытия тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function saveMorfoHistologicRefuse($data) {
		$procedure = '';

		if ( (!isset($data['MorfoHistologicRefuse_id'])) || ($data['MorfoHistologicRefuse_id'] <= 0) ) {
			$procedure = 'p_MorfoHistologicRefuse_ins';
		}
		else {
			$procedure = 'p_MorfoHistologicRefuse_upd';
		}

		if ( isset($data['MorfoHistologic_refuseDate']) ) {
			$data['MorfoHistologic_refuseDate'] .= ' 00:00:00.000';
		}
		
		$query = "
			select
				MorfoHistologicRefuse_id as \"MorfoHistologicRefuse_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorfoHistologicRefuse_id := :MorfoHistologicRefuse_id,
				EvnDirectionMorfoHistologic_id := :EvnDirectionMorfoHistologic_id,				
 				MorfoHistologicRefuse_setDT := :MorfoHistologicRefuse_setDT,
 				MorfoHistologicRefuseType_id := :MorfoHistologicRefuseType_id,
 				Person_id := :Person_id,
 				pmUser_id := :pmUser_id
			)
		";		

		$queryParams = array(
			'MorfoHistologicRefuse_id' =>  $data['MorfoHistologicRefuse_id'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'MorfoHistologicRefuse_setDT' => $data['MorfoHistologic_refuseDate'],
			'MorfoHistologicRefuseType_id' => $data['RefuseType_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			return false;
		}

		return $result->result("array");
	}

	/**
	 * Удаление  сведений об отказе от вскрытия тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array
	 * Используется: журнал рабочего места патологоанатома
	 */
	function deleteMorfoHistologicRefuse($data) {
		$query = "
		select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_MorfoHistologicRefuse_del(
				MorfoHistologicRefuse_id := :MorfoHistologicRefuse_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, array(
			'MorfoHistologicRefuse_id' => $data['MorfoHistologicRefuse_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}
}
?>