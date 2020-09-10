<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'controllers/Privilege.php');

class Samara_Privilege extends Privilege {
	/**
	 * Samara_Privilege
	 */
    function __construct()
	{
		parent::__construct();
              
        $this->inputRules['savePrivilegeSamara'] = array(
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'id'
			),       
			array(
				'field' => 'PersonPrivilege_Serie',
				'label' => 'Серия',
				'rules' => '',
				'type' => 'string'
			),   
			array(
				'field' => 'PersonPrivilege_Number',
				'label' => 'Номер',
				'rules' => '',
				'type' => 'string'
			),                   
			array(
				'field' => 'PersonPrivilege_IssuedBy',
				'label' => 'Кем выдан',
				'rules' => '',
				'type' => 'string'
			),   
			array(
				'field' => 'PersonPrivilege_Group',
				'label' => 'Группа',
				'rules' => '',
				'type' => 'string'
			),                  
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPrivilege_id',
				'label' => 'Идентификатор льготы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPrivilege_begDate',
				'label' => 'Дата начала действия льготы',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonPrivilege_endDate',
				'label' => 'Дата окончания действия льготы',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Категория льготы',
				'rules' => 'required',
				'type' => 'id'
			)
		);
        
        
        $this->inputRules['loadPrivilegeSamara'] = array(
			array(
				'field' => 'PersonPrivilege_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
		);
            
        $this->inputRules['deletePrivilegeSamara'] = array(
        	array(
        		'field' => 'PersonPrivilege_id',
        		'label' => 'Идентификатор льготы',
        		'rules' => 'required',
        		'type' => 'id'
        	)
        );
        
        $this->load->database();
        $this->load->model('Samara_Privilege_model', 'samara_dbmodel');            
    }
	/**
	 * savePrivilegeSamara
	 */
	function savePrivilegeSamara()
	{
		$data = $this->ProcessInputData('savePrivilegeSamara', true, false, true);
		if ( $data === false ) { return false; }

		$result = $this->samara_dbmodel->save($data)->result('array');
		$this->ProcessModelSave($result)->ReturnData();
	}
	/**
	 * deletePrivilegeSamara
	 */
	function deletePrivilegeSamara()
	{
		$data = $this->ProcessInputData('deletePrivilegeSamara');
		if ( $data === false ) { return false; }
	
		$result = $this->samara_dbmodel->delete($data)->result('array');
		$this->ProcessModelSave($result, true, 'При удалении Льготы возникли ошибки')->ReturnData();
	}
	/**
	 * loadPrivilegeSamara
	 */
	function loadPrivilegeSamara()
	{
		$data = $this->ProcessInputData('loadPrivilegeSamara');
		if ( $data === false ) { return false; }
	
		$response = $this->samara_dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}    	        
	/**
	 * loadPrivilegeTypes
	 */
	function loadPrivilegeTypes() {		
		$response = $this->samara_dbmodel->getPrivilegeTypes();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * loadReceptFinances
	 */
	function loadReceptFinances() {
		$response = $this->samara_dbmodel->getReceptFinances();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * loadPersonPrivilegesList
	 */
	function loadPersonPrivilegesList() {
		$response = $this->samara_dbmodel->getPersonPrivilegesList($_POST['person_id']);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
    	
}