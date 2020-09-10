<?php

/**
 * Reg_helper - хелпер c функциями для электронной регистратуры
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Petukhov Ivan (megatherion@list.ru)
 * @version      October 2011
 */
defined('BASEPATH') or die('No direct script access allowed');

/**
 * Форматирование HTML строки
 */
function Fmt( $str ) {
	return htmlspecialchars(addslashes(trim($str)), ENT_QUOTES, 'utf-8');
}

/**
 * Проверка, являются ли подмассивы пустыми
 */
function EmptyArrays( $arDays ) {
	foreach ( $arDays as $arTimes )
		if ( count($arTimes) > 0 )
			return false;
	return true;
}

/**
 * Конвертирует PHP представление времени в идентификатор дня
 */
function TimeToDay( $nTime ) {
	$SECONDS_PER_DAY = 24 * 60 * 60;
	$arDate = getdate($nTime);
	$nGmtTime = gmmktime($arDate['hours'], $arDate['minutes'], $arDate['seconds'], $arDate['mon'], $arDate['mday'], $arDate['year']);
	$nTime += ( $nGmtTime - $nTime );
	return floor($nTime / $SECONDS_PER_DAY) + ( 36526 - 10956 ) - 2;
}

/**
 * Конвертирует день и минуты во время
 */
function DayMinuteToTime( $nDay, $nMinute ) {
	$SECONDS_PER_DAY = 24 * 60 * 60;
	$nUnixDay = $nDay - ( 36526 - 10956 ) + 2;
	$nUnixTime = $nUnixDay * $SECONDS_PER_DAY + $nMinute * 60;
	$arDate = getdate($nUnixTime);
	$nGmtTime = gmmktime($arDate['hours'], $arDate['minutes'], $arDate['seconds'], $arDate['mon'], $arDate['mday'], $arDate['year']);
	$nUnixTime -= ( $nGmtTime - $nUnixTime );
	return $nUnixTime;
}

/**
 * Конвертации строки времени в количество минут после полуночи
 */
function StringToTime( $s ) {
	if ( preg_match("/^([0-9]{1,2})\:([0-9]{2})$/i", $s, $Args) )
		return $Args[1] * 60 + $Args[2];
	return false;
}

/**
 * Является ли пользователь администратором центра записи
 */
function IsCZAdmin() {
	global $_USER;
	return ( isset($_USER) && $_USER->hasOneOfGroup(array("CallCenterAdmin")) ) || isSuperAdmin();
}

/**
 * Является ли пользователь оператором центра записи
 */
function IsCZOper() {
	global $_USER;
	return ( isset($_USER) && $_USER->hasOneOfGroup(array("OperatorCallCenter")) ) || isSuperAdmin();
}

/**
 * Проверка является ли текущий пользователь пользователем ЦЗ
 */
function IsCZUser() {
	global $_USER;
	return ( isset($_USER) && $_USER->hasOneOfGroup(array("CallCenterAdmin", "OperatorCallCenter")) ) || isSuperAdmin();
}

/**
 * Является ли текущий пользователь пользователь администратором ЛПУ
 * Если задана $Org_id то именно этого ЛПУ, иначе любого ЛПУ
 * Если передается NULL то считаем что это не пользователь ЛПУ
 */
function IsLpuRegAdmin( $Org_id = NULL ) {
	global $_USER;
	if ( isset($_USER) && $_USER->hasOneOfGroup(array("OrgAdmin", "RegAdmin", "LpuAdmin")) ) {
		return $_USER->belongsToOrg($Org_id);
	} else {
		return false;
	}
}

/**
 * Является ли текущий пользователь пользователем ЛПУ
 * Если задана $Org_id то именно этого ЛПУ, иначе любого ЛПУ
 * Если передается NULL то считаем что это не пользователь ЛПУ
 */
function IsLpuRegUser( $Org_id = NULL ) {
	global $_USER;
	if ( isset($_USER) && $_USER->hasOneOfGroup(array("OrgUser", "LpuUser", "LpuAdmin", 'LpuPowerUser', "OrgAdmin", "RegAdmin")) ) {
		return $_USER->belongsToOrg($Org_id);
	} else {
		return false;
	}
}

/**
 * Является ли текущий пользователь пользователем ЛПУ
 * Если задана $Lpu_id то именно этого ЛПУ, иначе любого ЛПУ
 * Если передается NULL то считаем что это не пользователь ЛПУ
 */
function IsLpuRegUserByLpu( $Lpu_id = NULL ) {
	global $_USER;

	$CI = & get_instance();
	$CI->load->model('Org_model', 'Org_model');
	$Org_id = $CI->Org_model->getOrgOnLpu(array('Lpu_id' => $Lpu_id));

	if ( isset($_USER) && $_USER->hasOneOfGroup(array("OrgUser", "LpuUser", "LpuAdmin", 'LpuPowerUser', "OrgAdmin", "RegAdmin")) ) {
		return $_USER->belongsToOrg($Org_id);
	} else {
		return false;
	}
}

/**
 * Является ли пользователь пользователем другой ЛПУ
 */
function IsOtherLpuRegUser( $Org_id = NULL ) {
	global $_USER;

	if ( isset($_USER) && $_USER->hasOneOfGroup(array("OrgUser", "LpuUser", "LpuAdmin", 'LpuPowerUser', "OrgAdmin", "RegAdmin")) ) {
		if ( isset($Org_id) ) {
			return !$_USER->belongsToOrg($Org_id);
		} else {
			return false;
		}
	}
	return false;
}

/**
 * По идентификатору пользователя, определяет что это пользователь интернет или нет
 */
function IsInetUser( $pmUser_id ) {
	return ($pmUser_id > 1000000 && $pmUser_id < 5000000);
}

/**
 * По идентификатору пользователя, определяет что запись из ФЭР или нет
 * На всех регионах это пользователь с ИД = 999900
 */
function IsFerUser( $pmUser_id ) {
	return ($pmUser_id == 999900);
}


/**
 * Являеться ли текущий пользователь врачем МО
 * @return bool
 */
function getDocArms() {
	return in_array($_SESSION['CurArmType'], ['common', 'phys', 'stom', 'stom6', 'stac', 'stacpriem', 'vk', 'mse', 'smpvr', 'polkallo', 'regPAY', 'reanimation', 'headdoct', 'nmpgranddoc', 'smpheaddoctor', 'polka']);
}

/**
 * По идентификатору человека, определяет что это человек из ФЭР или нет
 * Если запись приходит из ФЭР, то на бирку всегда записывается один и тот же условный пациент с ФИО = "Запись из ФЭР"
 */
function IsFerPerson( $Person_id ) {
	switch (getRegionNick()) {
		case 'astra': $Person_ferid = '817089'; break;
		case 'ufa': $Person_ferid = '2602784'; break;
		case 'kareliya': $Person_ferid = '995399'; break;
		case 'perm': $Person_ferid = '3615838'; break;
		case 'pskov': $Person_ferid = '262046'; break;
		case 'ekb': $Person_ferid = '71201'; break;
		case 'khak': $Person_ferid = '2535596'; break;
		default: $Person_ferid = null; break;
	}
	return ($Person_ferid == $Person_id);
}

/**
 * Разбирает диапазон на массив номеров домов
 */
function getHouseArray( $arr ) {
	$arr = trim(mb_strtoupper($arr));
	//print $arr.": ";
	if ( preg_match("/^([Ч|Н])\((\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)\)$/iu", $arr, $matches) ) {
		// Четный или нечетный 
		$matches[count($matches)] = 1;
		return $matches;
	} elseif ( preg_match("/^([\s]?)(\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)$/iu", $arr, $matches) ) {
		// Обычный диапазон
		$matches[count($matches)] = 2;
		return $matches;
	} elseif ( preg_match("/^(\d+[а-яА-Я]?[\/]?\d{0,3}[а-яА-Я]?(\s[к]\d{0,3})?-?)$/iu", $arr, $matches) ) {
		//print $arr." ";
		if ( preg_match("/^(\d+)/i", $matches[1], $ms) ) {
			$matches[count($matches)] = $ms[1];
		} else {
			$matches[count($matches)] = '';
		}
		$matches[count($matches)] = 3;
		return $matches;
	}
	return array();
}

/**
 * Возвращает признак вхождения в диапазон домов
 */
function HouseExist( $h_s, $houses ) {
	// Сначала разбираем h_arr и определяем: 
	// 1. Обычный диапазон 
	// 2. Четный диапазон
	// 3. Нечетный диапазон
	// 4. Перечисление 
	// Разбиваем на номера домов и диапазоны с которым будем проверять
	$hs_arr = preg_split('[,|;]', $houses, -1, PREG_SPLIT_NO_EMPTY);
	// Разбиваем на номера домов и диапазоны, которые будем проверять
	$h_arr = preg_split('[,|;]', $h_s, -1, PREG_SPLIT_NO_EMPTY);
	$i = 0;
	foreach ( $h_arr as $row_arr ) {
		//print $row_arr."   | ";
		$ch = getHouseArray($row_arr); // сохраняемый 
		//print_r($ch);
		if ( count($ch) > 0 ) {
			//print $i."-";
			foreach ( $hs_arr as $rs_arr ) {
				$chn = getHouseArray($rs_arr); // выбранный
				if ( count($chn) > 0 ) {
					// Проверка на правильность указания диапазона
					if ( (($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($ch[2] > $ch[4]) ) {
						return false;
					}

					if ( (($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == 'Ч') && ($chn[1] == 'Ч')) || // сверяем четный с четным
							(($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == 'Н') && ($chn[1] == 'Н')) || // сверяем нечетный с нечетным
							((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 2)) ) {  // или любой диапазон с обычным
						if ( ($ch[2] <= $chn[4]) && ($ch[4] >= $chn[2]) ) {
							return true; // Перечесение (С) и (В) диапазонов
						}
					}
					if ( (($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 3) ) { // Любой диапазон с домом 
						if ( (($ch[1] == 'Ч') && ($chn[2] % 2 == 0)) || // если четный
								(($ch[1] == 'Н') && ($chn[2] % 2 <> 0)) || // нечетный 
								($ch[count($ch) - 1] == 2) ) { // обычный
							if ( ($ch[2] <= $chn[2]) && ($ch[4] >= $chn[2]) ) {
								return true; // Перечесение диапазона с конкретным домом
							}
						}
					}
					if ( (($chn[count($chn) - 1] == 1) || ($chn[count($chn) - 1] == 2)) && ($ch[count($ch) - 1] == 3) ) { // Любой дом с диапазоном
						if ( (($chn[1] == 'Ч') && ($ch[2] % 2 == 0)) || // если четный
								(($chn[1] == 'Н') && ($ch[2] % 2 <> 0)) || // нечетный 
								($chn[count($chn) - 1] == 2) ) { // обычный
							if ( ($chn[2] <= $ch[2]) && ($chn[4] >= $ch[2]) ) {
								return true; // Перечесение дома с каким-либо диапазоном
							}
						}
					}
					if ( ($ch[count($ch) - 1] == 3) && ($chn[count($chn) - 1] == 3) ) { // Дом с домом
						if ( mb_strtolower($ch[0]) == mb_strtolower($chn[0]) ) {
							return true; // Перечесение дома с домом
						}
					}
				}
			}
		} else {
			return false; // Перечесение дома с домом
		}
	}
	return "";
}

/**
 * Проверка входил ли переданный номер дома в диапазон домов, заданный вторым параметром
 */
function HouseMatchRange( $sHouse, $sRange ) {
	if ( $sRange == "" )
		return true;
	return HouseExist($sHouse, $sRange);
}

/**
 * Возвращает количество дней для записи в другую МО
 */
function GetPortalDayCount() {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$res = $CI->rmodel->GetPortalDayCount();
	return $res;
}

/**
 * Возвращает количество дней для записи для поликлиники
 */
function GetPolDayCount($lpu_id=null, $MedStaffFact_id=null) {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$res = $CI->rmodel->getPolDayCount($lpu_id, $MedStaffFact_id);
	return $res;
}

/**
 * Возвращает количество дней для записи для служб
 */
function GetMedServiceDayCount($lpu_id = null, $MedService_id = null) {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$res = $CI->rmodel->getMedServiceDayCount($lpu_id, $MedService_id);
	return $res;
}

/**
 * Возвращает количество дней для записи для стационаров
 */
function GetStacDayCount($lpu_id=null, $LpuSection_id=null) {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$res = $CI->rmodel->getStacDayCount($lpu_id, $LpuSection_id);
	return $res;
}

/**
 * Получение времени открытия нового дня
 */
function getShowNewDayTime() {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$res = $CI->rmodel->getShowNewDayTime();
	return $res;
}

/**
 * Получение времени запрета записи на завтра
 */
function getCloseNextDayRecordTime() {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$res = $CI->rmodel->getCloseNextDayRecordTime();
	return $res;
}

/**
 * Загружаем праздники от переданного дня на месяц вперед
 */
function IsWorkDay($date) {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$holidays = $CI->rmodel->getHolidays($date);
	$date->setTime(0, 0, 0);
	return !(in_array($date, $holidays) || $date->format('N') == 6 || $date->format('N') == 7 );
}

/**
 * Загружаем праздники от переданного дня на месяц вперед
 */
function NextWorkDay($date) {
	$CI = & get_instance();
	$CI->load->model('Reg_model', 'rmodel');
	$holidays = $CI->rmodel->getHolidays($date);
	$date->setTime(0, 0, 0);
	$date->modify('+1 day');
	while( in_array($date, $holidays) || $date->format('N') == 6 || $date->format('N') == 7 ) {
		$date->modify('+1 day');
	}
	return $date;
}

/**
 * Возможна ли запись на эту бирку
 */ 
function canRecord($tt_data, $user_data) {
	global $_USER;

	loadLibrary('TTimetableTypes');
	$TimetableType = TTimetableTypes::instance()->getTimetableType($tt_data['TimetableType_id']);

	if ( IsCZAdmin() ) { // Админам ЦЗ можно всё
		return true;
	}
	
	if (!isset($tt_data['MedStaffFact_IsDirRec'])) {
		$tt_data['MedStaffFact_IsDirRec'] = 2;
	}

	if ( 'ufa' == getRegionNick() 
		&& IsCZOper()
		&& (!empty($user_data['session']['CurArmType'] ) && $user_data['session']['CurArmType'] == 'callcenter')
	) {
		// Период возможной записи оператором call-центра определяется настройками указанными в АРМ Администратора ЦОД/Система/Параметры системы/Электронная регистратура
		$today = TimeToDay(time());
		if (isset($tt_data['TimetableGraf_Day'])) {
			$rec_day = $tt_data['TimetableGraf_Day'];
			$limit_day = $today + GetPolDayCount() - 1;
		} else if (isset($tt_data['TimetableMedService_Day'])) {
			$rec_day = $tt_data['TimetableMedService_Day'];
			$maxDays = GetMedServiceDayCount();
			if (!empty($maxDays)) {
				$limit_day = $today + GetMedServiceDayCount() - 1;
			} else {
				$limit_day = $today + 365; // не ограничено
			}
		} else if (isset($tt_data['TimetableMedServiceOrg_Day'])) {
			$rec_day = $tt_data['TimetableMedServiceOrg_Day'];
			$maxDays = GetMedServiceDayCount();
			if (!empty($maxDays)) {
				$limit_day = $today + GetMedServiceDayCount() - 1;
			} else {
				$limit_day = $today + 365; // не ограничено
			}
		} else if (isset($tt_data['TimetableResource_Day'])) {
			$rec_day = $tt_data['TimetableResource_Day'];
			$maxDays = GetMedServiceDayCount();
			if (!empty($maxDays)) {
				$limit_day = $today + GetMedServiceDayCount() - 1;
			} else {
				$limit_day = $today + 365; // не ограничено
			}
		} else if (isset($tt_data['TimetableStac_Day'])) {
			$rec_day = $tt_data['TimetableStac_Day'];
			$limit_day = $today + GetStacDayCount() - 1;
		} else {
			$rec_day = null;
			$limit_day = null;
		}
		//$debug = array($tt_data, $today, $limit_day, $rec_day, $limit_day < $rec_day);
		//throw new Exception(var_export($debug, true));
		if (!$rec_day || !$limit_day || $limit_day == $today || $limit_day < $rec_day) {
			return false;
		}
	}

	if ( $TimetableType->inSources(1) ) { // запись врача самому к себе или запись орагнизации на защиту в МЗ
		if ( (isset($_USER) && !empty($_USER->medpersonal_id) && !empty($tt_data['MedPersonal_id']) && ($_USER->medpersonal_id == $tt_data['MedPersonal_id']))
		|| (isset($tt_data['object'])&&!empty($tt_data['object']) && $tt_data['object'] === 'TimetableMedServiceOrg')
		) {
			return true;
		}
	}
	
	if ( $TimetableType->inSources(2) ) { // пользователи своего подразделения
		if ( 
			isset($_USER) && 
			isset($tt_data['LpuUnit_id']) && 
			isset($user_data['session']['CurLpuUnit_id']) && 
			( $tt_data['LpuUnit_id'] == $user_data['session']['CurLpuUnit_id'] )
		) {
			return true;
		}
	}
	
	if ( $TimetableType->inSources(3) ) { // врачи своей МО
		if ( 
			isset($_USER) && 
			!empty($_USER->medpersonal_id) && 
			IsLpuRegUser($tt_data['Org_id']) && 
			(!empty($user_data['session']['CurArmType'] ) && $user_data['session']['CurArmType'] != 'regpol' && $user_data['session']['CurArmType'] != 'callcenter')
		) {
			return true;
		}
	}
	
	if ( $TimetableType->inSources(4) ) { // регистраторы своей МО
		if ( isset($_USER) && IsLpuRegUser($tt_data['Org_id']) && (!empty($user_data['session']['CurArmType'] ) && in_array($user_data['session']['CurArmType'], ['regpol', 'regpol6'])) ) {
			return true;
		}
	}
	
	if ( $TimetableType->inSources(5)) { // все пользователи своей МО
		if (
			isset($_USER) && 
			IsLpuRegUser($tt_data['Org_id']) && 
			($tt_data['MedStaffFact_IsDirRec'] != 1 || getRegionNick() != 'kareliya') && 
			(!empty($user_data['session']['CurArmType'] ) && $user_data['session']['CurArmType'] != 'callcenter')
		) {
			return true;
		}
	}
	
	if ( $TimetableType->inSources(6) ) { // пользователи чужих МО через направления
		if (
			(isset($_USER) && IsOtherLpuRegUser($tt_data['Org_id']) && $tt_data['MedStaffFact_IsDirRec'] != 1)
			|| havingGroup('DrivingCommissionOphth')
			|| havingGroup('DrivingCommissionPsych')
			|| havingGroup('DrivingCommissionPsychNark')
			|| havingGroup('DrivingCommissionTherap')
		) {
			return true;
		}
	}
	
	if ( $TimetableType->inSources(7) ) { // пользователи центра записи
		if ( isset($_USER) && IsCZOper() ) {
			return true;
		}
	}
	
	if( !empty($user_data['session']['CurArmType'] ) && $user_data['session']['CurArmType'] == 'smo' && in_array($tt_data['TimetableType_id'], array(1,3,5)) )
		return true;
	return false;
}

function day_sql($timestamp) {
	$date = getdate($timestamp);
	$gmt = gmmktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], $date['mday'], $date['year']);
	$timestamp += ( $gmt - $timestamp );
	return (int) floor($timestamp / 86400) + ( 36526 - 10956 ) - 2;
}