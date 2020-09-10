<?php
/**
 * MedStaffFactLink_helper - хелпер c функциями для работы со связью врача и среднего мед персонала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      25.09.2013
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Возращает список идентификаторов врачей из сессии, которые связаны с пользователем
 * @return array
 */
function getMedPersonalListWithLinks() {
	$med_personal_list = array();
	if (!empty($_SESSION['medpersonal_id'])) {
		$med_personal_list[] = $_SESSION['medpersonal_id'];
	}
	if (!empty($_SESSION['MedStaffFactLinks'])) {
		foreach($_SESSION['MedStaffFactLinks'] as $item) {
			if (!empty($item['MedPersonal_id'])) {
				$med_personal_list[] = $item['MedPersonal_id'];
			}
		}
	}
	return $med_personal_list;
}

/**
 * Возвращает список отделений, со всех мест работы врача в МО
 * @param $Lpu_id
 * @param $MedPersonal_id
 * @return array|bool
 */
function getLpuSectionListFromMSF($Lpu_id, $MedPersonal_id) {
	$CI = & get_instance();
	$CI->load->model('MedPersonal_model', 'mpmodel');

	$resp = $CI->mpmodel->loadLpuSectionList(array('Lpu_id' => $Lpu_id, 'MedPersonal_id' => $MedPersonal_id));
	if (!is_array($resp)) {
		return false;
	}

	$lpu_section_list = array();
	foreach($resp as $item) {
		$lpu_section_list[] = $item['LpuSection_id'];
	}
	return $lpu_section_list;
}