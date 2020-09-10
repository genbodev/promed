<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonDopDispPlan - контроллер для работы с планом диспансеризации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property PersonDopDispPlan_model dbmodel
 */

class PersonDopDispPlan extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPersonDopDispPlanExportList' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDopDispPlan_ids',
				'label' => 'Идентификаторы планов',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'PersonDopDispPlanExport_expDateRange',
				'label' => 'Дата постановки на учет',
				'rules' => '',
				'type' => 'daterange'
			)
		),
		'importPersonDopDispPlan' => array(
			array(
				'field' => 'File',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadExportErrorPlanDDList' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PersonDopDispPlanExport_id',
				'label' => 'Идентификатор файла экспорта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadList' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PersonDopDispPlan_Year',
				'label' => 'год',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDopDispPlanExport_expDateRange',
				'label' => 'Дата постановки на учет',
				'rules' => '',
				'type' => 'daterange'
			)
		),
		'load' => array(
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getDispCheckPeriod' => array(
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDopDispPlan_ids',
				'label' => 'Идентификаторы планов',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispCheckPeriod_id',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isForTransfer',
				'label' => 'Для переноса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isForRetryInclude',
				'label' => 'Для повторного включения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'save' => array(
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор типа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DispCheckPeriod_id',
				'label' => 'Идентификатор периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDopDispPlan_Year',
				'label' => 'Год периода',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPlanPersonList' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonAge_Max',
				'label' => 'Максимальный возраст человека',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonAge_Min',
				'label' => 'Минимальный возраст человека',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Fact_id',
				'label' => 'Факт прохождения диспансеризации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PlanPersonListStatusType_id',
				'label' => 'Статус записи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PacketNumber',
				'label' => 'Номер пакета',
				'rules' => '',
				'type' => 'int'
			)
		),
		'savePlanPersonList' => array(
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_ids',
				'label' => 'Идентификатор плана',
				'rules' => 'required',
				'type' => 'json_array'
			),
		),
		'deletePlanPersonList' => array(
			array(
				'field' => 'PlanPersonList_ids',
				'label' => 'Люди',
				'rules' => 'required',
				'type' => 'json_array'
			),
		),
		'transferPlanPersonList' => array(
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана на который переносим',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PlanPersonList_ids',
				'label' => 'Люди',
				'rules' => 'required',
				'type' => 'json_array'
			),
			array(
				'field' => 'ignore_period_check',
				'label' => 'Проврека по периоду',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deletePersonDopDispPlanExport' => array(
			array(
				'field' => 'PersonDopDispPlanExport_id',
				'label' => 'Идентификатор файла экспорта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreMultiplePlans',
				'label' => 'Признак игнорирования наличия нескольких планов',
				'rules' => '',
				'type' => 'int'
			)
		),
		'retryIncludePlanPersonList' => array(
			array(
				'field' => 'PersonDopDispPlan_id',
				'label' => 'Идентификатор плана на который переносим',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PlanPersonList_ids',
				'label' => 'Люди',
				'rules' => 'required',
				'type' => 'json_array'
			),
			array(
				'field' => 'ignore_period_check',
				'label' => 'Проврека по периоду',
				'rules' => '',
				'type' => 'id'
			),
		),
		'getPersonDopDispPlanExportPackNum' => array(
			array(
				'field' => 'PersonDopDispPlanExport_Year',
				'label' => 'Год',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'exportPersonDopDispPlan' => array(
			array(
				'field' => 'PersonDopDispPlan_ids',
				'label' => 'Список планов для экспорта',
				'rules' => 'required',
				'type' => 'json_array'
			),
			array(
				'field' => 'DispCheckPeriod_id',
				'label' => 'Период плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSMO_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PacketNumber',
				'label' => 'Номер пакета',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Вид диспансеризации',
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
		$this->load->model('PersonDopDispPlan_model', 'dbmodel');
	}

	/**
	 * Получение номера пакета для экспорта
	 */
	function getPersonDopDispPlanExportPackNum() {
		$data = $this->ProcessInputData('getPersonDopDispPlanExportPackNum');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonDopDispPlanExportPackNum($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении номера пакета')->ReturnData();

		return true;
	}

	/**
	 * Удаление плана
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении примечания')->ReturnData();
		return true;
	}

	/**
	 * Импорт данных плана
	 */
	function importPersonDopDispPlan()
	{
		$data = $this->ProcessInputData('importPersonDopDispPlan');
		if ($data === false) { return false; }

		$response = $this->dbmodel->importPersonDopDispPlan($data);
		$this->ProcessModelSave($response, true, 'Ошибка при импорте')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список планов
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список экспортов планов
	 */
	function loadPersonDopDispPlanExportList()
	{
		$data = $this->ProcessInputData('loadPersonDopDispPlanExportList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonDopDispPlanExportList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список ошибок экспортов планов
	 */
	function loadExportErrorPlanDDList()
	{
		$data = $this->ProcessInputData('loadExportErrorPlanDDList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadExportErrorPlanDDList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает план
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список периодов
	 */
	function getDispCheckPeriod()
	{
		$data = $this->ProcessInputData('getDispCheckPeriod');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDispCheckPeriod($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение плана
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список людей в плане
	 */
	function loadPlanPersonList()
	{
		$data = $this->ProcessInputData('loadPlanPersonList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPlanPersonList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Добавляет людей в план
	 */
	function savePlanPersonList()
	{
		$data = $this->ProcessInputData('savePlanPersonList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePlanPersonList($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаляет людей из плана
	 */
	function deletePlanPersonList()
	{
		$data = $this->ProcessInputData('deletePlanPersonList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePlanPersonLists($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Перенос
	 */
	function transferPlanPersonList()
	{
		$data = $this->ProcessInputData('transferPlanPersonList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->transferPlanPersonList($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление файла экспорта
	 */
	function deletePersonDopDispPlanExport()
	{
		$data = $this->ProcessInputData('deletePersonDopDispPlanExport');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePersonDopDispPlanExport($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления файла экспорта')->ReturnData();
		return true;
	}

	/**
	 * Повторное включение
	 */
	function retryIncludePlanPersonList()
	{
		$data = $this->ProcessInputData('retryIncludePlanPersonList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->retryIncludePlanPersonList($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Экспорт планов
	 */
	function exportPersonDopDispPlan()
	{
		$data = $this->ProcessInputData('exportPersonDopDispPlan');
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportPersonDopDispPlan($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}