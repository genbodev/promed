<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с расписанием ресурса
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Stanislav Bykov
 * @version			11.2018
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class TimeTableResource extends SwREST_Controller {
	protected  $inputRules = array(
		'TimeTableResource_post' => array(
			array('field' => 'Resource_id', 'label' => 'Идентификатор ресурса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableResourceCreate','label' => 'Массив данных для создания бирок','rules' => 'required','type' => 'array'),
		),
		'TimeTableResource_put' => array(
			array('field' => 'Resource_id', 'label' => 'Идентификатор ресурса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableResourceEdit','label' => 'Массив данных для редактирования бирок','rules' => 'required','type' => 'array'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('TimetableResource_model', 'dbmodel');
	}

	/**
	 * Создание расписания на ресурс
	 */
	function TimeTableResource_post() {
		$data = $this->ProcessInputData('TimeTableResource_post', null, true);

		$this->load->helper('Reg');
		$resp = $this->dbmodel->addTimetableResource($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Изменение расписания на ресурс
	 */
	function TimeTableResource_put() {
		$data = $this->ProcessInputData('TimeTableResource_put', null, true);

		$this->load->helper('Reg');
		$resp = $this->dbmodel->editTimetableResource($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}