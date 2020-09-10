<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Resource - контроллер для работы с ресурсами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package        Common
 * @access        public
 * @copyright    Copyright (c) 2010-2015 Swan Ltd.
 */
class Resource extends swController
{
	public $inputRules = array(
		'loadResourceMedServiceGrid' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadResourceList' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Идентификатор ресурса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_ids',
				'label' => 'Услуги выполняемые на ресурсе',
				'rules' => '',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга выполняемая на ресурсе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'onDate',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			)
		)
	);

	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('Resource_model', 'dbmodel');
	}

	/**
	 *    Загрузка грида ресурсов
	 */
	function loadResourceMedServiceGrid()
	{
		$data = $this->ProcessInputData('loadResourceMedServiceGrid', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadResourceMedServiceGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *    Загрузка комбо ресурсов
	 */
	function loadResourceList()
	{
		$data = $this->ProcessInputData('loadResourceList', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadResourceList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}