<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с очередью
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnQueue extends SwREST_Controller {
	protected  $inputRules = array(
		'getEvnQueue' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id'),
		),
		'postEvnQueue' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Направивший врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_did', 'label' => 'Идентификатор рабочего места врача, к которому в лист ожидания включается пациент', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_sid', 'label' => 'Идентификатор направившей МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_did', 'label' => 'Идентификатор МО направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnQueue_desDT', 'label' => 'Желаемая дата посещения', 'rules' => '', 'type' => 'date'),
		),
		'updateEvnQueue' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор постановки в очередь', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnQueueStatus_id', 'label' => 'Идентификатор статуса записи в очереди', 'rules' => 'required', 'type' => 'id'),
		),
		'updateEvnQueueStatus' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор постановки в очередь', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnQueueStatus_id', 'label' => 'Идентификатор статуса записи в очереди', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'QueueFailCause_id', 'label' => 'Идентификатор причины изменения порядка в очереди', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStatus_id', 'label' => 'Идентификатор статуса направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Врач, изменивший статус записи в листе ожидания', 'rules' => 'required', 'type' => 'id'),
		),
		'EvnQueueByUpdPeriod_get' => array(
			array('field' => 'Lpu_did', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_updbeg', 'label' => 'Дата начала периода изменений', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Evn_updend', 'label' => 'Дата окончания периода изменений', 'rules' => 'required', 'type' => 'date'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnQueue_model', 'dbmodel');
	}

	/**
	 * Получение листа ожидания
	 */
	function EvnQueue_get() {
		$data = $this->ProcessInputData('getEvnQueue', null, false);

		if ( empty($data['Person_id']) && empty($data['Lpu_id']) && empty($data['LpuSection_id']) && empty($data['LpuSectionProfile_id']) && empty($data['MedStaffFact_id']) ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не заполнен ни один из условных параметров'
			));
			return false;
		}

		$resp = $this->dbmodel->getEvnQueue($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			return false;
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление записи в лист ожидания
	 */
	function EvnQueue_post() {
		$data = $this->ProcessInputData('postEvnQueue', null, true);

		if ( empty($data['LpuSectionProfile_id']) && empty($data['MedStaffFact_did']) ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Должен быть заполнен или профиль, или врач, к которому в очередь ставится пациент'
			));
			return false;
		}

		if ( empty($data['LpuSectionProfile_id']) ) {
			$data['LpuSectionProfile_id'] = $this->dbmodel->getFirstResultFromQuery("select top 1 LpuSectionProfile_id from v_MedStaffFact with (nolock) where MedStaffFact_id = :MedStaffFact_id", array('MedStaffFact_id' => $data['MedStaffFact_did']));
		}

		$data['EvnDirection_IsCito'] = null;
		$data['EvnDirection_desDT'] = $data['EvnQueue_desDT'];
		$data['EvnQueueStatus_id'] = 1; // Статус записи: в очереди
		$data['RecMethodType_id'] = 13; // Способ записи: РИШ
		
		if ($this->checkPersonId($data['Person_id']) === false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Пациент не найден в системе'));
		}
		
		$fields = ['LpuSectionProfile_id', 'LpuSection_id', 'MedStaffFact_did', 'Lpu_sid', 'Lpu_did'];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}
		
		$cdata = $data;
		$cdata['LpuSectionProfile_did'] = $data['LpuSectionProfile_id'];
		
		$fields = ['Person_id', 'LpuSectionProfile_did', 'MedStaffFact_did', 'EvnQueue_desDT'];
		if ($this->commonCheckDoubles('EvnQueue', $fields, $data) !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные записи не прошли проверку на дублирование'));
		}

		$resp = $this->dbmodel->saveEvnQueue($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'EvnQueue_id' => $resp['EvnQueue_id'],
				'EvnQueueStatus_id' => $resp['EvnQueueStatus_id'],
			)
		));
	}

	/**
	 * Изменение листа ожидания
	 */
	public function EvnQueue_put() {
		$data = $this->ProcessInputData('updateEvnQueue', null, true);

		if ($data['EvnQueueStatus_id'] != 4) {
			// Идентификатор статуса записи в очереди. Может принимать значение 4 (Отмена).
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Идентификатор статуса записи в очереди может принимать значение 4 (Отмена)'
			));
		}

		$data['QueueFailCause_id'] = 8; // Отказ пациента

		$resp = $this->dbmodel->updateEvnQueueFromAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение справочника Статус очереди
	 */
	public function EvnQueueStatus_get() {
		$resp = $this->dbmodel->getEvnQueueStatus();

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Изменение статуса записи в листе ожидания
	 */
	public function EvnQueueStatus_put() {
		$data = $this->ProcessInputData('updateEvnQueueStatus', null, true);
		
		$сhk = $this->dbmodel->getFirstResultFromQuery("select top 1 EvnQueue_id from v_EvnQueue with(nolock) where EvnQueue_id = :EvnQueue_id", $data);
		if (!$сhk) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Запись о постановке в очередь с указанным EvnQueue_id не найдена в системе'
			));
		}
		
		$fields = ['EvnQueueStatus_id', 'QueueFailCause_id', 'EvnStatus_id', 'MedPersonal_id'];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}

		if ($data['EvnStatus_id'] != 12 && $data['EvnStatus_id'] != 13) {
			// Идентификатор статуса направления. Может принимать значения 12 (отменено) или 13 (отклонено).
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Идентификатор статуса направления может принимать значения 12 (отменено) или 13 (отклонено)'
			));
		}
		else if ($data['EvnQueueStatus_id'] != 4) {
			// Идентификатор статуса записи в очереди. Может принимать значение 4 (Отмена).
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Идентификатор статуса записи в очереди может принимать значение 4 (Отмена)'
			));
		}

		$resp = $this->dbmodel->setQueueFailCause($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
		));
	}

	/**
	 * Получение данных об изменениях по биркам поликлиники
	 */
	function EvnQueueByUpdPeriod_get() {
		$data = $this->ProcessInputData('EvnQueueByUpdPeriod_get');

		if (date_create($data['Evn_updend']) >= date_modify(date_create($data['Evn_updbeg']), '+1 month')) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Период между Evn_updbeg и Evn_updend не должен превышать 1 мес'
			));
		}

		$resp = $this->dbmodel->getEvnQueueByUpdPeriod($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}