<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * @copyright Copyright (c) 2009-2011, Swan Ltd.
 * @link http://swan.perm.ru/PromedWeb
 *
 * @class swLoader 
 * Библиотека, рассширяющая возможности стандартного загрузчика
 * Включает в себя поддержку разделения по регионам
 *
 * @package Library
 * @access public
 *
 * @author Petukhov Ivan aka Lich (ethereallich@gmail.com)
 *
 * @version 18.03.2013
 */

// ------------------------------------------------------------------------

class SwLoader extends CI_Loader {

	/**
	 * Название региона
	 *
	 * @var string
	 * @access protected
	 */
	protected $region_name = NULL;

    /**
     * Экземпляр подключения к базе для PostgreSQL
     *
     * @var CI_DB
     */
	protected $pg_db;

	/**
	 * @return string
	 */
	public function getRegionNick()
	{
		return $this->region_name;
	}


	/**
	 * Constructor
	 *
	 * Добавляет получение названия региона
	 */
	public function __construct()
	{
		parent::__construct();
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
	}

	// --------------------------------------------------------------------

	/**
	 * Database Loader
	 *
	 * @param	mixed	$params		Database configuration options
	 * @param	bool	$return 	Whether to return the database object
	 * @param	bool	$query_builder	Whether to enable Query Builder
	 *					(overrides the configuration setting)
	 *
	 * @return	object|bool	Database object if $return is set to TRUE,
	 *					FALSE on failure, CI_Loader instance in any other case
	 */
	public function database($params = '', $return = FALSE, $query_builder = NULL)
	{
		// Grab the super object
		$CI =& get_instance();
		$moduleName = $CI->router->moduleName;

		// Do we even need to load the database class?
		if ($return === FALSE && $query_builder === NULL && isset($CI->db) && is_object($CI->db) && ! empty($CI->db->conn_id))
		{
			return FALSE;
		}
		if (!empty($_REQUEST['archiveRecord'])) {
			$params = "archive"; // архивная бд если передаём признак архивной записи
		}
		if (empty($params) || $params == "default") {
			switch(true) {
				case $moduleName == "lis":
					$params = "lis";
					break;
				case $CI->usePostgre:
					$params = "postgres";
					break;
			}
		}

		require_once(BASEPATH.'database/DB.php');

		if ($return === TRUE)
		{
			return DB($params, $query_builder);
		}

		// Initialize the db variable. Needed to prevent
		// reference errors with some configurations
		$CI->db = '';

		// Load the DB class
		$CI->db =& DB($params, $query_builder);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * @param string $model
	 * @param string $name
	 * @return bool
	 */
	protected function modelWithNamespace($model, $name) {
		$CI =& get_instance();
		$region = ucfirst($this->region_name);
		$ms_namespace = 'Promed\MSSQL';
		$pg_namespace = 'Promed\PostgreSQL';
		$model = str_replace("{$region}_", "", $model);
		$orig_model = $model;
		$is_pg_model = false;

		foreach ($this->_ci_model_paths as $mod_path) {
			if ($CI->usePostgre) {
				$search_config = [[
					'alias'	=> $model,
					'class'	=> "{$ms_namespace}\\{$model}",
					'path'	=> "{$mod_path}models/{$model}.php",
				], [
					'alias'	=> $model,
					'class'	=> "{$pg_namespace}\\{$model}",
					'path'	=> "{$mod_path}models/_pgsql/{$model}.php",
				], [
					'alias'	=> "{$region}_{$model}",
					'class'	=> "{$ms_namespace}\\{$region}_{$model}",
					'path'	=> "{$mod_path}models/{$this->region_name}/{$region}_{$model}.php",
				], [
					'alias'	=> "{$region}_{$model}",
					'class'	=> "{$pg_namespace}\\{$region}_{$model}",
					'path'	=> "{$mod_path}models/_pgsql/{$this->region_name}/{$region}_{$model}.php",
				]];
			} else {
				$search_config = [[
					'alias'	=> $model,
					'class'	=> "{$ms_namespace}\\{$model}",
					'path'	=> "{$mod_path}models/{$model}.php",
				], [
					'alias'	=> "{$region}_{$model}",
					'class'	=> "{$ms_namespace}\\{$region}_{$model}",
					'path'	=> "{$mod_path}models/{$this->region_name}/{$region}_{$model}.php",
				]];
			}

			$alias_config = null;
			foreach ($search_config as $config) {
				if ($alias_config && $alias_config['alias'] != $config['alias'] && !class_exists($alias_config['alias'], false)) {
					class_alias($alias_config['class'], $alias_config['alias'], false);
					$alias_config = null;
				}
				if (file_exists($config['path'])) {
					require_once($config['path']);
				}
				if (class_exists($config['class'], false)) {
					$model = $config['class'];
					$alias_config = $config;
				}
				if (strpos($model, $pg_namespace) === 0) {
					$is_pg_model = true;
				}
			}
			if ($alias_config && !class_exists($alias_config['alias'], false)) {
				class_alias($alias_config['class'], $alias_config['alias'], false);
			}
		}

		if ($model != $orig_model) {
			$this->_ci_models[] = $name;
			$CI->$name = new $model();
			$CI->$name->is_pg = $is_pg_model;
			return true;
		}

		return false;
	}

	/**
	 * Model Loader
	 *
	 * Loads and instantiates models.
	 *
	 * @param	string	$model		Model name
	 * @param	string	$name		An optional object name to assign to
	 * @param	bool	$db_conn	An optional database connection configuration to initialize
	 * @param	bool	$use_namespace
	 * @return	object
	 */
	public function model($model, $name = '', $db_conn = FALSE, $use_namespace = NULL)
	{
		if (empty($model))
		{
			return $this;
		}
		elseif (is_array($model))
		{
			foreach ($model as $key => $value)
			{
				is_int($key) ? $this->model($value, '', $db_conn) : $this->model($key, $value, $db_conn);
			}

			return $this;
		}

		$path = '';

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($model, '/')) !== FALSE)
		{
			// The path is in front of the last slash
			$path = substr($model, 0, ++$last_slash);

			// And the model name behind it
			$model = substr($model, $last_slash);
		}

		if (empty($name))
		{
			$name = $model;
		}

		if (in_array($name, $this->_ci_models, TRUE))
		{
			return $this;
		}

		$CI =& get_instance();
		if (isset($CI->$name))
		{
			throw new RuntimeException('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		if ($db_conn !== FALSE && ! class_exists('CI_DB', FALSE))
		{
			if ($db_conn === TRUE)
			{
				$db_conn = '';
			}

			$this->database($db_conn, FALSE, TRUE);
		}

		// Note: All of the code under this condition used to be just:
		//
		//       load_class('Model', 'core');
		//
		//       However, load_class() instantiates classes
		//       to cache them for later use and that prevents
		//       MY_Model from being an abstract class and is
		//       sub-optimal otherwise anyway.
		if ( ! class_exists('CI_Model', FALSE))
		{
			$app_path = APPPATH.'core'.DIRECTORY_SEPARATOR;
			if (file_exists($app_path.'Model.php'))
			{
				require_once($app_path.'Model.php');
				if ( ! class_exists('CI_Model', FALSE))
				{
					throw new RuntimeException($app_path."Model.php exists, but doesn't declare class CI_Model");
				}
			}
			elseif ( ! class_exists('CI_Model', FALSE))
			{
				require_once(BASEPATH.'core'.DIRECTORY_SEPARATOR.'Model.php');
			}

			$class = config_item('subclass_prefix').'Model';
			if (file_exists($app_path.$class.'.php'))
			{
				require_once($app_path.$class.'.php');
				if ( ! class_exists($class, FALSE))
				{
					throw new RuntimeException($app_path.$class.".php exists, but doesn't declare class ".$class);
				}
			}
            // Загрузка наследуемой базовой модели для PostgreSQL SwPgModel модели от SwModel
            $class = config_item('subclass_prefix').'PgModel';
            if (file_exists($app_path.$class.'.php'))
            {
                require_once($app_path.$class.'.php');
                if ( ! class_exists($class, FALSE))
                {
                    throw new RuntimeException($app_path.$class.".php exists, but doesn't declare class ".$class);
                }
            }
		}

		$model = ucfirst($model);
		$class_exists = class_exists($model, FALSE);
		if ($class_exists && isset($this->region_name) && class_exists($this->region_name.'_'.$model, FALSE)) {
			$model = $this->region_name.'_'.$model;
		}
		if (!$class_exists)
		{
			if ($use_namespace && $this->modelWithNamespace($model, $name)) {
				return $this;
			}

			foreach ($this->_ci_model_paths as $mod_path)
			{
				$region = ucfirst($this->region_name);
				if (file_exists($mod_path.'models/'.$this->region_name.'/'.$path.$region.'_'.$model.'.php')) {
					$model = $region.'_'.$model;
				}

				if ( isset($this->region_name) ) { // если задан регион
					if ( file_exists($mod_path.'models/'.$this->region_name.'/'.$path.$model.'.php') ) { // и существует подкласс для региона
						$path = $this->region_name.'/'.$path; // добавляем в путь подпапку региона
					}
				}

				// Загрузка модели из папки _pgsql
				if ($CI->usePostgre) {
					if (file_exists($mod_path.'models/_pgsql/'.$path.$model.'.php')) {
						$path = '_pgsql/' . $path;
					}
				}elseif($CI->usePostgreRegistry && strripos($model,'Registry_model')) {
					if (file_exists($mod_path.'models/_pgsql/'.$path.$model.'.php')) {
						$path = '_pgsql/' . $path;
					}
				}

				$full_path = $mod_path.'models/'.$path.$model.'.php';

				if ( ! file_exists($full_path))
				{
					continue;
				}

				if (false && $use_namespace !== false) {
					$tokens = array();
					foreach (token_get_all(file_get_contents($full_path)) as $item) {
						if (is_array($item)) $tokens[] = $item[1];
					}
					if (in_array('namespace', $tokens) && $this->modelWithNamespace($model, $name)) {
						return $this;
					}
				}

				require_once($full_path);
				if ( ! class_exists($model, FALSE))
				{
					throw new RuntimeException($full_path." exists, but doesn't declare class ".$model);
				}

				break;
			}

			if ( ! class_exists($model, FALSE))
			{
				throw new RuntimeException('Unable to locate the model you have specified: '.$model);
			}
		}
		elseif ( ! is_subclass_of($model, 'CI_Model'))
		{
			throw new RuntimeException("Class ".$model." already exists and doesn't extend CI_Model");
		}

		$this->_ci_models[] = $name;
		$CI->$name = new $model();
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * View Loader
	 *
	 * Loads "view" files.
	 *
	 * @param	string	$view	View name
	 * @param	array	$vars	An associative array of data
	 *				to be extracted for use in the view
	 * @param	bool	$return	Whether to return the view output
	 *				or leave it to the Output class
	 * @return	object|string
	 */
	public function view($view, $vars = array(), $return = FALSE, $ConvertToUtf8 = FALSE, $headerUtf8 = FALSE)
	{
		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return), $ConvertToUtf8, $headerUtf8);
	}

	// --------------------------------------------------------------------

	/**
	 * Internal CI Data Loader
	 *
	 * Used to load views and files.
	 *
	 * Variables are prefixed with _ci_ to avoid symbol collision with
	 * variables made available to view files.
	 *
	 * @used-by	CI_Loader::view()
	 * @used-by	CI_Loader::file()
	 * @param	array	$_ci_data	Data to load
	 * @return	object
	 */
	protected function _ci_load($_ci_data, $ConvertToUtf8 = FALSE, $headerUtf8 = FALSE)
	{
		// Set the default data variables
		foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val)
		{
			$$_ci_val = isset($_ci_data[$_ci_val]) ? $_ci_data[$_ci_val] : FALSE;
		}

		$file_exists = FALSE;

		// Set the path to the requested file
		if (is_string($_ci_path) && $_ci_path !== '')
		{
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		}
		else
		{
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext === '') ? $_ci_view.'.php' : $_ci_view;

			foreach ($this->_ci_view_paths as $_ci_view_file => $cascade)
			{
				if ( isset($this->region_name) ) { // если задан регион
					if ( file_exists($_ci_view_file.$this->region_name.'/'.$_ci_file) ) { // и существует подкласс для региона
						$_ci_view_file = $_ci_view_file.$this->region_name.'/'; // добавляем в путь подпапку региона
					}
				}
				if (file_exists($_ci_view_file.$_ci_file))
				{
					$_ci_path = $_ci_view_file.$_ci_file;
					$file_exists = TRUE;
					break;
				}

				if ( ! $cascade)
				{
					break;
				}
			}
		}

		if ( ! $file_exists && ! file_exists($_ci_path))
		{
			show_error('Unable to load the requested file: '.$_ci_file);
		}

		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.
		$_ci_CI =& get_instance();
		foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var)
		{
			if ( ! isset($this->$_ci_key))
			{
				$this->$_ci_key =& $_ci_CI->$_ci_key;
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load->vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */
		if (is_array($_ci_vars))
		{
			foreach (array_keys($_ci_vars) as $key)
			{
				if (strncmp($key, '_ci_', 4) === 0)
				{
					unset($_ci_vars[$key]);
				}
			}

			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be post-processed by
		 *	the output class. Why do we need post processing? For one thing,
		 *	in order to show the elapsed page load time. Unless we can
		 *	intercept the content right before it's sent to the browser and
		 *	then stop the timer it won't be accurate.
		 */
		ob_start();

		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.
		try {
			if ( ! is_php('5.4') && ! ini_get('short_open_tag') && config_item('rewrite_short_tags') === TRUE)
			{
				echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
			}
			else
			{
				include($_ci_path); // include() vs include_once() allows for multiple views with the same name
			}
		} catch(Exception $e) {
			log_message('error', 'File loaded '.$_ci_path." with error: ".$e->getMessage());
		}
		
		log_message('info', 'File loaded: '.$_ci_path);

		// Return the file data if requested
		if ($_ci_return === TRUE)
		{
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 */
		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			$_ci_CI->output->append_output(ob_get_contents());
			@ob_end_clean();
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Internal CI Library Loader
	 *
	 * @used-by	CI_Loader::library()
	 * @uses	CI_Loader::_ci_init_library()
	 *
	 * @param	string	$class		Class name to load
	 * @param	mixed	$params		Optional parameters to pass to the class constructor
	 * @param	string	$object_name	Optional object name to assign to
	 * @return	void
	 */
	protected function _ci_load_library($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		$class = str_replace('.php', '', trim($class, '/'));

		// Was the path included with the class name?
		// We look for a slash to determine this
		if (($last_slash = strrpos($class, '/')) !== FALSE)
		{
			// Extract the path
			$subdir = substr($class, 0, ++$last_slash);

			// Get the filename from the path
			$class = substr($class, $last_slash);
		}
		else
		{
			$subdir = '';
		}

		$class = ucfirst($class);

		$CI =& get_instance();

		if ( isset($this->region_name) ) { // если задан регион
			if ( file_exists(APPPATH.'libraries/'.$this->region_name.'/'.$subdir.config_item('subclass_prefix').$class.'.php') || file_exists(APPPATH.'libraries/'.$this->region_name.'/'.$subdir.$class.'.php') ) { // и существует подкласс для региона
				$subdir .= $this->region_name.'/'; // добавляем в путь подпапку региона
			}
		}

		// Is this a stock library? There are a few special conditions if so ...
		if (file_exists(BASEPATH.'libraries/'.$subdir.$class.'.php'))
		{
			return $this->_ci_load_stock_library($class, $subdir, $params, $object_name);
		}

		// Let's search for the requested library file and load it.
		foreach ($this->_ci_library_paths as $path)
		{
			// BASEPATH has already been checked for
			if ($path === BASEPATH)
			{
				continue;
			}

			$filepath = $path.'libraries/'.$subdir.$class.'.php';

			// Загрузка библиотеки из папки _pgsql
			if ($CI->usePostgre) {
				if (file_exists($path . 'libraries/_pgsql/' . $subdir . $class . '.php')) {
					$filepath = $path.'libraries/_pgsql/'.$subdir.$class.'.php';
				}
			}

			// Safety: Was the class already loaded by a previous call?
			if (class_exists($class, FALSE))
			{
				// Before we deem this to be a duplicate request, let's see
				// if a custom object name is being supplied. If so, we'll
				// return a new instance of the object
				if ($object_name !== NULL)
				{
					if ( ! isset($CI->$object_name))
					{
						return $this->_ci_init_library($class, '', $params, $object_name);
					}
				}

				log_message('debug', $class.' class already loaded. Second attempt ignored.');
				return;
			}
			// Does the file exist? No? Bummer...
			elseif ( ! file_exists($filepath))
			{
				continue;
			}

			include_once($filepath);
			return $this->_ci_init_library($class, '', $params, $object_name);
		}

		// One last attempt. Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir === '')
		{
			return $this->_ci_load_library($class.'/'.$class, $params, $object_name);
		}

		// If we got this far we were unable to find the requested class.
		log_message('error', 'Unable to load the requested class: '.$class);
		show_error('Unable to load the requested class: '.$class);
	}

	// --------------------------------------------------------------------

	/**
	 * Helper Loader
	 *
	 * @param	string|string[]	$helpers	Helper name(s)
	 * @return	object
	 */
	public function helper($helpers = array())
	{
		foreach ($this->_ci_prep_filename($helpers, '_helper') as $helper)
		{
			if (isset($this->_ci_helpers[$helper]))
			{
				continue;
			}

			$subdir = "";
			if ( isset($this->region_name) ) { // если задан регион
				if ( file_exists(APPPATH.'helpers/'.$this->region_name.'/'.config_item('subclass_prefix').$helper.'.php') || file_exists(APPPATH.'helpers/'.$this->region_name.'/'.$helper.'.php') ) { // и существует подкласс для региона
					$subdir = $this->region_name.'/'; // добавляем в путь подпапку региона
				}
			}

			// Is this a helper extension request?
			$ext_helper = config_item('subclass_prefix').$helper;
			$ext_loaded = FALSE;
			foreach ($this->_ci_helper_paths as $path)
			{
				if (file_exists($path.'helpers/'.$subdir.$ext_helper.'.php'))
				{
					include_once($path.'helpers/'.$subdir.$ext_helper.'.php');
					$ext_loaded = TRUE;
				}
			}

			// If we have loaded extensions - check if the base one is here
			if ($ext_loaded === TRUE)
			{
				$base_helper = BASEPATH.'helpers/'.$subdir.$helper.'.php';
				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.'.php');
				}

				include_once($base_helper);
				$this->_ci_helpers[$helper] = TRUE;
				log_message('info', 'Helper loaded: '.$helper);
				continue;
			}

			// No extensions found ... try loading regular helpers and/or overrides
			foreach ($this->_ci_helper_paths as $path)
			{
				if (file_exists($path.'helpers/'.$subdir.$helper.'.php'))
				{
					include_once($path.'helpers/'.$subdir.$helper.'.php');

					$this->_ci_helpers[$helper] = TRUE;
					log_message('info', 'Helper loaded: '.$helper);
					break;
				}
			}

			// unable to load the helper
			if ( ! isset($this->_ci_helpers[$helper]))
			{
				show_error('Unable to load the requested file: helpers/'.$helper.'.php');
			}
		}

		return $this;
	}

	/**
	 * @param string $name
	 */
	public function swapi($name) {
		$CI =& get_instance();
		$config = $CI->config->item('SwService'.ucfirst($name));
		$this->library('SwServiceApi', $config, $name);
	}

	/**
	 * @param string|string[] $filename
	 * @param string $extension
	 * @return array|string|string[]
	 */
    protected function _ci_prep_filename($filename, $extension)
    {
        if ( ! is_array($filename))
        {
            return array(strtolower(str_replace(array($extension, '.php'), '', $filename).$extension));
        }
        else
        {
            foreach ($filename as $key => $val)
            {
                $filename[$key] = strtolower(str_replace(array($extension, '.php'), '', $val).$extension);
            }

            return $filename;
        }
    }
}