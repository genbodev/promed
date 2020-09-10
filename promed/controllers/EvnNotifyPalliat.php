<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyTub - контроллер формы "Извещение по паллиативной помощи"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Kirill Sabirov
 * @version      12.2018
 *
 * @property EvnNotifyPalliat_model $dbmodel
 */

class EvnNotifyPalliat extends swController {

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	var $inputRules = array(
		'checkAllowCreate' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
		),
		'save' => array(
			array('field' => 'PalliatNotify_id', 'label' => 'Иденитификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnNotifyBase_id', 'label' => 'Иденитификатор базового извещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Server_id', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'PersonEvn_id', 'label' => 'PersonEvn_id', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Person_id', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnNotifyBase_setDate', 'label' => 'Дата извещения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_did', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
		),
		'delete' => array(
			array('field' => 'PalliatNotify_id', 'label' => 'Иденитификатор', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEditForm' => array(
			array('field' => 'PalliatNotify_id', 'label' => 'Иденитификатор', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * EvnNotifyPalliat constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnNotifyPalliat_model', 'dbmodel');
	}

	/**
	 * @return bool
	 */
	function checkAllowCreate() {
		$data = $this->ProcessInputData('checkAllowCreate', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkAllowCreate($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) { return false; }
		$data['scenario'] = swModel::SCENARIO_DELETE;
		$response = $this->dbmodel->doDelete($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadEditForm() {
		$data = $this->ProcessInputData('loadEditForm', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEditForm($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}
}