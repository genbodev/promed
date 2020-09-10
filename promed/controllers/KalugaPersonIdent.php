<?php
/**
 * KalugaPersonIdent
 */
class KalugaPersonIdent extends swController {
	var $NeedCheckLogin = false; // авторизация не нужна
	
	/**
	 * @comment
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model("KalugaPersonIdent_model", "identmodel");
	}
	
	/**
	 *
	 * @comment
	 */
	function PersonIdentSend(){

		$response = $this->identmodel->PersonIdentSend();
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 *
	 * @comment
	 */
	function PersonIdentRead(){

		$response = $this->identmodel->PersonIdentRead();
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
}

