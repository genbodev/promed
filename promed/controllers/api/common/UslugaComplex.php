<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class UslugaComplex
 */
class UslugaComplex extends SwRest_Controller {
	protected $inputRules = array(
		'findOrCreateUslugaComplexAttribute' => array(
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplexAttributeType_SysNick', 'label' => 'Системное наименование типа атрибута услуги', 'rules' => 'required', 'type' => 'string'),
		),
		'checkUslugaComplexMedServiceIsUsed' => array(
			array('field' => 'UslugaComplexMedService_id', 'label' => 'Идентифкатор связи услуги и службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'tablesToPass', 'label' => '', 'rules' => '', 'type' => 'array'),
		),
		'getUslugaComplexComposition' => array(
			array('field' => 'MedService_id', 'label' => 'Идентифкатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор комплексной услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('UslugaComplex_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 * 		path="/api/UslugaComplex/AttributeByUslugaComplex",
	 *     	tags={"UslugaComplex"},
	 *     	summary="Добавление атрибута услуги",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplexAttributeType_SysNick",
	 *     					description="Системное наименование типа атрибута услуги",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"UslugaComplex_id",
	 *     					"UslugaComplexAttributeType_SysNick"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Attribute_post() {
		$data = $this->ProcessInputData('findOrCreateUslugaComplexAttribute', null, true);
		$response = $this->dbmodel->findOrCreateUslugaComplexAttribute($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/UslugaComplex/checkUslugaComplexMedServiceIsUsed",
	 *     	tags={"UslugaComplex"},
	 *     	summary="Проверка использования услуги на службе",
	 * 		@OA\Parameter(
	 *     		name="UslugaComplexMedService_id",
	 *     		in="query",
	 *     		description="Идентификатор связи услуги и службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="tablesToPass",
	 *     		in="query",
	 *     		description="Список таблиц для проверки",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function checkUslugaComplexMedServiceIsUsed_get() {
		$data = $this->ProcessInputData('checkUslugaComplexMedServiceIsUsed', null, false);
		if (!isset($data['tablesToPass']))
			$data['tablesToPass'] = [];
		$response = $this->dbmodel->checkUslugaComplexMedServiceIsUsed($data['UslugaComplexMedService_id'], $data['tablesToPass']);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	function getUslugaComplexComposition_get() {

		$data = $this->ProcessInputData('getUslugaComplexComposition', null, false);

		$uslugaComposition = $this->dbmodel->getUslugaComplexComposition($data);
		if (!empty($uslugaComposition) && is_array($uslugaComposition)) {
			$uslugaComposition = array_column($uslugaComposition, 'UslugaComplex_id');
		} else {
			$uslugaComposition = array();
		}

		$this->response(array('error_code' => 0, 'data' => $uslugaComposition));
	}
}
