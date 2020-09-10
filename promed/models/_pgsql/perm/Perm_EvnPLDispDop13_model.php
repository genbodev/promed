<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Perm_EvnPLDispDop13_model - модель для работы с талонами по диспансеризации взрослого населения (Пермь)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Stanislav Bykov
* @version      12.05.2014
*/
require_once(APPPATH.'models/_pgsql/EvnPLDispDop13_model.php');

class Perm_EvnPLDispDop13_model extends EvnPLDispDop13_model
{
	/**
	 *	Конструктор
	 */	
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка категорий льготы, которые участвуют в ДВН
	 * @task https://redmine.swan.perm.ru/issues/37296
	 * @task https://redmine.swan.perm.ru/issues/115088
	 * @task https://redmine.swan.perm.ru/issues/126538 - с 2018 года исключен код 60
	 */
	public function getPersonPrivilegeCodeList($date = null) {
		$response = array(10,11,20,50,140,150);
		$year = date('Y');
		
		if ( $date instanceof DateTime ) {
			$year = $date->format('Y');
		}
		else if ( !empty($date) ) {
			$year = date('Y', strtotime($date));
		}

		if ( $year < 2018 ) {
			$response[] = 60;
		}

		return $response;
	}
}
