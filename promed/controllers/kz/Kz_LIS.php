<?php  defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class Kz_LIS - Сервис интеграции с ЛИС НЦЭ. Казахстан
 * 
 * Kukuzapa - forever
 */

class Kz_LIS  extends swController
{
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('LIS_model', 'dbmodel');
	}

	function sendDirectionToLis() {
		$response = $this->dbmodel->sendDirectionToLis();
		$this->ReturnData($response);
	}
}