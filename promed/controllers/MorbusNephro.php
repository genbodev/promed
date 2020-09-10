<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusNephro - контроллер для работы с регистром по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      11.2014
 *
 * @property-read MorbusNephro_model $dbmodel
 * @property-read EvnNotifyNephro_model $evnNotifyNephro
 * @property-read EvnDiagNephro_model $evnDiagNephro
 * @property-read MorbusNephroLab_model $morbusNephroLab
 * @property-read MorbusNephroDisp_model $morbusNephroDisp
 * @property-read MorbusNephroDialysis_model $morbusNephroDialysis
 */
class MorbusNephro extends swController 
{
	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 *  Загрузка для формы редактирования объекта списка "Динамическое наблюдение"
	 */
	function doLoadEditFormMorbusNephroDisp()
	{
		$this->load->model('MorbusNephroDisp_model', 'morbusNephroDisp');
		$this->inputRules['doLoadEditFormMorbusNephroDisp'] = $this->morbusNephroDisp->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormMorbusNephroDisp', true);
		if ($data === false) { return false; }
		$response = $this->morbusNephroDisp->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка для формы редактирования объекта списка "Нуждается в диализе"
	 */
	function doLoadEditFormMorbusNephroDialysis()
	{
		$this->load->model('MorbusNephroDialysis_model', 'morbusNephroDialysis');
		$this->inputRules['doLoadEditFormMorbusNephroDialysis'] = $this->morbusNephroDialysis->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormMorbusNephroDialysis', true);
		if ($data === false) { return false; }
		$response = $this->morbusNephroDialysis->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка для формы редактирования объекта списка "Лабораторные исследования"
	 */
	function doLoadEditFormMorbusNephroLab()
	{
		$this->load->model('MorbusNephroLab_model', 'morbusNephroLab');
		$this->inputRules['doLoadEditFormMorbusNephroLab'] = $this->morbusNephroLab->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormMorbusNephroLab', true);
		if ($data === false) { return false; }
		$response = $this->morbusNephroLab->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка для формы редактирования объекта списка "Диагноз"
	 */
	function doLoadEditFormEvnDiagNephro()
	{
		$this->load->model('EvnDiagNephro_model', 'evnDiagNephro');
		$this->inputRules['doLoadEditFormEvnDiagNephro'] = $this->evnDiagNephro->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormEvnDiagNephro', true);
		if ($data === false) { return false; }
		$response = $this->evnDiagNephro->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка для формы редактирования извещения по нефрологии
	 */
	function doLoadEditFormEvnNotifyNephro()
	{
		$this->load->model('EvnNotifyNephro_model', 'evnNotifyNephro');
		$this->inputRules['doLoadEditFormEvnNotifyNephro'] = $this->evnNotifyNephro->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormEvnNotifyNephro', true);
		if ($data === false) { return false; }
		$response = $this->evnNotifyNephro->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Загрузка полей записи регистра для формы
	 * «Диспансерные карты пациентов: Добавление / Редактирование»
	 * а также для формы создания извещения
	 */
	function doLoadEditFormMorbusNephro()
	{
		$this->load->model('MorbusNephro_model', 'dbmodel');
		$this->inputRules['doLoadEditFormMorbusNephro'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormMorbusNephro', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка списка "Лабораторные исследования" для формы «Диспансерные карты пациентов: Добавление / Редактирование»
	 */
	function doLoadGridLab()
	{
		$this->load->model('MorbusNephroLab_model', 'morbusNephroLab');
		$this->inputRules['doLoadGridLab'] = $this->morbusNephroLab->getInputRules(swModel::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('doLoadGridLab', true);
		if ($data === false) { return false; }
		$response = $this->morbusNephroLab->doLoadGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка комбобокса показателей
	 */
	function doLoadRateTypeList()
	{
		$this->load->model('MorbusNephroLab_model', 'morbusNephroLab');
		$this->inputRules['doLoadRateTypeList'] = $this->morbusNephroLab->getInputRules('doLoadRateTypeList');
		$data = $this->ProcessInputData('doLoadRateTypeList', true);
		if ($data === false) { return false; }
		$response = $this->morbusNephroLab->doLoadRateTypeList($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Сохранение объекта списка "Динамическое наблюдение"
	 */
	function doSaveMorbusNephroDisp()
	{
		$this->load->model('MorbusNephroDisp_model', 'morbusNephroDisp');
		$this->inputRules['doSaveMorbusNephroDisp'] = $this->morbusNephroDisp->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveMorbusNephroDisp', true);
		if ($data === false) { return false; }
		$this->morbusNephroDisp->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->morbusNephroDisp->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение объекта списка "Динамическое наблюдение"
	 */
	function doSaveMorbusNephroDialysis()
	{
		$this->load->model('MorbusNephroDialysis_model', 'morbusNephroDialysis');
		$this->inputRules['doSaveMorbusNephroDialysis'] = $this->morbusNephroDialysis->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveMorbusNephroDialysis', true);
		if ($data === false) { return false; }
		$this->morbusNephroDialysis->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->morbusNephroDialysis->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение объекта списка "Лабораторные исследования"
	 */
	function doSaveMorbusNephroLab()
	{
		$this->load->model('MorbusNephroLab_model', 'morbusNephroLab');
		$this->inputRules['doSaveMorbusNephroLab'] = $this->morbusNephroLab->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveMorbusNephroLab', true);
		if ($data === false) { return false; }
		$this->morbusNephroLab->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->morbusNephroLab->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение объекта списка "Диагноз"
	 */
	function doSaveEvnDiagNephro()
	{
		$this->load->model('EvnDiagNephro_model', 'evnDiagNephro');
		$this->inputRules['doSaveEvnDiagNephro'] = $this->evnDiagNephro->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveEvnDiagNephro', true);
		if ($data === false) { return false; }
		$this->evnDiagNephro->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->evnDiagNephro->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 *  Сохранение извещения по нефрологии
	 */
	function doSaveEvnNotifyNephro()
	{
		$this->load->model('EvnNotifyNephro_model', 'evnNotifyNephro');
		$this->inputRules['doSaveEvnNotifyNephro'] = $this->evnNotifyNephro->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveEvnNotifyNephro', true);
		if ($data === false) { return false; }
		$this->evnNotifyNephro->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->evnNotifyNephro->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение полей записи регистра
	 */
	function doSaveMorbusNephro()
	{
		$this->load->model('MorbusNephro_model', 'dbmodel');
		$this->inputRules['doSaveMorbusNephro'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveMorbusNephro', true);
		if ($data === false) { return false; }
		$this->dbmodel->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Сохранение полей записи регистра из формы "Диспансерные карты пациентов: Добавление / Редактирование"
	 */
	function doSavePersonDispForm()
	{
		$this->load->model('MorbusNephro_model', 'dbmodel');
		$this->inputRules['doSavePersonDispForm'] = $this->dbmodel->getInputRules('doSavePersonDispForm');
		$data = $this->ProcessInputData('doSavePersonDispForm', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doSavePersonDispForm($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *  Загрузка для формы редактирования
	 */
	function checkByPersonDispForm()
	{
		$this->load->model('MorbusNephro_model', 'dbmodel');
		$this->inputRules['checkByPersonDispForm'] = $this->dbmodel->getInputRules('checkByPersonDispForm');
		$data = $this->ProcessInputData('checkByPersonDispForm', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkByPersonDispForm($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Печать извещения по нефрологии
	 */
	function doPrintEvnNotifyNephro()
	{
		$this->load->model('EvnNotifyNephro_model', 'evnNotifyNephro');
		$this->inputRules['doPrintEvnNotifyNephro'] = $this->evnNotifyNephro->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doPrintEvnNotifyNephro', true);
		if ($data === false) { return false; }
		echo $this->evnNotifyNephro->doPrint($data);
		return true;
	}

	/**
	 * Вывод печатной формы «Карта динамического наблюдения»
	 */
	function doPrintMorbusNephro()
	{
		$this->load->model('MorbusNephro_model', 'dbmodel');
		$this->inputRules['doPrintMorbusNephro'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doPrintMorbusNephro', true);
		if ($data === false) { return false; }
		echo $this->dbmodel->doPrint($data);
		return true;
	}
}