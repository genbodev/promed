<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
* Timetable6E - общие методы для работы с расписанием
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
 *
 * @property Timetable_model $dbmodel
 */
class Timetable6E extends swController {
	/**
	 * Timetable constructor.
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('Timetable6E_model', 'dbmodel');
		
		$this->inputRules = [
			'loadLpuStructureTree' => [
				['field' => 'parentNodeType', 'label' => 'Тип родительского узла', 'rules' => 'required', 'type' => 'string'],
				['field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'],
				['field' => 'LpuUnitType_id', 'label' => 'Идентификатор типа подразделения', 'rules' => '', 'type' => 'id'],
				['field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'],
				['field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'],
			],
			'loadSubjectList' => [
				['field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'],
				['field' => 'LpuUnitType_id', 'label' => 'Идентификатор типа подразделения', 'rules' => '', 'type' => 'id'],
				['field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'],
				['field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'],
				['field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id'],
				['field' => 'query', 'label' => 'Запрос', 'rules' => 'trim', 'type' => 'string'],
			],
			'loadTimetableTypeList' => [

			],
			'loadTimetableSchedule' => [
				['field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Date', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'],
			],
			'saveTimetableSchedule' => [
				['field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Range', 'label' => 'Период', 'rules' => 'required', 'type' => 'daterange'],
				['field' => 'TimetableType_id', 'label' => 'Идентификатор типа бирки', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Duration', 'label' => 'Длительность приема', 'rules' => 'required', 'type' => 'int'],
				['field' => 'BegTime', 'label' => 'Начало работ', 'rules' => 'required', 'type' => 'string'],
				['field' => 'EndTime', 'label' => 'Окончание работ', 'rules' => 'required', 'type' => 'string'],
			],
			'deleteTimetableSchedule' => [
				['field' => 'ids', 'label' => 'Идентификаторы', 'rules' => 'required', 'type' => 'json_array'],
			],
			'copyTimetableSchedule' => [
				['field' => 'ids', 'label' => 'Идентификаторы', 'rules' => 'required', 'type' => 'json_array'],
				['field' => 'fromRange', 'label' => 'Копирование с диапазона', 'rules' => 'required', 'type' => 'daterange'],
				['field' => 'toRange', 'label' => 'Копирование на диапазон', 'rules' => 'required', 'type' => 'daterange'],
				['field' => 'repeatable', 'label' => 'Флаг цикличного копирования в диапазоне', 'rules' => '', 'type' => 'checkbox'],
			],
			'setTimetableType' => [
				['field' => 'ids', 'label' => 'Идентификаторы', 'rules' => 'required', 'type' => 'json_array'],
				['field' => 'typeId', 'label' => 'Идентификаторы типа бирки', 'rules' => 'required', 'type' => 'id'],
			],
			'loadAnnotationGrid' => [
				['field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Date', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'],
			],
			'loadAnnotationTypeList' => [
				
			],
			'loadAnnotationEditForm' => [
				['field' => 'Annotation_id', 'label' => 'Идентификатор примечания', 'rules' => 'required', 'type' => 'id'], 
			],
			'saveAnnotation' => [
				['field' => 'Annotation_id', 'label' => 'Идентификатор примечания', 'rules' => '', 'type' => 'id'],
				['field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Annotation_begDate', 'label' => 'Дата начала примечания', 'rules' => 'required', 'type' => 'date'],
				['field' => 'Annotation_begTime', 'label' => 'Время начала примечания', 'rules' => '', 'type' => 'time'],
				['field' => 'Annotation_endDate', 'label' => 'Дата окончания примечания', 'rules' => 'required', 'type' => 'date'],
				['field' => 'Annotation_endTime', 'label' => 'Время окончания примечания', 'rules' => '', 'type' => 'time'],
				['field' => 'AnnotationType_id', 'label' => 'Идентификатор типа примечания', 'rules' => 'required', 'type' => 'id'],
				['field' => 'AnnotationVison_id', 'label' => 'Идентификатор видимости примечания', 'rules' => 'required', 'type' => 'id'],
			],
			'setAnnotationRange' => [
				['field' => 'Annotation_id', 'label' => 'Идентификатор примечания', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Annotation_begDate', 'label' => 'Дата начала примечания', 'rules' => 'required', 'type' => 'date'],
				['field' => 'Annotation_endDate', 'label' => 'Дата окончания примечания', 'rules' => '', 'type' => 'date'],
			],
			'deleteAnnotation' => [
				['field' => 'Annotation_id', 'label' => 'Идентификатор примечания', 'rules' => '', 'type' => 'id'],
			],
			'addAnnotationTypeCustom' => [
				['field' => 'name', 'label' => 'Наименование типа примечания', 'rules' => 'required|trim', 'type' => 'string'],
			],
			'deleteAnnotationTypeCustom' => [
				['field' => 'id', 'label' => 'Идентификатор типа примечания', 'rules' => 'required', 'type' => 'id'],
			],
		];
	}
	
	/**
	 * Получение структуры МО для работы с расписанием
	 */
	function loadLpuStructureTree() {
		$data = $this->ProcessInputData('loadLpuStructureTree', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->loadLpuStructureTree($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	function loadSubjectList() {
		$data = $this->ProcessInputData('loadSubjectList', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->loadSubjectList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	function loadTimetableTypeList() {
		$data = $this->ProcessInputData('loadTimetableTypeList', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->loadTimetableTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	function loadTimetableSchedule() {
		$data = $this->ProcessInputData('loadTimetableSchedule', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->loadTimetableSchedule($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function saveTimetableSchedule() {
		$data = $this->ProcessInputData('saveTimetableSchedule', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->saveTimetableSchedule($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function deleteTimetableSchedule() {
		$data = $this->ProcessInputData('deleteTimetableSchedule', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->deleteTimetableSchedule($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function copyTimetableSchedule() {
		$data = $this->ProcessInputData('copyTimetableSchedule', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->copyTimetableSchedule($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function setTimetableType() {
		$data = $this->ProcessInputData('setTimetableType', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->setTimetableType($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	function loadAnnotationTypeList() {
		$data = $this->ProcessInputData('loadAnnotationTypeList', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->loadAnnotationTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	function loadAnnotationEditForm() {
		$data = $this->ProcessInputData('loadAnnotationEditForm', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->loadAnnotationEditForm($data);
		$this->ProcessModelList($response)->ReturnData();
	}
	
	function saveAnnotation() {
		$data = $this->ProcessInputData('saveAnnotation', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->saveAnnotation($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	function setAnnotationRange() {
		$data = $this->ProcessInputData('setAnnotationRange', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->setAnnotationRange($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	function deleteAnnotation() {
		$data = $this->ProcessInputData('deleteAnnotation', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->deleteAnnotation($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	function addAnnotationTypeCustom() {
		$data = $this->ProcessInputData('addAnnotationTypeCustom', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->addAnnotationTypeCustom($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	function deleteAnnotationTypeCustom() {
		$data = $this->ProcessInputData('deleteAnnotationTypeCustom', true);
		if ($data === false) return;
		
		$response = $this->dbmodel->deleteAnnotationTypeCustom($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
}