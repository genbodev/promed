<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* LpuPassport - операции с паспортом МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2015 Swan Ltd.
* @author       Kurakin Alexander (a.kurakin@swan.perm.ru)
* @version      03.12.2015
*/
require_once(APPPATH.'controllers/EvnHistologicProto.php');

class Kz_EvnHistologicProto extends EvnHistologicProto {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		foreach ( $this->inputRules['saveEvnHistologicProto'] as $key => $array ) {
			if ( $array['field'] == 'EvnHistologicProto_Ser' ) {
				$array['rules'] = '';
			}

			$this->inputRules['saveEvnHistologicProto'][$key] = $array;
		}
	}
}
