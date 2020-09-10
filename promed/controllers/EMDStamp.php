<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EMDStamp - контроллер для вывода штампа с данными ЭП, для использования в PDF
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2010-2018 Swan Ltd.
* @author		Dmitry Vlasenko
*/
class EMDStamp extends swController {
	var $NeedCheckLogin = false;
	public $inputRules = array(
		'printStamp' => array(
			array(
				'field' => 'EMDCertificate_id',
				'label' => 'Идентификатор сертификата',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database('emd'); // своя БД на PostgreSQL
		$this->load->model('EMD_model', 'dbmodel');
	}

	/**
	 * Вывод штампа
	 */
	function printStamp() {
		$data = $this->ProcessInputData('printStamp', false);
		if ($data === false) { return false; }

		$this->dbmodel->printStamp($data);

		return true;
	}
}
