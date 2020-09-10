<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Hole - Контроллер для работы с лункой планшета
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) EMSIS.
 * @author       Magafurov Salavat
 * @version      28.11.19
 */

require_once('LisScenario.php');
class Hole extends LisScenario {
	var $model_name = "Hole_model";
	
	/**
	 * Добавление списка тестов в лунки
	 */
	function addUslugaTests() {
		$this->doScenarioSave(Hole_model::SCENARIO_ADD_USLUGATESTS);
	}
	
	/**
	 * Установление пустой контрольной лунки
	 */
	function setEmptyControlHole() {
		$this->doScenarioSave(Hole_model::setEmptyControlHole);
	}

	/**
	 * Очистка лунки
	 */
	function clearHole() {
		$this->doScenarioSave(Hole_model::clearHole);
	}

	/**
	 * Создание контрольной лунки
	 */
	function createControlHole() {
		$this->doScenarioSave(Hole_model::createControlHole);
	}

	/**
	 * Установление статуса брак лунки
	 */
	function setDefect() {
		$this->doScenarioSave(Hole_model::setDefect);
	}

	/**
	 * Установление статуса калибратор
	 */
	function setCalibratorHole() {
		$this->doScenarioSave(Hole_model::setCalibrator);
	}
}