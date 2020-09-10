<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Таблица логов запуска загрузок
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property RegisterListLog_model RegisterListLog_model
 */

class RegisterListLog extends swController
{
	/**
	 * @construct
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'RegisterListLog_id',
					'label' => 'Строка лога с запуском загрузки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListLog_begDT',
					'label' => 'название основной таблицы в БД',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegisterListLog_endDT',
					'label' => 'схема БД',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegisterListRunType_id',
					'label' => 'Тип запуска',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListLog_AllCount',
					'label' => 'Количество записей в файле',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListLog_UploadCount',
					'label' => 'Количество загруженных записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListResultType_id',
					'label' => 'Результат загрузки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterList_id',
					'label' => 'Загружаемый регистр/справочник',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'RegisterListLog_id',
					'label' => 'Строка лога с запуском загрузки',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'RegisterListLog_id',
					'label' => 'Строка лога с запуском загрузки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListLog_begDT',
					'label' => 'название основной таблицы в БД',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegisterListLog_endDT',
					'label' => 'схема БД',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegisterListRunType_id',
					'label' => 'Тип запуска',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListLog_AllCount',
					'label' => 'Количество записей в файле',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListLog_UploadCount',
					'label' => 'Количество загруженных записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterListResultType_id',
					'label' => 'Результат загрузки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegisterList_id',
					'label' => 'Загружаемый регистр/справочник',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
			),
			'delete' => array(
				array(
					'field' => 'RegisterListLog_id',
					'label' => 'Строка лога с запуском загрузки',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('RegisterListLog_model', 'RegisterListLog_model');
	}

	/**
	 *
	 * @return type 
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['RegisterListLog_id'])) {
				$this->RegisterListLog_model->setRegisterListLog_id($data['RegisterListLog_id']);
			}
			if (isset($data['RegisterListLog_begDT'])) {
				$this->RegisterListLog_model->setRegisterListLog_begDT($data['RegisterListLog_begDT']);
			}
			if (isset($data['RegisterListLog_endDT'])) {
				$this->RegisterListLog_model->setRegisterListLog_endDT($data['RegisterListLog_endDT']);
			}
			if (isset($data['RegisterListRunType_id'])) {
				$this->RegisterListLog_model->setRegisterListRunType_id($data['RegisterListRunType_id']);
			}
			if (isset($data['RegisterListLog_AllCount'])) {
				$this->RegisterListLog_model->setRegisterListLog_AllCount($data['RegisterListLog_AllCount']);
			}
			if (isset($data['RegisterListLog_UploadCount'])) {
				$this->RegisterListLog_model->setRegisterListLog_UploadCount($data['RegisterListLog_UploadCount']);
			}
			if (isset($data['RegisterListResultType_id'])) {
				$this->RegisterListLog_model->setRegisterListResultType_id($data['RegisterListResultType_id']);
			}
			if (isset($data['RegisterList_id'])) {
				$this->RegisterListLog_model->setRegisterList_id($data['RegisterList_id']);
			}
			$response = $this->RegisterListLog_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Таблица логов запуска загрузок')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->RegisterListLog_model->setRegisterListLog_id($data['RegisterListLog_id']);
			$response = $this->RegisterListLog_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->RegisterListLog_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields('d.m.Y H:i:s')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->RegisterListLog_model->setRegisterListLog_id($data['RegisterListLog_id']);
			$response = $this->RegisterListLog_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}