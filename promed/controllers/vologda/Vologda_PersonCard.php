<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/PersonCard.php');

class Vologda_PersonCard extends PersonCard {
	/**
	 * Vologda_PersonCard constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules = array_merge($this->inputRules, array(
			'exportPersonCardAttach' => array(
				array('field' => 'Lpu_aid', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'OrgSMO_id', 'label' => 'Идентификатор СМО', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'year', 'label' => 'Год', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'month', 'label' => 'Месяц', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'begDate', 'label' => 'Начало отчетного периода', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'endDate', 'label' => 'Окончание отчетного периода', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'packageNumber', 'label' => 'Номер пакета', 'rules' => 'required', 'type' => 'int'),
			),
			'importPersonCardAttachResponse' => array(
				array('field' => 'File', 'label' => 'Файл', 'rules' => '', 'type' => 'string'),
				array('field' => 'Lpu_aid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			),
			'importPersonCardDetach' => array(
				array('field' => 'File', 'label' => 'Файл', 'rules' => '', 'type' => 'string'),
				array('field' => 'Lpu_aid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			),
			'importPersonCardRegister' => array(
				array('field' => 'File', 'label' => 'Файл', 'rules' => '', 'type' => 'string'),
				array('field' => 'Lpu_aid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			),
		));
	}

	/**
	 * Экспорт заявлений о прикреплении
	 */
	function exportPersonCardAttach() {
		$data = $this->ProcessInputData('exportPersonCardAttach');
		if ($data === false) return;
		$response = $this->pcmodel->exportPersonCardAttach($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Импорт предварительного ответа по прикрепленному населению
	 */
	function importPersonCardAttachResponse() {
		$data = $this->ProcessInputData('importPersonCardAttachResponse');
		if ($data === false) return;

		$data['File'] = $_FILES['File'];

		$response = $this->pcmodel->importPersonCardAttachResponse($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Импорт сведений о ЗЛ, открепленных от МО
	 */
	function importPersonCardDetach() {
		$data = $this->ProcessInputData('importPersonCardDetach');
		if ($data === false) return;

		$data['File'] = $_FILES['File'];

		$response = $this->pcmodel->importPersonCardDetach($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Импорт регистра прикрепленного населения
	 */
	function importPersonCardRegister() {
		$data = $this->ProcessInputData('importPersonCardRegister');
		if ($data === false) return;

		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");

		$data['File'] = $_FILES['File'];

		$response = $this->pcmodel->importPersonCardRegister($data);
		if ($response === true) return;
		$this->ProcessModelSave($response)->ReturnData();
	}
}