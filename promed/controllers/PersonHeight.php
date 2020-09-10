<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonHeight extends swController {
	public $inputRules = array(
		'deletePersonHeight' => array(
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonHeightEditForm' => array(
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonHeightPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLastPersonHeightMeasure' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonHeightGrid' => array(
			array(
				'default' => 'all',
				'field' => 'mode',
				'label' => 'Режим',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'HeightMeasureType_id',
				'label' => 'Тип измерения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'savePersonHeight' => array(
			array(
				'field' => 'HeightAbnormType_id',
				'label' => 'Тип отклонения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HeightMeasureType_id',
				'label' => 'Вид замера',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_Height',
				'label' => 'Рост',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_IsAbnorm',
				'label' => 'Отклонение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_setDate',
				'label' => 'Дата измерения',
				'rules' => 'trim',
				'type' => 'date'
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
	 * constructor
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonHeight_model', 'dbmodel');
	}


	/**
	*  Удаление измерения роста пациента
	*  Входящие данные: $_POST['PersonHeight_id']
	*  На выходе: JSON-строка
	*  Используется: -
	*/
	function deletePersonHeight() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deletePersonHeight']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deletePersonHeight($data);

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
			$val['Error_Msg'] = 'При удалении измерения роста пациента возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования измерения роста пациента
	*  Входящие данные: $_POST['PersonHeight_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования измерения роста пациента
	*/
	function loadPersonHeightEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonHeightEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonHeightEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка измерений роста пациента
	*  Входящие данные: $_POST['Person_id'],
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadPersonHeightGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonHeightGrid']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonHeightGrid($data);

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		$this->ReturnData($val);

		return false;
	}


	/**
	*  Сохранение измерения роста пациента
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования измерения роста пациента
	*/
	function savePersonHeight() {
		$data = $this->ProcessInputData('savePersonHeight', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePersonHeight($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка измерений роста пациента для ЭМК
	 */
	function loadPersonHeightPanel() {
		$data = $this->ProcessInputData('loadPersonHeightPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonHeightPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение последнего измерения роста пациента для панели антропометрических данных
	 */
	function loadLastPersonHeightMeasure() {
		$data = $this->ProcessInputData('loadLastPersonHeightMeasure', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLastPersonHeightMeasure($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
}
