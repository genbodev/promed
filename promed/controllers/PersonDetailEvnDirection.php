<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonDetailEvnDirection - Дополнительные сведения о паци
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author
 * @version
 *
 * @property PersonDetailEvnDirection_model $dbmodel
 */
class PersonDetailEvnDirection extends swController {
	public $inputRules = [
		'getOne' => [
			[
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			], [
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'id'
			]
		],
		'save' => [
			[
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			], [
				'field' => 'HIVContingentTypeFRMIS_id',
				'label' => 'Код контингента ВИЧ',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'CovidContingentType_id',
				'label' => 'Код контингента COVID',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'HormonalPhaseType_id',
				'label' => 'Идентификатор фазы цикла',
				'rules' => '',
				'type' => 'id'
			]
		]
	];

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('PersonDetailEvnDirection_model', 'dbmodel');
	}
	
	function getOne() {
		$data = $this->ProcessInputData('getOne', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getOne($data);
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Сохранение
	 */
	function save() {
		$this->dbmodel->setScenario(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('save', true, true);

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("PersonDetailEvnDirection/save", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->doSave($data);
			$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		}
	}
}
