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

require_once(APPPATH.'models/LpuStructure_model.php');

class Perm_LpuStructure_model extends LpuStructure_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Меняем правила для некоторых полей
		foreach ( $this->inputRules['saveLpuRegion'] as $key => $array ) {
			switch ( $array['field'] ) {
				case 'LpuRegionType_SysNick':
					$this->inputRules['saveLpuRegion'][$key]['rules'] = 'required';
				break;
			}
		}
	}
}
