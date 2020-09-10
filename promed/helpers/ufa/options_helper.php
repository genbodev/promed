<?php
/**
* Options_helper - хелпер для работы с настройками
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stanislav aka Savage (savage@swan.perm.ru)
* @version      ?
*/
defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'helpers/options_helper.php');

/**
 *	Получение настроек по-умолчанию, отличающихся от общих
 *	Пока сделал переопределение целой ветки настроек
 */
function getRegionOptions() {
	return array(
		'appearance' => array(
			'menu_type' => 'simple'
		),
		'usluga' => array(
			'allowed_usluga' => 'all'
		),
		'stac' => array(
			'stac_schedule_time_binding' => ($_SESSION['region']['nick'] == 'ufa') ? 1 : 2
		)
	);
}
?>