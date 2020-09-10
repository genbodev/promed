<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Наборы референсных значений тестов
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 * @property RefValuesSet_model RefValuesSet_model
 */

class RefValuesSet extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'saveRefValuesSet' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Идентификатор теста анализатора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RefValuesSet_Name',
					'label' => 'Наименование',
					'rules' => '',
					'type' => 'string'
				)
			),
			'resaveRefValuesSet' => array(
				array(
					'field' => 'RefValuesSet_id',
					'label' => 'Идентификатор набора',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRefValuesSet' => array(
				array(
					'field' => 'RefValuesSet_id',
					'label' => 'RefValuesSet_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Тест анализатора',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRefValuesSetRefValues' => array(
				array(
					'field' => 'RefValuesSet_id',
					'label' => 'Набор референсных значений',
					'rules' => '',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'RefValuesSet_id',
					'label' => 'RefValuesSet_id',
					'rules' => 'required',
					'type' => 'int'
				)
			)
		);
		$this->load->database();
		$this->load->model('RefValuesSet_model', 'RefValuesSet_model');
	}

	/**
	 * Сохранение
	 */
	function saveRefValuesSet() {
		$data = $this->ProcessInputData('saveRefValuesSet', true);
		if ($data === false) { return false; }

		$response = $this->RefValuesSet_model->saveRefValuesSet($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении набора референсных значений')->ReturnData();
		
		return true;
	}

	/**
	 * Пересохранение
	 */
	function resaveRefValuesSet() {
		$data = $this->ProcessInputData('resaveRefValuesSet', true);
		if ($data === false) { return false; }

		$response = $this->RefValuesSet_model->resaveRefValuesSet($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении набора референсных значений')->ReturnData();
		
		return true;
	}

	/**
	 * Загрузка единицы измерения
	 */
	function loadRefValuesSet() {
		$data = $this->ProcessInputData('loadRefValuesSet', true);
		if ($data === false) { return false; }

		$response = $this->RefValuesSet_model->loadRefValuesSet($data);
		$this->ProcessModelSave($response, true, 'Ошибка при загрузке набора референсных значений')->ReturnData();
		
		return true;
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->RefValuesSet_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Загрузка списка
	 */
	function loadRefValuesSetRefValues() {
		$data = $this->ProcessInputData('loadRefValuesSetRefValues', true);
		if ($data) {
			$filter = $data;
			$response = $this->RefValuesSet_model->loadRefValuesSetRefValues($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$response = $this->RefValuesSet_model->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}