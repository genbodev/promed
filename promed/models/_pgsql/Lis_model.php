<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Lis
 * @access	   public
 * @copyright	Copyright (c) 2011 Swan Ltd.
 * @author	   Markoff Andrew <markov@swan.perm.ru>
 * @version	  10.2011
 *
 * @property CI_DB_driver $db
 *
 * @property EvnLabSample_model $EvnLabSample_model
 * @property EvnLabRequest_model $EvnLabRequest_model
 * @property Analyzer_model $Analyzer_model
 * @property LisUser_model $usermodel
*/

class Lis_model extends SwPgModel
{
	public $log_to = 'textlog';//Куда выводить лог: textlog|print_r|false
	public $map = array(
		// Оказывается у нас таблицы называются не так, как в ЛИС справочники, что грустно. 
		// Пришлось ввести параметр table 
		'target' => array('table' => 'Target', 'name' => 'Target_Name', 'code' => 'Target_Code', 'mnemonics' => 'Target_SysNick', 'id' => 'Target_id', 'removed' => 'Target_Deleted', 'pmUser_id' => 'pmUser_id'),
		'category' => array('table' => 'Category', 'name' => 'Category_Name', 'code' => 'Category_Code', 'mnemonics' => 'Category_SysNick', 'id' => 'Category_id', 'removed' => 'Category_Deleted', 'pmUser_id' => 'pmUser_id'),
		'requestCustomState' => array('table' => 'CustomState', 'name' => 'CustomState_Name', 'code' => 'CustomState_Code', 'mnemonics' => 'CustomState_SysNick', 'id' => 'CustomState_id', 'removed' => 'CustomState_Deleted', 'pmUser_id' => 'pmUser_id'),
		'defectState' => array('table' => 'defectState', 'name' => 'defectState_Name', 'code' => 'defectState_Code', 'mnemonics' => 'defectState_SysNick', 'id' => 'defectState_id', 'removed' => 'defectState_Deleted', 'pmUser_id' => 'pmUser_id'),
		'priority' => array('table' => 'priority', 'name' => 'priority_Name', 'code' => 'priority_Code', 'mnemonics' => 'priority_SysNick', 'id' => 'priority_id', 'removed' => 'priority_Deleted', 'pmUser_id' => 'pmUser_id'),
		'states' => array('table' => 'states', 'name' => 'states_Name', 'code' => 'states_Code', 'mnemonics' => 'states_SysNick', 'id' => 'states_id', 'removed' => 'states_Deleted', 'pmUser_id' => 'pmUser_id'),
		'sex' => array('table' => 'sex', 'name' => 'sex_Name', 'code' => 'sex_Code', 'mnemonics' => 'sex_SysNick', 'id' => 'sex_id', 'removed' => 'sex_Deleted', 'pmUser_id' => 'pmUser_id'),
		'profile' => array('table' => 'profile', 'name' => 'profile_Name', 'code' => 'profile_Code', 'mnemonics' => 'profile_SysNick', 'id' => 'profile_id', 'removed' => 'profile_Deleted', 'pmUser_id' => 'pmUser_id'),
		'targetGroup' => array('table' => 'targetGroup', 'name' => 'targetGroup_Name', 'code' => 'targetGroup_Code', 'mnemonics' => 'targetGroup_SysNick', 'id' => 'targetGroup_id', 'removed' => 'targetGroup_Deleted', 'pmUser_id' => 'pmUser_id'),
		'formLayout' => array('table' => 'formLayout', 'name' => 'formLayout_Name', 'code' => 'formLayout_Code', 'mnemonics' => 'formLayout_SysNick', 'id' => 'formLayout_id', 'removed' => 'formLayout_Deleted', 'pmUser_id' => 'pmUser_id'),
		'storage' => array('table' => 'storage', 'name' => 'storage_Name', 'code' => 'storage_Code', 'mnemonics' => 'storage_SysNick', 'id' => 'storage_id', 'removed' => 'storage_Deleted', 'pmUser_id' => 'pmUser_id'),
		'printFormUnit' => array('table' => 'printFormUnit', 'name' => 'printFormUnit_Name', 'code' => 'printFormUnit_Code', 'mnemonics' => 'printFormUnit_SysNick', 'id' => 'printFormUnit_id', 'removed' => 'printFormUnit_Deleted', 'pmUser_id' => 'pmUser_id'),
		'externalSystem' => array('table' => 'externalSystem', 'name' => 'externalSystem_Name', 'code' => 'externalSystem_Code', 'mnemonics' => 'externalSystem_SysNick', 'id' => 'externalSystem_id', 'removed' => 'externalSystem_Deleted', 'pmUser_id' => 'pmUser_id'),
		'commentSource' => array('table' => 'commentSource', 'name' => 'commentSource_Name', 'code' => 'commentSource_Code', 'mnemonics' => 'commentSource_SysNick', 'id' => 'commentSource_id', 'removed' => 'commentSource_Deleted', 'pmUser_id' => 'pmUser_id'),
		'printForm' => array('table' => 'printForm', 'name' => 'printForm_Name', 'code' => 'printForm_Code', 'mnemonics' => 'printForm_SysNick', 'id' => 'printForm_id', 'removed' => 'printForm_Deleted', 'pmUser_id' => 'pmUser_id'),
		'pricelist' => array('table' => 'pricelist', 'name' => 'pricelist_Name', 'code' => 'pricelist_Code', 'mnemonics' => 'pricelist_SysNick', 'id' => 'pricelist_id', 'removed' => 'pricelist_Deleted', 'pmUser_id' => 'pmUser_id'),
		'userRule' => array('table' => 'userRule', 'name' => 'userRule_Name', 'code' => 'userRule_Code', 'mnemonics' => 'userRule_SysNick', 'id' => 'userRule_id', 'removed' => 'userRule_Deleted', 'pmUser_id' => 'pmUser_id'),
		'scanForm' => array('table' => 'scanForm', 'name' => 'scanForm_Name', 'code' => 'scanForm_Code', 'mnemonics' => 'scanForm_SysNick', 'id' => 'scanForm_id', 'removed' => 'scanForm_Deleted', 'pmUser_id' => 'pmUser_id'),
		'streetType' => array('table' => 'streetType', 'name' => 'streetType_Name', 'code' => 'streetType_Code', 'mnemonics' => 'streetType_SysNick', 'id' => 'streetType_id', 'removed' => 'streetType_Deleted', 'pmUser_id' => 'pmUser_id'),
		'sampleBlank' => array('table' => 'sampleBlank', 'name' => 'sampleBlank_Name', 'code' => 'sampleBlank_Code', 'mnemonics' => 'sampleBlank_SysNick', 'id' => 'sampleBlank_id', 'removed' => 'sampleBlank_Deleted', 'pmUser_id' => 'pmUser_id'),
		'patient' => array('table' => 'patient', 'name' => 'patient_Name', 'code' => 'patient_Code', 'mnemonics' => 'patient_SysNick', 'id' => 'patient_id', 'removed' => 'patient_Deleted', 'pmUser_id' => 'pmUser_id'),
		'worklistDefGroup' => array('table' => 'worklistDefGroup', 'name' => 'worklistDefGroup_Name', 'code' => 'worklistDefGroup_Code', 'mnemonics' => 'worklistDefGroup_SysNick', 'id' => 'worklistDefGroup_id', 'removed' => 'worklistDefGroup_Deleted', 'pmUser_id' => 'pmUser_id'),
		'qcTestGroup' => array('table' => 'qcTestGroup', 'name' => 'qcTestGroup_Name', 'code' => 'qcTestGroup_Code', 'mnemonics' => 'qcTestGroup_SysNick', 'id' => 'qcTestGroup_id', 'removed' => 'qcTestGroup_Deleted', 'pmUser_id' => 'pmUser_id'),
		'testPrintGroup' => array('table' => 'testPrintGroup', 'name' => 'testPrintGroup_Name', 'code' => 'testPrintGroup_Code', 'mnemonics' => 'testPrintGroup_SysNick', 'id' => 'testPrintGroup_id', 'removed' => 'testPrintGroup_Deleted', 'pmUser_id' => 'pmUser_id'),
		'equipmentTestGroups' => array('table' => 'equipmentTestGroups', 'name' => 'equipmentTestGroups_Name', 'code' => 'equipmentTestGroups_Code', 'mnemonics' => 'equipmentTestGroups_SysNick', 'id' => 'equipmentTestGroups_id', 'removed' => 'equipmentTestGroups_Deleted', 'pmUser_id' => 'pmUser_id'),
		'doctor' => array('table' => 'doctor', 'name' => 'doctor_Name', 'code' => 'doctor_Code', 'mnemonics' => 'doctor_SysNick', 'id' => 'doctor_id', 'removed' => 'doctor_Deleted', 'pmUser_id' => 'pmUser_id'),
		'myelogramm' => array('table' => 'myelogramm', 'name' => 'myelogramm_Name', 'code' => 'myelogramm_Code', 'mnemonics' => 'myelogramm_SysNick', 'id' => 'myelogramm_id', 'removed' => 'myelogramm_Deleted', 'pmUser_id' => 'pmUser_id'),
		'policyType' => array('table' => 'policyType', 'name' => 'policyType_Name', 'code' => 'policyType_Code', 'mnemonics' => 'policyType_SysNick', 'id' => 'policyType_id', 'removed' => 'policyType_Deleted', 'pmUser_id' => 'pmUser_id'),
		'printFormNew' => array('table' => 'printFormNew', 'name' => 'printFormNew_Name', 'code' => 'printFormNew_Code', 'mnemonics' => 'printFormNew_SysNick', 'id' => 'printFormNew_id', 'removed' => 'printFormNew_Deleted', 'pmUser_id' => 'pmUser_id'),
		'constant' => array('table' => 'constant', 'name' => 'constant_Name', 'code' => 'constant_Code', 'mnemonics' => 'constant_SysNick', 'id' => 'constant_id', 'removed' => 'constant_Deleted', 'pmUser_id' => 'pmUser_id'),
		'requestForm' => array('table' => 'requestForm', 'name' => 'requestForm_Name', 'code' => 'requestForm_Code', 'mnemonics' => 'requestForm_SysNick', 'id' => 'requestForm_id', 'removed' => 'requestForm_Deleted', 'pmUser_id' => 'pmUser_id'),
		'requestFormLayout' => array('table' => 'requestFormLayout', 'name' => 'requestFormLayout_Name', 'code' => 'requestFormLayout_Code', 'mnemonics' => 'requestFormLayout_SysNick', 'id' => 'requestFormLayout_id', 'removed' => 'requestFormLayout_Deleted', 'pmUser_id' => 'pmUser_id'),
		'defaultPrintForm' => array('table' => 'defaultPrintForm', 'name' => 'defaultPrintForm_Name', 'code' => 'defaultPrintForm_Code', 'mnemonics' => 'defaultPrintForm_SysNick', 'id' => 'defaultPrintForm_id', 'removed' => 'defaultPrintForm_Deleted', 'pmUser_id' => 'pmUser_id'),
		'hospital' => array('table' => 'hospital', 'name' => 'hospital_Name', 'code' => 'hospital_Code', 'mnemonics' => 'hospital_SysNick', 'id' => 'hospital_id', 'removed' => 'hospital_Deleted', 'pmUser_id' => 'pmUser_id'),
		'bioMaterial' => array('table' => 'bioMaterial', 'name' => 'bioMaterial_Name', 'code' => 'bioMaterial_Code', 'mnemonics' => 'bioMaterial_SysNick', 'id' => 'bioMaterial_id', 'removed' => 'bioMaterial_Deleted', 'pmUser_id' => 'pmUser_id'),
		'userGraphics' => array('table' => 'userGraphics', 'name' => 'userGraphics_Name', 'code' => 'userGraphics_Code', 'mnemonics' => 'userGraphics_SysNick', 'id' => 'userGraphics_id', 'removed' => 'userGraphics_Deleted', 'pmUser_id' => 'pmUser_id'),
		'material' => array('table' => 'material', 'name' => 'material_Name', 'code' => 'material_Code', 'mnemonics' => 'material_SysNick', 'id' => 'material_id', 'removed' => 'material_Deleted', 'pmUser_id' => 'pmUser_id'),
		'userGroup' => array('table' => 'userGroup', 'name' => 'userGroup_Name', 'code' => 'userGroup_Code', 'mnemonics' => 'userGroup_SysNick', 'id' => 'userGroup_id', 'removed' => 'userGroup_Deleted', 'pmUser_id' => 'pmUser_id'),
		'userField' => array('table' => 'userField', 'name' => 'userField_Name', 'code' => 'userField_Code', 'mnemonics' => 'userField_SysNick', 'id' => 'userField_id', 'removed' => 'userField_Deleted', 'pmUser_id' => 'pmUser_id'),
		'qcEvent' => array('table' => 'qcEvent', 'name' => 'qcEvent_Name', 'code' => 'qcEvent_Code', 'mnemonics' => 'qcEvent_SysNick', 'id' => 'qcEvent_id', 'removed' => 'qcEvent_Deleted', 'pmUser_id' => 'pmUser_id'),
		'userDirectory' => array('table' => 'userDirectory', 'name' => 'userDirectory_Name', 'code' => 'userDirectory_Code', 'mnemonics' => 'userDirectory_SysNick', 'id' => 'userDirectory_id', 'removed' => 'userDirectory_Deleted', 'pmUser_id' => 'pmUser_id'),
		'unit' => array('table' => 'unit', 'name' => 'unit_Name', 'code' => 'unit_Code', 'mnemonics' => 'unit_SysNick', 'id' => 'unit_id', 'removed' => 'unit_Deleted', 'pmUser_id' => 'pmUser_id'),
		'userDirectoryValue' => array('table' => 'userDirectoryValue', 'name' => 'userDirectoryValue_Name', 'code' => 'userDirectoryValue_Code', 'mnemonics' => 'userDirectoryValue_SysNick', 'id' => 'userDirectoryValue_id', 'removed' => 'userDirectoryValue_Deleted', 'pmUser_id' => 'pmUser_id'),
		'accessRight' => array('table' => 'accessRight', 'name' => 'accessRight_Name', 'code' => 'accessRight_Code', 'mnemonics' => 'accessRight_SysNick', 'id' => 'accessRight_id', 'removed' => 'accessRight_Deleted', 'pmUser_id' => 'pmUser_id'),
		'test' => array('table' => 'test', 'name' => 'test_Name', 'code' => 'test_Code', 'mnemonics' => 'test_SysNick', 'id' => 'test_id', 'removed' => 'test_Deleted', 'pmUser_id' => 'pmUser_id'),
		'requestDistrict' => array('table' => 'requestDistrict', 'name' => 'requestDistrict_Name', 'code' => 'requestDistrict_Code', 'mnemonics' => 'requestDistrict_SysNick', 'id' => 'requestDistrict_id', 'removed' => 'requestDistrict_Deleted', 'pmUser_id' => 'pmUser_id'),
		'patientGroup' => array('table' => 'patientGroup', 'name' => 'patientGroup_Name', 'code' => 'patientGroup_Code', 'mnemonics' => 'patientGroup_SysNick', 'id' => 'patientGroup_id', 'removed' => 'patientGroup_Deleted', 'pmUser_id' => 'pmUser_id'),
		'hospitalCategory' => array('table' => 'hospitalCategory', 'name' => 'hospitalCategory_Name', 'code' => 'hospitalCategory_Code', 'mnemonics' => 'hospitalCategory_SysNick', 'id' => 'hospitalCategory_id', 'removed' => 'hospitalCategory_Deleted', 'pmUser_id' => 'pmUser_id'),
		'qcProducer' => array('table' => 'qcProducer', 'name' => 'qcProducer_Name', 'code' => 'qcProducer_Code', 'mnemonics' => 'qcProducer_SysNick', 'id' => 'qcProducer_id', 'removed' => 'qcProducer_Deleted', 'pmUser_id' => 'pmUser_id'),
		'customReport' => array('table' => 'customReport', 'name' => 'customReport_Name', 'code' => 'customReport_Code', 'mnemonics' => 'customReport_SysNick', 'id' => 'customReport_id', 'removed' => 'customReport_Deleted', 'pmUser_id' => 'pmUser_id'),
		'qcMaterial' => array('table' => 'qcMaterial', 'name' => 'qcMaterial_Name', 'code' => 'qcMaterial_Code', 'mnemonics' => 'qcMaterial_SysNick', 'id' => 'qcMaterial_id', 'removed' => 'qcMaterial_Deleted', 'pmUser_id' => 'pmUser_id'),
		'service' => array('table' => 'service', 'name' => 'service_Name', 'code' => 'service_Code', 'mnemonics' => 'service_SysNick', 'id' => 'service_id', 'removed' => 'service_Deleted', 'pmUser_id' => 'pmUser_id'),
		'materialUnit' => array('table' => 'materialUnit', 'name' => 'materialUnit_Name', 'code' => 'materialUnit_Code', 'mnemonics' => 'materialUnit_SysNick', 'id' => 'materialUnit_id', 'removed' => 'materialUnit_Deleted', 'pmUser_id' => 'pmUser_id'),
		'report' => array('table' => 'report', 'name' => 'report_Name', 'code' => 'report_Code', 'mnemonics' => 'report_SysNick', 'id' => 'report_id', 'removed' => 'report_Deleted', 'pmUser_id' => 'pmUser_id'),
		'city' => array('table' => 'city', 'name' => 'city_Name', 'code' => 'city_Code', 'mnemonics' => 'city_SysNick', 'id' => 'city_id', 'removed' => 'city_Deleted', 'pmUser_id' => 'pmUser_id'),
		'cityType' => array('table' => 'cityType', 'name' => 'cityType_Name', 'code' => 'cityType_Code', 'mnemonics' => 'cityType_SysNick', 'id' => 'cityType_id', 'removed' => 'cityType_Deleted', 'pmUser_id' => 'pmUser_id'),
		'street' => array('table' => 'street', 'name' => 'street_Name', 'code' => 'street_Code', 'mnemonics' => 'street_SysNick', 'id' => 'street_id', 'removed' => 'street_Deleted', 'pmUser_id' => 'pmUser_id'),
		'customCommand' => array('table' => 'customCommand', 'name' => 'customCommand_Name', 'code' => 'customCommand_Code', 'mnemonics' => 'customCommand_SysNick', 'id' => 'customCommand_id', 'removed' => 'customCommand_Deleted', 'pmUser_id' => 'pmUser_id'),
		'treeViewLayout' => array('table' => 'treeViewLayout', 'name' => 'treeViewLayout_Name', 'code' => 'treeViewLayout_Code', 'mnemonics' => 'treeViewLayout_SysNick', 'id' => 'treeViewLayout_id', 'removed' => 'treeViewLayout_Deleted', 'pmUser_id' => 'pmUser_id'),
		'printFormHeader' => array('table' => 'printFormHeader', 'name' => 'printFormHeader_Name', 'code' => 'printFormHeader_Code', 'mnemonics' => 'printFormHeader_SysNick', 'id' => 'printFormHeader_id', 'removed' => 'printFormHeader_Deleted', 'pmUser_id' => 'pmUser_id'),
		'serviceShort' => array('table' => 'serviceShort', 'name' => 'serviceShort_Name', 'code' => 'serviceShort_Code', 'mnemonics' => 'serviceShort_SysNick', 'id' => 'serviceShort_id', 'removed' => 'serviceShort_Deleted', 'pmUser_id' => 'pmUser_id'),
		'supplier' => array('table' => 'supplier', 'name' => 'supplier_Name', 'code' => 'supplier_Code', 'mnemonics' => 'supplier_SysNick', 'id' => 'supplier_id', 'removed' => 'supplier_Deleted', 'pmUser_id' => 'pmUser_id'),
		'testRule' => array('table' => 'testRule', 'name' => 'testRule_Name', 'code' => 'testRule_Code', 'mnemonics' => 'testRule_SysNick', 'id' => 'testRule_id', 'removed' => 'testRule_Deleted', 'pmUser_id' => 'pmUser_id'),
		'payCategory' => array('table' => 'payCategory', 'name' => 'payCategory_Name', 'code' => 'payCategory_Code', 'mnemonics' => 'payCategory_SysNick', 'id' => 'payCategory_id', 'removed' => 'payCategory_Deleted', 'pmUser_id' => 'pmUser_id'),
	);
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
	// Список справочников разрешенный для загрузки
	public $dirs = array(
		//'profile'			 ,
		'targetgroup',
		'target',
		'formlayout',
		'storage',
		'printformunit',
		'externalsystem',
		'commentsource',
		'printform',
		'pricelist',
		'userrule',
		'scanform',
		//'streettype'		  ,
		//'serviceshort'		,
		//'sampleblank'		 ,
		//'patient'			 ,
		//'worklistdefgroup'	,
		'qctestgroup',
		'testprintgroup',
		//'equipmenttestgroups' ,
		'doctor',
		'myelogramm',
		//'policytype'		  ,
		//'printformnew'		,
		'constant',
		'requestform',
		//'requestformlayout'   ,
		'defaultprintform',
		'hospital',
		'biomaterial',
		'usergraphics',
		'material',
		//'usergroup'		   ,
		'userfield',
		'qcevent',
		'userdirectory',
		'unit',
		//'userdirectoryvalue'  ,
		'accessright',
		'test',
		//'requestdistrict'	 ,
		'patientgroup',
		'hospitalcategory',
		'qcproducer',
		'customreport',
		'qcmaterial',
		//'service'			 ,
		'materialunit',
		'report',
		//'city'				,
		//'citytype'			,
		//'street'			  ,
		//'customcommand'	   ,
		//'treeviewlayout'	  ,
		'printformheader',
		'supplier',
		'testrule',
		'paycategory',
		'biomaterial',
		'defectstate'
	);
	public $sessionid = null;

	/**
	 * @param string $value
	 */
	function textlogAdd($value)
	{
		$this->textlog->add($value);
	}
	/**
	 * Lis_model constructor.
	 * @throws Exception
	 */
	function __construct()
	{
		$this->load->library("textlog", ["file" => "Lis.log"]);
		parent::__construct();
		if (!property_exists($this, "server")) {
			$this->load->swapi("common");
			try {
				$dbres = $this->common->GET("Options/DataStorageValues", ["DataStorageGroup_SysNick" => "lis"], "list");
				if (!$this->isSuccessful($dbres)) {
					throw new Exception("Ошибка при получении настроек");
				}
				$options = [];
				foreach ($dbres as $value) {
					$options[$value["DataStorage_Name"]] = $value["DataStorage_Value"];
				}
				//при тесте возвращаются параметры с другими названиями
				$this->server = [
					"address" => isset($options["lis_address"]) ? $options["lis_address"] : null,
					"server" => isset($options["lis_server"]) ? $options["lis_server"] : null,
					"port" => isset($options["lis_port"]) ? $options["lis_port"] : null,
					"path" => isset($options["lis_path"]) ? $options["lis_path"] : null,
					"version" => isset($options["lis_version"]) ? $options["lis_version"] : null,
					"buildnumber" => isset($options["lis_buildnumber"]) ? $options["lis_buildnumber"] : null,
				];
			} catch (Exception $e) {
				throw new Exception("Не удалось получить настройки для ЛИС", 0, $e);
			}
		}
	}

	/**
	 * Получает список проб по выбранным заявкам для отправки в ЛИС
	 * @param $data
	 * @return array
	 */
	function getLabSamplesForEvnLabRequests($data)
	{
		$arrayId = [];
		if (!empty($data["EvnLabRequests"])) {
			$data["EvnLabRequests"] = json_decode($data["EvnLabRequests"]);
			if (!empty($data["EvnLabRequests"])) {
				// достаём пробы заявки
				$EvnLabRequestsString = implode(",", $data["EvnLabRequests"]);
				$query = "
					select EvnLabSample_id as \"EvnLabSample_id\"
					from v_EvnLabSample
					where EvnLabRequest_id in ({$EvnLabRequestsString})
				";
				/**@var CI_DB_result $result */
				$result = $this->db->query($query);
				$data["EvnLabSamples"] = [];
				if (is_object($result)) {
					$resp = $result->result_array();
					foreach ($resp as $respone) {
						$arrayId[] = $respone["EvnLabSample_id"];
					}
				}
			}
		}
		return $arrayId;
	}

	/**
	 * Получение результатов из ЛИС по всем отправленным пробам без результата
	 * @throws Exception
	 */
	function checkLisLabSamples()
	{
		$this->textlogAdd("checkLisLabSamples: Запуск");
		// 1. получаем список проб находящихся в работе за последние 3 дня
		$query = "
			select ls.EvnLabSample_id as \"EvnLabSample_id\"
			from v_EvnLabSample ls
			where ls.LabSampleStatus_id = 2
			  and datediff('day', ls.EvnLabSample_updDT, dbo.tzGetDate()) <= 3
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$funcParams = [
					"EvnLabSample_id" => $respone["EvnLabSample_id"],
					"pmUser_id" => 1,
					"login" => LIS_LOGIN,
					"password" => LIS_PASSWORD,
					"clientId" => LIS_CLIENTID
				];
				$response = $this->sample($funcParams);
				$this->textlogAdd(
					(!empty($response[0]["Error_Msg"]))
						? "checkLisLabSamples: EvnLabSample_id: {$respone["EvnLabSample_id"]}, Error_Msg: {$response[0]["Error_Msg"]}"
						: "checkLisLabSamples: EvnLabSample_id: {$respone["EvnLabSample_id"]}, Ok"
				);
			}
		}
		$this->textlogAdd("checkLisLabSamples: Конец");
	}

	function base()
	{

	}

	/**
	 * Формирование запроса на добавление, редактирование или удаление записи
	 * @param $name
	 * @param $map
	 * @param $records
	 * @param $mode
	 * @return string
	 */
	function getSql($name, $map, $records, $mode)
	{
		//TODO 111
		$sql = "";
		$procedureName = (isset($map[$name]["table"])) ? $map[$name]["table"] : $name;
		switch ($mode) {
			case "insert":
				$sql .= "exec lis.p_{$procedureName}_ins ";
				foreach ($records as $k => $v) {
					// Для boolean логика YesNo
					if ($v == "false") {
						$v = 1;
					}
					if ($v == "true") {
						$v = 2;
					}
					if (isset($map[$name])) {
						if (isset($map[$name][$k])) {
							$k = $map[$name][$k];
							$sql .= "@{$k}='{$v}', ";
						}
					} else {
						$sql .= "@{$k}='{$v}', ";
					}
				}
				$sql = trim($sql, ", ");
				break;
			case "update":
				$sql .= "exec lis.p_{$procedureName}_upd ";
				foreach ($records as $k => $v) {
					// Для boolean логика YesNo
					if ($v == "false") {
						$v = 1;
					}
					if ($v == "true") {
						$v = 2;
					}
					if (isset($map[$name])) {
						if (isset($map[$name][$k])) {
							$k = $map[$name][$k];
							$sql .= "@{$k}='{$v}', ";
						}
					} else {
						$sql .= "@{$k}='{$v}', ";
					}
				}
				$sql = trim($sql, ", ");
				break;
			case "delete":
				$sql .= "exec lis.p_{$procedureName}_del " . $records["id"];
				break;
		}
		return $sql;
	}

	/**
	 * Проверка существует ли запись
	 * @param $name
	 * @param $id
	 * @return bool
	 */
	function existRecord($name, $id)
	{
		$query = "
			select 1
			from lis.{$name}
			where {$name}_id = :id
		";
		$result = $this->getFirstResultFromQuery($query, ["id" => $id]);
		return ($result != false);
	}

	/**
	 * Сохранение справочника
	 * @param $name
	 * @param $map
	 * @param $records
	 * @return bool
	 */
	function saveDirectory($name, $map, $records)
	{
		// Перед сохранением надо убедиться что такой записи в справочнике нет по ID 
		// Если нет - добавляем запись
		array_walk($records, "ConvertFromUTF8ToWin1251");
		$removed = (isset($records["removed"]) && ($records["removed"] == "true")) ? 1 : 0;
		$sql = (!$removed && ($this->existRecord($name, $records["id"]) === false))
			? $this->getSql($name, $map, $records, "insert")
			: (!$removed
				? $this->getSql($name, $map, $records, "update")
				: "");
		if (!empty($sql)) {
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql);
			$response = $result->result_array();
			return (is_array($response) && count($response) > 0) ? true : false;
		}
		return false;
	}

	/**
	 * Сохраняет связь между записью ПромедВеб и записью в ЛИС
	 * @param $object_name
	 * @param $in_id
	 * @param $out_id
	 * @param $data
	 * @return bool|mixed
	 * @throws Exception
	 */
	function saveLink($object_name, $in_id, $out_id, $data)
	{
		$query = "
			select
				Link_id as \"Link_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from lis.p_Link_ins (
				Link_id := :Link_id,
				link_object := :object_name,
				object_id := :in_id,
				lis_id := :out_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = [
			"object_name" => $object_name,
			"in_id" => $in_id,
			"out_id" => $out_id,
			"pmUser_id" => $data["pmUser_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$response = $result->result_array();
		return (is_array($response) && count($response) > 0) ? $response[0] : false;
	}

	/**
	 * Возвращает идентификатор ЛИС по идентификатору объекта в промед
	 * @param $object_name
	 * @param $in_id
	 * @return |null
	 * @throws Exception
	 */
	function loadLinkId($object_name, $in_id)
	{
		$query = "
			select lis_id 
			from lis.Link
			where link_object = :object_name and object_id = :in_id;
		";
		$queryParams = [
			"object_name" => $object_name,
			"in_id" => $in_id
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$response = $result->result_array();
		return (is_array($response) && count($response) > 0) ? $response[0]["lis_id"] : null;
	}

	/**
	 * Получает список тестов по пробе
	 * @param $data
	 * @return array
	 */
	function getSampleTests($data)
	{
		$params = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$query = "
			Select
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				case when length(EvnLabSample_Num) = 12
				    then substring(EvnLabSample_Num, length(EvnLabSample_Num)-3, 4)
				    else EvnLabSample_Num
				end as \"EvnLabSample_Num\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				test.test_id as \"test_id\",
				link.lis_id as \"lis_id\"
			from
				v_EvnLabSample ls
				inner join v_UslugaTest ut on ut.EvnLabSample_id = ls.EvnLabSample_id
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
				left join v_UslugaComplex uc2011 on uc.UslugaComplex_2011id = uc2011.UslugaComplex_id
				left join lateral (
					select Test.id as test_id
					from lis._test Test
					where code = uc2011.UslugaComplex_Code
					  and removed = 'false'
					limit 1
				) as test on true
				left join lateral (
					select link.lis_id
					from lis.link link
					where object_id = ls.EvnLabSample_id
					  and link_object = 'EvnLabSample'
					order by link_id desc
					limit 1
				) as link on true
			where ls.EvnLabSample_id = :EvnLabSample_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : [];
	}

	/**
	 * Получает информацию по пробе
	 * @param $data
	 * @return array
	 */
	function getSampleInfo($data)
	{
		$params = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$query = "
			select
				link.lis_id,
				link_defect.lis_id as defect_id
			from
				v_EvnLabSample ls
				left join lateral (
					select link.lis_id
					from lis.link link
					where object_id = ls.EvnLabSample_id and link_object = 'EvnLabSample'
					order by link_id desc
					limit 1
				) as link on true
				left join lateral (
					select link.lis_id
					from lis.link link
					where object_id = ls.DefectCauseType_id and link_object = 'DefectCauseType'
					order by link_id desc
					limit 1
				) as link_defect on true
			where ls.EvnLabSample_id = :EvnLabSample_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : [];
	}


	/**
	 * Функция создания XML-запроса для обращения к сервису ЛИС из текстовой XML
	 *
	 * @param $method
	 * @param $request
	 * @param bool $empty
	 * @return string
	 */
	function createXmlRequestWithText($method, $request, $empty = true)
	{
		if ($this->isLogon()) {
			$sessionid = $_SESSION["phox"]["sessionid"];
		}
		$xml = "<?xml version=\"1.0\" encoding=\"Windows - 1251\"?>";
		$code = "<!DOCTYPE request SYSTEM \"lims . dtd\">";
		return "
			{$xml}
			{$code}
			<request type=\"{$method}\" sessionid=\"{$sessionid}\" version=\"{$this->server["version"]}\" buildnumber = \"{$this->server["buildnumber"]}\" >
			<content>
			{$request}
			</content>
			</request>
		";
	}

	/**
	 * Создает XMLWriter, который можно наполнить данными запроса. Будет включать всю необходимю информацию кроме тела запроса
	 * Для корректной работы требуется перед вызовом метода убедится что произведен вход в ЛИС
	 * Чтобы после добавления в документ нужной иформации корректно закончить его и получить XML, надо вызвать endXmlRequestWriterDocument
	 * @param $type
	 * @param bool $silent
	 * @return array|XMLWriter
	 * @throws Exception
	 */
	function xml_startXmlRequestWriterDocument($type, $silent = false)
	{
		$sessionid = null;
		if ($this->isLogon()) {
			$sessionid = $_SESSION["phox"]["sessionid"];
		}
		if (is_null($sessionid)) {
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
		$w->writeAttribute("sessionid", $sessionid);
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
		$result = $result && $w->startElement('f');
		$var_type = gettype($f_value);
		switch ($var_type) {
			case "double":
			case "integer":
				$t = "i";
				$v = (string)$f_value;
				break;
			case "object":
				if (!($f_value instanceof Datetime)) {
					throw new Exception("Запись объекта класса в XML" . get_class($f_value) . " не поддерживается ({$f_name} = {$f_value})");
				}
				/** @var Datetime $f_value */
				$t = "d";
				$v = $f_value->format("d.m.Y H:i:s");
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
			$result = $result && $w->writeAttribute("t", $t);
		}
		if (!is_null($f_name)) {
			$result = $result && $w->writeAttribute("n", $f_name);
		}
		$result = $result && $w->writeAttribute("v", $v);
		$result = $result && $w->endElement();//f
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
		$w->endElement();//r
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
		$w->endElement();//s
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
				throw new Exception("xml_add_s: wrong type of \"{$name}\"[\"{$key}\"] value - array");
			}
			switch ($tag) {
				case "f":
					$this->xml_add_f($w, null, $value, true);
					break;
				case "r":
					$w->startElement("r");
					$w->writeAttribute("i", $value);
					$w->endElement();//r
					break;
				default:
					throw new Exception("xml_add_s: tag \"{$tag}\" not supported");
					break;
			}
		}
		$w->endElement();//s
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
					if (strtolower($r->value) === "true") {
						$v = true;
					} else {
						$v = false;
					}
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
						throw new Exception("Не удалось распознать дату: - {$r->value}");
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
								$tmp = [];
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
										//элемент o, содержаций userValues закончился
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
							throw new Exception("Неожиданный элемент {$r->name} в {$s_name}");
						}
						if (!$r->moveToAttribute("i")) {
							throw new Exception("Не найден атрибут i элемента r в {$s_name}");
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
						throw new Exception("Неожиданный элемент {$r->name} в <s n=\"works\">");
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
	 * Функция преобразования XML в нормальный массив
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
	 * Запрос registration-journal. Предназначен для выгрузки содержимого заявок на клиентское приложение.
	 * @param $data
	 * @return bool|false|string
	 * @throws Exception
	 */
	function listRegistrationJournal($data)
	{
		$this->textlogAdd("listRegistrationJournal: Запуск");
		if (!$this->isLogon()) {
			$this->login($data);
		}
		if (!$this->isLogon()) {
			return false;
		}
		$this->textlogAdd("listRegistrationJournal: Формируем запрос в XML ");
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
		$fields = ["doctors", "hospitals", "custDepartments", "departments", "targets", "customStates", "payCategories", "states"];
		foreach ($fields as $field) {
			if (!is_array($filter[$field])) {
				$filter[$field] = [];
			}
		}
		$fields = ["onlyDelayed", "markPlanDeviation", "emptyPayCategory"];
		foreach ($fields as $field) {
			if (!isset($filter[$field])) {
				$filter[$field] = false;
			}
		}
		unset($filter["session"]);//левых элементов в параметре быть не должно
		foreach ($filter as $filter_name => $filter_value) {
			if (is_null($filter_value)) {
				unset($filter[$filter_name]);//параметров, по которым не требуется фильтрация, не должно быть в запросе
			}
		}
		$xml = $this->lis_registration_journal($filter);
		$this->textlogAdd("listRegistrationJournal: Запрос: " . $xml);
		$response = $this->request($xml);
		$this->textlogAdd("listRegistrationJournal: Получили ответ");
		$this->textlogAdd("listRegistrationJournal: Ответ: " . $response);
		if (strlen($response) == 0) {
			$this->textlogAdd("listRegistrationJournal: Пришел пустой ответ!");
			return false;
		}
		$arr = $this->getXmlResponse($response, false);
		$this->textlogAdd("listRegistrationJournal: Пришел не пустой ответ, распарсили в массив");
		// Обрабатываем ответ
		if (!is_array($arr)) {
			return json_encode([]);
		}
		if ($arr["success"] !== true) {
			$this->textlogAdd("listRegistrationJournal: Ошибка {$arr["Error_Code"]}: " . toAnsi($arr["Error_Msg"]));
			$this->textlogAdd($xml);
			return json_encode($arr);
		}
		$s = $this->lis_parse_responce_registration_journal($response);
		foreach ($s as $key => $sample) {
			if (isset($sample["state"])) {
				$s[$key]["state_name"] = $this->lis_static_dicts["request_state"][$sample["state"]];
			}
			if (isset($sample["priority"])) {
				$s[$key]["priority_name"] = $this->lis_static_dicts["priority"][$sample["priority"]];
			}
			if (isset($sample["birthDay"]) && isset($sample["birthMonth"]) && isset($sample["birthYear"])) {
				$t = DateTime::createFromFormat("d.m.Y", $sample["birthDay"] . "." . $sample["birthMonth"] . "." . $sample["birthYear"]);
				$s[$key]["birthDate"] = $t->format("d.m.Y");
			}
			if (isset($sample["custHospitalId"])) {
				$s[$key]["custHospital_name"] = $this->getFirstResultFromQuery("select Hospital_Name from lis.Hospital where Hospital_id = :Hospital_id", ["Hospital_id" => $sample["custHospitalId"]]);
			}
			if (isset($sample["requestFormId"])) {
				$s[$key]["requestForm_name"] = $this->getFirstResultFromQuery("select RequestForm_name from lis.RequestForm where RequestForm_id = :RequestForm_id", ["RequestForm_id" => $sample["requestFormId"]]);
			}
			if (isset($sample["sex"])) {
				$s[$key]["Sex_name"] = $this->lis_static_dicts["sex"][$sample["sex"]];
			}
			if (isset($sample["endDate"])) {
				if (is_object($sample["endDate"]) && ($sample["endDate"] instanceof Datetime)) {
					$s[$key]["endDate"] = $sample["endDate"]->format("d.m.Y H:i");
				}
			}
			if (isset($sample["sampleDeliveryDate"])) {
				if (is_object($sample["sampleDeliveryDate"]) && ($sample["sampleDeliveryDate"] instanceof Datetime)) {
					$s[$key]["sampleDeliveryDate"] = $sample["sampleDeliveryDate"]->format("d.m.Y H:i");
				}
			}
		}
		array_walk_recursive($s, "ConvertFromWin1251ToUTF8");
		echo json_encode($s);
		return true;
	}

	/**
	 * Запрос directory. Предназначен для получения от сервера информации о текущих версиях используемых справочников.
	 *
	 * @param $data
	 * @return void
	 * @throws Exception
	 */
	function getDirectoryVersions($data)
	{
		if (!$this->isLogon()) {
			$this->login($data);
		}
		if ($this->isLogon()) {
			$request = [];
			$this->textlogAdd("getDirectoryVersions: Формируем запрос в XML ");
			// Формируем запрос в XML 
			$xml = $this->setXmlRequest("directory-versions", $request);
			$response = $this->request($xml);
			$this->textlogAdd("getDirectoryVersions: Получили ответ ");
			if (strlen($response) > 0) {
				$this->textlogAdd("getDirectoryVersions: Ответ не пустой ");
				$arr = $this->getXmlResponse($response, false);
				$this->textlogAdd("getDirectoryVersions: Распарсили ответ ");
				// Обрабатываем ответ
				if (is_array($arr)) {
					if ($arr["success"] === true) {
						$this->textlogAdd("getDirectoryVersions: Ответ хороший, список справочников получен ");
						// дальше разбираем полученный ответ и получаем список справочников 
						// Если количество справочников больше нуля
						$s = [];
						$d = $arr["s"]["o"];
						if (count($d) > 0) {
							$this->textlogAdd("getDirectoryVersions: Всего справочников получено " . count($d));
							for ($i = 0; $i < count($d); $i++) {
								$s[$i] = [];
								$s[$i]["name"] = $d[$i]["f"][0]["v"];
								$s[$i]["version"] = $d[$i]["f"][1]["v"];
								// получаем все справочники в XML формате
								$this->textlogAdd("getDirectoryVersions: Справочник {$s[$i]["name"]} (версия {$s[$i]["version"]}) готов к загрузке в XML-формате");
								$this->getDirectory($s[$i]);
								if (in_array(strtolower($s[$i]["name"]), $this->dirs, true)) {
									$this->textlogAdd("getDirectoryVersions: Справочник {$s[$i]["name"]} (версия {$s[$i]["version"]}) готов к загрузке в БД");
									$this->getDirectory($s[$i]);
								} else {
									$this->textlogAdd("getDirectoryVersions: Справочник {$s[$i]["name"]} (версия {$s[$i]["version"]}) не входит в список разрешенных к загрузке в БД справочников");
								}
							}
						}
					} else {
						// ошибка 
						$this->textlogAdd("getDirectoryVersions: Ошибка {$arr["Error_Code"]}" . toAnsi($arr["Error_Msg"]));
					}
				}
			} else {
				$this->textlogAdd("getDirectoryVersions: Ответ пустой ");
			}
		}
	}

	/**
	 * Запрос directory-versions. Предназначен для получения от сервера содержимого справочников.
	 *
	 * @param $data
	 * @return array|bool|string
	 * @throws Exception
	 */
	function getDirectory($data)
	{
		$this->textlogAdd("getDirectory: Запуск");
		if (!$this->isLogon()) {
			return false;
		}
		// Данные запроса
		$request = [];
		$request["name"] = $data["name"];
		// Формируем запрос в XML
		$this->textlogAdd("getDirectory: Хотим забрать справочник {$data["name"]}");
		$xml = $this->setXmlRequest("directory", $request);
		$this->textlogAdd("getDirectory: Формируем запрос в XML");
		$response = $this->request($xml);
		// создаем файл с именем справочника и записываем в него все пришедшие данные
		$fd = "logs\\{$data["name"]}.{$data["version"]}.xml";
		$f = fopen($fd, "w");
		fputs($f, "" . var_export($response, true));
		fclose($f);
		if (strlen($response) == 0) {
			$this->textlogAdd("getDirectory: Пришел пустой ответ!");
			return false;
		}
		$arr = $this->getXmlResponse($response, false);
		$this->textlogAdd("getDirectory: Пришел не пустой ответ");
		// Обрабатываем ответ
		if (!is_array($arr)) {
			$this->textlogAdd("getDirectory: Ошибка при возврате справочника");
			return false;
		}
		if ($arr["success"] !== true) {
			$this->textlogAdd("getDirectory: Ошибка при запросе справочника {$data["name"]}: {$arr["Error_Code"]}" . toAnsi($arr["Error_Msg"]));
			return $arr;
		}
		$this->textlogAdd("getDirectory: Ответ хороший");
		$s = [];
		$rows = ((isset($arr["s"])) && (isset($arr["s"]["o"]))) ? $arr["s"]["o"] : null;
		if (isset($rows["f"])) {
			// одна строка
			$this->textlogAdd("getDirectory: В ответе одна строка");
			$s[0] = [];
			$r = $rows["f"];
			if (count($r) > 0) {
				for ($j = 0; $j < count($r); $j++) {
					if (isset($r[$j])) {
						$s[0][$r[$j]["n"]] = $r[$j]["v"];
					}
				}
			}
		} else {
			// несколько строк
			if (count($rows) > 0) {
				$this->textlogAdd("getDirectory: В ответе " . count($rows) . " строк");
				for ($i = 0; $i < count($rows); $i++) {
					$s[$i] = [];
					$r = $rows[$i]["f"];
					if (isset($r)) {
						if (count($r) > 0) {
							for ($j = 0; $j < count($r); $j++) {
								if (isset($r[$j])) {
									$s[$i][$r[$j]["n"]] = $r[$j]["v"];
								}
							}
						}
					}
				}
			}
		}
		$this->textlogAdd("getDirectory: Успешный финиш");
		return $s;
	}

	/**
	 * Сохранение справочника
	 * @param $name
	 * @param $data
	 * @return bool
	 */
	function saveDirectoryOld($name, $data)
	{
		$this->textlogAdd('saveDirectory: Сохраняем справочник ' . $name);
		if (is_array($data)) {
			if (isset($data['Error_Msg'])) {
				$this->textlogAdd('saveDirectory: Ошибка при сохранении справочника "' . $name . '": ' . $data['Error_Code'] . ' - ' . $data['Error_Msg']);
				return false;
			}
			for ($i = 0; $i < count($data); $i++) {
				$data[$i]['pmUser_id'] = $_SESSION['pmuser_id'];
				try {
					$response = $this->saveDirectory($name, $this->map, $data[$i]);
					$this->ProcessModelSave($response, true);
				} catch (Exception $e) {
					$this->textlogAdd('ошибка сохранения элемента справочника ' . $name . ' ' . var_export($data[$i], true));
				}
			}
			$this->textlogAdd('saveDirectory: Сохранили все записи');
			return true;
		}
		return false;
	}

	/**
	 * Запросу получения проб
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
	 * Парсер ответа от запроса
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
												if ($r->moveToAttribute("i")) {
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

	/**
	 * Инфо о пробе
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
	 * Парсер инфы о пробе
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
	 * @param $data
	 * @return string
	 * @throws Exception
	 */
	function saveLisRequestFromLabSamples($data)
	{
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
		$this->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		$this->load->model("EvnLabSample_model", "EvnLabSample_model");
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
				$this->login($data);
			}
			$xml = $this->lis_create_requests_2($req);
			$response = $this->request($xml);
			return htmlspecialchars($response);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Получает список проб нуждающихся в синхронизации с ЛИС
	 * @param $data
	 * @return array
	 */
	function getEvnLabSampleNeedToSync($data)
	{
		$arrayid = [];
		$query = "
			select EvnLabSample_id as \"EvnLabSample_id\"
			from v_EvnLabSample
			where Lpu_id = :Lpu_id
			  and EvnLabSample_IsLIS = 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$arrayid[] = $respone["EvnLabSample_id"];
			}
		}
		return $arrayid;
	}

	/**
	 * Синхронизирует дефекты по выбранным пробам с ЛИС
	 * @param $data
	 * @throws Exception
	 */
	function syncSampleDefects($data)
	{
		// Алгоритм работы функции проверки результата по определенному тесту
		// Одним запросом получаем и идентификатор заявки в ЛИС и перечень тестов с кодами
		$sampleinfo = $this->getSampleInfo($data);
		$lis_request_id = null;
		$defect = [];
		if (count($sampleinfo) > 0) {
			// Выбираем идентификатор заявки в лис
			$lis_request_id = $sampleinfo[0]["lis_id"];
			$defect = [$sampleinfo[0]["defect_id"]];
		}

		// Получаем из ЛИС по идентификатору заявки список проб
		$this->textlogAdd("у пробы записан в комментах lis_request_id: {$lis_request_id}");
		if ($lis_request_id) {
			if (!$this->isLogon()) {
				$this->login($data);
			}
			// Получаем список проб из ЛИС по номеру заявки
			$this->textlogAdd("Получаем результат по заявке с ID = {$lis_request_id}");
			$xml = $this->lis_request_samples($lis_request_id);
			$this->textlogAdd("Sample R request: {$xml}");
			$request_samples_response_xml = $this->request($xml);
			$this->textlogAdd("Sample R response: {$request_samples_response_xml}");
			// Разбираем ответ от ЛИС
			$request_samples_response = $this->lis_parse_responce_request_sample($request_samples_response_xml);
			$xmlsamples = [];
			// Достаем идентификаторы проб из ответа
			foreach ($request_samples_response as $row) {
				if (!isset($row["id"])) {
					throw new Exception("Получение проб: в ответе от ЛИС у пробы не указан идентификатор. Идентификатор заявки для анализатора: {$lis_request_id}");
				}
				$xmlsamples[$row["id"]] = $this->lis_sample_info($row["id"]);
			}
			// собираем в массив нужные данные - значение анализа, верхние/нижние границы, ед. изм.
			// Каждый наш тест стыкуем с тестом в ЛИС и получаем необходимую информацию о выполнении теста
			foreach ($xmlsamples as $sampleid => $xmlsample) { // по каждой пробе в заявке ЛИС получаем перечень выполненных тестов
				$response_xml = $this->request($xmlsample);
				$this->textlogAdd("Запрос по определенной пробе: " . $response_xml);
				// Разбираем ответ по пробе
				$response = $this->lis_parse_responce_sample_info($response_xml);
				$funcParams = [
					"id" => $response["id"],
					"fields" => [
						"comment" => "",
						"commentModified" => false,
						"works" => $response["works"],
						"addedDefects" => $defect,
						"removedDefects" => $response["defectTypes"],
						"departmentNr" => ""
					]
				];
				$this->lis_save_sample($funcParams);
			}
		}
	}

	/**
	 * Синхронизирует справочник дефектов
	 * @param $data
	 */
	function syncDefectCauseTypeSpr($data)
	{
		// получаем все данные из справочника лис
		$query = "
			select id, code, name
			from lis._defectType
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_lis
		 * @var CI_DB_result $result_upd
		 */
		$result = $this->db->query($query);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $oneresp) {
				$DefectCauseType_id = null;
				// ищем запись с таким id в lis.Link
				$query = "
					select l.object_id
					from
						lis.Link l
						inner join lis.v_DefectCauseType dct on dct.DefectCauseType_id = l.object_id
					where l.link_object = 'DefectCauseType'
					  and l.lis_id = :lis_id
				";
				$result_lis = $this->db->query($query, ["lis_id" => $oneresp["id"]]);
				if (is_object($result_lis)) {
					$resp_lis = $result_lis->result_array();
					if (!empty($resp_lis[0]["object_id"])) {
						$DefectCauseType_id = $resp_lis[0]["object_id"];
					}
				}
				$procedure = (!empty($DefectCauseType_id)) ? "lis.p_DefectCauseType_upd" : "lis.p_DefectCauseType_ins";
				$selectString = "
					DefectCauseType_id as \"DefectCauseType_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				";
				$query = "
					select {$selectString}
					from {$procedure} (
						DefectCauseType_id := :DefectCauseType_id,
						DefectCauseType_Code := :DefectCauseType_Code,
						DefectCauseType_Name := :DefectCauseType_Name,
						pmUser_id := :pmUser_id
					)
				";
				$queryParams = [
					"DefectCauseType_id" => $DefectCauseType_id,
					"DefectCauseType_Code" => $oneresp["code"],
					"DefectCauseType_Name" => $oneresp["name"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$result_upd = $this->db->query($query, $queryParams);
				if (is_object($result_upd)) {
					$resp_upd = $result_upd->result_array();
					if (!empty($resp_upd[0]["DefectCauseType_id"]) && empty ($DefectCauseType_id)) {
						$query = "
							select
								Link_id as \"Link_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from lis.p_Link_ins (
								Link_id := Link_id,
								link_object := :object_name,
								object_id := :object_id,
								lis_id := :lis_id,
								pmUser_id := :pmUser_id
							)
						";
						$queryParams = [
							"object_name" => "DefectCauseType",
							"object_id" => $resp_upd[0]["DefectCauseType_id"],
							"lis_id" => $oneresp["id"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$this->db->query($query, $queryParams);
					}
				}
			}
		}
	}

	/**
	 * Cохраняет результаты полученные с анализатора в EvnLabSample
	 * Необходимо наличие в сессии идентификатора заявки для анализатора (lis_request_id)
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function sample($data)
	{
		$this->load->model("EvnLabSample_model", "EvnLabSample_model");
		$this->load->model("EvnLabRequest_model", "EvnLabRequest_model");
		$this->load->model("Analyzer_model", "Analyzer_model");

		// Алгоритм работы функции проверки результата по определенному тесту
		// Одним запросом получаем и идентификатор заявки в ЛИС и перечень тестов с кодами
		$tests = $this->getSampleTests($data);
		$lis_request_id = null;
		if (count($tests) > 0) {
			// Выбираем идентификатор заявки в лис
			$lis_request_id = $tests[0]["lis_id"];
		}
		// формируем массив
		$linktests = [];
		foreach ($tests as $test) {
			$linktests[$test["test_id"]] = ["UslugaComplex_id" => $test["UslugaComplex_id"], "UslugaTest_id" => $test["UslugaTest_id"]];
		}
		$sampleNum = (isset($tests[0]) && isset($tests[0]["EvnLabSample_Num"])) ? $tests[0]["EvnLabSample_Num"] : "[не определен]";
		// Получаем из ЛИС по идентификатору заявки список проб
		$this->textlogAdd("у пробы записан в комментах lis_request_id: {$lis_request_id}");
		if (!$lis_request_id) {
			throw new Exception("Выбранная проба №{$sampleNum} еще не была отправлена");
		}
		if (!$this->isLogon()) {
			$this->login($data);
		}
		// Получаем список проб из ЛИС по номеру заявки
		$this->textlogAdd("Получаем результат по заявке с ID = {$lis_request_id}");
		$xml = $this->lis_request_samples($lis_request_id);
		$this->textlogAdd("Sample R request: {$xml}");
		$request_samples_response_xml = $this->request($xml);
		$this->textlogAdd("Sample R response: {$request_samples_response_xml}");
		// Разбираем ответ от ЛИС
		$request_samples_response = $this->lis_parse_responce_request_sample($request_samples_response_xml);
		$xmlsamples = [];
		// Достаем идентификаторы проб из ответа
		foreach ($request_samples_response as $row) {
			if (!isset($row["id"])) {
				throw new Exception("Получение проб: в ответе от ЛИС у пробы не указан идентификатор. Идентификатор заявки для анализатора: {$lis_request_id}");
			}
			// И составляем запрос на получение данных о пробе по идентификатору пробы
			$xmlsamples[$row["id"]] = $this->lis_sample_info($row["id"]);
		}
		// признак наличия результата
		$countResult = 0;
		// Каждый наш тест стыкуем с тестом в ЛИС и получаем необходимую информацию о выполнении теста
		foreach ($xmlsamples as $sampleid => $xmlsample) { // по каждой пробе в заявке ЛИС получаем перечень выполненных тестов
			$response_xml = $this->request($xmlsample);
			$this->textlogAdd("Запрос по определенной пробе: {$response_xml}");
			// Разбираем ответ по пробе
			$response = $this->lis_parse_responce_sample_info($response_xml);
			foreach ($response["works"] as $work) {
				// $test_id = $work["test"];
				if (isset($linktests[$work["test"]])) { // если из ЛИС вернулся такой же тест, как есть у нас в Промеде
					$test = $linktests[$work["test"]];
					// собираем результат выполнения теста
					// 1. нормы теста
					$RefValues_id = null;
					$UslugaTest_ResultLower = null;
					$UslugaTest_ResultUpper = null;
					if (isset($work["storedNorms"])) {
						$t1 = explode("-", $work["storedNorms"]);
						if (count($t1) === 2) {
							$UslugaTest_ResultLower = trim($t1[0]);
							$UslugaTest_ResultUpper = trim($t1[1]);
						}
					}
					// 2. значение теста
					if (!isset($work["value"])) {
						$work["value"] = null;
					} else {
						// если приходит число, то сохраняем как число несмотря на наличие первых нулей
						if (is_numeric($work["value"])) {
							$work["value"] = 0 + $work["value"];
						}
					}
					// 3. единица измерения
					if (!isset($work["storedUnit"])) {
						$work["storedUnit"] = null;
					}
					$this->textlogAdd("UslugaTest_id-" . $test["UslugaTest_id"]);
					$this->textlogAdd("UslugaComplex_id-" . $test["UslugaComplex_id"]);
					if (!empty($test["UslugaTest_id"])) {
						if (isset($work["value"]) && strlen($work["value"]) > 0) {
							$countResult++;

							$withRefs = false;
							if (!empty($UslugaTest_ResultLower) || !empty($UslugaTest_ResultUpper)) {
								// 1. ищем данный тест на службе
								$query = "
									select at_child.AnalyzerTest_id as \"AnalyzerTest_id\"
									from
										v_UslugaTest ut
										inner join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
										inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
										inner join lis.v_AnalyzerTest at_child on at_child.UslugaComplex_id = ut.UslugaComplex_id and at_child.Analyzer_id = els.Analyzer_id
										inner join lis.v_AnalyzerTest at_parent on at_parent.AnalyzerTest_id = at_child.AnalyzerTest_pid and at_parent.UslugaComplex_id = elr.UslugaComplex_id
									where ut.UslugaTest_id = :UslugaTest_id
									limit 1
								";
								$data["AnalyzerTest_id"] = $this->getFirstResultFromQuery($query, $test);
								if (!empty($data['AnalyzerTest_id'])) {
									// 2. если нашли тест, то проверяем, есть ли у него в промеде референсные значения
									$query = "
										select atrv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\"
										from lis.v_AnalyzerTestRefValues atrv
										where atrv.AnalyzerTest_id = :AnalyzerTest_id
										limit 1
									";
									$AnalyzerTestRefValues_id = $this->getFirstResultFromQuery($query, ["AnalyzerTest_id" => $data["AnalyzerTest_id"]]);
									if (empty($AnalyzerTestRefValues_id)) {
										// 3. если референсных значений нет, то надо подгрузить из ЛИС.
										$this->Analyzer_model->copyRefValuesFromLis($data, $work["test"]);
										// 4. ищем указанное реф. значение в промеде
										$query = "
											select atrv.RefValues_id
											from
												lis.v_AnalyzerTestRefValues atrv
												inner join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id
											where atrv.AnalyzerTest_id = :AnalyzerTest_id
											  and RefValues_LowerLimit = :RefValues_LowerLimit
											  and RefValues_UpperLimit = :RefValues_UpperLimit
											limit 1
										";
										$queryParams = [
											"AnalyzerTest_id" => $data["AnalyzerTest_id"],
											"RefValues_LowerLimit" => $UslugaTest_ResultLower,
											"RefValues_UpperLimit" => $UslugaTest_ResultUpper
										];
										$RefValues_id = $this->getFirstResultFromQuery($query, $queryParams);
										if (empty($RefValues_id)) {
											$RefValues_id = null;
										}
										$withRefs = true;
									}
								}
							}
							// обновляем UslugaTest
							$funcParams = ($withRefs)
								? [
									"disableRecache" => true,
									"UslugaTest_id" => $test["UslugaTest_id"],
									"UslugaComplex_id" => $test["UslugaComplex_id"],
									"UslugaTest_ResultValue" => $work["value"],
									"RefValues_id" => $RefValues_id,
									"UslugaTest_ResultLower" => $UslugaTest_ResultLower,
									"UslugaTest_ResultUpper" => $UslugaTest_ResultUpper,
									"UslugaTest_ResultUnit" => $work["storedUnit"],
									"updateType" => "fromLISwithRefValues",
									"session" => $data["session"],
									"pmUser_id" => $data["pmUser_id"]
								]
								: [
									"disableRecache" => true,
									"UslugaTest_id" => $test["UslugaTest_id"],
									"UslugaComplex_id" => $test["UslugaComplex_id"],
									"UslugaTest_ResultValue" => $work["value"],
									"updateType" => "fromLIS",
									"session" => $data["session"],
									"pmUser_id" => $data["pmUser_id"]
								];
							$this->EvnLabSample_model->updateResult($funcParams);
						}
					}
				}
			}
		}
		if ($countResult < 1) { // не пришло ни одного результата из ЛИС
			$this->textlogAdd("результаты не пришли");
			throw new Exception("Результаты по пробе №{$sampleNum} в ЛИС не заполнены");
		}
		// апдейтим дату в пробе EvnLabSample_StudyDT = dbo.tzGetDate()
		$this->textlogAdd("результаты пришли (" . $countResult . " штук), обновляем дату в пробе, кэшируем статус");
		$query = "
			update EvnLabSample
			set EvnLabSample_StudyDT = dbo.tzGetDate()
			where evn_id = :EvnLabSample_id						
		";
		$queryParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$this->db->query($query, $queryParams);
		$this->EvnLabSample_model->ReCacheLabSampleIsOutNorm($queryParams);
		$this->EvnLabSample_model->ReCacheLabSampleStatus($queryParams);
		$data["EvnLabRequest_id"] = $this->getFirstResultFromQuery("select EvnLabRequest_id from v_EvnLabSample where EvnLabSample_id = :EvnLabSample_id limit 1", $queryParams);
		if (!empty($data["EvnLabRequest_id"])) {
			// кэшируем статус заявки
			$queryParams = [
				"EvnLabRequest_id" => $data["EvnLabRequest_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$this->EvnLabRequest_model->ReCacheLabRequestStatus($queryParams);
			// кэшируем статус проб в заявке
			$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType($queryParams);
		}
		$this->textlogAdd("обновили дату, закэшировали статус");
		return [["Error_Msg" => ""]];
	}

	/**
	 * Отправляет одну запись в ЛИС и возврашает ответ по одной записи
	 * @param $data
	 * @param bool $silent
	 * @return array|bool|mixed|string
	 * @throws Exception
	 */
	function createRequest2($data, $silent = false)
	{
		$this->load->model("EvnLabSample_model", "EvnLabSample_model");
		$response = $this->EvnLabSample_model->getRequest2Data($data);
		if (empty($response["Analyzer_id"])) {
			// не задан анализатор
			if ($silent) {
				throw new Exception("Для отправки пробы на анлизатор необходимо указать анализатор в пробе.");
			}
		}
		if (empty($response["EvnLabSample_setDT"])) {
			// не задана дата взятия пробы
			if ($silent) {
				throw new Exception("<b>Выбранная проба не содержит данных о составе пробы.</b><br/>Откройте пробу и заполните информацию о взятии пробы.");
			}
		}
		if (empty($response["target_id"])) {
			// не определенно исследование в ЛИС
			if ($silent) {
				throw new Exception("<b>По выбранной комплексной услуге невозможно определить <br/>исследование в ЛИС.</b><br/>Код комплексной услуги в ПромедВеб должен соответствовать <br/>кодификации номенклатуры ГОСТ-2011.");
			}
		}
		if (empty($response["biomaterial_id"])) {
			// не определенно исследование в ЛИС
			if ($silent) {
				throw new Exception("<b>Невозможно определить биоматериал в пробе.</b><br/>Проверьте наличие в пробе биоматериала.");
			}
		}
		if (empty($response["EvnLabSample_DelivDT"])) {
			// проставляем дату доставки пробы
			$this->EvnLabSample_model->setDelivDT($data);
		}
		$targets = [$response["target_id"] => ["cancel" => false, "readonly" => false]];
		if (!empty($response["test_ids"])) {
			// отсекаем лишнюю запятую и раскладываем ответ в массив (хотя в принципе данные можно получать отдельным запросом сразу в массив: это на подумать)
			$response["test_ids"] = substr($response["test_ids"], 0, -1);
			$tests = explode(", ", $response["test_ids"]);
			// согласно протоколу клиент-серверного взаимодействия с ЛИС добавляем tests в targets
			$targets[$response["target_id"]]["tests"] = $tests;
		}
		$dt = date("d.m.Y H:i:s");
		if (isset($data["session"]) && (!isset($data["session"]["setting"]))) {
			// настроек в сессии почему-то нет 
			log_message("error", "Нет настроек в данных сессии: " . var_export($data["session"], true));
		}
		$registrationFormCode = "15";
		$req = [
			"o" => [
				"registrationFormCode" => $registrationFormCode,
				"hospitalCode" => $data["Lpu_id"],
				"hospitalName" => (isset($data["session"]) && isset($data["session"]["setting"]) && isset($data["session"]["setting"]["server"]["lpu_nick"])) ? toAnsi($data["session"]["setting"]["server"]["lpu_nick"]) : "",
				"internalNr" => $response["EvnLabSample_Num"],
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
					"street" => $response["KLStreet_Name"],
					"building" => $response["Address_House"],
					"flat" => $response["Address_Flat"],
					"insuranceCompany" => $response["OrgSmo_Nick"],
					"policySeries" => $response["Polis_Ser"],
					"policyNumber" => $response["Polis_Num"]
				],
				"samples" => [
					[
						"biomaterial" => $response["biomaterial_id"],
						"internalNr" => (isset($response["EvnLabSample_Num"])) ? $response["EvnLabSample_Num"] : $data["EvnLabSample_id"],
						"targets" => $targets
					]
				]
			]
		];
		if (!$this->isLogon()) {
			$resp_login = $this->login($data, $silent);
			if (is_array($resp_login) && !empty($resp_login["Error_Msg"])) {
				return $resp_login;
			}
		}
		$xml = $this->lis_create_requests_2($req, true, $silent);
		if (is_array($xml) && !empty($xml["Error_Msg"])) {
			return $xml;
		}
		$this->textlogAdd($xml);
		$response = $this->request($xml, null, $silent);
		if (is_array($response) && !empty($response["Error_Msg"])) {
			return $response;
		}
		$this->textlogAdd($response);
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
		$this->saveLink("EvnLabSample", $data["EvnLabSample_id"], $lis_request_id, $data);
		$this->EvnLabSample_model->ReCacheLabSampleStatus(["EvnLabSample_id" => $data["EvnLabSample_id"]]);
		$output = ["success" => true, "request" => $xml, "response" => $response, "lis_request_id" => $lis_request_id];
		array_walk_recursive($output, "ConvertFromWin1251ToUTF8", true);
		return $output;
	}

	/**
	 * Сохраняет пробу
	 * @param $sample
	 * @return string
	 * @throws Exception
	 */
	function lis_save_sample($sample)
	{
		$w = $this->xml_startXmlRequestWriterDocument("save-sample");
		$w->startElement("o");
		if (isset($sample["id"])) {
			$w->writeAttribute("id", $sample["id"]);
		}
		$w->writeAttribute("n", "sample");
		$this->xml_add_f($w, "comment", $sample["fields"]["comment"], false);
		$this->xml_add_f($w, "commentModified", $sample["fields"]["commentModified"], false);
		$w->startElement("s");
		$w->writeAttribute("n", "works");
		foreach ($sample["fields"]["works"] as $id => $work) {
			$w->startElement("o");
			if (isset($work["id"])) {
				$w->writeAttribute("id", $work["id"]);
			}
			$w->startElement("r");
			$w->writeAttribute("n", "test");
			$w->writeAttribute("i", $work["fields"]["test"]);
			$w->endElement();//r
			$this->xml_add_f($w, "state", $work["fields"]["state"], false);
			$this->xml_add_f($w, "value", $work["fields"]["value"], false);
			$this->xml_add_f($w, "comment", $work["fields"]["comment"], false);
			$this->xml_add_f($w, "dilution", $work["fields"]["dilution"], false);
			$this->xml_add_s($w, "images", $work["fields"]["images"], "s");
			$w->endElement();//o
		}
		$w->endElement();//s
		$this->xml_add_s($w, "addedDefects", $sample["fields"]["addedDefects"]);
		$this->xml_add_s($w, "removedDefects", $sample["fields"]["removedDefects"]);
		$this->xml_add_f($w, "departmentNr", $sample["fields"]["departmentNr"], false);
		$w->endElement();//o
		return $this->xml_endXmlRequestWriterDocument($w);
	}


	/**
	 * Формирует XML-запрос для создания заявок (create-requests-2) из массива установленного образца. При ошибке в структуре массива вываливает исключение.
	 * Переменные в массиве должны быть строго типизированы, даты должны быть объектами класса Datetime.
	 * По умолчанию предполагается, что строки во входном массиве $request_data закодированы в Windows-1251.
	 * Если данные закодированы в utf-8, необходимо передать $isInWindows1251 = false.
	 *
	 * @param $request_data
	 * @param bool $isInWindows1251
	 * @param bool $silent
	 * @return string
	 * @throws Exception
	 */
	function lis_create_requests_2($request_data, $isInWindows1251 = true, $silent = false)
	{
		if (!is_array($request_data)) {
			throw new Exception("Неправильный параметр: ожидается массив");
		}
		if ($isInWindows1251) {
			array_walk_recursive($request_data, "ConvertFromWin1251ToUTF8");
		}
		$w = $this->xml_startXmlRequestWriterDocument("create-requests-2", $silent);
		if (is_array($w) && !empty($w["Error_Msg"])) {
			return $w;
		}
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
		$fields = [
			"custDepartmentCode" => "custDepartmentName",
			"doctorCode" => "doctorName"
		];
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
					throw new Exception("Элемент \"samples[" . $key . "]\" не является массивом");
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
					throw new Exception("Элемент \"samples[" . $key . "]\" не содержит обязательный параметр: Исследования, которые необходимо выполнить для данной пробы (targets)");
				}
				if (!is_array($sample["targets"])) {
					throw new Exception("Элемент \"samples[" . $key . "][targets]\" не является массивом");
				}
				$w->startElement("s");
				$w->writeAttribute("n", "targets");
				foreach ($sample["targets"] as $target_idx => $target) {
					if (!is_array($target)) {
						throw new Exception("Элемент \"samples[" . $key . "][targets][" . $target_idx . "]\" не является массивом");
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
							throw new Exception("Элемент \"samples[" . $key . "][targets][" . $target_idx . "][tests]\" не является массивом");
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
		$w->endElement();//o
		$w->endDocument();
		$result = $this->xml_endXmlRequestWriterDocument($w);
		return $result;
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
					$w->startElement("o");
					$w->writeAttribute("n", $key);
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
	 * Функция отправки серверу xml-запроса и получения от него ответа
	 * @param $xml
	 * @param null $server
	 * @param bool $silent
	 * @return mixed
	 */
	function request($xml, $server = null, $silent = false)
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
			if ($silent) {
				return ["Error_Msg" => curl_error($ch)];
			}
			DieWithError(curl_error($ch));
		}
		return $result;
	}

	/**
	 * @param $m
	 * @return mixed
	 */
	function toRec($m)
	{
		foreach ($m as $k => $v) {
			if (is_array($v)) {
				$m[$k] = (isset($v["i"])) ? $v["i"] : $this->toRec($v);
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
		return ($tojson) ? json_encode($r["content"]["o"]) : $r["content"]["o"];
	}


	/**
	 * Запрос login. Авторизация пользователя в системе и получение пользователем уникального идентификатора сессии
	 * @param null $data
	 * @param bool $silent
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function login($data = null, $silent = false)
	{
		$this->textlogAdd("login: Запуск");
		$request = (isset($data) && isset($data["login"]) && isset($data["password"]) && isset($data["clientId"]))
			? [
				"login" => $data["login"],
				"password" => md5($data["password"]),
				"clientId" => $data["clientId"],
				"company" => "company",
				"lab" => "lab",
				"machine" => "machine_" . $data["login"],
				"instanceCount" => 0,
				"sessionCode" => rand(10000, 20000)
			]
			: $this->getLisRequestData($data);
		if ($request == false) {
			throw new Exception(toUtf("Не заполнены параметры пользователя ЛИС! <br/>Перед работой с ЛИС заполните пожалуйста настройки пользователя."));
		}
		$this->textlogAdd("login: Формируем запрос в XML");
		$xml = $this->setXmlRequest("login", $request);
		$this->textlogAdd("login: Параметры: " . var_export($this->server, true));
		$this->textlogAdd("login: Запрос: {$xml}");
		$response = $this->request($xml, null, $silent);
		if (is_array($response) && !empty($response["Error_Msg"])) {
			return $response;
		}
		$this->textlogAdd("login: Ответ: {$response}");
		if ($response == false) {
			$this->textlogAdd("login: Ответ пустой!");
			$this->textlogAdd("login: {$xml}");
			throw new Exception(toUtf("Не заполнены параметры пользователя ЛИС! <br/>Перед работой с ЛИС заполните пожалуйста настройки пользователя."));
		}
		$this->textlogAdd("login: Ответ не пустой ");
		$arr = $this->getXmlResponse($response, false);
		$this->textlogAdd("login: Распарсили ответ");
		// Обрабатываем ответ
		if ($arr["success"] === true) {
			$this->textlogAdd("login: Ответ хороший, синхронизировали сессии");
			// При успешной авторизации сохраняем данные для авторизации (getLisRequestData) в сессии
			$_SESSION["lisrequestdata"] = $request;
			// При успешной авторизации сохраняя связь с сессией
			$_SESSION["phox"] = [];
			$_SESSION["phox"]["sessionid"] = $arr["sessionid"];
			$this->sessionid = $_SESSION["phox"]["sessionid"];
		} else {
			$this->textlogAdd("login: Ответ плохой, сессию удалили");
			$this->textlogAdd("login: Ошибка {$arr["Error_Code"]}" . toAnsi($arr["Error_Msg"]));
			$_SESSION["phox"] = [];
		}
		// Вывод только, если установлен признак дебага
		if (isset($this->debug) && $this->debug && (isset($arr))) {
			var_dump($arr);
		}
		$this->textlogAdd("login: Финиш");
		return true;
	}

	/**
	 * Проверка, залогинен ли пользователь на удаленном сервисе (проверка на наличие открытой сессии)
	 * @return bool
	 */
	function isLogon()
	{
		$result = (isset($_SESSION["phox"]) && (isset($_SESSION["phox"]["sessionid"])));
		$this->textlogAdd("isLogon: Проверка залогинен ли пользователь  в ЛИС: {$result}");
		return $result;
	}

	/**
	 * getTablesSql
	 */
	function getTablesSql()
	{
		$tpl = "
				/*CREATE TABLE [lis].[{[_TABLE_NAME_]}]
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
			GO			   */
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
		$tables = "SELECT name FROM sys.all_objects WHERE type = 'U' AND schema_id = (SELECT schema_id FROM sys.schemas WHERE name = 'lis')";
		$tables = $this->db->query($tables);
		if (is_object($tables)) {
			$tables = $tables->result('array');
		} else {
			$tables = array();
		}
		$alredyExistingTables = array();
		foreach ($tables as $value) {
			$alredyExistingTables[] = strtolower($value['name']);
		}
		foreach ($this->map as $mapping) {
			//if (!in_array(strtolower($mapping['table']),$alredyExistingTables)) {
			$sql = str_replace('{[_TABLE_NAME_]}', $mapping['table'], $tpl);
			return $sql;
			//}
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getLisRequestData($data)
	{
		$this->load->model("LisUser_model", "usermodel");
		if (isset($data["session"]["lisrequestdata"])) {
			// если в сессии сохранены данные успешной авторизации то возвращаем их
			//return $data["session"]["lisrequestdata"];
		} else {
			// иначе читаем из lis.User
			return $this->usermodel->getLisRequestData($data);

		}
		return true;
	}


	/**
	 * Запрос worklist-save, сохраняет изменения в рабочий список
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function worklistSave($data)
	{
		$this->load->model("EvnLabSample_model", "EvnLabSample_model");
		// список проб пришедший в $data["samples"] надо разобрать, и выбрать из них lis_id
		$samples = "";
		if (isset($data["samples"]) && is_array($data["samples"]) && (count($data["samples"]) > 0)) {
			foreach ($data["samples"] as $sample) {
				if (isset($sample["lis_id"]) && ($sample["lis_id"] > 0)) {
					$samples .= "<r i=\"{$sample["lis_id"]}\"/>";
				}
			}
		}
		if (strlen($samples) == 0) { // нет проб в рабочем списке
			DieWithError("Нет ни одной сохраненной (в ЛИС) пробы в выбранном рабочем списке.<br/>Отправка рабочего списка в анализатор невозможна.");
		}
		// создаем XML-ку для отправки рабочего списка
		$textxml = '
			<content>
				<o id="" n="worklist">
					<r n="worklistDef" i="{$worklistDef}"/>
					<r n="rack" i="{$rack}"/>
					<f n="code" v="t1"/>
					<s n="positions"/>
					<o n="addSamples">
						<f n="startX" v="0"/>
						<f n="startY" v="0"/>
						<s n="samples">
							{$samples}
						</s>
					</o>
					<f n="methodName" v=""/>
					<f n="expireDate" v="{$expireDate}"/>
					<f n="calcResult1" v=""/>
					<f n="calcResult2" v=""/>
					<f n="comment1" v=""/>
					<f n="comment2" v=""/>
					<s n="userValues"/>
					<s n="tests"/>
					<f n="skipTests" v="true"/>
					<f n="sendRemote" v="{$sendRemote}"/>
				</o>
			</content>
		';
		if (!$this->isLogon()) {
			$this->login($data);
		}
		$xml = $this->createXmlRequestWithText($textxml, "");
		$response = $this->request($xml);
		$this->textlogAdd($response);
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
		$this->saveLink("EvnLabSample", $data["EvnLabSample_id"], $lis_request_id, $data);
		$this->EvnLabSample_model->ReCacheLabSampleStatus(["EvnLabSample_id" => $data["EvnLabSample_id"]]);
		$output = ["success" => true, "request" => $xml, "response" => $response, "lis_request_id" => $lis_request_id];
		array_walk_recursive($output, "ConvertFromWin1251ToUTF8");
		return $output;
	}

	/**
	 * Запрос worklist-change-state, изменяет статус рабочего списка
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function worklistChangeState($data)
	{
		$lis_id = $this->loadLinkId("AnalyzerWorksheet", $data["AnalyzerWorksheet_id"]);
		if (!isset($lis_id)) {
			return true;
		}
		$textxml = "
			<content>
				<s n=\"worklists\">
					<r i=\"{$lis_id}\"/>
				</s>
				<f n=\"state\" v=\"{$data["AnalyzerWorksheetStatusType_id"]}\"/>
			</content>
		";
		if (!$this->isLogon()) {
			$this->login($data);
		}
		$xml = $this->createXmlRequestWithText($textxml, "");
		$this->request($xml);
		return true;
	}
}