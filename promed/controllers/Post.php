<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Post - контроллер для работы c должностями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.04.2016
 *
 * @property Post_model dbmodel
 */

class Post extends swController {
	protected  $inputRules = array(
		'savePost' => array(
			array(
				'field' => 'Post_id',
				'label' => 'Идентификатор',
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
				'field' => 'Post_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'loadPostGrid' => array(
			array(
				'field' => 'Post_Name',
				'label' => 'Наименование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'searchMode',
                'label' => 'Режим поиска',
                'rules' => '',
                'type' => 'string'
            )
		),
		'loadPostForm' => array(
			array(
				'field' => 'Post_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPostList' => array(
			array(
				'field' => 'Post_id',
				'label' => 'Идентификатор',
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
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'searchMode',
				'label' => 'Режим поиска',
				'rules' => '',
				'type' => 'string'
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
		$this->load->model('Post_model', 'dbmodel');
	}

	/**
	 * Сохранение должности
	 */
	function savePost() {
		$data = $this->ProcessInputData('savePost');
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePost($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка должностей
	 */
	function loadPostGrid() {
		$data = $this->ProcessInputData('loadPostGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPostGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных должности для редактирования
	 */
	function loadPostForm() {
		$data = $this->ProcessInputData('loadPostForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPostForm($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка должностей
	 */
	function loadPostList() {
		$data = $this->ProcessInputData('loadPostList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPostList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}