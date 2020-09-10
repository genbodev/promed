<?php

/**
 * pmAuthUsers - библиотека поддержки работы с пользователями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan (megatherion@list.ru)
 * @version      11.09.09
 */

/**
 * Группа
 */
class pmAuthGroup {

	public $id;
	public $name;
	public $desc;
	public $blocked;
	public $parallelSessions;

}

/**
 * Группы пользователей
 */
class pmAuthGroups {

	public $groups = array();

	/**
	 * Функция конвертирования из win-1251 в UTF
	 */
	function toUTF( $var ) {
		return toUTF($var);
	}

	/**
	 * Конструктор
	 */
	function __construct($options = array()) {
		$this->groups = array();
		$groups = $this->getGroups(isset($options['forceGroupsReload']) && $options['forceGroupsReload'] === true);
		for ( $i = 0; $i < count($groups); $i++ ) {
			$this->readGroup($groups[$i]);
		}
	}

	/**
	 * Чтение данных группы из данных запроса к LDAP
	 * 
	 * @param array $ldapdata
	 */
	private function readGroup( $ldapdata ) {
		$group = new pmAuthGroup();
		$group->id = $ldapdata["id"];
		$group->name = $ldapdata["name"];
		$group->desc = $ldapdata["desc"];
		$group->parallelSessions = $ldapdata['parallelsessions'];
		$group->blocked = (isset($ldapdata["blocked"])) ? $ldapdata["blocked"] : 0;
		$this->groups[$group->name] = $group;
	}

	/**
	 * Чтение списка групп из LDAP
	 */
	static function load( $data = array() ) {
		// варианты поиска 
		// 1. Группы созданные определенным ЛПУ (организацией)
		// 2. Группы созданные определенным пользователем
		// 3. Группы заблокированные или не заблокированные 
		$filter = "";
		/* Фильтрацию по ЛПУ отключил до понимания и реализации функционала
		  if (!empty($data['Lpu_id'])) {
		  $filter .= "(o={$data['Lpu_id']})";
		  } else {
		  $filter .= ""; //$filter .= "(o=*)";
		  } */
		if ( !empty($data['pmUser_Login']) ) {
			$filter = "(ou={$data['pmUser_Login']})";
		} else {
			$filter .= ""; //$filter .= "(ou=*)";
		}
		if ( !empty($data['IsBlocked']) ) {
			$filter = "(blocked={$data['IsBlocked']})";
		} else {
			$filter .= ""; //$filter .= "(blocked=*)";
		}
		// ищем группы под определенным Lpu_id (o), созданные определенным пользователем (ou) или (без o и без ou)
		$f = "(&(!(ou=Groups)){$filter})";
		$q = ldap_query(LDAP_GROUP_PATH, $f, array('dn', 'cn', 'description', 'o', 'ou', 'blocked', 'member'));
		$result = array();
		if ( $q["count"] > 0 ) {
			for ( $i = 0; $i < $q["count"]; $i++ ) {
				$result[$i]['dn'] = $q[$i]["dn"];
				$result[$i]['Group_id'] = $q[$i]["cn"][0];
				$result[$i]['Group_Code'] = $q[$i]["cn"][0];
				$result[$i]['Group_Name'] = (isset($q[$i]["description"][0])) ? $q[$i]["description"][0] : "";
				if ( isset($q[$i]["o"][0]) ) {
					$result[$i]['Lpu_id'] = $q[$i]["o"][0];
				} else {
					$result[$i]['Lpu_id'] = null;
				}
				if ( isset($q[$i]["ou"][0]) ) {
					$result[$i]['pmUser_Login'] = $q[$i]["ou"][0];
					$result[$i]['pmUser_Name'] = $result[$i]['pmUser_Login'];
				} else {
					$result[$i]['pmUser_Login'] = null;
				}
				if ( isset($q[$i]["blocked"][0]) ) {
					$result[$i]['Group_IsBlocked'] = $q[$i]["blocked"][0];
				} else {
					$result[$i]['Group_IsBlocked'] = 0;
				}
				if ( isset($q[$i]["isonly"][0]) ) {
					$result[$i]['Group_IsOnly'] = $q[$i]["isonly"][0];
				} else {
					$result[$i]['Group_IsOnly'] = 0;
				}
				$result[$i]['Group_UserCount'] = 0;
				if ( isset($q[$i]["member"]) ) {
					$result[$i]['Group_UserCount'] = $q[$i]["member"]['count'];
				}
			}
		}
		return $result;
	}

	/**
	 * Чтение "короткого" списка групп из LDAP
	 * Группы сохраняются в сессию при первом обращении к LDAP либо при указанном $forceReload = true
	 */
	static function getGroups($forceReload = false) {
		// todo: Как только станет понятно, что будем использовать для хранения кеша, надо будет переделать сохранение всех групп в кеш (а не в сессию). Тоже самое с ролями групп.
		if ( isset($_SESSION["allgroups"]) && (!empty($_SESSION["allgroups"])) && $forceReload === false ) { // если $_SESSION["allgroups"] уже определена
			$result = $_SESSION["allgroups"];
		} else {
			$f = "(&(!(ou=Groups))(cn=*))"; // (blocked=0)
			$q = ldap_query(LDAP_GROUP_PATH, $f, array('dn', 'cn', 'blocked', 'description', 'parallelsessions'));
			$result = array();
			if ( $q["count"] > 0 ) {
				for ( $i = 0; $i < $q["count"]; $i++ ) {
					$result[$i]['id'] = $q[$i]["dn"];
					$result[$i]['name'] = $q[$i]["cn"][0];
					$result[$i]['blocked'] = (isset($q[$i]["blocked"][0])) ? $q[$i]["blocked"][0] : 0;
					$result[$i]['desc'] = $q[$i]["description"][0];
					$result[$i]['parallelsessions'] = isset($q[$i]["parallelsessions"][0]) ? $q[$i]["parallelsessions"][0] : null;
				}
			}
			//print_r($result);
			$_SESSION["allgroups"] = $result;
		}
		return $result;
	}

	/**
	 * Чтение количества пользователей в группе
	 */
	static function getCountMembers( $id ) {
		// ищем определенную группу
		$f = "(&(!(ou=Groups))(cn={$id}))";
		$q = ldap_query(LDAP_GROUP_PATH, $f, array('member'));
		$count = 0;
		if ( $q["count"] > 0 ) {
			for ( $i = 0; $i < $q["count"]; $i++ ) {
				if ( isset($q[$i]["member"]) ) {
					$count = $q[$i]["member"]['count'];
				}
			}
		}
		return $count;
	}

	/**
	 * Добавление новой группы в LDAP
	 */
	static function add( $data ) {
		// Сохраняем группу 
		$record = array();

		// Код и идешник
		if ( isset($data['Group_Code']) )
			$record["cn"] = toUtf($data['Group_Code']);
		else
			return false;
		$dn = "cn=" . $record["cn"] . ',' . LDAP_GROUP_PATH;

		// В дальнейшем можно быстро сделать сохранение группы на несколько ЛПУ
		/* пример 
		  for ($i=0;$i<count($this->organizations);$i++) {
		  $record["o"][$i] = (int)$this->organizations[$i];
		  }
		 */
		$record["o"] = null;
		if ( isset($data['Lpu_id']) )
			$record["o"] = $data['Lpu_id'];
		// Автор группы
		if ( isset($data['pmUser_Login']) )
			$record["ou"] = $data['pmUser_Login'];
		else
			return false;
		// Название группы 
		if ( isset($data['Group_Name']) )
			$record["description"] = toUtf($data['Group_Name']);
		else
			return false;
		// Признак заблокированности группы 
		$record["blocked"] = 0;
		if (isset($data['Group_IsBlocked'])) {
			$record["blocked"] = $data['Group_IsBlocked'];
		}
		$record["isonly"] = 0;
		if (isset($data['Group_IsOnly'])) {
			$record["isonly"] = $data['Group_IsOnly'];
		}
		if ( isset($data['Group_ParallelSessions']) )
			$record['parallelsessions'] = $data['Group_ParallelSessions'];
		// По идее здесь еще должен быть тип
		/*
		  $record["Group_Type"] = 1;
		  if (isset($data['Group_Type']))
		  $record["sn"] = $data['Group_Type'];
		 */

		$record["objectclass"][0] = "top";
		$record["objectclass"][1] = "extensibleObject";
		$record["objectclass"][2] = "organizationalRole";
		ldap_insert($dn, $record);
	}

	/**
	 * Редактирование группы в LDAP
	 * Изменяется только название (и тип)
	 */
	static function edit( $data ) {
		$record = array();
		if ( isset($data['Group_Code']) )
			$record["cn"] = toUtf($data['Group_Code']);
		else
			return false;

		// Название группы 
		if ( isset($data['Group_Name']) )
			$record["description"] = toUtf($data['Group_Name']);
		else
			return false;

		if ( isset($data['Group_ParallelSessions']) )
			$record['parallelsessions'] = $data['Group_ParallelSessions'];

		if ($data['Group_ParallelSessions'] == null) {
			$record['parallelsessions'] = ' ';
		}
		/*
		  if (isset($data['pmUser_Login']))
		  $record["ou"] = $data['pmUser_Login'];
		  else
		  return false;
		 */
		// Признак заблокированности группы 
		$record["blocked"] = 0;
		if (isset($data['Group_IsBlocked'])) {
			$record["blocked"] = $data['Group_IsBlocked'];
		}
		$record["isonly"] = 0;
		if (isset($data['Group_IsOnly'])) {
			$record["isonly"] = $data['Group_IsOnly'];
		}

		$dn = "cn=" . $record["cn"] . ',' . LDAP_GROUP_PATH;

		// По идее здесь еще должен быть тип 
		/*
		  $record["Group_Type"] = 1;
		  if (isset($data['Group_Type']))
		  $record["sn"] = $data['Group_Type'];
		 */

		ldap_edit($dn, $record);
	}

	/**
	 * Удаляем группу из LDAP
	 * C проверкой можно ли это сделать
	 */
	static function remove( $data ) {
		$record = array();
		if ( !isset($data['dn']) )
			return false;
		// Название группы 
		if ( !isset($data['Group_id']) )
			return false;
		// TODO: Тут должна быть проверка на наличие пользователей в группе
		$q = ldap_query(LDAP_GROUP_PATH, "cn={$data['Group_id']}", array());
		if ( $q["count"] <> 0 )
			ldap_remove($data['dn']);
	}

	/**
	 * Чтение роли группы
	 */
	static function loadRole( $group_code ) {
		// todo: Надо придумать как хранить роли групп в одном месте для всех пользователей (в MongoDB, в кеше)
		/// ищем определенную группу
		$q = ldap_query(LDAP_GROUP_PATH, "(&(!(ou=Groups))(cn={$group_code}))", array('role'));
		// и отдаем роль группы, преобразуя из в массив
		return ( ($q["count"] > 0) && (isset($q[0]["role"][0])) ) ? json_decode($q[0]["role"][0], true) : array();
	}

	/**
	 * Проверка наличия группы
	 */
	static function checkExists( $group_code ) {
		// todo: Надо придумать как хранить роли групп в одном месте для всех пользователей (в MongoDB, в кеше)
		/// ищем определенную группу
		$q = ldap_query(LDAP_GROUP_PATH, "(&(!(ou=Groups))(cn={$group_code}))", array());
		// и отдаем роль группы, преобразуя из в массив
		return ($q["count"] > 0);
	}

	/**
	 * Сохранение роли группы
	 */
	static function saveRole( $group_code, $role ) {
		$record = array();
		// Код и идешник
		if ( isset($group_code) )
			$dn = "cn=" . $group_code . ',' . LDAP_GROUP_PATH;
		else
			return false;

		if ( isset($role) ) {
			$record["role"] = json_encode($role);
		}
		$record["objectclass"][0] = "top";
		$record["objectclass"][1] = "extensibleObject";
		$record["objectclass"][2] = "organizationalRole";
		ldap_edit($dn, $record);
	}

	/**
	 * Возвращает группу по идентификатору пользователя
	 * @param <type> $id
	 * @return <type>
	 */
	public function groupsById( $id ) {
		$this->groups = array();
		$ldap = ldap_query(LDAP_GROUP_PATH, "member=$id", array('dn', 'cn', 'blocked', 'description', 'parallelsessions'));
		for ( $i = 0; $i < $ldap["count"]; $i++ ) {
			$result = array();
			$result['id'] = $ldap[$i]["dn"];
			$result['name'] = $ldap[$i]["cn"][0];
			$result['blocked'] = (isset($ldap[$i]["blocked"][0])) ? $ldap[$i]["blocked"][0] : 0;
			$result['desc'] = $ldap[$i]["description"][0];
			$result['parallelsessions'] = isset($ldap[$i]["parallelsessions"][0]) ? $ldap[$i]["parallelsessions"][0] : null;
			$this->readGroup($result);
		}
		return $this->groups;
	}

	/**
	 * @desc Возвращает пользователей групп
	 * @param string $group Код группы (Например: smpheadduty)
	 * @return array or false
	 */
	public function getGroupUsers( $group ) {
		if ( !$group ) {
			return false;
		}

		// Устанавливаем соединение
		$conn = ldap_connect(LDAP_SERVER, LDAP_SERVER_PORT);
		ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		if ( !$conn ) {
			DieWithError("Невозможно соединиться с сервером авторизации.");
		}

		if ( !( @ldap_bind($conn, LDAP_USER, LDAP_PASS) ) ) {
			DieWithError('Перегружен сервер авторизации!');
		}

		// Получаем пользователей группы
		$filter = '(cn=' . $group . ')';

		$result = ldap_search($conn, LDAP_GROUP_PATH, $filter, array('member'));
		$entries = ldap_get_entries($conn, $result);
		ldap_unbind($conn);

		// Цикл по группам из массива
		$members_list = array();
		for ( $i = 0; $i < $entries['count']; $i++ ) {
			$members = array();
			// Получение атрибутов member для текущей группы из массива
			if ( isset($entries[$i]['member']) ) {
				for ( $j = 0; $j < $entries[$i]['member']['count']; $j++ ) {
					array_push($members, $entries[$i]['member'][$j]);
				}
			} else {
				$members = NULL;
			}

			$key = preg_replace('#,' . LDAP_GROUP_PATH . '#', '', $entries[$i]['dn']);
			$members_list[$key] = $members;
		}

		return $members_list;
	}

}

/**
 * Пользователь
 */
class pmAuthUser {

	// идентификатор (текст)
	public $id;
	// идентификатор (число)
	public $pmuser_id;
	// логин
	public $login;
	// пароль
	public $pass;
	// Заблокирован или нет
	public $blocked;
	// группы, в которые входит пользователь
	public $groups;
	// сертификаты пользователя
	public $certs;
	// Полное имя пользователя
	public $name;
	// Фамилия
	public $surname;
	// Имя
	public $firname;
	// Отчество
	public $secname;
	// Информация о юзвере
	public $about;
	// Аватар
	public $avatar;
	// электропочта
	public $email;
	// номер мобильного
	public $phone;
	// статус активации мобильного
	public $phone_act;
	// код активации мобильного
	public $phone_act_code;
	// идентификатор МАРШа
	public $marshserial;
	// токен
	public $swtoken;
	// конец даты токена
	public $swtoken_enddate;
	// описание
	public $desc;
	// врач
	public $medpersonal_id;
	// право выписывать медсвидетельства
	public $medsvidgrant_add;
	// матрица запретов доступа к АРМам
	public $deniedarms;
	// настройки
	public $settings;
	// активен или нет
	public $enabled;
	// Организации
	public $org;
	// настройки ЛИС 
	public $lis;
	// Тип организации
	public $orgtype;
	// Права доступа
	public $access_rights;
	// Признак временного пароля
	public $password_temp;
	// JSON-массив последних 4 временных паролей
	public $password_last;
	// Дата задания пароля (unix time)
	public $password_date;
	// JSON-массив АРМ-ов в которых показали уведомление
	public $shown_armlist;
	//кол-во паралелльных сеансов
	public $parallelSessions;
	// логин ЕМИАС
	public $loginEmias;

	// Масив полей необходимых для создания объекта пользователя из данных LDAP
	static $ldap_user_fields = array('dn','uid','cn','userpassword','sn','givenname','secname','email','phone','phone_act','phone_act_code','marshserial','swtoken','swtoken_enddate','about','avatar','description','employeenumber','orgid','certs','pseudonym','uidnumber','blocked','employeenumber','medsvidgrantadd','deniedarms','password_temp','password_last','password_date','shown_armlist','organizationalstatus','lis', 'parallelsessions','loginemias');

	/**
	 * Конструктор
	 * 
	 * @param <type> $id
	 * @param <type> $name
	 * @param <type> $login
	 * @param <type> $pass
	 * @param <type> $surname
	 * @param <type> $secname
	 * @param <type> $firname
	 * @param <type> $is_farmnetadmin
	 * @param boolean $refreshGroups Автоматически обновлять список групп
	 */
	function __construct( $id, $name, $login, $pass, $surname, $secname, $firname, $is_farmnetadmin = false, $parallelSessions = '', $loginEmias = '' ) {
		if ( empty($id) )
			$this->id = "uid=" . $login . ',' . LDAP_USER_PATH;
		else
			$this->id = $id;
		$this->name = $name;
		$this->login = $login;
		if ( substr($pass, 0, 5) <> "{MD5}" )
			$pass = "{MD5}" . base64_encode(md5($pass, TRUE));
		$this->pass = $pass;
		$this->surname = $surname;
		$this->secname = $secname;
		$this->firname = $firname;
		$this->pmuser_id = $this->genUserId();
		$this->enabled = ($is_farmnetadmin ? 2 : 1);
		$this->parallelSessions = $parallelSessions;
		$this->loginEmias = $loginEmias;
		$this->lpu = array();
		$this->org = array();
		$this->groups = array();
		$this->certs = array();
		$this->access_rights = array();
	}

	/**
	 * Поиск человека в базе по заданному логину, возвращает объект pmAuthUser
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	static function find( $login ) {
		// Если данный пользователь текущий и уже найден, то смысла его искать в LDAP повторно нет, если конечно он не был изменен в процессе
		// todo: Если пользователя меняем, то в том же месте должны перечитать сессию (это надо будет проверить)
		// todo: надо будет убрать последнее условие (isset($_SESSION['surname'])) как только будут обновлены все регионы
		if ( isset($_SESSION['login']) && ($_SESSION['login'] == $login) && (isset($_SESSION['surname'])) ) { // данные о текущем пользователе сохранены в сессии
			// искать его в лдап нет смысла, возвращаем из сессии
			$user = pmAuthUser::fromSession();
		} else {
			$q = ldap_query(LDAP_USER_PATH, "uid=$login", pmAuthUser::$ldap_user_fields);
			if ( $q["count"] == 1 ) {
				$ldap = $q[0];
				$user = pmAuthUser::fromLdapData($ldap);
				return $user;
			} else {
				return null;
			}
		}
		return $user;
	}

	/**
	 * Создание объекта пользователя в сесcии
	 */
	public function toSession() {
		$CI = & get_instance();
		$CI->load->database();
		$CI->load->model('Org_model', 'Org_model');
		$CI->load->model('User_model', 'User_model');
		$CI->load->model('Options_model', 'opmodel');
		$CI->load->model('MedStaffFactLink_model', 'msflmodel');
		$CI->load->model('AccessRights_model', 'armodel');
		$CI->load->helper('Options');

		//$_SESSION['lpu_id'] = (isset($this->org[0]) && isset($this->org[0]["org_id"])) ? $CI->Org_model->getLpuOnOrg(array('Org_id' => $this->org[0]["org_id"])) : 0;
		$_SESSION['lpu_id'] = (!empty($_SESSION['lpu_id']) && $_SESSION['lpu_id'] > 0) ? $_SESSION['lpu_id'] :((isset($this->org[0]) && isset($this->org[0]["org_id"])) ? $CI->Org_model->getLpuOnOrg(array('Org_id' => $this->org[0]["org_id"])) : 0);
		$_SESSION['server_id'] = $_SESSION['lpu_id'] > 0 ? $_SESSION['lpu_id'] : 0;
		$_SESSION['org_id'] = (isset($this->org[0]) && isset($this->org[0]["org_id"])) ? $this->org[0]["org_id"] : 0;
		$_SESSION['Org_Name'] = (isset($this->org[0]) && isset($this->org[0]["Org_Name"])) ? $this->org[0]["Org_Name"] : '';
		if (isset($this->org[0]) && isset($this->org[0]["org_id"])) {
			$orgtype = $CI->Org_model->getOrgType(array('Org_id' => $this->org[0]["org_id"]));
			if (!empty($orgtype['OrgType_SysNick'])) {
				$_SESSION['orgtype'] = $orgtype['OrgType_SysNick'];
				$_SESSION['orgtype_id'] = $orgtype['OrgType_id'];
			} else {
				$_SESSION['orgtype'] = '';
				$_SESSION['orgtype_id'] = null;
			}
		} else {
			$_SESSION['orgtype'] = '';
			$_SESSION['orgtype_id'] = null;
		}
        if (empty($_SESSION['Org_Name']) && !empty($this->org[0]) && !empty($this->org[0]["org_id"])) {
            $_SESSION['Org_Name'] = $CI->Org_model->getOrgName($this->org[0]["org_id"]);
        }
		$_SESSION['user_id'] = $this->id;
		$_SESSION['pmuser_id'] = $this->pmuser_id;
		$_SESSION['surname'] = $this->surname;
		$_SESSION['secname'] = $this->secname;
		$_SESSION['firname'] = $this->firname;
		$_SESSION['pass'] = $this->pass; // todo: пока сделал хранение в сессии, надо сделать нормальное редактирование и убрать
		$_SESSION['login'] = $this->login;
		$_SESSION['login_emias'] = $this->loginEmias;
		$_SESSION['user'] = $this->name;
		$_SESSION['email'] = $this->email;
		$_SESSION['phone'] = $this->phone;
		$_SESSION['phone_act'] = $this->phone_act;
		$_SESSION['phone_act_code'] = $this->phone_act_code;
		$_SESSION['marshserial'] = $this->marshserial;
		$_SESSION['swtoken'] = $this->swtoken;
		$_SESSION['swtoken_enddate'] = $this->swtoken_enddate;
		$_SESSION['about'] = $this->about;
		$_SESSION['avatar'] = $this->avatar;
		$_SESSION['desc'] = $this->desc;
		$_SESSION['medpersonal_id'] = $this->medpersonal_id;
		$_SESSION['medsvidgrant_add'] = $this->medsvidgrant_add;
		$_SESSION['deniedarms'] = $this->deniedarms;
		$_SESSION['password_temp'] = $this->password_temp;
		$_SESSION['password_last'] = $this->password_last;
		$_SESSION['password_date'] = $this->password_date;
		$_SESSION['shown_armlist'] = $this->shown_armlist;
		$_SESSION['blocked'] = $this->blocked;
		$_SESSION['settings'] = $this->settings;
		$_SESSION['parallel_sessions'] = $this->parallelSessions;
		/**
		 * Для теста!
		 */
		$_SESSION['MedStaffFact_id'] = array();
		// Заходи под аптекой для теста ))
		//$_SESSION['OrgFarmacy_id'] = 8000009;
		//-----

		$grArr = array();
		$isFarmacy = false;
		$isFarmacyNetAdmin = false;
		foreach ( $this->groups as $key => $value ) {
			$grArr[] = $value->name;
			if ( $value->name == 'FarmacyAdmin' || $value->name == 'FarmacyUser' || $value->name == 'FarmacyNetAdmin' ) {
				$isFarmacy = true;
			}

			if ( $value->name == 'FarmacyNetAdmin' ) {
				$isFarmacyNetAdmin = true;
			}
		}
		$_SESSION['groups'] = implode('|', $grArr);
		if ( $isFarmacy ) {
			$_SESSION['OrgFarmacy_id'] = (isset($this->org[0]) && isset($this->org[0]["org_id"])) ? $CI->Org_model->getOrgFarmacyOnOrg(array('Org_id' => $this->org[0]["org_id"])) : 0;
			$_SESSION['lpu_id'] = 0; //по сути у аптеки не должно быть никакой связи с лпу
		}

		if ( $isFarmacyNetAdmin ) {
			$_SESSION['isFarmacyNetAdmin'] = true;
			$_SESSION['OrgNet_id'] = (isset($this->org[0]) && isset($this->org[0]["org_id"])) ? $CI->Org_model->getOrgFarmacyOnOrg(array('Org_id' => $this->org[0]["org_id"])) : 0;
		}

		$lpuArr = array();
		$orgArr = array();
		foreach ( $this->org as $key => $value ) {
			$lpuArr[] = $CI->Org_model->getLpuOnOrg(array('Org_id' => $value['org_id']));
			$orgArr[] = $value['org_id'];
		}
		$_SESSION['lpus'] = implode('|', $lpuArr);
		$_SESSION['orgs'] = $orgArr;
		$_SESSION['certs'] = $this->certs;

		// https://redmine.swan.perm.ru/issues/19634
		$_SESSION['isMedStatUser'] = $CI->User_model->isMedStatUser();
		$_SESSION['isPathoMorphoUser'] = $CI->User_model->isPathoMorphoUser();
		
		// регион
		$_SESSION['region'] = $CI->opmodel->getRegion();
		// язык приложения из настроек
		$_SESSION['language'] = getOptions('appearance','language');
		
		if ( !empty($this->medpersonal_id) ) {
			$_SESSION['MedStaffFactLinks'] = $CI->msflmodel->getMedStaffFactLinkList(array('MedPersonal_sid' => $this->medpersonal_id));
		}

		$_SESSION['access_rights'] = $CI->armodel->getAccessRightsForUser(array(
			'pmUser_id' => $this->pmuser_id, 'MedPersonal_id' => $this->medpersonal_id, 'Lpus' => array($_SESSION['lpu_id']), 'UserGroups' => $grArr
		));

		if (!empty($_SESSION['lpu_id'])) {
			$_SESSION['linkedLpuIdList'] = $CI->Org_model->getLinkedLpuIdList(array('Lpu_id' => $_SESSION['lpu_id']));
		} else {
			$_SESSION['linkedLpuIdList'] = array(0);
		}
	}

	/**
	 * Создание объекта пользователя из сесcии, без обращения в другие места
	 */
	static function fromSession() {
		if ( isset($_SESSION['login']) ) {
			$parallerSessions = isset($_SESSION['parallel_sessions'])?$_SESSION['parallel_sessions']:'';
			$loginEmias = !empty($_SESSION['login_emias'])?$_SESSION['login_emias']:'';
			// Создаем пользователя, сразу заполняя какие-то данные из сесиии
			$user = new pmAuthUser($_SESSION['user_id'], $_SESSION['user'], $_SESSION['login'], "", "", "", "", false, $parallerSessions, $loginEmias);

			// Вытаскиваем все данные из сесии кроме групп, их будем позже
			foreach ( $user as $class_field => $value ) {
				if ( isset($_SESSION[$class_field]) && $class_field != 'groups' ) {
					$user->$class_field = $_SESSION[$class_field];
				}
			}

			// Обновление списка групп
			if ( isset($_SESSION["groups"]) ) {
				foreach ( explode('|', $_SESSION["groups"]) as $group_name ) {
					if ( isset($_SESSION["allgroups"]) && (count($_SESSION["allgroups"]) > 0) ) {
						//print_r($_SESSION["allgroups"]);
						foreach ( $_SESSION["allgroups"] as $grp ) {
							if ( $grp['name'] == $group_name ) {
								$group = new pmAuthGroup();
								$group->id = $grp['id'];
								$group->name = $grp['name'];
								$group->desc = $grp['desc'];
								$group->blocked = $grp['blocked'];
								$group->parallelSessions = isset($grp['parallelsessions']) ? $grp['parallelsessions'] : '';
								array_push($user->groups, $group);
								break;
							}
						}
					}
				}
			}
			// Обновление списка организаций
			if ( isset($_SESSION["orgs"]) ) {
				foreach ( $_SESSION["orgs"] as $org_id ) {
					array_push($user->org, array("org_id" => $org_id));
				}
			}

			return $user;
		}
	}

	/**
	 * Создание объекта пользователя из полученных данных из LDAP
	 */
	static function fromLdapData($ldap) {
		$pmuser_id = $ldap["dn"];
		$user_login = $ldap["uid"][0];
		$user_name = $ldap["cn"][0];
		$user_pass = (isset($ldap["userpassword"][0])) ? $ldap["userpassword"][0] : "";
		$user_surname = (isset($ldap["sn"][0])) ? $ldap["sn"][0] : "";
		$user_firname = (isset($ldap["givenname"][0])) ? $ldap["givenname"][0] : "";
		$user_secname = (isset($ldap["secname"][0])) ? $ldap["secname"][0] : "";
		$user_parallel_sessions = (isset($ldap["parallelsessions"][0])) ? $ldap["parallelsessions"][0] : "";
		$user_login_emias = (!empty($ldap["loginemias"][0])) ? $ldap["loginemias"][0] : "";
		$user = new pmAuthUser($pmuser_id, $user_name, $user_login, $user_pass, $user_surname, $user_secname, $user_firname, false, $user_parallel_sessions, $user_login_emias);
		$user->email = (isset($ldap["email"][0])) ? $ldap["email"][0] : "";
		$user->phone = (isset($ldap["phone"][0])) ? $ldap["phone"][0] : "";
		$user->phone_act = (isset($ldap["phone_act"][0])) ? $ldap["phone_act"][0] : "";
		$user->phone_act_code = (isset($ldap["phone_act_code"][0])) ? $ldap["phone_act_code"][0] : "";
		$user->marshserial = (isset($ldap["marshserial"][0])) ? $ldap["marshserial"][0] : "";
		$user->swtoken = (isset($ldap["swtoken"][0])) ? $ldap["swtoken"][0] : "";
		$user->swtoken_enddate = (isset($ldap["swtoken_enddate"][0])) ? $ldap["swtoken_enddate"][0] : "";
		$user->about = (isset($ldap["about"][0])) ? $ldap["about"][0] : "";
		$user->avatar = (isset($ldap["avatar"][0])) ? $ldap["avatar"][0] : "";
		$user->desc = (isset($ldap["description"][0])) ? $ldap["description"][0] : "";
		$user->medpersonal_id = (isset($ldap["employeenumber"][0])) ? $ldap["employeenumber"][0] : "";
		// Обновление списка организаций
		if ( isset($ldap["orgid"]) ) {
			for ( $j = 0; $j < count($ldap["orgid"]); $j++ ) {
				if ( isset($ldap["orgid"][$j]) ) {
					array_push($user->org, array("org_id" => $ldap["orgid"][$j]));
				}
			}
		}
		if ( isset($ldap["certs"]) ) {
			for ( $j = 0; $j < count($ldap["certs"]); $j++ ) {
				if ( isset($ldap["certs"][$j]) ) {
					array_push($user->certs, json_decode($ldap["certs"][$j]));
				}
			}
		}

		$CI = & get_instance();
		$CI->load->database();
		$CI->load->model('Org_model', 'Org_model');
		$user->orgtype = '';
		if (isset($user->org[0]) && isset($user->org[0]["org_id"])) {
			$orgtype = $CI->Org_model->getOrgType(array('Org_id' => $user->org[0]["org_id"]));
			if (!empty($orgtype['OrgType_SysNick'])) {
				$user->orgtype = $orgtype['OrgType_SysNick'];
			}
		}

		$user->settings = (isset($ldap["pseudonym"][0])) ? $ldap["pseudonym"][0] : "";
		if ( isset($ldap["uidnumber"][0]) and ( $ldap["uidnumber"][0] != 0) ) {
			$user->pmuser_id = $ldap["uidnumber"][0];
		}
		$user->blocked = (isset($ldap["blocked"][0])) ? $ldap["blocked"][0] : "";
		$user->medpersonal_id = (isset($ldap["employeenumber"][0])) ? $ldap["employeenumber"][0] : "";
		$user->medsvidgrant_add = (isset($ldap["medsvidgrantadd"][0])) ? $ldap["medsvidgrantadd"][0] : "";
		$user->deniedarms = (isset($ldap["deniedarms"][0])) ? $ldap["deniedarms"][0] : "";
		$user->password_temp = (isset($ldap["password_temp"][0])) ? $ldap["password_temp"][0] : 0;
		$user->password_last = (isset($ldap["password_last"][0])) ? $ldap["password_last"][0] : "[]";
		$user->password_date = (isset($ldap["password_date"][0])) ? $ldap["password_date"][0] : 0;
		$user->shown_armlist = (isset($ldap["shown_armlist"][0])) ? $ldap["shown_armlist"][0] : "[]";
		$user->enabled = (isset($ldap["organizationalstatus"][0])) ? $ldap["organizationalstatus"][0] : 1;
		$user->lis = (isset($ldap["lis"][0])) ? $ldap["lis"][0] : null;
		$user->refreshGroups();
		return $user;
	}

	/**
	 * Поиск человека в базе по заданному серийнику МАРШа, возвращает объект pmAuthUser
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	static function findByMarshSerial( $serial ) {
		$q = ldap_query(LDAP_USER_PATH, "(&(organizationalstatus=1)(marshserial=$serial))", pmAuthUser::$ldap_user_fields);
		if ( $q["count"] > 0 ) {
			$ldap = $q[0];
			$user = pmAuthUser::fromLdapData($ldap);
			return $user;
		}
	}

	/**
	 * Поиск человека в базе по заданному токену, возвращает объект pmAuthUser
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	static function findByToken( $token ) {
		$q = ldap_query(LDAP_USER_PATH, "(&(organizationalstatus=1)(swtoken=$token))", pmAuthUser::$ldap_user_fields);
		if ( $q["count"] > 0 ) {
			$ldap = $q[0];
			if ( !empty($ldap["swtoken_enddate"][0]) && strtotime($ldap["swtoken_enddate"][0]) >= time() ) {
				$user = pmAuthUser::fromLdapData($ldap);
				return $user;
			} else {
				return array('success' => false, 'Error_Msg' => 'Token expired');
			}
		}

		return array('success' => false, 'Error_Msg' => 'Invalid token');
	}

	/**
	 * Поиск человека в базе по заданному медперсоналу, возвращает объект pmAuthUser
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	static function findByMedPersonalId($med_personal_id, $firstMedPersonal = false)
	{
		$q = ldap_query(LDAP_USER_PATH, "employeeNumber=$med_personal_id", pmAuthUser::$ldap_user_fields);
		if ($q["count"] == 1 || ($firstMedPersonal && $q["count"] > 0)) {
			$ldap = $q[0];
			$user = pmAuthUser::fromLdapData($ldap);
			return $user;
		} else if ($q["count"] > 1) {
			return array('Error_Msg' => 'Для входа в систему необходимо указать логин');
		} else {
			return false;
		}
	}

	/**
	 * Поиск человека в базе по заданному логину ЕМИАС, возвращает объект pmAuthUser или массив с ошибкой
	 *
	 * @param string $loginEmias Логин пользователя
	 * @return pmAuthUser
	 */
	static function findByEMIASData($loginEmias, $OGRN)
	{
		$q = ldap_query(LDAP_USER_PATH, "loginEmias=$loginEmias", pmAuthUser::$ldap_user_fields);
		if ($q["count"] >= 1) {
			for($i=0;$i<$q["count"];$i++) {
				$user = pmAuthUser::fromLdapData($q[$i]);

				//проверяем организации пользователя на совпадение по OGRN
				$CI = & get_instance();
				$CI->load->database();
				$CI->load->model("Org_model", "orgmodel");
				$orgIds = array();
				foreach ($user->org as $org) {
					$orgIds[] = $org['org_id'];
				}
				$orgEntry = $CI->orgmodel->checkOGRNEntry($orgIds, $OGRN);

				if ($user->havingGroup('OperLLO') && $orgEntry) {
					return $user;
				}
			}
		}
		return array('Error_Msg' => 'Не выполнена идентификация пользователя. Обратитесь к администратору системы для уточнения персональных данных пользователя');
	}

	/**
	 * Поиск людей в базе по заданному медперсоналу, возвращает массив объектов pmAuthUser
	 *
	 * @param string $med_personal_id Логин пользователя
	 * @return array
	 */
	static function findUsersByMedPersonalId($med_personal_id)
	{
		$q = ldap_query(LDAP_USER_PATH, "employeeNumber=$med_personal_id", pmAuthUser::$ldap_user_fields);
		if ($q["count"] > 0) {
			$users = array();
			foreach ($q as $item) {
				$users[] = pmAuthUser::fromLdapData($item);
			}
			return $users;
		} else {
			return array();
		}
	}

	/**
	 * Проверяет существует ли человек заданный логином в базе
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	static function exist( $login ) {
		$q = ldap_query(LDAP_USER_PATH, "uid=$login", array());
		if ( $q["count"] == 1 ) {
			return true;
		}
		return false;
	}

	/**
	 * Проверка авторизации пользователся
	 */
	function auth( $login, $pass ) {
		$_SESSION['IsSWANUser'] = 0;
		if (defined('SW_PASS_SALT')) {
			$swPassSalt = SW_PASS_SALT;
			if (md5($swPassSalt . md5(getRegionNick() . $login . gmdate('YmdH'))) === $pass) {
				$_SESSION['IsSWANUser'] = 1;
			}
		}
		return ($this->enabled and ( "{MD5}" . base64_encode(md5($pass, TRUE)) === $this->pass || $_SESSION['IsSWANUser']));
	}

	/**
	 * Залогинивает пользователя, без проверки пароля
	 */
	function loginTheUser( $auth_type ) {
		if ( isset($_SESSION['login']) )
			$this->logout();
		if ( $this->enabled ) {
			// Проверяем ограничение на количество параллельных сеансов
			$CI = & get_instance();
			$CI->load->library('swParallelSessions', null, 'parallel_sessions');
			$CI->parallel_sessions->checkOnParallelSessions($this);

			$this->toSession();

			// Логируем логин
			if ( defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS ) {
				require_once(APPPATH . 'libraries/UserAudit.php');
				UserAudit::Login($this->login, $this->pmuser_id, session_id(), $auth_type);
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Залогинивает пользователя с проверкой пароля
	 */
	function login( $login, $pass, $newpass = null ) {
		if ( isset($_SESSION['login']) )
			$this->logout();
		if ( $this->auth($login, $pass) ) {
			$CI = & get_instance();
			$CI->load->database();
			$CI->load->model('User_model', 'User_model');

			$lang = isset($_SESSION['lang'])?$_SESSION['lang']:'ru';
			$CI->lang->load('portal', $lang);

			// проверяем дату
			$date = strtotime(date('01.03.2016'));
			if (!empty($this->password_date)) {
				$date = $this->password_date;
			}
			$checkdate = array('Error_Msg' => '');
			if (mb_strtoupper( mb_substr($login, 0,4) ) != 'SWNT') {
				$checkdate = $CI->User_model->checkPasswordDate($date, $this->password_temp);
			}

			$checkDuplicate = $CI->User_model->checkLoginDuplicate($date, $this->pmuser_id);
			if (!empty($checkDuplicate['Error_Msg'])) {
				return array('Error_Msg' => $checkDuplicate['Error_Msg'], 'Error_Code' => '10');
			}
			// #100120
			$IsMainServer = $CI->config->item('IsMainServer');
			if ($IsMainServer === true) {
				// При авторизации пользователя на рабочем сервере (не являющимся сервером СМП) под учетной записью, которая имеет ТОЛЬКО группу/группы доступа СМП,
				// выводится ошибка «Для авторизации под пользователем с группой прав, относящейся к скорой медицинской помощи, необходимо обратиться на сайт <адрес сервера СМП данного региона>». Вход в систему НЕ производится.
				$smpGroups = array('smpadmin', 'smpcalldispath', 'smpdispatchdirections', 'smpdispatchstation', 'smpheadbrig', 'smpheaddoctor', 'smpheadduty', 'zmk');
				if (!$this->hasOneOfGroupExceptOf($smpGroups)) {
					$servername = $CI->config->item('SPMServerName');
					if (empty($servername)) {
						$url = $CI->config->item('SPMServerURL');
						$servername = '<a href="'.$url.'">'.$url.'</a>';
					}
					return array('Error_Msg' => 'Для авторизации под пользователем с группой прав, относящейся к скорой медицинской помощи, необходимо обратиться на сайт '.$servername, 'Error_Code' => '10');
				}
			}

			$IsSMPServer = $CI->config->item('IsSMPServer');
			if ($IsSMPServer === true) {
				// При авторизации пользователя на сервере СМП под учетной записью, которая НЕ имеет группы доступа СМП,
				// выводится ошибка «Для авторизации под пользователем с группой прав, не относящейся к скорой медицинской помощи, необходимо обратиться на сайт <адрес рабочего сервера данного региона>». Вход в систему НЕ производится.
				$smpArms = array('superadmin', 'smpadmin', 'smpcalldispath', 'smpdispatchdirections', 'smpdispatchstation', 'smpheadbrig', 'smpheaddoctor', 'smpheadduty', 'zmk');

				if($CI->config->item('ADDITIONAL_SMP_ARMS') && is_array($CI->config->item('ADDITIONAL_SMP_ARMS'))){
					$smpArms = array_merge($smpArms, $CI->config->item('ADDITIONAL_SMP_ARMS'));
				}
				if (!$this->hasOneOfGroup($smpArms)) {
					$servername = $CI->config->item('MainServerName');
					if (empty($servername)) {
						$url = $CI->config->item('MainServerURL');
						$servername = '<a href="'.$url.'">'.$url.'</a>';
					}
					return array('Error_Msg' => 'Для авторизации под пользователем с группой прав, не относящейся к скорой медицинской помощи, необходимо обратиться на сайт '.$servername, 'Error_Code' => '10');
				}
			}

			if ($this->blocked == 1) {
				// если заблочен, то авторизоваться не даём
				//return array('Error_Msg' => 'Ваша учётная запись заблокирована', 'Error_Code' => '10');
				return array('Error_Msg' => lang('Vasha_uchyotnaya_zapis_zablokirovana'), 'Error_Code' => '10');
			} else if ($this->password_temp == 1) {
				// если срок действия временного пароля вышел, то беда :)
				if (!empty($checkdate['Error_Msg'])) {
					return array('Error_Msg' => $checkdate['Error_Msg'], 'Error_Code' => '13');
				} if (empty($newpass)) {
					//return array('Error_Msg' => 'Необходимо сменить временный пароль', 'Error_Code' => '11');
					return array('Error_Msg' => lang('Neobhodimo_smenit_vremennyj_parol'), 'Error_Code' => '11');
				} else {
					// проверяем корректность пароля
					$check = $CI->User_model->checkPassword($newpass, $pass, $this);
					if (!empty($check['Error_Msg'])) {
						return array('Error_Msg' => $check['Error_Msg'], 'Error_Code' => '11');
					}
					// меняем пароль пользователю
					$this->pass = "{MD5}" . base64_encode(md5($newpass, TRUE));
					$this->password_temp = 0;
					$this->password_date = time();
					$this->post();
				}
			}

			if (!empty($checkdate['Error_Msg'])) {
				if (empty($newpass)) {
					// если временный пароль, то авторизоваться не даём, пока не пришлёт новый пароль
					return array('Error_Msg' => $checkdate['Error_Msg'], 'Error_Code' => '12');
				} else {
					// проверяем корректность пароля
					$check = $CI->User_model->checkPassword($newpass, $pass, $this);
					if (!empty($check['Error_Msg'])) {
						return array('Error_Msg' => $check['Error_Msg'], 'Error_Code' => '12');
					}
					// меняем пароль пользователю
					$this->pass = "{MD5}" . base64_encode(md5($newpass, TRUE));
					$this->password_temp = 0;
					$this->password_date = time();
					$this->post();
				}
			}

			// Проверяем ограничение на количество параллельных сеансов
			$CI = & get_instance();
			$CI->load->library('swParallelSessions', null, 'parallel_sessions');
			$CI->parallel_sessions->checkOnParallelSessions($this);

			$this->toSession();

			// Логируем логин
			if ( defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS ) {
				require_once(APPPATH . 'libraries/UserAudit.php');
				UserAudit::Login($login, $this->pmuser_id, session_id(), 1);
			}

			return true;
		} else {
			// Логируем неудачную попытку логина
			if ( defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS ) {
				require_once(APPPATH . 'libraries/UserAudit.php');
				$regBlock = false;
				if (UserAudit::getCountFailLogin($login) == 2) {
					$regBlock = true;
				}
				if ($regBlock == true) {
					UserAudit::LoginBlock($login, session_id(), 1);
				} else {
					UserAudit::LoginFail($login, session_id(), 1);
				}
			}
			return false;
		}
	}

	/**
	 * Добавление нового пользователя
	 * 
	 * @param string $name Полное имя
	 * @param string $login Логин
	 * @param string $pass Пароль
	 * @param string $surname Фамилия
	 * @param string $firname Имя
	 * @return pmAuthUser Добавленный пользователь
	 */
	static function add( $name, $login, $pass, $surname, $secname, $firname, $loginEmias = '' ) {
		if ( !pmAuthUser::exist($login) ) {
			$user = new pmAuthUser("uid=" . $login . "," . LDAP_USER_PATH, $name, $login, $pass, $surname, $secname, $firname, false,'', $loginEmias);
		} else {
			DieWithError('Невозможно добавить пользователя. Пользователь с таким логином уже существует!');
		}
		return $user;
	}

	/**
	 * Завершение сессии пользователя
	 */
	function logout() {
		if ( isset($_SESSION['login']) ) {
			unset($_SESSION['lpu_id']);
			unset($_SESSION['server_id']);
			unset($_SESSION['org_id']);
			unset($_SESSION['lpu_name']);
			unset($_SESSION['medpersonal_id']);
			unset($_SESSION['medsvidgrant_add']);
			unset($_SESSION['deniedarms']);
			unset($_SESSION['password_temp']);
			unset($_SESSION['password_last']);
			unset($_SESSION['password_date']);
			unset($_SESSION['shown_armlist']);
			unset($_SESSION['blocked']);
			unset($_SESSION['user_id']);
			unset($_SESSION['pmuser_id']);
			unset($_SESSION['surname']);
			unset($_SESSION['secname']);
			unset($_SESSION['firname']);
			unset($_SESSION['pass']);
			unset($_SESSION['login']);
			unset($_SESSION['user']);
			unset($_SESSION['email']);
			unset($_SESSION['phone']);
			unset($_SESSION['phone_act']);
			unset($_SESSION['phone_act_code']);
			unset($_SESSION['marshserial']);
			unset($_SESSION['swtoken']);
			unset($_SESSION['swtoken_enddate']);
			unset($_SESSION['desc']);
			unset($_SESSION['allgroups']);
			unset($_SESSION['groups']);
			unset($_SESSION['OrgFarmacy_id']);
			if ( isset($_SESSION['settings']) )
				unset($_SESSION['settings']);
			if ( isset($_SESSION['lis']) )
				unset($_SESSION['lis']);
		}
	}

	/**
	 * Обновление списка групп, к которым пользователь принадлежит
	 */
	private function refreshGroups() {
		$groups = new pmAuthGroups(array('forceGroupsReload' => true));
		$this->groups = array();
		$n = ldap_query(LDAP_GROUP_PATH, "member={$this->id}", array('cn'));
		for ( $i = 0; $i < $n["count"]; $i++ ) {
			array_push($this->groups, $groups->groups[$n[$i]["cn"][0]]);
		}
	}

	/**
	 * Формирование массива данных для сохранения в LDAP
	 */
	private function getData( $add = false, $disable = false, $is_farmacyadmin = false ) {
		// todo: данную функцию надо будет доработать, а точнее поля назвать в соответствии со смыслом
		// todo: Код ниже не понимаю зачем, но пока оставил
		// Включаем пользователя
		if ( !$disable ) {
			if ( $is_farmacyadmin === true )
				$this->enabled = 2;
			else
				$this->enabled = 1;
		}
		else {
			$this->enabled = 0;
		}

		$data = array();
		$data["cn"] = $this->name;
		if ( $this->surname )
			$data["sn"] = $this->surname;
		if ( $this->secname )
			$data["secname"] = $this->secname;
		if ( $this->firname )
			$data["givenname"] = $this->firname;
		if ( $this->pass )
			$data["userpassword"] = $this->pass;
		if ( $this->email )
			$data["email"] = $this->email;
		if ( $this->phone )
			$data["phone"] = $this->phone;
		if ( $this->phone_act )
			$data["phone_act"] = $this->phone_act;
		if ( $this->phone_act_code )
			$data["phone_act_code"] = $this->phone_act_code;
		if ( $this->marshserial )
			$data["marshserial"] = $this->marshserial;
		if ( $this->swtoken )
			$data["swtoken"] = $this->swtoken;
		if ( $this->swtoken_enddate )
			$data["swtoken_enddate"] = $this->swtoken_enddate;
		if ( $this->about )
			$data["about"] = $this->about;
		if ( $this->avatar )
			$data["avatar"] = $this->avatar;
		if ( $this->desc )
			$data["description"] = $this->desc;
		if ( $this->parallelSessions )
			$data["parallelSessions"] = $this->parallelSessions;
		if ( $this->loginEmias )
			$data["loginEmias"] = $this->loginEmias;
		if ( !$add ) {
			$data["orgid"] = array();
			$data["certs"] = array();
		}

		for ( $i = 0; $i < count($this->org); $i++ ) {
			$data["orgid"][$i] = intval($this->org[$i]["org_id"]);
		}
		for ( $i = 0; $i < count($this->certs); $i++ ) {
			$data["certs"][$i] = json_encode($this->certs[$i]);
		}
		$data["employeenumber"] = ($this->medpersonal_id) ? $this->medpersonal_id : "0";
		// настройки
		$data["medsvidgrantadd"] = (isset($this->medsvidgrant_add) && $this->medsvidgrant_add == 1) ? 1 : 0;
		$data["deniedarms"] = ($this->deniedarms) ? $this->deniedarms : 0;
		$data["password_temp"] = ($this->password_temp) ? $this->password_temp : 0;
		$data["password_last"] = ($this->password_last) ? $this->password_last : "[]";
		$data["password_date"] = ($this->password_date) ? $this->password_date : 0;
		$data["shown_armlist"] = ($this->shown_armlist) ? $this->shown_armlist : "[]";
		$data["blocked"] = (isset($this->blocked) && $this->blocked == 1) ? 1 : 0;

		if ( $this->settings )
			$data["pseudonym"][0] = $this->settings; // todo: надо поменять на settings
		if ( ($this->pmuser_id) and ( $this->pmuser_id != 0) ) // todo: надо поменять на pmuser_id
			$data["uidnumber"] = (double) $this->pmuser_id;
		if ( isset($this->enabled) )
			$data["organizationalstatus"] = $this->enabled; // todo: надо поменять на enabled	
		if ( $this->lis )
			$data["lis"] = $this->lis;
		if ( $add ) {
			$data["objectclass"][0] = "top";
			$data["objectclass"][1] = "person";
			$data["objectclass"][2] = "extensibleObject";
			$data["objectclass"][3] = "uidObject";
		}
		/*
		  $CI =& get_instance();
		  $CI->load->library('textlog', array('file'=>'pmuser_'.date('Y-m-d').'.log'));
		  $CI->textlog->add(var_export($data,true));
		 */
		return $data;
	}

	/**
	 * Сохранить изменения по человеку в LDAP
	 */
	public function post( $disable = false, $is_farmacyadmin = false ) {
		// Сохраняем данные
		$data = $this->getData(false, $disable, $is_farmacyadmin); // получаем данные класса в массив
		ldap_edit($this->id, $data);
		if ( isset($_SESSION['login']) && ($_SESSION['login'] == $this->login) && (isset($_SESSION['surname'])) ) { // при текущем пользователе надо отредактировать данные в сессии
			$this->toSession();
		}

		if ( $this->groups ) {
			$groups = new pmAuthGroups();

			$savedGroupIds = array();
			$savedGroups = array_values($groups->groupsById($this->id));
			foreach($savedGroups as $group) {
				$savedGroupIds[] = $group->id;
			}
			$groupsToSaveIds = array();
			$groupsToSave = array_values($this->groups);
			// добавляем новые
			foreach($groupsToSave as $group) {
				$groupsToSaveIds[] = $group->id;
				if (!in_array($group->id, $savedGroupIds)) {
					// если такой группы ещё нет, то добавляем
					ldap_insertattr($group->id, array("member" => $this->id));
					// сохранили
					$savedGroupIds[] = $group->id;
				}
			}

			// удаляем лишние
			foreach($savedGroups as $group) {
				if (!in_array($group->id, $groupsToSaveIds)) {
					ldap_removeattr($group->id, array("member" => $this->id));
				}
			}
		}
	}

	/**
	 * Вносим данные по человеку в LDAP
	 */
	public function insert( $disable = false, $is_farmacyadmin = false ) {
		$data = $this->getData(true, $disable, $is_farmacyadmin); // получаем данные класса в массив
		ldap_insert($this->id, $data);
		// добавляем группы
		foreach ( array_values($this->groups) as $group ) {
			ldap_insertattr($group->id, array("member" => $this->id));
		}
	}

	/**
	 * Удаляем данные по человеку из LDAP
	 */
	public function remove() {
		$q = ldap_query(LDAP_USER_PATH, "uid={$this->login}", array());
		if ( $q["count"] <> 0 )
			ldap_remove($this->id);
		// чистим группы
		$groups = new pmAuthGroups();
		foreach ( array_values($groups->groupsById($this->id)) as $group ) {
			ldap_removeattr($group->id, array("member" => $this->id));
		}
	}

	/**
	 * Добавить группу
	 * @param string $groupname Название группы
	 */
	public function addGroup( $groupname ) {
		if ( !isset($this->groups->groups[$groupname]) ) {
			$groups = new pmAuthGroups();
			// Если в списке групп нет нужной, то принудительно начитываем список из LDAP
			// @task https://redmine.swan.perm.ru/issues/100655
			if ( !isset($groups->groups[$groupname]) ) {
				$groups = new pmAuthGroups(array('forceGroupsReload' => true));
			}
			if ( isset($groups->groups[$groupname]) ) {
				array_push($this->groups, $groups->groups[$groupname]);
			}
		}
	}

	/**
	 * Удалить группу
	 * @param string $groupname Название группы
	 */
	public function removeGroup( $groupname ) {
		foreach ( $this->groups as $key => $value ) {
			if ( $value->name == $groupname )
				unset($this->groups[$key]);
		}
	}

	/**
	 * Добавить сертификат
	 * @param string SHA1 Название группы
	 */
	public function addCert( $cert ) {
		array_push($this->certs, $cert);
	}

	/**
	 * Удалить сертификат
	 * @param string $cert_id ID сертификата
	 */
	public function removeCert( $cert_id ) {
		foreach ( $this->groups as $key => $value ) {
			if ( $value->cert_id == $cert_id )
				unset($this->groups[$key]);
		}
	}

	/**
	 * Проверка того, имеет ли пользователь заданную группу
	 * 
	 * @param string $groupname Название группы
	 * @return boolean Есть ли у пользователя группа
	 */
	public function havingGroup( $groupname, $defined_groups = null ) {
		if ($defined_groups === null) {
			$defined_groups = $this->groups;
		}
		foreach ( $defined_groups as $key => $value ) {
			if ( defined('USE_UTF') && USE_UTF ) {
				if ( mb_strtolower($value->name) == mb_strtolower($groupname) )
					return true;
			} else {
				if ( strtolower($value->name) == strtolower($groupname) )
					return true;
			}
		}
		return false;
	}

	/**
	 * Проверка того, имеет одну из заданных групп
	 * 
	 * @param array $groups Массив с названиями групп
	 * @return boolean Есть ли у пользователя хоть одна из заданных групп
	 */
	public function hasOneOfGroup( $groups, $defined_groups = null ) {
		array_walk($groups, 'ConvertToLowerCase');

		if ($defined_groups === null) {
			$defined_groups = $this->groups;
		}
		foreach ( $defined_groups as $key => $value ) {
			if ( in_array(mb_strtolower($value->name), $groups) )
				return true;
		}
		return false;
	}

	/**
	 * Проверка того, имеет одну из групп, за исключением заданных
	 *
	 * @param array $groups Массив с названиями групп
	 * @return boolean Есть ли у пользователя хоть одна из групп, кроме заданных групп
	 */
	public function hasOneOfGroupExceptOf( $groups, $defined_groups = null ) {
		array_walk($groups, 'ConvertToLowerCase');

		if ($defined_groups === null) {
			$defined_groups = $this->groups;
		}
		foreach ( $defined_groups as $key => $value ) {
			if ( !in_array(mb_strtolower($value->name), $groups) )
				return true;
		}
		return false;
	}

	/**
	 * Проверка того, что пользователь работает в ЛПУ
	 * Если задано lpu_id, то именно этому ЛПУ, если не задано, то хоть какому-то ЛПУ
	 * 
	 * @return boolean Работает ли пользователь в ЛПУ
	 */
	public function belongsToOrg( $org_id = null ) {
		if ( isset($org_id) ) {
			return isset($this->orgtype) && $this->orgtype == 'lpu' && havingOrg($org_id);
		} else {
			return isset($this->orgtype) && $this->orgtype == 'lpu';
		}
	}

	/**
	 * Возвращает количество групп пользователя
	 * 
	 * @return integer Количество групп у пользователя
	 */
	public function getGroupCount() {
		return count($this->groups);
	}

	/**
	 * Возвращает все группы пользователя массивом
	 * 
	 * @return array Список групп пользователя
	 */
	public function getGroups() {
		$groups = array();
		foreach ( array_values($this->groups) as $group ) {
			$groups[] = array(
				'Group_id' => $group->id,
				'Group_Name' => $group->name,
				'Group_Desc' => $group->desc
			);
		}
		return $groups;
	}

	public function getMinParallelSessionsCount()
	{
		$parallelSessionsTmp = array();
		foreach ( array_values($this->groups) as $group) {
			if ( $group->parallelSessions != NULL ) {
				$parallelSessionsTmp[] = $group->parallelSessions;
			}
		}

		if (count($parallelSessionsTmp) > 0) {
			return min($parallelSessionsTmp);
		} else {
			return 0;
		}
	}

	/**
	 * Добавление организации в список организаций прикрепленных к пользователю
	 * 
	 * @param integer $org_id Идентификатор организации
	 */
	public function addOrg( $org_id ) {
		if ( array_search($org_id, $this->org) === FALSE )
			$this->org[] = array('org_id' => $org_id);
	}

	/**
	 * Проверка того прикреплен ли пользователь к организации
	 * 
	 * @param integer $org_id Идентификатор организации
	 * @return boolean Прикрплен ли пользователь к организации
	 */
	public function havingOrg( $org_id ) {
		foreach ( $this->org as $key => $value ) {
			if ( $value['org_id'] == $org_id )
				return true;
		}
		return false;
	}

	/**
	 * Генерация идентификатора пользователя
	 * 
	 * @return integer Идентификатор пользователя
	 */
	private function genUserId() {
		return ceil((microtime(true) - strtotime("01 April 2009 00:00")) * 1000)/*.rand(0,9)*/;
	}

	/**
	 * Проверка может ли пользователь загрузить данную форму
	 * 
	 * @param string $form_name Имя формы
	 * @return boolean
	 *
	 * Deprecated
	 */
	/*public function canLoadForm( $form_name ) {
		//TO-DO: Построить для всего этого дела нормальную таблицу соотвествий прав пользователя и возможности загрузки форм
		switch ( $form_name ) {
			case 'swUsersTreeViewWindow': // Формы только для администраторов
			case 'swUserEditWindow':
			case 'LpuUnitEditForm':
			case 'LpuSectionEditForm':
			case 'LpuStructureViewForm':
			case 'swMedStaffFactEditWindow':
			case 'swMedPersonalViewWindow':
				if ( $this->havingGroup(SUPER_ADMIN) || $this->havingGroup(LPU_ADMIN) || $this->havingGroup(FARMACY_ADMIN) || $this->havingGroup('FarmacyNetAdmin') )
					return true;
				else
					return false;
				break;
			//  case 'swReportViewWindow':
			//  if ($this->havingGroup(SUPER_ADMIN) || $this->havingGroup(LPU_ADMIN) || $this->havingGroup(LPU_POWERUSER) )
			//  return true;
			//  else
			//  return false;
			//  break;
			case 'swOrgFarmacyViewWindow':
				if ( $this->havingGroup(SUPER_ADMIN) )
					return true;
				else
					return false;
				break;
			default:
				return true;
		}
	}*/

	/**
	 * Проверка является ли пользователь суперадмином
	 */
	function isSuperadmin() {
		return $this->havingGroup('SuperAdmin');
	}

}

/**
 * Список пользователей
 */
class pmAuthUsers {

	public $users = array();

	/**
	 * Конструктор
	 * 
	 * @param string $query Строка запроса, если пустая, то не загружаем вообще список, если * - загружаем всех
	 */
	function __construct( $query = '' ) {
		if ( $query == '' ) {
			return;
		}
		if ( $query == '*' ) {
			$this->refresh();
		} else {
			$this->refresh($query);
		}
	}

	/**
	 * Обновление списка пользователей
	 * 
	 * @param string $query Строка запроса, если пустая, то не загружаем вообще список, если * - загружаем всех
	 */
	function refresh( $query = '' ) {
		$users = array();
		if ( $query == '' ) {
			$ldap = ldap_query(LDAP_USER_PATH, "uid=*", pmAuthUser::$ldap_user_fields);
		} else {
			$ldap = ldap_query(LDAP_USER_PATH, $query, pmAuthUser::$ldap_user_fields);
		}
		for ( $i = 0; $i < $ldap["count"]; $i++ ) {
			if ( !isset($ldap[$i]["cn"]) )
				continue;
			$pmuser_id = $ldap[$i]["dn"];
			$user_login = $ldap[$i]["uid"][0];
			$user_name = $ldap[$i]["cn"][0];
			$user_pass = (isset($ldap[$i]["userpassword"][0])) ? $ldap[$i]["userpassword"][0] : "";
			$user_surname = (isset($ldap[$i]["sn"][0])) ? $ldap[$i]["sn"][0] : "";
			$user_secname = (isset($ldap[$i]["secname"][0])) ? $ldap[$i]["secname"][0] : "";
			$user_firname = (isset($ldap[$i]["givenname"][0])) ? $ldap[$i]["givenname"][0] : "";
			$user = new pmAuthUser($pmuser_id, $user_name, $user_login, $user_pass, $user_surname, $user_secname, $user_firname);
			$user->email = (isset($ldap[$i]["email"][0])) ? $ldap[$i]["email"][0] : "";
			$user->marshserial = (isset($ldap[$i]["marshserial"][0])) ? $ldap[$i]["marshserial"][0] : "";
			$user->swtoken = (isset($ldap[$i]["swtoken"][0])) ? $ldap[$i]["swtoken"][0] : "";
			$user->swtoken_enddate = (isset($ldap[$i]["swtoken_enddate"][0])) ? $ldap[$i]["swtoken_enddate"][0] : "";
			$user->about = (isset($ldap[$i]["about"][0])) ? $ldap[$i]["about"][0] : "";
			$user->avatar = (isset($ldap[$i]["avatar"][0])) ? $ldap[$i]["avatar"][0] : "";
			$user->desc = (isset($ldap[$i]["description"][0])) ? $ldap[$i]["description"][0] : "";
			$user->medpersonal_id = (isset($ldap[$i]["employeenumber"][0])) ? $ldap[$i]["employeenumber"][0] : "";
			$user->medsvidgrant_add = (isset($ldap[$i]["medsvidgrantadd"][0])) ? $ldap[$i]["medsvidgrantadd"][0] : "";
			$user->deniedarms = (isset($ldap[$i]["deniedarms"][0])) ? $ldap[$i]["deniedarms"][0] : "";
			$user->blocked = (isset($ldap[$i]["blocked"][0])) ? $ldap[$i]["blocked"][0] : "";
			$user->lis = (isset($ldap[$i]["lis"][0])) ? $ldap[$i]["lis"][0] : null;
			if ( isset($ldap[$i]["orgid"]) ) {
				for ( $j = 0; $j < count($ldap[$i]["orgid"]); $j++ ) {
					if ( isset($ldap[$i]["orgid"][$j]) ) {
						array_push($user->org, array("org_id" => $ldap[$i]["orgid"][$j]));
					}
				}
			}
			if ( isset($ldap[$i]["certs"]) ) {
				for ( $j = 0; $j < count($ldap[$i]["certs"]); $j++ ) {
					if ( isset($ldap[$i]["certs"][$j]) ) {
						array_push($user->certs, json_decode($ldap[$i]["certs"][$j]));
					}
				}
			}

			$user->settings = (isset($ldap[$i]["pseudonym"][0])) ? $ldap[$i]["pseudonym"][0] : "";
			if ( isset($ldap[$i]["uidnumber"][0]) and ( $ldap[$i]["uidnumber"][0] != 0) ) {
				$user->pmuser_id = $ldap[$i]["uidnumber"][0];
			}
			$user->enabled = (isset($ldap[$i]["organizationalstatus"][0])) ? $ldap[$i]["organizationalstatus"][0] : 1;
			$this->refreshUserGroups($user);
			$this->users[$user_login] = $user;
		}
	}

	/**
	 * Обновление списка групп, к которым пользователь принадлежит
	 */
	private function refreshUserGroups( &$user ) {
		$groups = new pmAuthGroups();
		$user->groups = array();
		$n = ldap_query(LDAP_GROUP_PATH, "member={$user->id}", array('cn'));
		for ( $i = 0; $i < $n["count"]; $i++ ) {
			array_push($user->groups, $groups->groups[$n[$i]["cn"][0]]);
		}
	}

	/**
	 * Добавление нового пользователя
	 * 
	 * @param string $name Полное имя
	 * @param string $login Логин
	 * @param string $pass Пароль
	 * @param string $surname Фамилия
	 * @param string $secname Отчество
	 * @param string $firname Имя
	 * @return pmAuthUser Добавленный пользователь
	 */
	function addUser( $name, $login, $pass, $surname, $secname, $firname ) {
		if ( !pmAuthUser::exist($login) )
			$user = new pmAuthUser("uid=" . $login . "," . LDAP_USER_PATH, $name, $login, $pass, $surname, $secname, $firname);
		else
			die(json_encode(array("success" => false, Error_Msg => "Пользователь $login уже существует!\n")));
		return $user;
	}

	/**
	 * Поиск пользователей по логину
	 * @param string $login Логин пользователя
	 * @return pmAuthUser Данные о пользователе
	 */
	function getUserByLogin( $login ) {
		foreach ( $this->users as $user ) {
			if ( $user->login == $login )
				return $user;
		}
		return false;
	}

}

/**
 * Адресная книга
 */
class pmAdressBooks {

	// идентификатор (текст)
	public $id;
	// Наименование адресной книги
	public $name;
	// Тип адресной книги
	public $desc;
	// Тип адресной книги
	public $type;
	// организации 
	public $organizations;
	// пользователи
	public $users;

	/**
	 * Конструктор
	 * 
	 * @param <type> $id
	 * @param <type> $name
	 * @param <type> $desc
	 * @param <type> $type
	 */
	function __construct( $id, $name, $desc, $type ) {
		$namef = $this->genAddressBook();
		if ( empty($id) )
			$this->id = "cn=" . $namef . ',' . LDAP_ADDRBOOK_PATH;
		else
			$this->id = $id;
		if ( empty($name) )
			$this->name = $namef;
		else
			$this->name = $name;
		$this->desc = $desc;
		$this->type = $type;
		$this->organizations = array(); // организация пользователя, на которого добавляется АК (если АК - локальная)
		$this->pmuser_id = null;  // пользователь, на которого добавляется АК (если АК - персональная)
		$this->users = array();   // список пользователей в АК
	}

	/**
	 * Поиск адресной книги по уникальному наимерованию
	 * 
	 * @param string $name Логин пользователя
	 * @return pmAuthUser
	 */
	static function load( $lpu_id, $user_id ) {
		// ищем все записи под определенным Lpu_id (o), определенным пользователем (ou) или (без o и без ou)
		// $f="(|(o={$lpu_id})(ou={$user_id})"; // (!(&(ou=*)(o=*))))
		// $f="(&(!(ou=Books))(|(o={$lpu_id})(ou={$user_id})(&(!(ou=*))(!(o=*)))))"; // по идее это должно работать, но не работает!
		// $f="(|(&(!(ou=Books))(&(!(ou=*))(!(o=*))))(|(o={$lpu_id})(ou={$user_id})))"; // (!(&(ou=*)(o=*)))) //  // 
		$f = "(&(!(ou=Books))(|(o={$lpu_id})(u={$user_id})(sn=0)))"; // (&(ou=0)(o=0))

		$q = ldap_query(LDAP_ADDRBOOK_PATH, $f);
		$result = array();
		if ( $q["count"] > 0 ) {
			for ( $i = 0; $i < $q["count"]; $i++ ) {
				$result[$i]['dn'] = $q[$i]["dn"];
				$result[$i]['name'] = $q[$i]["cn"][0];
				$result[$i]['desc'] = (isset($q[$i]["description"][0])) ? $q[$i]["description"][0] : "";
				$result[$i]['type'] = (isset($q[$i]["sn"][0])) ? $q[$i]["sn"][0] : null;
				// обрабатываем внешний вид 
				switch ( $result[$i]['type'] ) {
					case 0: $result[$i]['iconCls'] = 'group-global16';
						break;
					case 1: $result[$i]['iconCls'] = 'group-local16';
						break;
					default: $result[$i]['iconCls'] = 'group-private16';
						break;
				}

				$result[$i]['leaf'] = true;
				$result[$i]['text'] = $result[$i]['desc'];
				$result[$i]['id'] = $result[$i]['name'];

				//$result[$i]['pmuser_id'] = $result[$i]['u'];

				$result[$i]['organizations'] = array();
				$result[$i]['users'] = array();
				/*
				  if (isset($q[$i]["o"])) {
				  for ($j = 0; $j<count($q[$i]["o"]); $j++) {
				  if (isset($q[$i]["o"][$j])) {
				  array_push($result[$i]['organizations'],$q[$i]["o"][$j]);
				  }
				  }
				  }
				  if (isset($q[$i]["ou"])) {
				  for ($j = 0; $j<count($q[$i]["ou"]); $j++) {
				  if (isset($q[$i]["ou"][$j])) {
				  array_push($result[$i]['users'],$q[$i]["o"][$j]);
				  }
				  }
				  } */
			}
		}
		return $result;
	}

	/**
	 * Список адресных книг, в которых нет указанного пользователя
	 * 
	 * @param string $name Логин пользователя
	 * @return pmAuthUser
	 */
	static function no_user_books( $lpu_id, $pmUser_id, $user_id ) {
		$f = "(&(!(ou=Books))(|(o={$lpu_id})(u={$pmUser_id})(sn=0)))"; // (&(ou=0)(o=0))

		$q = ldap_query(LDAP_ADDRBOOK_PATH, $f);
		$result = array();
		$k = 0;
		if ( $q["count"] > 0 ) {
			for ( $i = 0; $i < $q["count"]; $i++ ) {
				$isuser = false;
				if ( isset($q[$i]["ou"]) ) {
					$j = 0;
					while ( $j < count($q[$i]["ou"]) && (!$isuser) ) {
						if ( isset($q[$i]["ou"][$j]) ) {
							if ( $q[$i]["ou"][$j] == $user_id ) {
								$isuser = true;
							}
						}
						$j++;
					}
				}
				if ( !$isuser ) {
					$result[$k]['dn'] = $q[$i]["dn"];
					$result[$k]['code'] = $q[$i]["cn"][0];
					$result[$k]['name'] = (isset($q[$i]["description"][0])) ? $q[$i]["description"][0] : "";
					$result[$k]['type'] = (isset($q[$i]["sn"][0])) ? $q[$i]["sn"][0] : null;
					$result[$k]['id'] = $result[$k]['code'];
					switch ( $result[$k]['type'] ) {
						case 0: $result[$k]['iconCls'] = 'group-global16';
							break;
						case 1: $result[$k]['iconCls'] = 'group-local16';
							break;
						default: $result[$k]['iconCls'] = 'group-private16';
							break;
					}
					$k++;
				}
			}
		}
		return $result;
	}

	/**
	 * Проверяет, существует ли адресная книга с таким названием у данного пользователя
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	static function exist( $name, $desc ) {
		$q = ldap_query(LDAP_ADDRBOOK_PATH, "(&(cn=$name)(description=$desc))", array());
		if ( $q["count"] == 1 ) {
			return true;
		}
		return false;
	}

	/**
	 * Проверяет существует ли пользователь с таким логином
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	private function user_exist( $name ) {
		$q = ldap_query(LDAP_ADDRBOOK_PATH, "(&(cn={$this->name})(ou={$name}))", array());
		if ( $q["count"] > 0 ) {
			return true;
		}
		return false;
	}

	/**
	 * Проверяет, существует ли организация с таким названием
	 * 
	 * @param string $login Логин пользователя
	 * @return pmAuthUser
	 */
	private function organization_exist( $name ) {
		$q = ldap_query(LDAP_ADDRBOOK_PATH, "(&(cn={$this->name})(o={$name}))", array());
		if ( $q["count"] == 1 ) {
			return true;
		}
		return false;
	}

	/**
	 * Пересохранение данных книги
	 */
	public function add() {
		$this->remove();
		$this->insert();
	}

	/**
	 * Добавление новой адресной книги в LDAP
	 */
	private function insert() {
		$data = array();
		$data["cn"] = $this->name;
		if ( isset($this->desc) )
			$data["description"] = $this->desc;
		$data["sn"] = $this->type;

		if ( $this->type == 2 ) // персональная АК
			$data["u"] = $this->pmuser_id;

		// локальная АК 
		for ( $i = 0; $i < count($this->organizations); $i++ ) {
			$data["o"][$i] = (float) $this->organizations[$i];
		}

		for ( $i = 0; $i < count($this->users); $i++ ) {
			$data["ou"][$i] = (float) $this->users[$i];
		}

		//$data["objectclass"][0] = "document";
		$data["objectclass"][0] = "top";
		$data["objectclass"][1] = "person";
		$data["objectclass"][2] = "extensibleObject";
		ldap_insert($this->id, $data);
	}

	/**
	 * Добавление пользователя в группу
	 */
	public function user_insert( $id ) {
		if ( !$this->user_exist($id) ) {
			ldap_insertattr($this->id, array("ou" => array((float) $id)));
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление пользователя из группы
	 */
	public function user_delete( $id ) {
		if ( $this->user_exist($id) ) {
			ldap_removeattr($this->id, array("ou" => $id));
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка пользователей по указанной группе
	 */
	public function users( $add_empty = true ) {
		$q = ldap_query(LDAP_ADDRBOOK_PATH, "(cn={$this->name})", array('ou'));
		$result = array();
		if ( $q["count"] > 0 ) {
			for ( $i = 0; $i < $q["count"]; $i++ ) {
				if ( isset($q[$i]["ou"]) ) {
					for ( $j = 0; $j < count($q[$i]["ou"]); $j++ ) {
						if ( isset($q[$i]["ou"][$j]) ) {
							array_push($result, $q[$i]["ou"][$j]);
						}
					}
				}
				if ( $add_empty )
					array_push($result, 0);
			}
		}
		return $result;
	}

	/**
	 * Внутрення функция для вставки организации и сохранения атрибутов
	 */
	private function organization_insert( $id ) {
		if ( !$this->organization_exist($id) ) {
			ldap_insertattr($this->id, array("o" => $id));
		}
	}

	/**
	 * Редактирование адресной книги в LDAP
	 * Изменяется только описание /и тип/
	 */
	public function edit( $access ) {
		$data = array();
		if ( $this->desc )
			$data["description"] = $this->desc;
		if ( $access === true ) {

			$data["sn"] = $this->type;
			if ( $this->type == 2 ) // персональная АК
				$data["u"] = $this->pmuser_id;

			// локальная АК 
			for ( $i = 0; $i < count($this->organizations); $i++ ) {
				$data["o"][$i] = (float) $this->organizations[$i];
			}

			for ( $i = 0; $i < count($this->users); $i++ ) {
				$data["ou"][$i] = (float) $this->users[$i];
			}
		}
		ldap_edit($this->id, $data);
	}

	/**
	 * Удаляем адресную книгу из LDAP
	 */
	public function remove( $id = null ) {
		// TODO: Может быть Тут должна быть проверка на то, чтобы в книге не было данных
		$q = ldap_query(LDAP_ADDRBOOK_PATH, "cn={$this->name}", array());
		if ( $q["count"] <> 0 )
			ldap_remove($this->id);
	}

	/**
	 * Генерация идентификатора адресной книги
	 * 
	 * @return integer Идентификатор адресной книги
	 */
	private function genAddressBook() {
		return ceil((microtime(true) - strtotime("01 April 2009 00:00")) * 10);
	}

}

?>