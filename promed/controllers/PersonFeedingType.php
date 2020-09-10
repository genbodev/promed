<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonFeedingType extends swController {
	public $inputRules = array(
		'loadPersonFeedingTypeEditForm' => array(
			array(
				'field' => 'FeedingTypeAge_id',
				'label' => 'Идентификатор способа вскармливания',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deletePersonFeedingType' => array(
			array(
				'field' => 'FeedingTypeAge_id',
				'label' => 'Идентификатор способа вскармливания',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonFeedingType' => array(
			array(
				'field' => 'PersonChild_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			)
		),
		'savePersonFeedingType' => array(
			array(
				'field' => 'FeedingTypeAge_id',
				'label' => 'Идентификатор способа вскармливания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FeedingTypeAge_Age',
				'label' => 'Возраст (мес)',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'FeedingType_id',
				'label' => 'Вид вскармливания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			)

		)
	);

	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonFeedingType_model', 'dbmodel');
	}

	function loadPersonFeedingTypeEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonFeedingTypeEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonFeedingTypeEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	function savePersonFeedingType() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['savePersonFeedingType']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->savePersonFeedingType($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];

			if ( array_key_exists('Error_Msg', $val) && empty($val['Error_Msg']) ) {
				$val['success'] = true;
				$val['FeedingTypeAge_id'] = $response[0]['FeedingTypeAge_id'];
			}
			else {
				$val['success'] = false;
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении способа вскармливания');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	function loadPersonFeedingType(){
		$data = $this->ProcessInputData('loadPersonFeedingType', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonFeedingType($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	function deletePersonFeedingType() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deletePersonFeedingType']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deletePersonFeedingType($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			$val = $response[0];

			if ( array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg']) ) {
				$val['success'] = true;
			}
			else {
				$val = $response[0];
				$val['success'] = false;
			}
		}
		else {
			$val['Error_Msg'] = 'При удалении способа вскармливания пациента возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);

		return true;
	}
}
