<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class UnitSpr
 */
class UnitSpr extends SwRest_Controller {
	protected $inputRules = array(
		'getUnitLinkUnitConv' => array(
			array('field' => 'Unit_id', 'label' => 'Единица измерения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'baseUnit_id', 'label' => 'Единица измерения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UnitType_id', 'label' => 'Справочник единиц измерения', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('UnitSpr_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/UnitSpr/UnitConv",
	 *     	tags={"UnitSpr"},
	 *     	summary="Получение коэффициента для конвертации единиц измерения",
	 * 		@OA\Parameter(
	 *     		name="Unit_id",
	 *     		in="query",
	 *     		description="Идентификатор единицы измерения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="baseUnit_id",
	 *     		in="query",
	 *     		description="Идентификатор базовой единицы измерения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="UnitType_id",
	 *     		in="query",
	 *     		description="Идентификатор справочника единиц измерения",
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
	function UnitConv_get() {
		$data = $this->ProcessInputData('getUnitLinkUnitConv', null, true);
		$response = $this->dbmodel->getUnitLinkUnitConv($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}