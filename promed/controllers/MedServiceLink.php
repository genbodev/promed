<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Контроллер для объектов Связь между службами
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property MedServiceLink_model MedServiceLink_model
 */
class MedServiceLink extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'MedServiceLink_id',
					'label' => 'MedServiceLink_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedServiceLinkType_id',
					'label' => 'Тип связи служб',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_lid',
					'label' => 'MedService_lid',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'MedServiceLink_id',
					'label' => 'MedServiceLink_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'MedServiceLink_id',
					'label' => 'MedServiceLink_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedServiceLinkType_id',
					'label' => 'Тип связи служб',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_lid',
					'label' => 'MedService_lid',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'Тип службы просматривающей список',
					'rules' => '',
					'type' => 'string'
				)
			),
			'delete' => array(
				array(
					'field' => 'MedServiceLink_id',
					'label' => 'MedServiceLink_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('MedServiceLink_model', 'MedServiceLink_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['MedServiceLink_id'])) {
				$this->MedServiceLink_model->setMedServiceLink_id($data['MedServiceLink_id']);
			}
			if (isset($data['MedServiceLinkType_id'])) {
				$this->MedServiceLink_model->setMedServiceLinkType_id($data['MedServiceLinkType_id']);
			}
			if (isset($data['MedService_id'])) {
				$this->MedServiceLink_model->setMedService_id($data['MedService_id']);
			}
			if (isset($data['MedService_lid'])) {
				$this->MedServiceLink_model->setMedService_lid($data['MedService_lid']);
			}
			$response = $this->MedServiceLink_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Связь между службами')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->MedServiceLink_model->setMedServiceLink_id($data['MedServiceLink_id']);
			$response = $this->MedServiceLink_model->load();
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
			$response = $this->MedServiceLink_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->MedServiceLink_model->setMedServiceLink_id($data['MedServiceLink_id']);
			$response = $this->MedServiceLink_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}