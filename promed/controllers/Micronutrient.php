<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Micronutrient - контроллер для работы с микронутриентами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

class Micronutrient extends swController {

	/**
	 * @var array
	 */
	protected  $inputRules = array(
		'loadMicronutrientList' => array(

		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Micronutrient_model', 'dbmodel');
	}

	/**
	 * Возвращает список микронутриентов
	 * @return bool
	 */
	function loadMicronutrientList()
	{
		$data = $this->ProcessInputData('loadMicronutrientList',true);
		if ($data === false) {return false;}

		$micronutrient_data = $this->dbmodel->loadMicronutrientList($data);
		$this->ProcessModelList($micronutrient_data,true,true)->ReturnData();
		return true;
	}
}