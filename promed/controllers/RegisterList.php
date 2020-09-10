<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Таблица регистров/справочников доступных для загрузки
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property RegisterList_model RegisterList_model
 */

class RegisterList extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'RegisterList_id',
					'label' => 'RegisterList_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterList_Name',
					'label' => 'название основной таблицы в БД',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegisterList_Schema',
					'label' => 'схема БД',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegisterList_Descr',
					'label' => 'Описание справочника',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Region_id',
					'label' => 'Идентификатор региона справочника территорий',
					'rules' => '',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'RegisterList_id',
					'label' => 'RegisterList_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'RegisterList_id',
					'label' => 'RegisterList_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterList_Name',
					'label' => 'название основной таблицы в БД',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegisterList_Schema',
					'label' => 'схема БД',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegisterList_Descr',
					'label' => 'Описание справочника',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Region_id',
					'label' => 'Идентификатор региона справочника территорий',
					'rules' => '',
					'type' => 'int'
				),
			),
			'delete' => array(
				array(
					'field' => 'RegisterList_id',
					'label' => 'RegisterList_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('RegisterList_model', 'RegisterList_model');
	}

	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['RegisterList_id'])) {
				$this->RegisterList_model->setRegisterList_id($data['RegisterList_id']);
			}
			if (isset($data['RegisterList_Name'])) {
				$this->RegisterList_model->setRegisterList_Name($data['RegisterList_Name']);
			}
			if (isset($data['RegisterList_Schema'])) {
				$this->RegisterList_model->setRegisterList_Schema($data['RegisterList_Schema']);
			}
			if (isset($data['RegisterList_Descr'])) {
				$this->RegisterList_model->setRegisterList_Descr($data['RegisterList_Descr']);
			}
			if (isset($data['Region_id'])) {
				$this->RegisterList_model->setRegion_id($data['Region_id']);
			}
			$response = $this->RegisterList_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Таблица регистров/справочников доступных для загрузки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->RegisterList_model->setRegisterList_id($data['RegisterList_id']);
			$response = $this->RegisterList_model->load();
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
			$response = $this->RegisterList_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->RegisterList_model->setRegisterList_id($data['RegisterList_id']);
			$response = $this->RegisterList_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}