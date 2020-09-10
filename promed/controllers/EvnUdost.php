<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnUdost - контроллер для управления удостоверениями льготников
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		DLO
* @access		public
* @copyright	Copyright (c) 2009 Swan Ltd.
* @author		Stas Bykov aka Savage (savage@swan.perm.ru)
* @version		14.08.2009
*/
class EvnUdost extends swController {
	/**
	*  Описание правил для входящих параметров
	*  @var array
	*/
	public $inputRules = array(
		'deleteEvnUdost' => array(
			array(
				'field' => 'EvnUdost_id',
				'label' => 'Идентификатор удостоверения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printUdost' =>array(
			array(
				'field' => 'EvnUdost_id',
				'label' => 'Идентификатор удостоверени',
				'rules' => 'required',
				'type'  => 'id'
			)
		),
		'loadEvnUdostEditForm' => array(
			array(
				'field' => 'EvnUdost_id',
				'label' => 'Идентификатор удостоверения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnUdostList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор льготы',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'soc_card_id',
				'label' => 'Идентификатор социальной карты',
				'rules' => 'trim',
				'type'  => 'string'
			)
		),
		'loadUdostList' => array(
			array(
				'field' => 'Person_Surname',
				'label' => 'Идентификатор удостоверения',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Work_Period',
				'label' => 'Рабочий период',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'soc_card_id',
				'label' => 'Идентификатор социальной карты',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'saveEvnUdost' => array(
			array(
				'field' => 'EvnUdost_disDate',
				'label' => 'Дата закрытия',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnUdost_id',
				'label' => 'Идентификатор удостоверения',
				'rules' => '',
				'type' => 'id'
			),
			array
			(
				'field' => 'EvnUdost_Num',
				'label' => 'Номер удостоверения',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array
			(
				'field' => 'EvnUdost_Ser',
				'label' => 'Серия удостоверения',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnUdost_setDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim|required',
				'type' => 'date'
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
				'field' => 'PrivilegeType_id',
				'label' => 'Тип категории льготы',
				'rules' => 'required',
				'type' => 'id'
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
	 * EvnUdost constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnUdost_model', 'dbmodel');
	}

	/**
	 * @return bool
	 */
	function index() {
		return false;
	}


	/**
	*  Удаление удостоверения льготника
	*  Входящие данные: $_POST['EvnUdost_id']
	*  На выходе: JSON-строка
	*  Используется: форма поиска льгот
	*/
	function deleteEvnUdost() {

		$data = array();
		$val  = array();

		$data = $this->ProcessInputData('deleteEvnUdost', true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteEvnUdost($data);

		$this->ProcessModelList($response,true,true,'При удалении удостоверения возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Подсчет количества найденных записей
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма поиска удостоверений
	*/
	function getRecordsCount() {

		$data = array();
		$val  = array();

		$data = $this->ProcessInputData('loadUdostList',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadUdostList($data, true);

		$this->ProcessModelList($response,true,true,'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}


	/**
	*  Получение данных для формы редактирования удостоверения льготника
	*  Входящие данные: $_POST['EvnUdost_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования удостоверения льготника
	*/
	function loadEvnUdostEditForm() {

		$data = array();
		$val  = array();

		$data = $this->ProcessInputData('loadEvnUdostEditForm',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadEvnUdostEditForm($data);

		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка удостоверений
	*  Входящие данные: $_POST['Person_id'],
	*                   $_POST['PrivilegeType_id']
	*  На выходе: JSON-строка
	*  Используется: список удостоверений льготника
	*/
	function loadEvnUdostList() {

		$data = array();
		$val  = array();

        $data = $this->ProcessInputData('loadEvnUdostList',true);
        if ($data === false) {return false;}		

		$response = $this->dbmodel->loadEvnUdostList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}

	/**
	*  Получение списка удостоверений
	*  Входящие данные: $_POST['Person_Surname'],
	*                   $_POST['Work_Period']
	*  На выходе: JSON-строка
	*  Используется: форма поиска удостоверений
	*/
	function loadUdostList() {

		$data = array();
		$val  = array();

		$data = $this->ProcessInputData('loadUdostList',true);
		if ($data === false) {return false;}
		
		$response = $this->dbmodel->loadUdostList($data);

		$this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}


	/**
	*  Печать удостоверения
	*  Входящие данные: $_GET['EvnUdost_id']
	*  На выходе: форма для печати удостоверения
	*  Используется: форма редактирования удостоверения
	*/
	function printUdost() {
		$this->load->library('parser');

		$data = array();
		$data = $this->ProcessInputData('printUdost',true);
		if ($data === false) {return false;}
		if ( 0 == $data['EvnUdost_id'] ) {
			echo 'Неверный параметр: EvnUdost_id';
			return true;
		}

		// Получаем данные по рецепту
		$response = $this->dbmodel->getUdostFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по удостоверению';
			return true;
		}

		$udost_template = 'udost_template';

		$pb = $response[0]['Person_BirthDay'];
		$person_birthday = substr($pb, 0, 2) . substr($pb, 3, 2) . substr($pb, 6, 4);
		$sd = $response[0]['EvnUdost_setDT'];
		$start_dt = substr($sd, 0, 2) . substr($sd, 3, 2) . substr($sd, 6, 4);

		$data = array(
			'evn_udost_number'=> $response[0]['EvnUdost_Num'],
			'privilege_type'=> $response[0]['PrivilegeType_Code'],
			'person_fio'=> $response[0]['Person_FIO'],
			'person_birthday'=> $person_birthday,
			'person_snils'=> $response[0]['Person_Snils'],
			'lpu_code'=> $response[0]['Org_OGRN'],
			'evn_udost_startdt'=> $start_dt
		);

		echo $this->parser->parse($udost_template, $data);

		return true;
	}


	/**
	*  Сохранение удостоверения
	*  Входящие данные: $_POST['EvnUdost_disDate'],
	*                   $_POST['EvnUdost_id'],
	*                   $_POST['EvnUdost_Num'],
	*                   $_POST['EvnUdost_Ser'],
	*                   $_POST['EvnUdost_setDate'],
	*                   $_POST['PersonEvn_id'],
	*                   $_POST['PrivilegeType_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования льготы
	*/
	function saveEvnUdost() {

		$data = array();
		$val  = array();

		$this->load->library('textlog', array('file'=>'evnudost.log'));
		
		$this->textlog->add('');
		$this->textlog->add('saveEvnUdost: Запуск');

		$data = $this->ProcessInputData('saveEvnUdost', true);
		if ($data === false) {return false;}
		
		if ( isset($_POST['EvnUdost_disDate']) ) {
			$compare_result = swCompareDates($_POST['EvnUdost_disDate'], '31.12.2039');
			if ( -1 == $compare_result[0] ) {
				$return = array('success' => false, 'Error_Msg' => 'Дата закрытия удостоверения не должна быть больше 31.12.2039');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}

			$compare_result = swCompareDates('01.01.1900', $_POST['EvnUdost_disDate']);
			if ( -1 == $compare_result[0] ) {
				$return = array('success' => false, 'Error_Msg' => 'Дата закрытия удостоверения должна быть больше 01.01.1900');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}
		}

		if ( isset($_POST['EvnUdost_setDate']) ) {
			$compare_result = swCompareDates($_POST['EvnUdost_setDate'], date('d.m.Y'));
			if ( -1 == $compare_result[0] ) {
				$return = array('success' => false, 'Error_Msg' => 'Дата выдачи удостоверения не должна быть больше текущей даты');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}

			$compare_result = swCompareDates('01.01.1900', $_POST['EvnUdost_setDate']);
			if ( -1 == $compare_result[0] ) {
				$return = array('success' => false, 'Error_Msg' => 'Дата выдачи удостоверения должна быть больше 01.01.1900');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}
		}

		if ( isset($data['EvnUdost_disDate']) && isset($data['EvnUdost_setDate']) ) {
			$compare_result = swCompareDates($_POST['EvnUdost_setDate'], $_POST['EvnUdost_disDate']);
			if ( $compare_result[0] == 100 ) {
				$return = array('success' => false, 'Error_Msg' => 'Неверный формат даты выдачи или даты закрытия удостоверения');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}
			else if ( (-1 == $compare_result[0]) || (0 == $compare_result[0]) ) {
				$return = array('success' => false, 'Error_Msg' => 'Дата выдачи должна быть меньше даты закрытия удостоверения');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}
		}
		
		$this->textlog->add('saveEvnUdost: Выполнены все проверки без обращения к БД');


		$response = $this->dbmodel->CheckEvnUdost($data);
		
		$this->textlog->add('saveEvnUdost: Выполнена проверка CheckEvnUdost с обращением к БД ');
		
		if ( is_array($response) && count($response) > 0 ) {
			if ( $response[0]['val1'] > 0 ) {
				$this->textlog->add('saveEvnUdost: Удостоверение с такими серией и номером уже были выданы в вашей ЛПУ');
				$return = array('success' => false, 'Error_Msg' => 'Удостоверение с такими серией и номером уже были выданы в вашей ЛПУ');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}
			else if ( $response[0]['val2'] > 0 ) {
				$this->textlog->add('saveEvnUdost: У человека есть действующее удостоверение по выбранной категории');
				$return = array('success' => false, 'Error_Msg' => 'У человека есть действующее удостоверение по выбранной категории');
				array_walk($return, 'ConvertFromWin1251ToUTF8');
				echo json_encode($return);
				return false;
			}
		}
		
		$this->textlog->add('saveEvnUdost: Сохранение... ');
		$response = $this->dbmodel->saveEvnUdost($data);
		$this->textlog->add('saveEvnUdost: Сохранение выполнено ');
		
		if ( is_array($response) && count($response) > 0 ) {
			if ( empty($response[0]['Error_Msg']) ) {
				$response[0]['success'] = true;
			}
			else {
				$response[0]['success'] = false;
			}
			$val = $response[0];
		}
		else {
			$this->textlog->add('saveEvnUdost: Ошибка при выполнении запроса к базе данных');
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->textlog->add('saveEvnUdost: Finish him');
		
		$this->ReturnData($val);

		return true;
	}
}
