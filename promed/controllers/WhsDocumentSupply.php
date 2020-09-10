<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для объектов Договора поставок
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       ModelGenerator
* @version
* @property WhsDocumentSupply_model WhsDocumentSupply_model
* @property WhsDocumentSupplySpec_model WhsDocumentSupplySpec_model
* @property WhsDocumentUcPriceHistory_model WhsDocumentUcPriceHistory_model
*/

class WhsDocumentSupply extends swController{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'sign' => array(
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'unsign' => array(
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'signWhsDocumentSupplyAdditional' => array(
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор соглашения', 'rules' => 'required', 'type' => 'id')
			),
			'save' => array(
				array(
					'field' => 'WhsDocumentUc_pid',
					'label' => 'Ссылка на родительский документ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_Num',
					'label' => 'Номер',
					'rules' => 'required',
					'type' => 'string'/*''*/
				),
				array(
					'field' => 'WhsDocumentUc_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'Тип договора поставки',
					'default' => 1,
					'rules' => ''/*'required'*/,
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_Date',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Org_sid',
					'label' => 'Поставщик',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_cid',
					'label' => 'Заказчик',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_pid',
					'label' => 'Плательщик',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_rid',
					'label' => 'Получатель',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_Sum',
					'label' => 'Сумма',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Идентификатор документа учета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupply_ProtNum',
					'label' => 'Номер протокола аукциона',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentSupply_ProtDate',
					'label' => 'Дата протокола аукциона',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentSupplyType_id',
					'label' => 'Тип поставки',
					'default' => 1,
					'rules' => ''/*'required'*/,
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupply_BegDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentSupply_ExecDate',
					'label' => 'Дата исполнения обязательств Поставщиком',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'FinanceSource_id',
					'label' => 'Источник оплаты',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов',
					'default' => 1,
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BudgetFormType_id',
					'label' => 'Целевая статья',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentPurchType_id',
					'label' => 'Вид закупа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SupplySpecJSON',
					'label' => 'Строка спецификации',
					'default' => '',
					'rules' => ''/*'required'*/,
					'type' => 'string'
				),
				array(
					'field' => 'SupplyAdditionalJSON',
					'label' => 'Строка доп. соглашений',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DeliveryData',
					'label' => 'Строка графика поставки',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugNds_id',
					'label' => 'Ставка НДС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Действие',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array('field' => 'WhsDocumentStatusType_id', 'label' => 'Статус документа', 'default' => '', 'rules' => 'required', 'type' => 'id')
			),
			'saveWhsDocumentSupplyAdditional' => array(
				array('field' => 'WhsDocumentUc_pid', 'label' => 'Родительский документ', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'WhsDocumentUc_Num', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'WhsDocumentUc_Name', 'label' => 'WhsDocumentUc_Name', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'WhsDocumentType_id', 'label' => 'Тип документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Date', 'label' => 'WhsDocumentUc_Date', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'SupplySpecJSON', 'label' => 'Строка спецификации', 'default' => '', 'rules' => '', 'type' => 'string'),
				array('field' => 'WhsDocumentStatusType_id', 'label' => 'Статус документа', 'default' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentStatusType_Code', 'label' => 'Код статуса документа', 'default' => '', 'rules' => '', 'type' => 'string')
			),
			'load' => array(
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentSupplyAdditional' => array(
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id')
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentUc_pid',
					'label' => 'WhsDocumentUc_pid',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_Num',
					'label' => 'WhsDocumentUc_Num',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentUc_Name',
					'label' => 'WhsDocumentUc_Name',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'WhsDocumentType_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentType_Code',
					'label' => 'Код типа документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_Date',
					'label' => 'Дата документа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentUc_DateRange',
					'label' => 'Диапазон дат локументов',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'begDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'mode',
					'label' => 'Тип загрузки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentUc_Sum',
					'label' => 'WhsDocumentUc_Sum',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Org_cid',
					'label' => 'Заказчик',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_sid',
					'label' => 'Поставщик',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentSupply_ProtNum',
					'label' => 'Номер протокола аукциона',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentSupply_ProtDate',
					'label' => 'Дата протокола аукциона',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'WhsDocumentSupplyType_id',
					'label' => 'Тип поставки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupply_ExecDate',
					'label' => 'Дата исполнения обязательств Поставщиком',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'FinanceSource_id',
					'label' => 'Источник оплаты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentStatusType_id',
					'label' => 'Статус документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BudgetFormType_id',
					'label' => 'Целевая статья',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentPurchType_id',
					'label' => 'Вид закупа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentUc_KBK',
					'label' => 'WhsDocumentUc_KBK',
					'rules' => '',
					'type' => 'string'
				),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int')
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentProcurementRequestList' => array(
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор договора',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadWhsDocumentProcurementRequestSpecList' => array(
				array(
					'field' => 'WhsDocumentProcurementRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentSupplyList' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentSupplyAdditionalList' => array(
				array('field' => 'ParentWhsDocumentSupply_id', 'label' => 'Идентификатор родительского контракта', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
				array('field' => 'WhsDocumentUc_DateRange', 'label' => 'Диапазон дат локументов', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'Org_sid', 'label' => 'Поставщик', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentStatusType_id', 'label' => 'Статус документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
                array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
				array('field' => 'OrgFilter_Type', 'label' => 'Фильтр по организации: тип', 'rules' => '', 'type' => 'string'),
				array('field' => 'OrgFilter_Org_sid', 'label' => 'Фильтр по организации: поставщик', 'rules' => '', 'type' => 'string'),
				array('field' => 'OrgFilter_Org_cid', 'label' => 'Фильтр по организации: заказчик', 'rules' => '', 'type' => 'string'),
				array('field' => 'OrgFilter_Org_pid', 'label' => 'Фильтр по организации: плательщик', 'rules' => '', 'type' => 'string')

			),
			'loadWhsDocumentSupplyAdditionalShortList' => array(
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Государственный контракт', 'rules' => 'required', 'type' => 'id')
			),
			'loadWhsDocumentSupplyCombo' => array(
				array(
					'field' => 'WhsDocumentType_ids',
					'label' => 'типы документов',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Org_id',
					'label' => 'организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'договор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'запрос',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadWhsDocumentSupplySecondCombo' => array(
				array(
					'field' => 'WhsDocumentType_ids',
					'label' => 'типы документов',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Org_cid',
					'label' => 'Заказчик',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'договор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'запрос',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getMaxSalePrice' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentUc_Date',
					'label' => 'Дата контракта',
					'rules' => '',
					'type' => 'date'
				)
			),
			'generateNum' => array(
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_pid', 'label' => 'Заявка', 'rules' => '', 'type' => 'id')
			),
            'loadWhsDocumentSupplySpecDrug' => array(
                array('field' => 'WhsDocumentSupplySpecDrug_id', 'label' => 'Синоним', 'rules' => 'required', 'type' => 'id')
            ),
            'saveWhsDocumentSupplySpecDrug' => array(
                array('field' => 'WhsDocumentSupplySpecDrug_id', 'label' => 'Синоним', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentSupplySpec_id', 'label' => 'Позиция спецификации', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Drug_id', 'label' => 'Медикамент из спецификации', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'WhsDocumentSupplySpecDrug_Price', 'label' => 'Цена из спецификации', 'rules' => '', 'type' => 'float'),
                array('field' => 'WhsDocumentSupplySpecDrug_Coeff', 'label' => 'Коэфицент замены', 'rules' => '', 'type' => 'float'),
                array('field' => 'Drug_sid', 'label' => 'Медикамент синонима', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'WhsDocumentSupplySpecDrug_PriceSyn', 'label' => 'Цена синонима', 'rules' => '', 'type' => 'float')
            ),
            'deleteWhsDocumentSupplySpecDrug' => array(
                array('field' => 'id', 'label' => 'Идентификатор синонима', 'rules' => 'required', 'type' => 'id')
            ),
            'loadWhsDocumentSupplySpecDrugList' => array(
                array('field' => 'WhsDocumentSupply_id', 'label' => 'Государственный контракт', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'WhsDocumentSupplySpec_id', 'label' => 'Позиция спецификации', 'rules' => '', 'type' => 'id'),
                array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
                array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int')
            ),
            'getWhsDocumentSupplySpecDrugContext' => array(
                array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Date', 'label' => 'Дата для расчета', 'rules' => '', 'type' => 'date')
            ),
            'loadSynonymSupplyCombo' => array(
                array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
            ),
            'loadSynonymSupplySpecCombo' => array(
                array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentSupplySpec_id', 'label' => 'Позиция спецификации', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
            ),
            'loadSynonymDrugCombo' => array(
                array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
                array('field' => 'Actmatters_id', 'label' => 'МНН', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
            ),
            'loadWhsDocumentProcurementRequestSpecCombo' => array(
                array('field' => 'WhsDocumentUc_id', 'label' => 'Лот', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequest_id', 'label' => 'Лот', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Позиция спецификации лота', 'rules' => '', 'type' => 'id'),
                array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
            ),
            'loadActmattersCombo' => array(
                array('field' => 'DrugComplexMnn_id', 'label' => 'Комплексное МНН', 'rules' => '', 'type' => 'id'),
                array('field' => 'Actmatters_id', 'label' => 'Действующее вещество', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
            ),
            'loadDrugCombo' => array(
                array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
                array('field' => 'Actmatters_id', 'label' => 'Действующее вещество', 'rules' => '', 'type' => 'int'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'DrugComplexMnn_id', 'label' => 'МНН', 'rules' => 'trim', 'type' => 'id')
            )
		 );
		$this->load->database();
		$this->load->model('WhsDocumentSupply_model', 'WhsDocumentSupply_model');
		$this->load->model('WhsDocumentSupplySpec_model', 'WhsDocumentSupplySpec_model');
		$this->load->model('WhsDocumentUcPriceHistory_model', 'WhsDocumentUcPriceHistory_model');
	}

	/**
	 *	Подписание договора
	 */
	function sign() {
        $this->load->helper("Options");
        $this->load->model("Options_model", "Options_model");
		$this->load->model('RlsDrug_model', 'RlsDrug_model');

		$data = $this->ProcessInputData('sign', true);
		if ($data === false) { return false; }

        $data['options'] = $this->Options_model->getOptionsAll($data);
		$response = $this->WhsDocumentSupply_model->sign($data);
		$this->Cancel_Error_Handle = true;

		$this->ProcessModelSave($response, true, 'Ошибка подписания договора')->ReturnData();
	}

	/**
	 *	Снятие подписания с договора
	 */
	function unsign() {
        //$this->load->helper("Options");
        //$this->load->model("Options_model", "Options_model");

		$data = $this->ProcessInputData('unsign', true);
		if ($data === false) { return false; }

        //$data['options'] = $this->Options_model->getOptionsAll($data);
		$response = $this->WhsDocumentSupply_model->unsign($data);
		$this->ProcessModelSave($response, true, 'Ошибка снятия подписания с договора')->ReturnData();
	}

	/**
	 *	Подписание дополнительного соглашения
	 */
	function signWhsDocumentSupplyAdditional() {
		$this->load->helper("Options");
		$this->load->model("Options_model", "Options_model");

		$data = $this->ProcessInputData('signWhsDocumentSupplyAdditional', true);
		$data['options'] = $this->Options_model->getOptionsAll($data);
		if ($data) {
			$response = $this->WhsDocumentSupply_model->signWhsDocumentSupplyAdditional($data);
			$this->ProcessModelSave($response, true, 'Ошибка подписания дополнительного соглашения')->ReturnData();
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
        $this->load->model('DrugNomen_model', 'DrugNomen_model');
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['WhsDocumentUc_pid'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentUc_pid($data['WhsDocumentUc_pid']);
			}
			if (isset($data['WhsDocumentUc_Num'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentUc_Num($data['WhsDocumentUc_Num']);
			}
			if (isset($data['WhsDocumentUc_Name'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentUc_Name($data['WhsDocumentUc_Name']);
			}
			if (isset($data['WhsDocumentType_id'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentType_id($data['WhsDocumentType_id']);
			}
			if (isset($data['WhsDocumentUc_Date'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentUc_Date($data['WhsDocumentUc_Date']);
			}
			if (isset($data['WhsDocumentStatusType_id'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentStatusType_id($data['WhsDocumentStatusType_id']);
			}
			if (isset($data['session']['org_id'])) {
				$this->WhsDocumentSupply_model->setOrg_aid($data['session']['org_id']);
			}
			if (isset($data['Org_sid'])) {
				$this->WhsDocumentSupply_model->setOrg_sid($data['Org_sid']);
			}
			if (isset($data['Org_cid'])) {
				$this->WhsDocumentSupply_model->setOrg_cid($data['Org_cid']);
			}
			if (isset($data['Org_pid'])) {
				$this->WhsDocumentSupply_model->setOrg_pid($data['Org_pid']);
			}
			if (isset($data['Org_rid'])) {
				$this->WhsDocumentSupply_model->setOrg_rid($data['Org_rid']);
			}
			if (isset($data['WhsDocumentUc_Sum'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentUc_Sum($data['WhsDocumentUc_Sum']);
			}
			if (isset($data['WhsDocumentSupply_id'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentSupply_id($data['WhsDocumentSupply_id']);
			}
			if (isset($data['WhsDocumentUc_id'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentUc_id($data['WhsDocumentUc_id']);
			}
			if (isset($data['WhsDocumentSupply_ProtNum'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentSupply_ProtNum($data['WhsDocumentSupply_ProtNum']);
			}
			if (isset($data['WhsDocumentSupply_ProtDate'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentSupply_ProtDate($data['WhsDocumentSupply_ProtDate']);
			}
			if (isset($data['WhsDocumentSupplyType_id'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentSupplyType_id($data['WhsDocumentSupplyType_id']);
			}
			if (isset($data['WhsDocumentSupply_BegDate'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentSupply_BegDate($data['WhsDocumentSupply_BegDate']);
			}
			if (isset($data['WhsDocumentSupply_ExecDate'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentSupply_ExecDate($data['WhsDocumentSupply_ExecDate']);
			}
			if (isset($data['DrugFinance_id'])) {
				$this->WhsDocumentSupply_model->setDrugFinance_id($data['DrugFinance_id']);
			}
			if (isset($data['WhsDocumentCostItemType_id'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentCostItemType_id($data['WhsDocumentCostItemType_id']);
			}
			if (isset($data['BudgetFormType_id'])) {
				$this->WhsDocumentSupply_model->setBudgetFormType_id($data['BudgetFormType_id']);
			}
			if (isset($data['WhsDocumentPurchType_id'])) {
				$this->WhsDocumentSupply_model->setWhsDocumentPurchType_id($data['WhsDocumentPurchType_id']);
			}
			if (isset($data['FinanceSource_id'])) {
				$this->WhsDocumentSupply_model->setFinanceSource_id($data['FinanceSource_id']);
			}
			if (isset($data['DrugNds_id'])) {
				$this->WhsDocumentSupply_model->setDrugNds_id($data['DrugNds_id']);
			}
			
			$response = $this->WhsDocumentSupply_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Договора поставок')->ReturnData();

			if (isset($data['DeliveryData']) && !empty($data['DeliveryData'])) {
				$this->WhsDocumentSupplySpec_model->saveDeliveryDataFromJSON(array(
					'WhsDocumentSupply_id' => $this->OutData['WhsDocumentSupply_id'],
					'WhsDocumentSupplySpec_id' => null,
					'graph_data' => $data['DeliveryData'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			if (isset($this->OutData['WhsDocumentSupply_id']) && $this->OutData['WhsDocumentSupply_id'] > 0) {
				//сохранение спецификации договора
				$response = $this->WhsDocumentSupplySpec_model->saveFromJSON(array(
					'WhsDocumentSupply_id' => $this->OutData['WhsDocumentSupply_id'],
					'json_str' => $data['SupplySpecJSON'],
					'pmUser_id' => $data['pmUser_id']
				));
				/*$this->ProcessModelSave($response, true, 'Ошибка при сохранении Спецификации договора')->ReturnData();*/

				//сохранение доп соглашений
				$response = $this->WhsDocumentSupply_model->saveSupplyAdditionalFromJSON(array(
					'WhsDocumentSupply_id' => $this->OutData['WhsDocumentSupply_id'],
					'json_str' => $data['SupplyAdditionalJSON'],
					'pmUser_id' => $data['pmUser_id']
				));
				//return false;

				$response = $this->WhsDocumentUcPriceHistory_model->saveWhsDocumentUcPriceHistoryFromJSON(array(
					'WhsDocumentUc_id' => $this->OutData['WhsDocumentSupply_id'],
					'SupplySpecJSON' => $data['SupplySpecJSON'],
					'pmUser_id' => $data['pmUser_id'],
					'WhsDocumentSupply_State' => $data['action']
				));

                //сохраняем данные о медикаментах в номенклатурный справочник
                $spec = $this->WhsDocumentSupplySpec_model->loadList(array(
                    'WhsDocumentSupply_id' => $this->OutData['WhsDocumentSupply_id']
                ));
                foreach($spec as $sp) {

					$nomenData = array(
						'pmUser_id' => $data['pmUser_id'],
						'DrugNds_id' => !empty($sp['DrugNds_id']) ? $sp['DrugNds_id'] : null,
						'Okei_id' => !empty($sp['Okei_id']) ? $sp['Okei_id'] : null
					);

                    $this->DrugNomen_model->addNomenData('Drug', $sp['Drug_id'], $nomenData);
                    $this->DrugNomen_model->addNomenData('DrugComplexMnn', $sp['DrugComplexMnn_id'], $nomenData);

                    $code = $this->DrugNomen_model->generateCodeForObject(array('Object'=>'DrugPrepFasCode','Drug_id' => $sp['Drug_id']));
                    $sp_params = array('Drug_id' => $sp['Drug_id'],'pmUser_id' => $data['pmUser_id']);
                    if(!empty($code[0]['DrugPrepFasCode_Code'])){
                    	$sp_params['DrugPrepFasCode_Code'] = $code[0]['DrugPrepFasCode_Code'];
                    } else {
                    	$sp_params['DrugPrepFasCode_Code'] = null;
                    }
                    $this->DrugNomen_model->addDrugPrepFasCodeByDrugId($sp_params);
                }
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение дополнительного соглашения
	 */
	function saveWhsDocumentSupplyAdditional() {
		$data = $this->ProcessInputData('saveWhsDocumentSupplyAdditional', true);
		if ($data){
			if (empty($data['WhsDocumentStatusType_id']) && !empty($data['WhsDocumentStatusType_Code'])) {
				$data['WhsDocumentStatusType_id'] = $this->WhsDocumentSupply_model->getObjectIdByCode('WhsDocumentStatusType', $data['WhsDocumentStatusType_Code']);
			}

			$response = $this->WhsDocumentSupply_model->saveObject('WhsDocumentSupply', $data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении дополнительного соглашения')->ReturnData();

			if (isset($this->OutData['WhsDocumentSupply_id']) && $this->OutData['WhsDocumentSupply_id'] > 0) {
				//сохранение спецификации договора
				$response = $this->WhsDocumentSupplySpec_model->saveFromJSON(array(
					'WhsDocumentSupply_id' => $this->OutData['WhsDocumentSupply_id'],
					'json_str' => $data['SupplySpecJSON'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->WhsDocumentSupply_model->setWhsDocumentSupply_id($data['WhsDocumentSupply_id']);
			$response = $this->WhsDocumentSupply_model->load();
			$response[0]['WhsDocumentDelivery_Data'] = $this->WhsDocumentSupply_model->loadWhsDocumentDeliveryList(array('WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']));
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function loadWhsDocumentSupplyAdditional() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyAdditional', true);
		if ($data){
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplyAdditional($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentSupply_model->loadList($filter);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->WhsDocumentSupply_model->setWhsDocumentSupply_id($data['id']);
			$this->WhsDocumentSupply_model->setpmUser_id($data['pmUser_id']);
			$response = $this->WhsDocumentSupply_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка лотов
	 */
	function loadWhsDocumentProcurementRequestList() {
		$data = $this->ProcessInputData('loadWhsDocumentProcurementRequestList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentProcurementRequestList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение спецификации лота
	 */
	function loadWhsDocumentProcurementRequestSpecList() {
		$data = $this->ProcessInputData('loadWhsDocumentProcurementRequestSpecList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentProcurementRequestSpecList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных для комбобокса "Номер ГК"
	 */
	function loadWhsDocumentSupplyCombo() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyCombo', true);
		if ($data) {
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplyCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных для комбобокса "Контракт"
	 */
	function loadWhsDocumentSupplySecondCombo() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplySecondCombo', true);
		if ($data) {
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplySecondCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка спецификации ГК
	 */
	function loadWhsDocumentSupplyList() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyList', true);
		if ($data) {
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplyList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка доп. соглашений
	 */
	function loadWhsDocumentSupplyAdditionalList() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyAdditionalList', true);
		if ($data) {
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplyAdditionalList($data);
			if (!empty($data['limit'])) {
				$this->ProcessModelMultiList($response, true, true)->ReturnData();
			} else {
				$this->ProcessModelList($response, true, true)->ReturnData();
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка доп. соглашений (используется в гриде на форме редактирования контракта)
	 */
	function loadWhsDocumentSupplyAdditionalShortList() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyAdditionalShortList', true);
		if ($data) {
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplyAdditionalShortList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение предельной цены (с учетом НДС) для конкретного медикамента
	 */
	function getMaxSalePrice() {
		$data = $this->ProcessInputData('getMaxSalePrice', true);
		if ($data){
			$response = $this->WhsDocumentSupply_model->getMaxSalePrice($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Генерация номера для ГК
	 */
	function generateNum() {
		$data = $this->ProcessInputData('generateNum', true);
		if ($data){
			$response = $this->WhsDocumentSupply_model->generateNum($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Загрузка синонима
     */
    function loadWhsDocumentSupplySpecDrug() {
        $data = $this->ProcessInputData('loadWhsDocumentSupplySpecDrug', true);
        if ($data){
            $response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplySpecDrug($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение синонима
     */
    function saveWhsDocumentSupplySpecDrug() {
        $this->load->model('DrugNomen_model', 'DrugNomen_model');
        $data = $this->ProcessInputData('saveWhsDocumentSupplySpecDrug', true);
        if ($data){
            $response = $this->WhsDocumentSupply_model->saveObject('WhsDocumentSupplySpecDrug', $data);

            if (empty($response['Error_Msg'])) {
                //при необходимости добавляем синоним в номенклатурный справочник
                $res = $this->DrugNomen_model->addNomenData('Drug', $data['Drug_sid'], array(
                    'pmUser_id' => $data['pmUser_id']
                ));
            }

            $this->ProcessModelSave($response, true, 'Ошибка при сохранении синонима')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаление синонима
     */
    function deleteWhsDocumentSupplySpecDrug() {
        $data = $this->ProcessInputData('deleteWhsDocumentSupplySpecDrug', true, true);
        if ($data) {
            $usage_data = $this->WhsDocumentSupply_model->checkSynonymUsage(array(
                'WhsDocumentSupplySpecDrug_id' => $data['id']
            ));

            if ($usage_data['cnt'] > 0) {
                $response = array('Error_Msg' => "Удаление синонима невозможно, т.к. его данные используются в документе {$usage_data['num']} от  {$usage_data['date']} {$usage_data['name']}");
            } else {
                $response = $this->WhsDocumentSupply_model->deleteObject('WhsDocumentSupplySpecDrug', array(
                    'WhsDocumentSupplySpecDrug_id' => $data['id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
            }

            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 *  Получение списка синонимов
	 */
	function loadWhsDocumentSupplySpecDrugList() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplySpecDrugList', true);
		if ($data){
			$response = $this->WhsDocumentSupply_model->loadWhsDocumentSupplySpecDrugList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение сопутствующих данных для синонима
	 */
	function getWhsDocumentSupplySpecDrugContext() {
		$data = $this->ProcessInputData('getWhsDocumentSupplySpecDrugContext', false);
		if ($data) {
			$response = $this->WhsDocumentSupply_model->getWhsDocumentSupplySpecDrugContext($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Загрузка данных для комбобокса "Контракт" на формах ввода синонимов
     */
    function loadSynonymSupplyCombo() {
        $data = $this->ProcessInputData('loadSynonymSupplyCombo', true);
        if ($data) {
            $response = $this->WhsDocumentSupply_model->loadSynonymSupplyCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка данных для комбобокса "Медикамент (из контракта)" на формах ввода синонимов
     */
    function loadSynonymSupplySpecCombo() {
        $data = $this->ProcessInputData('loadSynonymSupplySpecCombo', true);
        if ($data) {
            $response = $this->WhsDocumentSupply_model->loadSynonymSupplySpecCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка данных для комбобокса "Медикамент" на формах ввода синонимов
     */
    function loadSynonymDrugCombo() {
        $data = $this->ProcessInputData('loadSynonymDrugCombo', true);
        if ($data) {
            $response = $this->WhsDocumentSupply_model->loadSynonymDrugCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка позиций лота
     */
    function loadWhsDocumentProcurementRequestSpecCombo() {
        $data = $this->ProcessInputData('loadWhsDocumentProcurementRequestSpecCombo', false);
        if ($data) {
            $response = $this->WhsDocumentSupply_model->loadWhsDocumentProcurementRequestSpecCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка МНН
     */
    function loadActmattersCombo() {
        $data = $this->ProcessInputData('loadActmattersCombo', false);
        if ($data) {
            $response = $this->WhsDocumentSupply_model->loadActmattersCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка медикаментов
     */
    function loadDrugCombo() {
        $data = $this->ProcessInputData('loadDrugCombo', false);
        if ($data) {
            $response = $this->WhsDocumentSupply_model->loadDrugCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
}