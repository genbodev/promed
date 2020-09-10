<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusTub - Специфика по психиатрии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *-TODO: Do some explanation, preamble and describing
 * @package      Foobaring
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Markoff
 * @version      2012/11
 */
/**
 * @property MorbusTub_model $dbmodel
 */
class MorbusTub extends swController
{
    var $model_name = "MorbusTub_model";

	/**
	 * construct
	 */
	function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model($this->model_name, 'dbmodel');
        $this->inputRules = array(
			'loadMorbusTubDiagSop' => array(
				array('field' => 'MorbusTubDiagSop_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id'),
			),
			'loadMorbusTubDiagGeneralForm' => array(
				array('field' => 'TubDiagGeneralForm_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id'),
			),
			'saveMorbusTubDiagSop' => array(
				array('field' => 'MorbusTubDiagSop_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
				array('field' => 'TubDiagSop_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubDiagSop_setDT','label' => 'Дата выявления','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubDiagSop_OtherDiag','label' => 'Прочее заболевание','rules' => 'max_length[30]', 'type' => 'string'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'saveTubDiagGeneralForm' => array(
				array('field' => 'TubDiagGeneralForm_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз','rules' => 'required', 'type' => 'id'),
				array('field' => 'TubDiagGeneralForm_setDT','label' => 'Дата выявления','rules' => '', 'type' => 'date')
			),
			'loadMorbusTubConditChem' => array(
				array('field' => 'MorbusTubConditChem_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'TubStandartConditChemType_id','label' => 'Стандартные режимы химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubTreatmentChemType_id','label' => 'Схема лечения химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubStageChemType_id','label' => 'Фазы химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubVenueType_id','label' => 'Место проведения','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubConditChem_BegDate','label' => 'Дата начала лечения ','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubConditChem_EndDate','label' => 'Дата окончания лечения ','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusTubPrescr' => array(
				array('field' => 'MorbusTubPrescr_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_setDT','label' => 'Дата назначения','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubPrescr_endDate','label' => 'Дата назначения','rules' => '', 'type' => 'date'),
				array('field' => 'TubDrug_id','label' => 'Противотуберкулезные препараты','rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id','label' => 'Препараты (РЛС)','rules' => '', 'type' => 'id'),
				array('field' => 'TubTreatmentChemType_id','label' => 'Схема лечения химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubStageChemType_id','label' => 'Фазы химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_DoseDay','label' => 'Суточная доза','rules' => 'max_length[30]', 'type' => 'float'),
				array('field' => 'MorbusTubPrescr_Schema','label' => 'Схема','rules' => 'max_length[40]', 'type' => 'string'),
				array('field' => 'MorbusTubPrescr_DoseTotal','label' => 'Общее количество доз','rules' => 'max_length[30]', 'type' => 'float'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusTubPrescrTimetable' => array(
				array('field' => 'MorbusTubPrescrTimetable_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_id','label' => 'Лекарственное назначение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubPrescrTimetable_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubPrescrTimetable_IsExec','label' => 'отметка о выполнении','rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id','label' => 'Врач выполнивший назначение','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusTubStudyResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubStudyResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
				array('field' => 'TubStageChemType_id','label' => 'Месяц/фаза','rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id','label' => 'Человек','rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonWeight_id','label' => 'Вес человека','rules' => '', 'type' => 'id'),
				array('field' => 'PersonWeight_Weight','label' => 'Вес человека','rules' => '', 'type' => 'float'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadEvnDirectionTub' => array(
				array('field' => 'EvnDirectionTub_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_id','label' => 'Направление','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				// Дата сбора образцов
				array('field' => 'EvnDirectionTub_PersonRegNum','label' => 'Региональный регистрационный номер пациента','rules' => '', 'type' => 'string'),
				array('field' => 'TubDiagnosticMaterialType_id','label' => 'Диагностический материал','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_OtherMeterial','label' => 'Другой диагностический материал','rules' => '', 'type' => 'string'),
				array('field' => 'TubTargetStudyType_id','label' => 'Цель исследования','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_NumLab','label' => 'лабораторный номер исследования','rules' => '', 'type' => 'string'),
				array('field' => 'MedPersonal_id','label' => '– Медицинский работник, направивший пациента на исследование','rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_lid','label' => 'Медицинский работник, собравший образцы диагностического материала','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_ResDT','label' => 'Дата выдачи результата','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id'),
				array('field' => 'Server_id','label' => 'Сервер','rules' => '', 'type' => 'int'),
				array('field' => 'PersonEvn_id','label' => 'Человек','rules' => '', 'type' => 'id')
			),
			'loadTubMicrosResult' => array(
				array('field' => 'TubMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_id','label' => 'Направление','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_id','label' => 'Направление','rules' => '', 'type' => 'id'),
				array('field' => 'TubMicrosResult_setDT','label' => 'Дата сбора образцов','rules' => '', 'type' => 'date'),
				array('field' => 'TubMicrosResult_Num','label' => 'номер образца мокроты','rules' => '', 'type' => 'string'),
				array('field' => 'TubMicrosResult_MicrosDT','label' => 'Дата проведения исследования','rules' => '', 'type' => 'date'),
				array('field' => 'TubMicrosResultType_id','label' => 'Результат микроскопии','rules' => '', 'type' => 'id'),
				array('field' => 'TubMicrosResult_EdResult','label' => 'Число микобактерий','rules' => '', 'type' => 'string'),
				array('field' => 'TubMicrosResult_Comment','label' => 'Примечание','rules' => '', 'type' => 'string'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'saveMorbusTub' => array(
				array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusBase_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTub_begDT','label' => 'Дата возникновения симптомов','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_FirstDT','label' => 'Дата первого обращения к любому врачу по поводу этих симптомов','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_DiagDT','label' => 'Дата установления диагноза','rules' => '', 'type' => 'date'),				
				array('field' => 'MorbusTub_SanatorDT','label' => 'Дата завершения санаторно-курортного лечения','rules' => '', 'type' => 'date'),				
				array('field' => 'MorbusTub_ResultDT','label' => 'Дата исхода курса химиотерапии','rules' => '', 'type' => 'date'),
				array('field' => 'TubSickGroupType_id','label' => 'Группа больных','rules' => '', 'type' => 'id'),
				array('field' => 'TubResultChemClass_id','label' => 'Вид исхода курса химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubResultChemType_id','label' => 'Тип исхода курса химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubDiag_id','label' => 'Диагноз','rules' => '', 'type' => 'id'),				
				array('field' => 'TubPhase_id','label' => 'Фаза','rules' => '', 'type' => 'id'),
				array('field' => 'TubDisability_id','label' => 'Инвалидность','rules' => '', 'type' => 'id'),				
				array('field' => 'PersonDecreedGroup_id','label' => 'Декретированная группа','rules' => '', 'type' => 'id'),
				array('field' => 'PersonLivingFacilies_id','label' => 'Жилищные условия','rules' => '', 'type' => 'id'),
				array('field' => 'PersonDispGroup_id','label' => 'Группа диспансерного наблюдения','rules' => '', 'type' => 'id'),
				array('field' => 'PersonResidenceType_id','label' => 'Статус пациента','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз МКБ-10 записи регистра (не заболевания)','rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonRegister_id','label' => 'Запись регистра','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_RegNumCard','label' => 'Регистратрационный номер пациента','rules' => '', 'type' => 'int'),
				array('field' => 'TubBreakChemType_id','label' => 'Причина прерывания химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_ConvDT','label' => 'Дата перевода в III группу ДУ','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_CountDay','label' => 'Общее кол-во дней нетрудоспособности','rules' => '', 'type' => 'int'),
				array('field' => 'TubResultDeathType_id','label' => 'Причина смерти','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_deadDT','label' => 'Дата смерти','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_breakDT','label' => 'Дата прерывания курса химиотерапии','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_disDT','label' => 'Дата выбытия','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_unsetDT','label' => 'Дата снятия диагноза туберкулеза','rules' => '', 'type' => 'date'),
				array('field' => 'Person_id','label' => 'Пациент','rules' => '', 'type' => 'id'),
				//array('field' => 'Diag_id','label' => 'Диагноз заболевания','rules' => '','type' => 'id'),
				array('field' => 'Evn_pid','label' => 'Движение/посещение','rules' => '', 'type' => 'id'),
				array('field' => 'Mode','label' => 'Режим сохранения','rules' => '', 'type' => 'string'),

				array('field' => 'MorbusTubMDR_id','label' => 'MorbusTubMDR_id','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_RegNumPerson','label' => 'Региональный регистрационный номер пациента ','rules' => 'trim|max_length[10]', 'type' => 'string'),
				array('field' => 'MorbusTubMDR_RegNumCard','label' => 'Региональный регистрационный номер случая лечения','rules' => 'trim|max_length[10]', 'type' => 'string'),
				array('field' => 'MorbusTubMDR_GroupDisp','label' => 'Группа диспансерного наблюдения на момент регистрации текущего случая лечения','rules' => 'trim|max_length[20]', 'type' => 'string'),
				array('field' => 'MorbusTubMDR_regDT','label' => 'Дата регистрации ЦВКК','rules' => 'trim', 'type' => 'date'),
				array('field' => 'MorbusTubMDR_regdiagDT','label' => 'Дата регистрации на лечение по IV режиму','rules' => 'trim', 'type' => 'date'),
				array('field' => 'MorbusTubMDR_begDT','label' => 'Дата первого обнаружения устойчивости к рифампицину','rules' => 'trim', 'type' => 'date'),
				array('field' => 'MorbusTubMDR_TubDiag_id','label' => 'Диагноз','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_TubSickGroupType_id','label' => 'Группа случая лечения туберкулеза по IV режиму','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_IsPathology','label' => 'Наличие патологии, кодируемой В20-В24','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_IsART','label' => 'Назначена АРТ','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_IsCotrim','label' => 'Назначена профилактическая терапия котримоксазолом','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_IsDrugFirst','label' => 'Проходил лечение препаратами 1-го ряда до начала текущего курса лечения','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_IsDrugSecond','label' => 'Проходил лечение ранее препаратами 2-го ряда','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_IsDrugResult','label' => 'Курс лечения по IV режиму обоснован результатами тестов на лекарственную чувствительность','rules' => 'trim', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_IsEmpiric','label' => 'Начат, как эмпирический курс','rules' => 'trim', 'type' => 'id'),

				array('field' => 'SopDiags','label' => 'Сопутствующие диагнозы','rules' => 'trim', 'type' => 'string'),
				array('field' => 'SopDiag_Descr','label' => '','rules' => 'trim', 'type' => 'string'),
				array('field' => 'RiskTypes','label' => 'Факторы риска','rules' => 'trim', 'type' => 'string')
			),
			'getTubDiag' => array(
				array('field' => 'TubDiag_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'МКБ-10','rules' => '', 'type' => 'id'),
				array('field' => 'TubDiag_Code','label' => 'Код','rules' => '', 'type' => 'string'),
				array('field' => 'TubDiag_Name','label' => 'Наименование','rules' => '', 'type' => 'string'),
				array('field' => 'query','label' => 'Запрос','rules' => '', 'type' => 'string')
			),
			'loadMorbusTubStudyMicrosResult' => array(
				array('field' => 'MorbusTubStudyMicrosResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'loadListMorbusTubStudyMicrosResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubStudyMicrosResult' => array(
				array('field' => 'MorbusTubStudyMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyMicrosResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'MorbusTubStudyMicrosResult_NumLab','label' => '№ образца мокроты','rules' => 'max_length[10]', 'type' => 'string'),
				array('field' => 'TubMicrosResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyMicrosResult_EdResult','label' => 'Число микобактерий','rules' => '', 'type' => 'int'),
			),
			'loadMorbusTubStudyDrugResult' => array(
				array('field' => 'MorbusTubStudyDrugResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'loadListMorbusTubStudyDrugResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubStudyDrugResult' => array(
				array('field' => 'MorbusTubStudyDrugResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyDrugResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubDrug_id','label' => 'Тип препарата','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyDrugResult_IsResult','label' => 'Результат теста','rules' => '', 'type' => 'id'),
				//array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusTubMolecular' => array(
				array('field' => 'MorbusTubMolecular_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'loadListMorbusTubMolecular' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubMolecular' => array(
				array('field' => 'MorbusTubMolecular_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubMolecular_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'MorbusTubMolecularType_id','label' => 'Тест на лекарственную устойчивость','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubMolecular_IsResult','label' => 'Результат теста','rules' => '', 'type' => 'id'),
				//array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusTubStudyXrayResult' => array(
				array('field' => 'MorbusTubStudyXrayResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'loadListMorbusTubStudyXrayResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubStudyXrayResult' => array(
				array('field' => 'MorbusTubStudyXrayResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyXrayResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubXrayResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyXrayResult_Comment','label' => 'Примечание','rules' => 'max_length[30]', 'type' => 'string'),
			),
			'loadMorbusTubStudySeedResult' => array(
				array('field' => 'MorbusTubStudySeedResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'loadListMorbusTubStudySeedResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubStudySeedResult' => array(
				array('field' => 'MorbusTubStudySeedResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudySeedResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubSeedResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
			),
			'loadMorbusTubStudyHistolResult' => array(
				array('field' => 'MorbusTubStudyHistolResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'loadListMorbusTubStudyHistolResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubStudyHistolResult' => array(
				array('field' => 'MorbusTubStudyHistolResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyHistolResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubDiagnosticMaterialType_id','label' => 'Диагностический материал','rules' => 'required', 'type' => 'id'),
				array('field' => 'TubHistolResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
			),
			'loadMorbusTubAdvice' => array(
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubAdvice' => array(
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubAdvice_setDT','label' => 'Дата консультации фтизиохирурга','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubAdviceResultType_id','label' => 'Результат консультации','rules' => 'required', 'type' => 'id'),
			),
			'loadMorbusTubAdviceOper' => array(
				array('field' => 'MorbusTubAdviceOper_id','label' => 'Оперативное лечение','rules' => 'required', 'type' => 'id'),
			),
			'loadListMorbusTubAdviceOper' => array(
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => 'required', 'type' => 'id'),
			),
			'saveMorbusTubAdviceOper' => array(
				array('field' => 'MorbusTubAdviceOper_id','label' => 'Оперативное лечение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubAdviceOper_setDT','label' => 'Дата операции','rules' => 'required', 'type' => 'date'),
				array('field' => 'UslugaComplex_id','label' => 'Тип операции','rules' => 'required', 'type' => 'id'),
			),
	        'createMorbusTubMDR' => array(
		        array('field' => 'Morbus_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_RegNumPerson','label' => 'Региональный регистрационный номер пациента ','rules' => 'trim|max_length[10]', 'type' => 'string', 'default' => 1),
		        array('field' => 'MorbusTubMDR_RegNumCard','label' => 'Региональный регистрационный номер случая лечения','rules' => 'trim|max_length[10]', 'type' => 'string', 'default' => 1),
		        array('field' => 'MorbusTubMDR_GroupDisp','label' => 'Группа диспансерного наблюдения на момент регистрации текущего случая лечения','rules' => 'trim|max_length[20]', 'type' => 'string'),
		        array('field' => 'MorbusTubMDR_regDT','label' => 'Дата регистрации ЦВКК','rules' => 'trim', 'type' => 'date'),
		        array('field' => 'MorbusTubMDR_regdiagDT','label' => 'Дата регистрации на лечение по IV режиму','rules' => 'trim', 'type' => 'date'),
		        array('field' => 'MorbusTubMDR_begDT','label' => 'Дата первого обнаружения устойчивости к рифампицину','rules' => 'trim', 'type' => 'date'),
		        array('field' => 'MorbusTubMDR_TubDiag_id','label' => 'Диагноз','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_TubSickGroupType_id','label' => 'Группа случая лечения туберкулеза по IV режиму','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_IsPathology','label' => 'Наличие патологии, кодируемой В20-В24','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_IsART','label' => 'Назначена АРТ','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_IsCotrim','label' => 'Назначена профилактическая терапия котримоксазолом','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_IsDrugFirst','label' => 'Проходил лечение препаратами 1-го ряда до начала текущего курса лечения','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_IsDrugSecond','label' => 'Проходил лечение ранее препаратами 2-го ряда','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_IsDrugResult','label' => 'Курс лечения по IV режиму обоснован результатами тестов на лекарственную чувствительность','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_IsEmpiric','label' => 'Начат, как эмпирический курс','rules' => 'trim', 'type' => 'id'),
	        ),
	        'loadMorbusTubMDRPrescr' => array(
		        array('field' => 'MorbusTubPrescr_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
		        array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
	        ),
	        'saveMorbusTubMDRPrescr' => array(
		        array('field' => 'MorbusTubPrescr_id','label' => 'Идентификатор','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_id','label' => 'МЛУ','rules' => 'required', 'type' => 'id'),
		        array('field' => 'MorbusTubPrescr_setDT','label' => 'Дата начала','rules' => 'required', 'type' => 'date'),
		        array('field' => 'MorbusTubPrescr_endDate','label' => 'Дата окончания','rules' => '', 'type' => 'date'),
		        array('field' => 'TubDrug_id','label' => 'Препарат','rules' => 'required', 'type' => 'id'),
		        array('field' => 'MorbusTubPrescr_SetDay','label' => 'Назначено дней лечения','rules' => 'trim|max_length[30]', 'type' => 'string'),
		        array('field' => 'MorbusTubPrescr_MissDay','label' => 'Пропущено дней лечения','rules' => 'trim|max_length[30]', 'type' => 'string'),
		        array('field' => 'Lpu_id','label' => 'МО','rules' => 'required', 'type' => 'id'),
		        array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '', 'type' => 'id'),
		        array('field' => 'Person_id','label' => 'Человек','rules' => 'required', 'type' => 'id'),
		        //array('field' => 'Okei_id','label' => 'Человек','rules' => 'required', 'type' => 'id'),37
		        array('field' => 'PersonWeight_id','label' => 'Вес на начало лечения','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'PersonWeight_Weight','label' => 'Вес на начало лечения','rules' => 'trim', 'type' => 'float'),
		        array('field' => 'MorbusTubPrescr_DoseDay','label' => 'Дозировка','rules' => 'trim|max_length[30]', 'type' => 'string'),
		        array('field' => 'MorbusTubPrescr_DoseMiss','label' => 'Пропущено доз','rules' => 'trim|max_length[30]', 'type' => 'string'),
		        //array('field' => 'MorbusTubPrescr_Schema','label' => 'Схема','rules' => 'trim|max_length[40]', 'type' => 'string'),
		        array('field' => 'MorbusTubPrescr_DoseTotal','label' => 'Принято доз','rules' => 'trim|max_length[30]', 'type' => 'string'),
		        array('field' => 'MorbusTubPrescr_Comment','label' => 'Примечание','rules' => 'trim|max_length[100]', 'type' => 'string'),
		        array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
	        ),
	        'loadMorbusTubMDRStudyDrugResult' => array(
		        array('field' => 'MorbusTubMDRStudyDrugResult_id','label' => 'Идентификатор','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDRStudyResult_id','label' => 'Результат исследований','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
	        ),
	        'saveMorbusTubMDRStudyDrugResult' => array(
		        array('field' => 'MorbusTubMDRStudyDrugResult_id','label' => 'Идентификатор','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDRStudyResult_id','label' => 'Результат исследований','rules' => 'required', 'type' => 'id'),
		        array('field' => 'MorbusTubMDRStudyDrugResult_setDT','label' => 'Дата','rules' => 'trim', 'type' => 'date'),
		        array('field' => 'TubDrug_id','label' => 'Препарат','rules' => 'required', 'type' => 'id'),
		        array('field' => 'TubDiagResultType_id','label' => 'Результат теста','rules' => 'required', 'type' => 'id'),
	        ),
	        'loadMorbusTubMDRStudyResult' => array(
		        array('field' => 'MorbusTubMDRStudyResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
		        array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
	        ),
	        'saveMorbusTubMDRStudyResult' => array(
		        array('field' => 'MorbusTubMDRStudyResult_id','label' => 'Идентификатор','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDR_id','label' => 'Туберкулез с МЛУ','rules' => 'required', 'type' => 'id'),
		        array('field' => 'MorbusTubMDRStudyResult_setDT','label' => 'Дата сбора','rules' => 'trim', 'type' => 'date'),
		        array('field' => 'MorbusTubMDRStudyResult_Month','label' => 'Месяц лечения','trim|max_length[10]', 'type' => 'string'),
		        array('field' => 'MorbusTubMDRStudyResult_NumLab','label' => 'Лабораторный номер','rules' => 'trim|max_length[10]', 'type' => 'string'),
		        array('field' => 'TubHistolResultType_id','label' => 'МГМ','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'TubMicrosResultType_id','label' => 'Микроскопия','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'TubSeedResultType_id','label' => 'Культура','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'TubXrayResultType_id','label' => 'Результат рентгенологического обследования','rules' => 'trim', 'type' => 'id'),
		        array('field' => 'MorbusTubMDRStudyResult_Comment','label' => 'Примечание','rules' => 'trim|max_length[100]', 'type' => 'string'),
	        ),
	        'exportToXLS' => array(
		        array('field' => 'Lpu_oid','label' => 'МО','rules' => '', 'type' => 'id'),
		        array('field' => 'Range','label' => 'Перид','rules' => 'required', 'type' => 'daterange'),
		        array('field' => 'ExportType_id','label' => 'Тип выгруки','rules' => 'required', 'type' => 'id'),
	        ),
        );
    }

	/**
	 * @return bool
	 */
	function loadMorbusTubAdvice() {
		$data = $this->ProcessInputData('loadMorbusTubAdvice', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubAdviceViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubAdvice() {
		$data = $this->ProcessInputData('saveMorbusTubAdvice', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubAdvice($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadListMorbusTubAdviceOper() {
		$data = $this->ProcessInputData('loadListMorbusTubAdviceOper', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubAdviceOperViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubAdviceOper() {
		$data = $this->ProcessInputData('loadMorbusTubAdviceOper', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubAdviceOperViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubAdviceOper() {
		$data = $this->ProcessInputData('saveMorbusTubAdviceOper', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubAdviceOper($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadListMorbusTubStudyHistolResult() {
		$data = $this->ProcessInputData('loadListMorbusTubStudyHistolResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyHistolResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubStudyHistolResult() {
		$data = $this->ProcessInputData('loadMorbusTubStudyHistolResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyHistolResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubStudyHistolResult() {
		$data = $this->ProcessInputData('saveMorbusTubStudyHistolResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubStudyHistolResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadListMorbusTubStudyXrayResult() {
		$data = $this->ProcessInputData('loadListMorbusTubStudyXrayResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyXrayResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubStudyXrayResult() {
		$data = $this->ProcessInputData('loadMorbusTubStudyXrayResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyXrayResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubStudyXrayResult() {
		$data = $this->ProcessInputData('saveMorbusTubStudyXrayResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubStudyXrayResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadListMorbusTubStudySeedResult() {
		$data = $this->ProcessInputData('loadListMorbusTubStudySeedResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudySeedResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubStudySeedResult() {
		$data = $this->ProcessInputData('loadMorbusTubStudySeedResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudySeedResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubStudySeedResult() {
		$data = $this->ProcessInputData('saveMorbusTubStudySeedResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubStudySeedResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadListMorbusTubStudyMicrosResult() {
		$data = $this->ProcessInputData('loadListMorbusTubStudyMicrosResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyMicrosResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubStudyMicrosResult() {
		$data = $this->ProcessInputData('loadMorbusTubStudyMicrosResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyMicrosResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubStudyMicrosResult() {
		$data = $this->ProcessInputData('saveMorbusTubStudyMicrosResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubStudyMicrosResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadListMorbusTubStudyDrugResult() {
		$data = $this->ProcessInputData('loadListMorbusTubStudyDrugResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyDrugResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubStudyDrugResult() {
		$data = $this->ProcessInputData('loadMorbusTubStudyDrugResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyDrugResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubStudyDrugResult() {
		$data = $this->ProcessInputData('saveMorbusTubStudyDrugResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubStudyDrugResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadListMorbusTubMolecular() {
		$data = $this->ProcessInputData('loadListMorbusTubMolecular', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubMolecularViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubMolecular() {
		$data = $this->ProcessInputData('loadMorbusTubMolecular', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubMolecularViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubMolecular() {
		$data = $this->ProcessInputData('saveMorbusTubMolecular', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubMolecular($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubDiagSop() {
		$data = $this->ProcessInputData('loadMorbusTubDiagSop', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubDiagSopViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubDiagGeneralForm() {
		$data = $this->ProcessInputData('loadMorbusTubDiagGeneralForm', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubDiagGeneralFormViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubDiagSop() {
		$data = $this->ProcessInputData('saveMorbusTubDiagSop', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubDiagSop($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveTubDiagGeneralForm() {
		$data = $this->ProcessInputData('saveTubDiagGeneralForm', true);
		if ($data) {
			$response = $this->dbmodel->saveTubDiagGeneralForm($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubConditChem() {
		$data = $this->ProcessInputData('loadMorbusTubConditChem', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubConditChemViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubConditChem() {
		$data = $this->ProcessInputData('loadMorbusTubConditChem', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubConditChem($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubPrescr() {
		$data = $this->ProcessInputData('loadMorbusTubPrescr', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubPrescrViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubPrescr() {
		$data = $this->ProcessInputData('loadMorbusTubPrescr', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubPrescr($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubPrescrTimetable() {
		$data = $this->ProcessInputData('loadMorbusTubPrescrTimetable', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubPrescrTimetableViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubPrescrTimetable() {
		$data = $this->ProcessInputData('loadMorbusTubPrescrTimetable', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubPrescrTimetable($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubStudyResult() {
		$data = $this->ProcessInputData('loadMorbusTubStudyResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubStudyResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubStudyResult() {
		$data = $this->ProcessInputData('saveMorbusTubStudyResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubStudyResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadEvnDirectionTub() {
		$data = $this->ProcessInputData('loadEvnDirectionTub', true);
		if ($data) {
			$response = $this->dbmodel->getEvnDirectionTubViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveEvnDirectionTub() {
		$data = $this->ProcessInputData('loadEvnDirectionTub', true);
		if ($data) {
			$response = $this->dbmodel->saveEvnDirectionTub($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadTubMicrosResult() {
		$data = $this->ProcessInputData('loadTubMicrosResult', true);
		if ($data) {
			$response = $this->dbmodel->getTubMicrosResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveTubMicrosResult() {
		$data = $this->ProcessInputData('loadTubMicrosResult', true);
		if ($data) {
			$response = $this->dbmodel->saveTubMicrosResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	
	/**
	 * Сохранение специфики
	 */
	function saveMorbusTub()
	{
		$data = $this->ProcessInputData('saveMorbusTub', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTub($data);
			if (isset($data["MorbusTub_deadDT"]) && isset($data["TubResultDeathType_id"]) && getRegionNick() != 'kz') {
				$params = array(
					'PersonRegister_id' => $data["PersonRegister_id"],
					'PersonRegister_disDate' => $data["MorbusTub_deadDT"],
					'PersonRegisterOutCause_id' => '1',
					'PersonDeathCause_id' => null,
					'MedPersonal_did' => $data["session"]["medpersonal_id"],
					'Lpu_did' => $data["Lpu_id"],
					'pmUser_id' => $data["pmUser_id"],
					'Lpu_id' => $data["Lpu_id"],
					'session' => $data["session"],
				);
				$this->load->model('PersonRegister_model');
				$resp = $this->PersonRegister_model->out($params);
			}
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function getTubDiag() {
		$data = $this->ProcessInputData('getTubDiag', true);
		if ($data) {
			$response = $this->dbmodel->getTubDiag($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function createMorbusTubMDR() {
		$data = $this->ProcessInputData('createMorbusTubMDR', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubMDR($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubMDRPrescr() {
		$data = $this->ProcessInputData('loadMorbusTubMDRPrescr', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubMDRPrescrViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubMDRPrescr() {
		$data = $this->ProcessInputData('saveMorbusTubMDRPrescr', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubMDRPrescr($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubMDRStudyDrugResult() {
		$data = $this->ProcessInputData('loadMorbusTubMDRStudyDrugResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubMDRStudyDrugResultViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubMDRStudyDrugResult() {
		$data = $this->ProcessInputData('saveMorbusTubMDRStudyDrugResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubMDRStudyDrugResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadMorbusTubMDRStudyResult() {
		$data = $this->ProcessInputData('loadMorbusTubMDRStudyResult', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusTubMDRStudyResultViewData($data);
			$this->ProcessModelList($response, true, false)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function saveMorbusTubMDRStudyResult()
	{
		$data = $this->ProcessInputData('saveMorbusTubMDRStudyResult', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusTubMDRStudyResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function deleteMorbusTubMDRStudyResult()
	{
		$data = $this->ProcessInputData('loadMorbusTubMDRStudyResult', true);
		if ($data) {
			$response = $this->dbmodel->deleteMorbusTubMDRStudyResult($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Выгрузка сведений регистра больных туберкулёзом в Excel
	 * @return bool
	 */
	function exportToXLS() {
		$data = $this->ProcessInputData('exportToXLS', true);
		if (!$data) return false;

		$exportData = $this->dbmodel->getDataForXLS($data);
		if (!is_array($exportData)) {
			$this->ReturnError('Ошибка при запросе данных для выгрузки');
			return false;
		}
		if (count($exportData) == 0) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}

		$fieldsMap = $this->dbmodel->getFieldsMapForXLS();

		$fileName = 'tub_register_'.time();

		require_once('vendor/autoload.php');
		$objPHPExcel = new PhpOffice\PhpSpreadsheet\Spreadsheet();
		$objPHPExcel->getProperties();
		$objPHPExcel->getActiveSheet()->setTitle('Лист1');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);

		$colIdx = 0;
		$rowIdx = 1;
		foreach($fieldsMap as $name => $title) {
			$sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $title);
			$colIdx++;
		}
		foreach($exportData as $rowData) {
			$rowIdx++;
			$colIdx = 0;
			foreach($fieldsMap as $name => $title) {
				if (isset($rowData[$name])) {
					$sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $rowData[$name]);
				}
				$colIdx++;
			}
		}

		$objWriter = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);

		$path = EXPORTPATH_ROOT.'tub_register';
		if (!file_exists($path)) {
			mkdir($path);
		}

		$file = "{$path}/{$fileName}.xlsx";
		$objWriter->save($file);

		$response = array('success' => true, 'file' => $file);

		$this->ProcessModelSave($response, true, 'Ошибка при выгрузке')->ReturnData();
		return true;
	}
}
