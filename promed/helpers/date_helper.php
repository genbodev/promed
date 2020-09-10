<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Date_helper - хелпер с функциями для обработки дат
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan (megatherion@list.ru)
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      ?
*/

/**
 * Русские названия месяцев
 */
$arMonthOf = array(
	1 => "января",
	2 => "февраля",
	3 => "марта",
	4 => "апреля",
	5 => "мая",
	6 => "июня",
	7 => "июля",
	8 => "августа",
	9 => "сентября",
	10 => "октября",
	11 => "ноября",
	12 => "декабря",
);


/**
*  Сравнивает 2 даты в формате 'dd.mm.yyyy' / 'yyyy.mm.dd', возвращает массив из двух элементов
*  Варианты:
*    1. $result[0] = 1 - дата 1 меньше даты 2
*       $result[1] = 'date1 < date2'
*    2. $result[0] = 0 - даты равны
*       $result[1] = 'date1 = date2'
*    3. $result[0] = -1 - дата 1 больше даты 2
*       $result[1] = 'date1 > date2'
*    4. $result[0] = 100 - неверный формат параметров
*       $result[1] = '<текст ошибки>'
*/
function swCompareDates($date1, $date2)
{
	$result = array(0 => 100, 1 => '');

	$dd1 = 0;
	$dd2 = 0;
	$dm1 = 0;
	$dm2 = 0;
	$dy1 = 0;
	$dy2 = 0;

	if ( (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date1)) && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date1)) ) {
		$result[1] = 'Неверный формат первой даты';
	}
	else if ( (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date2)) && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date2)) ) {
		$result[1] = 'Неверный формат второй даты';
	}
	else {
		if ( preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date1) ) {
			list($dd1, $dm1, $dy1) = explode('.', $date1);
		}
		else {
			list($dy1, $dm1, $dd1) = explode('-', $date1);
		}

		if ( preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date2) ) {
			list($dd2, $dm2, $dy2) = explode('.', $date2);
		}
		else {
			list($dy2, $dm2, $dd2) = explode('-', $date2);
		}

		if ( intval($dy1) < intval($dy2) ) {
			$result[0] = 1;
			$result[1] = 'date1 < date2';
		}
		else if ( intval($dy1) == intval($dy2) ) {
			if ( intval($dm1) < intval($dm2) ) {
				$result[0] = 1;
				$result[1] = 'date1 < date2';
			}
			else if ( intval($dm1) == intval($dm2) ) {
				if ( intval($dd1) < intval($dd2) ) {
					$result[0] = 1;
					$result[1] = 'date1 < date2';
				}
				else if ( intval($dd1) == intval($dd2) ) {
					$result[0] = 0;
					$result[1] = 'date1 = date2';
				}
				else {
					$result[0] = -1;
					$result[1] = 'date1 > date2';
				}
			}
			else {
				$result[0] = -1;
				$result[1] = 'date1 > date2';
			}
		}
		else {
			$result[0] = -1;
			$result[1] = 'date1 > date2';
		}
	}

	return $result;
}


/**
* Преобразует текстовое значение даты из одного формата в другой (y-m-d <=> d.m.y)
* отсекая при этом время
*/
function ConvertDateEx($date, $split_in=".", $split_out="-")
{
	if ( empty($date) ) {
		return $date;
	}

	$sdate = explode(" ", $date);
	$date = $sdate[0];
	$ndate = explode($split_in, $date);
	return $ndate[2] . $split_out . $ndate[1] . $split_out . $ndate[0];
}

/**
*  Принимает значение даты в формате 'dd.mm.yyyy'
*  В случае соответствия шаблону, возвращает дату в новом формате
*/
function ConvertDateFormat($date, $format = 'Y-m-d')
{
	if (empty($date)) {
		return null;
	}

	try {
		if ($date instanceof DateTime) {
			$dt = $date;
		} else {
			$dt = new DateTime($date);
		}
		$response = $dt->format($format);
	}
	catch ( Exception $e ) {
		$response = false;
	}

	return $response;
}

/**
 * Конвертит даты в Y-m-d
 */
function ConvertDatesFromObjectToYmd(&$var){
	if ($var instanceOf DateTime){
		$var = $var->format('Y-m-d H:i:s');
	}
}

/**
*  Принимает значение даты в формате 'dd.mm.yyyy hh:mm'
*  В случае соответствия шаблону, возвращает дату в новом формате
*/
function ConvertDateTimeFormat($date, $format = 'y-m-d h:i:s')
{
	if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4}) (\d{2})\:(\d{2})\:(\d{2})$/', $date, $matches))
	{
		$date = strtolower($format);

		$date = str_replace('d', $matches[1], $date);
		$date = str_replace('m', $matches[2], $date);
		$date = str_replace('y', $matches[3], $date);
		$date = str_replace('h', $matches[4], $date);
		$date = str_replace('i', $matches[5], $date);
		$date = str_replace('s', $matches[6], $date);

		return $date;
	}
	else
	{
		return false;
	}
}


/**
*  Проверяет соответствие даты форматам 'dd.mm.yyyy', 'yyyy-mm-dd' и ее корректность
*  Возвращает код ошибки:
*      0 - ошибок нет
*      1 - неверный формат даты
*      2 - некорректная дата
*/
function CheckDateFormat($date) {
	$result = 0;

	if ( preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date) ) {
		$format = 'd.m.Y';
	}
	else if ( preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date) ) {
		$format = 'Y-m-d';
	}
	else {
		$result = 1;
	}

	if ( !empty($format) ) {
		try {
			$dt = new DateTime($date);

			if ( $date != $dt->format($format) ) {
				$result = 2;
			}
		}
		catch ( Exception $e ) {
			$result = 2;
		}
	}

	return $result;
}


/**
*  Проверяет соответствие времени формату 'H:i'
*  Возвращает код ошибки:
*      0 - ошибок нет
*      1 - неверный формат времени
*/
function CheckTimeFormat($time) {
	$result = 0;

	if ( !preg_match('/^(\d{2}):(\d{2})$/', $time, $matches) ) {
		$result = 1;
	}
	else {
		if ( intval($matches[1]) < 0 || intval($matches[1]) > 23 ) {
			$result = 1;
		}
		else if ( intval($matches[2]) < 0 || intval($matches[2]) > 59 ) {
			$result = 1;
		}
	}

	return $result;
}


/**
*  Проверяет соответствие даты cо временем формату 'dd.mm.yyyy hh:ii' и ее корректность
*  Возвращает код ошибки:
*      0 - ошибок нет
*      1 - неверный формат даты
*      2 - некорректная дата
*/
function CheckDateTimeFormat($date) {
	$result = 0;

	if ( !preg_match('/^(\d{2})\.(\d{2})\.(\d{4}) (\d{2}):(\d{2})$/', $date, $matches) ) {
		$result = 1;
	}
	else {
		/*if ( $date != date('d.m.Y H:i', mktime(intval($matches[4]), intval($matches[5]), 0, intval($matches[2]), intval($matches[1]), intval($matches[3]))) ) {
			$result = 2;
		}*/
		try {
			$dt = new DateTime($date);

			if ( $date != $dt->format('d.m.Y H:i') ) {
				$result = 2;
			}
		}
		catch ( Exception $e ) {
			$result = 2;
		}
	}

	return $result;
}

/**
*  Принимает значение поля ввода диапазона дат в формате 'dd.mm.yyyy - dd.mm.yyyy'
*  В случае соответствия шаблону, возвращает массив из двух дат в указанном формате
*/
function ExplodeTwinDate($s, $format = 'y-m-d') {
	if (mb_strpos($s, '—') !== false) {
		// может быть другой разделитель
		$dates = explode(' — ', $s);
	} else {
		$dates = explode(' - ', $s);
	}

	if ((is_array($dates))) {
		if (count($dates) == 2) {
			if ((preg_match('/^\d|_{2}\.\d|_{2}\.\d|_{4}$/', $dates[0])) || (preg_match('/^\d|_{2}\.\d|_{2}\.\d|_{4}$/', $dates[1]))) {
				if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dates[0])) {
					$dates[0] = ConvertDateFormat($dates[0]);
				} else {
					$dates[0] = NULL;
				}

				if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dates[1])) {
					$dates[1] = ConvertDateFormat($dates[1]);
				} else {
					$dates[1] = NULL;
				}

				if ((isset($dates[0])) || (isset($dates[1]))) {
					return $dates;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else if (count($dates) == 1) {
			// может быть передана только одна дата
			if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dates[0])) {
				$dates[0] = ConvertDateFormat($dates[0]);
				$dates[1] = ConvertDateFormat($dates[0]);
			} else {
				$dates[0] = NULL;
				$dates[1] = NULL;
			}

			if ((isset($dates[0])) || (isset($dates[1]))) {
				return $dates;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	else {
		return false;
	}
}
	
/**
 * Возвращает количество полных лет от переданной даты до текущей
 */
function getCurrentAge($startDate, $endDate = null) {
	$bdt = new Datetime($startDate);
	$cdt = new Datetime(!empty($endDate) ? $endDate : date('Y-m-d'));

	$result = $cdt->diff($bdt)->y;

	if ( $bdt->format('dm') == $cdt->format('dm') ) {
		$result++;
	}

	return $result;
}
?>