<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class InnovaSysService
 * @property InnovaSysService_model dbmodel
 */
class InnovaSysService extends SwRest_Controller {
	protected $inputRules = array(
		'getAISTargetCode' => array(
			array('field' => 'Code', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Barcode', 'label' => 'Штрихкод', 'rules' => 'required', 'type' => 'string')
		),
		'getTests' => [
			['field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'id']
		]
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('InnovaSysService_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/InnovaSysService/AISTargetCode",
	 *     	tags={"InnovaSysService"},
	 *     	summary="Определение кода заказанного исследования и проверки наличия услуг",
	 * 		@OA\Parameter(
	 *     		name="Code",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     @OA\Parameter(
	 *     		name="Barcode",
	 *     		in="query",
	 *     		description="Штрихкод",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function AISTargetCode_get() {
		$data = $this->ProcessInputData('getAISTargetCode', null);
		$response = $this->dbmodel->getAISTargetCode($data['Code'], $data['Barcode']);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 * 		path="/api/InnovaSysService/Tests",
	 *     	tags={"InnovaSysService"},
	 *     	summary="Получение тестов по пробе",
	 * 		@OA\Parameter(
	 *     		name="Code",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
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
	function Tests_get() {
		$data = $this->ProcessInputData('getTests', null);
		$response = $this->dbmodel->getTests($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}
}
