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

class WhsDocumentOrderReserveDrug extends swController
{
    /**
     * Конструктор
     */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'loadRAWList' => array(
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
					'field' => 'WhsDocumentOrderReserve_Percent',
					'label' => 'Величина резерва',
					'rules' => 'required',
					'type' => 'id'
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
				)
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentOrderReserve_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentOrderReserveDrugList' => array(
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
			)
		 );
		$this->load->database();
		$this->load->model('WhsDocumentOrderReserveDrug_model', 'dbmodel');
	}

    /**
     * Загрузка списка при формировании резерва
     */
	function loadRAWList() {
		$data = $this->ProcessInputData('loadRAWList', false);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadRAWList($data);		
		foreach ($response as &$res) {
			$res['WhsDocumentOrderReserveDrug_id'] = rand(100, 1000);
			$res['WhsDocumentUc_Sum'] = $res['WhsDocumentOrderReserveDrug_Kolvo'] * $res['WhsDocumentOrderReserveDrug_PriceNDS'];
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
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
     * Загрузка списка медикаментов
     */
	function loadWhsDocumentOrderReserveDrugList() {
		$data = $this->ProcessInputData('loadWhsDocumentOrderReserveDrugList', true);
		if ($data) {
			$response = $this->dbmodel->loadWhsDocumentOrderReserveDrugList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}