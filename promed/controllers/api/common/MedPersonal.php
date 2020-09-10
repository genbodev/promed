<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class MedPersonal
 * @property MedPersonal_model dbmodel
 */
class MedPersonal extends SwRest_Controller {
	protected $inputRules = array(
		'getFioFromMedPersonal' => array(
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MedPersonal_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/MedPersonal/Fio",
	 *     	tags={"MedPersonal"},
	 *     	summary="Получение ФИО врача",
	 * 		@OA\Parameter(
	 *     		name="MedPersonal_id",
	 *     		in="query",
	 *     		description="Идентификатор врача",
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
	function Fio_get() {
		$data = $this->ProcessInputData('getFioFromMedPersonal', null);
		$response = $this->dbmodel->getFioFromMedPersonal($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * добавление результатов взятия пробы
	 */
	function info_get() {
		$data = $this->ProcessInputData('getMedPersonInfo', null);
		$response = $this->dbmodel->getMedPersonInfo($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}
}
