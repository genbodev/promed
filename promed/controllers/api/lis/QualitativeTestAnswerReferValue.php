<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class QualitativeTestAnswerReferValue
 * @OA\Tag(
 *     name="QualitativeTestAnswerReferValue",
 *     description="Референсныне значения для ответов на качественные тесты"
 * )
 */
class QualitativeTestAnswerReferValue extends SwRest_Controller {
	protected $inputRules = array(
		'save' => array(
			array('field' => 'QualitativeTestAnswerReferValue_id', 'label' => 'QualitativeTestAnswerReferValue_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTestRefValues_id', 'label' => 'Референсное значение теста', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'QualitativeTestAnswerAnalyzerTest_id', 'label' => 'Вариант ответа', 'rules' => 'required', 'type' => 'id')
		),
		'load' => array(
			array('field' => 'QualitativeTestAnswerReferValue_id', 'label' => 'QualitativeTestAnswerReferValue_id', 'rules' => 'required', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'QualitativeTestAnswerReferValue_id', 'label' => 'QualitativeTestAnswerReferValue_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTestRefValues_id', 'label' => 'Референсное значение теста', 'rules' => 'required', 'type' => 'id')
		),
		'delete' => array(
			array('field' => 'QualitativeTestAnswerReferValue_id', 'label' => 'QualitativeTestAnswerReferValue_id', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('QualitativeTestAnswerReferValue_model', 'dbmodel');
	}

	/**
	 * Сохранение
	 */
	/**
	 * @OA\Post(
	 *     	path="/api/QualitativeTestAnswerReferValue",
	 *  	tags={"QualitativeTestAnswerReferValue"},
	 *	    summary="Сохранение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="QualitativeTestAnswerReferValue_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTestRefValues_id",
	 *     					description="Идентификатор референсного значения теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="QualitativeTestAnswerAnalyzerTest_id",
	 *     					description="Идентификатор варианта ответа",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"QualitativeTestAnswerReferValue_id",
	 *     					"AnalyzerTestRefValues_id",
	 *     					"QualitativeTestAnswerAnalyzerTest_id"
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
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/QualitativeTestAnswerReferValue",
	 *  	tags={"QualitativeTestAnswerReferValue"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="QualitativeTestAnswerReferValue_id",
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
		$response = $this->dbmodel->load($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/QualitativeTestAnswerReferValue/list",
	 *  	tags={"QualitativeTestAnswerReferValue"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="QualitativeTestAnswerReferValue_id",
	 *     		in="query",
	 *     		description="Идентификатор",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestRefValues_id",
	 *     		in="query",
	 *     		description="Идентификатор референсного значения теста",
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
	 *     	path="/api/QualitativeTestAnswerReferValue",
	 *  	tags={"QualitativeTestAnswerReferValue"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="QualitativeTestAnswerReferValue_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"QualitativeTestAnswerReferValue_id"
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
		$response = $this->dbmodel->delete($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}