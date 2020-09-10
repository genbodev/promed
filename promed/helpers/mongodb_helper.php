<?php
/**
 * MongoDB_helper - хелпер работы с запросами MongoDB
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Марков Андрей
 * @version      Май 2012
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Преобразует тип значения в числовой по возможности
 * @param $item
 * @param $index
 */
function convertFieldToInt(&$item, $index)
{
	//print  $index.' : '.toAnsi($item).' - '.gettype($item)."<br/> ".(strtolower(substr($index, -5, 5)))." ".(strtolower(substr($index, -3, 3))).' - '.(ctype_digit($item)).' - '.(gettype($item) == 'string')."<br/> ";
	// Если тип = строка и содержит только цифры и это код или id
	if ((gettype($item) == 'string') && (ctype_digit($item)) && ($item === (string)((int)$item)) && ((strtolower(substr($index, -5, 5))=='_code') || (strtolower(substr($index, -3, 3))=='_id') || (strtolower(substr($index, -3, 3))=='_pid') || (is_int($index)))) {
		// Допусловия
		if (!in_array(strtolower($index),array('usluga_code'))) {
			$item = (int)$item;
		}
	}
}

/**
 * Функция преобразует параметры Where в массив MongoDB
 */
function mongo_where($data, $method, $field, $value) {
	if (!isset($data)) {
		$data = array();
	}
	$field = strtolower($field);

	$value = (ctype_digit($value))?(int)$value:$value;
	// Поскольку данные преобразуются в массив по типам, никаких кавычек не надо
	if (strpos($value, "'")!==false) {
		$value = str_replace("'","",$value);
	} else {
		//кавычки не встретились - может там какое-нибудь ключевое слово, например NULL?
		if (strtoupper($value) === 'NULL') {
			$value = null;
		}
	}
	// если поле является датой, то в дату его и преобразуем
	if ($is_date = preg_match('/^\d{2,4}([\-\.])\d{2,2}\1\d{2,4}/', $value) == true) {
		$value = objectToArray(new DateTime($value)); // а точнее даже не в дату, а в массив
	}

	switch (trim(strtolower($method))) {
		case '=': // равно
			$data[$field] = $value;
			break;
		case '!=': case '<>': // неравно
			//$value = (is_numeric($value))?(int)$value:$value;
			if (!isset($data[$field])) {
				$data[$field] = array();
			}
			$data[$field]['$ne']=$value;
			break;
		case '>': // больше
			if (!isset($data[$field])) {
				$data[$field] = array();
			}
			$data[$field]['$gt']=$value;
			break;
		case '<': // меньше
			if (!isset($data[$field])) {
				$data[$field] = array();
			}
			$data[$field]['$lt']=$value;
			break;
		case '>=': // больше или равно
			if (!isset($data[$field])) {
				$data[$field] = array();
			}
			$data[$field]['$gte']=$value;
			break;
		case '<=': // меньше или равно
			if (!isset($data[$field])) {
				$data[$field] = array();
			}
			$data[$field]['$lte']=$value;
			break;
		case 'like': // like
			$value = str_replace("'","",$value);
			$flags = 'i';
			if (strpos($value, "%")!==0) {
				$value = "^" . $value;
			}
			$value = str_replace("%","",$value);
			if (checkMongoDb() == 'mongodb') {
				$data[$field] = new MongoDB\BSON\Regex($value, $flags);
			} else {
				$regex = "/$value/$flags";
				$data[$field] = new MongoRegex($regex);
			}
			break;
		case 'not like': // not like
			/**
			 * Использовал $nor для not like, т.к. $nor больше нигде не используется, а как создать массив из нескольких $not для одного поля пока не понял
			 * not like также вряд ли будет исользоваться в выражениях, связанных через or
			 */
			if (!isset($data['$nor'])) {
				$data['$nor'] = array();
			}
			$value = str_replace("'","",$value);
			$flags = 'i';
			if (strpos($value, "%")!==0) {
				$value = "^" . $value;
			}
			$value = str_replace("%","",$value);
			if (checkMongoDb() == 'mongodb') {
				$data['$nor'][] = array($field => new MongoDB\BSON\Regex($value, $flags));
			} else {
				$regex = "/$value/$flags";
				$data['$nor'][] = array($field => new MongoRegex($regex));
			}
			break;
		case 'in': // in
			$value = str_replace("(","",$value);
			$value = str_replace(")","",$value);
			$value = str_replace(" ","",$value);
			$value = explode(',',$value);
			if (!in_array(strtolower($field),array('usluga_code'))) {
				array_walk($value, 'convertFieldToInt');
			}
			foreach ($value as $k => $v) {
				if (strpos($v, "'")!==false) {
					$value[$k] = str_replace("'","",$v);
				}
			}
			if (!isset($data[$field])) {
				$data[$field] = array();
			}
			$data[$field]['$in']=$value;
			break;
		case 'not in': // not in
			$value = str_replace("(","",$value);
			$value = str_replace(")","",$value);
			$value = str_replace(" ","",$value);
			$value = explode(',',$value);
			if (!in_array(strtolower($field),array('usluga_code'))) {
				array_walk($value, 'convertFieldToInt');
			}
			foreach ($value as $k => $v) {
				if (strpos($v, "'")!==false) {
					$value[$k] = str_replace("'","",$v);
				}
			}
			if (!isset($data[$field])) {
				$data[$field] = array();
			}
			$data[$field]['$nin']=$value;
			break;
		case 'is': // is null
			$data[$field] = null;
			break;
	}
	return $data;
}

/**
 * Функция формирует массив для MongoDB по параметрам SQL
 */
function mongo_getwhere($sql) {
	if (strpos(strtoupper($sql), 'WHERE')!==false) {
		$params = array();
		preg_match('/(?:WHERE\s+(.+?)(?:\bNATURAL\b|\bHAVING\b|\bGROUP\b|\bORDER\b|\bLIMIT\b|$))/im', $sql, $m); /* LEFT\s|JOIN\s|ON\s|RIGHT\s|CROSS\s|INNER\s| */
		if (isset($m[1])) {
			$where = $m[1];
			// разбираем параметры
			$paramsOr = array();
			$or = false;
			$add = array();
			$i = 0;
			$k = -1;
			while (strlen($where)>0) {
				preg_match('/\s*(\w+)\s*(=|>=|<=|>|<|like|not like|in|not in|<>|!=|is\s)\s*(([А-Яа-я\w\'\.\-\,\%]+)|([\(А-Яа-я\w\'\.\-\,\%\s\)]+))\s*(and|or)*(.*)/im', $where, $m);
				if (is_array($m) && (count($m)==8)) {
					// Предварительная обработка: если разорванной оказалась строка (определяем по наличию одного символа '),
					// то в концовке ищем второй символ и все по этот символ прибиваем к разорванному значению
					$where = $m[7];
					if (substr_count($m[3], "'") == 1) {
						$p = strpos($where, "'");
						if ($p !== false) {
							$m[3] .= ''.substr($where, 0, $p+1); // todo: тут надо понять почему был пробел изначально
							preg_match('/\s*(and|or)*(.*)/im', substr($where, $p+1), $mm);
							$m[6] = $mm[1];
							//$m[7] = substr($where, $p+1);
							$m[7] = $mm[2];
						}
					}
					//print_r($m);
					if ((strtolower($m[6])=='or') || ($or === true)) { // собираем массив из ИЛИ
						if ($or === false) { // Первый элемент из условия по ИЛИ
							$k++;
						}
						$paramsOr[$k][] = mongo_where(null, $m[2], $m[1], $m[3]);

						$or = (strtolower($m[6])=='or');
						if ($or === false) { // Последний элемент из условия по ИЛИ
							$i++;
							// на случай нескольких or, оборачиваем or предварительно в and
							$add[]= array('$or'=>$paramsOr[$k]);
							//$params = array_merge($params, array('$or'=>$paramsOr[$k]));
						}
					} else {
						$params = array_merge($params, mongo_where($params, $m[2], $m[1], $m[3]));
						$i++;
					}
				} else {
					$where = '';
				}
			}
			// or обернем в and
			if (($ca = count($add))>1) { // больше одного $or
				$params = array_merge($params, array('$and'=>$add));
			} elseif ($ca==1)  { // если одно $or
				$params = array_merge($params, $add[0]);
			}
		}
		//var_dump($params); die();
		return $params;
	} else {
		return array();
	}

}

/**
 * Функция формирует массив GROUP BY для MongoDB по параметрам SQL
 */
function mongo_getgroup($sql) {
	// Загрушка
	/*
	if (strpos(strtoupper($sql), 'GROUP')!==false) {
		preg_match('/(?:GROUP\s+BY\s+([\w\*\,\s]+)\s+(?:ORDER|LIMIT))/im', $sql, $m);
		//print_r($m);
	}*/
}
/**
 * Функция формирует массив ORDER BY для MongoDB по параметрам SQL
 */
function mongo_getorder($sql) {
	$params = array();
	if (strpos(strtoupper($sql), 'ORDER')!==false) {
		// TODO: Надо прикрутить обработку DESC
		preg_match('/(?:ORDER\s+BY\s+([\w\*\,\s]+)\s+(?:LIMIT))/im', $sql, $m);
		if (isset($m[1])) {
			$data = explode(',',$m[1]);
			for($i=0; $i<count($data); $i++) {
				$params[trim($data[$i])] = $i+1;
			}
		}
	}
	return $params;
}
/**
 * Функция формирует массив HAVING для MongoDB по параметрам SQL
 */
function mongo_gethaving($sql) {
	// Загрушка
	/*
	if (strpos(strtoupper($sql), 'HAVING')!==false) {
		preg_match('/(?:HAVING\s+([\w\*\,\s]+)\s+(?:GROUP|ORDER|LIMIT))/im', $sql, $m);
	}*/
}
/**
 * Функция формирует массив LIMIT для MongoDB по параметрам SQL
 */
function mongo_getlimit($sql) {
	$params = array();
	$params['limit'] = 99999;
	$params['skip'] = 0;
	if (strpos(strtoupper($sql), 'LIMIT')!==false) {
		preg_match('/(?:LIMIT\s+([\d\*\,\s]+))/im', $sql, $m);
		if (isset($m[1])) {
			$data = explode(',',$m[1]);
			if (count($data)>1) {
				$params['limit'] = $data[1];
				$params['skip'] = $data[0];
			} elseif (isset($data[0])) {
				$params['limit'] = $data[0];
			}
		}
	}
	return $params;
}
?>