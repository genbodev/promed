<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * MethodsIFA - Контроллер "Методики ИФА"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) EMSIS.
 * @author       Magafurov Salavat
 * @version      18.11.19
 */

require_once('LisScenario.php');
class MethodsIFA extends LisScenario
{
	var $model_name = "MethodsIFA_model";

	/**
	 * Загрузка производителей
	 */
	function loadFirms() {
		$this->doScenarioLoad(MethodsIFA_model::SCENARIO_LOAD_FIRMS);
	}

	/**
	 * Загрузка справочника фильтра в АРМе лаборанта ИФА
	 */
	function loadFilterCombo() {
		$this->doScenarioLoad(MethodsIFA_model::loadFilterCombo);
	}
}
