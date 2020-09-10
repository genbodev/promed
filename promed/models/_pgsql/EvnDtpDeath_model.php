<?php   defined('BASEPATH') or die ('No direct script access allowed');

/**
 * PromedWeb
 *
 * Класс модели скончавшиеся в ДТП
 *
 * The New Generation of Medical Statistic Software
 *
 * @package             Common
 * @copyright           Copyright (c) 2016 Swan Ltd.
 * @author              Alexander Kurakin (a.kurakin@swan.perm.ru)
 * @link                http://swan.perm.ru/PromedWeb
 * @version             2016
 */

class EvnDtpDeath_model extends SwPgModel {

	/**
	 * Comment
	 */
    function __construct() {
        parent::__construct();
    }

	/**
	 * Comment
	 */
    function getEvnDtpDeathFields($data) {
        $query = "
			select
				RTRIM(COALESCE(Lpu.Lpu_Name, '')) as \"Lpu_Name\",
                RTRIM(COALESCE(uaddr.Address_Address, '')) as \"Lpu_UAddress\",
                RTRIM(COALESCE(Org.Org_Phone, '')) as \"Lpu_Phone\",
				RTRIM(RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, ''))) as \"Person_Fio\",
				RTRIM(COALESCE(Sex.Sex_Name, '')) as \"Sex_Name\",
				to_char (PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
   				COALESCE(to_char (EvnDtpDeath_DeathDate, 'dd.mm.yyyy'), '') as \"EvnDtpDeath_DeathDate\",
   				COALESCE(to_char (EvnDtpDeath_HospDate, 'dd.mm.yyyy'), '') as \"EvnDtpDeath_HospDate\",
   				COALESCE(to_char (EvnDtpDeath_DtpDate, 'dd.mm.yyyy'), '') as \"EvnDtpDeath_DtpDate\",
   				COALESCE(to_char (EvnDtpDeath_setDate, 'dd.mm.yyyy'), '') as \"EvnDtpDeath_setDate\",
   				RTRIM(COALESCE(DiagP.Diag_Code, '')) as \"DiagP_Code\",
				RTRIM(COALESCE(DiagP.Diag_Name, '')) as \"DiagP_Name\",
                RTRIM(COALESCE(DiagE.Diag_Code, '')) as \"DiagE_Code\",
				RTRIM(COALESCE(DiagE.Diag_Name, '')) as \"DiagE_Name\",
                RTRIM(COALESCE(DiagI.Diag_Code, '')) as \"DiagI_Code\",
                RTRIM(COALESCE(DiagI.Diag_Name, '')) as \"DiagI_Name\",
                RTRIM(COALESCE(DiagM.Diag_Code, '')) as \"DiagM_Code\",
                RTRIM(COALESCE(DiagM.Diag_Name, '')) as \"DiagM_Name\",
                MP.Dolgnost_Name as \"MedPersonal_Dolgnost\",
                MP.Person_Fin as \"MedPersonal_Fin\",
                COALESCE(EDD.DtpDeathPlace_id, 0) as \"DeathPlace\",
                COALESCE(EDD.DtpDeathTime_id, 0) as \"DeathTime\"
            from v_EvnDtpDeath EDD
				inner join v_Lpu Lpu on Lpu.Lpu_id = EDD.Lpu_id
				inner join v_Person_all PS on PS.Server_id = EDD.Server_id
					and PS.PersonEvn_id = EDD.PersonEvn_id
                left join Address as uaddr on uaddr.Address_id = Lpu.UAddress_id
                left join v_Org Org on Org.Org_id = Lpu.Org_id
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join Diag DiagP on DiagP.Diag_id = EDD.Diag_pid
				left join Diag DiagE on DiagE.Diag_id = EDD.Diag_eid
				left join Diag DiagI on DiagI.Diag_id = EDD.Diag_iid
                left join Diag DiagM on DiagM.Diag_id = EDD.Diag_mid
                left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EDD.MedStaffFact_id
                left join v_MedPersonal MP on MP.MedPersonal_id = MSF.MedPersonal_id
			where
				EDD.EvnDtpDeath_id = :EvnDtpDeath_id
				and EDD.Lpu_id = :Lpu_id
		";
        $result = $this->db->query($query, array(
                    'EvnDtpDeath_id' => $data['EvnDtpDeath_id'],
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
    function deleteEvnDtpDeath($data) {
        $query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EvnDtpDeath_del (
				EvnDtpDeath_id := :EvnDtpDeath_id,
				pmUser_id := :pmUser_id
				)
		";
        $result = $this->db->query($query, array('EvnDtpDeath_id' => $data['EvnDtpDeath_id'],
                    'pmUser_id' => $data['pmUser_id']));

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление извещения о скончавшемся в ДТП)'));
        }
    }

	/**
	 * Comment
	 */
    function loadEvnDtpDeathEditForm($data) {
        $query = "
			SELECT
				EDD.EvnDtpDeath_id as \"EvnDtpDeath_id\",
				to_char (EDD.EvnDtpDeath_HospDate,'dd.mm.yyyy') as \"EvnDtpDeath_HospDate\",
				to_char (EDD.EvnDtpDeath_DtpDate,'dd.mm.yyyy') as \"EvnDtpDeath_DtpDate\",
                to_char (EDD.EvnDtpDeath_DeathDate,'dd.mm.yyyy') as \"EvnDtpDeath_DeathDate\",
				EDD.Diag_pid as \"Diag_pid\",
				EDD.Diag_iid as \"Diag_iid\",
                EDD.Diag_mid as \"Diag_mid\",
                EDD.Diag_eid as \"Diag_eid\",
				EDD.MedStaffFact_id as \"MedStaffFact_id\",
				to_char (EDD.EvnDtpDeath_setDT, 'dd.mm.yyyy') as \"EvnDtpDeath_setDate\",
				EDD.Person_id as \"Person_id\",
				EDD.PersonEvn_id as \"PersonEvn_id\",
				EDD.Server_id as \"Server_id\",
                EDD.DtpDeathPlace_id as \"DtpDeathPlace_id\",
                EDD.DtpDeathTime_id as \"DtpDeathTime_id\"
			FROM
				v_EvnDtpDeath EDD
			WHERE (1 = 1)
				and EDD.EvnDtpDeath_id = :EvnDtpDeath_id
				and EDD.Lpu_id = :Lpu_id
            LIMIT 1
		";
        $result = $this->db->query($query, array('EvnDtpDeath_id' => $data['EvnDtpDeath_id'],
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
    function saveEvnDtpDeath($data) {
        $procedure = '';

        if (isset($data['EvnDtpDeath_id'])) {
            $procedure = 'p_EvnDtpDeath_upd';
        } else {
            $procedure = 'p_EvnDtpDeath_ins';
        }

        $query = "
			select
			    EvnDtpDeath_id as \"EvnDtpDeath_id\", 
			    Error_Code as \"Error_Code\", 
			    Error_Message as \"Error_Msg\"
			from " . $procedure . " (
				EvnDtpDeath_id := :EvnDtpDeath_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnDtpDeath_setDT := :EvnDtpDeath_setDT,
				MedStaffFact_id := :MedStaffFact_id, 
                EvnDtpDeath_DtpDate := :EvnDtpDeath_DtpDate,
				EvnDtpDeath_HospDate := :EvnDtpDeath_HospDate,
				EvnDtpDeath_DeathDate := :EvnDtpDeath_DeathDate,
				Diag_pid := :Diag_pid,
                Diag_iid := :Diag_iid,
                Diag_mid := :Diag_mid,
				Diag_eid := :Diag_eid,
                DtpDeathPlace_id := :DtpDeathPlace_id,
                DtpDeathTime_id := :DtpDeathTime_id,
				pmUser_id := :pmUser_id
				)
		";

        $queryParams = array('EvnDtpDeath_id' => isset($data['EvnDtpDeath_id'])?$data['EvnDtpDeath_id']:null,
            'Lpu_id' => $data['Lpu_id'],
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
            'EvnDtpDeath_setDT' => $data['EvnDtpDeath_setDate'],
            'MedStaffFact_id' => $data['MedStaffFact_id'],
            'EvnDtpDeath_DeathDate' => $data['EvnDtpDeath_DeathDate'],
            'EvnDtpDeath_HospDate' => $data['EvnDtpDeath_HospDate'],
            'EvnDtpDeath_DtpDate' => $data['EvnDtpDeath_DtpDate'],
            'Diag_pid' => $data['Diag_pid'],
            'Diag_eid' => $data['Diag_eid'],
            'Diag_iid' => $data['Diag_iid'],
            'Diag_mid' => $data['Diag_mid'],
            'DtpDeathPlace_id' => $data['DtpDeathPlace_id'],
            'DtpDeathTime_id' => $data['DtpDeathTime_id'],
            'pmUser_id' => $data['pmUser_id']);

        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }

        $response = $result->result('array');

        if (!is_array($response) || count($response) == 0) {
            return array(array('Error_Msg' => 'Ошибка при сохранении извещения о скончавшемся в ДТП'));
        }

        if (isset($response[0]['Error_Msg']) && strlen($response[0]['Error_Msg']) > 0) {
            return $response;
        }

        if (!isset($response[0]['EvnDtpDeath_id']) || $response[0]['EvnDtpDeath_id'] <= 0) {
            return array(array('Error_Msg' => 'Ошибка при сохранении извещения о скончавшемся в ДТП'));
        }

        $data['EvnDtpDeath_id'] = $response[0]['EvnDtpDeath_id'];

        return array(array('EvnDtpDeath_id' => $data['EvnDtpDeath_id'],
                'Error_Msg' => ''));
    }

}

?>
