<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Соответствия конкретных ответов конкретному качественному тесту
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2010-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 * @property Organization_model Organization_model
 */

class Organization extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'Organization_id',
					'label' => 'Организация в ЛИС',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Тип редактирования',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Organization_Code',
					'label' => 'Код',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Organization_Name',
					'label' => 'Наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Org_id',
					'label' => 'МО',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'load' => array(
				array(
					'field' => 'Organization_id',
					'label' => 'Organization_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadList' => array(
			),
			'loadLisOrganizationList' => array(
				array(
					'field' => 'Organization_id',
					'label' => 'Organization_id',
					'rules' => '',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'Organization_id',
					'label' => 'Organization_id',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
		$this->load->database();
		$this->load->model('Organization_model', 'Organization_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		$response = $this->Organization_model->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Соответствия конкретных ответов конкретному качественному тесту')->ReturnData();
		return true;
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
			
		$response = $this->Organization_model->load($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }

		$response = $this->Organization_model->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Загрузка справочника организаций в ЛИС
	 */
	function loadLisOrganizationList() {
		$data = $this->ProcessInputData('loadLisOrganizationList', true);
		if ($data === false) { return false; }

		$response = $this->Organization_model->loadLisOrganizationList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data === false) { return false; }
		
		$response = $this->Organization_model->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
		return true;
	}
}