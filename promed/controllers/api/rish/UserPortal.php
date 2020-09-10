<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * UserPortal - контроллер API для взаимодействия портала КВрачу с промедом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		2020
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class UserPortal extends SwREST_Controller
{

	protected $inputRules = array();

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
}