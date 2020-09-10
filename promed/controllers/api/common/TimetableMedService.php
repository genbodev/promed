<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class TimetableMedService
 */
class TimetableMedService extends SwRest_Controller {
	protected $inputRules = array(
		'addTTMSDop' => array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Day', 'label' => 'День', 'rules' => '', 'type' => 'int'),
			array('field' => 'StartTime', 'label' => 'Время начала', 'rules' => '', 'type' => 'string'),
			array('field' => 'TimetableExtend_Descr', 'label' => 'Описание', 'rules' => '', 'type' => 'string'),
			array('field' => 'withoutRecord', 'label' => 'Без записи', 'rules' => '', 'type' => 'checkbox'),
		),
		'Clear' => array(
			array('field' => 'cancelType', 'label' => 'Тип отмены направления', 'rules' => '', 'type' => 'string'),
			array('field' => 'TimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DirFailType_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина смены статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnComment_Comment', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string')
		),
		'load' => array(
			array('field' => 'TimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
		),
		'recordTTMS' => [
			['field' => 'TimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Evn_id', 'label' => 'Идентификатор род. события направления', 'rules' => '', 'type' => 'id'],
			['field' => 'RecClass_id', 'label' => 'Тип записи на прием', 'rules' => '', 'type' => 'id'],
			['field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'],
			['field' => 'EvnDirection_IsAuto', 'label' => 'Автоматическое направление', 'rules' => '', 'type' => 'id'],
			['field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'],
		],
		'getPzmRecordData' => array(
			array('field' => 'TimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->helper('Reg');
		$this->load->model('TimetableMedService_model', 'dbmodel');
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/TimetableMedService",
	 *  	tags={"TimetableMedService"},
	 *	    summary="Освобождение бирки",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="cancelType",
	 *     					description="Тип отмены направления",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="TimetableMedService_id",
	 *     					description="Идентификатор бирки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="DirFailType_id",
	 *     					description="Идентификатор причины отмены направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnStatusCause_id",
	 *     					description="Идентификатор причины смены статуса",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnComment_Comment",
	 *     					description="Комментарий",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"TimetableMedService_id"
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
		$data = $this->ProcessInputData('Clear', null, true);
		$data['object'] = 'TimetableMedService';

		$response = $this->dbmodel->Clear($data);

		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/TimetableMedService/Dop",
	 *  	tags={"TimetableMedService"},
	 *	    summary="Создание дополнительной бирки",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Day",
	 *     					description="День",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="StartTime",
	 *     					description="Время начала",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="TimetableExtend_Descr",
	 *     					description="Описание",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="withoutRecord",
	 *     					description="Признак 'Без записи'",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"MedService_id"
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
	function Dop_post() {
		$data = $this->ProcessInputData('addTTMSDop', null, true);

		if (!empty($data['MedService_id'])) {
			$response = $this->dbmodel->addTTMSDop($data);
		} else {
			$response = $this->dbmodel->addTTMSDopUslugaComplex($data);
		}

		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/TimetableMedService",
	 *     	tags={"TimetableMedService"},
	 *     	summary="Получение данных бирки",
	 * 		@OA\Parameter(
	 *     		name="TimetableMedService_id",
	 *     		in="query",
	 *     		description="Идентификатор бирки",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_get() {
		$data = $this->ProcessInputData('load', null, true);

		if (empty($data['TimetableMedService_id']) &&
			empty($data['EvnDirection_id'])
		) {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "Отсутствуют параметры для запроса"
			));
		}

		$response = $this->dbmodel->load($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/TimetableMedService/recordEvnDirection",
	 *  	tags={"TimetableMedService"},
	 *	    summary="Запись направления на бирку",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="TimeTableMedService_id",
	 *     					description="Идентификатор бирки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Person_id",
	 *     					description="Идентификатор пациента",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Evn_id",
	 *     					description="Идентификатор род. события направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RecClass_id",
	 *     					description="Тип записи на прием",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_IsAuto",
	 *     					description="Автоматическое направление",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="pmUser_id",
	 *     					description="Идентификатор пользователя",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"TimeTableMedService_id",
	 *     					"Person_id",
	 *     					"pmUser_id"
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
	function recordEvnDirection_post() {
		$data = $this->ProcessInputData('recordTTMS', null, false);

		$this->load->model('Timetable_model', 'dbmodel');
		$response = $this->dbmodel->recordEvnDirection($data);

		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/*
	 * получить что-то
	 */
	function getPzmRecordData_get() {

		$data = $this->ProcessInputData('getPzmRecordData', null, false);
		$response = $this->dbmodel->getPzmRecordData($data);

		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(['error_code' => 0, 'data' => $response]);
	}
}