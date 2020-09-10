<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для объектов Записи перечня медикаментов 
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       ModelGenerator
* @version
* @property DrugListRequest_model DrugListRequest_model
*/

class DrugListRequest extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'DrugListRequest_id',
					'label' => 'Идентификатор списка медикаментов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'Список медикаментов для заявки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugListRequest_Price',
					'label' => 'Цена',
					'rules' => '',
					'type' => 'money'
				),
				array(
					'field' => 'DrugTorgUse_id',
					'label' => 'Способ использования торгового наименования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugListRequest_Code',
					'label' => 'DrugListRequest_Code',
					'rules' => '',
					'type' => 'int'
				)
			),
			'load' => array(
				array(
					'field' => 'DrugListRequest_id',
					'label' => 'DrugListRequest_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'DrugListRequest_id',
					'label' => 'DrugListRequest_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'Список медикаментов для заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugListRequest_Price',
					'label' => 'Цена',
					'rules' => '',
					'type' => 'money'
				),
				array(
					'field' => 'DrugTorgUse_id',
					'label' => 'Способ использования торгового наименования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugListRequest_Code',
					'label' => 'DrugListRequest_Code',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delete' => array(
				array(
					'field' => 'DrugListRequest_id',
					'label' => 'DrugListRequest_id',
					'rules' => 'required',
					'type' => 'int'
				)
			)
		 );
		$this->load->database();
		$this->load->model('DrugListRequest_model', 'DrugListRequest_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['DrugListRequest_id'])) {
				$this->DrugListRequest_model->setDrugListRequest_id($data['DrugListRequest_id']);
			}
			if (isset($data['DrugRequestProperty_id'])) {
				$this->DrugListRequest_model->setDrugRequestProperty_id($data['DrugRequestProperty_id']);
			}
			if (isset($data['DrugComplexMnn_id'])) {
				$this->DrugListRequest_model->setDrugComplexMnn_id($data['DrugComplexMnn_id']);
			}
			if (isset($data['DrugListRequest_Price'])) {
				$this->DrugListRequest_model->setDrugListRequest_Price($data['DrugListRequest_Price']);
			}
			if (isset($data['DrugTorgUse_id'])) {
				$this->DrugListRequest_model->setDrugTorgUse_id($data['DrugTorgUse_id']);
			}
			if (isset($data['DrugListRequest_Code'])) {
				$this->DrugListRequest_model->setDrugListRequest_Code($data['DrugListRequest_Code']);
			}
			$response = $this->DrugListRequest_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Записи перечня медикаментов ')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->DrugListRequest_model->setDrugListRequest_id($data['DrugListRequest_id']);
			$response = $this->DrugListRequest_model->load();
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
			$response = $this->DrugListRequest_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
		
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->DrugListRequest_model->setDrugListRequest_id($data['DrugListRequest_id']);
			$response = $this->DrugListRequest_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}