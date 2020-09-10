<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RoutingMap - Карта маршрутизации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Sharipov Fidan
 * @version      11.2019
 *
 * @property RoutingMap_model $dbmodel
 */

class RoutingMap extends swController {
	var $inputRules = [
		'save' => [[
				'field' => 'RoutingProfile_id',
				'label' => 'Тип маршрутизации',
				'rules' => 'required',
				'type' => 'id'
			], [
				'field' => 'RoutingLevel_id',
				'label' => 'Уровень',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'RoutingMap_pid',
				'label' => 'Родительска запись',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'Lpu_id',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			], [
				'field' => 'RoutingMap_begDate',
				'label' => 'Дата начала действия',
				'rules' => 'required',
				'type' => 'date'
			]
		],
		'loadTree' => [[
			'field' => 'RoutingProfile_id',
			'label' => 'Тип маршрутизации',
			'rules' => '',
			'type' => 'id'
		]],
		'loadGrid' => [[
			'field' => 'RoutingProfile_id',
			'label' => 'Тип маршрутизации',
			'rules' => 'required',
			'type' => 'id'
		], [
			'field' => 'RoutingMap_pid',
			'label' => 'Родительская запись',
			'rules' => '',
			'type' => 'id'
		], [
			'field' => 'OnlyActive',
			'label' => 'Флаг',
			'rules' => 'required',
			'type' => 'int'
		]],
		'delete' => [[
			'field' => 'RoutingMap_List',
			'label' => 'Список записей',
			'rules' => 'required',
			'type' => 'string'
		], [
			'field' => 'permanenteDelete',
			'label' => 'Окончательное удаление',
			'rules' => 'required',
			'type' => 'int'
		], [
			'field' => 'deleteChild',
			'label' => 'Флаг удаления дочерних элементов',
			'rules' => '',
			'type' => 'int'
		]],
		'restore' => [[
			'field' => 'RoutingMap_List',
			'label' => 'Список записей',
			'rules' => 'required',
			'type' => 'string'
		]]
	];

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('RoutingMap_model', 'dbmodel');
	}

	/**
	 * Сохраняет запись
	 */
	public function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) {
			return false;
		}
		
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;

		$rowCount = $this->dbmodel->getLpuCount($data);

		if ($rowCount == 0) {
			$response = $this->dbmodel->doSave($data);
		} else {
			$response = [
				'Error_Msg' => 'МО уже добавлена',
				'success' => false
			];
		}
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает вложенное дерево МО в рамках типа маршрутизации
	 */
	public function loadTree() {
		$data = $this->ProcessInputData('loadTree', true);
		if ($data === false) {
			return false;
		}

		if (empty($data['RoutingProfile_id'])) {
			$response = null;
		} else {
			$response = $this->dbmodel->loadRoutingMapList($data);
		}
		$tree = empty($response) ? [] : $this->buildTree($response);
		$this->ReturnData($tree);
		return true;
	}

	/**
	 * Возвращает список подчиненных МО
	 */
	public function loadGrid() {
		$data = $this->ProcessInputData('loadGrid', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadGrid($data);
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Удаляет запись
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) {
			return false;
		}

		$idList = json_decode($data['RoutingMap_List'], true);
		$this->dbmodel->beginTransaction();
		foreach ($idList as $id) {
			$childCount = $this->dbmodel->getChildCount([
				'RoutingMap_id' => $id,
				'onlyActive' => 2
			]);
			if ($childCount > 0) {
				$response = [
					'Error_Msg' => 'Невозможно удалить запись с дочерними элементами',
					'success' => false
				];
				break;
			}

			if ($data['deleteChild'] != 2 && $data['permanenteDelete'] == 2) {
				$allChildCount = $this->dbmodel->getChildCount([
					'RoutingMap_id' => $id,
					'onlyActive' => 1
				]);
				if ($allChildCount > 0) {
					$response = [
						'isEmpty' => false,
						'success' => false
					];
					break;
				}
			}
			if ($data['deleteChild'] == 2) {
				$childList = $this->dbmodel->getChildList([
					'RoutingMap_id' => $id
				]);
				$childList = array_reverse($childList);
				foreach ($childList as $record) {
					$this->dbmodel->delete([
						'RoutingMap_id' => $record['RoutingMap_id'],
						'permanenteDelete' => 2
					]);
				}
			}

			$response = $this->dbmodel->delete([
				'RoutingMap_id' => $id,
				'permanenteDelete' => $data['permanenteDelete']
			]);
		}

		if (!array_key_exists('Error_Msg', $response)) {
			$this->dbmodel->commitTransaction();
		} else {
			$this->dbmodel->rollbackTransaction();
		}
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Восстанавливает запись
	 */
	public function restore() {
		$data = $this->ProcessInputData('restore', true);
		if ($data === false) {
			return false;
		}

		$idList = json_decode($data['RoutingMap_List'], true);
		$this->dbmodel->beginTransaction();
		foreach ($idList as $id) {
			$response = $this->dbmodel->restore([
				'RoutingMap_id' => $id
			]);
		}

		if (!array_key_exists('Error_Msg', $response)) {
			$this->dbmodel->commitTransaction();
		} else {
			$this->dbmodel->rollbackTransaction();
		}
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Возвращает вложенный массив
	 * @param Array Массив элементов
	 * @return Array Вложенный массив
	 */
	private function buildTree(array &$elements, $parentId = 0) {
		$branch = array();
	
		foreach ($elements as $element) {
			if ($element['RoutingMap_pid'] == $parentId) {
				$children = $this->buildTree($elements, $element['RoutingMap_id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[] = $element;
				unset($elements[$element['RoutingMap_id']]);
			}
		}
		return $branch;
	}
}