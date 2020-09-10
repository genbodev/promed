<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TariffValue - контроллер для работы со справочником тарифов ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 *
 * @property TariffValue_model dbmodel
 */

class TariffValue extends swController {
	protected $inputRules = array(
		'delete' => array(
			array('field' => 'TariffValue_id', 'label' => 'Идентификатор тарифа', 'rules' => 'required', 'type' => 'id'),
		),
		'import' => array(),
		'loadList' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'TariffValue_begDT_From', 'label' => 'Верхняя граница даты начала', 'rules' => '', 'type' => 'date'),
			array('field' => 'TariffValue_begDT_To', 'label' => 'Нижняя граница даты начала', 'rules' => '', 'type' => 'date'),
			array('field' => 'TariffValue_endDT_From', 'label' => 'Верхняя граница даты окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'TariffValue_endDT_To', 'label' => 'Нижняя граница даты окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'TariffValue_Code', 'label' => 'Код тарифа', 'rules' => '', 'type' => 'string'),
			array('default' => 0, 'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 100, 'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
		),
		'load' => array(
			array('field' => 'TariffValue_id', 'label' => 'Идентификатор тарифа', 'rules' => 'required', 'type' => 'id'),
		),
		'save' => array(
			array('field' => 'TariffValue_id', 'label' => 'Идентификатор тарифа', 'rules' => '', 'type' => 'id'),
			array('field' => 'TariffValue_Code', 'label' => 'Код', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'TariffValue_Value', 'label' => 'Значение', 'rules' => 'trim|required', 'type' => 'float'),
			array('field' => 'TariffValue_begDT', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'TariffValue_endDT', 'label' => 'Дата окончания', 'rules' => 'required', 'type' => 'date'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('TariffValue_model', 'dbmodel');
	}

	/**
	 * Удаление тарифа
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении тарифа')->ReturnData();

		return true;
	}

	/**
	 * Импорт тарифов
	 */
	public function import() {
		$data = $this->ProcessInputData('import');
		if ($data === false) { return false; }

		$response = $this->dbmodel->import($data);
		$this->ProcessModelSave($response, true, 'Ошибка при импорте тарифов')->ReturnData();

		return true;
	}

	/**
	 * Возвращает список тарифов
	 */
	public function loadList() {
		$data = $this->ProcessInputData('loadList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает данные тарифа
	 */
	public function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение тарифа
	 */
	public function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
}