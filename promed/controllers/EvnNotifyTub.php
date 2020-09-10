<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyTub - контроллер формы "Извещение о больном туберкулезом"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      10.2012
 *
 * @property EvnNotifyTub_model $dbmodel
 */

class EvnNotifyTub extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyTub_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyTub_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyTub_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyTub_pid',
					'label' => 'Идентификатор движения или посещения',
					'rules' => '',
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
					'field' => 'EvnNotifyTub_setDT',
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
					'field' => 'Lpu_id',
					'label' => 'МО заполнения извещения',
					'rules' => '',
					'type' => 'id'
				),
				array('field' => 'PersonCategoryType_id','label' => 'Категория населения','rules' => '','type' => 'id'),
				array('field' => 'PersonLivingFacilies_id','label' => 'Жилищные условия','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_OtherPersonCategory','label' => 'Другое ведомство','rules' => '','type' => 'string'),
				array('field' => 'PersonDecreedGroup_id','label' => 'Декретированная группа','rules' => '','type' => 'string'),
				array('field' => 'EvnNotifyTub_IsDecreeGroup','label' => 'Принадлежность к декретированным группам','rules' => '','type' => 'id'),
				array('field' => 'TubFluorSurveyPeriodType_id','label' => 'Сроки предыдущего ФГ обследования','rules' => '','type' => 'id'),
				array('field' => 'TubDetectionPlaceType_id','label' => 'Место выявления','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_OtherDetectionPlace','label' => 'Учреждение другого ведомства','rules' => '','type' => 'string'),
				array('field' => 'DrugResistenceTest_id','label' => 'Тестирование на лекарственную устойчивость','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_FirstDT','label' => 'Дата первого обращения за медпомощью','rules' => '','type' => 'date'),
				array('field' => 'EvnNotifyTub_RegDT','label' => 'Дата взятия на учет в противотуберкулезном учреждении','rules' => '','type' => 'date'),
				array('field' => 'TubDetectionFactType_id','label' => 'Обстоятельства, при которых выявлено заболевание (пути выявления)','rules' => '','type' => 'id'),
				array('field' => 'TubSurveyGroupType_id','label' => 'Типы групп наблюдаемых в тубучреждениях','rules' => '','type' => 'id'),
				array('field' => 'TubDetectionMethodType_id','label' => 'Метод выявления','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_OtherDetectionMethod','label' => 'Другой метод','rules' => '','type' => 'string'),
				array('field' => 'Diag_id','label' => 'Диагноз по МКБ-10','rules' => '','type' => 'id'),
				array('field' => 'TubDiagNotify_id','label' => 'Диагноз','rules' => '','type' => 'id'),
				array('field' => 'TubDiagForm8_id', 'label' => 'Заболевание по форме №8', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_IsFirstDiag','label' => 'Установлен впервые в жизни','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsDestruction','label' => 'Наличие распада','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsConfirmBact','label' => 'Подтверждение бактериовыделения','rules' => '','type' => 'id'),
				array('field' => 'TubBacterialExcretion_id','label' => 'Бактериовыделение','rules' => '','type' => 'id'),
				array('field' => 'TubMethodConfirmBactType_id','label' => 'Метод подтверждения бактериовыделения','rules' => '','type' => 'id'),
				array('field' => 'TubDiagSop','label' => 'Сопутствующие заболевания','rules' => 'trim','type' => 'string'),
				array('field' => 'TubRiskFactorType','label' => 'Факторы риска','rules' => 'trim','type' => 'string'),
				array('field' => 'TubDiagSopLink_Descr','label' => 'Прочие сопутствующие заболевания','rules' => 'trim','type' => 'string'),
				array('field' => 'EvnNotifyTub_IsRegCrazy','label' => 'Состоит на учете в наркологическом диспансере','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsConfirmedDiag','label' => 'Диагноз подтвержден','rules' => '','type' => 'id'),
				array('field' => 'TubRegCrazyType_id','label' => 'Тип учета в наркологическом диспансере','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_DiagConfirmDT','label' => 'Дата подтверждения диагноза туберкулеза ЦВК','rules' => '','type' => 'date'),
				array('field' => 'PersonDispGroup_id','label' => 'Группа диспансерного наблюдения','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_Comment','label' => 'Примечание','rules' => '','type' => 'string'),
				array('field' => 'saveFromJournal','label' => 'флаг сохранения из журнала извещений','rules' => '','type' => 'id')
			),
			'checkTubRegistryEntry' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			)
    );

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnNotifyTub_model', 'dbmodel');
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
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->dbmodel->doSave($data);
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

	/**
	 * Пороверка наличия записи
	 */
	function checkTubRegistryEntry() {
		
		$data = $this->ProcessInputData('checkTubRegistryEntry', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkTubRegistryEntry($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
}