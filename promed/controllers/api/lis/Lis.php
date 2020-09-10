<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Lis
 * @OA\Tag(
 *     name="Lis",
 *     description="Взаимодействие со службой ЛИС"
 * )
 */
class Lis extends SwREST_Controller
{
	protected $inputRules = array(
		'getLisFolder' => array(
		),
		'getGroups' => array(
		),
		'login' => array(
		),
		'getDirectoryVersions' => array(
		),
		'createRequest2' => array(
		),
		'getDirectory' => array(
			array('field' => 'name', 'label' => 'Справочник', 'rules' => 'required', 'type' => 'string'),
		),
		'testXml' =>array(
			array('field' => 'xml', 'label' => 'Xml', 'rules' => '', 'type' => 'string'),
			array('field' => 'server', 'label' => 'Server', 'rules' => '', 'type' => 'string'),
		),
		'saveLisRequestFromLabSamples' => array(
			array('field' => 'EvnLabSample_idsJSON', 'label' => 'EvnLabSample_idsJSON', 'rules' => 'required', 'type' => 'string'),
		),
		'createRequestSelections' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'EvnLabSample_id', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabSamples', 'label' => 'Набор проб', 'rules' => '', 'type' => 'string'),
		),
		'createRequestSelectionsLabRequest' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'EvnLabRequest_id', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabRequests', 'label' => 'Набор заявок', 'rules' => '', 'type' => 'string'),
		),
		'getXml' => array(
			array('field' => 'request_type', 'label' => 'request_type', 'rules' => 'required', 'type' => 'string'),
		),
		'sample' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'EvnLabSample_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnLabSamples', 'label' => 'EvnLabSamples', 'rules' => '', 'type' => 'string'),
		),
		'syncSampleDefects' => array(
		),
		'listRegistrationJournal' => array(
			array('field' => 'nr', 'label' => 'Номер', 'rules' => '', 'type' => 'string'),
			array('field' => 'dateFrom', 'label' => 'Дата поступления заявки «С»', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'dateTill', 'label' => 'Дата поступления заявки «По»', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'endDateFrom', 'label' => 'Дата закрытия заявки «С»', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'endDateTill', 'label' => 'Дата закрытия заявки «По»', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'lastModificationDateFrom', 'label' => 'Дата последнего изменения заявки «С»', 'rules' => '', 'type' => 'date'),
			array('field' => 'priority', 'label' => 'Приоритет заявки', 'rules' => '', 'type' => 'string'),
			array('field' => 'defectState', 'label' => 'Наличие браков в заявке', 'rules' => '', 'type' => 'string'),
			array('field' => 'payCategories', 'label' => 'Множество ссылок на справочник Категория оплаты', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'lastName', 'label' => 'Фамилия пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'firstName', 'label' => 'Имя пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'middleName', 'label' => 'Отчество пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'birthDate', 'label' => 'Дата рождения пациента', 'rules' => '', 'type' => 'date'),
			array('field' => 'sex', 'label' => 'Пол пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'patientNr', 'label' => 'Номер пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'billNr', 'label' => 'Номер выставленного счета', 'rules' => '', 'type' => 'string'),
			array('field' => 'states', 'label' => 'Статусы заявок', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'doctors', 'label' => 'Врачи заказчики', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'hospitals', 'label' => 'Заказчики', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'custDepartments', 'label' => 'Отделения заказчика', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'departments', 'label' => 'Подразделения, в которых выполняется заявка', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'targets', 'label' => 'Исследования, входящие в заявку', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'lastTimestamp', 'label' => 'Метка последнего изменения записи', 'rules' => '', 'type' => 'string', 'default' => 0),
			array('field' => 'customStates', 'label' => 'Дополнительные статусы заявки', 'rules' => '', 'type' => 'string', 'default' => ''),
			array('field' => 'requestForms', 'label' => 'Регистрационные формы, при помощи которых создается заявка', 'rules' => '', 'type' => 'string'),
		),
		'saveLisRequest' => array(
			array('field' => 'Id',					'label' => 'ИД',						'rules' => '','type' => 'string'),
			array('field' => 'InternalNr',			'label' => 'Номер',						'rules' => '','type' => 'string'),
			array('field' => 'HospitalCode',		'label' => 'Код ЛПУ',					'rules' => '','type' => 'string'),
			array('field' => 'HospitalName',		'label' => 'Наименование ЛПУ',			'rules' => '','type' => 'string'),
			array('field' => 'CustDepartmentCode',	'label' => 'Код подразделения',			'rules' => '','type' => 'string'),
			array('field' => 'CustDepartmentName',	'label' => 'Наименование подразделения','rules' => '','type' => 'string'),
			array('field' => 'DoctorCode',			'label' => 'Код врача',					'rules' => '','type' => 'string'),
			array('field' => 'DoctorName',			'label' => 'ФИО врача',					'rules' => '','type' => 'string'),
			array('field' => 'RegistrationFormCode','label' => 'Код формы',					'rules' => '','type' => 'string'),
			array('field' => 'SamplingDate',		'label' => 'Дата взятия',				'rules' => '','type' => 'datetime'),
			array('field' => 'SampleDeliveryDate',	'label' => 'Дата доставки',				'rules' => '','type' => 'datetime'),
			array('field' => 'PregnancyDuration',	'label' => 'Срок беременности',			'rules' => '','type' => 'string'),
			array('field' => 'CyclePeriod',			'label' => 'Фаза цикла',				'rules' => '','type' => 'string'),
			array('field' => 'ReadOnly',			'label' => 'Только чтение',				'rules' => '','type' => 'string'),
			array('field' => 'Priority',			'label' => 'Приоритет',					'rules' => '','type' => 'int'),
			array('field' => 'Code',				'label' => 'Код пациента',				'rules' => '','type' => 'string'),
			array('field' => 'CardNr',				'label' => 'Номер карты',				'rules' => '','type' => 'string'),
			array('field' => 'FirstName',			'label' => 'Имя',						'rules' => '','type' => 'string'),
			array('field' => 'LastName',			'label' => 'Фамилия',					'rules' => '','type' => 'string'),
			array('field' => 'MiddleName',			'label' => 'Отчество',					'rules' => '','type' => 'string'),
			array('field' => 'BirthDay',			'label' => 'Дата рождения',				'rules' => '','type' => 'date'),
			array('field' => 'Sex',					'label' => 'Пол',						'rules' => '','type' => 'string'),
			array('field' => 'Country',				'label' => 'Страна',					'rules' => '','type' => 'string'),
			array('field' => 'City',				'label' => 'Город',						'rules' => '','type' => 'string'),
			array('field' => 'Street',				'label' => 'Улица',						'rules' => '','type' => 'string'),
			array('field' => 'Building',			'label' => 'Дом',						'rules' => '','type' => 'string'),
			array('field' => 'Flat',				'label' => 'Квартира',					'rules' => '','type' => 'string'),
			array('field' => 'InsuranceCompany',	'label' => 'Страховая компания',		'rules' => '','type' => 'string'),
			array('field' => 'PolicySeries',		'label' => 'Номер полиса',				'rules' => '','type' => 'string'),
			array('field' => 'PolicyNumber',		'label' => 'Серия полиса',				'rules' => '','type' => 'string'),
			array('field' => 'Biomaterial',			'label' => 'Биоматериал',				'rules' => '','type' => 'int'),
			array('field' => 'InternalNrBarCode',	'label' => 'Штрих-код',					'rules' => '','type' => 'string'),
			array('field' => 'Target',				'label' => 'Исследование',				'rules' => '','type' => 'int'),
			array('field' => 'Cancel',				'label' => 'Отмена',					'rules' => '','type' => 'string'),
			array('field' => 'ReadOnly',			'label' => 'Только чтение',				'rules' => '','type' => 'string')
		),
	);

	public $server = array();//редактируются через форму "Общие настройки", умолчания прописаны в Options_model.php
	public $map = array(
		// Оказывается у нас таблицы называются не так, как в ЛИС справочники, что грустно.
		// Пришлось ввести параметр table
		'target' => array('table'=>'Target', 'name'=>'Target_Name', 'code'=>'Target_Code', 'mnemonics'=>'Target_SysNick', 'id'=>'Target_id', 'removed'=>'Target_Deleted', 'pmUser_id'=>'pmUser_id'),
		'category' => array('table'=>'Category','name'=>'Category_Name', 'code'=>'Category_Code', 'mnemonics'=>'Category_SysNick', 'id'=>'Category_id', 'removed'=>'Category_Deleted', 'pmUser_id'=>'pmUser_id'),
		'requestCustomState' => array('table'=>'CustomState','name'=>'CustomState_Name', 'code'=>'CustomState_Code', 'mnemonics'=>'CustomState_SysNick', 'id'=>'CustomState_id', 'removed'=>'CustomState_Deleted', 'pmUser_id'=>'pmUser_id'),
		'defectState' => array('table'=>'defectState','name'=>'defectState_Name', 'code'=>'defectState_Code', 'mnemonics'=>'defectState_SysNick', 'id'=>'defectState_id', 'removed'=>'defectState_Deleted', 'pmUser_id'=>'pmUser_id'),
		'priority' => array('table'=>'priority','name'=>'priority_Name', 'code'=>'priority_Code', 'mnemonics'=>'priority_SysNick', 'id'=>'priority_id', 'removed'=>'priority_Deleted', 'pmUser_id'=>'pmUser_id'),
		'states' => array('table'=>'states','name'=>'states_Name', 'code'=>'states_Code', 'mnemonics'=>'states_SysNick', 'id'=>'states_id', 'removed'=>'states_Deleted', 'pmUser_id'=>'pmUser_id'),
		'sex' => array('table'=>'sex','name'=>'sex_Name', 'code'=>'sex_Code', 'mnemonics'=>'sex_SysNick', 'id'=>'sex_id', 'removed'=>'sex_Deleted', 'pmUser_id'=>'pmUser_id'),
		'profile'             => array('table'=>'profile'             , 'name'=>'profile_Name'            , 'code'=>'profile_Code'            , 'mnemonics'=>'profile_SysNick'            , 'id'=>'profile_id'            , 'removed'=>'profile_Deleted'            , 'pmUser_id'=>'pmUser_id'),
		'targetGroup'         => array('table'=>'targetGroup'         , 'name'=>'targetGroup_Name'        , 'code'=>'targetGroup_Code'        , 'mnemonics'=>'targetGroup_SysNick'        , 'id'=>'targetGroup_id'        , 'removed'=>'targetGroup_Deleted'        , 'pmUser_id'=>'pmUser_id'),
		'formLayout'          => array('table'=>'formLayout'          , 'name'=>'formLayout_Name'         , 'code'=>'formLayout_Code'         , 'mnemonics'=>'formLayout_SysNick'         , 'id'=>'formLayout_id'         , 'removed'=>'formLayout_Deleted'         , 'pmUser_id'=>'pmUser_id'),
		'storage'             => array('table'=>'storage'             , 'name'=>'storage_Name'            , 'code'=>'storage_Code'            , 'mnemonics'=>'storage_SysNick'            , 'id'=>'storage_id'            , 'removed'=>'storage_Deleted'            , 'pmUser_id'=>'pmUser_id'),
		'printFormUnit'       => array('table'=>'printFormUnit'       , 'name'=>'printFormUnit_Name'      , 'code'=>'printFormUnit_Code'      , 'mnemonics'=>'printFormUnit_SysNick'      , 'id'=>'printFormUnit_id'      , 'removed'=>'printFormUnit_Deleted'      , 'pmUser_id'=>'pmUser_id'),
		'externalSystem'      => array('table'=>'externalSystem'      , 'name'=>'externalSystem_Name'     , 'code'=>'externalSystem_Code'     , 'mnemonics'=>'externalSystem_SysNick'     , 'id'=>'externalSystem_id'     , 'removed'=>'externalSystem_Deleted'     , 'pmUser_id'=>'pmUser_id'),
		'commentSource'       => array('table'=>'commentSource'       , 'name'=>'commentSource_Name'      , 'code'=>'commentSource_Code'      , 'mnemonics'=>'commentSource_SysNick'      , 'id'=>'commentSource_id'      , 'removed'=>'commentSource_Deleted'      , 'pmUser_id'=>'pmUser_id'),
		'printForm'           => array('table'=>'printForm'           , 'name'=>'printForm_Name'          , 'code'=>'printForm_Code'          , 'mnemonics'=>'printForm_SysNick'          , 'id'=>'printForm_id'          , 'removed'=>'printForm_Deleted'          , 'pmUser_id'=>'pmUser_id'),
		'pricelist'           => array('table'=>'pricelist'           , 'name'=>'pricelist_Name'          , 'code'=>'pricelist_Code'          , 'mnemonics'=>'pricelist_SysNick'          , 'id'=>'pricelist_id'          , 'removed'=>'pricelist_Deleted'          , 'pmUser_id'=>'pmUser_id'),
		'userRule'            => array('table'=>'userRule'            , 'name'=>'userRule_Name'           , 'code'=>'userRule_Code'           , 'mnemonics'=>'userRule_SysNick'           , 'id'=>'userRule_id'           , 'removed'=>'userRule_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'scanForm'            => array('table'=>'scanForm'            , 'name'=>'scanForm_Name'           , 'code'=>'scanForm_Code'           , 'mnemonics'=>'scanForm_SysNick'           , 'id'=>'scanForm_id'           , 'removed'=>'scanForm_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'streetType'          => array('table'=>'streetType'          , 'name'=>'streetType_Name'         , 'code'=>'streetType_Code'         , 'mnemonics'=>'streetType_SysNick'         , 'id'=>'streetType_id'         , 'removed'=>'streetType_Deleted'         , 'pmUser_id'=>'pmUser_id'),
		'sampleBlank'         => array('table'=>'sampleBlank'         , 'name'=>'sampleBlank_Name'        , 'code'=>'sampleBlank_Code'        , 'mnemonics'=>'sampleBlank_SysNick'        , 'id'=>'sampleBlank_id'        , 'removed'=>'sampleBlank_Deleted'        , 'pmUser_id'=>'pmUser_id'),
		'patient'             => array('table'=>'patient'             , 'name'=>'patient_Name'            , 'code'=>'patient_Code'            , 'mnemonics'=>'patient_SysNick'            , 'id'=>'patient_id'            , 'removed'=>'patient_Deleted'            , 'pmUser_id'=>'pmUser_id'),
		'worklistDefGroup'    => array('table'=>'worklistDefGroup'    , 'name'=>'worklistDefGroup_Name'   , 'code'=>'worklistDefGroup_Code'   , 'mnemonics'=>'worklistDefGroup_SysNick'   , 'id'=>'worklistDefGroup_id'   , 'removed'=>'worklistDefGroup_Deleted'   , 'pmUser_id'=>'pmUser_id'),
		'qcTestGroup'         => array('table'=>'qcTestGroup'         , 'name'=>'qcTestGroup_Name'        , 'code'=>'qcTestGroup_Code'        , 'mnemonics'=>'qcTestGroup_SysNick'        , 'id'=>'qcTestGroup_id'        , 'removed'=>'qcTestGroup_Deleted'        , 'pmUser_id'=>'pmUser_id'),
		'testPrintGroup'      => array('table'=>'testPrintGroup'      , 'name'=>'testPrintGroup_Name'     , 'code'=>'testPrintGroup_Code'     , 'mnemonics'=>'testPrintGroup_SysNick'     , 'id'=>'testPrintGroup_id'     , 'removed'=>'testPrintGroup_Deleted'     , 'pmUser_id'=>'pmUser_id'),
		'equipmentTestGroups' => array('table'=>'equipmentTestGroups' , 'name'=>'equipmentTestGroups_Name', 'code'=>'equipmentTestGroups_Code', 'mnemonics'=>'equipmentTestGroups_SysNick', 'id'=>'equipmentTestGroups_id', 'removed'=>'equipmentTestGroups_Deleted', 'pmUser_id'=>'pmUser_id'),
		'doctor'              => array('table'=>'doctor'              , 'name'=>'doctor_Name'             , 'code'=>'doctor_Code'             , 'mnemonics'=>'doctor_SysNick'             , 'id'=>'doctor_id'             , 'removed'=>'doctor_Deleted'             , 'pmUser_id'=>'pmUser_id'),
		'myelogramm'          => array('table'=>'myelogramm'          , 'name'=>'myelogramm_Name'         , 'code'=>'myelogramm_Code'         , 'mnemonics'=>'myelogramm_SysNick'         , 'id'=>'myelogramm_id'         , 'removed'=>'myelogramm_Deleted'         , 'pmUser_id'=>'pmUser_id'),
		'policyType'          => array('table'=>'policyType'          , 'name'=>'policyType_Name'         , 'code'=>'policyType_Code'         , 'mnemonics'=>'policyType_SysNick'         , 'id'=>'policyType_id'         , 'removed'=>'policyType_Deleted'         , 'pmUser_id'=>'pmUser_id'),
		'printFormNew'        => array('table'=>'printFormNew'        , 'name'=>'printFormNew_Name'       , 'code'=>'printFormNew_Code'       , 'mnemonics'=>'printFormNew_SysNick'       , 'id'=>'printFormNew_id'       , 'removed'=>'printFormNew_Deleted'       , 'pmUser_id'=>'pmUser_id'),
		'constant'            => array('table'=>'constant'            , 'name'=>'constant_Name'           , 'code'=>'constant_Code'           , 'mnemonics'=>'constant_SysNick'           , 'id'=>'constant_id'           , 'removed'=>'constant_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'requestForm'         => array('table'=>'requestForm'         , 'name'=>'requestForm_Name'        , 'code'=>'requestForm_Code'        , 'mnemonics'=>'requestForm_SysNick'        , 'id'=>'requestForm_id'        , 'removed'=>'requestForm_Deleted'        , 'pmUser_id'=>'pmUser_id'),
		'requestFormLayout'   => array('table'=>'requestFormLayout'   , 'name'=>'requestFormLayout_Name'  , 'code'=>'requestFormLayout_Code'  , 'mnemonics'=>'requestFormLayout_SysNick'  , 'id'=>'requestFormLayout_id'  , 'removed'=>'requestFormLayout_Deleted'  , 'pmUser_id'=>'pmUser_id'),
		'defaultPrintForm'    => array('table'=>'defaultPrintForm'    , 'name'=>'defaultPrintForm_Name'   , 'code'=>'defaultPrintForm_Code'   , 'mnemonics'=>'defaultPrintForm_SysNick'   , 'id'=>'defaultPrintForm_id'   , 'removed'=>'defaultPrintForm_Deleted'   , 'pmUser_id'=>'pmUser_id'),
		'hospital'            => array('table'=>'hospital'            , 'name'=>'hospital_Name'           , 'code'=>'hospital_Code'           , 'mnemonics'=>'hospital_SysNick'           , 'id'=>'hospital_id'           , 'removed'=>'hospital_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'bioMaterial'         => array('table'=>'bioMaterial'         , 'name'=>'bioMaterial_Name'        , 'code'=>'bioMaterial_Code'        , 'mnemonics'=>'bioMaterial_SysNick'        , 'id'=>'bioMaterial_id'        , 'removed'=>'bioMaterial_Deleted'        , 'pmUser_id'=>'pmUser_id'),
		'userGraphics'        => array('table'=>'userGraphics'        , 'name'=>'userGraphics_Name'       , 'code'=>'userGraphics_Code'       , 'mnemonics'=>'userGraphics_SysNick'       , 'id'=>'userGraphics_id'       , 'removed'=>'userGraphics_Deleted'       , 'pmUser_id'=>'pmUser_id'),
		'material'            => array('table'=>'material'            , 'name'=>'material_Name'           , 'code'=>'material_Code'           , 'mnemonics'=>'material_SysNick'           , 'id'=>'material_id'           , 'removed'=>'material_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'userGroup'           => array('table'=>'userGroup'           , 'name'=>'userGroup_Name'          , 'code'=>'userGroup_Code'          , 'mnemonics'=>'userGroup_SysNick'          , 'id'=>'userGroup_id'          , 'removed'=>'userGroup_Deleted'          , 'pmUser_id'=>'pmUser_id'),
		'userField'           => array('table'=>'userField'           , 'name'=>'userField_Name'          , 'code'=>'userField_Code'          , 'mnemonics'=>'userField_SysNick'          , 'id'=>'userField_id'          , 'removed'=>'userField_Deleted'          , 'pmUser_id'=>'pmUser_id'),
		'qcEvent'             => array('table'=>'qcEvent'             , 'name'=>'qcEvent_Name'            , 'code'=>'qcEvent_Code'            , 'mnemonics'=>'qcEvent_SysNick'            , 'id'=>'qcEvent_id'            , 'removed'=>'qcEvent_Deleted'            , 'pmUser_id'=>'pmUser_id'),
		'userDirectory'       => array('table'=>'userDirectory'       , 'name'=>'userDirectory_Name'      , 'code'=>'userDirectory_Code'      , 'mnemonics'=>'userDirectory_SysNick'      , 'id'=>'userDirectory_id'      , 'removed'=>'userDirectory_Deleted'      , 'pmUser_id'=>'pmUser_id'),
		'unit'                => array('table'=>'unit'                , 'name'=>'unit_Name'               , 'code'=>'unit_Code'               , 'mnemonics'=>'unit_SysNick'               , 'id'=>'unit_id'               , 'removed'=>'unit_Deleted'               , 'pmUser_id'=>'pmUser_id'),
		'userDirectoryValue'  => array('table'=>'userDirectoryValue'  , 'name'=>'userDirectoryValue_Name' , 'code'=>'userDirectoryValue_Code' , 'mnemonics'=>'userDirectoryValue_SysNick' , 'id'=>'userDirectoryValue_id' , 'removed'=>'userDirectoryValue_Deleted' , 'pmUser_id'=>'pmUser_id'),
		'accessRight'         => array('table'=>'accessRight'         , 'name'=>'accessRight_Name'        , 'code'=>'accessRight_Code'        , 'mnemonics'=>'accessRight_SysNick'        , 'id'=>'accessRight_id'        , 'removed'=>'accessRight_Deleted'        , 'pmUser_id'=>'pmUser_id'),
		'test'                => array('table'=>'test'                , 'name'=>'test_Name'               , 'code'=>'test_Code'               , 'mnemonics'=>'test_SysNick'               , 'id'=>'test_id'               , 'removed'=>'test_Deleted'               , 'pmUser_id'=>'pmUser_id'),
		'requestDistrict'     => array('table'=>'requestDistrict'     , 'name'=>'requestDistrict_Name'    , 'code'=>'requestDistrict_Code'    , 'mnemonics'=>'requestDistrict_SysNick'    , 'id'=>'requestDistrict_id'    , 'removed'=>'requestDistrict_Deleted'    , 'pmUser_id'=>'pmUser_id'),
		'patientGroup'        => array('table'=>'patientGroup'        , 'name'=>'patientGroup_Name'       , 'code'=>'patientGroup_Code'       , 'mnemonics'=>'patientGroup_SysNick'       , 'id'=>'patientGroup_id'       , 'removed'=>'patientGroup_Deleted'       , 'pmUser_id'=>'pmUser_id'),
		'hospitalCategory'    => array('table'=>'hospitalCategory'    , 'name'=>'hospitalCategory_Name'   , 'code'=>'hospitalCategory_Code'   , 'mnemonics'=>'hospitalCategory_SysNick'   , 'id'=>'hospitalCategory_id'   , 'removed'=>'hospitalCategory_Deleted'   , 'pmUser_id'=>'pmUser_id'),
		'qcProducer'          => array('table'=>'qcProducer'          , 'name'=>'qcProducer_Name'         , 'code'=>'qcProducer_Code'         , 'mnemonics'=>'qcProducer_SysNick'         , 'id'=>'qcProducer_id'         , 'removed'=>'qcProducer_Deleted'         , 'pmUser_id'=>'pmUser_id'),
		'customReport'        => array('table'=>'customReport'        , 'name'=>'customReport_Name'       , 'code'=>'customReport_Code'       , 'mnemonics'=>'customReport_SysNick'       , 'id'=>'customReport_id'       , 'removed'=>'customReport_Deleted'       , 'pmUser_id'=>'pmUser_id'),
		'qcMaterial'          => array('table'=>'qcMaterial'          , 'name'=>'qcMaterial_Name'         , 'code'=>'qcMaterial_Code'         , 'mnemonics'=>'qcMaterial_SysNick'         , 'id'=>'qcMaterial_id'         , 'removed'=>'qcMaterial_Deleted'         , 'pmUser_id'=>'pmUser_id'),
		'service'             => array('table'=>'service'             , 'name'=>'service_Name'            , 'code'=>'service_Code'            , 'mnemonics'=>'service_SysNick'            , 'id'=>'service_id'            , 'removed'=>'service_Deleted'            , 'pmUser_id'=>'pmUser_id'),
		'materialUnit'        => array('table'=>'materialUnit'        , 'name'=>'materialUnit_Name'       , 'code'=>'materialUnit_Code'       , 'mnemonics'=>'materialUnit_SysNick'       , 'id'=>'materialUnit_id'       , 'removed'=>'materialUnit_Deleted'       , 'pmUser_id'=>'pmUser_id'),
		'report'              => array('table'=>'report'              , 'name'=>'report_Name'             , 'code'=>'report_Code'             , 'mnemonics'=>'report_SysNick'             , 'id'=>'report_id'             , 'removed'=>'report_Deleted'             , 'pmUser_id'=>'pmUser_id'),
		'city'                => array('table'=>'city'                , 'name'=>'city_Name'               , 'code'=>'city_Code'               , 'mnemonics'=>'city_SysNick'               , 'id'=>'city_id'               , 'removed'=>'city_Deleted'               , 'pmUser_id'=>'pmUser_id'),
		'cityType'            => array('table'=>'cityType'            , 'name'=>'cityType_Name'           , 'code'=>'cityType_Code'           , 'mnemonics'=>'cityType_SysNick'           , 'id'=>'cityType_id'           , 'removed'=>'cityType_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'street'              => array('table'=>'street'              , 'name'=>'street_Name'             , 'code'=>'street_Code'             , 'mnemonics'=>'street_SysNick'             , 'id'=>'street_id'             , 'removed'=>'street_Deleted'             , 'pmUser_id'=>'pmUser_id'),
		'customCommand'       => array('table'=>'customCommand'       , 'name'=>'customCommand_Name'      , 'code'=>'customCommand_Code'      , 'mnemonics'=>'customCommand_SysNick'      , 'id'=>'customCommand_id'      , 'removed'=>'customCommand_Deleted'      , 'pmUser_id'=>'pmUser_id'),
		'treeViewLayout'      => array('table'=>'treeViewLayout'      , 'name'=>'treeViewLayout_Name'     , 'code'=>'treeViewLayout_Code'     , 'mnemonics'=>'treeViewLayout_SysNick'     , 'id'=>'treeViewLayout_id'     , 'removed'=>'treeViewLayout_Deleted'     , 'pmUser_id'=>'pmUser_id'),
		'printFormHeader'     => array('table'=>'printFormHeader'     , 'name'=>'printFormHeader_Name'    , 'code'=>'printFormHeader_Code'    , 'mnemonics'=>'printFormHeader_SysNick'    , 'id'=>'printFormHeader_id'    , 'removed'=>'printFormHeader_Deleted'    , 'pmUser_id'=>'pmUser_id'),
		'serviceShort'        => array('table'=>'serviceShort'        , 'name'=>'serviceShort_Name'       , 'code'=>'serviceShort_Code'       , 'mnemonics'=>'serviceShort_SysNick'       , 'id'=>'serviceShort_id'       , 'removed'=>'serviceShort_Deleted'       , 'pmUser_id'=>'pmUser_id'),
		'supplier'            => array('table'=>'supplier'            , 'name'=>'supplier_Name'           , 'code'=>'supplier_Code'           , 'mnemonics'=>'supplier_SysNick'           , 'id'=>'supplier_id'           , 'removed'=>'supplier_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'testRule'            => array('table'=>'testRule'            , 'name'=>'testRule_Name'           , 'code'=>'testRule_Code'           , 'mnemonics'=>'testRule_SysNick'           , 'id'=>'testRule_id'           , 'removed'=>'testRule_Deleted'           , 'pmUser_id'=>'pmUser_id'),
		'payCategory'         => array('table'=>'payCategory'         , 'name'=>'payCategory_Name'        , 'code'=>'payCategory_Code'        , 'mnemonics'=>'payCategory_SysNick'        , 'id'=>'payCategory_id'        , 'removed'=>'payCategory_Deleted'        , 'pmUser_id'=>'pmUser_id'),
	);
	public $debug = false;
	public $log_to = 'textlog';//Куда выводить лог: textlog|print_r|false
	// Список справочников разрешенный для загрузки
	public $dirs = array(
		//'profile'             ,
		'targetgroup'         ,
		'target'         ,
		'formlayout'          ,
		'storage'             ,
		'printformunit'       ,
		'externalsystem'      ,
		'commentsource'       ,
		'printform'           ,
		'pricelist'           ,
		'userrule'            ,
		'scanform'            ,
		//'streettype'          ,
		//'serviceshort'        ,
		//'sampleblank'         ,
		//'patient'             ,
		//'worklistdefgroup'    ,
		'qctestgroup'         ,
		'testprintgroup'      ,
		//'equipmenttestgroups' ,
		'doctor'              ,
		'myelogramm'          ,
		//'policytype'          ,
		//'printformnew'        ,
		'constant'            ,
		'requestform'         ,
		//'requestformlayout'   ,
		'defaultprintform'    ,
		'hospital'            ,
		'biomaterial'         ,
		'usergraphics'        ,
		'material'            ,
		//'usergroup'           ,
		'userfield'           ,
		'qcevent'             ,
		'userdirectory'       ,
		'unit'                ,
		//'userdirectoryvalue'  ,
		'accessright'         ,
		'test'                ,
		//'requestdistrict'     ,
		'patientgroup'        ,
		'hospitalcategory'    ,
		'qcproducer'          ,
		'customreport'        ,
		'qcmaterial'          ,
		//'service'             ,
		'materialunit'        ,
		'report'              ,
		//'city'                ,
		//'citytype'            ,
		//'street'              ,
		//'customcommand'       ,
		//'treeviewlayout'      ,
		'printformheader'     ,
		'supplier'            ,
		'testrule'            ,
		'paycategory'         ,
		'biomaterial',
		'defectstate'
	);

	public $sessionid = null;
	public $lis_static_dicts = array(
		'request_state' => array(//статус заявок
			'1' => 'Регистрация',
			'2' => 'Открыта',
			'3' => 'Закрыта',
		),
		'priority' => array(//приоритет
			'10' => 'Низкий',
			'20' => 'Высокий',
		),
		'sex' => array(//приоритет
			'0' => 'Пол не указан',
			'1' => 'Мужской',
			'2' => 'Женский',
			'3' => 'Не важно',
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_model', 'dbmodel');
		$this->load->helper('Xml');
		$this->load->library('textlog', array('file'=>'Lis.log'));
		$this->load->model('Options_model', 'Options_model');
		if ($this->isLogon()) {
			$this->sessionid = $_SESSION['phox']['sessionid'];
		}
		try {
			$dbres = $this->Options_model->getDataStorageValues(array('DataStorageGroup_SysNick'=>'lis'), array());
			$options = array();
			foreach($dbres as $value) {
				$options[$value['DataStorage_Name']] = $value['DataStorage_Value'];
			}
			$this->server = array(
				'address'     => $options['lis_address'    ],
				'server'      => $options['lis_server'     ],
				'port'        => $options['lis_port'       ],
				'path'        => $options['lis_path'       ],
				'version'     => $options['lis_version'    ],
				'buildnumber' => $options['lis_buildnumber'],
			);
		} catch (Exception $e) {
			//throw new Exception('Не удалось получить настройки для ЛИС',0,$e);
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		assert_options(ASSERT_BAIL, 1);
	}

	/**
	 * @param bool $assertation
	 * @param null $message
	 * @throws Exception
	 */
	function assert($assertation, $message = null){
		if (!$assertation) {
			if (is_null($message)){
				$message = 'assertation failed';
			}
			throw new Exception($message);
		}
	}

	/**
	 * @param string $m
	 * @return bool
	 */
	private function add_log_message($m){
		switch ($this->log_to) {
			case 'textlog':
				$this->textlog->add($m);
				break;
			case 'print_r':
				print_r($m);
				print_r('<br />');
				break;
			default:
				return false;
		}
		return true;
	}

	/**
	 * Функция создания XML-запроса для обращения к сервису ЛИС
	 *
	 * @param $method
	 * @param $request
	 * @param bool $empty
	 * @param bool $isInWindows1251
	 * @return string
	 */
	function setXmlRequest($method, $request, $empty = true, $isInWindows1251 = true) {
		$sessionid = '';
		if ($this->isLogon()) {
			$sessionid = $_SESSION['phox']['sessionid'];
		}
		if ($isInWindows1251) {
			array_walk_recursive($request,'ConvertFromWin1251ToUTF8');
		}
		$w = new XMLWriter();
		$w->openMemory();
		$w->setIndent(true);
		$w->setIndentString("\t");
		$w->startDocument("1.0", "Windows-1251");
		$w->startDTD('request', null, 'lims.dtd');
		$w->endDtd();
		$w->startElement('request');
		$w->writeAttribute('type', $method);
		$w->writeAttribute('sessionid', $sessionid);
		$w->writeAttribute('version', $this->server['version']);
		$w->writeAttribute('buildnumber', $this->server['buildnumber']);
		$w->startElement('content');

		if (is_array($request) && (count($request) > 0)) {
			foreach ($request as $key=>$val) {
				if (is_array($val) && (count($val) > 0)) {
					$w->startElement('o');
					$w->writeAttribute('n',$key);
					foreach ($val as $k=>$v) {
						if ($empty || (strlen($v)>0)) {
							$this->xml_add_f($w,$k,$v);
						}
					}
					$w->endElement();//o
				} else {
					if ($empty || (strlen($val)>0)) {
						$this->xml_add_f($w,$key,$val);
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
	 * @param array $m
	 * @return mixed
	 */
	function toRec($m) {
		foreach ($m as $k=>$v) {
			if (is_array($v)) {
				if (isset($v['i'])) {
					$m[$k] = $v['i'];

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
	function toArray($xml) {
		// функция
		$x = new SimpleXMLElement($xml);
		return $this->toRec(simpleXMLToArray($x));
	}

	/**
	 * Функция обработки полученного XML-ответка и преобразование его в JSON (при необходимости)
	 * @param $xml
	 * @param bool $tojson
	 * @return array|string
	 */
	function getXmlResponse($xml, $tojson = true) {
		$r = $this->toArray($xml);
		if (is_array($r)) {
			if (isset($r['error'])) {
				$err = array('success' => false, 'Error_Code' => $r['error']['code'], 'Error_Msg' => $r['error']['description']);
				if ($tojson) {
					return json_encode($err);
				} else {
					return $err;
				}
			}
		}
		$r['content']['o']['success'] = true;
		$r['content']['o']['buildnumber'] = $r['buildnumber'];
		$r['content']['o']['sessionid'] = $r['sessionid'];
		if ($tojson) {
			return  $r['content']['o']; // TODO: тут надо сделать преобразование в JSON-формат
		} else {
			return  $r['content']['o'];
		}
	}

	/**
	 * Функция отправки серверу xml-запроса и получения от него ответа
	 * @param $xml
	 * @param null $server
	 * @return mixed
	 */
	function request($xml, $server=null){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, (!empty($server))?$server:$this->server['address']);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		/*
		$AUTHPROXY = 'proxy1:3128';
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
		curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'user:passwor');
		curl_setopt($ch, CURLOPT_PROXY, $AUTHPROXY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		*/
		$result=curl_exec($ch);
		if (!$result) {
			// сообщаем пользователю страшную весть
			$this->add_log_message('CURL ERROR:'.curl_error($ch));
			DieWithError(curl_error($ch));
		}
		return $result;
	}

	/**
	 * Проверка, залогинен ли пользователь на удаленном сервисе (проверка на наличие открытой сессии)
	 *
	 * @return bool
	 */
	function isLogon() {
		$result = (isset($_SESSION['phox']) && (isset($_SESSION['phox']['sessionid'])));
		$this->add_log_message('isLogon: Проверка залогинен ли пользователь  в ЛИС: '.$result);
		return $result;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function getLisRequestData($data) {
		$request = array();
		$this->load->model('LisUser_model', 'usermodel');
		if (isset($_SESSION['lisrequestdata'])) {
			// если в сессии сохранены данные успешной авторизации то возвращаем их
			return $_SESSION['lisrequestdata'];
		} else {
			// иначе читаем из lis.User
			return $this->usermodel->getLisRequestData($data);
		}
	}

	/**
	 * Запрос login. Авторизация пользователя в системе и получение пользователем уникального идентификатора сессии
	 *
	 * @return bool
	 */
	function login() {
		$data = $this->ProcessInputData('login', null, true);
		$this->add_log_message('login: Запуск');
		// TODO: Данные для пользователя нужно брать из pmUserCache наверное... или какая-то отдельная таблица с машинами (компами) - тут надо у Тараса уточнить откуда брать.
		$request = $this->getLisRequestData($data);

		if (!$request) {
			$response = array('success' => false, 'Error_Msg' => 'Не заполнены параметры пользователя ЛИС! <br/>Перед работой с ЛИС заполните пожалуйста настройки пользователя.');
			$this->response(array('error_code' => 0, 'data' => $response));
		}

		$this->add_log_message('login: Формируем запрос в XML');
		$xml = $this->setXmlRequest('login', $request);
		$this->add_log_message('login: Получили ответ');
		$response = $this->request($xml);
		if (strlen($response)>0) {
			$this->add_log_message('login: Ответ не пустой');
			$arr = $this->getXmlResponse($response, false);
			$this->add_log_message('login: Распарсили ответ');
			// Обрабатываем ответ
			if ($arr['success'] === true) {
				$this->add_log_message('login: Ответ хороший, синхронизировали сессии');
				// При успешной авторизации сохраняем данные для авторизации (getLisRequestData) в сессии
				$_SESSION['lisrequestdata'] = $request;
				// При успешной авторизации сохраняя связь с сессией
				$_SESSION['phox'] = array();
				$_SESSION['phox']['sessionid'] = $arr['sessionid'];
				$this->sessionid = $_SESSION['phox']['sessionid'];
			} else {
				$this->add_log_message('login: Ответ плохой, сессию удалили');
				$this->add_log_message('login: Ошибка '.$arr['Error_Code'].''.toAnsi($arr['Error_Msg']));
				$_SESSION['phox'] = array();
			}
			// Вывод только, если установлен признак дебага
			if ($this->debug && (isset($arr))) {
				var_dump($arr);
			}
		} else {
			$this->add_log_message('login: Ответ пустой!');
			$this->add_log_message('login: '.$xml);
		}
		$this->add_log_message('login: Финиш');

		$response = array('success' => true);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Lis/DirectoryVersions",
	 *  	tags={"Lis"},
	 *	    summary="Получение информации о текущих версиях справочников",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DirectoryVersions_get() {
		$data = $this->ProcessInputData('getDirectoryVersions', null, true);
		$this->dbmodel->getDirectoryVersions($data);
		$this->add_log_message('getDirectoryVersions: Финиш ');
		$response = array('success' => true);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Запрос directory-versions. Предназначен для получения от сервера содержимого справочников.
	 *
	 * @param $data
	 * @return array|bool|string
	 */
	function getDirectory($data) {
		$this->add_log_message('getDirectory: Запуск');
		if ($this->isLogon()) {
			// Данные запроса
			// TODO: Данные для пользователя нужно брать из pmUserCache наверное... или какая-то отдельная таблица с машинами (компами) - тут надо у Тараса уточнить откуда брать.
			$request = array();
			$request['name'] = $data['name'];
			// Формируем запрос в XML
			$this->add_log_message('getDirectory: Хотим забрать справочник '.$data['name']);
			$xml = $this->setXmlRequest('directory', $request);
			$this->add_log_message('getDirectory: Формируем запрос в XML ');
			$response = $this->request($xml);
			// создаем файл с именем справочника и записываем в него все пришедшие данные
			$fd = "logs\\".$data['name'].".".$data['version'].".xml";
			$f = fopen($fd, 'w');
			fputs($f, ''.var_export($response, true));
			fclose($f);
			if (strlen($response)>0) {
				$arr = $this->getXmlResponse($response, false);
				$this->add_log_message('getDirectory: Пришел не пустой ответ');
				// Обрабатываем ответ
				if (is_array($arr)) {
					if ($arr['success'] === true) {
						$this->add_log_message('getDirectory: Ответ хороший');
						$rows = array();
						$s = array();
						// TODO: Лопеция! Надо сделать предварительную проверку
						$rows = ((isset($arr['s'])) && (isset($arr['s']['o'])))?$arr['s']['o']:null;
						if (isset($rows['f'])) {
							// одна строка
							$this->add_log_message('getDirectory: В ответе одна строка');
							$s[0] = array();
							$r = $rows['f'];
							if (count($r)>0) {
								for($j=0; $j<count($r); $j++) {
									// TODO: проверить правильность загрузки с этим условием
									if (isset($r[$j])) {
										$s[0][$r[$j]['n']] = $r[$j]['v'];
									}
								}
							}
						} else {
							// несколько строк
							if (count($rows)>0) {
								$this->add_log_message('getDirectory: В ответе '.count($rows).' строк');
								for($i=0; $i<count($rows); $i++) {
									$s[$i] = array();
									$r = $rows[$i]['f'];
									if (isset($r)) {
										if (count($r)>0) {
											for($j=0; $j<count($r); $j++) {
												// TODO: проверить правильность загрузки с этим условием
												if (isset($r[$j])) {
													$s[$i][$r[$j]['n']] = $r[$j]['v'];
												}
											}
										}
									}
								}
							}
						}
						/*
						if ($this->debug) {
							print_r($s);
						}
						*/
						$this->add_log_message('getDirectory: Успешный финиш');
						return $s;
					} else {
						$this->add_log_message('getDirectory: Ошибка при запросе справочника '.$data['name'].': '.$arr['Error_Code'].''.toAnsi($arr['Error_Msg']));
						return $arr;
					}
				} else {
					$this->add_log_message('getDirectory: Ошибка при возврате справочника');
					return false;
				}
			} else {
				$this->add_log_message('getDirectory: Пришел пустой ответ!');
				return false;
			}
		}
		return false;
	}

	/**
	 * Сохранение справочника
	 *
	 * @param $name
	 * @param $data
	 * @return bool
	 */
	function saveDirectory($name, $data) {
		$this->add_log_message('saveDirectory: Сохраняем справочник '.$name);
		if (is_array($data)) {
			if (isset($data['Error_Msg'])) {
				$this->add_log_message('saveDirectory: Ошибка при сохранении справочника "'.$name.'": '.$data['Error_Code'].' - '.$data['Error_Msg']);
				return false;
			}
			for($i=0; $i<count($data); $i++) {
				$data[$i]['pmUser_id'] = $_SESSION['pmuser_id'];
				try {
					$response = $this->dbmodel->saveDirectory($name, $this->map, $data[$i]);
					$this->ProcessModelSave($response, true);
				} catch (Exception $e) {
					$this->add_log_message('ошибка сохранения элемента справочника '.$name.' '.var_export($data[$i], true));
				}
			}
			$this->add_log_message('saveDirectory: Сохранили все записи');
			return true;
		}
		return false;
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Lis/listRegistrationJournal",
	 *  	tags={"Lis"},
	 *	    summary="Выгрузка содержимого заявок",
	 *     	@OA\Parameter(
	 *     		name="nr",
	 *     		in="query",
	 *     		description="Номер",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="dateFrom",
	 *     		in="query",
	 *     		description="Дата и время поступления заявки «С»",
	 *     		@OA\Schema(type="string", format="date-time")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="dateTill",
	 *     		in="query",
	 *     		description="Дата и время поступления заявки «По»",
	 *     		@OA\Schema(type="string", format="date-time")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="endDateFrom",
	 *     		in="query",
	 *     		description="Дата и время закрытия заявки «С»",
	 *     		@OA\Schema(type="string", format="date-time")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="endDateTill",
	 *     		in="query",
	 *     		description="Дата и время закрытия заявки «По»",
	 *     		@OA\Schema(type="string", format="date-time")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="lastModificationDateFrom",
	 *     		in="query",
	 *     		description="Дата последнего изменения заявки «С»",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="priority",
	 *     		in="query",
	 *     		description="Приоритет заявки",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="defectState",
	 *     		in="query",
	 *     		description="Наличие брака в заявке",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="payCategories",
	 *     		in="query",
	 *     		description="Множество ссылок на справочник Категория оплаты",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="lastName",
	 *     		in="query",
	 *     		description="Фамилия пациента",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="firstName",
	 *     		in="query",
	 *     		description="Имя пациента",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="middleName",
	 *     		in="query",
	 *     		description="Отчество пациента",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="birthDate",
	 *     		in="query",
	 *     		description="Дата рождения пациента",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="sex",
	 *     		in="query",
	 *     		description="Пол пациента",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="patientNr",
	 *     		in="query",
	 *     		description="Номер пациента",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="billNr",
	 *     		in="query",
	 *     		description="Номер выставленного счета",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="states",
	 *     		in="query",
	 *     		description="Статусы заявок",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="doctors",
	 *     		in="query",
	 *     		description="Врачи заказчики",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="hospitals",
	 *     		in="query",
	 *     		description="Заказчики",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="custDepartments",
	 *     		in="query",
	 *     		description="Отделение заказчика",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="departments",
	 *     		in="query",
	 *     		description="Подразделения, в которых выполняется заявка",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="targets",
	 *     		in="query",
	 *     		description="Исследования, входящие в заявку",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="lastTimestamp",
	 *     		in="query",
	 *     		description="Метка последнего изменения записи",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="customStates",
	 *     		in="query",
	 *     		description="Дополнительные статусы заявки",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="requestForms",
	 *     		in="query",
	 *     		description="Регистрационные формы, при помощи которых создается заявка",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function listRegistrationJournal_get() {
		$data = $this->ProcessInputData('listRegistrationJournal', null, true);
		$this->dbmodel->listRegistrationJournal($data);
		$this->response(array('error_code' => 0));
	}

	/**
	 * Отправляет одну запись в ЛИС и возврашает ответ по одной записи
	 * @param $data
	 * @throws Exception
	 */
	function createRequest2($data=null) {
		if($data==null)$data = $this->ProcessInputData('createRequest2', null, true);
		return $this->dbmodel->createRequest2($data);
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Lis/RequestSelections",
	 *  	tags={"Lis"},
	 *	    summary="Отправка набора проб в ЛИС",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSamples",
	 *     					description="Список идентификаторов проб",
	 *     					type="string"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function RequestSelections_post(){
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$data = $this->ProcessInputData('createRequestSelections', null, true);

		$arrayId = array();
		if($data) {
			if (!empty($data['EvnLabSamples'])) {
				$arrayId = json_decode($data['EvnLabSamples']);
			}
		} else {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'Для создания заявки необходимо выбрать хотя бы одну пробу'
			));
		}
		$answers = array();
		//print_r($arrayId);
		foreach($arrayId as $id) {
			$data['EvnLabSample_id'] = $id;
			$answers = $this->dbmodel->createRequest2($data);
		}
		//array_walk_recursive($ouput4client,'ConvertFromWin1251ToUTF8');

		$this->response(array(
			'error_code' => 0,
			'data' => $answers
		));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Lis/RequestSelectionsLabRequest",
	 *  	tags={"Lis"},
	 *	    summary="Отправка набора заявок в ЛИС",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequests",
	 *     					description="Список идентификаторов заявок",
	 *     					type="string"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function RequestSelectionsLabRequest_post(){
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$data = $this->ProcessInputData('createRequestSelectionsLabRequest', null, true);

		$arrayId = array();
		if($data) {
			$arrayId = $this->dbmodel->getLabSamplesForEvnLabRequests($data);
		} else {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'Для создания заявки необходимо выбрать хотя бы одну заявку с пробами'
			));
		}
		$answers = array();
		//print_r($arrayId);
		foreach($arrayId as $id) {
			$data['EvnLabSample_id'] = $id;
			$answers = $this->dbmodel->createRequest2($data);
		}
		//array_walk_recursive($ouput4client,'ConvertFromWin1251ToUTF8');
		$this->response(array(
			'error_code' => 0,
			'data' => $answers
		));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Lis/ResultSamples",
	 *  	tags={"Lis"},
	 *	    summary="Получение данных из ЛИС по нескольким пробам",
	 *     	@OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnLabSamples",
	 *     		in="query",
	 *     		description="Список идентификаторов проб",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ResultSamples_get(){
		$data = $this->ProcessInputData('sample', null, true);
		$arrayId = array();
		if($data) {
			if (!empty($data['EvnLabSamples'])) {
				$arrayId = json_decode($data['EvnLabSamples']);
			}
		} else {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'Для получения результатов нужно выбрать хотя бы одну пробу'
			));
		}
		$answers = array();
		$errors = '';
		foreach($arrayId as $id) {
			$data['EvnLabSample_id'] = $id;
			$result = $this->dbmodel->sample($data);
			if (!empty($result[0]['Error_Msg'])) {
				$errors .= $result[0]['Error_Code'].' '.$result[0]['Error_Msg'].'<br/>';
			}
		}
		if (strlen($errors)>0) { // Если есть ошибки то выведем их
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Ошибки при получении результатов:<br/>'.$errors
			));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Lis/syncSampleDefects",
	 *  	tags={"Lis"},
	 *	    summary="Синхронизация данных по отбраковке",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function syncSampleDefects_post(){
		$data = $this->ProcessInputData('syncSampleDefects', null, true);

		// получаем список проб нуждающихся в синхронизации с ЛИС (EvnLabSample_IsLIS = 1)
		$arrayId = $this->dbmodel->getEvnLabSampleNeedToSync($data);

		$this->dbmodel->syncDefectCauseTypeSpr($data);

		$answers = array();
		foreach($arrayId as $id) {
			$data['EvnLabSample_id'] = $id;
			$result = $this->dbmodel->syncSampleDefects($data);
			if (!empty($result[0]['Error_Msg'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при синхронизации с ЛИС:'.$result[0]['Error_Code'].' '.$result[0]['Error_Msg']
				));
			}
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Lis/RequestFromLabSamples",
	 *  	tags={"Lis"},
	 *	    summary="",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_idsJSON",
	 *     					description="Список идентификаторов заявок",
	 *     					type="string"
	 * 					),
	 *     				required={"EvnLabSample_idsJSON"}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function RequestFromLabSamples_post(){
		$data = $this->ProcessInputData('saveLisRequestFromLabSamples', null, true);
		$res = $this->dbmodel->saveLisRequestFromLabSamples($data);
		$this->response(array('error_code' => 0, 'data' => $res));
	}

	/*###################Xml-помощники#########################*/
	/**
	 * Создает XMLWriter, который можно наполнить данными запроса. Будет включать всю необходимю информацию кроме тела запроса
	 * Для корректной работы требуется перед вызовом метода убедится что произведен вход в ЛИС
	 * Чтобы после добавления в документ нужной иформации корректно закончить его и получить XML, надо вызвать endXmlRequestWriterDocument
	 *
	 * @param string $type
	 * @return XMLWriter
	 */
	function xml_startXmlRequestWriterDocument($type){
		/*
		//Запрос к серверу имеет следующий вид:
		<?xml version="1.0" encoding="Windows-1251"?>
		<!DOCTYPE request SYSTEM "lims.dtd">
		<request type="Тип запроса" sessionid=" ID сессии" version="3.8" buildnumber="Версия релиза клиентского ПО">
			<content>
				Тело запроса
			</content>
		</request>
		*/
		if (is_null($this->sessionid)) {
			throw new Exception('Вход в ЛИС не выполнен');
		}
		$w = new XMLWriter();
		$w->openMemory();
		$w->setIndent(true);
		$w->setIndentString("\t");
		$w->startDocument("1.0", "Windows-1251");
		$w->startDTD('request', null, 'lims.dtd');
		$w->endDtd();
		$w->startElement('request');
		$w->writeAttribute('type', $type);
		$w->writeAttribute('sessionid', $this->sessionid);
		$w->writeAttribute('version', $this->server['version']);
		$w->writeAttribute('buildnumber', $this->server['buildnumber']);
		$w->startElement('content');
		return $w;
	}

	/**
	 * Закрывает XML-документ, ранее открытый методом startXmlRequestWriterDocument
	 * Возвращает сформированный документ в виде строки.
	 * @param XMLWriter $writer
	 * @return string
	 */
	function xml_endXmlRequestWriterDocument($writer){
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
	 * @throws Exception
	 * @return bool
	 */
	function xml_add_f($w, $f_name, $f_value, $include_type = true){
		$result = true;
		$result = $result && $w->startElement('f');
		$var_type = gettype($f_value);
		$t = '';
		$v = '';
		switch ($var_type) {
			case 'double':
			case 'integer':
				$t = 'i';
				$v = (string)$f_value;
				break;
			case 'object':
				if ($f_value instanceof Datetime) {
					/** @var Datetime $f_value */
					$t = 'd';
					$v = $f_value->format('d.m.Y H:i:s');
				} else {
					throw new Exception("Запись объекта класса в XML".get_class($f_value)." не поддерживается ($f_name = $f_value)");
				}
				break;
			case 'string':
				$t = 's';
				$v = $f_value;
				break;
			case 'boolean':
				$t = 'b';
				$v = $f_value?'true':'false';
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
		$result = $result && $w->endElement();//f
		return $result;
	}

	/**
	 * @param XMLWriter $w
	 * @param string $userField_i
	 * @param mixed $value
	 */
	function xml_add_userValues_entry($w, $userField_i, $value) {
		$w->startElement('o');
		$w->startElement('r');
		$w->writeAttribute('n','userField');
		$w->writeAttribute('i', $userField_i);
		$w->endElement();//r
		$this->xml_add_f($w, 'value', $value);
		$w->endElement();
	}

	/**
	 * @param XMLWriter $w
	 * @param Array $userValues
	 * @throws Exception
	 */
	private function xml_add_userValues_block($w, $userValues)
	{
		$w->startElement('s');
		$w->writeAttribute('n', 'userValues');
		foreach ($userValues as $key => $value) {
			$this->xml_add_userValues_entry($w, $key, $value);
		}
		$w->endElement();//s
	}

	/**
	 * @param XMLWriter $w
	 * @param string $name
	 * @param array $values
	 * @param string $tag
	 * @throws Exception
	 */
	function xml_add_s($w, $name, $values, $tag = 'f') {
		$w->startElement('s');
		$w->writeAttribute('n', $name);
		if (is_array($values)) {
			foreach($values as $key => $value) {
				if (is_array($value)) {
					throw new Exception("xml_add_s: wrong type of '$name'['$key'] value - array");
				} else {
					switch ($tag) {
						case 'f':
							$this->xml_add_f($w, null, $value, true);
							break;
						case 'r':
							$w->startElement('r');
							$w->writeAttribute('i',$value);
							$w->endElement();//r
							break;
						default:
							throw new Exception('xml_add_s: tag "'.$tag.'" not supported');
							break;
					}
				}
			}
		} else {
			throw new Exception("xml_add_s: Элемент $name не является массивом");
		}
		$w->endElement();//s
	}

	/**
	 * @param XMLWriter $w
	 * @param string $name
	 * @param string $operator
	 * @param array $idList
	 * @param string $tag
	 */
	function xml_add_filter_o($w, $name, $operator, $idList, $tag = 'r'){
		$w->startElement('o');
		$w->writeAttribute('n', $name);
		if (!is_null($operator)) {
			$this->xml_add_f($w, 'operator', $operator, false);
			$this->xml_add_s($w, 'idList', $idList, $tag);
		}
		$w->endElement();//o
	}

	/**
	 * @param string $type
	 * @param array $filter
	 * @param $andOrListR
	 * @param $andOrListF
	 * @param array $collectionsR
	 * @param $collectionsF
	 * @param array $fieldsR
	 * @throws Exception
	 * @return string
	 */
	function xml_composeFilter($type, $filter, $andOrListR, $andOrListF, $collectionsR, $collectionsF, $fieldsR){
		$w = $this->xml_startXmlRequestWriterDocument($type);
		$w->startElement('o');
		$w->writeAttribute('n', 'filter');
		foreach($filter as $filter_name => $filter_value){
			if (in_array($filter_name, $andOrListR)) {
				if (isset($filter_value['operator']) && isset($filter_value['idList'])) {
					$this->xml_add_filter_o($w, $filter_name, $filter_value['operator'], $filter_value['idList']);
				} else {
					if (count($filter_value) === 0) {
						$this->xml_add_filter_o($w, $filter_name, null, array());
					} else {
						throw new Exception("Поскольку '$filter_name' ожидается как объект типа 'AndOrList', то элементы 'operator' и 'idList' в нем являются обязательными");
					}
				}
			} else {
				if (in_array($filter_name, $andOrListF)) {
					if (isset($filter_value['operator']) && isset($filter_value['idList'])) {
						$this->xml_add_filter_o($w, $filter_name, $filter_value['operator'], $filter_value['idList'], 'f');
					} else {
						if (count($filter_value) === 0) {
							$this->xml_add_filter_o($w, $filter_name, null, array(), 'f');
						} else {
							throw new Exception("Поскольку '$filter_name' ожидается как объект типа 'AndOrList', то элементы 'operator' и 'idList' в нем являются обязательными");
						}
					}
				} else {
					if (in_array($filter_name, $collectionsR)) {
						$this->xml_add_s($w, $filter_name, $filter_value, 'r');
					} else {
						if (in_array($filter_name, $fieldsR)) {
							$w->startElement('r');
							$w->writeAttribute('n', $filter_name);
							$w->writeAttribute('i', $filter_value);
							$w->endElement();//r
						} else {
							if (is_array($filter_value)) {
								$this->xml_add_s($w, $filter_name, $filter_value);
							} else {
								$this->xml_add_f($w, $filter_name, $filter_value, false);
							}
						}
					}

				}
			}
		}
		$w->endElement(); //o
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
	function xml_read_moveTo($r, $element, $n = null, $v = null){
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
		$t = 's';
		$v = null;
		$n = null;
		if ($r->moveToAttribute('n')) {
			$n = $r->value;
		} else {
			throw new Exception('У элемента f не задан атрибут n');
		}
		if ($r->moveToAttribute('t')) {
			$t = strtolower($r->value);
		}
		if ($r->moveToAttribute('v')) {
			switch($t){
				case 'b':
					if (strtolower($r->value) === 'true') {
						$v = true;
					} else {
						$v = false;
					}
					break;
				case 'i':
					$v = (int) $r->value;
					break;
				case 's':
					$v = (string) $r->value;
					break;
				case 'r':
					$v = (int) $r->value;
					break;
				case 'f':
					$v = (float) $r->value;
					break;
				case 'l':
					$v = (int) $r->value;
					break;
				case 'd':
					$v = Datetime::createFromFormat('d.m.Y H:i:s', $r->value);
					if ($v === false) {
						throw new Exception('Не удалось распознать дату: - '.$r->value);
					}
					break;
				default:
					throw new Exception('Неизвестное обозначение типа переменной - '.$t);
			}
		}
		if (!is_null($n)){
			$result[$n] = $v;
		}
	}

	/**
	 * @param XMLReader $r
	 * @throws Exception
	 * @return array
	 */
	private function xml_read_collect_userValues($r){
		//проверяем правильность установки позиции
		$ok =	(($r::ELEMENT === $r->nodeType)//курсор должен быть установлен на элемент
			&& ('s' === $r->name)// это должен быть элемент s
			&& $r->moveToAttribute('n') // у него должен быть атрибут n
			&& ('userValues' === $r->value));//значение атрибута должно быть = userValues
		if (!$ok) {
			throw new Exception('Парсер блока пользовательских данных: Курсор xml-считываля находится в неправильной позиции');
		} else {
			$r->moveToElement();
		}
		$result = array();
		if (!$r->isEmptyElement) {
			while ($r->read()){
				switch ($r->nodeType) {
					case $r::ELEMENT:
						if ('o' === $r->name) {
							if ($r->moveToAttribute('n')){
								if ('userValues' === $r->value) {
									$tmp = array();
									$key = null;
									while ($r->read()){
										if (($r->nodeType === XMLReader::ELEMENT) && ($r->name === 'f')) {
											$this->xml_read_collect_f($r, $tmp);
										}
										if (($r->nodeType === XMLReader::ELEMENT) && ($r->name === 'r')) {
											if ($r->moveToAttribute('n')) {
												switch ($r->value) {
													case 'userField':
														if ($r->moveToAttribute('i')){
															$key = $r->value;
														} else {
															throw new Exception('не найдено i у элемента r');
														}
														break;
													default:
														$n = $r->value;
														if ($r->moveToAttribute('i')) {
															$tmp[$n] = (int)$r->value;
														} else {
															$tmp[$n] = null;
														}
														break;
												}
											} else {
												throw new Exception('не найдено n у элемента r');
											}
										}
										if (($r->nodeType === XMLReader::END_ELEMENT) && ($r->name === 'o')) {
											//элемент o, содержаций userValues закончился
											break;
										}
									}
									if (!is_null($key)) {
										$result[$key] = $tmp;
									} else {
										throw new Exception('не указан userField у userValues');
									}
								}
							} else {
								throw new Exception('не найден атрибут "n" у элемента "o" в множестве userValues');
							}
						}
						break;
					case $r::END_ELEMENT:
						if ('s' === $r->name) {
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
	 * @throws Exception
	 * @return array
	 */
	function xml_read_collect_r($r, $s_name = 's'){
		$result = array();
		if (!$r->isEmptyElement) {
			while ($r->read()) {
				switch ($r->nodeType) {
					case $r::ELEMENT:
						if ('r' === $r->name) {
							if ($r->moveToAttribute('i')) {
								$result[] = (int)$r->value;
							} else {
								throw new Exception("Не найден атрибут i элемента r в $s_name");
							}
						} else {
							throw new Exception('Неожиданный элемент '.$r->name.' в '.$s_name);
						}
						break;
					case $r::END_ELEMENT:
						if ('s' === $r->name) {
							break 2;
						}
				}
			}
		}
		return $result;
	}

	/**
	 * @param XMLReader $r
	 * @throws Exception
	 * @return array
	 */
	public function xml_read_collect_works($r)	{
		$result = array();
		while ($r->read()) {
			switch ($r->nodeType) {
				case $r::ELEMENT:
					if ('o' === $r->name) {
						if ($r->moveToAttribute('n')) {
							if ('works' === $r->value) {
								$tmp = array();
								while ($r->read()) {
									switch ($r->nodeType) {
										case $r::ELEMENT:
											switch ($r->name) {
												case 'f':
													$this->xml_read_collect_f($r, $tmp);
													break;
												case 'r':
													if ($r->moveToAttribute('n')) {
														$key = $r->value;
														if ($r->moveToAttribute('i')) {
															$tmp[$key] = (int)$r->value;
														} else  {
															throw new Exception('Не найден атрибут i у элемента r');
														}
													} else {
														throw new Exception('Не найден атрибут n у элемента r');
													}
													break;
												case 's':
													if ($r->moveToAttribute('n')) {
														$s_name = $r->value;
														$r->moveToElement();
														$tmp[$s_name] = $this->xml_read_collect_r($r, $s_name);
													} else {
														throw new Exception('Не найден атрибут n у элемента s');
													}
													break;
												default:
													throw new Exception('Неожиданный элемент ' . $r->name . ' в <o n="works">');
													break;
											}
											break;
										case $r::END_ELEMENT:
											if ('o' === $r->name) {
												break 2;
											} else {
												throw new Exception('Неожиданный конец элемента ' . $r->name . 'в <o n="works">');
											}
									}
								}
								$result[] = $tmp;
							}
						} else {
							throw new Exception("Не найден атрибут n в элементе o");
						}
					} else {
						throw new Exception('Неожиданный элемент ' . $r->name . 'в <s n="works">');
					}
					break;
				case $r::END_ELEMENT:
					if ('s' === $r->name) {
						break 2;
					}
			}
		}
		return $result;
	}
	/*###################Запросы#########################*/
	/**
	 * Формирование запроса для получения текущих версий используемых справочников (directory-versions)
	 *
	 * @return string
	 */
	function lis_directory_versions(){
		$w = $this->xml_startXmlRequestWriterDocument('directory-versions');
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Формирует XML для запроса для получения содержимого справочника
	 *
	 * @param string $directory_name
	 * @return string
	 */
	function lis_directory($directory_name){
		$w = $this->xml_startXmlRequestWriterDocument('directory');
		$this->xml_add_f($w, 'name', $directory_name);
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Формирует XML для запроса сохранения элемента справочника
	 *
	 * @param string $directory_name
	 * @param array $element
	 * @return string
	 */
	function lis_directory_save($directory_name, $element){
		$w = $this->xml_startXmlRequestWriterDocument('directory-save');
		$this->xml_add_f($w, 'directory', $directory_name, false);
		$w->startElement('o');
		if (isset($element['id'])) {
			$w->writeAttribute('id', $element['id']);
		}
		$w->writeAttribute('n', 'element');
		foreach ($element['fields'] as $f_name => $f_value) {
			$this->xml_add_f($w, $f_name, $f_value, false);
		}
		$w->endElement();//o
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Формирует XML для запроса выгрузки содержимого заявок
	 *
	 * @param $filter
	 * @param bool $isInWindows1251
	 * @return string
	 */
	function lis_registration_journal($filter,  $isInWindows1251 = true) {
		if ($isInWindows1251) {
			array_walk_recursive($filter,'ConvertFromWin1251ToUTF8');
		}

		/*if (!isset($filter["onlyDelayed"])) {
			throw new Exception ('Элемент onlyDelayed обязателен');
		};
		if (!isset($filter["markPlanDeviation"])) {
			throw new Exception ('Элемент markPlanDeviation обязателен');
		};
		if (!isset($filter["emptyPayCategory"])) {
			throw new Exception ('Элемент emptyPayCategory обязателен');
		};*/
		//параметры onlyDelayed, markPlanDeviation и emptyPayCategory не используются и всегда = false, а без них запрос падает
		$filter['onlyDelayed'] = false;
		$filter['markPlanDeviation'] = false;
		$filter['emptyPayCategory'] = false;
		$collectionsR = array ('payCategories','doctors','hospitals','custDepartments','customStates','requestForms','internalNrs','patientCodes');
		$andOrListR = array('targets', 'departments');
		$andOrListF = array();
		$collectionsF = array('states');
		return $this->xml_composeFilter('registration-journal', $filter, $andOrListR, $andOrListF, $collectionsR, $collectionsF, array());
	}

	/**
	 * Формирует XML-запрос для создания заявок (create-requests-2) из массива установленного образца. При ошибке в структуре массива вываливает исключение.
	 * Переменные в массиве должны быть строго типизированы, даты должны быть объектами класса Datetime.
	 * По умолчанию предполагается, что строки во входном массиве $request_data закодированы в Windows-1251.
	 * Если данные закодированы в utf-8, необходимо передать $isInWindows1251 = false.
	 *
	 * @param $request_data
	 * @param bool $isInWindows1251
	 * @throws Exception
	 * @return string
	 */
	function lis_create_requests_2($request_data, $isInWindows1251 = true){
		if (is_array($request_data)) {
			if ($isInWindows1251) {
				array_walk_recursive($request_data,'ConvertFromWin1251ToUTF8');
			}
			$w = $this->xml_startXmlRequestWriterDocument('create-requests-2');
			if (isset($request_data['o'])) {
				$w->startElement('o');
				if (is_array($request_data['o'])) {
					if (isset($request_data['o']['id'])) {
						$this->xml_add_f($w,'id',$request_data['o']['id']);
					}
					$this->xml_add_f($w,'registrationFormCode',$request_data['o']['registrationFormCode']);
					$this->xml_add_f($w,'hospitalCode',$request_data['o']['hospitalCode']);
					if (isset($request_data['o']['hospitalName'])) {
						$this->xml_add_f($w,'hospitalName',$request_data['o']['hospitalName']);
					}
					$this->xml_add_f($w,'internalNr',$request_data['o']['internalNr']);
					if (isset($request_data['o']['custDepartmentCode'])) {
						$this->xml_add_f($w,'custDepartmentCode',$request_data['o']['custDepartmentCode']);
						if (isset($request_data['o']['custDepartmentName'])) {
							$this->xml_add_f($w,'custDepartmentName',$request_data['o']['custDepartmentName']);
						}
					}
					if (isset($request_data['o']['doctorCode'])) {
						$this->xml_add_f($w,'doctorCode',$request_data['o']['doctorCode']);
						if (isset($request_data['o']['doctorName'])) {
							$this->xml_add_f($w,'doctorName',$request_data['o']['doctorName']);
						}
					}
					if (isset($request_data['o']['samplingDate'])) {
						$this->xml_add_f($w,'samplingDate',$request_data['o']['samplingDate']);
					}
					if (isset($request_data['o']['sampleDeliveryDate'])) {
						$this->xml_add_f($w,'sampleDeliveryDate',$request_data['o']['sampleDeliveryDate']);
					}
					if (isset($request_data['o']['priority'])) {
						$this->xml_add_f($w,'priority',$request_data['o']['priority']);
					}
					if (isset($request_data['o']['readonly'])) {
						$this->xml_add_f($w,'readonly',$request_data['o']['readonly']);
					}
					if (isset($request_data['o']['patient'])){
						if (is_array($request_data['o']['patient'])) {
							$w->startElement('o');
							$w->writeAttribute('n', 'patient');
							$this->xml_add_f($w,'code',$request_data['o']['patient']['code']);
							$this->xml_add_f($w,'firstName',$request_data['o']['patient']['firstName']);
							$this->xml_add_f($w,'lastName',$request_data['o']['patient']['lastName']);
							$this->xml_add_f($w,'middleName',$request_data['o']['patient']['middleName']);
							//todo в документации не написано, поля даты рождения - обязательные или опциональные - надо выяснить эмпирически
							if (isset($request_data['o']['patient']['birthDay'])) {
								$this->xml_add_f($w,'birthDay',$request_data['o']['patient']['birthDay']);
							}
							if (isset($request_data['o']['patient']['birthMonth'])) {
								$this->xml_add_f($w,'birthMonth',$request_data['o']['patient']['birthMonth']);
							}
							if (isset($request_data['o']['patient']['birthYear'])) {
								$this->xml_add_f($w,'birthYear',$request_data['o']['patient']['birthYear']);
							}
							if (isset($request_data['o']['patient']['sex'])) {
								$this->xml_add_f($w,'sex',$request_data['o']['patient']['sex']);
							}
							if (isset($request_data['o']['patient']['country'])) {
								$this->xml_add_f($w,'country',$request_data['o']['patient']['country']);
							}
							if (isset($request_data['o']['patient']['city'])){
								$this->xml_add_f($w,'city',$request_data['o']['patient']['city']);
							}
							if (isset($request_data['o']['patient']['street'])) {
								$this->xml_add_f($w,'street',$request_data['o']['patient']['street']);
							}
							if (isset($request_data['o']['patient']['building'])){
								$this->xml_add_f($w,'building',$request_data['o']['patient']['building']);
							}
							if (isset($request_data['o']['patient']['flat'])){
								$this->xml_add_f($w,'flat',$request_data['o']['patient']['flat']);
							}
							if (isset($request_data['o']['patient']['insuranceCompany'])){
								$this->xml_add_f($w,'insuranceCompany',$request_data['o']['patient']['insuranceCompany']);
							}
							if (isset($request_data['o']['patient']['policySeries'])){
								$this->xml_add_f($w,'policySeries',$request_data['o']['patient']['policySeries']);
							}
							if (isset($request_data['o']['patient']['policyNumber'])){
								$this->xml_add_f($w,'policyNumber',$request_data['o']['patient']['policyNumber']);
							}
							if (isset($request_data['o']['patient']['patientCard'])){
								if (is_array($request_data['o']['patient']['patientCard'])) {
									$w->startElement('o');
									$w->writeAttribute('n', 'patientCard');
									if (isset($request_data['o']['patient']['patientCard']['cardNr'])){
										$this->xml_add_f($w,'cardNr',$request_data['o']['patient']['patientCard']['cardNr']);
									}
									if (isset($request_data['o']['patient']['patientCard']['userValues'])){
										if (is_array($request_data['o']['patient']['patientCard']['userValues'])){
											$this->xml_add_userValues_block($w, $request_data['o']['patient']['patientCard']['userValues']);
										} else {
											throw new Exception('Раздел o.patient.patientCard.userValues не является массивом');
										}
									}
									$w->endElement();//o-patientCard
								} else {
									throw new Exception('patientCard не является массивом');
								}
							}
							if (isset($request_data['o']['patient']['userValues'])){
								if (is_array($request_data['o']['patient']['userValues'])){
									$this->xml_add_userValues_block($w, $request_data['o']['patient']['userValues']);
								} else {
									throw new Exception('Раздел o.patient.userValues не является массивом');
								}
							}
							$w->endElement();//o-patient
						} else {
							throw new Exception('Раздел patient не является массивом');
						}
					} else {
						throw new Exception('Не указаны обязательные данные: отсутствуют данные о пациенте (элемент "patient")');
					}
					//todo в документации не обозначено, является ли элемент samples обязательным. Опытным путем установлено, что без него не падает, значит необязательно
					if (isset($request_data['o']['samples'])) {
						if (is_array($request_data['o']['samples'])){
							$w->startElement('s');
							$w->writeAttribute('n','samples');
							foreach ($request_data['o']['samples'] as $key => $sample) {
								if (is_array($sample)) {
									$w->startElement('o');
									if (isset($sample['internalNr'])){
										$this->xml_add_f($w,'internalNr',$sample['internalNr']);
									}
									if (isset($sample['biomaterial'])){
										$w->startElement('r');
										$w->writeAttribute('n', 'biomaterial');
										$w->writeAttribute('i', $sample['biomaterial']);
										$w->endElement();//r
									} /*else {
										throw new Exception('Элемент "samples['.$key.']" не содержит обязательный параметр: ссылку на биометриал (biomaterial)');
									}*/
									if (isset($sample['targets'])) {
										if (is_array($sample['targets'])) {
											$w->startElement('s');
											$w->writeAttribute('n', 'targets');
											foreach ($sample['targets'] as $target_idx => $target) {
												if (is_array($target)) {
													$w->startElement('o');
													$w->startElement('r');
													$w->writeAttribute('n', 'target');
													$w->writeAttribute('i', $target_idx);
													$w->endElement();//r
													if (isset($target['cancel'])){
														$this->xml_add_f($w,'cancel',$target['cancel']);
													}
													if (isset($target['readonly'])){
														$this->xml_add_f($w,'readonly',$target['readonly']);
													}
													if (isset($target['tests'])){
														if (is_array($target['tests'])) {
															$w->startElement('s');
															$w->writeAttribute('n','tests');
															foreach ($target['tests'] as $test_idx => $test) {
																$w->startElement('r');
																$w->writeAttribute('i',$test);
																$w->endElement();//r
															}
															$w->endElement();//s
														} else {
															throw new Exception('Элемент "samples['.$key.'][targets]['.$target_idx.'][tests]" не является массивом');
														}
													}
													$w->endElement();//o
												} else {
													throw new Exception('Элемент "samples['.$key.'][targets]['.$target_idx.']" не является массивом');
												}
											}
											$w->endElement();//s-targets
										} else {
											throw new Exception('Элемент "samples['.$key.'][targets]" не является массивом');
										}
									} else {
										throw new Exception('Элемент "samples['.$key.']" не содержит обязательный параметр: Исследования, которые необходимо выполнить для данной пробы (targets)');
									}
									$w->endElement();//o
								} else {
									throw new Exception('Элемент "samples['.$key.']" не является массивом');
								}
							}
							$w->endElement();//s
						} else {
							throw new Exception('Элемент "samples" не является массивом');
						}
					} else {
						//throw new Exception('Не указаны обязательные данные: отсутствуют данные о пробах (элемент "samples")');
					}
					if (isset($request_data['o']['userValues'])){
						if (is_array($request_data['o']['userValues'])){
							$this->xml_add_userValues_block($w, $request_data['o']['userValues']);
						} else {
							throw new Exception('Раздел o.userValues не является массивом');
						}
					}

				} else {
					throw new Exception('Элемент "o" не является массивом');
				}
				$w->endElement();//o
			} else {
				throw new Exception('Не указаны обязательные данные: отсутствует элемент "o"');
			}
			$w->endDocument();
			$result = $this->xml_endXmlRequestWriterDocument($w);
		} else {
			throw new Exception('Неправильный параметр: ожидается массив');
		}
		return $result;
	}

	/**
	 * Тест запроса create-requests
	 * @param $request_data
	 * @param bool $isInWindows1251
	 * @throws Exception
	 * @return string
	 */
	function lis_create_requests($request_data, $isInWindows1251 = true){
		if (is_array($request_data)) {
			if ($isInWindows1251) {
				array_walk_recursive($request_data,'ConvertFromWin1251ToUTF8');
			}
			$w = $this->xml_startXmlRequestWriterDocument('create-requests');
			if (isset($request_data['o'])) {
				$w->startElement('o');
				$this->xml_add_f($w,'createWorklists',$request_data['createWorklists'], false);
				if (is_array($request_data['o'])) {
					$w->startElement('o');
					$r_elems = array('requestForm', 'patient');
					foreach($request_data['o'] as $n => $el) {
						if (is_array($el)) {
							if ($n === 'samples') {
								$w->startElement('s');
								$w->writeAttribute('n', $n);
								$w->startElement('o');
								foreach ($el as $sample) {
									if (isset($sample['defectTypes'])) {
										$this->xml_add_s($w, 'defectTypes', $sample['defectTypes']);
									}
								}
								$w->endElement();//o
								$w->endElement();//s
							} else {
								$this->xml_add_s($w, $n, $el, 'r');
							}
						} else {
							if (!is_null($el)) {
								if (in_array($n, $r_elems)) {
									$w->startElement('r');
									$w->writeAttribute('n', $n);
									$w->writeAttribute('i', $el);
									$w->endElement();//r
								} else {
									$this->xml_add_f($w, $n, $el, false);
								}
							}
						}
					}
					$w->endElement();//o
					/*
					if (isset($request_data['o']['patient'])){

						if (is_array($request_data['o']['patient'])) {
							$w->startElement('o');
							$w->writeAttribute('n', 'patient');
							$this->xml_add_f($w,'code',$request_data['o']['patient']['code']);
							$this->xml_add_f($w,'firstName',$request_data['o']['patient']['firstName']);
							$this->xml_add_f($w,'lastName',$request_data['o']['patient']['lastName']);
							$this->xml_add_f($w,'middleName',$request_data['o']['patient']['middleName']);
							//todo в документации не написано, поля даты рождения - обязательные или опциональные - надо выяснить эмпирически
							if (isset($request_data['o']['patient']['birthDay'])) {
								$this->xml_add_f($w,'birthDay',$request_data['o']['patient']['birthDay']);
							}
							if (isset($request_data['o']['patient']['birthMonth'])) {
								$this->xml_add_f($w,'birthMonth',$request_data['o']['patient']['birthMonth']);
							}
							if (isset($request_data['o']['patient']['birthYear'])) {
								$this->xml_add_f($w,'birthYear',$request_data['o']['patient']['birthYear']);
							}
							if (isset($request_data['o']['patient']['sex'])) {
								$this->xml_add_f($w,'sex',$request_data['o']['patient']['sex']);
							}
							if (isset($request_data['o']['patient']['country'])) {
								$this->xml_add_f($w,'country',$request_data['o']['patient']['country']);
							}
							if (isset($request_data['o']['patient']['city'])){
								$this->xml_add_f($w,'city',$request_data['o']['patient']['city']);
							}
							if (isset($request_data['o']['patient']['street'])) {
								$this->xml_add_f($w,'street',$request_data['o']['patient']['street']);
							}
							if (isset($request_data['o']['patient']['building'])){
								$this->xml_add_f($w,'building',$request_data['o']['patient']['building']);
							}
							if (isset($request_data['o']['patient']['flat'])){
								$this->xml_add_f($w,'flat',$request_data['o']['patient']['flat']);
							}
							if (isset($request_data['o']['patient']['insuranceCompany'])){
								$this->xml_add_f($w,'insuranceCompany',$request_data['o']['patient']['insuranceCompany']);
							}
							if (isset($request_data['o']['patient']['policySeries'])){
								$this->xml_add_f($w,'policySeries',$request_data['o']['patient']['policySeries']);
							}
							if (isset($request_data['o']['patient']['policyNumber'])){
								$this->xml_add_f($w,'policyNumber',$request_data['o']['patient']['policyNumber']);
							}
							if (isset($request_data['o']['patient']['patientCard'])){
								if (is_array($request_data['o']['patient']['patientCard'])) {
									$w->startElement('o');
									$w->writeAttribute('n', 'patientCard');
									if (isset($request_data['o']['patient']['patientCard']['cardNr'])){
										$this->xml_add_f($w,'cardNr',$request_data['o']['patient']['patientCard']['cardNr']);
									}
									if (isset($request_data['o']['patient']['patientCard']['userValues'])){
										if (is_array($request_data['o']['patient']['patientCard']['userValues'])){
											$this->xml_add_userValues_block($w, $request_data['o']['patient']['patientCard']['userValues']);
										} else {
											throw new Exception('Раздел o.patient.patientCard.userValues не является массивом');
										}
									}
									$w->endElement();//o-patientCard
								} else {
									throw new Exception('patientCard не является массивом');
								}
							}
							if (isset($request_data['o']['patient']['userValues'])){
								if (is_array($request_data['o']['patient']['userValues'])){
									$this->xml_add_userValues_block($w, $request_data['o']['patient']['userValues']);
								} else {
									throw new Exception('Раздел o.patient.userValues не является массивом');
								}
							}
							$w->endElement();//o-patient
						} else {
							throw new Exception('Раздел patient не является массивом');
						}
					} else {
						throw new Exception('Не указаны обязательные данные: отсутствуют данные о пациенте (элемент "patient")');
					}
					//todo в документации не обозначено, является ли элемент samples обязательным. Опытным путем установлено, что без него не падает, значит необязательно
					if (isset($request_data['o']['samples'])) {
						if (is_array($request_data['o']['samples'])){
							$w->startElement('s');
							$w->writeAttribute('n','samples');
							foreach ($request_data['o']['samples'] as $key => $sample) {
								if (is_array($sample)) {
									$w->startElement('o');
									if (isset($sample['internalNr'])){
										$this->xml_add_f($w,'internalNr',$sample['internalNr']);
									}
									if (isset($sample['biomaterial'])){
										$w->startElement('r');
										$w->writeAttribute('n', 'biomaterial');
										$w->writeAttribute('i', $sample['biomaterial']);
										$w->endElement();//r
									} else {
										throw new Exception('Элемент "samples['.$key.']" не содержит обязательный параметр: ссылку на биометриал (biomaterial)');
									}
									if (isset($sample['targets'])) {
										if (is_array($sample['targets'])) {
											$w->startElement('s');
											$w->writeAttribute('n', 'targets');
											foreach ($sample['targets'] as $target_idx => $target) {
												if (is_array($target)) {
													$w->startElement('o');
													$w->startElement('r');
													$w->writeAttribute('n', 'target');
													$w->writeAttribute('i', $target_idx);
													$w->endElement();//r
													if (isset($target['cancel'])){
														$this->xml_add_f($w,'cancel',$target['cancel']);
													}
													if (isset($target['readonly'])){
														$this->xml_add_f($w,'readonly',$target['readonly']);
													}
													if (isset($target['tests'])){
														if (is_array($target['tests'])) {
															$w->startElement('s');
															$w->writeAttribute('n','tests');
															foreach ($target['tests'] as $test_idx => $test) {
																$w->startElement('r');
																$w->writeAttribute('i',$test);
																$w->endElement();//r
															}
															$w->endElement();//s
														} else {
															throw new Exception('Элемент "samples['.$key.'][targets]['.$target_idx.'][tests]" не является массивом');
														}
													}
													$w->endElement();//o
												} else {
													throw new Exception('Элемент "samples['.$key.'][targets]['.$target_idx.']" не является массивом');
												}
											}
											$w->endElement();//s-targets
										} else {
											throw new Exception('Элемент "samples['.$key.'][targets]" не является массивом');
										}
									} else {
										throw new Exception('Элемент "samples['.$key.']" не содержит обязательный параметр: Исследования, которые необходимо выполнить для данной пробы (targets)');
									}
									$w->endElement();//o
								} else {
									throw new Exception('Элемент "samples['.$key.']" не является массивом');
								}
							}
							$w->endElement();//s
						} else {
							throw new Exception('Элемент "samples" не является массивом');
						}
					} else {
						//throw new Exception('Не указаны обязательные данные: отсутствуют данные о пробах (элемент "samples")');
					}
					if (isset($request_data['o']['userValues'])){
						if (is_array($request_data['o']['userValues'])){
							$this->xml_add_userValues_block($w, $request_data['o']['userValues']);
						} else {
							throw new Exception('Раздел o.userValues не является массивом');
						}
					}
					*/
				} else {
					throw new Exception('Элемент "o" не является массивом');
				}
				$w->startElement('s');
				$w->endElement();
				$w->endElement();//o
			} else {
				throw new Exception('Не указаны обязательные данные: отсутствует элемент "o"');
			}
			$w->endDocument();
			$result = $this->xml_endXmlRequestWriterDocument($w);
		} else {
			throw new Exception('Неправильный параметр: ожидается массив');
		}
		return $result;
	}

	/**
	 * @param array $filter
	 * @param bool $isInWindows1251
	 * @return string
	 */
	function lis_work_journal($filter, $isInWindows1251 = true){
		$andOrListR = array('equipments', 'worklists', 'testFilter', 'targets');
		if ($isInWindows1251) {
			array_walk_recursive($filter,'ConvertFromWin1251ToUTF8');
		}
		$andOrListR = array('equipments', 'worklists', 'testFilter', 'targets');
		$andOrListF = array('workStates', 'normality');
		$collectionsR = array ('biomaterials', 'doctors', 'custDepartments', 'hospitals', 'payCategories');
		$collectionsF = array ();
		$fieldsR = array('department');
		return $this->xml_composeFilter('work-journal', $filter, $andOrListR, $andOrListF, $collectionsR, $collectionsF, $fieldsR);
	}

	/**
	 * @param int $sample
	 * @return string
	 */
	function lis_request_works($sample){
		$w = $this->xml_startXmlRequestWriterDocument('request-works');
		$w->startElement('r');
		$w->writeAttribute('n', 'sample');
		$w->writeAttribute('i', $sample);
		$w->endElement();//r
		return $this->xml_endXmlRequestWriterDocument($w);

	}

	/**
	 * TO-DO: Описать
	 */
	function lis_sample_info($sample){
		$w = $this->xml_startXmlRequestWriterDocument('sample-info');
		$w->startElement('r');
		$w->writeAttribute('n', 'sample');
		$w->writeAttribute('i', $sample);
		$w->endElement();//r
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * TO-DO: Описать
	 */
	function lis_request_samples($requestId)
	{
		$w = $this->xml_startXmlRequestWriterDocument('request-samples');
		$w->startElement('r');
		$w->writeAttribute('n', 'request');
		$w->writeAttribute('i', $requestId);
		$w->endElement();//r
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * TO-DO: Описать
	 */
	function lis_parse_responce_registration_journal($response){
		$result = array();
		$r = new XMLReader();
		$r->xml($response);
		if ($this->xml_read_moveTo($r, 's', 'request')) {
			while ($this->xml_read_moveTo($r, 'o', '')){
				//для каждого o
				$tmp = array();
				while ($r->read()){
					switch ($r->nodeType) {
						case XMLReader::ELEMENT:
							if ($r->nodeType === XMLReader::ELEMENT) {
								switch ($r->name) {
									case 'f':
										$this->xml_read_collect_f($r, $tmp);
										break;
									case 's':
										if ($r->moveToAttribute("n")) {
											if ($r->value === 'userValues') {
												$r->moveToElement();
												$tmp['userValues'] = $this->xml_read_collect_userValues($r);
											} else {
												throw new Exception('Неожиданный элемент <s ... n="'.$r->value.'" ...>');
											}
										} else {
											throw new Exception('Элемент s без атрибута n');
										}
								}
							}
							break;
						case XMLReader::END_ELEMENT:
							break 2;
					}
				}
				$result[] = $tmp;
			}
		} else {
			throw new Exception('Элемент <s n=request> не найден');
		}
		array_walk_recursive($result, 'ConvertFromUTF8ToWin1251');
		return $result;
	}

	/**
	 * TO-DO: Описать
	 */
	function lis_parse_responce_request_works($response){
		$result = array();
		$r = new XMLReader();
		$r->xml($response);
		if ($this->xml_read_moveTo($r, 'o', '')) {
			while ($r->read()) {
				switch ($r->nodeType) {
					case $r::ELEMENT:
						switch ($r->name) {
							case 'f':
								$this->xml_read_collect_f($r,$result);
								break;
							case 's':
								if ($r->moveToAttribute('n')) {
									switch ($r->value) {
										case 'patientGroups':
											$result['patientGroups'] = $this->xml_read_collect_r($r, 'patientGroups');
											break;
										case 'works':
											$result['works'] = $this->xml_read_collect_works($r);
											break;
										default:
											throw new Exception('Неожиданный элемент <s n="'.$r->value.'">');
											break;
									}
								} else {
									throw new Exception('Не найден атрибут n элемента s в <o n="">');
								}
								break;
							default:
								throw new Exception('Неожиданный элемент '.$r->name.'в <o n="">');
								break;
						}
						break;
					case $r::END_ELEMENT:
						break 2;
				}
			}
		} else {
			throw new Exception('Элемент <o "n"=""> не найден');
		}
		array_walk_recursive($result, 'ConvertFromUTF8ToWin1251');
		return $result;
	}

	/**
	 * TO-DO: Описать
	 */
	function lis_parse_responce_work_journal($response){
		$result = array();
		$r = new XMLReader();
		$r->xml($response);
		if ($this->xml_read_moveTo($r, 's', 'samples')) {
			while ($r->read()) {
				switch ($r->nodeType) {
					case $r::ELEMENT:
						if ('o' === $r->name) {
							if ($r->moveToAttribute('n')) {
								if ("" === $r->value) {
									$tmp = array();
									while ($r->read()) {
										switch ($r->nodeType) {
											case $r::ELEMENT:
												switch ($r->name) {
													case 'f':
														$this->xml_read_collect_f($r,$tmp);
														break;
													case 's':
														if ($r->moveToAttribute('n')) {
															switch ($r->value) {
																case 'targets':
																	$r->moveToElement();
																	$tmp['targets'] = $this->xml_read_collect_r($r, 'targets');
																	break;
																case 'userValues':
																	$r->moveToElement();
																	$tmp['userValues'] = $this->xml_read_collect_userValues($r);
																	break;
															}
														} else {
															throw new Exception('Не указан атрибут n у элемента s в <o n="">');
														}
														break;
													default:
														throw new Exception('Неожиданный элемент '.$r->name.' в <o n="">');
														break;
												}
												break;
											case $r::END_ELEMENT:
												if ('o' === $r->name) {
													break 2;
												} else {
													throw new Exception('Неожиданный конец элемента '.$r->name.' в <o n="">');
												}
										}
									}
									$result[] = $tmp;
								} else {
									throw new Exception('Неожиданное значение '.$r->value.' атрибута n у элемента o в <s n="samples">');
								}
							} else {
								throw new Exception('Не найден атрибут n у элемента o в <s n="samples">');
							}
						}
						break;
				}
			}
		} else {
			throw new Exception('Элемент <s "n"="samples"> не найден');
		}
		array_walk_recursive($result, 'ConvertFromUTF8ToWin1251');
		return $result;
	}

	/**
	 * TO-DO: Описать
	 */
	function lis_parse_responce_sample_info($response) {
		$result = array();
		$r = new XMLReader();
		$r->xml($response);
		if ($this->xml_read_moveTo($r, 'o', '')) {
			while ($r->read()) {
				switch($r->nodeType){
					case $r::ELEMENT:
						switch ($r->name) {
							case 'f':
								$this->xml_read_collect_f($r,$result);
								break;
							case 's':
								if ($r->moveToAttribute('n')) {
									if ('works' === $r->value) {
										$result['works'] = $this->xml_read_collect_works($r);
									} else {
										$s_name = $r->value;
										$r->moveToElement();
										$result[$s_name] = $this->xml_read_collect_r($r, $s_name);
									}
								} else {
									throw new Exception('Не найден атрибут n у элемента s');
								}
								break;
							case 'r':
								if ($r->moveToAttribute('n')) {
									$key = $r->value;
									if ($r->moveToAttribute('i')) {
										$result[$key] = (int)$r->value;
									} else  {
										throw new Exception('Не найден атрибут i у элемента r');
									}
								} else {
									throw new Exception('Не найден атрибут n у элемента r');
								}
								break;
							default:
								throw new Exception('Неожиданный элемент '.$r->name.' в <o "n"="">');
								break;
						}
						break;
					case $r::END_ELEMENT:
						if ('o' === $r->name) {
							break 2;
						} else {
							throw new Exception('Неожиданный конец элемента '.$r->name.' в <o "n"="">');
						}
				}
			}
		} else {
			throw new Exception('Элемент <o "n"=""> не найден');
		}
		array_walk_recursive($result, 'ConvertFromUTF8ToWin1251');
		return $result;
	}

	/**
	 * TO-DO: Описать
	 */
	function lis_parse_responce_request_sample($response){
		$result = array();
		$r = new XMLReader();
		$r->xml($response);
		if ($this->xml_read_moveTo($r, 's', 'samples')) {
			while ($r->read()) {
				switch($r->nodeType){
					case $r::ELEMENT:
						if ('o' === $r->name) {
							if ($r->moveToAttribute('n')) {
								if ('' === $r->value) {
									$tmp = array();
									while ($r->read()) {
										switch ($r->nodeType) {
											case $r::ELEMENT:
												switch ($r->name) {
													case 'f':
														$this->xml_read_collect_f($r,$tmp);
														break;
													case 's':
														if ($r->moveToAttribute('n')) {
															$s_name = $r->value;
															$r->moveToElement();
															$tmp[$s_name] = $this->xml_read_collect_r($r, $s_name);
														} else {
															throw new Exception('Не найден атрибут n у элемента s');
														}
														break;
													case 'r':
														if ($r->moveToAttribute('n')) {
															$key = $r->value;
															if ($r->moveToAttribute('i')) {
																$tmp[$key] = (int)$r->value;
															} else  {
																throw new Exception('Не найден атрибут i у элемента r');
															}
														} else {
															throw new Exception('Не найден атрибут n у элемента r');
														}
														break;
													default:
														throw new Exception('Неожиданный элемент '.$r->name.' в <o "n"="">');
														break;
												}
												break;
											case $r::END_ELEMENT:
												if ('o' === $r->name) {
													break 2;
												} else {
													throw new Exception('Неожиданный конец элемента '.$r->name.' в <o "n"="">');
												}
										}
									}
									$result[] = $tmp;
								}
							} else {
								throw new Exception('Не найден атрибут n у элемента o');
							}
						}
						break;
					case $r::END_ELEMENT:
						if ('s' === $r->name) {
							break 2;
						} else {
							throw new Exception('Неожиданный конец элемента '.$r->name.' в <s "n"="samples">');
						}
				}
			}
		} else {
			throw new Exception('Элемент <s "n"="samples"> не найден');
		}
		array_walk_recursive($result, 'ConvertFromUTF8ToWin1251');
		return $result;
	}
}