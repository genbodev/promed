<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicInfomat - контроллер для работы со справочником  инфоматов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property ElectronicInfomat_model dbmodel
 */

class ElectronicInfomat extends swController {

    protected  $inputRules = array(
        'delete' => array(
            array(
                'field' => 'ElectronicInfomat_id',
                'label' => 'Идентификатор инфомата',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'loadList' => array(
            array(
                'field' => 'f_Lpu_id',
                'label' => 'Фильтр: ЛПУ',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuBuilding_id',
                'label' => 'Фильтр: Подразделение',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicInfomat_Code',
                'label' => 'Фильтр: Код',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicInfomat_Name',
                'label' => 'Фильтр: Наименование',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicInfomat_WorkRange',
                'label' => 'Фильтр: Период работы',
                'rules' => '',
                'type' => 'string'
            ),
            array('default' => 0, 'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
            array('default' => 100, 'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
        ),
        'load' => array(
            array(
                'field' => 'ElectronicInfomat_id',
                'label' => 'Идентификатор инфомата',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'loadElectronicInfomatCombo' => array(
            array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор ЛПУ',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadElectronicQueueInfoCombo' => array(
            array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор ЛПУ',
                'rules' => 'required',
                'type' => 'id'
            ),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			)
        ),
        'loadElectronicInfomatQueues' => array(
            array(
                'field' => 'ElectronicInfomat_id',
                'label' => 'Идентификатор инфомата',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'loadElectronicInfomatProfiles' => array(
            array(
                'field' => 'ElectronicInfomat_id',
                'label' => 'Идентификатор инфомата',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'loadElectronicInfomatProfileForm' => array(
            array(
                'field' => 'ElectronicInfomatProfile_id',
                'label' => 'Идентификатор профиля инфомата',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'saveElectronicInfomatProfile' => array(
            array(
                'field' => 'ElectronicInfomatProfile_id',
                'label' => 'Идентификатор профиля инфомата',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicInfomat_id',
                'label' => 'Идентификатор инфомата',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSectionProfile_id',
                'label' => 'Идентификатор профиля',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedSpecOms_id',
                'label' => 'Идентификатор специальности',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicInfomatProfile_Position',
                'label' => 'Позиция профиля инфомата',
                'rules' => 'required',
                'type' => 'int'
            ),
        ),
        'loadAllRelatedLpu'=> array(
        ),
        'save' => array(
            array(
                'field' => 'ElectronicInfomat_id',
                'label' => 'Идентификатор инфомата',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор МО',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuBuilding_id',
                'label' => 'Идентификатор подразделения',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicInfomat_Code',
                'label' => 'Код',
                'rules' => 'trim|required',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicInfomat_Name',
                'label' => 'Наименование',
                'rules' => 'trim|required',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicInfomat_StartPage',
                'label' => 'Стартовая страница',
                'rules' => 'trim',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicInfomat_begDate',
                'label' => 'Дата начала',
                'rules' => 'required',
                'type' => 'date'
            ),
            array(
                'field' => 'ElectronicInfomat_endDate',
                'label' => 'Дата окончания',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'queueData',
                'label' => 'набор данных инфомат-очередь',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicInfomatButtons',
                'label' => 'Кнопки стартового экрана',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicInfomat_isPrintOut',
                'label' => 'Печать талона записи',
                'rules' => '',
                'type' => 'checkbox'
            ),
            array(
                'field' => 'ElectronicInfomat_IsAllSpec',
                'label' => 'Печать талона записи',
                'rules' => '',
                'type' => 'checkbox'
            ),
			array(
                'field' => 'ElectronicInfomat_IsPrintService',
                'label' => 'Тип печати',
                'rules' => '',
                'type' => 'int'
            )
        )
    );

    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('ElectronicInfomat_model', 'dbmodel');
    }

    /**
     * Удаление табло
     */
    function delete()
    {
        $data = $this->ProcessInputData('delete');
        if ($data === false) { return false; }

        $response = $this->dbmodel->delete($data);
        $this->ProcessModelSave($response, true, 'Ошибка при удалении очереди')->ReturnData();
        return true;
    }

    /**
     * Возвращает список инфоматов
     */
    function loadList()
    {
        $data = $this->ProcessInputData('loadList');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadList($data);
        $this->ProcessModelMultiList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Возвращает список всех связанных c инфоматами ЛПУ
     */
    function loadAllRelatedLpu()
    {
        $data = $this->ProcessInputData('loadAllRelatedLpu');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadAllRelatedLpu($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Возвращает список инофматов для комбо
     */
    function loadElectronicInfomatCombo()
    {
        $data = $this->ProcessInputData('loadElectronicInfomatCombo');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadElectronicInfomatCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Возвращает список очередей для комбо
     */
    function loadElectronicQueueInfoCombo()
    {
        $data = $this->ProcessInputData('loadElectronicQueueInfoCombo');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadElectronicQueueInfoCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Возвращает список очередей для табло
     */
    function loadElectronicInfomatQueues()
    {
        $data = $this->ProcessInputData('loadElectronicInfomatQueues');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadElectronicInfomatQueues($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Возвращает список профилей для инфомата
     */
    function loadElectronicInfomatProfiles() {
        $data = $this->ProcessInputData('loadElectronicInfomatProfiles');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadElectronicInfomatProfiles($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Загружает данные о профиле инофомата
     */
    function loadElectronicInfomatProfileForm()
    {
        $data = $this->ProcessInputData('loadElectronicInfomatProfileForm');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadElectronicInfomatProfileForm($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Сохраняет профиль для инфомата
     */
    function saveElectronicInfomatProfile()
    {
        $data = $this->ProcessInputData('saveElectronicInfomatProfile');
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveElectronicInfomatProfile($data);
        $this->ProcessModelSave($response)->ReturnData();
        return true;
    }

    /**
     * Возвращает табло
     */
    function load()
    {
        $data = $this->ProcessInputData('load');
        if ($data === false) { return false; }

        $response = $this->dbmodel->load($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Сохранение табло
     */
    function save()
    {
        // проверим рулзы
        $data = $this->ProcessInputData('save');
        if ($data === false) { return false; }

        // проверим дату
        if ( !empty($data['ElectronicInfomat_begDate']) && !empty($data['ElectronicInfomat_endDate']) && $data['ElectronicInfomat_begDate'] > $data['ElectronicInfomat_endDate'] ) {
            $this->ReturnData(array(
                'Error_Msg' => 'Дата начала не может быть больше даты окончания',
                'Error_Code' => 146,
                'success' => false
            ));
            return false;
        }

        // начнем транзакцию
        $this->dbmodel->beginTransaction();
        // сохраним табло
        $response = $this->dbmodel->save($data);

        // откатим если ошибки
        if ( !$this->dbmodel->isSuccessful( $response ) ) {
            $this->dbmodel->rollbackTransaction() ;
            $this->ReturnError('Ошибка при сохранении(обновлении) табло');
            return false;
        }

        // получим айди сущности
        if (empty($data['ElectronicInfomat_id'])) {
            $data['ElectronicInfomat_id'] = $response[0]['ElectronicInfomat_id'];
        }

        // если связанные очереди есть, обновим\создадим их
        if (isset($data['queueData'])) {

            // сформируем доп. параметры для передачи
            $linkedQueueParams = array(
                'ElectronicInfomat_id' => $data['ElectronicInfomat_id'],
                'jsonData' => $data['queueData'],
                'pmUser_id' => $data['pmUser_id'],
                'Server_id' => $data['Server_id']
            );

            $saveLinkedQueueRes = $this->dbmodel->updateElectronicInfomatLink($linkedQueueParams);

            if (!$this->dbmodel->isSuccessful($saveLinkedQueueRes)) {
                $this->dbmodel->rollbackTransaction() ;
                if (!empty($saveLinkedQueueRes[0]['Error_Msg'])) {
                    $this->ReturnError($saveLinkedQueueRes[0]['Error_Msg']);
                    return false;
                }
            }
        }

        // кнопки стартового экрана
        if ( !empty($data['ElectronicInfomatButtons']) ) {
            // сформируем доп. параметры для передачи
            $electronicInfomatButtonParams = array(
                'ElectronicInfomat_id' => $data['ElectronicInfomat_id'],
                'jsonData' => $data['ElectronicInfomatButtons'],
                'pmUser_id' => $data['pmUser_id'],
            );

            $saveElectronicInfomatButtonRes = $this->dbmodel->updateElectronicInfomatButtonLink($electronicInfomatButtonParams);

            if (!$this->dbmodel->isSuccessful($saveElectronicInfomatButtonRes)) {
                $this->dbmodel->rollbackTransaction() ;
                if (!empty($saveElectronicInfomatButtonRes[0]['Error_Msg'])) {
                    $this->ReturnError($saveElectronicInfomatButtonRes[0]['Error_Msg']);
                    return false;
                }
            }
        }

        // завершаем
        $this->dbmodel->commitTransaction();
        $this->ProcessModelSave($response)->ReturnData();

        return true;
    }
}