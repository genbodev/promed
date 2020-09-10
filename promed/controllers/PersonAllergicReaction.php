<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonAllergicReaction extends swController {
	public $inputRules = array(
		'deletePersonAllergicReaction' => array(
			array(
				'field' => 'PersonAllergicReaction_id',
				'label' => 'Идентификатор вида аллергической реакции',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonAllergicReaction' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonAllergicReactionEditForm' => array(
			array(
				'field' => 'PersonAllergicReaction_id',
				'label' => 'Идентификатор вида аллергической реакции',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'savePersonAllergicReaction' => array(
			array(
				'field' => 'AllergicReactionLevel_id',
				'label' => 'Характер аллергической реакции',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AllergicReactionType_id',
				'label' => 'Тип аллергической реакции',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugMnn_id',
				'label' => 'Лекарственный препарат-аллерген',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RlsActmatters_id',
				'label' => 'Лекарственный препарат-аллерген',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TRADENAMES_ID',
				'label' => 'Лекарственный препарат-аллерген',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonAllergicReaction_Kind',
				'label' => 'Вид аллергена',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonAllergicReaction_setDate',
				'label' => 'Дата возникновения аллергической реакции',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonAllergicReaction_id',
				'label' => 'Идентификатор вида аллергической реакции',
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
		"getPersonAllergicReaction" => [
			["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"]
		],
		"checkPersonAllergicReaction" => [
			["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"],
			["field" => "DrugComplexMnn_id", "label" => "Идентификатор препарата", "rules" => "required", "type" => "id"],
		],
		"checkPersonDrugReaction" => [
			["field" => "Evn_id", "label" => "Идентификатор назначения", "rules" => "required", "type" => "id"],
			["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"],
			["field" => "DrugComplexMnn_id", "label" => "Идентификатор препарата", "rules" => "required", "type" => "id"],
		],
		"checkPersonDrugReactionInEvn" => [
			["field" => "EvnCourseTreat_setDate", "label" => "Дата начала курса лекарственного назначения", "rules" => "required", "type" => "date"],
			["field" => "Evn_id", "label" => "Идентификатор назначения", "rules" => "required", "type" => "id"],
			["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"],
			["field" => "DrugComplexMnn_id", "label" => "Идентификатор препарата", "rules" => "required", "type" => "id"],
			["field" => "DrugComplexMnn_ids", "label" => "Идентификаторы добавленных препаратов", "rules" => "", "type" => "string"],
			["field" => "PacketPrescr_id", "label" => "Идентификатор пакета назначений", "rules" => "", "type" => "id"],
		],
		"getDrugInteractionsDescription" => [
			["field" => "EvnCourseTreat_setDate", "label" => "Дата начала курса лекарственного назначения", "rules" => "required", "type" => "date"],
			["field" => "Evn_id", "label" => "Идентификатор назначения", "rules" => "required", "type" => "id"],
			["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"],
			["field" => "DrugComplexMnn_ids", "label" => "Идентификатор препарата", "rules" => "required", "type" => "string"],
			["field" => "PacketPrescr_id", "label" => "Идентификатор пакета назначений", "rules" => "", "type" => "id"],
		]
	);

	/**
	 * PersonAllergicReaction constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonAllergicReaction_model', 'dbmodel');
	}


	/**
	*  Удаление вида аллергической реакции
	*  Входящие данные: $_POST['PersonAllergicReaction_id']
	*  На выходе: JSON-строка
	*  Используется: -
	*/
	function deletePersonAllergicReaction() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deletePersonAllergicReaction']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		// возможность удаления записи только для пользователя, создавшего запись или администратора
		$delete_allow = false;
		if ( isSuperadmin() )
		{
			$delete_allow = true;
		}
		else
		{
			$response = $this->dbmodel->loadPersonAllergicReactionEditForm($data);
			if ( !is_array($response) || count($response) == 0 || !isset($response[0]['pmUser_insID']))
			{
				json_return_errors('Не удалось выполнить проверку возможности удаления!');
				return false;
			}
			if ( $response[0]['pmUser_insID'] == $data['pmUser_id'])
			{
				$delete_allow = true;
			}
		}
		if ( $delete_allow == false )
		{
			json_return_errors('Вы можете удалить только свои записи!');
			return false;
		}

		$response = $this->dbmodel->deletePersonAllergicReaction($data);

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
			$val['Error_Msg'] = 'При удалении вида аллергической реакции возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования вида аллергической реакции
	*  Входящие данные: $_POST['PersonAllergicReaction_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования вида аллергической реакции
	*/
	function loadPersonAllergicReactionEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonAllergicReactionEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonAllergicReactionEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Сохранение вида аллергической реакции
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования вида аллергической реакции
	*/
	function savePersonAllergicReaction() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['savePersonAllergicReaction']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->savePersonAllergicReaction($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];

			if ( array_key_exists('Error_Msg', $val) && empty($val['Error_Msg']) ) {
				$val['success'] = true;
				$val['PersonAllergicReaction_id'] = $response[0]['PersonAllergicReaction_id'];
			}
			else {
				$val['success'] = false;
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении вида аллергической реакции');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 * Получение списка аллергических реакций пациента для ЭМК
	 */
	function loadPersonAllergicReaction() {
		$data = $this->ProcessInputData('loadPersonAllergicReaction', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonAllergicReaction($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение массива аллергических препаратов и компонентов пациента
	 * @return bool
	 * @throws Exception
	 */
	function getPersonAllergicReaction()
	{
		$data = $this->ProcessInputData('getPersonAllergicReaction', true, true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getPersonAllergicReaction($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Проверка аллергических реакций на связку Person_id и Drug_id
	 * @return bool
	 * @throws Exception
	 */
	function checkPersonAllergicReaction()
	{
		$data = $this->ProcessInputData('checkPersonAllergicReaction', true, true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->checkPersonAllergicReaction($data);
		echo @json_encode($response);
		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	function checkPersonDrugReaction()
	{
		$data = $this->ProcessInputData('checkPersonDrugReaction', true, true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->checkPersonDrugReaction($data);
		$this->ProcessModelSave($response, true, 'При проверке лекарственного взаимодействия произошла ошибка')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	function checkPersonDrugReactionInEvn()
	{
		$data = $this->ProcessInputData('checkPersonDrugReactionInEvn', true, true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->checkPersonDrugReactionInEvn($data);
		$this->ProcessModelSave($response, true, 'При проверке лекарственного взаимодействия произошла ошибка')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	function getDrugInteractionsDescription()
	{
		$data = $this->ProcessInputData('getDrugInteractionsDescription', true, true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadDrugInteractionsDescription($data);
		$this->ProcessModelSave($response, true, 'При получении описания лекарственного взаимодействия произошла ошибка')->ReturnData();
		return true;
	}
}