<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class AnalyzerControlSeries
 * @OA\Tag(
 *     name="AnalyzerControlSeries",
 *     description="Результаты измерения контрольной серии"
 * )
 */
class AnalyzerControlSeries extends SwREST_Controller {
	protected  $inputRules = array(
		'delete' => array(
			array('field' => 'AnalyzerControlSeries_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerTest_id', 'label' => 'Идентификатор теста', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AnalyzerControlSeries_regDateRange', 'label' => 'Период', 'rules' => '', 'type' => 'daterange'),
		),
		'load' => array(
			array('field' => 'AnalyzerControlSeries_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
		),
		'save' => array(
			array('field' => 'AnalyzerControlSeries_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AnalyzerControlSeries_regDT', 'label' => 'Дата регистрации результата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'AnalyzerControlSeries_Value','label' => 'Результат', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerControlSeries_IsControlPassed', 'label' => 'Контроль пройден', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AnalyzerControlSeries_Comment', 'label' => 'Примечание', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Сотрудник', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerControlSeries_model', 'dbmodel');
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerControlSeries",
	 *  	tags={"AnalyzerControlSeries"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerControlSeries_id"
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

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerControlSeries/list",
	 *  	tags={"AnalyzerControlSeries"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerControlSeries_regDateRange",
	 *     		in="query",
	 *     		description="Период",
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
		if (isset($data['AnalyzerControlSeries_regDateRange']) && is_string($data['AnalyzerControlSeries_regDateRange'])) {
			$data['AnalyzerControlSeries_regDateRange'] = explode('-', $data['AnalyzerControlSeries_regDateRange']);
		}
		$response = $this->dbmodel->loadList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerControlSeries",
	 *  	tags={"AnalyzerControlSeries"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerControlSeries_id",
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
	 * @OA\Post(
	 *     	path="/api/AnalyzerControlSeries",
	 *  	tags={"AnalyzerControlSeries"},
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
	 *     					property="AnalyzerControlSeries_regDT",
	 *     					description="Дата регистрации результата",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_Value",
	 *     					description="Результат",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_IsControlPassed",
	 *     					description="Признак прохождения контроля",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_Comment",
	 *     					description="Примечание",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_id",
	 *     					description="Идентификатор сотрудника",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerControlSeries_regDT",
	 *     					"AnalyzerControlSeries_Value",
	 *     					"AnalyzerControlSeries_IsControlPassed",
	 *     					"MedService_id",
	 *     					"MedPersonal_id"
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
		$data['AnalyzerControlSeries_id'] = null;

		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerControlSeries",
	 *  	tags={"AnalyzerControlSeries"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_id",
	 *     					description="Идентификатор",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_regDT",
	 *     					description="Дата регистрации результата",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_Value",
	 *     					description="Результат",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_IsControlPassed",
	 *     					description="Признак прохождения контроля",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerControlSeries_Comment",
	 *     					description="Примечание",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_id",
	 *     					description="Идентификатор сотрудника",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerControlSeries_id",
	 *     					"AnalyzerControlSeries_regDT",
	 *     					"AnalyzerControlSeries_Value",
	 *     					"AnalyzerControlSeries_IsControlPassed",
	 *     					"MedService_id",
	 *     					"MedPersonal_id"
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
		if (empty($data['AnalyzerControlSeries_id'])) {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "Отсутствует обязательный параметр 'AnalyzerControlSeries_id'"
			));
		}

		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}
}