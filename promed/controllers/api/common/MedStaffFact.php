<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class MedStaffFact
 * @property MedStaffFact_model dbmodel
 */
class MedStaffFact extends SwRest_Controller {
	protected $inputRules = array(
		'getMedStaffFact' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => 'required', 'type' => 'id'),
		),
		'getMSFData' => array(
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение МО', 'rules' => '', 'type' => 'id'),
		),
		'getMedStaffFactId' => array(
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => '', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MedStaffFact_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/MedStaffFact",
	 *     	tags={"MedStaffFact"},
	 *     	summary="Получение данных места работы врача",
	 * 		@OA\Parameter(
	 *     		name="MedStaffFact_id",
	 *     		in="query",
	 *     		description="Идентификатор места работы врача",
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
	function index_get() {
		$data = $this->ProcessInputData('getMedStaffFact', null, true);
		$response = $this->dbmodel->getMedStaffFact($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/MedStaffFact/msfData",
	 *     	tags={"MedStaffFact"},
	 *     	summary="Определение открытого рабочего места врача",
	 * 		@OA\Parameter(
	 *     		name="MedPersonal_id",
	 *     		in="query",
	 *     		description="Идентификатор врача",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="LpuSection_id",
	 *     		in="query",
	 *     		description="Идентификатор отделения МО",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function msfData_get() {
		$data = $this->ProcessInputData('getMSFData', null);
		$response = $this->dbmodel->getMSFData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/MedStaffFact/Id",
	 *     	tags={"MedStaffFact"},
	 *     	summary="Получение идентификатора рабочего места",
	 * 		@OA\Parameter(
	 *     		name="MedPersonal_id",
	 *     		in="query",
	 *     		description="Идентификатор врача",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="LpuSection_id",
	 *     		in="query",
	 *     		description="Идентификатор отделения МО",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="Post_id",
	 *     		in="query",
	 *     		description="Идентификатор должности",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Id_get() {
		$data = $this->ProcessInputData('getMedStaffFactId', null);
		$response = $this->dbmodel->getMedStaffFactByParams($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
