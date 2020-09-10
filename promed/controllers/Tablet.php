<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Tablet - Контроллер для работы с формой "Выбор пленшета"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) EMSIS.
 * @author       Magafurov Salavat
 * @version      19.11.19
 */

require_once('LisScenario.php');
class Tablet extends LisScenario
{
	var $model_name = "Tablet_model";
	
	/**
	 * Установление дефекта планшету
	 */
	function setDefect() {
		$this->doScenarioSave(Tablet_model::setDefect);
	}

	/**
	 * Создание дочернего планшета
	 */
	function createChild() {
		$this->doScenarioSave(Tablet_model::createChild);
	}
}
