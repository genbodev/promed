<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/Usluga.php');

class Samara_Usluga extends Usluga {
  

	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Samara_Usluga_model', 'samara_dbmodel');
		
		$this->inputRules['loadNewUslugaComplexList'] = array(
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор МЭС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MesFilter_Enable',
					'label' => 'Флаг фильтрации по МЭС',
					'rules' => '',
					'default' => 0,
					'type' => 'int'
				),
				array(
					'field' => 'MesFilter_Evn_id',
					'label' => 'ID движения или посещения для фильтрации по МЭС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'allowedUslugaComplexAttributeList',
					'label' => 'Список допустимых типов атрибутов комплексной услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'or',
					'field' => 'allowedUslugaComplexAttributeMethod',
					'label' => 'Метод учета допустимых типов атрибутов комплексной услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'allowMorbusVizitOnly',
					'label' => 'Признак "Услуги по заболеванию"',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'allowNonMorbusVizitOnly',
					'label' => 'Признак "Услуги, кроме услуг по заболеванию"',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'disallowedUslugaComplexAttributeList',
					'label' => 'Список недопустимых типов атрибутов комплексной услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'withoutLpuFilter',
					'label' => 'Флаг загрузки без фильтрации по ЛПУ',
					'rules' => '',
					'default' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isEvnPrescr',
					'label' => 'Флаг загрузки услуг для назначений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuLevel_Code',
					'label' => 'Код профиля',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaCategory_id',
					'label' => 'Категория услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'uslugaCategoryList',
					'label' => 'Список категорий услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplex_Code',
					'label' => 'Код услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplex_Date',
					'label' => 'Дата актуальности услуги',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_pid',
					'label' => 'Идентификатор уровня',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MesOperTypeList',
					'label' => 'Вид лечение',
					'rules' => '',
					'type' => 'string'							
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль отделения',
					'rules' => '',
					'type' => 'id'
				)
			);
	}    
	   

	/**
	 * Функция загрузки списка услуг для нового комбо услуг
	 */
	function loadNewUslugaComplexList() {
		$this->load->helper('Options');

		$data = $this->ProcessInputData('loadNewUslugaComplexList', true);
		if ( $data === false ) { return false; }

		$response = $this->samara_dbmodel->loadNewUslugaComplexList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}	   
}