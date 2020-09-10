<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceNSI - контроллер для сервиса обновления реестра УЦ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Common
 * @access		public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author      Maksim Yavorskiy
 * @version     17.10.2019
 *
 * @property ServiceYC_model dbmodel
 */

class ServiceYC extends swController {
	public $inputRules = array(
		'syncAll' => array()
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->load->model('ServiceYC_model', 'dbmodel');
	}

	/**
	 * Запуск импорта данных
	 */
	function syncAll() {
		$data = $this->ProcessInputData('syncAll');
		if ($data === false) return false;

		$response = $this->dbmodel->syncAll($data);
		$this->ProcessModelSave($response, true, 'Ошибка импорта данных из сервиса НСИ ЕГИСЗ')->ReturnData();

		return true;
	}
}