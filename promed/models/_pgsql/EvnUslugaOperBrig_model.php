<?php

/**
 * Class EvnUslugaOperBrig_model
 *
 * @property-read CI_DB_pdo_pgsql_driver $db
 */
class EvnUslugaOperBrig_model extends SwPgModel
{

    /**
     * Удаление
     *
     * @param $data
     * @return array
     * @throws Exception
     */
    function deleteEvnUslugaOperBrig($data)
    {
        $query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_EvnUslugaOperBrig_del
			(
				EvnUslugaOperBrig_id := :EvnUslugaOperBrig_id
			)
		";
        $result = $this->db->query($query, ['EvnUslugaOperBrig_id' => $data['EvnUslugaOperBrig_id']]);

        if (!is_object($result)) {
            throw new Exception('Ошибка при выполнении запроса к базе данных (удаление операционной бригады)');
        }

        return $result->result('array');
    }

    /**
     * Получение данных для грида
     *
     * @param $data
     * @return bool
     */
    function loadEvnUslugaOperBrigGrid($data)
    {
        $this->load->helper('MedStaffFactLink');
        $med_personal_list = getMedPersonalListWithLinks();

        $caseFilter = "";

        if ($data['session']['isMedStatUser'] == false && count($med_personal_list) > 0) {
            $caseFilter = "and EUO.MedPersonal_id in (" . implode(',', $med_personal_list) . ")";
        }

        $isMedPerson = isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0;
        $query = "
			select
				case 
				    when EUO.Lpu_id = :Lpu_id " . $caseFilter . " 
				 then 'edit' else 'view' end as \"accessType\",
				EUOB.EvnUslugaOperBrig_id as \"EvnUslugaOperBrig_id\",
				EUOB.EvnUslugaOper_id as EvnUslugaOperBrig_pid,
				EUO.Person_id as \"Person_id\",
				EUO.PersonEvn_id as \"PersonEvn_id\",
				EUO.Server_id as \"Server_id\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				coalesce(ST.SurgType_Code, 0) as \"SurgType_Code\",
				ST.SurgType_id as \"SurgType_id\",
				MSF.MedPersonal_TabCode as \"MedPersonal_Code\",
				RTRIM(coalesce(MSF.Person_Fio, '')) as \"MedPersonal_Fio\",
				RTRIM(coalesce(ST.SurgType_Name, '')) as \"SurgType_Name\"
			from v_EvnUslugaOperBrig EUOB
				inner join v_EvnUslugaOper EUO on EUO.EvnUslugaOper_id = EUOB.EvnUslugaOper_id
				left join SurgType ST on ST.SurgType_id = EUOB.SurgType_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EUOB.MedStaffFact_id
			where
			    EUOB.EvnUslugaOper_id = :EvnUslugaOperBrig_pid
			and
				(EUO.Lpu_id = :Lpu_id or " . $isMedPerson . " = 1)
		";
        $result = $this->db->query($query, [
            'EvnUslugaOperBrig_pid' => $data['EvnUslugaOperBrig_pid'],
            'Lpu_id' => $data['Lpu_id']
        ]);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * Функция определяет, существует ли рабочее место на указанную дату
     *
     * @param $MedStaffFact_id
     * @param $date
     * @return bool
     */
    function medStaffFactIsOpen($MedStaffFact_id, $date)
    {
        //Проверка что место работы врача не закрыто на дату выполнения услуги
        $query = "
                select 
                    MedStaffFact_id
                from
                    v_MedStaffFact
                where
                    MedStaffFact_id = :MedStaffFact_id
                and
                    (WorkData_endDate is null OR WorkData_endDate >= :setDT::timestamp)
                limit 1
        ";

        $response = $this->getFirstResultFromQuery($query, array(
            'setDT' => $date,
            'MedStaffFact_id' => $MedStaffFact_id
        ));

        return is_numeric($response) ? true : false;
    }

    /**
     * Сохранение
     *
     * @param $data
     * @return array|bool
     * @throws Exception
     */
    function saveEvnUslugaOperBrig($data)
    {
        $procedure = 'p_EvnUslugaOperBrig_upd';

        if ((!isset($data['EvnUslugaOperBrig_id'])) || ($data['EvnUslugaOperBrig_id'] <= 0)) {
            $procedure = 'p_EvnUslugaOperBrig_ins';
        }

        //Проверка что место работы врача не закрыто на дату выполнения услуги
        $isMSFOpen = $this->medStaffFactIsOpen($data['MedStaffFact_id'], $data['EvnUslugaOper_setDate']);


        if (!$isMSFOpen) {
            throw new Exception('У выбранного врача закрыто рабочее место. Добавление врача невозможно.');
        }

        $query = "
		    select
		        EvnUslugaOperBrig_id as \"EvnUslugaOperBrig_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from " . $procedure . "
			(
				EvnUslugaOperBrig_id := :EvnUslugaOperBrig_id,
				EvnUslugaOper_id := :EvnUslugaOperBrig_pid,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				SurgType_id := :SurgType_id,
				pmUser_id := :pmUser_id
			)
		";

        $queryParams = [
            'EvnUslugaOperBrig_id' => $data['EvnUslugaOperBrig_id'],
            'EvnUslugaOperBrig_pid' => $data['EvnUslugaOperBrig_pid'],
            'MedPersonal_id' => $data['MedPersonal_id'],
            'MedStaffFact_id' => $data['MedStaffFact_id'],
            'SurgType_id' => $data['SurgType_id'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $result = $this->db->query($query, $queryParams);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }
}