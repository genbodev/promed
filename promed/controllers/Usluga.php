<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Usluga - контроллер для работы с услугами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      15.12.2011
 *
 * @property Usluga_model dbmodel
 * @property LoadUslugaComplexListRequest loaduslugacomplexlistrequest
 */
 
class Usluga extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('Usluga_model', 'dbmodel');
		
		$this->inputRules = array(
			'checkUslugaComplexIsMes' => array(
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Mes_id', 'label' => 'Идентификатор МЭС', 'rules' => 'required', 'type' => 'id')
			),
			'loadUslugaComplexTariffList' => array(
				array('field' => 'IsForGrid', 'label' => 'Признак загрузки данных в грид', 'rules' => '', 'type' => 'int'),
				array('field' => 'UEDAboveZero', 'label' => 'УЕТ больше 0', 'rules' => '', 'type' => 'int'),
				array('field' => 'IsSmp', 'label' => 'СМП', 'rules' => '', 'type' => 'int'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplexTariff_Date', 'label' => 'Дата актуальности тарифа', 'rules' => '', 'type' => 'date'),
				array('field' => 'UslugaComplexTariff_id', 'label' => 'Идентификатор тарифа', 'rules' => '', 'type' => 'id')
			),
			'loadUslugaComplexTariffLloList' => array(
				array('field' => 'Year', 'label' => 'Год', 'rules' => '', 'type' => 'int'),
				array('field' => 'UslugaComplexTariff_Date', 'label' => 'Дата актуальности тарифа', 'rules' => '', 'type' => 'date')
			),
			'deleteUslugaPriceList' => array(
				array(
					'field' => 'UslugaPriceList_id',
					'label' => 'Идентификатор позиции прайс-листа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadUslugaPriceListEditForm' => array(
				array(
					'field' => 'UslugaPriceList_id',
					'label' => 'Идентификатор позиции прайс-листа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadUslugaPriceListGrid' => array(
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Лимит записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveUslugaPriceList' => array(
				array(
					'field' => 'Usluga_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaPriceList_id',
					'label' => 'Идентификатор позиции прайс-листа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaPriceList_UET',
					'label' => 'УЕТ',
					'rules' => 'required',
					'type' => 'float'
				)
			),
			'loadUslugaComplexEdit' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услугу',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_pid',
					'label' => 'Идентификатор подчененной услуги',
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
					'field' => 'LpuUnit_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Группа подразделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Здание',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Urgency_id',
					'label' => 'Актуальность',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadUslugaComplex' => array(
				array(
					'default' => null,
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'UslugaComplex_pid',
					'label' => 'Идентификатор уровня',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'level',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'check',
					'label' => 'Дерево с чеками',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Группа подразделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Здание',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Urgency_id',
					'label' => 'Актуальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => null,
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadUslugaComplexTree' => array(
				array(
					'default' => -1,
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'check',
					'label' => 'Дерево с чеками',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'UslugaComplex_pid',
					'label' => 'Идентификатор уровня',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'level',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Группа подразделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Здание',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Urgency_id',
					'label' => 'Актуальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadUslugaComplexList' => array(
				array(
					'field' => 'uslugaCategoryList',
					'label' => 'Список категорий услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'allowedUslugaComplexAttributeList',
					'label' => 'Список допустимых типов атрибутов категорий услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => null,
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => null,
					'field' => 'UslugaComplex_pid',
					'label' => 'Идентификатор уровня',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaGost_Code',
					'label' => 'Код услуги ГОСТ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'level',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Ресурс',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'filter_by_exists',
					'label' => 'Отображать только те услуги, которые присутствуют в заведенных заявках',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_prescid',
					'label' => 'Назначенная услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'complexOnly',
					'label' => 'Только комплексные',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'medServiceComplexOnly',
					'label' => 'Только комплексные услуги службы',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'linkedMesServiceOnly',
					'label' => 'Только услуги со связанных служб',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'hasActiveTargets',
					'label' => 'Только если есть незакрытые исследования на активных анализаторах',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadNewUslugaComplexList' => array(
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
					'field' => 'LpuSectionCode_id',
					'label' => 'Код отделения',
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
					'field' => 'allowedUslugaComplexAttributeList',
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
					'field' => 'filterByLpu_id',
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
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Группа подразделений',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getUslugaComplexPartition' => array(
				array(
					'field' => 'Sex_id',
					'label' => 'Идентификатор пола',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedSpecOms_id',
					'label' => 'Идентификатор спец. ОМС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Идентификатор типа оплаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'IsMes',
					'label' => 'Флаг МЕС',
					'rules' => '',
					'type' => 'checkbox'
				),
			),
			'loadKsgEkbList' => array(
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnSection_setDate',
					'label' => 'Дата начала движения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnSection_disDate',
					'label' => 'Дата окончания движения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
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
					'field' => 'EvnPS_id',
					'label' => 'КВС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DiagFilter',
					'label' => 'Фильр по диагнозу',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DiagGroupFilter',
					'label' => 'Фильтр по группе диагнозов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplexFilter',
					'label' => 'Фильтр по услугам',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonAgeGroupFilter',
					'label' => 'Фильтр по возрастной группе',
					'rules' => '',
					'type' => 'int'
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
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор карты дд',
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
					'field' => 'allowedUslugaComplexAttributeList',
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
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
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
					'field' => 'UslugaComplex_id',
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
				)
			),
			'loadUslugaGostList' => array(
				array(
					'field' => 'UslugaGost_id',
					'label' => 'Код услуги ГОСТ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaGost_pid',
					'label' => 'Уровень подчиненности услуги ГОСТ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaLevel_id',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaGost_Code',
					'label' => 'Код услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaGost_Name',
					'label' => 'Наименование услуги',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Запрос',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveUslugaComplexTariffLlo'  => array(
				array('field' => 'UslugaComplexTariff_id', 'label' => 'Идентификатор тарифа', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplexTariff_Tariff', 'label' => 'Тариф', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaComplexTariff_begDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date'),
				array('field' => 'UslugaComplexTariff_endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date')
			),
			'deleteUslugaComplexTariffLlo'  => array(
				array('field' => 'id', 'label' => 'Идентификатор тарифа', 'rules' => 'required', 'type' => 'id')
			),
			'loadUslugaComplexTariffLlo'  => array(
				array('field' => 'UslugaComplexTariff_id', 'label' => 'Идентификатор тарифа', 'rules' => 'required', 'type' => 'id')
			),
			'loadUslugaComplexMethodsIFA' => array(
				array('field' => 'MedService_id', 'label' => 'Лаборатория', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'int')
			),
			'loadUslugaComplexCombo' => array(
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaCategory_Code', 'label' => 'Код категории услуги', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
			),
            'getUslugaAtributTypeAndGost' => array(
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
			)
		);
	}


	/**
	*  Удаление позиции из прайс-листа
	*  Входящие данные: $_POST['UslugaPriceList_id']
	*  На выходе: JSON-строка
	*  Используется: форма просмотра прайс-листа по услугам
	*/
	function deleteUslugaPriceList() {
		$data = $this->ProcessInputData('deleteUslugaPriceList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteUslugaPriceList($data);
		$this->ProcessModelSave($response, true, 'При удалении позиции прайс-листа возникли ошибки')->ReturnData();
		
		return true;
	}


	/**
	*  Получение прайс-листа по услугам
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма просмотра прайс-листа по услугам
	*/
	function loadUslugaPriceListGrid() {
		$data = $this->ProcessInputData('loadUslugaPriceListGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaPriceListGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	*  Получение данных для формы редактирования позиции прайс-листа для услуг
	*  Входящие данные: $_POST['UslugaPriceList_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования позиции прайс-листа для услуг
	*/
	function loadUslugaPriceListEditForm() {
		$data = $this->ProcessInputData('loadUslugaPriceListEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaPriceListEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Сохранение позиции прайс-листа для услуг
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования позиции прайс-листа для услуг
	*/
	function saveUslugaPriceList() {
		$data = $this->ProcessInputData('saveUslugaPriceList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveUslugaPriceList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении позиции прайс-листа для услуг')->ReturnData();

		return true;
	}
	
	/* Методы работы с комплексными услугами */
	
	/**
	 * Формирование элементов дерева из записей таблицы
	 */
	function getTreeNodes($nodes, $field, $level, $dop="", $check=0)
	{
		$val = array();
		$i = 0;
		if ( $nodes != false && count($nodes) > 0 )
		{
			foreach ($nodes as $rows)
			{
				if (isset($rows['ChildrensCount'])) {
					$field['leaf'] = ($rows['ChildrensCount'] == 0) ? true : false;
				}
				$node = array(
					'text' => trim($rows[$field['name']].(!empty($rows[$field['comment']]) ? ' ('.$rows[$field['comment']].')' : '')),
					//'id' => $field['object'].'_'.$level.'_'.$dop.'_'.$rows[$field['id']],
					'id' => $rows[$field['id']],
					'UslugaComplexMedService_id' => (empty($rows['UslugaComplexMedService_id']))?null:$rows['UslugaComplexMedService_id'],
					'object' => (!isset($rows['object']))?$field['object']:$rows['object'],
					'object_id' => $field['id'],
					'object_value' => $rows[$field['id']],
					'object_code' => $rows[$field['code']],
					'leaf' => $rows['leaf'],
					'iconCls' => (!isset($rows['iconCls']))?$field['iconCls']:$rows['iconCls'],
					'cls' => $field['cls']
					);
				if ($check==1)
				{
					$node['uiProvider'] = $field['uiProvider'];
					$node['checked'] = $field['checked'];
				}
				/*
				$val[] = array_merge($obj);
				*/
				$val[] = $node;
			}
			
		}
		return $val;
	}
	
	/**
	 * Функция читает ветку дерева услуг
	 */
	function loadUslugaComplexTree() {
		$data = $this->ProcessInputData('loadUslugaComplexTree', true);
		if ( $data === false ) { return false; }
		
		if(empty($data['MedService_id']) && empty($data['UslugaComplexMedService_id'])) {
			$response = $this->dbmodel->loadUslugaComplexTree($data);
		} else {
			$response = $this->dbmodel->loadUslugaComplexMedServiceTree($data);
		}
		$this->ProcessModelList($response, true, true);
		// Обработка для дерева 
		$field = Array(
			'object' => "UslugaComplex",
			'id' => "UslugaComplex_id", 
			'name' => "UslugaComplex_Name", 
			'comment' => "LpuSection_Name", 
			'code' => "UslugaComplex_Code", 
			'iconCls' => 'uslugacomplex-16', 
			'uiProvider' => 'tristate',
			'checked' => false,
			'leaf' => false, 
			'cls' => "folder"
		);
		$this->ReturnData($this->getTreeNodes($this->OutData, $field, $data['level'], "", $data['check']));
		return true;
	}
	
	/**
	 * Функция читает ветку дерева услуг
	 */
	function loadUslugaComplexList() {
		$data = $this->ProcessInputData('loadUslugaComplexList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Возращает набор услуг определенной службы для отображения в комбобоксе
	 * @return bool
	 */
	function loadUslugaComplexMedServiceList() {
		$data = $this->ProcessInputData('loadUslugaComplexList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexMedServiceList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Функция загрузки списка услуг для нового комбо услуг
	 */
	function loadNewUslugaComplexList() {
		$data = $this->ProcessInputData('loadNewUslugaComplexList', true);
		if ( $data === false ) { return false; }
		$this->load->helper('Options');
		$this->load->library('sql/LoadUslugaComplexListRequest');
		try {
			/*
			 * Есть множество вариантов загрузки списка услуг
			 * (возможно надо ввести параметр useCase?)
			 * И каждый вариант имеет свои базовые параметры и
			 * допускает использование тех или иных фильтров
			 */
			if (empty($data['withoutPackage'])) {
				//вывод списка пакетов и комплексных услуг
				$useCase = 'with_package';
			} else {
				/*
				 * реализация разных вариантов загрузки комплексных услуг без пакетов,
				 * которая была перенесена так, как тут было,
				 * которую разгребать и разгребать, например,
				 * загрузку кодов посещений целесообразно вынести в отдельный вариант
				 */
				$useCase = 'mix';
			}
			if (getRegionNick() != 'pskov' || empty($data['isVizitCode'])) {
				$this->loaduslugacomplexlistrequest->applyData($useCase, $data, $this->dbmodel, getOptions());
				$response = $this->loaduslugacomplexlistrequest->execute();
			} else {
				// Если после фильтрации, описанной в п.2.1.9.2, не осталось ни одного значения для выбора из списка, тогда в список значений автоматически добавляются все возможные коды посещения с учетом других фильтраций из п. 2.1.9.
				if (!empty($data['query'])) {
					//если ищем конкретное значение, нужно проверить есть ли значения без поиска
					$searchQuery = $data['query'];
					$data['query'] = '';
				}

				
				$this->loaduslugacomplexlistrequest->applyData($useCase, $data, $this->dbmodel, getOptions());
				$response = $this->loaduslugacomplexlistrequest->execute();

				if (empty($response)) {
					//если нет значений для выбора, отключаем фильтр
					$data['ignoreVolume2019Pskov'] = true;
				} 

				if (!empty($searchQuery)) {
					// возвращаем поиск
					$data['query'] = $searchQuery;
				}

				if (empty($response) || !empty($data['query'])) {
					// если с фильтром не было значений или есть строка для поиска, то нужен повторный запрос
					$this->loaduslugacomplexlistrequest->reset();
					$this->loaduslugacomplexlistrequest->applyData($useCase, $data, $this->dbmodel, getOptions());
					$response = $this->loaduslugacomplexlistrequest->execute();
				}		
			}
			
			$this->OutData = array();
			foreach ($response as $row) {
				array_walk_recursive($row, 'ConvertFromWin1251ToUTF8');
				$this->OutData[] = $row;
			}
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
	 * Функция загрузки списка услуг для комбо КСГ екб
	 */
	function loadKsgEkbList() {
		$this->load->helper('Options');

		$data = $this->ProcessInputData('loadKsgEkbList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadKsgEkbList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Функция читает список комплексных услуг при выборе определенной ветки дерева
	 */
	function loadUslugaComplexGrid() {
		$data = $this->ProcessInputData('loadUslugaComplex', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexView($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возращает состав комплексной услуги для определенной службы
	 */
	function loadUslugaComplexContentGrid() {
		$data = $this->ProcessInputData('loadUslugaComplex', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexContent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	* Функция читает одну комплексную услугу
	*/
	function loadUslugaComplexView() {
		$data = $this->ProcessInputData('loadUslugaComplexEdit', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexView($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Функция чтения списка Услуг ГОСТ для комбобокса
	 */
	function loadUslugaGostList() {
		$data = $this->ProcessInputData('loadUslugaGostList', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaGostList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	

	/**
	 *	Функция чтения списка тарифов для выбранной комплексной услуги
	 */
	function loadUslugaComplexTariffList() {
		$data = $this->ProcessInputData('loadUslugaComplexTariffList', true);
		if ( $data === false ) { return false; }

		if ( empty($data['UslugaComplexTariff_id']) ) {
			if ( empty($data['PayType_id']) ) {
				$this->ReturnError('Не указан вид оплаты');
				return false;
			}
			else if ( empty($data['Person_id']) ) {
				$this->ReturnError('Не указан идентификатор пациента');
				return false;
			}
			else if ( empty($data['UslugaComplex_id']) ) {
				$this->ReturnError('Не указана услуга');
				return false;
			}
			else if ( empty($data['UslugaComplexTariff_Date']) ) {
				$this->ReturnError('Не указана дата оказания услуги');
				return false;
			}
		}

		$response = $this->dbmodel->loadUslugaComplexTariffList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 *	Получение списка тарифов
	 */
	function loadUslugaComplexTariffLloList() {
		$data = $this->ProcessInputData('loadUslugaComplexTariffLloList', true);
		if ($data) {
			$response = $this->dbmodel->loadUslugaComplexTariffLloList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return true;
		}
	}


	/**
	 * Проверка услуги по МЭС
	 */
	function checkUslugaComplexIsMes() {
		$data = $this->ProcessInputData('checkUslugaComplexIsMes', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->checkUslugaComplexIsMes($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение тарифа ЛЛО
	 */
	function saveUslugaComplexTariffLlo() {
		$data = $this->ProcessInputData('saveUslugaComplexTariffLlo', true);
		if ($data){
			$response = $this->dbmodel->saveUslugaComplexTariffLlo($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении тарифа')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Удаление тарифа ЛЛО
	 */
	function deleteUslugaComplexTariffLlo() {
		$data = $this->ProcessInputData('deleteUslugaComplexTariffLlo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteUslugaComplexTariffLlo($data);
		$this->ProcessModelSave($response, true, 'При удалении тарифа ЛЛО возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 * Загрузка данных тарифа ЛЛО
	 */
	function getUslugaComplexPartition() {
		$data = $this->ProcessInputData('getUslugaComplexPartition', false);
		if ($data){
			$response = $this->dbmodel->getUslugaComplexPartition($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных тарифа ЛЛО
	 */
	function loadUslugaComplexTariffLlo() {
		$data = $this->ProcessInputData('loadUslugaComplexTariffLlo', false);
		if ($data){
			$response = $this->dbmodel->loadUslugaComplexTariffLlo($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка услуг для комбобокса (используется на форме редактирования тарифа ЛЛО)
	 */
	function loadUslugaComplexCombo() {
		$data = $this->ProcessInputData('loadUslugaComplexCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получения списка услуг связанных с методиками ИФА
	 */
	function loadUslugaComplexMethodsIFA() {
		$data = $this->ProcessInputData('loadUslugaComplexMethodsIFA', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexMethodsIFA($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получения списка инструментальных исследований
	 */
	function getUslugaAtributTypeAndGost() {
		$data = $this->ProcessInputData('getUslugaAtributTypeAndGost', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getUslugaAtributTypeAndGost($data);
        $this->ReturnData($response);

        return true;
//		$this->ProcessModelList($response, true, true)->ReturnData();
//		return true;
	}
}