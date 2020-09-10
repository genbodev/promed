<?php defined('BASEPATH') or die ('No direct script access allowed');

class PhpLogService extends swController {
	
	var $NeedCheckLogin = false; // авторизация не нужна
	/**
	 * Конструктор
	 */
    function __construct() {
		parent::__construct();
		$this->load->database();
		$this->default_db = $this->db->database;
		$this->db = null;
		$this->load->database( 'phplog' );
		$this->load->model('PhpLog_model', 'dbmodel');
	}
	/**
	 * Сливаем данные из MongoDB в PHPLog
	 */
	function transferDataFromMongoDB() {
		$response = $this->dbmodel->transferDataFromMongoDB();
		return true;
	}
}
