<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class ERSB extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Ersb_model', 'dbmodel');
		$this->inputRules = array(
			'getRefbookMap' => array(
				array('field' => 'Refbook_Code', 'label' => 'Код справочника', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Refbook_MapName', 'label' => 'Наименование таблицы стыковки', 'rules' => '', 'type' => 'string'),
				array('field' => 'Column_Name', 'label' => 'Наименование поля для фильтрации', 'rules' => '', 'type' => 'string'),
				array('field' => 'Column_Value', 'label' => 'Значение поля для фильтрации', 'rules' => '', 'type' => 'string')
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function RefbookMap_get() {
		$data = $this->ProcessInputData('getRefbookMap');

		if (!empty($data['Column_Name']) && !in_array($data['Column_Name'], array('id', 'Code', 'ERSB_id', 'ERSB_code'))) {
			$this->response(array(
				'error_msg' => 'Параметр Column_Name может принимать только значения: id, Code, ERSB_id, ERSB_code',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->getRefbookMapForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}