<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FoodStuff - контроллер для работы с продуктами питания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

class FoodStuff extends swController {

	/**
	 * @var array
	 */
	protected  $inputRules = array(
		'loadFoodStuffGrid' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuff_Code',
				'label' => 'Код продукта питания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'FoodStuff_Name',
				'label' => 'Наименование продукта питания',
				'rules' => '',
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
				'type' => 'id'
			)
		),
		'loadFoodStuffEditForm' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadFoodStuffPriceEditForm' => array(
			array(
				'field' => 'FoodStuffPrice_id',
				'label' => 'Идентификатор цены продукта питания',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadFoodStuffSubstitEditForm' => array(
			array(
				'field' => 'FoodStuffSubstit_id',
				'label' => 'Идентификатор заменителя продукта питания',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadFoodStuffMicronutrientEditForm' => array(
			array(
				'field' => 'FoodStuffMicronutrient_id',
				'label' => 'Идентификатор микронутриента продукта питания',
				'rules' => 'required',
				'type' => 'int'
			),
		),
		'loadFoodStuffCoeffEditForm' => array(
			array(
				'field' => 'FoodStuffCoeff_id',
				'label' => 'Идентификатор пересчетного коэффициента',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'saveFoodStuff' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuff_Code',
				'label' => 'Код продукта питания',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'FoodStuff_Name',
				'label' => 'Наименование продукта питания',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'FoodStuff_Descr',
				'label' => 'Описание продукта питания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'FoodStuff_StorCond',
				'label' => 'Описание условий хранения продукта питания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Идентификатор единици измерения ОКЕИ',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuff_Protein',
				'label' => 'Содержание протеинов в продукте питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuff_Fat',
				'label' => 'Содержание жиров в продукте питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuff_Carbohyd',
				'label' => 'Содержание углеводов в продукте питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuff_Caloric',
				'label' => 'Количество колорий в продукте питания',
				'rules' => '',
				'type' => 'int'
			),

		),
		'saveFoodStuffSubstit' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffSubstit_id',
				'label' => 'Идентификатор заменителя продукта питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuff_sid',
				'label' => 'Заменитель продукта питания',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffSubstit_Priority',
				'label' => 'Приоритет заменителя продукта питания',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffSubstit_Coeff',
				'label' => 'Коэффициент заменителя продукта питания',
				'rules' => 'required',
				'type' => 'float'
			)
		),
		'saveFoodStuffPrice' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffPrice_id',
				'label' => 'Идентификатор цены продукта питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffPrice_begDate',
				'label' => 'Дата начала действия цены продукта питания',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'FoodStuffPrice_Price',
				'label' => 'Цена продукта питания',
				'rules' => 'required',
				'type' => 'float'
			)
		),
		'saveFoodStuffMicronutrient' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffMicronutrient_id',
				'label' => 'Идентификатор микронутриента продукта питания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Micronutrient_id',
				'label' => 'Идентификатор микронутриента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffMicronutrient_Content',
				'label' => 'Содержание микронутриента в 100г. продукта',
				'rules' => 'required',
				'type' => 'float'
			),
		),
		'saveFoodStuffCoeff' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffCoeff_id',
				'label' => 'Идентификатор пересчетного коэффициента',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Идентификатор единицы измерения',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'FoodStuffCoeff_Coeff',
				'label' => 'Пересчетный коэффициент',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'FoodStuffCoeff_Descr',
				'label' => 'Описание пересчетного коэффициента',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadFoodStuffSubstitGrid' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadFoodStuffPriceGrid' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadFoodStuffMicronutrientGrid' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadFoodStuffCoeffGrid' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта питания',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadFoodStuffList' => array(
			array(
				'field' => 'FoodStuff_id',
				'label' => 'Идентификатор продукта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => '',
				'type' => 'string'
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
		$this->load->model('FoodStuff_model', 'dbmodel');
	}

	/**
	 * Возвращает список продуктов питания
	 * @return bool
	 */
	function loadFoodStuffGrid()
	{
		$data = $this->ProcessInputData('loadFoodStuffGrid',true);
		if ($data === false) {return false;}

		$food_stuff_data = $this->dbmodel->loadFoodStuffGrid($data);

		$this->ProcessModelMultiList($food_stuff_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные о продуктах питания для формы редактирования
	 * @return bool
	 */
	function loadFoodStuffEditForm()
	{
		$data = $this->ProcessInputData('loadFoodStuffEditForm',true);
		if ($data === false) {return false;}

		$food_stuff_data = $this->dbmodel->loadFoodStuffEditForm($data);

		$this->ProcessModelList($food_stuff_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные о цене продукта питания для формы редактирования
	 * @return bool
	 */
	function loadFoodStuffPriceEditForm()
	{
		$data = $this->ProcessInputData('loadFoodStuffPriceEditForm',true);
		if ($data === false) {return false;}

		$food_stuff_price_data = $this->dbmodel->loadFoodStuffPriceEditForm($data);

		$this->ProcessModelList($food_stuff_price_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные о заменителях продукта питания для формы редактирования
	 * @return bool
	 */
	function loadFoodStuffSubstitEditForm()
	{
		$data = $this->ProcessInputData('loadFoodStuffSubstitEditForm',true);
		if ($data === false) {return false;}

		$food_stuff_substit_data = $this->dbmodel->loadFoodStuffSubstitEditForm($data);

		$this->ProcessModelList($food_stuff_substit_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные о микронутриенте продукта питания для формы редактирования
	 * @return bool
	 */
	function loadFoodStuffMicronutrientEditForm()
	{
		$data = $this->ProcessInputData('loadFoodStuffMicronutrientEditForm',true);
		if ($data === false) {return false;}

		$food_stuff_micronutrient_data = $this->dbmodel->loadFoodStuffMicronutrientEditForm($data);

		$this->ProcessModelList($food_stuff_micronutrient_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные о пересчетном коэффициенте продукта питания для формы редактирования
	 * @return bool
	 */
	function loadFoodStuffCoeffEditForm()
	{
		$data = $this->ProcessInputData('loadFoodStuffCoeffEditForm',true);
		if ($data === false) {return false;}

		$food_stuff_coeff_data = $this->dbmodel->loadFoodStuffCoeffEditForm($data);

		$this->ProcessModelList($food_stuff_coeff_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение продукта питания
	 * @return bool
	 */
	function saveFoodStuff()
	{
		$data = $this->ProcessInputData('saveFoodStuff', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveFoodStuff($data);

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Сохранение заменителя продукта питания
	 * @return bool
	 */
	function saveFoodStuffSubstit()
	{
		$data = $this->ProcessInputData('saveFoodStuffSubstit', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveFoodStuffSubstit($data);

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Сохранение цены продукта питания
	 * @return bool
	 */
	function saveFoodStuffPrice()
	{
		$data = $this->ProcessInputData('saveFoodStuffPrice', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveFoodStuffPrice($data);

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Сохранение микронутриента продукта питания
	 * @return bool
	 */
	function saveFoodStuffMicronutrient()
	{
		$data = $this->ProcessInputData('saveFoodStuffMicronutrient', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveFoodStuffMicronutrient($data);

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Сохранение пересчетного коэффициента продукта питания
	 * @return bool
	 */
	function saveFoodStuffCoeff()
	{
		$data = $this->ProcessInputData('saveFoodStuffCoeff', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveFoodStuffCoeff($data);

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Возвращает список заменителей продукта питания
	 * @return bool
	 */
	function loadFoodStuffSubstitGrid()
	{
		$data = $this->ProcessInputData('loadFoodStuffSubstitGrid',true);
		if ($data === false) {return false;}

		$food_stuff_substit_data = $this->dbmodel->loadFoodStuffSubstitGrid($data);

		$this->ProcessModelList($food_stuff_substit_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список цен продукта питания
	 * @return bool
	 */
	function loadFoodStuffPriceGrid()
	{
		$data = $this->ProcessInputData('loadFoodStuffPriceGrid',true);
		if ($data === false) {return false;}

		$food_stuff_price_data = $this->dbmodel->loadFoodStuffPriceGrid($data);

		$this->ProcessModelList($food_stuff_price_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список микронутриентов продукта питания
	 * @return bool
	 */
	function loadFoodStuffMicronutrientGrid()
	{
		$data = $this->ProcessInputData('loadFoodStuffMicronutrientGrid',true);
		if ($data === false) {return false;}

		$food_stuff_micronutrient_data = $this->dbmodel->loadFoodStuffMicronutrientGrid($data);

		$this->ProcessModelList($food_stuff_micronutrient_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список пересчетных коэффициентов продукта питания
	 * @return bool
	 */
	function loadFoodStuffCoeffGrid()
	{
		$data = $this->ProcessInputData('loadFoodStuffCoeffGrid',true);
		if ($data === false) {return false;}

		$food_stuff_coeff_data = $this->dbmodel->loadFoodStuffCoeffGrid($data);

		$this->ProcessModelList($food_stuff_coeff_data,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список пересчетных коэффициентов продукта питания
	 * @return bool
	 */
	function loadFoodStuffList()
	{
		$data = $this->ProcessInputData('loadFoodStuffList',true);
		if ($data === false) {return false;}

		$food_stuff_data = $this->dbmodel->loadFoodStuffList($data);
		$this->ProcessModelList($food_stuff_data,true,true)->ReturnData();
		return true;
	}
}