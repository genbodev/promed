<?php
/**
 * Vologda_EvnDirection_model - модель для работы с направлениями (Вологда)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 */

require_once(APPPATH.'models/EvnDirection_model.php');

class Vologda_EvnDirection_model extends EvnDirection_model {
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
			declare @EvnDirection_Num bigint;
			exec xp_GenpmID @ObjectName = 'EvnDirection', @Lpu_id = :Lpu_id, @ObjectID = @EvnDirection_Num output, @ObjectValue = :ObjectValue;
			select @EvnDirection_Num as EvnDirection_Num;
		";

		if ($data['year'] >= 2018) {
			$ObjectValue = !empty($data['year']) ? $data['year'] : date('Y');
		} else {
			$ObjectValue = null;
		}

		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'ObjectValue' => $ObjectValue
		));

		if ( !is_object($result) ) {
			return false;
		}

		return $result->result('array');
	}
}
