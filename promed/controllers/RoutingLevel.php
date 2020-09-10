<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RoutingLevel - Уровень маршрутизации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Sharipov Fidan
 * @version      11.2019
 *
 * @property RoutingLevel_model $dbmodel
 */

class RoutingLevel extends swController {
	var $inputRules = [
		'load' => [[
				'field' => 'RoutingLevel_id',
				'label' => 'Идентификатор уровня',
				'rules' => '',
				'type' => 'id'
			]
		]
	];

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('RoutingLevel_model', 'dbmodel');
	}

	/**
	 * Возвращает список уровней ниже, чем переданный RoutingLevel_id
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}