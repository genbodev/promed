<?php
/**
 * EvnNotifyHIVPreg_model - модель для работы с таблицей EvnNotifyHIVPreg
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      12.2012
 */

class EvnNotifyHIVPreg_model extends SwPgModel
{
    protected $dateTimeFormat104 = "'dd.mm.yyyy'";

    /**
     * Проверка наличия извещения
     * Проверка выполняется из Common_model->signedDocument
     */
    function checkEvnNotifyHIVPreg($data)
    {
        $tableName = 'EvnNotifyHIVPreg';
        $this->load->library('swMorbus');
        $morbusCommon = swMorbus::getStaticMorbusCommon();

        $select = "
            ,EN.{$tableName}_id as \"{$tableName}_id\"
            ,PR.EvnNotifyBase_id as \"EvnNotifyBase_id\"
            ,PR.PersonRegister_id as \"PersonRegister_id\"
            ,PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\"
            ";
        $from = "
            left join v_{$tableName} EN on EN.Morbus_id = Morbus.Morbus_id
            left join v_PersonRegister PR  on PR.Morbus_id = Morbus.Morbus_id
        ";
        return $morbusCommon->checkExistsExtended('hiv',
            $data['Person_id'],
            null,
            $select,
            $from,
            true
        );
    }

    /**
     * Method description
     * @param $data
     * @return bool
     */
    function load($data)
    {
        $query = "
			select
				ENO.EvnNotifyHIVPreg_id as \"EvnNotifyHIVPreg_id\",
				ENO.EvnNotifyHIVPreg_pid as \"EvnNotifyHIVPreg_pid\",
				ENO.Morbus_id as \"Morbus_id\",
				ENO.Server_id as \"Server_id\",
				ENO.PersonEvn_id as \"PersonEvn_id\",
				ENO.Person_id as \"Person_id\",
				ENO.MedPersonal_id as \"MedPersonal_id\",
				to_char(ENO.EvnNotifyHIVPreg_setDT, {$this->dateTimeFormat104}) as \"EvnNotifyHIVPreg_setDT\",
				to_char(ENO.EvnNotifyHIVPreg_DiagDT, {$this->dateTimeFormat104}) as \"EvnNotifyHIVPreg_DiagDT\",
				to_char(ENO.EvnNotifyHIVPreg_endDT, {$this->dateTimeFormat104}) as \"EvnNotifyHIVPreg_endDT\",
				ENO.HIVPregPathTransType_id as \"HIVPregPathTransType_id\",
				ENO.HIVPregPeriodType_id as \"HIVPregPeriodType_id\",
				ENO.HIVPregInfectStudyType_id as \"HIVPregInfectStudyType_id\",
				ENO.HIVPregInfectStudyType_did as \"HIVPregInfectStudyType_did\",
				ENO.HIVPregResultType_id as \"HIVPregResultType_id\",
				ENO.EvnNotifyHIVPreg_IsPreterm as \"EvnNotifyHIVPreg_IsPreterm\",
				ENO.HIVPregWayBirthType_id as \"HIVPregWayBirthType_id\",
				ENO.EvnNotifyHIVPreg_OtherWayBirth as \"EvnNotifyHIVPreg_OtherWayBirth\",
				ENO.EvnNotifyHIVPreg_DuratBirth as \"EvnNotifyHIVPreg_DuratBirth\",
				ENO.EvnNotifyHIVPreg_DuratWaterless as \"EvnNotifyHIVPreg_DuratWaterless\",
				ENO.HIVPregChemProphType_id as \"HIVPregChemProphType_id\",
				ENO.EvnNotifyHIVPreg_SrokChem as \"EvnNotifyHIVPreg_SrokChem\",
				ENO.EvnNotifyHIVPreg_IsChemProphBirth as \"EvnNotifyHIVPreg_IsChemProphBirth\",
				ENO.HIVPregAbortPeriodType_id as \"HIVPregAbortPeriodType_id\",
				ENO.AbortType_id as \"AbortType_id\",
				ENO.EvnNotifyHIVPreg_Srok as \"EvnNotifyHIVPreg_Srok\"
			from
				v_EvnNotifyHIVPreg ENO
			where
				ENO.EvnNotifyHIVPreg_id = :EvnNotifyHIVPreg_id
		";
        $res = $this->db->query($query, $data);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Method description
     *
     * @param $data
     * @return array
     */
    function save($data)
    {
        try {

            $procedure_action = 'ins';

            if (!empty($data['EvnNotifyHIVPreg_id'])) {
                throw new Exception('Редактирование извещения не предусмотрено!');
                $procedure_action = 'upd';

            }
            $data['EvnNotifyHIVPreg_id'] = null;
            if (empty($data['Morbus_id'])) {
                throw new Exception('Не указано заболевание');
            }

            $this->load->library('swMorbus');

            $data['MorbusType_id'] = swMorbus::getMorbusTypeIdBySysNick('hiv');
            if (empty($data['MorbusType_id'])) {
                throw new Exception('Попытка получить идентификатор типа заболевания hiv провалилась', 500);
            }
            $queryEvnNotifyHIVPreg = "
				select
				    EvnNotifyHIVPreg_id as \"EvnNotifyHIVPreg_id\",
				    Error_Code as \"Error_Code\",
				    Error_Message as \"Error_Msg\"
				from p_EvnNotifyHIVPreg_' . $procedure_action . '
				(
					EvnNotifyHIVPreg_id := :EvnNotifyHIVPreg_id,
					EvnNotifyHIVPreg_pid := :EvnNotifyHIVPreg_pid,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					Morbus_id := :Morbus_id,
					MorbusType_id := :MorbusType_id,
					EvnNotifyHIVPreg_setDT := :EvnNotifyHIVPreg_setDT,
					MedPersonal_id := :MedPersonal_id,
					
					EvnNotifyHIVPreg_DiagDT := :EvnNotifyHIVPreg_DiagDT,
					EvnNotifyHIVPreg_endDT := :EvnNotifyHIVPreg_endDT,
					HIVPregPathTransType_id := :HIVPregPathTransType_id,
					HIVPregPeriodType_id := :HIVPregPeriodType_id,
					HIVPregInfectStudyType_id := :HIVPregInfectStudyType_id,
					HIVPregInfectStudyType_did := :HIVPregInfectStudyType_did,
					HIVPregResultType_id := :HIVPregResultType_id,
					EvnNotifyHIVPreg_IsPreterm := :EvnNotifyHIVPreg_IsPreterm,
					HIVPregWayBirthType_id := :HIVPregWayBirthType_id,
					EvnNotifyHIVPreg_OtherWayBirth := :EvnNotifyHIVPreg_OtherWayBirth,
					EvnNotifyHIVPreg_DuratBirth := :EvnNotifyHIVPreg_DuratBirth,
					EvnNotifyHIVPreg_DuratWaterless := :EvnNotifyHIVPreg_DuratWaterless,
					HIVPregChemProphType_id := :HIVPregChemProphType_id,
					EvnNotifyHIVPreg_SrokChem := :EvnNotifyHIVPreg_SrokChem,
					EvnNotifyHIVPreg_IsChemProphBirth := :EvnNotifyHIVPreg_IsChemProphBirth,
					HIVPregAbortPeriodType_id := :HIVPregAbortPeriodType_id,
					AbortType_id := :AbortType_id,
					EvnNotifyHIVPreg_Srok := :EvnNotifyHIVPreg_Srok,

					pmUser_id := :pmUser_id
			)";
            // Стартуем транзакцию
            if (!$this->beginTransaction()) {
                throw new Exception('Ошибка при попытке запустить транзакцию');
            }
            //Сохраняем извещение
            $res = $this->db->query($queryEvnNotifyHIVPreg, $data);
            if (!is_object($res)) {
                $this->rollbackTransaction();
                throw new Exception('Ошибка БД!');
            }
            $tmp = $res->result('array');
            if (isset($tmp[0]['Error_Msg'])) {
                $this->rollbackTransaction();
                throw new Exception($tmp[0]['Error_Msg']);
            }

            $this->commitTransaction();
            return $tmp;
        } catch (Exception $e) {
            return [[
                    'EvnNotifyHIVPreg_id' => $data['EvnNotifyHIVPreg_id'],
                    'Error_Msg' => 'Cохранениe извещения. <br />' . $e->getMessage()
            ]];
        }
    }

    /**
     * Method description
     * @param $data
     * @return bool
     */
    function del($data)
    {
        $query = "
            select
                EvnNotifyHIVPreg_id as \"EvnNotifyHIVPreg_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EvnNotifyHIVPreg_del
			(
				EvnNotifyHIVPreg_id := :EvnNotifyHIVPreg_id,
				pmUser_id := :pmUser_id
			)
		";

        $queryParams = [
            'EvnNotifyHIVPreg_id' => $data['EvnNotifyHIVPreg_id'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $res = $this->db->query($query, $queryParams);

        if (!is_object($res))
            return false;

        return $res->result('array');
    }
}
