<?php
/**
 * PersonIdent
 */
class AstraPersonIdent extends swController {
	var $NeedCheckLogin = false; // авторизация не нужна
	public $inputRules = array(
		'PersonIdentPackage'=>array(
			
		)
	);
	/**
	 * @comment
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('PersonIdentRequest_model', 'identmodel');
	}
	/**
	 *
	 * @return type 
	 */
	function PersonIdentPackage(){
		$this->load->model("Options_model", "opmodel");
		$this->load->model("PersonIdentRequest_model", "identmodel");
		
		$this->load->library('swPersonIdentAstrahan');
		//$globalOptions = $this->opmodel->getOptionsGlobals($data);
		$response = $this->identmodel->PersonIdentPackage();
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
		
	}
}

?>
