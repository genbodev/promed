<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* InetPerson - контроллер для работы с людьми из сайта самозаписи. бд (UserPortal)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       SWAN developers
* @version      09.04.2013
*/

class InetPerson extends swController {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->model('InetPerson_model', 'dbmodel');
		
		$this->inputRules = array(
			'loadInetPersonModerationEditWindow' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadInetPersonGrid' => array(
				array(
					'field' => 'Person_Surname',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Firname',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Secname',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Polis_Ser',
					'label' => 'Серия полиса',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Polis_Num',
					'label' => 'Номер полиса',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Phone',
					'label' => 'Телефон',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ModerateType_id',
					'label' => 'Модерация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'cancelInetPersonModeration' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonModeration_FailComment',
					'label' => 'Причина отказа',
					'rules' => '',
					'type' => 'string'
				)
			),
			'confirmInetPersonModeration' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_mainId',
					'label' => 'Идентификатор человека в базе',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Address_Address',
					'label' => 'Адрес',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Address_Corpus',
					'label' => 'Корпус',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Address_Flat',
					'label' => 'Квартира',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Address_House',
					'label' => 'Дом',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Город',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLCountry_id',
					'label' => 'Страна',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLRgn_id',
					'label' => 'Регион',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLStreet_id',
					'label' => 'Улица',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLSubRgn_id',
					'label' => 'Район',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Населённый пункт',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgSMO_id',
					'label' => 'Страховая компания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OMSSprTerr_id',
					'label' => 'Территория страхования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_BirthDate',
					'label' => 'Дата рождения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Person_Firname',
					'label' => 'Имя',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Phone',
					'label' => 'Телефон',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Secname',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Surname',
					'label' => 'Фамилия',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Polis_Num',
					'label' => 'Номер полиса',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Polis_Ser',
					'label' => 'Серия полиса',
					'rules' => '',
					'type' => 'string'
				),
			)
		);
	}


	/**
	*  Получение списка неотмодерированных людей
	*  На выходе: JSON-строка
	*/
	function loadInetPersonGrid() {
		$data = $this->ProcessInputData('loadInetPersonGrid', true, true);
		if ( $data === false ) return false;
		
		$this->load->database('UserPortal');
		
		$response = $this->dbmodel->loadInetPersonGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	*  Получение данных для формы модерации человека
	*  На выходе: JSON-строка
	*/
	function loadInetPersonModerationEditWindow() {
		$data = $this->ProcessInputData('loadInetPersonModerationEditWindow', true, true);
		if ( $data === false ) return false;
		
		$this->load->database('UserPortal');
		
		$response = $this->dbmodel->loadInetPersonModerationEditWindow($data);
		$this->ProcessModelList($response)->ReturnData();
		
		return true;
	}
	
	/**
	*  Подтверждение модерации
	*  На выходе: JSON-строка
	*/
	function confirmInetPersonModeration() {
		$data = $this->ProcessInputData('confirmInetPersonModeration', true, true);
		if ( $data === false ) return false;
		
		$personData = $data;
		
		$this->load->library('email');
		
		$this->load->database('UserPortal');
		
		// получаем данные о пользователе в UserPortal
		$userData = $this->dbmodel->getUserPortalUserData(array(
			'Person_id' => $data['Person_id']
		));
		
		if ($userData === false) {
			$this->ReturnError('Ошибка получения данных о пользователе для указанного человека');
			return false;
		}
		
		if (empty($data['Person_mainId'])) { // человек из базы не выбран, новый

			unset($this->db);
			$this->load->database('default');
			
			$response = $this->dbmodel->checkPersonDouble($data);
			if ( is_array($response) && count($response) > 0 && (int)$response[0]['DoubleType_id'] > 0 ) {
				$this->ReturnError("Человек с такими данными уже существует в базе! Добавление невозможно. Если вы не можете найти человека, попробуйте искать с буквой Ё.");
				return false;
			}
				
			// добавляем пользователя
			$Person_id = $this->dbmodel->addPerson($data);
			if (empty($Person_id)) {
				$this->ReturnError("Ошибка добавления нового человека");
				return false;
			}
			
			unset($this->db);
			$this->load->database('UserPortal');
				
			// обновляем Person_mainId, Person_isModerated в UserPortal
			$this->dbmodel->confirmPersonModeration(array(
				'Person_mainId' => $Person_id,
				'Person_id' => $data['Person_id']
			));

			$data['PersonSex_Name'] = ($data['PersonSex_id'] == 2)?'Женский':'Мужской';
			
            // Посылаем письмо что человек успешно промодерирован и добавлен в базу
            $Subject  =  'Потверждение добавления человека в картотеку k-vrachu.ru'; // тема письма
            $Body        =  
			"Уважаемый(ая) {$userData['FirstName']} {$userData['MidName']}.
			
    Данные о человеке введенные вами были проверены и человек был добавлен в базу данных. Теперь вы можете записать его на прием к врачу.
ФИО : {$data['Person_Surname']} {$data['Person_Firname']} {$data['Person_Secname']}
Пол : {$data['PersonSex_Name']}
Дата рождения : {$data['Person_BirthDate']}
Телефон : {$data['Person_Phone']}
Серия, номер полиса : {$data['Polis_Ser']} {$data['Polis_Num']}
Адрес фактического проживания : {$data['Address_Address']}";
			
            $this->email->sendKvrachu($userData['EMail'], $Subject, $Body); 
			
		} else { // берем существующего человека из базы
		
			$Person_id = $data['Person_mainId'];
			//Проверяем, что такой человек уже не добавлен в картотеку, а то бывает (#4762)
			if (!$this->dbmodel->checkPersonAlreadyModerated(array('Person_id' => $Person_id, 'pmUser_id' => $userData['id']))) { //всё хорошо, такого человека еще нет
				// обновляем Person_mainId, Person_isModerated в UserPortal
				$this->dbmodel->confirmPersonModeration(array(
					'Person_mainId' => $Person_id,
					'Person_id' => $data['Person_id']
				));
				
				unset($this->db);
				$this->load->database('default');
				
				// получаем данные о человеке из v_Person_ER.
				$personData = $this->dbmodel->getPersonERData(array('Person_id' => $Person_id));
				if ($personData === false) {
					$this->ReturnError('Ошибка получения данных о человеке в v_Person_ER');
					return false;
				}
				
				$personData['PersonSex_Name'] = ($personData['PersonSex_id'] == 2)?'Женский':'Мужской';
				
				// Посылаем письмо что человек успешно промодерирован и добавлен в базу
				$Subject = 'Потверждение добавления человека в картотеку k-vrachu.ru'; // тема письма
				$Body = 
				"Уважаемый(ая) {$userData['FirstName']} {$userData['MidName']}.
				
	Данные о человеке введенные вами были проверены, человек существовал в нашей базе данных, но с другими данными. Теперь вы можете записать его на прием к врачу. 
ФИО : {$personData['Person_Surname']} {$personData['Person_Firname']} {$personData['Person_Secname']}
Пол : {$personData['PersonSex_Name']}
Дата рождения : {$personData['Person_BirthDate']}
Телефон : {$personData['Person_Phone']}
Серия, номер полиса : {$personData['Polis_Ser']} {$personData['Polis_Num']}
Адрес фактического проживания : {$personData['Address_Address']}";
				$this->email->sendKvrachu($userData['EMail'], $Subject, $Body); 
				
			} else { // такой человек уже есть в картотеке, смысла добавлять второй раз нет
				// удаляем из Person
				$this->dbmodel->deleteFromPerson(array(
					'Person_id' => $data['Person_id']
				));

				unset($this->db);
				$this->load->database('default');
				
				// получаем данные о человеке из v_Person_ER.
				$personData = $this->dbmodel->getPersonERData(array('Person_id' => $Person_id));
				if ($personData === false) {
					$this->ReturnError('Ошибка получения данных о человеке в v_Person_ER');
					return false;
				}
				
				$personData['PersonSex_Name'] = ($personData['PersonSex_id'] == 2)?'Женский':'Мужской';
				
                // Посылаем письмо что человек уже есть в картотеке
                $Subject = 'Потверждение добавления человека в картотеку k-vrachu.ru'; // тема письма
                $Body = 
				"Уважаемый(ая) {$userData['FirstName']} {$userData['MidName']}.
				
	Данные о человеке введенные вами были проверены, этот человек уже есть в Вашей картотеке.";
				$this->email->sendKvrachu($userData['EMail'], $Subject, $Body); 
			}
		}
		
		
		unset($this->db);
		$this->load->database('default');
		
		//Обновляем информацию о телефоне в основной базе
		$this->dbmodel->personSetInternetPhone(array(
			'Person_id' => $Person_id,
			'Person_Phone' => $userData['Person_Phone'],
			'pmUser_id' => $data['pmUser_id']				
		));
				
		// Записываем информацию о модерации
		$this->dbmodel->personModeration(array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'User_id' => $userData['id'],
			'Lpu_id' => null,
			'Person_Surname' => $personData['Person_Surname'],
			'Person_Firname' => $personData['Person_Firname'],
			'Person_Secname' => $personData['Person_Secname'],
			'PersonSex_id' => $personData['PersonSex_id'],
			'Polis_Ser' => $personData['Polis_Ser'],
			'Polis_Num' => $personData['Polis_Num'],
			'Person_BirthDate' => date("Y-m-d", strtotime($personData['Person_BirthDate']))
		));

		$this->ReturnData(array('success' => true));

		return true;
	}
	
	/**
	*  Отказ в модерации
	*  На выходе: JSON-строка
	*/
	function cancelInetPersonModeration() {
		$data = $this->ProcessInputData('cancelInetPersonModeration', true, true);
		if ( $data === false ) return false;

		$this->load->library('email');
		$this->load->database('UserPortal');
		
		$response = $this->dbmodel->cancelInetPersonModeration($data);
		if (is_array($response)) {
		
			// Посылаем письмо об отказе
			$Subject = 'Отказ добавления человека в картотеку k-vrachu.ru'; // тема письма
			$Body = 
			"Уважаемый(ая) {$response['FirstName']} {$response['MidName']}.
			
	Данные о человеке введенные Вами были проверены и в добавлении человека было отказано по следующей причине :
".$data['PersonModeration_FailComment']."
			
Введенные Вами данные:
ФИО : {$response['Person_Surname']} {$response['Person_Firname']} {$response['Person_Secname']}
Пол : {$response['Sex_Name']}
Дата рождения : ".date("d.m.Y", strtotime($response['Person_BirthDate']))."}
Телефон : {$response['Person_Phone']}
Серия, номер полиса : {$response['Polis_Ser']} {$response['Polis_Num']}
Адрес фактического проживания : {$response['Address_Address']} ";
	
			$this->email->sendKvrachu($response['EMail'], $Subject, $Body); 
		
			// Записываем информацию об отмене модерации
			unset($this->db);
			$this->load->database('default');
			$this->dbmodel->personModerationFail(array(
				'pmUser_id' => $data['pmUser_id'],
				'User_id' => $response['pmUser_id'],
				'Lpu_id' => null,
				'Person_Surname' => $response['Person_Surname'],
				'Person_Firname' => $response['Person_Firname'],
				'Person_Secname' => $response['Person_Secname'],
				'PersonSex_id' => $response['PersonSex_id'],
				'Polis_Ser' => $response['Polis_Ser'],
				'Polis_Num' => $response['Polis_Num'],
				'Person_BirthDate' => date("Y-m-d", strtotime($response['Person_BirthDate'])),
				'Person_insDT' => $response['Person_updDT'],
				'PersonModeration_FailComment' => $data['PersonModeration_FailComment']
			));
			
			$this->ReturnData(array('success' => true));
			return true;
		}
		
		$this->ReturnError('В процессе отказа в модерации произошли ошибки');		
		return false;
	}
}
