<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Wrapper для запросов к сервису
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 * @property SignServiceJaxRSWrapper_model SignServiceJaxRSWrapper_model
 */
class SignServiceJaxRSWrapper extends swController
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('textlog', array('file' => 'SignServiceJaxRSWrapper.log', 'logging' => true));
		$this->textlog->add('QUERY_STRING: '.print_r($_SERVER['QUERY_STRING'], true).' _POST: '.print_r($_POST, true));
	}

	/**
	 * Ремап
	 */
	function _remap($method)
	{
		$data = getSessionParams();

		$url = SIGN_SERVICE_URL."/{$method}";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		)); 

		$POST = json_encode($_POST);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POST); 
		$response = curl_exec($ch);

		if ($method == 'put_doc_sign' && mb_strpos($response, "Document stored") !== false) {
			// разбираем doc_id, апдейтим поля подписи, если нужно
			$docparams = explode('_', $_POST['doc_id']);
			if (count($docparams) == 2 && is_numeric($docparams[1])) {
				$needUpdate = false;
				switch($docparams[0]) {
					case 'EvnReceptOtv':
						$needUpdate = true;
						$table = "EvnRecept";
						$idField = "EvnRecept_id";
						$signedField = "EvnRecept_IsOtvSigned";
						$pmuserField = "pmUser_signotvID";
						$dateField = "EvnRecept_signotvDT";
						break;
				}

				if ($needUpdate) {
					$this->load->database();
					$this->load->model('SignServiceApi_model');
					$this->SignServiceApi_model->updateSignedData(array(
						'table' => $table,
						'dateField' => $dateField,
						'pmuserField' => $pmuserField,
						'signedField' => $signedField,
						'idField' => $idField,
						'doc_id' => (float)$docparams[1],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}
		
		if (!empty($_REQUEST['callback'])) {
			echo $_REQUEST['callback']."(".$response.")";
		} else {
			echo $response;
		}
	}
}