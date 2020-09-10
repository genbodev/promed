<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class AnalyzerTestWorksheetType
 * @OA\Tag(
 *     name="AnalyzerTestWorksheetType",
 *     description="Связь тестов с типами рабочего списка"
 * )
 */
class AnalyzerTestWorksheetType extends SwREST_Controller {
	protected $inputRules = array(
		'save' => array(
			array('field' => 'AnalyzerTestWorksheetType_id', 'label' => 'AnalyzerTestWorksheetType_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тесты анализаторов', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'Тип рабочего списка', 'rules' => 'required', 'type' => 'int'),
		),
		'load' => array(
			array('field' => 'AnalyzerTestWorksheetType_id', 'label' => 'AnalyzerTestWorksheetType_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerTestWorksheetType_id', 'label' => 'AnalyzerTestWorksheetType_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тесты анализаторов', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'Тип рабочего списка', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerTestWorksheetType_id', 'label' => 'AnalyzerTestWorksheetType_id', 'rules' => 'required', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerTestWorksheetType_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerTestWorksheetType",
	 *  	tags={"AnalyzerTestWorksheetType"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_id",
	 *     					description="Идентификатор типа рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTest_id",
	 *     					"AnalyzerWorksheetType_id"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_post() {
		$data = $this->ProcessInputData('save', null, true);
		if (isset($data['AnalyzerTest_id'])) {
			$this->dbmodel->setAnalyzerTest_id($data['AnalyzerTest_id']);
		}
		if (isset($data['AnalyzerWorksheetType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerTestWorksheetType",
	 *  	tags={"AnalyzerTestWorksheetType"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTestWorksheetType_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_id",
	 *     					description="Идентификатор типа рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTestWorksheetType_id",
	 *     					"AnalyzerTest_id",
	 *     					"AnalyzerWorksheetType_id"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_put() {
		$data = $this->ProcessInputData('save', null, true);
		if (isset($data['AnalyzerTestWorksheetType_id'])) {
			$this->dbmodel->setAnalyzerTestWorksheetType_id($data['AnalyzerTestWorksheetType_id']);
		}
		if (isset($data['AnalyzerTest_id'])) {
			$this->dbmodel->setAnalyzerTest_id($data['AnalyzerTest_id']);
		}
		if (isset($data['AnalyzerWorksheetType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTestWorksheetType",
	 *  	tags={"AnalyzerTestWorksheetType"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_get() {
		$data = $this->ProcessInputData('load', null, true);
		$this->dbmodel->setAnalyzerTestWorksheetType_id($data['AnalyzerTestWorksheetType_id']);
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTestWorksheetType/list",
	 *  	tags={"AnalyzerTestWorksheetType"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа рабочего списка",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function list_get() {
		$data = $this->ProcessInputData('loadList', null, true);
		$response = $this->dbmodel->loadList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerTestWorksheetType",
	 *  	tags={"AnalyzerTestWorksheetType"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTestWorksheetType_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTestWorksheetType_id"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_delete() {
		$data = $this->ProcessInputData('delete', null, true);
		$this->dbmodel->setAnalyzerTestWorksheetType_id($data['AnalyzerTestWorksheetType_id']);
		$response = $this->dbmodel->delete();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}