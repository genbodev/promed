<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
* Assistant - контроллер для АРМ лаборанта
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      unknown
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Chebukin Alexander
* @version      18.03.2011
 *
 * @property Assistant_model dbmodel
 *
*/

class Assistant extends swController {
	
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadLabRequestGrid' => array(),
			'loadLabRequest' => array(
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveLabRequest' => array(
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkReagentsGodnDate' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReagentsGodnDate',
					'label' => 'Количество дней до конца срока годности',
					'rules' => 'required',
					'type' => 'int'
				)
			)
		);
		
		$this->load->database();
		$this->load->model('Assistant_model', 'dbmodel');
	}
	
	function loadLabRequestGrid() {
		
		$data = $this->ProcessInputData('loadLabRequestGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadLabRequestGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

	}
	
	function loadLabRequest() {
		
		$data = $this->ProcessInputData('loadLabRequest', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadLabRequest($data);
		$this->ProcessModelList($response)->ReturnData();

	}
	
	function saveLabRequest() {
		
		$data = $this->ProcessInputData('saveLabRequest', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveLabRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
		
	}
	
	//#PROMEDWEB-9689
	function checkReagentsGodnDate(){
		
		$data = $this->ProcessInputData('checkReagentsGodnDate', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkReagentsGodnDate($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
		
	}

	//#PROMEDWEB-9689
	function printReagentsGodnDate(){
		
		$this->load->library('parser');

		$data = $this->ProcessInputData('checkReagentsGodnDate', true);

		$response = $this->dbmodel->checkReagentsGodnDate($data);
		
		$print_data = array(
			'items'=>$response
		);
		return $this->parser->parse('delay_reagents_print', $print_data);
	}
	
}




?>