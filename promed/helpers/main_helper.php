<?php
/**
 * Main_helper - хелпер с самыми базовыми функциями :)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan (megatherion@list.ru)
 * @version      ?
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Проверка права действия $role над объектом $window
 */
function checkRole($window, $role) {
	if(!empty($_SESSION['setting']['roles']['windows'][$window][$role])) {
		return $_SESSION['setting']['roles']['windows'][$window][$role];
	} else {
		return false;
	}
}

/**
 * Возвращает значение из массива по ключу, если значение не найдено
 * возвращает пустую строку. Используется для подавления ошибки "Index not found".
 *
 * @param array $ar
 * @param string $sIndex Индекс в ассоциативном массиве
 * @param string $sDefaultValue Значение возвращаемое если ключ не найден
 * @return string
 */
function ArrayVal(array &$ar, $sIndex, $sDefaultValue = "" ) {
	if( isset( $ar[$sIndex] ) && !(empty($ar[$sIndex]) and $ar[$sIndex]!="0"))
		return $ar[$sIndex];
	else
		return $sDefaultValue;
}

/**
 * Возвращает значение из массива по ключу, если значение не найдено
 * возвращает пустую строку. Используется для подавления ошибки "Index not found"
 * Заключает значение в кавычки
 *
 * @param array $ar
 * @param string $sIndex Индекс в ассоциативном массиве
 * @param string $sDefaultValue Значение возвращаемое если ключ не найден
 * @return string
 */
function ArrayStrVal( &$ar, $sIndex, $sDefaultValue = "null" ) {
	if( isset( $ar[$sIndex] ) )
		return "'".$ar[$sIndex]."'";
	else
		return $sDefaultValue;
}

/**
 * Аналог coalesce из t-sql
 *
 * @param mixed $val1
 * @param mixed $val2 [optional]
 * @param mixed $_ [optional]
 * @return mixed
 */
function coalesce($val1, $val2 = null, $_ = null) {
	$args = func_get_args();
	foreach ($args as $arg) {
		if (!empty($arg)) {
			return $arg;
		}
	}
	return null;
}

/**
 * Мержим права. array_merge с логикой.
 */
function mergeRoles(&$roles, $newroles) {
	// видимо цикл и рекурсия решает
	foreach($newroles as $key=>$role) {
		if ($role !== 'hidden') {
			if (is_array($role)) { // если есть и массив, то пойдем в рекурсию
				mergeRoles($roles[$key], $role);
			} elseif (empty($roles[$key]) || ($role > $roles[$key])) { // если не массив и true > false, то возьмём true
				$roles[$key] = $role;
			}
		}
	}
	return $roles;
}

/**
 * Функция осуществляет проверку ИНН по контрольному разряду
 *
 * @param string $inn ИНН человека
 * @return boolean
 */
function CheckInn( $inn ) {
	$result = true;

	if ( (strlen($inn) == 12) && (preg_match('/^\d{12}$/', $inn)) ) {
		$c1 = 0;
		$c2 = 0;
		$sum = 0;

		$koef_1 = array(7, 2, 4, 10, 3, 5, 9, 4, 6, 8, 0);
		$koef_2 = array(3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8, 0);

		for ( $i = 0; $i < count($koef_1); $i++ ) {
			$sum += intval($inn[$i]) * $koef_1[$i];
		}

		$c1 = $sum % 11 % 10;

		$sum = 0;

		for ( $i = 0; $i < count($koef_2); $i++ ) {
			$sum += intval($inn[$i]) * $koef_2[$i];
		}

		$c2 = $sum % 11 % 10;

		if ( $c1 != intval($inn[10]) || $c2 != intval($inn[11]) ) {
			$result = false;
		}
	}
	else {
		$result = false;
	}

	return $result;
}

/**
 * Возвращает значение из массива по ключу, если значение не найдено
 * возвращает пустую строку. Используется для подавления ошибки "Index not found"
 * Заключает значение в кавычки и %
 *
 * @param array $ar
 * @param string $sIndex Индекс в ассоциативном массиве
 * @param string $sDefaultValue Значение возвращаемое если ключ не найден
 * @return string
 */
function ArrayStrBothLikeVal( &$ar, $sIndex, $sDefaultValue = "%" ) {
	if( isset( $ar[$sIndex] ) )
		return "'%".$ar[$sIndex]."%'";
	else
		return $sDefaultValue;
}

/**
 * Подключает javascript файлы.
 *
 * Проверяет права и кэширует последнюю дату изменения в параметре.
 * @param string $file Подключаемый javascript файл.
 */
function show_stamped_JS($file) {
	// global $_USER;
	// $ff = pathinfo( $file );
	// If ((isset($_USER) && $_USER->canLoadForm($ff['filename'])) || !isset($_USER)) { // Проверяем что имеем права на загрузку этой формы

	$config = &get_config();
	if ($config['develop']) { // добавляем ?filetime только для девелоп-режима
		$fileName = $_SERVER['DOCUMENT_ROOT'] . preg_replace("/^(.*)\.js\??(.*)$/", '$1.js', $file);
		if (file_exists($fileName))
			$t = filemtime($fileName);
		else
			$t = "";
		$file .= ( strpos($file, ".js?") === false && !(strpos($file, ".js") === false) ? "?" : "").$t;
	}
	echo "<script src='", $file, "' type='text/javascript'></script>\r\n";
}

/**
 * Подключает CSS файлы.
 *
 * Кэширует последнюю дату изменения в параметре.
 *
 * @param string $file Подключаемый CSS файл.
 */
function show_stamped_CSS($file) {
	$t = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
	echo "<link rel='stylesheet' type='text/css' href='$file?", $t, "' />\r\n";
}

/**
 * Возвращает ошибку в формате json
 *
 *
 * @access	public
 * @param string $err Строка ошибки
 * @return	string
 */
function json_return_errors($err, $cansel_error_handler = false) {
	$val = array('success' => false, 'Error_Msg' => $err);
	if ( $cansel_error_handler === true )
		$val['Cancel_Error_Handle'] = true;
	array_walk($val, 'ConvertFromWin1251ToUTF8');
	return json_encode($val);
}

/**
 * Возвращает ошибку ajax запроса
 */
function ajaxErrorReturn() {
	die;
}

/**
 * Проверка того, что пользователь залогинен
 * и вывод сообщения об ошибке с командой перелогинивания клиенту
 *
 * @return boolean
 */
function checkLogin() {
	if ((!isset($_SESSION['pmuser_id'])) || (!is_numeric($_SESSION['pmuser_id'])) || ($_SESSION['pmuser_id'] <= 0)) {
		DieWithLoginError();
	}

	if (!isset($_SESSION['login'])) {
		DieWithLoginError();
	}
	return true;
}

/**
 * Завершает выполнения текущего скрипта и возвращает ошибку на клиента
 *
 * @param string $err Строка с описание ошибки
 */
function DieWithError($err) {
	if (isXMLHttpRequest()) {
		// https://redmine.swan.perm.ru/issues/21966
		// Пользователю выдавался "Неверный ответ сервера", если $err содержал двойную кавычку
		$err = str_replace('"', '&quot;', $err);

		die('{
			success: false,
			Error_Msg: "'.$err.'"
		}');
	}
	else {
		die("<h1>".htmlspecialchars($err, ENT_QUOTES, 'windows-1251')."</h1>");
	}
}


/**
 * Завершает выполнения текущего скрипта, возвращает ошибку на клиента и перекидывает на форму логина
 */
function DieWithLoginError() {
    if (isXMLHttpRequest()) {
		die("{Action: 'logout'}");
	} else {
        die("<html><head><META HTTP-EQUIV='REFRESH' CONTENT='0;URL=/?c=portal&m=promed&from=promed'></head><body></body></html>");
    }
}

/**
 * Определяет является ли запрос серверу AJAX запросом
 */
function isXMLHttpRequest() {
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    }
    else
    {
        return false;
    }
}


/**
 * Проверка на суперадминистратора по данным в сессии
 */
function isSuperadmin() {
	return havingGroup('SuperAdmin');
}

/**
 * Проверка на пользователя СМО
 */
function isSMOUser() {
	return havingGroup('SMOUser');
}

/**
 * Проверка на пользователя ТФОМС
 */
function isTFOMSUser() {
	return havingGroup('TFOMSUser');
}

/**
 * Проверка на специалиста МЗ
 */
function isOuzSpec() {
	return havingGroup('OuzSpec');
}

/**
 * Проверка на доступ к экспорту прикрепленного населения
 */
function isExpPop() {
	return havingGroup('ExportAttachedPopulation');
}

/**
 * Проверка на пользователя ТОУЗ
 */
function isTOUZuser() {
	$CI = & get_instance();
	$CI->load->model('Org_model');
	if (havingGroup('OuzSpec') && is_array($_SESSION['orgs']) && count($_SESSION['orgs']) > 0) {
		return $CI->Org_model->checkIsTouzOrg(array('orgs' => $_SESSION['orgs']));
	}

	return false;
}

/**
 * Проверка на присутсвие группы в списке групп пользователя
 * Или хоть в одной группе если передан массив
 */
function havingGroup($group, $defined_groups = null) {
	global $_USER;
	if (isset($_USER)) {
		if (is_array($group)) {
			return $_USER->hasOneOfGroup($group, $defined_groups);
		} else {
			return $_USER->havingGroup($group, $defined_groups);
		}
	}
	return false;
}

/**
 * @return mixed
 */
function getUser() {
	global $_USER;
	return $_USER;
}

/**
 * Проверка на присутсвие организации в списке организаций пользователя
 */
function havingOrg($org) {
	global $_USER;
    if ( isset($_USER) && $_USER->havingOrg($org) ) {
		return true;
	} else {
        return false;
	}
}

/**
 * Проверка на администратора аптеки по данным в сессии
 */
function isFarmacyadmin() {
	return havingGroup('FarmacyAdmin');
}

/**
 * Проверка на администратора ЛПУ по данным в сессии
 * Если передано $Lpu_id то проверяет, что администратор именно этой ЛПУ
 */
function isLpuAdmin($Lpu_id = NULL) {
	global $_USER;
	// if ( isset($_SESSION['groups']) && preg_match("/LpuAdmin/", $_SESSION['groups']) ) {
	if ( isset($_USER) && ( $_USER->hasOneOfGroup(array('OrgAdmin','LpuAdmin')) ) && $_USER->belongsToOrg() ) {
		if (!isset($Lpu_id)) {
			return true;
		} else {
			if ($Lpu_id == $_SESSION['lpu_id']) {
				return true;
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
}

/**
 * Проверка на арм мед.статиста по данным в сессии
 */
function isMstatArm($data) {
	return (isset($data['session']['CurARM']['ARMType']) && $data['session']['CurARM']['ARMType'] == 'mstat') ? 1 : 0;
}


/**
 * Проверка на то, что пользователь является пользователем ЛПУ
 * Если передано $Lpu_id то проверяет, что пользователь именно этой ЛПУ
 */
function isLpuUser($Lpu_id = NULL) {
	global $_USER;
	// if ( isset($_SESSION['groups']) && preg_match("/LpuUser/", $_SESSION['groups']) ) {
	if ( isset($_USER) && $_USER->hasOneOfGroup(['OrgUser', 'LpuUser']) && $_USER->belongsToOrg() ) {
		if (!isset($Lpu_id)) {
			return true;
		} else {
			if ($Lpu_id == $_SESSION['lpu_id']) {
				return true;
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
}

/**
 * Проверка, имеет ли пользователь доступ к просмотру кадровой инфы.
 */
function isCadrUserView() {
	return havingGroup(array('LpuCadrView', 'RosZdrNadzorView', 'SuperAdmin', 'LpuAdmin'));
}

/**
 * Проверка, входит ли пользователь в группу  LpuTariffSpec (Специалиста по тарификации)
 */
function isLpuTariffSpec() {
	return havingGroup('LpuTariffSpec');
}

/**
 * Проверка, входит ли пользователь только в группы просмотра кадровой инфы.
 */
function onlyCadrUserView() {
	global $_USER;
	return havingGroup(array('LpuCadrView', 'RosZdrNadzorView')) && $_USER->getGroupCount() == 1;
}

/**
 * Получение признака является ли текущая ЛПУ онкодиспансером
 */
function isOnkoGem()
{
	return (in_array($_SESSION['lpu_id'], array(13002429, 10011387))); // Онкогематология (ГКП 5, Чернушинская ЦРП)
}

/**
 * Получение признака является ли текущая ЛПУ для психов
 */
function isPsih()
{
	$CI = &get_instance();
	return property_exists($CI, 'config') && is_array($CI->config->item('PsychoLpuList')) && in_array($_SESSION['lpu_id'], $CI->config->item('PsychoLpuList'));
}

/**
 * Получение признака, является ли текущая ЛПУ чем-то там
 */
function isRA()
{
	if (in_array($_SESSION['lpu_id'], array(150185, 13002524, 10011165, 150184, 10011114,10011387,13002450,150262,13002550,13002551,10011058,10011059,10011181,150231,10010904,13002449,13002455,150219,150220,150222,10010914,10011386,13002447,10011216,13002446,10011377,10010881,13002375,10010987,10010988,10010989,10011378,10010862,10011365,10010863,10010819,10010820,10011060,10011063,10010868,150225,13002404,13002534,10011396,10010818,10011397,10010832,150258,150260,10011105,10011107))) // ПЕРМЬ ККБ и ПЕРМЬ ККВД и ПКДКБ + ПЕРМЬ КБ 3 ЦЕНТР ДИАЛИЗА #58578
		return true;
	else
		return false;
}

/**
 * Получение признака, является ли текущая ЛПУ ПЕРМЬ МСЧ ГУВД
 */
function isGuvd()
{
	if ($_SESSION['lpu_id']==10011372) // ПЕРМЬ МСЧ ГУВД
		return true;
	else
		return false;
}


/**
 * Получение признака является ли текущая организация минздравом
 */
function isMinZdrav()
{
	if($_SESSION['region']['nick']=='kz')
		return false;
	else
		return ((isset($_SESSION) && isset($_SESSION['org_id']) && in_array($_SESSION['org_id'], array(13002451,13002457,102,68320081956,68320020775)))); // для тестовой и для рабочей базы получились разные ID
}

/**
 * Проверка наличия АРМа
 */
function haveARMType($ARMType)
{
	return (isset($_SESSION['ARMList']) && is_array($_SESSION['ARMList']) && in_array($ARMType, $_SESSION['ARMList']));
}

/**
 * Проверка текущего АРМа
 */
function isCurrentARMType($ARMType)
{
	return (isset($_SESSION['CurARM']['ARMType']) && $ARMType == $_SESSION['CurARM']['ARMType']);
}

/**
 * Получение признака является ли текущая организация минздравом или не лпу.
 */
function isMinZdravOrNotLpu()
{
	return (empty($_SESSION['lpu_id']) || isMinZdrav());
}

/**
 * Получение признака промед запущен в режиме аптеки или нет
 */
function isFarmacy()
{
	return havingGroup(array('FarmacyUser','FarmacyAdmin','FarmacyNetAdmin'));
}

/**
 * Признак, что ЛПУ является онкодиспансером
 */
function isOnko()
{
	if ($_SESSION['lpu_id']==10011168)
		return true;
	else
		return false;
}

/**
 * Определение регионов с шифрованием ВИЧ-инфицированных
 */
function isEncrypHIVRegion($regionNick) {
	return in_array($regionNick, array('astra','kaluga'));
}

/**
 * Разрешение на отображение зашифрованных ВИЧ-инфицированных
 */
function allowPersonEncrypHIV($session = null) {
	if (isset($_SESSION) && empty($session)) {
		$session = $_SESSION;
	}
	// только пользователям СПИД-центров (у МО пользователя проставлен «особый статус» в Паспорте МО)
	return (isset($session)
		&& is_array($session)
		&& isset($session['region'])
		&& isEncrypHIVRegion($session['region']['nick'])
		&& isset($session['setting'])
		&& isset($session['setting']['server'])
		&& is_array($session['setting']['server'])
		&& array_key_exists('lpu_is_secret', $session['setting']['server'])
		&& $session['setting']['server']['lpu_is_secret'] === true
	);
}

/**
 * Разрешение на редактирование зашифрованных ВИЧ-инфицированных
 */
function allowEditPersonEncrypHIV($session) {
	return (allowPersonEncrypHIV($session)
		&& havingGroup('HIVRegistry')
		&& isset($session['ARMList']) && is_array($session['ARMList']) && in_array('regpol',$session['ARMList'])
	);
}

/**
 * Поиск по шифру
 */
function isSearchByPersonEncrypHIV($value) {
	return (
		// по шифру ищут только по полному совпадению?
		mb_strlen($value) == 12 //  если да
		// mb_strlen($value) >= 2 //  если нет
		&& is_numeric(mb_substr($value, 0, 2))
	);
}

/**
 * Склеивает ассоциативный массив парами. Пропускает поля, значения которых пустые
 */
function ImplodeAssoc( $sPairGlue, $sSeparator, $ar, $bUrlEncode = False )
{
	$sResult = "";
	foreach( $ar as $sKey => $sValue )
	{
		if( is_array( $sValue ) )
		{
			if( $bUrlEncode )
				$sKey = urlencode( $sKey . "[]" );
			foreach ( $sValue as $s )
			{
                if (isset($s) && $s != '' && $s != 'null' ) {
                    if( $sResult != "" )
                        $sResult .= $sSeparator;
                    if( $bUrlEncode )
                        $s = urlencode( $s );
                    $sResult .= $sKey . $sPairGlue . $s;
                }
			}
		}
		else {
            if (isset($sValue) && $sValue != '' && $sValue != 'null' ) {
                if( $sResult != "" )
                    $sResult .= $sSeparator;
                if( $bUrlEncode )
                    $sValue = urlencode( $sValue );
                $sResult .= $sKey . $sPairGlue . $sValue;
            }
		}
	}
	return $sResult;
}
/**
 * Merges any number of arrays / parameters recursively, replacing
 * entries with string keys with values from latter arrays.
 * If the entry or the next value to be assigned is an array, then it
 * automagically treats both arguments as an array.
 * Numeric entries are appended, not replaced, but only if they are
 * unique
 *
 * calling: result = array_merge_recursive_distinct(a1, a2, ... aN)
 */
function array_merge_recursive_distinct () {
	$arrays = func_get_args();
	$base = array_shift($arrays);
	if(!is_array($base)) $base = empty($base) ? array() : array($base);
	foreach($arrays as $append) {
		if(!is_array($append)) $append = array($append);
		foreach($append as $key => $value) {
			if(!array_key_exists($key, $base) and !is_numeric($key)) {
				$base[$key] = $append[$key];
				continue;
			}
			if(is_array($value) or is_array($base[$key])) {
				$base[$key] = array_merge_recursive_distinct($base[$key], $append[$key]);
			} else if(is_numeric($key)) {
				if(!in_array($value, $base)) $base[] = $value;
			} else {
				$base[$key] = $value;
			}
		}
	}
	return $base;
}


/**
 *	Рекурсивное объединение двух массивов
 *	(array_merge_recursive_distinct почему-то не работает)
 */
function mergeArraysRecursive(&$options, $regOptions = array()) {
	if ( is_array($regOptions) && count($regOptions) > 0 ) {
		foreach ( $regOptions as $key => $value ) {
			if ( !array_key_exists($key, $options) ) {
				$options[$key] = $value;
			}
			else if ( is_array($value) && count($value) > 0 ) {
				mergeArraysRecursive($options[$key], $value);
			}
			else {
				$options[$key] = $value;
			}
		}
	}
}

/**
 * Преобразует объект в массив
 * @param $object
 * @return array
 */
function objectToArray( $object ) {
	if( !is_object( $object ) && !is_array( $object ) ) {
		return $object;
	}
	if( is_object( $object ) ) {
		$object = get_object_vars( $object );
	}
	return array_map( 'objectToArray', $object );
}

/**
 * Получение списка сокращений для дней недели
 */
function shortDayNames($index)
{
	$shortDayNames = array(
		0 => "ВС",
		1 => "ПН",
		2 => "ВТ",
		3 => "СР",
		4 => "ЧТ",
		5 => "ПТ",
		6 => "СБ",
	);
	return $shortDayNames[$index];
}
/*
function json_fix_cyr($var)
{
    if (is_array($var)) {
       $new = array();
       foreach ($var as $k => $v) {
          $new[json_fix_cyr($k)] = json_fix_cyr($v);
       }
       $var = $new;
    } elseif (is_object($var)) {
       $vars = get_object_vars($var);
       foreach ($vars as $m => $v) {
          $var->$m = json_fix_cyr($v);
       }
    } elseif (is_string($var)) {
       $var = toUTF($var);
    }
    return $var;
}

function json_safe_encode($var)
{
   return json_encode(json_fix_cyr($var));
}
*/
/**
 * Приведение из формата PHP к формату JS
 */
function php2js($a=false)
{
	if (is_null($a)) return 'null';
	if ($a === false) return 'false';
	if ($a === true) return 'true';
	if (is_scalar($a))
	{
		if (is_float($a))
		{
			$a = str_replace(",", ".", strval($a));
		}

		static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
		array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
		return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
	}
	$isList = true;
	for ($i = 0, reset($a); $i < count($a); $i++, next($a))
	{
		if (key($a) !== $i)
		{
			$isList = false;
			break;
		}
	}
	$result = array();
	if ($isList)
	{
		foreach ($a as $v) $result[] = php2js($v);
		return '[ ' . join(', ', $result) . ' ]';
	}
	else
	{
		foreach ($a as $k => $v) $result[] = php2js($k).': '.php2js($v);
		return '{ ' . join(', ', $result) . ' }';
	}
}

/**
 * Логирование SQL запросов
 */
function sql_log_message($level = 'error', $message, $sql)
{
	static $_log;

	$config =& get_config();
	if ($config['log_threshold'] == 0)
	{
		return ;
	}

	if ($_log === NULL)
	{
		// references cannot be directly assigned to static variables, so we use an array
		$_log[0] =& load_class('Log', 'core');
	}

	$_log[0]->sql_write_log($level, $message, $sql);
}

/**
 * Обработчик исключений
 */
function exception_handler($exception) {
	$config = &get_config();
	if ((!empty($config['IS_DEBUG']) && $exception instanceOf Error) || !empty($_REQUEST['errorDebug'])) {
		// ошибки в PHP 7 выдают исключения типа Error (в том числе ParseError), хорошо бы иметь возможность знать о них больше
		// включаем их отображение для IS_DEBUG и включаем отображение всех ошибок при переданном параметре errorDebug
		var_dump($exception);
	}
	DieWithError($exception->getMessage());
}
set_exception_handler('exception_handler');

/**
 * Возвращает ошибку SOAP и прекращает
 */
function DieWithSoapFault($code, $text = NULL)
{
	throw new SoapFault((string)$code, toUTF($text));
}

/**
 * Вызов скрипта с другого сервиса (вебсервера)
 */
function exec_php($url, $params = array())
{
	$parts = parse_url($url);
	$data = http_build_query($params);
	$responce = file_get_contents($url.'?'.$data);

	return $responce;
}

/**
 * Поиск файла по маске
 * Используется в кэшировании JS файлов
 */
function sdir( $path='.', $mask='*', $nocache=0 ) {
	static $dir = array(); // cache result in memory
	$sdir = array();
	if ( !isset($dir[$path]) || $nocache) {
		$dir[$path] = scandir($path);
	}
	foreach ($dir[$path] as $i=>$entry) {
		if ($entry!='.' && $entry!='..' && fnmatch($mask, $entry) ) {
			$sdir[] = $entry;
		}
	}
	return ($sdir);
}

/**
 * Возвращает номер региона
 * 0	undefined('Неопределенный'),
 * 2	ufa('Уфа'),
 * 11	komi('Сыктывкар'),
 * 28	amur('Белогорск'),
 * 59	perm('Пермь'),
 * 60	pskov('Псков'),
 * 68	tambov('Тамбов'),
 * 77	msk('Москва')
 */
function getRegionNumber() {
	if (empty($_SESSION) || empty($_SESSION['region']) || empty($_SESSION['region']['number']))
	{
		$CI = & get_instance();
		$CI->load->model('Options_model', 'Options_model');
		$number = $CI->Options_model->getRegionNumber();
		$_SESSION['region']['number'] = $number;
		return $number;
	}
	else
	{
		//берем из сессии, если сессия уже создана
		return $_SESSION['region']['number'];
	}
}

/**
 * Получение Nick региона
 */
function getRegionNick() {
	$CI = &get_instance();
	return $CI->load->getRegionNick();
}

/**
 * Получение SysNick типа оплаты по ОМС
 *
 * Аналогично функции getPayTypeSysNickOMS() в \jscore\libs\swFunctions.js
 */
function getPayTypeSysNickOMS() {
	$PayType_SysNick = 'oms';
	switch ( getRegionNick() ) {
		case 'by': $PayType_SysNick = 'besus'; break;
		case 'kz': $PayType_SysNick = 'Resp'; break;
	}
	return $PayType_SysNick;
}

/**
 * Возвращает строку наименования МЭСа в зависимости от региона
 */
function getMESAlias() {
	switch(getRegionNumber()) {
		case 63://Самара
			$s = 'КСГ';
			break;
		default:
			$s = 'МЭС';
	}
	return $s;
}

/**
 * Отправка сообщения в ФЭР
 */
function sendFerStompMessage($message, $object) {
	if (defined('STOMP_MESSAGE_ENABLE') && defined('STOMP_MESSAGE_SERVER_URL') && STOMP_MESSAGE_ENABLE === TRUE) {
		$CI = &get_instance();
		$CI->load->library('textlog', array('file'=>'FerStomp_'.date('Y-m-d').'.log'));
		try {
			$stomp = new Stomp(STOMP_MESSAGE_SERVER_URL, 'system', 'manager');
			$stomp->setReadTimeout(STOMP_MESSAGE_TIMEOUT);
			$destination = STOMP_MESSAGE_DESTINATION_RULE;
			// разбивка сообщений по очередям
			if (!empty($message['action']) && in_array($message['action'], array('Reserv','RecPatient','FreeTicket','FreeTag_CancelDirect'))) {
				$destination = STOMP_MESSAGE_DESTINATION_SLOT;
			}
			if ($CI->config->item('IS_DEBUG')) {
				$CI->textlog->add("destination: {$destination}. message: ".json_encode($message).". object: {$object}.");
			}
			$stomp->send($destination, json_encode($message), array('object' => $object));
		} catch (Exception $e) {
			// запишем ошибку в лог
			$CI->textlog->add('Ошибка отправки STOMP-сообщения: '.$e->getMessage());
		}
	}

	return true;
}

/**
 * Отправка сообщения в брокер сообщений(#88396)
 * пока тестовая обкатка, потом переделывать anyway
 */
function sendStompMQMessage($message, $object, $destination) {
	if (defined('STOMPMQ_MESSAGE_ENABLE') && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE) {
		$CI = &get_instance();
		$CI->load->library('textlog', array('file'=>'StompMQ_'.date('Y-m-d').'.log'));
		try {
			$stomp = new Stomp(STOMPMQ_MESSAGE_SERVER_URL, 'system', 'manager');
			$stomp->setReadTimeout(STOMPMQ_MESSAGE_TIMEOUT);
			if ($CI->config->item('IS_DEBUG')) {
				$CI->textlog->add("destination: {$destination}. message: ".json_encode($message).". object: {$object}.");
			}
			if(isset($_GET['getDebug'])){
				echo "destination: {$destination}. message: ".json_encode($message).". object: {$object}.";
			}
			//var_dump($message);
			$stomp->send($destination, json_encode($message), array('object' => $object));
		} catch (Exception $e) {
			// запишем ошибку в лог
			$CI->textlog->add('Ошибка отправки STOMP-сообщения: '.$e->getMessage());
		}
	}

	return true;
}
/**
 * Отправка сообщения в брокер сообщений(#88396)
 * используя старую php-шную библиотеку Stomp, так как Dll библиотека правильно формировать сообщение отказывается
 */
function sendStompMQMessageOld($message, $object, $destination) {
	$CI = &get_instance();
	$CI->load->library('StompOld/StompOld');
	$CI->load->library('textlog', array('file'=>'StompMQ_'.date('Y-m-d').'.log'));
	$CI->textlog->add("const: ".STOMPMQ_MESSAGE_ENABLE." destination: {$destination}. message: ".json_encode($message).". object: {$object}.");
	if (defined('STOMPMQ_MESSAGE_ENABLE') && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE) {

		try {
			$stomp = new StompOld(STOMPMQ_MESSAGE_SERVER_URL);
			$stomp->setReadTimeout(STOMPMQ_MESSAGE_TIMEOUT);

			$stomp->connectAndSendOld($destination, $message, $object);
		} catch (Exception $e) {
			// запишем ошибку в лог
			$CI->textlog->add('Ошибка отправки STOMP-сообщения: '.$e->getMessage());
		}
	}
	return true;
}

/**
 * Функция определения мобильного устройства
 * todo: нужно доработать или заменить на фри и доступную
 */
function isMobileAgent($iphone=true,$ipad=true,$android=true,$opera=true,$blackberry=true,$palm=true,$windows=true){
	$mobile_browser   = false; // set mobile browser as false till we can prove otherwise
	$user_agent       = $_SERVER['HTTP_USER_AGENT']; // get the user agent value - this should be cleaned to ensure no nefarious input gets executed
	$accept           = (isset($_SERVER['HTTP_ACCEPT']))?$_SERVER['HTTP_ACCEPT']:'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; // get the content accept value - this should be cleaned to ensure no nefarious input gets executed
	switch(true){ // using a switch against the following statements which could return true is more efficient than the previous method of using if statements

		case (preg_match('/ipad/i',$user_agent)); // we find the word ipad in the user agent
			$mobile_browser = $ipad; // mobile browser is either true or false depending on the setting of ipad when calling the function
			$status = 'Apple iPad';
			if(substr($ipad,0,4)=='http'){ // does the value of ipad resemble a url
				$mobileredirect = $ipad; // set the mobile redirect url to the url value stored in the ipad value
			} // ends the if for ipad being a url
			break; // break out and skip the rest if we've had a match on the ipad // this goes before the iphone to catch it else it would return on the iphone instead

		case (preg_match('/ipod/i',$user_agent)||preg_match('/iphone/i',$user_agent)); // we find the words iphone or ipod in the user agent
			$mobile_browser = $iphone; // mobile browser is either true or false depending on the setting of iphone when calling the function
			$status = 'Apple';
			if(substr($iphone,0,4)=='http'){ // does the value of iphone resemble a url
				$mobileredirect = $iphone; // set the mobile redirect url to the url value stored in the iphone value
			} // ends the if for iphone being a url
			break; // break out and skip the rest if we've had a match on the iphone or ipod

		case (preg_match('/android/i',$user_agent));  // we find android in the user agent
			$mobile_browser = $android; // mobile browser is either true or false depending on the setting of android when calling the function
			$status = 'Android';
			if(substr($android,0,4)=='http'){ // does the value of android resemble a url
				$mobileredirect = $android; // set the mobile redirect url to the url value stored in the android value
			} // ends the if for android being a url
			break; // break out and skip the rest if we've had a match on android

		case (preg_match('/opera mini/i',$user_agent)); // we find opera mini in the user agent
			$mobile_browser = $opera; // mobile browser is either true or false depending on the setting of opera when calling the function
			$status = 'Opera';
			if(substr($opera,0,4)=='http'){ // does the value of opera resemble a rul
				$mobileredirect = $opera; // set the mobile redirect url to the url value stored in the opera value
			} // ends the if for opera being a url
			break; // break out and skip the rest if we've had a match on opera

		case (preg_match('/blackberry/i',$user_agent)); // we find blackberry in the user agent
			$mobile_browser = $blackberry; // mobile browser is either true or false depending on the setting of blackberry when calling the function
			$status = 'Blackberry';
			if(substr($blackberry,0,4)=='http'){ // does the value of blackberry resemble a rul
				$mobileredirect = $blackberry; // set the mobile redirect url to the url value stored in the blackberry value
			} // ends the if for blackberry being a url
			break; // break out and skip the rest if we've had a match on blackberry

		case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent)); // we find palm os in the user agent - the i at the end makes it case insensitive
			$mobile_browser = $palm; // mobile browser is either true or false depending on the setting of palm when calling the function
			$status = 'Palm';
			if(substr($palm,0,4)=='http'){ // does the value of palm resemble a rul
				$mobileredirect = $palm; // set the mobile redirect url to the url value stored in the palm value
			} // ends the if for palm being a url
			break; // break out and skip the rest if we've had a match on palm os

		case (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent)); // we find windows mobile in the user agent - the i at the end makes it case insensitive
			$mobile_browser = $windows; // mobile browser is either true or false depending on the setting of windows when calling the function
			$status = 'Windows Smartphone';
			if(substr($windows,0,4)=='http'){ // does the value of windows resemble a rul
				$mobileredirect = $windows; // set the mobile redirect url to the url value stored in the windows value
			} // ends the if for windows being a url
			break; // break out and skip the rest if we've had a match on windows

		case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent)); // check if any of the values listed create a match on the user agent - these are some of the most common terms used in agents to identify them as being mobile devices - the i at the end makes it case insensitive
			$mobile_browser = true; // set mobile browser to true
			$status = 'Mobile matched on piped preg_match';
			break; // break out and skip the rest if we've preg_match on the user agent returned true

		case ((strpos($accept,'text/vnd.wap.wml')>0)||(strpos($accept,'application/vnd.wap.xhtml+xml')>0)); // is the device showing signs of support for text/vnd.wap.wml or application/vnd.wap.xhtml+xml
			$mobile_browser = true; // set mobile browser to true
			$status = 'Mobile matched on content accept header';
			break; // break out and skip the rest if we've had a match on the content accept headers

		case (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])); // is the device giving us a HTTP_X_WAP_PROFILE or HTTP_PROFILE header - only mobile devices would do this
			$mobile_browser = true; // set mobile browser to true
			$status = 'Mobile matched on profile headers being set';
			break; // break out and skip the final step if we've had a return true on the mobile specfic headers

		case (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',))); // check against a list of trimmed user agents to see if we find a match
			$mobile_browser = true; // set mobile browser to true
			$status = 'Mobile matched on in_array';
			break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it

		default;
			$mobile_browser = false; // set mobile browser to false
			$status = 'Desktop / full capability browser';
			break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it

	} // ends the switch

	// tell adaptation services (transcoders and proxies) to not alter the content based on user agent as it's already being managed by this script
	// header('Cache-Control: no-transform'); // http://mobiforge.com/developing/story/setting-http-headers-advise-transcoding-proxies
	// header('Vary: User-Agent, Accept'); // http://mobiforge.com/developing/story/setting-http-headers-advise-transcoding-proxies

	return $mobile_browser; // will return either true or false

} // ends function mobile_device_detect

function usePostgre() {
	$CI = & get_instance();
	return $CI->usePostgre;
}

/**
 * Подключение библиотеки без автоматического создания объекта, например для бибиотек синглтонов
 * По сути обычный require
 */
function loadLibrary($library_name) {
	$CI = & get_instance();
	if ($CI->usePostgre && file_exists(APPPATH . 'libraries/_pgsql/' . $library_name . '.php')) {
		$path = APPPATH . 'libraries/_pgsql/' . $library_name . '.php';
	} else {
		$path = APPPATH . 'libraries/' . $library_name . '.php';
	}
	include_once($path);
}

/**
 * Возвращает элементы первого ассоциативного массива, чьи ключи содержатся во втором массиве
 */
function getArrayElements($arr1, $arr2) {
	return array_intersect_key($arr1, array_flip($arr2));
}

/**
 * Проверка наличия элемента в строковом списке
 */
function inStrList($val, $str_list, $delimiter = ',') {
	return in_array($val, explode($delimiter, $str_list));
}
/**
 * Определение IP-адреса пользователя
 */
function getClientIP($checkProxy = true)
{
	if ($checkProxy && !empty($_SERVER['HTTP_X_REAL_IP'])) {
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	} else if ($checkProxy && !empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else if ($checkProxy && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * 	Определяет вид фильтра по Lpu_id
 */
function getLpuIdFilter($data, $not = false, $UseIsTransitFilter = false, $IsTransitField = null) {
	$result = ($not == true ? "!" : "") . "= :Lpu_id";

	if (
		is_array($data)
		&& array_key_exists('session', $data)
		&& is_array($data['session'])
		&& array_key_exists('linkedLpuIdList', $data['session'])
		&& count($data['session']['linkedLpuIdList']) > 1
	) {
		$result = ($not == true ? "not " : "") . "in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
	}

	return $result;
}

/**
 * Возвращает дату, с которой используется новый вид стомат. ТАП
 */
function getEvnPLStomNewBegDate() {
	$xDate = null;

	$config = &get_config();
	if (!empty($config['EVNPLSTOMNEW_BEGDATE'])) {
		$begDate = $config['EVNPLSTOMNEW_BEGDATE'];
		return strtotime($begDate);
	}

	switch(getRegionNick()) {
		case 'perm':
			$xDate = strtotime('01.11.2015'); // 1 ноября 2015
			break;
		case 'krym':
			$xDate = strtotime('01.05.2016'); // 1 мая 2016
			break;
		default:
			$xDate = strtotime('01.07.2016'); // 1 июля 2016
			break;
	}

	return $xDate;
}

/**
 * Получение хэша для формы
 */
function getFormHash($formName)
{
	$md5 = '';
	$jsfile = getFile($formName);
	if (!empty($jsfile)) {
		$md5 = md5_file($_SERVER['DOCUMENT_ROOT'] . $jsfile['path']);
		if (!empty($jsfile['dependencies'])) {
			foreach ($jsfile['dependencies'] as $one) {
				$md5 .= md5_file($_SERVER['DOCUMENT_ROOT'] . $one);
			}
		}
	}
	return md5($md5); // ещё раз md5 от получившегося, чтобы строка была не длинной.
}

/**
 * Получает и возвращает либо массив файлов, либо один запрошенный файл из конфига согласно правам пользователя
 * @return array
 */
function getFile($name = '') {
	// выбираем установленное меню
	$files = filetoarray(APPPATH.'config/files.php');
	$f = false;
	if (count($files)>0) {
		if (strlen($name)>0) {
			$f = _getFile($name, $files);
		} else {
			// Если возвращаем список файлов
			foreach ($files as $key => $value) {
				$f[$key] = _getFile($key, $files);
			}
		}
	}

	return $f;
}

/**
 * Получает и возвращает либо массив файлов, либо один запрошенный файл из конфига согласно правам пользователя
 * @return array
 */
function getFile6($name = '') {
	// выбираем установленное меню
	$files = filetoarray(APPPATH.'config/files6.php');
	$f = false;
	if (count($files)>0) {
		if (strlen($name)>0) {
			$f = _getFile($name, $files);
		} else {
			// Если возвращаем список файлов
			foreach ($files as $key => $value) {
				$f[$key] = _getFile($key, $files);
			}
		}
	}

	return $f;
}


/**
 * @param $name
 * @param $files
 * @return bool
 */
function _getFile($name, $files){

	$f = false;
	// Если требуется один файл
	if (isset($files[$name])) {
		// Если это одна запись
		if (isset($files[$name]['path'])) {
			$f = $files[$name];
		} elseif (isset($files[$name][$_SESSION['region']['nick']])) {
			$f = $files[$name][$_SESSION['region']['nick']];
		} elseif (isset($files[$name]['default'])) {
			$f = $files[$name]['default'];
		}
	}
	return $f;
}


/**
 * Функция получения массива из файла в переменную
 */
function filetoarray($file) {
	if (file_exists($file)) {
		return require($file);
	}
}

/**
 * Функция получения списка всех файлов в папке
 */
function getFilesList($folder) {
	$return = array();

	if (is_dir($folder)) {
		$files = scandir($folder);
		foreach($files as $file) {
			if ($file != '.' && $file != '..') {
				if (is_dir($folder . '/' . $file)) {
					$return = array_merge($return, getFilesList($folder . '/' . $file));
				} else {
					$return[] = $folder . '/' . $file;
				}
			}
		}
	}

	return $return;
}

/**
 * Генерация GUID
 * @return string
 */
function GUID() {
	if (function_exists('com_create_guid')){
		return trim(com_create_guid(), '{}');
	} else {
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		return ''
			.substr($charid, 0, 8).$hyphen
			.substr($charid, 8, 4).$hyphen
			.substr($charid,12, 4).$hyphen
			.substr($charid,16, 4).$hyphen
			.substr($charid,20,12);
	}
}


/**
 * Получение наименования группы подключения к БД для проверки вхождения документов в реестры
 */
function getRegistryChecksDBConnection() {
	$CI = & get_instance();
	$RegistryChecksDBConnection = $CI->config->item('RegistryChecksDBConnection');
	if ( empty($RegistryChecksDBConnection) || $RegistryChecksDBConnection === false ) {
		$RegistryChecksDBConnection = 'registry';
	}
	return $RegistryChecksDBConnection;
}

/**
 * Получение простого стека вызовов
 */
function getSimpleBackTrace() {
	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	$backtrace = array_slice($backtrace, 1);
	$return = array();
	foreach($backtrace as $one) {
		$class = '';
		if (isset($one['class'])) {
			$class = $one['class'];
		}
		$type = '';
		if (isset($one['type'])) {
			$type = $one['type'];
		}
		$return[] = $one['file'] . ':' . $one['line'] . ' (' . $class . $type . $one['function'] . ')';
	}

	return $return;
}

/**
 * @param string $operation
 * @param string $object
 * @param int|string $key
 */
function collectEditedData($operation, $object, $key, $key_name = null) {
	$instance = &get_instance();
	$instance->editedDataCollection[] = array(
		'operation' => $operation,
		'object' => $object,
		'key_name' => $key_name,
		'key' => $key,
	);
}

/**
 * @param array|mixed $response
 * @param string $type
 * @return array
 */
function processRestResponse($response, $type = 'list') {
	$output = array();
	if (!is_array($response)) {
		return $output;
	}
	if (!empty($response['error_msg'])) {
		$output = array(
			'success' => false,
			'Error_Code' => $response['error_code'],
			'Error_Msg' => $response['error_msg']
		);
	} else if (!empty($response['error_code'])) {
		$output = array(
			'success' => false,
			'Error_Msg' => "Ошибка с кодом {$response['error_code']}"
		);
	} else if (!empty($response['data'])) {
		if ($type == 'single') {
			if (isset($response['data'][0])) {
				$output = $response['data'][0];
			} else {
				$output = $response['data'];
			}
			if (!isset($output['success'])) {
				$output['success'] = (
					empty($output['Error_Code']) &&
					empty($output['Error_Msg'])
				);
			}
		} else {
			$output = $response['data'];
		}
	}
	return $output;
}

/**
 * @param array $arr
 * @return array
 */
function keystolower($arr) {
	$result = array();
	foreach($arr as $key => $item) {
		$result[strtolower($key)] = $item;
	}
	return $result;
}

/**
 * @param array $arr
 *
 * Функция для postgre
 * Переводит поле 'leaf': string -> int
*/
function leafToInt(&$arr) {
	foreach ($arr as $key => $value) {
		if (isset($value['leaf'])) {
			$arr[$key]['leaf'] = intval($value['leaf']);
		}
	}
}

/**
 * Получение версии промеда из ver.txt
 */
function getPromedVersion() {
	$result = [
		'version' => 'prmd.1.0.0',
		'commit' => 'не определена',
		'date' => 'дата неизвестна'
	];

	if (file_exists('ver.txt') ) {
		$fp = fopen('ver.txt', 'r');
		while( ! feof($fp))
		{
			$s = fgets($fp);
			if (strpos($s, 'rel_ver:')===0) {
				$result['version'] = trim(substr($s, 8));
			}
			if (strpos($s, 'commit:')===0) {
				$result['commit'] = trim(substr($s, 7, 7));
			}
			if (strpos($s, 'date:')===0) {
				$result['date'] = date('d.m.Y H:i', strtotime(trim(substr($s, 5))));
			}
		}
		fclose($fp);
		
		return $result;
	}
}
