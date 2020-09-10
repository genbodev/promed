<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Состояние пациента
 *
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 * @property MorbusOnkoBasePersonState_model MorbusOnkoBasePersonState
 */

class MorbusOnkoBasePersonState extends swController
{
	/**
	 * Описание метода
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'create' => array(
				array(
					'field' => 'MorbusOnkoBase_id',
					'label' => 'общее заболевание',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusOnkoBasePersonState_setDT',
					'label' => 'Дата наблюдения',
					'rules' => 'required',
					'type' => 'date'
				),
			),
			'save' => array(
				array(
					'field' => 'MorbusOnkoBasePersonState_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusOnkoBase_id',
					'label' => 'общее заболевание',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusOnkoBasePersonState_setDT',
					'label' => 'Дата наблюдения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'OnkoPersonStateType_id',
					'label' => 'Общее состояние пациента',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'load' => array(
				array(
					'field' => 'MorbusOnkoBasePersonState_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadList' => array(
				array(
					'field' => 'Morbus_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'id'
				),
			),
			'destroy' => array(
				array(
					'field' => 'MorbusOnkoBasePersonState_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
		$this->load->database();
		$this->load->model('MorbusOnkoBasePersonState_model', 'MorbusOnkoBasePersonState');
	}

	/**
	 * Описание метода
	 */
	function destroy()
	{
		$data = $this->ProcessInputData('destroy', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoBasePersonState->destroy($data);
		$this->ProcessModelSave($response, true, 'Ошибка запроса удаления')->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function create()
	{
		$data = $this->ProcessInputData('create', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoBasePersonState->create($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Состояние пациента')->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoBasePersonState->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Состояние пациента')->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function load()
	{
		$data = $this->ProcessInputData('load', true);
		if (!$data) {
			return false;
		}
		$this->MorbusOnkoBasePersonState->setId($data['MorbusOnkoBasePersonState_id']);
		$response = $this->MorbusOnkoBasePersonState->read($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Описание метода
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoBasePersonState->getViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

}