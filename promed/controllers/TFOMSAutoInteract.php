<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * TFOMSAutoInteract - контроллер для автоматического взаимодействия с ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			TFOMSAutoInteract
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan-it.ru)
 * @version			11.2018
 *
 * @property TFOMSAutoInteract_model $dbmodel
 */
class TFOMSAutoInteract extends swController {
	public $inputRules = array(
		'showServiceConfig' => array(
			array('field' => 'name', 'label' => 'serviceName', 'rules' => 'required|trim', 'type' => 'string'),
		),
		'runService' => array(
			array('field' => 'packageType', 'label' => 'packageType', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'procDataType', 'label' => 'procDataType', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'packageLimit', 'label' => 'packageLimit', 'rules' => '', 'type' => 'int'),
			array('field' => 'exportId', 'label' => 'exportId', 'rules' => '', 'type' => 'json_array'),
		),
		'runPublisher' => array(
			array('field' => 'packageType', 'label' => 'packageType', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'procDataType', 'label' => 'procDataType', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'packageLimit', 'label' => 'packageLimit', 'rules' => '', 'type' => 'int'),
			array('field' => 'exportId', 'label' => 'exportId', 'rules' => '', 'type' => 'json_array'),
		),
		'runConsumer' => array(
			array('field' => 'queueNick', 'label' => 'queueNick', 'rules' => '', 'type' => 'string', 'default' => 'common'),
		),
		'publicateDopDispPlan' => array(
			array('field' => 'PersonDopDispPlan_ids', 'label' => 'Список планов для экспорта', 'rules' => 'required', 'type' => 'json_array'),
			array('field' => 'DispCheckPeriod_id', 'label' => 'Период плана', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgSMO_id', 'label' => 'СМО', 'rules' => '', 'type' => 'string'),
			array('field' => 'PacketNumber', 'label' => 'Номер пакета', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DispClass_id', 'label' => 'Вид диспансеризации', 'rules' => '', 'type' => 'id')
		),
		'consumeDopDospPlan' => array(
		),
		'publicatePers' => array(
			array('field' => 'begPeriod', 'label' => 'begPeriod', 'rules' => '', 'type' => 'date'),
			array('field' => 'endPeriod', 'label' => 'endPeriod', 'rules' => '', 'type' => 'date'),
			array('field' => 'start', 'label' => 'start', 'rules' => '', 'type' => 'int'),
			array('field' => 'limit', 'label' => 'limit', 'rules' => '', 'type' => 'int'),
			array('field' => 'disableSend', 'label' => 'disableSend', 'rules' => '', 'type' => 'int'),
			array('field' => 'exportId', 'label' => 'exportId', 'rules' => '', 'type' => 'json_array'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('TFOMSAutoInteract_model', 'dbmodel');
	}

	/**
	 * @return bool
	 */
	function showServiceConfig() {
		$data = $this->ProcessInputData('showServiceConfig', true);
		if ($data === false) return false;
		$this->dbmodel->showServiceConfig($data['name']);
	}

	/**
	 * Запуск отправки/получения данных из очереди RabbitMQ
	 */
	function runService() {
		$data = $this->ProcessInputData('runService', true);
		if ($data === false) return false;

		$response = $this->dbmodel->runService($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Запуск отправки данных в очередь RabbitMQ
	 */
	function runPublisher() {
		$data = $this->ProcessInputData('runPublisher', true);
		if ($data === false) return false;

		$response = $this->dbmodel->runPublisher($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Запуск получения данных из очереди RabbitMQ
	 */
	function runConsumer() {
		$data = $this->ProcessInputData('runConsumer', true);
		if ($data === false) return false;

		$response = $this->dbmodel->runConsumer($data, $data['queueNick']);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Отправка планов диспансеризации
	 */
	function publicateDopDispPlan() {
		$data = $this->ProcessInputData('publicateDopDispPlan', true);
		if ($data === false) return false;

		$response = $this->dbmodel->publicateDopDispPlan($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение ответов по плана диспансеризации
	 */
	function consumeDopDospPlan() {
		$data = $this->ProcessInputData('consumeDopDospPlan', true);
		if ($data === false) return false;

		$response = $this->dbmodel->consumeDopDospPlan($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function publicatePers() {
		$data = $this->ProcessInputData('publicatePers', true);
		if ($data === false) return false;

		$response = $this->dbmodel->publicatePers($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/*function publicateTestPolis() {
		$this->dbmodel->publicateTestPolis();
	}*/
}