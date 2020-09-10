<?php

/* 
 * ZnoSuspectRegister_User - контроллер для регистра подозреваемых на ЗНО
 *  серверная часть
 * @author	Артамонов И.Г.		 
 * @version			06.11.2018
 */

class ZnoSuspectRegister_User extends swController {

	var $model = "ZnoSuspectRegister_User_model";

	/**
	 *  * Конструктор
	 */
	function __construct() {
		$this->result = array();
		$this->start = true;

		parent::__construct();

		//$this->load->database('testUfa');
		$this->load->database();
		$this->load->model($this->model, 'dbmodel');

		$this->inputRules = array(
			'getListZnoSuspectUser' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getListZnoRoutPerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ZNOSuspectRout_id',
					'label' => 'Идентификатор случая',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getListZnoResearchPerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ZNOSuspect_setDate',
					'label' => 'Начало случая',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ZNOSuspect_disDate',
					'label' => 'Окончание случая',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getListPersonZnoWithoutDirect' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ZNOSuspect_setDate',
					'label' => 'Начало случая',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ZNOSuspect_disDate',
					'label' => 'Окончание случая',
					'rules' => '',
					'type' => 'string'
				)
			),
			'made_p_ZNOSuspectAdmin' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SetDate1',
					'label' => 'Начало периода',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'SetDate2',
					'label' => 'Окончание периода',
					'rules' => '',
					'type' => 'string'
				)
			)
		);
	}

	/**
	 * Получение списка случаев наблюдения для конкретного пациента
	 */
	function getListZnoSuspectUser() {
		$data = $this->ProcessInputData('getListZnoSuspectUser', false);

		if ($data === false) {
			return false;
		}

		$dataIn['Person_id'] = $data['Person_id'];

		$list = $this->dbmodel->getListZnoSuspectUser($dataIn);

		return $this->ReturnData($list);
	}

	/**
	 * Получение маршрута пациента по ЗНО
	 */
	function getListZnoRoutPerson() {
		$data = $this->ProcessInputData('getListZnoRoutPerson', false);

		if ($data === false) {
			return false;
		}

		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['ZNOSuspectRout_id'] = $data['ZNOSuspectRout_id'];

		$list = $this->dbmodel->getListZnoRoutPerson($dataIn);

		return $this->ReturnData($list);
	}

	/**
	 * Получение Исследований пациента по ЗНО
	 */
	function getListZnoResearchPerson() {
		$data = $this->ProcessInputData('getListZnoResearchPerson', false);

		if ($data === false) {
			return false;
		}

		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['ZNOSuspect_setDate'] = $data['ZNOSuspect_setDate'];
		$dataIn['ZNOSuspect_disDate'] = $data['ZNOSuspect_disDate'];


		$list = $this->dbmodel->getListZnoResearchPerson($dataIn);

		return $this->ReturnData($list);
	}

	/**
	 * Получение случаев лечения пациента по ЗНО без направления на Консультацию
	 */
	function getListPersonZnoWithoutDirect() {
		$data = $this->ProcessInputData('getListPersonZnoWithoutDirect', false);

		if ($data === false) {
			return false;
		}

		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['ZNOSuspect_setDate'] = $data['ZNOSuspect_setDate'];
		$dataIn['ZNOSuspect_disDate'] = $data['ZNOSuspect_disDate'];


		$list = $this->dbmodel->getListPersonZnoWithoutDirect($dataIn);

		return $this->ReturnData($list);
	}

	/**
	 * Получение списка запусков процедур по обновлению регистра
	 */
	function getListZNOSuspectAdmin() {

		$list = $this->dbmodel->getListZNOSuspectAdmin();

		return $this->ReturnData($list);
	}

	/**
	 * Запуск процедур по заполнению-обновленипю регистра(после отладки  made_p_ZNOSuspectAdmin1)
	 */
	
	/*
	function made_p_ZNOSuspectAdmin() {
		$data = $this->ProcessInputData('made_p_ZNOSuspectAdmin', true);

		if ($data === false) {
			return false;
		}
		// Отработка по Person_id -- Написать процедуру!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		if ($data['Person_id'] != null) {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => "Данная процедура в разработке!"
			));
		}

		//Работа по датам
		if ($data['SetDate1'] == null && $data['SetDate2'] == null) {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => "Не указана одна из дат!"
			));
		}


		if ($data['SetDate1'] != null) {
			//Поиск записей в ZNOSuspectAdmin
			$response = $this->dbmodel->getListZNOSuspectAdmin1();

			if (is_array($response)) {
				if ($response[0]['nKol'] > 0) {
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => "Первоначальная загрузка данных в регистр уже произведена!"
					));
				} else {
					//Подготовка данных
					//SetDate1,SetDate2 = null,pmUser_id
					$data['SetDate2'] = null;
					//Запуск процедуры
					$list = $this->dbmodel->made_p_ZNOSuspectAdmin1($data);
					if (is_array($list)) {
						if (isset($list[0]['Error_Code'])) {
							return $this->ReturnData(array(
										'success' => false,
										'Error_Msg' => toUTF($response[0]['Error_Message'])
							));
						} else {
							return $this->ReturnData(array(
										'success' => true,
										'Error_Msg' => toUTF('Все в норме!')
							));
						}
					} else {
						// echo 'Что-то такое ';
						return $response;
					}
				}
			} else {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => "Ошибка в БД!"
				));
			}
		}


		if ($data['SetDate2'] != null) {
			//Ищем мах Дату начала запуска процедур и если есть, то присваиваем к началу диапазона
			$response = $this->dbmodel->getListZNOSuspectAdmin2();

			//						echo "$response=";
			//			echo '<pre>' . print_r($response, 1) . '</pre>';

			if (is_array($response)) {
				if ($response[0]['SetDate'] == null) {
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => "Первоначальная загрузка данных в регистр не проводилась!"
					));
				} 
				else 
				{
					//Запуск процедуры по диапазону
					//Подготовка данных
					//SetDate1,SetDate2 = null,pmUser_id
					$data['SetDate1'] = $response[0]['SetDate'];
					//Запуск процедуры
					$list = $this->dbmodel->made_p_ZNOSuspectAdmin1($data);
					if (is_array($list)) {
						if (isset($list[0]['Error_Code'])) {
							return $this->ReturnData(array(
										'success' => false,
										'Error_Msg' => toUTF($response[0]['Error_Message'])
							));
						} else {
							return $this->ReturnData(array(
										'success' => true,
										'Error_Msg' => toUTF('Все в норме!')
							));
						}
					}
				}
			} else {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => "Ошибка в БД!"
				));
			}
		}

		return $this->ReturnData($list);
	}
	
	*/
	
	/**
	 * Запуск процедур по заполнению-обновленипю регистра (на время отладки made_p_ZNOSuspectAdmin2   )
	 */
	function made_p_ZNOSuspectAdmin() {
		$data = $this->ProcessInputData('made_p_ZNOSuspectAdmin', true);

		if ($data === false) {
			return false;
		}
		// Отработка по Person_id -- Написать процедуру!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		if ($data['Person_id'] != null) {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => "Данная процедура в разработке!"
			));
		}

		//Работа по датам
		if ($data['SetDate1'] == null && $data['SetDate2'] == null) {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => "Не указана одна из дат!"
			));
		}


		if ($data['SetDate1'] != null) {
			//Подготовка данных
			//SetDate1,SetDate2 = null,pmUser_id
			$data['SetDate2'] = null;
			//Запуск процедуры
			$list = $this->dbmodel->made_p_ZNOSuspectAdmin2($data);
			if (is_array($list)) {
				if (isset($list[0]['Error_Code'])) {
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => toUTF($response[0]['Error_Message'])
					));
				} else {
					return $this->ReturnData(array(
								'success' => true,
								'Error_Msg' => toUTF('Все в норме!')
					));
				}
			} else {
				// echo 'Что-то такое ';
				return $response;
			}
		}


		if ($data['SetDate2'] != null) {
			//Ищем мах Дату начала запуска процедур и если есть, то присваиваем к началу диапазона
			$response = $this->dbmodel->getListZNOSuspectAdmin2();

			//						echo "$response=";
			//			echo '<pre>' . print_r($response, 1) . '</pre>';

			if (is_array($response)) {
				if ($response[0]['SetDate'] == null) {
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => "Первоначальная загрузка данных в регистр не проводилась!"
					));
				} 
				else 
				{
					//Запуск процедуры по диапазону
					//Подготовка данных
					//SetDate1,SetDate2 = null,pmUser_id
					$data['SetDate1'] = $response[0]['SetDate'];
					//Запуск процедуры
					$list = $this->dbmodel->made_p_ZNOSuspectAdmin2($data);
					if (is_array($list)) {
						if (isset($list[0]['Error_Code'])) {
							return $this->ReturnData(array(
										'success' => false,
										'Error_Msg' => toUTF($response[0]['Error_Message'])
							));
						} else {
							return $this->ReturnData(array(
										'success' => true,
										'Error_Msg' => toUTF('Все в норме!')
							));
						}
					}
				}
			} else {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => "Ошибка в БД!"
				));
			}
		}

		return $this->ReturnData($list);
	}

}
