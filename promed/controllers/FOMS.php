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

class FOMS extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('FOMS_model', 'dbmodel');
	}
}