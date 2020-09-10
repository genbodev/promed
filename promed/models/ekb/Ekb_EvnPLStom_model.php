<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/EvnPLStom_model.php');

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
			declare @EvnPLStom_NumCard bigint;
			exec xp_GenpmID @ObjectName = 'EvnPLStom', @Lpu_id = :Lpu_id, @ObjectID = @EvnPLStom_NumCard output, @ObjectValue = :ObjectValue;
			select @EvnPLStom_NumCard as EvnPLStom_NumCard;
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