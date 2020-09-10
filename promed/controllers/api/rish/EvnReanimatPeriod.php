<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnReanimatPeriod - контроллер API для работы с движением в реанимации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @author			brotherhood of swan developers
 * @version			2019
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnReanimatPeriod extends SwREST_Controller
{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnReanimatPeriod_model', 'dbmodel');
		$this->inputRules = array(
			'moveToReanimation' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор персоны',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор медслужбы реанимации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnSection_id',
					'label' => 'Идентификатор движения пациента в отделении',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор профильного отделения пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getReanimationServices' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
	}


	/**
	 * @OA\post(
	path="/api/EvnReanimatPeriod/mMoveToReanimation",
	tags={"EvnReanimatPeriod"},
	summary="Перевод пациента в реанимацию",

	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор персоны",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedService_id",
	in="query",
	description="Идентификатор медслужбы реанимации",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_id",
	in="query",
	description="Идентификатор движения пациента в отделении",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Идентификатор профильного отделения пациента",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",
	@OA\Property(
	property="ReanimatRegister_id",
	description="Идентификатор реанимационного регистра",
	type="string",

	),
	@OA\Property(
	property="EvnReanimatPeriod_id",
	description="Идентификатор реанимационного периода",
	type="string",

	),

	@OA\Property(
	property="error_msg",
	description="Сообщение об ошибке",
	type="string",

	)
	,
	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)

	)
	)

	)

	 */
	function mMoveToReanimation_post() {
		$data = $this->ProcessInputData('moveToReanimation',false,true);
		$response = $this->dbmodel->mMoveToReanimation($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	@OA\get(
	path="/api/EvnReanimatPeriod/mGetReanimationServices",
	tags={"EvnReanimatPeriod"},
	summary="Получение реанимационных служб",

	@OA\Parameter(
	name="Lpu_id",
	in="query",
	description="Идентификатор ЛПУ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Массив данных",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="MedService_id",
	description="Cлужбы, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MedService_Nick",
	description="Cлужбы, краткое наименование",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mGetReanimationServices_get() {
		$data = $this->ProcessInputData('getReanimationServices',false, true);
		$response = $this->dbmodel->getReanimationServices($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}
}