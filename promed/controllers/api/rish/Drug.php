<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPrescr - контроллер API для работы с лекарствами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Maksim Sysolin
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Drug extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Drug_model', 'dbmodel');
	}

	protected  $inputRules = array(
		'loadDrugComplexMnnList' => array(
			array('field' => 'Date', 'label' => 'Дата', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'fromReserve', 'label' => 'Флаг поиска в резерве врача', 'rules' => '', 'type' => 'int'),
			array('field' => 'query', 'label' => 'Наименование комплексного МНН', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'ReceptType_Code', 'label' => 'Код типа рецепта', 'rules' => '', 'type' => 'id'),
			array('default' => 'start', 'field' => 'mode', 'label' => 'Режим поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnRecept_IsKEK', 'label' => 'Протокол ВК', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnRecept_IsMnn', 'label' => 'Выписка по МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'withOptions', 'label' => 'Учитывать глобальные настройки', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'is_mi_1', 'label' => 'МИ-1', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'paging', 'label' => 'пэйджинг', 'rules' => '', 'type' => 'checkbox', 'default' => false )
		),
		'mLoadDrugRlsList' => array(
			array('field' => 'Date', 'label' => 'Дата', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Drug_rlsid', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Наименование комплексного МНН', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'ReceptType_Code', 'label' => 'Код типа рецепта', 'rules' => '', 'type' => 'id'),
			array('default' => 'start', 'field' => 'mode', 'label' => 'Режим поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnRecept_IsMnn', 'label' => 'Выписка по МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugOstatRegistry_id', 'label' => 'Разнорядка МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnRecept_IsKEK', 'label' => 'Протокол ВК', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'is_mi_1', 'label' => 'МИ-1', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id')
		),
		'mLoadFarmacyRlsOstatList' => array(
			array('field' => 'Drug_rlsid', 'label' => 'Идентификатор медикамента', 'rules' => 'trim|required', 'type' => 'id'),
			array('field' => 'OrgFarmacy_id', 'label' => 'Идентификатор аптеки', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'ReceptType_Code', 'label' => 'Код типа рецепта', 'rules' => 'trim|required', 'type' => 'int'),
			array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id')
		),
		'mLoadDrugList' => array(
			array(
				'field' => 'Date',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Drug_DoseCount',
				'label' => 'Дозировка',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'Drug_DoseQ',
				'label' => 'Дозировка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Drug_DoseUEEi',
				'label' => 'Непонятная хрень',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Drug_Fas',
				'label' => 'Количество',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Drug_id',
				'label' => 'Идентификатор медикамента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Drug_CodeG',
				'label' => 'Код ГЕС',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugFormGroup_id',
				'label' => 'Группа формы выпуска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugMnn_id',
				'label' => 'Идентификатор МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestRow_id',
				'label' => 'Идентификатор строки заявки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestRow_IsReserve',
				'label' => 'Признак выписки медикамента из резерва',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnRecept_Is7Noz_Code',
				'label' => 'Код признака 7 нозологий',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 'all', // варианты: all, request
				'field' => 'mode',
				'label' => 'Режим поиска',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Наименование медикамента',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'ReceptFinance_Code',
				'label' => 'Код типа финансирования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ReceptType_Code',
				'label' => 'Код типа рецепта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Код категории',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RequestDrug_id',
				'label' => 'Идентификатор медикамента из заявки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'is_mi_1',
				'label' => 'МИ-1',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'default' => '1',
				'field' => 'DopRequest',
				'label' => 'Дополнительная заявка',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 *	Получение списка комплексных МНН
	 */
	function mGetDrugComplexMnnList_get() {

		$data = $this->ProcessInputData('loadDrugComplexMnnList', false, true);
		if ( $data === false ) { return false; }

		$this->load->helper('Options');
		$options = $this->dbmodel->getGlobalOptions();

		$response = $this->dbmodel->loadDrugComplexMnnList($data, $options['globals']);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 *	Получение списка комплексных МНН
	 */
	function mLoadDrugRlsList_get() {//echo 'mLoadDrugRlsList_get()';

		if (!empty($this->_args['ReceptFinance_Code'])) {

			$this->mLoadDrugList_get();

		} else {
			$data = $this->ProcessInputData('mLoadDrugRlsList', false, true);
			//echo json_encode($data, JSON_UNESCAPED_UNICODE);
			if ( $data === false ) { return false; }

			if (empty($data['Drug_rlsid']) && (empty($data['ReceptType_Code']) || empty($data['Date'])) ) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}

			$this->load->helper('Options');
			$options = $this->dbmodel->getGlobalOptions();

			$response = $this->dbmodel->loadDrugRlsList($data, $options['globals']);
			$this->response(array('error_code' => 0, 'data' => $response));
		}
	}

	/**
	 *	Список аптек, в которых есть медикамент
	 */
	function mLoadFarmacyRlsOstatList_get() {

		$data = $this->ProcessInputData('mLoadFarmacyRlsOstatList', false, true);
		if ( $data === false ) { return false; }

		$this->load->helper('Options');
		$options = $this->dbmodel->getGlobalOptions();

		$response = $this->dbmodel->loadFarmacyRlsOstatList($data, $options['globals']);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение справочника торговых наименований медикаментов
	 */
	function mLoadDrugList_get() {

		$data = $this->ProcessInputData('mLoadDrugList', null, true);
		$this->load->model('Dlo_EvnRecept_model');

		if (!(isset($data['DopRequest']) && $data['DopRequest'] == 2)) {
			if((!isset($data['Drug_id'])) && ((!isset($data['ReceptFinance_Code'])) /*|| (!isset($data['ReceptType_Code'])) || (!isset($data['Date']))*/)) {
				return false;
			}
		}

		$resp = $this->Dlo_EvnRecept_model->loadDrugList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}
}

