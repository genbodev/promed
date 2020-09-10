<?php	defined('BASEPATH') or die ('No direct script access allowed');
class EvnOtherSectionBedProfile extends swController {
	public $inputRules = array(
		'deleteEvnOtherSectionBedProfile' => array(
			array(
				'field' => 'EvnOtherSectionBedProfile_id',
				'label' => 'Идентификатор случая перевода пациента на другой профиль коек',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnOtherSectionBedProfileEditForm' => array(
			array(
				'field' => 'EvnOtherSectionBedProfile_id',
				'label' => 'Идентификатор случая перевода пациента на другой профиль коек',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnOtherSectionBedProfile' => array(
			array(
				'field' => 'EvnOtherSectionBedProfile_id',
				'label' => 'Идентификатор случая перевода пациента на другой профиль коек',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherSectionBedProfile_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'from',
				'label' => 'откуда была открыта форма',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnOtherSectionBedProfile_setDate',
				'label' => 'Дата перевода',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOtherSectionBedProfile_setTime',
				'label' => 'Время перевода',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnOtherSectionBedProfile_UKL',
				'label' => 'Уровень качества лечения',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'LeaveCause_id',
				'label' => 'Причина перевода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_oid',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfile_oid',
				'label' => 'Профиль коек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDesease_id',
				'label' => 'Исход госпитализации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			)
		)
	);


	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnOtherSectionBedProfile_model', 'dbmodel');
	}


	/**
	*  Удаление случая перевода пациента на другой профиль коек из стационара
	*  Входящие данные: $_POST['EvnOtherSectionBedProfile_id']
	*  На выходе: JSON-строка
	*  Используется: ???
	*/
	function deleteEvnOtherSectionBedProfile() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deleteEvnOtherSectionBedProfile']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deleteEvnOtherSectionBedProfile($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg']) ) {
				$val['success'] = true;
			}
			else {
				$val = $response[0];
				$val['success'] = false;
			}
		}
		else {
			$val['Error_Msg'] = 'При удалении случая перевода пациента на другой профиль коек возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования случая перевода пациента на другой профиль коек из стационара
	*  Входящие данные: $_POST['EvnOtherSectionBedProfile_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая перевода пациента на другой профиль коек из стационара
	*/
	function loadEvnOtherSectionBedProfileEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadEvnOtherSectionBedProfileEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadEvnOtherSectionBedProfileEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Сохранение случая перевода пациента на другой профиль коек
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая перевода пациента на другой профиль коек
	*/
	function saveEvnOtherSectionBedProfile() {
		$this->load->database();
		$this->load->model('EvnSection_model', 'esmodel');

		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['saveEvnOtherSectionBedProfile']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		// УКЛ
		if ( (!isset($data['EvnOtherSectionBedProfile_UKL'])) || ($data['EvnOtherSectionBedProfile_UKL'] <= 0) || ($data['EvnOtherSectionBedProfile_UKL'] > 1) ) {
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Ошибка при сохранении случая перевода пациента на другой профиль коек (неверно задано значение поля "УКЛ")')));
			return false;
		}

		if ( empty($data['Lpu_id']) ) {
			$this->ReturnError('Не указан идентификатор МО');
			return false;
		}
		else if ( !isset($data['Server_id']) || $data['Server_id'] < 0 ) {
			$this->ReturnError('Не указан параметр Server_id');
			return false;
		}
		/*
		// Проверяем количество записей в EvnSection
		$cnt = $this->esmodel->getEvnSectionCount(array('EvnSection_pid' => $data['EvnOtherSectionBedProfile_pid']));

		if ( $cnt == -1 ) {
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Ошибка при проверке количества записей в "Движении"')));
			return true;
		}

		if ( $cnt == 0 ) {
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Пациенту не назначено ни одно отделение. Сохранение невозможно')));
			return true;
		}
		*/
		// Запрос на сохранение
		$response = $this->dbmodel->saveEvnOtherSectionBedProfile($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];
			if ( empty($val['Error_Msg']) ) {
				$val['success'] = true;
				/*
				//хотя это не правильно, но в форме КВС такая же логика
				if($data['from'] == 'stac' && !empty($data['EvnSection_id']))
				{
					$data['EvnOtherSectionBedProfile_setDate'] .= ' ' . (empty($data['EvnOtherSectionBedProfile_setTime'])?date('H:i'):$data['EvnOtherSectionBedProfile_setTime']) . ':00.000';
					$response = $this->esmodel->setEvnSectionDisDate(array(
						'EvnSection_pid' => $data['EvnOtherSectionBedProfile_pid'],
						'EvnSection_id' => $data['EvnSection_id'],
						'EvnSection_disDT' => $data['EvnOtherSectionBedProfile_setDate'],
						'pmUser_id' => $data['pmUser_id']
					));
					$this->load->model('EvnPS_model', 'EvnPS_model');
					$response = $this->EvnPS_model->setEvnPSDisDate(array(
						'LeaveType_id' => 5,
						'EvnPS_id' => $data['EvnOtherSectionBedProfile_pid'],
						'EvnPS_disDT' => $data['EvnOtherSectionBedProfile_setDate'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
				*/
			}
			else {
				$val['success'] = false;
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении случая перевода пациента на другой профиль коек');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
