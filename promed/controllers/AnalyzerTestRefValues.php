<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Референсные значения тестов
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 * @property AnalyzerTestRefValues_model AnalyzerTestRefValues_model
 */

class AnalyzerTestRefValues extends swController
{
	private $moduleMethods = [
		'loadRefValuesList'
	];

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'Идентификатор референсного значения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Идентификатор теста анализатора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RefValues_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Unit_id',
					'label' => 'Единица измерения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RefValues_LowerLimit',
					'label' => 'Нижнее нормальное',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RefValues_UpperLimit',
					'label' => 'Верхнее нормальное',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RefValues_BotCritValue',
					'label' => 'Нижнее критическое',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RefValues_TopCritValue',
					'label' => 'Верхнее критическое',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RefValues_Description',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LimitData',
					'label' => 'Ограничения',
					'rules' => '',
					'type' => 'string'
				)
			),
			'load' => array(
				array(
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'AnalyzerTestRefValues_id',
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
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'AnalyzerTestRefValues_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadRefValuesList' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id',
				),
				array(
					'field' => 'UslugaComplexTarget_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id',
				),
				array(
					'field' => 'UslugaComplexTest_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id',
				),
				array(
					'field' => 'EvnLabSample_setDT',
					'label' => 'EvnLabSample_setDT',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
		$this->init();
	}

	private function init() {
		$method = $this->router->fetch_method();

		if ($this->usePostgreLis && in_array($method, $this->moduleMethods)) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('AnalyzerTestRefValues_model', 'AnalyzerTestRefValues_model');
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			$response = $this->AnalyzerTestRefValues_model->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Соответствия конкретных ответов конкретному качественному тесту')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка единицы измерения
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$response = $this->AnalyzerTestRefValues_model->load($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->AnalyzerTestRefValues_model->loadList($filter);
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
			$response = $this->AnalyzerTestRefValues_model->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка референсных значений
	 */
	function loadRefValuesList()
	{
		$data = $this->ProcessInputData('loadRefValuesList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('AnalyzerTestRefValues/RefValuesList', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->AnalyzerTestRefValues_model->loadRefValuesList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}
	}
}