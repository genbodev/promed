<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ParameterValue - контроллер работы с ParameterValue 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Пермяков Александр
*/

/**
 * @version  09.2014
 * @property ParameterValue_model $dbModel
 */
class ParameterValue extends swController
{
	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array();
		$this->load->database();
		$this->load->model('ParameterValue_model', 'dbModel');
	}
	
	/**
	 *  Функция чтения списка параметров для грида
	 *  На выходе: JSON-строка
	 *  Используется: форма swParameterValueListWindow
	 */
	function doLoadGrid()
	{
		$this->inputRules['doLoadGrid'] = $this->dbModel->getInputRules(swModel::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('doLoadGrid', true);
		if (false == $data) { return false; }
		$response = $this->dbModel->doLoadGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Функция сохранения параметра
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования swParameterValueEditWindow
	 */
	function doSave()
	{
		$this->inputRules['doSave'] = $this->dbModel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSave', true);
		if (false == $data) { return false; }
		//SCENARIO_DO_SAVE или SCENARIO_AUTO_CREATE
		$this->dbModel->setScenario(swModel::SCENARIO_DO_SAVE);
		//$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->dbModel->doSave($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	*  Функция чтения параметра
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swParameterValueEditWindow
	*/
	function doLoadEditForm()
	{
		$this->inputRules['doLoadEditForm'] = $this->dbModel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditForm', true);
		if (false == $data) { return false; }
		$response = $this->dbModel->doLoadEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Функция удаления параметра
	*  На выходе: JSON-строка
	*  Используется: форма swParameterValueListWindow
	*/
	function doDelete()
	{
		$this->inputRules['doDelete'] = $this->dbModel->getInputRules(swModel::SCENARIO_DELETE);
		$data = $this->ProcessInputData('doDelete', true);
		if (false == $data) { return false; }
		$response = $this->dbModel->doDelete($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
}
