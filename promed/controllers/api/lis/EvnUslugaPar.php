<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH . 'libraries/SwREST_Controller.php');

/**
 * Class EvnUslugaPar
 * @property EvnUslugaPar_model $dbmodel
 * @OA\Tag(
 *     name="EvnUslugaPar",
 *     description="Параклинические услуги"
 * )
 */
class EvnUslugaPar extends SwRest_Controller {
	protected $inputRules = [
		'getBactMicroList' => [
			['field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => '', 'type' => 'id']
		],
		'getBactAntibioticList' => [
			['field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => '', 'type' => 'id']
		],
		'getBactMicroIsNotFind' => [
			['field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => '', 'type' => 'id']
		]
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('EvnUslugaPar_model', 'dbmodel');
	}

	function getBactMicroList_get() {
		$data = $this->ProcessInputData('getBactMicroList', null, true);
		$response = $this->dbmodel->getBactMicroList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	function getBactAntibioticList_get() {
		$this->load->model('EvnUslugaPar_model', 'EvnUslugaPar_model');
		$data = $this->ProcessInputData('getBactAntibioticList', null, true);
		$response = $this->dbmodel->getBactAntibioticList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	function getBactMicroIsNotFind_get() {
		$this->load->model('EvnUslugaPar_model', 'EvnUslugaPar_model');
		$data = $this->ProcessInputData('getBactMicroIsNotFind', null, true);
		$response = $this->dbmodel->getBactMicroIsNotFind($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
