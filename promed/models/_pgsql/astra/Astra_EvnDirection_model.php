<?php
/**
* Astra_EvnDirection_model - модель для работы с направлениями (Астрахань)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
*/

require_once(APPPATH.'models/_pgsql/EvnDirection_model.php');

class Astra_EvnDirection_model extends EvnDirection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @task https://redmine.swan.perm.ru/issues/43218
	 * @param $data
	 * @return mixed
	 */
	public function getEvnDirectionNumber($data, $tryCount = 0) {
		$query = "
			select
				ObjectID as \"EvnDirection_Num\"
			from xp_GenpmID(
				ObjectName := 'EvnDirection',
				Lpu_id := :Lpu_id,
				ObjectValue := :ObjectValue
			)
		";

		$ObjectValue = !empty($data['year']) ? $data['year'] : date('Y');
		$ObjectValue .= !empty($data['EvnDirection_IsAuto']) && $data['EvnDirection_IsAuto'] == 2 ? 2 : 1;

		$result = $this->db->query($query, array(
			 'Lpu_id' => $data['Lpu_id']
			,'ObjectValue' => $ObjectValue
		));

		if ( !is_object($result) ) {
			return false;
		}

		return $result->result('array');
	}
}
