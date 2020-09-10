<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * astra_LpuPassport - контроллер для выполнения операций с паспортом МО (Пермь)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Быков Станислав
 * @version      март 2014
 */
require_once(APPPATH.'controllers/LpuPassport.php');

class Perm_LpuPassport extends LpuPassport {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Меняем правила для некоторых полей
		foreach ( $this->inputRules['saveLpuPassport'] as $key => $array ) {
			switch ( $array['field'] ) {
				case 'LpuLevel_id':
					// Закомментировал, ибо https://redmine.swan.perm.ru/issues/36345
					// $this->inputRules['saveLpuPassport'][$key]['rules'] = 'trim';
				break;
			}
		}
	}
}
