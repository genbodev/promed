<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* Morbus - Контроллер простых заболеваний
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       gabdushev
* @version      12 2011
*/

/**
 * @property MorbusSimple_model $dbmodel 
 */
class Morbus extends swController
{
	var $model_name = "MorbusSimple_model";

	/**
	 * Description
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model($this->model_name, 'dbmodel');
		$this->inputRules = array();
	}
	
	/**
	 * Удаление заболевания. Все проверки выполняются. Логика с общими заболеваниями отрабатывает.
	 *
	 * @return bool
	 */
	function doDelete()
	{
		$this->inputRules['doDelete'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DELETE);
		$data = $this->ProcessInputData('doDelete', true);
		if (false == $data) { return false; }
		$response = $this->dbmodel->doDelete($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
}
