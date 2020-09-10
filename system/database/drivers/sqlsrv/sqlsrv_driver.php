<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 2.0.3
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SQLSRV Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the query builder
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/database/
 */
class CI_DB_sqlsrv_driver extends CI_DB {

	var $log_queries = FALSE; // логировать все запросы
	var $log_query_time_limit = 10; // ограничение по времени, больше которого должен выполняться запрос, чтобы его залогировали. В секундах
	var $add_comment = false; // добавлять комментарии к каждому запросу какой пользователь, с какого IP адреса и когда его запустил

	/**
	 * Database driver
	 *
	 * @var	string
	 */
	public $dbdriver = 'sqlsrv';

	/**
	 * Scrollable flag
	 *
	 * Determines what cursor type to use when executing queries.
	 *
	 * FALSE or SQLSRV_CURSOR_FORWARD would increase performance,
	 * but would disable num_rows() (and possibly insert_id())
	 *
	 * @var	mixed
	 */
	public $scrollable = FALSE;

	/**
	 * Устанавливать контекст или нет refs #162139
	 * @var bool|string
	 */
	public $context_info = FALSE;

	// --------------------------------------------------------------------

	/**
	 * ORDER BY random keyword
	 *
	 * @var	array
	 */
	protected $_random_keyword = array('NEWID()', 'RAND(%d)');

	/**
	 * Quoted identifier flag
	 *
	 * Whether to use SQL-92 standard quoted identifier
	 * (double quotes) or brackets for identifier escaping.
	 *
	 * @var	bool
	 */
	protected $_quoted_identifier = TRUE;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param	array	$params
	 * @return	void
	 */
	public function __construct($params)
	{
		parent::__construct($params);

		// This is only supported as of SQLSRV 3.0
		if ($this->scrollable === NULL)
		{
			$this->scrollable = defined('SQLSRV_CURSOR_CLIENT_BUFFERED')
				? SQLSRV_CURSOR_CLIENT_BUFFERED
				: FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Database connection
	 *
	 * @param	bool	$pooling
	 * @return	resource
	 */
	public function db_connect($pooling = FALSE)
	{
		$charset = in_array(strtolower($this->char_set), array('utf-8', 'utf8'), TRUE)
			? 'UTF-8' : SQLSRV_ENC_CHAR;

		//Если сессия хранится в базе и существует подключение к базе для сессии
		if ( class_exists('MSSQL_SESSIONS') && MSSQL_SESSIONS::getConn() ) {
			$ConnParams = MSSQL_SESSIONS::getConnParams();
			// И если его параметры совпадают с нужным нам соединением
			if (
				$ConnParams['server'] == $this->hostname &&
				($ConnParams['user'] == $this->username || empty($this->username) ) &&
				($ConnParams['password'] == $this->password || empty($this->password) ) &&
				$ConnParams['base'] == $this->database
			) {
				return MSSQL_SESSIONS::getConn(); // то не создаем новое, а просто возвращаем уже готовое соединение
			}
		}

		$connection = array(
			'LoginTimeout'	=> empty($this->login_timeout) ? '' : $this->login_timeout,
			'UID'			=> empty($this->username) ? '' : $this->username,
			'PWD'			=> empty($this->password) ? '' : $this->password,
			'Database'		=> $this->database,
			'ConnectionPooling'	=> ($pooling === TRUE) ? 1 : 0,
			'CharacterSet'		=> $charset,
			'Encrypt'		=> ($this->encrypt === TRUE) ? 1 : 0,
			'ReturnDatesAsStrings'	=> 0
		);

		// If the username and password are both empty, assume this is a
		// 'Windows Authentication Mode' connection.
		if (empty($connection['UID']) && empty($connection['PWD']))
		{
			unset($connection['UID'], $connection['PWD']);
		}

		if (empty($connection['LoginTimeout']))
		{
			unset($connection['LoginTimeout']);
		}

		if (FALSE !== ($this->conn_id = sqlsrv_connect($this->hostname, $connection)))
		{
			// Determine how identifiers are escaped
			$query = $this->query('SELECT CASE WHEN (@@OPTIONS | 256) = @@OPTIONS THEN 1 ELSE 0 END AS qi');
			$query = $query->row_array();
			$this->_quoted_identifier = empty($query) ? FALSE : (bool) $query['qi'];
			$this->_escape_char = ($this->_quoted_identifier) ? '"' : array('[', ']');
		}

		return $this->conn_id;
	}

	// --------------------------------------------------------------------

	/**
	 * Select the database
	 *
	 * @param	string	$database
	 * @return	bool
	 */
	public function db_select($database = '')
	{
		if ($database === '')
		{
			$database = $this->database;
		}

		if ($this->_execute('USE '.$this->escape_identifiers($database)))
		{
			$this->database = $database;
			$this->data_cache = array();
			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Execute the query
	 *
	 * @param	string	$sql	an SQL query
	 * @return	resource
	 */
	protected function _execute($sql, $params)
	{
		if ( !$this->conn_id ) {
			if ($this->throw_exception) {
				throw new Exception('db_connection_not_exists');
			}

			if ($this->db_debug)
			{
				$this->display_error('db_connection_not_exists');
			}
			return false;
		}

		$options = array();

		if (isset($this->query_timeout)) {
			$options['QueryTimeout'] = $this->query_timeout;
		}

		if ($this->scrollable !== FALSE AND !$this->is_write_type($sql)) {
			$options['Scrollable'] = $this->scrollable;
		}

		// todo это можно будет выпилить, когда sqlsrv будет корректно понимать большие интовые параметры
		if (!empty($params) && is_array($params)) {
			foreach ($params as $key => $value) {
				if (is_int($value) && $value > 2147483647) {
					$params[$key] = strval($value);
				}
			}
		}

		if ($this->context_info) {
			// пишем в контекст текущий контроллер, метод и пользователя
			$c = isset($GLOBALS['swClassName']) ? $GLOBALS['swClassName'] : (isset($_REQUEST['c']) ? $_REQUEST['c'] : "");
			$m = isset($GLOBALS['swMethodName']) ? $GLOBALS['swMethodName'] : (isset($_REQUEST['m']) ? $_REQUEST['m'] : "");
			$context = (isset($_SESSION['login']) ? $_SESSION['login'] : "?") . ", " . $c . ", " . $m;
			$packedAr = unpack('H*', $context);
			if (!empty($packedAr[1])) {
				$packed = substr($packedAr[1], 0, 256);
				sqlsrv_query($this->conn_id, "
					SET CONTEXT_INFO 0x{$packed}
				");
			}
		}

		return sqlsrv_query($this->conn_id, $sql, $params, $options);
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return	bool
	 */
	protected function _trans_begin()
	{
		return sqlsrv_begin_transaction($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return	bool
	 */
	protected function _trans_commit()
	{
		return sqlsrv_commit($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return	bool
	 */
	protected function _trans_rollback()
	{
		return sqlsrv_rollback($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @return	int
	 */
	public function affected_rows()
	{
		return sqlsrv_rows_affected($this->result_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * Returns the last id created in the Identity column.
	 *
	 * @return	string
	 */
	public function insert_id()
	{
		return $this->query('SELECT SCOPE_IDENTITY() AS insert_id')->row()->insert_id;
	}

	// --------------------------------------------------------------------

	/**
	 * Database version number
	 *
	 * @return	string
	 */
	public function version()
	{
		if (isset($this->data_cache['version']))
		{
			return $this->data_cache['version'];
		}

		if ( ! $this->conn_id OR ($info = sqlsrv_server_info($this->conn_id)) === FALSE)
		{
			return FALSE;
		}

		return $this->data_cache['version'] = $info['SQLServerVersion'];
	}

	// --------------------------------------------------------------------

	/**
	 * List table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param	bool
	 * @return	string	$prefix_limit
	 */
	protected function _list_tables($prefix_limit = FALSE)
	{
		$sql = 'SELECT '.$this->escape_identifiers('name')
			.' FROM '.$this->escape_identifiers('sysobjects')
			.' WHERE '.$this->escape_identifiers('type')." = 'U'";

		if ($prefix_limit === TRUE && $this->dbprefix !== '')
		{
			$sql .= ' AND '.$this->escape_identifiers('name')." LIKE '".$this->escape_like_str($this->dbprefix)."%' "
				.sprintf($this->_escape_like_str, $this->_escape_like_chr);
		}

		return $sql.' ORDER BY '.$this->escape_identifiers('name');
	}

	// --------------------------------------------------------------------

	/**
	 * List column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @param	string	$table
	 * @return	string
	 */
	protected function _list_columns($table = '')
	{
		return 'SELECT COLUMN_NAME
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = '.$this->escape(strtoupper($table));
	}

	// --------------------------------------------------------------------

	/**
	 * Returns an object with field data
	 *
	 * @param	string	$table
	 * @return	array
	 */
	public function field_data($table)
	{
		$sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = '.$this->escape(strtoupper($table));

		if (($query = $this->query($sql)) === FALSE)
		{
			return FALSE;
		}
		$query = $query->result_object();

		$retval = array();
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retval[$i]			= new stdClass();
			$retval[$i]->name		= $query[$i]->COLUMN_NAME;
			$retval[$i]->type		= $query[$i]->DATA_TYPE;
			$retval[$i]->max_length		= ($query[$i]->CHARACTER_MAXIMUM_LENGTH > 0) ? $query[$i]->CHARACTER_MAXIMUM_LENGTH : $query[$i]->NUMERIC_PRECISION;
			$retval[$i]->default		= $query[$i]->COLUMN_DEFAULT;
		}

		return $retval;
	}

	// --------------------------------------------------------------------

	/**
	 * Error
	 *
	 * Returns an array containing code and message of the last
	 * database error that has occured.
	 *
	 * @return	array
	 */
	public function error()
	{
		$error = array('code' => '00000', 'message' => '');
		$sqlsrv_errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);

		if ( ! is_array($sqlsrv_errors))
		{
			return $error;
		}

		$sqlsrv_error = array_shift($sqlsrv_errors);
		if (isset($sqlsrv_error['SQLSTATE']))
		{
			$error['code'] = isset($sqlsrv_error['code']) ? $sqlsrv_error['SQLSTATE'].'/'.$sqlsrv_error['code'] : $sqlsrv_error['SQLSTATE'];
		}
		elseif (isset($sqlsrv_error['code']))
		{
			$error['code'] = $sqlsrv_error['code'];
		}

		if (isset($sqlsrv_error['message']))
		{
			$error['message'] = $sqlsrv_error['message'];
		}

		return $error;
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param	string	$table
	 * @param	array	$values
	 * @return	string
	 */
	protected function _update($table, $values)
	{
		$this->qb_limit = FALSE;
		$this->qb_orderby = array();
		return parent::_update($table, $values);
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 *
	 * If the database does not support the TRUNCATE statement,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param	string	$table
	 * @return	string
	 */
	protected function _truncate($table)
	{
		return 'TRUNCATE TABLE '.$table;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param	string	$table
	 * @return	string
	 */
	protected function _delete($table)
	{
		if ($this->qb_limit)
		{
			return 'WITH ci_delete AS (SELECT TOP '.$this->qb_limit.' * FROM '.$table.$this->_compile_wh('qb_where').') DELETE FROM ci_delete';
		}

		return parent::_delete($table);
	}

	// --------------------------------------------------------------------

	/**
	 * LIMIT
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @param	string	$sql	SQL Query
	 * @return	string
	 */
	protected function _limit($sql)
	{
		// As of SQL Server 2012 (11.0.*) OFFSET is supported
		if (version_compare($this->version(), '11', '>='))
		{
			// SQL Server OFFSET-FETCH can be used only with the ORDER BY clause
			empty($this->qb_orderby) && $sql .= ' ORDER BY 1';

			return $sql.' OFFSET '.(int) $this->qb_offset.' ROWS FETCH NEXT '.$this->qb_limit.' ROWS ONLY';
		}

		$limit = $this->qb_offset + $this->qb_limit;

		// An ORDER BY clause is required for ROW_NUMBER() to work
		if ($this->qb_offset && ! empty($this->qb_orderby))
		{
			$orderby = $this->_compile_order_by();

			// We have to strip the ORDER BY clause
			$sql = trim(substr($sql, 0, strrpos($sql, $orderby)));

			// Get the fields to select from our subquery, so that we can avoid CI_rownum appearing in the actual results
			if (count($this->qb_select) === 0)
			{
				$select = '*'; // Inevitable
			}
			else
			{
				// Use only field names and their aliases, everything else is out of our scope.
				$select = array();
				$field_regexp = ($this->_quoted_identifier)
					? '("[^\"]+")' : '(\[[^\]]+\])';
				for ($i = 0, $c = count($this->qb_select); $i < $c; $i++)
				{
					$select[] = preg_match('/(?:\s|\.)'.$field_regexp.'$/i', $this->qb_select[$i], $m)
						? $m[1] : $this->qb_select[$i];
				}
				$select = implode(', ', $select);
			}

			return 'SELECT '.$select." FROM (\n\n"
				.preg_replace('/^(SELECT( DISTINCT)?)/i', '\\1 ROW_NUMBER() OVER('.trim($orderby).') AS '.$this->escape_identifiers('CI_rownum').', ', $sql)
				."\n\n) ".$this->escape_identifiers('CI_subquery')
				."\nWHERE ".$this->escape_identifiers('CI_rownum').' BETWEEN '.($this->qb_offset + 1).' AND '.$limit;
		}

		return preg_replace('/(^\SELECT (DISTINCT)?)/i','\\1 TOP '.$limit.' ', $sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data.
	 *
	 * @param	string	$table	Table name
	 * @param	array	$keys	INSERT keys
	 * @param	array	$values	INSERT values
	 * @return	string|bool
	 */
	protected function _insert_batch($table, $keys, $values)
	{
		// Multiple-value inserts are only supported as of SQL Server 2008
		if (version_compare($this->version(), '10', '>='))
		{
			return parent::_insert_batch($table, $keys, $values);
		}

		return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @return	void
	 */
	function _close()
	{
		//Если соединение было создано для сессий, не закрываем его, закроем позже
		if ( class_exists('MSSQL_SESSIONS') && MSSQL_SESSIONS::getConn() && $conn_id !== MSSQL_SESSIONS::getConn())
			@sqlsrv_close($this->conn_id);
	}

	/**
	 * Execute the query
	 *
	 * Accepts an SQL string as input and returns a result object upon
	 * successful execution of a "read" type query. Returns boolean TRUE
	 * upon successful execution of a "write" type query. Returns boolean
	 * FALSE upon failure, and if the $db_debug variable is set to TRUE
	 * will raise an error.
	 *
	 * @param	string	$sql
	 * @param	array	$binds = FALSE		An array of binding data
	 * @param	bool	$return_object = NULL
	 * @return	mixed
	 */
	public function query($sql, $binds = FALSE, $return_object = NULL)
	{
		if ($sql === '')
		{
			sql_log_message('error', 'Invalid query', $sql);
			return ($this->db_debug) ? $this->display_error('db_invalid_query') : FALSE;
		}
		elseif ( ! is_bool($return_object))
		{
			$return_object = ! $this->is_write_type($sql);
		}

		// Verify table prefix and replace if necessary
		if ($this->dbprefix !== '' && $this->swap_pre !== '' && $this->dbprefix !== $this->swap_pre)
		{
			$sql = preg_replace('/(\W)'.$this->swap_pre.'(\S+?)/', '\\1'.$this->dbprefix.'\\2', $sql);
		}

		if ($binds !== false) {
			//Заменяем именованные параметры, если они есть
			list($sql, $query_params) = $this->ReplaceNamedParams($sql, $binds);
		}
		else {
			$query_params = NULL;
		}

		// Is query caching enabled? If the query is a "read type"
		// we will load the caching class and return the previously
		// cached query if it exists
		if ($this->cache_on === TRUE && $return_object === TRUE && $this->_cache_init())
		{
			$this->load_rdriver();
			if (FALSE !== ($cache = $this->CACHE->read($sql)))
			{
				return $cache;
			}
		}

		// Save the query for debugging
		if ($this->save_queries === TRUE)
		{
			$this->queries[] = $this->compile_binds($sql, $query_params);
		}

		// Start the Query Timer
		$time_start = list($sm, $ss) = explode(' ', microtime());

		// для отладки запросов
		$config =& get_config();
		$sql_debug = (isset($_REQUEST['sql_debug'])) ? $_REQUEST['sql_debug'] : (isset($_GET['sql_debug']) ? $_GET['sql_debug'] : false);
		if ((!empty($config['IS_DEBUG']) || isSuperAdmin() || !empty($_SESSION['IsSWANUser'])) && $sql_debug) {
			switch($sql_debug) {
				case 1:
				case 4:
					echo "<pre>".getDebugSql($sql, $query_params). "</pre>";
					break;
				case 2:
					echo "<pre>".$sql."</pre>";
					echo "<pre>".json_encode($query_params)."</pre>";
					break;
				case 3:
					echo "<pre>".$sql."</pre>";
					echo "<pre>".var_export($query_params, true)."</pre>";
					break;
			}
		}

		//Добавляем комментарии к запросу
		if ( $this->add_comment ) {
			$User = (isset($_SESSION['login']) ? $_SESSION['login'] : "?")." (".( isset($_SESSION['pmuser_id']) ? $_SESSION['pmuser_id'] : "?").")";
			$IP = $_SERVER['REMOTE_ADDR'];
			$Time = date("Y-m-d H:i:s");
			$Params = str_replace(array("\r\n", "\n", "\r"),"",var_export($query_params, true)); // можно выводить и binds, но параметры имеют вид @p1 и т.д., с query_params будет проще
			
			$sql = "
			/*
			User {$User}
			IP {$IP}
			Time {$Time}
			Params {$Params}
			*/\r\n".$sql;
		}
		// Run the Query
		if (FALSE === ($this->result_id = $this->parametric_query($sql, $query_params)))
		{
			if ($this->save_queries === TRUE)
			{
				$this->query_times[] = 0;
			}

			// This will trigger a rollback if transactions are being used
			if ($this->_trans_depth !== 0)
			{
				$this->_trans_status = FALSE;
			}

			// Grab the error now, as we might run some additional queries before displaying the error
			$error = $this->error();

			$isRAISERROR = false;
			$errorMessage = null;
			if ($error['code'] == '42000/50000') {
				$isRAISERROR = true;
				// ошибку выдала хранимка с помощью RAISERROR.
				// обрезаем всякие заголовки (пример [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]), чтобы получить читаемую для пользователя ошибку.
				$error['message'] = preg_replace('/.*\]/ui', '', $error['message']);
				// обрезаем название хранимок выдавших ошибку (пример p_EvnDirection_insToQueue : p_EvnDirection_ins : Направление с указанным н...), чтобы не показывать юзеру
				$error['message'] = trim(preg_replace('/[A-Za-z\_\s:]*:/ui', '', $error['message']));
			}

			if ($this->db_debug)
			{
				// We call this function in order to roll-back queries
				// if transactions are enabled. If we don't call this here
				// the error message will trigger an exit, causing the
				// transactions to remain in limbo.
				while ($this->_trans_depth !== 0)
				{
					$trans_depth = $this->_trans_depth;
					$this->trans_complete();
					if ($trans_depth === $this->_trans_depth)
					{
						sql_log_message('error', 'Database: Failure during an automated transaction commit/rollback!', $this->compile_binds($sql, $query_params));
						break;
					}
				}

				// Log errors
				sql_log_message('error', 'Query error: '.$error['message'], $this->compile_binds($sql, $query_params));
				return $this->display_error(
					array(
						'Error Number: ' . $error['code'],
						$error['message'],
						$sql . "
							Параметры:
						" . print_r($query_params, true)
					),
					'',
					$isRAISERROR
				);
			}

			return FALSE;
		}

		// Stop and aggregate the query time results
		$time_end = list($em, $es) = explode(' ', microtime());
		$time_total = ($em + $es) - ($sm + $ss);
		$this->benchmark += $time_total;

		if ((!empty($config['IS_DEBUG']) || isSuperAdmin() || !empty($_SESSION['IsSWANUser'])) && $sql_debug && in_array($sql_debug, array(1,2,3,4))) {
			$time_total_str = number_format($time_total, 10, ".", "");
			echo "<pre>execution time: {$time_total_str}</pre>";

			if ($sql_debug == 4) {
				echo "<pre>" . implode(PHP_EOL, getSimpleBackTrace()) . "</pre>";
			}
		}
		if ($this->save_queries === TRUE) {
			$this->query_times[] = $time_total;
		}

		// Increment the query counter
		$this->query_count++;

		// Will we have a result object instantiated? If not - we'll simply return TRUE
		if ($return_object !== TRUE)
		{
			// If caching is enabled we'll auto-cleanup any existing files related to this particular URI
			if ($this->cache_on === TRUE && $this->cache_autodel === TRUE && $this->_cache_init())
			{
				$this->CACHE->delete();
			}

			return TRUE;
		}

		if ($this->log_queries == TRUE)
		{
			if (count($this->query_times) > 0 && $this->query_times[ count($this->query_times) - 1 ] > $this->log_query_time_limit) {
				sql_log_message('info', 'Запрос выполнен за '.$this->query_times[ count($this->query_times) - 1 ].' сек', $this->queries[ count($this->queries) - 1 ]);
			}
		}

		// Load and instantiate the result driver
		$driver		= $this->load_rdriver();
		$RES		= new $driver($this);

		// Is query caching enabled? If so, we'll serialize the
		// result object and save it to a cache file.
		if ($this->cache_on === TRUE && $this->_cache_init())
		{
			// We'll create a new instance of the result object
			// only without the platform specific driver since
			// we can't use it with cached data (the query result
			// resource ID won't be any good once we've cached the
			// result object, so we'll have to compile the data
			// and save it)
			$CR = new CI_DB_result($this);
			$CR->result_object	= $RES->result_object();
			$CR->result_array	= $RES->result_array();
			$CR->num_rows		= $RES->num_rows();

			// Reset these since cached objects can not utilize resource IDs.
			$CR->conn_id		= NULL;
			$CR->result_id		= NULL;

			$this->CACHE->write($sql, $CR);
		}

		return $RES;
	}

	/**
	 * Параметризованный запрос
	 *
	 * @access public
	 * @param string $sql Запрос
	 * @param array $query_params Набор параметров в массиве
	 * @return mixed
	 */
	function parametric_query($sql, $query_params)
	{
		if ( ! $this->conn_id)
		{
			$this->initialize();
		}

		return $this->_execute($sql, $query_params);
	}

	/**
	 * Вывод ошибок MSSQL, замена стандартного
	 *
	 */
	function display_error($error = '', $swap = '', $native = FALSE)
	{
		$errorHasQuery = false;
		$errstr = '';
		if (is_array($error)) {
			$errstr .= $error[0] . "\r\n";
			$errstr .= $error[1] . "\r\n";
			$config =& get_config();
			if ($config['develop'] || isSuperadmin()) {
				$errorHasQuery = true;
				$errstr .= $error[2];
			}
		} else {
			$errstr = $error;
		}

		$trace = debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT);
		$app = str_replace(DIRECTORY_SEPARATOR, "\\".DIRECTORY_SEPARATOR, APPPATH);
		$file = '';

		foreach ($trace as $idx => $call) {
			if ($call['function'] != 'display_error') {
				if (count($trace) > 2 && preg_match("/{$app}(.+_model\\.+php)/", $trace[$idx]['file'], $match)) {
					$file = $match[1];
				} else if (count($trace) > 2 && preg_match("/{$app}(.+_model\\.+php)/", $trace[$idx+1]['file'], $match)) {
					$file = $match[1];
				} else if (preg_match("/{$app}(.+\\.php)/", $trace[$idx]['file'], $match)) {
					$file = $match[1];
				}
				break;
			}
		}
		if (!empty($file)) {
			$file = "в файле {$file} ";
		}

		$errstr = "Ошибка БД ".$file."(db: " . $this->database . ", host: " . $this->hostname . "):" . $errstr . ")!";
		$logMessage = $errstr;
		if (!$errorHasQuery && is_array($error)) {
			$logMessage .= "\r\n" . $error[2];
		}
		sql_log_message('error', $logMessage, (isset($this->queries) && isset($this->queries[0])) ? $this->queries[0] : '');
		if (($errors = sqlsrv_errors()) != null) {
			sql_log_message('error', "Ответ сервера БД:", var_export($errors, true));
		}

		if (!$native) {
			throw new Exception($errstr);
		} else {
			if (is_array($error)) {
				throw new Exception($error[1]);
			} else {
				throw new Exception($error);
			}
		}
	}
}
