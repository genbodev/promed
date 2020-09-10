<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DrugDocument - контроллер для работы со справочниками для документов по медикаментам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.12.2013
 *
 * @property DrugDocument_model dbmodel
 */

class DrugDocument extends swController {

	public $inputRules = array(
		'loadDrugDocumentClassList' => array(
			array(
				'field' => 'DrugDocumentClass_id',
				'label' => 'Идентификатор вида заявки на медикаменты',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadDrugDocumentStatusList' => array(
			array(
				'field' => 'DrugDocumentStatus_id',
				'label' => 'Идентификатор статусов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugDocumentType_id',
				'label' => 'Идентификатор типа документа',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadDrugDocumentClassGrid' => array(
			array(
				'field' => 'DrugDocumentClass_id',
				'label' => 'Идентификатор вида заявки на медикаменты',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadDrugDocumentStatusGrid' => array(
			array(
				'field' => 'DrugDocumentStatus_id',
				'label' => 'Идентификатор статуса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugDocumentType_id',
				'label' => 'Идентификатор типа документа',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadDrugDocumentClassForm' => array(
			array(
				'field' => 'DrugDocumentClass_id',
				'label' => 'Идентификатор вида заявки на медикаменты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadDrugDocumentStatusForm' => array(
			array(
				'field' => 'DrugDocumentStatus_id',
				'label' => 'Идентификатор статуса',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveDrugDocumentClass' => array(
			array(
				'field' => 'DrugDocumentClass_id',
				'label' => 'Идентификатор вида заявки на медикаменты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugDocumentClass_Code',
				'label' => 'Код вида заявки на медикаменты',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'DrugDocumentClass_Name',
				'label' => 'Наименование вида заявки на медикаменты',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugDocumentClass_Nick',
				'label' => 'Краткое наименование вида заявки на медикаменты',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveDrugDocumentStatus' => array(
			array(
				'field' => 'DrugDocumentStatus_id',
				'label' => 'Идентификатор статуса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugDocumentStatus_Code',
				'label' => 'Код статуса',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'DrugDocumentStatus_Name',
				'label' => 'Наименование статуса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugDocumentType_id',
				'label' => 'Идентификатор типа документа',
				'rules' => '',
				'type' => 'string'
			)
		),
		'deleteDrugDocumentClass' => array(
			array(
				'field' => 'DrugDocumentClass_id',
				'label' => 'Идентификатор вида заявки на медикаменты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteDrugDocumentStatus' => array(
			array(
				'field' => 'DrugDocumentStatus_id',
				'label' => 'Идентификатор статуса',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('DrugDocument_model', 'dbmodel');
	}

	/**
	 * Получение списка видов заявки на медикаменты
	 */
	function loadDrugDocumentClassList() {
		$data = $this->ProcessInputData('loadDrugDocumentClassList',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->loadDrugDocumentClassList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка статусов заявки на медикаменты
	 */
	function loadDrugDocumentStatusList() {
		$data = $this->ProcessInputData('loadDrugDocumentStatusList',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->loadDrugDocumentStatusList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка видов заявки на медикаменты
	 */
	function loadDrugDocumentClassGrid() {
		$data = $this->ProcessInputData('loadDrugDocumentClassGrid',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->loadDrugDocumentClassGrid($data);
		$this->ProcessModelMultiList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка статусов заявки на медикаменты
	 */
	function loadDrugDocumentStatusGrid() {
		$data = $this->ProcessInputData('loadDrugDocumentStatusGrid',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->loadDrugDocumentStatusGrid($data);
		$this->ProcessModelMultiList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает данные для редактирования вида заявки на медикаменты
	 */
	function loadDrugDocumentClassForm() {
		$data = $this->ProcessInputData('loadDrugDocumentClassForm',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->loadDrugDocumentClassForm($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает данные для редактирования статуса заявки на медикаменты
	 */
	function loadDrugDocumentStatusForm() {
		$data = $this->ProcessInputData('loadDrugDocumentStatusForm',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->loadDrugDocumentStatusForm($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение вида заявки на медикаменты
	 */
	function saveDrugDocumentClass() {
		$data = $this->ProcessInputData('saveDrugDocumentClass',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->saveDrugDocumentClass($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение статуса заявки на медикаменты
	 */
	function saveDrugDocumentStatus() {
		$data = $this->ProcessInputData('saveDrugDocumentStatus',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->saveDrugDocumentStatus($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Удаление вида заявки на медикаменты
	 */
	function deleteDrugDocumentClass() {
		$data = $this->ProcessInputData('deleteDrugDocumentClass',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->deleteDrugDocumentClass($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Удаление статуса заявки на медикаменты
	 */
	function deleteDrugDocumentStatus() {
		$data = $this->ProcessInputData('deleteDrugDocumentStatus',true,true);
		if ($data === false) {return false;}

		$this->load->model("DrugDocument_model", "dbmodel");

		$response = $this->dbmodel->deleteDrugDocumentStatus($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}
}