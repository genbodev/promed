<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusIBS - контроллер для работы с регистром ИБС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      IBS
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      12.2014
 *
 * @property-read MorbusIBS_model $dbmodel
 * @property-read EvnNotifyIBS_model $evnNotifyIBS
 */
class MorbusIBS extends swController 
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
	 *  Загрузка для формы редактирования извещения по ИБС
	 */
	function doLoadEditFormEvnNotifyIBS()
	{
		$this->load->model('EvnNotifyIBS_model', 'evnNotifyIBS');
		$this->inputRules['doLoadEditFormEvnNotifyIBS'] = $this->evnNotifyIBS->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormEvnNotifyIBS', true);
		if ($data === false) { return false; }
		$response = $this->evnNotifyIBS->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Загрузка полей записи регистра для формы
	 * «Диспансерные карты пациентов: Добавление / Редактирование»
	 * а также для формы создания извещения
	 */
	function doLoadEditFormMorbusIBS()
	{
		$this->load->model('MorbusIBS_model', 'dbmodel');
		$this->inputRules['doLoadEditFormMorbusIBS'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormMorbusIBS', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}
	
	/**
	 *  Сохранение извещения по ИБС
	 */
	function doSaveEvnNotifyIBS()
	{
		$this->load->model('EvnNotifyIBS_model', 'evnNotifyIBS');
		$this->inputRules['doSaveEvnNotifyIBS'] = $this->evnNotifyIBS->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveEvnNotifyIBS', true);
		if ($data === false) { return false; }
		$this->evnNotifyIBS->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->evnNotifyIBS->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение полей записи регистра
	 */
	function doSaveMorbusIBS()
	{
		$this->load->model('MorbusIBS_model', 'dbmodel');
		$this->inputRules['doSaveMorbusIBS'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveMorbusIBS', true);
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
		$this->load->model('MorbusIBS_model', 'dbmodel');
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
		$this->load->model('MorbusIBS_model', 'dbmodel');
		$this->inputRules['checkByPersonDispForm'] = $this->dbmodel->getInputRules('checkByPersonDispForm');
		$data = $this->ProcessInputData('checkByPersonDispForm', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkByPersonDispForm($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Печать извещения по ИБС
	 */
	function doPrintEvnNotifyIBS()
	{
		$this->load->model('EvnNotifyIBS_model', 'evnNotifyIBS');
		$this->inputRules['doPrintEvnNotifyIBS'] = $this->evnNotifyIBS->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doPrintEvnNotifyIBS', true);
		if ($data === false) { return false; }
		echo $this->evnNotifyIBS->doPrint($data);
		return true;
	}

	/**
	 * Вывод печатной формы «Карта динамического наблюдения»
	 */
	function doPrintMorbusIBS()
	{
		$this->load->model('MorbusIBS_model', 'dbmodel');
		$this->inputRules['doPrintMorbusIBS'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doPrintMorbusIBS', true);
		if ($data === false) { return false; }
		echo $this->dbmodel->doPrint($data);
		return true;
	}
}