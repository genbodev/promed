<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property EvnLeave_model $dbmodel
 */
class EvnLeave extends swController
{
	public $inputRules = array(
		'getEvnLeaveBaseId' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteEvnLeave' => array(
			array(
				'field' => 'EvnLeave_id',
				'label' => 'Идентификатор случая выписки пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnLeaveEditForm' => array(
			array(
				'field' => 'EvnLeave_id',
				'label' => 'Идентификатор случая выписки пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
	);

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnLeave_model', 'dbmodel');
	}

	/**
	 *  Получение идентификатора случая исхода госпитализации
	 */
	function getEvnLeaveBaseId() {
		$data = $this->ProcessInputData('getEvnLeaveBaseId', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getEvnLeaveBaseId($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}


	/**
	*  Удаление случая выписки пациента из стационара
	*  Входящие данные: $_POST['EvnLeave_id']
	*  На выходе: JSON-строка
	*  Используется: ???
	*/
	function deleteEvnLeave() {
		$data = $this->ProcessInputData('deleteEvnLeave', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doDelete($data);
		$this->ProcessModelSave($response, true, 'При удалении случая выписки пациента из стационара возникли ошибки')->ReturnData();
		return true;
	}


	/**
	*  Получение данных для формы редактирования случая выписки пациента из стационара
	*  Входящие данные: $_POST['EvnLeave_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая выписки пациента из стационара
	*/
	function loadEvnLeaveEditForm() {
		$data = $this->ProcessInputData('loadEvnLeaveEditForm', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEvnLeaveEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}


	/**
	*  Сохранение случая выписки пациента из стационара
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая выписки пациента из стационара
	*/
	function saveEvnLeave()
	{
		$this->inputRules['saveEvnLeave'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveEvnLeave', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении случая выписки пациента из стационара')->ReturnData();
		return true;
	}
}
