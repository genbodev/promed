<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonBloodGroup extends swController {
	public $inputRules = array(
		'deletePersonBloodGroup' => array(
			array(
				'field' => 'PersonBloodGroup_id',
				'label' => 'Идентификатор определения группы крови',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonBloodGroupPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonBloodGroupEditForm' => array(
			array(
				'field' => 'PersonBloodGroup_id',
				'label' => 'Идентификатор определения группы крови',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'savePersonBloodGroup' => array(
			array(
				'field' => 'BloodGroupType_id',
				'label' => 'Группа крови',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RhFactorType_id',
				'label' => 'Rh-фактор крови',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonBloodGroup_setDate',
				'label' => 'Диагноз определения группы крови',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'PersonBloodGroup_id',
				'label' => 'Идентификатор определения группы крови',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'getPersonBloodGroup' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * PersonBloodGroup constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonBloodGroup_model', 'dbmodel');
	}


	/**
	*  Удаление определения группы крови
	*  Входящие данные: $_POST['PersonBloodGroup_id']
	*  На выходе: JSON-строка
	*  Используется: -
	*/
	function deletePersonBloodGroup() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deletePersonBloodGroup']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deletePersonBloodGroup($data);

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
			$val['Error_Msg'] = 'При удалении определения группы крови возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования группы крови и Rh-фактора
	*  Входящие данные: $_POST['PersonBloodGroup_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования группы крови и Rh-фактора
	*/
	function loadPersonBloodGroupEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonBloodGroupEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonBloodGroupEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Сохранение определения группы крови
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования определения группы крови
	*/
	function savePersonBloodGroup() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['savePersonBloodGroup']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->savePersonBloodGroup($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];

			if ( array_key_exists('Error_Msg', $val) && empty($val['Error_Msg']) ) {
				$val['success'] = true;
				$val['PersonBloodGroup_id'] = $response[0]['PersonBloodGroup_id'];
			}
			else {
				$val['success'] = false;
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении определения группы крови');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 * Получение показателей крови человека
	 */
	function getPersonBloodGroup() {
		$data = $this->ProcessInputData('getPersonBloodGroup', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getPersonBloodGroup($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка групп крови пациента для ЭМК
	 */
	function loadPersonBloodGroupPanel() {
		$data = $this->ProcessInputData('loadPersonBloodGroupPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonBloodGroupPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
}
