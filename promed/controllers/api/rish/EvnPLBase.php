<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPL - контроллер API для работы с ТАП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			30.11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnPLBase extends SwREST_Controller {
	protected $inputRules = array(
		'getEvnPL' => array(
			array('field' => 'EvnPLBase_id', 'label' => 'Идентификатор ТАП', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPL_NumCard', 'label' => 'Номер талона', 'rules' => '', 'type' => 'string')
		),
		'getEvnPLBaseInfo' => array(
			array('field' => 'EvnPLBase_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnVizitPLList' => array(
			array('field' => 'EvnPLBase_id', 'label' => 'Идентификатор ТАП', 'rules' => '', 'type' => 'id'),
		),
		'getEvnVizitPL' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLBase_id', 'label' => 'Идентификатор ТАП', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата и время посещения', 'rules' => '', 'type' => 'datetime')
		),
		'mGetEvnPLInfo' => array(
			array('field' => 'EvnPL_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id')
		),
		'mGetEvnVizitPLInfo' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id')
		),
		'createEvnPL' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPL_NumCard', 'label' => 'Номер талона', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Lpu_did', 'label' => 'Направившая МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_did', 'label' => 'Направившая организация', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSection_did', 'label' => 'Идентификатор направившего отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPL_IsFinish', 'label' => 'Признак законченности случая', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'ResultClass_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_id', 'label' => 'Исход обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_lid', 'label' => 'Заключительный диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата и время посещения', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'VizitClass_id', 'label' => 'Идентификатор вида посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_sid', 'label' => 'Средний мед. персонал', 'rules' => '', 'type' => 'id', 'checklpu' => true),
			array('field' => 'TreatmentClass_id', 'label' => 'Вид обращения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ServiceType_id', 'label' => 'Место обслуживания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'VizitType_id', 'label' => 'Цель посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_uid', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPL_Time', 'label' => 'Время (мин)', 'rules' => '', 'type' => 'int'),
			array('field' => 'ProfGoal_id', 'label' => 'Цель профосмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'DispClass_id', 'label' => 'В рамках дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDisp_id', 'label' => 'Карта дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonDisp_id', 'label' => 'Идентификатор карты дисп. учёта', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Харакатер заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_agid', 'label' => 'Осложнение', 'rules' => '', 'type' => 'id'),
			array('field' => 'RankinScale_id', 'label' => 'Значение по шкале Рэнкина', 'rules' => '', 'type' => 'id'),
			array('field' => 'HomeVisit_id', 'label' => 'Идентификатор посещения на дому', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'required', 'type' => 'id')
		),
		'updateEvnPL' => array(
			array('field' => 'EvnPLBase_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'Lpu_did', 'label' => 'Направившая МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_did', 'label' => 'Направившая организация', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSection_did', 'label' => 'Идентификатор направившего отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPL_IsFinish', 'label' => 'Признак законченности случая', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'ResultClass_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_id', 'label' => 'Исход обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_lid', 'label' => 'Заключительный диагноз', 'rules' => '', 'type' => 'id')
		),
		'createEvnVizitPL' => array(
			array('field' => 'EvnPLBase_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'Evn_setDT', 'label' => 'Дата и время посещения', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'VizitClass_id', 'label' => 'Идентификатор вида посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_sid', 'label' => 'Средний мед. персонал', 'rules' => '', 'type' => 'id', 'checklpu' => true),
			array('field' => 'TreatmentClass_id', 'label' => 'Вид обращения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ServiceType_id', 'label' => 'Место обслуживания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'VizitType_id', 'label' => 'Цель посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_uid', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPL_Time', 'label' => 'Время (мин)', 'rules' => '', 'type' => 'int'),
			array('field' => 'ProfGoal_id', 'label' => 'Цель профосмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'DispClass_id', 'label' => 'В рамках дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDisp_id', 'label' => 'Карта дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonDisp_id', 'label' => 'Идентификатор карты дисп. учёта', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Харакатер заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_agid', 'label' => 'Осложнение', 'rules' => '', 'type' => 'id'),
			array('field' => 'RankinScale_id', 'label' => 'Значение по шкале Рэнкина', 'rules' => '', 'type' => 'id'),
			array('field' => 'HomeVisit_id', 'label' => 'Идентификатор посещения на дому', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'required', 'type' => 'id')
		),
		'updateEvnVizitPL' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая посещения', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата и время посещения', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'VizitClass_id', 'label' => 'Идентификатор вида посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_sid', 'label' => 'Средний мед. персонал', 'rules' => '', 'type' => 'id', 'checklpu' => true),
			array('field' => 'TreatmentClass_id', 'label' => 'Вид обращения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ServiceType_id', 'label' => 'Место обслуживания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'VizitType_id', 'label' => 'Цель посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_uid', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPL_Time', 'label' => 'Время (мин)', 'rules' => '', 'type' => 'int'),
			array('field' => 'ProfGoal_id', 'label' => 'Цель профосмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'DispClass_id', 'label' => 'В рамках дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDisp_id', 'label' => 'Карта дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonDisp_id', 'label' => 'Идентификатор карты дисп. учёта', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Харакатер заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_agid', 'label' => 'Осложнение', 'rules' => '', 'type' => 'id'),
			array('field' => 'RankinScale_id', 'label' => 'Значение по шкале Рэнкина', 'rules' => '', 'type' => 'id'),
			array('field' => 'HomeVisit_id', 'label' => 'Идентификатор посещения на дому', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnPL_model', 'dbmodel');
	}

	/**
	 * Получение данных ТАП
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnPL');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		if (empty($data['EvnPLBase_id']) && empty($data['Person_id']) && empty($data['EvnPL_NumCard'])) {
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6',
				'data' => ''
			));
		}

		$resp = $this->dbmodel->getEvnPLForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных о ТАП
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnPL');

		if (empty($GLOBALS['isSwanApiKey'])) {
			if (!empty($data['EvnPL_IsFinish']) && $data['EvnPL_IsFinish'] == 2) {
				// проверяем наличие
				if (empty($data['ResultClass_id'])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не заполнено поле ResultClass_id'
					));
				}
				if (empty($data['ResultDeseaseType_id'])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не заполнено поле ResultDeseaseType_id'
					));
				}
				if (empty($data['Diag_lid'])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не заполнено поле Diag_lid'
					));
				}
			}
		}

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$data['session'] = $sp['session'];
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->editEvnPLFromAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				array('EvnPLBase_id' => $resp[0]['EvnPL_id'])
			)
		);

		$this->load->model('EvnVizitPL_model');

		$data['EvnPLBase_id'] = $resp[0]['EvnPL_id'];
		$resp = $this->EvnVizitPL_model->editEvnVizitPLFromAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}
		$response['data'][0]['EvnVizitPL_id'] = $resp[0]['EvnVizitPL_id'];

		$this->response($response);
	}

	/**
	 * Добавление данных о ТАП
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnPL');

		if (empty($GLOBALS['isSwanApiKey'])) {
			if (!empty($data['EvnPL_IsFinish']) && $data['EvnPL_IsFinish'] == 2) {
				// проверяем наличие
				if (empty($data['ResultClass_id'])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не заполнено поле ResultClass_id'
					));
				}
				if (empty($data['ResultDeseaseType_id'])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не заполнено поле ResultDeseaseType_id'
					));
				}
				if (empty($data['Diag_lid'])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не заполнено поле Diag_lid'
					));
				}
			}
		}

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$data['session'] = $sp['session'];
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->editEvnPLFromAPI($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение данных посещений в ТАП
	 */
	function EvnVizitPL_get() {
		$data = $this->ProcessInputData('getEvnVizitPL', null, true);

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

		$resp = $this->dbmodel->getEvnVizitPLForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных ТАП
	 */
	function mGetEvnPLInfo_get() {
		$data = $this->ProcessInputData('mGetEvnPLInfo', null, true);

		$this->load->model('EPH_model', 'EPH_model');
		$resp = $this->EPH_model->loadEvnPLForm($data);

		$this->response(array(
			'error_code' => 0,
			'data' => !empty($resp) ? $resp : array()
		));
	}

	/**
	 * Получение данных посещения
	 */
	function mGetEvnVizitPLInfo_get() {
		$data = $this->ProcessInputData('mGetEvnVizitPLInfo', null, true);

		$this->load->model('EPH_model', 'EPH_model');
		$data['forMobileArm'] = true;
		$resp = $this->EPH_model->loadEvnVizitPLForm($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных посещений в ТАП
	 */
	function EvnVizitPL_list_get() {
		$data = $this->ProcessInputData('getEvnVizitPLList', null, true);

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

		$resp = $this->dbmodel->getEvnVizitPLListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных о посещении
	 */
	function EvnVizitPL_post() {
		$data = $this->ProcessInputData('createEvnVizitPL', null, true, true, true);

		$this->load->model('EvnVizitPL_model');

		$resp = $this->EvnVizitPL_model->editEvnVizitPLFromAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				array('EvnVizitPL_id' => $resp[0]['EvnVizitPL_id'], 'Evn_id' => $resp[0]['EvnVizitPL_id'])
			)
		);

		$this->response($response);
	}

	/**
	 * Редактирование данных о посещении
	 */
	function EvnVizitPL_put() {
		$data = $this->ProcessInputData('updateEvnVizitPL', null, true, true, true);

		$this->load->model('EvnVizitPL_model');

		$resp = $this->EvnVizitPL_model->editEvnVizitPLFromAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение информации по случаю амбулаторно-поликлинического лечения
	 */
	public function EvnPLBaseInfo_get() {
		$data = $this->ProcessInputData('getEvnPLBaseInfo', null, true, true, true);
		$resp = $this->dbmodel->getEvnPLBaseInfoForAPI($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}