<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * EvnPrescr - контроллер для работы с назначениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Stas Bykov aka Savage (savage@swan.perm.ru)
 * @version      09.2013
 *
 * @property EvnPrescr_model $dbmodel
 * @property EvnPrescrList_model $EvnPrescrList_model
 * @property EvnPrescrProc_model $EvnPrescrProc_model
 * @property EvnPrescrOper_model $EvnPrescrOper_model
 * @property EvnPrescrLabDiag_model $EvnPrescrLabDiag_model
 * @property EvnPrescrFuncDiag_model $EvnPrescrFuncDiag_model
 * @property EvnPrescrConsUsluga_model $EvnPrescrConsUsluga_model
 * @property EvnPrescrTreat_model $EvnPrescrTreat_model
 * @property EvnPrescrRegime_model $EvnPrescrRegime_model
 * @property EvnPrescrDiet_model $EvnPrescrDiet_model
 * @property EvnPrescrObserv_model $EvnPrescrObserv_model
 * @property CureStandart_model $CureStandart_model
 * @property EvnPrescrVaccination_model $EvnPrescrVaccination_model
 */
class EvnPrescr extends swController {

	public $inputRules = array(
		'loadEvnPrescrConsJournal' => array(
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'ЛПУ', 'rules' => 'required', 'type' => 'id')
		),
		'saveCureStandartForm' => array(
			array('field' => 'save_data', 'label' => 'Выделенные назначения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int')
		),
		'printCureStandart' => array(
			array('field' => 'CureStandart_id', 'label' => 'Идентификатор МЭС', 'rules' => 'required', 'type' => 'id')
		),
		'loadCureStandartForm' => array(
			array('field' => 'CureStandart_id', 'label' => 'Идентификатор МЭС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'newWndExt6', 'label' => 'Новое окно', 'rules' => '', 'type' => 'string'),
			array('field' => 'objectPrescribe', 'label' => 'Вид назначения', 'rules' => '', 'type' => 'string'),
		),
		'cancelEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			//array('field' => 'EvnPrescr_setDate','label' => 'Дата назначения', 'rules' => 'trim', 'type' =>  'date'),
			//array('field' => 'EvnPrescr_rangeDate','label' => 'Интервал дат назначения', 'rules' => 'trim', 'type' =>  'daterange'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_IsExec','label' => 'Признак выполнения','rules' => '','type' => 'id'),
			array('field' => 'EvnStatus_id','label' => 'Идентификатор статуса направления','rules' => '','type' => 'id'),
			array('field' => 'DirType_id','label' => 'Идентификатор типа направления','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Идентификатор удаляемой услуги','rules' => '','type' => 'id'),
			array('field' => 'couple','label' => 'Несколько исследований в направлении','rules' => '','type' => 'boolean')
		),
		'deleteFromDirection' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => '','type' => 'id'),
			array('field' => 'EvnPrescr_IsExec','label' => 'Признак выполнения','rules' => '','type' => 'id'),
			array('field' => 'EvnStatus_id','label' => 'Идентификатор статуса направления','rules' => '','type' => 'id'),
			array('field' => 'DirType_id','label' => 'Идентификатор типа направления','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Идентификатор удаляемой услуги','rules' => '','type' => 'id'),
			array('field' => 'couple','label' => 'Несколько исследований в направлении','rules' => '','type' => 'boolean')
		),
		'cancelEvnCourse' => array(
			array('field' => 'EvnCourse_id', 'label' => 'Идентификатор курса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id')
		),
		'execEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Timetable_id', 'label' => 'Идентификатор бирки графика', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор сотрудника, выполнившего назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор отделения сотрудника, выполнившего назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы сотрудника, выполнившего назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnPrescrEvnDirectionCombo' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
		),
		'directEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
		),
		'loadEvnObservDataViewGrid' => array(
			array('field' => 'EvnObserv_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnPrescrObservPosList' => array(
			array('field' => 'EvnObserv_pid', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnObserv_setDate', 'label' => 'Дата проведения наблюдений', 'rules' => 'trim|required', 'type' => 'date'),
		//array('field' => 'ObservTimeType_id','label' => 'Время проведения наблюдений', 'rules' => 'required', 'type' =>  'id'),
		),
		'loadEvnPrescrCompletedJournalGrid' => array(
			array('field' => 'EvnPrescr_setDate_Range', 'label' => 'Период', 'rules' => 'trim', 'type' => 'daterange'),
			array('field' => 'Person_Birthday', 'label' => 'Дата рождения', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Person_Firname', 'label' => 'Имя', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Secname', 'label' => 'Отчество', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Surname', 'label' => 'Фамилия', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => '', 'type' => 'id')
		),
		'loadEvnPrescrJournalGrid' => array(
			array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_BirthDay', 'label' => 'Д/Р', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionIntroType_id', 'label' => 'Способ применения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionWard_id', 'label' => 'Палата', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_setDate_Range', 'label' => 'Период', 'rules' => 'trim|required', 'type' => 'daterange'),
			array('field' => 'EvnPrescr_IsExec', 'label' => 'Признак выполнения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionStatusType_id', 'label' => 'Статус назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Usluga_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_insDT', 'label' => 'Дата формирования назначения', 'rules' => '', 'type' => 'daterange'),
			array('field' => 'EvnQueueShow_id','label' => 'Очередь','rules' => '','type' => 'int'),
			array('field' => 'isClose','label' => 'Закончен','rules' => '','type' => 'int'),
			// Параметры страничного вывода
			array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
		),
		'loadEvnPrescrProcCmpJournalGrid' => array(
			array('field' => 'EvnUslugaCommon_setDate_Range', 'label' => 'Период выполнения', 'rules' => 'trim', 'type' => 'daterange'),
			array('field' => 'Person_Birthday', 'label' => 'Дата рождения', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Person_Firname', 'label' => 'Имя', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Secname', 'label' => 'Отчество', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Surname', 'label' => 'Фамилия', 'rules' => 'ban_percent|trim', 'type' => 'string')
		),
		'loadEvnPrescrProcData' => array(
			array('field' => 'EvnPrescrProc_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id')
		),
		'rollbackEvnPrescrExecution' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id')
		),
		'saveEvnObserv' => array(
			array('field' => 'evnObservDataList', 'label' => 'Список измеряемых/наблюдаемых', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'EvnObserv_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnObserv_setDate', 'label' => 'Дата наблюдения', 'rules' => 'trim|required', 'type' => 'date'),
			//array('field' => 'ObservTimeType_id','label' => 'Время проведения наблюдений', 'rules' => 'required', 'type' =>  'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'int')
		),
		'saveEvnPrescrUnExecReason' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescrFailureType_id', 'label' => 'Идентификатор причины невыполнения назначения', 'rules' => '', 'type' => 'id')
		),
		'saveEvnPrescrDrugStream' => array(
			array('field' => 'evnDrugList', 'label' => 'Список медикаментов', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'EvnPrescrTreat_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор сотрудника, выполнившего назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор отделения сотрудника, выполнившего назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы сотрудника, выполнившего назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrTreatTimetable_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id')
		),
		'signEvnPrescrAll' => array(
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id')
		),
		'signEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_setDate', 'label' => 'Дата назначения', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescr_rangeDate', 'label' => 'Интервал дат назначения', 'rules' => 'trim', 'type' => 'daterange'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'unsign', 'label' => 'Отмена подписи', 'rules' => '', 'type' => 'string')
		),
		'saveEvnPrescrListAsXTemplate' => array(
			array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_begDate', 'label' => 'Дата события', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonAgeGroup_id', 'label' => 'Возрастная группа', 'rules' => '', 'type' => 'id')
		),
		'getEvnPrescrList' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_begDate', 'label' => 'Дата события', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonAgeGroup_id', 'label' => 'Возрастная группа', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'checkTemplateOnExist', 'label' => 'проверка на наличие шаблона', // 1 - yes
				'rules' => '', 'type' => 'int')
		),
		'EvnPrescrSignAll' => array(
			array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnUslugaData' => array(
			array('field' => 'Evn_id', 'label' => '', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => '', 'rules' => 'required', 'type' => 'id')
		),
		'checkDoubleUsluga' => array(
			array('field' => 'EvnPrescr_pid', 'label' => 'Посещение поликлиники/движение в отделении стационара', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Место оказания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'checkRecordQueue', 'label' => 'Проверка постановки в очередь', 'rules' => '', 'type' => 'checkbox'),
		),
		'loadEvnObservGraphsData' => array(
			array(
				'field' => 'EvnObserv_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnObservGraphs' => array(
			array(
				'field' => 'EvnObserv_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
        'getDrugPackData' => array(
            array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugComplexMnn_id', 'label' => 'Комплексное МНН', 'rules' => '', 'type' => 'id')
        ),
		'setCitoEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_IsCito', 'label' => 'Cito', 'rules' => 'required', 'type' => 'int')
		),
		'loadPrescrPerformanceList' => array(
			array('field' => 'PeriodRange', 'label' => 'Отчетный период', 'rules' => 'trim', 'type' => 'daterange'),
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescrPerform_FIO', 'label' => 'ФИО пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'PrescrPerform_DrugNameNazn', 'label' => 'Наименование ЛС: назначено', 'rules' => '', 'type' => 'string'),
			array('field' => 'PrescrPerform_DrugCodeNazn', 'label' => 'Код ЛС: назначено', 'rules' => '', 'type' => 'string'),		
			array('field' => 'PrescrPerform_DrugNameIspoln', 'label' => 'Наименование ЛС: выполнено', 'rules' => '', 'type' => 'string'),
			array('field' => 'PrescrPerform_DrugCodeIspoln', 'label' => 'Код ЛС: выполнено', 'rules' => '', 'type' => 'string'),
			array('field' => 'PrescrPerform_IspolnCombo', 'label' => 'Выполнение', 'rules' => '', 'type' => 'int'),
			array('field' => 'PrescrPerform_Differences', 'label' => 'Расхождения в количестве', 'rules' => '', 'type' => 'int'),		
			
			array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int')
		),
		'loadLpuSectionCombo' => array(
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'UserLpuSection_id', 'label' => 'Отделение пользователя', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
		),
		'loadStorageCombo' => array(
			array('field' => 'Storage_id', 'label' => 'Склад', 'rules' => '', 'type' => 'id'),
			array('field' => 'UserLpuSection_id', 'label' => 'Отделение пользователя', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
		),
		'loadEvnVizitWithPrescrList' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'top', 'label' => 'количество записей', 'rules' => '', 'type' => 'int')
		),
		'printLabDirections' => array(
			array('field' => 'Evn_id', 'label' => 'Evn_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id')
		),
		'saveTreatmentStandardsForm' => array(
			array('field' => 'save_data', 'label' => 'Выделенные назначения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int')
		),
		'checkPersonPrescrTreat' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPrescr_model', 'dbmodel');
	}

	/**
	 *  Получение данных направления, связанного с назначением
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: комбобокс "Запись" и формы, в которых он используется
	 */
	function loadEvnPrescrEvnDirectionCombo() {
		$data = $this->ProcessInputData('loadEvnPrescrEvnDirectionCombo', true, true);
		if ($data == false) {
			return false;
		}
		$response = $this->dbmodel->loadEvnPrescrEvnDirectionCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение списка направлений, созданных на основе назначения с типом «Консультация»
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: журнал консультаций
	 */
	function loadEvnPrescrConsJournal() {
		$data = $this->ProcessInputData('loadEvnPrescrConsJournal', true, true);

		if (is_array($data) && count($data) > 0) {
			$response = $this->dbmodel->loadEvnPrescrConsJournal($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет назначения выделенные в шаблоне
	 * @return bool
	 */
	function saveCureStandartForm() {
		$data = $this->ProcessInputData('saveCureStandartForm', true);
		if ($data === false) {
			return false;
		}
		$save_data = json_decode($data['save_data'], true);
		$evn = $this->dbmodel->getEvnData([
			'id' => $data['Evn_pid'],
		]);
		if (!isset($evn['Evn_setDT']) || !($evn['Evn_setDT'] instanceof DateTime)) {
			$this->ReturnData(['success' => false, 'Error_Msg' => 'Не удалось получить дату учетного документа']);
			return false;
		}
		$default_set_date = $evn['Evn_setDT']->format('Y-m-d');

		if (!empty($save_data['oper']) && is_array($save_data['oper']) && count($save_data['oper']) > 0) {
			$tmp_data = $data;
			$tmp_data['EvnPrescrOper_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrOper_id'] = NULL;
			$tmp_data['EvnPrescrOper_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrOper_IsCito'] = NULL;
			$tmp_data['EvnPrescrOper_Descr'] = NULL;
			$tmp_data['MedService_id'] = NULL;
			$this->load->model('EvnPrescrOper_model', 'EvnPrescrOper_model');
			foreach ($save_data['oper'] as $id) {
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['EvnPrescrOper_uslugaList'] = $id;
					$response = $this->EvnPrescrOper_model->doSave($tmp_data);
					$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения');
					if (!empty($this->OutData['Error_Msg'])) {
						$this->ReturnData();
						return false;
					}
				}
			}
		}

		if (!empty($save_data['proc']) && is_array($save_data['proc']) && count($save_data['proc']) > 0) {
			$tmp_data = $data;
			$tmp_data['EvnCourseProc_pid'] = $data['Evn_pid'];
			$tmp_data['EvnCourseProc_id'] = NULL;
			$tmp_data['EvnCourseProc_setDate'] = $default_set_date;
			$tmp_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$tmp_data['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			$tmp_data['Morbus_id'] = NULL;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrProc_IsCito'] = NULL;
			$tmp_data['EvnPrescrProc_Descr'] = NULL;
			$tmp_data['MedService_id'] = NULL;
			$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
			foreach ($save_data['proc'] as $id) {
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['UslugaComplex_id'] = $id;
					$response = $this->EvnPrescrProc_model->doSaveEvnCourseProc($tmp_data);
					$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения');
					if (!empty($this->OutData['Error_Msg'])) {
						$this->ReturnData();
						return false;
					}
				}
			}
		}

		if (!empty($save_data['funcdiag']) && is_array($save_data['funcdiag']) && count($save_data['funcdiag']) > 0) {
			$tmp_data = $data;
			$tmp_data['EvnPrescrFuncDiag_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrFuncDiag_id'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_IsCito'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			$tmp_data['MedService_id'] = NULL;
			$this->load->model('EvnPrescrFuncDiag_model', 'EvnPrescrFuncDiag_model');
			foreach ($save_data['funcdiag'] as $id) {
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['EvnPrescrFuncDiag_uslugaList'] = $id;
					$response = $this->EvnPrescrFuncDiag_model->doSave($tmp_data);
					$this->ProcessModelSave($response, true, 'Ошибка при сохранении курса');
					if (!empty($this->OutData['Error_Msg'])) {
						$this->ReturnData();
						return false;
					}
				}
			}
		}

		if (!empty($save_data['drug']) && is_array($save_data['drug']) && count($save_data['drug']) > 0) {
			$tmp_data = $data;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnCourseTreat_id'] = NULL;
			$tmp_data['EvnCourseTreat_pid'] = $data['Evn_pid'];
			$tmp_data['EvnCourseTreat_setDate'] = $default_set_date;
			$tmp_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$tmp_data['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			$tmp_data['Morbus_id'] = NULL;
			$tmp_data['PrescriptionIntroType_id'] = NULL;
			$tmp_data['PrescriptionTreatType_id'] = NULL;
			$tmp_data['PerformanceType_id'] = NULL;
			$tmp_data['EvnPrescrTreat_IsCito'] = NULL;
			$tmp_data['EvnPrescrTreat_Descr'] = NULL;

			$DrugData = array();
			$DrugData['id'] = NULL;
			$DrugData['MethodInputDrug_id'] = 1;
			$DrugData['DrugComplexMnn_id'] = NULL;
			$DrugData['Drug_id'] = NULL;
			$DrugData['KolvoEd'] = NULL;
			$DrugData['Kolvo'] = NULL;
			$DrugData['CUBICUNITS_id'] = NULL;
			$DrugData['MASSUNITS_id'] = NULL;
			$DrugData['ACTUNITS_id'] = NULL;
			$DrugData['DoseDay'] = NULL;
			$DrugData['PrescrDose'] = NULL;
			$DrugData['status'] = 'new';

			$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
			foreach ($save_data['drug'] as $actmatters_id) {
				if (!empty($actmatters_id) && is_numeric($actmatters_id)) {
					$DrugData['actmatters_id'] = $actmatters_id;
					$tmp_data['DrugListData'] = array($DrugData);
					ConvertFromWin1251ToUTF8($tmp_data['DrugListData']);
					$tmp_data['DrugListData'] = json_encode($tmp_data['DrugListData']);
					$response = $this->EvnPrescrTreat_model->doSaveEvnCourseTreat($tmp_data);

					$this->ProcessModelSave($response, true, 'Ошибка при сохранении курса');
					if (!empty($this->OutData['Error_Msg'])) {
						$this->ReturnData();
						return false;
					}
				}
			}
		}

		if (!empty($save_data['labdiag']) && is_array($save_data['labdiag']) && count($save_data['labdiag']) > 0) {
			$tmp_data = $data;
			$tmp_data['EvnPrescrLabDiag_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrLabDiag_id'] = NULL;
			$tmp_data['EvnPrescrLabDiag_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrLabDiag_IsCito'] = NULL;
			$tmp_data['EvnPrescrLabDiag_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			$tmp_data['MedService_id'] = NULL;
			$this->load->model('EvnPrescrLabDiag_model', 'EvnPrescrLabDiag_model');
			foreach ($save_data['labdiag'] as $uslugacomplex_pid => $uslugaList) {
				$tmp_data['UslugaComplex_id'] = $uslugacomplex_pid;
				if (is_array($uslugaList) && count($uslugaList) > 0) {
					$tmp_data['EvnPrescrLabDiag_uslugaList'] = implode(',', $uslugaList);
					$response = $this->EvnPrescrLabDiag_model->doSave($tmp_data);
					$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения');
					if (!empty($this->OutData['Error_Msg'])) {
						$this->ReturnData();
						return false;
					}
				}
			}
		}
		$this->ReturnData(array('success' => true, 'Error_Msg' => null));
		return true;
	}

	/**
	 * Просмотр стандарта лечения
	 * @return bool
	 */
	function printCureStandart(){
		$data = $this->ProcessInputData('printCureStandart', true);
		if ($data === false) {
			return false;
		}
		return $this->getCureStandartForm($data, true);
	}

	/**
	 * Возвращает шаблон МЭС для создания назначений по этому шаблону
	 * @return bool
	 */
	function loadCureStandartForm() {
		$data = $this->ProcessInputData('loadCureStandartForm', true);
		if ($data === false) {
			return false;
		}
		return $this->getCureStandartForm($data, false);
	}

	/**
	 * Возвращает шаблон МЭС для создания назначений по этому шаблону
	 * @return bool
	 */
	private function getCureStandartForm($data, $isPrint) {
		$this->load->model('CureStandart_model');
		if ($isPrint) {
			$data['scenario'] = CureStandart_model::SCENARIO_LOAD_PRINT_DATA;
		} else {
			$data['scenario'] = CureStandart_model::SCENARIO_LOAD_PRESCRIPTION_DATA;
		}
		try {
			$this->CureStandart_model->applyData($data);
			$response = $this->CureStandart_model->getPrintData();
			if(isset($data['newWndExt6'])) {
				$dataPrescr = array();
				switch($data['objectPrescribe']){
					case 'DrugData':{
						$dataPrescr = $response['tplData']['DrugData'];
						break;
					}
					case 'LabDiagData':{
						foreach($response['tplData']['LabDiagData'] as $labDiagPrescribe)
							$dataPrescr[] = $labDiagPrescribe;
						break;
					}
					case 'FuncDiagData':{
						foreach($response['tplData']['FuncDiagData'] as $funcDiagPrescribe)
							$dataPrescr[] = $funcDiagPrescribe;
						break;
					}
				}
				$this->ReturnData(array(
					'Error_Msg' => null,
					'data' => $dataPrescr,
				));
				return true;
			}

			$this->load->library('parser');
			$html = $this->parser->parse('print_cure_standart', $response['tplData'], true);
			if ($isPrint) {
				echo $html;
			} else {
				$this->ReturnData(array(
					'Error_Msg' => null,
					'checkboxes' => $response['checkboxes'],
					'html' => toUTF($html)
				));
			}
		} catch (Exception $e) {
			if ($isPrint) {
				echo $e->getMessage();
			} else {
				$this->ReturnData(array(
					'Error_Msg' => toUTF($e->getMessage()),
					'html' => null
				));
			}
		}
		return true;
	}

	/**
	 * Запрос для грида выбора лечения на основе стандарта
	 * @return bool
	 */
	function loadCureStandartList()
	{
		$this->load->model('CureStandart_model');
		$this->inputRules['loadCureStandartList'] = $this->CureStandart_model->getInputRules(CureStandart_model::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('loadCureStandartList', true);
		if (false == $data) { return false; }
		try {
			$response = $this->CureStandart_model->doLoadGrid($data);
			$this->ProcessModelList($response, true, true);
		} catch (Exception $e) {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			);
		}
		$this->ReturnData();
		return true;
	}

	/**
	 * Запрос для видоизменённого грида выбора лечения на основе стандарта
	 * @return boolean
	 */
	function loadCureStandartListForRestyledGrid()
	{
		$this->load->model('CureStandart_model');
		$this->inputRules['loadCureStandartList'] = $this->CureStandart_model->getInputRules(CureStandart_model::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('loadCureStandartList', true);
		if (false == $data) { return false; }
		try {
			$response = $this->CureStandart_model->doLoadGrid($data);
			$this->load->library('parser');
			foreach ($response as $key => $value) {
				$value['CureStandart_Name'] = wordwrap($value['CureStandart_Name'], 150, '<br>', false);
				$html = $this->parser->parse('cure_standart_select_item', $value,true);
				if(isset($data['Ext6Wnd']))
					$html = $value['CureStandart_Name'];
				$response["$key"] = array(
					'html'=>$html,
					'Row_Num'=>$value['Row_Num'],
					'CureStandart_id'=>$value['CureStandart_id'],
				);
			}
			$this->ProcessModelList($response, true, true);
		} catch (Exception $e) {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			);
		}
		$this->ReturnData();
		return true;
	}

	/**
	 *  Отмена/удаление назначений
	 *  Входящие данные: $_POST['EvnPrescr_id'], $_POST['PrescriptionType_id']
	 *  На выходе: JSON-строка
	 */
	function cancelEvnPrescr() {
		$data = $this->ProcessInputData('cancelEvnPrescr', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->cancelEvnPrescr($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отмене/удалении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Отмена назначений из курса
	 */
	function cancelEvnCourse() {
		$data = $this->ProcessInputData('cancelEvnCourse', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->cancelEvnCourse($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отмене назначений из курса')->ReturnData();
		return true;
	}

	/**
	 *  Отмена назначений из курса
	 */
	function directEvnPrescr() {
		$data = $this->ProcessInputData('directEvnPrescr', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->directEvnPrescr($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отмене назначений из курса')->ReturnData();
		return true;
	}

	/**
	 *  Получение данных по назначению с типом "Манипуляции и процедуры"
	 *  Входящие данные: <...>
	 *  На выходе: JSON-строка
	 *  Используется: журнал назначений
	 */
	function loadEvnPrescrProcData() {
		$data = array();
		$val = array();

		$data = $this->ProcessInputData('loadEvnPrescrProcData', true, true);

		$response = $this->dbmodel->loadEvnPrescrProcData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		/* if ( is_array($response) && count($response) > 0 ) {
		  $val = $response[0];
		  $val['success'] = true;
		  array_walk($val, 'ConvertFromWin1251ToUTF8');
		  }

		  $this->ReturnData($val); */

		return true;
	}

	/**
	 *  Установка статуса "Выполнено" для назначения
	 *  Входящие данные: $_POST['EvnPrescr_id'], $_POST['Timetable_id'], $_POST['PrescriptionType_id']
	 *  На выходе: JSON-строка
	 *  Используется: журнал назначений
	 */
	function execEvnPrescr() {
		$data = $this->ProcessInputData('execEvnPrescr', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->execEvnPrescr($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Отмена статуса "Выполнено" с назначения
	 *  Входящие данные: $_POST['EvnPrescr_id'], $_POST['PrescriptionType_id']
	 *  На выходе: JSON-строка
	 *  Используется: журнал назначений
	 */
	function rollbackEvnPrescrExecution() {
		$data = $this->ProcessInputData('rollbackEvnPrescrExecution', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->rollbackEvnPrescrExecution($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отмене выполнения назначения')->ReturnData();
		return true;
	}

	/**
	 * Неведомо
	 * @return array|bool
	 */
	function getObservParamTypeList() {
		$response = $this->dbmodel->getObservParamTypeList();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка препаратов назначения ЛС для выполнения со списанием
	 * @return array|bool
	 */
	function loadEvnDrugGrid() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->inputRules['loadEvnDrugGrid'] = $this->EvnPrescrTreat_model->getInputRules('doLoadEvnDrugGrid');
		$data = $this->ProcessInputData('loadEvnDrugGrid', true, true);
		if ($data === false) {
			return false;
		}

		$response = array();
		switch($this->EvnPrescrTreat_model->options['drugcontrol']['drugcontrol_module']) {
			case 1:
				$response = $this->EvnPrescrTreat_model->doOldLoadEvnDrugGrid($data); //Старая схема учета
				break;
			case 2:
				$response = $this->EvnPrescrTreat_model->doLoadEvnDrugGrid($data);
				break;
		}
		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка результатов наблюдений
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма просмотра результатов наблюдений
	 */
	function loadEvnObservDataViewGrid() {
		$data = array();
		$data = $this->ProcessInputData('loadEvnObservDataViewGrid', true, true);
		$arr = array();
		$respr = array();
		if (is_array($data) && count($data) > 0) {
			$response = $this->dbmodel->loadEvnObservDataViewGrid($data);

			foreach ($response as $key => $array) {
				//echo"<pre>";print_r($response);echo "</pre>?exit();
				if (in_array($array['ObservParamType_id'], array(5, 6, 7, 8, 9, 10, 11))) {
					if (!in_array($array['EvnObserv_setDate'] . '-' . $array['ObservParamType_id'], $arr)) {
						$arr[] = $array['EvnObserv_setDate'] . '-' . $array['ObservParamType_id'];
						$respr[$key]['ObservTimeType_Name'] = "Суточные";
						$respr[$key]['EvnObservData_id'] = $array['EvnObservData_id'];
						$respr[$key]['ObservParamType_id'] = $array['ObservParamType_id'];
						$respr[$key]['ObservTimeType_id'] = $array['ObservTimeType_id'];
						$respr[$key]['EvnObserv_setDate'] = $array['EvnObserv_setDate'];
						$respr[$key]['ObservParamType_Name'] = $array['ObservParamType_Name'];
						if (in_array($array['ObservParamType_id'], array(9, 10, 11))) {
							$respr[$key]['EvnObservData_Value'] = ($array['EvnObservData_Value'] == 2) ? 'Да' : 'Нет';
						} else {
							$respr[$key]['EvnObservData_Value'] = $array['EvnObservData_Value'];
						}
					}
				}
				if (in_array($array['ObservParamType_id'], array(1, 2, 3, 4))) {
					$respr[$key]['EvnObservData_Value'] = $array['EvnObservData_Value'];
					$respr[$key]['EvnObservData_id'] = $array['EvnObservData_id'];
					$respr[$key]['ObservParamType_id'] = $array['ObservParamType_id'];
					$respr[$key]['ObservTimeType_id'] = $array['ObservTimeType_id'];
					$respr[$key]['EvnObserv_setDate'] = $array['EvnObserv_setDate'];
					$respr[$key]['ObservParamType_Name'] = $array['ObservParamType_Name'];
					$respr[$key]['ObservTimeType_Name'] = $array['ObservTimeType_Name'];
				}
			}

			$this->ProcessModelList($respr, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка результатов наблюдений для графиков
	 */
	public function loadEvnObservGraphsData() {
		$data = $this->ProcessInputData('loadEvnObservGraphsData', true);
		if (!$data) {
			return false;
		}

		$response = $this->dbmodel->loadEvnObservGraphsData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Печать температурного листа
	 */
	public function printEvnObservGraphs() {
		set_time_limit(60);

		$data = $this->ProcessInputData('printEvnObservGraphs', true);
		if (!$data) {
			return false;
		}
		// $data['session']['region']['nick'] = 'kz'; // for debug only

		$response = $this->dbmodel->loadEvnObservGraphsData(array(
			'EvnObserv_pid'=>$data['EvnObserv_pid'],
			'loadAll' => 1,
		));
		if (!$response) {
			return false;
		}

		$this->load->model('LpuPassport_model', 'LpuPassport_model');
		$lpu_data = $this->LpuPassport_model->getLpuPassport($data);

		$info = $this->dbmodel->loadEvnObservGraphsInfo($data);

		// Форматирование данных полученных из базы
		$graph_data = $this->dbmodel->preparePrintEvnObservGraphsData($response);

		// От количества дней зависит количество листов температурных страниц
		$total_days = sizeof($graph_data['dates']);

		$this->load->library('parser');

		$view_file = 'print_evn_observ_graphs';
		if ( $data['session']['region']['nick'] == 'kz' ) {
			$view_file .= '_kazakhstan';
		}
		
		$is_pdf = false;
		if ( isset( $_REQUEST['pdf'] ) ) {
			if ( $_REQUEST['pdf'] == 1 ) {
				$is_pdf = true;
			}
		}

		$doc = $this->parser->parse($view_file, array(
			'data' => $data,
			'graph_data' => $graph_data,
			'info' => isset($info[0]) ? $info[0] : array(),
			'total_days' => $total_days,
			'lpu' => isset($lpu_data[0]) ? $lpu_data[0] : array(),
			'TIME_TYPE_MORNING' => EvnPrescr_model::TIME_TYPE_MORNING,
			'TIME_TYPE_DAY' => EvnPrescr_model::TIME_TYPE_DAY,
			'TIME_TYPE_EVENING' => EvnPrescr_model::TIME_TYPE_EVENING,
			'PARAM_TYPE_TEMPERATURE' => EvnPrescr_model::PARAM_TYPE_TEMPERATURE,
			'PARAM_TYPE_PULSE' => EvnPrescr_model::PARAM_TYPE_PULSE,
			'PARAM_TYPE_SYSTOLIC' => EvnPrescr_model::PARAM_TYPE_SYSTOLIC,
			'PARAM_TYPE_DIASTOLIC' => EvnPrescr_model::PARAM_TYPE_DIASTOLIC,
			'is_pdf' => $is_pdf
		), true);

		if ( !$is_pdf ) {
			echo $doc;
			return true;
		}

		require_once('vendor/autoload.php');
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-L',
			'default_font_size' => 10,
			'margin_left' => 5,
			'margin_right' => 1,
			'margin_top' => 5,
			'margin_bottom' => 5,
			'margin_header' => 9,
			'margin_footer' => 9
		]);

		$mpdf->SetDisplayMode('fullpage');

		// Adding CSS support
		$stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/css/print_evn_observ_graphs.css');
		$mpdf->CSSselectMedia = 'pdf';
		$mpdf->WriteHTML($stylesheet, 1);
		$mpdf->list_indent_first_level = 1;

		// $mpdf->useOnlyCoreFonts = true;
		// Making PDF
		$mpdf->WriteHTML($doc, 2);

		$mpdf->Output('print_evn_observ_graphs.pdf', 'I');

		return true;
	}

	/**
	 *  Получение списка параметров наблюдения
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма выполнения назначения с типом "Наблюдение"
	 */
	function loadEvnPrescrObservPosList() {
		$data = $this->ProcessInputData('loadEvnPrescrObservPosList', true, true);

		if (is_array($data) && count($data) > 0) {
			$response = $this->dbmodel->loadEvnPrescrObservPosList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Консультационная услуга"
	 *  Входящие данные: $_POST['EvnPrescrConsUsluga_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Консультационная услуга"
	 */
	function loadEvnPrescrConsUslugaEditForm() {
		$this->load->model('EvnPrescrConsUsluga_model', 'EvnPrescrConsUsluga_model');
		$this->inputRules['loadEvnPrescrConsUslugaEditForm'] = $this->EvnPrescrConsUsluga_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrConsUslugaEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrConsUsluga_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Наблюдение"
	 *  Входящие данные: $_POST['EvnPrescr_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Наблюдение"
	 */
	function loadEvnPrescrObservEditForm() {
		$this->load->model('EvnPrescrObserv_model', 'EvnPrescrObserv_model');
		$this->inputRules['loadEvnPrescrObservEditForm'] = $this->EvnPrescrObserv_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrObservEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrObserv_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Наблюдение"
	 *  Входящие данные: $_POST['EvnPrescr_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Наблюдение"
	 */
	function getFreeDay() {
		$this->load->model('EvnPrescrObserv_model', 'EvnPrescrObserv_model');
		$this->inputRules['getFreeDay'] = $this->EvnPrescrObserv_model->getInputRules('getFreeDay');
		$data = $this->ProcessInputData('getFreeDay', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrObserv_model->getFreeDay($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Сохранение назначения с типом "Наблюдение"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Наблюдение"
	 */
	function saveEvnPrescrObserv() {
		$this->load->model('EvnPrescrObserv_model', 'EvnPrescrObserv_model');
		$this->inputRules['saveEvnPrescrObserv'] = $this->EvnPrescrObserv_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrObserv', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrObserv_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Диета"
	 *  Входящие данные: $_POST['EvnPrescr_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Диета"
	 */
	function loadEvnPrescrDietEditForm() {
		$this->load->model('EvnPrescrDiet_model', 'EvnPrescrDiet_model');
		$this->inputRules['loadEvnPrescrDietEditForm'] = $this->EvnPrescrDiet_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrDietEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrDiet_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Сохранение назначения с типом "Диета"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Диета"
	 */
	function saveEvnPrescrDiet() {
		$this->load->model('EvnPrescrDiet_model', 'EvnPrescrDiet_model');
		$this->inputRules['saveEvnPrescrDiet'] = $this->EvnPrescrDiet_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrDiet', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrDiet_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Получение списка выполненных назначений
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: журнал медицинских мероприятий
	 */
	function loadEvnPrescrCompletedJournalGrid() {
		$data = $this->ProcessInputData('loadEvnPrescrCompletedJournalGrid', true, true);

		if (is_array($data) && count($data) > 0) {
			$response = $this->dbmodel->loadEvnPrescrCompletedJournalGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка назначений
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: журнал назначений
	 */
	function loadEvnPrescrJournalGrid() {
		$data = $this->ProcessInputData('loadEvnPrescrJournalGrid', true, true);
		if (is_array($data) && count($data) > 0) {
			$response = $this->dbmodel->loadEvnPrescrJournalGrid($data);
			$this->ProcessModelMultiList($response, true, true, 'При запросе возникла ошибка.', null, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Операция"
	 *  Входящие данные: $_POST['EvnPrescrOper_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Операция"
	 */
	function loadEvnPrescrOperEditForm() {
		$this->load->model('EvnPrescrOper_model', 'EvnPrescrOper_model');
		$this->inputRules['loadEvnPrescrOperEditForm'] = $this->EvnPrescrOper_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrOperEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrOper_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение списка выполненных назначенных процедур
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: журнал выполненных процедур
	 */
	function loadEvnPrescrProcCmpJournalGrid() {
		$data = array();

		$data = $this->ProcessInputData('loadEvnPrescrProcCmpJournalGrid', true, true);

		if (is_array($data) && count($data) > 0) {
			$response = $this->dbmodel->loadEvnPrescrProcCmpJournalGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  Сохранение причины невыполнения назначенной процедуры
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: журнал выполненных процедур
	 */
	function saveEvnPrescrUnExecReason() {
		$data = $this->ProcessInputData('saveEvnPrescrUnExecReason', false, true);
		
		if (is_array($data) && count($data) > 0) {
			$response = $this->dbmodel->saveEvnPrescrUnExecReason($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении причины невыполнения')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение данных для формы редактирования курса назначений с типом "Процедуры и манипуляции"
	 */
	function loadEvnCourseProcEditForm() {
		$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
		$this->inputRules['doLoadEvnCourseProcEditForm'] = $this->EvnPrescrProc_model->getInputRules('doLoadEvnCourseProcEditForm');
		$data = $this->ProcessInputData('doLoadEvnCourseProcEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrProc_model->doLoadEvnCourseProcEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Процедуры и манипуляции"
	 */
	function loadEvnPrescrProcEditForm() {
		$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
		$this->inputRules['loadEvnPrescrProcEditForm'] = $this->EvnPrescrProc_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrProcEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrProc_model->doLoad($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования курса с типом "Лекарственное лечение"
	 *  Входящие данные: $_POST['EvnPrescrTreat_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Лекарственное лечение"
	 */
	function loadEvnCourseTreatEditForm() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->inputRules['loadEvnCourseTreatEditForm'] = $this->EvnPrescrTreat_model->getInputRules('doLoadEvnCourseTreatEditForm');
		$data = $this->ProcessInputData('loadEvnCourseTreatEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrTreat_model->doLoadEvnCourseTreatEditForm($data);
		$this->ProcessModelList($response, false, false)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Лекарственное лечение"
	 *  Входящие данные: $_POST['EvnPrescrTreat_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Лекарственное лечение"
	 */
	function loadEvnPrescrTreatEditForm() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->inputRules['loadEvnPrescrTreatEditForm'] = $this->EvnPrescrTreat_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrTreatEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrTreat_model->doLoad($data);
		$this->ProcessModelList($response, false, false)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных списка назначений с типом "Лекарственное лечение"
	 *  Входящие данные: $_POST['EvnCourse_id']
	 *  На выходе: JSON-строка
	 *  Используется: ЭМК
	 */
	function loadEvnPrescrTreatDrugDataView() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->inputRules['loadEvnPrescrTreatDrugDataView'] = $this->EvnPrescrTreat_model->getInputRules('doLoadEvnPrescrTreatDrugDataView');
		$data = $this->ProcessInputData('loadEvnPrescrTreatDrugDataView', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrTreat_model->doLoadEvnPrescrTreatDrugDataView($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения лабораторной диагностики
	 *  Входящие данные: $_POST['EvnPrescrLabDiag_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения лабораторной диагностики
	 */
	function loadEvnPrescrLabDiagEditForm() {
		$this->load->model('EvnPrescrLabDiag_model', 'EvnPrescrLabDiag_model');
		$this->inputRules['loadEvnPrescrLabDiagEditForm'] = $this->EvnPrescrLabDiag_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrLabDiagEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrLabDiag_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения лабораторной диагностики
	 *  Входящие данные: $_POST['EvnPrescrLabDiag_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения лабораторной диагностики
	 */
	function loadEvnPrescrOperBlockEditForm() {
		$this->load->model('EvnPrescrOperBlock_model', 'EvnPrescrOperBlock_model');
		$this->inputRules['loadEvnPrescrOperBlockEditForm'] = $this->EvnPrescrOperBlock_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrOperBlockEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrOperBlock_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения диагностических процедур
	 *  Входящие данные: $_POST['EvnPrescrFuncDiag_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения диагностических процедур
	 */
	function loadEvnPrescrFuncDiagEditForm() {
		$this->load->model('EvnPrescrFuncDiag_model', 'EvnPrescrFuncDiag_model');
		$this->inputRules['loadEvnPrescrFuncDiagEditForm'] = $this->EvnPrescrFuncDiag_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrFuncDiagEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrFuncDiag_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Режим"
	 *  Входящие данные: $_POST['EvnPrescr_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Режим"
	 */
	function loadEvnPrescrRegimeEditForm() {
		$this->load->model('EvnPrescrRegime_model', 'EvnPrescrRegime_model');
		$this->inputRules['loadEvnPrescrRegimeEditForm'] = $this->EvnPrescrRegime_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrRegimeEditForm', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrRegime_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Сохранение назначения с типом "Режим"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Режим"
	 */
	function saveEvnPrescrRegime() {
		$this->load->model('EvnPrescrRegime_model', 'EvnPrescrRegime_model');
		$this->inputRules['saveEvnPrescrRegime'] = $this->EvnPrescrRegime_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrRegime', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrRegime_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение наблюдений
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма выполнения назначения с типом "Наблюдение"
	 */
	function saveEvnObserv() {
		$data = array();
		$val = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('saveEvnObserv', true);

		if ($data === false) {
			return false;
		}

		if (empty($data['PersonEvn_id']) || empty($data['Server_id'])) {
			$evn = $this->dbmodel->getEvnData([
				'id' => $data['EvnObserv_pid']
			]);
			if (!isset($evn['Server_id'])) {
				throw new Exception('Поле Идентификатор сервера обязательно.');
				//return [['success' => false, 'Error_Msg' => 'Поле Идентификатор сервера обязательно.']];
			}
			if (!isset($evn['PersonEvn_id'])) {
				throw new Exception('Поле Идентификатор состояния человека обязательно.');
				//return [['success' => false, 'Error_Msg' => 'Поле Идентификатор состояния человека обязательно.']];
			}
			$data['PersonEvn_id'] = $evn['PersonEvn_id'];
			$data['Server_id'] = $evn['Server_id'];
		}

		$evnObservData = json_decode(toUTF($data['evnObservDataList']), true);
		//print_r($evnObservData);exit();
		if (!empty($evnObservData)) {
			foreach ($evnObservData as $arrays) {
				$evnObservDataList = array();
				$isFullList = true;
				// Формирование evnObservDataList
				$arrays['Server_id'] = $data['Server_id'];
				$arrays['Lpu_id'] = $data['Lpu_id'];
				$arrays['PersonEvn_id'] = $data['PersonEvn_id'];
				$arrays['pmUser_id'] = $data['pmUser_id'];
				// Реализовать проверку значений
				foreach ($arrays['dataList'] as $key => $array) {
					if (empty($array['ObservParamType_id'])) {
						$this->ReturnError('Не указан тип измеряемого параметра (строка ' . ($key + 1) . ')');
					} else if (empty($array['EvnObservData_Value'])) {
						$evnObservDataList[] = $array;
						if ($array['isMain'] == 1) {
							$isFullList = false;
						}
					} else if ($array['EvnObservData_Value'] < 0) {
						//$this->ReturnError('Неверное значение измеряемого параметра (строка ' . ($key + 1) . ')');
						//return false;
					} else {
						$evnObservDataList[] = $array;
					}
					if (!empty($array['EvnObserv_id'])) {
						$arrays['EvnObserv_id'] = $array['EvnObserv_id'];
					}
				}
				if (count($evnObservDataList) == count($arrays['dataList']) && $isFullList == true) {
					$isFullList = true;
				}


				// Стартуем транзакцию
				$this->dbmodel->beginTransaction();

				if (empty($arrays['EvnObserv_id'])) {
					// Создаем случай наблюдения
					$response = $this->dbmodel->saveEvnObserv($arrays);
					if (!is_array($response) || count($response) == 0) {
						$this->ReturnError('Ошибка при сохранении случая измерения наблюдаемых параметров');
						$this->dbmodel->rollbackTransaction();
						return false;
					} else if (!empty($response[0]['Error_Msg'])) {
						$this->ReturnError($response[0]['Error_Msg']);
						$this->dbmodel->rollbackTransaction();
						return false;
					}
					$arrays['EvnObserv_id'] = $response[0]['EvnObserv_id'];
				}
				foreach ($evnObservDataList as $evnObservDatas) {
					if (empty($evnObservDatas['EvnObservData_id'])) {
						$evnObservDatas['EvnObservData_id'] = null;
					}
					// Сохраняем параметры по списку
					$response = $this->dbmodel->saveEvnObservData(array(
						'EvnObservData_id' => $evnObservDatas['EvnObservData_id']
						, 'EvnObserv_id' => $arrays['EvnObserv_id']
						, 'ObservParamType_id' => $evnObservDatas['ObservParamType_id']
						, 'EvnObservData_Value' => toAnsi($evnObservDatas['EvnObservData_Value'])
						, 'pmUser_id' => $arrays['pmUser_id']
							));

					if (!is_array($response) || count($response) == 0) {
						$this->ReturnError('Ошибка при сохранении измеряемого параметра');
						$this->dbmodel->rollbackTransaction();
						return false;
					} else if (!empty($response[0]['Error_Msg'])) {
						$this->ReturnError($response[0]['Error_Msg']);
						$this->dbmodel->rollbackTransaction();
						return false;
					}
				}

				if ($isFullList) {
					// Помечаем задание как выполненное, если отмечены все параметры наблюдения
					$response = $this->dbmodel->execEvnPrescr(array(
						'EvnPrescr_id' => $arrays['EvnObserv_pid'],
						'pmUser_id' => $arrays['pmUser_id'],
						'PrescriptionType_id' => 10,
					));
					if (!is_array($response) || count($response) == 0) {
						$this->ReturnError('Ошибка при установке признака "Выполнен" для назначения');
						$this->dbmodel->rollbackTransaction();
						return false;
					} else if (!empty($response[0]['Error_Msg'])) {
						$this->ReturnError($response[0]['Error_Msg']);
						$this->dbmodel->rollbackTransaction();
						return false;
					}
				}

				$val['success'] = true;
				$val['isExec'] = $isFullList;

				$this->dbmodel->commitTransaction();
			}
		}
		//echo"OK";
		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 *  Сохранение назначения с типом "Консультационная услуга"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Консультационная услуга"
	 */
	function saveEvnPrescrConsUsluga() {
		$this->load->model('EvnPrescrConsUsluga_model', 'EvnPrescrConsUsluga_model');
		$this->inputRules['saveEvnPrescrConsUsluga'] = $this->EvnPrescrConsUsluga_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrConsUsluga', true, true);
		if ($data === false) {
			return false;
		}
		// Сохраняем назначение
		$response = $this->EvnPrescrConsUsluga_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Списание медикаментов при выполнении назначения с типом "Лекарственное лечение"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма потокового списания медикаментов
	 */
	function saveEvnPrescrDrugStream() {
		$data = array();
		$val = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('saveEvnPrescrDrugStream', true);

		if ($data === false) {
			return false;
		}

		// Обработка списка списываемых медикаментов
		$evnDrugList = json_decode(toUTF($data['evnDrugList']), true);

		if (!is_array($evnDrugList) || count($evnDrugList) == 0) {
			$this->ReturnError('Неверный формат дат продолжительности курса');
			return false;
		}

		// Проверяем правильность заполнения полей
		foreach ($evnDrugList as $key => $array) {
			if (empty($array['EvnSection_id'])) {
				$this->ReturnError('Не указан идентификатор случая движения в стационаре');
				return false;
			} else if (empty($array['EvnDrug_setDate'])) {
				$this->ReturnError('Не указана дата списания медикамента');
				return false;
			} else if (CheckDateFormat($array['EvnDrug_setDate']) != 0) {
				$this->ReturnError('Неверный формат даты списания медикамента');
				return false;
			} else if (empty($array['LpuSection_id'])) {
				$this->ReturnError('Не указано отделение');
				return false;
			} else if (empty($array['Mol_id'])) {
				$this->ReturnError('Не указано МОЛ');
				return false;
			} else if (empty($array['Drug_id'])) {
				$this->ReturnError('Не указана упаковка');
				return false;
			} else if (empty($array['DocumentUcStr_oid'])) {
				$this->ReturnError('Не указана партия');
				return false;
			} else if (empty($array['EvnDrug_Kolvo'])) {
				$this->ReturnError('Не указано количество упаковок');
				return false;
			} else if (empty($array['EvnDrug_KolvoEd'])) {
				$this->ReturnError('Не указано количество единиц дозировки');
				return false;
			}
		}

		$this->load->model('EvnDrug_model', 'edmodel');

		// Стартуем транзакцию
		$this->dbmodel->beginTransaction();

		// Сохраняем EvnDrug
		foreach ($evnDrugList as $array) {
			$array['EvnDrug_setDate'] = ConvertDateFormat($array['EvnDrug_setDate']);

			if (!empty($array['EvnDrug_setTime']) && preg_match("/^\d{2}:\d{2}$/", $array['EvnDrug_setTime'])) {
				$array['EvnDrug_setDate'] .= ' ' . $array['EvnDrug_setTime'];
			}

			$response = $this->edmodel->saveEvnDrug(array(
				'EvnDrug_pid' => $array['EvnSection_id'],
				'EvnDrug_setDate' => $array['EvnDrug_setDate'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $array['Server_id'],
				'PersonEvn_id' => $array['PersonEvn_id'],
				'Drug_id' => $array['Drug_id'],
				'DocumentUcStr_id' => $array['DocumentUcStr_id'],
				'LpuSection_id' => $array['LpuSection_id'],
				'DocumentUcStr_oid' => $array['DocumentUcStr_oid'],
				'Mol_id' => $array['Mol_id'],
				'EvnDrug_Kolvo' => $array['EvnDrug_Kolvo'],
				'EvnDrug_KolvoEd' => $array['EvnDrug_KolvoEd'],
				'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id'],
				'pmUser_id' => $data['pmUser_id']
					));

			if (!is_array($response) || count($response) == 0) {
				$this->ReturnError('Ошибка при списании медикамента');
				$this->edmodel->rollbackTransaction();
				return false;
			} else if (!empty($response[0]['Error_Msg'])) {
				$this->ReturnError($response[0]['Error_Msg']);
				$this->edmodel->rollbackTransaction();
				return false;
			}
		}

		// Помечаем назначение как выполненное
		$response = $this->dbmodel->execEvnPrescr(array(
			'EvnPrescr_id' => $data['EvnPrescrTreat_id'],
			'Timetable_id' => $data['EvnPrescrTreatTimetable_id'],
			'LpuSection_id' => (empty($data['LpuSection_id']) ? NULL : $data['LpuSection_id'] ),
			'MedPersonal_id' => (empty($data['MedPersonal_id']) ? NULL : $data['MedPersonal_id'] ),
			'MedService_id' => (empty($data['MedService_id']) ? NULL : $data['MedService_id'] ),
			'pmUser_id' => $data['pmUser_id'],
			'PrescriptionType_id' => 5
				));

		if (!is_array($response) || count($response) == 0) {
			$this->ReturnError('Ошибка при выполнении назначения');
			$this->dbmodel->rollbackTransaction();
			return false;
		} else if (!empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			$this->dbmodel->rollbackTransaction();
			return false;
		}

		$this->dbmodel->commitTransaction();

		$val['success'] = true;

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 *  Сохранение курса назначений с типом "Манипуляции и процедуры"
	 */
	function saveEvnCourseProc() {
		$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
		$this->inputRules['doSaveEvnCourseProc'] = $this->EvnPrescrProc_model->getInputRules('doSaveEvnCourseProc');
		$data = $this->ProcessInputData('doSaveEvnCourseProc', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrProc_model->doSaveEvnCourseProc($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении курса назначений')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение назначения с типом "Манипуляции и процедуры"
	 */
	function savePolkaEvnPrescrProc() {
		$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
		$this->inputRules['savePolkaEvnPrescrProc'] = $this->EvnPrescrProc_model->getInputRules('doSave');
		$data = $this->ProcessInputData('savePolkaEvnPrescrProc', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrProc_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение назначения с типом "Оперативное лечение"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 */
	function savePolkaEvnPrescrOper() {
		$this->load->model('EvnPrescrOper_model', 'EvnPrescrOper_model');
		$this->inputRules['savePolkaEvnPrescrOper'] = $this->EvnPrescrOper_model->getInputRules('doSave');
		$data = $this->ProcessInputData('savePolkaEvnPrescrOper', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrOper_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение курса назначений с типом "Лекарственное лечение"
	 */
	function saveEvnCourseTreat() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->inputRules['saveEvnCourseTreat'] = $this->EvnPrescrTreat_model->getInputRules('doSaveEvnCourseTreat');
		$data = $this->ProcessInputData('saveEvnCourseTreat', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrTreat_model->doSaveEvnCourseTreat($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении курса назначений')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение назначения с типом "Лекарственное лечение"
	 */
	function saveEvnPrescrTreat() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->inputRules['saveEvnPrescrTreat'] = $this->EvnPrescrTreat_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrTreat', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrTreat_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение назначения с типом "Функциональная диагностика"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Функциональная диагностика"
	 */
	function saveEvnPrescrFuncDiag() {

		$this->load->model('EvnPrescrFuncDiag_model', 'EvnPrescrFuncDiag_model');
		$this->inputRules['saveEvnPrescrFuncDiag'] = $this->EvnPrescrFuncDiag_model->getInputRules('doSave');

		$data = $this->ProcessInputData('saveEvnPrescrFuncDiag', true, true);
		if ($data === false) { return false; }

		// Сохраняем назначение
		$response = $this->EvnPrescrFuncDiag_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();

		return true;
	}

	/**
	 *  Сохранение назначения с типом "Лабораторная диагностика"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Лабораторная диагностика"
	 */
	function saveEvnPrescrLabDiag() {
		$this->load->model('EvnPrescrLabDiag_model', 'EvnPrescrLabDiag_model');
		$this->inputRules['saveEvnPrescrLabDiag'] = $this->EvnPrescrLabDiag_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrLabDiag', true, true);
		if ($data === false) { return false; }
		// Сохраняем назначение
		$response = $this->EvnPrescrLabDiag_model->doSave($data);

		if ($this->dbmodel->getRegionNick() == 'ufa' && !empty($data['EvnDirection_id']) && (!empty($data['HIVContingentTypeFRMIS_id']) || !empty($data['HormonalPhaseType_id']) || !empty($data['CovidContingentType_id']))) {
			$this->load->model('PersonDetailEvnDirection_model', 'edpdmodel');
			$pdresponse = $this->dbmodel->saveObject('PersonDetailEvnDirection', [
				'HormonalPhaseType_id' => !empty($data['HormonalPhaseType_id']) ? $data['HormonalPhaseType_id'] : null,
				'EvnDirection_id' => $data['EvnDirection_id'],
	            'HIVContingentTypeFRMIS_id' => !empty($data['HIVContingentTypeFRMIS_id']) ? $data['HIVContingentTypeFRMIS_id'] : null,
	            'CovidContingentType_id' => !empty($data['CovidContingentType_id']) ? $data['CovidContingentType_id'] : null,
	            'PersonDetailEvnDirection_id' => $data['PersonDetailEvnDirection_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();

		return true;
	}

	/**
	 *  Сохранение назначения с типом "Операционный блок"
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Лабораторная диагностика"
	 */
	function saveEvnPrescrOperBlock() {
		$this->load->model('EvnPrescrOperBlock_model', 'EvnPrescrOperBlock_model');
		$this->inputRules['saveEvnPrescrOperBlock'] = $this->EvnPrescrOperBlock_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrOperBlock', true, true);
		if ($data === false) {
			return false;
		}
		// Сохраняем назначение
		$response = $this->EvnPrescrOperBlock_model->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
		return true;
	}

	/**
	 *  Подписание всех неподписанных назначений
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: ЭМК
	 */
	function signEvnPrescrAll() {
		$data = $this->ProcessInputData('signEvnPrescrAll', true);
		if ($data === false)
			return false;
		$prescrtypes = $this->dbmodel->getEvnPrescrsList(array('EvnPrescr_pid' => $data['EvnPrescr_pid'], 'PrescriptionStatusType_id' => 1));
		if (!is_array($prescrtypes)) {
			$this->ReturnError('Ошибка при подписании назначений');
			return false;
		}
		if (count($prescrtypes) == 0) {
			$this->ReturnError('Неподписанные назначения не найдены');
			return false;
		}

		$this->dbmodel->beginTransaction();
		foreach ($prescrtypes as $row) {
			$tmpdata = $data;
			$tmpdata['PrescriptionType_id'] = $row['PrescriptionType_id'];
			if (in_array($row['PrescriptionType_id'], array(5, 6, 7, 11, 12))) {
				$tmpdata['EvnPrescr_pid'] = $data['EvnPrescr_pid'];
				$tmpdata['EvnPrescr_id'] = $row['EvnPrescr_id'];
			} else {
				$tmpdata['EvnPrescr_pid'] = $row['EvnPrescr_id'];
				$tmpdata['EvnPrescr_id'] = null;
			}
			$response = $this->dbmodel->signEvnPrescr($tmpdata);

			if (!is_array($response) || count($response) == 0) {
				$this->ReturnError('Ошибка при подписании назначений');
				$this->dbmodel->rollbackTransaction();
				return false;
			} else if (!empty($response[0]['Error_Msg'])) {
				$this->ReturnError($response[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
		return true;
	}

	/**
	 *  Подписание назначений
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма потокового ввода назначений, ЭМК
	 */
	function signEvnPrescr() {
		$data = array();
		$val = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('signEvnPrescr', true);
		if ($data === false) {
			return false;
		}

		if (!in_array($data['PrescriptionType_id'], array(1, 2, 4, 5, 6, 7, 10, 11, 12)) && empty($data['EvnPrescr_rangeDate']) && empty($data['EvnPrescr_setDate'])) {
			$this->ReturnError('Дата назначения обязательна для заполнения!');
			return false;
		}

		// Стартуем транзакцию
		$this->dbmodel->beginTransaction();

		// Подписываем назначение(я)
		$response = $this->dbmodel->signEvnPrescr($data);

		if (!is_array($response) || count($response) == 0) {
			$this->ReturnError('Ошибка при подписании назначения');
			$this->dbmodel->rollbackTransaction();
			return false;
		} else if (!empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			$this->dbmodel->rollbackTransaction();
			return false;
		}

		$val['success'] = true;

		$this->dbmodel->commitTransaction();

		$this->ReturnData($val);

		return true;
	}

	/**
	 *  Проверка на дублирование назначения-направления
	 *  Используется: форма добавления назначений услуг
	 */
	function checkDoubleUsluga()
	{
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('checkDoubleUsluga', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkDoubleUsluga($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * 	Сохранение листа назначений как xml-шаблон
	 * 	Входящие данные: EvnPrescr_pid, EvnPrescr_begDate, LpuSection_id, Diag_id
	 * 	На выходе: JSON-строка
	 * 	Используется: форма сохранения шаблона листа назначений
	 */
	function saveEvnPrescrListAsXTemplate() {
		$data = $this->ProcessInputData('saveEvnPrescrListAsXTemplate', true);
		if ($data === false)
			return false;

		// Получим список всех назначений
		$response = $this->dbmodel->getEvnPrescrsList($data);
		if (!is_array($response) || count($response) == 0)
			return false;

		$prescriptions = array();
		// Получаем детальную информацию об назначениях
		foreach ($response as $r) {
			$evnPrescrData = array(
				'PrescriptionType_id' => $r['PrescriptionType_id']
			);

			$dopFields = array();

			switch ($r['PrescriptionType_id']) {
				case 1:
					$evnPrescrType = 'Regime';
					$dopFields[] = 'PrescriptionRegimeType_id as "PrescriptionRegimeType_id"';
					break;
				case 2:
					$evnPrescrType = 'Diet';
					$dopFields[] = 'PrescriptionDietType_id as "PrescriptionDietType_id"';
					break;
				case 5:
					$evnPrescrType = 'Treat';
					$dopFields[] = 'ecp.PerformanceType_id as "PerformanceType_id"';

					$dopFields[] = 'ecp.EvnCourseTreat_MaxCountDay as "EvnPrescrTreat_CountInDay"';
					$dopFields[] = 'ecp.EvnCourseTreat_Duration as "EvnPrescrTreat_CourseDuration"';
					$dopFields[] = 'ecp.EvnCourseTreat_ContReception as "EvnPrescrTreat_ContReception"';
					$dopFields[] = 'ecp.EvnCourseTreat_Interval as "EvnPrescrTreat_Interval"';
					$dopFields[] = 'ec.DurationType_id as "DurationType_id"';
					$dopFields[] = 'ec.DurationType_recid as "DurationType_recid"';
					$dopFields[] = 'ec.DurationType_intid as "DurationType_intid"';
					break;
				case 6:
					$evnPrescrType = 'Proc';
					$dopFields[] = 'ecp.EvnCourseProc_MaxCountDay as "EvnPrescrTreat_CountInDay"';
					$dopFields[] = 'ecp.EvnCourseProc_Duration as "EvnPrescrProc_CourseDuration"';
					$dopFields[] = 'ecp.EvnCourseProc_ContReception as "EvnPrescrProc_ContReception"';
					$dopFields[] = 'ecp.EvnCourseProc_Interval as "EvnPrescrProc_Interval"';
					$dopFields[] = 'ec.DurationType_id as "DurationType_id"';
					$dopFields[] = 'ec.DurationType_recid as "DurationType_nid"';
					$dopFields[] = 'ec.DurationType_intid as "DurationType_sid"';
					break;
				case 7:
					$evnPrescrType = 'Oper';
					break;
				case 10:
					$evnPrescrType = 'Observ';
					$dopFields[] = 'ObservTimeType_id as "ObservTimeType_id"';
					break;
				case 11:
					$evnPrescrType = 'LabDiag';
					$dopFields[] = 'UslugaComplex_id as "UslugaComplex_id"';
					break;
				case 12:
					$evnPrescrType = 'FuncDiag';
					break;
				case 13:
					$evnPrescrType = 'ConsUsluga';
					$dopFields[] = 'UslugaComplex_id as "UslugaComplex_id"';
					break;
				default:
					return array(array('Error_Msg' => 'Неверный тип назначения'));
					break;
			}
			$r['evnPrescrType'] = $evnPrescrType;
			$r['EvnPrescr_begDate'] = $data['EvnPrescr_begDate'];
			$tmpData = $this->dbmodel->getEvnPrescription($r, $dopFields);
			if (!is_array($tmpData) || count($tmpData) == 0)
				return array(array('Error_Msg' => 'Ошибка БД!'));

			$evnPrescrData['dayData'] = $tmpData;

			switch ($r['PrescriptionType_id']) {
				case 5:
					$r['evnPrescrType'] = 'TreatDrug';
					$dopFields = array();
					$dopFields[] = 'epp.Drug_id as "Drug_id"';
					$dopFields[] = 'epp.DrugComplexMnn_id as "DrugComplexMnn_id"';
					$dopFields[] = 'ect.PrescriptionIntroType_id as "PrescriptionIntroType_id"';
					$dopFields[] = 'epp.EvnPrescrTreatDrug_Kolvo as "EvnPrescrTreatDrug_Kolvo"';
					$dopFields[] = 'epp.EvnPrescrTreatDrug_KolvoEd as "EvnPrescrTreatDrug_KolvoEd"';
					$tmpData = $this->dbmodel->getEvnPrescription($r, $dopFields);
					$evnPrescrData['drugData'] = $tmpData;
					break;
				case 10:
					$r['evnPrescrType'] = 'ObservPos';
					$dopFields = array();
					$dopFields[] = 'ObservParamType_id as "ObservParamType_id"';
					$tmpData = $this->dbmodel->getEvnPrescription($r, $dopFields);
					$evnPrescrData['observData'] = $tmpData;
					break;
				case 6:
				case 7:
				case 11:
				case 12:
					$r['evnPrescrType'] = $evnPrescrType . 'Usluga';
					$r['ept'] = $evnPrescrType;
					$dopFields = array();
					$dopFields[] = 'UslugaComplex_id as "UslugaComplex_id"';
					$tmpData = $this->dbmodel->getEvnPrescription($r, $dopFields);
					$evnPrescrData['uslugaData'] = $tmpData;
					break;
			}
			$prescriptions[] = $evnPrescrData;
		}
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getXmlTemplateModelInstance();
		$tpl_data = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Diag_id' => $data['Diag_id'],
			'PersonAgeGroup_id' => $data['PersonAgeGroup_id'],
			'XmlTemplate_Data' => json_encode($prescriptions),
			'XmlTemplateType_id' => swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID,
			'session' => $data['session'],
		);
		$tpl_data['XmlTemplate_id'] = $instance->checkExistsEvnPrescrPlan($tpl_data, true);
		if ( empty($tpl_data['XmlTemplate_id']) ) {
			$tpl_data['scenario'] = 'create';
		} else {
			$tpl_data['scenario'] = 'update';
		}
		$response = $instance->doSave($tpl_data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Загрузка плана назначений из шаблона
	 */
	function getEvnPrescrList() {
		$data = $this->ProcessInputData('getEvnPrescrList', true);

		if ($data === false)
			return false;

		// Проверим есть ли шаблон для данных (LpuSection_id, Diag_id, PersonAgeGroup_id)
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getXmlTemplateModelInstance();
		$result = $instance->loadEvnPrescrPlan($data);

		if ($data['checkTemplateOnExist'] == 1) {
			$this->ReturnData(array('success' => true, 'checkTemplateOnExist' => true));
			return false;
		}

		// Ищем все назначения и удаляем (если есть)
		$evnPrescrsresp = $this->dbmodel->getEvnPrescrsList($data);
		if (is_array($evnPrescrsresp) && count($evnPrescrsresp) > 0) {
			foreach ($evnPrescrsresp as $del_data) {
				$del_data['pmUser_id'] = $data['pmUser_id'];
				$delresp = $this->dbmodel->cancelEvnPrescr($del_data);
				if (!empty($delresp[0]['Error_Msg'])) {
					$this->ReturnError($delresp[0]['Error_Msg']);
					return false;
				}
				/*
				  if(in_array($res['PrescriptionType_id'],array(1,2,3,4,10))) {
				  }
				 */
			}
		}
		$this->load->model('EvnPrescrRegime_model', 'EvnPrescrRegime_model');
		$this->load->model('EvnPrescrDiet_model', 'EvnPrescrDiet_model');
		$this->load->model('EvnPrescrObserv_model', 'EvnPrescrObserv_model');
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->load->model('EvnPrescrOper_model', 'EvnPrescrOper_model');
		$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
		$this->load->model('EvnPrescrLabDiag_model', 'EvnPrescrLabDiag_model');
		$this->load->model('EvnPrescrFuncDiag_model', 'EvnPrescrFuncDiag_model');
		$this->load->model('EvnPrescrConsUsluga_model', 'EvnPrescrConsUsluga_model');
		$this->load->model('EvnPrescrVaccination_model', 'EvnPrescrVaccination_model');
		

		foreach ($result as $res) {
			$observParamTypeList = array();
			$observTimeTypeList = array();
			if ($res['PrescriptionType_id'] == 10) {
				if (isset($res['observData']) && is_array($res['observData']) && count($res['observData']) > 0) {
					foreach ($res['observData'] as $observ) {
						$observParamTypeList[] = $observ['ObservParamType_id'];
					}
				}
				if (isset($res['dayData']) && is_array($res['dayData']) && count($res['dayData']) > 0) {
					foreach ($res['dayData'] as $day) {
						if (!in_array($day['ObservTimeType_id'], $observTimeTypeList)) {
							$observTimeTypeList[] = $day['ObservTimeType_id'];
						}
					}
				}
				if (empty($observParamTypeList) || empty($observTimeTypeList)) {
					continue;
				}
			}
			if (isset($res['dayData']) && is_array($res['dayData']) && count($res['dayData']) > 0) {
				foreach ($res['dayData'] as $r) {
					// Считаем дату
					$newDate = new DateTime($data['EvnPrescr_begDate']);
					if ((int) $r['EvnPrescr_DayNum'] > 1) {
						$newDate->add(new DateInterval('P' . ((int) $r['EvnPrescr_DayNum'] - 1) . 'D'));
					}
					$newDate = $newDate->format('Y-m-d H:i:s');

					$r['session'] = $data['session'];
					$r['Lpu_id'] = $data['Lpu_id'];
					$r['Server_id'] = $data['Server_id'];
					$r['PersonEvn_id'] = $data['PersonEvn_id'];
					$r['pmUser_id'] = $data['pmUser_id'];
					$r['PrescriptionStatusType_id'] = 1;
					if (in_array($res['PrescriptionType_id'], array(1, 2, 10))) {
						$r['EvnPrescr_id'] = null;
						$r['EvnPrescr_pid'] = $data['EvnPrescr_pid'];
						$r['EvnPrescr_setDate'] = $newDate;
						$r['EvnPrescr_dayNum'] = count($res['dayData']);
						$r['EvnPrescr_Descr'] = null;
						switch ($res['PrescriptionType_id']) {
							case 1:
								$resp = $this->EvnPrescrRegime_model->doSave($r);
								break;
							case 2:
								$resp = $this->EvnPrescrDiet_model->doSave($r);
								break;
							case 10:
								$r['EvnPrescr_dayNum'] = count($res['dayData']) / count($observTimeTypeList);
								$r['observParamTypeList'] = json_encode($observParamTypeList);
								$r['observTimeTypeList'] = json_encode($observTimeTypeList);
								$resp = $this->EvnPrescrObserv_model->doSave($r);
								break;
						}
						continue;
					}

					switch ($res['PrescriptionType_id']) {
						case 5:
							$r['EvnPrescrTreat_pid'] = $data['EvnPrescr_pid'];
							$r['EvnPrescrTreat_setDate'] = $newDate;
							$r['EvnPrescrTreat_Descr'] = '';
							$r['PrescriptionTreatType_id'] = null;
							if (isset($res['drugData']) && is_array($res['drugData']) && count($res['drugData']) > 0) {
								foreach ($res['drugData'] as $d) {
									$r['Drug_id'] = $d['Drug_id'];
									$r['DrugComplexMnn_id'] = $d['DrugComplexMnn_id'];
									$r['PrescriptionIntroType_id'] = $d['PrescriptionIntroType_id'];
									$r['Okei_id'] = $d['Okei_id'];
									$r['EvnPrescrTreatDrug_Kolvo'] = $d['EvnPrescrTreatDrug_Kolvo'];
									$r['EvnPrescrTreatDrug_KolvoEd'] = $d['EvnPrescrTreatDrug_KolvoEd'];
								}
							}
							$r['MethodInputDrug_id'] = empty($r['Drug_id']) ? 1 : 2;
							$resp = $this->EvnPrescrTreat_model->doSave($r);
							break;
						case 6:
							$r['EvnPrescrProc_pid'] = $data['EvnPrescr_pid'];
							$r['EvnPrescrProc_setDate'] = $newDate;
							$r['EvnPrescrProc_Descr'] = '';
							$r['EvnPrescrProc_uslugaList'] = '';
							if (isset($res['uslugaData']) && is_array($res['uslugaData']) && count($res['uslugaData']) > 0) {
								$uslugalist = array();
								foreach ($res['uslugaData'] as $u) {
									$uslugalist[] = $u['UslugaComplex_id'];
								}
								$r['EvnPrescrProc_uslugaList'] = implode(',', $uslugalist);
							}
							$resp = $this->EvnPrescrProc_model->doSave($r);
							break;
						case 7:
							$r['EvnPrescrOper_pid'] = $data['EvnPrescr_pid'];
							$r['EvnPrescrOper_setDate'] = $newDate;
							$r['EvnPrescrOper_Descr'] = '';
							$r['EvnPrescrOper_uslugaList'] = '';
							if (isset($res['uslugaData']) && is_array($res['uslugaData']) && count($res['uslugaData']) > 0) {
								$uslugalist = array();
								foreach ($res['uslugaData'] as $u) {
									$uslugalist[] = $u['UslugaComplex_id'];
								}
								$r['EvnPrescrOper_uslugaList'] = implode(',', $uslugalist);
							}
							$resp = $this->EvnPrescrOper_model->doSave($r);
							break;
						case 11:
							$r['EvnPrescrLabDiag_pid'] = $data['EvnPrescr_pid'];
							$r['EvnPrescrLabDiag_setDate'] = $newDate;
							$r['EvnPrescrLabDiag_Descr'] = '';
							$r['EvnPrescrLabDiag_uslugaList'] = '';
							if (isset($res['uslugaData']) && is_array($res['uslugaData']) && count($res['uslugaData']) > 0) {
								$uslugalist = array();
								foreach ($res['uslugaData'] as $u) {
									$uslugalist[] = $u['UslugaComplex_id'];
								}
								$r['EvnPrescrLabDiag_uslugaList'] = implode(',', $uslugalist);
							}
							$resp = $this->EvnPrescrLabDiag_model->doSave($r);
							break;
						case 12:
							$r['EvnPrescrFuncDiag_pid'] = $data['EvnPrescr_pid'];
							$r['EvnPrescrFuncDiag_setDate'] = $newDate;
							$r['EvnPrescrFuncDiag_Descr'] = '';
							$r['EvnPrescrFuncDiag_uslugaList'] = '';
							if (isset($res['uslugaData']) && is_array($res['uslugaData']) && count($res['uslugaData']) > 0) {
								$uslugalist = array();
								foreach ($res['uslugaData'] as $u) {
									$uslugalist[] = $u['UslugaComplex_id'];
								}
								$r['EvnPrescrFuncDiag_uslugaList'] = implode(',', $uslugalist);
							}
							$resp = $this->EvnPrescrFuncDiag_model->doSave($r);
							break;
						case 13:
							$r['EvnPrescrConsUsluga_pid'] = $data['EvnPrescr_pid'];
							$r['EvnPrescrConsUsluga_setDate'] = $newDate;
							$r['EvnPrescrConsUsluga_Descr'] = '';
							$resp = $this->EvnPrescrConsUsluga_model->doSave($r);
							break;
						default:
							return array(array('Error_Msg' => 'Неверный тип назначения'));
							break;
					}
					if (isset($resp) && !empty($resp[0]['Error_Msg'])) {
						$this->ReturnError($resp[0]['Error_Msg']);
						return false;
					}
				}
			}
		}
		$this->returnData(array('success' => true));
		return true;
	}

	/**
	 * Перенос плановой даты в форме "Лист назначений"
	 * @return bool
	 */
	function EvnPrescrMoveInDay() {
		$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
		$this->inputRules['EvnPrescrMoveInDay'] = $this->EvnPrescrList_model->getInputRules('doMoveInDay');
		$data = $this->ProcessInputData('EvnPrescrMoveInDay', true);
		if ($data === false)
			return false;
		$response = $this->EvnPrescrList_model->doMoveInDay($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Подписание всех назначений в рамках учетного документа
	 * @return bool
	 */
	function EvnPrescrSignAll() {
		$data = $this->ProcessInputData('EvnPrescrSignAll', true);
		if ($data === false)
			return false;

		$response = $this->dbmodel->getEvnPrescrsList($data);
		if (!is_array($response) || count($response) == 0) {
			$this->ReturnError('Ошибка БД!');
			return false;
		}
		//print_r($response); exit();
		foreach ($response as $res) {
			$dopFields = array();
			switch ($res['PrescriptionType_id']) {
				case 1: $evnPrescrType = 'Regime';
					break;
				case 2: $evnPrescrType = 'Diet';
					break;
				case 3: $evnPrescrType = 'Diag';
					break;
				case 4: $evnPrescrType = 'Cons';
					break;
				case 5: $evnPrescrType = 'Treat';
					break;
				case 6: $evnPrescrType = 'Proc';
					break;
				case 7: $evnPrescrType = 'Oper';
					break;
				case 10: $evnPrescrType = 'Observ';
					break;
				case 11: $evnPrescrType = 'LabDiag';
					break;
				case 12: $evnPrescrType = 'FuncDiag';
					break;
				default:
					return false;
					break;
			}
			$res['evnPrescrType'] = $evnPrescrType;
			// Warning!!! далее быдлокод!!
			$dopFields[0] = 'EvnPrescr' . $evnPrescrType . '_setDate';
			$dopFields[1] = 'PrescriptionStatusType_id';

			$prescrData = $this->dbmodel->getEvnPrescription($res, $dopFields);
			if (!is_array($prescrData) || count($prescrData) == 0) {
				$this->ReturnError('Ошибка БД!');
				return false;
			}
			//print_r($prescrData); exit();
			foreach ($prescrData as $prescr) {
				$prescr['EvnPrescr_pid'] = $res['EvnPrescr_id'];
				$prescr['PrescriptionType_id'] = $res['PrescriptionType_id'];
				$prescr['EvnPrescr_setDate'] = $prescr['EvnPrescr' . $evnPrescrType . '_setDate']->format('Y-m-d');
				$prescr['pmUser_id'] = $data['pmUser_id'];
				//print_r($prescr); exit();
				// Если назначение не подписанное
				if ($prescr['PrescriptionStatusType_id'] != 2) {
					$this->dbmodel->beginTransaction();
					$resp = $this->dbmodel->signEvnPrescr($prescr);
					if (!is_array($resp) || count($resp) == 0) {
						$this->ReturnError('Ошибка при подписании назначения');
						$this->dbmodel->rollbackTransaction();
						return false;
					} else if (!empty($resp[0]['Error_Msg'])) {
						$this->ReturnError($resp[0]['Error_Msg']);
						$this->dbmodel->rollbackTransaction();
						return false;
					}
					$this->dbmodel->commitTransaction();
				}
			}
		}
		$this->ReturnData(array('success' => true));
		return true;
	}

	/**
	 * Получение данных для формы добавления услуги при выполнении назначения с оказанием услуги
	 * @return bool
	 */
	function loadEvnUslugaData() {
		$data = $this->ProcessInputData('loadEvnUslugaData', true);
		if ($data === false)
			return false;

		$response = $this->dbmodel->loadEvnUslugaData($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Печать листа врачебных назначений
	 *
	 * @return bool
	 */
	function printEvnPrescrList() {
		$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
		$this->inputRules['doloadEvnPrescrDoctorList'] = $this->EvnPrescrList_model->getInputRules('doloadEvnPrescrDoctorList');
		$data = $this->ProcessInputData('doloadEvnPrescrDoctorList', true);
		if ($data === false) {
			return false;
		}
		try {
			$parse_data = $this->EvnPrescrList_model->doloadEvnPrescrDoctorList($data);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		if (allowPersonEncrypHIV($data['session']) && !empty($parse_data['PersonEncrypHIV_Encryp'])) {
			$parse_data['Person_FIO'] = $parse_data['PersonEncrypHIV_Encryp'];
		}
		$this->load->library('parser');
		$isPolka = (in_array($parse_data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom')));
		if ($isPolka) {
			$doc = $this->parser->parse('print_evnprescr_polka', $parse_data, true);
		} else { 
			if ($data['session']['region']['nick'] == 'ufa') {
				
						if(isset($data['DocType_id']) && $data['DocType_id'] == 5)
							$parse_data['Head'] = 'Лист лекарственных назначений';
						else if(isset($data['DocType_id']) && $data['DocType_id'] == -5)
							$parse_data['Head'] = 'План обследования';
						else 
							$parse_data['Head'] = 'Лист назначений';
						//echo '<pre>' . print_r($parse_data, 1) . '</pre>'; exit;
						$doc = $this->parser->parse('ufa_print_evnprescr_list', $parse_data, true);
			}
			else
				$doc = $this->parser->parse('print_evnprescr_list', $parse_data, true);
		}
		// echo $doc; return true;
		if ($isPolka) {
			$paper_format = 'A5';
		} else {
			$paper_format = 'A4';
		}
		$paper_orient = ''; // '-L';
		$font_size = '10';
		$margin_top = 10;
		$margin_right = 10;
		$margin_bottom = 10;
		$margin_left = 10;

		require_once('vendor/autoload.php');
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => $paper_format.$paper_orient,
			'default_font_size' => $font_size,
			'margin_left' => $margin_left,
			'margin_right' => $margin_right,
			'margin_top' => $margin_top,
			'margin_bottom' => $margin_bottom,
			'margin_header' => 10,
			'margin_footer' => 10
		]);
		//var_dump($mpdf); return true;
		$mpdf->charset_in = (defined('USE_UTF') && USE_UTF ? 'utf-8' : 'cp1251');
		$mpdf->onlyCoreFonts = true;
		$mpdf->list_indent_first_level = 0;
		$mpdf->WriteHTML($doc, 2);
		$mpdf->Output('ep_list.pdf', 'I');
		return true;
	}

	/**
	 * Печать списка направлений на лабораторное исследование
	 *
	 * @return bool
	 */
	function printLabDirections() {
		$data = $this->ProcessInputData('printLabDirections', false);
		if ($data === false) {
			return false;
		}
		$this->load->model('EvnPrescrList_model', 'model');
		try {
			$person = $this->model->doloadPatientInfo($data);
			$mo = $this->model->doloadMoInfo($data);
			$parse_data['LabDirections'] = $this->model->doloadLabDirectionList($data);

			$parse_data['Person_FIO'] = $person->Person_FIO;
			$parse_data['Person_Birthday'] = $person->Person_Birthday;

			$parse_data['Org_Name'] = $mo->Org_Name;
			$parse_data['Org_Address'] = $mo->Org_Address;

			$this->load->library('QBarcode');
			$parse_data['Barcode'] = QBarcode::getBarcode128Base64($person->Person_id);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}

		$this->load->library('parser');

		$doc = $this->parser->parse('ufa_print_labdirection', $parse_data, true);
		
		$paper_format = 'A5';
		$paper_orient = '-L';
		$font_size = '10';
		$margin_top = 10;
		$margin_right = 10;
		$margin_bottom = 10;
		$margin_left = 10;
		require_once('vendor/autoload.php');
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => $paper_format.$paper_orient,
			'default_font_size' => $font_size,
			'margin_left' => $margin_left,
			'margin_right' => $margin_right,
			'margin_top' => $margin_top,
			'margin_bottom' => $margin_bottom,
			'margin_header' => 10,
			'margin_footer' => 10
		]);
		$mpdf->charset_in = (defined('USE_UTF') && USE_UTF ? 'utf-8' : 'cp1251');
		$mpdf->onlyCoreFonts = true;
		$mpdf->list_indent_first_level = 0;
		
		$mpdf->WriteHTML($doc, 2);
		$mpdf->Output('ep_list.pdf', 'I');
		return true;
	}

	/**
	 * Получение данных для правой панели формы ввода назначениий услуг
	 *
	 * @return bool
	 */
	function loadEvnPrescrUslugaDataView() {
		$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
		$this->inputRules['loadEvnPrescrUslugaDataView'] = $this->EvnPrescrList_model->getInputRules('doLoadEvnPrescrUslugaDataView');
		$data = $this->ProcessInputData('loadEvnPrescrUslugaDataView', true);
		if ($data === false)
			return false;
		$response = $this->EvnPrescrList_model->doloadEvnPrescrUslugaDataView($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы "Лист назначений"
	 *
	 * @return bool
	 */
	function loadEvnPrescrList() {
		$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
		$this->inputRules['loadEvnPrescrList'] = $this->EvnPrescrList_model->getInputRules('doloadEvnPrescrList');
		$data = $this->ProcessInputData('loadEvnPrescrList', true);
		if ($data === false)
			return false;
		$response = $this->EvnPrescrList_model->doloadEvnPrescrList($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы "Лист назначений" с новым дизайном
	 *
	 * @return bool
	 */
	function getPrescrPlanView() {
		$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
		$this->inputRules['loadEvnPrescrList'] = $this->EvnPrescrList_model->getInputRules('doloadEvnPrescrList');
		$data = $this->ProcessInputData('loadEvnPrescrList', true);
		if ($data === false)
			return false;
		$response = $this->EvnPrescrList_model->getPrescrPlanView($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для комбика "Назначение"
	 *
	 * @return bool
	 */
	function loadEvnPrescrCombo() {
		$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
		$this->inputRules['doLoadEvnPrescrCombo'] = $this->EvnPrescrList_model->getInputRules('doLoadEvnPrescrCombo');
		$data = $this->ProcessInputData('doLoadEvnPrescrCombo', true);
		if ($data === false)
			return false;
		$response = $this->EvnPrescrList_model->doLoadEvnPrescrCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка списка единиц измерения
	 */
	function loadEdUnitsList() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$response = $this->EvnPrescrTreat_model->loadEdUnitsList();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для комбика "Назначение" формы списания
	 */
	function loadEvnPrescrTreatDrugCombo() {
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$this->inputRules['doLoadEvnPrescrTreatDrugCombo'] = $this->EvnPrescrTreat_model->getInputRules('doLoadEvnPrescrTreatDrugCombo');
		$data = $this->ProcessInputData('doLoadEvnPrescrTreatDrugCombo', false);
		if ($data === false)
			return false;

		$response = array();
		switch($this->EvnPrescrTreat_model->options['drugcontrol']['drugcontrol_module']) {
			case 1:
				$response = $this->EvnPrescrTreat_model->doOldLoadEvnPrescrTreatDrugCombo($data); //Старая схема учета
				break;
			case 2:
				$response = $this->EvnPrescrTreat_model->doLoadEvnPrescrTreatDrugCombo($data);
				break;
		}

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

    /**
     * Получение значения по умолчанию для полей в лекарственном лечении
     */
    function getDrugPackData() {
        $this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
        $data = $this->ProcessInputData('getDrugPackData', false);
        if ($data === false) {
            return false;
        }
        $response = $this->EvnPrescrTreat_model->getDrugPackData($data);
        $this->ProcessModelSave($response)->ReturnData();
        return true;
    }

    /**
     * Получение значения по умолчанию для полей в лекарственном лечении
     */
    function getFreeEvnPrescrProc() {
        $this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
        $data = $this->ProcessInputData('getDrugPackData', false);
        if ($data === false) {
            return false;
        }
        $response = $this->EvnPrescrTreat_model->getDrugPackData($data);
        $this->ProcessModelSave($response)->ReturnData();
        return true;
    }

	/**
	 * назначение/снятие флага Cito
	 * @return bool
	 */
	function setCitoEvnPrescr() {
		$data = $this->ProcessInputData('setCitoEvnPrescr', true);
		if ($data === false)
			return false;


		$resp = $this->dbmodel->setCitoEvnPrescr($data);
		if (!is_array($resp) || count($resp) == 0) {
			$this->ReturnError('Ошибка при назначении Cito');
			$this->dbmodel->rollbackTransaction();
			return false;
		} else if (!empty($resp[0]['Error_Msg'])) {
			$this->ReturnError($resp[0]['Error_Msg']);
			$this->dbmodel->rollbackTransaction();
			return false;
		}
		//print_r($resp); exit();
		$this->ReturnData(array('success' => true));
		return true;
	}
	/**
	 *  Отмена/удаление назначения из направления объединенных услуг
	 *  Входящие данные: $_POST['EvnPrescr_id'], $_POST['PrescriptionType_id']
	 *  На выходе: JSON-строка
	 */
	function deleteFromDirection() {
		$data = $this->ProcessInputData('deleteFromDirection', true, true);
		if ($data === false) {
			return false;
		}
		if($this->usePostgreLis && !empty($data['DirType_id']) && $data['DirType_id'] == 10){
			$this->load->swapi('lis');
			// А вот не ясно, все ли там удалено или нет, но вроде как все записи
			$response = $this->lis->POST('EvnPrescr/deleteFromDirection', $data, 'single');
			// Если с postgre все прошло плохо, об этом надо предупредить
			if(is_array($response) && !empty($response['Error_Msg'])){
				$this->ReturnError($response['Error_Msg']);
				return false;
			} else {
				// Если postgre справилась, надо ошмётки направления (связки) удалить из MS
				$response = $this->dbmodel->findAndDeleteEvnPrescrDirection($data);
			}
		} else {
			$response = $this->dbmodel->deleteFromDirection($data);
		}

		$this->ProcessModelSave($response, true, 'Ошибка при отмене/удалении назначения')->ReturnData();
		return true;
	}
	
	/**
	 * Загрузка списка назначений / выполнений
	 */
	function loadPrescrPerformanceList() {
		
		$data = $this->ProcessInputData('loadPrescrPerformanceList', false);
                //echo '<pre>' . print_r($data, 1) . '</pre>'; exit;
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadPrescrPerformanceList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка отделений для фильтрации медикаментов на форме редактирования назначения
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: комбобокс "Отделение" на форме редактирования назначения
	 */
	function loadLpuSectionCombo() {
		$data = $this->ProcessInputData('loadLpuSectionCombo', false);
		if ($data == false) {
			return false;
		}
		$response = $this->dbmodel->loadLpuSectionCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *  Получение списка отделений для фильтрации медикаментов на форме редактирования назначения
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: комбобокс "Отделение" на форме редактирования назначения
	 */
	function loadStorageCombo() {
		$data = $this->ProcessInputData('loadStorageCombo', false);
		if ($data == false) {
			return false;
		}
		$response = $this->dbmodel->loadStorageCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Получение списка клин. рекомендаций
	 */
	function loadEvnVizitWithPrescrList(){
		$data = $this->ProcessInputData('loadEvnVizitWithPrescrList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnVizitWithPrescrList($data);
		if(!empty($response['countRouteList']))
			$this->ReturnData($response);
		else
			$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Применеие списка клин. рекомендаций
	 */
	function saveTreatmentStandardsForm(){
		$data = $this->ProcessInputData('saveTreatmentStandardsForm', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->saveTreatmentStandardsForm($data);

		$this->ProcessModelSave($response, true)->ReturnData($response);
		return true;
	}

	/**
	 *  Получение данных для формы редактирования назначения с типом "Вакцинация"
	 *  Входящие данные: $_POST['EvnPrescr_pid']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Вакцинация"
	 */
	function loadEvnPrescrVaccinationGrid() {
		

		$this->load->model('EvnPrescrVaccination_model', 'EvnPrescrVaccination_model');
		$this->inputRules['loadEvnPrescrVaccinationForm'] = $this->EvnPrescrVaccination_model->getInputRules('doLoad');
		$data = $this->ProcessInputData('loadEvnPrescrVaccinationForm', true, true);
		
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrVaccination_model->doLoad($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
     * Сохранение вакцин и доз в назначении вакцинации
     * после подтверждения пользователем
     */
    function saveEvnPrescrVaccination() {

		$this->load->model('EvnPrescrVaccination_model', 'EvnPrescrVaccination_model');
		$this->inputRules['saveEvnPrescrVaccination'] = $this->EvnPrescrVaccination_model->getInputRules('doSave');
		$data = $this->ProcessInputData('saveEvnPrescrVaccination', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrVaccination_model->doSaveEvnCourseVaccination($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
	}

	/**
	 * deleteEvnPrescrVaccination - удаление назначения вакцинации
	 */
	function deleteEvnPrescrVaccination() {
		$this->load->model('EvnPrescrVaccination_model', 'EvnPrescrVaccination_model');
		$this->inputRules['deleteEvnPrescrVaccination'] = $this->EvnPrescrVaccination_model->getInputRules('doDelete');
		$data = $this->ProcessInputData('deleteEvnPrescrVaccination', true, true);
		if ($data === false) {
			return false;
		}
	
		$response = $this->EvnPrescrVaccination_model->deleteEvnPrescrVaccination($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении назначения')->ReturnData();
	}

	/**
	 * saveVacinationDirection - направление в кабинет вакцинации (*после назначения)
	 */
	function saveVacinationDirection() {
		$this->load->model('EvnPrescrVaccination_model', 'EvnPrescrVaccination_model');
		$this->inputRules['saveVacinationDirection'] = $this->EvnPrescrVaccination_model->getInputRules('saveVacinationDirection');
		$data = $this->ProcessInputData('saveVacinationDirection', true, true);
		if ($data === false) {
			return false;
		}
		$response = $this->EvnPrescrVaccination_model->saveVacinationDirection($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении назначения')->ReturnData();
	}



	/**
	 * saveEvnPrescrPermission - сохранение согласия на вакцинацию
	 * 
	 * @param bool PersonalStatement - личное заявление
	 */
	function saveEvnPrescrPermission() {
		$this->load->model('EvnPrescrVaccination_model', 'EvnPrescrVaccination_model');
		$this->inputRules['doSaveEvnPrescrPermission'] = $this->EvnPrescrVaccination_model->getInputRules('savePermission');
        $data = $this->ProcessInputData('doSaveEvnPrescrPermission', true);
        if ($data === false) {
            return false;
        }
        $response = $this->EvnPrescrVaccination_model->doSaveEvnPrescrPermission($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
	}

	/**
	 * deleteEvnPrescrPermission - удаление согласия на вакцинацию
	 */
	function deleteEvnPrescrPermission(){
		$this->load->model('EvnPrescrVaccination_model', 'EvnPrescrVaccination_model');
		$this->inputRules['deleteEvnPrescrPermission'] = $this->EvnPrescrVaccination_model->getInputRules('deletePermission');
		$data = $this->ProcessInputData('deleteEvnPrescrPermission', true);
		if ($data === false) {
            return false;
		}
		$response = $this->EvnPrescrVaccination_model->doDeleteEvnPrescrPermission($data);
		$this->ProcessModelSave($response, true)->ReturnData();
        return true;
	}
	 /** yl:5588 Действующие лекарственные наначения для пациента
	 */
	function checkPersonPrescrTreat(){
		$this->load->model("EvnPrescrTreat_model", "EvnPrescrTreat_model");
		$data = $this->ProcessInputData("checkPersonPrescrTreat", false);
		if ($data === false) {return false;}
		$response = $this->EvnPrescrTreat_model->checkPersonPrescrTreat($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
}
