<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Хирургическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 * 
 * @property EvnUslugaOnkoSurg_model EvnUslugaOnkoSurg
 */

class EvnUslugaOnkoSurg extends swController
{
	/**
	 * Описание метода
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnUslugaOnkoSurg_model', 'EvnUslugaOnkoSurg');
		$this->inputRules = $this->EvnUslugaOnkoSurg->getInputRules();
	}

	/**
	 * Описание метода
	 */
	function getDefaultTreatmentConditionsTypeId()
	{
		$data = $this->ProcessInputData('getDefaultTreatmentConditionsTypeId', true);
		if ($data){
			$id = $this->EvnUslugaOnkoSurg->getDefaultTreatmentConditionsTypeId($data);
			$this->ReturnData(array(
				'success' => ($id > 0),
				'TreatmentConditionsType_id' => $id,
			));
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Описание метода
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data){
			$response = $this->EvnUslugaOnkoSurg->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Специфика услуги онкологии (Химиотерапевтическое)')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Описание метода
	 */
	function load()
	{
		$data = $this->ProcessInputData('load', true);
		if ($data) {
			$this->EvnUslugaOnkoSurg->setId($data['EvnUslugaOnkoSurg_id']);
			$response = $this->EvnUslugaOnkoSurg->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Описание метода
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->EvnUslugaOnkoSurg->setId($data['EvnUslugaOnkoSurg_id']);
			$response = $this->EvnUslugaOnkoSurg->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Описание метода
	 */
	function loadForPrint()
	{
		$data = $this->ProcessInputData('loadForPrint', true);
		if ($data) {
			$this->EvnUslugaOnkoSurg->setId($data['EvnUslugaOnkoSurg_id']);
			$response = $this->EvnUslugaOnkoSurg->loadForPrint();
			$resp = '';
			foreach ($response as $value) {
				$resp .= '<tr>';
				$resp .= '<td>'.$value['EvnUslugaOnkoSurg_setDate'].'</td>';
				$resp .= '<td>'.$value['UslugaComplex'].'</td>';
				$resp .= '<td>'.$value['OperType_Name'].'</td>';
				$resp .= '<td>'.$value['MedPersonal_Fio'].'</td>';
				$resp .= '<td>'.$value['OnkoSurgTreatType_Name'].'</td>';
				$resp .= '<td>'.($value['AggType_Code']!=0 ? $value['AggType_Code'] : '').' '.$value['AggType_Name'].'</td>';
				$resp .= '<td>'.($value['sAggType_Code']!=0 ? $value['sAggType_Code'] : '').' '.$value['sAggType_Name'].'</td>';
				$resp .= '</tr>';
			}
			$this->ReturnData($resp);
			return true;
		} else {
			return false;
		}
	}
}