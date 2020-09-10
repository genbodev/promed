<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class AnalyzerWorksheet
 * @OA\Tag(
 *     name="AnalyzerWorksheet",
 *     description="Рабочие списки"
 * )
 */
class AnalyzerWorksheet extends SwREST_Controller {
	protected $inputRules = array(
		'getDailyCount' => array(
			array('field' => 'gendate', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'),
		),
		'save' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheet_Code', 'label' => 'Код', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheet_Name', 'label' => 'Наименование', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheet_setDT', 'label' => 'Дата создания', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'AnalyzerRack_id', 'label' => 'Штатив', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetStatusType_id', 'label' => 'Статус рабочего списка', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'Тип рабочих списков', 'rules' => '', 'type' => 'int'),
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'int'),
		),
		'load' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
		),
		'work' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
		),
		'setStatus' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AnalyzerWorksheetStatusType_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheet_Code', 'label' => 'Код', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheet_Name', 'label' => 'Наименование', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerWorksheet_setDT', 'label' => 'Дата создания', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'AnalyzerRack_id', 'label' => 'Штатив', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetStatusType_id', 'label' => 'Статус рабочего списка', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_id', 'label' => 'Тип рабочих списков', 'rules' => '', 'type' => 'int'),
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		//$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerWorksheet_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerWorksheet",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_Code",
	 *     					description="Код",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_Name",
	 *     					description="Наименование",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_setDT",
	 *     					description="Дата и время создания",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_id",
	 *     					description="Идентификатор штатива",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetStatusType_id",
	 *     					description="Идентификатора статуса рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_id",
	 *     					description="Идентификатора типа рабочих списков",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатора анализатора",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheet_Code",
	 *     					"AnalyzerWorksheet_Name"
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
		$data = $this->ProcessInputData('save');
		if (isset($data['AnalyzerWorksheet_Code'])) {
			$this->dbmodel->setAnalyzerWorksheet_Code($data['AnalyzerWorksheet_Code']);
		}
		if (isset($data['AnalyzerWorksheet_Name'])) {
			$this->dbmodel->setAnalyzerWorksheet_Name($data['AnalyzerWorksheet_Name']);
		}
		if (isset($data['AnalyzerWorksheet_setDT'])) {
			$this->dbmodel->setAnalyzerWorksheet_setDT($data['AnalyzerWorksheet_setDT']);
		}
		if (isset($data['AnalyzerRack_id'])) {
			$this->dbmodel->setAnalyzerRack_id($data['AnalyzerRack_id']);
		}
		if (isset($data['AnalyzerWorksheetStatusType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetStatusType_id($data['AnalyzerWorksheetStatusType_id']);
		}
		if (isset($data['AnalyzerWorksheetType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
		}
		if (isset($data['Analyzer_id'])) {
			$this->dbmodel->setAnalyzer_id($data['Analyzer_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerWorksheet",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_Code",
	 *     					description="Код",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_Name",
	 *     					description="Наименование",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_setDT",
	 *     					description="Дата и время создания",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerRack_id",
	 *     					description="Идентификатор штатива",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetStatusType_id",
	 *     					description="Идентификатора статуса рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetType_id",
	 *     					description="Идентификатора типа рабочих списков",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатора анализатора",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheet_id",
	 *     					"AnalyzerWorksheet_Code",
	 *     					"AnalyzerWorksheet_Name"
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
		$data = $this->ProcessInputData('save');
		if (isset($data['AnalyzerWorksheet_id'])) {
			$this->dbmodel->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
		}
		if (isset($data['AnalyzerWorksheet_Code'])) {
			$this->dbmodel->setAnalyzerWorksheet_Code($data['AnalyzerWorksheet_Code']);
		}
		if (isset($data['AnalyzerWorksheet_Name'])) {
			$this->dbmodel->setAnalyzerWorksheet_Name($data['AnalyzerWorksheet_Name']);
		}
		if (isset($data['AnalyzerWorksheet_setDT'])) {
			$this->dbmodel->setAnalyzerWorksheet_setDT($data['AnalyzerWorksheet_setDT']);
		}
		if (isset($data['AnalyzerRack_id'])) {
			$this->dbmodel->setAnalyzerRack_id($data['AnalyzerRack_id']);
		}
		if (isset($data['AnalyzerWorksheetStatusType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetStatusType_id($data['AnalyzerWorksheetStatusType_id']);
		}
		if (isset($data['AnalyzerWorksheetType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
		}
		if (isset($data['Analyzer_id'])) {
			$this->dbmodel->setAnalyzer_id($data['Analyzer_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     	path="/api/AnalyzerWorksheet/Status",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="Изменение статуса",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetStatusType_id",
	 *     					description="Идентификатора статуса рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerWorksheet_id",
	 *     					"AnalyzerWorksheetStatusType_id"
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
	function Status_patch() {
		$data = $this->ProcessInputData('setStatus');
		$this->dbmodel->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
		$response = $this->dbmodel->setStatus($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerWorksheet/work",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="В работу",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_id",
	 *     					description="Идентификатор",
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
	function work_post() {
		$data = $this->ProcessInputData('work');
		$this->dbmodel->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
		$response = $this->dbmodel->work($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerWorksheet",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheet_id",
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
		$data = $this->ProcessInputData('load');
		$this->dbmodel->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerWorksheet/DailyCount",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="Получение номера",
	 *     	@OA\Parameter(
	 *     		name="gendate",
	 *     		in="query",
	 *     		description="Дата",
	 *     		required=true,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DailyCount_get() {
		$data = $this->ProcessInputData('getDailyCount');
		$response = $this->dbmodel->getDailyCount($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerWorksheet/list",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheet_id",
	 *     		in="query",
	 *     		description="Идентификатор",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheet_Code",
	 *     		in="query",
	 *     		description="Код",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheet_Name",
	 *     		in="query",
	 *     		description="Наименование",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheet_setDT",
	 *     		in="query",
	 *     		description="Дата создания",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerRack_id",
	 *     		in="query",
	 *     		description="Идентификатор штатива",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetStatusType_id",
	 *     		in="query",
	 *     		description="Идентификатор статуса рабочего списка",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа рабочего списка",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
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
		$data = $this->ProcessInputData('loadList');
		$response = $this->dbmodel->loadList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerWorksheet",
	 *  	tags={"AnalyzerWorksheet"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheet_id",
	 *     					description="Идентификатор",
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
	function index_delete() {
		$data = $this->ProcessInputData('delete');
		$this->dbmodel->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
		$response = $this->dbmodel->delete();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}