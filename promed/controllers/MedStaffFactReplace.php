<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedStaffFactReplace - контроллер для работы с замещениями врачей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Polka
 * @access      public
 * @copyright   Copyright (c) 2017 Swan Ltd.
 * @author		Dmitrii Vlasenko
 * @version     10.09.2017
 *
 * @property MedStaffFactReplace_model dbmodel
 */

class MedStaffFactReplace extends swController {
	public $inputRules = array(
		'loadMedStaffFactReplaceGrid' => array(
			array('field' => 'MedStaffFactReplace_DateRange', 'label' => 'Диапазон дат замещения', 'rules' => '', 'type' => 'daterange'),
			array('field' => 'MedStaffFact_rid', 'label' => 'Сотрудник 1 (замещающий)', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Сотрудник 2 (замещаемый)', 'rules' => '', 'type' => 'id')
		),
		'saveMedStaffFactReplace' => array(
			array('field' => 'MedStaffFactReplace_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_rid', 'label' => 'Сотрудник 1 (замещающий врач)', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFactReplace_BegDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedStaffFactReplace_EndDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedStaffFact_id', 'label' => 'Сотрудник 2 (замещаемый врач)', 'rules' => '', 'type' => 'id')
		),
		'deleteMedStaffFactReplace' => array(
			array('field' => 'id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
		),
		'loadMedStaffFactReplaceForm' => array(
			array('field' => 'MedStaffFactReplace_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
		),
		'checkExist' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Сотрудник',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала периода расписания',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания периода расписания',
				'rules' => '',
				'type' => 'date'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('MedStaffFactReplace_model', 'dbmodel');
	}

	/**
	 * Сохранение
	 */
	public function saveMedStaffFactReplace() {
		$data = $this->ProcessInputData('saveMedStaffFactReplace', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMedStaffFactReplace($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Удаление
	 */
	public function deleteMedStaffFactReplace() {
		$data = $this->ProcessInputData('deleteMedStaffFactReplace', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteMedStaffFactReplace($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Проверка
	 */
	public function checkExist() {
		$data = $this->ProcessInputData('checkExist', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkExist($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы
	 */
	public function loadMedStaffFactReplaceForm() {
		$data = $this->ProcessInputData('loadMedStaffFactReplaceForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedStaffFactReplaceForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка
	 */
	public function loadMedStaffFactReplaceGrid() {
		$data = $this->ProcessInputData('loadMedStaffFactReplaceGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedStaffFactReplaceGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}