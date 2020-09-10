<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * TimetableQuote - контроллер для работы с квотами на прием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      11.12.2013
 *
 * @property TimetableQuote_model $model
 */
class TimetableQuote extends swController {

	public $inputRules = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();

		$this->inputRules = array(
			'getQuotesList' => array(
				array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль, для которого показывать квоты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение, для которого показывать квоты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Врач, для которого показывать квоты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Подразделение, для которого показывать квоты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба, для которой показывать квоты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Ресурс, для которого показывать квоты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableQuoteRule_Date',
					'label' => 'Дата действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'TimetableQuoteType_id',
					'label' => 'Тип квоты',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getLpuSectionProfileList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'lpu_id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionPid_id',
					'label' => 'Идентификатор подотделения',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveQuoteRule' => array(
				array(
					'field' => 'TimetableQuoteRule_id',
					'label' => 'Идентификатор правила',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableQuoteType_id',
					'label' => 'Тип правила',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Идентификатор профиля',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Идентификатор ресурса',
					'rules' => '',
					'type' => 'id'
				),

				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableQuoteRule_begDT',
					'label' => 'Начало действия квоты',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'TimetableQuoteRule_endDT',
					'label' => 'Конец действия квоты',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'rule_subjects',
					'label' => 'Субъекты квоты',
					'rules' => 'required',
					'type' => 'string'
				),
			),
			'getQuoteRule' => array(
				array(
					'field' => 'TimetableQuoteRule_id',
					'label' => 'Идентификатор правила',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getQuoteRuleSubjects' => array(
				array(
					'field' => 'TimetableQuoteRule_id',
					'label' => 'Идентификатор правила',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteQuoteRule' => array(
				array(
					'field' => 'TimetableQuoteRule_id',
					'label' => 'Идентификатор правила',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		);

		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');
	}

	/**
	 * Получение списка квот
	 */
	function getQuotesList() {

		/**
		 * Обработка результатов
		 */
		function ProcessData( $row ) {
			$row['TimetableQuoteSubjects'] = str_replace('|', '<br/>', $row['TimetableQuoteSubjects']);
			return $row;
		}

		$data = $this->ProcessInputData('getQuotesList', true);
		if ( $data === false )
			return false;
		$this->load->model('TimetableQuote_model', 'model');
		$response = $this->model->getQuotesList($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', 'ProcessData')->ReturnData();
	}

	/**
	 * Получение списка профилей для ЛПУ с фильтрами по структуре
	 */
	function getLpuSectionProfileList() {
		$data = $this->ProcessInputData('getLpuSectionProfileList', true);
		if ( $data === false )
			return false;
		$this->load->model('TimetableQuote_model', 'model');
		$response = $this->model->getLpuSectionProfileList($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Сохранение правила квоты
	 */
	function saveQuoteRule() {
		$data = $this->ProcessInputData('saveQuoteRule', true);

		if ( $data === false )
			return false;
		
		$this->load->model('TimetableQuote_model', 'model');
		ConvertFromWin1251ToUTF8($data['rule_subjects']);
		$response = $this->model->saveQuoteRule($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

	/**
	 * Загрузка правила квоты
	 */
	function getQuoteRule() {

		/**
		 * Обработка результатов
		 */
		function ProcessData( $row ) {
			$row['TimetableQuoteRule_dateRange'] = ConvertDateFormat($row['TimetableQuoteRule_begDT'], 'd.m.Y') . ' - ' . ConvertDateFormat($row['TimetableQuoteRule_endDT'], 'd.m.Y');
			return $row;
		}

		$data = $this->ProcessInputData('getQuoteRule', true);
		if ( $data === false )
			return false;
		$this->load->model('TimetableQuote_model', 'model');
		$response = $this->model->getQuoteRule($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', 'ProcessData')->ReturnData();
	}

	/**
	 * Загрузка субъектов квоты
	 */
	function getQuoteRuleSubjects() {
		$data = $this->ProcessInputData('getQuoteRuleSubjects', true);
		if ( $data === false )
			return false;
		$this->load->model('TimetableQuote_model', 'model');
		$response = $this->model->getQuoteRuleSubjects($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление правила квоты
	 */
	function deleteQuoteRule() {
		$data = $this->ProcessInputData('deleteQuoteRule', true);
		if ( $data === false )
			return false;
		$this->load->model('TimetableQuote_model', 'model');
		$response = $this->model->deleteQuoteRule($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

}

?>