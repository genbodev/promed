<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Модели анализаторов
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property AnalyzerModel_model AnalyzerModel_model
 */

class AnalyzerModel extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'AnalyzerModel_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_Name',
					'label' => '',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerModel_SysNick',
					'label' => '',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'FRMOEquipment_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerClass_id',
					'label' => 'Класс анализатора',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerInteractionType_id',
					'label' => 'Тип взаимодействия',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_IsScaner',
					'label' => 'Наличие сканера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetInteractionType_id',
					'label' => 'Тип взаимодействия с рабочими списками',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'AnalyzerModel_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerModel_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_Name',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerModel_SysNick',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerClass_id',
					'label' => 'Класс анализатора',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerInteractionType_id',
					'label' => 'Тип взаимодействия',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_IsScaner',
					'label' => 'Наличие сканера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetInteractionType_id',
					'label' => 'Тип взаимодействия с рабочими списками',
					'rules' => '',
					'type' => 'int'
				),
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerModel_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('AnalyzerModel_model', 'AnalyzerModel_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['AnalyzerModel_id'])) {
				$this->AnalyzerModel_model->setAnalyzerModel_id($data['AnalyzerModel_id']);
			}
			if (isset($data['AnalyzerModel_Name'])) {
				$this->AnalyzerModel_model->setAnalyzerModel_Name($data['AnalyzerModel_Name']);
			}
			if (isset($data['AnalyzerModel_SysNick'])) {
				$this->AnalyzerModel_model->setAnalyzerModel_SysNick($data['AnalyzerModel_SysNick']);
			}
			if (isset($data['FRMOEquipment_id'])) {
				$this->AnalyzerModel_model->setFRMOEquipment_id($data['FRMOEquipment_id']);
			}
			if (isset($data['AnalyzerClass_id'])) {
				$this->AnalyzerModel_model->setAnalyzerClass_id($data['AnalyzerClass_id']);
			}
			if (isset($data['AnalyzerInteractionType_id'])) {
				$this->AnalyzerModel_model->setAnalyzerInteractionType_id($data['AnalyzerInteractionType_id']);
			}
			if (isset($data['AnalyzerModel_IsScaner'])) {
				$this->AnalyzerModel_model->setAnalyzerModel_IsScaner($data['AnalyzerModel_IsScaner']);
			}
			if (isset($data['AnalyzerWorksheetInteractionType_id'])) {
				$this->AnalyzerModel_model->setAnalyzerWorksheetInteractionType_id($data['AnalyzerWorksheetInteractionType_id']);
			}
			$response = $this->AnalyzerModel_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Модели анализаторов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->AnalyzerModel_model->setAnalyzerModel_id($data['AnalyzerModel_id']);
			$response = $this->AnalyzerModel_model->load();
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
			$response = $this->AnalyzerModel_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->AnalyzerModel_model->setAnalyzerModel_id($data['AnalyzerModel_id']);
			$response = $this->AnalyzerModel_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


}
