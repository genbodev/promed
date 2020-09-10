<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы со спецификой о больном туберкулезом
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

class EvnNotifyTub extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnNotifyTub_model', 'dbmodel');
		$this->inputRules = array(
			'getEvnNotifyTub' => array (
				array(
					'field' => 'EvnNotifyTub_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
			),
			'save' => array(
				array('field' => 'Evn_pid', 'label' => '', 'rules' => 'required','type' => 'id'),
						/*array(
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
						),*/
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array('field' => 'EvnNotifyTub_setDT', 'label' => 'Дата заполнения извещения', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'MedPersonal_id', 'label' => 'Врач, заполнивший извещение', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'МО заполнения извещения', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonCategoryType_id','label' => 'Категория населения','rules' => 'required','type' => 'id'),
				array('field' => 'PersonLivingFacilies_id','label' => 'Жилищные условия','rules' => 'required','type' => 'id'),
						array('field' => 'EvnNotifyTub_OtherPersonCategory','label' => 'Другое ведомство','rules' => '','type' => 'string'),
				array('field' => 'PersonDecreedGroup_id','label' => 'Декретированная группа','rules' => '','type' => 'string'),
				array('field' => 'EvnNotifyTub_IsDecreeGroup','label' => 'Принадлежность к декретированным группам','rules' => '','type' => 'id'),
				array('field' => 'TubFluorSurveyPeriodType_id','label' => 'Сроки предыдущего ФГ обследования','rules' => '','type' => 'id'),
				array('field' => 'TubDetectionPlaceType_id','label' => 'Место выявления','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_OtherDetectionPlace','label' => 'Учреждение другого ведомства','rules' => '','type' => 'string'),
				array('field' => 'DrugResistenceTest_id','label' => 'Тестирование на лекарственную устойчивость','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_FirstDT','label' => 'Дата первого обращения за медпомощью','rules' => '','type' => 'date'),
				array('field' => 'EvnNotifyTub_RegDT','label' => 'Дата взятия на учет в противотуберкулезном учреждении','rules' => '','type' => 'date'),
				array('field' => 'TubDetectionFactType_id','label' => 'Обстоятельства, при которых выявлено заболевание (пути выявления)','rules' => 'required','type' => 'id'),
				array('field' => 'TubSurveyGroupType_id','label' => 'Типы групп наблюдаемых в тубучреждениях','rules' => '','type' => 'id'),
				array('field' => 'TubDetectionMethodType_id','label' => 'Метод выявления','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_OtherDetectionMethod','label' => 'Другой метод','rules' => '','type' => 'string'),
				array('field' => 'Diag_id','label' => 'Диагноз по МКБ-10','rules' => 'required','type' => 'id'),
				array('field' => 'TubDiagNotify_id','label' => 'Диагноз','rules' => 'required','type' => 'id'),
				array('field' => 'TubDiagForm8_id', 'label' => 'Заболевание по форме №8', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_IsFirstDiag','label' => 'Установлен впервые в жизни','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsDestruction','label' => 'Наличие распада','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsConfirmBact','label' => 'Подтверждение бактериовыделения','rules' => '','type' => 'id'),
				array('field' => 'TubBacterialExcretion_id','label' => 'Бактериовыделение','rules' => '','type' => 'id'),
				array('field' => 'TubMethodConfirmBactType_id','label' => 'Метод подтверждения бактериовыделения','rules' => '','type' => 'id'),
				array('field' => 'TubDiagSop_id_List','label' => 'Сопутствующие заболевания','rules' => 'trim','type' => 'string'), //TubDiagSop
				array('field' => 'TubRiskFactorType_id_List','label' => 'Факторы риска','rules' => 'trim','type' => 'string'),//TubRiskFactorType
				array('field' => 'TubDiagSopLink_Descr','label' => 'Прочие сопутствующие заболевания','rules' => 'trim','type' => 'string'),
				array('field' => 'EvnNotifyTub_IsRegCrazy','label' => 'Состоит на учете в наркологическом диспансере','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsConfirmedDiag','label' => 'Диагноз подтвержден','rules' => 'required','type' => 'id'),
				array('field' => 'TubRegCrazyType_id','label' => 'Тип учета в наркологическом диспансере','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_DiagConfirmDT','label' => 'Дата подтверждения диагноза туберкулеза ЦВК','rules' => '','type' => 'date'),
				array('field' => 'PersonDispGroup_id','label' => 'Группа диспансерного наблюдения','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_Comment','label' => 'Примечание','rules' => '','type' => 'string'),
						array('field' => 'saveFromJournal','label' => 'флаг сохранения из журнала извещений','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsAutopsied', 'label' => 'Проводилась аутопсия', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_deathDT', 'label' => 'Дата смерти', 'rules' => '', 'type' => 'date'),
				array('field' => 'TubResultDeathType_id', 'label' => 'Причина смерти', 'rules' => '', 'type' => 'id'),
				array('field' => 'TubClinicStateClass_id', 'label' => 'Клиническая форма туберкулеза', 'rules' => '', 'type' => 'id'),
				array('field' => 'TubLocalizationClass_id', 'label' => 'Локализация туберкулеза', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_evnAnalysisDate', 'label' => 'Дата разбора случая', 'rules' => '', 'type' => 'date'),
				array('field' => 'TubPostmortalDecision_id', 'label' => 'Результат разбора случая', 'rules' => '', 'type' => 'id'),
				
				array('field' => 'Lpu_iid', 'label' => 'ЛПУ, добавившее человека в регистр', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_iid', 'label' => 'врач, добавивший человека в регистр', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegister_setDate', 'label' => 'Дата включения в регистр', 'rules' => '', 'type' => 'date'),
				array('field' => 'PersonRegisterType_id', 'label' => 'тип регистра', 'rules' => '', 'type' => 'id'),
			),
			'update' => array(
				array('field' => 'EvnNotifyTub_id', 'label' => 'Идентификатор извещения', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id','label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_setDT', 'label' => 'Дата заполнения извещения', 'rules' => '', 'type' => 'date'),
				array('field' => 'MedPersonal_id', 'label' => 'Врач, заполнивший извещение', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'МО заполнения извещения', 'rules' => '', 'type' => 'id'),
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
				array('field' => 'Diag_id','label' => 'Диагноз по МКБ-10','rules' => 'required','type' => 'id'),
				array('field' => 'TubDiagNotify_id','label' => 'Диагноз','rules' => 'required','type' => 'id'),
				array('field' => 'TubDiagForm8_id', 'label' => 'Заболевание по форме №8', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_IsFirstDiag','label' => 'Установлен впервые в жизни','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsDestruction','label' => 'Наличие распада','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsConfirmBact','label' => 'Подтверждение бактериовыделения','rules' => '','type' => 'id'),
				array('field' => 'TubBacterialExcretion_id','label' => 'Бактериовыделение','rules' => '','type' => 'id'),
				array('field' => 'TubMethodConfirmBactType_id','label' => 'Метод подтверждения бактериовыделения','rules' => '','type' => 'id'),
				array('field' => 'TubDiagSop_id_List','label' => 'Сопутствующие заболевания','rules' => 'trim','type' => 'string'), //TubDiagSop
				array('field' => 'TubRiskFactorType_id_List','label' => 'Факторы риска','rules' => 'trim','type' => 'string'),//TubRiskFactorType
				array('field' => 'TubDiagSopLink_Descr','label' => 'Прочие сопутствующие заболевания','rules' => 'trim','type' => 'string'),
				array('field' => 'EvnNotifyTub_IsRegCrazy','label' => 'Состоит на учете в наркологическом диспансере','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsConfirmedDiag','label' => 'Диагноз подтвержден','rules' => 'required','type' => 'id'),
				array('field' => 'TubRegCrazyType_id','label' => 'Тип учета в наркологическом диспансере','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_DiagConfirmDT','label' => 'Дата подтверждения диагноза туберкулеза ЦВК','rules' => '','type' => 'date'),
				array('field' => 'PersonDispGroup_id','label' => 'Группа диспансерного наблюдения','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_Comment','label' => 'Примечание','rules' => '','type' => 'string'),
						array('field' => 'saveFromJournal','label' => 'флаг сохранения из журнала извещений','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyTub_IsAutopsied', 'label' => 'Проводилась аутопсия', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_deathDT', 'label' => 'Дата смерти', 'rules' => '', 'type' => 'date'),
				array('field' => 'TubResultDeathType_id', 'label' => 'Причина смерти', 'rules' => '', 'type' => 'id'),
				array('field' => 'TubClinicStateClass_id', 'label' => 'Клиническая форма туберкулеза', 'rules' => '', 'type' => 'id'),
				array('field' => 'TubLocalizationClass_id', 'label' => 'Локализация туберкулеза', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyTub_evnAnalysisDate', 'label' => 'Дата разбора случая', 'rules' => '', 'type' => 'date'),
				array('field' => 'TubPostmortalDecision_id', 'label' => 'Результат разбора случая', 'rules' => '', 'type' => 'id'),				
				array('field' => 'Lpu_iid', 'label' => 'ЛПУ, добавившее человека в регистр', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_iid', 'label' => 'врач, добавивший человека в регистр', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegister_setDate', 'label' => 'Дата включения в регистр', 'rules' => '', 'type' => 'date'),
				array('field' => 'PersonRegisterType_id', 'label' => 'тип регистра', 'rules' => '', 'type' => 'id'),
			),
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnNotifyTub');
		if(empty($data['EvnNotifyTub_id']) && empty($data['Person_id'])){
			return array(
				'Error_Msg' => 'Не переданы параметры'
			);
		}

		$resp = $this->dbmodel->getEvnNotifyTubForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response($resp);
	}
	
	/**
	 * Создание извещения о больном туберкулезом
	 */
	function index_post(){
		$data = $this->ProcessInputData('save', null, true);
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		
		if(!empty($data['TubDiagSop_id_List'])){
			$data['TubDiagSop'] = $data['TubDiagSop_id_List'];
		}
		if(!empty($data['TubRiskFactorType_id_List'])){
			$data['TubRiskFactorType'] = $data['TubRiskFactorType_id_List'];
		}
		
		$res = $this->dbmodel->saveEvnNotifyTubAPI($data);
		
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res['EvnNotifyTub_id'])){
			$returnArr = array(
				'error_code' => 0,
				'EvnNotifyTub_id' => $res['EvnNotifyTub_id']
			);
			if(!empty($res['PersonRegister_id'])){
				$returnArr['PersonRegister_id'] = $res['PersonRegister_id'];
			}
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res['Error_Msg'])) ? $res['Error_Msg'] : 'ошибка создания данных по хирургическому лечению в рамках специфики онкологии'
			));
		}
	}
	
	/**
	 * Изменение извещения о больном туберкулезом
	 */
	function index_put(){
		$data = $this->ProcessInputData('update', null, true);
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		
		$data['TubRiskFactorType'] = (!empty($data['TubRiskFactorType_id_List'])) ? $data['TubRiskFactorType_id_List'] : null;
		$data['TubDiagSop'] = (!empty($data['TubDiagSop_id_List'])) ? $data['TubDiagSop_id_List'] : null;
		
		$res = $this->dbmodel->updateEvnNotifyTubAPI($data);
		if(!empty($res['EvnNotifyTub_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res['Error_Msg'])) ? $res['Error_Msg'] : 'ошибка редактировании данных данных по хирургическому лечению в рамках специфики онкологии'
			));
		}
	}
}