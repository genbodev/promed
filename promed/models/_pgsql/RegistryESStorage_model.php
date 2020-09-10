<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RegistryESStorage_model - модель для работы с номерами ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Mse
 * @access      public
 * @copyright   Copyright (c) 2014 Swan Ltd.
 * @author		Stanislav Bykov (savage@swan.perm.ru)
 * @version     21.07.2017
 */

class RegistryESStorage_model extends SwPgModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка номеров ЛВН
	 * @param $data
	 * @return array|bool
	 */
	public function loadRegistryESStorageGrid($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['Lpu_id'])) {
			$filters .= " and RESS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		} else {
			return array();
		}

		$query = "
			select
				RESS.RegistryESStorage_id as \"RegistryESStorage_id\",
				RESS.RegistryESStorage_NumQuery as \"RegistryESStorage_NumQuery\",
				RESS.EvnStickBase_Num as \"EvnStickBase_Num\",
				to_char (RESS.RegistryESStorage_updDT, 'dd.mm.yyyy') as \"RegistryESStorage_updDT\",
				case when RESS.EvnStickBase_id is not null then 2 else 1 end as \"RegistryESStorage_IsUsed\",
				COALESCE(puc.pmUser_Name, '') || ' (' || rtrim(puc.pmUser_Login) || ')' as \"pmUser_Name\"
			from
				v_RegistryESStorage RESS
				left join v_pmUserCache puc on puc.pmUser_id = RESS.pmUser_insID
			where
				{$filters}
				and RESS.EvnStickBase_Num is not null
				and exists( -- рамках одного блока (Один номер запроса) есть хотя бы один не использованный номер
					select
						RESS2.RegistryESStorage_id as RegistryESStorage_id
					from
						v_RegistryESStorage RESS2
					where
						RESS2.RegistryESStorage_NumQuery = RESS.RegistryESStorage_NumQuery
						and RESS2.EvnStickBase_id is null
						and RESS2.Lpu_id = RESS.Lpu_id
                    limit 1
				)
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение номера запроса номеров ЛВН
	 * @param $data
	 * @return array|bool
	 */
	public function loadRegistryESStorageNumQuery($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['Lpu_id'])) {
			$filters .= " and RESS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		} else {
			return array('Error_Msg' => 'Не указана МО, получение номера запроса не возможно.');
		}

		// Если количество не израсходованных блоков ЛВН равно 2-м, то пользователю выходит сообщение: «Хранилище номеров ЛВН заполнено».
		$resp = $this->queryResult("
			select
				count(distinct RESS.RegistryESStorage_NumQuery) as \"cnt\"
			from
				v_RegistryESStorage RESS
			where
				RESS.Lpu_id = :Lpu_id
				and exists( -- рамках одного блока (Один номер запроса) есть хотя бы один не использованный номер
					select
						RESS2.RegistryESStorage_id as RegistryESStorage_id
					from
						v_RegistryESStorage RESS2
					where
						RESS2.RegistryESStorage_NumQuery = RESS.RegistryESStorage_NumQuery
						and RESS2.EvnStickBase_id is null
						and RESS2.EvnStickBase_Num is not null
						and RESS2.Lpu_id = RESS.Lpu_id
                    limit 1
				)
		", array(
			'Lpu_id' => $data['Lpu_id']
		));
		if (!empty($resp[0]['cnt']) && $resp[0]['cnt'] >= 2) {
			return array('Error_Msg' => 'Хранилище номеров ЭЛН заполнено');
		}

		$query = "
			select
				COALESCE(MAX(cast(RESS.RegistryESStorage_NumQuery as int)), 0) + 1 as \"RegistryESStorage_NumQuery\"
			from
				v_RegistryESStorage RESS
			where
				{$filters} and ISNUMERIC(RESS.RegistryESStorage_NumQuery) = 1
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0]['RegistryESStorage_NumQuery'])) {
			$resp[0]['Error_Msg'] = '';
			return $resp[0];
		}

		return array('Error_Msg' => 'Ошибка получения номера запроса');
	}

	/**
	 * Формирование запроса номеров ЛВН
	 * @param $data
	 * @return array|bool
	 */
	public function getRegistryESStorageQuery($data)
	{
		if ($data['RegistryESStorage_Count'] > 1000) {
			return array('Error_Msg' => 'Превышено максимальное количество запрашиваемых номеров ЭЛН. Количество не может быть больше 1000.');
		}

		if (empty($data['Lpu_id'])) {
			return array('Error_Msg' => 'Не указана МО, запрос номеров ЭЛН не возможен.');
		}

		$this->load->library('parser');

		$path = EXPORTPATH_ROOT . "registry_es/";

		if (!file_exists($path)) {
			mkdir($path);
		}

		$out_dir = "numquery_" . $data['Lpu_id'] . "_" . time();
		if (!file_exists($path . $out_dir)) {
			mkdir($path . $out_dir);
		}

		$this->load->helper('openssl');
		$certAlgo = getCertificateAlgo($data['certbase64']);

		$xml_data = array(
			'ogrn' => $this->getFirstResultFromQuery("select Lpu_Ogrn as \"Lpu_Ogrn\" from v_Lpu where Lpu_id = :Lpu_id", array(
				'Lpu_id' => $data['Lpu_id']
			)),
			'cntLnNumbers' => $data['RegistryESStorage_Count'],
			'filehash' => (!empty($data['filehash']) ? $data['filehash'] : ''),
			'filesign' => (!empty($data['filesign']) ? $data['filesign'] : ''),
			'certbase64' => (!empty($data['certbase64']) ? $data['certbase64'] : ''),
			'certhash' => (!empty($data['certhash']) ? $data['certhash'] : ''),
			'signatureMethod' => $certAlgo['signatureMethod'],
			'digestMethod' => $certAlgo['digestMethod']
		);

		$template = 'get_new_ln_num_range';
		$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n" . $this->parser->parse('export_xml/' . $template, $xml_data, true);

		// echo '<textarea cols="150" rows="150">'.$xml.'</textarea>';

		// подписываем и отправляем
		$dir = $path . $out_dir;
		$tempfile = $dir . "/tmp.txt";
		$doc = new DOMDocument();
		$xml = preg_replace('/\r\n/u', "\n", $xml);
		$doc->loadXML($xml);

		// сохраняем XML
		$xml = $doc->saveXML();

		if (!empty($data['needHash'])) {
			$doc = new DOMDocument();
			$doc->loadXML($xml);
			$toHash = $doc->getElementsByTagName('Body')->item(0)->C14N(true, false);
			// считаем хэш
			$cryptoProHash = getCryptCpHash($toHash, $data['certbase64']);
			// 2. засовываем хэш в DigestValue
			$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue = $cryptoProHash;
			// 3. считаем хэш по SignedInfo
			$toSign = $doc->getElementsByTagName('SignedInfo')->item(0)->C14N(true, false);
			$Base64ToSign = base64_encode($toSign);

			return array('Error_Msg' => '', 'xml' => $xml, 'Base64ToSign' => $Base64ToSign, 'Hash' => $cryptoProHash);
		} else {
			return array('Error_Msg' => '', 'xml' => $xml);
		}
	}

	/**
	 * Запрос номеров ЛВН
	 */
	public function queryRegistryESStorage($data) {

		$this->load->library('textlog', array('file'=>'queryRegistryESStorage_'.date('Y-m-d').'.log'));
		$this->textlog->add("queryRegistryESStorage begin");

		$stream_context = stream_context_create(array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		));

		$options = array(
			'soap_version'=>SOAP_1_2,
			'stream_context' => $stream_context,
			'exceptions'=>1, // обработка ошибок
			'trace'=>1, // трассировка
			'connection_timeout'=>15,
			//'proxy_host' => '192.168.36.31',
			//'proxy_port' => '3128',
			//'proxy_login' => '',
			//'proxy_password' => '',
		);

		if (!empty($this->config->item('REGISTRY_LVN_PROXY_HOST')) && !empty($this->config->item('REGISTRY_LVN_PROXY_PORT'))) {
			$options['proxy_host'] = $this->config->item('REGISTRY_LVN_PROXY_HOST');
			$options['proxy_port'] = $this->config->item('REGISTRY_LVN_PROXY_PORT');
			$options['proxy_login'] = $this->config->item('REGISTRY_LVN_PROXY_LOGIN');
			$options['proxy_password'] = $this->config->item('REGISTRY_LVN_PROXY_PASSWORD');
		}

		$this->textlog->add('Запрос: ' . $data['xml']);
		if (in_array($data['signType'], array('authapplet', 'authapi', 'authapitomee'))) {
			// надо в XML подсунуть хэш и подпись
			$newxml = new DOMDocument();
			$newxml->loadXML($data['xml']);
			$newxml->getElementsByTagName('DigestValue')->item(0)->nodeValue = $data['Hash'];
			$newxml->getElementsByTagName('SignatureValue')->item(0)->nodeValue = $data['SignedData'];
			$data['xml'] = $newxml->saveXML();
		}

		// echo "<textarea>{$data['xml']}</textarea>"; die();

		$url = $this->config->item('EvnStickServiceUrl');
		if (empty($url)) {
			return array('Error_Msg' => 'Не указан URL сервиса обмена ЭЛН с ФСС');
		}

		try {
			$error_msg = "Не удалось установить соединение с сервисом";
			set_error_handler(function ($errno, $errstr) {
				throw new Exception($errstr);
			}, E_ALL & ~E_NOTICE);
			$soapClient = new SoapClient($url, $options);
			restore_error_handler();
		} catch ( Exception $e ) {
			$result['errorMsg'] = $e->getMessage();
			$result['success'] = false;
			return array('Error_Msg' => $e->getMessage());
		}

		$encryption = $this->config->item('EvnStickServiceEncryption');
		if ($encryption) {
			$cert_base64 = $this->getCertBase64();
			$xml = $this->encodeXmlForTransfer($data['xml'], $cert_base64);
			if (is_array($xml)) {
				// если не строка пришла, значит ошибка какая то
				return $xml;
			}
		} else {
			$xml = $data['xml'];
		}


		// echo "<textarea cols='150' rows='30'>{$xml}</textarea>";

		try {
			$response = $soapClient->__doRequest($xml, $url, 'getNewLNNumRange', '1.2');
		} catch ( SoapFault $e ) {
			// var_dump($e);
			$result['errorMsg'] = $e->getMessage();
			$result['success'] = false;
			return array('Error_Msg' => $e->getMessage());
		}

		if (!empty($response)) {
			if (!empty($_REQUEST['getDebug'])) {
				echo '<textarea>' . $response . '</textarea>';
			}
			if ($encryption) {
				// надо расшифровать, чтобы получить XML с данными
				$xml = $this->decodeXmlForTransfer($response);
				if (is_array($xml)) {
					// если не строка пришла, значит ошибка какая то
					return $xml;
				}
			} else {
				$xml = $response;
			}
			$this->textlog->add('Ответ: ' . $xml);
			$this->textlog->add("queryRegistryESStorage end");
			// echo "<textarea cols='150' rows='30'>{$response}</textarea>";
			$resp_xml = new DOMDocument();
			$resp_xml->loadXML($xml);

			$fault_arr = array();
			$faults = $resp_xml->getElementsByTagName('faultstring');
			foreach($faults as $fault) {
				$fault_arr[] = $fault->nodeValue;
			}

			if (count($fault_arr) > 0) {
				return array('Error_Msg' => $fault_arr[0]);
			}

			$fileOperationsLnUserGetNewLNNumRangeOut = $resp_xml->getElementsByTagName('fileOperationsLnUserGetNewLNNumRangeOut')->item(0);
			if (!empty($fileOperationsLnUserGetNewLNNumRangeOut)) {
				$status = $fileOperationsLnUserGetNewLNNumRangeOut->getElementsByTagName('STATUS')->item(0);
				if (!empty($status)) {
					if ($status->nodeValue == '1') {
						$nums = $fileOperationsLnUserGetNewLNNumRangeOut->getElementsByTagName('LNNum');
						$numSaved = false;
						foreach($nums as $num) {
							if(!empty($num->nodeValue)) {
								$numSaved = true;
								$this->db->query("
                                select 
                                    Error_Code as \"Error_Code\",
                                    Error_Message as \"Error_Msg\"
                                from p_RegistryESStorage_ins (   
                                    EvnStickBase_Num := :EvnStickBase_Num,
                                    RegistryESStorage_NumQuery := :RegistryESStorage_NumQuery,
                                    Lpu_id := :Lpu_id,
                                    RegistryESStorage_bookDT := null,
                                    EvnStickBase_id := null,
                                    pmUser_id := :pmUser_id
                                    )
								", array(
									'EvnStickBase_Num' => $num->nodeValue,
									'RegistryESStorage_NumQuery' => $data['RegistryESStorage_NumQuery'],
									'Lpu_id' => $data['Lpu_id'],
									'pmUser_id' => $data['pmUser_id']
								));
							}
						}
						if($numSaved) {
							return array('Error_Msg' => '');
						}
					} else {
						$message = $fileOperationsLnUserGetNewLNNumRangeOut->getElementsByTagName('MESS')->item(0);
						if (!empty($message)) {
							return array('Error_Msg' => 'Получить номер ЭЛН не удалось: '."<br>".$message->nodeValue);
						} else {
							return array('Error_Msg' => 'Получить номер ЭЛН не удалось.');
						}
					}
				}
			} else {
				return array('Error_Msg' => 'Получить номер ЭЛН не удалось: в ответе нет тега fileOperationsLnUserGetNewLNNumRangeOut');
			}
		}

		return array('Error_Msg' => 'Ошибка запроса номеров ЭЛН');
	}

	/**
	 * Расшифровка XML
	 */
	public function decodeXmlForTransfer($xml, $count = 0) {
		// обработка ошибки от сервиса ФСС, приходит в незашифрованном виде
		$matches = array();
		if (preg_match('/<faultstring>(.*?)<\/faultstring>/uis', $xml, $matches)) {
			return array('Error_Msg' => "Ошибка от сервиса ФСС: " . $matches[1]);
		}

		// отправляем сервису шифрования
		$EncryptionServiceUrl = $this->config->item('EncryptionServiceUrl');
		if (empty($EncryptionServiceUrl)) {
			return array('Error_Msg' => 'Не указан URL сервиса шифрования');
		}

		$options_encr = array(
			'soap_version' => SOAP_1_1,
			'exceptions' => 1, // обработка ошибок
			'trace' => 1, // трассировка
			'connection_timeout' => 15
		);

		try {
			$error_msg = "Не удалось установить соединение с сервисом";
			set_error_handler(function ($errno, $errstr) {
				throw new Exception($errstr);
			}, E_ALL & ~E_NOTICE);
			$soapClient = new SoapClient($EncryptionServiceUrl, $options_encr);
			restore_error_handler();
		} catch ( Exception $e ) {
			if ($count < 3) {
				$count++;
				return $this->decodeXmlForTransfer($xml, $count);
			}
			$result['errorMsg'] = $e->getMessage();
			$result['success'] = false;
			return array('Error_Msg' => $e->getMessage());
		}

		try {
			$params = array();
			$params[] = new SoapVar($xml, XSD_STRING, null, null, 'arg0' );
			$response = $soapClient->getStringDecryptedDoc(new SoapVar($params, SOAP_ENC_OBJECT));
			if (!empty($_REQUEST['getDebug'])) {
				echo 'запрос к сервису шифрования (' . $EncryptionServiceUrl . '):';
				echo '<textarea>' . $soapClient->__getLastRequest() . '</textarea>';
				echo 'ответ сервиса шифрования:';
				echo '<textarea>' . $soapClient->__getLastResponse() . '</textarea>';
			}
		} catch ( Exception $e ) {
			if ($count < 3) {
				$count++;
				return $this->decodeXmlForTransfer($xml, $count);
			}
			echo "<textarea cols='150' rows='30'>".($soapClient->__getLastRequest())."</textarea>";
			$result['errorMsg'] = $e->getMessage();
			$result['success'] = false;
			return array('Error_Msg' => $e->getMessage());
		}

		if (empty($response->return)) {
			return array('Error_Msg' => 'Ошибка сервиса шифрования');
		}

		return $response->return;
	}

	/**
	 * Получение сертификата для подписи запросов в ФСС
	 */
	public function getCertBase64() {
		$certfile = $this->config->item('EvnStickServiceEncryptionCert');
		$cert_base64 = '';

		if (!empty($certfile) && file_exists($certfile)) {
			$cert = file_get_contents($certfile);

			$this->load->helper('openssl');
			$cert_base64 = getCertificateFromString($cert, true);
		}

		return $cert_base64;
	}

	/**
	 * Шифрование XML для передачи
	 */
	public function encodeXmlForTransfer($xml, $cert, $count = 0) {
		// отправляем сервису шифрования
		$EncryptionServiceUrl = $this->config->item('EncryptionServiceUrl');
		if (empty($EncryptionServiceUrl)) {
			return array('Error_Msg' => 'Не указан URL сервиса шифрования');
		}

		$options_encr = array(
			'soap_version' => SOAP_1_1,
			'exceptions' => 1, // обработка ошибок
			'trace' => 1, // трассировка
			'connection_timeout' => 15
		);

		try {
			$error_msg = "Не удалось установить соединение с сервисом";
			set_error_handler(function ($errno, $errstr) {
				throw new Exception($errstr);
			}, E_ALL & ~E_NOTICE);
			$soapClient = new SoapClient($EncryptionServiceUrl, $options_encr);
			restore_error_handler();
		} catch ( Exception $e ) {
			if ($count < 3) {
				$count++;
				return $this->encodeXmlForTransfer($xml, $cert, $count);
			}
			$result['errorMsg'] = $e->getMessage();
			$result['success'] = false;
			return array('Error_Msg' => $e->getMessage());
		}

		$resp_xml = new DOMDocument();
		$resp_xml->loadXML($xml);
		$envelopeData = $resp_xml->getElementsByTagName('Envelope');
		if (count($envelopeData) < 1) {
			return array('Error_Msg' => 'Ошибка сервиса шифрования');
		}
		$envelope_xml = $envelopeData->item(0)->C14N();
		unset($resp_xml);
		unset($envelopeData);

		try {
			$params = array();
			$params[] = new SoapVar('<x>'.$envelope_xml.'</x>', XSD_STRING, null, null, 'arg0' );
			$params[] = new SoapVar($cert, XSD_STRING, null, null, 'arg1' );
			$response = $soapClient->getStringEncryptedDoc(new SoapVar($params, SOAP_ENC_OBJECT));
			if (!empty($_REQUEST['getDebug'])) {
				echo 'запрос к сервису шифрования (' . $EncryptionServiceUrl . '):';
				echo '<textarea>' . $soapClient->__getLastRequest() . '</textarea>';
				echo 'ответ сервиса шифрования:';
				echo '<textarea>' . $soapClient->__getLastResponse() . '</textarea>';
			}
		} catch ( Exception $e ) {
			if ($count < 3) {
				$count++;
				return $this->encodeXmlForTransfer($xml, $cert, $count);
			}

			echo "<textarea cols='150' rows='30'>".($soapClient->__getLastRequest())."</textarea>";
			$result['errorMsg'] = $e->getMessage();
			$result['success'] = false;
			return array('Error_Msg' => $e->getMessage());
		}

		if (empty($response->return)) {
			return array('Error_Msg' => 'Ошибка сервиса шифрования');
		}

		$resp_xml = new DOMDocument();
		$resp_xml->loadXML($response->return);
		$encData = $resp_xml->getElementsByTagName('EncryptedData');
		if (count($encData) < 1) {
			return array('Error_Msg' => 'Ошибка сервиса шифрования');
		}
		$EncryptedData = $encData->item(0)->C14N();
		unset($resp_xml);
		unset($encData);

		$new_xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
<soapenv:Header>
</soapenv:Header>
<soapenv:Body>
'.$EncryptedData.'
</soapenv:Body>
</soapenv:Envelope>';

		return $new_xml;
	}

	/**
	 * Получение номера ЭЛН
	 */
	public function getEvnStickNum($data) {
		// •	Номер берется из хранилища по принципу FIFO. Берется первый номер из самого раннего пакета запрашиваемых номеров в рамках МО, в которой добавляется ЛВН.
		// •	Не берутся уже занятые номера. (Наличие ссылки на ЛВН)
		// •	Не берутся номера которые забронированы менее 30 минут назад. (Разница между текущей датой/временем и  указанной в поле бронирования номера меньше чем 30 минут. )

		$resp = $this->queryResult("
			select
				RegistryESStorage_id as \"RegistryESStorage_id\",
				EvnStickBase_Num as \"EvnStickBase_Num\"
			from
				v_RegistryESStorage
			where
				EvnStickBase_id is null
				and DATEDIFF('minute', COALESCE(RegistryESStorage_bookDT, '2010-01-01'), dbo.tzGetDate()) > 30
				and Lpu_id = :Lpu_id
				and RegistryESStorage_NumQuery <> ''
				and EvnStickBase_Num <> ''
			order by
				cast(RegistryESStorage_NumQuery as int),
				EvnStickBase_Num
            limit 1
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($resp[0]['RegistryESStorage_id'])) {
			$this->db->query("
				update RegistryESStorage set RegistryESStorage_bookDT = dbo.tzGetDate() where RegistryESStorage_id = :RegistryESStorage_id
			", array(
				'RegistryESStorage_id' => $resp[0]['RegistryESStorage_id']
			));

			return array('Error_Msg' => '', 'EvnStick_Num' => $resp[0]['EvnStickBase_Num'], 'RegistryESStorage_id' => $resp[0]['RegistryESStorage_id']);
		}

		return array('Error_Msg' => 'Получить номер ЛВН не удалось. Хранилище номеров пустое');
	}

	/**
	 * Отмена бронирвоания номера ЭЛН
	 */
	public function unbookEvnStickNum($data) {

		$result = $this->db->query("
			select 
				RES.RegistryESStorage_id as \"RegistryESStorage_id\"
			from RegistryESStorage RES 
			left join v_EvnStickBase ESB on ESB.EvnStickBase_id = RES.EvnStickBase_id
			left join v_EvnStickWorkRelease ESWR on ESWR.EvnStickBase_id = RES.EvnStickBase_id
			where 
				RES.RegistryESStorage_id = :RegistryESStorage_id and (
					coalesce(ESB.EvnStickBase_IsInReg,1) <> 2 and
					coalesce(ESB.EvnStickBase_IsPaid,1) <> 2 and
					coalesce(ESWR.EvnStickWorkRelease_IsInReg,1) <> 2 and
					coalesce(ESWR.EvnStickWorkRelease_IsPaid,1) <> 2
				)
		", array(
			'RegistryESStorage_id' => $data['RegistryESStorage_id']
		));

		$result = $result->result('array');

		if(isset($result[0])) {
			$this->db->query("
				update RegistryESStorage
				set 
					RegistryESStorage_bookDT = null 
				where 
					RegistryESStorage_id = :RegistryESStorage_id",
				array('RegistryESStorage_id' => $result[0]['RegistryESStorage_id'])
			);
			return array('success' => true);
		} else {
			return array('Error_Msg' => 'Ошибка при освобождении номера ЭЛН. ЭЛН находится в реестре или отправлен в ФСС');
		}
	}
}