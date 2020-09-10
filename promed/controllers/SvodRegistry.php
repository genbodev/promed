<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Сводные Реестры
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       ModelGenerator
 * @version
 * @property SvodRegistry_model SvodRegistry_model
 */

class SvodRegistry extends swController
{
	/**
	 * Doc
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'тип реестра',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'идентификатор справочника ЛПУ',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_begDate',
					'label' => 'начало периода',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_endDate',
					'label' => 'окончание периода',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'KatNasel_id',
					'label' => 'категория населения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_Num',
					'label' => 'номер счета',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_accDate',
					'label' => 'дата счета',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'идентификатор статуса реестра',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_Sum',
					'label' => 'Registry_Sum',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Registry_IsActive',
					'label' => 'Registry_IsActive',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_ErrorCount',
					'label' => 'количество ошибок в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_ErrorCommonCount',
					'label' => 'Registry_ErrorCommonCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_RecordCount',
					'label' => 'количество записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OrgRSchet_id',
					'label' => 'идентификатор расчетного счета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_ExportPath',
					'label' => 'Registry_ExportPath',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_expDT',
					'label' => 'Registry_expDT',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegistryStacType_id',
					'label' => 'тип реестра стационара',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryEventType_id',
					'label' => 'Тип случаев реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_RecordPaidCount',
					'label' => 'Registry_RecordPaidCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_KdCount',
					'label' => 'Registry_KdCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_KdPaidCount',
					'label' => 'Registry_KdPaidCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_xmlExportPath',
					'label' => 'Registry_xmlExportPath',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_xmlExpDT',
					'label' => 'Registry_xmlExpDT',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegistryCheckStatus_id',
					'label' => 'Статус проверки реестра',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_Task',
					'label' => 'задание на обработку реестра',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'идентификатор подразделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Тип оплаты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_SumPaid',
					'label' => 'Реально оплаченная сумма реестра',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Registry_CheckStatusDate',
					'label' => 'Дата установления статуса в Промед',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_CheckStatusTFOMSDate',
					'label' => 'Дата проставления статуса в ТФОМС',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_sendDT',
					'label' => 'Дата отправки реестра',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_IsNeedReform',
					'label' => 'Признак необходимости переформирования реестра',
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
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'int'
				)
			),
			'load' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'тип реестра',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'идентификатор справочника ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_begDate',
					'label' => 'начало периода',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_endDate',
					'label' => 'окончание периода',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'KatNasel_id',
					'label' => 'категория населения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_Num',
					'label' => 'номер счета',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_accDate',
					'label' => 'дата счета',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'идентификатор статуса реестра',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_Sum',
					'label' => 'Registry_Sum',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Registry_IsActive',
					'label' => 'Registry_IsActive',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_ErrorCount',
					'label' => 'количество ошибок в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_ErrorCommonCount',
					'label' => 'Registry_ErrorCommonCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_RecordCount',
					'label' => 'количество записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OrgRSchet_id',
					'label' => 'идентификатор расчетного счета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_ExportPath',
					'label' => 'Registry_ExportPath',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_expDT',
					'label' => 'Registry_expDT',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegistryStacType_id',
					'label' => 'тип реестра стационара',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryEventType_id',
					'label' => 'Тип случаев реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_RecordPaidCount',
					'label' => 'Registry_RecordPaidCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_KdCount',
					'label' => 'Registry_KdCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_KdPaidCount',
					'label' => 'Registry_KdPaidCount',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_xmlExportPath',
					'label' => 'Registry_xmlExportPath',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_xmlExpDT',
					'label' => 'Registry_xmlExpDT',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'RegistryCheckStatus_id',
					'label' => 'Статус проверки реестра',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_Task',
					'label' => 'задание на обработку реестра',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'идентификатор подразделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Тип оплаты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_SumPaid',
					'label' => 'Реально оплаченная сумма реестра',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Registry_CheckStatusDate',
					'label' => 'Дата установления статуса в Промед',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_CheckStatusTFOMSDate',
					'label' => 'Дата проставления статуса в ТФОМС',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_sendDT',
					'label' => 'Дата отправки реестра',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Registry_IsNeedReform',
					'label' => 'Признак необходимости переформирования реестра',
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
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_Date',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Registry_insDT',
					'label' => 'Дата экспертизы',
					'rules' => '',
					'type' => 'date'
				)
			),
			'delete' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadRegistryDataReceptList' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadReceptStatusFLKMEKList' => array(
				array(
					'field' => 'loadReceptStatusFLKMEKList_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				)
			),
			'setReceptIsReceived' => array(
				array(
					'field' => 'RegistryDataRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'IsReceived',
					'label' => 'Получен',
					'rules' => '',
					'type' => 'string'
				)
			),
			'setAllReceptsIsReceived' => array(
				array(
					'field' => 'RegistryDataReceptList',
					'label' => 'Список идентификаторов рецептов',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'IsReceived',
					'label' => 'Получен',
					'rules' => '',
					'type' => 'string'
				)
			)
		);
		$this->load->database();
		$this->load->model('SvodRegistry_model', 'SvodRegistry_model');
	}
	/**
	 * Doc
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['Registry_id'])) {
				$this->SvodRegistry_model->setRegistry_id($data['Registry_id']);
			}
			if (isset($data['RegistryType_id'])) {
				$this->SvodRegistry_model->setRegistryType_id($data['RegistryType_id']);
			}
			if (isset($data['Lpu_id'])) {
				$this->SvodRegistry_model->setLpu_id($data['Lpu_id']);
			}
			if (isset($data['Registry_begDate'])) {
				$this->SvodRegistry_model->setRegistry_begDate($data['Registry_begDate']);
			}
			if (isset($data['Registry_endDate'])) {
				$this->SvodRegistry_model->setRegistry_endDate($data['Registry_endDate']);
			}
			if (isset($data['KatNasel_id'])) {
				$this->SvodRegistry_model->setKatNasel_id($data['KatNasel_id']);
			}
			if (isset($data['Registry_Num'])) {
				$this->SvodRegistry_model->setRegistry_Num($data['Registry_Num']);
			}
			if (isset($data['Registry_accDate'])) {
				$this->SvodRegistry_model->setRegistry_accDate($data['Registry_accDate']);
			}
			if (isset($data['RegistryStatus_id'])) {
				$this->SvodRegistry_model->setRegistryStatus_id($data['RegistryStatus_id']);
			}
			if (isset($data['Registry_Sum'])) {
				$this->SvodRegistry_model->setRegistry_Sum($data['Registry_Sum']);
			}
			if (isset($data['Registry_IsActive'])) {
				$this->SvodRegistry_model->setRegistry_IsActive($data['Registry_IsActive']);
			}
			if (isset($data['Registry_ErrorCount'])) {
				$this->SvodRegistry_model->setRegistry_ErrorCount($data['Registry_ErrorCount']);
			}
			if (isset($data['Registry_ErrorCommonCount'])) {
				$this->SvodRegistry_model->setRegistry_ErrorCommonCount($data['Registry_ErrorCommonCount']);
			}
			if (isset($data['Registry_RecordCount'])) {
				$this->SvodRegistry_model->setRegistry_RecordCount($data['Registry_RecordCount']);
			}
			if (isset($data['OrgRSchet_id'])) {
				$this->SvodRegistry_model->setOrgRSchet_id($data['OrgRSchet_id']);
			}
			if (isset($data['Registry_ExportPath'])) {
				$this->SvodRegistry_model->setRegistry_ExportPath($data['Registry_ExportPath']);
			}
			if (isset($data['Registry_expDT'])) {
				$this->SvodRegistry_model->setRegistry_expDT($data['Registry_expDT']);
			}
			if (isset($data['RegistryStacType_id'])) {
				$this->SvodRegistry_model->setRegistryStacType_id($data['RegistryStacType_id']);
			}
			if (isset($data['RegistryEventType_id'])) {
				$this->SvodRegistry_model->setRegistryEventType_id($data['RegistryEventType_id']);
			}
			if (isset($data['Registry_RecordPaidCount'])) {
				$this->SvodRegistry_model->setRegistry_RecordPaidCount($data['Registry_RecordPaidCount']);
			}
			if (isset($data['Registry_KdCount'])) {
				$this->SvodRegistry_model->setRegistry_KdCount($data['Registry_KdCount']);
			}
			if (isset($data['Registry_KdPaidCount'])) {
				$this->SvodRegistry_model->setRegistry_KdPaidCount($data['Registry_KdPaidCount']);
			}
			if (isset($data['Registry_xmlExportPath'])) {
				$this->SvodRegistry_model->setRegistry_xmlExportPath($data['Registry_xmlExportPath']);
			}
			if (isset($data['Registry_xmlExpDT'])) {
				$this->SvodRegistry_model->setRegistry_xmlExpDT($data['Registry_xmlExpDT']);
			}
			if (isset($data['RegistryCheckStatus_id'])) {
				$this->SvodRegistry_model->setRegistryCheckStatus_id($data['RegistryCheckStatus_id']);
			}
			if (isset($data['Registry_Task'])) {
				$this->SvodRegistry_model->setRegistry_Task($data['Registry_Task']);
			}
			if (isset($data['LpuBuilding_id'])) {
				$this->SvodRegistry_model->setLpuBuilding_id($data['LpuBuilding_id']);
			}
			if (isset($data['PayType_id'])) {
				$this->SvodRegistry_model->setPayType_id($data['PayType_id']);
			}
			if (isset($data['Registry_SumPaid'])) {
				$this->SvodRegistry_model->setRegistry_SumPaid($data['Registry_SumPaid']);
			}
			if (isset($data['Registry_CheckStatusDate'])) {
				$this->SvodRegistry_model->setRegistry_CheckStatusDate($data['Registry_CheckStatusDate']);
			}
			if (isset($data['Registry_CheckStatusTFOMSDate'])) {
				$this->SvodRegistry_model->setRegistry_CheckStatusTFOMSDate($data['Registry_CheckStatusTFOMSDate']);
			}
			if (isset($data['Registry_sendDT'])) {
				$this->SvodRegistry_model->setRegistry_sendDT($data['Registry_sendDT']);
			}
			if (isset($data['Registry_IsNeedReform'])) {
				$this->SvodRegistry_model->setRegistry_IsNeedReform($data['Registry_IsNeedReform']);
			}
			if (isset($data['DrugFinance_id'])) {
				$this->Registry_model->setDrugFinance_id($data['DrugFinance_id']);
			}
			if (isset($data['WhsDocumentCostItemType_id'])) {
				$this->Registry_model->setWhsDocumentCostItemType_id($data['WhsDocumentCostItemType_id']);
			}
			if (isset($data['Org_id'])) {
				$this->Registry_model->setOrg_id($data['Org_id']);
			}
			$response = $this->SvodRegistry_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Реестры')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Doc
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->SvodRegistry_model->setRegistry_id($data['Registry_id']);
			$response = $this->SvodRegistry_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Doc
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->SvodRegistry_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Doc
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->SvodRegistry_model->setRegistry_id($data['Registry_id']);
			$response = $this->SvodRegistry_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Doc
	 */
	function loadRegistryDataReceptList() {
		$data = $this->ProcessInputData('loadRegistryDataReceptList', true);
		if ($data) {
			$filter = $data;
			$response = $this->SvodRegistry_model->loadRegistryDataReceptList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Doc
	 */
	function loadReceptStatusFLKMEKList() {
		$data = $this->ProcessInputData('loadReceptStatusFLKMEKList', true);
		if ($data) {
			$filter = $data;
			$response = $this->SvodRegistry_model->loadReceptStatusFLKMEKList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Doc
	 */
	function setReceptIsReceived() {
		$data = $this->ProcessInputData('setReceptIsReceived', true);
		if ($data){
			$response = $this->SvodRegistry_model->setReceptIsReceived($data);
			//$this->Cancel_Error_Handle = true;
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении рецепта')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Doc
	 */
	function setAllReceptsIsReceived() {
		$data = $this->ProcessInputData('setAllReceptsIsReceived', true);
		if ($data){
			$response = $this->SvodRegistry_model->setAllReceptsIsReceived($data);

			$this->ProcessModelSave($response, true, 'Ошибка при сохранении рецепта')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}