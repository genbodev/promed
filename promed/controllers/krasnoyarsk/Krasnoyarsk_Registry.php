<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry - операции с реестрами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @region       Krasnoyarsk
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Bykov Stas aka Savage
 * @version      07.12.2019
 */
require_once(APPPATH . 'controllers/Registry.php');

class Krasnoyarsk_Registry extends Registry {
	public $db = "registry";
	public $scheme = "r24";

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
			[ 'field' => 'DispClass_id', 'label' => 'Тип диспансеризации', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryData'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
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

		$this->inputRules['loadRegistryErrorTFOMS'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'Person_FIO', 'label' => 'ФИО', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryErrorTfomsType_Code', 'label' => 'Код ошибка', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryErrorTfomsClass_id', 'label' => 'Класс ошибки', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Evn_id', 'label' => 'ИД случая', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryErrorTfomsBDZ'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'Person_FIO', 'label' => 'ФИО', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryErrorType_Code', 'label' => 'Код ошибка', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Evn_id', 'label' => 'ИД случая', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['loadRegistryError'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Evn_id', 'label' => 'ИД случая', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RegistryErrorType_id', 'label' => 'Ошибка', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['deleteRegistry'] = [
			[ 'field' => 'id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
		];

		$this->inputRules['importRegistryFromTFOMS'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string' ],
		];

		$this->inputRules['printRegistry'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
		];

		$this->inputRules['exportRegistryToXml'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
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
	}

	/**
	 * Изменение статуса счета-реестра
	 * Входящие данные: ID рееестра и значение статуса
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	public function setRegistryStatus() {
		$data = $this->ProcessInputData('setRegistryStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setRegistryStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 */
	public function exportRegistryToXml() {
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryToXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка при экспорте реестров')->ReturnData();

		return true;
	}

	/**
	 * Импорт реестра из ТФОМС
	 */
	public function importRegistryFromTFOMS() {
		set_time_limit(0);

		$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
		if ($data === false) { return false; }

		$this->load->library('textlog', [ 'file' => 'importRegistryFromTFOMS_' . date('Y_m_d') . '.log' ]);

		$response = $this->dbmodel->importRegistryFromTFOMS($data);
		$this->ProcessModelSave($response, true, 'Ошибка при импорте реестра')->ReturnData();

		return true;
	}

	/**
	 * Удаление реестра
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
	 * Получение ветки дерева реестров
	 */
	protected function _getRegistryTreeChild($childrens, $field, $lvl, $node_id = "") {
		$response = [];

		if ( !empty($node_id) ) {
			$node_id = "/" . $node_id;
		}

		if ( is_array($childrens) && count($childrens) > 0 ) {
			foreach ( $childrens as $rows ) {
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
	 * Функция возвращает данные для дерева реестров в json-формате
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
				$childrens = $this->lsmodel->GetLpuNodeList($data);

				$field = [ 'object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder" ];
				$response = $this->_getRegistryTreeChild($childrens, $field, $data['level']);
				break;

			case 1: // Уровень 1. Типы
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = [ 'object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder" ];
				$response = $this->_getRegistryTreeChild($childrens, $field, $data['level']);
				break;

			case 2: // Уровень 2. Статусы реестров
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = [ 'object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file" ];
				$response = $this->_getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
		}

		$this->ReturnData($response);

		return true;
	}

	/**
	 * Получение списка результатов проверки по БДЗ
	 */
	public function loadRegistryErrorTfomsBDZ() {
		$data = $this->ProcessInputData('loadRegistryErrorTfomsBDZ', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryErrorTfomsBDZ($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Помечаем запись в реестре на удаление
	 */
	public function deleteRegistryData() {
		$data = $this->ProcessInputData('deleteRegistryData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteRegistryData($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}
}
