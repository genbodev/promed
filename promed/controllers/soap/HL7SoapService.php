<?php
/**
 * Сервис для РИШ. HL7 в ФЭР
 *
 * http://swan.perm.ru/ER
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Ivan Petukhov aka Lich (ethereallich@gmail.com)
 * @version      05.09.2012
 */

/**
 * Контроллер, обрабатывающий методы wsdl
 *
 * @package		WebService
 */
class HL7SoapService extends swController
{
	var $LinkedWDSL = "HL7SoapService.wsdl";
	var $NeedCheckLogin = false;

	/**
	 * Индекс, запуск SOAP сервера, разруливание запросов
	 */
	function index()
	{
		/**
		 * Глобальный объект SOAP сервиса, нужен для добавления заголовков в него
		 */
		global $_SOAP_Server;

		if ( strpos($_SERVER['REQUEST_URI'], '?wsdl') ) {
			$this->wsdl();
		} else {
			// Надо проверить авторизацию, для этого достаём Header.
			$xml = file_get_contents("php://input");
			// Из Header'a Username и Password
			libxml_use_internal_errors(true);
			$dom = new DOMDocument();
			if (!empty($xml)) {
				$dom->loadXML($xml);
				$dom_uname = $dom->getElementsByTagName('Username');
				$login = null;
				foreach ($dom_uname as $dom_uname_one) {
					$login = $dom_uname_one->nodeValue;
				}
				$dom_upass = $dom->getElementsByTagName('Password');
				$password = null;
				foreach ($dom_upass as $dom_upass_one) {
					$password = $dom_upass_one->nodeValue;
				}

				if (!empty($login) && !empty($password)) {
					$user = pmAuthUser::find($login);
					if ($user && $result = $user->login($login, $password)) {
						if (is_array($result) && !empty($result['Error_Msg'])) {
							session_destroy();
						}
					}
				}
			}

			require_once(APPPATH.'libraries/DebugSoapServer.php');
			$_SOAP_Server = new DebugSoapServer(
				WSDL_PATH . $this->LinkedWDSL,
				array('uri' => 'urn:NAMESPACEOFWEBSERVICE', 'encoding' => SOAP_ENCODED)
			);
			$_SOAP_Server->setClass(get_class($this));
			/*soap_log_message(
				'info',
				print_r($GLOBALS, true)
			);*/
			header('Content-type: text/html; charset=utf-8');
			ob_start();
			$_SOAP_Server->handle();
			$debug = $_SOAP_Server->getAllDebugValues();
			soap_log_message(
				'info',
				"Время выполнения: ".$debug['CallDuration']."\r\n".
				"Запрос: \n".$debug['RequestString']."\r\n".
				"Ответ: \r\n".$debug['ResponseString']."\r\n"
			);
		}
	}

	/**
	 * Обработка заголовка
	 */
	function Header($header)
	{
		/** @var $_SOAP_Server SoapServer */
		global $_SOAP_Server;

		$header = new SoapHeader("rev", "Header", $header);

		$_SOAP_Server->addSoapHeader( $header );
	}

	/**
	 * Выдает сам WSDL
	 */
	function wsdl()
	{
		header('Content-type: text/xml; charset=utf-8');
		echo file_get_contents(WSDL_PATH . $this->LinkedWDSL);
	}

	/**
	 * Метод получения сообщений. Единственный доступный снаружи
	 */
	public function Send($message) {
		/*soap_log_message(
			'info',
			$data->MessageData->AppData->message
		);*/
		$this->load->database();
		$this->load->model('HL7SoapService_model');
		$this->load->helper('DataStructure\Common');
		$this->load->helper('DataStructure\HL7');

		if (empty($_SESSION['pmuser_id'])) {
			return new SoapFault("Server", "Ошибка авторизации");
		}

		// $_GET['sql_debug'] = 1;
		$data = getSessionParams();
		try {
			$resp = $this->HL7SoapService_model->handleHL7Message($data, $message->MessageData->AppData->message);
			// var_dump($resp); die();
			return $resp;
		} catch (Exception $e) {
			// var_dump($e); die();
			return new SoapFault("Server", $e->getMessage());
		}
	}
}

