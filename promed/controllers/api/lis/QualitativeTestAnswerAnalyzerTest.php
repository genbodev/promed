<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property QualitativeTestAnswerAnalyzerTest_model dbmodel
 * Class QualitativeTestAnswerAnalyzerTest
 * @OA\Tag(
 *     name="QualitativeTestAnswerAnalyzerTest",
 *     description="Соответствия конкретных ответов конкретному качественному тесту"
 * )
 */
class QualitativeTestAnswerAnalyzerTest extends SwRest_Controller {
	protected $inputRules = array(
		'save' => array(
			array('field' => 'QualitativeTestAnswerAnalyzerTest_id', 'label' => 'QualitativeTestAnswerAnalyzerTest_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'QualitativeTestAnswerAnalyzerTest_Answer', 'label' => 'Вариант ответа', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тесты анализаторов', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'QualitativeTestAnswerAnalyzerTest_SortCode', 'label' => 'Приоритет отображения ответов', 'rules' => '', 'type' => 'int')
		),
		'load' => array(
			array('field' => 'QualitativeTestAnswerAnalyzerTest_id', 'label' => 'QualitativeTestAnswerAnalyzerTest_id', 'rules' => 'required', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'QualitativeTestAnswerAnalyzerTest_id', 'label' => 'QualitativeTestAnswerAnalyzerTest_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'QualitativeTestAnswerAnalyzerTest_Answer', 'label' => 'Вариант ответа', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тесты анализаторов', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTestRefValues_id', 'label' => 'Тесты анализаторов', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaTest_id', 'label' => 'Тест', 'rules' => '', 'type' => 'id'),
			array('field' => 'QualitativeTestAnswerAnalyzerTest_SortCode', 'label' => 'Приоритет отображения ответов', 'rules' => '', 'type' => 'int')
		),
		'delete' => array(
			array('field' => 'QualitativeTestAnswerAnalyzerTest_id', 'label' => 'QualitativeTestAnswerAnalyzerTest_id', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('QualitativeTestAnswerAnalyzerTest_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/QualitativeTestAnswerAnalyzerTest",
	 *  	tags={"QualitativeTestAnswerAnalyzerTest"},
	 *	    summary="Сохранение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="QualitativeTestAnswerAnalyzerTest_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="QualitativeTestAnswerAnalyzerTest_Answer",
	 *     					description="Вариант ответа",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_id",
	 *     					description="Идентификатор теста анализатора",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"QualitativeTestAnswerAnalyzerTest_id",
	 *     					"QualitativeTestAnswerAnalyzerTest_Answer",
	 *     					"AnalyzerTest_id"
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
	 *     	path="/api/QualitativeTestAnswerAnalyzerTest",
	 *  	tags={"QualitativeTestAnswerAnalyzerTest"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="QualitativeTestAnswerAnalyzerTest_id",
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
	 *     	path="/api/QualitativeTestAnswerAnalyzerTest/list",
	 *  	tags={"QualitativeTestAnswerAnalyzerTest"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="QualitativeTestAnswerAnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="QualitativeTestAnswerAnalyzerTest_Answer",
	 *     		in="query",
	 *     		description="Вариант ответа",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestRefValues_id",
	 *     		in="query",
	 *     		description="Идентификатор теста анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста",
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
	 *     	path="/api/QualitativeTestAnswerAnalyzerTest",
	 *  	tags={"QualitativeTestAnswerAnalyzerTest"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="QualitativeTestAnswerAnalyzerTest_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				required={
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
	function index_delete() {
		$data = $this->ProcessInputData('delete', null, true);
		$response = $this->dbmodel->delete($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}