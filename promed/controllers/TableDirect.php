<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TableDirect - контроллер для работы с базовыми справочниками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.07.2014
 *
 * @property TableDirect_model dbmodel
 */

class TableDirect extends swController {
	protected  $inputRules = array(
		'loadTableDirectInfoGrid' => array(
			array(
				'field' => 'TableDirectInfo_Name',
				'label' => 'Наименование',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadTableDirectList' => array(
			array(
				'field' => 'TableDirectInfo_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadTableDirectInfoList' => array(
			array(
				'field' => 'TableDirectInfo_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadTableDirectGrid' => array(
			array(
				'field' => 'TableDirectInfo_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'TableDirect_Name',
				'label' => 'Наименование',
				'rules' => '',
				'type' => 'string'
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
		'loadTableDirectInfoForm' => array(
			array(
				'field' => 'TableDirectInfo_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadTableDirectForm' => array(
			array(
				'field' => 'TableDirect_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveTableDirectInfo' => array(
			array(
				'field' => 'TableDirectInfo_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TableDirectInfo_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'TableDirectInfo_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'TableDirectInfo_SysNick',
				'label' => 'Системное наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'TableDirectInfo_Descr',
				'label' => 'Описание',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveTableDirect' => array(
			array(
				'field' => 'TableDirect_id',
				'label' => 'Идентификатор базового справочника',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TableDirectInfo_id',
				'label' => 'Идентификатор информации о базовом справочнике',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'TableDirect_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'TableDirect_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'TableDirect_SysNick',
				'label' => 'Системное наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'TableDirect_begDate',
				'label' => 'Дата начала действия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'TableDirect_endDate',
				'label' => 'Дата окончания действия',
				'rules' => '',
				'type' => 'date'
			)
		),
		'deleteTableDirectInfo' => array(
			array(
				'field' => 'TableDirectInfo_id',
				'label' => 'Идентификатор иформации о базовых справочниках',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteTableDirect' => array(
			array(
				'field' => 'TableDirect_id',
				'label' => 'Идентификатор базового справочника',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('TableDirect_model', 'dbmodel');
	}

	/**
	 * Получение списка информации о базовых справочниках
	 */
	function loadTableDirectInfoGrid()
	{
		$data = $this->ProcessInputData('loadTableDirectInfoGrid',false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTableDirectInfoGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка базовых справочников
	 */
	function loadTableDirectGrid()
	{
		$data = $this->ProcessInputData('loadTableDirectGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTableDirectGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка записей из базового справочника
	 */
	function loadTableDirectList()
	{
		$data = $this->ProcessInputData('loadTableDirectList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTableDirectList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка базовых справочников
	 */
	function loadTableDirectInfoList()
	{
		$data = $this->ProcessInputData('loadTableDirectInfoList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTableDirectInfoList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для редактирования информации о базовых справочниках
	 */
	function loadTableDirectInfoForm()
	{
		$data = $this->ProcessInputData('loadTableDirectInfoForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTableDirectInfoForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для редактирования базового справочника
	 */
	function loadTableDirectForm()
	{
		$data = $this->ProcessInputData('loadTableDirectForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTableDirectForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение ифнормации о базовом справочнике
	 */
	function saveTableDirectInfo()
	{
		$data = $this->ProcessInputData('saveTableDirectInfo');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveTableDirectInfo($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение базового справочника
	 */
	function saveTableDirect()
	{
		$data = $this->ProcessInputData('saveTableDirect');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveTableDirect($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление информации о базовых справочниках
	 */
	function deleteTableDirectInfo()
	{
		$data = $this->ProcessInputData('deleteTableDirectInfo');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteTableDirectInfo($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление базового справочника
	 */
	function deleteTableDirect()
	{
		$data = $this->ProcessInputData('deleteTableDirect');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteTableDirect($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}