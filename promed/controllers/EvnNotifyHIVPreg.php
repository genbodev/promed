<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyHIVPreg - контроллер формы "Извещение о случае завершения беременности у ВИЧ-инфицированной женщины"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      12.2012
 * @property EvnNotifyHIVPreg_model $dbmodel
 */

class EvnNotifyHIVPreg extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyHIVPreg_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyHIVPreg_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyHIVPreg_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyHIVPreg_pid',
					'label' => 'Идентификатор движения или посещения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyHIVPreg_setDT',
					'label' => 'Дата заполнения извещения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач, заполнивший извещение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Заболевание',
					'rules' => '',
					'type' => 'id'
				),
				array('field' => 'HIVPregPathTransType_id','label' => 'Предполагаемый путь инфицирования','rules' => '','type' => 'id'),
				array('field' => 'HIVPregPeriodType_id','label' => 'Период установления диагноза','rules' => '','type' => 'id'),
				array('field' => 'HIVPregInfectStudyType_id','label' => 'Стадия ВИЧ-инфекции при взятии на учет по беременности','rules' => '','type' => 'id'),
				array('field' => 'HIVPregInfectStudyType_did','label' => 'Стадия ВИЧ-инфекции при завершении беременности','rules' => '','type' => 'id'),
				array('field' => 'HIVPregResultType_id','label' => 'Результат окончания беременности','rules' => '','type' => 'id'),
				array('field' => 'HIVPregWayBirthType_id','label' => 'Способ родоразрешения','rules' => '','type' => 'id'),
				array('field' => 'HIVPregChemProphType_id','label' => 'Химиопрофилактика в период беременности','rules' => '','type' => 'id'),
				array('field' => 'HIVPregAbortPeriodType_id','label' => 'Срок беременности при аборте','rules' => '','type' => 'id'),
				array('field' => 'AbortType_id','label' => 'Тип аборта','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyHIVPreg_IsChemProphBirth','label' => 'Химиопрофилактика в родах','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyHIVPreg_IsPreterm','label' => 'Признак преждевременных родов','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyHIVPreg_DuratBirth','label' => 'Продолжительность родов (в часах)','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVPreg_DuratWaterless','label' => 'Продолжительность безводного промежутка (в часах)','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVPreg_SrokChem','label' => 'Срок беременности на момент начала химиопрофилактика','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVPreg_Srok','label' => 'Cрок беременности на момент установаления диагноза','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVPreg_OtherWayBirth','label' => 'Иные вмешательства в роды','rules' => '','type' => 'string'),
				array('field' => 'EvnNotifyHIVPreg_DiagDT','label' => 'Дата установления диагноза ВИЧ-инфекции','rules' => '','type' => 'date'),
				array('field' => 'EvnNotifyHIVPreg_endDT','label' => 'Дата завершения беременности','rules' => '','type' => 'date'),
			)
    );


	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnNotifyHIVPreg_model', 'dbmodel');
	}
	
	/**
	 * Загрузка  формы
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
	
	/**
	 * Удаление
	 */
	function del()
	{
		$data = $this->ProcessInputData('del', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->del($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
		
	}
	
}