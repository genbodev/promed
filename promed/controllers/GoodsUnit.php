<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * GoodsUnit - контроллер для работы с единицами измерения товара
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.01.2016
 *
 * @property GoodsUnit_model dbmodel
 */

class GoodsUnit extends swController {
	protected  $inputRules = array(
		'loadGoodsUnitGrid' => array(
			array(
				'field' => 'GoodsUnit_Name',
				'label' => 'Наименование единицы измерения товара',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начало',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество',
				'rules' => '',
				'type' => 'int'
			),
		),
		'saveGoodsUnit' => array(
			array(
				'field' => 'GoodsUnit_id',
				'label' => 'Идентификатор единицы измерения товара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'GoodsUnit_Name',
				'label' => 'Наименование единицы измерения товара',
				'rules' => 'required|trim',
				'type' => 'string'
			),
			array(
				'field' => 'GoodsUnit_Nick',
				'label' => 'Краткое наименование единицы измерения товара',
				'rules' => 'required|trim',
				'type' => 'string'
			),
			array(
				'field' => 'GoodsUnit_Descr',
				'label' => 'Примечание об единице измерения товара',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Идентификатор ОКЕИ',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deleteGoodsUnit' => array(
			array(
				'field' => 'GoodsUnit_id',
				'label' => 'Идентификатор единицы измерения товара',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadGoodsUnitForm' => array(
			array(
				'field' => 'GoodsUnit_id',
				'label' => 'Идентификатор единицы измерения товара',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadGoodsUnitCombo' => array(
			array(
				'field' => 'where',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
		),
		'importGoodsUnitFromRls' => array(

		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('GoodsUnit_model', 'dbmodel');
	}

	/**
	 * Получение списка единиц измерения товара
	 */
	function loadGoodsUnitGrid() {
		$data = $this->ProcessInputData('loadGoodsUnitGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadGoodsUnitGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранения единицы измерения товара
	 */
	function saveGoodsUnit() {
		$data = $this->ProcessInputData('saveGoodsUnit', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveGoodsUnit($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаления единицы измерения товара
	 */
	function deleteGoodsUnit() {
		$data = $this->ProcessInputData('deleteGoodsUnit', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteGoodsUnit($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных единицы измерения товара для редактирования
	 */
	function loadGoodsUnitForm() {
		$data = $this->ProcessInputData('loadGoodsUnitForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadGoodsUnitForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Импорт единиц измерения товара из справочников РЛС
	 */
	function importGoodsUnitFromRls() {
		$data = $this->ProcessInputData('importGoodsUnitFromRls', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->importGoodsUnitFromRls($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

    /**
     * Получение данных единицы измерения товара для редактирования
     */
    function loadGoodsUnitCombo() {
    	$data = $this->ProcessInputData('loadGoodsUnitCombo', true);
        $response = $this->dbmodel->loadGoodsUnitCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }
}
