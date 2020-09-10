<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ServiceSUR - контроллер для работы с порталом СУР
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			ServiceSUR
* @access			public
* @copyright		Copyright (c) 2015 Swan Ltd.
* @author			Sabirov Kirill (ksabirov@swan.perm.ru)
* @version			13.10.2015
*/

class ServiceSUR extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('ServiceSUR_model', 'dbmodel');
	}
}
?>