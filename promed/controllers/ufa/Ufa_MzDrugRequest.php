<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для объектов Справочник медикаментов: заявки по медикаментам
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version		16.07.2019
* @version
* @property MzDrugRequest_model MzDrugRequest_model
*/

require_once(APPPATH.'controllers/MzDrugRequest.php');


class Ufa_MzDrugRequest extends MzDrugRequest {
	
	/**
	 * Конструктор.
	 */ 
	function __construct() {
		parent::__construct();
		$this->inputRules['saveDrugRequestPersonOrderList'] = array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Persons', 'label' => 'Список ИД пациентов', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id')	
		);
	}
	
	/**
	 * Сохранение списка персональной разнарядки
	 */	
	function saveDrugRequestPersonOrderList() {
		$data = $this->ProcessInputData('saveDrugRequestPersonOrderList', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->saveDrugRequestPersonOrderList($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении списка персональной разнарядки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

}
