<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для объектов Правополучатели
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       ModelGenerator
* @version
* @property WhsDocumentRightRecipient_model WhsDocumentRightRecipient_model
*/

class WhsDocumentRightRecipient extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'WhsDocumentRightRecipient_id',
					'label' => 'WhsDocumentRightRecipient_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentTitle_id',
					'label' => 'Правоустанавливающий документ',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentRightRecipient_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'WhsDocumentRightRecipient_endDate',
					'label' => 'Дата окончания дийствия',
					'rules' => '',
					'type' => 'datetime'
				)
			),
			'load' => array(
				array(
					'field' => 'WhsDocumentRightRecipient_id',
					'label' => 'WhsDocumentRightRecipient_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentRightRecipient_id',
					'label' => 'WhsDocumentRightRecipient_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentTitle_id',
					'label' => 'Правоустанавливающий документ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentRightRecipient_begDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'WhsDocumentRightRecipient_endDate',
					'label' => 'Дата окончания дийствия',
					'rules' => '',
					'type' => 'datetime'
				)
			),
			'delete' => array(
				array(
					'field' => 'WhsDocumentRightRecipient_id',
					'label' => 'WhsDocumentRightRecipient_id',
					'rules' => 'required',
					'type' => 'int'
				)
			)
		 );
		$this->load->database();
		$this->load->model('WhsDocumentRightRecipient_model', 'WhsDocumentRightRecipient_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['WhsDocumentRightRecipient_id'])) {
				$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_id($data['WhsDocumentRightRecipient_id']);
			}
			if (isset($data['WhsDocumentTitle_id'])) {
				$this->WhsDocumentRightRecipient_model->setWhsDocumentTitle_id($data['WhsDocumentTitle_id']);
			}
			if (isset($data['Org_id'])) {
				$this->WhsDocumentRightRecipient_model->setOrg_id($data['Org_id']);
			}
			if (isset($data['Contragent_id'])) {
				$this->WhsDocumentRightRecipient_model->setContragent_id($data['Contragent_id']);
			}
			if (isset($data['WhsDocumentRightRecipient_begDate'])) {
				$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_begDate($data['WhsDocumentRightRecipient_begDate']);
			}
			if (isset($data['WhsDocumentRightRecipient_endDate'])) {
				$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_endDate($data['WhsDocumentRightRecipient_endDate']);
			}
			$response = $this->WhsDocumentRightRecipient_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Правополучатели')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_id($data['WhsDocumentRightRecipient_id']);
			$response = $this->WhsDocumentRightRecipient_model->load();
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
			$response = $this->WhsDocumentRightRecipient_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
		
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_id($data['WhsDocumentRightRecipient_id']);
			$response = $this->WhsDocumentRightRecipient_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}