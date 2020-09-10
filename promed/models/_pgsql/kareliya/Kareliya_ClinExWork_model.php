<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Valery Bondarev
 * @version      01.2020
 */

require_once(APPPATH.'models/_pgsql/ClinExWork_model.php');

class Kareliya_ClinExWork_model extends ClinExWork_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение номера протокола ВК
	 */
	function getNewEvnVKNumber($data = array()) {
		if ( empty($data['Lpu_id']) ) {
			return false;
		}

		$data['DateX'] = '2016-04-27 12:00';

		$query = "
			select COALESCE(MAX(CAST(EvnVK_NumProtocol as bigint)), 0) as \"EvnVK_NumProtocol\"
			from v_EvnVK
			where ISNUMERIC(EvnVK_NumProtocol) = 1
				and PATINDEX('%.%', EvnVK_NumProtocol) = 0
				and Lpu_id = :Lpu_id
				and EvnVK_insDT >= cast(:DateX as datetime)
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка уникальности номера протокола ВК
	 */
	// Сделана общая проверка по задаче https://redmine.swan.perm.ru/issues/81019
	/*function checkEvnVKNumProtocol($data) {
		$response = array(array('success' => true, 'Error_Msg' => '', 'Alert_Msg' => ''));
		return $response;
	}*/
}
