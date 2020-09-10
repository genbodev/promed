<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* RegistryRecept - операции с реестрами рецептов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Registry
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      20.12.2012
* @property Registry_model dbmodel
*/
class RegistryRecept extends swController 
{
	var $dbgroup = "registry";
	var $model_name = "RegistryRecept_model";
	//var $model_name = "ufa/Ufa_RegistryRecept_model";

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		// Инициализация класса и настройки
		$this->load->database($this->dbgroup, false);
		//Выставляем таймауты для выполнения запросов, пока вручную
		$this->db->query_timeout = 600;
		$this->load->model($this->model_name, 'dbmodel');
		
		$options = @unserialize($_SESSION['settings']);
		
		$this->inputRules = array(
			'importRegistryFromDbf' => array(
				array(
					'field' => 'RegistryReceptType_id',
					'label' => 'Тип реестра рецептов',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteRegistryRecept' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryReceptDataGrid' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryReceptReceptOtovGrid' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryReceptEvnReceptGrid' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryReceptDocumentUcGrid' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryDataReceptList' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryDataRecept_Snils',
					'label' => 'СНИЛС',
					'rules' => '',
					'type' => 'string'
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
			'loadDrugOstatRegistryList' => array(
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
					'field' => 'OrgType_id',
					'label' => 'Тип организации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgType_Filter',
					'label' => 'Тип организации',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
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
					'field' => 'Storage_id_state',
					'label' => 'Признак наличия идентификатора склада',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'SubAccountType_id',
					'label' => 'Тип субсчета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentUc_Date',
					'label' => 'Дата ГК',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentUc_Num',
					'label' => 'Номер ГК',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentUc_Name',
					'label' => 'Наименование ГК',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AllowReservation',
					'label' => 'Флаг учёта резервирования',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KLAreaStat_id',
					'label' => 'Территория',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugNomen_Code',
					'label' => 'Код номенклатуры',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugComplexMnnCode_Code',
					'label' => 'Код номенклатуры',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RlsActmatters_RusName',
					'label' => 'Наименование действующего вещества',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RlsTorg_Name',
					'label' => 'Торговое наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RlsClsdrugforms_Name',
					'label' => 'Наименование формы выпуска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PrepSeries_Ser',
					'label' => 'Серия выпуска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PrepSeries_isDefect',
					'label' => 'Фальсификат',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PrepSeries_godnMinMonthCount',
					'label' => 'Остаточный срок годности (мес.) от',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PrepSeries_godnMaxMonthCount',
					'label' => 'Остаточный срок годности (мес.) до',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LastUpdateDayCount',
					'label' => 'Товар без движения (количество дней)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CLSPHARMAGROUP_ID',
					'label' => 'Идентификатор фармгруппы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CLSATC_ID',
					'label' => 'Идентификатор анатомо-террапевтической группы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CLS_MZ_PHGROUP_ID',
					'label' => 'Идентификатор фармгруппы МЗ РФ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'STRONGGROUPS_ID',
					'label' => 'Идентификатор группы сильнодейсвующих ЛС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NARCOGROUPS_ID',
					'label' => 'Идентификатор группы наркотических ЛС',
					'rules' => '',
					'type' => 'id'
				),
                array(
                    'field' => 'isPKU',
                    'label' => 'Флаг ПКУ',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'FIRMS_ID',
					'label' => 'Идентификатор фирмы производителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'COUNTRIES_ID',
					'label' => 'Идентификатор страны производителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentSupply_Year',
					'label' => 'Финансовый год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AccountType_id',
					'label' => 'Тип учета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'GoodsUnit_id',
					'label' => 'Ед. учета',
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
            'loadDocumentUcList' => array(
                array(
                    'field' => 'DrugOstatRegistry_id',
                    'label' => 'Строка регистра',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'loadDrugTurnoverList' => array(
                array(
                    'field' => 'PeriodRange',
                    'label' => 'Отчетный период',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				), 
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
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
					'field' => 'Storage_id',
					'label' => 'Склад',
					'rules' => '',
					'type' => 'id'
				),
                array(
                        'field' => 'Drug_Code',
                        'label' => 'Код ЛП',
                        'rules' => '',
                        'type' => 'int'
                ),
				array(
					'field' => 'Drug_Name',
					'label' => 'Торговое наименование',
					'rules' => '',
					'type' => 'string' 
				),
				array(
					'field' => 'DrugMNN_Name',
					'label' => 'Наименование МНН',
					'rules' => '',
					'type' => 'string' 
				),
                 array(
					'default' => 0,
					'field' => 'Differences',
					'label' => 'Расхождения в остатках',
					'rules' => '',
					'type' => 'int'
				),
				array(
					//'default' => 0,
					'field' => 'SubAccountType_id',
					'label' => 'Тип субсчета',
					'rules' => '',
					'type' => 'int'
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
            'loadDrugTurnoverDetail' => array(
                array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'PeriodRange',
                    'label' => 'Отчетный период',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'DrugShipment_id',
                    'label' => 'ИД партии',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'WhsDocumentCostItemType_id',
                    'label' => 'Статья расхода',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Drug_Code',
                    'label' => 'Код ЛП',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'ИД ЛПУ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'SubAccountType_id',
                    'label' => 'Тип субсчета',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
                    'field' => 'DrugOstatRegistry_id',
                    'label' => 'Строка регистра остатков',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
			'UpdateDrugOstatRegistry_balances' => array(
				array(
					'field' => 'DrugOstatRegistry_id',
					'label' => 'ИД',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugOstat_Kolvo',
					'label' => 'Количество',
					'rules' => '',
					'type' => 'float'
				)
			), 
			'loadDrugPeriodCloseList' => array(
				array(
					'field' => 'DrugPeriodCloseView_Apteka',
					'label' => 'Аптека',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugPeriodCloseType_id',
					'label' => 'ИД статуса периода',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveDrugPeriodClose' => array(
				array(
					'field' => 'DrugPeriodClose_id',
					'label' => 'ИД записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'ИД аптеки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugPeriodOpen_DT',
					'label' => 'Дата открытия периода',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DrugPeriodClose_DT',
					'label' => 'Дата закрытия периода',
					'rules' => '',
					'type' => 'date'
				)
			),	
		    
		    'geDrugPeriodCloseDT' => array(
		    
			    array(
					'field' => 'Org_id',
					'label' => 'ИД аптеки',
					'rules' => '',
					'type' => 'int'
				)
			),
		    
		    'ufaExportPL2dbf' => array(
                                array(
                                    'field' => 'PeriodRange',
                                    'label' => 'Период выгрузки',
                                    'rules' => 'trim',
                                    'type' => 'daterange'
                                ),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов (ИД)',
					'rules' => '',
					'type' => 'int'
				),
			array(
					'field' => 'path',
					'label' => 'Путь к папке',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRegistryReceptViewForm' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryReceptErrorList' => array(
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
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
			'loadRegistryReceptErrorTypeList' => array(
				array(
					'field' => 'RegistryReceptErrorType_id',
					'label' => 'Идентификатор типа ошибки',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadRegistryReceptList' => array(
				array(
					'field' => 'ReceptUploadLog_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryRecept_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryRecept_Snils',
					'label' => 'СНИЛС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryRecept_Fio',
					'label' => 'ФИО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PrivilegeType_Code',
					'label' => 'Льгота',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryRecept_Ser',
					'label' => 'Серия рецепта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryRecept_Num',
					'label' => 'Номер рецепта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_Name',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
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
					'field' => 'RegistryReceptType_id',
					'label' => 'Тип записи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'ЛС',
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
			'loadRegistryReceptExpertiseTypeGrid' => array(
				array(
					'field' => 'RegistryReceptExpertiseType_id',
					'label' => 'Идентификатор критерия экспертизы',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadRegistryReceptErrorTypeGrid' => array(
				array(
					'field' => 'RegistryReceptErrorType_id',
					'label' => 'Идентификатор ошибки',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveRegistryReceptExpertiseTypeActive' => array(
				array(
					'field' => 'RegistryReceptExpertiseType_id',
					'label' => 'Идентификатор критерия экспертизы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryReceptExpertiseType_SysNick',
					'label' => 'Системное имя критерия экспертизы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryReceptExpertiseType_Name',
					'label' => 'Наименование критерия экспертизы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryReceptExpertiseType_IsFLK',
					'label' => 'Признак ФЛК',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryReceptExpertiseType_IsActive',
					'label' => 'Признак активности критерия экспертизы',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryReceptExpertiseType_ErrorList',
					'label' => 'Список ошибок',
					'rules' => '',
					'type' => 'string'
				)
			),
            'loadStorageList'=> array(
                array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
                array('field' => 'StorageType_id', 'label' => 'Тип склада', 'rules' => '', 'type' => 'id'),
                array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
                array('field' => 'Lpu_oid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
                array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
                array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'),
                array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
                array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
                array('field' => 'StorageForAptMuFirst', 'label' => 'Перым вывести склад аптеки МО', 'rules' => '', 'type' => 'checkbox'),
                array('field' => 'date', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
                array('field' => 'query', 'label' => 'Наименование склада', 'rules' => '', 'type' => 'string')
            )
		);
	}
	
	/**
	 * Чтение данных для формы "Реестр рецептов, обеспеченных ЛС"
	 * Входящие данные: фильтры,
	 * На выходе: строка в JSON-формате
     *
     * @return string
     */
	function loadRegistryReceptViewForm()
	{
		$data = $this->ProcessInputData('loadRegistryReceptViewForm', false);
		if ($data === false) return false;
		
		$response = $this->dbmodel->loadRegistryReceptViewForm($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}
	
	/**
	 * Возвращает "список медикаментов ГК, доступных для выдачи разнарядки"
	 * Входящие данные: фильтры,
	 * На выходе: строка в JSON-формате
     *
     * @return string
     */
	function loadDrugOstatRegistryList() {
		$this->db = null;
		$this->load->database();
		
		$data = $this->ProcessInputData('loadDrugOstatRegistryList', false);
		if ($data === false) { return false; }
		//  В рамках разделения АРМа товароведа
		$session_data = getSessionParams();
		$orgtype = $session_data['session']['orgtype'];
		$region =  $session_data['session']['region']['nick'];
		 $isAdminLLO = false;
		if ($region == 'ufa') {
		    $groups = explode('|', $_SESSION['groups']);
		    foreach ($groups as $group) {
			    if ($group == 'AdminLLO') {
				    $isAdminLLO = true;
			    }
		    }
		}
		
		if ($region == 'ufa' && ($orgtype == 'farm' || $isAdminLLO)) {
		    $response = $this->dbmodel->farm_loadDrugOstatRegistryList($data);
		}
		else {
		    $response = $this->dbmodel->loadDrugOstatRegistryList($data);
		}
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Возвращает список документов учета связанных со строкой регистра остатков
	 * Входящие данные: идентификатор строки регистра остатков,
	 * На выходе: строка в JSON-формате
     *
     * @return string
     */
	function loadDocumentUcList() {
		$this->db = null;
		$this->load->database();
		
		$data = $this->ProcessInputData('loadDocumentUcList', false);
		if ($data === false) { return false; }

        $response = $this->dbmodel->loadDocumentUcList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
        
        
    /**
	 * Возвращает ОБОРОТНУЮ ВЕДОМОСТЬ по аптеке"
	 * Входящие данные: фильтры
	 * 
    */
	function loadDrugTurnoverList()
	{
		$this->db = null;
		$this->load->database();
		
		$data = $this->ProcessInputData('loadDrugTurnoverList', false);
                //echo '<pre>' . print_r($data, 1) . '</pre>'; exit;
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadDrugTurnoverList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
        
        
        /**
	 * Возвращает детализацию ОБОРОТНОЙ ВЕДОМОСТИ по аптеке"
	 * Входящие данные: фильтры
	 * 
        */
	function loadDrugTurnoverDetail()
	{
		$this->db = null;
		$this->load->database();
		
		$data = $this->ProcessInputData('loadDrugTurnoverDetail', false);
                //echo '<pre>' . print_r($data, 1) . '</pre>'; exit;
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadDrugTurnoverDetail($data);
                
                $count = 0;
               
		for ( $key = 0; $key < count($response ['data']); $key++)  {
			$response ['data'][$key]['endOst'] = 0; 
			
			if (isset($response  ['data'] [$key]['BegOst']))
				$response  ['data'] [$key]['endOst'] = $response  ['data'] [$key]['BegOst'];
			if (isset($response  ['data'] [$key]['Pr_Kol']))
				$response  ['data'] [$key]['endOst'] = $response  ['data'] [$key]['endOst'] + $response  ['data'] [$key]['Pr_Kol'];
			if (isset($response  ['data'] [$key]['Ras_Kol']) && $response  ['data'] [$key]['recdeleted'] != 2
                                && $response  ['data'] [$key]['recdeleted'] != 3)  
				$response ['data'] [$key]['endOst'] = $response  ['data'] [$key]['endOst'] - $response  ['data'] [$key]['Ras_Kol'];
			$response  ['data'] [$key]['endOst'] = $response  ['data'] [$key]['endOst'] + $count;
			
			$response  ['data'] [$key]['endOst'] = number_format($response  ['data'] [$key]['endOst'], 2, '.', '');

				
			if  ($response  ['data'] [$key]['recType'] != -1) {
				$count = $response  ['data'] [$key]['endOst'];
			}
		}

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Корректировка очтатков
	 * 
	*/
	function UpdateDrugOstatRegistry_balances()
	{
		$this->db = null;
		$this->load->database();
		$data = $this->ProcessInputData('UpdateDrugOstatRegistry_balances');
		//var_dump($data);
		if ($data === false) return false;
			$response = $this->dbmodel->UpdateDrugOstatRegistry_balances($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			//return true;  
		}
		
	 /**
	 *  Закрытие отчетного периода по аптеке (список)
	 */
	function loadDrugPeriodCloseList() 
	{
		$this->db = null;
		$this->load->database();
		$data = $this->ProcessInputData('loadDrugPeriodCloseList');
		$response = $this->dbmodel->loadDrugPeriodCloseList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}	
	
	/**
	 *  Сохранение отчетного периода по аптекам в БД
	 */
	function saveDrugPeriodClose() {
	    $this->db = null;
	    $this->load->database();
		
	    $data = $this->ProcessInputData('saveDrugPeriodClose', false);
	    //echo '<pre>' . print_r($data, 1) . '</pre>'; exit;
	    if ($data === false) { return false; }
	    
	    $response = $this->dbmodel->saveDrugPeriodClose($data);
	    //var_dump($response);
	    
	    $this->ProcessModelSave($response, true, 'Ошибка при сохранении отчетного периода')->ReturnData();
	    
	    //$this->ProcessModelSave($response, true, true)->ReturnData();

	    //return true;
	    
	}
	
	/**
	 * Получение даты закрытия отчетного периода
	 */
	function geDrugPeriodCloseDT() {
		$this->db = null;
		$this->load->database();
		$data = $this->ProcessInputData('geDrugPeriodCloseDT');
		$response = $this->dbmodel->geDrugPeriodCloseDT($data);
		$this->ProcessModelList($response, true)->ReturnData();

		return true;
	}
        
        
	/**
	 * Экспорт результатов поиска в регистре остатков в формате CSV.
     */
	function exportDrugOstatRegistryList() {
        $this->db = null;
        $this->load->database();

		//так как для экспорта нам нужно получить отфильтрованный список, используем для получения параметров чужие inputRules
		$data = $this->ProcessInputData('loadDrugOstatRegistryList', false);
		if ($data === false) { return false; }

		$data['export'] = true;
		$response = $this->dbmodel->loadDrugOstatRegistryList($data);
		if( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		set_time_limit(0);

		if(!is_dir(EXPORTPATH_OSTAT_REGISTRY)) {
			if (!mkdir(EXPORTPATH_OSTAT_REGISTRY)) {
				DieWithError("Ошибка при создании директории ".EXPORTPATH_OSTAT_REGISTRY."!");
			}
		}

		$f_name = "ostat_registry";
		$file_name = EXPORTPATH_OSTAT_REGISTRY.$f_name.".csv";
		$archive_name = EXPORTPATH_OSTAT_REGISTRY.$f_name.".zip";
		if( is_file($archive_name) ) {
			unlink($archive_name);
		}

		try {
			$h = fopen($file_name, 'w');
			if(!$h) {
				DieWithError("Ошибка при попытке открыть файл!");
			}
			$str_result = "";
			$str_result .= "Район;";
			$str_result .= "Организация;";
			$str_result .= "Склад;";
			$str_result .= "Тип субсчета;";
			$str_result .= "МНН;";
			$str_result .= "Код ЛП;";
			$str_result .= "Торговое наим.;";
			$str_result .= "Форма выпуска;";
			$str_result .= "Дозировка;";
			$str_result .= "Фасовка;";
			$str_result .= "Серия выпуска;";
			$str_result .= "Срок годности;";
			$str_result .= "Производитель;";
			$str_result .= "№ РУ;";
			$str_result .= "Количество медикамента;";
			$str_result .= "Цена (руб.);";
			$str_result .= "Сумма (руб.);";
			$str_result .= "Ед.измерения;";
			$str_result .= "№ ГК;";
			$str_result .= "Год;";
			$str_result .= "Источник финансирования;";
			$str_result .= "Статья расхода;";
			$str_result .= "Партия\n";

			$patterns = array('/&alpha;/u', '/&beta;/u', '/&mdash;|&ndash;/u');
			$replacements = array('a', 'b', '-');

			foreach($response as $row) {
				$str_result .= str_replace(';','',$row['Org_Area']).";";
				$str_result .= str_replace(';','',$row['Org_Name']).";";
				$str_result .= str_replace(';','',$row['Storage_Name']).";";
				$str_result .= str_replace(';','',$row['SubAccountType_Name']).";";
				$str_result .= str_replace(';','',$row['ActMatters_RusName']).";";
				$str_result .= str_replace(';','',$row['DrugNomen_Code']).";";
				$str_result .= str_replace(';','',preg_replace($patterns,$replacements,html_entity_decode(htmlspecialchars_decode(strip_tags($row['Prep_Name']))))).";";
				$str_result .= str_replace(';','',$row['DrugForm_Name']).";";
				$str_result .= str_replace(';','',$row['Drug_Dose']).";";
				$str_result .= str_replace(';','',$row['Drug_Fas']).";";
				$str_result .= str_replace(';','',$row['PrepSeries_Ser']).";";
				$str_result .= str_replace(';','',$row['PrepSeries_GodnDate']).";";
				$str_result .= str_replace(';','',preg_replace($patterns,$replacements,html_entity_decode(htmlspecialchars_decode(strip_tags($row['Firm_Name']))))).";";
				$str_result .= str_replace('.',',',$row['Reg_Num']).";";
				$str_result .= str_replace('.',',',$row['DrugOstatRegistry_Kolvo']).";";
				$str_result .= str_replace('.',',',$row['DrugOstatRegistry_Price']).";";
				$str_result .= str_replace('.',',',$row['DrugOstatRegistry_Sum']).";";
				$str_result .= str_replace(';','',$row['Okei_Name']).";";
				$str_result .= str_replace('.',',',$row['WhsDocumentUc_Num']).";";
				$str_result .= str_replace('.',',',$row['WhsDocumentSupply_Year']).";";
				$str_result .= str_replace(';','',$row['DrugFinance_Name']).";";
				$str_result .= str_replace(';','',$row['WhsDocumentCostItemType_Name']).";";
				$str_result .= str_replace('.',',',$row['DrugShipment_Name'])."\n";
			}

			$str_result = toAnsi($str_result, true);

			fwrite($h, $str_result);
			fclose($h);

			$zip = new ZipArchive();
			$zip->open($archive_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_name, basename($file_name));
			$zip->close();
			unlink($file_name);

			$this->ReturnData(array('success' => true, 'url' => $archive_name));
		} catch (Exception $e) {
			DieWithError($e->getMessage());
			$this->ReturnData(array('success' => false));
		}

		if(is_file($file_name)) {
			@unlink($file_name);
		}
	}
	
	/**
	 * Загрузка списка "Данные рецепта по реестру"
     */
	function loadRegistryReceptDataGrid()
	{
		$data = $this->ProcessInputData('loadRegistryReceptDataGrid', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryReceptDataGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка списка "Данные о выписке рецепта"
     */
	function loadRegistryReceptEvnReceptGrid()
	{
		$data = $this->ProcessInputData('loadRegistryReceptEvnReceptGrid', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryReceptEvnReceptGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка списка "Данные об обеспечении рецепта ЛС"
     */
	function loadRegistryReceptReceptOtovGrid()
	{
		$data = $this->ProcessInputData('loadRegistryReceptReceptOtovGrid', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryReceptReceptOtovGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка списка "Данные об отпущенных аптеке ЛС"
     */
	function loadRegistryReceptDocumentUcGrid()
	{
		$data = $this->ProcessInputData('loadRegistryReceptDocumentUcGrid', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryReceptDocumentUcGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Удаляет реестры рецептов
	*  Входящие данные: RegistryRecept_id
	*  На выходе: JSON-строка
	*  Используется: форма списка реестров
	*/	
	function deleteRegistryRecept()
	{
		$data = $this->ProcessInputData('deleteRegistryRecept', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deleteRegistryRecept($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении реестра рецептов')->ReturnData();
		
		return true;
	}
	
	/**
	*  Импорт реестра рецептов
	*  На выходе: JSON-строка
	*  Используется: форма списка реестров
	*/		
	function importRegistryFromDbf() 
	{
		$data = $this->ProcessInputData('importRegistryFromDbf', true);
		if ($data === false) { return false; }

		$upload_path = './'.IMPORTPATH_ROOT.$data['Lpu_id'].'/';
		$allowed_types = explode('|','zip');
		
		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл реестра!') ) );
			return false;
		}
		
		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array($file_data['file_ext'], $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}
		
		$lFile = null;
		$pFile = null;
		
		if ($data['RegistryReceptType_id'] == 1) {
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
			{
				// там должен быть 2 файла L*.dbf и P*.dbf, если их нет -> файл не является архивом реестра
				$dbfcount = 0;
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/^L.*dbf$/', $filename) > 0 ) {
						$lFile = $filename;
					}
					
					if ( preg_match('/^P.*dbf$/', $filename) > 0 ) {
						$pFile = $filename;
					}
				}
				
				if ($dbfcount>=2) {
					$zip->extractTo( $upload_path );
				}
				
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);

			if (empty($pFile) || empty($lFile))
			{
				$this->ReturnData( array('success' => false, 'Error_Code' => 100016 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
				return false;
			}

			$recRecAll = 0;
			$recRecPersAll = 0;
			
			// импорт сведений о рецептах
			$h = dbase_open($upload_path . $lFile, 0);
			if ( $h ) {
				$r = dbase_numrecords($h);

				for ( $i = 1; $i <= $r; $i++ ) {
					$rech = dbase_get_record_with_names($h, $i);

					foreach ( $rech as $key => $value ) {
						$rech[$key] = trim($rech[$key]);
					}

					array_walk($rech, 'ConvertFromWin866ToCp1251');

					$rs = $this->dbmodel->saveRegistryReceptData(array_merge($data,$rech));
					$recRecAll++;
				}

				dbase_close($h);
			}
			
			// импорт сведений о пациентах
			$h = dbase_open($upload_path . $pFile, 0);
			if ( $h ) {
				$r = dbase_numrecords($h);

				for ( $i = 1; $i <= $r; $i++ ) {
					$rech = dbase_get_record_with_names($h, $i);

					foreach ( $rech as $key => $value ) {
						$rech[$key] = trim($rech[$key]);
					}

					array_walk($rech, 'ConvertFromWin866ToCp1251');

					$rs = $this->dbmodel->saveRegistryReceptPersonData(array_merge($data,$rech));
					$recRecPersAll++;
				}

				dbase_close($h);
			}

			$this->ReturnData(array('success' => true, 'recRecAll' => $recRecAll, 'recRecPersAll' => $recRecPersAll, 'Message' => toUTF('Данные обработаны.')));
			return true;
		} else {
			unlink($_FILES["RegistryFile"]["tmp_name"]);
			$this->ReturnError('Импорт сводных реестров не реализован');
			return false;
		}
	}
	
	/**
	 * Возвращает рецепты реестра по id реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
     *
     * @return string
     */
	function loadRegistryDataReceptList()
	{
		$data = $this->ProcessInputData('loadRegistryDataReceptList', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryDataReceptList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	*  Возвращает список реестров
	*  Входящие данные: фильтры
	*  На выходе: JSON-строка
	*  Используется: форма списка реестров
	*/
	function loadRegistryReceptList()
	{
		$data = $this->ProcessInputData('loadRegistryReceptList', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryReceptList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	*  Возвращает список ошибок реестров
	*  Входящие данные: фильтры
	*  На выходе: JSON-строка
	*  Используется: форма списка реестров
	*/
	function loadRegistryReceptErrorList()
	{
		$data = $this->ProcessInputData('loadRegistryReceptErrorList', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryReceptErrorList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	*  Возвращает список типов ошибок реестров
	*  Входящие данные: фильтры
	*  На выходе: JSON-строка
	*  Используется: форма списка реестров
	*/
	function loadRegistryReceptErrorTypeList()
	{
		$data = $this->ProcessInputData('loadRegistryReceptErrorTypeList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryReceptErrorTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Обновляет статус активности критерия экспертизы реестров
	 *  Используется: форма справочника критерия экспертизы
	 */
	function saveRegistryReceptExpertiseTypeActive()
	{
		$data = $this->ProcessInputData('saveRegistryReceptExpertiseTypeActive', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveRegistryReceptExpertiseTypeActive($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}

	/**
	*  Возвращает список критериев экспертизы
	*  Входящие данные: фильтры
	*  На выходе: JSON-строка
	*  Используется: форма справочника критерия экспертизы
	*/
	function loadRegistryReceptExpertiseTypeGrid()
	{
		$data = $this->ProcessInputData('loadRegistryReceptExpertiseTypeGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryReceptExpertiseTypeGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	*  Возвращает список ошибок
	*  Входящие данные: фильтры
	*  На выходе: JSON-строка
	*  Используется: форма справочника критерия экспертизы
	*/
	function loadRegistryReceptErrorTypeGrid()
	{
		$data = $this->ProcessInputData('loadRegistryReceptErrorTypeGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryReceptErrorTypeGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

    /**
     * Формирование списка складов
     */
    function loadStorageList() {
        $this->db = null;
        $this->load->database();

        $data = $this->ProcessInputData('loadStorageList',true);
        if ($data) {
            $response = $this->dbmodel->loadStorageList($data);
            $this->ProcessModelList($response,true,true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
	/**
	 * Выгрузка файлов P и L
	 */
    function ufaExportPL2dbf() {
		$this->db = null;
		$this->load->database();
		
		$data = $this->ProcessInputData('ufaExportPL2dbf', false);
		if (!$data) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Отсутствуют входные параметры.'));
			return false;
		}
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
	
		$defP = array(
			array("SS", "C", 14),
			array("SN_POL", "C", 25),
			array("FAM", "C", 40),
			array("IM", "C", 40),
			array("OT", "C", 40),
			array("W", "C", 1),
			array("DR", "C", 10),
			array("C_KATL", "C", 3),
			array("SN_DOC", "C", 16),
			array("C_DOC", "N", 2, 0),
			array("OKATO_OMS", "N", 5, 0),
			array("QM_OGRN", "C", 15),
			array("OKATO_REG", "N", 5, 0),
			array("D_TYPE", "C", 3)
		);

		$defL = array(
			array("SS", "C", 14),
			array("OKATO_REG", "N", 5, 0),
			array("C_OGRN", "C", 15),
			array("MCOD", "C", 7),
			array("PCOD", "C", 22),
			array("DS", "C", 7),
			array("SN_LR", "C", 20),	
			array("DATE_VR", "D", 8),
			array("C_FINL", "N", 1, 0),
			array("PR_LR", "N", 3, 0),
			array("A_COD", "C", 20),
			array("NOMK_LS", "N", 13, 0),
			array("KO_ALL", "N", 7, 3),
			array("DOZ_ME", "N", 5, 0),
			array("C_PFS", "N", 9, 0),
			array("DATE_OBR", "D", 8),
			array("DATE_OTP", "D", 8),
			array("SL_ALL", "N", 11, 2),
			array("TYPE_SCHET", "N", 1, 0),
			array("FO_OGRN", "C", 15),
			array("P_KEK", "N", 1, 0),
			array("D_TYPE", "C", 3),
			array("LINEID", "N", 10, 0), 
			array("OWNER", "C", 5),
			array("RAS", "C", 3),
			array("SPR_TYPE", "N", 1, 0),
			array("SPECIFIC", "N", 2, 0)
			// ,array("N_R", "C", 20)
		);

		try
		{
			$response = $this->dbmodel->ufaExportPL2dbf_Pers($data);

			if ( !is_array($response) || count($response) == 0 || !array_key_exists('data', $response) || !is_array($response['data']) ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => 'Произошла ошибка при получении данных. Сообщите об ошибке разработчикам.'));
				return false;
			}
			else if ( count($response['data']) == 0 ) {
				$this->textlog->add('exportRegistryToDbf: Отсутствуют данные для выгрузки.');
				$this->ReturnData(array('success' => false, 'Error_Msg' => 'Отсутствуют данные для выгрузки.'));
				return false;
			}
			//echo ('EXPORTPATH_PL = ' .EXPORTPATH_PL);
			$out_dir = "recipes_" . time();
			$file_zip_name = EXPORTPATH_REC . $out_dir . "/recipes_" .$data['path'] .".zip"; 
			
			
			$fname = 'fileP.dbf';
			mkdir(EXPORTPATH_REC . $out_dir);
			$file_re_name = EXPORTPATH_REC . $out_dir . "/" . $fname;
			$h = dbase_create($file_re_name, $defP);
			
			foreach ( $response['data'] as $row ) {
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record($h, array_values($row));
			}

			dbase_close($h);
				
			//  Файл типа L
			$responseL = $this->dbmodel->ufaExportPL2dbf_L($data);
			//var_dump($responseL);
			if ( !is_array($responseL) || count($responseL) == 0 || !array_key_exists('data', $responseL) || !is_array($responseL['data']) ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => 'Произошла ошибка при получении данных. Сообщите об ошибке разработчикам.'));
				return false;
			}
			else if ( count($responseL['data']) == 0 ) {
				//$this->textlog->add('exportRegistryToDbf: Отсутствуют данные для выгрузки.');
				$this->ReturnData(array('success' => false, 'Error_Msg' => 'Отсутствуют данные для выгрузки.'));
				return false;
			}
			$fnameL = 'fileL.dbf';
			$file_re_nameL = EXPORTPATH_REC . $out_dir . "/" . $fnameL;
			$hL = dbase_create($file_re_nameL, $defL);
			
			foreach ( $responseL['data'] as $row ) {
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record($hL, array_values($row));
			}

			dbase_close($hL);
			
			$zip = new ZipArchive();
			//echo '3';
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			//echo '4';
			//$zip->AddFile('C:\temp\1.dbf');
			$zip->AddFile($file_re_name, $fname); 
			$zip->AddFile($file_re_nameL, $fnameL); 
			
			$zip->close();
			
			//Удаляем исходные файлы
			if (!@unlink($file_re_name)) {
				log_message('debug', 'ufaExportPL2dbf: Не удалось удалить исходный файл после архивации ' . $file_re_name);
			}			
			if (!@unlink($file_re_nameL)) {
				log_message('debug', 'ufaExportPL2dbf: Не удалось удалить исходный файл после архивации ' . $file_re_nameL);
			}
			log_message('debug', 'ufaExportPL2dbf: Архив закрыли '. $file_zip_name);
			$result = array('success' => true, 'filename' => $file_zip_name);
			//echo  $result;
			//echo "{'success':true} {'filename':$file_zip_name}";
			$this->ReturnData($result);
			return true;
		}
		catch (Exception $e)
			{
				log_message('error', $e->getMessage());
				echo "{'success':false}";
			}

	}
	
    /**
     * Выгрузка справочника врачей
     */
    function ufaExportCVF2dbf() {
	    
		$this->db = null;
		$this->load->database();
		/*
		$data = $this->ProcessInputData('ufaExportCVF2dbf', false);
		if (!$data) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Отсутствуют входные параметры.'));
			return false;
		}
		*/
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
	
		$def = array(
			array("TF_OKATO", "C", 11),
			array("MCOD", "C", 7),
			array("PCOD", "C", 22),
			array("FAM_V", "C", 30),
			array("IM_V", "C", 20),
			array("OT_V", "C", 20),
			array("C_OGRN", "C", 15),
			array("PRVD", "N", 19, 5),
			array("D_JOB", "C", 50),
			array("D_PRIK", "D", 8),
			array("D_SER", "D", 8),
			array("PRVS", "C", 9),
			array("KV_KAT", "C", 1),
			array("DATE_B", "D", 8),
			array("DATE_E", "D", 8),
			array("MSG_TEXT", "C", 100)
		);  
		try
		{			
			$response = $this->dbmodel->ufaExportCVF2dbf();
			
			//var_dump($response);
			if ( !is_array($response) || count($response) == 0 || !array_key_exists('data', $response) || !is_array($response['data']) ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => 'Произошла ошибка при получении данных. Сообщите об ошибке разработчикам.'));
				return false;
			}
			else if ( count($response['data']) == 0 ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => 'Отсутствуют данные для выгрузки.'));
				return false;
			}
			//echo ('Step 1');
			$out_dir = "CVF_" . time();
			$file_zip_name = EXPORTPATH_REC . $out_dir . "/cvf.zip"; 
			
			
			$fname = 'CVF.dbf';
			
			//conv ('utf-8', 'windows-1251', $fname);
			//ConvertFromWin1251ToUTF8($var)
			//ConvertFromWin1251ToUTF8($fname);
			//$fname = iconv('windows-1251', 'utf-8', $fname);
			//$fname = iconv('utf-8', 'windows-1251', $fname);
			//iconv("utf-8", "cp1251", $fname);
			 //$fname = iconv('CP866', 'utf-8', $fname);
			
			$fname = iconv( 'utf-8', 'cp866', $fname);
			mkdir(EXPORTPATH_REC . $out_dir);
			
			$file_re_name = EXPORTPATH_REC . $out_dir . "/" . $fname;
			$hL = dbase_create($file_re_name, $def);
			
			foreach ( $response['data'] as $row ) {
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record($hL, array_values($row));
			}

			dbase_close($hL);
			
			$zip = new ZipArchive();
			//echo '3';
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			//echo '4';
			//$zip->AddFile('C:\temp\1.dbf');
			$zip->AddFile($file_re_name, $fname);  
			
			$zip->close();
			
			//Удаляем исходные файлы
			if (!@unlink($file_re_name)) {
				log_message('debug', 'ufaExportCVF2dbf: Не удалось удалить исходный файл после архивации ' . $file_re_name);
			}			
			
			log_message('debug', 'ufaExportCVF2dbf: Архив закрыли '. $file_zip_name);
			$result = array('success' => true, 'filename' => $file_zip_name);
			//echo  $result;
			//echo "{'success':true} {'filename':$file_zip_name}";
			$this->ReturnData($result);
			return true;
		}
		catch (Exception $e)
			{
				log_message('error', $e->getMessage());
				echo "{'success':false}";
			}
    }
}