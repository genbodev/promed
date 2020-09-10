<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Препарат
 *
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 * @property MorbusOnkoDrug_model MorbusOnkoDrug
 */

class MorbusOnkoDrug extends swController
{
	/**
	 * Описание метода
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array('field' => 'MorbusOnkoDrug_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnko_id','label' => 'c','rules' => 'required','type' => 'id'),
				array('field' => 'Evn_id','label' => 'Случай лечения','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnkoVizitPLDop_id','label' => 'Случай лечения','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnkoDiagPLStom_id','label' => 'Случай лечения','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnkoLeave_id','label' => 'Случай лечения','rules' => '','type' => 'id'),
				array('field' => 'DrugDictType_id','label' => 'Справочник','rules' => 'required','type' => 'id'),
				array('field' => 'CLSATC_id','label' => 'Препарат','rules' => '','type' => 'id'),
				array('field' => 'OnkoDrug_id','label' => 'Препарат','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnkoDrug_begDT','label' => 'Дата начала','rules' => 'required','type' => 'date'),
				array('field' => 'MorbusOnkoDrug_endDT','label' => 'Дата окончания','rules' => '','type' => 'date'),
				array('field' => 'OnkoDrugUnitType_id','label' => 'Единица','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnkoDrug_Dose','label' => '','rules' => '','type' => 'string'),
				array('field' => 'MorbusOnkoDrug_Multi','label' => '','rules' => '','type' => 'string'),
				array('field' => 'MorbusOnkoDrug_Period','label' => '','rules' => '','type' => 'string'),
				array('field' => 'MorbusOnkoDrug_SumDose','label' => '','rules' => '','type' => 'string'),
				array('field' => 'MorbusOnkoDrug_Method','label' => '','rules' => '','type' => 'string'),
				array('field' => 'MorbusOnkoDrug_IsPreventionVomiting','label' => 'Проведена профилактика тошноты и рвотного рефлекса','rules' => '','type' => 'id'),
				array('field' => 'PrescriptionIntroType_id','label' => '','rules' => '','type' => 'id'),
				array('field' => 'Drug_id','label' => 'Медикамент','rules' => '','type' => 'id'),
				array('field' => 'DrugMNN_id','label' => 'Медикамент','rules' => '','type' => 'id'),
			),
			'load' => array(
				array('field' => 'MorbusOnkoDrug_id','label' => 'Препарат','rules' => 'required','type' => 'id'),
			),
			'destroy' => array(
				array('field' => 'MorbusOnkoDrug_id','label' => 'Препарат','rules' => 'required','type' => 'id'),
			),
			'readList' => array(
				array('field' => 'Evn_id','label' => 'Случай лечения','rules' => 'required','type' => 'id'),
			),
			'readListForPrint' => array(
				array('field' => 'Evn_id','label' => 'Случай лечения','rules' => 'required','type' => 'id'),
			),
			'loadDrugCombo' => array(
				array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
				array('field' => 'CLSATC_id', 'label' => 'Класс АТХ', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор случая лечения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Date', 'label' => 'Дата на которую актуален медикамент', 'rules' => '', 'type' => 'date'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'loadFedDrugMNNCombo' => array(
				array('field' => 'DrugMNN_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор случая лечения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Date', 'label' => 'Дата, на которую актуален медикамент', 'rules' => '', 'type' => 'date'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'loadSelectionList' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Заболевание', 'rules' => 'required', 'type' => 'id'),
			),
			'setEvn' => array(
				array('field' => 'MorbusOnkoDrug_ids', 'label' => 'Препараты', 'rules' => 'required', 'type' => 'json_array'),
				array('field' => 'Evn_id', 'label' => 'Случай лечения', 'rules' => 'required', 'type' => 'id'),
			),
			'loadMorbusOnkoDrugList' => array(
				array('field' => 'Morbus_id', 'label' => 'Заболевание', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_pid', 'label' => 'Заболевание', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoDiagPLStom_id', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoLeave_id', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoVizitPLDop_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			)
		);
		$this->load->database();
		$this->load->model('MorbusOnkoDrug_model', 'MorbusOnkoDrug');
	}

	/**
	 * Описание метода
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->save($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function destroy()
	{
		$data = $this->ProcessInputData('destroy', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->destroy($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function load()
	{
		$data = $this->ProcessInputData('load', true);
		if (!$data) {
			return false;
		}
		$this->MorbusOnkoDrug->setId($data['MorbusOnkoDrug_id']);
		$response = $this->MorbusOnkoDrug->read();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function readList()
	{
		$data = $this->ProcessInputData('readList', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->readList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function readListForPrint()
	{
		$data = $this->ProcessInputData('readListForPrint', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->readListForPrint($data);
		$resp = '';
		foreach ($response as $value) {
			$resp .= '<tr>';
			$resp .= '<td>'.$value['OnkoDrug_Name'].'</td>';
			$resp .= '<td>'.$value['MorbusOnkoDrug_Dose'].'</td>';
			$resp .= '<td>'.$value['OnkoDrugUnitType_Name'].'</td>';
			$resp .= '<td>'.$value['MorbusOnkoDrug_Multi'].'</td>';
			$resp .= '<td>'.$value['MorbusOnkoDrug_Period'].'</td>';
			$resp .= '<td>'.$value['MorbusOnkoDrug_SumDose'].'</td>';
			$resp .= '<td>'.$value['MorbusOnkoDrug_begDT'].'</td>';
			$resp .= '<td>'.$value['MorbusOnkoDrug_endDT'].'</td>';
			$resp .= '<td>'.$value['MorbusOnkoDrug_Method'].'</td>';
			$resp .= '</tr>';
		}
		$this->ReturnData($resp);
		return true;
	}

	/**
	 * Загрузка записей комбобокса для выбора медикамента
	 */
	function loadDrugCombo()
	{
		$data = $this->ProcessInputData('loadDrugCombo', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->loadDrugCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка записей комбобокса для выбора медикамента
	 */
	public function loadFedDrugMNNCombo() {
		$data = $this->ProcessInputData('loadFedDrugMNNCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->MorbusOnkoDrug->loadFedDrugMNNCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка для выбора в лечении
	 */
	function loadSelectionList()
	{
		$data = $this->ProcessInputData('loadSelectionList', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->loadSelectionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка для выбора в лечении
	 */
	function setEvn()
	{
		$data = $this->ProcessInputData('setEvn', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->setEvn($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка для формы специфики
	 */
	function loadMorbusOnkoDrugList()
	{
		$data = $this->ProcessInputData('loadMorbusOnkoDrugList', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoDrug->getViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}