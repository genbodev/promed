<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Таблица регистров/справочников доступных для загрузки
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 EMSIS.
 * @author       Magafurov SM
 * @version
 * @property Register Register
 */

class Register extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->load->model('Register_model', 'register');
		$this->load->database();
	}

	/**
	 * Добавление
	 */
	function add() {
		$this->doScenario(Register_model::SCENARIO_ADD);
	}

	/**
	 * Редактирование
	 */
	function edit() {
		$this->doScenario(Register_model::SCENARIO_EDIT);
	}

	/**
	 * Исключение
	 */
	function out() {
		$this->doScenario(Register_model::SCENARIO_OUT);
	}

	/**
	 * Удаление
	 */
	function delete() {
		$this->doScenario(SwModel::SCENARIO_DELETE);
	}

	/**
	 * Выполнение сценария
	 */
	function doScenario($name) {
		$this->register->setScenario($name);
		$this->inputRules['doScenario'] = $this->register->getInputRules($name);
		$data = $this->ProcessInputData('doScenario', true, true);
		$response = $this->register->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

}