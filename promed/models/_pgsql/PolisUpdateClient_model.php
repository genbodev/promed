<?php
class PolisUpdateClient_model extends swPgModel {
	/**
	 * PolisUpdateClient_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param array $orgSmoList
	 * @return bool
	 */
	function getOrgSmoData($orgSmoList = array()) {
		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getPolisQueueList() {
		$query = "
			select
				COALESCE(PQ.PolisQueue_id, 0) as \"PolisQueue_id\",
				COALESCE(PQ.OrgSmo_id, 0) as \"OrgSmo_id\",
				COALESCE(PQ.Polis_id, 0) as \"Polis_id\"
			from
				PolisQueue PQ 
				left join YesNo YN  on YN.YesNo_id = PQ.PolisQueue_IsLoad
			where
				COALESCE(YN.YesNo_Code, 0) = 0
		";
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
