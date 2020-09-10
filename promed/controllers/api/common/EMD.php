<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EMD
 * @property EMD_model dbmodel
 */
class EMD extends SwRest_Controller {
	protected $inputRules = array(
		'getEMDDocumentListByEvn' => array(
			array('field' => 'EvnClass_SysNick', 'label' => 'Системное имя события', 'rules' => '', 'default' => 'EvnDirection', 'type' => 'string'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EMD_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EMD/DocumentListByEvn",
	 *     	tags={"EMD"},
	 *     	summary="Определяем есть ли в базе РЭМД определенные события\документы РМИС",
	 * 		@OA\Parameter(
	 *     		name="EvnClass_SysNick",
	 *     		in="query",
	 *     		description="Системное имя события",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор события",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DocumentListByEvn_get() {
		$data = $this->ProcessInputData('getEMDDocumentListByEvn', null, true);
		$response = $this->dbmodel->getEMDDocumentListByEvn($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
