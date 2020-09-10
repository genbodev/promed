<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Online - контроллер API для проверки онлайна
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Online extends SwREST_Controller{

	/**
	 * Возвращает всегда {"error_code":0,"success":true}
	 */
	function index_get(){
		$this->response(array('error_code' => 0,'success' => true));
	}
}
