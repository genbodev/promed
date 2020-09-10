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
 * @property MorbusOnkoTumorStatus_model MorbusOnkoTumorStatus
 */

class MorbusOnkoTumorStatus extends swController
{
	/**
	 * MorbusOnkoTumorStatus constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'MorbusOnkoTumorStatus_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusOnkoBasePersonState_id',
					'label' => 'Общее состояние пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Топография',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoTumorStatusType_id',
					'label' => 'Состояние опухолевого процесса (мониторинг опухоли)',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusOnkoTumorStatus_NumTumor',
					'label' => 'Номер опухоли',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'readList' => array(
				array(
					'field' => 'MorbusOnkoBasePersonState_id',
					'label' => 'Общее состояние пациента',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadTumorStageList' => array(
				array('field' => 'mode', 'label' => 'Общее состояние пациента', 'rules' => '', 'type' => 'int', 'default' => 0)
			)
		);
		$this->load->database();
		$this->load->model('MorbusOnkoTumorStatus_model', 'MorbusOnkoTumorStatus');
	}

	/**
	 * save
	 * @return bool
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoTumorStatus->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Состояние опухолевого процесса')->ReturnData();
		return true;
	}

	/**
	 * readList
	 * @return bool
	 */
	function readList()
	{
		$data = $this->ProcessInputData('readList', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoTumorStatus->readList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Метод для альтернативной загрузки справочника TumorStage
	 * @return bool
	 */
	function loadTumorStageList()
	{
		$data = $this->ProcessInputData('loadTumorStageList', true);
		if (!$data) {
			return false;
		}
		$response = $this->MorbusOnkoTumorStatus->loadTumorStageList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

}