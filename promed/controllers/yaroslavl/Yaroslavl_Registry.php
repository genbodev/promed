<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry - операции с реестрами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru
 *
 *
 * @package      Admin
 * @region       Yaroslavl
 * @access       public
 * @copyright    Copyright (c) 2020 RT MIS Ltd.
 * @author       Stanislav Bykov
 * @version      29.04.2020
 */
require_once(APPPATH . 'controllers/Registry.php');

class Yaroslavl_Registry extends Registry {
	public $db = "registry";
	public $scheme = "r76";

	/**
	 * comment
	 */
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		$this->inputRules['saveRegistry'] = [
			[ 'default' => null, 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Registry_Num', 'label' => 'Номер счета', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryStatus_id', 'label' => 'Статус реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Registry_accDate', 'label' => 'Дата счета', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Registry_begDate', 'label' => 'Начало периода', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Registry_endDate', 'label' => 'Окончание периода', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'OrgRSchet_id', 'label' => 'Расчётный счёт', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'KatNasel_id', 'label' => 'Категория населения', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'OrgSMO_id', 'label' => 'СМО', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'DispClass_id', 'label' => 'Тип диспансеризации', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryData'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Evn_id', 'label' => 'ИД случая', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Polis_Num', 'label' => 'Полис', 'rules' => '', 'type' => 'string' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RegistryStatus_id', 'label' => 'Статус реестра', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'filterRecords', 'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)', 'rules' => '', 'default' => 1, 'type' => 'int' ],
			[ 'field' => 'NumCard', 'label' => 'Номер талона', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Evn_disDate', 'label' => 'Дата выписки', 'rules' => '', 'type' => 'date' ],
		];

		$this->inputRules['loadRegistryErrorCom'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryErrorBDZ'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryErrorTFOMS'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'Person_FIO', 'label' => 'ФИО', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryErrorType_Code', 'label' => 'Код ошибки', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Evn_id', 'label' => 'ИД случая', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryError'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'Evn_id', 'label' => 'ИД случая', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryErrorType_id', 'label' => 'Ошибка', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryDataBadVol'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'Evn_id', 'label' => 'ИД случая', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ],
		];

		$this->inputRules['deleteRegistry'] = [
			[ 'field' => 'id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['importRegistry'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'importType', 'label' => 'Вид импорта', 'rules' => 'required', 'type' => 'string' ],
		];

		$this->inputRules['exportRegistryToXml'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'OverrideExportOneMoreOrUseExist', 'label' => 'Скачать с сервера или перезаписать', 'rules' => '', 'type' => 'int' ],
		];

		$this->inputRules['setRegistryStatus'] = [
			[ 'default' => null, 'field' => 'Registry_ids', 'label' => 'Идентификаторы реестров', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true ],
			[ 'field' => 'RegistryStatus_id', 'label' => 'Статус реестра', 'rules' => 'required', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryTree'] = [
			[ 'field' => 'level', 'label' => 'Уровень', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'node', 'label' => 'Узел', 'rules' => '', 'type' => 'string' ],
		];

		$this->inputRules['deleteRegistryData'] = [
			[ 'field' => 'Evn_id', 'label' => 'Идентификатор записи в реестре', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Evn_rid', 'label' => 'Идентификатор родительского события', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Evn_ids', 'label' => 'Идентификаторы записей в реестре', 'rules' => '', 'type' => 'json_array', 'assoc' => true ],
			[ 'field' => 'Evn_rids', 'label' => 'Идентификаторы корневых событий', 'rules' => '', 'type' => 'json_array', 'assoc' => true ],
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryData_deleted', 'label' => 'Признак удаления реестра', 'rules' => '', 'type' => 'int', 'default' => 1 ],
		];

		// Правила для методов работы с объединенными реестрами
		$this->inputRules['saveUnionRegistry'] = [
			[ 'default' => null, 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'KatNasel_id', 'label' => 'Категория населения', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'OrgRSchet_id', 'label' => 'Расчетный счет', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'OrgSMO_id', 'label' => 'СМО', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Registry_accDate', 'label' => 'Дата счета', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Registry_begDate', 'label' => 'Начало периода', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Registry_endDate', 'label' => 'Окончание периода', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Registry_Num', 'label' => 'Номер счета', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryGroupType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadUnionRegistryEditForm'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор объединённого реестра', 'rules' => 'required', 'type' => 'id' ]
		];

		$this->inputRules['loadUnionRegistryGrid'] = [
			[ 'field' => 'Lpu_id', 'label' => 'ЛПУ', 'rules' => '', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
		];

		$this->inputRules['loadUnionRegistryChildGrid'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор объединенного реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
		];

		$this->inputRules['deleteUnionRegistry'] = [
			[ 'field' => 'id', 'label' => 'Идентификатор объединённого реестра', 'rules' => 'required', 'type' => 'id' ],
		];
	}

	/**
	 * @return bool
	 * @throws Exception
	 * @description Изменение статуса счета-реестра
	 */
	public function setRegistryStatus() {
		$data = $this->ProcessInputData('setRegistryStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setRegistryStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool|void
	 * @description Функция формирует файлы в XML формате для выгрузки данных
	 */
	public function exportRegistryToXml() {
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryToXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка при экспорте реестров')->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * @description Импорт реестра
	 */
	public function importRegistry() {
		set_time_limit(0);

		$data = $this->ProcessInputData('importRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->importRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при импорте реестра')->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * @description Удаление реестра
	 */
	public function deleteRegistry() {
		$this->load->model('Utils_model', 'umodel');

		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }

		if ( !$this->checkDeleteRegistry($data) ) {
			$this->ReturnError('Извините, удаление реестра невозможно!');
			return false;
		}

		$response = $this->umodel->ObjectRecordDelete($data, "Registry", false, $data['id'], $this->scheme);

		if ( !empty($response[0]['Error_Message']) ) {
			$response[0]['Error_Msg'] = $response[0]['Error_Message'];
		}
		else {
			$response[0]['Error_Msg'] = '';
		}

		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @param $children
	 * @param $field
	 * @param $lvl
	 * @param string $node_id
	 * @return array
	 * @description Получение ветки дерева реестров
	 */
	protected function _getRegistryTreeChild($children, $field, $lvl, $node_id = "") {
		$response = [];

		if ( !empty($node_id) ) {
			$node_id = "/" . $node_id;
		}

		if ( is_array($children) && count($children) > 0 ) {
			foreach ( $children as $rows ) {
				$response[] = [
					'text' => trim($rows[$field['name']]),
					'id' => $field['object'] . "." . $lvl . "." . $rows[$field['id']] . $node_id,
					'object' => $field['object'],
					'object_id' => $field['id'],
					'object_value' => $rows[$field['id']],
					'leaf' => $field['leaf'],
					'iconCls' => $field['iconCls'],
					'cls' => $field['cls']
				];
			}
		}

		return $response;
	}

	/**
	 * @return bool|void
	 * @description Функция возвращает данные для дерева реестров в json-формате
	 */
	public function loadRegistryTree() {
		$data = $this->ProcessInputData('loadRegistryTree', true);
		if ($data === false) { return false; }

		$response = [];

		// Текущий уровень
		if ( !isset($data['level']) || !is_numeric($data['level']) ) {
			$this->ReturnData([]);
			return false;
		}

		$node = "";

		if ( !empty($data['node']) ) {
			$node = $data['node'];
		}

		switch ( $data['level'] ) {
			case 0: // Уровень Root. ЛПУ
				$this->load->model("LpuStructure_model", "lsmodel");
				$children = $this->lsmodel->GetLpuNodeList($data);

				$field = [ 'object' => "Lpu", 'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder" ];
				$response = $this->_getRegistryTreeChild($children, $field, $data['level']);
				break;

			case 1: // Уровень 1. Объединённые реестры
				$children = [
					['RegistryType_id' => 13, 'RegistryType_Name' => 'Объединённые реестры'],
				];
				$field = [ 'object' => "RegistryType", 'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder" ];
				$response = $this->_getRegistryTreeChild($children, $field, $data['level']);
				break;

			case 2: // Уровень 1. Типы
				$children = $this->dbmodel->loadRegistryTypeNode($data);
				$field = [ 'object' => "RegistryType", 'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder" ];
				$response = $this->_getRegistryTreeChild($children, $field, $data['level']);
				break;

			case 3: // Уровень 2. Статусы реестров
				$children = $this->dbmodel->loadRegistryStatusNode($data);
				$field = [ 'object' => "RegistryStatus", 'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file" ];
				$response = $this->_getRegistryTreeChild($children, $field, $data['level'], $node);
				break;
		}

		$this->ReturnData($response);

		return true;
	}

	/**
	 * @return bool
	 * @description Помечаем запись в реестре на удаление
	 */
	public function deleteRegistryData() {
		$data = $this->ProcessInputData('deleteRegistryData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteRegistryData($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool|void
	 * Сохранение объединенного реестра
	 */
	public function saveUnionRegistry() {
		$data = $this->ProcessInputData('saveUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении объединенного реестра')->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * Данные объединенного реестра
	 */
	public function loadUnionRegistryEditForm() {
		$data = $this->ProcessInputData('loadUnionRegistryEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 * Загрузка списка объединённых реестров
	 */
	public function loadUnionRegistryGrid() {
		$data = $this->ProcessInputData('loadUnionRegistryGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * Загрузка списка предварительных реестров, входящих в объединённый
	 */
	public function loadUnionRegistryChildGrid() {
		$data = $this->ProcessInputData('loadUnionRegistryChildGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryChildGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * Удаление объединённого реестра
	 */
	public function deleteUnionRegistry() {
		$data = $this->ProcessInputData('deleteUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * Загрузка списка ошибок перс. данных
	 */
	public function loadRegistryErrorBDZ() {
		$data = $this->ProcessInputData('loadRegistryErrorBDZ', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryErrorBDZ($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * Загрузка списка превышения плановых объемов
	 */
	public function loadRegistryDataBadVol() {
		$data = $this->ProcessInputData('loadRegistryDataBadVol', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryDataBadVol($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
}
