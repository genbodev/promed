<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * QcControlSeries - Контроллер для работы с формой "Контрольные серии" (swQcControlSeriesWindow)
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) EMSIS.
 * @author       Magafurov Salavat
 * @version      01.07.19
 */
require_once('LisScenario.php');
class QcControlSeries extends LisScenario {
	var $model_name = "QcControlSeries_model";

	/**
	 * Расчет стадии Xcp, S, CV,
	 */
	function calculateForStage () {
		$this->doScenarioSave(QcControlSeries_model::SCENARIO_CALCULATE);
	}
}