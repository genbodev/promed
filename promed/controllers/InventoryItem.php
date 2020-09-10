<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * InventoryItem - контроллер для работы с ТМЦ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			27.11.2014
 *
 * @property InventoryItem_model dbmodel
 */

class InventoryItem extends swController {
	protected  $inputRules = array(
		'loadInventoryItemList' => array(
			array(
				'field' => 'InventoryItem_id',
				'label' => 'Идентификатор ТМЦ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
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
				'field' => 'Date',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('InventoryItem_model', 'dbmodel');
	}

	/**
	 * Получение списка ТМЦ
	 */
	function loadInventoryItemList() {
		$data = $this->ProcessInputData('loadInventoryItemList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadInventoryItemList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}