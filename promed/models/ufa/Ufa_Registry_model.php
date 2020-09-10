<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry_model - модель для работы с таблицей Registry
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      25.11.2018
*/
require_once(APPPATH.'models/Registry_model.php');

class Ufa_Registry_model extends Registry_model {
	var $scheme = "r2";
	var $region = "ufa";
}