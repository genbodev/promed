<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для объектов Распоряжение на включение/искючение из резерва
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version
*/

class WhsDocumentOrderReserve extends swController
{
    /**
     * Конструктор
     */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'sign' => array(
				array(
					'field' => 'WhsDocumentOrderReserve_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'save' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'WhsDocumentUc_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentOrderReserve_id',
					'label' => 'WhsDocumentOrderReserve_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentUc_Num',
					'label' => 'Номер документа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentUc_Name',
					'label' => 'Наименование документа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'Тип документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentUc_Date',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
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
					'field' => 'WhsDocumentOrderReserve_Percent',
					'label' => 'Величина резерва',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentUc_Sum',
					'label' => 'Сумма',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'ReserveDrugJSON',
					'label' => 'Строка спецификации',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
                    'field' => 'DrugRequest_id',
                    'label' => 'Сводная заявка',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
                    'field' => 'WhsDocumentSupply_id',
                    'label' => 'Контракт',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
                    'field' => 'Org_id',
                    'label' => 'Организация',
                    'rules' => '',
                    'type' => 'id'
                )
			),
			'load' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'WhsDocumentUc_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentUc_Date_Range',
					'label' => 'Период',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'Вид документа',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delete' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
            'loadConsolidatedDrugRequestCombo' => array(
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
            ),
            'loadWhsDocumentSupplyCombo' => array(
                array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор контракта', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор контракта', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => '', 'type' => 'id'),
                array('field' => 'Org_cid', 'label' => 'Организация заказчика', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugOstatRegistry_Org_id', 'label' => 'Организация содержащая остатки', 'rules' => '', 'type' => 'id'),
                array('field' => 'SubAccountType_SysNick', 'label' => 'Тип субсчета (ник)', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
            )
		 );
		$this->load->database();
		$this->load->model('WhsDocumentOrderReserve_model', 'dbmodel');
		$this->load->model('WhsDocumentOrderReserveDrug_model', 'WhsDocumentOrderReserveDrug_model');
	}

    /**
     * Исполнение
     */
	function sign() {
		$data = $this->ProcessInputData('sign', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->sign($data);
		$this->ProcessModelSave($response, true, 'Ошибка подписания договора')->ReturnData();
	}

    /**
     * Сохранение
     */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) {
			return false;
		}
		
		$response = $this->dbmodel->save($data);
		
		if (isset($response[0]['WhsDocumentUc_id']) && $response[0]['WhsDocumentUc_id'] > 0) {				
			$res = $this->WhsDocumentOrderReserveDrug_model->saveFromJSON(array(
				'WhsDocumentOrderReserve_id' => $response[0]['WhsDocumentUc_id'],
				'json_str' => $data['ReserveDrugJSON'],
				'pmUser_id' => $data['pmUser_id']
			));			
		}
		
		$this->ProcessModelSave($response, true, 'При сохранении данных произошла ошибка запроса к БД.')->ReturnData();
		return true;
	}

    /**
     * Загрузка
     */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}

    /**
     * Загрузка списка
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
     * Получение номера документа
     */
	function getWhsDocumentOrderReserveNumber() {
		$data = getSessionParams();

		$response = $this->dbmodel->getWhsDocumentOrderReserveNumber($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера документа')->ReturnData();
		
		return true;
	}

    /**
     * Удаление
     */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$response = $this->dbmodel->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Загрузка комбобокса для выбора сводной заявки
     */
    function loadConsolidatedDrugRequestCombo() {
        $data = $this->ProcessInputData('loadConsolidatedDrugRequestCombo', false);
        if ($data) {
            $response = $this->dbmodel->loadConsolidatedDrugRequestCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка комбобокса для выбора контракта
     */
    function loadWhsDocumentSupplyCombo() {
        $data = $this->ProcessInputData('loadWhsDocumentSupplyCombo', false);
        if ($data) {
            $response = $this->dbmodel->loadWhsDocumentSupplyCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
}