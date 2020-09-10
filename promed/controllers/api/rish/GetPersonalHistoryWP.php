<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * GetPersonalHistoryWP - контроллер API для работы с КВС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class GetPersonalHistoryWP extends SwREST_Controller {
	protected $inputRules = array(
		'getGetPersonalHistoryWP' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'WorkPlace_id', 'label' => 'Идентификатор записи "Место работы"', 'rules' => '', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Ersb_model', 'dbmodel');
	}

	/**
	 * Получение данных
	 */
	function index_get() {
		$data = $this->ProcessInputData('getGetPersonalHistoryWP');

		$resp = $this->dbmodel->getGetPersonalHistoryWPForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}