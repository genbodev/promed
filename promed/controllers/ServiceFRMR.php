<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceFRMR - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Stanislav Bykov (savage@swan-it.ru)
 * @version			21.12.2018
 *
 * @property ServiceFRMR_model dbmodel
 */

class ServiceFRMR extends SwController {
	protected $inputRules = array(
		'runImport' => array(
			array('field' => 'LpuList', 'label' => 'Список МО', 'rules' => '', 'type' => 'json_array'),
		),
		'runExport' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ServiceFRMR_model', 'dbmodel');
	}

	/**
	 * Запуск экспопрта данных в сервис ФРМР
	 * @return bool
	 */
	function runExport() {
		$data = $this->ProcessInputData('runExport');
		if ($data === false) return false;

		$response = $this->dbmodel->runExport($data);

		$this->ProcessModelSave($response, true, 'Ошибка при передаче данных в сервис ФРМО')->ReturnData();
		return true;
	}

	/**
	 * Запуск импорта данных из сервиса ФРМР
	 * @return bool
	 */
	public function runImport() {
		$data = $this->ProcessInputData('runImport');
		if ($data === false) return false;

		$response = $this->dbmodel->runImport($data);

		$this->ProcessModelSave($response, true, 'Ошибка при запуске импорта данных из сервиса ФРМР')->ReturnData();
		return true;
	}
}