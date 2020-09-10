<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property AnalyzerTest_model dbmodel
 * @OA\Tag(
 *     name="AnalyzerTest",
 *     description="Тесты анализаторов"
 * )
*/
class AnalyzerTest extends SwREST_Controller {
	protected $inputRules = array(
		'deleteUslugaComplexMedServiceDouble' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
		),
		'fixAnalyzerTest' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
		),
		'getSysNickForAnalyzerTest' => array(
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
		),
		'linkUslugaComplexMedService' => array(
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplexMedService_ids', 'label' => 'Список услуг', 'rules' => 'required', 'type' => 'string'),
		),
		'getAnalyzerTestReagent' => array(
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_Code', 'label' => 'Код услуги', 'rules' => 'trim', 'type' => 'string'),
		),
		'saveAnalyzerTestNotActive' => array(
			array('field' => 'AnalyzerTest_id', 'label' => 'Идентификатор теста', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AnalyzerTest_IsNotActive', 'label' => 'Неактивный', 'rules' => 'required', 'type' => 'int'),
		),
		'getUnlinkedUslugaComplexMedServiceCount' => array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
		),
		'getUnlinkedUslugaComplexMedServiceGrid' => array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplexMedService_pid', 'label' => 'Идентификатор родительской услуги', 'rules' => '', 'type' => 'id'),
		),
		'save' => array(
			array('field' => 'AnalyzerTest_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTest_Name', 'label' => 'Наименование', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerTest_SysNick', 'label' => 'Мнемоника', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerTest_pid', 'label' => 'Родительский тест', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => '', 'type' => 'id'),
			array('field' => 'Analyzer_id', 'label' => 'Анализатора', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTest_isTest', 'label' => 'Признак теста', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга теста', 'rules' => '', 'type' => 'id'),
			array('field' => 'postUslugaComplex_id', 'label' => 'услуга теста', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplexMedService_id', 'label' => 'Связь с услугой службы', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTest_begDT', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date'),
			array('field' => 'AnalyzerTest_endDT', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'AnalyzerTest_SortCode', 'label' => 'Приоритет', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTestType_id', 'label' => 'Тип теста', 'rules' => '', 'type' => 'int'),
			array('field' => 'Unit_id', 'label' => 'Единица измерения', 'rules' => '', 'type' => 'int'),
			array('field' => 'ReagentNormRate_id', 'label' => 'Запись Расхода реактива', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_Code', 'label' => 'Код услуги', 'rules' => 'trim', 'type' => 'string'),
		),
		'load' => array(
			array('field' => 'AnalyzerTest_id', 'label' => '', 'rules' => 'required', 'type' => 'int'),
		),
		'loadAnalyzerTestGrid' => array(
			array('field' => 'AnalyzerTest_id', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_pid', 'label' => 'Родительский тест', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => '', 'type' => 'id'),
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга теста', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTestType_id', 'label' => 'Тип теста', 'rules' => '', 'type' => 'int'),
			array('field' => 'Unit_id', 'label' => 'Единица измерения', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_id', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerTest_id', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_pid', 'label' => 'Родительский тест', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модели анализаторов', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_Code', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerTest_Name', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerTest_SysNick', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerTestType_id', 'label' => 'Тип теста', 'rules' => '', 'type' => 'int'),
			array('field' => 'Unit_id', 'label' => 'Единица измерения', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_Deleted', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetType_id', 'label' => '', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerTest_id', 'label' => '', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'Analyzer_id', 'label' => '', 'rules' => '', 'type'  => 'int'),
			array('field' => 'AnalyzerTest_pid', 'label' => '', 'rules' => '', 'type' => 'int'),
		),
		'checkAnalyzerTestBegDate' => array(
			array('field' => 'AnalyzerTest_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTest_begDT', 'label' => '', 'rules' => '', 'type' => 'string'),
		),
		'checkAnalyzerTestIsExists' => array(
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => '', 'type' => 'id'),
			array('field' => 'AnalyzerTest_pid', 'label' => 'Исследование', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'услуга', 'rules' => 'required', 'type' => 'id'),
		),
		'loadAnalyzerTestType' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор анализатора', 'rules' => '', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerTest_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerTest",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_Name",
	 *     					description="Наименование",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_SysNick",
	 *     					description="Мнемоника",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_pid",
	 *     					description="Идентификатор родительского теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_isTest",
	 *     					description="Признак теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="postUslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplexMedService_id",
	 *     					description="Идентификатор связи услуги со службой",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_begDT",
	 *     					description="Дата начала",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_endDT",
	 *     					description="Дата окончания",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_SortCode",
	 *     					description="Приоритет",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTestType_id",
	 *     					description="Идентификатор типа теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Unit_id",
	 *     					description="Идентификатор единицы измерения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="ReagentNormRate_id",
	 *     					description="Идентификатор записи расхода реактива",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_Code",
	 *     					description="Код услуги",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTest_isTest"
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

		$data['AnalyzerTest_id'] = null;
		if (!empty($data['postUslugaComplex_id'])) {
			$data['UslugaComplex_id'] = $data['postUslugaComplex_id'];
		}

		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerTest",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Изменение",
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
	 *     					property="AnalyzerTest_Name",
	 *     					description="Наименование",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_SysNick",
	 *     					description="Мнемоника",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_pid",
	 *     					description="Идентификатор родительского теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_isTest",
	 *     					description="Признак теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="postUslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplexMedService_id",
	 *     					description="Идентификатор связи услуги со службой",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_begDT",
	 *     					description="Дата начала",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_endDT",
	 *     					description="Дата окончания",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_SortCode",
	 *     					description="Приоритет",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTestType_id",
	 *     					description="Идентификатор типа теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Unit_id",
	 *     					description="Идентификатор единицы измерения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="ReagentNormRate_id",
	 *     					description="Идентификатор записи расхода реактива",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_Code",
	 *     					description="Код услуги",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTest_id",
	 *     					"AnalyzerTest_isTest"
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

		if (!empty($data['postUslugaComplex_id'])) {
			$data['UslugaComplex_id'] = $data['postUslugaComplex_id'];
		}

		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerTest",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Удаление",
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
	 *     				required={
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
	function index_delete() {
		$data = $this->ProcessInputData('delete', null, true);
		// удаление формул, если тест расчетный
		if(!empty($data['Analyzer_id']) && !empty($data['AnalyzerTest_pid']))
		{
			$this->load->model('Lis_Ufa_AnalyzerTestFormula_model');
			$this->Lis_Ufa_AnalyzerTestFormula_model->AnalyzerTestFormulaAll_del($data);
		}
		$response = $this->dbmodel->delete($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение данных",
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
	 *     	path="/api/AnalyzerTest/grid",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Unit_id",
	 *     		in="query",
	 *     		description="Идентификатор единицы измерения",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа рабочего списка",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="start",
	 *     		in="query",
	 *     		description="Номер начальной записи",
	 *     		@OA\Schema(type="integer", default=0)
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="limit",
	 *     		in="query",
	 *     		description="Количество записей",
	 *     		@OA\Schema(type="integer", default=100)
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function grid_get() {
		$data = $this->ProcessInputData('loadAnalyzerTestGrid', null, true);
		$response = $this->dbmodel->loadAnalyzerTestGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/list",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_Code",
	 *     		in="query",
	 *     		description="Код теста",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_Name",
	 *     		in="query",
	 *     		description="Наименование теста",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_SysNick",
	 *     		in="query",
	 *     		description="Мнемоника теста",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTestType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Unit_id",
	 *     		in="query",
	 *     		description="Идентификатор единицы измерения",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_Deleted",
	 *     		in="query",
	 *     		description="Признак удаления теста",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа рабочего списка",
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
	 *     	path="/api/AnalyzerTest/linkUslugaComplexMedService",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Сохраненеи связи услуг с анализатором",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplexMedService_ids",
	 *     					description="Список идентификатор услуг на службе",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"Analyzer_id",
	 *     					"MedService_id",
	 *     					"UslugaComplexMedService_ids"
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
	function linkUslugaComplexMedService_post() {
		$data = $this->ProcessInputData('linkUslugaComplexMedService', null, true);
		$response = $this->dbmodel->linkUslugaComplexMedService($data);
		if ($response === false) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$response = array(array('success' => truer));
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/Reagent",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение данных реагента, привязанного к тесту анализатора",
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_Code",
	 *     		in="query",
	 *     		description="Код услуги",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Reagent_get() {
		$data = $this->ProcessInputData('getAnalyzerTestReagent', null, true);
		$response = $this->dbmodel->getAnalyzerTestReagent($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     	path="/api/AnalyzerTest/NotActive",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Изменение признака неактивности теста анализатора",
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
	 *     					property="AnalyzerTest_IsNotActive",
	 *     					description="Признак неактивности",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerTest_id",
	 *     					"AnalyzerTest_IsNotActive"
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
	function NotActive_patch() {
		$data = $this->ProcessInputData('saveAnalyzerTestNotActive', null, true);
		$response = $this->dbmodel->saveAnalyzerTestNotActive($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/SysNick",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение мнемоники",
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function SysNick_get() {
		$data = $this->ProcessInputData('getSysNickForAnalyzerTest', null, true);
		$response = $this->dbmodel->getSysNickForAnalyzerTest($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerTest/fix",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Фикс тестов анализаторов",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Lpu_id",
	 *     					description="Идентификатор МО",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					)
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
	function fix_post() {
		$data = $this->ProcessInputData('fixAnalyzerTest', null, true);
		$response = $this->dbmodel->fixAnalyzerTest($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/AnalyzerTest/UslugaComplexMedServiceDouble",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Удаление дублей услуг",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Lpu_id",
	 *     					description="Идентификатор МО",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					)
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
	function UslugaComplexMedServiceDouble_delete() {
		$data = $this->ProcessInputData('deleteUslugaComplexMedServiceDouble', null, true);
		$response = $this->dbmodel->deleteUslugaComplexMedServiceDouble($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/UnlinkedUslugaComplexMedServiceCount",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение количества несвязанных услуг на службе с анализаторами",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
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
	function UnlinkedUslugaComplexMedServiceCount_get() {
		$data = $this->ProcessInputData('getUnlinkedUslugaComplexMedServiceCount', null, true);
		$response = $this->dbmodel->getUnlinkedUslugaComplexMedServiceCount($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/UnlinkedUslugaComplexMedServiceGrid",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение списка несвязанных услуг на службе с анализаторами",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplexMedService_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительской услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function UnlinkedUslugaComplexMedServiceGrid_get() {
		$data = $this->ProcessInputData('getUnlinkedUslugaComplexMedServiceGrid', null, true);
		$response = $this->dbmodel->getUnlinkedUslugaComplexMedServiceGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/checkBegDate",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Проверка даты начала теста",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_id",
	 *     		in="query",
	 *     		description="Идентификатор теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_begDT",
	 *     		in="query",
	 *     		description="Дата начала",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function checkBegDate_get() {
		$data = $this->ProcessInputData('checkAnalyzerTestBegDate', null, true);
		$response = $this->dbmodel->checkAnalyzerTestBegDate($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/isExists",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Проверка теста на дубликат в списке тестов для модели или для экземпляра анализатора",
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerTest_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского теста",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function isExists_get() {
		$data = $this->ProcessInputData('checkAnalyzerTestIsExists', null, true);
		$response = $this->dbmodel->checkAnalyzerTestIsExists($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerTest/loadAnalyzerTestType",
	 *  	tags={"AnalyzerTest"},
	 *	    summary="Получение списка типов тестов",
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	)
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function loadAnalyzerTestType () {
		$data = $this->ProcessInputData('loadAnalyzerTestType', null, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAnalyzerTestType($data);
		$this->response($response);
	}
}
