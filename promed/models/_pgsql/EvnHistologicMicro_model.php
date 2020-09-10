<?php
class EvnHistologicMicro_model extends SwPgModel {
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
	       	 select
	            Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from p_EvnHistologicMicro_del
            (
    			EvnHistologicMicro_id := :EvnHistologicMicro_id
		    )";


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
			SELECT 
				EHM.EvnHistologicMicro_id as \"EvnHistologicMicro_id\",
				EHM.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				EHM.HistologicSpecimenPlace_id as \"HistologicSpecimenPlace_id\",
				--EHM.HistologicSpecimenSaint_id as \"HistologicSpecimenSaint_id\",
				--EHM.HistologicSpecimenSaint_did as \"HistologicSpecimenSaint_did\",
				EHM.EvnHistologicMicro_Count as \"EvnHistologicMicro_Count\",
				EHM.EvnHistologicMicro_Descr as \"EvnHistologicMicro_Descr\"
			FROM
				v_EvnHistologicMicro EHM 
			WHERE (1 = 1)
				and EHM.EvnHistologicMicro_id = :EvnHistologicMicro_id
            LIMIT 1
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
				EHM.EvnHistologicMicro_id as \"EvnHistologicMicro_id\",
				EHM.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				EHM.HistologicSpecimenPlace_id as \"HistologicSpecimenPlace_id\",
				EHM.PrescrReactionType_id as \"PrescrReactionType_id\",
				EHM.PrescrReactionType_did as \"PrescrReactionType_did\",
				EHM.EvnHistologicMicro_Count as \"EvnHistologicMicro_Count\",
				EHM.EvnHistologicMicro_Descr as \"EvnHistologicMicro_Descr\",
				RTRIM(COALESCE(HSP.HistologicSpecimenPlace_Name, '')) as \"HistologicSpecimenPlace_Name\"
			from v_EvnHistologicMicro EHM
				inner join HistologicSpecimenPlace HSP  on HSP.HistologicSpecimenPlace_id = EHM.HistologicSpecimenPlace_id
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
	       	 select
	            Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\",
		        EvnHistologicMicro_id as \"EvnHistologicMicro_id\"
		from {$procedure}
            (
			    EvnHistologicMicro_id := :EvnHistologicMicro_id,
				EvnHistologicProto_id := :EvnHistologicProto_id,
				HistologicSpecimenPlace_id := :HistologicSpecimenPlace_id,
				PrescrReactionType_id := :PrescrReactionType_id,
				PrescrReactionType_did := :PrescrReactionType_did,
				EvnHistologicMicro_Count := :EvnHistologicMicro_Count,
				EvnHistologicMicro_Descr := :EvnHistologicMicro_Descr,
				pmUser_id := :pmUser_id
		    )";


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