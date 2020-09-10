<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnSection
 * @property EvnSection_model dbmodel
 */
class EvnUsluga extends SwRest_Controller {
	protected $inputRules = array(
		'loadEvnUslugaParSimpleEditFormAdditData' => array(
			array('field' => 'EvnUslugaPar_pid', 'label' => 'Идентификатор родителя услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_pid', 'label' => 'Идентификатор родителя направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_oid', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
		),
		'beforeEditEvnUslugaPar' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_pid', 'label' => 'Идентификатор родителя услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_setDT', 'label' => 'Дата услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'savedData', 'label' => 'savedData', 'rules' => 'required', 'type' => 'string'),
		),
	);

	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnUsluga_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnUsluga/ParSimpleEditFormAdditData",
	 *     	tags={"EvnUsluga"},
	 *     	summary="Получение дополнительных данных для формы редактирования",
	 * 		@OA\Parameter(
	 *     		name="EvnUslugaPar_pid",
	 *     		in="query",
	 *     		description="Идентификатор родителского события услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_pid",
	 *     		in="query",
	 *     		description="Идентификатор родителского события направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="Lpu_oid",
	 *     		in="query",
	 *     		description="Идентификатор МО",
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
	function ParSimpleEditFormAdditData_get() {
		$data = $this->ProcessInputData('loadEvnUslugaParSimpleEditFormAdditData', null, true);
		$response = $this->dbmodel->loadEvnUslugaParSimpleEditFormAdditData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnUsluga/ParBeforeEdit",
	 *     tags={"EvnUsluga"},
	 *     summary="Обработка данных перед изменением параклинической услуги",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_pid",
	 *     					description="Идентификатор родителского события услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_setDT",
	 *     					description="Дата услуги",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="savedData",
	 *     					description="Сохраненные данные",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={
	 *	 					"EvnUslugaPar_id",
	 *	 					"EvnUslugaPar_pid",
	 *	 					"savedData"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function ParBeforeEdit_post() {
		$data = $this->ProcessInputData('beforeEditEvnUslugaPar', null, true);
		$this->load->model('EvnUslugaPar_model');
		$response = $this->EvnUslugaPar_model->beforeEditEvnUslugaPar($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}