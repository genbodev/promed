<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * @package	  RzhdRegistry
 * @author	  Salavat Magafurov
 * @version	  11 2017
 */

class RzhdRegistry extends swController {

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('RzhdRegistry_model', 'dbmodel');

	}

	/**
	 * Обновление записи в регистре
	 */
	function doSave() {
		$this->dbmodel->setScenario(RzhdRegistry_model::SCENARIO_DO_SAVE);
		$this->inputRules['saveRzhdRegistry'] = $this->dbmodel->getInputRules(RzhdRegistry_model::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveRzhdRegistry', true, true);
		if($data === false) return false;
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Получение данных регистра по id
	 */
	function loadEditForm() {
		$this->dbmodel->setScenario(RzhdRegistry_model::SCENARIO_LOAD_EDIT_FORM);
		$this->inputRules['loadEditForm'] = $this->dbmodel->getInputRules(RzhdRegistry_model::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadEditForm', true, true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEditForm($data);
		$this->ReturnData([$response]);
	}

	/**
	 * Проверяем наличие пациента в регистре
	 */
	function isExistProfile() {
		$this->dbmodel->setScenario(RzhdRegistry_model::SEARCH_PROFILE);
		$this->inputRules['isExistProfile'] = $this->dbmodel->getInputRules(RzhdRegistry_model::SEARCH_PROFILE);
		$data = $this->ProcessInputData('isExistProfile', true, true);
		if ($data === false) { return false; }
		$params = [
			'Register_id' => $data['Register_id']
		];
		$response = $this->dbmodel->isExistObjectRecord('RzhdRegistry',$params, 'r2');
		$this->ReturnData($response);
	}
}