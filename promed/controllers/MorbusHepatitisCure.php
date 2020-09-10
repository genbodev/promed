<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHepatitisCure - контроллер для выполнения операций с лечением по гепатиту
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Chebukin 
 * @version      07.2012
 */

class MorbusHepatitisCure extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'load' => array(
				array(
					'field' => 'MorbusHepatitisCure_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'MorbusHepatitisCure_id',
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
					'field' => 'MorbusHepatitisCure_begDT',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MorbusHepatitisCure_endDT',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Препарат',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HepatitisResultClass_id',
					'label' => 'Результат',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HepatitisSideEffectType_id',
					'label' => 'Побочный эффект',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusHepatitisCureEffMonitoring',
					'label' => 'Мониторинг эффективности лечения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnSection_id',
					'label' => 'Идентификатор движения',
					'rules' => '',
					'type' => 'id'
				)
			)
    );

	/**
	 * MorbusHepatitisCure constructor.
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('MorbusHepatitisCure_model', 'dbmodel');
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
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
		
	}
	
}
?>