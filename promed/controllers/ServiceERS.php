<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceERS - контроллер для синхронизации данных по ЭРС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			ServiceERS
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 *
 * @property ServiceERS_model $dbmodel
 */

class ServiceERS extends SwController {
	public $inputRules = array(
		'runConsumer' => array(
			array('field' => 'timeout', 'label' => 'timeout', 'rules' => '', 'type' => 'int'),
		),
		'runPublisher' => array(
			array('field' => 'packageType', 'label' => 'packageType', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'procDataType', 'label' => 'procDataType', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'packageLimit', 'label' => 'packageLimit', 'rules' => '', 'type' => 'int'),
			array('field' => 'exportId', 'label' => 'exportId', 'rules' => '', 'type' => 'json_array'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ServiceERS_model', 'dbmodel');
		
		header('Content-Type: application/xml; charset=utf-8');
		$this->dbmodel->registerChildErs(730023881404718);
		
		exit;
	}

	/**
	 * @return bool
	 */
	function showServiceConfig() {
		$this->dbmodel->showServiceConfig();
	}

	/**
	 * Запуск получения ответов из очереди RabbitMQ
	 */
	function runConsumer() {
		$data = $this->ProcessInputData('runConsumer', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->runConsumer($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Запуск отправки данных в очередь RabbitMQ
	 */
	function runPublisher() {
		$data = $this->ProcessInputData('runPublisher', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->runPublisher($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}