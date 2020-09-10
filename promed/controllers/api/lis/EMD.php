<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH . 'libraries/SwREST_Controller.php');

/**
 * Class EMD
 * @property Lis_EMD_model $dbmodel
 * @OA\Tag(
 *     name="EMD",
 *     description="Электронные медицинские документы"
 * )
 */
class EMD extends SwRest_Controller
{
	protected $inputRules = array(
		'loadEMDSignWindowGrid' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectIDs',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'json_array'
			)
		),
		'saveSignField' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'EMDSignDocNextVersion'  => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('EMD_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EMD/EMDSignWindowData",
	 *  	tags={"EMD"},
	 *	    summary="Получение списка документов для подписи",
	 *     	@OA\Parameter(
	 *     		name="EMDRegistry_ObjectName",
	 *     		in="query",
	 *     		description="Наименование объекта",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EMDRegistry_ObjectIDs",
	 *     		in="query",
	 *     		description="Идентификатор объекта",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EMDSignWindowGrid_get()
	{
		$data = $this->ProcessInputData('loadEMDSignWindowGrid', null, true);
		$response = $this->dbmodel->getEMDSignWindowGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EMD/saveSignField",
	 *  	tags={"EMD"},
	 *	    summary="Получение списка документов для подписи",
	 *     	@OA\Parameter(
	 *     		name="EMDRegistry_ObjectName",
	 *     		in="query",
	 *     		description="Наименование объекта",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EMDRegistry_ObjectID",
	 *     		in="query",
	 *     		description="Идентификатор объекта",
	 *     		required=true,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function saveSignField_post()
	{
		$data = $this->ProcessInputData('saveSignField', null, true);
		$response = $this->dbmodel->saveSignField($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EMD/EMDSignDocNextVersion",
	 *  	tags={"EMD"},
	 *	    summary="Получение следующего номера версии документа, если будет подписан, а так же следующий ID",
	 *     	@OA\Parameter(
	 *     		name="EMDRegistry_ObjectName",
	 *     		in="query",
	 *     		description="Наименование объекта",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EMDRegistry_ObjectID",
	 *     		in="query",
	 *     		description="Идентификатор объекта",
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
	function EMDSignDocNextVersion_get()
	{
		$data = $this->ProcessInputData('EMDSignDocNextVersion', null, true);
		$response = $this->dbmodel->getEMDSignDocNextVersion($data);
		if (empty($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

}
