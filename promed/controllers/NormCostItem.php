<?php	defined('BASEPATH') or die ('No direct script access allowed');

class NormCostItem extends swController {
	public $inputRules = array(
		'loadNormCostItemGrid' => array(
			array(
				'field' => 'AnalyzerTest_id',
				'label' => 'Идентификатор теста',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadNormCostItemEditForm' => array(
			array(
				'field' => 'NormCostItem_id',
				'label' => 'Идентификатор норматива',
				'rules' => 'required',
				'type' => 'id'
			)		
		),
		'saveNormCostItem' => array(
			array(
				'field' => 'NormCostItem_id',
				'label' => 'Идентификатор норматива',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AnalyzerTest_id',
				'label' => 'Идентификатор теста',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugNomen_id',
				'label' => 'Реактив',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'NormCostItem_Kolvo',
				'label' => 'Количество',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'Unit_id',
				'label' => 'Идентификатор единицы измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Analyzer_id',
				'label' => 'Анализатор',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteNormCostItem' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор норматива',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * NormCostItem constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('NormCostItem_model', 'dbmodel');
	}


	/**
	 *  Получение списка нормативов
	 */
	function loadNormCostItemGrid() {
		$data = $this->ProcessInputData('loadNormCostItemGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNormCostItemGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Загрузка формы редактирования
	 */
	function loadNormCostItemEditForm() {
		$data = $this->ProcessInputData('loadNormCostItemEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNormCostItemEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Сохранение норматива
	 */
	function saveNormCostItem() {
		$data = $this->ProcessInputData('saveNormCostItem', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveNormCostItem($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Удаление нормы
	 */
	function deleteNormCostItem() {
		$data = $this->ProcessInputData('deleteNormCostItem', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteNormCostItem($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
}
