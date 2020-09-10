<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class AnalyzerWorksheetEvnLabSample
 * @OA\Tag(
 *     name="AnalyzerWorksheetEvnLabSample",
 *     description="Список проб рабочего списка"
 * )
 */
class AnalyzerWorksheetEvnLabSample extends SwREST_Controller {
	protected $inputRules = array(
		'saveBulk' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Рабочий список', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'PickedEvnLabSamples', 'label' => 'Выбранные пробы', 'rules' => 'required', 'type' => 'string'),
		),
		'save' => array(
			array('field' => 'AnalyzerWorksheetEvnLabSample_id', 'label' => 'AnalyzerWorksheetEvnLabSample_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Рабочий список', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnLabSample_id', 'label' => 'Проба на лабораторное исследование', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetEvnLabSample_X', 'label' => 'Координата расположения пробы по оси X', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheetEvnLabSample_Y', 'label' => 'Координата расположения пробы по оси Y', 'rules' => 'required', 'type' => 'string'),
		),
		'load' => array(
			array('field' => 'AnalyzerWorksheetEvnLabSample_id', 'label' => 'AnalyzerWorksheetEvnLabSample_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerWorksheetEvnLabSample_id', 'label' => 'AnalyzerWorksheetEvnLabSample_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Рабочий список', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnLabSample_id', 'label' => 'Проба на лабораторное исследование', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetEvnLabSample_X', 'label' => 'Координата расположения пробы по оси X', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheetEvnLabSample_Y', 'label' => 'Координата расположения пробы по оси Y', 'rules' => '', 'type' => 'string'),
		),
		'delete' => array(
			array('field' => 'AnalyzerWorksheetEvnLabSample_id', 'label' => 'AnalyzerWorksheetEvnLabSample_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadMatrix' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'AnalyzerWorksheet_id', 'rules' => 'required', 'type' => 'int'),
		),
		'clearMatrix' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'AnalyzerWorksheet_id', 'rules' => 'required', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerWorksheetEvnLabSample_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerWorksheetEvnLabSample",
	 *  	tags={"AnalyzerWorksheetEvnLabSample"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_id",
	 *     					description="Идентификатор рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetEvnLabSample_X",
	 *     					description="Координата расположения пробы по оси X",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetEvnLabSample_Y",
	 *     					description="Координата расположения пробы по оси Y",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheet_id",
	 *     					"EvnLabSample_id",
	 *     					"AnalyzerWorksheetEvnLabSample_X",
	 *     					"AnalyzerWorksheetEvnLabSample_Y"
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
		if (isset($data['AnalyzerWorksheet_id'])) {
			$this->dbmodel->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
		}
		if (isset($data['EvnLabSample_id'])) {
			$this->dbmodel->setEvnLabSample_id($data['EvnLabSample_id']);
		}
		if (isset($data['AnalyzerWorksheetEvnLabSample_X'])) {
			$this->dbmodel->setAnalyzerWorksheetEvnLabSample_X($data['AnalyzerWorksheetEvnLabSample_X']);
		}
		if (isset($data['AnalyzerWorksheetEvnLabSample_Y'])) {
			$this->dbmodel->setAnalyzerWorksheetEvnLabSample_Y($data['AnalyzerWorksheetEvnLabSample_Y']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerWorksheetEvnLabSample",
	 *  	tags={"AnalyzerWorksheetEvnLabSample"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetEvnLabSample_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_id",
	 *     					description="Идентификатор рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetEvnLabSample_X",
	 *     					description="Координата расположения пробы по оси X",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetEvnLabSample_Y",
	 *     					description="Координата расположения пробы по оси Y",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheetEvnLabSample_id",
	 *     					"AnalyzerWorksheet_id",
	 *     					"EvnLabSample_id",
	 *     					"AnalyzerWorksheetEvnLabSample_X",
	 *     					"AnalyzerWorksheetEvnLabSample_Y"
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
		if (isset($data['AnalyzerWorksheetEvnLabSample_id'])) {
			$this->dbmodel->setAnalyzerWorksheetEvnLabSample_id($data['AnalyzerWorksheetEvnLabSample_id']);
		}
		if (isset($data['AnalyzerWorksheet_id'])) {
			$this->dbmodel->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
		}
		if (isset($data['EvnLabSample_id'])) {
			$this->dbmodel->setEvnLabSample_id($data['EvnLabSample_id']);
		}
		if (isset($data['AnalyzerWorksheetEvnLabSample_X'])) {
			$this->dbmodel->setAnalyzerWorksheetEvnLabSample_X($data['AnalyzerWorksheetEvnLabSample_X']);
		}
		if (isset($data['AnalyzerWorksheetEvnLabSample_Y'])) {
			$this->dbmodel->setAnalyzerWorksheetEvnLabSample_Y($data['AnalyzerWorksheetEvnLabSample_Y']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Массовое заполнение ячеек штатива рабочего списка пробами
	 */
	function bulk_post() {
		$data = $this->ProcessInputData('saveBulk', null, true);
		$response = $this->dbmodel->saveBulk($data['AnalyzerWorksheet_id'], $data['PickedEvnLabSamples']);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerWorksheetEvnLabSample",
	 *  	tags={"AnalyzerWorksheetEvnLabSample"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetEvnLabSample_id",
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
		$this->dbmodel->setAnalyzerWorksheetEvnLabSample_id($data['AnalyzerWorksheetEvnLabSample_id']);
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * array('field' => 'AnalyzerWorksheetEvnLabSample_id', 'label' => 'AnalyzerWorksheetEvnLabSample_id', 'rules' => '', 'type' => 'int'),
	array('field' => 'AnalyzerWorksheet_id', 'label' => 'Рабочий список', 'rules' => '', 'type' => 'int'),
	array('field' => 'EvnLabSample_id', 'label' => 'Проба на лабораторное исследование', 'rules' => '', 'type' => 'int'),
	array('field' => 'AnalyzerWorksheetEvnLabSample_X', 'label' => 'Координата расположения пробы по оси X', 'rules' => '', 'type' => 'string'),
	array('field' => 'AnalyzerWorksheetEvnLabSample_Y', 'label' => 'Координата расположения пробы по оси Y', 'rules' => '', 'type' => 'string'),
	 *
	 * @OA\Get(
	 *     	path="/api/AnalyzerWorksheetEvnLabSample/list",
	 *  	tags={"AnalyzerWorksheetEvnLabSample"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetEvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheet_id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего списка",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetEvnLabSample_X",
	 *     		in="query",
	 *     		description="Координата расположения пробы по оси X",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetEvnLabSample_Y",
	 *     		in="query",
	 *     		description="Координата расположения пробы по оси Y",
	 *     		@OA\Schema(type="string")
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
	 *     	path="/api/AnalyzerWorksheetEvnLabSample/Matrix",
	 *  	tags={"AnalyzerWorksheetEvnLabSample"},
	 *	    summary="Получение матрицы проб для рабочего списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheet_id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего списка",
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
	function Matrix_get() {
		$data = $this->ProcessInputData('loadMatrix', null, true);
		$response = $this->dbmodel->loadMatrix($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerWorksheetEvnLabSample/Matrix",
	 *  	tags={"AnalyzerWorksheetEvnLabSample"},
	 *	    summary="Очищение матрицы проб рабочего списка",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_id",
	 *     					description="Идентификатор рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheet_id"
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
	function Matrix_delete() {
		$data = $this->ProcessInputData('clearMatrix', null, true);
		$response = $this->dbmodel->clearMatrix($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerWorksheetEvnLabSample",
	 *  	tags={"AnalyzerWorksheetEvnLabSample"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetEvnLabSample_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheetEvnLabSample_id"
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
		$this->dbmodel->setAnalyzerWorksheetEvnLabSample_id($data['AnalyzerWorksheetEvnLabSample_id']);
		$response = $this->dbmodel->delete();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}