<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusPalliat - контроллер для MorbusPalliat
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      10.2012
 *
 * @property MorbusPalliat_model $dbmodel
 * @property EvnNotifyPalliat_model $EvnNotifyPalliat_model
 */

class MorbusPalliat extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		'load' => array(
			array(
				'field' => 'MorbusPalliat_id',
				'label' => 'Идентификатор специфики заболевания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'MorbusPalliat_id',
				'label' => 'Идентификатор специфики заболевания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Morbus_id',
				'label' => 'Идентификатор заболевания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusPalliat_IsIVL',
				'label' => 'Нуждается в ИВЛ',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'MorbusPalliat_IsZond',
				'label' => 'Находится на зондовом питании',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ViolationsDegreeType_id',
				'label' => 'Степень выраженности стойких нарушений организма',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AnesthesiaType_id',
				'label' => 'Нуждается в обезболивании',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'МО оказания паллиативной помощи (стац)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_aid',
				'label' => 'МО оказания паллиативной помощи (амб)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusPalliat_DiagDate',
				'label' => 'Дата установки диагноза',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusPalliat_DisDetDate',
				'label' => 'Дата в необходимости установления инвалидности',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'RecipientInformation_id',
				'label' => 'Информирован о заболевании',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusPalliat_IsFamCare',
				'label' => 'Наличие родственников, имеющих возможность осуществлять уход за пациентом',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'PalliatFamilyCare',
				'label' => 'Сведения о родственниках, осуществляющих уход за пациентом',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PalliativeType_id',
				'label' => 'Условия оказания паллиативной помощи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusPalliat_VKDate',
				'label' => 'Дата проведения ВК',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusPalliat_StomPrescrDate',
				'label' => 'Дата назначения установки Стомы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusPalliat_StomSetDate',
				'label' => 'Дата установки Стомы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusPalliat_VLDateRange',
				'label' => 'Период оказания респираторной поддержки',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'MethodRaspiratAssist',
				'label' => 'Метод респираторной поддержки',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'MedProductCard',
				'label' => 'Оборудование',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'MorbusPalliat_IsTIR',
				'label' => 'Необходимость обеспечения ТСР, медицинскими изделиями',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'MorbusPalliat_VKTIRDate',
				'label' => 'Дата проведения ВК по ТСР',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusPalliat_TIRDate',
				'label' => 'Дата обеспечения ТСР',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'TechnicInstrumRehab_id',
				'label' => 'Наименование ТСР',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MorbusPalliat_TextTIR',
				'label' => 'Наименование ТСР',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PalliatIndicatChangeCondit_id',
				'label' => 'Показания к изменению условий оказания паллиативной медицинской помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusPalliat_OtherIndicatChangeCondit',
				'label' => 'Другие показания',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'MorbusPalliat_ChangeConditDate',
				'label' => 'Дата перевода в учреждение соц. защиты',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusPalliat_SocialProtDate',
				'label' => 'Дата перевода в учреждение соц. защиты',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusPalliat_SocialProt',
				'label' => 'Учреждение соц. защиты',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MainSyndrome',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'TechnicInstrumRehab',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
		),
		'loadPalliatFamilyCareList' => array(
			array(
				'field' => 'MorbusPalliat_id',
				'label' => 'MorbusPalliat_id',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadLpuList' => array(
			array(
				'field' => 'PalliativeType_id',
				'label' => 'Условия оказания паллиативной помощи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Date',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			)
		),
		'loadMainSyndromeList' => array(
		),
		'loadMedProductCardList' => array(
			array(
				'field' => 'Lpu_did',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'query',
				'rules' => '',
				'type' => 'string'
			),
		),
		'getIdForEmk' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'checkCanInclude' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getDirectionMSE' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		)
    );

	/**
	 * Description
	 */
	function __construct () 
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('MorbusPalliat_model', 'dbmodel');
	}

	/**
	 * Загрузка формы редактирования записи регистра
	 */
	function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение формы редактирования записи регистра
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);

		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function loadPalliatFamilyCareList() {
		$data = $this->ProcessInputData('loadPalliatFamilyCareList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPalliatFamilyCareList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function loadLpuList() {
		$data = $this->ProcessInputData('loadLpuList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function loadMainSyndromeList() {
		$data = $this->ProcessInputData('loadMainSyndromeList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMainSyndromeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function loadMedProductCardList() {
		$data = $this->ProcessInputData('loadMedProductCardList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedProductCardList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function getIdForEmk() {
		$data = $this->ProcessInputData('getIdForEmk');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getIdForEmk($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function checkCanInclude() {
		$data = $this->ProcessInputData('checkCanInclude');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkCanInclude($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * @return bool
	 */
	function getDirectionMSE() {
		$data = $this->ProcessInputData('getDirectionMSE');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDirectionMSE($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}