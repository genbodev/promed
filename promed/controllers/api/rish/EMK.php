<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EMK - контроллер API для работы с ЭМК
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.10.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EMK extends SwREST_Controller {
	protected  $inputRules = array(
		'mEmkHistory' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
	}

	/**
	 * Получение списка в ЭМК
	 */
	function mEmkHistory_get() {

		$data = $this->ProcessInputData('mEmkHistory', null, true);
		$this->load->helper('Reg');

		$this->load->library('swFilterResponse');
		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');

		$resp = $this->EPH_model->getPersonHistoryForApi($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->response(array('error_code' => 0, 'data' => !empty($resp) ? $resp : array()));
	}
}