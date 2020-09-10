<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы со спецификой о новорождённом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class PersonNewborn extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('PersonNewBorn_model', 'dbmodel');
		$this->inputRules = array(
			'getPersonNewborn' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnSection_mid', 'label' => 'Идентификатор движения матери, из которого добавлена специфика', 'rules' => '', 'type' => 'id')
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getPersonNewborn');

		if (empty($data['Person_id']) && empty($data['EvnSection_mid'])) {
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->getPersonNewbornForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}