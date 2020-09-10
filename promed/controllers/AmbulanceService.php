<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* AmbulanceService - контроллер для сервисов скорой помощи
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      unknown
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      24.03.2011
*/

class AmbulanceService extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->library('textlog', array('file'=>'AmbulanceService.log'));
		$this->inputRules = array(
			'getLpu' => array(
				array('field' => 'lpuCode', 'label' => 'Код ЛПУ', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'updDT', 'label' => 'Дата обновления', 'rules' => 'trim', 'type' => 'datetime')
			),
			'getPersonByFIOPolis' => array(
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_BirthDay', 'label' => 'Дата рождения', 'rules' => 'trim', 'type' => 'date'),
				array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'trim', 'type' => 'string')
			),
			'getPersonByPolis' => array(
				array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => '', 'type' => 'string'),
				array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'required', 'type' => 'string') // TODO: Вроде как он должен быть только числовой, но если первые нули?
			),
			'getPersonByFIODR' => array(
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
				array('field' => 'Person_BirthDay', 'label' => 'Дата рождения', 'rules' => 'required', 'type' => 'date')
			),
			'getPersonByAddress' => array(
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Person_Age', 'label' => 'Возраст', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'KLStreet_Name', 'label' => 'Улица', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Address_House', 'label' => 'Дом', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Address_Flat', 'label' => 'Квартира', 'rules' => '', 'type' => 'string')
			),
			'getStacList' => array(
				array('field' => 'LpuSectionProfile_Code', 'label' => 'Код профиля', 'rules' => 'required', 'type' => 'int')
			), 
			'bookEmergencyBed' => array(
				array('field' => 'Person_id', 'label' => 'ID человека', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'Lpu_id', 'label' => 'ID ЛПУ', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'LpuSection_id', 'label' => 'ID отделения', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'LpuSectionProfile_Code', 'label' => 'Код профиля ОМС', 'rules' => '', 'type' => 'int'),
				array('field' => 'emergencyBedCount', 'label' => 'Количество бронируемых коек', 'rules' => 'required', 'type' => 'int', 'default' =>1),
				array('field' => 'EmergencyData_BrigadeNum', 'label' => 'Номер бригады', 'rules' => '', 'type' => 'int'),
				array('field' => 'EmergencyData_CallNum', 'label' => 'Номер карты вызова', 'rules' => '', 'type' => 'int'),
				array('field' => 'CmpCallCard_id', 'label' => 'ID карты вызова', 'rules' => '', 'type' => 'int'),
				array('field' => 'Diag_id', 'label' => 'Текущий диагноз', 'rules' => '', 'type' => 'id')
				
			),
			'getPolisByPerson'=> array(
			  array('field' => 'Person_id', 'label' => 'ID человека', 'rules' => 'required', 'type' => 'int')
			)
		);
		
		$this->load->database();
		$this->load->model('AmbulanceService_model', 'dbmodel');
	}


	/**
	*  Возвращает данные по лпу для соответствующего сервиса скорой.
	*  Входящие данные: $_POST['AmbulanceService_id']
	*		$_POST['lpuCode'] – код ЛПУ, если есть то возвращаются данные только по указанному ЛПУ
	*		$_POST['updDT'] - дата обновления данных в справочнике. Если указано – то возвращаются только измененные с определенной даты данные
	*  На выходе: JSON-строка
	*  Используется: внешние сервисы
	*/
	function getLpu() {
		$data = $this->ProcessInputData('getLpu', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getLpu($data);
		//ProcessModelSave для возврата не списка, а одного результата
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	*  Возвращает данные по человеку.
	*  Используется: внешние сервисы
	*/
	function getPersonByFIOPolis() {
		$this->textlog->add('getPersonByFIOPolis: Post: '.print_r($_POST, true));
		$data = $this->ProcessInputData('getPersonByFIOPolis', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getPersonByFIOPolis($data);
		$this->textlog->add('getPersonByFIOPolis: Ответ: '.print_r($response, true));
		//ProcessModelSave для возврата не списка, а одного результата
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	* Возвращает ID человека по номеру и серии полиса, если человек успешно идентифицирован. 
	* В случае «старого» полиса или ВС передается серия и номер полиса. Для нового полиса передается 16-ти значный номер и пустая серия.
	* Используется: внешние сервисы
	*/
	function getPersonByPolis() {
		$this->textlog->add('getPersonByPolis: Старт! ');
		$this->textlog->add('getPersonByPolis: Post: '.print_r($_POST, true));
		$data = $this->ProcessInputData('getPersonByPolis', true);
		if ($data) {
			$response = $this->dbmodel->getPersonByPolis($data);
			$this->textlog->add('getPersonByPolis: Ответ: '.print_r($response, true));
			//ProcessModelSave для возврата не списка, а одного результата
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
		$this->textlog->add('getPersonByPolis: Финиш');
	}

	/**
	* Возвращает ID человека по ФИО ДР. Фамилия, имя, дата рождения – обязательные параметры. Отчество может отсутствовать.
	* Используется: внешние сервисы
	*/
	function getPersonByFIODR() {
		$this->textlog->add('getPersonByFIODR: Старт! ');
		$data = $this->ProcessInputData('getPersonByFIODR', true);
		if ($data) {
			$response = $this->dbmodel->getPersonByFIOPolis($data);
			$this->textlog->add('getPersonByFIODR: Получили ответ на '.count($response).' строк');
			$this->ProcessModelList($response, true, true);
			if (count($this->OutData)>0) {
				$this->textlog->add('getPersonByFIODR: Данные ответа: '.serialize($this->OutData[0]));
				$this->ReturnData($this->OutData[0]);
				$this->textlog->add('getPersonByFIODR: Вернули ответ ');
			} else {
				$this->textlog->add('getPersonByFIODR: Нет данных ответа ');
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Возвращает ID человека по ФИ, возрасту и адресу
	* Используется: внешние сервисы
	*/
	function getPersonByAddress() {
		$this->textlog->add('getPersonByAddress: Post: '.print_r($_POST, true));
		$data = $this->ProcessInputData('getPersonByAddress', true);
		if ($data) {
			$response = $this->dbmodel->getPersonByAddress($data);
			$this->textlog->add('getPersonByAddress: Ответ: '.print_r($response, true));
			//ProcessModelSave для возврата не списка, а одного результата
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	
	/**
	* Возвращает серию-номер полиса и СМО по ид человека
	* Используется: внешние сервисы
	*/
	function getPolisByPerson() {
		
		$data = $this->ProcessInputData('getPolisByPerson', true);
		if ($data) {
			$response = $this->dbmodel->getPolisByPerson($data);
			//ProcessModelSave для возврата не списка, а одного результата
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	* Возвращает список профилей доступных для выбора СМП
	* Используется: внешние сервисы
	*/
	function getProfileList() {

		$data = $this->ProcessInputData(null, true);
		if ($data) {
			$response = $this->dbmodel->getProfileList($data);
			//ProcessModelSave для возврата не списка, а одного результата
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	* Возвращает список профилей доступных для выбора СМП
	* Используется: внешние сервисы
	*/
	function getStacList() {

		$data = $this->ProcessInputData('getStacList', true);
		if ($data) {
			$response = $this->dbmodel->getStacList($data);
			//ProcessModelSave для возврата не списка, а одного результата
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Бронирование койки
	* Используется: внешние сервисы
	*/
	function bookEmergencyBed() {
		$this->textlog->add('bookEmergencyBed: Старт! ');
		$data = $this->ProcessInputData('bookEmergencyBed', true);
		if ($data) {
			$data['object'] = 'TimetableStac';
			$data['TimetableObject_id'] = 2;
			$data['Evn_pid'] = null;
			$data['date'] = date('d.m.Y');
			//$this->db = null;

			// 1. Получаем бригаду и прочее, если есть 
			$data['EmergencyData_id'] = $this->dbmodel->getEmergencyData($data);
			
			$this->load->model('TimetableGraf_model', 'ttgmodel');
			$this->load->model('CmpCallCard_model', 'cccmodel');
			
			// В цикле по количеству записей:
			for($i=0; $i<count($data['emergencyBedCount']); $i++) {
				// 2. Получаем свободную бирку 
				$data['TimetableStac_id'] = $this->ttgmodel->getFreeTimetable($data);
				$this->textlog->add('TimetableStac_id: ' . $data['TimetableStac_id']);
				// 3. если свободной бирки нет - создаем новую бирку И записываем на нее сразу 
				if ($data['TimetableStac_id']==0) {
					
					if (!isset($data['EmergencyData_id'])&&isset($data['Diag_id'])&&isset($data['EmergencyData_BrigadeNum'])) {
						
						if(!isset($data['EmergencyData_CallNum'])&&isset($data['CmpCallCard_id'])) {
							$data['EmergencyData_CallNum'] = $this->cccmodel->getCmpCallCardNgod($data);
						}
						
						$newEmergencyData = $this->dbmodel->saveEmergencyData($data);
						$data['EmergencyData_id'] = (isset($newEmergencyData[0])&&isset($newEmergencyData[0]['EmergencyData_id']))?$newEmergencyData[0]['EmergencyData_id']:null;
					}
					$response = $this->ttgmodel->Create($data);
					if (!( is_array($response) && count($response) > 0 )) {
						// Ошибка 
						$this->textlog->add('Ошибка при создании экстренной койки');
						$this->ReturnData(array('success' => false, 'Error_Code' => -1, 'Error_Msg' => toUTF('Ошибка при создании экстренной койки')));
						return false;
					}
					$data['TimetableStac_id'] = $response[0]['TimetableStac_id'];
				} elseif ($data['TimetableStac_id']>0) {
					if (!isset($data['EmergencyData_id'])&&isset($data['Diag_id'])&&isset($data['EmergencyData_BrigadeNum'])) {
						
						if(!isset($data['EmergencyData_CallNum'])&&isset($data['CmpCallCard_id'])) {
							$data['EmergencyData_CallNum'] = $this->cccmodel->getCmpCallCardNgod($data);
						}
						
						$newEmergencyData = $this->dbmodel->saveEmergencyData($data);
						$data['EmergencyData_id'] = (isset($newEmergencyData[0])&&isset($newEmergencyData[0]['EmergencyData_id']))?$newEmergencyData[0]['EmergencyData_id']:null;
					}
					// 3. Записываем на бирку
					$response = $this->ttgmodel->Apply($data, false);						
					if ( !$response['success'] ) {
						// Ошибка 
						$this->textlog->add('Ошибка при записи на экстренную койку');
						$this->ReturnData(array('success' => false, 'Error_Code' => -1, 'Error_Msg' => toUTF('Ошибка при записи на экстренную койку')));
						return false;
					}
				} else {
					// Ошибка 
					$this->textlog->add('Ошибка при получении свободных экстренных коек');
					$this->ReturnData(array('success' => false, 'Error_Code' => -1, 'Error_Msg' => toUTF('Ошибка при получении свободных экстренных коек')));
					return false;
				}
			}
			if (isset($data['CmpCallCard_id'])&&($data['CmpCallCard_id']>0)) {
				$this->cccmodel->setCmpCloseCardTimetable($data);
			}
			$this->ReturnData(array('success' => true, 'Error_Code' => 0, 'Error_Msg' => ''));
			$this->textlog->add('bookEmergencyBed: Успешно завершено!');
			return true;
		} else {
			$this->textlog->add('bookEmergencyBed: Ошибка входных данных');
			return false;
		}
	}
}
