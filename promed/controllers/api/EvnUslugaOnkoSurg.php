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

class EvnUslugaOnkoSurg extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoSurg_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnUslugaOnkoSurg' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => 'required', 'type' => 'id')
			),
			'saveEvnUslugaOnkoSurg' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => 'required', 'type' => 'id'),
						/*array(
							'field' => 'EvnUslugaOnkoSurg_pid',
							'label' => 'Учетный документ (посещение или движение в стационаре)',
							'rules' => '',
							'type' => 'id'
						),
						array(
							'field' => 'Server_id',
							'label' => 'Источник',
							'rules' => 'required',
							'type' => 'int'
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
						),
						array(
							'field' => 'Morbus_id',
							'label' => 'Заболевание',
							'rules' => 'required',
							'type' => 'id'
						),						 */
				array(
					'field' => 'EvnUslugaOnkoSurg_setDT',
					'label' => 'Дата проведения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
						/*array(
							'field' => 'EvnUslugaOnkoSurg_id',
							'label' => 'Хирургическое лечение',
							'rules' => '',
							'type' => 'id'
						),*/
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Название операции',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OperType_id',
					'label' => 'Тип операции',
					'rules' => '',
					'type' => 'id'
				),
						
				array(
					'field' => 'OnkoSurgTreatType_id',
					'label' => 'Характер хирургического лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoSurgicalType_id',
					'label' => 'Тип лечения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_id',
					'label' => 'Интраоперационное осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_sid',
					'label' => 'Послеоперационное осложнение',
					'rules' => '',
					'type' => 'id'
				),
						/*array(
							'field' => 'AggTypes',
							'label' => 'Интраоперационные осложнения',
							'rules' => '',
							'type' => 'string'
						),
						array(
							'field' => 'AggTypes2',
							'label' => 'Послеоперационные осложнения',
							'rules' => '',
							'type' => 'string'
						),
						array(
							'field' => 'MedPersonal_id',
							'label' => 'Кто проводил',
							'rules' => '',
							'type' => 'id'
						),
						array(
							'field' => 'PayType_id',
							'label' => 'Тип оплаты',
							'rules' => '',
							'type' => 'id'
						),
						array(
							'field' => 'UslugaPlace_id',
							'label' => 'Тип места проведения',
							'rules' => '',
							'type' => 'id'
						) */
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				)
			),
			'updateEvnUslugaOnkoSurg' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => '', 'type' => 'id'),
				array(
					'field' => 'EvnUslugaOnkoSurg_setDT',
					'label' => 'Дата проведения',
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
					'field' => 'EvnUslugaOnkoSurg_id',
					'label' => 'Хирургическое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Название операции',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OperType_id',
					'label' => 'Тип операции',
					'rules' => '',
					'type' => 'id'
				),
						
				array(
					'field' => 'OnkoSurgTreatType_id',
					'label' => 'Характер хирургического лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoSurgicalType_id',
					'label' => 'Тип лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_id',
					'label' => 'Интраоперационное осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_sid',
					'label' => 'Послеоперационное осложнение',
					'rules' => '',
					'type' => 'id'
				),
						/*array(
							'field' => 'AggTypes',
							'label' => 'Интраоперационные осложнения',
							'rules' => '',
							'type' => 'string'
						),
						array(
							'field' => 'AggTypes2',
							'label' => 'Послеоперационные осложнения',
							'rules' => '',
							'type' => 'string'
						),
						array(
							'field' => 'MedPersonal_id',
							'label' => 'Кто проводил',
							'rules' => '',
							'type' => 'id'
						),
						array(
							'field' => 'PayType_id',
							'label' => 'Тип оплаты',
							'rules' => '',
							'type' => 'id'
						),
						array(
							'field' => 'UslugaPlace_id',
							'label' => 'Тип места проведения',
							'rules' => '',
							'type' => 'id'
						) */
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				)
			),
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnUslugaOnkoSurg');

		$resp = $this->dbmodel->getEvnUslugaOnkoSurgForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Создание данных по хирургическому лечению в рамках специфики онкологии
	 */
	function index_post(){
		$data = $this->ProcessInputData('saveEvnUslugaOnkoSurg', null, true);
		
		$res = $this->dbmodel->saveEvnUslugaOnkoSurgAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoSurg_id'])){
			$this->response(array(
				'error_code' => 0,
				'EvnUslugaOnkoSurg_id' => $res[0]['EvnUslugaOnkoSurg_id']
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания данных по хирургическому лечению в рамках специфики онкологии'
			));
		}
	}
	
	/**
	 * Изменение данных по хирургическому лечению в рамках специфики онкологии
	 */
	function index_put(){
		$data = $this->ProcessInputData('updateEvnUslugaOnkoSurg', null, true);
		
		$res = $this->dbmodel->updateEvnUslugaOnkoSurgAPI($data);
		if(!empty($res[0]['EvnUslugaOnkoSurg_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактировании данных данных по хирургическому лечению в рамках специфики онкологии'
			));
		}
	}
}