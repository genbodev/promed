<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class LisSpr
 * @property LisSpr_model dbmodel
 * @OA\Tag(
 *     name="LisSpr",
 *     description="Справочники ЛИС"
 * )
 */
class LisSpr extends SwREST_Controller {
	protected $inputRules = array(
		'loadEquipmentsGrid' => array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
		),
		'loadTestsGrid' => array(
			array('field' => 'UslugaComplexMedService_pid', 'label' => 'Идентификатор родительской услуги', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEquipmentTestsGrid' => array(
			array('field' => 'equipment_id', 'label' => 'Идентификатор анализатора', 'rules' => 'required', 'type' => 'id'),
		),
		'loadUnitList' => array(
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id',),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id',),
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
			array('field' => 'RefValues_id', 'label' => 'Референсное значение', 'rules' => '', 'type' => 'id'),
		),
		'loadTestUnitList' => array(
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id',),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id',),
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaTest_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
			array('field' => 'RefValues_id', 'label' => 'Референсное значение', 'rules' => '', 'type' => 'id'),
			array('field' => 'UnitOld_id', 'label' => 'Старое значение единицы измерения', 'rules' => '', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('LisSpr_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/LisSpr/EquipmentsGrid",
	 *  	tags={"LisSpr"},
	 *	    summary="Получение списка анализаторов",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EquipmentsGrid_get() {
		$data = $this->ProcessInputData('loadEquipmentsGrid', null, true);
		$response = $this->dbmodel->loadEquipmentsGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/LisSpr/TestsGrid",
	 *  	tags={"LisSpr"},
	 *	    summary="Получение списка тестов",
	 *     	@OA\Parameter(
	 *     		name="UslugaComplexMedService_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительской услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function TestsGrid_get() {
		$data = $this->ProcessInputData('loadTestsGrid', null, true);
		$response = $this->dbmodel->loadTestsGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/LisSpr/EquipmentTestsGrid",
	 *  	tags={"LisSpr"},
	 *	    summary="Получение списка тестов анализатора ЛИС",
	 *     	@OA\Parameter(
	 *     		name="equipment_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EquipmentTestsGrid_get() {
		$data = $this->ProcessInputData('loadEquipmentTestsGrid', null, true);
		$response = $this->dbmodel->loadEquipmentTestsGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/LisSpr/UnitList",
	 *  	tags={"LisSpr"},
	 *	    summary="Получение списка единиц измерения",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="RefValues_id",
	 *     		in="query",
	 *     		description="Идентификатор референсного значения",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function UnitList_get() {
		$data = $this->ProcessInputData('loadUnitList', null, true);
		$response = $this->dbmodel->loadUnitList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/LisSpr/TestUnitList",
	 *  	tags={"LisSpr"},
	 *	    summary="Получение списка единиц измерения",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaTest_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="RefValues_id",
	 *     		in="query",
	 *     		description="Идентификатор референсного значения",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UnitOld_id",
	 *     		in="query",
	 *     		description="Идентификатор старого значения единицы измерения",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function TestUnitList_get() {
		$data = $this->ProcessInputData('loadTestUnitList', null, true);
		$response = $this->dbmodel->loadTestUnitList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}