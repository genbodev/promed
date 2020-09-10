<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class QuantitativeTestUnit
 * @OA\Tag(
 *     name="QuantitativeTestUnit",
 *     description="Единицы измерений количественных тестов"
 * )
 *
 * @property QuantitativeTestUnit_model dbmodel
 */
class QuantitativeTestUnit extends SwRest_Controller {
	protected $inputRules = array(
		'loadCoeff' => array(
			array('field' => 'AnalyzerTest_id', 'label' => 'Тест анализатора', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Unit_id', 'label' => 'Единица измерения', 'rules' => 'required', 'type' => 'id')
		),
		'save' => array(
			array('field' => 'QuantitativeTestUnit_id', 'label' => 'QuantitativeTestUnit_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'Unit_id', 'label' => 'Наименование', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'QuantitativeTestUnit_IsBase', 'label' => 'Базовая', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'QuantitativeTestUnit_CoeffEnum', 'label' => 'Коэффициент пересчета', 'rules' => '', 'type' => 'float'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тест анализатора', 'rules' => 'required', 'type' => 'int')
		),
		'load' => array(
			array('field' => 'QuantitativeTestUnit_id', 'label' => 'QuantitativeTestUnit_id', 'rules' => 'required', 'type' => 'int')
		),
		'loadList' => array(
			array('field' => 'AnalyzerTest_id', 'label' => 'Тест анализатора', 'rules' => '', 'type' => 'int')
		),
		'delete' => array(
			array('field' => 'QuantitativeTestUnit_id', 'label' => 'QuantitativeTestUnit_id', 'rules' => 'required', 'type' => 'int')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('QuantitativeTestUnit_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/QuantitativeTestUnit",
	 *  	tags={"QuantitativeTestUnit"},
	 *	    summary="Сохранение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="QuantitativeTestUnit_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Unit_id",
	 *     					description="Идентификатор единицы измерения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="QuantitativeTestUnit_IsBase",
	 *     					description="Признак базовой единицы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="QuantitativeTestUnit_CoeffEnum",
	 *     					description="Коэффициент пересчета",
	 *     					type="number"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_id",
	 *     					description="Идентификатор теста анализатора",
	 *     					type="interger"
	 * 					),
	 *     				required={
	 *     					"Unit_id",
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
	 *     	path="/api/QuantitativeTestUnit",
	 *  	tags={"QuantitativeTestUnit"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="QuantitativeTestUnit_id",
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
	 *     	path="/api/QuantitativeTestUnit/list",
	 *  	tags={"QuantitativeTestUnit"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста анализатора",
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

	function coeff_get() {
		$data = $this->ProcessInputData('loadCoeff', null, true);
		$response = $this->dbmodel->loadCoeff($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/QuantitativeTestUnit",
	 *  	tags={"QuantitativeTestUnit"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="QuantitativeTestUnit_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"QuantitativeTestUnit_id"
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