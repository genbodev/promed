<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Convert - контроллер для конвертации данных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      15.07.2009
 */

 /**
 * @property Utils_model $dbmodel
 * @property Utils_model $umodel
 *
 */
class Convert extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("Convert_model", "umodel");

		$this->inputRules = array(
			'convertDataStorage' => array(
				array(
					'field' => 'object',
					'label' => 'Наименование параметра',
					'rules' => 'trim',
					'type' => 'string'
				)
			)
		);
	}

	/**
	 * Проводит конвертацию данных из UTF-8 в WIN-1251 данных из таблицы DataStorage
	 */
	function convertDataStorage() {
		$data = $this->ProcessInputData('convertDataStorage', true);
		if ($data === false) {return false;}

		if ( !isSuperadmin() ) {
			$this->ReturnError('В доступе отказано');
			return false;
		}
		
		$response = $this->umodel->convertDataStorage($data);
		$this->ProcessModelSave($response,true,'Извините, в данный момент сервис недоступен!')->ReturnData();
	}
}
