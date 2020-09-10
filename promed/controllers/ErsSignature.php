<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsSignature - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property ErsSignature_model dbmodel
 */

class ErsSignature extends swController {
	protected  $inputRules = array(
		'doSign' => array(
			array( 'field' => 'EvnERS_id', 'label' => 'Идентификатор ЭРС', 'rules' => 'required', 'type' => 'id' ),
			array( 'field' => 'ERSSignatureType_id', 'label' => '', 'rules' => 'required', 'type' => 'id' ),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ErsSignature_model', 'dbmodel');
	}
	
	/**
	 * Сохранение ЭРС
	 */
	function doSign() {
		$data = $this->ProcessInputData('doSign');
		if ($data === false) { return false; }

		$response = $this->dbmodel->doSign($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Сохранение подписи счета
	 */
	function doSignBill() {
		$data = $this->ProcessInputData('doSign');
		if ($data === false) { return false; }

		$response = $this->dbmodel->doSignBill($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Сохранение подписи талона
	 */
	function doSignTicket() {
		$data = $this->ProcessInputData('doSign');
		if ($data === false) { return false; }

		$response = $this->dbmodel->doSignTicket($data);
		$this->ProcessModelSave($response)->ReturnData();
	}	
}