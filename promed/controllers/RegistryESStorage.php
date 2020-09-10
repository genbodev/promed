<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RegistryESStorage - контроллер для работы с номерами ЭЛН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Mse
 * @access      public
 * @copyright   Copyright (c) 2014 Swan Ltd.
 * @author		Stanislav Bykov (savage@swan.perm.ru)
 * @version     21.07.2017
 *
 * @property RegistryESStorage_model dbmodel
 */

class RegistryESStorage extends swController {
	public $inputRules = array(
		'loadRegistryESStorageGrid' => array(),
		'loadRegistryESStorageNumQuery' => array(),
		'getRegistryESStorageQuery' => array(
			array('field' => 'RegistryESStorage_NumQuery', 'label' => 'Номер запроса', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'RegistryESStorage_Count', 'label' => 'Количество номеров', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'certbase64', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
			array('field' => 'certhash', 'label' => 'Хэш сертификата', 'rules' => '', 'type' => 'string'),
			array('field' => 'needHash', 'label' => 'Признак необходимости подсчёта хэша', 'rules' => '', 'type' => 'int'),
		),
		'queryRegistryESStorage' => array(
			array(
				'field' => 'RegistryESStorage_NumQuery',
				'label' => 'Номер запроса',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'RegistryESStorage_Count',
				'label' => 'Количество номеров',
				'rules' => 'required',
				'type' => 'int'
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
		'getEvnStickNum' => array(),
		'unbookEvnStickNum' => array(
			array('field' => 'RegistryESStorage_id', 'label' => 'Идентификатор номера ЭЛН', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('RegistryESStorage_model', 'dbmodel');
	}

	/**
	 * Получение списка номеров ЛВН
	 */
	public function loadRegistryESStorageGrid() {
		$data = $this->ProcessInputData('loadRegistryESStorageGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryESStorageGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение номера запроса номеров ЛВН
	 */
	public function loadRegistryESStorageNumQuery() {
		$data = $this->ProcessInputData('loadRegistryESStorageNumQuery', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryESStorageNumQuery($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера запроса')->ReturnData();
		return true;
	}

	/**
	 * Формирование запроса номеров ЛВН
	 */
	public function getRegistryESStorageQuery() {
		$data = $this->ProcessInputData('getRegistryESStorageQuery', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getRegistryESStorageQuery($data);
		$this->ProcessModelSave($response, true, 'Ошибка формирования запроса номеров ЛВН')->ReturnData();
		return true;
	}

	/**
	 * Запрос номеров ЛВН
	 */
	public function queryRegistryESStorage() {
		$data = $this->ProcessInputData('queryRegistryESStorage', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->queryRegistryESStorage($data);
		$this->ProcessModelSave($response, true, 'Ошибка запроса номеров ЭЛН')->ReturnData();
		return true;
	}

	/**
	 * Получение номера ЭЛН
	 */
	public function getEvnStickNum() {
		$data = $this->ProcessInputData('getEvnStickNum', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnStickNum($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Отмена бронирования номера ЭЛН
	 */
	public function unbookEvnStickNum() {
		$data = $this->ProcessInputData('unbookEvnStickNum', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->unbookEvnStickNum($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
}