<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/EvnPLStom_model.php');

class Ekb_EvnPLStom_model extends EvnPLStom_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLStomNumber($data) {
		$query = "
			select	EvnPLStom_NumCard as \"EvnPLStom_NumCard\"
			from	xp_GenpmID(
				ObjectName = 'EvnPLStom',
				Lpu_id = :Lpu_id,
				ObjectValue = :ObjectValue )
		";
		$result = $this->db->query($query, array(
			 'Lpu_id' => $data['Lpu_id']
			,'ObjectValue' => (!empty($data['year']) ? $data['year'] : date('Y'))
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}