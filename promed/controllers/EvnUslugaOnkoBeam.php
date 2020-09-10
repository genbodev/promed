<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Лучевое  лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 * @property EvnUslugaOnkoBeam_model EvnUslugaOnkoBeam
 */

class EvnUslugaOnkoBeam extends swController
{
	/**
	 *	Function
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoBeam_model', 'EvnUslugaOnkoBeam');
		$this->inputRules = $this->EvnUslugaOnkoBeam->getInputRules();
	}

	/**
	 *	Function
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data){
			$response = $this->EvnUslugaOnkoBeam->save($data);
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
			$this->EvnUslugaOnkoBeam->setId($data['EvnUslugaOnkoBeam_id']);
			$response = $this->EvnUslugaOnkoBeam->load();
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
			$this->EvnUslugaOnkoBeam->setId($data['EvnUslugaOnkoBeam_id']);
			$response = $this->EvnUslugaOnkoBeam->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}