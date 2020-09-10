<?php

defined( 'BASEPATH' ) or die( 'No direct script access allowed' );

/**
 * CureStandart - клинические рекомендации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @property CureStandart_model
 */
class CureStandart extends swController {
	protected $inputRules = array(
		'loadCureStandartSpr' => array(
			array('field' => 'subj', 'label' => 'Справочник', 'rules' => 'required', 'type' => 'string'),
		),
		'save' => array(
			array('field' => 'action', 'label' => 'Событие', 	'rules' => 'required', 'type' => 'string'),
			array('field' => 'id', 'label' => 'Индекс рекомендации', 	'rules' => '', 'type' => 'id'),
			array('field' => 'Name', 'label' => 'Наименование', 	'rules' => 'required', 'type' => 'string'),
			array('field' => 'Age_id', 'label' => 'Возрастная категория', 	'rules' => 'required', 'type' => 'id'),

			array('field' => 'Diags', 'label' => 'Код по МКБ-10', 'rules' => 'required', 'type' => 'json_array'),
			array('field' => 'Phase_id', 'label' => 'Фаза', 	'rules' => 'required', 'type' => 'id'),
			array('field' => 'Stage_id', 'label' => 'Стадия', 		'rules' => 'required', 'type' => 'id'),
			array('field' => 'Complication_id', 'label' => 'Осложнения', 	'rules' => 'required', 'type' => 'id'),

			array('field' => 'Description', 'label' => 'Описание', 'rules' => '', 'type' => 'string'),
			array('field' => 'Duration', 'label' => 'Продолжительность лечения', 	'rules' => 'required', 'type' => 'id'),
			array('field' => 'Conditions', 'label' => 'Условия оказания', 'rules' => 'required', 'type' => 'json_array'),
			// Разделы .  rules: not required для всех
			array('field' => 'Diagnostika', 'label' => 'Диагностика', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'Treatment', 'label' => 'Лечения', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'TreatmentDrug', 'label' => 'Лекарственное лечение', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'NutrMixture', 'label' => 'Питательные смеси', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'Implant', 'label' => 'Импланты', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'PresBlood', 'label' => 'Компоненты и препараты крови', 'rules' => '', 'type' => 'json_array'),
		),
		'delete' => array(
			array('field' => 'id', 'label' => 'Индекс рекомендации', 	'rules' => 'required', 'type' => 'id'),
		),
		'load' => array(
			array('field' => 'id', 	'label' => 'Индекс', 'rules' => '', 'type' => 'int'),
		),
		'loadList' => array( //
			array('field' => 'node', 		'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'query','label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'age', 		'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'phase', 		'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'stage', 		'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'complication','label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'conditions', 	'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'standart', 	'label' => '', 'rules' => '', 'type' => 'string'),
		),
		'loadDiagList' => array(
			array('field' => 'query', 'label' => 'Строка для поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'diags', 'label' => 'Выбранные диагнозы', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'first', 'label' => 'Первая загрузка', 'rules' => '', 'type' => 'int'),
		),
		'loadUslugaComplexList' => array(
			array('field' => 'query', 'label' => 'Строка для поиска', 'rules' => '', 'type' => 'string'),
			array('field' => 'code', 'label' => 'Строка для поиска', 'rules' => '', 'type' => 'string'),
		),
		'loadUslugaComplex' => array(
			array('field' => 'id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id')
		),
		'loadCureStandartList' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id')
		),
		'loadDiag' => array(
			array('field' => 'CureStandart_id', 'label' => 'Идентификатор МЭС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'objectPrescribe', 'label' => 'Вид назначения', 'rules' => '', 'type' => 'string'),
		),
		'loadStandardDiagnosticsGrid' => array(
			array('field' => 'CureStandart_id', 'label' => 'Идентификатор МЭС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id')
		),
		'loadMNNbyACTMATTERS' => array(
			array('field' => 'Actmatters_id', 'label' => 'Идентификатор действующего вещества', 'rules' => 'required', 'type' => 'id')
		),
		'loadRecommendedDoseForDrug' => [
			['field' => 'ActMatter_id', 'label' => 'Идентификатор действующего вещества', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Person_Age', 'label' => 'Возраст пациента', 'rules' => '', 'type' => 'string'],
			['field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'],
			['field' => 'EvnClass', 'label' => 'Класс события', 'rules' => '', 'type' => 'string']
		]
	);
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model( 'CureStandart_model', 'CureStandart_model' );
	}

	/**
	 *  Загрузка данных клинической рекомендации
	 */
	function load() {
		$data = $this->ProcessInputData('load', true, true, true);
		if ($data === false) { return false; }
		if(!empty($data['id'])) {
			$response = $this->CureStandart_model->load($data['id']);
			$this->ProcessModelList($response, true, true)->ReturnData( array('success' => true, 'data'=> $response ) );
			return true;
		} else return false;
	}

	/**
	 * Сохранение клинической рекомендации
	 */
	function save() {
		$data = $this->ProcessInputData('save', true, true, true);
		if ($data === false) { return false; }

		$res = $this->CureStandart_model->save($data);

		$this->ReturnData($res);
	}

	/**
	 * Удаление клинической рекомендации
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true, true);
		if ($data === false) { return false; }

		$res = $this->CureStandart_model->delete($data);

		$this->ReturnData($res);
	}

	/**
	 *  Данные для ряда комбобоксов типовых справочников Клинических рекомендаций
	 *  Используется: форма swCureStandartsWindow
	 */
	function loadCureStandartSpr() {
		$data = $this->ProcessInputData('loadCureStandartSpr', true, true, true);
		if ($data === false) { return false; }
		if(!empty($data['subj'])) {
			$response = $this->CureStandart_model->loadSpr($data['subj']);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else return false;
	}

	/**
	 * Загрузка условий оказания в чекбоксы
	 * Используется на форме Клиническая рекомендация (swCureStandartWindow)
	 */
	function loadConditions() {
		$response = $this->CureStandart_model->loadConditions();

		if(sizeof($response) == 0) return false;

		$this->ProcessModelList( $response, true)->ReturnData();
	}

	/**
	 * Область данных на форме Клиническая рекомендация
	 * TreeStore ( ExtJS 6 )
	 */
	function loadTree() {
		$data = $this->ProcessInputData('loadList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->CureStandart_model->loadTree($data);

		if(sizeof($response) == 0) return false;

		$this->ProcessModelList( $response, true)->ReturnData();
	}
	
	/**
	 * Область данных на форме Пакеты назначений
	 * TreeStore ( ExtJS 6 )
	 */
	function loadTreeFederalStandards() {
		$data = $this->ProcessInputData('loadList', true, true, true);
		if ($data === false) { return false; }
		
		$data['standart'] = (!empty($data['standart'])) ? json_decode($data['standart']) : array();
		if($data['node'] == 'root'){
			$response = $this->CureStandart_model->getFirstLevelOfDiagnoses_loadTree($data);
		}else{
			$response = $this->CureStandart_model->loadTree($data);
		}

		if(sizeof($response) == 0) return false;
		
		if($data['node'] == 'root'){
			foreach ($response as $key => $value) {
				if($value['expanded'] == 1){
					$node = $value['sid'];
					$data['node'] = $node;
					$childr = $this->CureStandart_model->loadTree($data);
					$response[$key]['children'] = $childr;
//					$response[$key]['expanded'] = 1;
				}
			}
		}

		$this->ProcessModelList( $response, true)->ReturnData();
	}
	
	/**
	 * Федеральные стандарты на форме Пакеты назначений
	 * TreeStore ( ExtJS 6 )
	 */
	function loadFederalStandards() {
		$data = $this->ProcessInputData('loadList', true, true, true);
		if ($data === false) { return false; }
		
		if($data['standarrt']) $data['standart'] = json_decode($data['standart']);

		$response = $this->CureStandart_model->loadTree($data);

		if(sizeof($response) == 0) return false;		

		$this->ProcessModelList( $response, true)->ReturnData();
	}
	
	/**
	 * Колонка со списком стандартов на форме Пакеты назначений
	 */
	function loadCureStandartList(){
		$data = $this->ProcessInputData('loadCureStandartList', true, true, true);
		if (false == $data) { return false; }
		$response = $this->CureStandart_model->doLoadGrid(array('EvnPrescr_pid' => $data['Evn_id'], 'causingMethod' => 'loadCureStandartList'));
		
		$this->load->library('parser');
		$standartArray = array(); $resultArr = array();
		foreach ($response as $key => $value) {
			if(in_array($value['CureStandart_id'], $standartArray))	continue;
			$standartArray[] = $value['CureStandart_id'];
			//$value['CureStandart_Name_Title'] = wordwrap($value['CureStandart_Name'], 50, '<br>', false);
			$html = $this->parser->parse('cure_standart_select_column', $value,true);
			
			$resultArr["$key"] = array(
				'html'=>$html,
				'CureStandart_Name'=>$value['CureStandart_Name'],
				'Row_Num'=>$value['Row_Num'],
				'Diag_Code'=>$value['Diag_Code'],
				'Diag_id'=>$value['Diag_id'],
				'Diag_Name'=>$value['Diag_Name'],
				'CureStandart_id'=>$value['CureStandart_id'],
			);
		}
		$this->ProcessModelList($resultArr, true, true);
		$this->ReturnData();
		return true;
	}

	/**
	 * Справочник МКБ-10
	 */
	function loadDiagList() {
		$data = $this->ProcessInputData('loadDiagList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->CureStandart_model->loadDiagList($data);

		if(sizeof($response) == 0) return false;

		$this->ProcessModelList( $response, true)->ReturnData(array('success' => true, 'data'=> $response ));
	}

	/**
	 * Для комбо Услуги
	 */
	function loadUslugaComplexList() {
		$data = $this->ProcessInputData('loadUslugaComplexList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->CureStandart_model->loadUslugaComplexList($data);

		if(sizeof($response) == 0) return false;

		$this->ProcessModelList( $response, true)->ReturnData();
	}
	
	/**
	 * Загрузка данных услуги
	 */
	function loadUslugaComplex() {
		$data = $this->ProcessInputData('loadUslugaComplex', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->CureStandart_model->loadUslugaComplex($data);
		
		if(sizeof($response) == 0) return false;
		
		$this->ProcessModelList( $response, true)->ReturnData();
	}
	
	/**
	 * Грид Диагностика на форме Пакетные назначения
	 */
	function loadStandardDiagnosticsGrid(){
		$data = $this->ProcessInputData('loadStandardDiagnosticsGrid', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->CureStandart_model->loadStandardDiagnosticsGrid($data);
		$this->ReturnData(array(
			'Error_Msg' => null,
			'data' => $response,
		));
	}
	
	/**
	 * Грид Лечение на форме Пакетные назначения
	 */
	function loadStandardTreatmentsGrid(){
		$data = $this->ProcessInputData('loadStandardDiagnosticsGrid', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->CureStandart_model->loadStandardTreatmentsGrid($data);
		$this->ReturnData(array(
			'Error_Msg' => null,
			'data' => $response,
		));
	}
	
	/**
	 * Грид Медикаменты на форме Пакетные назначения
	 */
	function loadStandardTreatmentDrugGrid(){
		$data = $this->ProcessInputData('loadStandardDiagnosticsGrid', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->CureStandart_model->loadStandardTreatmentDrugGrid($data);
		$this->ReturnData(array(
			'Error_Msg' => null,
			'data' => $response,
		));
	}
	
	/**
	 * Загрузка Справочника комплексных МНН по действующему веществу
	 */
	function loadMNNbyACTMATTERS(){
		$data = $this->ProcessInputData('loadMNNbyACTMATTERS', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->CureStandart_model->loadMNNbyACTMATTERS($data);
		$this->ReturnData(array(
			'Error_Msg' => null,
			'data' => $response,
		));
	}
	
	/**
	 * Загрузка курсовой и дневной дозы по действующему веществу
	 */
	function loadRecommendedDoseForDrug(){
		$data = $this->ProcessInputData('loadRecommendedDoseForDrug', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->CureStandart_model->loadRecommendedDoseForDrug($data);
		$this->ReturnData(array(
			'Error_Msg' => null,
			'data' => $response,
		));
	}
	
	/**
	 * 
	 */
//	function loadDiagTest(){
//		$data = $this->ProcessInputData('loadDiag', true, true, true);
//		if ($data === false) { return false; }
//		
//		$this->CureStandart_model->applyData($data);
//		//$response = $this->CureStandart_model->getPrintData();
//		$response = $this->CureStandart_model->loadDiagTest();
//		$this->ReturnData(array(
//			'Error_Msg' => null,
//			'data' => $response,
//		));
//	}
}
