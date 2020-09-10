<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 */

class EvnPSLocat extends swController 
{
	
	var $inputRules = array(
		
		'loadMedicalHistory' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonEvnPSLocat' => array(
			array(
				'field' => 'PersonEvnPSLocat_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required|trim',
				'type' => 'id'
			)
		),
		'savePersonEvnPSLocat' => array(
				array(
					'field' => 'PersonEvnPSLocat_id',
					'label' => 'Идентификатор движения АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'AmbulatCardLocatType_id',
					'label' => 'Идентификатор типа',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvnPSLocat_Desc',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonEvnPSLocat_OtherLocat',
					'label' => 'Местонахождение',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonEvnPSLocat_begD',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonEvnPSLocat_begT',
					'label' => 'Время',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnPS_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				
			
			),
		'getEvnPSList' => array(
			array(
				'field' => 'Person_Firname',
				'label' => 'Person_Firname',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Person_Secname',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Person_Surname',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Polis_Ser',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Polis_Num',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedFIO',
				'label' => 'MedFIO',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AmbulatCardLocatType_id',
				'label' => 'AmbulatCardLocatType_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'MedStaffFact_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PostMed_id',
				'label' => 'Идентификатор должности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Person_BirthDay',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PEPSLW_date_range',
				'label' => 'Период',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => 'trim',
				'type' => 'int'
			),
		),
		'getEvnPSLocatList' => array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 5,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPS_id',
					'label' => 'Тип арма, вызвавшего метод',
					'rules' => '',
					'type' => 'id'
				),
			)
	);
	/**
	 * @comment
	 */
	function __construct ()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnPSLocat_model', 'EvnPSLocat_model');
	}
	/**
	 * @comment
	 */
	function getEvnPSLocatList(){
		$data = $this->ProcessInputData('getEvnPSLocatList', true);
		if ($data === false) {
			return true;
		}
		$response = $this->EvnPSLocat_model->getEvnPSLocatList($data);
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
		else
		{
			echo json_return_errors('Проблема выполнения запроса к БД.');
			return false;
		}
		$this->ReturnData($val);

		return true;
	}
	/**
	 * @comment
	 */
	function getEvnPSList(){
		$data = $this->ProcessInputData('getEvnPSList', true);
		if ($data === false) {
			return true;
		}
		$response = $this->EvnPSLocat_model->getEvnPSList($data);
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
		else
		{
			echo json_return_errors('Проблема выполнения запроса к БД.');
			return false;
		}
		$this->ReturnData($val);

		return true;
	}
	/**
	 * @comment
	 */
	function savePersonEvnPSLocat() {
		$data = $this->ProcessInputData('savePersonEvnPSLocat', true);
		if ($data === false)
			return false;

		$response = $this->EvnPSLocat_model->savePersonEvnPSLocat($data);
		$this->load->model('Messages_model', 'msgmodel');
		if($data['AmbulatCardLocatType_id'] != 2 && !isset($data['PersonEvnPSLocat_id']) && isset($response[0]['PersonEvnPSLocat_id'])){ //Если добавляем новое, и местонахождение - НЕ "сотрудник МО"
			//Проверим местонахождение предыдущего движения карты
			$data['PersonEvnPSLocat_id'] = $response[0]['PersonEvnPSLocat_id'];
			$result_prev = $this->EvnPSLocat_model->checkPrevLocat($data);
			$messageData = array(
				'autotype' => 1
				,'type' => 1
				,'pmUser_id' => $data['pmUser_id']
				,'PersonEvnPSLocat_id' => $response[0]['PersonEvnPSLocat_id']
			);
			//Получим данные для текста сообщения
			$result_data_for_message = $this->EvnPSLocat_model->getLocatInfo($response[0]);
			$Person_FIO = $result_data_for_message[0]['Person_FIO'];
			$Change_Date = $result_data_for_message[0]['Change_Date'];
			$Locat_Name = $result_data_for_message[0]['Locat_Name'];
			$messageData['text'] = "У пациента {$Person_FIO} произошла смена местонахождения оригинала истории болезни на '{$Locat_Name}'. Дата - {$Change_Date}";//"Пациент <a href=\"#\" onClick=\"getWnd('swPersonEmkWindow').show({Person_id: {$register[0]['Person_id']}, Server_id: {$register[0]['Server_id']}, PersonEvn_id: {$register[0]['PersonEvn_id']}, mode: 'workplace', ARMType: 'common'});\">{$Person_FIO}</a> {$Person_BirthDay} г.р. включен в регистр по орфанным заболеваниям, но у него указана дата смерти. Возможно, его нужно исключить из регистра.";
			$messageData['title'] = "Смена местонахождения оригинала истории болезни ({$Person_FIO})";
			if(is_array($result_prev) && count($result_prev) > 0)
			{
				$users = $this->EvnPSLocat_model->getUsersForAmbulatCard($result_prev[0]);
				if ($users!==false) {
					foreach ($users as $v) {
						$messageData['User_rid'] = $v['PMUser_id'];
						$this->msgmodel->autoMessage($messageData);
					}
				}
			}
		}
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Движения АК')->ReturnData();
	}
	/**
	 * @comment
	 */
	function loadPersonEvnPSLocat() {
		$data = $this->ProcessInputData('loadPersonEvnPSLocat', false);
		if ($data === false) {
			return false;
		}

		$response = $this->EvnPSLocat_model->loadPersonEvnPSLocat($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * @comment
	 */
	function loadMedicalHistory() {
		
		$data = $this->ProcessInputData('loadMedicalHistory', true);
		if ($data === false) { return false; }
		
		$response = $this->EvnPSLocat_model->loadMedicalHistory($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
}