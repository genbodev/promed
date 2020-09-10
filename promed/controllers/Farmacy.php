<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Farmacy - методы работы для модуля "Аптека"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      28.12.2009
* @property Farmacy_model $dbmodel
*/

class Farmacy extends swController {

	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
        $this->load->database();
		$this->load->model('Farmacy_model', 'dbmodel');
		//$this->load->model('ufa/Ufa_Farmacy_model', 'dbmodel');
		$this->inputRules = array(
			'checkEvnReceptProcessAbilty' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'evnReceptReleaseRollback' => array(
				array(
					'field' => 'EvnRecept_id',
					// 'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnReceptTrafficBook' => array(
				array(
					'field' => 'EvnRecept_obrDate',
					'label' => 'Дата обращения',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'EvnRecept_otpDate',
					'label' => 'Дата обращения',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'EvnRecept_setDate',
					'label' => 'Дата выписки рецепта',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Person_Birthday',
					'label' => 'Дата рождения пациента',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Person_Firname',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'russtring'
				),
				array(
					'field' => 'Person_Secname',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'russtring'
				),
				array(
					'field' => 'Person_Surname',
					'label' => 'Фамилия',
					'rules' => 'trim',
					'type' => 'russtring'
				),
				array(
					'field' => 'ReceptDelayType_id',
					'label' => 'Статус рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'deleteDocumentUc' => array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор учетного документа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteDocumentUcStr' => array(
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Идентификатор строки учетного документа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'searchEvnRecept' => array(
				array(
					'field' => 'EvnRecept_Num',
					'label' => 'Номер рецепта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_Ser',
					'label' => 'Серия рецепта',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'loadEvnReceptData' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
            'loadDrugLabResult' => array(
                array(
                    'field' => 'Contragent_id',
                    'label' => 'Контрагент',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
			'loadDocumentUcStrList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор выписанного медикамента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugMnn_id',
					'label' => 'Идентификатор МНН',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTorg_id',
					'label' => 'Идентификатор торгового наименования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_Kolvo',
					'label' => 'Количество',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnRecept_otpDate',
					'label' => 'Дата обращения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'date',
					'label' => 'Дата документа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'ReceptFinance_Code',
					'label' => 'Код типа финансирования рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
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
					'field' => 'DocumentUc_id',
					'label' => 'Документ учета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Строка документа учета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'is_personal',
					'label' => 'Персонифицированное списание',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Ost_DocumentUcStr_id',
					'label' => 'Строка из остатков',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadDocumentUcStrView' => array(
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Идентификатор строки документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
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
			'loadDocumentInvStrView' => array(
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Идентификатор строки документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => '',
					'type' => 'id'
				)
			),
			'evnReceptProcess' =>array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_obrDate',
					'label' => 'Дата обращения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnRecept_otpDate',
					'label' => 'Дата отоваривания рецепта',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'ProcessingType_Name',
					'label' => 'Тип обработки рецепта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'ReceptFinance_Code',
					'label' => 'Код типа финансирования',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'putEvnReceptOnDelay' =>array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_obrDate',
					'label' => 'Дата обращения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор оранизации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'receptNotification_phone',
					'label' => 'Номер телефона для оповещения',
					'rules' => '',
					'type' => 'string'
				),
			),
			
			'loadDrugPrepList' => array(
				array(
					'field' => 'DrugPrep_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Текущая строка документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Фасовка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFasCode_Code',
					'label' => 'Фасовка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugPrep_Name',
					'label' => 'Наименование медикамента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'date',
					'label' => 'Дата документа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'expenditure',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
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
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_Kolvo',
					'label' => 'Количество',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => '',
					'field' => 'load',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'id'
				)
			),

			'loadDrugList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Комбобокс "Медикамент"',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Расходная строка документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_Code',
					'label' => 'Код медикамента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Name',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'date',
					'label' => 'Дата документа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'default' => 'expenditure',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
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
					'field' => 'checking_exp_date',
					'label' => 'Учет срока годности',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'id'
				)
			),
			
			'loadDrugMultiList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Комбобокс "Медикамент"',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTorg_Name',
					'label' => 'Торговое наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugForm_Name',
					'label' => 'Форма выпуска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_PackName',
					'label' => 'Упаковка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Dose',
					'label' => 'Дозировка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Firm',
					'label' => 'Производитель',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'expenditure',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
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
					'field' => 'Storage_id',
					'label' => 'Склад',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			
			'loadContragentView'=> array(
				array(
					'default' => 0,
					'field' => 'ContragentType_id',
					'label' => 'Идентификатор типа контрагента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'Contragent_aid',
					'label' => 'Идентификатор контрагента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'mode',
					'label' => 'Режим',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ContragentOrg_Org_id',
					'label' => 'Связь с организацией',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadContragentEdit'=> array(
				array(
					'field' => 'Contragent_id',
					'label' => 'Идентификатор контрагента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadContragentList'=> array(
				array(
					'field' => 'Contragent_id',
					'label' => 'Идентификатор контрагента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FilterContragent_id',
					'label' => 'Идентификатор контрагента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FilterOrg_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ContragentType_id',
					'label' => 'Тип контрагента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ContragentType_CodeList',
					'label' => 'Список тиов контрагентов',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'mode',
					'label' => 'Режим',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Наименование контрагента',
					'rules' => '',
					'type' => 'string'
				)
			),
            'loadDrugFinanceListOld'=> array(
                array(
                    'field' => 'Contragent_id',
                    'label' => 'Идентификатор контрагента',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'ContragentType_Code',
                    'label' => 'Тип контрагента',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
			'loadMolView'=> array(
				array(
					'field' => 'Mol_id',
					'label' => 'Идентификатор МОЛ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Идентификатор контрагента',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveContragent'=> array(
				array(
					'field' => 'Contragent_id',
					'label' => 'Идентификатор контрагента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ContragentType_id',
					'label' => 'Тип контрагента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Аптека',
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
					'field' => 'Contragent_Code',
					'label' => 'Код',
					'rules' => 'required|numeric|no_zero',
					'type' => 'int'
				),
				array(
					'field' => 'Contragent_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'deleteContragent'=> array(
				array(
					'field' => 'Contragent_id',
					'label' => 'Идентификатор контрагента',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'checkContragentOrgInDocs'=> array(
				array(
					'field' => 'Contragent_id',
					'label' => 'Идентификатор контрагента',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'saveMol'=> array(
				array(
					'field' => 'Mol_id',
					'label' => 'Идентификатор материально-ответственного лица',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
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
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Storage_id',
					'label' => 'Склад',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Mol_Code',
					'label' => 'Код материально-ответсвенного лица',
					'rules' => 'required|numeric|no_zero',
					'type' => 'int'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Mol_begDT',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Mol_endDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				)
			),
			'saveDocumentUc'=> array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUc_Num',
					'label' => 'Номер документа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_setDate',
					'label' => 'Дата подписания',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_didDate',
					'label' => 'Дата поставки',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_DogNum',
					'label' => 'Номер договора',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_DogDate',
					'label' => 'Дата договора',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_Sum',
					'label' => 'Сумма',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUc_SumR',
					'label' => 'Сумма',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUc_SumR',
					'label' => 'Сумма розничная с НДС',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUc_SumNds',
					'label' => 'Сумма НДС',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUc_SumNdsR',
					'label' => 'Сумма НДС розничной',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUc_InvNum',
					'label' => 'Номер документа инвентаризации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_InvDate',
					'label' => 'Дата инвентаризации',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Contragent_sid',
					'label' => 'Поставщик',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Mol_sid',
					'label' => 'Материально-ответственное лицо поставщика',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Contragent_tid',
					'label' => 'Потребитель',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Mol_tid',
					'label' => 'Материально-ответственное лицо потребителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
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
					'field' => 'DrugDocumentType_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugDocumentStatus_id',
					'label' => 'Статус документа',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveDocumentUcStr'=> array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор учетного документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_godnDate',
					'label' => 'Срок годности',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Идентификатор строки учетного документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_IsLab',
					'label' => 'Рез. лаб. иссл.',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_oid',
					'label' => 'Идентификатор приходной строки учетного документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugNds_id',
					'label' => 'НДС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_Price',
					'label' => 'Цена (опт)',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUcStr_PriceR',
					'label' => 'Цена (розница)',
					'rules' => '',
					'type' => 'float'
				),
				// TO-DO: два нижеследующих удалить - в приныипе их уже не надо
				array(
					'field' => 'DocumentUcStr_RashCount',
					'label' => 'Количество (ед. уч.)',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUcStr_RashEdCount',
					'label' => 'Количество (ед. доз.)',
					'rules' => '',
					'type' => 'float'
				),
				
				array(
					'field' => 'DocumentUcStr_Count',
					'label' => 'Количество (ед. уч.)',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUcStr_EdCount',
					'label' => 'Количество (ед. доз.)',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUcStr_Ser',
					'label' => 'Серия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUcStr_NZU',
					'label' => 'НЗУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DocumentUcStr_Nds',
					'label' => 'НДС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUcStr_Sum',
					'label' => 'Сумма (опт)',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUcStr_SumR',
					'label' => 'Сумма (розница)',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Тип финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugProducer_New',
					'label' => 'Производитель',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugProducer_id',
					'label' => 'Производитель',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugLabResult_Name',
					'label' => 'Результат лабораторных исследований',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUcStr_CertNum',
					'label' => 'Номер сертификата',
					'rules' => '',
					'type' => 'string'
				)
			),
            'generateCode' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Lpu_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
			'saveDocumentInvUcStr'=> array(
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Идентификатор строки учетного документа',
					'rules' => '',
					'type' => 'id'
				),				
				array(
					'field' => 'DocumentUcStr_Count',
					'label' => 'Количество (ед. уч.)',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUcStr_EdCount',
					'label' => 'Количество (ед. доз.)',
					'rules' => 'required',
					'type' => 'float'
				),				
				array(
					'field' => 'DocumentUcStr_Sum',
					'label' => 'Сумма (опт)',
					'rules' => '',
					'type' => 'float'
				)
			),
			'loadDocumentUcView'=> array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugDocumentType_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' => 'id'
				), 
				array('field' => 'Contragent_sid', 'label' => 'Идентификатор поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_tid', 'label' => 'Идентификатор получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_sINN', 'label' => 'ИНН поставщика', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_tINN', 'label' => 'ИНН получателя', 'rules' => '', 'type' => 'string'),
				array('field' => 'Storage_sid', 'label' => 'Идентификатор склада поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_tid', 'label' => 'Идентификатор склада получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_tid', 'label' => 'МОЛ получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_setDate', 'label' => 'Дата подписания', 'rules' => '', 'type' => 'date'),
				array('field' => 'DocumentUc_setDate_range', 'label' => 'Период', 'type' => 'daterange'),
				array('field' => 'DocumentUc_date_range', 'label' => 'Дата исполнения документа', 'type' => 'daterange'),
				array('field' => 'begDate', 'label' => 'Начало периода', 'rules' => '', 'type' => 'date'),
				array('field' => 'endDate', 'label' => 'Конец периода', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentClass_id', 'label' => 'Вид заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentStatus_id', 'label' => 'Статус', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер контракта', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_sid', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugMnn_Name', 'label' => 'МНН', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugTorg_Name', 'label' => 'Торг.наим.', 'rules' => '', 'type' => 'string'),
				array('field' => 'pmUser', 'label' => 'Исполнитель', 'rules' => '', 'type' => 'string'),
				array('field' => 'Postms', 'label' => 'Постовая м/с', 'rules' => '', 'type' => 'string'),
				array('field' => 'Patient', 'label' => 'Пациент', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Склад', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUcStr_Reason', 'label' => 'Причина списания', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'filterByOrgUser', 'label' => 'Фильтвать по пользователю организации', 'rules' => '', 'type' => 'checkbox'),
				array('field' => 'MedService_Storage_id', 'label' => 'Склад прописанный на службе', 'rules' => '', 'type' => 'id')
			),
            'loadDocumentUcEdit'=>array(
                array(
                    'field' => 'DocumentUc_id',
                    'label' => 'Идентификатор документа',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DrugDocumentType_id',
                    'label' => 'Тип документа',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
			'saveDokInv'=> array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUc_Num',
					'label' => 'Номер документа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_setDate',
					'label' => 'Дата подписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_didDate',
					'label' => 'Дата поставки',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_DogNum',
					'label' => 'Номер договора',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_DogDate',
					'label' => 'Дата договора',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_Sum',
					'label' => 'Сумма',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocumentUc_InvNum',
					'label' => 'Номер документа инвентаризации',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_InvDate',
					'label' => 'Дата инвентаризации',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Contragent_sid',
					'label' => 'Поставщик',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Mol_sid',
					'label' => 'Материально-ответственное лицо поставщика',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Contragent_tid',
					'label' => 'Потребитель',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Mol_tid',
					'label' => 'Материально-ответственное лицо потребителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
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
					'field' => 'DrugDocumentType_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveDokNak'=> array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugDocumentType_id',
					'label' => 'Тип документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Действие',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'saveDocZayav' => array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUc_Num',
					'label' => 'Номер документа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentUc_setDate',
					'label' => 'Дата заявки',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_didDate',
					'label' => 'Дата поставки',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_planDate',
					'label' => 'Дата план',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_begDate',
					'label' => 'Начало периода использования',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_endDate',
					'label' => 'Окончание периода использования',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentUc_Sum',
					'label' => 'Сумма',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Contragent_sid',
					'label' => 'Исполнитель',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Contragent_tid',
					'label' => 'Потребитель',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugDocumentType_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugDocumentStatus_id',
					'label' => 'Статус заявки',
					'rules' => '',
					'default' => '',
					'type' => 'id'
				),
				array(
					'field' => 'changeStatus',
					'label' => 'Флаг изменения статуса',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'DocumentUcStrData',
					'label' => 'Данный позиций заявки на медикаменты',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadDocumentListByDay' => array(
				array('field' => 'begDate', 'label' => 'Дата начала периода расписания', 'rules' => '', 'type' => 'date'),
				array('field' => 'endDate', 'label' => 'Дата окончания периода расписания', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_DogNum', 'label' => 'Номер договора', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugDocumentStatus_id', 'label' => 'Статус документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_sid', 'label' => 'Идентификатор постовщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_tid', 'label' => 'Идентификатор получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_Date', 'label' => 'Дата документа', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugMnn_id', 'label' => 'МНН медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id')
			),
			'saveDokDef'=> array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id'),				
				array('field' => 'action', 'label' => 'Действие', 'rules' => 'required', 'type' => 'string')
			),
			'saveDokDemand'=> array(
				//array('field' => 'action', 'label' => 'Действие', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_sid', 'label' => 'Поставщик', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_sid', 'label' => 'Материально-ответственное лицо поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_tid', 'label' => 'Получатель', 'rules' => '','type' => 'id'),
				array('field' => 'Mol_tid', 'label' => 'Материально-ответственное лицо получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DocumentUc_setDate', 'label' => 'Дата подписания', 'rules' => 'required', 'type' => 'date'),
				//array('field' => 'DocumentUc_didDate', 'label' => 'Дата поставки', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'DocumentUc_DogNum', 'label' => 'Номер договора', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_DogDate', 'label' => 'Дата договора', 'rules' => '', 'type' => 'date')
			),
			'executeDocumentUc'=> array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id')
			),
			'provideEvnRecept'=> array(
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_id', 'label' => 'Идентификатор рецепта', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnReceptGeneral_id', 'label' => 'Идентификатор рецепта', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'quantity', 'label' => 'Количество медикамента', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugOstatDataJSON', 'label' => 'Данные об остатках', 'rules' => '', 'type' => 'string'),
				array('field' => 'EvnRecept_otpDate', 'label' => 'Дата обеспечения', 'rules' => '', 'type' => 'string'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Идентификатор статьи расхода', 'rules' => '', 'type' => 'int')
				, array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'int')
			),
			'loadContragentDocumentsList' => array(
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => 'required', 'type' => 'id')
			),
			'importDocumentUc' => array(
				array('field' => 'DrugDocumentType_id', 'label' => 'Тип документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Контрагент', 'rules' => 'required', 'type' => 'id')
			),
			'importDokNak' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_id', 'label' => 'Накладная', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Контрагент', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_setDate', 'label' => 'Накладная от', 'rules' => '', 'type' => 'date'),
				array('field' => 'DocumentUc_InvoiceDate', 'label' => 'Счет-фактура от', 'rules' => '', 'type' => 'date'),
				array('field' => 'DocumentUc_InvoiceNum', 'label' => 'Счет-фактура №', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_didDate', 'label' => 'Дата поставки', 'rules' => '', 'type' => 'date'),
				array('field' => 'Contragent_sid', 'label' => 'Поставщик', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_tid', 'label' => 'Получатель', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_tid', 'label' => 'Склад', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_tid', 'label' => 'МОЛ', 'rules' => '', 'type' => 'id'),
				array('field' => 'Note_id', 'label' => 'Идентификатор примечания', 'rules' => '', 'type' => 'id'),
				array('field' => 'Note_Text', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
                array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id')
			),
			'getDrugOstatForProvide' => array(
				array('field' => 'DrugOstatRegistry_id', 'label' => 'Идентификатор партии', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_id', 'label' => 'Идентификатор рецепта', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnReceptGeneral_id', 'label' => 'Идентификатор рецепта', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Идентификатор статьи расхода', 'rules' => '', 'type' => 'int'),
				array('field' => 'subAccountType_id', 'label' => 'Идентификатор субсчета', 'rules' => '', 'type' => 'int')
			),
            'getDrugOstatForProvideFromBarcode' => array(
                array(
                    'field' => 'Drug_ean',
                    'label' => 'Код EAN',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'DrugFinance_id',
                    'label' => 'Финансирование',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'WhsDocumentCostItemType',
                    'label' => 'Программа',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'MedService_id',
                    'label' => 'Служба',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'DrugComplexMnn_id',
                    'label' => 'Идентификатор комплексного МНН',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'Sin_check',
                    'label' => 'Синонимическая замена',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'Drug_id',
                    'label' => 'Идентификатор медикамента rls',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Drugnomen_Code',
                    'label' => 'Код',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'EvnRecept_id',
                    'label' => 'Идентитфикатор обеспечиваемого рецепта',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'DrugPackageBarCode_BarCode',
                    'label' => 'Штрих-код',
                    'rules' => '',
                    'type'  => 'string'
                ),
				array(
					'field' => 'SubAccountTypeIsReserve',
					'label' => 'Признак резервирования медикамента',
					'rules' => '',
					'type'  => 'int'
				)
            ),
			'getLpuSectionContragent' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id')
			),
			'createDocumentForReagentConsumption' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения ЛПУ', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaListJSON', 'label' => 'Список услуг', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Date', 'label' => 'Дата', 'rules' => 'required', 'type' => 'string')
			),
			'setDocumentUcStatus' => array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugDocumentStatus_id',
					'label' => 'Иденитификатор статуса',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getAllowedDrugDocumentStatusConfig' => array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugDocumentType_id',
					'label' => 'Код типа документа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadDrugDocumentStatusHistoryGrid' => array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'createDocumentUcStrListByWhsDocumentSupply'=> array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор контракта', 'rules' => 'required', 'type' => 'id')
			),
			'loadWhsDocumentSupplyList' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер контракта', 'rules' => '', 'type' => 'string'),
				array('field' => 'WhsDocumentUc_DateRange', 'label' => 'Дата контракта', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'Contragent_sid', 'label' => 'Поставщик', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_Name', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'string'),
				array('field' => 'WhsDocumentCostItemType_Name', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugRequest_Name', 'label' => 'Заявка', 'rules' => '', 'type' => 'string'),
				array('field' => 'OrgSidOstatExists', 'label' => 'Признак наличия остатков на поставщике', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'WhsDocumentType_Code', 'label' => 'Код типа документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'WhsDocumentType_CodeList', 'label' => 'Список кодов типов документов', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'WhsDocumentStatusType_Code', 'label' => 'Код статуса документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '','type' => 'string'),
				array('field' => 'Org_cid', 'label' => 'Идентификатор организации заказчика', 'rules' => '','type' => 'id'),
				array('field' => 'OrgCid_Name', 'label' => 'Наименование организации заказчика', 'rules' => '','type' => 'string'),
				array('field' => 'OrgSid_Name', 'label' => 'Наименование организации поставщика', 'rules' => '','type' => 'string'),
				array('field' => 'WhsDocumentRightRecipientOrg_id', 'label' => 'Организации указанная получателем товара по ГК', 'rules' => '','type' => 'id'),
                array('field' => 'OrgFilter_Type', 'label' => 'Фильтр по организации: тип', 'rules' => '', 'type' => 'string'),
                array('field' => 'OrgFilter_Org_cid', 'label' => 'Фильтр по организации: заказчик', 'rules' => '', 'type' => 'string'),
                array('field' => 'OrgFilter_Org_pid', 'label' => 'Фильтр по организации: плательщик', 'rules' => '', 'type' => 'string'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int')
			),
			'copyDocumentUcStr' => array(
				array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор строки учетного документа', 'rules' => '', 'type' => 'id')
			),
            'loadDocumentUcStrListByEvnCourseTreatDrug' => array(
                array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id')
            ),
			'loadWhsDocumentRequestList'=> array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugDocumentType_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' => 'id'
				), 
				array('field' => 'Contragent_sid', 'label' => 'Идентификатор поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_tid', 'label' => 'Идентификатор получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_sid', 'label' => 'Идентификатор склада поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_tid', 'label' => 'Идентификатор склада получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_tid', 'label' => 'МОЛ получателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_setDate', 'label' => 'Дата подписания', 'rules' => '', 'type' => 'date'),
				array('field' => 'DocumentUc_setDate_range', 'label' => 'Период', 'type' => 'daterange'),
				array('field' => 'begDate', 'label' => 'Начало периода', 'rules' => '', 'type' => 'date'),
				array('field' => 'endDate', 'label' => 'Конец периода', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentClass_id', 'label' => 'Вид заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentStatus_id', 'label' => 'Статус', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер контракта', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы товароведа', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_sid', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugMnn_Name', 'label' => 'МНН', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugTorg_Name', 'label' => 'Торг.наим.', 'rules' => '', 'type' => 'string'),
				array('field' => 'pmUser', 'label' => 'Исполнитель', 'rules' => '', 'type' => 'string'),
				array('field' => 'Postms', 'label' => 'Постовая м/с', 'rules' => '', 'type' => 'string'),
				array('field' => 'Patient', 'label' => 'Пациент', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Склад', 'rules' => '', 'type' => 'id'),
				array('field' => 'UserMedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
			),
			'loadDrugFinanceGrid'=> array(
			),
			'loadDrugFinanceList'=> array(
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id')
			),
			'loadDrugFinanceForm'=> array(
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => 'required', 'type' => 'id')
			),
			'saveDrugFinance'=> array(
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор источника финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_Code', 'label' => 'Код источника финансирования', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'DrugFinance_Name', 'label' => 'Наименование источника финансирования', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'DrugFinance_SysNick', 'label' => 'Ник источника финансирования', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'DrugFinance_begDate', 'label' => 'Начало действия источника финансирования', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugFinance_endDate', 'label' => 'Окончание действия источника финансирования', 'rules' => '', 'type' => 'date'),
				array('field' => 'Region_id', 'label' => 'Регион', 'rules' => '', 'type' => 'int'),
			),
			'loadBudgetFormTypeGrid'=> array(
			),
			'loadBudgetFormTypeList'=> array(
			),
			'loadBudgetFormTypeForm'=> array(
				array('field' => 'BudgetFormType_id', 'label' => 'Целевая статья', 'rules' => 'required', 'type' => 'id')
			),
			'generateBudgetFormTypeCode'=> array(
			),
			'saveBudgetFormType'=> array(
				array('field' => 'BudgetFormType_id', 'label' => 'Идентификатор целевой статьи', 'rules' => '', 'type' => 'id'),
				array('field' => 'BudgetFormType_Code', 'label' => 'Код целевой статьи', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'BudgetFormType_Name', 'label' => 'Наименование (им.падеж) целевой статьи', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'BudgetFormType_NameGen', 'label' => 'Наименование (род.падеж) целевой статьи', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'BudgetFormType_begDate', 'label' => 'Начало действия целевой статьи', 'rules' => '', 'type' => 'date'),
				array('field' => 'BudgetFormType_endDate', 'label' => 'Окончание действия целевой статьи', 'rules' => '', 'type' => 'date'),
				array('field' => 'Region_id', 'label' => 'Регион', 'rules' => '', 'type' => 'int'),
			),
			'loadWhsDocumentCostItemTypeGrid'=> array(
			),
			'loadWhsDocumentCostItemTypeList'=> array(
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => '', 'type' => 'id')
			),
			'loadWhsDocumentCostItemTypeForm'=> array(
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => 'required', 'type' => 'id')
			),
			'generateWhsDocumentCostItemTypeCode'=> array(
			),
			'saveWhsDocumentCostItemType'=> array(
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Идентификатор статьи расхода', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_Code', 'label' => 'Код статьи расхода', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'WhsDocumentCostItemType_Nick', 'label' => 'Ник наименование статьи расхода', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'WhsDocumentCostItemType_Name', 'label' => 'Краткое наименование статьи расхода', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'WhsDocumentCostItemType_FullName', 'label' => 'Полное наименование статьи расхода', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа регистра', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор источника финансирования', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DocNormative_id', 'label' => 'Идентификатор нормативного акта', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_isDLO', 'label' => 'Флаг ЛЛО', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'WhsDocumentCostItemType_isPersonAllocation', 'label' => 'Флаг персональной разнарядки', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'WhsDocumentCostItemType_isPrivilegeAllowed', 'label' => 'Флаг разрешения выписки всем МО из заявки главного внештатного специалиста', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'WhsDocumentCostItemType_isDrugRequest', 'label' => 'Флаг проведения заявочных кампаний', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'WhsDocumentCostItemType_begDate', 'label' => 'Начало действия статьи расхода', 'rules' => '', 'type' => 'date'),
				array('field' => 'WhsDocumentCostItemType_endDate', 'label' => 'Окончание действия статьи расхода', 'rules' => '', 'type' => 'date'),
				array('field' => 'Region_id', 'label' => 'Регион', 'rules' => '', 'type' => 'int'),
			),
			'loadFinanceSourceGrid'=> array(
			),
			'loadFinanceSourceForm'=> array(
				array('field' => 'FinanceSource_id', 'label' => 'Идентефикатор финансирования контрактов', 'rules' => 'required', 'type' => 'id')
			),
			'generateFinanceSourceCode'=> array(
			),
			'saveFinanceSource'=> array(
				array('field' => 'FinanceSource_id', 'label' => 'Идентификатор финансирования контрактов', 'rules' => '', 'type' => 'id'),
				array('field' => 'FinanceSource_Code', 'label' => 'Код финансирования контрактов', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'FinanceSource_Name', 'label' => 'Полное официальное наименование источника финансирования', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'FinanceSource_SuppName', 'label' => 'Полное официальное наименование контракта', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'FinanceSource_begDate', 'label' => 'Начало действия финансирования контрактов', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'FinanceSource_endDate', 'label' => 'Окончание действия финансирования контрактов', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор бюджета', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Идентификатор статьи расхода', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'BudgetFormType_id', 'label' => 'Идентификатор целевой статьи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Region_id', 'label' => 'Регион', 'rules' => '', 'type' => 'int'),
			),
			'loadDrugOstatRegistryGrid'=> array(
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор контракта', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор источника финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Идентификатор статьи расхода', 'rules' => '', 'type' => 'id'),
				array('field' => 'Actmatters_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugPrep_id', 'label' => 'Идентификатор препарата', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugComplexMnnName_Name', 'label' => 'МНН', 'rules' => '', 'type' => 'string'),
                array('field' => 'DrugTorg_Name', 'label' => 'Торговое наименование', 'rules' => '', 'type' => 'string'),
                array('field' => 'SubAccountType_id', 'label' => 'Тип субсчета', 'rules' => '', 'type' => 'id'),
                array('field' => 'PrepSeries_IsDefect', 'label' => 'Признак забраковки серии', 'rules' => '', 'type' => 'id'),
                array('field' => 'PrepSeries_MonthCount_Max', 'label' => 'Максимальное количество целых месяцев до истечения срока годности', 'rules' => '', 'type' => 'int'),
                array('field' => 'Sort_Type', 'label' => 'Тип сортировки', 'rules' => '', 'type' => 'string'),
                array('field' => 'only_doc_str_linked', 'label' => 'Только привязанные к документам учета', 'rules' => '', 'type' => 'int'),
                array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int'),
                array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int')
			),
			'getGoodsPackCount'=> array(
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор единицы измерения', 'rules' => '', 'type' => 'id')
			),
			'checkGoodsPackCount'=> array(
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id')
			),
			'saveReceptNotification' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'receptNotification_phone',
					'label' => 'Номер телефона',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'receptNotification_setDate',
					'label' => 'Дата оповещения',
					'rules' => '',
					'type' => 'date'
				)
			    )
		);
	}


	/**
	*  Проверка возможности отоваривания рецепта, если отдел "Дорогостой" и тип финансирования рецепта "Федеральный бюджет"
	*  Входящие данные: $_POST['EvnRecept_id']
	*  На выходе: JSON-строка
	*  Используется: форма отоваривания рецепта
	*/
	function checkEvnReceptProcessAbilty() {
		$this->load->model('Farmacy_model', 'dbmodel');

		$val  = array();

		$data = $this->ProcessInputData('checkEvnReceptProcessAbilty', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->checkEvnReceptProcessAbilty($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( isset($response[0]['Drug_Count']) ) {
				$val['Error_Msg'] = '';

				if ( $response[0]['Drug_Count'] > 0 ) {
					$val['EvnRecept_id'] = $data['EvnRecept_id'];
				}
				else {
					$val['EvnRecept_id'] = -1;
				}
			}
			else {
				$val['Error_Msg'] = 'Ошибка при проверке возможности отоваривания рецепта';
				$val['EvnRecept_id'] = -1;
			}
		}
		else {
			$val['Error_Msg'] = 'Ошибка при проверке возможности отоваривания рецепта';
			$val['EvnRecept_id'] = -1;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Удаление документа
	*  Входящие данные: $_POST['DocumentUc_id']
	*  На выходе: JSON-строка
	*  Используется: форма отоваривания рецепта
	*/
	function deleteDocumentUc() {
		$this->load->model('Farmacy_model', 'dbmodel');

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteDocumentUc', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->deleteDocumentUc($data);
		/*if ((is_array($response)) && (count($response) > 0)) {
			if (!isset($response['Error_Msg']) || strlen($response['Error_Msg']) == 0) {
				$val['success'] = true;
			} else {
				$val = $response;
				$val['success'] = false;
			}
		} else {
			$val['Error_Msg'] = 'При удалении учетного документа возникли ошибки';
			$val['success'] = false;
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);*/
        $this->ProcessModelSave($response,true,'При удалении учетного документа возникли ошибки')->ReturnData();
		return true;
	}


	/**
	*  Удаление услуги
	*  Входящие данные: $_POST['DocumentUcStr_id']
	*  На выходе: JSON-строка
	*  Используется: форма отоваривания рецепта
	*/
	function deleteDocumentUcStr() {
		$this->load->model('Farmacy_model', 'dbmodel');

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteDocumentUcStr', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->deleteDocumentUcStr($data);

		/*if ( (is_array($response)) && (count($response) > 0) ) {
			if ( (isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0) ) {
				$val['success'] = true;
			}
			else {
				$val = $response[0];
				$val['success'] = false;
			}
		}
		else {
			$val['Error_Msg'] = 'При удалении строки учетного документа возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);*/
        $this->ProcessModelSave($response,true,'При удалении строки учетного документа возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 * Обработка рецепта
	 */
	function evnReceptProcess() {
		$this->load->model("Farmacy_model", "dbmodel");

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('evnReceptProcess', true);
		if ($data === false)
		{
			return false;
		}

		if ( !in_array($data['ProcessingType_Name'], array('release', 'reserve')) ) {
            $this->ReturnError('Неверный тип обработки рецепта');
			return false;
		}

		if ( $data['ProcessingType_Name'] == 'release' && !isset($data['EvnRecept_otpDate']) ) {
            $this->ReturnError('Не задана дата отпуска рецепта');
			return false;
		}

		$documentUcStrData = array();

		if ( isset($_POST['documentUcStrData']) && strlen(trim($_POST['documentUcStrData'])) > 0 && trim($_POST['documentUcStrData']) != '[]' ) {
			$documentUcStrData = json_decode(trim($_POST['documentUcStrData']), true);
		}

		if ( !is_array($documentUcStrData) || count($documentUcStrData) == 0 ) {
            $this->ReturnError('Не указаны медикаменты для отоваривания рецепта или постановки рецепта на резерв');
			return false;
		}

		$data['documentUcStrData'] = $documentUcStrData;

		$response = $this->dbmodel->evnReceptProcess($data);

		/*if ( is_array($response) && count($response) > 0 ) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');

			if ( strlen($response[0]['Error_Msg']) == 0 ) {
				$response[0]['success'] = true;
			}
			else {
				$response[0]['success'] = false;
			}

			$val = $response[0];
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);*/
        $this->ProcessModelSave($response,true,'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}

	/**
	 * Отмена отоваривания рецепта
	 */
	function evnReceptReleaseRollback() {
		$this->load->model("Farmacy_model", "dbmodel");

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('evnReceptReleaseRollback', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->evnReceptReleaseRollback($data);
        /*
		if ( is_array($response) && count($response) > 0 ) {
			if ( strlen($response[0]['Error_Msg']) == 0 ) {
				$response[0]['success'] = true;
			}
			else {
				$response[0]['success'] = false;
			}

			$val = $response[0];
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (отмена отоваривания рецепта)');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);*/
        $this->ProcessModelSave($response,true,'Ошибка при выполнении запроса к базе данных (отмена отоваривания рецепта)')->ReturnData();

		return true;
	}


	/**
	* Получение списка партий по остаткам медикамента
	* Используется в окне отоваривания рецепта
	*/
	function loadDocumentUcStrList() {
		$this->load->model("Farmacy_model", "dbmodel");

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadDocumentUcStrList', false);
		if ($data === false)
		{
			return false;
		}

		$session_data = getSessionParams();
		$data['Lpu_id'] = $session_data['Lpu_id'];
		
		$data['isFarmacy'] = (isset($_SESSION['OrgFarmacy_id']));
		
		$response = $this->dbmodel->loadDocumentUcStrList($data);

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');

				if ( isset($row['DocumentUcStr_Price']) ) {
					$row['DocumentUcStr_Price'] = number_format($row['DocumentUcStr_Price'], 2, '.', '');
				}

				if ( isset($row['DocumentUcStr_PriceR']) ) {
					$row['DocumentUcStr_PriceR'] = number_format($row['DocumentUcStr_PriceR'], 2, '.', '');
				}

				if ( isset($row['DocumentUcStr_Count']) ) {
					$row['DocumentUcStr_Count'] = number_format($row['DocumentUcStr_Count'], 4, '.', '');
				}

				$val[] = $row;
			}
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	 * Получение списка документов определенного типа 
	 */
	function loadDocumentUcView() {
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadDocumentUcView', false);
		if ($data === false) {return false;}

        $session_data = getSessionParams();
		$data['session'] = $session_data['session'];
        $data['Lpu_id'] = $session_data['Lpu_id'];
        if (empty($data['Contragent_id']) && !empty($session_data['Contragent_id'])) {
            $data['Contragent_id'] = $session_data['Contragent_id'];
        }

		$response = $this->dbmodel->loadDocumentUcView($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Получение списка документов определенного типа 
	 */
	function farm_loadDocumentUcView() {
		$this->load->model("Farmacy_model", "dbmodel");

		$val  = array();

		$val['data'] = array();
		$val['totalCount'] = 0;

        $data = $this->ProcessInputData('loadDocumentUcView', false);
        $session_data = getSessionParams();

        $data['Lpu_id'] = $session_data['Lpu_id'];
        if (empty($data['Contragent_id']) && !empty($session_data['Contragent_id'])) {
            $data['Contragent_id'] = $session_data['Contragent_id'];
        }

        if ($data === false) {return false;}

		$response = $this->dbmodel->farm_loadDocumentUcView($data);
		
		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val['data'][] = $row;
			}
			$val['totalCount'] = $response['totalCount'];
		}
		$this->ReturnData($val);

		return true;
	}

	/**
	 * Получение документа определенного типа 
	 */
	function loadDocumentUcEdit() {

        $this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadDocumentUcEdit',true,true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadDocumentUcEdit($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение строк документа определенного документа или вообще определенной строки
	 */
	function loadDocumentUcStrView() {
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadDocumentUcStrView',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadDocumentUcStrView($data);
        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение строк документа инвернтаризационной ведомости или вообще определенной строки
	 */
	function loadDocumentInvStrView() {
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadDocumentInvStrView',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->loadDocumentInvStrView($data);
        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение списка медикаментов, доступных для отоваривания
	 * Используется в окне редактирования товарной позиции
	 */
	function loadDrugList() {
		$this->load->model("Farmacy_model", "dbmodel");

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadDrugList', false);
		if ($data === false)
		{
			return false;
		}

		$session_data = getSessionParams();
		$data['Lpu_id'] = $session_data['Lpu_id'];

		$response = $this->dbmodel->loadDrugList($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка медикаментов (DrugPrepList), доступных для выбора
	 * Используется в окне редактирования товарной позиции (первый комбобокс)
	 */
	function loadDrugPrepList() {
		$this->load->model("Farmacy_model", "dbmodel");
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadDrugPrepList', false);
		if ($data === false)
		{
			return false;
		}

		$session_data = getSessionParams();
		$data['Lpu_id'] = $session_data['Lpu_id'];

		$response = $this->dbmodel->loadDrugPrepList($data);

        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	*  Функция чтения списка медикаментов 
	*  На выходе: JSON-строка
	*  Используется: форма поиска медикаментов
	*/
	function loadDrugMultiList() {
		$this->load->model("Farmacy_model", 'dbmodel');

		$data = $this->ProcessInputData('loadDrugMultiList', false);
		if ($data) {
			$session_data = getSessionParams();
			$data['Lpu_id'] = $session_data['Lpu_id'];

			$response = $this->dbmodel->loadDrugMultiList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Получение "сгенерированного" кода Мол
	 */
	function generateMolCode() 
	{
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('generateCode',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->generateMolCode($data);
        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение "сгенерированного" кода контрагента
	 */
	function generateContragentCode() 
	{
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('generateCode',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->generateContragentCode($data);
        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение информации о рецепте
	 * Используется в окне обработки рецептов
	 */
	function loadEvnReceptData() {
		$this->load->model("Farmacy_model", "dbmodel");

		$data = $this->ProcessInputData('loadEvnReceptData', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnReceptData($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Чтение списка контрагентов
	 * Используется для формы просмотра  контрагентов
	 */
	function loadContragentView() {
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadContragentView',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadContragentView($data);

        $this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Формирование списка контрагентов для определенной аптеки или определенного ЛПУ 
	 */
	function loadContragentList()
	{
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadContragentList',true);
        if ($data === false) {return false;}
		$response = $this->dbmodel->loadContragentList($data);

        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

    /**
     * Формирование списка источников финансирования для определенного отделения в зависимости от наличия остатков медикаментов
     */
    function loadDrugFinanceListOld()
    {
        $this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadDrugFinanceListOld',true);
        if ($data === false) {return false;}
        $response = $this->dbmodel->loadDrugFinanceListOld($data);

        $this->ProcessModelList($response,true,true)->ReturnData();
        return true;
    }

	/**
	 * Чтение одного контрагента в редактировании
	 * Используется для формы просмотра, редактирования МОЛ и для грида 
	 */
	function loadContragentEdit() {
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadContragentEdit',true);
        if ($data == false) {return false;}

		$response = $this->dbmodel->loadContragentEdit($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Результаты лабораторных исследований
	 */
	function loadDrugLabResult()
	{
		$this->load->model("Farmacy_model", "dbmodel");

		//$data = array();
		$val  = array();
		
		/*$data = $_POST;
		$data = array_merge(getSessionParams(), $data);
		*/
        $data = $this->ProcessInputData('loadDrugLabResult',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadDrugLabResult($data);
		/*if ( is_array($response) && count($response) > 0 )
		{
			foreach ($response as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		else {
			$val['success'] = false;
		}
		$this->ReturnData($val);*/
        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	* Чтение списка МОЛ
	* Используется для формы просмотра, редактирования МОЛ и для грида 
	*/
	function loadMolView() {
		$this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('loadMolView', false);
        if ($data === false) {return false;}

		$sessionParams = getSessionParams();
		$data['session'] = $sessionParams['session'];

		$response = $this->dbmodel->loadMolView($data);

        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	
	/**
	 * Проверка на двойное заведение контрагента
	 */
	function checkDoubleContragent($model, $data) {
		$err_msg = "";
		$result = $model->checkDoubleContragent($data);

		if (is_array($result) && (count($result) > 0)) {
			if ($result[0]['checkCount'] > 0) {
				$err_msg = "Сохранение не возможно! Данный контрагент уже занесен!";
			}
		} else {
			$err_msg = "При выполнении проверки контрагента<br/>текущей записи сервер базы данных вернул ошибку!";
		}

		return $err_msg;
	}

	/**
	 * Удаление контрагента
	 */
	function deleteContragent() {
		$this->load->model('Farmacy_model', 'dbmodel');
		$data = $this->ProcessInputData('deleteContragent', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->deleteContragent($data);

		$this->ProcessModelSave($response,true,'При удалении контрагента возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Проверка наличия контрагента в документах
	 */
	function checkContragentOrgInDocs() {
		$this->load->model('Farmacy_model', 'dbmodel');
		$data = $this->ProcessInputData('checkContragentOrgInDocs', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->checkContragentOrgInDocs($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Логические проверки
	 */
	function getObjectCheck($model, $data, $method)
	{
		// Логические проверки
		Switch ($method)
		{
			case 'saveContragent':
				return $this->checkDoubleContragent($model, $data);
				break;
				
			default:
				return '';
				break;
		}
	}
	
	/**
	 * Выбор метода сохранения
	 */
	function choiseMethod($model, $data, $method)
	{
		Switch ($method)
		{
			/*case 'saveContragent':
				return $model->saveContragent($data);
				break;*/
			case 'saveMol':
				return $model->saveMol($data);
				break;
			case 'saveDocumentUc':
			case 'saveDokInv':
			case 'saveDokDemand':				
				return $model->saveDocumentUc($data);
				break;	
			case 'saveDokNak':
				return $model->saveDokNak($data);
				break;
			case 'saveDocZayav':
				return $model->saveDocZayav($data);
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 *  Сохраненеи
	 */
	function save() {
		if (!isset($_SESSION['login'])) {
			header("Location: /?c=promed");
		}
		$method=$_REQUEST['method'];
		
		if (isset($method)) {
			switch ($method) {
				case 'DokSpis':
					$_POST['DrugDocumentType_id'] = 2; // Документ списания медикаментов
					$method = 'saveDocumentUc';
					break;
				case 'DokOst':
					$_POST['DrugDocumentType_id'] = 3; // Документ ввода остатков
					//$_POST['Contragent_tid'] = $_SESSION['Contragent_id']; 
					$method = 'saveDocumentUc';
					break;
				case 'DokInv':
					$_POST['DrugDocumentType_id'] = 4; // Инвентаризационная ведомость
					$method = 'saveDokInv';
					break;
				case 'DokOstKor':
					$_POST['DrugDocumentType_id'] = 5; // Корректировка остатков
					$method = 'saveDocumentUc';
					break;
				case 'DokPrRas': 
					$_POST['DrugDocumentType_id'] = 1; // Документ прихода/расхода медикаментов
					$method = 'saveDocumentUc';
					break;
				case 'DokUcLpu': 
					$_POST['DrugDocumentType_id'] = 1; // Документ прихода/расхода медикаментов В ЛПУ
					$method = 'saveDocumentUc';
					break;
				case 'DokNak': 
					$_POST['DrugDocumentType_id'] = 6; // Накладная
					$method = 'saveDokNak';
					break;
				case 'DokDef': 
					$_POST['DrugDocumentType_id'] = 7; // Дефектура
					$method = 'saveDokDef';
					break;
				case 'DokDemand': 
					$_POST['DrugDocumentType_id'] = 8; // Заявка
					$method = 'saveDokDemand';
					break;
				case 'DocZayav':
					$_POST['DrugDocumentType_id'] = 9; // Внутренняя заявка
					$method = 'saveDocZayav';
					break;
			}
		}
		if (isset($method)) {
			$this->saveObject($method);
		}
		else {
			header("Location: /?c=promed");
		}
	}

	/**
	 *  Сохранение без предопределенного типа
	 */
	function saveDocumentUc() {
		$this->saveObject('saveDocumentUc');
	}

	/**
	 *  Загрузка
	 */
	function load() {
		if (!isset($_SESSION['login'])) {
			header("Location: /?c=promed");
		}
		
		if (isset($_REQUEST['method'])) {
			switch ($_REQUEST['method']) {
				case 'DokSpis':
					$_POST['DrugDocumentType_id'] = 2; // Документ списания медикаментов
					break;
				case 'DokOst':
					$_POST['DrugDocumentType_id'] = 3; // Документ ввода остатков
					break;
				case 'DokInv':
					$_POST['DrugDocumentType_id'] = 4; // Инвентаризационная ведомость
					break;
				case 'DokOstKor':
					$_POST['DrugDocumentType_id'] = 5; // Корректировка остатков
					break;
				case 'DokPrRas': 
					$_POST['DrugDocumentType_id'] = 1; // Документ прихода/расхода медикаментов
					break;
				case 'DokNak': 
					$_POST['DrugDocumentType_id'] = 6; // Накладная
					break;
				case 'DocZayav':
					$_POST['DrugDocumentType_id'] = 9; // Внутренняя заявка
					break;
				case 'DokUcLpu': default:
					$_POST['DrugDocumentType_id'] = 1; // Документ прихода/расхода медикаментов в ЛПУ
					break;
				case 'AllDok': // Любые документы
					break;
				case 'farm_AllDok': // Любые документы для аптеки
					break;    
			}
			if ($_REQUEST['method'] == 'farm_AllDok')
			    $this->farm_loadDocumentUcView();
			else 
			    $this->loadDocumentUcView();
		}
		else {
			header("Location: /?c=promed");
		}
	}

	/**
	 *  Редактирование
	 */
	function edit() {
		if (!isset($_SESSION['login'])) {
			header("Location: /?c=promed");
		}
		
		if (isset($_REQUEST['method'])) {
			switch ($_REQUEST['method']) {
				case 'DokSpis':
					$_POST['DrugDocumentType_id'] = 2; // Документ списания медикаментов
					break;
				case 'DokOst':
					$_POST['DrugDocumentType_id'] = 3; // Документ ввода остатков
					break;
				case 'DokInv':
					$_POST['DrugDocumentType_id'] = 4; // Инвентаризационная ведомость
					break;
				case 'DokOstKor':
					$_POST['DrugDocumentType_id'] = 5; // Корректировка остатков
					break;
				case 'DokPrRas': default:
					$_POST['DrugDocumentType_id'] = 1; // Документ прихода/расхода медикаментов
					break;
				case 'DokNak': 
					$_POST['DrugDocumentType_id'] = 6; // Накладная
					break;
				case 'DokUcLpu':  
					$_POST['DrugDocumentType_id'] = 1; // Документ прихода/расхода медикаментов в ЛПУ
					break;
				case 'DocZayav':
					$_POST['DrugDocumentType_id'] = 9; // Внутренняя заявка
					break;
			}
			$this->loadDocumentUcEdit();
		}
		else {
			header("Location: /?c=promed");
		}
	}

	/**
	 *  Сохранение
	 */
	function saveObject($method)
	{
		$this->load->model('Farmacy_model', 'dbmodel');
		$this->load->helper('Text');
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules[$method]);
		if (strlen($err) > 0)
		{
			$this->ReturnError($err);
			return;
		}
		$err = $this->getObjectCheck($this->dbmodel, $data, $method);
		if (strlen($err) > 0)
		{
			$this->ReturnError($err);
			return;
		}
		$val = array();
		$this->Cancel_Error_Handle = false;
		$result = $this->choiseMethod($this->dbmodel, $data, $method);
		if (is_array($result) && (count($result) == 1))
		{
			if ($result[0]['Error_Code']>0)
			{
				$result[0]['success'] = false;
				$result[0]['Error_Msg'] = $result[0]['Error_Message'];
			}
			else 
			{
				$result[0]['success'] = true;
			}
			$val = $result[0];
		}
		else
		{
			$val = array('success' => false, 'Error_Code' => 100002, 'Error_Msg' => 'Системная ошибка при выполнении скрипта');
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}

	/**
	 * Сохранение контрагента
	 */
	function saveContragent() {
		//Сессионые параметры цепляю к $data отдельно, что бы избежать путаницы
		$data = $this->ProcessInputData('saveContragent', false);
		if ($data === false) { return false; }

		$sessionParams = getSessionParams();
		$data['session'] = $sessionParams['session'];

		$this->load->model('Farmacy_model', 'dbmodel');

		$err = $this->checkDoubleContragent($this->dbmodel, $data);
		if (strlen($err) > 0) {
			$this->ReturnError($err);
			return false;
		}

		$response = $this->dbmodel->saveContragent($data);
		if (is_array($response) && empty($response[0]['Error_Msg'])) {
			$response[0]['success'] = true;
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	
	/**
	* Поиск рецепта по серии и номеру
	* Используется в окне обработки рецептов
	*/
	function searchEvnRecept() {
		$this->load->model("Farmacy_model", "dbmodel");

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('searchEvnRecept', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->searchEvnRecept($data);

		if ( is_array($response) ) {
			if ( count($response) == 0 ) {
				$val['Error_Msg'] = 'Рецепт не найден';
				$val['success'] = false;
			}
			else if ( count($response) == 1 ) {
				array_walk($response[0], 'ConvertFromWin1251ToUTF8');
				$val = $response[0];
				$val['success'] = true;
			}
			else {
				$val['Error_Msg'] = 'Найдено более одного рецепта';
				$val['success'] = false;
			}
		}
		else {
			$val['Error_Msg'] = 'При поиске рецепта произошла ошибка';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 *  Загрузка данных журнала движения рецептов
	 */
	function loadEvnReceptTrafficBook() {
		$this->load->model("Farmacy_model", "dbmodel");

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnReceptTrafficBook', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnReceptTrafficBook($data);

		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response['data']) ) {
				$val['data'] = array();
				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}

				$val['totalCount'] = $response['totalCount'];
			}
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	 *  Сохранение строки документа
	 */
	function saveDocumentUcStr()
	{
		$this->load->model('Farmacy_model', 'dbmodel');


		$data = $this->ProcessInputData('saveDocumentUcStr', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->saveDocumentUcStr($data);

		/*if ( is_array($response) && (count($response) == 1) )
		{
			if ( strlen($response[0]['Error_Msg']) > 0 )
			{
				$response[0]['success'] = false;
			}
			else
			{
				$response[0]['success'] = true;
			}

			$val = $response[0];
		}
		else
		{
			$val = array('success' => false, 'Error_Code' => 100002, 'Error_Msg' => 'Системная ошибка при выполнении скрипта');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);
		*/
        $this->ProcessModelSave($response, true,'Системная ошибка при выполнении скрипта')->ReturnData();
		return true;
	}

	/**
	 *  Сохранение строки документа
	 */
	function saveDocumentInvUcStr() {

		$this->load->model('Farmacy_model', 'dbmodel');

		$data = $this->ProcessInputData('saveDocumentInvUcStr', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->saveDocumentInvUcStr($data);

		/*if (is_array($response)){
			if ( strlen($response['Error_Msg']) > 0 ) {
				$response['success'] = false;
			} else {
				$response['success'] = true;
			}
			$val = $response;
		} else {
			$val = array('success' => false, 'Error_Code' => 100002, 'Error_Msg' => 'Системная ошибка при выполнении скрипта');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);*/
        $this->ProcessModelSave($response,true,'Системная ошибка при выполнении скрипта')->ReturnData();
		return true;
	}

	/**
	*  Постановка рецепта на отсрочку
	*/
	function putEvnReceptOnDelay() {

		$this->load->model("Farmacy_model", "dbmodel");

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('putEvnReceptOnDelay', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->putEvnReceptOnDelay($data);

		/*if ( is_array($response) && count($response) > 0 ) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');

			if ( strlen($response[0]['Error_Msg']) == 0 ) {
				$response[0]['success'] = true;
			}
			else {
				$response[0]['success'] = false;
			}

			$val = $response[0];
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);*/
        $this->ProcessModelSave($response,true,'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}

	/**
	 *  Печать акта
	 */
	function printDokSpisAkt() {
		$this->load->model('Farmacy_model', 'dbmodel');
		$this->load->library('parser');

		$data = array();
		$default_value = '&nbsp;';
		//$default_value = '<font style="color: red;"><b>N</b></font>';
		
		// Получаем идентификатор документа
		if ( (isset($_GET['DocumentUc_id'])) && (is_numeric($_GET['DocumentUc_id'])) && ($_GET['DocumentUc_id'] >= 0) ) {
			$data['DocumentUc_id'] = $_GET['DocumentUc_id'];
		} else {
			echo 'Не указан идентификатор документа';
			return true;
		}
		$template = 'print_dok_spis';
		
		// Получаем данные
		$response = $this->dbmodel->getDokSpisAktFields($data);

		if ( (!is_array($response)) || (count($response) == 0) ) {
			echo 'Ошибка при получении данных по свидетельствам';
			return true;
		}
		
		$row_num = 0;
		$table_body = '';
		foreach($response['docuc_str_data'] as $row) {
			$table_body .= "
				<tr>
					<td class=\"tleft\">".($row['Drug_Name'] ? ltrim(rtrim($row['Drug_Name'])) : "&nbsp;")."</td>
					<td>".($row['Drug_Code'] ? ltrim(rtrim($row['Drug_Code'])) : "&nbsp;")."</td>
					<td>".($row['Drug_PackName'] ? ltrim(rtrim($row['Drug_PackName'])) : "&nbsp;")."</td>
					<td>&nbsp;</td>
					<td>".($row['DocumentUcStr_Count'] ? round($row['DocumentUcStr_Count'],2) : "&nbsp;")."</td>
					<td>".($row['DocumentUcStr_PriceR'] ? round($row['DocumentUcStr_PriceR'],2) : "&nbsp;")."</td>
					<td>".($row['DocumentUcStr_SumR'] ? round($row['DocumentUcStr_SumR'],2) : "&nbsp;")."</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>";
			$row_num++;
		}
		
		for($i = $row_num; $i < 5; $i++) {
			$table_body .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
		}

		$total_sum_words = '&nbsp;';
		if ($response['DocumentUc_SumR'] && $response['DocumentUc_SumR'] > 0) {
			$m = new money2str();
			$total_sum_words = preg_replace('/ 00 копеек/','',trim($m->work(number_format($response['DocumentUc_SumR'], 2, '.', ''), 2)));
		}
		
		$parse_data = array(
			'table_body' => $table_body,
			'total_sum' => isset($response['DocumentUc_SumR']) ? round($response['DocumentUc_SumR'],2) : $default_value,
			'total_sum_words' => $total_sum_words,
			'Act_Date' => isset($response['Act_Date']) ? $response['Act_Date'] : $default_value,
			'Mol_Name' => isset($response['Mol_Name']) ? rtrim(ltrim($response['Mol_Name'])) : $default_value,
			'Org1' => isset($response['Org1']) ? rtrim(ltrim($response['Org1'])) : $default_value,
			'Org2' => isset($response['Org2']) ? rtrim(ltrim($response['Org2'])) : $default_value
		);

		$this->parser->parse($template, $parse_data);
		
		return true;
	}
	
	/**
	* Формирование списка документов для АРМ-а аптекаря
	*/
	function loadDocumentListByDay() {
		$this->load->model("Farmacy_model", "model");
		$data = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadDocumentListByDay');
		
		if ($data) {
			//print_r($data);
			$response = $this->model->loadDocumentListByDay($data);
			if (is_array($response) && count($response) > 0) {
				$val = array();
				foreach ($response as $row) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
				$this->ReturnData($val);
			} else {
				$this->ReturnData(array());
			}
		}
	}
	
	/**
	 * Исполнение документа.
	 */
	function executeDocumentUc() {
		$this->load->model("Farmacy_model", "model");
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		$data = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('executeDocumentUc');
		
		if ($data) {
			//Установка идентификатора ГК в созданых партиях, если требуется
			if ($data['DocumentUc_id'] > 0) {
				$this->DocumentUc_model->setDrugShipmentSupply($data);
			}

			$response = $this->model->executeDocumentUc($data);
			if (count($response) > 0) {
				if (isset($response['Error_Msg'])) {
					$this->ReturnError( $response['Error_Msg'] );
				} else {
					$this->ReturnData(
						array(
							'success' => true,
							'DocumentUc_id' => $response['DocumentUc_id']
						)
					);
				}
			} else {
				$this->ReturnError();
			}
		}
	}
	
	/**
	 * обеспечение рецепта. (устаревшая версия)
	 * Необходимо удалить, после того как начнет стабильно работать новое обеспечение
	 */
	function old_provideEvnRecept() {
		$this->load->model("Farmacy_model", "f_model");
		$this->load->model("FarmacyDrugOstat_model", "fdo_model");
		$data = array();
		$doc_id = 0;

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('provideEvnRecept');
		//print_r($data); die;
		if ($data) {
			$quantity = $data['quantity'];
			$sum = 0;
			$uc_str_array = array();
		
			//Проверяем есть ли требуемые медикаменты на складе, если да получаем набор партий
			$response = $this->fdo_model->loadDrugOstatByFilters(array('Contragent_id' => $data['Contragent_id'], 'Drug_id' => $data['Drug_id']));
			if (isset($response['data'])) {
				//Собираем необходимый набор партий
				foreach($response['data'] as $uc_str) {
					if($sum < $quantity) {
						$sum += $uc_str['ostat'];
						$uc_str_array[] = $uc_str;
						//Не позволяем брать больше чем нужно
						if ($sum > $quantity)
							$uc_str_array[count($uc_str_array)-1]['ostat'] -= $sum - $quantity;
					}
				}
			}
			
			if ($sum < $quantity) {
				$this->ReturnError('Для обеспечения рецепта недостаточно медикаментов');
				return;
			}
			
			//TODO: Создаем документ учета
			$now = getdate();
			$save_data = array(
				'DocumentUc_Num' => null,
				'DocumentUc_setDate' => $now['year'].'-'.$now['mon'].'-'.$now['mday'],
				'DocumentUc_didDate' => $now['year'].'-'.$now['mon'].'-'.$now['mday'],
				'DocumentUc_DogNum' => null,
				'DocumentUc_DogDate' => null,
				'Lpu_id' => null,//$data['Lpu_id'],
				'Contragent_id' => $data['Contragent_id'],
				'Contragent_sid' => $data['Contragent_id'],
				'Mol_sid' => null,
				'Contragent_tid' => 1, //пациент
				'Mol_tid' => null,
				'DrugDocumentType_id' => 1, //док. прихода/расхода
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->f_model->saveDocumentUc($save_data);
			if (is_array($result) && (count($result) == 1)) {
				if ($result[0]['Error_Code']>0) {
					$this->ReturnError($result[0]['Error_Message']);
					return;
				} else {
					if (isset($result[0]['DocumentUc_id']) && $result[0]['DocumentUc_id'] > 0)
						$doc_id = $result[0]['DocumentUc_id'];
				}
			}
			
			if ($doc_id > 0) {
				//Персчитываем и сохраняем партиии в созданный документ
				foreach($uc_str_array as $uc_str) {
					$this->f_model->displacementDrugs(array(
						'EvnRecept_id' => $data['EvnRecept_id'],
						'pmUser_id' => $data['pmUser_id'],
						'DocumentUc_id' => $doc_id,
						'DocumentUcStr_id' => $uc_str['DocumentUcStr_id'],
						'quantity' => $uc_str['ostat']
					));
				}
				$this->f_model->EvnReceptSetDelayType($data, 1);
				//Возвращаем ид нового документа				
				$this->ReturnData(
					array(
						'success' => true,
						'DocumentUc_id' => $doc_id
					)
				);
			}
		}
	}

	/**
	 * Обеспечение рецепта.
	 */
	function provideEvnRecept() {
		$this->load->model("Farmacy_model", "f_model");
		//$this->load->model("ufa/Ufa_Farmacy_model", "f_model");
		$data = $this->ProcessInputData('provideEvnRecept', true);
		//print_r($data); die;
		if ($data) {
			if (!empty($data['EvnReceptGeneral_id']) && $data['EvnReceptGeneral_id'] > 0) {
				$response = $this->f_model->provideEvnReceptGeneral($data);
			} else { 
				$response = $this->f_model->provideEvnRecept($data);
			}
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  Загрузка списка документов, на основе которых контрагент является действующим
	 */
	function loadContragentDocumentsList() {
		$this->load->model("Farmacy_model", "f_model");
		$data = $this->ProcessInputData('loadContragentDocumentsList', true);
		if ($data) {
			$filter = $data;
			$response = $this->f_model->loadContragentDocumentsList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Импорт документов учета.
	 */
	function importDocumentUc() {
		$this->load->model("Farmacy_model", "f_model");
		$data = $this->ProcessInputData('importDocumentUc', true);
		$doctype_id  = $data['DrugDocumentType_id'];
		$import_method = null;

		$root_dir = IMPORTPATH_ROOT;
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				return $this->ReturnError('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}

		if( !isset($_FILES['uploadfilefield']) ) {
			return $this->ReturnError('Ошибка! Отсутствует файл! (поле uploadfilefield)');
		}

		$file = $_FILES['uploadfilefield'];
		if( $file['error'] > 0 ) {
			return $this->ReturnError('Ошибка при загрузке файла!', $file['error']);
		}

		//вычисляем расширение из названия файла
		$ext = explode('.', $file['name']);
		if (count($ext) > 0) {
			$ext = strtolower($ext[count($ext)-1]);
		} else {
			$ext = null;
		}

		//проверяем расширение
		switch ($doctype_id) {
			case 6:
				if( $ext != 'xls' ) {
					return $this->ReturnError('Необходим файл с расширением xls.');
				}
				break;
		}

		//выбираем метод для импорта
		switch ($doctype_id) {
			case 6:
				$import_method = 'importDokNak';
				break;
			case 16:
				$import_method = 'importDokNak';
				break;
		}


		$fileFullName = $root_dir.$file['name'];
		if( is_file($file['tmp_name']) ) {
			$fileFullName = $root_dir.time().'_'.$file['name'];
		}

		if( !rename($file['tmp_name'], $fileFullName) ) {
			return $this->ReturnError('Не удалось создать файл ' . $fileFullName);
		}
		$data['FileFullName'] = $fileFullName;

		if ($data && !empty($import_method)){
			$response = $this->f_model->$import_method($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			unlink($fileFullName);
			return true;
		} else {
			unlink($fileFullName);
			return false;
		}
	}

	/**
	 * Импорт приходной накладной из dbf
	 */
	function importDokNak() {
		$this->load->model("Farmacy_model", "f_model");
		$this->load->model("DocumentUc_model", "du_model");
		$data = $this->ProcessInputData('importDokNak', true);
		if (!$data) {
			return false;
		}

		$root_dir = IMPORTPATH_ROOT;
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				return $this->ReturnError('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}

		if( !isset($_FILES['uploadfilefield']) ) {
			return $this->ReturnError('Ошибка! Отсутствует файл! (поле uploadfilefield)');
		}

		$file = $_FILES['uploadfilefield'];
		if( $file['error'] > 0 ) {
			return $this->ReturnError('Ошибка при загрузке файла!', $file['error']);
		}

		//вычисляем расширение из названия файла
		$ext = explode('.', $file['name']);
		if (count($ext) > 0) {
			$ext = strtolower($ext[count($ext)-1]);
		} else {
			$ext = null;
		}

		if (!in_array($ext, array('dbf', 'sst', 'txt'))) {
			return $this->ReturnError('Данный тип файла не разрешен.');
		}

		//выбираем метод для импорта
		if ($ext == 'dbf') {
			$import_model = $this->f_model;
            $import_method = 'importDokNakFromDbf';
		}
        if ($ext == 'sst' || $ext == 'txt') {
            $import_model = $this->du_model;
			$import_method = 'importDokNakFromSst';
		}

		$fileFullName = $root_dir.$file['name'];
		if( is_file($file['tmp_name']) ) {
			$fileFullName = $root_dir.time().'_'.$file['name'];
		}

		if( !rename($file['tmp_name'], $fileFullName) ) {
			return $this->ReturnError('Не удалось создать файл ' . $fileFullName);
		}
		$data['FileFullName'] = $fileFullName;

		if ($data && !empty($import_method)){
			$response = $import_model->$import_method($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			unlink($fileFullName);
			return true;
		} else {
			unlink($fileFullName);
			return false;
		}
	}

	/**
	 * Получение списка остатков для обеспечения рецепта
	 */
	function getDrugOstatForProvide() {
		$this->load->model("Farmacy_model", "dbmodel");
		//$this->load->model("ufa/Ufa_Farmacy_model", "dbmodel");
		$data = $this->ProcessInputData('getDrugOstatForProvide', true);
		if ($data) {
			$response = $this->dbmodel->getDrugOstatForProvide($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}

		return true;
	}
    /**
     * Получение списка остатков для обеспечения рецепта
     */
    function getDrugOstatForProvideFromBarcode() {
        $this->load->model("Farmacy_model", "dbmodel");

        $data = $this->ProcessInputData('getDrugOstatForProvideFromBarcode', true);

        if ($data)
        {
            $response = $this->dbmodel->getDrugOstatForProvideFromBarcode($data);
            $this->ProcessModelList($response,true,true)->ReturnData();
        }

        return true;
    }

	/**
	 * Получение данных контрагента по отделению ЛПУ
	 */
	function getLpuSectionContragent() {
		$this->load->model("Farmacy_model", "dbmodel");

		$data = $this->ProcessInputData('getLpuSectionContragent', true);
		if ($data) {
			$response = $this->dbmodel->getLpuSectionContragent($data);
			$this->ProcessModelSave($response,true,true)->ReturnData();
		}

		return true;
	}

	/**
	 * Создание документа учета для списания реактивов.
	 */
	function createDocumentForReagentConsumption() {
		$this->load->model("Farmacy_model", "dbmodel");

		$data = $this->ProcessInputData('createDocumentForReagentConsumption', true);
		if ($data) {
			$response = $this->dbmodel->createDocumentForReagentConsumption($data);
			$this->ProcessModelSave($response,true,true)->ReturnData();
		}

		return true;
	}

	/**
	 * Получение конфига для меню изменения статуса внутренней заявки
	 */
	function getAllowedDocZayavStatusConfig() {
		$_POST['DrugDocumentType_id'] = 9;

		$response = $this->getAllowedDrugDocumentStatusConfig();
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}

	/**
	 * Получение конфига для меню изменения статуса документа
	 */
	function getAllowedDrugDocumentStatusConfig() {
		$data = $this->ProcessInputData('getAllowedDrugDocumentStatusConfig', true);
		if ( $data === false ) { return false; }

		$this->load->model("Farmacy_model", "dbmodel");
		return $this->dbmodel->getAllowedDrugDocumentStatusConfig($data);
	}

	/**
	 * Изменение статуса документа
	 */
	function setDocumentUcStatus() {
		$data = $this->ProcessInputData('setDocumentUcStatus', true);
		if ( $data === false ) { return false; }

		$this->load->model("Farmacy_model", "dbmodel");
		$this->dbmodel->beginTransaction();

		$response = $this->dbmodel->setDocumentUcStatus($data);
		if (empty($response) || empty($response[0]) || !empty($response[0]['Error_Message'])) {
			$this->dbmodel->rollbackTransaction();
			if (!empty($response[0]['Error_Message'])) {
				return $response;
			}
			return false;
		}

		$StatusHistory = $this->dbmodel->saveDrugDocumentStatusHistory($data);
		if (empty($StatusHistory) || empty($StatusHistory[0]) || !empty($StatusHistory[0]['Error_Message'])) {
			$this->dbmodel->rollbackTransaction();
			if (!empty($StatusHistory[0]['Error_Message'])) {
				return $StatusHistory;
			}
			return false;
		}

		$this->ProcessModelSave($response,true,true)->ReturnData();

		$this->dbmodel->commitTransaction();

		return true;
	}

	/**
	 * Получение истории изменения статуса документа
	 */
	function loadDrugDocumentStatusHistoryGrid() {
		$data = $this->ProcessInputData('loadDrugDocumentStatusHistoryGrid',true,true);
		if ($data === false) {return false;}

		$this->load->model("Farmacy_model", "dbmodel");

		$response = $this->dbmodel->loadDrugDocumentStatusHistoryGrid($data);
		$this->ProcessModelMultiList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Создание спецификации для документа учета медикаментов на основе ГК.
	 */
	function createDocumentUcStrListByWhsDocumentSupply() {
		$this->load->model("Farmacy_model", "model");
		$data = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('createDocumentUcStrListByWhsDocumentSupply');

		if ($data) {
			$response = $this->model->createDocumentUcStrListByWhsDocumentSupply($data);
			if (count($response) > 0) {
				if (isset($response['Error_Msg'])) {
					$this->ReturnError( $response['Error_Msg'] );
				} else {
					$this->ReturnData(
						array(
							'success' => true,
							'DocumentUc_id' => $response['DocumentUc_id']
						)
					);
				}
			} else {
				$this->ReturnError();
			}
		}
	}

	/**
	 * Загрузка списка для окна выбора ГК (используется в форме редактирования документов учета)
	 */
	function loadWhsDocumentSupplyList() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadWhsDocumentSupplyList', true);
		if ($data) {
			$filter = $data;
			$response = $this->Farmacy_model->loadWhsDocumentSupplyList($filter);

			if (!empty($data['limit'])) {
				$this->ProcessModelMultiList($response,true,true)->ReturnData();
			} else {
				$this->ProcessModelList($response, true, true)->ReturnData();
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Копирование строки документа. Возвращет id новой строки.
	 */
	function copyDocumentUcStr() {
		$this->load->model('Farmacy_model', 'dbmodel');

		$data = $this->ProcessInputData('copyDocumentUcStr', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->copyDocumentUcStr($data);
		$this->ProcessModelSave($response, true,'Системная ошибка при выполнении скрипта')->ReturnData();
		return true;
	}

	/**
	 *  Получение идентификатора организации соответствующей Минздраву.
	 */
	function getMinzdravDloOrgId() {
		$this->load->model('Farmacy_model', 'dbmodel');
		$response = $this->dbmodel->getMinzdravDloOrgId();
		$this->ProcessModelSave($response,true,true)->ReturnData();
		return true;
	}


    /**
     * Получение данных о медикаментых на основе назначений
     */
    function loadDocumentUcStrListByEvnCourseTreatDrug() {
        $this->load->model("Farmacy_model", "Farmacy_model");
        $data = $this->ProcessInputData('loadDocumentUcStrListByEvnCourseTreatDrug', true);
        if ($data) {
            $response = $this->Farmacy_model->loadDocumentUcStrListByEvnCourseTreatDrug($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
	 * Загрузка списка для вкладки заявки (используется в АРМ товароведа)
	 */
	function loadWhsDocumentRequestList() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadWhsDocumentRequestList', true);
		if ($data) {
			$filter = $data;
			$response = $this->Farmacy_model->loadWhsDocumentRequestList($filter);

			if (!empty($data['limit'])) {
				$this->ProcessModelMultiList($response,true,true)->ReturnData();
			} else {
				$this->ProcessModelList($response, true, true)->ReturnData();
			}
			return true;
		} else {
			return false;
		}
	}

    /**
	 * Загрузка списка источников финансирования
	 */
	function loadDrugFinanceGrid() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadDrugFinanceGrid', true);
		if ($data) {
			$response = $this->Farmacy_model->loadDrugFinanceGrid($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка источников финансировния
	 */
	function loadDrugFinanceList() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadDrugFinanceList', true);
		if ($data) {
			$response = $this->Farmacy_model->loadDrugFinanceList($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактировния источника финансировния
	 */
	function loadDrugFinanceForm() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadDrugFinanceForm', true);
		if ($data) {
			$response = $this->Farmacy_model->loadDrugFinanceForm($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение источника финансировния
	 */
	function saveDrugFinance() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('saveDrugFinance', true);
		if ($data) {
			$response = $this->Farmacy_model->saveDrugFinance($data);

			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
	 * Получение списка целевых статей
	 */
	function loadBudgetFormTypeGrid() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadBudgetFormTypeGrid', true);
		if ($data) {
			$response = $this->Farmacy_model->loadBudgetFormTypeGrid($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка целевых статей
	 */
	function loadBudgetFormTypeList() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadBudgetFormTypeList', true);
		if ($data) {
			$response = $this->Farmacy_model->loadBudgetFormTypeList($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для редактирования целевой статьи
	 */
	function loadBudgetFormTypeForm() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadBudgetFormTypeForm', true);
		if ($data) {
			$response = $this->Farmacy_model->loadBudgetFormTypeForm($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Полученние кода целевой статьи
	 */
	function generateBudgetFormTypeCode() {
		$data = $this->ProcessInputData('generateBudgetFormTypeCode', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->generateBudgetFormTypeCode($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение целевой статьи
	 */
	function saveBudgetFormType() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('saveBudgetFormType', true);
		if ($data) {
			$response = $this->Farmacy_model->saveBudgetFormType($data);

			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
	 * Получение списка статьи расхода
	 */
	function loadWhsDocumentCostItemTypeGrid() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadWhsDocumentCostItemTypeGrid', true);
		if ($data) {
			$response = $this->Farmacy_model->loadWhsDocumentCostItemTypeGrid($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка статьи расхода
	 */
	function loadWhsDocumentCostItemTypeList() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadWhsDocumentCostItemTypeList', true);
		if ($data) {
			$response = $this->Farmacy_model->loadWhsDocumentCostItemTypeList($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для редактирования статьи расхода
	 */
	function loadWhsDocumentCostItemTypeForm() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadWhsDocumentCostItemTypeForm', true);
		if ($data) {
			$response = $this->Farmacy_model->loadWhsDocumentCostItemTypeForm($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение кода статьи расхода
	 */
	function generateWhsDocumentCostItemTypeCode() {
		$data = $this->ProcessInputData('generateWhsDocumentCostItemTypeCode', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->generateWhsDocumentCostItemTypeCode($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение статьи расхода
	 */
	function saveWhsDocumentCostItemType() {
		$data = $this->ProcessInputData('saveWhsDocumentCostItemType', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveWhsDocumentCostItemType($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка финансирований контрактов
	 */
	function loadFinanceSourceGrid() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadFinanceSourceGrid', true);
		if ($data) {
			$response = $this->Farmacy_model->loadFinanceSourceGrid($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для редактирования финансирования контрактов
	 */
	function loadFinanceSourceForm() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$data = $this->ProcessInputData('loadFinanceSourceForm', true);
		if ($data) {
			$response = $this->Farmacy_model->loadFinanceSourceForm($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение кода финансирования контракта
	 */
	function generateFinanceSourceCode() {
		$data = $this->ProcessInputData('generateFinanceSourceCode', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->generateFinanceSourceCode($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение финансирования контракта
	 */
	function saveFinanceSource() {
		$data = $this->ProcessInputData('saveFinanceSource', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveFinanceSource($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка медикаментов из регистра остатков
	 */
	function loadDrugOstatRegistryGrid() {
		$data = $this->ProcessInputData('loadDrugOstatRegistryGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugOstatRegistryGrid($data);
        if (!empty($data['limit'])) {
            $this->ProcessModelMultiList($response, true, true)->ReturnData();
        } else {
            $this->ProcessModelList($response, true, true)->ReturnData();
        }

		return true;
	}

	/**
	 * Получение количества препарата в упаковке
	 */
	function getGoodsPackCount() {
		$data = $this->ProcessInputData('getGoodsPackCount', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getGoodsPackCount($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение единиц измерения
	 */
	function checkGoodsPackCount() {
		$data = $this->ProcessInputData('checkGoodsPackCount', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkGoodsPackCount($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Cоздание записи  оповещения для осроченных рецептов
	*/
	function saveReceptNotification() {//echo('Step1');
	    $session_data = getSessionParams();
	    $data = $this->ProcessInputData('saveReceptNotification', true);
		if ($data === false) { return false; }
		$data['pmUser_id'] = $session_data['pmUser_id'];
		$response = $this->dbmodel->saveReceptNotification($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		    return true;

	    
	}
}