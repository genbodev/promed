<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/MedService.php');

class Samara_MedService extends MedService {
	/**
	 * __construct
	 */
	function __construct() {
		parent::__construct();


 		$this->load->database();
 		$this->load->model('Samara_MedService_model', 'Samara_MedService_model');

		$this->inputRules['getUslugaComplexSelectList'][] = array('field' => 'MedService_id','label' => 'Фильтр по типу услуги','rules' => '','type' => 'int');

		$this->inputRules['getLpuMedServiceTypes'][] = array('field' => 'Lpu_id', 'label' => 'идентификатор', 'rules' => '', 'type' => 'int' );
		$this->inputRules['getLpuMedServiceTypes'][] = array('field' => 'MedServiceType_id', 'label' => 'идентификатор', 'rules' => '', 'type' => 'int' );
	}    
	/**
	 * getUslugaComplexSelectList
	 */
	function getUslugaComplexSelectList()
	{
		$data = $this->ProcessInputData('getUslugaComplexSelectList', true);
		if ($data) {
			$response = $this->Samara_MedService_model->getUslugaComplexSelectList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * getLpuMedServiceTypes
	 */
	function getLpuMedServiceTypes(){
		$data = $this->ProcessInputData('getLpuMedServiceTypes', true);
		if ($data)
		{
			$response = $this->Samara_MedService_model->getLpuMedServiceTypes($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
}