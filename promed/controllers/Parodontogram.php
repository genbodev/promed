<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2014 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		05.2014
 */

/**
 * Parodontogram - контроллер
 *
 * @package		Stom
 * @author		Александр Пермяков
 *
 * @property Parodontogram_model $dbmodel
 */
class Parodontogram extends swController {

	public $inputRules = array();

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('Parodontogram_model', 'dbmodel');
	}

	/**
	 * Печать пародонтограммы
	 */
	public function doPrint() {
		$this->inputRules['doPrint'] = $this->dbmodel->getInputRules('doPrint');
		$data = $this->ProcessInputData('doPrint', false, true);
		if ($data === false) {
			return false;
		}
		echo $this->dbmodel->doPrint($data);
		return true;
	}

	/**
	 *  Сохранение данных пародонтограммы
	 */
	function doSave() {
		$this->inputRules['doSave'] = $this->dbmodel->getInputRules('doSave');
		$data = $this->ProcessInputData('doSave', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
		return true;
	}

	/**
	 * Удаление пародонтограммы
	 */
	function doRemove() {
		$this->inputRules['doRemove'] = $this->dbmodel->getInputRules('doRemove');
		$data = $this->ProcessInputData('doRemove', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->doRemove($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
		return true;
	}

	/**
	 * Получение расчетных значений выносливости зуба для панели просмотра и редактирования
	 */
	function doLoadToothStateValues() {
		$this->inputRules['doLoadToothStateValues'] = $this->dbmodel->getInputRules('doLoadToothStateValues');
		$data = $this->ProcessInputData('doLoadToothStateValues', false);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->doLoadToothStateValues($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Получение данных пародонтограммы для панели просмотра и редактирования
	 */
	function doLoadViewData() {
		$this->inputRules['doLoadViewData'] = $this->dbmodel->getInputRules('doLoadViewData');
		$data = $this->ProcessInputData('doLoadViewData', false);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->doLoadViewData($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных списка истории панели просмотра и редактирования пародонтограммы
	 */
	function doLoadHistory() {
		$this->inputRules['doLoadHistory'] = $this->dbmodel->getInputRules('doLoadHistory');
		$data = $this->ProcessInputData('doLoadHistory', false);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->doLoadHistory($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}
