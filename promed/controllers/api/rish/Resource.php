<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с ресурсами
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

class Resource extends SwREST_Controller {
	protected  $inputRules = array(
		'Resource_post' => array(
			array('field' => 'Resource_Name', 'label' => 'Наименование ресурса', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'ResourceType_id', 'label' => 'Идентификатор типа ресурса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Resource_begDT', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Resource_endDT', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedProductCardResourceData', 'label' => 'Массив карточек мед. изделий', 'rules' => '', 'type' => 'string', 'default' => '[]'),
		),
		'Resource_put' => array(
			array('field' => 'Resource_id', 'label' => 'Идентификатор ресурса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Resource_Name', 'label' => 'Наименование ресурса', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'ResourceType_id', 'label' => 'Идентификатор типа ресурса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Resource_begDT', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Resource_endDT', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedProductCardResourceData', 'label' => 'Массив карточек мед. изделий', 'rules' => '', 'type' => 'string', 'default' => '[]'),
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
		$this->load->model('Resource_model', 'dbmodel');
	}

	/**
	 * Создание расписания на ресурс
	 */
	function Resource_post() {
		$data = $this->ProcessInputData('Resource_post', null, true);

		$resp = $this->dbmodel->saveResource($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'Resource_id' => $resp['Resource_id']
			)
		));
	}

	/**
	 * Изменение расписания на ресурс
	 */
	function Resource_put() {
		$data = $this->ProcessInputData('Resource_put', null, true);

		$resp = $this->dbmodel->saveResource($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'Resource_id' => $resp['Resource_id']
			)
		));
	}
}