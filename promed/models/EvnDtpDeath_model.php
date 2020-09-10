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

class EvnDtpDeath_model extends CI_Model {

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
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
                RTRIM(ISNULL(uaddr.Address_Address, '')) as Lpu_UAddress,
                RTRIM(ISNULL(Org.Org_Phone, '')) as Lpu_Phone,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				RTRIM(ISNULL(Sex.Sex_Name, '')) as Sex_Name,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
   				ISNULL(convert(varchar(10), EvnDtpDeath_DeathDate, 104), '') as EvnDtpDeath_DeathDate,
   				ISNULL(convert(varchar(10), EvnDtpDeath_HospDate, 104), '') as EvnDtpDeath_HospDate,
   				ISNULL(convert(varchar(10), EvnDtpDeath_DtpDate, 104), '') as EvnDtpDeath_DtpDate,
   				ISNULL(convert(varchar(10), EvnDtpDeath_setDate, 104), '') as EvnDtpDeath_setDate,
   				RTRIM(ISNULL(DiagP.Diag_Code, '')) as DiagP_Code,
				RTRIM(ISNULL(DiagP.Diag_Name, '')) as DiagP_Name,
                RTRIM(ISNULL(DiagE.Diag_Code, '')) as DiagE_Code,
				RTRIM(ISNULL(DiagE.Diag_Name, '')) as DiagE_Name,
                RTRIM(ISNULL(DiagI.Diag_Code, '')) as DiagI_Code,
                RTRIM(ISNULL(DiagI.Diag_Name, '')) as DiagI_Name,
                RTRIM(ISNULL(DiagM.Diag_Code, '')) as DiagM_Code,
                RTRIM(ISNULL(DiagM.Diag_Name, '')) as DiagM_Name,
                MP.Dolgnost_Name as MedPersonal_Dolgnost,
                MP.Person_Fin as MedPersonal_Fin,
                RTRIM(ISNULL(EDD.DtpDeathPlace_id, '')) as DeathPlace,
                RTRIM(ISNULL(EDD.DtpDeathTime_id, '')) as DeathTime
            from v_EvnDtpDeath EDD WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EDD.Lpu_id
				inner join v_Person_all PS with (nolock) on PS.Server_id = EDD.Server_id
					and PS.PersonEvn_id = EDD.PersonEvn_id
                left join Address as uaddr with (nolock) on uaddr.Address_id = Lpu.UAddress_id
                left join v_Org Org with (nolock) on Org.Org_id = Lpu.Org_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join Diag DiagP with (nolock) on DiagP.Diag_id = EDD.Diag_pid
				left join Diag DiagE with (nolock) on DiagE.Diag_id = EDD.Diag_eid
				left join Diag DiagI with (nolock) on DiagI.Diag_id = EDD.Diag_iid
                left join Diag DiagM with (nolock) on DiagM.Diag_id = EDD.Diag_mid
                left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = EDD.MedStaffFact_id
                left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDtpDeath_del
				@EvnDtpDeath_id = :EvnDtpDeath_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			SELECT TOP 1
				EDD.EvnDtpDeath_id,
				convert(varchar(10), EDD.EvnDtpDeath_HospDate,104) as EvnDtpDeath_HospDate, 
				convert(varchar(10), EDD.EvnDtpDeath_DtpDate,104) as EvnDtpDeath_DtpDate,
                convert(varchar(10), EDD.EvnDtpDeath_DeathDate,104) as EvnDtpDeath_DeathDate,  
				EDD.Diag_pid,
				EDD.Diag_iid,
                EDD.Diag_mid,
                EDD.Diag_eid,
				EDD.MedStaffFact_id,
				convert(varchar(10), EDD.EvnDtpDeath_setDT, 104) as EvnDtpDeath_setDate,
				EDD.Person_id,
				EDD.PersonEvn_id,
				EDD.Server_id,
                EDD.DtpDeathPlace_id,
                EDD.DtpDeathTime_id
			FROM
				v_EvnDtpDeath EDD with (nolock)
			WHERE (1 = 1)
				and EDD.EvnDtpDeath_id = :EvnDtpDeath_id
				and EDD.Lpu_id = :Lpu_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDtpDeath_id;
			exec " . $procedure . "
				@EvnDtpDeath_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDtpDeath_setDT = :EvnDtpDeath_setDT,
				@MedStaffFact_id = :MedStaffFact_id, 
                @EvnDtpDeath_DtpDate = :EvnDtpDeath_DtpDate,
				@EvnDtpDeath_HospDate = :EvnDtpDeath_HospDate,
				@EvnDtpDeath_DeathDate = :EvnDtpDeath_DeathDate,
				@Diag_pid = :Diag_pid,
                @Diag_iid = :Diag_iid,
                @Diag_mid = :Diag_mid,
				@Diag_eid = :Diag_eid,
                @DtpDeathPlace_id = :DtpDeathPlace_id,
                @DtpDeathTime_id = :DtpDeathTime_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDtpDeath_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
