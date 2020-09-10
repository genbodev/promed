<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Неспецифическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @version      12.2018
 * @property     EvnUslugaOnkoNonSpec_model EvnUslugaOnkoNonSpec
 */

class EvnUslugaOnkoNonSpec extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnUslugaOnkoNonSpec_model', 'EvnUslugaOnkoNonSpec');
		$this->inputRules = $this->EvnUslugaOnkoNonSpec->getInputRules();
	}

	/**
	 * Сохранение
	 */
	public function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false ) { return false; }

		$response = $this->EvnUslugaOnkoNonSpec->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Специфика услуги онкологии (Неспецифическое)')->ReturnData();

		return true;
	}

	/**
	 * Загрузка
	 */
	public function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false ) { return false; }

		$this->EvnUslugaOnkoNonSpec->setId($data['EvnUslugaOnkoNonSpec_id']);
		$response = $this->EvnUslugaOnkoNonSpec->load();
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();

		return true;
	}

	/**
	 * Удаление
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data === false ) { return false; }

		$this->EvnUslugaOnkoNonSpec->setId($data['EvnUslugaOnkoNonSpec_id']);
		$response = $this->EvnUslugaOnkoNonSpec->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();

		return true;
	}
}