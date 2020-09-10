<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для работы с экстемпоральными рецептурами
*/

/**
 * @property Extemporal_model $dbmodel
*/
class Extemporal extends swController{
	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules = array(
			'loadExtemporalList' => array(
				array('field' => 'Extemporal_Code', 'label' => 'Код рецептуры', 'rules' => '', 'type' => 'int' ),
				array('field' => 'Extemporal_Name', 'label' => 'Наименование рецептуры', 'rules' => '', 'type' => 'string' ),
				array('field' => 'ExtemporalType_id', 'label' => 'Вид прописи', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => '', 'type' => 'id' ),
				array('field' => 'withoutPaging', 'label' => '', 'rules' => '', 'type' => 'int' ),
				array('field' => 'ExtemporalComp_Name', 'label' => 'Компонент', 'rules' => '', 'type' => 'string' ),
				array('field' => 'Org_Name', 'label' => 'Организация', 'rules' => '', 'type' => 'string' ),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 50 )
			),
			'getLatName' => array(
				array('field' => 'Tradename_id', 'label' => 'Идентификатор препарата', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Actmatters_id', 'label' => 'Идентификатор вещества', 'rules' => '', 'type' => 'id' )
			),
			'saveExtemporal' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Extemporal_Code', 'label' => 'Код рецептуры', 'rules' => 'required', 'type' => 'int' ),
				array('field' => 'ExtemporalType_id', 'label' => 'Вид прописи', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Extemporal_Name', 'label' => 'Наименование рецептуры', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'RlsClsdrugforms_id', 'label' => 'Идентификатор лекарственной формы', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Extemporal_IsClean', 'label' => 'Идентификатор стерильности', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Extemporal_daterange', 'label' => 'Период действия записи', 'rules' => '', 'type' => 'daterange' )
			),
			'deleteExtemporal' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' )
			),
			'copyExtemporal' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' )
			),
			'saveExtemporalComp' => array(
				array('field' => 'ExtemporalComp_id', 'label' => 'Идентификатор компонента', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ExtemporalCompType_id', 'label' => 'Идентификатор вида компонента', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ExtemporalComp_Name', 'label' => 'Наименование компонента', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'ExtemporalComp_LatName', 'label' => 'Латинское наименование компонента', 'rules' => '', 'type' => 'string' ),
				array('field' => 'RlsActmatters_id', 'label' => 'Идентификатор действующего вещества', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id' ),
				array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор единицы измерения', 'rules' => '', 'type' => 'id' ),
				array('field' => 'ExtemporalComp_Count', 'label' => 'Количество', 'rules' => '', 'type' => 'float' )
			),
			'deleteExtemporalComp' => array(
				array('field' => 'ExtemporalComp_id', 'label' => 'Идентификатор компонента', 'rules' => 'required', 'type' => 'id' )
			),
			'loadExtemporalCompList' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' )
			),
			'loadExtemporal' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' )
			),
			'checkExtemporalComp' => array(
				array('field' => 'RlsActmatters_id', 'label' => 'Идентификатор действующего вещества', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ExtemporalComp_id', 'label' => 'Идентификатор компонента', 'rules' => '', 'type' => 'id' )
			),
			'checkExtemporal' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Extemporal_IsClean', 'label' => 'Идентификатор стерильности', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'count', 'label' => 'Количество компонентов', 'rules' => 'required', 'type' => 'int' ),
				array('field' => 'actmatters', 'label' => 'Компоненты', 'rules' => '', 'type' => 'string', 'default' => '' ),
				array('field' => 'tradenames', 'label' => 'Компоненты', 'rules' => '', 'type' => 'string', 'default' => '' )
			),
			'checkExtemporalName' => array(
				array('field' => 'Extemporal_Name', 'label' => 'Наименование рецептуры', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => '', 'type' => 'id' )
			),
			'checkExtemporalCompStandart' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ExtemporalCompStandart_id', 'label' => 'Идентификатор тарифа', 'rules' => '', 'type' => 'id' )
			),
			'loadExtemporalCompStandartList' => array(
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id' )
			),
			'saveExtemporalCompStandart' => array(
				array('field' => 'ExtemporalCompStandart_id', 'label' => 'Идентификатор тарифа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Extemporal_id', 'label' => 'Идентификатор рецептуры', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор единицы измерения', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ExtemporalCompStandart_Count', 'label' => 'Количество', 'rules' => 'required', 'type' => 'float' ),
				array('field' => 'ExtemporalCompStandart_Tariff', 'label' => 'Тариф', 'rules' => 'required', 'type' => 'float' )
			),
			'deleteExtemporalCompStandart' => array(
				array('field' => 'ExtemporalCompStandart_id', 'label' => 'Идентификатор тарифа', 'rules' => 'required', 'type' => 'id' )
			)
		);
		 
		$this->load->database();
		$this->load->model('Extemporal_model', 'dbmodel');
	}
    
	/**
	 *	Получает латинское название препарата
	 */
	function getTorgLatName() {
		$data = $this->ProcessInputData('getTorgLatName', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTorgLatName($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Сохранение рецептуры
	 */
	function saveExtemporal() {
		$data = $this->ProcessInputData('saveExtemporal', true);
		if ($data === false) { return false; }
		$data['CLSDRUGFORMS_ID'] = $data['RlsClsdrugforms_id'];
		$data['Extemporal_begDT'] = $data['Extemporal_daterange'][0];
		$data['Extemporal_endDT'] = $data['Extemporal_daterange'][1];
		$data['Region_id'] = $this->dbmodel->getRegionNumber();
		$response = $this->dbmodel->saveExtemporal($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Читает список рецептур
	 */
	function loadExtemporalList() {
		$data = $this->ProcessInputData('loadExtemporalList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadExtemporalList($data);
		if(!empty($data['withoutPaging'])){
			$this->ProcessModelList($response, true, true)->ReturnData();
		} else {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
	}

	/**
	 *	Сохранение компонента рецептуры
	 */
	function saveExtemporalComp() {
		$data = $this->ProcessInputData('saveExtemporalComp', true);
		if ($data === false) { return false; }
		$data['ACTMATTERS_ID'] = $data['RlsActmatters_id'];
		$data['TRADENAMES_ID'] = $data['Tradenames_id'];
		$response = $this->dbmodel->saveExtemporalComp($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 *	Удаление компонента рецептуры
	 */
	function deleteExtemporalComp() {
		$data = $this->ProcessInputData('deleteExtemporalComp', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->deleteExtemporalComp($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Копирует рецептуру
	 */
	function copyExtemporal() {
		$data = $this->ProcessInputData('copyExtemporal', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->copyExtemporal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Читает список компонентов рецептуры
	 */
	function loadExtemporalCompList() {
		$data = $this->ProcessInputData('loadExtemporalCompList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadExtemporalCompList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Читает тарифы рецептуры
	 */
	function loadExtemporalCompStandartList() {
		$data = $this->ProcessInputData('loadExtemporalCompStandartList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadExtemporalCompStandartList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Сохранение тарифа рецептуры
	 */
	function saveExtemporalCompStandart() {
		$data = $this->ProcessInputData('saveExtemporalCompStandart', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveExtemporalCompStandart($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Удаление тарифа рецептуры
	 */
	function deleteExtemporalCompStandart() {
		$data = $this->ProcessInputData('deleteExtemporalCompStandart', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->deleteExtemporalCompStandart($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 *	Проверка наличия компонента в рецептуре
	 */
	function checkExtemporalComp() {
		$data = $this->ProcessInputData('checkExtemporalComp', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkExtemporalComp($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Проверка наличия тарифа для рецептуры
	 */
	function checkExtemporalCompStandart() {
		$data = $this->ProcessInputData('checkExtemporalCompStandart', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkExtemporalCompStandart($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Проверка уникальности рецептуры
	 */
	function checkExtemporal() {
		$data = $this->ProcessInputData('checkExtemporal', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkExtemporal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Проверка уникальности наименования рецептуры
	 */
	function checkExtemporalName() {
		$data = $this->ProcessInputData('checkExtemporalName', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkExtemporalName($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение кода рецептуры
	 */
	function getExtemporalCode() {
		$response = $this->dbmodel->getExtemporalCode();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Читает данные рецептуры для справочника РЛС
	 */
	function loadExtemporal() {
		$data = $this->ProcessInputData('loadExtemporal', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadExtemporal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}