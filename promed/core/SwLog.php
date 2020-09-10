<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2010 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		25.10.2010
 */

/**
 * Расширение стандартной библиотеки логирования с поддержкой логирования SQL запросов в отдельные логи
 *
 *
 * @package		Library
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 */
class SwLog extends CI_Log {
	
	function __construct() {
		parent::__construct();
	}
    
    
	// --------------------------------------------------------------------
	
	/**
	 * Логирование SQL запросов
	 *
	 * Функция вызывается из драйвера MSSQL
	 *
	 * @access	public
	 * @param	string	Уровень ошибки
     * @param	string	Сообщение
	 * @param	string	SQL запрос
	 * @return	bool
	 */		
	function sql_write_log($level = 'error', $msg, $sql)
	{		
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}
	
		$filepath = $this->_log_path.'sql-log-'.date('Y-m-d').EXT;
		$message  = '';
		
		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}
			
		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";
		
		flock($fp, LOCK_EX);	
		fwrite($fp, $message);
        fwrite($fp, $sql."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	
		return TRUE;
	}
}
// END swLog class
