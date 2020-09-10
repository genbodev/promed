<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonRace_model - Раса пациента
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Sharipov F.
 * @version      March 2020
 *
 * @property PersonRace_model $dbmodel
 */
class PersonRace extends swController {
	public $inputRules = [
		'loadGrid' => [
			[
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'trim|required',
				'type' => 'int'
			]
		],
		'doSave' => [
			[
				'field' => 'PersonRace_id',
				'label' => 'Идентификатор записи',
				'rules' => 'trim',
				'type' => 'id'
			], [
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'trim|required',
				'type' => 'id'
			], [
				'field' => 'RaceType_id',
				'label' => 'Идентификатор расы',
				'rules' => 'trim|required',
				'type' => 'id'
			], [
				'field' => 'PersonRace_setDT',
				'label' => 'Дата внесения',
				'rules' => 'trim|required',
				'type' => 'date'
			]
		],
		'delete' => [
			[
				'field' => 'PersonRace_id',
				'label' => 'Идентификатор записи',
				'rules' => 'required',
				'type' => 'id'
			]
		]
	];

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('PersonRace_model', 'dbmodel');
	}

	/**
	 * Загрузка данных для грида
	 */
	public function loadGrid() {
		$data = $this->ProcessInputData('loadGrid', true);
		if ($data === false) { return false; }

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("PersonRace/loadGrid", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadGrid($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		}
		return true;
	}

	/**
	 * Удаление
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete', true);
		$result = $this->dbmodel->deleteObject('PersonRace',$data);
		$this->ReturnData($result);
	}
}
