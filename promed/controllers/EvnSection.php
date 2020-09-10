<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnSection - контроллер для работы с движением по отделениям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Stac
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author	Stas Bykov aka Savage (savage1981@gmail.com)
* @version			24.01.2012
*
* @property EvnPS_model $EvnPS_model
* @property BirthSpecStac_model BirthSpecStac_model
* @property MedSvid_model MedSvid_model
* @property PregnancySpec_model PregnancySpec_model
* @property EvnSection_model dbmodel
* @property Common_model commodel
* @property EvnLeave_model evnleavemodel
* @property EvnOtherSection_model evnothersectionmodel
* @property EvnOtherSectionBedProfile_model evnothersectionbedprofilemodel
* @property EvnDie_model evndiemodel
* @property HospitalWard_model Ward_model
* @property HospitalWard_model HospitalWard_model
* @property Registry_model Reg_model
* @property EvnOtherLpu_model evnotherlpumodel
* @property EvnOtherStac_model evnotherstacmodel
* @property EvnSection_model $EvnSection
* @property HospitalWard_model hmodel
* @property Morbus_model Morbus
* @property EvnDiag_model evndiagmodel
* @property EvnDirection_model dir_model
* @property Messages_model Messages_model
* @property MorbusOnkoLeave_model MorbusOnkoLeave
* @property Org_model orgmodel
*
*/
class EvnSection extends swController {

	public $inputRules = array(
		'saveEvnSectionKSGPaid' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type'  => 'id'),
			array('field' => 'mode', 'label' => 'Тип оплаты КСГ', 'rules' => '', 'type'  => 'string')
		),
		'loadEvnSectionKSGEditForm' => array(
			array('field' => 'EvnSectionKSG_id', 'label' => 'Идентификатор КСГ', 'rules' => 'required', 'type'  => 'id')
		),
		'saveEvnSectionKSG' => array(
			array('field' => 'EvnSectionKSG_id', 'label' => 'Идентификатор КСГ', 'rules' => 'required', 'type'  => 'id'),
			array('field' => 'EvnSectionKSG_begDate', 'label' => 'Дата начала', 'rules' => 'required', 'type'  => 'date'),
			array('field' => 'EvnSectionKSG_endDate', 'label' => 'Дата окончания', 'rules' => 'required', 'type'  => 'date')
		),
		'loadEvnSectionKSGList' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type'  => 'id'),
			array('field' => 'mode', 'label' => 'Тип оплаты КСГ', 'rules' => '', 'type'  => 'string')
		),
		'getLastEvnSection' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type'  => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type'  => 'id')
		),
		'setEvnSectionIndexNum' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type'  => 'id'),
			array('field' => 'EvnSection_IndexNum', 'label' => 'Номер группы', 'rules' => 'required', 'type'  => 'int')
		),
		'getEvnSectionIndexNum' => array(
			array('field' => 'EvnSection_pid', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type'  => 'id')
		),
		'checkIsEco' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type'  => 'id')
		),
		'recalcCoeffCTP' => array(
			array('field' => 'filterLpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type'  => 'id'),
			array('field' => 'filterEvn_id', 'label' => 'Идентификатор движения', 'rules' => '', 'type'  => 'id'),
			array('field' => 'filterCoeffOne', 'label' => 'Фильтр по движениям с КСЛП = 1', 'rules' => '', 'type'  => 'id'),
			array('field' => 'filterNotPaid', 'label' => 'Фильтр по неоплаченным движениям', 'rules' => '', 'type'  => 'id'),
			array('field' => 'recalcIndexNum', 'label' => 'Признак необходимости пересчёта IndexNum', 'rules' => '', 'type'  => 'id'),
			array('field' => 'begDate', 'label' => 'Дата начала периода', 'rules' => 'required', 'type'  => 'date'),
			array('field' => 'endDate', 'label' => 'Дата окончания периода', 'rules' => 'required', 'type'  => 'date'),
		),
		'recalcIndexNum' => array(
			array('field' => 'filterLpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type'  => 'id'),
			array('field' => 'filterEvn_id', 'label' => 'Идентификатор движения', 'rules' => '', 'type'  => 'id'),
			array('field' => 'begDate', 'label' => 'Дата начала периода', 'rules' => 'required', 'type'  => 'date'),
			array('field' => 'endDate', 'label' => 'Дата окончания периода', 'rules' => 'required', 'type'  => 'date'),
		),
		'getSectionPriemData' =>array(
		    array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getCSDuration'=>array(
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),array(
				'field' => 'MedicalCareKind_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),array(
				'field' => 'AgeGroupType_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),array(
				'field' => 'EvnSection_setDT',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'date'
			)
		),
		'getDiagPred' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getPriemDiag' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getEvnSectionDiag' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveEvnXmlDate' => array(
			array(
				'field' => 'EvnXml_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' 	=> 'id'
			),
			array(
				'field' => 'EvnXml_setDT',
				'label' => 'Дата и время направления',
				'rules' => 'required',
				'type' 	=> 'datetime'
			)
		),
		'loadKSGKPGKOEF' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HTMedicalCareClass_id',
				'label' => 'Метод ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_IndexRep',
				'label' => 'Признак повторной подачи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_IndexRepInReg',
				'label' => 'Признак повторной подачи в реестре',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagPriem_id',
				'label' => 'Идентификатор диагноза приёмного',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор родительского',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Идентификатор профиля',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Идентификатор типа группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата госпитализации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата выписки',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_IsPriem',
				'label' => 'Признак движения в приёмном',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_SofaScalePoints',
				'label' => 'Оценка по шкале органной недостаточности c(SOFA)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CureResult_id',
				'label' => 'Итог лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugTherapyScheme_ids',
				'label' => 'Схема лекарственной терапии',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'MesDop_ids',
				'label' => 'Доп критерии',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'RehabScale_id',
				'label' => 'Оценка состояния по ШРМ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'Профиль койки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_IsAdultEscort',
				'label' => 'Сопровождается взрослым',
				'rules' => '',
				'type' => 'id'
			)
		),
		'checkMesOldUslugaComplexFields' => array(
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата госпитализации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата выписки',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Идентификатор типа группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'id'
			)
		),
		'checkDrugTherapySchemeLinks' => array(
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата госпитализации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата выписки',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Mes_id',
				'label' => 'КСГ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadKSGKPGKOEFCombo' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),array(
				'field' => 'HTMedicalCareClass_id',
				'label' => 'Метод ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_IndexRep',
				'label' => 'Признак повторной подачи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_IndexRepInReg',
				'label' => 'Признак повторной подачи в реестре',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagPriem_id',
				'label' => 'Идентификатор диагноза приёмного',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор родительского',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Идентификатор профиля',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Идентификатор типа группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата госпитализации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата выписки',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_IsPriem',
				'label' => 'Признак движения в приёмном',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_SofaScalePoints',
				'label' => 'Оценка по шкале органной недостаточности c(SOFA)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CureResult_id',
				'label' => 'Итог лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MesDop_ids',
				'label' => 'Доп критерии',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'DrugTherapyScheme_ids',
				'label' => 'Схема лекарственной терапии',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'RehabScale_id',
				'label' => 'Оценка состояния по ШРМ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'Профиль койки',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnSectionEditForm' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLpuSectionWardHistory' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLpuSectionBedProfileHistory' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setEvnSectionWard' => array(
			array(
				'field' => 'ignore_sex',
				'label' => 'Игнорировать тип палаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Движение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionWard_id',
				'label' => 'Палата',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionWardCur_id',
				'label' => 'Текущая палата',
				'rules' => '',
				'type' => 'id'
			)
		),
		'setEvnSectionMedPersonal' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Движение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_pid',
				'label' => 'КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required',
				'type' => 'id'
			),
		),
        'setLpuSectionProfile' => array(
            array(
                'field' => 'EvnSection_id',
                'label' => 'Движение',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnSection_pid',
                'label' => 'КВС',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSectionProfile_id',
                'label' => 'Профиль',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
		'deleteEvnSectionInHosp' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор случая движения пациента в стационаре',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deleteBirthSpecStac' => array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор специфики беременности',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnSectionLast' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'useCase',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadEvnSectionGrid' => array(
				array(
					'field' => 'EvnSection_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		'loadEvnSectionGridMorbusOnko' => array(
				array(
					'field' => 'EvnSection_id',
					'label' => 'Идентификатор Движения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Идентификатор заболевания',
					'rules' => 'required',
					'type' => 'id'
				),

			),
		'loadMesList' => array(
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата выписки',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата госпитализации',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Mes_id',
				'label' => 'Идентификатор МЭС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadMes2List' => array(
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveEvnSectionFromOtherLpu' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор случая движения пациента в стационаре',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionWard_id',
				'label' => 'Палата',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignore_sex',
				'label' => 'Игнорировать тип палаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'vizit_direction_control_check',
				'label' => 'Проверка пересечения КВС с ТАП',
				'rules' => '',
				'type' => 'int'
			)
		),
		'saveEvnSection' => array(
			// Исход госпитализации
			array(
				'field' => 'editAnatom',
				'label' => 'Призак редактирования экспертизы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSectionTransType_id',
				'label' => 'LpuSectionTransType_id',
				'rules' => '',
				'type' =>'id'
			),
			array(
				'field' => 'checkIsOMS',
				'label' => 'Проверка диагноза',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreParentEvnDateCheck',
				'label' => 'Признак игнорирования проверки периода выполенения услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'anatomDiagData',
				'label' => 'Список сопутствующих патологоанатомических диагнозов',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'AnatomWhere_id',
				'label' => 'Место проведения экспертизы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_aid',
				'label' => 'Основной патологоанатомический диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_eid',
				'label' => 'Внешняя причина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_expDate',
				'label' => 'Дата проведения экспертизы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDie_expTime',
				'label' => 'Время проведения экспертизы',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnDie_id',
				'label' => 'Идентификатор исхода "Смерть"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_IsWait',
				'label' => 'Умер в приемном покое',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_IsAnatom',
				'label' => 'Признак необходимости проведения экспертизы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLeave_id',
				'label' => 'Идентификатор исхода "Выписка"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLeave_IsAmbul',
				'label' => 'Направлен на амбулаторное лечение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLeave_UKL',
				'label' => 'Уровень качества лечения',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'EvnOtherLpu_id',
				'label' => 'Идентификатор исхода "Перевод в другое ЛПУ"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherSection_id',
				'label' => 'Идентификатор исхода "Перевод в другое отделение"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherSectionBedProfile_id',
				'label' => 'Идентификатор исхода "Перевод на другой профиль коек"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherStac_id',
				'label' => 'Идентификатор исхода "Перевод в стационар другого типа"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveCause_id',
				'label' => 'Исход госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_id',
				'label' => 'Исход госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_Code',
				'label' => 'Код исхода госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DeathPlace_id',
				'label' => 'Идентификатор места смерти',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_SysNick',
				'label' => 'Системное наименование исхода госпитализации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Org_oid',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_aid',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_oid',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfile_oid',
				'label' => 'Профиль коек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfileLink_fedoid',
				'label' => 'Профиль коек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_oid',
				'label' => 'Тип стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_aid',
				'label' => 'Врач-патологоанатом',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'Врач, установивший смерть',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_did',
				'label' => 'Рабочее место врача, установившего смерть',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_aid',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDesease_id',
				'label' => 'Исход заболевания',
				'rules' => '',
				'type' => 'id'
			),

			// Движение
			array(
				'field' => 'Diag_id',
				'label' => 'Основной диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetPhase_id',
				'label' => 'Стадия/фаза основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetPhase_aid',
				'label' => 'Состояние пациента при выписке',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Впервые выявленная инвалидность',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'ESecEF_EvnSection_IsZNOCheckbox',
				'label' => 'Подозрение на ЗНО',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата выписки',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disTime',
				'label' => 'Время выписки',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор случая движения пациента в стационаре',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_PhaseDescr',
				'label' => 'Расшифровка диагноза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата поступления',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_setTime',
				'label' => 'Время поступления',
				'rules' => 'trim|required',
				'type' => 'time'
			),
			array(
				'field' => 'EvnSection_PlanDisDT',
				'label' => 'Планируемая дата выписки',
				'rules' => '',
				'type' => 'date'
			),

			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionWard_id',
				'label' => 'Палата',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Mes_id',
				'label' => 'МЭС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Mes2_id',
				'label' => 'МЭС2',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Mes_tid',
				'label' => 'КСГ найденная через диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Mes_sid',
				'label' => 'КСГ найденная через услугу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Mes_kid',
				'label' => 'КПГ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MesTariff_id',
				'label' => 'Коэффициент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MesTariff_sid',
				'label' => 'Коэффициент КПГ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Тип финансирования',
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
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
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
				'field' => 'TariffClass_id',
				'label' => 'Вид тарифа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ChildTermType_id',
				'label' => 'Доношенность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FeedingType_id',
				'label' => 'Вид вскармливания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'personHeightData',
				'label' => 'Измерения длины (роста) новорожденного',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'personWeightData',
				'label' => 'Измерения массы новорожденного',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'isPersonNewBorn',
				'label' => 'Сохранение специфики новорожденного',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FeedingType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ChildTermType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsAidsMother',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsBCG',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_BCGSer',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_BCGNum',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_BCGDate',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonNewBorn_IsHepatit',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_HepatitSer',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_HepatitNum',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_HepatitDate',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonNewBorn_CountChild',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ChildPositionType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsRejection',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsHighRisk',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsAudio',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsNeonatal',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsBleeding',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsBreath',
				'label' => 'Дыхание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsHeart',
				'label' => 'Сердцебиение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsPulsation',
				'label' => 'Пульсация пуповины',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsMuscle',
				'label' => 'Произвольное сокращение мускулатуры',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewborn_BloodBili',
				'label' => 'Общий билирубин',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'PersonNewborn_BloodHemoglo',
				'label' => 'Гемоглобин',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'PersonNewborn_BloodEryth',
				'label' => 'Эритроциты',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'PersonNewborn_BloodHemato',
				'label' => 'Гематокрит',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'NewBornWardType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'Профиль койки отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfileLink_fedid',
				'label' => 'таблица стыковки fed.LpuSectionBedProfileLink',
				'rules' => '',
				'type' => 'id'
			),

			array(
				'field' => 'birthDataPresented',
				'label' => 'Заполнять даные по беременности и родам',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnSection_IsAdultEscort',
				'label' => 'Сопровождается взрослым',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_IsMedReason',
				'label' => 'По медицинским показаниям',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_IndexRep',
				'label' => 'Признак повторной подачи',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'EvnSection_IsMeal',
				'label' => 'С питанием',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'HTMedicalCareClass_id',
				'label' => 'Идентификатор метода высокотехнологичной медицинской помощи',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'ignoreEvnUslugaKSGCheck',
				'label' => 'Признак игнорирования проверки наличия услуги',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'ignoreNotHirurgKSG',
				'label' => 'Признак игнорирования проверки нехирургической КСГ',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'silentSave',
				'label' => 'Автосохранение',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'vizit_direction_control_check',
				'label' => 'Проверка пересечения КВС с ТАП',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_isPartialPay',
				'label' => 'Частичная оплата',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'EvnSection_IsCardShock',
				'label' => 'Осложнен кардиогенным шоком',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_StartPainHour',
				'label' => 'Время от начала боли, часов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_StartPainMin',
				'label' => 'Время от начала боли, минут',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_GraceScalePoints',
				'label' => 'Кол-во баллов по шкале GRACE',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DeseaseBegTimeType_id',
				'label' => 'Время с начала заболевания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TumorStage_id',
				'label' => 'Стадия выявленного ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'label' => 'Подозрение на ЗНО',
				'field' => 'EvnSection_IsZNO',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'label' => 'Подозрение на диагноз',
				'field' => 'Diag_spid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PainIntensity_id',
				'label' => 'Интенсивность боли',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancyEvnPS_Period',
				'label' => 'Срок беременности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в базовом регистре',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPregnancy',
				'label' => 'Анкета по беременности',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PregnancyScreenList',
				'label' => 'Список скринингов беременности',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BirthSpecStac',
				'label' => 'Исход беременности',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MesOldUslugaComplex_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseType_pid',
				'label' => 'Тип отвода от пробы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RefuseType_aid',
				'label' => 'Тип отвода от аудиоскрининга',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RefuseType_bid',
				'label' => 'Тип отвода от БЦЖ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RefuseType_gid',
				'label' => 'Тип отвода от гепатита',
				'rules' => '',
				'type' => 'string'
			)
		),
		'evnBirthData'=> array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор сведений о родах в КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancySpec_id',
				'label' => 'Идентификатор специфики о беременностях и родах в карте диспансерного учета',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_CountPregnancy',
				'label' => 'Которая беременность',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_CountChild',
				'label' => 'Количество плодов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_CountChildAlive',
				'label' => 'В т.ч. живорожденные',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_IsHIVtest',
				'label' => 'Обследована на ВИЧ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_IsHIV',
				'label' => 'Наличие ВИЧ-инфекции',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AbortType_id',
				'label' => 'Тип аборта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_IsMedicalAbort',
				'label' => 'Медикаментозный',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_CountBirth',
				'label' => 'Роды которые',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthResult_id',
				'label' => 'Характер родов',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'BirthPlace_id',
				'label' => 'Место родов',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_OutcomPeriod',
				'label' => 'Срок, недель',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_OutcomD',
				'label' => 'Дата исхода беременности',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'BirthSpecStac_OutcomT',
				'label' => 'Время родов',
				'rules' => 'trim|required',
				'type' => 'time'
			),
			array(
				'field' => 'BirthSpec_id',
				'label' => 'Особенности родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_BloodLoss',
				'label' => 'Кровопотери (мл)',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'deathChilddata',
				'label' => 'Данные о мертворожденных',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'childdata',
				'label' => 'Данные о детях',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getKVCbezVrachaMore24h' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'trim|required',
				'type' => 'id'
			)
		),

		'getSectionTreeData' => array(
			// данные для отображения структуры дерева
			array(
				'default' => 'stac',
				'field' => 'ARMType',
				'label' => 'Тип рабочего места врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'level',
				'label' => 'Уровень события',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'group',
				'label' => 'Группа к которой относится пациент',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'group_by',
				'label' => 'Группировка пациентов',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 'root',
				'field' => 'node',
				'label' => 'Идентификатор родительской ноды',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'object',
				'label' => 'Тип объекта',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'object_id',
				'label' => 'Имя идентификатора объекта',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'object_value',
				'label' => 'Идентификатор объекта',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'date',
				'label' => 'дата',
				'rules' => 'required',
				'type' => 'date'
			),
			/*
			array(
				'field' => 'date1',
				'label' => 'дата начала периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'date2',
				'label' => 'дата конца периода',
				'rules' => 'required',
				'type' => 'date'
			),*/
			
			// фильтры
			array(
				'field' => 'filter_Person_F',
				'label' => 'Фамилия человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'filter_Person_I',
				'label' => 'Имя человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'filter_Person_O',
				'label' => 'Отчество человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'filter_Person_BirthDay',
				'label' => 'ДР человека',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'filter_PSNumCard',
				'label' => 'Номер КВС',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'filter_MedStaffFact_id',
				'label' => 'Идентификатор рабочего места врача',
				'rules' => 'trim',
				'type' => 'id'
			),
            //BOB - 21.03.2017
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор медслужбы реанимации',
				'rules' => 'trim',
				'type' => 'id'
			)
            //BOB - 21.03.2017
		),
		'saveEvnSectionInHosp' => array(
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата поступления',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_setTime',
				'label' => 'Время поступления',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnSection_PlanDisDT',
				'label' => 'Планируемая дата выписки',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionWard_id',
				'label' => 'Палата',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
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
				'field' => 'vizit_direction_control_check',
				'label' => 'Проверка пересечения КВС и ТАП',
				'rules' => '',
				'type' => 'int'
			)
		),
		'saveHospInOtherLpuSection' => array(
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
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
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'getLpuSectionWardList' => array(
			array(
				'field' => 'date',
				'label' => 'Дата',
				'rules' => 'trim',
				//'default' => date('Y-m-d'),
				'type' => 'date'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getLpuSectionWardSelectList' => array(
			array('field' => 'sort','label' => 'Поле для сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'dir','label' => 'Направление сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_uid','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'Person_id','label' => 'Ид пациента','rules' => '','type' => 'id'),
			array('field' => 'WithoutChildLpuSectionAge','label' => 'не отображать отделения с указанной возрастной группой «Детское»','rules' => '','type' => 'int')
		),
		'getLpuSectionBedProfileLink' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль отделения',
				'rules' => '',
				'type' => 'id'
		),
			array(
				'field' => 'validityLpuSection', 
				'label' => 'по периоду действия отделения', 
				'rules' => '', 
				'type' => 'boolean'
			),
		),
		'getLpuSectionBedProfilesByLpuSection' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Движение в отделении',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getLpuSectionBedProfilesLinkByLpuSection' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'endDate',
				'label' => '',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'begDate',
				'label' => '',
				'rules' => 'trim',
				'type' => 'date'
			)
		),
		'getLpuSectionBedProfilesByLpuSectionProfile' => array(
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfile_IsChild',
				'label' => 'Ребенок',
				'rules' => '',
				'type' => 'id'
			),
		),
		'setLpuSectionBedProfile' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
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
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'Профиль койки отделения',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSectionBedProfileLink_fedid',
				'label' => 'таблица стыковки fed.LpuSectionBedProfileLink',
				'rules' => '',
				'type' => 'id'
			)
		),
		'recalcKSGKPGKOEF' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getLpuSectionPatientList' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'date',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'object_value',
				'label' => 'параметр',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'filter_Person_F',
				'label' => 'Фамилия человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'filter_Person_I',
				'label' => 'Имя человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'filter_Person_O',
				'label' => 'Отчество человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'filter_Person_BirthDay',
				'label' => 'ДР человека',
				'rules' => 'trim',
				'type' => 'date'
			),
		),
		'setEvnSectionParameter' => array(
			array('field' => 'object','label' => 'Системное имя объекта','rules' => '','type' => 'string')
			,array('field' => 'id','label' => 'Идентификатор объекта','rules' => 'required','type' => 'id')
			,array('field' => 'param_name','label' => 'Системное имя параметра','rules' => 'required','type' => 'string')
			,array('field' => 'param_value','label' => 'Значение параметра','rules' => '','type' => 'int')
			,array('field' => 'options','label' => 'Дополнительные опции','rules' => '','type' => 'string')
		),
		'checkEvnSectionOutcomeOrgDate' => array(
			array('field' => 'Org_oid','label' => 'Идентификатор организации перевода','rules' => 'required','type' => 'id')
			,array('field' => 'EvnSection_OutcomeDate','label' => 'Дата исхода из отделения','rules' => 'required','type' => 'date')
		),
		'RecalcKSG' => array(
			array('field' => 'EvnDateRange', 'label' => 'Диапазон дат выписки', 'rules' => 'required', 'type' => 'daterange'),
			array('field' => 'Lpu_id', 'label' => 'Идентификаторы МО', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => '', 'type' => 'id'),
			array('field' => 'StType', 'label' => 'Вид стационара', 'rules' => '', 'type' => 'id'),
			array('field' => 'PaidStatus', 'label' => 'Статус оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'proceed', 'label' => '', 'rules' => '', 'type' => 'int')
			),
		'RecalcKSLP' => array(
			array('field' => 'EvnDateRange', 'label' => 'Диапазон дат выписки', 'rules' => 'required', 'type' => 'daterange'),
			array('field' => 'Lpu_id', 'label' => 'Идентификаторы МО', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => '', 'type' => 'id'),
			array('field' => 'StType', 'label' => 'Вид стационара', 'rules' => '', 'type' => 'id'),
			array('field' => 'PaidStatus', 'label' => 'Статус оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'proceed', 'label' => '', 'rules' => '', 'type' => 'int')
		),
		'setEvnSectionDiag' => array(
			array('field' => 'EvnSection_id','label' => 'Идентификатор движения','rules' => 'required','type' => 'id'),
			array('field' => 'ignoreCheckMorbusOnko','label' => 'Признак игнорирования проверки перед удалением специфики','rules' => '','type' => 'int'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => '','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Врач','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_id','label' => 'Диагноз','rules' => 'required','type' => 'id')
		),
		'saveDrugTherapyScheme' => array(
			array('field' => 'EvnSection_id','label' => 'Идентификатор движения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugTherapyScheme_ids', 'label' => 'Схема лекарственной терапии', 'rules' => '', 'type' => 'multipleid'),
		),
		'loadDrugTherapySchemeList' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'isForEMK',
				'label' => '',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'getRoomList' => array (
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GetRoom_id', 'label' => 'Палата', 'rules' => '', 'type' => 'id'),
		),
		'getBedList' => array (
			array('field' => 'GetRoom_id', 'label' => 'Палата', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GetBed_id', 'label' => 'Профиль койки', 'rules' => '', 'type' => 'id'),
		),
		'saveTransfusionFact' => array (
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnSection_id','label' => 'Идентификатор движения','rules' => 'required','type' => 'id'),
			array('field' => 'TransfusionFact_setDT', 'label' => 'Дата переливания крови', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'TransfusionMethodType_id','label' => 'Способ переливания','rules' => '','type' => 'id'),
			array('field' => 'TransfusionAgentType_id','label' => 'Трансфузионные средства','rules' => 'required','type' => 'id'),
			array('field' => 'TransfusionIndicationType_id','label' => 'Показания к трансфузии','rules' => 'required','type' => 'id'),
			array('field' => 'VizitClass_id','label' => 'Тип','rules' => 'required','type' => 'id'),
			array('field' => 'TransfusionFact_Volume','label' => 'Объем','rules' => 'required','type' => 'int'),
			array('field' => 'TransfusionFact_Dose','label' => 'Доза','rules' => '','type' => 'int'),
			array('field' => 'TransfusionReactionType_id','label' => 'Трансфузионные реакции','rules' => '','type' => 'id'),
			array('field' => 'TransfusionComplication', 'label' => 'Осложнения при переливании', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransfusionFact_id', 'label' => 'Идентификатор факта переливания крови', 'rules' => '', 'type' => 'id'),
			array('field' => 'action', 'label' => 'Действие', 'rules' => 'required', 'type' => 'string')
		),
		'loadTransfusionFactList' => array (
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnSection_id','label' => 'Идентификатор движения','rules' => 'required','type' => 'id'),
		),
		'loadTransfusionFact' => array (
			array('field' => 'TransfusionFact_id', 'label' => 'Идентификатор факта переливания крови', 'rules' => 'required', 'type' => 'id')
		),
		'deleteTransfusionFact' => array (
			array('field' => 'TransfusionFact_id', 'label' => 'Идентификатор факта переливания крови', 'rules' => 'required', 'type' => 'id')
		),
		'deleteTransfusionCompl' => array (
			array('field' => 'TransfusionCompl_id', 'label' => 'Идентификатор осложнения переливания крови', 'rules' => '', 'type' => 'id')
		),
		'loadScreenList' => array (
			array('field' => 'EvnSection_id','label' => 'Идентификатор движения','rules' => 'required','type' => 'id'),
		),
		'getAverageDateStatement' => array (
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'required','type' => 'id'),
			array('field' => 'Evn_setDT','label' => 'Дата начала движения','rules' => 'required','type' => 'date'),
			array('field' => 'Diag_id','label' => 'Идентификатор диагноза','rules' => 'required','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Идентификатор отделения', 'rules' => '','type' => 'id'),
			array('field' => 'LpuSectionBedProfile_id','label' => 'Профиль койки', 'rules' => '','type' => 'id')
		),
		'saveEvnDiagHSNDetails' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HSNStage_id',
				'label' => 'Идентификатор стадии ХСН для основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HSNFuncClass_id',
				'label' => 'Идентификатор функционального класса для основного диагноза',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getLastHsnDetails' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnSection_model', 'dbmodel');
	}

	/**
	 * Пересчёт КСГ у движения
	 */
	function recalcKSGKPGKOEF() {
		$data = $this->ProcessInputData('recalcKSGKPGKOEF', true);
		if ($data === false) { return false; }

		$this->dbmodel->recalcKSGKPGKOEF($data['EvnSection_id'], $data['session']);
		$this->ReturnData(array('success' => true));
	}

	/**
	 * Пересчёт КСГ у движений
	 */
	function RecalcKSG() {
		ignore_user_abort(true);
		set_time_limit(0);

		$err=false;

		$data = $this->ProcessInputData('RecalcKSG', false, false, false);
		if ($data === false) { return false; }

		if(!$data['proceed'] && $this->dbmodel->getRecalcKSGlistStatus()) {
			$this->ReturnData(array('success' => true, 'count' => 0, 'complete'=>0, 'in_progress'=>1, 'Error_Msg' => "" ));
			return true;
		} else {
			$this->dbmodel->setRecalcKSGlistStatus('1'); //ставим блокировку, чтобы не запустили параллельно второй процесс
		}

		if(count($data['EvnDateRange'])==2) {
			$data['date1'] = $data['EvnDateRange'][0];
			$data['date2'] = $data['EvnDateRange'][1];
		} else $err =true;

		$StType='1,6,9';
		if($data['StType']=='0') {
			$StType = '1,6,9'; //и дневные, и круглосуточные стационары
		} else if($data['StType']=='2') {
			$StType = '6,9'; //только дневные
		} else if($data['StType']=='1') {//круглосуточные
			$StType = '1';
		}
		$data['StType']=$StType;
		$data['Lpu_id'] = preg_replace('/[^0-9,]/', '', $data['Lpu_id']);

		if($err) {
			$this->ReturnData(array('success' => false, 'data' => null ));
			return false;
		}

		$res = $this->dbmodel->recalcKSGlist($data);

		$this->dbmodel->setRecalcKSGlistStatus('0'); //снимаем блокировку на запуск
		$this->ReturnData($res);
	}

	/**
	 * Остановка процесса пересчёта КСГ у движений по кнопке
	 */
	function RecalcKSGstop() {
		ignore_user_abort(true);
		set_time_limit(0);
		if(session_status()==PHP_SESSION_ACTIVE) session_write_close();
		session_start();
		$_SESSION['recalc_stop']=1;
		session_write_close();

		$this->dbmodel->setRecalcKSGlistStatus('0'); //снимаем блокировку на запуск

		$this->ReturnData(array('success' => true,
			'progress' => isset($_SESSION['recalc_progress'])?$_SESSION['recalc_progress']:0,
			'max' => isset($_SESSION['recalc_progress_max'])?$_SESSION['recalc_progress_max']:0,
			'stop' => isset($_SESSION['recalc_stop'])?$_SESSION['recalc_stop']:0
		));
	}

	/**
	 * Статус процесса пересчёта КСГ у движений (для индикатора формы)
	 */
	function RecalcKSGstatus()
	{
		ignore_user_abort(true);
		set_time_limit(0);
		$this->ReturnData(array('success' => true,
			'progress' => isset($_SESSION['recalc_progress']) ? $_SESSION['recalc_progress'] : 0,
			'max' => isset($_SESSION['recalc_progress_max']) ? $_SESSION['recalc_progress_max'] : 0,
			'stop' => isset($_SESSION['recalc_stop']) ? $_SESSION['recalc_stop'] : 0
		));
	}

	/**
	 * Пересчёт КСЛП у движений
	 */
	public function RecalcKSLP() {
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");
		/*session_set_cookie_params(86400);
		ini_set("session.gc_maxlifetime",86400);
		ini_set("session.cookie_lifetime",86400);*/

		$err = false;

		$data = $this->ProcessInputData('RecalcKSLP', false, false, false);
		if ($data === false) { return false; }

		if (!$data['proceed'] && $this->dbmodel->getRecalcKSLPlistStatus()) {
			$this->ReturnData(array('success' => true, 'count' => 0, 'complete' => 0, 'in_progress' => 1, 'Error_Msg' => ''));
			return true;
		}

		$this->dbmodel->setRecalcKSLPlistStatus('1'); //ставим блокировку, чтобы не запустили параллельно второй процесс

		if (count($data['EvnDateRange']) == 2) {
			$data['date1'] = $data['EvnDateRange'][0];
			$data['date2'] = $data['EvnDateRange'][1];
		}
		else {
			$err =true;
		}

		$StType='1,6,9';
		if($data['StType']=='0') {
			$StType = '1,6,9'; //и дневные, и круглосуточные стационары
		} else if($data['StType']=='2') {
			$StType = '6,9'; //только дневные
		} else if($data['StType']=='1') {//круглосуточные
			$StType = '1';
		}
		$data['StType']=$StType;
		$data['Lpu_id'] = preg_replace('/[^0-9,]/', '', $data['Lpu_id']);

		if ($err) {
			$this->ReturnData(array('success' => false, 'data' => null));
			return false;
		}

		$res = $this->dbmodel->recalcKSLPlist($data);
		//$res = $this->dbmodel->recalcCoeffCTP($data);

		$this->dbmodel->setRecalcKSLPlistStatus('0'); //снимаем блокировку на запуск
		$this->ReturnData($res);

		return true;
	}

	/**
	 * Остановка процесса пересчёта КСЛП у движений по кнопке
	 */
	public function RecalcKSLPstop() {
		ignore_user_abort(true);
		set_time_limit(0);

		if (session_status() == PHP_SESSION_ACTIVE ) {
			session_write_close();
		}

		session_start();
		$_SESSION['kslp_recalc_stop'] = 1;
		session_write_close();

		$this->dbmodel->setRecalcKSLPlistStatus('0'); //снимаем блокировку на запуск

		$this->ReturnData(array('success' => true,
			'progress' => isset($_SESSION['kslp_recalc_progress']) ? $_SESSION['kslp_recalc_progress'] : 0,
			'max' => isset($_SESSION['kslp_recalc_progress_max']) ? $_SESSION['kslp_recalc_progress_max'] : 0,
			'stop' => isset($_SESSION['kslp_recalc_stop']) ? $_SESSION['kslp_recalc_stop'] : 0
		));

		return true;
	}

	/**
	 * Статус процесса пересчёта КСЛП у движений (для индикатора формы)
	 */
	public function RecalcKSLPstatus() {
		ignore_user_abort(true);
		set_time_limit(0);

		$this->ReturnData(array('success' => true,
			'progress' => isset($_SESSION['kslp_recalc_progress']) ? $_SESSION['kslp_recalc_progress'] : 0,
			'max' => isset($_SESSION['kslp_recalc_progress_max']) ? $_SESSION['kslp_recalc_progress_max'] : 0,
			'stop' => isset($_SESSION['kslp_recalc_stop']) ? $_SESSION['kslp_recalc_stop'] : 0
		));

		return true;
	}

	/**
	 * Пересчёт КСЛП у движений
	 */
	public function recalcCoeffCTP() {
		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$data = $this->ProcessInputData('recalcCoeffCTP', true);
		if ($data === false) { return false; }

		$this->dbmodel->recalcCoeffCTP($data);
		return true;
	}

	/**
	 * Пересчёт IndexNum
	 */
	public function recalcIndexNum() {
		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$data = $this->ProcessInputData('recalcIndexNum', true);
		if ($data === false) { return false; }

		$this->dbmodel->recalcIndexNum($data);
		return true;
	}

	/**
	 *  Запись одного параметра события
	 *  Используется: ЭМК
     */
	function setEvnSectionParameter() {
		$data = $this->ProcessInputData('setEvnSectionParameter', true);
		if ($data === false) { return false; }
		try {
			if (!empty($data['options'])) {
				$options = json_decode($data['options'], true);
				$data = array_merge($data, $options);
			}
			// проверяем наличие метода у модели
			// имя метода должно быть в верблюжьем стиле!
			// и начинаться с update
			$parts = explode('_', $data['param_name']);
			$method = 'update';
			foreach($parts as $word) {
				$method .= ucfirst($word);
			}
			if (!method_exists($this->dbmodel, $method)) {
				throw new Exception('Указанное поле нельзя изменить', 400);
			}
			// устанавливаем сценарий и параметры
			$this->dbmodel->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
			$this->dbmodel->setParams($data);
			// вызываем метод у модели
			$response = call_user_func(array($this->dbmodel, $method), $data['id'], $data['param_value']);
			$this->ProcessModelSave($response, true, 'При записи параметра возникли ошибки');
			$this->ReturnData();
			return true;
		} catch (Exception $e) {
			$this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			));
			return false;
		}
	}
	
	/**
	*  Получение данных для формы редактирования движения пациента в стационаре
	*  Входящие данные: $_POST['EvnSection_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования движения пациента в стационаре
	*                форма редактирования исхода госпитализации пациента
	 * @return bool
	 */
	function loadEvnSectionEditForm() {
		$data = $this->ProcessInputData('loadEvnSectionEditForm', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnSectionEditForm($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();

		return true;
	}
	
	/**
	* Получение предыдущего диагноза
	*/
	function getDiagPred() {
		$data = $this->ProcessInputData('getDiagPred', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDiagPred($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Получение диагноза приемного отделения
	*/
	function getPriemDiag() {
		$data = $this->ProcessInputData('getPriemDiag', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPriemDiag($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Получение диагноза отделения
	*/
	function getEvnSectionDiag() {
		$data = $this->ProcessInputData('getEvnSectionDiag', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnSectionDiag($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Получение КСГ/КПГ/Коэффициента КСГ/КПГ
	*/
	function loadKSGKPGKOEF() {
		$data = $this->ProcessInputData('loadKSGKPGKOEF', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadKSGKPGKOEF($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Проверка наличия связок для отображения полей
	*/
	function checkMesOldUslugaComplexFields() {
		$data = $this->ProcessInputData('checkMesOldUslugaComplexFields', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkMesOldUslugaComplexFields($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Проверка наличия связок для отображения полей
	*/
	function checkDrugTherapySchemeLinks() {
		$data = $this->ProcessInputData('checkDrugTherapySchemeLinks', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkDrugTherapySchemeLinks($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Получение комбо КСГ/КПГ/Коэффициента КСГ/КПГ
	*/
	function loadKSGKPGKOEFCombo() {
		$data = $this->ProcessInputData('loadKSGKPGKOEFCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadKSGKPGKOEFCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	*  Получение списка палат отделения действующих и свободных на данный момент времени
	*  На выходе: JSON-строка
	*  Используется: АРМ стационара, ЭМК
	 * @return bool
	 */
	function getLpuSectionWardList() {
		$data = $this->ProcessInputData('getLpuSectionWardList', true);
		if ($data) {
			$this->load->model('HospitalWard_model', 'HospitalWard_model');
			$response = $this->HospitalWard_model->getLpuSectionWardList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	*  Загрузка данных в грид выбора палаты при приеме на госпитализацию
	 */
	function getLpuSectionWardSelectList() {
		$data = $this->ProcessInputData('getLpuSectionWardSelectList', true);
		if ($data) {
			$this->load->model('HospitalWard_model', 'HospitalWard_model');
			$response = $this->HospitalWard_model->getLpuSectionWardSelectList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Функция записи даты и времени
	 * На выходе: JSON-строка
	 */
	function saveEvnXmlDate() {

		$data = $this->ProcessInputData('saveEvnXmlDate', true);
		if ($data) {
			$response = $this->dbmodel->saveEvnXmlDate($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Получаем список КВС с хотябы одним движением без врача
	 * @return bool
	 * @throws Exception
	 */
	function getKVCbezVrachaMore24h(){
		$data = $this->ProcessInputData('getKVCbezVrachaMore24h', true, true);
		if ($data === false){
			return false;
		}

		$response = $this->dbmodel->getKVCbezVrachaMore24h($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Переведенные пациенты из других отделений в АРМ стационара
	 * @param $data
	 * @return array|bool
	 */
	private function getAnotherSection($data){
		$result = array();

		// отображаем переведенных из другого отделения
		$childrens = $this->dbmodel->getAnotherLpuSectionPatientList($data);

		if (!is_array($childrens)) {
			echo json_encode(array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => 'Ошибка БД при получении данных дочерних узлов!'));
			return false;
		}

		$mes_alias = getMESAlias();
		foreach ($childrens as $rows) {
			if($rows['Mes_id'] != "" && $rows['Mes_id'] != 0) {
				$percentage = (isset($rows['KoikoDni']) && ($rows['KoikoDni']>0)) ? floor(($rows['EvnSecdni']/$rows['KoikoDni']) * 100) : '?';
				$mes = '<b>' . $mes_alias . ':</b> ' . $rows['KoikoDni'] . ' дней (' . $percentage . '%)';
			}
			if(1 == $rows['Sex_id']) {
				$icon = 'male16';
			} else {
				$icon = 'female16';
			}

			if ( $rows['Person_Age'] == 0 ) {
				$rows['Person_Age'] = $rows['Person_AgeMonth'] . ' мес.';
			}
			else {
				$o = str_split($rows['Person_Age'] . '');
				$n = count($o) - 1;
				switch($o[$n]) {
					case '1':
						if( $rows['Person_Age'] == 11 )
							$rows['Person_Age'] .= ' лет';
						else
							$rows['Person_Age'] .= ' год';
						break;

					case '2':
					case '3':
					case '4':
						if( in_array($rows['Person_Age'],array(12,13,14)) )
							$rows['Person_Age'] .= ' лет';
						else
							$rows['Person_Age'] .= ' года';
						break;

					default:
						$rows['Person_Age'] .= ' лет';
						break;
				}
			}


			$text = '';
			if(isset($rows['EvnSection_PlanDisDT']) && $rows['EvnSection_PlanDisDT'] != '')
			{
				$text .= '<img title="запланирована выписка пациента на '.$rows['EvnSection_PlanDisDT'].'" src="/img/icons/patient-leave16.png" />';
			}

			$surgery = $this->dbmodel->getSurgeryforEvn($rows['EvnSection_rid']);

			$sur_cnt = count($surgery);
			if($sur_cnt > 0) {
				$todate = strtotime($data['date']);
				$uslugadate = strtotime($surgery[$sur_cnt-1]['EvnUsluga_setDate']);
				if($todate < $uslugadate) {
					$text .= '<img title="запланирована операция пациенту на ' . $surgery[$sur_cnt-1]['EvnUsluga_setDate'] . '" src="/img/icons/hand-red16.png">';
				}
				elseif($todate >= $uslugadate) {
					$text .= '<img title="' . $surgery[$sur_cnt-1]['EvnUsluga_setDate'] . ' пациент прооперирован" src="/img/icons/hand-green16.png">';
				}
			}

			if($rows['EvnPS_NumCard'] != '') {
				$rows['EvnPS_NumCard'] = '№' . $rows['EvnPS_NumCard'];
			}

			if (isset($mes)){
				$mes = '&nbsp;&nbsp;&nbsp;' . $mes;
			} else {
				$mes = '';
			}
			if (allowPersonEncrypHIV($data['session']) && !empty($rows['PersonEncrypHIV_Encryp'])) {
				$person_info_text = '<span style="color: darkblue;">' . $rows['PersonEncrypHIV_Encryp'] . '</span>';
			} else {
				$person_info_text = '<span style="color: darkblue;">' . $rows['Person_Fio'] . '</span>&nbsp;(' . $rows['Person_Age'] . ')';
			}
			// Изменения в рамках задачи #199127
			// $text .= $person_info_text . '&nbsp;&nbsp;&nbsp;' . $rows['EvnPS_NumCard'] .   
			// 	'&nbsp;&nbsp;<b>Диагноз:</b> ' . $rows['Diag_Code'] .
			// 	'<span>&nbsp;&nbsp;&nbsp;<b>Поступил:</b> ' . $rows['EvnSection_setDate'] . '</span>' .
			// 	(($rows['EvnSection_disDate']) ? '&nbsp;&nbsp;<span><b>Выписан:</b> ' . $rows['EvnSection_disDate'] . '</span>' : '') .
			// 	$mes;

			if( getRegionNick() == 'vologda' ) {
				$text .= $person_info_text . '&nbsp;&nbsp;&nbsp;' . $rows['EvnPS_NumCard'] .
				'&nbsp;&nbsp;<b>Диагноз:</b> ' . $rows['Diag_Code'] .
				'<span>&nbsp;&nbsp;&nbsp;<b>Поступил в ПДО:</b> ' . $rows['EvnSection_setDate'] . '</span>' .
				(($rows['EvnSection_disDate']) ? '&nbsp;&nbsp;<span><b>Переведен:</b> ' . $rows['EvnSection_disDate'] . '</span>' : '') .
				$mes;
			} else {
				$text .= $person_info_text . '&nbsp;&nbsp;&nbsp;' . $rows['EvnPS_NumCard'] .
				'&nbsp;&nbsp;<b>Диагноз:</b> ' . $rows['Diag_Code'] .
				'<span>&nbsp;&nbsp;&nbsp;<b>Поступил:</b> ' . $rows['EvnSection_setDate'] . '</span>' .
				(($rows['EvnSection_disDate']) ? '&nbsp;&nbsp;<span><b>Выписан:</b> ' . $rows['EvnSection_disDate'] . '</span>' : '') .
				$mes;
			}	

			$obj = array(
				'text' => $text,
				'id' => 'anothersect'.$rows['EvnSection_id'],
				'EvnSection_id' => $rows['EvnSection_id'],
				'EvnSection_setDate' => $rows['EvnSection_setDate'],
				'LpuSection_id' => $rows['LpuSection_id'],
				'LpuSectionWard_id' => $rows['LpuSectionWard_id'],
				'MedPersonal_id' => $rows['MedPersonal_id'],
				'Sex_id' => $rows['Sex_id'],
				'Person_id' => $rows['Person_id'],
				'Server_id' => $rows['Server_id'],
				'PersonEvn_id' => $rows['PersonEvn_id'],
				'Person_BirthDay' => $rows['Person_BirthDay'],
				'PersonEncrypHIV_Encryp' => allowPersonEncrypHIV($data['session']) ? $rows['PersonEncrypHIV_Encryp'] : null,
				'EvnPS_id' => $rows['EvnPS_id'],
				'leaf' => true,
				'group'	=> $data['group'],
				'iconCls' => $icon,
				'cls' => 'folder',
				'AnotherSection' => true
			);
			$result[] = $obj;
			if(isset($mes)) {
				unset($mes);
			}
		}

		return $result;
	}

	/**
	 * Вывод данных для группировки пациентов по режимам наблюдения
	 * @param $data
	 * @return array|bool
	 */
	private function getTreeData($data){
		$result = array();

		if(0 == $data['level']){
			$regimeTypes = $this->dbmodel->getTreeData();
			$regimeTypes = array_combine(array_column($regimeTypes, 'PrescriptionRegimeType_id'), $regimeTypes);

			$nodeIcon = '/img/icons/tree-rooms-common.png';

			foreach($regimeTypes as $row){
				$text = '<span class="x-tree-node-text" style="line-height:28px;margin:0;"></span><span style="margin-left: 0;" class="tree-folder">' . $row['PrescriptionRegimeType_Name'] . '</span>';
				$id = 'LpuSectionWard_id_' . $row['PrescriptionRegimeType_id'];
				$date = $row['PrescriptionRegimeType_id'];

				$obj = array(
					'text' => $text,
					'id' => $id,
					'group'	=> 'pacients_inward',
					'date' => $date,
					'object' => 'LpuSectionWard',
					'object_id' => 'LpuSectionWard_id',
					'object_value' => $date,
					'leaf' => false,
					'iconCls' => '',
					'icon' => $nodeIcon,
					'cls' => 'x-tree-node-24x24'
				);
				$result[] = $obj;
			}

			$result[] = array(
				'text' => '<span class="x-tree-node-text"  style="line-height:28px;margin:0;"></span><span  style="margin-left: 0;" class="tree-folder">Без режима</span>',
				'id' => 'LpuSectionWard_id_0',
				'group'	=> 'pacients_inward',
				'date' => 0,
				'object' => 'LpuSectionWard',
				'object_id' => 'LpuSectionWard_id',
				'object_value' => 0,
				'leaf' => false,
				'iconCls' => '',
				'icon' => $nodeIcon,
				'cls' => 'x-tree-node-24x24'
			);

			$data['object_value'] = -3;// Все пациенты
		}

		if(1 == $data['level'] && $data['group'] == 'pacients_anothersection'){
			$result = array_merge($result, $this->getAnotherSection($data));
		}
		else{
			$children = $this->dbmodel->getLpuSectionPatientList($data);
			if(!is_array($children)){
				echo json_encode(array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => 'Ошибка БД при получении данных дочерних узлов!'));
				return false;
			}
			foreach($children as $child){
				if($child['Sex_id'] == 1){
					$icon = 'male16';
				} else {
					$icon = 'female16';
				}

				$text = '';

				if($child['EvnReanimatPeriod_id'] != 0){
					$text .= '<img title="В реанимации" alt="В реанимации" src="/img/icons/ambulance16.png">';
				}

				if($child['EvnSection_disDate'] != ''){
					$text .= '<img title="запланирована выписка пациента на ' . $child['EvnSection_disDate'] . '" alt="запланирована выписка пациента на ' . $child['EvnSection_disDate'] . '" src="/img/icons/patient-leave16.png">';
				}

				$surgery = $this->dbmodel->getSurgeryforEvn($child['EvnSection_rid']);
				$sur_cnt = count($surgery);
				if($sur_cnt > 0){
					$todate = strtotime($data['date']);
					$uslugadate = strtotime($surgery[$sur_cnt-1]['EvnUsluga_setDate']);
					if($todate < $uslugadate){
						$text .= '<img title="запланирована операция пациенту на ' . $surgery[$sur_cnt-1]['EvnUsluga_setDate'] . '" alt="запланирована операция пациенту на ' . $surgery[$sur_cnt-1]['EvnUsluga_setDate'] . '" src="/img/icons/hand-red16.png">';
					}
					elseif($todate >= $uslugadate){
						$text .= '<img title="' . $surgery[$sur_cnt-1]['EvnUsluga_setDate'] . ' пациент прооперирован" alt="' . $surgery[$sur_cnt-1]['EvnUsluga_setDate'] . ' пациент прооперирован" src="/img/icons/hand-green16.png">';
					}
				}

				if($child['EvnPS_NumCard'] != ''){
					$child['EvnPS_NumCard'] = '№' . $child['EvnPS_NumCard'];
				}

				$hint = '';
				if(!empty($child['MedPersonal_Fin'])){
					$hint .= 'Лечащий врач: ' . $child['MedPersonal_Fin'];
				}
				
				$background = '';
				if (isset($rows['PersonQuarantine_IsOn']) && $rows['PersonQuarantine_IsOn'] == 2) {
					$background = 'background: #FCC;';
				}

				if (allowPersonEncrypHIV($data['session']) && !empty($child['PersonEncrypHIV_Encryp'])) {
					$person_info_text = '<span title="' . $hint . '" style="color: darkblue; '.$background.'">' . $child['PersonEncrypHIV_Encryp'] . '</span>';
				} else {
					$person_info_text = '<span title="' . $hint . '" style="color: darkblue; '.$background.'">' . $child['Person_Fio'] . '</span>&nbsp;(' . $child['Person_Age'] . ')';
				}

				$entered = 'Поступил';
				$discharged = 'Выписан';
				if (getRegionNick() == 'vologda') {
					$entered .= ' в ПДО';
					$discharged = 'Выбыл';
				}
				
				$text .= $person_info_text . '&nbsp;&nbsp;&nbsp;' . $child['EvnPS_NumCard'] .
					'&nbsp;&nbsp;<b>Диагноз:</b> ' . $child['Diag_Code'] .
					'<span>&nbsp;&nbsp;&nbsp;<b>' . $entered . ':</b> ' . $child['EvnSection_setDate'] . '</span>' .
					(($child['EvnSection_disDate']) ? '&nbsp;&nbsp;<span><b>' . $discharged . ':</b> ' . $child['EvnSection_disDate'] . '</span>' : '');


				$obj = array(
					'text' => $text,
					'id' => $child['EvnSection_id'],
					'EvnSection_id' => $child['EvnSection_id'],
					'EvnSection_setDate' => $child['EvnSection_setDate'],
					'LpuSection_id' => $child['LpuSection_id'],
					'LpuSectionWard_id' => $child['LpuSectionWard_id'] ?? 0,
					'MedPersonal_id' => $child['MedPersonal_id'],
					'Sex_id' => $child['Sex_id'],
					'PayType_id' => $child['PayType_id'] ?? null,
					'Person_id' => $child['Person_id'],
					'Server_id' => $child['Server_id'],
					'PersonEvn_id' => $child['PersonEvn_id'],
					'Person_BirthDay' => $child['Person_BirthDay'],
					'PersonEncrypHIV_Encryp' => (allowPersonEncrypHIV($data['session']) && !empty($child['PersonEncrypHIV_Encryp'])) ? $child['PersonEncrypHIV_Encryp'] : null,
					'EvnPS_id' => $child['EvnPS_id'],
					'DiagFinance_IsRankin' => $child['DiagFinance_IsRankin'] ?? null,
					'leaf' => true,
					'group'	=> $data['group'],
					'iconCls' => $icon,
					'cls' => 'folder'
				);

				$result[] = $obj;
			}
		}

		// THERE IS NO ANY SENSE TO KEEP THE NODE FOR GROUPING BY MODE
		// Ниже всех на первом уровне отображаем папку "Переведены из других отделений"
		if ($data['level'] == 0  && $data['group_by'] != 'po_rejimam') {
			$result[count($result)] = array(
				'text'			=> '<span class="x-tree-node-text"  style="line-height:28px;margin:0;"></span><span style="margin-left: 0;" class="tree-folder">Переведены из других отделений</span>',
				'group'			=> 'pacients_anothersection',
				'leaf'			=> false,
				'iconCls'		=> '',
				'cls'			=> 'x-tree-node-24x24',
				'icon'			=> '/img/icons/tree-rooms-common.png',
				'object_value'	=> -4,
				'id'			=> 'anothersection'
			);
		}

		return $result;
	}
	/**
	 * Вывод данных для группировки пациентов по режимам наблюдения
	 * @param $data
	 * @return array|bool
	 */
	private function getTreeStatusData($data){
		$result = array();

		if(0 == $data['level']){
			$regimeTypes = array(
				array("StatusType_Id" => "0","StatusType_Name" => "Вновь поступивший"),//Если у пациента дата поступления в отделение равна текущей дате.
				array("StatusType_Id" => "-1","StatusType_Name" => "В отделении"),//Пациенты, не входящие в остальные категории.
				array("StatusType_Id" => "-2","StatusType_Name" => "К выписке"),//Если у пациента дата планируемой выписки меньше или равна текущей плюс один день.
				array("StatusType_Id" => "-3","StatusType_Name" => "Переведены из других отделений"),//Отображаются пациенты, переведенные из других отделений.
				getRegionNick() == 'vologda' ? array("StatusType_Id" => "-4","StatusType_Name" => "Выбыл") : array("StatusType_Id" => "-4","StatusType_Name" => "Выписан"), //#199127
				array("StatusType_Id" => "-5","StatusType_Name" => "Не поступал")//Пациенты, у которых на текущую дату есть открытая бирка в отделении и нет движения в выбранном отделении.
			);
			$nodeIcon = '/img/icons/tree-rooms-common.png';

			foreach($regimeTypes as $row){
				$text = '<span class="x-tree-node-text"  style="line-height:28px;margin:0;"></span><span style="margin-left: 0;" class="tree-folder">' . $row['StatusType_Name'] . '</span>';
				$id = 'LpuSectionWard_id_' . $row['StatusType_Id'];
				$date = $row['StatusType_Id'];
				$group = $row['StatusType_Id']==-3?"pacients_anothersection":"pacients_inward";

				$obj = array(
					'text' => $text,
					'id' => $id,
					'group'	=> $group,
					'date' => $date,
					'object' => 'LpuSectionWard',
					'object_id' => 'LpuSectionWard_id',
					'object_value' => $date,
					'leaf' => false,
					'iconCls' => '',
					'icon' => $nodeIcon,
					'cls' => 'x-tree-node-24x24'
				);
				$result[] = $obj;
			}
		}

		if(1 == $data['level'] && $data['group'] == 'pacients_anothersection'){
			$result = array_merge($result, $this->getAnotherSection($data));
		}
		else if(1 == $data['level']){
			$children = $this->dbmodel->getLpuSectionPatientList($data);
			if(!is_array($children)){
				echo json_encode(array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => 'Ошибка БД при получении данных дочерних узлов!'));
				return false;
			}
			foreach($children as $child){
				if($child['Sex_id'] == 1){
					$icon = 'male16';
				} else {
					$icon = 'female16';
				}

				$text = '';

				if(isset($child['EvnReanimatPeriod_id']) && $child['EvnReanimatPeriod_id'] != 0){
					$text .= '<img title="В реанимации" alt="В реанимации" src="/img/icons/ambulance16.png">';
				}

				if(isset($child['EvnSection_PlanDisDT']) && $child['EvnSection_PlanDisDT'] != '') {
					$text .= '<img title="запланирована выписка пациента на ' . $child['EvnSection_PlanDisDT'] . '" alt="запланирована выписка пациента на ' . $child['EvnSection_PlanDisDT'] . '" src="/img/icons/patient-leave16.png">';
				}

				if(isset($child['EvnSection_rid'])) {
					$surgery = $this->dbmodel->getSurgeryforEvn($child['EvnSection_rid']);
					$sur_cnt = count($surgery);
					if ($sur_cnt > 0) {
						$todate = strtotime($data['date']);
						$uslugadate = strtotime($surgery[$sur_cnt - 1]['EvnUsluga_setDate']);
						if ($todate < $uslugadate) {
							$text .= '<img title="запланирована операция пациенту на ' . $surgery[$sur_cnt - 1]['EvnUsluga_setDate'] . '" alt="запланирована операция пациенту на ' . $surgery[$sur_cnt - 1]['EvnUsluga_setDate'] . '" src="/img/icons/hand-red16.png">';
						} elseif ($todate >= $uslugadate) {
							$text .= '<img title="' . $surgery[$sur_cnt - 1]['EvnUsluga_setDate'] . ' пациент прооперирован" alt="' . $surgery[$sur_cnt - 1]['EvnUsluga_setDate'] . ' пациент прооперирован" src="/img/icons/hand-green16.png">';
						}
					}
				}

				if(isset($child['EvnPS_NumCard']) && $child['EvnPS_NumCard'] != ''){
					$child['EvnPS_NumCard'] = '№' . $child['EvnPS_NumCard'];
				}

				$hint = '';
				if(!empty($child['MedPersonal_Fin'])){
					$hint .= 'Лечащий врач: ' . $child['MedPersonal_Fin'];
				}
				
				$background = '';
				if (isset($rows['PersonQuarantine_IsOn']) && $rows['PersonQuarantine_IsOn'] == 2) {
					$background = 'background: #FCC;';
				}

				if (allowPersonEncrypHIV($data['session']) && !empty($child['PersonEncrypHIV_Encryp'])) {
					$person_info_text = '<span title="' . $hint . '" style="color: darkblue; '.$background.'">' . $child['PersonEncrypHIV_Encryp'] . '</span>';
				} else {
					$person_info_text = '<span title="' . $hint . '" style="color: darkblue; '.$background.'">' . $child['Person_Fio'] . '</span>&nbsp;(' . $child['Person_Age'] . ')';
				}

				$entered = 'Поступил';
				$discharged = 'Выписан';
				if (getRegionNick() == 'vologda') {
					$entered .= ' в ПДО';
					$discharged = 'Выбыл';
				}

				$text .= $person_info_text .
					(isset($child['EvnPS_NumCard']) ? '&nbsp;&nbsp;&nbsp;'. $child['EvnPS_NumCard'] : '').
					(isset($child['Diag_Code']) ? '&nbsp;&nbsp;<b>Диагноз:</b> ' . $child['Diag_Code']: '') . '<span>&nbsp;&nbsp;&nbsp;<b>' . $entered . ':</b> ' .
					(($child['EvnSection_setDate']) ?  $child['EvnSection_setDate'] : $child['EvnDirection_setDate']) . '</span>' .
					(($child['EvnSection_disDate']) ? '&nbsp;&nbsp;<span><b>' . $discharged . ':</b> ' . $child['EvnSection_disDate'] . '</span>' : '');
					if(isset($child['EvnSection_PlanDisDT']) && $child['EvnSection_PlanDisDT'] != '')
						$text .= '&nbsp;&nbsp;<span><b>Дата планируемой выписки:</b> ' . $child['EvnSection_PlanDisDT'] . '</span>';


					$obj = array(
					'text' => $text,
					'id' => $child['EvnSection_id'] ?? $child['EvnDirection_id'],
					'EvnSection_id' => $child['EvnSection_id'] ?? $child['EvnDirection_id'],
					'EvnSection_setDate' => $child['EvnSection_setDate'] ?? $child['EvnDirection_id_setDate'],
					'LpuSection_id' => $child['LpuSection_id'],
					'LpuSectionWard_id' => $child['LpuSectionWard_id'] ?? 0,
					'MedPersonal_id' => $child['MedPersonal_id'] ?? '',
					'Sex_id' => $child['Sex_id'],
					'PayType_id' => $child['PayType_id'] ?? null,
					'Person_id' => $child['Person_id'],
					'Server_id' => $child['Server_id'],
					'PersonEvn_id' => $child['PersonEvn_id'],
					'Person_BirthDay' => $child['Person_BirthDay'],
					'PersonEncrypHIV_Encryp' => allowPersonEncrypHIV($data['session']) ? $child['PersonEncrypHIV_Encryp'] : null,
					'EvnPS_id' => $child['EvnPS_id'] ??  '',
					'DiagFinance_IsRankin' => $child['DiagFinance_IsRankin'] ?? null,
					'leaf' => true,
					'group'	=> $data['group'],
					'iconCls' => $icon,
					'cls' => 'folder'
				);

				$result[] = $obj;
			}
		}

		return $result;
	}

	/**
	*  Выбирает данные для дерева коечной структуры отделения
	*  Используется: форма рабочего места стационара swMPWorkPlaceStacWindow.js
	 * @return bool
	 */
	function getSectionTreeData() {
		$this->load->helper('Date');
		$data = $this->ProcessInputData('getSectionTreeData', true, true);
		
		if ( $data === false )
		{
			 return false;
		}
		if($data['filter_Person_F'])   $data['filter_Person_F'] = rtrim($data['filter_Person_F']);
		if($data['filter_Person_I'])   $data['filter_Person_I'] = rtrim($data['filter_Person_I']);
		if($data['filter_Person_O'])   $data['filter_Person_O'] = rtrim($data['filter_Person_O']);
		if($data['filter_PSNumCard'])   $data['filter_PSNumCard'] = rtrim($data['filter_PSNumCard']);

		if(!empty($data['group_by']) && 'po_rejimam' === $data['group_by']){
			return $this->ReturnData($this->getTreeData($data));
		}
		if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
			return $this->ReturnData($this->getTreeStatusData($data));
		}

		// в дереве будут только два объекта - палаты и койки с больными или без.
		$result = array();

		//BOB - 14.03.2017
		//РЕАНИМАЦИЯ
        //    echo '<pre>' . print_r($data['MedService_id'], 1) . '</pre>'; //BOB - 14.03.2017                
        if ($data['ARMType'] == 'reanimation'){
			$this->load->model('EvnReanimatPeriod_model', 'EvnReanimatPeriod_Model');	
			$childrens = $this->EvnReanimatPeriod_Model->getReanimationPatientList($data);   

			if ( !is_array($childrens) )
			{
				echo json_encode( array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Ошибка БД при получении данных дочерних узлов!') ) );
				return false;
			}
                    
                        $mes_alias = getMESAlias();

			foreach ($childrens as $rows) {
                            
                                //МЭС?, у меня пока нет 
				if($rows['Mes_id'] != "" && $rows['Mes_id'] != 0)
				{
					$percentage = (isset($rows['KoikoDni']) && ($rows['KoikoDni']>0))?floor(($rows['EvnSecdni']/$rows['KoikoDni'])*100):'?';
					$mes = '<b>'.$mes_alias.':</b> '.$rows['KoikoDni'].' дней ('.$percentage.'%)';
				}
                                //ПОЛ
				if($rows['Sex_id'] == 1)
				{
					$icon = 'male16';
				}
				else
				{
					$icon = 'female16';
				}
                                //ВОЗРАСТ
				if ( $rows['Person_Age'] == 0 ) {
					$rows['Person_Age'] = $rows['Person_AgeMonth'].' мес.';
				}
				else {
					$o = str_split($rows['Person_Age'].'');
					$n = count($o)-1;
					switch($o[$n])
					{
						case '1':
							if( $rows['Person_Age'] == 11 )
								$rows['Person_Age'].=' лет';
							else
								$rows['Person_Age'].=' год';
							break;

						case '2':
						case '3':
						case '4':
							if( in_array($rows['Person_Age'],array(12,13,14)) )
								$rows['Person_Age'].=' лет';
							else
								$rows['Person_Age'].=' года';
							break;

						default:
							$rows['Person_Age'].=' лет';
							break;
					}
				}
                            
                $text = '';   
                                
				$text .= '<img title="В реанимации" src="/img/icons/ambulance16.png" />';
                            
                //сведения об услуге/операции??
				$surgery = $this->dbmodel->getSurgeryforEvn($rows['EvnReanimatPeriod_rid']);
				$sur_cnt = count($surgery);
				if($sur_cnt > 0)
				{
					$todate = strtotime($data['date']);
					$uslugadate = strtotime($surgery[$sur_cnt-1]['EvnUsluga_setDate']);
					if($todate < $uslugadate)
					{
						$text .= '<img title="запланирована операция пациенту на '.$surgery[$sur_cnt-1]['EvnUsluga_setDate'].'" src="/img/icons/hand-red16.png" />';
					}
					elseif($todate >= $uslugadate)
					{
						$text .= '<img title="'.$surgery[$sur_cnt-1]['EvnUsluga_setDate'].' пациент прооперирован" src="/img/icons/hand-green16.png" />';
					}
				}
                                //номер карты
				if($rows['EvnPS_NumCard'] != '')
				{
					$rows['EvnPS_NumCard'] = '№'.$rows['EvnPS_NumCard'];
				}
                            
				if (isset($mes)){
					$mes = '&nbsp;&nbsp;&nbsp;'.$mes;
				} else {
					$mes = '';
				}

                //ЛЕЧАЩИЙ ВРАЧ
				$hint = '';
				if (isset($rows['MedPersonal_Fin']) && !empty($rows['MedPersonal_Fin'])) {
					$hint .= 'Лечащий врач: '. $rows['MedPersonal_Fin'];
				}
                
                $background = '';
				if (isset($rows['PersonQuarantine_IsOn']) && $rows['PersonQuarantine_IsOn'] == 2) {
					$background = 'background: #FCC;';
				}
				
				if (allowPersonEncrypHIV($data['session']) && !empty($rows['PersonEncrypHIV_Encryp'])) {
					$person_info_text = '<span title="'. $hint .'" style="color: darkblue; '.$background.'">'.$rows['PersonEncrypHIV_Encryp'].'</span>';
				} else {
					$person_info_text = '<span title="'. $hint .'" style="color: darkblue; '.$background.'">'.$rows['Person_Fio'].'</span>&nbsp;('.$rows['Person_Age'].')';
				}
                //   echo '<pre>' . print_r((gettype($rows['Diag_Code'])!='NULL')?$rows['Diag_Code']:'yyyyy' , 1) . '</pre>'; //BOB - 14.03.2017

				$entered = 'Поступил';
				$discharged = 'Выписан';
				if (getRegionNick() == 'vologda') {
					$entered .= ' в ПДО';
					$discharged = 'Выбыл';
				}
				
                $text .= $person_info_text.'&nbsp;&nbsp;&nbsp;'.$rows['EvnPS_NumCard'].'&nbsp;&nbsp;'.$rows['LpuSection_Name'].                                        
					'&nbsp;&nbsp;<b>Диагноз:</b> '.((gettype($rows['Diag_Code'])!='NULL')?$rows['Diag_Code']:'').
					'<span>&nbsp;&nbsp;&nbsp;<b>' . $entered . ':</b> '.$rows['EvnReanimatPeriod_setDate'].
					'</span>'.(($rows['EvnReanimatPeriod_disDate'])?'&nbsp;&nbsp;<span><b>' . $discharged . ':</b> '.$rows['EvnReanimatPeriod_disDate'].
					'</span>':'').$mes; /**/

				//echo '<pre>' . print_r($text, 1) . '</pre>'; //BOB - 14.03.2017

				$obj = array(
					'text' => toUTF($text),
					'id' => $rows['EvnReanimatPeriod_id'],
					'EvnSection_id' => $rows['EvnReanimatPeriod_id'],
					'EvnSection_setDate' => $rows['EvnReanimatPeriod_setDate'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'LpuSectionWard_id' => '',
					'MedPersonal_id' => '',
					'Sex_id' => $rows['Sex_id'],
					'Person_id' => $rows['Person_id'],
					'Server_id' => $rows['Server_id'],
					'PersonEvn_id' => $rows['PersonEvn_id'],
					'Person_BirthDay' => $rows['Person_BirthDay'],
					'PersonEncrypHIV_Encryp' => allowPersonEncrypHIV($data['session'])?$rows['PersonEncrypHIV_Encryp']:null,
					'EvnPS_id' => $rows['EvnPS_id'],
					'leaf' => true,
					'group'	=> $data['group'],
					'iconCls' => $icon,
					'cls' => 'folder'
				);
				$result[] = $obj;
				if(isset($mes))
				{
					unset($mes);
				}
			}

			return $this->ReturnData($result);
		}
		//BOB - 14.03.2017
                
                
		// Сначала отображаем палаты на первом уровне иерархии
		if (0 == $data['level']) {
			//выводим не закрытые палаты
			$data['notCloseWard'] = true;
			$this->load->model('HospitalWard_model', 'HospitalWard_model');
			$childrens = $this->HospitalWard_model->getHospitalWardList($data);
			if ( !is_array($childrens) )
			{
				echo json_encode( array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Ошибка БД при получении данных дочерних узлов!') ) );
				return false;
			}
			foreach ($childrens as $rows)
			{
				$rows['Lpu_id'] = $data['Lpu_id'];
				switch($rows['Sex_id'])
				{
					case 1:
						$nodeIcon = '/img/icons/tree-rooms-male.png';
						$wardsex = 'Мужская палата';
						break;

					case 2:
						$nodeIcon = '/img/icons/tree-rooms-female.png';
						$wardsex = 'Женская палата';
						break;

					default:
						$nodeIcon = '/img/icons/tree-rooms-common.png';
						$wardsex = 'Общая палата';
						break;
				}

				if($rows['LpuWardType_id'] == 2)
				{
					$wardtype = ' повышенной комфортности';
				}
				else
				{
					$wardtype = '';
				}
				$BedWard_count = $this->HospitalWard_model->getCountBedLpuSectionWard($rows, $data['date']);
				/*

				$text = '<div class="x-tree-node-text"><div style="margin-left: 14px;" title="'.$wardsex.''.$wardtype.', всего мест - '.$BedWard_count[0]['onlyBed_cnt'].', свободных мест - '.$BedWard_count[0]['freedomBed_cnt'].'">'
					.$BedWard_count[0]['onlyBed_cnt'].
					'</div><div style="margin-left: 14px; margin-top: -10px; color: blue;" title="'.$wardsex.''.$wardtype.', всего мест - '.$BedWard_count[0]['onlyBed_cnt'].', свободных мест - '.$BedWard_count[0]['freedomBed_cnt'].'">'
					.$BedWard_count[0]['freedomBed_cnt'].
					'</div></div><div style="margin-top: -20px; margin-left: 55px;">'.$rows['LpuSectionWard_Name'].'</div>';

				$text .= '<div title="'.$wardsex.''.$wardtype.', всего мест - '.$BedWard_count[0]['onlyBed_cnt'].', свободных мест - '.$BedWard_count[0]['freedomBed_cnt'].'" style="font-size: 14pt; height: 20px; width: 10px; margin-top: -20px; margin-left: 20px;">&nbsp;&nbsp;</div>';

				if($rows['LpuWardType_id'] == 2)
				{
					$text.= '<div title="'.$wardsex.' повышенной комфортности, всего мест - '.$BedWard_count[0]['onlyBed_cnt'].', свободных мест - '.$BedWard_count[0]['freedomBed_cnt'].'" style="width: 10px; margin-top: -18px; margin-left: 42px; font-weight: bold; font-size: 12pt; color: #ffd700;">+</div>';
				}
				*/
				if (getRegionNick() != 'kz') {
					$text = '<div class="x-tree-node-text"><div style="margin-left: 14px;">&nbsp;</div><div style="margin-left: 14px; margin-top: -10px; color: blue;">&nbsp;</div></div><div title="'.
						$wardsex.''.$wardtype.'" style="margin-top: -20px; margin-left: 55px;">'.
						$rows['LpuSectionWard_Name'].' (Свободно '.$BedWard_count[0]['freedomBed_cnt'].' коек)</div>';
				} else {
					$text = '<div class="x-tree-node-text"><div style="margin-left: 14px;">&nbsp;</div><div style="margin-left: 14px; margin-top: -10px; color: blue;">&nbsp;</div></div><div title="'.
						$wardsex.''.$wardtype.'" style="margin-top: -20px; margin-left: 55px;">'.
						$rows['LpuSectionWard_Name'].'</div>';
				}
				

				$text .= '<div style="font-size: 14pt; height: 20px; width: 10px; margin-top: -20px; margin-left: 30px;">&nbsp;&nbsp;</div>';

				if($rows['LpuWardType_id'] == 2)
				{
					$text.= '<div title="'.
						$wardsex.''.$wardtype.'" style="width: 10px; margin-top: -18px; margin-left: 42px; font-weight: bold; font-size: 12pt; color: #ffd700;">+</div>';
				}
				$obj = array(
					'text' => toUTF($text),
					'id' => 'LpuSectionWard_id_'.$rows['LpuSectionWard_id'],
					'group'	=> 'pacients_inward',
					'date' => $rows['LpuSectionWard_id'],
					'object' => 'LpuSectionWard',
					'object_id' => 'LpuSectionWard_id',
					'object_value' => $rows['LpuSectionWard_id'],
					'leaf' => false,
					'iconCls' => '',
					'icon' => $nodeIcon,
					'cls' => 'x-tree-node-24x24'
				);
				$result[] = $obj;
			}
			// чтобы отобразились пациенты без палаты на первом уровне
			$data['object_value'] = -1;
		}

		if (1 == $data['level'] && $data['group'] == 'pacients_anothersection') {// отображаем переведенных из другого отделения
			$result = array_merge($result, $this->getAnotherSection($data));// повторяющийся код вынес в функцию
		}
		else {
			// отображаем пациентов в палатах или без, свободные койки
			$BedWardAll = null;
			if ( $data['object_value'] > 0 )
			{
				$this->load->model('HospitalWard_model', 'HospitalWard_model');
				//'EvnSection_id' => true для того чтобы считать койки по профильному отделению, а не по приемному
				$BedWardAll = $this->HospitalWard_model->getLpuSectionWardBedCount(array('EvnSection_id' => true,'LpuSection_id' => $data['LpuSection_id'],'LpuSectionWard_id' => $data['object_value'],'date' => $data['date']));
			}

			$childrens = $this->dbmodel->getLpuSectionPatientList($data);

			if ( !is_array($childrens) )
			{
				echo json_encode( array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Ошибка БД при получении данных дочерних узлов!') ) );
				return false;
			}

			$mes_alias = getMESAlias();
			foreach ($childrens as $rows) {
				if($rows['Mes_id'] != "" && $rows['Mes_id'] != 0)
				{
					$percentage = (isset($rows['KoikoDni']) && ($rows['KoikoDni']>0))?floor(($rows['EvnSecdni']/$rows['KoikoDni'])*100):'?';
					$mes = '<b>'.$mes_alias.':</b> '.$rows['KoikoDni'].' дней ('.$percentage.'%)';
				}
				if($rows['Sex_id'] == 1)
				{
					$icon = 'male16';
				}
				else
				{
					$icon = 'female16';
				}

				if ( $rows['Person_Age'] == 0 ) {
					$rows['Person_Age'] = $rows['Person_AgeMonth'].' мес.';
				}
				else {
					$o = str_split($rows['Person_Age'].'');
					$n = count($o)-1;
					switch($o[$n])
					{
						case '1':
							if( $rows['Person_Age'] == 11 )
								$rows['Person_Age'].=' лет';
							else
								$rows['Person_Age'].=' год';
							break;

						case '2':
						case '3':
						case '4':
							if( in_array($rows['Person_Age'],array(12,13,14)) )
								$rows['Person_Age'].=' лет';
							else
								$rows['Person_Age'].=' года';
							break;

						default:
							$rows['Person_Age'].=' лет';
							break;
					}
				}


				$text = '';
				//BOB - 21.11.2017
				if($rows['EvnReanimatPeriod_id'] != 0)
				{
					$text .= '<img title="В реанимации" src="/img/icons/ambulance16.png" />';
				}
				//BOB - 21.11.2017

				if (isset($rows['EvnSection_PlanDisDT']) && $rows['EvnSection_PlanDisDT'] != '')
				{
					$text .= '<img title="запланирована выписка пациента на '.$rows['EvnSection_PlanDisDT'].'" src="/img/icons/patient-leave16.png" />';
				}

				$surgery = $this->dbmodel->getSurgeryforEvn($rows['EvnSection_rid']);

				$sur_cnt = count($surgery);
				if($sur_cnt > 0)
				{
					$todate = strtotime($data['date']);
					$uslugadate = strtotime($surgery[$sur_cnt-1]['EvnUsluga_setDate']);
					if($todate < $uslugadate)
					{
						$text .= '<img title="запланирована операция пациенту на '.$surgery[$sur_cnt-1]['EvnUsluga_setDate'].'" src="/img/icons/hand-red16.png" />';
					}
					elseif($todate >= $uslugadate)
					{
						$text .= '<img title="'.$surgery[$sur_cnt-1]['EvnUsluga_setDate'].' пациент прооперирован" src="/img/icons/hand-green16.png" />';
					}
				}

				if($rows['EvnPS_NumCard'] != '')
				{
					$rows['EvnPS_NumCard'] = '№'.$rows['EvnPS_NumCard'];
				}

				if (isset($mes)){
					$mes = '&nbsp;&nbsp;&nbsp;'.$mes;
				} else {
					$mes = '';
				}
				$hint = '';
				if (!empty($rows['MedPersonal_Fin'])) {
					$hint .= 'Лечащий врач: '. $rows['MedPersonal_Fin'];
				}
				//print_r($rows);
				$background = '';
				if (isset($rows['PersonQuarantine_IsOn']) && $rows['PersonQuarantine_IsOn'] == 2) {
					$background = 'background: #FCC;';
				}
				if (allowPersonEncrypHIV($data['session']) && !empty($rows['PersonEncrypHIV_Encryp'])) {
					$person_info_text = '<span title="'. $hint .'" style="color: darkblue; '.$background.'">'.$rows['PersonEncrypHIV_Encryp'].'</span>';
				} else {
					$person_info_text = '<span title="'. $hint .'" style="color: darkblue; '.$background.'">'.$rows['Person_Fio'].'</span>&nbsp;('.$rows['Person_Age'].')';
				}

				$entered = 'Поступил';
				$discharged = 'Выписан';
				if (getRegionNick() == 'vologda') {
					$entered .= ' в ПДО';
					$discharged = 'Выбыл';
				}
				
				$text .= $person_info_text.'&nbsp;&nbsp;&nbsp;'.$rows['EvnPS_NumCard'].
					'&nbsp;&nbsp;<b>Диагноз:</b> '.$rows['Diag_Code'].
					'<span>&nbsp;&nbsp;&nbsp;<b>' . $entered . ':</b> '.$rows['EvnSection_setDate'].
					'</span>'.(($rows['EvnSection_disDate'])?'&nbsp;&nbsp;<span><b>' . $discharged . ':</b> '.$rows['EvnSection_disDate'].
					'</span>':'').$mes;

				$obj = array(
					'text' => toUTF($text),
					'id' => $rows['EvnSection_id'],
					'EvnSection_id' => $rows['EvnSection_id'],
					'EvnSection_setDate' => $rows['EvnSection_setDate'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'LpuSectionWard_id' => $rows['LpuSectionWard_id'],
					'MedPersonal_id' => $rows['MedPersonal_id'],
					'Sex_id' => $rows['Sex_id'],
					'PayType_id' => isset($rows['PayType_id']) ? $rows['PayType_id'] : null,
					'Person_id' => $rows['Person_id'],
					'Server_id' => $rows['Server_id'],
					'PersonEvn_id' => $rows['PersonEvn_id'],
					'Person_BirthDay' => $rows['Person_BirthDay'],
					'PersonEncrypHIV_Encryp' => allowPersonEncrypHIV($data['session'])?$rows['PersonEncrypHIV_Encryp']:null,
					'EvnPS_id' => $rows['EvnPS_id'],
					'DiagFinance_IsRankin' => $rows['DiagFinance_IsRankin'] ?? null,
					'leaf' => true,
					'group'	=> $data['group'],
					'iconCls' => $icon,
					'cls' => 'folder'
				);
				$result[] = $obj;
				if(isset($mes))
				{
					unset($mes);
				}
			}
		}

		if (0 == $data['level']) {
			//Ниже всех на первом уровне отображаем папку "Переведены из других отделений"
			$i = count($result);
			$style = '';
			$text = 'Переведены из других отделений';
			if (getRegionNick() == 'msk') {
				$anotherSectionPatients = $this->dbmodel->getAnotherLpuSectionPatientList($data);
				$countAnotherSectionPatients = count(!is_bool($anotherSectionPatients) ? $anotherSectionPatients : []);
				if ($countAnotherSectionPatients > 0) {
					$style = 'color: red;';
					$text .= '(' . $countAnotherSectionPatients . ')';
				}
			}
			
			$params = array(
				'text'			=> toUTF('<span class="x-tree-node-text"  style="line-height:28px;margin:0;"></span><span style="margin-left:0;' . $style . '" class="tree-folder">' . $text . '</span>'),
				'group'			=> 'pacients_anothersection',
				'leaf'			=> false,
				'iconCls'		=> '',
				'cls'			=> 'x-tree-node-24x24',
				'icon'			=> '/img/icons/tree-rooms-common.png',
				'object_value'	=> -4,
				'id'			=> 'anothersection'
			);
			if (getRegionNick() == 'msk') {
				array_unshift($result, $params);
			} else {
				$result[$i] = $params;
			}
			
		}

		return $this->ReturnData($result);
	}
	
	/**
	 * Копирование движения при приёме из другого отделения.
	 */
	function saveEvnSectionFromOtherLpu() {
		$data = $this->ProcessInputData('saveEvnSectionFromOtherLpu', true);
		if ($data === false) { return false; }
		// загружаем данные исходного отделения
		$this->load->model('EvnSection_model', 'EvnSection');
		$this->EvnSection->setAttributes(array('EvnSection_id' => $data['EvnSection_id']));
		// мержим его данные с входными данными
		$evnSectionData = array();
		$evnSectionData['EvnSection_id'] = NULL;
		$evnSectionData['EvnSection_pid'] = $this->EvnSection->pid;
		$evnSectionData['EvnSection_setDate'] = $this->EvnSection->disDate;
		$evnSectionData['EvnSection_setTime'] = $this->EvnSection->disTime;
		$evnSectionData['PayType_id'] = $this->EvnSection->PayType_id;
		$evnSectionData['Person_id'] = $this->EvnSection->Person_id;
		$evnSectionData['Server_id'] = $this->EvnSection->Server_id;
		$evnSectionData['PersonEvn_id'] = $this->EvnSection->PersonEvn_id;
		//$evnSectionData['MedStaffFact_id'] = $this->EvnSection->MedStaffFact_id;
		$evnSectionData['UslugaComplex_id'] = $this->EvnSection->UslugaComplex_id;
		$evnSectionData['LpuSectionProfile_id'] = $this->EvnSection->LpuSectionProfile_id;
		$evnSectionData['MedPersonal_id'] = $data['MedPersonal_id'];
		$evnSectionData['LpuSection_id'] = $data['LpuSection_id'];
		$evnSectionData['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		$evnSectionData = array_merge($data, $evnSectionData);
		$evnSectionData['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		// сохраняем
		$response = $this->dbmodel->doSave($evnSectionData);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении движения по стационару')
			->ReturnData();
		return true;
	}
	
	/**
	 * Печать списка пациентов
	 * @return bool
	 */
	function printPatientList()
	{
		$this->load->library('parser');
		$view = (getRegionNick() == 'msk') ? 'evn_lpusection_patientlist_msk' : 'evn_lpusection_patientlist';
		$val = array();
		$data = $_POST;
		
		$this->load->model('HospitalWard_model', 'Ward_model');
		
		$response = $this->Ward_model->getHospitalWardList($data);
		
		if (!is_array($response))
		{
			echo json_encode( array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Ошибка БД при получении списка пациентов!') ) );
			return false;
		}
		else
		{
			$response[count($response)] = array(
				'LpuSectionWard_id' => 0,
				'LpuSectionWard_Name' => 'Пациенты без палаты'
			);
			for($i=0; $i<count($response); $i++)
			{
				$data['LpuSectionWard_id'] = $response[$i]['LpuSectionWard_id'];
				$patients = $this->dbmodel->getforPrintLpuSectionPatientList($data);
				for ( $j = 0; $j < count($patients); $j++ ) {
					$patients[$j]['Record_Num'] = $j + 1;
				}
				$val[$i] = $response[$i];
				$val[$i]['patients'] = $patients;
				if(count($patients) == 0)
				{
					unset($val[$i]);
				}
			}
			$this->parser->parse($view, array('search_results' => $val, 'date' => date('d.m.Y H:i:s')));
			return true;
		}
	}
	
	/**
	*  Получение списка случаев движения пациента в стационаре
	*  Входящие данные: $_POST['EvnSection_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты выбывшего из стационара
	 * @return bool
	 */
	function loadEvnSectionGrid() {
		//$data = array();
		//$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnSectionGrid', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadEvnSectionGrid($data);

		/*if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		$this->ReturnData($val);*/
		$this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}


	/**
	*  Получение списка "Лечение" для окна "Специфика онкологического заболевания"
	*  Входящие данные: $_POST['EvnSection_id'], $_POST['Morbus_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты выбывшего из стационара
	 * @return bool
	 */
	function loadEvnSectionGridMorbusOnko() {
		//$data = array();
		//$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnSectionGridMorbusOnko', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadEvnSectionGridMorbusOnko($data);

		$this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}


	/**
	*  Получение списка МЭС
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая движения пациента в стационаре
	 * @return bool
	 */
	function loadMesList() {
		//$data = array();
		//$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadMesList', true);

		if ( $data === false ) {
			return false;
		}
		
		if ( (!isset($data['Diag_id']) || !isset($data['EvnSection_setDate']) || !isset($data['LpuSection_id']) || !isset($data['Person_id'])) && (!isset($data['Mes_id'])) ) {
			echo json_return_errors('Неверные параметры');
			return false;
		}

		$response = $this->dbmodel->loadMesOldList($data);

		/*if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);*/
		$this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}

	/**
	 * 
	 * @return boolean
	 */
	function getSectionPriemData(){
	    $data = $this->ProcessInputData('getSectionPriemData', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getSectionPriemData($data);

		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();

		return true;
	}
	
	/**
	*  Получение списка МЭС2
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая движения пациента в стационаре
	 * @return bool
	 */
	function loadMes2List() {
		//$data = array();
		//$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadMes2List', true);

		if ( $data === false ) {
			return false;
		}
		
		if (!isset($data['Diag_id'])) {
			echo json_return_errors('Неверные параметры');
			return false;
		}

		$response = $this->dbmodel->loadMes2List($data);

		$this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}

	/**
	 *  Сохранение случая движения пациента в стационаре
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования случая движения пациента в стационаре
	 * @throws Exception
	 * @return bool
	 */
	function saveEvnSection() {
		$this->inputRules['saveEvnSection'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$this->inputRules['saveEvnSection'] = array_merge($this->inputRules['saveEvnSection'],

			array(
				array(
					'field' => 'EvnSection_PlanDisDT',
					'label' => 'Планируемая дата выписки',
					'rules' => '',
					'type' => 'date'
				),
				'silentSave' => array(
					'field' => 'silentSave',
					'label' => 'Автосохранение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedProfileLink_fedid',
					'label' => 'таблица стыковки fed.LpuSectionBedProfileLink',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PrehospWaifRetired_id',
					'label' => 'Беспризорный выбыл',
					'rules' => '',
					'type' => 'id'
				)
			)
		);

		if (isset($_POST['birthDataPresented']) && ('2' == $_POST['birthDataPresented'])) {
			//заполнены даные о беременности
			$this->inputRules['saveEvnSection'] = array_merge($this->inputRules['saveEvnSection'], $this->inputRules['evnBirthData']);
		}
		$data = $this->ProcessInputData('saveEvnSection', true);
		$HospArray = array(
			'Person_id' => $data["Person_id"],
			'EvnSection_setDate' => $data["EvnSection_setDate"],
			'EvnSection_disDate' => $data["EvnSection_disDate"],
			'LpuSection_id' => $data["LpuSection_id"],
			'EvnSection_pid' => $data["EvnSection_pid"],
			'ResultDesease_id' => $data["ResultDesease_id"],
			'IsLeave' => $data["LeaveType_id"] ? true : false,
			'pmUser_id' => $data["pmUser_id"],
			'EvnSection_id' => $data["EvnSection_id"],
			'Diag_id' => $data["Diag_id"],
			'Lpu_id' => $data["Lpu_id"],
			'LpuSection_id' => $data["LpuSection_id"],
		);
		if ( $data === false ) { return false; }
		if ( empty($data['silentSave']) && empty($data['isAutoCreate']) ) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}
		$data['LpuSectionWardCur_id'] = $this->dbmodel->getCurLpuSectionWard($data['EvnSection_id']);
		$data['LpuSectionBedProfileLinkCur_fedid'] = $this->dbmodel->getCurLpuSectionBedProfile($data['EvnSection_id']);

		$response = $this->dbmodel->doSave($data);

		// #182939
		// Если заданы окружность головы или груди, сохраним их в HeadCircumference
		// и ChestCircumference:
		if (isset($data['PersonNewBorn_Head']) || isset($data['PersonNewBorn_Breast']))
			{
				// Заносим в $pcId значение PersonChild_id.
				// 1. Из исходных данных:
				if (isset($data['PersonChild_id']))
					$pcId = $data['PersonChild_id'];
				else
				{
					// 2. В исходных данных нет - ищем в БД по Person_id:
					$this->load->model('PersonChild_model');
					$resp = $this->PersonChild_model->loadPersonChildData(
								array('Person_id' => $data['Person_id']));

					if (is_array($resp) && count($resp) > 0)
						$pcId = $resp[0]['PersonChild_id'];
					else
					{
						// 3. В БД нет - создаем новую запись PersonChild:
						$resp = $this->PersonChild_model->savePersonChild(array(
									'Person_id' => $data['Person_id'],
									'Server_id' => $data['Server_id'],
									'pmUser_id' => $this->dbmodel->promedUserId));

						if (is_array($resp) && count($resp) > 0)
							$pcId = $resp[0]['PersonChild_id'];
					}

					// 4. Так и не удалось определить $pcId - выходим с ошибкой:
					if (!isset($pcId))
						return false;
				}

				// Сохраняем PersonNewBorn_Head в HeadCircumference, указываем вид
				// замера "При рождении". Если запись с таким видом уже есть,
				// редактируем ее, если нет - создаем новую:
				if (isset($data['PersonNewBorn_Head']))
				{
					$hcId = NULL;

					$this->load->model('HeadCircumference_model');
					$result = $this->HeadCircumference_model->getIdByPersonChild([
						'PersonChild_id' => $pcId
					]);

					if (is_object($result))
					{
						$res = $result->result('array');

						if (count($res) > 0)
							// Нашли - запоминаем в $hcId
							$hcId = $res[0]['HeadCircumference_id'];
					}

 					$resp = $this->HeadCircumference_model->saveHeadCircumference(
							array(
								'HeadCircumference_id' => $hcId,
								'Person_id' => $data['Person_id'],
								'PersonChild_id' => $pcId,
								'HeightMeasureType_id' => '1',
								'HeadCircumference_Head' => $data['PersonNewBorn_Head'],
								'pmUser_id' => $this->dbmodel->promedUserId
								));

					if (!is_array($resp) || count($resp) == 0)
						return false;

					if (isset($resp['Error_Msg']))
						throw new Exception($resp['Error_Msg']);
				}

				// Сохраняем PersonNewBorn_Breast в ChestCircumference, указываем вид
				// замера "При рождении". Если запись с таким видом уже есть,
				// редактируем ее, если нет - создаем новую:
				if (isset($data['PersonNewBorn_Breast']))
				{
					$ccId = NULL;

					$this->load->model('ChestCircumference_model');
					$result = $this->ChestCircumference_model->getIdByPersonChild([
						'PersonChild_id' => $pcId
					]);

					if (is_object($result))
					{
						$res = $result->result('array');

						if (count($res) > 0)
							// Нашли - запоминаем в $ccId
							$ccId = $res[0]['ChestCircumference_id'];
					}

 					$resp = $this->ChestCircumference_model->saveChestCircumference(
							array(
								'ChestCircumference_id' => $ccId,
								'Person_id' => $data['Person_id'],
								'PersonChild_id' => $pcId,
								'HeightMeasureType_id' => '1',
								'ChestCircumference_Chest' => $data['PersonNewBorn_Breast'],
								'pmUser_id' => $this->dbmodel->promedUserId
								));

					if (!is_array($resp) || count($resp) == 0)
						return false;

					if (isset($resp['Error_Msg']))
						throw new Exception($resp['Error_Msg']);
				}
			}

		if (!empty($response['EvnSection_id']) && empty($response['Error_Msg'])) {
			$params = $data;
			$params['source'] = 'EvnSection';
			$params['EvnSection_id'] = $response['EvnSection_id'];
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);

			if (!empty($evnId = $response['EvnSection_id']) && $evnId > 0 && empty($response['Error_Code']))
			{
				$evnId = (!empty($data['EvnSection_id']) ? $data['EvnSection_id'] : $evnId);
	
				$this->saveEvnDiagHSNDetails(
					array(
						'Evn_id' => $evnId,
						'pmUser_id' => $data['pmUser_id']
					));
			}
		}
		
		if($data['LpuSectionWard_id'] != $data['LpuSectionWardCur_id'])
			$this->dbmodel->updateLpuSectionWardHistory($response['EvnSection_id'], $data['LpuSectionWard_id'], $data['LpuSectionWardCur_id']);
		if($data['LpuSectionBedProfileLink_fedid'] != $data['LpuSectionBedProfileLinkCur_fedid'])
			$this->dbmodel->updateLpuSectionBedProfileHistory($response['EvnSection_id'], $data['LpuSectionBedProfileLink_fedid'], $data['LpuSectionBedProfileLinkCur_fedid']);

		$this->ProcessModelSave($response, true)->ReturnData();

		if ($HospArray["IsLeave"] && !empty($response['EvnSection_id']) && empty($response['Error_Msg'])) {
			$this->load->model('MorbusOnkoSpecifics_model');
			$DiagCode = $this->MorbusOnkoSpecifics_model->getDiagName($HospArray);
			if ($DiagCode) {
				if ($DiagCode["Diag_Code"][0] == 'C' ||
					in_array(substr($DiagCode["Diag_Code"], 0, -2), array('D00', 'D01', 'D02', 'D03', 'D04', 'D05', 'D06', 'D07', 'D08', 'D09'))) {
					$this->load->model('MorbusOnkoBasePS_model');
					$HospFound = $this->MorbusOnkoBasePS_model->getHosp($HospArray);
					if (!$HospFound) {
						$resp = $this->MorbusOnkoBasePS_model->getMorbusBaseData($HospArray);
						if ($resp) {
							$this->load->model('MorbusOnkoSpecifics_model');
							$arrayData = $this->MorbusOnkoSpecifics_model->getOnkoSpecificData($HospArray);
							//Стыковка справочников исхода (Заполняется на основе Исхода заболевания (ResultDesease_id))
							if (in_array($HospArray["ResultDesease_id"], array('140','144','14','62','66','181','185','127','131', '1'))) {
								$ResultDesease_id = 1;
							}
							else if (in_array($HospArray["ResultDesease_id"], array('141','145','20','63','67','182','186','128','132', '6'))) {
								$ResultDesease_id = 2;
							}
							else if (in_array($HospArray["ResultDesease_id"], array('142','146','21','64','68','183','187','129','133', '11'))) {
								$ResultDesease_id = 3;
							}
							else if (in_array($HospArray["ResultDesease_id"], array('143','147','22','65','69','184','188','130','134', '10'))) {
							$ResultDesease_id = 4;
							} else {
								$ResultDesease_id = 6;
							}
							//Стыковка цель госпитализации (OnkoTreatment_id)
							if (in_array($resp["OnkoTreatment_id"], array('15','20','81','5','10','103'))) {
								$OnkoTreatment_id = 1;
							}
							else if (in_array($resp["OnkoTreatment_id"], array('16','21','82','6','11','104'))) {
								$OnkoTreatment_id = 3;
							}
							else if (in_array($resp["OnkoTreatment_id"], array('17','22','83','7','12','105'))) {
								$OnkoTreatment_id = 2;
							}
							else if (in_array($resp["OnkoTreatment_id"], array('101','89','113','88','110','117'))) {
								$OnkoTreatment_id = 5;
							}
							else if (in_array($resp["OnkoTreatment_id"], array('102','100','114','90','111','118'))) {
								$OnkoTreatment_id = 9;
							} else {
								$OnkoTreatment_id = 11;
							}
							$paramsHosp = array(
								'pmUser_id' => $HospArray["pmUser_id"],
								'MorbusOnkoBase_id' => $resp["MorbusOnkoBase_id"],
								'Evn_id' => $HospArray["EvnSection_id"],
								'MorbusOnkoBasePS_setDT' => $HospArray["EvnSection_setDate"],
								'MorbusOnkoBasePS_disDT' => $HospArray["EvnSection_disDate"],
								'OnkoHospType_id' => null,
								'Diag_id' => $HospArray["Diag_id"],
								'OnkoPurposeHospType_id' => $OnkoTreatment_id,
								'Lpu_id' => $HospArray["Lpu_id"],
								'LpuSection_id' => $HospArray["LpuSection_id"],
								'MorbusOnkoBasePS_IsTreatDelay' => '1',
								'MorbusOnkoBasePS_IsNotTreat' => '1',
								'MorbusOnkoBasePS_IsSurg' => $arrayData["IsSurg"] ? '2' : '1',
								'MorbusOnkoBasePS_IsPreOper' => '1',
								'MorbusOnkoBasePS_IsIntraOper' => '1',
								'MorbusOnkoBasePS_IsPostOper' => '1',
								'MorbusOnkoBasePS_IsBeam' => $arrayData["IsBeam"] ? '2' : '1',
								'MorbusOnkoBasePS_IsChem' => $arrayData["IsChem"] ? '2' : '1',
								'MorbusOnkoBasePS_IsGormun' => $arrayData["IsGormun"] ? '2' : '1',
								'MorbusOnkoBasePS_IsImmun' => $arrayData["IsGormun"] ? '2' : '1',
								'MorbusOnkoBasePS_IsOther' => '1',
								'OnkoLeaveType_id' => $ResultDesease_id,
							);
							$this->load->model('MorbusOnkoBasePS_model');
							$response = $this->MorbusOnkoBasePS_model->save($paramsHosp);
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Получение стадии ХСН
	 */
	function getHsnStage() {
		$this->load->model('Evn_model', 'Evn_model');
		$response = $this->Evn_model->getHsnStage();
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Получение функционального класса ХСН
	 */
	function getHSNFuncClass() {
		$this->load->model('Evn_model', 'Evn_model');
		$response = $this->Evn_model->getHSNFuncClass();
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Сохранение детализации диагноза ХСН по пациенту в рамках события
	 */
	function saveEvnDiagHSNDetails($params)
	{
		$data = $this->ProcessInputData('saveEvnDiagHSNDetails', false);

		if ($data === false)
			return false;
		
		$data['saveEvnPL'] = $params;
		$data['Evn_id']= $params['Evn_id'];
		$data['pmUser_id'] = $params['pmUser_id'];		
		$data['nonDel'] = $this->dbmodel->checkHSNDiagExists(array('Evn_id' => $params['Evn_id']));	

		$this->load->model('Evn_model', 'Evn_model');
		$this->Evn_model->saveEvnDiagHSNDetails($data);
	}

	/**
	 * Получение последней детализации диагноза ХСН по пациенту
	 */
	function getLastHsnDetails()
	{
		$data = $this->ProcessInputData('getLastHsnDetails', false);

		if ($data === false)
			return false;
		
		$this->load->model('Evn_model', 'Evn_model');
		$response = $this->Evn_model->getLastHsnDetails($data);

		$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 *  Получение групп движений
	 */
	function getEvnSectionIndexNum() {
		$data = $this->ProcessInputData('getEvnSectionIndexNum', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnSectionIndexNum($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Установка группы
	 */
	function setEvnSectionIndexNum() {
		$data = $this->ProcessInputData('setEvnSectionIndexNum', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->setEvnSectionIndexNum($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка шаблона документа для стационара
	 */
	function getEvnDocumentStac()
	{
		$this->load->model('EvnSection_model', 'EvnSection');
		$data = $_POST;
		//$this->document = '';
		
		$response = $this->EvnSection->getEvnDocumentStac($data);

		$val = array();

		if ( is_array($response) )
		{
			if(count($response) > 0)
			{
				foreach($response as $row)
				{
					array_walk($row, 'ConvertFromWin1251ToUTF8');

					$sticks = array();
					$surgery = array();
					
					if($row['EvnSection_pid'] != '')
					{
						$sticks = $this->EvnSection->getStickforEvn($row['EvnSection_pid']);
						for ($i=0; $i<count($sticks); $i++)
						{
							array_walk($sticks[$i], 'ConvertFromWin1251ToUTF8');
						}
						$surgery = $this->EvnSection->getSurgeryforEvn($row['EvnSection_rid']);
						for ($i=0; $i<count($surgery); $i++)
						{
							array_walk($surgery[$i], 'ConvertFromWin1251ToUTF8');
						}
						$val = $row;
					}
					//не знаю зачем это здесь, но погружаться некогда
					if (count($sticks)) {
						$val['sticks'] = $sticks;
					} else {
						$val['sticks'] = '';
					}
					if (count($surgery)) {
						$val['surgery'] = $surgery;
					} else {
						$val['surgery'] = '';
					}
				}
			}
		}
		$this->ReturnData($val);
	}
	
	/**
	 * Получение количества коек в отделении
	 */
	function getCountBedLpuSection()
	{
		$this->load->model('HospitalWard_model', 'hmodel');
		$data = $_POST;
		
		$data['date'] = explode(".", $data['date']);
		$data['date'] = $data['date'][2]."-".$data['date'][1]."-".$data['date'][0];
		$response = $this->hmodel->getCountBedLpuSection($data);

		/*if ( is_array($response) )
		{
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			
			foreach ($response as $row)
			{
				$val = $row;
			}
			$this->ReturnData($val);
		}*/
		$this->ProcessModelList($response,true,true)->ReturnData();
	}
	
	/**
	 * Создание движения по указанному отделению
	 * @return bool
	 */
	function saveEvnSectionInHosp()
	{
		$data = $this->ProcessInputData('saveEvnSectionInHosp', true);
		if (false == $data) { return false; }
		$this->load->model('EvnPS_model');
		$response = $this->EvnPS_model->saveEvnSectionInHosp(array(
			'scenario' => swModel::SCENARIO_SET_ATTRIBUTE,
			'session' => $data['session'],
			'EvnPS_id' => $data['EvnSection_pid'],
			'Person_id' => $data['Person_id'],
			'EvnPS_OutcomeDate' => $data['EvnSection_setDate'],
			'EvnPS_OutcomeTime' => $data['EvnSection_setTime'],
			'vizit_direction_control_check' => $data['vizit_direction_control_check'],
			'LpuSectionWard_id' => $data['LpuSectionWard_id'],
			'LpuSection_eid' => $data['LpuSection_id']
		));
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Перевод в др.отделение
	 * @return bool
	 */
	function saveHospInOtherLpuSection()
	{
		$data = $this->ProcessInputData('saveHospInOtherLpuSection', true);
		if ($data == false) { return false; }
		try {
			$this->load->model('Common_model', 'commodel');
			$leave_type_id = $this->commodel->getLeaveTypeBySysNick([
				'LeaveType_SysNick' => 'section'
			]);
			if (empty($leave_type_id)) {
				throw new Exception('Не удалось получить значение типа исхода');
			}
			$current_dt = $this->dbmodel->currentDT;
			$this->dbmodel->beginTransaction();
			// выписка из текущего отделения
			$response = $this->dbmodel->doSave(array(
				'scenario' => swModel::SCENARIO_DO_SAVE,
				'session' => $data['session'],
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_disDate' => $current_dt->format('Y-m-d'),
				'EvnSection_disTime' => $current_dt->format('H:i'),
				'LpuSection_oid' => $data['LpuSection_id'],
				'LeaveType_id' => $leave_type_id,
				'ResultDesease_id' => 2, // Переведен в др. отделение
				'EvnLeave_UKL' => 1,
			), false);
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg']);
			}
			// создание нового движения
			$this->load->model('EvnSection_model', 'EvnSection');
			$response = $this->EvnSection->doSave(array(
				'scenario' => swModel::SCENARIO_AUTO_CREATE,
				'session' => $data['session'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'LpuSection_id' => $data['LpuSection_id'],
				'Person_id' => $data['Person_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Server_id' => $data['Server_id'],
				'EvnSection_setDate' => $current_dt->format('Y-m-d'),
				'EvnSection_setTime' => $current_dt->format('H:i'),
			), false);
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg']);
			}
			$this->dbmodel->commitTransaction();
			$this->ProcessModelSave($response, true);
		} catch (Exception $e) {
			$this->dbmodel->rollbackTransaction();
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			);
		}
		$this->ReturnData();
		return true;
	}
	
	/**
	 * Отмена госпитализации из АРМа приемного отделения
	 * @return bool
	 */
	function deleteEvnSectionInHosp() {
		//$response = array();
		$data = $this->ProcessInputData('deleteEvnSectionInHosp', true);
		if($data) {
			$this->load->model('EvnPS_model', 'EvnPS_model');
			$response = $this->EvnPS_model->deleteEvnSectionInHosp($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		}
		else {
			$this->ReturnError();
			return false;
		}
	}

	/**
	 *  Удаление исхода госпитализации в профильное отделение
	 *  Используется: ЭМК, журнал выбывших
	 */
	function deleteLeave() {
		$data = $this->ProcessInputData('loadEvnSectionEditForm', true);
		if ( $data === false ) { return false; }
		// Проверка есть ли в реестрах записи об этом случае
		if ( in_array($this->dbmodel->getRegionNick(), array('buryatiya')) ) {
			// Цепляем реестровую БД
			$dbConnection = getRegistryChecksDBConnection();
			if ( $dbConnection != 'default' ) {
				$this->db = null;
				$this->load->database($dbConnection);
			}
			$this->load->model('Registry_model', 'Reg_model');
			$registryData = $this->Reg_model->checkEvnInRegistry($data, 'delete');
			if ( is_array($registryData) ) {
				$this->ProcessModelSave($registryData, true)->ReturnData();
				return false;
			}
			// Цепляем рабочую БД
			if ( $dbConnection != 'default' ) {
				$this->db = null;
				$this->load->database();
			}
		}
		// имитация функционала, который сейчас реализован при очистке исхода госпитализации на форме редактирования движения.
		$response = $this->dbmodel->doSave(array(
			'scenario' => swModel::SCENARIO_DO_SAVE,
			'session' => $data['session'],
			'EvnSection_id' => $data['EvnSection_id'],
			'LeaveType_id' => NULL,
			'EvnSection_disDate' => NULL,
			'EvnSection_disTime' => NULL,
		));
		$this->ProcessModelSave($response, true, 'Ошибка при удалении исхода');
		$this->ReturnData();
		return true;
	}

	/**
	 * Функция для проставления указанной палаты
	 */
	function setEvnSectionWard()
	{
		$data = $this->ProcessInputData('setEvnSectionWard', true);
		if ($data === false) { return false; }
		try {
			// палата записывается или в EvnSection или в EvnPS
			if (isset($data['EvnPS_id'])) {
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$this->EvnPS_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->EvnPS_model->setParams($data);
				$response = $this->EvnPS_model->updateLpuSectionWardId($data['EvnPS_id'], $data['LpuSectionWard_id']);
			} else if (isset($data['EvnSection_id'])) {
				$this->dbmodel->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->dbmodel->setParams($data);
				$response = $this->dbmodel->updateLpuSectionWardId($data['EvnSection_id'], $data['LpuSectionWard_id'], $data['LpuSectionWardCur_id']);
			} else {
				throw new Exception('Не указано или движение или карта выбывшего из стационара!', 400);
			}
			$this->ProcessModelSave($response, true, 'При записи палаты возникли ошибки');
			$this->ReturnData();
			return true;
		} catch (Exception $e) {
			$this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			));
			return false;
		}
	}


	/**
	 * Функция получения историии изменения палат
	 */
	function loadLpuSectionWardHistory()
	{
		$data = $this->ProcessInputData('loadLpuSectionWardHistory', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loadLpuSectionWardHistory($data['EvnSection_id']);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Функция получения историии изменения палат
	 */
	function loadLpuSectionBedProfileHistory()
	{
		$data = $this->ProcessInputData('loadLpuSectionBedProfileHistory', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loadLpuSectionBedProfileHistory($data['EvnSection_id']);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}	
	/**
	 * Функция для изменения врача в движении на указанного
	 */
	function setEvnSectionMedPersonal() {
		$data = $this->ProcessInputData('setEvnSectionMedPersonal', true);
		if ($data === false) { return false; }
		try {
			// врач записывается или в EvnSection или в EvnPS
			if (!empty($data['EvnSection_pid']) && $data['EvnSection_id'] == $data['EvnSection_pid']) {
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$this->EvnPS_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->EvnPS_model->setParams($data);
				$response = $this->EvnPS_model->updateMedPersonalPid($data['EvnSection_pid'], $data['MedPersonal_id']);
				$response = $this->EvnPS_model->updateMedStaffFactPid($data['EvnSection_pid'], $data['MedStaffFact_id']);
			} else if (isset($data['EvnSection_id'])) {
				$this->dbmodel->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->dbmodel->setParams($data);
				$response = $this->dbmodel->updateMedPersonalId($data['EvnSection_id'], $data['MedPersonal_id']);
				$response = $this->dbmodel->updateMedStaffFactId($data['EvnSection_id'], $data['MedStaffFact_id']);
			} else {
				throw new Exception('Не указано или движение или карта выбывшего из стационара!', 400);
			}
			$this->ProcessModelSave($response, true, 'При записи врача возникли ошибки');
			$this->ReturnData();
			return true;
		} catch (Exception $e) {
			$this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			));
			return false;
		}
	}

	/**
	 * Функция для изменения Профиля на указанный
	 */
	function setLpuSectionProfile() {
		$data = $this->ProcessInputData('setLpuSectionProfile', true);
		if ($data === false) { return false; }
		try {
			// врач записывается или в EvnSection или в EvnPS
			if (!empty($data['EvnSection_pid']) && $data['EvnSection_id'] == $data['EvnSection_pid']) {
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$this->EvnPS_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->EvnPS_model->setParams($data);
				$response = $this->EvnPS_model->updateLpuSectionProfileId($data['EvnSection_pid'], $data['LpuSectionProfile_id']);
			} else if (isset($data['EvnSection_id'])) {
				$this->dbmodel->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->dbmodel->setParams($data);
				$response = $this->dbmodel->updateLpuSectionProfileId($data['EvnSection_id'], $data['LpuSectionProfile_id']);
			} else {
				throw new Exception('Не указано или движение или карта выбывшего из стационара!', 400);
			}
			$this->ProcessModelSave($response, true, 'При записи профиля возникли ошибки');
			$this->ReturnData();
			return true;
		} catch (Exception $e) {
			$this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			));
			return false;
		}
	}
	/**
	 * Функция для изменения Профиля койки на указанный
	 */
	function setLpuSectionBedProfile() {
		$data = $this->ProcessInputData('setLpuSectionBedProfile', true);
		if ($data === false) { return false; }
		try {
			// Профиль коек записывается или в EvnSection или в EvnPS
			if (!empty($data['EvnSection_pid']) && $data['EvnSection_id'] == $data['EvnSection_pid']) {
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$this->EvnPS_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->EvnPS_model->setParams($data);
				$response = $this->EvnPS_model->updateLpuSectionBedProfileId($data['EvnSection_pid'], $data['LpuSectionBedProfile_id']);
			} else if (isset($data['EvnSection_id'])) {
				$this->dbmodel->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->dbmodel->setParams($data);
				$response = $this->dbmodel->updateLpuSectionBedProfileId($data['EvnSection_id'], $data['LpuSectionBedProfile_id']);
				$response = $this->dbmodel->updateLpuSectionBedProfileLinkfedId($data['EvnSection_id'], $data['LpuSectionBedProfileLink_fedid']);
			} else {
				throw new Exception('Не указано или движение или карта выбывшего из стационара!', 400);
			}
			$this->ProcessModelSave($response, true, 'При записи профиля возникли ошибки');
			$this->ReturnData();
			return true;
		} catch (Exception $e) {
			$this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			));
			return false;
		}
	}

	/**
	 *
	 * @return type 
	 */
	function getCSDuration(){
		$data = $this->ProcessInputData('getCSDuration', true);
		if($data) {
			$response = $this->dbmodel->getCSDuration($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			$this->ReturnError();
			return false;
		}
	}
	
	/**
	 * Функция для получения данных о последнем движении 
	 * На выходе: JSON-строка
	 * Используется: рабочее место врача приемного отделения
	 * @return bool
	 */
	function getEvnSectionLast() {
		$data = $this->ProcessInputData('getEvnSectionLast', true);
		if($data) {
			$response = $this->dbmodel->getEvnSectionLast($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			$this->ReturnError();
			return false;
		}
	}

	/**
	 * Удаление специфики беременности
	 */
	function deleteBirthSpecStac() {
		$data = $this->ProcessInputData('deleteBirthSpecStac', true, true);
		if ($data) {
			$BirthSpecStac_id = $data['BirthSpecStac_id'];
			$pmUser_id = $data['pmUser_id'];
			$this->load->model('BirthSpecStac_model', 'BirthSpecStac_model');
			$response = $this->BirthSpecStac_model->Del($BirthSpecStac_id, $pmUser_id);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение профилей койки по отделению
	 */
	function getLpuSectionBedProfilesByLpuSection(){
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getLpuSectionBedProfilesByLpuSection', true);
		if ( $data === false ) { return false; }
		if ( !isset($data['LpuSection_id']) ) {
			echo json_return_errors('Неверные параметры');
			return false;
		}
		$response = $this->dbmodel->getLpuSectionBedProfilesByLpuSection($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return false;
	}

	/**
	 * Получение профилей койки по выбранному профилю
	 */
	function getLpuSectionBedProfilesByLpuSectionProfile(){
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getLpuSectionBedProfilesByLpuSectionProfile', true);
		if ( $data === false ) { return false; }
		if ( !isset($data['LpuSectionProfile_id']) ) {
			echo json_return_errors('Неверные параметры');
			return false;
		}
		$response = $this->dbmodel->getLpuSectionBedProfilesByLpuSectionProfile($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return false;
	}
	
	/**
	 * Получение профилей койки по профилям отделения (по основному и дополнительным) через стыковочную таблицу «Профиль отделения – Профиль койки». 
	 */
	function getLpuSectionBedProfileLink(){
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getLpuSectionBedProfileLink', true);
		if ( $data === false ) { return false; }
		if ( !isset($data['LpuSection_id']) ) {
			echo json_return_errors('Неверные параметры');
			return false;
		}
		$response = $this->dbmodel->getLpuSectionBedProfileLink($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return false;
	}
	
	/**
	 * Получение профилей койки добавленных в коечном фонде . 
	 */
	function getLpuSectionBedProfilesLinkByLpuSection(){
		$data = $this->ProcessInputData('getLpuSectionBedProfilesLinkByLpuSection', true);
		if ( $data === false ) { return false; }
		if ( !isset($data['LpuSection_id']) ) {
			echo json_return_errors('Неверные параметры');
			return false;
		}
		$response = $this->dbmodel->getLpuSectionBedProfilesLinkByLpuSection($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return false;
	}

	/**
	 * Получение всех пациентов находящихся в стационарном отделении
	 */
	function getLpuSectionPatientList(){
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getLpuSectionPatientList', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->getLpuSectionPatientList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return false;
	}

	/**
	 * Сохранение КСГ для оплаты
	 */
	function saveEvnSectionKSGPaid() {
		$data = $this->ProcessInputData('saveEvnSectionKSGPaid',true);
		if ($data === false)return false;

		$response = $this->dbmodel->saveEvnSectionKSGPaid($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка формы редактирования КСГ
	 */
	function loadEvnSectionKSGEditForm() {
		$data = $this->ProcessInputData('loadEvnSectionKSGEditForm',true);
		if ($data === false)return false;

		$response = $this->dbmodel->loadEvnSectionKSGEditForm($data);
		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение периода КСГ
	 */
	function saveEvnSectionKSG() {
		$data = $this->ProcessInputData('saveEvnSectionKSG',true);
		if ($data === false)return false;

		$response = $this->dbmodel->saveEvnSectionKSG($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка КСГ для движения
	 */
	function loadEvnSectionKSGList() {
		$data = $this->ProcessInputData('loadEvnSectionKSGList',true);
		if ($data === false)return false;

		$response = $this->dbmodel->loadEvnSectionKSGList($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение последнего движения в стационаре в профильном отделении
	 */
	function getLastEvnSection() {
		$data = $this->ProcessInputData('getLastEvnSection',true);
		if ($data === false)return false;

		$response = $this->dbmodel->getLastEvnSection($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		return true;
	}

	/**
	 * Проверка наличия услуг по ЭКО
	 */
	function checkIsEco() {
		$data = $this->ProcessInputData('checkIsEco',true);
		if ($data === false)return false;

		$response = $this->dbmodel->checkIsEco($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки наличия услуг по ЭКО')->ReturnData();
		return true;
	}

	/**
	 * Проверка даты закрытия организации перевода
	 */
	function checkEvnSectionOutcomeOrgDate() {
		$data = $this->ProcessInputData('checkEvnSectionOutcomeOrgDate',true);
		if ($data === false)return false;

		$response = $this->dbmodel->checkEvnSectionOutcomeOrgDate($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение только диагноза
	 */
	function setEvnSectionDiag() {
		$data = $this->ProcessInputData('setEvnSectionDiag',true);
		if ($data === false)return false;

		$response = $this->dbmodel->setEvnSectionDiag($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения диагноза')->ReturnData();
		return true;
	}

	/**
	 * Сохранение схем лекарственной терапии
	 */
	public function saveDrugTherapyScheme() {
		$data = $this->ProcessInputData('saveDrugTherapyScheme',true);
		if ($data === false)return false;

		$response = $this->dbmodel->saveDrugTherapyScheme($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения схем лекарственной терапии')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список схем лечения
	 */
	function loadDrugTherapySchemeList()
	{
		$data = $this->ProcessInputData('loadDrugTherapySchemeList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugTherapySchemeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Список палат
	 */
	function getRoomList() {
		$data = $this->ProcessInputData('getRoomList', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->getRoomList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Список профилей коек
	 */
	function getBedList() {
		$data = $this->ProcessInputData('getBedList', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->getBedList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение факта переливания крови
	 */
	function saveTransfusionFact() {
		$data = $this->ProcessInputData('saveTransfusionFact', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->saveTransfusionFact($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}

	/**
	 * Получения списка фактов переливания крови и осложнений по ним
	 */
	function loadTransfusionFactList() {
		$data = $this->ProcessInputData('loadTransfusionFactList', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->loadTransfusionFactList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение факта переливания крови и осложнений по ним
	 */
	function loadTransfusionFact() {
		$data = $this->ProcessInputData('loadTransfusionFact', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->loadTransfusionFact($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление факта переливания крови
	 */
	function deleteTransfusionFact() {
		$data = $this->ProcessInputData('deleteTransfusionFact', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->deleteTransfusionFact($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}

	/**
	 * Удаление осложнения перелевиния крови
	 */
	function deleteTransfusionCompl() {
		$data = $this->ProcessInputData('deleteTransfusionCompl', true);
		if ( $data === false )  return;
		if (empty($data['TransfusionCompl_id'])) {
			$this->ProcessModelSave(['success'=>true], true, true)->ReturnData();
		} else {
			$response = $this->dbmodel->deleteTransfusionCompl($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		}
	}

	/**
	 * Список скрининговых исследований
	 */
	function loadScreenList() {
		$data = $this->ProcessInputData('loadScreenList', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->loadScreenList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * Получить планируемую дату выписки
	 */
	function getAverageDateStatement() {
		$data = $this->ProcessInputData('getAverageDateStatement', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->getAverageDateStatement($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*  Получение истории смены врачей
	*  Входящие данные: $_POST['EvnSection_id']']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadDoctorHistoryList() {
		$data = $this->ProcessInputData('loadScreenList', true);
		$response=[];
		if (in_array(getRegionNick(), array('vologda'))) {
			//история изменения врачей, только Вологда #192334
			$this->load->model('Evn_model', 'Evn_model');
			$response=$this->Evn_model->getDoctorHistoryWrapper(["EvnDoctor_pid"=>$data['EvnSection_id']]);
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

}
