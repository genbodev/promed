<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsRequest - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 *
 * @property ErsRequest_model dbmodel
 */

class ErsRequest extends swController {
	protected  $inputRules = array(
		'sendErsToFss' => array(
			array( 'field' => 'EvnERS_id', 'label' => 'Идентификатор ЭРС', 'rules' => 'required', 'type' => 'id' ),
		),
		'save' => array(
			array( 'field' => 'EvnERS_id', 'label' => 'Идентификатор ЭРС', 'rules' => '', 'type' => 'id' ),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ErsRequest_model', 'dbmodel');
	}
	
	/**
	 * Сохранение ЭРС
	 */
	function sendErsToFss() {
		$data = $this->ProcessInputData('sendErsToFss');
		if ($data === false) { return false; }

		$response = $this->dbmodel->sendErsToFss($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Сохранение ЭРС
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}