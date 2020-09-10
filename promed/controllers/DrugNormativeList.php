<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для объектов Справочник нормативных перечней
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       ModelGenerator
* @version
* @property DrugNormativeList_model DrugNormativeList_model
*/

class DrugNormativeList extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'DrugNormativeList_id',
					'label' => 'DrugNormativeList_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugNormativeList_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип перечня ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugNormativeList_BegDT',
					'label' => 'Дата начала действия записи',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DrugNormativeList_EndDT',
					'label' => 'Дата окончания действия записи',
					'rules' => '',
					'type' => 'date'
				)
			),
			'load' => array(
				array(
					'field' => 'DrugNormativeList_id',
					'label' => 'Идентификатор перечня',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'DrugNormativeList_id',
					'label' => 'DrugNormativeList_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugNormativeList_Name',
					'label' => 'Наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип перечня ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugNormativeList_BegDT',
					'label' => 'Дата начала действия записи',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'DrugNormativeList_EndDT',
					'label' => 'Дата окончания действия записи',
					'rules' => '',
					'type' => 'datetime'
				)
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор перечня',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'saveDrugNormativeListSpecFromJSON' => array(
				array(
					'field' => 'DrugNormativeList_id',
					'label' => 'Идентификатор перечня',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugNormativeList_JsonData',
					'label' => 'Строка данных',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadDrugNormativeListSpecList' => array(
				array(
					'field' => 'DrugNormativeList_id',
					'label' => 'DrugNormativeList_id',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getDrugNormativeListSpecContext' => array(
				array(
					'field' => 'RlsActmatters_id',
					'label' => 'Идентификатор МНН',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugFormArray',
					'label' => 'Список идентификаторов форм выпуска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TorgNameArray',
					'label' => 'Список идентификаторов торговых наименований',
					'rules' => '',
					'type' => 'string'
				)
			),
			'copyDrugNormativeList' => array(
				array(
					'field' => 'DrugNormativeList_id',
					'label' => 'DrugNormativeList_id',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadDrugFormsCombo' => array(
				array(
					'field' => 'RlsClsdrugforms_id',
					'label' => 'Идентификатор лекарственной формы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RlsActmatters_id',
					'label' => 'Идентификатор действующего вещества',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'query',
					'label' => 'Строка запроса',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadTradenamesCombo' => array(
				array(
					'field' => 'RlsTradenames_id',
					'label' => 'Идентификатор торговой формы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RlsActmatters_id',
					'label' => 'Идентификатор действующего вещества',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugFormList',
					'label' => 'Список лекарственных форм',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка запроса',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadListByRlsDrug' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getPersonRegisterTypeByWhsDocumentCostItemType'=> array(
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Идентификатор статьи расхода',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		 );
		$this->load->database();
		$this->load->model('DrugNormativeList_model', 'DrugNormativeList_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['DrugNormativeList_id'])) {
				$this->DrugNormativeList_model->setDrugNormativeList_id($data['DrugNormativeList_id']);
			}
			if (isset($data['DrugNormativeList_Name'])) {
				$this->DrugNormativeList_model->setDrugNormativeList_Name($data['DrugNormativeList_Name']);
			}
			if (isset($data['WhsDocumentCostItemType_id'])) {
				$this->DrugNormativeList_model->setWhsDocumentCostItemType_id($data['WhsDocumentCostItemType_id']);
			}
			if (isset($data['PersonRegisterType_id'])) {
				$this->DrugNormativeList_model->setPersonRegisterType_id($data['PersonRegisterType_id']);
			}
			if (isset($data['DrugNormativeList_BegDT'])) {
				$this->DrugNormativeList_model->setDrugNormativeList_BegDT($data['DrugNormativeList_BegDT']);
			}
			if (isset($data['DrugNormativeList_EndDT'])) {
				$this->DrugNormativeList_model->setDrugNormativeList_EndDT($data['DrugNormativeList_EndDT']);
			}
			$response = $this->DrugNormativeList_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Справочник нормативных перечней')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->DrugNormativeList_model->setDrugNormativeList_id($data['DrugNormativeList_id']);
			$response = $this->DrugNormativeList_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugNormativeList_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->DrugNormativeList_model->setDrugNormativeList_id($data['id']);
			$response = $this->DrugNormativeList_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение позиции списка из сериализованного массива
	 */
	function saveDrugNormativeListSpecFromJSON() {
		$this->load->model('RlsDrug_model', 'RlsDrug_model');
		$data = $this->ProcessInputData('saveDrugNormativeListSpecFromJSON', true, true);
		if ($data) {
			$response = $this->DrugNormativeList_model->saveDrugNormativeListSpecFromJSON($data);
			//сохраняем действующиие в-ва и торг. наименования в номенклатурный справочник
			ConvertFromWin1251ToUTF8($data['DrugNormativeList_JsonData']);
			$dt = (array) json_decode($data['DrugNormativeList_JsonData']);
			foreach($dt as $record) if ($record->state == 'add' || $record->state == 'edit') {
				if ($record->RlsActmatters_id > 0) {
					$this->RlsDrug_model->addNomenData('ACTMATTERS', $record->RlsActmatters_id, $data);
				}
				$torg_arr = explode(',',$record->TorgNameArray);
				foreach($torg_arr as $torg_id)
					$this->RlsDrug_model->addNomenData('TRADENAMES', $torg_id, $data);
			}
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка позиций
	 */
	function loadDrugNormativeListSpecList() {
		$data = $this->ProcessInputData('loadDrugNormativeListSpecList', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugNormativeList_model->loadDrugNormativeListSpecList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение дполнительных данных
	 */
	function getDrugNormativeListSpecContext() {
		$data = $this->ProcessInputData('getDrugNormativeListSpecContext', true);
		if ($data){			
			$response = $this->DrugNormativeList_model->getDrugNormativeListSpecContext($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Копирование нормативного списка
	 */
	function copyDrugNormativeList() {
		$data = $this->ProcessInputData('copyDrugNormativeList', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugNormativeList_model->copyDrugNormativeList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка значений для комбобокса "Лекарственная форма"
	 */
	function loadDrugFormsCombo() {
		$data = $this->ProcessInputData('loadDrugFormsCombo', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugNormativeList_model->loadDrugFormsCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка значений для комбобокса "Торговое наименование"
	 */
	function loadTradenamesCombo() {
		$data = $this->ProcessInputData('loadTradenamesCombo', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugNormativeList_model->loadTradenamesCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadListByRlsDrug() {
		$data = $this->ProcessInputData('loadListByRlsDrug', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugNormativeList_model->loadListByRlsDrug($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение Типа регистра по Статье расходов
	 */
	function getPersonRegisterTypeByWhsDocumentCostItemType() {
		$data = $this->ProcessInputData('getPersonRegisterTypeByWhsDocumentCostItemType', true);
		if ($data) {
			$response = $this->DrugNormativeList_model->getPersonRegisterTypeByWhsDocumentCostItemType($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}