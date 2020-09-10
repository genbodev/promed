<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusGEBT - контроллер для MorbusGEBT
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Morbus
 * @access       public
 * @copyright    Copyright (c) 2009-2019 Swan Ltd.
 *
 * @property MorbusGEBT_model $dbmodel
 */

class MorbusGEBT extends swController {
	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	public $inputRules = array(
		'load' => array(
			array('field' => 'MorbusGEBT_id', 'label' => 'Идентификатор специфики заболевания', 'rules' => 'required', 'type' => 'id'),
		),
		'loadMorbusGEBTDrug' => array(
			array('field' => 'MorbusGEBTDrug_id', 'label' => 'Идентификатор курса', 'rules' => 'required', 'type' => 'id'),
		),
		'saveMorbusGEBTDrug' => array(
			array('field' => 'MorbusGEBTDrug_id', 'label' => 'Идентификатор курса', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGEBT_id', 'label' => 'Идентификатор специфики заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugComplexMNN_id', 'label' => 'МНН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusGEBTDrug_OneInject', 'label' => 'На одно введение', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MorbusGEBTDrug_InjectCount', 'label' => 'Количество введений', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MorbusGEBTDrug_InjectQuote', 'label' => 'Количество введений на квоту', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MorbusGEBTDrug_QuoteYear', 'label' => 'Количество квот в год', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MorbusGEBTDrug_BoxYear', 'label' => 'Упаковок в год', 'rules' => 'required', 'type' => 'int'),
		),
		'loadMorbusGEBTPlan' => array(
			array('field' => 'MorbusGEBTPlan_id', 'label' => 'Идентификатор плана', 'rules' => 'required', 'type' => 'id'),
		),
		'saveMorbusGEBTPlan' => array(
			array('field' => 'MorbusGEBTPlan_id', 'label' => 'Идентификатор плана', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGEBT_id', 'label' => 'Идентификатор специфики заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Место оказания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedicalCareType_id', 'label' => 'Условия оказания МП', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusGEBTPlan_Year', 'label' => 'Планируемый год проведения лечения', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MorbusGEBTPlan_Month', 'label' => 'Планируемый месяц проведения лечения', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DrugComplexMNN_id', 'label' => 'Препарат', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusGEBTPlan_Treatment', 'label' => 'Лечение проведено', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('MorbusGEBT_model', 'dbmodel');
	}

	/**
	 * Загрузка списка Курс препарата
	 */
	function loadMorbusGEBTDrugList() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMorbusGEBTDrugList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка формы Курс препарата
	 */
	function loadMorbusGEBTDrug() {
		$data = $this->ProcessInputData('loadMorbusGEBTDrug');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMorbusGEBTDrug($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение формы Курс препарата
	 */
	function saveMorbusGEBTDrug() {
		$data = $this->ProcessInputData('saveMorbusGEBTDrug');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMorbusGEBTDrug($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Загрузка списка Планируемое лечение
	 */
	function loadMorbusGEBTPlanList() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMorbusGEBTPlanList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка формы Планируемое лечение
	 */
	function loadMorbusGEBTPlan() {
		$data = $this->ProcessInputData('loadMorbusGEBTPlan');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMorbusGEBTPlan($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение формы Планируемое лечение
	 */
	function saveMorbusGEBTPlan() {
		$data = $this->ProcessInputData('saveMorbusGEBTPlan');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMorbusGEBTPlan($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Загрузка списка препаратов 
	 */
	function getDrugList() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDrugList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}