<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * MorbusACS - контроллер для MorbusOrphan
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       
 * @version      10.2012
 * @property MorbusACS_model dbmodel
 */
class MorbusACS extends swController {

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	var $inputRules = array(
		'loadACSGrid' => array(
			array('field' => 'Person_id', 'label' => '', 'rules' => '', 'type' => 'id')
		),
		'loadMorbusACSEditWindow' => array(
			array('field' => 'MorbusACS_id', 'label' => '', 'rules' => '', 'type' => 'id')
		),
		'saveMorbusACSEditWindow' => array(
			array('field' => 'Morbus_setDT', 'label' => '', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Morbus_disDT', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'MorbusACS_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Morbus_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusBase_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_did', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusACS_Comment', 'label' => '', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'MorbusACS_Result', 'label' => '', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'MorbusACS_TimeDesease', 'label' => '', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'MorbusACS_isCoronary', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isFCSSH', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isLpu', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isPso', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isST', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isTinaki', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isTransderm', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isTrombPrehosp', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusACS_isTrombStac', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'PrehospArrive_id', 'label' => '', 'rules' => '', 'type' => 'int')
		),
		'getACSDiag' => array(
			array('field' => 'CrazyDiag_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id','label' => 'МКБ-10','rules' => '', 'type' => 'id'),
			array('field' => 'Diag_Code','label' => 'Код','rules' => '', 'type' => 'string'),
			array('field' => 'Diag_Name','label' => 'Наименование','rules' => '', 'type' => 'string'),
			array('field' => 'query','label' => 'Запрос','rules' => '', 'type' => 'string'),
			array('field' => 'type','label'=>'тип регистра', 'rules'=>'','type'=>'string')

		)
	);

	/**
	 * dsf
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('MorbusACS_model', 'dbmodel');
	}

	/**
	 * Функция возвращает в XML список регионального сегмента регистра по орфанным заболеваниям
	 */
	function loadACSGrid() {
		$data = $this->ProcessInputData('loadACSGrid', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadACSGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *
	 * @return type 
	 */
	function loadMorbusACSEditWindow() {
		$data = $this->ProcessInputData('loadMorbusACSEditWindow', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadMorbusACSEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function deleteMorbusACS(){
		$data = $this->ProcessInputData('loadMorbusACSEditWindow', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->deleteMorbusACS($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function saveMorbusACSEditWindow() {
		$data = $this->ProcessInputData('saveMorbusACSEditWindow', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->saveMorbusACSEditWindow($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение списка диагнозов по ОКС
	 */
	function getACSDiag() {
		$data = $this->ProcessInputData('getACSDiag', true);
		if ($data) {
			$response = $this->dbmodel->getACSDiag($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


}