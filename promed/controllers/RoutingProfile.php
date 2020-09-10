<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RoutingProfile - Тип маршрутизации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Sharipov Fidan
 * @version      11.2019
 *
 * @property RoutingProfile_model $dbmodel
 */

class RoutingProfile extends swController {
	var $inputRules = [
		'save' => [[
				'field' => 'RoutingProfile_name',
				'label' => 'Наименование типа маршрутизации',
				'rules' => 'required',
				'type' => 'string'
			], [
				'field' => 'RoutingProfile_sysnick',
				'label' => 'Системное наименование',
				'rules' => 'required',
				'type' => 'string'
			], [
				'field' => 'MorbusType_id',
				'label' => 'Тип заболевания',
				'rules' => '',
				'type' => 'id'
			]
		],
		'loadProfileList' => [[
			'field' => 'Region_id',
			'label' => 'Регион',
			'rules' => '',
			'type' => 'id'
		]],
		'delete' => [[
			'field' => 'RoutingProfile_id',
			'label' => 'Идентификатор типа маршрутизации',
			'rules' => 'required',
			'type' => 'id'
		]]
	];
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('RoutingProfile_model', 'dbmodel');
	}

	/**
	 * Сохранение типа маршрутизации
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) {
			return false;
		}

		$isValidName = $this->dbmodel->checkAttribute('RoutingProfile_name', $data);
		$isValidSysnick = $this->dbmodel->checkAttribute('RoutingProfile_sysnick', $data);
		if (!$isValidName) {
			$response = [
				'Error_Msg' => 'Тип маршрутизации с таким наименованием уже существует.',
				'success' => false
			];
		} elseif (!$isValidSysnick) {
			$response = [
				'Error_Msg' => 'Тип маршрутизации с таким системным наименованием уже существует.',
				'success' => false
			];
		} else {
			$data['Region_id'] = $data['session']['region']['number'];
			$data['RoutingProfile_begDate'] = (new DateTime())->format('Y-m-d');
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;

			$response = $this->dbmodel->doSave($data, false);
		}
		
		
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список типов маршрутизации региона
	 */
	public function loadProfileList() {
		$data = $this->ProcessInputData('loadProfileList', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadProfileList($data);
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Удаляет тип маршрутизации
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) {
			return false;
		}
		$this->dbmodel->beginTransaction();
		
		$this->load->model('RoutingMap_model', 'RoutingMap_model');
		$flag = $this->RoutingMap_model->deleteByProfile([
			'RoutingProfile_id' => $data['RoutingProfile_id'],
			'permanenteDelete' => 1
		]);
		$response = $this->dbmodel->delete($data);

		if ($flag && !array_key_exists('Error_Msg', $response)) {
			$this->dbmodel->commitTransaction();
		} else {
			$this->dbmodel->rollbackTransaction();
		}
		$this->ReturnData($response);
		return true;
	}
}