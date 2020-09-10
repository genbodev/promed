<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceFRMO - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			14.06.2017
 *
 * @property ServiceFRMO_model dbmodel
 */

class ServiceFRMO extends SwController {
	protected $inputRules = array(
		'loadLpuListForExport' => array(),
		'loadLpuListForImport' => array(),
		'runExport' => array(
			array('field' => 'LpuList', 'label' => 'Список МО', 'rules' => 'required', 'type' => 'json_array'),
		),
		'runImport' => array(
			array('field' => 'LpuList', 'label' => 'Список МО', 'rules' => 'required', 'type' => 'json_array'),
		),
		'resumeFRMOSession' => array(
			array(
				'field' => 'FRMOSession_id',
				'label' => 'Идентификатор сессии',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadFRMOSessionHistGrid' => array(
			array(
				'field' => 'FRMOSession_id',
				'label' => 'Идентификатор сессии',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadFRMOSessionErrorGrid' => array(
			array(
				'field' => 'FRMOSession_id',
				'label' => 'Идентификатор сессии',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'runUpdate' => array(

		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->load->model('ServiceFRMO_model', 'dbmodel');
	}

	/**
	 * Получение списка МО для экспорта в сервис ФРМО
	 * @return bool
	 */
	function loadLpuListForExport() {
		$data = $this->ProcessInputData('loadLpuListForExport');
		if ($data === false) return false;

		$response = $this->dbmodel->loadLpuListForExport($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка МО для имспорта из сервиса ФРМО
	 * @return bool
	 */
	public function loadLpuListForImport() {
		$data = $this->ProcessInputData('loadLpuListForImport');
		if ($data === false) return false;

		$response = $this->dbmodel->loadLpuListForImport($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка детального лога
	 * @return bool
	 */
	function loadFRMOSessionHistGrid() {
		$data = $this->ProcessInputData('loadFRMOSessionHistGrid');
		if ($data === false) return false;

		$response = $this->dbmodel->loadFRMOSessionHistGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка ошибок
	 * @return bool
	 */
	function loadFRMOSessionErrorGrid() {
		$data = $this->ProcessInputData('loadFRMOSessionErrorGrid');
		if ($data === false) return false;

		$response = $this->dbmodel->loadFRMOSessionErrorGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Запуск экспорта данных в сервис ФРМО
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
	 * Запуск импорта данных из сервиса ФРМО
	 * @return bool
	 */
	public function runImport() {
		$data = $this->ProcessInputData('runImport');
		if ($data === false) return false;

		$response = $this->dbmodel->runImport($data);

		$this->ProcessModelSave($response, true, 'Ошибка при импорте данных из сервиса ФРМО')->ReturnData();
		return true;
	}

	/**
	 * Возобновление экспорта данных в сервис ФРМО
	 * @return bool
	 */
	function resumeFRMOSession() {
		$data = $this->ProcessInputData('resumeFRMOSession');
		if ($data === false) return false;

		$response = $this->dbmodel->resumeFRMOSession($data);

		$this->ProcessModelSave($response, true, 'Ошибка при передаче данных в сервис ФРМО')->ReturnData();
		return true;
	}

	/**
	 * Запуск сервиса обновления ФРМО
	 */
	function runUpdate() {
		$data = $this->ProcessInputData('runUpdate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->runUpdate($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
}