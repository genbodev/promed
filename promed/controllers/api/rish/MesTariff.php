<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API
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

class MesTariff extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MesTariff_model', 'dbmodel');
		$this->inputRules = array(
			'getMesTariff' => array(
				array('field' => 'Mes_id', 'label' => 'Идентификатор МЭС (КЗГ)', 'rules' => 'required', 'type' => 'id')
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getMesTariff');

		$resp = $this->dbmodel->getMesTariffForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}