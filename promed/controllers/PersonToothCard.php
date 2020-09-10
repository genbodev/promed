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
 * PersonToothCard - контроллер зубной карты
 *
 * @package		Stom
 * @author		Александр Пермяков
 *
 * @property PersonToothCard_model $dbmodel
 */
class PersonToothCard extends swController {
	public $NeedCheckLogin = false;
	public $inputRules = array();

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('PersonToothCard_model', 'dbmodel');
	}

	/**
	 * Вывод изображения зубной карты
	 */
	function Index() {
		return $this->doOutputPng();
	}

	/**
	 * Печать зубной карты
	 */
	public function doPrint() {
		checkLogin();
		$this->inputRules['doPrint'] = $this->dbmodel->getInputRules('doPrint');
		$data = $this->ProcessInputData('doPrint', false, true);
		if ($data === false) {
			return false;
		}
		echo $this->dbmodel->doPrint($data);
		return true;
	}

	/**
	 * Вывод изображения зубной карты
	 */
	public function doOutputPng() {
		$this->dbmodel->doOutputPng();
		return true;
	}

	/**
	 * Установка активных состояний сегмента из меню или зуба из формы редактирования
	 */
	function doSave() {
		checkLogin();
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
	 * Отмена активных состояний, созданных в рамках посещения
	 */
	function doRemove() {
		checkLogin();
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
	 * Получение данных зубной карты для панели просмотра и редактирования
	 */
	function doLoadViewData() {
		checkLogin();
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
	 *  Получение данных списка истории панели просмотра и редактирования зубной карты
	 */
	function doLoadHistory() {
		checkLogin();
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
