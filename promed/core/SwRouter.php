<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * @copyright Copyright (c) 2009-2011, Swan Ltd.
 * @link http://swan.perm.ru/PromedWeb
 *
 * @class swRouter 
 * Библиотека, рассширяющая возможности стандартного роутера
 * Включает в себя поддержку вызова контроллеров по регионам
 *
 * @package Library
 * @access public
 *
 * @author Petukhov Ivan aka Lich (ethereallich@gmail.com)
 *
 * @version 19.03.2013
 */

// ------------------------------------------------------------------------

class SwRouter extends CI_Router {
	/**
	 * Название региона
	 *
	 * @var string
	 * @access protected
	 */
	protected $region_name = NULL;

	/**
	 * Модуль api
	 * @var string
	 */
	public $moduleName = null;

	/**
	 * Constructor
	 *
	 * Добавляет получение названия региона
	 */
	function __construct()
	{
		if ( getenv('REGION') !== false ) {
			$this->region_name = getenv('REGION');
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
			// закрываем если была закрыта.
			if ($startsession) {
				session_write_close();
			}
		}

		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * @return array
	 */
	protected function _get_api_list() {
		$api_dirs_template = APPPATH.'controllers/api/*';

		return array_map(function($dir) {
			return basename($dir);
		}, glob($api_dirs_template, GLOB_ONLYDIR));
	}

	/**
	 * Set route mapping
	 *
	 * Determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @return	void
	 */
	protected function _set_routing()
	{
		// Load the routes.php file. It would be great if we could
		// skip this for enable_query_strings = TRUE, but then
		// default_controller would be empty ...
		if (file_exists(APPPATH.'config/routes.php'))
		{
			include(APPPATH.'config/routes.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/routes.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/routes.php');
		}

		// Validate & get reserved routes
		if (isset($route) && is_array($route)) {
			isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
			isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
			unset($route['default_controller'], $route['translate_uri_dashes']);
			$this->routes = $route;
		}

		$this->_parse_routes();

		$module_name = $this->config->item('PROMED_MODULE');

		if (isset($this->uri->segments[0]) && mb_strtolower($this->uri->segments[0]) == 'api') {
			if (!empty($module_name) && $this->uri->segments[1] != $module_name && !in_array($this->uri->segments[1], $this->_get_api_list())) {
				$this->moduleName = $module_name;
				array_splice($this->uri->segments, 1, 0, $module_name);
				$this->directory = null;
				$this->_parse_routes();
			} else {
				$this->moduleName = $this->uri->segments[1];
			}
		}
		if (isset($this->uri->segments[1]) && mb_strtolower($this->uri->segments[1]) == 'api')
		{
			return;
		}

		if ($this->enable_query_strings)
		{
			// If the directory is set at this time, it means an override exists, so skip the checks
			if ( ! isset($this->directory))
			{
				$_d = $this->config->item('directory_trigger');
				$_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';

				if ($_d !== '')
				{
					$this->uri->filter_uri($_d);
					$this->set_directory($_d);
				}
			}

			$_c = trim($this->config->item('controller_trigger'));
			if ( ! empty($_GET[$_c]))
			{
				$this->uri->filter_uri($_GET[$_c]);
				$class_name = ucfirst($_GET[$_c]);

				if ( isset($this->region_name) ) {
					$region = ucfirst($this->region_name);
					if (file_exists(APPPATH.'controllers/'.$this->directory.$this->region_name.'/'.$region.'_'.$class_name.'.php')) {
						$this->set_directory($this->region_name, true);
						$class_name = $region.'_'.$class_name;
					}
				}

				$this->set_class($class_name);

				$_f = trim($this->config->item('function_trigger'));
				if ( ! empty($_GET[$_f]))
				{
					$this->uri->filter_uri($_GET[$_f]);
					$this->set_method($_GET[$_f]);
				}

				$this->uri->rsegments = array(
					1 => $this->class,
					2 => $this->method
				);
			}
			else
			{
				$this->_set_default_controller();
			}

			// Routing rules don't apply to query strings and we don't need to detect
			// directories, so we're done here
			return;
		}

		// Is there anything to parse?
		if ($this->uri->uri_string !== '')
		{
			$this->_parse_routes();
		}
		else
		{
			$this->_set_default_controller();
		}
	}

	/**
	 * Validate request
	 *
	 * Attempts validate the URI request and determine the controller path.
	 *
	 * @used-by	CI_Router::_set_request()
	 * @param	array	$segments	URI segments
	 * @return	mixed	URI segments
	 */
	protected function _validate_request($segments)
	{
		if ( isset($this->region_name) && count($segments) > 0 ) { // если задан регион
			if ( file_exists(APPPATH.'controllers/'.$this->region_name.'/'.$segments[0].'.php') ) { // и существует подкласс для региона
				$this->set_directory($this->region_name); // меняем директорию для контроллера
				$segments[0] = $this->region_name.'_'.$segments[0]; // подменяем сегмент
				return $segments;
			}
		}

		$c = count($segments);
		$directory_override = isset($this->directory);

		// Loop through our segments and return as soon as a controller
		// is found or when such a directory doesn't exist
		while ($c-- > 0)
		{
			$test = $this->directory
				.ucfirst($this->translate_uri_dashes === TRUE ? str_replace('-', '_', $segments[0]) : $segments[0]);

			if ( ! file_exists(APPPATH.'controllers/'.$test.'.php')
				&& $directory_override === FALSE
				&& is_dir(APPPATH.'controllers/'.$this->directory.$segments[0])
			)
			{
				$this->set_directory(array_shift($segments), TRUE);
				continue;
			}

			return $segments;
		}

		// This means that all segments were actually directories
		return $segments;
	}

	/**
	 * Регистронезависимая обработка сегментов запроса
	 */
	protected function _parse_routes()
	{
		$this->uri->segments = array_values($this->uri->segments);
		if (isset($this->uri->segments[0]) && $this->uri->segments[0] == '--run=') {
			array_shift($this->uri->segments);
		}

		return parent::_parse_routes();
	}
}
// END swRouter Class