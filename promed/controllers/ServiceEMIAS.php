<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceEMIAS - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property ServiceEMIAS_model dbmodel
 */

class ServiceEMIAS extends swController {
	public $inputRules = array(
		'getPatientId' => array(
			array(
				'field' => 'Surname',
				'label' => 'Фамилия',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Name',
				'label' => 'Имя',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Patronymic',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BirthDate',
				'label' => 'Дата рождения пациента',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'SNILS',
				'label' => 'СНИЛС пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PolisNumber',
				'label' => 'Номер полиса ОМС',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Address',
				'label' => 'Адрес',
				'rules' => '',
				'type' => 'string'
			)
		),
		'AddPatient' => array(
			array(
				'field' => 'Surname',
				'label' => 'Фамилия',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Name',
				'label' => 'Имя',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Patronymic',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'SNILS',
				'label' => 'СНИЛС пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Birthday',
				'label' => 'Дата рождения пациента',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Address',
				'label' => 'Адрес',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Resident',
				'label' => 'Иногородний пациент',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Sex',
				'label' => 'Пол пациента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'AreaId',
				'label' => 'Регион пациента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Polis',
				'label' => 'Сведения о полисе ОМС',
				'rules' => '',
				'type' => 'assoc_array'
			),
			array(
				'field' => 'IdentityDocument',
				'label' => 'Сведения о документе удостоверяющим личность',
				'rules' => '',
				'type' => 'assoc_array',
			),
			array(
				'field' => 'Clinic',
				'label' => 'Данные о медицинской организации',
				'rules' => '',
				'type' => 'assoc_array'
			),
		),
		'UpdatePatient' => array(
			array(
				'field' => 'PatientId',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Surname',
				'label' => 'Фамилия',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Name',
				'label' => 'Имя',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Patronymic',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'SNILS',
				'label' => 'СНИЛС пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Birthday',
				'label' => 'Дата рождения пациента',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Address',
				'label' => 'Адрес',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Resident',
				'label' => 'Иногородний пациент',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Sex',
				'label' => 'Пол пациента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'AreaId',
				'label' => 'Регион пациента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Polis',
				'label' => 'Сведения о полисе ОМС',
				'rules' => '',
				'type' => 'assoc_array'
			),
			array(
				'field' => 'IdentityDocument',
				'label' => 'Сведения о документе удостоверяющим личность',
				'rules' => '',
				'type' => 'assoc_array',
			),
			array(
				'field' => 'Clinic',
				'label' => 'Данные о медицинской организации',
				'rules' => '',
				'type' => 'assoc_array'
			),
		),
		'syncAll' => [
			[
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			]
		]
	);
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		
		$this->load->model('ServiceEMIAS_model', 'dbmodel');
	}
	
	/**
	 * Запуск импорта данных из ЕФИС
	 */
	function GetPatientId() {
		$data = $this->ProcessInputData('getPatientId');
		if ($data === false) return false;
		
		//подготовка нужных данных
		$preparedData = array();
		$preparedData['Surname'] = $data['Surname'];
		$preparedData['Name'] = $data['Name'];
		$preparedData['Patronymic'] = $data['Patronymic'];
		$preparedData['Birthday'] = $data['BirthDate'];
		$preparedData['SNILS'] = $data['SNILS'];
		$preparedData['PolisNumber'] = $data['PolisNumber'];
		$preparedData['Address'] = $data['Address'];
		$preparedData['pmUser_id'] = $data['pmUser_id'];
		
		$response = $this->dbmodel->GetPatientId($preparedData);
		
		$this->ProcessModelSave($response, true, 'Ошибка при получении ID пациента')->ReturnData();
		return true;
	}

	/**
	 * Добавление нового пациента в СПО УЛО
	 */
	function AddPatient() {
		$data = $this->ProcessInputData('AddPatient');
		if ($data === false) return false;
		
		//подготовка нужных данных
		$preparedData = array();
		$preparedData['Surname'] = $data['Surname'];
		$preparedData['Name'] = $data['Name'];
		$preparedData['Patronymic'] = $data['Patronymic'];
		$preparedData['Birthday'] = $data['Birthday'];
		$preparedData['SNILS'] = $data['SNILS'];
		$preparedData['Resident'] = $data['Resident'];
		$preparedData['Address'] = $data['Address'];
		$preparedData['Sex'] = $data['Sex'];
		$preparedData['AreaId'] = $data['AreaId'];
		$preparedData['Polis'] = $data['Polis'];
		$preparedData['IdentityDocument'] = $data['IdentityDocument'];
		$preparedData['Clinic'] = $data['Clinic'];
		$preparedData['pmUser_id'] = $data['pmUser_id'];

		$response = $this->dbmodel->AddPatient($preparedData);
		
		$this->ProcessModelSave($response, true, 'Ошибка при добавлений пациента')->ReturnData();
		return true;
	}

	/**
	 * Обновление данных пациента в СПО УЛО
	 */
	function UpdatePatient() {
		$data = $this->ProcessInputData('UpdatePatient');
		if ($data === false) return false;
		
		//подготовка нужных данных
		$preparedData = array();
		$preparedData['PatientId'] = $data['PatientId'];
		$preparedData['Surname'] = $data['Surname'];
		$preparedData['Name'] = $data['Name'];
		$preparedData['Patronymic'] = $data['Patronymic'];
		$preparedData['Birthday'] = $data['Birthday'];
		$preparedData['SNILS'] = $data['SNILS'];
		$preparedData['Resident'] = $data['Resident'];
		$preparedData['Address'] = $data['Address'];
		$preparedData['Sex'] = $data['Sex'];
		$preparedData['AreaId'] = $data['AreaId'];
		$preparedData['Polis'] = $data['Polis'];
		$preparedData['IdentityDocument'] = $data['IdentityDocument'];
		$preparedData['Clinic'] = $data['Clinic'];
		$preparedData['pmUser_id'] = $data['pmUser_id'];

		$response = $this->dbmodel->UpdatePatient($preparedData);
		$this->ProcessModelSave(false, true, 'Ошибка при обновлений данных пациента')->ReturnData();

		return true;
	}

	/**
	 * Передача данных в API СЛО УЛО
	 */
	function syncAll() {
		$data = $this->ProcessInputData('syncAll');
		if ($data === false) return false;

		$response = $this->dbmodel->syncAll($data);
		$this->ProcessModelSave($response, true, 'Ошибка передачи данных в API СПО УЛО')->ReturnData();

		return true;
	}
}
