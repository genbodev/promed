<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusCrazy - Специфика по психиатрии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *-TODO: Do some explanation, preamble and describing
 * @package      Foobaring
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Markoff
 * @version      2012-10
 */
/**
 * @property MorbusCrazy_model $dbmodel
 */
class MorbusCrazy extends swController
{
    var $model_name = "MorbusCrazy_model";

	/**
	 * fg
	 */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model($this->model_name, 'dbmodel');
        $this->inputRules = array(
            'load' => array(
                array('field' => 'Morbus_id'                          ,'label' => 'Идентификатор заболевания'                                                  ,'rules' => 'required', 'type' => 'id'),
                array('field' => 'Evn_id'                             ,'label' => 'Идентификатор учетного документа'                                           ,'rules' => 'required', 'type' => 'id'),
            ),
            'loadMorbusCrazyDiag' => array(
				array('field' => 'MorbusCrazyDiag_id','label' => 'Идентификатор диагноза','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyDiag_id','label' => 'Диагноз','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyDiagDepend_id', 'label' => 'Код зависимости', 'rules' => '', 'type' => 'id'),
				array('field' => 'Diag_sid','label' => 'Диагноз основного заболевания','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyDiag_setDT','label' => 'Дата установления (пересмотра)','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyDynamicsObserv' => array(
				array('field' => 'MorbusCrazyDynamicsObserv_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_sid','label' => 'ЛПУ наблюдения','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyAmbulMonitoringType_id','label' => 'Вид амбулаторной помощи','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyDynamicsObserv_setDT','label' => 'Помощь оказывается с даты','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyVizitCheck' => array(
				array('field' => 'MorbusCrazyVizitCheck_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyVizitCheck_setDT','label' => 'Назначено','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyVizitCheck_vizitDT','label' => 'Явился','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyDynamicsState' => array(
				array('field' => 'MorbusCrazyDynamicsState_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyDynamicsState_begDT','label' => 'Дата начала ремиссии','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyDynamicsState_endDT','label' => 'Дата окончания ремиссии','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyBasePS' => array(
				array('field' => 'MorbusCrazyBasePS_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyPurposeHospType_id','label' => 'Цель госпитализациии','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyPurposeDirectType_id','label' => 'Цель направления','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBasePS_setDT','label' => 'Дата поступления','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyBasePS_disDT','label' => 'Дата выбытия','rules' => '', 'type' => 'date'),
				array('field' => 'CrazyDiag_id','label' => 'Диагноз','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Код основного заболевания','rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id','label' => 'ЛПУ','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyHospType_id','label' => 'Госпитализирован','rules' => '', 'type' => 'id'),
				array('field' => 'CrazySupplyType_id','label' => 'Поступление','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyDirectType_id','label' => 'Кем направлен','rules' => '', 'type' => 'id'),
				array('field' => 'CrazySupplyOrderType_id','label' => 'Порядок поступления','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyDirectFromType_id','label' => 'Откуда поступил','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyJudgeDecisionArt35Type_id','label' => 'Решение судьи по ст. 35','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyLeaveType_id','label' => 'Выбыл','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
				// MorbusCrazyBasePS - не хватает "Цель направления" и "Выбыл"
			),
			'loadMorbusCrazyForceTreat' => array(
				array('field' => 'MorbusCrazyForceTreat_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyForceTreat_begDT','label' => 'Дата начала','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyForceTreat_endDT','label' => 'Дата окончания','rules' => '', 'type' => 'date'),
				array('field' => 'CrazyForceTreatType_id','label' => 'Вид принудительного лечения','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyUpForceTreat' => array(
				array('field' => 'MorbusCrazyUpForceTreat_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyForceTreat_id','label' => 'Принудительное лечение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyUpForceTreat_setDT','label' => 'Дата изменения','rules' => '', 'type' => 'date'),
				array('field' => 'CrazyForceTreatType_id','label' => 'Вид принудительного лечения','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyPersonSurveyHIV' => array(
				array('field' => 'MorbusCrazyPersonSurveyHIV_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_id','label' => 'Заболевание человека','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPersonSurveyHIV_setDT','label' => 'Дата обследования','rules' => '', 'type' => 'date'),
				array('field' => 'CrazySurveyHIVType_id','label' => 'Результат обследования','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyNdOsvid' => array(
				array('field' => 'MorbusCrazyNdOsvid_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyNdOsvid_setDT','label' => 'Дата прохождения','rules' => '', 'type' => 'date'),
				array('field' => 'Lpu_id','label' => 'ЛПУ','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyPersonStick' => array(
				array('field' => 'MorbusCrazyPersonStick_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_id','label' => 'Заболевание человека','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPersonStick_setDT','label' => 'Дата открытия больничного листа','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyPersonStick_disDT','label' => 'Дата закрытия больничного листа','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyPersonStick_Article','label' => 'Статья УК','rules' => '', 'type' => 'string'),
				array('field' => 'Diag_id','label' => 'Диагноз','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyPersonSuicidalAttempt' => array(
				array('field' => 'MorbusCrazyPersonSuicidalAttempt_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_id','label' => 'Заболевание человека','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPersonSuicidalAttempt_setDT','label' => 'Дата совершения','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyPersonSocDangerAct' => array(
				array('field' => 'MorbusCrazyPersonSocDangerAct_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_id','label' => 'Заболевание человека','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPersonSocDangerAct_setDT','label' => 'Дата совершения','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyPersonSocDangerAct_Article','label' => 'Статья УК','rules' => '', 'type' => 'string'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyBaseDrugStart' => array(
				array('field' => 'MorbusCrazyBaseDrugStart_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBaseDrugStart_Name','label' => 'Наименование вещества','rules' => '', 'type' => 'string'),
				array('field' => 'CrazyDrugReceptType_id','label' => 'Тип приема','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBaseDrugStart_Age','label' => 'Полных лет','rules' => '', 'type' => 'int'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyDrug' => array(
				array('field' => 'MorbusCrazyDrug_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyDrug_Name','label' => 'Наименование','rules' => '', 'type' => 'string'),
				array('field' => 'CrazyDrugType_id','label' => 'Вид вещества','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyDrugReceptType_id','label' => 'Тип приема','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyDrugVolume' => array(
				array('field' => 'MorbusCrazyDrugVolume_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id','label' => 'ЛПУ оказавшее помощь','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyDrugVolume_setDT','label' => 'Дата оказания помощи','rules' => '', 'type' => 'date'),
				array('field' => 'CrazyDrugVolumeType_id','label' => 'Тип объема наркологической помощи','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyBBK' => array(
				array('field' => 'MorbusCrazyBBK_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBBK_setDT','label' => 'Дата осмотра','rules' => '', 'type' => 'date'),
				array('field' => 'CrazyDiag_id','label' => 'Диагноз предварительный','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBBK_firstDT','label' => 'Дата установки диагноза','rules' => '', 'type' => 'date'),
				array('field' => 'MedicalCareType_id','label' => 'ВВК','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyDiag_lid','label' => 'Заключительный диагноз','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBBK_lidDT','label' => 'Дата установки заключительного диагноза','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusCrazyPersonInvalid' => array(
				array('field' => 'MorbusCrazyPersonInvalid_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPersonInvalid_setDT','label' => 'Дата установления (пересмотра)','rules' => '', 'type' => 'date'),
				array('field' => 'InvalidGroupType_id','label' => 'Группа инвалидности','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPersonInvalid_reExamDT','label' => 'Срок очередного переосвидетельствования','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyPersonInvalid_Article','label' => 'Статья УК','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyWorkPlaceType_id','label' => 'Место работы','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'saveMorbusCrazy' => array(
				array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				array('field' => 'Morbus_setDT','label' => 'Дата начала заболевания','rules' => '', 'type' => 'date'),
				array('field' => 'Diag_nid','label' => 'Сопутствующее психическое (наркологическое) заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_sid','label' => 'Сопутствующее соматическое (в т.ч. неврологическое) заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyResultDeseaseType_id','label' => 'Исход заболевания','rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegister_id','label' => 'Идентификатор записи регистра','rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegister_setDate','label' => 'Дата включения в регистр','rules' => '', 'type' => 'date'),
				array('field' => 'Morbus_disDT','label' => 'Дата закрытия карты (снятия с учета)','rules' => '', 'type' => 'date'),
				array('field' => 'CrazyCauseEndSurveyType_id','label' => 'Причина прекращения наблюдения','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_LTMDayCount','label' => 'Число дней работы в ЛТМ','rules' => '', 'type' => 'int'),
				array('field' => 'MorbusCrazyBase_HolidayDayCount','label' => 'Число дней лечебных отпусков','rules' => '', 'type' => 'int'),
				array('field' => 'MorbusCrazyBase_HolidayCount','label' => 'Число лечебных отпусков (за период госпитализации)','rules' => '', 'type' => 'int'),
				array('field' => 'MorbusCrazyPerson_IsWowInvalid','label' => 'Инвалид ВОВ','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_IsWowMember','label' => 'Участник ВОВ','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyEducationType_id','label' => 'Образование','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_CompleteClassCount','label' => 'Число законченных классов среднеобразовательного учреждения','rules' => '', 'type' => 'int'),
				array('field' => 'MorbusCrazyPerson_IsEducation','label' => 'Учится','rules' => '', 'type' => 'int'),
				array('field' => 'CrazySourceLivelihoodType_id','label' => 'Источник средств существования','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyResideType_id','label' => 'Проживает','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyResideConditionsType_id','label' => 'Условия проживания','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_firstDT','label' => 'Дата обращения к психиатру (наркологу) впервые в жизни','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazyPerson_IsConvictionBeforePsych','label' => 'Судимости до обращения к психиатру (наркологу)','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_DeathDT','label' => 'Дата смерти','rules' => '', 'type' => 'date'),
				array('field' => 'CrazyDeathCauseType_id','label' => 'Причина смерти','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_IsUseAlienDevice','label' => 'Использование чужих шприцов, игл, приспособлений','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_IsLivingConsumDrug','label' => 'Проживание с потребителем психоактивных средств','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_id','label' => 'Заболевание по психиатрии','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusBase_id','label' => 'Заболевание (базовое)','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyBase_id','label' => 'Заболевание по психиатрии (базовое)','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazyPerson_id','label' => 'Заболевание по психиатрии (персональное)','rules' => '', 'type' => 'id'),
				array('field' => 'Person_id','label' => 'Пациент','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз записи регистра, из которого редактируется','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_pid','label' => 'Учетный документ, из которого редактируется','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusType_id','label' => 'Вид заболевания','rules' => '', 'type' => 'id'),
				array('field' => 'Mode','label' => 'Режим сохранения','rules' => '', 'type' => 'string')
			),
			'getCrazyDiag' => array(
				array('field' => 'CrazyDiag_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'МКБ-10','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyDiag_Code','label' => 'Код','rules' => '', 'type' => 'string'),
				array('field' => 'CrazyDiag_Name','label' => 'Наименование','rules' => '', 'type' => 'string'),
				array('field' => 'query','label' => 'Запрос','rules' => '', 'type' => 'string'),
				array('field' => 'date','label' => 'Дата','rules' => '', 'type' => 'date'),
				array('field' =>'type','label'=>'тип регистра', 'rules'=>'','type'=>'string')
			),
			'setCauseEndSurveyType' => array(
				array('field' => 'Morbus_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'CrazyCauseEndSurveyType_id','label' => 'Причина прекращения наблюдения','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusCrazy_CardEndDT','label' => 'Дата закрытия карты','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusCrazy_DeRegDT','label' => 'Дата снятия с учета','rules' => '', 'type' => 'date')
			)
        );
    }

	/**
	 *
	 * @return type 
	 */
    function save()
    {
        $data = $this->ProcessInputData('save', true);
        if ($data) {
            $this->dbmodel->assign($data);
            $response = $this->dbmodel->save();
            $this->ProcessModelSave($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 *
	 * @return type 
	 */
    function load() {
   		$data = $this->ProcessInputData('load', true);
   		if ($data) {
            $response = $this->dbmodel->load($data['Morbus_id'], $data['Evn_id']);
   			$this->ProcessModelList(array($response), true, true)->formatDatetimeFields()->ReturnData();
   			return true;
   		} else {
   			return false;
   		}
   	}

	/**
	 *
	 * @return type 
	 */
    function loadMorbusCrazyDiag() {
		$data = $this->ProcessInputData('loadMorbusCrazyDiag', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyDiagViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyDiag() {
		$data = $this->ProcessInputData('loadMorbusCrazyDiag', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyDiag($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyDynamicsObserv() {
		$data = $this->ProcessInputData('loadMorbusCrazyDynamicsObserv', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyDynamicsObservViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyDynamicsObserv() {
		$data = $this->ProcessInputData('loadMorbusCrazyDynamicsObserv', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyDynamicsObserv($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyVizitCheck() {
		$data = $this->ProcessInputData('loadMorbusCrazyVizitCheck', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyVizitCheckViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyVizitCheck() {
		$data = $this->ProcessInputData('loadMorbusCrazyVizitCheck', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyVizitCheck($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyDynamicsState() {
		$data = $this->ProcessInputData('loadMorbusCrazyDynamicsState', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyDynamicsStateViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyDynamicsState() {
		$data = $this->ProcessInputData('loadMorbusCrazyDynamicsState', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyDynamicsState($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyBasePS() {
		$data = $this->ProcessInputData('loadMorbusCrazyBasePS', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyBasePSViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyBasePS() {
		$data = $this->ProcessInputData('loadMorbusCrazyBasePS', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyBasePS($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyForceTreat() {
		$data = $this->ProcessInputData('loadMorbusCrazyForceTreat', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyForceTreatViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyForceTreat() {
		$data = $this->ProcessInputData('loadMorbusCrazyForceTreat', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyForceTreat($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyUpForceTreat() {
		$data = $this->ProcessInputData('loadMorbusCrazyUpForceTreat', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyUpForceTreatViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyUpForceTreat() {
		$data = $this->ProcessInputData('loadMorbusCrazyUpForceTreat', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyUpForceTreat($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyPersonSurveyHIV() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonSurveyHIV', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyPersonSurveyHIVViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyPersonSurveyHIV() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonSurveyHIV', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyPersonSurveyHIV($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyNdOsvid() {
		$data = $this->ProcessInputData('loadMorbusCrazyNdOsvid', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyNdOsvidViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyNdOsvid() {
		$data = $this->ProcessInputData('loadMorbusCrazyNdOsvid', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyNdOsvid($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyPersonStick() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonStick', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyPersonStickViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyPersonStick() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonStick', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyPersonStick($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyPersonSuicidalAttempt() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonSuicidalAttempt', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyPersonSuicidalAttemptViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyPersonSuicidalAttempt() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonSuicidalAttempt', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyPersonSuicidalAttempt($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyPersonSocDangerAct() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonSocDangerAct', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyPersonSocDangerActViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyPersonSocDangerAct() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonSocDangerAct', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyPersonSocDangerAct($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyBaseDrugStart() {
		$data = $this->ProcessInputData('loadMorbusCrazyBaseDrugStart', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyBaseDrugStartViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyBaseDrugStart() {
		$data = $this->ProcessInputData('loadMorbusCrazyBaseDrugStart', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyBaseDrugStart($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyDrug() {
		$data = $this->ProcessInputData('loadMorbusCrazyDrug', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyDrugViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyDrug() {
		$data = $this->ProcessInputData('loadMorbusCrazyDrug', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyDrug($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyBBK() {
		$data = $this->ProcessInputData('loadMorbusCrazyBBK', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyBBKViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyBBK() {
		$data = $this->ProcessInputData('loadMorbusCrazyBBK', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyBBK($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyDrugVolume() {
		$data = $this->ProcessInputData('loadMorbusCrazyDrugVolume', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyDrugVolumeViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyDrugVolume() {
		$data = $this->ProcessInputData('loadMorbusCrazyDrugVolume', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyDrugVolume($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusCrazyPersonInvalid() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonInvalid', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusCrazyPersonInvalidViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusCrazyPersonInvalid() {
		$data = $this->ProcessInputData('loadMorbusCrazyPersonInvalid', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusCrazyPersonInvalid($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение специцики по психиатрии
	 */
	function saveMorbusCrazy()
	{
		$data = $this->ProcessInputData('saveMorbusCrazy', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusSpecific($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *
	 * @return type 
	 */
	function getCrazyDiag() {
		$data = $this->ProcessInputData('getCrazyDiag', true);
		if ($data) {
			$response = $this->dbmodel->getCrazyDiag($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Установка Причины прекращения наблюдения, Даты закрытия карты, Даты снятия с учета - используется для Уфы
	 * @return type 
	 */
    function setCauseEndSurveyType()
    {
        $data = $this->ProcessInputData('setCauseEndSurveyType', true);
        if ($data) {
            $response = $this->dbmodel->setCauseEndSurveyType($data);
            $this->ProcessModelSave($response, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

}
