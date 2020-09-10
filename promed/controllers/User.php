<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * User - контроллер для управления пользователями, получение списка, информации о пользователе,
 * добавление, изменение, удаление пользователей. Данные о пользователях хранятся в LDAP
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      22.05.2009
 *
 * @property User_model dbmodel
 * @property Org_model $orgmodel
 */
class User extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->library('textlog', array('file' => 'LDAP_' . date('Y-m-d') . '.log'));
		$this->inputRules = array(
			'changePassword' => array(
				array(
					'field' => 'old_password',
					'label' => 'Старый пароль',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'new_password',
					'label' => 'Новый пароль',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'new_password_two',
					'label' => 'Новый пароль',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'dropUser' => array(
				array(
					'field' => 'user_login',
					'label' => 'Логин',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'restoreUser' => array(
				array(
					'field' => 'user_login',
					'label' => 'Логин',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getUserData' => array(
				array(
					'field' => 'user_login',
					'label' => 'Логин',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'saveUserData' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'login',
					'label' => 'Логин',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'mode',
					'label' => 'Режим сохранения',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'pass',
					'label' => 'Временный пароль',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'surname',
					'label' => 'Фамилия',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'firname',
					'label' => 'Имя',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'secname',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'email',
					'label' => 'Эл. почта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'desc',
					'label' => 'Описание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Сотрудник',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'orgs',
					'label' => 'Организации',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'groups',
					'label' => 'ID групп',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'groupsNames',
					'label' => 'Имена групп',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'certs',
					'label' => 'Сертификаты',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'deniedARMs',
					'label' => 'Запрещённые АРМы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'blocked',
					'label' => 'Заблокирован',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'swtoken',
					'label' => 'Токен',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'swtoken_enddate',
					'label' => 'Дата окончания токена',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'marshserial',
					'label' => 'Идент. МАРШа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'parallel_sessions',
					'label' => 'Кол-во параллельных сессий',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'login_emias',
					'label' => 'Логин ЕМИАС',
					'rules' => '',
					'type' => 'string'
				)
			),
			'setCurrentARM' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
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
					'field' => 'ARMType',
					'label' => 'Тип АРМа',
					'rules' => '',
					'type' => 'string'
				)
			),
			'setCurrentMSF' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadAccessGridPanel' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'login',
					'label' => 'Логин',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Orgs',
					'label' => 'Список организаций',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Groups',
					'label' => 'Список групп',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getMSFList' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				)
			),
			'setCurrentLpu' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'changeCurrentLpu' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'setCurrentFarmacy' => array(
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'DrugFinance_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FarmacyOtdel_Name',
					'label' => 'FarmacyOtdel_Name',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getOrgUsersTree' => array(
				array(
					'field' => 'node',
					'label' => 'Название ноды',
					'rules' => 'required',
					'type' => 'string'
				),

			),
			'getGroupTree' => array(
				array(
					'field' => 'level',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'node',
					'label' => 'Ветка',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadGroups' => array(
				array(
					'field' => 'group_id',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'filter',
					'label' => 'Фильтр',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveGroup' => array(
				array(
					'field' => 'pmUserCacheGroup_id',
					'label' => 'id Кэш Группы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'dn',
					'label' => 'LDAP Link',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Group_id',
					'label' => 'Группа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Group_Code',
					'label' => 'Код группы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Group_Name',
					'label' => 'Наименование группы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Group_IsBlocked',
					'label' => 'Признак "Группа заблокирована"',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Group_IsOnly',
					'label' => 'Признак "Единственность"',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Group_Type',
					'label' => 'Тип группы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Group_ParallelSessions',
					'label' => 'Кол-во парралельных сеансов для группы',
					'rules' => '',
					'type' => 'string'
				)
			),
			'importUserCert' => array(
				array(
					'field' => 'UserCertFile',
					'label' => 'Файл',
					'rules' => '',
					'type' => 'string'
				)
			),
			'deleteGroup' => array(
				array(
					'field' => 'pmUserCacheGroup_id',
					'label' => 'ID Кэш группы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Group_id',
					'label' => 'ID группы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'dn',
					'label' => 'ID',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getObjectTree' => array(
				array('field' => 'level', 'label' => 'Уровень', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'node', 'label' => 'Ветка', 'rules' => '', 'type' => 'string')
			),
			'getWindowRoles' => array(
				array('field' => 'objectClass', 'label' => 'Класс окна', 'rules' => 'required', 'type' => 'string'),
			),
			'getObjectRoleList' => array(
				array('field' => 'level', 'label' => 'Уровень', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'node', 'label' => 'Ветка', 'rules' => '', 'type' => 'string'),
				array('field' => 'Role_id', 'label' => 'Роль', 'rules' => '', 'type' => 'string')
			),
			'getLisSettings' => array(
				array('field' => 'pmUser_Login', 'label' => 'Логин', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_login', 'label' => 'Логин в ЛИС', 'rules' => 'required', 'type' => 'string')
			),
			'loadLisWPGrid' => array(
				array('field' => 'pmUser_Login', 'label' => 'Логин', 'rules' => 'required', 'type' => 'string')
			),
			'setLisSettings' => array(
				array('field' => 'pmUser_Login', 'label' => 'Логин', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_analyzername', 'label' => 'Наименование анализатора', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_note', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
				array('field' => 'lis_login', 'label' => 'Логин в ЛИС', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_password', 'label' => 'Пароль', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_company', 'label' => 'Наименование ЛПУ', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_lab', 'label' => 'Наименование лаборатории', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_machine', 'label' => 'Название машины в ЛИС', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_clientId', 'label' => 'Id клиента', 'rules' => 'required', 'type' => 'string')
			),
			'setDefaultWorkPlace' => array(
				array('field' => 'ARMName', 'label' => 'Название АРМа', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'ARMType', 'label' => 'Тип АРМа', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'ARMForm', 'label' => 'Форма', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Место работы', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id')
			),
			'resetDefaultWorkPlace' => array(),
			'deleteLisSetting' => array(
				array('field' => 'pmUser_Login', 'label' => 'Логин', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'lis_login', 'label' => 'Логин в ЛИС', 'rules' => 'required', 'type' => 'string')
			),
			'getObjectActionsList' => array(
				array('field' => 'type', 'label' => 'Тип объекта', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'object', 'label' => 'Объект', 'rules' => 'required', 'type' => 'string')
			),
			'getObjectHeaderList' => array(
				array('field' => 'node', 'label' => 'Тип объекта', 'rules' => 'required', 'type' => 'string')
			),
			'saveObjectRole' => array(
				array('field' => 'data', 'label' => 'Данные', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'node', 'label' => 'Ветка', 'rules' => '', 'type' => 'string'),
				array('field' => 'Role_id', 'label' => 'Роль', 'rules' => 'required', 'type' => 'string')
			),
			'getUsersList' => array(
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),

				array('field' => 'org', 'label' => 'ЛПУ', 'rules' => '', 'type' => 'string'),
				array('field' => 'orgFarmacy', 'label' => 'ЛПУ', 'rules' => '', 'type' => 'string'),
				array('field' => 'withoutPaging', 'label' => 'Без пейджинга', 'rules' => '', 'type' => 'string'),
				// Фильтры поиска
				array('field' => 'pmUser_surName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'pmUser_firName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'pmUser_deleted', 'label' => 'Удаленные', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'OrgType_id', 'label' => 'Тип организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'login', 'label' => 'Логин', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'group', 'label' => 'Группа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'pmUser_desc', 'label' => 'Описание', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'pmUser_Blocked', 'label' => 'Заблокирован', 'rules' => 'trim', 'type' => 'id')
			),
			'getUsersListOfCache' => array(
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100),

				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'string'),
				// Фильтры поиска
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'login', 'label' => 'Логин', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'group', 'label' => 'Группа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'desc', 'label' => 'Описание', 'rules' => 'trim', 'type' => 'string')
			),
			// пока что в рамках конкретного ЛПУ
			'syncLdapAndCacheUserData' => array(
				array('field' => 'Org_id', 'label' => 'ЛПУ', 'rules' => 'required', 'type' => 'string')
			),
			'updateARMList' => array(),
			'loadARMAccessGrid' => array(
				array('field' => 'Report_id', 'label' => 'Отчет', 'rules' => '', 'type' => 'id'),
				array('field' => 'ReportContentParameter_id', 'label' => 'Параметр отчёта', 'rules' => '', 'type' => 'id')
			),
            'saveReportARMAccessAll' => array(
                array('field' => 'Report_id', 'label' => 'Отчет', 'rules' => '', 'type' => 'id'),
                array('field' => 'ReportContentParameter_id', 'label' => 'Параметр', 'rules' => '', 'type' => 'id'),
                array('field' => 'idField', 'label' => 'Наименование поля идентификатора', 'rules' => '', 'type' => 'string'),
                array('field' => 'action', 'label' => 'Тип действия (отметить/снять)', 'rules' => 'required', 'type' => 'string')
            ),
			'saveReportARM' => array(
				array('field' => 'isAccess', 'label' => 'Создать/Убрать доступ', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Report_id', 'label' => 'Отчет', 'rules' => '', 'type' => 'id'),
				array('field' => 'ReportContentParameter_id', 'label' => 'Параметр', 'rules' => '', 'type' => 'id'),
				array('field' => 'ReportContentParameterLink_id', 'label' => 'Параметр-Арм', 'rules' => '', 'type' => 'id'),
				array('field' => 'ReportARM_id', 'label' => 'Отчет-Арм', 'rules' => '', 'type' => 'id'),
				array('field' => 'idField', 'label' => 'Наименование поля идентификатора', 'rules' => '', 'type' => 'string'),
				array('field' => 'ARMType_id', 'label' => 'Тип арма', 'rules' => 'required', 'type' => 'id')
			),
			'ldapAttributeChange' => array(
				array('field' => 'attribute', 'label' => 'Атрибут', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'oldValue', 'label' => 'Старое значение', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'newValue', 'label' => 'Новое значение', 'rules' => 'required', 'type' => 'string')
			),
			'blockUsers' => array(
				array('field' => 'pmUser_Blocked', 'label' => 'Блокировать', 'rules' => 'required', 'type' => 'checkbox'),
				array('field' => 'pmUser_ids', 'label' => 'Идентификаторы пользователей', 'rules' => 'required', 'type' => 'string')
			),
			'interruptUserSessions' => array(
				array('field' => 'Session_ids', 'label' => 'Идентификаторы сессии', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DelayMinutes', 'label' => 'Задержка в минутах', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'Message', 'label' => 'Сообщение', 'rules' => '', 'type' => 'string')
			),
			"getUserSessions" => [
				["field" => "start", "label" => "Начальный номер записи", "rules" => "", "type" => "int", "default" => 0],
				["field" => "limit", "label" => "Количество возвращаемых записей", "rules" => "", "type" => "int", "default" => 100],
				["field" => "Login_Range", "label" => "Период входа", "rules" => "trim", "type" => "daterange"],
				["field" => "Logout_Range", "label" => "Период выхода", "rules" => "trim", "type" => "daterange"],
				["field" => "IsMedPersonal", "label" => "Врач", "rules" => "", "type" => "id"],
				["field" => "PMUser_Name", "label" => "Имя пользователя", "rules" => "trim", "type" => "string"],
				["field" => "PMUser_Login", "label" => "Логин", "rules" => "trim", "type" => "string"],
				["field" => "IP", "label" => "IP пользователя", "rules" => "trim", "type" => "string"],
				["field" => "AuthType_id", "label" => "Тип авторизации", "rules" => "trim", "type" => "id"],
				["field" => "Org_id", "label" => "Организация", "rules" => "trim", "type" => "id"],
				["field" => "userOrg_id", "label" => "Организация пользователя", "rules" => "trim", "type" => "id"],
				["field" => "PMUserGroup_Name", "label" => "Группа пользователя", "rules" => "trim", "type" => "string"],
				["field" => "Status", "label" => "Попытка подключения", "rules" => "trim", "type" => "int"],
				["field" => "onlyActive", "label" => "Только активные", "rules" => "trim", "type" => "checkbox"]
			],
			"getMethods" => [],
			'addPmAuthGroups' => array(
				array('field' => 'Session_ids', 'label' => 'Идентификаторы сессии', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'DelayMinutes', 'label' => 'Задержка в минутах', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'Message', 'label' => 'Сообщение', 'rules' => '', 'type' => 'string')
			),
			'getPHPARMTypeList' => array(
				array(
					'field' => 'query',
					'label' => 'Строка контекстного поиска',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadOnlineUsersList' => array(
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id'),
				array('field' => 'OrgType_id', 'label' => 'Тип организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'ARMType_SysNick', 'label' => 'АРМ', 'rules' => 'trim', 'type' => 'string')
			),
			'loadPMUserCacheOrgList' => array(
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'pmUserCacheOrg_id', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка запроса', 'rules' => '', 'type' => 'string'),
			),
			'checkShownMsgArms' => array(
				array('field' => 'curARMType', 'label' => 'АРМ', 'rules' => '', 'type' => 'string')
			),
			'getLpuList' => array(
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'MedServiceType_SysNick',
					'rules' => '',
					'type' => 'string'
				),
			),
		);
	}

	/**
	 * index
	 */
	function Index() {
		return false;
	}

	/**
	 * Получение информации о конкретном пользователе
	 */
	function getUserData() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('getUserData', true, false);
		if ($data === false) { return false; }

		$user = pmAuthUser::find($data['user_login']);
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			$val = array();
			
			//$groups = $user->getGroups();
			$data['pmUser_id'] = $user->pmuser_id;
			$groups = $this->dbmodel->getUserGroups($data);
			
			$this->load->model('Org_model', 'orgmodel');
			
			$orgs = array();
			foreach (array_values($user->org) as $org) {
				$orginfo = $this->orgmodel->getOrgList(array(
					'Org_id' => $org['org_id'],
					'needOrgType' => true
				));
				
				$orgs[] = array(
					'Org_id' => $org['org_id'],
					'Org_Nick' => isset($orginfo[0]['Org_Nick'])?toUtf($orginfo[0]['Org_Nick']):toUtf('Название не определено'),
					'OrgType_Name' => isset($orginfo[0]['OrgType_Name'])?toUtf($orginfo[0]['OrgType_Name']):''
				);
			}
			
			$certs = $user->certs;
			
			$val[] = array(
				'pmUser_id'=>$user->pmuser_id,
				'id'=>$user->id,
				'login'=>$user->login,
				'pass'=>'#$&password#$??',
				'blocked'=>$user->blocked,
				'firname'=>$user->firname,
				'surname'=>$user->surname,
				'secname'=>$user->secname,
				'name'=>$user->name,
				'desc'=>$user->desc,
				'email'=>$user->email,
				'marshserial'=>$user->marshserial,
				'swtoken'=>$user->swtoken,
				'swtoken_enddate'=>$user->swtoken_enddate,
				'MedPersonal_id'=>!empty($user->medpersonal_id)?$user->medpersonal_id:null,
				'Groups'=>$groups,
				'parallel_sessions'=>$user->parallelSessions,
				'login_emias'=>$user->loginEmias,
				'Orgs'=>$orgs,
				'Certs'=>$certs
			);
			
			$this->ReturnData($val);
		}
	}

	/**
	 * Сохранение информации о конкретном пользователе
	 */
	function saveUserData() {
		$this->load->helper('Text');
		$this->load->database();
		$this->load->model("Org_model", "Org_model");
		$this->load->model("User_model", "dbmodel");
		$adminSWNT = array("SWNT_ADMIN1","SWNT_ADMIN2");
		

		$data = $this->ProcessInputData('saveUserData', true, false);
		if ($data === false) { return false; }

		if (!preg_match('/^[a-zA-Z0-9\_\.]+$/', $data['login']) && $data['mode'] == 'add' ) {
			$this->ReturnError('Неверное значение в поле Логин');
			return false;
		}

		if (strtoupper( substr($data['login'], 0,4) ) == 'SWNT') {
			// пользователи, которым разрешенно создавать пользователя с логином начинающимся на "SWNT"
			if( !in_array($data['session']['login'], $adminSWNT) && $data['login'] != 'SWNT_ADMIN1' ){
				$this->ReturnError('Добавление/редактирование тестовых учетных записей недоступно');
				return false;
			}
		}
		
		if (!isSuperAdmin() && empty($data['groups'])) {
			$this->ReturnError('Нельзя добавить пользователя без привязки к организации!');
			return false;
		}
		
		$is_farmacy_net_admin = false; // перенёс из saveFarmacyUserData для сохранения Администраторов сети аптек с organizationalstatus = 2.
		if (in_array('FarmacyNetAdmin', $data['groups'])) {
			$is_farmacy_net_admin = true;
		}

		/*if(
			!empty($data['groupsNames']) 
			&& is_array($data['groupsNames']) 
			&& array_search('APIUser', $data['groupsNames']) !== false
			&& count($data['groupsNames']) > 1
		) {
			$this->ReturnError('Пользователям с группой «Пользователь API» не предусмотрен доступ к другим группам');
			return false;
		}*/
		
		switch ( $data['mode'] ) {
			case 'add':
				$newUser = pmAuthUser::add(trim($data['surname'] . " " . $data['firname']), $data['login'], $data['pass'], $data['surname'], $data['secname'], $data['firname']);
				// добавляем сертификаты
				foreach($data['certs'] as $cert) {
					$newUser->addCert($cert);
				}
				// добавляем новые группы
				foreach($data['groupsNames'] as $group) {
					if ( isSuperAdmin() || $group != 'SuperAdmin' ) {
						$newUser->addGroup($group);
					}
				}
				/*foreach($data['groups'] as $group) {
					if ( isSuperAdmin() || $group != 'SuperAdmin' ) {
						$data['group'] = $group;
						$data['id'] = $newUser->pmuser_id;
						$this->dbmodel->addGroupLink($data);
					}
				}*/

				// добавляем новые организации
				foreach($data['orgs'] as $org) {
					if ( isSuperAdmin() || havingOrg($org) ) {
						// Проверка что пользователь есть в выбранном ЛПУ
						$newUser->addOrg($org);
					}
				}
				$newUser->email = $data['email'];
				$newUser->marshserial = (!empty($data["marshserial"]) ? $data["marshserial"] : '');
				$newUser->swtoken = (!empty($data["swtoken"]) ? $data["swtoken"] : '');
				$newUser->swtoken_enddate = (!empty($data["swtoken_enddate"]) ? $data["swtoken_enddate"] : '');
				$newUser->medpersonal_id = $data["MedPersonal_id"];
				if (!empty($data['deniedARMs'])) {
					$newUser->deniedarms = $data['deniedARMs'];
				}

				$newUser->desc = $data['desc'];

				$newUser->password_temp = 1;
				$newUser->password_date = time();

				//	настройки для новых пользователей
				$opt = array();
				$opt['recepts']['print_extension'] = 1;
				$newUser->settings = @serialize($opt);
				
				if ( $is_farmacy_net_admin === true ) {
					$newUser->insert(false, true);
				} else {
					$newUser->insert();
				}
				$this->ReCacheUserData($newUser);
				
				if (!empty($data['formMode']) && $data['formMode']='orgaccess' && isSuperadmin()) {
					// разрешить доступ всем организациям пользователя
					for($j = 0, $cnt = count($newUser->org); $j < $cnt; $j++) { // Проверка что пользователь есть в выбранном ЛПУ
						if (isset($newUser->org[$j])) {
							$params = array();
							$params['Org_id'] = $newUser->org[$j]['org_id'];
							$params['grant'] = 2;
							$this->Org_model->giveOrgAccess($params);
						}
					}
				}
				break;
			case 'edit':
				if( !in_array($data['session']['login'], $adminSWNT) && strtoupper( substr($data['login'], 0,4) ) == 'SWNT'){
					$this->ReturnError('Редактирование учетной записи недоступно');
					return false;
				}
				$editUser = pmAuthUser::find($data['login']);
				
				if (!isSuperAdmin()) {
					foreach (array_values($editUser->groups) as $group) {
						if ($group->name == 'SuperAdmin') {
							$this->ReturnError('Вам запрещено редактировать данного пользователя.');
							return false;
						}
					}
				}
				
				if (!$editUser) {
					$this->ReturnError('Не удалось найти пользователя.');
					return false;
				}

				// удаляем все сертификаты
				$editUser->certs = array();
				// добавляем новые
				foreach($data['certs'] as $cert) {
					$editUser->addCert($cert);
				}
				// удаляем все группы
				foreach (array_values($editUser->groups) as $group) {
					$editUser->removeGroup($group->name);
				}
				/*$data['id'] = $editUser->pmuser_id;
				$this->dbmodel->removeGroupLink($data);*/
				
				// добавляем новые группы
				foreach($data['groupsNames'] as $group) {
					if ( isSuperAdmin() || $group != 'SuperAdmin' ) {
						$editUser->addGroup($group);
					}
				}
				/*foreach($data['groups'] as $group) {
					if ( isSuperAdmin() || $group != 'SuperAdmin' ) {
						$data['group'] = $group;
						$data['id'] = $editUser->pmuser_id;
						$this->dbmodel->addGroupLink($data);
					}
				}*/

				$editUser->org = array();
				// добавляем новые организации
				foreach($data['orgs'] as $org) {
					if ( isSuperAdmin() || havingOrg($org) ) {
						// Проверка что пользователь есть в выбранном ЛПУ
						$editUser->addOrg($org);
					}
				}

				if ( $data['pass'] != '#$&password#$??' && substr($data['pass'],0,5)<>"{MD5}" ) {
					$this->load->model("User_model");
					$check = $this->User_model->checkPassword($data['pass'], null, $editUser);
					if (!empty($check['Error_Msg'])) {
						$this->ReturnError($check['Error_Msg']);
						return false;
					}
					
					$editUser->pass = "{MD5}" . base64_encode(md5($data['pass'], TRUE));
					$editUser->password_temp = 1;
					$editUser->password_date = time();
					switch (checkMongoDb()) {
						case 'mongo':
							$this->load->library('swMongodb', null, 'mongo_db');
							break;
						case 'mongodb':
							$this->load->library('swMongodbPHP7', null, 'mongo_db');
							break;
						default:
							$this->ReturnError('The MongoDB PECL extension has not been installed or enabled.');
							return false;
					}
					$this->mongo_db->where(array('_id' => $data['login']))->delete('users_login_fail');
				}

				$editUser->name = trim($data['surname'] . " " . $data['firname']);
				$editUser->surname = $data['surname'];
				$editUser->firname = $data['firname'];
				$editUser->secname = $data['secname'];
				$editUser->parallelSessions = $data['parallel_sessions'] != null ? $data['parallel_sessions'] : ' ';
				$editUser->loginEmias = !empty($data['login_emias']) ? $data['login_emias']:' ';
				$editUser->email = $data['email'];
				$editUser->marshserial = (!empty($data["marshserial"]) ? $data["marshserial"] : '');
				$editUser->swtoken = (!empty($data["swtoken"]) ? $data["swtoken"] : '');
				$editUser->swtoken_enddate = (!empty($data["swtoken_enddate"]) ? $data["swtoken_enddate"] : '');
				$editUser->desc = $data['desc'];
				$editUser->medpersonal_id = $data["MedPersonal_id"];
				if (!empty($data['deniedARMs'])) {
					$editUser->deniedarms = $data['deniedARMs'];
				}
				$editUser->blocked = (!empty($data["blocked"]) ? 1 : 0);
				
				// todo: Временное решение, lis из ldap убрали, если lis есть, то нещадно прибиваем. Нужно будет убрать после правильного пересохранения настроек на пользователе
				if (isset($editUser->lis)) {
					$editUser->lis = array();
				}
				
				if ($data['login'] == $_SESSION['login']) {
					$editUser->toSession();
				}

				if ( $is_farmacy_net_admin === true )
					$editUser->post( false, true );
				else
					$editUser->post();
				$this->ReCacheUserData($editUser);
				
				break;
		}

		$this->ReturnData(array('success' => true));
		return true;
	}
	
	/**
	 * Удаляет пользователя, но не совсем, а чуть-чуть
	 */
	function dropUser() {
		if (isSuperadmin() || isLpuAdmin()) {
			$data = $this->ProcessInputData('dropUser', true);
			if ($data === false) { return false; }

			if ($data['user_login'] == $_SESSION['login']) {
				DieWithError('Извините, ритуальные самоубийства запрещены.');
			}
			
			// неприкасаемые пользователи
			$adminSWNT = array("SWNT_ADMIN1", "SWNT_ADMIN2");
			if( in_array($data['user_login'], $adminSWNT) ){
				DieWithError('Пользователь "'.$data['user_login'].'" неприкасаем. Лучше увольтесь.');
			}
			
			if( strtoupper( substr($data['user_login'], 0,4) ) == 'SWNT' && !in_array($data['session']['login'], $adminSWNT)){
				DieWithError('Удаление пользователя "'.$data['user_login'].'" недоступно !!!');
			}
			
			$user = pmAuthUser::find($data['user_login']);
			if (!$user)
				die("{success: false}");

			// Времянка на удаление админов системы админами МО
			// todo: при создании системы прав тут надо будет сделать все согласно ролям
			if (isLpuAdmin() && !isSuperadmin()) { // Если пользователь админ МО
				// то ему запрещено удалять администраторов системы
				if ( $user->isSuperadmin() ) {
					echo json_encode(
						array(
							'success' => false,
							'Error_Msg' =>toUtf('Вы не можете удалить пользователя с правами суперадминистратора!')
						)
					);
					return false;
				}
			}

			$user->post( true );

			$this->load->database();
			$this->load->model("User_model", "dbmodel");
			// пользователь в кэше помечается как удаленный
			$this->dbmodel->deleteUserOfCache(array('pmUser_id' => $user->pmuser_id, 'pmUser_delID' => (!empty($data['pmUser_id']) ? $data['pmUser_id'] : null)));
			echo("{success: true}");
		} else {
			echo("{success: false}");
		}
	}

	/**
	 * Восстанавливает удаленного пользователя
	 */
	function restoreUser() {
		if (isSuperadmin() || isLpuAdmin()) {
			$data = $this->ProcessInputData('restoreUser', true);
			if ($data === false) { return false; }

			$user = pmAuthUser::find($data['user_login']);
			if (!$user)
				die("{success: false}");

			$user->post( false );

			$this->load->database();
			$this->load->model("User_model", "dbmodel");
			// снимаем пометку об удалении в кэше
			$this->dbmodel->restoreUserOfCache(array('pmUser_id' => $user->pmuser_id));
			echo("{success: true}");
		} else {
			echo("{success: false}");
		}
	}
	
	/**
	 * Загрузка сертификата X.509
	 */
	function importUserCert()
	{
		$data = $this->ProcessInputData('importUserCert', true);
		if ($data === false) { return false; }
		
		$allowed_types = explode('|','cer|crt');
		
		if (!isset($_FILES['UserCertFile'])) {
			$this->ReturnError('Не выбран файл сертификата!');
			return false;
		}
		
		if (!is_uploaded_file($_FILES['UserCertFile']['tmp_name']))
		{
			$error = (!isset($_FILES['UserCertFile']['error'])) ? 4 : $_FILES['UserCertFile']['error'];
			switch($error)
			{
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
			
			$this->ReturnError($message);
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['UserCertFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен.');
			return false;
		}
		
		if (!extension_loaded('openssl')) {
			$this->ReturnError('Не подключена библиотека openssl. Импорт сертификатов невозможен.');
			return false;
		}
		
		$cert = file_get_contents($_FILES["UserCertFile"]["tmp_name"]);

		$this->load->helper('openssl');
		$cert = getCertificateFromString($cert);

		$resource = @openssl_x509_read($cert);
		if ($resource === false) {
			$this->ReturnError('Неверный файл сертификата');
			return false;
		}
		
		$fingerprint = null;
		$output = null;
		$result = openssl_x509_export($resource, $output);
		if($result !== false) {
			$cert_base64 = str_replace(array("\r\n", "\n", "\r", "-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----"), "", $output);
			$bin = base64_decode($cert_base64);
			$fingerprint = sha1($bin);
		}
		
		$info = openssl_x509_parse($resource);
		
		$resp = array('success' => true);
		$newcert = array();
		$newcert['cert_id'] = $fingerprint.'_'.$info['serialNumber']; // уникальный идентификатор сертификата
		$newcert['cert_begdate'] = $info['validFrom_time_t'];
		$newcert['cert_enddate'] = $info['validTo_time_t'];
		$newcert['cert_sha1'] = $fingerprint;
		$newcert['cert_base64'] = $cert_base64;
		$newcert['cert_cn'] = !empty($info['subject']['CN']) ? $info['subject']['CN'] : '';
		$newcert['cert_sn'] = !empty($info['subject']['SN']) ? $info['subject']['SN'] : '';
		$newcert['cert_g'] = !empty($info['subject']['GN']) ? $info['subject']['GN'] : '';
		$newcert['cert_name'] = toUtf($_FILES['UserCertFile']['name']);
		$resp['newcert'] = $newcert;
		
		$this->ReturnData($resp);
		
		return true;
	}
	
	/**
	 * Получение списка пользователей
	 */
	function getUsersList() 
	{
		$data = $this->ProcessInputData('getUsersList', true);
		if ($data === false) { return false; }
		
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		$response = $this->dbmodel->getUsersList($data);


		if (!empty($data['withoutPaging']) && $data['withoutPaging']) {
			$this->ProcessModelList($response, true, true)->ReturnData();
		} else {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}

		/*
		$val = array();
		
		if ($data['org'] !== null) {
			if ($data['org'] == 'deleted' && !isSuperadmin() && !isLpuAdmin()) {
				DieWithError('У вас нет прав для просмотра этих пользователей');
			}
			else if ($data['org'] == '0' && !isSuperadmin()) {
				DieWithError('У вас нет прав для просмотра этих пользователей');
			}
			else if ($data['org'] != 'deleted' && !(havingOrg($data['org']) || isSuperadmin())) {
				DieWithError('У вас нет прав для просмотра этих пользователей');
			}
			
			if ($data['org'] == 'deleted') {
				if (isSuperadmin()) {
					$ldap_query = "(organizationalstatus=0)";
				} else {
					$ldap_query = "(&(organizationalstatus=0)(orgid=".implode(')(orgid=', $data['session']['orgs'])."))";
				}
			}
			else if ($data['org'] == '0') {
				$ldap_query = "(&(organizationalstatus=1)(uid=*)(!(orgid=*)))";
			}
			else if ($data['org'] == 'farmnetadmin') {
				if ( isSuperadmin() ) {
					$ldap_query = "(&(organizationalstatus=2)(orgid=*))";
				} else {
					$ldap_query = "(&(organizationalstatus=2)(orgid=" . $_SESSION['OrgNet_id'] . "))";
				}
			}
			else {
				$ldap_query = "(&(organizationalstatus=1)(orgid=".$data['org']."))";
			}
			
			if(!empty($data['login'])) {
				$ldap_query = "(&{$ldap_query}(uid={$data['login']}*))";
			}
			
			if(!empty($data['pmUser_surName'])) {
				$ldap_query = "(&{$ldap_query}(sn=".toUTF($data['pmUser_surName'])."*))";
			}
			
			$users = new pmAuthUsers($ldap_query);
				
			if( !empty($data['group']) ) {
				// это ужоснах конечно, но пока не нашел ничего лучше
				foreach($users->users as $k=>$user) {
					$inGroup = false;
					foreach($user->group as $group ) {
						if($group->name == $data['group']) {
							$inGroup = true;
							break;
						}
					}
					if(!$inGroup) {
						unset($users->users[$k]);
					}
				}
			}

			foreach ($users->users as $user) {
				$user_groups = "";
				foreach ( $user->group as $user_group )
					$user_groups .= (empty($user_groups)?$user_group->name:(", ".$user_group->name));
				$val[] = array(
					'login'=>$user->login,
					'name'=>$user->firname,
					'surname'=>$user->surname,
					'secname'=>$user->secname,
					'desc'=>$user->desc,
					'IsMedPersonal'=>$user->medpersonal_id > 0 ? 'true' : 'false',
					'groups'=>$user_groups
				);
			}
		}
		
		$this->ReturnData($val);*/
	}
	
	/**
	 * Получение списка пользователей из кэша
	 */
	function getUsersListOfCache() {
		$data = $this->ProcessInputData('getUsersListOfCache', true);
		if ($data === false) { return false; }
		
		if ( !havingGroup('OrgAdmin') && !isLpuAdmin() && !isSuperadmin()) {
			$this->ReturnError('У вас нет прав для просмотра этих пользователей');
			return false;
		}

		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		$response = $this->dbmodel->getUsersListOfCache($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 *	Вспомогательная функция для скрипта конвертации Lpu_id и OrgFarmacy_id из поля o в LDAP в поле orgid для пользователей всех аптек и всех лпу.
	 */	 
	function ldapConvertationHelper($ds, $oneorg, $orgsinfo, $field, &$globalorgs, &$notchangeusers, &$usercount) 
	{
		if (!empty($oneorg)) {
			$o = $oneorg[$field];
			$this->textlog->add( 'ldap_search: WHERE: '.LDAP_USER_PATH. '| QUERY: '."o={$o}".'| FIELDS: "uid", "givenname", "secnname", "sn", "uidnumber", "o"' );
			$sr=ldap_search($ds,LDAP_USER_PATH,"o={$o}", array("uid", "givenname", "secnname", "sn", "uidnumber", "o"));
			$entries = ldap_get_entries($ds, $sr);
			
			for( $i=0; $i<$entries['count']; $i++ ){
				if (!empty($entries[$i]['uid'][0])) {
					if (!in_array($entries[$i]['uid'][0],$notchangeusers)) {
						$usercount++;
						if ($usercount % 100 == 0) {
							echo "Обработано {$usercount} записей<br>";
							flush();
							ob_flush();
						}
						// прописываем пользователю все его лпу в org_id
						$orgs = array();
						for( $j=0; $j<$entries[$i]['o']['count']; $j++ ) {
							if (!empty($orgsinfo[$entries[$i]['o'][$j]])) {
								$orgs[] = $orgsinfo[$entries[$i]['o'][$j]];
							}
						}
						$globalorgs = array_merge($globalorgs, $orgs);
						$entry = array("orgid" => $orgs);
						ldap_modify($ds, "uid={$entries[$i]['uid'][0]},".LDAP_USER_PATH, $entry);
						
						// перекэшируем фио пользователя и список его организаций
						$user = array();
						if (!empty($entries[$i]['uidnumber'][0])) {
							$user['pmuser_id'] = $entries[$i]['uidnumber'][0];
						} else {
							// идентификатора нет в ldap
							$newuid = ceil((microtime(true)-strtotime("01 April 2009 00:00"))*10);
							$user['pmuser_id'] = $newuid;
							$entry = array("uidnumber" => array($newuid));
							ldap_modify($ds, "uid={$entries[$i]['uid'][0]},".LDAP_USER_PATH, $entry);
						}
						$user['login'] = $entries[$i]['uid'][0];
						$user['surname'] = (isset($entries[$i]["sn"][0])) ? $entries[$i]["sn"][0]:"";
						$user['firname'] = (isset($entries[$i]["givenname"][0])) ? $entries[$i]["givenname"][0]:"";
						$user['secname'] = (isset($entries[$i]["secnname"][0])) ? $entries[$i]["secnname"][0]:"";
						$this->User_model->ReCacheOrgUserData($user, $orgs);
						// исключаем пользователя из дальнейших операций
						$notchangeusers[] = $entries[$i]['uid'][0];
					}
				}
			}
		}
	}

	/**
	 *	Скрипт замены старого значения конкретного атрибута для всех пользователей LDAP на новое значение
	 */
	function ldapAttributeChange() 
	{
		if (!isSuperadmin()) {
			$this->ReturnError('Недостаточно прав для использования данного функционала.');
			return false;
		}
		
		$data = $this->ProcessInputData('ldapAttributeChange');
		if ( $data === false ) { return false; }

		set_time_limit(0);
		
		$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		if ($ds) {
			if (!$r=@ldap_bind($ds,LDAP_USER,LDAP_PASS)) {
				$this->ReturnError('Перегружен сервер авторизации!');
				return false;
			}
			
			// ищем все записи с атрибутом = старое значение
			$this->textlog->add( 'ldap_search: WHERE: '.LDAP_USER_PATH. '| QUERY: '."{$data['attribute']}={$data['oldValue']}".'| FIELDS: "uid", '."{$data['attribute']}" );
			$sr=ldap_search($ds,LDAP_USER_PATH,"{$data['attribute']}={$data['oldValue']}", array("uid", $data['attribute']));
			$entries = ldap_get_entries($ds, $sr);
			// для каждого меняем атрибут..
			for( $i=0; $i<$entries['count']; $i++ ){
				if (!empty($entries[$i]['uid'][0])) {
					// считываем значения атрибута, кроме старого
					$attrs = array();
					for( $j=0; $j<$entries[$i][$data['attribute']]['count']; $j++ ) {
						if ($entries[$i][$data['attribute']][$j] == $data['oldValue']) {
							$attrs[] = $data['newValue'];
						} else {
							$attrs[] = $entries[$i][$data['attribute']][$j];
						}
					}
					$entry = array($data['attribute'] => $attrs);
					ldap_modify($ds, "uid={$entries[$i]['uid'][0]},".LDAP_USER_PATH, $entry);
				}
			}
			
			$this->ReturnData(array('success' => true));
			return true;
		} else {
			$this->ReturnError("Невозможно соединиться с сервером авторизации.");
			return false;
		}
	}

	/**
	 *	Скрипт конвертации Lpu_id и OrgFarmacy_id из поля o в LDAP в поле orgid для пользователей всех аптек и всех лпу.
	 */
	function ldapConvertationToOrgId() {
		if (!isSuperadmin()) {
			DieWithError('access denied');
		}
		
		$this->load->database();
		$this->load->model("Org_model", "Org_model");
		$this->load->model("User_model", "User_model");
		
		set_time_limit(0);
		
		$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		$isDebug = (int)$this->config->item('IS_DEBUG');
		// костыль для учёток на тестовых серверах (для регионов использующих единый LDAP, но разную бд MSSQL, т.е. могут совпадать Lpu_id, OrgFarmacy_id)
		$notchangeusers = array();
		if ($isDebug) {
			if ($_SESSION['region']['nick'] != 'ufa') {
				$notchangeusers = array_merge($notchangeusers, array('testdoc', 'lputest21', 'ufaadmin', 'UfaAdminLpu1'));
			}
			if ($_SESSION['region']['nick'] != 'pskov') {
				$notchangeusers = array_merge($notchangeusers, array('padmin', 'Kataeva'));
			}
			if ($_SESSION['region']['nick'] != 'khak') {
				$notchangeusers = array_merge($notchangeusers, array('hadmin'));
			}
		}
		
		// массив для организаций, всем организациям у которых есть пользователи проставим флаг доступа в систему.
		$globalorgs = array();
		$usercount = 0;
		
		if ($ds) {
			if (!$r=@ldap_bind($ds,LDAP_USER,LDAP_PASS)) {
				DieWithError('Перегружен сервер авторизации!');
			}

			// все лпу
			$info = $this->Org_model->getLpuAllList(array(), true);
			// массив лпу
			$lpus = array();
			
			foreach($info as $onelpu) {
				$lpus[$onelpu['Lpu_id']] = $onelpu['Org_id'];
			}
			
			foreach($info as $onelpu) {
				$this->ldapConvertationHelper($ds, $onelpu, $lpus, 'Lpu_id', $globalorgs, $notchangeusers, $usercount);
			}
			
			
			// все аптеки
			$info = $this->Org_model->getOrgFarmacyAllList(array(), true);
			// массив аптек
			$orgfarmacys = array();
			
			foreach($info as $oneorgfarmacy) {
				$orgfarmacys[$oneorgfarmacy['OrgFarmacy_id']] = $oneorgfarmacy['Org_id'];
			}
			
			foreach($info as $oneorgfarmacy) {
				$this->ldapConvertationHelper($ds, $oneorgfarmacy, $orgfarmacys, 'OrgFarmacy_id', $globalorgs, $notchangeusers, $usercount);
			}
			
			// проставляем всем организациям с пользователями флаг доступа в систему
			foreach($globalorgs as $oneorg) {
				$params = array();
				$params['Org_id'] = $oneorg;
				$params['grant'] = 2;
				$this->Org_model->giveOrgAccess($params);
			}
			
			ldap_unbind( $ds ); 
			DieWithError("Успешная конвертация.");
		} else {
			DieWithError("Невозможно соединиться с сервером авторизации.");
		}
	}
	
	/**
	 * Функция конвертации групп пользователя 
	 * (всем пользователям с группой like %admin% присваиваем группу OrgAdmin)
	 * (всем пользователям с группой like %user% присваиваем группу OrgUser)
	 *
	 * (необходимо переделывать, т.к. хранение групп сейчас производится
	 * дополнительно в базе данных
	 */
	/*
	function ldapConvertationGroups()
	{
		if (!isSuperadmin()) {
			DieWithError('access denied');
		}
		
		$this->load->database();
		$this->load->model("User_model", "User_model");
		
		set_time_limit(0);
		
		$ds=ldap_connect(LDAP_SERVER,LDAP_SERVER_PORT);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		$usercount = 0;
		
		if ($ds) {
			if (!$r=@ldap_bind($ds,LDAP_USER,LDAP_PASS)) {
				DieWithError('Перегружен сервер авторизации!');
			}
			
			// получаем всех пользователей группы OrgAdmin
			$this->textlog->add( 'ldap_search: WHERE: '.LDAP_GROUP_PATH. '| QUERY: (cn=OrgAdmin)| FIELDS: "member"' );
			$sr=ldap_search($ds,LDAP_GROUP_PATH,"(cn=OrgAdmin)", array('member'));
			$groupentries = ldap_get_entries($ds, $sr);
			// составляем массив таких пользователей
			$alreadyOrgAdminUsers = array();
			for( $i=0; $i<$groupentries['count']; $i++ ) {
				if (!empty($groupentries[$i]['member'])) {
					for( $j=0; $j<$groupentries[$i]['member']['count']; $j++ ) {
						$login = $groupentries[$i]['member'][$j];
						if (!in_array($login, $alreadyOrgAdminUsers)) {
							$alreadyOrgAdminUsers[] = $login;
						}
					}
				}
			}
			
			// получаем всех пользователей группы OrgUser
			$this->textlog->add( 'ldap_search: WHERE: '.LDAP_GROUP_PATH. '| QUERY: (cn=OrgUser)| FIELDS: "member"' );
			$sr=ldap_search($ds,LDAP_GROUP_PATH,"(cn=OrgUser)", array('member'));
			$groupentries = ldap_get_entries($ds, $sr);
			// составляем массив таких пользователей
			$alreadyOrgUserUsers = array();
			for( $i=0; $i<$groupentries['count']; $i++ ) {
				if (!empty($groupentries[$i]['member'])) {
					for( $j=0; $j<$groupentries[$i]['member']['count']; $j++ ) {
						$login = $groupentries[$i]['member'][$j];
						if (!in_array($login, $alreadyOrgUserUsers)) {
							$alreadyOrgUserUsers[] = $login;
						}
					}
				}
			}
			
			// получаем всех пользователей групп like %admin%
			$this->textlog->add( 'ldap_search: WHERE: '.LDAP_GROUP_PATH. '| QUERY: (&(cn=*admin*)(!(cn=SuperAdmin))(!(cn=OrgAdmin)))| FIELDS: "member"' );
			$sr=ldap_search($ds,LDAP_GROUP_PATH,"(&(cn=*admin*)(!(cn=SuperAdmin))(!(cn=OrgAdmin)))", array('member'));
			$groupentries = ldap_get_entries($ds, $sr);
			// составляем массив таких пользователей
			$adminusers = array();
			for( $i=0; $i<$groupentries['count']; $i++ ) {
				if (!empty($groupentries[$i]['member'])) {
					for( $j=0; $j<$groupentries[$i]['member']['count']; $j++ ) {
						$login = $groupentries[$i]['member'][$j];
						if (!in_array($login, $adminusers) && !in_array($login, $alreadyOrgAdminUsers)) {
							$adminusers[] = $login;
						}
					}
				}
			}
			
			// получаем всех пользователей групп like %user%
			$this->textlog->add( 'ldap_search: WHERE: '.LDAP_GROUP_PATH. '| QUERY: (&(cn=*user*)(!(cn=OrgAdmin)))| FIELDS: "member"' );
			$sr=ldap_search($ds,LDAP_GROUP_PATH,"(&(cn=*user*)(!(cn=OrgAdmin)))", array('member'));
			$groupentries = ldap_get_entries($ds, $sr);

			// составляем массив таких пользователей
			$userusers = array();
			for( $i=0; $i<$groupentries['count']; $i++ ) {
				if (!empty($groupentries[$i]['member'])) {
					for( $j=0; $j<$groupentries[$i]['member']['count']; $j++ ) {
						$login = $groupentries[$i]['member'][$j];
						if (!in_array($login, $userusers) && !in_array($login, $alreadyOrgUserUsers)) {
							$userusers[] = $login;
						}
					}
				}
			}
			
			$userallcount = count($adminusers) + count($userusers);
			
			foreach($adminusers as $adminuser) {
				$usercount++;
				if ($usercount % 100 == 0) {
					echo "Обработано {$usercount} записей из {$userallcount}<br>";
					flush();
					ob_flush();
				}
				
				@ldap_mod_add($ds, "cn=OrgAdmin,".LDAP_GROUP_PATH, array("member"=> $adminuser));
			}
			
			foreach($userusers as $useruser) {
				$usercount++;
				if ($usercount % 100 == 0) {
					echo "Обработано {$usercount} записей из {$userallcount}<br>";
					flush();
					ob_flush();
				}
				
				@ldap_mod_add($ds, "cn=OrgUser,".LDAP_GROUP_PATH, array("member"=> $useruser));
			}
			
			ldap_unbind( $ds ); 
			echo "Обработано {$usercount} записей из {$userallcount}<br>";
			DieWithError("Успешная конвертация.");						
		} else {
			DieWithError("Невозможно соединиться с сервером авторизации.");
		}
	}
	*/
	/**
	 * updateMedPersonalId
	 */
	function updateMedPersonalId() {
		// update пользователей - не надо запускать!
		if (!isSuperadmin()) {
			DieWithError('access denied');
		}
		DieWithError('access denied');
		$this->load->database();
		$this->load->model("User_model", "User_model");
		
		set_time_limit(0);
		
		$users = $this->User_model->getUsersWithInvalidMedPersonalId(array());
		$i = 0;
		foreach($users as $user) {
			$ldapuser = pmAuthUser::find($user['PMUser_Login']);
			if (!$ldapuser)
				echo 'Не удалось найти пользователя '.$user['PMUser_Login'].'<br/>';
			else {
				if ($ldapuser->medpersonal_id!=$user['medpersonal_id']) {
					$i++;
					$ldapuser->medpersonal_id = $user['medpersonal_id'];
					$ldapuser->post();
				}
			}
		}
		echo 'Апдейт записей завершен. Измененных записей: '.$i.' из '.count($users).'<br/>';
	}
	
	/**
	*	Синхронизация данных кэша с данными из лдапа
	*/
	function syncLdapAndCacheUserData() {
		ini_set('max_execution_time', 0);

		$data = $this->ProcessInputData('syncLdapAndCacheUserData', false);
		if ($data === false) { return false; }
		
		$user = pmAuthUser::find($_SESSION['login']);
		if ( !havingGroup('OrgAdmin') && !isLpuAdmin() && !isSuperAdmin()) {
			DieWithError('У вас нет прав для перекэширования данных!');
		}
		
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		$ldap_query = "";
		
		$data['session'] = $_SESSION;
		$data['org'] = $data['Org_id'];
		$data['withoutPaging'] = true;
		
		$cache_users = $this->dbmodel->getUsersList($data);
		// print_r($cache_users); die();
		if (isset($data['Org_id']) && $data['Org_id'] != null) {
			// Пользователи конкретных организаций
			if (is_numeric($data['Org_id']) && $data['Org_id'] > 0) {
				$ldap_query = "(&(organizationalstatus=1)(orgid=".$data['Org_id']."))";
			}
			// Удалённые пользователи
			if ($data['Org_id'] == 'deleted') {
				$ldap_query = "(organizationalstatus=0)";
			}
			// Прочие пользователи (пользователи без организации)
			if ($data['Org_id'] == '0') {
				$ldap_query = "(&(organizationalstatus=1)(!(orgid=*)))";
			}
			// Администраторы сети аптек
			if ($data['Org_id'] == 'farmnetadmin') {
				$ldap_query = "(organizationalstatus=2)";
			}
			
			if (!empty($ldap_query)) {
				$ldap_users = new pmAuthUsers($ldap_query);
				
				foreach($ldap_users->users as $lu) {
					// Обновляем данные по пользователю в кэше
					$this->ReCacheUserData($lu);
					foreach($cache_users as $key => $cu) {
						if ($cu['pmUser_id'] == $lu->pmuser_id) {
							unset($cache_users[$key]);
						}
					}
				}
				
				// все что осталось в $cache_users это удаленные(из лдапа) пользователи
				foreach($cache_users as $cu) {
					if ($data['Org_id'] == 'deleted') {
						// отсутсвующих в удаленнных удаляем из кэша.
						$this->dbmodel->deleteUserOfCache($cu, true);
					} else {
						$this->dbmodel->deleteUserOfCache($cu, false);
					}
				}
			}
		}

		$this->ReturnData(array('success' => true));
	}
	
	/**
	 * Получение списка пользователей
	 */
	function getCurrentOrgUsersList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		$data = getSessionParams();

		if ( !empty($data['session']['org_id']) ) {
			$response = $this->dbmodel->getCurrentOrgUsersList($data['session']['org_id']);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
		else {
			$this->ReturnData(array());
		}

		return true;
	}

	/**
	 * Построение дерева по списку ЛПУ, доступных для данного пользователя
	 */
	function getOrgUsersTree() {
		$this->load->helper('Text');
		
		$data = $this->ProcessInputData('getOrgUsersTree', true, false);
		if ( isset($_SESSION['OrgNet_id']) )
			$data['OrgNet_id'] = $_SESSION['OrgNet_id'];

		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$val = array();
		$superadmin = isSuperadmin();
		$user = pmAuthUser::find($_SESSION['login']);
		$farmacynetadmin = $user->havingGroup('FarmacyNetAdmin');
		$lpuadmin = $user->havingGroup('LpuAdmin');
		$orgadmin = $user->havingGroup('OrgAdmin');
		if (!$user)
			die();
			
		if ( $data['node'] == 'root' ) {
			$val[] = array ('text'=>
				toUTF(
				"Все"),
				'id'=>"all",
				'leaf'=>true,
				'cls'=>'file');
				
			if ( $superadmin ) {
				$val[] = array ('text'=>
					toUTF(
					"Прочие пользователи"),
					'id'=>"0",
					'leaf'=>true,
					'cls'=>'file');
			}
			
			if ( $superadmin || $lpuadmin || $orgadmin) {
				$val[] = array ('text'=>
					toUTF(
					"Удаленные пользователи"),
					'id'=>"deleted",
					'leaf'=>true,
					'cls'=>'file');
			}
			
			if ( $superadmin || $farmacynetadmin ) {
				$val[] = array ('text'=>
					toUTF(
					"Администраторы сети аптек"),
					'id'=>"farmnetadmin",
					'leaf'=>true,
					'cls'=>'file'
				);
			}
			
			if ($superadmin) {
				// для суперадмина все организации разделенные на группы
				$info = $this->dbmodel->getOrgTypeTree($data);
				foreach ($info as $rows) {
					$val[] = array ('text'=> toUtf(trim($rows['OrgType_Name'])),
						'id'=>$rows['OrgType_id'],
						'leaf'=>false,
						'cls'=>'file',
						'iconCls'=>'org16',
						'object'=>'OrgType');
				}
				$val[] = array ('text'=> toUtf('Прочие организации'),
					'id'=> 'other',
					'leaf'=>false,
					'cls'=>'file',
					'iconCls'=>'org16',
					'object'=>'OrgType');
			} else {
				// для не суперамдина - только своя организация
				$info = $this->dbmodel->getOrgUsersTree($data, $superadmin);
				if ( $info != false && count($info) > 0 ) {
					foreach ($info as $rows) {
						$val[] = array ('text'=> toUtf(trim($rows['Org_Nick'])),
							'id'=>$rows['Org_id'],
							'leaf'=>true,
							'cls'=>'file',
							'iconCls'=>($rows['Org_isAccess'] == 1)?'org-denied16':'spr-org16',
							'object'=>($rows['Org_isAccess'] == 1)?'OrgDenied':'Org');
					}
				}
			}			
		} elseif (!empty($data['node']) && ($data['node'] > 0 || $data['node'] == 'other')) {
			$info = $this->dbmodel->getOrgUsersTree($data, $superadmin);
			if ( $info != false && count($info) > 0 ) {
				foreach ($info as $rows) {
					$val[] = array ('text'=> toUtf(trim($rows['Org_Nick'])),
						'id'=>$rows['Org_id'],
						'leaf'=>true,
						'cls'=>'file',
						'iconCls'=>($rows['Org_isAccess'] == 1)?'org-denied16':'spr-org16',
						'object'=>($rows['Org_isAccess'] == 1)?'OrgDenied':'Org');
				}
			}
		}
		
		$json=json_encode($val);
		echo $json;

	}

	/**
	 * Получение списка групп, недоступных для выбора
	 */
	protected function _getDeniedGroupsList($mode = 'all') {
		if ( $mode == 'lpuadmin' ) {
			return array('SuperAdmin', 'FarmacyAdmin', 'FarmacyUser', 'FarmacyNetAdmin','CallCenterAdmin', 'RosZdrNadzorView', 'OuzChief', 'OuzAdmin', 'OuzUser',
				'OuzSpec', /*'CardEditUser',*/ 'OuzSpecMPC', 'TFOMSUser', 'SMOUser', 'epidem', 'epidem_ufa', 'OKSRegistry', 'DLOAccess', /*'OperPregnRegistry',*/
				'EndoRegistry', 'AdminLLO', 'SuicideRegistry', 'ZagsUser', 'OperRegBirth', 'IPRARegistryEdit', 'MedPersView', '106', 'APIUser', 'AdminOrgReference',
				'DispCallNMP', 'DispDirNMP', 'NMPGrandDoc', 'minzdravdlo', 'EGISSOAdmin', 'RzhdRegistry', 'PM', 'MIACStat', 'MIACMonitoring', 'MIACSysAdmin', 
				'MIACSuperAdmin', 'MIACAdminFRMR', 'EditingMES', 'hivresearch'
			);
		}
		else {
			return array('SuperAdmin', 'CardCloseUser', 'CardEditUser','OperatorCallCenter', 'FarmacyAdmin', 'FarmacyUser', 'FarmacyNetAdmin', 'RegAdmin',
				'CallCenterAdmin', 'MPCModer', 'VenerRegistry', 'VznRegistry', 'HIVRegistry', 'HepatitisRegistry', 'NarkoRegistry', 'OnkoRegistry', 'OnkoRegistryFullAccess',
				'Orphan', 'CrazyRegistry', 'DiabetesRegistry', 'TubRegistry', 'LpuAdmin', 'LpuUser', 'LpuPowerUser', 'RosZdrNadzorView', 'OuzChief', 'OuzAdmin', 'OuzUser',
				'OuzSpec', 'OuzSpecMPC', 'TFOMSUser', 'SMOUser', 'epidem', 'epidem_ufa', 'OKSRegistry', 'DLOAccess',  'OperPregnRegistry', 'EndoRegistry', 'AdminLLO',
				'SuicideRegistry', 'ZagsUser', 'OperRegBirth', 'IPRARegistryEdit', 'MedPersView', '106', 'APIUser', 'AdminOrgReference', 'DispCallNMP', 'DispDirNMP',
				'NMPGrandDoc', 'minzdravdlo', 'EGISSOAdmin', 'EGISSOUser','RzhdRegistry', 'PM', 'MIACStat', 'MIACMonitoring', 'MIACSysAdmin', 'MIACSuperAdmin', 
				'MIACAdminFRMR', 'EditingMES', 'hivresearch'
			);
		}
	}

	/**
	 * Получение списка ролей пользователей
	 */
	function getUsersGroupsList() {
		$this->load->helper('Text');
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		//$groups = new pmAuthGroups();
		$groups = $this->dbmodel->getGroupsDB();
		
		$user = pmAuthUser::find($_SESSION['login']);
		if (!$user)
			die();
		
		$superadmin = $user->havingGroup('SuperAdmin');
		$lpuadmin = $user->havingGroup('LpuAdmin');
		$orgadmin = $user->havingGroup('OrgAdmin');
			
		$val = array();

		if($superadmin && isset($_POST['onlyFarmacy']) || isset($_SESSION['OrgFarmacy_id'])) {
			foreach ( $groups as $rows ) {
				if ( $rows->name == 'FarmacyAdmin' || $rows->name == 'FarmacyUser' || $rows->name == 'FarmacyNetAdmin' )
					$val[] = array('Group_id' => $rows->id,
						'Group_Name' => $rows->name,
						'Group_Desc' => $rows->desc,
						'Group_IsOnly' => $rows->isonly,
						'Group_IsBlocked' => $rows->isblocked
					);
			}
		}
		else {
			if ( $superadmin ) {
				foreach ( $groups as $rows ) {
					$val[] = array('Group_id' => $rows->id,
						'Group_Name' => $rows->name,
						'Group_Desc' => $rows->desc,
						'Group_IsOnly' => $rows->isonly,
						'Group_IsBlocked' => $rows->isblocked
					);

				}
			} elseif ($lpuadmin) { // для Админов ЛПУ только определенные группы
				foreach ( $groups as $rows ) {
					if (!in_array($rows->name, $this->_getDeniedGroupsList('lpuadmin')) && !(getRegionNick() == 'yaroslavl' && $rows->isblocked == 2)) {
						$val[] = array('Group_id' => $rows->id,
							'Group_Name' => $rows->name,
							'Group_Desc' => $rows->desc,
							'Group_IsOnly' => $rows->isonly,
							'Group_IsBlocked' => $rows->isblocked
						);
					}
				}
			} elseif ($orgadmin) { // todo: на всякий случай, нужно дорабатывать этот список 
				foreach ( $groups as $rows ) {
					if (!in_array($rows->name, $this->_getDeniedGroupsList()) && !(getRegionNick() == 'yaroslavl' && $rows->isblocked == 2)) {
						$val[] = array('Group_id' => $rows->id,
							'Group_Name' => $rows->name,
							'Group_Desc' => $rows->desc,
							'Group_IsOnly' => $rows->isonly,
							'Group_IsBlocked' => $rows->isblocked
						);
					}
				}
			}
		}

		$json = json_encode($val);
		echo $json;
	}

	/**
	 * Получение списка ЛПУ, к которым принадлежит пользователь
	 */
	function getOwnedLpuList() 
	{
		$this->load->database();
		$this->load->model("Org_model", "Org_model");
		$val=array();
		if (isset($_SESSION['login'])) {
			if ($user = pmAuthUser::find($_SESSION['login'])) {
				foreach($user->org as $oneorg) {
					$lpu_id = $this->Org_model->getLpuOnOrg(array('Org_id' => $oneorg['org_id']));
					if (!empty($lpu_id)) {
						$val[] = array('lpu_id' => $lpu_id);
					}
				}
			}
		}
		$json=json_encode($val);
		echo $json;
	}

	/**
	 * Получение списка ТОУЗ, к которым принадлежит пользователь
	 */
	function getOwnedTouzLpuList() 
	{
		$this->load->database();
		$orgs = array();
		if (isset($_SESSION['login'])) {
			if ($user = pmAuthUser::find($_SESSION['login'])) {
				foreach($user->org as $oneorg) {
					$orgs[] = $oneorg['org_id'];
				}
				
				$this->load->model("Org_model", "Org_model");
				if (is_array($orgs) && count($orgs) > 0) {
					$this->ReturnData(array('TOUZLpuArr' => $this->Org_model->getTouzOrgs(array('orgs' => $orgs))));
					return true;
				}
			}
		}
		
		$this->ReturnData(array('TOUZLpuArr' => array()));
		return true;
	}
	
	
	/**
	 * Получение списка аптек, к которым принадлежит пользователь
	 */
	function getOwnedFarmacyList() {
		$val=array();
		$data = array();
		$data = array_merge($data, getSessionParams());
		
		if (isset($_SESSION['login'])) {
			if ($user = pmAuthUser::find($_SESSION['login'])) {
				// для сетевого админа тянем из базы
				if ( isset($_SESSION['isFarmacyNetAdmin']) && $_SESSION['isFarmacyNetAdmin'] === true )
				{
					$this->load->database();
					$this->load->model("User_model", "dbmodel");
					$info = $this->dbmodel->getNetAdminFarmacies($data);
					foreach ($info as $rows) {
						$val[] = array (
							'lpu_id' => $rows['OrgFarmacy_id'],
							'server_id' => $rows['OrgFarmacy_id']
						);
					}
				}
				else
					$val = $user->lpu;
			}
		}
		$json=json_encode($val);
		echo $json;
	}
	/**
	 * changeLpu
	 */
	function changeLpu($data) {
		if (!isset($data['Lpu_id'])) {
			return false;
		}
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$this->load->model("Org_model", "Org_model");
			
		$info = $this->dbmodel->getCurrentLpuData($data);
		
		if ( isset($info[0]) && (havingOrg($info[0]['Org_id']) || isSuperadmin() || havingGroup('RosZdrNadzorView') || havingGroup('MedPersView') || havingGroup('OuzUser') || havingGroup('OuzAdmin') || havingGroup('OuzChief') || havingGroup('OuzSpec') || havingGroup('OuzSpecMPC')) ) {
			$_SESSION['lpu_id'] = $data['Lpu_id'];
			$_SESSION['server_id'] = $data['Lpu_id'];
			$_SESSION['org_id'] = $info[0]['Org_id'];
			$_SESSION['Org_Name'] = $info[0]['Org_Name'];
			$orgtype = $this->Org_model->getOrgType(array('Org_id' => $info[0]['Org_id']));
			if (!empty($orgtype['OrgType_SysNick'])) {
				$_SESSION['orgtype'] = $orgtype['OrgType_SysNick'];
				$_SESSION['orgtype_id'] = $orgtype['OrgType_id'];
			} else {
				$_SESSION['orgtype'] = '';
				$_SESSION['orgtype_id'] = null;
			}
			$_SESSION['isMedStatUser'] = $this->dbmodel->isMedStatUser();
			$_SESSION['isPathoMorphoUser'] = $this->dbmodel->isPathoMorphoUser();
			$_SESSION['linkedLpuIdList'] = $this->Org_model->getLinkedLpuIdList(array('Lpu_id' => $data['Lpu_id']));
			$_SESSION['lpuIsTransit'] = $this->Org_model->getLpuIsTransit(array('Lpu_id' => $data['Lpu_id']));
			$val['success'] = true;
			$val['Lpu_id'] = $_SESSION['lpu_id'];
			$val['Org_id'] = $_SESSION['org_id'];
			$val['pmuser_id'] = $_SESSION['pmuser_id'];
			$val['linkedLpuIdList'] = $_SESSION['linkedLpuIdList'];
			$val['lpuIsTransit'] = $_SESSION['lpuIsTransit'];
			
			
			
			$val['Lpu_Nick'] = $info[0]['Lpu_Nick'];
			$val['Lpu_SysNick'] = $info[0]['Lpu_SysNick'];
			$val['Lpu_Name'] = $info[0]['Lpu_Name'];
			$val['Lpu_Email'] = $info[0]['Lpu_Email'];
			$val["LpuLevel_id"] = $info[0]['LpuLevel_id'];
			$val["LpuLevel_Code"] = $info[0]['LpuLevel_Code'];
			$val["Lpu_IsDMS"] = $info[0]['Lpu_IsDMS'];
			
			// Здесь выбрать medstafffact_id (или несклько для этого врача)
			// нужна для того чтобы выбрать места работы согласно выбранному ЛПУ 
			$data['MedPersonal_id'] = $_SESSION['medpersonal_id'];
			$ms = $this->dbmodel->getMedStaffFact($data);
			$msf = "";
			$i=0;
			$_SESSION['MedStaffFact'] = array();
			if (is_array($ms)) {
				foreach ($ms as $row) {
					$_SESSION['MedStaffFact'][$i] = $row['MedStaffFact_id'];
					$i++;
				}
			}
			if (isset($_SESSION['MedStaffFact']))
				$val['medstafffact'] = $_SESSION['MedStaffFact'];
			else 
				$val['medstafffact'] = array();

			//Обновляется расчет прав доступа при смене ЛПУ
			$this->load->model('AccessRights_model', 'armodel');
			$_SESSION['access_rights'] = $this->armodel->getAccessRightsForUser(array(
				'pmUser_id' => $_SESSION['pmuser_id'], 'MedPersonal_id' => $_SESSION['medpersonal_id'], 'Lpus' => array($_SESSION['lpu_id']), 'UserGroups' => explode('|', $_SESSION['groups'])
			));

			return $val;
		} else {
			return false;
		}
	}
	/**
	* changeCurLpu
	*/
	function changeCurLpu($data){
		if (!isset($data['Lpu_id'])) {
			return false;
		}
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$this->load->model("Org_model", "Org_model");
			
		$info = $this->dbmodel->getCurrentLpuData($data);

		$_SESSION['lpu_id'] = $data['Lpu_id'];
		$_SESSION['server_id'] = $data['Lpu_id'];
		$val['success'] = true;
		$val['Lpu_id'] = $_SESSION['lpu_id'];
		$val['Org_id'] = $_SESSION['org_id'];
		$val['pmuser_id'] = $_SESSION['pmuser_id'];
		if(isset($info[0])){
			$val['Lpu_Nick'] = $info[0]['Lpu_Nick'];
			$val['Lpu_SysNick'] = $info[0]['Lpu_SysNick'];
			$val['Lpu_Name'] = $info[0]['Lpu_Name'];
			$val['Lpu_Email'] = $info[0]['Lpu_Email'];
			$val["LpuLevel_id"] = $info[0]['LpuLevel_id'];
			$val["LpuLevel_Code"] = $info[0]['LpuLevel_Code'];
			$val["Lpu_IsDMS"] = $info[0]['Lpu_IsDMS'];
		}
		return $val;
	}

	/**
	 * Cохранение текущего выбранного ЛПУ в сессии
	 */
	function setCurrentLpu() {
		$data = $this->ProcessInputData('setCurrentLpu', true, false);
		if ($data === false) { return false; }

		$result = $this->changeLpu($data);

		if ($result !== false) {
			$this->ReturnData($result);
		} else  {
			$this->ReturnError('Невозможно выбрать ЛПУ, так как пользователь не привязан к этому ЛПУ!');
		}

		return true;
	}

	/**
	 * changeCurrentLpu
	 */
	function changeCurrentLpu() {
		$data = $this->ProcessInputData('changeCurrentLpu', true, false);
		if ($data === false) { return false; }

		$result = $this->changeCurLpu($data);

		if ($result !== false) {
			$this->ReturnData($result);
		} else  {
			$this->ReturnError('Ошибка при смене МО');
		}

		return true;
	}
	
	/**
	 * Cохранение текущей выбранной аптеки в сессии
	 */
	function setCurrentFarmacy() {
		$data = $this->ProcessInputData('setCurrentFarmacy', true, false);
		if ($data === false) { return false; }

		$user = pmAuthUser::find($_SESSION['login']);
		if (!isset($data['OrgFarmacy_id'])) {
			$data['OrgFarmacy_id'] = $_SESSION['OrgFarmacy_id'];
		}
		
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		$info = $this->dbmodel->getCurrentOrgFarmacyData($data);
		
		if ( isset($info[0]) && (havingOrg($info[0]['Org_id']) || isSuperadmin() || (isset($_SESSION['isFarmacyNetAdmin']) && $_SESSION['isFarmacyNetAdmin'] === true )) ) {
			$_SESSION['OrgFarmacy_id']=$data['OrgFarmacy_id'];
			$_SESSION['FarmacyOtdel_id']=$data['DrugFinance_id'];
			$_SESSION['FarmacyOtdel_Name']=$data['FarmacyOtdel_Name'];
			$_SESSION['org_id'] = $info[0]['Org_id'];
			$_SESSION['Org_Name'] = $info[0]['Org_Name'];

			$val['success'] = true;
			$val['Lpu_id'] = $_SESSION['lpu_id'];

			$val['OrgFarmacy_Nick'] = toUTF($info[0]['OrgFarmacy_Nick']);
			$json=json_encode($val);
			$inf_c = $this->dbmodel->getCurrentOrgFarmacyContragent($data);

			if (!$inf_c) {
				$inf_c = $this->dbmodel->getCurrentOrgContragent($data);
			}

			if ($inf_c) {
				$val['Contragent_id'] = $inf_c[0]['Contragent_id'];
				$val['FarmacyOtdel_id'] = $data['DrugFinance_id'];
				$val['FarmacyOtdel_Name'] = $data['FarmacyOtdel_Name'];
				$val['Org_pid'] = $inf_c[0]['Org_pid'];
				$val['Contragent_Name'] = toUTF($inf_c[0]['Contragent_Name']);
				$_SESSION['FarmacyOtdel_id']=$data['DrugFinance_id'];
				$_SESSION['Contragent_id'] = $inf_c[0]['Contragent_id'];
				$_SESSION['Org_pid'] = $inf_c[0]['Org_pid'];
			}
			$json=json_encode($val);
		}
		else {
			$json=json_encode(array('success' => false, 'Error_Msg' => toUTF('Невозможно выбрать ЛПУ, так как пользователь не привязан к этому ЛПУ!')));
		}
		echo $json;
	}

	/**
	 * Получение списка мест работы для врача
	 */
	function loadAccessGridPanel() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$this->load->model("Lpu_model");
		$this->load->model("Org_model");

		$data = $this->ProcessInputData('loadAccessGridPanel', true);
		if ($data === false) { return false; }

		$data['MedService_id'] = null;
		// места работы надо получить для всех указанных у пользователя организаций
		$response = array();

		if (!empty($data['Groups'])) {
			$groups = json_decode($data['Groups'], true);
			$data['Groups'] = array();
			foreach($groups as $group) {
				$obj = new stdClass();
				$obj->name = $group;
				$data['Groups'][] = $obj;
			}
		} else {
			$data['Groups'] = null;
		}

		if (!empty($data['Orgs'])) {
			$data['Orgs'] = json_decode($data['Orgs'], true);
			foreach($data['Orgs'] as $oneOrg) {
				$data['Lpu_id'] = $this->Lpu_model->getLpuByOrg([
					'Org_id' => $oneOrg
				]);

				$data['session']['orgtype'] = 'lpu';
				$res = $this->Org_model->getOrgSysNick([
						'Org_id' => $oneOrg
				]);
				
				if (empty($data['Lpu_id'])) {
					$data['Lpu_id'] = null;
					$data['session']['lpu_id'] = null;
					$data['session']['orgtype']  = ($res) ? $res : 'org';
				} else {
					$data['session']['lpu_id'] = $data['Lpu_id'];
					$data['session']['orgtype']  = ($res) ? $res : 'lpu';
				}

				$data['session']['org_id'] = $oneOrg;
				$data['Need_all'] = 1;
				$result = $this->dbmodel->getUserMedStaffFactList($data);
				$response = array_merge($response, $this->dbmodel->getArmsForMedStaffFactList($data, $result));
			}
		}

		// если задан логин, достаём матрицу запретов из пользователя
		$deniedarms = null;
		if (!empty($data['login'])) {
			$user = pmAuthUser::find($data['login']);
			if ($user) {
				$deniedarms = $user->deniedarms;
			}
		}

		$ArmsWithoutAccess = array();
		if (!empty($deniedarms)) {
			$ArmsWithoutAccess = json_decode($deniedarms, true);
		}

		$out_resp = array();
		// обрабатываем как нам надо
		$i = 0;
		foreach($response as $oneresp) {
			$i++;

			$ArmAccess_Params = json_encode(array(
				'l' => !empty($oneresp['Lpu_id'])?$oneresp['Lpu_id']:null,
				'o' => $oneresp['Org_id'],
				'msf' => $oneresp['MedStaffFact_id'],
				'ls' => $oneresp['LpuSection_id'],
				'lsp' => $oneresp['LpuSectionProfile_id'],
				'ms' => $oneresp['MedService_id'],
				'at' => $oneresp['ARMType']
			));

			$ArmAccess_HasAccess = true;
			if (in_array($ArmAccess_Params, $ArmsWithoutAccess)) {
				$ArmAccess_HasAccess = false;
			}

			/*if (!isset($oneresp['Org_Nick'])) {
				var_dump($oneresp);
			}*/
			$out_resp[] = array(
				'ArmAccess_id' => $i,
				'ArmAccess_Params' => $ArmAccess_Params, // параметры рабочего места
				'ArmAccess_WorkPlace' => $oneresp['ARMName'].' / '.$oneresp['Org_Nick'].(!empty($oneresp['LpuSection_Nick'])?(' / '.$oneresp['LpuSection_Nick']):'').(!empty($oneresp['PostMed_Name'])?(' / '.$oneresp['PostMed_Name']):''),
				'ArmAccess_HasAccess' => $ArmAccess_HasAccess
			);
		}

		$this->ProcessModelList($out_resp, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка мест работы для врача привязанного к пользователю
	 */
	function getMSFList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		$data = $this->ProcessInputData('getMSFList', true, false);

		if ( $data ) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$result = $this->dbmodel->getUserMedStaffFactList($data);
			$result = $this->dbmodel->getArmsForMedStaffFactList($data, $result);

			$ArmsWithoutAccess = array();
			if (!empty($data['session']['deniedarms'])) {
				$ArmsWithoutAccess = json_decode($data['session']['deniedarms'], true);
			}

			$response = array();
			foreach($result as $oneresp) {
				$IsLocalSMP = $this->config->item('IsLocalSMP');
				$LocalSMP_MedServices = $this->config->item('LocalSMP_MedServices');
				if ($IsLocalSMP === true) {
					if (empty($LocalSMP_MedServices) || !in_array($oneresp['MedService_id'], $LocalSMP_MedServices)) { // только по службе заданной в конфиге
						continue;
					}
				}
				$ArmAccess_Params = json_encode(array(
					'l' => !empty($oneresp['Lpu_id'])?$oneresp['Lpu_id']:null,
					'o' => $oneresp['Org_id'],
					'msf' => $oneresp['MedStaffFact_id'],
					'ls' => $oneresp['LpuSection_id'],
					'lsp' => $oneresp['LpuSectionProfile_id'],
					'ms' => $oneresp['MedService_id'],
					'at' => $oneresp['ARMType']
				));
				// Для оперативных отделов в Перми, название АРМ-а диспетчера подстанции меняется на АРМ диспетчера отправлющей части.
				if($oneresp['ARMType'] == 'smpdispatchstation' && $_SESSION['region']['nick'] == 'perm' && $oneresp['SmpUnitType_Code'] == 4) {
						$oneresp['ARMName'] = 'АРМ диспетчера отправляющей части';
				}
				if (!in_array($ArmAccess_Params, $ArmsWithoutAccess)) {
					if ($oneresp['ARMType'] == 'paidservice' || $oneresp['ARMType'] == 'profosmotr') {
						// надо подтянуть инфу по услуге и пункту обслуживания
						$this->load->model('MedServiceElectronicQueue_model');
						$info = $this->MedServiceElectronicQueue_model->getInfoForMSFList($oneresp);
						if (!empty($info[0]['ElectronicService_id'])) {
							$oneresp['ElectronicService_id'] = $info[0]['ElectronicService_id'];
							$oneresp['UslugaComplexMedService_id'] = $info[0]['UslugaComplexMedService_id'];
							$oneresp['UslugaComplex_id'] = $info[0]['UslugaComplex_id'];
							$oneresp['ElectronicService_Name'] = $info[0]['ElectronicService_Name'];
							$oneresp['UslugaComplex_Name'] = $info[0]['UslugaComplex_Name'];
							$response[] = $oneresp;
						}
					} else {
						$response[] = $oneresp;
					}
				}
			}

			// Сохраняем ид. армов в сессию
			$_SESSION['ARMList'] = array();
			foreach($response as $r) {
				if( isset($r['ARMType']) && !in_array($r['ARMType'], $_SESSION['ARMList']) ) {
					$_SESSION['ARMList'][] = $r['ARMType'];
				}
			}

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Функция возвращает список всех имеющихся в системе армов 
	 * (пока хранится непосредственно в модели)
	 */
	function getARMList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		return $this->dbmodel->getARMList();
	}

	/**
	 * Функция возвращает список всех имеющихся в системе типов армов
	 * (пока хранится непосредственно в модели)
	 */
	function getARMTypeList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$response = $this->dbmodel->getARMTypeList();
		echo @json_encode($response);
	}

	/**
	 * Cохранение текущего выбранного места работы врача в сессии
	 */
	function setCurrentMSF() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		//Сессию спецом не закрываем, чтобы следующие запросы не выполнялись, пока не выбрано место работы
		$data = $this->ProcessInputData('setCurrentMSF', true, false);
		
		$res = $this->dbmodel->getMedStaffFactData($data);
		array_walk($res, 'ConvertFromWin1251ToUTF8');
		
		$_SESSION['CurMedStaffFact_id'] = $data['MedStaffFact_id'];
		//Определение типа рабочего места по месту работы врача
		$ARMType = $this->dbmodel->defineARMType($res);
		$json = json_encode(
			array(
				'success' => true,
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'LpuSection_id' => $res['LpuSection_id'],
				'LpuSectionProfile_SysNick' => $res['LpuSectionProfile_SysNick'],
				'LpuSectionProfile_Code' => $res['LpuSectionProfile_Code'],
				'MedPersonal_id' => $res['MedPersonal_id'],
				'LpuSectionProfile_id' => $res['LpuSectionProfile_id'],
				'LpuUnitType_id' => $res['LpuUnitType_id'],
				'LpuUnitType_SysNick' => $res['LpuUnitType_SysNick'],
				'PostMed_id' => $res['PostMed_id'],
				'PostMed_Code' => $res['PostMed_Code'],
				'ARMType' => $ARMType,
				'LpuSection_Name' => $res['LpuSection_Name'],
				'MedPersonal_FIO' => $res['MedPersonal_FIO'],
			)
		);
		echo $json;
	}
	
	/**
	 * Cохранение текущего АРМа (места работы врача ) в сессии
	 */
	function setCurrentARM() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		//Сессию спецом не закрываем, чтобы следующие запросы не выполнялись, пока не выбрано место работы
		$data = $this->ProcessInputData('setCurrentARM', true, false);
		if ($data == false) return false;
		$data['lpu'] = null;
		// Врача всегда берем из сессии 
		$data['MedPersonal_id'] = $_SESSION['medpersonal_id'];
		$data['Lpu_id'] = (empty($data['Lpu_id']))?$_SESSION['lpu_id']:$data['Lpu_id'];
		if ($data['Lpu_id'] != $_SESSION['lpu_id']) {
			// Поменялось ЛПУ 
			$result = $this->changeLpu($data);
			if ($result !== false) {
				$data['lpu'] = $result;
			}
		}

		$res = $this->dbmodel->getUserMedStaffFactList($data); // фильтруем места работы по MedStaffFact_id / MedService_id с целью найти только выбранное место работы
		$res = $this->dbmodel->getArmsForMedStaffFactList($data, $res); // получаем армы для мест работы + групп пользователя

		if (is_array($res) && count($res) > 0 ) {
			array_walk($res, 'ConvertFromWin1251ToUTF8');
		}
		else {
			$res = array();
		}

		// оставляем только АРМ выбранного типа
		foreach ($res as $key => $record) {
			if($record['ARMType'] !== strtolower($data['ARMType'])){
				unset($res[$key]);
			}
		}
		// ресетим нумерацию, т.к. могла нарушиться после unset'ов
		$res = array_values($res);
		// если ничего не осталось, значит что то пошло не так
		if (empty($res[0])) {
			$this->ReturnError('Ошибка выбора места работы');
			return false;
		}

		if ($res[0]['ARMType'] == 'paidservice' || $res[0]['ARMType'] == 'profosmotr') {
			// надо подтянуть инфу по услуге и пункту обслуживания
			$this->load->model('MedServiceElectronicQueue_model');
			$info = $this->MedServiceElectronicQueue_model->getInfoForMSFList($res[0]);
			if (!empty($info[0]['ElectronicService_id'])) {
				$res[0]['ElectronicService_id'] = $info[0]['ElectronicService_id'];
				$res[0]['UslugaComplexMedService_id'] = $info[0]['UslugaComplexMedService_id'];
				$res[0]['UslugaComplex_id'] = $info[0]['UslugaComplex_id'];
				$res[0]['ElectronicService_Name'] = $info[0]['ElectronicService_Name'];
				$res[0]['UslugaComplex_Name'] = $info[0]['UslugaComplex_Name'];
			}
		}
		
		if (in_array($res[0]['ARMType'], ['polka', 'common'])) {
			//достаем атрибуты отделения
			$this->load->model('LpuStructure_model');
			$info = $this->LpuStructure_model->getLpuSectionAttributes($res[0]);
			//пока проверяем на код 13, потом можно будет все грузить
			$res[0]['LpuSectionAttributes'] = ($info!=false and count($info)>0) ? '13' : null;
		}

		// берём данные по рабочему месту из отфильтрованного места работы
		$_SESSION['CurARM'] = $res[0]; // загоняем в сессию все данные по текущему арму, чтобы использовать в других местах.
		// некоторые данные в сессию уже загонялись, пусть остаются отдельно
		$_SESSION['CurMedStaffFact_id'] = !empty($res[0]['MedStaffFact_id'])?$res[0]['MedStaffFact_id']:null;
		$_SESSION['CurLpuSection_id'] = !empty($res[0]['LpuSection_id'])?$res[0]['LpuSection_id']:null;
		$_SESSION['CurMedService_id'] = !empty($res[0]['MedService_id'])?$res[0]['MedService_id']:null;
		$_SESSION['CurLpuUnit_id'] = !empty($res[0]['LpuUnit_id'])?$res[0]['LpuUnit_id']:null;
		$_SESSION['CurArmType'] = !empty($res[0]['ARMType'])?$res[0]['ARMType']:null;
		$res[0]['lpu'] = $data['lpu'];

		$this->ProcessModelList($res, true, true)->ReturnData();

		return true;
	}
	
	
	/**
	 * Перекеширование данных о человеке в базе
	 */
	function ReCacheUserData($user) {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		return $this->dbmodel->ReCacheUserData($user, $data);
	}
	
	/**
	 * Возвращаем данные по группам из БД в LDAP
	 */
	function recacheGroupFromDB() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		if (!isSuperAdmin()) {
			DieWithError("Доступ запрещен!");
		}
		$data = array();
		if (isset($_GET['user'])) {
			$data['user'] = $_GET['user'];
		}
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		return $this->dbmodel->recacheGroupFromDB($data);
	}
	
	/**
	 * По признаку из LDAP создаем группу 
	 */
	function createGroupFromFlag() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		if (!isSuperAdmin()) {
			DieWithError("Доступ запрещен!");
		}
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		return $this->dbmodel->createGroupFromFlag($data);
	}
	
	/**
	 * Получение данных для дерева фильтрации в форме просмотра групп 
	 */
	function getGroupTree() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('getGroupTree', true);
		if ($data) {
			$response = $this->dbmodel->getGroupTree($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Получение списка групп
	 */
	function loadGroups() {
		$data = $this->ProcessInputData('loadGroups', true);
		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		if ($data) {
			$groups = $this->dbmodel->loadGroups();
			// Группы берем из LDAP согласно поиску 
			//if ($data['filter']=='Blocked') {
			//	$data['IsBlocked'] = 1;
			//}
			//$groups = pmAuthGroups::load($data);
			// Возможно здесь надо будет сортировать
			echo json_encode($groups);
			/*
			// Группа существует 
			$addrbook = new pmAdressBooks($data['dn'],$data['group_id'],toUtf($data['group_name']), $data['group_type']);
			// Остается получить список людей в данной группе 
			$data['users'] = $addrbook->users();
			// И по нему прочитать из базы людей 
			$response = $this->dbmodel->loadUserSearchGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			*/
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Сохранение группы 
	 */
	function saveGroup()
	{
		$data = $this->ProcessInputData('saveGroup', true);
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$access = false;
		// Если группу добавляет(не редактирует) админ ЛПУ
		if(isLpuAdmin() && empty($data['dn'])) {
			$access = true;
		} else {
			//
		}
		// TODO: Если у пользователя есть возможность сохранять группы 
		if (isSuperAdmin() || $access) {

			if (isset($data['Group_ParallelSessions']) && ($data['Group_ParallelSessions'] < 1 || $data['Group_ParallelSessions'] > 1000)) {
				throw new Exception('Значение поля "Количество параллельных сеансов одного пользователя" должно быть между 1 и 1000.');
			}

			$result = $this->dbmodel->checkSaveGroupDB($data);

			if (!is_array($result) || count($result) != 0 || $result != false) {
					throw new Exception('Группа с таким кодом уже существует');
			}
			$this->dbmodel->saveGroupDB($data);
			// Здесь два варианта

			$groups = new pmAuthGroups();
			$existInLdap = $groups->checkExists($data['Group_Code']);
			if ($existInLdap) {
				// 1. Если данные пересохраняются
				// TODO: Тип группы можно менять только под суперадмином
				$data['pmUser_Login'] = $data['session']['login'];
				$data['Group_IsBlocked'] = 0;
				pmAuthGroups::edit($data);
			} else {
				// 2. Данные сохраняются впервые 
				$data['pmUser_Login'] = $data['session']['login'];
				pmAuthGroups::add($data);
			}
			$r = array('Group_id'=>$data['Group_Code'], 'success'=>true, 'Error_Msg'=>'');
			echo json_encode($r);
		} else {
			echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Ваши права не позволяют создать данную группу.')));
		}
	}
	/**
	 * Удаление группы 
	 */ 
	function deleteGroup()
	{
		$data = $this->ProcessInputData('deleteGroup', true);
		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		// TODO: Предварительная проверка на наличие доступа на удаление 
		if (isSuperAdmin()) {
			if (pmAuthGroups::getCountMembers($data['Group_id'])==0) { // Хотя, конечно это надуманное условие
				pmAuthGroups::remove($data);
				$this->dbmodel->deleteGroupDB($data);

				$r = array(array('success'=>true, 'Error_Msg'=>''));
				echo json_encode($r);
			} else {
				echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Группу удалить нельзя, поскольку есть аккаунты пользователей<br/>входящие в данную группу.')));
			}
		} else {
			echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Ваши права не позволяют удалить данную группу.')));
		}
	}
	
	/**
	 * Получение дерева объектов для отображения/изменения матрицы прав (ролей)
	 */
	function getObjectTree() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('getObjectTree', true);
		if ($data) {
			$response = $this->dbmodel->getObjectTree($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка прав для окна
	 */	
	function getWindowRoles() {
		$data = $this->ProcessInputData('getWindowRoles', true);
		if ($data === false) { return false; }

		$roles = isset($_SESSION['setting']['roles']['windows'])?$_SESSION['setting']['roles']['windows']:array();
		// объект возвращаемый на форму, с правами для этой формы. по умолчанию всё разрешено.
		$retRoles = array( 'add' => true, 'edit' => true, 'delete' => true, 'view' => true, 'import' => true, 'export' => true );
		if (isset($roles[$data['objectClass']])) {
			$retRoles = $roles[$data['objectClass']];
		}
		$this->ReturnData($retRoles);
		return true;
	}
	
	/**
	 * Получение списка объектов для отображения/изменения матрицы прав (ролей)
	 */
	function getObjectRoleList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('getObjectRoleList', true);
		if ($data) {
			$response = $this->dbmodel->getObjectRoleList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Получение списка акшенов определенного объекта 
	 */
	function getObjectActionsList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('getObjectActionsList', true);
		if ($data) {
			$response = $this->dbmodel->getObjectActionsList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Получение заголовков полей для грида
	 */
	function getObjectHeaderList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('getObjectHeaderList', true);
		if ($data) {
			$response = $this->dbmodel->getObjectHeaderList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение пользователей онлайн
	 */
	function loadOnlineUsersList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		$data = $this->ProcessInputData('loadOnlineUsersList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadOnlineUsersList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка типов АРМ
	 */
	public function getPHPARMTypeList() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		$data = $this->ProcessInputData('getPHPARMTypeList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPHPARMTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Сохранение роли объекта
	 */
	function saveObjectRole() {
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('saveObjectRole', true);
		if (true) {
			if (!empty($data['data'])) {
				// Приводим к необходимому виду
				$d = json_decode($data['data'], true);
				$roles = array();
				for ($i=0; $i<count($d); $i++) {
					$id = $d[$i]['id'];
					unset($d[$i]['id']);
					$roles[$id] = $d[$i];
				}
				// Сохраняем 
				$this->dbmodel->saveObjectRole($data, $roles);
				
			}
			$r = array('Role_id'=>$data['Role_id'], 'success'=>true, 'Error_Msg'=>'');
			echo json_encode($r);
		} else {
			echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Ваши права не позволяют изменить права данного объекта.')));
		}
	}
	
	/**
	 * Получение настроек для ЛИС текущего анализатора (раб.места) пользователя 
	 */
	function getLisSettings() {
		$data = $this->ProcessInputData('getLisSettings', true);
		//print_r($data); exit();
		$user = pmAuthUser::find($data['pmUser_Login']);
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			if ($user->lis) {
				$params = json_decode($user->lis, true);
				foreach($params as $p) {
					if(toUtf($data['lis_login']) == $p['lis_login']) {
						$val = $p;
						$val['success'] = true;
						break;
					}
				}
				if(isset($val)) {
					$this->ReturnData(array($val));
				}
			} else {
				echo json_encode(array(array('pmuser_id'=>$user->pmuser_id)));
			}
		}
	}
	/**
	 * deleteLisSetting
	 */
	function deleteLisSetting()
	{
		$data = $this->ProcessInputData('deleteLisSetting', false);
		$user = pmAuthUser::find($data['pmUser_Login']);
		
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			$params = json_decode($user->lis, true);
			$cnt = count($params);
			foreach($params as $k=>$p) {
				if(toUtf($data['lis_login']) == $p['lis_login']) {
					unset($params[$k]);
				}
			}
			//print_r($params); exit();
			// Если количество элементов массива уменьшилось на единицу, значит все гут=)
			if( $cnt == count($params) + 1 ) {
				$user->lis = json_encode($params);
				$user->post();
				$this->ReturnData(array('success' => true));
			} else {
				DieWithError('Не удалось найти анализатор.');
			}
		}
	}
	/**
	 * loadLisWPGrid
	 */
	function loadLisWPGrid()
	{
		$data = $this->ProcessInputData('loadLisWPGrid', true);
		$user = pmAuthUser::find($data['pmUser_Login']);
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			if ($user->lis) {
				$val = array();
				$params = json_decode($user->lis, true);
				foreach($params as $p) {
					$val[] = array(
						'lis_login' => (isset($p['lis_login'])) ? $p['lis_login'] : null,
						'lis_analyzername' => (isset($p['lis_analyzername'])) ? $p['lis_analyzername'] : null,
						'lis_note' => (isset($p['lis_note'])) ? $p['lis_note'] : null
					);
				}
				echo json_encode($val);
			} else {
				echo json_encode(array(array('pmuser_id'=>$user->pmuser_id)));
			}
		}
	}
	
	/**
	 * Сохранение настроек для ЛИС текущего пользователя 
	 */
	function setLisSettings() {
		$data = $this->ProcessInputData('setLisSettings', false);
		
		$user = pmAuthUser::find($data['pmUser_Login']);
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			// собираем все данные в json
			$params = $data;
			unset($params['pmUser_Login']);
			unset($params['session']);
			array_walk($params, 'ConvertFromWin1251ToUTF8');
			if( is_array(json_decode($user->lis, true)) ) {
				$isEdit = false;
				$lisArr = json_decode($user->lis, true);
				// Пробуем найти инфу о текущем анализаторе
				foreach($lisArr as $key=>$arr) {
					if( $arr['lis_login'] == toUtf($data['lis_login']) ) {
						$lisArr[$key] = $params;
						$isEdit = true;
					}
				}
				// тоесть если не редактируем
				if(!$isEdit) {
					$lisArr[] = $params;
				}
			} else {
				$lisArr = array($params);
			}
			$_SESSION['lis'] = $lisArr;
			$user->lis = json_encode($lisArr);
			$user->post();
			$this->ReturnData(array('success' => true));
		}
	}

	/**
	 * Сохранение АРМа по умолчанию
	 */
	function setDefaultWorkPlace() {
		$data = $this->ProcessInputData('setDefaultWorkPlace', false, false);
		if ( $data === false ) {
			return false;
		}
		$user = pmAuthUser::find($_SESSION['login']);
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			// собираем все данные в json
			if (isset($data['session'])) { // сессия мешается здесь.. 
				unset($data['session']);
			}
			array_walk($data, 'ConvertFromWin1251ToUTF8');
			$options = @unserialize($_SESSION['settings']);
			// Массив настроек
			$options['defaultARM'] = $data;
			$options['defaultARM']['Lpu_id'] = $_SESSION['lpu_id'];
			// Сохраняем-пересохраняем настройки
			$user->settings = @serialize($options);
			$_SESSION['settings'] = $user->settings;
			$user->post();
			$this->ReturnData($options['defaultARM']);
		}
	}
	
	
	/**
	 * Сброс АРМа по умолчанию
	 */
	function resetDefaultWorkPlace() {
		$data = $this->ProcessInputData('resetDefaultWorkPlace', false, false);
		if ( $data === false ) {
			return false;
		}
		$user = pmAuthUser::find($_SESSION['login']);
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			// собираем все данные в json
			if (isset($data['session'])) { // сессия мешается здесь.. 
				unset($data['session']);
			}
			$options = @unserialize($_SESSION['settings']);
			$options['defaultARM'] = '';
			// Сохраняем-пересохраняем настройки
			$user->settings = @serialize($options);
			$_SESSION['settings'] = $user->settings;
			$user->post();
			$this->ReturnData(array('success' => true));
		}
	}
	
	/**
	 * Синхронизирует список АРМов в User_model::getARMList и БД
	 */
	function updateARMList() {
		$this->load->database('registry');
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('updateARMList');
		$arms_loc = $this->dbmodel->loadARMList(); // список армов, хранящихся локально
		
		foreach($arms_loc as $k=>$a) {
			$d = array(
				'ARMType_id'		=> null
				,'ARMType_Code'		=> $a['Arm_id']
				,'ARMType_Name'		=> $a['Arm_Name']
				,'ARMType_SysNick'	=> $k
				,'pmUser_id'		=> $data['pmUser_id']
			);
			$rp = $this->dbmodel->getARMinDB(array('ARMType_SysNick' => $d['ARMType_SysNick']));
			$needUpdate = true;
			if(!empty($rp[0]['ARMType_id'])) {
				$d['ARMType_id'] = $rp[0]['ARMType_id'];
				if (
					$d['ARMType_Code'] == $rp[0]['ARMType_Code']
					&& $d['ARMType_Name'] == $rp[0]['ARMType_realName']
					&& $d['ARMType_SysNick'] == $rp[0]['ARMType_SysNick']
				) {
					$needUpdate = false;
				}
			}
			if ($needUpdate) {
				$this->dbmodel->saveARMinDB($d);
			}
			if (count($rp) > 1) {
				$rp = array_slice($rp, 1);
				// удаляем дубли
				foreach($rp as $one) {
					$this->dbmodel->deleteARMinDB($one);
				}
			}
		}
		
		@$arms_db = $this->dbmodel->getARMinDB(array()); // список армов, хранящихся в бд
		
		// Если записей в БД больше чем локально, то необходимо найти лишние и удалить
		if( count($arms_db) > count($arms_loc) ) {
			foreach($arms_db as $k=>$a) {
				if( !isset($arms_loc[$a['ARMType_SysNick']]) ) {
					$this->dbmodel->deleteARMinDB($a);
				}
			}
		}
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 * Чтение списка АРМов из БД и определение прав доступа к отчету
	 */
	function loadARMAccessGrid() {
		$this->load->database('registry');
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('loadARMAccessGrid');
		if ( $data === false ) {
			return false;
		}

		$arms_rep = $this->dbmodel->getARMsAccessOnReport($data);
		$this->ProcessModelMultiList(array('success' => true, 'data' => $arms_rep, 'totalCount' => count($arms_rep)), true, true)->ReturnData();
	}
	
	/**
	 * Сохранение связи типа АРМа и отчета (разрешение права доступа к отчету для пользователя АРМа)
	 */
	function saveReportARM() {
		$this->load->database('registry');
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('saveReportARM');
		if ( $data === false ) {
			return false;
		}
		$isAccess = (bool)($data['isAccess'] === "true" ? 1 : 0);
		$check = $this->dbmodel->checkOnIssetReportARM($data);

		if($isAccess && !$check) {
			// Сохраняем запись
			$response = $this->dbmodel->saveReportARM($data);
		} elseif(!$isAccess && $check) {
			// Удаляем запись
			$response = $this->dbmodel->deleteReportARM($data);
		} else {
			DieWithError("Не удалось проверить существование записи!");
			return;
		}
		$this->ProcessModelSave($response)->ReturnData();
	}

    /**
     * Утановить либо снять (в зависимости от action) доступ для всех АРМов для отчета
     */
    function saveReportARMAccessAll(){
        $this->load->database('registry');
        $this->load->model("User_model", "dbmodel");
        $data = $this->ProcessInputData('saveReportARMAccessAll');
        if ( $data === false ) {
            return false;
        }
        if (!empty($data['idField'])) {
        	$idField = $data['idField'];
        } else {
        	$idField = 'Report_id';
        }
        $ARM_array = $this->dbmodel->GetARMSOnReport($data); //Получим список АРМов в зависимости от action

        if (($ARM_array) && (count($ARM_array) > 0)){
            $data_save = array();
            $data_save['idField'] = $idField;

            foreach ($ARM_array as $a){
                if($data['action'] == 'add'){
                    $data_save['ARMType_id'] = $a['ARMType_id'];
					$data_save[$idField] = $data[$idField];

                    $data_save['pmUser_id'] = $data['pmUser_id'];
                    $this->dbmodel->SaveReportARM($data_save);
                }
                else{
                	if ($idField == 'Report_id') {
                		$data_save['ReportARM_id'] = $a['ReportARM_id'];
                	} else {
                		$data_save['ReportContentParameterLink_id'] = $a['ReportContentParameterLink_id'];
                	}

                    $this->dbmodel->deleteReportARM($data_save);
                }
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    function getUserSessions(){
        $this->load->database('phplog');
        $this->load->model("User_model", "dbmodel");
        $data = $this->ProcessInputData('getUserSessions',true);
        $response = $this->dbmodel->getUserSessions($data);

        $this->ProcessModelMultiList($response, true, true)->ReturnData();
        return true;
    }

	/**
	 * @return bool
	 */
	function getMethods(){
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		echo @json_encode($this->dbmodel->getMethods());
		return true;
	}

	/**
	 * Блокирование пользователей
	 * @throws Exception
	 */
	function blockUsers() {
		$data = $this->ProcessInputData('blockUsers',false);
		if (!$data) {
			return false;
		}

		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$response = $this->dbmodel->blockUsers($data);

		$this->ProcessModelSave($response);
		return true;
	}

	/**
	 * Прерывание саенсов пользователей
	 */
	function interruptUserSessions() {
		if (!defined('NODEJS_CONTROL_ENABLE') || !NODEJS_CONTROL_ENABLE) {
			$this->ReturnError('Функция управления сессиями пользователей отключена');
			return false;
		}
		$data = $this->ProcessInputData('interruptUserSessions',false);
		if (!$data) {
			return false;
		}
		$params = array(
			'action' => 'interrupt',
			'Session_ids' => $data['Session_ids'],
			'DelayMinutes' => $data['DelayMinutes'],
			'Message' => $data['Message']
		);

		$this->load->helper('NodeJS');
		$postSendResult = NodePostRequest($params);

		if ($postSendResult[0]['success']==true) {
			//нод жив
			$responseData = json_decode($postSendResult[0]['data'],true);

			$this->ProcessModelSave( $responseData )->ReturnData();
		} else {
			//нод мертв
			$this->ProcessModelSave($postSendResult, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Убивается сессия
	 */
	function logoutUser() {
		if ( defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS ) {
			require_once(APPPATH . 'libraries/UserAudit.php');
			UserAudit::Logout(session_id());
		}
		$user = pmAuthUser::find($_SESSION['login']);
		$user->logout();

		$response = array('success' => true);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Смена пароля
	 */
	function changePassword() {
		$data = $this->ProcessInputData('changePassword', true, false);
		if ( $data === false ) { return false; }

		$this->load->model("User_model", "dbmodel");
		$response = $this->dbmodel->changePassword($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение списка учетных записей, связанных с организацией
	 */
	function loadPMUserCacheOrgList() {
		$data = $this->ProcessInputData('loadPMUserCacheOrgList', true, false);
		if ( $data === false ) { return false; }

		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		$response = $this->dbmodel->loadPMUserCacheOrgList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка МО + фильтры
	 */
	function getLpuList() {
		$data = $this->ProcessInputData('getLpuList', true, false);
		if ( $data === false ) { return false; }

		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		$response = $this->dbmodel->getLpuList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Смена пароля
	 */
	function checkShownMsgArms() {
		$data = $this->ProcessInputData('checkShownMsgArms', true, false);
		if ( $data === false ) { return false; }

		$this->load->model("User_model", "dbmodel");
		$response = $this->dbmodel->checkShownMsgArms($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	public function generateNewUsers() {
		if ( !isSuperAdmin() ) {
			die('Функционал недоступен');
		}

		$this->load->model("User_model", "dbmodel");
		echo $this->dbmodel->generateNewUsers();

		return true;
	}

	/**
	 * Метод проверяет юзера на активность и если долго нет активности то блокирует юзера (работает по крону)
	 */
	public function checkPersonActivity()
	{
		if (in_array(getRegionNick(), array('kz'))) {return;}

		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$this->load->model("Messages_model", "dbmessage");
		$this->load->model("Options_model");

		$users = $this->dbmodel->getNotAdminUsers();
		$daysBeforeBlock = $this->Options_model->getDataStorageValueByName([
			'DataStorage_Name' => 'check_user_activity'
		]);
		$usersForBlock = array();

		if ($daysBeforeBlock != '') {
			require_once(APPPATH . 'libraries/UserAudit.php');

			foreach ($users as &$user) {
				$user = $user['pmUser_id'];
			}

			$users_id = implode(',', $users);
			$lastUsersAuth = UserAudit::lastAuth(array('pmUsers_id' => $users_id, 'type' => 'LogoutTime'));
			$current_date = new DateTime();

			if (!is_numeric($daysBeforeBlock)) {
				die('Не верный формат опции системы "Блокировать пользователя после истечения срока отсутствия активности (в днях)".');
			}
			$hoursBeforeBlock = $daysBeforeBlock * 24;

			foreach ($lastUsersAuth as $user) {
				$userLogoutTime = $user['LogoutTime'];
				if (!($userLogoutTime instanceof DateTime)) {
					try {
						$userLogoutTime = new DateTime($userLogoutTime);
					}
					catch (Exception $e) {
						die('Не верный формат даты последней аутентификации пользователя.');
					}
				}

				$dateDiff = date_diff($current_date, $userLogoutTime);
				$dayDiff = $dateDiff->days ? $dateDiff->days : 0;
				$hoursDiff = $dayDiff * 24 + $dateDiff->h;

				if($hoursDiff >= $hoursBeforeBlock) {
					$usersForBlock[] = $user['pmUser_id'];
				}
			}

			$blockedUsers['pmUser_ids'] = json_encode($usersForBlock);
			$blockedUsers['pmUser_Blocked'] = true;

			$this->dbmodel->blockUsers($blockedUsers);

			$lpuAdmins = $this->dbmodel->getLpuAdminList();

			if(isset($lpuAdmins[0])) {
				foreach ($lpuAdmins as $lpuAdmin) {
					foreach ($usersForBlock as $user) {
						$noticeData = array(
							'autotype' => 1,
							'User_rid' => $lpuAdmin['pmUser_id'],
							'pmUser_id' => 1,
							'type' => 1,
							'title' => 'Автоматическое уведомление',
							'text' => 'Пользователь с логином "'.$user['Login'].'" был заблокирован из-за отсутствие активности.'
						);

						$this->dbmessage->autoMessage($noticeData);
					}
				}
			}
		}
	}

	function getLastAuth() {
		$user = isset($_SESSION['pmuser_id']) ? $_SESSION['pmuser_id'] : false;
		if ( $user === false ) { return false; }

		require_once(APPPATH . 'libraries/UserAudit.php');
		$lastUsersAuth = UserAudit::lastAuth(array('pmUsers_id' => $user, 'type' => 'LoginTime'));

		if($lastUsersAuth) {
			$date = $lastUsersAuth[0]['LoginTime'];
			if($date instanceof DateTime) {
				$this->ReturnData($date->format('Y-m-d H:i:s'));
			}
		}
		return false;
	}
}