<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для работы с непатентованными наименованиями
*/

class DrugNonpropNames extends swController{
	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules = array(
			'loadDrugNonpropNamesList' => array(
				array('field' => 'query', 'label' => 'Запрос', 'rules' => '', 'type' => 'string' ),
				array('field' => 'DrugNonpropNames_Code', 'label' => 'Код', 'rules' => '', 'type' => 'string' ),
				array('field' => 'DrugNonpropNames_Nick', 'label' => 'Наименование', 'rules' => '', 'type' => 'string' ),
				array('field' => 'DrugNonpropNames_Property', 'label' => 'Свойство', 'rules' => '', 'type' => 'string' ),
				array('field' => 'RlsActmatters_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id' ),
				array('field' => 'forCombo', 'label' => '', 'rules' => '', 'type' => 'id' ),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )
			),
			'saveDrugNonpropNames' => array(
				array('field' => 'DrugNonpropNames_id', 'label' => 'Идентификатор непатентованого наименования', 'rules' => '', 'type' => 'id' ),
				array('field' => 'DrugNonpropNames_Code', 'label' => 'Код непатентованого наименования', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'DrugNonpropNames_Nick', 'label' => 'Краткое наименование непатентованого наименования', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'DrugNonpropNames_Name', 'label' => 'Наименование непатентованого наименования', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'DrugNonpropNames_Property', 'label' => 'Свойство непатентованого наименования', 'rules' => '', 'type' => 'string' )
			),
			'deleteDrugNonpropNames' => array(
				array('field' => 'id', 'label' => 'Идентификатор непатентованого наименования', 'rules' => 'required', 'type' => 'id' )
			),
			'checkDrugNonpropNames' => array(
				array('field' => 'DrugNonpropNames_id', 'label' => 'Идентификатор непатентованого наименования', 'rules' => 'required', 'type' => 'id' )
			)
		);
		 
		$this->load->database();
		$this->load->model('DrugNonpropNames_model', 'dbmodel');
	}

	/**
	 *	Сохранение непатентованого наименования
	 */
	function saveDrugNonpropNames() {
		$data = $this->ProcessInputData('saveDrugNonpropNames', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveDrugNonpropNames($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Читает список рецептур
	 */
	function loadDrugNonpropNamesList() {
		$data = $this->ProcessInputData('loadDrugNonpropNamesList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadDrugNonpropNamesList($data);
		if(!empty($data['forCombo'])){
			$this->ProcessModelList($response, true, true)->ReturnData();
		} else {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Удаление непатентованого наименования
	 */
	function deleteDrugNonpropNames() {
		$data = $this->ProcessInputData('deleteDrugNonpropNames', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->deleteDrugNonpropNames($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Проверка связанных значений для непатентованого наименования
	 */
	function checkDrugNonpropNames() {
		$data = $this->ProcessInputData('checkDrugNonpropNames', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkDrugNonpropNames($data);
		$this->ProcessModelList($response, true)->ReturnData();
	}

}