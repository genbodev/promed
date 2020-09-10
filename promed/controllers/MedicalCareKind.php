<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Вид медицинской помощи
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property MedicalCareKind_model MedicalCareKind_model
 */

class MedicalCareKind extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'MedicalCareKind_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareKind_Code',
					'label' => 'код',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareKind_Name',
					'label' => 'наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'MedicalCareKind_begDate',
					'label' => 'MedicalCareKind_begDate',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'MedicalCareKind_endDate',
					'label' => 'MedicalCareKind_endDate',
					'rules' => '',
					'type' => 'datetime'
				),
			),
			'load' => array(
				array(
					'field' => 'MedicalCareKind_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'MedicalCareKind_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareKind_Code',
					'label' => 'код',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareKind_Name',
					'label' => 'наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedicalCareKind_begDate',
					'label' => 'MedicalCareKind_begDate',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'MedicalCareKind_endDate',
					'label' => 'MedicalCareKind_endDate',
					'rules' => '',
					'type' => 'datetime'
				),
			),
			'delete' => array(
				array(
					'field' => 'MedicalCareKind_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('MedicalCareKind_model', 'MedicalCareKind_model');
	}

	/**
	 * Получение списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MedicalCareKind_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка видов медицинской помощи из федерального справочника
	 */
	function loadFedMedicalCareKindList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MedicalCareKind_model->loadFedMedicalCareKindList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка видов медицинской помощи
	 */
	function loadMedicalCareKindList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MedicalCareKind_model->loadMedicalCareKindList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка форм оказания мед. помощи
	 */
	function loadMedicalCareFormTypeList() {
		$result = $this->MedicalCareKind_model->loadMedicalCareFormTypeList();
		if (!is_array($result)) {
			return false;
		}
		$this->ProcessModelList($result, true, true)->ReturnData();
		return true;
	}
}