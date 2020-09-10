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

class MorbusTub extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MorbusTub_model', 'dbmodel');
		$this->inputRules = array(
			'getMorbusTub' => array (
				array(
					'field' => 'MorbusTub_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
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
				/*????*/array('field' => 'TubDiagSop_id','label' => 'Сопутствующие заболевания','rules' => '', 'type' => 'id'),
				// ? TubDiagSopLink_Descr
				// ? TubRiskFactorType_id
						array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
						array('field' => 'MorbusTub_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
						array('field' => 'MorbusBase_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTub_begDT','label' => 'Дата возникновения симптомов','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_FirstDT','label' => 'Дата первого обращения к любому врачу по поводу этих симптомов','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_DiagDT','label' => 'Дата установления диагноза','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_ResultDT','label' => 'Дата исхода курса химиотерапии','rules' => '', 'type' => 'date'),
				array('field' => 'TubSickGroupType_id','label' => 'Группа больных','rules' => '', 'type' => 'id'),
						array('field' => 'TubResultChemClass_id','label' => 'Вид исхода курса химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubResultChemType_id','label' => 'Тип исхода курса химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubDiag_id','label' => 'Диагноз','rules' => '', 'type' => 'id'),
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
				array('field' => 'Person_id','label' => 'Пациент','rules' => 'required', 'type' => 'id'),
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
			'update' => array(
				array('field' => 'MorbusTub_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTub_begDT','label' => 'Дата возникновения симптомов','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_FirstDT','label' => 'Дата первого обращения к любому врачу по поводу этих симптомов','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_DiagDT','label' => 'Дата установления диагноза','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_ResultDT','label' => 'Дата исхода курса химиотерапии','rules' => '', 'type' => 'date'),
				array('field' => 'TubSickGroupType_id','label' => 'Группа больных','rules' => '', 'type' => 'id'),
				array('field' => 'TubResultChemType_id','label' => 'Тип исхода курса химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'PersonDecreedGroup_id','label' => 'Декретированная группа','rules' => '', 'type' => 'id'),
				array('field' => 'PersonLivingFacilies_id','label' => 'Жилищные условия','rules' => '', 'type' => 'id'),
				array('field' => 'PersonDispGroup_id','label' => 'Группа диспансерного наблюдения','rules' => '', 'type' => 'id'),
				array('field' => 'PersonResidenceType_id','label' => 'Статус пациента','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз МКБ-10 записи регистра (не заболевания)','rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegister_id','label' => 'Запись регистра','rules' => '', 'type' => 'id'),
				array('field' => 'TubBreakChemType_id','label' => 'Причина прерывания химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_ConvDT','label' => 'Дата перевода в III группу ДУ','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_CountDay','label' => 'Общее кол-во дней нетрудоспособности','rules' => '', 'type' => 'int'),
				array('field' => 'TubResultDeathType_id','label' => 'Причина смерти','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_deadDT','label' => 'Дата смерти','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_breakDT','label' => 'Дата прерывания курса химиотерапии','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_disDT','label' => 'Дата выбытия','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTub_unsetDT','label' => 'Дата снятия диагноза туберкулеза','rules' => '', 'type' => 'date'),
				array('field' => 'Person_id','label' => 'Пациент','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubMDR_TubDiag_id','label' => 'Диагноз','rules' => 'trim', 'type' => 'id'),
				array('field' => 'TubDiagSop_id_Link','label' => 'Сопутствующие диагнозы','rules' => 'trim', 'type' => 'string'),
				array('field' => 'TubDiagSopLink_Descr','label' => '','rules' => 'trim', 'type' => 'string'),
				array('field' => 'TubRiskFactorType_id_Link','label' => 'Факторы риска','rules' => 'trim', 'type' => 'string')
			),
			'saveTubDiagGeneralForm' => array(
				array('field' => 'TubDiagGeneralForm_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз','rules' => '', 'type' => 'id'),
				array('field' => 'TubDiagGeneralForm_setDT','label' => 'Дата выявления','rules' => '', 'type' => 'date')
			),
			'saveMorbusTubConditChem' => array(
				array('field' => 'MorbusTubConditChem_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'TubStandartConditChemType_id','label' => 'Стандартные режимы химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubTreatmentChemType_id','label' => 'Схема лечения химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubStageChemType_id','label' => 'Фазы химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubVenueType_id','label' => 'Место проведения','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubConditChem_BegDate','label' => 'Дата начала лечения ','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubConditChem_EndDate','label' => 'Дата окончания лечения ','rules' => '', 'type' => 'date')
			),
			'saveMorbusTubAdvice' => array(
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubAdvice_setDT','label' => 'Дата консультации фтизиохирурга','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubAdviceResultType_id','label' => 'Результат консультации','rules' => 'required', 'type' => 'id')
			),
			'updateMorbusTubAdvice' => array(
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubAdvice_setDT','label' => 'Дата консультации фтизиохирурга','rules' => '', 'type' => 'date'),
				array('field' => 'TubAdviceResultType_id','label' => 'Результат консультации','rules' => '', 'type' => 'id')
			),
			'getMorbusTubAdvice' => array(
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id')
			),
			'saveMorbusTubAdviceOper' => array(
				array('field' => 'MorbusTubAdviceOper_id','label' => 'Оперативное лечение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubAdviceOper_setDT','label' => 'Дата операции','rules' => 'required', 'type' => 'date'),
				array('field' => 'UslugaComplex_id','label' => 'Тип операции','rules' => 'required', 'type' => 'id'),
			),
			'updateMorbusTubAdviceOper' => array(
				array('field' => 'MorbusTubAdviceOper_id','label' => 'Оперативное лечение','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubAdvice_id','label' => 'Консультация фтизиохирурга','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubAdviceOper_setDT','label' => 'Дата операции','rules' => '', 'type' => 'date'),
				array('field' => 'UslugaComplex_id','label' => 'Тип операции','rules' => '', 'type' => 'id'),
			),
			'getMorbusTubAdviceOper' => array(
				array('field' => 'MorbusTubAdviceOper_id','label' => 'Оперативное лечение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id')
			),
			'saveEvnDirectionTub' => array(
					array('field' => 'EvnDirectionTub_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
					array('field' => 'EvnDirection_id','label' => 'Направление','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
					// Дата сбора образцов
				array('field' => 'EvnDirectionTub_PersonRegNum','label' => 'Региональный регистрационный номер пациента','rules' => '', 'type' => 'string'),
				array('field' => 'TubDiagnosticMaterialType_id','label' => 'Диагностический материал','rules' => 'required', 'type' => 'id'),
					array('field' => 'EvnDirectionTub_OtherMeterial','label' => 'Другой диагностический материал','rules' => '', 'type' => 'string'),
				array('field' => 'TubTargetStudyType_id','label' => 'Цель исследования','rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_NumLab','label' => 'лабораторный номер исследования','rules' => '', 'type' => 'string'),
				array('field' => 'MedPersonal_id','label' => '– Медицинский работник, направивший пациента на исследование','rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_lid','label' => 'Медицинский работник, собравший образцы диагностического материала','rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_ResDT','label' => 'Дата выдачи результата','rules' => '', 'type' => 'date'),
					array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id'),
					array('field' => 'Server_id','label' => 'Сервер','rules' => '', 'type' => 'int'),
					array('field' => 'PersonEvn_id','label' => 'Человек','rules' => '', 'type' => 'id')
			),
			'updateEvnDirectionTub' => array(
				array('field' => 'EvnDirectionTub_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
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
			'getEvnDirectionTub' => array(
				array('field' => 'EvnDirectionTub_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id')
			),
			'saveTubMicrosResult' => array(
				//array('field' => 'TubMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_id','label' => 'Направление','rules' => 'required', 'type' => 'id'),
					array('field' => 'EvnDirection_id','label' => 'Направление','rules' => '', 'type' => 'id'),
					array('field' => 'TubMicrosResult_setDT','label' => 'Дата сбора образцов','rules' => '', 'type' => 'date'),
					array('field' => 'TubMicrosResult_Num','label' => 'номер образца мокроты','rules' => '', 'type' => 'string'),
				array('field' => 'TubMicrosResult_MicrosDT','label' => 'Дата проведения исследования','rules' => 'required', 'type' => 'date'),
					array('field' => 'TubMicrosResultType_id','label' => 'Результат микроскопии','rules' => '', 'type' => 'id'),
					array('field' => 'TubMicrosResult_EdResult','label' => 'Число микобактерий','rules' => '', 'type' => 'string'),
					array('field' => 'TubMicrosResult_Comment','label' => 'Примечание','rules' => '', 'type' => 'string'),
					array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'updateTubMicrosResult' => array(
				array('field' => 'TubMicrosResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
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
			'getTubMicrosResult' => array(
				array('field' => 'TubMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id')
			),
			'saveTubMicrosResult' => array(
				array('field' => 'TubMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_id','label' => 'Направление','rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnDirection_id','label' => 'Направление','rules' => '', 'type' => 'id'),
				array('field' => 'TubMicrosResult_setDT','label' => 'Дата сбора образцов','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubMicrosResult_Num','label' => 'номер образца мокроты','rules' => '', 'type' => 'string'),
				array('field' => 'TubMicrosResult_MicrosDT','label' => 'Дата проведения исследования','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubMicrosResultType_id','label' => 'Результат микроскопии','rules' => 'required', 'type' => 'id'),
				array('field' => 'TubMicrosResult_EdResult','label' => 'Число микобактерий','rules' => '', 'type' => 'string'),
				array('field' => 'TubMicrosResult_Comment','label' => 'Примечание','rules' => '', 'type' => 'string'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'updateTubMicrosResult' => array(
				array('field' => 'TubMicrosResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
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
			'getTubMicrosResult' => array(
				array('field' => 'TubMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirectionTub_id','label' => 'Направление','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubPrescr' => array(
					array('field' => 'MorbusTubPrescr_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_setDT','label' => 'Дата назначения','rules' => 'required', 'type' => 'date'),
				array('field' => 'MorbusTubPrescr_endDate','label' => 'Дата назначения','rules' => '', 'type' => 'date'),
				array('field' => 'TubDrug_id','label' => 'Противотуберкулезные препараты','rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id','label' => 'Препараты (РЛС)','rules' => '', 'type' => 'id'),
					array('field' => 'TubTreatmentChemType_id','label' => 'Схема лечения химиотерапии','rules' => '', 'type' => 'id'),
				array('field' => 'TubStageChemType_id','label' => 'Фазы химиотерапии','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_DoseDay','label' => 'Суточная доза','rules' => 'max_length[30]', 'type' => 'float'),
				array('field' => 'MorbusTubPrescr_Schema','label' => 'Схема','rules' => 'max_length[40]', 'type' => 'string'),
				array('field' => 'MorbusTubPrescr_DoseTotal','label' => 'Общее количество доз','rules' => 'max_length[30]', 'type' => 'float'),
					array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'updateMorbusTubPrescr' => array(
				array('field' => 'MorbusTubPrescr_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
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
			'getMorbusTubPrescr' => array(
				array('field' => 'MorbusTubPrescr_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubPrescrTimetable' => array(
				array('field' => 'MorbusTubPrescrTimetable_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_id','label' => 'Лекарственное назначение','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubPrescrTimetable_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'MorbusTubPrescrTimetable_IsExec','label' => 'отметка о выполнении','rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id','label' => 'Врач выполнивший назначение','rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'updateMorbusTubPrescrTimetable' => array(
				array('field' => 'MorbusTubPrescrTimetable_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_id','label' => 'Лекарственное назначение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubPrescrTimetable_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubPrescrTimetable_IsExec','label' => 'отметка о выполнении','rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id','label' => 'Врач выполнивший назначение','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'getMorbusTubPrescrTimetable' => array(
				array('field' => 'MorbusTubPrescrTimetable_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubPrescr_id','label' => 'Лекарственное назначение','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubStudyResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'TubStageChemType_id','label' => 'Месяц/фаза','rules' => '', 'type' => 'id'),
				array('field' => 'PersonWeight_id','label' => 'Вес человека','rules' => '', 'type' => 'id'),
				array('field' => 'PersonWeight_Weight','label' => 'Вес человека','rules' => '', 'type' => 'id')
			),
			'getMorbusTubStudyResult' => array(
				array('field' => 'MorbusTubStudyResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Заболевание','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubStudyDrugResult' => array(
				//? •	MorbusTub_id 
				array('field' => 'MorbusTubStudyDrugResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyDrugResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubDrug_id','label' => 'Тип препарата','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyDrugResult_IsResult','label' => 'Результат теста','rules' => '', 'type' => 'id'),
			),
			'updateMorbusTubStudyDrugResult' => array(
				array('field' => 'MorbusTubStudyDrugResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyDrugResult_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'TubDrug_id','label' => 'Тип препарата','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyDrugResult_IsResult','label' => 'Результат теста','rules' => '', 'type' => 'id'),
			),
			'getMorbusTubStudyDrugResult' => array(
				array('field' => 'MorbusTubStudyDrugResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubMolecular' => array(
				array('field' => 'MorbusTubMolecular_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubMolecular_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'MorbusTubMolecularType_id','label' => 'Тест на лекарственную устойчивость','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubMolecular_IsResult','label' => 'Результат теста','rules' => 'required', 'type' => 'id')
			),
			'updateMorbusTubMolecular' => array(
				array('field' => 'MorbusTubMolecular_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubMolecular_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubMolecularType_id','label' => 'Тест на лекарственную устойчивость','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubMolecular_IsResult','label' => 'Результат теста','rules' => '', 'type' => 'id')
			),
			'getMorbusTubMolecular' => array(
				array('field' => 'MorbusTubMolecular_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubStudyMicrosResult' => array(
				array('field' => 'MorbusTubStudyMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyMicrosResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'MorbusTubStudyMicrosResult_NumLab','label' => '№ образца мокроты','rules' => 'max_length[10]', 'type' => 'string'),
				array('field' => 'TubMicrosResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyMicrosResult_EdResult','label' => 'Число микобактерий','rules' => '', 'type' => 'int'),
			),
			'updateMorbusTubStudyMicrosResult' => array(
				array('field' => 'MorbusTubStudyMicrosResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyMicrosResult_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusTubStudyMicrosResult_NumLab','label' => '№ образца мокроты','rules' => 'max_length[10]', 'type' => 'string'),
				array('field' => 'TubMicrosResultType_id','label' => 'Результат','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyMicrosResult_EdResult','label' => 'Число микобактерий','rules' => '', 'type' => 'int'),
			),
			'getMorbusTubStudyMicrosResult' => array(
				array('field' => 'MorbusTubStudyMicrosResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Простое заболевание: Туберкулез','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubStudySeedResult' => array(
				array('field' => 'MorbusTubStudySeedResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudySeedResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubSeedResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
			),
			'updateMorbusTubStudySeedResult' => array(
				array('field' => 'MorbusTubStudySeedResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudySeedResult_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'TubSeedResultType_id','label' => 'Результат','rules' => '', 'type' => 'id'),
			),
			'getMorbusTubStudySeedResult' => array(
				array('field' => 'MorbusTubStudySeedResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Простое заболевание: Туберкулез','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubStudyHistolResult' => array(
				array('field' => 'MorbusTubStudyHistolResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyHistolResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubDiagnosticMaterialType_id','label' => 'Диагностический материал','rules' => 'required', 'type' => 'id'),
				array('field' => 'TubHistolResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
			),
			'updateMorbusTubStudyHistolResult' => array(
				array('field' => 'MorbusTubStudyHistolResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyHistolResult_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'TubDiagnosticMaterialType_id','label' => 'Диагностический материал','rules' => '', 'type' => 'id'),
				array('field' => 'TubHistolResultType_id','label' => 'Результат','rules' => '', 'type' => 'id'),
			),
			'getMorbusTubStudyHistolResult' => array(
				array('field' => 'MorbusTubStudyHistolResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Простое заболевание: Туберкулез','rules' => '', 'type' => 'id')
			),
			'saveMorbusTubStudyXrayResult' => array(
				array('field' => 'MorbusTubStudyXrayResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyXrayResult_setDT','label' => 'Дата','rules' => 'required', 'type' => 'date'),
				array('field' => 'TubXrayResultType_id','label' => 'Результат','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyXrayResult_Comment','label' => 'Примечание','rules' => 'max_length[30]', 'type' => 'string'),
			),
			'updateMorbusTubStudyXrayResult' => array(
				array('field' => 'MorbusTubStudyXrayResult_id','label' => 'Идентификатор','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyXrayResult_setDT','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' => 'TubXrayResultType_id','label' => 'Результат','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyXrayResult_Comment','label' => 'Примечание','rules' => 'max_length[30]', 'type' => 'string'),
			),
			'getMorbusTubStudyXrayResult' => array(
				array('field' => 'MorbusTubStudyXrayResult_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTubStudyResult_id','label' => 'Результаты исследований','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusTub_id','label' => 'Простое заболевание: Туберкулез','rules' => '', 'type' => 'id')
			),
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getMorbusTub');
		if(empty($data['MorbusTub_id']) && empty($data['Person_id'])){
			return array(
				'Error_Msg' => 'Не переданы параметры'
			);
		}

		$resp = $this->dbmodel->getMorbusTubAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response($resp);
	}
	
	/**
	 * Создание специфики по туберкулезу
	 */
	function index_post(){
		$this->response(array(
			'error_code' => 1,
			'Error_Msg' => 'Метод не предусмотрен'
		));
		/*
		$data = $this->ProcessInputData('save', null, true);
		
		$res = $this->dbmodel->saveMorbusTubAPI($data);
		
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res['MorbusTub_id'])){
			$returnArr = array(
				'error_code' => 0,
				'EvnNotifyTub_id' => $res['MorbusTub_id']
			);
			if(!empty($res['PersonRegister_id'])){
				$returnArr['PersonRegister_id'] = $res['PersonRegister_id'];
			}
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res['Error_Msg'])) ? $res['Error_Msg'] : 'ошибка создания данных специфики по туберкулезу'
			));
		}
		 */
	}
	
	/**
	 * Изменение специфики по туберкулезу
	 */
	function index_put(){
		$data = $this->ProcessInputData('update', null, true);
		
		$res = $this->dbmodel->updateMorbusTubAPI($data);
		if(!empty($res[0]['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$error_msg = (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактировании данных специфики по туберкулезу';
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res['Error_Msg'])) ? $res['Error_Msg'] : $error_msg
			));
		}
	}
	
	/**
	 * Создание записи Генерализированные формы в рамках специфики по туберкулезу
	 */
	function TubDiagGeneralForm_post(){
		$data = $this->ProcessInputData('saveTubDiagGeneralForm', null, true);
		if(empty($data['MorbusTub_id']) || empty($data['Diag_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->saveTubDiagGeneralForm($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['TubDiagGeneralForm_id'])){
			$returnArr = array(
				'error_code' => 0,
				'TubDiagGeneralForm_id' => $res[0]['TubDiagGeneralForm_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания записи Генерализированные формы в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение записи Генерализированные формы в рамках специфики по туберкулезу
	 */
	function TubDiagGeneralForm_put(){
		$data = $this->ProcessInputData('saveTubDiagGeneralForm', null, true);
		if(empty($data['TubDiagGeneralForm_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		$res = $this->dbmodel->updateTubDiagGeneralFormAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['TubDiagGeneralForm_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования записи Генерализированные формы в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение записи Генерализированные формы в рамках специфики по туберкулезу
	 */
	function TubDiagGeneralForm_get(){
		$data = $this->ProcessInputData('saveTubDiagGeneralForm', null, true);
		if(empty($data['MorbusTub_id']) && empty($data['TubDiagGeneralForm_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubDiagGeneralFormAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание записи режима химиотерапии в рамках специфики по туберкулеза
	 */
	function MorbusTubConditChem_post(){
		$data = $this->ProcessInputData('saveMorbusTubConditChem', null, true);
		if(empty($data['MorbusTub_id']) || empty($data['MorbusTubConditChem_BegDate']) || empty($data['TubStandartConditChemType_id']) || empty($data['TubVenueType_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->saveMorbusTubConditChem($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubConditChem_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubConditChem_id' => $res[0]['MorbusTubConditChem_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания записи режима химиотерапии в рамках специфики по туберкулеза'
			));
		}
	}
	
	/**
	 * Изменение записи режима химиотерапии в рамках специфики по туберкулезу
	 */
	function MorbusTubConditChem_put(){
		$data = $this->ProcessInputData('saveMorbusTubConditChem', null, true);
		if(empty($data['MorbusTubConditChem_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		$res = $this->dbmodel->updateMorbusTubConditChemAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubConditChem_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования записи режима химиотерапии в рамках специфики по туберкулеза'
			));
		}
	}
	
	/**
	 * Получение записи режима химиотерапии в рамках специфики по туберкулезу
	 */
	function MorbusTubConditChem_get(){
		$data = $this->ProcessInputData('saveMorbusTubConditChem', null, true);
		if(empty($data['MorbusTub_id']) && empty($data['MorbusTubConditChem_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubConditChemAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание консультации фтизиохирурга в рамках специфики по туберкулезу
	 */
	function MorbusTubAdvice_post(){
		$data = $this->ProcessInputData('saveMorbusTubAdvice', null, true);
		
		$res = $this->dbmodel->saveMorbusTubAdvice($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubAdvice_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubAdvice_id' => $res[0]['MorbusTubAdvice_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания записи Генерализированные формы в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение консультации фтизиохирурга в рамках специфики по туберкулезу
	 */
	function MorbusTubAdvice_put(){
		$data = $this->ProcessInputData('updateMorbusTubAdvice', null, true);
		
		$res = $this->dbmodel->updateMorbusTubAdviceAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubAdvice_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования записи Генерализированные формы в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение консультации фтизиохирурга в рамках специфики по туберкулезу
	 */
	function MorbusTubAdvice_get(){
		$data = $this->ProcessInputData('getMorbusTubAdvice', null, true);
		if(empty($data['MorbusTub_id']) && empty($data['MorbusTubAdvice_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubAdviceAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание записи об оперативном лечении в рамках специфики по туберкулезу
	 */
	function MorbusTubAdviceOper_post(){
		$data = $this->ProcessInputData('saveMorbusTubAdviceOper', null, true);
		
		$res = $this->dbmodel->saveMorbusTubAdviceOper($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubAdviceOper_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubAdviceOper_id' => $res[0]['MorbusTubAdviceOper_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания записи об оперативном лечении в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение записи об оперативном лечении в рамках специфики по туберкулезу
	 */
	function MorbusTubAdviceOper_put(){
		$data = $this->ProcessInputData('updateMorbusTubAdviceOper', null, true);
		
		$res = $this->dbmodel->updateMorbusTubAdviceOperAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubAdviceOper_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования записи об оперативном лечении в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение записи об оперативном лечении в рамках специфики по туберкулезу
	 */
	function MorbusTubAdviceOper_get(){
		$data = $this->ProcessInputData('getMorbusTubAdviceOper', null, true);
		if(empty($data['MorbusTub_id']) && empty($data['MorbusTubAdviceOper_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubAdviceOperAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание направления на проведение микроскопических исследований на туберкулез
	 */
	function EvnDirectionTub_post(){
		$data = $this->ProcessInputData('saveEvnDirectionTub', null, true);
		
		$res = $this->dbmodel->saveEvnDirectionTubAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnDirectionTub_id'])){
			$returnArr = array(
				'error_code' => 0,
				'EvnDirectionTub_id' => $res[0]['EvnDirectionTub_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания направления на проведение микроскопических исследований на туберкулез'
			));
		}
	}
	
	/**
	 * Изменение записи направления на проведение микроскопических исследований на туберкулез
	 */
	function EvnDirectionTub_put(){
		$data = $this->ProcessInputData('updateEvnDirectionTub', null, true);
		
		$res = $this->dbmodel->updateEvnDirectionTubAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnDirectionTub_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования направления на проведение микроскопических исследований на туберкулез'
			));
		}
	}
	
	/**
	 * Получение записи направления на проведение микроскопических исследований на туберкулез
	 */
	function EvnDirectionTub_get(){
		$data = $this->ProcessInputData('getEvnDirectionTub', null, true);
		if(empty($data['MorbusTub_id']) && empty($data['EvnDirectionTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getEvnDirectionTubAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание результатов микроскопических исследований в рамках специфики по туберкулезу
	 */
	function TubMicrosResult_post(){
		$data = $this->ProcessInputData('saveTubMicrosResult', null, true);
		
		$res = $this->dbmodel->saveTubMicrosResult($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['TubMicrosResult_id'])){
			$returnArr = array(
				'error_code' => 0,
				'TubMicrosResult_id' => $res[0]['TubMicrosResult_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания направления на проведение микроскопических исследований на туберкулез'
			));
		}
	}
	
	/**
	 * Изменение результатов микроскопических исследований в рамках специфики по туберкулезу
	 */
	function TubMicrosResult_put(){
		$data = $this->ProcessInputData('updateTubMicrosResult', null, true);
		
		$res = $this->dbmodel->updateTubMicrosResultAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['TubMicrosResult_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования направления на проведение микроскопических исследований на туберкулез'
			));
		}
	}
	
	/**
	 * Получение результатов микроскопических исследований в рамках специфики по туберкулезу
	 */
	function TubMicrosResult_get(){
		$data = $this->ProcessInputData('getTubMicrosResult', null, true);
		if(empty($data['TubMicrosResult_id']) && empty($data['EvnDirectionTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getTubMicrosResultAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}	
	
	/**
	 * Создание лекарственных назначений в рамках специфики по туберкулезу
	 */
	function MorbusTubPrescr_post(){
		$data = $this->ProcessInputData('saveMorbusTubPrescr', null, true);
		
		$res = $this->dbmodel->saveMorbusTubPrescr($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubPrescr_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubPrescr_id' => $res[0]['MorbusTubPrescr_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания лекарственных назначений в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение лекарственных назначений в рамках специфики по туберкулезу
	 */
	function MorbusTubPrescr_put(){
		$data = $this->ProcessInputData('updateMorbusTubPrescr', null, true);
		
		$res = $this->dbmodel->updateMorbusTubPrescrAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubPrescr_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования лекарственных назначений в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение лекарственных назначений в рамках специфики по туберкулезу
	 */
	function MorbusTubPrescr_get(){
		$data = $this->ProcessInputData('getMorbusTubPrescr', null, true);
		if(empty($data['MorbusTubPrescr_id']) && empty($data['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubPrescrAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание графика исполнения назначения процедур в рамках специфики по туберкулезу
	 */
	function MorbusTubPrescrTimetable_post(){
		$data = $this->ProcessInputData('saveMorbusTubPrescrTimetable', null, true);
		
		$res = $this->dbmodel->saveMorbusTubPrescrTimetable($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubPrescrTimetable_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubPrescrTimetable_id' => $res[0]['MorbusTubPrescrTimetable_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания графика исполнения назначения процедур в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение графика исполнения назначения процедур в рамках специфики по туберкулезу
	 */
	function MorbusTubPrescrTimetable_put(){
		$data = $this->ProcessInputData('updateMorbusTubPrescrTimetable', null, true);
		
		$res = $this->dbmodel->updateMorbusTubPrescrTimetableAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubPrescrTimetable_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования графика исполнения назначения процедур в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение графика исполнения назначения процедур в рамках специфики по туберкулезу
	 */
	function MorbusTubPrescrTimetable_get(){
		$data = $this->ProcessInputData('getMorbusTubPrescrTimetable', null, true);
		if(empty($data['MorbusTubPrescrTimetable_id']) && empty($data['MorbusTubPrescr_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubPrescrTimetableAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание результатов исследования в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyResult_post(){
		$data = $this->ProcessInputData('saveMorbusTubStudyResult', null, true);
		if(empty($data['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не передан параметр MorbusTub_id'
			));
		}
		$res = $this->dbmodel->saveMorbusTubStudyResult($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubStudyResult_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubStudyResult_id' => $res[0]['MorbusTubStudyResult_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания результатов исследования в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение результатов исследования в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyResult_put(){
		$data = $this->ProcessInputData('saveMorbusTubStudyResult', null, true);
		if(empty($data['MorbusTubStudyResult_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не передан параметр MorbusTubStudyResult_id'
			));
		}
		$res = $this->dbmodel->saveMorbusTubStudyResultAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования результатов исследования в рамках специфики по туберкулезу'
			));
		}elseif(!empty($res[0]['MorbusTubStudyResult_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'ошибка редактирования результатов исследования в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение результатов исследования в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyResult_get(){
		$data = $this->ProcessInputData('getMorbusTubStudyResult', null, true);
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubStudyResultAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyDrugResult_post(){
		$data = $this->ProcessInputData('saveMorbusTubStudyDrugResult', null, true);
		
		$res = $this->dbmodel->saveMorbusTubStudyDrugResult($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubStudyDrugResult_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubStudyDrugResult_id' => $res[0]['MorbusTubStudyDrugResult_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyDrugResult_put(){
		$data = $this->ProcessInputData('updateMorbusTubStudyDrugResult', null, true);
		
		$res = $this->dbmodel->updateMorbusTubStudyDrugResultAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу'
			));
		}elseif(!empty($res[0]['MorbusTubStudyDrugResult_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'ошибка редактирования результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyDrugResult_get(){
		$data = $this->ProcessInputData('getMorbusTubStudyDrugResult', null, true);
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyDrugResult_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubStudyDrugResultAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание молекулярно–генетические методов в рамках специфики по туберкулезу
	 */
	function MorbusTubMolecular_post(){
		$data = $this->ProcessInputData('saveMorbusTubMolecular', null, true);
		if(!empty($data['MorbusTubStudyDrugResult_IsResult']) && !in_array($data['MorbusTubStudyDrugResult_IsResult'], array(1,2))){
			$returnArr = array(
				'error_code' => 1,
				'Error_Msg' => 'параметр MorbusTubStudyDrugResult_IsResult может иметь только значения 1 или 2'
			);
		}
		
		$res = $this->dbmodel->saveMorbusTubMolecular($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubMolecular_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubMolecular_id' => $res[0]['MorbusTubMolecular_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания молекулярно–генетические методов в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение молекулярно–генетические методов в рамках специфики по туберкулезу
	 */
	function MorbusTubMolecular_put(){
		$data = $this->ProcessInputData('updateMorbusTubMolecular', null, true);
		if(!empty($data['MorbusTubStudyDrugResult_IsResult']) && !in_array($data['MorbusTubStudyDrugResult_IsResult'], array(1,2))){
			$returnArr = array(
				'error_code' => 1,
				'Error_Msg' => 'параметр MorbusTubStudyDrugResult_IsResult может иметь только значения 1 или 2'
			);
		}
		
		$res = $this->dbmodel->updateMorbusTubMolecularAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования молекулярно–генетические методов в рамках специфики по туберкулезу'
			));
		}elseif(!empty($res[0]['MorbusTubMolecular_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'ошибка редактирования молекулярно–генетические методов в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение молекулярно–генетические методов в рамках специфики по туберкулезу
	 */
	function MorbusTubMolecular_get(){
		$data = $this->ProcessInputData('getMorbusTubMolecular', null, true);
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubMolecular_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubMolecularAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание микроскопии в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyMicrosResult_post(){
		$data = $this->ProcessInputData('saveMorbusTubStudyMicrosResult', null, true);
		
		$res = $this->dbmodel->saveMorbusTubStudyMicrosResult($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubStudyMicrosResult_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubStudyMicrosResult_id' => $res[0]['MorbusTubStudyMicrosResult_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания микроскопии в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение микроскопии в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyMicrosResult_put(){
		$data = $this->ProcessInputData('updateMorbusTubStudyMicrosResult', null, true);
		
		$res = $this->dbmodel->updateMorbusTubStudyMicrosResultAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования микроскопии в рамках специфики по туберкулезу'
			));
		}elseif(!empty($res[0]['MorbusTubStudyMicrosResult_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'ошибка редактирования микроскопии в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение микроскопии в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyMicrosResult_get(){
		$data = $this->ProcessInputData('getMorbusTubStudyMicrosResult', null, true);
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyMicrosResult_id']) && empty($data['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubStudyMicrosResultAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание результатов посева в рамках специфики по туберкулезу
	 */
	function MorbusTubStudySeedResult_post(){
		$data = $this->ProcessInputData('saveMorbusTubStudySeedResult', null, true);
		
		$res = $this->dbmodel->saveMorbusTubStudySeedResult($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubStudySeedResult_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubStudySeedResult_id' => $res[0]['MorbusTubStudySeedResult_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания результатов посева в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение результатов посева в рамках специфики по туберкулезу
	 */
	function MorbusTubStudySeedResult_put(){
		$data = $this->ProcessInputData('updateMorbusTubStudySeedResult', null, true);
		
		$res = $this->dbmodel->updateMorbusTubStudySeedResultAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования результатов посева в рамках специфики по туберкулезу'
			));
		}elseif(!empty($res[0]['MorbusTubStudySeedResult_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'ошибка редактирования результатов посева в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение результатов посева в рамках специфики по туберкулезу
	 */
	function MorbusTubStudySeedResult_get(){
		$data = $this->ProcessInputData('getMorbusTubStudySeedResult', null, true);
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudySeedResult_id']) && empty($data['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubStudySeedResultAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание результатов гистологии в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyHistolResult_post(){
		$data = $this->ProcessInputData('saveMorbusTubStudyHistolResult', null, true);
		
		$res = $this->dbmodel->saveMorbusTubStudyHistolResult($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubStudyHistolResult_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubStudyHistolResult_id' => $res[0]['MorbusTubStudyHistolResult_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания результатов гистологии в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение результатов гистологии в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyHistolResult_put(){
		$data = $this->ProcessInputData('updateMorbusTubStudyHistolResult', null, true);
		
		$res = $this->dbmodel->updateMorbusTubStudyHistolResultAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования результатов гистологии в рамках специфики по туберкулезу'
			));
		}elseif(!empty($res[0]['MorbusTubStudyHistolResult_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'ошибка редактирования результатов гистологии в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение результатов гистологии в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyHistolResult_get(){
		$data = $this->ProcessInputData('getMorbusTubStudyHistolResult', null, true);
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyHistolResult_id']) && empty($data['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubStudyHistolResultAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание результатов рентгена в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyXrayResult_post(){
		$data = $this->ProcessInputData('saveMorbusTubStudyXrayResult', null, true);
		
		$res = $this->dbmodel->saveMorbusTubStudyXrayResult($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['MorbusTubStudyXrayResult_id'])){
			$returnArr = array(
				'error_code' => 0,
				'MorbusTubStudyXrayResult_id' => $res[0]['MorbusTubStudyXrayResult_id']
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка создания результатов рентгена в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Изменение результатов рентгена в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyXrayResult_put(){
		$data = $this->ProcessInputData('updateMorbusTubStudyXrayResult', null, true);
		
		$res = $this->dbmodel->updateMorbusTubStudyXrayResultAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка редактирования результатов рентгена в рамках специфики по туберкулезу'
			));
		}elseif(!empty($res[0]['MorbusTubStudyXrayResult_id'])){
			$returnArr = array(
				'error_code' => 0
			);
			$this->response($returnArr);
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'ошибка редактирования результатов рентгена в рамках специфики по туберкулезу'
			));
		}
	}
	
	/**
	 * Получение результатов рентгена в рамках специфики по туберкулезу
	 */
	function MorbusTubStudyXrayResult_get(){
		$data = $this->ProcessInputData('getMorbusTubStudyXrayResult', null, true);
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyXrayResult_id']) && empty($data['MorbusTub_id'])){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'не переданы обязательные параметры '
			));
		}
		
		$res = $this->dbmodel->getMorbusTubStudyXrayResultAPI($data);
		if(is_array($res) && count($res)>0){
			$this->response($res);
		}else{
			$this->response(0);
		}
	}
}