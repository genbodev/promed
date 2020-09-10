<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Связь тестов с услугами
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property AnalyzerTestUslugaComplex_model AnalyzerTestUslugaComplex_model
 */

class AnalyzerTestUslugaComplex extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'AnalyzerTestUslugaComplex_id',
					'label' => 'AnalyzerTestUslugaComplex_id',
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
					'field' => 'UslugaComplex_id',
					'label' => 'Комплексная услуга',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTestUslugaComplex_Deleted',
					'label' => 'AnalyzerTestUslugaComplex_Deleted',
					'rules' => '',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'AnalyzerTestUslugaComplex_id',
					'label' => 'AnalyzerTestUslugaComplex_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadAlowedUslugaCategory_SysNicks' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'AnalyzerTest_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerTestUslugaComplex_id',
					'label' => 'AnalyzerTestUslugaComplex_id',
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
					'field' => 'UslugaComplex_id',
					'label' => 'Комплексная услуга',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTestUslugaComplex_Deleted',
					'label' => 'AnalyzerTestUslugaComplex_Deleted',
					'rules' => '',
					'type' => 'int'
				),
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerTestUslugaComplex_id',
					'label' => 'AnalyzerTestUslugaComplex_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('AnalyzerTestUslugaComplex_model', 'AnalyzerTestUslugaComplex_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['AnalyzerTestUslugaComplex_id'])) {
				$this->AnalyzerTestUslugaComplex_model->setAnalyzerTestUslugaComplex_id($data['AnalyzerTestUslugaComplex_id']);
			}
			if (isset($data['AnalyzerTest_id'])) {
				$this->AnalyzerTestUslugaComplex_model->setAnalyzerTest_id($data['AnalyzerTest_id']);
			}
			if (isset($data['UslugaComplex_id'])) {
				$this->AnalyzerTestUslugaComplex_model->setUslugaComplex_id($data['UslugaComplex_id']);
			}
			if (isset($data['AnalyzerTestUslugaComplex_Deleted'])) {
				$this->AnalyzerTestUslugaComplex_model->setAnalyzerTestUslugaComplex_Deleted($data['AnalyzerTestUslugaComplex_Deleted']);
			}
			$response = $this->AnalyzerTestUslugaComplex_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Связь тестов с услугами')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->AnalyzerTestUslugaComplex_model->setAnalyzerTestUslugaComplex_id($data['AnalyzerTestUslugaComplex_id']);
			$response = $this->AnalyzerTestUslugaComplex_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function loadAlowedUslugaCategory_SysNicks() {
		$data = $this->ProcessInputData('loadAlowedUslugaCategory_SysNicks', true);
		if ($data){
			$response = $this->AnalyzerTestUslugaComplex_model->loadAlowedUslugaCategory_SysNicks($data['AnalyzerTest_id']);
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
			$response = $this->AnalyzerTestUslugaComplex_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->AnalyzerTestUslugaComplex_model->setAnalyzerTestUslugaComplex_id($data['AnalyzerTestUslugaComplex_id']);
			$response = $this->AnalyzerTestUslugaComplex_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}