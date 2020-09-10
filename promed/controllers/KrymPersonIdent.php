<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property PersonIdentPackage_model identmodel
 */

class KrymPersonIdent extends SwController {
	var $NeedCheckLogin = false; // авторизация не нужна

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		if (getRegionNick() != 'krym') {
			DieWithError('Только для Крыма');
		}
		$this->load->database();
		$this->load->model('PersonIdentPackage_model', 'identmodel');
	}

	/**
	 * @return bool
	 */
	function PersonIdentPackage() {
		$response = $this->identmodel->createPersonIdentPackages();
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function PersonIdentPackageResponse() {
		$data = array();
		$data['pmUser_id'] = 1;
		$data['Server_id'] = 0;
		$data['session']['pmuser_id'] = $data['pmUser_id'];
		$data['session']['server_id'] = $data['Server_id'];
		$response = $this->identmodel->autoImportPersonIdentPackagesResponse($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
}