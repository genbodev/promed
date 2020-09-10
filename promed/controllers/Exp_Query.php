<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * exp_Query - контроллер для работы с файлами информационного обмена с поставщиками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.11.2013
 *
 * @property Exp_Query_model dbmodel
 */

class Exp_Query extends swController {
	/**
	 * @var array
	 */
	protected  $inputRules = array(
		'loadQueryGrid' => array(
			array(
				'field' => 'Query_id',
				'label' => 'Идентификатор файла для информационного обмена',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadDbaseStructureGrid' => array(
			array(
				'field' => 'Query_id',
				'label' => 'Идентификатор файла для информационного обмена',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadQueryForm' => array(
			array(
				'field' => 'Query_id',
				'label' => 'Идентификатор файла для информационного обмена',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'saveQuery' => array(
			array(
				'field' => 'Query_id',
				'label' => 'Идентификатор файла для информационного обмена',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Ord',
				'label' => 'Номер файла для информационного обмена',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Query_Nick',
				'label' => 'Ник запроса для информационного обмена',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Filename',
				'label' => 'Файл для информационного обмена',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Name',
				'label' => 'Наименование запроса для информационного обмена',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Query',
				'label' => 'Запроса для информационного обмена',
				'rules' => 'required|trim',
				'type' => 'string'
			),
			array(
				'field' => 'DbaseStructureData',
				'label' => 'Данные о полях запроса для информационного обмена',
				'rules' => 'required|trim',
				'type' => 'string'
			)
		),
		'deleteQuery' => array(
			array(
				'field' => 'Query_id',
				'label' => 'Идентификатор файла для информационного обмена',
				'rules' => 'required',
				'type' => 'int'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Exp_Query_model', 'dbmodel');
	}

	/*
	 * Возвращает список файлов
	 */
	function loadQueryGrid()
	{
		$data = $this->ProcessInputData('loadQueryGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadQueryGrid($data);

		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}

	/*
	 * Возвращает список полей запроса
	 */
	function loadDbaseStructureGrid()
	{
		$data = $this->ProcessInputData('loadDbaseStructureGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadDbaseStructureGrid($data);

		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}

	/*
	 * Возвращает данные для формы редактирования файла запроса
	 */
	function loadQueryForm()
	{
		$data = $this->ProcessInputData('loadQueryForm',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadQueryForm($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/*
	 * Сохраняет данные файла запроса
	 */
	function saveQuery()
	{
		$data = $this->ProcessInputData('saveQuery',false);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveQuery($data);

		$this->ProcessModelSave($response,true,true)->ReturnData();
		return true;
	}

	/*
	 * Удаляет данные файла запроса
	 */
	function deleteQuery()
	{
		$data = $this->ProcessInputData('deleteQuery',false);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteQuery($data);

		$this->ProcessModelSave($response,true,true)->ReturnData();
		return true;
	}

}