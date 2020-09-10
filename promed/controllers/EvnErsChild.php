<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnErsChild - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property EvnErsChild_model dbmodel
 */

class EvnErsChild extends swController {
	protected  $inputRules = [
		'loadJournal' => [
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ERSRequestType_id', 'label' => 'Тип запроса', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ERSRequestStatus_id', 'label' => 'Статус запроса', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_Number', 'label' => 'Номер ЭРС', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'ERSStatus_id', 'label' => 'Статус ЭРС', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_CreateDate_Range', 'label' => 'Дата выдачи ЭРС', 'rules' => '', 'type' => 'daterange' ],
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия матери', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_FirName', 'label' => 'Имя матери', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_SecName', 'label' => 'Отчество матери', 'rules' => '', 'type' => 'string' ], 
			[ 'field' => 'PersonChild_SurName', 'label' => 'Фамилия ребенка', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'PersonChild_FirName', 'label' => 'Имя ребенка', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'PersonChild_SecName', 'label' => 'Отчество ребенка', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'ERSStatus_ChildId', 'label' => 'Статус учета детей', 'rules' => '', 'type' => 'id' ],
		],
		'loadChildGrid' => [
			[
				'field' => 'EvnErsChild_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			],
		],
		'load' => [
			[
				'field' => 'EvnErsChild_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			],
		],
		'sendToFss' => [
			[ 'field' => 'EvnERS_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id' ],
		],
		'delete' => [
			[ 'field' => 'EvnErsChild_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id' ],
		],
		'save' => [
			[ 'field' => 'EvnErsChild_id', 'label' => 'Идентификатор Талона', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnErsChild_pid', 'label' => '', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'PersonEvn_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'LpuFSSContract_id', 'label' => 'Идентификатор договора', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'EvnErsChild_PolisNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnErsChild_SnilsNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnErsChild_DocNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnErsChild_AddressNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'ChildGridData', 'label' => '', 'rules' => '','type' => 'json_array' ],
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnErsChild_model', 'dbmodel');
	}
	
	/**
	 * Загрузка списка детей
	 */
	function loadChildGrid() {
		$data = $this->ProcessInputData('loadChildGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadChildGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка журнала учёта детей
	 */
	function loadJournal() {
		$data = $this->ProcessInputData('loadJournal');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadJournal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка постановки на учёт
	 */
	function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение постановки на учёт
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Отправка в ФСС
	 */
	function sendToFss() {
		$data = $this->ProcessInputData('sendToFss');
		if ($data === false) { return false; }

		$response = $this->dbmodel->sendToFss($data);
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