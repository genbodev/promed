<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RaceType_model - Тип расы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author
 * @version
 *
 * @property RaceType_model $dbmodel
 */
class RaceType extends swController {
	public $inputRules = [];

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('RaceType_model', 'dbmodel');
	}

	/**
	 * Загрузка данных для грида
	 */
	public function loadGrid() {
		if ($this->usePostgreLis) {
			$response = $this->lis->GET("RaceType/loadGrid");
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadGrid();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		}
		return true;
	}
}
