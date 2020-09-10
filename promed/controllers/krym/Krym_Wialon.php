<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/Wialon.php');


class Krym_Wialon extends Wialon {

	/**
	 * @var string Хост с изображениями
	 */
	//protected $_image_host = 'http://wialon.03.perm.ru:8022';
	//protected $_image_host = WIALON_IMAGE_HOST;
	
	/**
	 * @var stting Wialon API URL
	 */
	//protected $_api_url = 'http://wialonweb.promedweb.ru/wialon/ajax.html';
	//protected $_api_url = WIALON_API_URL;
	/**
	 * @var string Ключ сессии
	 */
	protected $_session_key = 'sid';

	/**
	 * Авторизация
	 * 
	 * @return output JSON
	 */
	public function login($credentials = array()){
		
		//		$data = array(
		//			'svc' => 'token/login',
		//			'params' => json_encode( array(
		//				//'user' => $this->_user,
		//				//'password' => $this->_password
		//				'token' => '3646e3d33894e4b8e370d13ddd327dbf92265F84FD99B84074DC465D728EF69A25C7C919'
		//				//'token' => '3646e3d33894e4b8e370d13ddd327dbfAAFF8CDD89389EC8D30206D0CA4BDB04065CDE3C'
		//				//'token' => '3646e3d33894e4b8e370d13ddd327dbf1339EA4EF62269D0246100B7DA0B60049180CE38'
		//			) )
		//		);
		//
		/*
		запрос на регистрацию нового токена
		$data = array(
			'svc' => 'token/update',
			'params' => json_encode( array(
				'callMode' =>'create',
				'app' => 'РМИАС',
				'userId' => '41',
				'at' => 0,
				'dur' => 0,
				'fl' => 0x200,
				'p' => "{}",
				'items' => array()
			) )
		);*/
		//		$result = json_decode( $this->_httpQuery( $data ) );
		//		//var_dump($result);exit;
		//		if (empty($result) || array_key_exists( 'error', $result ) ) {
		//			return false;
		//		} else {
		//			$_SESSION['wialon'][$this->_session_key] = (string) $result->eid;
		//			return true;
		//		}

		$this->dbmodel->login($credentials);
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
				$result = $this->retriveAccessData( false );
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
					$this->ReturnData( array( 'error' => $this->outputMessage( 'Wialon: не удалось авторизоваться.' ) ) );
					exit;
				}
			}
		} else {
			$_SESSION['wialon']['authorized'] = true;
		}
	}

}