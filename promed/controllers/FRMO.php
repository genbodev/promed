<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FRMO - контроллер для работы с ведеральным регистром медицинских организаций
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			17.12.2018
 */

class FRMO extends swController {
	/**
	 * @var array
	 */
	protected $inputRules = array(
		//
	);

	var $NeedCheckLogin = false;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->library('textlog', array('file' => 'FRMO_' . date('Y_m_d') . '.log'));
		$this->load->database();
		$this->load->model('FRMO_model', 'dbmodel');
	}

	/**
	 * Запуск задания на обработку данных от ФРМО
	 */
	public function parseFRMOData() {
		$res = $this->dbmodel->parseFRMOData();
		$this->ReturnData($res);
		return true;
	}
}