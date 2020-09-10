<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusProf - контроллер для работы с регистром по профзаболванию
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Prof
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      12.2014
 *
 * @property-read MorbusProf_model $dbmodel
 * @property-read EvnNotifyProf_model $evnNotifyProf
 * @property-read EvnDiagProf_model $evnDiagProf
 * @property-read MorbusProfLab_model $morbusProfLab
 * @property-read MorbusProfDisp_model $morbusProfDisp
 */
class MorbusProf extends swController 
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
	 *  Загрузка для формы редактирования извещения по нефрологии
	 */
	function doLoadEditFormEvnNotifyProf()
	{
		$this->load->model('EvnNotifyProf_model', 'evnNotifyProf');
		$this->inputRules['doLoadEditFormEvnNotifyProf'] = $this->evnNotifyProf->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormEvnNotifyProf', true);
		if ($data === false) { return false; }
		$response = $this->evnNotifyProf->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Загрузка полей записи регистра для формы создания извещения
	 */
	function doLoadEditFormMorbusProf()
	{
		$this->load->model('MorbusProf_model', 'dbmodel');
		$this->inputRules['doLoadEditFormMorbusProf'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormMorbusProf', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Загрузка комбобокса показателей
	 */
	function doLoadRateTypeList()
	{
		$this->load->model('MorbusProfLab_model', 'morbusProfLab');
		$this->inputRules['doLoadRateTypeList'] = $this->morbusProfLab->getInputRules('doLoadRateTypeList');
		$data = $this->ProcessInputData('doLoadRateTypeList', true);
		if ($data === false) { return false; }
		$response = $this->morbusProfLab->doLoadRateTypeList($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Сохранение извещения по нефрологии
	 */
	function doSaveEvnNotifyProf()
	{
		$this->load->model('EvnNotifyProf_model', 'evnNotifyProf');
		$this->inputRules['doSaveEvnNotifyProf'] = $this->evnNotifyProf->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveEvnNotifyProf', true);
		if ($data === false) { return false; }
		$this->evnNotifyProf->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->evnNotifyProf->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение полей записи регистра
	 */
	function doSaveMorbusProf()
	{
		$this->load->model('MorbusProf_model', 'dbmodel');
		$this->inputRules['doSaveMorbusProf'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveMorbusProf', true);
		if ($data === false) { return false; }
		$this->dbmodel->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Печать извещения по нефрологии
	 */
	function doPrintEvnNotifyProf()
	{
		$this->load->model('EvnNotifyProf_model', 'evnNotifyProf');
		$this->inputRules['doPrintEvnNotifyProf'] = $this->evnNotifyProf->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doPrintEvnNotifyProf', true);
		if ($data === false) { return false; }
		echo $this->evnNotifyProf->doPrint($data);
		return true;
	}

	/**
	 * Вывод печатной формы «Карта динамического наблюдения»
	 */
	function doPrintMorbusProf()
	{
		$this->load->model('MorbusProf_model', 'dbmodel');
		$this->inputRules['doPrintMorbusProf'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doPrintMorbusProf', true);
		if ($data === false) { return false; }
		echo $this->dbmodel->doPrint($data);
		return true;
	}

	/**
	 * Получение данных по профзаболению
	 */
	function getMorbusProfDiagData()
	{
		$this->load->model('MorbusProf_model', 'dbmodel');
		$this->inputRules['getMorbusProfDiagData'] = $this->dbmodel->getInputRules('getMorbusProfDiagData');
		$data = $this->ProcessInputData('getMorbusProfDiagData', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getMorbusProfDiagData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}