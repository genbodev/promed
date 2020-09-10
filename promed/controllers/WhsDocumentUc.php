<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * WhsDocumentUc - контроллер для работы с документами учета
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.03.2016
 *
 * @property WhsDocumentUc_model dbmodel
 */

class WhsDocumentUc extends swController {
	protected  $inputRules = array(
		'generateWhsDocumentUcNum' => array(
			array('field' => 'WhsDocumentType_Code', 'label' => 'Код типа документа', 'rules' => 'required', 'type' => 'int'),
		),
		'loadEvnCourseTreatList' => array(
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
		),
		'loadReceptOtovList' => array(
			array('field' => 'DrugComplexMnn_id','label' => 'Идентификатор комплексного МНН','rules' => '','type' => 'id'),
			array('field' => 'Org_id','label' => 'Идентификатор организации аптеки','rules' => '','type' => 'id'),
		),
		'saveWhsDocumentUc' => array(
			array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentSpecificity_id', 'label' => 'Идентификатор специфики документа учета', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentClass_id', 'label' => 'Идентификатор класса документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentClass_Code', 'label' => 'Код класса документа', 'rules' => '', 'type' => 'id'),
			//array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер документа учета', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'WhsDocumentUc_Date', 'label' => 'Дата документа учета', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'WhsDocumentUc_Sum', 'label' => 'Сумма стоимости медикаментов', 'rules' => '', 'type' => 'float'),
			array('field' => 'WhsDocumentStatusType_id', 'label' => 'Статус документа учета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_sid', 'label' => 'Идентфикатор организации-заявителя', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_tid', 'label' => 'Идентфикатор организации-поставщика', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Storage_sid', 'label' => 'Идентфикатор склада-заявителя', 'rules' => '', 'type' => 'id'),
			array('field' => 'Storage_tid', 'label' => 'Идентфикатор склада-поставщика', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mol_sid', 'label' => 'Идентфикатор МОЛ склада-заявителя', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентфикатор контракта', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugFinance_id', 'label' => 'Идентфикатор источника финансирования', 'rules' => '','type' => 'id'),
			array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Идентфикатор статьи расхода', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentSpecificationJSON', 'label' => 'Спецификация медикаментов в документе', 'rules' => '','type' => 'string'),
		),
		'loadWhsDocumentUcForm' => array(
			array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => 'required', 'type' => 'id')
		),
		'loadWhsDocumentSpecificationGrid' => array(
			array('field' => 'WhsDocumentSpecificity_id', 'label' => 'Идентификатор специфики документа учета', 'rules' => 'required', 'type' => 'id')
		),
		'createWhsDocumentSpecificationByWhsDocumentSupply' => array(
			array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор контракта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'type' => 'id'),
		),
		'createWhsDocumentSpecificationByPrescr' => array(
			array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugFinance_id', 'label' => 'Идентификатор источника финансирования', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Идентификатор статьи расхода', 'rules' => '', 'type' => 'id'),
			array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор контракта', 'rules' => '', 'type' => 'id')
		),
		'createWhsDocumentSpecificationByDrugOstatRegistry' => array(
			array('field' => 'DrugOstatRegistryJSON', 'label' => 'Список записей регистра остатков', 'rules' => 'required', 'type' => 'json_array'),
		),
        'executeWhsDocumentSpecificity' => array(
            array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id')
        ),
        'cancelWhsDocumentSpecificity' => array(
            array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
        ),
        'deleteWhsDocumentSpecificity' => array(
            array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
        ),
		'loadGoodsUnitCombo' => array(
			array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного мнн', 'rules' => '', 'type' => 'id'),
			array('field' => 'Tradnames_id', 'label' => 'Идентификатор торгововго наименования', 'rules' => '', 'type' => 'id'),
			array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'id'),
			array('field' => 'UserOrg_Type', 'label' => 'Тип организации пользователя', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
		),
        'loadOrgCombo' => array(
            array('field' => 'Org_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'OrgType_id', 'label' => 'Тип организации', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        )
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('WhsDocumentUc_model', 'dbmodel');
	}

	/**
	 * Получение сгенерированного номера для документа учета
	 */
	function generateWhsDocumentUcNum() {
		$data = $this->ProcessInputData('generateWhsDocumentUcNum', false);
		if ($data) {
			$response = $this->dbmodel->generateWhsDocumentUcNum($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Получение списка лекарственных назначений
	 */
	function loadEvnCourseTreatList() {
		$data = $this->ProcessInputData('loadEvnCourseTreatList', false);
		if ($data) {
			$response = $this->dbmodel->loadEvnCourseTreatList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Получение списка рецептов из реестра рецептов аптеки
	 */
	function loadReceptOtovList() {
		$data = $this->ProcessInputData('loadReceptOtovList', false);
		if ($data) {
			$response = $this->dbmodel->loadReceptOtovList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Сохранение документа учета
	 */
	function saveWhsDocumentUc() {
		$data = $this->ProcessInputData('saveWhsDocumentUc', true);
		if ($data) {
			$response = $this->dbmodel->saveWhsDocumentUc($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Получение данных для редактирования докумкента учета
	 */
	function loadWhsDocumentUcForm() {
		$data = $this->ProcessInputData('loadWhsDocumentUcForm', true);
		if ($data) {
			$response = $this->dbmodel->loadWhsDocumentUcForm($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Получение спецификации медикаментов в документе учета
	 */
	function loadWhsDocumentSpecificationGrid() {
		$data = $this->ProcessInputData('loadWhsDocumentSpecificationGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadWhsDocumentSpecificationGrid($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Формирование спецификации медикаментов на основе контракта
	 */
	function createWhsDocumentSpecificationByWhsDocumentSupply() {
		$data = $this->ProcessInputData('createWhsDocumentSpecificationByWhsDocumentSupply', true);
		if ($data) {
			$response = $this->dbmodel->createWhsDocumentSpecificationByWhsDocumentSupply($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Формирование спецификации медикаментов на основе назначений лекарственного лечения
	 */
	function createWhsDocumentSpecificationByPrescr() {
		$data = $this->ProcessInputData('createWhsDocumentSpecificationByPrescr', true);
		if ($data) {
			$response = $this->dbmodel->createWhsDocumentSpecificationByPrescr($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

	/**
	 * Формирование спецификации медикаментов из остатков поставщика
	 */
	function createWhsDocumentSpecificationByDrugOstatRegistry() {
		$data = $this->ProcessInputData('createWhsDocumentSpecificationByDrugOstatRegistry', false);
		if ($data) {
			$response = $this->dbmodel->createWhsDocumentSpecificationByDrugOstatRegistry($data);
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else {
			return true;
		}
	}

    /**
     * Исполнение накладной-требования
     */
    function executeWhsDocumentSpecificity() {
        $this->load->model("DocumentUc_model", "DocumentUc_model");

        $data = $this->ProcessInputData('executeWhsDocumentSpecificity', true);
        if ($data) {
            //генерируем номер для нового документа
            $response = $this->DocumentUc_model->generateDocumentUcNum(array(
                'Contragent_id' => $data['Contragent_id'],
                'DrugDocumentType_Code' => 15 // 15 - Накладная на внутренее перемещение
            ));
            $data['DocumentUc_NewNum'] = null;
            if (is_array($response) && isset($response[0]) && !empty($response[0]['DocumentUc_Num'])) {
                $data['DocumentUc_NewNum'] = $response[0]['DocumentUc_Num'];
            }

            $response = $this->dbmodel->executeWhsDocumentSpecificity($data);
            $this->ProcessModelSave($response)->ReturnData();
            return true;
        } else {
            return true;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
	function loadGoodsUnitCombo() {
		$data = $this->ProcessInputData('loadGoodsUnitCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadGoodsUnitCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

    /**
     * Загрузка списка для комбобокса
     */
    function loadOrgCombo() {
        $data = $this->ProcessInputData('loadOrgCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadOrgCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }
}
