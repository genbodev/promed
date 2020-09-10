<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FoodCook - контроллер для работы с рецептами блюд
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			01.10.2013
 */

class FoodCook extends swController {
	/**
	 * @var array
	 */
	protected  $inputRules = array(
		'loadFoodCookGrid' => array(
			array('field' => 'FoodCook_Code', 'label' => 'Код блюда', 'rules' => '', 'type' => 'string'),
			array('field' => 'FoodCook_Name', 'label' => 'Наименование блюда', 'rules' => '', 'type' => 'string'),
			array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'int')
		),
		'loadFoodCookSpecGrid' => array(
			array('field' => 'FoodCook_id', 'label' => 'Идентификатор блюда', 'rules' => 'required', 'type' => 'id')
		),
		'loadFoodCookEditForm' => array(
			array('field' => 'FoodCook_id', 'label' => 'Идентификатор рецепта блюда', 'rules' => 'required', 'type' => 'id')
		),
		'saveFoodCook' => array(
			array('field' => 'FoodCook_id', 'label' => 'Идентификатор блюда', 'rules' => '', 'type' => 'id'),
			array('field' => 'FoodCook_Code', 'label' => 'Код блюда', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'FoodCook_Name', 'label' => 'Наименование блюда', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'FoodCook_Descr', 'label' => 'Описание процесса приготовления', 'rules' => '', 'type' => 'string'),
			array('field' => 'FoodCook_DescrOrgan', 'label' => 'Описание органолептических свойств', 'rules' => '', 'type' => 'string'),
			array('field' => 'FoodCook_Caloric', 'label' => 'Энергетическая ценность', 'rules' => '', 'type' => 'float'),
			array('field' => 'FoodCook_Fat', 'label' => 'Содержание жиров', 'rules' => '', 'type' => 'float'),
			array('field' => 'FoodCook_Protein', 'label' => 'Содержание белков', 'rules' => '', 'type' => 'float'),
			array('field' => 'FoodCook_Carbohyd', 'label' => 'Содержание углеводов', 'rules' => '', 'type' => 'float'),
			array('field' => 'FoodCook_Time', 'label' => 'Время приготовления', 'rules' => '', 'type' => 'int'),
			array('field' => 'FoodCook_Mass', 'label' => 'Масса готового блюда', 'rules' => '', 'type' => 'float'),
			array('field' => 'Okei_id', 'label' => 'Ед. изм.', 'rules' => '', 'type' => 'id'),
			array('field' => 'FoodCookSpecData', 'label' => 'Ингредиенты', 'rules' => 'trim', 'type' => 'string')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('FoodCook_model', 'dbmodel');
	}

	/**
	 * Возвращает список рецептов блюд
	 * @return bool
	 */
	function loadFoodCookGrid()
	{
		$data = $this->ProcessInputData('loadFoodCookGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadFoodCookGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные о рецепте блюда для формы редактирования
	 * @return bool
	 */
	function loadFoodCookEditForm()
	{
		$data = $this->ProcessInputData('loadFoodCookEditForm',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadFoodCookEditForm($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение рецепта блюда
	 * @return bool
	 */
	function saveFoodCook()
	{
		$data = $this->ProcessInputData('saveFoodCook', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveFoodCook($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список ингредиентов блюда
	 * @return bool
	 */
	function loadFoodCookSpecGrid()
	{
		$data = $this->ProcessInputData('loadFoodCookSpecGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadFoodCookSpecGrid($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}