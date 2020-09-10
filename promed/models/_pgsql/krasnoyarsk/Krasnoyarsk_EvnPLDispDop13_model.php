<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Krasnoyarsk_EvnPLDispDop13_model - модель для работы с талонами по диспансеризации взрослого населения (Красноярский край)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://rtmis.ru/
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Stanislav Bykov
 * @version      23.01.2020
 */
require_once(APPPATH.'models/_pgsql/EvnPLDispDop13_model.php');

class Krasnoyarsk_EvnPLDispDop13_model extends EvnPLDispDop13_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка категорий льготы, которые участвуют в ДВН
	 * @task https://redmine.swan-it.ru/issues/188233
	 */
	public function getPersonPrivilegeCodeList($date = null) {
		return [ 10, 11, 20, 50, 140, 150 ];
	}
}
