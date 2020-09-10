<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для данных сводной заявки региона и данных о количестве ЛП, выделяемых минздравом на выписку рецептов по заявке
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Model Generator
 * @version      01.2015
 * @property DrugRequestRecept_model DrugRequestRecept_model
 */

class DrugRequestRecept extends swController {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array('field' => 'DrugRequestRecept_id', 'label' => 'идентификатор', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'DrugRequestPeriod_id', 'label' => 'идентификатор справочника медикаментов: период заявки', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'DrugProtoMnn_id', 'label' => 'идентификатор справочника медикаментов: медикаменты МНН по заявке', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'DrugRequestRecept_Kolvo', 'label' => 'кол-во ЛП в сводной заявке', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestRecept_KolvoRAS', 'label' => 'кол-во ЛП в остатках РАС, которое выделяется на выписку рецептов в периоде заявки', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestRecept_KolvoPurch', 'label' => 'кол-во ЛП, закупленное по годовому контракту на поставку', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestRecept_KolvoDopPurch', 'label' => 'кол-во ЛП, закупленное дополнительно', 'rules' => '', 'type' => 'float')
			),
			'load' => array(
				array('field' => 'DrugRequestRecept_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
			),
			'loadList' => array(
				array('field' => 'ReceptFinance_id', 'label' => 'Тип заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Рабочий период период заявки', 'rules' => '', 'type' => 'id')
			),
			'loadDrugRequestReceptConsolidatedList' => array(
				array('field' => 'Year', 'label' => 'Год', 'rules' => 'required', 'type' => 'int')
			),
			'delete' => array(
				array('field' => 'DrugRequestRecept_id', 'label' => 'идентификатор', 'rules' => 'required', 'type' => 'int')
			),
			'deleteDrugRequestReceptConsolidated' => array(
				array('field' => 'id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'string')
			),
			'importFromXls' => array(
				array('field' => 'ReceptFinance_id', 'label' => 'Тип заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Период заявки', 'rules' => 'required', 'type' => 'id')
			),
			'getCount' => array(
				array('field' => 'ReceptFinance_id', 'label' => 'Тип заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Период заявки', 'rules' => 'required', 'type' => 'id')
			),
			'getDrugRequestReceptTotalKolvo' => array(
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ReceptFinance_id', 'label' => 'Тип заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'Date', 'label' => 'Дата', 'rules' => '', 'type' => 'string')
			)
		);
		$this->load->database();
		$this->load->model('DrugRequestRecept_model', 'DrugRequestRecept_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			$response = $this->DrugRequestRecept_model->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Данные сводной заявки региона и данных о количестве ЛП, выделяемых минздравом на выписку рецептов по заявке')->ReturnData();
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
			$response = $this->DrugRequestRecept_model->load($data);
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
			$response = $this->DrugRequestRecept_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка сводных заявок
	 */
	function loadDrugRequestReceptConsolidatedList() {
		$data = $this->ProcessInputData('loadDrugRequestReceptConsolidatedList', true);
		if ($data) {
			$filter = $data;
			$response = $this->DrugRequestRecept_model->loadDrugRequestReceptConsolidatedList($filter);
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
			$response = $this->DrugRequestRecept_model->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Удаление списка заявок
	 */
	function deleteDrugRequestReceptConsolidated() {
		$data = $this->ProcessInputData('deleteDrugRequestReceptConsolidated', true, true);
		if ($data) {
			$response = $this->DrugRequestRecept_model->deleteDrugRequestReceptConsolidated($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 *  Импорт данных из xls файла.
	 */
	function importFromXls() {
		$data = $this->ProcessInputData('importFromXls', true, true);

		$root_dir = IMPORTPATH_ROOT;
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				return $this->ReturnError('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}

		if( !isset($_FILES['uploadfilefield']) ) {
			return $this->ReturnError('Ошибка! Отсутствует файл! (поле uploadfilefield)');
		}

		$file = $_FILES['uploadfilefield'];
		if( $file['error'] > 0 ) {
			return $this->ReturnError('Ошибка при загрузке файла!', $file['error']);
		}

		//вычисляем расширение из названия файла
		$ext = explode('.', $file['name']);
		if (count($ext) > 0) {
			$ext = strtolower($ext[count($ext)-1]);
		} else {
			$ext = null;
		}
		if( $ext != 'xls' ) {
			return $this->ReturnError('Необходим файл с расширением xls.');
		}

		$fileFullName = $root_dir.$file['name'];
		if( is_file($file['tmp_name']) ) {
			$fileFullName = $root_dir.time().'_'.$file['name'];
		}

		if( !rename($file['tmp_name'], $fileFullName) ) {
			return $this->ReturnError('Не удалось создать файл ' . $fileFullName);
		}
		$data['FileFullName'] = $fileFullName;

		$response = $this->DrugRequestRecept_model->importFromXls($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		unlink($fileFullName);
		return true;
	}


	/**
	 * Получние количества заявок
	 */
	function getCount() {
		$data = $this->ProcessInputData('getCount', true);
		if ($data){
			$response = $this->DrugRequestRecept_model->getCount($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных о количестве заявок')->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Получние количества медикаментов в разнарядке
	 */
	function getDrugRequestReceptTotalKolvo() {
		$data = $this->ProcessInputData('getDrugRequestReceptTotalKolvo', true);
		if ($data){
			$response = $this->DrugRequestRecept_model->getDrugRequestReceptTotalKolvo($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}