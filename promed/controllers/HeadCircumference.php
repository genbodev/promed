<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property HeadCircumference_model dbmodel
 */
class HeadCircumference extends swController {
	public $inputRules = array(
		'deleteHeadCircumference' => array(
			array(
				'field' => 'HeadCircumference_id',
				'label' => 'Идентификатор измерения окружности головы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadHeadCircumferenceEditForm' => array(
			array(
				'field' => 'HeadCircumference_id',
				'label' => 'Идентификатор измерения окружности головы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadHeadCircumferencePanel' => array(
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
// 		'loadHeadCircumferenceGrid' => array(
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
		'saveHeadCircumference' => array(
			array(
				'field' => 'HeadCircumference_id',
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
				'field' => 'HeadCircumference_Head',
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
	 * HeadCircumference constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('HeadCircumference_model', 'dbmodel');
	}


	/**
	*  Удаление измерения окружности головы
	*  Входящие данные: $_POST['HeadCircumference_id']
	*  На выходе: JSON-строка
	*  Используется: -
	*/
	function deleteHeadCircumference()
	{
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deleteHeadCircumference']);

		if (strlen($err) > 0 )
		{
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deleteHeadCircumference($data);

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
			$val['Error_Msg'] = 'При удалении измерения окружности головы возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования измерения окружности головы
	*  Входящие данные: $_POST['HeadCircumference_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования измерения окружности головы
	*/
	function loadHeadCircumferenceEditForm()
	{
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadHeadCircumferenceEditForm']);

		if (strlen($err) > 0)
		{
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadHeadCircumferenceEditForm($data);

		if (is_array($response) && count($response) > 0)
		{
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


// 	/**
// 	*  Получение списка измерений окружности головы
// 	*  Входящие данные:
// 	*		$_POST['Person_id'],
// 	*		$_POST['PersonChild_id']
// 	*  На выходе: JSON-строка
// 	*  Используется: форма редактирования КВС?
// 	*/
// 	function loadHeadCircumferenceGrid()
// 	{
// 		$data = array();
// 		$val  = array();
//
// 		// Получаем сессионные переменные
// 		$data = array_merge($data, getSessionParams());
//
// 		$err = getInputParams($data, $this->inputRules['loadHeadCircumferenceGrid']);
//
// 		if ( strlen($err) > 0 ) {
// 			echo json_return_errors($err);
// 			return false;
// 		}
//
// 		$response = $this->dbmodel->loadHeadCircumferenceGrid($data);
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
	 * Получение списка измерений окружности головы для ЭМК
	 */
	function loadHeadCircumferencePanel()
	{
		$data = $this->ProcessInputData('loadHeadCircumferencePanel', true, true, true);

		if ($data === false)
			return false;

		$response = $this->dbmodel->loadHeadCircumferencePanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	*  Сохранение измерения окружности головы
	*  Если ид. измерения (HeadCircumference_id) задан или найден в БД по другим
	*  параметрам, редактируем запись. В остальных случах - создаем новую запись.
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма "Окружность головы"
	*/
	function saveHeadCircumference()
	{
		$data = $this->ProcessInputData('saveHeadCircumference', true, true, true);

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
		// HeadCircumference_id из имеющейся записи, если она есть:
		if (!isset($data['HeadCircumference_id']) &&
			($data['HeightMeasureType_id'] == 1))
		{
			$query =
				"select
					HC.HeadCircumference_id
				from
					v_HeadCircumference HC with(nolock)
					inner join PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
				where
					HC.HeightMeasureType_id = 1 and
					PC.Person_id = :Person_id
				";

			$result = $this->dbmodel->getHeadCircumferenceId(['Person_id' => $data['Person_id']]);
			if (is_object($result))
			{
				$res = $result->result('array');

				if (count($res) > 0)
					$data['HeadCircumference_id'] = $res[0]['HeadCircumference_id'];
			}
		}

		$response = $this->dbmodel->saveHeadCircumference($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}
}
