<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Гормоноиммунотерапевтическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 * @property EvnUslugaOnkoGormun_model EvnUslugaOnkoGormun
 */

class EvnUslugaOnkoGormun extends swController
{
	/**
	 *	Function
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoGormun_model', 'EvnUslugaOnkoGormun');
		$this->inputRules = $this->EvnUslugaOnkoGormun->getInputRules();
	}

	/**
	 *	Function
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data){
			$response = $this->EvnUslugaOnkoGormun->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Специфика услуги онкологии (Химиотерапевтическое)')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Function
	 */
	function load()
	{
		$data = $this->ProcessInputData('load', true);
		if ($data) {
			$this->EvnUslugaOnkoGormun->setId($data['EvnUslugaOnkoGormun_id']);
			$response = $this->EvnUslugaOnkoGormun->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Function
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->EvnUslugaOnkoGormun->setId($data['EvnUslugaOnkoGormun_id']);
			$response = $this->EvnUslugaOnkoGormun->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}