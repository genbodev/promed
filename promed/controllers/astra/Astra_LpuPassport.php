<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * astra_LpuPassport - контроллер для выполнения операций с паспортом МО (Астрахань)
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

class Astra_LpuPassport extends LpuPassport {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Меняем правила для некоторых полей
		foreach ( $this->inputRules['saveLpuPassport'] as $key => $array ) {
			switch ( $array['field'] ) {
				case 'Lpu_StickNick':
					$this->inputRules['saveLpuPassport'][$key]['rules'] = 'trim|max_length[38]';
				break;

				/*case 'LevelType_id':
					$this->inputRules['saveLpuPassport'][$key]['rules'] = 'required';
				break;*/

				case 'LpuAgeType_id':
				case 'LpuLevel_id':
				case 'LpuSubjectionLevel_id':
				case 'LpuType_id':
					$this->inputRules['saveLpuPassport'][$key]['rules'] = 'trim';
				break;
			}
		}
	}
}
