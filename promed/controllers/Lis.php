<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * Lis - контроллер обмена данными с ЛИС-сервисом
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Lis
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Markoff Andrew <markov@swan.perm.ru>
 * @version      10.2011
 *
 * @property CI_DB_driver $db
 * @property Textlog $textlog
 * @property Options_model $Options_model
 * @property Lis_model $dbmodel
 * @property EvnLabSample_model $EvnLabSample_model
 */
class Lis extends swController
{

	public $server = [];//редактируются через форму "Общие настройки", умолчания прописаны в Options_model.php
	public $map = [
		// Оказывается у нас таблицы называются не так, как в ЛИС справочники, что грустно. 
		// Пришлось ввести параметр table 
		"target" => ["table" => "Target", "name" => "Target_Name", "code" => "Target_Code", "mnemonics" => "Target_SysNick", "id" => "Target_id", "removed" => "Target_Deleted", "pmUser_id" => "pmUser_id"],
		"category" => ["table" => "Category", "name" => "Category_Name", "code" => "Category_Code", "mnemonics" => "Category_SysNick", "id" => "Category_id", "removed" => "Category_Deleted", "pmUser_id" => "pmUser_id"],
		"requestCustomState" => ["table" => "CustomState", "name" => "CustomState_Name", "code" => "CustomState_Code", "mnemonics" => "CustomState_SysNick", "id" => "CustomState_id", "removed" => "CustomState_Deleted", "pmUser_id" => "pmUser_id"],
		"defectState" => ["table" => "defectState", "name" => "defectState_Name", "code" => "defectState_Code", "mnemonics" => "defectState_SysNick", "id" => "defectState_id", "removed" => "defectState_Deleted", "pmUser_id" => "pmUser_id"],
		"priority" => ["table" => "priority", "name" => "priority_Name", "code" => "priority_Code", "mnemonics" => "priority_SysNick", "id" => "priority_id", "removed" => "priority_Deleted", "pmUser_id" => "pmUser_id"],
		"states" => ["table" => "states", "name" => "states_Name", "code" => "states_Code", "mnemonics" => "states_SysNick", "id" => "states_id", "removed" => "states_Deleted", "pmUser_id" => "pmUser_id"],
		"sex" => ["table" => "sex", "name" => "sex_Name", "code" => "sex_Code", "mnemonics" => "sex_SysNick", "id" => "sex_id", "removed" => "sex_Deleted", "pmUser_id" => "pmUser_id"],
		"profile" => ["table" => "profile", "name" => "profile_Name", "code" => "profile_Code", "mnemonics" => "profile_SysNick", "id" => "profile_id", "removed" => "profile_Deleted", "pmUser_id" => "pmUser_id"],
		"targetGroup" => ["table" => "targetGroup", "name" => "targetGroup_Name", "code" => "targetGroup_Code", "mnemonics" => "targetGroup_SysNick", "id" => "targetGroup_id", "removed" => "targetGroup_Deleted", "pmUser_id" => "pmUser_id"],
		"formLayout" => ["table" => "formLayout", "name" => "formLayout_Name", "code" => "formLayout_Code", "mnemonics" => "formLayout_SysNick", "id" => "formLayout_id", "removed" => "formLayout_Deleted", "pmUser_id" => "pmUser_id"],
		"storage" => ["table" => "storage", "name" => "storage_Name", "code" => "storage_Code", "mnemonics" => "storage_SysNick", "id" => "storage_id", "removed" => "storage_Deleted", "pmUser_id" => "pmUser_id"],
		"printFormUnit" => ["table" => "printFormUnit", "name" => "printFormUnit_Name", "code" => "printFormUnit_Code", "mnemonics" => "printFormUnit_SysNick", "id" => "printFormUnit_id", "removed" => "printFormUnit_Deleted", "pmUser_id" => "pmUser_id"],
		"externalSystem" => ["table" => "externalSystem", "name" => "externalSystem_Name", "code" => "externalSystem_Code", "mnemonics" => "externalSystem_SysNick", "id" => "externalSystem_id", "removed" => "externalSystem_Deleted", "pmUser_id" => "pmUser_id"],
		"commentSource" => ["table" => "commentSource", "name" => "commentSource_Name", "code" => "commentSource_Code", "mnemonics" => "commentSource_SysNick", "id" => "commentSource_id", "removed" => "commentSource_Deleted", "pmUser_id" => "pmUser_id"],
		"printForm" => ["table" => "printForm", "name" => "printForm_Name", "code" => "printForm_Code", "mnemonics" => "printForm_SysNick", "id" => "printForm_id", "removed" => "printForm_Deleted", "pmUser_id" => "pmUser_id"],
		"pricelist" => ["table" => "pricelist", "name" => "pricelist_Name", "code" => "pricelist_Code", "mnemonics" => "pricelist_SysNick", "id" => "pricelist_id", "removed" => "pricelist_Deleted", "pmUser_id" => "pmUser_id"],
		"userRule" => ["table" => "userRule", "name" => "userRule_Name", "code" => "userRule_Code", "mnemonics" => "userRule_SysNick", "id" => "userRule_id", "removed" => "userRule_Deleted", "pmUser_id" => "pmUser_id"],
		"scanForm" => ["table" => "scanForm", "name" => "scanForm_Name", "code" => "scanForm_Code", "mnemonics" => "scanForm_SysNick", "id" => "scanForm_id", "removed" => "scanForm_Deleted", "pmUser_id" => "pmUser_id"],
		"streetType" => ["table" => "streetType", "name" => "streetType_Name", "code" => "streetType_Code", "mnemonics" => "streetType_SysNick", "id" => "streetType_id", "removed" => "streetType_Deleted", "pmUser_id" => "pmUser_id"],
		"sampleBlank" => ["table" => "sampleBlank", "name" => "sampleBlank_Name", "code" => "sampleBlank_Code", "mnemonics" => "sampleBlank_SysNick", "id" => "sampleBlank_id", "removed" => "sampleBlank_Deleted", "pmUser_id" => "pmUser_id"],
		"patient" => ["table" => "patient", "name" => "patient_Name", "code" => "patient_Code", "mnemonics" => "patient_SysNick", "id" => "patient_id", "removed" => "patient_Deleted", "pmUser_id" => "pmUser_id"],
		"worklistDefGroup" => ["table" => "worklistDefGroup", "name" => "worklistDefGroup_Name", "code" => "worklistDefGroup_Code", "mnemonics" => "worklistDefGroup_SysNick", "id" => "worklistDefGroup_id", "removed" => "worklistDefGroup_Deleted", "pmUser_id" => "pmUser_id"],
		"qcTestGroup" => ["table" => "qcTestGroup", "name" => "qcTestGroup_Name", "code" => "qcTestGroup_Code", "mnemonics" => "qcTestGroup_SysNick", "id" => "qcTestGroup_id", "removed" => "qcTestGroup_Deleted", "pmUser_id" => "pmUser_id"],
		"testPrintGroup" => ["table" => "testPrintGroup", "name" => "testPrintGroup_Name", "code" => "testPrintGroup_Code", "mnemonics" => "testPrintGroup_SysNick", "id" => "testPrintGroup_id", "removed" => "testPrintGroup_Deleted", "pmUser_id" => "pmUser_id"],
		"equipmentTestGroups" => ["table" => "equipmentTestGroups", "name" => "equipmentTestGroups_Name", "code" => "equipmentTestGroups_Code", "mnemonics" => "equipmentTestGroups_SysNick", "id" => "equipmentTestGroups_id", "removed" => "equipmentTestGroups_Deleted", "pmUser_id" => "pmUser_id"],
		"doctor" => ["table" => "doctor", "name" => "doctor_Name", "code" => "doctor_Code", "mnemonics" => "doctor_SysNick", "id" => "doctor_id", "removed" => "doctor_Deleted", "pmUser_id" => "pmUser_id"],
		"myelogramm" => ["table" => "myelogramm", "name" => "myelogramm_Name", "code" => "myelogramm_Code", "mnemonics" => "myelogramm_SysNick", "id" => "myelogramm_id", "removed" => "myelogramm_Deleted", "pmUser_id" => "pmUser_id"],
		"policyType" => ["table" => "policyType", "name" => "policyType_Name", "code" => "policyType_Code", "mnemonics" => "policyType_SysNick", "id" => "policyType_id", "removed" => "policyType_Deleted", "pmUser_id" => "pmUser_id"],
		"printFormNew" => ["table" => "printFormNew", "name" => "printFormNew_Name", "code" => "printFormNew_Code", "mnemonics" => "printFormNew_SysNick", "id" => "printFormNew_id", "removed" => "printFormNew_Deleted", "pmUser_id" => "pmUser_id"],
		"constant" => ["table" => "constant", "name" => "constant_Name", "code" => "constant_Code", "mnemonics" => "constant_SysNick", "id" => "constant_id", "removed" => "constant_Deleted", "pmUser_id" => "pmUser_id"],
		"requestForm" => ["table" => "requestForm", "name" => "requestForm_Name", "code" => "requestForm_Code", "mnemonics" => "requestForm_SysNick", "id" => "requestForm_id", "removed" => "requestForm_Deleted", "pmUser_id" => "pmUser_id"],
		"requestFormLayout" => ["table" => "requestFormLayout", "name" => "requestFormLayout_Name", "code" => "requestFormLayout_Code", "mnemonics" => "requestFormLayout_SysNick", "id" => "requestFormLayout_id", "removed" => "requestFormLayout_Deleted", "pmUser_id" => "pmUser_id"],
		"defaultPrintForm" => ["table" => "defaultPrintForm", "name" => "defaultPrintForm_Name", "code" => "defaultPrintForm_Code", "mnemonics" => "defaultPrintForm_SysNick", "id" => "defaultPrintForm_id", "removed" => "defaultPrintForm_Deleted", "pmUser_id" => "pmUser_id"],
		"hospital" => ["table" => "hospital", "name" => "hospital_Name", "code" => "hospital_Code", "mnemonics" => "hospital_SysNick", "id" => "hospital_id", "removed" => "hospital_Deleted", "pmUser_id" => "pmUser_id"],
		"bioMaterial" => ["table" => "bioMaterial", "name" => "bioMaterial_Name", "code" => "bioMaterial_Code", "mnemonics" => "bioMaterial_SysNick", "id" => "bioMaterial_id", "removed" => "bioMaterial_Deleted", "pmUser_id" => "pmUser_id"],
		"userGraphics" => ["table" => "userGraphics", "name" => "userGraphics_Name", "code" => "userGraphics_Code", "mnemonics" => "userGraphics_SysNick", "id" => "userGraphics_id", "removed" => "userGraphics_Deleted", "pmUser_id" => "pmUser_id"],
		"material" => ["table" => "material", "name" => "material_Name", "code" => "material_Code", "mnemonics" => "material_SysNick", "id" => "material_id", "removed" => "material_Deleted", "pmUser_id" => "pmUser_id"],
		"userGroup" => ["table" => "userGroup", "name" => "userGroup_Name", "code" => "userGroup_Code", "mnemonics" => "userGroup_SysNick", "id" => "userGroup_id", "removed" => "userGroup_Deleted", "pmUser_id" => "pmUser_id"],
		"userField" => ["table" => "userField", "name" => "userField_Name", "code" => "userField_Code", "mnemonics" => "userField_SysNick", "id" => "userField_id", "removed" => "userField_Deleted", "pmUser_id" => "pmUser_id"],
		"qcEvent" => ["table" => "qcEvent", "name" => "qcEvent_Name", "code" => "qcEvent_Code", "mnemonics" => "qcEvent_SysNick", "id" => "qcEvent_id", "removed" => "qcEvent_Deleted", "pmUser_id" => "pmUser_id"],
		"userDirectory" => ["table" => "userDirectory", "name" => "userDirectory_Name", "code" => "userDirectory_Code", "mnemonics" => "userDirectory_SysNick", "id" => "userDirectory_id", "removed" => "userDirectory_Deleted", "pmUser_id" => "pmUser_id"],
		"unit" => ["table" => "unit", "name" => "unit_Name", "code" => "unit_Code", "mnemonics" => "unit_SysNick", "id" => "unit_id", "removed" => "unit_Deleted", "pmUser_id" => "pmUser_id"],
		"userDirectoryValue" => ["table" => "userDirectoryValue", "name" => "userDirectoryValue_Name", "code" => "userDirectoryValue_Code", "mnemonics" => "userDirectoryValue_SysNick", "id" => "userDirectoryValue_id", "removed" => "userDirectoryValue_Deleted", "pmUser_id" => "pmUser_id"],
		"accessRight" => ["table" => "accessRight", "name" => "accessRight_Name", "code" => "accessRight_Code", "mnemonics" => "accessRight_SysNick", "id" => "accessRight_id", "removed" => "accessRight_Deleted", "pmUser_id" => "pmUser_id"],
		"test" => ["table" => "test", "name" => "test_Name", "code" => "test_Code", "mnemonics" => "test_SysNick", "id" => "test_id", "removed" => "test_Deleted", "pmUser_id" => "pmUser_id"],
		"requestDistrict" => ["table" => "requestDistrict", "name" => "requestDistrict_Name", "code" => "requestDistrict_Code", "mnemonics" => "requestDistrict_SysNick", "id" => "requestDistrict_id", "removed" => "requestDistrict_Deleted", "pmUser_id" => "pmUser_id"],
		"patientGroup" => ["table" => "patientGroup", "name" => "patientGroup_Name", "code" => "patientGroup_Code", "mnemonics" => "patientGroup_SysNick", "id" => "patientGroup_id", "removed" => "patientGroup_Deleted", "pmUser_id" => "pmUser_id"],
		"hospitalCategory" => ["table" => "hospitalCategory", "name" => "hospitalCategory_Name", "code" => "hospitalCategory_Code", "mnemonics" => "hospitalCategory_SysNick", "id" => "hospitalCategory_id", "removed" => "hospitalCategory_Deleted", "pmUser_id" => "pmUser_id"],
		"qcProducer" => ["table" => "qcProducer", "name" => "qcProducer_Name", "code" => "qcProducer_Code", "mnemonics" => "qcProducer_SysNick", "id" => "qcProducer_id", "removed" => "qcProducer_Deleted", "pmUser_id" => "pmUser_id"],
		"customReport" => ["table" => "customReport", "name" => "customReport_Name", "code" => "customReport_Code", "mnemonics" => "customReport_SysNick", "id" => "customReport_id", "removed" => "customReport_Deleted", "pmUser_id" => "pmUser_id"],
		"qcMaterial" => ["table" => "qcMaterial", "name" => "qcMaterial_Name", "code" => "qcMaterial_Code", "mnemonics" => "qcMaterial_SysNick", "id" => "qcMaterial_id", "removed" => "qcMaterial_Deleted", "pmUser_id" => "pmUser_id"],
		"service" => ["table" => "service", "name" => "service_Name", "code" => "service_Code", "mnemonics" => "service_SysNick", "id" => "service_id", "removed" => "service_Deleted", "pmUser_id" => "pmUser_id"],
		"materialUnit" => ["table" => "materialUnit", "name" => "materialUnit_Name", "code" => "materialUnit_Code", "mnemonics" => "materialUnit_SysNick", "id" => "materialUnit_id", "removed" => "materialUnit_Deleted", "pmUser_id" => "pmUser_id"],
		"report" => ["table" => "report", "name" => "report_Name", "code" => "report_Code", "mnemonics" => "report_SysNick", "id" => "report_id", "removed" => "report_Deleted", "pmUser_id" => "pmUser_id"],
		"city" => ["table" => "city", "name" => "city_Name", "code" => "city_Code", "mnemonics" => "city_SysNick", "id" => "city_id", "removed" => "city_Deleted", "pmUser_id" => "pmUser_id"],
		"cityType" => ["table" => "cityType", "name" => "cityType_Name", "code" => "cityType_Code", "mnemonics" => "cityType_SysNick", "id" => "cityType_id", "removed" => "cityType_Deleted", "pmUser_id" => "pmUser_id"],
		"street" => ["table" => "street", "name" => "street_Name", "code" => "street_Code", "mnemonics" => "street_SysNick", "id" => "street_id", "removed" => "street_Deleted", "pmUser_id" => "pmUser_id"],
		"customCommand" => ["table" => "customCommand", "name" => "customCommand_Name", "code" => "customCommand_Code", "mnemonics" => "customCommand_SysNick", "id" => "customCommand_id", "removed" => "customCommand_Deleted", "pmUser_id" => "pmUser_id"],
		"treeViewLayout" => ["table" => "treeViewLayout", "name" => "treeViewLayout_Name", "code" => "treeViewLayout_Code", "mnemonics" => "treeViewLayout_SysNick", "id" => "treeViewLayout_id", "removed" => "treeViewLayout_Deleted", "pmUser_id" => "pmUser_id"],
		"printFormHeader" => ["table" => "printFormHeader", "name" => "printFormHeader_Name", "code" => "printFormHeader_Code", "mnemonics" => "printFormHeader_SysNick", "id" => "printFormHeader_id", "removed" => "printFormHeader_Deleted", "pmUser_id" => "pmUser_id"],
		"serviceShort" => ["table" => "serviceShort", "name" => "serviceShort_Name", "code" => "serviceShort_Code", "mnemonics" => "serviceShort_SysNick", "id" => "serviceShort_id", "removed" => "serviceShort_Deleted", "pmUser_id" => "pmUser_id"],
		"supplier" => ["table" => "supplier", "name" => "supplier_Name", "code" => "supplier_Code", "mnemonics" => "supplier_SysNick", "id" => "supplier_id", "removed" => "supplier_Deleted", "pmUser_id" => "pmUser_id"],
		"testRule" => ["table" => "testRule", "name" => "testRule_Name", "code" => "testRule_Code", "mnemonics" => "testRule_SysNick", "id" => "testRule_id", "removed" => "testRule_Deleted", "pmUser_id" => "pmUser_id"],
		"payCategory" => ["table" => "payCategory", "name" => "payCategory_Name", "code" => "payCategory_Code", "mnemonics" => "payCategory_SysNick", "id" => "payCategory_id", "removed" => "payCategory_Deleted", "pmUser_id" => "pmUser_id"],
	];
	public $debug = false;
	public $log_to = "textlog";//Куда выводить лог: textlog|print_r|false
	// Список справочников разрешенный для загрузки
	public $dirs = [
		"targetgroup",
		"target",
		"formlayout",
		"storage",
		"printformunit",
		"externalsystem",
		"commentsource",
		"printform",
		"pricelist",
		"userrule",
		"scanform",
		"qctestgroup",
		"testprintgroup",
		"doctor",
		"myelogramm",
		"constant",
		"requestform",
		"defaultprintform",
		"hospital",
		"biomaterial",
		"usergraphics",
		"material",
		"userfield",
		"qcevent",
		"userdirectory",
		"unit",
		"accessright",
		"test",
		"patientgroup",
		"hospitalcategory",
		"qcproducer",
		"customreport",
		"qcmaterial",
		"materialunit",
		"report",
		"printformheader",
		"supplier",
		"testrule",
		"paycategory",
		"biomaterial",
		"defectstate"
	];

	public $inputRules = [
		"getLisFolder" => [],
		"getGroups" => [],
		"login" => [],
		"getDirectoryVersions" => [],
		"createRequest2" => [],
		"getDirectory" => [
			["field" => "name", "label" => "Справочник", "rules" => "required", "type" => "string"]
		],
		"testXml" => [
			["field" => "xml", "label" => "Xml", "rules" => "", "type" => "string"],
			["field" => "server", "label" => "Server", "rules" => "", "type" => "string"]
		],
		"saveLisRequestFromLabSamples" => [
			["field" => "EvnLabSample_idsJSON", "label" => "EvnLabSample_idsJSON", "rules" => "required", "type" => "string"]
		],
		"createRequestSelections" => [
			["field" => "EvnLabSample_id", "label" => "EvnLabSample_id", "rules" => "", "type" => "string"],
			["field" => "EvnLabSamples", "label" => "Набор проб", "rules" => "", "type" => "string"]
		],
		"createRequestSelectionsLabRequest" => [
			["field" => "EvnLabRequest_id", "label" => "EvnLabRequest_id", "rules" => "", "type" => "string"],
			["field" => "EvnLabRequests", "label" => "Набор заявок", "rules" => "", "type" => "string"]
		],
		"getXml" => [
			["field" => "request_type", "label" => "request_type", "rules" => "required", "type" => "string"]
		],
		"sample" => [
			["field" => "EvnLabSample_id", "label" => "EvnLabSample_id", "rules" => "", "type" => "int"],
			["field" => "EvnLabSamples", "label" => "EvnLabSamples", "rules" => "", "type" => "string"]
		],
		"syncSampleDefects" => [],
		"listRegistrationJournal" => [
			["field" => "nr", "label" => "Номер", "rules" => "", "type" => "string"],
			["field" => "dateFrom", "label" => "Дата поступления заявки «С»", "rules" => "", "type" => "datetime"],
			["field" => "dateTill", "label" => "Дата поступления заявки «По»", "rules" => "", "type" => "datetime"],
			["field" => "endDateFrom", "label" => "Дата закрытия заявки «С»", "rules" => "", "type" => "datetime"],
			["field" => "endDateTill", "label" => "Дата закрытия заявки «По»", "rules" => "", "type" => "datetime"],
			["field" => "lastModificationDateFrom", "label" => "Дата последнего изменения заявки «С»", "rules" => "", "type" => "date"],
			["field" => "priority", "label" => "Приоритет заявки", "rules" => "", "type" => "string"],
			["field" => "defectState", "label" => "Наличие браков в заявке", "rules" => "", "type" => "string"],
			["field" => "payCategories", "label" => "Множество ссылок на справочник Категория оплаты", "rules" => "", "type" => "string", "default" => ""],
			["field" => "lastName", "label" => "Фамилия пациента", "rules" => "", "type" => "string"],
			["field" => "firstName", "label" => "Имя пациента", "rules" => "", "type" => "string"],
			["field" => "middleName", "label" => "Отчество пациента", "rules" => "", "type" => "string"],
			["field" => "birthDate", "label" => "Дата рождения пациента", "rules" => "", "type" => "date"],
			["field" => "sex", "label" => "Пол пациента", "rules" => "", "type" => "string"],
			["field" => "patientNr", "label" => "Номер пациента", "rules" => "", "type" => "string"],
			["field" => "billNr", "label" => "Номер выставленного счета", "rules" => "", "type" => "string"],
			["field" => "states", "label" => "Статусы заявок", "rules" => "", "type" => "string", "default" => ""],
			["field" => "doctors", "label" => "Врачи заказчики", "rules" => "", "type" => "string", "default" => ""],
			["field" => "hospitals", "label" => "Заказчики", "rules" => "", "type" => "string", "default" => ""],
			["field" => "custDepartments", "label" => "Отделения заказчика", "rules" => "", "type" => "string", "default" => ""],
			["field" => "departments", "label" => "Подразделения, в которых выполняется заявка", "rules" => "", "type" => "string", "default" => ""],
			["field" => "targets", "label" => "Исследования, входящие в заявку", "rules" => "", "type" => "string", "default" => ""],
			["field" => "lastTimestamp", "label" => "Метка последнего изменения записи", "rules" => "", "type" => "string", "default" => 0],
			["field" => "customStates", "label" => "Дополнительные статусы заявки", "rules" => "", "type" => "string", "default" => ""],
			["field" => "requestForms", "label" => "Регистрационные формы, при помощи которых создается заявка", "rules" => "", "type" => "string",]
		],
		"saveLisRequest" => [
			["field" => "Id", "label" => "ИД", "rules" => "", "type" => "string"],
			["field" => "InternalNr", "label" => "Номер", "rules" => "", "type" => "string"],
			["field" => "HospitalCode", "label" => "Код ЛПУ", "rules" => "", "type" => "string"],
			["field" => "HospitalName", "label" => "Наименование ЛПУ", "rules" => "", "type" => "string"],
			["field" => "CustDepartmentCode", "label" => "Код подразделения", "rules" => "", "type" => "string"],
			["field" => "CustDepartmentName", "label" => "Наименование подразделения", "rules" => "", "type" => "string"],
			["field" => "DoctorCode", "label" => "Код врача", "rules" => "", "type" => "string"],
			["field" => "DoctorName", "label" => "ФИО врача", "rules" => "", "type" => "string"],
			["field" => "RegistrationFormCode", "label" => "Код формы", "rules" => "", "type" => "string"],
			["field" => "SamplingDate", "label" => "Дата взятия", "rules" => "", "type" => "datetime"],
			["field" => "SampleDeliveryDate", "label" => "Дата доставки", "rules" => "", "type" => "datetime"],
			["field" => "PregnancyDuration", "label" => "Срок беременности", "rules" => "", "type" => "string"],
			["field" => "CyclePeriod", "label" => "Фаза цикла", "rules" => "", "type" => "string"],
			["field" => "ReadOnly", "label" => "Только чтение", "rules" => "", "type" => "string"],
			["field" => "Priority", "label" => "Приоритет", "rules" => "", "type" => "int"],
			["field" => "Code", "label" => "Код пациента", "rules" => "", "type" => "string"],
			["field" => "CardNr", "label" => "Номер карты", "rules" => "", "type" => "string"],
			["field" => "FirstName", "label" => "Имя", "rules" => "", "type" => "string"],
			["field" => "LastName", "label" => "Фамилия", "rules" => "", "type" => "string"],
			["field" => "MiddleName", "label" => "Отчество", "rules" => "", "type" => "string"],
			["field" => "BirthDay", "label" => "Дата рождения", "rules" => "", "type" => "date"],
			["field" => "Sex", "label" => "Пол", "rules" => "", "type" => "string"],
			["field" => "Country", "label" => "Страна", "rules" => "", "type" => "string"],
			["field" => "City", "label" => "Город", "rules" => "", "type" => "string"],
			["field" => "Street", "label" => "Улица", "rules" => "", "type" => "string"],
			["field" => "Building", "label" => "Дом", "rules" => "", "type" => "string"],
			["field" => "Flat", "label" => "Квартира", "rules" => "", "type" => "string"],
			["field" => "InsuranceCompany", "label" => "Страховая компания", "rules" => "", "type" => "string"],
			["field" => "PolicySeries", "label" => "Номер полиса", "rules" => "", "type" => "string"],
			["field" => "PolicyNumber", "label" => "Серия полиса", "rules" => "", "type" => "string"],
			["field" => "Biomaterial", "label" => "Биоматериал", "rules" => "", "type" => "int"],
			["field" => "InternalNrBarCode", "label" => "Штрих-код", "rules" => "", "type" => "string"],
			["field" => "Target", "label" => "Исследование", "rules" => "", "type" => "int"],
			["field" => "Cancel", "label" => "Отмена", "rules" => "", "type" => "string"],
			["field" => "ReadOnly", "label" => "Только чтение", "rules" => "", "type" => "string"]
		]
	];
	public $sessionid = null;
	public $lis_static_dicts = [
		"request_state" => [//статус заявок
			"1" => "Регистрация",
			"2" => "Открыта",
			"3" => "Закрыта",
		],
		"priority" => [//приоритет
			"10" => "Низкий",
			"20" => "Высокий",
		],
		"sex" => [//приоритет
			"0" => "Пол не указан",
			"1" => "Мужской",
			"2" => "Женский",
			"3" => "Не важно",
		]
	];

	/**
	 * Lis constructor.
	 * @throws Exception
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->helper("Xml");
		$this->load->library("textlog", array("file" => "Lis.log"));
		$this->load->model("Options_model", "Options_model");
		if ($this->isLogon()) {
			$this->sessionid = $_SESSION["phox"]["sessionid"];
		}
		try {
			$dbres = $this->Options_model->getDataStorageValues(["DataStorageGroup_SysNick" => "lis"], []);
			$options = [];
			foreach ($dbres as $value) {
				$options[$value["DataStorage_Name"]] = $value["DataStorage_Value"];
			}
			$this->server = [
				"address" => $options["lis_address"],
				"server" => $options["lis_server"],
				"port" => $options["lis_port"],
				"path" => $options["lis_path"],
				"version" => $options["lis_version"],
				"buildnumber" => $options["lis_buildnumber"],
			];
		} catch (Exception $e) {
			throw new Exception("Не удалось получить настройки для ЛИС", 0, $e);
		}
		assert_options(ASSERT_BAIL, 1);
		if ($this->usePostgreLis) {
			$this->load->swapi("lis");
		} else {
			$this->load->database();
			$this->load->model("Lis_model", "dbmodel");
		}
	}

	/**
	 * @param bool $assertation
	 * @param null $message
	 * @throws Exception
	 */
	function assert($assertation, $message = null)
	{
		if (!$assertation) {
			if (is_null($message)) {
				$message = "assertation failed";
			}
			throw new Exception($message);
		}
	}

	function getTablesSql()
	{
		$tpl = "/*CREATE TABLE [lis].[{[_TABLE_NAME_]}]
				(
				[{[_TABLE_NAME_]}_id] [bigint] NOT NULL IDENTITY(1, 1),
				[{[_TABLE_NAME_]}_Code] [varchar] (30) COLLATE Cyrillic_General_CI_AS NOT NULL,
				[{[_TABLE_NAME_]}_Name] [varchar] (50) COLLATE Cyrillic_General_CI_AS NOT NULL,
				[{[_TABLE_NAME_]}_SysNick] [varchar] (50) COLLATE Cyrillic_General_CI_AS NULL,
				[{[_TABLE_NAME_]}_Deleted] [bigint] NULL,
				[pmUser_insID] [bigint] NOT NULL,
				[pmUser_updID] [bigint] NOT NULL,
				[{[_TABLE_NAME_]}_insDT] [datetime] NOT NULL,
				[{[_TABLE_NAME_]}_updDT] [datetime] NOT NULL
				) ON [PRIMARY]
		GO
		ALTER TABLE [lis].[{[_TABLE_NAME_]}] ADD CONSTRAINT [PK_lis.{[_TABLE_NAME_]}] PRIMARY KEY CLUSTERED  ([{[_TABLE_NAME_]}_id]) ON [PRIMARY]
		GO               */
		--AUTOGEN
		--project ProMed
		--insert into 'lis.{[_TABLE_NAME_]}'
		
		create procedure lis.p_{[_TABLE_NAME_]}_ins
		(@{[_TABLE_NAME_]}_id bigint = null output,
		 @{[_TABLE_NAME_]}_Code varchar(30),
		 @{[_TABLE_NAME_]}_Name varchar(50),
		 @{[_TABLE_NAME_]}_SysNick varchar(50) = null,
		 @{[_TABLE_NAME_]}_Deleted bigint = null,
		 @pmUser_id bigint,
		 @Error_Code int = null output,
		 @Error_Message varchar(4000) = null output)
		as
		set nocount on
		
		begin try
		
		begin tran
		
		if @pmUser_id = 0
			set @pmUser_id = null
		
		if isnull(@{[_TABLE_NAME_]}_id, 0) = 0
		begin
			insert into lis.{[_TABLE_NAME_]} with (ROWLOCK) ({[_TABLE_NAME_]}_Code, {[_TABLE_NAME_]}_Name, {[_TABLE_NAME_]}_SysNick, {[_TABLE_NAME_]}_Deleted, pmUser_insID, pmUser_updID, {[_TABLE_NAME_]}_insDT, {[_TABLE_NAME_]}_updDT)
			values (@{[_TABLE_NAME_]}_Code, @{[_TABLE_NAME_]}_Name, @{[_TABLE_NAME_]}_SysNick, @{[_TABLE_NAME_]}_Deleted, @pmUser_id, @pmUser_id, GetDate(), GetDate())
			set @{[_TABLE_NAME_]}_id = (select scope_identity())
		end
		else
		begin
			set identity_insert lis.{[_TABLE_NAME_]} on
			insert into lis.{[_TABLE_NAME_]} with (ROWLOCK) ({[_TABLE_NAME_]}_id, {[_TABLE_NAME_]}_Code, {[_TABLE_NAME_]}_Name, {[_TABLE_NAME_]}_SysNick, {[_TABLE_NAME_]}_Deleted, pmUser_insID, pmUser_updID, {[_TABLE_NAME_]}_insDT, {[_TABLE_NAME_]}_updDT)
			values (@{[_TABLE_NAME_]}_id, @{[_TABLE_NAME_]}_Code, @{[_TABLE_NAME_]}_Name, @{[_TABLE_NAME_]}_SysNick, @{[_TABLE_NAME_]}_Deleted, @pmUser_id, @pmUser_id, GetDate(), GetDate())
		end
		
		commit tran
		
		end try
		
		begin catch
			set @Error_Code = error_number()
			set @Error_Message = error_message()
			if @@trancount>0
				rollback tran
		end catch
		
		set nocount off
		GO
		
		
		SET QUOTED_IDENTIFIER ON
		SET ANSI_NULLS ON
		GO
		--AUTOGEN
		--project ProMed
		--insert into 'lis.{[_TABLE_NAME_]}'
		
		create procedure lis.p_{[_TABLE_NAME_]}_upd
		(@{[_TABLE_NAME_]}_id bigint output,
		 @{[_TABLE_NAME_]}_Code varchar(30),
		 @{[_TABLE_NAME_]}_Name varchar(50),
		 @{[_TABLE_NAME_]}_SysNick varchar(50) = null,
		 @{[_TABLE_NAME_]}_Deleted bigint = null,
		 @pmUser_id bigint,
		 @Error_Code int = null output,
		 @Error_Message varchar(4000) = null output)
		as
		set nocount on
		
		begin try
		
		begin tran
		
		if @pmUser_id = 0
			set @pmUser_id = null
		
		update lis.{[_TABLE_NAME_]} with (ROWLOCK) set
		{[_TABLE_NAME_]}_Code = @{[_TABLE_NAME_]}_Code,
		{[_TABLE_NAME_]}_Name = @{[_TABLE_NAME_]}_Name,
		{[_TABLE_NAME_]}_SysNick = @{[_TABLE_NAME_]}_SysNick,
		{[_TABLE_NAME_]}_Deleted = @{[_TABLE_NAME_]}_Deleted,
		pmUser_updID = @pmUser_id,
		{[_TABLE_NAME_]}_updDT = GetDate()
		where {[_TABLE_NAME_]}_id = @{[_TABLE_NAME_]}_id
		
		commit tran
		
		end try
		
		begin catch
			set @Error_Code = error_number()
			set @Error_Message = error_message()
			if @@trancount>0
				rollback tran
		end catch
		
		set nocount off
		GO
		
		
		
		SET QUOTED_IDENTIFIER ON
		SET ANSI_NULLS ON
		GO
		
		--AUTOGEN
		--project ProMed
		--delete from 'lis.{[_TABLE_NAME_]}'
		
		create procedure lis.p_{[_TABLE_NAME_]}_del
		(@{[_TABLE_NAME_]}_id bigint,
		 @Error_Code int = null output,
		 @Error_Message varchar(4000) = null output)
		as
		set nocount on
		
		begin try
		
		begin tran
		
		delete lis.{[_TABLE_NAME_]} with (ROWLOCK)
		where {[_TABLE_NAME_]}_id = @{[_TABLE_NAME_]}_id
		
		commit tran
		
		end try
		
		begin catch
			set @Error_Code = error_number()
			set @Error_Message = error_message()
			if @@trancount>0
				rollback tran
		end catch
		
		set nocount off
		GO
		";
		/**@var CI_DB_result $tables */
		$tables = "SELECT name FROM sys.all_objects WHERE type = 'U' AND schema_id = (SELECT schema_id FROM sys.schemas WHERE name = 'lis')";
		$tables = $this->dbmodel->db->query($tables);
		$tables = (is_object($tables)) ? $tables->result_array() : [];
		$alredyExistingTables = [];
		foreach ($tables as $value) {
			$alredyExistingTables[] = strtolower($value["name"]);
		}
		foreach ($this->map as $mapping) {
			$sql = str_replace("{[_TABLE_NAME_]}", $mapping["table"], $tpl);
			echo $sql;
		}
	}

	/**
	 * @param string $m
	 * @return bool
	 */
	private function add_log_message($m)
	{
		switch ($this->log_to) {
			case "textlog":
				$this->textlog->add($m);
				break;
			case "print_r":
				print_r($m);
				print_r("<br />");
				break;
			default:
				return false;
		}
		return true;
	}

	/**
	 * Функция создания XML-запроса для обращения к сервису ЛИС
	 * @param $method
	 * @param $request
	 * @param bool $empty
	 * @param bool $isInWindows1251
	 * @return string
	 * @throws Exception
	 */
	function setXmlRequest($method, $request, $empty = true, $isInWindows1251 = true)
	{
		$sessionid = "";
		if ($this->isLogon()) {
			$sessionid = $_SESSION["phox"]["sessionid"];
		}
		if ($isInWindows1251) {
			array_walk_recursive($request, "ConvertFromWin1251ToUTF8");
		}
		$w = new XMLWriter();
		$w->openMemory();
		$w->setIndent(true);
		$w->setIndentString("\t");
		$w->startDocument("1.0", "Windows-1251");
		$w->startDTD("request", null, "lims.dtd");
		$w->endDtd();
		$w->startElement("request");
		$w->writeAttribute("type", $method);
		$w->writeAttribute("sessionid", $sessionid);
		$w->writeAttribute("version", $this->server["version"]);
		$w->writeAttribute("buildnumber", $this->server["buildnumber"]);
		$w->startElement("content");

		if (is_array($request) && (count($request) > 0)) {
			foreach ($request as $key => $val) {
				if (is_array($val) && (count($val) > 0)) {
					$w->startElement('o');
					$w->writeAttribute('n', $key);
					foreach ($val as $k => $v) {
						if ($empty || (strlen($v) > 0)) {
							$this->xml_add_f($w, $k, $v);
						}
					}
					$w->endElement();//o
				} else {
					if ($empty || (strlen($val) > 0)) {
						$this->xml_add_f($w, $key, $val);
					}
				}
			}
		}
		$w->endElement();//content
		$w->endElement();//request
		$xml = $w->outputMemory();
		return $xml;
	}

	/**
	 * Функция создания XML-запроса для обращения к сервису ЛИС
	 *
	 * @param $method
	 * @param $request
	 * @param bool $empty
	 * @return string
	 * @deprecated
	 */
	function setXmlRequest_old($method, $request, $empty = true)
	{
		$sessionid = '';
		if ($this->isLogon()) {
			$sessionid = $_SESSION['phox']['sessionid'];
		}
		$xml = '<?xml version="1.0" encoding="Windows-1251"?>
		';
		$xml .= '<!DOCTYPE request SYSTEM "lims.dtd">
		';
		$xml .= '<request type="' . $method . '" sessionid="' . $sessionid . '" version="' . $this->server['version'] . '" buildnumber="' . $this->server['buildnumber'] . '">
		';
		$xml .= "<content>";
		$data = "";
		if (is_array($request) && (count($request) > 0)) {
			foreach ($request as $key => $val) {
				if (is_array($val) && (count($val) > 0)) {
					$data .= '<o n="' . $key . '">';
					foreach ($val as $k => $v) {
						if ($empty || (strlen($v) > 0)) {
							$data .= '<f n="' . $k . '" v="' . $v . '"/>';
						}
					}
					$data .= '</o>';
				} else {
					if ($empty || (strlen($val) > 0)) {
						$data .= '<f n="' . $key . '" v="' . $val . '"/>';
					}
				}
			}
		}
		$xml .= $data;
		$xml .= "</content>
		</request>
		";
		return $xml;
	}

	/**
	 * @param $m
	 * @return mixed
	 */
	function toRec($m)
	{
		foreach ($m as $k => $v) {
			if (is_array($v)) {
				if (isset($v["i"])) {
					$m[$k] = $v["i"];

				} else {
					$m[$k] = $this->toRec($v);
				}
			}
		}
		return $m;
	}

	/**
	 * Функция преобразования XML в нормальный массив
	 * @param $xml
	 * @return array
	 */
	function toArray($xml)
	{
		$x = new SimpleXMLElement($xml);
		return $this->toRec(simpleXMLToArray($x));
	}

	/**
	 * Функция обработки полученного XML-ответка и преобразование его в JSON (при необходимости)
	 * @param $xml
	 * @param bool $tojson
	 * @return array|string
	 */
	function getXmlResponse($xml, $tojson = true)
	{
		$r = $this->toArray($xml);
		if (is_array($r)) {
			if (isset($r["error"])) {
				$err = ["success" => false, "Error_Code" => $r["error"]["code"], "Error_Msg" => $r["error"]["description"]];
				return ($tojson) ? json_encode($err) : $err;
			}
		}
		$r["content"]["o"]["success"] = true;
		$r["content"]["o"]["buildnumber"] = $r["buildnumber"];
		$r["content"]["o"]["sessionid"] = $r["sessionid"];
		if ($tojson) {
			// TODO: тут надо сделать преобразование в JSON-формат
			return $r["content"]["o"];
		} else {
			return $r["content"]["o"];
		}
	}

	/**
	 * Функция отправки серверу xml-запроса и получения от него ответа
	 * @param $xml
	 * @param null $server
	 * @return mixed
	 */
	function request($xml, $server = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, (!empty($server)) ? $server : $this->server["address"]);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		$result = curl_exec($ch);
		if (!$result) {
			$this->add_log_message("CURL ERROR:" . curl_error($ch));
			DieWithError(curl_error($ch));
		}
		return $result;
	}

	/**
	 * Проверка, залогинен ли пользователь на удаленном сервисе (проверка на наличие открытой сессии)
	 * @return bool
	 */
	function isLogon()
	{
		$result = (isset($_SESSION["phox"]) && (isset($_SESSION["phox"]["sessionid"])));
		$this->add_log_message("isLogon: Проверка залогинен ли пользователь  в ЛИС: " . $result);
		return $result;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function getLisRequestData($data)
	{
		$this->load->model("LisUser_model", "usermodel");
		if (isset($_SESSION["lisrequestdata"])) {
			// если в сессии сохранены данные успешной авторизации то возвращаем их
			return $_SESSION["lisrequestdata"];
		} else {
			// иначе читаем из lis.User
			return $this->usermodel->getLisRequestData($data);
		}
	}

	function md5()
	{
		print(md5($_GET["md5"]));
	}

	/**
	 * Запрос login. Авторизация пользователя в системе и получение пользователем уникального идентификатора сессии
	 * @return bool
	 * @throws Exception
	 */
	function login()
	{
		$result = false;
		$data = $this->ProcessInputData("login", true, false);
		$this->add_log_message("login: Запуск");
		// TODO: Данные для пользователя нужно брать из pmUserCache наверное... или какая-то отдельная таблица с машинами (компами) - тут надо у Тараса уточнить откуда брать.
		$request = $this->getLisRequestData($data);
		if ($request == false) {
			throw new Exception(toUtf("Не заполнены параметры пользователя ЛИС! <br/>Перед работой с ЛИС заполните пожалуйста настройки пользователя."));
		}
		$this->add_log_message("login: Формируем запрос в XML");
		$xml = $this->setXmlRequest("login", $request);
		$this->add_log_message("login: Получили ответ");
		$response = $this->request($xml);
		if (strlen($response) > 0) {
			$this->add_log_message("login: Ответ не пустой");
			$arr = $this->getXmlResponse($response, false);
			$this->add_log_message("login: Распарсили ответ");
			// Обрабатываем ответ
			if ($arr["success"] === true) {
				$this->add_log_message("login: Ответ хороший, синхронизировали сессии");
				// При успешной авторизации сохраняем данные для авторизации (getLisRequestData) в сессии
				$_SESSION["lisrequestdata"] = $request;
				// При успешной авторизации сохраняя связь с сессией
				$_SESSION["phox"] = [];
				$_SESSION["phox"]["sessionid"] = $arr["sessionid"];
				$this->sessionid = $_SESSION["phox"]["sessionid"];
				$result = true;
			} else {
				$this->add_log_message("login: Ответ плохой, сессию удалили");
				$this->add_log_message("login: Ошибка " . $arr["Error_Code"] . "" . toAnsi($arr["Error_Msg"]));
				$_SESSION["phox"] = [];
			}
			// Вывод только, если установлен признак дебага
			if ($this->debug && (isset($arr))) {
				var_dump($arr);
			}
		} else {
			$this->add_log_message("login: Ответ пустой!");
			$this->add_log_message("login: " . $xml);
		}
		$this->add_log_message("login: Финиш");
		return $result;
	}

	/**
	 * Запрос directory. Предназначен для получения от сервера информации о текущих версиях используемых справочников.
	 *
	 * @throws Exception
	 */
	function getDirectoryVersions_old()
	{
		$this->add_log_message("getDirectoryVersions: Запуск");
		$this->ProcessInputData("getDirectoryVersions", true, false);
		if (!$this->isLogon()) {
			$this->login();
		}
		if ($this->isLogon()) {
			// Данные запроса 
			$request = array();
			$this->add_log_message("getDirectoryVersions: Формируем запрос в XML ");
			// Формируем запрос в XML 
			$xml = $this->setXmlRequest("directory-versions", $request);
			$response = $this->request($xml);
			$this->add_log_message("getDirectoryVersions: Получили ответ ");
			if (strlen($response) > 0) {
				$this->add_log_message("getDirectoryVersions: Ответ не пустой ");
				$arr = $this->getXmlResponse($response, false);
				$this->add_log_message("getDirectoryVersions: Распарсили ответ ");
				// Обрабатываем ответ
				if (is_array($arr)) {
					if ($arr["success"] === true) {
						$this->add_log_message("getDirectoryVersions: Ответ хороший, список справочников получен ");
						// дальше разбираем полученный ответ и получаем список справочников 
						// Если количество справочников больше нуля
						$s = [];
						$d = $arr["s"]["o"];
						if (count($d) > 0) {
							$this->add_log_message("getDirectoryVersions: Всего справочников получено " . count($d));
							for ($i = 0; $i < count($d); $i++) {
								$s[$i] = array();
								$s[$i]["name"] = $d[$i]["f"][0]["v"];
								$s[$i]["version"] = $d[$i]["f"][1]["v"];
								// получаем все справочники в XML формате
								$this->add_log_message("getDirectoryVersions: Справочник {$s[$i]["name"]} (версия {$s[$i]["version"]}) готов к загрузке в XML-формате");
								if (in_array(strtolower($s[$i]["name"]), $this->dirs, true)) {
									$this->add_log_message("getDirectoryVersions: Справочник {$s[$i]["name"]} (версия {$s[$i]["version"]}) готов к загрузке в БД");
									$records = $this->getDirectory($s[$i]);
									$this->saveDirectory($s[$i]["name"], $records);
								} else {
									$this->add_log_message("getDirectoryVersions: Справочник {$s[$i]["name"]} (версия {$s[$i]["version"]}) не входит в список разрешенных к загрузке в БД справочников");
								}
							}
						}
					} else {
						$this->add_log_message("getDirectoryVersions: Ошибка {$arr["Error_Code"]}" . toAnsi($arr["Error_Msg"]));
					}
				}
			} else {
				$this->add_log_message("getDirectoryVersions: Ответ пустой ");
			}
		}
		echo json_encode(["success" => true]);
		$this->add_log_message("getDirectoryVersions: Финиш ");
	}

	/**
	 * Запрос directory. Предназначен для получения от сервера информации о текущих версиях используемых справочников.
	 * @return bool
	 */
	function getDirectoryVersions()
	{
		$data = $this->ProcessInputData("getDirectoryVersions", false);
		if ($data === false) {
			return false;
		}
		if ($this->usePostgreLis) {
			$response = $this->lis->GET("Lis/DirectoryVersions", $data);
			$this->ProcessRestResponse($response, "single")->ReturnData();
		} else {
			$this->dbmodel->getDirectoryVersions($data);
			$this->ReturnData(["success" => true]);
			$this->add_log_message("getDirectoryVersions: Финиш ");
		}
		return true;
	}

	/**
	 * Запрос directory-versions. Предназначен для получения от сервера содержимого справочников.
	 * @param $data
	 * @return array|bool|string
	 * @throws Exception
	 */
	function getDirectory($data)
	{
		$this->add_log_message("getDirectory: Запуск");
		if ($this->isLogon()) {
			// Данные запроса 
			// TODO: Данные для пользователя нужно брать из pmUserCache наверное... или какая-то отдельная таблица с машинами (компами) - тут надо у Тараса уточнить откуда брать.
			$request = [];
			$request["name"] = $data["name"];
			// Формируем запрос в XML 
			$this->add_log_message("getDirectory: Хотим забрать справочник {$data["name"]}");
			$xml = $this->setXmlRequest("directory", $request);
			$this->add_log_message("getDirectory: Формируем запрос в XML");
			$response = $this->request($xml);
			// создаем файл с именем справочника и записываем в него все пришедшие данные
			$fd = "logs\\" . $data["name"] . "." . $data["version"] . ".xml";
			$f = fopen($fd, "w");
			fputs($f, "" . var_export($response, true));
			fclose($f);
			if (strlen($response) == 0) {
				$this->add_log_message("getDirectory: Пришел пустой ответ!");
				return false;
			}
			$arr = $this->getXmlResponse($response, false);
			$this->add_log_message("getDirectory: Пришел не пустой ответ");
			// Обрабатываем ответ
			if (!is_array($arr)) {
				$this->add_log_message("getDirectory: Ошибка при возврате справочника");
				return false;
			}
			if ($arr["success"] !== true) {
				$this->add_log_message("getDirectory: Ошибка при запросе справочника {$data["name"]}: {$arr["Error_Code"]}" . toAnsi($arr["Error_Msg"]));
				return $arr;
			}
			$this->add_log_message("getDirectory: Ответ хороший");
			$s = [];
			// TODO: Лопеция! Надо сделать предварительную проверку
			$rows = ((isset($arr["s"])) && (isset($arr["s"]["o"]))) ? $arr["s"]["o"] : null;
			if (isset($rows["f"])) {
				// одна строка
				$this->add_log_message("getDirectory: В ответе одна строка");
				$s[0] = [];
				$r = $rows["f"];
				if (count($r) > 0) {
					for ($j = 0; $j < count($r); $j++) {
						// TODO: проверить правильность загрузки с этим условием
						if (isset($r[$j])) {
							$s[0][$r[$j]["n"]] = $r[$j]["v"];
						}
					}
				}
			} else {
				// несколько строк
				if (count($rows) > 0) {
					$this->add_log_message("getDirectory: В ответе " . count($rows) . " строк");
					for ($i = 0; $i < count($rows); $i++) {
						$s[$i] = [];
						$r = $rows[$i]["f"];
						if (isset($r)) {
							if (count($r) > 0) {
								for ($j = 0; $j < count($r); $j++) {
									// TODO: проверить правильность загрузки с этим условием
									if (isset($r[$j])) {
										$s[$i][$r[$j]["n"]] = $r[$j]["v"];
									}
								}
							}
						}
					}
				}
			}
			$this->add_log_message("getDirectory: Успешный финиш");
			return $s;
		}
		return false;
	}

	/**
	 * Сохранение справочника
	 * @param $name
	 * @param $data
	 * @return bool
	 */
	function saveDirectory($name, $data)
	{
		$this->add_log_message("saveDirectory: Сохраняем справочник " . $name);
		if (!is_array($data)) {
			return false;
		}
		if (isset($data["Error_Msg"])) {
			$this->add_log_message("saveDirectory: Ошибка при сохранении справочника \"{$name}\": {$data["Error_Code"]} - {$data["Error_Msg"]}");
			return false;
		}
		for ($i = 0; $i < count($data); $i++) {
			$data[$i]["pmUser_id"] = $_SESSION["pmuser_id"];
			try {
				$response = $this->dbmodel->saveDirectory($name, $this->map, $data[$i]);
				$this->ProcessModelSave($response, true);
			} catch (Exception $e) {
				$this->add_log_message("ошибка сохранения элемента справочника {$name} " . var_export($data[$i], true));
			}
		}
		$this->add_log_message("saveDirectory: Сохранили все записи");
		return true;
	}

	/**
	 * Запрос registration-journal. Предназначен для выгрузки содержимого заявок на клиентское приложение.
	 * @return bool
	 * @throws Exception
	 */
	function listRegistrationJournal_old()
	{
		$this->add_log_message("listRegistrationJournal: Запуск");
		$data = $this->ProcessInputData("listRegistrationJournal", false, false);
		if (!$this->isLogon()) {
			$this->login();
		}
		if ($this->isLogon()) {
			$this->add_log_message("listRegistrationJournal: Формируем запрос в XML ");
			// Формируем запрос в XML
			$filter = $data;
			if (isset($filter["birthDate"])) {
				$d = Datetime::createFromFormat("d.m.Y", $filter["birthDate"]);//пробуем дату и так
				if (!$d) {
					$d = Datetime::createFromFormat("Y-m-d", $filter["birthDate"]); // и этак
				}
				$filter["birthDay"] = (int)$d->format("d");
				$filter["birthMonth"] = (int)$d->format("m");
				$filter["birthYear"] = (int)$d->format("Y");
				unset($filter["birthDate"]);
			}
			if (!is_array($filter["doctors"])) {
				$filter["doctors"] = [];//TODO необходимо докторов передавать как массив
			}
			if (!is_array($filter["hospitals"])) {
				$filter["hospitals"] = [];//TODO необходимо госпитали передавать как массив
			}
			if (!is_array($filter["custDepartments"])) {
				$filter["custDepartments"] = [];//TODO необходимо кустДепартаменты передавать как массив
			}
			if (!is_array($filter["departments"])) {
				$filter["departments"] = [];//TODO необходимо департаметы передавать как AndOrList
			}
			if (!is_array($filter["targets"])) {
				$filter["targets"] = [];//TODO необходимо департаметы передавать как AndOrList
			}
			if (!is_array($filter["customStates"])) {
				$filter["customStates"] = [];//TODO необходимо кустомСтаты передавать как массив
			}
			if (!is_array($filter["payCategories"])) {
				$filter["payCategories"] = [];//TODO необходимо пайКатегории передавать как массив
			}
			if (!is_array($filter["states"])) {
				$filter["states"] = [];//TODO необходимо статы передавать как массив
			}
			if (!isset($filter["onlyDelayed"])) {
				$filter["onlyDelayed"] = false; //Todo толькоДелаед обязателен
			}
			if (!isset($filter["markPlanDeviation"])) {
				$filter["markPlanDeviation"] = false; //Todo маркпланДевиатон обязателен
			}
			if (!isset($filter["emptyPayCategory"])) {
				$filter["emptyPayCategory"] = false; //Todo пустойПэйКатегория обязателен
			}
			unset($filter["session"]);//левых элементов в параметре быть не должно
			foreach ($filter as $filter_name => $filter_value) {
				if (is_null($filter_value)) {
					unset($filter[$filter_name]);//параметров, по которым не требуется фильтрация, не должно быть в запросе
				}
			}
			$xml = $this->lis_registration_journal($filter);
			$this->add_log_message("listRegistrationJournal: Запрос: " . $xml);
			$response = $this->request($xml);
			$this->add_log_message("listRegistrationJournal: Получили ответ");
			$this->add_log_message("listRegistrationJournal: Ответ: " . $response);
			if (strlen($response) > 0) {
				$arr = $this->getXmlResponse($response, false);
				$this->add_log_message("listRegistrationJournal: Пришел не пустой ответ, распарсили в массив");
				// Обрабатываем ответ
				if (is_array($arr)) {
					if ($arr["success"] === true) {
						$s = $this->lis_parse_responce_registration_journal($response);
						foreach ($s as $key => $sample) {
							if (isset($sample["state"])) {
								$s[$key]["state_name"] = $this->lis_static_dicts["request_state"][$sample["state"]];
							}
							if (isset($sample["priority"])) {
								$s[$key]["priority_name"] = $this->lis_static_dicts["priority"][$sample["priority"]];
							}
							if (isset($sample["birthDay"]) && isset($sample["birthMonth"]) && isset($sample["birthYear"])) {
								$t = DateTime::createFromFormat("d.m.Y", "{$sample["birthDay"]}.{$sample["birthMonth"]}.{$sample["birthYear"]}");
								$s[$key]["birthDate"] = $t->format("d.m.Y");
							}
							if (isset($sample["custHospitalId"])) {
								$s[$key]["custHospital_name"] = $this->dbmodel->getFirstResultFromQuery("select Hospital_Name from lis.Hospital where Hospital_id = :Hospital_id", ["Hospital_id" => $sample["custHospitalId"]]);
							}
							if (isset($sample["requestFormId"])) {
								$s[$key]["requestForm_name"] = $this->dbmodel->getFirstResultFromQuery("select RequestForm_name from lis.RequestForm where RequestForm_id = :RequestForm_id", ["RequestForm_id" => $sample["requestFormId"]]);
							}
							if (isset($sample["sex"])) {
								$s[$key]["Sex_name"] = $this->lis_static_dicts["sex"][$sample["sex"]];
							}
							if (isset($sample["endDate"])) {
								if (is_object($sample["endDate"]) && ($sample["endDate"] instanceof Datetime)) {
									/**@var DateTime $sampleEndDate */
									$sampleEndDate = $sample["endDate"];
									$s[$key]["endDate"] = $sampleEndDate->format("d.m.Y H:i");
								}
							}
							if (isset($sample["sampleDeliveryDate"])) {
								if (is_object($sample["sampleDeliveryDate"]) && ($sample["sampleDeliveryDate"] instanceof Datetime)) {
									/**@var DateTime $sampleDeliveryDate */
									$sampleDeliveryDate = $sample["sampleDeliveryDate"];
									$s[$key]["sampleDeliveryDate"] = $sampleDeliveryDate->format("d.m.Y H:i");
								}
							}
						}
						array_walk_recursive($s, "ConvertFromWin1251ToUTF8");
						echo json_encode($s);
					} else {
						$this->add_log_message("listRegistrationJournal: Ошибка {$arr["Error_Code"]}: " . toAnsi($arr["Error_Msg"]));
						$this->add_log_message($xml);
						echo json_encode($arr);
					}
				} else {
					echo json_encode([]);
				}
				return true;
			} else {
				$this->add_log_message("listRegistrationJournal: Пришел пустой ответ!");
			}
		}
		return false;
	}

	/**
	 * @return bool
	 */
	function listRegistrationJournal()
	{
		$data = $this->ProcessInputData("listRegistrationJournal", true);
		if ($data === false) {
			return false;
		}
		if ($this->usePostgreLis) {
			$response = $this->lis->GET("Lis/listRegistrationJournal", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$this->dbmodel->listRegistrationJournal($data);
		}
		return true;
	}

	/**
	 *  Тест запроса create-requests
	 * @throws Exception
	 */
	function testRequestOld()
	{
		// сначала получаем какие-то нужные данные из заявки
		$data = $this->ProcessInputData("createRequestSelections", true, false);
		$this->load->model("EvnLabSample_model", "EvnLabSample_model");
		$response = $this->EvnLabSample_model->getTestData($data);
		$dt = date("d.m.Y H:i:s");
		$req = [
			"createWorklists" => "false",
			"o" => [
				"requestForm" => "11044", // TODO: Id справочника форм (деление на подразделения в лаборатории), надо чтобы здесь подставлялся выбор из справочника или определялось автоматически, для какого именно подразделения создается заявка
				"internalNr" => $data["EvnLabSample_id"],
				"priority" => 10,
				"delivered" => false,
				"printed" => false,
				"originalSent" => false,
				"copySent" => false,
				"code" => $response["Person_id"], // TODO: здесь должно быть PersonCard_code
				"patient" => $response["Person_id"],
				"patientCardNr" => "",
				"firstName" => $response["Person_FirName"],
				"lastName" => $response["Person_SurName"],
				"middleName" => $response["Person_SecName"],
				"birthDay" => $response["BirthDay_Day"],
				"birthMonth" => $response["BirthDay_Month"],
				"birthYear" => $response["BirthDay_Year"],
				"sex" => $response["Sex_Code"],
				"country" => $response["KLCountry_Name"],
				"city" => $response["KLCity_Name"],
				"street" => $response["Sex_Code"],
				"building" => $response["Address_House"],
				"flat" => $response["Address_Flat"],
				"insuranceCompany" => $response["OrgSmo_Nick"],
				"policySeries" => $response["Polis_Ser"],
				"policyNumber" => $response["Polis_Num"],
				"samplingDate" => $dt,
				"sampleDeliveryDate" => $dt,
				"cyclePeriod" => 0,
				"pregnancyDuration" => 0,
				"targets" => ["32"],
				"fromClient" => true,
				"biomaterials" => [],
				"samples" => [["defectTypes" => []]]
			]
		];
		if (!$this->isLogon()) {
			$this->login();
		}
		$xml = $this->lis_create_requests($req);
		$this->add_log_message("create-requests request: " . $xml);
		$response = $this->request($xml);
		$this->add_log_message("create-requests response: " . $response);
		$this->add_log_message($response);
		//парсим ответ
		$lis_request_id = null;
		$r = new XMLReader();
		$r->xml($response);
		while ($r->read()) {
			if (($r->nodeType === XMLReader::ELEMENT)) {
				switch ($r->name) {
					case "o":
						while ($r->read()) {
							if ($r->nodeType === XMLReader::ELEMENT) {
								switch ($r->name) {
									case "f":
										if ($r->moveToAttribute("n") && ($r->value === "id")) {
											//это переменная с идентификатором созданной заявки
											//считаем идентификатор в атрибуте
											if ($r->moveToAttribute("v")) {
												$lis_request_id = $r->value;
											}
										}
										break;
								}
							}
						}
						break;
					case "error":
						if ($r->moveToAttribute("description")) {
							throw new Exception(toAnsi($r->value));
						}
						break;
				}

			}
		}
		$ouput4client = ["success" => true, "request" => $xml, "response" => $response, "lis_request_id" => $lis_request_id];
		$_SESSION["lis_request_id"] = $lis_request_id;
		array_walk_recursive($ouput4client, "ConvertFromWin1251ToUTF8");
		echo json_encode($ouput4client);
	}


	/**
	 * Отправляет одну запись в ЛИС и возврашает ответ по одной записи
	 * @param null $data
	 * @return array|bool|mixed|string
	 * @throws Exception
	 */
	function createRequest2($data = null)
	{
		if ($data == null) {
			$data = $this->ProcessInputData("createRequest2", true, false);
		}
		return $this->dbmodel->createRequest2($data);
	}

	/**
	 * Отправляет набор выделенных проб в ЛИС, выполняя метод ЛИС create-request2
	 * @throws Exception
	 */
	function createRequestSelections()
	{
		$data = $this->ProcessInputData("createRequestSelections", true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("Lis/RequestSelections", $data);
			$this->ProcessRestResponse($response, "single")->ReturnData();
		} else {
			$arrayId = [];
			if (!$data) {
				throw new Exception("Для создания заявки необходимо выбрать хотя бы одну пробу");
			}
			if (!empty($data["EvnLabSamples"])) {
				$arrayId = json_decode($data["EvnLabSamples"]);
			}
			$answers = [];
			foreach ($arrayId as $id) {
				$data["EvnLabSample_id"] = $id;
				$answers = $this->dbmodel->createRequest2($data);
			}
			echo json_encode($answers);
		}
	}

	/**
	 * Отправляет набор выделенных заявок в ЛИС, выполняя метод ЛИС create-request2
	 * @throws Exception
	 */
	function createRequestSelectionsLabRequest()
	{
		$data = $this->ProcessInputData("createRequestSelectionsLabRequest", true);
		if ($data === false) {
			return false;
		}

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("Lis/RequestSelectionsLabRequest", $data);
			$this->ProcessRestResponse($response, "single")->ReturnData();
		} else {
			if (!$data) {
				throw new Exception("Для создания заявки необходимо выбрать хотя бы одну заявку с пробами");
			}
			$arrayId = $this->dbmodel->getLabSamplesForEvnLabRequests($data);
			$answers = [];
			foreach ($arrayId as $id) {
				$data["EvnLabSample_id"] = $id;
				$answers = $this->dbmodel->createRequest2($data);
			}
			echo json_encode($answers);
		}
		return true;
	}

	/**
	 *  Тест запроса create-requests-2
	 * @throws Exception
	 */
	function testRequest2Old()
	{
		// сначала получаем какие-то нужные данные из заявки
		$this->load->model("EvnLabSample_model", "EvnLabSample_model");
		$data = $this->ProcessInputData("createRequestSelections", true, false);
		$response = $this->EvnLabSample_model->getTestData($data);
		$dt = date("d.m.Y H:i:s");
		$query = "
			select 
				u.UslugaComplex_Code as Usluga_Code, 
				ls.EvnLabSample_Num 
			from
				v_EvnLabSample ls
				left join v_EvnLabRequest lr on lr.EvnLabRequest_id = ls.EvnLabRequest_id
				left join v_evnUslugaPar eup on eup.EvnDirection_id = lr.EvnDirection_id
				left join v_UslugaComplex u on eup.UslugaComplex_id = u.UslugaComplex_id
			where ls.EvnLabSample_id = :EvnLabSample_id
		";
		$queryParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$this->add_log_message("определение Usluga_Code для EvnLabSample_id = {$data["EvnLabSample_id"]} запрос: {$query}");
		$row = $this->EvnLabSample_model->getFirstRowFromQuery($query, $queryParams);
		$Usluga_Code = $row["Usluga_Code"];
		$EvnLabSample_Num = $row["EvnLabSample_Num"];
		$this->add_log_message("определен Usluga_Code: " . var_export($Usluga_Code, true));
		switch ($Usluga_Code) {
			case "1":
				$biomaterial = "1";
				if (($_SESSION["lpu_id"] == 3) || ($_SESSION["lpu_id"] == 10)) {
					$registrationFormCode = "12";
					$targets = ["5108495" => ["cancel" => false, "readonly" => false]];
				} else {
					$registrationFormCode = "7";
					$targets = ["1" => ["cancel" => false, "readonly" => false]];
				}
				break;
			case "2":
				$registrationFormCode = "14";
				$targets = [
					"41" => ["cancel" => false, "readonly" => false],
					"40" => ["cancel" => false, "readonly" => false],
					"47" => ["cancel" => false, "readonly" => false],
					"48" => ["cancel" => false, "readonly" => false],
					"50" => ["cancel" => false, "readonly" => false],
					"74" => ["cancel" => false, "readonly" => false],
					"73" => ["cancel" => false, "readonly" => false],
					"72" => ["cancel" => false, "readonly" => false]
				];
				$biomaterial = "10";
				break;
			case "7":
				$biomaterial = "9";
				if (($_SESSION["lpu_id"] == 3) || ($_SESSION["lpu_id"] == 10)) {
					$registrationFormCode = "12";
					$targets = ["5101635" => ["cancel" => false, "readonly" => false]];
				} else {
					$registrationFormCode = "3";
					$targets = ["32" => ["cancel" => false, "readonly" => false]];
				}
				break;
			default:
				$registrationFormCode = "";
		}
		if (!$registrationFormCode) {
			$registrationFormCode = "1";
			$biomaterial = "9";
			$targets = ["32" => ["cancel" => false, "readonly" => false]];
			$this->add_log_message("используется значение registrationFormCode по умолчанию: " . var_export($registrationFormCode, true));
		} else {
			$this->add_log_message("по Usgluga_Code {$Usluga_Code} определен registrationFormCode: {$registrationFormCode}");
		}
		$req = [
			"o" => [
				"registrationFormCode" => $registrationFormCode,
				"hospitalCode" => $data["Lpu_id"],
				"hospitalName" => toAnsi($data["session"]["setting"]["server"]["lpu_nick"]),
				"internalNr" => $data["EvnLabSample_id"],
				"samplingDate" => $dt,
				"sampleDeliveryDate" => $dt,
				"priority" => 20,
				"cyclePeriod" => 0,
				"pregnancyDuration" => 0,
				"readonly" => false,
				"patient" => [
					"code" => $response["Person_id"],
					"firstName" => $response["Person_FirName"],
					"lastName" => $response["Person_SurName"],
					"middleName" => $response["Person_SecName"],
					"birthDay" => $response["BirthDay_Day"],
					"birthMonth" => $response["BirthDay_Month"],
					"birthYear" => $response["BirthDay_Year"],
					"sex" => $response["Sex_Code"],
					"country" => $response["KLCountry_Name"],
					"city" => $response["KLCity_Name"],
					"street" => $response["Sex_Code"],
					"building" => $response["Address_House"],
					"flat" => $response["Address_Flat"],
					"insuranceCompany" => $response["OrgSmo_Nick"],
					"policySeries" => $response["Polis_Ser"],
					"policyNumber" => $response["Polis_Num"]
				],
				"samples" => [
					[
						"biomaterial" => $biomaterial,
						"internalNr" => (isset($EvnLabSample_Num)) ? $EvnLabSample_Num : $data["EvnLabSample_id"],
						"targets" => $targets
					]
				]
			]
		];
		if (!$this->isLogon()) {
			$this->login();
		}
		$xml = $this->lis_create_requests_2($req);
		$this->add_log_message($xml);
		$response = $this->request($xml);
		$this->add_log_message($response);
		$lis_request_id = null;
		$r = new XMLReader();
		$r->xml($response);
		while ($r->read()) {
			if (($r->nodeType === XMLReader::ELEMENT)) {
				switch ($r->name) {
					case "o":
						while ($r->read()) {
							if ($r->nodeType === XMLReader::ELEMENT) {
								switch ($r->name) {
									case "f":
										if ($r->moveToAttribute("n") && ($r->value === "id")) {
											if ($r->moveToAttribute("v")) {
												$lis_request_id = $r->value;
											}
										}
										break;
								}
							}
						}
						break;
					case "error":
						if ($r->moveToAttribute("description")) {
							throw new Exception(toAnsi($r->value));
						}
						break;
				}

			}
		}
		$ouput4client = ["success" => true, "request" => $xml, "response" => $response, "lis_request_id" => $lis_request_id];
		$this->add_log_message("Сохраняем lis_request_id = {$lis_request_id} в EvnLabSample_id = {$data["EvnLabSample_id"]} " . var_export($this->EvnLabSample_model->setComment($data["EvnLabSample_id"], $lis_request_id), true));
		array_walk_recursive($ouput4client, "ConvertFromWin1251ToUTF8");
		echo json_encode($ouput4client);
	}

	function testtest()
	{
		$this->load->model("EvnLabSample_model", "EvnLabSample_model");
		$EvnLabSample = new EvnLabSample_model();
		$EvnLabSample_id = 73002309787287;
		$this->add_log_message("загружаем пробу с указанным идентификатором: {$EvnLabSample_id}");
		$EvnLabSample->EvnLabSample_id = $EvnLabSample_id;
		$EvnLabSample->load();
		var_dump($EvnLabSample);
		echo("$EvnLabSample->Server_id: {$EvnLabSample->Server_id}<br/>");
	}

	/**
	 * Получает данные из ЛИС по нескольким выбранным пробам
	 * @throws Exception
	 */
	function getResultSamples()
	{
		$data = $this->ProcessInputData("sample", true);
		if ($data === false) {
			return false;
		}
		if ($this->usePostgreLis) {
			$response = $this->lis->GET("Lis/ResultSamples", $data);
			$this->ProcessRestResponse($response, "single")->ReturnData();
		} else {
			$arrayId = [];
			if (!$data) {
				throw new Exception("Для получения результатов нужно выбрать хотя бы одну пробу");
			}
			if (!empty($data["EvnLabSamples"])) {
				$arrayId = json_decode($data["EvnLabSamples"]);
			}
			$errors = "";
			foreach ($arrayId as $id) {
				$data["EvnLabSample_id"] = $id;
				$result = $this->dbmodel->sample($data);
				if (!empty($result[0]["Error_Msg"])) {
					$errors .= "{$result[0]["Error_Code"]} {$result[0]["Error_Msg"]}<br/>";
				}
			}
			if (strlen($errors) > 0) {
				$this->ReturnError("Ошибки при получении результатов:<br/>" . $errors);
				return false;
			}
			$this->ReturnData(array("success" => true));
		}
		return true;
	}

	/**
	 * Синхронизация данных по отбраковке
	 * @throws Exception
	 */
	function syncSampleDefects()
	{
		$data = $this->ProcessInputData("syncSampleDefects", true);
		if ($data === false) {
			return false;
		}
		if ($this->usePostgreLis) {
			$response = $this->lis->POST("Lis/syncSampleDefects", $data);
			$this->ProcessRestResponse($response, "single")->ReturnData();
		} else {
			// получаем список проб нуждающихся в синхронизации с ЛИС (EvnLabSample_IsLIS = 1)
			$arrayId = $this->dbmodel->getEvnLabSampleNeedToSync($data);
			$this->dbmodel->syncDefectCauseTypeSpr($data);
			foreach ($arrayId as $id) {
				$data["EvnLabSample_id"] = $id;
				$result = $this->dbmodel->syncSampleDefects($data);
				if (!empty($result[0]["Error_Msg"])) {
					$this->ReturnError("Ошибка при синхронизации с ЛИС:{$result[0]["Error_Code"]} {$result[0]["Error_Msg"]}");
					return false;
				}
			}

			$this->ReturnData(["success" => true]);
		}
		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	function saveLisRequestFromLabSamples()
	{
		$data = $this->ProcessInputData("saveLisRequestFromLabSamples", true);
		if ($data === false) {
			return false;
		}
		if ($this->usePostgreLis) {
			$response = $this->lis->POST("Lis/RequestFromLabSamples", $data);
			$this->ProcessRestResponse($response, "single")->ReturnData();
		} else {
			$res = $this->dbmodel->saveLisRequestFromLabSamples($data);
			echo $res;
		}
		return true;
	}

	/**
	 * @throws Exception
	 */
	function saveLisRequestFromLabSamples_old()
	{
		$data = $this->ProcessInputData("saveLisRequestFromLabSamples", true);
		$json = $data["EvnLabSample_idsJSON"];
		$decoded = json_decode($json, true);
		if (is_null($decoded)) {
			throw new Exception("Неправильные параметры: Не удалось раскодировать JSON");
		}
		if (!is_array($decoded)) {
			throw new Exception("Неправильные параметры: Ожидается массив");
		}
		if (!count($decoded)) {
			throw new Exception("Неправильные параметры: Массив пуст");
		}
		try {
			$req = [
				"o" => [
					"registrationFormCode" => "3",
					"hospitalCode" => $data["Lpu_id"],
					"hospitalName" => toAnsi($data["session"]["setting"]["server"]["lpu_name"]),
					"internalNr" => "1111110003",
					"samplingDate" => Datetime::createFromFormat("d.m.Y H:i:s", "17.11.2011 09:00:00"),
					"sampleDeliveryDate" => Datetime::createFromFormat("d.m.Y H:i:s", "17.11.2011 11:25:00"),
					"priority" => 20,
					"readonly" => false,
					"patient" => [
						"code" => "0001",
						"firstName" => "Петров",
						"lastName" => "Пётр",
						"middleName" => "Петрович",
						"birthDay" => 1,
						"birthMonth" => 1,
						"birthYear" => 1954,
						"sex" => 1,
						"country" => "Россия",
						"city" => "Пушкин, Лен. обл.",
						"street" => "Ленина",
						"building" => "1/3",
						"flat" => "10",
						"insuranceCompany" => "РГС",
						"policySeries" => "333",
						"policyNumber" => "100 200 300",
						"patientCard" => [
							"cardNr" => "ZX10",
							"userValues" => [
								11704492 => "Госпиталь №1",
								11704410 => "17.05.2011"
							]
						],
						"userValues" => [11704441 => "ул. им. Тарасова"]
					],
					"samples" => [
						[
							"internalNr" => "1111110012",
							"biomaterial" => "10078",
							"targets" => [
								11055 => [
									"cancel" => false,
									"readonly" => false,
									"tests" => [123, 124],
								],
							]
						]
					],
					"userValues" => [16 => "РЕСО"]
				]
			];
			if (!$this->isLogon()) {
				$this->login();
			}
			$xml = $this->lis_create_requests_2($req);
			$response = $this->request($xml);
			echo htmlspecialchars($response);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * Создает XMLWriter, который можно наполнить данными запроса. Будет включать всю необходимю информацию кроме тела запроса
	 * Для корректной работы требуется перед вызовом метода убедится что произведен вход в ЛИС
	 * Чтобы после добавления в документ нужной иформации корректно закончить его и получить XML, надо вызвать endXmlRequestWriterDocument
	 * @param $type
	 * @return XMLWriter
	 * @throws Exception
	 */
	function xml_startXmlRequestWriterDocument($type)
	{
		if (is_null($this->sessionid)) {
			throw new Exception("Вход в ЛИС не выполнен");
		}
		$w = new XMLWriter();
		$w->openMemory();
		$w->setIndent(true);
		$w->setIndentString("\t");
		$w->startDocument("1.0", "Windows-1251");
		$w->startDTD("request", null, "lims.dtd");
		$w->endDtd();
		$w->startElement("request");
		$w->writeAttribute("type", $type);
		$w->writeAttribute("sessionid", $this->sessionid);
		$w->writeAttribute("version", $this->server["version"]);
		$w->writeAttribute("buildnumber", $this->server["buildnumber"]);
		$w->startElement("content");
		return $w;
	}

	/**
	 * Закрывает XML-документ, ранее открытый методом startXmlRequestWriterDocument
	 * Возвращает сформированный документ в виде строки.
	 * @param XMLWriter $writer
	 * @return string
	 */
	function xml_endXmlRequestWriterDocument($writer)
	{
		$writer->endElement();//закрываем content
		$writer->endElement();//закрываем request
		$writer->endDocument();//
		return $writer->outputMemory();
	}

	/**
	 * Добавит в XML вот примерно такое: <f t="i" n="id" v="11721537" />.
	 * Определяет тип переменной и устанавливает (c $include_type = true - по умолчнию) атрибут "t" (i - целое, d - дата, b - булево, s - строка)
	 * Дату необходимо передавать как объект Datetime.
	 *
	 * @param XMLWriter $w
	 * @param string $f_name
	 * @param mixed $f_value
	 * @param bool $include_type
	 * @return bool
	 * @throws Exception
	 */
	function xml_add_f($w, $f_name, $f_value, $include_type = true)
	{
		$result = true;
		$result = $result && $w->startElement("f");
		$var_type = gettype($f_value);
		switch ($var_type) {
			case "double":
			case "integer":
				$t = "i";
				$v = (string)$f_value;
				break;
			case "object":
				if ($f_value instanceof Datetime) {
					/** @var Datetime $f_value */
					$t = "d";
					$v = $f_value->format("d.m.Y H:i:s");
				} else {
					throw new Exception("Запись объекта класса в XML" . get_class($f_value) . " не поддерживается ({$f_name} = {$f_value})");
				}
				break;
			case "string":
				$t = "s";
				$v = $f_value;
				break;
			case "boolean":
				$t = "b";
				$v = $f_value ? "true" : "false";
				break;
			default:
				throw new Exception("Неизвестный тип переменной $var_type ($f_name = $f_value)");
		}
		if ($include_type) {
			$result = $result && $w->writeAttribute('t', $t);
		}
		if (!is_null($f_name)) {
			$result = $result && $w->writeAttribute('n', $f_name);
		}
		$result = $result && $w->writeAttribute('v', $v);
		$result = $result && $w->endElement();
		return $result;
	}

	/**
	 * @param XMLWriter $w
	 * @param string $userField_i
	 * @param mixed $value
	 * @throws Exception
	 */
	function xml_add_userValues_entry($w, $userField_i, $value)
	{
		$w->startElement("o");
		$w->startElement("r");
		$w->writeAttribute("n", "userField");
		$w->writeAttribute("i", $userField_i);
		$w->endElement();
		$this->xml_add_f($w, "value", $value);
		$w->endElement();
	}

	/**
	 * @param XMLWriter $w
	 * @param array $userValues
	 * @throws Exception
	 */
	private function xml_add_userValues_block($w, $userValues)
	{
		$w->startElement("s");
		$w->writeAttribute("n", "userValues");
		foreach ($userValues as $key => $value) {
			$this->xml_add_userValues_entry($w, $key, $value);
		}
		$w->endElement();
	}

	/**
	 * @param XMLWriter $w
	 * @param string $name
	 * @param array $values
	 * @param string $tag
	 * @throws Exception
	 */
	function xml_add_s($w, $name, $values, $tag = "f")
	{
		$w->startElement("s");
		$w->writeAttribute("n", $name);
		if (!is_array($values)) {
			throw new Exception("xml_add_s: Элемент {$name} не является массивом");
		}
		foreach ($values as $key => $value) {
			if (is_array($value)) {
				throw new Exception("xml_add_s: wrong type of '{$name}'['{$key}'] value - array");
			}
			switch ($tag) {
				case "f":
					$this->xml_add_f($w, null, $value, true);
					break;
				case "r":
					$w->startElement("r");
					$w->writeAttribute("i", $value);
					$w->endElement();
					break;
				default:
					throw new Exception("xml_add_s: tag \"{$tag}\" not supported");
					break;
			}
		}
		$w->endElement();
	}

	/**
	 * @param XMLWriter $w
	 * @param string $name
	 * @param string $operator
	 * @param array $idList
	 * @param string $tag
	 * @throws Exception
	 */
	function xml_add_filter_o($w, $name, $operator, $idList, $tag = "r")
	{
		$w->startElement("o");
		$w->writeAttribute("n", $name);
		if (!is_null($operator)) {
			$this->xml_add_f($w, "operator", $operator, false);
			$this->xml_add_s($w, "idList", $idList, $tag);
		}
		$w->endElement();
	}

	/**
	 * @param string $type
	 * @param array $filter
	 * @param $andOrListR
	 * @param $andOrListF
	 * @param array $collectionsR
	 * @param $collectionsF
	 * @param array $fieldsR
	 * @return string
	 * @throws Exception
	 */
	function xml_composeFilter($type, $filter, $andOrListR, $andOrListF, $collectionsR, $collectionsF, $fieldsR)
	{
		$w = $this->xml_startXmlRequestWriterDocument($type);
		$w->startElement("o");
		$w->writeAttribute("n", "filter");
		foreach ($filter as $filter_name => $filter_value) {
			if (in_array($filter_name, $andOrListR)) {
				if (isset($filter_value["operator"]) && isset($filter_value["idList"])) {
					$this->xml_add_filter_o($w, $filter_name, $filter_value["operator"], $filter_value["idList"]);
				} else {
					if (count($filter_value) !== 0) {
						throw new Exception("Поскольку '{$filter_name}' ожидается как объект типа 'AndOrList', то элементы 'operator' и 'idList' в нем являются обязательными");
					}
					$this->xml_add_filter_o($w, $filter_name, null, []);
				}
			} else {
				if (in_array($filter_name, $andOrListF)) {
					if (isset($filter_value["operator"]) && isset($filter_value["idList"])) {
						$this->xml_add_filter_o($w, $filter_name, $filter_value["operator"], $filter_value["idList"], "f");
					} else {
						if (count($filter_value) !== 0) {
							throw new Exception("Поскольку '{$filter_name}' ожидается как объект типа 'AndOrList', то элементы 'operator' и 'idList' в нем являются обязательными");
						}
						$this->xml_add_filter_o($w, $filter_name, null, [], "f");
					}
				} else {
					if (in_array($filter_name, $collectionsR)) {
						$this->xml_add_s($w, $filter_name, $filter_value, "r");
					} else {
						if (in_array($filter_name, $fieldsR)) {
							$w->startElement("r");
							$w->writeAttribute("n", $filter_name);
							$w->writeAttribute("i", $filter_value);
							$w->endElement();
						} else {
							$this->xml_add_s($w, $filter_name, $filter_value, (is_array($filter_value)) ? "f" : false);
						}
					}

				}
			}
		}
		$w->endElement();
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Ищет и перемещает курсор XML-считывателя на искомый элемент $element в пределах текущего элемента.
	 * Если указаны $n и $v, будет произведен поиск с учетом значений атрибутов 'n' и 'v'.
	 * В случае успеха возвращает true, иначе false.
	 * Регистр переменных $element и $n значения не имеет, в отличие от $v.
	 *
	 * @param XMLReader $r
	 * @param string $element
	 * @param string $n
	 * @param string $v
	 * @return bool
	 */
	function xml_read_moveTo($r, $element, $n = null, $v = null)
	{
		$cur_el = $r->name;
		$cur_depth = $r->depth;
		if ($r->nodeType === XMLReader::ATTRIBUTE) {
			//если курсор на атрибуте - переходим на элемент
			$r->moveToElement();
		} else {
			//если курсор на другом элементе, переходим к первому элементу, который найдется при последовательном чтении
			while ($r->nodeType !== XMLReader::ELEMENT) {
				if (!$r->read()) {
					//на случай если окажемся в конце документа
					break;
				}
			}
		}
		do {
			$result = (($r->nodeType === XMLReader::ELEMENT) && (strtolower($r->name) === strtolower($element)));//является ли этот элемент искомым
			if ($result) {
				if (!is_null($n)) {//Нужно ли проверить значение атрибута n?
					$result = ($result && ($r->moveToAttribute('n') && (strtolower($r->value) === strtolower($n))));
				}
				if (!is_null($v)) {
					$result = ($result && ($r->moveToAttribute('v') && ($r->value === $v)));
				}
			}
			if ($result) {
				$r->moveToElement();
				break;
			} else {
				if (($r->nodeType === XMLReader::END_ELEMENT) && ($r->name === $cur_el) && ($cur_depth === $r->depth)) {
					//достигли конца текущего элемента
					break;
				}
			}
		} while ($r->read());
		return $result;
	}

	/**
	 * @param XMLReader $r
	 * @param $result
	 * @throws Exception
	 */
	private function xml_read_collect_f($r, &$result)
	{
		$t = "s";
		$v = null;
		$n = null;
		if (!$r->moveToAttribute("n")) {
			throw new Exception("У элемента f не задан атрибут n");
		}
		$n = $r->value;
		if ($r->moveToAttribute("t")) {
			$t = strtolower($r->value);
		}
		if ($r->moveToAttribute("v")) {
			switch ($t) {
				case "b":
					$v = (strtolower($r->value) === "true") ? true : false;
					break;
				case "r":
				case "l":
				case "i":
					$v = (int)$r->value;
					break;
				case "s":
					$v = (string)$r->value;
					break;
				case "f":
					$v = (float)$r->value;
					break;
				case "d":
					$v = Datetime::createFromFormat("d.m.Y H:i:s", $r->value);
					if ($v === false) {
						throw new Exception("Не удалось распознать дату: - " . $r->value);
					}
					break;
				default:
					throw new Exception("Неизвестное обозначение типа переменной - {$t}");
			}
		}
		if (!is_null($n)) {
			$result[$n] = $v;
		}
	}

	/**
	 * @param XMLReader $r
	 * @return array
	 * @throws Exception
	 */
	private function xml_read_collect_userValues($r)
	{
		//проверяем правильность установки позиции
		$ok = (
			($r::ELEMENT === $r->nodeType) &&
			("s" === $r->name) &&
			$r->moveToAttribute("n") &&
			("userValues" === $r->value)
		);
		if (!$ok) {
			throw new Exception("Парсер блока пользовательских данных: Курсор xml-считываля находится в неправильной позиции");
		}
		$r->moveToElement();
		$result = [];
		if (!$r->isEmptyElement) {
			while ($r->read()) {
				switch ($r->nodeType) {
					case $r::ELEMENT:
						if ("o" === $r->name) {
							if (!$r->moveToAttribute("n")) {
								throw new Exception("не найден атрибут \"n\" у элемента \"o\" в множестве userValues");
							}
							if ("userValues" === $r->value) {
								$tmp = array();
								$key = null;
								while ($r->read()) {
									if (($r->nodeType === XMLReader::ELEMENT) && ($r->name === "f")) {
										$this->xml_read_collect_f($r, $tmp);
									}
									if (($r->nodeType === XMLReader::ELEMENT) && ($r->name === "r")) {
										if (!$r->moveToAttribute("n")) {
											throw new Exception("не найдено n у элемента r");
										}
										switch ($r->value) {
											case "userField":
												if (!$r->moveToAttribute("i")) {
													throw new Exception("не найдено i у элемента r");
												}
												$key = $r->value;
												break;
											default:
												$n = $r->value;
												$tmp[$n] = ($r->moveToAttribute("i")) ? (int)$r->value : null;
												break;
										}
									}
									if (($r->nodeType === XMLReader::END_ELEMENT) && ($r->name === "o")) {
										break;
									}
								}
								if (is_null($key)) {
									throw new Exception("не указан userField у userValues");
								}
								$result[$key] = $tmp;
							}
						}
						break;
					case $r::END_ELEMENT:
						if ("s" === $r->name) {
							break 2;//выходим из switch и while
						}
				}
			}
		}
		return $result;
	}

	/**
	 * @param XMLReader $r
	 * @param string $s_name
	 * @return array
	 * @throws Exception
	 */
	function xml_read_collect_r($r, $s_name = "s")
	{
		$result = [];
		if (!$r->isEmptyElement) {
			while ($r->read()) {
				switch ($r->nodeType) {
					case $r::ELEMENT:
						if ("r" !== $r->name) {
							throw new Exception("Неожиданный элемент " . $r->name . " в " . $s_name);
						}
						if (!$r->moveToAttribute("i")) {
							throw new Exception("Не найден атрибут i элемента r в $s_name");
						}
						$result[] = (int)$r->value;
						break;
					case $r::END_ELEMENT:
						if ("s" === $r->name) {
							break 2;
						}
				}
			}
		}
		return $result;
	}

	/**
	 * @param XMLReader $r
	 * @return array
	 * @throws Exception
	 */
	public function xml_read_collect_works($r)
	{
		$result = [];
		while ($r->read()) {
			switch ($r->nodeType) {
				case $r::ELEMENT:
					if ("o" !== $r->name) {
						throw new Exception("Неожиданный элемент " . $r->name . "в <s n=\"works\">");
					}
					if (!$r->moveToAttribute("n")) {
						throw new Exception("Не найден атрибут n в элементе o");
					}
					if ("works" === $r->value) {
						$tmp = [];
						while ($r->read()) {
							switch ($r->nodeType) {
								case $r::ELEMENT:
									switch ($r->name) {
										case "f":
											$this->xml_read_collect_f($r, $tmp);
											break;
										case "r":
											if (!$r->moveToAttribute("n")) {
												throw new Exception("Не найден атрибут n у элемента r");
											}
											$key = $r->value;
											if (!$r->moveToAttribute("i")) {
												throw new Exception("Не найден атрибут i у элемента r");
											}
											$tmp[$key] = (int)$r->value;
											break;
										case "s":
											if (!$r->moveToAttribute("n")) {
												throw new Exception("Не найден атрибут n у элемента s");
											}
											$s_name = $r->value;
											$r->moveToElement();
											$tmp[$s_name] = $this->xml_read_collect_r($r, $s_name);
											break;
										default:
											throw new Exception("Неожиданный элемент {$r->name} в <o n=\"works\">");
											break;
									}
									break;
								case $r::END_ELEMENT:
									if ("o" !== $r->name) {
										throw new Exception("Неожиданный конец элемента {$r->name} в <o n=\"works\">");
									}
									break 2;
							}
						}
						$result[] = $tmp;
					}
					break;
				case $r::END_ELEMENT:
					if ("s" === $r->name) {
						break 2;
					}
			}
		}
		return $result;
	}

	/**
	 * Формирование запроса для получения текущих версий используемых справочников (directory-versions)
	 * @return string
	 * @throws Exception
	 */
	function lis_directory_versions()
	{
		$w = $this->xml_startXmlRequestWriterDocument("directory-versions");
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Формирует XML для запроса для получения содержимого справочника
	 * @param $directory_name
	 * @return string
	 * @throws Exception
	 */
	function lis_directory($directory_name)
	{
		$w = $this->xml_startXmlRequestWriterDocument("directory");
		$this->xml_add_f($w, "name", $directory_name);
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Формирует XML для запроса сохранения элемента справочника
	 * @param $directory_name
	 * @param $element
	 * @return string
	 * @throws Exception
	 */
	function lis_directory_save($directory_name, $element)
	{
		$w = $this->xml_startXmlRequestWriterDocument("directory-save");
		$this->xml_add_f($w, "directory", $directory_name, false);
		$w->startElement("o");
		if (isset($element["id"])) {
			$w->writeAttribute("id", $element["id"]);
		}
		$w->writeAttribute("n", "element");
		foreach ($element["fields"] as $f_name => $f_value) {
			$this->xml_add_f($w, $f_name, $f_value, false);
		}
		$w->endElement();
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Формирует XML для запроса выгрузки содержимого заявок
	 * @param $filter
	 * @param bool $isInWindows1251
	 * @return string
	 * @throws Exception
	 */
	function lis_registration_journal($filter, $isInWindows1251 = true)
	{
		if ($isInWindows1251) {
			array_walk_recursive($filter, "ConvertFromWin1251ToUTF8");
		}
		//параметры onlyDelayed, markPlanDeviation и emptyPayCategory не используются и всегда = false, а без них запрос падает
		$filter["onlyDelayed"] = false;
		$filter["markPlanDeviation"] = false;
		$filter["emptyPayCategory"] = false;
		$collectionsR = ["payCategories", "doctors", "hospitals", "custDepartments", "customStates", "requestForms", "internalNrs", "patientCodes"];
		$andOrListR = ["targets", "departments"];
		$andOrListF = [];
		$collectionsF = ["states"];
		return $this->xml_composeFilter("registration-journal", $filter, $andOrListR, $andOrListF, $collectionsR, $collectionsF, []);
	}

	/**
	 * Формирует XML-запрос для создания заявок (create-requests-2) из массива установленного образца. При ошибке в структуре массива вываливает исключение.
	 * Переменные в массиве должны быть строго типизированы, даты должны быть объектами класса Datetime.
	 * По умолчанию предполагается, что строки во входном массиве $request_data закодированы в Windows-1251.
	 * Если данные закодированы в utf-8, необходимо передать $isInWindows1251 = false.
	 *
	 * @param $request_data
	 * @param bool $isInWindows1251
	 * @return string
	 * @throws Exception
	 */
	function lis_create_requests_2($request_data, $isInWindows1251 = true)
	{
		if (!is_array($request_data)) {
			throw new Exception("Неправильный параметр: ожидается массив");
		}
		if ($isInWindows1251) {
			array_walk_recursive($request_data, "ConvertFromWin1251ToUTF8");
		}
		$w = $this->xml_startXmlRequestWriterDocument("create-requests-2");
		if (!isset($request_data["o"])) {
			throw new Exception("Не указаны обязательные данные: отсутствует элемент \"o\"");
		}
		$w->startElement("o");
		if (!is_array($request_data["o"])) {
			throw new Exception("Элемент \"o\" не является массивом");
		}
		if (isset($request_data["o"]["id"])) {
			$this->xml_add_f($w, "id", $request_data["o"]["id"]);
		}
		$this->xml_add_f($w, "registrationFormCode", $request_data["o"]["registrationFormCode"]);
		$this->xml_add_f($w, "hospitalCode", $request_data["o"]["hospitalCode"]);
		if (isset($request_data["o"]["hospitalName"])) {
			$this->xml_add_f($w, "hospitalName", $request_data["o"]["hospitalName"]);
		}
		$this->xml_add_f($w, "internalNr", $request_data["o"]["internalNr"]);
		$fields = ["custDepartmentCode" => "custDepartmentName", "doctorCode" => "doctorName"];
		foreach ($fields as $fieldKey => $fieldValue) {
			if (isset($request_data["o"][$fieldKey])) {
				$this->xml_add_f($w, $fieldKey, $request_data["o"][$fieldKey]);
				if (isset($request_data["o"][$fieldValue])) {
					$this->xml_add_f($w, $fieldValue, $request_data["o"][$fieldValue]);
				}
			}
		}
		$fields = ["samplingDate", "sampleDeliveryDate", "priority", "readonly"];
		foreach ($fields as $field) {
			if (isset($request_data["o"][$field])) {
				$this->xml_add_f($w, $field, $request_data["o"][$field]);
			}
		}
		if (!isset($request_data["o"]["patient"])) {
			throw new Exception("Не указаны обязательные данные: отсутствуют данные о пациенте (элемент \"patient\")");
		}
		if (!is_array($request_data["o"]["patient"])) {
			throw new Exception("Раздел patient не является массивом");
		}
		$w->startElement("o");
		$w->writeAttribute("n", "patient");
		$fields = ["code", "firstName", "lastName", "middleName"];
		foreach ($fields as $field) {
			$this->xml_add_f($w, $field, $request_data["o"]["patient"][$field]);
		}
		$fields = ["birthDay", "birthMonth", "birthYear", "sex", "country", "city", "street", "building", "flat", "insuranceCompany", "policySeries", "policyNumber"];
		foreach ($fields as $field) {
			if (isset($request_data["o"]["patient"][$field])) {
				$this->xml_add_f($w, $field, $request_data["o"]["patient"][$field]);
			}
		}
		if (isset($request_data["o"]["patient"]["patientCard"])) {
			if (!is_array($request_data["o"]["patient"]["patientCard"])) {
				throw new Exception("patientCard не является массивом");
			}
			$w->startElement("o");
			$w->writeAttribute("n", "patientCard");
			if (isset($request_data["o"]["patient"]["patientCard"]["cardNr"])) {
				$this->xml_add_f($w, "cardNr", $request_data["o"]["patient"]["patientCard"]["cardNr"]);
			}
			if (isset($request_data["o"]["patient"]["patientCard"]["userValues"])) {
				if (!is_array($request_data["o"]["patient"]["patientCard"]["userValues"])) {
					throw new Exception("Раздел o.patient.patientCard.userValues не является массивом");
				}
				$this->xml_add_userValues_block($w, $request_data["o"]["patient"]["patientCard"]["userValues"]);
			}
			$w->endElement();//o-patientCard
		}
		if (isset($request_data["o"]["patient"]["userValues"])) {
			if (!is_array($request_data["o"]["patient"]["userValues"])) {
				throw new Exception("Раздел o.patient.userValues не является массивом");
			}
			$this->xml_add_userValues_block($w, $request_data["o"]["patient"]["userValues"]);
		}
		$w->endElement();//o-patient
		if (isset($request_data["o"]["samples"])) {
			if (!is_array($request_data["o"]["samples"])) {
				throw new Exception("Элемент \"samples\" не является массивом");
			}
			$w->startElement("s");
			$w->writeAttribute("n", "samples");
			foreach ($request_data["o"]["samples"] as $key => $sample) {
				if (!is_array($sample)) {
					throw new Exception("Элемент \"samples[{$key}]\" не является массивом");
				}
				$w->startElement("o");
				if (isset($sample["internalNr"])) {
					$this->xml_add_f($w, "internalNr", $sample["internalNr"]);
				}
				if (isset($sample["biomaterial"])) {
					$w->startElement("r");
					$w->writeAttribute("n", "biomaterial");
					$w->writeAttribute("i", $sample["biomaterial"]);
					$w->endElement();//r
				}
				if (!isset($sample["targets"])) {
					throw new Exception("Элемент \"samples[{$key}]\" не содержит обязательный параметр: Исследования, которые необходимо выполнить для данной пробы (targets)");
				}
				if (!is_array($sample["targets"])) {
					throw new Exception("Элемент \"samples[{$key}][targets]\" не является массивом");
				}
				$w->startElement("s");
				$w->writeAttribute("n", "targets");
				foreach ($sample["targets"] as $target_idx => $target) {
					if (!is_array($target)) {
						throw new Exception("Элемент \"samples[{$key}][targets][{$target_idx}]\" не является массивом");
					}
					$w->startElement("o");
					$w->startElement("r");
					$w->writeAttribute("n", "target");
					$w->writeAttribute("i", $target_idx);
					$w->endElement();//r
					if (isset($target["cancel"])) {
						$this->xml_add_f($w, "cancel", $target["cancel"]);
					}
					if (isset($target["readonly"])) {
						$this->xml_add_f($w, "readonly", $target["readonly"]);
					}
					if (isset($target["tests"])) {
						if (!is_array($target["tests"])) {
							throw new Exception("Элемент \"samples[{$key}][targets][{$target_idx}][tests]\" не является массивом");
						}
						$w->startElement("s");
						$w->writeAttribute("n", "tests");
						foreach ($target["tests"] as $test_idx => $test) {
							$w->startElement("r");
							$w->writeAttribute("i", $test);
							$w->endElement();//r
						}
						$w->endElement();//s
					}
					$w->endElement();//o
				}
				$w->endElement();//s-targets
				$w->endElement();//o
			}
			$w->endElement();//s
		}
		if (isset($request_data["o"]["userValues"])) {
			if (!is_array($request_data["o"]["userValues"])) {
				throw new Exception("Раздел o.userValues не является массивом");
			}
			$this->xml_add_userValues_block($w, $request_data["o"]["userValues"]);
		}
		$w->endElement();
		$w->endDocument();
		$result = $this->xml_endXmlRequestWriterDocument($w);
		return $result;
	}

	/**
	 * Тест запроса create-requests
	 * @param $request_data
	 * @param bool $isInWindows1251
	 * @return string
	 * @throws Exception
	 */
	function lis_create_requests($request_data, $isInWindows1251 = true)
	{
		if (!is_array($request_data)) {
			throw new Exception("Неправильный параметр: ожидается массив");
		}
		if ($isInWindows1251) {
			array_walk_recursive($request_data, "ConvertFromWin1251ToUTF8");
		}
		$w = $this->xml_startXmlRequestWriterDocument("create-requests");
		if (!isset($request_data["o"])) {
			throw new Exception("Не указаны обязательные данные: отсутствует элемент \"o\"");
		}
		$w->startElement("o");
		$this->xml_add_f($w, "createWorklists", $request_data["createWorklists"], false);
		if (!is_array($request_data["o"])) {
			throw new Exception("Элемент \"o\" не является массивом");
		}
		$w->startElement("o");
		$r_elems = ["requestForm", "patient"];
		foreach ($request_data["o"] as $n => $el) {
			if (is_array($el)) {
				if ($n === "samples") {
					$w->startElement("s");
					$w->writeAttribute("n", $n);
					$w->startElement("o");
					foreach ($el as $sample) {
						if (isset($sample["defectTypes"])) {
							$this->xml_add_s($w, "defectTypes", $sample["defectTypes"]);
						}
					}
					$w->endElement();//o
					$w->endElement();//s
				} else {
					$this->xml_add_s($w, $n, $el, "r");
				}
			} else {
				if (!is_null($el)) {
					if (in_array($n, $r_elems)) {
						$w->startElement("r");
						$w->writeAttribute("n", $n);
						$w->writeAttribute("i", $el);
						$w->endElement();//r
					} else {
						$this->xml_add_f($w, $n, $el, false);
					}
				}
			}
		}
		$w->endElement();//o
		$w->startElement("s");
		$w->endElement();
		$w->endElement();//o
		$w->endDocument();
		$result = $this->xml_endXmlRequestWriterDocument($w);
		return $result;
	}

	/**
	 * @param $filter
	 * @param bool $isInWindows1251
	 * @return string
	 * @throws Exception
	 */
	function lis_work_journal($filter, $isInWindows1251 = true)
	{
		$andOrListR = ["equipments", "worklists", "testFilter", "targets"];
		if ($isInWindows1251) {
			array_walk_recursive($filter, "ConvertFromWin1251ToUTF8");
		}
		$andOrListR = ["equipments", "worklists", "testFilter", "targets"];
		$andOrListF = ["workStates", "normality"];
		$collectionsR = ["biomaterials", "doctors", "custDepartments", "hospitals", "payCategories"];
		$collectionsF = [];
		$fieldsR = ["department"];
		return $this->xml_composeFilter("work-journal", $filter, $andOrListR, $andOrListF, $collectionsR, $collectionsF, $fieldsR);
	}

	/**
	 * @param $sample
	 * @return string
	 * @throws Exception
	 */
	function lis_request_works($sample)
	{
		$w = $this->xml_startXmlRequestWriterDocument("request-works");
		$w->startElement("r");
		$w->writeAttribute("n", "sample");
		$w->writeAttribute("i", $sample);
		$w->endElement();//r
		return $this->xml_endXmlRequestWriterDocument($w);

	}

	/**
	 * @param $sample
	 * @return string
	 * @throws Exception
	 */
	function lis_sample_info($sample)
	{
		$w = $this->xml_startXmlRequestWriterDocument("sample-info");
		$w->startElement("r");
		$w->writeAttribute("n", "sample");
		$w->writeAttribute("i", $sample);
		$w->endElement();//r
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * @param $requestId
	 * @return string
	 * @throws Exception
	 */
	function lis_request_samples($requestId)
	{
		$w = $this->xml_startXmlRequestWriterDocument("request-samples");
		$w->startElement("r");
		$w->writeAttribute("n", "request");
		$w->writeAttribute("i", $requestId);
		$w->endElement();//r
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * @param $response
	 * @return array
	 * @throws Exception
	 */
	function lis_parse_responce_registration_journal($response)
	{
		$result = [];
		$r = new XMLReader();
		$r->xml($response);
		if (!$this->xml_read_moveTo($r, "s", "request")) {
			throw new Exception("Элемент <s n=request> не найден");
		}
		while ($this->xml_read_moveTo($r, "o", "")) {
			//для каждого o
			$tmp = [];
			while ($r->read()) {
				switch ($r->nodeType) {
					case XMLReader::ELEMENT:
						if ($r->nodeType === XMLReader::ELEMENT) {
							switch ($r->name) {
								case "f":
									$this->xml_read_collect_f($r, $tmp);
									break;
								case "s":
									if (!$r->moveToAttribute("n")) {
										throw new Exception("Элемент s без атрибута n");
									}
									if ($r->value !== "userValues") {
										throw new Exception("Неожиданный элемент <s ... n=\"{$r->value}\" ...>");
									}
									$r->moveToElement();
									$tmp["userValues"] = $this->xml_read_collect_userValues($r);
							}
						}
						break;
					case XMLReader::END_ELEMENT:
						break 2;
				}
			}
			$result[] = $tmp;
		}
		array_walk_recursive($result, "ConvertFromUTF8ToWin1251");
		return $result;
	}

	/**
	 * @param $response
	 * @return array
	 * @throws Exception
	 */
	function lis_parse_responce_request_works($response)
	{
		$result = [];
		$r = new XMLReader();
		$r->xml($response);
		if (!$this->xml_read_moveTo($r, "o", "")) {
			throw new Exception("Элемент <o \"n\"=\"\"> не найден");
		}
		while ($r->read()) {
			switch ($r->nodeType) {
				case $r::ELEMENT:
					switch ($r->name) {
						case "f":
							$this->xml_read_collect_f($r, $result);
							break;
						case "s":
							if (!$r->moveToAttribute("n")) {
								throw new Exception("Не найден атрибут n элемента s в <o n=\"\">");
							}
							switch ($r->value) {
								case "patientGroups":
									$result["patientGroups"] = $this->xml_read_collect_r($r, "patientGroups");
									break;
								case "works":
									$result["works"] = $this->xml_read_collect_works($r);
									break;
								default:
									throw new Exception("Неожиданный элемент <s n=\"{$r->value}\">");
									break;
							}
							break;
						default:
							throw new Exception("Неожиданный элемент {$r->name}в <o n=\"\">");
							break;
					}
					break;
				case $r::END_ELEMENT:
					break 2;
			}
		}
		array_walk_recursive($result, "ConvertFromUTF8ToWin1251");
		return $result;
	}

	/**
	 * @param $response
	 * @return array
	 * @throws Exception
	 */
	function lis_parse_responce_work_journal($response)
	{
		$result = [];
		$r = new XMLReader();
		$r->xml($response);
		if (!$this->xml_read_moveTo($r, "s", "samples")) {
			throw new Exception("Элемент <s \"n\"=\"samples\"> не найден");
		}
		while ($r->read()) {
			switch ($r->nodeType) {
				case $r::ELEMENT:
					if ("o" === $r->name) {
						if (!$r->moveToAttribute("n")) {
							throw new Exception("Не найден атрибут n у элемента o в <s n=\"samples\">");
						}
						if ("" !== $r->value) {
							throw new Exception("Неожиданное значение {$r->value} атрибута n у элемента o в <s n=\"samples\">");
						}
						$tmp = [];
						while ($r->read()) {
							switch ($r->nodeType) {
								case $r::ELEMENT:
									switch ($r->name) {
										case "f":
											$this->xml_read_collect_f($r, $tmp);
											break;
										case "s":
											if (!$r->moveToAttribute("n")) {
												throw new Exception("Не указан атрибут n у элемента s в <o n=\"\">");
											}
											switch ($r->value) {
												case "targets":
													$r->moveToElement();
													$tmp["targets"] = $this->xml_read_collect_r($r, "targets");
													break;
												case "userValues":
													$r->moveToElement();
													$tmp["userValues"] = $this->xml_read_collect_userValues($r);
													break;
											}
											break;
										default:
											throw new Exception("Неожиданный элемент {$r->name} в <o n=\"\">");
											break;
									}
									break;
								case $r::END_ELEMENT:
									if ("o" !== $r->name) {
										throw new Exception("Неожиданный конец элемента {$r->name} в <o n=\"\">");
									}
									break 2;
							}
						}
						$result[] = $tmp;
					}
					break;
			}
		}
		array_walk_recursive($result, "ConvertFromUTF8ToWin1251");
		return $result;
	}

	/**
	 * @param $response
	 * @return array
	 * @throws Exception
	 */
	function lis_parse_responce_sample_info($response)
	{
		$result = [];
		$r = new XMLReader();
		$r->xml($response);
		if (!$this->xml_read_moveTo($r, "o", "")) {
			throw new Exception("Элемент <o \"n\"=\"\"> не найден");
		}
		while ($r->read()) {
			switch ($r->nodeType) {
				case $r::ELEMENT:
					switch ($r->name) {
						case "f":
							$this->xml_read_collect_f($r, $result);
							break;
						case "s":
							if (!$r->moveToAttribute("n")) {
								throw new Exception("Не найден атрибут n у элемента s");
							}
							if ("works" === $r->value) {
								$result["works"] = $this->xml_read_collect_works($r);
							} else {
								$s_name = $r->value;
								$r->moveToElement();
								$result[$s_name] = $this->xml_read_collect_r($r, $s_name);
							}
							break;
						case "r":
							if (!$r->moveToAttribute("n")) {
								throw new Exception("Не найден атрибут n у элемента r");
							}
							$key = $r->value;
							if (!$r->moveToAttribute("i")) {
								throw new Exception("Не найден атрибут i у элемента r");
							}
							$result[$key] = (int)$r->value;
							break;
						default:
							throw new Exception("Неожиданный элемент {$r->name} в <o \"n\"=\"\">");
							break;
					}
					break;
				case $r::END_ELEMENT:
					if ("o" !== $r->name) {
						throw new Exception("Неожиданный конец элемента {$r->name} в <o \"n\"=\"\">");
					}
					break 2;
			}
		}
		array_walk_recursive($result, "ConvertFromUTF8ToWin1251");
		return $result;
	}

	/**
	 * @param $response
	 * @return array
	 * @throws Exception
	 */
	function lis_parse_responce_request_sample($response)
	{
		$result = [];
		$r = new XMLReader();
		$r->xml($response);
		if (!$this->xml_read_moveTo($r, "s", "samples")) {
			throw new Exception("Элемент <s \"n\"=\"samples\"> не найден");
		}
		while ($r->read()) {
			switch ($r->nodeType) {
				case $r::ELEMENT:
					if ("o" === $r->name) {
						if (!$r->moveToAttribute("n")) {
							throw new Exception("Не найден атрибут n у элемента o");
						}
						if ("" === $r->value) {
							$tmp = [];
							while ($r->read()) {
								switch ($r->nodeType) {
									case $r::ELEMENT:
										switch ($r->name) {
											case "f":
												$this->xml_read_collect_f($r, $tmp);
												break;
											case "s":
												if (!$r->moveToAttribute("n")) {
													throw new Exception("Не найден атрибут n у элемента s");
												}
												$s_name = $r->value;
												$r->moveToElement();
												$tmp[$s_name] = $this->xml_read_collect_r($r, $s_name);
												break;
											case "r":
												if (!$r->moveToAttribute("n")) {
													throw new Exception("Не найден атрибут n у элемента r");
												}
												$key = $r->value;
												if (!$r->moveToAttribute("i")) {
													throw new Exception("Не найден атрибут i у элемента r");
												}
												$tmp[$key] = (int)$r->value;
												break;
											default:
												throw new Exception("Неожиданный элемент {$r->name} в <o \"n\"=\"\">");
												break;
										}
										break;
									case $r::END_ELEMENT:
										if ("o" !== $r->name) {
											throw new Exception("Неожиданный конец элемента {$r->name} в <o \"n\"=\"\">");
										}
										break 2;
								}
							}
							$result[] = $tmp;
						}
					}
					break;
				case $r::END_ELEMENT:
					if ("s" !== $r->name) {
						throw new Exception("Неожиданный конец элемента {$r->name} в <s \"n\"=\"samples\">");
					}
					break 2;
			}
		}
		array_walk_recursive($result, "ConvertFromUTF8ToWin1251");
		return $result;
	}
}