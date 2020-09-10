<?php

class EvnForensicSub_model extends BSME_model {
    /**
     *
     * @param type $data
     */
    protected function _checkForenPersRequestCommonFields($data) {
        if (!$data || !is_array($data)) {
            return false;
        }
        //if (empty($data['EvnForensic_Date'])) {
        //	return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Дата заявки'));
        //}
        if (empty($data['Person_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Подэкспертное лицо'));
        }
        if (empty($data['EvnForensicSub_Goal'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Цели экспертизы'));
        }
        if (empty($data['EvnForensicSub_Facts'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Краткие обстоятельства дела'));
        }
        if (empty($data['Person_cid'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Назначившее лицо'));
        }
        if (empty($data['EvnForensicSub_AccidentDT'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Время происшествия'));
        }
        if (empty($data['session']['CurMedService_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор службы'));
        }
        return true;

    }
    /**
     * Функция сохранения заявки для службы судебно-биологической экспертизы потерпевших, обвиняемых и других лиц
     * @param array $data
     * @param boolean $setStatus_flag Флак установки статуса: TRUE - проставлять заявке новый статус, FALSE - сохранять предыдущий
     * @return type
     */
    public function saveForenPersRequest($data, $setStatus_flag = true) {


        //Тип заявки является обязательным, если заявка создаётся вновь,
        $ForensicSubType_id_required = ($data['EvnForensicSub_id'])?'':'required';


        $rules = array(
            array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSub_pid', 'label' => 'Идентификатор первичной экспертизы', 'rules' => '', 'type' => 'int'),
            array('field' => 'XmlType_id', 'label' => 'Идентификатор типа итогового документа', 'rules' => '', 'type' => 'id', 'default'=>13 /*Заключение по уголовному делу*/),
            array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'EvnForensicSub_Num', 'label' => 'Номер заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'EvnForensicSub_ExpertiseComeDate', 'label' => 'Дата поступления экспертизы', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicSub_ResDate', 'label' => 'Дата постановления', 'rules' => '', 'type' => 'string'),
            array('field' => 'Person_id', 'label' => 'Подэкспертное лицо', 'rules' => '', 'type' => 'id'),
            array('field' => 'Person_cid', 'label' => 'Инициатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Org_did', 'label' => 'Идентификатор учреждения направившего', 'rules' => '', 'type' => 'id'),
            array('field' => 'ForensicIniciatorPost_id', 'label' => 'Идентификатор должности инициатора', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnForensicSub_AccidentDT', 'label' => 'Дата происшествия', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicSub_ExpertiseDT', 'label' => 'Дата проведения экспертизы', 'rules' => '', 'type' => 'string', 'default'=>null),
            array('field' => 'MedPersonal_eid', 'label' => 'Идентификатор эксперта', 'rules' => '', 'type' => 'id'),
            array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'EvnForensicSub_Result', 'label' => 'Идентификатор заявки', 'rules' => 'max_length[1024]', 'type' => 'string', 'default'=>''),
            array('field' => 'EvnForensicSub_Receiver', 'label' => 'Получатель результата', 'rules' => 'max_length[1024]', 'type' => 'string', 'default'=>''),
            array('field' => 'Lpu_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
            array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
            array( 'field' => 'EvnForensicSub_Inherit', 'label' => 'Копировать разделы заключения из связной экспертизы?', 'rules' => '', 'type' => 'int' ),

            array('field' => 'PersonEvn_id', 'label' => 'Подэкспертное лицо: состояние ', 'rules' => '', 'type' => 'id'),
            array('field' => 'Server_id', 'label' => 'Подэкспертное лицо: сервер ', 'rules' => '', 'type' => 'id', 'default'=>'0'),

            array('field' => 'EvnStatus_id', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'id', 'default'=>null),

        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $proc = (empty($queryParams['EvnForensicSub_id']))?'p_EvnForensicSub_ins':'p_EvnForensicSub_upd';

        /*
         * Данная конструкция обусловлена возможностью передачи в метод как PersonEvn_id+Server_id так и Person_id
         */

        //Проверка наличия пары Server_id+PersonEvn_id как ключевого поля для пациента проверяется наличием поля PersonEvn_id,
        //т.к. Server_id может быть === '0'

        if (empty($queryParams['PersonEvn_id']) /* || empty($queryParams['Server_id'])*/ ) {

            if (empty($queryParams['Person_id'])) {
                return $this->createError('', 'Не передан идентификатор подэкспертного лица');
            }
            /*Получение PersonEvn_id и Server_id*/
            $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$data['Person_id']));
            if (!$personState || empty($personState[0]) || !isset($personState[0]['PersonEvn_id']) || !isset($personState[0]['Server_id'])) {
                return $this->createError('', 'Ошибка получения идентификатора состояния');
            }
            $queryParams['PersonEvn_id'] = $personState[0]['PersonEvn_id'];
            $queryParams['Server_id'] = $personState[0]['Server_id'];
        }

        $query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				EvnForensicSub_id as \"Evn_id\"			
			from {$proc} (
				EvnForensicSub_id := :EvnForensicSub_id,
				EvnForensicSub_pid := :EvnForensicSub_pid,
				XmlType_id := :XmlType_id,
				ForensicSubType_id := :ForensicSubType_id,
				EvnForensicSub_Num := :EvnForensicSub_Num,
				EvnForensicSub_ExpertiseComeDate := :EvnForensicSub_ExpertiseComeDate,
				EvnForensicSub_ResDate := :EvnForensicSub_ResDate,
				Person_cid := :Person_cid,
				Org_did := :Org_did,
				ForensicIniciatorPost_id := :ForensicIniciatorPost_id,
				EvnForensicSub_AccidentDT := :EvnForensicSub_AccidentDT,
				EvnForensicSub_ExpertiseDT := :EvnForensicSub_ExpertiseDT,
				MedPersonal_eid := :MedPersonal_eid,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				MedService_id := :MedService_id,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id,
				EvnForensicSub_Result := :EvnForensicSub_Result,
				EvnForensicSub_Receiver := :EvnForensicSub_Receiver,
				EvnStatus_id := :EvnStatus_id,
				EvnForensicSub_Inherit := :EvnForensicSub_Inherit
			);
		";

        //
        // 1. Сохраняем заявку
        //

        $this->db->trans_begin();

        $result = $this->queryResult($query, $queryParams);

        if (!$this->isSuccessful($result)) {
            $this->db->trans_rollback();
            return $result;
        }

        //
        // 2. Сохраняем прикрепления
        //
        $data['Evn_id'] = $result[0]['Evn_id'];

        if (sizeof($_FILES)) {
            $file_data_array = $this->_processFileArray($data);
            $this->load->model('EvnMediaFiles_model', 'emfmodel');
            //var_dump_exit($file_data_array);
            foreach ($file_data_array as $file_data) {
                $save_file_result = $this->emfmodel->uploadFile($file_data['file'],$file_data);
                if (!$this->isSuccessful($save_file_result)) {

                    //@TODO: убрать транзакцию, удалять уже сохраненные файлы с сервака

                    $this->db->trans_rollback();
                    return $save_file_result;
                }
            }
        }

        //
        // 3. Проставляем статус
        // 
        // Изменяем статус заявки на Назначенные

        if ($setStatus_flag) {

            $status = (empty($queryParams['MedPersonal_eid']))?'New':'Appoint';

            $this->load->model('Evn_model');
            $success = $this->Evn_model->updateEvnStatus(array(
                'Evn_id' => $data['Evn_id'],
                'EvnStatus_SysNick' => $status,
                'EvnClass_SysNick' => 'EvnForensic',
                'pmUser_id' => $queryParams['pmUser_id']
            ));

            if (!$success) {
                return $this->createError('', 'При постановке статуса заявке произошла ошибка. Обратитесь к администратору');
            }
        }

        $this->db->trans_commit();
        return $result;


    }

    /**
     * Функция создания записи в журнале регистрации заявлений о назначении судебно-медицинской экспертизы (исследования) подэкспертному
     * в службе судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
     * @param type $data
     * @return boolean
     */
    protected function _saveEvnForenSubDir($data) {
        $checkParam = $this->_checkForenPersRequestCommonFields($data);
        if ($checkParam !== true) {
            return $checkParam;
        }
        if (empty($data['Org_did'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор учреждения направившего'));
        }

        if (empty($data['EvnForensic_ResDate'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Дата постановления'));
        }
        $proc = 'p_EvnForensicSubDir_ins';
        if ($data['EvnForensicSub_id']) {
            $proc = 'p_EvnForensicSubDir_upd';

        }

        /*Получение PersonEvn_id и Server_id*/

        $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$data['Person_id']));
        if (!$personState || empty($personState[0]) || !isset($personState[0]['PersonEvn_id']) || !isset($personState[0]['Server_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Ошибка получения идентификатора состояния'));
        }
        $data['PersonEvn_id'] = $personState[0]['PersonEvn_id'];
        $data['Server_id'] = $personState[0]['Server_id'];

        $EvnForensicSub_Num = $data['EvnForensicSub_id']?':EvnForensicSub_Num':'COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF ),1);';

        $query = "
			select
				EvnForensicSubDir_id as \"Evn_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicSubDir_id := :EvnForensicSubDir_id,
				EvnForensicSubDir_ResDate := :EvnForensic_ResDate,
				EvnForensicSubDir_Num := {$EvnForensicSub_Num},
				EvnForensicSubDir_Goal := :EvnForensicSub_Goal,
				EvnForensicSubDir_Facts := :EvnForensicSub_Facts,
				Org_did := :Org_did,
				Person_cid := :Person_cid,
				EvnForensicSubDir_AccidentDT := :EvnForensicSub_AccidentDT,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				MedService_id := :MedService_id,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
		";
        $result = $this->db->query($query,array(
            'EvnForensicSub_id'=>$data['EvnForensicSub_id'],
            'EvnForensicSub_Num'=>$data['EvnForensicSub_Num'],
            'EvnForensic_ResDate'=>$data['EvnForensic_ResDate'],
            'Person_id'=>$data['Person_id'],
            'EvnForensicSub_Goal'=>$data['EvnForensicSub_Goal'],
            'EvnForensicSub_Facts'=>$data['EvnForensicSub_Facts'],
            'Org_did'=>$data['Org_did'],
            'Person_cid'=>$data['Person_cid'],
            'EvnForensicSub_AccidentDT'=>$data['EvnForensicSub_AccidentDT'],
            'PersonEvn_id'=>$data['PersonEvn_id'],
            'Server_id'=>$data['Server_id'],
            'MedService_id'=> $data['session']['CurMedService_id'],
            'pmUser_id'=>$data['pmUser_id'],
            'Lpu_id'=>$data['Lpu_id'],
        ));



        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }
    /**
     * Получение данных о заявке
     * @param type $data
     */
    protected function _getEvnForenSubDir($data) {
        $rules = array(
            array('field'=>'EvnForensicSub_id', 'rules'=>'required','label'=>'Идентификатор заявки', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				EFSD.*
			FROM
				v_EvnForensicSubDir EFSD
			WHERE
				EFSD.EvnForensicSubDir_id = :
		";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Функция создания записи в журнале регистрации заявлений о назначении судебно-медицинской экспертизы (исследования) медицинских документов
     * в службе судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
     * @param type $data
     * @return boolean
     */
    protected function _saveEvnForenSubDoc($data) {
        $checkParam = $this->_checkForenPersRequestCommonFields($data);
        if ($checkParam !== true) {
            return $checkParam;
        }
        if (empty($data['Org_did'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор учреждения направившего'));
        }
        if (empty($data['EvnForensicSubDoc_TransferMat'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Переданные материалы'));
        }

        if (empty($data['EvnForensic_ResDate'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Дата постановления'));
        }

        $proc = 'p_EvnForensicSubDoc_ins';
        if ($data['EvnForensicSub_id']) {
            $proc = 'p_EvnForensicSubDoc_upd';

        }

        /*Получение PersonEvn_id и Server_id*/

        $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$data['Person_id']));

        if (!$personState || empty($personState[0]) || !isset($personState[0]['PersonEvn_id']) || !isset($personState[0]['Server_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Ошибка получения идентификатора состояния'));
        }
        $data['PersonEvn_id'] = $personState[0]['PersonEvn_id'];
        $data['Server_id'] = $personState[0]['Server_id'];


        $EvnForensicSub_Num = $data['EvnForensicSub_id']?':EvnForensicSub_Num':'COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1);';

        $query = "
			select
				EvnForensicSubDoc_id as \"Evn_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicSubDoc_id := :EvnForensicSubDoc_id,
				EvnForensicSubDoc_ResDate := :EvnForensic_ResDate,
				EvnForensicSubDoc_Num := {$EvnForensic_Num},
				EvnForensicSubDoc_Goal := :EvnForensicSub_Goal,
				EvnForensicSubDoc_Facts := :EvnForensicSub_Facts,
				Org_did := :Org_did,
				Person_cid := :Person_cid,
				EvnForensicSubDoc_AccidentDT := :EvnForensicSub_AccidentDT,
				EvnForensicSubDoc_TransferMat := :EvnForensicSubDoc_TransferMat,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				MedService_id := :MedService_id,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
		";

        $result = $this->db->query($query,array(
            'EvnForensicSub_id'=>$data['EvnForensicSub_id'],
            'EvnForensicSub_Num'=>$data['EvnForensicSub_Num'],
            'EvnForensicSub_Goal'=>$data['EvnForensicSub_Goal'],
            'EvnForensicSub_Facts'=>$data['EvnForensicSub_Facts'],
            'EvnForensic_ResDate'=>$data['EvnForensic_ResDate'],
            'Org_did'=>$data['Org_did'],
            'Person_cid'=>$data['Person_cid'],
            'EvnForensicSub_AccidentDT'=>$data['EvnForensicSub_AccidentDT'],
            'PersonEvn_id'=>$data['PersonEvn_id'],
            'Server_id'=>$data['Server_id'],
            'MedService_id'=> $data['session']['CurMedService_id'],
            'EvnForensicSubDoc_TransferMat'=>$data['EvnForensicSubDoc_TransferMat'],
            'pmUser_id'=>$data['pmUser_id'],
            'Lpu_id'=>$data['Lpu_id'],
        ));

        if (!is_object($result)) {
            return false;
        }


        return $result->result('array');
    }
    /**
     * Функция создания записи в журнале регистрации заявлений о назначении судебно-медицинской экспертизы медицинских документов с осмотром подэкспертного
     * в службе судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
     * @param type $data
     * @return boolean
     */
    protected function _saveEvnForenSubInsp($data) {
        $checkParam = $this->_checkForenPersRequestCommonFields($data);
        if ($checkParam !== true) {
            return $checkParam;
        }
        if (empty($data['Org_did'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор учреждения направившего'));
        }
        if (empty($data['EvnForensicSubInsp_TransferMat'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Переданные материалы'));
        }

        if (empty($data['EvnForensic_ResDate'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Дата постановления'));
        }
        $proc = 'p_EvnForensicSubInsp_ins';
        if ($data['EvnForensicSub_id']) {
            $proc = 'p_EvnForensicSubInsp_upd';

        }

        /*Получение PersonEvn_id и Server_id*/

        $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$data['Person_id']));
        if (!$personState || empty($personState[0]) || !isset($personState[0]['PersonEvn_id']) || !isset($personState[0]['Server_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Ошибка получения идентификатора состояния'));
        }
        $data['PersonEvn_id'] = $personState[0]['PersonEvn_id'];
        $data['Server_id'] = $personState[0]['Server_id'];

        $EvnForensicSub_Num = $data['EvnForensicSub_id']?':EvnForensicSub_Num':'COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1);';

        $query = "
			select
				EvnForensicSubInsp_id as \"Evn_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicSubInsp_id := :EvnForensicSubInsp_id,
				EvnForensicSubInsp_ResDate := :EvnForensic_ResDate,
				EvnForensicSubInsp_Num := {$EvnForensicSub_Num},
				EvnForensicSubInsp_Goal := :EvnForensicSub_Goal,
				EvnForensicSubInsp_Facts := :EvnForensicSub_Facts,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				Org_did := :Org_did,
				Person_cid := :Person_cid,
				EvnForensicSubInsp_AccidentDT := :EvnForensicSub_AccidentDT,
				EvnForensicSubInsp_TransferMat := :EvnForensicSubInsp_TransferMat,
				MedService_id := :MedService_id,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
		";


        $result = $this->db->query($query,array(
            'EvnForensicSub_id'=>$data['EvnForensicSub_id'],
            'EvnForensicSub_Num'=>$data['EvnForensicSub_Num'],
            'EvnForensic_ResDate'=>$data['EvnForensic_ResDate'],
            'Person_id'=>$data['Person_id'],
            'EvnForensicSub_Goal'=>$data['EvnForensicSub_Goal'],
            'EvnForensicSub_Facts'=>$data['EvnForensicSub_Facts'],
            'Org_did'=>$data['Org_did'],
            'Person_cid'=>$data['Person_cid'],
            'EvnForensicSub_AccidentDT'=>$data['EvnForensicSub_AccidentDT'],
            'EvnForensicSubInsp_TransferMat'=>$data['EvnForensicSubInsp_TransferMat'],
            'PersonEvn_id'=>$data['PersonEvn_id'],
            'Server_id'=>$data['Server_id'],
            'MedService_id'=> $data['session']['CurMedService_id'],
            'pmUser_id'=>$data['pmUser_id'],
            'Lpu_id'=>$data['Lpu_id'],
        ));

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }
    /**
     * Функция создания записи в журнале регистрации заявлений по личному заявлению потерпевшего в службе судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
     * @param type $data
     * @return boolean
     */
    protected function _saveEvnForenSubOwn($data) {
        if (isset($data['Person_id'])) {
            $data['Person_cid'] = $data['Person_id'];
        }
        $checkParam = $this->_checkForenPersRequestCommonFields($data);
        if ($checkParam !== true) {
            return $checkParam;
        }

        if (empty($data['EvnForensicSubOwn_Cost'])) {
            return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Стоимость'));
        }

        $proc = 'p_EvnForensicSubOwn_ins';
        if ($data['EvnForensicSub_id']) {
            $proc = 'p_EvnForensicSubOwn_upd';

        }


        /*Получение PersonEvn_id и Server_id*/

        $personState = $this->_getPersonStateByPersonId(array('Person_id'=>$data['Person_id']));
        if (!$personState || empty($personState[0]) || !isset($personState[0]['PersonEvn_id']) || !isset($personState[0]['Server_id'])) {
            return array(array('success'=>false,'Error_Msg'=>'Ошибка получения идентификатора состояния'));
        }
        $data['PersonEvn_id'] = $personState[0]['PersonEvn_id'];
        $data['Server_id'] = $personState[0]['Server_id'];

        $EvnForensicSub_Num = $data['EvnForensicSub_id']?':EvnForensicSub_Num ;':'COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1);';

        $query = "
			select
				EvnForensicSubOwn_id as \"Evn_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"		
			from {$proc} (
				EvnForensicSubOwn_id := :EvnForensicSubOwn_id,
				EvnForensicSubOwn_ResDate := dbo.tzGetDate(),
				EvnForensicSubOwn_Num := {$EvnForensic_Num},
				EvnForensicSubOwn_Goal := :EvnForensicSub_Goal,
				EvnForensicSubOwn_Facts := :EvnForensicSub_Facts,
				EvnForensicSubOwn_Cost := :EvnForensicSubOwn_Cost,
				Person_cid := :Person_cid,
				EvnForensicSubOwn_AccidentDT := :EvnForensicSub_AccidentDT,
				PersonEvn_id :=:PersonEvn_id,
				Server_id :=:Server_id,
				MedService_id :=:MedService_id,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
		";

        $result = $this->db->query($query,array(
            'EvnForensicSubOwn_id'=>$data['EvnForensicSub_id'],
            'EvnForensicSub_Num'=>$data['EvnForensicSub_Num'],
            'Person_id'=>$data['Person_id'],
            'EvnForensicSub_Goal'=>$data['EvnForensicSub_Goal'],
            'EvnForensicSub_Facts'=>$data['EvnForensicSub_Facts'],
            'Org_did'=>$data['Org_did'],
            'Person_cid'=>$data['Person_cid'],
            'EvnForensicSub_AccidentDT'=>$data['EvnForensicSub_AccidentDT'],
            'EvnForensicSubOwn_Cost'=>$data['EvnForensicSubOwn_Cost'],
            'PersonEvn_id'=>$data['PersonEvn_id'],
            'Server_id'=>$data['Server_id'],
            'MedService_id'=> $data['session']['CurMedService_id'],
            'pmUser_id'=>$data['pmUser_id'],
            'Lpu_id'=>$data['Lpu_id'],
        ));

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Функция получения заявки службы судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
     * @return boolean
     */
    public function getForenPersRequest($data) {
        $rules = array(
            array('field' => 'EvnForensic_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
        );
        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				-- Общий блок

				EFS.EvnForensicSub_Num as \"EvnForensicSub_Num\",
				EFS.XmlType_id as \"XmlType_id\",
				to_char(EFS.EvnForensicSub_ResDate, 'dd.mm.yyyy') as \"EvnForensicSub_ResDate\",
				to_char(EFS.EvnForensicSub_ResDate, 'hh24:mi') as \"EvnForensicSub_ResTime\",
				to_char(EFS.EvnForensicSub_ExpertiseComeDate, 'dd.mm.yyyy') as \"EvnForensicSub_ExpertiseComeDate\",
				to_char(EFS.EvnForensicSub_ExpertiseComeDate, 'hh24:mi') as \"EvnForensicSub_ExpertiseComeTime\",
				to_char(EFS.EvnForensicSub_ExpertiseDT, 'dd.mm.yyyy') as \"EvnForensicSub_ExpertiseDate\",
				to_char(EFS.EvnForensicSub_ExpertiseDT, 'hh24:mi') as \"EvnForensicSub_ExpertiseTime\",
				EFS.EvnForensicSub_id as \"EvnForensicSub_id\",
				EFIP.ForensicIniciatorPost_id as \"ForensicIniciatorPost_id\",
				EFIP.ForensicIniciatorPost_Name as \"ForensicIniciatorPost_Name\",
				to_char( EFS.EvnForensicSub_insDT, 'dd.mm.yyyy') as \"EvnForensicSub_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFST.ForensicSubType_Name,'Заявка') as \"ForensicSubType_Name\",
				EFST.ForensicSubType_id as \"ForensicSubType_id\",
				EFS.EvnClass_id as \"EvnClass_id\",
				COALESCE(Iniciator.Person_Fio,'') as \"Iniciator_Fio\",
				EFS.MedPersonal_eid as \"MedPersonal_eid\",
				EFS.Person_cid as \"Person_cid\",
				EFS.Person_gid as \"Person_gid\",
				EFS.Person_id as \"Person_id\",
				EFS.Org_did as \"Org_did\",
				COALESCE(O.Org_Name,'') as \"Org_Name\",
				EFS.EvnForensicSub_pid as \"EvnForensicSub_pid\",
				SUBEFS.EvnForensicSub_Num as \"EvnForensicSubFirstExp_Num\",
				EFS.EvnForensicSub_Inherit as \"EvnForensicSub_Inherit\",
				
                to_char( EFS.EvnForensicSub_AccidentDT, 'dd.mm.yyyy') as \"EvnForensicSub_AccidentDate\",
				to_char(EFS.EvnForensicSub_AccidentDT, 'hh24:mi') as \"EvnForensicSub_AccidentTime\",
				COALESCE(P.Person_SurName, '') || (COALESCE(' ' || P.Person_FirName, '')) || (COALESCE(' ' || P.Person_SecName, '')) AS \"Person_Fio\",
				
				-- Комментарий, если есть
				COALESCE(ESH.EvnStatusHistory_Cause,'') as \"EvnStatusHistory_Cause\",
                
				EX.EvnXml_id as \"EvnXml_id\"

			FROM
				v_EvnForensicSub EFS
				left join v_ForensicIniciatorPost EFIP on EFIP.ForensicIniciatorPost_id = EFS.ForensicIniciatorPost_id
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFS.EvnForensicSub_id
				left join v_ForensicSubType EFST on EFS.ForensicSubType_id = EFST.ForensicSubType_id
				left join v_Org O on EFS.Org_did = O.Org_id
				
				LEFT JOIN v_ForensicEvnXmlVersion FEXV1 ON FEXV1.EvnForensic_id = EFS.EvnForensicSub_id
                LEFT JOIN v_ForensicEvnXmlVersion FEXV2 ON FEXV2.EvnForensic_id = FEXV1.EvnForensic_id AND FEXV2.ForensicEvnXmlVersion_Num > FEXV1.ForensicEvnXmlVersion_Num
				LEFT JOIN v_EvnXml EX on FEXV1.EvnXml_id = EX.EvnXml_id

				left join v_Person_all P on EFS.PersonEvn_id = P.PersonEvn_id and EFS.Server_id = P.Server_id
				left join v_EvnForensicSub SUBEFS on SUBEFS.EvnForensicSub_id = EFS.EvnForensicSub_pid
				left join lateral (
					SELECT
						ESH.EvnStatusHistory_Cause
					FROM
						v_EvnStatusHistory ESH
					WHERE
						ESH.Evn_id = EFS.EvnForensicSub_id
					ORDER BY
						ESH.EvnStatusHistory_insDT DESC
					LIMIT 1
				) as ESH on true
				left join lateral (
					SELECT
						MP.Person_Fin as Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EFS.MedPersonal_eid
					LIMIT 1
				) as MP on true
				left join lateral (
					SELECT
						P.Person_Fio as Person_Fio
					FROM
						v_Person_all P
					WHERE
						P.Person_id = EFS.Person_cid
					LIMIT 1
				) as Iniciator on true
			WHERE 
				FEXV2.ForensicEvnXmlVersion_id is NULL AND
				EFS.EvnForensicSub_id = :EvnForensic_id
		";

        //var_dump(getDebugSQL($query, $queryParams)); exit;

        $result = $this->queryResult($query, $queryParams);

        if (!$this->isSuccessful($result)) {
            return $result;
        }

        $attachment = $this->_getAttachment($data);
        if (!$this->isSuccessful($attachment)) {
            return $attachment;
        }
        $result[0]['attachment'] = $attachment;


        $dopDocDirections = $this->_getEvnForensicSubDopDocRequestList(array(
            'EvnForensicSub_id'=>$queryParams['EvnForensic_id']
        ));
        if (!$this->isSuccessful($dopDocDirections)) {
            return $dopDocDirections;
        }
        $result[0]['dopDocDirections'] = $dopDocDirections;

        $dopPersDirections = $this->_getEvnForensicSubDopPersRequestList(array(
            'EvnForensicSub_id'=>$queryParams['EvnForensic_id']
        ));
        if (!$this->isSuccessful($dopPersDirections)) {
            return $dopPersDirections;
        }
        $result[0]['dopPersDirections'] = $dopPersDirections;

        $coverLetters = $this->_getEvnForensicSubCoverLetterList(array(
            'EvnForensicSub_id'=>$queryParams['EvnForensic_id']
        ));
        if (!$this->isSuccessful($coverLetters)) {
            return $coverLetters;
        }
        $result[0]['coverLetters'] = $coverLetters;


        return $result;
    }
    /**
     * Получение списка заявок службы судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicSubList($data) {
        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        if (empty($data['MedPersonal_id']) && !empty($data['session']['medpersonal_id'])) {
            $data['MedPersonal_id'] = $data['session']['medpersonal_id'];
        }

        $rules = array(
            array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
            array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
            array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
            array('field'=>'ARMType', 'label'=>'Тип АРМ','rules' => '', 'type' => 'string'),
            array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'rules' => '', 'type' => 'id'),
            //Параметры поиска
            array('field'=>'EvnForensic_Num', 'label'=>'Номер заявки','rules' => '', 'type' => 'int'),
            array('field'=>'MedPersonal_eid', 'label'=>'Идентификатор эксперта','rules' => '', 'type' => 'id'),
            array('field' => 'own', 'label' => 'Только собственные заявки', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
            array('field' => 'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
            array( 'field' => 'Evn_insDT', 'label' => 'Дата экспертизы', 'rules' => '', 'type' => 'string' ),
            array( 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ),
            array( 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ),
            array( 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ),
            array( 'field' => 'Expert_id', 'label' => 'Идентификатор эксперта', 'rules' => '', 'type' => 'id' ),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $where = array(
            'EFS.MedService_id=:MedService_id',
            'FEXV2.EvnForensic_id is null'
        );

        //добавочный фильр
        if (!empty($data['filters'])) {
            $arr = json_decode($data['filters']);

            if (trim($data['filters']) != '[{}]') foreach ($arr as $key => $value) {
                $filt = (array)$value;
                $filtstr = ' '.key($filt).' = '.$filt[key($filt)];
                if($filt[key($filt)]!=''&&$filt[key($filt)]!=null){$where[] = $filtstr;}
            }
        }

        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                //$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
                $where[] = "COALESCE(ES.EvnStatus_SysNick,'New') = :EvnStatus_SysNick";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        } elseif (!empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] == 'All') {
            $where[] = "COALESCE(ES.EvnStatus_SysNick,'New') != 'Done' ";
        }

        if (!empty($queryParams['MedPersonal_eid'])) {
            $where[] = "EFS.MedPersonal_eid=:MedPersonal_eid";
        }

        if (!empty($queryParams['EvnForensic_Num'])) {
            $where[] = "EFS.EvnForensicSub_Num=:EvnForensic_Num";
        }

        if (!empty($queryParams['Person_SurName'])) {
            $where[] = "P.Person_SurName iLIKE '%'||:Person_SurName||'%'";
        }
        if (!empty($queryParams['Evn_insDT'])) {
            $where[] = "CAST(EFS.EvnForensicSub_insDT as date)=CAST(:Evn_insDT as date)";
        }
        if (!empty($queryParams['Person_FirName'])) {
            $where[] = "P.Person_FirName iLIKE '%'||:Person_FirName||'%'";
        }
        if (!empty($queryParams['Person_SecName'])) {
            $where[] = "P.Person_SecName iLIKE '%'||:Person_SecName||'%'";
        }
        if (!empty($queryParams['Expert_id'])) {
            $where[] = "EFS.MedPersonal_eid=:Expert_id";
        }

        if ($queryParams['own']) {
            $where[] =  "EFS.pmUser_insID=:pmUser_id";
        }

        //if ($queryParams['ARMType'] == 'expert') {
        //if (empty($queryParams['MedPersonal_id'])) {
        //return $this->createError('','Не задан обязательны параметр: Идентификатор эксперта');
        //}
        //$where[] = "EFS.MedPersonal_eid=:MedPersonal_id";
        //}

        if (!empty($queryParams['ForensicSubType_id'])) {
            $where[] = "EFS.ForensicSubType_id=:ForensicSubType_id";
        }


        if ( $queryParams[ 'EvnStatus_SysNick' ] == 'Done'
            && empty( $queryParams[ 'EvnForensic_Num' ] )
            && empty( $queryParams[ 'Expert_id' ] )
            && empty( $queryParams[ 'Person_SurName' ] )
            && empty( $queryParams[ 'Person_FirName' ] )
            && empty( $queryParams[ 'Person_SecName' ] )
            && empty( $queryParams[ 'Evn_insDT' ] )
        ) {
            $where[] = "1=2";
        }

        $query = "
			SELECT
			-- select
				EFS.EvnForensicSub_Num as \"EvnForensic_Num\",
				EFS.EvnForensicSub_id  as \"EvnForensic_id\",
				to_char( EFS.EvnForensicSub_insDT, 'dd.mm.yyyy') as \"Evn_insDT\",

				-- Эксперт
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(MP.Person_Fio,'Не назначен') as \"Expert_Fio\",
				--MP.Person_id as Expert_id,
				EFS.MedPersonal_eid as \"Expert_id\",
				-- Подэкспертный
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fin\",
				P.Person_Fio as \"Person_Fio\",
				P.Person_id as \"Person_id\",

				'Не определён' as \"EvnForensicType_Name\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				EFS.EvnClass_id as \"EvnClass_id\",
				EFS.XmlType_id as \"XmlType_id\",
				EFS.ForensicSubType_id as \"ForensicSubType_id\",
				EFS.EvnForensicSub_Inherit as \"EvnForensicSub_Inherit\",
				EX.EvnXml_id as \"EvnXml_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicSub EFS
				left join lateral (
                	SELECT
                    	MP.Person_Fin,
						MP.Person_Fio,
						MP.Person_id
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EFS.MedPersonal_eid
                    LIMIT 1
                ) as MP ON TRUE
				left join v_Person_all P on EFS.PersonEvn_id = P.PersonEvn_id and EFS.Server_id = P.Server_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFS.EvnStatus_id )
				LEFT JOIN v_ForensicEvnXmlVersion FEXV1 ON FEXV1.EvnForensic_id = EFS.EvnForensicSub_id
                LEFT JOIN v_ForensicEvnXmlVersion FEXV2 ON FEXV2.EvnForensic_id = FEXV1.EvnForensic_id AND FEXV2.ForensicEvnXmlVersion_Num > FEXV1.ForensicEvnXmlVersion_Num
				left join v_EvnXml EX on FEXV1.EvnXml_id = EX.EvnXml_id
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFS.EvnForensicSub_Num DESC
			-- end order by
		";
        //@TODO: Добавим статусы, когда разберёмся
        $result = array();

        //echo getDebugSQL($query,$queryParams);exit;

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
     * Получение списка заявок журнала судебно-медицинской экспертизы (исследования) подэкспертному
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicSubDirList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

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
            'EFSD.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        } elseif (!empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] == 'All') {
            $where[] = "COALESCE(ES.EvnStatus_SysNick,'') != 'Done' ";
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
				EFSD.EvnForensicSubDir_Num as \"EvnForensic_Num\",
				EFSD.EvnForensicSubDir_id as \"EvnForensic_id\",
				to_char(EFSD.EvnForensicSubDir_insDT, 'dd.mm.yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\" ,
				EFSD.EvnClass_id as \"EvnClass_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicSubDir EFSD
				left join v_EvnDirectionForensic EDF on  EDF.EvnForensic_id = EFSD.EvnForensicSubDir_id
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
				left join v_Person_all P on EFSD.PersonEvn_id = P.PersonEvn_id and EFSD.Server_id = P.Server_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFSD.EvnStatus_id )
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFSD.EvnForensicSubDir_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF on true
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFSD.EvnForensicSubDir_Num DESC
			-- end order by
		";
        //@TODO: Добавим статусы, когда разберёмся
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
     * Получение списка заявок журнала судебно-медицинской экспертизы медицинских документов с осмотром подэкспертного
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicSubInspList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

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
            'EFSI.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        } elseif (!empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] == 'All') {
            $where[] = "COALESCE(ES.EvnStatus_SysNick,'') != 'Done' ";
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
				EFSI.EvnForensicSubInsp_Num as \"EvnForensic_Num\",
				EFSI.EvnForensicSubInsp_id  as \"EvnForensic_id\",
				to_char(EFSI.EvnForensicSubInsp_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\" ,
				EFSI.EvnClass_id as \"EvnClass_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicSubInsp EFSI
				left join v_EvnDirectionForensic EDF on  EDF.EvnForensic_id = EFSI.EvnForensicSubInsp_id
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
				left join v_Person_all P on EFSI.PersonEvn_id = P.PersonEvn_id and EFSI.Server_id = P.Server_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFSI.EvnStatus_id )
				LEFT JOIN LATERAL (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFSI.EvnForensicSubInsp_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF ON TRUE
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFSI.EvnForensicSubInsp_Num DESC
			-- end order by
		";
        //@TODO: Добавим статусы, когда разберёмся
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
     * Получение списка заявок журнала судебно-медицинской экспертизы (исследования) медицинских документов
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicSubDocList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

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
            'EFSD.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        } elseif (!empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] == 'All') {
            $where[] = "COALESCE(ES.EvnStatus_SysNick,'') != 'Done' ";
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
				EFSD.EvnForensicSubDoc_Num as \"EvnForensic_Num\",
				EFSD.EvnForensicSubDoc_id  as \"EvnForensic_id\",
				to_char( EFSD.EvnForensicSubDoc_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\" ,
				EFSD.EvnClass_id as \"EvnClass_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicSubDoc EFSD
				left join v_EvnDirectionForensic EDF on  EDF.EvnForensic_id = EFSD.EvnForensicSubDoc_id
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
				left join v_Person_all P on EFSD.PersonEvn_id = P.PersonEvn_id and EFSD.Server_id = P.Server_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFSD.EvnStatus_id )
				left join lateral(
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFSD.EvnForensicSubDoc_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF ON TRUE
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFSD.EvnForensicSubDoc_Num DESC
			-- end order by
		";
        //@TODO: Добавим статусы, когда разберёмся
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
     * Получение списка заявок журнала судебно-медицинской экспертизы  по личному заявлению потерпевшего
     * @param null $data
     * @return boolean
     */
    public function getEvnForensicSubOwnList($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

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
            'EFSO.MedService_id=:MedService_id',
        );
        if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
            if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
                $where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
            } else {
                $where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
            }
        } elseif (!empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] == 'All') {
            $where[] = "COALESCE(ES.EvnStatus_SysNick,'') != 'Done' ";
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
				EFSO.EvnForensicSubOwn_Num as \"EvnForensic_Num\",
				EFSO.EvnForensicSubOwn_id as \"EvnForensic_id\",
				to_char(EFSO.EvnForensicSubOwn_insDT, 'dd/mm/yyyy') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\" ,
				EFSO.EvnClass_id as \"EvnClass_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicSubOwn EFSO
				left join v_EvnDirectionForensic EDF on  EDF.EvnForensic_id = EFSO.EvnForensicSubOwn_id
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
				left join v_Person_all P on EFSO.PersonEvn_id = P.PersonEvn_id and EFSO.Server_id = P.Server_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFSO.EvnStatus_id )
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFSO.EvnForensicSubOwn_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF ON TRUE
			-- end from
			WHERE
			-- where
				".implode( " AND ", $where )."
			-- end where
			ORDER BY
			-- order by
				EFSO.EvnForensicSubOwn_Num DESC
			-- end order by
			";
        //@TODO: Добавим статусы, когда разберёмся
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
     * Сохранение заявки на дополнительные материалы в службе судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
     * @param $data
     * @return bool
     */
    public function saveForenPersDopMatQuery($data) {
        $queryParams = array(
            'EvnForensicSubDopMatQuery_id' => $data['EvnForensicSubDopMatQuery_id'],
            'EvnForensicSub_id' => $data['EvnForensicSub_id'],
            'EvnForensicSubDopMatQuery_Name' => $data['EvnForensicSubDopMatQuery_Name'],
            'Org_id' => $data['Org_id'],
            'EvnForensicSubDopMatQuery_ResearchDate' => $data['EvnForensicSubDopMatQuery_ResearchDate'],
            'Person_aid' => $data['Person_aid'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id'],
        );

        $query = "
			select
				EvnForensicSubDopMatQuery_id as \"EvnForensicSubDopMatQuery_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicSubDopMatQuery_ins (
				EvnForensicSubDopMatQuery_id := :EvnForensicSubDopMatQuery_id,
				EvnForensicSubDopMatQuery_pid := :EvnForensicSub_id,
				EvnForensicSubDopMatQuery_Name := :EvnForensicSubDopMatQuery_Name,
				EvnForensicSubDopMatQuery_Num := COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1),
				Org_id := :Org_id,
				EvnForensicSubDopMatQuery_ResearchDate := :EvnForensicSubDopMatQuery_ResearchDate,
				Person_aid := :Person_aid,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Получение заявки на доп. материалы
     * @param $data
     * @return bool
     */
    public function getForenPersDopMatQuery($data) {
        $rules = array(
            array('field'=>'EvnForensicSubDopMatQuery_id', 'rules'=>'required','label'=>'Идентификатор запроса на доп. материалы', 'type' => 'id')
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				EFSDMQ.EvnForensicSubDopMatQuery_id as \"EvnForensicSubDopMatQuery_id\",
				EFSDMQ.EvnForensicSubDopMatQuery_pid as \"EvnForensicSub_id\",
				to_char(EFSDMQ.EvnForensicSubDopMatQuery_insDT, 'dd.mm.yyyy')||' '||to_char(EFSDMQ.EvnForensicSubDopMatQuery_insDT, 'hh24:mi') as \"EvnForensicSubDopMatQuery_insDT\",
				COALESCE(to_char(EFSDMQ.EvnForensicSubDopMatQuery_ResultDT, 'dd.mm.yyyy'),'') as \"EvnForensicSubDopMatQuery_ResultDT\",
				COALESCE(to_char(EFSDMQ.EvnForensicSubDopMatQuery_ResearchDate, 'dd.mm.yyyy'),'') as \"EvnForensicSubDopMatQuery_ResearchDate\",
				EFSDMQ.EvnForensicSubDopMatQuery_Name as \"EvnForensicSubDopMatQuery_Name\",
				EFSDMQ.Org_id as \"Org_id\",
				EFSDMQ.Person_aid as \"Person_aid\",
				PS.Person_SurName||' '||PS.Person_FirName||' '||COALESCE(PS.Person_SecName,'') as \"Person_FIO\"
			FROM
				v_EvnForensicSubDopMatQuery EFSDMQ
				left join v_PersonState PS on PS.Person_id = EFSDMQ.Person_aid
			WHERE
				EFSDMQ.EvnForensicSubDopMatQuery_id = :EvnForensicSubDopMatQuery_id
		";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Получение списка направлений на получение дополнительных материалов
     * @param type $data
     * @return type
     */
    protected function _getEvnForenSubDopMatQueryList($data) {
        $rules = array(
            array('field'=>'EvnForensicSub_id', 'rules'=>'required','label'=>'Идентификатор заявки', 'type' => 'id')
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;
        //Пока получаем только номер, дату создания и 
        $query = "
			SELECT
				EFSDMQ.EvnForensicSubDopMatQuery_id as \"EvnForensicSubDopMatQuery_id\",
				COALESCE (EFSDMQ.EvnForensicSubDopMatQuery_Num,0) as \"EvnForensicSubDopMatQuery_Num\",
				to_char(EFSDMQ.EvnForensicSubDopMatQuery_insDT, 'dd.mm.yyyy')||' '||to_char(EFSDMQ.EvnForensicSubDopMatQuery_insDT, 'hh24:mi') as \"EvnForensicSubDopMatQuery_insDT\",
				COALESCE(to_char(EFSDMQ.EvnForensicSubDopMatQuery_ResultDT, 'dd.mm.yyyy'),'') as \"EvnForensicSubDopMatQuery_ResultDT\"
			FROM
				v_EvnForensicSubDopMatQuery EFSDMQ
			WHERE
				EFSDMQ.EvnForensicSubDopMatQuery_pid =:EvnForensicSub_id
		";

        return $this->queryResult($query,$queryParams);

    }

    /**
     * Получение списка заявок для журнала
     * @param type $data
     */
    public function getEvnForensicSubArchive($data) {

        $data['MedService_id'] = (empty($data['MedService_id']))
            ?((empty($data['session']['CurMedService_id']))
                ? null
                : $data['session']['CurMedService_id'])
            : $data['MedService_id'];

        $rules = array(
            array('field' => 'XmlType_id', 'label' => 'Идентификатор типа итогового документа', 'rules' => '', 'type' => 'id', 'default'=>0),
            array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'type' => 'id', 'default'=>0),
            array('field' => 'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
            array('field' => 'start', 'label' => '','rules' => '', 'type' => 'int', 'default' => 0),
            array('field' => 'limit', 'label' => '','rules' => '', 'type' => 'int', 'default' => 10),
            array('field' => 'JournalType', 'label' => 'Тип журнала','rules' => 'required', 'type' => 'string'),
            //array('field' => 'filterField', 'label' => 'Тип поля фильтрования','rules' => '', 'type' => 'string'),
            //array('field' => 'filterVal', 'label' => 'Значение поля фильтрации','rules' => '', 'type' => 'string'),
            array('field' => 'begDate', 'label' => '','rules' => '', 'type' => 'string'),
            array('field' => 'endDate', 'label' => '','rules' => '', 'type' => 'string'),

            array('field' => 'EvnForensic_Num', 'label' => 'Значение поля фильтрации','rules' => '', 'type' => 'int'),
            array('field' => 'Person_SurName', 'label' => 'Фамилия','rules' => '', 'type' => 'string'),
            array('field' => 'Person_FirName', 'label' => 'Имя','rules' => '', 'type' => 'string'),
            array('field' => 'Person_SecName', 'label' => 'Отчество','rules' => '', 'type' => 'string'),
            array('field' => 'MedPersonal_eid', 'label'=>'Идентификатор эксперта', 'rules' => '', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $whereClause = '';
        $selectClause = '';
        $filter = "
				EFS.MedService_id=:MedService_id
                AND ES.EvnStatus_SysNick = 'Done'
				AND FEXV2.ForensicEvnXmlVersion_id is NULL
			";

        if ( !empty($queryParams['begDate'] ) && ( !empty($queryParams['endDate']) ))
        {

            $queryParams['begDate'] = $queryParams['begDate'].' 00:00:00';
            $queryParams['endDate'] = $queryParams['endDate'].' 23:59:59';

            $filter .= "
				AND EFS.EvnForensicSub_insDT >= :begDate
				AND EFS.EvnForensicSub_insDT <= :endDate
			";
        }

        if (!empty($queryParams['MedPersonal_eid'])) {
            $filter .= "
				AND EFS.MedPersonal_eid = :MedPersonal_eid
			";
        }

        if (!empty($queryParams['EvnForensic_Num'])) {
            $filter .= "
				AND EFS.EvnForensicSub_Num = :EvnForensic_Num
			";
        }

        if (!empty($queryParams['Person_SurName'])) {
            $filter .= "
				AND 
					lower(PS_all.Person_SurName) like '%'||lower(:Person_SurName)||'%'
				";
        }

        if (!empty($queryParams['Person_FirName'])) {
            $filter .= "
				AND 
					lower(PS_all.Person_FirName) like '%'||lower(:Person_FirName)||'%'
				";
        }

        if (!empty($queryParams['Person_SecName'])) {
            $filter .= "
				AND 
					lower(PS_all.Person_SecName) like '%'||lower(:Person_SecName)||'%'
				";
        }

        if (!empty($queryParams['ForensicSubType_id'])) {
            $filter .= "AND EFS.ForensicSubType_id=:ForensicSubType_id";
        }
        //@TODO: Добавить подзапрос для поля "Оценка вреда здоровью/ определение половых состояний/ определение возраста, рубцов"

        $query = "
			SELECT
			 -- select
				EFS.EvnForensicSub_id as \"EvnForensicSub_id\",
				EFS.EvnForensicSub_Num as \"EvnForensic_Num\", --№ п/п
				NULLIF (COALESCE(PS_all.Person_SurName, '') || COALESCE(' ' || PS_all.Person_FirName, '') || COALESCE(' ' || PS_all.Person_SecName, ''), '') AS \"Person_Fio\", --ФИО свидетельствуемого
				to_char(COALESCE(S.Sex_Name,'')) as \"Sex_Name\", --Пол
				to_char( PS_all.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\", --Дата рождения
				COALESCE(EFIP.ForensicIniciatorPost_Name,'') || ' ' || COALESCE(Iniciator.Person_Fio,'') as \"Iniciator\", -- Инициатор экспертизы
				to_char( EFS.EvnForensicSub_ExpertiseDT, 'dd/mm/yyyy') as \"EvnForensicSub_ExpertiseDT\", -- Дата проведения экспертизы
				to_char( EFS.EvnForensicSub_AccidentDT, 'dd.mm.yyyy')||' '||to_char( EFS.EvnForensicSub_AccidentDT, 'hh24:mi') as \"EvnForensicSub_AccidentDT\", --Время происшествия
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				EFS.EvnForensicSub_Receiver as \"EvnForensicSub_Receiver\",
				EFS.EvnForensicSub_Result as \"EvnForensicSub_Result\",
				EFS.EvnForensicSub_insDT as \"EvnForensicSub_insDT\",
				EFS.EvnForensicSub_Inherit as \"EvnForensicSub_Inherit\",
				EX.EvnXml_id as \"EvnXml_id\",
				CASE WHEN COALESCE(FSRW.ForensicSubReportWorking_id,0) = 0 
					THEN ''
					ELSE CASE WHEN COALESCE(FDSO.ForensicDefinitionSexualOffenses_id,0) != 0 
						THEN 'Определение половых состояний: '||FDSO.ForensicDefinitionSexualOffenses_Name
						ELSE CASE WHEN COALESCE(FVI.ForensicValuationInjury_id,0) != 0
							THEN 'Оценка вреда здоровью: '||FVI.ForensicValuationInjury_Name
							ELSE CASE WHEN COALESCE(FSD.ForensicSubDefinition_id,0) != 0
								THEN 'Определение: '||FSD.ForensicSubDefinition_Name
								ELSE ''
							END
						END
					END
				END AS \"ForensicSubReportWorking_Text\" -- оценка состояний
				
			-- end select
			FROM
			-- from
				v_EvnForensicSub EFS
				LEFT JOIN v_ForensicEvnXmlVersion FEXV1 ON FEXV1.EvnForensic_id = EFS.EvnForensicSub_id
                LEFT JOIN v_ForensicEvnXmlVersion FEXV2 ON FEXV2.EvnForensic_id = FEXV1.EvnForensic_id AND FEXV2.ForensicEvnXmlVersion_Num > FEXV1.ForensicEvnXmlVersion_Num
				left join v_EvnXml EX on FEXV1.EvnXml_id = EX.EvnXml_id
				
				LEFT JOIN v_ForensicSubReportWorking FSRW on FSRW.EvnForensicSub_id = EFS.EvnForensicSub_id
				LEFT JOIN v_ForensicDefinitionSexualOffenses FDSO on FSRW.ForensicDefinitionSexualOffenses_id = FDSO.ForensicDefinitionSexualOffenses_id
				LEFT JOIN v_ForensicValuationInjury FVI on FSRW.ForensicValuationInjury_id = FVI.ForensicValuationInjury_id
				LEFT JOIN v_ForensicSubDefinition FSD on FSRW.ForensicSubDefinition_id = FSD.ForensicSubDefinition_id

				left join lateral (
					SELECT
						MP.Person_Fin as Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EFS.MedPersonal_eid
					LIMIT 1
				) as MP on true
				left join v_PersonState_all PS_all on EFS.PersonEvn_id = PS_all.PersonEvn_id and EFS.Server_id = PS_all.Server_id
				left join v_Sex S on S.Sex_id = PS_all.Sex_id

				left join v_ForensicIniciatorPost EFIP on EFIP.ForensicIniciatorPost_id = EFS.ForensicIniciatorPost_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFS.EvnStatus_id )
				
				left join lateral (
					SELECT
						P.Person_Fio as Person_Fio
					FROM
						v_Person_all P
					WHERE
						P.Person_id = EFS.Person_cid
					LIMIT 1
				) as Iniciator on true
				{$whereClause}
			-- end from
			WHERE
			-- where
				{$filter}
			-- end where
			ORDER BY
			-- order by
				EFS.EvnForensicSub_Num DESC
			-- end order by
		";

        $result = array();
        //echo(getDebugSQL($query,$queryParams)); exit;
        $count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $result['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);

        if (!$this->isSuccessful($data_result))
        {
            return $data_result;
        } else {
            $result['data']=$data_result;
        }

        return $result;

    }
    /**
     * Функция сохранения результата направления на дополнительные материалы в службу судмедэкспертизы потерпевших/обвиняемых
     * @return boolean
     */
    public function saveEvnForenSubDopMatQueryResult($data) {
        $rules = array(
            array('field' => 'EvnForensicSubDopMatQuery_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id'),
            array('field' => 'EvnForensicSubDopMatQuery_ResultDT','label' => 'Дата получения','rules' => 'required','type' => 'string'),
            array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $queryData = $this->getEvnForenSubDopMatQueryResult($data);
        if (!$this->isSuccessful($queryData)) {
            return $queryData;
        }
        if (sizeof($queryData) != 1) {
            return $this->createError('', 'Ошибка получения направления');
        }

        $queryData = array_merge($queryData[0],$queryParams);
        $params = '';
        foreach ($queryData as $key => $value) {
            $params .= "{$key} := :{$key},";
        }
        $query = "
			select
				EvnForensicSubDopMatQuery_id as \":EvnForensicSubDopMatQuery_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicSubDopMatQuery_upd (
				EvnForensicSubDopMatQuery_id := :EvnForensicSubDopMatQuery_id,
				EvnForensicSubDopMatQuery_pid := :EvnForensicSubDopMatQuery_pid,
				EvnForensicSubDopMatQuery_rid := :EvnForensicSubDopMatQuery_rid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnForensicSubDopMatQuery_setDT := :EvnForensicSubDopMatQuery_setDT,
				EvnForensicSubDopMatQuery_disDT := :EvnForensicSubDopMatQuery_disDT,
				EvnForensicSubDopMatQuery_didDT := :EvnForensicSubDopMatQuery_didDT,
				EvnForensicSubDopMatQuery_insDT := :EvnForensicSubDopMatQuery_insDT,
				EvnForensicSubDopMatQuery_updDT := :EvnForensicSubDopMatQuery_updDT,
				EvnForensicSubDopMatQuery_Index := :EvnForensicSubDopMatQuery_Index,
				EvnForensicSubDopMatQuery_Count := :EvnForensicSubDopMatQuery_Count,
				Morbus_id := :Morbus_id,
				EvnForensicSubDopMatQuery_IsSigned := :EvnForensicSubDopMatQuery_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnForensicSubDopMatQuery_signDT := :EvnForensicSubDopMatQuery_signDT,
				EvnStatus_id := :EvnStatus_id,
				EvnForensicSubDopMatQuery_statusDate := :EvnForensicSubDopMatQuery_statusDate,
				EvnForensicSubDopMatQuery_Num := :EvnForensicSubDopMatQuery_Num,
				EvnForensicSubDopMatQuery_ResDate := :EvnForensicSubDopMatQuery_ResDate,
				MedService_id := :MedService_id,
				MedService_pid := :MedService_pid,
				EvnForensicSubDopMatQuery_IsDistrict := :EvnForensicSubDopMatQuery_IsDistrict,
				Person_gid := :Person_gid,
				EvnForensicSubDopMatQuery_ResultOutDT := :EvnForensicSubDopMatQuery_ResultOutDT,
				Person_cid := :Person_cid,
				RecipientIdentity_Num := :RecipientIdentity_Num,
				PostTicket_Num := :PostTicket_Num,
				PostTicket_Date := :PostTicket_Date,
				EvnForensicSubDopMatQuery_Goal := :EvnForensicSubDopMatQuery_Goal,
				EvnForensicSubDopMatQuery_Facts := :EvnForensicSubDopMatQuery_Facts,
				EvnForensicSubDopMatQuery_AccidentDT := :EvnForensicSubDopMatQuery_AccidentDT,
				EvnForensicSubDopMatQuery_Name := :EvnForensicSubDopMatQuery_Name,
				Org_id := :Org_id,
				EvnForensicSubDopMatQuery_ResearchDate := :EvnForensicSubDopMatQuery_ResearchDate,
				Person_aid := :Person_aid,
				EvnForensicSubDopMatQuery_ResultDT := :EvnForensicSubDopMatQuery_ResultDT,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query,$queryData);
    }
    /**
     * Функция удаления направления на на дополнительные материалы в службу судмедэкспертизы потерпевших/обвиняемых
     * @return boolean
     */
    public function deleteEvnForenSubDopMatQuery($data) {
        $rules = array(
            array('field' => 'EvnForensicSubDopMatQuery_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id'),
            array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;
        $query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicSubDopMatQuery_del (
				EvnForensicSubDopMatQuery_id := :EvnForensicSubDopMatQuery_id,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query,$queryParams);
    }
    /**
     * Функция получения всех данных по направлению
     * @param type $data
     */
    public function getEvnForenSubDopMatQueryResult($data) {
        $rules = array(
            array('field' => 'EvnForensicSubDopMatQuery_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;
        $query = "
			SELECT
				EFSDMQ.*
			FROM
				v_EvnForensicSubDopMatQuery EFSDMQ
			WHERE
				EFSDMQ.EvnForensicSubDopMatQuery_id = :EvnForensicSubDopMatQuery_id
		";

        return $this->queryResult($query,$queryParams);

    }

    /**
     * Получение данных для экспорта в dbf
     */
    public function exportEvnForensicSubDirToDbf($data) {
        $rules = array(
            array('field' => 'MedService_id','label' => '','rules' => 'required','type' => 'id'),
            array('field' => 'begDate','label' => '','rules' => 'required','type' => 'string'),
            array('field' => 'endDate','label' => '','rules' => 'required','type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			Select distinct
				EvnFS.EvnForensicSub_Num as \"Num\", --№ ПП
				rtrim(Person.Person_SurName) as \"FamSved\",
				rtrim(Person.Person_FirName) as \"ImSved\",
				rtrim(Person.Person_SecName) as \"OtchSved\",
				(to_char(Person.Person_BirthDay,'dd.mm.yyyy'))||' г.' as \"BirthDay\", --дата рождения
				Sex.Sex_Name as \"SexName\",  -- Название пола
				post.post_name as \"PostName\", -- звание  должности
				Adrs.Address_nick as \"Address\",-- адрес проживания
				COALESCE(KLS.KLAdr_Code, KLA.KLAdr_Code) as \"KladrCode\",
				rtrim(Post3.Name) as \"PostOtNaEx\",			--Должность отправившего на экспертизу
				rtrim(Person2.Person_SurName) as \"FamOtNaEx\",
				rtrim(Person2.Person_FirName) as \"ImOtNaEx\",
				rtrim(Person2.Person_SecName) as \"OtchOtNaEx\",
				Org.Org_Nick as \"OrgNapr\", --Направившая организация
				to_char(AVF2.ActVersionForensic_insDT,'dd.mm.yyyy') as \"ActExDate\" ,	-- Дата проведения экспертизы
				to_char(EvnFS.EvnForensicSub_AccidentDT,'hh24:mi:ss') as \"PrestTime\",	-- Время совершения преступления
				to_char(EvnFS.EvnForensicSub_AccidentDT,'dd.mm.yyyy') as \"PrestDate\", -- Дата совершения преступления
				EvnFT.EvnForensicType_Name as \"ExpertType\",	-- Тип экпертизы
				AVF2.ActVersionForensic_Text as \"ActText\",	--Результаты экспертизы
				AVF2.ActVersionForensic_Num as \"ActNum\",	--Номер «Заключения Эксперта» «Акта»
				rtrim(Person3.Person_SurName) as \"FamExpert\",
				rtrim(Person3.Person_FirName) as \"ImExpert\",
				rtrim(Person3.Person_SecName) as \"OtchExpert\",

				rtrim(Person4.Person_SurName) as \"FamRecip\",
				rtrim(Person4.Person_FirName) as \"ImRecip\",
				rtrim(Person4.Person_SecName) as \"OtchRecip\",
				rtrim(EvnFS.RecipientIdentity_Num) as \"NumRecip\",
				rtrim(EvnFS.PostTicket_Num) as \"TicketNum\",
				to_char(EvnFS.PostTicket_Date,'dd.mm.yyyy') as \"TicketDate\"
			From v_EvnForensicSub EvnFS
				left join v_PersonEvn Pevn on Pevn.PersonEvn_id =EvnFS.PersonEvn_id
				left join v_PersonState Person on Person.Person_id =Pevn.Person_id
				Left join Sex Sex on Sex.Sex_id =Person.Sex_id
				Left join v_Job Job on Job.Job_id =Person.Job_id
				Left join v_post post on post.post_id =Job.post_id
				Left join Address Adrs on Adrs.Address_id =Person.PAddress_id
				left join v_KLStreet KLS on KLS.KLStreet_id = Adrs.KLStreet_id
				left join v_KLArea KLA on KLA.KLArea_id = coalesce(Adrs.KLTown_id, Adrs.KLCity_id)
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EvnFS.EvnStatus_id )
				left join v_EvnForensicSubDir EvnFSD  on EvnFSD.EvnForensicSubDir_id = EvnFS.EvnForensicSub_id
				left join Org Org on Org.org_id = EvnFSD.org_did
				left join v_PersonState Person2  on Person2.Person_id  = EvnFSD.Person_cid
				Left join v_Job Job3 on Job3.Job_id =Person2.Job_id
				Left join Persis.post post3 on post3.id =Job3.post_id
				left join v_EvnDirectionForensic EvnDF on EvnDF.EvnForensic_id =EvnFS.EvnForensicSub_id
				left join EvnForensicType EvnFT on EvnFT.EvnForensicType_id =EvnDF.EvnForensicType_id
				left join persis.MedWorker MedPer on MedPer.id =EvnDF.MedPersonal_id
				left join v_PersonState Person3 on Person3.Person_id =MedPer.Person_id
				left join v_PersonState Person4 on Person4.Person_id =EvnFs.Person_gid
				Left join v_Job Job2 on Job2.Job_id =Person4.Job_id
				Left join Persis.post post2 on post2.id =Job2.post_id
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text,
						AVF.ActVersionForensic_insDT
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EvnFS.EvnForensicSub_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF2 ON TRUE
			Where
				(EvnFS.MedService_id=:MedService_id)
				and (cast(EvnFS.EvnForensicSub_insDT as date) between :begDate AND :endDate)
				AND EvnFS.EvnClass_id = 123
				AND ES.EvnStatus_SysNick = 'Done'
		";
        //die(getDebugSQL($query,$queryParams));
        $result = $this->queryResult($query, $queryParams);
        if (!$this->isSuccessful($result)) {
            return $this->createError('', 'Ошибка при получении данных для экспорта');
        }
        return $result;
    }

    /**
     * Получение данных для экспорта в dbf
     */
    public function exportEvnForensicSubInspToDbf($data) {
        $rules = array(
            array('field' => 'MedService_id','label' => '','rules' => 'required','type' => 'id'),
            array('field' => 'begDate','label' => '','rules' => 'required','type' => 'string'),
            array('field' => 'endDate','label' => '','rules' => 'required','type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			Select distinct
				EvnFS.EvnForensicSub_Num as \"Num\", --№ ПП
				rtrim(Person.Person_SurName) as \"FamSved\",
				rtrim(Person.Person_FirName) as \"ImSved\",
				rtrim(Person.Person_SecName) as \"OtchSved\",
				(to_char(Person.Person_BirthDay,'dd.mm.yyyy'))||' г.' as \"BirthDay\", --дата рождения
				Sex.Sex_Name as \"SexName\",  -- Название пола
				post.post_name as \"PostName\", -- звание  должности
				Adrs.Address_nick as \"Address\",-- адрес проживания
				COALESCE(KLS.KLAdr_Code, KLA.KLAdr_Code) as \"KladrCode\",
				rtrim(Post3.Name) as \"PostOtNaEx\",			--Должность отправившего на экспертизу
				rtrim(Person2.Person_SurName) as \"FamOtNaEx\",
				rtrim(Person2.Person_FirName) as \"ImOtNaEx\",
				rtrim(Person2.Person_SecName) as \"OtchOtNaEx\",
				Org.Org_Nick as \"OrgNapr\", --Направившая организация
				to_char(AVF2.ActVersionForensic_insDT,'dd.mm.yyyy') as \"ActExDate\" ,	-- Дата проведения экспертизы
				to_char(EvnFS.EvnForensicSub_AccidentDT,'hh24:mi:ss') as \"PrestTime\",	-- Время совершения преступления
				to_char(EvnFS.EvnForensicSub_AccidentDT,'dd.mm.yyyy') as \"PrestDate\", -- Дата совершения преступления
				EvnFT.EvnForensicType_Name as \"ExpertType\",	-- Тип экпертизы
				AVF2.ActVersionForensic_Text as \"ActText\",	--Результаты экспертизы
				AVF2.ActVersionForensic_Num as \"ActNum\",	--Номер «Заключения Эксперта» «Акта»
				rtrim(Person3.Person_SurName) as \"FamExpert\",
				rtrim(Person3.Person_FirName) as \"ImExpert\",
				rtrim(Person3.Person_SecName) as \"OtchExpert\",

				rtrim(Person4.Person_SurName) as \"FamRecip\",
				rtrim(Person4.Person_FirName) as \"ImRecip\",
				rtrim(Person4.Person_SecName) as \"OtchRecip\",
				rtrim(EvnFS.RecipientIdentity_Num) as \"NumRecip\",
				rtrim(EvnFS.PostTicket_Num) as \"TicketNum\",
				to_char(EvnFS.PostTicket_Date,'dd.mm.yyyy') as \"TicketDate\"
			From v_EvnForensicSub EvnFS
				left join v_PersonEvn Pevn on Pevn.PersonEvn_id =EvnFS.PersonEvn_id
				--left join v_Person Person3  on Person3.Server_id = EvnFS.Server_id
				left join v_PersonState Person  on Person.Person_id =Pevn.Person_id
				Left join Sex Sex  on Sex.Sex_id =Person.Sex_id
				Left join v_Job Job  on Job.Job_id =Person.Job_id
				Left join v_post post  on post.post_id =Job.post_id
				Left join Address Adrs  on Adrs.Address_id =Person.PAddress_id
				left join v_KLStreet KLS  on KLS.KLStreet_id = Adrs.KLStreet_id
				left join v_KLArea KLA  on KLA.KLArea_id = coalesce(Adrs.KLTown_id, Adrs.KLCity_id)
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EvnFS.EvnStatus_id )
				--left join v_PersonState Person2  on Person2.Person_id =EvnFS.Person_cid
				--left join EvnForensicSubDir EvnFSD  on EvnFSD.EvnForensicSub_id = EvnFS.EvnForensicSub_id
				--left join Org Org  on Org.org_id = EvnFSD.org_did
				left join v_EvnForensicSubInsp EvnFSD  on EvnFSD.EvnForensicSubInsp_id = EvnFS.EvnForensicSub_id
				left join Org Org  on Org.org_id = EvnFSD.org_did
				left join v_PersonState Person2  on Person2.Person_id  = EvnFSD.Person_cid
				Left join v_Job Job3  on Job3.Job_id =Person2.Job_id
				Left join Persis.post post3  on post3.id =Job3.post_id
				left join v_EvnDirectionForensic EvnDF  on EvnDF.EvnForensic_id =EvnFS.EvnForensicSub_id
				left join EvnForensicType EvnFT  on EvnFT.EvnForensicType_id =EvnDF.EvnForensicType_id
				left join persis.MedWorker MedPer  on MedPer.id =EvnDF.MedPersonal_id
				left join v_PersonState Person3  on Person3.Person_id =MedPer.Person_id
				left join v_PersonState Person4  on Person4.Person_id =EvnFs.Person_gid
				Left join v_Job Job2  on Job2.Job_id =Person4.Job_id
				Left join Persis.post post2  on post2.id =Job2.post_id
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text,
						AVF.ActVersionForensic_insDT
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EvnFS.EvnForensicSub_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF2 ON TRUE
			Where
				(EvnFS.MedService_id=:MedService_id)
				and (cast(EvnFS.EvnForensicSub_insDT as date) between :begDate AND :endDate)
				AND EvnFS.EvnClass_id = 124
				AND ES.EvnStatus_SysNick = 'Done'
		";
        //die(getDebugSQL($query,$queryParams));
        $result = $this->queryResult($query, $queryParams);
        if (!$this->isSuccessful($result)) {
            return $this->createError('', 'Ошибка при получении данных для экспорта');
        }
        return $result;
    }

    /**
     * Получение данных для экспорта в dbf
     */
    public function exportEvnForensicSubOwnToDbf($data) {
        $rules = array(
            array('field' => 'MedService_id','label' => '','rules' => 'required','type' => 'id'),
            array('field' => 'begDate','label' => '','rules' => 'required','type' => 'string'),
            array('field' => 'endDate','label' => '','rules' => 'required','type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			Select distinct
				EvnFS.EvnForensicSub_Num as \"Num\", --№ ПП
				rtrim(Person.Person_SurName) as \"FamSved\",
				rtrim(Person.Person_FirName) as \"ImSved\",
				rtrim(Person.Person_SecName) as \"OtchSved\",
				(to_char(Person.Person_BirthDay,'dd.mm.yyyy'))||' г.' as \"BirthDay\", --дата рождения
				Sex.Sex_Name as \"SexName\",  -- Название пола
				post.post_name as \"PostName\", -- звание  должности
				Adrs.Address_nick as \"Address\",-- адрес проживания
				COALESCE(KLS.KLAdr_Code, KLA.KLAdr_Code) as \"KladrCode\",
				--rtrim(coalesce(' '||Post3.Name, ''))||rtrim(coalesce(' '||Person2.Person_SurName, ''))|| rtrim(coalesce(' '||Person2.Person_FirName, ''))||rtrim(coalesce(' '||Person2.Person_SecName, '')) as \"FioOtNaEx\", ---ФИО отправившего на экспертизу
				--Org.Org_Nick as \"Org_Nick\", --Направившая организация
				to_char(AVF2.ActVersionForensic_insDT,'dd.mm.yyyy') as \"ActExDate\" ,	-- Дата проведения экспертизы
				to_char(EvnFS.EvnForensicSub_AccidentDT,'hh24:mi:ss') as \"PrestTime\",	-- Время совершения преступления
				to_char(EvnFS.EvnForensicSub_AccidentDT,'dd.mm.yyyy') as \"PrestDate\", -- Дата совершения преступления
				EvnFT.EvnForensicType_Name as \"ExpertType\",	-- Тип экпертизы
				AVF2.ActVersionForensic_Text as \"ActText\",	--Результаты экспертизы
				AVF2.ActVersionForensic_Num as \"ActNum\",	--Номер «Заключения Эксперта» «Акта»
				rtrim(Person3.Person_SurName) as \"FamExpert\",
				rtrim(Person3.Person_FirName) as \"ImExpert\",
				rtrim(Person3.Person_SecName) as \"OtchExpert\",

				rtrim(Person4.Person_SurName) as \"FamRecip\",
				rtrim(Person4.Person_FirName) as \"ImRecip\",
				rtrim(Person4.Person_SecName) as \"OtchRecip\",
				rtrim(EvnFS.RecipientIdentity_Num) as \"NumRecip\",
				rtrim(EvnFS.PostTicket_Num) as \"TicketNum\",
				to_char(EvnFS.PostTicket_Date,'dd.mm.yyyy') as \"TicketDate\"
			From v_EvnForensicSub EvnFS
				left join v_PersonEvn Pevn on Pevn.PersonEvn_id =EvnFS.PersonEvn_id
				left join v_PersonState Person on Person.Person_id =Pevn.Person_id
				Left join Sex Sex on Sex.Sex_id =Person.Sex_id
				Left join v_Job Job on Job.Job_id =Person.Job_id
				Left join v_post post on post.post_id =Job.post_id
				Left join Address Adrs on Adrs.Address_id =Person.PAddress_id
				left join v_KLStreet KLS on KLS.KLStreet_id = Adrs.KLStreet_id
				left join v_KLArea KLA on KLA.KLArea_id = coalesce(Adrs.KLTown_id, Adrs.KLCity_id)
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EvnFS.EvnStatus_id )
				left join v_EvnDirectionForensic EvnDF on EvnDF.EvnForensic_id =EvnFS.EvnForensicSub_id
				left join EvnForensicType EvnFT on EvnFT.EvnForensicType_id =EvnDF.EvnForensicType_id
				left join persis.MedWorker MedPer on MedPer.id =EvnDF.MedPersonal_id
				left join v_PersonState Person3 on Person3.Person_id =MedPer.Person_id
				left join v_PersonState Person4 on Person4.Person_id =EvnFs.Person_gid
				Left join v_Job Job2 on Job2.Job_id =Person4.Job_id
				Left join Persis.post post2 on post2.id =Job2.post_id
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text,
						AVF.ActVersionForensic_insDT
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EvnFS.EvnForensicSub_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF2 ON TRUE
			Where
				(EvnFS.MedService_id=:MedService_id)
				and (cast(EvnFS.EvnForensicSub_insDT as date) between :begDate AND :endDate)
				AND EvnFS.EvnClass_id = 125
				AND ES.EvnStatus_SysNick = 'Done'
		";
        //die(getDebugSQL($query,$queryParams));
        $result = $this->queryResult($query, $queryParams);
        if (!$this->isSuccessful($result)) {
            return $this->createError('', 'Ошибка при получении данных для экспорта');
        }
        return $result;
    }

    /**
     * Получение данных для экспорта в dbf
     */
    public function exportEvnForensicSubDocToDbf($data) {
        $rules = array(
            array('field' => 'MedService_id','label' => '','rules' => 'required','type' => 'id'),
            array('field' => 'begDate','label' => '','rules' => 'required','type' => 'string'),
            array('field' => 'endDate','label' => '','rules' => 'required','type' => 'string'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			Select distinct
				EvnFS.EvnForensicSub_Num as \"Num\", --№ ПП
				rtrim(Person.Person_SurName) as \"FamSved\",
				rtrim(Person.Person_FirName) as \"ImSved\",
				rtrim(Person.Person_SecName) as \"OtchSved\",
				(to_char(Person.Person_BirthDay,'dd.mm.yyyy'))||' г.' as \"BirthDay\", --дата рождения
				Sex.Sex_Name as \"SexName\",  -- Название пола
				post.post_name as \"PostName\", -- звание  должности
				Adrs.Address_nick as \"Address\",-- адрес проживания
				COALESCE(KLS.KLAdr_Code, KLA.KLAdr_Code) as \"KladrCode\",
				rtrim(Post3.Name) as \"PostOtNaEx\",			--Должность отправившего на экспертизу
				rtrim(Person2.Person_SurName) as \"FamOtNaEx\",
				rtrim(Person2.Person_FirName) as \"ImOtNaEx\",
				rtrim(Person2.Person_SecName) as \"OtchOtNaEx\",
				Org.Org_Nick as \"OrgNapr\", --Направившая организация
				to_char(AVF2.ActVersionForensic_insDT,'dd.mm.yyyy') as \"ActExDate\" ,	-- Дата проведения экспертизы
				to_char(EvnFS.EvnForensicSub_AccidentDT,'hh24:mi:ss') as \"PrestTime\",	-- Время совершения преступления
				to_char(EvnFS.EvnForensicSub_AccidentDT,'dd.mm.yyyy') as \"PrestDate\", -- Дата совершения преступления
				EvnFT.EvnForensicType_Name as \"ExpertType\",	-- Тип экпертизы
				AVF2.ActVersionForensic_Text as \"ExpertType\",	--Результаты экспертизы
				AVF2.ActVersionForensic_Num as \"ExpertNum\",	--Номер «Заключения Эксперта» «Акта»
				rtrim(Person3.Person_SurName) as \"FamExpert\",
				rtrim(Person3.Person_FirName) as \"ImExpert\",
				rtrim(Person3.Person_SecName) as \"OtchExpert\",

				rtrim(Person4.Person_SurName) as \"FamRecip\",
				rtrim(Person4.Person_FirName) as \"ImRecip\",
				rtrim(Person4.Person_SecName) as \"OtchRecip\",
				rtrim(EvnFS.RecipientIdentity_Num) as \"NumRecip\",
				rtrim(EvnFS.PostTicket_Num) as \"TicketNum\",
				to_char(EvnFS.PostTicket_Date,'dd.mm.yyyy') as \"TicketDate\"
			From v_EvnForensicSub EvnFS
				left join v_PersonEvn Pevn on Pevn.PersonEvn_id =EvnFS.PersonEvn_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EvnFS.EvnStatus_id )
				--left join v_Person Person3 on Person3.Server_id = EvnFS.Server_id
				left join v_PersonState Person on Person.Person_id =Pevn.Person_id
				Left join Sex Sex on Sex.Sex_id =Person.Sex_id
				Left join v_Job Job on Job.Job_id =Person.Job_id
				--Left join Persis.post post on post.id =Job.post_id
				Left join v_post post on post.post_id =Job.post_id
				Left join Address Adrs on Adrs.Address_id =Person.PAddress_id
				left join v_KLStreet KLS on KLS.KLStreet_id = Adrs.KLStreet_id
				left join v_KLArea KLA on KLA.KLArea_id = coalesce(Adrs.KLTown_id, Adrs.KLCity_id)
				--left join v_PersonState Person2 on Person2.Person_id =EvnFS.Person_cid
				--left join EvnForensicSubDir EvnFSD on EvnFSD.EvnForensicSub_id = EvnFS.EvnForensicSub_id
				--left join Org Org on Org.org_id = EvnFSD.org_did
				left join v_EvnForensicSubDoc EvnFSD on EvnFSD.EvnForensicSubDoc_id = EvnFS.EvnForensicSub_id
				left join Org Org on Org.org_id = EvnFSD.org_did
				left join v_PersonState Person2 on Person2.Person_id  = EvnFSD.Person_cid
				Left join v_Job Job3 on Job3.Job_id =Person2.Job_id
				Left join Persis.post post3 on post3.id =Job3.post_id
				left join v_EvnDirectionForensic EvnDF on EvnDF.EvnForensic_id =EvnFS.EvnForensicSub_id
				left join EvnForensicType EvnFT on EvnFT.EvnForensicType_id =EvnDF.EvnForensicType_id
				left join persis.MedWorker MedPer on MedPer.id =EvnDF.MedPersonal_id
				left join v_PersonState Person3 on Person3.Person_id =MedPer.Person_id
				left join v_PersonState Person4 on Person4.Person_id =EvnFs.Person_gid
				Left join v_Job Job2 on Job2.Job_id =Person4.Job_id
				Left join Persis.post post2 on post2.id =Job2.post_id
				left join lateral (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text,
						AVF.ActVersionForensic_insDT
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EvnFS.EvnForensicSub_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF2 ON TRUE
			Where
				(EvnFS.MedService_id=:MedService_id)
				and (cast(EvnFS.EvnForensicSub_insDT as date) between :begDate AND :endDate)
				AND EvnFS.EvnClass_id = 126
				AND ES.EvnStatus_SysNick = 'Done'
		";
        //die(getDebugSQL($query,$queryParams));
        $result = $this->queryResult($query, $queryParams);
        if (!$this->isSuccessful($result)) {
            return $this->createError('', 'Ошибка при получении данных для экспорта');
        }
        return $result;
    }

    /**
     * Функция получения данных запроса на получение дополнительных документов службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @return boolean
     */
    public function getEvnForensicSubDopDocQuery($data) {
        $rules = array(
            array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubDopDocQuery_id', 'label' => 'Идентификатор запроса документа', 'rules' => '', 'type' => 'id', 'default'=>NULL),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        if ($data['EvnForensicSubDopDocQuery_id']) {

            $query = "
				SELECT
					EvnForensicSub_id as \"EvnForensicSub_id\",
					EvnForensicSubDopDocQuery_Num as \"EvnForensicSubDopDocQuery_Num\",
					to_char(EvnForensicSubDopDocQuery_Date, 'dd.mm.yyyy') as \"EvnForensicSubDopDocQuery_Date\",
					EvnForensicSubDopDocQuery_Iniciator as \"EvnForensicSubDopDocQuery_Iniciator\",
					EvnForensicSubDopDocQuery_IniciatorJob as \"EvnForensicSubDopDocQuery_IniciatorJob\",
					EvnForensicSubDopDocQuery_Person as \"EvnForensicSubDopDocQuery_Person\",
					EvnForensicSubDopDocQuery_Subject as \"EvnForensicSubDopDocQuery_Subject\"
				FROM
					v_EvnForensicSubDopDocQuery EFSDDQ
				WHERE
					EvnForensicSubDopDocQuery_id = :EvnForensicSubDopDocQuery_id
			";

        } elseif ($data['EvnForensicSub_id']) {

            $query = "
				SELECT
					COALESCE((SELECT MAX(COALESCE(EFSDDQ.EvnForensicSubDopDocQuery_Num,0)+1) as EvnForensicSubDopDocQuery_Num FROM v_EvnForensicSubDopDocQuery EFSDDQ ),1) as \"EvnForensicSubDopDocQuery_Num\",
					to_char( dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnForensicSubDopDocQuery_Date\",
					P.Person_Fio as \"EvnForensicSubDopDocQuery_Person\",
					COALESCE(rtrim(ltrim(PS.Person_SurName)), '') || COALESCE(' ' || rtrim(ltrim(PS.Person_FirName)), '') || COALESCE(' ' || rtrim(ltrim(PS.Person_SecName)), '') AS \"EvnForensicSubDopDocQuery_Iniciator\",
					COALESCE(EFIP.ForensicIniciatorPost_Name,'') || ' ' || COALESCE(O.Org_Nick,'')  as \"EvnForensicSubDopDocQuery_IniciatorJob\"

					--,EvnForensicSubDopDocQuery_IniciatorJob
					
				
				FROM
					v_EvnForensicSub EFS
					left join v_Person_all P on P.PersonEvn_id = EFS.PersonEvn_id AND P.Server_id = EFS.Server_id
					left join v_PersonState PS on PS.Person_id = EFS.Person_cid
					
					left join v_ForensicIniciatorPost EFIP on EFIP.ForensicIniciatorPost_id = EFS.ForensicIniciatorPost_id
					Left join v_Org O on O.Org_id = EFS.Org_did
	
				WHERE
					EFS.EvnForensicSub_id = :EvnForensicSub_id
					
			";

        } else {
            return $this->createError('', 'Не переданы необходимые идентификаторы');
        }

        return $this->queryResult($query, $queryParams);

    }

    /**
     * Функция сохранения запроса на получение дополнительных документов службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @return boolean
     */
    public function saveEvnForensicSubDopDocQuery($data) {
        $rules = array(
            array('field' => 'EvnForensicSubDopDocQuery_id', 'label' => 'Идентификатор запроса документа', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubDopDocQuery_Num', 'label' => 'Номер запроса', 'rules' => 'max_length[128]', 'type' => 'string', 'default'=>NULL),
            array('field' => 'EvnForensicSubDopDocQuery_Date', 'label' => 'Дата заявки', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicSubDopDocQuery_Iniciator','label' => 'Кому (ФИО)','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopDocQuery_IniciatorJob','label' => 'Кому (Должность, место работы)','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopDocQuery_Person','label' => 'Подэкспертный','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopDocQuery_Subject','label' => 'Что предоставить','rules' => 'required, max_length[512]','type' => 'string'),
            array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $proc = 'p_EvnForensicSubDopDocQuery_ins';
        if ($data['EvnForensicSubDopDocQuery_id']) {
            $proc = 'p_EvnForensicSubDopDocQuery_upd';

        }

        $query = "
			select
				EvnForensicSubDopDocQuery_id as \"EvnForensicSubDopDocQuery_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				EvnForensicSubDopDocQuery_id := :EvnForensicSubDopDocQuery_id,
				EvnForensicSub_id := :EvnForensicSub_id,
				EvnForensicSubDopDocQuery_Num := :EvnForensicSubDopDocQuery_Num,
				EvnForensicSubDopDocQuery_Date := :EvnForensicSubDopDocQuery_Date,
				EvnForensicSubDopDocQuery_Iniciator := :EvnForensicSubDopDocQuery_Iniciator,
				EvnForensicSubDopDocQuery_IniciatorJob := :EvnForensicSubDopDocQuery_IniciatorJob,
				EvnForensicSubDopDocQuery_Person := :EvnForensicSubDopDocQuery_Person,
				EvnForensicSubDopDocQuery_Subject := :EvnForensicSubDopDocQuery_Subject,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query, $queryParams);
    }

    /**
     * Получение списка заявок дополнительных документов
     * @param type $data
     * @return type
     */
    protected function _getEvnForensicSubDopDocRequestList($data) {
        $rules = array(
            array('field'=>'EvnForensicSub_id', 'rules'=>'required','label'=>'Идентификатор заявки', 'type' => 'id')
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;
        $query = "
			SELECT
				EFSDDQ.EvnForensicSubDopDocQuery_id as \"EvnForensicSubDopDocQuery_id\",
				COALESCE(EFSDDQ.EvnForensicSubDopDocQuery_Num,'') as \"EvnForensicSubDopDocQuery_Num\",
				to_char(EFSDDQ.EvnForensicSubDopDocQuery_insDT, 'dd.mm.yyyy')||' '||to_char(EFSDDQ.EvnForensicSubDopDocQuery_insDT, 'hh24:mi') as \"EvnForensicSubDopDocQuery_insDT\"
			FROM
				v_EvnForensicSubDopDocQuery EFSDDQ
			WHERE
				EFSDDQ.EvnForensicSub_id =:EvnForensicSub_id
		";

        return $this->queryResult($query,$queryParams);
    }


    /**
     * Функция удаления запроса на участие службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @param type $data
     * @return type
     */
    public function deleteEvnForenSubDopDocQuery($data) {
        $rules = array(
            array('field' => 'EvnForensicSubDopDocQuery_id', 'label' => 'Идентификатор запроса документа', 'rules' => 'required', 'type' => 'id', 'default'=>NULL),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicSubDopDocQuery_del (
				EvnForensicSubDopDocQuery_id := :EvnForensicSubDopDocQuery_id
			);
		";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Функция получения данных запроса на участие службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @return boolean
     */
    public function getEvnForensicSubDopPersQuery($data) {
        $rules = array(
            array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubDopPersQuery_id', 'label' => 'Идентификатор запроса на участие', 'rules' => '', 'type' => 'id', 'default'=>NULL),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        if ($data['EvnForensicSubDopPersQuery_id']) {

            $query = "
				SELECT
					EFSDPQ.EvnForensicSub_id as \"EvnForensicSub_id\",
					EFSDPQ.EvnForensicSubDopPersQuery_Num as \"EvnForensicSubDopPersQuery_Num\",
					to_char( EFSDPQ.EvnForensicSubDopPersQuery_Date, 'dd.mm.yyyy') as \"EvnForensicSubDopPersQuery_Date\",
					EFSDPQ.EvnForensicSubDopPersQuery_Iniciator as \"EvnForensicSubDopPersQuery_Iniciator\",
					EFSDPQ.EvnForensicSubDopPersQuery_IniciatorJob as \"EvnForensicSubDopPersQuery_IniciatorJob\",
					EFSDPQ.EvnForensicSubDopPersQuery_ExpertFIO as \"EvnForensicSubDopPersQuery_ExpertFIO\",
					EFSDPQ.EvnForensicSubDopPersQuery_ExpertRole as \"EvnForensicSubDopPersQuery_ExpertRole\",
					EFSDPQ.EvnForensicSubDopPersQuery_Person as \"EvnForensicSubDopPersQuery_Person\",
					EFSDPQ.EvnForensicSubDopPersSubject_Goal as \"EvnForensicSubDopPersSubject_Goal\"
				FROM
					v_EvnForensicSubDopPersQuery EFSDPQ
				WHERE
					EFSDPQ.EvnForensicSubDopPersQuery_id = :EvnForensicSubDopPersQuery_id
			";

        } elseif ($data['EvnForensicSub_id']) {

            $query = "
				SELECT
					COALESCE((SELECT MAX(COALESCE(EFSDDQ.EvnForensicSubDopPersQuery_Num,0)+1) as EvnForensicSubDopPersQuery_Num FROM v_EvnForensicSubDopPersQuery EFSDDQ ),1) as \"EvnForensicSubDopPersQuery_Num\",
					to_char(dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnForensicSubDopPersQuery_Date\",
					P.Person_Fio as \"EvnForensicSubDopPersQuery_Person\",
					COALESCE(PS.Person_SurName, '') || COALESCE(' ' || PS.Person_FirName, '') || COALESCE(' ' || PS.Person_SecName, '') AS \"EvnForensicSubDopPersQuery_Iniciator\",
					COALESCE(EFIP.ForensicIniciatorPost_Name,'') || ' ' || COALESCE(O.Org_Nick,'')  as \"EvnForensicSubDopPersQuery_IniciatorJob\"
				
				FROM
					v_EvnForensicSub EFS
					left join v_Person_all P on P.PersonEvn_id = EFS.PersonEvn_id AND P.Server_id = EFS.Server_id
					left join v_PersonState PS on PS.Person_id = EFS.Person_cid
	
					left join v_ForensicIniciatorPost EFIP on EFIP.ForensicIniciatorPost_id = EFS.ForensicIniciatorPost_id
					Left join v_Org O on O.Org_id = EFS.Org_did
				WHERE
					EFS.EvnForensicSub_id = :EvnForensicSub_id
					
			";

        } else {
            return $this->createError('', 'Не переданы необходимые идентификаторы');
        }

        return $this->queryResult($query, $queryParams);

    }

    /**
     * Функция сохранения запроса на участие службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @return boolean
     */
    public function saveEvnForensicSubDopPersQuery($data) {
        $rules = array(
            array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubDopPersQuery_id', 'label' => 'Идентификатор запроса на участие', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubDopPersQuery_Num', 'label' => 'Номер запроса', 'rules' => 'max_length[128]', 'type' => 'string', 'default'=>NULL),
            array('field' => 'EvnForensicSubDopPersQuery_Date', 'label' => 'Дата заявки', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicSubDopPersQuery_Iniciator','label' => 'Кому (ФИО)','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopPersQuery_IniciatorJob','label' => 'Кому (Должность, место работы)','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopPersQuery_ExpertFIO','label' => 'Запрашиваемый эксперт','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopPersQuery_ExpertRole','label' => 'В качестве кого вызывается эксперт','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopPersQuery_Person','label' => 'Подэкспертный','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubDopPersSubject_Goal','label' => 'Цель запроса','rules' => 'required, max_length[512]','type' => 'string'),
            array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $proc = 'p_EvnForensicSubDopPersQuery_ins';
        if ($data['EvnForensicSubDopPersQuery_id']) {
            $proc = 'p_EvnForensicSubDopPersQuery_upd';

        }

        $query = "
			select
				EvnForensicSubDopPersQuery_id as \"EvnForensicSubDopPersQuery_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"		
			from {$proc} (
				EvnForensicSubDopPersQuery_id := :EvnForensicSubDopPersQuery_id,
				EvnForensicSub_id := :EvnForensicSub_id,
				EvnForensicSubDopPersQuery_Num := :EvnForensicSubDopPersQuery_Num,
				EvnForensicSubDopPersQuery_Date := :EvnForensicSubDopPersQuery_Date,
				EvnForensicSubDopPersQuery_Iniciator := :EvnForensicSubDopPersQuery_Iniciator,
				EvnForensicSubDopPersQuery_IniciatorJob := :EvnForensicSubDopPersQuery_IniciatorJob,
				EvnForensicSubDopPersQuery_Person := :EvnForensicSubDopPersQuery_Person,
				EvnForensicSubDopPersQuery_ExpertFIO :=:EvnForensicSubDopPersQuery_ExpertFIO,
				EvnForensicSubDopPersQuery_ExpertRole :=:EvnForensicSubDopPersQuery_ExpertRole,
				EvnForensicSubDopPersSubject_Goal :=:EvnForensicSubDopPersSubject_Goal,	
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query, $queryParams);
    }

    /**
     * Получение списка запросов на участие
     * @param type $data
     * @return type
     */
    protected function _getEvnForensicSubDopPersRequestList($data) {
        $rules = array(
            array('field'=>'EvnForensicSub_id', 'rules'=>'required','label'=>'Идентификатор на участие', 'type' => 'id')
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;
        $query = "
			SELECT
				EFSDDQ.EvnForensicSubDopPersQuery_id as \"EvnForensicSubDopPersQuery_id\",
				COALESCE(EFSDDQ.EvnForensicSubDopPersQuery_Num,'') as \"EvnForensicSubDopPersQuery_Num\",
				to_char(EFSDDQ.EvnForensicSubDopPersQuery_insDT, 'dd.mm.yyyy')||' '||to_char(EFSDDQ.EvnForensicSubDopPersQuery_insDT, 'hh24:mi') as \"EvnForensicSubDopPersQuery_insDT\"
			FROM
				v_EvnForensicSubDopPersQuery EFSDDQ
			WHERE
				EFSDDQ.EvnForensicSub_id =:EvnForensicSub_id
		";

        return $this->queryResult($query,$queryParams);
    }


    /**
     * Функция удаления запроса на участие службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @param type $data
     * @return type
     */
    public function deleteEvnForenSubDopPersQuery($data) {
        $rules = array(
            array('field' => 'EvnForensicSubDopPersQuery_id', 'label' => 'Идентификатор запроса на участие', 'rules' => 'required', 'type' => 'id', 'default'=>NULL),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicSubDopPersQuery_del (
				EvnForensicSubDopPersQuery_id := :EvnForensicSubDopPersQuery_id
			);
		";

        return $this->queryResult($query,$queryParams);
    }
    /**
     * Функция получения данных сопроводительного письма службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @return boolean
     */
    public function getEvnForensicSubCoverLetter($data) {
        $rules = array(
            array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubCoverLetter_id', 'label' => 'Идентификатор сопроводительного письма', 'rules' => '', 'type' => 'id', 'default'=>NULL),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        if ($data['EvnForensicSubCoverLetter_id']) {

            $query = "
				SELECT
					EFSCL.EvnForensicSub_id as \"EvnForensicSub_id\",
					EFSCL.EvnForensicSubCoverLetter_Num as \"EvnForensicSubCoverLetter_Num\",
					to_char( EFSCL.EvnForensicSubCoverLetter_Date, 'dd.mm.yyyy') as \"EvnForensicSubCoverLetter_Date\",
					EFSCL.EvnForensicSubCoverLetter_Iniciator as \"EvnForensicSubCoverLetter_Iniciator\",
					EFSCL.EvnForensicSubCoverLetter_IniciatorJob as \"EvnForensicSubCoverLetter_IniciatorJob\",
					EFSCL.EvnForensicSubCoverLetter_Person as \"EvnForensicSubCoverLetter_Person\",
					to_char( EFSCL.EvnForensicSubCoverLetter_PersonBirthdate, 'dd.mm.yyyy') as \"EvnForensicSubCoverLetter_PersonBirthdate\",
					EFSCL.EvnForensicSubCoverLetter_DocType as \"EvnForensicSubCoverLetter_DocType\",
					EFSCL.EvnForensicSubCoverLetter_DocNum as \"EvnForensicSubCoverLetter_DocNum\",
					to_char( EFSCL.EvnForensicSubCoverLetter_DocDate, 'dd.mm.yyyy') as \"EvnForensicSubCoverLetter_DocDate\",
					EFSCL.EvnForensicSubCoverLetter_Attachment as \"EvnForensicSubCoverLetter_Attachment\"
				FROM
					v_EvnForensicSubCoverLetter EFSCL
				WHERE
					EFSCL.EvnForensicSubCoverLetter_id = :EvnForensicSubCoverLetter_id
			";

        } elseif ($data['EvnForensicSub_id']) {

            $query = "
				SELECT
					COALESCE((SELECT MAX(COALESCE(EFSDDQ.EvnForensicSubCoverLetter_Num,0)+1) as EvnForensicSubCoverLetter_Num FROM v_EvnForensicSubCoverLetter EFSDDQ),1) as \"EvnForensicSubCoverLetter_Num\",
					to_char( dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnForensicSubCoverLetter_Date\",
					P.Person_Fio as \"EvnForensicSubCoverLetter_Person\",
					COALESCE(PS.Person_SurName, '') || COALESCE(' ' || PS.Person_FirName, '') || COALESCE(' ' || PS.Person_SecName, '') AS \"EvnForensicSubCoverLetter_Iniciator\",
					EFS.EvnForensicSub_Num as \"EvnForensicSubCoverLetter_DocNum\",
					to_char( P.Person_BirthDay, 'dd.mm.yyyy') as \"EvnForensicSubCoverLetter_PersonBirthdate\",
					COALESCE(EFIP.ForensicIniciatorPost_Name,'') || ' ' || COALESCE(O.Org_Nick,'')  as \"EvnForensicSubCoverLetter_IniciatorJob\",
					to_char( EvnXml.EvnXml_insDT, 'dd.mm.yyyy') as \"EvnForensicSubCoverLetter_DocDate\"
				
				FROM
					v_EvnForensicSub EFS
					left join v_Person_all P on P.PersonEvn_id = EFS.PersonEvn_id AND P.Server_id = EFS.Server_id
					left join v_PersonState PS on PS.Person_id = EFS.Person_cid
					
					left join v_ForensicIniciatorPost EFIP on EFIP.ForensicIniciatorPost_id = EFS.ForensicIniciatorPost_id
					Left join v_Org O on O.Org_id = EFS.Org_did

					left join lateral(
						SELECT
							EX.EvnXml_insDT
						FROM
							v_EvnXml EX
						WHERE
							EX.Evn_id = EFS.EvnForensicSub_id
						ORDER BY
							EX.EvnXml_insDT DESC
						LIMIT 1	
					) as EvnXml on true
	
				WHERE
					EFS.EvnForensicSub_id = :EvnForensicSub_id
					
			";

        } else {
            return $this->createError('', 'Не переданы необходимые идентификаторы');
        }

        return $this->queryResult($query, $queryParams);

    }

    /**
     * Функция сохранения сопроводительного письма службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @return boolean
     */
    public function saveEvnForensicSubCoverLetter($data) {
        $rules = array(
            array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubCoverLetter_id', 'label' => 'Идентификатор сопроводительного письма', 'rules' => '', 'type' => 'id', 'default'=>NULL),
            array('field' => 'EvnForensicSubCoverLetter_Num', 'label' => 'Номер запроса', 'rules' => 'max_length[128]', 'type' => 'string', 'default'=>NULL),
            array('field' => 'EvnForensicSubCoverLetter_Date', 'label' => 'Дата заявки', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnForensicSubCoverLetter_Iniciator','label' => 'Кому (ФИО)','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubCoverLetter_IniciatorJob','label' => 'Кому (Должность, место работы)','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubCoverLetter_Person','label' => 'Подэкспертный','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubCoverLetter_PersonBirthdate', 'label' => 'Дата рождения', 'rules' => 'required', 'type' => 'string'),
            array('field' => 'EvnForensicSubCoverLetter_DocType', 'label' => 'Тип документа', 'rules' => 'required', 'type' => 'int'),
            array('field' => 'EvnForensicSubCoverLetter_DocNum','label' => 'Номер документа','rules' => 'required, max_length[255]','type' => 'string'),
            array('field' => 'EvnForensicSubCoverLetter_DocDate', 'label' => 'Дата документа', 'rules' => 'required', 'type' => 'string'),
            array('field' => 'EvnForensicSubCoverLetter_Attachment','label' => 'Цель запроса','rules' => 'max_length[512]','type' => 'string'),

            array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $proc = 'p_EvnForensicSubCoverLetter_ins';
        if ($data['EvnForensicSubCoverLetter_id']) {
            $proc = 'p_EvnForensicSubCoverLetter_upd';

        }

        $query = "
			select
				EvnForensicSubCoverLetter_id as \"EvnForensicSubCoverLetter_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"		
			from {$proc} (
				EvnForensicSubCoverLetter_id := :EvnForensicSubCoverLetter_id,
				EvnForensicSub_id := :EvnForensicSub_id,
				EvnForensicSubCoverLetter_Num := :EvnForensicSubCoverLetter_Num,
				EvnForensicSubCoverLetter_Date := :EvnForensicSubCoverLetter_Date,
				EvnForensicSubCoverLetter_Iniciator := :EvnForensicSubCoverLetter_Iniciator,
				EvnForensicSubCoverLetter_IniciatorJob := :EvnForensicSubCoverLetter_IniciatorJob,
				EvnForensicSubCoverLetter_Person := :EvnForensicSubCoverLetter_Person,
				EvnForensicSubCoverLetter_PersonBirthdate := :EvnForensicSubCoverLetter_PersonBirthdate,
				EvnForensicSubCoverLetter_DocType :=:EvnForensicSubCoverLetter_DocType,
				EvnForensicSubCoverLetter_DocNum :=:EvnForensicSubCoverLetter_DocNum,
				EvnForensicSubCoverLetter_DocDate :=:EvnForensicSubCoverLetter_DocDate,
				EvnForensicSubCoverLetter_Attachment :=:EvnForensicSubCoverLetter_Attachment,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult($query, $queryParams);
    }

    /**
     * Получение списка заявок дополнительных документов
     * @param type $data
     * @return type
     */
    protected function _getEvnForensicSubCoverLetterList($data) {
        $rules = array(
            array('field'=>'EvnForensicSub_id', 'rules'=>'required','label'=>'Идентификатор на участие', 'type' => 'id')
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;
        $query = "
			SELECT
				EFSCL.EvnForensicSubCoverLetter_id as \"EvnForensicSubCoverLetter_id\",
				COALESCE(EFSCL.EvnForensicSubCoverLetter_Num,'') as \"EvnForensicSubCoverLetter_Num\",
				to_char(EFSCL.EvnForensicSubCoverLetter_insDT, 'dd.mm.yyyy')||' '||to_char(EFSCL.EvnForensicSubCoverLetter_insDT, 'hh24:mi') as \"EvnForensicSubCoverLetter_insDT\"
			FROM
				v_EvnForensicSubCoverLetter EFSCL
			WHERE
				EFSCL.EvnForensicSub_id = :EvnForensicSub_id
		";

        return $this->queryResult($query,$queryParams);
    }


    /**
     * Функция удаления сопроводительного письма службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
     * @param type $data
     * @return type
     */
    public function deleteEvnForenSubCoverLetter($data) {
        $rules = array(
            array('field' => 'EvnForensicSubCoverLetter_id', 'label' => 'Идентификатор сопроводительного письма', 'rules' => 'required', 'type' => 'id', 'default'=>NULL),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnForensicSubCoverLetter_del (
				EvnForensicSubCoverLetter_id := :EvnForensicSubCoverLetter_id
			);
		";

        return $this->queryResult($query,$queryParams);
    }
    /**
     * Получение следующего за номера заявки службы судмедэкспертизы потерпевишх обвиняемых
     * @param type $data
     * @return type
     */
    public function getNextForenPersRequestNumber($data) {
        $rules = array(
            array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'rules' => 'required', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;


        $query = "
			SELECT
				COALESCE(MAX(EFS.EvnForensicSub_Num),0)+1 as \"EvnForensic_Num\"
			FROM 
				v_EvnForensicSub EFS
			WHERE
				EFS.ForensicSubType_id = :ForensicSubType_id
		";

        return $this->queryResult($query,$queryParams);
    }
    /**
     * Функция получения количества заявок для вкладок армов службы
     * @param type $data
     * @return array
     */
    public function getRequestCount($data) {


        //Если не задан идентификатор службы, получаем его из сессии
        if (empty($data['MedService_id'])) {
            if (empty($data['session']['CurMedService_id'])) {
                return $this->createError('','Не задан обязательный параметр: Идентификатор службы');
            } else {
                $data['MedService_id'] = $data['session']['CurMedService_id'];
            }
        }

        $rules = array(
            //array('field'=>'ARMType', 'label'=>'Тип АРМ','rules' => '', 'type' => 'string'),
            array('field'=>'MedService_id','label'=>'Идентификатор службы', 'rules'=>'required', 'type' => 'id'),
            //array('field'=>'MedPersonal_id', 'label'=>'Идентификатор эксперта', 'rules'=>'', 'type'=>'id'),
            //Параметры поиска
            array('field' => 'EvnForensicSub_Num', 'label'=>'Номер заявки','rules' => '', 'type' => 'int'),
            array('field' => 'MedPersonal_eid', 'label'=>'Идентификатор эксперта','rules' => '', 'type' => 'id'),
            array('field' => 'ForensicSubType_id' , 'label' => 'Тип заявки', 'rules' => '', 'type' => 'id'),
            array('field' => 'own' , 'label' => 'Только собственные заявки', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
            array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        //@TODO: Почему-то при выборке из v_EvnForensic количество записей по группам не совпадает с выборкой из потомков 
        $medServiceNickResult = $this->getCurrentMedServiceSysNick($queryParams);
        if (!$this->isSuccessful($medServiceNickResult)) {
            return $medServiceNickResult;
        }

        $where = '';
        $from = '';

        if (!empty($queryParams['MedPersonal_eid'])) {
            $where .= ' AND EF.MedPersonal_eid =:MedPersonal_eid';
        }

        if (!empty($queryParams['ForensicSubType_id'])) {
            $where .= ' AND EF.ForensicSubType_id =:ForensicSubType_id';
        }

        if (!empty($queryParams['EvnForensicSub_Num'])) {
            $where .=  " AND EF.EvnForensicSub_Num=:EvnForensicSub_Num";
        }

        if ($queryParams['own']) {
            $where .=  " AND EF.pmUser_insID=:pmUser_id";
        }

        $query = "
			SELECT
				sum(case when COALESCE(ES.EvnStatus_SysNick,'New') != 'Done' then 1 else 0 end) as \"All\",
				sum(case when COALESCE(ES.EvnStatus_SysNick,'New') = 'New' then 1 else 0 end) as \"New\",
				sum(case when COALESCE(ES.EvnStatus_SysNick,'New') = 'Appoint' then 1 else 0 end) as \"Appoint\",
				sum(case when COALESCE(ES.EvnStatus_SysNick,'New') = 'Check' then 1 else 0 end) as \"Check\",
				sum(case when COALESCE(ES.EvnStatus_SysNick,'New') = 'Approved' then 1 else 0 end) as \"Approved\"

			FROM
				v_EvnForensicSub as EF
				left join v_EvnStatus ES on EF.EvnStatus_id = ES.EvnStatus_id
				{$from}
			WHERE
				EF.MedService_id = :MedService_id
				{$where}
		";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Возвращает данные справочника "Оценка вреда здоровью"
     *
     * @return array
     */
    public function loadForensicValuationInjury(){
        $sql = "SELECT * FROM v_ForensicValuationInjury";

        $query = $this->db->query( $sql );
        if ( is_object( $query ) ) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * Возвращает данные справочника "Определение половых состояний (преступлений)"
     *
     * @return array
     */
    public function loadForensicDefinitionSexualOffenses(){
        $sql = "SELECT * FROM v_ForensicDefinitionSexualOffenses";

        $query = $this->db->query( $sql );
        if ( is_object( $query ) ) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * Возвращает данные справочника "Определение"
     *
     * @return array
     */
    public function loadForensicSubDefinition(){
        $sql = "SELECT * FROM v_ForensicSubDefinition ";

        $query = $this->db->query( $sql );
        if ( is_object( $query ) ) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * Сохранение отчета "Деятельность бюро"
     *
     * @return boolean
     */
    public function saveForensicSubReportWorking( $data ){
        // Если ничего не выбрано удалим отчет
        if ( !$data[ 'ForensicValuationInjury_id' ] && !$data[ 'ForensicDefinitionSexualOffenses_id' ] && !$data[ 'ForensicSubDefinition_id' ] ) {
            if ( !$data['ForensicSubReportWorking_id'] ) {
                return array( array( 'success' => false, 'Error_Code' => '', 'Error_Msg' => 'Не удалось получить идентификатор отчета.' ) );
            }

            $sql = "
				select
				    NULL as \"ForensicSubReportWorking_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_ForensicSubReportWorking_del (
					ForensicSubReportWorking_id := :ForensicSubReportWorking_id
				);
			";

            return $this->queryResult( $sql, array(
                'ForensicSubReportWorking_id' => $data['ForensicSubReportWorking_id'],
                'pmUser_id' => $data[ 'pmUser_id' ],
            ) );
        }

        $proc = $data[ 'ForensicSubReportWorking_id' ] ? 'p_ForensicSubReportWorking_upd' : 'p_ForensicSubReportWorking_ins';

        $sql = "
			select
				ForensicSubReportWorking_id as \"ForensicSubReportWorking_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				ForensicSubReportWorking_id := :ForensicSubReportWorking_id,
				EvnForensicSub_id := :EvnForensicSub_id,
				ForensicValuationInjury_id := :ForensicValuationInjury_id,
				ForensicDefinitionSexualOffenses_id := :ForensicDefinitionSexualOffenses_id,
				ForensicSubDefinition_id := :ForensicSubDefinition_id,
				pmUser_id := :pmUser_id
			);
		";

        return $this->queryResult( $sql, array(
            'ForensicSubReportWorking_id' => $data[ 'ForensicSubReportWorking_id' ],
            'EvnForensicSub_id' => $data[ 'EvnForensicSub_id' ],
            'ForensicValuationInjury_id' => $data[ 'ForensicValuationInjury_id' ],
            'ForensicDefinitionSexualOffenses_id' => $data[ 'ForensicDefinitionSexualOffenses_id' ],
            'ForensicSubDefinition_id' => $data[ 'ForensicSubDefinition_id' ],
            'pmUser_id' => $data[ 'pmUser_id' ],
        ) );
    }

    /**
     * Возвращает данные отчета "Деятельности бюро"
     *
     * @param array $data
     * @return array or false on query error
     */
    public function getEvnForensicSubReportWorking( $data ){
        if ( ( !isset( $data['EvnForensicSub_id'] ) || !$data['EvnForensicSub_id'] ) && ( !isset( $data['ForensicSubReportWorking_id'] ) || !$data['ForensicSubReportWorking_id'] ) ) {
            return $this->createError( '', 'Не переданы необходимые идентификаторы' );
        }

        if ( isset( $data['ForensicSubReportWorking_id'] ) && $data['ForensicSubReportWorking_id'] ) {
            $sqlArr = array(
                ':ForensicSubReportWorking_id' => $data['ForensicSubReportWorking_id']
            );
            $where = 'ForensicSubReportWorking_id=:ForensicSubReportWorking_id';
        } else {
            $sqlArr = array(
                'EvnForensicSub_id' => $data['EvnForensicSub_id']
            );
            $where = 'EvnForensicSub_id=:EvnForensicSub_id';
        }

        $sql = "SELECT * FROM v_ForensicSubReportWorking WHERE ".$where;

        return $this->queryResult( $sql, $sqlArr );
    }

    /**
     * Получение данных заявки
     * @param type $data
     * @return type
     */
    protected function _getEvnForesincSubRequest($data) {
        $rules = array(
            array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $query = "
			SELECT
				EFS.EvnForensicSub_id as \"EvnForensicSub_id\",
				EFS.EvnForensicSub_pid as \"EvnForensicSub_pid\",
				EFS.XmlType_id as \"XmlType_id\",
				EFS.ForensicSubType_id as \"ForensicSubType_id\",
				EFS.EvnForensicSub_Num as \"EvnForensicSub_Num\",
				to_char(EFS.EvnForensicSub_ExpertiseComeDate, 'yyyy-mm-dd hh24:mi:ss') as \"EvnForensicSub_ExpertiseComeDate\",
				to_char(EFS.EvnForensicSub_ResDate, 'yyyy-mm-dd hh24:mi:ss') as \"EvnForensicSub_ResDate\",
				EFS.Person_cid as \"Person_cid\",
				EFS.Org_did as \"Org_did\",
				EFS.ForensicIniciatorPost_id as \"ForensicIniciatorPost_id\",
				to_char( EFS.EvnForensicSub_AccidentDT, 'yyyy-mm-dd hh24:mi:ss') as \"EvnForensicSub_AccidentDT\",
				to_char( EFS.EvnForensicSub_ExpertiseDT, 'yyyy-mm-dd hh24:mi:ss') as \"EvnForensicSub_ExpertiseDT\",
				EFS.PersonEvn_id as \"PersonEvn_id\",
				EFS.Server_id as \"Server_id\",
				EFS.MedPersonal_eid as \"MedPersonal_eid\",
				EFS.MedService_id as \"MedService_id\",
				EFS.Lpu_id as \"Lpu_id\",
				EFS.EvnForensicSub_Result as \"EvnForensicSub_Result\",
				EFS.EvnForensicSub_Receiver as \"EvnForensicSub_Receiver\",
				EFS.EvnStatus_id as \"EvnStatus_id\"
			FROM
				v_EvnForensicSub EFS
			WHERE
				EFS.EvnForensicSub_id = :EvnForensicSub_id
			";

        return $this->queryResult($query,$queryParams);
    }

    /**
     * Функция описания результата экспертизы службы потерпевших, обвиняемых и других лиц
     * @return boolean
     */
    public function setEvnForensicSubResult($data)  {
        $rules = array(
            array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array( 'field' => 'EvnForensicSub_Result', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'string', 'default'=>''),
            array( 'field' => 'pmUser_id','rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
        );
        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $previousData = $this->_getEvnForesincSubRequest($queryParams);

        if (!$this->isSuccessful($previousData)) {
            return $previousData;
        } elseif (empty($previousData)) {
            return $this->createError('', 'Изменяемая запись не найдена. Пожалуйста, обновите страницу.');
        }

        $queryParams = array_merge($previousData[0], $queryParams);

        return $this->saveForenPersRequest($queryParams , false);

    }

    /**
     * Функция сохранения информации о получателе результата экспертизы экспертизы службы потерпевших, обвиняемых и других лиц
     * @return boolean
     */
    public function setEvnForensicSubReceiver($data)  {
        $rules = array(
            array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array( 'field' => 'EvnForensicSub_Receiver', 'label' => 'Получатель результата', 'rules' => '', 'type' => 'string', 'default'=>''),
            array( 'field' => 'pmUser_id','rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $previousData = $this->_getEvnForesincSubRequest($queryParams);

        if (!$this->isSuccessful($previousData)) {
            return $previousData;
        } elseif (empty($previousData)) {
            return $this->createError('', 'Изменяемая запись не найдена. Пожалуйста, обновите страницу.');
        }

        $queryParams = array_merge($previousData[0], $queryParams);
        return $this->saveForenPersRequest($queryParams, false);

    }
    /**
     * Функция сохранения даты проведеня экспертизы экспертизы службы потерпевших, обвиняемых и других лиц
     * @return boolean
     */
    public function setEvnForensicSubExpertiseDT($data)  {
        $rules = array(
            array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array( 'field' => 'EvnForensicSub_ExpertiseDT', 'label' => 'Дата проведения экспертизы', 'rules' => '', 'type' => 'string', 'default'=>null),
            array( 'field' => 'pmUser_id','rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
        );

        $queryParams = $this->_checkInputData($rules, $data, $err, false);
        if (!$queryParams) return $err;

        $previousData = $this->_getEvnForesincSubRequest($queryParams);

        if (!$this->isSuccessful($previousData)) {
            return $previousData;
        } elseif (empty($previousData)) {
            return $this->createError('', 'Изменяемая запись не найдена. Пожалуйста, обновите страницу.');
        }

        $queryParams = array_merge($previousData[0], $queryParams);
        return $this->saveForenPersRequest($queryParams, false);

    }

}
