<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с исследованиями PACS
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class LpuPacs extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaParAssociatedResearches_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnUslugaParAssociatedResearches' => array(
				array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => 'required', 'type' => 'id')
			),
			'createEvnUslugaParAssociatedResearches' => array(
				array('field' => 'Study_uid', 'label' => 'Уникальный идентификатор исследования', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Study_date', 'label' => 'Дата исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'Study_time', 'label' => 'Время исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'Patient_Name', 'label' => 'Имя пациента', 'rules' => '', 'type' => 'string')
			),
			'updateEvnUslugaParAssociatedResearches' => array(
				array('field' => 'EvnUslugaParAssociatedResearches_id', 'label' => 'Идентификатор исследования, привязанного к услуге', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Study_uid', 'label' => 'Уникальный идентификатор исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'Study_date', 'label' => 'Дата исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'Study_time', 'label' => 'Время исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'Patient_Name', 'label' => 'Имя пациента', 'rules' => '', 'type' => 'string')
			),
			'deleteEvnUslugaParAssociatedResearches' => array(
				array('field' => 'EvnUslugaParAssociatedResearches_id', 'label' => 'Идентификатор исследования, привязанного к услуге', 'rules' => 'required', 'type' => 'id')
			)
		);
	}

	/**
	 * Получение результатов исследования по параклинической услуге
	 */
	function Study_uidByEvnUslugaPar_get() {
		$data = $this->ProcessInputData('getEvnUslugaParAssociatedResearches');

		$resp = $this->dbmodel->getEvnUslugaParAssociatedResearches($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление исследования, прикрепленного к услуге
	 */
	function EvnUslugaParAssociatedResearches_post() {
		$data = $this->ProcessInputData('createEvnUslugaParAssociatedResearches');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->saveEvnUslugaParAssociatedResearches($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaParAssociatedResearches_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'EvnUslugaParAssociatedResearches_id' => $resp[0]['EvnUslugaParAssociatedResearches_id']
			)
		));
	}

	/**
	 * Изменение исследования, прикрепленного к услуге
	 */
	function EvnUslugaParAssociatedResearches_put() {
		$data = $this->ProcessInputData('updateEvnUslugaParAssociatedResearches');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createEvnUslugaParAssociatedResearches');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$old_data = $this->dbmodel->getEvnUslugaParAssociatedResearchesForAPI($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		$resp = $this->dbmodel->saveEvnUslugaParAssociatedResearches($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaParAssociatedResearches_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление исследования, прикрепленного к услуге
	 */
	function EvnUslugaParAssociatedResearches_delete() {
		$data = $this->ProcessInputData('deleteEvnUslugaParAssociatedResearches');

		$resp = $this->dbmodel->deleteEvnUslugaParAssociatedResearches($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}