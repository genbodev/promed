<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QcRuleLpu - Контроллер для добавления правил контроля качества
 * 
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      01.07.2019
 */
require_once('LisScenario.php');
class QcRuleLpu extends LisScenario {
	var $model_name = "QcRuleLpu_model";
}