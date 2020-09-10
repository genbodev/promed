<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PaidService - контроллер для работы с ЭО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class ElectronicQueue extends swController {

    public $inputRules = array(
        'loadWorkPlaceGrid' => array(
            array(
                'field' => 'UslugaComplexMedService_id',
                'label' => 'Идентификатор услуги на службе',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'onDate',
                'label' => 'Дата',
                'rules' => 'required',
                'type' => 'date'
            )
        ),
        'fixPersonUnknown' => array(
            array(
                'field' => 'Person_oldId',
                'label' => 'Идентификатор неизвестного человека',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Person_newId',
                'label' => 'Идентификатор человека',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'setElectronicTalonStatus' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicTalonStatus_id',
                'label' => 'Идентификатор статуса талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'cancelCallCount',
                'label' => 'Количество вызовов (до отмены пациента)',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Идентификатор места работы врача',
                'rules' => '',
                'type' => 'int'
            )
        ),
        'applyCall' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор пациента',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'DispClass_id',
                'label' => 'Идентификатор класса диспансеризации',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'EvnDirection_id',
                'label' => 'Идентификатор направления',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'EvnPLDispTeenInspection_id',
                'label' => 'Идентификатор профосмотра детей',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'withoutElectronicQueue',
                'label' => 'Признак приёма без электронной очереди',
                'rules' => '',
                'type' => 'int'
            )
        ),
        'finishCall' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'DispClass_id',
                'label' => 'Идентификатор класса диспансеризации',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'setNoPatientTalonStatus' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'checkElectronicQueueInfoEnabled' => array(
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'checkIsDigitalServiceBusy' => array(
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => 'required',
                'type' => 'id'
            ),
            array('field' => 'ServiceAction',
                'label' => 'текущее действие',
                'rules' => '',
                'type' => 'string'
            )
        ),
		'isEnableEvnDirectionsWithEmptyTalonCode' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_id',
				'label' => 'Идентификатор услуги на службе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Идентификатор ресурса',
				'rules' => '',
				'type' => 'id'
			)
		),
		'generateTalonCodeForExistedRecords' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_id',
				'label' => 'Идентификатор услуги на службе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Идентификатор ресурса',
				'rules' => '',
				'type' => 'id'
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
        $this->load->model('ElectronicQueue_model', 'dbmodel');
    }

    /**
     * Загрузка области данных АРМ
     */
    function loadWorkPlaceGrid() {
        $data = $this->ProcessInputData('loadWorkPlaceGrid', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadWorkPlaceGrid($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Установка статуса электронного талона
     */
    function setElectronicTalonStatus() {
        $data = $this->ProcessInputData('setElectronicTalonStatus', false);
        if ($data === false) { return false; }

        $this->load->model('ElectronicTalon_model');
        $response = $this->ElectronicTalon_model->setElectronicTalonStatus($data);
        $this->ProcessModelSave($response, true, 'Ошибка установки статуса электронного талона')->ReturnData();

        return true;
    }

    /**
     * Установка статуса электронного талона при неявке пациента
     */
    function setNoPatientTalonStatus() {
        $data = $this->ProcessInputData('setNoPatientTalonStatus', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->setNoPatientTalonStatus($data);
        $this->ProcessModelSave($response, true, 'Ошибка установки статуса электронного талона')->ReturnData();

        return true;
    }

    /**
     * Проверка активности электронной очереди
     */
    function checkElectronicQueueInfoEnabled() {
        $data = $this->ProcessInputData('checkElectronicQueueInfoEnabled', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->checkElectronicQueueInfoEnabled($data);
        $this->ProcessModelSave($response, true, 'Ошибка проверки активности электронной очереди')->ReturnData();

        return true;
    }

    /**
     * Проверка на завершенность обслуживания в сервисе
     */
    function checkIsDigitalServiceBusy() {
        $data = $this->ProcessInputData('checkIsDigitalServiceBusy', false);
        if ($data === false) { return false; }

        $response = $this->dbmodel->checkIsDigitalServiceBusy($data);
        $this->ProcessModelSave($response, true, 'Ошибка проверки текущего сервиса на возможность вызова')->ReturnData();

        return true;
    }

    /**
     * Приём пациента
     */
    function applyCall() {
        $data = $this->ProcessInputData('applyCall', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->applyCall($data);
        $this->ProcessModelSave($response, true, (!empty($response['Error_Msg']) ? $response['Error_Msg'] : 'Ошибка приёма пациента'))->ReturnData();

        return true;
    }

    /**
     * Завершение приёма пациента
     */
    function finishCall() {
        $data = $this->ProcessInputData('finishCall', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->finishCall($data);
        $this->ProcessModelSave($response, true, 'Ошибка завершения приёма пациента')->ReturnData();

        return true;
    }

    /**
     * Замена неизвестного человека на известного
     */
    function fixPersonUnknown() {
        $data = $this->ProcessInputData('fixPersonUnknown', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->fixPersonUnknown($data);
        $this->ProcessModelSave($response, true, 'Ошибка обновления данных человека в талоне')->ReturnData();

        return true;
    }

	/**
	 * Проверка есть ли у связи ПО-ОБЪЕКТ-СОТРУДНИК направления без кода брони
	 */
	function isEnableEvnDirectionsWithEmptyTalonCode() {

		$data = $this->ProcessInputData('isEnableEvnDirectionsWithEmptyTalonCode');
		if ($data === false) { return false; }

		$response = $this->dbmodel->isEnableEvnDirectionsWithEmptyTalonCode($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Создаем коды бронирования для записей, у которых нет кода брони после создания очереди
	 */
	function generateTalonCodeForExistedRecords() {

		$data = $this->ProcessInputData('generateTalonCodeForExistedRecords');
		if ($data === false) { return false; }

		$response = $this->dbmodel->generateTalonCodeForExistedRecords($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}