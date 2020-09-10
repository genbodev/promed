<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHepatitisPlan - контроллер для выполнения операций с планами лечения гепатита C
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      06.2019
 */
class MorbusHepatitisPlan extends swController {
	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	var $inputRules = array(
		'load' => array(
			array(
				'field' => 'MorbusHepatitisPlan_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'save' => array(
			array(
				'field' => 'MorbusHepatitisPlan_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitis_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitisPlan_Year',
				'label' => 'Год лечения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareType_id',
				'label' => 'Условия оказания МП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'МО планируемого лечения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitisPlan_Month',
				'label' => 'Месяц лечения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitisPlan_Treatment',
				'label' => 'Лечение проведено',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * MorbusHepatitisPlan constructor.
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('MorbusHepatitisPlan_model', 'dbmodel');
	}
	
	/**
	 * Загрузка 
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	
	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
}
?>