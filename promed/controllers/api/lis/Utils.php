<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Utils
 * @property Utils_model dbmodel
 * @OA\Tag(
 *     name="Utils",
 *     description="утилиты"
 * )
 */
class Utils extends SwRest_Controller {
	protected $inputRules = array(
		'GetObjectList' => array(
			array('field' => 'filterLpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id')
		),
		'withFileIntegration' => array(
			array('field' => 'MedService_id','label' => 'Идентификатор мед. службы','rules' => 'required','type' => 'int')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_Utils_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Utils/List",
	 *  	tags={"Utils"},
	 *	    summary="Получение списка ЛПУ",
	 *     	@OA\Parameter(
	 *     		name="filterLpu_id",
	 *     		in="query",
	 *     		description="Идентификатор МО",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function List_get() {
		$data = $this->ProcessInputData('getOrgList', null, true);
		$response = $this->dbmodel->getLpuList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Utils/withFileIntegration",
	 *  	tags={"Utils"},
	 *	    summary="Получение списка ЛПУ",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор мед. службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function withFileIntegration_get() {
		$data = $this->ProcessInputData('withFileIntegration', null, true);
		$response = $this->dbmodel->withFileIntegration($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * тестирование работы внутренних сервисов
	 */
	function TestSwServiceLis_get() {
		$this->response(array('error_code' => 0, 'data' => array('success' => true)));
	}

	/**
	 * тестирование работы внутренних сервисов
	 */
	function TestSwServiceLis_post() {
		$this->response(array('error_code' => 0, 'data' => array('success' => true)));
	}
}
