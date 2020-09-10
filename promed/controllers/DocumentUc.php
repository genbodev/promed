<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для документов учета медикаметнов
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version		07.2014
* @property 	WhsDocumentTitle_model WhsDocumentTitle_model
* @property 	DocumentUc_model DocumentUc_model
*/

class DocumentUc extends swController {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();

		$this->load->database();
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		//$this->load->model("ufa/Ufa_DocumentUc_model", "DocumentUc_model");
		
		$this->inputRules = array(
			'load' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => 'required', 'type' => 'id')
			),
			'loadList' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'save' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_pid', 'label' => 'Идентификатор родительского документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DocumentUc_setDate', 'label' => 'Дата подписания', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'DocumentUc_didDate', 'label' => 'Дата поставки', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'DocumentUc_InvoiceNum', 'label' => 'Номер счета-фактуры', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_InvoiceDate', 'label' => 'Дата счета-фактуры', 'rules' => '', 'type' => 'date'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Договор', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUc_DogDate', 'label' => 'Дата договора', 'rules' => '', 'type' => 'date'),
				array('field' => 'DocumentUc_DogNum', 'label' => 'Номер договора', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_Sum', 'label' => 'Сумма', 'rules' => '', 'type' => 'float'),
				array('field' => 'Contragent_sid', 'label' => 'Поставщик', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Контрагент пользователя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_sid', 'label' => 'Склад поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_sid', 'label' => 'Материально-ответственное лицо поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_tid', 'label' => 'Потребитель', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_tid', 'label' => 'Склад потребителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mol_tid', 'label' => 'Материально-ответственное лицо потребителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentType_Code', 'label' => 'Код типа документа', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUcStrJSON', 'label' => 'Строки документа учета', 'rules' => '', 'type' => 'string'),
				array('field' => 'Note_id', 'label' => 'Идентификатор примечания', 'rules' => '', 'type' => 'string'),
				array('field' => 'Note_Text', 'label' => 'Текст примечания', 'rules' => '', 'type' => 'string'),
				array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => '', 'type' => 'id', 'default' => null),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'AccountType_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'StorageZone_sid', 'label' => 'Зона хранения поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'StorageZone_tid', 'label' => 'Зона хранения получателя', 'rules' => '', 'type' => 'id')
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'farm_delete' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'canceling' => array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadStorageList'=> array(
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_sid', 'label' => 'Идентификатор склада-поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'StorageType_id', 'label' => 'Тип склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_oid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'OrgStruct_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MolMedPersonal_id', 'label' => 'Идентификатор врача (МОЛ)', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPrescrTreatDrug_id', 'label' => 'Идентификатор назначения медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'StorageForAptMuFirst', 'label' => 'Перым вывести склад аптеки МО', 'rules' => '', 'type' => 'checkbox'),
				array('field' => 'filterByOrgUser', 'label' => 'Фильтвать по пользователю организации', 'rules' => '', 'type' => 'checkbox'),
				array('field' => 'StructFilterPreset', 'label' => 'Готовый пресет фильтров (структура)', 'rules' => '', 'type' => 'string'),
				array('field' => 'date', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
				array('field' => 'query', 'label' => 'Наименование склада', 'rules' => '', 'type' => 'string')
			),
			'loadDocumentUcStrList' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => 'required', 'type' => 'id')
			),
			'farm_loadDocumentUcStrList' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => 'required', 'type' => 'id')
			),
			'getDocumentUcStrListByWhsDocumentSupply'=> array(
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор контракта', 'rules' => 'required', 'type' => 'id')
			),
			'getDocumentUcStrListByWhsDocumentUcOrder'=> array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id')
			),
			'getDocumentUcStrDataByBarCode'=> array(
				array('field' => 'DrugPackageBarCode_BarCode', 'label' => 'Штрих-код', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Storage_id', 'label' => 'Склад', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'mode', 'label' => 'Режим работы функции', 'rules' => 'required', 'type' => 'string')
			),
			'loadDrugComboForDocumentUcStr' => array(
				array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugNomen_Code', 'label' => 'Код медикамента', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор госконтракта', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'StorageZone_id', 'label' => 'Идентификатор места хранения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugShipment_setDT_max', 'label' => 'Максимальная дата партии', 'rules' => '', 'type' => 'date'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int'),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int')
			), 
			'farm_loadDrugComboForDocumentUcStr' => array(
				array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugNomen_Code', 'label' => 'Код медикамента', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор госконтракта', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор сколада', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int'),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int')
			),
			'getPrepSeriesByDrugAndSeries' => array(
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'PrepSeries_Ser', 'label' => 'Серия', 'rules' => 'required', 'type' => 'string')
			),
			'getMolByContragentOrStorage' => array(
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'int'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'int'),
				array('field' => 'isSmpMainStorage', 'label' => 'Флаг центрального склада СМП', 'rules' => '', 'type' => 'int'),
				array('field' => 'Date', 'label' => 'Дата', 'rules' => '', 'type' => 'date')
			),
			'loadContragentList'=> array(
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'ContragentType_CodeList', 'label' => 'Список кодов типа контрагента', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ExpDate', 'label' => 'Дата актуальности', 'rules' => '', 'type' => 'date'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id' ),
				array('field' => 'mode', 'label' => 'Режим работы', 'rules' => '', 'type' => 'string' )
			),
			'loadDocumentUcStrOidCombo' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор партии', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'StorageZone_id', 'label' => 'Идентификатор места хранения', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор госконтракта', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
                array('field' => 'PrepSeries_IsDefect', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugShipment_setDT_max', 'label' => 'Максимальная дата партии', 'rules' => '', 'type' => 'date'),
                array('field' => 'CheckGodnDate', 'label' => 'Проверка срока годности', 'rules' => '', 'type' => 'string'),
                array('field' => 'Sort_Type', 'label' => 'Тип сортировки', 'rules' => '', 'type' => 'string'),
                array('field' => 'isEdOstEnabled', 'label' => 'Флаг отображения остатка в ед измерения', 'rules' => '','type' => 'string')
			),
			'farm_loadDocumentUcStrOidCombo' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор партии', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор госконтракта', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id')
			),
			'generateDocumentUcNum' => array(
				array('field' => 'DrugDocumentType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugDocumentType_Code', 'label' => 'Тип документа', 'rules' => '', 'type' => 'string'),
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id')
			),
			'executeDocumentUc'=> array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id')
			),
			'farm_executeDocumentUc'=> array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id')
			),
			'checkDrugShipmentName'=> array(
				array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор строки документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugShipment_Name', 'label' => 'Наименование партии', 'rules' => 'required', 'type' => 'string')
			),
			'getReservedDrugOstatForDocumentUcStr'=> array(
				array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор строки документа', 'rules' => '', 'type' => 'id')
			),
			'getDocNakData' => array(
				array('field' => 'DocumentUc_Num', 'label' => 'Номер накладной', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DocumentUc_setDate', 'label' => 'Дата накладной', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id'),
				array('field' => 'List', 'label' => 'Получить список', 'rules' => '', 'type' => 'int')
			),
			'loadWhsDocumentSpecificityList' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
                array('field' => 'WhsDocumentType_Code', 'label' => 'Код типа', 'rules' => '', 'type' => 'string'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
                array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int'),
                array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int')
			),
			'loadWhsDocumentUcOrderList' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
                array('field' => 'WhsDocumentType_Code', 'label' => 'Код типа', 'rules' => '', 'type' => 'string'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
                array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int'),
                array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int')
			),
			'loadDrugPackageBarCodeList' => array(
				array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id')
			),
			'loadStorageZoneCombo' => array(
				array('field' => 'StorageZone_id', 'label' => 'Идентификатор места хранения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_sid', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id')
			),
			'loadStorageZoneByDrugIdCombo' => array(
				array('field' => 'StorageZone_id', 'label' => 'Идентификатор места хранения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
                array('field' => 'isPKU', 'label' => 'Флаг ПКУ', 'rules' => '','type' => 'string'),
                array('field' => 'DrugShipment_id', 'label' => 'Идентификатор партии', 'rules' => '', 'type' => 'id'),
                array('field' => 'isCountEnabled', 'label' => 'Флаг отображения количества', 'rules' => '','type' => 'string'),
                array('field' => 'isEdOstEnabled', 'label' => 'Флаг отображения остатка в ед измерения', 'rules' => '','type' => 'string')
			),
            'getDocumentUcStrListByDrugOstatRegistry' => array(
                array('field' => 'DrugOstatRegistryJSON', 'label' => 'Список записей регистра остатков', 'rules' => 'required', 'type' => 'json_array'),
            ),
            'loadLpuCombo' => array(
                array('field' => 'Lpu_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
            ),
            'loadLpuBuildingCombo' => array(
                array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
                array('field' => 'Lpu_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
            ),
            'loadGoodsUnitCombo' => array(
                array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
                array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
                //array('field' => 'Contragent_sid', 'label' => 'Идентификатор контрагента поставщика', 'rules' => '', 'type' => 'id'),
                array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'string'),
                array('field' => 'UserOrg_Type', 'label' => 'Тип организации пользователя', 'rules' => '', 'type' => 'string'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
            ),
			'loadLpuSectionMerchCombo'=> array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_Storage_id', 'label' => 'Идентификатор склада службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование склада', 'rules' => '', 'type' => 'string')
			),
			'loadStorageMerchCombo'=> array(
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'string'),
				array('field' => 'UserOrg_Type', 'label' => 'Тип организации пользователя', 'rules' => '', 'type' => 'string'),
				array('field' => 'Field_Name', 'label' => 'Имя комбобокса', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_Storage_id', 'label' => 'Идентификатор склада службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование склада', 'rules' => '', 'type' => 'string')
			),
			'loadMolMerchCombo'=> array(
				array('field' => 'Mol_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_Storage_id', 'label' => 'Идентификатор склада службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование склада', 'rules' => '', 'type' => 'string')
			),
            'createDocumentUcStorageWork' => array(
                array('field' => 'DocumentUc_id', 'label' => 'Документ учета', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DocumentUcTypeWork_id', 'label' => 'Вид работы', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Person_cid', 'label' => 'Заказчик', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Post_cid', 'label' => 'Должность заказчика', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Person_eid', 'label' => 'Заказчик', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Post_eid', 'label' => 'Должность заказчика', 'rules' => 'required', 'type' => 'id')
            ),
            'clearDocumentUcStorageWork' => array(
                array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id')
            ),
			'checkPrepSeries' => array(
				array(
					'field'	=> 'DocumentUcStr_Ser',
					'label'	=> 'DocumentUcStr_Ser',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'removeReserve' => array(
				array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор строки документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'debug', 'label' => 'Флаг режима отладки', 'rules' => '', 'type' => 'int')
			)
		);
	}

	/**
	 * Загрузка данных
	 */
	function load() {
		$data = $this->ProcessInputData('load', false);
		if ($data){
			$response = $this->DocumentUc_model->load($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', false);
		if ($data){
			$response = $this->DocumentUc_model->loadList($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Вспомогательная функция для определения является ли тип документа учета расходным
     */
    function isExpendableDrugDocumentType($data) {
        $is_exp = false;

        $code_array = array(
            '2',  // Документ списания медикаментов
            '10', // Расходная накладная
            '11', // Реализация
            '14', // Ордер на Резерв
            '15', // Накладная на внутреннее перемещение
            '17', // Возвратная накладная (расходная)
            '21', // Списание медикаментов со склада на пациента
            '23', // Списание в производство
            '25', // Списание медикаментов со склада на пациента. СМП
            '26', // Списание медикаментов из укладки на пациента
            '27', // Передача на подотчет
            '29', // Передача укладки
            '31',  // Накладная на перемещение внутри склада
            '33'  // Возврат из отделения
        );

        $id_array = array(
            '2',  // Документ списания медикаментов
            '10', // Расходная накладная
            '11', // Реализация
            '14', // Ордер на Резерв
            '15', // Накладная на внутреннее перемещение
            '17', // Возвратная накладная (расходная)
            '22', // Списание медикаментов со склада на пациента
            '24', // Списание в производство
            '25', // Списание медикаментов со склада на пациента. СМП
            '26', // Списание медикаментов из укладки на пациента
            '27', // Передача на подотчет
            '29', // Передача укладки
            '31',  // Накладная на перемещение внутри склада
            '33'  // Возврат из отделения
        );

        if (!empty($data['DrugDocumentType_Code']) && in_array($data['DrugDocumentType_Code'], $code_array)) {
            $is_exp = true;
        }

        if (!empty($data['DrugDocumentType_id']) && in_array($data['DrugDocumentType_id'], $id_array)) {
            $is_exp = true;
        }

        return $is_exp;
    }

	/**
	 * Сохранение
	 */
	function save() {
		$error = array();
		$session_data = getSessionParams();
		$data = $this->ProcessInputData('save', false);
        $lpu_id = null;

        if (!$data) {
            return false;
        }
		$data['pmUser_id'] = $session_data['pmUser_id'];

		if ($data['DrugDocumentType_id'] <= 0 && !empty($data['DrugDocumentType_Code'])) {
			$data['DrugDocumentType_id'] = $this->DocumentUc_model->getObjectIdByCode('DrugDocumentType', $data['DrugDocumentType_Code']);
		}

		//при добавлении докумена учета фиксруем DrugDocumentStatus_id, Contragent_id, Lpu_id, SubAccountType_sid и SubAccountType_tid
		if (empty($data['DocumentUc_id'])) {
            $data['DrugDocumentStatus_id'] = $this->DocumentUc_model->getObjectIdByCode('DrugDocumentStatus', 1); //1 - Новый

			if (!isset($data['Contragent_id']) && $session_data['Contragent_id'] > 0) {
				$data['Contragent_id'] = $session_data['Contragent_id'];
			}
			// Для Уфы фиксируем Org_id
			if ($data['DrugDocumentType_id'] == 17 && 'ufa' == $session_data['session']['region']['nick'] ) {
			    if (!isset($data['Org_id']) && $session_data["session"]['org_id'] > 0) {
				    $data['Org_id'] = $session_data["session"]['org_id'];
			    }
			}
			if (!empty($data['Lpu_id'])) {
                $lpu_id = $data['Lpu_id'];
            } else if ($session_data['Lpu_id'] > 0) {
				$data['Lpu_id'] = $session_data['Lpu_id'];
			}
			if (isset($data['Contragent_sid']) && $data['Contragent_sid'] > 0 && !isset($data['SubAccountType_sid'])) {
				$data['SubAccountType_sid'] = $this->DocumentUc_model->getObjectIdByCode('SubAccountType', 1); //1 - Доступно
			}
			if (isset($data['Contragent_tid']) && $data['Contragent_tid'] > 0 && !isset($data['SubAccountType_tid'])) {
				$data['SubAccountType_tid'] = $this->DocumentUc_model->getObjectIdByCode('SubAccountType', 1); //1 - Доступно
			}
			if (empty($data['Contragent_id'])) {
				$this->ReturnError('Отсутвует контрагент текущей организации пользователя');
				return false;
			}
		} else {
            if (empty($data['Contragent_id'])) { //чтобы контрагент не слетал при редактировании документа учета, если его идентификатор не передан извне
                unset($data['Contragent_id']);
            }
        }
		$session_data = getSessionParams();
		$orgtype = $session_data['session']['orgtype'];
		$region =  $session_data['session']['region']['nick'];

		//var_Dump($region);
		if ($data){
            //старт транзакции
            $this->DocumentUc_model->beginTransaction();

            //проверка статуса
			$status_code = $this->DocumentUc_model->getDrugDocumentStatusCode($data['DocumentUc_id']);
            if (!empty($data['DocumentUc_id']) && in_array($status_code, ['12', '4'])) {  //4 - Исполнен; 12 - На исполнении
                $error[] = "Документ ".($status_code == '4' ? "уже исполнен" : "занят").", сохранение невозможно.";
            }

            //проверка уникальности номера (для всех новых документов, кроме приходных накладных)
            if (count($error) == 0 && empty($data['DocumentUc_id']) && $data['DrugDocumentType_id'] != $this->DocumentUc_model->getObjectIdByCode('DrugDocumentType', 6)) { //6 - Приходная накладная
            	//если номер пуст, его нужно сгенерировать
				if (empty($data['DocumentUc_Num'])) {
					$res = $this->DocumentUc_model->generateDocumentUcNum(array(
						'DrugDocumentType_id' => $data['DrugDocumentType_id'],
						'DrugDocumentType_Code' => $data['DrugDocumentType_Code'],
						'Contragent_id' => $session_data['Contragent_id'],
						'disable_limits' => true
					));
					if ($res && !empty($res[0]['DocumentUc_Num'])) {
						$num = $res[0]['DocumentUc_Num'];
					}
				} else {
					$num = $data['DocumentUc_Num'];
				}

				//5 попыток обеспечить уникальность
				$forbidden_num_list = array();
				$is_unique = false;
				for($i = 0; $i <= 5; $i++) {
					if (!$this->DocumentUc_model->checkDocumentUcNumUnique($num)) {
						$forbidden_num_list[] = $num;
						$res = $this->DocumentUc_model->generateDocumentUcNum(array(
							'DrugDocumentType_id' => $data['DrugDocumentType_id'],
							'DrugDocumentType_Code' => $data['DrugDocumentType_Code'],
							'Contragent_id' => $session_data['Contragent_id'],
							'disable_limits' => true,
							'forbidden_num_list' => $forbidden_num_list
						));
						if ($res && !empty($res[0]['DocumentUc_Num'])) {
							$num = $res[0]['DocumentUc_Num'];
						}
					} else {
						$is_unique = true;
						break;
					}
				}
				if ($is_unique) {
					$data['DocumentUc_Num'] = $num;
				} else {
					$error[] = "Не удалось сгенерировать уникальный номер документа";
				}
			}

            if (count($error) == 0) {
                if ($region == 'ufa' && $orgtype == 'farm') {
                    //  Если это уфимская аптека
                    $response = $this->DocumentUc_model->farm_saveObject('DocumentUc', $data);
                } else {
                    $response = $this->DocumentUc_model->saveObject('DocumentUc', $data);
                }
                $this->ProcessModelSave($response, true, 'Ошибка при сохранении документа учета');

                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }

			if (count($error) == 0 && !empty($this->OutData['DocumentUc_id'])) {
                //для документов разукомплектации сохраняем изменения в сопутствующий документ
                if ($data['DrugDocumentType_id'] == 34) { //34 - Разукомплектация: списание
                    $save_data = $data;
                    $save_data['DocumentUc_id'] = $this->DocumentUc_model->getDocRazPostId($this->OutData['DocumentUc_id']);
                    $save_data['DocumentUc_pid'] = $this->OutData['DocumentUc_id'];
                    $save_data['DrugDocumentType_id'] = 35; // 35 - Разукомплектация: постановка на учет
                    $save_data['Contragent_tid'] = $save_data['Contragent_sid']; //меняем местами получателя и поставщика для дочернего документа учета
                    $save_data['Storage_tid'] = $save_data['Storage_sid'];
                    $save_data['Contragent_sid'] = null;
                    $save_data['Storage_sid'] = null;
                    $res = $this->DocumentUc_model->saveObject('DocumentUc', $save_data);
                    if (!empty($res['DocumentUc_id'])) {
                        $data['PostDocumentUc_id'] = $res['DocumentUc_id'];
                    } else if (!empty($res['Error_Msg'])) {
                        $error[] = $res['Error_Msg'];
                    }
                }

				//сохранение списка медикаментов
				if(!empty($data['DocumentUcStrJSON'])) {
					$res = $this->DocumentUc_model->saveDocumentUcStrFromJSON(array(
						'DocumentUc_id' => $this->OutData['DocumentUc_id'],
						'PostDocumentUc_id' => !empty($data['PostDocumentUc_id']) ? $data['PostDocumentUc_id'] : null,
						'EmergencyTeam_id' => $data['EmergencyTeam_id'],
						'json_str' => $data['DocumentUcStrJSON'],
						'pmUser_id' => $data['pmUser_id'],
						'region' => $region,
						'orgtype' => $orgtype,
						'DrugDocumentType_Code' => !empty($data['DrugDocumentType_Code']) ? $data['DrugDocumentType_Code'] : null,
                        'AccountType_id' => $data['AccountType_id']
					));
                    if (!empty($res['Error_Msg'])) {
                        $error[] = $res['Error_Msg'];
                    }

                    if (count($error) == 0 && $res && isset($res['file_data'])) {
						foreach($res['file_data'] as $file_data) {
							$res = $this->saveFileChanges(array(
								'ObjectID' => $file_data['DocumentUcStr_id'],
								'ObjectName' => 'DocumentUcStr',
								'changed_data' => $file_data['changed_data']
							));
                            if (!empty($res['Error_Msg'])) {
                                $error[] = $res['Error_Msg'];
                            }
						}
					}

                    //автоматическое создание нарядов для добавленных строк
                    if (count($error) == 0 && !empty($data['DocumentUc_id'])) {
                        $res = $this->DocumentUc_model->autoCreateDocumentUcStorageWork($data['DocumentUc_id']);
                        if (!empty($res['Error_Msg'])) {
                            $error[] = $res['Error_Msg'];
                        }
                    }
				}

                //признак расходного типа документа учета
                $is_exp = $this->isExpendableDrugDocumentType(array(
                    'DrugDocumentType_id' => $data['DrugDocumentType_id']
                ));
				
				if (!($region == 'ufa' && $orgtype == 'farm')) { // Если не аптека ЛЛО Уфы  #116142
					//проверка наличия необходимого количества медикаментов на местах хранения для расходных типов документов учета
					if (count($error) == 0 && $is_exp) {
						$res = $this->DocumentUc_model->checkDrugStorageZoneCount(array(
							'DocumentUc_id' => $this->OutData['DocumentUc_id']
						));
						if (!empty($res['Error_Msg'])) {
							$error[] = $res['Error_Msg'];
						}
					}
				}

				//сохранение примечания
                if (count($error) == 0) {
                    $res = $this->DocumentUc_model->saveNote(array(
                        'DocumentUc_id' => $this->OutData['DocumentUc_id'],
                        'Note_id' => $data['Note_id'],
                        'Note_Text' => $data['Note_Text'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                }

                //сохранение информации о связи документа с ЛПУ и подразделением
                if (count($error) == 0) {
                    $res = $this->DocumentUc_model->saveDocumentUcLink(array(
                        'DocumentUc_id' => $this->OutData['DocumentUc_id'],
                        'Lpu_id' => $lpu_id,
                        'LpuBuilding_id' => $data['LpuBuilding_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!empty($res['Error_Msg'])) {
                        $error[] = $res['Error_Msg'];
                    }
                }

				if (!($region == 'ufa' && $orgtype == 'farm')) {  //  Если это не аптека Уфы
					//резервирование медикаментов
					if (count($error) == 0) {
						$res = $this->DocumentUc_model->createReserve(array(
							'DocumentUc_id' => $this->OutData['DocumentUc_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!empty($res['Error_Msg'])) {
							$error[] = $res['Error_Msg'];
						}
					}
				}

                //сохранение типа учета в связанных партиях
                if (count($error) == 0 && !empty($data['AccountType_id'])) {
                    $res = $this->DocumentUc_model->saveLinkedDrugShipmentAccountType(array(
                        'DocumentUc_id' => $this->OutData['DocumentUc_id'],
                        'AccountType_id' => $data['AccountType_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!empty($res['Error_Msg'])) {
                        $error[] = $res['Error_Msg'];
                    }
                }

                //сохранение даты выполнения документа в связанных партиях
                if (count($error) == 0) {
                    $res = $this->DocumentUc_model->saveLinkedDrugShipmentSetDT(array(
                        'DocumentUc_id' => $this->OutData['DocumentUc_id'],
                        'DrugShipment_setDT' => !empty($data['DocumentUc_didDate']) ? $data['DocumentUc_didDate'] : null,
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!empty($res['Error_Msg'])) {
                        $error[] = $res['Error_Msg'];
                    }
                }
			}

            if (count($error) > 0) {
                //откат транзакции
                $this->DocumentUc_model->rollbackTransaction();
                $this->ReturnError($error[0]);
            } else {
                //коммит транзакции
                $this->DocumentUc_model->commitTransaction();
                $this->ReturnData();
            }

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление 
	 */
	function delete() {
        $session_data = getSessionParams();
		$data = $this->ProcessInputData('delete', false);
		if ($data) {
            $data['pmUser_id'] = $session_data['pmUser_id'];
			$response = $this->DocumentUc_model->delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Удаление для аптеки
	 */
	function farm_delete() {
		$data = $this->ProcessInputData('farm_delete', false);
		if ($data) {
			$response = $this->DocumentUc_model->farm_delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Отмена обеспечения рецепта
	 */
	function canceling() {
		$data = $this->ProcessInputData('canceling', true);
		if ($data) {
			$response = $this->DocumentUc_model->canceling($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Формирование списка складов
	 */
	function loadStorageList() {
		$data = $this->ProcessInputData('loadStorageList',true);
		if ($data) {
			$response = $this->DocumentUc_model->loadStorageList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Формирование списка складов с прикрепленными МО
	 */
	function loadStorage2LpuList() {
		$data = $this->ProcessInputData('loadStorageList',true);
		if ($data) {
			$response = $this->DocumentUc_model->loadStorage2LpuList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка строк документа учета
	 */
	function loadDocumentUcStrList() {
		$filter = $this->ProcessInputData('loadDocumentUcStrList', true);
		if ($filter) {
			$response = $this->DocumentUc_model->loadDocumentUcStrList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	} 
	
	/**
	 * Загрузка списка строк документа учета
	 */
	function farm_loadDocumentUcStrList() {
		$filter = $this->ProcessInputData('farm_loadDocumentUcStrList', false);
		if ($filter) {
			$response = $this->DocumentUc_model->farm_loadDocumentUcStrList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение строк спецификации для документа учета медикаментов на основе ГК.
	 */
	function getDocumentUcStrListByWhsDocumentSupply() {
        $this->load->helper("Options");
        $this->load->model("Options_model", "Options_model");

		$data = $this->ProcessInputData('getDocumentUcStrListByWhsDocumentSupply');

		if ($data) {
            $data['options'] = $this->Options_model->getOptionsAll($data);
			$response = $this->DocumentUc_model->getDocumentUcStrListByWhsDocumentSupply($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении строк для документа учета')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение строк спецификации для документа учета медикаментов на основе заказа.
	 */
	function getDocumentUcStrListByWhsDocumentUcOrder() {
		$data = $this->ProcessInputData('getDocumentUcStrListByWhsDocumentUcOrder');

		if ($data) {
			$response = $this->DocumentUc_model->getDocumentUcStrListByWhsDocumentUcOrder($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении строк для документа учета')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     *  Получение данных строки приходного документа учета по штрих-коду
     */
    function getDocumentUcStrDataByBarCode() {
		$data = $this->ProcessInputData('getDocumentUcStrDataByBarCode');

		if ($data) {
			$response = $this->DocumentUc_model->getDocumentUcStrDataByBarCode($data);
			$this->ProcessModelList($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Перенос загруженных файлов из временной папки в папку для аплоада, а также схоранение информации в бд
	 */
	function saveFileChanges($data) {
		$session_data = getSessionParams();
		$this->load->model('PMMediaData_model', 'PMMediaData_model');

		$response = array('success' => true, 'Error_Code' => '', 'Error_Msg' => '');
		$ObjectName = '';
		$ObjectID = 0;

		if (isset($data['ObjectName']) && $data['ObjectName'] != '' && isset($data['ObjectID']) && $data['ObjectID'] > 0) {
			$ObjectName = $data['ObjectName'];
			$ObjectID = $data['ObjectID'];
		} else {
			return false;
		}

		$dt = (array) $data['changed_data'];
		foreach($dt as $val) {
			$val = ((array) $val);
			$file_name = $val['pmMediaData_FilePath'];

			//сохранение данных о файле в БД
			if ($val['state'] == 'add') {
				$file_data = array(
					'orig_name' => $val['pmMediaData_FileName'],
					'file_name' => $val['pmMediaData_FilePath'],
					'ObjectName' => $ObjectName,
					'ObjectID' => $ObjectID,
					'description' => $val['pmMediaData_Comment'],
					'pmUser_id' => $session_data['pmUser_id']
				);

				$res = $this->PMMediaData_model->savepmMediaData($file_data);
				if ($res['Error_Msg'] != '') {
					$response['success'] = false;
					$response['Error_Code'] = $res['Error_Code'];
					$response['Error_Msg'] = $res['Error_Msg'];
				}
			}
			if ($val['state'] == 'delete') {
				$res = $this->delFile(array(
					'id' => $val['pmMediaData_id'],
					'file_name' => $file_name
				), true);
                if ($res['Error_Msg'] != '') {
                    $response['success'] = false;
                    $response['Error_Code'] = $res['Error_Code'];
                    $response['Error_Msg'] = $res['Error_Msg'];
                }
			}
		}

		return $response;
	}

	/**
	 * Удаление файла
	 */
	private function delFile($data, $return_array = false) {
		$this->load->model('PMMediaData_model', 'PMMediaData_model');

        $error = array();

        try {
            if ($data['id'] > 0) {
                $data['pmMediaData_id'] = $data['id'];
                $response = $this->PMMediaData_model->getpmMediaData($data);
                $data['file'] = isset($response[0]) && isset($response[0]['pmMediaData_FilePath']) ? $response[0]['pmMediaData_FilePath'] : '';
            } else {
                $data['file'] = $data['file_name'];
            }

            // Проверяем корректность имени файла
            if ( !preg_match("/^([0-9a-z\.]+)$/i", $data['file']) ) {
                $error[] = array('Error_Code' => 101 , 'Error_Msg' => 'Имя файла '.$data['file'].' имеет некорректный вид');
                throw new Exception();
            }

            $filename = './'.PMMEDIAPATH.$data['file'];

            if (!is_file($filename)) {
                // Удаляем данные о файле из бд
                $response = $this->PMMediaData_model->deletepmMediaData($data);
                $error[] = array('Error_Code' => 102 , 'Error_Msg' => 'Файл '.$filename.' не найден!');
                throw new Exception();
            }

            if (!is_writable($filename)){
                $error[] = array('Error_Code' => 103 , 'Error_Msg' => 'Файл не может быть удален, т.к. нет прав на запись');
                throw new Exception();
            }

            // Удаляем файл
            if (unlink($filename)) {
                // Удаляем данные о файле из бд
                $response = $this->PMMediaData_model->deletepmMediaData($data);
            } else {
                $error[] = array('Error_Code' => 104 , 'Error_Msg' => 'Попытка удалить файл провалилась.');
                throw new Exception();
            }
        } catch (Exception $e) {
            $result = array(
                'Error_Code' => 0,
                'Error_Msg' => 'Ошибка',
                'success' => false
            );

            if (count($error) > 0) {
                if (isset($error[0]['Error_Code'])) {
                    $result['Error_Code'] = $error[0]['Error_Code'];
                }
                $result['Error_Msg'] = $error[0]['Error_Msg'];
            }

            if ($return_array) {
                return $result;
            } else {
                $result['Error_Msg'] = toUTF($result['Error_Msg']);
                echo json_encode($result);
                return false;
            }
        }
	}

	/**
	 * Загрузка списка медикаментов для комбо (используется при редактировании спецификации документа учета)
	 */
	function loadDrugComboForDocumentUcStr() {
		$data = $this->ProcessInputData('loadDrugComboForDocumentUcStr', false);
		if ($data) {
			$filter = $data;
			$response = $this->DocumentUc_model->loadDrugComboForDocumentUcStr($filter);
            if (!empty($filter['limit'])) {
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
	 * Загрузка списка медикаментов для комбо (используется при редактировании спецификации документа учета)
	 * Для аптек
	 */
	function farm_loadDrugComboForDocumentUcStr() {
		$data = $this->ProcessInputData('farm_loadDrugComboForDocumentUcStr', false);
		if ($data) {
			$filter = $data;
			$response = $this->DocumentUc_model->farm_loadDrugComboForDocumentUcStr($filter);
            if (!empty($filter['limit'])) {
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
	 * Получние данных серии по медикаменту и серии. 
	 */
	function getPrepSeriesByDrugAndSeries() {
		$data = $this->ProcessInputData('getPrepSeriesByDrugAndSeries', false);
		if ($data){
			$response = $this->DocumentUc_model->getPrepSeriesByDrugAndSeries($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получние данных МОЛ по контрагенту или складу.
	 */
	function getMolByContragentOrStorage() {
		$data = $this->ProcessInputData('getMolByContragentOrStorage', false);
		if ($data){
			$response = $this->DocumentUc_model->getMolByContragentOrStorage($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка контрагентов
	 */
	function loadContragentList() {
		$data = $this->ProcessInputData('loadContragentList', false);
		if ($data) {
            $response = array();
            switch($data['mode']) {
                case 'krym_t': //режим "Получатель" для Крыма
                    $response = $this->DocumentUc_model->loadContragentKrymTList($data);
                    break;
                default:
                    $response = $this->DocumentUc_model->loadContragentList($data);
                    break;
            }

			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка партий для комбо (используется при редактировании спецификации документа учета)
	 */
	function loadDocumentUcStrOidCombo() {
		$data = $this->ProcessInputData('loadDocumentUcStrOidCombo', false);
		if ($data) {
			$filter = $data;
			$response = $this->DocumentUc_model->loadDocumentUcStrOidCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение зарезервированного количества медикамента для строки документа
	 */
	function getReservedDrugOstatForDocumentUcStr() {
		$data = $this->ProcessInputData('getReservedDrugOstatForDocumentUcStr', false);
		if ($data) {
			$filter = $data;
			$response = $this->DocumentUc_model->getReservedDrugOstatForDocumentUcStr($filter);
			$this->ReturnData($response);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Загрузка списка партий для комбо (используется при редактировании спецификации документа учета)
	 * Для аптек
	 */
	function farm_loadDocumentUcStrOidCombo() {
		$data = $this->ProcessInputData('farm_loadDocumentUcStrOidCombo', false);
		if ($data) {
			$filter = $data;
			$response = $this->DocumentUc_model->farm_loadDocumentUcStrOidCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение сгенерированного наименования партии
	 */
	function generateDrugShipmentName() {
		$response = $this->DocumentUc_model->generateDrugShipmentName();
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение сгенерированного номера для документа учета
	 */
	function generateDocumentUcNum() {
		$data = $this->ProcessInputData('generateDocumentUcNum', false);
		if ($data) {
			$response = $this->DocumentUc_model->generateDocumentUcNum($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Исполнение документа 
	 */
	function executeDocumentUc() {
        $this->load->helper("Options");
		$this->load->model("Farmacy_model", "Farmacy_model");
		$this->load->model("Options_model", "Options_model");

		$data = $this->ProcessInputData('executeDocumentUc');
		if ($data) {
			//Получаем данные документа учета документа учета
			$response = $this->DocumentUc_model->load($data);
			if (is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id'])) {
				$data = array_merge($data, $response[0]);
			} else {
				$this->ReturnError("Не удалось получить данные документа учета.");
				return false;
			}

			//Установка идентификатора ГК в созданых партиях, если требуется
			if ($data['DocumentUc_id'] > 0) {
				$this->DocumentUc_model->setDrugShipmentSupply($data);
			}

			//проверка на формирование инв. ведомостей
			/*$response = $this->DocumentUc_model->checkInventExists($data);
			if ($response && !empty($response['Error_Msg'])) {
				$this->ReturnError($response['Error_Msg']);
				return false;
			}*/

			//для совместимости с прежними реализациями исполнения ддокументов учета
			switch($data['DrugDocumentType_Code']) {
				case 2: //Документ списания
				case 3: //Документ ввода остатков
				case 6: //Приходная накладная
				case 10: //Расходная накладная
				case 12: //Документ оприходования
				case 15: //Накладная на внутреннее перемещение
				case 17: //Возвратная накладная (расходная)
				case 18: //Возвратная накладная (приходная)
				case 20: //Пополнение укладки со склада
				case 21: //Списание медикаментов со склада на пациента
				case 23: //Списание в производство.
				case 25: //Списание медикаментов со склада на пациента. СМП
                case 31: //Накладная на перемещение внутри склада
                case 32: //Приход в отделение
                case 33: //Возврат из отделения
                case 34: //Разукомплектация: списание
					$data['options'] = $this->Options_model->getOptionsAll($data);
					$response = $this->DocumentUc_model->executeDocumentUc($data);
					break;
				default:
					$response = $this->Farmacy_model->executeDocumentUc($data);
					if (empty($response['Error_Msg']) && $response['DocumentUc_id'] > 0) {
						$response['success'] = true;
					}
					break;
			}

			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	
	/**
	 * Исполнение документа для аптек
	 */
	function farm_executeDocumentUc() {
		$this->load->model("Farmacy_model", "Farmacy_model");
		$this->load->model("Options_model", "Options_model");

		$data = $this->ProcessInputData('farm_executeDocumentUc');
		if ($data) {
			//Получаем данные документа учета документа учета
			$response = $this->DocumentUc_model->load($data);
			if (is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id'])) {
				$data = array_merge($data, $response[0]);
			} else {
				$this->ReturnError("Не удалось получить данные документа учета.");
				return false;
			}

			//Установка идентификатора ГК в созданых партиях, если требуется
			if ($data['DocumentUc_id'] > 0) {
				$this->DocumentUc_model->setDrugShipmentSupply($data);
			}

			//проверка на формирование инв. ведомостей
			$response = $this->DocumentUc_model->checkInventExists($data);
			if ($response && !empty($response['Error_Msg'])) {
				$this->ReturnError($response['Error_Msg']);
				return false;
			}

			//для совместимости с прежними реализациями исполнения ддокументов учета
			switch($data['DrugDocumentType_Code']) {
				case 2: //Документ списания
				case 3: //Документ ввода остатков
				case 6: //Приходная накладная
				case 10: //Расходная накладная
				case 11: //Документ реализации
				case 12: //Документ оприходования
				case 15: //Накладная на внутреннее перемещение
				case 17: //Возвратная накладная (расходная)
				case 18: //Возвратная накладная (приходная)
				case 20: // Пополнение укладки со склада
				case 21: // Списание медикаментов со склада на пациента
					$data['options'] = $this->Options_model->getOptionsGlobals($data);
					$response = $this->DocumentUc_model->farm_executeDocumentUc($data);
					break;
				default:
					$response = $this->Farmacy_model->farm_executeDocumentUc($data);
					if (empty($response['Error_Msg']) && $response['DocumentUc_id'] > 0) {
						$response['success'] = true;
					}
					break;
			}

			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Проверка наименование партии на уникальность
	 */
	function checkDrugShipmentName() {
		$data = $this->ProcessInputData('checkDrugShipmentName', false);

		if ($data){
			$response = $this->DocumentUc_model->checkDrugShipmentName($data);
			$this->ProcessModelList($response, true, 'Ошибка при проверке наименования партии')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных накладной
	 */
	function getDocNakData() {
		$data = $this->ProcessInputData('getDocNakData', false);
		if ($data){
			$response = $this->DocumentUc_model->getDocNakData($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Загрузка списка заявок для комбо или формы поиска (используется при редактировании документа учета)
     */
    function loadWhsDocumentSpecificityList() {
        $data = $this->ProcessInputData('loadWhsDocumentSpecificityList', false);
        if ($data) {
            $response = $this->DocumentUc_model->loadWhsDocumentSpecificityList($data);
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
     * Загрузка списка заказаов на производство для комбо или формы поиска (используется при редактировании документа учета)
     */
    function loadWhsDocumentUcOrderList() {
        $data = $this->ProcessInputData('loadWhsDocumentUcOrderList', false);
        if ($data) {
            $response = $this->DocumentUc_model->loadWhsDocumentUcOrderList($data);
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
     * Загрузка списка штрих-кодов
     */
    function loadDrugPackageBarCodeList() {
        $data = $this->ProcessInputData('loadDrugPackageBarCodeList', false);
        if ($data) {
            $response = $this->DocumentUc_model->loadDrugPackageBarCodeList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
	 * Загрузка списка мест хранения медикамента
	 */
	function loadStorageZoneCombo() {
		$filter = $this->ProcessInputData('loadStorageZoneCombo', false);
		if ($filter) {
			if (empty($filter['Storage_id']) && !empty($filter['Storage_sid'])) { //идентификатор склада может прийти с альт
				$filter['Storage_id'] = $filter['Storage_sid'];
			}
			$response = $this->DocumentUc_model->loadStorageZoneCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
	 * Загрузка списка мест хранения медикамента для конкретного медикамента
	 */
	function loadStorageZoneByDrugIdCombo() {
		$filter = $this->ProcessInputData('loadStorageZoneByDrugIdCombo', false);
		if ($filter) {
			$response = $this->DocumentUc_model->loadStorageZoneByDrugIdCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Формирование спецификации документа учета из выбранных остатков
     */
    function getDocumentUcStrListByDrugOstatRegistry() {
        $data = $this->ProcessInputData('getDocumentUcStrListByDrugOstatRegistry');

        if ($data) {
            $response = $this->DocumentUc_model->getDocumentUcStrListByDrugOstatRegistry($data);
            $this->ProcessModelSave($response, true, 'Ошибка при получении строк для документа учета')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadLpuCombo() {
        $data = $this->ProcessInputData('loadLpuCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->DocumentUc_model->loadLpuCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadLpuBuildingCombo() {
        $data = $this->ProcessInputData('loadLpuBuildingCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->DocumentUc_model->loadLpuBuildingCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadGoodsUnitCombo() {
        $data = $this->ProcessInputData('loadGoodsUnitCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->DocumentUc_model->loadGoodsUnitCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка отделений для комбобокса (фильтры АРМ Товароведа)
     */
    function loadLpuSectionMerchCombo() {
        $data = $this->ProcessInputData('loadLpuSectionMerchCombo', false);
        if ( $data === false ) { return false; }

        $response = $this->DocumentUc_model->loadLpuSectionMerchCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка складов для комбобокса (фильтры АРМ Товароведа)
     */
    function loadStorageMerchCombo() {
        $data = $this->ProcessInputData('loadStorageMerchCombo', false);
        if ( $data === false ) { return false; }

        $response = $this->DocumentUc_model->loadStorageMerchCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка МОЛ для комбобокса (фильтры АРМ Товароведа)
     */
    function loadMolMerchCombo() {
        $data = $this->ProcessInputData('loadMolMerchCombo', false);
        if ( $data === false ) { return false; }

        $response = $this->DocumentUc_model->loadMolMerchCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Ссоздание наряда на выполнение работ
     */
    function createDocumentUcStorageWork() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('createDocumentUcStorageWork', false);
        if (!empty($data)) {
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->DocumentUc_model->createDocumentUcStorageWork($data);
            $this->ProcessModelSave($response, true, 'Ошибка при создании наряда на выполнение работ')->ReturnData();
            return true;
        } else {
            return false;
        }
    }
	
	/**
	*	checkPrepSeries
	*/
	function checkPrepSeries()
	{
		$data = $this->ProcessInputData('checkPrepSeries',true);
		if($data === false)
			return false;
		$result = $this->DocumentUc_model->checkPrepSeries($data);
		$this->ProcessModelSave($result, true, 'Error')->ReturnData();
		return true;
	}

	/**
	 * Вспомогательная функция удаления резерва
	 */
	function removeReserve() {
		$data = $this->ProcessInputData('removeReserve', false);
		if (!empty($data)) {
			$this->DocumentUc_model->beginTransaction();
			$response = $this->DocumentUc_model->removeReserve($data);

			if (!empty($data['debug'])) {
				if(empty($response['Error_Msg'])) {
					$response['Error_Msg'] = 'Отладка';
				} else {
					print "<br/>Ошибка: {$response['Error_Msg']}<br/>";
				}
			}

			if (!empty($response['Error_Msg'])) {
				$this->DocumentUc_model->rollbackTransaction();
				$this->ReturnError($response['Error_Msg']);
				return false;
			} else {
				$response['success'] = true;
			}

			$this->DocumentUc_model->commitTransaction();
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
?>
