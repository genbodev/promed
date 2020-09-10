<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Auth_helper - хелпер с функциями для работы с системой аутентификации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan (megatherion@list.ru)
* @version      11.09.09
*/

/**
 * Возвращает набор записей из LDAP по переданному запросу
 *
 * @param string $base_dn Базовый путь
 * @param string $filter запрос к LDAP
 * @return array Информация из LDAP в многомерном массиве
 */
function ldap_query($base_dn, $filter, $attrs = null)
{
	$CI = & get_instance();
	$IsLocalSMP = $CI->config->item('IsLocalSMP');
	if ($IsLocalSMP === true) {
		return array();
	}
	$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ds) {
		if (!$r=@ldap_bind($ds,LDAP_USER,LDAP_PASS)) {
			DieWithError('Перегружен сервер авторизации!');
		}

		if (isset($attrs)) {
			$sr = ldap_search($ds, $base_dn, $filter, $attrs);
		} else {
			$sr = ldap_search($ds, $base_dn, $filter);
		}

		if ($sr === false) {
			$CI =& get_instance();
			$CI->load->library('textlog', array('file'=>'LDAP_'.date('Y-m-d').'.log'));
			$CI->textlog->add( 'ldap_search: WHERE: '."{$base_dn}". '| QUERY: '."{$filter}" );
			$CI->textlog->add( 'ЗАПРОС НЕ ВЫПОЛНИЛСЯ: ldap_search: WHERE: '."{$base_dn}". '| QUERY: '."{$filter}" );
		}
		$info = ldap_get_entries($ds, $sr);
		ldap_unbind( $ds ); 
		return $info;
	} else {
		DieWithError("Невозможно соединиться с сервером авторизации.");
	}
}


/**
 * Вносит переданный объект в LDAP
 *
 * @param string $base_dn Базовый путь
 * @param array $entry Набор атрибутов
 */
function ldap_insert($base_dn,$entry) {
	$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ds) {
		$r=ldap_bind($ds,LDAP_USER,LDAP_PASS);
		if (!@ldap_add($ds, $base_dn, $entry)) {
			$error=ldap_error($ds);
			$CI = & get_instance();
			$CI->load->library('textlog', array('file'=>'LDAP_'.date('Y-m-d').'.log'));
			$CI->textlog->add('ldap_insert '.var_export($entry, true));
			$CI->textlog->add('ldap_insert error '.$error);
			die('Ошибка ldap_edit: '.$error."\n\rПараметры\n\r".var_export($entry, true));
		}
		ldap_unbind( $ds ); 
	} else {
		die("Unable to connect to LDAP server");
	}
}

/**
 * Редактирует существующий объект в LDAP
 *
 * @param string $base_dn Базовый путь
 * @param array $entry Набор атрибутов
 */
function ldap_edit($base_dn,$entry) {
	$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ds) {
		$r=ldap_bind($ds,LDAP_USER,LDAP_PASS);
		if (!@ldap_modify($ds, $base_dn, $entry)) {
			$error=ldap_error($ds);
			$CI =& get_instance();
			$CI->load->library('textlog', array('file'=>'LDAP_'.date('Y-m-d').'.log'));
			$CI->textlog->add('ldap_modify '.var_export($entry, true));
			$CI->textlog->add('ldap_modify error '.$error);
			die('Ошибка ldap_modify: '.$error."\n\rПараметры\n\r".var_export($entry, true));
		}
		ldap_unbind( $ds ); 
	} else {
		die("Unable to connect to LDAP server");
	}
}

/**
 * Изменяется заданные атрибуты в объекте LDAP
 *
 * @param string $base_dn Базовый путь
 * @param array $entry Набор атрибутов
 */
function ldap_insertattr($base_dn,$entry) {
	$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ds) {
		$r=ldap_bind($ds,LDAP_USER,LDAP_PASS);
		if (isSuperAdmin() && !empty($_REQUEST['isDebug'])) {
			var_dump(array($ds,$base_dn, $entry));
		}
		ldap_mod_add($ds,$base_dn, $entry);
		ldap_unbind( $ds );
	} else {
		die("Unable to connect to LDAP server");
	}
}


/**
 * Удаляет запись из LDAP
 *
 * @param string $base_dn Базовый путь
 */
function ldap_remove($base_dn) {
	$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ds) {
		$r=ldap_bind($ds,LDAP_USER,LDAP_PASS);
		ldap_delete($ds,$base_dn);
		ldap_unbind( $ds ); 
	} else {
		die("Unable to connect to LDAP server");
	}
}


/**
 * Удаляет значения заданных атрибутов из LDAP
 *
 * @param string $base_dn Базовый путь
 * @param array $entry Набор атрибутов
 */
function ldap_removeattr($base_dn,$entry) {
	$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ds) {
		$r=ldap_bind($ds,LDAP_USER,LDAP_PASS);
		ldap_mod_del($ds,$base_dn,$entry);
		ldap_unbind( $ds ); 
	} else {
		die("Unable to connect to LDAP server");
	}
}