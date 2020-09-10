<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property AnalyzerRack_model dbmodel
 * @OA\Tag(
 *     name="AnalyzerRack",
 *     description="Штативы"
 * )
*/
class AnalyzerRack extends SwREST_Controller {
	protected $inputRules = array(
		'save' => array(
			array('field' => 'AnalyzerRack_id', 'label' => 'AnalyzerRack_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerRack_DimensionX', 'label' => 'Размерность по Х', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'AnalyzerRack_DimensionY', 'label' => 'Размерность по Y', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'AnalyzerRack_IsDefault', 'label' => 'По умолчанию', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerRack_Deleted', 'label' => 'AnalyzerRack_Deleted', 'rules' => '', 'type' => 'int'),
		),
		'load' => array(
			array('field' => 'AnalyzerRack_id', 'label' => 'AnalyzerRack_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerRack_id', 'label' => 'AnalyzerRack_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerRack_DimensionX', 'label' => 'Размерность по Х', 'rules' => '', 'type' => 'float'),
			array('field' => 'AnalyzerRack_DimensionY', 'label' => 'Размерность по Y', 'rules' => '', 'type' => 'float'),
			array('field' => 'AnalyzerRack_IsDefault', 'label' => 'По умолчанию', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerRack_Deleted', 'label' => 'AnalyzerRack_Deleted', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerRack_id', 'label' => 'AnalyzerRack_id', 'rules' => 'required', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerRack_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerRack",
	 *  	tags={"AnalyzerRack"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_DimensionX",
	 *     					description="Размерность по X",
	 *     					type="number"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_DimensionY",
	 *     					description="Размерность по Н",
	 *     					type="number"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_IsDefault",
	 *     					description="По умолчанию",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_Deleted",
	 *     					description="Признак удаления",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerModel_id",
	 *     					"AnalyzerRack_DimensionX",
	 *     					"AnalyzerRack_DimensionY",
	 *     					"AnalyzerRack_IsDefault"
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
		if (isset($data['AnalyzerModel_id'])) {
			$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
		}
		if (isset($data['AnalyzerRack_DimensionX'])) {
			$this->dbmodel->setAnalyzerRack_DimensionX($data['AnalyzerRack_DimensionX']);
		}
		if (isset($data['AnalyzerRack_DimensionY'])) {
			$this->dbmodel->setAnalyzerRack_DimensionY($data['AnalyzerRack_DimensionY']);
		}
		if (isset($data['AnalyzerRack_IsDefault'])) {
			$this->dbmodel->setAnalyzerRack_IsDefault($data['AnalyzerRack_IsDefault']);
		}
		if (isset($data['AnalyzerRack_Deleted'])) {
			$this->dbmodel->setAnalyzerRack_Deleted($data['AnalyzerRack_Deleted']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerRack",
	 *  	tags={"AnalyzerRack"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_id",
	 *     					description="Идентификатор штатива",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_DimensionX",
	 *     					description="Размерность по X",
	 *     					type="number"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_DimensionY",
	 *     					description="Размерность по Н",
	 *     					type="number"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_IsDefault",
	 *     					description="По умолчанию",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_Deleted",
	 *     					description="Признак удаления",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerRack_id",
	 *     					"AnalyzerModel_id",
	 *     					"AnalyzerRack_DimensionX",
	 *     					"AnalyzerRack_DimensionY",
	 *     					"AnalyzerRack_IsDefault"
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
		if (isset($data['AnalyzerRack_id'])) {
			$this->dbmodel->setAnalyzerRack_id($data['AnalyzerRack_id']);
		}
		if (isset($data['AnalyzerModel_id'])) {
			$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
		}
		if (isset($data['AnalyzerRack_DimensionX'])) {
			$this->dbmodel->setAnalyzerRack_DimensionX($data['AnalyzerRack_DimensionX']);
		}
		if (isset($data['AnalyzerRack_DimensionY'])) {
			$this->dbmodel->setAnalyzerRack_DimensionY($data['AnalyzerRack_DimensionY']);
		}
		if (isset($data['AnalyzerRack_IsDefault'])) {
			$this->dbmodel->setAnalyzerRack_IsDefault($data['AnalyzerRack_IsDefault']);
		}
		if (isset($data['AnalyzerRack_Deleted'])) {
			$this->dbmodel->setAnalyzerRack_Deleted($data['AnalyzerRack_Deleted']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerRack",
	 *  	tags={"AnalyzerRack"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerRack_id",
	 *     		in="query",
	 *     		description="Идентификатор штатива",
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
		$this->dbmodel->setAnalyzerRack_id($data['AnalyzerRack_id']);
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerRack/list",
	 *  	tags={"AnalyzerRack"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerRack_id",
	 *     		in="query",
	 *     		description="Идентификатор штатива",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerRack_DimensionX",
	 *     		in="query",
	 *     		description="Размерность по Х",
	 *     		@OA\Schema(type="number")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerRack_DimensionY",
	 *     		in="query",
	 *     		description="Размерность по Y",
	 *     		@OA\Schema(type="number")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerRack_IsDefault",
	 *     		in="query",
	 *     		description="По умолчанию",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerRack_Deleted",
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
	 * @OA\Delete(
	 *     	path="/api/AnalyzerRack",
	 *  	tags={"AnalyzerRack"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_id",
	 *     					description="Идентификатор штатива",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerRack_id"
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
		$this->dbmodel->setAnalyzerRack_id($data['AnalyzerRack_id']);
		$response = $this->dbmodel->delete();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}