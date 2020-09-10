<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* RlsDrug - операции с медикаментами (для схемы rls)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DlO
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @originalauthors       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru), Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      23.01.2012
*/

/**
 * @property RlsDrug_model $dbmodel
 */
class RlsDrug extends swController {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("RlsDrug_model", "dbmodel");
		
		$this->inputRules = array(
			'loadDrugList' => array(
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование медикамента', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Drug_Ean', 'label' => 'Код ЕАН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'onlyMnn', 'label' => 'Только по МНН', 'rules' => 'trim', 'type' => 'checkbox', 'default' => true),
				array('field' => 'findByLatName', 'label' => 'Поиск по LatName', 'rules' => 'trim', 'type' => 'checkbox', 'default' => false)
			),
			'loadDrugMnnList' => array(
				array('field' => 'DrugMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование МНН', 'rules' => 'trim', 'type' => 'string')
			),
			'loadDrugComplexMnnList' => array(
				array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска медикамента по МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'isFromDocumentUcOst', 'label' => 'Только на остатках отделения', 'rules' => 'trim', 'type' => 'checkbox'),
				array('field' => 'isFromCentralStorageOst', 'label' => 'Из остатков центрального склада', 'rules' => 'trim', 'type' => 'checkbox'),
				array('field' => 'hasDrugComplexMnnCode', 'label' => 'Имеет код комплексного МНН', 'rules' => 'trim', 'type' => 'checkbox'),
				array('field' => 'needFas', 'label' => 'Нужна ли фасовка', 'rules' => 'trim', 'type' => 'checkbox'),
				array('field' => 'UserLpuSection_id', 'label' => 'Идентификатор отделения пользователя', 'rules' => '', 'type' => 'id')
			),
			'loadDrugTorgList' => array(
				array('field' => 'DrugTorg_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор контракта', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка запроса', 'rules' => 'trim', 'type' => 'string')
			),
			'loadDrugSimpleList' => array(
				array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска медикамента по МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'isFromDocumentUcOst', 'label' => 'Из остатков центрального склада', 'rules' => 'trim', 'type' => 'checkbox'),
				array('field' => 'isFromCentralStorageOst', 'label' => 'Только на остатках отделения', 'rules' => 'trim', 'type' => 'checkbox'),
				array('field' => 'UserLpuSection_id', 'label' => 'Идентификатор отделения пользователя', 'rules' => '', 'type' => 'id'),
				array('field' => 'getCountOnly', 'label' => 'Посчитать только каунт', 'rules' => '', 'type' => 'id')
			),
			'loadDrugNomenSimpleList' => array(
				array('field' => 'DrugNomen_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска медикамента', 'rules' => 'trim', 'type' => 'string')
			),
			'loadFirmNamesList' => array(
				array('field' => 'FIRMNAMES_ID', 'label' => 'Идентификатор производителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование производителя', 'rules' => 'trim', 'type' => 'string')
			),
			'loadDrugPackList' => array(
				array('field' => 'DRUGPACK_ID', 'label' => 'Идентификатор упаковки', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Наименование упаковки', 'rules' => 'trim', 'type' => 'string')
			),
			'loadFullOstatList' => array(
				array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id')
			),
			'loadFullReceptList' => array(
				array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id')
			),
			'loadPrepSeriesList' => array(
				array('field' => 'Prep_id', 'label' => 'Препарат', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'ЛС', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Номер серии препарата', 'rules' => 'trim', 'type' => 'string')
			),
			'loadPrepBlockCauseList' => array(
				array('field' => 'PrepBlockCause_id', 'label' => 'Идентификатор причины блокировки', 'rules' => '', 'type' => 'id'),
			),
			'loadPrepBlockCauseGrid' => array(
				array('field' => 'PrepBlockCause_id', 'label' => 'Идентификатор причины блокировки', 'rules' => '', 'type' => 'id'),
				array('field' => 'start', 'label' => '', 'rules' => 'Номер стартовой записи', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => '', 'rules' => 'Количество записей', 'type' => 'int', 'default' => 100),
			),
			'savePrepBlockCause' => array(
				array('field' => 'PrepBlockCause_id', 'label' => 'Идентификатор причины блокировки', 'rules' => '', 'type' => 'id'),
				array('field' => 'PrepBlockCause_Code', 'label' => 'Код причины блокировки', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'PrepBlockCause_Name', 'label' => 'Причина блокировки', 'rules' => 'required|trim', 'type' => 'string')
			),
			'deletePrepBlockCause' => array(
				array('field' => 'PrepBlockCause_id', 'label' => 'Идентификатор причины блокировки', 'rules' => 'required', 'type' => 'id'),
			),
			'getPrepBlockCauseCode' => array(
				array('field' => 'PrepBlockCause_id', 'label' => 'Идентификатор причины блокировки', 'rules' => '', 'type' => 'id'),
			),
			'loadPrepBlockGrid' => array(
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'Actmatters_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'RlsClsdrugforms_id', 'label' => 'Идентификатор лекарственной формы', 'rules' => '', 'type' => 'id'),
				array('field' => 'RlsCountries_id', 'label' => 'Идентификатор страны производителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_Dose', 'label' => 'Дозировка', 'rules' => '', 'type' => 'string'),
				array('field' => 'Drug_Fas', 'label' => 'Фасовка', 'rules' => '', 'type' => 'string'),
				array('field' => 'Prep_RegNum', 'label' => 'Номер регистрационного удостоверения', 'rules' => '', 'type' => 'string'),
				array('field' => 'PrepSeries_Ser', 'label' => 'Номер серии препарата', 'rules' => '', 'type' => 'string'),
				array('field' => 'PrepBlockCause_id', 'label' => 'Идентификатор причины блокировки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocNormative_Num', 'label' => 'Номер нормативного документа', 'rules' => '', 'type' => 'string'),
				array('field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'int', 'default' => 50),
			),
			'loadPrepBlockForm' => array(
				array('field' => 'PrepBlock_id', 'label' => 'Идентификатор блокировки ЛС', 'rules' => 'required', 'type' => 'id'),
			),
			'savePrepBlock' => array(
				array('field' => 'PrepBlock_id', 'label' => 'Идентификатор блокировки ЛС', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id', 'label' => 'Идентификатор торгового наименования ЛС', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PrepSeries_id', 'label' => 'Идентификатор серии препарата', 'rules' => '', 'type' => 'id'),
				array('field' => 'PrepSeries_Ser', 'label' => 'Серия препарата', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'PrepBlockCause_id', 'label' => 'Идентификатор причины блокировки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PrepBlock_begDate', 'label' => 'Дата начала блокировки', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'PrepBlock_endDate', 'label' => 'Дата окончания блокировки', 'rules' => '', 'type' => 'date'),
				array('field' => 'PrepBlock_Comment', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
				array('field' => 'DocNormativeList', 'label' => 'Список нормативных документов', 'rules' => 'required', 'type' => 'string'),
			),
			'deletePrepBlock' => array(
				array('field' => 'PrepBlock_id', 'label' => 'Идентификатор блокировки ЛС', 'rules' => 'required', 'type' => 'id'),
			),
			'loadJNVLPPriceGrid' => array(
				array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
				array('field' => 'ActMatters_RusName', 'label' => 'МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Prep_Name', 'label' => 'Торговое наименование', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugMarkup_Delivery', 'label' => 'Зона', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugForm_Name', 'label' => 'Форма выпуска', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'IsNarko', 'label' => 'Наркотика', 'rules' => 'trim', 'type' => 'string')
			),
			'exportJNVLPPrice' => array(
				array('field' => 'ActMatters_RusName', 'label' => 'МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Prep_Name', 'label' => 'Торговое наименование', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugMarkup_Delivery', 'label' => 'Зона', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugForm_Name', 'label' => 'Форма выпуска', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'IsNarko', 'label' => 'Наркотика', 'rules' => 'trim', 'type' => 'string')
			),
			'loadActmatters' => [
				['field' => 'Actmatters_id', 'label' => 'Идентификатор Действующие вещества', 'rules' => 'required', 'type' => 'id']
			],
			'saveActmatters' => [
				['field' => 'Actmatters_id', 'label' => 'Идентификатор Действующие вещества', 'rules' => '', 'type' => 'id'],
				['field' => 'Actmatters_Names', 'label' => 'Наименование (рус)', 'rules' => 'trim', 'type' => 'string'],
				['field' => 'Actmatters_LatName', 'label' => 'Наименование (лат. им.п.)', 'rules' => 'required|trim', 'type' => 'string'],
				['field' => 'Actmatters_LatNameGen', 'label' => 'Наименование (лат. род.п.)', 'rules' => 'required|trim', 'type' => 'string'],
				['field' => 'Actmatters_StrongGroupID', 'label' => 'Сильнодействующее, группа', 'rules' => '', 'type' => 'id'],
				['field' => 'Actmatters_NarcoGroupID', 'label' => 'Наркосодержащее, группа', 'rules' => '', 'type' => 'id'],
				['field' => 'Actmatters_isMNN', 'label' => 'Включено в классификацию МНН', 'rules' => '', 'type' => 'int'],
				['field' => 'changeRusName', 'label' => 'Было ли изменено рус наименование ?', 'rules' => '', 'type' => 'int']
			]
		);
	}

	/**
	*  Получение справочника медикаментов
	*  Входящие данные: $_POST['DrugNomen_id'],
	*                   $_POST['query']
	*  На выходе: JSON-строка
	*  Используется: sw.Promed.swDrugPanel 
	*/
	function loadDrugNomenSimpleList() {
		$data = $this->ProcessInputData('loadDrugNomenSimpleList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugNomenSimpleList($data);			
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*  Получение справочника МНН медикаментов
	*  Используется: sw.Promed.swDrugPanel 
	*/
	function loadDrugComplexMnnList()
	{
		$data = $this->ProcessInputData('loadDrugComplexMnnList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugComplexMnnList($data, false);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение справочника МНН медикаментов
	 *  Используется: sw.Promed.swEvnPrescrDrugMnnSearchWindow
	 */
	function loadDrugComplexMnnListWithPaging()
	{
		$data = $this->ProcessInputData('loadDrugComplexMnnList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugComplexMnnList($data, true);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Получение справочника торговых наименований медикаментов
	 */
	function loadDrugTorgList()
	{
		$data = $this->ProcessInputData('loadDrugTorgList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugTorgList($data, false);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 *  Получение справочника медикаментов
	 *  Используется: sw.Promed.swDrugPanel
	 */
	function loadDrugSimpleList()
	{
		$data = $this->ProcessInputData('loadDrugSimpleList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugSimpleList($data, false);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 *  Получение справочника медикаментов
	 *  Используется: sw.Promed.swEvnPrescrDrugTorgSearchWindow
	 */
	function loadDrugSimpleListWithPaging()
	{
		$data = $this->ProcessInputData('loadDrugSimpleList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugSimpleList($data, true);
			
			if (!empty($data['getCountOnly'])) {
				$this->ProcessModelSave($response, true)->ReturnData();
			} else {
				$this->ProcessModelMultiList($response, true, true)->ReturnData();
				return true;
			}
			
		} else {
			return false;
		}
	}
	
	/**
	*  Получение справочника торговых наименований медикаментов
	*  Входящие данные: $_POST['Drug_id'],
	*                   $_POST['DrugMnn_id'],
	*                   $_POST['query']
	*  На выходе: JSON-строка
	*  Используется: форма поиска АРМ фармацевта и оп. склада
	*/
	function loadDrugList() {
		$data = $this->ProcessInputData('loadDrugList', true);
		if ($data) {
			$response = $this->dbmodel->searchFullDrugList($data);			
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadDrugMnnList() {
		$data = $this->ProcessInputData('loadDrugMnnList', true);
		if ($data) {
			$response = $this->dbmodel->searchFullDrugMnnList($data);			
			$this->ProcessModelList($response, true, true)->ReturnData();			
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadFirmNamesList() {
		$data = $this->ProcessInputData('loadFirmNamesList', true);
		if ($data) {
			$response = $this->dbmodel->searchFullFirmNamesList($data);			
			$this->ProcessModelList($response, true, true)->ReturnData();			
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadDrugPackList() {
		$data = $this->ProcessInputData('loadDrugPackList', true);
		if ($data) {
			$response = $this->dbmodel->searchFullDrugPackList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();			
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadFullOstatList() {
		$data = $this->ProcessInputData('loadFullOstatList', true);
		if ($data) {
			$filter = $data;
			$response = $this->dbmodel->loadFullOstatList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadFullReceptList() {
		$data = $this->ProcessInputData('loadFullReceptList', true);
		if ($data) {
			$filter = $data;
			$response = $this->dbmodel->loadFullReceptList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка цен на ЖНВЛП
	 */
	function loadJNVLPPriceGrid() {
		$data = $this->ProcessInputData('loadJNVLPPriceGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadJNVLPPriceGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Получения списка зон из справочника предельных наценок
	 */
	function loadDrugMarkupDeliveryList() {
		$response = $this->dbmodel->loadDrugMarkupDeliveryList();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Возвращает список серий препаратов
	 */
	function loadPrepSeriesList() {
		$data = $this->ProcessInputData('loadPrepSeriesList', true);
		if ($data) {
			$response = $this->dbmodel->loadPrepSeriesList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список причин блокировки серий препаратов
	 */
	function loadPrepBlockCauseList() {
		$data = $this->ProcessInputData('loadPrepBlockCauseList', true);
		if ($data) {
			$response = $this->dbmodel->loadPrepBlockCauseList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список причин блокировки серий препаратов
	 */
	function loadPrepBlockCauseGrid() {
		$data = $this->ProcessInputData('loadPrepBlockCauseGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadPrepBlockCauseGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет причину блокировки серий препаратов
	 */
	function savePrepBlockCause() {
		$data = $this->ProcessInputData('savePrepBlockCause', true);
		if ($data) {
			$response = $this->dbmodel->savePrepBlockCause($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаляет причину блокировки серий препаратов
	 */
	function deletePrepBlockCause() {
		$data = $this->ProcessInputData('deletePrepBlockCause', true);
		if ($data) {
			$response = $this->dbmodel->deletePrepBlockCause($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение кода для причины блокировки серий препаратов
	 */
	function getPrepBlockCauseCode() {
		$data = $this->ProcessInputData('getPrepBlockCauseCode', true);
		if ($data) {
			$response = $this->dbmodel->getPrepBlockCauseCode($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Возрашает список блокировок серий препаратов
	 */
	function loadPrepBlockGrid() {
		$data = $this->ProcessInputData('loadPrepBlockGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadPrepBlockGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные для формы редактирования блокировки серии препарата
	 */
	function loadPrepBlockForm() {
		$data = $this->ProcessInputData('loadPrepBlockForm', true);
		if ($data) {
			$response = $this->dbmodel->loadPrepBlockForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет блокировку серии препарата
	 */
	function savePrepBlock() {
		$data = $this->ProcessInputData('savePrepBlock', true);
		if ($data) {
			$response = $this->dbmodel->savePrepBlock($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаляет блокировку серии препарата
	 */
	function deletePrepBlock() {
		$data = $this->ProcessInputData('deletePrepBlock', true);
		if ($data) {
			$response = $this->dbmodel->deletePrepBlock($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Экспорт цен на ЖНВЛП в формате CSV
	 */
	function exportJNVLPPrice() {
		$data = $this->ProcessInputData('exportJNVLPPrice', true);
		if ($data === false) { return false; }

		$data['export'] = true;
		$response = $this->dbmodel->loadJNVLPPriceGrid($data);
		if( !is_array($response) || count($response) == 0 ) {
			DieWithError("Нет данных для экспорта");
		}

		set_time_limit(0);

		if(!is_dir(EXPORTPATH_JNVLP_PRICE)) {
			if (!mkdir(EXPORTPATH_JNVLP_PRICE)) {
				DieWithError("Ошибка при создании директории ".EXPORTPATH_JNVLP_PRICE."!");
			}
		}

		$f_name = "jnvlp_price";
		$file_name = EXPORTPATH_JNVLP_PRICE.$f_name.".csv";
		$archive_name = EXPORTPATH_JNVLP_PRICE.$f_name.".zip";
		if( is_file($archive_name) ) {
			unlink($archive_name);
		}

		try {
			$h = fopen($file_name, 'w');
			if(!$h) {
				DieWithError("Ошибка при попытке открыть файл!");
			}
			$str_result = "";
			$str_result .= "МНН;";
			$str_result .= "ЛП;";
			$str_result .= "Форма выпуска;";
			$str_result .= "Дозировка;";
			$str_result .= "Фасовка;";
			$str_result .= "№ РУ;";
			$str_result .= "Производитель;";
			$str_result .= "Дата рег.цены;";
			$str_result .= "№ решения;";
			$str_result .= "Зарег.цена произв. (руб.);";
			$str_result .= "Зона;";
			$str_result .= "Опт. надб.;";
			$str_result .= "Опт. цена;";
			$str_result .= "Опт. цена с НДС;";
			$str_result .= "Розн. надб.;";
			$str_result .= "Розн. цена;";
			$str_result .= "Розн. цена с НДС\n";

			$patterns = array('/&alpha;/u', '/&beta;/u', '/&mdash;|&ndash;/u');
			$replacements = array('a', 'b', '-');

			foreach($response as $row) {
				$str_result .= str_replace(';','',$row['ActMatters_RusName']).";";
				$str_result .= str_replace(';','',preg_replace($patterns,$replacements,html_entity_decode(htmlspecialchars_decode(strip_tags($row['Prep_Name']))))).";";
				$str_result .= str_replace(';','',$row['DrugForm_Name']).";";
				$str_result .= str_replace(';','',$row['Drug_Dose']).";";
				$str_result .= str_replace(';','',$row['Drug_Fas']).";";
				$str_result .= str_replace(';','',$row['Reg_Num']).";";
				$str_result .= str_replace(';','',preg_replace($patterns,$replacements,html_entity_decode(htmlspecialchars_decode(strip_tags($row['Firm_Name']))))).";";
				$str_result .= str_replace(';','',$row['Price_Date']).";";
				$str_result .= str_replace(';','',$row['Price_Order']).";";
				$str_result .= str_replace('.',',',$row['Price']).";";
				$str_result .= str_replace(';','',$row['Drugmarkup_Delivery']).";";
				$str_result .= str_replace('.',',',$row['Wholesale_Markup']).";";
				$str_result .= str_replace('.',',',$row['Wholesale_Price']).";";
				$str_result .= str_replace('.',',',$row['Wholesale_NdsPrice']).";";
				$str_result .= str_replace('.',',',$row['Retail_Markup']).";";
				$str_result .= str_replace('.',',',$row['Retail_Price']).";";
				$str_result .= str_replace('.',',',$row['Retail_NdsPrice'])."\n";
			}

			$str_result = toAnsi($str_result, true);

			fwrite($h, $str_result);
			fclose($h);

			$zip = new ZipArchive();
			$zip->open($archive_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_name, basename($file_name));
			$zip->close();
			unlink($file_name);

			$this->ReturnData(array('success' => true, 'url' => $archive_name));
		} catch (Exception $e) {
			DieWithError($e->getMessage());
			$this->ReturnData(array('success' => false));
		}

		if(is_file($file_name)) {
			@unlink($file_name);
		}

	}

	/**
	 *  Получение справочника торговых наименований медикаментов
	 *  Входящие данные: $_POST['Drug_id'],
	 *                   $_POST['DrugMnn_id'],
	 *                   $_POST['query']
	 *  На выходе: JSON-строка
	 *  Используется: форма добавления лек. назначения Ext6,
	 */
	function loadDrugMNNNameList() {
		$data = $this->ProcessInputData('loadDrugList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->searchByNameInMnnAndDrugList($data);

		if(sizeof($response) == 0) return false;

		$this->ProcessModelList( $response, true)->ReturnData();
	}

	/** Загрузка записи из справочнике Действующих веществ по id */
	function loadActmatters() {
		$data = $this->ProcessInputData('loadActmatters', true);
		if ($data) {
			$response = $this->dbmodel->loadActmatters($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/** Добавление новой записи или обновление по id в справочнике Действующих веществ */
	function saveActmatters() {
		$data = $this->ProcessInputData('saveActmatters', true);
		if ($data) {
			/// Доп проверки и валидация
			// Если не существует Actmatters_id, то идет добавление нового действующего
			if ( empty($data['Actmatters_id']) ) {
				// Проверяем на существование
				if ( $this->dbmodel->checkActmatters($data) ) {
					$this->ReturnData([
						'success' => false,
						'Error_Msg' => toUtf("В справочнике уже есть такое действующее вещество.<br>Сохранение невозможно.")
					]);
					return false;
				}
				// endpoint один на добавление и обновление, Actmatters_Names не всегда передается
				// На фронте проверка есть, тут больше от умников и багов каких
				// При добавление нового действующего имя на рус не должно быть пустым
				if ( empty($data['Actmatters_Names']) ) {
					$this->ReturnData([
						'success' => false,
						'Error_Msg' => toUtf("Заполните поле Наименование (рус).<br>Сохранение невозможно.")
					]);
					return false;
				}
			} else {
				// Если рус имя при редактировании было изменено, проверяем на дубль
				if ( $data['changeRusName'] && $this->dbmodel->checkActmatters($data) ) {
					$this->ReturnData([
						'success' => false,
						'Error_Msg' => toUtf("В справочнике уже есть такое действующее вещество.<br>Сохранение невозможно.")
					]);
					return false;
				}
			}
			/// Фиксация данных
			$response = $this->dbmodel->saveActmatters($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
