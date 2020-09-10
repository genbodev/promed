<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property Messages_model dbmodel
 */
class Messages extends SwRest_Controller {
	protected $inputRules = array(
		'autoMessage' => array(
			array('field' => 'title', 'label' => 'Заголовок', 'rules' => 'required|trim', 'type' => 'string'),
			array('field' => 'text', 'label' => 'Текст', 'rules' => 'required|trim', 'type' => 'string'),
			array('field' => 'type', 'label' => 'Тип сообщения', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'autotype', 'label' => 'Тип события', 'rules' => '', 'type' => 'int'),
			array('field' => 'User_rid', 'label' => 'Получатель', 'rules' => '', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->model('Messages_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     path="/api/Messages/auto",
	 *     tags={"Messages"},
	 *     summary="Cоздание и запись в бд автоматического сообщения",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="title",
	 *     					description="Заголовок",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="text",
	 *     					description="Текст",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="type",
	 *     					description="Тип сообщения",
	 *     					type="int"
	 * 					),
	 *     				@OA\Property(
	 *     					property="autotype",
	 *     					description="Тип события",
	 *     					type="int"
	 * 					),
	 *     				@OA\Property(
	 *     					property="User_rid",
	 *     					description="Получатель",
	 *     					type="int"
	 * 					),
	 *     				required={
	 *     					"title",
	 *     					"text",
	 *     					"type"
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
	function auto_post() {
		$data = $this->ProcessInputData('autoMessage', null, true);
		$response = $this->dbmodel->autoMessage($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
