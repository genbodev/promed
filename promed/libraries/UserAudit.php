<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Аудит действий пользователя
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2014 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		30.01.2014
 *
 * @package		Library
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 */
class UserAudit {
	
	/**
	 * Создание записи аудита
	 * @param $login
	 * @param $user_id
	 * @param $session_id
	 * @param $auth_type
	 * @param $status
	 */
	public static function createAuditRow($login, $user_id, $session_id, $auth_type, $status) {
		/**@var CI_DB_driver $DB1 */
		$CI =& get_instance();
		$DB1 = $CI->load->database('phplog', TRUE);
		if ( $DB1 ) {
			$sql = "
				Insert into UserSessions(
					Session_id,
					pmUser_id,
					LoginTime,
					IP,
					AuthType_id,
					Login,
					Status
				)
				Values (
					:Session_id,
					:pmUser_id,
					getdate(),
					:IP,
					:AuthType_id,
					:Login,
					:Status
				)
			";
			$sqlParams = [
				"pmUser_id" => $user_id,
				"Session_id" => $session_id,
				"IP" => getClientIP(),
				"AuthType_id" => $auth_type,
				"Login" => $login,
				"Status" => $status
			];
			$DB1->query($sql, $sqlParams);
		}
	}

	/**
	 * Аудит входа
	 * @param $login
	 * @param $user_id
	 * @param $session_id
	 * @param $auth_type
	 */
	public static function Login($login, $user_id, $session_id, $auth_type) {
		UserAudit::createAuditRow($login, $user_id, $session_id, $auth_type, 1);
	}

	/**
	 * Аудит неудачного входа
	 * @param $login
	 * @param $session_id
	 * @param $auth_type
	 */
	public static function LoginFail($login, $session_id, $auth_type) {
		UserAudit::createAuditRow($login, null, $session_id, $auth_type, 0);
	}

	/**
	 * Аудит попытки входа заблокированного пользователя
	 * @param $login
	 * @param $session_id
	 * @param $auth_type
	 */
	public static function LoginBlock($login, $session_id, $auth_type) {
		UserAudit::createAuditRow($login, null, $session_id, $auth_type, 2);
	}

	/**
	 * Проверяет сколько было ошибочных попыток входа
	 * @param $login
	 * @return int
	 */
	public static function getCountFailLogin($login) {
		$count = 0;
		/**@var CI_DB_driver $DB1 */
		$CI =& get_instance();
		$DB1 = $CI->load->database('phplog', TRUE);
		if (!$DB1) {
			return $count;
		}

		$top = "top 10";
		$limit  = "";
		if (defined('USE_POSTGRESQL') && USE_POSTGRESQL) {
			$top = "";
			$limit  = "limit 10";
		}
		$query = "
			select {$top}
				Status as \"Status\"
			from UserSessions
			where Login = :Login
			order by LoginTime desc
			{$limit}
		";
		/**@var CI_DB_result $result */
		$result = $DB1->query($query, ["Login" => $login]);
		if (!is_object($result)) {
			return $count;
		}
		$result = $result->result_array();
		if (@$result[0]["Status"] == 2) {
			//Пользователь заблокирован
			$count = -1;
		} else {
			if(@$result[0]["Status"] == 0) {
				$count++;
			}
			if(@$result[1]["Status"] == 0) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Дата последнего входа в систему пользователя
	 * @param $data
	 * @return array
	 */
	public static function lastAuth($data) {
		/**@var CI_DB_driver $DB1 */
		$CI =& get_instance();
		$DB1 = $CI->load->database('phplog', TRUE);
		if (!$DB1) {
			return [];
		}
		$filter = (isset($data["type"]) && $data["type"] == "LogoutTime") ? "MAX(LogoutTime) as \"LogoutTime\"" : "MAX(LoginTime) as \"LoginTime\"";
		$query = "
			SELECT
				Login as \"Login\",
				{$filter}
			FROM UserSessions 
			WHERE pmUser_id in ({$data["pmUsers_id"]})
			GROUP BY Login
		";
		/**@var CI_DB_result $result */
		$result = $DB1->query($query);
		if(!is_object($result)) {
			return [];
		}
		return $result->result_array();
	}

	/**
	 * Аудит выхода
	 */
	public static function Logout($session_id) {
		// Метод может запускаться до запуска CodeIgniter, поэтому нельзя на него полагаться, придется работать с БД вручную
		
		if ( getenv('USER_CAN_CHANGE_REGION') ) {
			// смотрим регион в сессии
			$startsession = (!isset($_SESSION)) ? true : false;
			if ($startsession) {
				session_start();
			}
			if (!empty($_SESSION['REGION_ENV'])) {
				$region_name = $_SESSION['REGION_ENV'];
			}
			if (!empty($_COOKIE['DBTYPE_ENV'])) {
				$dbtype = $_COOKIE['DBTYPE_ENV'];
			} else if (!empty($_SESSION['DBTYPE_ENV'])) {
				$dbtype = $_SESSION['DBTYPE_ENV'];
			}
			// закрываем если была закрыта.
			if ($startsession) {
				session_write_close();
			}
		}
		
		
		if ( !isset($region_name) && getenv('REGION') ) {
			$region_name = getenv('REGION');
		}
		if ( !isset($dbtype) && getenv('DBTYPE') ) {
			$dbtype = getenv('DBTYPE');
		}

		$configPath = 'config/';
		if (isset($dbtype) && $dbtype == 'pgsql') {
			$configPath .= '_pgsql/';
		}

		$file = (isset($region_name) && $region_name != "undefined") ? "config/{$region_name}/database.php" : "config/database.php";
		require(APPPATH . $file);

		if (!(isset($db["phplog"]) && isset($db["phplog"]["hostname"]) && isset($db["phplog"]["database"]))) {
			die("Не заданы настройки БД для работы с аудитом пользователей");
		}

        if ($db['phplog']['dbdriver'] == 'postgre'){
	        $post = (!empty($db['phplog']['port']))?$db['phplog']['port']:"5432";
            $conn_string = "host={$db['phplog']['hostname']} port={$post} dbname={$db['phplog']['database']} user={$db['phplog']['username']} password={$db['phplog']['password']}";
            $pg_conn = pg_connect($conn_string);
            if (!$pg_conn) {
                die("Ошибка работы БД аудита пользователей");
            }
            $sql = "
			    update UserSessions u set LogoutTime = getdate() where Session_id = '{$session_id}' and LogoutTime is null
		    ";
            $result = pg_query($pg_conn, $sql);

        } else {

            $conn = sqlsrv_connect(
                $db["phplog"]["hostname"],
                [
                    "UID" => $db["phplog"]["username"],
                    "PWD" => $db["phplog"]["password"],
                    "Database" => $db["phplog"]["database"],
                    "ConnectionPooling" => 1,
                    "LoginTimeout" => 30
                ]
            );
            if (!$conn) {
                die("Ошибка работы БД аудита пользователей");
            }
            $sql = "
			update UserSessions
			set LogoutTime = getdate()
			where Session_id = ? and LogoutTime is null
			";
			$options = ["QueryTimeout" => 30];
			sqlsrv_query($conn, $sql, [$session_id], $options);

        }

	}
}