<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Common
 * @property Common_model dbmodel
 * @OA\Tag(
 *     name="Common",
 *     description="Общее"
 * )
 */
class Common extends SwRest_Controller {
	protected $inputRules = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Common_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Common/DateTime",
	 *  	tags={"Common"},
	 *	    summary="Получение текущего времени",
	 *  	@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DateTime_get() {
		$response = $this->dbmodel->getCurrentDateTime();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
