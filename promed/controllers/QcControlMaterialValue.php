<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * QcControlMaterialValue - Контроллер для работы с формой "Контрольные материалы" (swQcControlMaterialValueWindow)
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) EMSIS.
 * @author       Magafurov Salavat
 * @version      01.07.19
 */

require_once('LisScenario.php');
class QcControlMaterialValue extends LisScenario {
	var $model_name = 'QcControlMaterialValue_model';

	/**
	 * Загрузка параметров CV10,B10,CV20,B20 со справочника
	 */
	function getMaxValues() {
		$this->doScenarioLoad(QcControlMaterialValue_model::SCENARIO_GET_MAXVALUES);
	}

	/**
	 * Загрузка сводного грида
	 */
	function loadSvodGrid() {
		$this->doScenarioLoad(QcControlMaterialValue_model::SCENARIO_LOAD_SVOD_GRID);
	}

	/**
	 * 
	 */
	function loadUslugaComplex()
	{
		$this->doScenarioLoad(QcControlMaterialValue_model::SCENARIO_LOAD_USLUGACOMPLEX);
	}
}