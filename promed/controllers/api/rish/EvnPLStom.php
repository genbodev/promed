<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPL - контроллер API для работы с стомат. ТАП
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

class EvnPLStom extends SwREST_Controller {
	protected $inputRules = array(
		'getEvnPLStom' => array(
			array('field' => 'EvnPLStom_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnVizitPLStomList' => array(
			array('field' => 'EvnPLStom_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnDiagPLStomList' => array(
			array('field' => 'EvnPLStom_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnUslugaStomList' => array(
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id'),
		),
		'getEvnDiagPLStomSopList' => array(
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор заболевания', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnVizitPLStom' => array(
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnDiagPLStomSop' => array(
			array('field' => 'DiagPLStomSop_id', 'label' => 'Идентификатор сопутствующего диагноза', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnDiagPLStom' => array(
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор заболевания', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnUslugaStom' => array(
			array('field' => 'EvnUslugaStom_id', 'label' => 'Идентификатор стомат. услуги', 'rules' => 'required', 'type' => 'id'),
		),
		'createEvnPLStom' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'Date', 'label' => 'Дата начала случая', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'NumCard', 'label' => 'Номер карты', 'rules' => 'required|trim|max_length[10]', 'type' => 'string'),
			array('field' => 'IsFinish', 'label' => 'Признак законченности случая', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'ResultClass_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_id', 'label' => 'Исход обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLStom_UKL', 'label' => 'УКЛ', 'rules' => '', 'type' => 'int'),
			array('field' => 'Diag_lid', 'label' => 'Заключительный диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPLStom_setDate', 'label' => 'Дата посещения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'TreatmentClass_id', 'label' => 'Вид обращения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ServiceType_id', 'label' => 'Место обслуживания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'VizitType_id', 'label' => 'Цель посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MesEkb_id', 'label' => 'Идентификатор МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_uid', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Харакатер диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'Tooth_Code', 'label' => 'Зуб', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnDiagPLStom_IsClosed', 'label' => 'Признак "Заболевание закрыто"', 'rules' => 'required', 'type' => 'api_flag')
		),
		'createDiagPLStomSop' => array(
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Идентификатор характера заболевания', 'rules' => 'required', 'type' => 'id')
		),
		'updateDiagPLStomSop' => array(
			array('field' => 'DiagPLStomSop_id', 'label' => 'Идентификатор сопутствующего диагноза', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Идентификатор характера заболевания', 'rules' => '', 'type' => 'id')
		),
		'createEvnDiagPLStom' => array(
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Идентификатор характера заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPLStom_IsClosed', 'label' => 'Признак "Заболевание закрыто"', 'rules' => 'required', 'type' => 'api_flag')
		),
		'updateEvnDiagPLStom' => array(
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор заболевания', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Идентификатор характера заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPLStom_IsClosed', 'label' => 'Признак "Заболевание закрыто"', 'rules' => '', 'type' => 'api_flag')
		),
		'createEvnUslugaStom' => array(
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'PayType_id', 'label' => 'Вид оплтаы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaCategory_id', 'label' => 'Идентификатор категории услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id')
		),
		'updateEvnUslugaStom' => array(
			array('field' => 'EvnUslugaStom_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'PayType_id', 'label' => 'Вид оплтаы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaCategory_id', 'label' => 'Идентификатор категории услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id')
		),
		'updateEvnPLStom' => array(
			array('field' => 'EvnPLStom_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnPLStom_IsFinish', 'label' => 'Признак законченности случая', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'ResultClass_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_id', 'label' => 'Исход обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_lid', 'label' => 'Заключительный диагноз', 'rules' => '', 'type' => 'id')
		),
		'createEvnVizitPLStom' => array(
			array('field' => 'EvnPLStom_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnVizitPLStom_setDate', 'label' => 'Дата посещения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'TreatmentClass_id', 'label' => 'Вид обращения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ServiceType_id', 'label' => 'Место обслуживания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'VizitType_id', 'label' => 'Цель посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MesEkb_id', 'label' => 'Идентификатор МЭС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_uid', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'required', 'type' => 'id')
		),
		'updateEvnVizitPLStom' => array(
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnVizitPLStom_setDate', 'label' => 'Дата посещения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'TreatmentClass_id', 'label' => 'Вид обращения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ServiceType_id', 'label' => 'Место обслуживания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'VizitType_id', 'label' => 'Цель посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MesEkb_id', 'label' => 'Идентификатор МЭС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_uid', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
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
		$this->load->model('EvnPLStom_model', 'dbmodel');
	}

	/**
	 * Получение данных ТАП
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnPLStom', null, true);

		$resp = $this->dbmodel->getEvnPLStomForAPI($data);
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
		$data = $this->ProcessInputData('createEvnPLStom', null, true);

		if (!empty($data['IsFinish']) && $data['IsFinish'] == 2) {
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
			if (!empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed'] == 1) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Заболевание должно быть закрыто, т.к. случай лечения закончен.'
				));
			}
		}

		// сохранение ТАП
		$resp = $this->dbmodel->editEvnPLStomFromAPI($data);
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
				array('EvnPLStom_id' => $resp[0]['EvnPLStom_id'])
			)
		);

		// сохранение посещения
		$this->load->model('EvnVizitPLStom_model');
		$data['EvnPLStom_id'] = $resp[0]['EvnPLStom_id'];
		$resp = $this->EvnVizitPLStom_model->editEvnVizitPLStomFromAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}
		$response['data'][0]['EvnVizitPLStom_id'] = $resp[0]['EvnVizitPLStom_id'];

		// сохранение заболевания
		$this->load->model('EvnDiagPLStom_model');
		$data['EvnVizitPLStom_id'] = $resp[0]['EvnVizitPLStom_id'];
		$info = $this->EvnDiagPLStom_model->getFirstRowFromQuery("
			select top 1
				EVPLS.Person_id,
				EVPLS.PersonEvn_id,
				EVPLS.Server_id,
				EVPLS.Lpu_id,
				convert(varchar(10), EVPLS.EvnVizitPLStom_setDT, 120) as EvnVizitPLStom_setDate
			from v_EvnVizitPLStom EVPLS with(nolock)
			where EVPLS.EvnVizitPLStom_id = :EvnVizitPLStom_id
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$resp = $this->EvnDiagPLStom_model->saveEvnDiagPLStom(array(
			'EvnDiagPLStom_id' => null,
			'EvnDiagPLStom_pid' => $data['EvnVizitPLStom_id'],
			'Lpu_id' => $info['Lpu_id'],
			'Server_id' => $info['Server_id'],
			'PersonEvn_id' => $info['PersonEvn_id'],
			'Person_id' => $info['Person_id'],
			'EvnDiagPLStom_setDate' => $info['EvnVizitPLStom_setDate'],
			'EvnDiagPLStom_disDate' => $info['EvnVizitPLStom_setDate'],
			'Diag_id' => $data['Diag_id'],
			'DeseaseType_id' => $data['DeseaseType_id'],
			'Tooth_Code' => $data['Tooth_Code'],
			'Mes_id' => null,
			'EvnDiagPLStom_IsClosed' => (!empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed'] == 2)?true:false,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}
		$response['data'][0]['EvnDiagPLStom_id'] = $resp[0]['EvnDiagPLStom_id'];

		$this->response($response);
	}

	/**
	 * Добавление данных о ТАП
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnPLStom', null, true);

		if (!empty($data['IsFinish']) && $data['IsFinish'] == 2) {
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

		$resp = $this->dbmodel->editEvnPLStomFromAPI($data);

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
	 * Получение данных посещений в ТАП
	 */
	function EvnVizitPLStom_get() {
		$data = $this->ProcessInputData('getEvnVizitPLStom', null, true);

		$resp = $this->dbmodel->getEvnVizitPLStomForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных заболевания в ТАП
	 */
	function EvnDiagPLStom_get() {
		$data = $this->ProcessInputData('getEvnDiagPLStom', null, true);

		$resp = $this->dbmodel->getEvnDiagPLStomForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных стомат. услуги в ТАП
	 */
	function EvnUslugaStom_get() {
		$data = $this->ProcessInputData('getEvnUslugaStom', null, true);

		$resp = $this->dbmodel->getEvnUslugaStomForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных сопутствующего диагноза в ТАП
	 */
	function DiagPLStomSop_get() {
		$data = $this->ProcessInputData('getEvnDiagPLStomSop');

		$resp = $this->dbmodel->getEvnDiagPLStomSopForAPI($data);
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
	function EvnVizitPLStom_list_get() {
		$data = $this->ProcessInputData('getEvnVizitPLStomList', null, true);

		$resp = $this->dbmodel->getEvnVizitPLStomListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных заболеваний в ТАП
	 */
	function EvnDiagPLStom_list_get() {
		$data = $this->ProcessInputData('getEvnDiagPLStomList', null, true);

		$resp = $this->dbmodel->getEvnDiagPLStomListForAPI($data);
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
	function EvnVizitPLStom_post() {
		$data = $this->ProcessInputData('createEvnVizitPLStom', null, true);

		$this->load->model('EvnVizitPLStom_model');

		$resp = $this->EvnVizitPLStom_model->editEvnVizitPLStomFromAPI($data);
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
				array('EvnVizitPLStom_id' => $resp[0]['EvnVizitPLStom_id'])
			)
		);

		$this->response($response);
	}

	/**
	 * Редактирование данных о посещении
	 */
	function EvnVizitPLStom_put() {
		$data = $this->ProcessInputData('updateEvnVizitPLStom', null, true);

		$this->load->model('EvnVizitPLStom_model');

		$resp = $this->EvnVizitPLStom_model->editEvnVizitPLStomFromAPI($data);
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
	 * Получение списка стомат. услуг
	 */
	function EvnUslugaStom_list_get() {
		$data = $this->ProcessInputData('getEvnUslugaStomList', null, true);

		if (empty($data['EvnDiagPLStom_id']) && empty($data['EvnVizitPLStom_id'])) {
			$this->response(null, self::HTTP_NOT_FOUND);
		}

		$resp = $this->dbmodel->getEvnUslugaStomListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка сопутствующих диагнозов
	 */
	function DiagPLStomSop_list_get() {
		$data = $this->ProcessInputData('getEvnDiagPLStomSopList', null, true);

		$resp = $this->dbmodel->getEvnDiagPLStomSopListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных о сопутствующем диагнозе
	 */
	function DiagPLStomSop_post() {
		$data = $this->ProcessInputData('createDiagPLStomSop', null, true);

		$this->load->model('EvnDiagPLStom_model');

		$info = $this->EvnDiagPLStom_model->queryResult("
			select top 1
				EPLDS.Person_id,
				EPLDS.PersonEvn_id,
				EPLDS.Server_id,
				EPLDS.Lpu_id,
				convert(varchar(10), EPLDS.EvnDiagPLStom_setDT, 120) as EvnDiagPLStom_setDate
			from v_EvnDiagPLStom EPLDS with(nolock)
			where
				EPLDS.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and EPLDS.Lpu_id = :Lpu_id
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($info[0]['EvnDiagPLStom_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного EvnDiagPLStom_id',
				'error_code' => '6'
			));
		}

		$resp = $this->EvnDiagPLStom_model->saveEvnDiagPLStomSop(array(
			'EvnDiagPLStomSop_id' => null,
			'EvnDiagPLStomSop_pid' => $data['EvnDiagPLStom_id'],
			'Lpu_id' => $info[0]['Lpu_id'],
			'Server_id' => $info[0]['Server_id'],
			'PersonEvn_id' => $info[0]['PersonEvn_id'],
			'EvnDiagPLStomSop_setDate' => $info[0]['EvnDiagPLStom_setDate'],
			'Diag_id' => $data['Diag_id'],
			'DeseaseType_id' => $data['DeseaseType_id'],
			'Tooth_id' => null,
			'EvnDiagPLStomSop_ToothSurface' => null,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				'DiagPLStomSop_id' => $resp[0]['EvnDiagPLStomSop_id']
			)
		);

		$this->response($response);
	}

	/**
	 * Редактирование данных о сопутствующем диагнозе
	 */
	function DiagPLStomSop_put() {
		$data = $this->ProcessInputData('updateDiagPLStomSop', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createDiagPLStomSop');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$this->load->model('EvnDiagPLStom_model');

		$old_data = $this->EvnDiagPLStom_model->loadEvnDiagPLStomSopEditForm(array(
			'EvnDiagPLStomSop_id' => $data['DiagPLStomSop_id'],
			'Lpu_id' => $data['Lpu_id'],
			'session' => $data['session']
		));
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		$resp = $this->EvnDiagPLStom_model->saveEvnDiagPLStomSop($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0
		);

		$this->response($response);
	}

	/**
	 * Установка заболевания (ТАП стоматологический)
	 */
	function EvnDiagPLStom_post() {
		$data = $this->ProcessInputData('createEvnDiagPLStom', null, true);

		$this->load->model('EvnDiagPLStom_model');

		$info = $this->EvnDiagPLStom_model->getFirstRowFromQuery("
			select top 1
				EVPLS.Person_id,
				EVPLS.PersonEvn_id,
				EVPLS.Server_id,
				EVPLS.Lpu_id,
				convert(varchar(10), EVPLS.EvnVizitPLStom_setDT, 120) as EvnVizitPLStom_setDate
			from v_EvnVizitPLStom EVPLS with(nolock)
			where EVPLS.EvnVizitPLStom_id = :EvnVizitPLStom_id
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp = $this->EvnDiagPLStom_model->saveEvnDiagPLStom(array(
			'EvnDiagPLStom_id' => null,
			'EvnDiagPLStom_pid' => $data['EvnVizitPLStom_id'],
			'Lpu_id' => $info['Lpu_id'],
			'Server_id' => $info['Server_id'],
			'PersonEvn_id' => $info['PersonEvn_id'],
			'Person_id' => $info['Person_id'],
			'EvnDiagPLStom_setDate' => $info['EvnVizitPLStom_setDate'],
			'EvnDiagPLStom_disDate' => $info['EvnVizitPLStom_setDate'],
			'Diag_id' => $data['Diag_id'],
			'DeseaseType_id' => $data['DeseaseType_id'],
			'Tooth_Code' => $data['Tooth_Code'],
			'Mes_id' => null,
			'EvnDiagPLStom_IsClosed' => (!empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed'] == 2)?true:false,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				'EvnDiagPLStom_id' => $resp[0]['EvnDiagPLStom_id']
			)
		);

		$this->response($response);
	}

	/**
	 * Изменить заболевание (ТАП стоматологический)
	 */
	function EvnDiagPLStom_put() {
		$data = $this->ProcessInputData('updateEvnDiagPLStom', null, true);

		$this->load->model('EvnDiagPLStom_model');

		$info = $this->EvnDiagPLStom_model->queryResult("
			select top 1
				EDPLS.EvnDiagPLStom_id,
				EDPLS.Person_id,
				EDPLS.PersonEvn_id,
				EDPLS.Server_id,
				EDPLS.Lpu_id,
				EDPLS.EvnDiagPLStom_pid,
				convert(varchar(10), EDPLS.EvnDiagPLStom_setDT, 120) as EvnDiagPLStom_setDate,
				convert(varchar(10), EDPLS.EvnDiagPLStom_disDT, 120) as EvnDiagPLStom_disDate
			from v_EvnDiagPLStom EDPLS with(nolock)
			where
				EDPLS.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and EDPLS.Lpu_id = :Lpu_id 
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($info[0]['EvnDiagPLStom_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного EvnDiagPLStom_id',
				'error_code' => '6'
			));
		}

		$resp = $this->EvnDiagPLStom_model->saveEvnDiagPLStom(array(
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
			'EvnDiagPLStom_pid' => $info[0]['EvnDiagPLStom_pid'],
			'Lpu_id' => $info[0]['Lpu_id'],
			'Server_id' => $info[0]['Server_id'],
			'PersonEvn_id' => $info[0]['PersonEvn_id'],
			'Person_id' => $info[0]['Person_id'],
			'EvnDiagPLStom_setDate' => $info[0]['EvnDiagPLStom_setDate'],
			'EvnDiagPLStom_disDate' => $info[0]['EvnDiagPLStom_disDate'],
			'Diag_id' => $data['Diag_id'],
			'DeseaseType_id' => $data['DeseaseType_id'],
			'Tooth_Code' => $data['Tooth_Code'],
			'Mes_id' => null,
			'EvnDiagPLStom_IsClosed' => (!empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed'] == 2)?true:false,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0
		);

		$this->response($response);
	}

	/**
	 * Добавление данных о сопутствующем диагнозе
	 */
	function EvnUslugaStom_post() {
		$data = $this->ProcessInputData('createEvnUslugaStom', null, true);

		$this->load->model('EvnUsluga_model');

		$info = $this->EvnUsluga_model->queryResult("
			select top 1
				EPLDS.EvnDiagPLStom_id,
				EPLDS.Person_id,
				EPLDS.PersonEvn_id,
				EPLDS.Server_id,
				EPLDS.Lpu_id,
				convert(varchar(10), EPLDS.EvnDiagPLStom_setDT, 120) as EvnDiagPLStom_setDate,
				msf.MedPersonal_id
			from v_EvnDiagPLStom EPLDS with(nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = :MedStaffFact_id
			where
				EPLDS.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and EPLDS.Lpu_id = :Lpu_id
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($info[0]['EvnDiagPLStom_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного EvnDiagPLStom_id',
				'error_code' => '6'
			));
		}

		$resp = $this->EvnUsluga_model->saveEvnUslugaStom(array(
			'EvnUslugaStom_id' => null,
			'EvnUslugaStom_pid' => $data['EvnVizitPLStom_id'],
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $info[0]['MedPersonal_id'],
			'PayType_id' => $data['PayType_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $info[0]['Lpu_id'],
			'EvnUslugaStom_setDate' => $info[0]['EvnDiagPLStom_setDate'],
			'Person_id' => $info[0]['Person_id'],
			'Server_id' => $info[0]['Server_id'],
			'PersonEvn_id' => $info[0]['PersonEvn_id'],
			'EvnUslugaStom_Kolvo' => 1,
			'UslugaPlace_id' => 1,
			'EvnUslugaStom_Price' => null,
			'LpuSection_uid' => null,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				'EvnUslugaStom_id' => $resp[0]['EvnUslugaStom_id']
			)
		);

		$this->response($response);
	}

	/**
	 * Редактирование данных о сопутствующем диагнозе
	 */
	function EvnUslugaStom_put() {
		$data = $this->ProcessInputData('updateEvnUslugaStom', null, true);

		$this->load->model('EvnUsluga_model');

		$info = $this->EvnUsluga_model->queryResult("
			select top 1
				EPLDS.Person_id,
				EPLDS.PersonEvn_id,
				EPLDS.Server_id,
				EPLDS.Lpu_id,
				convert(varchar(10), EPLDS.EvnDiagPLStom_setDT, 120) as EvnDiagPLStom_setDate,
				msf.MedPersonal_id
			from v_EvnDiagPLStom EPLDS with(nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = :MedStaffFact_id
			where
				EPLDS.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and EPLDS.Lpu_id = :Lpu_id
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($info[0]['EvnDiagPLStom_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного EvnDiagPLStom_id',
				'error_code' => '6'
			));
		}

		$resp = $this->EvnUsluga_model->saveEvnUslugaStom(array(
			'EvnUslugaStom_id' => $data['EvnUslugaStom_id'],
			'EvnUslugaStom_pid' => $data['EvnVizitPLStom_id'],
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $info[0]['MedPersonal_id'],
			'PayType_id' => $data['PayType_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $info[0]['Lpu_id'],
			'EvnUslugaStom_setDate' => $info[0]['EvnDiagPLStom_setDate'],
			'Person_id' => $info[0]['Person_id'],
			'Server_id' => $info[0]['Server_id'],
			'PersonEvn_id' => $info[0]['PersonEvn_id'],
			'EvnUslugaStom_Kolvo' => 1,
			'UslugaPlace_id' => 1,
			'EvnUslugaStom_Price' => null,
			'LpuSection_uid' => null,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0
		);

		$this->response($response);
	}
}