<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер API для сервиса подписания
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 * @property SignServiceApi_model SignServiceApi_model
 */
class SignServiceApi extends swController
{
	var $NeedCheckLogin = false; // авторизация не нужна
	
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'checkSignature' => array(
				array('field' => 'signature', 'label' => 'Попдись', 'rules' => '', 'type' => 'string'),
				array('field' => 'cert', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
				array('field' => 'data', 'label' => 'Данные', 'rules' => '', 'type' => 'string')
			)
		);
		$this->load->database();
		$this->load->model('SignServiceApi_model', 'dbmodel');
		
		$this->load->library('textlog', array('file' => 'SignServiceApi.log', 'logging' => true));
		
		$this->putdata = file_get_contents('php://input');
		
		$this->textlog->add('QUERY_STRING: '.print_r($_SERVER['QUERY_STRING'], true).' _PUT: '.$this->putdata);

		header('Content-Type: application/json');
	}
	
	/**
	 * API-функция get_token
	 * Вход: Данные, включающие факторы пользователя (данные http сессии)
	 * Выход: Поле token - токе доступа, ассоциированный с пользователем
	 */
	function get_token()
	{
		if (empty($_SESSION['token'])) {
			$this->dbmodel->json_p(array('@status' => 'auth_error', '@note' => 'auth error'));
			return false;
		}
		
		$token = $_SESSION['token'];
		$this->dbmodel->json_p(array('@status' => 'ok', '@note' => 'success', 'token' => $token));
		return true;
	}
	
	/**
	 * API-функция check_perm
	 * Вход: 
	 *	- user_id - идентификатор пользователя
	 *	- permission - тип операции (sign/fetch)
	 *	- doc_id - идентификатор документа
	 * Выход: Поле access - результат проверки (allow/deny)
	 */
	function check_perm()
	{
		// пока заглушка, разрешающая всё
		$access = 'allow';
		$this->dbmodel->json_p(array('@status' => 'ok', '@note' => 'success', 'access' => $access));
		return true;
	}

	/**
	 * API-функция get_cert
	 * Вход: 
	 *	- user_id - идентификатор пользователя
	 *	- timestamp - дата, на которую нужно получить сертификат (сертификаты имеют ограниченнный срок действия)
	 * Выход: Массив полей cert - один или несколько сертификатов X.509
	 */
	function get_cert()
	{
		$json = array();
		if (!empty($this->putdata)) {
			$json = json_decode($this->putdata);
		} elseif (!empty($_REQUEST['json'])) {
			$json = json_decode($_REQUEST['json']);
		}
		
		if (empty($json->user_id) || empty($json->timestamp)) {
			$this->dbmodel->json_p(array('@status' => 'inv_user_id', '@note' => 'inv_user_id'));
			return false;
		}
		// надо получить логин пользователя
		$login = $this->dbmodel->getFirstResultFromQuery('SELECT PMUser_Login FROM v_pmUserCache (nolock) WHERE PMUser_id = :PMUser_id', array('PMUser_id' => $json->user_id));
		if (empty($login)) {
			$this->dbmodel->json_p(array('@status' => 'inv_user_id', '@note' => 'inv_user_id'));
			return false;		
		}
		// получаем сертификаты
		$user = pmAuthUser::find($login);
		$certs = array();
		if (!empty($user->certs)) {
			foreach($user->certs as $onecert) {
				if (!empty($onecert->cert_begdate) && !empty($onecert->cert_enddate) && $onecert->cert_begdate <= $json->timestamp && $onecert->cert_enddate >= $json->timestamp) {
					$certs[] = $onecert->cert_base64;
				}
			}
		}
		$this->textlog->add('output: '.print_r($certs, true));
		$this->dbmodel->json_p(array('@status' => 'ok', '@note' => 'success', 'cert' => $certs));
		return true;
	}
	
	/**
	 * API-функция get_doc
	 * Вход: 
	 *	- token - токен доступ службы ЭЦП
	 *	- doc_id - идентификатор документа
	 * Выход: Поле doc - документ XML в кодировке base64
	 */
	function get_doc()
	{
		/*$xmldoc = '<?xml version="1.0" encoding="UTF-8"?><doc>SAFJALFAFHJDHFEUWH4uh3uh54FSHSAU</doc>';
		$xmldoc_enc = base64_encode($xmldoc);
		$this->textlog->add('output: '.$xmldoc_enc);
		$this->dbmodel->json_p(array('@status' => 'ok', '@note' => 'success', 'doc' => $xmldoc_enc));
		return true;
		*/
		$json = array();
		if (!empty($this->putdata)) {
			$json = json_decode($this->putdata);
		} elseif (!empty($_REQUEST['json'])) {
			$json = json_decode($_REQUEST['json']);
		}
		
		if (empty($json->token) || empty($json->doc_id)) {
			$this->dbmodel->json_p(array('@status' => 'inv_token', '@note' => 'inv_token'));
			return false;
		}
		
		$docparams = explode('_',$json->doc_id);
		if (count($docparams) < 2) {
			$this->dbmodel->json_p(array('@status' => 'inv_doc_id', '@note' => 'invalid document id'));
			return false;
		}
		
		$data = array(
			'Doc_Type' => $docparams[0],
			'Doc_id' => $docparams[1]
		);
		$doc = $this->dbmodel->get_doc($data);
		if ($doc == false) {
			$this->dbmodel->json_p(array('@status' => 'inv_doc_id', '@note' => 'invalid document id'));
			return false;
		}
		// print_r($doc);
		$doc_json = base64_encode(json_encode($doc));
		$xmldoc = '<?xml version="1.0" encoding="UTF-8"?><doc>'.$doc_json.'</doc>';
		$xmldoc_enc = base64_encode($xmldoc);
		// $xmldoc_enc = "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4NCjxkb2M+PC9kb2M+";
		$this->textlog->add('output: '.$xmldoc_enc);
		$this->dbmodel->json_p(array('@status' => 'ok', '@note' => 'success', 'doc' => $xmldoc_enc));
		return true;
	}

	/**
	 * Проверка подписи
	 */
	function checkSignature() {
		$data = $this->ProcessInputData('checkSignature', false);
		if ($data === false) { return false; }

		$resp = 'Verification Failure';
		$this->load->helper('openssl');
		$verified = checkSignature($data['cert'], base64_decode($data['data']), base64_decode($data['signature']));
		if ($verified) {
			$resp = 'Verified OK';
		}

		echo "devicecallback(".json_encode(array('resp' => $resp)).")";
	}
}