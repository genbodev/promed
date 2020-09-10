<?php
class PolisUpdateClient_model extends CI_Model {
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
				ISNULL(PQ.PolisQueue_id, 0) as PolisQueue_id,
				ISNULL(PQ.OrgSmo_id, 0) as OrgSmo_id,
				ISNULL(PQ.Polis_id, 0) as Polis_id
			from
				PolisQueue PQ with (nolock)
				left join YesNo YN with(nolock) on YN.YesNo_id = PQ.PolisQueue_IsLoad
			where
				ISNULL(YN.YesNo_Code, 0) = 0
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
