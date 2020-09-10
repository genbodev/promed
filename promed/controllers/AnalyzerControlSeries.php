<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AnalyzerControlSeries - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 *
 * @property AnalyzerControlSeries_model dbmodel
 */

class AnalyzerControlSeries extends swController {
	protected $inputRules = array(
		'delete' => array(
			array(
				'field' => 'AnalyzerControlSeries_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'AnalyzerTest_id',
				'label' => 'Идентификатор теста',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AnalyzerControlSeries_regDateRange',
				'label' => 'Период',
				'rules' => '',
				'type' => 'string'
			),
		),
		'load' => array(
			array(
				'field' => 'AnalyzerControlSeries_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'AnalyzerControlSeries_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AnalyzerTest_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AnalyzerControlSeries_regDT',
				'label' => 'Дата регистрации результата',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'AnalyzerControlSeries_Value',
				'label' => 'Результат',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'AnalyzerControlSeries_IsControlPassed',
				'label' => 'Контроль пройден',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AnalyzerControlSeries_Comment',
				'label' => 'Примечание',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Сотрудник',
				'rules' => 'required',
				'type' => 'id'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('AnalyzerControlSeries_model', 'dbmodel');
		}
	}

	/**
	 * Удаление
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->DELETE('AnalyzerControlSeries', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->delete($data);
			$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
		}
	}

	/**
	 * Возвращает список
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) return;

		if (isset($data['AnalyzerControlSeries_regDateRange'])) {
			$data['AnalyzerControlSeries_regDateRange'] = str_replace(' ', '', $data['AnalyzerControlSeries_regDateRange']);
		}

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('AnalyzerControlSeries/list', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Возвращает
	 */
	function load()
	{
		$data = $this->ProcessInputData('load', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('AnalyzerControlSeries', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->load($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Сохранение
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			if (empty($data['AnalyzerControlSeries_id'])) {
				$response = $this->lis->POST('AnalyzerControlSeries', $data);
			} else {
				$response = $this->lis->PUT('AnalyzerControlSeries', $data);
			}

			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->save($data);
			$this->ProcessModelSave($response)->ReturnData();
		}
	}
}