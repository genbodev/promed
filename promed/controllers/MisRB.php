<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MisRB - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.04.2015
 *
 * @property MisRB_model dbmodel
 */

class MisRB extends swController {
	protected  $inputRules = array(
		'execCommand' => array(
			array(
				'field' => 'serviceType',
				'label' => 'Тип сервиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'command',
				'label' => 'Команда',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'params',
				'label' => 'Команда',
				'rules' => '',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'printResult',
				'label' => 'Печать резульатат',
				'rules' => '',
				'type' => 'checkbox'
			),
		),
		'test' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Evn_id',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'syncAll' => array(
			array(
				'field' => 'ignoreSettings',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadMISErrorGrid' => array(
			array(
				'field' => 'MISError_setDT_From',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MISError_setDT_To',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 0
			),
			array(
				'field' => 'limit',
				'label' => 'Лимит записей',
				'rules' => '',
				'type' => 'int',
				'default' => 100
			)
		),
		'getPersonFromIEMK' => array(
			array(
				'field' => 'Person_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'type',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadEvnDirectionIEMKList' => array(
			array(
				'field' => 'DirectionType_id',
				'label' => 'Тип направления',
				'rules' => 'required',
				'type' => 'id'
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
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionIEMK_setDT_From',
				'label' => 'Дата направления от',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionIEMK_setDT_To',
				'label' => 'Дата направления до',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		'getDistrictList' => array(

		),
		'getLpuList' => array(
			array(
				'field' => 'IdDistrict',
				'label' => 'Район',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getSpesialityList' => array(
			array(
				'field' => 'IdLPU',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDoctorList' => array(
			array(
				'field' => 'IdSpesiality',
				'label' => 'Специальность',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'IdLPU',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDateList' => array(
			array(
				'field' => 'IdDoc',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'IdLPU',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getAppointmentList' => array(
			array(
				'field' => 'IdDoc',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'IdLPU',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Date',
				'label' => 'Дата',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		'setWaitingList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'idDoc',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'nameDoc',
				'label' => 'ФИО врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'idSpesiality',
				'label' => 'Специальность',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'nameSpesiality',
				'label' => 'Специальность',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'idLpu',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setAppointment' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'idAppointment',
				'label' => 'Талон для записи',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'idLpu',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();

		set_time_limit(0);

		ini_set("max_execution_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->model('MisRB_model', 'dbmodel');
	}

	/**
	 * Проверка подключения к МИС РБ
	 */
	function checkConnection() {
		$response = $this->dbmodel->initSoapOptions($_SESSION, true);

		echo '<pre>';
		print_r($response);
		echo '</pre>';
	}

	/**
	 * Выполнение запроса к soap-сервису МИС РБ
	 */
	function execCommand() {
		$data = $this->ProcessInputData('execCommand', true);
		if (!$data) {
			return false;
		}

		if (!isSuperadmin()) {
			$this->ReturnError('Недостаточно прав для выполнениея метода');
			return false;
		}

		$this->dbmodel->setExecIterationDelay(2);
		$initResp = $this->dbmodel->initSoapOptions($data['session'], true);
		if (!empty($initResp['Error_Msg'])) {
			print_r($initResp);
			$this->ReturnError($initResp['Error_Msg'], $initResp['Error_Code']);
			return false;
		}

		try {
			$result = $this->dbmodel->exec($data['serviceType'], $data['command'], !empty($params)?$params:null);
		} catch(Exception $e) {
			$this->ReturnError($e->getMessage(), $e->getCode());
			return false;
		}

		if ($data['printResult']) {
			echo '<pre>'.print_r($result, true).'</pre>';
		} else {
			$response = array('success' => true, 'result' => json_encode($result));
			$this->ReturnData($response);
		}

		return true;
	}

	/**
	 * Тест
	 */
	function test() {
		$data = $this->ProcessInputData('test', true);
		if ($data === false) { return false; }

		$this->dbmodel->test($data);
	}

	/**
	 * Отправка всех изменённых ТАП/КВС/ДД после последней отпрваки.
	 */
	function syncAll() {
		$data = $this->ProcessInputData('syncAll', true);
		if ($data === false) { return false; }

		if (!isSuperadmin()) {
			$this->ReturnError('Функционал доступен только для суперадмина');
			return false;
		}

		$this->dbmodel->syncAll($data);

		$this->ReturnData(array('success' => true));
		return true;
	}

	/**
	 * Получение списка ошибок
	 */
	function loadMISErrorGrid() {
		$data = $this->ProcessInputData('loadMISErrorGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMISErrorGrid($data);
		$this->ProcessModelMultiList($response, false, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Получение данных пациента из ИЭМК
	 */
	function getPersonFromIEMK() {
		$data = $this->ProcessInputData('getPersonFromIEMK', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonFromIEMK($data);
		$this->ProcessModelSave($response, true, 'При получении данных возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Получение направлений из ИЭМК
	 */
	function loadEvnDirectionIEMKList() {
		$data = $this->ProcessInputData('loadEvnDirectionIEMKList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDirectionIEMKList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение регионов
	 */
	function getDistrictList() {
		$data = $this->ProcessInputData('getDistrictList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDistrictList($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Получение МО
	 */
	function getLpuList() {
		$data = $this->ProcessInputData('getLpuList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuList($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Получение специальностей
	 */
	function getSpesialityList() {
		$data = $this->ProcessInputData('getSpesialityList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getSpesialityList($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Получение врачей
	 */
	function getDoctorList() {
		$data = $this->ProcessInputData('getDoctorList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDoctorList($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Получение дат
	 */
	function getDateList() {
		$data = $this->ProcessInputData('getDateList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDateList($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Получение времени
	 */
	function getAppointmentList() {
		$data = $this->ProcessInputData('getAppointmentList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getAppointmentList($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Постановка в очередь
	 */
	function setWaitingList() {
		$data = $this->ProcessInputData('setWaitingList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setWaitingList($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Запись
	 */
	function setAppointment() {
		$data = $this->ProcessInputData('setAppointment', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setAppointment($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
}
