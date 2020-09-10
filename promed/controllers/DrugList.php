<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DrugList - контроллер для работы с перечнями медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.10.2017
 *
 * @property DrugList_model dbmodel
 */

class DrugList extends SwController{
	protected $inputRules = array(
		'loadDrugListGrid' => array(
			array(
				'field' => 'DrugListRange',
				'label' => 'Период дейстия перечня',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'DrugList_Name',
				'label' => 'Наименование перечня',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListType_id',
				'label' => 'Тип перечня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListObj_id',
				'label' => 'Издатель',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugList_Profile',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Склад',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'limit',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 1000
			),
		),
		'loadDrugListUsedGrid' => array(
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadDrugListStrGrid' => array(
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListStr_Name',
				'label' => 'Наименование медикамента перечня',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugListGroup_id',
				'label' => 'Идентификатор группы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ClsATC_id',
				'label' => 'Класс АТХ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'limit',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadDrugListObjList' => array(
			array(
				'field' => 'query',
				'label' => 'Строка запроса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugListObj_id',
				'label' => 'Идентификатор объекта, использующего перечни',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isPublisher',
				'label' => 'Издатель',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadDrugListForm' => array(
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadDrugListObjForm' => array(
			array(
				'field' => 'DrugListObj_id',
				'label' => 'Идентификатор объекта, использующего перечни',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadDrugListUsedForm' => array(
			array(
				'field' => 'DrugListUsed_id',
				'label' => 'Идентификатор использования перечня',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadDrugListStrForm' => array(
			array(
				'field' => 'DrugListStr_id',
				'label' => 'Идентификатор медикамента в перечне',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveDrugList' => array(
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugList_begDate',
				'label' => 'Дата начала действия перечня',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'DrugList_endDate',
				'label' => 'Дата окончания действия перечня',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'DrugList_Name',
				'label' => 'Наименование перечня',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugListType_id',
				'label' => 'Тип перечня',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DocNormative_id',
				'label' => 'Идентификатор нормативного документа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListObj_id',
				'label' => 'Идентификатор издателя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListObj_id',
				'label' => 'Идентификатор издателя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLCountry_id',
				'label' => 'Идентификатор страны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Region_id',
				'label' => 'Идентификатор региона',
				'rules' => '',
				'type' => 'id'
			),
		),
		'saveDrugListObj' => array(
			array(
				'field' => 'DrugListObj_id',
				'label' => 'Идентификатор объекта, использующего перечни',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListObj_Name',
				'label' => 'Наименование объекта, использующего перечни',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EmergencyTeamSpec_id',
				'label' => 'Идентификатор профиля бригады',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexTariff_id',
				'label' => 'Идентификатор тарифа услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			),
		),
		'saveDrugListUsed' => array(
			array(
				'field' => 'DrugListUsed_id',
				'label' => 'Идентификатор использования перечня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListObj_id',
				'label' => 'Идентификатор объекта, использующего перечни',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListObj_Name',
				'label' => 'Наименование объекта, использующего перечни',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EmergencyTeamSpec_id',
				'label' => 'Идентификатор профиля бригады',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexTariff_id',
				'label' => 'Идентификатор тарифа услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			),
		),
		'saveDrugListStr' => array(
			array(
				'field' => 'DrugListStr_id',
				'label' => 'Идентификатор медикамента в перечне',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListStr_Name',
				'label' => 'Наименованеи медикамента в перечне',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugListGroup_id',
				'label' => 'Идентификатор группы медикаментов в перечне',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Drug_id',
				'label' => 'Идентификатор ЛП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_id',
				'label' => 'Идентификатор комплексного МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Actmatters_id',
				'label' => 'Идентификатор МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugNonpropNames_id',
				'label' => 'Идентификатор непатентованного наименования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Tradenames_id',
				'label' => 'Идентификатор торгового наименования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Clsdrugforms_id',
				'label' => 'Идентификатор лекарственной формы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListStr_Comment',
				'label' => 'Примечание',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugListStr_Dose',
				'label' => 'Дозировка',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'GoodsUnit_did',
				'label' => 'Идентификатор единица измерения дозировки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugListStr_Num',
				'label' => 'Нормативное количество',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'GoodsUnit_nid',
				'label' => 'Идентификатор единица измерения нормативного количества',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deleteDrugList' => array(
			array(
				'field' => 'DrugList_id',
				'label' => 'Идентификатор перечня',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deleteDrugListUsed' => array(
			array(
				'field' => 'DrugListUsed_id',
				'label' => 'Идентификатор использования перечня',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deleteDrugListStr' => array(
			array(
				'field' => 'DrugListStr_id',
				'label' => 'Идентификатор медикамента в перечне',
				'rules' => 'required',
				'type' => 'id'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();

		$this->load->model('DrugList_model', 'dbmodel');
	}

	/**
	 * Получение списка перечнией медикаментов
	 * @return bool
	 */
	function loadDrugListGrid() {
		$data = $this->ProcessInputData('loadDrugListGrid');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка применения перечня медикаментов
	 * @return bool
	 */
	function loadDrugListUsedGrid() {
		$data = $this->ProcessInputData('loadDrugListUsedGrid');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListUsedGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка медикаментов перечня
	 * @return bool
	 */
	function loadDrugListStrGrid() {
		$data = $this->ProcessInputData('loadDrugListStrGrid');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListStrGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadDrugListObjList() {
		$data = $this->ProcessInputData('loadDrugListObjList');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListObjList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadDrugListForm() {
		$data = $this->ProcessInputData('loadDrugListForm');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListForm($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadDrugListObjForm() {
		$data = $this->ProcessInputData('loadDrugListObjForm');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListObjForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadDrugListUsedForm() {
		$data = $this->ProcessInputData('loadDrugListUsedForm');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListUsedForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadDrugListStrForm() {
		$data = $this->ProcessInputData('loadDrugListStrForm');
		if ($data === false) return false;

		$response = $this->dbmodel->loadDrugListStrForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение перечня медикаментов
	 * @return bool
	 */
	function saveDrugList() {
		$data = $this->ProcessInputData('saveDrugList');
		if ($data === false) return false;

		$response = $this->dbmodel->saveDrugList($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение объкта, использующего перечени медикаментов
	 * @return bool
	 */
	function saveDrugListObj() {
		$data = $this->ProcessInputData('saveDrugListObj');
		if ($data === false) return false;

		$response = $this->dbmodel->saveDrugListObjOrUsed($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function saveDrugListUsed() {
		$data = $this->ProcessInputData('saveDrugListUsed');
		if ($data === false) return false;

		$response = $this->dbmodel->saveDrugListObjOrUsed($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение медикамента в перечне
	 * @return bool
	 */
	function saveDrugListStr() {
		$data = $this->ProcessInputData('saveDrugListStr');
		if ($data === false) return false;

		$response = $this->dbmodel->saveDrugListStr($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function deleteDrugList() {
		$data = $this->ProcessInputData('deleteDrugList');
		if ($data === false) return false;

		$response = $this->dbmodel->deleteDrugList($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function deleteDrugListUsed() {
		$data = $this->ProcessInputData('deleteDrugListUsed');
		if ($data === false) return false;

		$response = $this->dbmodel->deleteDrugListUsed($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаления медикамента из перечня
	 * @return bool
	 */
	function deleteDrugListStr() {
		$data = $this->ProcessInputData('deleteDrugListStr');
		if ($data === false) return false;

		$response = $this->dbmodel->deleteDrugListStr($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}