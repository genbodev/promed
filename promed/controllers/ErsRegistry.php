<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsRegistry - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property ErsRegistry_model dbmodel
 */

class ErsRegistry extends swController {
	protected  $inputRules = [
		'checkCanCreate' => [
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id' ],
		],
		'getNumber' => [
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'ErsRegistry_Month', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsRegistry_Year', 'label' => '', 'rules' => '','type' => 'string' ],
		],
		'loadJournal' => [
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'ERSRequestType_id', 'label' => 'Тип запроса', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ERSRequestStatus_id', 'label' => 'Статус запроса', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ErsRegistry_Date_Range', 'label' => 'Дата реестра', 'rules' => 'trim', 'type' => 'daterange'],
			[ 'field' => 'ERSStatus_id', 'label' => 'Статус реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ErsRegistry_Number', 'label' => 'Номер реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ErsBill_Date_Range', 'label' => 'Дата счета', 'rules' => 'trim', 'type' => 'daterange'],
			[ 'field' => 'ERSStatus_BillId', 'label' => 'Статус счета', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ErsBill_Number', 'label' => 'Номер счета', 'rules' => '', 'type' => 'id' ],
		],
		'loadTickets' => [
			[ 'field' => 'ErsRegistry_id', 'label' => 'Идентификатор талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'load' => [
			[ 'field' => 'ErsRegistry_id', 'label' => 'Идентификатор талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'delete' => [
			[ 'field' => 'ErsRegistry_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id' ],
		],
		'save' => [
			[ 'field' => 'ErsRegistry_id', 'label' => 'Идентификатор Талона', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'ErsRegistry_Number', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsRegistry_Date', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'ErsRegistry_Month', 'label' => '', 'rules' => '','type' => 'int' ],
			[ 'field' => 'ErsRegistry_Year', 'label' => '', 'rules' => '','type' => 'int' ],
			[ 'field' => 'ErsRegistry_TicketsCount', 'label' => '', 'rules' => '','type' => 'int' ],
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ErsRegistry_model', 'dbmodel');
	}
	
	/**
	 * Првоерка возможности создания
	 */
	function checkCanCreate() {
		$data = $this->ProcessInputData('checkCanCreate');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkCanCreate($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Получение номера реестра
	 */
	function getNumber() {
		$data = $this->ProcessInputData('getNumber');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getNumber($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Загрузка списка реестров
	 */
	function loadJournal() {
		$data = $this->ProcessInputData('loadJournal');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadJournal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка списка талонов в реестре
	 */
	function loadTickets() {
		$data = $this->ProcessInputData('loadTickets');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTickets($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка реестра
	 */
	function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение реестра
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Сохранение реестра
	 */
	function delete() {
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}