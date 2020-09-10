<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * StickFSSData - контроллер для работы с запросами ЭЛН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Mse
 * @access      public
 * @copyright   Copyright (c) 2017 Swan Ltd.
 * @author		Dmitrii Vlasenko
 * @version     18.08.2017
 *
 * @property StickFSSData_model dbmodel
 */

class StickFSSData extends swController {
	public $inputRules = array(
		'loadStickFSSDataGrid' => array(
			array(
				'field' => 'StickFSSData_DateRange',
				'label' => 'Диапазон дат запросов',
				'rules' => '',
				'type' => 'daterange'
			)
		),
		'loadStickFSSDataGetGrid' => array(
			array(
				'field' => 'StickFSSData_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadStickFSSErrorGrid' => array(
			array(
				'field' => 'StickFSSData_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'queryStickFSSData' => array(
			array(
				'field' => 'StickFSSData_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'xml',
				'label' => 'Запрос',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'signType',
				'label' => 'Тип подписи',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SignedData',
				'label' => 'SignedData',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Hash',
				'label' => 'Hash',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getNewStickFSSDataNum' => array(
			array('field' => 'StickFSSData_id', 'label' => 'Идентификатор запроса ЭЛН', 'rules' => '', 'type' => 'id')
		),
		'saveStickFSSData' => array(
			array('field' => 'StickFSSData_id', 'label' => 'Идентификатор запроса ЭЛН', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickFSSData_Num', 'label' => 'Номер запроса', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Lpu_OGRN', 'label' => 'ОГРН МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'StickFSSData_StickNum', 'label' => 'Номер ЭЛН', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'ignoreCheckExist', 'label' => 'Признак игнорирования проверки', 'rules' => '', 'type' => 'int')
		),
		'loadStickFSSDataForm' => array(
			array('field' => 'StickFSSData_id', 'label' => 'Идентификатор запроса ЭЛН', 'rules' => 'required', 'type' => 'id')
		),
		'exportStickFSSDataToXml' => array(
			array('field' => 'StickFSSData_id', 'label' => 'Идентификатор запроса ЭЛН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'certbase64', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
			array('field' => 'needHash', 'label' => 'Признак необходимости подсчёта хэша', 'rules' => '', 'type' => 'int')
		),
		'showFiles' => array(
			array('field' => 'StickFSSData_id', 'label' => 'Идентификатор запроса ЭЛН', 'rules' => 'required', 'type' => 'id')
		),
		'UploadTestXML' => array(
			array('field' => 'StickFSSData_id', 'label' => 'Идентификатор запроса ЭЛН', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('StickFSSData_model', 'dbmodel');
	}

	/**
	 * Просмотр файлов отправленных в ФСС по запросу
	 */
	public function showFiles() {
		$data = $this->ProcessInputData('showFiles', true);
		if ($data === false) { return false; }

		$this->dbmodel->showFiles($data);

		return true;
	}

	/**
	 * Получение списка запросов ЛВН
	 */
	public function loadStickFSSDataGrid() {
		$data = $this->ProcessInputData('loadStickFSSDataGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStickFSSDataGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка данных ЛВН
	 */
	public function loadStickFSSDataGetGrid() {
		$data = $this->ProcessInputData('loadStickFSSDataGetGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStickFSSDataGetGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка расхождений
	 */
	public function loadStickFSSErrorGrid() {
		$data = $this->ProcessInputData('loadStickFSSErrorGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStickFSSErrorGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение номера для нового запроса ЭЛН
	 */
	public function getNewStickFSSDataNum() {
		$data = $this->ProcessInputData('getNewStickFSSDataNum', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getNewStickFSSDataNum($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 * Сохранение запроса ЭЛН
	 */
	public function saveStickFSSData() {
		$data = $this->ProcessInputData('saveStickFSSData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveStickFSSData($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение данных для формы запроса ЭЛН
	 */
	public function loadStickFSSDataForm() {
		$data = $this->ProcessInputData('loadStickFSSDataForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStickFSSDataForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Экспорт запроса в xml
	 */
	public function exportStickFSSDataToXml() {
		$data = $this->ProcessInputData('exportStickFSSDataToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportStickFSSDataToXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка формирования запроса')->ReturnData();

		return true;
	}

	/**
	 * Запрос в ФСС
	 */
	public function queryStickFSSData() {
		$data = $this->ProcessInputData('queryStickFSSData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->queryStickFSSData($data);
		$this->ProcessModelSave($response, true, 'Ошибка запроса номеров ЭЛН')->ReturnData();
		return true;
	}

	/**
	 * Имитирует получение ответа на запрос в ФСС
	 */
	public function UploadTestXML() {
		$is_debug = $this->config->item('IS_DEBUG');
		if( empty($is_debug) ) {
			throw new Exception("Доступно только в тестовой среде");
		}
		if (!isset($_FILES['userfile'])) {
			throw new Exception("Не выбран файл");
		}
		$data = $this->ProcessInputData('UploadTestXML', true);
		if ($data === false) { return false; }
		$xml = file_get_contents($_FILES['userfile']['tmp_name']);

		$response = $this->dbmodel->parseXmlResponse($xml, $data);

		$this->ProcessModelSave($response, true, 'Ошибка сохранения XML')->ReturnData();
		return true;
	}
}