<?php

class EvnDtp_model extends SwPgModel {

	/**
	 * Comment
	 */
    function __construct() {
        parent::__construct();
    }

	/**
	 * Comment
	 */
    function getEvnDtpWoundFields($data) {
        $query = "
			select
				RTRIM(COALESCE(Lpu.Lpu_Name, '')) as \"Lpu_Name\",
                RTRIM(COALESCE(uaddr.Address_Address, '')) as \"Lpu_UAddress\",
                RTRIM(COALESCE(Org.Org_Phone, '')) as \"Lpu_Phone\",
				RTRIM(RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, ''))) as \"Person_Fio\",
				RTRIM(COALESCE(Sex.Sex_Name, '')) as \"Sex_Name\",
				to_char (PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
   				COALESCE(to_char (EvnDtpWound_ObrDate, 'dd.mm.yyyy'), '') as \"EvnDtpWound_ObrDate\",
   				COALESCE(to_char (EvnDtpWound_HospDate, 'dd.mm.yyyy'), '') as \"EvnDtpWound_HospDate\",
   				COALESCE(to_char (EvnDtpWound_DtpDate, 'dd.mm.yyyy'), '') as \"EvnDtpWound_DtpDate\",
   				COALESCE(to_char (EvnDtpWound_setDate, 'dd.mm.yyyy'), '') as \"EvnDtpWound_setDate\",
   				RTRIM(COALESCE(DiagP.Diag_Code, '')) as \"DiagP_Code\",
				RTRIM(COALESCE(DiagP.Diag_Name, '')) as \"DiagP_Name\",
                RTRIM(COALESCE(DiagE.Diag_Code, '')) as \"DiagE_Code\",
				RTRIM(COALESCE(DiagE.Diag_Name, '')) as \"DiagE_Name\",
   				COALESCE(to_char (EvnDtpWound_OtherLpuDate, 'dd.mm.yyyy'), '') as \"EvnDtpWound_OtherLpuDate\",
                RTRIM(COALESCE(OtherLpu.Lpu_Name, '')) as \"OtherLpu_Name\",
                RTRIM(COALESCE(DiagO.Diag_Code, '')) as \"DiagO_Code\",
				RTRIM(COALESCE(DiagO.Diag_Name, '')) as \"DiagO_Name\",
                MP.Dolgnost_Name as \"MedPersonal_Dolgnost\",
                MP.Person_Fin as \"MedPersonal_Fin\"
            from v_EvnDtpWound EDW
				inner join v_Lpu Lpu on Lpu.Lpu_id = EDW.Lpu_id
				inner join v_Person_all PS on PS.Server_id = EDW.Server_id
					and PS.PersonEvn_id = EDW.PersonEvn_id
                left join Address as uaddr on uaddr.Address_id = Lpu.UAddress_id
                left join v_Org Org on Org.Org_id = Lpu.Org_id
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join Diag DiagP on DiagP.Diag_id = EDW.Diag_pid
				left join Diag DiagE on DiagE.Diag_id = EDW.Diag_eid
				left join Diag DiagO on DiagO.Diag_id = EDW.Diag_oid
				left join v_Lpu as OtherLpu on OtherLpu.Lpu_id = EDW.Lpu_oid
                left join v_MedPersonal MP on MP.MedPersonal_id = EDW.MedPersonal_id
			where
				EDW.EvnDtpWound_id = :EvnDtpWound_id
				and EDW.Lpu_id = :Lpu_id
		";
        $result = $this->db->query($query, array(
                    'EvnDtpWound_id' => $data['EvnDtpWound_id'],
                    'Lpu_id' => $data['Lpu_id']
                ));

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * Comment
	 */
    function deleteEvnDtpWound($data) {
        $query = "
            select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from p_EvnDtpWound_del (
				EvnDtpWound_id := :EvnDtpWound_id,
				pmUser_id := :pmUser_id
				)
		";
        $result = $this->db->query($query, array('EvnDtpWound_id' => $data['EvnDtpWound_id'],
                    'pmUser_id' => $data['pmUser_id']));

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление извещения о раненом в ДТП)'));
        }
    }

	/**
	 * Comment
	 */
    function loadEvnDtpWoundEditForm($data) {
        $query = "
			SELECT
				EDW.EvnDtpWound_id as \"EvnDtpWound_id\",
				to_char (EDW.EvnDtpWound_ObrDate,'dd.mm.yyyy') as \"EvnDtpWound_ObrDate\",
				to_char (EDW.EvnDtpWound_HospDate,'dd.mm.yyyy') as \"EvnDtpWound_HospDate\",
				to_char (EDW.EvnDtpWound_DtpDate,'dd.mm.yyyy') as \"EvnDtpWound_DtpDate\",
				EDW.Diag_pid as \"Diag_pid\",
				EDW.Diag_eid as \"Diag_eid\",
				to_char (EDW.EvnDtpWound_OtherLpuDate,'dd.mm.yyyy') as \"EvnDtpWound_OtherLpuDate\",
				EDW.Lpu_oid as \"Lpu_oid\",
				EDW.Diag_oid as \"Diag_oid\",
				EDW.MedPersonal_id as \"MedPersonal_id\",
				to_char (EDW.EvnDtpWound_setDT, 'dd.mm.yyyy') as \"EvnDtpWound_setDate\",
				EDW.Person_id as \"Person_id\",
				EDW.PersonEvn_id as \"PersonEvn_id\",
				EDW.Server_id as \"Server_id\"
			FROM
				v_EvnDtpWound EDW
			WHERE (1 = 1)
				and EDW.EvnDtpWound_id = :EvnDtpWound_id
				and EDW.Lpu_id = :Lpu_id
            LIMIT 1
		";
        $result = $this->db->query($query, array('EvnDtpWound_id' => $data['EvnDtpWound_id'],
                    'Lpu_id' => $data['Lpu_id']));

        if (is_object($result)) {
            $result = $result->result('array');
            return $result[0]; // Возвращаем одну найденную запись
        } else {
            return false;
        }
    }

	/**
	 * Comment
	 */
    function saveEvnDtpWound($data) {
        $procedure = '';

        if (isset($data['EvnDtpWound_id'])) {
            $procedure = 'p_EvnDtpWound_upd';
        } else {
            $procedure = 'p_EvnDtpWound_ins';
        }

        $query = "
			select 
			    EvnDtpWound_id as \"EvnDtpWound_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from " . $procedure . " (
				EvnDtpWound_id := :EvnDtpWound_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnDtpWound_setDT := :EvnDtpWound_setDT,
				MedPersonal_id := :MedPersonal_id, 
				EvnDtpWound_ObrDate := :EvnDtpWound_ObrDate,
				EvnDtpWound_HospDate := :EvnDtpWound_HospDate,
				EvnDtpWound_DtpDate := :EvnDtpWound_DtpDate,
				EvnDtpWound_OtherLpuDate := :EvnDtpWound_OtherLpuDate,
				Lpu_oid := :Lpu_oid,
				Diag_pid := :Diag_pid,
				Diag_eid := :Diag_eid,
				Diag_oid := :Diag_oid,
				pmUser_id := :pmUser_id
				)
		";

        $queryParams = array('EvnDtpWound_id' => $data['EvnDtpWound_id'],
            'Lpu_id' => $data['Lpu_id'],
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
            'EvnDtpWound_setDT' => $data['EvnDtpWound_setDate'],
            'MedPersonal_id' => $data['MedPersonal_id'],
            'EvnDtpWound_ObrDate' => $data['EvnDtpWound_ObrDate'],
            'EvnDtpWound_HospDate' => $data['EvnDtpWound_HospDate'],
            'EvnDtpWound_DtpDate' => $data['EvnDtpWound_DtpDate'],
            'EvnDtpWound_OtherLpuDate' => $data['EvnDtpWound_OtherLpuDate'],
            'Lpu_oid' => $data['Lpu_oid'],
            'Diag_pid' => $data['Diag_pid'],
            'Diag_eid' => $data['Diag_eid'],
            'Diag_oid' => $data['Diag_oid'],
            'pmUser_id' => $data['pmUser_id']);

        $result = $this->db->query($query, $queryParams);

        if (!is_object($result)) {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }

        $response = $result->result('array');

        if (!is_array($response) || count($response) == 0) {
            return array(array('Error_Msg' => 'Ошибка при сохранении извещения о раненом в ДТП'));
        }

        if (isset($response[0]['Error_Msg']) && strlen($response[0]['Error_Msg']) > 0) {
            return $response;
        }

        if (!isset($response[0]['EvnDtpWound_id']) || $response[0]['EvnDtpWound_id'] <= 0) {
            return array(array('Error_Msg' => 'Ошибка при сохранении извещения о раненом в ДТП'));
        }

        $data['EvnDtpWound_id'] = $response[0]['EvnDtpWound_id'];

        return array(array('EvnDtpWound_id' => $data['EvnDtpWound_id'],
                'Error_Msg' => ''));
    }

}

?>
