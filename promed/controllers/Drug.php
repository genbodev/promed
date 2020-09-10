<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Drug - операции с медикаментами
* Заодно тут же работа с аптеками
* Вынесено из dlo_ivp.php
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DlO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      23.07.2009
 *
 * @property Drug_model $dbmodel
*/

class Drug extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("Drug_model", "dbmodel");
		//$this->load->model("ufa/Ufa_Drug_model", "dbmodel");
		
		$this->inputRules = array(
			'loadDrugProtoMnnCombo' =>  array(
				array(
					'field' => 'DrugProtoMnn_id',
					'label' => 'МНН медикамента по протоколу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugMnn_id',
					'label' => 'МНН медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_id',
					'label' => 'Финансирование',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugProtoMnnType_id',
					'label' => 'Тип МНН медикамента по протоколу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugProtoMnn_Name',
					'label' => 'Название МНН медикамента по протоколу',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка запроса',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadDrugComplexMnnList' => array(
				array('field' => 'Date', 'label' => 'Дата', 'rules' => 'trim', 'type' => 'date'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа заболевания', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
				array('field' => 'fromReserve', 'label' => 'Флаг поиска в резерве врача', 'rules' => '', 'type' => 'int'),
				array('field' => 'query', 'label' => 'Наименование комплексного МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ReceptType_Code', 'label' => 'Код типа рецепта', 'rules' => '', 'type' => 'id'),
				array('field' => 'searchFull', 'label' => 'Режим поиска (2)', 'rules' => 'trim', 'type' => 'string'),
				array('default' => 'start', 'field' => 'mode', 'label' => 'Режим поиска', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PrivilegeType_id', 'label' => 'Категория льготы', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Финансирование', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_IsKEK', 'label' => 'Протокол ВК', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_IsMnn', 'label' => 'Выписка по МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'withOptions', 'label' => 'Учитывать глобальные настройки', 'rules' => '', 'type' => 'checkbox'),
				array('field' => 'is_mi_1', 'label' => 'МИ-1', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'recept_drug_ostat_viewing', 'label' => 'Настройка', 'rules' => '', 'type' => 'int', 'default' => -1),
				array('field' => 'recept_drug_ostat_control', 'label' => 'Настройка', 'rules' => '', 'type' => 'int', 'default' => -1),
				array('field' => 'recept_empty_drug_ostat_allow', 'label' => 'Настройка', 'rules' => '', 'type' => 'int', 'default' => -1),
				array('field' => 'select_drug_from_list', 'label' => 'Настройка', 'rules' => '', 'type' => 'string', 'default' => ''),
				array('field' => 'paging', 'label' => 'пэйджинг', 'rules' => '', 'type' => 'checkbox', 'default' => false ),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )
			),
			'loadDrugComplexMnnJnvlpList' => array(
				array('field' => 'Date', 'label' => 'Дата', 'rules' => 'trim', 'type' => 'date'),
				array('field' => 'query', 'label' => 'Наименование комплексного МНН', 'rules' => 'trim', 'type' => 'string'),
				array('default' => 'start', 'field' => 'mode', 'label' => 'Режим поиска', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
				array('field' => 'paging', 'label' => 'пэйджинг', 'rules' => '', 'type' => 'checkbox', 'default' => false ),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )
			),
			'loadDrugRlsList' => array(
				array('field' => 'Date', 'label' => 'Дата', 'rules' => 'trim', 'type' => 'date'),
				array('field' => 'Drug_rlsid', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование комплексного МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ReceptType_Code', 'label' => 'Код типа рецепта', 'rules' => '', 'type' => 'id'),
				array('field' => 'searchFull', 'label' => 'Режим поиска (2)', 'rules' => 'trim', 'type' => 'string'),
				array('default' => 'start', 'field' => 'mode', 'label' => 'Режим поиска', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'EvnRecept_IsMnn', 'label' => 'Выписка по МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'PrivilegeType_id', 'label' => 'Категория льготы', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Финансирование', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugOstatRegistry_id', 'label' => 'Разнорядка МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_IsKEK', 'label' => 'Протокол ВК', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа заболевания', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
				array('field' => 'is_mi_1', 'label' => 'МИ-1', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'recept_drug_ostat_viewing', 'label' => 'Настройка', 'rules' => '', 'type' => 'int', 'default' => -1),
				array('field' => 'recept_drug_ostat_control', 'label' => 'Настройка', 'rules' => '', 'type' => 'int', 'default' => -1),
				array('field' => 'recept_empty_drug_ostat_allow', 'label' => 'Настройка', 'rules' => '', 'type' => 'int', 'default' => -1),
				array('field' => 'select_drug_from_list', 'label' => 'Настройка', 'rules' => '', 'type' => 'string', 'default' => '')
			),
			'loadFarmacyRlsOstatList' => array(
				array('field' => 'Drug_rlsid', 'label' => 'Идентификатор медикамента', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'OrgFarmacy_id', 'label' => 'Идентификатор аптеки', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'ReceptType_Code', 'label' => 'Код типа рецепта', 'rules' => 'trim|required', 'type' => 'int'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'isKardio', 'label' => 'Флаг режима ЛЛО Кардио', 'rules' => '', 'type' => 'int')
			),
			'checkDrugOstatOnSklad' => array(
				array(
						'field' => 'Drug_id',
						'label' => 'Идентификатор медикамента',
						'rules' => 'required',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptFinance_Code',
						'label' => 'Код типа финансирования',
						'rules' => 'required',
						'type' => 'int'
					)
			),
			'loadSicknessDrugList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugMnn_id',
					'label' => 'Идентификатор МНН',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadDrugList' => array(
				array(
					'default' => date('Y-m-d'),
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugMnn_id',
					'label' => 'Идентификатор МНН',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_Is7Noz_Code',
					'label' => 'Код признака 7 нозологий',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 'start',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Наименование медикамента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ReceptFinance_Code',
					'label' => 'Код типа финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptType_Code',
					'label' => 'Код типа рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'searchFull',
					'label' => 'Режим поиска (2)',
					'rules' => 'trim',
					'type' => 'string'
				),
                array(
                    'field' => 'is_mi_1',
                    'label' => 'МИ-1',
                    'rules' => '',
                    'type'  => 'string'
                ),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Тип льготы',
					'rules' => '',
					'type' => 'id'
				)
			),
            'SearchDrugRlsList' => array(
                array(
                    'field' => 'query',
                    'label' => 'Наименование',
                    'rules' => 'trim',
                    'type' => 'string'
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
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type'  => 'id'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'subAccountType_id',
					'label' => 'Субсчет',
					'rules' => '',
					'type'  => 'id'
				)
            ),
			'loadDrugMnnList' => array(
				array(
					'field' => 'byDrugRequest',
					'label' => 'Признак по заявкам',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestRow_IsReserve',
					'label' => 'Признак выписки медикамента из резерва',
					//'rules' => $_SESSION['region']['nick'] == 'perm' ? 'required' : '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					//'rules' => $_SESSION['region']['nick'] == 'perm' ? 'required' : '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugProtoMnn_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_id',
					'label' => 'Тип финасирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Программа ЛЛО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => date('Y-m-d'),
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'DrugMnn_id',
					'label' => 'Идентификатор МНН',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_Is7Noz_Code',
					'label' => 'Код признака 7 нозологий',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 'start',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Наименование МНН',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ReceptFinance_Code',
					'label' => 'Код типа финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptType_Code',
					'label' => 'Код типа рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'searchFull',
					'label' => 'Режим поиска (2)',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadDrugMnnGrid' => array(
				array(
						'field' => 'DrugMnn_Name',
						'label' => 'Наименование МНН',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'privilegeType',
						'label' => 'Тип льготы',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'start',
						'label' => 'Номер стартовой записи',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 100,
						'field' => 'limit',
						'label' => 'Количество записей',
						'rules' => 'trim',
						'type' => 'id'
					)
			),
			'loadDrugTorgGrid' => array(
				array(
						'field' => 'DrugTorg_Name',
						'label' => 'Наименование медикамента',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'default' => 0,
						'field' => 'start',
						'label' => 'Номер стартовой записи',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 100,
						'field' => 'limit',
						'label' => 'Количество записей',
						'rules' => 'trim',
						'type' => 'id'
					)
			),
			'orgFarmacyReplace' => array(
				array(
						'field' => 'direction',
						'label' => 'Направление',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'OrgFarmacy_id',
						'label' => 'Идентификатор аптеки',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Lpu_id',
						'label' => 'Идентификатор ЛПУ',
						'rules' => 'trim',
						'type' => 'id'
					)
			),
			'getDrugOstat' => array(
				array(
						'field' => 'Drug_id',
						'label' => 'Идентификатор медикамента',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				array(
						'default' => '',
						'field' => 'mode',
						'label' => 'Тип выбора',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'ReceptFinance_Code',
						'label' => 'Код типа финансирования',
						'rules' => 'required',
						'type' => 'int'
					),
				array(
						'field' => 'ReceptType_Code',
						'label' => 'Код типа рецепта',
						'rules' => '',
						'type' => 'id'
					)
			),
			'getDrugOstatGrid' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Аптека выписки рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_oid',
					'label' => 'Аптека отоваривания рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_id',
					'label' => 'Тип финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'org_farm_filter',
					'label' => 'Аптека',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'mode',
					'label' => 'Тип загрузки',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadFarmacyOstatList' => array(
				array(
						'field' => 'Drug_id',
						'label' => 'Идентификатор медикамента',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				array(
						'field' => 'OrgFarmacy_id',
						'label' => 'Идентификатор аптеки',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptFinance_Code',
						'label' => 'Код типа финансирования',
						'rules' => 'trim|required',
						'type' => 'int'
					),
				array(
						'field' => 'ReceptType_Code',
						'label' => 'Код типа рецепта',
						'rules' => 'trim|required',
						'type' => 'int'
					)
			),
			'getOrgFarmacyGrid' => array(
				array(
						'field' => 'mnn',
						'label' => 'Наименование МНН',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'torg',
						'label' => 'Торговое наименование',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'orgfarm',
						'label' => 'Наименование аптеки',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'default' => 1,
						'field' => 'is_net_admin',
						'label' => 'Список для администратора сети аптек',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgFarmacys',
						'label' => 'Список аптек',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'add_without_orgfarmacy_line',
						'label' => 'Флаг',
						'rules' => '',
						'type' => 'string'
					),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onlyAttachLpu',
					'label' => 'Только прикрепленные к МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LLO_program',
					'label' => 'Программа ЛЛО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'typeList',
					'label' => 'Тип списка',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getOrgFarmacyGridByLpu' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'show_storage',
					'label' => 'Признак необходимости отображения прикрепленных складов',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getDrugGrid' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'mnn',
					'label' => 'Наименование МНН',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'torg',
					'label' => 'Торговое наименование',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'org_farm_filter',
					'label' => 'Аптека',
					'rules' => 'trim',
					'type' => 'string'
				),
			    array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 2,
					'field' => 'ost',
					'label' => 'Есть на остатках',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'start',
					'label' => 'С какого элемента начинать отображение',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'limit',
					'label' => 'Сколько элементов показывать на странице',
					'rules' => 'trim',
					'type' => 'int'
				)
			),
			'vklOrgFarmacy' => array(
				array(
						'field' => 'vkl',
						'label' => 'Флаг включения / отключения',
						'rules' => 'trim|required',
						'type' => 'int'
					),
				array(
						'field' => 'OrgFarmacy_id',
						'label' => 'Идентификатор аптеки',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgFarmacyIndex_id',
						'label' => 'Идентификатор индекса аптеки',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Lpu_id',
						'label' => 'Идентификатор ЛПУ',
						'rules' => 'trim',
						'type' => 'id'
					)
			),
			'getDrugOstatByFarmacyGrid' => array(
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => 'trim|required',
					'type' => 'id'
					),
				array(
					'field' => 'mnn',
					'label' => 'МНН',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'torg',
					'label' => 'Торговое наименование',
					'rules' => 'trim',
					'type' => 'string'
				),
			    array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'int'
					)
			),
			'saveDrugMnnLatinName' => array(
				array(
					'field' => 'DrugMnn_id',
					'label' => 'DrugMnn',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugMnn_NameLat',
					'label' => 'DrugMnn_NameLat',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'saveDrugTorgLatinName' => array(
				array(
					'field' => 'DrugTorg_id',
					'label' => 'DrugTorg',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTorg_NameLat',
					'label' => 'DrugTorg_NameLat',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'saveReceptWrong' => array(
				array('field' => 'ReceptWrong_id', 'label' => 'ИД отказа', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_id', 'label' => 'ИД рецепта', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'ИД организации', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'OrgFarmacy_id', 'label' => 'ИД аптеки', 'rules' => '', 'type' => 'id'),
				array('field' => 'ReceptWrong_decr', 'label' => 'Причина отказа', 'rules' => 'trim|required', 'type' => 'string')
            ),
			'loadReceptWrongInfo' => array(
						array(
							'field' => 'EvnRecept_id',
							'label' => 'EvnRecept_id',
							'rules' => 'trim',
							'type' => 'id'
						)
				),
			'GetMoByFarmacy' => array(
						array(
							'field' => 'Lpu_id',
							'label' => 'Lpu_id',
							'rules' => 'trim',
							'type' => 'id'
						),
				array(
							'field' => 'OrgFarmacy_id',
							'label' => 'OrgFarmacy_id',
							'rules' => 'trim',
							'type' => 'id'
						),
				array(
							'field' => 'WhsDocumentCostItemType_id',
							'label' => 'Идентификатор программы ЛЛО',
							'rules' => '',
							'type' => 'id'
				)
			), 
			'getLpuBuildingLinkedByOrgFarmacy' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Идентификатор программы ЛЛО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getLpuBuildingStorageLinkedByOrgFarmacy' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Идентификатор программы ЛЛО',
					'rules' => '',
					'type' => 'id'
				)
			),
				'saveMoByFarmacy' => array(
						array(
							'field' => 'arr',
							'label' => 'Параметры',
							'rules' => 'trim',
							'type' => 'string'
						),
					array(
							'field' => 'WhsDocumentCostItemType_id',
							'label' => 'Идентификатор программы ЛЛО',
							'rules' => '',
							'type' => 'id'
				)
			),
			'saveLpuBuildingLinkDataFromJSON' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Идентификатор программы ЛЛО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LinkDataJSON',
					'label' => 'Идентификатор программы ЛЛО',
					'rules' => '',
					'type' => 'json_array'
				)
			),
			'saveLpuBuildingStorageLinkDataFromJSON' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Идентификатор программы ЛЛО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LinkDataJSON',
					'label' => 'Идентификатор программы ЛЛО',
					'rules' => 'required',
					'type' => 'json_array'
			)
			),
			'deleteLpuBuildingLinkData' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Идентификатор программы ЛЛО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'updateFarmacyRlsOstatListBySpoUlo' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Категория льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadOrgFarmacyCombo' => array(
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => '',
					'type' => 'id'
				),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'loadOrgFarmacyStorageCombo' => array(
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}

	/**
	* Проверка наличия медикамента на аптечном складе
	* Используется в окне просмотра остатков медикамента по аптекам
	*/
	function checkDrugOstatOnSklad() {

		$val  = array();

		$data = $this->ProcessInputData('checkDrugOstatOnSklad', false);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->checkDrugOstatOnSklad($data);

		if (is_array($response) && count($response) > 0)
		{
			if ( isset($response[0]) && isset($response[0]['DrugOstat_Kolvo']) && $response[0]['DrugOstat_Kolvo'] > 0) {
				$val[0]['DrugOstat_Value'] = 'Да';
			}
			else {
				$val[0]['DrugOstat_Value'] = 'Нет';
			}
		}
		else
		{
			$val[0]['DrugOstat_Value'] = 'Нет';
		}

		array_walk($val[0], 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);
	}


	/**
	 * Description
	 */
	function loadDrugMnnGrid()
	{
		$data = $this->ProcessInputData('loadDrugMnnGrid', false);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadDrugMnnGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnLimitData(NULL, $data['start'], $data['limit']);
	}


	/**
	 * Description
	 */
	function loadDrugTorgGrid()
	{
		$data = $this->ProcessInputData('loadDrugTorgGrid', false);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadDrugTorgGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnLimitData(NULL, $data['start'], $data['limit']);
	}


	/**
	* Получение списка аптек с остатками по выбранному медикаменту
	*/
	function getDrugOstat()
	{
		$val  = array();

		$data = $this->ProcessInputData('getDrugOstat', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->getDrugOstat($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	* Получение списка медикаментов по выбранной аптеке
	*/
	function getDrugOstatByFarmacyGrid()
	{
		$data = $this->ProcessInputData('getDrugOstatByFarmacyGrid', false);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getDrugOstatByFarmacyGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Получение списка аптек с остатками по выбранному медикаменту
	*/
	function getDrugOstatGrid()
	{
		$data = $this->ProcessInputData('getDrugOstatGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getDrugOstatGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Получение комбобокса медикамента
	*/
	function loadDrugProtoMnnCombo()
	{
		$data = $this->ProcessInputData('loadDrugProtoMnnCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadDrugProtoMnnCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Получение полного списка аптек
	*/
	function getOrgFarmacyGrid()
	{
		$data = $this->ProcessInputData('getOrgFarmacyGrid', true);
		if ( $data === false ) { return false; }

		// подгружаем список администраторов сети аптек
		if ( isset($data['is_net_admin']) && $data['is_net_admin'] == 2 )
		{
			$response = $this->dbmodel->getOrgFarmacyNetGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
		else
		{
			$response = $this->dbmodel->getOrgFarmacyGrid($data);
			
			$outdata = array();

			if ( isset($data['add_without_orgfarmacy_line']) && $data['add_without_orgfarmacy_line'] == true )
			{
				$outdata[] = array(
					"OrgFarmacy_id" => -1, 
					"OrgFarmacy_Name"=> toUTF("[Аптека не в списке]")
				);
			}

			$outdata = array_merge($outdata,$this->ProcessModelList($response, true, true)->GetOutData());
			$this->ReturnData($outdata);
		}
	}
	
	/**
	* Получение полного списка аптек (для формы просмотра прикрепления к МО)
	*/
	function getOrgFarmacyGridByLpu()
	{
		$data = $this->ProcessInputData('getOrgFarmacyGridByLpu', false);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getOrgFarmacyGridByLpu($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Включение и выключение аптек
	*/
	function vklOrgFarmacy() {
		$data = $this->ProcessInputData('vklOrgFarmacy', true);

        if ($data['session']['region']['nick'] == 'buryatiya') {
			$access = (isSuperadmin() || isLpuAdmin());
			if (!$access) {
				if (!empty($data['session']['ARMList'])) {
					foreach ($data['session']['ARMList'] as $val) {
						if (in_array($val, array('lpuadmin', 'lloadmin', 'minzdravdlo'))) {
							$access = true;
						}
					}
				}
			}
			if(!$access){
                $this->ReturnError('Данная функция доступна только администратору ЛПУ.');
                return false;
			}
		} else {
            if (!isSuperAdmin() && (empty($data['session']['ARMList']) || (!in_array('minzdravdlo', $data['session']['ARMList']) &&  !in_array('adminllo', $data['session']['ARMList'])))) {
                $this->ReturnError('Включение/выключение аптек могут выполнять только администраторы ЦОД и специалисты ЛЛО ОУЗ.');
                return false;
            }
        }

		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->vklOrgFarmacy($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	* Получение списка медикаментов
	*/
	function getDrugGrid()
	{
		$data = $this->ProcessInputData('getDrugGrid', false);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->getDrugGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	* Изменение порядка аптек
	*/
	function orgFarmacyReplace()
	{
		$data = $this->ProcessInputData('orgFarmacyReplace', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->orgFarmacyReplace($data);
		
		$outdata=array();
		$outdata['data'] = $this->ProcessModelList($response, true, true)->GetOutData();
		$this->ReturnData($outdata);
	}


	/**
	* Список аптек, в которых есть медикамент
	*/
	function loadFarmacyOstatList() {
		$data = $this->ProcessInputData('loadFarmacyOstatList', true);
		if ( $data === false ) { return false; }		

		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsGlobals($data);
		
		$response = $this->dbmodel->loadFarmacyOstatList($data, $options['globals']);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	* Получение даты последнего обновления
	*/
	function getDrugOstatUpdateTime()
	{
		$response = $this->dbmodel->getDrugOstatUpdateTime();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	* Получение даты последнего обновления на РАС
	*/
	function getDrugOstatRASUpdateTime()
	{
		$response = $this->dbmodel->getDrugOstatRASUpdateTime();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	*  Получение справочника торговых наименований медикаментов
	*  Входящие данные: $_POST['Date'],
	*                   $_POST['Drug_id'],
	*                   $_POST['DrugMnn_id'],
	*                   $_POST['query'],
	*                   $_POST['ReceptFinance_Code'],
	*                   $_POST['ReceptType_Code']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function loadDrugList() {
		$data = $this->ProcessInputData('loadDrugList', true);
		if ($data) {
			if ( !isset($data['searchFull']) ) {
				$this->load->model("Options_model", "opmodel");
				$options = $this->opmodel->getOptionsGlobals($data);
				if ( (!isset($data['Drug_id']) && !isset($data['DrugMnn_id'])) && ((!isset($data['ReceptFinance_Code'])) || (!isset($data['ReceptType_Code'])) || (!isset($data['Date']))) ) {
					return false;
				}
                if($data['session']['region']['nick'] == 'ufa')
                {
                    $this->load->model("Dlo_EvnRecept_model", "ermodel");
					//$this->load->model('ufa/Ufa_Dlo_EvnRecept_model', 'ermodel');
                    $data['mode'] = 'all';
                    $response = $this->ermodel->loadDrugList($data);
                }
                else
				    $response = $this->dbmodel->loadDrugList($data, $options['globals']);
			}
			else {
				$response = $this->dbmodel->searchFullDrugList($data);
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * @return bool
     */
    function SearchDrugRlsList()
    {
        $data = $this->ProcessInputData('SearchDrugRlsList', true);
        $this->load->model("Farmacy_model", "fpmodel");
        //$response = $this->dbmodel->SearchDrugRlsList($data);
        //$response = $this->fpmodel->getDrugOstatForProvideFromBarcode($data);
		if($data['session']['region']['nick'] == 'ufa') {
			$response = $this->fpmodel->getDrugOstatForProvide($data);
		}		
		else {
			$response = $this->fpmodel->getDrugRlsListForProvide($data);
		}
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

	/**
	*  Получение справочника торговых наименований медикаментов для формы добавления дорогостоя
	*  Входящие данные: $_POST['DrugMnn_id']
	*  На выходе: JSON-строка
	*  Используется: форма назначения медикаментов по дорогостою в регистре заболеваний
	*/
	function loadSicknessDrugList()
	{
		$data = $this->ProcessInputData('loadSicknessDrugList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadSicknessDrugList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Description
	 */
	function loadDrugMnnList() {
		$data = $this->ProcessInputData('loadDrugMnnList', true);
		if ($data) {
			if ( !isset($data['searchFull']) ) {
				$this->load->model("Options_model", "opmodel");
				$options = $this->opmodel->getOptionsGlobals($data);
				if ( ($data['DrugMnn_id'] == 0) && ((!isset($data['query'])) || (!isset($data['ReceptType_Code'])) || (!isset($data['ReceptFinance_Code'])) || (!isset($data['Date']))) ) {
					return false;
				}
				$response = $this->dbmodel->loadDrugMnnList($data, $options['globals']);
			}
			else {
				$response = $this->dbmodel->searchFullDrugMnnList($data);
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Description
	 */
	function saveDrugMnnLatinName()
	{
		$user_groups = array();
		$val         = array();

		if (!isset($_SESSION['groups']))
		{
			$val = array('success' => false, 'Error_Msg' => 'Ошибка авторизации');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			return false;
		}

		if (!preg_match("/Admin/", $_SESSION['groups']))
		{
			$val = array('success' => false, 'Error_Msg' => 'У вас нет прав на редактирование МНН');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			return false;
		}

		$data = $this->ProcessInputData('saveDrugMnnLatinName', true);
		if ( $data === false ) { return false; }		
		
		$response = $this->dbmodel->saveDrugMnnLatinName($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Description
	 */
	function saveDrugTorgLatinName()
	{
		$user_groups = array();
		$val         = array();


		if ( !isset($_SESSION['groups']) )
		{
			$val = array('success' => false, 'Error_Msg' => 'Ошибка авторизации');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			return false;
		}

		if ( !preg_match("/Admin/", $_SESSION['groups']) )
		{
			$val = array('success' => false, 'Error_Msg' => 'У вас нет прав на редактирование торгового наименования медикамента');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			return false;
		}

		$data = $this->ProcessInputData('saveDrugTorgLatinName', true);
		if ( $data === false ) { return false; }		
		
		$response = $this->dbmodel->saveDrugTorgLatinName($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}


	/**
	 *	Получение списка комплексных МНН
	 */
	function loadDrugComplexMnnList() {
		$data = $this->ProcessInputData('loadDrugComplexMnnList', true);
		if ( $data === false ) { return false; }

		if ( empty($data['searchFull']) ) {
			$this->load->model("Options_model", "opmodel");
			$options = $this->opmodel->getOptionsGlobals($data);

			/*if ( empty($data['DrugComplexMnn_id']) && (empty($data['query']) || empty($data['ReceptType_Code']) || empty($data['Date'])) ) {
				return false;
			}*/

			$response = $this->dbmodel->loadDrugComplexMnnList($data, $options['globals']);
		}
		else {
			$response = $this->dbmodel->searchFullDrugComplexMnnList($data);
		}

		if ($data['paging']) {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}


	/**
	 *	Получение списка комплексных МНН (выборка из ЖНВЛП)
	 */
	function loadDrugComplexMnnJnvlpList() {
		$data = $this->ProcessInputData('loadDrugComplexMnnJnvlpList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadDrugComplexMnnJnvlpList($data);

		if ($data['paging']) {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}


	/**
	 *	Получение списка комплексных МНН
	 */
	function loadDrugRlsList() {
		$data = $this->ProcessInputData('loadDrugRlsList', true);
		if ( $data === false ) { return false; }

		if ( empty($data['searchFull']) ) {
			$this->load->model("Options_model", "opmodel");
			$options = $this->opmodel->getOptionsGlobals($data);

			if ( empty($data['Drug_rlsid']) && (empty($data['ReceptType_Code']) /*|| empty($data['WhsDocumentCostItemType_id'])*/ || empty($data['Date'])) ) {
				$this->ReturnData(['success' => false]);
				return false;
			}

			$response = $this->dbmodel->loadDrugRlsList($data, $options['globals']);
		}
		else {
			$response = $this->dbmodel->searchFullDrugRlsList($data);
		}

		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 *	Список аптек, в которых есть медикамент
	 */
	function loadFarmacyRlsOstatList() {
		$data = $this->ProcessInputData('loadFarmacyRlsOstatList', true);
		if ( $data === false ) { return false; }		

		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsGlobals($data);

		$response = $this->dbmodel->loadFarmacyRlsOstatList($data, $options['globals']);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	
     /**
     * Сохранение записи о признании рецепта недействительным
     */
    public function saveReceptWrong() {
        $data = $this->ProcessInputData('saveReceptWrong', true);
        if ($data === false) {
            return false;
        }
        
        $val = array();
        $response = $this->dbmodel->saveReceptWrong($data);

        foreach ($response as $row) {
            array_walk($row, 'ConvertFromWin1251ToUTF8');
            $val[] = $row;
        }

		//    Echo '{rows:'.json_encode($val).'}';
        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }
    
    /**
     * Загрузка записи о признании рецепта недействительным
     */
    public function loadReceptWrongInfo() {
         $data = $this->ProcessInputData('loadReceptWrongInfo', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->loadReceptWrongInfo($data);
        array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

	/**
     * Получение списка прикрепления МО/подразделений МО к аптеке
     */
    public function GetMoByFarmacy() {
		$data = $this->ProcessInputData('GetMoByFarmacy', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->GetMoByFarmacy($data);
        array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке
	 */
	function getLpuBuildingLinkedByOrgFarmacy() {
		$data = $this->ProcessInputData('getLpuBuildingLinkedByOrgFarmacy', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getLpuBuildingLinkedByOrgFarmacy($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке склада
	 */
	function getLpuBuildingStorageLinkedByOrgFarmacy() {
		$data = $this->ProcessInputData('getLpuBuildingStorageLinkedByOrgFarmacy', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getLpuBuildingStorageLinkedByOrgFarmacy($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
     * Запись прикрепления подразделений МО к аптеке
     */
    public function saveMoByFarmacy() {
		$data = $this->ProcessInputData('saveMoByFarmacy', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->saveMoByFarmacy($data);
        $this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Сохранение данных о прикреплении подразделений МО к аптеке
	 */
	function saveLpuBuildingLinkDataFromJSON() {
		$data = $this->ProcessInputData('saveLpuBuildingLinkDataFromJSON', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveLpuBuildingLinkDataFromJSON($data);
		$this->ProcessModelSave($response, true)->ReturnData();
}

	/**
	 * Сохранение данных о прикреплении подразделений МО к складам аптеки
	 */
	function saveLpuBuildingStorageLinkDataFromJSON() {
		$data = $this->ProcessInputData('saveLpuBuildingStorageLinkDataFromJSON', false);
		if ($data === false) {
			return false;
}

		$response = $this->dbmodel->saveLpuBuildingStorageLinkDataFromJSON($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Удаление данных о прикреплении подразделений МО к аптеке
	 */
	function deleteLpuBuildingLinkData() {
		$data = $this->ProcessInputData('deleteLpuBuildingLinkData', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deleteLpuBuildingLinkData($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Обновление данных об остатках при промощи сервиса СПО УЛО
	 */
	function updateFarmacyRlsOstatListBySpoUlo() {
		$data = $this->ProcessInputData('updateFarmacyRlsOstatListBySpoUlo', true);
		if ($data === false) {
			return false;
		}

		if (empty($data['pmUser_id'])) {
			$data['pmUser_id'] = $this->dbmodel->getPromedUserId();;
		}

		$response = array();
		$this->load->model("ServiceEMIAS_model", "ServiceEMIAS_model");

		try {
			$this->ServiceEMIAS_model->beginTransaction();
			if (empty($data['Drug_id']) && empty($data['DrugComplexMnn_id'])) {
				throw new Exception("Не удалось определить медикамент");
			}

			$response = $this->ServiceEMIAS_model->getRemains($data);
			if (!$response['success']) {
				throw new Exception(!empty($response['Error_Msg']) ? $response['Error_Msg'] : "При обновлении данных произошла ошибка");
			}

			$this->ServiceEMIAS_model->commitTransaction();
		} catch (Exception $e) {
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
			$this->ServiceEMIAS_model->rollbackTransaction();
		}

		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Получение списка аптек для комбобокса
	 */
	function loadOrgFarmacyCombo() {
		$data = $this->ProcessInputData('loadOrgFarmacyCombo', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadOrgFarmacyCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка складов аптеки для комбобокса
	 */
	function loadOrgFarmacyStorageCombo() {
		$data = $this->ProcessInputData('loadOrgFarmacyStorageCombo', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadOrgFarmacyStorageCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
