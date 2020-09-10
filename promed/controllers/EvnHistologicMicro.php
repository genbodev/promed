<?php	defined('BASEPATH') or die ('No direct script access allowed');
class EvnHistologicMicro extends swController {
	public $inputRules = array(
		'deleteEvnHistologicMicro' => array(
			array(
				'field' => 'EvnHistologicMicro_id',
				'label' => 'Идентификатор микроскопического описания',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnHistologicMicroEditForm' => array(
			array(
				'field' => 'EvnHistologicMicro_id',
				'label' => 'Идентификатор микроскопического описания',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnHistologicMicroGrid' => array(
			array(
				'field' => 'EvnHistologicProto_id',
				'label' => 'Идентификатор протокола патологогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnHistologicMicro' => array(
			array(
				'field' => 'HistologicSpecimenPlace_id',
				'label' => 'Откуда взят',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrescrReactionType_id',
				'label' => 'Основной метод окраски',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrescrReactionType_did',
				'label' => 'Дополнительная окраска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicMicro_id',
				'label' => 'Идентификатор микроскопическое описание препарата',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_id',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicMicro_Count',
				'label' => 'Количество кусочков',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnHistologicMicro_Descr',
				'label' => 'Микроскопическая картина',
				'rules' => 'trim',
				'type' => 'string'
			)
		)
	);


	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnHistologicMicro_model', 'dbmodel');
	}


	/**
	*  Удаление микроскопического описания
	*  Входящие данные: $_POST['EvnHistologicMicro_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования протокола патологогистологического исследования
	*/
	function deleteEvnHistologicMicro() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deleteEvnHistologicMicro']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deleteEvnHistologicMicro($data);

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
			$val['Error_Msg'] = 'При удалении микроскопического описания возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования микроскопического описания препарата
	*  Входящие данные: $_POST['EvnHistologicMicro_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования микроскопического описания препарата
	*/
	function loadEvnHistologicMicroEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadEvnHistologicMicroEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadEvnHistologicMicroEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка микроскопических описаний препаратов
	*  Входящие данные: $_POST['EvnHistologicProto_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования протокола патологогистологического исследования
	*/
	function loadEvnHistologicMicroGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadEvnHistologicMicroGrid']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadEvnHistologicMicroGrid($data);

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
	*  Сохранение микроскопического описания препарата
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования микроскопического описания препарата
	*/
	function saveEvnHistologicMicro() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['saveEvnHistologicMicro']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->saveEvnHistologicMicro($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];

			if ( array_key_exists('Error_Msg', $val) && empty($val['Error_Msg']) ) {
				$val['success'] = true;
			}
			else {
				$val['success'] = false;
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении микроскопического описания препарата');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
