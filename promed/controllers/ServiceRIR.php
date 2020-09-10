<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceRIR - контроллер 
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @property ServiceRIR_model dbmodel
 */

class ServiceRIR extends swController {
	public $inputRules = [
		'syncAll' => [
			[
				'field' => 'id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			]
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->load->model('ServiceRIR_model', 'dbmodel');
	}

	/**
	 * Запуск импорта данных из НСИ ЕГИСЗ
	 */
	function syncAll() {
		$data = $this->ProcessInputData('syncAll');
		if ($data === false) return false;

		$response = $this->dbmodel->syncAll($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
}