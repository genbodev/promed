<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter i18n library
 *
 */
class SwLang extends CI_Lang {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Возвращает текущий язык
	 */
	function lang() {
		if (!empty($_SESSION['language'])) {
			return $_SESSION['language'];
		}
		return null;
	}

}

/* End of file */
