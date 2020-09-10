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

class EvnUslugaOnkoBeam extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoBeam_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnUslugaOnkoBeam' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => '', 'type' => 'id'),
				array(
					'field' => 'EvnUslugaOnkoBeam_id',
					'label' => 'Лучевое лечение',
					'rules' => '',
					'type' => 'id'
				),
			),
			'saveEvnUslugaOnkoBeam' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания по онкологии', 'rules' => 'required', 'type' => 'id'),
						/*array(
							'field' => 'EvnUslugaOnkoBeam_pid',
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
						),*/
				array(
					'field' => 'EvnUslugaOnkoBeam_setDT',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_disDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'datetime'
				),
						/*array(
							'field' => 'Morbus_id',
							'label' => 'Заболевание',
							'rules' => 'required',
							'type' => 'id'
						),*/
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
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_id',
					'label' => 'Лучевое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamRadioModifType_id',
					'label' => 'Радиомодификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoPlanType_id',
					'label' => 'Вид планирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_CountFractionRT',
					'label' => 'Кол-во фракций проведения лучевой терапии',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseTumor',
					'label' => 'Суммарная доза облучения опухоли',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_id',
					'label' => 'Единица измерения cуммарной дозы облучения опухоли',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseRegZone',
					'label' => 'Суммарная доза облучения зон регионарного метастазирования',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseLymph',
					'label' => 'Суммарная доза облучения на регионарные лимфоузлы',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_did',
					'label' => 'Единица измерения cуммарной дозы облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamIrradiationType_id',
					'label' => 'Способ облучения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamKindType_id',
					'label' => 'Вид лучевой терапии',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamMethodType_id',
					'label' => 'Метод лучевой терапии',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamFocusType_id',
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
					'field' => 'AggTypes',
					'label' => 'Осложнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoTreatType_id',
					'label' => 'Характер лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoRadiotherapy_id',
					'label' => 'Тип лечения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
			),
			'updateEvnUslugaOnkoBeam' => array(
				array('field' => 'EvnUslugaOnkoBeam_id', 'label' => 'Лучевое  лечение', 'rules' => 'required', 'type' => 'id'),
				array(
					'field' => 'EvnUslugaOnkoBeam_setDT',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_disDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_CountFractionRT ',
					'label' => 'Количество фракций проведения лучевой терапии',
					'rules' => '',
					'type' => 'int'
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
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_id',
					'label' => 'Лучевое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamRadioModifType_id',
					'label' => 'Радиомодификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoPlanType_id',
					'label' => 'Вид планирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_CountFractionRT',
					'label' => 'Кол-во фракций проведения лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseTumor',
					'label' => 'Суммарная доза облучения опухоли',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_id',
					'label' => 'Единица измерения cуммарной дозы облучения опухоли',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseRegZone',
					'label' => 'Суммарная доза облучения зон регионарного метастазирования',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseLymph',
					'label' => 'Суммарная доза облучения на регионарные лимфоузлы',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_did',
					'label' => 'Единица измерения cуммарной дозы облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamIrradiationType_id',
					'label' => 'Способ облучения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamKindType_id',
					'label' => 'Вид лучевой терапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamMethodType_id',
					'label' => 'Метод лучевой терапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamFocusType_id',
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
					'field' => 'AggTypes',
					'label' => 'Осложнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoTreatType_id',
					'label' => 'Характер лечения',
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
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
			),
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnUslugaOnkoBeam');

		$resp = $this->dbmodel->getEvnUslugaOnkoBeamForAPI($data);
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
		$data = $this->ProcessInputData('saveEvnUslugaOnkoBeam', null, true);
		
		$res = $this->dbmodel->saveEvnUslugaOnkoBeamAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoBeam_id'])){
			$this->response(array(
				'error_code' => 0,
				'EvnUslugaOnkoBeam_id' => $res[0]['EvnUslugaOnkoBeam_id']
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
		$data = $this->ProcessInputData('updateEvnUslugaOnkoBeam', null, true);
		
		$res = $this->dbmodel->updateEvnUslugaOnkoBeamAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnUslugaOnkoBeam_id'])){
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