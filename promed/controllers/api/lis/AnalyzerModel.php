<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property AnalyzerModel_model dbmodel
 * @OA\Tag(
 *     name="AnalyzerModel",
 *     description="Модели анализаторов"
 * )
 */
class AnalyzerModel extends SwREST_Controller {
	protected $inputRules = array(
		'save' => array(
			array('field' => 'AnalyzerModel_id', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_Name', 'label' => '', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerModel_SysNick', 'label' => '', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'FRMOEquipment_id', 'label' => 'Тип оборудования', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerClass_id', 'label' => 'Класс анализатора', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerInteractionType_id', 'label' => 'Тип взаимодействия', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerModel_IsScaner', 'label' => 'Наличие сканера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetInteractionType_id', 'label' => 'Тип взаимодействия с рабочими списками', 'rules' => 'required', 'type' => 'int'),
		),
		'load' => array(
			array('field' => 'AnalyzerModel_id', 'label' => '', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'AnalyzerModel_id', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_Name', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerModel_SysNick', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerClass_id', 'label' => 'Класс анализатора', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerInteractionType_id', 'label' => 'Тип взаимодействия', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_IsScaner', 'label' => 'Наличие сканера', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerWorksheetInteractionType_id', 'label' => 'Тип взаимодействия с рабочими списками', 'rules' => '', 'type' => 'int'),
		),
		'delete' => array(
			array('field' => 'AnalyzerModel_id', 'label' => '', 'rules' => 'required', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('AnalyzerModel_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AnalyzerModel",
	 *  	tags={"AnalyzerModel"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_Name",
	 *     					description="Наименование анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_SysNick",
	 *     					description="Системное наименование анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerClass_id",
	 *     					description="Идентификатор класса анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerInteractionType_id",
	 *     					description="Идентификатор типа взаимодействия",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_IsScaner",
	 *     					description="Наличие сканера",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetInteractionType_id",
	 *     					description="Идентификатор типа взаимодействия с рабочими списками",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerModel_Name",
	 *     					"AnalyzerModel_SysNick",
	 *     					"AnalyzerInteractionType_id",
	 *     					"AnalyzerModel_IsScaner",
	 *     					"AnalyzerWorksheetInteractionType_id"
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
		if (isset($data['AnalyzerModel_Name'])) {
			$this->dbmodel->setAnalyzerModel_Name($data['AnalyzerModel_Name']);
		}
		if (isset($data['AnalyzerModel_SysNick'])) {
			$this->dbmodel->setAnalyzerModel_SysNick($data['AnalyzerModel_SysNick']);
		}
		if (isset($data['FRMOEquipment_id'])) {
			$this->dbmodel->setFRMOEquipment_id($data['FRMOEquipment_id']);
		}
		if (isset($data['AnalyzerClass_id'])) {
			$this->dbmodel->setAnalyzerClass_id($data['AnalyzerClass_id']);
		}
		if (isset($data['AnalyzerInteractionType_id'])) {
			$this->dbmodel->setAnalyzerInteractionType_id($data['AnalyzerInteractionType_id']);
		}
		if (isset($data['AnalyzerModel_IsScaner'])) {
			$this->dbmodel->setAnalyzerModel_IsScaner($data['AnalyzerModel_IsScaner']);
		}
		if (isset($data['AnalyzerWorksheetInteractionType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetInteractionType_id($data['AnalyzerWorksheetInteractionType_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     	path="/api/AnalyzerModel",
	 *  	tags={"AnalyzerModel"},
	 *	    summary="Изменение",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_Name",
	 *     					description="Наименование анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_SysNick",
	 *     					description="Системное наименование анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerClass_id",
	 *     					description="Идентификатор класса анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerInteractionType_id",
	 *     					description="Идентификатор типа взаимодействия",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_IsScaner",
	 *     					description="Наличие сканера",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerWorksheetInteractionType_id",
	 *     					description="Идентификатор типа взаимодействия с рабочими списками",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerModel_id",
	 *     					"AnalyzerModel_Name",
	 *     					"AnalyzerModel_SysNick",
	 *     					"AnalyzerInteractionType_id",
	 *     					"AnalyzerModel_IsScaner",
	 *     					"AnalyzerWorksheetInteractionType_id"
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
		if (isset($data['AnalyzerModel_id'])) {
			$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
		}
		if (isset($data['AnalyzerModel_Name'])) {
			$this->dbmodel->setAnalyzerModel_Name($data['AnalyzerModel_Name']);
		}
		if (isset($data['AnalyzerModel_SysNick'])) {
			$this->dbmodel->setAnalyzerModel_SysNick($data['AnalyzerModel_SysNick']);
		}
		if (isset($data['AnalyzerClass_id'])) {
			$this->dbmodel->setAnalyzerClass_id($data['AnalyzerClass_id']);
		}
		if (isset($data['AnalyzerInteractionType_id'])) {
			$this->dbmodel->setAnalyzerInteractionType_id($data['AnalyzerInteractionType_id']);
		}
		if (isset($data['AnalyzerModel_IsScaner'])) {
			$this->dbmodel->setAnalyzerModel_IsScaner($data['AnalyzerModel_IsScaner']);
		}
		if (isset($data['AnalyzerWorksheetInteractionType_id'])) {
			$this->dbmodel->setAnalyzerWorksheetInteractionType_id($data['AnalyzerWorksheetInteractionType_id']);
		}
		$response = $this->dbmodel->save();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerModel",
	 *  	tags={"AnalyzerModel"},
	 *	    summary="Получение данных",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
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
		$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AnalyzerModel/list",
	 *  	tags={"AnalyzerModel"},
	 *	    summary="Получение списка",
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_Name",
	 *     		in="query",
	 *     		description="Наименование модели анализатора",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_SysNick",
	 *     		in="query",
	 *     		description="Системное наименование модели анализатора",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerClass_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerInteractionType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа взаимодействия",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_IsScaner",
	 *     		in="query",
	 *     		description="Наличие сканера",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerWorksheetInteractionType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа взаимодействия с рабочими списками",
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
	 *     	path="/api/AnalyzerModel",
	 *  	tags={"AnalyzerModel"},
	 *	    summary="Удаление",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"AnalyzerModel_id"
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
		$this->dbmodel->setAnalyzerModel_id($data['AnalyzerModel_id']);
		$response = $this->dbmodel->delete();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}