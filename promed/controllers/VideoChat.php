<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * VideoChat - контроллер для работы с видеосвязью
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			24.05.2018
 *
 * @property VideoChat_model dbmodel
 */

class VideoChat extends swController {

	public $inputRules = array(
		'getVideoSettings' => array(
		),
		'setVideoSettings' => array(
			array('field' => 'settings', 'label' => 'Настройки', 'rules' => 'required', 'type' => 'string'),
		),
		'saveImage' => array(
			array('field' => 'imageBase64', 'label' => 'Изображение', 'rules' => 'required', 'type' => 'string'),
		),
		'loadPMUserContactList' => array(
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
			array('field' => 'searchInPromed', 'label' => 'Флаг поиска в промеде', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'Lpu_oid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Dolgnost_id', 'label' => 'Идентификатор должности', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_oid', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id'),
		),
		'addPMUserContact' => array(
			array('field' => 'pmUserCache_rid', 'label' => 'Идентификатор пользователя контакта', 'rules' => 'required', 'type' => 'id'),
		),
		'deletePMUserContact' => array(
			array('field' => 'pmUserCache_rid', 'label' => 'Идентификатор пользователя контакта', 'rules' => 'required', 'type' => 'id'),
		),
		'getPMUserInfo' => array(
			array('field' => 'pmUserCache_rid', 'label' => 'Идентификатор пользователя контакта', 'rules' => 'required', 'type' => 'id'),
		),
		'sendTextMessage' => array(
			array('field' => 'pmUser_gid_list', 'label' => 'Идентификатор получателя', 'rules' => 'required', 'type' => 'json_array'),
			array('field' => 'text', 'label' => 'Текст сообщение', 'rules' => 'required', 'type' => 'string'),
		),
		'sendFileMessage' => array(
			array('field' => 'pmUser_gid_list', 'label' => 'Идентификатор получателя', 'rules' => 'required', 'type' => 'json_array'),
		),
		'loadMessageList' => array(
			array('field' => 'pmUser_cid_list', 'label' => 'Идентификатор собеседника', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'beforeDT', 'label' => 'Дата/время', 'rules' => '', 'type' => 'datetime'),
		),
		'getFileMessage' => array(
			array('field' => 'id', 'label' => 'Идентификатор сообщения', 'rules' => 'required', 'type' => 'id'),
		),
		'loadFileList' => array(
			array('field' => 'pmUser_cid_list', 'label' => 'Идентификатор собеседника', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'pmUser_sid_list', 'label' => 'Идентификатор отправителя', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'query', 'label' => 'Запрос', 'rules' => '', 'type' => 'string'),
			array('field' => 'fileTypeName', 'label' => 'Тип файла', 'rules' => 'trim', 'type' => 'string'),
		),
		'loadFileTypeList' => array(),
		'saveCall' => array(
			array('field' => 'room', 'label' => 'Идентификатор комнаты', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'pmUser_iid', 'label' => 'Идентификатор инициатора звонка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'pmUser_ids', 'label' => 'Идентификаторы собеседников', 'rules' => 'required', 'type' => 'json_array'),
			array('field' => 'callType', 'label' => 'Тип звонка', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'begDT', 'label' => 'Дата и время начала звонка', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'endDT', 'label' => 'Дата и время окончания звонка', 'rules' => '', 'type' => 'datetime'),
		),
		'updateCall' => array(
			array('field' => 'room', 'label' => 'Идентификатор комнаты', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'endDT', 'label' => 'Дата и время окончания звонка', 'rules' => '', 'type' => 'datetime'),
		),
		'saveCallRecord' => array(
			array('field' => 'room', 'label' => 'Идентификатор комнаты', 'rules' => 'required', 'type' => 'string'),
		),
		'loadCallList' => array(
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('VideoChat_model', 'dbmodel');
	}

	/**
	 * Получение настроек видеосвязи пользователя
	 */
	function getVideoSettings() {
		$data = $this->ProcessInputData('getVideoSettings', true);
		if ($data === false) return;
		$response = $this->dbmodel->getVideoSettings($data);
		$this->processModelSave($response, true, 'Ошибка при получении настроек видеосвязи')->ReturnData();
	}

	/**
	 * Изменение настроек видеосвязи пользователя
	 */
	function setVideoSettings() {
		$data = $this->ProcessInputData('setVideoSettings', true);
		if ($data === false) return;
		$data['settings'] = json_decode($data['settings'], true);
		$response = $this->dbmodel->setVideoSettings($data);
		$this->processModelSave($response)->ReturnData();
	}

	/**
	 * Сохранение изображения на сервер
	 */
	function saveImage() {
		$data = $this->ProcessInputData('saveImage', false);
		if ($data === false) return;
		$response = $this->dbmodel->saveImage($data);
		$this->processModelSave($response)->ReturnData();
	}

	/**
	 * Загрузка изображения на сервер
	 */
	function uploadImage() {
		$response = $this->dbmodel->uploadImage($_FILES['ImageFile']);
		$this->processModelSave($response)->ReturnData();
	}

	/**
	 * Получение списка контактов пользователя
	 */
	function loadPMUserContactList() {
		$data = $this->ProcessInputData('loadPMUserContactList', true);
		if ($data === false) return;
		$response = $this->dbmodel->loadPMUserContactList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Добавление контакта
	 */
	function addPMUserContact() {
		$data = $this->ProcessInputData('addPMUserContact', true);
		if ($data === false) return;
		$response = $this->dbmodel->addPMUserContact($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Удаление контакта
	 */
	function deletePMUserContact() {
		$data = $this->ProcessInputData('deletePMUserContact', true);
		if ($data === false) return;
		$response = $this->dbmodel->deletePMUserContact($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение ниформации о пользователе
	 */
	function getPMUserInfo() {
		$data = $this->ProcessInputData('getPMUserInfo', true);
		if ($data === false) return;
		$response = $this->dbmodel->getPMUserInfo($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Отправка текстового сообщения
	 */
	function sendTextMessage() {
		$data = $this->ProcessInputData('sendTextMessage', true);
		if ($data === false) return;
		$response = $this->dbmodel->sendTextMessage($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Отправка сообщения с файлом
	 */
	function sendFileMessage() {
		$data = $this->ProcessInputData('sendFileMessage', true);
		if ($data === false) return;
		$response = $this->dbmodel->sendFileMessage($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение текстовых сообщений
	 */
	function loadMessageList() {
		$data = $this->ProcessInputData('loadMessageList', true);
		if ($data === false) return;
		$response = $this->dbmodel->loadMessageList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Вывод файла из сообщения
	 */
	function getFileMessage() {
		$data = $this->ProcessInputData('getFileMessage', true);
		if ($data === false) return;

		$message = $this->dbmodel->getFileMessage($data);

		if (empty($message) || empty($message['file_path'])) {
			echo 'Файл не найден';
			exit;
		}

		header("Content-type: {$message['file_type']}");
		header("Content-Disposition: attachment; filename={$message['file_name']}");
		header("Content-length: ".filesize($message['file_path']));
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile($message['file_path']);
	}
	
	/**
	 * Получение списка файлов
	 */
	function loadFileList() {
		$data = $this->ProcessInputData('loadFileList', true);
		if ($data === false) return;
		$response = $this->dbmodel->loadFileList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение списка типов файлов
	 */
	function loadFileTypeList() {
		$data = $this->ProcessInputData('loadFileTypeList', true);
		if ($data === false) return;
		$response = $this->dbmodel->loadFileTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	function saveCall() {
		$data = $this->ProcessInputData('saveCall', true);
		if ($data === false) return;
		$response = $this->dbmodel->saveCall($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function updateCall() {
		$data = $this->ProcessInputData('updateCall', true);
		if ($data === false) return;
		$response = $this->dbmodel->updateCall($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function saveCallRecord() {
		$data = $this->ProcessInputData('saveCallRecord', true);
		if ($data === false) return;
		$response = $this->dbmodel->saveCallRecord($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function loadCallList() {
		$data = $this->ProcessInputData('loadCallList', true);
		if ($data === false) return;
		$response = $this->dbmodel->loadCallList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}