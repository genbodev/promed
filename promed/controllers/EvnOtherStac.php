<?php	defined('BASEPATH') or die ('No direct script access allowed');
class EvnOtherStac extends swController {
	public $inputRules = array(
		'deleteEvnOtherStac' => array(
			array(
				'field' => 'EvnOtherStac_id',
				'label' => 'Идентификатор случая перевода пациента в стационар другого типа',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnOtherStacEditForm' => array(
			array(
				'field' => 'EvnOtherStac_id',
				'label' => 'Идентификатор случая перевода пациента в стационар другого типа',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnOtherStac' => array(
			array(
				'field' => 'EvnOtherStac_id',
				'label' => 'Идентификатор случая перевода пациента в стационар другого типа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherStac_pid',
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
				'field' => 'EvnOtherStac_setDate',
				'label' => 'Дата перевода',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOtherStac_setTime',
				'label' => 'Время перевода',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnOtherStac_UKL',
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
				'field' => 'LpuUnitType_oid',
				'label' => 'Тип стационара',
				'rules' => 'required',
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
		$this->load->model('EvnOtherStac_model', 'dbmodel');
	}


	/**
	*  Удаление случая перевода пациента в стационар другого типа
	*  Входящие данные: $_POST['EvnOtherStac_id']
	*  На выходе: JSON-строка
	*  Используется: ???
	*/
	function deleteEvnOtherStac() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deleteEvnOtherStac']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deleteEvnOtherStac($data);

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
			$val['Error_Msg'] = 'При удалении случая перевода пациента в стационар другого типа возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования случая перевода пациента в стационар другого типа
	*  Входящие данные: $_POST['EvnOtherStac_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая перевода пациента в стационар другого типа
	*/
	function loadEvnOtherStacEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadEvnOtherStacEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadEvnOtherStacEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Сохранение случая перевода пациента в стационар другого типа
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая перевода пациента в стационар другого типа
	*/
	function saveEvnOtherStac() {
		$this->load->database();
		$this->load->model('EvnSection_model', 'esmodel');

		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['saveEvnOtherStac']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		// УКЛ
		if ( (!isset($data['EvnOtherStac_UKL'])) || ($data['EvnOtherStac_UKL'] <= 0) || ($data['EvnOtherStac_UKL'] > 1) ) {
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Ошибка при сохранении случая перевода пациента в стационар другого типа (неверно задано значение поля "УКЛ")')));
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
		$cnt = $this->esmodel->getEvnSectionCount(array('EvnSection_pid' => $data['EvnOtherStac_pid']));

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
		$response = $this->dbmodel->saveEvnOtherStac($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];
			if ( strlen($val['Error_Msg']) == 0 ) {
				$val['success'] = true;
				/*
				// закрыл за ненадобностью, т.к. это реализовано на хранимках
				if($data['from'] == 'stac' && !empty($data['EvnSection_id']))
				{
					$data['EvnOtherStac_setDate'] .= ' ' . (empty($data['EvnOtherStac_setTime'])?date('H:i'):$data['EvnOtherStac_setTime']) . ':00.000';
					$response = $this->esmodel->setEvnSectionDisDate(array(
						'EvnSection_pid' => $data['EvnOtherStac_pid'],
						'EvnSection_id' => $data['EvnSection_id'],
						'EvnSection_disDT' => $data['EvnOtherStac_setDate'],
						'pmUser_id' => $data['pmUser_id']
					));
					$this->load->model('EvnPS_model', 'EvnPS_model');
					$response = $this->EvnPS_model->setEvnPSDisDate(array(
						'LeaveType_id' => 4,
						'EvnPS_id' => $data['EvnOtherStac_pid'],
						'EvnPS_disDT' => $data['EvnOtherStac_setDate'],
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
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении случая перевода пациента в стационар другого типа');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
