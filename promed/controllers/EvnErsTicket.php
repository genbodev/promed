<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnERSTicket - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property EvnErsTicket_model dbmodel
 */

class EvnErsTicket extends swController {
	protected  $inputRules = [
		'getPersonNewborn' => [
			[ 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'BirthSpecStac_id', 'label' => 'Идентификатор специфики', 'rules' => 'required', 'type' => 'id' ],
		],
		'loadList' => [
			[ 'field' => 'EvnERSTicket_pid', 'label' => 'Идентификатор ЭРС', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'ERSRequestType_id', 'label' => 'Тип запроса', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ERSRequestStatus_id', 'label' => 'Статус запроса', 'rules' => '', 'type' => 'id' ],
		],
		'loadJournal' => [
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_Number', 'label' => 'Номер ЭРС', 'rules' => '', 'type' => 'int' ], 
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ], 
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ], 
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ], 
			[ 'field' => 'ERSStatus_id', 'label' => 'Статус талона', 'rules' => '', 'type' => 'id' ], 
			[ 'field' => 'EvnERSTicket_setDate_Range', 'label' => 'Дата формирования талона', 'rules' => '', 'type' => 'daterange' ], 
			[ 'field' => 'ERSRequestType_id', 'label' => 'Тип запроса', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ERSRequestStatus_id', 'label' => 'Статус запроса', 'rules' => '', 'type' => 'id' ],
				
		],
		'getErsChildInfo' => [
			[ 'field' => 'ERSTicketType_id', 'label' => '', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'EvnErsChild_pid', 'label' => 'Идентификатор талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'loadNewbornGrid' => [
			[ 'field' => 'EvnERSTicket_id', 'label' => 'Идентификатор талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'load' => [
			[ 'field' => 'EvnERSTicket_id', 'label' => 'Идентификатор талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'SendTicketsToFss' => [
			[ 'field' => 'ERSTicketType_id', 'label' => 'Тип талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'getFssResult' => [
			[ 'field' => 'EvnERSTicket_id', 'label' => 'Тип талона', 'rules' => 'required', 'type' => 'id' ],
		],
		'save' => [
			[ 'field' => 'EvnERSTicket_id', 'label' => 'Идентификатор Талона', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERSTicket_pid', 'label' => '', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'ERSTicketType_id', 'label' => '', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'PersonEvn_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERSTicket_PolisNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnERSTicket_SnilsNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnERSTicket_DocNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnERSTicket_AddressNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnErsTicket_PregnancyRegisterTime', 'label' => '', 'rules' => '','type' => 'int' ],
			[ 'field' => 'EvnErsTicket_PregnancyPutTime', 'label' => '', 'rules' => '','type' => 'int' ],
			[ 'field' => 'EvnErsTicket_IsMultiplePregnancy', 'label' => '', 'rules' => '','type' => 'int' ],
			[ 'field' => 'EvnErsTicket_StickNumber', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'EvnErsTicket_CardNumber', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'EvnErsTicket_CardDate', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'EvnERSTicket_ArrivalDT', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'EvnERSTicket_BirthDT', 'label' => '', 'rules' => '','type' => 'datetime' ],
			[ 'field' => 'EvnERSTicket_BirthDate', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'EvnERSTicket_BirthTime', 'label' => '', 'rules' => '','type' => 'time' ],
			[ 'field' => 'EvnERSTicket_DeathReason', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'EvnERSTicket_ChildrenCount', 'label' => '', 'rules' => '','type' => 'int' ],
			[ 'field' => 'NewbornGridData', 'label' => '', 'rules' => '','type' => 'json_array' ],
			[ 'field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => '', 'type' => 'id' ],
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnErsTicket_model', 'dbmodel');
	}
	
	/**
	 * Загрузка новорожденных и мертворожденных из специфики
	 */
	function getPersonNewborn() {
		$data = $this->ProcessInputData('getPersonNewborn');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonNewborn($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка списка детей
	 */
	function getErsChildInfo() {
		$data = $this->ProcessInputData('getErsChildInfo');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getErsChildInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка новорожденных
	 */
	function loadNewbornGrid() {
		$data = $this->ProcessInputData('loadNewbornGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNewbornGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка журнала талонов
	 */
	function loadJournal() {
		$data = $this->ProcessInputData('loadJournal');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadJournal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка талонов
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка талонов
	 */
	function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение талонов
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$data['EvnERSTicket_BirthDT'] = $data['EvnERSTicket_BirthDate'] . ' ' . $data['EvnERSTicket_BirthTime'];

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Отправка талонов
	 */
	function SendTicketsToFss() {
		$data = $this->ProcessInputData('SendTicketsToFss');
		if ($data === false) { return false; }

		$response = $this->dbmodel->SendTicketsToFss($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Запрос результата
	 */
	function getFssResult() {
		$data = $this->ProcessInputData('getFssResult');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getFssResult($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}