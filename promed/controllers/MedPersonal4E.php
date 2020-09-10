<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MedService - контроллер работы со службами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Common
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 *				Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		27.08.2009
 */


/**
 * Класс контроллера для операций с медицинским персоналом, местами работы
 *
 * @package		Common
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 *				Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 */
class MedPersonal4E extends swController {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		
        $this->load->database();
        $this->load->model('MedPersonal_model4E', 'dbmodel');
		
		$this->inputRules = array(
			'loadMedPersonalSearchList' => array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 100,
	                'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
	                'field' => 'Person_SurName',
					'label' => 'Фпмилия',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadMedPersonalSearchList_Ufa_Old_ERMP' => array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фпмилия',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadMedPersonal' => array(
				array(
	                'field' => 'MedPersonal_id',
					'label' => 'Идентификатор медперсонала',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'getMedStaffFactEditWindow_getDataForEdit' => array(
				array(
	                'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'getMedStaffFactEditWindow_edit' => array(
				array(
	                'field' => 'MedStaffFact_idEdit',
					'label' => 'Идентификатор места работы',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'getMedStaffFactEditWindow' => array(
				array(
	                'field' => 'action',
					'label' => 'Действие',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'getMedStaffFactEditWindow_save' => array(
				array(
	                'field' => 'LpuSection_idEdit',
					'label' => 'Отделение',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
	                'field' => 'LpuUnit_idEdit',
					'label' => 'Подразделение',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
	                'field' => 'MedPersonal_idEdit',
					'label' => 'Врач',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'MedStaffFact_IsSpecialistEdit',
					'label' => 'Признак специалиста',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'MedStaffFact_IsOMSEdit',
					'label' => 'Признак работы в системе ОМС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'MedSpecOms_id',
					'label' => 'Специальность S90',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 0,
	                'field' => 'MedStaffFact_StavkaEdit',
					'label' => 'Ставка',
					'rules' => 'trim|required',
					'type' => 'float'
				),
				array(
					'default' => 'NULL',
	                'field' => 'MedSpec_idEdit',
					'label' => 'Идентификатор специальности',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'PostMed_idEdit',
					'label' => 'Идентификатор должности',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'PostMedCat_idEdit',
					'label' => 'Идентификатор категории',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'PostMedClass_idEdit',
					'label' => 'Вид должности',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'PostMedType_idEdit',
					'label' => 'Тип должности',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'NULL',
	                'field' => 'MedStaffFact_setDateEdit',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'default' => 'NULL',
	                'field' => 'MedStaffFact_disDateEdit',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
	                'field' => 'RecType_id',
					'label' => 'Поле для ЭР',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
	                'field' => 'MedStaffFact_PriemTime',
					'label' => 'Поле для ЭР',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
	                'field' => 'MedStatus_id',
					'label' => 'Поле для ЭР',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
	                'field' => 'MedStaffFact_IsDirRec',
					'label' => 'Поле для ЭР',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'MedStaffFact_IsQueueOnFree',
					'label' => 'Поле для ЭР',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'MedStaffFact_Descr',
					'label' => 'Поле для ЭР',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'MedStaffFact_Contacts',
					'label' => 'Поле для ЭР',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'addMedPersonal' =>array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_Code',
					'label' => 'Код врача',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_TabCode',
					'label' => 'Табельный код врача',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'required|trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'required|trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'required|trim',
					'type' => 'date'
				),
				array(
					'field' => 'WorkData_begDate',
					'label' => 'Дата начала работы',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'WorkData_endDate',
					'label' => 'Дата окончания работы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Person_Snils',
					'label' => 'СНИЛС',
					'rules' => 'trim',
					'type' => 'snils'
				),
				array(
					'default' => 1,
					'field' => 'WorkData_IsDlo',
					'label' => 'Врач ЛЛО',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Действие',
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'editMedPersonal' =>array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор медперсонала',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'loadMedStaffFactList' =>array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'loadAdminPersonal',
					'label' => 'Флаг загруки руководящего состава',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedSpecOms_id',
					'label' => 'Идентификатор специальности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PostMedType_Code',
					'label' => 'Идентификатор специальности',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedPersonalList' =>array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'lpu_id' //параметр в сессии из которого можно взять значение если пришедшее значение пусто
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreWorkInLpu',
					'label' => 'Флаг ignoreWorkInLpu',
					'rules' => '',
					'type' => 'string'
				)
			),
			'searchDoctorByFioBirthday' => array(
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'required|trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'required|trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'required|trim',
					'type' => 'string'
				)
			),
			'getMedPersonInfo' =>array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'getMedStaffFactComment' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				)
			),
			'saveMedStaffFactComment' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				),
				array(
					'field' => 'MedStaffFact_Descr',
					'label' => 'Комментарий места работы врача',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getMedStaffFactDuration' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				)
			),
			'getMedPersonalCombo' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_ids',
					'label' => 'Идентификаторы организаций',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingType_id',
					'label' => 'Тип подразделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuLpuUnitType_Code',
					'label' => 'Тип группы отделений',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getMedPersonalWithLpuRegionCombo' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => 'Идентификатор участка',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadMedPersonalCombo' => array(
				array(
					'field' => 'IsDlo',
					'label' => 'Флаг IsDlo',
					'rules' => '',
					'type' => 'int'
				)
			),
			'dropMedStaffFact' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getStaffTTGridDetail' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedStaffGridDetail' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedStaffGridDetail_Ufa_Old_ERMP' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedPersonalGridDetail' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedPersonalGridPaged' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Search_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Search_FirName',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SecName',
					'label' => 'Фамилия',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SurName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'sort',
					'label' => 'Сортировка',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'dir',
					'label' => 'Направление сортировки',
					'rules' => 'trim',
					'type' => 'string'
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
	* Получает отделение, профиль отделения, должность текущего пользователя.
	* Входящие данные: $data['MedStaffFact_id']
	* Используется форма подтверждения госпитализации swHospDirectionConfirmWindow.js
	*/
	function getMedPersonInfo() {
		$data = $this->ProcessInputData('getMedPersonInfo', false, true);
		if ( $data === false ) { return false;	}
		
		$response = $this->dbmodel->getMedPersonInfo($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
	}
	
	/**
	* Поиск медперсонала по ФИО и ДР
	*/
	function searchDoctorByFioBirthday() {
		$data = $this->ProcessInputData('searchDoctorByFioBirthday', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->searchDoctorByFioBirthday($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	* Сохранение медперсонала
	*/
	function saveMedPersonal() {
		$data = $this->ProcessInputData('addMedPersonal', true);
		if ($data === false) { return false; }
		
		if ( $data['action'] == 'edit' )
		{
			$err = getInputParams($data, $this->inputRules['editMedPersonal'], false);
			if (strlen($err) > 0) 
			{
				echo json_return_errors($err);
				return false;
			}
		}
		
		$response = $this->dbmodel->saveMedPersonal($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Получение списка мест работы врача
	 * Используется: окно просмотра и редактирования мед. персонала.
	 */
	public function getMedStaffFactEditWindow() {
		$data = $this->ProcessInputData('getMedStaffFactEditWindow', true);
		if ($data === false) { return false; }
		
		$action = $data['action'];
		
		switch ($action) {
			case 'getDataForAdd':
				$val[] = array(
					'Lpu_idEdit'=>$data['session']['lpu_id']
				);
				$this->ReturnData($val);
				return true;
				break;
			case 'getDataForEdit':
				$data = $this->ProcessInputData('getMedStaffFactEditWindow_getDataForEdit', true);
				if ($data === false) { return false; }
				
				$info = $this->dbmodel->getMedStaffFactEditWindow($data);
				$this->ProcessModelList($info, true, true)->ReturnData();
				return true;
				break;
			case 'add':				
				$val = array("success"=>true);

				$data = $this->ProcessInputData('getMedStaffFactEditWindow_save', true);
				if ($data === false) { return false; }
                $data=array_merge($data, getSessionParams());
				$info = $this->dbmodel->checkIfLpuSectionExists($data);
				if ( $info == true )
				{
					echo json_return_errors("Этот врач уже привязан к этому отделению.", true);
					return false;
				}
				$info = $this->dbmodel->insertMedStaffFact($data);
				$this->ReturnData($val);
				break;
			case 'addinstructure':
				$val = array("success"=>true);

				$data = $this->ProcessInputData('getMedStaffFactEditWindow_save', true);
				if ($data === false) { return false; }
				
                $data=array_merge($data, getSessionParams());
				$info = $this->dbmodel->checkIfLpuSectionExists($data);
				if ( $info == true )
				{
					echo json_return_errors("Этот врач уже привязан к этому отделению.", true);
					return false;
				}
				$info = $this->dbmodel->insertMedStaffFact($data);
				$this->ReturnData($val);
				break;
			case 'edit':
				$val = array("success"=>true);

				$this->inputRules['getMedStaffFactEditWindow_edit'] = array_merge($this->inputRules['getMedStaffFactEditWindow_save'], $this->inputRules['getMedStaffFactEditWindow_edit']);
				
				$data = $this->ProcessInputData('getMedStaffFactEditWindow_edit', true);
				if ($data === false) { return false; }
				
				$data=array_merge($data, getSessionParams());
				$info = $this->dbmodel->checkIfLpuSectionExists($data);
				if ( $info == true )
				{
					echo json_return_errors("Этот врач уже привязан к этому отделению.", true);
					return false;
				}
				$info = $this->dbmodel->updateMedStaffFact($data);
				$this->ReturnData($val);
				break;
			default:
				die();
		} //end switch
		
		if ( $info == false || count($info) == 0 )
		{
			json_return_errors("При запросе к базе данных произошла ошибка.");
		}
	} //end getMedStaffFactEditWindow()

	/**
	 * Получение списка медицинского персонала. Для гридов и комбобоксов
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalCombo() {
		$data = $this->ProcessInputData('getMedPersonalCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getMedPersonalCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	} //end getMedPersonalGrid()

	/**
	 * Получение списка медицинского персонала (только участковых врачей)
	 */
	public function getMedPersonalWithLpuRegionCombo() {
		$data = $this->ProcessInputData('getMedPersonalWithLpuRegionCombo', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getMedPersonalWithLpuRegionCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	} //end getMedPersonalGrid()

	
	/**
	* Получение списка медицинского персонала. Для формы поиска
	*/
	public function loadMedPersonalSearchList() {
		$val  = array();
		
		$data = $this->ProcessInputData('loadMedPersonalSearchList', true);
		if ($data === false) { return false; }
		
		$val['data'] = array();
		$val['totalCount'] = 0;
		
		$response = $this->dbmodel->loadMedPersonalSearchList($data);
		
		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val['data'][] = $row;
			}
			$val['totalCount'] = $response['totalCount'];
		}
		$this->ReturnData($val);
		return true;
	}

	/**
	 * Список врачей для Уфы из старого ЕРМП
	 */
	public function loadMedPersonalSearchList_Ufa_Old_ERMP() {
		$val  = array();

		$data = $this->ProcessInputData('loadMedPersonalSearchList_Ufa_Old_ERMP', true);
		if ($data === false) { return false; }

		$val['data'] = array();
		$val['totalCount'] = 0;

		$response = $this->dbmodel->loadMedPersonalSearchList_Ufa_Old_ERMP($data);

		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val['data'][] = $row;
			}
			$val['totalCount'] = $response['totalCount'];
		}
		$this->ReturnData($val);
		return true;
	}

	/**
	* Загрузка данных формы редактирования медперсонала
	*/
	function loadMedPersonal() {
		$data = $this->ProcessInputData('loadMedPersonal', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadMedPersonal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка медицинского персонала. Для гридов и комбобоксов
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGrid() {
		$data = getSessionParams();
		
		$response = $this->dbmodel->getMedPersonalGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	} //end getMedPersonalGrid()


	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGridDetail() {
		$data = $this->ProcessInputData('getMedPersonalGridDetail', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getMedPersonalGridDetail($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	} //end getMedPersonalGridDetail()
	
	
	/**
	 * Получение постраничной информации о местах работы врача
	 * Используется: АРМ кадровика
	 */
	public function getMedPersonalGridPaged() {
		$data = $this->ProcessInputData('getMedPersonalGridPaged', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getMedPersonalGridPaged($data);
        $this->ProcessModelMultiList($response,true,true)->ReturnData();
	} //end getMedPersonalGridPaged()
	
	
	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мест работы мед. персонала
	 */
	public function getMedStaffGridDetail() {
		$data = $this->ProcessInputData('getMedStaffGridDetail', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getMedStaffGridDetail($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	} //end getMedStaffGridDetail()

	/**
	 * Список мест работы для Уфы из старого ЕРМП
	 */
	public function getMedStaffGridDetail_Ufa_Old_ERMP() {
		$data = $this->ProcessInputData('getMedStaffGridDetail_Ufa_Old_ERMP', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getMedStaffGridDetail_Ufa_Old_ERMP($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getStaffTTGridDetail() {
		$data = $this->ProcessInputData('getStaffTTGridDetail', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getStaffTTGridDetail($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	} //end getStaffTTGridDetail()


	/**
	 * Удаление места работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function dropMedStaffFact() {
		$data = $this->ProcessInputData('dropMedStaffFact', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->dropMedStaffFact($data);
		$this->ProcessModelSave($response, true, 'Ошибка запроса удаления места работы')->ReturnData();
	} //end dropMedStaffFact()


	/**
	 * Получение справочника врачей имеющих право на выписку рецептов ЛЛО
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования рецепта
	 */
    function loadDloMedPersonalList()
    {
        $this->getMedPersonalList(true);
    }


	/**
	 * Получение справочника всех врачей ЛПУ
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования рецепта
	 */
    function loadMedPersonalList()
    {
        $this->getMedPersonalList(false);
    }

	/**
	 * Получение справочника врачей ЛПУ ЛЛО или всех
	 * Входящие данные: $_POST['IsDlo']
	 * На выходе: JSON-строка
	 * Используется: в заявке
	 */
    function loadMedPersonalCombo()
    {
		$data = $this->ProcessInputData('loadMedPersonalCombo', true, true);
		if ($data === false) { return false; }
		
		if ((isset($data['IsDlo'])) && ($data['IsDlo']==1)) {
			$this->getMedPersonalList(true);
		} else {
			$this->getMedPersonalList(false);
		}
    }

		
	/**
	 * Получение справочника врачей
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования рецепта
	 */
    private function getMedPersonalList($dloonly = false)
    {
		$data = $this->ProcessInputData('getMedPersonalList', true, true);
		if ($data) {
			// для загрузки списка врачей в не зависимости от того работают они на данный момент в ЛПУ или нет.
			if ( isset($data['ignoreWorkInLpu']) && !empty($data['ignoreWorkInLpu']) )
			{
				$data['onlyWorkInLpu'] = false;
			} else {
				$data['onlyWorkInLpu'] = true;
			}

			$response = $this->dbmodel->loadMedPersonalList($data, $dloonly);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
    } //end getMedPersonalList()


	/**
	 *  Получение справочника врачей с учетом работы в нескольких отделениях
	 *  Входящие данные: $_POST['date'],
	 *  На выходе: JSON-строка
	 *  Используется: <куча форм>
	 */
	function loadMedStaffFactList() {
		$data = $this->ProcessInputData('loadMedStaffFactList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadMedStaffFactList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	} // end loadMedStaffFactList()
	
	
	/**
	 * Получение комментария места работы врача
	 */
    function getMedStaffFactComment()
    {
		$data = $this->ProcessInputData('getMedStaffFactComment', true, true);
		if ($data) {
			$response = $this->dbmodel->getMedStaffFactComment($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
    } //end getMedStaffFactComment()
	
	/**
	 * Сохранение комментария места работы врача
	 */
    function saveMedStaffFactComment()
    {
		$data = $this->ProcessInputData('saveMedStaffFactComment', true, true);
		if ($data) {
			$response = $this->dbmodel->saveMedStaffFactComment($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
    } //end saveMedStaffFactComment()
	
	/**
	 * Получение длительности времени приёма врача
	 */
    function getMedStaffFactDuration()
    {
		$data = $this->ProcessInputData('getMedStaffFactDuration', true, true);
		if ($data) {
			$response = $this->dbmodel->getMedStaffFactDuration($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
    } //end saveMedStaffFactComment()
}
// END Medpersonal class