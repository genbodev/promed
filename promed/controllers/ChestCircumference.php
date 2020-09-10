<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property ChestCircumference_model dbmodel
 */
class ChestCircumference extends swController {
	public $inputRules = array(
		'deleteChestCircumference' => array(
			array(
				'field' => 'ChestCircumference_id',
				'label' => 'Идентификатор измерения окружности груди',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadChestCircumferenceEditForm' => array(
			array(
				'field' => 'ChestCircumference_id',
				'label' => 'Идентификатор измерения окружности груди',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadChestCircumferencePanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_id',
				'label' => 'Идентификатор специфики детства',
				'rules' => '',
				'type' => 'id'
			)
		),
// 		'loadChestCircumferenceGrid' => array(
// 			array(
// 				'default' => 'all',
// 				'field' => 'mode',
// 				'label' => 'Режим',
// 				'rules' => '',
// 				'type' => 'string'
// 			),
// 			array(
// 				'field' => 'Person_id',
// 				'label' => 'Идентификатор человека',
// 				'rules' => 'required',
// 				'type' => 'id'
// 			),
// 			array(
// 				'field' => 'PersonChild_id',
// 				'label' => 'Идентификатор специфики детства',
// 				'rules' => '',
// 				'type' => 'id'
// 			),
// 			array(
// 				'field' => 'HeightMeasureType_id',
// 				'label' => 'Идентификатор вида замера',
// 				'rules' => '',
// 				'type' => 'id'
// 			)
// 		),
		'saveChestCircumference' => array(
			array(
				'field' => 'ChestCircumference_id',
				'label' => 'Идентификатор измерения окружности головы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_id',
				'label' => 'Идентификатор специфики детства',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HeightMeasureType_id',
				'label' => 'Идентификатор вида измерения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ChestCircumference_Chest',
				'label' => 'Масса',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * ChestCircumference constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ChestCircumference_model', 'dbmodel');
	}


	/**
	*  Удаление измерения окружности груди
	*  Входящие данные: $_POST['ChestCircumference_id']
	*  На выходе: JSON-строка
	*  Используется: -
	*/
	function deleteChestCircumference()
	{
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deleteChestCircumference']);

		if (strlen($err) > 0 )
		{
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deleteChestCircumference($data);

		if (is_array($response) && count($response) > 0)
			if (array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg']))
				$val['success'] = true;

			else
			{
				$val = $response[0];
				$val['success'] = false;
			}
		else
		{
			$val['Error_Msg'] = 'При удалении измерения окружности груди возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования измерения окружности груди
	*  Входящие данные: $_POST['ChestCircumference_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования измерения окружности груди
	*/
	function loadChestCircumferenceEditForm()
	{
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadChestCircumferenceEditForm']);

		if (strlen($err) > 0)
		{
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadChestCircumferenceEditForm($data);

		if (is_array($response) && count($response) > 0)
		{
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


// 	/**
// 	*  Получение списка измерений окружности груди
// 	*  Входящие данные:
// 	*		$_POST['Person_id'],
// 	*		$_POST['PersonChild_id']
// 	*  На выходе: JSON-строка
// 	*  Используется: форма редактирования КВС?
// 	*/
// 	function loadChestCircumferenceGrid()
// 	{
// 		$data = array();
// 		$val  = array();
//
// 		// Получаем сессионные переменные
// 		$data = array_merge($data, getSessionParams());
//
// 		$err = getInputParams($data, $this->inputRules['loadChestCircumferenceGrid']);
//
// 		if ( strlen($err) > 0 ) {
// 			echo json_return_errors($err);
// 			return false;
// 		}
//
// 		$response = $this->dbmodel->loadChestCircumferenceGrid($data);
//
// 		if (is_array($response) && count($response) > 0)
// 			foreach ($response as $row)
// 			{
// 				array_walk($row, 'ConvertFromWin1251ToUTF8');
// 				$val[] = $row;
// 			}
//
// 		$this->ReturnData($val);
//
// 		return false;
// 	}

	/**
	 * Получение списка измерений окружности груди для ЭМК
	 */
	function loadChestCircumferencePanel()
	{
		$data = $this->ProcessInputData('loadChestCircumferencePanel', true, true, true);

		if ($data === false)
			return false;

		$response = $this->dbmodel->loadChestCircumferencePanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	*  Сохранение измерения окружности груди
	*  Если ид. измерения (ChestCircumference_id) задан или найден в БД по другим
	*  параметрам, редактируем запись. В остальных случах - создаем новую запись.
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма "Окружность груди"
	*/
	function saveChestCircumference()
	{
		$data = $this->ProcessInputData('saveChestCircumference', true, true, true);

		if ($data === false)
			return false;

		// Если PersonChild_id не задан, определяем его по Person_id:
		if (!isset($data['PersonChild_id']))
		{
			$this->load->model('PersonChild_model');

			// Ищем запись PersonChild по Person_id:
			$response = $this->PersonChild_model->loadPersonChildData($data);

			if (is_array($response) && count($response) > 0)
			{
				// Нашли - запоминаем PersonChild_id:
				$data['PersonChild_id'] = $response[0]['PersonChild_id'];
			}
			else
			{
				// Не нашли - пытаемся создать новую запись PersonChild:
				$response = $this->PersonChild_model->savePersonChild($data);

				if (is_array($response) && count($response) > 0)
					// Создали - запоминаем PersonChild_id:
					$data['PersonChild_id'] = $response[0]['PersonChild_id'];
			}

			// Если не удалось определить PersonChild_id, выходим с ошибкой:
			if (!isset($data['PersonChild_id']))
				return false;
		}

		// Измерений вида "При рождении" (HeightMeasureType_id == 1) не должно
		// быть больше одного, поэтому для данного вида берем
		// ChestCircumference_id из имеющейся записи, если она есть:
		if (!isset($data['ChestCircumference_id']) &&
			($data['HeightMeasureType_id'] == 1))
		{
			$query =
				"select
					HC.ChestCircumference_id
				from
					v_ChestCircumference HC with(nolock)
					inner join PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
				where
					HC.HeightMeasureType_id = 1 and
					PC.Person_id = :Person_id
				";

			$params = array('Person_id' => $data['Person_id']);

			$result = $this->db->query($query, $params);

			if (is_object($result))
			{
				$res = $result->result('array');

				if (count($res) > 0)
					$data['ChestCircumference_id'] = $res[0]['ChestCircumference_id'];
			}
		}

		$response = $this->dbmodel->saveChestCircumference($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}
}
