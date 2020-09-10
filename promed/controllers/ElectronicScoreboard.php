<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicScoreboard - контроллер для работы со справочником  электронных табло
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property ElectronicScoreboard_model dbmodel
 */

class ElectronicScoreboard extends swController {

    protected  $inputRules = array(
        'delete' => array(
            array(
                'field' => 'ElectronicScoreboard_id',
                'label' => 'Идентификатор табло',
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
                'field' => 'ElectronicScoreboard_Code',
                'label' => 'Фильтр: Код',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicScoreboard_Name',
                'label' => 'Фильтр: Наименование',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicScoreboard_WorkRange',
                'label' => 'Фильтр: Период работы',
                'rules' => '',
                'type' => 'string'
            ),
            array('default' => 0, 'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
            array('default' => 100, 'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
        ),
        'load' => array(
            array(
                'field' => 'ElectronicScoreboard_id',
                'label' => 'Идентификатор табло',
                'rules' => 'required',
                'type' => 'id'
            ),
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
        'loadElectronicServiceCombo' => array(
            array(
                'field' => 'ElectronicQueueInfo_id',
                'label' => 'Идентификатор ЭО',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'loadElectronicScoreboardQueues' => array(
            array(
                'field' => 'ElectronicScoreboard_id',
                'label' => 'Идентификатор табло',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'loadAllRelatedLpu'=> array(
        ),
		'refreshScoreboardBrowserPage'=> array(
			array(
				'field' => 'ElectronicScoreboard_id',
				'label' => 'Идентификатор табло',
				'rules' => 'required',
				'type' => 'id'
			)
		),
        'save' => array(
            array(
                'field' => 'ElectronicScoreboard_id',
                'label' => 'Идентификатор табло',
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
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSection_id',
                'label' => 'Идентификатор отделения',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicScoreboard_Code',
                'label' => 'Код',
                'rules' => 'trim|required',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicScoreboard_Name',
                'label' => 'Наименование',
                'rules' => 'trim|required',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicScoreboard_Nick',
                'label' => 'Краткое наименование',
                'rules' => 'trim',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicScoreboard_begDate',
                'label' => 'Дата начала',
                'rules' => 'required',
                'type' => 'date'
            ),
            array(
                'field' => 'ElectronicScoreboard_endDate',
                'label' => 'Дата окончания',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'queueData',
                'label' => 'набор данных табло-очереди',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicScoreboard_IsLED',
                'label' => 'Тип табло',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'ElectronicScoreboard_IsShownTimetable',
                'label' => 'Отображение расписания',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicScoreboard_RefreshInSeconds',
                'label' => 'Интервал смены информации на экране (сек.)',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'ElectronicScoreboard_IPaddress',
                'label' => 'IP-Адрес табло',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'ElectronicScoreboard_Port',
                'label' => 'Порт табло',
                'rules' => '',
                'type' => 'int'
            ),
			array(
				'field' => 'ElectronicScoreboard_IsCalled',// #150322
				'label' => 'Отображать текстовый статус талона',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ElectronicScoreboard_IsShownForEachDoctor',
				'label' => 'Индивидуальное табло (свой экран для каждого пункта обслуживания)',
				'rules' => '',
				'type' => 'swcheckbox'
			)
    ),
    );

    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('ElectronicScoreboard_model', 'dbmodel');
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
     * Возвращает список табло
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
     * Возвращает список всех связанных c табло ЛПУ
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
     * Возвращает список пунктов обслужваний
     */
    function loadElectronicServiceCombo()
    {
        $data = $this->ProcessInputData('loadElectronicServiceCombo');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadElectronicServiceCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Возвращает список очередей для табло
     */
    function loadElectronicScoreboardQueues()
    {
        $data = $this->ProcessInputData('loadElectronicScoreboardQueues');
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadElectronicScoreboardQueues($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
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
        if ( !empty($data['ElectronicScoreboard_begDate']) && !empty($data['ElectronicScoreboard_endDate']) && $data['ElectronicScoreboard_begDate'] > $data['ElectronicScoreboard_endDate'] ) {
            $this->ReturnError('Дата начала не может быть больше даты окончания', 146);
            return false;
        }

        // начнем транзакцию
        $this->dbmodel->beginTransaction();
        // сохраним табло
        $response = $this->dbmodel->save($data);

        // откатим если ошибки
        if ( !$this->dbmodel->isSuccessful( $response ) ) {
            $this->dbmodel->rollbackTransaction() ;
            $this->ReturnError('Ошибка при сохранении (обновлении) табло');
            return false;
        }

        // получим айди сущности
        if (empty($data['ElectronicScoreboard_id'])) {
            $data['ElectronicScoreboard_id'] = $response[0]['ElectronicScoreboard_id'];
        }

        // если связанные очереди есть, обновим\создадим их
        if (isset($data['queueData'])) {

            // сформируем доп. параметры для передачи
            $linkedQueueParams = array(
                'ElectronicScoreboard_id' => $data['ElectronicScoreboard_id'],
                'jsonData' => $data['queueData'],
                'pmUser_id' => $data['pmUser_id'],
                'Server_id' => $data['Server_id']
            );

            $saveLinkedQueueRes = $this->dbmodel->updateElectronicScoreboardQueueLink($linkedQueueParams);

            if (!$this->dbmodel->isSuccessful($saveLinkedQueueRes)) {
                $this->dbmodel->rollbackTransaction() ;
                if (!empty($save_drug_result[0]['Error_Msg'])) {
                    $this->ReturnError($saveLinkedQueueRes[0]['Error_Msg']);
                    return false;
                }
            }
        }

        // завершаем
        $this->dbmodel->commitTransaction();
        $this->ProcessModelSave($response)->ReturnData();

        return true;
    }

	/**
	 * Обновляет страницу браузера на ТВ через socket.io и NodeJS
	 */
	function refreshScoreboardBrowserPage() {

		$data = $this->ProcessInputData('refreshScoreboardBrowserPage',false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->refreshScoreboardBrowserPage($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}