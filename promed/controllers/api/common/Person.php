<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Person
 */
class Person extends SwRest_Controller {
	protected $inputRules = array(
		'getPersonIdByPersonEvnId' => array(
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор периодики человека', 'rules' => 'required', 'type' => 'id'),
		),
		'getServerByPersonEvnId' => array(
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор периодики человека', 'rules' => 'required', 'type' => 'id'),
		),
		'getPersonDataForRefValues' => array(
			array('field' => 'Person_id', 'label' => 'Персональный идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabSample_setDT', 'label' => 'EvnLabSample_setDT', 'rules' => '', 'type' => 'string'),
		),
		'getPersonForInnova' => array(
			array('field' => 'Person_id', 'label' => 'Персональный идентификатор', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Person_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/Person/IdByPersonEvn",
	 *     	tags={"Person"},
	 *     	summary="Получение идентификатора человека по периодике",
	 * 		@OA\Parameter(
	 *     		name="PersonEvn_id",
	 *     		in="query",
	 *     		description="Идентификатор периодики человека",
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
	function IdByPersonEvn_get() {
		$data = $this->ProcessInputData('getPersonIdByPersonEvnId', null, true);
		$response = $this->dbmodel->getPersonIdByPersonEvnId($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/Person/serverByPersonEvn",
	 *     	tags={"Person"},
	 *     	summary="Определение сервера по периодие человека",
	 * 		@OA\Parameter(
	 *     		name="PersonEvn_id",
	 *     		in="query",
	 *     		description="Идентификатор периодики человека",
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
	function serverByPersonEvn_get() {
		$data = $this->ProcessInputData('getServerByPersonEvnId', null, true);
		$response = $this->dbmodel->serverByPersonEvn($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/Person/PersonDataForRefValues",
	 *     	tags={"Person"},
	 *     	summary="Получение параметров человека для определения референсных значений",
	 * 		@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnPrescr_id",
	 *     		in="query",
	 *     		description="Идентификатор периодики человека",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnLabSample_setDT",
	 *     		in="query",
	 *     		description="Дата",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function PersonDataForRefValues_get() {
		$data = $this->ProcessInputData('getPersonDataForRefValues', null, true);
		$response = $this->dbmodel->getPersonDataForRefValues($data);
		if(!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/Person/PersonForInnova",
	 *     	tags={"Person"},
	 *     	summary="Получение данных о человеке для сервиса 'Иннова'",
	 * 		@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
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
	function PersonForInnova_get() {
		$data = $this->ProcessInputData('getPersonForInnova', null, true);
		$response = $this->dbmodel->getPersonForInnova($data);
		if(!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
