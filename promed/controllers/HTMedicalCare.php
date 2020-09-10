<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * HTMedicalCare - контроллер для с работы с высокотехнологичной медицинской помощью
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Hospital
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.03.2014
 *
 * @property HTMedicalCare_model dbmodel
 */

class HTMedicalCare extends swController {
	protected  $inputRules = array(
		'loadHTMedicalCareClassList' => array(
			array(
				'field' => 'Diag_ids',
				'label' => 'Список идентификаторов диагноза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Идентификатор вида оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала случая',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания случая',
				'rules' => 'trim',
				'type' => 'date'
			)
		),
		'loadHTMedicalCareClassListByHTFinance' => array(
			array(
				'field' => 'HTMFinance_Code',
				'label' => 'Код источника финансирования',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			)
		),
		'loadHTMedicalCareClassListFed' => array(
			array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				)
		),
		'loadHTMedicalCareTypeListFed' => array(
			array(
					'field' => 'query',
					'label' => 'Строка поиска',
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
		$this->load->model('HTMedicalCare_model', 'dbmodel');
	}

	/**
	 * Возвращает список методов высокотехнологичной медицинской помоши
	 */
	function loadHTMedicalCareClassList()
	{
		$data = $this->ProcessInputData('loadHTMedicalCareClassList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadHTMedicalCareClassList($data);
		$this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список видов медицинской помощи по источнику финансирования и дате
	 */
	function loadHTMedicalCareClassListByHTFinance()
	{
		$data = $this->ProcessInputData('loadHTMedicalCareClassListByHTFinance', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadHTMedicalCareClassListByHTFinance($data);
		$this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список федеральных видов высокотехнологичной медицинской помоши
	 */
	function loadHTMedicalCareTypeListFed()
	{
		$data = $this->ProcessInputData('loadHTMedicalCareTypeListFed', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadHTMedicalCareTypeListFed($data);
		$this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	/**
	 * Возвращает список федеральных методов высокотехнологичной медицинской помоши
	 */
	function loadHTMedicalCareClassListFed()
	{
		$data = $this->ProcessInputData('loadHTMedicalCareClassListFed', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadHTMedicalCareClassListFed($data);
		$this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
}