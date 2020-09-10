<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuIndividualPeriod - контроллер для работы с индивидуальными периодами записи
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Timofeev
 * @version			30052019
 *
 */

class LpuIndividualPeriod extends swController {
	public $inputRules = array(
		'saveLpuIndividualPeriod' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteLpuIndividualPeriod' => array(
			array(
				'field' => 'LpuIndividualPeriod_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getIndividualPeriodList' => array(),
		'loadIndividualPeriodEditForm' => array(
			array(
				'field' => 'IndividualPeriod_id',
				'label' => 'Идентификатор индивидуального периода',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveIndividualPeriod' => array(
			array(
				'field' => 'IndividualPeriod_id',
				'label' => 'Идентификатор индивидуального периода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IndividualPeriodType_id',
				'label' => 'Идентификатор типа индивидуального периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IndividualPeriod_value',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'deleteIndividualPeriod' => array(
			array(
				'field' => 'IndividualPeriod_id',
				'label' => 'Идентификатор индивидуального периода',
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
		$this->load->model('LpuIndividualPeriod_model', 'dbmodel');
	}

	/**
	 * Получение списка МО, имеющих доступ к индивидуальной настройке периодов записи
	 */
	function getLpuIndividualPeriodList() {

		$response = $this->dbmodel->getLpuIndividualPeriodList();

		$this->ProcessModelMultiList($response,true,'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 * Добавление МО в список МО, имеющих доступ к индивидуальной настройке периодов записи
	 */
	function saveLpuIndividualPeriod() {
		$data = $this->ProcessInputData('saveLpuIndividualPeriod', true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveLpuIndividualPeriod($data);

		$this->ProcessModelSave($response, true, 'Ошибка при добавлении МО')->ReturnData();
		return true;
	}

	/**
	 * Удаление МО из списка МО, имеющих доступ к индивидуальной настройке периодов записи
	 */
	function deleteLpuIndividualPeriod() {
		$data = $this->ProcessInputData('deleteLpuIndividualPeriod', true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteLpuIndividualPeriod($data);

		$this->ProcessModelSave($response, true, 'При удалении МО возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Получение списка индивидуальных периодов записи для текущей МО
	 */
	function getIndividualPeriodList() {

		$data = $this->ProcessInputData('getIndividualPeriodList', true);
		$response = $this->dbmodel->getIndividualPeriodList($data);

		$this->ProcessModelMultiList($response,true,'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Загрузка формы редактирования индивидуального периода
	 */
	function loadIndividualPeriodEditForm() {
		$data = $this->ProcessInputData('loadIndividualPeriodEditForm', true);
		$response = $this->dbmodel->loadIndividualPeriodEditForm($data);

		$this->ProcessModelList($response,true,'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Сохранение индивидуального периода записи для текущей МО
	 */
	function saveIndividualPeriod() {
		$data = $this->ProcessInputData('saveIndividualPeriod', true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveIndividualPeriod($data);

		$this->ProcessModelSave($response, true, 'При удалении МО возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Удаление индивидуального периода
	 */
	function deleteIndividualPeriod() {
		$data = $this->ProcessInputData('deleteIndividualPeriod', true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteIndividualPeriod($data);

		$this->ProcessModelSave($response, true, 'При удалении МО возникли ошибки')->ReturnData();
		return true;
	}
}