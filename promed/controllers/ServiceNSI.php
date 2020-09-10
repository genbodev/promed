<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceNSI - контроллер для сервиса импорта справочников из НСИ ЕГИСЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property ServiceNSI_model dbmodel
 */

class ServiceNSI extends swController {
	public $inputRules = array(
		'syncAll' => array(
		),
		'downloadRefTableRegistry' => array(
			array(
				'field' => 'RefTableName',
				'label' => 'Название справочника',
				'rules' => 'required',
				'type' => 'string'
			),
            array(
                'field' => 'RefTableId',
                'label' => 'Идентификатор записи справочника',
                'rules' => 'required',
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

		$this->load->model('ServiceNSI_model', 'dbmodel');
	}

	/**
	 * Запуск импорта данных из НСИ ЕГИСЗ
	 */
	function syncAll() {
		$data = $this->ProcessInputData('syncAll');
		if ($data === false) return false;

		$response = $this->dbmodel->syncAll($data);
		$this->ProcessModelSave($response, true, 'Ошибка импорта данных из сервиса НСИ ЕГИСЗ')->ReturnData();

		return true;
	}

	/**
	 * Скачивание файла справочника
	 */
	function downloadRefTableRegistry() {
		$data = $this->ProcessInputData('downloadRefTableRegistry');
		if ($data === false) return false;

		$this->dbmodel->downloadRefTableRegistry($data);
	}
}