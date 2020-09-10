<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы со спецификой
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnUslugaOnkoNonSpec extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoNonSpec_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnUslugaOnkoNonSpec' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => '', 'type' => 'id')
			),
			'saveEvnUslugaOnkoNonSpec' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => '', 'type' => 'id'),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Название услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoNonSpec_setDT',
					'label' => 'Дата выполнения услуги',
					'rules' => 'required',
					'type' => 'date'
				),
			),
			'updateEvnUslugaOnkoNonSpec' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => '', 'type' => 'id'),
				array(
					'field' => 'EvnUslugaOnkoNonSpec_id',
					'label' => 'Название услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Название услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoNonSpec_setDT',
					'label' => 'Дата выполнения услуги',
					'rules' => '',
					'type' => 'date'
				),
			),
		);
	}

	/**
	 *  Получение данных по неспецифическому лечению в рамках специфики онкологии
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnUslugaOnkoNonSpec');

		$resp = $this->dbmodel->getEvnUslugaOnkoNonSpecForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Создание данных по неспецифическому лечению в рамках специфики онкологии
	 */
	function index_post(){
		$data = $this->ProcessInputData('saveEvnUslugaOnkoNonSpec', null, true);

		$res = $this->dbmodel->saveEvnUslugaOnkoNonSpecForAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoNonSpec_id'])){
			$this->response(array(
				'error_code' => 0,
				'EvnUslugaOnkoNonSpec_id' => $res[0]['EvnUslugaOnkoNonSpec_id']
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания данных по неспецифическому лечению в рамках специфики онкологии'
			));
		}
	}
	
	/**
	 * Изменение данных по неспецифическому лечению в рамках специфики онкологии
	 */
	function index_put(){
		$data = $this->ProcessInputData('updateEvnUslugaOnkoNonSpec', null, true);

		$res = $this->dbmodel->updateEvnUslugaOnkoNonSpecForAPI($data);
		if(!empty($res[0]['EvnUslugaOnkoNonSpec_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактировании данных по неспецифическому лечению в рамках специфики онкологии'
			));
		}
	}
}