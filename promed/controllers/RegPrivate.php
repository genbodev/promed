<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Reg - контроллер для общих операций электронной регистратуры частной клиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       brotherhood of swan developers
 *
 * @property RegPrivate_model dbmodel
 */
class RegPrivate extends swController {

	public $inputRules = array(
		'loadIncomeRequests' => array(
			array('field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id')
		),
		'loadProcessedRequests' => array(
			array('field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
			array('field' => 'date', 'label' => 'Фильтр даты', 'rules' => '', 'type' => 'date')
		),
		'loadRequestData' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
		),
		'saveRequest' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_Phone', 'label' => 'Номер телефона', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetableGraf_begTime_date', 'label' => 'Дата записи к врачу', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'TimetableGraf_begTime_time', 'label' => 'Время записи к врачу', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'OverrideWarning', 'label' => 'Признак подтверждения предупреждения при записи', 'rules' => '', 'type' => 'int'),
			array('field' => 'overwriteTimetableGraf', 'label' => 'Признак подтверждения перезаписи бирки', 'rules' => '', 'type' => 'int')
		),
		'declineRequest' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStatusHistory_Cause', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string')
		),
		'setVisitApproveStatus' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'isApprove', 'label' => 'Статус доходимости', 'rules' => 'required', 'type' => 'int'),
		),
		'setRequestStatus' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Системное имя статуса направления', 'rules' => 'required', 'type' => 'string'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('RegPrivate_model', 'dbmodel');
	}

	/**
	 * Загрузка входящих заявок
	 */
	function loadIncomeRequests(){

		$data = $this->ProcessInputData('loadIncomeRequests', true);
		if ( $data === false ) return false;

		$response = $this->dbmodel->loadIncomeRequests($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Загрузка обработанных заявок
	 */
	function loadProcessedRequests(){

		$data = $this->ProcessInputData('loadProcessedRequests', true);
		if ( $data === false ) return false;

		$response = $this->dbmodel->loadProcessedRequests($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Загрузка инфы по человеку
	 */
	function loadRequestData(){

		$data = $this->ProcessInputData('loadRequestData', true);
		if ( $data === false ) return false;
		$this->load->database('default');

		$response = $this->dbmodel->loadRequestData($data);

        if (!empty($response)) {
			$response = array(
				'success' => true,
				'data' =>  array($response)
			);
		} else {
			$response = array(
				'success' => true,
				'data' =>  array()
			);
		}

		$this->ProcessModelMultiList($response, true, true, 'При запросе возникла ошибка.', null, true)->ReturnData();
	}

	/**
	 * Подтверждение заявки
	 */
	function saveRequest(){

		$data = $this->ProcessInputData('saveRequest', true);
		if ( $data === false ) return false;

		$response = $this->dbmodel->saveRequest($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Подтверждение заявки
	 */
	function declineRequest(){

		$data = $this->ProcessInputData('declineRequest', true);
		if ( $data === false ) return false;

		$response = $this->dbmodel->declineRequest($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Подтверждение заявки
	 */
	function setVisitApproveStatus(){

		$data = $this->ProcessInputData('setVisitApproveStatus', true);
		if ( $data === false ) return false;

		$response = $this->dbmodel->setVisitApproveStatus($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Смена статуса заявки(блокировка, разблокировка)
	 */
	function setRequestStatus(){

		$data = $this->ProcessInputData('setRequestStatus', true);
		if ( $data === false ) return false;

		$response = $this->dbmodel->setRequestStatus($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}