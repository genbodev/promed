<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionExt - контроллер для вшешних направлений
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EvnDirectionExt
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      11 2014
 */
class EvnDirectionExt extends swController {
	public $inputRules = array(
		'reidentEvnDirectionExt' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadList' => array(
			array(
				'field' => 'notIdentOnly',
				'label' => 'Только неидентифицированные',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'NaprLpu_id',
				'label' => 'Направившая МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionExt_setDT_From',
				'label' => 'Дата направления от',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionExt_setDT_To',
				'label' => 'Дата направления до',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionExt_IsIdent',
				'label' => 'Идентифицировано',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'identEvnDirectionExt' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionExt_id',
				'label' => 'Внешнее направление',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnDirectionExt_model', 'dbmodel');
	}

	/**
	 * Идентификация / смена пациента для внешнего направления
	 */
	function identEvnDirectionExt() {
		$data = $this->ProcessInputData('identEvnDirectionExt', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->identEvnDirectionExt($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Повторная идентификация направления
	 */
	function reidentEvnDirectionExt() {
		$data = $this->ProcessInputData('reidentEvnDirectionExt', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->reidentEvnDirectionExt($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
}