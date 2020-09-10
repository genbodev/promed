<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Vologda_EvnPLDispDop13_model - модель для работы с талонами по диспансеризации взрослого населения (Крым)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Stanislav Bykov
* @version      06.09.2017
*/
require_once(APPPATH.'models/_pgsql/EvnPLDispDop13_model.php');

class Vologda_EvnPLDispDop13_model extends EvnPLDispDop13_model
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
