<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * Логирование запросов к PHP в БД
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2012 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		10.02.2012
 *
 * @package		Library
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 */

require_once(APPPATH . "hooks/DBLog.php");
class DBMongoLog extends DBLog {
	private $table = 'sysLog';
	var $db_name = '';
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Проверят использовать новую систему логгирования или старую
	 * @return bool
	 */
	function isNew() {
		return (in_array(getRegionNick(), ["vologda"])) ? true : false;
	}

	/**
	 * Инициализация ресурса
	 */
	function initDB() {
		switch (checkMongoDb()) {
			case 'mongo':
				require_once(APPPATH.'libraries/SwMongodb.php');
				return new swMongodb(array('config_file'=>'mongodblog'));
				break;
			case 'mongodb':
				require_once(APPPATH.'libraries/SwMongodbPHP7.php');
				return new swMongodbPHP7(array('config_file'=>'mongodblog'));
				break;
			default:
				return null;
		}
	}
	/**
	 * Сохранение записи в БД
	 */
	function saveToDB($params) {
		// дополнительно дозапишем в params нужные параметры 
		$params['PMUser_Login'] = isset($_SESSION['login']) ? $_SESSION['login'] : NULL;
		$params['PHPLog_insDT'] = $this->db->date();
		if (getRegionNick() != 'kz' && isset($_SESSION["CurARM"]["ARMType_id"])) $params["ARMType_id"] = @$_SESSION["CurARM"]["ARMType_id"];
		$this->db->insert($this->table, $params);
	}
}