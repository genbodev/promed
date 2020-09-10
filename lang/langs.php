<?php
/**
 * Формирование нового файла строк
 */
function generateNewLangs($filename)
{
	// берём казахские строки
	$kz_lang = file_get_contents("..\jscore\locale\kz\\".$filename.".js");
	preg_match_all("/lang\['(.*?)'\]\s*=\s*'(.*?)';/uis", $kz_lang, $matches);
	$kzArray = array();
	foreach ($matches[1] as $key => $value) {
		$kzArray[$value] = $matches[2][$key];
	}

	$kzNewArray = array();
	// берём русские строки
	$ru_lang = file_get_contents("..\jscore\locale\\ru\\".$filename.".js");
	preg_match_all("/lang\['(.*?)'\]\s*=\s*'(.*?)';/uis", $ru_lang, $matches);
	$ruArray = array();
	foreach ($matches[1] as $key => $value) {
		$ruArray[$value] = $matches[2][$key];
		if (!empty($kzArray[$value])) {
			$kzNewArray[$ruArray[$value]] = $kzArray[$value];
		} else {
			// echo $value; die(); // ну нет такой строки в казахском, ну и нет.
		}
	}
	// записываем новые казахские строки
	$newFileData = "if (typeof lang == \"undefined\") {".PHP_EOL.
	"	lang = [];".PHP_EOL.
	"}".PHP_EOL.PHP_EOL;
	file_put_contents("..\jscore\locale\\ru\\".$filename."_new.js", $newFileData);
	foreach ($kzNewArray as $key => $value) {
		$newFileData .= "lang['" . $key . "'] = '" . $value . "';" . PHP_EOL;
	}
	file_put_contents("..\jscore\locale\kz\\".$filename."_new.js", $newFileData);
}

generateNewLangs('promed');
generateNewLangs('portal');
?>
