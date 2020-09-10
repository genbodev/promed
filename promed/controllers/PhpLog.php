<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * Class PhpLog
 *
 * @property CI_DB_driver $db
 * @property PhpLog_model $dbmodel
 */
class PhpLog extends swController
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->default_db = $this->db->database;
		$this->db = null;
		$this->load->database("phplog");
		$this->load->model("PhpLog_model", "dbmodel");
		$this->inputRules = [
			"loadPhpLogGrid" => [
				["field" => "start", "label" => "", "rules" => "", "type" => "int", "default" => 0],
				["field" => "limit", "label" => "Лимит записей", "rules" => "", "type" => "int", "default" => 50],
				["field" => "PHPLog_insDT", "label" => "", "rules" => "", "type" => "daterange"],
				["field" => "Controller", "label" => "", "rules" => "", "type" => "string"],
				["field" => "Method", "label" => "", "rules" => "", "type" => "string"],
				["field" => "methodAdvanced", "label" => "", "rules" => "", "type" => "string"],
				["field" => "ET_from", "label" => "", "rules" => "", "type" => "float"],
				["field" => "ET_to", "label" => "", "rules" => "", "type" => "float"],
				["field" => "ET_Query_from", "label" => "", "rules" => "", "type" => "float"],
				["field" => "ET_Query_to", "label" => "", "rules" => "", "type" => "float"],
				["field" => "PMUser_Login", "label" => "", "rules" => "", "type" => "string"],
				["field" => "IP", "label" => "", "rules" => "", "type" => "string"],
				["field" => "Server_IP", "label" => "", "rules" => "", "type" => "string"],
				["field" => "POST", "label" => "", "rules" => "", "type" => "string"],
				["field" => "AnswerError", "label" => "", "rules" => "", "type" => "int"],
				["field" => "ARMType", "label" => "", "rules" => "", "type" => "string"],
				['field' => 'isUsePersonData', 'label' => '', 'rules' => '', 'type' => 'swcheckbox']
			]
		];
	}

	/**
	 * Читаем данные в грид
	 * @return bool
	 * @throws Exception
	 */
	function loadPhpLogGrid()
	{
		$data = $this->ProcessInputData("loadPhpLogGrid", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadPhpLogGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получаем список методов из базы логов
	 * @return bool
	 */
	function loadMethodsList()
	{
		$response = $this->dbmodel->loadMethodsList();
		echo @json_encode($response);
		return true;
	}

	/**
	 * Получаем список методов которые имеют описание из базы логов
	 * @return bool
	 */
	function loadRuMethodsList()
	{
		$response = $this->dbmodel->loadRuMethodsList();
		echo @json_encode($response);
		return true;
	}

	/**
	 * @return bool
	 */
	function loadOldLogToNewFormat()
	{
		$this->dbmodel->loadOldLogToNewFormat();
		return true;
	}
}