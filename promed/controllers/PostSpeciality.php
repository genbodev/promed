<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * PostSpeciality - контроллер для работы cо стыковочной таблицей должностей и специальностей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2018 Swan Ltd.
 *
 * @property PostSpeciality_model dbmodel
 */
class PostSpeciality extends swController
{
	protected $inputRules = array(
		'loadPostSpecialityList' => array(
			array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_Name', 'label' => 'строка должности', 'rules' => '', 'type' => 'string'),
			array('field' => 'Speciality_Name', 'label' => 'строка специальности', 'rules' => '', 'type' => 'string'),
		),
		'savePostSpecialityPair' => array(
			array('field' => 'Post_id', 'label' => 'ид должности', 'rules' => '', 'type' => 'int'),
			array('field' => 'Speciality_id', 'label' => 'ид специальности', 'rules' => '', 'type' => 'int')
		),
		'deletePostSpecialityPair' => array(
			array('field' => 'PostSpeciality_id', 'label' => 'ид связки', 'rules' => '', 'type' => 'int')
		),
		'editPostSpecialityPair' => array(
			array('field' => 'PostSpeciality_id', 'label' => 'ид связки', 'rules' => '', 'type' => 'int'),
			array('field' => 'Post_id', 'label' => 'ид должности', 'rules' => '', 'type' => 'int'),
			array('field' => 'Speciality_id', 'label' => 'ид специальности', 'rules' => '', 'type' => 'int')
		),
		'loadSpecialityList' => array()
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('PostSpeciality_model', 'dbmodel');
	}

	/**
	 * Контроллер для получения постраничного списка соответствий
	*/
	function loadPostSpecialityList()
	{
		$data = $this->ProcessInputData('loadPostSpecialityList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPostSpecialityList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Контроллер для получения списка специальностей
	 */
	function loadSpecialityList()
	{
		$response = $this->dbmodel->loadSpecialityList();
		//$this->ProcessModelMultiList($response, true, true)->ReturnData();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Контроллер для сохранения нового соответствия
	 */
	function savePostSpecialityPair()
	{
		$data = $this->ProcessInputData('savePostSpecialityPair', true);
		if ($data === false) return false;

		$response = $this->dbmodel->savePostSpecialityPair($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Контроллер для удаления соответствия
	 */
	function deletePostSpecialityPair()
	{
		$data = $this->ProcessInputData('deletePostSpecialityPair', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deletePostSpecialityPair($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Контроллер для редактирования соответствия
	 */
	function editPostSpecialityPair()
	{
		$data = $this->ProcessInputData('editPostSpecialityPair', true);
		if ($data === false) return false;

		$response = $this->dbmodel->editPostSpecialityPair($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}
