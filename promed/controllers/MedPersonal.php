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
 * @property MedPersonal_model $dbmodel
 */
class MedPersonal extends swController {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		
        $this->load->database();
        $this->load->model('MedPersonal_model', 'dbmodel');
		
		$this->inputRules = array(
			'getHistSpec' => array(
				array(
					'field'=>'LpuSection_id',
					'label'=>'ID Отделения',
					'rules'=>'trim',
					'type'=>'id'
				)
			),
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
			'loadMedStaffFactProfileEditForm' => array(
				array(
	                'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadLpuSectionProfileForMedStaffFact' => array(
				array(
	                'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadMedStaffFactDLOPeriodLinkEditForm' => array(
				array(
	                'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadMedPersonalDLOPeriod' => array(
				array(
	                'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveMedStaffFactDLOPeriodLink' => array(
				array(
	                'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
	                'field' => 'MedPersonalDLOPeriod_id',
					'label' => 'Идентификатор кода ЛЛО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
	                'field' => 'MedstaffFactDLOPeriodLink_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
	                'field' => 'MedstaffFactDLOPeriodLink_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				)
			),
			'saveMedStaffFactProfileEditForm' => array(
				array(
	                'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
	                'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => 'required',
					'type' => 'id'
				)
				,array(
					'field' => 'MedSpecOmsExt_id',
					'label' => 'Доп. специальность для рег. портала',
					'rules' => 'trim',
					'type' => 'int'
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
			'getMedPersonalInfo' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => 'required',
					'type' => 'id'
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
			'compareMedStaffFactECISxml' => array(
				array(
					'field' => 'compareDate',
					'label' => 'Дата сравнения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'compareEcisFile',
					'label' => 'Файл',
					'rules' => '',
					'type' => 'string'
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
					'field' => 'Org_id',
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
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
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
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
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
				),
				array(
					'field' => 'andWithoutLpuSection',
					'label' => 'Включать врачей без отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreDisableInDocParam',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'mode',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isDoctor',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'withDloCodeOnly',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'isMidMedPersonal',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'hideDummy',
					'label' => 'скрывать фиктивные рабочие места',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'hideFired',
					'label' => 'скрывать рабочие места где человек уволен',
					'rules' => '',
					'type' => 'int'
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
					'field' => 'Lpu_iid',
					'label' => 'Идентификатор ЛПУ руч',
					'rules' => '',
					'type' => 'int'
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
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Системное слово типа группы отделений',
					'rules' => '',
					'type' => 'string'
				),


				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор подразделения',
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
				),
				array(
					'field' => 'checkDloDate',
					'label' => 'Флаг checkDloDate',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'displayHmsSpec',
					'label' => 'Флаг: отображать специальность гл. вн. спец.',
					'rules' => '',
					'type' => 'string'
				),
				array( //https://redmine.swan.perm.ru/issues/51050
					'field' => 'begDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type'	=> 'string'
				),
				array( //https://redmine.swan.perm.ru/issues/51050
					'field' => 'endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type'	=> 'string'
				),
				array( //https://redmine.swan.perm.ru/issues/51050
					'field' => 'fromRegistryViewForm',
					'label' => 'Вызвано из окна реестров',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'LpuRegion_begDate',
					'label' => 'Дата начала работы участка',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuRegion_endDate',
					'label' => 'Дата окончания работы участка',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Идентификатор рабочего периода заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'All_Rec',
					'label' => 'Вывод только первых 1000 записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'querystr',
					'label' => 'Часть введенной строки',
					'rules' => 'ban_percent|trim',
					'type' => 'string'
				),
				array(
					'field' => 'withPosts',
					'label' => 'Фильтр специальностей',//(PostKind_id) 1 - врачи, 6 - средний медю персонал
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonalNotNeeded',
					'label' => 'Флаг игнорирования поля MedPersonal_id',
					'rules' => '',
					'type' => 'string'
				), [
					'field' => 'hideNotWork',
					'label' => 'Флаг сокрытия неработающих сотрудников',
					'rules' => '',
					'type' => 'string'
				]
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
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Мед. работник',
					'rules' => 'trim',
					'type' => 'id'
				),
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
			'getMedPersonalIsOpenMOCombo' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
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
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
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
					'type' => 'string'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => 'Идентификатор участка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'StomRequest',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedPersonalListWithPosts' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'query',
					'label' => 'Запрос',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор должностного лица',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadMedPersonalCombo' => array(
				array(
					'field' => 'IsDlo',
					'label' => 'Флаг IsDlo',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuRegion_begDate',
					'label' => 'Дата начала работы участка',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuRegion_endDate',
					'label' => 'Дата окончания работы участка',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'IsDlo',
					'label' => 'Флаг IsDlo',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuRegionType_id',
					'label' => 'Тип участка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата работы',
					'rules' => '',
					'type' => 'date'
				),
				//gaf ufa 08052018
				array(
					'field' => 'Lpu_iid',
					'label' => 'ЛПУ  руч.',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'All_Rec',
					'label' => 'Вывод только первых 1000 записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'querystr',
					'label' => 'Часть введенной строки',
					'rules' => 'ban_percent|trim',
					'type' => 'string'
				),
				array(
					'field' => 'withPosts',
					'label' => 'Фильтр специальностей',//(PostKind_id) 1 - врачи, 6 - средний медю персонал
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonalNotNeeded',
					'label' => 'Флаг игнорирования поля MedPersonal_id',
					'rules' => '',
					'type' => 'string'
				), [
					'field' => 'hideNotWork',
					'label' => 'Флаг сокрытия неработающих сотрудников',
					'rules' => '',
					'type' => 'string'
				]
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
				),
				array(
					'field' => 'PostMed_id',
					'label' => 'Идентификатор должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedicalCareKind_id',
					'label' => 'Идентификатор вида медицинской помощи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'medStaffFactDateRange',
					'label' => 'Флаг поиска в периоде',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'medStaffFactEndDateRange',
					'label' => 'Флаг поиска в периоде',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Staff_Date_range',
					'label' => 'Создана в диапазоне дат',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'Staff_endDate_range',
					'label' => 'Закрыта в диапазоне дат',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
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
				),
				array(
					'field' => 'Search_Fio',
					'label' => 'ФИО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Person_Snils',
					'label' => 'СНИЛС',
					'rules' => '',
					'type' => 'snils'
				),
				array(
					'field' => 'PostMed_id',
					'label' => 'Идентификатор должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Идентификатор типа подразделения ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WorkType_id',
					'label' => 'Идетификатор типа занятия должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WorkType_id',
					'label' => 'Идетификатор типа занятия должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'medStaffFactDateRange',
					'label' => 'Флаг поиска по работе в диапазоне дат',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'medStaffFactEndDateRange',
					'label' => 'Флаг поиска уволнению в диапазоне дат',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedStaffFact_date_range',
					'label' => 'Работает в диапазоне дат',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'MedStaffFact_disDate_range',
					'label' => 'Уволен в диапазоне дат',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
				),
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
					'field' => 'Search_Fio',
					'label' => 'ФИО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Snils',
					'label' => 'СНИЛС',
					'rules' => '',
					'type' => 'snils'
				),
				array(
					'field' => 'PostMed_id',
					'label' => 'Идентификатор должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Идентификатор типа подразделения ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WorkType_id',
					'label' => 'Идетификатор типа занятия должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WorkType_id',
					'label' => 'Идетификатор типа занятия должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'medStaffFactDateRange',
					'label' => 'Флаг поиска по работе в диапазоне дат',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'medStaffFactEndDateRange',
					'label' => 'Флаг поиска уволнению в диапазоне дат',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedStaffFact_date_range',
					'label' => 'Работает в диапазоне дат',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'MedStaffFact_disDate_range',
					'label' => 'Уволен в диапазоне дат',
					'rules' => '',
					'type' => 'daterange'
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
				),
				array(
				    'field' => 'isClose',
				    'label' => 'Флаг закрытия',
				    'rules' => '',
				    'type' => 'int'
				)
			), 
		 'ufa_getMedPersonalGridPaged' => array(
				
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),array(
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
					'field' => 'Search_Fio',
					'label' => 'ФИО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TabCode',
					'label' => 'Табельный номер',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'CodeDLO',
					'label' => 'Код ДЛО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Snils',
					'label' => 'СНИЛС',
					'rules' => '',
					'type' => 'snils'
				),
				array(
					'field' => 'PostMed_id',
					'label' => 'Идентификатор должности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WorkType_id',
					'label' => 'Идетификатор типа занятия должности',
					'rules' => '',
					'type' => 'id'
				), 
				array(
					'field' => 'RegistryDloON',
					'label' => 'Врач в регистре ДЛО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WorkPlace4DloApplyStatus_id',
					'label' => 'Статус записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WorkPlace4DloApplyTYpe_id',
					'label' => 'Тип записи',
					'rules' => '',
					'type' => 'int'
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
		     ), 
		    'saveWorkPlace4DloApply' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => '',
					'type'  => 'int'
				),
			    array(
					'field' => 'WorkPlace4DloApplyTYpe_id',
					'label' => 'Идентификатор типа',
					'rules' => '',
					'type'  => 'int'
				),
			    array(
					'field' => 'WorkPlace4DloApplyStatus_id',
					'label' => 'Статус записи',
					'rules' => '',
					'type'  => 'int'
				), 
				array(
					'field' => 'WorkPlace4DloApply_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type'  => 'int'
				)
			),
			'exportMedPersonalToXml' => array(
				array(
					'field' => 'MPExportDate',
					'label' => 'Дата выгрузки',
					'rules' => '',
					'type'  => 'string'
				)
			),
			'exportMedPersonalToXMLFRMP' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'Lpu_ids',
					'label' => 'Идентификаторы ЛПУ',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'date_from',
					'label' => '"Начиная с"',
					'rules' => 'required',
					'type'	=> 'date'
				),
				array(
					'field' => 'on_date',
					'label'	=> 'Дата',
					'rules'	=> '',
					'type'	=> 'date'
				)
			),
			'exportMedPersonalToXMLFRMPStaff' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'Lpu_ids',
					'label' => 'Идентификаторы ЛПУ',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'on_date',
					'label'	=> 'Дата',
					'rules'	=> '',
					'type'	=> 'date'
				)
			),
			'exportMedCert2XML' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'string' //т.к. для значения "Все МО" взял Lpu_id = 'all'
				),
				array(
					'field' => 'Date_range',
					'label' => 'диапазон дат',
					'rules' => '',
					'type' => 'daterange'
				)
			),
			'getMedPersonalPhoto' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'uploadMedPersonalPhoto' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteMedPersonalPhoto' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => 'required',
					'type' => 'id'
				)
			), 
		    'treatmentWorkPlace4DloApply' => array(
				array(
					'field' => 'WorkPlace4DloApply_id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
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
		$data = $this->ProcessInputData('getMedPersonInfo', true, true);
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
	* Загрузка формы редактирования профиля сотрудника
	*/
	function loadMedStaffFactProfileEditForm() {
		$data = $this->ProcessInputData('loadMedStaffFactProfileEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedStaffFactProfileEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Загрузка профилей для формы редактирования профиля сотрудника
	*/
	function loadLpuSectionProfileForMedStaffFact() {
		$data = $this->ProcessInputData('loadLpuSectionProfileForMedStaffFact', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionProfileForMedStaffFact($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Загрузка формы "Внешний код врача ЛЛО"
	*/
	function loadMedStaffFactDLOPeriodLinkEditForm() {
		$data = $this->ProcessInputData('loadMedStaffFactDLOPeriodLinkEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedStaffFactDLOPeriodLinkEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Загрузка справочника кодов ЛЛО
	*/
	function loadMedPersonalDLOPeriod() {
		$data = $this->ProcessInputData('loadMedPersonalDLOPeriod', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedPersonalDLOPeriod($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Сохранение связи рабочего места врача с внешним кодом ЛЛО
	*/
	function saveMedStaffFactDLOPeriodLink() {
		$data = $this->ProcessInputData('saveMedStaffFactDLOPeriodLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMedStaffFactDLOPeriodLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	* Сохранение профиля для формы редактирования профиля сотрудника
	*/
	function saveMedStaffFactProfileEditForm() {
		$data = $this->ProcessInputData('saveMedStaffFactProfileEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMedStaffFactProfileEditForm($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения профиля')->ReturnData();
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
	 * Получение постраничной информации о местах работы врача
	 * Используется: АРМ кадровика
	 */
	public function ufa_getMedPersonalGridPaged() {
		$data = $this->ProcessInputData('ufa_getMedPersonalGridPaged', false);
		if ($data === false) { return false; }
		//var_dump($data['WorkPlace4DloApplyTYpe_id']);
		$response = $this->dbmodel->ufa_getMedPersonalGridPaged($data);
        $this->ProcessModelMultiList($response,true,true)->ReturnData();
	} //end getMedPersonalGridPaged()
	
	
	/**
	 * Сохранение заявки на изменения в регистре врачей ЛЛО
	 */
	function saveWorkPlace4DloApply()
	{
	    $data = $this->ProcessInputData('saveWorkPlace4DloApply', true, true);
	    if ($data) {
		    $response = $this->dbmodel->saveWorkPlace4DloApply($data);
		    $this->ProcessModelSave($response, true)->ReturnData();
		    return true;
	    } else {
		    return false;
	    }
	} //end saveWorkPlace4DloApply()
	
	/**
	 * Получение информации о враче
	 * @param int $MedStaffFact_id
	 */
	function getMedPersonalInfo() {
		$data = $this->ProcessInputData('getMedPersonalInfo', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getMedPersonalInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 *
	 * @return type 
	 */
	public function getHistSpec(){
		$data = $this->ProcessInputData('getHistSpec', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getHistSpec($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
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
			$data['checkDloDate'] = !empty($data['checkDloDate']);
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
	function compareMedStaffFactECISxml() {

		$data = $this->ProcessInputData('compareMedStaffFactECISxml', true, true);

		if (!isset($_FILES['compareEcisFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл!') ) ) ;
			return false;
		}

		if ( !is_uploaded_file($_FILES['compareEcisFile']['tmp_name']) ) {
			$error = (!isset($_FILES['compareEcisFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

			switch ( $error ) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}

			$this->ReturnError($message, 100012);
			$this->textlog->add($message);
			return false;
		}

		$x = explode('.', $_FILES['compareEcisFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array($file_data['file_ext'], array('xml'))) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		$xmlfile = $_FILES['compareEcisFile']['tmp_name'];

		$xml = file_get_contents($_FILES['compareEcisFile']['tmp_name']);

		$xmlEl = new SimpleXMLElement($xml);

		$mspersonList = array();

		$insexEcis = 0;
		$this->load->model('MedStaffFact_model', 'MedStaffFact_model');
		foreach ($xmlEl->children()  as $key => $value) {
			$mperson = (array)$value;

			$mperson = array(
				"EcisPerson_SurName" => !empty($mperson['Фамилия']) ? $mperson['Фамилия'] : null,
				"EcisPerson_FirName" => !empty($mperson['Имя']) ? $mperson['Имя'] : null,
				"EcisPerson_SecName" => !empty($mperson['Отчество']) ? $mperson['Отчество'] : null,
				"EcisPerson_BirthDay" => !empty($mperson['ДатаРождения']) ? $mperson['ДатаРождения'] : null,
				"EcisLpuSection_Name" => !empty($mperson['Отделение']) ? $mperson['Отделение'] : null,
				"EcisDolgnost_Name" => !empty($mperson['Должность']) ? $mperson['Должность'] : null,
				"EcisPostOccupationTypeName" => !empty($mperson['ВидОклада']) ? $mperson['ВидОклада'] : null,
				"EcisMoveInOrgRecordType_id" => !empty($mperson['Статус']) ? $mperson['Статус'] : null,
				"EcisMedStaffFact_Stavka" => !empty($mperson['Ставка']) ? $mperson['Ставка']: null
			);

			$ms = array(
				'Person_SurName' => $mperson['EcisPerson_SurName'],
				'Person_FirName' => $mperson['EcisPerson_FirName'],
				'Person_SecName' => $mperson['EcisPerson_SecName'],
				'Person_BirthDay' => $mperson['EcisPerson_BirthDay'],
				 'compareDate' => $data['compareDate']
			);

			$res = $this->MedStaffFact_model->getMedStaffFactInfo($ms);
			if(is_array($res) && !empty($res[0])){
				$insexEcis++;
				foreach ($res as $key => $value) {
					$pers = $value;
					//для 1 результатв вставляем 1 из ецис
					if($key == 0){
						//параметр для rowsapna
						$pers["CountProMs"] = count($res);
						$pers["insexEcis"] = $insexEcis;
						$pers["EcisPerson_SurName"] = $mperson['EcisPerson_SurName'];
						$pers["EcisPerson_FirName"] = $mperson['EcisPerson_FirName'];
						$pers["EcisPerson_SecName"] = $mperson['EcisPerson_SecName'];
						$pers["EcisPerson_BirthDay"] = $mperson['EcisPerson_BirthDay'];
						$pers["EcisLpuSection_Name"] = $mperson['EcisLpuSection_Name'];
						$pers["EcisDolgnost_Name"] = $mperson['EcisDolgnost_Name'];
						$pers["EcisPostOccupationTypeName"] = $mperson['EcisPostOccupationTypeName'];
						$pers["EcisMoveInOrgRecordType_id"] = $mperson['EcisMoveInOrgRecordType_id'];
						$pers["EcisMedStaffFact_Stavka"] = $mperson['EcisMedStaffFact_Stavka'];
					}
					$mspersonList[] = $pers;
				}
			}
		}

		$this->load->library( 'parser' );

		//кол-во записей Ецис и промедовских могут разниться (у Промеда мб больше)
		$this->parser->parse( 'print_MsEcis', array(
			'mspersonList' => $mspersonList,
			'compareDate' =>  date('d.m.Y', strtotime($data['compareDate']))
		) );

	}

	/**
	 *  Получение справочника врачей с учетом работы в нескольких отделениях
	 *  Входящие данные: $_POST['date'],
	 *  На выходе: JSON-строка
	 *  Используется: <куча форм>
	 */
	function loadMedStaffFactList() {
		$data = $this->ProcessInputData('loadMedStaffFactList', true, true);
		if ($data) {
			if (!empty($_POST['Lpu_id'])) {
				$data['Lpu_id'] = $_POST['Lpu_id'];
			}
			if (isset($data['MedPersonal_id']) && isset($data['LpuSection_id'])) {
				$response = $this->dbmodel->loadMedStaffFactList($data);
				$this->ProcessModelList($response, true, true)->ReturnData();
				return true;
			}
			if(!((isset($data['Lpu_id'])&&($data['Lpu_id'] > 0))||(isset($data['Org_id'])&&($data['Org_id'] > 0))))
			{ //https://redmine.swan.perm.ru/issues/60543 под аптекой нет Lpu_id и Org_id, поэтому запрос гребет все подряд, а это больше 40тыщ записей, и это только на тестовом.
				//запрос выполняется овер40 секунд и вываливается ошибка.
				//поэтому в таком случае не идем дальше, возращаем false
				return false;
			}
			$response = $this->dbmodel->loadMedStaffFactList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	} // end loadMedStaffFactList()

	/**
	 *  Получение справочника врачей с учетом работы в нескольких отделениях
	 *  Входящие данные: $_POST['date'],
	 *  На выходе: JSON-строка
	 *  Используется: <комбо>
	 */
	function loadMedStaffFactListByLpuStructure() {

		$data = $this->ProcessInputData('getMedPersonalGridDetail', true);
		if ($data) {

			if (isset($_POST['Lpu_id'])) {
				$data['Lpu_id'] = $_POST['Lpu_id'];
			}

			$data['isClose'] = 1;

			$response = $this->dbmodel->getMedPersonalGridDetail($data);
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

	/**
	 *  Экспорт реестра мед работников на дату
	 */
	function exportMedPersonalToXml()
	{
		$data = $this->ProcessInputData('exportMedPersonalToXml',true,true);
		if ($data === false) { return false; }
		set_time_limit(0);
		$MedPersonalData = $this->dbmodel->getDataForMPExport($data);
		//var_dump($MedPersonalData);die;
		array_walk_recursive($MedPersonalData, 'ConvertFromUTF8ToWin1251', true);
		$this->load->library('parser');
		if (!file_exists(EXPORTPATH_MEDPERSONAL_LIST))
			mkdir( EXPORTPATH_MEDPERSONAL_LIST );
		$out_dir = "xml_".time()."_"."MPList";
		mkdir( EXPORTPATH_MEDPERSONAL_LIST.$out_dir );
		$mp_list_file_name = "MP_LIST";
		$mp_list_file_path = EXPORTPATH_MEDPERSONAL_LIST.$out_dir."/".$mp_list_file_name.".xml";
		$templ = "medpersonal";
		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$templ, $MedPersonalData, true);
		file_put_contents($mp_list_file_path, $xml);
		
		$file_zip_sign = $mp_list_file_name;
        $file_zip_name = EXPORTPATH_MEDPERSONAL_LIST.$out_dir."/".$file_zip_sign.".zip";
        $zip = new ZipArchive();
        $zip->open($file_zip_name, ZIPARCHIVE::CREATE);
        $zip->AddFile( $mp_list_file_path, $mp_list_file_name . ".xml" );
        $zip->close();

        unlink($mp_list_file_path);

        if (file_exists($file_zip_name))
        {
            $this->ReturnData(array('success' => true,'Link' => $file_zip_name/*, 'Doc' => $attached_list_data['DOC']*/));
        }
        else {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
        }
        return true;
		//var_dump($mp_list_file_path);die;
	}

	/**
	 * Выгрузка регистра медработников для ТФОМС
	 */
	function exportMedPersonalToXMLFRMP()
	{
		set_time_limit(0);

		$data = $this->ProcessInputData('exportMedPersonalToXMLFRMP', false);
		if ($data === false) { return false; }

		if(isset($data['on_date']))
		{	
			$on_date = $data['on_date'][5].$data['on_date'][6].$data['on_date'][2].$data['on_date'][3]; //берем только ММГГ
			$data['on_date'] = $on_date;
		}
		$response = $this->dbmodel->exportMedPersonalToXMLFRMP($data);
		$this->ReturnData($response);

		return true;
	}


	/**
	 * Выгрузка регистра медработников для ТФОМС
	 */
	function exportMedPersonalToXMLFRMPStaff()
	{
		set_time_limit(0);

		$data = $this->ProcessInputData('exportMedPersonalToXMLFRMPStaff', false);
		if ($data === false) { return false; }

		if(isset($data['on_date']))
		{	
			$on_date = $data['on_date'][5].$data['on_date'][6].$data['on_date'][2].$data['on_date'][3]; //берем только ММГГ
			$data['on_date'] = $on_date;
		}
		
		$response = $this->dbmodel->exportMedPersonalToXMLFRMPStaff($data);
		$this->ReturnData($response);

		return true;
	}

    /**
     * Функция возвращает в XML список врачей с сертификатами
     */
    function exportMedCert2XML() {

		$data = $this->ProcessInputData('exportMedCert2XML',true,true);
		if ($data === false) { return false; }
		set_time_limit(0);

		$MedPersonalCertData = $this->dbmodel->exportMedCert2XML($data);
		array_walk_recursive($MedPersonalCertData, 'ConvertFromUTF8ToWin1251', true);
		$this->load->library('parser');
		if (!file_exists(EXPORTPATH_MEDPERSONALCERT_LIST))
			mkdir( EXPORTPATH_MEDPERSONALCERT_LIST );

		$out_dir = "medcert_".time();
		mkdir( EXPORTPATH_MEDPERSONALCERT_LIST.$out_dir );

		$mp_list_file_name = "medcert_".date('Ymd', time());
		$errors_list_file_name = "error";

		$mp_list_file_path = EXPORTPATH_MEDPERSONALCERT_LIST.$out_dir."/".$mp_list_file_name.".xml";
		$error_list_file_path = EXPORTPATH_MEDPERSONALCERT_LIST.$out_dir."/".$errors_list_file_name;


		if (is_array($MedPersonalCertData['MED_PERS']) && !empty($MedPersonalCertData['MED_PERS'][0])) {
			$templ = "medcertificat";
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$templ, array('MED_PERS' => $MedPersonalCertData['MED_PERS']), true);
			file_put_contents($mp_list_file_path, $xml);
		}

		if (is_array($MedPersonalCertData['ERRORS']) && !empty($MedPersonalCertData['ERRORS'][0])) {
			$error = '';
			foreach ($MedPersonalCertData['ERRORS'] as $key => $value) {
				if (!empty($value['MP_info'])) {
					$error .= $value['MP_info'] ."\r\n";
				}
			}
			$error = toUTF($error);
			file_put_contents($error_list_file_path, $error);
		}

		if (!file_exists($mp_list_file_path) && !file_exists($mp_list_file_path)){
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('В базе не найдены данные по заданным параметрам.')));
			return false;
		}

		$file_zip_sign = $mp_list_file_name;
		$file_zip_name = EXPORTPATH_MEDPERSONALCERT_LIST.$out_dir."/".$file_zip_sign.".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		if (file_exists($mp_list_file_path)){
			$zip->AddFile( $mp_list_file_path, $mp_list_file_name . ".xml" );
		}
		if (file_exists($error_list_file_path)){
			$zip->AddFile( $error_list_file_path, $errors_list_file_name . ".txt" );
		}
		$zip->close();
		if (file_exists($mp_list_file_path)){
			unlink($mp_list_file_path);
		}

		if (file_exists($error_list_file_path)){
			unlink($error_list_file_path);
		}

		if (file_exists($file_zip_name))
		{
			$this->ReturnData(array('success' => true,'Link' => $file_zip_name));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива с данными!')));
		}
		return true;
	}
	
	/**
	 * Метод загрузки фотографии медицинского работника
	 * формирует два файла по пути вида вида: 
	 * uploads/medpersonal/photos/[MedPersonal_id].(jpg|png|gif)
	 * uploads/medpersonal/photos/thumbs/[MedPersonal_id].(jpg|png|gif)
	 */
	function uploadMedPersonalPhoto() {
		$data = $this->ProcessInputData('uploadMedPersonalPhoto', false);
		$response = $this->dbmodel->uploadMedPersonalPhoto($data, $_FILES);
		if (is_array($response)) {
			$this->ReturnData($response);
		} else {
			DieWithError('Не удалось загрузить файл!');
			return false;
		}
	}

	/**
	 * Метод чтения фотографии медицинского работника
	 * получает thumbs: 
	 * uploads/medpersonal/photos/[MedPersonal_id].(jpg|png|gif)
	 * uploads/medpersonal/photos/thumbs/[MedPersonal_id].(jpg|png|gif)
	 */
	function getMedPersonalPhoto() {
		$data = $this->ProcessInputData('getMedPersonalPhoto', false);
		$response = $this->dbmodel->getMedPersonalPhoto($data, $_FILES);
		if ($response) {
			$this->ReturnData(array('success'=>true, 'mp_photo'=>$response));
		} else {
			$this->ReturnData(array('success'=>false, 'mp_photo'=>''));
		}
	}

	/**
	 * Метод удаления фотографии медицинского работника
	 * удаляет: 
	 * uploads/medpersonal/photos/[MedPersonal_id].(jpg|png|gif)
	 * uploads/medpersonal/photos/thumbs/[MedPersonal_id].(jpg|png|gif)
	 */
	function deleteMedPersonalPhoto() {
		$data = $this->ProcessInputData('deleteMedPersonalPhoto', false);
		if ($data === false) {return false;}
		$response = $this->dbmodel->deleteMedPersonalPhoto($data);
		$this->ReturnData($response);
	}
	
	/**
	* Формирование Кода ДЛО нв основании заявок на изменение (таблица WorkPlace4DloApply)
	*/
	function treatmentWorkPlace4DloApply() {
		$data = $this->ProcessInputData('treatmentWorkPlace4DloApply', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->treatmentWorkPlace4DloApply($data);
		$this->ProcessModelSave($response, true, 'Ошибка обработки')->ReturnData();
	}
	
	/**
	 * Получение списка медицинского персонала в действующих МО на текущую дату. Для комбобокса
	 */
	function getMedPersonalIsOpenMOCombo(){
		$data = $this->ProcessInputData('getMedPersonalIsOpenMOCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getMedPersonalIsOpenMOCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
// END Medpersonal class