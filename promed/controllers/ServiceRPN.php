<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ServiceRPN - контроллер для работы с порталом РПН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			ServiceRPN
* @access			public
* @copyright		Copyright (c) 2015 Swan Ltd.
* @author			Markoff Andrew
* @version			07.2015
*/

class ServiceRPN extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('ServiceRPN_model', 'dbmodel');
	}
}
?>