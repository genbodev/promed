<?php
/**
 * PersonIdent
 */
class KareliyaPersonIdent extends swController {
	var $NeedCheckLogin = false; // авторизация не нужна

	/**
	 * Конструктор
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
		$this->load->model("PersonIdentRequest_model", "identmodel");

		$response = $this->identmodel->PersonIdentPackage();
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;

	}
}

?>
