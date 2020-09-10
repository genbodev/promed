<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		05.11.2009
 */

/**
 * Расширение стандартного класса валидации
 *
 * Расширенная версия с дополнительными проверками
 *
 * @package		Library
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 */
class SwForm_validation extends CI_Form_validation {
	function __construct() {
		parent::__construct();
	}
    
    /**
	 * No Empty
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function no_empty($str)
	{
		if (((mb_strtolower(trim($str))!='null') && (!is_numeric($str))) || (is_numeric($str) && ($str>0)))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * No Zero
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function no_zero($str)
	{
		if (is_numeric($str) && ($str>0))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Переводит строку в верхний регистр
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function uppercase($str)
	{
		return mb_strtoupper($str);
	}

	/**
	 * Переводит строку в нижний регистр
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function lowercase($str)
	{
		return mb_strtolower($str);
	}

	/**
	 * Удаление знаков процента
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function ban_percent( $str ) {
		return str_replace(array('%','_'), '', $str);
	}

	/**
	 * htmlspecialchars
	 *
	 * @param $str
	 * @return string
	 */
	function spec_chars($str) {
		return htmlspecialchars($str);
	}

	/**
	 * notnull
	 */
	function notnull($str) {
		// обрабатывается в Input_helper
		return $str;
	}
	
	/**
	 * Error String
	 *
	 * Returns the error messages as a string, wrapped in the error delimiters
	 *
	 * @param string $prefix Текст (символы, тэги) перед текстом ошибки
	 * @param string $suffix Текст (символы, тэги) после текста ошибки
	 * @param bool $json Подготовлен для JSON (true), выводить как есть
	 * @return string 
	 */
	public function error_string($prefix = '', $suffix = '', $json = true)
	{
		$str = parent::error_string($prefix = '', $suffix = '');
		if ($json){
			return htmlspecialchars($str, true);
		}
		
		return $str;
	}
	
}
// END swController class