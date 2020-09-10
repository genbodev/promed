<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class InnovaSysService
 * @OA\Tag(
 *     name="InnovaSysService",
 *     description="Интеграция с Innova Systems ЛИС"
 * )
 */
class InnovaSysService extends SwRest_Controller {
	protected $inputRules = array(
		'makeRequests' => array(
			array('field' => 'EvnLabRequests', 'label' => 'ид заявок', 'rules' => '', 'type' => 'json_array')
		),
		'makeRequest' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'ид заявки', 'rules' => '', 'type' => 'string')
		),
		'makeUnloadRequest' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'ид заявки', 'rules' => '', 'type' => 'string')
		),
		'makeUnloadRequests' => array(
			array('field' => 'EvnLabRequest_ids', 'label' => 'ид заявок', 'rules' => '', 'type' => 'json_array')
		),
		'parseRequest' => array(
			array('field' => 'xml', 'label' => 'путь', 'rules' => '', 'type' => 'string')
		),
		'checkForUpdates' => array(
			array('field' => 'xml', 'label' => 'путь', 'rules' => '', 'type' => 'string'),
			array('field' => 'dirName', 'label' => 'имя папки', 'rules' => '', 'type' => 'string')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('InnovaSysService_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/InnovaSysService/makeUnloadRequest",
	 *  	tags={"InnovaSysService"},
	 *	    summary="Создание одного запроса на выгрузку",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
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
	function makeUnloadRequest_post()
	{
		$data = $this->ProcessInputData('makeUnloadRequest', null, true);
		$xmls = $this->dbmodel->makeUnloadRequest($data);
		foreach($xmls as $xml) {
			$this->sendXML($xml->RequestFilter, $xml->RequestFilter->RequestCodes->String);
		}
		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/InnovaSysService/makeUnloadRequests",
	 *  	tags={"InnovaSysService"},
	 *	    summary="Создание многих запросов на выгрузку",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_ids",
	 *     					description="Список идентификаторов заявок в JSON-формате",
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
	function makeUnloadRequests_post()
	{
		$data = $this->ProcessInputData('makeUnloadRequests', null, true);
		foreach ($data['EvnLabRequest_ids'] as $id) {
			$request = array(
				'EvnLabRequest_id' => $id
			);
			$xmls = $this->dbmodel->makeUnloadRequest($request);
			foreach($xmls as $xml) {
				$this->sendXML($xml->RequestFilter, $xml->RequestFilter->RequestCodes->String);
			}
		}
		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/InnovaSysService/makeRequests",
	 *  	tags={"InnovaSysService"},
	 *	    summary="Подготовка к отправке многих новых заявок",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequests",
	 *     					description="Список идентификаторов заявок в JSON-формате",
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
	function makeRequests_post()
	{
		$data = $this->ProcessInputData('makeRequests', null, true);
		foreach ($data['EvnLabRequests'] as $id) {
			$data['EvnLabRequest_id'] = $id;
			$xmls = $this->dbmodel->makeRequest($data);
			foreach($xmls as $xml) {
				$this->sendXML($xml->Request, $xml->Request->RequestCode);
			}
		}
		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/InnovaSysService/makeRequest",
	 *  	tags={"InnovaSysService"},
	 *	    summary="Подготовка к отправке одной новой заявки",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
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
	function makeRequest_post()
	{
		$data = $this->ProcessInputData('makeRequest', null, true);
		$xmls = $this->dbmodel->makeRequest($data);
		foreach($xmls as $xml) {
			$this->sendXML($xml->Request, $xml->Request->RequestCode);
		}
		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/InnovaSysService/parseRequest",
	 *  	tags={"InnovaSysService"},
	 *	    summary="Считывание нового результата по заявке от ЛИС",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="xml",
	 *     					description="Путь до файла",
	 *     					type="string"
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
	function parseRequest_post()
	{
		$data = $this->ProcessInputData('parseRequest', null, true);
		$this->dbmodel->parseRequest($data);
		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/InnovaSysService/checkForUpdates",
	 *  	tags={"InnovaSysService"},
	 *	    summary="Автоматическая проверка обновлений папки ЛИС-МИС",
	 *     	@OA\Parameter(
	 *     		name="xml",
	 *     		in="query",
	 *     		description="Путь до файла",
	 *     		@OA\Schema(type="strging")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="dirName",
	 *     		in="query",
	 *     		description="Начальная папка",
	 *     		@OA\Schema(type="strging")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function checkForUpdates_get()
	{
		$data = $this->ProcessInputData('checkForUpdates', null, true);

		return $this->dbmodel->checkForUpdates($data);
	}

	/**
	 * отправка файла в папку МИс-ЛИС
	 */
	function sendXML($data, $name)
	{
		$xml = new DOMDocument('1.0', 'utf-16');
		$xmlData = dom_import_simplexml($data);
		$xmlData = $xml->importNode($xmlData, true);
		$xmlData = $xml->appendChild($xmlData);
		$xml = $xml->saveXML();

		if (is_dir(MIS_LIS_FOLDER)) {
			file_put_contents(MIS_LIS_FOLDER . $name . '.xml', $xml);
			return true;
		} else {
			return false;
		}
	}
}