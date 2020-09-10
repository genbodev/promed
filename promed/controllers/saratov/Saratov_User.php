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
 */
require_once(APPPATH.'controllers/User.php');

class Saratov_User extends User {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Index
	 */
	function Index() {
		return false;
	}

	/**
	 *	Определение типа места работы пользователя
	 */
	function defineARMType($res) {
		// Проверка на АРМы служб
		if ( !empty($res['MedServiceType_SysNick']) && in_array($res['MedServiceType_SysNick'], array('minzdravdlo', 'mekllo', 'touz', 'merch', 'pmllo', 'dpoint', 'leadermo')))  {
			return $res['MedServiceType_SysNick'];
		}
		else {
			return false;
		}
	}
}
