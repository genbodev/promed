<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property AnalyzerTestRefValues_model dbmodel
 * @OA\Tag(
 *     name="AnalyzerTestRefValues",
 *     description="Связь тестов анализаторов и референтных значений"
 * )
*/
class AnalyzerTestRefValues extends SwREST_Controller {
	protected  $inputRules = array(
		'save' => array(
			array('field' => 'AnalyzerTestRefValues_id', 'label' => 'Идентификатор референсного значения', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Идентификатор теста анализатора', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RefValues_Name', 'label' => 'Наименование', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Unit_id', 'label' => 'Единица измерения', 'rules' => '', 'type' => 'id'),
			array('field' => 'RefValues_LowerLimit', 'label' => 'Нижнее нормальное', 'rules' => '', 'type' => 'string'),
			array('field' => 'RefValues_UpperLimit', 'label' => 'Верхнее нормальное', 'rules' => '', 'type' => 'string'),
			array('field' => 'RefValues_BotCritValue', 'label' => 'Нижнее критическое', 'rules' => '', 'type' => 'string'),
			array('field' => 'RefValues_TopCritValue', 'label' => 'Верхнее критическое', 'rules' => '', 'type' => 'string'),
			array('field' => 'RefValues_Description', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'),
			array('field' => 'LimitData', 'label' => 'Ограничения', 'rules' => '', 'type' => 'string'),
		),
		'load' => array(
			array('field' => 'AnalyzerTestRefValues_id', 'label' => 'AnalyzerTestRefValues_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerTest_id', 'label' => 'Тест анализатора', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerTestRefValues_id', 'label' => 'AnalyzerTestRefValues_id', 'rules' => 'required', 'type' => 'int'),
		),
		'loadRefValuesList' => array(
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id',),
			array('field' => 'UslugaComplexTarget_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id',),
			array('field' => 'UslugaComplexTest_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id',),
			array('field' => 'EvnLabSample_setDT', 'label' => 'EvnLabSample_setDT', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'Person_id', 'label' => 'Человек', 'rules' => '', 'type' => 'id'),
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaTest_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerTestRefValues_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerTestRefValues",
	 *  	tags={"AnalyzerTestRefValues"},
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
	 *     					property="RefValues_Name",
	 *     					description="Наименование референсного значения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Unit_id",
	 *     					description="Идентификатор единицы измерения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_LowerLimit",
	 *     					description="Нижнее нормальное",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_UpperLimit",
	 *     					description="Верхнее нормальное",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_BotCritValue",
	 *     					description="Нижнее критическое",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_TopCritValue",
	 *     					description="Верхнее критическое",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_Description",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LimitData",
	 *     					description="Ограничения",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTest_id",
	 *     					"RefValues_Name"
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
		$data['AnalyzerTestRefValues_id'] = null;
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerTestRefValues",
	 *  	tags={"AnalyzerTestRefValues"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTestRefValues_id",
	 *     					description="Идентификатор референсного значения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_Name",
	 *     					description="Наименование референсного значения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Unit_id",
	 *     					description="Идентификатор единицы измерения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_LowerLimit",
	 *     					description="Нижнее нормальное",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_UpperLimit",
	 *     					description="Верхнее нормальное",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_BotCritValue",
	 *     					description="Нижнее критическое",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_TopCritValue",
	 *     					description="Верхнее критическое",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefValues_Description",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LimitData",
	 *     					description="Ограничения",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTestRefValues_id",
	 *     					"AnalyzerTest_id",
	 *     					"RefValues_Name"
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
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTestRefValues",
	 *  	tags={"AnalyzerTestRefValues"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestRefValues_id",
	 *     		in="query",
	 *     		description="Идентификатор референсного значения",
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
	 *     	path="/api/AnalyzerTestRefValues/list",
	 *  	tags={"AnalyzerTestRefValues"},
	 *	    summary="Получение списка",
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
	 *     	path="/api/AnalyzerTestRefValues",
	 *  	tags={"AnalyzerTestRefValues"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTestRefValues_id",
	 *     					description="Идентификатор референсного значения",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTestRefValues_id"
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
	 *     	path="/api/AnalyzerTestRefValues/RefValuesList",
	 *  	tags={"AnalyzerTestRefValues"},
	 *	    summary="Получение списка референсных значений",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplexTarget_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplexTest_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnLabSample_setDT",
	 *     		in="query",
	 *     		description="Дата и время взятия пробы",
	 *     		@OA\Schema(type="string", format="date-time")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Дата человека",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
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
	function RefValuesList_get() {
		$data = $this->ProcessInputData('loadRefValuesList', null, true);
		$response = $this->dbmodel->loadRefValuesList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}