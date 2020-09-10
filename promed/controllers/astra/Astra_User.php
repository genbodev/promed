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
 * @version      03.03.2014
 * @region       Астрахань
 *
 * @property User_model dbmodel
 */
require_once(APPPATH.'controllers/User.php');

class Astra_User extends User {
}
