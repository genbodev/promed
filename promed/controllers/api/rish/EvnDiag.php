<?php defined('BASEPATH') or die ('No direct script access allowed');


require(APPPATH.'libraries/SwREST_Controller.php');

class EvnDiag extends SwREST_Controller{
	protected $inputRules = array(
			'mSaveEvnDiagPS' => array(
				array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DiagSetPhase_id', 'label' => 'Фаза/стадия', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDiagPS_PhaseDescr', 'label' => 'Расшифровка', 'rules' => '', 'type' => 'string'),
				array('field' => 'DiagSetClass_id', 'label' => 'Вид диагноза', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DiagSetType_id', 'label' => 'Тип диагноза', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnDiagPS_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required|zero', 'type' => 'id'),
				array('field' => 'EvnDiagPS_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnDiagPS_setDate', 'label' => 'Дата установки диагноза', 'rules' => 'trim|required', 'type' => 'date'),
				array('field' => 'EvnDiagPS_setTime', 'label' => 'Время установки диагноза', 'rules' => 'trim', 'type' => 'time'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int')
			),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnDiag_model', 'dbmodel');
	}

	/**
	 *
	@OA\post(
	path="/api/EvnDiag/mSaveEvnDiagPS",
	tags={"EvnDiag"},
	summary="Сохранение диагноза для стационара",

	@OA\Parameter(
	name="Diag_id",
	in="query",
	description="Диагноз",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DiagSetPhase_id",
	in="query",
	description="Фаза/стадия",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDiagPS_PhaseDescr",
	in="query",
	description="Расшифровка",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="DiagSetClass_id",
	in="query",
	description="Вид диагноза",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DiagSetType_id",
	in="query",
	description="Тип диагноза",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDiagPS_id",
	in="query",
	description="Идентификатор диагноза",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDiagPS_pid",
	in="query",
	description="Идентификатор родительского события",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDiagPS_setDate",
	in="query",
	description="Дата установки диагноза",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnDiagPS_setTime",
	in="query",
	description="Время установки диагноза",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор человека",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonEvn_id",
	in="query",
	description="Идентификатор состояния человека",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Server_id",
	in="query",
	description="Идентификатор сервера",
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
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="string",

	)

	)
	)

	)
	 */
	function mSaveEvnDiagPS_post() {
		$data = $this->ProcessInputData('mSaveEvnDiagPS',false, true);
		try {
			$response = $this->dbmodel->mSaveEvnDiagPS($data);
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0 ,'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);
	}
}