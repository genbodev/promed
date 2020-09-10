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
 * @property Limit_model Limit_model
 */

class Limit extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'Limit_id',
					'label' => 'Limit_id',
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
					'field' => 'Limit_Values',
					'label' => 'Значение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'Референсное значение теста',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Limit_ValuesFrom',
					'label' => 'От',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Limit_ValuesTo',
					'label' => 'До',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Limit_IsActiv',
					'label' => 'Актив',
					'rules' => '',
					'type' => 'id'
				)				
			),
			'load' => array(
				array(
					'field' => 'Limit_id',
					'label' => 'Limit_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'Референсное значение теста',
					'rules' => '',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'Limit_id',
					'label' => 'Limit_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
		$this->load->database();
		$this->load->model('Limit_model', 'Limit_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		$response = $this->Limit_model->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Соответствия конкретных ответов конкретному качественному тесту')->ReturnData();
		return true;
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
			
		$response = $this->Limit_model->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }

		$response = $this->Limit_model->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data === false) { return false; }
		
		$response = $this->Limit_model->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
		return true;
	}
}