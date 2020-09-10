<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PaidService - контроллер для работы с согласиями на паллиативное лечение
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Person
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */
class PalliatInfoConsent extends swController {
	public $inputRules = array(
		'deletePalliatInfoConsent' => array(
			array(
				'field' => 'PalliatInfoConsent_id',
				'label' => 'Идентификатор согласия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPalliatInfoConsentEditForm' => array(
			array(
				'field' => 'PalliatInfoConsent_id',
				'label' => 'Идентификатор согласия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'savePalliatInfoConsent' => array(
			array(
				'field' => 'PalliatInfoConsent_id',
				'label' => 'Идентификатор согласия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PalliatInfoConsentType_id',
				'label' => 'Тип согласия',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PalliatInfoConsent_consDT',
				'label' => 'Дата согласия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Специалист',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PalliatInfoConsent_isSelf',
				'label' => 'Признак',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PalliatMedCareTypeLinkData',
				'label' => 'Список мероприятий',
				'rules' => '',
				'type' => 'json_array',
				'assoc' => true
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PalliatInfoConsent_model', 'dbmodel');
	}


	/**
	*  Удаление согласия
	*/
	function deletePalliatInfoConsent() {
		$data = $this->ProcessInputData('deletePalliatInfoConsent', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePalliatInfoConsent($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления согласия')->ReturnData();

		return true;
	}


	/**
	*  Загрузка формы редактирования
	*/
	function loadPalliatInfoConsentEditForm() {
		$data = $this->ProcessInputData('loadPalliatInfoConsentEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPalliatInfoConsentEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Сохранение согласия
	*/
	function savePalliatInfoConsent() {
		$data = $this->ProcessInputData('savePalliatInfoConsent', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePalliatInfoConsent($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения согласия')->ReturnData();

		return true;
	}
}
