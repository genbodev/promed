<?php

# MSSQL sessions handler (c) Lich base on Lockable MSSQL sessions handler by Alex/AT

# Хранение сессий PHP в СУБД MSSQL
# Применять для распараллеливания веб-серверов и прочих нужд
# Используется прямая работа с функциями драйвера SQL Server Driver For PHP 

# SCHEMA:

#CREATE TABLE [dbo].[PHPSessions](
#	[id] [varchar](255) NOT NULL,
#	[value] [text] NULL,
#	[updated] [datetime] NULL,
# CONSTRAINT [pk_PHPSessions] PRIMARY KEY CLUSTERED 
#(
#	[id] ASC
#)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
#) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
#CREATE NONCLUSTERED INDEX [idx_PHPSessions_updated] ON [dbo].[PHPSessions] 
#(
#	[updated] ASC
#)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]

/**
 *
 * @property Resource dbid
 * @property Resource conn
 */
class MSSQL_SESSIONS
{
    var $server; # MSSQL server (optionally :port)
    var $user; # MSSQL user
    var $password; # MSSQL password
    var $base; # MSSQL database
    var $table; # MSSQL table to use
	var $login_timeout = 30; # MSSQL login timeout
	var $query_timeout = 100; # MSSQL query timeout
    var $compress = TRUE; # compress database data? DO NOT CHANGE IN REALTIME!
    var $compress_level = 6; # compression level

    var $dbid = NULL; // идентификатор подключения
	
	private static $conn = NULL; // идентификатор подключения
	private static $params = NULL; // параметры подключения
	
	/**
	 * Создаем объект и сразу же его инициализируем или не инициализируем
	 */
    function __construct($params)
    {
		//Если заданы параметры сразу инициализируем объект
		if ( isset($params) ) {
			$this->server = $params['server']; # задаем сервер MySQL
			$this->user = $params['user']; # пользователь для доступа к БД
			$this->password = $params['password']; # пароль для доступа к БД
			$this->base = $params['base']; # имя БД, в которой лежит таблица сессий
			$this->table = $params['table']; # имя нашей таблицы сессий
			$this->init();
		}
    }
	
	/**
	 * Деструктор в данном случае необходим, см. комментарий
	 */
	function __destruct()
	{
		//ВАЖНО!!! См. http://php.net/manual/en/function.session-set-save-handler.php
		session_write_close();
		if ( isset(self::$conn) && is_resource(self::$conn) )
			@sqlsrv_close(self::$conn);
	}

    /**
	 * Sets up and installs MSSQL session handlers
	 */
    function init()
    {
        session_set_save_handler(
            array($this, '_open'),
            array($this, '_close'),
            array($this, '_read'),
            array($this, '_write'),
            array($this, '_destroy'),
            array($this, '_gc')
        );
    }

	/**
	 * Открытие сессии
	 */
    function _open()
    {
		$this->connect();
		$this->dbid = MSSQL_SESSIONS::getConn();
		return !empty($this->dbid); // Session callback expects true/false return value
    }

	/**
	 * Подключение к MSSQL
	 */
	function connect()
	{
		if ( isset($this->user) ) {
			self::$conn = sqlsrv_connect(
				$this->server, 
				array(
					"UID" => $this->user, 
					"PWD" => $this->password, 
					"Database" => $this->base,
					"ConnectionPooling" => 1,
					"LoginTimeout" => $this->login_timeout
				) 
			);
			self::$params = array(
				'server' => $this->server,
				'user' => $this->user,
				'password' => $this->password,
				'base' => $this->base
			);
		} else {
			self::$conn = sqlsrv_connect(
				$this->server, 
				array(
					"Database" => $this->base,
					"ConnectionPooling" => 1,
					"LoginTimeout" => $this->login_timeout
				) 
			);
			self::$params = array(
				'server' => $this->server,
				'user' => NULL,
				'password' => NULL,
				'base' => $this->base
			);
		}
        if (!self::$conn) {
			echo "Ошибка: Проблема соединения с БД. Более подробная информация об ошибке может содержаться в логах.";
            $error_stack = sqlsrv_errors();
            $pwd = 'using password: ';
            if (isset($this->user)) {
                $pwd = $pwd.'yes (***)';
                $uid = $this->user;
            } else {
                $pwd = $pwd.'no';
                $uid = '';
            }
            $error_text[] = 'error connection to database '.$this->base.'@'.$this->server.' using login \''.$uid.'\' '.$pwd;
            foreach ($error_stack as $e) {
				$error_text[] = implode(', ', $e);
            }
            log_message('error', implode($error_text,"\r\n"));
			return(FALSE);
		} else {
			return self::$conn;
		}
    }
	
	/**
	 * Геттер подключения
	 */
	static function getConn() {
		if ( isset(self::$conn) ) {
			return self::$conn;
		} else {
			return false;
		}
	}
	
	/**
	 * Геттер подключения
	 */
	static function getConnParams() {
		if ( isset(self::$conn) ) {
			return self::$params;
		} else {
			return false;
		}
	}
	
	/**
	 * Закрытие сессии
	 */
    function _close()
    {
        if (!$this->dbid) return(FALSE);
        //sqlsrv_close($this->dbid);
        return(TRUE);
    }

	/**
	 * Чтение сессии
	 */
    function _read($id)
    {
		$value = $this->getvalue($id);
        return ($value);
    }
	/**
	 * Чтение содержания сессии из таблицы сессии
	 */
	function getvalue($id) {
		if (!$this->dbid) die('Session failure: READ_NO_CONNECT');
		$options = array('QueryTimeout' => $this->query_timeout); 
		$sql = "
			SELECT
				value
			FROM {$this->table} (nolock)
			WHERE 
				id = ?";
		# perform the read
		$res = sqlsrv_query(
			$this->dbid,
			$sql,
			array(
				$id
			),
			$options 
		);
		if (!$res) die('Session failure: READ_NO_RESULT');
		
		$row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
		if (!is_array($row)) {
			$value = null;
		} else {
			if (isset($row['value'])) {
				$value = $this->compress ? gzuncompress($row['value']) : $row['value'];
			} else {
				$value = null;
			}
		}
		return $value;
	}
	
	/**
	 * Запись сессии
	 */
    function _write($id, $data)
    {
		if (!$this->dbid) die('Session failure: WRITE_NO_CONNECT');

		# write session
		$options = array('QueryTimeout' => $this->query_timeout); 
		$value = $this->compress ? gzcompress($data, $this->compress_level) : $data;
		
		$value_old = $this->getvalue($id);
		
		//Если записи вообще нет, добавляем ее
		if ( !isset($value_old) ) {
			$sql = "
				INSERT INTO {$this->table} with (rowlock) (id, value, updated) VALUES(?, ?, ?)";
			$res = sqlsrv_query(
				$this->dbid,
				$sql,
				array(
					$id,
					$value,
					date('Y-m-d H:i:s')
				),
				$options
			);
		} else { //запись есть
			if ( $value_old != $value ) { //данные в сесии изменились, обновляем их
				$sql = "
					UPDATE {$this->table} with (rowlock)
					SET
						value = ?,
						updated = ?
					WHERE id = ?";
				$res = sqlsrv_query(
					$this->dbid,
					$sql,
					array(
						$value,
						date('Y-m-d H:i:s'),
						$id
					),
					$options
				);
			} else { // данные в сессии не изменились, просто изменяем дату обращения
				$sql = "
					UPDATE {$this->table} with (rowlock)
					SET
						updated = ?
					WHERE id = ?";
				$res = sqlsrv_query(
					$this->dbid,
					$sql,
					array(
						date('Y-m-d H:i:s'),
						$id
					),
					$options
				);
			}
		}
        if (!$res) die('Session failure: WRITE_NO_RESULT');
        # done
        return(TRUE);
	}

	/**
	 * Удаление сессии
	 */
    function _destroy($id)
    {
		if (!$this->dbid) die('Session failure: DESTROY_NO_CONNECT');
		
        # perform the cleanup
		$options = array('QueryTimeout' => $this->query_timeout); 
		$sql = "
		DELETE FROM {$this->table} with (rowlock)
		WHERE id = ?";
		
		$res = sqlsrv_query(
			$this->dbid,
			$sql,
			array(
				$id
			),
			$options
		);

        if (!$res) die('Session failure: DESTROY_NO_RESULT');

        # done retrieving session data
        return(TRUE);
    }

	/**
	 * Очистка устаревших сессий
	 */
    function _gc($lifetime)
    {
		if (!$this->dbid) die('Session failure: GC_NO_CONNECT');

        # perform the read
		$options = array('QueryTimeout' => $this->query_timeout); 
		$sql = "
		DELETE FROM {$this->table} with (rowlock)
		WHERE updated < ?";
		$res = sqlsrv_query(
			$this->dbid,
			$sql,
			array(
				date('Y-m-d H:i:s', time() - $lifetime).".000"
			),
			$options
		);
		//log_message('error', getDebugSql($sql, array(date('Y-m-d H:i:s', time() - $lifetime).".000")));
		
        if (!$res) die('Session failure: GC_NO_RESULT');
        # done retrieving session data
        return(TRUE);
    }
}

?>
