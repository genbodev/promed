<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Storage - контроллер для работы со складами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			02.07.2014
 *
 * @property Storage_model dbmodel
 */

class Storage extends swController {
	protected  $inputRules = array(
		'loadStorageGrid' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_id',
				'label' => 'Идентификатор группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Тип подразденения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_id',
				'label' => 'Идентификатор структуры организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_pid',
				'label' => 'Идентификатор родительского склада',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isClose',
				'label' => 'Флаг закрытия',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadStorageStructLevelGrid' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadGoodsStorageGrid' => array(
			array(
				'field' => 'StorageUnitType_Code',
				'label' => 'Код наименования места хранения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StorageUnitType_Name',
				'label' => 'Наименование места хранения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StorageUnitType_Nick',
				'label' => 'Краткое наименование места хранения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'dateRange',
				'label' => 'Период действия записи',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 0
			),
			array('field' => 'limit',
				'label' => '',
				'rules' => '',
				'type' => 'int',
				'default' => 100
			)
		),
		'loadGoodsStorage' => array(
			array(
				'field' => 'StorageUnitType_id',
				'label' => 'Идентификатор наименования места хранения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteGoodsStorage' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор наименования места хранения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveGoodsStorage' => array(
			array(
				'field' => 'StorageUnitType_id',
				'label' => 'Идентификатор наименования места хранения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageUnitType_Code',
				'label' => 'Код наименования места хранения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StorageUnitType_Name',
				'label' => 'Наименование места хранения',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'StorageUnitType_Nick',
				'label' => 'Краткое наименование места хранения',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'dateRange',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'daterange'
			)
		),
		'loadMolGrid' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadStorageForm' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getRowStorageStructLevel' => array(
			array(
				'field' => 'StorageStructLevel_id',
				'label' => 'Идентификатор структурного уровня склада',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RecordStatus_Code',
				'label' => 'Код статсуа записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_id',
				'label' => 'Идентификатор группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_id',
				'label' => 'Идентификатор структуры организации',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveStorage' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_Code',
				'label' => 'Номер склада',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Storage_Name',
				'label' => 'Наименование склада',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Storage_Area',
				'label' => 'Площадь склада',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'Storage_Vol',
				'label' => 'Объём склада',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'StorageRecWriteType_id',
				'label' => 'Идентификатор приема списания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_IsPKU',
				'label' => 'Флаг ПКУ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'TempConditionType_id',
				'label' => 'Температурный режим',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StorageType_id',
				'label' => 'Идентификатор типа склада',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_begDate',
				'label' => 'Дата открытия склада',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Storage_endDate',
				'label' => 'Дата закрытия склада',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Address_id',
				'label' => 'Идентификатор адреса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Address_Zip',
				'label' => 'Индекс',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'KLCountry_id',
				'label' => 'Страна',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLRgn_id',
				'label' => 'Регион',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLSubRgn_id',
				'label' => 'Район',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLCity_id',
				'label' => 'Город',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLTown_id',
				'label' => 'Населенный пункт',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLStreet_id',
				'label' => 'Улица',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Address_House',
				'label' => 'Дом',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Address_Corpus',
				'label' => 'Корпус',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Address_Flat',
				'label' => 'Квартира',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Address_Address',
				'label' => 'Адрес',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StorageStructLevelData',
				'label' => 'Список структурных уровней склада',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MolData',
				'label' => 'Список МОЛ',
				'rules' => '',
				'type' => 'string'
			),
            array(
				'field' => 'Lpu_id',
				'label' => 'Иджентификатор ЛПУ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Storage_pid',
				'label' => 'Идентификатор родительского склада',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteStorage' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadStorageMedPersonalGrid' => array(
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadStorageMedPersonalForm' => array(
			array(
				'field' => 'StorageMedPersonal_id',
				'label' => 'Идентификатор сотрудника склада',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveStorageMedPersonal' => array(
			array(
				'field' => 'StorageMedPersonal_id',
				'label' => 'Идентификатор сотрудника склада',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Storage_id',
				'label' => 'Идентификатор склада',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор сотрудника',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StorageMedPersonal_begDT',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'StorageMedPersonal_endDT',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'date'
			)
		),
		'loadStorageStructLevelList'=> array(
			array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
			array('field' => 'StorageType_id', 'label' => 'Тип склада', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_aid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Наименование склада', 'rules' => '', 'type' => 'string')
		),
		'checkIfMerchandiserIsInSmp' => array( ),
		'getCurrentMedServiceContragentId' => array( ),
		'loadSmpMainStorageList' => array( ),
		'loadSmpSubStorageList' => array( ),
		'getMolByEmergencyTeam' => array(
			array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => 'required', 'type' => 'id'),
		),
		'GetLpu4FarmStorage'=> array(
			array('field' => 'Org_id', 'label' => 'Идентификатор организации (аптеки)', 'rules' => '', 'type' => 'id')
		),
        'getStorageByMedServiceId' => array(
            array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id')
        ),
        'getStorageListByOrgLpu' => array(
            array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
            array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
            array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Наименование склада', 'rules' => '', 'type' => 'string')
        )
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Storage_model', 'dbmodel');
	}

	/**
	 * Получение списка наименований мест хранения товара для грида
	 */
	function loadGoodsStorageGrid() {
		$data = $this->ProcessInputData('loadGoodsStorageGrid', false);
		if ($data) {
			$response = $this->dbmodel->loadGoodsStorageGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования наименований мест хранения товара
	 */
	function loadGoodsStorage() {
		$data = $this->ProcessInputData('loadGoodsStorage', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadGoodsStorage($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение наименований мест хранения товара
	 */
	function saveGoodsStorage() {
		$data = $this->ProcessInputData('saveGoodsStorage', true);
		if ($data === false) { return false; }
		$check = $this->dbmodel->checkGoodsStorage($data);
		if (!empty($check)) {
			foreach ($check as $value) {
				if (mb_strtolower($value["StorageUnitType_Name"]) == mb_strtolower($data["StorageUnitType_Name"])) {
					throw new Exception('Место хранения с таким наименованием уже существует');
				} elseif (mb_strtolower($value["StorageUnitType_Nick"]) == mb_strtolower($data["StorageUnitType_Nick"])	) {
					throw new Exception('Место хранения с таким кратким наименованием уже существует');
				}
			}
			return false;
		}

		$response = $this->dbmodel->saveGoodsStorage($data);
		if (!$response['success']) {
			$this->ReturnError('Error');
			return false;
		}

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление наименований мест хранения товара
	 */
	function deleteGoodsStorage() {
		$data = $this->ProcessInputData('deleteGoodsStorage', true);
		if ($data === false) { return false; }
		$check = $this->dbmodel->checkGoodsStorageIsUsed($data['id']);
		if ($check['Error_Msg'] != '') {
			throw new Exception($check['Error_Msg']);
		}

		$response = $this->dbmodel->deleteGoodsStorage($data);

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение списка складов для грида
	 */
	function loadStorageGrid() {
		$data = $this->ProcessInputData('loadStorageGrid', false);
		if ($data === false) { return false; }

		if (empty($data['Lpu_id']) && empty($data['Org_id'])) {
			$this->ReturnData(array('Error_Msg' => toUtf('Должен быть передан идентификатор организации или МО')));
			return false;
		}

		$response = $this->dbmodel->loadStorageGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка структурных уровней склада для грида
	 */
	function loadStorageStructLevelGrid() {
		$data = $this->ProcessInputData('loadStorageStructLevelGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStorageStructLevelGrid($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка МОЛ для грида
	 */
	function loadMolGrid() {
		$data = $this->ProcessInputData('loadMolGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMolGrid($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для строки в гриде структурных уровней склада
	 */
	function getRowStorageStructLevel() {
		$data = $this->ProcessInputData('getRowStorageStructLevel', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getRowStorageStructLevel($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы редактирования склада
	 */
	function loadStorageForm() {
		$data = $this->ProcessInputData('loadStorageForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStorageForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение склада
	 */
	function saveStorage() {
		$data = $this->ProcessInputData('saveStorage', true);
		if ($data === false) { return false; }

		if (!empty($data['Storage_id']) && $data['Storage_id'] == $data['Storage_pid']) {
			$this->ReturnError('Идентификатор склада не должен совпадать с идентификатором родительского склада');
			return false;
		}

		$this->dbmodel->beginTransaction();

		if (!empty($data['Address_Address']) || !empty($data['Address_id'])) {
			$resp = $this->dbmodel->saveStorageAddress($data);
			if (!$resp) {
				$this->dbmodel->rollbackTransaction();
				$this->ReturnError('Ошибка при сохранении адреса');
				return false;
			}
			$data['Address_id'] = $resp['Address_id'];
		}

		$response = $this->dbmodel->saveStorage($data);
		if (!isset($response['Storage_id']) || !($response['Storage_id'] > 0)) {
			$this->dbmodel->rollbackTransaction();
			$this->ReturnError('Ошибка при сохранении склада');
			return false;
		}
		$data['Storage_id'] = $response['Storage_id'];

		if (!empty($data['StorageStructLevelData'])) {
			$resp = $this->dbmodel->saveStorageStructLevelData($data);
			if (!$resp || !empty($resp['Error_Msg'])) {
				$this->dbmodel->rollbackTransaction();
				if (!empty($resp['Error_Msg'])) {
					$error_msg = $resp['Error_Msg'];
				} else {
					$error_msg = 'Ошибка при сохранении структурного уровня склада';
				}
				$this->ReturnError($error_msg);
				return false;
			}
		}

		if (!empty($data['MolData'])) {
			$resp = $this->dbmodel->saveMolData($data);
			if (!$resp || !empty($resp['Error_Msg'])) {
				$this->dbmodel->rollbackTransaction();
				if (!empty($resp['Error_Msg'])) {
					$error_msg = $resp['Error_Msg'];
				} else {
					$error_msg = 'Ошибка при сохранении МОЛ';
				}
				$this->ReturnError($error_msg);
				return false;
			}
		}

		$this->dbmodel->commitTransaction();

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление склада
	 */
	function deleteStorage() {
		$data = $this->ProcessInputData('deleteStorage', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteStorage($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка сотрудников склада
	 */
	function loadStorageMedPersonalGrid() {
		$data = $this->ProcessInputData('loadStorageMedPersonalGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStorageMedPersonalGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы редактирования сотрудника склада
	 */
	function loadStorageMedPersonalForm() {
		$data = $this->ProcessInputData('loadStorageMedPersonalForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStorageMedPersonalForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение сотрудника склада
	 */
	function saveStorageMedPersonal() {
		$data = $this->ProcessInputData('saveStorageMedPersonal', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveStorageMedPersonal($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка складов на структурном уровне
	 */
	function loadStorageStructLevelList() {
		$data = $this->ProcessInputData('loadStorageStructLevelList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStorageStructLevelList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Функция определения принадлежности рабочего места товароведа к центральному складу СМП
	 */
	public function checkIfMerchandiserIsInSmp() {
		$data = $this->ProcessInputData('checkIfMerchandiserIsInSmp', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkIfMerchandiserIsInSmp($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Функция получения главного склада СМП у текущей службы, если таковой вообще существует
	 * @return boolean
	 */
	public function getCurrentMedServiceContragentId() {
		$data = $this->ProcessInputData('getCurrentMedServiceContragentId', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getCurrentMedServiceContragentId($data);
				
		// Нужно ли здесь сохранять в сессию Contragent_id ?
		// Пока сделал сохранение в сессию, т.к. не исполняется без Contragent_id пополнение укладки со склада подстанции		
		if (!array_key_exists('Contragent_id', $_SESSION)) {			
			session_write_close();
			session_start();	
			$_SESSION['Contragent_id'] = $response[0]['Contragent_id'];		
		}
		
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Формирование списка складов, подчиненных службе главного склада СМП
	 */
	public function loadSmpMainStorageList() {
		$data = $this->ProcessInputData('loadSmpMainStorageList',true);
		if (!$data) return false;
		
		$response = $this->dbmodel->loadSmpMainStorageList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Формирование списка второстепенных складов подстанций СМП
	 */
	public function loadSmpSubStorageList() {
		$data = $this->ProcessInputData('loadSmpSubStorageList',true);
		if (!$data) return false;
		
		$response = $this->dbmodel->loadSmpSubStorageList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка МОЛов бригады СМП
	 * @return boolean
	 */
	public function getMolByEmergencyTeam() {
		$data = $this->ProcessInputData('getMolByEmergencyTeam',true);
		if (!$data) return false;
		
		$response = $this->dbmodel->getMolByEmergencyTeam($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	       
    /**
	 * Получение списка МО для прикрепления к складам аптек
	 */
	function GetLpu4FarmStorage() {

		$data = $this->ProcessInputData('GetLpu4FarmStorage', false);

		$val = array();
		$response = $this->dbmodel->GetLpu4FarmStorage($data);

		foreach ($response as $row) {
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
	
	}

    /**
     * Получение идентификатора склада по идентификатору службы
     */
    function getStorageByMedServiceId() {
        $data = $this->ProcessInputData('getStorageByMedServiceId', false);
        if ($data === false) { return false; }

        $response = $this->dbmodel->getStorageByMedServiceId($data);

        $this->ProcessModelSave($response)->ReturnData();
        return true;
    }

	/**
	 * Получение списка складов по идентификатору МО или идентификатору организации (используется для комбобокса на форме редактирования складов)
	 */
	function getStorageListByOrgLpu() {
		$data = $this->ProcessInputData('getStorageListByOrgLpu',false);
		if (!$data) return false;

		$response = $this->dbmodel->getStorageListByOrgLpu($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
}
