<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnDrug - контроллер персонифицированного учета (регион: Уфа)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author			Марков Андрей
* @version			18.04.2010
*/

require_once(APPPATH.'controllers/EvnDrug.php');

class Ufa_EvnDrug extends EvnDrug {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		foreach ( $this->inputRules['saveEvnDrug'] as $key => $array ) {
			/*if ( in_array($array['field'], array('Storage_id')) ) {
				$array['rules'] = 'required';
			}*/

			$this->inputRules['saveEvnDrug'][$key] = $array;
		}
	}
}
?>