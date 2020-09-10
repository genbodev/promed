<?php
require_once("User_model_common.php");
require_once("User_model_get.php");
/**
 * User_model - модель для работы с учетными записями пользователей
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
 * @property CI_DB_driver $db
 * @property LpuStructure_model $lsmodel
 * @property Options_model $Options_model
 * @property MedPersonal_model $MedPersonal_model
 * @property Org_model $orgmodel
*/
class User_model extends SwPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";
	public $dateTimeForm121 = "YYYY-MM-DD HH24:MI:SS.mi";
	/**
	 * Префикс Arm_id для БСМЭ
	 */
	const BSME_ARM_ID_PREF = 200;

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Берем один символ из строки в utf-кодировке
	 * @param $str
	 * @param $pos
	 * @return string
	 */
	public function char($str, $pos)
	{
		return mb_substr($str, $pos, 1, 'UTF-8');
	}

	/**
	 *
	 */
	public function generateNewUsers() {
		$list = $this->queryResult("
			select
				msf.Person_SurName as \"Person_SurName\",
				msf.Person_FirName as \"Person_FirName\",
				msf.Person_SecName as \"Person_SecName\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				l.Org_id as \"Org_id\"
			from
				v_MedStaffFact msf
				inner join v_Lpu l on l.Lpu_id = msf.Lpu_id
			where not exists (
				select
					pmUser_id
				from pmUserCache
				where MedPersonal_id = msf.MedPersonal_id
			  	limit 1
			)
		");

		$doneList = [];
		$usedLogins = [];

		if ( is_array($list) && count($list) > 0 ) {
			$log_file = EXPORTPATH_ROOT . date('YmdHis') . '_' . swGenRandomString(32) . '_generateNewUsers.csv';
			$sessionParams = getSessionParams();
			//var_dump($sessionParams); die();

			// настройки для новых пользователей
			$opt = @serialize([
				'recepts' => [
					'print_extension' => 1
				]
			]);

			file_put_contents($log_file, toAnsi("ФИО;Логин;Пароль\r\n", true));

			foreach ( $list as $row ) {
				if ( isset($doneList[$row['MedPersonal_id']]) ) {
					continue;
				}

				$login = mb_ucfirst(mb_strtolower(translit(mb_substr($row['Person_SurName'], 0, 3))));
				$login .= mb_ucfirst(translit(mb_substr($row['Person_FirName'], 0, 1)));

				if ( !empty($row['Person_SecName']) ) {
					$login .= mb_ucfirst(translit(mb_substr($row['Person_SecName'], 0, 1)));
				}

				if ( !isset($usedLogins[$login]) ) {
					$usedLogins[$login] = 0;
				}
				else {
					$usedLogins[$login]++;
				}

				//$login = 'admin';

				$userData = [
					'login' => $login . (!empty($usedLogins[$login]) ? $usedLogins[$login] : ''),
					'pass' => swGenRandomString(6),
					'surname' => $row['Person_SurName'],
					'firname' => $row['Person_FirName'],
					'secname' => $row['Person_SecName'],
					'MedPersonal_id' => $row['MedPersonal_id'],
					'orgs' => [ $row['Org_id'] ],
					'groups' => [ 2 ],
					'groupsNames' => [ 'LpuUser' ],
				];

				$user = pmAuthUser::find($userData['login']);

				if ( $user instanceof pmAuthUser && $user->medpersonal_id == $row['MedPersonal_id'] ) {
					continue;
				}

				while ( $user instanceof pmAuthUser ) {
					if ( !isset($usedLogins[$login]) ) {
						$usedLogins[$login] = 0;
					}

					$usedLogins[$login]++;
					$userData['login'] = $login . $usedLogins[$login];
					$user = pmAuthUser::find($userData['login']);

					if ( $user instanceof pmAuthUser && $user->medpersonal_id == $row['MedPersonal_id'] ) {
						break;
					}
				}

				if ( $user instanceof pmAuthUser && $user->medpersonal_id == $row['MedPersonal_id'] ) {
					continue;
				}

				$newUser = pmAuthUser::add(trim($userData['surname'] . " " . $userData['firname']), $userData['login'], $userData['pass'], $userData['surname'], $userData['secname'], $userData['firname']);

				// добавляем новые группы
				foreach ( $userData['groupsNames'] as $group ) {
					$newUser->addGroup($group);
				}

				foreach ( $userData['groups'] as $group ) {
					$this->db->query("
						select
							pmUserCacheGroupLink_id as \"pmUserCacheGroup_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_pmUserCacheGroupLink_ins(
							pmUserCacheGroup_id := :pmUserCacheGroup_id,
							pmUserCache_id := :pmUserCache_id,
							pmUser_id := :pmUser_id
						)
					", [
						'pmUserCache_id' => $newUser->pmuser_id,
						'pmUserCacheGroup_id' => $group,
						'pmUser_id' => $sessionParams['pmUser_id'],
					]);
				}

				// добавляем организации
				foreach ( $userData['orgs'] as $org ) {
					$newUser->addOrg($org);
				}

				$newUser->medpersonal_id = $userData['MedPersonal_id'];

				$newUser->password_temp = 1;
				$newUser->password_date = time();
				$newUser->settings = $opt;

				$newUser->insert();

				$this->ReCacheUserData($newUser, $sessionParams);

				// Пишем в лог
				$s = trim($userData['surname'] . ' ' . $userData['firname'] . ' ' . $userData['secname']) . ";"
					. $userData['login'] . ";" . $userData['pass'] . "\r\n";

				file_put_contents($log_file, toAnsi($s, true), FILE_APPEND);

				$doneList[$row['MedPersonal_id']] = $userData;
			}
		}

		return
			'<div>Добавлено пользователей: ' . count($doneList) . '</div>'
			. (count($doneList) > 0 ? '<div>Файл: <a href="' . $log_file . '">ссылка</a></div>' : '');
	}

	public function getNotAdminUsers()
	{
		$query = "
			select
				PMUser_id as \"pmUser_id\"
			from
				pmUserCache
			where
				PMUser_Login not like '%swnt%' and pmUser_groups not like '%admin%' and PMUser_Blocked != 1
		";

		$res = $this->db->query($query);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return array();
		}
	}

	public function getLpuAdminList()
	{
		$query = "
			select
				PMUser_id as \"pmUser_id\"
			from
				pmUserCache
			where
				PMUser_login = 'Streetmike' and pmUser_groups like '%LpuAdmin%' and PMUser_Blocked != 1
		";

		$res = $this->db->query($query);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return array();
		}
	}

	#region get
	/**
	 * Дополнительное условие для отображения АРМ приемного отделения
	 * @param $data
	 * @return bool
	 */
	function getStacPriemAdditionalCondition($data)
	{
		return User_model_get::getStacPriemAdditionalCondition($data);
	}

	/**
	 * Получение списка армов для мест работы
	 * @param $data
	 * @param $response
	 * @return array
	 */
	function getArmsForMedStaffFactList($data, $response)
	{
		return User_model_get::getArmsForMedStaffFactList($this, $data, $response);
	}

	/**
	 * Функция возвращает список всех имеющихся в системе армов (пока хранится непосредственно в модели)
	 * @return array
	 */
	function getARMList()
	{
		return User_model_get::getARMList($this);
	}

	/**
	 * Возвращает список типов АРМ
	 * @return array|false
	 */
	function getARMTypeList()
	{
		return User_model_get::getARMTypeList($this);
	}

	/**
	 * Возвращает список типов АРМ для комбо на основании массива в методе loadARMList
	 * @param $data
	 * @return array
	 */
	public function getPHPARMTypeList($data)
	{
		return User_model_get::getPHPARMTypeList($this, $data);
	}

	/**
	 * @param $lpu_nick
	 * @return bool
	 */
	function getLpuIdFromLpuNick($lpu_nick)
	{
		return User_model_get::getLpuIdFromLpuNick($this, $lpu_nick);
	}

	/**
	 * @param $lpu_id
	 * @return bool
	 */
	function getLpuNickFromLpuId($lpu_id)
	{
		return User_model_get::getLpuNickFromLpuId($this, $lpu_id);
	}

	/**
	 * @param $org_id
	 * @return string
	 */
	function getOrgNickFromOrgId($org_id)
	{
		return User_model_get::getOrgNickFromOrgId($this, $org_id);
	}

	/**
	 * @param $lpu_name
	 * @return mixed|bool
	 */
	function getLpuIdFromLpuName($lpu_name)
	{
		return User_model_get::getLpuIdFromLpuName($this, $lpu_name);
	}

	/**
	 * Возвращает список типов организаций
	 * @param $data
	 * @return array|false
	 */
	function getOrgTypeTree($data)
	{
		return User_model_get::getOrgTypeTree($this, $data);
	}

	/**
	 * Возвращает список организаций для дерева
	 * @param $data
	 * @param $superadmin
	 * @return array|bool
	 */
	function getOrgUsersTree($data, $superadmin)
	{
		return User_model_get::getOrgUsersTree($this, $data, $superadmin);
	}

	/**
	 * Возвращает список ЛПУ доступных для данного пользователя
	 * @param $data
	 * @param $superadmin
	 * @return array|bool
	 */
	function getLpuUsersTree($data, $superadmin)
	{
		return User_model_get::getLpuUsersTree($this, $data, $superadmin);
	}

	/**
	 * Возвращает список аптек доступных для данного пользователя
	 * @param $data
	 * @param $superadmin
	 * @param bool $farmacynetadmin
	 * @return array|bool
	 */
	function getOrgFarmacyUsersTree($data, $superadmin, $farmacynetadmin = false)
	{
		return User_model_get::getOrgFarmacyUsersTree($this, $data, $superadmin, $farmacynetadmin);
	}

	/**
	 * Возвращает список информации о текущем ЛПУ
	 * @param $data
	 * @return array|bool
	 */
	function getCurrentLpuData($data)
	{
		return User_model_get::getCurrentLpuData($this, $data);
	}

	/**
	 * Возвращает список информации о текущей организации
	 * @param $data
	 * @return array|bool
	 */
	function getCurrentOrgData($data)
	{
		return User_model_get::getCurrentOrgData($this, $data);
	}

	/**
	 * Возвращает список АРМов, не относящихся к службам и местам работы
	 * @param $data
	 * @return array
	 */
	function getOtherARMList($data)
	{
		return User_model_get::getOtherARMList($this, $data);
	}

	/**
	 * Возвращает список АРМов, относящихся к иным организациям
	 * @param $data
	 * @return array
	 */
	function getOtherOrgARMList($data)
	{
		return User_model_get::getOtherOrgARMList($this, $data);
	}

	/**
	 * Возвращает список мест работы врача
	 * @param $data
	 * @return array|bool
	 */
	function getUserMedStaffFactList($data)
	{
		return User_model_get::getUserMedStaffFactList($this, $data);
	}

	/**
	 * Возвращает список мест работы
	 * @param $data
	 * @return array|bool
	 */
	function getMedStaffFact($data)
	{
		return User_model_get::getMedStaffFact($this, $data);
	}

	/**
	 * Возвращает список мест работы по идентификатору пользователя, включая места работы другого врача, где пользователь является средним медперсоналом для него
	 * @param $pmUser_id
	 * @return array
	 */
	function getMedStaffFactsBypmUser($pmUser_id)
	{
		return User_model_get::getMedStaffFactsBypmUser($this, $pmUser_id);
	}

	/**
	 * Возвращает данные по месту работы
	 * @param $data
	 * @return bool|mixed
	 */
	function getMedStaffFactData($data)
	{
		return User_model_get::getMedStaffFactData($this, $data);
	}

	/**
	 * Определение наличия рабочего места с должностью «Главная медсестра»
	 * @param $data
	 * @return bool
	 */
	function getHeadNursePost($data)
	{
		return User_model_get::getHeadNursePost($this, $data);
	}

	/**
	 * Возвращает данные по рабочему месту врача ЛЛО поликлинники (если врач ЛЛО) иначе false
	 * @param $data
	 * @return bool|mixed
	 */
	function getLLOData($data)
	{
		return User_model_get::getLLOData($this, $data);
	}

	/**
	 * Возвращает идентификатор мед. персонала по номеру социальной карты
	 * @param $data
	 * @return array|bool
	 */
	function getMedPersonalBySocCardNum($data)
	{
		return User_model_get::getMedPersonalBySocCardNum($this, $data);
	}

	/**
	 * Возвращает идентификатор мед. персонала по ИИН
	 * @param $data
	 * @return array|false
	 */
	function getMedPersonalByIin($data)
	{
		return User_model_get::getMedPersonalByIin($this, $data);
	}

	/**
	 * Возвращает идентификатор мед. персонала по ФИО и ДР
	 * @param $data
	 * @return array|bool
	 */
	function getMedPersonalByFIODR($data)
	{
		return User_model_get::getMedPersonalByFIODR($this, $data);
	}

	/**
	 * Возвращает список наименование текущей аптеки
	 * @param $data
	 * @return array|bool
	 */
	function getCurrentOrgFarmacyData($data)
	{
		return User_model_get::getCurrentOrgFarmacyData($this, $data);
	}

	/**
	 * Возвращает текущего контрагента (по аптеке)
	 * @param $data
	 * @return array|bool
	 */
	function getCurrentOrgFarmacyContragent($data)
	{
		return User_model_get::getCurrentOrgFarmacyContragent($this, $data);
	}

	/**
	 * Возвращает текущего контрагента (по организации)
	 * @param $data
	 * @return array|bool
	 */
	function getCurrentOrgContragent($data)
	{
		return User_model_get::getCurrentOrgContragent($this, $data);
	}

	/**
	 * Возвращает список аптек сетевого админа
	 * @param $data
	 * @return array|bool
	 */
	function getNetAdminFarmacies($data)
	{
		return User_model_get::getNetAdminFarmacies($this, $data);
	}

	/**
	 * Возвращает список наименование текущего ЛПУ
	 * @param $data
	 * @return array|bool
	 */
	function getCurrentLpuName($data)
	{
		return User_model_get::getCurrentLpuName($this, $data);
	}

	/**
	 * Получение списка пользователей в организации
	 * @param $org
	 * @return array|bool
	 */
	function getUsersInOrg($org)
	{
		return User_model_get::getUsersInOrg($this, $org);
	}

	/**
	 * Получение данных для дерева фильтрации в форме просмотра групп
	 * @param $data
	 * @return array|bool
	 */
	function getGroupTree($data)
	{
		return User_model_get::getGroupTree($this, $data);
	}

	/**
	 * Получение списка объектов и ролей для определенного типа объекта
	 * @param $data
	 * @return array|bool
	 */
	function getObjectRoleList($data)
	{
		return User_model_get::getObjectRoleList($this, $data);
	}

	/**
	 * Возвращает простой массив разрешенных акшенов по общему массиву всех групп из LDAP (пример формата возвращаемого файла: array('swAboutAction', 'swExitAction'))
	 * @param $roles
	 * @return array|bool
	 */
	function getSimpleMenuActions($roles)
	{
		return User_model_get::getSimpleMenuActions($roles);
	}

	/**
	 * Возвращает список всех акшенов меню для формы просмотра и редактирования роли
	 * @return array|string
	 */
	function getMenusList()
	{
		return User_model_get::getMenusList($this);
	}

	/**
	 * Получение списка объектов для определенного типа объекта
	 * @param $data
	 * @param bool $list
	 * @return array|bool
	 */
	function getObjectList($data, $list = true)
	{
		return User_model_get::getObjectList($this, $data, $list);
	}

	/**
	 * Получение данных для дерева выбора типа объекта для фильтрации
	 * @param $data
	 * @return array|bool
	 */
	function getObjectTree($data)
	{
		return User_model_get::getObjectTree($this, $data);
	}

	/**
	 * Получение списка всех доступных типов объектов
	 * @param $data
	 * @return array
	 */
	function getObjectType($data)
	{
		return User_model_get::getObjectType($data);
	}

	/**
	 * Получение списка всех доступных типов акшенов
	 * @return array
	 */
	function getActionType()
	{
		return User_model_get::getActionType();
	}

	/**
	 * Возвращает список разрешенных типов акшенов для определенного типа оъекта
	 * @param $objecttype
	 * @return array|bool
	 */
	function getObjectActionType($objecttype)
	{
		return User_model_get::getObjectActionType($objecttype);
	}

	/**
	 * Возвращает список разрешенных типов акшенов для определенного оъекта
	 * @param $data
	 * @return array
	 */
	function getObjectActionsList($data)
	{
		return User_model_get::getObjectActionsList($this, $data);
	}

	/**
	 * Формирует заголовок для грида для определенного типа оъекта
	 * @param $data
	 * @return array
	 */
	function getObjectHeaderList($data)
	{
		return User_model_get::getObjectHeaderList($this, $data);
	}

	/**
	 * выводит список организаций пользователя через запятую..
	 * @param $pmUser_id
	 * @return string
	 */
	function getOrgsByUser($pmUser_id)
	{
		return User_model_get::getOrgsByUser($this, $pmUser_id);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getUsersList($data)
	{
		return User_model_get::getUsersList($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getUsersListOfCache($data)
	{
		return User_model_get::getUsersListOfCache($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetARMSOnReport($data)
	{
		return User_model_get::GetARMSOnReport($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getARMsAccessOnReport($data)
	{
		return User_model_get::getARMsAccessOnReport($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getARMinDB($data)
	{
		return User_model_get::getARMinDB($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getUsersWithInvalidMedPersonalId($data)
	{
		return User_model_get::getUsersWithInvalidMedPersonalId($this, $data);
	}

	/**
	 * @param null $Org_id
	 * @return array|bool
	 */
	function getCurrentOrgUsersList($Org_id = null)
	{
		return User_model_get::getCurrentOrgUsersList($this, $Org_id);
	}

    /**
     * @param $login
     * @return array
     */
	function getUserSessionsByLogin($login){
		$CI =& get_instance();
		$DB1 = $CI->load->database('phplog', TRUE);
		if($DB1){
			$query = "
				select Session_id as \"Session_id\"
				from UserSessions us
				where Login = :login and LogoutTime is null
				order by us.LoginTime desc
			";

			$sessions = $DB1->query($query, array('login' => $login));
			return $sessions->result('array');
		}

		return array();
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getUserSessions($data)
	{
		return User_model_get::getUserSessions($this, $data);
	}

	/**
	 * Блок по pmUserCache
	 * @param $login
	 * @return int
	 */
	function getBlockedFromUserCache($login)
	{
		return User_model_get::getBlockedFromUserCache($this, $login);
	}

	/**
	 * Получение списка групп пользователя
	 * @param $data
	 * @return array|bool
	 */
	function getUserGroups($data)
	{
		return User_model_get::getUserGroups($this, $data);
	}

	/**
	 * Получение списка групп
	 * @return array|bool
	 */
	function getGroupsDB()
	{
		return User_model_get::getGroupsDB($this);
	}

	/**
	 * Список МО
	 * @param $data
	 * @return array|false
	 */
	function getLpuList($data)
	{
		return User_model_get::getLpuList($this, $data);
	}

	/**
	 * Требуется ли проверка количества неудачных попыток входа текущего пользователя
	 * @return string|null|false
	 */
	function getCheckFailLoginCounter() {
		return $this->getFirstResultFromQuery("
			select DS.DataStorage_Value as \"DataStorage_Value\" 
			from v_DataStorage DS
			where DS.DataStorage_Name = 'check_fail_login' 
			and DS.DataStorage_Value = '1'
			limit 1
		", [], true);
	}

	/**
	 * Длительность блокировки учетной записи после неудачных попыток входа текущего пользователя
	 * @return string|null|false
	 */
	function getBlockTimeFailLogin() {
		return $this->getFirstResultFromQuery("
			select DS.DataStorage_Value as \"DataStorage_Value\"
			from v_DataStorage DS
			where DS.DataStorage_Name = 'block_time_fail_login'
			limit 1
		", [], true);
	}

	/**
	 * Количество попыток ввода данных учетной записи до блокировки
	 * @return string|null|false
	 */
	function getCountBadFailLogin() {
		return $this->getFirstResultFromQuery("
			select DS.DataStorage_Value as \"DataStorage_Value\"
			from v_DataStorage DS
			where DS.DataStorage_Name = 'count_bad_fail_login'
			limit 1
		", [], true);
	}
	#endregion get
	#region common
	/**
	 * Добавление записи о группе пользователя
	 * @param $data
	 * @return array|bool
	 */
	function addGroupLink($data)
	{
		return User_model_common::addGroupLink($this, $data);
	}

	/**
	 * Удаление всех записей о группах пользователя
	 * @param $data
	 * @return array
	 */
	function removeGroupLink($data)
	{
		return User_model_common::removeGroupLink($this, $data);
	}

	/**
	 * Блокирование пользователей
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function blockUsers($data)
	{
		return User_model_common::blockUsers($this, $data);
	}

	/**
	 * Смена пароля
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function changePassword($data)
	{
		return User_model_common::changePassword($this, $data);
	}

	/**
	 * Проверяет есть ли пользователь в организации
	 * @param $data
	 * @return bool
	 */
	function checkExistUserInOrg($data)
	{
		return User_model_common::checkExistUserInOrg($this, $data);
	}

	/**
	 * Проверка
	 * @param $data
	 * @param $pmuser_id
	 * @return array
	 * @throws Exception
	 */
	function checkLoginDuplicate($data, $pmuser_id)
	{
		return User_model_common::checkLoginDuplicate($this, $data, $pmuser_id);
	}

	/**
	 * Сохранение записи о показе сообщения пользователю
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkShownMsgArms($data)
	{
		return User_model_common::checkShownMsgArms($this, $data);
	}

	/**
	 * Проверка на существование связи АРМа и отчета (право доступа)
	 * @param $data
	 * @return bool
	 */
	function checkOnIssetReportARM(&$data)
	{
		return User_model_common::checkOnIssetReportARM($this, $data);
	}

	/**
	 * Проверка пароля
	 * @param $new_password
	 * @param $old_password
	 * @param $user
	 * @return array
	 * @throws Exception
	 */
	function checkPassword($new_password, $old_password, &$user)
	{
		return User_model_common::checkPassword($this, $new_password, $old_password, $user);
	}

	/**
	 * Проверка пароля
	 * @param $time
	 * @param int $temp
	 * @return array
	 * @throws Exception
	 */
	function checkPasswordDate($time, $temp = 0)
	{
		return User_model_common::checkPasswordDate($this, $time, $temp);
	}

	/**
	 * Проверка на уникальность группы
	 * @param $data
	 * @return array|bool
	 */
	function checkSaveGroupDB($data)
	{
		return User_model_common::checkSaveGroupDB($this, $data);
	}

	/**
	 * По признаку из LDAP создаем группу
	 * @param $data
	 */
	function createGroupFromFlag($data)
	{
		User_model_common::createGroupFromFlag($this, $data);
	}

	/**
	 * Определение типа места работы врача
	 * @param $res
	 * @param null $groups
	 * @return array|mixed|string
	 */
	function defineARMType($res, $groups = null)
	{
		return User_model_common::defineARMType($this, $res, $groups);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deleteARMinDB($data)
	{
		return User_model_common::deleteARMinDB($this, $data);
	}

	/**
	 * Удаление группы
	 * @param $data
	 * @return array|bool
	 */
	function deleteGroupDB($data)
	{
		return User_model_common::deleteGroupDB($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deleteReportARM($data)
	{
		return User_model_common::deleteReportARM($this, $data);
	}

	/**
	 * Удаление пользователя из кэша (отметка удаленным)
	 * @param $user
	 * @param bool $delete
	 * @return bool
	 */
	function deleteUserOfCache($user, $delete = false)
	{
		return User_model_common::deleteUserOfCache($this, $user, $delete);
	}

	/**
	 * Признак того, что врач пользователя включен в регистр главных специалистов
	 * @return bool
	 */
	function isHeadMedSpecMedPersonal()
	{
		return User_model_common::isHeadMedSpecMedPersonal($this);
	}

	/**
	 * Признак того, что пользователь является Заведующим и существует действующаю служба с типом «Производственный отдел»
	 * @return bool|mixed
	 */
	function isHeadWithMedService()
	{
		return User_model_common::isHeadWithMedService($this);
	}

	/**
	 * Признак того, что пользователь является патологоанатомом
	 * @return bool
	 */
	function isPathoMorphoUser()
	{
		$response = false;

		if ( !empty($_SESSION['lpu_id']) && is_numeric($_SESSION['lpu_id']) && !empty($_SESSION['medpersonal_id']) && is_numeric($_SESSION['medpersonal_id']) ) {
			$query = "
				select
					t1.MedPersonal_id as \"MedPersonal_id\"
				from
					v_MedServiceMedPersonal t1 
					inner join v_MedService t2  on t2.MedService_id = t1.MedService_id
					inner join v_MedServiceType t3  on t3.MedServiceType_id = t2.MedServiceType_id
				where
					t1.MedPersonal_id = :MedPersonal_id
					and t2.Lpu_id = :Lpu_id
					and t3.MedServiceType_SysNick = 'patb'
					and t1.MedServiceMedPersonal_begDT <= dbo.tzGetDate()
					and (t1.MedServiceMedPersonal_endDT is null or t1.MedServiceMedPersonal_endDT >= dbo.tzGetDate())
					and t2.MedService_begDT <= dbo.tzGetDate()
					and (t2.MedService_endDT is null or t2.MedService_endDT >= dbo.tzGetDate())
				limit 1
			";

			$queryParams = array(
				 'Lpu_id' => $_SESSION['lpu_id']
				,'MedPersonal_id' => $_SESSION['medpersonal_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['MedPersonal_id']) ) {
					$response = true;
				}
			}
		}

		return $response;
	}

	/**
	 * Признак того, что пользователь является сотрудником службы мед. статистики
	 * @return bool
	 */
	function isMedStatUser()
	{
		$response = false;

		if ( !empty($_SESSION['lpu_id']) && is_numeric($_SESSION['lpu_id']) && !empty($_SESSION['medpersonal_id']) && is_numeric($_SESSION['medpersonal_id']) ) {
			$query = "
				select
					t1.MedPersonal_id as \"MedPersonal_id\"
				from
					v_MedServiceMedPersonal t1
					inner join v_MedService t2 on t2.MedService_id = t1.MedService_id
					inner join v_MedServiceType t3 on t3.MedServiceType_id = t2.MedServiceType_id
				where
					t1.MedPersonal_id = :MedPersonal_id
					and t2.Lpu_id = :Lpu_id
					and t3.MedServiceType_SysNick = 'mstat'
					and t1.MedServiceMedPersonal_begDT <= dbo.tzGetDate()
					and (t1.MedServiceMedPersonal_endDT is null or t1.MedServiceMedPersonal_endDT >= dbo.tzGetDate())
					and t2.MedService_begDT <= dbo.tzGetDate()
					and (t2.MedService_endDT is null or t2.MedService_endDT >= dbo.tzGetDate())
				limit 1
			";

			$queryParams = array(
				 'Lpu_id' => $_SESSION['lpu_id']
				,'MedPersonal_id' => $_SESSION['medpersonal_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['MedPersonal_id']) ) {
					$response = true;
				}
			}
		}

		return $response;
	}

	/**
	 * Получение списка всевозможных АРМов
	 * @return array
	 */
	function loadARMList()
	{
		return User_model_common::loadARMList($this);
	}

	/**
	 * Получение списка групп для Сервис -> Пользователи -> Группы
	 * @return array
	 */
	function loadGroups()
	{
		return User_model_common::loadGroups($this);
	}

	/**
	 * Получение пользователей онлайн
	 * @param $data
	 * @return array
	 */
	function loadOnlineUsersList($data)
	{
		return User_model_common::loadOnlineUsersList($this, $data);
	}

	/**
	 * Получение списка учетных данных пользователей по организации
	 * @param $data
	 * @return array|false
	 */
	function loadPMUserCacheOrgList($data)
	{
		return User_model_common::loadPMUserCacheOrgList($this, $data);
	}

	/**
	 * Возвращаем данные по группам из БД в LDAP
	 * @param $data
	 * @throws Exception
	 */
	function recacheGroupFromDB($data)
	{
		User_model_common::recacheGroupFromDB($this, $data);
	}

	/**
	 * @param $user
	 * @param $orgs
	 */
	function ReCacheUserOrgs($user, $orgs)
	{
		User_model_common::ReCacheUserOrgs($this, $user, $orgs);
	}

	/**
	 * Перекэширование логина и списка организаций пользователя
	 * @param $user
	 * @param $orgs
	 * @return bool
	 */
	function ReCacheOrgUserData($user, $orgs)
	{
		return User_model_common::ReCacheOrgUserData($this, $user, $orgs);
	}

	/**
	 * Перекеширование данных о человеке в базе
	 * @param $user
	 * @param null $data
	 * @return bool
	 */
	function ReCacheUserData($user, $data = null)
	{
		return User_model_common::ReCacheUserData($this, $user, $data);
	}

	/**
	 * Восстановление пользователя в кэше
	 * @param $user
	 * @return bool
	 */
	function restoreUserOfCache($user)
	{
		return User_model_common::restoreUserOfCache($this, $user);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveARMinDB($data)
	{
		return User_model_common::saveARMinDB($this, $data);
	}

	/**
	 * Сохранение группы
	 * @param $data
	 * @return array|bool
	 */
	function saveGroupDB($data)
	{
		return User_model_common::saveGroupDB($this, $data);
	}

	/**
	 * Сохранение роли объекта
	 * @param $data
	 * @param $roles
	 * @return bool
	 */
	function saveObjectRole($data, $roles)
	{
		return User_model_common::saveObjectRole($this, $data, $roles);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveReportARM($data)
	{
		return User_model_common::saveReportARM($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getFirstLpuUnitTypeSysNickByMedStaffFact($data)
	{
		return User_model_common::getFirstLpuUnitTypeSysNickByMedStaffFact($this, $data);
	}

	#endregion common

    /**
     * Возвращает check_count_parallel_sessions
     * @return bool|float|int|string
     */
    function getCheckCountParallelSessions(){
        $query = "
            SELECT DataStorage_Value as \"DataStorage_Value\"
            FROM v_DataStorage DS  
            WHERE DS.DataStorage_Name = 'check_count_parallel_sessions' 
            AND DS.DataStorage_Value = '1' 
            LIMIT 1
        ";
        return $this->getFirstResultFromQuery($query);
    }

    /**
     * Возвращает count_parallel_sessions
     * @return bool|float|int|string
     */
    function getCountParallelSessions(){
        $query = "
            SELECT DataStorage_Value as \"DataStorage_Value\"
            FROM v_DataStorage DS  
            WHERE DS.DataStorage_Name = 'count_parallel_sessions'
            LIMIT 1
        ";
        return $this->getFirstResultFromQuery($query);
    }

}