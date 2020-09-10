<?php
/**
 * MorfoHistologicCorpseReciept_model - модель для работы со сведениями о поступлениях тел умерших
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 */

class MorfoHistologicCorpseReciept_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных для формы редактирования сведений о поступлении тел умерших
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о поступлении тел умерших
	 */
	function loadMorfoHistologicCorpseRecieptEditForm($data) {

		$query = "
			SELECT
			MorfoHistologicCorpseReciept_id as \"MorfoHistologicCorpseReciept_id\"
			,EvnDirectionMorfoHistologic_id as \"EvnDirectionMorfoHistologic_id\"
			,to_char(MorfoHistologicCorpseReciept_setDT, 'DD.MM.YYYY') as \"MorfoHistologicCorpse_recieptDate\"
			,MedPersonal_id as \"MedPersonal_id\"
			FROM
				v_MorfoHistologicCorpseReciept
			WHERE
				MorfoHistologicCorpseReciept_id = :MorfoHistologicCorpseReciept_id
			LIMIT 1
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseReciept_id' => $data['MorfoHistologicCorpseReciept_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение сведений о поступлении тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о поступлении тел умерших
	 */
	function saveMorfoHistologicCorpseReciept($data) {
		$procedure = '';

		if ( (!isset($data['MorfoHistologicCorpseReciept_id'])) || ($data['MorfoHistologicCorpseReciept_id'] <= 0) ) {
			$procedure = 'p_MorfoHistologicCorpseReciept_ins';
		}
		else {
			$procedure = 'p_MorfoHistologicCorpseReciept_upd';
		}

		if ( isset($data['MorfoHistologicCorpse_recieptDate']) ) {
			$data['MorfoHistologicCorpse_recieptDate'] .= ' 00:00:00.000';
		}
		
		$query = "
			select
				MorfoHistologicCorpseReciept_id as \"MorfoHistologicCorpseReciept_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorfoHistologicCorpseReciept_id := :MorfoHistologicCorpseReciept_id,
				EvnDirectionMorfoHistologic_id := :EvnDirectionMorfoHistologic_id,
				MorfoHistologicCorpseReciept_setDT := :MorfoHistologicCorpseReciept_setDT,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'MorfoHistologicCorpseReciept_id' =>  $data['MorfoHistologicCorpseReciept_id'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'MorfoHistologicCorpseReciept_setDT' => $data['MorfoHistologicCorpse_recieptDate'],
			'MedPersonal_id' => $data['MedPersonal_id'],
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
	function deleteMorfoHistologicCorpseReciept($data) {
		
		$query = "
		select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_MorfoHistologicCorpseReciept_del(
				MorfoHistologicCorpseReciept_id := :MorfoHistologicCorpseReciept_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseReciept_id' => $data['MorfoHistologicCorpseReciept_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Получение даты поступления тела
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function getMorfoHistologicCorpseRecieptDate($data) {

		$query = "
			SELECT
			to_char(MorfoHistologicCorpseReciept_setDT, 'DD.MM.YYYY') as \"MorfoHistologicCorpse_recieptDate\"
			FROM
				v_MorfoHistologicCorpseReciept
			WHERE (1 = 1)
			AND EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id
			LIMIT 1
		";
		$result = $this->db->query($query, array(
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
?>