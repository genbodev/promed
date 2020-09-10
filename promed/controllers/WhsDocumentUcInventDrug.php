<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Специфика инвентаризационной ведомости
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Model Generator
 * @version      13.03.2017
 *
 * @property WhsDocumentUcInventDrug_model WhsDocumentUcInventDrug_model
 */

class WhsDocumentUcInventDrug extends swController {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array('field' => 'WhsDocumentUcInventDrug_id', 'label' => 'Строка инвентаризационной ведомости', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Инвентаризационная ведомость', 'rules' => 'required', 'type' => 'id'),
				//array('field' => 'Org_id', 'label' => 'Организация', 'rules' => 'required', 'type' => 'id'),
				//array('field' => 'Storage_id', 'label' => 'Склад', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'StorageZone_id', 'label' => 'Место хранения', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentUcInventDrug_FactKolvo', 'label' => 'Кол-во', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'WhsDocumentUcInventDrug_Cost', 'label' => 'Цена', 'rules' => 'required', 'type' => 'float'),
				//array('field' => 'WhsDocumentUcInventDrug_Sum', 'label' => 'Сумма', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PrepSeries_Ser', 'label' => 'Серия', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'PrepSeries_GodnDate', 'label' => 'Срок годности', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'PersonWork_id', 'label' => 'Исполитель', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'GoodsUnit_id', 'label' => 'Ед. учета', 'rules' => '', 'type' => 'id')
			),
			'deleteList' => array(
				array('field' => 'WhsDocumentUcInventDrug_List', 'label' => 'Список медикаментов инв. ведомости', 'rules' => 'required', 'type' => 'string'),
			),
			'load' => array(
				array('field' => 'WhsDocumentUcInventDrug_id', 'label' => 'Строка инвентаризационной ведомости', 'rules' => 'required', 'type' => 'id'),
			)
		);
		$this->load->database();
		$this->load->model('WhsDocumentUcInventDrug_model', 'WhsDocumentUcInventDrug_model');
	}

	/**
	 * Ручное добавление медикамента в инветаризационной ведомости (для излишков)
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			$response = $this->WhsDocumentUcInventDrug_model->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Специфика инвентаризационной ведомости')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление списка медикаментов из инвентаризационной ведомости
	 */
	function deleteList() {
		$data = $this->ProcessInputData('deleteList', true);
		if ($data){
			$response = $this->WhsDocumentUcInventDrug_model->deleteList($data);
			$this->ProcessModelSave($response, true, 'Ошибка при удалении Специфика инвентаризационной ведомости')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для редактирования медикаментов из инв. ведомости
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$response = $this->WhsDocumentUcInventDrug_model->load($data);
			$this->ProcessModelList($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}