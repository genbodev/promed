<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс для справок о стоимости лечения
 *
 * @package				CostPrint
 * @copyright			Copyright (c) 2014 Swan Ltd.
 * @author				Dmitriy Vlasenko
 * @link				http://swan.perm.ru/PromedWeb
 */
class CostPrint extends swController
{

	public $inputRules = array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('CostPrint_model', 'dbmodel');

		$this->inputRules = array(
			'getCostPrintData' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Случай',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CmpCallCard_id',
					'label' => 'Карта СМП',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveCostPrint' => array(
				array(
					'field' => 'CostPrint_setDT',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Случай',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CmpCallCard_id',
					'label' => 'Карта СМП',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_IsPred',
					'label' => 'Выдана представителю',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Person_pid',
					'label' => 'Идентификатор представителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CostPrint_IsNoPrint',
					'label' => 'Отказ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CostPrint_begDate',
					'label' => 'Период от',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'CostPrint_endDate',
					'label' => 'Период до',
					'rules' => '',
					'type' => 'date'
				)
			),
			'setCostParameter' => array(
				array(
					'field' => 'object',
					'label' => 'Системное имя объекта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'id',
					'label' => 'Идентификатор объекта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'param_name',
					'label' => 'Системное имя параметра',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'param_value',
					'label' => 'Значение параметра',
					'rules' => 'required',
					'type' => 'string'
				)
			)
		);
	}

	/**
	 * Установка параметра справки о стоимости
	 */
	function setCostParameter()
	{
		$data = $this->ProcessInputData('setCostParameter', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->setCostParameter($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных для справки
	 */
	function getCostPrintData()
	{
		$data = $this->ProcessInputData('getCostPrintData', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getCostPrintData($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение факта печати справки
	 */
	function saveCostPrint()
	{
		$data = $this->ProcessInputData('saveCostPrint', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveCostPrint($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
}