<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* LpuPassport - операции с паспортом МО (Республика Беларусь)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Bykov Stanislav (savage@swan.perm.ru)
* @version      15.10.2014
*/
require_once(APPPATH.'controllers/LpuPassport.php');

class By_LpuPassport extends LpuPassport {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		foreach ( $this->inputRules['saveLpuPassport'] as $key => $array ) {
			switch ( $array['field'] ) {
				case 'Lpu_f003mcod': $array['label'] = 'Федеральный код МО'; break;
				case 'Lpu_RegNomN2': $array['label'] = 'Региональный код МО'; break;
				case 'Lpu_Okato': $array['label'] = 'СОАТО'; break;
				case 'Org_INN': $array['label'] = 'УНП'; break;
				case 'Org_OGRN': $array['label'] = 'Номер в ЕГР'; break;
				case 'Okved_id': $array['label'] = 'ОКЭД'; break;
				case 'LpuLevel_id': $array['label'] = 'Технологический уровень МП'; break;
				case 'Lpu_PensRegNum': $array['label'] = 'Рег. номер в ПФ'; break;
			}

			if ( in_array($array['field'], array('Oktmo_id', 'Org_OKPO', 'Org_KPP')) ) {
				$array['rules'] = '';
			}
			else if ( in_array($array['field'], array('Okogu_id')) ) {
				$array['rules'] = 'required';
			}

			$this->inputRules['saveLpuPassport'][$key] = $array;
		}

		foreach ( $this->inputRules['saveMOArea'] as $key => $array ) {
			switch ( $array['field'] ) {
				case 'MoArea_OKATO': $array['label'] = 'СОАТО'; break;
			}

			$this->inputRules['saveMOArea'][$key] = $array;
		}
	}
}
