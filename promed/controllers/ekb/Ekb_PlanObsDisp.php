<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PlanObsDisp - контроллер для работы с планами контрольных посещений в рамках диспансерного наблюдения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 *
 * @property PlanObsDisp_model dbmodel
 */

class Ekb_PlanObsDisp extends swController {
	protected  $inputRules = array(
		'getDispCheckPeriod' => array(//для комбика периодов
			array(
				'field' => 'Year',
				'label' => 'Год',
				'rules' => 'required',
				'type' => 'int'
			),
		),
		'getPlanObsDispExportPackNum' => array(
			array(
				'field' => 'Export_Year',
				'label' => 'Год',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Export_Month',
				'label' => 'Месяц',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			)
		),
		//методы формы "План КП..."
		'makePlan' => array(
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => '',//создаем если пуст
				'type' => 'id'
			),
			array(
				'field' => 'DispCheckPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DispCheckPeriod_begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'DispCheckPeriod_endDate',
				'label' => 'Конец периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'TFOMSWorkDirection_id',
				'label' => 'Направление работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPlan' => array( //для грида - список карт в плане
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'Фамилия',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PlanPersonListStatusType_id',
				'label' => 'Статус записи',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadPlanErrorData'=>array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'Фамилия',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deletePlanLink'=>array(
			array(
				'field' => 'PlanObsDispLink_id',
				'label' => 'Идентификатор записи плана',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setPlanObsDispLinkStatus'=>array(
			array(
				'field' => 'PersonDisp_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PlanObsDispLink_id',
				'label' => 'Идентификатор записи плана',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PlanPersonListStatusType_id',
				'label' => 'Статус записи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//методы формы "Планы КП..."
		'importPlanObsDisp' => array(
			array(
				'field' => 'File',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		),
		'exportPlanObsDisp' => array(
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DispCheckPeriod_id',
				'label' => 'Идентификатор периода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PacketNumber',
				'label' => 'Номер пакета',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadPlans' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PlanObsDisp_Year',
				'label' => 'Год',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PlanObsDispExport_expDateRange',
				'label' => 'Период экспорта',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'TFOMSWorkDirection_id',
				'label' => 'Направление работы',
				'rules' => '',
				'type' => 'int'
			)
		),
		'deletePlan' => array(
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadExportErrorPlanList' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PersonDopDispPlanExport_id',
				'label' => 'Идентификатор файла экспорта',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPlanObsDispExportList' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PlanObsDisp_id',
				'label' => 'Идентификатор плана',
				'rules' => '',
				'type' => 'id'
			)
		),
		
	);
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('PlanObsDisp_model', 'dbmodel');
	}
	
	/**
	 * Получение номера пакета для экспорта
	 */
	function getPlanObsDispExportPackNum() {
		$data = $this->ProcessInputData('getPlanObsDispExportPackNum');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPlanObsDispExportPackNum($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении номера пакета')->ReturnData();

		return true;
	}
	
	/**
	 * Импорт данных плана
	 */
	function importPlanObsDisp()
	{
		$data = $this->ProcessInputData('importPlanObsDisp');
		if ($data === false) { return false; }

		$response = $this->dbmodel->importPlanObsDisp($data);
		$this->ProcessModelSave($response, true, 'Ошибка при импорте')->ReturnData();
		return true;
	}
	
	/**
	 * Экспорт
	 */
	function exportPlanObsDisp() {
		$data = $this->ProcessInputData('exportPlanObsDisp');
		if ($data === false) { return false; }
		$response = $this->dbmodel->exportPlanObsDisp($data);
		
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 * Получить периоды за указанный год для комбо "период"
	 */
	function getDispCheckPeriod() {
		$data = $this->ProcessInputData('getDispCheckPeriod');
		if ($data === false) { return false; }
		$response = $this->dbmodel->getDispCheckPeriod($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получить значения для комбо "направление работы"
	 */
	function getWorkDirectionSpr() {
		$response = $this->dbmodel->getWorkDirectionSpr();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Сохранить/сформировать план
	 */
	function makePlan() {
		$data = $this->ProcessInputData('makePlan');
		if ($data === false) { return false; }		
		$response = $this->dbmodel->makePlan($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
		
	/**
	 * Список планов. Данные для грида
	 */
	function loadPlans() {
		$data = $this->ProcessInputData('loadPlans');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPlans($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Удалить план
	 */
	function deletePlan() {
		$data = $this->ProcessInputData('deletePlan');
		if ($data === false) { return false; }
		$response = $this->dbmodel->deletePlan($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении плана')->ReturnData();
		return true;
	}
	
	/**
	 * Список сущностей плана КП
	 */
	function loadPlanPersonList() {
		$data = $this->ProcessInputData('loadPlanPersonList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPlanPersonList($data);
		//$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Список ошибок
	 */
	function loadExportErrorPlanList() {
		$data = $this->ProcessInputData('loadExportErrorPlanList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadExportErrorPlanList($data);
		if($response!==false)
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Список файлов экспорта
	 */
	function loadPlanObsDispExportList() {
		$data = $this->ProcessInputData('loadPlanObsDispExportList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPlanObsDispExportList($data);
		
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	

	/**
	 * Данные для грида. Список карт ДН в плане
	 */
	function loadPlan() {
		$data = $this->ProcessInputData('loadPlan');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPlan($data);
		
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Данные для грида ошибочных данных плана. 
	 */
	function loadPlanErrorData() {
		$data = $this->ProcessInputData('loadPlanErrorData');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPlanErrorData($data);
		
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Удалить запись плана
	 */
	function deletePlanLink() {
		$data = $this->ProcessInputData('deletePlanLink');
		if ($data === false) { return false; }
		$response = $this->dbmodel->deletePlanLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении записи плана')->ReturnData();
		return true;
	}
	
	/**
	 * Установить статус плана
	 */
	function setPlanObsDispLinkStatus() {
		$data = $this->ProcessInputData('setPlanObsDispLinkStatus');
		if ($data === false) { return false; }
		$response = $this->dbmodel->setPlanObsDispLinkStatus($data);
		
		if($response===true) $this->ReturnData(array('success' => true, 'Error_Msg' => ''));
		else $this->ReturnData(array('success' => false, 'Error_Msg' => 'Произошла ошибка при смене статуса записи плана.'));
		return true;
	}
}