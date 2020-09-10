<?php

defined('BASEPATH') or die('No direct script access allowed');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SignalInfo extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('SignalInfo_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение списка выписанных из стационара
	 */
	function loadEvnPS() {
		$data = $this->ProcessInputData('loadEvnPS', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnPS($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка параклинических услуг
	 */
	function loadEvnUsluga() {
		$data = $this->ProcessInputData('loadEvnUsluga', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnUsluga($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка выбывших из стационара
	 */
	function loadFromStac() {
		$data = $this->ProcessInputData('loadFromStac', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadFromStac($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка
	 */
	function loadRegisterPrivilege() {
		$data = $this->ProcessInputData('loadRegisterPrivilege', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadRegisterPrivilege($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка
	 */
	function loadEvnStick() {
		$data = $this->ProcessInputData('loadEvnStick', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnStick($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка
	 */
	function loadDeathSvid() {
		$data = $this->ProcessInputData('loadDeathSvid', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadDeathSvid($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка
	 */
	function loadCmpCallCard() {
		$data = $this->ProcessInputData('loadCmpCallCard', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadCmpCallCard($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Список пациентов записанных к текущему врачу, которым необходимо пройти ДВН 1 этап
	 */
	function loadListByDayDisp() {
		$data = $this->ProcessInputData('loadListByDayDisp', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadListByDayDisp($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Список пациентов записанных к текущему врачу, которым необходимо пройти ДВН 2 этап
	 */
	function loadListByDayDisp2() {
		$data = $this->ProcessInputData('loadListByDayDisp2', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadListByDayDisp2($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Данные о пациентах направленных (записанных), у которых отсутствует посещение, связанное с направлением
	 */
	function loadPersonNoVisit() {
		$data = $this->ProcessInputData('loadPersonNoVisit', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadPersonNoVisit($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *
	 */
	function loadCdk() {
		$data = $this->ProcessInputData('loadCdk', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadCdk($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
     * Проставление признака просмотра анкеты
     */
    function setIsBrowsed() {

        $data = $this->ProcessInputData('setIsBrowsed', true);
        if ($data === false) return false;
        $result = $this->dbmodel->setIsBrowsed($data);
        $this->ReturnData($result);
	}
	/**
	 * Список беременных у которых Не проведена консультация
	 */
	function loadPregnancyRouteNotConsultation() {
		$data = $this->ProcessInputData('loadPregnancyRouteNotConsultation', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadPregnancyRouteNotConsultation($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Список беременных, которые нах в стац или выпис
	 */
	function loadPregnancyRouteHospital() {
		$data = $this->ProcessInputData('loadPregnancyRouteHospital', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadPregnancyRouteHospital($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Список беременных, которые вызывали СМП
	 */
	function loadPregnancyRouteSMP() {
		$data = $this->ProcessInputData('loadPregnancyRouteSMP', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadPregnancyRouteSMP($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Список беременных, по триместрам и МО
	 */
	function loadTrimesterListMO() {
		$data = $this->ProcessInputData('loadTrimesterListMO', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadTrimesterListMO($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Получение списка записей не включенных в регистр
	 */
	function loadPregnancyNotIncludeList() {
		$data = $this->ProcessInputData('loadPregnancyNotIncludeList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadPregnancyNotIncludeList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Регистр БСК
	 */
	 function loadListRegistBSK() {
		$data = $this->ProcessInputData('loadListRegistBSK', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadListRegistBSK($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  вкладка "КВИ"
	 */
	function loadCVI(){
		$data = $this->ProcessInputData('loadCVI', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadCVI($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *  вкладка "Диспансерный учёт"
	 */
	function loadPersonDispInfo(){
		$data = $this->ProcessInputData('loadPersonDispInfo', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadPersonDispInfo($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение списка дистанционного мониторинга
	 */
	function loadDistObservList() {
		$data = $this->ProcessInputData('loadDistObservList', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadDistObservList($data);
		$this->ReturnData($response);
		//return true;
	}

}
