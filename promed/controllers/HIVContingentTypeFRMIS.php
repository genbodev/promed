<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * HIVContingentTypeFRMIS_model - Тип контингента для ФРМИС
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
 * @property HIVContingentTypeFRMIS_model $dbmodel
 */
class HIVContingentTypeFRMIS extends swController {
	public $inputRules = [];

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('HIVContingentTypeFRMIS_model', 'dbmodel');
	}

	/**
	 * Загрузка данных для грида
	 */
	public function getAll() {
		if ($this->usePostgreLis) {
			$response = $this->lis->GET("HIVContingentTypeFRMIS/getAll");
			$this->ReturnData($response);
		} else {
			$response = $this->dbmodel->getAll();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		}
		return true;
	}
}
