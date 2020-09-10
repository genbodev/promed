<?php
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stanislav
* @version      22.11.2016
*/

require_once(APPPATH.'models/_pgsql/LpuPassport_model.php');

class Perm_LpuPassport_model extends LpuPassport_model {
    /**
	 *	Функция проверки класса медицинского изделия на наличие созданных медицинских изделий
	 */
	public function checkMedProductCardHasClass($data)
	{
		return false;
	}
}
