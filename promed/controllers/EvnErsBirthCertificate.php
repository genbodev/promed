<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnERSBirthCertificate - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property EvnErsBirthCertificate_model dbmodel
 */

class EvnErsBirthCertificate extends swController {
	protected  $inputRules = [
		'checkLpu' => [
			[ 'field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id' ],
		],
		'checkLpuFSSContractType' => [
			[ 'field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'LpuFSSContractType_id', 'label' => 'Тип договора', 'rules' => 'required', 'type' => 'id' ],
		],
		'checkErsExists' => [
			[ 'field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id' ],
		],
		'loadPersonRegisterList' => [
			[ 'field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id' ],
		],
		'loadPersonData' => [
			[ 'field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Person_id', 'label' => 'Пациент', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id' ],
		],
		'doClose' => [
			[ 'field' => 'EvnERS_id', 'label' => 'Идентификатор ЭРС', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'ERSCloseCauseType_id', 'label' => 'Причина закрытия', 'rules' => 'required', 'type' => 'id' ],
		],
		'load' => [
			[ 'field' => 'EvnERSBirthCertificate_id', 'label' => 'Идентификатор ЭРС', 'rules' => 'required', 'type' => 'id' ],
		],
		'save' => [
			[ 'field' => 'EvnERSBirthCertificate_id', 'label' => 'Идентификатор ЭРС', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_OrgName', 'label' => '', 'rules' => 'trim|required', 'type' => 'string' ],
			[ 'field' => 'EvnERSBirthCertificate_OrgINN', 'label' => '', 'rules' => 'trim|required', 'type' => 'string' ],
			[ 'field' => 'EvnERSBirthCertificate_OrgOGRN', 'label' => '', 'rules' => 'trim|required', 'type' => 'string' ],
			[ 'field' => 'EvnERSBirthCertificate_OrgKPP', 'label' => '', 'rules' => 'trim|required', 'type' => 'string' ],
			[ 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'PersonEvn_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'LpuFSSContract_id', 'label' => 'Идентификатор договора', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_PolisNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_SnilsNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_DocNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnERSBirthCertificate_AddressNoReason', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'PersonRegister_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnErsBirthCertificate_PregnancyRegDate', 'label' => '', 'rules' => '','type' => 'date' ],
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnErsBirthCertificate_model', 'dbmodel');
	}
	
	/**
	 * Контроль наличия действующего договора с ФСС + Контроль наличия данных МО
	 */
	function checkLpu() {
		$data = $this->ProcessInputData('checkLpu');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpu($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Контроль наличия действующего договора с ФСС по определенному Виду услуг
	 */
	function checkLpuFSSContractType() {
		$data = $this->ProcessInputData('checkLpuFSSContractType');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpuFSSContractType($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Проверика наличия ЭРС у пациентки
	 */
	function checkErsExists() {
		$data = $this->ProcessInputData('checkErsExists');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkErsExists($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка карт беременной
	 */
	function loadPersonRegisterList() {
		$data = $this->ProcessInputData('loadPersonRegisterList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonRegisterList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка необходимых данных в режиме добавления
	 */
	function loadPersonData() {
		$data = $this->ProcessInputData('loadPersonData');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonData(false, $data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка  ЭРС
	 */
	function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение ЭРС
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Сохранение ЭРС
	 */
	function doClose() {
		$data = $this->ProcessInputData('doClose');
		if ($data === false) { return false; }

		$response = $this->dbmodel->doClose($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}