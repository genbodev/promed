<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * ufa_Reab_Register_User - контроллер для регистра VIP пациентов
 *  серверная часть
 *
 *
 * @package			
 * @author			 
 * @version			25.02.2019
 */
class AdminVIPPerson_User extends swController {

	var $model = "AdminVIPPerson_User_model";

	/**
	 * Конструктор
	 */
	function __construct() {
		$this->result = array();
		$this->start = true;

		parent::__construct();

		//$this->load->database('testUfa');
		$this->load->database();
		$this->load->model($this->model, 'dbmodel');

		$this->inputRules = array(
			'doRecords' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Operation',
					'label' => 'Тип операции',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'VIPPerson_id',
					'label' => 'Идентификатор Регистра',
					'rules' => '',
					'type' => 'id'
				)
			)
			
		);
	}
	
	/**
	 * Работа с VIP пациентами
	 */
	function doRecords() {
		$data = $this->ProcessInputData('doRecords', true);
		if ($data === false) {
			return false;
		}
		$Response = array(
			'success' => 'true',
			'Error_Msg' => '');

		$OutParams['Person_id'] = $data['Person_id'];
		$OutParams['Lpu_id'] = $data['Lpu_id'];
		$OutParams['pmUser_id'] = $data['pmUser_id'];
		$OutParams['VIPPerson_id'] = $data['VIPPerson_id'];
		//echo '<pre>' . print_r(count($OutParams['VIPPerson_id']), 1) . '</pre>';
		
		if ($data['Operation'] == 'ins') 
		{
			//Перевод в сосотояние VIP
			$recordVIP = $this->dbmodel->checkrecordVIP($OutParams);
			if (count($recordVIP) > 0) 
			{
				//Проверка на инверсию !!!!!!!!!!!!!
				if ($recordVIP[0]['VIPPerson_id'] == null) {
					//echo '55555';
					return  $this->ReturnError('Пациент в данной ЛПУ уже имеет статус VIP!');
					//return $this->ReturnData(array(
					//			'success' => false,
					//			'Error_Msg' => toUTF('Пациент в данной ЛПУ уже имеет статус VIP!')
					//));
				} else 
				{
					//конвертация записи -- Восстановление
					$OutParams['Operation'] = 'upd';
					$OutParams['VIPPerson_id'] = $recordVIP[0]['VIPPerson_id'];
					$d1 = new DateTime();
					$OutParams['VIPPerson_setDate'] = $d1->format('Y-m-d H:i:s');
					//echo '<pre>' . print_r($OutParams['VIPPerson_disDate'], 1) . '</pre>';
					$Result = $this->dbmodel->saveVIPPerson($OutParams);
					if (is_array($Result)) {
						if (isset($Result[0]['Error_Code'])) {
							$Response['success'] = 'false';
							$Response['Error_Msg'] = $Result[0]['Error_Message'];
							return $this->ReturnData($Response);
						} else {
							//echo 'нет ошибки ';
							return $this->ReturnData($Response);
						}
					}
				}
				//echo '<pre>' . print_r($recordVIP, 1) . '</pre>';
			} else {
				//Добавление записи
				$OutParams['Operation'] = 'ins';
				$response = $this->dbmodel->saveVIPPerson($OutParams);
				if (is_array($response)) {
					// echo '<pre>' . print_r($response[0]['Error_Code'], 1) . '</pre>';
					if (isset($response[0]['Error_Code'])) {
						//echo 'это ошибка ';
						return  $this->ReturnError('Ошибка записи в регистр VIP пациентов!');
					} else {
						//echo 'нет ошибки ';
						return $this->ReturnData($Response);
					}
				}
			};
		} 
		else {
			//Снятие сосотояния VIP
			$OutParams['Operation'] = 'del';
			$d1 = new DateTime();
			$OutParams['VIPPerson_disDate'] = $d1->format('Y-m-d H:i:s');
			$Result = $this->dbmodel->saveVIPPerson($OutParams);
			if (is_array($Result)) {
				if (isset($Result[0]['Error_Code'])) {
					$Response['success'] = 'false';
					$Response['Error_Msg'] = $Result[0]['Error_Message'];
					return $this->ReturnData($Response);
				} else {
					//echo 'нет ошибки ';
					return $this->ReturnData($Response);
				}
			}
		}
	}

}
