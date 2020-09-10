<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с картами СМП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			08.2017
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class CmpCallCard extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		// $this->checkAuth(); // для показа без авторизации
		$this->load->database();

		$this->inputRules = array(
			'setCmpCallCardMessageTabletDT' => array(
				array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты СМП', 'rules' => 'required', 'type' => 'id'),
			),
			'mloadCmpCallCard' => array(
				array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты СМП', 'rules' => 'required', 'type' => 'id')
			)
		);
	}

	/**
	 * Установка даты получения карты СМП на планшете
	 */
	function setCmpCallCardMessageTabletDT_post() {
		$data = $this->ProcessInputData('setCmpCallCardMessageTabletDT');

		$this->load->model('CmpCallCardMessage_model');
		$this->CmpCallCardMessage_model->setCmpCallCardMessageTabletDT($data);
		$this->response(array(
			'error_code' => 0
		));
	}



	/**
	 * Загрузка списка направлений для мобильного приложения
	 */
	function mloadCmpCallCard_get() {
		$data = $this->ProcessInputData('mloadCmpCallCard');

		$this->load->model('CmpCallCard_model');
		$resp = $this->CmpCallCard_model->printCmpCallCardEMK($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}