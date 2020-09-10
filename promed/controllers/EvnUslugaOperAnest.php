<?php	defined('BASEPATH') or die ('No direct script access allowed');
class EvnUslugaOperAnest extends swController {
	public $inputRules = array(
		'deleteEvnUslugaOperAnest' => array(
			array(
				'field' => 'EvnUslugaOperAnest_id',
				'label' => 'Идентификатор вида анестезии',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnUslugaOperAnestGrid' => array(
			array(
				'field' => 'EvnUslugaOperAnest_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnUslugaOperAnest' => array(
			array(
				'field' => 'EvnUslugaOperAnest_id',
				'label' => 'Идентификатор выполненной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaOperAnest_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AnesthesiaClass_id',
				'label' => 'Вид анестезии',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnUslugaOperAnestFromJson' => array(
			array(
				'field' => 'EvnUslugaOperAnest_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);
	
	/**
	 * EvnUslugaOperAnest constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnUslugaOperAnest_model', 'dbmodel');
	}


	/**
	*  Удаление вида анестезии
	*  Входящие данные: $_POST['EvnUslugaOperAnest_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования выполнения операции
	*/
	function deleteEvnUslugaOperAnest() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteEvnUslugaOperAnest', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteEvnUslugaOperAnest($data);

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
			$val['Error_Msg'] = 'При удалении применяемого вида анестезии возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка применяемых видов анестезии
	*  Входящие данные: $_POST['EvnUslugaOperAnest_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования выполнения операции
	*/
	function loadEvnUslugaOperAnestGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnUslugaOperAnestGrid', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadEvnUslugaOperAnestGrid($data);

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
	*  Сохранение вида анестезии
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: новая форма редактирования вида анестезии
	*/
	function saveEvnUslugaOperAnest() {
		$data = array();
		$val  = array();

		$jsonPost = file_get_contents('php://input');
		$jsonData = json_decode($jsonPost, true);

		if ( ! is_array($jsonData))
		{
			 //Получаем сессионные переменные
			$data = $this->ProcessInputData('saveEvnUslugaOperAnest', true);
		} else
		{
			$data = $this->ProcessInputData('saveEvnUslugaOperAnestFromJson', true);
			$data['AnesthesiaClass_id'] = $jsonData['AnesthesiaClass_id'];
		}

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveEvnUslugaOperAnest($data);

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
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении применяемого вида анестезии');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
