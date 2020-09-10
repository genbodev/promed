<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * ReagentModel - Контроллер для управления привязкой реагента к модели анализатора
 * 
 * @package	  common
 * @access	  public
 * @author	  Arslanov Azat
 */
class ReagentModel extends swController {
	/**
	 * конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'loadReagentList' => array(
				array('field' => 'query', 'label' => 'Наименование реактива', 'rules' => 'trim', 'type' => 'string')
			),
			'loadReagentModel' => array(
				array(
					'field' => 'ReagentModel_id',
					'label' => 'Идентификатор норматива',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadReagentModelGrid' => array(
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'id'
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
			'saveReagentModel' => array(
				array(
					'field' => 'ReagentModel_id',
					'label' => 'ReagentModel_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugNomen_id',
					'label' => 'Реактив',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'ReagentModel_id',
					'label' => 'Реактив модели анализатора',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('ReagentModel_model', 'dbmodel');
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
	 *  получение реактива модели анализатора
	 */
	function loadReagentModel() {
		$data = $this->ProcessInputData('loadReagentModel', true);
		if ($data) {
			$response = $this->dbmodel->loadReagentModel($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка реактивов
	 */
	function loadReagentModelGrid() {
		$data = $this->ProcessInputData('loadReagentModelGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadReagentModelGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  Сохранение
	 */
	function saveReagentModel() {
		$data = $this->ProcessInputData('saveReagentModel', true);
		if ($data){
			if (isset($data['ReagentModel_id'])) {
				$this->dbmodel->setReagentModel_id($data['ReagentModel_id']);
			}
			if (isset($data['AnalyzerModel_id'])) {
				$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
			}
			if (isset($data['DrugNomen_id'])) {
				$this->dbmodel->setDrugNomen_id($data['DrugNomen_id']);
			}

			$response = $this->dbmodel->saveReagentModel();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Реагента Модели анализатора')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление реагента модели анализатора
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->dbmodel->setReagentModel_id($data['ReagentModel_id']);
			$response = $this->dbmodel->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
