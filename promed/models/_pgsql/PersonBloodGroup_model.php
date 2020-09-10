<?php
class PersonBloodGroup_model extends swPgModel {
	/**
	 * PersonBloodGroup_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function deletePersonBloodGroup($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonBloodGroup_del(
				PersonBloodGroup_id := :PersonBloodGroup_id
			)
		";
		$result = $this->db->query($query, array(
			'PersonBloodGroup_id' => $data['PersonBloodGroup_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных о группе крови и резус-факторе человека
	 */
	function getPersonBloodGroupViewData($data) {
		$query = "
			select
				PBG.Person_id as \"Person_id\",
				0 as \"Children_Count\",
				PBG.PersonBloodGroup_id as \"PersonBloodGroup_id\",
				PBG.PersonBloodGroup_id as \"BloodData_id\",
				to_char(PBG.PersonBloodGroup_setDT, 'dd.mm.yyyy') as \"PersonBloodGroup_setDate\",
				coalesce(BGT.BloodGroupType_Name, '') as \"BloodGroupType_Name\",
				coalesce(RFT.RhFactorType_Name, '') as \"RhFactorType_Name\",
				PBG.pmUser_insID as \"pmUser_insID\"
			from
				v_PersonBloodGroup PBG
				left join v_BloodGroupType BGT on BGT.BloodGroupType_id = PBG.BloodGroupType_id
				left join v_RhFactorType RFT on RFT.RhFactorType_id = PBG.RhFactorType_id
			where
				PBG.Person_id = :Person_id
			order by
				PBG.PersonBloodGroup_setDT desc,
				PersonBloodGroup_id desc
			limit 1
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadPersonBloodGroupEditForm($data) {
		$query = "
			select
				PBG.PersonBloodGroup_id as \"PersonBloodGroup_id\",
				PBG.Person_id as \"Person_id\",
				PBG.Server_id as \"Server_id\",
				PBG.BloodGroupType_id as \"BloodGroupType_id\",
				PBG.RhFactorType_id as \"RhFactorType_id\",
				to_char(PBG.PersonBloodGroup_setDT, 'dd.mm.yyyy') as \"PersonBloodGroup_setDate\"
			from
				v_PersonBloodGroup PBG
			where (1 = 1)
				and PBG.PersonBloodGroup_id = :PersonBloodGroup_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'PersonBloodGroup_id' => $data['PersonBloodGroup_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function savePersonBloodGroup($data) {
		$procedure = '';

		if ( (!isset($data['PersonBloodGroup_id'])) || ($data['PersonBloodGroup_id'] <= 0) ) {
			$procedure = 'p_PersonBloodGroup_ins';
		}
		else {
			$procedure = 'p_PersonBloodGroup_upd';
		}

		$query = "
			select
				PersonBloodGroup_id as \"PersonBloodGroup_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				Server_id := :Server_id,
				PersonBloodGroup_id := :PersonBloodGroup_id,
				Person_id := :Person_id,
				BloodGroupType_id := :BloodGroupType_id,
				RhFactorType_id := :RhFactorType_id,
				PersonBloodGroup_setDT := :PersonBloodGroup_setDate,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'PersonBloodGroup_id' => $data['PersonBloodGroup_id'],
			'Person_id' => $data['Person_id'],
			'BloodGroupType_id' => $data['BloodGroupType_id'],
			'RhFactorType_id' => $data['RhFactorType_id'],
			'PersonBloodGroup_setDate' => $data['PersonBloodGroup_setDate'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение показателей крови человека
	 */
	function getPersonBloodGroup($data) {
		$params = array('Person_id' => $data['Person_id']);
		$response = array(
			'success' => true,
			'BloodGroupType_id' => null,
			'RhFactorType_id' => null
		);
		$query = "
			select
				PBG.BloodGroupType_id as \"BloodGroupType_id\",
				PBG.RhFactorType_id as \"RhFactorType_id\"
			from v_PersonBloodGroup PBG
			where PBG.Person_id = :Person_id
			order by PersonBloodGroup_setDT desc
			limit 1
		";
		$PersonBloodGroup = $this->getFirstRowFromQuery($query, $params, true);
		if ($PersonBloodGroup === false) {
			return $this->createError('','Ошибка при получении данных о группе крови человека');
		}
		if (empty($PersonBloodGroup)) {
			return array($response);
		}
		return array(array_merge($response, $PersonBloodGroup));
	}

	/**
	 * Получение показателей крови человека. Метод для API.
	 */
	function getPersonBloodGroupForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['Person_id'])) {
			$filter .= " and pbg.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				pbg.Person_id as \"Person_id\",
				pbg.PersonBloodGroup_id as \"PersonBloodGroup_id\",
				pbg.BloodGroupType_id as \"BloodGroupType_id\",
				pbg.RhFactorType_id as \"RhFactorType_id\",
				to_char(pbg.PersonBloodGroup_setDT, 'yyyy-mm-dd') as \"PersonBloodGroup_setDT\"
			from
				v_PersonBloodGroup pbg
			where
				1=1
				{$filter}
		", $queryParams);
	}

	/**
	 * Получение списка групп крови пациента для ЭМК
	 */
	function loadPersonBloodGroupPanel($data) {

		$filter = " pbg.Person_id = :Person_id "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " pbg.Person_id in ({$data['person_in']}) ";
			$select = " ,pbg.Person_id as \"Person_id\"";
		}

		return $this->queryResult("
			select
				pbg.PersonBloodGroup_id as \"PersonBloodGroup_id\",
				bgt.BloodGroupType_Name as \"BloodGroupType_Name\",
				rft.RhFactorType_Name as \"RhFactorType_Name\",
				coalesce(to_char(PersonBloodGroup_setDT, 'dd.mm.yyyy'), '') as \"PersonBloodGroup_setDT\"
				{$select}
			from
				v_PersonBloodGroup pbg
				left join v_BloodGroupType bgt on bgt.BloodGroupType_id = pbg.BloodGroupType_id
				left join v_RhFactorType rft on rft.RhFactorType_id = pbg.RhFactorType_id
			where {$filter}
		", array(
			'Person_id' => $data['Person_id']
		));
	}
}
