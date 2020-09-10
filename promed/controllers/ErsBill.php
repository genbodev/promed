<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsBill - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property ErsBill_model dbmodel
 */

class ErsBill extends swController {
	protected  $inputRules = [
		'getOrgRSchet' => [
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id' ],
		],
		'gerBillAmount' => [
			[ 'field' => 'ErsRegistry_id', 'label' => 'Идентификатор регистра', 'rules' => 'required', 'type' => 'id' ],
		],
		'sendToFss' => [
			[ 'field' => 'EvnERS_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERS_pid', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id' ],
		],
		'load' => [
			[ 'field' => 'ErsBill_id', 'label' => 'Идентификатор талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'delete' => [
			[ 'field' => 'ErsBill_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id' ],
		],
		'save' => [
			[ 'field' => 'ErsBill_id', 'label' => 'Идентификатор Талона', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ErsRegistry_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'Lpu_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'LpuFSSContract_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'ErsBill_Name', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsBill_Number', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsBill_Date', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'ErsBill_BankCheckingAcc', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsBill_BankName', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsBill_BankBIK', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsBill_BankCorrAcc', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'ErsBill_BillAmount', 'label' => '', 'rules' => '','type' => 'string' ]
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ErsBill_model', 'dbmodel');
	}

	/**
	 * Загрузка списка счетов МО
	 */
	function getOrgRSchet() {
		$data = $this->ProcessInputData('getOrgRSchet');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getOrgRSchet($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение суммы счета
	 */
	function gerBillAmount() {
		$data = $this->ProcessInputData('gerBillAmount');
		if ($data === false) { return false; }

		$response = $this->dbmodel->gerBillAmount($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Загрузка счета
	 */
	function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение счета
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Отправка счёта
	 */
	function sendToFss() {
		$data = $this->ProcessInputData('sendToFss');
		if ($data === false) { return false; }

		$response = $this->dbmodel->sendToFss($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Запрос результата
	 */
	function getFssResult() {
		$data = $this->ProcessInputData('sendToFss');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getFssResult($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}