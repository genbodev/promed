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
 * @author       Stanislav Bykov (savage@swan.perm.ru)
 * @version      15.12.2014
 * @region       Пермь
 *
 * @property User_model dbmodel
 */
require_once(APPPATH.'controllers/User.php');

class Perm_User extends User {
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
		$result = array();

		if ( $mode == 'lpuadmin' ) {
			$result = array('SuperAdmin', 'CardCloseUser',/*'CardEditUser',*/ 'OperatorCallCenter', 'FarmacyAdmin', 'FarmacyUser', 'FarmacyNetAdmin',
				'CallCenterAdmin', 'RosZdrNadzorView', 'OuzChief', 'OuzAdmin', 'OuzUser', 'OuzSpec', 'OuzSpecMPC', 'TFOMSUser', 'SMOUser', 'epidem', 'epidem_ufa',
				'OKSRegistry', 'DLOAccess', /*'OperPregnRegistry',*/ 'EndoRegistry', 'ZagsUser', 'SuicideRegistry', 'OperRegBirth', 'FasUser', 'IPRARegistryEdit',
				'MedPersView', 'APIUser', 'AdminOrgReference','DispCallNMP', 'DispDirNMP', 'NMPGrandDoc', 'minzdravdlo', 'EGISSOAdmin', 'RzhdRegistry', 'PM', 'MIACStat', 'MIACMonitoring', 'MIACSysAdmin', 'MIACSuperAdmin', 'MIACAdminFRMR'
			);
		}
		else {
			$result = parent::_getDeniedGroupsList($mode);
		}

		$result[] = 'InetModer';
		$result[] = 'OnkoRegistryFullAccess';

		return $result;
	}
}
