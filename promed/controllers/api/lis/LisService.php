<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class LisService
 * @OA\Tag(
 *     name="LisService",
 *     description="Взаимодействие со службой ЛИС"
 * )
 */
class LisService extends SwREST_Controller {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);

		$this->load->model('Lis_model', 'dbmodel');
		$this->load->helper('Xml');
		$this->load->model('Options_model', 'Options_model');
		$dbres = $this->Options_model->getDataStorageValues(array('DataStorageGroup_SysNick'=>'lis'), array());
		$options = array();
		foreach($dbres as $value) {
			$options[$value['DataStorage_Name']] = $value['DataStorage_Value'];
		}
		$this->server = array(
			'address'     => $options['lis_address'    ],
			'server'      => $options['lis_server'     ],
			'port'        => $options['lis_port'       ],
			'path'        => $options['lis_path'       ],
			'version'     => $options['lis_version'    ],
			'buildnumber' => $options['lis_buildnumber'],
		);
	}

	/**
	 * @OA\Get(
	 *     	path="/api/LisService/checkLabSamples",
	 *  	tags={"LisService"},
	 *	    summary="Получение результатов из ЛИС по всем отправленным пробам без результата",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function checkLabSamples_get() {
		$this->dbmodel->checkLisLabSamples();
		$this->response(array('error_code' => 0));
	}
}