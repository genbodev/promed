<?php

class EvnForensicGenetic_model extends BSME_model {

    /**
     * Функция сохранения заявки для службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
     * @param array $data
     * @return boolean
     */
    public function saveEvnForensicGeneticRequest($data) {

        /* Сохранение заявки */
        $this->db->trans_begin();


        $insertEvnForensicGeneticResult = $this->saveEvnForensicGenetic($data);
        if (!$this->isSuccessful($insertEvnForensicGeneticResult)) {
            $this->db->trans_rollback();
            return $insertEvnForensicGeneticResult;
        }

        $data['EvnForensicGenetic_id'] = $insertEvnForensicGeneticResult[0]['EvnForensicGenetic_id'];

        /*Проверка наличия и полноты записей журналов*/


        $journalsCount = 0; //Счетчик заполненных журналов


        /****************************************************************************************** */
        /* Проверка журнала регистрации вещественных доказательств и документов к ним в лаборатории */
        /****************************************************************************************** */
        $journalSingleFields = array('EvnForensicGeneticEvid_AccDocNum','EvnForensicGeneticEvid_AccDocDate','EvnForensicGeneticEvid_AccDocNumSheets','Org_id','EvnForensicGeneticEvid_Facts','EvnForensicGeneticEvid_Goal');
        $journalArrayFields = array(array('key'=>'Person','val'=>'Person_id'),array('key'=>'Evidence','val'=>'Evidence_Name'));

        $journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);

        if ($journalFilledStatus == 'empty') {
            //Ничего
        } elseif ($journalFilledStatus == 'unfinished') {

            $this->db->trans_rollback();
            return $this->createError('', 'Не все поля журнала регистрации вещественных </br> доказательств и документов к ним в лаборатории заполнены. </br> Пожалуйста, заполните все поля');

        } elseif ($journalFilledStatus == 'filled') {

            $saveJournalResult = $this->_saveEvnForensicGeneticEvidJournal($data);

            if (!$this->isSuccessful($saveJournalResult)) {
                $this->db->trans_rollback();
                return $saveJournalResult;
            }
        }

        /****************************************************************************************** */
        /* Проверка журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории */
        /****************************************************************************************** */

        $journalSingleFields = array('EvnForensicGeneticSampleLive_TakeDate','Person_zid','EvnForensicGeneticSampleLive_Basis');
        $journalArrayFields = array(array('key'=>'BioSample','val'=>'BioSample_Name'));

        $journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);

        if ($journalFilledStatus == 'empty') {
            //Ничего
        } elseif ($journalFilledStatus == 'unfinished') {

            $this->db->trans_rollback();
            return $this->createError('', 'Не все поля журнала регистрации биологических </br> образцов, изъятых у живых лиц в лаборатории заполнены. </br> Пожалуйста, заполните все поля');

        } elseif ($journalFilledStatus == 'filled') {

            $saveJournalResult = $this->_saveEvnForensicGeneticSampleLiveJournal($data);

            if (!$this->isSuccessful($saveJournalResult)) {
                $this->db->trans_rollback();
                return $saveJournalResult;
            }
        }

        /*******************************************************************************************/
        /* Проверка журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования */
        /****************************************************************************************** */

        $journalSingleFields = array('EvnForensicGeneticGenLive_TakeDate','EvnForensicGeneticGenLive_Facts');
        $journalArrayFields = array(array('key'=>'PersonBioSample','val'=>'Person_id'),array('key'=>'BioSampleForMolGenRes','val'=>'BioSampleForMolGenRes_Name'));

        $journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);

        if ($journalFilledStatus == 'empty') {
            //Ничего
        } elseif ($journalFilledStatus == 'unfinished') {
            $this->db->trans_rollback();
            return $this->createError('','Не все поля журнала регистрации биологических образцов, </br> изъятых у живых лиц в лаборатории для молекулярно-генетического исследования заполнены. </br> Пожалуйста, заполните все поля');
        } elseif ($journalFilledStatus == 'filled') {
            $saveJournalResult = $this->_saveEvnForensicGeneticGenLiveJournal($data);

            if (!$this->isSuccessful($saveJournalResult)) {
                $this->db->trans_rollback();
                return $saveJournalResult;
            }
        }

        /****************************************************************************************** */
        /* Проверка журнала регистрации исследований мазков и тампонов в лаборатории */
        /****************************************************************************************** */

        $journalSingleFields = array('ReasearchedPerson_id','EvnForensicGeneticSmeSwab_Basis');
        $journalArrayFields = array(array('key'=>'Sample','val'=>'Sample_Name'));
        $journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);

        if ($journalFilledStatus == 'empty') {
            //Ничего
        } elseif ($journalFilledStatus == 'unfinished' || !$journalFilledStatus) {

            $this->db->trans_rollback();
            return array(array('succes'=>false,'Error_Msg'=>'Не все поля журнала регистрации исследований </br> мазков и тампонов в лаборатории заполнены. </br> Пожалуйста, заполните все поля'));

        } elseif ($journalFilledStatus == 'filled') {

            $saveJournalResult = $this->_saveEvnForensicGeneticSmeSwabJournal($data);

            if (!$this->isSuccessful($saveJournalResult)) {
                $this->db->trans_rollback();
                return $saveJournalResult;
            }

        }

        //if ($journalsCount == 0) {
        //	return array(array('succes'=>false,'Error_Msg'=>'Не заполнено ни одного журнала </br> Пожалуйста, заполните хотя бы один журнал.'));
        //}

        $this->db->trans_commit();
        return $insertEvnForensicGeneticResult;

    }

    /**
     * Функция сохранения заявки  службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
     */
    protected function saveEvnForensicGenetic($data) {
        if (empty($data['MedService_id'])) {
            if (empty($data['MedService_pid'])) {
                if (empty($data['session']['CurMedService_id'])) {
                    return $this->createError('','Не задан обязательный параметр: Идентификатор службы');
                } else {
                    $data['MedService_id'] = $data['session']['CurMedService_id'];
                }
            } else {
                //Если передан MedService_fid - значит создается направление из другой службы и из сессии получить MedService_id службы, 
                //которой направление назначается, не получится
                return $this->createError('','Не задан обязательный параметр: Идентификатор службы');
            }
        }

        $rules = array(
            array('field'=>'Evn_pid','label'=>'Идентификатор родительского события', 'type' => 'id'),
            array('field'=>'EvnForensic_ResDate','label'=>'Дата постановления', 'rules'=>'','type' => 'string'),
            array('field'=>'Evn_rid', 'label'=>'Идентификатор получателя документа', 'type' => 'id'),
            array('field'=>'Person_cid','label'=>'Направившее лицо', 'rules'=>'required', 'type' => 'id'),
            array('field'=>'MedService_id','label'=>'Идентификатор службы', 'rules'=>'required', 'type' => 'id'),
            array('field'=>'MedService_pid','label'=>'Идентификатор службы - родителя', 'type' => 'id'),
            array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
            array('field'=>'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				EvnForensicGenetic_id as \"EvnForensicGenetic_id\",
				EvnForensic_Num as \"EvnForensic_Num\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicGenetic_ins (
				EvnForensicGenetic_id := :EvnForensicGenetic_id,
				EvnForensicGenetic_Num := COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1),
				EvnForensicGenetic_ResDate := :EvnForensic_ResDate,
				MedService_id := :MedService_id,
				Person_cid := :Person_cid,
				MedService_pid := :MedService_pid,
				EvnForensicGenetic_pid :=:Evn_pid,
				EvnForensicGenetic_rid :=:Evn_rid,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
		";
        return $this->queryResult($query,$queryParams);
    }

    /**
     * Функция сохранения вещественного докозательства или био образца
     * @param int $data
     * @return boolean
     */
    protected function _saveEvnForensicGeneticGenLiveLink($data) {

        $rules = array(
            array('field'=>'EvnForensicGeneticGenLive_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
            array('field'=>'Person_id','label'=>'Идентификатор обвиняемого/потерпевшего', 'rules'=>'required' , 'type' => 'id'),
            array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				EvnForensicGeneticGenLiveLink_id as \"EvnForensicGeneticGenLiveLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicGeneticGenLiveLink_ins (
				Person_id := :Person_id,
				EvnForensicGeneticGenLive_id := :EvnForensicGeneticGenLive_id,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query, $queryParams);
    }
    /**
     * Функция удаления всех подозреваемых/обвиняемых для записи журнала регистрации вещественных доказательств и документов к ним в лаборатории
     * @param type $data
     * @return type
     */
    protected function _deleteEvnForensicGeneticEvidLink($data) {
        $rules = array(
            array('field'=>'EvnForensicGeneticEvid_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicGeneticEvidLink_delByEvnForensicId (
				EvnForensic_id := :EvnForensic_id
			);
		";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Функция сохранения подозреваемого/обвиняемого для журнала регистрации вещественных доказательств и документов к ним в лаборатории
     * @param type $data
     * @return boolean
     */
    protected function _saveEvnForensicGeneticEvidLink($data) {
        $rules = array(
            array('field'=>'EvnForensicGeneticEvid_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
            array('field'=>'Person_id','label'=>'Идентификатор обвиняемого/потерпевшего', 'rules'=>'required' , 'type' => 'id'),
            array('field'=>'EvnForensicGeneticEvidLink_IsVic','label'=>'флаг потерпевшего', 'rules'=>'required' , 'type' => 'int'),
            array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				EvnForensicGeneticEvidLink_id as \"EvnForensicGeneticEvidLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicGeneticEvidLink_ins (
				EvnForensicGeneticEvid_id := :EvnForensicGeneticEvid_id,
				Person_id := :Person_id,
				EvnForensicGeneticEvidLink_IsVic := :EvnForensicGeneticEvidLink_IsVic,	
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query, $queryParams);

    }
    /**
     * Функция удаления всех исследуемых лиц для записи журнала регистрации биологичесеских образцов живых лиц для мол/ген исследования
     */
    protected function _deleteEvnForensicGeneticGenLiveLink($data) {
        $rules = array(
            array('field'=>'EvnForensicGeneticGenLive_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicGeneticGenLiveLink_delByEvnForensicId (
				EvnForensic_id := :EvnForensic_id
			);
		";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Функция получения записи журнала мазков и тампонов
     * @param int $data
     * @return boolean
     */
    protected function getEvnForensicGeneticSmeSwabJournal($data) {
        $rules = array(
            array('field' => 'EvnForensicGeneticSmeSwab_id', 'label' => 'Идентификатор записи в журнале', 'rules' => 'required', 'type' => 'id'),
        );
        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT 
				EFGSS.EvnForensicGeneticSmeSwab_DelivDate as \"EvnForensicGeneticSmeSwab_DelivDate\",
				EFGSS.EvnForensicGeneticSmeSwab_Basis as \"EvnForensicGeneticSmeSwab_Basis\",
				EFGSS.EvnForensicGeneticSmeSwab_BegDate as \"EvnForensicGeneticSmeSwab_BegDate\",
				EFGSS.EvnForensicGeneticSmeSwab_EndDate as \"EvnForensicGeneticSmeSwab_EndDate\",
				EFGSS.EvnForensicGeneticSmeSwab_Comment as \"EvnForensicGeneticSmeSwab_Comment\"
			FROM
				v_EvnForensicGeneticSmeSwab EFGSS 
			WHERE
				EFGSS.EvnForensicGeneticSmeSwab_id = :EvnForensicGeneticSmeSwab_id
		";

        return $this->queryResult($query,$queryParams);

    }
    /**
     * Функция сохранения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории
     * @param type $data
     */
    protected function _saveEvnForensicGeneticSmeSwabJournal($data) {
        $rules = array(
            array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticSmeSwab_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
            array('field' => 'ReasearchedPerson_id', 'label' => 'Исследуемое лицо журнала регистрации исследований мазков и тампонов в лаборатории', 'rules' => '', 'type' => 'id'),
            array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
            array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticSmeSwab_Basis', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticSmeSwab_DelivDate', 'label' => 'Дата и время поступления образцов', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticSmeSwab_BegDate', 'label' => 'Дата начала исследования', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticSmeSwab_EndDate', 'label' => 'Дата окончания исследования', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticSmeSwab_Comment', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
            array('field' => 'Sample', 'label' => 'Список образцов', 'rules' => '', 'type' => 'array'),
            array('field' => 'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
            array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $proc = 'p_EvnForensicGeneticSmeSwab_ins';
        // Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
        // и удаляем прикреплённые биоматериалы
        if (!empty($queryParams['EvnForensicGeneticSmeSwab_id'])) {
            $proc = 'p_EvnForensicGeneticSmeSwab_upd';
            $currentDataResult = $this->getEvnForensicGeneticSmeSwabJournal($queryParams);
            if (!$this->isSuccessful($currentDataResult)) {
                return $currentDataResult;
            }
            //Те поля, что не переданы, восполняем из последней сохраненной записи
            foreach ($queryParams as $key => $value) {
                $queryParams = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
            }

            //Если переданы новые образцы сначала удаляем их из последней сохраненной записи журнала
            if (!empty($queryParams['Sample'])) {
                $deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticSmeSwab_id']));
                if (!$this->isSuccessful($deleteEvidResult)) {
                    return $deleteEvidResult;
                }
            }
        } else {

            // У вновь создаваемой записи журнала этого типа PersonEvn_id и Server_id обязательны и получаются из ReasearchedPerson_id
            if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
                //Если PersonEvn_id и Server_id не переданы, должен быть передан ReasearchedPerson_id - идентификатор исследуемого лица
                if (empty($queryParams['ReasearchedPerson_id'])) {
                    return  $this->createError('','Не задан обязательный параметр: Исследуемое лицо');
                } else {
                    $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$queryParams['ReasearchedPerson_id']));
                    if (!$this->isSuccessful($personState) || sizeof($personState)==0) {
                        return $this->createError('', 'Ошибка получения идентификатора состояния');
                    } else {
                        $queryParams = array_merge($queryParams,$personState[0]);
                    }
                }
            }
        }

        $query = "
			select
				EvnForensicGeneticSmeSwab_id as \"EvnForensicGeneticSmeSwab_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicGeneticSmeSwab_ins (
				EvnForensicGeneticSmeSwab_pid := :EvnForensicGenetic_id,
				EvnForensicGeneticSmeSwab_DelivDate:=:EvnForensicGeneticSmeSwab_DelivDate,
				EvnForensicGeneticSmeSwab_Basis:=:EvnForensicGeneticSmeSwab_Basis,
				EvnForensicGeneticSmeSwab_BegDate:=:EvnForensicGeneticSmeSwab_BegDate,
				EvnForensicGeneticSmeSwab_EndDate:=:EvnForensicGeneticSmeSwab_EndDate,
				EvnForensicGeneticSmeSwab_Comment:=:EvnForensicGeneticSmeSwab_Comment,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
		";

        $result = $this->queryResult($query,$queryParams);

        if (!$this->isSuccessful($result)) {
            return $result;
        }

        foreach ($queryParams['Sample'] as $evidence) {
            $saveEvidenceResult = $this->_saveEvidence(array(
                'EvnForensic_id'=>$result[0]['EvnForensicGeneticSmeSwab_id'],
                'Evidence_Name'=>$evidence['Sample_Name'],
                'EvidenceType_id'=>1,
                'pmUser_id' => $queryParams['pmUser_id'],
            ));
            if (!$this->isSuccessful($saveEvidenceResult)) {
                return $saveEvidenceResult;
            }
        }

        return $result;
    }
    /**
     * Функция получения записи журнала регистрации биообразцов живых лиц для мол/ген исследования
     * @param int $data
     * @return boolean
     */
    protected function getEvnForensicGeneticGenLiveJournal($data) {
        $rules = array(
            array('field' => 'EvnForensicGeneticGenLive_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
        );
        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				EFGGL.EvnForensicGenetic_id as \"EvnForensicGenetic_id\",
				EFGGL.EvnForensicGeneticGenLive_id as \"EvnForensicGeneticGenLive_id\",
				EFGGL.EvnForensicGeneticGenLive_TakeDate as \"EvnForensicGeneticGenLive_TakeDate\",
				EFGGL.EvnForensicGeneticGenLive_Facts as \"EvnForensicGeneticGenLive_Facts\",
				EFGGL.Person_eid as \"Person_eid\",
				EFGGL.Lpu_id as \"Lpu_id\"
			FROM
				v_EvnForensicGeneticGenLive EFGGL
			WHERE 
				EFGGL.EvnForensicGeneticGenLive_id = EvnForensicGeneticGenLive_id
		";
        return $this->queryResult($query,$queryParams);

    }
    /**
     * Функция сохранения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
     * @param type $data
     */
    protected function _saveEvnForensicGeneticGenLiveJournal($data) {
        $rules = array(
            array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticGenLive_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticGenLive_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticGenLive_TakeDate', 'label' => 'Дата изъятия образцов', 'rules' => '', 'type' => 'string'),
            array('field' => 'PersonBioSample', 'label' => 'Список исследуемых лиц', 'rules' => '', 'type' => 'array'),
            array('field' => 'BioSampleForMolGenRes', 'label' => 'Список биологических образцов', 'rules' => '', 'type' => 'array'),
            array('field' => 'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
            array('field' => 'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
        );
        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;


        $proc = 'p_EvnForensicGeneticGenLive_ins';
        // Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
        // и удаляем прикреплённые биоматериалы
        if (!empty($queryParams['EvnForensicGeneticGenLive_id'])) {
            $proc = 'p_EvnForensicGeneticGenLive_upd';
            $currentDataResult = $this->getEvnForensicGeneticGenLiveJournal($queryParams);
            if (!$this->isSuccessful($currentDataResult)) {
                return $currentDataResult;
            }
            //Те поля, что не переданы, восполняем из последней сохраненной записи
            foreach ($queryParams as $key => $value) {
                $queryParams = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
            }

            //Если переданы новые вещдоки и потерпевшие/обвиняемые сначала удаляем их из последней сохраненной записи журнала
            if (!empty($queryParams['BioSampleForMolGenRes'])) {
                $deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticGenLive_id']));
                if (!$this->isSuccessful($deleteEvidResult)) {
                    return $deleteEvidResult;
                }
            }
            if (!empty($queryParams['PersonBioSample'])) {
                $deleteEvidResult = $this->_deleteEvnForensicGeneticGenLiveLink(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticGenLive_id']));
                if (!$this->isSuccessful($deleteEvidResult)) {
                    return $deleteEvidResult;
                }
            }
        }

        $query = "
			select
				EvnForensicGeneticGenLive_id as \"EvnForensicGeneticGenLive_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicGeneticGenLive_id := :EvnForensicGeneticGenLive_id,
				EvnForensicGeneticGenLive_pid := :EvnForensicGenetic_id,
				EvnForensicGeneticGenLive_TakeDate :=:EvnForensicGeneticGenLive_TakeDate,
				EvnForensicGeneticGenLive_Facts := :EvnForensicGeneticGenLive_Facts,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id
			);
		";

        $result = $this->queryResult($query, $queryParams);
        if (!$this->isSuccessful($result)) {
            return $result;
        }

        //Сохраняем исследуемые лица
        foreach ($queryParams['PersonBioSample'] as $person) {
            $saveEvnForensicGeneticGenLiveLinkResult = $this->_saveEvnForensicGeneticGenLiveLink(array(
                'EvnForensicGeneticGenLive_id' => $result[0]['EvnForensicGeneticGenLive_id'],
                'Person_id' => $person['Person_id'],
                'pmUser_id' => $queryParams['pmUser_id'],
            ));
            if (!$this->isSuccessful($saveEvnForensicGeneticGenLiveLinkResult)) {
                return $saveEvnForensicGeneticGenLiveLinkResult;
            }
        }

        //Сохраняем биологические образцы
        foreach ($queryParams['BioSampleForMolGenRes'] as $evidence) {
            $saveEvidenceResult = $this->_saveEvidence(array(
                'EvnForensic_id'=>$result[0]['EvnForensicGeneticGenLive_id'],
                'Evidence_Name'=>$evidence['BioSampleForMolGenRes_Name'],
                'EvidenceType_id'=>1,
                'pmUser_id' => $queryParams['pmUser_id'],
            ));
            if (!$this->isSuccessful($saveEvidenceResult)) {
                return $saveEvidenceResult;
            }
        }

        return $result;
    }
    /**
     * Функция сохранения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории
     * @param type $data
     */
    protected function _saveEvnForensicGeneticSampleLiveJournal($data) {
        $rules = array(
            array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticSampleLive_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticSampleLive_TakeDate', 'label' => 'дата и время взятия образцов', 'rules' => '', 'type' => 'string'),
            array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
            array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticSampleLive_Basis', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticSampleLive_VerifyingDoc', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
            array('field' => 'MedPersonal_id', 'label' => 'Идентификатор работника изъявшего', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticSampleLive_IsConsent', 'label' => ' ', 'rules' => '', 'type' => 'int'),
            array('field' => 'EvnForensicGeneticSampleLive_IsIsosTestEA', 'label' => 'Тест-эритроцит А', 'rules' => '', 'type' => 'int'),
            array('field' => 'EvnForensicGeneticSampleLive_IsIsosTestEB', 'label' => 'Тест-эритроцит B', 'rules' => '', 'type' => 'int'),
            array('field' => 'EvnForensicGeneticSampleLive_IsIsosCyclAntiA', 'label' => 'Циклон Анти-A', 'rules' => '', 'type' => 'int'),
            array('field' => 'EvnForensicGeneticSampleLive_IsIsosCyclAntiB', 'label' => 'Циклон Анти-B', 'rules' => '', 'type' => 'int'),
            array('field' => 'EvnForensicGeneticSampleLive_IsosOtherSystems', 'label' => 'Другие системы (изосерология)', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticSampleLive_Result', 'label' => 'Результаты определения групп по исследованым системам', 'rules' => '', 'type' => 'string'),
            array('field' => 'BioSample', 'label' => 'Биологические образцы', 'rules' => '', 'type' => 'array'),
            array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
            array('field'=>'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;


        $proc = 'p_EvnForensicGeneticSampleLive_ins';
        // Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
        // и удаляем прикреплённые биоматериалы
        if (!empty($queryParams['EvnForensicGeneticSampleLive_id'])) {
            $proc = 'p_EvnForensicGeneticSampleLive_upd';
            $currentDataResult = $this->getEvnForensicGeneticSampleLiveJournal($queryParams);
            if (!$this->isSuccessful($currentDataResult)) {
                return $currentDataResult;
            }
            //Те поля, что не переданы, восполняем из последней сохраненной записи
            foreach ($queryParams as $key => $value) {
                $queryParams[$key] = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
            }
            //Если переданы новые вещдоки и потерпевшие/обвиняемые сначала удаляем их из последней сохраненной записи журнала
            if (!empty($queryParams['BioSample'])) {
                $deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticSampleLive_id']));
                if (!$this->isSuccessful($deleteEvidResult)) {
                    return $deleteEvidResult;
                }
            } else {
                $queryParams['BioSample'] = array();
            }
        } else {

            // У вновь создаваемой записи журнала этого типа PersonEvn_id и Server_id обязательны и получаются из Person_zid
            if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
                //Если PersonEvn_id и Server_id не переданы, должен быть передан Person_zid - идентификатор исследуемого лица
                if (empty($queryParams['Person_zid'])) {
                    return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Исследуемое лицо'));
                } else {
                    $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$queryParams['Person_zid']));
                    if (!$this->isSuccessful($personState) || sizeof($personState)==0) {
                        return $this->createError('', 'Ошибка получения идентификатора состояния');
                    } else {
                        $queryParams = array_merge($queryParams,$personState[0]);
                    }
                }
            }
        }
        $query = "
			select
				EvnForensicGeneticSampleLive_id as \"EvnForensicGeneticSampleLive_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicGeneticSampleLive_id := :EvnForensicGeneticSampleLive_id,
				EvnForensicGeneticSampleLive_pid := :EvnForensicGenetic_id,
				EvnForensicGeneticSampleLive_TakeDate:=:EvnForensicGeneticSampleLive_TakeDate,
				EvnForensicGeneticSampleLive_Basis:=:EvnForensicGeneticSampleLive_Basis,
				EvnForensicGeneticSampleLive_VerifyingDoc:=:EvnForensicGeneticSampleLive_VerifyingDoc,
				EvnForensicGeneticSampleLive_IsosOtherSystems:=:EvnForensicGeneticSampleLive_IsosOtherSystems,
				EvnForensicGeneticSampleLive_Result:=:EvnForensicGeneticSampleLive_Result,
				MedPersonal_id:=:MedPersonal_id,
				EvnForensicGeneticSampleLive_IsConsent:=:EvnForensicGeneticSampleLive_IsConsent,
				EvnForensicGeneticSampleLive_IsIsosTestEA:=:EvnForensicGeneticSampleLive_IsIsosTestEA,
				EvnForensicGeneticSampleLive_IsIsosTestEB:=:EvnForensicGeneticSampleLive_IsIsosTestEB,
				EvnForensicGeneticSampleLive_IsIsosCyclAntiA:=:EvnForensicGeneticSampleLive_IsIsosCyclAntiA,
				EvnForensicGeneticSampleLive_IsIsosCyclAntiB:=:EvnForensicGeneticSampleLive_IsIsosCyclAntiB,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id
			);
		";

        //$journalSingleFields = array('EvnForensicGeneticSampleLive_TakeDT','EvnForensicGeneticSampleLive_TakeTime','Person_zid','EvnForensicGeneticSampleLive_Basis');

        $result = $this->queryResult($query,$queryParams);

        if (!$this->isSuccessful($result)) {
            return $result;
        }

        //После сохранения записи в журнале сохраняем все биологические образцы
        foreach ($queryParams['BioSample'] as $evidence) {
            if (!empty($evidence['BioSample_Name'])) {
                $evidence['Evidence_Name'] = $evidence['BioSample_Name'];
            }
            $evidence['EvnForensic_id'] = $result[0]['EvnForensicGeneticSampleLive_id'];
            $evidence['EvidenceType_id'] = 1;
            $evidence['pmUser_id'] = $queryParams['pmUser_id'];

            $saveEvidenceResult = $this->_saveEvidence($evidence);
            if (!$this->isSuccessful($saveEvidenceResult)) {
                return $saveEvidenceResult;
            }
        }

        return $result;
    }
    /**
     * Функция получения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории
     * @param type $data
     */
    protected function getEvnForensicGeneticSampleLiveJournal($data)  {
        $rules = array(
            array('field' => 'EvnForensicGeneticSampleLive_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				EFSL.EvnForensicGeneticSampleLive_pid as \"EvnForensicGenetic_id\",
				EFSL.EvnForensicGeneticSampleLive_id as \"EvnForensicGeneticSampleLive_id\",
				EFSL.EvnForensicGeneticSampleLive_TakeDate as \"EvnForensicGeneticSampleLive_TakeDate\",
				EFSL.EvnForensicGeneticSampleLive_Basis as \"EvnForensicGeneticSampleLive_Basis\",
				EFSL.EvnForensicGeneticSampleLive_Result as \"EvnForensicGeneticSampleLive_Result\",
				EFSL.EvnForensicGeneticSampleLive_VerifyingDoc as \"EvnForensicGeneticSampleLive_VerifyingDoc\",
				EFSL.EvnForensicGeneticSampleLive_IsosOtherSystems as \"EvnForensicGeneticSampleLive_IsosOtherSystems\",
				EFSL.MedPersonal_id as \"MedPersonal_id\",
				EFSL.EvnForensicGeneticSampleLive_IsConsent as \"EvnForensicGeneticSampleLive_IsConsent\",
				EFSL.EvnForensicGeneticSampleLive_IsIsosTestEA as \"EvnForensicGeneticSampleLive_IsIsosTestEA\",
				EFSL.EvnForensicGeneticSampleLive_IsIsosTestEB as \"EvnForensicGeneticSampleLive_IsIsosTestEB\",
				EFSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiA as \"EvnForensicGeneticSampleLive_IsIsosCyclAntiA\",
				EFSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiB as \"EvnForensicGeneticSampleLive_IsIsosCyclAntiB\",
				EFSL.Lpu_id as \"Lpu_id\",
				EFSL.PersonEvn_id as \"PersonEvn_id\",
				EFSL.Server_id as \"Server_id\"
			FROM
				v_EvnForensicGeneticSampleLive EFSL
			WHERE
				EFSL.EvnForensicGeneticSampleLive_id = :EvnForensicGeneticSampleLive_id
		";

        return $this->queryResult($query, $queryParams);
    }

    /**
     * Функция сохранения записи в журнале регистрации вещественных доказательств и документов к ним в лаборатории
     * для службы судебно биологической экспертизы
     * @param type $data
     * @return boolean
     */
    protected function _saveEvnForensicGeneticEvidJournal($data) {

        $rules = array(
            array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticEvid_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticEvid_AccDocNum', 'label' => '№ основного сопроводительного документа', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticEvid_AccDocDate', 'label' => 'Дата основного сопроводительного документа', 'rules' => '', 'type' => 'string'),//string, т.к. уже преобразованов контроллере
            array('field' => 'EvnForensicGeneticEvid_AccDocNumSheets', 'label' => 'Кол-во листов документов', 'rules' => '', 'type' => 'int'),
            array('field' => 'Org_id', 'label' => 'Идентификатор учреждения направившего', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticEvid_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticEvid_Goal', 'label' => 'Цель экспертизы', 'rules' => '', 'type' => 'string'),

            array('field' => 'Person', 'label' => 'Потерпевшие/обвиняемые', 'rules' => '', 'type' => 'array'),// преобразовано из string в array контроллере
            array('field' => 'Evidence', 'label' => 'Вещественные доказательства', 'rules' => '', 'type' => 'array'),

            array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
            array('field'=>'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $proc = 'p_EvnForensicGeneticEvid_ins';
        // Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
        // и удаляем прикреплённые биоматериалы
        if (!empty($queryParams['EvnForensicGeneticEvid_id'])) {
            $proc = 'p_EvnForensicGeneticEvid_upd';
            $currentDataResult = $this->getEvnForensicGeneticEvidJournal($queryParams);
            if (!$this->isSuccessful($currentDataResult)) {
                return $currentDataResult;
            }
            //Те поля, что не переданы, восполняем из последней сохраненной записи
            foreach ($queryParams as $key => $value) {
                $queryParams = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
            }

            //Если переданы новые вещдоки и потерпевшие/обвиняемые сначала удаляем их из последней сохраненной записи журнала
            if (!empty($queryParams['Evidence'])) {
                $deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticEvid_id']));
                if (!$this->isSuccessful($deleteEvidResult)) {
                    return $deleteEvidResult;
                }
            }
            if (!empty($queryParams['Person'])) {
                $deleteEvidResult = $this->_deleteEvnForensicGeneticEvidLink(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticEvid_id']));
                if (!$this->isSuccessful($deleteEvidResult)) {
                    return $deleteEvidResult;
                }
            }
        }

        $query = "
			select
				EvnForensicGeneticEvid_id as \"EvnForensicGeneticEvid_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicGeneticEvid_id := :EvnForensicGeneticEvid_id,
				EvnForensicGeneticEvid_pid := :EvnForensicGenetic_id,
				EvnForensicGeneticEvid_AccDocNum := :EvnForensicGeneticEvid_AccDocNum,
				EvnForensicGeneticEvid_AccDocDate := :EvnForensicGeneticEvid_AccDocDate,
				EvnForensicGeneticEvid_AccDocNumSheets := :EvnForensicGeneticEvid_AccDocNumSheets,
				Org_id := :Org_id,
				EvnForensicGeneticEvid_Facts := :EvnForensicGeneticEvid_Facts,
				EvnForensicGeneticEvid_Goal := :EvnForensicGeneticEvid_Goal,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
		";

        $result = $this->queryResult($query,$queryParams);
        //Сохраняем потерпевших/обвиняемых

        foreach ($queryParams['Person'] as $person) {
            $person['EvnForensicGeneticEvid_id'] = $result[0]['EvnForensicGeneticEvid_id'];
            $person['pmUser_id'] = $queryParams['pmUser_id'];
            $saveEvnForensicGeneticEvidLinkResult = $this->_saveEvnForensicGeneticEvidLink($person);
            if (!$this->isSuccessful($saveEvnForensicGeneticEvidLinkResult)) {
                return $saveEvnForensicGeneticEvidLinkResult;
            }
        }

        //Сохраняем вещественные доказательства
        foreach ($queryParams['Evidence'] as $evidence) {
            $evidence = array_merge($evidence,array(
                'EvnForensic_id'=>$result[0]['EvnForensicGeneticEvid_id'],
                'pmUser_id'=>$queryParams['pmUser_id'],
                'EvidenceType_id'=>2
            ));

            $saveEvidence = $this->_saveEvidence($evidence);
            if (!$this->isSuccessful($saveEvidence)) {
                return $saveEvidence;
            }
        }

        return $result;
    }
    /**
     * Функция сохранения записи в журнале регистрации вещественных доказательств и документов к ним в лаборатории
     * для службы судебно биологической экспертизы
     * @param type $data
     */
    protected function getEvnForensicGeneticEvidJournal($data) {
        $rules = array(
            array('field' => 'EvnForensicGeneticEvid_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				EFGE.EvnForensicGenetic_id as \"EvnForensicGenetic_id\",
				EFGE.EvnForensicGeneticEvid_id as \"EvnForensicGeneticEvid_id\",
				
				EFGE.EvnForensicGeneticEvid_AccDocDate as \"EvnForensicGeneticEvid_AccDocDate\",--дата основного сопроводительного документа
				EFGE.EvnForensicGeneticEvid_AccDocNum as \"EvnForensicGeneticEvid_AccDocNum\",--номер основного сопроводительного документа
				EFGE.EvnForensicGeneticEvid_AccDocNumSheets as \"EvnForensicGeneticEvid_AccDocNumSheets\",--количество  листов документов
				EFGE.Org_id as \"Org_id\",--учреждение направившего
				EFGE.EvnForensicGeneticEvid_Facts as \"EvnForensicGeneticEvid_Facts\",--Кратко обстоятельства дела
				EFGE.EvnForensicGeneticEvid_Goal as \"EvnForensicGeneticEvid_Goal\",--цель экспертизы

				EFGE.Lpu_id as \"Lpu_id\"
			FROM
				v_EvnForensicGeneticEvid EFGE
			WHERE
				EFGE.EvnForensicGeneticEvid_id = :EvnForensicGeneticEvid_id
		";

        return $this->queryResult($query, $queryParams);
    }

    /**
     * Получение списка заявок службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicGeneticList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        if (empty($data['MedService_id'])) {
            if (empty($data['session']['CurMedService_id'])) {
                return $this->createError('','Не задан обязательный параметр: Идентификатор службы');
            } else {
                $data['MedService_id'] = $data['session']['CurMedService_id'];
            }
        }

        if (empty($data['MedPersonal_id']) && !empty($data['session']['medpersonal_id'])) {
            $data['MedPersonal_id'] = $data['session']['medpersonal_id'];
        }

        $rules = array(
            array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
            array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
            array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
            array('field'=>'ARMType', 'label'=>'Тип АРМ','rules' => '', 'type' => 'string'),
            array('field'=>'MedPersonal_id', 'label'=>'Идентификатор эксперта','rules' => '', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $where = array(
            'ISNULL(EFG.EvnClass_id,0)=122',
            'EFG.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        }

        if ($queryParams['ARMType'] == 'expert') {
            if (empty($queryParams['MedPersonal_id'])) {
                return $this->createError('','Не задан обязательны параметр: Идентификатор эксперта');
            }
            $where[] = "EDF.MedPersonal_id=:MedPersonal_id";
        }

        $query = "
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as \"EvnForensic_Num\",
				EFG.EvnForensicGenetic_id as \"EvnForensic_id\",
				to_char(EFG.EvnForensicGenetic_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				'Группа лиц' as \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicGenetic EFG
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFG.EvnForensicGenetic_id
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF on true
				LEFT JOIN LATERAL (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP on true
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFG.EvnStatus_id )
			-- end from
			WHERE 
			-- where
				".implode( " AND ", $where )."
			-- end where	
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
		";

        $result = array();

        $count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $result['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
        if (!$this->isSuccessful($data_result)) {
            return $data_result;
        } else {
            $result['data']=$data_result;
        }

        return $result;
    }
    /**
     * Получение списка заявок журнала регистрации вещественных доказательств и документов к ним в лаборатории в службе СБЭ
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicGeneticEvidList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        $rules = array(
            array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
            array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
            array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $where = array(
            'EFG.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        }

        $query = "
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as \"EvnForensic_Num\", 
				EFG.EvnForensicGenetic_id as \"EvnForensic_id\",
				to_char(EFGE.EvnForensicGeneticEvid_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",				
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				CASE WHEN (PEC.PersonEvidCount>1) THEN 'Группа лиц' ELSE
					CASE WHEN (PEC.PersonEvidCount=1) THEN PersonEvid.Person_Fio 
					ELSE 'Лицо отсутствует' END
				END as \"Person_Fio\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticEvid EFGE
				left join v_EvnForensicGenetic EFG on EFG.EvnForensicGenetic_id = EFGE.EvnForensicGeneticEvid_pid
				left join lateral (
					SELECT 
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF on true
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFGE.EvnForensicGeneticEvid_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				LEFT JOIN LATERAL (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP  on true
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN LATERAL (
					SELECT
						COUNT(EFGEL.Person_id) as PersonEvidCount
					FROM
						v_EvnForensicGeneticEvidLink EFGEL
					WHERE
						EFGEL.EvnForensicGeneticEvid_id = EFGE.EvnForensicGeneticEvid_id
				) as PEC on true
				LEFT JOIN LATERAL (
					SELECT
						
						EFGEL.Person_id,
						COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS Person_Fio
					FROM
						v_EvnForensicGeneticEvidLink EFGEL
						left join v_Person_all P on EFGEL.Person_id = P.Person_id
					WHERE
						EFGEL.EvnForensicGeneticEvid_id = EFGE.EvnForensicGeneticEvid_id
					LIMIT 1
				) as PersonEvid on true
			-- end from
			WHERE
			-- where
				 ".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
		";

        $result = array();

        $count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $result['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
        if (!$this->isSuccessful($data_result)) {
            return $data_result;
        } else {
            $result['data']=$data_result;
        }

        return $result;
    }
    /**
     * Получение списка заявок журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicGeneticSampleLiveList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        $rules = array(
            array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
            array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
            array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $where = array(
            'EFG.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        }

        $query = "
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as \"EvnForensic_Num\",
				EFG.EvnForensicGenetic_id as \"EvnForensic_id\",
				to_char(EFGSL.EvnForensicGeneticSampleLive_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\" 
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticSampleLive EFGSL
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFGSL.EvnForensicGeneticSampleLive_id
				left join v_EvnForensicGenetic EFG on EFG.EvnForensicGenetic_id = EFGSL.EvnForensicGeneticSampleLive_pid
				LEFT JOIN LATERAL (
					SELECT 
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF ON TRUE
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				LEFT JOIN LATERAL (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP ON TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_Person_all P on EFGSL.PersonEvn_id = P.PersonEvn_id and EFGSL.Server_id = P.Server_id
			-- end from
			WHERE
			-- where
				 ".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
		";

        $result = array();

        $count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $result['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
        if (!$this->isSuccessful($data_result)) {
            return $data_result;
        } else {
            $result['data']=$data_result;
        }

        return $result;
    }
    /**
     * Получение списка заявок журнала регистрации трупной крови в лаборатории
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicGeneticCadBloodList($data) {
        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        $rules = array(
            array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
            array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
            array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $where = array(
            'EFG.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        }

        $query = "
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as \"EvnForensic_Num\",
				EFG.EvnForensicGenetic_id as \"EvnForensic_id\",
				to_char(EFGSL.EvnForensicGeneticCadBlood_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id \"
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticCadBlood EFGSL
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFGSL.EvnForensicGeneticCadBlood_id
				left join v_EvnForensicGenetic EFG on EFG.EvnForensicGenetic_id = EFGSL.EvnForensicGeneticCadBlood_pid
				left join lateral (
					SELECT 
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF  on true
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				LEFT JOIN LATERAL (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP on true
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_Person_all P on EFGSL.PersonEvn_id = P.PersonEvn_id AND EFGSL.Server_id = P.Server_id
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
		";

        $result = array();

        $count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $result['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
        if (!$this->isSuccessful($data_result)) {
            return $data_result;
        } else {
            $result['data']=$data_result;
        }

        return $result;
    }

    /**
     * Получение списка заявок журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicGeneticGenLiveList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        $rules = array(
            array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
            array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
            array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $where = array(
            'EFG.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        }

        $query = "
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as \"EvnForensic_Num\",
				EFG.EvnForensicGenetic_id as \"EvnForensic_id\",
				to_char(EFGGL.EvnForensicGeneticGenLive_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				CASE WHEN (PEC.PersonEvidCount>1) THEN 'Группа лиц' ELSE
					CASE WHEN (PEC.PersonEvidCount=1) THEN Person.Person_Fio 
					ELSE 'Лицо отсутствует' END
				END as \"Person_Fio\"
				
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticGenLive EFGGL 
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFGGL.EvnForensicGeneticGenLive_id
				left join lateral (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP on true
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_EvnForensicGenetic EFG on EFG.EvnForensicGenetic_id = EFGGL.EvnForensicGeneticGenLive_pid
				left join lateral (
					SELECT 
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF 
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF on true
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				LEFT JOIN LATERAL (
					SELECT
						COUNT(EFGGLL.Person_id) as PersonEvidCount
					FROM
						v_EvnForensicGeneticGenLiveLink EFGGLL
					WHERE
						EFGGLL.EvnForensicGeneticGenLive_id = EFGGL.EvnForensicGeneticGenLive_id
				) as PEC on true
				LEFT JOIN LATERAL (
					SELECT 
						EFGGLL.Person_id,
						COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS Person_Fio
					FROM
						v_EvnForensicGeneticGenLiveLink EFGGLL
						left join v_Person_all P  on EFGGLL.Person_id = P.Person_id
					WHERE
						EFGGLL.EvnForensicGeneticGenLive_id = EFGGL.EvnForensicGeneticGenLive_id
				) as Person on true
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
		";

        $result = array();

        $count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $result['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
        if (!$this->isSuccessful($data_result)) {
            return $data_result;
        } else {
            $result['data']=$data_result;
        }

        return $result;
    }
    /**
     * Получение списка заявок журнала регистрации исследований мазков и тампонов в лаборатории
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicGeneticSmeSwabList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        $rules = array(
            array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
            array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
            array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $where = array(
            'EFG.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        }

        $query = "
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as \"EvnForensic_Num\",
				EFG.EvnForensicGenetic_id as \"EvnForensic_id\",
				to_char(EFGSS.EvnForensicGeneticSmeSwab_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE (' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id \"
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticSmeSwab EFGSS
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFGSS.EvnForensicGeneticSmeSwab_id
				LEFT JOIN LATERAL (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    limit 1
                ) as MP on true
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_Person_all P on EFGSS.PersonEvn_id = P.PersonEvn_id and EFGSS.Server_id = P.Server_id
				left join v_EvnForensicGenetic EFG on EFG.EvnForensicGenetic_id = EFGSS.EvnForensicGeneticSmeSwab_pid
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF 
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					limit 1
				) as AVF on true
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFG.EvnStatus_id )
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
		";

        $result = array();

        $count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $result['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
        if (!$this->isSuccessful($data_result)) {
            return $data_result;
        } else {
            $result['data']=$data_result;
        }

        return $result;
    }

    /**
     * Сохранение экспертизы в судебно-биологическом отделении с молекулярно-генетической лабораторией
     * @param $data
     * @return bool
     */
    public function saveEvnForensicGeneticExpertiseProtocol($data) {
        /**
         * Обработка значений чекбоксов
         */
        function processCheckboxValues(&$params, $checkbox_list) {
            foreach($checkbox_list as $field) {
                if ($params[$field] === 0) {
                    $params[$field] = 1;
                } else if ($params[$field] === 1) {
                    $params[$field] = 2;
                } else {
                    $params[$field] = null;
                }
            }
        };

        $data['EvnForensicGenetic_id'] = $data['EvnForensic_id'];

        $this->db->trans_begin();

        if (!empty($data['EvnForensicGeneticCadBlood_id']) && $data['EvnForensicGeneticCadBlood_id'] > 0) {
            processCheckboxValues($data, array(
                'EvnForensicGeneticCadBlood_IsIsosTestEA',
                'EvnForensicGeneticCadBlood_IsIsosTestEB',
                'EvnForensicGeneticCadBlood_IsIsosTestIsoB',
                'EvnForensicGeneticCadBlood_IsIsosTestIsoA',
                'EvnForensicGeneticCadBlood_IsIsosAntiA',
                'EvnForensicGeneticCadBlood_IsIsosAntiB',
                'EvnForensicGeneticCadBlood_IsIsosAntiH',
            ));
            $saveCadBloodResult = $this->saveEvnForensicGeneticCadBlood($data);
            if (!$this->isSuccessful($saveCadBloodResult)) {
                $this->db->trans_rollback();
                return $saveCadBloodResult;
            }
        }
        if (!empty($data['EvnForensicGeneticSampleLive_id']) && $data['EvnForensicGeneticSampleLive_id'] > 0) {
            processCheckboxValues($data, array(
                'EvnForensicGeneticSampleLive_IsIsosTestEA',
                'EvnForensicGeneticSampleLive_IsIsosTestEB',
                'EvnForensicGeneticSampleLive_IsIsosCyclAntiA',
                'EvnForensicGeneticSampleLive_IsIsosCyclAntiB',
            ));
            $saveSampleLiveResult = $this->_saveEvnForensicGeneticSampleLiveJournal($data);
            if (!$this->isSuccessful($saveSampleLiveResult)) {
                $this->db->trans_rollback();
                return $saveSampleLiveResult;
            }
        }

        $saveActVersionForensicResult = $this->_saveActVersionForensic($data);
        if (!$this->isSuccessful($saveActVersionForensicResult)) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }

        return $saveActVersionForensicResult;
    }

    /**
     * Функция получения записи журнала регистрации трупной крови
     * @param type $data
     * @return type
     */
    protected function getEvnForensicGeneticCadBloodJournal($data) {
        $rules = array(
            array('field' => 'EvnForensicGeneticCadBlood_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				EFGCB.EvnForensicGeneticCadBlood_id as \"EvnForensicGeneticCadBlood_id\",
				EFGCB.EvnForensicGeneticCadBlood_pid as \"EvnForensicGeneticCadBlood_pid\",
				EFGCB.EvnForensicGeneticCadBlood_rid as \"EvnForensicGeneticCadBlood_rid\",
				EFGCB.MedPersonal_id as \"MedPersonal_id\",
				EFGCB.EvnForensicGeneticCadBlood_TakeDate as \"EvnForensicGeneticCadBlood_TakeDate\",
				EFGCB.EvnForensicGeneticCadBlood_ForDate as \"EvnForensicGeneticCadBlood_ForDate\",
				EFGCB.EvnForensicGeneticCadBlood_StudyDate as \"EvnForensicGeneticCadBlood_StudyDate\",
				EFGCB.EvnForensicGeneticCadBlood_Result as \"EvnForensicGeneticCadBlood_Result\",
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEA as \"EvnForensicGeneticCadBlood_IsIsosTestEA\",
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEB as \"EvnForensicGeneticCadBlood_IsIsosTestEB\",
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoB as \"EvnForensicGeneticCadBlood_IsIsosTestIsoB\",
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoAsas \"EvnForensicGeneticCadBlood_IsIsosTestIsoA\",
				EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiA as \"EvnForensicGeneticCadBlood_IsIsosAntiA\",
				EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiB as \"EvnForensicGeneticCadBlood_IsIsosAntiB\",
				EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiH as \"EvnForensicGeneticCadBlood_IsIsosAntiH\",
				EFGCB.EvnForensicGeneticCadBlood_MatCondition as \"EvnForensicGeneticCadBlood_MatCondition\",
				EFGCB.EvnForensicGeneticCadBlood_IsosOtherSystems as \"EvnForensicGeneticCadBlood_IsosOtherSystems\",
				EFGCB.Lpu_id as \"Lpu_id\",
				EFGCB.PersonEvn_id as \"PersonEvn_id\",
				EFGCB.Server_id as \"Server_id\"
			FROM
				v_EvnForensicGeneticCadBlood EFGCB
			WHERE
				EFGCB.EvnForensicGeneticCadBlood_id = :EvnForensicGeneticCadBlood_id
		";

        return $this->queryResult($query, $queryParams);
    }

    /**
     * Сохранение записи в журнале регистрации трупной крови
     * @param type $data
     */
    public function saveEvnForensicGeneticCadBlood($data) {

        if (empty($data['MedPersonal_id']) || empty($data['EvnForensicGeneticCadBlood_id'])) {
            if (empty($data['session']['medpersonal_id'])) {
                return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор назначившего эксперта'));
            } else {
                $data['MedPersonal_id'] = $data['session']['medpersonal_id'];
            }
        }

        $rules = array(
            array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор родительской заявки', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
            array('field' => 'Evn_rid', 'label'=>'Идентификатор получателя документа', 'rules' => '',  'type' => 'id'),
            array('field' => 'MedPersonal_id', 'label' => 'Cотрудник назначивший экспертизу', 'rules' => '', 'type' => 'id'),
            array('field' => 'ReasearchedPerson_id', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
            //array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
            //array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_TakeDate', 'label' => 'Дата взятия', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticCadBlood_ForDate', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticCadBlood_StudyDate', 'label' => 'Дата исследования', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticCadBlood_Result', 'label' => 'Результат определения групп по исследованным системам', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestEA', 'label' => 'Тест-эритроцит А', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestEB', 'label' => '', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestIsoB', 'label' => 'Изосыворотка бетта', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestIsoA', 'label' => 'Изосыворотка альфа', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiA', 'label' => 'Имунная сыворотка Анти-А', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiB', 'label' => 'Имунная сыворотка Анти-B', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiH', 'label' => 'Имунная сыворотка Анти-H', 'rules' => '', 'type' => 'id'),

            array('field' => 'EvnForensicGeneticCadBlood_MatCondition', 'label' => 'Упаковка, состояние, количество материала', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicGeneticCadBlood_IsosOtherSystems', 'label' => 'Другие системы (изосерология)', 'rules' => '', 'type' => 'string'),

            array('field'=>'Lpu_id','rules' =>'required', 'label'=>'Идентификатор МО', 'type' => 'id'),
            array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );


        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $proc = 'p_EvnForensicGeneticCadBlood_ins';
        if (!empty($queryParams['EvnForensicGeneticCadBlood_id'])) {
            $proc = 'p_EvnForensicGeneticCadBlood_upd';
            $currentDataResult = $this->getEvnForensicGeneticCadBloodJournal($queryParams);
            if (!$this->isSuccessful($currentDataResult)) {
                return $currentDataResult;
            }
            //Те поля, что не переданы, восполняем из последней сохраненной записи
            foreach ($queryParams as $key => $value) {
                $queryParams[$key] = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
            }
        } else {

            if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
                //Если PersonEvn_id и Server_id не переданы, должен быть передан ReasearchedPerson_id - идентификатор исследуемого лица
                if (empty($queryParams['ReasearchedPerson_id'])) {
                    return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Исследуемое лицо'));
                } else {
                    $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$queryParams['ReasearchedPerson_id']));
                    if (!$this->isSuccessful($personState) || sizeof($personState)==0) {
                        return $this->createError('', 'Ошибка получения идентификатора состояния');
                    } else {
                        $queryParams = array_merge($queryParams,$personState[0]);
                    }
                }
            }

        }

        $query = "
			select
				EvnForensicGeneticCadBlood_id as \"EvnForensicGeneticCadBlood_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicGeneticCadBlood_id := :EvnForensicGeneticCadBlood_id,
				EvnForensicGeneticCadBlood_pid := :EvnForensicGenetic_id,
				EvnForensicGeneticCadBlood_rid := :Evn_rid,
				MedPersonal_id:=:MedPersonal_id,
				EvnForensicGeneticCadBlood_MatCondition :=:EvnForensicGeneticCadBlood_MatCondition,
				EvnForensicGeneticCadBlood_IsosOtherSystems :=:EvnForensicGeneticCadBlood_IsosOtherSystems,
				EvnForensicGeneticCadBlood_TakeDate:=:EvnForensicGeneticCadBlood_TakeDate,
				EvnForensicGeneticCadBlood_StudyDate:=:EvnForensicGeneticCadBlood_StudyDate,
				EvnForensicGeneticCadBlood_ForDate:=:EvnForensicGeneticCadBlood_ForDate,
				EvnForensicGeneticCadBlood_Result:= :EvnForensicGeneticCadBlood_Result,
				EvnForensicGeneticCadBlood_IsIsosTestEA:=:EvnForensicGeneticCadBlood_IsIsosTestEA,
				EvnForensicGeneticCadBlood_IsIsosTestEB:=:EvnForensicGeneticCadBlood_IsIsosTestEB,
				EvnForensicGeneticCadBlood_IsIsosTestIsoB:=:EvnForensicGeneticCadBlood_IsIsosTestIsoB,
				EvnForensicGeneticCadBlood_IsIsosTestIsoA:=:EvnForensicGeneticCadBlood_IsIsosTestIsoA,
				EvnForensicGeneticCadBlood_IsIsosAntiA:=:EvnForensicGeneticCadBlood_IsIsosAntiA,
				EvnForensicGeneticCadBlood_IsIsosAntiB:=:EvnForensicGeneticCadBlood_IsIsosAntiB,
				EvnForensicGeneticCadBlood_IsIsosAntiH:=:EvnForensicGeneticCadBlood_IsIsosAntiH,
				Lpu_id := :Lpu_id,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query, $queryParams);
    }

    /**
     * Функция получения заявки службы судебно-биологической экспертизы с молекулярно-генетической лабораторией для просмотра
     * @return boolean
     */
    public function getEvnForensicGeneticRequest($data) {
        $rules = array(
            array('field' => 'EvnForensic_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
        );
        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				-- Общий блок

				EFG.EvnForensicGenetic_Num as \"EvnForensic_Num\",
				EFG.EvnForensicGenetic_id as \"EvnForensic_id\",
				COALESCE(EDF.EvnDirectionForensic_id,0) as \"EvnDirectionForensic_id\",
				to_char(EFG.EvnForensicGenetic_insDT, 'dd.mm.yyyy') as \"EvnForensic_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",

				-- Журнал регистрации вещественных доказательств
				COALESCE(EFGE.EvnForensicGeneticEvid_id,0) as \"EvnForensicGeneticEvid_id\",
				EFGE.EvnForensicGeneticEvid_AccDocNum as \"EvnForensicGeneticEvid_AccDocNum\",
				to_char(EFGE.EvnForensicGeneticEvid_AccDocDate, 'dd.mm.yyyy') as \"EvnForensicGeneticEvid_AccDocDate\",
				EFGE.EvnForensicGeneticEvid_AccDocNumSheets as \"EvnForensicGeneticEvid_AccDocNumSheets\",
				O.Org_Name as \"Org_Name\",
				EFGE.EvnForensicGeneticEvid_Facts as \"EvnForensicGeneticEvid_Facts\",
				EFGE.EvnForensicGeneticEvid_Goal as \"EvnForensicGeneticEvid_Goal\",

				-- Журнал регистрации биообразцов
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_id,0) as \"EvnForensicGeneticSampleLive_id\",
				to_char(EFGSL.EvnForensicGeneticSampleLive_TakeDate, 'dd.mm.yyyy') || ' ' || to_char(EFGSL.EvnForensicGeneticSampleLive_TakeDate, 'hh24:mi') as \"EvnForensicGeneticSampleLive_TakeDate\",
				EFGSL_P.Person_Fio as \"EvnForensicGeneticSampleLive_Person_FIO\",
				EFGSL.EvnForensicGeneticSampleLive_Basis as \"EvnForensicGeneticSampleLive_Basis\",
				EFGSL_MP.Person_Fin as \"EvnForensicGeneticSampleLive_MedPersonal_Fin\",--фИО работника изъявшего
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_IsConsent,0) as \"EvnForensicGeneticSampleLive_IsConsent\",--согласие
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_IsIsosTestEA-1,0) as \"EvnForensicGeneticSampleLive_IsIsosTestEA\",--Тест-эритроцит А
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_IsIsosTestEB-1,0) as \"EvnForensicGeneticSampleLive_IsIsosTestEB\",--Тест-эритроцит B
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiA-1,0) as \"EvnForensicGeneticSampleLive_IsIsosCyclAntiA\",--Циклон Анти-А
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiB-1,0) as \"EvnFoEvnForensicGeneticSampleLive_ResultrensicGeneticSampleLive_IsIsosCyclAntiB\",--Циклон Анти-B
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_VerifyingDoc,'') as \"EvnForensicGeneticSampleLive_VerifyingDoc\",
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_IsosOtherSystems,'') as \"EvnForensicGeneticSampleLive_IsosOtherSystems\",
				COALESCE(EFGSL.EvnForensicGeneticSampleLive_Result,'') as \"EvnForensicGeneticSampleLive_Result\",

				-- Журнал регистрации биообразцов для мол.ген. иссл
				COALESCE(EFGGL.EvnForensicGeneticGenLive_id,0) as \"EvnForensicGeneticGenLive_id\" ,
				to_char(EFGGL.EvnForensicGeneticGenLive_TakeDate, 'dd.mm.yyyy') as \"EvnForensicGeneticGenLive_TakeDate\",
				EFGGL.EvnForensicGeneticGenLive_Facts as \"EvnForensicGeneticGenLive_Facts\",
				EFGGL_MP.Person_Fin as \"EvnForensicGeneticGenLive_MedPersonal_Fin\",

				--Журнал регистрации трупной крови в лаборатории
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_id,0) as \"EvnForensicGeneticCadBlood_id\" ,
				EFGCB_P.Person_Fio as \"EvnForensicGeneticCadBlood_Person_Fin\", --исследуемое лицо
				EFGCB_MP.Person_Fin as \"EvnForensicGeneticCadBlood_MedPersonal_Fin\", --EFGCB.MedPersonal_id,--фио эксперта направившего
				to_char(EFGCB.EvnForensicGeneticCadBlood_ForDate, 'dd.mm.yyyy') as \"EvnForensicGeneticCadBlood_ForDate\",--Дата поступления
				to_char(EFGCB.EvnForensicGeneticCadBlood_TakeDate, 'dd.mm.yyyy') as \"EvnForensicGeneticCadBlood_TakeDate\",--дата взятия
				to_char(EFGCB.EvnForensicGeneticCadBlood_StudyDate, 'dd.mm.yyyy') as \"EvnForensicGeneticCadBlood_StudyDate\",--дата исследования
				EFGCB.EvnForensicGeneticCadBlood_Result as \"EvnForensicGeneticCadBlood_Result\",--Результат определения групп по исследованным системам
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEA-1,0) as \"EvnForensicGeneticCadBlood_IsIsosTestEA\",--Тест-эритроцит А
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEB-1,0) as \"EvnForensicGeneticCadBlood_IsIsosTestEB\",--Тест-эритроцит B
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoB-1,0) as \"EvnForensicGeneticCadBlood_IsIsosTestIsoB\",--Изосыворотка бетта
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoA-1,0) as \"EvnForensicGeneticCadBlood_IsIsosTestIsoA\",--Изосыворотка альфа
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiA-1,0) as \"EvnForensicGeneticCadBlood_IsIsosAntiA\",--Имунная сыворотка Анти-А
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiB-1,0) as \"EvnForensicGeneticCadBlood_IsIsosAntiB\",--Имунная сыворотка Анти-B
				COALESCE(EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiH-1,0) as \"EvnForensicGeneticCadBlood_IsIsosAntiH\",--Имунная сыворотка Анти-H
				EFGCB.EvnForensicGeneticCadBlood_MatCondition as \"EvnForensicGeneticCadBlood_MatCondition\", --Упаковка, состояние, количество материала
				EFGCB.EvnForensicGeneticCadBlood_IsosOtherSystems as \"EvnForensicGeneticCadBlood_IsosOtherSystems\", --Другие системы (изосерология)

				
				-- Журнал регистрации мазков и тампонов
				ISNULL(EFGSS.EvnForensicGeneticSmeSwab_id,0) as \"EvnForensicGeneticSmeSwab_id\" ,
				EFGSS_P.Person_Fio as \"EvnForensicGeneticSmeSwab_Person_Fio\",
				EFGSS.EvnForensicGeneticSmeSwab_Basis as \"EvnForensicGeneticSmeSwab_Basis\",
				to_char(EFGSS.EvnForensicGeneticSmeSwab_DelivDate, 'dd.mm.yyyy') || ' ' || to_char(EFGSS.EvnForensicGeneticSmeSwab_DelivDate, 'hh24:mi') as \"EvnForensicGeneticSmeSwab_DelivDate\",
				to_char(EFGSS.EvnForensicGeneticSmeSwab_BegDate, 'dd.mm.yyyy') as \"EvnForensicGeneticSmeSwab_BegDate\",--дата начала исследования
				to_char(EFGSS.EvnForensicGeneticSmeSwab_EndDate, 'dd.mm.yyyy') as \"EvnForensicGeneticSmeSwab_EndDate\",--дата окончания исследования
				EFGSS.EvnForensicGeneticSmeSwab_Comment as \"EvnForensicGeneticSmeSwab_Comment\", --примечание
								
				-- Комментарий, если есть
				COALESCE(ESH.EvnStatusHistory_Cause,'') as \"EvnStatusHistory_Cause\",
				
				-- Акт экспертизы
				COALESCE(AVF.ActVersionForensic_id,0) as \"ActVersionForensic_id\" ,
				AVF.ActVersionForensic_Text as \"ActVersionForensic_Text\", 
				AVF.ActVersionForensic_Num as \"ActVersionForensic_Num\",
				to_char(AVF.ActVersionForensic_FactBegDT, 'dd.mm.yyyy') as \"ActVersionForensic_FactBegDT\",
				to_char(AVF.ActVersionForensic_FactEndDT, 'dd.mm.yyyy') as \"ActVersionForensic_FactEndDT\"

			FROM
				v_EvnForensicGenetic EFG
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFG.EvnForensicGenetic_id
				LEFT JOIN LATERAL (
					SELECT
						MP.Person_Fin as Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EDF.MedPersonal_id
					LIMIT 1
				) as MP ON TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_EvnForensicGeneticEvid EFGE on EFGE.EvnForensicGeneticEvid_pid = EFG.EvnForensicGenetic_id
				left join v_Org O on EFGE.Org_id = O.Org_id
				left join v_EvnForensicGeneticCadBlood EFGCB on EFGCB.EvnForensicGeneticCadBlood_pid = EFG.EvnForensicGenetic_id
				left join v_Person_all EFGCB_P on EFGCB.PersonEvn_id = EFGCB_P.PersonEvn_id AND EFGCB.Server_id = EFGCB_P.Server_id
				LEFT JOIN LATERAL (
					SELECT 
						MP.Person_Fin
					FROM
						v_MedPersonal MP 
					WHERE
						MP.MedPersonal_id = EFGCB.MedPersonal_id
					LIMIT 1
				) as EFGCB_MP ON TRUE
				left join v_EvnForensicGeneticGenLive  EFGGL on EFGGL.EvnForensicGeneticGenLive_pid = EFG.EvnForensicGenetic_id
				LEFT JOIN LATERAL (
					SELECT 
						MP.Person_Fin
					FROM
						v_MedPersonal MP 
					WHERE
						MP.MedPersonal_id = EFGGL.MedPersonal_id
					LIMIT 1
				) as EFGGL_MP ON TRUE
				left join v_EvnForensicGeneticSampleLive  EFGSL on EFGSL.EvnForensicGeneticSampleLive_pid = EFG.EvnForensicGenetic_id
				LEFT JOIN LATERAL (
					SELECT
						MP.Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EFGSL.MedPersonal_id
					LIMIT 1
				) as EFGSL_MP ON TRUE
				left join v_Person_all EFGSL_P on EFGSL.PersonEvn_id = EFGSL_P.PersonEvn_id AND EFGSL.Server_id = EFGSL_P.Server_id
				left join v_EvnForensicGeneticSmeSwab  EFGSS on EFGSS.EvnForensicGeneticSmeSwab_pid = EFG.EvnForensicGenetic_id
				left join v_Person_all EFGSS_P on EFGSS.PersonEvn_id = EFGSS_P.PersonEvn_id AND EFGSS.Server_id = EFGSS_P.Server_id
				LEFT JOIN LATERAL (
					SELECT 
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text,
						AVF.ActVersionForensic_FactBegDT,
						AVF.ActVersionForensic_FactEndDT
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF ON TRUE
				LEFT JOIN LATERAL (
					SELECT 
						ESH.EvnStatusHistory_Cause
					FROM
						v_EvnStatusHistory ESH
					WHERE
						ESH.Evn_id = EFG.EvnForensicGenetic_id
					ORDER BY
						ESH.EvnStatusHistory_insDT DESC
					LIMIT 1
				) as ESH ON TRUE
			WHERE 
				EFG.EvnForensicGenetic_id = :EvnForensic_id
		";
        //echo getDebugSQL($query, $queryParams);exit;
        $result = $this->queryResult($query, $queryParams);
        if (!$this->isSuccessful($result)) {
            return $result;
        }


        if (!empty($result[0])) {
            if (!empty($result[0]['EvnForensicGeneticEvid_id'])) {
                //
                // Получаем список потерпевших/обвиняемых
                //
                $evid_link_result = $this->getEvnForensicGeneticEvidLinkList(array(
                    'EvnForensicGeneticEvid_id' => $result[0]['EvnForensicGeneticEvid_id']
                ));

                if (!$this->isSuccessful($evid_link_result)) {
                    return $evid_link_result;
                } else {
                    $result[0]['EvnForensicGeneticEvidLink'] = $evid_link_result;
                }
                //
                // Получаем список вещдоков
                //
                $evidence_result = $this->getEvidenceList(array(
                    'EvnForensic_id' => $result[0]['EvnForensicGeneticEvid_id']
                ));

                if (!$this->isSuccessful($evidence_result)) {
                    return $evidence_result;
                } else {
                    $result[0]['EvnForensicGeneticEvid_Evidence'] = $evidence_result;
                }
            }
            if (!empty($result[0]['EvnForensicGeneticSampleLive_id'])) {
                //
                // Получаем список биологических образцов
                //
                $evidence_result = $this->getEvidenceList(array(
                    'EvnForensic_id' => $result[0]['EvnForensicGeneticSampleLive_id']
                ));
                if (!$this->isSuccessful($evidence_result)) {
                    return $evidence_result;
                } else {
                    $result[0]['EvnForensicGeneticSampleLive_BioSample'] = $evidence_result;
                }
            }
            if (!empty($result[0]['EvnForensicGeneticGenLive_id'])) {
                //
                // Получаем список исследуемых лиц
                //
                $genetic_gen_live_result = $this->getEvnForensicGeneticGenLiveLinkList(array(
                    'EvnForensicGeneticGenLive_id' => $result[0]['EvnForensicGeneticGenLive_id']
                ));
                if (!$this->isSuccessful($genetic_gen_live_result)) {
                    return $genetic_gen_live_result;
                } else {
                    $result[0]['EvnForensicGeneticGenLiveLink'] = $genetic_gen_live_result;
                }
                //
                // Получаем список биологических образцов
                //
                $biosample_result = $this->getEvidenceList(array(
                    'EvnForensic_id' => $result[0]['EvnForensicGeneticGenLive_id']
                ));
                if (!$this->isSuccessful($biosample_result)) {
                    return $biosample_result;
                } else {
                    $result[0]['EvnForensicGeneticGenLive_BioSample'] = $biosample_result;
                }
            }
            if (!empty($result[0]['EvnForensicGeneticSmeSwab_id'])) {
                //
                // Получаем список образцов
                //
                $biosample_result = $this->getEvidenceList(array(
                    'EvnForensic_id' => $result[0]['EvnForensicGeneticSmeSwab_id']
                ));
                if (!$this->isSuccessful($biosample_result)) {
                    return $biosample_result;
                } else {
                    $result[0]['EvnForensicGeneticSmeSwab_Sample'] = $biosample_result;
                }
            }

        }
        if (!empty($result[0])) {
            $result[0]['success']=true;
        }
        return $result;
    }
    /**
     * Функция получения списка потерпевших/обвиняемых для журнала регистрации вещественных доказательств и документов к нимы
     * службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
     * @param type $data
     * @return boolean
     */
    protected function getEvnForensicGeneticEvidLinkList($data) {
        if (empty($data['EvnForensicGeneticEvid_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор случая'));
        }

        $query = "
			SELECT
				EFGEL.EvnForensicGeneticEvidLink_id as \"EvnForensicGeneticEvidLink_id\",
				EFGEL.Person_id as \"Person_id\",
				EFGEL.EvnForensicGeneticEvidLink_IsVic as \"EvnForensicGeneticEvidLink_IsVic\",
				EFGEL_P.Person_Fio as \"Person_Fio\"
			FROM 
				v_EvnForensicGeneticEvidLink EFGEL
				LEFT JOIN LATERAL (
					SELECT
					P.Person_Fio
					FROM
						v_Person_all P
					WHERE P.Person_id = EFGEL.Person_id
					ORDER BY P.PersonEvn_id ASC
					LIMIT 1
				) As EFGEL_P ON TRUE
			WHERE
				EFGEL.EvnForensicGeneticEvid_id = :EvnForensicGeneticEvid_id
		";
        $result = $this->db->query($query,array(
            'EvnForensicGeneticEvid_id' => $data['EvnForensicGeneticEvid_id']
        ));
        if (!is_object($result)) {
            return false;
        }
        return $result->result('array');
    }

    /**
     * Функция получения списка исследуемых лиц для журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
     * службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
     * @param type $data
     * @return boolean
     */
    protected function getEvnForensicGeneticGenLiveLinkList($data) {

        if (empty($data['EvnForensicGeneticGenLive_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор случая'));
        }

        $query = "
			SELECT
				EFGGLL.EvnForensicGeneticGenLiveLink_id as \"EvnForensicGeneticGenLiveLink_id\",
				EFGGLL.Person_id as \"Person_id\",
				EFGGLL_P.Person_Fio as \"Person_Fio\"
			FROM
				v_EvnForensicGeneticGenLiveLink EFGGLL
				LEFT JOIN LATERAL (
					SELECT
					P.Person_Fio
					FROM
						v_Person_all P
					WHERE P.Person_id = EFGGLL.Person_id
					ORDER BY P.PersonEvn_id ASC
				    LIMIT 1
				) As EFGGLL_P ON TRUE
			WHERE
				EFGGLL.EvnForensicGeneticGenLive_id = :EvnForensicGeneticGenLive_id
		";
        $result = $this->db->query($query,array(
            'EvnForensicGeneticGenLive_id' => $data['EvnForensicGeneticGenLive_id']
        ));
        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');

    }
}