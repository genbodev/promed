<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* @property EvnAgg_model $dbmodel
*/

class EvnAgg extends swController {
	public $inputRules = array(
		'deleteEvnAgg' => array(
			array(
				'field' => 'EvnAgg_id',
				'label' => 'Идентификатор осложнения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnAggEditForm' => array(
			array(
				'field' => 'EvnAgg_id',
				'label' => 'Идентификатор осложнения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnAggGrid' => array(
			array(
				'field' => 'EvnAgg_pid',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnAgg' => array(
			array(
				'field' => 'AggType_id',
				'label' => 'Вид осложнения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AggWhen_id',
				'label' => 'Контекст осложнения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnAgg_id',
				'label' => 'Идентификатор осложнения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnAgg_pid',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnAgg_setDate',
				'label' => 'Дата осложнения',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnAgg_setTime',
				'label' => 'Время',
				'rules' => 'trim',
				'type' => 'time'
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
		),

		'saveEvnAggFromJson' => array(
			array(
				'field' => 'EvnAgg_pid',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * EvnAgg constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnAgg_model', 'dbmodel');
	}


	/**
	*  Удаление осложнения
	*  Входящие данные: $_POST['EvnAgg_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования услуги
	*/
	function deleteEvnAgg() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteEvnAgg', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteEvnAgg($data);

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
			$val['Error_Msg'] = 'При удалении осложнения возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования осложнения
	*  Входящие данные: $_POST['EvnAgg_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования осложнения
	*/
	function loadEvnAggEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnAggEditForm', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadEvnAggEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка осложнений
	*  Входящие данные: $_POST['EvnAgg_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования услуги, оказываемой пациенту
	*/
	function loadEvnAggGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnAggGrid', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadEvnAggGrid($data);

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Сохранение осложнения
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: новая форма редактирования осложнения
	*/
	function saveEvnAgg() {
		$data = array();
		$val  = array();

		$jsonPost = file_get_contents('php://input');
		$jsonData = json_decode($jsonPost, true);

		if ( ! is_array($jsonData))
		{
			//Получаем сессионные переменные
			$data = $this->ProcessInputData('saveEvnAgg', true);
		} else
		{	// для rec.save()
			$data = $this->ProcessInputData('saveEvnAggFromJson', true);

			//$arr = array();

			//getInputParams($arr, $this->inputRules['saveEvnAgg'], false, $jsonData);
			$data['EvnAgg_id'] = null;
			$data['Person_id'] = $jsonData['Person_id'];
			$data['PersonEvn_id'] = $jsonData['PersonEvn_id'];
			$data['Server_id'] = $jsonData['Server_id'];
			$data['EvnAgg_setDate'] = $jsonData['EvnAgg_setDate'];
			$data['EvnAgg_setTime'] = $jsonData['EvnAgg_setTime'];
			$data['AggType_id'] = $jsonData['AggType_id'];
			$data['AggWhen_id'] = $jsonData['AggWhen_id'];
		}

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveEvnAgg($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];
			if ( strlen($val['Error_Msg']) == 0 ) {
				$val['success'] = true;
			}
			else {
				$val['success'] = false;
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении осложнения');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
