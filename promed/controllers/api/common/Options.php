<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property Options_model dbmodel
 */
class Options extends SwRest_Controller {
	protected $inputRules = array(
		'getDataStorageValues' => array( // в чистом виде взято из контроллера EvnUsluga.php
			array(
				'field' => 'DataStorageGroup_SysNick',
				'label' => 'Объект',
				'rules' => '',
				'type' => 'string'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->model('Options_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/Options/DataStorageValues",
	 *     	tags={"Options"},
	 *     	summary="Получение настроек",
	 * 		@OA\Parameter(
	 *     		name="DataStorageGroup_SysNick",
	 *     		in="query",
	 *     		description="Объект",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DataStorageValues_get() {
		$data = $this->ProcessInputData('getDataStorageValues', null, true);
		$response = $this->dbmodel->getDataStorageValues($data, $data['session']);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * тестирование работы внутренних сервисов
	 */
	function TestSwServiceLis_get() {

		if (isset($this->_args['getDebug'])) {
			$_REQUEST['getDebug'] = true;
		}

		$data = $this->_args;
		if (isset($data['swtoken'])) unset($data['swtoken']);
		if (isset($data['getDebug'])) unset($data['getDebug']);

		$this->load->swapi('lis');
		$response = $this->lis->GET('Utils/TestSwServiceLis', $data, 'list');

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * тестирование работы внутренних сервисов
	 */
	function TestSwServiceLis_post() {

		if (isset($this->_args['getDebug'])) {
			$_REQUEST['getDebug'] = true;
		}

		$data = $this->_args;
		if (isset($data['swtoken'])) unset($data['swtoken']);
		if (isset($data['getDebug'])) unset($data['getDebug']);

		$this->load->swapi('lis');
		$response = $this->lis->POST('Utils/TestSwServiceLis', $data, 'list');

		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
