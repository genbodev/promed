<?php
class PersonFeedingType_model extends swModel {
	function __construct() {
		parent::__construct();
	}

	function deletePersonFeedingType($data) {
		$procedure = 'p_FeedingTypeAge_del';
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
				
				

			exec " . $procedure . "
				@FeedingTypeAge_id = :FeedingTypeAge_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'FeedingTypeAge_id' => $data['FeedingTypeAge_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	function loadPersonFeedingTypeEditForm($data) {
		$query = "
		 select top 1
				pch.Person_id,
				fta.FeedingTypeAge_id,
				fta.PersonChild_id,
				fta.FeedingTypeAge_Age,
				fta.FeedingType_id
			from v_FeedingTypeAge fta with (nolock)
			left join v_PersonChild pch(nolock) on fta.PersonChild_id = pch.PersonChild_id

			where (1 = 1)
				and fta.FeedingTypeAge_id = :FeedingTypeAge_id
		";
		$result = $this->db->query($query, array(
			'FeedingTypeAge_id' => $data['FeedingTypeAge_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	function loadPersonFeedingType($data){
		if (!$data['PersonChild_id']) {
			$query = "
			select PersonChild_id
			from v_PersonChild where Person_id = :Person_id
		";
			$queryParams = array(
				'Person_id' => $data['Person_id']
			);

			$result = $this->db->query($query, $queryParams)->result('array');

			if(!Empty($result)) {
				$data['PersonChild_id'] = $result[0]['PersonChild_id'];
			}
		}
		return $this->queryResult("
			select 
			fta.PersonChild_id,
			fta.FeedingTypeAge_id,
			fta.FeedingTypeAge_Age,
			fta.FeedingType_id,
			fta.pmUser_insID,
			ft.FeedingType_Name
			from v_FeedingTypeAge fta with(nolock)
			left join v_FeedingType ft (nolock) on ft.FeedingType_id = fta.FeedingType_id
			where fta.PersonChild_id = :PersonChild_id
    	", array(
			'PersonChild_id' => $data['PersonChild_id']
		));
	}

	function savePersonFeedingType($data) {
		$procedure = '';
		if (!isset($data['PersonChild_id'])) {
			$procedure = 'p_PersonChild_ins';
			$query = "

			exec " . $procedure . "
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id

			select PersonChild_id
			from v_PersonChild where Person_id = :Person_id
		";

			$queryParams = array(
				'Person_id' => $data['Person_id'],
				'Server_id' => (!empty($data['Server_id']) ? $data['Server_id']: 0),
				'pmUser_id' => $data['pmUser_id'],
			);

			$result = $this->db->query($query, $queryParams)->result('array');
			$data['PersonChild_id'] = $result[0]['PersonChild_id'];

		}
		if ( (!isset($data['FeedingTypeAge_id'])) || ($data['FeedingTypeAge_id'] <= 0) ) {
			$procedure = 'p_FeedingTypeAge_ins';
		}
		else {
			$procedure = 'p_FeedingTypeAge_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :FeedingTypeAge_id;

			exec " . $procedure . "
				@FeedingTypeAge_id = @Res output,
				@PersonChild_id = :PersonChild_id,
				@FeedingTypeAge_Age = :FeedingTypeAge_Age,
				@FeedingType_id = :FeedingType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as FeedingTypeAge_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'FeedingTypeAge_id' => (!empty($data['FeedingTypeAge_id']) ? $data['FeedingTypeAge_id']: NULL),
			'PersonChild_id' => $data['PersonChild_id'],
			'FeedingTypeAge_Age' => $data['FeedingTypeAge_Age'],
			'FeedingType_id' => $data['FeedingType_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}