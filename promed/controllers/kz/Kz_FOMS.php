<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FOMS - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 *
 * @property FOMS_model dbmodel
 */
 
require_once(APPPATH.'controllers/FOMS.php');

class Kz_FOMS extends FOMS {
	protected  $inputRules = array(
		'doRequest' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Inn',
				'label' => 'ИИН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'doRequestAuto' => array()
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Запрос
	 */
	function doRequest() {
		$data = $this->ProcessInputData('doRequest', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->doRequest($data);

		$this->ReturnData($response);
	}
}