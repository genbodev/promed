<?php
/**
* Pskov_Wialon_model - модель для работы с Wialon
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
*/

require_once(APPPATH.'models/_pgsql/Wialon_model.php');

class Pskov_Wialon_model extends Wialon_model {
	/**
	 * @var string Хост с изображениями
	 */
	//protected $_image_host = 'http://195.128.137.36:8022';
	protected $_image_host = WIALON_IMAGE_HOST;

	/**
	 * @var stting Wialon API URL
	 */
	//protected $_api_url = 'http://195.128.137.36:8026/ajax.html';
	protected $_api_url = WIALON_API_URL;

    /**
     * Конструктор
     */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Проверка авторизации в Виалоне
	 * метод является копией контроллера perm_Wialon
	 * @todo Вынести проверку авторизации в Виалоне из контроллеров в модель
	 */
	public function checkAuth(){
		$auth = false;
		if ( isset( $_SESSION['wialon']['user'] ) && isset( $_SESSION['wialon']['password'] ) ) {
			$this->_user = $_SESSION['wialon']['user'];
			$this->_password = $_SESSION['wialon']['password'];
			if ( $this->login() ) {
				$auth = true;
			} else {
				unset( $_SESSION['wialon']['user'], $_SESSION['wialon']['password'] );
			}
		}

		// Не удалось авторизоваться по данным из сессии?
		if ( !$auth ) {

			// Флаг авторизации через прокси
			$auth_through_proxy = false;

			// Получаем данные для авторизации
			// Если включено проксирование, то получаем данные из удаленного Промеда
			if ( $this->config->load('proxy_queries','proxy_queries',true) ) {
				// Прокисрование разрешено? А значит и настроено
				if ( $this->config->config['proxy_queries']['enable'] ) {
					$auth_through_proxy = true;

					$proxy =& load_class( 'swProxyQueries', 'libraries', null );
					$proxy->init( $this->config->config['proxy_queries']['settings'] );
					$proxy->setCookies( $proxy->restoreCookies() );
					$result = $proxy->forward( '/?c=Wialon&m=retriveAccessData', false );
					$proxy->rememberCookies( $result['cookies'] );

					$headers = explode( "\n", $result['headers'] );
					foreach( $headers as $header ) if ( strpos( $header, ":" ) !== false ) {
						list( $h, $v ) = explode( ':', $header );
						if ( $h == 'Content-Encoding' ) {
							if ( trim( $v ) == 'gzip' ) {
								$result['body'] = $proxy->gunzip( $result['body'] );
							}
						}
					}

					$result = (array)json_decode( $result['body'] );
					if ( $result ) {
						$this->_user = $result['MedService_WialonLogin'];
						$this->_password = $result['MedService_WialonPasswd'];
					}
				}
			}

			// Если данные для авторизации не были получены через прокси, сделаем запрос к базе
			if ( !$auth_through_proxy ) {
				$result = $this->retrieveAccessData( $_SESSION );
				$this->_user = $result['MedService_WialonLogin'];
				$this->_password = $result['MedService_WialonPasswd'];
			}

			if ( $this->login() ) {
				$_SESSION['wialon']['user'] = $this->_user;
				$_SESSION['wialon']['password'] = $this->_password;
				$_SESSION['wialon']['authorized'] = true;

			} else {
				$_SESSION['wialon']['authorized'] = false;
				if ($this->exit_on_auth_err) {
					return array( 'error' => 'Wialon: не удалось авторизоваться.' );
				}
			}
		} else {
			$_SESSION['wialon']['authorized'] = true;
		}
	}
}