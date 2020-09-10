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

class EvnUslugaOnkoGormun extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoGormun_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnUslugaOnkoGormun' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => 'required', 'type' => 'id')
			),
			'saveEvnUslugaOnkoGormun' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => 'required', 'type' => 'id'),
				//&UslugaCategory_id
				array(
					'field' => 'EvnUslugaOnkoGormun_setDT',
					'label' => 'Дата начала выполнения услуги',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_disDT',
					'label' => 'Дата окончания выполнения услуги',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Тип оплаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_id',
					'label' => 'Гормоноиммунотерапевтическое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsBeam',
					'label' => 'Вид гормоноиммунотерапии: лучевая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsSurg',
					'label' => 'Вид гормоноиммунотерапии: хирургическая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsDrug',
					'label' => 'Вид гормоноиммунотерапии: лекарственная',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsOther',
					'label' => 'Вид гормоноиммунотерапии: неизвестно',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaGormunFocusType_id',
					'label' => 'Преимущественная направленность',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_id',
					'label' => 'Осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoRadiotherapy_id',
					'label' => 'Тип лечения',
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
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_CountFractionRT',
					'label' => 'Кол-во фракций проведения лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_TotalDoseTumor',
					'label' => 'Суммарная доза облучения опухоли',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_TotalDoseRegZone',
					'label' => 'Суммарная доза облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'float'
				)
			),
			'updateEvnUslugaOnkoGormun' => array(
				array(
					'field' => 'EvnUslugaOnkoGormun_setDT',
					'label' => 'Дата начала выполнения услуги',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_disDT',
					'label' => 'Дата окончания выполнения услуги',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Тип оплаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_id',
					'label' => 'Гормоноиммунотерапевтическое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsBeam',
					'label' => 'Вид гормоноиммунотерапии: лучевая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsSurg',
					'label' => 'Вид гормоноиммунотерапии: хирургическая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsDrug',
					'label' => 'Вид гормоноиммунотерапии: лекарственная',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsOther',
					'label' => 'Вид гормоноиммунотерапии: неизвестно',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaGormunFocusType_id',
					'label' => 'Преимущественная направленность',
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
					'field' => 'OnkoRadiotherapy_id',
					'label' => 'Тип лечения',
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
				array(
					'field' => 'EvnUslugaOnkoGormun_CountFractionRT',
					'label' => 'Кол-во фракций проведения лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_TotalDoseTumor',
					'label' => 'Суммарная доза облучения опухоли',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_TotalDoseRegZone',
					'label' => 'Суммарная доза облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'float'
				)
			),
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnUslugaOnkoGormun');

		$resp = $this->dbmodel->getEvnUslugaOnkoGormunForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Создание данных по гормоноиммунотерапевтическому лечению в рамках специфики онкологии
	 */
	function index_post(){
		$data = $this->ProcessInputData('saveEvnUslugaOnkoGormun', null, true);
		
		$res = $this->dbmodel->saveEvnUslugaOnkoGormunAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoGormun_id'])){
			$this->response(array(
				'error_code' => 0,
				'EvnUslugaOnkoGormun_id' => $res[0]['EvnUslugaOnkoGormun_id']
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания данных по химиотерапевтическому лечению в рамках специфики онкологии'
			));
		}
	}
	
	/**
	 * Изменения данных по гормоноиммунотерапевтическому лечению в рамках специфики онкологии
	 */
	function index_put(){
		$data = $this->ProcessInputData('updateEvnUslugaOnkoGormun', null, true);
		if(empty($data['EvnUslugaOnkoGormun_id'])){
			$this->response(array(
				'error_code' => 3,
				'Error_Msg' => 'не передан обязательный параметр EvnUslugaOnkoGormun_id'
			));
		}
		
		$res = $this->dbmodel->updateEvnUslugaOnkoGormunAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoGormun_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактировании данных по гормоноиммунотерапевтическому лечению в рамках специфики онкологии'
			));
		}
	}
}