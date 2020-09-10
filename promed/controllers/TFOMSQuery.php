<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TFOMS - ТФОМС запросы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sobenin Alex aka GTP_fox
 * @version			24.10.2018
 */
class TFOMSQuery extends swController{
	public $inputRules = array(
		'loadTFOMSQueryStatusList' => array(
			array('field' => 'forMO','label' => 'Список для МО','rules' => '','type' => 'string'),
		),
		'loadTFOMSQueryList' => array(
			array('field' => 'TFOMSQueryEMK_insDT', 'label' => 'Дата формирования', 'rules' => '', 'type' => 'daterange'),
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'TFOMSQueryStatus_id', 'label' => 'Статус запроса', 'rules' => '', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'limit','label' => '', 'rules' => 'required','type' => 'int'),
			array('field' => 'forMO','label' => 'Список для МО','rules' => '','type' => 'string'),
			array('field' => 'noCount','label' => 'Флаг количества','rules' => '','type' => 'string'),
		),
		'saveTFOMSQuery' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'ТФОМС/ОМС', 'rules' => '', 'type' => 'id'),
			array('field' => 'TFOMSQueryStatus_id', 'label' => 'Идентификатор статуса запроса', 'rules' => '', 'type' => 'id'),
			array('field' => 'TFOMSQueryEMK_begDate','label' => 'Период доступа с','rules' => 'trim','type' => 'date'),
			array('field' => 'TFOMSQueryEMK_endDate','label' => 'Период доступа по','rules' => 'trim','type' => 'date')
		),
		'loadTFOMSQueryPersonList' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => '', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'limit', 'label' => '', 'rules' => 'required', 'type' => 'int'),
		),
		'addPersonToQuery' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'ТФОМС/ОМС', 'rules' => '', 'type' => 'id')
		),
		'deletePersonFromQuery' => array(
			array('field' => 'TFOMSQueryPerson_id', 'label' => 'Идентификатор записи человека в запросе', 'rules' => 'required', 'type' => 'id')
		),
		'deleteQuery' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id')
		),
		'setTFOMSQuery' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TFOMSQueryStatus_id', 'label' => 'Идентификатор статуса запроса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'TFOMSQueryEMK_begDate','label' => 'Период доступа с','rules' => 'trim','type' => 'date'),
			array('field' => 'TFOMSQueryEMK_endDate','label' => 'Период доступа по','rules' => 'trim','type' => 'date')
		),
		'setAccessPerson' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TFOMSQueryPerson_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TFOMSQueryPerson_isAccess','label' => 'Флаг доступа','rules' => '','type' => 'int'),
			array('field' => 'TFOMSQueryStatus_id', 'label' => 'Идентификатор статуса запроса', 'rules' => '', 'type' => 'id'),
		),
		'setViewPersonStatus' => array(
			array('field' => 'TFOMSQueryPerson_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
		),
		'closeEMKAccess' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id')
		),
		'setAccessAllPerson' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TFOMSQueryPerson_isAccess','label' => 'Флаг доступа','rules' => '','type' => 'int'),
			array('field' => 'TFOMSQueryStatus_id', 'label' => 'Идентификатор статуса запроса', 'rules' => '', 'type' => 'id')
		),
		'openAccessQuery' => array(
			array('field' => 'TFOMSQueryEMK_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id')
		),
	);

	/**
	 * TFOMSQuery constructor.
	 */
	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('TFOMSQuery_model','dbmodel');
	}
	/**
	 * Сохранение запроса
	 */
	function saveTFOMSQuery()
	{
		$data = $this->ProcessInputData('saveTFOMSQuery');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveTFOMSQuery($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Получение списка пакетов
	 */
	function loadTFOMSQueryStatusList(){
		$data = $this->ProcessInputData('loadTFOMSQueryStatusList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTFOMSQueryStatusList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Получение списка запросов
	 */
	function loadTFOMSQueryList(){
		$data = $this->ProcessInputData('loadTFOMSQueryList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTFOMSQueryList($data);
		if(!empty($data['noCount']))
			$this->ProcessModelList($response, true, true)->ReturnData();
		else
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Получение списка запросов
	 */
	function loadTFOMSQueryPersonList(){
		$data = $this->ProcessInputData('loadTFOMSQueryPersonList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTFOMSQueryPersonList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Добавление человека к запросу
	 */
	function addPersonToQuery()
	{
		$data = $this->ProcessInputData('addPersonToQuery');
		if ($data === false) { return false; }

		$response = $this->dbmodel->addPersonToQuery($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Удаление человека из запроса
	 */
	function deletePersonFromQuery()
	{
		$data = $this->ProcessInputData('deletePersonFromQuery');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePersonFromQuery($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Удаление запроса
	 */
	function deleteQuery()
	{
		$data = $this->ProcessInputData('deleteQuery');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteQuery($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Изменение запроса
	 */
	function setTFOMSQuery()
	{
		$data = $this->ProcessInputData('setTFOMSQuery');
		if ($data === false) { return false; }
		$params = array(
			'TFOMSQueryEMK_id' => $data['TFOMSQueryEMK_id'],
			'noCount' => true
		);
		$response = $this->dbmodel->loadTFOMSQueryList($params,120); // Второй параметр - формат даты
		// Дополняем данные с клиента сохраненными в БД
		$paramsArr = array('Lpu_id','Org_id','TFOMSQueryStatus_id','TFOMSQueryEMK_begDate','TFOMSQueryEMK_endDate');
		foreach($paramsArr as $p){
			if(!empty($data[$p]))
				$params[$p] = $data[$p];
			else
				$params[$p] = !empty($response[0][$p])?$response[0][$p]:null;
		}
		$params['pmUser_id'] = $data['pmUser_id'];

		$response = $this->dbmodel->saveTFOMSQuery($params);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Сохранение запроса
	 */
	function setAccessPerson()
	{
		$data = $this->ProcessInputData('setAccessPerson');
		if ($data === false) { return false; }

		$response = $this->dbmodel->setAccessPerson($data);
		$status = $this->dbmodel->checkQueryAccessAll($data);
		if(!empty($data['TFOMSQueryStatus_id']) && $status && $data['TFOMSQueryStatus_id'] != $status){
			$data['TFOMSQueryStatus_id'] = $status;
			$response = $this->dbmodel->setQueryStatus($data);
			$response[0]['setStatus'] = true;
		}
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Сохранение запроса
	 */
	function closeEMKAccess()
	{
		$data = $this->ProcessInputData('closeEMKAccess');
		if ($data === false) { return false; }

		$response = $this->dbmodel->closeEMKAccess($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Сохранение запроса
	 */
	function setAccessAllPerson()
	{
		$data = $this->ProcessInputData('setAccessAllPerson');
		if ($data === false) { return false; }

		$response = $this->dbmodel->setAccessAllPerson($data);
		if($data['TFOMSQueryStatus_id'] != 2){
			if($data['TFOMSQueryPerson_isAccess']==2) // Если предоставляем доступ ко всем ЭМК пациентов
				$data['TFOMSQueryStatus_id'] = 3; // Значит меняем статус на "Открыт доступ"
			else
				$data['TFOMSQueryStatus_id'] = 4; // Значит меняем статус на "Частично открыт доступ"
			$response = $this->dbmodel->setQueryStatus($data);
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Сохранение запроса
	 */
	function openAccessQuery()
	{
		$data = $this->ProcessInputData('openAccessQuery');
		if ($data === false) { return false; }
		$status = $this->dbmodel->checkQueryAccessAll($data);
		$data['TFOMSQueryStatus_id'] = (!empty($status))?$status:3;
		$response = $this->dbmodel->setQueryStatus($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Сохранение запроса
	 */
	function setViewPersonStatus()
	{
		$data = $this->ProcessInputData('setViewPersonStatus');
		if ($data === false) { return false; }

		$response = $this->dbmodel->setViewPersonStatus($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

}