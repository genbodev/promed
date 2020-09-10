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

class MorbusCrazyPerson extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MorbusCrazy_model', 'dbmodel');
		$this->inputRules = array(
			'getMorbusCrazyPerson' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_pid', 'label' => 'Идентификатор события-родителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusType', 'label' => 'Тип специфики', 'rules' => '', 'type' => 'id')
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getMorbusCrazyPerson');

		if (empty($data['Person_id']) && empty($data['Evn_pid'])) {
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->getMorbusCrazyPersonForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}