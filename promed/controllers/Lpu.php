<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Diag - контроллер для работы со справочником МО
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 ufa
 * @author			gilmiyarov 
 * @version			02042019
 *
 */

class Lpu extends swController {
	public $inputRules = array(
		'getLpuTreeSearchData' => array(
			array(
				'field' => 'node',
				'label' => 'node',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_Name',
				'label' => 'Наименование МО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuLevel_id',
				'label' => 'Уровень',
				'rules' => '',
				'type' => 'string'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Lpu_model', 'dbmodel');
	}

	/**
	 * Возвращает данные для дерева МО с поиском в дереве
	 * @return bool
	 */
	function getLpuTreeSearchData()
	{
		$data = $this->ProcessInputData('getLpuTreeSearchData',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getLpuTreeSearchData($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
}