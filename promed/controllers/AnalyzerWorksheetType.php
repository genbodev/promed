<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Контроллер для объектов Тип рабочих списков
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property AnalyzerWorksheetType_model AnalyzerWorksheetType_model
 */

class AnalyzerWorksheetType extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'AnalyzerWorksheetType_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_Code',
					'label' => 'Код',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheetType_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'AnalyzerWorksheetType_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'AnalyzerWorksheetType_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_Code',
					'label' => 'AnalyzerWorksheetType_Code',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheetType_Name',
					'label' => 'AnalyzerWorksheetType_Name',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'модель анализатора',
					'rules' => '',
					'type' => 'int'
				),
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'AnalyzerWorksheetType_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('AnalyzerWorksheetType_model', 'AnalyzerWorksheetType_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['AnalyzerWorksheetType_id'])) {
				$this->AnalyzerWorksheetType_model->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
			}
			if (isset($data['AnalyzerWorksheetType_Code'])) {
				$this->AnalyzerWorksheetType_model->setAnalyzerWorksheetType_Code($data['AnalyzerWorksheetType_Code']);
			}
			if (isset($data['AnalyzerWorksheetType_Name'])) {
				$this->AnalyzerWorksheetType_model->setAnalyzerWorksheetType_Name($data['AnalyzerWorksheetType_Name']);
			}
			if (isset($data['AnalyzerModel_id'])) {
				$this->AnalyzerWorksheetType_model->setAnalyzerModel_id($data['AnalyzerModel_id']);
			}
			$response = $this->AnalyzerWorksheetType_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Тип рабочих списков')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->AnalyzerWorksheetType_model->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
			$response = $this->AnalyzerWorksheetType_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->AnalyzerWorksheetType_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->AnalyzerWorksheetType_model->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
			$response = $this->AnalyzerWorksheetType_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}