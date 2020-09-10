<?php
class KzServiceRpn extends swController {
	var $NeedCheckLogin = false; // авторизация не нужна
	public $inputRules = array(
		'PersonIdentPackage'=>array(

		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		if (getRegionNick() != 'kz') {
			DieWithError('Только для Казахстана');
		}
		$this->load->database();
		$this->load->model("ServiceRPN_model", "ServiceRPN_model");
	}

	/**
	 * Запустить идентификацию
	 */
	function startPersonIdent(){
		set_time_limit(0);
		ignore_user_abort(true);

		$this->load->model("ServiceList_model", "ServiceList_model");

		$response = $this->ServiceList_model->startPersonIdent();

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Запуск сервиса для проверки статусов заявлений в РПН
	 */
	function startCheckPersonCardAttachStatusService() {
		set_time_limit(0);
		ignore_user_abort(true);

		$this->load->model("ServiceList_model", "ServiceList_model");

		$response = $this->ServiceList_model->startCheckPersonCardAttachStatusService();

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Запуск сервиса синхронизации участков с сервисом РПН
	 */
	function startLoadLpuRegionFromRpn() {
		set_time_limit(0);
		ignore_user_abort(true);

		$this->load->model("ServiceList_model", "ServiceList_model");

		$response = $this->ServiceList_model->startLoadLpuRegionFromRpn();

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}


}

?>
