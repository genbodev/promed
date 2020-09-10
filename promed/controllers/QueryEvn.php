<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QueryEvn - Журнал запросов сторонних МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 *
 * @property QueryEvn_model dbmodel
 */

class QueryEvn extends swController {
	protected $inputRules = array(
		'delete' => array(
			array(
				'field' => 'QueryEvn_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'doLoadHistory' => array(
			array(
				'field' => 'QueryEvn_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadEvnList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => '',
				'type' => 'id'
			),
		),
		'doLoadEvnXmlList' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'QueryEvn_id',
				'label' => 'Идентификатор запроса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'QueryEvnType_id',
				'label' => 'Тип запроса',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'QueryEvnStatus_id',
				'label' => 'Cтатус запроса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'QueryEvnMessage_TextRequest',
				'label' => 'Текст запроса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'QueryEvnMessage_TextResponse',
				'label' => 'Текст ответа',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'QueryEvnMessageAnswers',
				'label' => 'Ответы',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => '',
				'session_value' => 'medpersonal_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => '',
				'session_value' => 'medstafffact_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_rid',
				'label' => '',
				'session_value' => 'pmuser_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_msid',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'scenario',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
		),
		'load' => array(
			array(
				'field' => 'QueryEvn_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => '',
				'session_value' => 'medpersonal_id',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadList' => array(
			array(
				'field' => 'QueryEvnUserType_id',
				'label' => 'Идентификатор типа пользователя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'onlyMy',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'limit',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => '',
				'session_value' => 'medpersonal_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StatusFilter_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Fio',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'uploadFiles' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadUsersList' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'QueryEvnUserType_id',
				'label' => 'Тип пользователя',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveUser' => array(
			array(
				'field' => 'QueryEvn_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'QueryEvnUserType_id',
				'label' => 'Тип пользователя',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_rid',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
		),
		'addDoc' => array(
			array(
				'field' => 'loadedFiles',
				'label' => 'Файлы',
				'rules' => 'required',
				'type' => 'json_array'
			),
		),
		'send' => array(
			array(
				'field' => 'QueryEvn_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('QueryEvn_model', 'dbmodel');
	}

	/**
	 * Удаление запроса
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении запроса')->ReturnData();
		return true;
	}

	/**
	* История
	 */
	function doLoadHistory()
	{
		$data = $this->ProcessInputData('doLoadHistory');
		if ($data === false) { return false; }

		$response = $this->dbmodel->doLoadHistory($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список примечаний
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает примечание
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список случаев пациента
	 */
	function loadEvnList()
	{
		$data = $this->ProcessInputData('loadEvnList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	* Список документов по случаю
	 */
	function doLoadEvnXmlList()
	{
		$data = $this->ProcessInputData('doLoadEvnXmlList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->doLoadEvnXmlList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение запроса
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Загрузка файлов
	 */
	function uploadFiles()
	{
		$data = $this->ProcessInputData('uploadFiles');
		if ($data === false || !is_array($_FILES['loadfiles']) || !count($_FILES['loadfiles'])) { return false; }
		
		$files = array();
		foreach($_FILES['loadfiles']['name'] as $k => $v) {
			$files[] = array(
				'name' => $_FILES['loadfiles']['name'][$k],
				'type' => $_FILES['loadfiles']['type'][$k],
				'tmp_name' => $_FILES['loadfiles']['tmp_name'][$k],
				'error' => $_FILES['loadfiles']['error'][$k],
				'size' => $_FILES['loadfiles']['size'][$k]
			);
		}
		
		$data['filterType'] = null;
		
		$this->load->model('EvnMediaFiles_model');
		$response = $this->EvnMediaFiles_model->uploadSeveralFiles($files, $data);
		
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Список пользователей
	 */
	function loadUsersList()
	{
		$data = $this->ProcessInputData('loadUsersList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUsersList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение пользователей
	 */
	function saveUser()
	{
		$data = $this->ProcessInputData('saveUser');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUser($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Прикрепление файлов из ЭМК
	 */
	function addDoc()
	{
		$data = $this->ProcessInputData('addDoc');
		if ($data === false) { return false; }

		$response = $this->dbmodel->addDoc($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Отправка
	 */
	function send()
	{
		$data = $this->ProcessInputData('send');
		if ($data === false) { return false; }

		$response = $this->dbmodel->send($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
}