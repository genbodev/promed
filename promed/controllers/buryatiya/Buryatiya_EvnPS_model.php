<?php
require_once(APPPATH.'models/EvnPS_model.php');

class Buryatiya_EvnPS_model extends EvnPS_model {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Запрос для проверки повторных госпитализаций
	 */
	function checkEvnPSDoubles($data) {
		if ( !empty($data['EvnPS_setDate']) && !empty($data['EvnPS_setTime']) ) {
			$data['EvnPS_setDate'] .= ' ' . $data['EvnPS_setTime'];
		}

		$query = "
			select
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Nick,
				convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
				convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate
			from
				v_EvnPS EPS with (NOLOCK)
				inner join v_Lpu Lpu with (NOLOCK) on Lpu.Lpu_id = EPS.Lpu_id
			where
				EPS.EvnPS_id <> ISNULL(:EvnPS_id, 0)
				and EPS.EvnPS_setDT <= CAST(:EvnPS_setDate as datetime)
				and (EPS.EvnPS_disDT is null or EPS.EvnPS_disDate > CAST(:EvnPS_setDT as datetime))
				and EPS.Person_id = :Person_id
				and EPS.PrehospWaifRefuseCause_id is null
		";

		$queryParams = array(
			'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
			'EvnPS_setDate' => $data['EvnPS_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
