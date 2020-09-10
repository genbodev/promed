<?php
/**
* Предопределенные константы, сделано контроллером, так как нам нужен движок для работы с сессиями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      16.11.2010
*/

class JsMobileConstants extends CI_Controller
{
	/**
	 * @desc конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	/**
	 * @desc описание 
	 */
	function index() {
		echo "
			/**
			 * Предопределенные константы
			 *
			 * PromedWeb - The New Generation of Medical Statistic Software
			 * http://swan.perm.ru/PromedWeb
			 *
			 *
			 * @package      common
			 * @access       public
			 * @copyright    Copyright (c) 2012 Swan Ltd.
			 * @author       Miyusov Alexandr
			 * @version      19.11.2012
			 */
		";
		$host = explode(':', $_SERVER['HTTP_HOST']);
		echo "var connectHost= '{$host[0]}:".NODEJS_SOCKETSERVER_PORT."';\n";
	}
}
?>