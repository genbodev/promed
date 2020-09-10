<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Ограничения
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version
 * @property EvnPrescrLimit_model EvnPrescrLimit_model
 */

class EvnPrescrLimit extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'EvnPrescrLimit_id',
					'label' => 'EvnPrescrLimit_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LimitType_id',
					'label' => 'Тип ограничения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrLimit_Values',
					'label' => 'Значение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'Назначение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrLimit_ValuesNum',
					'label' => 'Значение',
					'rules' => '',
					'type' => 'int'
				)		
			),
			'load' => array(
				array(
					'field' => 'EvnPrescrLimit_id',
					'label' => 'EvnPrescrLimit_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadList' => array(
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'Назначение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkLimits' => array(
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'Назначение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'EvnPrescrLimit_id',
					'label' => 'EvnPrescrLimit_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadGrid' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'EvnDirection_id',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
		$this->load->database();
		$this->load->model('EvnPrescrLimit_model', 'EvnPrescrLimit_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		$response = $this->EvnPrescrLimit_model->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Соответствия конкретных ответов конкретному качественному тесту')->ReturnData();
		return true;
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
			
		$response = $this->EvnPrescrLimit_model->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }

		$response = $this->EvnPrescrLimit_model->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Проверка есть ли лимиты
	 */
	function checkLimits() {
		$data = $this->ProcessInputData('checkLimits', true);
		if ($data === false) { return false; }

		$response = $this->EvnPrescrLimit_model->checkLimits($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data === false) { return false; }
		
		$response = $this->EvnPrescrLimit_model->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка в арме лаборанта
	 * @return bool
	 */
	function loadGrid() {
		$data = $this->ProcessInputData('loadGrid', true, true);
		if ($data === false) { return false; }

		$response = $this->EvnPrescrLimit_model->loadGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}