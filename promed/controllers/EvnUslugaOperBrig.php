<?php	defined('BASEPATH') or die ('No direct script access allowed');
class EvnUslugaOperBrig extends swController {
	public $inputRules = array(
		'deleteEvnUslugaOperBrig' => array(
			array(
				'field' => 'EvnUslugaOperBrig_id',
				'label' => 'Идентификатор операционной бригады',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnUslugaOperBrigGrid' => array(
			array(
				'field' => 'EvnUslugaOperBrig_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnUslugaOperBrig' => array(
			array(
				'field' => 'EvnUslugaOperBrig_id',
				'label' => 'Идентификатор выполненной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaOperBrig_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaOper_setDate',
				'label' => 'Дата выполнения операции',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'SurgType_id',
				'label' => 'Вид',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnUslugaOperBrigFromJson' => array(
			array(
				'field' => 'EvnUslugaOperBrig_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
		),

		'checkMedStaffFactIsOpen' => array(
			array(
				'field' => 'setDT',
				'label' => 'Дата выполнения операции',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);


	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnUslugaOperBrig_model', 'dbmodel');
	}


	/**
	*  Удаление операционной бригады
	*  Входящие данные: $_POST['EvnUslugaOperBrig_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования выполнения операции
	*/
	function deleteEvnUslugaOperBrig() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteEvnUslugaOperBrig', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->deleteEvnUslugaOperBrig($data);

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
			$val['Error_Msg'] = 'При удалении операционной бригады возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка операционных бригад
	*  Входящие данные: $_POST['EvnUslugaOperBrig_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования выполнения операции
	*/
	function loadEvnUslugaOperBrigGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnUslugaOperBrigGrid', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnUslugaOperBrigGrid($data);

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
	*  Сохранение операционной бригады
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: новая форма редактирования операционной бригады
	*/
	function saveEvnUslugaOperBrig() {
		$data = array();
		$val  = array();


		$jsonPost = file_get_contents('php://input');
		$jsonData = json_decode($jsonPost, true);

		if ( ! is_array($jsonData))
		{
			//Получаем сессионные переменные
			$data = $this->ProcessInputData('saveEvnUslugaOperBrig', true);
		} else
		{	// для rec.save()
			$data = $this->ProcessInputData('saveEvnUslugaOperBrigFromJson', true);
			$data['EvnUslugaOperBrig_id'] = null;
			$data['EvnUslugaOper_setDate'] = $jsonData['EvnUslugaOper_setDate'];
			$data['MedPersonal_id'] = $jsonData['MedPersonal_id'];
			$data['MedStaffFact_id'] = $jsonData['MedStaffFact_id'];
			$data['SurgType_id'] = $jsonData['SurgType_id'];
		}

		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->saveEvnUslugaOperBrig($data);

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
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении операционной бригады');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 * Проверка существование рабочего места на определенную дату
	 */
	function checkMedStaffFactIsOpen()
	{
		$data = $this->ProcessInputData('checkMedStaffFactIsOpen', false);
		$MSFisOpen = $this->dbmodel->medStaffFactIsOpen($data['MedStaffFact_id'], $data['setDT']);

		$response = array('success' => true, 'isOpen' => $MSFisOpen );

		$this->ReturnData($response);

		return;
	}
}
