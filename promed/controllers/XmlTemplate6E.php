<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * XmlTemplate6E - контроллер для работы с шаблонами из форм на ExtJS6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.03.2018
 *
 * @property XmlTemplate6E_model $dbmodel
 */

class XmlTemplate6E extends swController {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->inputRules = array(
			'loadXmlTemplateList' => array(
				array('field' => 'query', 'label' => 'Запрос', 'rules' => '', 'type' => 'string'),
				array('field' => 'mode', 'label' => 'Режим', 'rules' => '', 'type' => 'string'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
			),
			'loadXmlTemplateComboList' => array(
				array('field' => 'query', 'label' => 'Запрос', 'rules' => '', 'type' => 'string'),
				array('field' => 'mode', 'label' => 'Режим', 'rules' => '', 'type' => 'string'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			),
			'loadXmlTemplateTree' => array(
				array('field' => 'id', 'label' => 'ID папки', 'rules' => '', 'type' => 'string'),
				array('field' => 'mode', 'label' => 'Режим', 'rules' => '', 'type' => 'string'),
				array('field' => 'node', 'label' => 'Родительская нода', 'rules' => '', 'type' => 'string'),
				array('field' => 'query', 'label' => 'Строка запроса', 'rules' => '', 'type' => 'string'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTypeKind_id', 'label' => 'Идентификатор вида документа одного типа', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_sid', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_sid', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'onlyFolders', 'label' => 'Флаг "Только папки"', 'rules' => '', 'type' => 'checkbox')
			),
			'getXmlTemplateForEvnXml' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор вида события', 'rules' => '', 'type' => 'id'),
				array('field' => 'reset', 'label' => 'reset', 'rules' => '', 'type' => 'checkbox'),
				array('field' => 'loadEmpty', 'label' => 'loadEmpty', 'rules' => '', 'type' => 'checkbox'),
			),
			'loadXmlDataSectionList' => array(
				array('field' => 'sysNicks', 'label' => 'sysNicks', 'rules' => '', 'type' => 'json_array'),
			),
			'loadParameterValueList' => array(
				array('field' => 'ParameterValue_Name', 'label' => 'Наименование параметра', 'rules' => '', 'type' => 'string'),
				array('field' => 'sysNicks', 'label' => 'sysNicks', 'rules' => '', 'type' => 'json_array'),
			),
			'loadParameterValueForm' => array(
				array('field' => 'ParameterValue_id', 'label' => 'Идентификатор параметра', 'rules' => 'required', 'type' => 'id'),
			),
			'saveParameterValue' => array(
				array('field' => 'ParameterValue_id', 'label' => 'Идентификатор параметра', 'rules' => '', 'type' => 'id'),
				array('field' => 'ParameterValue_Name', 'label' => 'Наименование для печати', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'ParameterValue_Alias', 'label' => 'Наименование параметра', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'ParameterValueListType_id', 'label' => 'Идентификатор типа списка значений', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplateScope_id', 'label' => 'Идентификатор видимости параметра', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ParameterValueList', 'label' => 'Список значений', 'rules' => 'trim|required', 'type' => 'string'),
			),
			'deleteParameterValue' => array(
				array('field' => 'ParameterValue_id', 'label' => 'Идентификатор параметра', 'rules' => 'required', 'type' => 'id'),
			),
			'loadSpecMarkerList' => array(
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Запрос', 'rules' => '', 'type' => 'string'),
				array('field' => 'mode', 'label' => 'Режим', 'rules' => '', 'type' => 'string'),
				array('field' => 'names', 'label' => 'Наименования', 'rules' => '', 'type' => 'json_array'),
			),
			'getSpecMarkerContent' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'SpecMarkerIds', 'label' => 'SpecMarkerIds', 'rules' => 'required|trim', 'type' => 'json_array'),
			),
			'getMarkerContent' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
				array('field' => 'markerData', 'label' => 'markerData', 'rules' => 'required|trim', 'type' => 'string'),
			),
			'getNewXmlTemplateCaption' => array(

			),
			'getNewXmlTemplateCatName' => array(
				array('field' => 'XmlTemplateCat_pid', 'label' => 'Идентификатор родительской папки', 'rules' => '', 'type' => 'id'),
			),
			'saveXmlTemplate' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplate_Caption', 'label' => 'Название шаблона', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'XmlTemplate_Descr', 'label' => 'Описание шаблона', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'XmlTemplate_HtmlTemplate', 'label' => 'Шаблон', 'rules' => '', 'type' => 'string'),
				array('field' => 'EvnXml_Data', 'label' => 'Данные шаблона по умолчанию', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'EvnXml_DataSettings', 'label' => 'Данные шаблона по умолчанию', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор категории', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplateScope_id', 'label' => 'Идентификатор видимости шабона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplateSettings_id', 'label' => 'Идентификатор настройки печати', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'mode', 'label' => 'Режим сохранения', 'rules' => 'required', 'type' => 'string'),
			),
			'loadXmlTemplateProperties' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
			),
			'deleteXmlTemplate' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
			),
			'saveXmlTemplateCat' => array(
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_pid', 'label' => 'Идентификатор родительской папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_Name', 'label' => 'Название папки', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			),
			'deleteXmlTemplateCat' => array(
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => 'required', 'type' => 'id'),
			),
			'moveXmlTemplate' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
			),
			'copyXmlTemplate' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
			),
			'renameXmlTemplateItem' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'name', 'label' => 'Наименование', 'rules' => 'required|trim', 'type' => 'string'),
			),
			'getXmlTemplatePath' => array(
				array('field' => 'mode', 'label' => 'Режим', 'rules' => '', 'type' => 'string'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			),
			'getXmlTemplateCatPath' => array(
				array('field' => 'node', 'label' => 'Режим', 'rules' => '', 'type' => 'string'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			),
			'setXmlTemplateDefault' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор категории', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'ignoreCheckSetDefault', 'label' => 'Игнорирование проверки', 'rules' => '', 'type' => 'checkbox'),
			),
			'unsetXmlTemplateDefault' => array(
				array('field' => 'XmlTemplateDefault_id', 'label' => 'Идентификатор шаблона по умолчанию', 'rules' => 'required', 'type' => 'id'),
			),
			'toggleXmlTemplateSelected' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'operation', 'label' => 'Operation', 'rules' => 'trim', 'type' => 'string'),
			),
			'loadPMUserForShareList' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Запрос', 'rules' => 'trim', 'type' => 'string'),
			),
			'shareXmlTemplate' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'shareTo', 'label' => 'JSON-строка', 'rules' => 'required|trim', 'type' => 'string'),
			),
			'deleteXmlTemplateShared' => array(
				array('field' => 'XmlTemplateShared_id', 'label' => 'Идентификатор отправленного шаблона', 'rules' => 'required', 'type' => 'id'),
			),
			'getXmlTemplateSharedUnreadCount' => array(),
			'setXmlTemplateSharedIsReaded' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
			),
			'getAnketMarkerContent' => array(
				array('field' => 'MedicalFormPerson_id', 'label' => 'Идентификатор анкеты', 'rules'=>'', 'type' => 'id')
			),
		);

		$this->load->database();
		$this->load->model('XmlTemplate6E_model', 'dbmodel');
	}

	/**
	 * @return bool
	 */
	function loadXmlTemplateList() {
		$data = $this->ProcessInputData('loadXmlTemplateList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadXmlTemplateList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadXmlTemplateComboList() {
		$data = $this->ProcessInputData('loadXmlTemplateComboList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadXmlTemplateComboList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadXmlTemplateTree() {
		$data = $this->ProcessInputData('loadXmlTemplateTree', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadXmlTemplateTree($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function getXmlTemplateForEvnXml() {
		$data = $this->ProcessInputData('getXmlTemplateForEvnXml', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getXmlTemplateForEvnXml($data);
		if ($this->dbmodel->isSuccessful($response)) {
			$response[0]['Alert_Msg'] = $this->dbmodel->getAlertMsg();
		}
		$this->ProcessModelSave($response, true, 'При получении шаблона произошла ошибка')->ReturnData();
		return true;
	}

	/**
	 * Получение списка облстей ввода данных для шаблона
	 * @return bool
	 */
	function loadXmlDataSectionList() {
		$data = $this->ProcessInputData('loadXmlDataSectionList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadXmlDataSectionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение параметров для вставки в шаблон
	 * @return bool
	 */
	function loadParameterValueList() {
		$data = $this->ProcessInputData('loadParameterValueList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadParameterValueList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных параметра для редактирования
	 * @return bool
	 */
	function loadParameterValueForm() {
		$data = $this->ProcessInputData('loadParameterValueForm', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadParameterValueForm($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Сохрание данных параметра
	 * @return bool
	 */
	function saveParameterValue() {
		$data = $this->ProcessInputData('saveParameterValue', true);
		if ($data === false) return false;

		$response = $this->dbmodel->saveParameterValue($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление параметра
	 * @return bool
	 */
	function deleteParameterValue() {
		$data = $this->ProcessInputData('deleteParameterValue', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deleteParameterValue($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка спецмаркеров
	 * @return bool
	 */
	function loadSpecMarkerList() {
		$data = $this->ProcessInputData('loadSpecMarkerList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadSpecMarkerList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение контента для спецмаркеров
	 * @return bool
	 */
	function getSpecMarkerContent() {
		$data = $this->ProcessInputData('getSpecMarkerContent', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getSpecMarkerContent($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение контента маркера документа
	 * @return bool
	 */
	function getMarkerContent() {
		$data = $this->ProcessInputData('getMarkerContent', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getMarkerContent($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение названия по умолчанию для нового шаблона
	 * @return bool
	 */
	function getNewXmlTemplateCaption() {
		$data = $this->ProcessInputData('getNewXmlTemplateCaption', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getNewXmlTemplateCaption($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение названия по умолчанию для новой папки
	 * @return bool
	 */
	function getNewXmlTemplateCatName() {
		$data = $this->ProcessInputData('getNewXmlTemplateCatName', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getNewXmlTemplateCatName($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение шаблона
	 * @return bool
	 */
	function saveXmlTemplate() {
		$data = $this->ProcessInputData('saveXmlTemplate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->saveXmlTemplate($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function deleteXmlTemplate() {
		$data = $this->ProcessInputData('deleteXmlTemplate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deleteXmlTemplate($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function saveXmlTemplateCat() {
		$data = $this->ProcessInputData('saveXmlTemplateCat', true);
		if ($data === false) return false;

		$response = $this->dbmodel->saveXmlTemplateCat($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function deleteXmlTemplateCat() {
		$data = $this->ProcessInputData('deleteXmlTemplateCat', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deleteXmlTemplateCatWithChildren($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadXmlTemplateProperties() {
		$data = $this->ProcessInputData('loadXmlTemplateProperties', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadXmlTemplateProperties($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Перемещение шаблона
	 * @return bool
	 */
	function moveXmlTemplate() {
		$data = $this->ProcessInputData('moveXmlTemplate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->moveXmlTemplate($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Копирование шаблона
	 * @return bool
	 */
	function copyXmlTemplate() {
		$data = $this->ProcessInputData('copyXmlTemplate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->copyXmlTemplate($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Переименование шаблонов и папок
	 * @return bool
	 */
	function renameXmlTemplateItem() {
		$data = $this->ProcessInputData('renameXmlTemplateItem', true);
		if ($data === false) return false;

		$response = $this->dbmodel->renameXmlTemplateItem($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение пути до шаблона
	 * @return bool
	 */
	function getXmlTemplatePath() {
		$data = $this->ProcessInputData('getXmlTemplatePath', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getXmlTemplatePath($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение пути до папки
	 * @return bool
	 */
	function getXmlTemplateCatPath() {
		$data = $this->ProcessInputData('getXmlTemplateCatPath', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getXmlTemplateCatPath($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool]
	 */
	function setXmlTemplateDefault() {
		$data = $this->ProcessInputData('setXmlTemplateDefault', true);
		if ($data === false) return false;

		$response = $this->dbmodel->setXmlTemplateDefault($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function unsetXmlTemplateDefault() {
		$data = $this->ProcessInputData('unsetXmlTemplateDefault', true);
		if ($data === false) return false;

		$response = $this->dbmodel->unsetXmlTemplateDefault($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function toggleXmlTemplateSelected() {
		$data = $this->ProcessInputData('toggleXmlTemplateSelected', true);
		if ($data === false) return false;

		$response = $this->dbmodel->toggleXmlTemplateSelected($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadPMUserForShareList() {
		$data = $this->ProcessInputData('loadPMUserForShareList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPMUserForShareList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function shareXmlTemplate() {
		$data = $this->ProcessInputData('shareXmlTemplate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->shareXmlTemplate($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function deleteXmlTemplateShared() {
		$data = $this->ProcessInputData('deleteXmlTemplateShared', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deleteXmlTemplateShared($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function getXmlTemplateSharedUnreadCount() {
		$data = $this->ProcessInputData('getXmlTemplateSharedUnreadCount', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getXmlTemplateSharedUnreadCount($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function setXmlTemplateSharedIsReaded() {
		$data = $this->ProcessInputData('setXmlTemplateSharedIsReaded', true);
		if ($data === false) return false;

		$response = $this->dbmodel->setXmlTemplateSharedIsReaded($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 * @return {MedicalForm: [], MedicalFormData: []}
	 */
	function getAnketMarkerContent() {
		$data = $this->ProcessInputData('getAnketMarkerContent', true);
		if ($data === false) return false;
		if(!empty($data['MedicalFormPerson_id'])) {
			$response = $this->dbmodel->getAnketMarkerContent(array($data['MedicalFormPerson_id']));
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		} else
			return false;
	}
}