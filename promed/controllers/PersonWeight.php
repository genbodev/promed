<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property PersonWeight_model dbmodel
 */
class PersonWeight extends swController {
	public $inputRules = array(
		'deletePersonWeight' => array(
			array(
				'field' => 'PersonWeight_id',
				'label' => 'Идентификатор измерения массы пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonWeightEditForm' => array(
			array(
				'field' => 'PersonWeight_id',
				'label' => 'Идентификатор измерения массы пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonWeightPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLastPersonWeightMeasure' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonWeightGrid' => array(
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
				'field' => 'WeightMeasureType_id',
				'label' => 'Тип измерения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'savePersonWeight' => array(
			array(
				'field' => 'WeightAbnormType_id',
				'label' => 'Тип отклонения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'WeightMeasureType_id',
				'label' => 'Вид замера',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Ед. измерения',
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
				'field' => 'PersonWeight_Weight',
				'label' => 'Масса',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PersonWeight_id',
				'label' => 'Идентификатор измерения массы пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonWeight_IsAbnorm',
				'label' => 'Отклонение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonWeight_setDate',
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
	 * PersonWeight constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonWeight_model', 'dbmodel');
	}


	/**
	*  Удаление измерения массы пациента
	*  Входящие данные: $_POST['PersonWeight_id']
	*  На выходе: JSON-строка
	*  Используется: -
	*/
	function deletePersonWeight() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deletePersonWeight']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deletePersonWeight($data);

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
			$val['Error_Msg'] = 'При удалении измерения массы пациента возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования измерения массы пациента
	*  Входящие данные: $_POST['PersonWeight_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования измерения массы пациента
	*/
	function loadPersonWeightEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonWeightEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonWeightEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка измерений массы пациента
	*  Входящие данные: $_POST['Person_id'],
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadPersonWeightGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonWeightGrid']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonWeightGrid($data);

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
	 * Получение списка измерений массы пациента для ЭМК
	 */
	function loadPersonWeightPanel() {
		$data = $this->ProcessInputData('loadPersonWeightPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonWeightPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение последнего измерения массы пациента для антропометрических параметров
	 */
	function loadLastPersonWeightMeasure() {
		$data = $this->ProcessInputData('loadLastPersonWeightMeasure', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLastPersonWeightMeasure($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	*  Сохранение измерения массы пациента
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования измерения массы пациента
	*/
	function savePersonWeight() {
		$data = $this->ProcessInputData('savePersonWeight', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePersonWeight($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}
}
