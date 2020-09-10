<?php
/**
 * MorfoHistologicCorpseGiveaway_model - модель для работы со сведениями о выдаче тел умерших
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 */

class MorfoHistologicCorpseGiveaway_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных для формы редактирования сведений о выдаче тел умерших
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о выдаче тел умерших
	 */
	function loadMorfoHistologicCorpseGiveawayEditForm($data) {

		$query = "
			SELECT 
			MorfoHistologicCorpseGiveaway_id as \"MorfoHistologicCorpseGiveaway_id\"
			,EvnDirectionMorfoHistologic_id as \"EvnDirectionMorfoHistologic_id\" 
			,to_char(MorfoHistologicCorpseGiveaway_setDT, 'DD.MM.YYYY') as \"MorfoHistologicCorpse_giveawayDate\"
			,MedPersonal_id as \"MedPersonal_id\"
			,Person_id as \"Person_id\"
			FROM
				v_MorfoHistologicCorpseGiveaway
			WHERE 
				MorfoHistologicCorpseGiveaway_id = :MorfoHistologicCorpseGiveaway_id
			LIMIT 1
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseGiveaway_id' => $data['MorfoHistologicCorpseGiveaway_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение сведений о выдаче тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о выдаче тел умерших
	 */
	function saveMorfoHistologicCorpseGiveaway($data) {
		$procedure = '';

		if ( (!isset($data['MorfoHistologicCorpseGiveaway_id'])) || ($data['MorfoHistologicCorpseGiveaway_id'] <= 0) ) {
			$procedure = 'p_MorfoHistologicCorpseGiveaway_ins';
		}
		else {
			$procedure = 'p_MorfoHistologicCorpseGiveaway_upd';
		}

		if ( isset($data['MorfoHistologicCorpse_GiveawayDate']) ) {
			$data['MorfoHistologicCorpseGiveaway_GiveawayDate'] .= ' 00:00:00.000';
		}

		$query = "
			select
				MorfoHistologicCorpseGiveaway_id as \"MorfoHistologicCorpseGiveaway_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorfoHistologicCorpseGiveaway_id := :MorfoHistologicCorpseGiveaway_id,
 				EvnDirectionMorfoHistologic_id := :EvnDirectionMorfoHistologic_id,
 				MorfoHistologicCorpseGiveaway_setDT := :MorfoHistologicCorpseGiveaway_setDT,
 				MedPersonal_id := :MedPersonal_id,
 				Person_id := :Person_id,
 				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'MorfoHistologicCorpseGiveaway_id' =>  $data['MorfoHistologicCorpseGiveaway_id'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'MorfoHistologicCorpseGiveaway_setDT' => $data['MorfoHistologicCorpse_giveawayDate'],
			'MedPersonal_id' => $data['MedPersonal_id'],
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
	 * Удаление сведений о поступлении тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array
	 * Используется: журнал рабочего места патологоанатома
	 */
	function deleteMorfoHistologicCorpseGiveaway($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_MorfoHistologicCorpseGiveaway_del(
				MorfoHistologicCorpseGiveaway_id := :MorfoHistologicCorpseGiveaway_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseGiveaway_id' => $data['MorfoHistologicCorpseGiveaway_id'],
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