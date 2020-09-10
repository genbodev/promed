<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Attribute - контроллер для работы с атрибутами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.07.2014
 *
 * @property Attribute_model dbmodel
 */

class Attribute extends swController {
	protected  $inputRules = array(
		'loadAttributeGrid' => array(
			array(
				'field' => 'Attribute_Name',
				'label' => 'Наименование атрибута',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'isClose',
				'label' => 'Закрытые',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Attribute_begDate_From',
				'label' => 'Дата начала от',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Attribute_begDate_To',
				'label' => 'Дата начала до',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Attribute_endDate_From',
				'label' => 'Дата окончания от',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Attribute_endDate_To',
				'label' => 'Дата окончания до',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Attribute_SysNick',
				'label' => 'Системное наименование атрибута',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeValueType_id',
				'label' => 'Идентификатор типа атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Attribute_Code',
				'label' => 'Код атрибута',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Attribute_isKeyValue',
				'label' => 'Является значением',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 0
			),
			array(
				'field' => 'limit',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 100
			),
		),
		'loadAttributeVisionGrid' => array(
			array(
				'field' => 'Attribute_Name',
				'label' => 'Наименование атрибута',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeVision_TableName',
				'label' => 'Объект БД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeVision_TablePKey',
				'label' => 'Ключ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Region_id',
				'label' => 'Идентификатор региона',
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
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 0
			),
			array(
				'field' => 'limit',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 100
			),
		),
		'loadAttributeSignValueGrid' => array(
			array(
				'field' => 'AttributeSign_TableName',
				'label' => 'Наименовнаие таблицы, связанной с признаком атрибута',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeSignValue_TablePKey',
				'label' => 'Идентификатор справочника, для которого указывается признак',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'formMode',
				'label' => 'Режим формы',
				'rules' => '',
				'type' => 'string',
				'default' => 'remote'
			),
		),
		'deleteAttribute' => array(
			array(
				'field' => 'Attribute_id',
				'label' => 'Идентификатор атрибута',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteAttributeVision' => array(
			array(
				'field' => 'AttributeVision_id',
				'label' => 'Идентификатор атрибута',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteAttributeSignValue' => array(
			array(
				'field' => 'AttributeSignValue_id',
				'label' => 'Идентификатор признака значениев атрибутов',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadAttributeForm' => array(
			array(
				'field' => 'Attribute_id',
				'label' => 'Идентификатор атрибута',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadAttributeVisionForm' => array(
			array(
				'field' => 'AttributeVision_id',
				'label' => 'Идентификатор области видимости атрибута',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadAttributeList' => array(
			array(
				'field' => 'Attribute_id',
				'label' => 'Идентификатор атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeSign_id',
				'label' => 'Идентификатор признака атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadAttributeSignList' => array(
			array(
				'field' => 'AttributeSign_id',
				'label' => 'Идентификатор атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeSign_TableName',
				'label' => 'Объект БД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeSignValue_TablePKey',
				'label' => 'Идентфикатор объекта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_Code',
				'label' => 'Код услуги',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveAttribute' => array(
			array(
				'field' => 'Attribute_id',
				'label' => 'Идентификатор атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Attribute_Code',
				'label' => 'Код атрибута',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Attribute_Name',
				'label' => 'Наименование атрибута',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Attribute_SysNick',
				'label' => 'Системное наименование атрибута',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeValueType_id',
				'label' => 'Идентификатор типа атрибута',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Attribute_begDate',
				'label' => 'Дата начала действия атрибута',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Attribute_endDate',
				'label' => 'Дата окончания действия атрибута',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Attribute_TableName',
				'label' => 'Справочник',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Attribute_TablePKey',
				'label' => 'Идентификатор базового справочника',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveAttributeVision' => array(
			array(
				'field' => 'AttributeVision_id',
				'label' => 'Идентификатор области видимости атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Attribute_id',
				'label' => 'Идентификатор атрибута',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Region_id',
				'label' => 'Идентификатор региона',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeVision_TableName',
				'label' => 'Объект БД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeVision_Sort',
				'label' => 'Сортировка',
				'rules' => 'required',
				'type' => 'int',
				'minValue' => '0',
				'maxValue' => '1000'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeVision_AppCode',
				'label' => 'Код условия',
				'rules' => 'max_length[4000]',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeVision_begDate',
				'label' => 'Дата начала действия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'AttributeVision_endDate',
				'label' => 'Дата окончания действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'AttributeVision_IsKeyValue',
				'label' => 'Является значением',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'AttributeVision_TablePKey',
				'label' => 'Значение в таблице',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeSign_id',
				'label' => 'Идентификатор признака',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveAttributeSignValue' => array(
			array(
				'field' => 'AttributeValue_id',
				'label' => 'Идентификатор значения атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeSignValue_id',
				'label' => 'Идентификатор значения атрибута',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeSignValue_TablePKey',
				'label' => 'Идентификатор справочника',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeSign_TableName',
				'label' => 'Наименовнаие таблицы, связанной с признаком атрибута',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'AttributeSign_id',
				'label' => 'Идентификатор признака атрибута',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AttributeSignValue_begDate',
				'label' => 'Дата начала действия значения признака для атрибута',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'AttributeSignValue_endDate',
				'label' => 'Дата окончания действия значения признака для атрибута',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'AttributeValueSaveParams',
				'label' => 'Значения атрибутов',
				'rules' => 'required',
				'type' => 'string'
			),
		),
		'loadAttributeSignValueForm' => array(
			array(
				'field' => 'AttributeSignValue_id',
				'label' => 'Идентификатор значние атрибута',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getAttributesForObject' => array(
			array(
				'field' => 'object',
				'label' => 'Объект',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getAttributesBySign' => array(
			array(
				'field' => 'AttributeSign_id',
				'label' => 'Идентификатор признака атрибута',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadAttributeTestForm' => array(
			array(
				'field' => 'attrObjects',
				'label' => 'Объекты',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveAttributeTestForm' => array(
			array(
				'field' => 'Tes_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Attribute_model', 'dbmodel');
	}

	/**
	 * Возвращает список атрибутов
	 * @return bool
	 */
	function loadAttributeGrid()
	{
		$data = $this->ProcessInputData('loadAttributeGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список атрибутов
	 * @return bool
	 */
	function loadAttributeList()
	{
		$data = $this->ProcessInputData('loadAttributeList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список признаков атрибутов
	 * @return bool
	 */
	function loadAttributeSignList()
	{
		$data = $this->ProcessInputData('loadAttributeSignList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeSignList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка значений атрибутов
	 */
	function loadAttributeSignValueGrid() {
		$data = $this->ProcessInputData('loadAttributeSignValueGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeSignValueGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список атрибутов
	 * @return bool
	 */
	function loadAttributeVisionGrid()
	{
		$data = $this->ProcessInputData('loadAttributeVisionGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeVisionGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные атрибута для редактирования
	 * @return bool
	 */
	function loadAttributeForm()
	{
		$data = $this->ProcessInputData('loadAttributeForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление атрибута
	 */
	function deleteAttribute()
	{
		$data = $this->ProcessInputData('deleteAttribute');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteAttribute($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении атрибута')->ReturnData();
		return true;
	}

	/**
	 * Удаление атрибута
	 */
	function deleteAttributeVision()
	{
		$data = $this->ProcessInputData('deleteAttributeVision');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteAttributeVision($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении атрибута')->ReturnData();
		return true;
	}

	/**
	 * Удаление значения атрибута с признаком
	 */
	function deleteAttributeSignValue()
	{
		$data = $this->ProcessInputData('deleteAttributeSignValue');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteAttributeSignValue($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении атрибута')->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные области видимости атрибута для редактирования
	 * @return bool
	 */
	function loadAttributeVisionForm()
	{
		$data = $this->ProcessInputData('loadAttributeVisionForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeVisionForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение атрибута
	 * @return bool
	 */
	function saveAttribute()
	{
		$data = $this->ProcessInputData('saveAttribute');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveAttribute($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение области видимости атрибута
	 */
	function saveAttributeVision()
	{
		$data = $this->ProcessInputData('saveAttributeVision');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveAttributeVision($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение значения атрибута по признаку
	 */
	function saveAttributeSignValue() {
		$data = $this->ProcessInputData('saveAttributeSignValue');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveAttributeSignValue($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для редатирования значения атрибута по признаку
	 */
	function loadAttributeSignValueForm() {
		$data = $this->ProcessInputData('loadAttributeSignValueForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAttributeSignValueForm($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Получение атрибутов для объекта
	 */
	function getAttributesForObject()
	{
		$data = $this->ProcessInputData('getAttributesForObject');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getAttributesForObject($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получения данных для создания полей атрибутов по признаку
	 */
	function getAttributesBySign() {
		$data = $this->ProcessInputData('getAttributesBySign', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getAttributesBySign($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/*
	function loadAttributeTestForm() {
		$data = $this->ProcessInputData('loadAttributeTestForm', false);
		if ($data === false) { return false; }

		//$response = $this->dbmodel->loadAttributeTestForm($data);
		$response = array(array('Test_id' => 1, 'Test_Name' => 'тестовая запись'));

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	*/

	/*
	function saveAttributeTestForm() {
		$data = $this->ProcessInputData('saveAttributeTestForm');
		if ($data === false) { return false; }

		//$response = $this->dbmodel->loadAttributeTestForm($data);
		$response = array(array('Test_id' => 1, 'Error_Msg' => ''));

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	*/
}