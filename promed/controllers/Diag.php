<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Diag - контроллер для работы со справочником диагназов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.12.2013
 *
 * @property Diag_model dbmodel
 */

class Diag extends swController {
	public $inputRules = array(
		'getDiagTreeData' => array(
			array(
				'field' => 'node',
				'label' => 'node',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Date',
				'label' => 'Diag_Date',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getDiagTreeSearchData' => array(
			array(
				'field' => 'node',
				'label' => 'node',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code',
				'label' => 'Код диагноза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Date',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Diag_Name',
				'label' => 'Наименование диагноза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DiagLevel_id',
				'label' => 'Уровень',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PersonRegisterType_SysNick',
				'label' => 'Тип регистра',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MorbusType_SysNick',
				'label' => 'Тип заболевания/нозологии',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'registryType',
				'label' => 'Тип регистра',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadDiagGrid' => array(
			array(
				'field' => 'Diag_pid',
				'label' => 'Идентификатор родительского диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_Code',
				'label' => 'Код диагноза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Name',
				'label' => 'Наименование диагноза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'query',
				'label' => 'Поисковый запрос',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'mode',
				'label' => 'Режим',
				'rules' => '',
				'type' => 'string'
			),
			array(//yl: вывод последних диагнозов Пациента за 5 лет при mode=lastPersonDiags
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_Date',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(//yl: вывод последних диагнозов Пациента за 5 лет при mode=lastPersonDiags
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => '',
				'type' => 'int'
			)

		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Diag_model', 'dbmodel');
	}

	/**
	 * Возвращает данные для дерева диагнозов
	 * @return bool
	 */
	function getDiagTreeData()
	{
		$data = $this->ProcessInputData('getDiagTreeData',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getDiagTreeData($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные для дерева диагнозов с поиском в дереве
	 * @return bool
	 */
	function getDiagTreeSearchData()
	{
		$data = $this->ProcessInputData('getDiagTreeSearchData',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getDiagTreeSearchData($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список диагназов
	 * @return bool
	 */
	function loadDiagGrid()
	{
		$data = $this->ProcessInputData('loadDiagGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadDiagGrid($data);

		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}
}