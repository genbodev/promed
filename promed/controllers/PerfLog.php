<?php defined('BASEPATH') or die ('No direct script access allowed');

class PerfLog extends swController {
	/**
	 * Конструктор
	 */
    function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'savePerfLog' => array(
				array('field' => 'perfLog', 'label' => '', 'rules' => '', 'type' => 'string', 'assoc' => true)
			)
		);
		
	}
	/**
	 * Читаем данные в грид
	 */
	function savePerfLog() {
		$data = $this->ProcessInputData('savePerfLog', true);
		if ($data === false) { return false; }

		if (!empty($data['perfLog'])) {
			$this->load->library('textlog', array('file'=>'perfLog_'.date('Y-m-d').'.log'));
			$this->textlog->add($data['perfLog']);
		}

		$this->ReturnData(array('success' => true));
		return true;
	}
}