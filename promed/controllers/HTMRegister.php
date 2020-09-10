<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 EMSIS.
 * @author       Салават Магафуров
 * @version      11.2018
 */

/**
 * HTMRegister - Контроллер "Регистр ВМП"
 */
class HTMRegister extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('HTMRegister_model', 'htmregister');
	}

	/**
	 * Загрузка левой панельки в форме редактирования (swHTMRegisterEditWindow)
	 */
	function loadGrid() {

		$this->inputRules['getGridData'] = $this->htmregister->getInputRules(SwModel::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('getGridData', true, true);
		$response = $this->htmregister->loadGrid($data);
		$this->ReturnData($response);
	}

	/**
	 * Загрузка формы редактирования (swHTMRegisterEditWindow)
	 */
	function loadEditForm() {
		$this->htmregister->setScenario(swModel::SCENARIO_LOAD_EDIT_FORM);
		$this->inputRules['loadFormData'] = $this->htmregister->getInputRules(SwModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadFormData', true, true);
		$response = $this->htmregister->loadEditForm($data);
		$this->ReturnData($response);
	}

	/**
	 * Сохранение формы редактирования (swHTMRegisterEditWindow)
	 */
	function doSave() {
		$this->htmregister->setScenario(swModel::SCENARIO_DO_SAVE);
		$this->inputRules['doSave'] =  $this->htmregister->getInputRules(swModel::SCENARIO_DO_SAVE);

		$data = $this->ProcessInputData('doSave', true, true);
		$response = $this->htmregister->doSave($data);
		//$this->ReturnData($response);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Загрузка данных по КВС (swHTMRegisterEditWindow)
	 */
	function getEvnPSData() {
		$this->inputRules['getEvnPSData'] =  $this->htmregister->getInputRules("getEvnPSData");
		$data = $this->ProcessInputData('getEvnPSData', true, true);
		$response = $this->htmregister->getEvnPSData($data);
		$this->ReturnData($response);
	}

	/**
	 * Проверка для поля "Дата планируемой госпитализации"
	 */
	function isAllowedToPlanDate() {
		$this->inputRules['isAllowedToPlanDate'] =  $this->htmregister->getInputRules("isAllowedToPlanDate");
		$data = $this->ProcessInputData('isAllowedToPlanDate', true, true);
		$response = $this->htmregister->isAllowedToPlanDate($data);
		$this->ReturnData($response);
	}

	/**
	 * Получает список значений для поля "Метод ВМП"
	 */
	function getHTMedicalCareClassComboStore()
	{
		$this->inputRules['getHTMedicalCareClassComboStore'] =  $this->htmregister->getInputRules("getHTMedicalCareClassComboStore");
		$data = $this->ProcessInputData('getHTMedicalCareClassComboStore', true);
		if ($data === false) { return false; }

		$response = $this->htmregister->getHTMedicalCareClassComboStore($data);
		$this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
}