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
require_once(APPPATH.'controllers/EvnMorfoHistologicProto.php');

class Kz_EvnMorfoHistologicProto extends EvnMorfoHistologicProto {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		foreach ( $this->inputRules['saveEvnMorfoHistologicProto'] as $key => $array ) {
			if ( $array['field'] == 'EvnMorfoHistologicProto_Ser' ) {
				$array['rules'] = '';
			}

			$this->inputRules['saveEvnMorfoHistologicProto'][$key] = $array;
		}
	}
}
