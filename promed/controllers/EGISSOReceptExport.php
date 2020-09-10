<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EGISSOReceptExport - Журнал ручного экспорта МСЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 *
 */

class EGISSOReceptExport extends swController {
	protected  $inputRules = array(
		'save' => array(
			array(
				'field' => 'EGISSOReceptExport_begDT',
				'label' => 'Дата начала периода экспорта',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EGISSOReceptExport_endDT',
				'label' => 'Дата окончания периода экспорта',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EGISSOReceptExport_isNew',
				'label' => 'Только новые',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'EGISSOReceptExport_setDT',
				'label' => 'Дата экспорта',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EGISSOReceptExport_begDT',
				'label' => 'Дата начала периода экспорта',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EGISSOReceptExport_endDT',
				'label' => 'Дата окончания периода экспорта',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EGISSOReceptExport_isNew',
				'label' => 'Только новые',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EGISSOReceptExportStatus_id',
				'label' => 'Статус экспорта',
				'rules' => '',
				'type' => 'id'
			),
			array('field' => 'start', 'type' => 'int'),
			array('field' => 'limit', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EGISSOReceptExport_model', 'dbmodel');
	}

	/**
	 * Список / поиск
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
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }
		
		if ( $data['EGISSOReceptExport_begDT'] > $data['EGISSOReceptExport_endDT'] ) {
			$this->ReturnError('Дата начала не может быть больше даты окончания');
			return false;
		}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}