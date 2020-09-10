<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnXml
 * @property EvnXmlBase_model dbmodel
 */
class EvnXml extends SwRest_Controller {
	protected $inputRules = array(
		'processingEvnLabRequest' => array(
			array('field' => 'EvnUslugaPar_oid', 'label' => 'Идентификатор заказа выполнения услуг', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequest_Comment', 'label' => 'Коментарий заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'LabSampleResultList', 'label' => 'Список результатов проб', 'rules' => '', 'type' => 'json_array', 'assoc' => true),
		),
		'deleteByEvn' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		),
		'getPrintData' => [
			['field' => 'EvnXml_id', 'label' => 'Идентификатор ротокола', 'rules' => '', 'type' => 'id'],
			['field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'],
		],
		'loadEvnXmlList' => [
			['field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'],
			['field' => 'Evn_ids', 'label' => 'Идентификаторы событий', 'rules' => '', 'type' => 'json_array', 'assoc' => true],
		],
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnXmlBase_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/EvnXml/list",
	 *     	tags={"EvnXml"},
	 *     	summary="Получение списка документов",
	 * 		@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор события",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="Evn_ids",
	 *     		in="query",
	 *     		description="Список идентификаторов событий",
	 *     		@OA\Schema(type="string", format="json")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function list_get() {
		$data = $this->ProcessInputData('loadEvnXmlList', null, true);
		$response = $this->dbmodel->loadEvnXmlList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/EvnXml/EvnLabRequest",
	 *  	tags={"EvnXml"},
	 *	    summary="Сохранение документа с типом 'Протокол лабораторной услуги'",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_oid",
	 *     					description="Идентификатор заказа выполнения услуг",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_Comment",
	 *     					description="Коментарий заявки",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LabSampleResultList",
	 *     					description="Список результатов проб",
	 *     					type="string",
	 *     					format="json"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EvnLabRequest_post() {
		$data = $this->ProcessInputData('processingEvnLabRequest', null, true);
		$response = $this->dbmodel->processingEvnLabRequest($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/EvnXml/byEvn",
	 *  	tags={"EvnXml"},
	 *	    summary="Удаление документов события",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Evn_id",
	 *     					description="Идентификатор события",
	 *     					type="integer"
	 * 					),
	 *     				required={"Evn_id"}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function byEvn_delete() {
		$data = $this->ProcessInputData('deleteByEvn', null, true);
		$response = $this->dbmodel->deleteByEvn($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}


	/**
	 * @OA\Get(
	 * 		path="/api/EvnXml/ PrintData",
	 *     	tags={"EvnXml"},
	 *     	summary="Получение данных протоколов для печати",
	 * 		@OA\Parameter(
	 *     		name="EvnXml_id",
	 *     		in="query",
	 *     		description="Идентификатор протокола",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор события",
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
	function PrintData_get() {
		$data = $this->ProcessInputData('getPrintData', null, true);
		$response = $this->dbmodel->doLoadPrintData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}
}