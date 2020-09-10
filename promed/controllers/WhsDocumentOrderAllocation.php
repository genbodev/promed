<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для объектов Разнарядка на выписку рецептов 
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version
*/

class WhsDocumentOrderAllocation extends swController
{
	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'sign' => array(
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'Тип документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'save' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'WhsDocumentUc_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_pid',
					'label' => 'WhsDocumentUc_pid',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentOrderAllocation_id',
					'label' => 'WhsDocumentOrderAllocation_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_Num',
					'label' => 'Номер документа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentUc_Name',
					'label' => 'Наименование документа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'Тип документа',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUc_Date',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentOrderAllocation_Percent',
					'label' => 'Величина резерва',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentUc_Sum',
					'label' => 'Сумма',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentUc_Date_Range',
					'label' => 'Период разнаярдки',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'OrderAllocationDrugJSON',
					'label' => 'Медикаменты сводной разнарядки',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OrderAllocationDrugMoJSON',
					'label' => 'Медикаменты разнарядки для МО',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OrderAllocationDrugSpecJSON',
					'label' => 'Медикаменты плана поставки',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OrderAllocationDrugFarmacyJSON',
					'label' => 'Медикаменты распределения ЛС по аптекам',
					'default' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор сводной заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				)
			),
			'load' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'WhsDocumentUc_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentUc_Date_Range',
					'label' => 'Период',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentType_id',
					'label' => 'Вид документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadWhsDocumentOrderAllocationGrid' => array(
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расхода',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentOrderAllocation_Range',
					'label' => 'Период',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'KLAreaStat_id',
					'label' => 'Территория',
					'rules' => '',
					'type' => 'id'
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
					'label' => 'Населённый пункт',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentType_Code',
					'label' => 'Тип разнарядки',
					'rules' => '',
					'type' => 'id'
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
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 100
				)
			),
			'loadSvodDrugRequestList' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadSourceWhsDocumentUcCombo' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Идентификатор документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка запроса из комбобокса',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadSourceWhsDocumentUcOrgCombo' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Идентификатор распоряжения на выдачу разнарядки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'id'
				)
			)
		 );
		$this->load->database();
		$this->load->model('WhsDocumentOrderAllocation_model', 'dbmodel');
		$this->load->model('WhsDocumentOrderAllocationDrug_model', 'WhsDocumentOrderAllocationDrug_model');
	}
	
	/**
	 * Загрузка списка разнарядок для формы поиска
	 */
	function loadWhsDocumentOrderAllocationGrid() {
		$data = $this->ProcessInputData('loadWhsDocumentOrderAllocationGrid', false); // сессию не надо.
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadWhsDocumentOrderAllocationGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *  Подписание распоряжения
	 */
	function sign() {
        $this->load->helper("Options");
        $this->load->model("Options_model", "Options_model");

		$data = $this->ProcessInputData('sign', true);
		if ($data === false) { return false; }

        $data['options'] = $this->Options_model->getOptionsAll($data);

		$response = $this->dbmodel->sign($data);
		$this->ProcessModelSave($response, true, 'Ошибка подписания договора')->ReturnData();
	}

	/**
	 *  Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) {
			return false;
		}
		
		//$data['Org_id'] = null;
		//$data['WhsDocumentUc_pid'] = null;
		$response = $this->dbmodel->save($data);

		if (isset($response[0]['WhsDocumentUc_id']) && $response[0]['WhsDocumentUc_id'] > 0) {		
			// 1. Сохранить все ЛС в сводной разнарядке в WhsDocumentOrderAllocationDrug
			ConvertFromWin1251ToUTF8($data['OrderAllocationDrugJSON']);
			$array = json_decode($data['OrderAllocationDrugJSON'], true);
			$res = $this->WhsDocumentOrderAllocationDrug_model->saveFromArray(array(
				'WhsDocumentOrderAllocation_id' => $response[0]['WhsDocumentUc_id'],
				'WhsDocumentType_id' => $data['WhsDocumentType_id'],
				'array' => $array,
				'pmUser_id' => $data['pmUser_id']
			));

			// Сохраняем спецификации для различных типов разнарядок
			$json_data_array = array(
				'OrderAllocationDrugMoJSON' => array(
					'WhsDocumentUc_Name' => 'Разнарядка для МО на выписку рецептов',
					'WhsDocumentType_id' => 9
				),
				'OrderAllocationDrugSpecJSON' => array(
					'WhsDocumentUc_Name' => 'Спецификация плана поставки',
					'WhsDocumentType_id' => 16
				),
				'OrderAllocationDrugFarmacyJSON' => array(
					'WhsDocumentUc_Name' => 'Спецификация документа распределения ЛС по аптекам',
					'WhsDocumentType_id' => 18
				)
			);

			foreach($json_data_array as $json_name => $json_data) {
				if (isset($data[$json_name]) && !empty($data[$json_name])) {
					$orgs = array();

					ConvertFromWin1251ToUTF8($data[$json_name]);
					$spec_arr = json_decode($data[$json_name], true);
					// распихиваем медикаменты по организациям
					if (!empty($spec_arr)) {
						foreach($spec_arr as $record) {
							$orgs[$record['Org_id'].''][] = $record;
						}
					}

					foreach($orgs as $key => $value) {
						$data['Org_id'] = $key;
						$data['WhsDocumentUc_id'] = isset($value[0]['WhsDocumentOrderAllocation_id']) ? $value[0]['WhsDocumentOrderAllocation_id'] : null;
						$data['WhsDocumentOrderAllocation_id'] = $data['WhsDocumentUc_id'];
						$data['WhsDocumentUc_pid'] = $response[0]['WhsDocumentUc_id'];
						$data['WhsDocumentUc_Num'] = $data['WhsDocumentUc_Num'] + 1;
						$data['WhsDocumentUc_Name'] = $json_data['WhsDocumentUc_Name'];
						$data['WhsDocumentType_id'] = $json_data['WhsDocumentType_id'];

						if (empty($data['WhsDocumentUc_id'])) { // если не редактирование добавляем новый документ для каждой лпу.
							$save_response = $this->dbmodel->save($data);
							$WhsDocumentOrderAllocation_id = $save_response[0]['WhsDocumentUc_id'];
						} else {
							$WhsDocumentOrderAllocation_id = $data['WhsDocumentUc_id'];
						}

						if (isset($WhsDocumentOrderAllocation_id) && $WhsDocumentOrderAllocation_id > 0) {
							$res = $this->WhsDocumentOrderAllocationDrug_model->saveFromArray(array(
								'WhsDocumentOrderAllocation_id' => $WhsDocumentOrderAllocation_id,
								'array' => $value,
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}
				}
			}
		}
		
		$this->ProcessModelSave($response, true, 'При сохранении данных произошла ошибка запроса к БД.')->ReturnData();
		return true;
	}

	/**
	 *  Загрузка данных распоряжения
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}

	/**
	 *  Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Генерация номера распоряжения
	 */
	function getWhsDocumentOrderAllocationNumber() {
		$data = getSessionParams();

		$response = $this->dbmodel->getWhsDocumentOrderAllocationNumber($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера документа')->ReturnData();
		
		return true;
	}

	/**
	 *  Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$response = $this->dbmodel->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка сводных заявок для распоряжения на выдачу разнарядки.
	 */
	function loadSvodDrugRequestList() {
		$data = $this->ProcessInputData('loadSvodDrugRequestList', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSvodDrugRequestList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение списка распоряжений на выдачу разнарядки МО для документа распределения ЛС по аптекам.
	 */
	function loadSourceWhsDocumentUcCombo() {
		$data = $this->ProcessInputData('loadSourceWhsDocumentUcCombo', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSourceWhsDocumentUcCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение списка получателей для определенного распоряжения на выдачу разнарядки МО.
	 */
	function loadSourceWhsDocumentUcOrgCombo() {
		$data = $this->ProcessInputData('loadSourceWhsDocumentUcOrgCombo', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSourceWhsDocumentUcOrgCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}