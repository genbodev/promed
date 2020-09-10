<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Rls - контроллер для работы со справочником РЛС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Storozhev
* @version      27.04.2011
* @property Rls_model $dbmodel
*/

class Rls extends swController {

	public $inputRules = array(
		'searchData' => array(
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
			),
			array(
				'default' => 'off',
				'field' => 'onlySQL',
				'label' => 'Вывести SQL-запрос',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'TRADENAMES_ID',
				'label' => 'Торг. название',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RlsFirms_id',
				'label' => 'Фирма',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RlsActmatters_id',
				'label' => 'Действующее вещество',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsDesctextes_id',
				'label' => 'Фарм действие',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsSynonim_id',
				'label' => 'Синоним',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsCountries_id',
				'label' => 'Страна',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsPharmagroup_id',
				'label' => 'Фармгруппа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsClsiic_id',
				'label' => 'Нозология (МКБ-10)',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsClsatc_id',
				'label' => 'АТХ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'CLS_MZ_PHGROUP_ID',
				'label' => 'ФТГ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsClsdrugforms_id',
				'label' => 'Лекарственная форма',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsDaterange',
				'label' => 'Диапазон даты регистрации',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'RlsRegnum',
				'label' => '№ РУ',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RlsRegOwnerFirm',
				'label' => 'Владелец РУ',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RlsProdFirm',
				'label' => 'Производитель',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RlsPackFirm',
				'label' => 'Упаковщик',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'check_0_1',
				'label' => 'Отпуск без рецепта',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_0_2',
				'label' => 'Жизненно-важные',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_0_3',
				'label' => 'ЛЛО',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_0_4',
				'label' => 'Наркотические',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_0_5',
				'label' => 'Сильнодействующие',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_1',
				'label' => 'Состав и форма выпуска',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_2',
				'label' => 'Описание лекарственной формы',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_3',
				'label' => 'Характеристика',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_4',
				'label' => 'Фармакологическое действие',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_5',
				'label' => 'Действие на организм',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_6',
				'label' => 'Свойства компонентов',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_7',
				'label' => 'Фармакокинетика',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_8',
				'label' => 'Фармакодинамика',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_1_9',
				'label' => 'Клиническая фармакология',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_1',
				'label' => 'Показания',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_2',
				'label' => 'Рекомендуется',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_3',
				'label' => 'Противопоказания',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_4',
				'label' => 'Применение при беременности и кормление грудью',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_5',
				'label' => 'Побочные действия',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_6',
				'label' => 'Взаимодействие',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_7',
				'label' => 'Способ применения и дозы',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_8',
				'label' => 'Инструкция для пациента',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_2_9',
				'label' => 'Передозировка',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_1',
				'label' => 'Меры предосторожности',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_2',
				'label' => 'Особые указания',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_3',
				'label' => 'Производитель',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_4',
				'label' => 'Литература',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_5',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_6',
				'label' => 'Фармакология',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_7',
				'label' => 'Применение',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'check_3_8',
				'label' => 'Ограничения к применению',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'sevennozology',
				'label' => '7 нозологий',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'RlsSearchKeyWord',
				'label' => 'Фильтр: ключевое слово',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RlsSearchKodEAN',
				'label' => 'Фильтр: код EAN',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'DrugNonpropNames_id',
				'label' => 'Фильтр: непатентованное наименование',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RlsTorgNamesFilter_type',
				'label' => 'Фильтр: тип препарата',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'KeyWord_filter',
				'label' => 'Фильтр: ключевое слово',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'FormSign',
				'label' => 'Метка фориы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'level',
				'label' => 'Уровень узла древовидной структуры',
				'rules' => '',
				'type' => 'id'
			),
			//<!-- это фильтры для формы поиска производителя
			array(
				'field' => 'Firm_Name',
				'label' => 'наименование производителя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Firm_Address',
				'label' => 'адрес производителя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RlsDrug_Dose',
				'label' => 'Дозировка',
				'rules' => '',
				'type' => 'string'
			),
			// -->
			array(
				'field' => 'id',
				'label' => 'ключ',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getTorgNames' => array(
			array(
				'field' => 'where',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'getDataForComboStore' => array(
			array(
				'field' => 'object',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'stringfield',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'codeField',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'where',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'lowercase',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			)
		),
		'getDataForComboStoreWithFields' => array(
			array(
				'field' => 'object',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'stringfield',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'codeField',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'where',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'lowercase',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'additionalFields',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'loadSearchFirmsGrid' => array(
			array(
				'field' => 'Firm_Name',
				'label' => 'Название организации',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Firm_Address',
				'label' => 'Адрес',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'saveFirm' => array(
			array(
				'field' => 'FIRMS_ID',
				'label' => 'Идентификатор фирмы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FIRMNAMES_ID',
				'label' => 'Идентификатор названия фирмы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FIRMS_NAME',
				'label' => 'Полное название',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'FIRMS_COUNTID',
				'label' => 'Страна',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'FIRMS_ADRMAIN',
				'label' => 'Адрес основного офиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'FIRMS_ADRRUSSIA',
				'label' => 'Адрес в России',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getFirm' => array(
			array(
				'field' => 'FIRMS_ID',
				'label' => 'Идентификатор фирмы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FIRMS_NAME',
				'label' => 'Наименование фирмы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'forCombo',
				'label' => 'Флаг - для комбо',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteFirm' => array(
			array(
				'field' => 'FIRMS_ID',
				'label' => 'Идентификатор фирмы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'GetRlsPackCode' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор торг названия (для получ упаковок)',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'GetRlsTorgView' => array(
			array(
				'field' => 'NOMEN_ID',
				'label' => 'Идентификатор упаковки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPrep' => array(
			array(
				'field' => 'Nomen_id',
				'label' => 'Идентификатор упаковки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrepType_id',
				'label' => 'Идентификатор типа препарата',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'GetRlsPharmacologyStructure' => array(
			array(
				'field' => 'node',
				'label' => 'Идентификатор корня дерева',
				'rules' => '',
				'type' => 'string'
			)
		),
		'GetRlsNozologyStructure' => array(
			array(
				'field' => 'node',
				'label' => 'Идентификатор корня дерева',
				'rules' => '',
				'type' => 'string'
			)
		),
		'GetRlsAtxStructure' => array(
			array(
				'field' => 'node',
				'label' => 'Идентификатор корня дерева',
				'rules' => '',
				'type' => 'string'
			)
		),
		'GetRlsAtxView' => array(
			array(
				'field' => 'node',
				'label' => 'Идентификатор корня дерева',
				'rules' => '',
				'type' => 'string'
			)
		),
		'GetRlsAtxList' => array(
			array(
				'field' => 'maxCodeLength',
				'label' => 'Максимальная длина кода',
				'rules' => '',
				'type' => 'int'
			)
		),
		'GetRlsNozologyView' => array(
			array(
				'field' => 'node',
				'label' => 'Идентификатор корня дерева',
				'rules' => '',
				'type' => 'string'
			)
		),
		'GetRlsPharmacologyView' => array(
			array(
				'field' => 'node',
				'label' => 'Идентификатор корня дерева',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'view',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'string'
			)
		),
		'GetRlsActmattersView' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор действующего вещества',
				'rules' => '',
				'type' => 'int'
			)
		),
		'GetRlsFirmsView' => array(
			array(
				'field' => 'id',
				'label' => 'Производитель',
				'rules' => '',
				'type' => 'int'
			)
		),
		'GetRlsPharmagroupList' => array(
			array(
				'field' => 'RlsPharmagroup_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'int'
			)
		),
		'GetRlsClsMzPhgroupList' => array(
			array(
				'field' => 'RlsClsMzPhgroup_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'int'
			)
		),
		'savePrep' => array(
			array('field' => 'Prep_id', 'label' => 'Препарат', 'rules' => '', 'type' => 'id'),
			array('field' => 'CLSNTFR_ID', 'label' => 'НТФР', 'rules' => '', 'type' => 'id'),
			array('field' => 'REGCERT_ID', 'label' => 'Регистратор', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'TRADENAMES_ID', 'label' => 'Торговое название', 'rules' => '', 'type' => 'id'),
			array('field' => 'TRADENAMES_NAME', 'label' => 'Торговое название', 'rules' => '', 'type' => 'string'),
			array('field' => 'LATINNAMES_ID', 'label' => 'Латинское название', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'LATINNAMES_NAME', 'label' => 'Латинское название', 'rules' => '', 'type' => 'string'),
			array('field' => 'LATINNAMES_NameGen', 'label' => 'Латинское название род.п.', 'rules' => '', 'type' => 'string'),
			array('field' => 'CLSDRUGFORMS_ID', 'label' => 'Лекарственная форма', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'DFMASS', 'label' => 'Кол-во ЛФ', 'rules' => '', 'type' => 'float'), //MASSUNITS
			array('field' => 'DFMASSID', 'label' => 'Название ЛФ', 'rules' => '', 'type' => 'id', 'default' => 0), //MASSUNITS
			array('field' => 'DFCONC', 'label' => 'Кол-во ЛФ', 'rules' => '', 'type' => 'float'), //CONCENUNITS
			array('field' => 'DFCONCID', 'label' => 'Название ЛФ', 'rules' => '', 'type' => 'id', 'default' => 0), //CONCENUNITS
			array('field' => 'DFACT', 'label' => 'Кол-во ЛФ', 'rules' => '', 'type' => 'float'), //ACTUNITS
			array('field' => 'DFACTID', 'label' => 'Название ЛФ', 'rules' => '', 'type' => 'id', 'default' => 0), //ACTUNITS
			array('field' => 'DRUGDOSE', 'label' => 'Кол-во доз в упаковке', 'rules' => '', 'type' => 'int'),
			array('field' => 'DRUGLIFETIME_ID', 'label' => 'Срок годности', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'DRUGLIFETIME_TEXT', 'label' => 'Срок годности', 'rules' => '', 'type' => 'string'),
			array('field' => 'DRUGSTORCOND_ID', 'label' => 'Условия хранения', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'DRUGSTORCOND_TEXT', 'label' => 'Условия хранения', 'rules' => '', 'type' => 'string'),
			array('field' => 'DFSIZE', 'label' => 'Размеры ЛФ', 'rules' => '', 'type' => 'string'),
			array('field' => 'DFSIZEID', 'label' => 'Название размера ЛФ', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'DFCHARID', 'label' => 'Название характеристики ЛФ', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'PrepType_id', 'label' => 'Тип препарата', 'rules' => '', 'type' => 'id'),
			array('field' => 'IDENT_WIND_STR_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			
			array('field' => 'NOMEN_ID', 'label' => 'Упаковка', 'rules' => '', 'type' => 'id'),
			array('field' => 'NOMEN_DRUGSINPPACK', 'label' => 'Кол-во преп. в перв. упаковке', 'rules' => '', 'type' => 'int'),
			array('field' => 'NOMEN_PPACKID', 'label' => 'Название перв. упаковки', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'NOMEN_PPACKVOLUME', 'label' => 'Кол-во', 'rules' => '', 'type' => 'string'), //CUBICUNITS
			array('field' => 'NOMEN_PPACKCUBUNID', 'label' => 'Название', 'rules' => '', 'type' => 'id', 'default' => 0), //CUBICUNITS
			array('field' => 'NOMEN_PPACKMASS', 'label' => 'Кол-во', 'rules' => '', 'type' => 'string'), //MASSUNITS2
			array('field' => 'NOMEN_PPACKMASSUNID', 'label' => 'Название', 'rules' => '', 'type' => 'id', 'default' => 0), //MASSUNITS2
			array('field' => 'NOMEN_SETID', 'label' => 'Назв. комплекта к перв. упаковке', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'NOMEN_PPACKINUPACK', 'label' => 'Кол-во перв. упаковок во втор.', 'rules' => '', 'type' => 'int'),
			array('field' => 'NOMEN_UPACKID', 'label' => 'Название втор. упаковки', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'NOMEN_UPACKINSPACK', 'label' => 'Кол-во втор. упаковок во трет.', 'rules' => '', 'type' => 'int'),
			array('field' => 'NOMEN_SPACKID', 'label' => 'Название трет. упаковки', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'FIRMS_ID', 'label' => 'Производитель', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegOwner', 'label' => 'Владелец РУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Manufacturer', 'label' => 'Производитель', 'rules' => '', 'type' => 'id'),
			array('field' => 'Packer', 'label' => 'Упаковщик', 'rules' => '', 'type' => 'id'),
			array('field' => 'NOMEN_EANCODE', 'label' => 'Код EAN', 'rules' => '', 'type' => 'string'),
			array('field' => 'REGCERT_REGNUM', 'label' => 'Номер регистрации', 'rules' => '', 'type' => 'string'),
			array('field' => 'REGCERT_REGDATE', 'label' => 'Дата регистрации', 'rules' => '', 'type' => 'date'),
			array('field' => 'REGCERT_ENDDATE', 'label' => 'Дата прекр. срока действия', 'rules' => '', 'type' => 'date'),
			array('field' => 'REGCERT_REGDATERange', 'label' => 'Период регистрации', 'rules' => '', 'type' => 'daterange'),
			array('field' => 'Reregdate', 'label' => 'Дата перерегистрации', 'rules' => '', 'type' => 'date'),
			array('field' => 'REGCERT_excDT', 'label' => 'Дата исключения', 'rules' => '', 'type' => 'date'),
			array('field' => 'CLSATC_ID', 'label' => 'Классификация АТХ', 'rules' => '', 'type' => 'id'),
			//array('field' => 'CLSIIC_ID', 'label' => 'Классификация МКБ-10', 'rules' => '', 'type' => 'id'),
			array('field' => 'CLSIICS', 'label' => 'Классификация МКБ-10', 'rules' => '', 'type' => 'string'),
			array('field' => 'CLSPHARMAGROUP_ID', 'label' => 'Фармакологическая группа', 'rules' => '', 'type' => 'id'),
			array('field' => 'TN_DF_LIMP', 'label' => 'Относится ли к жизненноважным лек. средствам по классиф. МЗ РФ по торг. названиям', 'rules' => '', 'type' => 'int'),
			array('field' => 'TRADENAMES_DRUGFORMS', 'label' => 'Является ли препаратом льготного ассортимента через торг. название и лек. форму', 'rules' => '', 'type' => 'int'),
			
			array('field' => 'DESCRIPTIONS_ID', 'label' => 'Ид. описания', 'rules' => '', 'type' => 'id'),
			array('field' => 'DESCTEXTES_COMPOSITION', 'label' => 'Состав и форма выпуска', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_CHARACTERS', 'label' => 'Характеристика', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_PHARMAACTIONS', 'label' => 'Фармакологическое действие', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_ACTONORG', 'label' => 'Действие на организм', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_COMPONENTSPROPERTIES', 'label' => 'Свойства компонентов', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_PHARMAKINETIC', 'label' => 'Фармакокинетика', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_PHARMADYNAMIC', 'label' => 'Фармакодинамика', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_CLINICALPHARMACOLOGY', 'label' => 'Клиническая фармакология', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_DIRECTION', 'label' => 'Инструкция', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_INDICATIONS', 'label' => 'Показания', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_RECOMMENDATIONS', 'label' => 'Рекомендуется', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_CONTRAINDICATIONS', 'label' => 'Противопоказания', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_PREGNANCYUSE', 'label' => 'Примен. при берем-ти и кормл. грудью', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_SIDEACTIONS', 'label' => 'Побочные действия', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_INTERACTIONS', 'label' => 'Взаимодействие', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_USEMETHODANDDOSES', 'label' => 'Способ применения и дозы', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_INSTRFORPAC', 'label' => 'Инструкция для пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_OVERDOSE', 'label' => 'Передозировка', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_PRECAUTIONS', 'label' => 'Меры предосторожности', 'rules' => '', 'type' => 'string'),
			array('field' => 'DESCTEXTES_SPECIALGUIDELINES', 'label' => 'Особые указания', 'rules' => '', 'type' => 'string'),

			array('field' => 'Extemporal_id', 'label' => 'Рецептура', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugNonpropNames_id', 'label' => 'Непатентованное наименование', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCountry_id', 'label' => 'Страна', 'rules' => '', 'type' => 'int'),
			array('field' => 'ACTMATTERS', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'NORECIPE', 'label' => '', 'rules' => '', 'type' => 'string', 'default' => 'N'),
			array('field' => 'file_uploaded', 'label' => 'Признак загружен ли файл', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'DFSIZE_LAT', 'label' => 'Кол-во ед.измерения на лат.', 'rules' => '', 'type' => 'string'),
			array('field' => 'SIZEUNITS_LAT', 'label' => 'Ед.измерения на лат.', 'rules' => '', 'type' => 'string'),
			array('field' => 'CLSATCS', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'PHARMAGROUPS', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'FTGGRLSS', 'label' => '', 'rules' => '', 'type' => 'string'),
		),
		'deleteNomen' => array(
			array('field' => 'Nomen_id', 'label' => 'Упаковка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Prep_id', 'label' => 'Препарат', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TradeNames_id', 'label' => 'Торг.назв.', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Rls_model', 'dbmodel');
	}
	
	/**
	 * Поиск по РЛС
	 */
	function searchData()
	{
		$data = $this->ProcessInputData('searchData');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->searchData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Загрузка дерева Фармакология
	 */
	function GetRlsPharmacologyStructure()
	{
		$data = $this->ProcessInputData('GetRlsPharmacologyStructure');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsPharmacologyStructure($data);
	
		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response) && count($response) > 0) {
				foreach ( $response as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$leafcnt = $this->dbmodel->getCountChildElement($parent_id = $row['RlsPharmagroup_id'], $tabname = 'rls.v_CLSPHARMAGROUP', $fieldname = 'PARENTID');
					if($leafcnt[0]['cnt'] != '0')
					{
						$leaf = false;
						$cls = 'folder';
					}
					else
					{
						$leaf = true;
						$cls = 'file';
					}
					$val[] = array(
						'id' 		=> $row['RlsPharmagroup_id'],
						'text'		=> $row['RlsPharmagroup_name'],
						'leaf'		=> $leaf,
						'cls'		=> $cls
					);					
				}
			}
		}
		$this->ReturnData($val);

		return true;
	}
	
	/**
	 * Загрузка дерева Нозологии
	 */
	function GetRlsNozologyStructure()
	{
		$data = $this->ProcessInputData('GetRlsNozologyStructure');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsNozologyStructure($data);
	
		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response) && count($response) > 0) {
				foreach ( $response as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$leafcnt = $this->dbmodel->getCountChildElement($parent_id = $row['RlsNozology_id'], $tabname = 'rls.v_CLSIIC', $fieldname = 'PARENTID');
					if($leafcnt[0]['cnt'] != '0')
					{
						$leaf = false;
						$cls = 'folder';
					}
					else
					{
						$leaf = true;
						$cls = 'file';
					}
					$val[] = array(
						'id' 		=> $row['RlsNozology_id'],
						'text'		=> $row['RlsNozology_name'],
						'leaf'		=> $leaf,
						'cls'		=> $cls
					);					
				}
			}
		}
		$this->ReturnData($val);

		return true;
	}
	
	/**
	 * Загрузка дерева АТХ
	 */
	function GetRlsAtxStructure()
	{
		$data = $this->ProcessInputData('GetRlsAtxStructure');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsAtxStructure($data);
	
		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response) && count($response) > 0) {
				foreach ( $response as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$leafcnt = $this->dbmodel->getCountChildElement($parent_id = $row['RlsAtx_id'], $tabname = 'rls.v_CLSATC', $fieldname = 'PARENTID');
					if($leafcnt[0]['cnt'] != '0')
					{
						$leaf = false;
						$cls = 'folder';
					}
					else
					{
						$leaf = true;
						$cls = 'file';
					}
					$val[] = array(
						'id' 		=> $row['RlsAtx_id'],
						'text'		=> $row['RlsAtx_name'],
						'leaf'		=> $leaf,
						'cls'		=> $cls
					);					
				}
			}
		}
		$this->ReturnData($val);

		return true;
	}
	
	/**
	 * Просмотр РЛС по АТХ
	 */
	function GetRlsAtxView()
	{
		$data = $this->ProcessInputData('GetRlsAtxView');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsAtxView($data);
		//print_r($response);
		if ( is_array($response) )
		{
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}

			$val['RlsAtx_name'] = toUTF($response[0]['RlsAtx_name']);
			
			foreach ($response as $row)
			{
				if($row['RlsActmatter_id'] != "")
				{
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$tradenames = $this->dbmodel->GetRlsTradenamesOnActmatters($row['RlsActmatter_id']);
					for ($i=0; $i<count($tradenames); $i++)
					{
						array_walk($tradenames[$i], 'ConvertFromWin1251ToUTF8');
					}
					
					$val[] = array(
						'RlsActmatter_id'	=> $row['RlsActmatter_id'],
						'RlsActmatter_name'	=> $row['RlsActmatter_name'],
						'tradenames'		=> (count($tradenames) > 0)?$tradenames:""
					);
				}
			}
			//print_r($val);
			$this->ReturnData($val);
		}
	}

	/**
	 * Получение списка
	 */
	function GetRlsAtxList()
	{
		$data = $this->ProcessInputData('GetRlsAtxList');
		if ($data) {
			$response = $this->dbmodel->GetRlsAtxList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка
	 */
	function GetRlsPharmagroupList()
	{
		$data = $this->ProcessInputData('GetRlsPharmagroupList');
		if ($data) {
			$response = $this->dbmodel->GetRlsPharmagroupList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка
	 */
	function GetRlsClsMzPhgroupList()
	{
		$data = $this->ProcessInputData('GetRlsClsMzPhgroupList');
		if ($data) {
			$response = $this->dbmodel->GetRlsClsMzPhgroupList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Просмотр РЛС по нозологии
	 */
	function GetRlsNozologyView()
	{
		$data = $this->ProcessInputData('GetRlsNozologyView');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsNozologyView($data);
		//print_r($response);
		if ( is_array($response) )
		{
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}

			$synonims = $this->dbmodel->GetRlsSynonimsforNozology($response[0]['RlsNozology_id']);
			for($i=0; $i<count($synonims); $i++)
			{
				array_walk($synonims[$i], 'ConvertFromWin1251ToUTF8');
			}

			$val['RlsNozology_name'] = toUTF($response[0]['RlsNozology_name']);
			$val['Synonims'] = (count($synonims) > 0)?$synonims:"";
			$actmatters = array();
			$j = 0;
			
			foreach ($response as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');

				if($row['RlsPharmagroup_id'] != "")
				{
					$actmatters[$j] = $this->dbmodel->GetRlsAcmattersOnPharmagroup($row['RlsPharmagroup_id'], $response[0]['RlsNozology_id']);
					
					foreach ($actmatters[$j] as $act)
					{
						array_walk($act, 'ConvertFromWin1251ToUTF8');
						if($act['RlsActmatter_id'] != "")
						{
							$tradenames = $this->dbmodel->GetRlsTradenamesOnActmatters($act['RlsActmatter_id']);
							if(count($tradenames) > 0)
							{
								for($i=0; $i<count($tradenames); $i++)
								{
									array_walk($tradenames[$i], 'ConvertFromWin1251ToUTF8');
								}
							}
							$actmatters_[$j][] = array(
								'RlsActmatter_id'	=> $act['RlsActmatter_id'],
								'RlsActmatter_name'	=> $act['RlsActmatter_name'],
								'tradenames'		=> (count($tradenames) > 0)?$tradenames:""
							);
						}
					}
				
					$val[] = array(
						'RlsPharmagroup_id'		=> $row['RlsPharmagroup_id'],
						'RlsPharmagroup_name'	=> $row['RlsPharmagroup_name'],
						'actmatters'			=> (isset($actmatters_[$j]))?$actmatters_[$j]:""
					);
				}
				$j++;
			}
			//print_r($val);
			$this->ReturnData($val);
		}
	}
	
	/**
	 * Просмотр РЛС по фармакологии
	 */
	function GetRlsPharmacologyView()
	{
		$data = $this->ProcessInputData('GetRlsPharmacologyView');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsPharmacologyView($data);

		if ( is_array($response) )
		{
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			if(isset($response[0]['RlsPharmagroup_id']))
			{
				$val = array(
					'RlsPharmagroup_id'		=> toUTF($response[0]['RlsPharmagroup_id']),
					'RlsPharmagroup_name'	=> toUTF($response[0]['RlsPharmagroup_name'])
				);
			}
			else
			{
				if(count($response) > 0)
				{
					//print_r($response);
					foreach ($response as $row)
					{
						array_walk($row, 'ConvertFromWin1251ToUTF8');
						if($row['RlsTorgNames_id'] != "")
						{
							$actmatters = $this->dbmodel->GetRlsActmattersOnTradenames($row['RlsTorgNames_id']);
							//print_r($actmatters);
							if(count($actmatters) > 0)
							{
								for ($i=0; $i<count($actmatters); $i++)
								{
									array_walk($actmatters[$i], 'ConvertFromWin1251ToUTF8');
								}
							}
						}
					
						$val[] = array(
							'RlsTorgNames_name' => $row['RlsTorgNames_name'],
							'RlsTorgNames_id'	=> $row['RlsTorgNames_id'],
							'actmatters'		=> (isset($actmatters))?$actmatters:""
						);
					}
					//print_r($val);
				}
			}
		}
		$this->ReturnData($val);
	}
	
	/**
	 * Просмотр РЛС по действующему веществу
	 */
	function GetRlsActmattersView()
	{
		$data = $this->ProcessInputData('GetRlsActmattersView');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsActmattersView($data);
		if(!empty($data['id']))
		{
			if ( is_array($response) )
			{
				if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
					$val = $response;
					array_walk($val, 'ConvertFromWin1251ToUTF8');
				}
				
				foreach ($response as $row)
				{
					array_walk($row, 'ConvertFromWin1251ToUTF8');
				
					$tradenames = $this->dbmodel->GetRlsTradenamesOnActmatters($row['RlsActmatter_id']);
					if(count($tradenames) > 0)
					{
						foreach($tradenames as $tn)
						{
							array_walk($tn, 'ConvertFromWin1251ToUTF8');
							$tradenames_[] = $tn;
						}
					}
					
					$pharma = $this->dbmodel->GetRlsPharmaonActmatter($row['RlsActmatter_id']);
					if(count($pharma) > 0)
					{
						foreach($pharma as $ph)
						{
							array_walk($ph, 'ConvertFromWin1251ToUTF8');
							
							if($ph['RlsPharmagroup_id'] != "")
								$pharm_parent = $this->dbmodel->GetParentNode($ph['RlsPharmagroup_id'], 'rls.v_CLSPHARMAGROUP', 'CLSPHARMAGROUP_ID');
							
							$pharmagroups_[] = array(
								'RlsPharmagroup_id'		=> $ph['RlsPharmagroup_id'],
								'RlsPharmagroup_name'	=> $ph['RlsPharmagroup_name'],
								'pharm_parents'			=> (isset($pharm_parent))?$pharm_parent:""
							);
						}
					}
					
					$nozology = $this->dbmodel->GetRlsNozologyonActmatter($response[0]['RlsActmatter_id']);
					if(count($nozology) > 0)
					{
						foreach($nozology as $nz)
						{
							array_walk($nz, 'ConvertFromWin1251ToUTF8');
							
							if($nz['RlsNozology_id'] != "")
								$noz_parent = $this->dbmodel->GetParentNode($nz['RlsNozology_id'], 'rls.v_CLSIIC', 'CLSIIC_ID');
							
							$nozology_[] = array(
								'RlsNozology_id'	=> $nz['RlsNozology_id'],
								'RlsNozology_name'	=> $nz['RlsNozology_name'],
								'noz_parents'		=> (isset($noz_parent))?$noz_parent:""
							);
						}
					}
					
					if (gettype($row['RlsVital']) == "NULL")
					{
						$row['RlsVital'] = "";
					}
					
					if (gettype($row['RlsPreferential']) == "NULL")
					{
						$row['RlsPreferential'] = "";
					}
					
					$val = array(
						'RlsActmatter_rusname'	=> $row['RlsActmatter_rusname'],
						'RlsActmatter_latname'	=> $row['RlsActmatter_latname'],
						'RlsStronggroups'		=> $row['RlsStronggroups'],
						'RlsNarcogroups'		=> $row['RlsNarcogroups'],
						'RlsVital'				=> $row['RlsVital'],
						'RlsPreferential'		=> $row['RlsPreferential'],
						'tradenames'			=> (isset($tradenames_))?$tradenames_:"",
						'pharmagroups'			=> (isset($pharmagroups_))?$pharmagroups_:"",
						'nozology'				=> (isset($nozology_))?$nozology_:""
					);
				}
				$this->ReturnData($val);
			}
		}
		$this->ReturnData(array_fill_keys(array('RlsActmatter_rusname','RlsActmatter_latname','RlsStronggroups','RlsNarcogroups','RlsVital', 'RlsPreferential'), null));
	}
	
	/**
	 * Просмотр РЛС по компаниям
	 */
	function GetRlsFirmsView()
	{
		$data = $this->ProcessInputData('GetRlsFirmsView');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsFirmsView($data);
	
		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			
			foreach($response as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
			
				$tradenames = $this->dbmodel->GetRlsTradenamesonFirm($data);
				if(count($tradenames) > 0) {
					foreach($tradenames as $tn) {
						array_walk($tn, 'ConvertFromWin1251ToUTF8');
						$tradenames_[] = $tn;
					}
				}
				
				$val = array(
					'RlsFirms_name'			=> $row['RlsFirms_name'],
					'RlsCountries_name'		=> $row['RlsCountries_name'],
					'RlsFirms_addr'			=> $row['RlsFirms_addr'],
					'RlsFirms_addr_rus'		=> $row['RlsFirms_addr_rus'],
					'RlsFirms_addr_ussr'	=> $row['RlsFirms_addr_ussr'],
					'tradenames'			=> (isset($tradenames_))?$tradenames_:"",
					'count_tradenames'		=> (isset($tradenames_))?count($tradenames_):0
				
				);
			}
		}
		$this->ReturnData($val);
	}
	
	/**
	 * Загрузка комбобокса торгового наименования
	 */
	function GetRlsPackCode()
	{
		$data = $this->ProcessInputData('GetRlsPackCode', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetRlsPackCode($data);
		if( is_array($response) ) {
			foreach($response as $k=>$r) {
				$response[$k]['RlsPack_Code'] = preg_replace('/<\/?+[^>]+>|&+[^;]+;/', ' ', $r['RlsPack_Code']);
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Просмотр РЛС по торговому наименованию
	 */
	function GetRlsTorgView()
	{
		$data = $this->ProcessInputData('GetRlsTorgView', true);

		if ($data === false) { return false; }
		$response = $this->dbmodel->GetRlsTorgView($data);
		//var_dump($response);
		
		$val = array();
		
		if ( is_array($response) ) {
			foreach ($response as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				
				$pharmagroups = $this->dbmodel->GetPharmagrouponPrep($row['RlsPrep_id']); //Получаем фармгруппы
				if(count($pharmagroups) > 0) {
					foreach ($pharmagroups as $phgr) {
						array_walk($phgr, 'ConvertFromWin1251ToUTF8');
						// Находим ид'шники родительских узлов дерева, чтоб потом найти искомый нод
						if($phgr['RlsPharmagroup_id'] != "")
							$pharm_parent = $this->dbmodel->GetParentNode($phgr['RlsPharmagroup_id'], 'rls.v_CLSPHARMAGROUP', 'CLSPHARMAGROUP_ID');

						$phgr['pharm_parents'] = (isset($pharm_parent)) ? $pharm_parent : "";
						$pharmagroup[] = $phgr;
					}
				}
				
				$nozology = $this->dbmodel->GetNozolonPrep($row['RlsPrep_id']); //Получаем нозологические группы
				if(count($nozology) > 0) {
					foreach ($nozology as $noz) {
						array_walk($noz, 'ConvertFromWin1251ToUTF8');
						// Находим ид'шники родительских узлов дерева, чтоб потом найти искомый нод
						if($noz['RlsNozology_id'] != "")
							$noz_parent = $this->dbmodel->GetParentNode($noz['RlsNozology_id'], 'rls.v_CLSIIC', 'CLSIIC_ID');
						
						$noz['noz_parents'] = ( isset($noz_parent) ) ? $noz_parent : "";
						$nozologygroup[] = $noz;
					}
				}
				
				// Находим ид'шники родительских узлов дерева, чтоб потом найти искомый нод
				if($row['RlsAtc_id'] != "")
					$atc_parent = $this->dbmodel->GetParentNode($row['RlsAtc_id'], 'rls.v_CLSATC', 'CLSATC_ID');

				$val = $row;
				$val['RlsPharmagroups'] = ( isset($pharmagroup) ) ? $pharmagroup : "";
				$val['atc_parents'] = ( isset($atc_parent) ) ? $atc_parent : "";
				$val['RlsNozolgroups'] = ( isset($nozologygroup) ) ? $nozologygroup : "";
				$val['image_url'] = ( is_file($row['image_url']) ) ? $row['image_url'] : "";
			}
		}
		$this->ReturnData($val);
	}
	
	/**
	 * Получение торговых наименований
	 */
	function getTorgNames()
	{
		$data = $this->ProcessInputData('getTorgNames', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getTorgNames($data);
		if(is_array($response) && count($response)>0)
		{
			for($i=0; $i<count($response); $i++)
			{
				$response[$i]['NAME'] = strip_tags($response[$i]['NAME']);
			}
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка действующего вещества в комбобокс
	 */
	function getDataForComboStore()
	{
		$data = $this->ProcessInputData('getDataForComboStore', true);
		if ($data === false) { return false; }
		
		foreach($_POST as $key=>$value){
			if(preg_match('/name/', strtolower($key))){
				$data['where'] = $data['stringfield']." like '".toAnsi($value)."%'";
			} else if(preg_match('/id/', strtolower($key))){
				$data['where'] = $key."=".$value;
			}
		}

		$response = $this->dbmodel->getDataForComboStore($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка данных в комбобокс
	 */
	function getDataForComboStoreWithFields()
	{
		$data = $this->ProcessInputData('getDataForComboStoreWithFields', true);
		if ($data === false) { return false; }
		
		foreach($_POST as $key=>$value){
			if(preg_match('/name/', strtolower($key))){
				$data['where'] = $data['stringfield']." like '".$_POST[$key]."%'";
				break;
			} else if(preg_match('/id/', strtolower($key))){
				$data['where'] = $key."=".$value;
			}
		}

		$response = $this->dbmodel->getDataForComboStoreWithFields($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Форма поиска производителя лекарственного средства
	 */
	function loadSearchFirmsGrid()
	{
		$data = $this->ProcessInputData('loadSearchFirmsGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadSearchFirmsGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение производителя лекарственного средства
	 */
	function saveFirm()
	{
		$data = $this->ProcessInputData('saveFirm', true);
		if ($data === false) { return false; }

		if(empty($data['FIRMS_ID']) && empty($data['FIRMNAMES_ID'])){ // тоесть при добавлении
			// Сначала проверяем существует ли производитель с таким именем
			$response = $this->dbmodel->checkFirmOnExist($data);
			// Если совпадения есть, показываем их и прерываем выполнение
			if(count($response) > 0){
				$errmsg = '<b>Производители с похожим наименованием уже есть в базе данных:</b><br />';
				foreach($response as $key=>$row){
					$errmsg .= ($key+1).'. '.$row['FIRMS_NAME'].'<br />';
					if($key>=9){
						$errmsg .= '<br />Показаны только первые <b>'.($key+1).'</b> совпадений из <b>'.count($response).'</b>!';
						break;
					}
				}
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($errmsg)));
				return false;
			}
		}
		// Сохраняем наименование
		$response = $this->dbmodel->saveFirmName($data);
		if(is_array($response) && strlen($response[0]['Error_Msg']) == 0) {
			$data['FIRMNAMES_ID'] = $response[0]['FIRMNAMES_ID'];
		} else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Произошла ошибка при сохранении наименования производителя!')));
			return false;
		}
		// Сохраняем производителя
		$response = $this->dbmodel->saveFirm($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Получение производителя лекарственного средства
	 */
	function getFirm()
	{
		$val = array();
		$data = $this->ProcessInputData('getFirm', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getFirm($data);
		if(is_array($response) && count($response)>0){
			$patterns = array('/<\/?[^>]+>|&nbsp;/', '/&#64;/', '/&#171;/', '/&#187;/', '/&#147;|&#148;/', '/&mdash;/');
			$replacements = array(' ', '@', '«', '»', '"', '-');
			// Убираем все теги и прочую херню заменяем на нормальные символы
			foreach($response as $key=>$row){
				$val[$key] = preg_replace($patterns, $replacements, $row);
			}
		}
		$this->ProcessModelList($val, true, true)->ReturnData();
	}
	
	/**
	 * Удаление производителя лекарственного средства
	 */
	function deleteFirm()
	{
		$data = $this->ProcessInputData('deleteFirm', true);
		if ($data === false) { return false; }
		
		// Проверим сначала можно ли удалять производителя
		$checkresponse = $this->dbmodel->checkFirmOnRls($data);
		if(is_array($checkresponse) && count($checkresponse)==1){
			if($checkresponse[0]['ProducerType_id']==1){
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Нельзя удалять данного производителя!')));
				return false;
			} else {
				$data['FIRMNAMES_ID'] = $checkresponse[0]['FIRMNAMES_ID'];
			}
		}
		if(isset($data['FIRMNAMES_ID'])){
			// Удаляем производителя
			$response = $this->dbmodel->deleteFirm($data);
			if(is_array($response) && strlen($response[0]['Error_Msg']) == 0){
				// Удаляем наименование
				$response = $this->dbmodel->deleteFirmName($data);
				$this->ProcessModelList($response, true, true)->ReturnData();
			}
		}
	}
	
	/**
	 * Предзагрузка изображения
	 */
	function preuploadImage() {
		// Разрешенные к загрузке типы файлов
		$allowedFiles = array( 'gif', 'jpeg', 'octet-stream' );
		
		//var_dump($_FILES); die();
		if(isset($_FILES['file'])){
			$t = explode('/', $_FILES['file']['type']);
			$type = $t[1];
			if(!preg_match('/'.implode('|', $allowedFiles).'/i', $type))
				return false;
			// Переименуем файл чтобы он сохранился в темповой папке=)
			$newname = DRUGSPATH .'noname.gif';
			$flag = @rename($_FILES['file']['tmp_name'], $newname);
			if($flag){
				$val = array(
					'file_name'	=> toUTF($_FILES['file']['name']),
					'file_url' => $newname.'?'.rand(1,10000),
					'success' => true
				);
				$this->ReturnData($val);
			}
		}
	}
	
	/**
	 * Для проверки ошибок в response
	 * $r - проверяемый ответ в виде массива
	 * $res - параметр @Res, чаще всего возвращаемый хранимкой в случае успешного выполнения
	 */
	function checkErrors($r, $res)
	{
		if(!is_array($r)) return false;
		if(isset($r[0]['Error_Msg']) && strlen($r[0]['Error_Msg'])>0){
			$this->dbmodel->rollbackTransaction();
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($r[0]['Error_Msg'])));
			return false;
		} else {
			if(isset($r[0][$res]) && $r[0][$res]>0){
				return $r[0][$res];
			} else {
				if(isset($r[$res]) && $r[$res]>0) {
					return $r[$res];
				}
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}
	}
	
	/**
	 * Сохранение добавляемого лек.средства
	 */
	function savePrep()
	{
		$data = $this->ProcessInputData('savePrep', true);
		if ($data === false) { return false; }
		try {
			$this->dbmodel->beginTransaction();
			// Если добавляем препарат
			if(empty($data['TRADENAMES_ID']) && empty($data['Prep_id'])){

				// Проверяем на существования торг. назв. с таким именем
				$tradenames = $this->dbmodel->getDataForComboStore(array(
						'object' => 'TRADENAMES',
						//'where' => 'NAME like \''.$data['TRADENAMES_NAME'].'\'+\'%\'',
						'where' => 'NAME = \''.$data['TRADENAMES_NAME'].'\'',
						'stringfield' => 'NAME'
					));
				if(count($tradenames) > 0){
					$errmsg = '<b>Препараты с похожим наименованием уже есть в базе данных:</b><br />';
					foreach($tradenames as $key=>$row){
						$errmsg .= ($key+1).'. '.$row['NAME'].'<br />';
						if($key>=9){
							$errmsg .= '<br />Показаны только первые <b>'.($key+1).'</b> совпадений из <b>'.count($tradenames).'</b>!';
							break;
						}
					}
					if((empty($data['CLSNTFR_ID']) || $data['CLSNTFR_ID'] == 1 || $data['CLSNTFR_ID']>=215) && !empty($tradenames[0]['TRADENAMES_ID'])){
						$data['TRADENAMES_ID'] = $tradenames[0]['TRADENAMES_ID'];
					} else {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($errmsg)));
						return false;
					}
				}

				// Сохраняем торговое название препарата
				$response = $this->dbmodel->saveTradeName($data);
				$result = $this->checkErrors($response, 'TRADENAMES_ID');
				if(!$result) return false;
				$data['TRADENAMES_ID'] = $result;
				
				$data['action'] = 'ins';
			} else {
				// Сохраняем торговое название препарата
				if(!empty($data['TRADENAMES_NAME'])){
					$response = $this->dbmodel->saveTradeName($data);
					$result = $this->checkErrors($response, 'TRADENAMES_ID');
					if(!$result) return false;
					$data['TRADENAMES_ID'] = $result;
				}
			}

			// Если указано латинское название, то сохраняем запись
			if(!empty($data['LATINNAMES_NAME'])){
				$response = $this->dbmodel->saveLatinName($data);
				$result = $this->checkErrors($response, 'LATINNAMES_ID');
				if(!$result) return false;
				$data['LATINNAMES_ID'] = $result;
			}

			// Если указана ед. измерения то сохраняем краткое латинское название
			if(!empty($data['DFSIZEID'])){
				$response = $this->dbmodel->saveObject('rls.SIZEUNITS', array(
					'key_field' => 'SIZEUNITS_ID',
					'SIZEUNITS_ID' => $data['DFSIZEID'],
					'SHORTNAMELATIN' => $data['SIZEUNITS_LAT']
				));
				$result = $this->checkErrors(array($response), 'SIZEUNITS_ID');
				if(!$result) {
					return false;
				}
				$data['DFSIZEID'] = $result;
			}
			
			// Сохраняем инфу о регистрации
			if( !empty($data['REGCERT_REGNUM']) || $data['REGCERT_ID'] != 0 ) {
				if(!empty($data['REGCERT_REGDATERange'][0])){
					$data['REGCERT_REGDATE'] = $data['REGCERT_REGDATERange'][0];
				}
				if(!empty($data['REGCERT_REGDATERange'][1])){
					$data['REGCERT_ENDDATE'] = $data['REGCERT_REGDATERange'][1];
				}
				$response = $this->dbmodel->saveRegCert($data);
				$result = $this->checkErrors($response, 'REGCERT_ID');
				if(!$result) return false;
				$data['REGCERT_ID'] = $result;

				if(!empty($data['RegOwner'])){
					$response = $this->dbmodel->saveRegCertOwner($data);
					if(!$response) return false;
				}
			}
			
			//var_dump($data['REGCERT_ID']);
			//$this->dbmodel->rollbackTransaction();
			//exit();
			if(!empty($data['Manufacturer'])){
				$data['FIRMS_ID'] = $data['Manufacturer'];
			}
			if(!empty($data['NORECIPE']) && $data['NORECIPE'] == '2'){
				$data['NORECIPE'] = 'Y';
			} else if(!empty($data['NORECIPE']) && $data['NORECIPE'] == '1'){
				$data['NORECIPE'] = 'N';
			}

			// для медиизделий, в базе данных в таблице rls.PrepType предусмотрены записи только для 1 и 2
			//поэтому все остальные PrepType_id приравниваем ко 2 значению "добавлено пользователем"
			if(!empty($data['PrepType_id']) && !in_array($data['PrepType_id'], array('1','2'))){
				$data['PrepType_id'] = 2;
			}

			// Сохраняем препарат
			$response = $this->dbmodel->savePrep($data);
			$result = $this->checkErrors($response, 'Prep_id');
			if(!$result) return false;
			$data['Prep_id'] = $result;
			// проверка на наличие записи в справочнике Действующих веществ записи с именем равным значению поля МНН 
			// (Если в форме заполнены данные о рецептуре)
			if(false && !empty($data['Extemporal_id']) && !empty($data['Actmatters_Names'])){
				$response = $this->dbmodel->checkAndSaveActmattersName($data);
				$result = $this->checkErrors($response, 'ACTMATTERS_ID');
				if(!$result) return false;
				if(!empty($data['ACTMATTERS']) && $data['ACTMATTERS'] != $result){
					$data['ACTMATTERS_ID'] = $result;
					$response = $this->dbmodel->directInsertData($data, 'PREP_ACTMATTERS');
				}
			}
			// Удаляем связи с ДВ
			$this->dbmodel->deletePrepOnActmatters($data);
			// Добавляем связи с ДВ (если указаны )
			if(empty($data['Extemporal_id']) && !empty($data['ACTMATTERS'])){
				$act = explode('|', $data['ACTMATTERS']);
				for($i=0; $i<count($act); $i++){
					$data['ACTMATTERS_ID'] = $act[$i];
					$response = $this->dbmodel->directInsertData($data, 'PREP_ACTMATTERS');
					// Пока без проверки на ошибки Error_Msg
				}
			}
			// Сохраняем срок годности (по идее поле обязательное, но всеравно проверим)
			if(!empty($data['DRUGLIFETIME_TEXT'])){
				if (($result = $this->dbmodel->getDrugLifeTimeId($data)) === false) // проверка на уникальность срока годности по полю TEXT
				{
					$response = $this->dbmodel->saveDrugLifeTime($data);
					$result = $this->checkErrors($response, 'DRUGLIFETIME_ID');
					if(!$result) return false;
				}

				$data['DRUGLIFETIME_ID'] = $result;
			}
			// Сохраняем условия хранения
			if(!empty($data['DRUGSTORCOND_TEXT'])){
				$response = $this->dbmodel->saveDrugStorCond($data);
				$result = $this->checkErrors($response, 'DRUGSTORCOND_ID');
				if(!$result) return false;
				$data['DRUGSTORCOND_ID'] = $result;
			}
			if(!empty($data['Packer'])){
				$data['FIRMS_ID'] = $data['Packer'];
			}
			// Сохраняем упаковку(номенклатуру)
			$response = $this->dbmodel->saveNomen($data);
			$result = $this->checkErrors($response, 'NOMEN_ID');
			if(!$result) return false;
			$data['NOMEN_ID'] = $result;
	        //Сохраняем препарат
	        if (!empty($data['NOMEN_ID']) && !empty($data['Prep_id'])) {
	            $response = $this->dbmodel->saveDrugMnnOne($data);
	        }

	        if (!empty($data['NOMEN_ID']) && !empty($data['Extemporal_id'])) {
	            $response = $this->dbmodel->saveExtemporalNomen($data);
	            $result = $this->checkErrors($response, 'ExtemporalNomen_id');
	            if(!$result) throw new Exception("Ошибка при сохранении связи номенклатуры с рецептурой");
	        }

			// Сохраняем описание
			if(empty($data['DESCRIPTIONS_ID'])){
				if(!empty($data['Manufacturer'])){
					$data['FIRMS_ID'] = $data['Manufacturer'];
				}
				$response = $this->dbmodel->saveDescriptions($data);
				$result = $this->checkErrors($response, 'DESCRIPTIONS_ID');
				if(!$result) return false;
				$data['DESCRIPTIONS_ID'] = $result;
			}
			//*/
			// Далее связь номенклатуры с описанием
			$response = $this->dbmodel->directInsertData($data, 'NOMEN_DESC');
			// Пока без проверки на ошибки Error_Msg
			
			// Относится ли к жизненноважным лек. средствам, также добавляем связи
			if(!empty($data['TN_DF_LIMP'])){
				$response = $this->dbmodel->directInsertData($data, 'TN_DF_LIMP');
				// Пока без проверки на ошибки Error_Msg
			}
			
			// Является ли препаратом льготного ассортимента
			if(!empty($data['TRADENAMES_DRUGFORMS'])){
				$response = $this->dbmodel->directInsertData($data, 'TRADENAMES_DRUGFORMS');
				// Пока без проверки на ошибки Error_Msg
			}

			// Связь с анатомо-терапевтическо-химической классификацией (АТХ)
			if (!empty($data['CLSATCS'])) {
				$data['CLSATCS'] = explode("|", urldecode($data['CLSATCS']));
				$resp = $this->dbmodel->getATCGroups(array('Prep_id' => $data['Prep_id']));
				//находим новые классы добавленные с формы и сохраняем их
				$clsAtcToInsert = array_diff($data['CLSATCS'], $resp);
				if (!empty($clsAtcToInsert)) {
					foreach ($clsAtcToInsert as $clsatc_id) {
						$response = $this->dbmodel->directInsertData(array(
							'Prep_id' => $data['Prep_id'],
							'CLSATC_ID' => $clsatc_id
						), 'PREP_ATC');
					}
				}
				//если некоторые классы на форме удалили
				$clsAtcToDel = array_diff($resp, $data['CLSATCS']);
				if (!empty($clsAtcToDel)) {
					foreach ($clsAtcToDel as $clsatc_id) {
						$response = $this->dbmodel->deleteATCGroup(array(
							'Prep_id' => $data['Prep_id'],
							'CLSATC_ID' => $clsatc_id
						));
					}
				}
			} else {
				//если на форме для препарата удалены все классы АТХ
				$clsAtcToDel = $this->dbmodel->getATCGroups(array('Prep_id' => $data['Prep_id']));
				if (!empty($clsAtcToDel)) {
					foreach ($clsAtcToDel as $clsatc_id) {
						$response = $this->dbmodel->deleteATCGroup(array(
							'Prep_id' => $data['Prep_id'],
							'CLSATC_ID' => $clsatc_id
						));
					}
				}
			}

			//Связь с классификатором фармакологических групп
			if (!empty($data['FTGGRLSS'])) {
				//работаем с данными о связи препарата и фармакологических групп
				$data['FTGGRLSS'] = explode("|", urldecode($data['FTGGRLSS']));
				$resp = $this->dbmodel->getPrepFTGGRLSs(array('Prep_id' => $data['Prep_id']));
				$clsFTGGRLSToInsert = array_diff($data['FTGGRLSS'], $resp);
				if (!empty($clsFTGGRLSToInsert)) {
					foreach ($clsFTGGRLSToInsert as $FTGGRLS_id) {
						$response = $this->dbmodel->directInsertData(array(
							'Prep_id' => $data['Prep_id'],
							'FTGGRLS_ID' => $FTGGRLS_id,
							'pmUser_id' => $data['pmUser_id']
						), 'FTGGRLS');
					}
				}
				//если на форме связи с некоторыми фармакологическими группами удалили
				$clsFTGGRLSToDel = array_diff($resp, $data['FTGGRLSS']);
				if (!empty($clsFTGGRLSToDel)) {
					foreach ($clsFTGGRLSToDel as $FTGGRLS_id) {
						$response = $this->dbmodel->deleteFTGGRLS(array(
							'Prep_id' => $data['Prep_id'],
							'FTGGRLS_ID' => $FTGGRLS_id
						));
					}
				}

				//работаем с данными о связи действующего вещества и фармакологических групп
				if (!empty($data['ACTMATTERS'])) {
					$actmatters = $this->dbmodel->getActmattersOnPrep(array('Prep_id' => $data['Prep_id']));
					$actmatters_id = $actmatters[0]['ACTMATTERS_ID'];
					$ActPhGrs = $this->dbmodel->getActmatFTGGRLSs(array('Actmatters_id' => $actmatters_id));
					$ActPhGrsToInsert = array_diff($data['FTGGRLSS'], $ActPhGrs);
					if (!empty($ActPhGrsToInsert)) {
						foreach ($ActPhGrsToInsert as $FTGGRLS_id) {
							$response = $this->dbmodel->directInsertData(array(
								'Actmatters_id' => $actmatters_id,
								'FTGGRLS_ID' => $FTGGRLS_id,
								'pmUser_id' => $data['pmUser_id']
							), 'ACTMATTERSFTGGRLS');
						}
					}

					$ActPhGrsToDel = array_diff($ActPhGrs, $data['FTGGRLSS']);
					if (!empty($ActPhGrsToDel)) {
						foreach ($ActPhGrsToDel as $FTGGRLS_id) {
							$response = $this->dbmodel->deleteActmattersFTGGRLS(array(
								'Actmatters_id' => $actmatters_id,
								'FTGGRLS_ID' => $FTGGRLS_id
							));
						}
					}
				}
			} else {
				//если на форме все связи с фармакологическими группами удалили, то:
				//удаляем их для препарата
				$clsFTGGRLSToDel = $this->dbmodel->getPrepFTGGRLSs(array('Prep_id' => $data['Prep_id']));
				if (!empty($clsFTGGRLSToDel)) {
					foreach ($clsFTGGRLSToDel as $FTGGRLS_id) {
						$response = $this->dbmodel->deleteFTGGRLS(array(
							'Prep_id' => $data['Prep_id'],
							'FTGGRLS_ID' => $FTGGRLS_id
						));
					}
				}
				//для действующего вещества
				if (!empty($data['ACTMATTERS'])) {
					$actmatters = $this->dbmodel->getActmattersOnPrep(array('Prep_id' => $data['Prep_id']));
					if (isset($actmatters[0]['ACTMATTERS_ID'])) {
						$actmatters_id = $actmatters[0]['ACTMATTERS_ID'];
						$ActPhGrsToDel = $this->dbmodel->getActmatFTGGRLSs(array('Actmatters_id' => $actmatters_id));
						if (!empty($ActPhGrsToDel)) {
							foreach ($ActPhGrsToDel as $FTGGRLS_id) {
								$response = $this->dbmodel->deleteActmattersFTGGRLS(array(
									'Actmatters_id' => $actmatters_id,
									'FTGGRLS_ID' => $FTGGRLS_id
								));
							}
						}
					}
				}
			}

			//Связь с классификатором фармакотерапевтических групп
			if (!empty($data['PHARMAGROUPS'])) {
				//работаем с данными о связи препарата и фармакотерапевтических групп
				$data['PHARMAGROUPS'] = explode("|", urldecode($data['PHARMAGROUPS']));
				$resp = $this->dbmodel->getPrepPharmaGroups(array('Prep_id' => $data['Prep_id']));
				$clsPharmaGroupToInsert = array_diff($data['PHARMAGROUPS'], $resp);
				if (!empty($clsPharmaGroupToInsert)) {
					foreach ($clsPharmaGroupToInsert as $pharmagroup_id) {
						$response = $this->dbmodel->directInsertData(array(
							'Prep_id' => $data['Prep_id'],
							'CLSPHARMAGROUP_ID' => $pharmagroup_id
						), 'PHARMAGROUPS');
					}
				}
				//если на форме связи с некоторыми фармакотерапевтическими группами удалили
				$clsPharmaGroupToDel = array_diff($resp, $data['PHARMAGROUPS']);
				if (!empty($clsPharmaGroupToDel)) {
					foreach ($clsPharmaGroupToDel as $pharmagroup_id) {
						$response = $this->dbmodel->deletePharmaGroups(array(
							'Prep_id' => $data['Prep_id'],
							'CLSPHARMAGROUP_ID' => $pharmagroup_id
						));
					}
				}

				//работаем с данными о связи действующего вещества и фармакотерапевтических групп
				if (!empty($data['ACTMATTERS'])) {
					$actmatters = $this->dbmodel->getActmattersOnPrep(array('Prep_id' => $data['Prep_id']));
					$actmatters_id = $actmatters[0]['ACTMATTERS_ID'];
					$ActPhGrs = $this->dbmodel->getActmatPharmaGroups(array('Actmatters_id' => $actmatters_id));
					$ActPhGrsToInsert = array_diff($data['PHARMAGROUPS'], $ActPhGrs);
					if (!empty($ActPhGrsToInsert)) {
						foreach ($ActPhGrsToInsert as $pharmagroup_id) {
							$response = $this->dbmodel->directInsertData(array(
								'Actmatters_id' => $actmatters_id,
								'CLSPHARMAGROUP_ID' => $pharmagroup_id
							), 'ACTMATTERSPHARMAGROUPS');
						}
					}

					$ActPhGrsToDel = array_diff($ActPhGrs, $data['PHARMAGROUPS']);
					if (!empty($ActPhGrsToDel)) {
						foreach ($ActPhGrsToDel as $pharmagroup_id) {
							$response = $this->dbmodel->deleteActmattersPharmaGroups(array(
								'Actmatters_id' => $actmatters_id,
								'CLSPHARMAGROUP_ID' => $pharmagroup_id
							));
						}
					}
				}
			} else {
				//если на форме все связи с фармакотерапевтическими группами удалили, то:
				//удаляем их для препарата
				$clsPharmaGroupToDel = $this->dbmodel->getPrepPharmaGroups(array('Prep_id' => $data['Prep_id']));
				if (!empty($clsPharmaGroupToDel)) {
					foreach ($clsPharmaGroupToDel as $pharmagroup_id) {
						$response = $this->dbmodel->deletePharmaGroups(array(
							'Prep_id' => $data['Prep_id'],
							'CLSPHARMAGROUP_ID' => $pharmagroup_id
						));
					}
				}
				//для действующего вещества
				if (!empty($data['ACTMATTERS'])) {
					$actmatters = $this->dbmodel->getActmattersOnPrep(array('Prep_id' => $data['Prep_id']));
					if (isset($actmatters[0]['ACTMATTERS_ID'])) {
						$actmatters_id = $actmatters[0]['ACTMATTERS_ID'];
						$ActPhGrsToDel = $this->dbmodel->getActmatPharmaGroups(array('Actmatters_id' => $actmatters_id));
						if (!empty($ActPhGrsToDel)) {
							foreach ($ActPhGrsToDel as $pharmagroup_id) {
								$response = $this->dbmodel->deleteActmattersPharmaGroups(array(
									'Actmatters_id' => $actmatters_id,
									'CLSPHARMAGROUP_ID' => $pharmagroup_id
								));
							}
						}
					}
				}
			}

			if (!empty($data['CLSIICS'])) {
				$data['CLSIICS'] = explode("|", urldecode($data['CLSIICS']));
				$ClsIICs = $this->dbmodel->getClsIIC(array('Prep_id' => $data['Prep_id']));
				$ClsIICsToInsert = array_diff($data['CLSIICS'], $ClsIICs);
				//если препарату добавилась связь с новыми диагнозами
				if (!empty($ClsIICsToInsert)) {
					foreach ($ClsIICsToInsert as $clsiic_id) {
						$response = $this->dbmodel->directInsertData(array(
							'Prep_id' => $data['Prep_id'],
							'CLSIIC_ID' => $clsiic_id,
							'CLSPHARMAGROUP_ID' => Null                        //с задачи #145229 фармгруппы сохраняются отдельно в таблицу rls.PREP_PHARMAGROUP
						), 'PREP_IIC');
					}
				}
				$ClsIICsToDel = array_diff($ClsIICs, $data['CLSIICS']);
				//если связь с некоторыми диагнозами на форме удалили
				if (!empty($ClsIICsToDel)) {
					foreach ($ClsIICsToDel as $clsiic_id) {
						$response = $this->dbmodel->deleteClsIICs(array(
							'Prep_id' => $data['Prep_id'],
							'CLSIIC_ID' => $clsiic_id
						));
					}
				}
			} else {
				//если на форме удалены все связи с диагнозами
				$ClsIICsToDel = $this->dbmodel->getClsIIC(array('Prep_id' => $data['Prep_id']));
				if (!empty($ClsIICsToDel)) {
					foreach ($ClsIICsToDel as $clsiic_id) {
						$response = $this->dbmodel->deleteClsIICs(array(
							'Prep_id' => $data['Prep_id'],
							'CLSIIC_ID' => $clsiic_id
						));
					}
				}
			}
			
			// Сохраняем инфу препарата
			$response = $this->dbmodel->saveDesctextes($data);
			$result = $this->checkErrors($response, 'DESCRIPTIONS_ID');
			if(!$result) return false;
			
			$this->dbmodel->commitTransaction();
			
			// Сохраняем имя картинки в таблицу и саму картинку
			if($data['file_uploaded']){
				// Получим IWID картинки
				$response = $this->dbmodel->getIWID($data);
				$result = $this->checkErrors($response, 'IWID');
				if(!$result) return false;
				$data['IWID'] = $result;
				
				$response = $this->dbmodel->directInsertData($data, 'IDENT_WIND_STR');
				$result = $this->checkErrors($response, 'IDENT_WIND_STR_id');
				if(!$result) return false;
				// Пока без проверки на ошибки Error_Msg
				
				$file = DRUGSPATH .'noname.gif';
				if(is_file($file)){
					$flag = @rename($file, DRUGSPATH.$data['IWID'].'.gif');
				} else {
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не удалось сохранить изображение!')));
				}
			}
			$this->ReturnData(array('success' => true));
		} catch (Exception $e) {
			$this->dbmodel->rollbackTransaction();
			$this->ReturnData(array('success' => false, 'Error_Msg' => $e->getMessage()));
		}
	}
	
	/**
	 * Загрузка формы выбора типа добавляемого лек.средства
	 */
	function getPrep() {
		$val = array();
		$data = $this->ProcessInputData('getPrep', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getPrep($data);
		if(!empty($response[0]['Prep_id'])){
			$data['Prep_id'] = $response[0]['Prep_id'];
		} else {
            return $this->ReturnError('Лек.средство не найдено в БД');
        }
		// Самое неприятное это удалить всякие быдло-символы типа тегов и хтмл-сущностей...
		$re = "/<\/?[^>]+>|&[\w]+;/i";
		foreach($response as $row) {
			foreach($row as $k=>$r) {
				$row[$k] = preg_replace($re, '', $r);
			}
			$val[] = $row;
		}
		
		$val[0]['actmatters'] = $this->dbmodel->getActmattersOnPrep($data);
		$val[0]['clsiics'] = $this->dbmodel->getClsIIConPrep($data);
		$val[0]['clsatcs'] = $this->dbmodel->getClsATC($data);
		$val[0]['pharmagroups'] = $this->dbmodel->getPharmaGroups($data);
		$val[0]['ftggrlss'] = $this->dbmodel->getFTGGRLSs($data);
		$this->ProcessModelList($val, true, true)->ReturnData();
	}

	/**
	 * Удаляем наименования
	 */
	function deleteNomen() {
		$data = $this->ProcessInputData('deleteNomen', true);
		if ($data === false) { return false; }
		
		$this->dbmodel->beginTransaction();
		$response = $this->dbmodel->deleteNomen($data);
		
		if( $response === false ) {
			$this->dbmodel->rollbackTransaction();
			return $this->ReturnError('Ошибка БД!');
		}
		
		if( isset($response[0]) && !empty($response[0]['Error_Msg']) ) {
			$this->dbmodel->rollbackTransaction();
			return $this->ReturnError($response[0]['Error_Msg']);
		}
		$this->dbmodel->commitTransaction();
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	
	
}