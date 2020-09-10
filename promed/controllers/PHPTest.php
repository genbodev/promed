<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PHPTest - контроллер для запуска тестов к серверной части PHP
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Services
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Ivan Petukhov aka Lich (ethereallich@gmail.com)
* @version      23.12.2010
*/


class PHPTest extends swController {

	var $NeedCheckLogin = false; // проверка на логин не нужна
	var $host = "192.168.136.20";
	var $port = "80";
	var $PHSESSID = NULL;
	var $Login = "admin";
	var $Password = "AMoKK";

	/**
	 * PHPTest constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * @return bool
	 */
	public function index() {
		$this->load->model('PHPTest_model', 'Testmodel');
		$TestCase = $this->Testmodel->getTestCase(
			array(
				//'Controller' => 'LpuStructure'
			)
		);
		//Выполняем до победного
		set_time_limit(0);
		//@ini_set('zlib.output_compression', 0);
		//@ini_set('implicit_flush', 1);
		session_write_close();
		
		if ( !$this->Login() ) {
			echo 'К сожалению не удалось залогиниться в систему для проведения тестов :(';
			return false;
		}
		ob_start();
		$time_start = list($sm, $ss) = explode(' ', microtime());
		$Results = array();
		$success = 0;
		$failed = 0;
		foreach($TestCase as $Test) {
			if ($this->RunTest($Test) ) {
				$success++;
			} else {
				$failed++;
			}
		}
		$time_end = list($em, $es) = explode(' ', microtime());
		echo "<h2>Всего выполнено ".($success + $failed)." шагов (".$success." завершились успешно и ".$failed." неуспешно)<br/>
			Итоговое время выполнения : ".(($em + $es) - ($sm + $ss))." сек.</h2>";
	}
	
	
	/**
	 * Авторизуется в системе и запоминает Cookie PHPSESSID для последующего использования
	 */
	function Login() {
		//Контроллер логина
		$controller = "?c=main&m=index&method=Logon&login=admin";
		//Логин и пароль под которым заходим
		$params = "login=".$this->Login."&psw=".$this->Password;
		$packet = "";
		$packet .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$packet .= "Cookie: NoRecord=true;\r\n";
		
		// Создать контекст и инициализировать POST запрос
		$context = stream_context_create(
			array(
				'http' => array(
					'method' => 'POST',
					'header' => $packet . PHP_EOL,
					'content' => $params,
				),
			)
		);

		// Отправить запрос
		$res = file_get_contents(
			$file = "http://".$this->host.":".$this->port."/".$controller,
			$use_include_path = false,
			$context);
		if ($res == '{"success":true}' ) { // Авторизация прошла успешно!
			foreach($http_response_header as $rec) {
				// Если нашли строку с идентификатором сессии то радуемся и выходим.
				if ( $p = strpos($rec, 'Set-Cookie: PHPSESSID=') !== false) {
					$this->PHSESSID = substr($rec, 22, strpos($rec, ';') - 22);
					return true;
				}
			}
			//Строка с Set-Cookie: PHPSESSID не нашлась, очень странно, но видать нас не авторизовало
			return false;
		} else { // Авторизация была неуспешной :(
			return false;
		}
	}
	
	
	/**
	 * Запуск теста и печать результатов его работы
	 */
	function RunTest($Test) {
		echo "<b>Вызов:</b> ".$Test['QueryString']." с параметрами ".urldecode($Test['Post'])."<br/>\r\n";
		ob_flush();
		//flush();
		$res = $this->SendHTTPRequest('/?'.$Test['QueryString'], ToUTF(urldecode($Test['Post'])));
		$json = json_decode($res[0], true);
		if ( isset($json) ) {
			echo "Тест <font color=green>пройден успешно</font><br/>\r\n";
			echo "Время выполнения: {$res[1]} сек.<br/>\r\n";
			$ret = true;
		} else {
			echo "Тест <font color=red>провален</font><br/>\r\n";
			echo "Время выполнения: {$res[1]} сек.<br/>\r\n";
			echo "Строка ответа: <br/>\r\n<pre>".$res[0]."</pre><br/>\r\n";
			$ret = false;
		}
		echo "<br/>\r\n";
		ob_flush();
		//flush();
		return $ret;
	}
	
	/**
	 * Посылает HTTP запрос с POST параметрами, возвращает результат
	 *
	 * @param String $controller Путь к контроллеру
	 * @param $params Строка POST запроса
	 *
	 * @return String Ответ сервера на запрос
	 */
	function SendHTTPRequest($controller, $params) {
		$packet = "";
		$packet .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$packet .= "Cookie: PHPSESSID=".$this->PHSESSID."; NoRecord=true;\r\n";
		
		// Создать контекст и инициализировать POST запрос
		$context = stream_context_create(
			array(
				'http' => array(
					'method' => 'POST',
					'header' => $packet . PHP_EOL,
					'content' => $params,
				),
			)
		);

		$time_start = list($sm, $ss) = explode(' ', microtime());
		// Отправить запрос на себя, чтобы запустить тесты
		// и показать результат выполнения тестов
		try {
			$res = @file_get_contents(
				$file = "http://".$this->host.":".$this->port."/".$controller,
				$use_include_path = false,
				$context
			);
		}
		catch(Exception $e) {
			$time_end = list($em, $es) = explode(' ', microtime());
			return array($e->getMessage(), ($em + $es) - ($sm + $ss));
		}
		$time_end = list($em, $es) = explode(' ', microtime());
		return array($res, ($em + $es) - ($sm + $ss));
	}

}
