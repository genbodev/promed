<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class LisUser
 * @OA\Tag(
 *     name="LisUser",
 *     description="Пользователь ЛИС"
 * )
 */
class LisUser extends SwREST_Controller {
	protected $inputRules = array(
		'get' => array(

		),
		'save' => array(
			array('field' => 'User_id', 'label' => 'Пользователь', 'rules' => '','type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required','type' => 'id'),
			array('field' => 'User_ClientId', 'label' => 'Id клиента', 'rules' => '','type' => 'string'),
			array('field' => 'User_Login', 'label' => 'Логин', 'rules' => 'required','type' => 'string'),
			array('field' => 'User_Password', 'label' => 'Пароль','rules' => 'required','type' => 'string')
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('LisUser_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/LisUser",
	 *  	tags={"LisUser"},
	 *	    summary="Получение учетных данных ЛИС текущего пользователя",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_get() {
		$data = $this->ProcessInputData('get', null, true);
		$response = $this->dbmodel->get($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/LisUser",
	 *  	tags={"LisUser"},
	 *	    summary="Сохранение учетных данных ЛИС для текущего пользователя",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="User_id",
	 *     					description="Идентификатор пользователя",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="User_ClientId",
	 *     					description="ClientId",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="User_Login",
	 *     					description="Логин",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="User_Password",
	 *     					description="Пароль",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"MedService_id",
	 *     					"User_Login",
	 *     					"User_Password"
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
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}