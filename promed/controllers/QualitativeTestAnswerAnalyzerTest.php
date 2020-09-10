<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Соответствия конкретных ответов конкретному качественному тесту
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property QualitativeTestAnswerAnalyzerTest_model QualitativeTestAnswerAnalyzerTest_model
 */

class QualitativeTestAnswerAnalyzerTest extends swController
{
	private $moduleMethods = [
		'loadList',
		'load',
		'save',
		'delete'
	];

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();

		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_id',
					'label' => 'QualitativeTestAnswerAnalyzerTest_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_Answer',
					'label' => 'Вариант ответа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Тесты анализаторов',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_SortCode',
					'label' => 'Приоритет отображения ответов',
					'rules' => '',
					'type' => 'int'
				)
			),
			'load' => array(
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_id',
					'label' => 'QualitativeTestAnswerAnalyzerTest_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadList' => array(
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_id',
					'label' => 'QualitativeTestAnswerAnalyzerTest_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_Answer',
					'label' => 'Вариант ответа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Тесты анализаторов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'Тесты анализаторов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_id',
					'label' => 'Тест',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_SortCode',
					'label' => 'Приоритет отображения ответов',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delete' => array(
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_id',
					'label' => 'QualitativeTestAnswerAnalyzerTest_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
		$this->init();
	}

	private function init() {
		$method = $this->router->fetch_method();

		if ($this->usePostgreLis && in_array($method, $this->moduleMethods)) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('QualitativeTestAnswerAnalyzerTest_model', 'dbmodel');
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;
		
		if ($this->usePostgreLis) {
			$response = $this->lis->POST('QualitativeTestAnswerAnalyzerTest', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Соответствия конкретных ответов конкретному качественному тесту')->ReturnData();
		} 
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('QualitativeTestAnswerAnalyzerTest', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->load($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('QualitativeTestAnswerAnalyzerTest/list', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
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