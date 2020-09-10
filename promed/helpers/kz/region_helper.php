<?php
/**
* Region_helper - хелпер для работы с регионами для казахстана
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
*/

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * @return array
 * Получение регионов Казахстана из config
 */
function getKzRegions() {
	//Получаем конфиги для KZ доступные из контроллера
	$ci =& get_instance();
	return $ci->config->config['portal']['kz_regions'];
}

/**
 * @return string
 * Получение основного, настраиваемового домена для казахстана
 */
function getKzMainDomain() {
	$ci =& get_instance();
	return $ci->config->config['portal']['kz_main_domain'];
}

/**
 * @return string
 * Получение названия региона для Казахстана
 */
function getKzRegionTitle() {
	
	$regions = getKzRegions();
	$key = array_search($_SERVER['SERVER_NAME'], array_column($regions, 'name'));
	
	if($key !== false) {
		return $regions[$key]['title'];
	}
	
	return false;
}

/**
 * @return string
 * Получение текущего региона для Казахстана
 */
function getKzRegionUrl($region_name) {
	$regions = getKzRegions();
	$main_domain = getKzMainDomain();

	$key = array_search($region_name, array_column($regions, 'name'));

	if($key !== false) {
		$region_url = parse_url($regions[$key]['url']);
		$region_domain = $region_url['host'];

		if($region_domain != $main_domain) {
			return $regions[$key]['url'];
		}
	}

	return false;
}