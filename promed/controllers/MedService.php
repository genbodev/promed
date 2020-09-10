<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MedService - контроллер работы со службами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @property MedService_model MedService_model
 * @property LpuSectionProfileMedService_model $LpuSectionProfileMedService_model
 */

class MedService extends swController {
	/**
	 * Конструктор
	 */

	private $moduleMethods = [
		'createMedServiceRefSample',
		'loadUslugaComplexMedServiceGrid',
		'loadUslugaComplexMedServiceGridChild',
		'getApproveRights',
		'loadMedServiceList',
        'saveUslugaComplexMedServiceIsSeparateSample'
	];

	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'loadMedServiceListStat' => array(
				array('field' => 'query', 'label' => 'Лаборатория', 'rules' => 'trim', 'type' => 'string')
			),
			'getMedServiceData' => array(
				array('field' => 'Columns', 'label' => 'Поля', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'MedService_id', 'label' => 'MedService_id', 'rules' => 'trim', 'type' => 'string')
			),
			'loadMedServiceGrid' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => '','type' => 'id'),
				array('field' => 'MedService_sid','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
				array('field' => 'armMode', 'label' => 'флаг ЛИС', 'rules' => '', 'type' => 'string')
			),
			'checkMedServiceHasLinked' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
				array('field' => 'MedServiceLinkType_Code','label' => 'Код связи','rules' => '','type' => 'int')
			),
			'checkEQMedServiceLink' => array(),
			'deleteResource' =>array(
				array('field' => 'Resource_id','label' => 'Идентификатор ресурса','rules' => 'required','type' => 'id')
			),
			'saveResourceLink' => array(
				array('field' => 'Resource_id','label' => 'Идентификатор ресурса','rules' => 'required','type' => 'id'),
				array('field' => 'UslugaComplexMedService_id','label' => 'Идентификатор услуги на службе','rules' => 'required','type' => 'id'),
				array('field' => 'UslugaComplexResource_id','label' => 'Идентификатор связи','rules' => '','type' => 'id'),
				array('field' => 'UslugaComplexResource_Time','label' => 'Плановая длительность','rules' => '','type' => 'int'),
				array('field' => 'isActive','label' => '','rules' => 'required','type' => 'checkbox')
			),
			'createMedServiceRefSample' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
				array('field' => 'RefMaterial_id','label' => 'Идентификатор биоматериала','rules' => 'required','type' => 'id'),
				array('field' => 'ContainerType_id','label' => 'Тип контейнера','rules' => '','type' => 'id'),
				array('field' => 'RefSample_Name','label' => 'Наименование пробы','rules' => '','type' => 'string','default' => ''),
				array('field' => 'Usluga_ids','label' => 'Идентификаторы услуг, объединяемых в пробу','rules' => 'required','type' => 'string'),
				array('field' => 'UslugaComplexMedService_IsSeparateSample','label' => 'Флаг отдельной пробы','rules' => '','type' => 'string')
			),
			'loadEditForm' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id')
			),
			'loadApparatusEditForm' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id')
			),
			'loadMedServiceMedPersonalEditForm' => array(
				array('field' => 'MedServiceMedPersonal_id','label' => 'Идентификатор врача службы','rules' => 'required','type' => 'id')
			),
			'loadUslugaComplexResourceGrid' =>array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
				array('field' => 'UslugaComplexMedService_id','label' => 'Идентификатор услуги','rules' => '','type' => 'id'),
				array('field' => 'Resource_id','label' => 'Идентификатор ресурса','rules' => '','type' => 'id'),
				array('field' => 'object','label' => 'Объект','rules' => '','type' => 'string')
			),
			'loadMedProductCardResourceGrid' =>array(
				array('field' => 'Resource_id','label' => 'Идентификатор ресурса службы','rules' => 'required','type' => 'id')
			),
			'loadResource' =>array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
				array('field' => 'Resource_id','label' => 'Идентификатор ресурса службы','rules' => '','type' => 'id')
			),
			'deleteUslugaComplexMedService' => array(
				array('field' => 'object','label' => 'Объект','rules' => '','type' => 'string'),
				array('field' => 'id','label' => 'Идентификатор','rules' => 'required','type' => 'id')
			),
			'deleteRecord' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id')
			),
			'saveResource'=>array(
				array('field' => 'Resource_id','label' => 'Resource_id','rules' => '','type' => 'id'),
				array('field' => 'ResourceType_id','label' => 'ResourceType_id','rules' => '','type' => 'id'),
				array('field' => 'Resource_Name','label' => 'Resource_Name','rules' => '','type' => 'string'),
				array('field' => 'MedService_id','label' => 'MedService_id','rules' => '','type' => 'id'),
				array('field' => 'Resource_begDT','label' => 'Resource_id','rules' => '','type' => 'date'),
				array('field' => 'Resource_endDT','label' => 'Resource_id','rules' => '','type' => 'date'),
				array('field' => 'MedProductCardResourceData', 'label' => 'Медицинские изделия', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ucrData', 'label' => 'Набор связей', 'rules' => 'trim', 'type' => 'string')
			),
			'loadMedServiceList' => array(
				array('field' => 'Lpu_isAll','label' => 'Все ЛПУ?','rules' => '','type' => 'id','default' => 0),
				array('field' => 'isMse','label' => 'isMse','rules' => '','type' => 'id','default' => 0),
				array('field' => 'isHtm','label' => 'isHtm','rules' => '','type' => 'id','default' => 0),
				array('field' => 'is_Act','label' => 'Фильтр «Актуальные службы/Все» ','rules' => '','type' => 'id','default' => 1),
				array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'Contragent_id','label' => 'Идентификатор контрагента','rules' => '','type' => 'id'),
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подразделения','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitType_id','label' => 'Идентификатор типа группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuUnit_id','label' => 'Идентификатор группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Идентификатор отделения','rules' => '','type' => 'id'),
				array('field' => 'MedService_id','label' => 'Идентификатор службы', 'rules' => '','type' => 'id'),
				array('field' => 'MedService_pid','label' => 'Идентификатор службы, с которой связаны загружаемые службы', 'rules' => '','type' => 'id'),
				array('field' => 'MedServiceType_id','label' => 'Тип службы', 'rules' => '','type' => 'id'),
				array('field' => 'MedServiceType_SysNick','label' => 'Тип службы', 'rules' => '','type' => 'string'),
				array('field' => 'MedServiceTypeIsLabOrFenceStation','label' => 'Тип службы - Лаборатории и Пункты забора', 'rules' => '','type' => 'id'),
				array('field' => 'UslugaComplex_prescid','label' => 'Услуга из назначения', 'rules' => '', 'type' => 'id'),
				array('field' => 'ARMType','label' => 'Тип арма','rules' => '','type' => 'string'),
				array('field' => 'MedService_IsCytologic','label' => 'признак Цитологическое исследование','rules' => '','type' => 'int')
			),
			'loadGrid' => array(
				array('field' => 'is_All','label' => 'Фильтр «Службы Выбранного уровня/Все службы»','rules' => '','type' => 'id','default' => 1),
				array('field' => 'is_Act','label' => 'Фильтр «Актуальные службы/Все» ','rules' => '','type' => 'id','default' => 1),
				array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => 'required','type' => 'id'),
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подразделения','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitType_id','label' => 'Идентификатор типа группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuUnit_id','label' => 'Идентификатор группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Идентификатор отделения','rules' => '','type' => 'id'),
				array('field' => 'isClose', 'label' => 'Флаг закрытия', 'rules' => '', 'type' => 'int')
			),
			/*'saveMedServiceResource' => array(
				array('field' => 'Resource_id','label' => '','rules' => '','type' => 'id'),
				array('field' => 'isActive','label' => '','rules' => '','type' => 'checkbox'),
				array('field' => 'Resource_Time','label' => '','rules' => '','type' => 'int'),
				array('field' => 'Resource_begDT','label' => '','rules' => '','type' => 'date'),
				array('field' => 'Resource_endDT','label' => '','rules' => '','type' => 'date')
			),*/
			'loadMedServiceMedPersonalGrid' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id')
			),
			/*'loadMedServiceResourceGrid' => array(
				array('field' => 'UslugaComplexMedService_id','label' => 'UslugaComplexMedService_id','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_id','label' => 'MedService_id','rules' => 'required','type' => 'id'),
			),*/
			'loadUslugaComplexMedServiceGrid' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
				array('field' => 'UslugaComplexMedService_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
				array('field' => 'Urgency_id','label' => 'Актуальность','rules' => '','type' => 'id'),
				array('field' => 'UslugaComplex_pid','label' => 'Идентификатор','rules' => '','type' => 'id'),
				array('field' => 'armMode', 'label' => 'флаг ЛИС', 'rules' => '', 'type' => 'string')
			),
			'loadUslugaComplexMedServiceList' => array(
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
				array('field' => 'DirType_id','label' => 'Тип направления','rules' => '','type' => 'id'),
				array('field' => 'EvnPrescr_id','label' => 'Назначение','rules' => '','type' => 'id'),
				array('field' => 'PrescriptionType_Code','label' => 'Тип назначения','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Отделение пользователя','rules' => '','type' => 'id'),
				array('field' => 'MedService_Caption','label' => 'Служба','rules' => 'trim','type' => 'string'),
				array('field' => 'onlyMyLpu','label' => 'Только своя МО','rules' => '','type' => 'id')
			),
			'getUslugaComplexMedServiceList' => array(
				array('field' => 'uslugaCategoryList','label' => 'Список допустимых категорий услуги','rules' => 'required','type' => 'string'),
				array('field' => 'allowedUslugaComplexAttributeList','label' => 'Список допустимых типов атрибутов услуги','rules' => 'required','type' => 'string'),
				array('field' => 'LpuSection_id','label' => 'Отделение пользователя','rules' => 'required','type' => 'id'),
			),
			'getTimetableNoLimit' => array(
				array('field' => 'UslugaComplexMedService_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PrescriptionType_Code', 'label' => 'Тип назначения', 'rules' => '', 'type' => 'id'),
				array('field' => 'pzm_MedService_id', 'label' => 'Идентификатор пункта забора', 'rules' => '', 'type' => 'id'),
				array('field' => 'Resource_id', 'label' => 'Идентификатор ресурса', 'rules' => '', 'type' => 'id')
			),
			'getTimetableNoLimitWithMedService' => array(
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PrescriptionType_Code', 'label' => 'Тип назначения', 'rules' => '', 'type' => 'id')
			),
			'getTimetableNext' => array(
				array('field' => 'TimetableMedService_id', 'label' => 'Бирка службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'TimetableResource_id', 'label' => 'Бирка ресурса', 'rules' => '', 'type' => 'id')
			),
			'getUslugaComplexSelectList' => array(
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
				array('field' => 'sort','label' => 'Поле для сортировки','rules' => 'trim','type' => 'string'),
				array('field' => 'dir','label' => 'Направление сортировки','rules' => 'trim','type' => 'string'),
				array('default' => 0,'field' => 'isOnlyPolka','label' => 'Флаг отображения служб только поликлинических отделений','rules' => '','type' => 'int'),
				array('field' => 'uslugaCategoryList','label' => 'Список допустимых категорий услуги','rules' => 'required','type' => 'string'),
				array('field' => 'allowedUslugaComplexAttributeList','label' => 'Список допустимых типов атрибутов услуги','rules' => 'required','type' => 'string'),
				array('field' => 'userLpuSection_id','label' => 'Отделение пользователя','rules' => 'required','type' => 'id'),
				array('field' => 'filterByUslugaComplex_id','label' => 'Фильтр по услуге','rules' => '','type' => 'id'),
				array('field' => 'filterByLpu_id','label' => 'Фильтр по ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'filterByMedService_id','label' => 'Фильтр по службе','rules' => '','type' => 'id'),
				array('field' => 'MedService_id','label' => 'Фильтр по службе','rules' => '','type' => 'id'),
				array('field' => 'pzm_MedService_id','label' => 'Фильтр по службе','rules' => '','type' => 'id'),
				array('field' => 'Resource_id','label' => 'Фильтр по ресурсу','rules' => '','type' => 'id'),
				array('field' => 'filterByUslugaComplex_str','label' => 'Фильтр по услуге','rules' => 'ban_percent|trim','type' => 'string'),
				array('field' => 'filterByLpu_str','label' => 'Фильтр по ЛПУ','rules' => 'ban_percent|trim','type' => 'string'),
				array('field' => 'groupByMedService','label' => 'Групиировать по месту оказания','rules' => '','type' => 'int'),
				array('field' => 'onlyByContract','label' => 'Услуги по договорам','rules' => '','type' => 'int'),
				array('field' => 'noDateLimit','label' => 'Без ограничения по дате','rules' => '','type' => 'id'),
				array('field' => 'isStac','label' => 'ИзСтац','rules' => '','type' => 'id'),
				array('field' => 'formMode', 'label' => 'Тип формы', 'rules' => '', 'type' => 'string'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'int'),
				array('field' => 'filterByAnalyzerTestName','label' => 'Фильтр по наименованию теста-исследования','rules' => '','type' => 'string'),
				array('field' => 'filter', 'label' => 'Фильтры грида', 'rules' => '', 'type' => 'json_array', 'assoc' => true)
			),
			'getMedServiceSelectList' => array(
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'PrescriptionType_Code','label' => 'Prescription Type','rules' => '','type' => 'int'),
				array('field' => 'sort','label' => 'Поле для сортировки','rules' => 'trim','type' => 'string'),
				array('field' => 'dir','label' => 'Направление сортировки','rules' => 'trim','type' => 'string'),
				array('default' => 0,'field' => 'isOnlyPolka','label' => 'Флаг отображения служб только поликлинических отделений','rules' => '','type' => 'int'),
				array('field' => 'userLpuSection_id','label' => 'Отделение пользователя','rules' => 'required','type' => 'id'),
				array('field' => 'filterByUslugaComplex_id','label' => 'Фильтр по услуге','rules' => '','type' => 'id'),
				array('field' => 'filterByLpu_id','label' => 'Фильтр по ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'filterByLpu_str','label' => 'Фильтр по ЛПУ','rules' => 'ban_percent|trim','type' => 'string'),
				array('field' => 'formMode','label' => 'Фильтр по ЛПУ','rules' => '','type' => 'string')
			),
			'loadFilterCombo' => array(
				array('field' => 'PrescriptionType_Code','label' => 'Prescription Type','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'isOnlyPolka','label' => 'Флаг отображения служб только поликлинических отделений','rules' => '','type' => 'int'),
				array('field' => 'MedServiceType_id','label' => 'Тип службы','rules' => '','type' => 'id'),
				array('field' => 'filterByUslugaComplex_id','label' => 'Фильтр по услуге','rules' => '','type' => 'id'),
				array('field' => 'filterByLpu_id','label' => 'Фильтр по ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'filterByLpu_str','label' => 'Фильтр по ЛПУ','rules' => 'ban_percent|trim','type' => 'string'),
				array('field' => 'query','label' => 'контекстный поиск','rules' => 'ban_percent|trim','type' => 'string'),
			),
			'saveRecord' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => '','type' => 'id','default' => 0),
				array('field' => 'copyFromLpuSection','label' => 'Признак для копирования списков услуг и сотрудников из данных отделения','rules' => '','type' => 'id'),
				array('field' => 'Org_id','label' => 'Идентификатор организации','rules' => '','type' => 'id'),
				array('field' => 'OrgStruct_id','label' => 'Идентификатор структурного уровня','rules' => '','type' => 'id'),
				array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подразделения','rules' => '','type' => 'id'),
				array('field' => 'LpuUnit_id','label' => 'Идентификатор группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitType_id','label' => 'Идентификатор типа группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Идентификатор отделения','rules' => '','type' => 'id'),
				array('field' => 'MedServiceSectionData', 'label' => 'Список обслуживаемых отделений', 'rules' => 'trim', 'type' => 'string'),    //BOB - 25.01.2017
				array('field' => 'MedService_Name','label' => 'Наименование','rules' => 'trim|required','type' => 'string'),
				array('field' => 'LpuEquipmentPacs_id','label' => 'Идентификатор PACS-сервера','rules' => '','type' => 'id'), //#146135
				array('field' => 'MedService_Nick','label' => 'Краткое наименование','rules' => 'trim','type' => 'string','default' => ''),
				array('field' => 'MedService_Code','label' => 'Код','rules' => '','type' => 'string','default' => ''),
				array('field' => 'MedServiceType_id','label' => 'Тип','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_begDT','label' => 'Дата создания','rules' => 'required','type' => 'date'),
				array('field' => 'MedService_endDT','label' => 'Дата закрытия','rules' => '','type' => 'date'),
				array('field' => 'MedService_WialonLogin','label' => 'Логин Wialon','rules' => 'trim','type' => 'string'),
				array('field' => 'MedService_WialonPasswd','label' => 'Пароль Wialon','rules' => 'trim','type' => 'string'),
				array('field' => 'MedService_IsAutoQueryRes','label' => 'Автоматический запрос результатов','rules' => '','type' => 'checkbox'),
				array('field' => 'MedService_FreqQuery','label' => 'Периодичность запроса','rules' => 'trim','type' => 'float'),
				array('field' => 'MedService_WialonNick','label' => 'Имя','rules' => 'trim','type' => 'string'),
				array('field' => 'MedService_WialonURL','label' => 'Адрес сервиса','rules' => 'trim','type' => 'string'),
				array('field' => 'MedService_WialonAuthURL','label' => 'Адрес авторизации','rules' => 'trim','type' => 'string'),
				array('field' => 'MedService_WialonToken','label' => 'Токен','rules' => 'trim','type' => 'string'),
				array('field' => 'MedService_WialonPort','label' => 'Порт','rules' => 'trim','type' => 'string'),
				array('field' => 'ApiServiceType_id','label' => 'Порт','rules' => 'trim','type' => 'id'),
				array('field' => 'RecordQueue_id', 'label' => 'RecordQueue_id', 'rules'=>'trim', 'type'=>'id'),
				array('field' => 'MedService_IsThisLPU','label' => 'MedService_IsThisLPU','rules' => '','type' => 'checkbox'),
				array('field' => 'MedService_IsExternal','label' => 'Внешняя служба','rules' => '','type' => 'checkbox'),
				array('field' => 'MedService_IsShowDiag','label' => 'Признак группового одобрения качественных тестов','rules' => '','type' => 'checkbox'),
				array('field' => 'MedService_IsQualityTestApprove','label' => 'Отображение диагноза в заявке','rules' => '','type' => 'checkbox'),
				array('field' => 'MedService_IsFileIntegration', 'label' => 'Файловая интеграция', 'rules' => '', 'type' => 'checkbox'),
				array('field' => 'MedService_IsLocalCMP','label' => 'Резервировать данные на локальный сервер СМП','rules' => '','type' => 'checkbox'),
				array('field' => 'MedService_LocalCMPPath','label' => 'Адрес локального сервера СМП','rules' => '','type' => 'string'),
				array('field' => 'Address_id','label' => 'Идентификатор адреса','rules' => '','type' => 'id'),
				array('field' => 'Address_Zip','label' => 'Адрес','rules' => '','type' => 'string'),
				array('field' => 'KLCountry_id','label' => 'Адрес','rules' => '','type' => 'id'),
				array('field' => 'KLRGN_id','label' => 'Адрес','rules' => '','type' => 'id'),
				array('field' => 'KLSubRGN_id','label' => 'Адрес','rules' => '','type' => 'id'),
				array('field' => 'KLCity_id','label' => 'Адрес','rules' => '','type' => 'id'),
				array('field' => 'KLTown_id','label' => 'Адрес','rules' => '','type' => 'id'),
				array('field' => 'KLStreet_id','label' => 'Адрес','rules' => '','type' => 'id'),
				array('field' => 'Address_House','label' => 'Адрес','rules' => '','type' => 'string'),
				array('field' => 'Address_Corpus','label' => 'Адрес','rules' => '','type' => 'string'),
				array('field' => 'Address_Flat','label' => 'Адрес','rules' => '','type' => 'string'),
				array('field' => 'Address_Address','label' => 'Адрес','rules' => '','type' => 'string'),
				array('field' => 'MseOffice_id','label' => 'Код ЕАВИИАС','rules' => '','type' => 'id'),
				array('field' => 'LpuSectionAge_id','label' => 'Возрастная группа','rules' => '','type' => 'id'),
				array('field' => 'MedService_IsSendMbu','label' => 'Передавать данные в ПАК НИЦ МБУ','rules' => '','type' => 'checkbox')
			),
			'saveApparatus' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => '','type' => 'id','default' => 0),
				array('field' => 'MedService_pid','label' => 'Идентификатор службы','rules' => 'required','type' => 'id','default' => 0),
				array('field' => 'Org_id','label' => 'Идентификатор организации','rules' => '','type' => 'id'),
				array('field' => 'OrgStruct_id','label' => 'Идентификатор структурного уровня','rules' => '','type' => 'id'),
				array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подразделения','rules' => '','type' => 'id'),
				array('field' => 'LpuUnit_id','label' => 'Идентификатор группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitType_id','label' => 'Идентификатор типа группы отделений','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Идентификатор отделения','rules' => '','type' => 'id'),
				array('field' => 'MedService_Name','label' => 'Наименование','rules' => 'trim|required','type' => 'string'),
				array('field' => 'MedService_Nick','label' => 'Краткое наименование','rules' => 'trim','type' => 'string','default' => ''),
				array('field' => 'MedService_begDT','label' => 'Дата создания','rules' => 'required','type' => 'date'),
				array('field' => 'MedService_endDT','label' => 'Дата закрытия','rules' => '','type' => 'date')
			),
			'saveMedServiceMedPersonalRecord' => array(
				array('field' => 'MedServiceMedPersonal_id','label' => 'Идентификатор врача службы','rules' => '','type' => 'id','default' => 0),
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
				array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => 'required','type' => 'id'),
				array('field' => 'MedServiceMedPersonal_isNotApproveRights','label' => 'Запрет на одобрение результатов исследований','rules' => '','type' => 'checkbox'),
				array('field' => 'MedServiceMedPersonal_isNotWithoutRegRights','label' => 'Запрет на создание заявки без записи','rules' => '','type' => 'checkbox'),
				array('field' => 'MedServiceMedPersonal_begDT','label' => 'Дата начала','rules' => 'required','type' => 'date'),
				array('field' => 'MedServiceMedPersonal_endDT','label' => 'Дата окончания','rules' => '','type' => 'date'),
				array('field' => 'MedServiceMedPersonal_IsTransfer','label' => 'Передавать данные в ЕГИСЗ','rules' => '','type' => 'checkbox'),
				array('field' => 'MedStaffFact_id','label' => 'Место работы','rules' => '','type' => 'id')
			),
			'defineMedServiceListOnMedPersonal' => array(
				array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => 'required','type' => 'id')
			),
			'loadMedServiceMedPersonalList' => array(
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedServiceType_id', 'label' => 'Тип', 'rules' => '', 'type' => 'id')
			),
			'loadCompositionMenu' => array(
				array(
					'field' => 'UslugaComplexMedService_pid',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_pid',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadApparatusList' => array(
				array(
					'field' => 'MedService_pid',
					'label' => 'Родительская служба',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadCompositionMenu' => array(
				array('field' => 'UslugaComplexMedService_pid', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_pid', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_pid', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'isExt6', 'label' => 'Загрузка состава услуги для формы ExtJS 6', 'rules' => '', 'type' => 'int'),
			),
			'loadCompositionTree' => array(
				array('field' => 'UslugaComplexMedService_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'check', 'label' => 'Дерево с чеками', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'Mes_id', 'label' => 'Параметр, чтобы знать входит ли услуга из состава в указанный МЭС', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnUsluga_pid', 'label' => 'Параметр, чтобы отобразить услуги, которые не были оказаны', 'rules' => '', 'type' => 'id'),
				array('field' => 'chooseUslugaComplexTariff', 'label' => 'Признак, что требуется выбор тарифов услуги из состава', 'rules' => '', 'type' => 'int', 'default' => 0),
			),
			'getLpusWithMedService' => array(
				array(
					'field' => 'MedServiceType_id',
					'label' => 'Тип службы',
					'rules' => '',
					'type' => 'int'
					),
				array(
					'field' => 'comAction',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KLAreaStat_idEdit',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CmpCallCard_Dom',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Age',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Server_id',
					'label' => 'источник данных',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_Name',
					'label' => 'наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedService_Nick',
					'label' => 'краткое наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedServiceType_id',
					'label' => 'Тип Cлужбы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'Тип Cлужбы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'тип подразделения ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Группа отделений',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_begDT',
					'label' => 'дата начала',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'MedService_endDT',
					'label' => 'дата окончания',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OrgStruct_id',
					'label' => 'Структурный уровень огранизации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_lid',
					'label' => 'Связанная служба',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'NotLinkedMedService_id',
					'label' => 'Только несвязанные',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'filterByCurrentMedPersonal',
					'label' => 'К которым имеет доступ пользователь ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'order',
					'label' => 'Сортировка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isDirection',
					'label' => 'Флаг',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'setDate',
					'label' => 'дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'isClose',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'isLpuSectionLpuSectionProfileList',
					'label' => 'Флаг',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				)
			),
			'getMedServiceCode' => array( ),
			'loadMedServiceListWithStorage' => array( ),
			'getApproveRights' => array(
				array('field' => 'MedServiceMedPersonal_id','label' => 'Идентификатор врача службы','rules' => '','type' => 'id','default' => 0),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id','default' => 0),
				array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => '','type' => 'id','default' => 0),
				array('field' => 'armMode','label' => 'Тип арма','rules' => '','type' => 'string')
			),
			'getArmLevelByMedService' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
            //BOB - 25.01.2017
			'loadMedServiceSectionGrid'=>array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				)
			),
            //BOB - 25.01.2017
			'getRowMedServiceSection'=>array(
				array(
					'field' => 'MedServiceSection_id',
					'label' => 'Идентификатор связи службы c обслуживаемым отделением',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор обслуживаемого отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RecordStatus_Code',
					'label' => 'Код статсуа записи',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLastPrescrList' => array(
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'top', 'label' => 'Количество записей', 'rules' => '', 'type' => 'int', 'default' => 20),
				array('field' => 'PrescriptionType_Code', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => 'required','type' => 'id')
			),
			'loadEvnPrescrUslugaList' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'userLpuSection_id', 'label' => 'Идентификатор отделения пользователя', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'debug', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'int'),

			),
			'getPzmUslugaComplexMedService' => [
				['field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'],
				['field' => 'MedService_id', 'label' => 'Идентификатор пункта забора', 'rules' => 'required', 'type' => 'id']
			],
			'checkMedServiceUsluga' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => '','type' => 'id')
			),
            'saveUslugaComplexMedServiceIsSeparateSample' => [
				[
				    'field' => 'UslugaComplexMedService_id',
                    'label' => 'ID связи услуги и мед.службы',
                    'rules' => 'required',
                    'type' => 'id'
                ],
                [
				    'field' => 'UslugaComplex_id',
                    'label' => 'ID комплексной услуги',
                    'rules' => 'required',
                    'type' => 'id'
                ],
                [
				    'field' => 'UslugaComplexMedService_IsSeparateSample',
                    'label' => 'Флаг отдельной пробы',
                    'rules' => 'required',
                    'type' => 'boolean'
                ],
			]
		);
		$this->init();
	}

	/**
	 * Дополнительная инициализация
	 */
	private function init(){
		$method = $this->router->fetch_method();

		if ($this->usePostgreLis && in_array($method, $this->moduleMethods)) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('MedService_model');
		}
	}

	/** Получение данных по МО */
	function getMedServiceData() {
		$data = $this->ProcessInputData('getMedServiceData', true);
		if ($data === 'false') return false;
		$query = "select {$data["Columns"]} from MedService where MedService_id = :MedService_id";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$this->ProcessModelList($result->result('object'), true, true)->ReturnData();
			return true;
		}
		return false;
	}

	/**
	*  Читает для комбобокса MedService
	*/
	function loadMedServiceList(){
		$data = $this->ProcessInputData('loadMedServiceList', true);
		if ($data === 'false') {return false;}
		$data['Contragent_id'] = (isset($_POST) && isset($_POST['Contragent_id']))?$_POST['Contragent_id']:null;

		if ($this->usePostgreLis && in_array($data['ARMType'], ['lab', 'pzm', 'reglab'])) {
			$res = $this->lis->GET('MedService/MedServiceList', $data);
			if (!$this->isSuccessful($res)) {
				return $res;
			}
			$this->ProcessRestResponse($res, 'list')->ReturnData();
		} else {
			$this->load->database();
			$this->load->model('MedService_model');
			$response = $this->MedService_model->loadMedServiceList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
		return true;
	}

	/**
	*  Читает список аппаратов
	*/
	function loadApparatusList() {
		$data = $this->ProcessInputData('loadApparatusList', true);
		if ($data)
		{
			$response = $this->MedService_model->loadApparatusList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Проверка наличия у службы связанных служб
	 */
	function checkMedServiceHasLinked() {
		$data = $this->ProcessInputData('checkMedServiceHasLinked', true);
		if ($data === false) { return false; }

		$response = $this->MedService_model->checkMedServiceHasLinked($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения связанных служб')->ReturnData();

		return true;
	}

	/**
	 * Получение списка служб
	 */
	function loadMedServiceGrid() {
		$data = $this->ProcessInputData('loadMedServiceGrid', true);
		if ($data === false) { return false; }

		if ($this->usePostgreLis && $data['armMode'] && strtolower($data['armMode']) == 'lis') {
			$this->load->swapi('lis');
			$res = $this->lis->GET('MedService/MedServiceGrid', $data);
			$this->ProcessRestResponse($res, 'list')->ReturnData();
		} else {
			$this->load->database();
			$this->load->model('MedService_model');
			$response = $this->MedService_model->loadMedServiceGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

    /**
     * Объединение услуг в пробу
     * @return bool
     */
    function createMedServiceRefSample(){
		$data = $this->ProcessInputData('createMedServiceRefSample', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST('MedService/createRefSample', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->MedService_model->createMedServiceRefSample($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		}
    }

	/**
	*  Функция чтения списка записей для грида MedServiceMedPersonal
	*  На выходе: JSON-строка
	*  Используется: форма LpuStructureViewForm
	*/
	function loadMedServiceMedPersonalGrid() {
        $data = $this->ProcessInputData('loadMedServiceMedPersonalGrid', true);
		if ($data)
		{
			$response = $this->MedService_model->loadMedServiceMedPersonalGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	*  Функция чтения списка записей для грида MedServiceMedPersonal
	*  На выходе: JSON-строка
	*  Используется: форма LpuStructureViewForm
	*/
	function loadUslugaComplexResourceGrid() {
        $data = $this->ProcessInputData('loadUslugaComplexResourceGrid', true);
		if ($data)
		{
			$response = $this->MedService_model->loadUslugaComplexResourceGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция чтения списка записей для грида MedProductCardGrid
	*  На выходе: JSON-строка
	*  Используется: форма LpuStructureViewForm
	*/
	function loadMedProductCardResourceGrid() {
        $data = $this->ProcessInputData('loadMedProductCardResourceGrid', true);
		if ($data)
		{
			$response = $this->MedService_model->loadMedProductCardResourceGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 *
	 * @return type
	 */
	/*function loadMedServiceResourceGrid() {
		$data = $this->ProcessInputData('loadMedServiceResourceGrid', true);
		if ($data)
		{
			$response = $this->MedService_model->loadMedServiceResourceGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}*/
	/**
	*  Функция чтения списка записей для грида UslugaComplexMedService
	*  На выходе: JSON-строка
	*  Используется: форма LpuStructureViewForm
	*/
	function loadUslugaComplexMedServiceGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexMedServiceGrid', true);
		if ($data === false) {return false;}

		if ($this->usePostgreLis && $data['armMode'] && strtolower($data['armMode']) == 'lis') {
			$this->load->swapi('lis');
			$res = $this->lis->GET('MedService/UslugaComplexMedServiceGrid', $data);
			$this->ProcessRestResponse($res, 'list')->ReturnData();
		} else {
			$this->load->database();
			$this->load->model('MedService_model');
			$response = $this->MedService_model->loadUslugaComplexMedServiceGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;

	}

	/**
	 * Удаление ресурса
	 */
	function deleteResource() {
		$data = $this->ProcessInputData('deleteResource', true);
		if ( $data === false ) { return false; }
		$response = $this->MedService_model->deleteResource($data);
		$this->ProcessModelSave($response, true, 'При удалении ресурса возникли ошибки')->ReturnData();

		return true;
	}
	/**
	 * loadUslugaComplexMedServiceGridChild
	 * @return bool
	 */
	function loadUslugaComplexMedServiceGridChild()
    {
		$data = $this->ProcessInputData('loadUslugaComplexMedServiceGrid', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$res = $this->lis->GET('MedService/UslugaComplexMedServiceGridChild', $data);
			$this->ProcessRestResponse($res, 'list')->ReturnData();
		} else {
			$response = $this->MedService_model->loadUslugaComplexMedServiceGridChild($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
    }

    /**
	*  Функция чтения списка записей для грида MedService
	*  На выходе: JSON-строка
	*  Используется: форма LpuStructureViewForm
	*/
	function loadGrid() {
		$data = $this->ProcessInputData('loadGrid', true);
		if ($data)
		{
			$response = $this->MedService_model->loadGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция сохранения аппарата
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swApparatusEditWindow
	*/
	function saveApparatus() {
		$data = $this->ProcessInputData('saveApparatus', true);
		if ($data)
		{
			$response = $this->MedService_model->saveApparatus($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 *
	 * @return type
	 */
	/*function saveMedServiceResource(){
		$data = $this->ProcessInputData('saveMedServiceResource', true);
		if ($data)
		{
			$response = $this->MedService_model->saveMedServiceResource($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}*/
	/**
	 *
	 * @return type
	 */
	function saveResource(){
		$data = $this->ProcessInputData('saveResource', true);
		if ($data)
		{
			$response = $this->MedService_model->saveResource($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 *
	 * @return type
	 */
	function saveResourceLink(){
		$data = $this->ProcessInputData('saveResourceLink', true);
		if ($data)
		{
			$response = $this->MedService_model->saveResourceLink($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция сохранения одной записи MedService
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swMedServiceEditWindow
	*/
	function saveRecord() {
		$data = $this->ProcessInputData('saveRecord', false);
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['session'] = $sp;

		if ($data)
		{
			$response = $this->MedService_model->saveRecord($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция удаления одной записи UslugaComplexMedService
	*  На выходе: JSON-строка
	*  Используется: форма LpuStructureViewForm
	*/
	function deleteUslugaComplexMedService() {
		$data = $this->ProcessInputData('deleteUslugaComplexMedService', true);
		if ($data)
		{
			$response = $this->MedService_model->deleteUslugaComplexMedService($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция чтения одной записи MedService
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swMedServiceEditWindow
	*/
	function loadEditForm() {
		$data = $this->ProcessInputData('loadEditForm', true);
		if ($data)
		{
			$response = $this->MedService_model->loadEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция чтения аппарата
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swApparatusEditWindow
	*/
	function loadApparatusEditForm() {
		$data = $this->ProcessInputData('loadApparatusEditForm', true);
		if ($data)
		{
			$response = $this->MedService_model->loadApparatusEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Читает для табгрида "Профили консультирования" в структуре МО
	 */
	function loadLpuSectionProfileGrid()
	{
		$this->load->model('LpuSectionProfileMedService_model');
		$this->inputRules['loadLpuSectionProfileGrid'] = $this->LpuSectionProfileMedService_model->getInputRules(swModel::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('loadLpuSectionProfileGrid', true);
		if (false == $data) { return false; }
		$response = $this->LpuSectionProfileMedService_model->loadGrid($data);
		$this->ProcessModelList($response, true, true)
			->ReturnData();
		return true;
	}

	/**
	 * Читает для формы "Профиль консультирования" в структуре МО
	 */
	function loadLpuSectionProfileMedServiceEditForm()
	{
		$this->load->model('LpuSectionProfileMedService_model');
		$this->inputRules['loadLpuSectionProfileMedServiceEditForm'] = $this->LpuSectionProfileMedService_model->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadLpuSectionProfileMedServiceEditForm', true);
		if (false == $data) { return false; }
		$response = $this->LpuSectionProfileMedService_model->loadEditForm($data);
		$this->ProcessModelList($response, true, false)
			->ReturnData();
		return true;
	}

	/**
	 * Сохраняет строку из табгрида "Профили консультирования" в структуре МО
	 */
	function saveLpuSectionProfileMedService()
	{
		$this->load->model('LpuSectionProfileMedService_model');
		$this->inputRules['loadLpuSectionProfileGrid'] = $this->LpuSectionProfileMedService_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('loadLpuSectionProfileGrid', true);
		if (false == $data) { return false; }
		if ( empty($data['isAutoCreate']) ) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}
		$response = $this->LpuSectionProfileMedService_model->doSave($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	*  Функция сохранения одной записи MedServiceMedPersonal
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swMedServiceMedPersonalEditWindow
	*/
	function saveMedServiceMedPersonalRecord() {
		$data = $this->ProcessInputData('saveMedServiceMedPersonalRecord', true);
		if ($data)
		{
			// проверка дублирования врача
			$response = $this->MedService_model->checkDoubleMedPersonal($data);
			if( !is_array($response) )
			{
				$this->ReturnData(array('success' => false,'Error_Code' => 1,'Error_Msg' => toUTF('Не удалось выполнить проверку дублирования врача!')));
				return false;
			}
			else if ( count($response) > 0)
			{
				$this->ReturnData(array('success' => false,'Error_Code' => 7,'Error_Msg' => toUTF('Данный врач уже указан на этой службе!')));
				return true;
			}
			$response = $this->MedService_model->saveMedServiceMedPersonalRecord($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция чтения одной записи MedServiceMedPersonal
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swMedServiceMedPersonalEditWindow
	*/
	function loadMedServiceMedPersonalEditForm() {
		$data = $this->ProcessInputData('loadMedServiceMedPersonalEditForm', true);
		if ($data)
		{
			$response = $this->MedService_model->loadMedServiceMedPersonalEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 * @comment
	 */
	function loadResource() {
		$data = $this->ProcessInputData('loadResource', true);
		if ($data)
		{
			$response = $this->MedService_model->getResourceData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	*  Функция определения к каким службам врач имеет доступ
	*  На выходе: JSON-строка
	*  Используется: АРМы
	*/
	function defineMedServiceListOnMedPersonal()
	{
		$data = $this->ProcessInputData('defineMedServiceListOnMedPersonal', true);
		if($data) {
			$response = $this->MedService_model->defineMedServiceListOnMedPersonal($data);
			if(is_array($response)) {
				echo json_encode($response);
				return true;
			}
		}
		return false;
	}

	/**
	*  Функция загрузки хранилища для комбобокса врачей служб
	*  На выходе: JSON-строка
	*  Используется: комбобокс врачей служб
	*/
	function loadMedServiceMedPersonalList()
	{
		$data = $this->ProcessInputData('loadMedServiceMedPersonalList', true);
		if($data) {
			$response = $this->MedService_model->loadMedServiceMedPersonalList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	*	Возвращает все ЛПУ, в которых есть служба определенного типа
	*/
	function getLpusWithMedService() {
		$data = $this->ProcessInputData('getLpusWithMedService', false);
		if($data) {
			$response = $this->MedService_model->getLpusWithMedService($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Читает список для sw.Promed.SwMedServiceCombo
	 * @return bool
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MedService_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Читает для формы направления на службы
	 * @return bool
	 */
	function loadUslugaComplexMedServiceList()
	{
		$data = $this->ProcessInputData('loadUslugaComplexMedServiceList', true);
		if ($data) {
			$response = $this->MedService_model->loadUslugaComplexMedServiceList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка данных в грид услуг левой части формы добавления назначений услуг
	 * @return bool
	 */
	function getUslugaComplexSelectList()
	{
		$data = $this->ProcessInputData('getUslugaComplexSelectList', true);
		if ($data) {
			$response = $this->MedService_model->getUslugaComplexSelectList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка данных в грид формы выбора службы по известной услуге
	 * @return bool
	 */
	function getMedServiceSelectList()
	{
		$data = $this->ProcessInputData('getMedServiceSelectList', true);
		if ($data) {
			$response = $this->MedService_model->getMedServiceSelectList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Проверка наличия связи службы с ЭО
	 */
	function checkEQMedServiceLink() {
		$data = $this->ProcessInputData('checkEQMedServiceLink', true);
		if (empty($data['session']['CurMedService_id'])) { return false; }
		$result = $this->MedService_model->checkEQMedServiceLink($data);
		$response = array(
			'MedServiceLinked' => $result,
			'success' => true
		);
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Загрузка данных выбора службы по известной услуге в комбо "Место оказания"
	 * в списке услуг формы добавления назначений услуг
	 * @return bool
	 */
	function getMedServiceSelectCombo()
	{
		$data = $this->ProcessInputData('getMedServiceSelectList', true);
		if (false == $data) { return false; }
		$response = $this->MedService_model->getMedServiceSelectCombo($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка в фильтр "Службы" в форме добавления назначений услуг
	 * @return bool
	 */
	function loadFilterCombo()
	{
		$data = $this->ProcessInputData('loadFilterCombo', true);
		if (false == $data) { return false; }
		$response = $this->MedService_model->loadFilterCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загружает список услуг служб для назначения
	 * @return bool
	 */
	function getUslugaComplexMedServiceList()
	{
		$data = $this->ProcessInputData('getUslugaComplexMedServiceList', true);
		if ($data) {
			$response = $this->MedService_model->getUslugaComplexMedServiceList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загружает состав услуг в меню
	 * @return bool
	 */
	function loadCompositionMenu()
	{
		$data = $this->ProcessInputData('loadCompositionMenu', true);
		if ($data) {
			$response = $this->MedService_model->loadCompositionMenu($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загружает первую свободную бирку по кнопке уточнить (без ограничения времени)
	 * @return bool
	 */
	function getTimetableNoLimit()
	{
		$data = $this->ProcessInputData('getTimetableNoLimit', true);
		if ($data === false) { return false; }

			$response = $this->MedService_model->getTimetableNoLimit($data);
			$this->ProcessModelSave($response, true, 'Ошибка получения первой свободной бирки')->ReturnData();
			return true;
		}

	/**
	 * Загружает следующую свободную бирку после того как записали назначение
	 * @return bool
	 */
	function getTimetableNext()
	{
		$data = $this->ProcessInputData('getTimetableNext', true);
		if ($data === false) { return false; }

		$response = $this->MedService_model->getTimetableNext($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения следующей свободной бирки')->ReturnData();
		return true;
	}

	/**
	 *	Загружает состав услуг в деревo
	 */
	function loadCompositionTree() {
		$data = $this->ProcessInputData('loadCompositionTree', true);
		if ( $data === false ) { return false; }
		$response = $this->MedService_model->loadCompositionTree($data);
		/**
		 * @param array $row
		 * @param swController $thas
		 * @return array
		 */
		function processingRowCompositionTree($row, $thas) {
			// Обработка для дерева
			$node = array();
			$id_field = $row['object'].'_id';
			$is_for_prescription = (false == $thas->GetInData('Mes_id'));
			$node['text'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
			$node['id'] = $row['UslugaComplex_id'];
			$node['UslugaComplexMedService_id'] = $row['UslugaComplexMedService_id'];
			$node['object'] = $row['object'];
			$node['object_id'] = $id_field;
			$node['object_value'] = $row[$id_field];
			$node['object_code'] = $row['UslugaComplex_Code'];
			$node['leaf'] = (empty($row['ChildrensCount']) ? true : false);
			$node['iconCls'] = 'uslugacomplex-16';
			$node['cls'] = 'folder';

			$node['UslugaComplex_isMes'] = $row['UslugaComplex_isMes'];
			$node['UslugaComplex_pid'] = $row['UslugaComplex_pid'];
			if ($thas->GetInData('chooseUslugaComplexTariff')) {
				$node['text'] = $row['UslugaComplex_Code'] . ' ' . $row['UslugaComplex_Name']
					. '<div style="float: right; margin-top: -17px; padding: 0; line-height: normal;" id="'
					. 'chooseUslugaComplexTariffWrap' . $row['UslugaComplex_id']
					. '"></div>';
			}

			if ($thas->GetInData('check')) {
				$node['uiProvider'] = 'tristate';
				$node['checked'] = false;
			}

			if ($is_for_prescription && false == $thas->MedService_model->options['prescription']['enable_show_service_code']) {
				$node['text'] = $row['UslugaComplex_Name'];
			}
			return $node;
		}
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', 'processingRowCompositionTree')->ReturnData();
		return true;
	}

	/**
	*  Список лабораторий для комбобокса на форме статистики расхода реактивов
	*/
	function loadMedServiceListStat(){
		$data = $this->ProcessInputData('loadMedServiceListStat', true);
		if ($data)
		{
			$response = $this->MedService_model->loadMedServiceListStat($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Генерирует код службы (для лабораторий)
	*/
	function getMedServiceCode(){
		$data = $this->ProcessInputData('getMedServiceCode', true);
		if ($data)
		{
			$response = $this->MedService_model->getMedServiceCode($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка служб, в которых есть склады
	 * @return boolean
	 */

	public function loadMedServiceListWithStorage() {
		$data = $this->ProcessInputData('loadMedServiceListWithStorage', true);
		if (!$data) return false;
		$response = $this->MedService_model->loadMedServiceListWithStorage($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Определение уровня на котором запущен АРМ для swWorkPlaceMerchandiserWindow
	*/
	function getArmLevelByMedService(){
		$data = $this->ProcessInputData('getArmLevelByMedService', true);
		if ($data)
		{
			$response = $this->MedService_model->getArmLevelByMedService($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получить права врача на данной службе (право одобрять пробы)
	 */
	function getApproveRights() {
		$data = $this->ProcessInputData('getApproveRights', true);
		if ($data === false) {return false;}

		if ($this->usePostgreLis) {
			$res = $this->lis->GET('MedService/ApproveRights', $data);
			$this->ProcessRestResponse($res, 'single')->ReturnData();
		} else {
			$response = $this->MedService_model->getApproveRights($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

    //BOB - 25.01.2017
	/**
	 * Возвращает список обслуживаемых отделений
	 */
	public function loadMedServiceSectionGrid() {
		$data = $this->ProcessInputData('loadMedServiceSectionGrid', false);
		if ($data === false) { return false; }

		$response = $this->MedService_model->loadMedServiceSectionGrid($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Формирование строки грида обслуживаемых отделений присоединённых к службе
	 */
	public function getRowMedServiceSection() {
		$data = $this->ProcessInputData('getRowMedServiceSection', false);
		if ($data === false) { return false; }

		$response = $this->MedService_model->getRowMedServiceSection($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
    //BOB - 25.01.2017
	/**
	 * Загружает первую свободную бирку по кнопке уточнить (без ограничения времени)
	 * @return bool
	 */
	function getTimetableNoLimitWithMedService()
	{
		$data = $this->ProcessInputData('getTimetableNoLimitWithMedService', true);
		if ($data === false) {
			return false;
		}

		$response = $this->MedService_model->getTimetableNoLimitWithMedService($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения первой свободной бирки')->ReturnData();
		return true;

	}
	/**
	 * Загружает список услуг служб для назначения
	 * @return bool
	 */
	function loadLastPrescrList()
	{
		$data = $this->ProcessInputData('loadLastPrescrList', true);
		if ($data) {
			$response = $this->MedService_model->loadLastPrescrList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 * Загружает список назначенных услуг для посещения
	 * @return bool
	 */
	function loadEvnPrescrUslugaList()
	{
		$data = $this->ProcessInputData('loadEvnPrescrUslugaList', true);
		if ($data) {
			$response = $this->MedService_model->loadEvnPrescrUslugaList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
			return false;
	}

	/**
	 * Список кодов ЕАВИИАС
	 */
	function loadMseOfficeList() {
		$response = $this->MedService_model->loadMseOfficeList();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение связи услуги службы и пункта забора
	*/
	function getPzmUslugaComplexMedService()
	{
		$data = $this->ProcessInputData('getPzmUslugaComplexMedService', false);
		if ($data === false) { return false; }

		$response = $this->MedService_model->getPzmUslugaComplexMedService($data);

		$this->ProcessModelSave($response, true, 'Ошибка получения связи услуги и пункта забора')->ReturnData();
		return true;
	}
	/**
	 * Проверка Содержит ли служба одну из услуг A05.10.002, A05.10.006, A05.10.004 (ЭКГ)
	 */
	function checkMedServiceUsluga() {
		$data = $this->ProcessInputData('checkMedServiceUsluga', true);
		if (empty($data['MedService_id'])) { return false; }
		$result = $this->MedService_model->checkMedServiceUsluga($data);
		$response = array(
			'checkMedServiceUsluga' => $result
		);
		$this->ReturnData($response);
		return true;
	}
	/**
	 * Сохранение ячейки "Всегда отдельная проба" комплексной услуги
	 */
	function saveUslugaComplexMedServiceIsSeparateSample() {
		$data = $this->ProcessInputData('saveUslugaComplexMedServiceIsSeparateSample', true);
        if ($this->usePostgreLis){

            $response = $this->lis->POST('MedService/saveUslugaComplexMedServiceIsSeparateSample', $data);
            $this->ProcessRestResponse($response, 'single')->ReturnData();

        }else{

            $this->load->model('UslugaComplexMedService_model');
            $resp = $this->UslugaComplexMedService_model->doSaveUslugaComplexMedService(array(
                'scenario' => SwModel::SCENARIO_DO_SAVE,
                'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
                'UslugaComplex_id' => $data['UslugaComplex_id'],
                'UslugaComplexMedService_IsSeparateSample' => ( $data['UslugaComplexMedService_IsSeparateSample'] == "true" ? 2 : 1 ),
                'session' => $data['session'],
                'pmUser_id' => $data['pmUser_id']
            ));

            if (!empty($resp['UslugaComplexMedService_id'])) {
                $this->ProcessModelSave($resp, true, 'Ошибка выделения теста в отдельную пробу')->ReturnData();
                return true;
            } else {
                return false;
            }
        }
	}
}
