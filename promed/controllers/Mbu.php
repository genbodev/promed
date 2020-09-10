<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для работы с данными для ПАК НИЦ МБУ
 *
 * @package		Common
 * @access		public
 * @copyright	Copyright (c) 2019 Swan Ltd.
 * @author		Марков Андрей
 * @version
 * @property Mbu_model Mbu_model
 */

class Mbu extends swController {
	/**
	 *	Конструктор контроллера Mbu
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'sendMbu' => array(
				array(
					'field' => 'MbuPerson_id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'mode',
					'label' => 'Режим',
					'rules' => '',
					'type' => 'string'
				)
			),
			'save' => array(
				array(
					'field' => 'MbuPerson_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_id',
					'label' => 'Тест',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MbuPerson_IsBact',
					'label' => 'Признак бактериологии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BactMicro_id',
					'label' => 'Тип бактерии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MbuStatus_id',
					'label' => 'Статус отправки данных в НИЦ МБУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'InterpretationResult_id',
					'label' => 'Справочник кодов интерпретации результатов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'InterpretationResult_id',
					'label' => 'Справочник кодов интерпретации результатов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MbuPerson_sendDT',
					'label' => 'Дата отправки данных',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_oid',
					'label' => 'Oid услуги',
					'rules' => '',
					'type' => 'id'
				)
			),
			'load' => array(
				array(
					'field' => 'MbuPerson_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'Search_SurName',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Search_FirName',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SecName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MbuStatus_id',
					'label' => 'Статус',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания',
					'rules' => 'required',
					'type' => 'date'
				)
			)
			
		);
		$this->load->database();
		$this->load->model('Mbu_model', 'Mbu_model');
	}

	/**
	 *	Отправка данных в ПАК НИЦ МБУ
	 */
	function sendMbu() {
		$data = $this->ProcessInputData('sendMbu', true);
		if ($data === false) { return false; }
		//$data['mode'] = 'manual';
		$response = $this->Mbu_model->sendMbu($data);
		$this->ReturnData($response);

		return true;
	}

	/**
	 *	Сохранение записи
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		$response = $this->Mbu_model->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении записи')->ReturnData();
		return true;
	}

	/**
	 *	Загрузка данных одной записи
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$response = $this->Mbu_model->loadRecord();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка для отображения в гриде "Передача данных в ПАК НИЦ МБУ"
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->Mbu_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}