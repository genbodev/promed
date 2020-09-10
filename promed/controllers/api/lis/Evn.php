<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Evn
 * @property Lis_Evn_model dbmodel
 * @OA\Tag(
 *     name="Evn",
 *     description="События"
 * )
 */
class Evn extends SwRest_Controller
{
	protected $inputRules = array(
		'getPersonEvnClassList' => array(
			['field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Evn_pid', 'label' => 'Идентификатор родительского события', 'rules' => '', 'type' => 'id'],
			['field' => 'ignoreFilterByEvnPid', 'label' => 'Признак игнорирования фильтра по родительскому событию', 'rules' => '', 'type' => 'int'],
			['field' => 'person_in', 'label' => 'person_in', 'rules' => '', 'type' => 'string'],
		),
		'getRelatedEvnList' => array(
			['field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'],
		),
		'getEvnClassSysNick' => [
			['field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'],
		],
		'updateEvnStatus' => [
			['field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'],
			['field' => 'EvnStatus_id', 'label' => 'Идентификатор статуса', 'rules' => '', 'type' => 'id'],
			['field' => 'EvnStatus_SysNick', 'label' => 'Системное наименование статуса', 'rules' => '', 'type' => 'string'],
			['field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => '', 'type' => 'id'],
			['field' => 'EvnClass_SysNick', 'label' => 'Системное наименование класса события', 'rules' => '', 'type' => 'string'],
			['field' => 'EvnStatusCause_id', 'label' => 'Идентификатор причины изменения статуса события', 'rules' => '', 'type' => 'id'],
			['field' => 'EvnStatusHistory_Cause', 'label' => 'Причина изменения статуса события', 'rules' => '', 'type' => 'string'],
			['field' => 'MedServiceMedPersonal_id', 'label' => 'Идентификатор врача службы', 'rules' => '', 'type' => 'id'],
		]
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_Evn_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Evn/PersonEvnClassList",
	 *  	tags={"Evn"},
	 *	    summary="Получение данных человека",
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Evn_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="ignoreFilterByEvnPid",
	 *     		in="query",
	 *     		description="Признак игнорирования фильтра по родительскому событию",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="person_in",
	 *     		in="query",
	 *     		description="person_in",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function PersonEvnClassList_get() {
		$data = $this->ProcessInputData('getPersonEvnClassList', null, true);
		$response = $this->dbmodel->getPersonEvnClassList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Evn/RelatedEvnList",
	 *  	tags={"Evn"},
	 *	    summary="Получение списка связанных событий",
	 *     	@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор соыбтия",
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
	function RelatedEvnList_get() {
		$data = $this->ProcessInputData('getRelatedEvnList', null, true);
		$response = $this->dbmodel->getRelatedEvnList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Evn/EvnClassSysNick",
	 *  	tags={"Evn"},
	 *	    summary="Получение системного наименования типа события",
	 *     	@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор соыбтия",
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
	function EvnClassSysNick_get()
	{
		$data = $this->ProcessInputData('getEvnClassSysNick', null, false);
		$response = $this->dbmodel->getEvnClassSysNick($data);
		if ($response === false) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}
	
	function Status_put() {
		$data = $this->ProcessInputData('updateEvnStatus', null, true);
		$response = $this->dbmodel->updateEvnStatus($data);
		if ($response === false) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}
}
