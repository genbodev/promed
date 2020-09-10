<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AttachmentCheck_model - модель для работы с параметрами проверки прикреплений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Max Sysolin (max.sysolin@gmail.com)
 * @version			20.04.2017
 */

class AttachmentCheck_model extends swPgModel {
    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Возвращает список параметров проверки прикреплений
     */
    function loadAttachmentCheckGrid($data)
    {
        $params = array();

        $query = "
			select
				AC.AttachmentCheck_id as \"AttachmentCheck_id\",
				AC.AttachmentCheck_CheckOn as \"AttachmentCheck_CheckOn\",
				ATTCHTYPE.LpuAttachType_Name as \"LpuAttachType_Name\",
				AC.LpuAttachType_id as \"LpuAttachType_id\",
				LPU.Lpu_Name as \"Lpu_Name\",
				AC.Lpu_id as \"Lpu_id\",
				PROFILE.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				AC.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				SPEC.MedSpecOms_Name as \"MedSpecOms_Name\",
				AC.MedSpecOms_id as \"MedSpecOms_id\",
				to_char(AC.AttachmentCheck_begDate, 'DD.MM.YYYY') || ' - ' || COALESCE(to_char(AC.AttachmentCheck_endDate, 'DD.MM.YYYY'), '') as \"AttachmentCheck_Period\"


			from
				v_AttachmentCheck as AC 

				left join v_LpuAttachType as ATTCHTYPE  on AC.LpuAttachType_id = ATTCHTYPE.LpuAttachType_id

				left join v_Lpu as LPU  on AC.Lpu_id = LPU.Lpu_id

				left join v_LpuSectionProfile as PROFILE  on AC.LpuSectionProfile_id = PROFILE.LpuSectionProfile_id

				left join v_MedSpecOms as SPEC  on AC.MedSpecOms_id = SPEC.MedSpecOms_id

            where LPU.Lpu_id is not null
                    or PROFILE.LpuSectionProfile_id is not null
                    or SPEC.MedSpecOms_id is not null

		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {

            $response['data'] = $result->result('array');
            return $response;

        } else
            return false;
    }

    /**
     * Возвращает запись проверки прикрепления
     */
    function getAttachmentCheckRecord($data)
    {
        $params = array('AttachmentCheck_id' => $data['AttachmentCheck_id']);

        $query = "
			select 
				AC.AttachmentCheck_id as \"AttachmentCheck_id\",
				AC.LpuAttachType_id as \"LpuAttachType_id\",
				AC.Lpu_id as \"ACEW_Lpu_id\",
				AC.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				AC.MedSpecOms_id as \"MedSpecOms_id\",
				AC.AttachmentCheck_CheckOn as \"AttachmentCheck_CheckOn\",
				to_char(AC.AttachmentCheck_begDate, 'DD.MM.YYYY') || ' - ' || COALESCE(to_char(AC.AttachmentCheck_endDate, 'DD.MM.YYYY'), '') as \"AttachmentCheck_Period\"


			from
				v_AttachmentCheck as AC 

			where AC.AttachmentCheck_id = :AttachmentCheck_id
limit 1
		";

        $result = $this->db->query($query, $params);

        if (is_object($result))
            return $result->result('array');
        else
            return false;
    }

    /**
     * Проверяет на дубликаты
     */
    function checkDuplicateAttachmentCheckRecord($data)
    {
        $filter = '';

        $params = array(
            'LpuAttachType_id' => $data['LpuAttachType_id'],
            'AttachmentCheck_begDate' => $data['AttachmentCheck_begDate'],
            'AttachmentCheck_endDate' => $data['AttachmentCheck_endDate']
        );

        if (isset($data['ACEW_Lpu_id'])){

            $params['Lpu_id'] = $data['ACEW_Lpu_id'];
            $filter .= ' and Lpu_id = :Lpu_id ';
        }

        if (isset($data['LpuSectionProfile_id'])){

            $params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
            $filter .= ' and LpuSectionProfile_id = :LpuSectionProfile_id ';
        }

        if (isset($data['MedSpecOms_id'])){

            $params['MedSpecOms_id'] = $data['MedSpecOms_id'];
            $filter .= ' and MedSpecOms_id = :MedSpecOms_id ';
        }

        $query = "
			select
				AttachmentCheck_id as \"AttachmentCheck_id\",
                Lpu_id as \"Lpu_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MedSpecOms_id as \"MedSpecOms_id\"
			from
				v_AttachmentCheck 

			where
			    LpuAttachType_id = :LpuAttachType_id
			    and AttachmentCheck_begDate = :AttachmentCheck_begDate
			    and AttachmentCheck_endDate = :AttachmentCheck_endDate
			    {$filter}
		";

        $result = $this->db->query($query, $params);
        $isDuplicate = false;

        if (is_object($result)) {

            $result->result('array');

            if (isset($result->result_array[0])) {

                $lpuSended = isset($data['ACEW_Lpu_id']);
                $profileSended = isset($data['LpuSectionProfile_id']);
                $specSended = isset($data['MedSpecOms_id']);

                $ret = $result->result_array;

                foreach($ret as $row) {

                    $lpuEquals = isset($row['Lpu_id']);
                    $profileEquals = isset($row['LpuSectionProfile_id']);
                    $specEquals = isset($row['MedSpecOms_id']);

                    // если на сабмит отправлено МО
                    if ($lpuSended) {

                        // и не указаны профиль\спец
                        if (!$profileSended && !$specSended) {

                            if ($lpuEquals && !$profileEquals && !$specEquals)
                                $isDuplicate = true;
                        }
                        // или если указан профиль не указана спец
                        elseif ($profileSended && !$specSended) {

                            if ($lpuEquals && $profileEquals && !$specEquals)
                                $isDuplicate = true;
                        }
                        // или если не указан профиль и указана спец
                        elseif (!$profileSended && $specSended) {

                            if ($lpuEquals && !$profileEquals && $specEquals)
                                $isDuplicate = true;
                        }
                        // если на сабмит МО не отправлено
                    } else {

                        // если указан профиль
                        if ($profileSended) {

                            if (!$lpuEquals && $profileEquals)
                                $isDuplicate = true;
                        }
                        // или если указан спец
                        elseif ($specSended) {

                            if (!$lpuEquals && $specEquals)
                                $isDuplicate = true;
                        }
                    }
                }
            }
        }

        return $isDuplicate;
    }

    /**
     * Сохраняет запись проверки прикрепления
     */
    function saveAttachmentCheckRecord($data)
    {
        list(

            $beginDate,
            $endDate

        ) = explode('-',$data['AttachmentCheck_Period']);

        if (trim($beginDate) != false && trim($endDate) != false) {

            $beginDate = strtotime(trim($beginDate));
            $endDate = strtotime(trim($endDate));

            $data['AttachmentCheck_begDate'] = date( 'Y-m-d', $beginDate);
            $data['AttachmentCheck_endDate'] = date( 'Y-m-d', $endDate);

            $isDuplicate = $this->checkDuplicateAttachmentCheckRecord($data);

            if (!$isDuplicate) {

                $query = "
                select AttachmentCheck_id as \"AttachmentCheck_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                from p_AttachmentCheck_" . (!empty($data['AttachmentCheck_id']) && $data['AttachmentCheck_id'] > 0 ? "upd" : "ins") . "(
                    AttachmentCheck_id := :AttachmentCheck_id,
                    LpuAttachType_id := :LpuAttachType_id,
                    Lpu_id := :Lpu_id,
                    LpuSectionProfile_id := :LpuSectionProfile_id,
                    MedSpecOms_id := :MedSpecOms_id,
                    AttachmentCheck_CheckOn := :AttachmentCheck_CheckOn,
                    AttachmentCheck_begDate := :AttachmentCheck_begDate,
                    AttachmentCheck_endDate := :AttachmentCheck_endDate,
                    pmUser_id := :pmUser_id);
            ";

                $params = array(
                    'AttachmentCheck_id' => (isset($data['AttachmentCheck_id']) ? $data['AttachmentCheck_id'] : null),
                    'LpuAttachType_id' => $data['LpuAttachType_id'],
                    'Lpu_id' => isset($data['ACEW_Lpu_id']) ? $data['ACEW_Lpu_id'] : null,
                    'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
                    'MedSpecOms_id' => $data['MedSpecOms_id'],
                    'AttachmentCheck_CheckOn' => $data['AttachmentCheck_CheckOn'],
                    'AttachmentCheck_begDate' => $data['AttachmentCheck_begDate'],
                    'AttachmentCheck_endDate' => $data['AttachmentCheck_endDate'],
                    'pmUser_id' => $data['pmUser_id']
                );

                $result = $this->db->query($query, $params);

                if (is_object($result))
                    return $result->result('array');
                else {
                    $response['Error_Msg'] = 'Ошибка запроса к БД';
                    return array($response);
                }
            } else {
                $response['Error_Msg'] = 'Данная настройка уже есть в системе. Возможно необходимо изменить период действия';
                return array($response);
            }
        } else {
            $response['Error_Msg'] = 'Невозможно определить временной период';
            return array($response);
        }
    }

    /**
     * Удаляет запись проверки прикрепления
     */
    function deleteAttachmentCheck($data)
    {
        $query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_AttachmentCheck_del(
				AttachmentCheck_id := :AttachmentCheck_id);
		";

        $params = array(
            'AttachmentCheck_id' => $data['AttachmentCheck_id']
        );

        $result = $this->db->query($query, $params);

        if ( is_object($result) )
            return $result->result('array');
        else
            return false;
    }

    /**
     * Возвращает список профилей для указанного типа прикрепления и МО
     * @return bool
     */
    function getLpuSectionProfiles($data)
    {

        $params = array('LpuAttachType_id' => $data['LpuAttachType_id']);

        $filter = '';
        $join = '';

        if (isset($data['lpu_id_filter']) && !empty($data['lpu_id_filter'])) {

            $params['Lpu_id'] = $data['lpu_id_filter'];
            $join .= 'inner join v_LpuSection as LS  on LS.LpuSectionProfile_id = ATLINK.LpuSectionProfile_id';

            $filter .= 'AND LS.Lpu_id = :Lpu_id';
        }

        $query = "
			select distinct
                ATLINK.LpuSectionProfile_id as \"LpuSectionProfile_id\"
			from
                v_ProfileMedSpecLpuAttachTypeLink as ATLINK 

                {$join}
            where
                ATLINK.LpuAttachType_id = :LpuAttachType_id
                {$filter}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {

            $profiles = $result->result('array');
            $id_list = array();

            //кладем в одномерный массив
            foreach ($profiles as $row) {
                $id_list[] = $row['LpuSectionProfile_id'];
            }

            return $id_list;

        } else
            return false;
    }

    /**
     * Возвращает список специальностей для указанного типа прикрепления и МО
     * @return bool
     */
    function getMedSpecs($data)
    {

        $params = array('LpuAttachType_id' => $data['LpuAttachType_id']);

        $filter = '';
        $join = '';

        if (isset($data['lpu_id_filter']) && !empty($data['lpu_id_filter'])) {

            $params['Lpu_id'] = $data['lpu_id_filter'];
            $join .= 'inner join v_MedStaffFact as MSF  on MSF.MedSpecOms_id = ATLINK.MedSpecOms_id';

            $filter .= 'AND MSF.Lpu_id = :Lpu_id';
        }

        $query = "
			select distinct
                ATLINK.MedSpecOms_id as \"MedSpecOms_id\"
			from
                v_ProfileMedSpecLpuAttachTypeLink as ATLINK 

                {$join}
            where
                ATLINK.LpuAttachType_id = :LpuAttachType_id
                {$filter}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {

            $profiles = $result->result('array');
            $id_list = array();

            //кладем в одномерный массив
            foreach ($profiles as $row) {
                $id_list[] = $row['MedSpecOms_id'];
            }

            return $id_list;

        } else
            return false;
    }

}