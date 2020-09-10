<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * @package	  CmpWorkPlan
 * @author	  Salavat Magafurov
 * @version	  12 2017
 */

class CmpWorkPlan extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('CmpWorkPlan_model', 'dbmodel');
		$this->inputRules = array(
			'getWorkPlans' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подстанция',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CmpWorkPlan_BegDT_Range',
					'label' => 'Дата начала действия плана',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'CmpWorkPlan_EndDT_Range',
					'label' => 'Дата окончания действия плана',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => 'trim',
					'type'  => 'int'
				)
			),
			'getWorkPlan' => array(
				array(
					'field' => 'CmpWorkPlan_id',
					'label' => 'Идентификатор плана',
					'rules' => '',
					'type' => 'int'
				),
			),
			'getSubstationPlans' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подстанция',
					'rules' => '',
					'type' => 'int'
				)
			),
			'addWorkPlan' => array(
				array(
					'field' => 'Data',
					'label' => 'План',
					'rules' => 'required',
					'type' => 'string'
				),
			),
			'updWorkPlan' => array(
				array(
					'field' => 'Data',
					'label' => 'План',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'delWorkPlan' => array(
				array(
					'field' => 'CmpWorkPlan_id',
					'label' => 'Идентификатор плана',
					'rules' => 'required',
					'type' => 'int',
				)
			),
			'getSubstationList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => 'trim',
					'type'  => 'int'
				)
			)
		);
	}

	/**
	 * Получение списка планов выхода на смену автомобилей и бригад
	 */
	function getWorkPlans() {
		$data = $this->ProcessInputData('getWorkPlans', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getWorkPlans($data);
		$this->ReturnData($response);
	}

	/**
	 * Добавление нового плана
	 */
	function addWorkPlan() {
		$data = $this->ProcessInputData('addWorkPlan', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->addWorkPlan($data);
		$this->ProcessModelList($response, true, 'При сохранении произошла ошибка')->ReturnData();
	}

	/**
	 * Получаем план
	 */
	function getWorkPlan() {
		$data = $this->ProcessInputData('getWorkPlan', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getWorkPlan($data);
		$this->ReturnData($response);
	}

	/**
	 * Получаем список планов для конкретной подстанции
	 */
	function getSubstationPlans() {
		$data = $this->ProcessInputData('getSubstationPlans', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getSubstationPlans($data);
		$this->ReturnData($response);
	}
	/**
	 * Обновление существующего плана
	 */
	function updWorkPlan() {
		$data = $this->ProcessInputData('updWorkPlan', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->updWorkPlan($data);
		$this->ReturnData($response);
	}

	/**
	 * Удаление плана
	 */
	function delWorkPlan() {
		$data = $this->ProcessInputData('delWorkPlan', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->delWorkPlan($data);
		$this->ReturnData($response);
	}

	/**
	 * получение списка подстанций
	 */
	function getSubstationList() {
		$data = $this->ProcessInputData('getSubstationList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getSubstationList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка МО имеющих подразделения СМП
	 */
	function getLpuList() {
		$response = $this->dbmodel->getLpuList();
		$this->ReturnData($response);
	}
}