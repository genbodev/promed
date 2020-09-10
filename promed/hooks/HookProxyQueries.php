<?php
/**
 * Class ProxyQueries
 *
 * Перенаправляет указанные запросы к контроллеру на другой сервер.
 * Конфигурационный файл proxy_queries.php содержит список настроек:
 * $config['proxy_queries'] = array(
 *	'settings' => array(
 *		// Сервер на который будут перенаправлены запросы, без указания протокола и слешей
 * 		'server'			=> '192.168.37.11',
 *		// Порт на который будут перенаправлены HTTP запросы
 * 		'http_port'			=> '80',
 *		// Порт на который будут перенаправлены HTTPS запросы
 * 		'https_port'		=> '443',
 *		// Таймаут в секундах
 * 		'timeout'			=> '5',
 *		// Данные если необходима авторизация HTTP
 * 		'http_auth_user'	=> null,
 * 		'http_auth_passwd'	=> null,
 *		// Ключ для хранения данных в сессии
 *		'session_key'		=> 'proxyq',
 * 	),
 * 	'routes' => array(
 * 		array( 'c' => 'User', 'm' => 'getUsersGroupsList' )
 * 	),
 * );
 */
class HookProxyQueries {

	/**
	 * @var bool Init flag. If not init, then proxy not run
	 */
	protected $_initialized = false;

	/**
	 * @var object Class Input
	 */
	public $IN;

	/**
	 * @var object Class Router
	 */
	public $RTR;

	/**
	 * @var object Class Config
	 */
	public $CFG;

	/**
	 * @var array Settings
	 */
	public $config;

	/**
	 * @var string Controls the default access state and the order in which Allow and Deny are evaluated
	 */
	public $order;

	/**
	 * @var array Routes for forwarding to remote server
	 */
	public $allow;

	/**
	 * @var array Routes for working on local server
	 */
	public $deny;

	/**
	 * @var string Remote PM Auth Api url
	 */
	public $pm_auth_api_url = '/?c=PmAuthApi';

	/**
	 * @var object Proxy instance object
	 */
	protected $proxy;

	/**
	 * @var bool Replace some global options data
	 */
	protected $replace_options = false;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->init();
	}

	/**
	 * Additional init
	 */
	public function init(){
		$this->CFG =& load_class('Config', 'core');
		$this->CFG->load( 'proxy_queries', false, true );

		// Config not found
		if ( !isset( $this->CFG->config[ 'proxy_queries' ] ) ) {
			return;
		}
		// Proxy queries disabled?
		if ( !isset( $this->CFG->config['proxy_queries']['enable'] ) || $this->CFG->config['proxy_queries']['enable'] === false ) {
			return;
		}
		
		$this->proxy =& load_class( 'swProxyQueries', 'libraries', null );
		$this->IN =& load_class('Input', 'core');
		$this->RTR =& load_class('Router', 'core');

		$this->config = $this->CFG->config['proxy_queries']['settings'];
		$this->order = $this->CFG->config['proxy_queries']['order'];
		$this->allow = $this->CFG->config['proxy_queries']['allow'];
		$this->deny = $this->CFG->config['proxy_queries']['deny'];

		$this->_initialized = true;
	}

	/**
	 * Запуск
	 */
	public function run( $params='' ){
		if ( !$this->_initialized ) {
			return;
		}
		
		// Запрашиваемый контроллер
		$c = $this->RTR->fetch_class();
		// Запрашиваемый экшен
		$m = $this->RTR->fetch_method();
		// Запрашиваемая директория. Скрыта т.к. пока не используется.
		$d = $this->RTR->fetch_directory();

		// Auth?
		if ( $this->IN->get('method') == 'Logon' ) {
			$this->auth();
		}

		// Global options?
		if ( $c == 'Options' && $m == 'getGlobalOptions' ) {
			$this->replace_options = true;
		}

		$this->proxy->init( $this->config );

		// Порядок allow,deny
		// По умолчанию все запросы проксируются. Проверяется параметр
		// deny, в случае совпадения запрос отклоняется.
		// !!! Временно allow никак не влияет при этом параметре.

		// Порядок deny,allow
		// По умолчанию запросы выполняются на текущем сервере. Проверяется
		// параметр allow, в случае совпадения запрос перенаправляется.
		// !!! Временно deny никак не влияет при этом параметре.

		if ( $this->order == 'allow,deny' ) {
			// Флаг перенаправления запросов
			$forward = true;

			foreach( $this->deny as $route  ) {
				if ( !isset( $route['c'] ) || $route['c'] != $c ) {
					continue;
				}
				if ( isset( $route['m'] ) && $route['m'] != $m ) {
					continue;
				}

				$forward = false;
				break;
			}

		} elseif ( $this->order == 'deny,allow' ) {

			// Флаг перенаправления запросов
			$forward = false;

			foreach( $this->allow as $route  ) {
				if ( !isset( $route['c'] ) || $route['c'] != $c ) {
					continue;
				}
				if ( isset( $route['m'] ) && $route['m'] != $m ) {
					continue;
				}

				$forward = true;
				break;
			}
		}

		if ( $forward ) {
			$this->forward( $this->IN->server('REQUEST_URI') );
		}
	}

	/**
	 * Перенаправление запроса
	 *
	 * @param string $url URL
	 * @param bool $force_output Output or return result
	 */
	public function forward( $url ){
		// Retrive cookies
		$cookies = $this->proxy->restoreCookies();

		// Set cookies
		$this->proxy->setCookies( $cookies );

		// Run query
		$result = $this->proxy->forward( $url, false );

		if ( $this->replace_options ) {
			$result['body'] = $this->replaceSomeGlobalOptions( $result['body'] );
		}

		// Update cookies in session
		$this->proxy->rememberCookies( $result['cookies'] );

		// Update session from remote server
		$this->updateSessionFromRemote();

		// Output
		$this->proxy->outputResponseHeaders( $result['headers'], array( "Set-Cookie" ) );
		echo $result['body'];
		exit;
	}

	/**
	 * Authenticate
	 */
	public function auth(){

		//
		// Auth
		//

		$this->proxy->init( $this->config );
		$cookies = $this->proxy->restoreCookies();
		$this->proxy->setCookies( $cookies, false );
		$result = $this->proxy->forward( $this->IN->server('REQUEST_URI'), false );
		$this->proxy->rememberCookies( $result['cookies'] );


		//
		// Update some local session information
		//

		$this->updateSessionFromRemote();

		
		//
		// Output
		//

		$this->proxy->outputResponseHeaders( $result['headers'], array( "Set-Cookie" ) );
		echo $result['body'];
		exit;
	}

	/**
	 * Update current _SESSION data from remote server
	 *
	 * @return void
	 */
	public function updateSessionFromRemote(){
		$this->proxy->init( $this->config );
		$cookies = $this->proxy->restoreCookies();
		$this->proxy->setCookies( $cookies );
		$session = $this->proxy->forward( $this->pm_auth_api_url.'&m=getSession', false );

		// @todo Проверять заголовки на предмет сжатия gzip и разжимать только если найден заголовок
		$data = $this->proxy->gunzip( $session['body'] );
		$data = json_decode( $data, true );
		if ( !is_array( $data ) ) {
			return;
		}
		foreach( $data as $k => $v ) {
			$_SESSION[ $k ] = $v;
		}
	}

	/**
	 * Замена параметров при получении глобальных опций с удаленного сервера
	 *
	 * @param gzip $body Ответ сервера
	 * @return gzip
	 */
	public function replaceSomeGlobalOptions( $body ){
		// @todo Проверять что ответ упакован gzip
		$data = $this->proxy->gunzip( $body );
		$data = json_decode( $data );

		$repack = false;

		// Настройки NodeJS
		if ( isset( $data->globals->smp->NodeJSSocketConnectHost ) ) {
			require_once( APPPATH.'controllers/Options.php' );
			// Константы из promed.php доступны только если инициализивароть контроллер *facepalm*
			$options = new Options();
			$data->globals->smp->NodeJSSocketConnectHost = $options->getNodeJSSmpSocketConnectHost();
			$repack = true;
		}

		if ( $repack ) {
			$data = json_encode( $data );
			$body = $this->proxy->gzip( $data );
		}

		return $body;
	}

}
