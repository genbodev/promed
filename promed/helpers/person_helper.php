<?php
/**
*	Person_helper - хелпер с функциями для дополнительной обработки данных по человеку
*
*	PromedWeb - The New Generation of Medical Statistic Software
*	http://swan.perm.ru/PromedWeb
*
*
*	@package      Common
*	@access       public
*	@copyright    Copyright (c) 2013 Swan Ltd.
*	@author       Stanislav Bykov (savage@swan.perm.ru)
*	@version      06.06.2013
*/

/**
 *	Проверка корректности единого номера полиса
 *
 *	@access	public
 *	@param	string $enp ЕНП
 *	@param	int $personBD дата рождения человека (формат DD.MM.YYYY или YYYY-MM-DD)
 *	@param	int $sexCode пол человека (0 - женский; 1 - мужской)
 *	@return	string
 */
function swCheckENPFormat($enp, $personBD, $sexCode) {
	if ( empty($enp) || !preg_match("/^\d{16}$/", $enp) ) {
		return 'Неверный формат ЕНП';
	}

	// Проверяем контрольное число
	$number = substr($enp, 0, 15);

	$chet = '';
	$nechet = '';
	$temp = 0;
	$zn = 0;

	for ( $i = strlen($number); $i > 0; $i-- ) {
		$temp = intval(substr($number, $i - 1, 1));

		if ( ($i % 2) == 0 ) {
			$chet .= $temp;
		}
		else {
			$nechet .= $temp;
		}
	}

	$temp = intval($chet) . (intval($nechet) * 2);

	for ( $i = 0; $i < strlen($temp); $i++ ) {
		$zn += intval(substr($temp, $i, 1));
	}

	$zn = (string)$zn;

	if ( ((10 - intval(substr($zn, strlen($zn) - 1, 1))) % 10) != intval(substr($enp, 15, 1)) ) {
		return 'Неверное контрольное число';
	}

	// Проверяем ЕНП на соответствие введенным ДР и полу
	$number = '';

	if ( preg_match("/^\d{2}\.\d{2}\.\d{4}$/", $personBD) ) {
		// DD.MM.YYYY
		$day   = intval(substr($personBD, 0, 2));
		$month = intval(substr($personBD, 3, 2));
		$year  = intval(substr($personBD, 6, 4));
	}
	else if ( preg_match("/^\d{4}\-\d{2}\-\d{2}$/", $personBD) ) {
		// YYYY-MM-DD
		$day   = intval(substr($personBD, 8, 2));
		$month = intval(substr($personBD, 5, 2));
		$year  = intval(substr($personBD, 0, 4));
	}
	else {
		return 'Неверный формат даты рождения';
	}

	if ( $sexCode == 1 ) {
		$day += 50;
	}

	if ( $year <= 1950 ) {
		$month += 20;
	}
	else if ( $year >= 1951 && $year <= 2000 ) {
		$month += 40;
	}

	$day = sprintf('%02d', $day);
	$month = sprintf('%02d', $month);

	$number .= (9 - intval(substr($month, 0, 1)));
	$number .= (9 - intval(substr($month, 1, 1)));

	$number .= (9 - intval(substr($year, 3, 1)));
	$number .= (9 - intval(substr($year, 2, 1)));
	$number .= (9 - intval(substr($year, 1, 1)));
	$number .= (9 - intval(substr($year, 0, 1)));

	$number .= (9 - intval(substr($day, 0, 1)));
	$number .= (9 - intval(substr($day, 1, 1)));

	if ( $number != substr($enp, 2, 8) ) {
		return 'ЕНП не соответствует дате рождения и полу';
	}

	return '';
}

/**
 * Проверка корректности СНИЛСа
 *
 * @param string $Person_Snils
 * @return bool
 */
function checkPersonSnils($Person_Snils, $allowEmpty = true) {
	$Person_Snils = str_replace(array('-',' '), '', $Person_Snils);

	if (mb_strlen($Person_Snils) == 0) {
		return (bool) $allowEmpty; // false или true
	}

	if (!preg_match('/^\d{11}$/', $Person_Snils)) {
		return false;
	}

	$psk = mb_substr($Person_Snils, 9, 2);
	$ps = mb_substr($Person_Snils, 0, 9);
	$arr = array();
	$z = 9;
	$sum = 0;

	for ($i = 0; $i < 9; $i++) {
		$arr[$i] = mb_substr($ps, $i, 1);
		$sum += $arr[$i]*$z;
		$z--;
	}

	while ($sum > 101) {
		$sum = $sum % 101;
	}

	if ( (($sum < 100) && ($sum != $psk)) || ((($sum == 100) || ($sum == 101)) && ($psk != '00')) ) {
		return false;
	}

	return true;
}
?>
