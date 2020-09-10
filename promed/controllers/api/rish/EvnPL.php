<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с талонами амбулаторного пациента (ТАП)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnPL extends SwREST_Controller {
	protected  $inputRules = array(
		'mgetEvnPLNumber' => array(
			array('field' => 'year','label' => 'Год','rules' => '','type' => 'int')
		),
		'msaveEmkEvnPL' => array(
			array(
				'field' => 'action',
				'label' => 'Действие',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_vid',
				'label' => 'Направление посещения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareKind_vid',
				'label' => 'Вид мед. помощи',
				'rules' => '',
				'type' => 'id'
			)
		),
		'maddEvnVizitPL' => array(
			array('field' => 'EvnPL_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Рабочее место врача', 'rules' => 'trim|required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => 'required', 'type' => 'id'),
		),
		'mdeleteEvnPL' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор ТАП',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mdeleteEvnVizitPL' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ArmType', 'label' => 'Текущий АРМ', 'rules' => '', 'type' => 'string'),
			array('field' => 'DeleteEvnParent', 'label' => 'Флаг удаления радительского события', 'rules' => '', 'type' => 'int'),
			array('field' => 'ignoreDoc', 'label' => 'Игнорировать прикрепленные документы', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreEvnDrug', 'label' => 'Игнорировать использование медикаментов', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreCheckEvnUslugaChange', 'label' => 'Игнорировать проверку наличия паракл. услуг', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'lastVizitDeleteConfirm', 'label' => 'Игнорировать прдупреждение удаления последнего случая', 'rules' => '', 'type' => 'boolean'),

		),
		'mUpdateEvnVizitPL' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая посещения', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'EvnVizitPL_setDT', 'label' => 'Дата и время посещения', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'VizitClass_id', 'label' => 'Идентификатор вида посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => '', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedStaffFact_sid', 'label' => 'Средний мед. персонал место работы', 'rules' => '', 'type' => 'id', 'checklpu' => true),
			array('field' => 'MedPersonal_sid', 'label' => 'Средний мед. персонал мед. персона', 'rules' => '', 'type' => 'id', 'checklpu' => true),
			array('field' => 'TreatmentClass_id', 'label' => 'Вид обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'ServiceType_id', 'label' => 'Место обслуживания', 'rules' => '', 'type' => 'id'),
			array('field' => 'VizitType_id', 'label' => 'Цель посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_uid', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPL_Time', 'label' => 'Время (мин)', 'rules' => '', 'type' => 'int'),
			array('field' => 'ProfGoal_id', 'label' => 'Цель профосмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'DispClass_id', 'label' => 'В рамках дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDisp_id', 'label' => 'Карта дисп./мед. осмотра', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonDisp_id', 'label' => 'Идентификатор карты дисп. учёта', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Харакатер заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_agid', 'label' => 'Осложнение', 'rules' => '', 'type' => 'id'),
			array('field' => 'RankinScale_id', 'label' => 'Значение по шкале Рэнкина', 'rules' => '', 'type' => 'id'),
			array('field' => 'HomeVisit_id', 'label' => 'Идентификатор посещения на дому', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => '', 'type' => 'id')
		),
		'mUpdateEvnPL' => array(
			array('field' => 'EvnPL_id', 'label' => 'Идентификатор ТАП', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnPL_IsFinish', 'label' => 'Признак законченности случая', 'rules' => '', 'type' => 'int'),
			array('field' => 'ResultClass_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_lid', 'label' => 'Заключительный диагноз', 'rules' => '', 'type' => 'id'),
			array(
				'field' => 'InterruptLeaveType_id',
				'label' => 'Случай прерван',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_UKL',
				'label' => 'УКЛ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DirectType_id',
				'label' => 'Направление',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirectClass_id',
				'label' => 'Куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_concid',
				'label' => 'Закл. внешняя причина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospTrauma_id',
				'label' => 'Вид травмы (внеш. возд)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsUnlaw',
				'label' => 'Противоправная',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsUnport',
				'label' => 'Нетранспортабельность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_fedid',
				'label' => 'Фед. результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_fedid',
				'label' => 'Фед. исход',
				'rules' => '',
				'type' => 'id'
			)
		),
		'mGetUslugaComplexList' => array(
			array(
				'field' => 'UcplDiag_id',
				'label' => 'Фильтр по диагнозу в UslugaComplexPartitionLink',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Фильтр по возрастной группе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SurveyTypeLink_IsLowWeight',
				'label' => 'Фильтр по недоношенности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SurveyType_id',
				'label' => 'Вид осмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'filterByLpuLevel',
				'label' => 'Флаг фильтрации по LpuLevel',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ExaminationPlace_id',
				'label' => 'Место выполнения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrpDispSpec_id',
				'label' => 'Специальность врача диспансеризации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonAge',
				'label' => 'Возраст',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isVizitCode',
				'label' => 'Код посещения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isStomVizitCode',
				'label' => 'Код стоматологического посещения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'dispOnly',
				'label' => 'Только услуги диспансеризации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'nonDispOnly',
				'label' => 'Все услуги, кроме услуг по диспансеризации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EducationInstitutionType_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isEvnPS',
				'label' => 'Из КВС',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'VizitClass_id',
				'label' => 'Вид посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VizitType_id',
				'label' => 'Цель посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentClass_id',
				'label' => 'Вид обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'notFilterByEvnVizitMes',
				'label' => 'Не фильтровать по мэсу из посещения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MesOldVizit_id',
				'label' => 'МЭС посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_id',
				'label' => 'Специальность врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FedMedSpec_id',
				'label' => 'Специальность врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfileByLpuSection_id',
				'label' => 'Идентификатор отделения для фильтрации по профилю',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispFilter',
				'label' => 'Фильтр для диспансеризации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Mes_id',
				'label' => 'Идентификатор МЭС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_pid',
				'label' => 'Родительское событие',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizit_id',
				'label' => 'Посещение для кода услуги',
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
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SurveyTypeLink_id',
				'label' => 'Тип осмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SurveyTypeLink_lid',
				'label' => 'Тип осмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SurveyTypeLink_mid',
				'label' => 'Тип осмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SurveyTypeLink_ComplexSurvey',
				'label' => 'Комплексное исследование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDisp_id',
				'label' => 'Идентификатор карты дд',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDiagPLStom_id',
				'label' => 'Стоматологическое заболевание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор типа диспансеризации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_idList',
				'label' => 'Идентификаторы типов диспансеризации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MesFilter_Enable',
				'label' => 'Флаг фильтрации по МЭС',
				'rules' => '',
				'default' => 0,
				'type' => 'int'
			),
			array(
				'field' => 'MesFilter_Evn_id',
				'label' => 'ID движения или посещения для фильтрации по МЭС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexAttributeTypeList',
				'label' => 'Список допустимых типов атрибутов комплексной услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 'or',
				'field' => 'allowedUslugaComplexAttributeMethod',
				'label' => 'Метод учета допустимых типов атрибутов комплексной услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'allowMorbusVizitOnly',
				'label' => 'Признак "Услуги по заболеванию"',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'allowMorbusVizitCodesGroup88',
				'label' => 'Признак "Услуги по заболеванию" c кодом заканчивающимся на %888 и %889',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'allowDispSomeAdultOnly',
				'label' => 'Признак "Услуги по диспансеризации отдельных групп взрослого населения"',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterByLpuSection',
				'label' => 'Признак "Услуги по диспансеризации отдельных групп взрослого населения"',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreUslugaComplexDate',
				'label' => 'Признак "Игнорировать даты начала и окончания действия услуги"',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Sex_Code',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'allowDispSomeAdultLabOnly',
				'label' => 'Признак "Услуги (обследования) по диспансеризации отдельных групп взрослого населения"',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'allowNonMorbusVizitOnly',
				'label' => 'Признак "Услуги, кроме услуг по заболеванию"',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'allowedUslugaComplexAttributeList',
				'label' => 'Список допустимых типов атрибутов комплексной услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'disallowedUslugaComplexAttributeList',
				'label' => 'Список недопустимых типов атрибутов комплексной услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_uid',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'withoutLpuFilter',
				'label' => 'Флаг загрузки без фильтрации по ЛПУ',
				'rules' => '',
				'default' => '',
				'type' => 'string'
			),
			array(
				'field' => 'isEvnPrescr',
				'label' => 'Флаг загрузки услуг для назначений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuLevel_Code',
				'label' => 'Код профиля',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение места выполнения услуги',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба места выполнения услуги',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Строка поиска',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'uslugaCategoryList',
				'label' => 'Список категорий услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'uslugaComplexCodeList',
				'label' => 'Список кодов услуг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'disallowedUslugaComplexCodeList',
				'label' => 'Список ненужных кодов услуг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_Code',
				'label' => 'Код услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexPartition_CodeList',
				'label' => 'Список кодов категорий услуг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_Date',
				'label' => 'Дата актуальности услуги',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplex_id', // если передан, то фильтруется по UslugaComplex_id, остальные фильтры не учитываются
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_wid', // учитываются и остальные фильтры
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_pid',
				'label' => 'Идентификатор уровня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_2011id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'hasLinkWithGost2011',
				'label' => 'Только связанные с гост2011',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'withoutPackage',
				'label' => 'Флаг загрузки без пакетов услуг',
				'rules' => '',
				'default' => 1,// без пакетов
				'type' => 'int'
			),
			array(
				'field' => 'complexOnly',
				'label' => 'Только комплексные',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'to',
				'label' => 'Код объекта, для которого происходит выборка услуг',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_pid',
				'label' => 'Отделение посещения/движения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'filterByLpuId',
				'label' => 'Фильтр по МО',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PrescriptionType_Code',
				'label' => 'Тип назначения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Analyzer_id',
				'label' => 'Анализатор',
				'rules' => 'trim',
				'type' => 'id'
			),
			array('default' => 0,'field' => 'isOnlyPolka','label' => 'Флаг отображения услуг служб только поликлинических отделений','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'isStac','label' => 'Флаг отображения услуг служб стационарных отделений','rules' => '','type' => 'int'),
			array(
				'field' => 'isPrimaryVizit',
				'label' => 'Первично в году',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isInoter',
				'label' => 'Иногородний',
				'rules' => '',
				'type' => 'checkbox'
			)
		),
		'mLoadEvnPLDiagPanel' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
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
		'mLoadEvnUslugaParPanel' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
		),
		'mLoadEvnStickPanel' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
		),
		'mLoadPersonEvnReceptPanel' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPL_id', 'label' => 'Идентификатор талона амбулаторного пациента', 'rules' => '', 'type' => 'id'),
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
		),
		'mCopyEvnVizitPL' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => 'required', 'type' => 'id')
		)
	);
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnPL_model', 'dbmodel');
	}
	
	/**
	 * Получение номера талона амбулаторного пациента
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function mgetEvnPLNumber_get() {

		//$this->echoParamsInfo($this->inputRules, 'mgetEvnPLNumber');
		//$this->showPathWithParams($this->_args);

		$data = $this->ProcessInputData('mgetEvnPLNumber',null, true);
		if ( $data === false ) { return false; }

		$resp = $this->dbmodel->getEvnPLNumber($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * метод копирования посещения в новый ТАП
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
				"listMorbus": null,
				"listPersonRegister": null,
				"TimetableGraf_id": "Идентификатор бирки",
				"EvnVizitPL_id": "Идентификатор посещения",
				"EvnPL_id": "Идентификатор ТАП",
				"success": "Успешно?",
				"error_code": "Код ошибки",
				"error_msg": "Сообщение ошибки"
	 * 		},
	 * 		"example": {
				"listMorbus": [],
				"listPersonRegister": [],
				"TimetableGraf_id": "171857272",
				"EvnVizitPL_id": "730023881179697",
				"EvnPL_id": "730023881179696",
				"success": true,
				"error_code": null,
				"error_msg": null
	 * 		}
	 * }
	 */
	function mCopyEvnVizitPL_post(){

		$data = $this->ProcessInputData('mCopyEvnVizitPL',null, true);

		$this->load->model('EvnVizitPL_model');
		$EvnVizitPL_data = $this->EvnVizitPL_model->getEvnVizitPLSavedData($data);

		// параметры которые надо скопировать из посещения
		$cloneParams = array(
			'EvnVizitPL_Count',
			'PayType_id',
			'TimetableGraf_id',
			'LpuSectionProfile_id',
			'TreatmentClass_id',
			'ServiceType_id',
			'VizitType_id',
			'Diag_id',
			'DeseaseType_id',
			'UslugaComplex_id',
			'MedicalCareKind_id',
			'Person_id',
			'Server_id',
			'PersonEvn_id',
			'MedPersonal_sid'
		);

		// убрем ненужные параметры
		foreach ($EvnVizitPL_data as $field => $value) {
			if (!in_array($field, $cloneParams)) {
				unset($EvnVizitPL_data[$field]);
			}
		}

		// переопределим параметр "вид мед. помощи"
		if (isset($EvnVizitPL_data['MedicalCareKind_id'])) {
			$EvnVizitPL_data['MedicalCareKind_vid'] = $EvnVizitPL_data['MedicalCareKind_id'];
			unset($EvnVizitPL_data['MedicalCareKind_id']);
		}

		// определим шаблон осмотра
		$this->load->model('EvnXmlBase_model', 'EvnXmlBase_model');
		$EvnXml_id = $this->EvnXmlBase_model->getEvnXmlByEvnVizitPL(['EvnVizitPL_id' => $data['EvnVizitPL_id']]);

		if (!empty($EvnXml_id)) $EvnVizitPL_data['copyEvnXml_id'] = $EvnXml_id;

		// определим данные по врачу
		$this->load->model('MedStaffFact_model', 'MedStaffFact_model');
		$MsfData = $this->MedStaffFact_model->getMedStaffFactandLpuUnitById(['MedStaffFact_id' => $data['MedStaffFact_id']]);
	
		if (!empty($MsfData)) $EvnVizitPL_data = array_merge($EvnVizitPL_data, $MsfData);

		//сольем во входные параметры данные по посещению
		$this->_args = array_merge($this->_args, $EvnVizitPL_data);

		// уберем из входящих параметров
		unset($this->_args['EvnVizitPL_id']);

		// создадим ТАП
		$this->msaveEmkEvnPL_post();
	}
	
	/**
	 * метод создания ТАП
	 */
	function msaveEmkEvnPL_post(){

		$methodName = 'msaveEmkEvnPL';

		$this->load->model('EvnSection_model', 'EvnSection_model');
		
		$default_params = array(
			'allowCreateEmptyEvnDoc' => 2,
			'Server_id' => $_SESSION['server_id'],
			'MedPersonal_id' => $_SESSION['medpersonal_id'],
			'TimetableGraf_id' => NULL,
			'EvnDirection_id' => NULL,
			'EvnDirection_vid' => NULL,
			'EvnPrescr_id' => NULL,
			'isMyOwnRecord' => 'true',
			'EvnPL_IsFinish' => 1,
			'EvnVizitPL_id' => 0,
			'ServiceType_id' => 1,
			'VizitType_id' => $this->dbmodel->getVizitTypeBySysNick('desease'),
			'EvnPL_IsWithoutDirection' => 1,
			'isAutoCreate' => 1,
			'PayType_id' => $this->EvnSection_model->getPayTypeIdBySysNick('oms'),
			'EvnPL_id' => 0,
			'action' => 'addEvnPL',

			// всякая шляпа
			'ignoreMesUslugaCheck' => 1,
			'vizit_kvs_control_check' => 1,
			'ignoreControl59536' => 1,
			'ignoreControl122430' => 1,
			'ignoreEvnDirectionProfile' => 1,
			'ignoreMorbusOnkoDrugCheck' => 1,
			'vizit_intersection_control_check' => 1,
			'ignoreLpuSectionProfileVolume' => 1,
			'ignoreCheckEvnUslugaChange' => 1,
			'ignoreDayProfileDuplicateVizit' => 1
		);

		// подготовим номер для ТАП
		if (!empty($_SESSION) && !empty($_SESSION['lpu_id'])) {
			$plNum = $resp = $this->dbmodel->getEvnPLNumber(array('Lpu_id' => $_SESSION['lpu_id']));
			if (!empty($plNum[0])) $plNum = $plNum[0];
		}

		if (empty($plNum) || !empty($plNum) && empty($plNum['EvnPL_NumCard'])) {
			$this->response(array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => (!empty($plNum['Error_Msg']) ? $plNum['Error_Msg']  : 'EvnPL_NumCard generation error'))
			);
		}

		$default_params['EvnPL_NumCard'] = $plNum['EvnPL_NumCard'];

		// нагребем периодику по умолчанию, если не указана
		if (empty($this->_args['PersonEvn_id'])) {
			if (!empty($this->_args['Person_id'])) {

				$this->load->model('Common_model');
				$personEvnData = $this->Common_model->loadPersonDataForApi(array('Person_id' => $this->_args['Person_id']));

				if (!empty($personEvnData[0])) $personEvnData = $personEvnData[0];
				if (empty($personEvnData) || !empty($personEvnData) && empty($personEvnData['PersonEvn_id'])) {
					$this->response(array(
						'success' => false,
						'error_code' => 6,
						'Error_Msg' => (!empty($personEvnData['Error_Msg']) ? $personEvnData['Error_Msg']  : 'Ошибка при получении периодики пациента'))
					);
				}

				$default_params['PersonEvn_id'] = $personEvnData['PersonEvn_id'];
				$default_params['Server_id'] = $personEvnData['Server_id'];

			} else {
				$this->response(array(
						'success' => false,
						'error_code' => 6,
						'Error_Msg' => 'Не указан Person_id')
				);
			}
		} else {

			// если передан PersonEvn_id, сверим его связку с Server_id из сессии,
			// если он не верен то вернем верную связку

			$pe_data = $this->dbmodel->getPersonEvnById(array('PersonEvn_id' => $this->_args['PersonEvn_id']));

			if (empty($pe_data)) {
				$this->response(array(
						'success' => false,
						'error_code' => 6,
						'Error_Msg' => 'Данной периодики пациента не существует')
				);
			}

			if ($pe_data['Server_id'] !== $_SESSION['server_id']) {
				$default_params['Server_id'] = $pe_data['Server_id'];
			}
		}

		$time = time();
		$timeData = array(
			'EvnVizitPL_setDate' =>  date('Y-m-d', $time),
			'EvnVizitPL_setTime' => date('H:i', $time)
		);

		//$this->showPathWithParams($this->_args);
		$default_params = array_merge($default_params, $timeData);
		// параметры по умолчанию мержатся только в том случае, если на входе аналогичные не указаны
		$this->mergeDefaultParams($default_params);

		$this->load->model('EvnVizitPL_model');
		$this->inputRules[$methodName] = array_merge(
			$this->inputRules[$methodName],
			$this->dbmodel->getInputRules(EvnPL_model::SCENARIO_DO_SAVE),
			$this->EvnVizitPL_model->getInputRules(EvnPL_model::SCENARIO_DO_SAVE)
		);

		// поправим правила для того чтобы можно было принимать нулевые значения
		$this->inputRules[$methodName]['EvnPL_id']['rules'] = "required|zero";

		$data = $this->ProcessInputData($methodName, null, true);
		if ( $data === false ) { return false; }

		$className = get_class($this->dbmodel);
		/**
		 * @var EvnPL_model $instance
		 */
		$instance = new $className();
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;

		switch ($data['action']) {

			case 'addEvnPL':

				// Создание ТАП и посещения
				if (!empty($data['isAutoCreate'])) {
					$data['EvnPL_IsFinish'] = 1;
					$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
				}

				$data['EvnPL_setDate'] = $data['EvnVizitPL_setDate'];
				$instance->applyData($data);
				$data['EvnDirection_vid'] = $data['EvnDirection_id'];

				$instance->setEvnVizitInputData($data);
				$response = $instance->doSave($data);
				break;

			case 'editEvnPL':

				if (isset($data['Lpu_id'])) unset($data['Lpu_id']);
				$instance->applyData($data);

				// надо обслужить направление, которое было выбрано в ТАП, созданный без направления, по аналогии с self::saveEvnPL
				if (!$instance->isNewRecord && !empty($data['EvnDirection_id']) && !empty($instance->evnVizitList)) {

					$first_EvnVizitPL_id = $data['EvnVizitPL_id'];// - это должно быть первое посещение

					if ($first_EvnVizitPL_id && empty($instance->evnVizitList[$first_EvnVizitPL_id]['EvnDirection_id'])) {
						$instance->setEvnVizitInputData(array(
							'session' => $data['session'],
							'scenario' => $data['scenario'],
							'EvnVizitPL_id' => $first_EvnVizitPL_id,
							'EvnDirection_vid' => $data['EvnDirection_id'],
							//параметры для игнорирования проверок
							'ignore_vizit_kvs_control' => 1,
							'ignore_vizit_intersection_control' => 1,
							'ignoreMesUslugaCheck' => 1,
							'ignoreControl59536' => 1,
							'ignoreControl122430' => 1,
							'ignoreEvnDirectionProfile' => 1,
							'ignoreMorbusOnkoDrugCheck' => 1,
							'ignoreLpuSectionProfileVolume' => 1,
						));
					}
				}
				$response = $instance->doSave();
				break;

			case 'closeEvnPL':

				if (isset($data['Lpu_id'])) unset($data['Lpu_id']);
				$response =  $instance->doSave($data);
				break;

			default:
				$response = array(
					'success' => false,
					'error_code' => 6,
					'Error_Msg' => "Действие не определено"
				);
		}
		
		if (!is_array($response)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (!empty($response['Error_Msg'])) {

			$response = array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => $response['Error_Msg']
			);

		} else {
			if (isset($response['Error_Msg'])) unset($response['Error_Msg']);
			$response = array_merge($response, array('success'=> true, 'error_code' => 0));
		}

		$this->response($response);
	}

	/**
	 * метод обновления посещения
	 */
	function mUpdateEvnVizitPL_post(){

		$data = $this->ProcessInputData('mUpdateEvnVizitPL');
		$this->load->model('EvnVizitPL_model');

		// подготовим дату если она есть
		if (!empty($data['EvnVizitPL_setDT'])) {
			$data['setdt'] = $data['EvnVizitPL_setDT'];
			$data['setdate'] = date('Y-m-d', strtotime($data['EvnVizitPL_setDT']));
			$data['settime'] = date('H:i', strtotime($data['EvnVizitPL_setDT']));
		}

		$resp = $this->EvnVizitPL_model->updateEvnVizitPL($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (!empty($resp[0])) $response = $resp[0];
		if (!empty($response['Error_Msg'])) {

			$response = array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => $response['Error_Msg']
			);

		} else {
			if (isset($response['Error_Msg'])) unset($response['Error_Msg']);
			$response = array_merge($response, array('success'=> true, 'error_code' => 0));
		}

		$this->response($response);
	}

	/**
	 * метод создания Посещения
	 */
	function  maddEvnVizitPL_post(){

		$data = $this->ProcessInputData('maddEvnVizitPL',null, true);
		if ( $data === false ) { return false; }

		$this->load->model('EvnVizitPL_model');
		$resp = $this->EvnVizitPL_model->addEvnVizitPL($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		if (!empty($resp[0])) $resp = $resp[0];

		if (!empty($resp['Error_Msg'])) {

			$resp = array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => $resp['Error_Msg']
			);

		} else {
			if (isset($resp['Error_Msg'])) unset($resp['Error_Msg']);

			if (!empty($resp['EvnVizitPL_id'])) $resp = array_merge($resp, array('success'=> true, 'error_code' => 0));
			else $resp = array('success'=> false, 'error_code' => 400);
		}

		$this->response($resp);
	}

	/**
	 * Удаление талона амбулаторного пациента: общая логика
	 */
	function deleteEvnPL($data) {

		$this->load->model('Stick_model');
		$lvn = $this->Stick_model->checkEvnDeleteAbility(array('Evn_id' => $data['EvnPL_id']));

		if (!empty($lvn)) {
			$this->response(array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => "Удаление ТАП невозможно, документ содержит ЛВН "
			));
		}
		$resp = $this->dbmodel->deleteEvnPL($data);
		return $resp;
	}

	/**
	 * Удаление талона амбулаторного пациента
	 */
	function mdeleteEvnPL_post() {

		$data = $this->ProcessInputData('mdeleteEvnPL', null, true);
		if ( $data === false ) return false;

		$resp = $this->deleteEvnPL($data);
		if (!empty($resp[0])) $resp = $resp[0];

		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => $resp['Error_Msg']
			));
		} else $resp['success'] = true;

		$resp = array_merge($resp, array('error_code' => 0));
		$this->response($resp);
	}

	/**
	 * Удаление талона амбулаторного пациента
	 */
	function mdeleteEvnVizitPL_post() {

		$data = $this->ProcessInputData('mdeleteEvnVizitPL', null, true);
		if ( $data === false ) return false;

		if (empty($data['ignoreDoc'])) $data['ignoreDoc'] = true;
		$data['EvnClass_SysNick'] = "EvnVizitPL";

		$resp['success'] = false;
		$this->load->model('Evn_model');

		$this->dbmodel->beginTransaction();

		try {


			$checkResult = $this->Evn_model->doCommonChecksOnDelete($data);
			if (empty($checkResult['Error_Msg'])) {

				// проверяем количество посещений
				$vizitData = $this->Evn_model->getChildEvnCount(
					array(
						'Evn_id' => $data['Evn_id'],
						'parentEvnClass_SysNick' => "EvnPL",
						'childEvnClass_SysNick' => "EvnVizitPL",
					)
				);

				if (!empty($vizitData)) {
					if ($vizitData['evnCount'] == 1 && empty($data['lastVizitDeleteConfirm'])) {

						$resp['warning_msg'] = "Это единственное посещение пациентом. Удалить весь случай АПЛ?";
						$resp['warning_bypass_flag'] = "lastVizitDeleteConfirm"; // признак который нужно прислать чтобы варнинг пропустился

					} else if ($vizitData['evnCount'] == 1 && !empty($data['lastVizitDeleteConfirm'])) {

						$data['EvnPL_id'] = $vizitData['Evn_pid']; // удаляемый родительский ТАП
						$response = $this->deleteEvnPL($data); // удаляем ТАП

						if (!is_array($response)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

						if (empty($response['Error_Msg'])){
							$resp['success'] = true;
							$this->dbmodel->commitTransaction();
						} else $resp['Error_Msg'] = $response['Error_Msg'];

					} else {
						$response = $this->Evn_model->deleteEvn($data);
						if (!is_array($response)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

						if (empty($response['Error_Msg'])){
							$resp['success'] = true;
							$this->dbmodel->commitTransaction();
						} else $resp['Error_Msg'] = $response['Error_Msg'];
					}
				} else {
					$resp['Error_Msg'] = "Не удалось определить общее количество посещений";
				}
			} else {
				$resp['Error_Msg'] = (!empty($checkResult[0])&& !empty($checkResult[0]['Error_Msg']) ? $checkResult[0]['Error_Msg'] : 'Проверка перед удалением не пройдена');
			}

		} catch (Exception $e) {
			$resp['Error_Msg'] = $e->getMessage();
		}

		if (!$resp['success']) {

			$this->dbmodel->rollbackTransaction();

			if (!empty($resp['Error_Msg'])) {
				$resp = array_merge($resp, array(
					'error_code' => 6,
					'Error_Msg' => $resp['Error_Msg']
				));
			}

			if (!empty($resp['warning_msg'])) {
				$resp = array_merge($resp, array(
					'warning_msg' => $resp['warning_msg']
				));
			}

			$this->response($resp);
		}

		$resp = array_merge($resp, array('error_code' => 0));
		$this->response($resp);
	}

	/**
	 * Редакторование данных о ТАП
	 */
	function mUpdateEvnPL_post()
	{
		$data = $this->ProcessInputData('mUpdateEvnPL');
		try {

			if (!empty($data['EvnPL_IsFinish']) && $data['EvnPL_IsFinish'] == 2) {
				// проверяем наличие
				if (empty($data['ResultClass_id'])) throw new Exception('Не заполнено поле ResultClass_id', 6);
				if (empty($data['ResultDeseaseType_fedid'])) throw new Exception('Не заполнено поле ResultDeseaseType_id', 6);
				if (empty($data['Diag_lid'])) throw new Exception('Не заполнено поле Diag_lid', 6);
			}

			$sp = getSessionParams();
			$data['Lpu_id'] = $sp['Lpu_id'];
			$data['session'] = $sp['session'];
			$data['pmUser_id'] = $sp['pmUser_id'];

			$resp = $this->dbmodel->updateEvnPL($data);
			if (!is_array($resp)) throw new Exception(self::HTTP_INTERNAL_SERVER_ERROR, null);
			if (!empty($resp[0]['Error_Msg'])) throw new Exception($resp[0]['Error_Msg'], 6);

		} catch (Exception $e) {

			$this->response(array('error_code' => $e->getCode(),'error_msg' => $e->getMessage()));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * Получение списка услуг для кода посещения
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"UslugaComplex_id": "Идентификатор услуги",
				"UslugaComplex_2011id": "Идентификатор услуги по гост 2011",
				"UslugaComplex_AttributeList": "Список типов атрибутов услуги",
				"UslugaCategory_id": "Идентификатор категории услуги",
				"UslugaCategory_Name": "Категория услуги",
				"UslugaCategory_SysNick": "Системное имя категории услуги",
				"UslugaComplex_pid": "Идентификатор родительской услуги",
				"UslugaComplexLevel_id": "Идентификатор уровня услуги",
				"UslugaComplex_begDT": "Дата начала услуги",
				"UslugaComplex_endDT": "Дата окончания услуги",
				"UslugaComplex_Code": "Код услуги",
				"UslugaComplex_Name": "Наименование услуги",
				"UslugaComplex_UET": "УЕТ",
				"FedUslugaComplex_id": "Федеральный идентификатор услуги",
				"LpuSection_Name": "Отделение"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"UslugaComplex_id": "3003408",
					"UslugaComplex_2011id": "3003408",
					"UslugaComplex_AttributeList": "vizit,consult,registry,nutver",
					"UslugaCategory_id": "4",
					"UslugaCategory_Name": "ГОСТ",
					"UslugaCategory_SysNick": "gost2011",
					"UslugaComplex_pid": "206567",
					"UslugaComplexLevel_id": "8",
					"UslugaComplex_begDT": "01.01.2018",
					"UslugaComplex_endDT": null,
					"UslugaComplex_Code": "B01.026.001.001",
					"UslugaComplex_Name": "Прием (осмотр, консультация) врача общей практики (семейного врача)",
					"UslugaComplex_UET": 0,
					"FedUslugaComplex_id": null,
					"LpuSection_Name": ""
	 * 			}
	 * 		}
	 * }
	 */
	function mGetUslugaComplexList_get()
	{
		$data = $this->ProcessInputData('mGetUslugaComplexList', null, true);
		if ( $data === false ) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		// Костыль для стандартизации
		$data['filterByLpu_id'] = (!empty($data['filterByLpuId']))?$data['filterByLpuId']:null;
		$this->load->helper('Options');
		$this->load->library('sql/LoadUslugaComplexListRequest');

		$response = array();

		try {

			if (empty($data['uslugaCategoryList'])) $data['uslugaCategoryList'] = '["gost2011"]';
			if (!empty($data['UslugaComplexAttributeTypeList'])) {

				// определим типы аттрибутов
				$this->load->model('UslugaComplex_model', 'UslugaComplex_model');
				$attributes = $this->UslugaComplex_model->getUslugaComplexAttributeTypeSysNickById(['UslugaComplexAttributeType_id' => $data['UslugaComplexAttributeTypeList']]);

				if (!empty($attributes)) {
					$attributes = array_column($attributes, 'UslugaComplexAttributeType_SysNick');
					$data['allowedUslugaComplexAttributeList'] = '['.implode(',', $attributes).']';
				} else {
					throw new Exception("Не найдены типы атрибутов");
				}
			}

			//вывод списка пакетов и комплексных услуг
			$useCase = (empty($data['withoutPackage']) ? 'with_package' : 'mix');

			$this->loaduslugacomplexlistrequest->applyData($useCase, $data, $this->dbmodel, getOptions());
			$result = $this->loaduslugacomplexlistrequest->execute();

			foreach ($result as $item) {
				array_walk_recursive($item, 'ConvertFromWin1251ToUTF8');
				$response[] = $item;
			}

		} catch (Exception $e) {
			$this->response(array('error_code' => 6,'error_msg' => $e->getMessage()));
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Загрузка диагнозов ТАП для ЭМК
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"Diag_dName": "Основной диагноз направившего учреждения",
				"Diag_fName": "Предварительный диагноз",
				"Diag_preName": "Предварительная внешняя причина",
				"Diag_Name": "Код и наименование диагноза",
				"Diag_lName": "Заключительный диагноз",
				"Diag_concName": "Заключительная внешняя причина",
				"DiagSop": [
	  				{
	 					"EvnDiagPLSop_id": "Идентификатор сопутствующего диагноза",
						"Diag_Name": "Код и наименование сопутствующего диагноза"
	 				}
				],
				"Diag": [
					{
						"EvnVizitPL_id": "Идентификатор посещения",
						"EvnVizitPL_setDate": "Дата посещения",
						"Person_Fin": "ФИО врача",
						"Lpu_Nick": "Наименование ЛПУ",
						"Diag_Name": "Код и наименование диагноза"
					}
				]
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"Diag_dName": null,
					"Diag_fName": null,
					"Diag_preName": null,
					"Diag_Name": "Z30.2 Стерилизация",
					"Diag_lName": null,
					"Diag_concName": null,
					"DiagSop": [],
					"Diag": [
						{
							"EvnVizitPL_id": "730023881153142",
							"EvnVizitPL_setDate": "28.10.2018",
							"Person_Fin": "ВРАЧОВ ДП",
							"Lpu_Nick": "ПЕРМЬ ККБ",
							"Diag_Name": "Z30.2 Стерилизация"
						}
					]
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadEvnPLDiagPanel_get()
	{
		$data = $this->ProcessInputData('mLoadEvnPLDiagPanel', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$response = $this->dbmodel->loadEvnPLDiagPanel($data);
		$isNullResponse = true;

		// stupid проверка
		foreach ($response as $item) {
			foreach ($item as $field) {
				if ($field != NULL) {
					$isNullResponse = false;
					break;
				}
			}
		}

		if ($isNullResponse) $response = array();
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение списка исследований в ЭМК
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"EvnUslugaPar_id": "Идентификатор события параклинической услуги",
				"EvnUslugaPar_rid": "Идентификатор род. события параклинической услуги",
				"EvnUslugaPar_setDate": "Дата создания",
				"UslugaComplex_Name": "Наименование услуги",
				"Lpu_Name": "Наименование ЛПУ",
				"MedService_Name": "Наименование службы",
				"EvnXml_id": "Идентификатор события протокола"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"EvnUslugaPar_id": "73002388172383",
					"EvnUslugaPar_rid": "73002388172383",
					"EvnUslugaPar_setDate": "20.10.2014",
					"UslugaComplex_Name": "Общий (клинический) анализ крови",
					"Lpu_Name": null,
					"MedService_Name": "служба 2",
					"EvnXml_id": "8877"
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadEvnUslugaParPanel_get()
	{
		$data = $this->ProcessInputData('mLoadEvnUslugaParPanel', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$data['EvnUslugaPar_rid'] = !empty($data['EvnPL_id']) ? $data['EvnPL_id'] : null ;

		if (empty($data['Person_id']) && empty($data['EvnPL_id'])) {
			$this->response(array('error_code' => 6,'error_msg' => "Не указан индентификатор ТАП или идентификатор человека"));
		}

		$this->load->model('EvnUslugaPar_model', 'EvnUslugaPar_model');
		$response = $this->EvnUslugaPar_model->loadEvnUslugaParPanel($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение списка ЛВН в ЭМК
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"accessType": "тип доступа",
				"delAccessType": null,
				"EvnStick_IsPaid": null,
				"EvnStick_IsInReg": "Случай в реестре",
				"EvnStick_id": "Идентификатор ЛВН",
				"EvnStick_IsSigned": "Признак подписания",
				"Person_id": "Идентификатор человека",
				"Server_id": "Идентификатор сервера",
				"PersonEvn_id": "Идентификатор события человека",
				"EvnStick_mid": null,
				"EvnStick_pid": "Идентификатор события родителя",
				"evnStickType": null,
				"EvnStick_setDate": "Дата создания",
				"EvnStick_disDate": "Дата закрытия",
				"EvnStick_Num": "Номер ЛВН",
				"StickWorkType_Name": "Тип работы для которого выписывается б/л, наименование",
				"StickOrder_Name": "порядок выдачи б/л, наименование",
				"StickLeaveType_Name": "исход ЛВН, наименование",
				"EvnStick_rid": "Идентификатор события родителя",
				"CardType": "Тип документа по которому выписан ЛВН",
				"NumCard": "Номер документа по которому выписан ЛВН",
				"Evn_pid": "Идентификатор события родителя",
				"ISELN": null,
				"isStickForCopy": null
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"accessType": "edit",
					"delAccessType": "view",
					"EvnStick_IsPaid": "1",
					"EvnStick_IsInReg": "1",
					"EvnStick_id": "730023881155261",
					"EvnStick_IsSigned": null,
					"Person_id": "2272564",
					"Server_id": "150185",
					"PersonEvn_id": "87059843",
					"EvnStick_mid": "730023881155257",
					"EvnStick_pid": "730023881155257",
					"evnStickType": 1,
					"EvnStick_setDate": "04.11.2018",
					"EvnStick_disDate": null,
					"EvnStick_Num": null,
					"StickWorkType_Name": "основная работа",
					"StickOrder_Name": "первичный ЛВН",
					"StickLeaveType_Name": null,
					"EvnStick_rid": "730023881155257",
					"CardType": "ТАП",
					"NumCard": "10199",
					"Evn_pid": "730023881155257",
					"ISELN": null,
					"isStickForCopy": 1
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadEvnStickPanel_get()
	{
		$data = $this->ProcessInputData('mLoadEvnStickPanel', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		
		$this->load->model('Stick_model', 'Stick_model');
		
		if (empty($data['Person_id']) && !empty($data['EvnPL_id'])) {
			$data['Person_id'] = $this->Stick_model->getPersonByEvnStickPid(['Evn_id' => $data['EvnPL_id']]);
		}

		$data['EvnStick_pid'] = $data['EvnPL_id'];
		$data['Org_id'] = $data['session']['org_id'];
		
		$response = $this->Stick_model->loadEvnStickPanel($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Загрузка списка рецептов пациента для ЭМК
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"EvnRecept_id": "Идентификатор рецепта",
				"EvnRecept_rid": "Идентификатор события родителя",
				"isGeneral": "тип рецепта (обычный, льготный)",
				"Person_id": "Идентификатор персоны",
				"Server_id": "Идентификатор сервера",
				"PersonEvn_id": "Идентификатор события персоны",
				"EvnRecept_Ser": "Серия рецепта",
				"EvnRecept_Num": "Номер рецепта",
				"Drug_id": "Идентификатор лекарственного средства",
				"Drug_rlsid": "Идентификатор РЛС",
				"DrugComplexMnn_id": "Идентификатор комплексного МНН",
				"Drug_Name": "Наименование лекарственного средства",
				"EvnRecept_setDate": "Дата создания",
				"EvnRecept_IsSigned": "Документ подписан",
				"EvnRecept_Kolvo": "Количество"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"EvnRecept_id": "730023881155879",
					"EvnRecept_rid": "730023881155257",
					"isGeneral": "privilege",
					"Person_id": "2272564",
					"Server_id": "150185",
					"PersonEvn_id": "87059843",
					"EvnRecept_Ser": "57000",
					"EvnRecept_Num": "0025",
					"Drug_id": null,
					"Drug_rlsid": "96612",
					"DrugComplexMnn_id": "602985",
					"Drug_Name": "Цефат®, пор. д/р-ра для в/в введ., 0.5 г, №10 (10), фл., в пач. картон., РУ Р N001515/01-2002 с 10.06.2008 Рег.: Синтез ОАО(Россия), Пр.: Синтез ОАО (Россия)",
					"EvnRecept_setDate": "07.11.2018",
					"EvnRecept_IsSigned": null,
					"EvnRecept_Kolvo": 1
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadPersonEvnReceptPanel_get()
	{
		$data = $this->ProcessInputData('mLoadPersonEvnReceptPanel', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (empty($data['Person_id']) && !empty($data['EvnPL_id'])) {
			$this->load->model('Evn_model', 'Evn_model');
			$data['Person_id'] = $this->Evn_model->getEvnPersonByEvnPLId(['Evn_id' => $data['EvnPL_id']]);
		}

		$data['EvnRecept_pid'] = $data['EvnPL_id'];
		$data['EvnReceptGeneral_rid'] = $data['EvnPL_id'];

		$this->load->model('Dlo_EvnRecept_model', 'Dlo_EvnRecept_model');
		$response = $this->Dlo_EvnRecept_model->loadPersonEvnReceptPanel($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}
}