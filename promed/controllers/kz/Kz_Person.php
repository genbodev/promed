<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * kz_Person - контроллер для управления людьми (Казахстан)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Быков Станислав
 * @version      апрель 2014
 */
require_once(APPPATH.'controllers/Person.php');

class Kz_Person extends Person {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Меняем правила для некоторых полей
		foreach ( $this->inputRules['savePersonEditWindow'] as $key => $array ) {
			switch ( $array['field'] ) {
				// https://redmine.swan.perm.ru/issues/36860
				case 'Person_FirName':
				case 'Person_SecName':
				case 'Person_SurName':
					$this->inputRules['savePersonEditWindow'][$key]['rules'] = 'trim';
				break;
			}
		}

		// https://redmine.swan.perm.ru/issues/41370
		foreach ( $this->inputRules['getPersonCardGrid'] as $key => $array ) {
			switch ( $array['field'] ) {
				case 'Person_Inn':
					$this->inputRules['getPersonCardGrid'][$key]['label'] = 'РРРќ';
				break;
			}
		}
	}
}
