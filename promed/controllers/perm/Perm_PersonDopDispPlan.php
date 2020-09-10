<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/PersonDopDispPlan.php');

class Perm_PersonDopDispPlan extends PersonDopDispPlan {
	
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
		
		$this->inputRules['exportPersonDopDispPlan'] = array(
			array(
				'field' => 'PersonDopDispPlan_ids',
				'label' => 'Список планов для экспорта',
				'rules' => 'required',
				'type' => 'json_array'
			),
			array(
				'field' => 'DispCheckPeriod_id',
				'label' => 'Период плана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSMO_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PacketNumber',
				'label' => 'Номер пакета',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Вид диспансеризации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDopDispPlanExport_Year',
				'label' => 'Год',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PersonDopDispPlanExport_Month',
				'label' => 'Месяц',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PersonDopDispPlanExport_Quart',
				'label' => 'Квартал',
				'rules' => '',
				'type' => 'int'
			)
		);
	}
}