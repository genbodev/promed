<?php
class EvnHistologicMicro_model extends CI_Model {
	/**
	 * Comment
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 * Comment
	 */
	function deleteEvnHistologicMicro($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnHistologicMicro_del
				@EvnHistologicMicro_id = :EvnHistologicMicro_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnHistologicMicro_id' => $data['EvnHistologicMicro_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление микроскопического описания)'));
		}
	}


	/**
	 * Comment
	 */
	function loadEvnHistologicMicroEditForm($data) {
		$query = "
			SELECT TOP 1
				EHM.EvnHistologicMicro_id,
				EHM.EvnHistologicProto_id,
				EHM.HistologicSpecimenPlace_id,
				EHM.HistologicSpecimenSaint_id,
				EHM.HistologicSpecimenSaint_did,
				EHM.EvnHistologicMicro_Count,
				EHM.EvnHistologicMicro_Descr
			FROM
				v_EvnHistologicMicro EHM with (nolock)
			WHERE (1 = 1)
				and EHM.EvnHistologicMicro_id = :EvnHistologicMicro_id
		";
		$result = $this->db->query($query, array(
			'EvnHistologicMicro_id' => $data['EvnHistologicMicro_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Comment
	 */
	function loadEvnHistologicMicroGrid($data) {
		$query = "
			select
				EHM.EvnHistologicMicro_id,
				EHM.EvnHistologicProto_id,
				EHM.HistologicSpecimenPlace_id,
				EHM.PrescrReactionType_id,
				EHM.PrescrReactionType_did,
				EHM.EvnHistologicMicro_Count,
				EHM.EvnHistologicMicro_Descr,
				RTRIM(ISNULL(HSP.HistologicSpecimenPlace_Name, '')) as HistologicSpecimenPlace_Name
			from v_EvnHistologicMicro EHM with (nolock)
				inner join HistologicSpecimenPlace HSP with (nolock) on HSP.HistologicSpecimenPlace_id = EHM.HistologicSpecimenPlace_id
			where EHM.EvnHistologicProto_id = :EvnHistologicProto_id
		";
		$result = $this->db->query($query, array('EvnHistologicProto_id' => $data['EvnHistologicProto_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Comment
	 */
	function saveEvnHistologicMicro($data) {
		$procedure = '';

		if ( (!isset($data['EvnHistologicMicro_id'])) || ($data['EvnHistologicMicro_id'] <= 0) ) {
			$procedure = 'p_EvnHistologicMicro_ins';
		}
		else {
			$procedure = 'p_EvnHistologicMicro_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnHistologicMicro_id;
			exec " . $procedure . "
				@EvnHistologicMicro_id = @Res output,
				@EvnHistologicProto_id = :EvnHistologicProto_id,
				@HistologicSpecimenPlace_id = :HistologicSpecimenPlace_id,
				@PrescrReactionType_id = :PrescrReactionType_id,
				@PrescrReactionType_did = :PrescrReactionType_did,
				@EvnHistologicMicro_Count = :EvnHistologicMicro_Count,
				@EvnHistologicMicro_Descr = :EvnHistologicMicro_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnHistologicMicro_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnHistologicMicro_id' => $data['EvnHistologicMicro_id'],
			'EvnHistologicProto_id' => $data['EvnHistologicProto_id'],
			'HistologicSpecimenPlace_id' => $data['HistologicSpecimenPlace_id'],
			'PrescrReactionType_id' => $data['PrescrReactionType_id'],
			'PrescrReactionType_did' => $data['PrescrReactionType_did'],
			'EvnHistologicMicro_Count' => $data['EvnHistologicMicro_Count'],
			'EvnHistologicMicro_Descr' => $data['EvnHistologicMicro_Descr'],
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
}
?>