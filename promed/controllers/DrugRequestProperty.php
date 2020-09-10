<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для объектов Перечень списков медикаментов для заявки.Свойства перечня
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       ModelGenerator
* @version
* @property DrugRequestProperty_model DrugRequestProperty_model
*/

class DrugRequestProperty extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'DrugRequestProperty_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OriginalDrugRequestProperty_id',
					'label' => 'Идентификатор списка для копирования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestProperty_Name',
					'label' => 'Справочник нормативных перечней',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Рабочий период списка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип списка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
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
					'field' => 'DrugRequestProperty_id',
					'label' => 'DrugRequestProperty_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'DrugRequestProperty_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestProperty_Name',
					'label' => 'Справочник нормативных перечней',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Рабочий период списка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип списка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugFinance_SysNick',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'string'
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
			'saveDrugListRequestFromJSON' => array(
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'Идентификатор списка',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugListRequest_JsonData',
					'label' => 'Строка данных',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadDrugListRequestList' => array(
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'Идентификатор списка',
					'rules' => '',
					'type' => 'int'
				),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )                
			),
			'loadDrugListRequestTorgList' => array(
				array(
					'field' => 'DrugListRequest_id',
					'label' => 'Идентификатор списка',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadSynchronizeList' => array(
				array(
					'field' => 'DrugComplexMnn_id_list',
					'label' => 'Список идентификаторов МНН',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugNormativeList_id',
					'label' => 'Идентификатор перечня',
					'rules' => '',
					'type' => 'int'
				),
                //https://redmine.swan.perm.ru/issues/73492
				array(
					'field' => 'DrugComplexMnnName_Name',
					'label' => 'наименование комплексного МНН ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'CLSDRUGFORMS_fullname',
					'label' => 'наименование лекарственной формы',
					'rules' => '',
					'type' => 'string'
				),
 				array(
					'field' => 'DrugComplexMnnDose_Name',
					'label' => '3.	Дозировка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugComplexMnnFas_Name',
					'label' => 'наименование фасовки',
					'rules' => '',
					'type' => 'string'
				)                                                               
			),
			'loadTradenames' => array(
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Идентификатор МНН',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadDrugComplexMnnList' => array(
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestProperty_id', 'label' => 'Идентификатор списка', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска медикамента по МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'paging', 'label' => 'пэйджинг', 'rules' => '', 'type' => 'checkbox', 'default' => false ),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )
			),
			'getAveragePrice' => array(
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'МНН',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugListRequest_id',
					'label' => 'Список медикаментов для заявки',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getLastYearSupplyPrices' => array(
				array('field' => 'TRADENAMES_ID', 'label' => 'Идентификатор торгового наименования', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestProperty_id', 'label' => 'Список медикаментов для заявки', 'rules' => '', 'type' => 'id')
			),
			'getPriceList' => array(
				array(
					'field' => 'mode',
					'label' => 'Режим',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'DrugComplexMnn_List',
					'label' => 'Список МНН',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'Список медикаментов для заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getDrugListRequestContext' => array(
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'МНН',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getJNVLPPrices' => array(
				array('field' => 'TRADENAMES_ID', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'TRADENAMES_ID_List', 'label' => 'Список идентификаторов торгового наименования', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id')
			),
			'loadOrgCombo' => array(
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			)
		 );
		$this->load->database();
		$this->load->model('DrugRequestProperty_model', 'DrugRequestProperty_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['DrugRequestProperty_id'])) {
				$this->DrugRequestProperty_model->setDrugRequestProperty_id($data['DrugRequestProperty_id']);
			}
			if (isset($data['DrugRequestProperty_Name'])) {
				$this->DrugRequestProperty_model->setDrugRequestProperty_Name($data['DrugRequestProperty_Name']);
			}
			if (isset($data['DrugRequestPeriod_id'])) {
				$this->DrugRequestProperty_model->setDrugRequestPeriod_id($data['DrugRequestPeriod_id']);
			}
			if (isset($data['PersonRegisterType_id'])) {
				$this->DrugRequestProperty_model->setPersonRegisterType_id($data['PersonRegisterType_id']);
			}
			if (isset($data['DrugFinance_id'])) {
				$this->DrugRequestProperty_model->setDrugFinance_id($data['DrugFinance_id']);
			}
			if (isset($data['DrugGroup_id'])) {
				$this->DrugRequestProperty_model->setDrugGroup_id($data['DrugGroup_id']);
			}
			if (isset($data['Org_id'])) {
				$this->DrugRequestProperty_model->setOrg_id($data['Org_id']);
			}
			$response = $this->DrugRequestProperty_model->save();
			//если задан идентификатор списка для копирования, копируем в сохраняемуый список медикаменты
			if (isset($data['OriginalDrugRequestProperty_id']) && $this->DrugRequestProperty_model->getDrugRequestProperty_id() > 0) {
				$this->DrugRequestProperty_model->CopyDrugListRequestList($data);
			}
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Перечень списков медикаментов для заявки.Свойства перечня')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->DrugRequestProperty_model->setDrugRequestProperty_id($data['DrugRequestProperty_id']);
			$response = $this->DrugRequestProperty_model->load();
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
			$response = $this->DrugRequestProperty_model->loadList($filter);
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
			$this->DrugRequestProperty_model->setDrugRequestProperty_id($data['id']);
			$response = $this->DrugRequestProperty_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение из сериализованного массива
	 */
	function saveDrugListRequestFromJSON() {
		$this->load->model('RlsDrug_model', 'RlsDrug_model');
		$data = $this->ProcessInputData('saveDrugListRequestFromJSON', true, true);
       
		if ($data) {
			$response = $this->DrugRequestProperty_model->saveDrugListRequestFromJSON($data);
			//сохраняем действующиие в-ва и торг. наименования в номенклатурный справочник
			ConvertFromWin1251ToUTF8($data['DrugListRequest_JsonData']);
			$dt = (array) json_decode($data['DrugListRequest_JsonData']);

			foreach($dt as $record) if ($record->state == 'add' || $record->state == 'edit') {
				$this->RlsDrug_model->addNomenData('DrugComplexMnn', $record->DrugComplexMnn_id, $data);
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
	function loadDrugListRequestList() {
		$data = $this->ProcessInputData('loadDrugListRequestList', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugRequestProperty_model->loadDrugListRequestList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка торговых наименований
	 */
	function loadDrugListRequestTorgList() {
		$data = $this->ProcessInputData('loadDrugListRequestTorgList', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugRequestProperty_model->loadDrugListRequestTorgList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для синхронизации
	 */
	function loadSynchronizeList() {
		$data = $this->ProcessInputData('loadSynchronizeList', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugRequestProperty_model->loadSynchronizeList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка торговых наименований
	 */
	function loadTradenames() {
		$data = $this->ProcessInputData('loadTradenames', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugRequestProperty_model->loadTradenames($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка комплексных МНН
	 */
	function loadDrugComplexMnnList() {
		$data = $this->ProcessInputData('loadDrugComplexMnnList', true);
		if ($data) {
			$response = $this->DrugRequestProperty_model->loadDrugComplexMnnList($data);
			if ($data['paging']) {
				$this->ProcessModelMultiList($response, true, true)->ReturnData();
			} else {
				$this->ProcessModelList($response, true, true)->ReturnData();
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Рассчет средней цены
	 */
	function getAveragePrice() {
		$data = $this->ProcessInputData('getAveragePrice', true);
		if ($data){
			$response = $this->DrugRequestProperty_model->getAveragePrice($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение минимальной, максимальной и средней цены из прошлогодних ГК
	 */
	function getLastYearSupplyPrices() {
		$data = $this->ProcessInputData('getLastYearSupplyPrices', true);
		if ($data){
			$response = $this->DrugRequestProperty_model->getLastYearSupplyPrices($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Рассчет списка цен
	 */
	function getPriceList() {
		$data = $this->ProcessInputData('getPriceList', true);
		if ($data){
			$response = $this->DrugRequestProperty_model->getPriceList($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение дополнительных данных для позиции списка медикаментов для заявки
	 */
	function getDrugListRequestContext() {
		$data = $this->ProcessInputData('getDrugListRequestContext', true);
		if ($data){
			$response = $this->DrugRequestProperty_model->getDrugListRequestContext($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Рассчет цен из справочника ЖНВЛП
	 */
	function getJNVLPPrices() {
		$data = $this->ProcessInputData('getJNVLPPrices', true);
		if ($data){
			$response = $this->DrugRequestProperty_model->getJNVLPPrices($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка организаций для комбобокса
	 */
	function loadOrgCombo() {
		$data = $this->ProcessInputData('loadOrgCombo', false);
		if ($data) {
			$response = $this->DrugRequestProperty_model->loadOrgCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}