<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnMediaData
 * @property EvnMediaFiles_model dbmodel
 */
class EvnMediaData extends SwRest_Controller {
	protected $inputRules = array(
		'addEvnMediaDataFromAPI' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnMediaData_FileName', 'label' => 'Название файла', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'File', 'label' => 'Файл в base64', 'rules' => 'required', 'type' => 'string'),
		),
		'getEvnMediaByEvn' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnMediaFiles_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/EvnMediaData",
	 *  	tags={"EvnMediaData"},
	 *	    summary="Создание",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="multipart/form-data",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Evn_id",
	 *     					description="Идентификатор события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnMediaData_FileName",
	 *     					description="Название файла",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="File",
	 *     					description="Файл в base64",
	 *     					type="string",
	 *     					format="base64"
	 * 					),
	 *     				required={
	 *     					"Evn_id",
	 *     					"EvnMediaData_FileName",
	 *     					"File"
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
		$data = $this->ProcessInputData('addEvnMediaDataFromAPI', null, true);
		$response = $this->dbmodel->addEvnMediaDataFromAPI($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnMediaData/byEvn",
	 *     	tags={"EvnMediaData"},
	 *     	summary="Получение идентификаторов файлов события",
	 * 		@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор события",
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
	function byEvn_get() {
		$data = $this->ProcessInputData('getEvnMediaByEvn', null, true);
		$response = $this->dbmodel->getEvnMediaByEvn($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}