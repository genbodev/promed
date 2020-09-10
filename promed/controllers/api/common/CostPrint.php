<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class CostPrint
 * @property CostPrint_model $dbmodel
 */
class CostPrint extends SwRest_Controller {
	protected $inputRules = array(
		'saveEvnCostPrint' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'CostPrint_IsNoPrint', 'label' => 'Признак отказа от печати справки', 'rules' => '', 'type' => 'id'),
			array('field' => 'CostPrint_setDT', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'),
		),
	);
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('CostPrint_model', 'dbmodel');
	}
	
	function index_post() {
		$data = $this->ProcessInputData('saveEvnCostPrint', null, true);
		$response = $this->dbmodel->saveEvnCostPrint($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}