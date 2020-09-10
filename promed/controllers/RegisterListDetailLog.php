<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Контроллер для объектов Таблица детальных логов
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property RegisterListDetailLog_model RegisterListDetailLog_model
 */

class RegisterListDetailLog extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'RegisterListDetailLog_id',
					'label' => 'RegisterListDetailLog_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListDetailLog_setDT',
					'label' => 'Дата, время записи',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegisterListLogType_id',
					'label' => 'Тип сообщения лога',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListDetailLog_Message',
					'label' => 'Текст сообщения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegisterListLog_id',
					'label' => 'RegisterListLog_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'RegisterListDetailLog_id',
					'label' => 'RegisterListDetailLog_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'RegisterListDetailLog_id',
					'label' => 'RegisterListDetailLog_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListDetailLog_setDT',
					'label' => 'Дата, время записи',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegisterListLogType_id',
					'label' => 'Тип сообщения лога',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListDetailLog_Message',
					'label' => 'Текст сообщения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegisterListLog_id',
					'label' => 'RegisterListLog_id',
					'rules' => '',
					'type' => 'int'
				),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),
			),
			'delete' => array(
				array(
					'field' => 'RegisterListDetailLog_id',
					'label' => 'RegisterListDetailLog_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('RegisterListDetailLog_model', 'RegisterListDetailLog_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['RegisterListDetailLog_id'])) {
				$this->RegisterListDetailLog_model->setRegisterListDetailLog_id($data['RegisterListDetailLog_id']);
			}
			if (isset($data['RegisterListDetailLog_setDT'])) {
				$this->RegisterListDetailLog_model->setRegisterListDetailLog_setDT($data['RegisterListDetailLog_setDT']);
			}
			if (isset($data['RegisterListLogType_id'])) {
				$this->RegisterListDetailLog_model->setRegisterListLogType_id($data['RegisterListLogType_id']);
			}
			if (isset($data['RegisterListDetailLog_Message'])) {
				$this->RegisterListDetailLog_model->setRegisterListDetailLog_Message($data['RegisterListDetailLog_Message']);
			}
			if (isset($data['RegisterListLog_id'])) {
				$this->RegisterListDetailLog_model->setRegisterListLog_id($data['RegisterListLog_id']);
			}
			$response = $this->RegisterListDetailLog_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Таблица детальных логов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_id($data['RegisterListDetailLog_id']);
			$response = $this->RegisterListDetailLog_model->load();
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
			$response = $this->RegisterListDetailLog_model->loadList($filter);
			$this->ProcessModelMultiList($response, true, true)->formatDatetimeFields('d.m.Y H:i:s')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_id($data['RegisterListDetailLog_id']);
			$response = $this->RegisterListDetailLog_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}