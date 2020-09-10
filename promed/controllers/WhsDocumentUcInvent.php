<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Специфика инвентаризационной ведомости
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Model Generator
 * @version      10.2014
 * @property WhsDocumentUcInvent_model WhsDocumentUcInvent_model
 */

class WhsDocumentUcInvent extends swController {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array('field' => 'WhsDocumentUc_pid', 'label' => 'WhsDocumentUc_pid', 'rules' => 'required', 'type' => ''),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'WhsDocumentUc_Num', 'rules' => 'required', 'type' => ''),
				array('field' => 'WhsDocumentUc_Name', 'label' => 'WhsDocumentUc_Name', 'rules' => 'required', 'type' => ''),
				array('field' => 'WhsDocumentType_id', 'label' => 'WhsDocumentType_id', 'rules' => 'required', 'type' => ''),
				array('field' => 'WhsDocumentUc_Date', 'label' => 'WhsDocumentUc_Date', 'rules' => 'required', 'type' => ''),
				array('field' => 'WhsDocumentUc_Sum', 'label' => 'WhsDocumentUc_Sum', 'rules' => 'required', 'type' => ''),
				array('field' => 'WhsDocumentStatusType_id', 'label' => 'WhsDocumentStatusType_id', 'rules' => 'required', 'type' => ''),
				array('field' => 'Org_aid', 'label' => 'Org_aid', 'rules' => 'required', 'type' => ''),
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Ссылка на инвентаризационную ведомость', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'WhsDocumentUcInvent_begDT', 'label' => 'Дата и время проведения инвентаризации', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'Org_id', 'label' => 'Организация, в которой проводится инвентаризация', 'rules' => '', 'type' => 'int'),
				array('field' => 'Contragent_id', 'label' => 'Контрагент', 'rules' => '', 'type' => 'int'),
				array('field' => 'Storage_id', 'label' => 'Склад, по которому проводится инвентаризация', 'rules' => '', 'type' => 'int'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'int'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'int')
			),
			'saveDrugFactKolvo' => array(
				array('field' => 'WhsDocumentUcInventDrug_id', 'label' => 'Идентификатор строки ведомости', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentUcInventDrug_FactKolvo', 'label' => 'Фактическое количество', 'rules' => 'trim', 'type' => 'string')
			),
			'saveWhsDocumentUcInventOrder' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор приказа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'WhsDocumentUc_Name', 'label' => 'Наименование документа', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'WhsDocumentType_id', 'label' => 'Идентификатор типа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Date', 'label' => 'Дата документа', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'WhsDocumentStatusType_id', 'label' => 'Идентификатор статуса', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Org_aid', 'label' => 'Org_aid', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WhsDocumentSupplyInventListJSON', 'label' => 'Список госконтрактов', 'rules' => '', 'type' => 'string'),
				array('field' => 'WhsDocumentUcInventListJSON', 'label' => 'Список ведомостей', 'rules' => '', 'type' => 'string')
			),
			'load' => array(
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'int')
			),
			'loadWhsDocumentUcInventOrder' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'int')
			),
			'loadList' => array(
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'OrgStruct_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_DateRange', 'label' => 'WhsDocumentUc_DateRange', 'rules' => 'trim', 'type' => 'daterange'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentStatusType_id', 'label' => 'Статус', 'rules' => '', 'type'	=> 'int'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'Contragent_id', 'label' => 'Контрагент', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'Storage_id', 'label' => 'Склад', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'StorageZone_id', 'label' => 'Место хранения', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'Storage_Name', 'label' => 'Склад', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ARMType', 'label' => 'Тип АРМ', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id')
			),
			'loadWhsDocumentSupplyInventList' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => 'required', 'type' => 'id')
			),
			'loadWhsDocumentUcInventList' => array(
				array('field' => 'WhsDocumentUc_pid', 'label' => 'Идентификатор документа учета', 'rules' => 'required', 'type' => 'id')
			),
			'loadWhsDocumentUcInventDrugList' => array(
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Идентификатор ведомости', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Drug_Name', 'label' => 'Наименование медикамента', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'StorageZone_id', 'label' => 'Место хранения', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'PersonWork_eid', 'label' => 'Идентификатор исполнителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'GoodsUnit_id', 'label' => 'Ед. учета', 'rules' => '', 'type' => 'id')
			),
			'loadWhsDocumentUcInventOrderList' => array(
				array('field' => 'Year', 'label' => 'Год', 'rules' => '', 'type' => 'int'),
				array('field' => 'Org_aid', 'label' => 'Организация', 'rules' => '', 'type' => 'id')
			),
			'loadStorageList' => array(
				array('field' => 'Org_aid', 'label' => 'Организация в которой создан приказ', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Date', 'label' => 'Дата создания приказа', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => '', 'type' => 'id'),
				array('field' => 'withStorageZones', 'label' => 'Флаг - места хранения', 'rules' => '', 'type' => 'id')
			),
			'delete' => array(
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int')
			),
			'deleteWhsDocumentUcInventOrder' => array(
				array('field' => 'id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int')
			),
			'loadOrgCombo' => array(
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка запроса из комбобокса', 'rules' => '', 'type' => 'string')
			),
			'createDocumentUcInventList' => array(
				array('field' => 'Storage_List', 'label' => 'Список складов', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_List', 'label' => 'Список организаций', 'rules' => '', 'type' => 'string')
			),
			'createDocumentUcInventDrugList' => array(
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Идентификатор ведомости', 'rules' => 'required', 'type' => 'id')
			),
			'createDocumentUcInventDrugListAllCommon' => array(
				array('field' => 'WhsDocumentUcInvent_ids', 'label' => 'Идентификаторы ведомостей', 'rules' => 'required', 'type' => 'string')
			),
			'createDocumentUcInventDrugListAll' => array(
				array('field' => 'WhsDocumentUcInvent_List', 'label' => 'Идентификаторы ведомостей', 'rules' => '', 'type' => 'string'),
				array('field' => 'Remake', 'label' => 'Remake', 'rules' => '', 'type' => 'int')
			),
			'createDocumentUc' => array(
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Идентификатор ведомости', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugDocumentType_SysNick', 'label' => 'Тип документа', 'rules' => 'required', 'type' => 'string')
			),
			'signWhsDocumentUcInvent' => array(
				array('field' => 'WhsDocumentUcInvent_id', 'label' => 'Идентификатор ведомости', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'sign', 'label' => 'Признак подписания', 'rules' => 'required', 'type' => 'string')
			),
			'signWhsDocumentUcInventOrder' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор приказа', 'rules' => 'required', 'type' => 'id')
			),
			'getNotice' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор приказа', 'rules' => 'required', 'type' => 'id')
			),
            'createDocumentUcStorageWork' => array(
                array('field' => 'WhsDocumentUcInventDrug_List', 'label' => 'Список медикаментов', 'rules' => 'required', 'type' => 'string'),
                array('field' => 'DocumentUcTypeWork_id', 'label' => 'Вид работы', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Person_cid', 'label' => 'Заказчик', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Post_cid', 'label' => 'Должность заказчика', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Person_eid', 'label' => 'Заказчик', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Post_eid', 'label' => 'Должность заказчика', 'rules' => 'required', 'type' => 'id')
            ),
			'editDocumentUcStorageWork' => array(
				array('field' => 'DocumentUcStorageWork_List', 'label' => 'Список нарядов', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DocumentUcTypeWork_id', 'label' => 'Вид работы', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_cid', 'label' => 'Заказчик', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Post_cid', 'label' => 'Должность заказчика', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_eid', 'label' => 'Заказчик', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Post_eid', 'label' => 'Должность заказчика', 'rules' => 'required', 'type' => 'id')
			),
            'deleteDocumentUcStorageWork' => array(
                array('field' => 'WhsDocumentUcInventDrug_List', 'label' => 'Список медикаментов', 'rules' => 'required', 'type' => 'string')
            ),
            'clearDrugFactKolvo' => array(
                array('field' => 'WhsDocumentUcInventDrug_List', 'label' => 'Список медикаментов', 'rules' => 'required', 'type' => 'string')
            ),
            'saveStorageWorkComment' => array(
                array('field' => 'DocumentUcStorageWork_id', 'label' => 'Идентификатор наряда', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DocumentUcStorageWork_Comment', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
            ),
            'loadWhsDocumentUcInventDrugInventoryNum' => array(
            	array(
            		'field'	=> 'WhsDocumentUcInventDrugInventory_id',
            		'label'	=> 'Идентификатор описи',
            		'rules'	=> 'required',
            		'type'	=> 'int'
            	)
            ),
            'loadWhsDocumentUcInventDrugInventoryNumList' => array(
            	array(
            		'field'	=> 'WhsDocumentUcInvent_id',
            		'label'	=> 'Идентификатор ведомости',
            		'rules'	=> '',
            		'type'	=> 'int'
            	)
            ),
            'getWhsDocumentUcInventDrugInventoryLastNum' => array(
            	array(
            		'field'	=> 'WhsDocumentUcInvent_id',
            		'label'	=> 'Идентификатор ведомости',
            		'rules'	=> '',
            		'type'	=> 'int'
            	)
            ),
            'saveWhsDocumentUcInventDrugInventoryNum' => array(
            	array(
            		'field'	=> 'WhsDocumentUcInventDrugInventory_id',
            		'label'	=> 'WhsDocumentUcInventDrugInventory_id',
            		'rules'	=> '',
            		'type'	=> 'int'
            	),
            	array(
            		'field'	=> 'WhsDocumentUcInvent_id',
            		'label'	=> 'Идентификатор ведомости',
            		'rules'	=> 'required',
            		'type'	=> 'int'
            	),
            	array(
            		'field'	=> 'WhsDocumentUcInventDrugInventory_InvNum',
            		'label'	=> 'Номер описи',
            		'rules'	=> 'required',
            		'type'	=> 'string'
            	)
            ),
            'getWhsDocumentUcInventNumbers'	=> array(
            	array(
            		'field'	=> 'WhsDocumentUcInvent_id',
            		'label'	=> 'Идентификатор ведомости',
            		'rules'	=> 'required',
            		'type'	=> 'int'
            	),
            	array(
            		'field'	=> 'WhsDocumentUcInventDrug_List',
            		'label'	=> 'Список медикаментов',
            		'rules'	=> '',
            		'type'	=> 'string'
            	)
            ),
			'assignWhsDocumentUcInventNumber' => array(
				array('field' => 'WhsDocumentUcInventDrug_List', 'label' => 'Список медикаментов', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'WhsDocumentUcInventDrug_InvNum', 'label' => 'Номер описи', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'WhsDocumentUcInventDrugInventory_id', 'label' => 'Идентификатор описи', 'rules' => 'required', 'type' => 'id')
			),
			'updateStatusDocumentUcInvent' => array(
				array(
            		'field'	=> 'WhsDocumentUcInvent_id',
            		'label'	=> 'Идентификатор ведомости',
            		'rules'	=> 'required',
            		'type'	=> 'int'
            	)
			),
			'loadLpuSectionCombo' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделеня', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка запроса из комбобокса', 'rules' => '', 'type' => 'string')
			),
			'loadStorageCombo' => array(
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedServiceStorage_id', 'label' => 'Идентификатор склада на котором прописана текущая служба', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделеня', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'OrgStruct_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка запроса из комбобокса', 'rules' => '', 'type' => 'string')
			)
		);
		$this->load->database();
		$this->load->model('WhsDocumentUcInvent_model', 'WhsDocumentUcInvent_model');
		//$this->load->model('ufa/Ufa_WhsDocumentUcInvent_model', 'WhsDocumentUcInvent_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			$response = $this->WhsDocumentUcInvent_model->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Специфика инвентаризационной ведомости')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение приказа на проведение инвентаризации
	 */
	function saveWhsDocumentUcInventOrder() {
		$data = $this->ProcessInputData('saveWhsDocumentUcInventOrder', true);
		if ($data){
			$save_response = $this->WhsDocumentUcInvent_model->saveObject('WhsDocumentUc', $data);
			$this->ProcessModelSave($save_response, true, 'Ошибка при сохранении Специфика инвентаризационной ведомости')->ReturnData();

			if (isset($this->OutData['WhsDocumentUc_id']) && $this->OutData['WhsDocumentUc_id'] > 0) {
				//сохранение списка госконтрактов
				if(!empty($data['WhsDocumentSupplyInventListJSON'])) {
					$res = $this->WhsDocumentUcInvent_model->saveWhsDocumentSupplyInventFromJSON(array(
						'WhsDocumentUc_id' => $this->OutData['WhsDocumentUc_id'],
						'WhsDocumentSupplyInventListJSON' => $data['WhsDocumentSupplyInventListJSON'],
						'pmUser_id' => $data['pmUser_id']
					));
				}

				//сохранение списка ведомостей
				if(!empty($data['WhsDocumentUcInventListJSON'])) {
					$res = $this->WhsDocumentUcInvent_model->saveWhsDocumentUcInventFromJSON(array(
						'WhsDocumentUc_id' => $this->OutData['WhsDocumentUc_id'],
						'WhsDocumentUcInventListJSON' => $data['WhsDocumentUcInventListJSON'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение фактического количества
	 */
	function saveDrugFactKolvo() {
		$data = $this->ProcessInputData('saveDrugFactKolvo', true);
		if ($data && $data['WhsDocumentUcInventDrug_id'] > 0){
			$response = $this->WhsDocumentUcInvent_model->saveDrugFactKolvo($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Специфика инвентаризационной ведомости')->ReturnData();
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
			$response = $this->WhsDocumentUcInvent_model->load($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных приказа на проведение инвентаризации
	 */
	function loadWhsDocumentUcInventOrder() {
		$data = $this->ProcessInputData('loadWhsDocumentUcInventOrder', true);
		if ($data){
			$response = $this->WhsDocumentUcInvent_model->loadWhsDocumentUcInventOrder($data);
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
			$session_data = getSessionParams();
			$orgtype = $session_data['session']['orgtype'];
			$region =  $session_data['session']['region']['nick'];
			if ($region == 'ufa' && $orgtype == 'farm') { 
				// Если Уфимская аптека ЛЛО
				$response = $this->WhsDocumentUcInvent_model->farm_loadList($filter);
			}
			else {
				$response = $this->WhsDocumentUcInvent_model->loadList($filter);
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка приказов на проведение инвентаризации
	 */
	function loadWhsDocumentUcInventOrderList() {
		$data = $this->ProcessInputData('loadWhsDocumentUcInventOrderList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentUcInvent_model->loadWhsDocumentUcInventOrderList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка госконтрактов
	 */
	function loadWhsDocumentSupplyInventList() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyInventList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentUcInvent_model->loadWhsDocumentSupplyInventList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка ведомостей
	 */
	function loadWhsDocumentUcInventList() {
		$data = $this->ProcessInputData('loadWhsDocumentUcInventList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentUcInvent_model->loadWhsDocumentUcInventList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка ведомостей
	 */
	function loadWhsDocumentUcInventDrugList() {
		$data = $this->ProcessInputData('loadWhsDocumentUcInventDrugList', true);
		if ($data) {
			$filter = $data;
			$session_data = getSessionParams();
			$orgtype = $session_data['session']['orgtype'];
			$region =  $session_data['session']['region']['nick'];
			if ($region == 'ufa' && $orgtype == 'farm') { 
				// Если Уфимская аптека ЛЛО
				$response = $this->WhsDocumentUcInvent_model->farm_loadWhsDocumentUcInventDrugList($filter);
			}
			else {
				$response = $this->WhsDocumentUcInvent_model->loadWhsDocumentUcInventDrugList($filter);
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка складов
	 */
	function loadStorageList() {
		$data = $this->ProcessInputData('loadStorageList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentUcInvent_model->loadStorageList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
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
			$response = $this->WhsDocumentUcInvent_model->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление приказа на проведение инвентаризации
	 */
	function deleteWhsDocumentUcInventOrder() {
		$data = $this->ProcessInputData('deleteWhsDocumentUcInventOrder', true, true);
		if ($data) {
			$response = $this->WhsDocumentUcInvent_model->deleteWhsDocumentUcInventOrder($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка организаций для комбобокса
	 */
	function loadOrgCombo() {
		$data = $this->ProcessInputData('loadOrgCombo', true);
		if ($data) {
			$response = $this->WhsDocumentUcInvent_model->loadOrgCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание списка инвентаризационных ведомостей
	 */
	function createDocumentUcInventList() {
		$data = $this->ProcessInputData('createDocumentUcInventList', true);
		if ($data) {
			$session_data = getSessionParams();
			$orgtype = $session_data['session']['orgtype'];
			$region =  $session_data['session']['region']['nick'];
			if ($region == 'ufa' && ($orgtype == 'farm' || $orgtype == 'reg_dlo')) {
				// Если Уфимская аптека ЛЛО
				$response = $this->WhsDocumentUcInvent_model->farm_createDocumentUcInventList($data);
			}
			else {
				$response = $this->WhsDocumentUcInvent_model->createDocumentUcInventList($data);
			};
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание списка медикаментов для инвентаризационной ведомости
	 */
	function createDocumentUcInventDrugList() {
		$data = $this->ProcessInputData('createDocumentUcInventDrugList', true);
		if ($data) {
			$session_data = getSessionParams();
			$orgtype = $session_data['session']['orgtype'];
			$region =  $session_data['session']['region']['nick'];
			if ($region == 'ufa' && ($orgtype == 'farm' || $orgtype == 'reg_dlo')) {
				// Если Уфимская аптека ЛЛО
				$response = $this->WhsDocumentUcInvent_model->farm_createDocumentUcInventDrugList($data);
			}
			else {
				$response = $this->WhsDocumentUcInvent_model->createDocumentUcInventDrugList($data);
			}
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание списка медикаментов для инвентаризационных ведомостей
	 */
	function createDocumentUcInventDrugListAllCommon() {
		$data = $this->ProcessInputData('createDocumentUcInventDrugListAllCommon', true);
		if ($data) {
			$response = $this->WhsDocumentUcInvent_model->createDocumentUcInventDrugListAllCommon($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Создание списка медикаментов для нескольких инвентаризационных ведомостей
	 */
	function createDocumentUcInventDrugListAll() {
		$data = $this->ProcessInputData('createDocumentUcInventDrugListAll', true);
		if ($data) {
			$response = $this->WhsDocumentUcInvent_model->createDocumentUcInventDrugListAll($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание дочернего документа для инвентаризационной ведомости
	 */
	function createDocumentUc() {
		$data = $this->ProcessInputData('createDocumentUc', true);
		if ($data) {
			$response = $this->WhsDocumentUcInvent_model->createDocumentUc($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Подписание приказа на инвентаризацию
	 */
	function signWhsDocumentUcInvent() {
		$data = $this->ProcessInputData('signWhsDocumentUcInvent', true);
		if ($data){
			$data['sign'] = ($data['sign'] == 'true');
			$response = $this->WhsDocumentUcInvent_model->signWhsDocumentUcInvent($data);
			$this->ProcessModelSave($response, true, 'Ошибка при '.'утверждении'.' инвентаризацонной ведомости')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Подписание приказа на инвентаризацию
	 */
	function signWhsDocumentUcInventOrder() {
		$data = $this->ProcessInputData('signWhsDocumentUcInventOrder', true);
		if ($data){
			$response = $this->WhsDocumentUcInvent_model->signWhsDocumentUcInventOrder($data);
			$this->ProcessModelSave($response, true, 'Ошибка при подписании приказа на проведение инвентаризации')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение сгенерированного уведомления
	 */
	function getNotice() {
		$data = $this->ProcessInputData('getNotice', true);
		if ($data){
			$response = $this->genNotice($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Генерация уведомлений связанных с заявками
	 */
	function genNotice($data) {
		$this->load->model('Messages_model', 'Messages_model');

		$res = array();
		$message_data = array();
		$recipient = array();
		$message_id = 0;
		$send = false;
		$header = '';
		$text = '';
		$send = true;

		// Получаем шаблоны для уведомления
		$header = 'Проведение инвентаризации';
		$text = 'На основании "{WhsDocumentUcInventOrder_Name}" по нижеследующим организациям и складам должна быть проведена инвентаризация: {Invent_Table} <br/>{Org_Name}';

		// Находим данные для уведомления
		$recipient = $this->WhsDocumentUcInvent_model->getRecipientForNotice($data);
		if(!$recipient) {
			return false;
		}

		// Находим данные для уведомления
		$notice_data = $this->WhsDocumentUcInvent_model->getDataForNotice($data);
		if(!$notice_data) {
			return false;
		} else {
			// Заполняем шаблоны даннами
			foreach($notice_data as $key => $value) {
				$text = preg_replace('/{'.$key.'}/', $value, $text);
			}
		}

		//print_r($recipient); die;

		// Формируем данные для сообщения
		$message_data['action'] = 'ins';
		$message_data['Message_id'] = null;
		$message_data['Message_pid'] = null;
		$message_data['pmUser_id'] = $data['session']['pmuser_id'];
		$message_data['Message_Subject'] = $header;
		$message_data['Message_Text'] = $text;
		$message_data['Message_isSent'] = $send ? 1 : null;
		$message_data['NoticeType_id'] = 1;
		$message_data['Message_isFlag'] = null;
		$message_data['Message_isDelete'] = null;
		$message_data['RecipientType_id'] = 1;
		$message_data['MessageRecipient_id'] = null;
		$message_data['Message_isRead'] = null;

		// Добавляем само сообщение
		$response = $this->Messages_model->insMessage($message_data);
		if(is_array($response) && strlen($response[0]['Error_Msg']) == 0) {
			$message_id = $response[0]['Message_id'];
		}

		// Если сообщение заинсертилось, т.е. существует его ид'шник, то добавляем связи для получателей
		if(isset($message_id)) {
			for($j=0; $j<count($recipient); $j++) {
				$res[$j] = $this->Messages_model->insMessageLink($message_id, $recipient[$j], $message_data);
				if(strlen($res[$j][0]['Error_Msg']) > 0) {
					break;
					DieWithError('Не удалось сохранить сообщение!');
					return false;
				}

				// Отправляем сообщение
				if ($send) {
					$this->Messages_model->sendMessage($message_data, $recipient[$j], $message_id);
				}
			}
		}

		$res['Message_Subject'] = $header;
		$res['Message_Text'] = $text;
		$res['Message_id'] = $message_id;

		return array($res);
	}

    /**
     * Ссоздание наряда на выполнение работ
     */
    function createDocumentUcStorageWork() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('createDocumentUcStorageWork', false);
        if (!empty($data)) {
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->WhsDocumentUcInvent_model->createDocumentUcStorageWork($data);
            $this->ProcessModelSave($response, true, 'Ошибка при создании наряда на выполнение работ')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Ссоздание наряда на выполнение работ
     */
    function editDocumentUcStorageWork() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('editDocumentUcStorageWork', false);
        if (!empty($data)) {
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->WhsDocumentUcInvent_model->editDocumentUcStorageWork($data);
            $this->ProcessModelSave($response, true, 'Ошибка при создании наряда на выполнение работ')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаление наряда на выполнение работ
     */
    function deleteDocumentUcStorageWork() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('deleteDocumentUcStorageWork', false);
        if (!empty($data)) {
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->WhsDocumentUcInvent_model->deleteDocumentUcStorageWork($data);
            $this->ProcessModelSave($response, true, 'Ошибка при удалении наряда на выполнение работ')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Очистка поля "Фактическое количество" в строках ведомости
     */
    function clearDrugFactKolvo() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('clearDrugFactKolvo', false);
        if (!empty($data)) {
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->WhsDocumentUcInvent_model->clearDrugFactKolvo($data);
            $this->ProcessModelSave($response, true, 'Ошибка при удалении информации о фактическом количестве')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Сохранение примечания в наряде на выполнение работы
	 */
    function saveStorageWorkComment() {
		$data = $this->ProcessInputData('saveStorageWorkComment');
		if (!empty($data)) {
			$response = $this->WhsDocumentUcInvent_model->saveObject('DocumentUcStorageWork', array(
				'DocumentUcStorageWork_id' => $data['DocumentUcStorageWork_id'],
				'DocumentUcStorageWork_Comment' => !empty($data['DocumentUcStorageWork_Comment'])?$data['DocumentUcStorageWork_Comment']:null,
				'pmUser_id' => $data['pmUser_id']
			));
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	* Получение номера описи
	*/
	function loadWhsDocumentUcInventDrugInventoryNum()
	{
		$data = $this->ProcessInputData('loadWhsDocumentUcInventDrugInventoryNum');
		if($data === false)
			return false;
		$response = $this->WhsDocumentUcInvent_model->loadWhsDocumentUcInventDrugInventoryNum($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	*	Получение списка описей
	*/
	function loadWhsDocumentUcInventDrugInventoryNumList() {
		$data = $this->ProcessInputData('loadWhsDocumentUcInventDrugInventoryNumList');
		if($data === false)
			return false;
		$response = $this->WhsDocumentUcInvent_model->loadWhsDocumentUcInventDrugInventoryNumList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*	Получение последнего номера описи
	*/
	function getWhsDocumentUcInventDrugInventoryLastNum() {
		$data = $this->ProcessInputData('getWhsDocumentUcInventDrugInventoryLastNum');
		if($data === false)
			return false;
		$response = $this->WhsDocumentUcInvent_model->getWhsDocumentUcInventDrugInventoryLastNum($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	*	Сохранение описи
	*/
	function saveWhsDocumentUcInventDrugInventoryNum()
	{
		$data = $this->ProcessInputData('saveWhsDocumentUcInventDrugInventoryNum',true);
		if($data === false)
			return false;
		$response = $this->WhsDocumentUcInvent_model->saveWhsDocumentUcInventDrugInventoryNum($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	*	getWhsDocumentUcInventNumbers
	*/
	function getWhsDocumentUcInventNumbers()
	{
		$data = $this->ProcessInputData('getWhsDocumentUcInventNumbers',true);
		if($data === false)
			return false;
		$response = $this->WhsDocumentUcInvent_model->getWhsDocumentUcInventNumbers($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Назначение номера описи выбранным медикаментам
	 */
	function assignWhsDocumentUcInventNumber(){
		$data = $this->ProcessInputData('assignWhsDocumentUcInventNumber',true);
		if($data === false)
			return false;
		$response = $this->WhsDocumentUcInvent_model->assignWhsDocumentUcInventNumber($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 * обновить статус ведомости
	 */
	function updateStatusDocumentUcInvent(){
		$data = $this->ProcessInputData('updateStatusDocumentUcInvent',true);
		if($data === false)
			return false;
		$response = $this->WhsDocumentUcInvent_model->updateStatusDocumentUcInvent($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка отделений для фильтрации списка инвентаризационных ведомостей
	 */
	function loadLpuSectionCombo() {
		$data = $this->ProcessInputData('loadLpuSectionCombo', false);
		if ($data == false) {
			return false;
		}
		$response = $this->WhsDocumentUcInvent_model->loadLpuSectionCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка складов для фильтрации списка инвентаризационных ведомостей
	 */
	function loadStorageCombo() {
		$data = $this->ProcessInputData('loadStorageCombo', false);
		if ($data == false) {
			return false;
		}
		$response = $this->WhsDocumentUcInvent_model->loadStorageCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}