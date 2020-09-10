<?php

# MongoDB sessions handler (c) Lich base on Lockable MongoDB sessions handler by Alex/AT

# Хранение сессий PHP в СУБД MongoDB
# Применять для распараллеливания веб-серверов и прочих нужд

# SCHEMA:

#[Sessions](
#	[_id] [varchar](255) NOT NULL,
#	[value] [text] NULL,
#	[updated] [datetime] NULL,
#)

/**
 *
 * @property Resource mongo_db
 * @property Resource conn
 */
class MONGODB_SESSIONS
{
    var $table = 'Session'; # Default MongoDB table to use 
	var $compress = TRUE; # compress database data? DO NOT CHANGE IN REALTIME!
    var $compress_level = 6; # compression level
	private $mongo_db = NULL;
	//private static $CI = null;
    //var $mongo_db = NULL; // идентификатор подключения
	
	private static $conn = NULL; // идентификатор подключения
	private static $params = NULL; // параметры подключения
	protected $db_id = null;
	protected $db_value = null;

	/**
	 * Создаем объект и сразу же его инициализируем или не инициализируем
	 * Настройки подключения к MongoDB лежат в конфиге
	 * Класс использует библиотеку Mongo_db
	 */
    function __construct($cfg = null) {
		// если передан конфиг, то берем из него наименование таблицы
		if (isset($cfg) && is_array($cfg) && isset($cfg['mongodb_session_settings'])) {
			$this->table = $cfg['mongodb_session_settings']['table'];
		}

		switch (checkMongoDb()) {
			case 'mongo':
				require_once(APPPATH.'libraries/SwMongodb.php');
				break;
			case 'mongodb':
				require_once(APPPATH.'libraries/SwMongodbPHP7.php');
				break;
			default:
				// Использование хранения сессий в MongoDB невозможно
				die('The MongoDB PECL extension has not been installed or enabled. Use session with MongoDB impossible.');
				break;
		}

		// берем конфиг mongodb для определенного региона
		$region = ( getenv('REGION') !== false ) ? getenv('REGION') : '';
		$path = APPPATH.'config/'.((!empty($region))?$region.'/':'').'mongodbsessions.php';
		if (!file_exists($path)) { // Если нет файла конфига по региону, то попробуем взять общий
			$path = APPPATH.'config/mongodbsessions.php';
		}
		if (!file_exists($path)) { // Если нет файла конфига по региону, то попробуем взять общий
			die('Not found config file MongoDB for sessions (mongodbsessions.php)');
		} else {
			require_once($path);
			if (isset($config)) {
				switch (checkMongoDb()) {
					case 'mongo':
						$this->mongo_db = new swMongodb($config); // используем нужную библиотеку (коннект выполняется автоматически при подключении библиотеки)
						break;
					case 'mongodb':
						$this->mongo_db = new swMongodbPHP7($config); // используем нужную библиотеку (коннект выполняется автоматически при подключении библиотеки)
						break;
				}
				$this->init();
			} else {
				die('Not defined parameters config file MongoDB for sessions');
			}
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
	 * sets up and installs MongoDB session handlers
	 */
    function init() {
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
    function _open() {
		//$this->textlog->add('_open');
		$this->connect();
		return true; // тип ресурса, у монгодб его нет
    }

	/**
	 * Подключение к MongoDB
	 */
	function connect() {
		// todo: возможно возникнет необходимость перенести коннект из __construct
    }
	
	/**
	 * Закрытие сессии
	 */
    function _close() {
        if (!$this->mongo_db) 
			return(FALSE);
		else 
			return(TRUE);
    }
	/**
	 * Возвращает количество записей в коллекции Session
	 */
	function session_count() {
		return $this->mongo_db->count($this->table);
    }
	
	/**
	 * Чтение сессии
	 */
    function _read($id) {
		//$this->textlog->add('_read '.$id);
		$value = $this->getvalue($id);
		return ($value);
    }

	/**
	 * Достает содержание сессии из БД
	 */
	function getvalue($id)
	{
		//$this->textlog->add('getvalue: id '.$id);
		if (!$this->mongo_db) die('Session failure: READ_NO_CONNECT');
		$row = $this->mongo_db->where(array('_id' => $id))->select(array('_id', 'value'))->get($this->table);
		if (is_array($row)) {
			if (count($row) > 0 && !empty($row[0]['_id'])) {
				$this->db_id = $row[0]['_id'];
				$value = base64_decode($row[0]['value']);
				$value = $this->compress ? gzuncompress($value) : $value;
				$this->db_value = $value;

				return $value;
			}
		}
		return ""; // всегда должна возвращать строку!
	}

	/**
	 * Проверяет наличие ID в БД
	 */
	function getid($id) {
		//$this->textlog->add('getvalue: id '.$id);
		if (!$this->mongo_db) die('Session failure: READ_NO_CONNECT');
		$row = $this->mongo_db->where(array('_id'=>$id))->select(array('_id'))->get($this->table);
		if (is_array($row)) {
			if (count($row)>0 && !empty($row[0]['_id'])) {
				return $row[0]['_id'];
			}
		}
		return null;
	}
	
	/**
	 * Запись сессии
	 */
    function _write($id, $data)
    {
		if (!$this->mongo_db) die('Session failure: WRITE_NO_CONNECT');
		//$this->textlog->add('_write: '.$id);
		# write session
		$logged = (mb_strpos($data, 'pmuser_id|') === FALSE) ? 0 : 1; // определяем залогинен ли в промед, по наличию в сессии pmuser_id.
		$org_id = null;
		$orgtype_id = null;
		$armtype = null;
		$pmuser_id = null;
		if ($logged) {
			preg_match('/org_id\|s:[0-9]*:"([0-9]*)"/u', $data, $matches);
			if (!empty($matches[1])) {
				$org_id = $matches[1];
			}
			preg_match('/orgtype_id\|s:[0-9]*:"([A-Za-z0-9_]*)"/u', $data, $matches);
			if (!empty($matches[1])) {
				$orgtype_id = $matches[1];
			}
			preg_match('/CurArmType\|s:[0-9]*:"([A-Za-z0-9_]*)"/u', $data, $matches);
			if (!empty($matches[1])) {
				$armtype = $matches[1];
			}
			preg_match('/pmuser_id\|s:[0-9]*:"([0-9]*)"/u', $data, $matches);
			if (!empty($matches[1])) {
				$pmuser_id = $matches[1];
			}
		}

		if (!empty($this->db_value) && $this->db_value == $data) {
			// только дату апдейтить
			$res = $this->mongo_db->where(array('_id' => $id))->set(array('updated' => time()))->update($this->table);
		} else {
			$this->db_value = $data; // сохраняем то, что записываем в БД.

			$value = $this->compress ? gzcompress($data, $this->compress_level) : $data;
			$value = base64_encode($value); // convert to base64

			$res = $this->mongo_db->where(array('_id' => $id))->update($this->table, array(
				'value' => $value,
				'updated' => time(),
				'logged' => $logged,
				'org_id' => $org_id,
				'orgtype_id' => $orgtype_id,
				'armtype' => $armtype,
				'pmuser_id' => $pmuser_id,
			), array(
				'upsert' => true // если записи нет, то будет создана
			));
		}
		if (!$res) die('Session failure: WRITE_NO_RESULT');
		//$this->textlog->add('_write end: '.$id);
        # done
        return(TRUE);
	}

	/**
	 * Удаление сессии
	 */
    function _destroy($id)
	{
		//$this->textlog->add('_destroy');
		if (!$this->mongo_db) die('Session failure: DESTROY_NO_CONNECT');
		
		$res = $this->mongo_db->where(array('_id'=>$id))->delete($this->table);
		if (!$res) die('Session failure: DESTROY_NO_RESULT');

        # done retrieving session data
        return(TRUE);
    }

	/**
	 * Очистка устаревших сессий
	 */
    function _gc($lifetime)
    {
		//$this->textlog->add('_gc');
		if (!$this->mongo_db) die('Session failure: GC_NO_CONNECT');

        # perform the read
		$_time = time() - $lifetime;
		
		if (defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS) {
			// Получаем все истекшие сессии
			$rows = $this->mongo_db->where_lt('updated', $_time)->get($this->table);
			
			if (count($rows) > 0 ) {
				require_once(APPPATH.'libraries/UserAudit.php');
				// По каждой такой сессии пишем в лог, что пользователь вышел
				foreach($rows as $row) {
					// В зависимости от mongo_return в конфиге тут может быть объект или массив.
					if (is_array($row)) {
						UserAudit::Logout($row['_id']);
					} else if (is_object($row)) {
						UserAudit::Logout($row->_id);
					}
				}
			}
		}
		
		$res = $this->mongo_db->where_lt('updated', $_time)->delete_all($this->table);
		
        if (!$res) die('Session failure: GC_NO_RESULT');
        # done retrieving session data
        return(TRUE);
    }
}

?>