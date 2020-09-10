<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnPrescr
 * @property EvnPrescr_model dbmodel
 */
class EvnPrescr extends SwRest_Controller {
	protected $inputRules = array(
		'getEvnDirectionIds' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'getChildEvnPrescrId' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'checkEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'checkAndDirectEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пальзователя', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnPrescrInsDate' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'defineUslugaParams' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_pid','label' => 'Идентификатор родительского события','rules' => 'trim','type' => 'id'),
			array('field' => 'EvnUslugaPar_setDT','label' => 'Дата оказания услуги','rules' => '','type' => 'string'),
		),
		'getSetDateByUslugaPar' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
		),
		'getPrescrByDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnPrescrLabDiagDescr' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'execEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'getPayTypeFromEvn' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'rollbackEvnPrescrExecution' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnPrescr_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/EvnDirectionIds",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Получение идентификаторов направлений, связанных с назначением",
	 * 		@OA\Parameter(
	 *     		name="EvnPrescr_id",
	 *     		in="query",
	 *     		description="Идентификатор назначения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EvnDirectionIds_get() {
		$data = $this->ProcessInputData('getEvnDirectionIds', null, true);
		$response = $this->dbmodel->getEvnDirectionIds($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/childEvnPrescrId",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Получение идентификатора дочернего назначения",
	 * 		@OA\Parameter(
	 *     		name="EvnPrescr_id",
	 *     		in="query",
	 *     		description="Идентификатор назначения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function childEvnPrescrId_get() {
		$data = $this->ProcessInputData('getChildEvnPrescrId', null, true);
		$response = $this->dbmodel->getChildEvnPrescrId($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 * 		path="/api/EvnPrescr/checkAndDirectEvnPrescr",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Проверка наличия направления по назначению, создание связи, если её нет",
	 * 		@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnPrescr_id",
	 *     					description="Идентификатор назначения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"EvnPrescr_id",
	 *     					"EvnDirection_id"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function checkAndDirectEvnPrescr_post() {
		$data = $this->ProcessInputData('checkAndDirectEvnPrescr', null, true);
		$response = $this->dbmodel->checkAndDirectEvnPrescr($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/checkEvnPrescr",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Проверка наличия направления по назначению",
	 * 		@OA\Parameter(
	 *     		name="EvnPrescr_id",
	 *     		in="query",
	 *     		description="Идентификатор назначения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function checkEvnPrescr_get() {
		$data = $this->ProcessInputData('checkEvnPrescr', null, true);
		$response = $this->dbmodel->checkEvnPrescr($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/EvnPrescrInsDate",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Получение даты назначения",
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EvnPrescrInsDate_get() {
		$data = $this->ProcessInputData('getEvnPrescrInsDate', null, true);
		$response = $this->dbmodel->getEvnPrescrInsDate($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/defineUslugaParams",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Определение назначения и случая к которому будет привязана услуга",
	 * 		@OA\Parameter(
	 *     		name="EvnPrescr_id",
	 *     		in="query",
	 *     		description="Идентификатор назначения",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnUslugaPar_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnUslugaPar_setDT",
	 *     		in="query",
	 *     		description="Дата оказания услуги",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function defineUslugaParams_get() {
		$data = $this->ProcessInputData('defineUslugaParams', null, true);
		$response = $this->dbmodel->defineUslugaParams($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/setDateByUslugaPar",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Определение даты назначения по услуге",
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function setDateByUslugaPar_get() {
		$data = $this->ProcessInputData('getSetDateByUslugaPar', null, true);
		$response = $this->dbmodel->getSetDateByUslugaPar($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/PrescrByDirection",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Определение назначения по направлению",
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function PrescrByDirection_get() {
		$data = $this->ProcessInputData('getPrescrByDirection', null, true);
		$response = $this->dbmodel->getPrescrByDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/EvnPrescrLabDiagDescr",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Получение комментария к назначению",
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EvnPrescrLabDiagDescr_get() {
		$data = $this->ProcessInputData('getEvnPrescrLabDiagDescr', null, true);
		$this->load->model('EvnPrescrLabDiag_model');
		$response = $this->EvnPrescrLabDiag_model->getEvnPrescrLabDiagDescr($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnPrescr/PayTypeFromEvn",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Получение вида оплаты из движения/посещения, в котором происходит назначение",
	 * 		@OA\Parameter(
	 *     		name="EvnPrescr_id",
	 *     		in="query",
	 *     		description="Идентификатор назначения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function PayTypeFromEvn_get() {
		$data = $this->ProcessInputData('getPayTypeFromEvn', null, true);
		$response = $this->dbmodel->getPayTypeFromEvn($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 * 		path="/api/EvnPrescr/exec",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Выполнение назначения",
	 * 		@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="multipart/form-data",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnPrescr_id",
	 *     					description="Идентификатор назначения",
	 *     					type="integer"
	 * 					),
	 *     				required={"EvnPrescr_id"}
	 * 				)
	 * 			)
	 * 		),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function exec_post() {
		$data = $this->ProcessInputData('execEvnPrescr', null, true);
		$response = $this->dbmodel->execEvnPrescr($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 * 		path="/api/EvnPrescr/rollback",
	 *     	tags={"EvnPrescr"},
	 *     	summary="Отмена выполнения назначения",
	 * 		@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="multipart/form-data",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnPrescr_id",
	 *     					description="Идентификатор назначения",
	 *     					type="integer"
	 * 					),
	 *     				required={"EvnPrescr_id"}
	 * 				)
	 * 			)
	 * 		),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function  rollback_put() {
		$data = $this->ProcessInputData('rollbackEvnPrescrExecution', null, true);
		$response = $this->dbmodel->rollbackEvnPrescrExecution($data);
		if(!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
