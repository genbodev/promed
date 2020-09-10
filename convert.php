<?php
/**
 * Скрипт для конвертации файлов в UTF-8
 *
 * @package		Promed
 * @author		Stanislav Bykov
 * @copyright	Copyright (c) 2014, Swan-Inform, Ltd.
 * @filesource
 */

	// Устанавливаем время работы скрипта
	@ini_set('max_execution_time', 3600);

 /**
 * Рекурсивная функция для конвертации файлов в UTF-8
 */
function convertDirFilesToUTF8($dir, $maskArray = array(), $showModifiedOnly = false) {
	if ( !is_dir($dir) ) {
		return false;
	}

	$fileList = scandir($dir);

	foreach ( $fileList as $entry ) {
		if ( in_array($entry, array('.', '..')) ) {
			continue;
		}

		if ( is_dir($dir . '/' . $entry) && $showModifiedOnly === false ) {
			echo '<div style="color: #009;">', $dir, '/', $entry, ': Директория</div>';
			convertDirFilesToUTF8($dir . '/' . $entry, $maskArray, $showModifiedOnly);
		}
		else if ( is_file($dir . '/' . $entry) && (count($maskArray) == 0 || preg_match('/(' . implode('|', $maskArray) . ')$/', $entry)) ) {
			// Конвертируем
			$data = file_get_contents($dir . '/' . $entry);

			if ( mb_detect_encoding($data, 'UTF-8', TRUE) === FALSE ) {
				file_put_contents($dir . '/' . $entry, iconv('windows-1251', 'utf-8//IGNORE', $data));
				echo '<div style="color: #090;">', $dir, '/', $entry, ': Файл обработан</div>';
			}
			else if ( $showModifiedOnly === false ) {
				echo '<div>', $dir, '/', $entry, ': ', mb_detect_encoding($data, 'UTF-8', TRUE), '</div>';
			}
		}
	}
}

$fileTypes = array('php', 'js', 'css');
$showModifiedOnly = (!empty($_GET['showModifiedOnly']) ? true : false);

convertDirFilesToUTF8('css', $fileTypes, $showModifiedOnly);
convertDirFilesToUTF8('jscore', $fileTypes, $showModifiedOnly);
convertDirFilesToUTF8('jscore4', $fileTypes, $showModifiedOnly);
convertDirFilesToUTF8('extjs', $fileTypes, $showModifiedOnly);
convertDirFilesToUTF8('extjs4', $fileTypes, $showModifiedOnly);
convertDirFilesToUTF8('jscore4', $fileTypes, $showModifiedOnly);
convertDirFilesToUTF8('promed', $fileTypes, $showModifiedOnly);
convertDirFilesToUTF8('system', $fileTypes, $showModifiedOnly);
?>