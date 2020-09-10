<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuBuildingOfficeMedStaffLink - контроллер для работы с местами работы в кабинетах
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property LpuBuildingOfficeMedStaffLink_model dbmodel
 */

class LpuBuildingOfficeMedStaffLink extends swController {
	protected $inputRules = array(
		'delete' => array(
			array('field' => 'LpuBuildingOfficeMedStaffLink_id', 'label' => 'Идентификатор связки кабинета с местом работы', 'rules' => 'required', 'type' => 'id'),
		),
		'load' => array(
			array('field' => 'LpuBuildingOfficeMedStaffLink_id', 'label' => 'Идентификатор связки кабинета с местом работы', 'rules' => 'required', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
			array('default' => 0, 'field' => 'start','label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit','label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('field' => 'isClose','label' => 'Признак закрытой записи', 'rules' => '', 'type' => 'int'),
		),
		'loadScheduleWorkDoctor' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Должность', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Кабинет', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_id', 'label' => 'Участок', 'rules' => '', 'type' => 'id'),

			array('field' => 'mondayDate', 'label' => 'Дата начала периода', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'sundayDate', 'label' => 'Дата окончания периода', 'rules' => 'trim|required', 'type' => 'date'),

		),
		'save' => array(
			array('field' => 'LpuBuildingOfficeMedStaffLink_id', 'label' => 'Идентификатор связки кабинета с местом работы', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOfficeMedStaffLink_begDate', 'label' => 'Дата начала действия', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'LpuBuildingOfficeMedStaffLink_endDate', 'label' => 'Дата окончания действия', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'LpuBuildingOfficeVizitTimeData', 'label' => 'Время приема', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuBuildingOfficeVizitTimeRemovedDays', 'label' => 'Удаленные дни', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'ignoreMedStaffFactDoubles', 'label' => 'Признак игнорирования дублей места работы в одном кабинете', 'rules' => '', 'type' => 'int'),
			array('field' => 'ignoreLpuBuildingDoubles', 'label' => 'Признак игнорирования дублей кабинетов для одного места работы', 'rules' => '', 'type' => 'int'),
		),

		// Сохранение формы «Выбор кабинета»
		'saveChoiceLpuBuildingOffice' => array(
			array('field' => 'LpuBuildingOffice_Number', 'label' => 'Номер и название текущего кабинета', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Fio', 'label' => 'ФИО врача', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuBuildingOfficeMedStaffLink_id', 'label' => 'Идентификатор связки кабинета с местом работы', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingOfficeMedStaffLink_begDate', 'label' => 'Дата начала', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'LpuBuildingOfficeMedStaffLink_endDate', 'label' => 'Дата окончания', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'checkDatesToChangeOfficeNumber', 'label' => 'Признак согласия на изменение номера кабинета при совпадении периодов приема', 'rules' => '', 'type' => 'int')

		),

		// Сохранение формы «Выбор времени приёма»
		'saveChoiceVizitTime' => array(
			array('field' => 'CalendarWeek_id', 'label' => 'Выбранный день недели', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuBuildingOfficeVizitTime_id', 'label' => 'Идентификатор времени приема', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOfficeMedStaffLink_id', 'label' => 'Идентификатор связки кабинета с местом работы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'curDate', 'label' => 'Дата выбранного дня недели', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'LpuBuildingOfficeVizitTime_begDate', 'label' => 'Время начала', 'rules' => 'trim|required', 'type' => 'time'),
			array('field' => 'LpuBuildingOfficeVizitTime_endDate', 'label' => 'Время окончания', 'rules' => 'trim|required', 'type' => 'time'),
			array('field' => 'LpuBuildingOfficeVizitTime_period', 'label' => 'Период', 'rules' => 'required', 'type' => 'int')
		),

		'printList' => array(
			array('field' => 'paramBegDate', 'label' => 'Отчетная дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'paramLpu', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'paramLpuBuilding', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
		),

		'getCurrentOffice' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id')
		),

		'changeCurrentOffice' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingOffice_id', 'label' => 'Идентификатор кабинета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingOfficeVizitTime_begDate', 'label' => 'Время начала', 'rules' => 'trim', 'type' => 'time'),
			array('field' => 'LpuBuildingOfficeVizitTime_endDate', 'label' => 'Время окончания', 'rules' => 'trim', 'type' => 'time')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('LpuBuildingOfficeMedStaffLink_model', 'dbmodel');
	}

	/**
	 * Удаление связки кабинета с местом работы
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении связки кабинета с местом работы')->ReturnData();

		return true;
	}

	/**
	 * Возвращает список связок кабинетов с местами работы
	 */
	public function loadList() {
		$data = $this->ProcessInputData('loadList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Возвращает список врачей для формы "Расписания работы врачей" в виде чистого HTML
	 */
	public function loadScheduleWorkDoctor() {
		$data = $this->ProcessInputData('loadScheduleWorkDoctor', true, true);
		if ($data === false) { return false; }

		$rows = $this->dbmodel->loadScheduleWorkDoctor($data);

		$view = 'reg/scheduleworkdoctor_data';

		return $this->load->view($view, array(
			'mondayDate' => $data['mondayDate'],
			'sundayDate' => $data['sundayDate'],
			'rows' => $rows
		));
	}

	/**
	 * Возвращает данные связки кабинета с местом работы
	 */
	public function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение связки кабинета с местом работы
	 */
	public function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}


	/**
	 * Сохранение формы "Выбор кабинета"
	 * @return bool
	 */
	public function saveChoiceLpuBuildingOffice(){
		$data = $this->ProcessInputData('saveChoiceLpuBuildingOffice');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveChoiceLpuBuildingOffice($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}


	/**
	 * Сохрание формы "Выбор времени приёма"
	 * @return bool
	 */
	public function saveChoiceVizitTime(){
		$data = $this->ProcessInputData('saveChoiceVizitTime');
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveChoiceVizitTime($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}


	/**
	 * Печатная форма на 2 листах
	 *
	 * Печатная форма содержит информацию о графике работы врачей (из связей кабинет – место работы)
	 * выбранного подразделения за указанный день.
	 *
	 * Лист 1
	 * На первом листе отображается информация о работе врачей, не обслуживающих по участкам (место работы врача
	 * не связано с участком на дату, указанную в поле «Дата» формы «Параметры печати»)
	 *
	 * Лист 2
	 * На втором листе отображается информация о работе врачей, обслуживающих по участкам (место работы врача
	 * связано с участком на дату, указанную в поле «Дата» формы «Параметры печати»).
	 */
	public function printList() {
		$data = $this->ProcessInputData('printList');
		if ($data === false) { return false; }

		echo 'Функционал в разработке';

		return true;
	}

	/**
	 * Возвращает данные связки кабинета
	 * с местом работы на основании рабочего места или службы
	 */
	public function getCurrentOffice() {
		$data = $this->ProcessInputData('getCurrentOffice');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getCurrentOffice($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохраняет связь кабинета и рабочего места врача (из АРМА)
	 */
	public function changeCurrentOffice() {
		$data = $this->ProcessInputData('changeCurrentOffice');
		if ($data === false) { return false; }

		$response = $this->dbmodel->changeCurrentOffice($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
}