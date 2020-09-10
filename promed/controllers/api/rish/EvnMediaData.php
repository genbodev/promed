<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MediaDataUslugaTest - контроллер API для работы с прикрепленными файлами лабораторных исследований
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			30.11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnMediaData extends SwREST_Controller {
	protected $inputRules = array(
		'getEvnMediaData' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnMediaData_IsRequired', 'label' => 'Нужен прикрепленный файл', 'rules' => 'required', 'type' => 'id')
		),
		'createEvnMediaData' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'File', 'label' => 'Файл в base64', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnMediaData_FileName', 'label' => 'Наименование файла', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача, сформировавшего файл с результатами исследований', 'rules' => '', 'type' => 'id'),
		),
		'deleteEvnMediaData' => array(
			array('field' => 'EvnMediaData_id', 'label' => 'Идентификатор файла', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnMediaFiles_model', 'dbmodel');
	}

	/**
	 * Получение прикрепленных файлов лабораторного исследования
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnMediaData');

		$resp = $this->dbmodel->getEvnMediaDataForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление прикрепленных файлов лабароторного исследования
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnMediaData');

		$sp = getSessionParams();
		$data['session'] = $sp['session'];
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$this->load->model('MedPersonal_model', 'mpmodel');
		if(!empty($data['MedStaffFact_id'])){
			$this->load->model('MedPersonal_model', 'mpmodel');
			$medStaffFact = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id' => $data['MedStaffFact_id']));
			if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) $data['MedPersonal_id'] = $medStaffFact[0]['MedPersonal_id'];
		}else if(!empty($data['session']['medpersonal_id'])){
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}
		if(empty($data['MedPersonal_id'])){
			$this->response(array(
				'error_code' => 5,
				'ErrorMsg' => 'Не указан врач, обработка пакета невозможна'
			));
		}

		$resp = $this->dbmodel->addEvnMediaDataFromAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		if (!is_array($resp) || empty($resp['EvnMediaData_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('EvnMediaData_id' => $resp['EvnMediaData_id'])
		));
	}

	/**
	 * Удаление файлов, прикрепленных к исследованию
	 */
	function index_delete() {
		$data = $this->ProcessInputData('deleteEvnMediaData');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$data['session'] = $sp['session'];
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->deleteEvnMediaData($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}