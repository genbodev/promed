<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicTalon - контроллер API для работы с талоном эл. очереди
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

class ElectronicTalon extends SwREST_Controller {

	//public $textlog = "";

	protected  $inputRules = array(
		'sendElectronicQueueNodeMessage' => array(
			array('field' => 'message', 'label' => 'Сообщение', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'ElectronicTalon_id', 'label' => 'Идентификатор талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicService_id', 'label' => 'Идентификатор сервиса', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicQueueInfo_id', 'label' => 'Идентификатор ЭО', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => '', 'type' => 'id'),
			array('field' => 'msfReplaceElectronicService_id', 'label' => 'Идентификатор ПО зам. врача', 'rules' => '', 'type' => 'id'),
		),
		'setElectronicTalonStatus' => array(
			array('field' => 'ElectronicTalon_id', 'label' => 'Идентификатор талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicService_id', 'label' => 'Идентификатор ПО', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTalonStatus_id', 'label' => 'Идентификатор статуса талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя системы', 'rules' => '', 'type' => 'id'),
			array('field' => 'cancelCallCount', 'label' => 'число вызовов до отмены', 'rules' => '', 'type' => 'int'),
		),
		'redirectElectronicTalon' => array(
			array(
				'field' => 'ElectronicTalon_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicService_id',
				'label' => 'Идентификатор пункта обслуживания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_id',
				'label' => 'Идентификатор услуги на службе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'Краткое наименование типа службы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор текущего направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления для перенаправления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Идентификатор профиля отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'From_MedStaffFact_id',
				'label' => 'Идентификатор текущего места работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор персонала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'redirectBack',
				'label' => 'Признак вовзрата талона на предыдущий ПО',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор ЭО куда переадресовали',
				'rules' => '',
				'type' => 'id'
			)
		),
		'finishCall' => array(
			array('field' => 'ElectronicTalon_id', 'label' => 'Идентификатор талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicService_id', 'label' => 'Идентификатор ПО', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTalonStatus_id', 'label' => 'Идентификатор статуса талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
		),
		'takeLabSample' => array(
			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'MedServiceType_SysNick',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedService_did',
				'label' => 'Служба, где взята проба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLabRequests',
				'label' => 'Заявки',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveEvnLabSampleBarcode' => array(
			array(
				'field' => 'EvnLabSample_id',
				'label' => 'Идентификатор пробы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLabSample_BarCode',
				'label' => 'Номер штрих-кода',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'updateEvnLabSampleNum',
				'label' => 'признак что номер пробы тоже нужно сохранить',
				'rules' => '',
				'type' => 'int'
			),
		),
		'cancelLabSample' => array(
			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'MedServiceType_SysNick',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedService_did',
				'label' => 'Служба, где взята проба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLabRequests',
				'label' => 'Заявки',
				'rules' => '',
				'type' => 'string'
			)
		),
		'createEvnPLDispAndAgreeConsent' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор класса диспансеризации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Возрастная группа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'makeEvnLabRequest' => array(
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_pzid',
				'label' => 'Идентификатор пункта забора',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLabRequest_prmTime',
				'label' => 'Время записи на бирку',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadScheduleWorkDoctor' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Должность', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Кабинет', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_id', 'label' => 'Участок', 'rules' => '', 'type' => 'id'),

			array('field' => 'mondayDate', 'label' => 'Дата начала периода', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'sundayDate', 'label' => 'Дата окончания периода', 'rules' => 'trim', 'type' => 'date'),
		),
		'loadScheduleWorkDoctorScoreboard' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Должность', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Кабинет', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_id', 'label' => 'Участок', 'rules' => '', 'type' => 'id'),

			array('field' => 'mondayDate', 'label' => 'Дата начала периода', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'sundayDate', 'label' => 'Дата окончания периода', 'rules' => 'trim', 'type' => 'date'),
		)

	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ElectronicTalon_model', 'dbmodel');
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function sendElectronicQueueNodeMessage_post() {

		$data = $this->ProcessInputData('sendElectronicQueueNodeMessage', null, false, false);

		$resp = $this->dbmodel->sendElectronicQueueNodeMessage($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function setElectronicTalonStatus_post() {

		$data = $this->ProcessInputData('setElectronicTalonStatus', null, false, false);
		$data['pmUser_did'] = NULL;

		$resp = $this->dbmodel->setElectronicTalonStatus($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function finishCall_post() {

		$data = $this->ProcessInputData('finishCall', null, false, false);
		$data['pmUser_id'] = 1;

		$this->load->model('ElectronicQueue_model');
		$result = $this->ElectronicQueue_model->finishCall($data);

		if (!empty($result['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $result['Error_Msg'],
				'error_code' => '6'
			));
		}

		$response_result = array('error_code' => 0);
		if (!empty($result['nextCab']))
			$response_result['nextCab'] = $result['nextCab'];

		$this->response($response_result);
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function takeLabSample_post() {

		$data = $this->ProcessInputData('takeLabSample', null, false, false);

		$data['session']['medpersonal_id'] = NULL;
		$data['pmUser_id'] = 1;
		$_SESSION['pmuser_id'] = 1;

		$this->load->model('EvnLabRequest_model', 'elrmodel');

		$resp = $this->elrmodel->takeLabSample($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		} else unset($resp['Error_Msg']);

		$this->response(array('error_code' => 0, 'data' => $resp));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function cancelLabSample_post() {

		$data = $this->ProcessInputData('cancelLabSample', null, false, false);

		$this->load->model('EvnLabRequest_model', 'elrmodel');

		$data['session']['medpersonal_id'] = NULL;
		$data['pmUser_id'] = 1;
		$_SESSION['pmuser_id'] = 1;

		$resp = $this->elrmodel->cancelLabSample($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * сохраним навый штрих-код
	 */
	function saveEvnLabSampleBarcodeAndNum_post() {

		$data = $this->ProcessInputData('saveEvnLabSampleBarcode', null, false, false);
		$this->load->model('EvnLabSample_model', 'elsmodel');

		$data['session']['medpersonal_id'] = NULL;
		$data['pmUser_id'] = 1;
		$_SESSION['pmuser_id'] = 1;

		$resp = $this->elsmodel->saveNewEvnLabSampleBarCode($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		if (!empty($data['updateEvnLabSampleNum'])) {

			$data['EvnLabSample_ShortNum'] = substr($data['EvnLabSample_BarCode'], -4);
			$resp = $this->elsmodel->saveNewEvnLabSampleNum($data);

			if (!empty($resp['Error_Msg'])) {
				$this->response(array(
					'error_msg' => $resp['Error_Msg'],
					'error_code' => '6'
				));
			}
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * Создадим профосмотры
	 */
	function createEvnPLDispAndAgreeConsent_post() {

		$data = $this->ProcessInputData('createEvnPLDispAndAgreeConsent', null, false, false);
		$data['session'] = null;

		$this->load->model('ElectronicQueue_model');
		$result = $this->ElectronicQueue_model->createEvnPLDispAndAgreeConsent(array(
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'AgeGroupDisp_id' => $data['AgeGroupDisp_id'], // возрастная группа
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Lpu_id' => $data['Lpu_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($result['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $result['Error_Msg'],
				'error_code' => '6'
			));
		}

		$response_result = array('error_code' => 0);
		if (!empty($result['EvnPLDispTeenInspection_id']))
			$response_result['EvnPLDispTeenInspection_id'] = $result['EvnPLDispTeenInspection_id'];

		$this->response($response_result);
	}

	/**
	 * обновим направление, тем самым создадим заявку на исследование
	 */
	function makeEvnLabRequest_post() {

		$data = $this->ProcessInputData('makeEvnLabRequest', null, false, false);
		$this->load->model('EvnDirection_model');
		$this->load->model('UslugaComplex_model');

		// так как мы без авторизации
		if (!isset($_SESSION['region'])) {
			$_SESSION['region'] = array(
				'nick' => ''
			);
		}

		$uslugaComposition = $this->UslugaComplex_model->getUslugaComplexComposition($data);
		if (!empty($uslugaComposition) && is_array($uslugaComposition)) {
			$uslugaComposition = array_column($uslugaComposition, 'UslugaComplex_id');
		} else {
			$uslugaComposition = array();
		}

		$result = $this->EvnDirection_model->makeEvnLabRequest(array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'MedService_id' => $data['MedService_id'],
			'MedService_pzid' => $data['MedService_pzid'],
			'EvnLabRequest_prmTime' => $data['EvnLabRequest_prmTime'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'order' => json_encode(array('UslugaComplex_id' => $data['UslugaComplex_id'], 'checked' => json_encode($uslugaComposition))),
			'EvnLabRequest_IsCito' => 1,
			'session' => null,
			'pmUser_id' => $data['pmUser_id'],
			'PayType_id' => 1 // ОМС
		));


		if (!empty($result['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $result['Error_Msg'],
				'error_code' => '6'
			));
		}

		$response_result = array('error_code' => 0);
		if (is_array($result)) {
			$response_result = array_merge($response_result, $result);
		}

		$this->response($response_result);
	}

	/**
	 * Получение расписания врачей на неделю
	 */
	function loadScheduleWorkDoctor_post() {

		$data = $this->ProcessInputData('loadScheduleWorkDoctor');
		$this->load->model('LpuBuildingOfficeMedStaffLink_model');

		$data['fromApi'] = true;

		$resp = $this->LpuBuildingOfficeMedStaffLink_model->loadScheduleWorkDoctor($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response($resp);
	}

	/**
	 * Получение расписания врачей на неделю
	 */
	function loadScheduleWorkDoctorScoreboard_post() {

		$data = $this->ProcessInputData('loadScheduleWorkDoctorScoreboard');
		$this->load->model('LpuBuildingOfficeMedStaffLink_model');

		$data['fromApi'] = true;

		$resp = $this->LpuBuildingOfficeMedStaffLink_model->loadScheduleWorkDoctorScoreboard($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('data'=>$resp));
	}

	/**
	 * Переадресация талона ЭО
	 */
	function redirectElectronicTalon_post() {

		$data = $this->ProcessInputData('redirectElectronicTalon');
		$this->load->model('ElectronicTalon_model');

		$resp = $this->ElectronicTalon_model->redirectElectronicTalon($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response($resp);
	}
}