<?php
/**
 * PersonIdent
 */
class MskPersonIdent extends swController {
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
		$response = $this->identmodel->PersonIdentPackage();
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;

	}
}