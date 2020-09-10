<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnAgg - контроллер API для работы с осложнениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnAgg extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnAgg_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Получение информации по осложнению услуги
	 */
	function index_get() {
		$data = $this->ProcessInputData('loadEvnAgg');

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->loadEvnAgg($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Создание осложнения на услуге
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnAgg');
		$data['EvnAgg_pid'] = $data['Evn_id'];
		$data['EvnAgg_id'] = null;

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Lpu_id'] = $sp['Lpu_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['PersonEvn_id'] = null;
		$data['EvnAgg_setDate'] = null;
		$data['EvnAgg_setTime'] = null;

		$this->load->model('EvnUsluga_model', 'eumodel');
		$evnData = $this->eumodel->loadEvnUslugaEvnData(array('Evn_pid'=>$data['Evn_id']));
		if(!empty($evnData[0]['Lpu_id'])){
			$data['Lpu_id'] = $evnData[0]['Lpu_id'];
		}
		
		$resp = $this->dbmodel->saveEvnAgg($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnAgg_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('EvnAgg_id'=>$resp[0]['EvnAgg_id'])
		));
	}

	/**
	 *  Редактирование осложнения на услуге
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnAgg', null, true);
		$data['EvnAgg_pid'] = $data['Evn_id'];

		$data['PersonEvn_id'] = null;
		$data['EvnAgg_setDate'] = null;
		$data['EvnAgg_setTime'] = null;

		$resp = $this->dbmodel->loadEvnAgg(array(
			'EvnAgg_id' => $data['EvnAgg_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['EvnAgg_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6',
				'data' => ''
			));
		}

		$evnId = '';
		if(!empty($data['Evn_id'])){
			$evnId = $data['Evn_id'];
		} else if(!empty($resp[0]['Evn_id'])){
			$evnId = $resp[0]['Evn_id'];
		}
		$this->load->model('EvnUsluga_model', 'eumodel');
		$evnData = $this->eumodel->loadEvnUslugaEvnData(array('Evn_pid'=>$evnId));
		if(!empty($evnData[0]['Lpu_id'])){
			$data['Lpu_id'] = $evnData[0]['Lpu_id'];
		}
		
		$resp = $this->dbmodel->saveEvnAgg($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnAgg_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => ''
		));
	}

	/**
	 *  Получение списка осложнений на услуге
	 */
	function EvnAggList_get() {
		$data = $this->ProcessInputData('loadEvnAggList', null, true);

		$resp = $this->dbmodel->loadEvnAgg($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}