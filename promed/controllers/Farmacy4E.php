<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Farmacy - методы работы для модуля "Аптека"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      28.12.2009
*/

class Farmacy4E extends swController {

	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
        $this->load->database();
		$this->inputRules = array(
			'loadDrugPrepList' => array(
				array(
					'field' => 'DrugPrep_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Текущая строка документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Фасовка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrep_Name',
					'label' => 'Наименование медикамента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'date',
					'label' => 'Дата документа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'expenditure',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_Kolvo',
					'label' => 'Количество',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => '',
					'field' => 'load',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'string'
				),
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
				)
			),

			'loadDrugList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Комбобокс "Медикамент"',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Расходная строка документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_Code',
					'label' => 'Код медикамента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Name',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'date',
					'label' => 'Дата документа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'default' => 'expenditure',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'checking_exp_date',
					'label' => 'Учет срока годности',
					'rules' => '',
					'type' => 'string'
				),
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
				)
			),
			
			'loadDrugMultiList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Комбобокс "Медикамент"',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTorg_Name',
					'label' => 'Торговое наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugForm_Name',
					'label' => 'Форма выпуска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_PackName',
					'label' => 'Упаковка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Dose',
					'label' => 'Дозировка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Firm',
					'label' => 'Производитель',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'expenditure',
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Contragent_id',
					'label' => 'Контрагент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			)
		);
	}

	/**
	 * Получение списка медикаментов, доступных для отоваривания
	 * Используется в окне редактирования товарной позиции
	 */
	function loadDrugList() {
		$this->load->model("Farmacy_model4E", "dbmodel");

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadDrugList', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadDrugList($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка медикаментов (DrugPrepList), доступных для выбора
	 * Используется в окне редактирования товарной позиции (первый комбобокс)
	 */
	function loadDrugPrepList() {
		$this->load->model("Farmacy_model4E", "dbmodel");
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadDrugPrepList', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadDrugPrepList($data);

        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	*  Функция чтения списка медикаментов 
	*  На выходе: JSON-строка
	*  Используется: форма поиска медикаментов
	*/
	function loadDrugMultiList() {
		$this->load->model("Farmacy_model4E", 'dbmodel');

		$data = $this->ProcessInputData('loadDrugMultiList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugMultiList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}