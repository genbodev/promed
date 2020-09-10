<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * @copyright Copyright (c) 2009-2011, Swan Ltd.
 * @link http://swan.perm.ru/PromedWeb
 *
 * @class SwConfig
 * Библиотека, рассширяющая возможности стандартного загрузчика конфига
 * Включает в себя поддержку разделения по регионам
 *
 * @package Library
 * @access public
 *
 * @author Vlasenko Dmitry
 *
 * @version 08.05.2013
 */

// ------------------------------------------------------------------------

class SwConfig extends CI_Config {
	/**
	 * Название региона
	 *
	 * @var string
	 * @access protected
	 */
	protected $region_name = NULL;
	protected $dbtype = NULL;

	/**
	 * Constructor
	 *
	 * Добавляет получение названия региона
	 */
	function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		if ( getenv('REGION') !== false ) {
			$this->region_name = getenv('REGION');
		}
		if ( getenv('DBTYPE') !== false ) {
			$this->dbtype = getenv('DBTYPE');
		}
		
		if ( getenv('USER_CAN_CHANGE_REGION') ) {
			// смотрим регион в сессии
			$startsession = false;
			if (!isset($_SESSION)) {
				$startsession = true;
			}
			if ($startsession) {
				session_start();
			}
			if (!empty($_SESSION['REGION_ENV'])) {
				$this->region_name = $_SESSION['REGION_ENV'];
			}
			if (!empty($_COOKIE['DBTYPE_ENV'])) {
				$this->dbtype = $_COOKIE['DBTYPE_ENV'];
			} else if (!empty($_SESSION['DBTYPE_ENV'])) {
				$this->dbtype = $_SESSION['DBTYPE_ENV'];
			}
			// закрываем если была закрыта.
			if ($startsession) {
				session_write_close();
			}
		}

		$file = ($file === '') ? 'config' : str_replace('.php', '', $file);
		$loaded = FALSE;

		foreach ($this->_config_paths as $path)
		{
			foreach (array($file, ENVIRONMENT.DIRECTORY_SEPARATOR.$file) as $location)
			{
				$configPath = 'config/';
				$file_path = $path . $configPath . $location . '.php';
				if (isset($this->region_name) && file_exists($path . $configPath . $this->region_name . '/' . $location . '.php')) { // если задан регион и есть конфиг этого региона, то грузим его.
					$file_path = $path . $configPath . $this->region_name . '/' . $location . '.php';
				}
				if (isset($this->dbtype) && $this->dbtype == 'pgsql') {
					$configPath .= '_pgsql/';
					if (file_exists($path . $configPath . $location . '.php')) {
						$file_path = $path . $configPath . $location . '.php';
					}
					if (isset($this->region_name) && file_exists($path . $configPath . $this->region_name . '/' . $location . '.php')) { // если задан регион и есть конфиг этого региона, то грузим его.
						$file_path = $path . $configPath . $this->region_name . '/' . $location . '.php';
					}
				}
			
				if (in_array($file_path, $this->is_loaded, TRUE))
				{
					return TRUE;
				}

				if ( ! file_exists($file_path))
				{
					continue;
				}

				include($file_path);

				if ( ! isset($config) OR ! is_array($config))
				{
					if ($fail_gracefully === TRUE)
					{
						return FALSE;
					}

					show_error('Your '.$file_path.' file does not appear to contain a valid configuration array.');
				}

				if ($use_sections === TRUE)
				{
					$this->config[$file] = isset($this->config[$file])
						? array_merge($this->config[$file], $config)
						: $config;
				}
				else
				{
					$this->config = array_merge($this->config, $config);
				}

				$this->is_loaded[] = $file_path;
				$config = NULL;
				$loaded = TRUE;
				log_message('debug', 'Config file loaded: '.$file_path);
			}
		}

		if ($loaded === TRUE)
		{
			return TRUE;
		}
		elseif ($fail_gracefully === TRUE)
		{
			return FALSE;
		}

		show_error('The configuration file '.$file.'.php does not exist.');
	}

}
// END swConfig Class