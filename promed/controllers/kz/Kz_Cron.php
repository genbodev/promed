<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Cron - контроллер для выполнения задач по расписанию 
* Вызывается из командной строки: php cron.php  --run=/Cron/<method>
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
*/
require_once(APPPATH.'controllers/Cron.php');

class Kz_Cron extends Cron {

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		set_time_limit(0);
		ini_set("max_execution_time", "0");
	}
	
	/**
	 * Сервис ФОМС
	 */
	public function getFomsData() {
		$this->load->model('FOMS_model', 'dbmodel');
		$res = $this->dbmodel->doRequestAuto();
		echo "Задание выполнено ".date('d.m.Y, H:i:s');
	}
}