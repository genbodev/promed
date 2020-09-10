<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnDirection
 */
class EvnDirection extends SwRest_Controller {
	protected $inputRules = array(
		'getEvnDirectionIds' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnfromPersonEvn' => array(
			array('field' => 'EvnDirection_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния пациента', 'rules' => 'required', 'type' => 'id'),
		),
		'getRecMethodTypeForDirection' => array(
			array('field' => 'ARMType_id', 'label' => 'Идентификатор типа АРМа', 'rules' => 'required', 'type' => 'id'),
		),
		'makeEvnDirectionTalonCode' => array(
			array('field' => 'TimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetableResource_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_did', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id')
		),
		'beforeRedirectEvnDirectionLis' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'oldTimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'newTimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'redirectEvnDirection', 'label' => 'Код перенаправления', 'rules' => 'required', 'type' => 'int'),
		),
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
	 * 		path="/api/EvnDirection/EvnFromPersonEvn",
	 *     	tags={"EvnDirection"},
	 *     	summary="Получение Evn_id из PersonEvn",
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="PersonEvn_id",
	 *     		in="query",
	 *     		description="Идентификатор состояния пациента",
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
	function EvnFromPersonEvn_get() {
		$data = $this->ProcessInputData('getEvnfromPersonEvn');
		$this->load->model('EvnDirection_model', 'EvnDirection_model');
		$response = $this->EvnDirection_model->getEvnfromPersonEvn($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnDirection/RecMethodType",
	 *     	tags={"EvnDirection"},
	 *     	summary="Получение способа записи для направления по ARMType_id",
	 * 		@OA\Parameter(
	 *     		name="ARMType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа АРМа",
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
	function RecMethodType_get() {
		$data = $this->ProcessInputData('getRecMethodTypeForDirection');
		$this->load->model('EvnDirection_model', 'EvnDirection_model');
		$response = $this->EvnDirection_model->getRecMethodTypeForDirection($data);

		if ($response !== false) {
			$response = array(array(
				'RecMethodType_id' => $response
			));
		} else {
			$response = array(array(
				'Error_Msg' => 'Не получилось определить метод записи направления'
			));
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnDirection/beforeRedirectLis",
	 *     tags={"EvnDirection"},
	 *     summary="Действия перед перенаправлением в ЛИС",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="oldTimetableMedService_id",
	 *     					description="Идентификатор бирки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="newTimetableMedService_id",
	 *     					description="Идентификатор бирки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="redirectEvnDirection",
	 *     					description="Код перенаправления",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"EvnDirection_id",
	 *     					"redirectEvnDirection"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *		response="200",
	 *		description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function beforeRedirectLis_post() {
		$data = $this->ProcessInputData('beforeRedirectEvnDirectionLis', null, true);
		$this->load->model('EvnDirection_model', 'EvnDirection_model');
		$response = $this->EvnDirection_model->beforeRedirectEvnDirectionLis($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/*
	 * метод который что то делает
	 */
	function EvnDirectionTalonCode_get() {

		$data = $this->ProcessInputData('makeEvnDirectionTalonCode');
		$this->load->model('EvnDirection_model', 'EvnDirection_model');
		$response = $this->EvnDirection_model->makeEvnDirectionTalonCode($data);

		if (empty($response['Error_Msg'])) {
			$response = array(array(
				'EvnDirection_TalonCode' => $response
			));
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
