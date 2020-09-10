<?php
/**
 * Скрипт для конвертации файлов из одной кодировки в другую
 *
 * @package		Promed
 * @author		Stanislav Bykov
 * @copyright	Copyright (c) 2014, Swan-Inform, Ltd.
 * @filesource
 */

 /**
  * Рекурсивная функция для конвертации файлов из одной кодировки в другую
  */
function convertFiles($fileList, $CPTo) {
	switch ( $CPTo ) {
		case 'windows-1251':
			$CPFrom = 'utf-8';
		break;

		case 'utf-8':
			$CPFrom = 'windows-1251';
		break;

		default:
			return false;
		break;
	}

	foreach ( $fileList as $entry ) {
		// Конвертируем
		$data = file_get_contents($entry);
		file_put_contents($entry, iconv($CPFrom, $CPTo . '//IGNORE', $data));
		echo '<div style="color: #090;">', $entry, ': Файл обработан</div>';
	}

	return true;
}

$fileList = array();

$f = fopen('convert_v2.txt', 'r');

while ( $s = fgets($f) ) {
	$s = trim($s);

	if ( is_file($s) ) {
		$fileList[] = $s;
	}
}

$CPTo = (!empty($_GET['CPTo']) ? $_GET['CPTo'] : 'utf-8');

convertFiles($fileList, $CPTo);
?>