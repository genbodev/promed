<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/PersonDopDispPlan.php');

class Vologda_PersonDopDispPlan extends PersonDopDispPlan {
	
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
		
		$this->inputRules['exportPersonDopDispPlan'] = array(
			array(
				'field' => 'DispCheckPeriod_id',
				'label' => 'Период плана',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDopDispPlanExport_IsExportPeriod',
				'label' => 'Выгрузить за период, начиная с выбранного месяца до конца года',
				'rules' => '',
				'type' => 'swcheckbox'
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
				'field' => 'ignoreCheck',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['autoCreatePlan'] = [[
			'field' => 'PersonDopDispPlan_id',
			'label' => 'План',
			'rules' => 'required',
			'type' => 'id'
		], [
			'field' => 'DispCheckPeriod_id',
			'label' => 'Период',
			'rules' => 'required',
			'type' => 'id'
		], [
			'field' => 'DispCheckPeriod_begDate',
			'label' => 'Начало периода',
			'rules' => 'required',
			'type' => 'date'
		], [
			'field' => 'DispCheckPeriod_endDate',
			'label' => 'Конец периода',
			'rules' => 'required',
			'type' => 'date'
		]];
	}

	/**
	 * Автоматичекское заполнение плана
	 */
	function autoCreatePlan() {
		$data = $this->ProcessInputData('autoCreatePlan');
		if ($data === false) { return false; }

		$this->dbmodel->autoCreatePlan($data);
		// вывод описан в модели
		return true;
	}
}