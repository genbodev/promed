<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Единицы измерений теста
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 * @property QuantitativeTestUnit_model QuantitativeTestUnit_model
 */

class QuantitativeTestUnit extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'loadCoeff' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Тест анализатора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Unit_id',
					'label' => 'Единица измерения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'save' => array(
				array(
					'field' => 'QuantitativeTestUnit_id',
					'label' => 'QuantitativeTestUnit_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Unit_id',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'QuantitativeTestUnit_IsBase',
					'label' => 'Базовая',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'QuantitativeTestUnit_CoeffEnum',
					'label' => 'Коэффициент пересчета',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Тест анализатора',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'load' => array(
				array(
					'field' => 'QuantitativeTestUnit_id',
					'label' => 'QuantitativeTestUnit_id',
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
			'delete' => array(
				array(
					'field' => 'QuantitativeTestUnit_id',
					'label' => 'QuantitativeTestUnit_id',
					'rules' => 'required',
					'type' => 'int'
				)
			)
		);

		$this->load->database();
		$this->load->model('QuantitativeTestUnit_model', 'dbmodel');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Соответствия конкретных ответов конкретному качественному тесту')->ReturnData();
	}

	/**
	 * Загрузка единицы измерения
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) return;

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 * Загрузка коэффициента пересчёта
	 */
	function loadCoeff() {
		$data = $this->ProcessInputData('loadCoeff', true);
		if ($data === false) return;

		$response = $this->dbmodel->loadCoeff($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) return;

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) return;

		$response = $this->dbmodel->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
}