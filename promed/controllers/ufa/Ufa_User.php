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

class Ufa_User extends User {
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
		$result = parent::_getDeniedGroupsList($mode);

		// @task https://redmine.swan.perm.ru/issues/99640
		if ( ($i = array_search('IPRARegistryEdit', $result)) !== false ) {
			unset($result[$i]);
		}

		return $result;
	}
}
