<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TimetableGraf6E - работа с расписанием в поликлинике для форм ExtJS 6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      30.11.2009
 * @property TimetableGraf6E_model $dbmodel
 */
 
class TimetableGraf6E extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->inputRules = array(
			'loadPolkaWorkPlaceList' => array(
				array(
					'default' => '',
					'field' => 'date_range',
					'label' => 'Период случаев',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'begDate',
					'label' => 'Дата начала периода расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания периода расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Phone_all',
					'label' => 'Телефон',
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
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
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
					'label' => 'Место работы врача',
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
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Режим',
					'rules' => '',
					'type' => 'string',
					'default' => 'polka'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id',
					'default' => ''
				),
				array(
					'field' => 'ElectronicService_id',
					'label' => 'Пункт обслуживания',
					'rules' => '',
					'type' => 'id',
				),
				array(
					'field' => 'showLiveQueue',
					'label' => 'Все услуги без записи',
					'rules' => '',
					'type' => 'int',
				),
				array(
					'field' => 'MedStaffFactFilterType_id',
					'label' => 'Фильтр списка записанных пациентов',
					'rules' => '',
					'type' => 'id',
				)
			),
			'loadTimeTableGrafRecList' => array(
				array(
					'default' => '',
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
			),
			'saveCheckedPerson' => array(
				array(
					'default' => '',
					'field' => 'TimetableGrafRecList',
					'label' => 'Список пациентов',
					'rules' => 'required',
					'type' => 'string'
				),
			)
		);

		$this->load->helper('Reg');

		$this->load->database();
		$this->load->model('TimetableGraf6E_model', 'dbmodel');
    }
	/**
	 * Расписание на заданную дату
	 */
	function loadPolkaWorkPlaceList() {
		$data = $this->ProcessInputData('loadPolkaWorkPlaceList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPolkaWorkPlaceList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * Расписание на заданную дату
	 */
	function loadTimeTableGrafRecList() {
		$data = $this->ProcessInputData('loadTimeTableGrafRecList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTimeTableGrafRecList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * Создание шаблона лекарственного назначения
	 */
	function saveCheckedPerson(){
		$data = $this->ProcessInputData('saveCheckedPerson',true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveCheckedPerson($data);
		$this->ProcessModelSave($response, true, 'При смене значения атрибута посещения возникла ошибка')->ReturnData();

		return true;
	}
}