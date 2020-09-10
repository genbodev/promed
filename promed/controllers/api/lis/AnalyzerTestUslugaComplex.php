<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class AnalyzerTestUslugaComplex
 * @OA\Tag(
 *     name="AnalyzerTestUslugaComplex",
 *     description="Связь тестов с услугами"
 * )
 */
class AnalyzerTestUslugaComplex extends SwREST_Controller {
	protected  $inputRules = array(
		'save' => array(
			array('field' => 'AnalyzerTestUslugaComplex_id', 'label' => 'AnalyzerTestUslugaComplex_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тесты анализаторов', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'UslugaComplex_id', 'label' => 'Комплексная услуга', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerTestUslugaComplex_Deleted', 'label' => 'AnalyzerTestUslugaComplex_Deleted', 'rules' => '', 'type' => 'int'),
		),
		'load' => array(
			array('field' => 'AnalyzerTestUslugaComplex_id', 'label' => 'AnalyzerTestUslugaComplex_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadAlowedUslugaCategory_SysNicks' => array(
			array('field' => 'AnalyzerTest_id', 'label' => 'AnalyzerTest_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerTestUslugaComplex_id', 'label' => 'AnalyzerTestUslugaComplex_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тесты анализаторов', 'rules' => '', 'type' => 'int'),
			array('field' => 'UslugaComplex_id', 'label' => 'Комплексная услуга', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTestUslugaComplex_Deleted', 'label' => 'AnalyzerTestUslugaComplex_Deleted', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerTestUslugaComplex_id', 'label' => 'AnalyzerTestUslugaComplex_id', 'rules' => 'required', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerTestUslugaComplex_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerTestUslugaComplex",
	 *  	tags={"AnalyzerTestUslugaComplex"},
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
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTestUslugaComplex_Deleted",
	 *     					description="Признак удаления",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTest_id",
	 *     					"UslugaComplex_id"
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
		if (isset($data['UslugaComplex_id'])) {
			$this->dbmodel->setUslugaComplex_id($data['UslugaComplex_id']);
		}
		if (isset($data['AnalyzerTestUslugaComplex_Deleted'])) {
			$this->dbmodel->setAnalyzerTestUslugaComplex_Deleted($data['AnalyzerTestUslugaComplex_Deleted']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerTestUslugaComplex",
	 *  	tags={"AnalyzerTestUslugaComplex"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTestUslugaComplex_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTestUslugaComplex_Deleted",
	 *     					description="Признак удаления",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTestUslugaComplex_id",
	 *     					"AnalyzerTest_id",
	 *     					"UslugaComplex_id"
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
		if (isset($data['AnalyzerTestUslugaComplex_id'])) {
			$this->dbmodel->setAnalyzerTestUslugaComplex_id($data['AnalyzerTestUslugaComplex_id']);
		}
		if (isset($data['AnalyzerTest_id'])) {
			$this->dbmodel->setAnalyzerTest_id($data['AnalyzerTest_id']);
		}
		if (isset($data['UslugaComplex_id'])) {
			$this->dbmodel->setUslugaComplex_id($data['UslugaComplex_id']);
		}
		if (isset($data['AnalyzerTestUslugaComplex_Deleted'])) {
			$this->dbmodel->setAnalyzerTestUslugaComplex_Deleted($data['AnalyzerTestUslugaComplex_Deleted']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTestUslugaComplex",
	 *  	tags={"AnalyzerTestUslugaComplex"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestUslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор",
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
	function index_get() {
		$data = $this->ProcessInputData('load', null, true);
		$this->dbmodel->setAnalyzerTestUslugaComplex_id($data['AnalyzerTestUslugaComplex_id']);
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTestUslugaComplex/list",
	 *  	tags={"AnalyzerTestUslugaComplex"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestUslugaComplex_id",
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
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestUslugaComplex_Deleted",
	 *     		in="query",
	 *     		description="Признак удаления",
	 *     		@OA\Schema(type="integer")
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
	 * @OA\Get(
	 *     	path="/api/AnalyzerTestUslugaComplex/AlowedUslugaCategorySysNicks",
	 *  	tags={"AnalyzerTestUslugaComplex"},
	 *	    summary="Получение категория услуг",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста",
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
	function AlowedUslugaCategorySysNicks_get() {
		$data = $this->ProcessInputData('loadAlowedUslugaCategory_SysNicks', null, true);
		$response = $this->dbmodel->loadAlowedUslugaCategory_SysNicks($data['AnalyzerTest_id']);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerTestUslugaComplex",
	 *  	tags={"AnalyzerTestUslugaComplex"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTestUslugaComplex_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTestUslugaComplex_id"
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
		$this->dbmodel->setAnalyzerTestUslugaComplex_id($data['AnalyzerTestUslugaComplex_id']);
		$response = $this->dbmodel->delete($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}