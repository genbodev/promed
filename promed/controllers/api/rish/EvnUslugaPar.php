<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnUslugaPar - контроллер API для работы с параклиническими услугами
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

class EvnUslugaPar extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUslugaPar_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnUslugaPar' => array(
				array('field' => 'EvnUsluga_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_setDT', 'label' => 'Дата и время начала выполнения услуги', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id')
			),
			'createEvnUslugaPar' => array(
				array('field' => 'Evn_setDT', 'label' => 'Дата и время начала выполнения услуги', 'rules' => 'required', 'type' => 'datetime'),
				array('field' => 'Evn_disDT', 'label' => 'Дата и время окончания выполнения услуги', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение МО', 'rules' => '', 'type' => 'id', 'checklpu' => true),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Другая организация ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль отделения МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedSpecOms_id', 'label' => 'Специальность МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача, оказавшего услугу', 'rules' => '', 'type' => 'id', 'checklpu' => true),
				array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnUsluga_Kolvo', 'label' => 'Количество услуг', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'UslugaPlace_id', 'label' => 'Место выполнения', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnUslugaPar_Comment', 'label' => 'Место выполнения', 'rules' => '', 'type' => 'string')
			),
			'updateEvnUslugaPar' => array(
				array('field' => 'EvnUsluga_id', 'label' => 'Идентификатор параклинической услуги', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_setDT', 'label' => 'Дата и время начала выполнения услуги', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'Evn_disDT', 'label' => 'Дата и время окончания выполнения услуги', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение МО', 'rules' => '', 'type' => 'id', 'checklpu' => true),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Другая организация ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль отделения МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedSpecOms_id', 'label' => 'Специальность МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача, оказавшего услугу', 'rules' => '', 'type' => 'id', 'checklpu' => true),
				array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
				array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnUsluga_Kolvo', 'label' => 'Количество услуг', 'rules' => '', 'type' => 'int'),
				array('field' => 'UslugaPlace_id', 'label' => 'Место выполнения', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnUslugaPar_Comment', 'label' => 'Место выполнения', 'rules' => '', 'type' => 'string')
			)
		);
	}

	/**
	 *  Получение информации по параклинической услуге
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnUslugaPar');

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6'
			));
		}

		$sp = getSessionParams();
		$data['session'] = $sp['session'];

		$resp = $this->dbmodel->getEvnUslugaParForApi($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Создание параклинической услуги
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnUslugaPar',null,true);

		$sp = getSessionParams();
		$data['session'] = $sp['session'];
		$data['EvnUsluga_id'] = null;

		if (!empty($data['UslugaPlace_id'])) {
			switch($data['UslugaPlace_id']) {
				case 1:
					if (empty($data['LpuSection_id'])) {
						$this->response(array(
							'error_msg' => 'Поле LpuSection_id обязательно при UslugaPlace_id = 1',
							'error_code' => '6'
						));
					}
					if (empty($data['MedStaffFact_id'])) {
						$this->response(array(
							'error_msg' => 'Поле MedStaffFact_id обязательно при UslugaPlace_id = 1',
							'error_code' => '6'
						));
					}
					break;
				case 2:
					if (empty($data['Lpu_id'])) {
						$this->response(array(
							'error_msg' => 'Поле Lpu_id обязательно при UslugaPlace_id = 2',
							'error_code' => '6'
						));
					}
					if (empty($data['LpuSectionProfile_id'])) {
						$this->response(array(
							'error_msg' => 'Поле LpuSectionProfile_id обязательно при UslugaPlace_id = 2',
							'error_code' => '6'
						));
					}
					if (empty($data['MedSpecOms_id'])) {
						$this->response(array(
							'error_msg' => 'Поле MedSpecOms_id обязательно при UslugaPlace_id = 2',
							'error_code' => '6'
						));
					}
					break;
				case 3:
					if (empty($data['Org_id'])) {
						$this->response(array(
							'error_msg' => 'Поле Org_id обязательно при UslugaPlace_id = 3',
							'error_code' => '6'
						));
					}
					break;
			}
		}

		$resp = $this->dbmodel->saveEvnUslugaParFromAPI($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaPar_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('EvnUsluga_id' => $resp[0]['EvnUslugaPar_id'], 'EvnUslugaPar_id' => $resp[0]['EvnUslugaPar_id'], 'Evn_id' => $resp[0]['EvnUslugaPar_id'])
		));
	}

	/**
	 *  Редактирование параклинической услуги
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnUslugaPar',null,true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createEvnUslugaPar');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['session'] = $sp['session'];

		$old_data = $this->dbmodel->getEvnUslugaParForApi(array(
			'EvnUsluga_id' => $data['EvnUsluga_id'],
			'session' => $data['session']
		));
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		if (!empty($data['UslugaPlace_id'])) {
			switch($data['UslugaPlace_id']) {
				case 1:
					if (empty($data['LpuSection_id'])) {
						$this->response(array(
							'error_msg' => 'Поле LpuSection_id обязательно при UslugaPlace_id = 1',
							'error_code' => '6'
						));
					}
					if (empty($data['MedStaffFact_id'])) {
						$this->response(array(
							'error_msg' => 'Поле MedStaffFact_id обязательно при UslugaPlace_id = 1',
							'error_code' => '6'
						));
					}
					break;
				case 2:
					if (empty($data['Lpu_id'])) {
						$this->response(array(
							'error_msg' => 'Поле Lpu_id обязательно при UslugaPlace_id = 2',
							'error_code' => '6'
						));
					}
					if (empty($data['LpuSectionProfile_id'])) {
						$this->response(array(
							'error_msg' => 'Поле LpuSectionProfile_id обязательно при UslugaPlace_id = 2',
							'error_code' => '6'
						));
					}
					if (empty($data['MedSpecOms_id'])) {
						$this->response(array(
							'error_msg' => 'Поле MedSpecOms_id обязательно при UslugaPlace_id = 2',
							'error_code' => '6'
						));
					}
					break;
				case 3:
					if (empty($data['Org_id'])) {
						$this->response(array(
							'error_msg' => 'Поле Org_id обязательно при UslugaPlace_id = 3',
							'error_code' => '6'
						));
					}
					break;
			}
		}

		$resp = $this->dbmodel->saveEvnUslugaParFromAPI($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaPar_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}