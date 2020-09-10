<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionCVI - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property EvnDirectionCVI_model dbmodel
 */

class EvnDirectionCVI extends swController {
	protected $inputRules = [
		'load' => [
			[ 'field' => 'EvnDirectionCVI_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id' ],
		],
		'loadJournal' => [
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string' ], 
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string' ], 
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string' ], 
			[ 'field' => 'Person_BirthDay', 'label' => '', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Person_AgeFrom', 'label' => '', 'rules' => 'trim', 'type' => 'int' ],
			[ 'field' => 'Person_AgeTo', 'label' => '', 'rules' => 'trim', 'type' => 'int' ],
			[ 'field' => 'PersonBirthYearFrom', 'label' => '', 'rules' => 'trim', 'type' => 'int' ],
			[ 'field' => 'PersonBirthYearTo', 'label' => '', 'rules' => 'trim', 'type' => 'int' ],
			[ 'field' => 'EvnDirectionCVI_RegNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_Lab', 'label' => '', 'rules' => 'trim','type' => 'string' ],
			[ 'field' => 'Diag_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_setDate_Range', 'label' => '', 'rules' => '','type' => 'daterange' ],
			[ 'field' => 'MedPersonal_id', 'label' => 'Направивший врач', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_takeDate_Range', 'label' => '', 'rules' => '','type' => 'daterange' ],
			[ 'field' => 'MedPersonal_tid', 'label' => 'Сотрудник, отобравший биоматериал', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_sendDate_Range', 'label' => '', 'rules' => '','type' => 'daterange' ],
			[ 'field' => 'EvnDirectionCVI_Number', 'label' => 'Номер образца', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'start', 'default' => 0, 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'limit', 'default' => 50, 'label' => 'Количество записей', 'rules' => '', 'type' => 'int' ]
		],
		'save' => [
			[ 'field' => 'EvnDirectionCVI_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_pid', 'label' => '', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'PersonEvn_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_RegNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_Contact', 'label' => '', 'rules' => 'trim','type' => 'string' ],
			[ 'field' => 'EvnDirectionCVI_Lab', 'label' => '', 'rules' => 'trim','type' => 'string' ],
			[ 'field' => 'Diag_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_setDate', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'EvnDirectionCVI_takeDate', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'EvnDirectionCVI_takeTime', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'EvnDirectionCVI_sendDate', 'label' => '', 'rules' => '','type' => 'date' ],
			[ 'field' => 'EvnDirectionCVI_sendTime', 'label' => '', 'rules' => '','type' => 'string' ],
			[ 'field' => 'MedPersonal_id', 'label' => 'Направивший врач', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'MedStaffFact_id', 'label' => 'Место работы', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'MedPersonal_tid', 'label' => 'Сотрудник, отобравший биоматериал', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_IsCito', 'label' => '', 'rules' => '','type' => 'swcheckbox' ],
			[ 'field' => 'EvnDirectionCVI_isSmear', 'label' => '', 'rules' => '','type' => 'swcheckbox' ],
			[ 'field' => 'EvnDirectionCVI_SmearNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_SmearResult', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_isBlood', 'label' => '', 'rules' => '','type' => 'swcheckbox' ],
			[ 'field' => 'EvnDirectionCVI_BloodNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_BloodResult', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_isSputum', 'label' => '', 'rules' => '','type' => 'swcheckbox' ],
			[ 'field' => 'EvnDirectionCVI_SputumNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_SputumResult', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_isLavage', 'label' => '', 'rules' => '','type' => 'swcheckbox' ],
			[ 'field' => 'EvnDirectionCVI_LavageNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_LavageResult', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_isAspirate', 'label' => '', 'rules' => '','type' => 'swcheckbox' ],
			[ 'field' => 'EvnDirectionCVI_AspirateNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_AspirateResult', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_isAutopsy', 'label' => '', 'rules' => '','type' => 'swcheckbox' ],
			[ 'field' => 'EvnDirectionCVI_AutopsyNumber', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVI_AutopsyResult', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'CVIBiomaterial_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'CVISampleStatus_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVILink_PhonePersonal', 'label' => '', 'rules' => 'trim','type' => 'string' ],
			[ 'field' => 'CVIOrderType_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVILink_ReceiverMoID', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'CVIPurposeSurvey_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'CVIStatus_id', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVILink_IsSymptom', 'label' => '', 'rules' => '','type' => 'id' ],
			[ 'field' => 'EvnDirectionCVILink_Address', 'label' => '', 'rules' => 'trim','type' => 'string' ],
			[ 'field' => 'EvnDirectionCVILink_Phone', 'label' => '', 'rules' => 'trim','type' => 'string' ],
			[ 'field' => 'EvnDirectionCVILink_WorkPlace', 'label' => '', 'rules' => 'trim','type' => 'string' ]
		],
		'delete' => [
			[ 'field' => 'id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id' ],
		],
		'getPersonAddressPhone' => [
			[ 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ],
		]
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnDirectionCVI_model', 'dbmodel');
	}
	
	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка журнала
	 */
	function loadJournal() {
		$data = $this->ProcessInputData('loadJournal');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadJournal($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }
		
		$this->load->model('Common_model');
		$dateTime = $this->Common_model->getCurrentDateTime();
		$curDT = Datetime::createFromFormat("d.m.Y H:i:s", "{$dateTime['date']} {$dateTime['time']}");
		
		$takeDT = Datetime::createFromFormat("Y-m-d H:i", "{$data['EvnDirectionCVI_takeDate']} {$data['EvnDirectionCVI_takeTime']}");
		if ($takeDT && $takeDT > $curDT) {
			return $this->ReturnError('Дата и время взятия образца не может быть позже текущей даты и времени');
		}
		
		$sendDT = Datetime::createFromFormat("Y-m-d H:i", "{$data['EvnDirectionCVI_sendDate']} {$data['EvnDirectionCVI_sendTime']}");
		if ($sendDT && $sendDT > $curDT) {
			return $this->ReturnError('Дата и время отправки не может быть позже текущей даты и времени');
		}
		
		if ($takeDT && $sendDT && $takeDT > $sendDT) {
			return $this->ReturnError('Сохранение невозможно. Дата и время взятия образца не может быть позже, чем дата и время отправки образца в лабораторию');
		}

		$response = $this->dbmodel->save($data);
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

	/**
	 * Получение адреса/телефона
	 */
	function getPersonAddressPhone() {
		$data = $this->ProcessInputData('getPersonAddressPhone');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonAddressPhone($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}