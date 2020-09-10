<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class User
 * @property User_model dbmodel
 */
class User extends SwRest_Controller {
	protected $inputRules = array(
		'authByToken' => array(
			array('field' => 'swtoken', 'label' => 'Токен', 'rules' => 'required', 'type' => 'string'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
	}

	/**
	 * @OA\Post(
	 *     path="/api/User/authByToken",
	 *     tags={"User"},
	 *     summary="Авторизация по токену",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="swtoken",
	 *     					description="Токен",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"swtoken"
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
	function authByToken_post() {
		$data = $this->ProcessInputData('authByToken');
		//Авторизация выполняется в конструкторе SwRest_Controller до вызова этого метода
		$response = array('success' => true);
		$this->response(array('error_code' => 0, 'data' => array($response)));
	}
}