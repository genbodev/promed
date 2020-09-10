<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2012 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		10.02.2012
 */
 
 /**
 * Логирование запросов к PHP в БД
 *
 * @package		Library
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 */
class DBLog {
	
	var $group_name = 'phplog';
	var $db = null;
	private $CI = null;
	/**
	 * Конструктор
	 */
	function __construct() {
		//
	}
	
	/**
	 * Получаем CI
	 */ 
	function getCI() {
		if (!$this->CI) {
			$this->CI = & get_instance();
		}
		return $this->CI;
	}
	
	/**
	 * Отмечаем начало выполнения скрипта
	 */
	function StartRequest() {
		$BM =& load_class('Benchmark');
		$BM->mark('code_start');
	}
	
	/**
	 * Проверят использовать новую систему логгирования или старую
	 * @return bool
	 */
	function isNew() {
		return (in_array(getRegionNick(), ["vologda", 'msk'])) ? true : false;
	}

	/**
	 * В конце выполнения скрипта помещаем данные о его выполнении в базу
	 * @param $params
	 * @return bool
	 */
	function FinishRequest($params) {
		//Если передан параметр отключения записи запросов, то и не записываем
		if ( isset($_REQUEST['NoRecord']) && $_REQUEST['NoRecord'] == 'true' ) {
			return false;
		}
		$BM =& load_class('Benchmark');
		$BM->mark('code_end');

		$c = isset($GLOBALS['swClassName']) ? $GLOBALS['swClassName'] : (isset($_REQUEST['c']) ? $_REQUEST['c'] : NULL);
		$m = isset($GLOBALS['swMethodName']) ? $GLOBALS['swMethodName'] : (isset($_REQUEST['m']) ? $_REQUEST['m'] : NULL);
		$CI = $this->getCI();
		$this->db = $this->initDB();
		
		if ( isset($params) && count($params) > 0) {
			$match = false;
			foreach($params as $rule) {
				$match = !isset($rule['controller']) || (isset($rule['controller']) && preg_match('/'.$rule['controller'].'/', $c));
				$match = $match && (!isset($rule['method']) || (isset($rule['method']) && preg_match('/'.$rule['method'].'/', $m)));
				if ($match) break; // если нашли совпадение с правилом, то прекращаем
			}
		} else {
			$match = true;
		}
		if ( $this->db && isset($c) && $match ) {
			$params = [
				'Controller' => $c,
				'Method' => $m,
				'ET' => (float)str_replace(',', '', $BM->elapsed_time('code_start', 'code_end')),
				'ET_Query' => (isset($CI->db) && isset($CI->db->benchmark)) ? $CI->db->benchmark : NULL,
				'pmUser_insID' => isset($_SESSION['pmuser_id']) ? $_SESSION['pmuser_id'] : NULL,
				'IP' => getClientIP(),
				'Server_IP' => $_SERVER['SERVER_ADDR'],
				'AnswerError' => isset($GLOBALS['swAnswerError']) ? $GLOBALS['swAnswerError'] : null,
				//"ARMType_id" => @$_SESSION["CurARM"]["ARMType_id"]
			];
			if (getRegionNick()!='kz' && isset($_SESSION["CurARM"]["ARMType_id"])) $params['ARMType_id'] = @$_SESSION["CurARM"]["ARMType_id"];
			
			// на казахстане валилось с ошибкой "Недопустимое значение точности", видимо слишком длинная строка в POST формировалась.
			if (defined('USE_UTF') && USE_UTF) {
				$params['QueryString'] = mb_substr($_SERVER['REQUEST_URI'], 0, 255);
				$params['POST'] = mb_substr(http_build_query($_POST), 0, 2048);
			} else {
				$params['QueryString'] = substr($_SERVER['REQUEST_URI'], 0, 255);
				$params['POST'] = http_build_query($_POST);
			}
			
			$this->saveToDB($params);
		}
		return true;
	}
	
	/**
	 * Инициализация ресурса
	 */
	function initDB() {
		return $this->getCI()->load->database($this->group_name, TRUE);
	}
	
	/**
	 * Сохранение записи в БД
	 * @param $params
	 */
	function saveToDB($params)
	{
		$tableName = "PHPLog2";
		if (!empty(@$params["ARMType_id"])) {
			$fields = "Controller, Method, QueryString, ET, ET_Query, PHPLog_insDT, pmUser_insID, IP, Post, Server_IP, AnswerError, ARMType_id";
			$values = ":Controller, :Method, :QueryString, :ET, :ET_Query, getdate(), :pmUser_insID, :IP, :POST, :Server_IP, :AnswerError, :ARMType_id";
		} else {
			$fields = "Controller, Method, QueryString, ET, ET_Query, PHPLog_insDT, pmUser_insID, IP, Post, Server_IP, AnswerError";
			$values = ":Controller, :Method, :QueryString, :ET, :ET_Query, getdate(), :pmUser_insID, :IP, :POST, :Server_IP, :AnswerError";
		}
		$sql = "insert into {$tableName}({$fields}) values ({$values})";
		$this->db->query($sql, $params);
	}
}