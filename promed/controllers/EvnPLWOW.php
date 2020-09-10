<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLWOW - контроллер для управления талонами углубленных обследований ВОВ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access				public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author				Марков Андрей
* @version			14.03.2010
*/

class EvnPLWOW extends swController
{
	/**
	 * EvnPLWOW constructor.
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPLWOW_model', 'dbmodel');
		
		$this->inputRules = array(
			'loadEvnPLWOWEditForm' => array(
				array(
						'field' => 'EvnPLWOW_id',
						'label' => 'Идентификатор талона по угл. обсл. ВОВ',
						'rules' => 'required',
						'type' => 'id'
					)
			),
			'loadEvnVizitPLWOW' => array(
				array(
						'field' => 'EvnPLWOW_id',
						'label' => 'Идентификатор талона по угл. обсл. ВОВ',
						'rules' => '',
						'type' => 'id'
					)
			),
			'loadEvnUslugaWOW' => array(
				array(
						'field' => 'EvnPLWOW_id',
						'label' => 'Идентификатор талона по угл. обсл. ВОВ',
						'rules' => 'required',
						'type' => 'id'
					),
				array(
						'field' => 'EvnUslugaWOW_id',
						'label' => 'Идентификатор исследования',
						'rules' => '',
						'type' => 'id'
					)
			),
			'checkDoublePerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveEvnPLWOW' => array(
				array(
					'field' => 'EvnPLWOW_id',
					'label' => 'Идентификатор талона по угл. обсл. ВОВ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор Server',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				),
				array(
						'field' => 'PersonEvn_id',
						'label' => 'Идентификатор человека в событии',
						'rules' => 'required',
						'type' => 'id'
				),
				array(
					'field' => 'ResultClass_id',
					'label' => 'Результат лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLWOW_UKL',
					'label' => 'УКЛ',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnPLWOW_IsFinish',
					'label' => 'Случай закончен',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'save',
					'label' => 'Вариант сохранения',
					'rules' => '',
					'default' => 0,
					'type' => 'int'
				)
			),
			'loadEvnPLWOWStreamList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'begTime',
					'label' => 'Время',
					'rules' => 'required',
					'type' => 'string'
				)				
			),
			'saveEvnUslugaWOW' => array(
				array(
					'field' => 'EvnUslugaWOW_id',
					'label' => 'Идентификатор исследования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLWOW_id',
					'label' => 'Идентификатор талона по угл. обсл. ВОВ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaWOW_setDate',
					'label' => 'Дата исследования',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaWOW_didDate',
					'label' => 'Дата результата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'DispWowUslugaType_id',
					'label' => 'Вид исследования',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор Server',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор PersonEvn_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
	}


	/**
	 * Выбор функции для записи
	 */
	function save()
	{
		if (isset($_REQUEST['method']))
		{
			switch ($_REQUEST['method'])
			{
				case 'saveEvnPLWOW': 
					$this->SaveObject($_REQUEST['method']);
				break;
				case 'saveEvnUslugaWOW': 
					$this->SaveObject($_REQUEST['method']);
				break;
				
				default:
					die;
			}
		}
	}

	/**
	 * Получение данных для формы редактирования талона по угл. обсл. ВОВ
	 * Входящие данные: $_POST['EvnPLWOW_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по угл. обсл. ВОВ
	 */
	function loadEvnPLWOWEditForm()
	{
		$data = $this->ProcessInputData('loadEvnPLWOWEditForm', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadEvnPLWOWEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Получение списка талонов по ДД для потокового ввода
	 * Входящие данные: $_POST['begDate'],
	 *                  $_POST['begTime']
	 * На выходе: JSON-строка
	 * Используется: форма потокового ввода талонов по ДД
	 */
	function loadEvnPLWOWStreamList()
	{
		$data = $this->ProcessInputData('loadEvnPLWOWStreamList', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadEvnPLWOWStreamList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка посещений в талоне углубленных обследований
	 * Входящие данные: $_POST['EvnPLWOW_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по угл. обсл. ВОВ
	 */
	function loadEvnVizitPLWOW()
	{
		$data = $this->ProcessInputData('loadEvnVizitPLWOW', true);
		if ( $data === false ) { return false; }
		
		if ($data['Lpu_id'] == 0)
		{
			echo json_encode(array('success' => false));
			return true;
		}
		
		$response = $this->dbmodel->loadEvnVizitPLWOW($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка обследований в талоне по угл. обсл.
	 * Входящие данные: $_POST['EvnPLWOW_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по угл. обсл. ВОВ
	 */
	function loadEvnUslugaWOW()
	{
		$data = $this->ProcessInputData('loadEvnUslugaWOW', true);
		if ( $data === false ) { return false; }
		
		if ($data['Lpu_id'] == 0)
		{
			echo json_encode(array('success' => false));
			return true;
		}
		
		$response = $this->dbmodel->loadEvnUslugaWOW($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	
	/**
	 * Проверка на уникальность исследования
	 */
	function checkUniDispWowUslugaType($model, $data)
	{
		$result = $model->checkUniDispWowUslugaType($data);
		if (is_array($result) && (count($result)>0))
		{
			if ($result[0]['record_count']>0)
				return "Выбранный вид исследования уже внесен в данном талоне.";
			else 
				return "";
		}
		else 
			return "При выполнении проверки на уникальность вида исследования<br/>сервер базы данных вернул ошибку!";
	}
	
	
	/**
	 * Проверка на завершенность при закрытии талона
	 */
	function checkIsFinish($model, $data)
	{
		if ($data['EvnPLWOW_IsFinish']==2)
		{
			// Первая проверка - на заполненность всех необходимых осмотров
			$result = $model->checkIsVizit($data);
			if (is_array($result))
			{
				if (count($result)>0)
				{
					// В принципе тут можно выбирать все не внесенные осмотры
					return "Не введены один или более обязательных осмотров. <br/>Случай не может быть закончен.";
				}
			}
			else 
			{
				return "При выполнении проверки на обязательность осмотров<br/>сервер базы данных вернул ошибку!<br/> Обратитесь к разработчикам с описанием проблемы.";
			}
			// Вторая проверка - на заполненность всех необходимых исследований
			
			$result = $model->checkIsUsluga($data);
			if (is_array($result))
			{
				if (count($result)>0)
				{
					// В принципе тут можно выбирать все не внесенные осмотры

					return "Не введены одно или более обязательных исследований. <br/>Случай не может быть закончен.";
				}
			}
			else 
			{
				return "При выполнении проверки на обязательность исследований<br/>сервер базы данных вернул ошибку!<br/> Обратитесь к разработчикам с описанием проблемы.";
			}

			//В соответствие с задачей 10729 необходимо проверить, заведен ли осмотр необходимых врачей при наличии определенных обследований
			$result = $model->checkUslugaVisit($data,1);
			if ($result == false)
				return "При вводе следущих обследований: <br>-Определение остроты зрения (02270401),<br>-Офтальмоскопия глазного дна (02270302) <br> необходим осмотр офтальмолога.<br/>Случай не может быть закончен.";

		}
		// Проверка на существование карты у этого пациента
		return $this->checkUniWowCard($model,$data);
	}
	
	
	/**
	 * Проверка на существование карты у пациента
	 */
	function checkUniWowCard($model,$data)
	{
		$result = $model->checkUniWowCard($data);
		if (is_array($result) && (count($result)>0))
		{
			if ($result[0]['Lpu']>0)
				return "На данного человека уже добавлен талон по углубленному осмотру ВОВ";
			else 
				return "";
		}
		else 
			return "При выполнении проверки на уникальность талона ВОВ<br/>сервер базы данных вернул ошибку!";
	}
	
	/**
	 * Проверка на существование карты у пациента
	 */
	function checkDoublePerson()
	{
		$data = $this->ProcessInputData('checkDoublePerson', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->checkDoublePerson($data);
		$this->ProcessModelSave($response, true, 'Системная ошибка при выполнении скрипта')->ReturnData();
	}
	
	
	/**
	 * Выбор проверки
	 */
	function getObjectCheck($model, $data, $method)
	{
		// Логические проверки
		Switch ($method)
		{
			case 'saveEvnUslugaWOW': 
				return $this->checkUniDispWowUslugaType($model, $data);
				break;
			case 'saveEvnPLWOW':
				return $this->checkIsFinish($model, $data);
				break;
			default:
				break;
		}
	}
	
	/**
	 * Выбор метода сохранения
	 */
	function ChoiseMethod($model, $data, $method)
	{
		Switch ($method)
		{
			case 'saveEvnPLWOW':
				return $model->saveEvnPLWOW($data);
				break;
			case 'saveEvnUslugaWOW':
				return $model->saveEvnUslugaWOW($data);
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Сохранение талона или услуги
	 */
	function SaveObject($method)
	{
		$this->load->helper('Text');
		
		$data = $this->ProcessInputData($method, true);
		if ( $data === false ) { return false; }
		
		$err = $this->getObjectCheck($this->dbmodel, $data, $method);
		if (strlen($err) > 0)
		{
			echo json_return_errors($err);
			exit;
		}
		
		$response = $this->ChoiseMethod($this->dbmodel, $data, $method);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
}
?>