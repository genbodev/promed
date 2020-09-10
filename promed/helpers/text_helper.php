<?php
/**
* Text_helper - хелпер для работы с тестом. Перевод из одной кодировки в другую,
* транслит, генерация строк и прочее
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

    defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Функция замены символов с поддержкой utf-8
 */
function strtr_utf8($str, $from, $to) {
	if (!defined('USE_UTF') || !USE_UTF) {
		return strtr($str, $from, $to);
	}
	$keys = array();
	$values = array();
	preg_match_all('/[а-яёА-ЯЁ]|./u', $from, $keys);
	preg_match_all('/[а-яёА-ЯЁ]|./u', $to, $values);
	$mapping = array_combine($keys[0], $values[0]);
	return strtr($str, $mapping);
}

if (!function_exists('sw_translit'))
{
	/**
	 * Транслит
	 */
    function sw_translit($str, $lang = 'ru')
    {
		if ( $lang == 'ru' ) {
			$res = strtr_utf8(
				$str,
				"АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя",
				"F,DULT`;PBQRKVYJGHCNEA[WXIO]SM'.Zf,dult`;pbqrkvyjghcnea[wxio]sm'.z"
			);
		}
		else {
			$res = strtr_utf8(
				$str,
				"F,DULT`;PBQRKVYJGHCNEA[WXIO]SM'.Zf,dult`;pbqrkvyjghcnea[wxio]sm'.z",
				"АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя"
			);
		}

        return $res;
    }
}

/**
 * Транслитерация
 */
function translit($s) {
	$s = (string) $s; // преобразуем в строковое значение
	$s = strip_tags($s); // убираем HTML-теги
	$s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
	$s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
	$s = trim($s); // убираем пробелы в начале и конце строки
	$s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
	$s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
	$s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
	$s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
	return $s; // возвращаем результат
}
/**
 * TO-DO описать
 */
function uft8html2utf8( $s ) {
	if ( !function_exists('uft8html2utf8_callback') ) {
		/**
		 * TO-DO описать
		 */
		function uft8html2utf8_callback($t) {
			$dec = $t[1];
			if ($dec < 128) {
				$utf = chr($dec);
			} else if ($dec < 2048) {
				$utf = chr(192 + (($dec - ($dec % 64)) / 64));
				$utf .= chr(128 + ($dec % 64));
			} else {
				$utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
				$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
				$utf .= chr(128 + ($dec % 64));
			}
			return $utf;
		}
	}
	return preg_replace_callback('|&#([0-9]{1,});|', 'uft8html2utf8_callback', $s );
}

/**
 * TO-DO описать
 */
function utf8RawUrlDecode ($source) {
    $decodedStr = "";
    $pos = 0;
    $len = strlen ($source);
    while ($pos < $len) {
        $charAt = mb_substr ($source, $pos, 1);
        if ($charAt == '%') {
            $pos++;
            $charAt = mb_substr ($source, $pos, 1);
            if ($charAt == 'u') {
                // we got a unicode character
                $pos++;
                $unicodeHexVal = mb_substr ($source, $pos, 4);
                $unicode = hexdec ($unicodeHexVal);
                $entity = "&#". $unicode . ';';
                $decodedStr .= utf8_encode ($entity);
                $pos += 4;
            }
            else {
                // we have an escaped ascii character
                $hexVal = mb_substr ($source, $pos, 2);
                $decodedStr .= chr (hexdec ($hexVal));
                $pos += 2;
            }
        } else {
            $decodedStr .= $charAt;
            $pos++;
        }
    }
    return $decodedStr;
}

/**
 * Разбивает строку $str на подстроки, которые помещаются в массив, размером не более $count
 */
function SplitString($str, $count)
{
	$splited_strings = array();
	if ( $count >= strlen($str) || $count < 0 )
		return array($str);
	$str_pieces = explode(' ', $str);
	foreach ( $str_pieces as $piece )
	{
		if ( ( count($splited_strings) == 0 ) || (strlen($splited_strings[count($splited_strings) - 1]." ".$piece) > $count) )
		{
			$splited_strings[] = $piece;
		}
		else
		{
			$splited_strings[count($splited_strings) - 1].= " ".$piece;
		}
	}
	return $splited_strings;
}

/**
 * Конвертирует все элементы заданного массива, заменяя в них \r\n на <br/>
 */
function ReplaceRNToBr(&$var){
	if (is_string($var)){
		$var = str_replace(chr(10), '<br/>', $var);
	}
}

/**
 * Конвертирует все элементы заданного массива из кодировки UTF8 в Windows-1251
 */
function ConvertFromUTF8ToWin1251(&$var, $key = null, $ignore_use_utf = false){
	if (defined('USE_UTF') && USE_UTF && !$ignore_use_utf) {
		return $var;
	}
	if (is_string($var)){
		$var = iconv('utf-8', 'windows-1251//IGNORE', $var);//uft8html2utf8(utf8RawUrlDecode($var)));
	}
}

/**
 * Конвертирует все элементы заданного массива из кодировки Windows-1251 в UTF8
 */
function ConvertFromWin1251ToUTF8(&$var, $key = null, $ignore_use_utf = false){
	if (defined('USE_UTF') && USE_UTF && !$ignore_use_utf) {
		return $var;
	}
	if (is_string($var)){
		$var = iconv('windows-1251', 'UTF-8//IGNORE', $var); //cp1251
	}
}

/**
 * Заменяет все переносы строк
 */
function ReplaceLineBreaks(&$var, $key = null, $replacement = " ") {
	if (is_string($var)) {
		$var = str_replace(array("\r\n", "\r", "\n"), $replacement, $var);
	}
}

/**
 * Конвертирует все элементы заданного массива в нижний регистр
 */
function ConvertToLowerCase(&$var, $key = null) {
	$var = mb_strtolower($var);
}

/**
 * Конвертирует все элементы заданного массива из кодировки CP866 в Windows-1251
 */
function ConvertFromWin866ToCp1251(&$var){
	$tmp = $var;
	if (is_string($var)){
		$var = @iconv('cp866', 'cp1251//IGNORE', $var);
		if ( $var === false )
			$var = $tmp;
	}
}

/**
 * Конвертирует все элементы заданного массива из кодировки CP866 в UTF-8
 */
function ConvertFromWin866ToUtf8(&$var){
	$tmp = $var;
	if (is_string($var)){
		$var = @iconv('cp866', 'utf-8//IGNORE', $var);
		if ( $var === false )
			$var = $tmp;
	}
}

/**
 * Конвертирует все элементы заданного массива из кодировки Windows-1251 в CP866
 */
function ConvertFromWin1251ToCp866(&$var){
	$tmp = $var;
	if (is_string($var)){
		$var = @iconv('cp1251', 'cp866//IGNORE', $var);
		if ( $var === false )
			$var = $tmp;
	}
}

/**
 * Конвертирует все элементы заданного массива из кодировки UTF-8 в CP866
 */
function ConvertFromUtf8ToCp866(&$var){
	$tmp = $var;
	if (is_string($var)){
		$var = @iconv('UTF-8', 'cp866//IGNORE', $var);
		if ( $var === false )
			$var = $tmp;
	}
}

/**
 * Конвертирует строку в UTF-8, если строка уже в UTF-8 - просто возвращает ее
 */
function toUTF($var, $ignore_use_utf = false) {
	if (defined('USE_UTF') && USE_UTF && !$ignore_use_utf) {
		return $var;
	}

	if (mb_detect_encoding($var, 'UTF-8', TRUE) != 'UTF-8') {
		return iconv('windows-1251', 'utf-8//IGNORE', $var);
	} else {
		return $var;
	}
}

/**
 * Возвращает длину строки с учетом кодировки
 */
function sw_strlen($var, $ignore_use_utf = false) {
	if ( defined('USE_UTF') && USE_UTF && !$ignore_use_utf ) {
		return mb_strlen($var);
	}
	else {
		return strlen($var);
	}
}

/**
 * К windows-1251
 */
function toAnsi($var, $ignore_use_utf = false) {
	if (defined('USE_UTF') && USE_UTF && !$ignore_use_utf) {
		return $var;
	}
    return iconv('utf-8', 'windows-1251//IGNORE', $var);
}

/**
 * Only string and array vars supported
 * Сделано по аналогии с UTF-8
 * @author Alex M
 * @param $var
 * @return mixed object converted in Ansi 
 */
function toAnsiR($var, $ignore_use_utf = false){
	if(is_string($var)) {
		return toAnsi($var, $ignore_use_utf);
	} else if(is_array($var)){
		$temp = array();
		foreach($var as $key=>$value){
			$temp[toAnsi($key, $ignore_use_utf)] = toAnsiR($value, $ignore_use_utf);
		}
		return $temp;
	} else return $var;
}

/**
 * Only string and array vars supported
 * @author moloco
 * @param $var
 * @return mixed object converted in UTF-8 
 */
function toUTFR($var){
	if(is_string($var)) {
		return toUTF($var);
	} else if(is_array($var)){
		$temp = array();
		foreach($var as $key=>$value){
			$temp[toUTF($key)] = toUTFR($value);
		}
		return $temp;
	} else return $var;
}

/**
 * Возвращение строки для HTML
 */
function returnValidHTMLString($str) {
	return ( !empty($str) ? htmlspecialchars($str, ENT_QUOTES, (defined('USE_UTF') && USE_UTF)?'utf-8':'windows-1251') : '&nbsp;' );
}


/**
 * Спряжение множественных форм существительных
 * Пример: "Прошло $n " . plural($n, 'день', 'дня', 'дней')
 */
function plural($n, $form1, $form2, $form5)
{
	$n = abs($n) % 100;
	$n1 = $n % 10;
	if ($n > 10 && $n < 20) return $form5;
	else if ($n1 > 1 && $n1 < 5) return $form2;
	else if ($n1 == 1) return $form1;

	return $form5;
}

if ( ! function_exists('ru_word_case')) {
    /**
     * Склоняет слово по числам
     *
     * на входе
     * $case1 - ед. число,
     * $case2 - мн. число для 2, 3, 4 или оканчивающихся на 2, 3, 4
     * $case3 - мн. число для 5-20 (включительно), и всех что кончаются на любые кроме 2, 3, 4
     * $anInteger - число
     * пример:
     *   '1 '.ru_word_case('день', 'дня', 'дней', 1) // output: 1 день
     *   '2 '.ru_word_case('день', 'дня', 'дней', 2) // output: 2 дня
     *   '11 '.ru_word_case('день', 'дня', 'дней', 11) // output: 11 дней
     *   '21 '.ru_word_case('день', 'дня', 'дней', 21) // output: 21 день

     * @param $case1
     * @param $case2
     * @param $case3
     * @param $anInteger
     * @return mixed
     */
    function ru_word_case($case1, $case2, $case3, $anInteger){
        $result = $case3;
        if (($anInteger < 5)||(20 < $anInteger)) {
            $days = (string)$anInteger;
            $lastSymbol =  $days[strlen($anInteger)-1];
            switch ($lastSymbol) {
                case '1':
                    $result = $case1;
                    break;
                case '2':
                case '3':
                case '4':
                    $result = $case2;
                    break;
                default:
                    break;
            }
        }
        return $result;
    }
}


/**
 * Переводит целое число от 1 до 31 в систему исчисления по основанию 35: 0-0, 1 - 1, 2 - 2, ..., 10 - A, ..., 31 - V.
 * Для чисел вне диапазона [0,35] вернет false
 * @param int $i
 * @return string
 */
function int2base35($i){
    if ($i >= 0 && $i <= 35) {
        if ($i>9) {
            $chr = $i + 55;
        } else {
            $chr = $i + 48;
        }
        $result = chr($chr);
    } else {
        $result = false;
    }
    return $result;
}

/**
 * Функция, обратная ф-ции int2base35
 *
 * @param string
 * @return int
 */
function base35toInt($s){
    $result = false;
    if (strlen($s) === 1) {
        $s = strtoupper($s);
        $i = ord($s);
        if ($i <= 90) {
            if ($i >= 65) {
                //буква A..Z
                $result = $i - 55;
            } else {
                if ($i >= 48 && $i <= 57) {
                    //цифра 0..9
                    $result = $i - 48;
                }
            }
        }
    }
    return $result;
}

/**
 * XML в массив
 */
function xml2array ( $xmlObject, $out = array () ) {
	foreach ( (array) $xmlObject as $index => $node )
		$out[$index] = ( is_object($node) || is_array($node) ) ? xml2array ( $node ) : $node;
	return $out;
}


if ( !function_exists('mb_ucfirst') ) {
	/**
	 * Делает первую букву в мультибайтовой строке заглавной
	 */
	function mb_ucfirst($str) {
		return mb_convert_case(mb_strtolower($str), MB_CASE_TITLE);
	}
}

/**
 * Генерирует случайную строку
 */
function swGenRandomString($length = 16) {
	$result = '';

	for ( $i = 1; $i <= $length; $i++ ) {
		$tmp = mt_rand(1, 62);

		if ( $tmp <= 10 ) {
			$result .= chr($tmp + 47);
		}
		elseif ( ($tmp > 10) && ($tmp <= 36) ) {
			$result .= chr($tmp + 54);
		}
		else {
			$result .= chr($tmp + 60);
		}
	}

	return $result;
}

/**
 * Считает CheckSum для EAN13 штрих кода
 */
function ean13_check_digit($digits){
	//first change digits to a string so that we can access individual numbers
	$digits =(string)$digits;
	// 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
	$even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];
	// 2. Multiply this result by 3.
	$even_sum_three = $even_sum * 3;
	// 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
	$odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];
	// 4. Sum the results of steps 2 and 3.
	$total_sum = $even_sum_three + $odd_sum;
	// 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
	$next_ten = (ceil($total_sum/10))*10;
	$check_digit = $next_ten - $total_sum;
	return $digits . $check_digit;
}

/**
 * Метод для рукурсивного вывода сообщения из массива(из сохраненного json-ответа от казахского сервиса)
 *
 * @param array $arr
 * @param string $t
 * @return null|string
 */
function recursiveArrayToString(array $arr, $t = '')
{
	$output = null;
	$t .= "\t";

	foreach ($arr as $key => $value)
	{
		if (is_bool($value))
		{
			$value = $value ? 'true' : 'false';
		}

		if (is_null($value))
		{
			$value = $value ?  : 'null';
		}

		$output .= "$t<b>$key</b>: ";
		if (is_array($value))
		{
			$output .= "<br>" . recursiveArrayToString($value, $t);
		}
		if (is_string($value) || is_numeric($value) || is_null($value) || is_bool($value))
		{
			$output .= "$value <br>";
		}

	}

	return $output;
}