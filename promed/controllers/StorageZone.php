<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * 
 */

class StorageZone extends swController {
	public $inputRules = array(
		'loadStorageZoneTree' => array(
			array(
				'default' => 0,
				'field' => 'level',
				'label' => 'Уровень',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'StorageZone_pid',
				'label' => 'Идентификатор родительского объекта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'mode',
				'label' => 'режим загрузки',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveStorageZone' => array(
			array(
				'field' => 'StorageZone_pid',
				'label' => 'Размещение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Склад',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StorageZone_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'StorageZone_IsPKU',
				'label' => 'ПКУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageZone_IsMobile',
				'label' => 'Мобильное',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageUnitType_id',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'TempConditionType_id',
				'label' => 'Температурный режим',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageZone_begDate',
				'label' => 'Дата начала периода действия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'StorageZone_endDate',
				'label' => 'Дата окончания периода действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'StorageZone_AdditionalInfo',
				'label' => 'Примечание',
				'rules' => '',
				'type' => 'string'
			)
		),
		'deleteStorageZone' => array(
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadStorageZoneList' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Склад',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_id',
				'label' => 'Структурный уровень организации',
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
				'field' => 'exceptStorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'withStorageOnly',
				'label' => 'Флаг - только со складом',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadStorageZone' => array(
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadDrugGrid' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Склад',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Without_sz',
				'label' => 'Флаг пункта Без места хранения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_Name',
				'label' => 'МНН',
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
				'field' => 'DrugFinance_id',
				'label' => 'Финансирование',
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
				'field' => 'GoodsUnit_id',
				'label' => 'Ед.уч.',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadAllDrugGrid' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Склад',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_Name',
				'label' => 'МНН',
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
				'field' => 'DrugFinance_id',
				'label' => 'Финансирование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'WhsDocumentCostItemType_id',
				'label' => 'Статья расхода',
				'rules' => '',
				'type' => 'id'
			),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>100),
		),
		'loadAllDrugStorageGrid' => array(
			array(
				'field' => 'DrugOstatRegistry_ids',
				'label' => 'Идентификаторы регистра остатков',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugShipment_id',
				'label' => 'Идентификатор партии',
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
				'field' => 'PrepSeries_id',
				'label' => 'Идентификатор серии',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Склад',
				'rules' => '',
				'type' => 'id'
			),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>100),
		),
		'loadStorageDrugMoveGrid' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Склад',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_Name',
				'label' => 'МНН',
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
				'field' => 'DrugFinance_id',
				'label' => 'Финансирование',
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
				'field' => 'begDate',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Конец периода',
				'rules' => '',
				'type' => 'date'
			),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>100),
		),
		'loadStorageDrugMoveList' => array(
			array(
				'field' => 'Drug_id',
				'label' => 'Идентификатор медикамента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => '',
				'type' => 'id'
			),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>100),
		),
		'loadStorageDocSupplyList' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			)
		),
		'findStorageDocSupplyLink' => array(
			array(
				'field' => 'WhsDocumentSupply_id',
				'label' => 'Идентификатор контракта',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveStorageDocSupplyLink' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'WhsDocumentSupply_id',
				'label' => 'Идентификатор контракта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StorageDocSupplyLink_id',
				'label' => 'Идентификатор связи',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deleteStorageDocSupplyLink' => array(
			array(
				'field' => 'StorageDocSupplyLink_id',
				'label' => 'Идентификатор связи склада и контракта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'moveDrugsToStorageZone' => array(
			array(
				'field' => 'record_ids',
				'label' => 'Идентификаторы медикаментов',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'drugostatreg_ids',
				'label' => 'Идентификаторы медикаментов по регистру',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'giveStorageZoneToPerson' => array(
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StorageZoneLiable_ObjectId',
				'label' => 'Идентификатор подотчетного лица',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugDocumentType_id',
				'label' => 'Идентификатор типа документа учета',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'takeStorageZoneFromPerson' => array(
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugDocumentType_id',
				'label' => 'Идентификатор типа документа учета',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getBrigadesForGiveStorageZoneToPerson' => array(
			array(
				'field' => 'StorageZone_id',
				'label' => 'Идентификатор места хранения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подстанции',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('StorageZone_model', 'dbmodel');
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
					'text' => $rows[$field['name']],
					'leaf' => $rows['leaf'],
					'Storage_id' => $rows['Storage_id'],
					'Org_id' => (!empty($rows['Org_id'])?$rows['Org_id']:null),
					'LpuBuilding_id' => (!empty($rows['LpuBuilding_id'])?$rows['LpuBuilding_id']:null),
					'LpuSection_id' => (!empty($rows['LpuSection_id'])?$rows['LpuSection_id']:null),
					'isMobile' => ((!empty($rows['StorageZone_IsMobile']) && $rows['StorageZone_IsMobile'] == 2)?1:null),
					'Liable_Object' => (!empty($rows['Liable_Object'])?$rows['Liable_Object']:null),
					'Liable_ObjectId' => (!empty($rows['Liable_ObjectId'])?$rows['Liable_ObjectId']:null),
					'without_sz' => (!empty($rows['without_sz'])?$rows['without_sz']:0),
					'Comment' => (!empty($rows['Comment'])?$rows['Comment']:''),
					'iconCls' => (empty($rows['iconCls']) ? $field['iconCls'] : $rows['iconCls']),
					'cls' => $field['cls']
				);

				if(!empty($rows[$field['id']])){
					$node['qtip'] = (!empty($rows['Comment'])?$rows['Comment']:'');
				}

				if(!($level > 0 && $node['id'] == 0 && $node['without_sz'] == 0)){
					$val[] = $node;
				}
			}
		}

		return $val;
	}
	
	/**
	 *	Функция читает ветку дерева
	 */
	function loadStorageZoneTree() {
		$data = $this->ProcessInputData('loadStorageZoneTree', true);
		if ( $data === false ) { return false; }

		/*if ($data['level'] == 0) {
			if (!empty($data['mode']) && $data['mode'] == 'common') {
				$data['PrepClass_Code'] = 1;
			}
		}*/
		$response = $this->dbmodel->loadStorageZoneTree($data);
		$this->ProcessModelList($response, true, true);

		// Обработка для дерева 
		$field = array(
			'id' => 'id', 
			'name' => 'name',
			'code' => 'code',
			'iconCls' => 'storage-tree-parent16',
			'leaf' => false, 
			'cls' => 'folder'
		);
		if($data['level'] > 0){
			$field['iconCls'] = 'storage-tree-child16';
		}

		$this->ReturnData($this->getTreeNodes($this->OutData, $field, $data['level'], ""));

		return true;
	}

	/**
	 *	Сохранение места хранения
	 */
	function saveStorageZone()
	{
		$data = $this->ProcessInputData('saveStorageZone', true);
		if ( $data === false ) { return false; }

		//старт транзакции
		$this->dbmodel->beginTransaction();

		$checkHierarchy = $this->dbmodel->checkStorageZoneHierarchy($data);
		if(!empty($checkHierarchy['Error_Msg'])){
			$this->dbmodel->rollbackTransaction();
			$this->ProcessModelSave($checkHierarchy, true)->ReturnData();
			return false;
		}
		$address = $this->dbmodel->formStorageZoneAddress($data);
		if(!empty($address['Error_Msg'])){
			$this->dbmodel->rollbackTransaction();
			$this->ProcessModelSave($address, true)->ReturnData();
			return false;
		} else {
			$data['StorageZone_Address'] = $address;
		}
		$response = $this->dbmodel->saveStorageZone($data);
		if(!empty($response[0]['StorageZone_id']) && empty($response[0]['Error_Msg'])){
			$res = $this->dbmodel->updateChildsStorageZoneAddress(array('StorageZone_id'=>$response[0]['StorageZone_id'],'pmUser_id'=>$data['pmUser_id']));
			if(!empty($res['Error_Msg'])){
				$this->dbmodel->rollbackTransaction();
				$this->ProcessModelSave($res, true)->ReturnData();
				return false;
			}

			$this->dbmodel->commitTransaction();
		} else {
			$this->dbmodel->rollbackTransaction();
		}

		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Удаление места хранения
	 */
	function deleteStorageZone()
	{
		$data = $this->ProcessInputData('deleteStorageZone', true);
		if ( $data === false ) { return false; }

		$check = $this->dbmodel->checkStorageZoneJournal($data);
		if( is_array($check) && count($check) > 0 ){
			$error = array('Error_Msg'=>'Выбранное место хранения включено в журнал операций, его удаление не возможно. Место хранения можно закрыть.');
			$this->ProcessModelSave($error, true)->ReturnData();
			return false;
		}
		$childs = $this->dbmodel->checkStorageZoneChilds($data);
		if( is_array($childs) && count($childs) > 0 ){
			$error = array('Error_Msg'=>'У выбранного места хранения есть дочерние места хранения. Удаление невозможно.');
			$this->ProcessModelSave($error, true)->ReturnData();
			return false;
		}
		
		$response = $this->dbmodel->deleteStorageZone($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Получение списка мест хранения
	 */
	function loadStorageZoneList()
	{
		$data = $this->ProcessInputData('loadStorageZoneList', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadStorageZoneList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Загрузка места хранения
	 */
	function loadStorageZone()
	{
		$data = $this->ProcessInputData('loadStorageZone', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadStorageZone($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение списка медикаментов
	 */
	function loadDrugGrid()
	{
		$data = $this->ProcessInputData('loadDrugGrid', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadDrugGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение списка медикаментов для вкладки "По медикаментам"
	 */
	function loadAllDrugGrid()
	{
		$data = $this->ProcessInputData('loadAllDrugGrid', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadAllDrugGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение списка мест хранения медикамента для вкладки "По медикаментам"
	 */
	function loadAllDrugStorageGrid()
	{
		$data = $this->ProcessInputData('loadAllDrugStorageGrid', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadAllDrugStorageGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение Журнала перемещений
	 */
	function loadStorageDrugMoveGrid()
	{
		$data = $this->ProcessInputData('loadStorageDrugMoveGrid', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadStorageDrugMoveGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение выписки из Журнала перемещений
	 */
	function loadStorageDrugMoveList()
	{
		$data = $this->ProcessInputData('loadStorageDrugMoveList', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadStorageDrugMoveList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение списка контрактов, связанных со складом
	 */
	function loadStorageDocSupplyList()
	{
		$data = $this->ProcessInputData('loadStorageDocSupplyList', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadStorageDocSupplyList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Получение склада, связанного со контрактом
	 */
	function findStorageDocSupplyLink()
	{
		$data = $this->ProcessInputData('findStorageDocSupplyLink', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->findStorageDocSupplyLink($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Сохранение связи склада и контракта
	 */
	function saveStorageDocSupplyLink()
	{
		$data = $this->ProcessInputData('saveStorageDocSupplyLink', true);
		if ( $data === false ) { return false; }
		if(empty($data['StorageDocSupplyLink_id'])){
			$resp = $this->dbmodel->findStorageDocSupplyLink($data);
			if(is_array($resp) && count($resp) > 0){
				$response = array(
					'AlertMsg'=>'Контракт № '.$resp[0]['WhsDocumentUc_Num'].' от '.$resp[0]['WhsDocumentUc_Date'].' связан со складом '.$resp[0]['Storage_Name'].'. Вы точно желаете изменить склад контракта?',
					'StorageDocSupplyLink_id'=>$resp[0]['StorageDocSupplyLink_id']
				);
				$this->ReturnData($response);
				return false;
			}
		}
		$response = $this->dbmodel->saveStorageDocSupplyLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Удаление связи склада и контракта
	 */
	function deleteStorageDocSupplyLink()
	{
		$data = $this->ProcessInputData('deleteStorageDocSupplyLink', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->deleteStorageDocSupplyLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Сохранение связи склада и контракта
	 */
	function moveDrugsToStorageZone()
	{
		$data = $this->ProcessInputData('moveDrugsToStorageZone', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->moveDrugsToStorageZone($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Передача на подотчет
	 */
	function giveStorageZoneToPerson()
	{
		$data = $this->ProcessInputData('giveStorageZoneToPerson', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->giveStorageZoneToPerson($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Принятие с подотчета
	 */
	function takeStorageZoneFromPerson()
	{
		$data = $this->ProcessInputData('takeStorageZoneFromPerson', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->takeStorageZoneFromPerson($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Получение списка бригад для передачи места хранения на подотчет
	 */
	function getBrigadesForGiveStorageZoneToPerson()
	{
		$data = $this->ProcessInputData('getBrigadesForGiveStorageZoneToPerson', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->getBrigadesForGiveStorageZoneToPerson($data);
		$this->ProcessModelList($response, true)->ReturnData();
	}
}