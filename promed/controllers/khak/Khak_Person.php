<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Person - контроллер для управления людьми
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Common
 * @access		public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author		Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version		12.07.2009
 * @property Person_model dbmodel
*/
require_once(APPPATH.'controllers/Person.php');

class Khak_Person extends Person {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
}
