<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для объектов Медикаменты
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version
*/

class WhsDocumentOrderAllocationDrug extends swController
{
	/**
	 *  Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'loadDrugRequestList' => array(
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
					'field' => 'begDate',
					'label' => 'Дата начала периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания периода',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'loadRAWList' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'int'
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
					'field' => 'begDate',
					'label' => 'Дата начала периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentOrderAllocation_Percent',
					'label' => 'Величина разнарядки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadRAWSupList' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
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
				)
			),
			'loadRAWFarmDrugList' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Распоряжение на выдачу разнарядки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRAWListMO' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'int'
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
					'field' => 'begDate',
					'label' => 'Дата начала периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentOrderAllocation_Percent',
					'label' => 'Величина разнарядки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор сводной заявки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRAWSupSpecList' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
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
				)
			),
			'loadRAWFarmDrugSpecList' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Распоряжение на выдачу разнарядки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadSupList' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadFarmDrugList' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadListMo' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadSupSpecList' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadFarmDrugSpecList' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadListSupplier' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentOrderAllocationDrugList' => array(
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'тип документа',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор договора',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentOrderAllocationDrugGrid' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Разнарядка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'start',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'limit',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 100
				)
			),
			'loadOrgLpuCombo' => array(
				array(
					'field' => 'query',
					'label' => 'Строка запроса из комбобокса',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadDrugOstatRegistryList' => array(
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'id'
				)
			)
		 );
		$this->load->database();
		$this->load->model('WhsDocumentOrderAllocationDrug_model', 'dbmodel');
	}
		
	/**
	 *  Загрузка списка содержимого разнарядки для формы поиска
	 */
	function loadWhsDocumentOrderAllocationDrugGrid() {
		$data = $this->ProcessInputData('loadWhsDocumentOrderAllocationDrugGrid', false); // сессию не надо.
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadWhsDocumentOrderAllocationDrugGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *  Загрузка списка заявок
	 */
	function loadDrugRequestList() {
		$data = $this->ProcessInputData('loadDrugRequestList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadDrugRequestList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Формирование первоначальной сводной разнарядки
	 */
	function loadRAWList() {
		$data = $this->ProcessInputData('loadRAWList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadRAWList($data);		
		foreach ($response as &$res) {
			$res['WhsDocumentOrderAllocationDrug_id'] = rand(100, 1000);
			$res['WhsDocumentUc_Sum'] = $res['WhsDocumentOrderAllocationDrug_Kolvo'] * $res['WhsDocumentOrderAllocationDrug_Price'];
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Формирование первоначального списка медикаментов, подлежащих распределению (план поставок)
	 */
	function loadRAWSupList() {
		$data = $this->ProcessInputData('loadRAWSupList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadRAWSupList($data);
		foreach ($response as &$res) {
			$res['WhsDocumentOrderAllocationDrug_id'] = rand(100, 1000);
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Формирование первоначального списка медикаментов, подлежащих распределению (распределение ЛС по аптекам)
	 */
	function loadRAWFarmDrugList() {
		$data = $this->ProcessInputData('loadRAWFarmDrugList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadRAWFarmDrugList($data);
		foreach ($response as &$res) {
			$res['WhsDocumentOrderAllocationDrug_id'] = rand(100, 1000);
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Формирование первоначальной разнарядки МО
	 */
	function loadRAWListMO() {
		$data = $this->ProcessInputData('loadRAWListMO', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadRAWListMO($data);		
		foreach ($response as &$res) {
			$res['WhsDocumentOrderAllocationDrug_id'] = rand(100, 1000);
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Формирование первоначального плана поставок
	 */
	function loadRAWSupSpecList() {
		$data = $this->ProcessInputData('loadRAWSupSpecList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadRAWSupSpecList($data);
		foreach ($response as &$res) {
			$res['WhsDocumentOrderAllocationDrug_id'] = rand(100, 1000);
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Формирование первоначальной спецификации распределения ЛС по аптекам
	 */
	function loadRAWFarmDrugSpecList() {
		$data = $this->ProcessInputData('loadRAWFarmDrugSpecList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadRAWFarmDrugSpecList($data);
		foreach ($response as &$res) {
			$res['WhsDocumentOrderAllocationDrug_id'] = rand(100, 1000);
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка сводной разнарядки
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка списка медикаментов, подлежащих распределению (план поставок)
	 */
	function loadSupList() {
		$data = $this->ProcessInputData('loadSupList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSupList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка списка медикаментов, подлежащих распределению (распределение ЛС по аптекам)
	 */
	function loadFarmDrugList() {
		$data = $this->ProcessInputData('loadFarmDrugList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadFarmDrugList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка разнарядки МО
	 */
	function loadListMo() {
		$data = $this->ProcessInputData('loadListMo', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadListMo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка плана поставок
	 */
	function loadSupSpecList() {
		$data = $this->ProcessInputData('loadSupSpecList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSupSpecList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка спецификации распределения ЛС по аптекам
	 */
	function loadFarmDrugSpecList() {
		$data = $this->ProcessInputData('loadFarmDrugSpecList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadFarmDrugSpecList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка списка поставщиков
	 */
	function loadListSupplier() {
		$data = $this->ProcessInputData('loadListSupplier', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadListSupplier($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Загрузка списка позиций разнарядки
	 */
	function loadWhsDocumentOrderAllocationDrugList() {
		$data = $this->ProcessInputData('loadWhsDocumentOrderAllocationDrugList', true);
		if ($data) {
			$response = $this->dbmodel->loadWhsDocumentOrderAllocationDrugList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка лпу для комбобокса (редактирование разнарядки МО)
	 */
	function loadOrgLpuCombo() {
		$data = $this->ProcessInputData('loadOrgLpuCombo', true);
		if ($data) {
			$filter = $data;
			$response = $this->dbmodel->loadOrgLpuCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка списка остатков для формы добавления записи в сводную разнарядку
	 */
	function loadDrugOstatRegistryList() {
		$data = $this->ProcessInputData('loadDrugOstatRegistryList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadDrugOstatRegistryList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}