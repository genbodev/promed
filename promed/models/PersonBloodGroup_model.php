<?php
class PersonBloodGroup_model extends swModel {
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonBloodGroup_del
				@PersonBloodGroup_id = :PersonBloodGroup_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				PBG.Person_id,
				0 as Children_Count,
				PBG.PersonBloodGroup_id,
				PBG.PersonBloodGroup_id as BloodData_id,
				convert(varchar(10), PBG.PersonBloodGroup_setDT, 104) as PersonBloodGroup_setDate,
				ISNULL(BGT.BloodGroupType_Name, '') as BloodGroupType_Name,
				ISNULL(RFT.RhFactorType_Name, '') as RhFactorType_Name,
				PBG.pmUser_insID
				--,ISNULL(PU.pmUser_Name, '') as pmUser_Name
			from
				v_PersonBloodGroup PBG with (nolock)
				left join v_BloodGroupType BGT with (nolock) on BGT.BloodGroupType_id = PBG.BloodGroupType_id
				left join v_RhFactorType RFT with (nolock) on RFT.RhFactorType_id = PBG.RhFactorType_id
				--left join v_pmUser PU with (nolock) on PU.pmUser_id = ISNULL(PBG.pmUser_updID, PBG.pmUser_insID)
			where
				PBG.Person_id = :Person_id
			order by
				PBG.PersonBloodGroup_setDT desc,
				PersonBloodGroup_id desc
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
			select top 1
				PBG.PersonBloodGroup_id,
				PBG.Person_id,
				PBG.Server_id,
				PBG.BloodGroupType_id,
				PBG.RhFactorType_id,
				convert(varchar(10), PBG.PersonBloodGroup_setDT, 104) as PersonBloodGroup_setDate
			from
				v_PersonBloodGroup PBG with (nolock)
			where (1 = 1)
				and PBG.PersonBloodGroup_id = :PersonBloodGroup_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonBloodGroup_id;

			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonBloodGroup_id = @Res output,
				@Person_id = :Person_id,
				@BloodGroupType_id = :BloodGroupType_id,
				@RhFactorType_id = :RhFactorType_id,
				@PersonBloodGroup_setDT = :PersonBloodGroup_setDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonBloodGroup_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				PBG.BloodGroupType_id,
				PBG.RhFactorType_id
			from v_PersonBloodGroup PBG with(nolock)
			where PBG.Person_id = :Person_id
			order by PersonBloodGroup_setDT desc
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
				pbg.Person_id,
				pbg.PersonBloodGroup_id,
				pbg.BloodGroupType_id,
				pbg.RhFactorType_id,
				convert(varchar(10), pbg.PersonBloodGroup_setDT, 120) as PersonBloodGroup_setDT
			from
				v_PersonBloodGroup pbg (nolock)
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
			$select = " ,pbg.Person_id ";
		}

		return $this->queryResult("
			select
				pbg.PersonBloodGroup_id,
				bgt.BloodGroupType_Name,
				rft.RhFactorType_Name,
				isnull(convert(varchar(10), PersonBloodGroup_setDT, 104), '') as PersonBloodGroup_setDT
				{$select}
			from
				v_PersonBloodGroup pbg (nolock)
				left join v_BloodGroupType bgt (nolock) on bgt.BloodGroupType_id = pbg.BloodGroupType_id
				left join v_RhFactorType rft (nolock) on rft.RhFactorType_id = pbg.RhFactorType_id
			where {$filter}
		", array(
			'Person_id' => $data['Person_id']
		));
	}
}
