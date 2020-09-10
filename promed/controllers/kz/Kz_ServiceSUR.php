<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceSUR - контроллер для работы с порталом СУР
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ServiceSUR
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      13.10.2015
 */
require_once(APPPATH.'controllers/ServiceSUR.php');

class Kz_ServiceSUR extends ServiceSUR
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'importGetMOList' => array(
				array(
					'field' => 'adrUnit',
					'label' => 'Идентификатор региона',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'recStart',
					'label' => 'Номер начальной строки выборки',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'recCount',
					'label' => 'Количество строк в выборке',
					'rules' => '',
					'type' => 'int',
					'default' => 200
				),
			),
			'importGetFPList' => array(
				array(
					'field' => 'MOId',
					'label' => 'Идентификатор МО в СУР',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'importGetRoomList' => array(
				array(
					'field' => 'idMo',
					'label' => 'Идентификатор МО в СУР',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'idFp',
					'label' => 'Идентификатор функционального подразделения в СУР',
					'rules' => '',
					'type' => 'id'
				),
			),
			'importGetBedList' => array(
				array(
					'field' => 'idMo',
					'label' => 'Идентификатор МО в СУР',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'idFp',
					'label' => 'Идентификатор функционального подразделения в СУР',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'idRoom',
					'label' => 'Идентификатор палат в СУР',
					'rules' => '',
					'type' => 'id'
				),
			),
			'importGetBedHistoryList' => array(
				array(
					'field' => 'idMo',
					'label' => 'Идентификатор МО в СУР',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'idFp',
					'label' => 'Идентификатор функционального подразделения в СУР',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'idBed',
					'label' => 'Идентификатор палат в СУР',
					'rules' => '',
					'type' => 'id'
				),
			),
			'importGetPersonalList' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор МО в СУР',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'fp',
					'label' => 'Идентификатор функционального подразделения в СУР',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'active',
					'label' => 'Флаг для выборки уволенных сотрудников',
					'rules' => '',
					'type' => 'int'
				),
			),
			'importGetPersonalHistoryList' => array(
				array(
					'field' => 'moId',
					'label' => 'Идентификатор МО в СУР',
					'rules' => 'required',
					'type' => 'id'
				),
				/*array(
					'field' => 'personalId',
					'label' => 'Массив идентификаторов сотрудников (json)',
					'rules' => '',
					'type' => 'string'
				),*/
				array(
					'field' => 'personalId',
					'label' => 'Идентификаторов сотрудника',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getMOInfo' => array(
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadFPTree' => array(
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО (Промед)',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ParentID',
					'label' => 'Идентификатор родительского подразделения',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadRoomGrid' => array(
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО (Промед)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FPID',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'setDate',
					'label' => 'Дата загрузки',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadBedGrid' => array(
				array(
					'field' => 'idRoom',
					'label' => 'Идентификатор палаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'setDate',
					'label' => 'Дата загрузки',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadBedHistoryGrid' => array(
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО (Промед)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'idBed',
					'label' => 'Идентификатор койки',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadPersonalGrid' => array(
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО (Промед)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'fp',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'setDate',
					'label' => 'Дата загрузки',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadPersonalHistoryGrid' => array(
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО (Промед)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'personalId',
					'label' => 'Идентификатор сотрудника',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'setDate',
					'label' => 'Дата загрузки',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadMOList' => array(),
			'loadMOListForSettings' => array(
				array(
					'field' => 'FullNameRU',
					'label' => 'МО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'MedCode',
					'label' => 'Код МО',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'loadPromedLpuList' => array(),
			'loadPersonalWorkGrid' => array(
				array(
					'field' => 'LastName',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'FirstName',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'SecondName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'IIN',
					'label' => 'ИИН',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PostFuncRU',
					'label' => 'Наименование должности',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OrderNum',
					'label' => 'Номер приказа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'BeginDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EndDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadPersonalWork' => array(
				array(
					'field' => 'ID',
					'label' => 'Идентификатор рабочего места СУР',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'savePersonalHistoryWP' => array(
				array(
					'field' => 'ID',
					'label' => 'Идентификатор рабочего места СУР',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreExistsLinkCheck',
					'label' => 'Флаг игнорирования проверки связей с местом работы СУР',
					'rules' => '',
					'type' => 'int'
				),
			),
			'deletePersonalHistoryWP' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'runImport' => array(
				array(
					'field' => 'objects',
					'label' => 'Список объектов для импорта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'regions',
					'label' => 'Список номеров регионов для импорта',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveMOSettings' => array(
				array(
					'field' => 'saveData',
					'label' => 'saveData',
					'rules' => 'required',
					'type' => 'json_array',
					'assoc' => true,
				)
			),
		);
	}

	/**
	 * Получение списка МО
	 */
	function importGetMOList() {
		$data = $this->ProcessInputData('importGetMOList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->importGetMOList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к СУР-сервису')->ReturnData();
		return true;
	}

	/**
	 * Получение списка подразделений в МО
	 */
	function importGetFPList() {
		$data = $this->ProcessInputData('importGetFPList', true);
		if ($data === false) { return false; }

		echo '<pre>';
		$response = $this->dbmodel->importGetFPList($data);
		//$this->ProcessModelSave($response, true, 'Ошибка при обращении к СУР-сервису')->ReturnData();
		print_r($response);
		return true;
	}

	/**
	 * Получение списка палат в МО
	 */
	function importGetRoomList() {
		$data = $this->ProcessInputData('importGetRoomList', true);
		if ($data === false) { return false; }

		echo '<pre>';
		$response = $this->dbmodel->importGetRoomList($data);
		//$this->ProcessModelSave($response, true, 'Ошибка при обращении к СУР-сервису')->ReturnData();
		print_r($response);
		return true;
	}

	/**
	 * Получение списка коек в МО
	 */
	function importGetBedList() {
		$data = $this->ProcessInputData('importGetBedList', true);
		if ($data === false) { return false; }

		echo '<pre>';
		$response = $this->dbmodel->importGetBedList($data);
		//$this->ProcessModelSave($response, true, 'Ошибка при обращении к СУР-сервису')->ReturnData();
		print_r($response);
		return true;
	}

	/**
	 * Получение истории состояний коек в МО
	 */
	function importGetBedHistoryList() {
		$data = $this->ProcessInputData('importGetBedHistoryList', true);
		if ($data === false) { return false; }

		echo '<pre>';
		$response = $this->dbmodel->importGetBedHistoryList($data);
		//$this->ProcessModelSave($response, true, 'Ошибка при обращении к СУР-сервису')->ReturnData();
		print_r($response);
		return true;
	}

	/**
	 * Получение списка сотрудников функциональных подразделении в МО
	 */
	function importGetPersonalList() {
		$data = $this->ProcessInputData('importGetPersonalList', true);
		if ($data === false) { return false; }

		echo '<pre>';
		$response = $this->dbmodel->importGetPersonalList($data);
		//$this->ProcessModelSave($response, true, 'Ошибка при обращении к СУР-сервису')->ReturnData();
		print_r($response);
		return true;
	}

	/**
	 * Получение истории должностей сотрудников в МО
	 */
	function importGetPersonalHistoryList() {
		$data = $this->ProcessInputData('importGetPersonalHistoryList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->importGetPersonalHistoryList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к СУР-сервису')->ReturnData();
		return true;
	}

	/**
	 * Получение информации об МО
	 */
	function getMOInfo() {
		$data = $this->ProcessInputData('getMOInfo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getMOInfo($data);
		if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Получение структуры подразделений
	 */
	function loadFPTree() {
		$data = $this->ProcessInputData('loadFPTree', true);
		if ( $data === false ) {
			return false;
		}

		$info = $this->dbmodel->getFPNodeList($data);
		if (isset($info[0]) && !empty($info[0]['Error_Msg'])) {
			$this->ReturnError($info[0]['Error_Msg']);
			return false;
		}
		$val = array();
		if ( $info != false && count($info) > 0 ) {
			foreach ( $info as $rows ) {
				$rows['text'] = $rows['NameRU'];
				$rows['id'] = $rows['FPID'];
				$rows['leaf'] = ($rows['leafcount'] > 0 ? false : true);
				$val[] = $rows;
			}
		}
		$this->ReturnData($val);
	}

	/**
	 * Получние списка палат/кабинетов
	 */
	function loadRoomGrid() {
		$data = $this->ProcessInputData('loadRoomGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRoomGrid($data);
		if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка коек
	 */
	function loadBedGrid() {
		$data = $this->ProcessInputData('loadBedGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBedGrid($data);
		if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка сотрудников
	 */
	function loadPersonalGrid() {
		$data = $this->ProcessInputData('loadPersonalGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonalGrid($data);
		if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение исптории должностей сотрудника
	 */
	function loadPersonalHistoryGrid() {
		$data = $this->ProcessInputData('loadPersonalHistoryGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonalHistoryGrid($data);
		if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение истории действий над койкой
	 */
	function loadBedHistoryGrid() {
		$data = $this->ProcessInputData('loadBedHistoryGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBedHistoryGrid($data);
		if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка МО
	 */
	function loadMOList() {
		$data = $this->ProcessInputData('loadMOList');
		if ($data === false) return false;

		$response = $this->dbmodel->loadMOList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка МО для настройки
	 */
	function loadMOListForSettings() {
		$data = $this->ProcessInputData('loadMOListForSettings');
		if ($data === false) return false;

		$response = $this->dbmodel->loadMOListForSettings($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка МО промеда, связанных с МО из СУР
	 */
	function loadPromedLpuList() {
		$data = $this->ProcessInputData('loadPromedLpuList');
		if ($data === false) return false;

		$response = $this->dbmodel->loadPromedLpuList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка сотрудников
	 */
	function loadPersonalWorkGrid() {
		$data = $this->ProcessInputData('loadPersonalWorkGrid', false);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPersonalWorkGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных о рабочем месте сотрудника
	 */
	function loadPersonalWork() {
		$data = $this->ProcessInputData('loadPersonalWork');
		if ($data === false) return false;

		$response = $this->dbmodel->loadPersonalWork($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение связи рабочего места из Промед и СУР
	 */
	function savePersonalHistoryWP() {
		$data = $this->ProcessInputData('savePersonalHistoryWP');
		if ($data === false) return false;

		$response = $this->dbmodel->savePersonalHistoryWP($data);
		if (isset($response[0])) {
			$response[0] = array_merge($this->dbmodel->getSaveResponse(), $response[0]);
		}

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Удаление связи рабочего места из Промед и СУР
	 */
	function deletePersonalHistroyWP() {
		$data = $this->ProcessInputData('deletePersonalHistoryWP');
		if ($data === false) return false;

		$response = $this->dbmodel->deletePersonalHistoryWP($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Сохранение настроек МО СУР
	 */
	function saveMOSettings() {
		$data = $this->ProcessInputData('saveMOSettings');
		if ($data === false) return false;

		$response = $this->dbmodel->saveMOSettings($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Запуск импорта данных из СУР
	 */
	function runImport() {
		$data = $this->ProcessInputData('runImport');
		if ($data === false) return false;

		$response = $this->dbmodel->runImport($data);

		$this->ProcessModelSave($response, true, 'Ошибка импорта данных из СУР')->ReturnData();
		return true;
	}

	/**
	 * Функция для тестирования СУР
	 */
	function test() {
		$this->dbmodel->test();
	}
}
?>