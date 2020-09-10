<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuBuildingOffice - контроллер для работы со справочником кабинетов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property LpuBuildingOffice_model dbmodel
 */

class LpuBuildingOffice extends swController {
	protected $inputRules = array(
		'delete' => array(
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
		),
		'load' => array(
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('default' => 0, 'field' => 'start','label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit','label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
		),
		'loadLpuBuildingOfficeCombo' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'showOfficeNumPrefix', 'label' => 'Показывать префикс перед названием кабинета', 'rules' => '', 'type' => 'boolean'),
		),
		'printList' => array(
			array('field' => 'date', 'label' => 'Отчетная дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
		),
		'saveLpuBuildingOfficeScoreboard' => array(
			array('field' => 'LpuBuildingOfficeScoreboard_id', 'label' => 'Идентификатор связи кабинета и табло', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ElectronicScoreboard_id', 'label' => 'Идентификатор табло', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingOfficeAssign_begDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuBuildingOfficeAssign_endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
		),
		'saveLpuBuildingOfficeInfomat' => array(
			array('field' => 'LpuBuildingOfficeInfomat_id', 'label' => 'Идентификатор кабинета', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ElectronicInfomat_id', 'label' => 'Идентификатор инфомата', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingOfficeAssign_begDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuBuildingOfficeAssign_endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
		),
		'save' => array(
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_Number', 'label' => 'Номер кабинета', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuBuildingOffice_Name', 'label' => 'Наименование', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuBuildingOffice_Comment', 'label' => 'Примечание', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuBuildingOffice_begDate', 'label' => 'Дата начала действия', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'LpuBuildingOffice_endDate', 'label' => 'Дата окончания действия', 'rules' => 'trim', 'type' => 'date'),
		),
		'loadLpuBuildingOfficeScoreboard' => array(
			array(
				'field' => 'ElectronicScoreboard_id',
				'label' => 'Идентификатор табло',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadLpuBuildingOfficeInfomat' => array(
			array(
				'field' => 'ElectronicInfomat_id',
				'label' => 'Идентификатор инофомата',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadLpuBuildingOfficeAssignData' => array(
			array(
				'field' => 'assign_id',
				'label' => 'Идентификатор связи кабинета и необходимой сущности',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'object',
				'label' => 'Сущность для связи с кабинетом',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'deleteLpuBuildingOfficeScoreboard' => array(
			array('field' => 'LpuBuildingOfficeScoreboard_id', 'label' => 'Идентификатор связи кабинета и табло', 'rules' => 'required', 'type' => 'id')
		),
		'deleteLpuBuildingOfficeInfomat' => array(
			array('field' => 'LpuBuildingOfficeInfomat_id', 'label' => 'Идентификатор связи кабинета и инфомата', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('LpuBuildingOffice_model', 'dbmodel');
	}

	/**
	 * Удаление кабинета
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении кабинета')->ReturnData();

		return true;
	}

	/**
	 * Возвращает список кабинетов
	 */
	public function loadList() {
		$data = $this->ProcessInputData('loadList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает данные кабинета
	 */
	public function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает список кабинетов в МО
	 */
	public function loadLpuBuildingOfficeCombo() {
		$data = $this->ProcessInputData('loadLpuBuildingOfficeCombo', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuBuildingOfficeCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Печать графика
	 */
	public function printList() {
		$data = $this->ProcessInputData('printList');
		if ($data === false) { return false; }

		echo 'Функционал в разработке';

		return true;
	}

	/**
	 * Сохранение кабинета
	 */
	public function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Сохранение кабинета и инфомата
	 */
	public function saveLpuBuildingOfficeInfomat() {
		$data = $this->ProcessInputData('saveLpuBuildingOfficeInfomat');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuBuildingOfficeInfomat($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Сохранение кабинета и инфомата
	 */
	public function saveLpuBuildingOfficeScoreboard() {
		$data = $this->ProcessInputData('saveLpuBuildingOfficeScoreboard');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuBuildingOfficeScoreboard($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Загружает грид связи табло и кабинета
	 */
	function loadLpuBuildingOfficeScoreboard() {

		$data = $this->ProcessInputData('loadLpuBuildingOfficeScoreboard',false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuBuildingOfficeScoreboard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загружает грид связи табло и кабинета
	 */
	function loadLpuBuildingOfficeInfomat() {

		$data = $this->ProcessInputData('loadLpuBuildingOfficeInfomat',false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuBuildingOfficeInfomat($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загружает данные формы назначения кабинета
	 */
	function loadLpuBuildingOfficeAssignData() {

		$data = $this->ProcessInputData('loadLpuBuildingOfficeAssignData',false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuBuildingOfficeAssignData($data);
		//echo '<pre>',print_r($response),'</pre>'; die();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}