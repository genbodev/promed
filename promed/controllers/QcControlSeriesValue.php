<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * QcControlSeriesValue - Контроллер для добавления значений контрольных серий (swQcControlSeriesWindow)
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) EMSIS.
 * @author       Magafurov Salavat
 * @version      01.07.19
 */
require_once('LisScenario.php');
class QcControlSeriesValue extends LisScenario {
	var $model_name = 'QcControlSeriesValue_model';
}