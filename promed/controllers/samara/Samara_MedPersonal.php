<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/MedPersonal.php');

class Samara_MedPersonal extends MedPersonal {
  
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('Samara_MedPersonal_model', 'samara_dbmodel');
		$this->load->helper('Text');
		
		$this->inputRules['getMedPersonalComboByLpu'] = array(
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'id'
			),						
			array(
				'field' => 'Lpu_did',
				'label' => 'Идентификатор направившего ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Строка контекстного поиска',
				'rules' => '',
				'type'  => 'string'
			)
		);
		
	}    

	/**
	 * Получение списка медицинского персонала. Для гридов и комбобоксов
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	//GolovinAV
	public function getMedPersonalComboByLpu() {
		$data = $this->ProcessInputData('getMedPersonalComboByLpu', true);	
		$response = $this->samara_dbmodel->getMedPersonalComboByLpu($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
