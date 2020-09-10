<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Resource - контроллер для работы с целями исследований
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package        Common
 * @access        public
 * @copyright    Copyright (c) 2010-2015 Swan Ltd.
 */
class StudyTarget extends swController
{
	public $inputRules = array(
		'loadStudyTargetList' => array(
//			array(
//				'field' => 'MedService_id',
//				'label' => 'Идентификатор службы',
//				'rules' => '',
//				'type' => 'id'
//			),
//			array(
//				'field' => 'Resource_id',
//				'label' => 'Идентификатор ресурса',
//				'rules' => '',
//				'type' => 'id'
//			),
//			array(
//				'field' => 'UslugaComplex_ids',
//				'label' => 'Услуги выполняемые на ресурсе',
//				'rules' => '',
//				'type' => 'json_array',
//				'assoc' => true
//			),
//			array(
//				'field' => 'UslugaComplex_id',
//				'label' => 'Услуга выполняемая на ресурсе',
//				'rules' => '',
//				'type' => 'id'
//			),
//			array(
//				'field' => 'onDate',
//				'label' => 'Дата',
//				'rules' => '',
//				'type' => 'date'
//			)
		)
	);

	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('StudyTarget_model', 'dbmodel');
	}

	/**
	 *    Загрузка комбо целей исследовний
	 */
	function loadStudyTargetList()
	{
		$response = $this->dbmodel->loadStudyTargetList();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}