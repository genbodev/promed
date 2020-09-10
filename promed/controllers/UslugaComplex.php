<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* UslugaComplex - контроллер для работы со справочником услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2010-2012 Swan Ltd.
* @author		Stas Bykov aka Savage (savage@swan.perm.ru)
* @version		15.05.2014
* @property UslugaComplex_model $dbmodel
*/
class UslugaComplex extends swController {
	public $inputRules = array(
		'loadUslugaComplexGroupList' => array(
			array(
				'field' => 'filterByUslugaComplex_id',
				'label' => 'Выбранная родительская услуга',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadUslugaComplexListForMes' => array(
			array('field' => 'Mes_id', 'label' => 'Идентификатор МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mes_Code', 'label' => 'Код МЭС', 'rules' => '', 'type' => 'string'),
			array('field' => 'requiredOnly', 'label' => 'Признак необходимости загрузки только обязательных услуг', 'rules' => '', 'type' => 'int'),
		),
		'loadUslugaComplexMedServiceGrid' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_Code',
				'label' => 'Код услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_Name',
				'label' => 'Название услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexMedService_pid',
				'label' => 'Идентификатор родительской услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteUslugaComplexMedService' => array(
			array(
				'field' => 'UslugaComplexMedService_id',
				'label' => 'Идентификатор услуги на службе',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveUslugaComplexMedService' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_pid',
				'label' => 'Идентификатор родительской услуги',
				'rules' => '',
				'default' => NULL,
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_id',
				'label' => 'Идентификатор услуги на службе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_begDT',
				'label' => 'Дата начала услуги',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplexMedService_endDT',
				'label' => 'Дата окончания услуги',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplexMedService_Time',
				'label' => 'Длительность, мин',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'UslugaComplexMedService_IsPortalRec',
				'label' => 'Разрешить запись на Портале и в Мобильном приложении',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'UslugaComplexMedService_IsPay',
				'label' => 'Платная услуга',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'UslugaComplexMedService_IsElectronicQueue',
				'label' => 'Участвует в электронной очереди',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array('field' => 'ucrData', 'label' => 'Набор связей', 'rules' => 'trim', 'type' => 'string')
		),
		'deleteUslugaComplex' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteLinkedUslugaComplex' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор связанной услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_pid',
				'label' => 'Идентификатор основной услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteUslugaComplexComposition' => array(
			array(
				'field' => 'UslugaComplexComposition_id',
				'label' => 'Идентификатор записи из состава услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUslugaSMPCombo' => array(
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'uslugaCategoryList', 'label' => 'Список категорий услуг', 'rules' => 'trim', 'type' => 'json_array'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
		),
		'loadUslugaComplexCombo' => array(
				array(
					'field' => 'Lpu_uid',
					'label' => 'ЛПУ',
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
					'field' => 'UslugaComplex_Code',
					'label' => 'Код услуги',
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
					'field' => 'UslugaComplex_begDT',
					'label' => 'Дата начала услуги',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_endDT',
					'label' => 'Дата начала услуги',
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
					'field' => 'UslugaComplex_ForLpuFilter_pid',
					'label' => 'Родительская услуга, для фильтрации по ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
		),
		'loadUslugaComplexGost' => array(
				array(
					'field' => 'UslugaCategory_id',
					'label' => 'Категория услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				)
		),
		'saveUslugaComplexLinked' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_pid',
				'label' => 'Идентификатор родительской услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'CopyAllLinked',
				'label' => 'Скопировать все связанные',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'default' => 0,
				'field' => 'CopyContent',
				'label' => 'Скопировать состав',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'default' => 0,
				'field' => 'rewriteExistent',
				'label' => 'Перезаписать категорию',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'default' => 0,
				'field' => 'CopyAttributes',
				'label' => 'Скопировать атрибуты',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'oldUslugaComplex_id',
				'label' => 'Идентификатор старой связи',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveUslugaComplexComposition' => array(
			array(
				'field' => 'UslugaComplexComposition_id',
				'label' => 'Идентификатор вхождения услуги в состав комплексной услуги',
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
				'field' => 'UslugaComplex_pid',
				'label' => 'Идентификатор родительской услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLinkedUslugaGrid' => array(
			array(
				'field' => 'deniedCategoryList',
				'label' => 'Список недоступных категорий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'noLPU',
				'label' => 'Признак отказа от загрузки услуг ЛПУ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUslugaComplexAttributeGrid' => array(
			array(
				'field' => 'uslugaComplexList',
				'label' => 'Список идентификаторов услуг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnUslugaPar_ids',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadUslugaComplexProfileGrid' => array(
			array(
				'field' => 'uslugaComplexList',
				'label' => 'Список идентификаторов услуг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUslugaComplexPlaceGrid' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuEditFlag',
				'label' => 'Флаг прогрузки из структуры ЛПУ',
				'rules' => '',
				'default' => 0,
				'type' => 'int'
			)
		),
		'loadUslugaComplexTariffGrid' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuEditFlag',
				'label' => 'Флаг прогрузки из структуры ЛПУ',
				'rules' => '',
				'default' => 0,
				'type' => 'int'
			)
		),
		'loadUslugaComplexEditForm' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUslugaComplexGroupEditForm' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUslugaComplexTree' => array(
			array(
				'default' => 0,
				'field' => 'level',
				'label' => 'Уровень',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_uid',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор комплексной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexLevel_id',
				'label' => 'Уровень комплексной услуги',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadUslugaContentsTree' => array(
			array(
				'field' => 'UslugaComplex_pid',
				'label' => 'Идентификатор комплексной услуги',
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
			)
		),
		'loadUslugaComplexMedServiceTree' => array(
			array(
				'field' => 'UslugaComplexMedService_pid',
				'label' => 'Идентификатор комплексной услуги на службе',
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
			)
		),
		'loadUslugaContentsGrid' => array(
			array(
				'field' => 'contents',
				'label' => 'Признак необходимости загрузить состав услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isClose',
				'label' => 'Признак закрытой услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'paging',
				'label' => 'Признак необходимости загрузить состав услуги в грид с постраничным выводом',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_uid',
				'label' => 'Идентификатор ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_pid',
				'label' => 'Идентификатор комплексной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_CodeName',
				'label' => 'Код и имя услуги',
				'rules' => '',
				'type' => 'string'
			),
			// Параметры страничного вывода
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveUslugaComplexGroup' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_begDate',
				'label' => 'Дата начала',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplex_Code',
				'label' => 'Код',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_endDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
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
				'label' => 'Идентификатор комплексной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_Name',
				'label' => 'Наименование',
				'rules' => 'trim|required',
				'type' => 'string'
			)
		),
		'saveUslugaComplex' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_ACode',
				'label' => 'Код подстановки в шаблон',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_begDate',
				'label' => 'Дата начала',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplex_cid',
				'label' => 'Идентификатор услуги, в состав которой добавляется сохраняемая услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_Code',
				'label' => 'Код',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_endDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
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
				'label' => 'Идентификатор комплексной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_Name',
				'label' => 'Наименование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_Nick',
				'label' => 'Краткое наименование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_UET',
				'label' => 'УЕТ',
				'rules' => 'trim',
				'type' => 'float'
			),
			array(
				'field' => 'linkedUslugaComplexData',
				'label' => 'Данные таблицы "Связанные услуги"',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'uslugaComplexCompositionData',
				'label' => 'Данные таблицы "Состав услуги"',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'uslugaComplexAttributeData',
				'label' => 'Данные таблицы "Атрибуты"',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'uslugaComplexPlaceData',
				'label' => 'Данные таблицы "Места оказания"',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'uslugaComplexTariffData',
				'label' => 'Данные таблицы "Тарифы"',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'uslugaComplexProfileData',
				'label' => 'Данные таблицы "Профили"',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'XmlTemplate_id',
				'label' => 'Шаблон услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_isPackage',
				'label' => 'Это пакет услуг',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'UslugaComplexInfo_id',
				'label' => 'Идентификатор описания услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexInfo_ImportantInfo',
				'label' => 'Важная информация',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_RecipientCat',
				'label' => 'Категиории получателей',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_DocumentUsluga',
				'label' => 'Документы, необходимые для получения услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_Limit',
				'label' => 'Ограничения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_PayOrder',
				'label' => 'Порядок оплаты услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_QueueType',
				'label' => 'Способ записи',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_ServiceOrder',
				'label' => 'Порядок оказания услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_Duration',
				'label' => 'Продолжительность',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexInfo_Result',
				'label' => 'Результат',
				'rules' => '',
				'type' => 'string'
			),
		),
		'deleteUslugaComplexPlace' => array(
			array(
				'field' => 'UslugaComplexPlace_id',
				'label' => 'Идентификатор места оказания услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveUslugaComplexPlace' => array(
			array(
				'field' => 'UslugaComplexPlace_id',
				'label' => 'Идентификатор места оказания услуги',
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
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexPlace_begDate',
				'label' => 'Дата начала',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplexPlace_endDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
				'type' => 'date'
			)
		),
		'deleteUslugaComplexTariff' => array(
			array(
				'field' => 'UslugaComplexTariff_id',
				'label' => 'Идентификатор тарифа услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveUslugaComplexTariff' => array(
			array(
				'field' => 'UslugaComplexTariff_id',
				'label' => 'Идентификатор тарифа услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreEndDate',
				'label' => 'Флаг игнорирования предупреждения о дате закрытия',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexTariff_Tariff',
				'label' => 'Тариф',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'UslugaComplexTariff_UED',
				'label' => 'Тариф',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'UslugaComplexTariff_UEM',
				'label' => 'Тариф',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'UslugaComplexTariff_Code',
				'label' => 'Код',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'UslugaComplexTariff_Name',
				'label' => 'Наименование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexTariff_begDate',
				'label' => 'Дата начала',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplexTariff_endDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_id',
				'label' => 'Группа отделений ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuLevel_id',
				'label' => 'Уровень ЛПУ',
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
				'field' => 'LpuUnitType_id',
				'label' => 'Вид мед. помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MesAgeGroup_id',
				'label' => 'Возрастная группа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VizitClass_id',
				'label' => 'Вид посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexTariffType_id',
				'label' => 'Тип тарифа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUslugaComplexTariffOnPlaceGrid' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
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
				'field' => 'LpuBuilding_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexPlace_id',
				'label' => 'Место оказания услуги / услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isClose',
				'label' => 'Флаг закрытия',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getUslugaComplexTariffMaxDate' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexTariff_id',
				'label' => 'Идентификатор тарифа услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getUslugaComplexMedServiceCompositionList' => array(
			array(
				'field' => 'UslugaComplexMedService_pid',
				'label' => 'Идентификатор основной услуги',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'checkUslugaComplexTariffUsedInEvnUsluga' => array(
			array(
				'field' => 'UslugaComplexTariff_id',
				'label' => 'Идентификатор тарифа услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadUslugaComplexOnPlaceGrid' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isClose',
				'label' => 'Признак закрытой услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'UslugaComplex_CodeName',
				'label' => 'Код и имя услуги',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getUslugaComplexAttributes' => array(
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('UslugaComplex_model', 'dbmodel');
	}

	/**
	 * Загрузка комбо групп услуг
	 */
	function loadUslugaComplexGroupList() {
		$data = $this->ProcessInputData('loadUslugaComplexGroupList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexGroupList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка услуг по идентификатору/коду МЭС
	 */
	public function loadUslugaComplexListForMes() {
		$data = $this->ProcessInputData('loadUslugaComplexListForMes', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexListForMes($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Проверка использования тарифа в случаях оказания услуг
	 */
	function checkUslugaComplexTariffUsedInEvnUsluga() {
		$data = $this->ProcessInputData('checkUslugaComplexTariffUsedInEvnUsluga', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->checkUslugaComplexTariffUsedInEvnUsluga($data);
		$response = array('check' => $response);
		$this->ProcessModelSave($response, true, 'Ошибки при проверке использования тарифа')->ReturnData();

		return true;
	}

	/**
	 *	Удаление тарифа услуги
	 */
	function deleteUslugaComplexTariff() {
		$data = $this->ProcessInputData('deleteUslugaComplexTariff', true);
		if ( $data === false ) { return false; }

		// если не суперадмин то проверяем возможность редактирования места оказания услуги
		if (!isSuperadmin()) {
			if (!$this->dbmodel->checkUslugaComplexTariffCanSave($data)) {
				$this->ReturnError('Нельзя удалять тарифы чужого ЛПУ');
				return false;
			}
		}
		
		if ($this->dbmodel->checkUslugaComplexTariffUsedInEvnUsluga($data)) {
			$this->ReturnError('Тариф используется в событиях оказания услуг, удаление невозможно.');
			return false;
		}
		
		$response = $this->dbmodel->deleteUslugaComplexTariff($data);
		$this->ProcessModelSave($response, true, 'При удалении тарифа возникли ошибки')->ReturnData();
		
		return true;
	}
	
	/**
	 *	Удаление места оказания услуги
	 */
	function deleteUslugaComplexPlace() {
		$data = $this->ProcessInputData('deleteUslugaComplexPlace', true);
		if ( $data === false ) { return false; }

		// если не суперадмин то проверяем возможность редактирования места оказания услуги
		if (!isSuperadmin()) {
			if (!$this->dbmodel->checkUslugaComplexPlaceCanSave($data)) {
				$this->ReturnError('Нельзя удалять места оказания чужого ЛПУ');
				return false;
			}
		}
		
		$response = $this->dbmodel->deleteUslugaComplexPlace($data);
		$this->ProcessModelSave($response, true, 'При удалении места оказания услуги ошибки')->ReturnData();
		
		return true;
	}
	
	/**
	 *	Проверка на пересекающийся тариф
	 */
	function checkUslugaComplexTariffHasDuplicate(){
		$data = $this->ProcessInputData('saveUslugaComplexTariff', false);
		if ( $data === false ) { return false; }
		if ($this->dbmodel->checkUslugaComplexTariffHasDuplicate($data)) {
			$this->ReturnError('Существует пересекающийся по дате тариф с тем же набором параметров, что и сохраняемый');
			return false;
		}
		$this->ReturnData(array("success"=>true));
	}
	
	/**
	 * Сохранение тарифа услуги
	 */
	function saveUslugaComplexTariff() {
		$data = $this->ProcessInputData('saveUslugaComplexTariff', false);
		if ( $data === false ) { return false; }
		
		$sp = getSessionParams();
		$data['Server_id'] = $sp['Server_id'];
		$data['pmUser_id'] = $sp['pmUser_id'];

		// если не суперадмин то проверяем возможность редактирования места оказания услуги
		if (!isSuperadmin() && !empty($data['UslugaComplexTariff_id'])) {
			if (!$this->dbmodel->checkUslugaComplexTariffCanSave($data)) {
				$this->ReturnError('Нельзя редактировать тарифы чужого ЛПУ');
				return false;
			}
		}
		
		// если не суперадмин и PayType_id == 1 (ОМС) то проверяем есть ли уже открытый тариф с Lpu_id = NULL
		if (!isSuperadmin() && $data['PayType_id'] == 1) {
			if ($this->dbmodel->checkUslugaComplexTariffHasOMSBySuperAdmin($data)) {
				$this->ReturnError('Нельзя добавлять тарифы ОМС, если есть действующий тариф ОМС, заведённый администратором ЦОД.');
				return false;
			}
		}
		
		// Запретить сохранение при совпадении всех параметров (кроме УЕТ и тарифа) с существующей записью. (refs #16927)
		if ($this->dbmodel->checkUslugaComplexTariffHasDuplicate($data)) {
			$this->ReturnError('Существует пересекающийся по дате тариф с тем же набором параметров, что и сохраняемый');
			return false;
		}
		
		// При закрытии тарифа выдавать предупреждение, если найдены услуги с датой оказания после закрытия тарифа (refs #16927)
		if (!empty($data['UslugaComplexTariff_id']) && !empty($data['UslugaComplexTariff_endDate']) && !$data['ignoreEndDate']) {
			if ($this->dbmodel->checkUslugaComplexTariffUsedInEvnUsluga($data)) {
				$this->ReturnError('Существуют услуги с датой оказания после даты закрытия тарифа', 11);
				return false;
			}
		}
		
		$response = $this->dbmodel->saveUslugaComplexTariff($data);
		$this->ProcessModelSave($response, true, 'При сохранении тарифа возникли ошибки')->ReturnData();
		
		return true;
	}
	
	/**
	 * Сохранение места оказания услуги
	 */
	function saveUslugaComplexPlace() {
		$data = $this->ProcessInputData('saveUslugaComplexPlace', true);
		if ( $data === false ) { return false; }

		// если не суперадмин то проверяем возможность редактирования места оказания услуги
		if (!isSuperadmin() && !empty($data['UslugaComplexPlace_id'])) {
			if (!$this->dbmodel->checkUslugaComplexPlaceCanSave($data)) {
				$this->ReturnError('Нельзя редактировать места оказания чужого ЛПУ');
				return false;
			}
		}
		
		if ($this->dbmodel->checkUslugaComplexPlaceExist($data)) {
			$this->ReturnError('Указанное место оказания услуги уже существует');
			return false;
		}
		
		$response = $this->dbmodel->saveUslugaComplexPlace($data);
		$this->ProcessModelSave($response, true, 'При сохранении места оказания услуги ошибки')->ReturnData();
		
		return true;
	}

	/**
	 * Удаление услуги
	 */
	function deleteUslugaComplex() {
		$data = $this->ProcessInputData('deleteUslugaComplex', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteUslugaComplex($data);
		$this->ProcessModelSave($response, true, 'При удалении услуги возникли ошибки')->ReturnData();
		
		return true;
	}

	/**
	 * Удаление услуги на службе
	 */
	function deleteUslugaComplexMedService() {
		$data = $this->ProcessInputData('deleteUslugaComplexMedService', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteUslugaComplexMedService($data);
		$this->ProcessModelSave($response, true, 'При удалении услуги возникли ошибки')->ReturnData();
		
		return true;
	}

	/**
	 * Удаление связанной услуги
	 */
	function deleteLinkedUslugaComplex() {
		$data = $this->ProcessInputData('deleteLinkedUslugaComplex', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteLinkedUslugaComplex($data);
		$this->ProcessModelSave($response, true, 'При удалении связанной услуги возникли ошибки')->ReturnData();
		
		return true;
	}


	/**
	 * Удаление услуги из состава комплексной услуги
	 */
	function deleteUslugaComplexComposition() {
		$data = $this->ProcessInputData('deleteUslugaComplexComposition', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteUslugaComplexComposition($data);
		$this->ProcessModelSave($response, true, 'При удалении услуги из состава комплексной услуги возникли ошибки')->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка комплексных услуг для комбо UslugaComplexAllCombo.
	*  Входящие данные: ..
	*  Используется: форма редактирования состава услуги
	*/	
	function loadUslugaComplexCombo() {
		$data = $this->ProcessInputData('loadUslugaComplexCombo', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	*  Получение списка комплексных услуг для комбо UslugaComplexSmpCombo.
	*/	
	function loadUslugaSMPCombo() {
		$data = $this->ProcessInputData('loadUslugaSMPCombo', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaSMPCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Получение списка комплексных услуг для комбо loadUslugaComplexGost.
	*  Входящие данные: ..
	*  Используется: форма редактирования состава услуги
	*/
	function loadUslugaComplexGost() {
		$data = $this->ProcessInputData('loadUslugaComplexGost', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexGost($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Сохранение связанной услуги
	*  Входящие данные: $_POST['UslugaComplex_id'], $_POST['UslugaComplex_pid']
	*  Используется: форма редактирования состава услуги
	*/
	function saveUslugaComplexLinked() {
		$data = $this->ProcessInputData('saveUslugaComplexLinked', true);
		if ( $data === false ) { return false; }
		
		$data['deniedCategoryList'] = $this->dbmodel->checkUslugaComplexCanBeLinked($data);
		
		if (is_array($data['deniedCategoryList'])) {
			$response = $this->dbmodel->saveUslugaComplexLinked($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении связанной услуги')->ReturnData();
		} else {
			$this->ReturnError('Ошибка при проверке на возможность добавления связанной услуги');
		}

		return true;
	}
	
	/**
	*  Сохранение связанной услуги
	*  Входящие данные: $_POST['UslugaComplex_id'], $_POST['UslugaComplex_pid']
	*  Используется: форма редактирования состава услуги
	*/
	function saveUslugaComplexMedService() {
		$data = $this->ProcessInputData('saveUslugaComplexMedService', true);
		if ( $data === false ) { return false; }
		
		if (!empty($data['UslugaComplexMedService_id']) || !$this->dbmodel->checkUslugaComplexInMedService($data)) {
			$response = $this->dbmodel->saveUslugaComplexMedService($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении услуги на службу')->ReturnData();
		} else {
			$this->ReturnError('Услуга уже присутствует в списке');
		}

		return true;
	}
	
	/**
	*  Сохранение услуги входящей в состав комплексной услуги
	*  Входящие данные: $_POST['UslugaComplex_id'], $_POST['UslugaComplex_pid']
	*  Используется: форма редактирования состава услуги
	*/
	function saveUslugaComplexComposition() {
		$data = $this->ProcessInputData('saveUslugaComplexComposition', true);
		if ( $data === false ) { return false; }
		
		if (!$this->dbmodel->checkUslugaComplexHasComposition($data)) {
			$response = $this->dbmodel->saveUslugaComplexComposition($data);
			$this->ProcessModelSave($response, true, 'Ошибка при добавлении услуги в состав услуги')->ReturnData();
		} else {
			$this->ReturnError('Услуга уже присутствует в составе');
		}

		return true;
	}


	/**
	 *	Получение списка связанных услуг
	 */
	function loadLinkedUslugaGrid() {
		$data = $this->ProcessInputData('loadLinkedUslugaGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadLinkedUslugaGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 *	Получение списка атрибутов услуги
	 */
	function loadUslugaComplexAttributeGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexAttributeGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexAttributeGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 *	Получение списка профилей услуги
	 */
	function loadUslugaComplexProfileGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexProfileGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexProfileGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 *	Получение списка мест выполнения услуги
	 */
	function loadUslugaComplexPlaceGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexPlaceGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexPlaceGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *	Получение состава услуги
	 */
	function getUslugaComplexMedServiceCompositionList() {
		$data = $this->ProcessInputData('getUslugaComplexMedServiceCompositionList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getUslugaComplexMedServiceCompositionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *	Получение списка услуг на определённом месте выполнения
	 */
	function loadUslugaComplexOnPlaceGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexOnPlaceGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexOnPlaceGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *	Получение списка услуг на службе
	 */
	function loadUslugaComplexMedServiceGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexMedServiceGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexMedServiceGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *	Получение списка тарифов по услуге на определённом месте выполнения
	 */
	function loadUslugaComplexTariffOnPlaceGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexTariffOnPlaceGrid', false);
		if ( $data === false ) { return false; }

		$sessionParams = getSessionParams();
		$data['session'] = $sessionParams['session'];
		
		$response = $this->dbmodel->loadUslugaComplexTariffOnPlaceGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *	Получение максимальной даты последней услуги по тарифу для редактирования на форме
	 */
	function getUslugaComplexTariffMaxDate() {
		$data = $this->ProcessInputData('getUslugaComplexTariffMaxDate', false);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->getUslugaComplexTariffMaxDate($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *	Получение списка тарифов по услуге
	 */
	function loadUslugaComplexTariffGrid() {
		$data = $this->ProcessInputData('loadUslugaComplexTariffGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexTariffGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка услуг для выбора из состава пакета или по МЭС для формы оказания услуг
	 */
	function loadForSelect() {
		$this->inputRules['loadForSelect'] = $this->dbmodel->getInputRules('loadForSelect');
		$data = $this->ProcessInputData('loadForSelect', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadForSelect($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Получение данных для формы редактирования услуги
	*  Входящие данные: $_POST['UslugaComplex_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования услуги
	*/
	function loadUslugaComplexEditForm() {
		$data = $this->ProcessInputData('loadUslugaComplexEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	*  Получение данных для формы редактирования группы услуг
	*  Входящие данные: $_POST['UslugaComplex_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования услуги
	*/
	function loadUslugaComplexGroupEditForm() {
		$data = $this->ProcessInputData('loadUslugaComplexGroupEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaComplexGroupEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 *	Функция читает ветку дерева услуг
	 */
	function loadUslugaComplexTree() {
		$data = $this->ProcessInputData('loadUslugaComplexTree', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexTree($data);
		$this->ProcessModelList($response, true, true);

		// Обработка для дерева 
		$field = array(
			'id' => 'id', 
			'name' => 'name',
			'code' => 'code',
			'iconCls' => 'folder16',
			'leaf' => false, 
			'cls' => 'folder'
		);

		$this->ReturnData($this->getTreeNodes($this->OutData, $field, $data['level'], ""));

		return true;
	}


	/**
	 *	Получение состава комплексной услуги
	 */
	function loadUslugaContentsGrid() {
		$data = $this->ProcessInputData('loadUslugaContentsGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaContentsGrid($data);

		if ( $data['paging'] == 2 ) {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
		else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	
	/**
	 * Формирование элементов дерева из записей таблицы
	 */
	function getUslugaComplexContentsTreeNodes($nodes, $field, $level, $dop="", $check=0)
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
	 *	Получение состава комплексной услуги для дерева
	 */
	function loadUslugaContentsTree() {
		$data = $this->ProcessInputData('loadUslugaContentsTree', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaContentsTree($data);
		
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
		$this->ReturnData($this->getUslugaComplexContentsTreeNodes($this->OutData, $field, $data['level'], "", $data['check']));
		return true;
	}
	
	/**
	 * Формирование элементов дерева из записей таблицы
	 */
	function getUslugaComplexMedServiceTreeNodes($nodes, $field, $level, $dop="", $check=0)
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
					'id' => $rows[$field['id']],
					'UslugaComplex_id' => (empty($rows['UslugaComplex_id']))?null:$rows['UslugaComplex_id'],
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
	 *	Получение состава комплексной услуги на службе для дерева
	 */
	function loadUslugaComplexMedServiceTree() {
		$data = $this->ProcessInputData('loadUslugaComplexMedServiceTree', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadUslugaComplexMedServiceTree($data);
		
		$this->ProcessModelList($response, true, true);
		// Обработка для дерева 
		$field = Array(
			'object' => "UslugaComplexMedService",
			'id' => "UslugaComplexMedService_id", 
			'name' => "UslugaComplex_Name", 
			'comment' => "LpuSection_Name", 
			'code' => "UslugaComplex_Code", 
			'iconCls' => 'uslugacomplex-16', 
			'uiProvider' => 'tristate',
			'checked' => false,
			'leaf' => false, 
			'cls' => "folder"
		);
		$this->ReturnData($this->getUslugaComplexMedServiceTreeNodes($this->OutData, $field, $data['level'], "", $data['check']));
		return true;
	}

	/**
	*  Сохранение услуги
	*  На выходе: JSON-строка
	*  Используется: форма редактирования услуги
	*/
	function saveUslugaComplex() {
		$data = $this->ProcessInputData('saveUslugaComplex', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveUslugaComplex($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении услуги')->ReturnData();
		return true;
	}

	/**
	*  Сохранение группы услуг
	*  На выходе: JSON-строка
	*  Используется: форма редактирования группы услуг
	*/
	function saveUslugaComplexGroup() {
		$data = $this->ProcessInputData('saveUslugaComplexGroup', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveUslugaComplexGroup($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении группы услуг')->ReturnData();
		return true;
	}


	/**
	 * Формирование элементов дерева из записей таблицы
	 */
	function getTreeNodes($nodes, $field, $level, $dop = "", $check = 0) {
		$val = array();
		$i = 0;

		if ( is_array($nodes) && count($nodes) > 0 ) {
			foreach ( $nodes as $rows ) {
				if ( array_key_exists('ChildrensCount', $rows) ) {
					$field['leaf'] = ($rows['ChildrensCount'] == 0 ? true : false);
				}

				$node = array(
					'id' => $rows[$field['id']],
					'object' => $rows['object'],
					'object_id' => $field['id'],
					'object_value' => $rows[$field['id']],
					'object_code' => $rows[$field['code']],
					'text' => (!empty($rows[$field['code']]) ? $rows[$field['code']] . ' ' : '') . $rows[$field['name']],
					'UslugaCategory_id' => $rows['UslugaCategory_id'],
					'UslugaCategory_SysNick' => $rows['UslugaCategory_SysNick'],
					'UslugaComplexLevel_id' => $rows['UslugaComplexLevel_id'],
					'leaf' => $rows['leaf'],
					'iconCls' => (empty($rows['iconCls']) ? $field['iconCls'] : $rows['iconCls']),
					'cls' => $field['cls']
				);

				$val[] = $node;
			}
		}

		return $val;
	}

	/**
	 * Получение списка аттрибутов комплесной услуги
	 * 192492
	 */
	function getUslugaComplexAttributes() {
		$data = $this->ProcessInputData('getUslugaComplexAttributes');
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getUslugaComplexAttributes($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}	
}
