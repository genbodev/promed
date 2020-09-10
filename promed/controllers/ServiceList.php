<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceList - контроллер для работы с внутренними сервисами для автоматизации действий промеда
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.01.2016
 *
 * @property ServiceList_model dbmodel
 */

class ServiceList extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'loadServiceListGrid' => array(

			),
			'loadServiceListLogGrid' => array(
				array(
					'field' => 'ServiceList_id',
					'label' => 'Идентификатор сервиса',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ServiceList_Code',
					'label' => 'Код сервиса',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ServiceListPackageType_id',
					'label' => 'Тип пакета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ServiceListLog_DateRange',
					'label' => 'Дата запуска',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Лимит записей',
					'rules' => '',
					'type' => 'int'
				),
			),
			'loadServiceListDetailLogGrid' => array(
				array(
					'field' => 'ServiceListLog_id',
					'label' => 'Идентификатор запуска сервиса',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ServiceListPackage_id',
					'label' => 'Идентификатор пакета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Лимит записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ServiceListDetailLog_Message',
					'label' => 'Залогированое сообщение',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ServiceListLogType_id',
					'label' => 'Тип ответа сервиса',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadServiceListPackageGrid' => array(
				array(
					'field' => 'ServiceListLog_id',
					'label' => 'Идентификатор запуска сервиса',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ServiceListPackageType_id',
					'label' => 'Тип пакета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ServiceListLogErrorType_id',
					'label' => 'Тип ошибки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PackageStatus_id',
					'label' => 'Идентификатор статуса пакета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Лимит записей',
					'rules' => '',
					'type' => 'int'
				),
			),
			'saveServiceListLog' => array(
				array(
					'field' => 'ServiceListLog_id',
					'label' => 'Идентификатор запуска сервиса',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ServiceList_id',
					'label' => 'Идентификатор сервиса',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ServiceListLog_begDT',
					'label' => 'Время запуска сервиса',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'ServiceListLog_endDT',
					'label' => 'Время завершения работы сервиса',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'ServiceListResult_id',
					'label' => 'Результат работы сервиса',
					'rules' => '',
					'type' => 'id'
				),
			),
			'deleteAllServiceListPackage' => array(
				array(
					'field' => 'ServiceListLog_id',
					'label' => 'Идентификатор запуска сервиса',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadServiceListPackageTypeGrid' => array(
				array(
					'field' => 'ServiceList_id',
					'label' => 'Идентификатор сервиса',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'setServiceListPackageIsNotSend' => array(
				array('field' => 'ServiceListPackage_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ServiceListPackage_IsNotSend', 'label' => 'Признак запрета повторной отправки', 'rules' => 'required', 'type' => 'id'),
			),
		);

		$this->load->database();
		$this->load->model('ServiceList_model', 'dbmodel');
	}

	/**
	 * Получение списка сервисов
	 */
	function loadServiceListGrid() {
		$data = $this->ProcessInputData('loadServiceListGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadServiceListGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка типов пакетов сервиса
	 */
	function loadServiceListPackageTypeGrid() {
		$data = $this->ProcessInputData('loadServiceListPackageTypeGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadServiceListPackageTypeGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка запусков сервиса
	 */
	function loadServiceListLogGrid() {
		$data = $this->ProcessInputData('loadServiceListLogGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadServiceListLogGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Получить список сообщений по работе серсиса
	 */
	function loadServiceListDetailLogGrid() {
		$data = $this->ProcessInputData('loadServiceListDetailLogGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadServiceListDetailLogGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Получить список пакетов, обработанных сервисом
	 */
	function loadServiceListPackageGrid() {
		$data = $this->ProcessInputData('loadServiceListPackageGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadServiceListPackageGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение информации о запуске сервиса
	 */
	function saveServiceListLog() {
		$data = $this->ProcessInputData('saveServiceListLog', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveServiceListLog($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление всех строк из пакета
	 */
	function deleteAllServiceListPackage() {
		$data = $this->ProcessInputData('saveServiceListLog', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveServiceListLog($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	function setServiceListPackageIsNotSend() {
		$data = $this->ProcessInputData('setServiceListPackageIsNotSend', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setServiceListPackageIsNotSend($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}
?>