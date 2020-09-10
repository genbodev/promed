<?php
/**
 * Config_helper - хелпер работы с Аррей-конфигом
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

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Функция сохранения массива в конфиг-файл
 */
function arraytofile($array, $filename = '', $comment = '')
{
	/**
	 * @param $array
	 * @param $file
	 * @param $level
	 */
	function recurse($array, $file, $level)
	{
		fwrite($file, "Array(\n");
		$i = 0;
		foreach ($array as $key => $value) {
			if ($i++ != 0) {
				fwrite($file, ",\n");
			}
			if (is_array($value)) {
				fwrite($file, str_repeat('	', ($level)) . "'$key' => ");
				recurse($value, $file, $level + 1);
			} else {
				$value = addcslashes($value, "'" . "\\\\");
				fwrite($file, str_repeat('	', ($level)) . "'$key' => '$value'");
			}
		}
		fwrite($file, "\n" . str_repeat('	', ($level - 1)) . ")");
	}

	$level = 0;
	$file = fopen($filename, "w");
	if (!$file) {
		return false;
	}
	if (strlen($comment) == 0) {
		$comment = '/* Конфиг */';
	}
	fwrite($file, "<?php \n" . $comment . "\n return ");
	recurse($array, $file, $level + 1);
	fwrite($file, ";\n?" . ">");
	fclose($file);
	return true;
}

/**
 * Функция построния меню по существующему конфигу (массиву)
 */
function createJSMenu($array)
{
	/**
	 * @param $array
	 * @param $file
	 * @param $level
	 */
	function recurse($array, $file, $level)
	{
		fwrite($file, "Array(\n");
		$i = 0;
		foreach ($array as $key => $value) {
			if ($i++ != 0) {
				fwrite($file, ",\n");
			}
			if (is_array($value)) {
				fwrite($file, str_repeat('	', ($level)) . "'$key' => ");
				recurse($value, $file, $level + 1);
			} else {
				$value = addcslashes($value, "'" . "\\\\");
				fwrite($file, str_repeat('	', ($level)) . "'$key' => '$value'");
			}
		}
		fwrite($file, "\n" . str_repeat('	', ($level - 1)) . ")");
	}

	$level = 0;
	$file = fopen($filename, "w");
	if (!$file) {
		return false;
	}
	if (strlen($comment) == 0) {
		$comment = '/* Конфиг */';
	}
	fwrite($file, "<?php \n" . $comment . "\n return ");
	recurse($array, $file, $level + 1);
	fwrite($file, ";\n?" . ">");
	fclose($file);
	return true;
}

?>