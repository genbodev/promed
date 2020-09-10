<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Usluga - контроллер API для работы с услугами
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Usluga extends SwREST_Controller {
	protected  $inputRules = array(
		'loadUslugaComplexTariffList' => array(
			array('field' => 'IsForGrid', 'label' => 'Признак загрузки данных в грид', 'rules' => '', 'type' => 'int'),
			array('field' => 'UEDAboveZero', 'label' => 'УЕТ больше 0', 'rules' => '', 'type' => 'int'),
			array('field' => 'IsSmp', 'label' => 'СМП', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplexTariff_Date', 'label' => 'Дата актуальности тарифа', 'rules' => '', 'type' => 'date'),
			array('field' => 'UslugaComplexTariff_id', 'label' => 'Идентификатор тарифа', 'rules' => '', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->checkAuth();
	}

	/**
	 * Функция чтения списка тарифов для выбранной комплексной услуги
	 * Аналог controllers/Usluga.php
	 */
	function mloadUslugaComplexTariffList_get(){
		$data = $this->ProcessInputData('loadUslugaComplexTariffList', null, true);

		if(empty($data['UslugaComplexTariff_id'])){
			if(empty($data['PayType_id'])){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не указан вид оплаты'
				));
			}
			elseif(empty($data['Person_id'])){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не указан идентификатор пациента'
				));
			}
			elseif(empty($data['UslugaComplex_id'])){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не указана услуга'
				));
			}
			elseif(empty($data['UslugaComplexTariff_Date'])){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не указана дата оказания услуги'
				));
			}
		}

		$this->load->database();
		$this->load->model('Usluga_model', 'dbmodel');

		$response = $this->dbmodel->loadUslugaComplexTariffList($data);

		if($response === false){
			//$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			$this->response(array(
				'error_code' => 7,
				'error_msg' => 'SQL запрос вернул false'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $response
		));
	}
}
