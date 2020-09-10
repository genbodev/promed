<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Perm_EvnPLDispProf_model - модель для работы с талонами по диспансеризации взрослого населения (Пермь)
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
require_once(APPPATH.'models/EvnPLDispProf_model.php');

class Perm_EvnPLDispProf_model extends EvnPLDispProf_model
{
	/**
	 *	Конструктор
	 */	
	function __construct()
	{
		parent::__construct();
	}
}
