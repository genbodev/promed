<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHepatitisCureEffMonitoring - контроллер формы "Мониторинг эффективности лечения"
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

class MorbusHepatitisCureEffMonitoring extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'load' => array(
				array(
					'field' => 'MorbusHepatitisCureEffMonitoring_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'loadList' => array(
				array(
					'field' => 'MorbusHepatitisCure_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'MorbusHepatitisCureEffMonitoring_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusHepatitisCure_id',
					'label' => 'Идентификатор лечения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HepatitisCurePeriodType_id',
					'label' => 'Срок лечения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HepatitisQualAnalysisType_id',
					'label' => 'Качественный анализ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusHepatitisCureEffMonitoring_VirusStress',
					'label' => 'Вирусная нагрузка',
					'rules' => 'required',
					'type' => 'int'
				)
			)
    );

	/**
	 * MorbusHepatitisCureEffMonitoring constructor.
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('MorbusHepatitisCureEffMonitoring_model', 'dbmodel');
	}
	
	/**
	 * Загрузка списка
	 */
	function loadList() {
		
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

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