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
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Bykov Stanislav (savage@swan.perm.ru)
* @version      08.10.2014
*/
require_once(APPPATH.'controllers/LpuPassport.php');

class Kz_LpuPassport extends LpuPassport {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		foreach ( $this->inputRules['saveLpuPassport'] as $key => $array ) {
			if ( $array['field'] == 'Org_INN' ) {
				$array['label'] = 'ИИН';
			}

			if ( in_array($array['field'], array('Lpu_RegNum')) ) {
				$array['type'] = 'string';
			}

			if ( in_array($array['field'], array('Oktmo_id', 'Org_INN', 'Org_KPP', 'Org_OGRN', 'Org_OKPO', 'Lpu_Okato')) ) {
				$array['rules'] = '';
			}
			else if ( in_array($array['field'], array('LpuNomen_id')) ) {
				$array['rules'] = 'required';
			}

			$this->inputRules['saveLpuPassport'][$key] = $array;
		}
	}
}
