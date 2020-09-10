<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * ReagentNormRate - Контроллер для привязки норм расхода реагента к определенной модели анализатора
 * 
 * @package	  common
 * @access	  public
 * @author	  Arslanov Azat
 */
class ReagentNormRate extends swController {
	/**
	 * конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'saveReagentNormRate' => array(
				array(
					'field' => 'ReagentNormRate_id',
					'label' => 'ReagentNormRate_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_Code',
					'label' => 'Код услуги',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugNomen_id',
					'label' => 'Реагент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReagentNormRate_RateValue',
					'label' => 'Расход реактива',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'unit_id',
					'label' => 'Ед. измерения расходуемого реактива',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RefMaterial_id',
					'label' => 'Биоматериал',
					'rules' => '',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'ReagentNormRate_id',
					'label' => 'Норма расхода анализатора',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadReagentList' => array(
				array(
					'field' => 'query', 
					'label' => 'Наименование реактива', 
					'rules' => 'trim', 
					'type' => 'string'
				)
			),
			'loadReagentListForTest' => array(
				array(
					'field' => 'UslugaComplex_Code',
					'label' => 'Код услуги',
					//'rules' => 'trim|required',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					//'rules' => 'required',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadReagentNormRate' => array(
				array(
					'field' => 'ReagentNormRate_id',
					'label' => 'Идентификатор норматива',
					'rules' => '',//required',
					'type' => 'id'
				)
			),
			'loadReagentNormRateGrid' => array(
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_Code',
					'label' => 'Код услуги',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'int'
				)
			),
		);
		$this->load->database();
		$this->load->model('ReagentNormRate_model', 'dbmodel');
	}

	/**
	 *  Сохранение норматива расхода
	 */
	function saveReagentNormRate() {
		$data = $this->ProcessInputData('saveReagentNormRate', true);
		if ($data){
			if (isset($data['ReagentNormRate_id'])) {
				$this->dbmodel->setReagentNormRate_id($data['ReagentNormRate_id']);
			}
			if (isset($data['UslugaComplex_Code'])) {
				$this->dbmodel->setUslugaComplex_Code($data['UslugaComplex_Code']);
			}
			if (isset($data['AnalyzerModel_id'])) {
				$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
			}
			if (isset($data['DrugNomen_id'])) {
				$this->dbmodel->setDrugNomen_id($data['DrugNomen_id']);
			}
			if (isset($data['ReagentNormRate_RateValue'])) {
				$this->dbmodel->setReagentNormRate_RateValue($data['ReagentNormRate_RateValue']);
			}
			if (isset($data['unit_id'])) {
				$this->dbmodel->setunit_id($data['unit_id']);
			}
			if (isset($data['RefMaterial_id'])) {
				$this->dbmodel->setRefMaterial_id($data['RefMaterial_id']);
			}

			$response = $this->dbmodel->saveReagentNormRate();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Реагента Модели анализатора')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление норматива расхода
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->dbmodel->setReagentNormRate_id($data['ReagentNormRate_id']);
			$response = $this->dbmodel->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  получение списка всех реактивов из номенклатурного справочника
	 */
	function loadReagentList() {
		$data = $this->ProcessInputData('loadReagentList', true);
		if ($data) {
			$response = $this->dbmodel->loadReagentList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Получение списка реактивов, допустимых для данной модели и теста
	 *  Входящие данные: $_POST['Analyzer_id'] и $_POST['UslugaComplex_Code']
	 *  На выходе: JSON-строка
	 */
	function loadReagentListForTest() {
		$data = $this->ProcessInputData('loadReagentListForTest', true);
		if ($data) {
			$response = $this->dbmodel->loadReagentListForTest($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  получение реактива модели анализатора
	 */
	function loadReagentNormRate() {
		$data = $this->ProcessInputData('loadReagentNormRate', true);
		if ($data) {
			$response = $this->dbmodel->loadReagentNormRate($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  Получение списка реактивов
	 */
	function loadReagentNormRateGrid() {
		$data = $this->ProcessInputData('loadReagentNormRateGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadReagentNormRateGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
