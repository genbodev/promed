<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FamilyRelation - контроллер для работы с родственнми связями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			18.11.2016
 *
 * @property FamilyRelation_model dbmodel
 */

class FamilyRelation extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'FamilyRelation_id',
				'label' => 'Идентификатор примечания',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'load' => array(
			array(
				'field' => 'FamilyRelation_id',
				'label' => 'Идентификатор связи',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'FamilyRelation_id',
				'label' => 'Идентификатор связи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_cid',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'FamilyRelationType_id',
				'label' => 'Тип родственной связи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'FamilyRelation_begDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'FamilyRelation_endDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
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
		$this->load->model('FamilyRelation_model', 'dbmodel');
	}

	/**
	 * Удаление связи
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении примечания')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список связей
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает связь
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение связи
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		if ( !empty($data['FamilyRelation_begDate']) && !empty($data['FamilyRelation_endDate']) && $data['FamilyRelation_begDate'] > $data['FamilyRelation_endDate'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Дата начала должна быть меньше или равна Дате окончания',
				'Error_Code' => 146,
				'success' => false
			));
			return false;
		}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}