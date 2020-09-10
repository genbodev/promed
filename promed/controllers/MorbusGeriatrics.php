<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusGeriatrics - контроллер для MorbusGeriatrics
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Morbus
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Быков Станислав
 * @version      12.2018
 *
 * @property MorbusGeriatrics_model $dbmodel
 */

class MorbusGeriatrics extends swController {
	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	public $inputRules = array(
		'load' => array(
			array('field' => 'MorbusGeriatrics_id', 'label' => 'Идентификатор специфики заболевания', 'rules' => 'required', 'type' => 'id')
		),
		'save' => array(
			array('field' => 'MorbusGeriatrics_id', 'label' => 'Идентификатор специфики заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Morbus_id', 'label' => 'Идентификатор заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AgeNotHindrance_id', 'label' => 'Градация пациента по скринингу', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsKGO', 'label' => 'Заполнена Карта комплексной гериатрической оценки (КГО)', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsWheelChair', 'label' => 'Колясочник', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsFallDown', 'label' => 'Падения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsWeightDecrease', 'label' => 'Снижение веса', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsCapacityDecrease', 'label' => 'Снижение функциональной активности', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsCognitiveDefect', 'label' => 'Когнитивные нарушения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsMelancholia', 'label' => 'Депрессии', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsEnuresis', 'label' => 'Недержание мочи', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsPolyPragmasy', 'label' => 'Полипрагмазия', 'rules' => '', 'type' => 'id'),
		),
		'getIdForEmk' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('MorbusGeriatrics_model', 'dbmodel');
	}

	/**
	 * Загрузка формы редактирования записи регистра
	 */
	public function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение формы редактирования записи регистра
	 */
	public function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);

		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function getIdForEmk() {
		$data = $this->ProcessInputData('getIdForEmk');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getIdForEmk($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
}