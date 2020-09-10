<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * HomeVisit - контроллер для вызовов врачей на дом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      20.09.2013
 *
 * @property HomeVisit_model $dbmodel
 */
class HomeVisit extends swController {

	public $inputRules = array();
	
	var $default_model = 'HomeVisit_model';

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();

		$this->inputRules = array(
			'getHomeVisitList' => array(
				array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
				array(
					'field' => 'date',
					'label' => 'Дата вызовов',
					'rules' => '',
					'type' => 'date',
					'default' => date('Y-m-d')
				),
				array(
					'field' => 'begDate',
					'label' => 'Дата начала периода журнала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания периода журнала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'type',
					'label' => 'тип',
					'rules' => '',
					'type' => 'string'
				),
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
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'HomeVisitCallType_id',
					'label' => 'Тип вызова',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisitStatus_id',
					'label' => 'Статус вызова на дом',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач, получающий список вызовов',
					'rules' => '',
					'type' => 'int' // возможность передачи "Без врача" (-1)
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'По какому ЛПУ список вызовов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'allLpu',
					'label' => 'Флаг - по всем МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => 'Участок',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuRegion_cid',
					'label' => 'Участок вызова',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'HomeVisit_setTimeFrom',
					'label' => 'Время вызова с',
					'rules' => '',
					'type'  => 'time'
				),
				array(
					'field' => 'HomeVisit_setTimeTo',
					'label' => 'Время вызова по',
					'rules' => '',
					'type'  => 'time'
				),
				array(
					'field' => 'CallProfType_id',
					'label' => 'Профиль вызова',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'HomeVisit_isQuarantine',
					'label' => 'Карантин',
					'rules' => '',
					'type'	=> 'string'
				)
			),
			'confirmHomeVisit' => array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова на дом',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач, назначенный на вызов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_LpuComment',
					'label' => 'Дополнительная информация',
					'rules' => '',
					'type' => 'string'
				),
			),
			'takeMP' => array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова на дом',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач, назначенный на вызов',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Врач, назначенный на вызов',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_LpuComment',
					'label' => 'Дополнительная информация',
					'rules' => '',
					'type' => 'string'
				),
			),
			'setStatusNew' => array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова на дом',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getHomeVisitEditWindow'=>array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова на дом',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'denyHomeVisit' => array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова на дом',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_LpuComment',
					'label' => 'Дополнительная информация',
					'rules' => 'required',
					'type' => 'string'
				),
			),
			'cancelHomeVisit' => array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова на дом',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_LpuComment',
					'label' => 'Дополнительная информация',
					'rules' => '',
					'type' => 'string'
				),
			),
			'completeHomeVisit' => array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова на дом',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_LpuComment',
					'label' => 'Дополнительная информация',
					'rules' => '',
					'type' => 'string'
				),
			),
			'getHomeVisitAddData' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Обслуживаемый человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО вызова',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveHomeVisitAdditionalSettings' => array(
				array(
					'field' => 'HomeVisitAdditionalSettings_id',
					'label' => 'Идентификатор записи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisitAdditionalSettings_begDate',
					'label' => 'Дата с',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'HomeVisitAdditionalSettings_endDate',
					'label' => 'Дата по',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'HomeVisitAdditionalSettings_begTime',
					'label' => 'Время с',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'HomeVisitAdditionalSettings_endTime',
					'label' => 'Время по',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'HomeVisitPeriodType_id',
					'label' => 'Тип периода',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'addHomeVisit' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Обслуживаемый человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => 'Участок',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegion_cid',
					'label' => 'Участок',
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
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
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
					'field' => 'KLSubRgn_id',
					'label' => 'Район',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Город',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Населенный пункт',
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
					'field' => 'Address_House',
					'label' => 'Номер дома',
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
					'field' => 'Address_Address',
					'label' => 'Текстовый адрес',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'HomeVisit_Phone',
					'label' => 'Телефон',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'HomeVisitWhoCall_id',
					'label' => 'Кто вызывает',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_Symptoms',
					'label' => 'Симптомы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'HomeVisit_Comment',
					'label' => 'Дополнительная информация',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'HomeVisit_LpuComment',
					'label' => 'Причина отказа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'HomeVisitStatus_id',
					'label' => 'Кто вызывает',
					'rules' => '',
					'type' => 'id',
					'default'=> 1
				),
				array(
					'field' => 'HomeVisitCallType_id',
					'label' => 'Тип вызова',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_setDT',
					'label' => 'Дата/время вызова',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'HomeVisit_setDate',
					'label' => 'Дата вызова',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'HomeVisit_setTime',
					'label' => 'Время вызова',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'CallProfType_id',
					'label' => 'Профиль вызова',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HomeVisit_Num',
					'label' => 'Номер вызова',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'CmpCallCard_id',
					'label' => 'Идентификатор карты вызова',
					'rules' => '',
					'type' => 'id'
				),
				/** Анкета по КВИ (refs #198982)*/
				array('field' => 'HomeVisit_isQuarantine', 'label' => 'Карантин', 'rules' => '', 'type' => 'string'),
				array('field' => 'PlaceArrival_id', 'label' => 'Прибытие', 'rules' => '', 'type' => 'int'),
				array('field' => 'CVICountry_id', 'label' => 'Страна', 'rules' => '', 'type' => 'int'),
				array('field' => 'OMSSprTerr_id', 'label' => 'Регион', 'rules' => '', 'type' => 'int'),
				array('field' => 'ApplicationCVI_arrivalDate', 'label' => 'Дата прибытия', 'rules' => '', 'type' => 'date'),
				array('field' => 'ApplicationCVI_flightNumber', 'label' => 'Рейс', 'rules' => '', 'type' => 'string'),
				array('field' => 'ApplicationCVI_isContact', 'label' => 'Контакт с человеком с подтвержденным диагнозом КВИ', 'rules' => '', 'type' => 'int'),
				array('field' => 'ApplicationCVI_isHighTemperature', 'label' => 'Высокая температура', 'rules' => '', 'type' => 'int'),
				array('field' => 'Cough_id', 'label' => 'Кашель', 'rules' => '', 'type' => 'int'),
				array('field' => 'Dyspnea_id', 'label' => 'Одышка', 'rules' => '', 'type' => 'int'),
				array('field' => 'ApplicationCVI_Other', 'label' => 'Другое', 'rules' => '', 'type' => 'string'),
				array('field' => 'isSavedCVI', 'label' => 'Анкета КВИ', 'rules' => '', 'type' => 'int')
			),
			'loadHomeVisitStatusHist' => array(
				array(
					'field' => 'HomeVisit_id',
					'label' => 'Идентификатор вызова',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLpuPeriodStomMOList' => array(),
			'getHomeVisitNum' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата вызова',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMO' => array(
				array(
					'field' => 'KLTown_id',
					'label' => 'Нас. пункт',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'KLStreet_id',
					'label' => 'Улица',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Город',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Address_House',
					'label' => 'Дом',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Person_Age',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getHomeVisitCount' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'date',
					'label' => 'Дата вызова',
					'rules' => '',
					'type' => 'date',
					'default' => date('Y-m-d')
				)
			),
			'loadHomeVisitAdditionalSettings' => array(
				array(
					'field' => 'HomeVisitAdditionalSettings_id',
					'label' => 'Иденификатор дополнительного времени',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteHomeVisitAdditionalSettings' => array(
				array(
					'field' => 'HomeVisitAdditionalSettings_id',
					'label' => 'Иденификатор дополнительного времени',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'RevertHomeVizitStatus' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор события',
					'rules' => 'required',
					'type' => 'id')
			),
		);

		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');
		$this->load->model('HomeVisit_model', 'dbmodel');
	}

	/**
	 * Получение списка вызовов на дом
	 */
	function getHomeVisitList() {
		$data = $this->ProcessInputData('getHomeVisitList', true);
		if ( $data === false ) {
			return false;
		}
		
		$IsSMPServer = $this->config->item('IsSMPServer');
		if($IsSMPServer === true){
			
			unset($this->db);
			$this->load->database('main');

			$response = $this->dbmodel->getHomeVisitList($data);
			
			unset($this->db);
			$this->load->database();
			
		} else {

			$response = $this->dbmodel->getHomeVisitList($data);
			
		}
		
		$this->ProcessModelMultiList($response, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение списка вызовов на дом на дату, в текущее ЛПУ. Обрабатывается магическим методом
	 */
	function getHomeVisitEditWindow() {
		$data = $this->ProcessInputData('getHomeVisitEditWindow', true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getHomeVisitEditWindow($data);

		if ( is_array($response) && count($response) > 0 ) {
			$this->load->model('ApplicationCVI_model', 'ApplicationCVI_model');
			$params = [
				'HomeVisit_id' => isset($response[0]['HomeVisit_id']) ? $response[0]['HomeVisit_id'] : null
			];
			$anketaCVIdata = $this->ApplicationCVI_model->doLoadData($params);
			if (isset($anketaCVIdata[0]['KLCountry_id'])) {
				$anketaCVIdata[0]['CVICountry_id'] = $anketaCVIdata[0]['KLCountry_id'];
				unset($anketaCVIdata[0]['KLCountry_id']);
			}

			if (!empty($anketaCVIdata) && isset($anketaCVIdata[0])) {
				$response[0] = array_merge($response[0], $anketaCVIdata[0]);
			}
		}

		$this->ProcessModelList($response,true,true)->ReturnData();
	} 
	
	/**
	 * Завершить обслуживание вызова на дом. Обрабатывается магическим методом
	 */
	// function completeHomeVisit() {}

	/**
	 * Одобрить вызов на дом
	 */
	function confirmHomeVisit() {
		$data = $this->ProcessInputData('confirmHomeVisit', true);
		if ( $data === false )
			return false;

		$response = $this->dbmodel->confirmHomeVisit($data);
		$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();
		$info = $this->dbmodel->getHomeVisitInfo($data['HomeVisit_id']);
		if ( $response !== false && $info['HomeVisit_date'] == date('d.m.Y') ) { // уведомления отправляются только по сегодняшним вызовам
			
			/**
			 * Отправка оповещений
			 */
			function sendNotify($notification, $info) {
				if ( $notification['homevisit_email'] == 1 ) {
					sendNotifyEmail(
							array(
								'EMail' => $notification['EMail'],
								'title' => 'Вызов на дом одобрен',
								'body' => "Уважаемый(ая) {$notification['FirstName']} {$notification['MidName']}.
		Вызов на дом к пациенту {$info['Person_FIO']} одобрен. На вызов назначен врач {$info['MedPersonal_FIO']}"
							)
					);
				}
				if ( $notification['homevisit_sms'] == 1 ) {
					sendNotifySMS(
							array(
								'UserNotify_Phone' => $notification['UserNotify_Phone'],
								'text' => "Вызов на дом к пациенту {$info['Person_FIO']} подтвержден.",
								'User_id' => $info['pmUser_insId']
							)
					);
				}
			}
	
			$this->load->model('UserPortal_model', 'upmodel');
			$this->load->helper('Notify');
			
			if ( IsInetUser($info['pmUser_insId']) ) { // если пользователь интернет портала
				$notification = $this->upmodel->getUserNotificationSettings($info['pmUser_insId']);
				if ($notification != false) {
					sendNotify($notification, $info);
				}
			} else {
				$notifications = $this->upmodel->getPromedPersonNotificationSettings($info['Person_id']);
				if ($notifications != false) {
					foreach($notifications as $notification) {
						sendNotify($notification, $info);
					}
				}
			}
		}
	}

	/**
	 *
	 * @return type 
	 */
	function takeMP() {
		$data = $this->ProcessInputData('takeMP', true);
		if ( $data === false )
			return false;

		$response = $this->dbmodel->takeMP($data);
		$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();
	}
	
	/**
	 *
	 * @return type 
	 */
	function setStatusNew() {
		$data = $this->ProcessInputData('setStatusNew', true);
		if ( $data === false )
			return false;

		$response = $this->dbmodel->setStatusNew($data);
		$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();
	}
	
	/**
	 * Отказать в вызове на дом
	 */
	function denyHomeVisit() {
		$data = $this->ProcessInputData('denyHomeVisit', true);
		if ( $data === false )
			return false;

		$response = $this->dbmodel->denyHomeVisit($data);
		$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();
		$info = $this->dbmodel->getHomeVisitInfo($data['HomeVisit_id']);
		if ( $response !== false && $info['HomeVisit_date'] == date('Y-m-D') ) { // уведомления отправляются только по сегодняшним вызовам
			
			/**
			 * Отправка оповещений
			 */
			function sendNotify($notification, $info) {
				if ( $notification['homevisit_email'] == 1 ) {
					sendNotifyEmail(
							array(
								'EMail' => $notification['EMail'],
								'title' => 'Вызов на дом одобрен',
								'body' => "Уважаемый(ая) {$notification['FirstName']} {$notification['MidName']}.
		В вызове на дом к пациенту {$info['Person_FIO']} отказано."
							)
					);
				}
				if ( $notification['homevisit_sms'] == 1 ) {
					sendNotifySMS(
							array(
								'UserNotify_Phone' => $notification['UserNotify_Phone'],
								'text' => "В вызове на дом к пациенту {$info['Person_FIO']} отказано.",
								'User_id' => $info['pmUser_insId']
							)
					);
				}
			}
			
			$this->load->model('UserPortal_model', 'upmodel');
			$this->load->helper('Notify');
			
			if ( IsInetUser($info['pmUser_insId']) ) { // если пользователь интернет портала
				$notification = $this->upmodel->getUserNotificationSettings($info['pmUser_insId']);
				if ($notification != false) {
					sendNotify($notification, $info);
				}
			} else {
				$notifications = $this->upmodel->getPromedPersonNotificationSettings($info['Person_id']);
				if ($notifications != false) {
					foreach($notifications as $notification) {
						sendNotify($notification, $info);
					}
				}
			}
		}
	}

	/**
	 * Сохранение доп времени работы врача на дом
	 */
	function saveHomeVisitAdditionalSettings() {
		$data = $this->ProcessInputData('saveHomeVisitAdditionalSettings', false);
		if ($data === false) return false; 
		
		$data['Lpu_id'] = $_SESSION['lpu_id'];
		$data['pmUser_id'] = $_SESSION['pmuser_id'];

		$response = $this->dbmodel->saveHomeVisitAdditionalSettings($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	
	/**
	 * Отмена вызова на дом
	 */
	function cancelHomeVisit() {
		$data = $this->ProcessInputData('cancelHomeVisit', true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->cancelHomeVisit($data);
		$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();

		$info = $this->dbmodel->getHomeVisitInfo($data['HomeVisit_id']);
		if ( $response !== false && $info['HomeVisit_date'] == date('Y-m-D') ) { // уведомления отправляются только по сегодняшним вызовам

			/**
			 * Отправка оповещений
			 */
			function sendNotify($notification, $info) {
				if ( $notification['homevisit_email'] == 1 ) {
					sendNotifyEmail(
						array(
							'EMail' => $notification['EMail'],
							'title' => 'Вызов на дом отменен',
							'body' => "Уважаемый(ая) {$notification['FirstName']} {$notification['MidName']}.
		Вызов на дом к пациенту {$info['Person_FIO']} отменен."
						)
					);
				}
				if ( $notification['homevisit_sms'] == 1 ) {
					sendNotifySMS(
						array(
							'UserNotify_Phone' => $notification['UserNotify_Phone'],
							'text' => "Вызов на дом к пациенту {$info['Person_FIO']} отменен.",
							'User_id' => $info['pmUser_insId']
						)
					);
				}
			}

			$this->load->model('UserPortal_model', 'upmodel');
			$this->load->helper('Notify');

			if ( IsInetUser($info['pmUser_insId']) ) { // если пользователь интернет портала
				$notification = $this->upmodel->getUserNotificationSettings($info['pmUser_insId']);
				if ($notification != false) {
					sendNotify($notification, $info);
				}
			} else {
				$notifications = $this->upmodel->getPromedPersonNotificationSettings($info['Person_id']);
				if ($notifications != false) {
					foreach($notifications as $notification) {
						sendNotify($notification, $info);
					}
				}
			}
		}
	}

	/**
	 * Получение HTML формы со списком симптомов
	 */
	function getSymptomsHTML() {

		$symptoms = $this->dbmodel->getSymptoms();
		
		$this->load->view(
			'reg/symptoms_form',
			array(
				'symptoms' => $symptoms
			)
		);
	}
	
	/**
	 * Получение данных для формирования вызова на дом
	 */
	function getHomeVisitAddData() {
		$data = $this->ProcessInputData('getHomeVisitAddData', true);
		if ( $data === false ) {
			return false;
		}

		$resp = $this->dbmodel->checkHomeVisitExists($data);
		$msg = '';
		if ($resp) {
			$this->load->model('Polka_PersonCard_model', 'ppcmodel');
			$personData = $this->ppcmodel->getPersonData(array('Person_id'=>$data['Person_id'],'LpuAttachType_id'=>1));
			$person = (empty($personData[0]['Person_FIO']) ? $personData[0]['Person_SurName'] : $personData[0]['Person_FIO']);
			$person .= (empty($personData[0]['Person_BirthDay']) ? '' : (' '.date("d.m.Y", strtotime($personData[0]['Person_BirthDay'])).' г.р.'));
			$list = '';
			foreach ($resp as $res) {
				if($res['Lpu_id'] == $data['Lpu_id']){
					$this->ReturnError('Для '.$person.' имеется необслуженный вызов врача на дом');
					return;
				}
				$list .= (' вызов номер '.$res['HomeVisit_Num'].' от '.$res['HomeVisit_setDT'].' в '.$res['Lpu_Nick'].' ');
			}
			$msg = 'Внимание. Для '.$person.' имеются не обслуженные вызовы в др. МО:'.$list.' Продолжить?';
		}
		
		$response = $this->dbmodel->getHomeVisitAddData($data);
		if(!empty($msg))
			$response[0]['alert_msg'] = $msg;
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}
	
	/**
	 * Сохранение вызова на дом. Обрабатывается магическим методом
	 */
	function addHomeVisit() {
		$data = $this->ProcessInputData('addHomeVisit', true);
		if ( $data === false ) {
			return false;
		}

		if (
			isset($data['HomeVisit_isQuarantine']) &&
			(strtolower($data['HomeVisit_isQuarantine']) == 'on' || $data['HomeVisit_isQuarantine'] == true)
		) {
			$data['HomeVisit_isQuarantine'] = 2;
		} else {
			$data['HomeVisit_isQuarantine'] = 1;
		}

		$resp = $this->dbmodel->checkHomeVisitExists($data);
		$msg = '';
		if ($resp) {
			$this->load->model('Polka_PersonCard_model', 'ppcmodel');
			$personData = $this->ppcmodel->getPersonData(array('Person_id'=>$data['Person_id'],'LpuAttachType_id'=>1));
			$person = (empty($personData[0]['Person_FIO']) ? $personData[0]['Person_SurName'] : $personData[0]['Person_FIO']);
			$person .= (empty($personData[0]['Person_BirthDay']) ? '' : (' '.date("d.m.Y", strtotime($personData[0]['Person_BirthDay'])).' г.р.'));
			$list = '';
			foreach ($resp as $res) {
				if($res['Lpu_id'] == $data['Lpu_id']){
					$this->ReturnError('Для '.$person.' имеется необслуженный вызов врача на дом');
					return;
				}
				//$list .= (' вызов номер '.$res['HomeVisit_Num'].' от '.$res['HomeVisit_setDT'].' в '.$res['Lpu_Nick'].' ');
			}
			//$msg = 'Внимание. Для '.$person.' имеются не обслуженные вызовы в др. МО:'.$list.' Продолжить?';
		}
		
		$response = $this->dbmodel->addHomeVisit($data);
		$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * История статусов вызова
	 */
	function loadHomeVisitStatusHist() {
		$data = $this->ProcessInputData('loadHomeVisitStatusHist', true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadHomeVisitStatusHist($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка грида с доп временем
	 */
	function loadHomeVisitAdditionalSettings() {
		$data = $this->ProcessInputData('loadHomeVisitAdditionalSettings', true);
		if ( $data === false ) return false;
		
		$data['Lpu_id'] = $_SESSION['lpu_id'];

		$response = $this->dbmodel->loadHomeVisitAdditionalSettings($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 * Удаление доп времени
	 */
	function deleteHomeVisitAdditionalSettings() {
		$data = $this->ProcessInputData('deleteHomeVisitAdditionalSettings', true);
		if($data === false) { return false; }

		$response = $this->dbmodel->deleteHomeVisitAdditionalSettings($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка ЛПУ для стом. профиля
	 */
	function getLpuPeriodStomMOList() {
		$data = $this->ProcessInputData('getLpuPeriodStomMOList', true);
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->getLpuPeriodStomMOList($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение номера активного нумератора
	 */
	function getHomeVisitNum() {
		$data = $this->ProcessInputData('getHomeVisitNum', true);
		if ( $data === false ) {
			return false;
		}

		$numData = $this->dbmodel->getHomeVisitNum($data);
		if (!empty($numData['Error_Msg'])) {
			$this->ProcessModelList($numData, true, true)->ReturnData();
			return false;
		}
		$val['num'] = $numData['Numerator_Num'];
		$val['intnum'] = $numData['Numerator_IntNum'];
		$val['prenum'] = $numData['Numerator_PreNum'];
		$val['postnum'] = $numData['Numerator_PostNum'];
		$val['ser'] = $numData['Numerator_Ser'];
		$val['success'] = true;

		//return $val;
		$this->ProcessModelList($val, true, true)->ReturnData();
	}

	/**
	 * Получение количества вызовов с назначенным врачем за день
	 */
	function getHomeVisitCount() {
		$data = $this->ProcessInputData('getHomeVisitCount', true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getHomeVisitCount($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Изменение статуса вызова на дом в посещении
	 */
	function RevertHomeVizitStatus() {
		$data = $this->ProcessInputData('RevertHomeVizitStatus', true);
		if ($data === false) { return false; }

		$this->load->model('Evn_model', 'Evn_model');
		$data['EvnClass_SysNick'] = $this->Evn_model->getEvnClassSysNick($data['Evn_id']);
		$response = $this->dbmodel->revertHomeVizitStatus($data);

		if (!is_array($response))
			if ($response == true)
				$response = array(
					array(
						'Error_Msg' => null,
						'Error_Code' => 0
					)
				);
		$this->ProcessModelSave($response, true, 'При измененении статуса вызова на дом возникли ошибки')
			->ReturnData();
		return true;
	}

	/**
	 * Изменение статусов вызовов на дом в посещениях ТАП
	 */
	function RevertHomeVizitStatusesTAP() {
		$data = $this->ProcessInputData('RevertHomeVizitStatus', true);
		if ($data === false) { return false; }

		// Получаем список связанных событий
		$this->load->model('Evn_model', 'Evn_model');
		$evnTreeData = $this->Evn_model->getRelatedEvnList($data);
		foreach ($evnTreeData as $evnData) {
			if (in_array($evnData['EvnClass_SysNick'], array('EvnVizitPL', 'EvnPLStom'))) {
				$evnData['pmUser_id'] = $data['pmUser_id'];
				$temp = $this->dbmodel->checkHomeVizit($evnData);
				if ($temp) {
					$resp = $this->dbmodel->revertHomeVizitStatus($evnData);
					if (is_object($resp)) {
						$resp = $resp->result('array');
						if (isset($resp[0]) && !empty($resp[0]['Error_Msg'])) {
							$this->ProcessModelSave($resp, true, 'При измененении статусов вызовов на дом возникли ошибки')->ReturnData();
							return false;
						}
					}
				}
			}
		}
		$resp = array(
			array(
				'Error_Code' => 0,
				'Error_Msg' => null
			)
		);
		$this->ProcessModelSave($resp, true, 'При измененении статусов вызовов на дом возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Получение МО по адресу
	 */
	function getMO() {
		$data = $this->ProcessInputData('getMO', true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getMO($data);
		if (empty($response)) {
			return false;
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}

?>