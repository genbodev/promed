<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Storozhev
* @version      20.07.2011
*/

require_once(APPPATH.'models/ClinExWork_model.php');

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
			select ISNULL(MAX(CAST(EvnVK_NumProtocol as bigint)), 0) as EvnVK_NumProtocol
			from v_EvnVK with (nolock)
			where ISNUMERIC(EvnVK_NumProtocol + 'e0') = 1
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
