<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Штативы
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property AnalyzerRack_model AnalyzerRack_model
 */

class AnalyzerRack extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'AnalyzerRack_id',
					'label' => 'AnalyzerRack_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerRack_DimensionX',
					'label' => 'Размерность по Х',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'AnalyzerRack_DimensionY',
					'label' => 'Размерность по Y',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'AnalyzerRack_IsDefault',
					'label' => 'По умолчанию',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerRack_Deleted',
					'label' => 'AnalyzerRack_Deleted',
					'rules' => '',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'AnalyzerRack_id',
					'label' => 'AnalyzerRack_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerRack_id',
					'label' => 'AnalyzerRack_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerRack_DimensionX',
					'label' => 'Размерность по Х',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'AnalyzerRack_DimensionY',
					'label' => 'Размерность по Y',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'AnalyzerRack_IsDefault',
					'label' => 'По умолчанию',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerRack_Deleted',
					'label' => 'AnalyzerRack_Deleted',
					'rules' => '',
					'type' => 'int'
				),
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerRack_id',
					'label' => 'AnalyzerRack_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('AnalyzerRack_model', 'AnalyzerRack_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['AnalyzerRack_id'])) {
				$this->AnalyzerRack_model->setAnalyzerRack_id($data['AnalyzerRack_id']);
			}
			if (isset($data['AnalyzerModel_id'])) {
				$this->AnalyzerRack_model->setAnalyzerModel_id($data['AnalyzerModel_id']);
			}
			if (isset($data['AnalyzerRack_DimensionX'])) {
				$this->AnalyzerRack_model->setAnalyzerRack_DimensionX($data['AnalyzerRack_DimensionX']);
			}
			if (isset($data['AnalyzerRack_DimensionY'])) {
				$this->AnalyzerRack_model->setAnalyzerRack_DimensionY($data['AnalyzerRack_DimensionY']);
			}
			if (isset($data['AnalyzerRack_IsDefault'])) {
				$this->AnalyzerRack_model->setAnalyzerRack_IsDefault($data['AnalyzerRack_IsDefault']);
			}
			if (isset($data['AnalyzerRack_Deleted'])) {
				$this->AnalyzerRack_model->setAnalyzerRack_Deleted($data['AnalyzerRack_Deleted']);
			}
			$response = $this->AnalyzerRack_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Штативы')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->AnalyzerRack_model->setAnalyzerRack_id($data['AnalyzerRack_id']);
			$response = $this->AnalyzerRack_model->load();
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
			$response = $this->AnalyzerRack_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->AnalyzerRack_model->setAnalyzerRack_id($data['AnalyzerRack_id']);
			$response = $this->AnalyzerRack_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}