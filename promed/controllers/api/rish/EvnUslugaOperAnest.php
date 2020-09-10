<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnUslugaOperAnest - контроллер API для работы с информации по анестезии
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

class EvnUslugaOperAnest extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaOperAnest_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Получение информации по анестезии
	 */
	function index_get() {
		$data = $this->ProcessInputData('loadEvnUslugaOperAnest');

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
		$resp = $this->dbmodel->loadEvnUslugaOperAnest($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Создание анестезии на оперативной услуге
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnUslugaOperAnest');
		$data['EvnUslugaOperAnest_pid'] = $data['EvnUslugaOper_id'];

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveEvnUslugaOperAnest($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaOperAnest_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('EvnUslugaOperAnest_id'=>$resp[0]['EvnUslugaOperAnest_id'])
		));
	}

	/**
	 *  Редактирование анестезии на оперативной услуге
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnUslugaOperAnest', null, true);
		$data['EvnUslugaOperAnest_pid'] = $data['EvnUslugaOper_id'];

		$resp = $this->dbmodel->loadEvnUslugaOperAnest(array(
			'EvnUslugaOperAnest_id' => $data['EvnUslugaOperAnest_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['EvnUslugaOperAnest_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора услуги',
				'error_code' => '6',
				'data' => ''
			));
		}
		
		$resp = $this->dbmodel->saveEvnUslugaOperAnest($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaOperAnest_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => ''
		));
	}

	/**
	 *  Получение списка анестезии по услуге
	 */
	function EvnUslugaOperAnestList_get() {
		$data = $this->ProcessInputData('loadEvnUslugaOperAnestList', null, true);

		$resp = $this->dbmodel->loadEvnUslugaOperAnest($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}