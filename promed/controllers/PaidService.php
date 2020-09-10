<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PaidService - контроллер для работы с АРМ платных услуг
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class PaidService extends swController {
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
				'field' => 'ElectronicTalonStatus_id',
				'label' => 'Идентификатор статуса талона',
				'rules' => 'required',
				'type' => 'id'
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
				'field' => 'ignoreCheckAnotherElectronicTalon',
				'label' => 'Признак игнорирования проверки существования вызова',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckRegister',
				'label' => 'Признак игнорирования проверки по регистру',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'withoutElectronicQueue',
				'label' => 'Признак приёма без электронной очереди',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'Тип службы',
				'rules' => '',
				'type' => 'string'
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
				'field' => 'takeNext',
				'label' => 'Признак вызова следующего',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'Тип службы',
				'rules' => '',
				'type' => 'string'
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
			array('field' => 'DigitalServiceAction',
				'label' => 'текущее действие',
				'rules' => '',
				'type' => 'string'
			),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('PaidService_model', 'dbmodel');
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
		$data = $this->ProcessInputData('setElectronicTalonStatus', true);
		if ($data === false) { return false; }

		$this->load->model('ElectronicTalon_model');
		$data['disablePush'] = true;
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
		$this->ProcessModelSave($response, true, 'Ошибка приёма пациента')->ReturnData();
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
}