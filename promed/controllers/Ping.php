<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Ping - контроллер для понга
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Ping
 * @access	   public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author	   Dmitriy Vlasenko
 */
class Ping extends swController {
	var $NeedCheckLogin = false;

	public $inputRules = array(
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Ответ на пинг
	 */
	function index() {
		$resp = array(
			'pong' => true
		);

		if (empty($_SESSION['pmuser_id'])) {
			$resp['needLogin'] = true; // признак необходимости авторизации
		}

		$this->ReturnData($resp);
	}
}