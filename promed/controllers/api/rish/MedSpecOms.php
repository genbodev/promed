<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с местом работы
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

class MedSpecOms extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MedStaffFact_model', 'dbmodel');
		$this->inputRules = array(
			'MedSpecOmsByMO' => array(
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'forRecord', 'label' => 'Признак "Для записи"', 'rules' => '', 'type' => 'int'),
			)
		);
	}

	/**
	 * Получение мест работы по МО
	 */
	function MedSpecOmsByMO_get() {
		$data = $this->ProcessInputData('MedSpecOmsByMO');

		$resp = $this->dbmodel->getMedSpecOmsByMo($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}