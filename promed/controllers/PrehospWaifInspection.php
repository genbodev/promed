<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PrehospWaifInspection - контроллер работы с осмотрами беспризорных 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Пермяков Александр
* @version      2011 год
*/

class PrehospWaifInspection extends swController {

	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'getRecord' => array(
				array('field' => 'PrehospWaifInspection_id','label' => 'Идентификатор','rules' => 'required','type' => 'id')
			),
			/*'deleteRecord' => array(
				array('field' => 'PrehospWaifInspection_id','label' => 'Идентификатор','rules' => 'required','type' => 'id')
			),*/
			'loadRecordGrid' => array(
				array('field' => 'EvnPS_id','label' => 'Идентификатор КВС','rules' => 'required','type' => 'id'),
				array('field' => 'start','default' => 0,'label' => 'Начальный номер записи','rules' => '','type' => 'int'),
				array('field' => 'limit','default' => 100,'label' => 'Количество возвращаемых записей','rules' => '','type' => 'int')
			),
			'saveRecord' => array(
				array('field' => 'PrehospWaifInspection_id','default' => 0,'label' => 'Идентификатор','rules' => '','type' => 'id'),
				array('field' => 'EvnPS_id','label' => 'Идентификатор КВС','rules' => 'required','type' => 'id'),
				array('field' => 'PrehospWaifInspection_SetDT','label' => 'Дата осмотра','rules' => 'required|trim','type' => 'date'),
				array('field' => 'LpuSection_id','label' => 'Отделение','rules' => 'required','type' => 'id'),
				array('field' => 'MedStaffFact_id','label' => 'Врач','rules' => 'required','type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз','rules' => 'required','type' => 'id')
			)
		);
	}

	/**
	*  Функция чтения списка записей для грида
	*  На выходе: JSON-строка
	*  Используется: форма swEvnPSPriemEditWindow
	*/
	function loadRecordGrid() {
		$data = $this->ProcessInputData('loadRecordGrid', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('PrehospWaifInspection_model', 'PrehospWaifInspection_model');
			$response = $this->PrehospWaifInspection_model->loadRecordGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	*  Функция сохранения одной записи
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swPrehospWaifInspectionEditWindow
	*/
	function saveRecord() {
		$data = $this->ProcessInputData('saveRecord', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('PrehospWaifInspection_model', 'PrehospWaifInspection_model');
			$response = $this->PrehospWaifInspection_model->saveRecord($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция чтения одной записи
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swPrehospWaifInspectionEditWindow
	*/
	function getRecord() {
		$data = $this->ProcessInputData('getRecord', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('PrehospWaifInspection_model', 'PrehospWaifInspection_model');
			$response = $this->PrehospWaifInspection_model->getRecord($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
}
