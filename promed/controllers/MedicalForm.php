<?php
	/**
	 * Created by PhpStorm.
	 * User: m.volkoderov
	 * Date: 26.11.2019
	 * Time: 13:08
	 */

class MedicalForm extends swController{
	/**
	 * @desc
	 */
	public $inputRules = array(
		'getMedicalForms' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveMedicalForm' => array(
			array(
				'field' => 'PersonAgeGroup_id',
				'label' => 'Возраст',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedicalForm_id',
				'label' => 'Ид формы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadMedicalFormData'=>array(
			array(
				'field' => 'MedicalFormPerson_id',
				'label' => 'Ид опроса',
				'rules' => '',
				'type' => 'id',
			),
		),
		'saveMedicalFormData' => array(
			array(
				'field' => 'MedicalFormData',
				'label' => 'Данные',
				'rules' => '',
				'type' => 'json_array',
			),
			array(
				'field' => 'MedicalForm_id',
				'label' => 'Ид формы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Ид человека',
				'rules' => '',
				'type' => 'int',
			)
		),
		'updateMedicalForm'=>array(
			array(
				'field' => 'MedicalForm_id',
				'label' => 'Ид анкеты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalForm_Name',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedicalFormTree',
				'label' => 'Все дерево',
				'rules' => '',
				'type' => 'json_array',
			),
			array(
				'field' => 'MedicalForm_Description',
				'label' => 'Описание',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getMedicalForm' =>array(
			array(
				'field' => 'MedicalForm_id',
				'label' => 'Ид анкеты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadMedicalForm'=>array(
			array(
				'field' => 'MedicalForm_id',
				'label' => 'Ид анкеты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'createMedicalFormQuestion'=>array(
			array(
				'field' => 'MedicalForm_id',
				'label' => 'Ид анкеты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMedicalFormActualList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
	);

	/**
	 * @desc
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("MedicalForm_model", "dbmodel");
	}

	/**
	 *
	 * @desc
	 */
	function getMedicalForms(){
		$data = $this->ProcessInputData('getMedicalForms', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getMedicalForms($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	function getMedicalForm(){
		$data = $this->ProcessInputData('getMedicalForm', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getMedicalForm($data);
		
		$this->ReturnData(array('success' => true, 'Error_Msg' => '', 'data' => $response));
		return true;
	}
	/**
	 * @desc
	 */
	function saveMedicalForm(){
		$data = $this->ProcessInputData('saveMedicalForm', true);
		if ($data === false) return false;

		$response = $this->dbmodel->saveMedicalForm($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;

	}

	/**
	 * @desc
	 */
	function updateMedicalForm(){
		$data = $this->ProcessInputData('updateMedicalForm', true);
		if ($data === false) return false;

		$response = $this->dbmodel->updateMedicalForm($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * @desc
	 */
	function loadMedicalForm(){
		$data = $this->ProcessInputData('loadMedicalForm', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadMedicalForm($data);

		$tree = $this->dbmodel->buildTree($response);

		$this->ProcessModelList($tree, true,true)->ReturnData();
		return true;
	}


	/**
	 * @desc
	 */
	function loadMedicalFormData(){
		$data = $this->ProcessInputData('loadMedicalFormData', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadMedicalFormData($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		return true;
	}

	/**
	 * @desc
	 */
	function saveMedicalFormData(){
		$data = $this->ProcessInputData('saveMedicalFormData', true);
		if ($data === false) return false;

		$response = $this->dbmodel->saveMedicalFormData($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получить список актуальных анкет по пациенту
	 */
	function getMedicalFormActualList(){
		$data = $this->ProcessInputData('getMedicalFormActualList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getMedicalFormActualList($data);
		
		$this->ReturnData(array('success' => true, 'Error_Msg' => '', 'list' => $response));
		return true;
	}
}