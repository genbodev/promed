<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Контроллер для объектов Список проб рабочего списка
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property AnalyzerWorksheetEvnLabSample_model AnalyzerWorksheetEvnLabSample_model
 */

class AnalyzerWorksheetEvnLabSample extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'saveBulk' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Рабочий список',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'PickedEvnLabSamples',
					'label' => 'Выбранные пробы',
					'rules' => 'required',
					'type' => 'string'
				),
			),
			'save' => array(
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_id',
					'label' => 'AnalyzerWorksheetEvnLabSample_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Рабочий список',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Проба на лабораторное исследование',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_X',
					'label' => 'Координата расположения пробы по оси X',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_Y',
					'label' => 'Координата расположения пробы по оси Y',
					'rules' => 'required',
					'type' => 'string'
				),
			),
			'load' => array(
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_id',
					'label' => 'AnalyzerWorksheetEvnLabSample_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_id',
					'label' => 'AnalyzerWorksheetEvnLabSample_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Рабочий список',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Проба на лабораторное исследование',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_X',
					'label' => 'Координата расположения пробы по оси X',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_Y',
					'label' => 'Координата расположения пробы по оси Y',
					'rules' => '',
					'type' => 'string'
				),
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerWorksheetEvnLabSample_id',
					'label' => 'AnalyzerWorksheetEvnLabSample_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadMatrix' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'AnalyzerWorksheet_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'clearMatrix' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'AnalyzerWorksheet_id',
					'rules' => 'required',
					'type' => 'int'
				)
			)
		);
		$this->load->database();
		$this->load->model('AnalyzerWorksheetEvnLabSample_model', 'AnalyzerWorksheetEvnLabSample_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['AnalyzerWorksheetEvnLabSample_id'])) {
				$this->AnalyzerWorksheetEvnLabSample_model->setAnalyzerWorksheetEvnLabSample_id($data['AnalyzerWorksheetEvnLabSample_id']);
			}
			if (isset($data['AnalyzerWorksheet_id'])) {
				$this->AnalyzerWorksheetEvnLabSample_model->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
			}
			if (isset($data['EvnLabSample_id'])) {
				$this->AnalyzerWorksheetEvnLabSample_model->setEvnLabSample_id($data['EvnLabSample_id']);
			}
			if (isset($data['AnalyzerWorksheetEvnLabSample_X'])) {
				$this->AnalyzerWorksheetEvnLabSample_model->setAnalyzerWorksheetEvnLabSample_X($data['AnalyzerWorksheetEvnLabSample_X']);
			}
			if (isset($data['AnalyzerWorksheetEvnLabSample_Y'])) {
				$this->AnalyzerWorksheetEvnLabSample_model->setAnalyzerWorksheetEvnLabSample_Y($data['AnalyzerWorksheetEvnLabSample_Y']);
			}
			$response = $this->AnalyzerWorksheetEvnLabSample_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Список проб рабочего списка')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function saveBulk() {
		$data = $this->ProcessInputData('saveBulk', true);
		if ($data){
			$response = $this->AnalyzerWorksheetEvnLabSample_model->saveBulk($data['AnalyzerWorksheet_id'], $data['PickedEvnLabSamples']);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Список проб рабочего списка')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->AnalyzerWorksheetEvnLabSample_model->setAnalyzerWorksheetEvnLabSample_id($data['AnalyzerWorksheetEvnLabSample_id']);
			$response = $this->AnalyzerWorksheetEvnLabSample_model->load();
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
			$response = $this->AnalyzerWorksheetEvnLabSample_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получает матрицу проб для рабочего списка
	 * @return bool
	 */
	function loadMatrix() {
		$data = $this->ProcessInputData('loadMatrix', true);
		if ($data) {
			$response = $this->AnalyzerWorksheetEvnLabSample_model->loadMatrix($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Очищает матрицу проб рабочего списка
	 * @return bool
	 */
	function clearMatrix() {
		$data = $this->ProcessInputData('clearMatrix', true);
		if ($data) {
			$response = $this->AnalyzerWorksheetEvnLabSample_model->clearMatrix($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}



	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->AnalyzerWorksheetEvnLabSample_model->setAnalyzerWorksheetEvnLabSample_id($data['AnalyzerWorksheetEvnLabSample_id']);
			$response = $this->AnalyzerWorksheetEvnLabSample_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}