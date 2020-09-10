<?php
//defined('BASEPATH') or die('No direct script access allowed');

// Подключение библиотеки для парсинга HTML страниц
load_class('simple_html_dom', 'libraries', null);

class SwServiceKZBot {
	const ERR_CURL_GET_URL = 100;
	const ERR_AUTH_OAUTH_ACCESS = 500;
	const ERR_AUTH_OAUTH_NO_HEADERS = 510;
	const ERR_AUTH_EMPTY = 0;

	/**
	 * @var string Логин
	 */
	protected $login;

	/**
	 * @var string Пароль
	 */
	protected $passwd;

	/**
	 * @var string Ключ сессии для хранения информации
	 */
	protected $session_key = 'KazakhProject';

	/**
	 * @var string Путь к файлу с cookie
	 */
	protected $cookie_jar;

	protected $rpnhost;
	protected $authost;
	protected $apiurl;
	protected $clientid;

	/**
	 * Настройки прокси сервера
	 */
	protected $proxy_host;
	protected $proxy_port;
	protected $proxy_login;
	protected $proxy_passwd;

	public $file_log;
	public $file_log_access;
	public $pmuser_login;

	/**
	 * Конструктор
	 */
	public function __construct($config){
		$this->rpnhost = $config['apihost'];
		$this->authost = $config['authhost'];
		$this->apiurl = $config['apiurl'];
		$this->login = $config['user'];
		$this->passwd = $config['password'];
		$this->clientid = $config['clientid'];

		$this->proxy_host = isset($config['proxy_host'])?$config['proxy_host']:null;
		$this->proxy_port = isset($config['proxy_port'])?$config['proxy_port']:null;
		$this->proxy_login = isset($config['proxy_login'])?$config['proxy_login']:null;
		$this->proxy_passwd = isset($config['proxy_password'])?$config['proxy_password']:null;

		$this->file_log = PROMED_LOGS.'ServiceKZBot_'.date('Y-m-d').'.log';
		$this->file_log_access = 'a';
		$this->pmuser_login = isset($_SESSION['login'])?$_SESSION['login']:null;
	}

	/**
	 * Запись в лог
	 */
	function writeLog($string) {
		$string = $this->pmuser_login.' '.date('d.m.Y H:i').': '.$string."\n";
		$f = fopen($this->file_log, $this->file_log_access);
		fputs($f, $string);
		fclose($f);
	}

	/**
	 * Отправка запроса через CURL
	 *
	 * @param string $url URL
	 * @param mixed $data Данные передаваемые в запросе массив, строка через & или null
	 * @param string $method Тип HTTP запроса GET, POST и тп
	 * @param mixed $cookie Путь к файлу для хранения и чтения cookie или null
	 * @param mixed $options Массив настроек curl или null
	 * @param int $retries Количество попыток установить соединение
	 * @return boolean В случае успеха true
	 * @throws Exception
	 */
	protected function CURL($url, $data = null, $method = 'GET', $cookie = null, $options = null, $retries = 3){
		if ((extension_loaded('curl') !== true) || (is_resource($curl = curl_init()) !== true)) {
			throw new Exception('Необходимо подключить расширение curl.');
		}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_TIMEOUT => 5,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_RETURNTRANSFER => true, // Возвращает HTML страницу и заголовки если запрос был удачным
			CURLOPT_HEADER => true,
			CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0",
			/*CURLOPT_PROXY => $this->proxy_host,
			CURLOPT_PROXYPORT => $this->proxy_port,
			CURLOPT_PROXYTYPE => 'HTTPS',
			CURLOPT_PROXYUSERPWD => $this->proxy_login . ':' . $this->proxy_passwd,*/
			CURLINFO_HEADER_OUT => true,
		));

		if ($this->proxy_host && $this->proxy_port) {
			curl_setopt($curl, CURLOPT_PROXY, $this->proxy_host.':'.$this->proxy_port);
			curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			if ($this->proxy_login && $this->proxy_passwd) {
				curl_setopt($curl, CURLOPT_PROXYUSERPWD,   $this->proxy_login.':'.$this->proxy_passwd);
			}
		}

		//curl_setopt($curl, CURLOPT_FAILONERROR, true);
		//curl_setopt($curl, CURLOPT_AUTOREFERER, true);

		if (!preg_match('#^(?:DELETE|GET|HEAD|OPTIONS|POST|PUT)$#i', $method)) {
			return false;
		}

		if (preg_match('#^(?:HEAD|OPTIONS)$#i', $method) > 0) {
			curl_setopt_array($curl, array(
				CURLOPT_NOBODY => true
			));

			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		} else if (preg_match('#^(?:POST|PUT)$#i', $method) > 0) {
			if (is_array($data) === true) {
				/*
				Закомментировал иначе не работает отправка файла
				foreach (preg_grep('#^@#', $data) as $key => $value) {
					$data[$key] = sprintf('@%s', rtrim(str_replace('\\', '/', realpath(ltrim($value, '@'))), '/') . (is_dir(ltrim($value, '@')) ? '/' : ''));
				}

				$data = http_build_query($data, '', '&');
				*/
			}

			if (preg_match('#^(?:POST)$#i', $method) > 0) {
				curl_setopt($curl, CURLOPT_POST, true);
			} else {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
			}

			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		} else {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		}

		if (isset($cookie) === true) {
			curl_setopt_array($curl, array_fill_keys(array(CURLOPT_COOKIEJAR, CURLOPT_COOKIEFILE), strval($cookie)));
		}

		if ((intval(ini_get('safe_mode')) == 0) && (ini_set('open_basedir', null) !== false)) {
			curl_setopt_array($curl, array(CURLOPT_MAXREDIRS => 5, CURLOPT_FOLLOWLOCATION => true));
		}

		if (is_array($options) === true) {
			curl_setopt_array($curl, $options);
		}

		$result = null;

		for ($i = 1; $i <= $retries; ++$i) {
			session_write_close(); // Bag with no sending PHPSESSID (@link http://stackoverflow.com/questions/15627217/curl-not-passing-phpsessid)
			$result = curl_exec($curl);
			session_start();

			$this->writeLog(print_r(array(
				'try' => $i,
				'url' => $url,
				'data' => $data,
				'method' => $method,
				'cookie' => $cookie,
				'options' => $options,
				'retries' => $retries,
				'session' => isset($_SESSION[$this->session_key])?$_SESSION[$this->session_key]:null,
				'result' => $result
			), true));

			if ($i == $retries || !empty($result)) {
				break;
			}

			usleep(pow(2, $i - 2) * 1000000);
		}

		//if ($result === false) {
		//	throw new Exception(curl_error($curl), curl_errno($curl));
		//}

		$info = curl_getinfo($curl);

		curl_close($curl);

		if (empty($result)) {
			return false;
		}

		return array(
			'data' => $result,
			'info' => $info,
		);
	}

	/**
	 * @return string Путь к файлу с cookie
	 */
	protected function getCookieJar(){
		if ($this->cookie_jar === null) {
			$this->cookie_jar = strval(tempnam('/tmp', 'cookie'));
		}

		return $this->cookie_jar;
	}

	/**
	 * @return string Сессионные cookie
	 */
	protected function readCookieSession(){
		if (!isset($_SESSION[$this->session_key]['cookie'])) {
			return '';
		}

		return $_SESSION[$this->session_key]['cookie'];
	}

	/**
	 * Записывает сессионные cookie
	 *
	 * @param string $cookie Сессионные cookie
	 */
	protected function writeCookieSession($cookie){
		$_SESSION[$this->session_key]['cookie'] = $cookie;
	}

	/**
	 * Сбрасывает сессионные cookie
	 */
	protected function resetCookieSession(){
		$_SESSION[$this->session_key]['cookie'] = '';
	}

	/**
	 * @param string $key Ключ
	 * @return string данные oauth сессии
	 */
	protected function readOauthSession($key){
		if (!isset($_SESSION[$this->session_key]['oauth'][$key])) {
			return '';
		}

		return $_SESSION[$this->session_key]['oauth'][$key];
	}

	/**
	 * Записывает данные oauth сессии
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function writeOauthSession($key, $value){
		$_SESSION[$this->session_key]['oauth'][$key] = $value;
	}

	/**
	 * Читает данные oauth сессии
	 */
	protected function resetOauthSession(){
		$_SESSION[$this->session_key]['oauth'] = array();
	}

	/**
	 * Обновляет cookie в файле
	 *
	 * @param string $cookie_jar
	 * @param string $cookie
	 */
	protected function updateCookieJar($cookie_jar, $cookie){
		$fo = fopen($cookie_jar, 'w+');
		fputs($fo, $cookie);
		fclose($fo);
	}

	/**
	 * Возвращает заголовок из результата запроса CURL
	 *
	 * @param array $data Ответ сервера
	 * @param array $info
	 * @param bool $only_last
	 * @return string
	 */
	protected function retriveResponseHeaders($data, $info, $only_last = true){
		$headers = substr($data, 0, $info['header_size']);
		if (strlen($headers) > 0 && strlen($headers) == $info['header_size']) {
			if (substr_count($headers, "\r\n\r\n") > 1) {
				$headers = explode("\r\n\r\n", $headers, -1);
				if ($only_last) {
					$headers = $headers[sizeof($headers) - 1] . "\r\n\r\n";
				}
			} else {
				$headers = array($headers);
			}
		}

		return $headers;
	}

	/**
	 * Возвращает тело ответа из результата запроса CURL
	 *
	 * @param array $data Ответ сервера
	 * @param array $info
	 * @param bool $force_json Декодировать JSON ответ?
	 * @return mixed
	 */
	protected function retriveResponseBody($data, $info, $force_json=false){
		$body = $info["size_download"] ? substr($data, $info["header_size"], $info["size_download"]) : "";
		if ( $force_json ) {
			$body = json_decode( $body, true );
		}
		return $body;
	}

	/**
	 * Авторизация на сайте eisz.kz
	 */
	protected function auth(){
		// Подключание
		// Перед отправкой данных формы необходимо получить токен из cookie
		// и токен в скрытом поле формы
		$result = $this->CURL($this->authost.'/login', null, 'GET', $this->getCookieJar());

		if (!$result) {
			$this->resetCookieSession();
			$this->resetOauthSession();
			throw new Exception('Не удалось получить страницу авторизации.');
		}

		// Обновляем cookie в сессии
		$this->writeCookieSession(file_get_contents($this->getCookieJar()));

		extract($result, EXTR_OVERWRITE);
		$body = $this->retriveResponseBody($data, $info);

		// Получение токена из скрытого поля формы
		$html = str_get_html($body);
		$login_box = $html->find('div[id=login-box] input[name=__RequestVerificationToken]');
		$form_token = $login_box[0]->attr['value'];

		// Аутентификация
		$result = $this->CURL(
			$this->authost.'/login',
			http_build_query(array(
				'__RequestVerificationToken' => $form_token,
				'UserName' => $this->login,
				'Password' => $this->passwd,
				'RememberMe' => 'on',
			), '', '&'),
			'POST',
			$this->getCookieJar(),
			array(
				//CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_REFERER => $this->authost.'/login',
				CURLOPT_ENCODING => 'gzip,deflate'
			)
		);

		if (!$result) {
			$this->resetCookieSession();
			$this->resetOauthSession();
			throw new Exception('Не удалось удалось выполнить аутентификацию.');
		}
		$this->writeCookieSession(file_get_contents($this->getCookieJar()));

		extract($result, EXTR_OVERWRITE);
		$body = $this->retriveResponseBody($data, $info);


		//
		// Проверка авторизации - снова загрузилась страница авторизации
		//

		$html = str_get_html($body);
		$title = $html->find('title');
		if ($title[0]->innertext() == 'Выполнить вход') {
			return false;
		}


		//
		// Дополнительная авторизация HTTP Basic auth для работы с РПН
		// 
		// В JS файле https://rpn.eisz.kz/bundles/app идет отправка к серверу
		// авторизации если не задан токен
		// 
		// Пример строки генерирующий URL
		// var t = urlEta + "/oauth/authorize?client_id=" + encodeURIComponent(EtaClientId) + "&redirect_uri=" + encodeURIComponent(window.location.href) + "&response_type=" + encodeURIComponent("token") + "&state=" + encodeURIComponent(n) + "&scope=" + encodeURIComponent("profile");
		//

		$result = $this->CURL(
			$this->authost.'/oauth/authorize?' . http_build_query(array(
				'client_id' => $this->clientid,
				'redirect_uri' => $this->rpnhost,
				'response_type' => 'token',
				'state' => '',
				'scope' => 'profile'
			)),
			null,
			'GET',
			$this->getCookieJar(),
			array(
				CURLOPT_FOLLOWLOCATION => true
			)
		);
		if ( !$result ) {
			return static::ERR_AUTH_OAUTH_ACCESS;
		}

		$headers = $this->retriveResponseHeaders($result['data'], $result['info'], false);
		if ( !$headers ) {
			return static::ERR_AUTH_OAUTH_NO_HEADERS;
		}

		// Надо достать Location: https://rpn.eisz.kz/#access_token=
		foreach ($headers as $h) {
			if (!strpos($h, '#access_token')) {
				continue;
			}
			$v_list = explode("\n", $h);
			foreach ($v_list as $z) {
				if (preg_match('#^Location: (.*)#i', $z, $m)) {
					$match_list = explode('&', parse_url($m[1], PHP_URL_FRAGMENT));
					foreach ($match_list as $l) {
						list($k, $v) = explode('=', $l);
						$this->writeOauthSession($k, $v);
					}
					break 2;
				}
			}
		}

		if (!$this->readOauthSession('access_token')) {
			return false;
		}

		return true;
	}

	protected function deleteHeader($header, &$header_list) {
		$cnt = count($header_list);
		for($i = 0; $i < $cnt; $i++) {
			if (strpos($header_list[$i], $header.':') === 0) {
				unset($header_list[$i]);
			}
		}
	}

	/**
	 * Отправка запроса через CURL
	 * Отличается от метода {CURL} тем, что проверяет ответ на необходимость
	 * авторизации, а так же работает с дефолтным файлом cookie. И в случае
	 * такой необходимости, пытается авторизоваться и повторить запрос снова.
	 *
	 * @param string $url URL
	 * @param mixed $data Данные передаваемые в запросе массив, строка через & или null
	 * @param string $method Тип HTTP запроса GET, POST и тп
	 * @param mixed $options Массив настроек curl или null
	 * @param bool $retries Количество попыток установить соединение
	 * @param bool $add_oauth_headers Добавить в запрос данные авторизации (для запросов XmlHttpRequest)
	 * @return mixed Результат или false
	 * @throws Exception
	 */
	protected function activeCURL($url, $data = null, $method = 'GET', $options = null, $retries = 3, $add_oauth_headers = false){
		// Обновим файл с cookie для запросов
		$this->updateCookieJar($this->getCookieJar(), $this->readCookieSession());

		// Запрос требует дополнительного заголовка для авторизации oauth?
		$run_query = true;
		if ($add_oauth_headers) {
			$token_type = $this->readOauthSession('token_type');
			$access_token = $this->readOauthSession('access_token');
			if (!empty($access_token)) {
				$options[CURLOPT_HTTPHEADER][] = "Authorization: {$token_type} {$access_token}";
			} else {
				$run_query = false;
			}
		}

		// Выполняем запрос если нет
		if ( $run_query ) {
			$result = $this->CURL($url, $data, $method, $this->getCookieJar(), $options, $retries);
			if ($result === false) {
				return false;
			}
		}


		//
		// Проверка на необходимость авторизации
		//

		$need_auth = ($run_query == false);
		// Авторизация для oauth не требутеся? Тогда проверим что нам не вернули страницу авторизации
		if (!$need_auth) {
			$body = $this->retriveResponseBody($result['data'], $result['info']);

			if (!empty($body)) {
				// Проверяем ответ на JSON формат
				$body_json = json_decode($body);
				if ( $body_json !== null ) {
					if ( is_object($body_json) && property_exists($body_json, 'Message') && $body_json->Message == 'Для этого запроса отказано в авторизации.' ) {
						$need_auth = true;
					}
				} else {
					$html = str_get_html($body);
					$title = $html->find('title');
					if (isset($title[0]) && $title[0]->innertext() == 'Выполнить вход') {
						$need_auth = true;
					}
				}
			}
		}

		if ($need_auth) {
			$auth_result = $this->auth();
			if ($auth_result !== true) {
				switch($auth_result){
					case static::ERR_AUTH_OAUTH_ACCESS:
						throw new Exception('Не удалось обратиться к серверу авторизации oauth.');
						break;

					case static::ERR_AUTH_OAUTH_NO_HEADERS:
						throw new Exception('Не удалось получить заголовок для извлечения access_token.');
						break;

					default:
						throw new Exception('Не удалось авторизоваться.');
						break;
				}
			}

			if ($add_oauth_headers) {
				if (isset($options[CURLOPT_HTTPHEADER])) {
					$this->deleteHeader('Authorization', $options[CURLOPT_HTTPHEADER]);
				}
				$options[CURLOPT_HTTPHEADER][] = 'Authorization: '.$this->readOauthSession('token_type').' '.$this->readOauthSession('access_token');
			}

			$result = $this->CURL($url, $data, $method, $this->getCookieJar(), $options, $retries);
		}

		// Обновление cookie после выполнения запроса
		if ($result !== false) {
			$this->writeCookieSession(file_get_contents($this->getCookieJar()));
		}

		return $result;
	}

	/**
	 * Поиск человека по указанному $inn
	 *
	 * @param string $inn ИНН
	 * @return array|bool array или false если результат пустой или более 1
	 * @throws Exception
	 */
	protected function getPersonByInn($inn){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/person?' . http_build_query(array(
				'fioiin' => $inn,
				'page' => 1,
				'pagesize' => 5,
				'_' => time()
			)),
			null,
			'GET',
			null,
			3,
			true
		);

		$body = $this->retriveResponseBody($result['data'], $result['info'], true);
		if ( !is_array( $body ) || sizeof( $body ) != 1 ) {
			return false;
		}

		//Array
		//(
		//    [0] => Array
		//        (
		//            [$id] => 1
		//            [deathDate] => 
		//            [isExistsOpenRequest] => 
		//            [isExistsSomeActiveAttachment] => 
		//            [hGBD] => 190001001
		//            [activeAttachment] => 
		//            [parentId] => 
		//            [somePersonDataForDuplicate] => 
		//            [isExistsDataOfDeath] => 
		//            [OpenRequestAttach] => 
		//            [OpenRequestCA] => 
		//            [ConfirmRequestCA] => 
		//            [LastAttachmentSV] => 
		//            [PersonID] => 15300000017227900
		//            [lastName] => АБДЕЛЬКАДЕР АЛИ
		//            [firstName] => МОХАМЕД ГАМАЛЬ АХМЕД
		//            [secondName] => 
		//            [birthDate] => 1985-12-04T00:00:00
		//            [iin] => 851204399146
		//            [sex] => 3
		//            [national] => 310013145
		//            [citizen] => 1001
		//        )
		//
		//)
		// Т.к. мы ищем только по ИИН, то результат будет всегда один
		return $body[0];
	}

	/**
	 * Поиск человека
	 */
	public function search(){
		$demo_inn = '851204399146';
		$person = $this->getPersonByInn($demo_inn);
		if (!$person) {
			throw new Exception('Не удалось найти человека по указанному ИНН ' . $demo_inn . '.');
		}

		echo'<pre>';
		var_dump((array)$person);
	}

	/**
	 * Очистка данных в сессии
	 */
	public function clearSession(){
		$this->resetCookieSession();
		$this->resetOauthSession();
		echo 'Session and oAuth cookie is clear.';
	}

	/**
	 * Выводит данные сессии
	 */
	public function readSession(){
		echo '<pre>';
		echo 'Session data: '."\n\n";
		var_dump($_SESSION[$this->session_key]);
		echo '</pre>';
	}

	/**
	 * @return string Номер запроса на прикрепление
	 */
	protected function getRequestNum(){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/attachment/requests/requestnum',
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			),
			3,
			true
		);

		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Возвращает данные по участкам прикрепленления для указнной оргнизации
	 *
	 * @param int $org
	 * @return type
	 */
	protected function getTerritoryServiceList($org_id){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/territory/org/'.$org_id,
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			),
			3,
			true
		);

		//Array
		//(
		//	[0] => Array
		//		(
		//			[$id] => 1
		//			[TerritoryServiceID] => 3594
		//			[territotyServiceNumber] => 1
		//			[orgHealthCareID] => 146
		//			[territotyServiceProfileID] => 5
		//			[actualDoctorID] => 404326806
		//			[doctors] => 
		//			[addreses] => 
		//			[endDate] => 
		//			[territoryDescription] => 
		//			[actualDoctor] => Array
		//				(
		//					[$id] => 2
		//					[DoctorID] => 112504
		//					[PersonID] => 404326806
		//					[TerritoryServiceID] => 3594
		//					[iin] => 
		//					[doctorFio] => ЖАЛМУРЗИЕВА ГУЛЬФАРИДА ЖАНГАВЫЛОВНА
		//					[staffTypeID] => 4
		//					[staffPostID] => 1
		//					[occupiedRates] => 1
		//					[beginDate] => 2015-07-09T17:59:53.143
		//					[endDate] => 
		//				)
		//
		//			[beginDate] => 2011-02-17T12:00:46.203
		//			[isClosing] => 
		//		)
		//	[1] => Array
		//	...

		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Возвращает данные учетной записи
	 *
	 * @return array
	 */
	protected function getAccountUserData(){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/Account/UserData',
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"X-Requested-With: XMLHttpRequest"
				),
				CURLOPT_TIMEOUT => 20 // Бывают задержки в получении этого запроса
			),
			3,
			true
		);

		//Array
		//(
		//    [$id] => 1
		//    [Id] => 29023
		//    [UserName] => 820914401629
		//    [Roles] => Array
		//        (
		//            [0] => Registrator
		//            [1] => AddPersonAddress
		//            [2] => EditPerson
		//            [3] => AccountGroup
		//            [4] => Certificates
		//            [5] => Diagnoses
		//        )
		//
		//    [Claims] => Array
		//        (
		//            [0] => Array
		//                (
		//                    [$id] => 2
		//                    [ClaimType] => Организация
		//                    [ClaimValue] => 146
		//                )
		//
		//        )
		//
		//    [Urls] => Array
		//        (
		//            [0] => Array
		//                (
		//                    [$id] => 3
		//                    [Id] => 1
		//                    [Name] => ЕТА
		//                    [Url] => https://www.eisz.kz
		//                )
		//
		//        )
		//
		//)		
		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Возвращает данные организации относящейся к учетной записи
	 *
	 * @param int $org_id
	 * @return array
	 */
	protected function getAccountOrgRpn($org_id){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/managemet/GetUserOrgRpn/'.$org_id,
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"X-Requested-With: XMLHttpRequest"
				),
			),
			3,
			true
		);
		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Возвращает список адресов человека
	 *
	 * @param number $person_id
	 * @return array
	 */
	protected function getPersonAddresses($person_id){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/person/'.$person_id.'/addresses',
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"X-Requested-With: XMLHttpRequest"
				)
			),
			3,
			true
		);

		//Array
		//(
		//	[0] => Array
		//		(
		//			[$id] => 1
		//			[PAddressID] => 45428525
		//			[personID] => 15300000002127956
		//			[addressTypeID] => 2
		//			[apartmentID] => 1945792
		//			[buildingID] => 
		//			[isMain] => 1
		//			[beginDate] => 2013-05-27T01:00:00
		//			[endDate] => 
		//			[addressString] => РЕСПУБЛИКА: Казахстан , ОБЛАСТЬ: Западно-казахстанская , ГОРОД ОБЛ.ЗНАЧ.: Уральск , УЛИЦА: Жукова , ДОМ: 2, КВАРТИРА: 4
		//			[addressStringKz] => : Қазақстан , : Батыс Қазақстан , : Орал Қ. , : Жуков , ҮЙ: 2, ПӘТЕР: 4
		//			[IsConfirmed] => 1
		//			[adr_s_buildingId] => 
		//			[adr_s_pbId] => 
		//			[countryLevelID] => 100396
		//			[kato] => 270000000
		//		)
		//
		//)
		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	protected function getCurlFileValue($url, $filename, $contentType = '')
	{
		// PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
		// See: https://wiki.php.net/rfc/curl-file-upload
		if (function_exists('curl_file_create')) {
			return curl_file_create($url, $contentType, $filename);
		}

		// Use the old style if using an older version of PHP
		$value = "@{$url};filename={$filename}";
		if (!empty($contentType)) {
			$value .= ";type={$contentType}";
		}

		return $value;
	}


	/**
	 * Прикрепление пользователя по указанному ИНН
	 */
	public function attachPerson($attachment){

		$org = $this->getAccountOrgRpn($attachment['Org_id']);
		if (empty($org)) {
			throw new Exception('Не удалось получить данные организации.');
		}

		// Номер запроса на прикрепление
		$num = $this->getRequestNum();
		$num_json = json_decode($num, true);
		if (isset($num_json['message'])) {
			throw new Exception('Не удалось получить номер запроса на прикрепление: '.$num_json['message']);
		} else if (!$num) {
			throw new Exception('Не удалось получить номер запроса на прикрепление.');
		}
		$this->writeLog('Получен номер: '.$num);

		// Загрузка сканов документов и получение их идентификаторов
		if (empty($attachment['files'])) {
			throw new Exception("Необходимо укзазать файлы документов для прикрепления.");
		}
		$files = array();
		$filesize = 0;
		$i = 0;
		foreach($attachment['files'] as $item){
			if (!file_exists($item['url'])) {
				throw new Exception('Не найден файл "'.basename($item['url']).'"');
			}
			$file = realpath($item['url']);
			$filesize += filesize($file);
			$files['file'.$i] = $this->getCurlFileValue($file, $item['filename']);
			$i++;
		}

		$file_result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/UploadFile/saveFile/'.$attachment['iin'],
			$files,
			'POST',
			null,
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: multipart/form-data",
					"X-Requested-Width: XMLHttpRequest",
				),
				CURLOPT_INFILESIZE => $filesize,
				CURLOPT_BUFFERSIZE => 128,
				CURLOPT_NOPROGRESS => 0
			),
			3,
			true
		);

		$file_ids = array();
		$file_ids = $this->retriveResponseBody($file_result['data'], $file_result['info'], true);
		if (empty($file_ids)) {
			throw new Exception("Ошибка загрузки сканов документов.");
		}

		// Прикрепление
		$attachmentFiles = array();
		foreach ($file_ids as $v) {
			$attachmentFiles[] = array(
				'id' => $v
			);
		}

		$beginDate = new DateTime($attachment['beginDate']);

		$attachment_post = array(
			"attachmentProfile" => $attachment['attachmentProfile'],
			"PersonID" => $attachment['PersonID'],
			"attachmentStatus" => '1', // Статус: запрос на прикрепление
			"beginDate" => $beginDate->format("Y-m-d\TH:i:s\.u\Z"), //бессмысленно, в РПН всегда проставляется текущая дата
			"orgHealthCare" => array(
				"id" => $org['id'],
				"name" => $org['name'],
				"originalId" => $org['originalId']
			),
			"careAtHome" => $attachment['careAtHome'],
			"causeOfAttach" => $attachment['causeOfAttach'],
			"personAddressesID" => $attachment['personAddressesID'],
			"territoryServiceID" => $attachment['territoryServiceID'],
			"doctorID" => /*$attachment['doctorID']*/null,
			"Num" => $num,
			"attachmentFiles" => $attachmentFiles
		);

		//print_r($attachment_post);

		$this->writeLog('start attachment');
		$attachment_result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/attachment',
			json_encode($attachment_post),
			'POST',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
					"X-Requested-With: XMLHttpRequest"
				)
			),
			3,
			true
		);

		$attachment = $this->retriveResponseBody($attachment_result['data'], $attachment_result['info']);
		$attachment_json = json_decode($attachment, true);

		if (isset($attachment_json['message'])) {
			throw new Exception("Не удалось выполнить прикрепление. Ответ сервиса: {$attachment_json['message']}");
		} else if (empty($attachment)) {
			throw new Exception("Запрос на прикрепление не вернул результат.");
		}
		//$this->textlog->add("Результат прикрепления:\n" . var_export($attachment, true));

		// @todo Сделать проверку ошибок в результатах по такому ответу
		//array (
		//  '$id' => '1',
		//  'Message' => 'Запрос недопустим.',
		//  'ModelState' => 
		//  array (
		//    '$id' => '2',
		//    'attachment.attachmentFiles' => 
		//    array (
		//      0 => 'Cannot deserialize the current JSON object (e.g. {"name":"value"}) into type \'System.Collections.Generic.IEnumerable`1[RPN.WebApi.Model.Files]\' because the type requires a JSON array (e.g. [1,2,3]) to deserialize correctly.
		//To fix this error either change the JSON to a JSON array (e.g. [1,2,3]) or change the deserialized type so that it is a normal .NET type (e.g. not a primitive type like integer, not a collection type like an array or List<T>) that can be deserialized from a JSON object. JsonObjectAttribute can also be added to the type to force it to deserialize from a JSON object.
		//Path \'attachmentFiles\', line 1, position 1335.',
		//    ),
		//  ),
		//)

		return array(
			'id' => $attachment,
			'num' => $num
		);
	}

	/**
	 * Получение номера свидетельства о рождении из сервиса РПН
	 */
	protected function getBirthNum(){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/birth/nextnum',
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			),
			3,
			true
		);

		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Получение номера для формы 2009у из сервиса РПН
	 */
	protected function getForma2009yNum(){
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/birth/forma2009y/nextnumdoc',
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			),
			3,
			true
		);

		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Проверка существования передаваемого свидетельства в сервисе РПН
	 */
	protected function isExistsBirth($birth) {
		$params = array(
			'CharBirth' => $birth['CharBirth'],
			'DateBirth' => $birth['DateBirth'],
			'MotherId' => $birth['motherPerson']['PersonID'],
			'NameChild' => $birth['flPerson']['firstName'],
			'SexId' => $birth['Sex'],
		);

		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/birth/isexistsbirth',
			json_encode($params),
			'POST',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
					"X-Requested-With: XMLHttpRequest"
				)
			),
			3,
			true
		);

		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Получение текущего времени и даты из сервиса РПН
	 */
	protected function getCurrentDateTime() {
		$result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/attachment/time',
			null,
			'GET',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			),
			3,
			true
		);

		return $this->retriveResponseBody($result['data'], $result['info'], true);
	}

	/**
	 * Передача свидетельства о рождении в сервис РПН
	 */
	public function sendBirth($birth) {
		$num = $this->getBirthNum();
		$num_json = json_decode($num, true);
		if (isset($num_json['Message'])) {
			throw new Exception('Не удалось получить номер свидетельства: '.$num_json['Message']);
		} else if (!$num) {
			throw new Exception('Не удалось получить номер свидетельства.');
		}
		$birth['Num'] = $num;

		$numdoc = $this->getForma2009yNum();
		$numdoc_json = json_decode($numdoc, true);
		if (isset($numdoc_json['Message'])) {
			throw new Exception('Не удалось получить номер для формы 2009у: '.$num_json['Message']);
		} else if (empty($numdoc)) {
			throw new Exception('Не удалось получить номер для формы 2009у.');
		}
		$birth['form2009y']['NumberDoc'] = $numdoc;

		$dt = $this->getCurrentDateTime();
		$dt_json = json_decode($dt, true);
		if (isset($dt_json['Message'])) {
			throw new Exception('Не удалось получить текущую дату: '.$dt_json['Message']);
		} else if (empty($numdoc)) {
			throw new Exception('Не удалось получить текущую дату.');
		}
		$birth['Dt'] = $dt;
		$birth['form2009y']['RegDate'] = $dt;

		$isexists = $this->isExistsBirth($birth);
		$isexists_json = json_decode($isexists, true);
		if (isset($isexists_json['Message'])) {
			throw new Exception('Ошибка при проверке существования свидетельства о рождении в РПН: '.$isexists_json['Message']);
		} else if (!is_bool($isexists) && empty($isexists)) {
			throw new Exception('Ошибка при проверке существования свидетельства о рождении в РПН.');
		} else if ($isexists === true) {
			throw new Exception('На указанное физическое лицо уже было зарегистрировано свидетельство о рождении.');
		}

		//print_r($birth);exit;
		$birth_result = $this->activeCURL(
			$this->rpnhost.$this->apiurl.'/birth',
			json_encode($birth),
			'POST',
			array(
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
					"X-Requested-With: XMLHttpRequest"
				)
			),
			3,
			true
		);

		$response = $this->retriveResponseBody($birth_result['data'], $birth_result['info']);
		$response_json = json_decode($response, true);

		if (isset($response_json['Message'])) {
			throw new Exception("Не удалось передать свидетельсво о рождении. Ответ сервиса: {$response_json['Message']}");
		} else if (empty($response)) {
			throw new Exception("Запрос на передачу свидетельсьва о рождении не вернул результат.");
		}

		return $response_json;
	}
}
