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
 * @author       Bykov Stanislav (savage@swan.perm.ru)
 * @version      22.05.2009
 *
 * @property User_model dbmodel
 */
require_once(APPPATH.'controllers/User.php');

class Kareliya_User extends User {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка групп, недоступных для выбора
	 */
	protected function _getDeniedGroupsList($mode = 'all') {
		if ( $mode == 'lpuadmin' ) {
			return array('SuperAdmin', 'FarmacyAdmin', 'FarmacyUser', 'FarmacyNetAdmin','CallCenterAdmin', 'RosZdrNadzorView', 'OuzChief', 'OuzAdmin', 'OuzUser',
				'OuzSpec', /*'CardEditUser',*/ 'OuzSpecMPC', 'TFOMSUser', 'SMOUser', 'epidem', 'epidem_ufa', 'OKSRegistry', 'DLOAccess', /*'OperPregnRegistry',*/
				'EndoRegistry', 'AdminLLO', 'SuicideRegistry', 'ZagsUser', 'SuperUser', 'SuperPowerUser', '001', 'HIVRegistry', 'OperRegBirth', 'IPRARegistryEdit',
				'MedPersView', 'APIUser', 'AdminOrgReference', 'minzdravdlo', 'EGISSOAdmin', 'RzhdRegistry', 'PM', 'MIACStat', 'MIACMonitoring', 'MIACSysAdmin', 'MIACSuperAdmin', 'MIACAdminFRMR'
			);
		} elseif ( $mode == 'superadmin' ) {
			return array('SuperUser', 'SuperPowerUser', '001');
		}
		else {
			return array('SuperAdmin', 'CardCloseUser', 'CardEditUser','OperatorCallCenter', 'FarmacyAdmin', 'FarmacyUser', 'FarmacyNetAdmin', 'RegAdmin',
				'CallCenterAdmin', 'MPCModer', 'VenerRegistry', 'VznRegistry', 'HepatitisRegistry', 'NarkoRegistry', 'OnkoRegistry', 'Orphan',
				'CrazyRegistry', 'DiabetesRegistry', 'TubRegistry', 'LpuAdmin', 'LpuUser', 'LpuPowerUser', 'RosZdrNadzorView', 'OuzChief', 'OuzAdmin', 'OuzUser',
				'OuzSpec', 'OuzSpecMPC', 'TFOMSUser', 'SMOUser', 'epidem', 'epidem_ufa', 'OKSRegistry', 'DLOAccess',  'OperPregnRegistry', 'EndoRegistry', 'AdminLLO',
				'SuicideRegistry', 'ZagsUser', 'SuperUser', 'SuperPowerUser', '001', 'HIVRegistry', 'OperRegBirth', 'IPRARegistryEdit', 'MedPersView', 'APIUser', 'AdminOrgReference',
				'minzdravdlo', 'EGISSOAdmin', 'EGISSOUser', 'RzhdRegistry', 'PM', 'MIACStat', 'MIACMonitoring', 'MIACSysAdmin', 'MIACSuperAdmin', 'MIACAdminFRMR'
			);
		}
	}

	/**
	 * Получения списка ролей пользователей
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
						'Group_Desc' => $rows->desc
					);
			}
		}
		else {
			if ( $superadmin ) {
				foreach ( $groups as $rows ) {
					if (!in_array($rows->name, $this->_getDeniedGroupsList('superadmin'))) {
						$val[] = array('Group_id' => $rows->id,
							'Group_Name' => $rows->name,
							'Group_Desc' => $rows->desc
						);
					}
				}
			} elseif ($lpuadmin) { // для Админов ЛПУ только определенные группы
				foreach ( $groups as $rows ) {
					if (!in_array($rows->name, $this->_getDeniedGroupsList('lpuadmin'))) {
						$val[] = array('Group_id' => $rows->id,
							'Group_Name' => $rows->name,
							'Group_Desc' => $rows->desc
						);
					}
				}
			} elseif ($orgadmin) { // todo: на всякий случай, нужно дорабатывать этот список
				foreach ( $groups as $rows ) {
					if (!in_array($rows->name, $this->_getDeniedGroupsList())) {
						$val[] = array('Group_id' => $rows->id,
							'Group_Name' => $rows->name,
							'Group_Desc' => $rows->desc
						);
					}
				}
			}
		}
		
		$json = json_encode($val);
		echo $json;
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

			// Убираем ненужные группы пользователей
			foreach ($groups as $key => $group) {
				if ($group['Group_Code'] == 'SuperUser' ||
					$group['Group_Code'] == 'SuperPowerUser' ||
					$group['Group_Code'] == '001')
				{
					//array_splice($groups, $key, 1);
					unset($groups[$key]);
				}
			}
			$arr = array_values($groups);
			echo json_encode($arr);
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
	 * Удаление определенных групп у всех пользователей
	 * (необходимо переделывать, т.к. хранение групп сейчас производится
	 * дополнительно в базе данных
	 */
	/*
	function delUsersFromGroup() {
		set_time_limit(0);
		$groups = new pmAuthGroups();
		$groupsID = array('SuperUser', 'SuperPowerUser', '001', 'HIVRegistry');
		foreach ($groupsID as $groupID) {
			$users = $groups->getGroupUsers($groupID);
			if (!empty($users)) {
				foreach ($users as $usersArray) {
					if (!empty($usersArray)) {
						foreach ($usersArray as $user) {
							$user = explode(',', $user);
							$uid = substr($user[0], 4);
							$userLdap = pmAuthUser::find($uid);
							$userLdap->removeGroup($groupID);

							if ($userLdap->groups) {
								foreach (array_values($groups->groupsById($userLdap->id)) as $group) {
									ldap_removeattr($group->id, array("member" => $userLdap->id));
								}
								foreach (array_values($userLdap->groups) as $group) {
									ldap_insertattr($group->id, array("member" => $userLdap->id));
								}
								echo 'Пользователи удалены из группы '.$groupID.'<br>';
							} else {
								echo 'Ошибка: группы пользователя не обнаружены'.'<br>';
							}
						}
					} else {
						echo 'Пользователей, входящих в группу '.$groupID.' не обнаружено'.'<br>';
					}
				}
			} else {
				echo 'Группа '.$groupID.' не обнаружена'.'<br>';
			}
		}
		echo 'Завершено'.'<br>';
	}
	*/
}