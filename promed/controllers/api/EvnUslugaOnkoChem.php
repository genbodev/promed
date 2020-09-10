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

class EvnUslugaOnkoChem extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoChem_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnUslugaOnkoChem' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => 'required', 'type' => 'id')
			),
			'saveEvnUslugaOnkoChem' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => 'required', 'type' => 'id'),
				array(
					'field' => 'EvnUslugaOnkoChem_id',
					'label' => 'Химиотерапевтическое лечение',
					'rules' => '',
					'type' => 'id'
				),
						/*array(
							'field' => 'EvnUslugaOnkoChem_pid',
							'label' => 'Учетный документ (посещение или движение в стационаре)',
							'rules' => '',
							'type' => 'id'
						),
						array(
							'field' => 'PersonEvn_id',
							'label' => 'Состояние данных человека',
							'rules' => 'required',
							'type' => 'id'
						),
						array(
							'field' => 'Person_id',
							'label' => 'Человек',
							'rules' => 'required',
							'type' => 'id'
						),*/
				array(
					'field' => 'Evn_setDT', 
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Evn_disDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
						/*array(
							'field' => 'Morbus_id',
							'label' => 'Заболевание',
							'rules' => 'required',
							'type' => 'id'
						),*/
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemKindType_id',
					'label' => 'Вид химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemStageType_id',
					'label' => 'Этапы лечения по химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemFocusType_id',
					'label' => 'Преимущественная направленность химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_id',
					'label' => 'Осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoTreatType_id',
					'label' => 'Характер лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoChem_Scheme',
					'label' => 'Схема химиотерапии',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugTherapyLineType_id',
					'label' => 'Линия лекарственной терапии',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTherapyLoopType_id',
					'label' => 'Цикл лекарственной терапии',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
			),
			'updateEvnUslugaOnkoChem' => array(
				array(
					'field' => 'EvnUslugaOnkoChem_id',
					'label' => 'Химиотерапевтическое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_setDT', 
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Evn_disDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemKindType_id',
					'label' => 'Вид химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemStageType_id',
					'label' => 'Этапы лечения по химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemFocusType_id',
					'label' => 'Преимущественная направленность химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_id',
					'label' => 'Осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoTreatType_id',
					'label' => 'Характер лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoChem_Scheme',
					'label' => 'Схема химиотерапии',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugTherapyLineType_id',
					'label' => 'Линия лекарственной терапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTherapyLoopType_id',
					'label' => 'Цикл лекарственной терапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnUslugaOnkoChem');

		$resp = $this->dbmodel->getEvnUslugaOnkoChemForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Создание данных по химиотерапевтическому лечению в рамках специфики онкологии
	 */
	function index_post(){
		$data = $this->ProcessInputData('saveEvnUslugaOnkoChem', null, true);
		if(!empty($data['Evn_setDT'])){
			$data['EvnUslugaOnkoChem_setDT'] = $data['Evn_setDT'];
		}
		if(!empty($data['Evn_disDT'])){
			$data['EvnUslugaOnkoChem_disDT'] = $data['Evn_disDT'];
		}
		$res = $this->dbmodel->saveEvnUslugaOnkoChemAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoChem_id'])){
			$this->response(array(
				'error_code' => 0,
				'EvnUslugaOnkoChem_id' => $res[0]['EvnUslugaOnkoChem_id']
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания данных по химиотерапевтическому лечению в рамках специфики онкологии'
			));
		}
	}
	
	/**
	 * Изменение данных по химиотерапевтическому лечению в рамках специфики онкологии
	 */
	function index_put(){
		$data = $this->ProcessInputData('updateEvnUslugaOnkoChem', null, true);
		if(empty($data['EvnUslugaOnkoChem_id'])){
			$this->response(array(
				'error_code' => 3,
				'EvnUslugaOnkoChem_id' => 'не передан обязательный параметр EvnUslugaOnkoChem_id'
			));
		}
		
		if(!empty($data['Evn_setDT'])){
			$data['EvnUslugaOnkoChem_setDT'] = $data['Evn_setDT'];
		}
		if(!empty($data['Evn_disDT'])){
			$data['EvnUslugaOnkoChem_disDT'] = $data['Evn_disDT'];
		}
		$res = $this->dbmodel->updateEvnUslugaOnkoChemAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoChem_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактировании данных по химиотерапевтическому лечению в рамках специфики онкологии'
			));
		}
	}
}