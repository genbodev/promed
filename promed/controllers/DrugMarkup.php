<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Величины надбавок
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Model Generator
 * @version      01.2014
 * @property DrugMarkup_model DrugMarkup_model
 */

class DrugMarkup extends swController {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'DrugMarkup_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugMarkup_begDT',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DrugMarkup_endDT',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DrugMarkup_MinPrice',
					'label' => 'Минимальная отпускная цена',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_MaxPrice',
					'label' => 'Максимальная отпускная цена',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_Wholesale',
					'label' => 'Предельная оптовая надбавка (%)',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_Retail',
					'label' => 'Предельной розничная надбавка (%)',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_IsNarkoDrug',
					'label' => 'признак наркотического препарата',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Drugmarkup_Delivery',
					'label' => 'зона доставки',
					'rules' => '',
					'type' => 'string'
				)
			),
			'load' => array(
				array(
					'field' => 'DrugMarkup_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'DrugMarkup_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugMarkup_begDT',
					'label' => 'дата начала действия',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'DrugMarkup_endDT',
					'label' => 'дата окончания действия',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'DrugMarkup_MinPrice',
					'label' => 'минимальная отпускная цена',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_MaxPrice',
					'label' => 'максимальная отпускная цена',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_Wholesale',
					'label' => 'размер предельной оптовой надбавки в %',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_Retail',
					'label' => 'размер предельной розничной надбавки в %',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugMarkup_IsNarkoDrug',
					'label' => 'признак наркотического препарата',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Drugmarkup_Delivery',
					'label' => 'зона доставки',
					'rules' => '',
					'type' => 'string'
				)
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
		);
		$this->load->database();
		$this->load->model('DrugMarkup_model', 'DrugMarkup_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['DrugMarkup_id'])) {
				$this->DrugMarkup_model->setDrugMarkup_id($data['DrugMarkup_id']);
			}
			if (isset($data['DrugMarkup_begDT'])) {
				$this->DrugMarkup_model->setDrugMarkup_begDT($data['DrugMarkup_begDT']);
			}
			if (isset($data['DrugMarkup_endDT'])) {
				$this->DrugMarkup_model->setDrugMarkup_endDT($data['DrugMarkup_endDT']);
			}
			if (isset($data['DrugMarkup_MinPrice'])) {
				$this->DrugMarkup_model->setDrugMarkup_MinPrice($data['DrugMarkup_MinPrice']);
			}
			if (isset($data['DrugMarkup_MaxPrice'])) {
				$this->DrugMarkup_model->setDrugMarkup_MaxPrice($data['DrugMarkup_MaxPrice']);
			}
			if (isset($data['DrugMarkup_Wholesale'])) {
				$this->DrugMarkup_model->setDrugMarkup_Wholesale($data['DrugMarkup_Wholesale']);
			}
			if (isset($data['DrugMarkup_Retail'])) {
				$this->DrugMarkup_model->setDrugMarkup_Retail($data['DrugMarkup_Retail']);
			}
			if (isset($data['DrugMarkup_IsNarkoDrug'])) {
				$this->DrugMarkup_model->setDrugMarkup_IsNarkoDrug($data['DrugMarkup_IsNarkoDrug']);
			}
			if (isset($data['Drugmarkup_Delivery'])) {
				$this->DrugMarkup_model->setDrugmarkup_Delivery($data['Drugmarkup_Delivery']);
			}
			$response = $this->DrugMarkup_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Величины надбавок')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->DrugMarkup_model->setDrugMarkup_id($data['DrugMarkup_id']);
			$response = $this->DrugMarkup_model->load();
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
			$response = $this->DrugMarkup_model->loadList($filter);
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
			$this->DrugMarkup_model->setDrugMarkup_id($data['id']);
			$response = $this->DrugMarkup_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}