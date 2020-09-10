<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/PersonDopDispPlan.php');

class Pskov_PersonDopDispPlan extends PersonDopDispPlan {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
		
		$this->inputRules['exportPersonDopDispPlan'] = array(
			array('field' => 'PersonDopDispPlan_ids', 'label' => 'Список планов для экспорта', 'rules' => 'required', 'type' => 'json_array'),
			array('field' => 'PersonDopDispPlanExport_expDate', 'label' => 'Отчетная дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'PersonDopDispPlanExport_Year', 'label' => 'Год', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'ExportByOrgSMO_flag', 'label' => 'Флаг "В разрезе СМО"', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgSMO_id', 'label' => 'СМО', 'rules' => '', 'type' => 'string'),
		);
	}
}