<?php
/**
 * @package     All
 * @access      public
 * @copyright   Copyright (c) 2018 EMSIS.
 * @author      Apaev Alexander
 * @version     07.12.2018
 */
class RegisterSixtyPlus extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('RegisterSixtyPlus_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение списка выписанных из стационара
	 */
	function getDiagList() {
		$data = $this->ProcessInputData('getDiagList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getDiagList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение диагнозов ДУ
	 */
	function getDiagDU() {
		$data = $this->ProcessInputData('getDiagDU', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getDiagDU($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение анализов и исследований
	 */
	function getLabResearch() {
		$data = $this->ProcessInputData('getLabResearch', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getLabResearch($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение ИМТ
	 */
	function getIMT() {
		$data = $this->ProcessInputData('getIMT', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getIMT($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение онкоконтроля 
	 */
	function getOncocontrol() {
		$data = $this->ProcessInputData('getOncocontrol', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getOncocontrol($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение полик. медпопощи
	 */
	function getMedicalCare() {
		$data = $this->ProcessInputData('getMedicalCare', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getMedicalCare($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение стац. медпопощи
	 */
	function getStacMed() {
		$data = $this->ProcessInputData('getStacMed', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getStacMed($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение ИМТ
	 */
	function getPersonIMT() {
		$data = $this->ProcessInputData('getPersonIMT', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getPersonIMT($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение даты обновления регистра
	 */
	function getupdDT() {
		$data = $this->ProcessInputData('getPersonIMT', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getupdDT($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Лекарственное леченеие
	 */
	function geTreatmentDrug() {
		$data = $this->ProcessInputData('geTreatmentDrug', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->geTreatmentDrug($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

}
