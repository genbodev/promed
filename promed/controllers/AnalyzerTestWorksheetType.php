<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Контроллер для объектов Связь тестов с типами рабочего списка
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property AnalyzerTestWorksheetType_model AnalyzerTestWorksheetType_model
 */

class AnalyzerTestWorksheetType extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'AnalyzerTestWorksheetType_id',
					'label' => 'AnalyzerTestWorksheetType_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Тесты анализаторов',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'Тип рабочего списка',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'AnalyzerTestWorksheetType_id',
					'label' => 'AnalyzerTestWorksheetType_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerTestWorksheetType_id',
					'label' => 'AnalyzerTestWorksheetType_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Тесты анализаторов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'Тип рабочего списка',
					'rules' => '',
					'type' => 'int'
				),
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerTestWorksheetType_id',
					'label' => 'AnalyzerTestWorksheetType_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('AnalyzerTestWorksheetType_model', 'AnalyzerTestWorksheetType_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['AnalyzerTestWorksheetType_id'])) {
				$this->AnalyzerTestWorksheetType_model->setAnalyzerTestWorksheetType_id($data['AnalyzerTestWorksheetType_id']);
			}
			if (isset($data['AnalyzerTest_id'])) {
				$this->AnalyzerTestWorksheetType_model->setAnalyzerTest_id($data['AnalyzerTest_id']);
			}
			if (isset($data['AnalyzerWorksheetType_id'])) {
				$this->AnalyzerTestWorksheetType_model->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
			}
			$response = $this->AnalyzerTestWorksheetType_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Связь тестов с типами рабочего списка')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->AnalyzerTestWorksheetType_model->setAnalyzerTestWorksheetType_id($data['AnalyzerTestWorksheetType_id']);
			$response = $this->AnalyzerTestWorksheetType_model->load();
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
			$response = $this->AnalyzerTestWorksheetType_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->AnalyzerTestWorksheetType_model->setAnalyzerTestWorksheetType_id($data['AnalyzerTestWorksheetType_id']);
			$response = $this->AnalyzerTestWorksheetType_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}