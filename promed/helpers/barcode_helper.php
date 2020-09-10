<?php
/**
* Barcode_helper - хелпер с функциями для генерации штрих кода на рецепте
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      ?
*/

    defined('BASEPATH') or die ('No direct script access allowed');

    function GetBinaryStr($value, $max_length)
    {
        // Получение битовой строки из числовой строки
        $result = '';

        if (strlen($value) == 0)
        {
            return str_repeat('0', $max_length);
        }

        $result = base_convert($value, 10, 2);

        if (strlen($result) > $max_length)
        {
            $result = substr($result, strlen($result) - $max_length);
        }

        $result = str_pad($result, $max_length, "0", STR_PAD_LEFT);
        return $result;
    }

    function GetStrFromBinary($value)
    {
        // Символьная строка из битовой
        $c = 0;
        $result = '';
        $s = '';

        for ($i = 0; $i < strlen($value); $i += 8)
        {
            $s = substr($value, $i, 8);
            $c = 128 * substr($s, 0, 1) + 64 * substr($s, 1, 1) + 32 * substr($s, 2, 1) + 16 * substr($s, 3, 1) + 8 * substr($s, 4, 1) + 4 * substr($s, 5, 1) + 2 * substr($s, 6, 1) + 1 * substr($s, 7, 1);
            $result .= chr($c);
        }

        return $result;
    }

    function GetBinaryStrFromChar($value, $max_length, $pad_string = null)
    {
        // битовая строка из символьной строки
        $result = '';

        for ($i = 0; $i < strlen($value); $i++)
        {
            $result .= GetBinaryStr(ord(substr($value, $i, 1)), 8);
        }

        if (strlen($result) < $max_length && empty($pad_string))
        {
            $result = str_pad($result, $max_length,"0");
        } else {
			$result = str_pad($result, $max_length,"00100000");	//дополняем пробелами
		}
        return $result;
    }
	
	/** Определение пути Промеда для передачи Бирту 
	 */
	function getPromedUrl() {
		
		// Определение Http/Https
		$http = (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])?$_SERVER["HTTP_X_FORWARDED_PROTO"].'://':'http://');
		return $http.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"];
	}
?>