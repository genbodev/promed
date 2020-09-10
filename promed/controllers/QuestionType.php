<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QuestionType - контроллер для работы с вопросами анкет
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.15.2016
 *
 * @property QuestionType_model dbmodel
 */

class QuestionType extends swController {
	public $inputRules = array(
		'loadQuestionTypeSettings' => array(
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор анкеты (вид диспансеризации)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'QuestionType_Code',
				'label' => 'Код вопроса/группы вопросов',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadQuestionTypeVisionGrid' => array(

		),
		'loadQuestionTypeVisionForm' => array(
			array(
				'field' => 'QuestionTypeVision_id',
				'label' => 'Идентификатор настройки элемента анкеты',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveQuestionTypeVisionList' => array(
			array(
				'field' => 'QuestionTypeVisionList',
				'label' => 'Список с настройками отображения анкеты',
				'rules' => 'required',
				'type' => 'json_array',
				'assoc' => true
			)
		),
		'saveQuestionTypeVision' => array(
			array(
				'field' => 'QuestionTypeVision_id',
				'label' => 'Идентификатор настройки элемента анкеты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор анкеты (вид диспансеризации)',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'QuestionType_pid',
				'label' => 'Идентификатор родидельского элемента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'QuestionType_id',
				'label' => 'Идентификатор элемента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'QuestionTypeVision_Settings',
				'label' => 'JSON-строка с настройками',
				'rules' => '',
				'type' => 'string'
			),
		),
		'loadDispClassList' => array(

		),
		'loadQuestionTypeList' => array(
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор анкеты (вид диспансеризации)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'QuestionType_pid',
				'label' => 'Родительский элемент',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Regime_id',
				'label' => 'Идентификатор режима загрузки',
				'rules' => '',
				'type' => 'id'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('QuestionType_model', 'dbmodel');
	}

	/**
	 * Получение настроек для элементов анкеты
	 */
	function loadQuestionTypeSettings() {
		$data = $this->ProcessInputData('loadQuestionTypeSettings', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadQuestionTypeSettings($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение списка
	 */
	function loadQuestionTypeVisionGrid() {
		$data = $this->ProcessInputData('loadQuestionTypeVisionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadQuestionTypeVisionGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы настройки отображения элемента анкеты
	 */
	function loadQuestionTypeVisionForm() {
		$data = $this->ProcessInputData('loadQuestionTypeVisionForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadQuestionTypeVisionForm($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение настроки отображения элемента анкеты
	 */
	function saveQuestionTypeVisionList() {
		$data = $this->ProcessInputData('saveQuestionTypeVisionList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveQuestionTypeVisionList($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение настроки отображения элемента анкеты
	 */
	function saveQuestionTypeVision() {
		$data = $this->ProcessInputData('saveQuestionTypeVision', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveQuestionTypeVision($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка анкет из справочника видов диспансеризаций
	 */
	function loadDispClassList() {
		$data = $this->ProcessInputData('loadDispClassList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDispClassList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получения списка элементов анкет
	 */
	function loadQuestionTypeList() {
		$data = $this->ProcessInputData('loadQuestionTypeList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadQuestionTypeList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}