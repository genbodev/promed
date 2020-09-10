<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Контроллер для объектов Рабочий список
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property AnalyzerWorksheet_model AnalyzerWorksheet_model
 */

class AnalyzerWorksheet extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'getDailyCount' => array(
				array(
					'field' => 'gendate',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'save' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheet_Code',
					'label' => 'Код',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheet_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheet_setDT',
					'label' => 'Дата создания',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'AnalyzerRack_id',
					'label' => 'Штатив',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetStatusType_id',
					'label' => 'Статус рабочего списка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'Тип рабочих списков',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => '',
					'type' => 'int'
				),
			),
			'load' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'work' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'setStatus' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerWorksheetStatusType_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheet_Code',
					'label' => 'Код',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheet_Name',
					'label' => 'Наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerWorksheet_setDT',
					'label' => 'Дата создания',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'AnalyzerRack_id',
					'label' => 'Штатив',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetStatusType_id',
					'label' => 'Статус рабочего списка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => 'Тип рабочих списков',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => '',
					'type' => 'int'
				),
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('AnalyzerWorksheet_model', 'AnalyzerWorksheet_model');
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			if (empty($data['AnalyzerWorksheet_id'])) {
				$response = $this->lis->POST('AnalyzerWorksheet', $data);
			} else {
				$response = $this->lis->PUT('AnalyzerWorksheet', $data);
			}
		} else {
			if (isset($data['AnalyzerWorksheet_id'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
			}
			if (isset($data['AnalyzerWorksheet_Code'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_Code($data['AnalyzerWorksheet_Code']);
			}
			if (isset($data['AnalyzerWorksheet_Name'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_Name($data['AnalyzerWorksheet_Name']);
			}
			if (isset($data['AnalyzerWorksheet_setDT'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_setDT($data['AnalyzerWorksheet_setDT']);
			}
			if (isset($data['AnalyzerRack_id'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerRack_id($data['AnalyzerRack_id']);
			}
			if (isset($data['AnalyzerWorksheetStatusType_id'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerWorksheetStatusType_id($data['AnalyzerWorksheetStatusType_id']);
			}
			if (isset($data['AnalyzerWorksheetType_id'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerWorksheetType_id($data['AnalyzerWorksheetType_id']);
			}
			if (isset($data['Analyzer_id'])) {
				$this->AnalyzerWorksheet_model->setAnalyzer_id($data['Analyzer_id']);
			}
			$response = $this->AnalyzerWorksheet_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Рабочий список')->ReturnData();
		}

		$this->ProcessRestResponse($response, 'single')->ReturnData();
	}

	/**
	 * Установка статуса
	 */
	function setStatus() {
		$data = $this->ProcessInputData('setStatus', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->PATCH('AnalyzerWorksheet/Status', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			if (isset($data['AnalyzerWorksheet_id'])) {
				$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
				$response = $this->AnalyzerWorksheet_model->setStatus($data);
				$this->ProcessModelSave($response, true, 'Ошибка при смене статуса рабочего списка')->ReturnData();
			} else {
				throw new Exception('Для переключения статуса рабочего списка необходимо указать его идентификатор');
			}
		}
	}
	
	/**
	 * В работу
	 */
	function work() {
		$data = $this->ProcessInputData('work', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST('AnalyzerWorksheet/work', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
			$response = $this->AnalyzerWorksheet_model->work($data);
			$this->ProcessModelSave($response, true, 'Ошибка при смене статуса рабочего списка')->ReturnData();
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('AnalyzerWorksheet', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
			$response = $this->AnalyzerWorksheet_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		}
	}

	/**
	 * Получение номера
	 */
	function getDailyCount() {
		$data = $this->ProcessInputData('getDailyCount', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('AnalyzerWorksheet/DailyCount', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->AnalyzerWorksheet_model->getDailyCount($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('AnalyzerWorksheet/list', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->AnalyzerWorksheet_model->loadList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->DELETE('AnalyzerWorksheet', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$this->AnalyzerWorksheet_model->setAnalyzerWorksheet_id($data['AnalyzerWorksheet_id']);
			$response = $this->AnalyzerWorksheet_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
		}
	}
}