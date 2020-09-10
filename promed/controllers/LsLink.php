<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LsLink - контроллер для работы с лекарственными взаимодействиями
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		LsLink
 * @access		public
 * @copyright	Copyright (c) 2014 Swan Ltd.
 * @author		Dmitriy Vlasenko
 * @version		12.2019
 *
 * @property LsLink_model dbmodel
 */
class LsLink extends swController {
	public $inputRules = [
		'loadLsLinkGrid' => [
			[
				'field' => 'PREP_ID',
				'label' => 'Препарат',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'LS_GROUP1',
				'label' => 'Группа 1 ЛС',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'LS_GROUP2',
				'label' => 'Группа 2 ЛС',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'PREP_NAME',
				'label' => 'Наименование ЛП',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'RlsRegnum',
				'label' => '№ РУ',
				'rules' => '',
				'type' => 'string'
			], [
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество',
				'rules' => 'trim',
				'type' => 'int'
			], [
				'default' => 0,
				'field' => 'start',
				'label' => 'Старт',
				'rules' => 'trim',
				'type' => 'int'
			]
		],
		'loadLsLinkEditForm' => [
			[
				'field' => 'LS_LINK_ID',
				'label' => 'Идентификатор взаимодействия',
				'rules' => 'required',
				'type' => 'id'
			]
		],
		'saveLsLink' => [
			[
				'field' => 'LS_LINK_ID',
				'label' => 'Идентификатор взаимодействия',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'PREP_ID',
				'label' => 'Идентификатор препарата',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'ACTMATTERS_G1ID',
				'label' => 'Группа 1 ЛС, Действующее вещество',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'TRADENAMES_G1ID',
				'label' => 'Группа 1 ЛС, Торговое наименование',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'CLSPHARMAGROUP_G1ID',
				'label' => 'Группа 1 ЛС, Фармакологическая группа',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'FTGGRLS_G1ID',
				'label' => 'Группа 1 ЛС, Фармакотерапевтическая группа ГРЛС',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'ACTMATTERS_G2ID',
				'label' => 'Группа 2 ЛС, Действующее вещество',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'TRADENAMES_G2ID',
				'label' => 'Группа 2 ЛС, Торговое наименование',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'CLSPHARMAGROUP_G2ID',
				'label' => 'Группа 2 ЛС, Фармакологическая группа',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'FTGGRLS_G2ID',
				'label' => 'Группа 2 ЛС, Фармакотерапевтическая группа ГРЛС',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'LS_FT_TYPE_ID',
				'label' => 'Фармакологический тип взаимодействия',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'LS_INFLUENCE_TYPE_ID',
				'label' => 'Тип влияния',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'LS_EFFECT_ID',
				'label' => 'Терапевтический эффект',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'LS_INTERACTION_CLASS_ID',
				'label' => 'Класс взаимодействия',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'DESCRIPTION',
				'label' => 'Описание (проявления взаимодействия)',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'RECOMMENDATION',
				'label' => 'Рекомендации',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'BREAKTIME',
				'label' => 'Временной перерыв',
				'rules' => '',
				'type' => 'float'
			]
		],
		'deleteLsLink' => [
			[
				'field' => 'LS_LINK_ID',
				'label' => 'Идентификатор взаимодействия',
				'rules' => 'required',
				'type' => 'id'
			], [
				'field' => 'ignorePrepLs',
				'label' => 'Признак игнорирования проверки связей препаратов со взаимодействием ЛС',
				'rules' => '',
				'type' => 'id'
			]
		],
		'getLsLinkInfo' => [
			[
				'field' => 'LS_LINK_ID',
				'label' => 'Идентификатор взаимодействия',
				'rules' => 'required',
				'type' => 'id'
			]
		],
		'linkLsLink' => [
			[
				'field' => 'LS_LINK_ID',
				'label' => 'Идентификатор взаимодействия',
				'rules' => 'required',
				'type' => 'id'
			], [
				'field' => 'PREP_ID',
				'label' => 'Идентификатор препарата',
				'rules' => 'required',
				'type' => 'id'
			]
		],
		'unlinkLsLink' => [
			[
				'field' => 'LS_LINK_ID',
				'label' => 'Идентификатор взаимодействия',
				'rules' => 'required',
				'type' => 'id'
			], [
				'field' => 'PREP_ID',
				'label' => 'Идентификатор препарата',
				'rules' => 'required',
				'type' => 'id'
			]
		],
		'loadLsGroupCombo' => [
			[
				'field' => 'LS_GROUP_ID',
				'label' => 'Идентификатор группы',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'query',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			]
		]
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('LsLink_model', 'dbmodel');
	}
	
	/**
	 * Загрузка списка взаимодействий
	 */
	function loadLsLinkGrid() {
		$data = $this->ProcessInputData('loadLsLinkGrid');
		if ($data === false) return;

		$response = $this->dbmodel->loadLsLinkGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка формы редактирования взаимодействия
	 */
	function loadLsLinkEditForm() {
		$data = $this->ProcessInputData('loadLsLinkEditForm');
		if ($data === false) return;

		$response = $this->dbmodel->loadLsLinkEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение взаимодействия
	 */
	function saveLsLink() {
		$data = $this->ProcessInputData('saveLsLink');
		if ($data === false) return;

		$response = $this->dbmodel->saveLsLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Удаление взаимодействия
	 */
	function deleteLsLink() {
		$data = $this->ProcessInputData('deleteLsLink');
		if ($data === false) return;

		$response = $this->dbmodel->deleteLsLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления взаимодействия')->ReturnData();
	}

	/**
	 * Получение описания взаимодействия
	 */
	function getLsLinkInfo() {
		$data = $this->ProcessInputData('getLsLinkInfo');
		if ($data === false) return;

		$response = $this->dbmodel->getLsLinkInfo($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении описания взаимодействия')->ReturnData();
	}

	/**
	 * Связь с взаимодействием
	 */
	function linkLsLink() {
		$data = $this->ProcessInputData('linkLsLink');
		if ($data === false) return;

		$response = $this->dbmodel->linkLsLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка связи с взаимодействием')->ReturnData();
	}

	/**
	 * Удаление связи со взаимодействием
	 */
	function unlinkLsLink() {
		$data = $this->ProcessInputData('unlinkLsLink');
		if ($data === false) return;

		$response = $this->dbmodel->unlinkLsLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления связи со взаимодействием')->ReturnData();
	}

	/**
	 *
	 */
	function loadLsGroupCombo() {
		$data = $this->ProcessInputData('loadLsGroupCombo');
		if ($data === false) return;

		$response = $this->dbmodel->loadLsGroupCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}