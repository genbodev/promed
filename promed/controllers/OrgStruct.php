<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* OrgStruct - контроллер для работы с организационной структурой
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2010-2012 Swan Ltd.
* @author		Dmitry Vlasenko aka DimICE (dimice@dimice.ru)
* @version		07.12.2012
*/
class OrgStruct extends swController {
	public $inputRules = array(
		'saveOrgFilial' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgFilial_id',
				'label' => 'Идентификатор филиала',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgFilial_oldid',
				'label' => 'Идентификатор предыдущего филиала',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgRSchetGrid' => array (
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgHeadGrid' => array (
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgLicenceGrid' => array (
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgFilialGrid' => array (
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgStructureTree' => array(
			array(
				'default' => 0,
				'field' => 'level',
				'label' => 'Уровень',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_pid',
				'label' => 'Структурный уровень',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgStructGrid' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_pid',
				'label' => 'Структурный уровень',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadMedServiceGrid' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_pid',
				'label' => 'Структурный уровень',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgServiceTypeGrid' => array(
			array(
				'field' => 'OrgType_id',
				'label' => 'Тип организации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getAllowedMedServiceTypes' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			)		
		),
		'loadOrgStructLevelTypeList' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStructLevelType_LevelNumber',
				'label' => 'Номер уровня',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OrgStructLevelType_id',
				'label' => 'Тип структурного уровня',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgStructLevelTypeGrid' => array(
			array(
				'field' => 'OrgType_id',
				'label' => 'Тип организации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadOrgTypeGrid' => array(
		
		),
		'loadOrgStructLevelTypeEditForm' => array(
			array(
				'field' => 'OrgStructLevelType_id',
				'label' => 'Тип структурного уровня',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadOrgTypeEditForm' => array(
			array(
				'field' => 'OrgType_id',
				'label' => 'Тип структурного уровня',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveOrgType' => array(
			array(
				'field' => 'OrgType_id',
				'label' => 'Тип организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgType_id',
				'label' => 'Тип структурного уровня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgType_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'OrgType_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgType_Nick',
				'label' => 'Краткое наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgType_SysNick',
				'label' => 'Системное наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgType_begDT',
				'label' => 'Дата открытия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'OrgType_endDT',
				'label' => 'Дата закрытия',
				'rules' => '',
				'type' => 'date'
			)
		),
		'saveOrgStructLevelType' => array(
			array(
				'field' => 'OrgType_id',
				'label' => 'Тип организации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStructLevelType_id',
				'label' => 'Тип структурного уровня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStructLevelType_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'OrgStructLevelType_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgStructLevelType_Nick',
				'label' => 'Краткое наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgStructLevelType_SysNick',
				'label' => 'Системное наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgStructLevelType_LevelNumber',
				'label' => 'Номер уровня',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'OrgStructLevelType_begDT',
				'label' => 'Дата открытия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'OrgStructLevelType_endDT',
				'label' => 'Дата закрытия',
				'rules' => '',
				'type' => 'date'
			)
		),
		'loadOrgServiceTypeEditForm' => array(
			array(
				'field' => 'OrgServiceType_id',
				'label' => 'Тип структурного уровня',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveOrgServiceType' => array(
			array(
				'field' => 'OrgType_id',
				'label' => 'Тип организации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedServiceType_id',
				'label' => 'Тип службы MedService',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgServiceType_id',
				'label' => 'Тип службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgServiceType_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'OrgServiceType_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgServiceType_Nick',
				'label' => 'Краткое наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgServiceType_SysNick',
				'label' => 'Системное наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgServiceType_begDT',
				'label' => 'Дата открытия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'OrgServiceType_endDT',
				'label' => 'Дата закрытия',
				'rules' => '',
				'type' => 'date'
			)
		),
		'deleteOrgServiceType' => array(
			array(
				'field' => 'OrgServiceType_id',
				'label' => 'Идентификатор типа службы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteOrgFilial' => array(
			array(
				'field' => 'OrgFilial_id',
				'label' => 'Идентификатор филиала',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteOrgType' => array(
			array(
				'field' => 'OrgType_id',
				'label' => 'Идентификатор типа службы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteOrgStructLevelType' => array(
			array(
				'field' => 'OrgStructLevelType_id',
				'label' => 'Идентификатор типа структурного уровня',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadOrgStructEditForm' => array(
			array(
				'field' => 'OrgStruct_id',
				'label' => 'Структурный уровень',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadOrgHeadEditForm' => array(
			array(
				'field' => 'OrgHead_id',
				'label' => 'Контактное лицо',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadOrgLicenceEditForm' => array(
			array(
				'field' => 'OrgLicence_id',
				'label' => 'Лицензия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveOrgLicence' => array(
			array(
				'field' => 'OrgLicence_id',
				'label' => 'Лицензия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Организация выдавшая лицензию',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgLicence_Ser',
				'label' => 'Серия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgLicence_Num',
				'label' => 'Номер',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgLicence_RegNum',
				'label' => 'Регистрационный номер',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgLicence_setDate',
				'label' => 'Дата выдачи',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'OrgLicence_begDate',
				'label' => 'Начало действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'OrgLicence_endDate',
				'label' => 'Окончание действия',
				'rules' => '',
				'type' => 'date'
			)
		),
		'saveOrgHead' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgHead_id',
				'label' => 'Контактное лицо',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgHeadPost_id',
				'label' => 'Должность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgHead_Phone',
				'label' => 'Телефон',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgHead_Mobile',
				'label' => 'Мобильный телефон',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgHead_Fax',
				'label' => 'Факс',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgHead_Email',
				'label' => 'e-mail',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgHead_CommissDate',
				'label' => 'Дата назначения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'OrgHead_CommissNum',
				'label' => 'Номер приказа о назначении',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgHead_Address',
				'label' => 'Адрес, № рабочего кабинета',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveOrgStruct' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_pid',
				'label' => 'Родительский уровень',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_id',
				'label' => 'Структурный уровень',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgStruct_NumLevel',
				'label' => 'Код',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OrgStruct_LeftNum',
				'label' => 'Код',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OrgStruct_RightNum',
				'label' => 'Код',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OrgStruct_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'OrgStruct_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgStruct_Nick',
				'label' => 'Краткое наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'OrgStruct_begDT',
				'label' => 'Дата открытия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'OrgStruct_endDT',
				'label' => 'Дата закрытия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'OrgStructLevelType_id',
				'label' => 'Тип структурного уровня',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadOrgStructList' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);


	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('OrgStruct_model', 'dbmodel');
	}
	
	/**
	 * Читает список расчётных счетов организации
	 */	
	function loadOrgRSchetGrid() 
	{
		$data = $this->ProcessInputData('loadOrgRSchetGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgRSchetGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает список контактных лиц организации
	 */	
	function loadOrgHeadGrid() 
	{
		$data = $this->ProcessInputData('loadOrgHeadGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgHeadGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает список лицензий организации
	 */	
	function loadOrgLicenceGrid() 
	{
		$data = $this->ProcessInputData('loadOrgLicenceGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgLicenceGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает список филиалов организации
	 */	
	function loadOrgFilialGrid() 
	{
		$data = $this->ProcessInputData('loadOrgFilialGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgFilialGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение доступных типов служб для организации
	 */		
	function getAllowedMedServiceTypes() 
	{
        $data = $this->ProcessInputData('getAllowedMedServiceTypes',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->getAllowedMedServiceTypes($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Сохранение лицензии организации
	 */	
	function saveOrgLicence() 
	{
		$data = $this->ProcessInputData('saveOrgLicence', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->saveOrgLicence($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении лицензии')->ReturnData();
		return true;
	}
	
	/**
	 * Сохранение филиала организации
	 */	
	function saveOrgFilial() 
	{
		$data = $this->ProcessInputData('saveOrgFilial', true);
		if ( $data === false ) { return false; }

		// проверим не является ли выбранный филиал филиалом другой организации.
		if ($this->dbmodel->checkOrgFilialExist($data)) {
			$this->ReturnError('Выбранная организация уже является филиалом другой организации.');
			return false;
		} else {
			if (!empty($data['OrgFilial_oldid'])) {
				// освобождаем Org_pid предудщем филиале
				$params = array();
				$params['OrgFilial_id'] = $data['OrgFilial_oldid'];
				$params['Org_id'] = null;
				$this->dbmodel->saveOrgFilial($params);
			}
			$response = $this->dbmodel->saveOrgFilial($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении филиала')->ReturnData();
			return true;
		}
	}

	/**
	 * Сохранение контактного лица
	 */
	function saveOrgHead()
	{
		$data = $this->ProcessInputData('saveOrgHead', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveOrgHead($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении контактного лица')->ReturnData();
		return true;
	}
	
	/**
	 * Сохранение структурного уровня
	 */	
	function saveOrgStruct() 
	{
		$data = $this->ProcessInputData('saveOrgStruct', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveOrgStruct($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении структурного уровня')->ReturnData();
		return true;
	}

	/**
	 * Чтение формы контактного лица
	 */	
	function loadOrgHeadEditForm()
	{
        $data = $this->ProcessInputData('loadOrgHeadEditForm',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadOrgHeadEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Чтение формы лицензии организации
	 */
	function loadOrgLicenceEditForm() 
	{
        $data = $this->ProcessInputData('loadOrgLicenceEditForm',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadOrgLicenceEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Чтение формы структурного уровня
	 */		
	function loadOrgStructEditForm() 
	{
        $data = $this->ProcessInputData('loadOrgStructEditForm',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadOrgStructEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	*  Удаление типа структурного уровня
	*/
	function deleteOrgStructLevelType() 
	{
		$data = $this->ProcessInputData('deleteOrgStructLevelType', true);
		if ( $data === false ) { return false; }
		
		// проверяем а не используется ли уже
		if ($this->dbmodel->checkOrgStructLevelTypeIsUsed($data)) {
			$this->ReturnError('Нельзя удалить тип структурного уровня, т.к. он уже используется');
			return false;
		} else {
			$response = $this->dbmodel->deleteOrgStructLevelType($data);
			$this->ProcessModelSave($response, true, 'Ошибка удаления типа структурного уровня')->ReturnData();
			return true;
		}
	}
	
	/**
	*  Удаление филиала
	*/
	function deleteOrgFilial() 
	{
		$data = $this->ProcessInputData('deleteOrgFilial', true);
		if ( $data === false ) { return false; }
		
		$data['Org_id'] = null;
		$response = $this->dbmodel->saveOrgFilial($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления филиала')->ReturnData();
		return true;
	}
	
	/**
	*  Удаление типа службы
	*/
	function deleteOrgServiceType() 
	{
		$data = $this->ProcessInputData('deleteOrgServiceType', true);
		if ( $data === false ) { return false; }
		
		// проверяем а не используется ли уже
		if ($this->dbmodel->checkOrgServiceTypeIsUsed($data)) {
			$this->ReturnError('Нельзя удалить тип службы, т.к. он уже используется');		
			return false;
		} else {
			$response = $this->dbmodel->deleteOrgServiceType($data);
			$this->ProcessModelSave($response, true, 'Ошибка удаления типа службы')->ReturnData();
			return true;
		}
	}
	
	/**
	*  Удаление типа организации
	*/
	function deleteOrgType() {
		$data = $this->ProcessInputData('deleteOrgType', true);
		if ( $data === false ) { return false; }
		
		// проверяем а не используется ли уже
		if ($this->dbmodel->checkOrgTypeIsUsed($data)) {
			$this->ReturnError('Нельзя удалить тип организации, т.к. он уже используется');		
			return false;
		} else {
			$response = $this->dbmodel->deleteOrgType($data);
			$this->ProcessModelSave($response, true, 'Ошибка удаления типа организации')->ReturnData();
			return true;
		}
	}
	
	/**
	 * Чтение формы типа службы
	 */		
	function loadOrgServiceTypeEditForm() {
        $data = $this->ProcessInputData('loadOrgServiceTypeEditForm',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadOrgServiceTypeEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение типа службы
	 */	
	function saveOrgServiceType() {
		$data = $this->ProcessInputData('saveOrgServiceType', true);
		if ( $data === false ) { return false; }
		
		// проверка есть ли уже c таким кодом
		if ($this->dbmodel->checkCodeExist('OrgServiceType', $data)) {
			$this->ReturnError('Уже есть тип службы с данным кодом');
			return false;
		}

		$response = $this->dbmodel->saveOrgServiceType($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении типа службы')->ReturnData();
		return true;
	}
	
	/**
	 * Чтение формы типа организации
	 */		
	function loadOrgTypeEditForm() {
        $data = $this->ProcessInputData('loadOrgTypeEditForm',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadOrgTypeEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}
	
	/**
	 * Чтение формы типа структурного уровня
	 */		
	function loadOrgStructLevelTypeEditForm() {
        $data = $this->ProcessInputData('loadOrgStructLevelTypeEditForm',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadOrgStructLevelTypeEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение типа структурного уровня
	 */	
	function saveOrgStructLevelType() {
		$data = $this->ProcessInputData('saveOrgStructLevelType', true);
		if ( $data === false ) { return false; }
		
		// проверка есть ли уже c таким кодом
		if ($this->dbmodel->checkCodeExist('OrgStructLevelType', $data)) {
			$this->ReturnError('Уже есть тип структурного уровня с данным кодом');
			return false;
		}
		
		// проверка есть ли уже c таким номером уровня в текущем типе организации
		if ($this->dbmodel->checkOrgStructLevelTypeNumber($data)) {
			$this->ReturnError('Уже есть тип структурного уровня с данным номером уровня');
			return false;
		}

		$response = $this->dbmodel->saveOrgStructLevelType($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении типа структурного уровня')->ReturnData();
		return true;
	}
	
	/**
	 * Сохранение типа организации
	 */	
	function saveOrgType() {
		$data = $this->ProcessInputData('saveOrgType', true);
		if ( $data === false ) { return false; }

		$this->ReturnError('Нельзя редактировать тип организации'); // добавление/редактирование только через бд.
		return false;
			
		$response = $this->dbmodel->saveOrgType($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении типа организации')->ReturnData();
		return true;
	}
	
	/**
	 * Читает грид типов организаций
	 */	
	function loadOrgTypeGrid() {
		$data = $this->ProcessInputData('loadOrgTypeGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgTypeGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает комбо типов структурных уровней
	 */	
	function loadOrgStructLevelTypeList() {
		$data = $this->ProcessInputData('loadOrgStructLevelTypeList', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgStructLevelTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает грид типов структурных уровней
	 */	
	function loadOrgStructLevelTypeGrid() {
		$data = $this->ProcessInputData('loadOrgStructLevelTypeGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgStructLevelTypeGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает грид типов служб
	 */	
	function loadOrgServiceTypeGrid() {
		$data = $this->ProcessInputData('loadOrgServiceTypeGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgServiceTypeGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает грид структурных уровней
	 */	
	function loadOrgStructGrid() {
		$data = $this->ProcessInputData('loadOrgStructGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgStructGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Читает грид служб
	 */	
	function loadMedServiceGrid() {
		$data = $this->ProcessInputData('loadMedServiceGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadMedServiceGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Формирование элементов дерева из записей таблицы
	 */
	function getTreeNodes($nodes, $field, $level, $dop = "", $check = 0) {
		$val = array();
		$i = 0;

		if ( is_array($nodes) && count($nodes) > 0 ) {
			foreach ( $nodes as $rows ) {
				$node = array(
					'id' => $rows[$field['id']],
					'object' => $rows['object'],
					'object_id' => $field['id'],
					'object_value' => $rows[$field['id']],
					'object_code' => $rows[$field['code']],
					'text' => (!empty($rows[$field['code']]) ? $rows[$field['code']] . ' ' : '') . $rows[$field['name']],
					'leaf' => $rows['leaf'],
					'iconCls' => (empty($rows['iconCls']) ? $field['iconCls'] : $rows['iconCls']),
					'OrgStruct_NumLevel' => $rows['OrgStruct_NumLevel'],
					'cls' => $field['cls']
				);

				$val[] = $node;
			}
		}

		return $val;
	}

	/**
	 * Получение структуры
	 */
	function loadOrgStructureTree() 
	{
		$data = $this->ProcessInputData('loadOrgStructureTree', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadOrgStructureTree($data);
		$this->ProcessModelList($response, true, true);
		
		// Обработка для дерева 
		$field = array(
			'id' => 'id', 
			'name' => 'name',
			'code' => 'code',
			'iconCls' => 'folder16',
			'leaf' => false, 
			'cls' => 'folder'
		);

		$this->ReturnData($this->getTreeNodes($this->OutData, $field, $data['level'], ""));

		return true;
	}

	/**
	 * Получение списка структурных уровней организации
	 */
	function loadOrgStructList()
	{
		$data = $this->ProcessInputData('loadOrgStructList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadOrgStructList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}

?>