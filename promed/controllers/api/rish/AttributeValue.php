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

class AttributeValue extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('TariffVolumes_model', 'dbmodel');
		$this->inputRules = array(
			'getAttributeValue' => array(
				array('field' => 'TariffClass_id', 'label' => 'Ид тарифа', 'rules' => '', 'type' => 'id'),
				array('field' => 'TarifClass_sysNick', 'label' => 'Код тарифа', 'rules' => '', 'type' => 'string'),
				array('field' => 'TariffClass_Name', 'label' => 'Наименование тарифа', 'rules' => '', 'type' => 'string'),
				array('field' => 'Attribute_id', 'label' => 'Ид атрибута', 'rules' => '', 'type' => 'id'),
				array('field' => 'Attribute_sysNick', 'label' => 'Код атрибута', 'rules' => '', 'type' => 'string'),
				array('field' => 'Attribute_Name', 'label' => 'Наименование атрибута', 'rules' => '', 'type' => 'string'),
				array('field' => 'Date_DT', 'label' => 'Дата действия', 'rules' => '', 'type' => 'date')
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getAttributeValue');

		if (empty($data['TariffClass_Name']) && empty($data['TariffClass_id']) && empty($data['TarifClass_sysNick'])) {
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->getAttributeValueForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}