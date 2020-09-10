<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class AnalyzerWorksheetType
 * @OA\Tag(
 *     name="AnalyzerWorksheetType",
 *     description="Тип рабочих списков"
 * )
 */
class AnalyzerWorksheetType extends SwREST_Controller {
	protected $inputRules = array(
		'save' => array(
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'AnalyzerWorksheetType_id', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_Code', 'label' => 'Код', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheetType_Name', 'label' => 'Наименование', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => '', 'type' => 'int'),
		),
		'load' => array(
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'AnalyzerWorksheetType_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'AnalyzerWorksheetType_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_Code', 'label' => 'AnalyzerWorksheetType_Code', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheetType_Name', 'label' => 'AnalyzerWorksheetType_Name', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerModel_id', 'label' => 'модель анализатора', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'AnalyzerWorksheetType_id', 'rules' => 'required', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerWorksheetType_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerWorksheetType",
	 *  	tags={"AnalyzerWorksheetType"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_Code",
	 *     					description="Код типа рабочего списка",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_Name",
	 *     					description="Наименование типа рабочего списка",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheetType_Code",
	 *     					"AnalyzerWorksheetType_Name"
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
		if (isset($data['AnalyzerWorksheetType_Code'])) {
			$this->dbmodel->setAnalyzerWorksheetType_Code($data['AnalyzerWorksheetType_Code']);
		}
		if (isset($data['AnalyzerWorksheetType_Name'])) {
			$this->dbmodel->setAnalyzerWorksheetType_Name($data['AnalyzerWorksheetType_Name']);
		}
		if (isset($data['AnalyzerModel_id'])) {
			$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerWorksheetType",
	 *  	tags={"AnalyzerWorksheetType"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_id",
	 *     					description="Идентификатор типа рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_Code",
	 *     					description="Код типа рабочего списка",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_Name",
	 *     					description="Наименование типа рабочего списка",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheetType_id",
	 *     					"AnalyzerWorksheetType_Code",
	 *     					"AnalyzerWorksheetType_Name"
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
		if (isset($data['AnalyzerWorksheetType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
		}
		if (isset($data['AnalyzerWorksheetType_Code'])) {
			$this->dbmodel->setAnalyzerWorksheetType_Code($data['AnalyzerWorksheetType_Code']);
		}
		if (isset($data['AnalyzerWorksheetType_Name'])) {
			$this->dbmodel->setAnalyzerWorksheetType_Name($data['AnalyzerWorksheetType_Name']);
		}
		if (isset($data['AnalyzerModel_id'])) {
			$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerWorksheetType",
	 *  	tags={"AnalyzerWorksheetType"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа рабочего сиска",
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
		$this->dbmodel->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerWorksheetType/list",
	 *  	tags={"AnalyzerWorksheetType"},
	 *	    summary="Получение сприска",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа рабочего сиска",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_Code",
	 *     		in="query",
	 *     		description="Код типа рабочего списка",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_Name",
	 *     		in="query",
	 *     		description="Наименование типа рабочего списка",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
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
	 *     	path="/api/AnalyzerWorksheetType",
	 *  	tags={"AnalyzerWorksheetType"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_id",
	 *     					description="Идентификатор типа рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				required={
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
	function index_delete() {
		$data = $this->ProcessInputData('delete', null, true);
		$this->dbmodel->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
		$response = $this->dbmodel->delete();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}