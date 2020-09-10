<?php

class EvnDtp_model extends CI_Model {

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
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
                RTRIM(ISNULL(uaddr.Address_Address, '')) as Lpu_UAddress,
                RTRIM(ISNULL(Org.Org_Phone, '')) as Lpu_Phone,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				RTRIM(ISNULL(Sex.Sex_Name, '')) as Sex_Name,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
   				ISNULL(convert(varchar(10), EvnDtpWound_ObrDate, 104), '') as EvnDtpWound_ObrDate,
   				ISNULL(convert(varchar(10), EvnDtpWound_HospDate, 104), '') as EvnDtpWound_HospDate,
   				ISNULL(convert(varchar(10), EvnDtpWound_DtpDate, 104), '') as EvnDtpWound_DtpDate,
   				ISNULL(convert(varchar(10), EvnDtpWound_setDate, 104), '') as EvnDtpWound_setDate,
   				RTRIM(ISNULL(DiagP.Diag_Code, '')) as DiagP_Code,
				RTRIM(ISNULL(DiagP.Diag_Name, '')) as DiagP_Name,
                RTRIM(ISNULL(DiagE.Diag_Code, '')) as DiagE_Code,
				RTRIM(ISNULL(DiagE.Diag_Name, '')) as DiagE_Name,
   				ISNULL(convert(varchar(10), EvnDtpWound_OtherLpuDate, 104), '') as EvnDtpWound_OtherLpuDate,
                RTRIM(ISNULL(OtherLpu.Lpu_Name, '')) as OtherLpu_Name,
                RTRIM(ISNULL(DiagO.Diag_Code, '')) as DiagO_Code,
				RTRIM(ISNULL(DiagO.Diag_Name, '')) as DiagO_Name,
                MP.Dolgnost_Name as MedPersonal_Dolgnost,
                MP.Person_Fin as MedPersonal_Fin
            from v_EvnDtpWound EDW WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EDW.Lpu_id
				inner join v_Person_all PS with (nolock) on PS.Server_id = EDW.Server_id
					and PS.PersonEvn_id = EDW.PersonEvn_id
                left join Address as uaddr with (nolock) on uaddr.Address_id = Lpu.UAddress_id
                left join v_Org Org with (nolock) on Org.Org_id = Lpu.Org_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join Diag DiagP with (nolock) on DiagP.Diag_id = EDW.Diag_pid
				left join Diag DiagE with (nolock) on DiagE.Diag_id = EDW.Diag_eid
				left join Diag DiagO with (nolock) on DiagO.Diag_id = EDW.Diag_oid
				left join v_Lpu as OtherLpu with (nolock) on OtherLpu.Lpu_id = EDW.Lpu_oid
                left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDW.MedPersonal_id
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDtpWound_del
				@EvnDtpWound_id = :EvnDtpWound_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			SELECT TOP 1
				EDW.EvnDtpWound_id,
				convert(varchar(10), EDW.EvnDtpWound_ObrDate,104) as EvnDtpWound_ObrDate, 
				convert(varchar(10), EDW.EvnDtpWound_HospDate,104) as EvnDtpWound_HospDate, 
				convert(varchar(10), EDW.EvnDtpWound_DtpDate,104) as EvnDtpWound_DtpDate, 
				EDW.Diag_pid,
				EDW.Diag_eid,
				convert(varchar(10), EDW.EvnDtpWound_OtherLpuDate,104) as EvnDtpWound_OtherLpuDate, 
				EDW.Lpu_oid,
				EDW.Diag_oid,
				EDW.MedPersonal_id,
				convert(varchar(10), EDW.EvnDtpWound_setDT, 104) as EvnDtpWound_setDate,
				EDW.Person_id,
				EDW.PersonEvn_id,
				EDW.Server_id
			FROM
				v_EvnDtpWound EDW with (nolock)
			WHERE (1 = 1)
				and EDW.EvnDtpWound_id = :EvnDtpWound_id
				and EDW.Lpu_id = :Lpu_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDtpWound_id;
			exec " . $procedure . "
				@EvnDtpWound_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDtpWound_setDT = :EvnDtpWound_setDT,
				@MedPersonal_id = :MedPersonal_id, 
				@EvnDtpWound_ObrDate = :EvnDtpWound_ObrDate,
				@EvnDtpWound_HospDate = :EvnDtpWound_HospDate,
				@EvnDtpWound_DtpDate = :EvnDtpWound_DtpDate,
				@EvnDtpWound_OtherLpuDate = :EvnDtpWound_OtherLpuDate,
				@Lpu_oid = :Lpu_oid,
				@Diag_pid = :Diag_pid,
				@Diag_eid = :Diag_eid,
				@Diag_oid = :Diag_oid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDtpWound_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
