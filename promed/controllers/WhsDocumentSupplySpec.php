<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для объектов Спецификация договора
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       ModelGenerator
* @version
* @property WhsDocumentSupplySpec_model WhsDocumentSupplySpec_model
*/

class WhsDocumentSupplySpec extends swController {

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'WhsDocumentSupplySpec_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Договор поставок',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_PosCode',
					'label' => 'Код позиции',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'FIRMNAMES_id',
					'label' => 'Производитель',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_KolvoForm',
					'label' => 'Количество единиц форм выпуска в упаковке',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'DRUGPACK_id',
					'label' => 'Торговая упаковка',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Okei_id',
					'label' => 'Единица поставки (ОКЕИ)',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_KolvoUnit',
					'label' => 'Количество единиц поставки',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_Count',
					'label' => 'Количество ЛС из лота',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_Price',
					'label' => 'Оптовая цена за ед. без НДС',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_NDS',
					'label' => 'НДС',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_SumNDS',
					'label' => 'Сумма с НДС',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_PriceNDS',
					'label' => 'Цена с НДС',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_ShelfLifePersent',
					'label' => 'Остаточный срок хранения не менее (%)',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'load' => array(
				array(
					'field' => 'WhsDocumentSupplySpec_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentSupplySpec_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Договор поставок',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_PosCode',
					'label' => 'Код позиции',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'FIRMNAMES_id',
					'label' => 'Производитель',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_KolvoForm',
					'label' => 'Количество единиц форм выпуска в упаковке',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DRUGPACK_id',
					'label' => 'Торговая упаковка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Okei_id',
					'label' => 'Единица поставки (ОКЕИ)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_KolvoUnit',
					'label' => 'Количество единиц поставки',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_Count',
					'label' => 'Количество поставляемых минимальных упаковок',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_Price',
					'label' => 'Оптовая цена за ед. без НДС',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_NDS',
					'label' => 'НДС',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_SumNDS',
					'label' => 'Сумма с НДС',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_PriceNDS',
					'label' => 'Цена с НДС',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'WhsDocumentSupplySpec_ShelfLifePersent',
					'label' => 'Остаточный срок хранения не менее (%)',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delete' => array(
				array(
					'field' => 'WhsDocumentSupplySpec_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'importFromXls' => array(
				array(
					'field' => 'WhsDocumentUc_pid',
					'label' => 'Идентификатор лота',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getWhsDocumentSupplySpecContext' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentSupplyStrCombo' => array(
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'Идентификатор контракта',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		 );
		$this->load->database();
		$this->load->model('WhsDocumentSupplySpec_model', 'WhsDocumentSupplySpec_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['WhsDocumentSupplySpec_id'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_id($data['WhsDocumentSupplySpec_id']);
			}
			if (isset($data['WhsDocumentSupply_id'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupply_id($data['WhsDocumentSupply_id']);
			}
			if (isset($data['WhsDocumentSupplySpec_PosCode'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_PosCode($data['WhsDocumentSupplySpec_PosCode']);
			}
			if (isset($data['DrugComplexMnn_id'])) {
				$this->WhsDocumentSupplySpec_model->setDrugComplexMnn_id($data['DrugComplexMnn_id']);
			}
			if (isset($data['FIRMNAMES_id'])) {
				$this->WhsDocumentSupplySpec_model->setFIRMNAMES_id($data['FIRMNAMES_id']);
			}
			if (isset($data['WhsDocumentSupplySpec_KolvoForm'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_KolvoForm($data['WhsDocumentSupplySpec_KolvoForm']);
			}
			if (isset($data['DRUGPACK_id'])) {
				$this->WhsDocumentSupplySpec_model->setDRUGPACK_id($data['DRUGPACK_id']);
			}
			if (isset($data['Okei_id'])) {
				$this->WhsDocumentSupplySpec_model->setOkei_id($data['Okei_id']);
			}
			if (isset($data['WhsDocumentSupplySpec_KolvoUnit'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_KolvoUnit($data['WhsDocumentSupplySpec_KolvoUnit']);
			}
			if (isset($data['WhsDocumentSupplySpec_Count'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_Count($data['WhsDocumentSupplySpec_Count']);
			}
			if (isset($data['WhsDocumentSupplySpec_Price'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_Price($data['WhsDocumentSupplySpec_Price']);
			}
			if (isset($data['WhsDocumentSupplySpec_NDS'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_NDS($data['WhsDocumentSupplySpec_NDS']);
			}
			if (isset($data['WhsDocumentSupplySpec_SumNDS'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_SumNDS($data['WhsDocumentSupplySpec_SumNDS']);
			}
			if (isset($data['WhsDocumentSupplySpec_PriceNDS'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_PriceNDS($data['WhsDocumentSupplySpec_PriceNDS']);
			}
			if (isset($data['WhsDocumentSupplySpec_ShelfLifePersent'])) {
				$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_ShelfLifePersent($data['WhsDocumentSupplySpec_ShelfLifePersent']);
			}
			$response = $this->WhsDocumentSupplySpec_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Спецификация договора')->ReturnData();
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
			$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_id($data['WhsDocumentSupplySpec_id']);
			$response = $this->WhsDocumentSupplySpec_model->load();
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
			$response = $this->WhsDocumentSupplySpec_model->loadList($filter);
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
			$this->WhsDocumentSupplySpec_model->setWhsDocumentSupplySpec_id($data['WhsDocumentSupplySpec_id']);
			$response = $this->WhsDocumentSupplySpec_model->Delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение дполнительных данных
	 */
	function getWhsDocumentSupplySpecContext() {
		$data = $this->ProcessInputData('getWhsDocumentSupplySpecContext', true);
		if ($data){
			$response = $this->WhsDocumentSupplySpec_model->getWhsDocumentSupplySpecContext($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка медикаментов в ГК вместе со списком синонимов
	 */
	function loadWhsDocumentSupplyStrCombo() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyStrCombo', true);
		if ($data){
			$response = $this->WhsDocumentSupplySpec_model->loadWhsDocumentSupplyStrCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Импорт спецификации коммерческого предложения из xls файла.
	 */
	function importFromXls() {
		$data = $this->ProcessInputData('importFromXls', true);

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

		$response = $this->WhsDocumentSupplySpec_model->importFromXls($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		unlink($fileFullName);
		return true;
	}
}