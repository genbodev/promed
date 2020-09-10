<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * WorkPlaceCovidPeriod - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 *
 * @property WorkPlaceCovidPeriod_model dbmodel
 */

class WorkPlaceCovidPeriod extends swController {
	protected $inputRules = array(
		'load' => [
			[ 'field' => 'MedStaffFact_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type' => 'id' ],
		],
		'save' => [
			[ 'field' => 'MedStaffFact_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'WorkPlaceCovidPeriodData', 'label' => '', 'rules' => '','type' => 'json_array', 'assoc' => true ],
		],
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('WorkPlaceCovidPeriod_model', 'dbmodel');
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}