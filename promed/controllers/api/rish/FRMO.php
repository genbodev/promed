<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FRMO - контроллер API для работы с сервисом FRMO
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Stanislav Bykov (savage@swan-it.ru)
 * @version			24.01.2019
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class FRMO extends SwREST_Controller {
	protected  $inputRules = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('FRMO_model', 'dbmodel');
	}

	/**
	 * Запуск задания на обработку данных от ФРМО
	 */
	public function parseFRMOData_get() {
		$res = $this->dbmodel->parseFRMOData(array('fromAPI' => true));

		if ( !empty($res['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $res['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}