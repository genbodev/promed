<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Scenario - Контроллер включает общие методы сохранения и загрузки
 * 
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat (emsis.magafurov@gmail.com)
 * @version      01.07.2019
 */

abstract class Scenario extends swController {

	/**
	 * Модель
	 */
	var $model_name = 'Scenario_model';
	var $model_dir = '';
	var $fields = [];
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		if(empty($this->model_name)) {
			return new Exception('Не задана модель ($model_name) в контроллере');
		}

		$this->initModel();
	}

	/**
	 * Инициализация модели
	 */
	protected function initModel() {
		$this->load->model($this->model_dir . $this->model_name, 'dbmodel');
		$this->load->database();
	}

	/**
	 * Выполнение сценария
	 * @param string $name наименование сценария
	 * @return bool
	 */
	private function doScenario($name) {
		$this->dbmodel->setScenario($name);

		// описание входящих параметров либо в контроллере либо из описания полей в таблице
		$this->inputRules['doScenario'] = $this->getInputRulesByName($name);

		if( !$this->inputRules['doScenario'] ) {
			// из описания полей таблиц
			$this->inputRules['doScenario'] = $this->dbmodel->getInputRules($name);
		}

		$data = $this->ProcessInputData('doScenario', true, true);
		if($data === false) return false;
		return $this->dbmodel->doScenario($data);
	}

	/**
	 * Возвращение правил входящих параметров для определенного сценария
	 * @param $name
	 * @return bool |null
	 */
	function getInputRulesByName($name = '') {
		return false;
	}

	/**
	 * Возвращаем правило для определенного поля
	 * @param string $fieldName
	 * @param string $rules
	 * @return mixed
	 * @throws Exception
	 */
	function getRuleByFieldName($fieldName = '', $rules = null) {
		if( !isset($this->fields[$fieldName]) )
			throw new Exception('Не описан входящий параметр: '.$fieldName);
		$description = $this->fields[$fieldName];

		if( !isset($description['field']) ) {
			$description['field'] = $fieldName;
		}

		if( isset($rules) ) {
			$description['rules'] = $rules;
		}

		if( !isset($description['rules']) ) {
			$description['rules'] = '';
		}

		return $description;
	}

	/**
	 * Инициазилация правил для входящих параметров
	 */
	function getFieldsRules() {
		$rules = [];
		foreach ( $this->fields as $field => $descr) {
			$descr['field'] = $field;
			if( !isset($descr['rules']) ) $descr['rules'] = '';
			$rules[] = $descr;
		}
		return $rules;
	}

	/**
	 * Выполнение сценария загрузки
	 * @param $name
	 * @return bool
	 */
	protected function doScenarioLoad($name) {
		$response = $this->doScenario($name);
		if($response === false) return false;
		$this->ProcessModelList($response,true,true)->formatDatetimeFields('Y-m-d H:i:s')->ReturnData();
	}

	/**
	 * Выполнение сценария сохранения
	 * @param $name
	 * @return bool
	 */
	protected function doScenarioSave($name) {
		$response = $this->doScenario($name);
		if($response === false) return false;
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Сохранение грида
	 */
	protected function doSaveGrid() {
		$this->doScenarioSave(SwModel::SCENARIO_DO_SAVE_GRID);
	}

	/**	
	 * Сохранение формы
	 */
	protected function doSave() {
		$this->doScenarioSave(SwModel::SCENARIO_DO_SAVE);
	}

	/**
	 * Удаление записи
	 */
	protected function doDelete() {
		$this->doScenarioSave(SwModel::SCENARIO_DELETE);
	}

	/**
	 * Исключение записи
	 */
	protected function doDisable() {
		$this->doScenarioSave(Scenario_model::SCENARIO_DISABLE);
	}

	/**
	 * Загрузка формы
	 */
	protected function loadEditForm() {
		$this->doScenarioLoad(SwModel::SCENARIO_LOAD_EDIT_FORM);
	}

	/**
	 * Загрузка грида
	 */
	protected function loadGrid() {
		$this->doScenarioLoad(SwModel::SCENARIO_LOAD_GRID);
	}

	/**
	 * Загрузка комбо
	 */
	protected function loadCombo() {
		$this->doScenarioLoad(SwModel::SCENARIO_LOAD_COMBO_BOX);
	}

	/**
	 * Сохранение массива объектов
	 */
	protected function doSaveMultiple () {
		$this->doScenarioSave(Scenario_model::SCENARIO_DO_SAVE_JSON_MULTIPLE);
	}
}