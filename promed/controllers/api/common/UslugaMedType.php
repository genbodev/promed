<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH . 'libraries/SwREST_Controller.php');

/**
 * Class UslugaMedType
 * @property UslugaMedType_model dbmodel
 */
class UslugaMedType extends SwRest_Controller
{
	protected $inputRules = [
		'saveUslugaMedTypeLink' => [
			['field' => 'UslugaMedType_id', 'label' => 'Идентификатор типа услуги', 'rules' => 'required', 'type' => 'string'],
			['field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'string'],
			['field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'string']
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('UslugaMedType_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     path="/api/UslugaMedType/link",
	 *     tags={"User"},
	 *     summary="сохранение связи события и типа услуги",
	 *     @OA\RequestBody(
	 *            required=true,
	 *     		@OA\MediaType(
	 *                mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *                        property="UslugaMedType_id",
	 *                        description="Идентификатор типа услуги",
	 *                        type="integer"
	 *                    ),
	 *     				@OA\Property(
	 *                        property="Evn_id",
	 *                        description="Идентификатор события",
	 *                        type="integer"
	 *                    ),
	 *     				@OA\Property(
	 *                        property="pmUser_id",
	 *                        description="Идентификатор пользователя",
	 *                        type="integer"
	 *                    ),
	 *                    required={
	 *                        "UslugaMedType_id",
	 *                        "Evn_id",
	 *                        "pmUser_id"
	 *                    }
	 *                )
	 *            )
	 *        ),
	 *		@OA\Response(
	 *        response="200",
	 *        description="JSON response",
	 *			@OA\JsonContent()
	 *        )
	 * )
	 */
	function link_post()
	{
		$data = $this->ProcessInputData('saveUslugaMedTypeLink', null, false);
		$response = $this->dbmodel->saveUslugaMedTypeLink($data);
		if ($response !== true) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0]);
	}
}
