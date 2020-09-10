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
 * @property MorbusOnkoBasePS_model MorbusOnkoBasePS
 */

class MorbusOnkoBasePS extends swController
{
	/**
	 * Описание метода
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'MorbusOnkoBasePS_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusOnkoBase_id',
					'label' => 'общее заболевание',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusOnkoBasePS_setDT',
					'label' => 'Дата поступления',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MorbusOnkoBasePS_disDT',
					'label' => 'Дата выписки',
					'rules' => '',
					'type' => 'date'
				),
				array( 'field' => 'OnkoHospType_id', 'label' => 'Первичная/повторная',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'Diag_id', 'label' => 'Диагноз',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'OnkoPurposeHospType_id', 'label' => 'Цель госпитализации',
					'rules' => 'required', 'type' => 'id' ),
				array( 'field' => 'Lpu_id', 'label' => 'МО проведения',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'LpuSection_id', 'label' => 'Отделение стационара',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsTreatDelay', 'label' => 'Обследование, лечение отстрочено',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsNotTreat', 'label' => 'Обследование, лечение не предусмотрено',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsSurg', 'label' => 'Хирургическое лечение при госпитализации',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsBeam', 'label' => 'Лучевая терапия',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsChem', 'label' => 'Химиотерапия',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsGormun', 'label' => 'Гормонотерапия',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsImmun', 'label' => 'Иммунотерапия',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsOther', 'label' => 'Другое',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsPreOper', 'label' => 'Предоперационная лучевая терапия',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsIntraOper', 'label' => 'Интраоперационная лучевая терапия',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'MorbusOnkoBasePS_IsPostOper', 'label' => 'Послеоперационная лучевая терапия',
					'rules' => '', 'type' => 'id' ),
				array( 'field' => 'OnkoLeaveType_id', 'label' => 'Состояние при выписке',
					'rules' => '', 'type' => 'id' ),
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
			'load' => array(
				array(
					'field' => 'MorbusOnkoBasePS_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
		$this->load->database();
		$this->load->model('MorbusOnkoBasePS_model', 'MorbusOnkoBasePS');
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
		$response = $this->MorbusOnkoBasePS->save($data);
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
		$this->MorbusOnkoBasePS->setId($data['MorbusOnkoBasePS_id']);
		$response = $this->MorbusOnkoBasePS->read($data);
		//$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
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
		$response = $this->MorbusOnkoBasePS->getViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}