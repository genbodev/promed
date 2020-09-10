<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Privilege - модель для работы со льготами людей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Stas Bykov aka Savage (savage1981@gmail.com)
* @version			kareliya
*/

require_once(APPPATH.'models/_pgsql/Privilege_model.php');

class Kareliya_Privilege_model extends Privilege_model {
	/**
	 * Kareliya_Privilege_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}
}
