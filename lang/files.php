<?php
/**
 * Получение списка js-файлов
 */
function GetListFiles($folder, &$all_files)
{
	$fp = opendir($folder);
	while ($cv_file = readdir($fp)) {
		if (
			is_file($folder . "/" . $cv_file) &&
			pathinfo($cv_file, PATHINFO_EXTENSION) == 'js' &&
			strpos($folder, '/locale/') === false &&
			strpos($cv_file, 'portal.js') === false
		) {
			$all_files[] = $folder . "/" . $cv_file;
		} elseif ($cv_file != "." && $cv_file != ".." && is_dir($folder . "/" . $cv_file)) {
			GetListFiles($folder . "/" . $cv_file, $all_files);
		}
	}
	closedir($fp);
}

// берём русские строки
$ru_lang = file_get_contents("..\jscore\locale\\ru\promed.js");
preg_match_all("/lang\['(.*?)'\]\s*=\s*'(.*?)';/uis", $ru_lang, $matches);
$ruArray = array();
foreach ($matches[1] as $key => $value) {
	$ruArray[$value] = $matches[2][$key];
}

//Получаем все js-файлы из promed/jscore
GetListFiles("../jscore", $all_files);

foreach($all_files as $file) {
	// читаем каждый файл, заменяем в нём lang[ на langs(
	$fileData = file_get_contents($file);
	unset($matches);
	preg_match_all('/lang\[\'(.*?)\'\]/uis', $fileData, $matches);
	if (!empty($matches[1]))
	foreach($matches[1] as $key => $value) {
		if (!empty($ruArray[$value])) {
			// заменяем по файлу все вхождения такой фразы
			$fileData = str_replace($matches[0][$key], "langs('" . $ruArray[$value] . "')", $fileData);
		} else {
			// не нашли фразу, это серьёзный баг! но всё равно переведём на использование langs
			$fileData = str_replace($matches[0][$key], "langs('" . $value . "')", $fileData);
			echo $value . "<br>";
		}
	}
	file_put_contents($file, $fileData);
}
?>