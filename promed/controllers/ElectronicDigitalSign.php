<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicDigitalSign - контроллер для работы с рецептами блюд
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			01.11.2013
 */

class ElectronicDigitalSign extends swController {
	/**
	 * @var array
	 */
	protected $inputRules = array(
		'documentVerification' => array(
			array(
				'field' => 'Doc_id',
				'label' => 'Идентификатор документа',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'loadDocumentVersionList' => array(
			array(
				'field' => 'Doc_id',
				'label' => 'Идентификатор документа',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getSignedDoc' => array(
			array(
				'field' => 'Doc_id',
				'label' => 'Идентификатор документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Doc_Version',
				'label' => 'Версия',
				'rules' => '',
				'type' => 'int'
			)
		),
		'exportSignedDoc' => array(
			array(
				'field' => 'Doc_id',
				'label' => 'Идентификатор документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Doc_Version',
				'label' => 'Версия',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getSignedDocInfo' => array(
			array(
				'field' => 'Doc_id',
				'label' => 'Идентификатор документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Doc_Version',
				'label' => 'Версия',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getCertificateList' => array(

		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->library('textlog', array('file'=>'ElectronicDigitalSign.log'));
		$this->load->database();
		$this->load->model('ElectronicDigitalSign_model', 'dbmodel');
	}

	/**
	 * Возвращает список хэшей сертификатов текущего пользователя
	 */
	function getCertificateList()
	{
		$data = $this->ProcessInputData('getCertificateList',true);
		if ( $data === false ) { return false; }

		$certs = array();
		if (!empty($data['session']['certs'])) {
			$curTime = time();
			foreach($data['session']['certs'] as $cert) {
				if (is_array($cert) && !empty($cert['cert_sha1'])) {
					if (!empty($cert['cert_enddate']) && $cert['cert_enddate'] < $curTime) {
						continue;
					}
					$certs[] = mb_strtolower($cert['cert_sha1']);
				} else if (is_object($cert) && property_exists($cert, 'cert_sha1')) {
					if (!empty($cert->cert_enddate) && $cert->cert_enddate < $curTime) {
						continue;
					}
					$certs[] = mb_strtolower($cert->cert_sha1);
				}

			}
		}
		$this->ReturnData(array('success' => true, 'certs' => $certs));
	}

	/**
	 * Возвращает список сертификатов текущего пользователя
	 */
	function getCertificateGrid()
	{
		$data = $this->ProcessInputData('getCertificateList',true);
		if ( $data === false ) { return false; }

		$k = 0;
		$certs = array();
		if (!empty($data['session']['certs'])) {
			$curTime = time();
			foreach($data['session']['certs'] as $cert) {
				if (is_array($cert) && !empty($cert['cert_sha1'])) {
					if (!empty($cert['cert_enddate']) && $cert['cert_enddate'] < $curTime) {
						continue;
					}
					$k++;

					$certs[] = array(
						'Cert_id' => $k,
						'Cert_Base64' => $cert['cert_base64'],
						'Cert_SubjectName' => $cert['cert_name'],
						'Cert_IssuerName' => null,
						'Cert_ValidFromDate' => date('d.m.Y', $cert['cert_begdate']),
						'Cert_ValidToDate' => date('d.m.Y', $cert['cert_enddate']),
						'Cert_Thumbprint' => mb_strtolower($cert['cert_sha1'])
					);
				} else if (is_object($cert) && property_exists($cert, 'cert_sha1')) {
					if (!empty($cert->cert_enddate) && $cert->cert_enddate < $curTime) {
						continue;
					}
					$k++;

					$certs[] = array(
						'Cert_id' => $k,
						'Cert_Base64' => $cert->cert_base64,
						'Cert_SubjectName' => $cert->cert_name,
						'Cert_IssuerName' => null,
						'Cert_ValidFromDate' => date('d.m.Y', $cert->cert_begdate),
						'Cert_ValidToDate' => date('d.m.Y', $cert->cert_enddate),
						'Cert_Thumbprint' => mb_strtolower($cert->cert_sha1)
					);
				}

			}
		}
		$this->ReturnData($certs);
	}

	/**
	 * Верификация документа
	 */
	function documentVerification() {
		$data = $this->ProcessInputData('documentVerification',true);
		if ( $data === false ) { return false; }
		
		$url = SIGN_SERVICE_URL."/verify_doc_sign";
		
		$docparams = explode('_',$data['Doc_id']);
		if (count($docparams) < 2) {
			$this->dbmodel->json_p(array('@status' => 'inv_doc_id', '@note' => 'invalid document id'));
			return false;
		}

		$pmUser_id = $this->dbmodel->getPmUserIdForSignedDoc($docparams);
		if (empty($pmUser_id)) {
			$pmUser_id = $data['session']['pmuser_id'];
		}

		$post = json_encode(array( // http_build_query
			'token' => $data['session']['token'],
			'doc_id' => $data['Doc_id'],
			'user_ids' => array($pmUser_id)
		));
		
		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		));

		$res = curl_exec($ch);
		$res = preg_replace("/\r\n/", '', $res);
		$res = preg_replace("/ +/", ' ', $res);

		$resp = json_decode($res, true);
		
		$valid = null;
		if (!empty($resp['validation'][0]) && $resp['validation'][0] == 'valid') {
			$valid = 2;
		}

		// нужно обновить статус документа в бд, пока только для Evn'ов.
		if (in_array($docparams[0], array('Evn', 'EvnVizitPL', 'EvnPS', 'EvnSection', 'EvnPrescrPlan', 'EvnUslugaPar', 'EvnRecept', 'EvnDirection', 'EvnFuncRequest', 'EvnDirectionMorfoHistologic', 'EvnDirectionHistologic', 'EvnReceptOtv'))) {
			$valid = $this->dbmodel->onValidateEvn($data, $docparams, $valid);
		}
		
		$this->ReturnData(array(
			'success' => true,
			'valid' => $valid
		));
		
		return true;
	}
	
	/**
	 * Получение информации о подписи документа
	 */
	function getSignedDocInfo()
	{
		$data = $this->ProcessInputData('getSignedDocInfo',true);
		if ( $data === false ) { return false; }

		$url = SIGN_SERVICE_URL."/get_signed_doc";
		
		$post = json_encode(array( // http_build_query
			'token' => $data['session']['token'],
			'version' => $data['Doc_Version'],
			'doc_id' => $data['Doc_id']
		));

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		));

		$res = curl_exec($ch);
		$res = preg_replace("/\r\n/", '', $res);
		$res = preg_replace("/ +/", ' ', $res);

		// echo $res; exit();

		$resp = json_decode($res, true);

		if ( empty($resp['@status']) || $resp['@status'] != 'ok' ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}
		else if ( !array_key_exists('sign', $resp) || empty($resp['sign']) ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}

		$response = array();
		$response[0]['success'] = true;
		$response[0]['pmUser_Name'] = toUtf($this->dbmodel->getFirstResultFromQuery('SELECT pmUser_Name FROM v_pmUserCache (nolock) WHERE pmUser_id = :pmUser_id', array('pmUser_id' => $resp['sign'][0]['user_id'])));
		$response[0]['xmldsig'] = $resp['sign'][0]['xmldsig'];
		
		$this->ReturnData($response);
		
		return true;
	}
	
	/**
	 * Получение информации о подписи документа
	 */
	function exportSignedDoc()
	{
		$data = $this->ProcessInputData('getSignedDocInfo',true);
		if ( $data === false ) { return false; }

		$url = SIGN_SERVICE_URL."/get_signed_doc";
		
		$post = json_encode(array( // http_build_query
			'token' => $data['session']['token'],
			'version' => $data['Doc_Version'],
			'doc_id' => $data['Doc_id']
		));

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		));

		$res = curl_exec($ch);
		$res = preg_replace("/\r\n/", '', $res);
		$res = preg_replace("/ +/", ' ', $res);

		// echo $res; exit();

		$resp = json_decode($res, true);
		if ( empty($resp['@status']) || $resp['@status'] != 'ok' ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}
		else if ( !array_key_exists('sign', $resp) || empty($resp['sign']) ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}
		if (empty($resp['doc'])) {
			$this->ReturnError('Ошибка получения документа');
			return false;			
		}
		if (empty($resp['sign'][0]['xmldsig'])) {
			$this->ReturnError('Ошибка получения подписи xmldsig');
			return false;
		}
		
		$xml = base64_decode($resp['doc']);
		$xmldsig = $resp['sign'][0]['xmldsig'];
		
		$out_dir = EXPORTPATH_REGISTRY."signed".time()."_".$data['Doc_id'].$data['Doc_Version'];
		mkdir( $out_dir );
		
		$file_zip_name = $out_dir."/signeddoc.zip";
		$documentxml = $out_dir."/document.xml";
		$xmldsigtxt = $out_dir."/xmldsig.txt";
		
		file_put_contents($documentxml, $xml);
		file_put_contents($xmldsigtxt, $xmldsig);
		
		$zip=new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $documentxml, "document.xml" );
		$zip->AddFile( $xmldsigtxt, "xmldsig.txt" );
		$zip->close();
		
		unlink($documentxml);
		unlink($xmldsigtxt);
		
		$this->ReturnData(array(
			'success' => true,
			'link' => $file_zip_name
		));
		
		return true;
	}
	 
	/**
	 * Получение данных подписанного документа
	 */
	function getSignedDoc()
	{
		$data = $this->ProcessInputData('getSignedDoc',true);
		if ( $data === false ) { return false; }

		$url = SIGN_SERVICE_URL."/get_signed_doc";
		
		$post = json_encode(array( // http_build_query
			'token' => $data['session']['token'],
			'version' => $data['Doc_Version'],
			'doc_id' => $data['Doc_id']
		));

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		));

		$res = curl_exec($ch);
		$res = preg_replace("/\r\n/", '', $res);
		$res = preg_replace("/ +/", ' ', $res);

		// echo $res; exit();

		$resp = json_decode($res, true);

		if ( empty($resp['@status']) || $resp['@status'] != 'ok' ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}
		else if ( !array_key_exists('doc', $resp) || empty($resp['doc']) ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}

		$response = array();
		$doc = json_decode(base64_decode(str_replace('</doc>','',str_replace('<?xml version="1.0" encoding="UTF-8"?><doc>','',base64_decode($resp['doc'])))), true);
		
		$response = array();
		$response[0]['success'] = true;
		$response[0]['html'] = '';
		if (!empty($doc['html'])) {
			$response[0]['html'] = $doc['html'];
		}
		$response[0]['pmUser_Name'] = toUtf($this->dbmodel->getFirstResultFromQuery('SELECT pmUser_Name FROM v_pmUserCache WHERE pmUser_id = :pmUser_id', array('pmUser_id' => $resp['sign'][0]['user_id'])));
		
		$this->ReturnData($response);

		return true;
	}
	
	/**
	 * Возвращает список версий документов
	 */
	function loadDocumentVersionList()
	{
		$data = $this->ProcessInputData('loadDocumentVersionList',true);
		if ( $data === false ) { return false; }

		$url = SIGN_SERVICE_URL."/list_doc_sign";
		
		$post = json_encode(array( // http_build_query
			'token' => $data['session']['token'],
			'doc_id' => $data['Doc_id']
		));

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		));

		$res = curl_exec($ch);
		$res = preg_replace("/\r\n/", '', $res);
		$res = preg_replace("/ +/", ' ', $res);

		// echo $res; exit();

		$resp = json_decode($res, true);

		if ( empty($resp['@status']) || $resp['@status'] != 'ok' ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}
		else if ( !array_key_exists('sign_info', $resp) || !is_array($resp['sign_info']) ) {
			$this->ReturnError('Неверный ответ сервиса');
			return false;
		}

		$response = $this->dbmodel->loadDocumentVersionList($resp['sign_info']);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}