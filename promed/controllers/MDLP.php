<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class MDLP extends swController {

    /**
	 * Интеграция с Регистратором Выбытия
	 */
  
	 public $inputRules = array();
	 var $model_name = "MDLP_model";

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model($this->model_name, "dbmodel");
		
		$this->inputRules = array(
			'GetInformationRv' => array(
				array(
					'field' => 'Org_id',
					'label' => 'ИД организации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'rv_ip',
					'label' => 'rv_ip',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'rv_port',
					'label' => 'rv_port',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'rv_login',
					'label' => 'rv_login',
					'rules' => '',
					'type' => 'string'
					),
		array(
					'field' => 'rv_pass',
					'label' => 'rv_pass',
					'rules' => '',
					'type' => 'string'
				)
			),
			'QueueUp' => array(
				array(
					'field' => 'rvRequestId',
					'label' => 'ИД задания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'type',
					'label' => 'тип задания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'marksJSON',
					'label' => 'список кодов маркировки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_id',
					'label' => 'ИД документа учета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnRecept_id',
					'label' => 'ИД рецепта',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ch',
					'label' => 'разделитель',
					'rules' => '',
					'type' => 'string'
				),
			), 
			'QueueUpRegisterMarksList' => array(
				array(
					'field' => 'rvRequestId',
					'label' => 'ИД задания',
					'rules' => '',
					'type' => 'string'
				)
			)
		);
	}	

	/**
	* 	Метод «Получить информацию об устройстве» 
	*/
	public function GetInformationRv() {
		$data = $this->ProcessInputData('GetInformationRv', false);

        if (!$data) {
            return false;
        }
		$response = $this->dbmodel->GetInformationRv($data);

		if ( $response[0]['success'] == false ) {
			$response['success'] = 'false';
			echo (json_encode($response));
		return true;
		}
		else {
			$response['success'] = 'true';
		}
		echo (json_encode($response));
		return true;
	}
	
	/**
	* 	Метод «Записать задание в очередь»
	 * 
	 * Значение параметра $data['type'] :
			egistration – регистрация РВ;
			checkMarks – проверка кода маркировки;
			registerMarksByRequisites – регистрация выбытия кодов маркировки по
					реквизитам документа-основания.
		 
	*/
	public function QueueUp() {
		
		$data = $this->ProcessInputData('QueueUp', false);

        if (!$data) {
            return false;
        }
		
		$response = $this->dbmodel->QueueUp($data);
		
		$response['success'] = 'true';
	
		echo (json_encode($response));
		
	}
	
	/**
	 * регистрация выбытия кодов маркировки по списку рецептов 
	*/
	public function QueueUpRegisterMarksList() {
		
		$data = $this->ProcessInputData('QueueUpRegisterMarksList', false);

		if (!$data) {
			return false;
		}
		
		$response = $this->dbmodel->QueueUpRegisterMarksList($data);
		
		echo (json_encode($response));
	}
	
	



}
?>