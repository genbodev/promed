<?php	defined('BASEPATH') or die ('No direct script access allowed');
class Demand extends swController {
	public $inputRules = array(
		'loadDemandListGrid' => array(
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Лимит записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'DemandState_id',
				'label' => 'Статус заявки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Start_Date',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'End_Date',
				'label' => 'Конец периода',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveAttachmentDemand' => array(
			array(
				'field' => 'AttachmentDemand_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'action',
				'label' => '',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'id'
			),
			/*array(
				'field' => 'Lpu_Id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),*/
			array(
				'field' => 'Polis_Org',
				'label' => 'Название страховой компании',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер Полиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DemandState_id',
				'label' => 'Идентификатор статуса заявки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'State_Comment',
				'label' => 'Комментарий к заявке',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveChangeSmoDemand' => array(
			array(
				'field' => 'ChangeSmoDemand_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'action',
				'label' => '',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Smo_Id',
				'label' => 'Идентификатор СМО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'SmoUnit_Id',
				'label' => 'Идентификатор филиала СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Pasport_Inf',
				'label' => 'Кем выдан паспорт',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Pasport_TimeInf',
				'label' => 'Когда выдан паспорт',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Pasport_Ser',
				'label' => 'Серия паспорта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Pasport_Num',
				'label' => 'Номер паспорта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Org',
				'label' => 'Название страховой компании',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер Полиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DemandState_id',
				'label' => 'Идентификатор статуса заявки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'State_Comment',
				'label' => 'Комментарий к заявке',
				'rules' => '',
				'type' => 'string'
			)
		),
		'deleteAttachmentDemand' => array(
			array(
				'field' => 'AttachmentDemand_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deleteChangeSmoDemand' => array(
			array(
				'field' => 'ChangeSmoDemand_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'setDemandState' => array(
			array(
				'field' => 'AttachmentDemand_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DemandState_id',
				'label' => 'Идентификатор статуса заявки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadAttachmentDemandEditForm' => array(
			array(
				'field' => 'AttachmentDemand_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadChangeSmoDemandEditForm' => array(
			array(
				'field' => 'ChangeSmoDemand_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getOrgSmoFilialList' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => 'required',
				'type' => 'id'
			),
		)
	);

	/**
	 * Demand constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return bool
	 */
	function loadAttachmentDemandListGrid() {
		return $this->loadDemandListGrid("attachment");
	}

	/**
	 * @return bool
	 */
	function loadChangeSmoDemandListGrid() {
		return $this->loadDemandListGrid("changesmo");
	}	
	
	/**
	*  Получение списка заявок
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*/
	function loadDemandListGrid($demand_type) {
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');

		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());		
		$err  = getInputParams($data, $this->inputRules['loadDemandListGrid']);		
		
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadDemandListGrid($data, $demand_type);

		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response['data']) ) {
				$val['data'] = array();
				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}

				$val['totalCount'] = $response['totalCount'];
			}
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	 * @return bool
	 */
	function loadAttachmentDemandEditForm() {
		return $this->loadDemandEditForm("attachment");
	}

	/**
	 * @return bool
	 */
	function loadChangeSmoDemandEditForm() {
		return $this->loadDemandEditForm("changesmo");
	}
	
	/**
	*  Получение данных для формы редактирования заявок
	*  Входящие данные: $_POST['AttachmentDemand_id']/$_POST['ChangeSmoDemand_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования заявки на прикрепление к ЛПУ
	*				 форма редактирования заявки на прикрепление к СМО
	*/
	function loadDemandEditForm($demand_type) {		
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');
	
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		if ($demand_type == "attachment")
			$err = getInputParams($data, $this->inputRules['loadAttachmentDemandEditForm']);
		if ($demand_type == "changesmo")
			$err = getInputParams($data, $this->inputRules['loadChangeSmoDemandEditForm']);
		
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadDemandEditForm($data, $demand_type);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;		
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	 * @return bool
	 */
	function saveAttachmentDemand() {
		return $this->saveDemand("attachment");
	}

	/**
	 * @return bool
	 */
	function saveChangeSmoDemand() {
		return $this->saveDemand("changesmo");
	}

	/**
	 * @param $demand_type
	 * @return bool
	 */
	function saveDemand($demand_type) {
		$this->load->helper('Options');
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');
	
		$data = array();
		$val  = array();

		
		if ($demand_type == "attachment")
			$err = getInputParams($data, $this->inputRules['saveAttachmentDemand']);
		if ($demand_type == "changesmo")
			$err = getInputParams($data, $this->inputRules['saveChangeSmoDemand']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
		
		//print('POST:<br/>'); print_r($_POST); print('<br/><br/>DATA:<br/>'); print_r($data);
		
		$response = $this->dbmodel->saveDemand($data, $demand_type);
	
		if ($response == 1) {
			$val['success'] = true;			
		} else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении заявки на прикрепление');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return true;
	}

	/**
	 * @return bool
	 */
	function deleteAttachmentDemand() {
		return $this->deleteDemand("attachment");
	}

	/**
	 * @return bool
	 */
	function deleteChangeSmoDemand() {
		return $this->deleteDemand("changesmo");
	}	
	
	/**
	*  Удаление заявки на прикрепление к ЛПУ
	*  Входящие данные: $_POST['AttachmentDemand_id']
	*  На выходе: JSON-строка
	*  Используется: Окно просмотра списка заявок на смену прикрепления к ЛПУ
	*/
	function deleteDemand($demand_type) {
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');	
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		if ($demand_type == "attachment")
			$err = getInputParams($data, $this->inputRules['deleteAttachmentDemand']);
		if ($demand_type == "changesmo")
			$err = getInputParams($data, $this->inputRules['deleteChangeSmoDemand']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deleteDemand($data, $demand_type);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( (isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0) ) {
				$val['success'] = true;
			} else {
				$val = $response[0];
				$val['success'] = false;
			}
		} else {
			$val['Error_Msg'] = 'При удалении заявки возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return true;
	}

	/**
	 * @return bool
	 */
	function setDemandState() {
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');	
		$data = array();
		$val  = array();

		$err = getInputParams($data, $this->inputRules['setDemandState']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->setDemandState($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( (isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0) ) {
				$val['success'] = true;
			} else {
				$val = $response[0];
				$val['success'] = false;
			}
		} else {
			$val['Error_Msg'] = 'При смене статуса заявки возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return true;
	}

	/**
	 * @return bool
	 */
	function getDemandStateList() {
		
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');
		$val  = array();
		
		$state_data = $this->dbmodel->getDemandStateList();
		
		if ( isset($state_data) && is_array($state_data) && count($state_data) > 0 ) {
			foreach ($state_data as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);

		return true;
	}

	/**
	 * @return bool
	 */
	function getOrgSmoList() {
		
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');
		$val  = array();
		
		$state_data = $this->dbmodel->getOrgSmoList();
		
		if ( isset($state_data) && is_array($state_data) && count($state_data) > 0 ) {
			foreach ($state_data as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);

		return true;
	}

	/**
	 * @return bool
	 */
	function getOrgSmoFilialList() {
		
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');
		$data = array();
		$val  = array();

		$err = getInputParams($data, $this->inputRules['getOrgSmoFilialList']);
		
		$state_data = $this->dbmodel->getOrgSmoFilialList($data);
		
		if ( isset($state_data) && is_array($state_data) && count($state_data) > 0 ) {
			foreach ($state_data as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);

		return true;
	}

	/**
	 * @return bool
	 */
	function getCountNewDemand() { //для проверки на наличие новых заявок
		$this->load->database();
		$this->load->model('Demand_model', 'dbmodel');	
		$val  = array();
		$data  = array();
		
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		$response = $this->dbmodel->getCountNewDemand($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( (isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0) ) {
				$val['success'] = true;
			} else {
				$val = $response[0];
				$val['success'] = false;
			}
		} else {
			$val['Error_Msg'] = 'При проверке на наличие новых заявок возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return true;
	}
}