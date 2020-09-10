<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * ServiceISProf - контроллер для отправки данных в ИС "Профилактика"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            ServiceISProf
 * @access            public
 * @copyright        Copyright (c) 2017 Swan Ltd.
 * @author            Dmitry Vlasenko
 * @version            11.11.2019
 *
 * @property ServiceISProf_model $dbmodel
 */
class ServiceISProf extends SwController
{
	public $inputRules = [
		'runPublisher' => [
			['field' => 'exportId', 'label' => 'exportId', 'rules' => '', 'type' => 'json_array']
		]
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('ServiceISProf_model', 'dbmodel');
	}

	/**
	 * @return bool
	 */
	function showServiceConfig()
	{
		$this->dbmodel->showServiceConfig();
	}

	/**
	 * Запуск отправки данных в очередь RabbitMQ
	 */
	function runPublisher()
	{
		$data = $this->ProcessInputData('runPublisher', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->runPublisher($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}