<?php	defined('BASEPATH') or die ('No direct script access allowed');
class EvnOtherLpu extends swController {
	public $inputRules = array(
		'deleteEvnOtherLpu' => array(
			array(
				'field' => 'EvnOtherLpu_id',
				'label' => 'Идентификатор случая перевода пациента в другое ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnOtherLpuEditForm' => array(
			array(
				'field' => 'EvnOtherLpu_id',
				'label' => 'Идентификатор случая перевода пациента в другое ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnOtherLpu' => array(
			array(
				'field' => 'EvnOtherLpu_id',
				'label' => 'Идентификатор случая перевода пациента в другое ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherLpu_pid',
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
				'field' => 'EvnOtherLpu_setDate',
				'label' => 'Дата выписки',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOtherLpu_setTime',
				'label' => 'Время выписки',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnOtherLpu_UKL',
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
				'field' => 'Org_oid',
				'label' => 'ЛПУ',
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
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnOtherLpu_model', 'dbmodel');
	}


	/**
	*  Удаление случая перевода пациента в другое ЛПУ из стационара
	*  Входящие данные: $_POST['EvnOtherLpu_id']
	*  На выходе: JSON-строка
	*  Используется: ???
	*/
	function deleteEvnOtherLpu() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deleteEvnOtherLpu']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deleteEvnOtherLpu($data);

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
			$val['Error_Msg'] = 'При удалении случая перевода пациента в другое ЛПУ из стационара возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования случая перевода пациента в другое ЛПУ из стационара
	*  Входящие данные: $_POST['EvnOtherLpu_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая перевода пациента в другое ЛПУ из стационара
	*/
	function loadEvnOtherLpuEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadEvnOtherLpuEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadEvnOtherLpuEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Сохранение случая перевода пациента в другое ЛПУ из стационара
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая перевода пациента в другое ЛПУ из стационара
	*/
	function saveEvnOtherLpu() {
		$this->load->database();
		$this->load->model('EvnSection_model', 'esmodel');

		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['saveEvnOtherLpu']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		// УКЛ
		if ( (!isset($data['EvnOtherLpu_UKL'])) || ($data['EvnOtherLpu_UKL'] <= 0) || ($data['EvnOtherLpu_UKL'] > 1) ) {
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Ошибка при сохранении случая перевода пациента в другое ЛПУ из стационара (неверно задано значение поля "УКЛ")')));
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
		$cnt = $this->esmodel->getEvnSectionCount(array('EvnSection_pid' => $data['EvnOtherLpu_pid']));

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
		$response = $this->dbmodel->saveEvnOtherLpu($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];
			if ( empty($val['Error_Msg']) ) {
				$val['success'] = true;
				/*
				// закрыл за ненадобностью, т.к. это реализовано на хранимках
				if($data['from'] == 'stac' && !empty($data['EvnSection_id']))
				{
					$data['EvnOtherLpu_setDate'] .= ' ' . (empty($data['EvnOtherLpu_setTime'])?date('H:i'):$data['EvnOtherLpu_setTime']) . ':00.000';
					$response = $this->esmodel->setEvnSectionDisDate(array(
						'EvnSection_pid' => $data['EvnOtherLpu_pid'],
						'EvnSection_id' => $data['EvnSection_id'],
						'EvnSection_disDT' => $data['EvnOtherLpu_setDate'],
						'pmUser_id' => $data['pmUser_id']
					));
					$this->load->model('EvnPS_model', 'EvnPS_model');
					$response = $this->EvnPS_model->setEvnPSDisDate(array(
						'LeaveType_id' => 2,
						'EvnPS_id' => $data['EvnOtherLpu_pid'],
						'EvnPS_disDT' => $data['EvnOtherLpu_setDate'],
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
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении случая перевода пациента в другое ЛПУ из стационара');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}