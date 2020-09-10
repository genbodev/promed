<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      25.11.2018
*/
require_once(APPPATH.'controllers/EvnPrescr.php');

class Ufa_EvnPrescr extends EvnPrescr {
	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules['loadAssignmentTemplateLS'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Lpu_id',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'LpuSection_id',
				'rules' => '',
				'type' => 'id'
			)
		);
		
	/**
	 * Загрузка шаблона для назначения ЛС
	 * 
    */
	function loadAssignmentTemplateLS()
		{
			$this->db = null;
			$this->load->database();

			$data = $this->ProcessInputData('loadAssignmentTemplateLS', false);
					//echo '<pre>' . print_r($data, 1) . '</pre>'; exit;
			if ($data === false) { return false; }

			$response = $this->dbmodel->loadAssignmentTemplateLS($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			// loadEvnCourseTreatEditForm
		}
	}
}
