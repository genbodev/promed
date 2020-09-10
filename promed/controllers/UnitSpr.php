<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * UnitSpr - контроллер для работы со справочниками единиц измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			28.01.2014
 *
 * @property UnitSpr_model dbmodel
 */

class UnitSpr extends swController {

	public $inputRules = array(
		'load' => array(
			array(
				'field' => 'UnitSpr_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Unit_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'save' => array(
			array(
				'field' => 'UnitSpr_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Unit_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitType_id',
				'label' => 'Тип единицы измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitSpr_Code',
				'label' => 'Код',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'UnitSpr_Name',
				'label' => 'Наименование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Unit_begDate',
				'label' => 'Дата начала',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Unit_endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadUnitLinkObr' => array(
			array(
				'field' => 'UnitLink_id',
				'label' => 'Идентификатор связи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUnitLink' => array(
			array(
				'field' => 'UnitLink_id',
				'label' => 'Идентификатор связи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveUnitLink' => array(
			array(
				'field' => 'UnitLink_id',
				'label' => 'Идентификатор связи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Unit_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitLink_Fir',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitType_fid',
				'label' => 'Тип единицы измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitType_sid',
				'label' => 'Тип единицы измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitLink_UnitConv',
				'label' => 'Коэффициент пересчёта',
				'rules' => '',
				'type' => 'float'
			)
		),
		'loadUnitSprGrid' => array(
		),
		'loadUnitLinkGrid' => array(
			array(
				'field' => 'Okei_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Unit_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteUnit' => array(
			array(
				'field' => 'Unit_id',
				'label' => 'Единица измерения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteUnitLink' => array(
			array(
				'field' => 'UnitLink_id',
				'label' => 'Связанное значение',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteOkei' => array(
			array(
				'field' => 'Okei_id',
				'label' => 'Единица измерения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getOkeiConv' => array(
			array(
				'field' => 'Okei_fid',
				'label' => 'Единица измерения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Okei_sid',
				'label' => 'Единица измерения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkUnitSprEndDate' => array(
			array(
				'field' => 'Unit_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitSpr_endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'string'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('UnitSpr_model', 'dbmodel');
	}

	/**
	 * Получение списка единиц измерения
	 */
	function loadUnitSprGrid() {
		$data = $this->ProcessInputData('loadUnitSprGrid',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadUnitSprGrid($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка связанных значений
	 */
	function loadUnitLinkGrid() {
		$data = $this->ProcessInputData('loadUnitLinkGrid',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadUnitLinkGrid($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение данных формы редактирования единиц измерения
	 */
	function load() {
		$data = $this->ProcessInputData('load',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение данных формы редактирования связи единиц измерения
	 */
	function loadUnitLink() {
		$data = $this->ProcessInputData('loadUnitLink',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadUnitLink($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение данных формы редактирования связи единиц измерения
	 */
	function loadUnitLinkObr() {
		$data = $this->ProcessInputData('loadUnitLinkObr',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadUnitLinkObr($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение единицы измерения
	 */
	function save() {
		$data = $this->ProcessInputData('save',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Сохранение связи единицы измерения
	 */
	function saveUnitLink() {
		$data = $this->ProcessInputData('saveUnitLink',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveUnitLink($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Удаление единицы измерения
	 */
	function deleteUnit() {
		$data = $this->ProcessInputData('deleteUnit',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteUnit($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Удаление связанной единицы измерения
	 */
	function deleteUnitLink() {
		$data = $this->ProcessInputData('deleteUnitLink',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteUnitLink($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Удаление единицы измерения
	 */
	function deleteOkei() {
		$data = $this->ProcessInputData('deleteOkei',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteOkei($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение коэфициента для конвертации единици измерения
	 */
	function getOkeiConv() {
		$data = $this->ProcessInputData('getOkeiConv',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getOkeiConv($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 *  Проверка даты окончания единицы измерения
	 */
	function checkUnitSprEndDate() {
		$data = $this->ProcessInputData('checkUnitSprEndDate',true,true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->checkUnitSprEndDate($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
}