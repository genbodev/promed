<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Invoice - контроллер для работы с накладными
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			14.10.2014
 *
 * @property Invoice_model dbmodel
 */

class Invoice extends swController {
	protected  $inputRules = array(
		'loadInvoiceGrid' => array(
			array(
				'field' => 'InvoiceType_Code',
				'label' => 'Код типа накладной',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DateRange',
				'label' => 'Диапазон дат',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvoiceSubject_id',
				'label' => 'Поставщик/Получатель',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InventoryItem_id',
				'label' => 'ТМЦ',
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
		'loadShipmentGrid' => array(
			array(
				'field' => 'DateRange',
				'label' => 'Диапазон дат',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvoiceSubject_id',
				'label' => 'Поставщик/Получатель',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InventoryItem_id',
				'label' => 'ТМЦ',
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
		'loadInvoicePositionGrid' => array(
			array(
				'field' => 'Invoice_id',
				'label' => 'Идентификатор накладной',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadInvoiceSubjectList' => array(
			array(
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'InvoiceSubject_id',
				'label' => 'Идентификатор объекта аналитического учета',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadInvoiceForm' => array(
			array(
				'field' => 'Invoice_id',
				'label' => 'Идентификатор накладной',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadInvoicePositionForm' => array(
			array(
				'field' => 'InvoicePosition_id',
				'label' => 'Идентификатор позиции в накладной',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveInvoice' => array(
			array(
				'field' => 'Invoice_id',
				'label' => 'Идентификатор накладной',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvoiceType_id',
				'label' => 'Идентификатор типа накладной',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Invoice_Date',
				'label' => 'Дата накладной',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Invoice_Num',
				'label' => 'Номер накладной',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'InvoiceSubject_id',
				'label' => 'Идентификатор поставщика/получателя',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PayInvoiceType_id',
				'label' => 'Идентификатор типа расчета',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Invoice_Comment',
				'label' => 'Примечание к накладной',
				'rules' => '',
				'type' => 'string'
			),
		),
		'saveInvoicePositionData' => array(
			array(
				'field' => 'InvoicePositionData',
				'label' => 'Данные о позиции в накладной',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'deleteInvoice' => array(
			array(
				'field' => 'Invoice_id',
				'label' => 'Идентификатор накладной',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteInvoicePosition' => array(
			array(
				'field' => 'InvoicePosition_id',
				'label' => 'Идентификатор позиции в накладной',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'InvoiceType_id',
				'label' => 'Идентификатор вида накладной',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getShipmentData' => array(
			array(
				'field' => 'InventoryItem_id',
				'label' => 'Идентификатор ТМЦ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvoicePosition_id',
				'label' => 'Идентификатор позиции в накладной',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvoicePosition_Count',
				'label' => 'Количество',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'InvoicePosition_Coeff',
				'label' => 'Коэфициент',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Invoice_Date',
				'label' => 'Дата списания',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		'calculateInvoicePositions' => array(
			array(
				'field' => 'Invoice_id',
				'label' => 'Идентификатор накладной',
				'rules' => '',
				'type' => 'id'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Invoice_model', 'dbmodel');
	}

	/**
	 * Получение списка приходных накладных
	 */
	function loadInvoiceInGrid() {
		$data = $this->ProcessInputData('loadInvoiceGrid', true);
		if ($data === false) { return false; }

		$data['InvoiceType_Code'] = 1;
		$response = $this->dbmodel->loadInvoiceGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка расходных накладных
	 */
	function loadInvoiceOutGrid() {
		$data = $this->ProcessInputData('loadInvoiceGrid', true);
		if ($data === false) { return false; }

		$data['InvoiceType_Code'] = 2;
		$response = $this->dbmodel->loadInvoiceGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка приходных/расходных накладных
	 */
	function loadInvoiceGrid() {
		$data = $this->ProcessInputData('loadInvoiceGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadInvoiceGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка партий
	 */
	function loadShipmentGrid() {
		$data = $this->ProcessInputData('loadShipmentGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadShipmentGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка позизий в накладной
	 */
	function loadInvoicePositionGrid() {
		$data = $this->ProcessInputData('loadInvoicePositionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadInvoicePositionGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получегние списка объектов аналитического учета
	 */
	function loadInvoiceSubjectList() {
		$data = $this->ProcessInputData('loadInvoiceSubjectList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadInvoiceSubjectList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение накладной
	 */
	function saveInvoice() {
		$data = $this->ProcessInputData('saveInvoice', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveInvoice($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сахранение позиций в накладной
	 */
	function saveInvoicePositionData() {
		$data = $this->ProcessInputData('saveInvoicePositionData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveInvoicePositionData($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление накладной
	 */
	function deleteInvoice() {
		$data = $this->ProcessInputData('deleteInvoice', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteInvoice($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление позиции в накладной
	 */
	function deleteInvoicePosition() {
		$data = $this->ProcessInputData('deleteInvoicePosition', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteInvoicePosition($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы редактирования накладной
	 */
	function loadInvoiceForm() {
		$data = $this->ProcessInputData('loadInvoiceForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadInvoiceForm($data);

		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы редактирования позиции в накладной
	 */
	function loadInvoicePositionForm() {
		$data = $this->ProcessInputData('loadInvoicePositionForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadInvoicePositionForm($data);

		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных о партиях для списания
	 */
	function getShipmentData() {
		$data = $this->ProcessInputData('getShipmentData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getShipmentData($data);

		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 * Перерасчет позиций в расходной накладной
	 */
	function calculateInvoicePositions() {
		$data = $this->ProcessInputData('calculateInvoicePositions', true);
		if ($data === false) { return false; }

		$resp = $this->dbmodel->loadInvoiceForm($data);
		if (is_array($resp) && count($resp) == 1 && $resp[0]['InvoiceType_id'] == 2) {
			$data = array_merge($data, $resp[0]);
			$data['Invoice_Date'] = ConvertDateFormat($data['Invoice_Date']);

			$this->dbmodel->beginTransaction();

			$response = $this->dbmodel->calculateInvoicePositions(array_merge($data));
			if (!empty($response[0]['Error_Msg'])) {
				$this->dbmodel->rollbackTransaction();
			} else {
				$this->dbmodel->commitTransaction();
			}

		} else {
			$response = array(array('success' => true));
		}

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
}