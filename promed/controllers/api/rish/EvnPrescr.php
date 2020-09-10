<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPrescr - контроллер API для работы с назначениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Maksim Sysolin
 */

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property EvnPrescr_model $dbmodel
*/
class EvnPrescr extends SwREST_Controller {
	protected  $inputRules = array(
		'getEvnPrescr' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescrType_id', 'label' => 'Идентификатор типа назначения', 'rules' => '', 'type' => 'id')
		),
		'mUpdateEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			// для типа назначений режим и диета
			array('field' => 'PrescriptionRegimeType_id', 'label' => 'Тип режима', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionDietType_id', 'label' => 'Тип диеты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_Descr', 'label' => 'комментарий к назначению', 'rules' => '', 'type' => 'string'),
			array('field' => 'accessType', 'label' => 'тип доступа', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescr_dayNum', 'label' => 'Продолжать дней', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescr_setDate', 'label' => 'Дата начала назначения', 'rules' => '', 'type' => 'date'),
		),
		'mSaveEvnPrescr' => array(
			array('field' => 'PrescriptionType_id','label' => 'Идентификатор типа назначения','rules' => 'required','type' => 'id'),
			array('field' => 'Evn_id','label' => 'Идентификатор род. события','rules' => 'required','type' => 'id'),
			array('field' => 'Timetable_id','label' => 'Идентификатор бирки для записи','rules' => '','type' => 'id'),
			array('field' => 'StudyTarget_id','default' => 2, 'label' => 'Цель исследования','rules' => '','type' => 'int'),

			// параметра сохранения для услуг и служб
			array('field' => 'UslugaComplex_id','label' => 'Услуга для назначения','rules' => '','type' => 'id'),
			array('field' => 'MedService_id','label' => 'Место оказания','rules' => '','type' => 'int'),
			array('field' => 'From_MedStaffFact_id','label' => 'Врач который сделал назначение','rules' => '','type' => 'id'),

			// параметры сохранения направления
			array('field' => 'DirType_id','label' => 'Тип направления','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_IsAuto','default' => 2,'label' => 'Автоматическое направление','rules' => '','type' => 'int'),
			array('field' => 'EvnDirection_Num','default' => 0,'label' => 'Номер направления','rules' => '','type' => 'int'),
			array('field' => 'EvnDirection_IsCito','default' => 1,'label' => 'Срочность направления','rules' => '','type' => 'int'),
			array('field' => 'EvnPrescr_IsCito','label' => 'Срочность назначения','rules' => '','type' => 'int'),
			array('field' => 'Diag_id','label' => 'Диагноз','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_Descr','label' => 'Обоснование','rules' => '','type' => 'string'),
			array('field' => 'MedPersonal_zid','label' => 'Зав. отделением','rules' => '','type' => 'id'),

			//параметры сохранения заказа
			array('field' => 'composition','label' => 'Состав услуги','rules' => '','type' => 'array'),
			array('field' => 'pzm_MedService_id','label' => 'Пункт забора','rules' => '','type' => 'id'),

			//для типа назначений "манипуляции и процедуры"
			array('field' => 'DurationType_id','label' => 'Тип продолжительности курса','rules' => '','type' => 'id'),
			array('field' => 'EvnCourseProc_Duration','label' => 'Продолжительность курса','rules' => '','type' => 'int'),
			array('field' => 'EvnPrescrProc_Descr','label' => 'Описание','rules' => '','type' => 'string'),

			// для типа назначений режим и диета
			array('field' => 'PrescriptionRegimeType_id', 'label' => 'Тип режима', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionDietType_id', 'label' => 'Тип диеты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_Descr', 'label' => 'комментарий к назначению', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescr_setDate', 'label' => 'Дата начала назначения', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescr_dayNum', 'label' => 'Продолжать дней', 'rules' => '', 'type' => 'int')
		),
		'mGetUslugaComplexList' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('field' => 'sort','label' => 'Поле для сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'dir','label' => 'Направление сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'PrescriptionType_id','label' => 'Тип назначения','rules' => 'required','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение пользователя','rules' => 'required','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Фильтр по услуге','rules' => '','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'Фильтр по ЛПУ','rules' => '','type' => 'id'),
			array('field' => 'MedService_id','label' => 'Фильтр по службе','rules' => '','type' => 'id'),
			array('field' => 'pzm_MedService_id','label' => 'Фильтр по службе','rules' => '','type' => 'id'),
			array('field' => 'groupByUslugaPlace','label' => 'Группировать по месту оказания','rules' => '','type' => 'boolean'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'int'),
			array('field' => 'filter', 'label' => 'Фильтры грида', 'rules' => '', 'type' => 'json_array', 'assoc' => true),
			array('field' => 'onlyByContract', 'default'=> 0, 'label' => 'Услуги по договорам','rules' => '','type' => 'int'),
			array('field' => 'isOnlyPolka', 'default' => 0, 'label' => 'Флаг отображения служб только поликлинических отделений','rules' => '','type' => 'int')
		),
		'mGetMedServiceList' => array(
			array('field' => 'Lpu_isAll', 'label' => 'Все ЛПУ?', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedServiceType_SysNick','label' => 'Тип службы', 'rules' => '','type' => 'string'),
		),
		'mGetUslugaComplexPlacesList' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('field' => 'sort','label' => 'Поле для сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'dir','label' => 'Направление сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'LpuSection_id','label' => 'Отделение пользователя','rules' => 'required','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Фильтр по услуге','rules' => '','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'Фильтр по ЛПУ','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'PrescriptionType_id','label' => 'Тип назначения','rules' => 'required','type' => 'int'),
			array('default' => 0,'field' => 'isOnlyPolka','label' => 'Флаг отображения служб только поликлинических отделений','rules' => '','type' => 'int')
			//array('field' => 'filterByLpu_str','label' => 'Фильтр по ЛПУ','rules' => 'ban_percent|trim','type' => 'string'),
		),
		'mCancelEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина смены статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnComment_Comment','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
		),
		'mGetUslugaComplexComposition' => array(
			array('field' => 'UslugaComplexMedService_id', 'label' => 'Идентификатор услуги службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ места оказания услуги', 'rules' => 'required', 'type' => 'id')
		),
		'mGetDrugPackData' => array(
			array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugComplexMnn_id', 'label' => 'Комплексное МНН', 'rules' => '', 'type' => 'id')
		),
		'mSaveEvnPrescrTreat' => array(

			array('field' => 'EvnCourseTreat_id', 'label' => 'Идентификатор курса назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор посещения/движения', 'rules' => 'required', 'type' => 'id'),

			array('field' => 'DrugComplexMnn_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionIntroType_id', 'label' => 'Способ применения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnCourseTreat_setDate', 'label' => 'Дата начала назначения', 'rules' => 'required', 'type' => 'date'),

			array('field' => 'EvnCourseTreat_CountDay', 'label' => 'Количество приемов в сутки', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnCourseTreat_Duration', 'label' => 'Продолжительность приема (кол-во)', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DurationType_id', 'label' => 'Продолжительность приема (тип)', 'rules' => 'required', 'type' => 'id'),

			array('field' => 'KolvoEd', 'label' => 'Кол-во ЛС на прием', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GoodsUnit_sid', 'label' => 'Кол-во ЛС на прием (ед. изм.)', 'rules' => 'required', 'type' => 'id'),

			array('field' => 'Kolvo', 'label' => 'Доза на прием', 'rules' => '', 'type' => 'int'),
			array('field' => 'GoodsUnit_id', 'label' => 'Доза на прием (ед. изм.)', 'rules' => '', 'type' => 'id'),

			array('field' => 'PerformanceType_id', 'label' => 'Исполнение', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrTreat_IsCito', 'default' => 0, 'label' => 'cito', 'rules' => '', 'type' => 'boolean'),
			array('field' => 'EvnPrescrTreat_Descr', 'label' => 'Комментарий к назначению', 'rules' => '', 'type' => 'string'),

			// не учитываются в новой форме, но должны быть
			array('field' => 'EvnCourseTreat_ContReception',  'default' => 1, 'label' => 'Продолжительности непрерывного приема', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCourseTreat_Interval', 'default' => 0, 'label' => 'Продолжительность перерыва', 'rules' => '', 'type' => 'id'),
			array('field' => 'DurationType_recid', 'default' => 1, 'label' => 'Тип продолжительности непрерывного приема', 'rules' => '', 'type' => 'id'),
			array('field' => 'DurationType_intid', 'default' => 1, 'label' => 'Тип продолжительности перерыва', 'rules' => '', 'type' => 'id'),

			array('field' => 'PrescriptionTreatType_id', 'label' => 'PrescriptionTreatType_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDesease_id', 'label' => 'ResultDesease_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'Morbus_id', 'label' => 'Morbus_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCourseTreatDrug_id', 'label' => 'EvnCourseTreatDrug_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id')

			// отключены
			//array('field' => 'EvnPrescr_isExec', 'label' => 'Признак выполнения назначения', 'rules' => 'required', 'type' => 'api_flag'),
		),
		'mGetSectionProfileList' => array(
			array('field' => 'AddLpusectionProfiles', 'label' => 'Грузить дополнительные профили отделения', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedSpecOms_id', 'label' => 'Специальность', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfileGRAPP_CodeIsNotNull', 'label' => 'Признак', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionProfileGRKSS_CodeIsNotNull', 'label' => 'Признак', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionProfileGRSZP_CodeIsNotNull', 'label' => 'Признак', 'rules' => '', 'type' => 'int'),
			array('field' => 'onDate', 'label' => 'Дата', 'rules' => '', 'type' => 'date')
		),
		'mGetDirectionLpuUnitList' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('field' => 'ListForDirection', 'label' => 'Показывать список для направлений', 'rules' => '', 'type' => 'id'),
			array('field' => 'DirType_Code', 'label' => 'Тип направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'Filter_Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Filter_LpuUnit_id', 'label' => 'Идентификатор Подразделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Filter_LpuSection_id', 'label' => 'Идентификатор текущего отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Filter_LpuRegionType_id', 'label' => 'Тип прикрепления', 'rules' => '', 'type' => 'id'),
			array('field' => 'Filter_Lpu_Nick', 'label' => 'Название ЛПУ', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Filter_includeDopProfiles', 'label' => 'Признак необходимости учитывать доп. профили отделений', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'Filter_MedPersonal_FIO', 'label' => 'ФИО врача', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_KLTown_Name', 'label' => 'Населенный пункт', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_KLStreet_Name', 'label' => 'Улица', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_KLHouse', 'label' => 'Номер дома', 'rules' => 'trim|mb_strtoupper', 'type' => 'string'),
			array('field' => 'Filter_LpuUnitType_id', 'label' => 'Тип подразделения', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_LpuType_id', 'label' => 'Тип ЛПУ', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_LpuUnit_Address', 'label' => 'Адрес подразделения', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'WithoutChildLpuSectionAge', 'label' => 'Скрыть детские отделения', 'rules' => '', 'type' => 'int'),
			array('field' => 'ARMType', 'label' => 'Текущий тип АРМа', 'rules' => '', 'type' => 'string')
		),
		'mGetDirectionMedPersonalList' => array(
			array('field' => 'LpuUnit_id', 'label' => 'Идентификатор подразделения ЛПУ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Date', 'label' => 'Дата начала отображения расписания', 'rules' => '', 'type' => 'string',),
			array('field' => 'Filter_Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Filter_LpuRegionType_id', 'label' => 'Тип прикрепления', 'rules' => '', 'type' => 'id'),
			array('field' => 'Filter_LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Filter_includeDopProfiles', 'label' => 'Признак необходимости учитывать доп. профили отделений', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'Filter_MedPersonal_FIO', 'label' => 'ФИО врача', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_KLTown_Name', 'label' => 'Населенный пункт', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_KLStreet_Name', 'label' => 'Улица', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Filter_KLHouse', 'label' => 'Номер дома', 'rules' => 'trim|mb_strtoupper', 'type' => 'string'),
			array('field' => 'ListForDirection', 'label' => 'Показывать список для направлений', 'rules' => '', 'type' => 'id'),
			array('field' => 'withDirection', 'label' => 'С электронным направлением', 'rules' => '', 'type' => 'id'),
			array('field' => 'WithoutChildLpuSectionAge', 'label' => 'Скрыть детские отделения', 'rules' => '', 'type' => 'int'),
			array('field' => 'FormName', 'label' => 'Форма, с которой вызывается поиск', 'rules' => '', 'type' => 'string'),
		),
		'mGetPrescrPlanView' => array(
			array('field' => 'Evn_rid', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' =>  'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор учетного документа', 'rules' => 'required', 'type' =>  'id')
		),
		'mGetDrugDataFromDestination' => array(
			array('field' => 'EvnPrescrTreat_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Storage_id', 'label' => 'идентификатор склада', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugPrepFas_id', 'label' => 'идентификатор препарата', 'rules' => '', 'type' => 'id'),
		),
		'mLoadEvnPrescrJournalGrid' => array(
			array('field' => 'EvnPrescr_begDate', 'label' => 'Период: дата начала', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'EvnPrescr_endDate', 'label' => 'Период: дата окончания', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'PrescriptionType_id', 'label' => 'Идентификатор типа назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификато отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_FIO', 'label' => 'ФИО', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор мед. персонала', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionWard_id', 'label' => 'Идинтификатор палаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_IsExec', 'label' => 'Признак выполнения', 'rules' => '', 'type' => 'api_flag_nc'),
			array('field' => 'showEvnQueue','label' => 'Признак только очередь','rules' => '','type' => 'api_flag_nc'),
			// Параметры страничного вывода
			array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
		),
		'mSetEvnPrescrExec' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' =>  'id')
		),
		'mUndoEvnPrescrExec' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' =>  'id')
		)
	);

	protected $prescriptions = array(
		1 => 'EvnPrescrRegime',
		2 => 'EvnPrescrDiet',
		6 => 'EvnPrescrProc',
		13 => 'EvnPrescrConsUsluga',
		12 => 'EvnPrescrFuncDiag',
		11 => 'EvnPrescrLabDiag',
		5 => 'EvnPrescrTreat',
		7 => 'EvnPrescrOper'
	);

	// атрибут для получения списка услуг
	protected $prescr_attribute = array(
		6 => 'manproc',
		13 => 'consult',
		11 => 'lab',
		12 => 'func',
		7 => 'oper'
	);

	// типы длительностей ЛС
	protected $prescr_treat_duration = array(
		1 => 1,
		2 => 7,
		3 => 30
	);


	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnPrescr_model', 'dbmodel');
		$this->load->library('swPrescription');
	}

	/**
	 * получение назначения определенного типа
	 */
	protected function getEvnPresrc($prescrName, $pid, $session){

		$this->load->model($prescrName.'_model');
		$prescr_data = $this->{$prescrName.'_model'}->doLoadViewData('api', $pid, $session);

		// по просьбе гравити
		if ($prescrName === "EvnPrescrTreat" && !empty($prescr_data)) {
			foreach ($prescr_data as $i => $prscr) {
				if (!empty($prscr['DrugListData'])) {

					$newDrugList = array();

					foreach ($prscr['DrugListData'] as $key => $val) {

						$newDrugItem['id'] = $key;
						$newDrugItem = array_merge($newDrugItem,$val);

						$newDrugList[] = $newDrugItem;
					}

					unset($prescr_data[$i]['DrugListData']); // для эстетичности (чтобы лист ушел в конец) :-)
					$prescr_data[$i]['DrugListData'] = $newDrugList;
				}
			}
		}

		// по просьбе гравити
		if ($prescrName === "EvnPrescrProc" && !empty($prescr_data)) {

			foreach ($prescr_data as $key => $prscr) {
				if (empty($prscr['object'])) {
					unset($prescr_data[$key]);
				}
			}

			$prescr_data = array_values($prescr_data);
		}

		if (!empty($prescr_data)) return $prescr_data; else return array();
	}

	/**
	 * Получение значения по умолчанию для полей в лекарственном лечении
	 */
	function mGetDrugPackData_get (){
		$data = $this->ProcessInputData('mGetDrugPackData', null);
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
        if ($data === false) {
            return false;
        }
        $resp = $this->EvnPrescrTreat_model->getDrugPackData($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Показать назначения для случая лечения
	 */
	function mGetEvnPrescr_get(){

		$data = $this->ProcessInputData('getEvnPrescr', null);

		$session = getSessionParams(); $resp = array();
		if (!empty($session) && empty($session['lpu_id'])) $session['lpu_id'] = $session['Lpu_id'];
		if (!empty($session) && empty($session['medpersonal_id'])) $session['medpersonal_id'] = NULL;

		if (!empty($data['EvnPrescrType_id'])) {

			if (isset($this->prescriptions[$data['EvnPrescrType_id']])) {

				$prescr = $this->prescriptions[$data['EvnPrescrType_id']];
				$resp[$prescr] = $this->getEvnPresrc($prescr, $data['Evn_id'], $session);
			}
		} else {

			foreach ($this->prescriptions as $key => $prescr) {
				$resp[$prescr] = $this->getEvnPresrc($prescr, $data['Evn_id'], $session);
			}
		}

		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка профилей
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"LpuSectionProfile_id": "Идентификатор профиля отделения",
				"LpuSectionProfile_Code": "Код профиля отделения",
				"LpuSectionProfile_Name": "Наименование профиля отделения"
	 * 		},
	 * 		"example": {
				"error_code": 0,
				"data": [
					{
						"LpuSectionProfile_id": "20",
						"LpuSectionProfile_Code": "4",
						"LpuSectionProfile_Name": "аллергологии и иммунологии"
					},{
						"LpuSectionProfile_id": "48",
						"LpuSectionProfile_Code": "3",
						"LpuSectionProfile_Name": "акушерскому делу"
					}
				]
			}
	 * }
	 */
	function mGetSectionProfileList_get(){
		$data = $this->ProcessInputData('mGetSectionProfileList', null);
		$this->load->model('Common_model', 'common_model');
		$session = getSessionParams();
		$resp = array();
		$data['session'] = $session['session'];
		$resp = $this->common_model->loadLpuSectionProfileList($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка ЛПУ
	 */
	function mGetDirectionLpuUnitList_get(){

		$data = $this->ProcessInputData('mGetDirectionLpuUnitList', null, true);

		$this->load->helper('Reg_helper');
		$this->load->model("Reg_model", "rmodel");

		if (empty($data['ARMType'])) $data['ARMType'] = 'common';
		if (empty($data['start'])) $data['start'] = '0';
		if (empty($data['limit'])) $data['limit'] = '100';

		$resp = $this->rmodel->getRecordLpuUnitList($data);

		if (!is_array($resp) || !isset($resp['data'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0,'data' => $resp['data']));
	}

	/**
	 * Получение списка врачей
	 */
	function mGetDirectionMedPersonalList_get(){
		$data = $this->ProcessInputData('mGetDirectionMedPersonalList', null);
		$session = getSessionParams();
		$data['session'] = $session['session'];
		$this->load->helper('Reg_helper');
		$this->load->model("Reg_model", "rmodel");

		if (empty($data['ARMType'])) $data['ARMType'] = 'common';
		if (empty($data['start'])) $data['start'] = '0';
		if (empty($data['limit'])) $data['limit'] = '100';
		if (empty($data['Date'])) $data['Date'] = date("Y-m-d");
		$data['fromApi'] = true;

		$resp = $this->rmodel->getRecordMedPersonalList($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$grouped_result = array();

		foreach ($resp as $key => $medpersonal) {
			unset($medpersonal['MedStaffFact_updDT']);
			if (!isset($grouped_result[$medpersonal['LpuSectionProfile_id']]['LpuSectionProfile_id'])) {
				$grouped_result[$medpersonal['LpuSectionProfile_id']]['LpuSectionProfile_id'] = $medpersonal['LpuSectionProfile_id'];
				$grouped_result[$medpersonal['LpuSectionProfile_id']]['LpuSectionProfile_Name'] = $medpersonal['LpuSectionProfile_Name'];
			}

			$grouped_result[$medpersonal['LpuSectionProfile_id']]['MedPersonalList'][] = $medpersonal;
		}

		usort($grouped_result, function ($a, $b) { return strcmp($a["LpuSectionProfile_Name"], $b["LpuSectionProfile_Name"]); });
		$resp = array_values($grouped_result);

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Сохраняем\обновляем лекарственное назначение
	 */
	function mSaveEvnPrescrTreat_post(){

		// инициализация
		$resp = array();

		// модель
		$this->load->model('EvnPrescrTreat_model', 'evn_prescr_model');

		try {

			// если мы обновляем
			if (!empty($this->_args['EvnPrescrTreat_id'])) {

				$params = array('EvnPrescrTreat_id' => $this->_args['EvnPrescrTreat_id']);

				$EvnCourseTreat_id = $this->dbmodel->getFirstResultFromQuery("
					select top 1 EvnCourse_id
					from v_EvnPrescrTreat (nolock)
					where EvnPrescrTreat_id = :EvnPrescrTreat_id
				", $params);

				if (empty($EvnCourseTreat_id)) throw new Exception('Не найден курс лечения по идентификатору назначения', 6);
				$course_data = $this->evn_prescr_model->loadEvnPrescrTreatDataForUpdate($params);

				foreach($course_data as $key => $value) {
					if (!array_key_exists($key, $this->_args)) $this->_args[$key] = $value;
				}

				// обязательность полей не проверяем
				$GLOBALS['isSwanApiKey'] = true;
			}

			// параметры
			$data = $this->ProcessInputData('mSaveEvnPrescrTreat', null, true);
			$evn_data = $this->dbmodel->queryResult("
				select top 1
					E.EvnClass_SysNick,
					E.PersonEvn_id,
					E.Server_id,
					E.Lpu_id,
					isnull(EVPL.MedPersonal_id, ES.MedPersonal_id) as MedPersonal_id,
					isnull(EVPL.LpuSection_id, ES.LpuSection_id) as LpuSection_id
				from
					v_Evn E with(nolock)
					left join v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_id = E.Evn_id
					left join v_EvnSection ES with(nolock) on ES.EvnSection_id = E.Evn_id
				where E.Evn_id = :Evn_id
			", array('Evn_id' => $data['Evn_id']));

			if (empty($evn_data[0])) throw new Exception('Не удалось определить данные родительского события', 6);
			else $data = array_merge($data, $evn_data[0]);

			// ид. изм. дозы
			if (!empty($data['GoodsUnit_id'])) {
				$gd_nick = $this->dbmodel->getFirstResultFromQuery("
					select top 1
						GoodsUnit_Nick
					from v_GoodsUnit with(nolock)
					where GoodsUnit_id = :GoodsUnit_id
				", array(
						'GoodsUnit_id' => $data['GoodsUnit_id']
					)
				);
			}

			// ед. изм. ЛС
			$gd_snick = $this->dbmodel->getFirstResultFromQuery("
				select top 1
					GoodsUnit_Nick
				from v_GoodsUnit with(nolock)
				where GoodsUnit_id = :GoodsUnit_id
			", array(
					'GoodsUnit_id' => $data['GoodsUnit_sid']
				)
			);

			// данные по наименованию МНН и ед. изм.
			$mnn_data = $this->dbmodel->getFirstRowFromQuery("
				select
					DCMD.DrugComplexMnnDose_Mass,
					COALESCE(sz.SIZEUNITS_ID, mass.MASSUNITS_ID, 0) as MnnUnit_id,
					COALESCE(sz.SHORTNAME, mass.SHORTNAME, '') as EdUnits_Nick,
					dcm.DrugComplexMnn_RusName as Drug_Name,
					df.CLSDRUGFORMS_NameLatinSocr as DrugForm_Name,
					CASE
						WHEN sz.SIZEUNITS_ID IS NOT NULL THEN 'SIZEUNITS'
						WHEN mass.MASSUNITS_ID IS NOT NULL THEN 'MASSUNITS'
						ELSE ''
					END as MnnUnit_Prefix
				from
					rls.v_DrugComplexMnn DCM (nolock)
					left join rls.v_DrugComplexMnnDose DCMD (nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
					left join rls.v_CLSDRUGFORMS df (nolock) on df.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
					left join rls.v_SIZEUNITS sz (nolock) on sz.SIZEUNITS_ID = DCMD.SIZEUNITS_ID
					left join rls.v_MASSUNITS mass (nolock) on mass.MASSUNITS_ID = DCMD.MASSUNITS_ID
				where
					DCM.DrugComplexMnn_id = :DrugComplexMnn_id
			", $data);

			// конвертируем параметры - род. событие
			$data["EvnCourseTreat_pid"] = $data['Evn_id'];

			// конвертируем
			$data['Kolvo'] = intval($data['Kolvo']);
			$data['KolvoEd'] = intval($data['KolvoEd']);

			$DrugListData = array(
				array(
					'MethodInputDrug_id' => empty($data['Drug_id']) ? 1 : 2,
					'Drug_Name' => !empty($mnn_data['Drug_Name']) ? $mnn_data['Drug_Name'] : null,
					'Drug_id' => !empty($data['Drug_id']) ? $data['Drug_id'] : null,
					'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
					'DrugForm_Name' => !empty($mnn_data['DrugForm_Name']) ? $mnn_data['DrugForm_Name'] : 'нет',
					'DrugForm_Nick' => $this->evn_prescr_model->getDrugFormNick($mnn_data['DrugForm_Name'], $mnn_data['Drug_Name']),
					'KolvoEd' => $data['KolvoEd'],
					'Kolvo' => $data['Kolvo'],
					'EdUnits_id' => (!empty($mnn_data['MnnUnit_id']) ? $mnn_data['MnnUnit_Prefix'].'_'.$mnn_data['MnnUnit_id'] : null),
					'EdUnits_Nick' => !empty($mnn_data['EdUnits_Nick']) ? $mnn_data['EdUnits_Nick'] : null,
					'GoodsUnit_id' => !empty($data['GoodsUnit_id']) ? $data['GoodsUnit_id'] : null,
					'GoodsUnit_Nick' => !empty($gd_nick) ? $gd_nick : null,
					'GoodsUnit_SNick' => $gd_snick,
					'DrugComplexMnnDose_Mass' => (!empty($mnn_data['DrugComplexMnnDose_Mass']) ? $mnn_data['DrugComplexMnnDose_Mass'] : null),
					'DoseDay' => $data['EvnCourseTreat_CountDay']*(!empty($data[$data['Kolvo']])? $data['Kolvo'] : 1).' '.(!empty($gd_nick) ? $gd_nick : $gd_snick),
					'PrescrDose' => $data['EvnCourseTreat_Duration']*$this->prescr_treat_duration[$data['DurationType_id']].' '.(!empty($gd_nick) ? $gd_nick : $gd_snick),
					'GoodsUnit_sid' => $data['GoodsUnit_sid'],
					'status' => !empty($data['EvnCourseTreat_id']) ? 'updated' : 'new',
					'id' => !empty($data['EvnCourseTreatDrug_id']) ? $data['EvnCourseTreatDrug_id'] : null,
					'FactCount' => 0,
				)
			);

			$params = array(

				// данные по событию
				'EvnCourseTreat_id' => !empty($data['EvnCourseTreat_id']) ? $data['EvnCourseTreat_id'] : null,
				'EvnCourseTreat_pid' => $data['Evn_id'],
				'parentEvnClass_SysNick' => $data['EvnClass_SysNick'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Server_id' => $data['Server_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'Lpu_id' => $data['Lpu_id'],

				// данные по МНН
				'DrugListData' => $DrugListData,
				'PrescriptionIntroType_id' => $data['PrescriptionIntroType_id'],
				'EvnCourseTreat_setDate' => $data['EvnCourseTreat_setDate'],
				'EvnCourseTreat_CountDay' => $data['EvnCourseTreat_CountDay'],
				'EvnCourseTreat_Duration' => $data['EvnCourseTreat_Duration'],
				'DurationType_id' => $data['DurationType_id'],
				'PerformanceType_id' => $data['PerformanceType_id'],

				// доп данные
				'EvnPrescrTreat_IsCito' => $data['EvnPrescrTreat_IsCito'] ? 'on' : 'off',
				'EvnPrescrTreat_Descr' => $data['EvnPrescrTreat_Descr'],

				// уч. запись
				'pmUser_id' => $data['pmUser_id'],
				'session' => $data['session'],

				// совсем ненужные данные
				'signature' => 0,
				'EvnCourseTreat_ContReception' => $data['EvnCourseTreat_ContReception'],
				'EvnCourseTreat_Interval' => $data['EvnCourseTreat_Interval'],
				'DurationType_recid' => $data['DurationType_recid'],
				'DurationType_intid' => $data['DurationType_intid'],
				'PrescriptionTreatType_id' => !empty($data['PrescriptionTreatType_id']) ? $data['PrescriptionTreatType_id'] : null,
				'ResultDesease_id' => !empty($data['ResultDesease_id']) ? $data['ResultDesease_id'] : null,
				'Morbus_id' => !empty($data['Morbus_id']) ? $data['Morbus_id'] : null
			);

			$this->dbmodel->beginTransaction();
			$resp = $this->evn_prescr_model->doSaveEvnCourseTreat($params);

			//echo '<pre>',print_r($resp),'</pre>'; die();

			if (empty($resp[0]["EvnPrescrTreat_id0"])) {

				// ошибка может прийти и внутри массива
				if (!empty($resp[0]['Error_Msg'])) $resp['Error_Msg'] = $resp[0]['Error_Msg'];

				throw new Exception('Во время сохранения назначения произошли ошибки' . (!empty($resp['Error_Msg']) ? ": " . $resp['Error_Msg'] : ""), 6);
			} else {
				$resp = $resp[0];
				$resp['EvnPrescrTreat_id']= $resp["EvnPrescrTreat_id0"];
				unset($resp["EvnPrescrTreat_id0"]);
				if (isset($resp['EvnCourseTreatDrug_id0_saved'])) unset($resp["EvnCourseTreatDrug_id0_saved"]);
			}

		} catch (Exception $e) {
			$this->dbmodel->rollbackTransaction();
			$this->response(
				array(
					'success' => false,
					'error_code' => $e->getCode(),
					'error_msg' => $e->getMessage()
				)
			);
		}

		$this->dbmodel->commitTransaction();
		$this->response(array_merge(array('success' => true, 'error_code' => 0), $resp));
	}

	/**
	 * Сохраняем назначение
	 */
	function mSaveEvnPrescr_post(){

		// инициализация
		$resp = array();

		// параметры
		$data = $this->ProcessInputData('mSaveEvnPrescr', null, true);


		$data['session']['CurArmType'] = "common";
		$data['StudyTarget_id'] = strval($data['StudyTarget_id']); // как строка

		try {

			$periodic = $this->dbmodel->queryResult("
				select top 1
					Person_id,
					PersonEvn_id,
					Server_id
				from v_Evn (nolock)
				where Evn_id = :Evn_id
			", array('Evn_id' => $data['Evn_id']));

			if (empty($periodic[0])) throw new Exception('Не удалось определить периодику пациента', 6);
			else $data = array_merge($data, $periodic[0]);

			// определяем назначение
			$prescr_name = $this->prescriptions[$data['PrescriptionType_id']];

			// определим признак назначения - самостоятельное выполнение или выполнение где-либо
			$is_self_exec = in_array($data['PrescriptionType_id'], array(1,2,5));

			// добавим правила
			if (!$is_self_exec) {

				$required_params = array('UslugaComplex_id', 'MedService_id', 'From_MedStaffFact_id');
				foreach ($required_params as $param) {
					if (empty($data[$param])) throw new Exception('Не указан обязательный параметр ' . $param, 6);
				}
			}

			// конвертируем параметры - род. событие
			$data["{$prescr_name}_pid"] = $data["EvnPrescr_pid"] = $data['Evn_id'];

			// список услуг если есть
			$data["{$prescr_name}_uslugaList"] = !empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null;

			// дата создания
			if (empty($data['EvnPrescr_setDate'])) $data['EvnPrescr_setDate'] = date('Y-m-d', time());

			if (empty($data["{$prescr_name}_setDate"]))	$data["{$prescr_name}_setDate"] = date('Y-m-d', time());
			
			// транзакции, чтобы откатить ежли што неведомое случится
			$this->dbmodel->beginTransaction();

			switch ($prescr_name) {

				case 'EvnPrescrFuncDiag':
					$this->load->model('EvnPrescrFuncDiag_model', 'evn_prescr_model');

					if (!empty($data['composition'])) {
						$data['EvnPrescrFuncDiag_uslugaList'] = implode(',', $data['composition']);
					} else $data['EvnPrescrFuncDiag_uslugaList'] = strval($data['UslugaComplex_id']);

					// смержим входные параметры для сохранения назначения
					$this->inputRules['saveEvnPrescrFuncDiag'] = $this->evn_prescr_model->getInputRules('doSave');
					$data = array_merge($data, $this->ProcessInputData('saveEvnPrescrFuncDiag', $data));

					$data['tt'] = 'TimetableResource';
					$data['TimetableResource_id'] = $data['Timetable_id'];

					// ставим флаг, чтобы подтянуть ресурс во время сохранения направления
					$data['withResource'] = true;
					// тип направления, на исследование
					$data['DirType_id'] = (!empty($data['DirType_id']) ? $data['DirType_id']  : 10 );
					break;

				case 'EvnPrescrLabDiag':

					$this->load->model('EvnPrescrLabDiag_model', 'evn_prescr_model');

					if (!empty($data['composition'])) {
						$data['EvnPrescrLabDiag_uslugaList'] = implode(',', $data['composition']);
					} else $data['EvnPrescrLabDiag_uslugaList'] = strval($data['UslugaComplex_id']);

					// смержим входные параметры для сохранения назначения
					$this->inputRules['saveEvnPrescrLabDiag'] = $this->evn_prescr_model->getInputRules('doSave');
					$data = array_merge($data, $this->ProcessInputData('saveEvnPrescrLabDiag', $data));

					$data['tt'] = 'TimetableMedService';
					$data['TimetableMedService_id'] = $data['Timetable_id'];

					// тип направления, на исследование
					$data['DirType_id'] = (!empty($data['DirType_id']) ? $data['DirType_id']  : 10 );

					// сохраняем лабораторию, чтобы вернуть её после сохранения назначения
					if (!empty($data['pzm_MedService_id'])) {
						$data['stored_lab_id'] = $data['MedService_id'];
						$data['MedService_id'] = $data['pzm_MedService_id'];
					}
					break;

				case 'EvnPrescrConsUsluga':

					$this->load->model('EvnPrescrConsUsluga_model', 'evn_prescr_model');

					// смержим входные параметры для сохранения назначения
					$this->inputRules['saveEvnPrescrConsUsluga'] = $this->evn_prescr_model->getInputRules('doSave');
					$data = array_merge($data, $this->ProcessInputData('saveEvnPrescrConsUsluga', $data));

					$data['tt'] = 'TimetableMedService';
					$data['TimetableMedService_id'] = $data['Timetable_id'];

					// тип направления, на консультацию
					$data['DirType_id'] = (!empty($data['DirType_id']) ? $data['DirType_id']  : 11 );
					break;

				case 'EvnPrescrProc':

					// определяем псведоним
					$alias_name = 'EvnCourseProc';
					$data["{$alias_name}_pid"] = $data['Evn_id'];

					$this->load->model('EvnPrescrProc_model', 'evn_prescr_model');

					$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
					$data['LpuSection_id'] = $this->dbmodel->getFirstResultFromQuery(
						"select top 1 msf.LpuSection_id from v_MedStaffFact msf (nolock) where msf.MedStaffFact_id = :MedStaffFact_id",
						array('MedStaffFact_id' => $data['From_MedStaffFact_id'])
					);

					// смержим входные параметры для сохранения назначения
					$rules = $this->evn_prescr_model->getInputRules('doSaveEvnCourseProc');

					// перетрубации
					$changed = 0;
					foreach ($rules as &$f) {
						if ($f['field'] === 'EvnPrescrProc_IsCito') { $f['type'] = 'int'; $changed++; };
						if ($f['field'] === 'UslugaComplex_id') { $f['type'] = 'int'; $changed++; };

						if ($changed == 2) break;
					}

					$this->inputRules['saveEvnPrescrProc'] = $rules;
					$data = array_merge($data, $this->ProcessInputData('saveEvnPrescrProc', $data));

					$data['tt'] = 'TimetableMedService';
					$data['TimetableMedService_id'] = $data['Timetable_id'];

					// тип направления, процедурный кабинет
					$data['DirType_id'] = (!empty($data['DirType_id']) ? $data['DirType_id']  : 15 );

					// правильная дата
					$data['EvnCourseProc_setDate'] = $data['EvnPrescrProc_setDate'];
					break;

				case 'EvnPrescrRegime':

					$this->load->model('EvnPrescrRegime_model', 'evn_prescr_model');
					break;

				case 'EvnPrescrDiet':

					$this->load->model('EvnPrescrDiet_model', 'evn_prescr_model');
					break;

				default:
					throw new Exception('Метод сохранения указанного назначения находится в разработке', 6);
					break;
			}

			// сохраняем назначение
			if ($prescr_name == 'EvnPrescrProc') {

				// было бы красиво если бы не это
				$resp = $this->evn_prescr_model->doSaveEvnCourseProc($data);
				// ассенизация параметров
				if (!empty($resp[0]["EvnCourseProc_id"])) $resp[0]["{$prescr_name}_id"] = $resp[0]["{$prescr_name}_id0"];

			} else {
				$resp = $this->evn_prescr_model->doSave($data);
			}

			if (!empty($resp[0])) $resp = $resp[0];
			if (!empty($resp["{$prescr_name}_id"]) || !empty($resp["EvnPrescr_id"])) {

				if (!empty($resp["{$prescr_name}_id"])) {
					$EvnPrescr_id = $resp["{$prescr_name}_id"];
				} else  {
					$EvnPrescr_id = $resp["EvnPrescr_id"];
				}

				// сохраняем признак CITO
				if (isset($data['EvnPrescr_IsCito']) && $data['EvnPrescr_IsCito'] === 2){
					$this->load->model('EvnPrescr_model');
					$this->EvnPrescr_model->setCitoEvnPrescr(array(
						'EvnPrescr_id' => $EvnPrescr_id,
						'EvnPrescr_IsCito' => $data['EvnPrescr_IsCito'],
						'pmUser_id' => $data['pmUser_id']
					));
				}

				// если назначение самостоятельного исполнения дальше не идем
				if (!$is_self_exec) {
					// берем данные по службе для направления
					$ms_data = $this->dbmodel->queryResult("
						select top 1
							ms.LpuSection_id as LpuSection_did,
							ms.Lpu_id as Lpu_did,
							ms.LpuUnit_id as LpuUnit_did,
							-- ето нужно для направления
							ls.LpuSectionProfile_id,
							-- ето нужно для постановки в очередь
							ls.LpuSectionProfile_id as LpuSectionProfile_did
						from v_MedService ms (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = ms.LpuSection_id
						where MedService_id = :MedService_id
					", array('MedService_id' => $data['MedService_id']));

					if (empty($ms_data[0])) throw new Exception('Не удалось определить данные по службе', 6);
					else $data = array_merge($data, $ms_data[0]);

					// возвращаем службу лабы для сохранения назначения (если меняли)
					if (!empty($data['stored_lab_id'])) $data['MedService_id'] = $data['stored_lab_id'];

					// сохраним назначение
					$data["EvnPrescr_id"] = $EvnPrescr_id;

					// сохраним код назначения
					// todo: справедливо, если коды аналогичны идентификаторам
					$data["PrescriptionType_Code"] = $data['PrescriptionType_id'];

					// родительское событие
					$data["EvnDirection_pid"] = $data["Evn_id"];

					// дата создания направления
					$data['EvnDirection_setDate'] = date('Y-m-d', time());

					// todo: меняем цель исследования, надо ли??
					$data['StudyTarget_id'] = 1;

					// нужно для постановки в очередь
					$data['LpuUnitType_SysNick'] = 'parka';

					// формируем заказ
					$data['order'] = array(
						'UslugaComplex_id' => $data['UslugaComplex_id'],
						'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
						'MedService_id' => $data['MedService_id'],
						'UslugaComplexMedService_id' => $this->dbmodel->getFirstResultFromQuery(
							"select top 1 UslugaComplexMedService_id from v_UslugaComplexMedService (nolock) where MedService_id = :MedService_id and UslugaComplex_id = :UslugaComplex_id",
							array('MedService_id' => $data['MedService_id'],'UslugaComplex_id' => $data['UslugaComplex_id'])
						),
						'MedService_pzid' => !empty($data['pzm_MedService_id']) ? $data['pzm_MedService_id'] : null,
						// todo: тут подумать откуда будет приходить
						'Usluga_isCito' => 1
					);

					if (!empty($data['composition']) && is_array($data['composition'])) {
						$data['order']['checked'] = json_encode($data['composition']);
					} else {
						$data['order']['checked'] = json_encode(array($data['UslugaComplex_id']));
					}

					// заджейсониваем - переджейсониваем
					$data['order'] = json_encode($data['order']);

					if (!empty($data['Timetable_id'])) {

						$this->load->model('Timetable_model');

						// создаем направление и занимаем бирку
						$resp = $this->Timetable_model->applyForApi($data);

					} else {

						$this->load->model('Queue_model');

						// создаем направление и записываем в очередь
						$resp = $this->Queue_model->addToQueueForApi($data);
					}

					if (!empty($resp[0])) $resp = $resp[0];

					if (empty($resp['Error_Code'])) unset($resp['Error_Code']);
					if (empty($resp['Error_Msg'])) unset($resp['Error_Msg']);

				}

				// явно сохраним, так как resp может перезаписаться
				$resp['EvnPrescr_id'] = $EvnPrescr_id;

			} else throw new Exception('Во время сохранения назначения произошли ошибки'.(!empty($resp['Error_Msg']) ? ": ".$resp['Error_Msg'] : ""), 6);

		} catch (Exception $e) {
			$this->dbmodel->rollbackTransaction();
			$this->response(
				array(
					'success' => false,
					'error_code' => $e->getCode(),
					'error_msg' => $e->getMessage()
				)
			);
		}

		$this->dbmodel->commitTransaction();
		$this->response(array_merge(array('success' => true, 'error_code' => 0), $resp));
	}

	/**
	 * Получаем список услуг для назначения
	 */
	function mGetUslugaComplexList_get(){

		$this->load->model('MedService_model');
		$data = $this->ProcessInputData('mGetUslugaComplexList', null, true);

		$this->load->database();
		$this->load->model("User_model");

		$data['isStac'] = false;
		if (!empty($_SESSION['CurMedStaffFact_id'])) {
			$LpuUnitType_SysNick = $this->User_model->getFirstLpuUnitTypeSysNickByMedStaffFact(
				array('MedStaffFact_id' =>  $_SESSION['CurMedStaffFact_id'])
			);

			if (!empty($LpuUnitType_SysNick)) {
				$data['isStac'] = strpos($LpuUnitType_SysNick, 'stac') !== false;
			}
		}

		$data['userLpuSection_id'] = $data['LpuSection_id'];
		$data['filterByUslugaComplex_id'] = $data['UslugaComplex_id'];
		$data['filterByLpu_id'] = $data['Lpu_id'];
		$data['filterByMedService_id'] = $data['MedService_id'];
		$data['formMode'] = 'ExtJS6';

		// максимум 2000 услуг
		$data['start'] = 0;
		$data['limit'] = 2000;

		$data['uslugaCategoryList'] = '["gost2011"]';
		$data['type'] = $this->prescr_attribute[$data['PrescriptionType_id']];


		if (empty($data['type'])) $data['type'] = "lab";
		$data['allowedUslugaComplexAttributeList'] = '["'.$data['type'].'"]';

		$response = $this->MedService_model->getUslugaComplexSelectListForApi($data);

		if (!empty($response['Error_Msg'])) {
			$this->response(
				array(
					'success' => false,
					'error_code' => (!empty($response['Error_Code']) ? $response['Error_Code'] : 6),
					'error_msg' => $response['Error_Msg']
				)
			);
		}

		if (!empty($response['data'])) $response = $response['data'];

		if (!empty($response)) {

			// настроим фильтр выдачи результатов
			$filter_params = array(
				'UslugaComplex_id',
				'UslugaComplex_Code',
				'UslugaComplex_Name',
				'UslugaComplexMedService_id',
				'Resource_id',
				'Resource_Name',
				'MedService_id',
				'MedService_Name',
				'pzm_MedService_id',
				'pzm_MedService_Name',
				'composition_cnt'
			);

			if (
				$data['PrescriptionType_id'] == 11
				|| $data['PrescriptionType_id'] == 13
				|| $data['PrescriptionType_id'] == 6
			) {
				$filter_params[] = 'TimetableMedService_begTime';
				$filter_params[] = 'TimetableMedService_id';
			}

			if ($data['PrescriptionType_id'] == 12) {
				$filter_params[] = 'TimetableResource_begTime';
				$filter_params[] = 'TimetableResource_id';
			}

			// по просьбе гравити...
			$rename_params = array(
				'TimetableMedService_begTime' => 'Timetable_begTime',
				'TimetableMedService_id' =>  'Timetable_id',
				'TimetableResource_begTime' => 'Timetable_begTime',
				'TimetableResource_id' => 'Timetable_id',
			);

			// группируем по месту оказания
			if (!empty($data['groupByUslugaPlace'])) {

				$grouped_result = array();
				$already_appended = array();
				//echo '<pre>',print_r($response),'</pre>'; die();

				foreach ($response as $key => $usluga) {

					// обнуляем ПЗ для тех услуг на которых первая свободная бирка не в ПЗ
					if (empty($usluga['is_pzm']) && !empty($usluga['pzm_MedService_id'])) {
						$usluga['pzm_MedService_id'] = null;
						$usluga['pzm_MedService_Name'] = null;
					}

					if (!empty($usluga['MedService_id']) && !empty($usluga['pzm_MedService_id'])) {

						$group_key = $usluga['MedService_id'].'_'.$usluga['pzm_MedService_id'];

						if (!isset($grouped_result[$group_key])) {
							$grouped_result[$group_key]['Group_id'] = $group_key;
							$grouped_result[$group_key]['MedService_Name'] = $usluga['MedService_Name'].' / '.$usluga['pzm_MedService_Name'];
						}

						$usluga = $this->filter_response_item($usluga, $filter_params, $rename_params);
						$grouped_result[$group_key]['uslugaList'][] = $usluga;

					} else if (!empty($usluga['MedService_id']) && !empty($usluga['Resource_id'])) {

						$group_key = $usluga['MedService_id'].'_'.$usluga['Resource_id'];

						if (!isset($grouped_result[$group_key])) {
							$grouped_result[$group_key]['Group_id'] = $group_key;
							$grouped_result[$group_key]['MedService_Name'] = $usluga['MedService_Name'].' / '.$usluga['Resource_Name'];
						}

						$usluga = $this->filter_response_item($usluga, $filter_params, $rename_params);
						$grouped_result[$group_key]['uslugaList'][] = $usluga;

					} else if (
						!empty($usluga['MedService_id'])
						&& empty($usluga['Resource_id'])
						&& empty($usluga['pzm_MedService_id'])
					) {

						$group_key = $usluga['MedService_id'];

						if (!isset($grouped_result[$group_key])) {
							$grouped_result[$group_key]['MedService_id'] = $usluga['MedService_id'];
							$grouped_result[$group_key]['MedService_Name'] = $usluga['MedService_Name'];

							$already_appended[$group_key] = array();
						}

						// исключаем дубли для службы
						if (!in_array($usluga['UslugaComplex_id'], $already_appended[$group_key])) {

							$usluga = $this->filter_response_item($usluga, $filter_params, $rename_params);
							$grouped_result[$group_key]['uslugaList'][] = $usluga;

							$already_appended[$group_key][] = $usluga['UslugaComplex_id'];
						}
					}
				}

				// переиндексируем
				$response = array_values($grouped_result);
				unset($already_appended);

			} else {

				foreach ($response as $u => $usluga) {

					// отфильтруем
					$usluga = $this->filter_response_item($usluga, $filter_params,$rename_params);
					$response[$u] = $usluga;
				}

				// группируем по услугам
				//$grouped_result = array();
				//
				//foreach ($response as $key => $usluga) {
				//	if (!isset($grouped_result[$usluga['UslugaComplex_id']]['UslugaComplex_id'])) {
				//		$grouped_result[$usluga['UslugaComplex_id']]['UslugaComplex_id'] = $usluga['UslugaComplex_id'];
				//		$grouped_result[$usluga['UslugaComplex_id']]['UslugaComplex_Name'] = $usluga['UslugaComplex_Name'];
				//		$grouped_result[$usluga['UslugaComplex_id']]['UslugaComplex_Code'] =
				//			!empty($usluga['UslugaComplex_Code']) ? $usluga['UslugaComplex_Code'] : "";
				//	}
				//	$grouped_result[$usluga['UslugaComplex_id']]['uslugaList'][] = $usluga;
				//}
			}
		}

		if (!empty($response['data'])) $response = $response['data'];
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	 * Получаем список мест оказания услуг
	 */
	function mGetMedServiceList_get(){

		$this->load->model('MedService_model');
		$data = $this->ProcessInputData('mGetMedServiceList', null, true);

		// чтобы взять из кэша если есть
		//$data['mode'] = 'all';

		// чтобы взять только активные
		$data['is_Act'] = true;

		$response = $this->MedService_model->loadMedServiceList($data);
		if (!empty($response)) {
			foreach($response as $key => $medservice) {

				$medservice = $this->filter_response_item($medservice,
					array('MedService_id', 'MedService_Name')
				);

				$response[$key] = $medservice;
			}
		}

		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	 * Получаем список мест оказания услуг
	 */
	function mGetUslugaComplexPlacesList_get(){

		$this->load->model('MedService_model');
		$data = $this->ProcessInputData('mGetUslugaComplexPlacesList', null, true);

		$data['userLpuSection_id'] = $data['LpuSection_id'];
		$data['filterByUslugaComplex_id'] = $data['UslugaComplex_id'];
		$data['filterByLpu_id'] = $data['Lpu_id'];
		$data['PrescriptionType_Code'] = $data['PrescriptionType_id'];

		$response = $this->MedService_model->getMedServiceSelectCombo($data);
		if (!empty($response['data'])) $response = $response['data'];

		if (!empty($response)) {
			foreach($response as $key => $medservice) {

				// отфильтруем параметры
				$filter_params = array(
					'UslugaComplex_id',
					'MedService_id',
					'UslugaComplexMedService_id',
					'Lpu_id',
					'Lpu_Nick',
					'MedService_Name',
					'UslugaComplexMedService_key'
				);

				if ($data['PrescriptionType_id'] == 11) {
					$filter_params[] = 'TimetableMedService_begTime';
					$filter_params[] = 'TimetableMedService_id';
				}

				if ($data['PrescriptionType_id'] == 12) {
					$filter_params[] = 'TimetableResource_begTime';
					$filter_params[] = 'TimetableResource_id';
				}

				$medservice = $this->filter_response_item($medservice, $filter_params);
				$response[$key] = $medservice;
			}
		}

		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	 * Отменяем назначение
	 */
	function mCancelEvnPrescr_post(){

		$data = $this->ProcessInputData('mCancelEvnPrescr', null, true);

		// Если не указана причина смены статуса
		if (empty($data['EvnStatusCause_id'])) {
			$data['DirFailType_id'] = 14; // Неверный ввод
		}

		// пытаемся отменить назначение
		$response = $this->dbmodel->cancelEvnPrescr($data);

		if (!empty($response[0]['Error_Code'])) {

			$bad_response = $response[0];
			if (in_array($bad_response['Error_Code'], array(800, 802))) {

				// если вернуло ошибку, что нужно сначало отменить бирку (800, 802)

				if (!empty($bad_response['TimetableResource_id'])) {
					$data['object'] = 'TimetableResource';
					$data['TimetableResource_id'] = $bad_response['TimetableResource_id'];
				}

				if (!empty($bad_response['TimetableMedService_id'])) {
					$data['object'] = 'TimetableMedService';
					$data['TimetableMedService_id'] = $bad_response['TimetableMedService_id'];
				}

				$this->load->model('Timetable_model');
				$post_action = $this->Timetable_model->Clear($data);


			} elseif ($bad_response['Error_Code'] == 801) {

				// если вернуло ошибку, что нужно сначало отменить постановку в очередь (801)
				if (!empty($bad_response['EvnQueue_id'])) $data['EvnQueue_id'] = $bad_response['EvnQueue_id'];

				$this->load->model('Queue_model');
				$post_action = $this->Queue_model->cancelQueueRecord($data);

			}
		}

		// если производились доп. манипуляции, еще раз отменяем назначение
		if (isset($post_action)) {
			if (!empty($post_action['success']))
				$response = $this->dbmodel->cancelEvnPrescr($data);
			else $response = $post_action;
		}

		if (!empty($response[0])) $response = $response[0];

		if (!empty($response['Error_Msg'])) {
			$this->response(
				array(
					'success' => false,
					'error_code' => (!empty($response['Error_Code']) ? $response['Error_Code'] : 6),
					'error_msg' => $response['Error_Msg']
				)
			);
		}

		$this->response(array('success' => true, 'error_code' => 0));
	}

	/**
	 * Получаем список состава услуги
	 */
	function mGetUslugaComplexComposition_get(){

		$data = $this->ProcessInputData('mGetUslugaComplexComposition');
		$this->load->model('MedService_model');

		$data['isExt6'] = false;
		$data['UslugaComplexMedService_pid'] = $data['UslugaComplexMedService_id'];

		// получим состав услуги
		$response = $this->MedService_model->loadCompositionMenu($data);

		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	 * Обновляем назначение, PUT!
	 */
	function mSaveEvnPrescr_put(){

		// инициализация
		$resp = array();

		// параметры
		$data = $this->ProcessInputData('mUpdateEvnPrescr');

		$params = array('EvnPrescr_id' => $data['EvnPrescr_id']);

		try {

			$session = getSessionParams();

			$sql = "
				SELECT TOP 1
					PersonEvn_id,
					Server_id,
					Lpu_id,
					PrescriptionType_id,
					EvnPrescr_pid,
					EvnPrescr_Descr
				FROM v_EvnPrescr
				WHERE EvnPrescr_id = :EvnPrescr_id
			";

			$common_data = $this->dbmodel->queryResult($sql, $params);

			if (empty($common_data[0])) throw new Exception('Не удалось определить назначение', 6);
			else $data = array_merge($common_data[0], $data);

			$data['pmUser_id'] = $session['pmUser_id'];

			// дата обновления
			if (empty($data['EvnPrescr_setDate'])) $data['EvnPrescr_setDate'] = date('Y-m-d', time());

			if ($data['PrescriptionType_id'] == 1) {

				$regime = $this->dbmodel->getFirstRowFromQuery("
					select top 1
						PrescriptionRegimeType_id,
						EvnPrescrRegime_Count
					from v_EvnPrescr ep (nolock)
					inner join v_EvnPrescrRegime epr with (nolock) on epr.EvnPrescrRegime_id = ep.EvnPrescr_id
					where ep.EvnPrescr_id = :EvnPrescr_id
				", $params);

				if (empty($data['PrescriptionRegimeType_id'])) {
					$data['PrescriptionRegimeType_id'] = $regime['PrescriptionRegimeType_id'];
				}

				if (empty($data['EvnPrescr_dayNum'])) {
					$data['EvnPrescr_dayNum'] = $regime['EvnPrescrRegime_Count'];
				}

				$this->load->model('EvnPrescrRegime_model', 'evn_prescr_model');

			} elseif ($data['PrescriptionType_id'] == 2) {

				$diet = $this->dbmodel->getFirstRowFromQuery("
					select top 1
						PrescriptionDietType_id,
						EvnPrescrDiet_Count
					from v_EvnPrescr ep (nolock)
					inner join v_EvnPrescrDiet epd with (nolock) on epd.EvnPrescrDiet_pid = ep.EvnPrescr_id
					where ep.EvnPrescr_id = :EvnPrescr_id
				", $params);

				if (empty($data['PrescriptionDietType_id'])) {
					$data['PrescriptionDietType_id'] = $diet['PrescriptionDietType_id'];
				}

				if (empty($data['EvnPrescr_dayNum'])) {
					$data['EvnPrescr_dayNum'] = $diet['EvnPrescrDiet_Count'];
				}

				$this->load->model('EvnPrescrDiet_model', 'evn_prescr_model');

			} else throw new Exception('Разрешены назначения только с типом "Диета" и "Режим"', 6);

			$resp = $this->evn_prescr_model->doSave($data);

			if (!empty($resp[0]["EvnPrescr_id"])) { $resp = $resp[0]; }  // успешно!
			else throw new Exception((!empty($resp[0]['Error_Msg']) ? ": ".$resp[0]['Error_Msg'] : ""), 6);

		} catch (Exception $e) {
			$this->response(
				array(
					'success' => false,
					'error_code' => $e->getCode(),
					'error_msg' => $e->getMessage()
				)
			);
		}

		$this->response(array_merge(array('success' => true, 'error_code' => 0), $resp));
	}

	/**

	@OA\get(
	path="/api/EvnPrescr/mGetPrescrPlanView",
	tags={"EvnPrescr"},
	summary="Получение данных для формы Лист назначений",

	@OA\Parameter(
	name="Evn_rid",
	in="query",
	description="Идентификатор случая лечения",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Evn_pid",
	in="query",
	description="Идентификатор учетного документа",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Данные",
	type="object",


	@OA\Property(
	property="group_name",
	description="Название группы. regime,diet, consul и тд",
	type="object",


	@OA\Property(
	property="PrescriptionType_id",
	description="Тип назначения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrescriptionType_name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="list",
	description="Данные о группе",
	type="array",

	@OA\Items(
	type="object",


	@OA\Property(
	property="accessType",
	description="Тип доступа",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_key",
	description="Ключ назначения",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_id",
	description="Назначение, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPrescr_pid",
	description="Идентификатор события, породившего назначение",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_rid",
	description="Родительский идентификатор назначения",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_setDate",
	description="Дата назначения",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_setTime",
	description="Время назначения",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsExec",
	description="Назначение, признак выполнения (Да/Нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsHasEvn",
	description="
	 * 1 - если есть код услуги
	 * 2 - если нет",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPrescr_execDT",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsDir",
	description="
	 * 1-елси нет EvnDirection_id
	 * 2-если есть",
	type="boolean",

	)
	,
	@OA\Property(
	property="PrescriptionStatusType_id",
	description="Статус назначения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrescriptionType_id",
	description="Тип назначения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrescriptionType_Code",
	description="Тип назначения, код",
	type="string",

	)
	,
	@OA\Property(
	property="PrescriptionType_Name",
	description="Тип назначения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="cntInPrescriptionTypeGroup",
	description="Количество типов назначений в группе",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsCito",
	description="Назначение, признак срочности (Да/Нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPrescr_cnt",
	description="Число дней указаннного назначения",
	type="string",

	)
	,
	@OA\Property(
	property="IsCito_Code",
	description="Код Обязательно к приёму",
	type="string",

	)
	,
	@OA\Property(
	property="IsCito_Name",
	description="Обязательно к приёму",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_Descr",
	description="Назначение, комментарий",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrRegime_id",
	description="Назначение с типом Режим, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrescriptionRegimeType_id",
	description="Тип режима, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrescriptionRegimeType_Code",
	description="Тип режима, код",
	type="string",

	)
	,
	@OA\Property(
	property="PrescriptionRegimeType_Name",
	description="Тип режима, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrDiet_id",
	description="Назначение с типом Диета, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrescriptionDietType_id",
	description="Тип диеты, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrescriptionDietType_Code",
	description="Тип диеты, код",
	type="string",

	)
	,
	@OA\Property(
	property="PrescriptionDietType_Name",
	description="Тип диеты, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrObserv_id",
	description="Назначение c типом Наблюдение, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="ObservTimeType_id",
	description="Время наблюдения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="ObservTimeType_Name",
	description="Время наблюдения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrObservPos_id",
	description="Параметры назначения для наблюдения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="ObservParamType_Name",
	description="Параметр наблюдения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="cntParam",
	description="Количество параметров",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrTreatDrug_id",
	description="Медикаменты назначения с типом лекарственное лечение, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Drug_id",
	description="Cправочник медикаментов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="DrugComplexMnn_id",
	description="Идентификатор комплексного МНН",
	type="integer",

	)
	,
	@OA\Property(
	property="Drug_Name",
	description="Cправочник медикаментов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="DrugTorg_Name",
	description="Справочник медикаментов: торговые наименования медикаментов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrTreatDrug_KolvoEd",
	description="Медикаменты назначения с типом лекарственное лечение, количество на прием в единицах дозировки",
	type="string",

	)
	,
	@OA\Property(
	property="DrugForm_Name",
	description="Справочник медикаментов: форма выпуска медикаментов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrTreatDrug_Kolvo",
	description="Медикаменты назначения с типом лекарственное лечение, количество на прием в единицах измерения",
	type="string",

	)
	,
	@OA\Property(
	property="EdUnits_Nick",
	description="Краткое наименование единицы массы",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrTreatDrug_DoseDay",
	description="Медикаменты назначения с типом лекарственное лечение, Суточная доза",
	type="string",

	)
	,
	@OA\Property(
	property="FactCntDay",
	description="Количество исполненных приемов на дату",
	type="string",

	)
	,
	@OA\Property(
	property="PrescrCntDay",
	description="Количество назначенных приемов на дату",
	type="string",

	)
	,
	@OA\Property(
	property="cntDrug",
	description="Количество лекартсв",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourseTreatDrug_id",
	description="Медикаменты курса лекарственных средств, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="CourseDrug_Name",
	description="Название лечебного курса",
	type="string",

	)
	,
	@OA\Property(
	property="CourseDrugTorg_Name",
	description="Торговое наименование лекраства",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourseTreatDrug_KolvoEd",
	description="Медикаменты курса лекарственных средств, количество на один прием в единицах дозировки",
	type="string",

	)
	,
	@OA\Property(
	property="CourseDrugForm_Name",
	description="Название лечебного курса",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourseTreatDrug_Kolvo",
	description="Медикаменты курса лекарственных средств, количество на один прием в единицах измерения",
	type="string",

	)
	,
	@OA\Property(
	property="CourseEdUnits_Nick",
	description="Название единицы массы",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourseTreatDrug_MaxDoseDay",
	description="Медикаменты курса лекарственных средств, максимальная дневная доза",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourseTreatDrug_MinDoseDay",
	description="Медикаменты курса лекарственных средств, минимальная дневная доза",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourseTreatDrug_PrescrDose",
	description="Медикаменты курса лекарственных средств, назначенная курсовая доза",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourseTreatDrug_FactDose",
	description="Медикаменты курса лекарственных средств, исполненная курсовая доза",
	type="string",

	)
	,
	@OA\Property(
	property="cntCourseDrug",
	description="Количество лекарственных назначений",
	type="string",

	)
	,
	@OA\Property(
	property="PrescriptionIntroType_Name",
	description="Метод введения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PerformanceType_Name",
	description="Тип исполнения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="CourseUslugaComplex_id",
	description="Идентификатор услуги",
	type="integer",

	)
	,
	@OA\Property(
	property="CourseUslugaComplex_2011id",
	description="Идентификатор услуги",
	type="string",

	)
	,
	@OA\Property(
	property="CourseUslugaComplex_Name",
	description="Название услуги",
	type="string",

	)
	,
	@OA\Property(
	property="CourseUslugaComplex_Code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MedService_id",
	description="Cлужбы, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MedService_Name",
	description="Cлужбы, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnCourse_id",
	description="Курс лечения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="graf_date",
	description="Дата назначения",
	type="string",

	)
	,
	@OA\Property(
	property="MaxCountInDay",
	description="Максимальное количество дней приёма назначения",
	type="string",

	)
	,
	@OA\Property(
	property="MinCountInDay",
	description="Минимальное количество дней приёма назначения",
	type="string",

	)
	,
	@OA\Property(
	property="Duration",
	description="Продолжительность",
	type="string",

	)
	,
	@OA\Property(
	property="ContReception",
	description="Непрерывный прием",
	type="string",

	)
	,
	@OA\Property(
	property="Interval",
	description="Интервал",
	type="string",

	)
	,
	@OA\Property(
	property="PrescrCount",
	description="Количество назначений",
	type="string",

	)
	,
	@OA\Property(
	property="DurationType_Nick",
	description="Тип продолжительности, краткое наименование",
	type="string",

	)
	,
	@OA\Property(
	property="DurationType_IntNick",
	description="Тип продолжительности, краткое наименование",
	type="string",

	)
	,
	@OA\Property(
	property="DurationType_RecNick",
	description="Тип продолжительности, краткое наименование",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_id",
	description="Комплексные услуги, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_2011id",
	description="Комплексные услуги, Категория услуг ГОСТ-2011",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_Code",
	description="Комплексные услуги, код",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_Name",
	description="Комплексные услуги, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="TableUsluga_id",
	description="Идентификатор услуги",
	type="integer",

	)
	,
	@OA\Property(
	property="cntUsluga",
	description="Количество услуг",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_id",
	description="Выписка направлений, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnQueue_id",
	description="Постановка в очередь, Идентификатор постановки в очередь",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDirection_Num",
	description="Выписка направлений, номер направления",
	type="string",

	)
	,
	@OA\Property(
	property="RecTo",
	description="Куда записали",
	type="string",

	)
	,
	@OA\Property(
	property="RecDate",
	description="Дата записи",
	type="string",

	)
	,
	@OA\Property(
	property="timetable",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="timetable_id",
	description="Идентификатор бирки",
	type="integer",

	)
	,
	@OA\Property(
	property="timetable_pid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnitType_SysNick",
	description="тип подразделения ЛПУ, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="DirType_Code",
	description="Справочник назначений направления, код",
	type="string",

	)
	,
	@OA\Property(
	property="EvnXmlDir_id",
	description="Идентификатор произвольного документа",
	type="integer",

	)
	,

	@OA\Property(
	property="isGroupTitle",
	description="Название группы",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_DateInterval",
	description="Временной интервал",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrGroup_Title",
	description="Наименование группы",
	type="string",

	)
	,
	@OA\Property(
	property="DrugDataList",
	description="Информация по лекарствам",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="id",
	description="Идентификатор",
	type="string",

	)
	,
	@OA\Property(
	property="Drug_Name",
	description="Cправочник медикаментов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="DrugTorg_Name",
	description="Справочник медикаментов: торговые наименования медикаментов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="KolvoEd",
	description="Количество на один прием в единицах дозировки",
	type="string",

	)
	,
	@OA\Property(
	property="DrugForm_Nick",
	description="Наименование формы выпуска",
	type="string",

	)
	,
	@OA\Property(
	property="Kolvo",
	description="Количество на один прием в единицах измерения",
	type="string",

	)
	,
	@OA\Property(
	property="EdUnits_Nick",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MaxDoseDay",
	description="Максимальная дневная доза",
	type="string",

	)
	,
	@OA\Property(
	property="MinDoseDay",
	description="Минимальная дневная доза",
	type="string",

	)
	,
	@OA\Property(
	property="PrescrDose",
	description="Назначенная курсовая доза",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="days",
	description="Массив дней",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="id",
	description="Идентификатор дня",
	type="integer",
	),

	@OA\Property(
	property="Day_IsExec",
	description="Признак выполнения",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsHasEvn",
	description="Описание",
	type="boolean",

	)
	,
	@OA\Property(
	property="Day_IsSign",
	description="Подписано ли назначение",
	type="boolean",

	)
	,
	@OA\Property(
	property="date",
	description="Дата",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_id",
	description="Назначение, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPrescrRegime_id",
	description="Назначение с типом Режим, идентификатор",
	type="integer",
	),
	@OA\Property(
	property="EvnPrescrDataList",
	description="Список назначений",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="id",
	description="Идентификтатор",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsCito",
	description="Назначение, признак срочности (Да/Нет)",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsExec",
	description="Назначение, признак выполнения (Да/Нет)",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsHasEvn",
	description="Наличие лекарственного назначения или направления на услугу",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPrescr_Descr",
	description="Назначение, комментарий",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_setDate",
	description="Дата создания назначения",
	type="string",

	)
	,
	@OA\Property(
	property="PrescriptionStatusType_id",
	description="Статус назначения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_id",
	description="Комплексные услуги, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_2011id",
	description="Комплексные услуги, Категория услуг ГОСТ-2011",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_Code",
	description="Комплексные услуги, код",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_Name",
	description="Комплексные услуги, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_id",
	description="Выписка направлений, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="RecTo",
	description="Куда записался",
	type="string",

	)
	,
	@OA\Property(
	property="RecDate",
	description="С какого числа человек находится в очереди",
	type="string",

	)
	,
	@OA\Property(
	property="timetable",
	description="Если есть EvnQueue_id принимает значение 'EvnQueue_id' иначе timetable",
	type="string",

	)
	,
	@OA\Property(
	property="timetable_id",
	description="Идентификатор бирки",
	type="integer",

	)



	)

	)

	)
	,

	)
	)
	)
	)
	)

	)

	)

	)

	)

	)

	)



	)
	 */
	function mGetPrescrPlanView_get() {
		$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
		$data = $this->ProcessInputData('mGetPrescrPlanView', false, true);
		$response = $this->EvnPrescrList_model->mGetPrescrPlanView($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	 * @OA\get(
	path="/api/rish/EvnPrescr/mLoadEvnPrescrJournalGrid",
	tags={"EvnPrescr"},
	summary="Получение списка назначений",

	@OA\Parameter(
	name="EvnPrescr_begDate",
	in="query",
	description="Период: дата начала",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPrescr_endDate",
	in="query",
	description="Период: дата окончания",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="PrescriptionType_id",
	in="query",
	description="Идентификатор типа назначения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Идентификато отделения",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Person_FIO",
	in="query",
	description="ФИО",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="MedPersonal_id",
	in="query",
	description="Идентификатор мед. персонала",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionWard_id",
	in="query",
	description="Идинтификатор палаты",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPrescr_IsExec",
	in="query",
	description="Признак выполнения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="showEvnQueue",
	in="query",
	description="Признак только очередь",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="start",
	in="query",
	description="Номер стартовой записи",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="limit",
	in="query",
	description="Количество записей",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="EvnPrescr_id",
	description="Назначение, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_FIO",
	description="Пациент ФИО",
	type="string",

	)
	,
	@OA\Property(
	property="PrescriptionType_Code",
	description="Тип назначения, код",
	type="string",

	)
	,
	@OA\Property(
	property="PrescriptionType_Name",
	description="Тип назначения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="pmUser_insName",
	description="Врач сделавший назначение",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_Name",
	description="Состав назначения",
	type="string",

	),
	@OA\Property(
	property="EvnPrescr_IsExec",
	description="Признак выполнения назначения",
	type="string",

	),
	@OA\Property(
	property="Person_Birthday",
	description="Дата рождения",
	type="string",

	),
	@OA\Property(
	property="Person_Age",
	description="Возраст",
	type="integer",

	),
	@OA\Property(
	property="EvnSection_id",
	description="Идентификатор движения в отделении",
	type="integer",

	),
	@OA\Property(
	property="LpuSectionWard_Name",
	description="Номер палаты",
	type="string",

	),
	@OA\Property(
	property="EvnDirection_setDate",
	description="Дата направления",
	type="string",

	),
	@OA\Property(
	property="EvnPrescr_planTime",
	description="Плановое время приема",
	type="string",

	)

	)

	)

	)
	)

	)
	*/
	function mLoadEvnPrescrJournalGrid_get() {
		$data = $this->ProcessInputData('mLoadEvnPrescrJournalGrid', false, true);
		$response = $this->dbmodel->mLoadEvnPrescrJournalGrid($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	@OA\get(
			path="/api/rish/EvnPrescr/mGetDrugDataFromDestination",
			tags={"EvnPrescr"},
			summary="Метод получения данных о медикаментах из назначения",

		@OA\Parameter(
			name="EvnPrescrTreat_id",
			in="query",
			description="Идентификатор назначения",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="Storage_id",
			in="query",
			description="идентификатор склада",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="DrugPrepFas_id",
			in="query",
			description="идентификатор препарата",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,

		@OA\Response(
			response="200",
			description="JSON response",
			@OA\JsonContent(
				type="object",

		@OA\Property(
			property="error_code",
			description="код ошибки",
			type="string",

		)
	,				 
		@OA\Property(
			property="data",
			description="Данные",
			type="array",

		@OA\Items(
			type="object",

		@OA\Property(
			property="EvnDrug_id",
			description="Назначение медикаментов, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Drug_id",
			description="Cправочник медикаментов, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Drug_Name",
			description="Cправочник медикаментов, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="DrugPrepFas_id",
			description="идентификатор препарата",
			type="integer",

		)
	,				 
		@OA\Property(
			property="EvnPrescrTreatDrug_id",
			description="Медикаменты назначения с типом лекарственное лечение, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="DrugComplexMnn_id",
			description="идентификатор комплексных МНН",
			type="integer",

		)
	,				 
		@OA\Property(
			property="EvnPrescrTreat_PrescrCount",
			description="Назначение с типом Лекарственное лечение, количество назначенных приемов на дату",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnPrescrTreatDrug_FactCount",
			description="Медикаменты назначения с типом лекарственное лечение, количество исполненных приемов на дату",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnPrescrTreatDrug_DoseDay",
			description="Медикаменты назначения с типом лекарственное лечение, Суточная доза",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnPrescrTreat_Descr",
			description="описание лекарственного назначения ",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnCourseTreatDrug_id",
			description="Медикаменты курса лекарственных средств, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="EvnCourseTreatDrug_FactDose",
			description="Медикаменты курса лекарственных средств, исполненная курсовая доза",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnCourse_id",
			description="Курс лечения, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="EvnDrug_rid",
			description="идентификатор события-потомка",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnDrug_pid",
			description="идентификатор родительского события",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnPrescr_id",
			description="Назначение, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Person_id",
			description="Справочник идентификаторов человека, Идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="PersonEvn_id",
			description="События по человеку, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Server_id",
			description="идентификатор сервера",
			type="integer",

		)
	,				 
		@OA\Property(
			property="EvnDrug_setDate",
			description="дата создания назначения",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnDrug_setTime",
			description="время создания назначения",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnDrug_Kolvo",
			description="Назначение медикаментов, Количество медикаментов",
			type="string",

		)
	,				 
		@OA\Property(
			property="EvnDrug_KolvoEd",
			description="Назначение медикаментов, Количество единиц",
			type="string",

		)
	,				 
		@OA\Property(
			property="ParentEvn_IsStac",
			description="стационарный случай",
			type="boolean",

		)
	,				 
		@OA\Property(
			property="Evn_setDate",
			description="дата события",
			type="string",

		)
	,				 
		@OA\Property(
			property="Lpu_id",
			description="справочник ЛПУ, ЛПУ",
			type="integer",

		)
	,				 
		@OA\Property(
			property="LpuSection_id",
			description="Справочник ЛПУ: отделения, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="LpuSection_Name",
			description="Справочник ЛПУ: отделения, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="MedPersonal_id",
			description="Кэш врачей, идентификатор медицинского работника",
			type="integer",

		)
	,				 
		@OA\Property(
			property="MedPersonal_FIO",
			description="ФИО сотрудника",
			type="string",

		)
	,				 
		@OA\Property(
			property="DocumentUcStr_oid",
			description="Строки документа учета, партия",
			type="string",

		)
	,				 
		@OA\Property(
			property="DocumentUcStr_Ost",
			description="остаток медикамента",
			type="string",

		)
	,				 
		@OA\Property(
			property="Storage_id",
			description="Склад, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Mol_id",
			description="Материально-ответственное лицо, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Mol_Name",
			description="ФИО ответственного лица (МОЛ)",
			type="string",

		)
	,				 
		@OA\Property(
			property="DrugFinance_Name",
			description="Справочник медикаментов: тип финансирования , наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="DocumentUcStr_Name",
			description="наименование партии",
			type="string",

		)
	,				 
		@OA\Property(
			property="DocumentUc_id",
			description="Документ учета, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="DocumentUcStr_Price",
			description="Строки документа учета, стоимость",
			type="string",

		)
	,				 
		@OA\Property(
			property="DocumentUcStr_Sum",
			description="Строки документа учета, сумма",
			type="string",

		)
	,				 
		@OA\Property(
			property="GoodsUnit_id",
			description="Единицы измерения товара, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="GoodsUnit_bid",
			description="базовая единица учета поля количество",
			type="string",

		)
	,				 
		@OA\Property(
			property="GoodsPackCount_bCount",
			description="количество единиц измерения товара в упаковке",
			type="string",

		)

		)

		)

			)
		)

		)
	 */
	function mGetDrugDataFromDestination_get(){
		//Метод получения данных о медикаментах из назначения
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$data = $this->ProcessInputData('mGetDrugDataFromDestination', null, true);
		$data['forAPI'] = true;
		$response = $this->EvnPrescrTreat_model->doLoadEvnDrugGrid($data);
		if(!is_array($response)) {
			$this->response(array('error_code' => 6, 'error_msg' => "Ошибка при получении данных о медикаментах из назначения"));
		}
		// по какой то причине людям мешают поля, которые им не нужны, уберем их
		$result = array_map(function($res){
			unset($res['Drug_Fas'], $res['EvnCourseTreatDrug_FactCount'], $res['WhsDocumentCostItemType_Name']);
			return $res;
		},$response);
		foreach ($result as $key => $value) {
			if(empty($value['EvnDrug_id'])) unset($result[$key]);
		}
		if(count($response)>0 && count($result)==0){
			$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка. Отсутствует назначение медикаментов EvnDrug_id'));
		}
		$this->response(array('error_code' => 0, 'data' => $result));
	}

	/**
	 * @OA\post(
	path="/api/rish/EvnPrescr/mSetEvnPrescrExec",
	tags={"EvnPrescr"},
	summary="Установка признака выполнения для назначения",

	@OA\Parameter(
	name="EvnPrescr_id",
	in="query",
	description="Идентификатор назначения",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Описание",
	type="string",

	),
	@OA\Property(
	property="error_msg",
	description="Сообщение об ошибке",
	type="string",

	)

	)
	)

	)
	*/
	function mSetEvnPrescrExec_post() {

		$this->load->model('EvnPrescr_model');
		$data = $this->ProcessInputData('mSetEvnPrescrExec', false, true);
		$response = $this->EvnPrescr_model->mSetEvnPrescrExec($data);

		if (!empty($response['Error_Msg'])) {
			$this->response(array('error_code' => 6,'error_msg' => $response['Error_Msg']));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\post(
	path="/api/rish/EvnPrescr/mUndoEvnPrescrExec",
	tags={"EvnPrescr"},
	summary="Отмена выполненения назначения",

	@OA\Parameter(
	name="EvnPrescr_id",
	in="query",
	description="Идентификатор назначения",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Сообщение об ошибке",
	type="string",

	)

	)
	)

	)
	 */
	function mUndoEvnPrescrExec_post() {

		$this->load->model('EvnPrescr_model');
		$data = $this->ProcessInputData('mUndoEvnPrescrExec', false, true);

		try {
			$response = $this->EvnPrescr_model->mUndoEvnPrescrExec($data);
			$this->response(array('error_code' => 0));
		} catch (Exception $e) {
			$this->response(array('error_code' => 6,'error_msg' => $e->getMessage()));
		}
	}
}
