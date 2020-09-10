<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RegistryES_model - модель для работы с реестрами ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Mse
 * @access      public
 * @copyright   Copyright (c) 2014 Swan Ltd.
 * @author		Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version     29.09.2014
 */

class RegistryES_model extends swModel {
	protected $StickFSSType = array(); // данные из справочника StickFSSType, используются для соответствия кода из ФСС с нашим id справочника.

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->dbMain = $this->load->database('default', true);
	}

	/**
	 * Запрос в ФСС
	 */
	function requestRegistryESToFss($data)
	{
		$this->load->library('parser');
		$this->load->library('textlog', array('file'=>'requestRegistryESToFss_'.date('Y-m-d').'.log'));

		$registry = $this->getRegistryESExport($data);
		if ( !is_array($registry) || count($registry) == 0 ) {
			return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.'));
		}
		$registry = $registry[0];

		if ($registry['RegistryESStatus_Code'] != 3) {
			return array('Error_Msg' => 'Реестр не может быть отправлен в ФСС, т.к. его статус "' . $registry['RegistryESStatus_Name'] . '".');
		}

		if ($registry['RegistryESType_id'] == 3) {
			// складываем из XML-ек ROW готовую XML-ку и кладем её в RegistryES, записываем дату эскпорта XML-ки
			$registry_data = json_decode($data['xmls'], true);

			$path = EXPORTPATH_ROOT . "registry_es/";
			$out_dir = "re_xml_fss_del_" . time();
			if (!file_exists($path . $out_dir)) {
				mkdir($path . $out_dir);
			}

			foreach ($registry_data as $item) {
				$num = (float)$item['num'];
				if (empty($num)) {
					return array('success' => false, 'Error_Msg' => toUtf('Ошибочные входящие данные.'));
				}

				$filepath = $path . $out_dir . "/export_" . $num . '_' . time() . ".xml";

				if (!empty($item['signType']) && in_array($item['signType'], array('authapplet', 'authapi', 'authapitomee'))) {
					// надо в XML подсунуть хэш и подпись
					$newxml = new DOMDocument();
					$newxml->loadXML($item['xml']);
					$newxml->getElementsByTagName('DigestValue')->item(0)->nodeValue = $item['Hash'];
					$newxml->getElementsByTagName('SignatureValue')->item(0)->nodeValue = $item['SignedData'];
					$item['xml'] = $newxml->saveXML();
				}

				$bytes = file_put_contents($filepath, $item['xml']);
				$this->textlog->add("RegistryES_id: " . $data['RegistryES_id'] . ", EvnStick_Num: " . $item['num'] . "filepath: " . $filepath . ", bytes: " . $bytes);

				$this->db->query("
					update RegistryESData with (rowlock) set RegistryESData_xmlExportPath = :RegistryESData_xmlExportPath where RegistryES_id = :RegistryES_id and EvnStick_Num = :EvnStick_Num
				", array(
					'RegistryESData_xmlExportPath' => $filepath,
					'RegistryES_id' => $data['RegistryES_id'],
					'EvnStick_Num' => $item['num']
				));
			}

			// помечаем реестр готовым к отправке (ставим в очередь)
			$this->setRegistryESXmlExport(array(
				'RegistryES_id' => $data['RegistryES_id'],
				'RegistryES_xmlExportPath' => ''
			));

			return array('Error_Msg' => '');
		} else {
			$ogrn = $this->getFirstResultFromQuery("select Lpu_Ogrn from v_Lpu (nolock) where Lpu_id = :Lpu_id", array(
				'Lpu_id' => $data['Lpu_id']
			));

			// складываем из XML-ек ROW готовую XML-ку и кладем её в RegistryES, записываем дату эскпорта XML-ки
			$registry_data = json_decode($data['xmls'], true);
			$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n";
			$xml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:fil="http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><soapenv:Header>';

			$rows = array();
			// добавляем ключи и подписи
			foreach ($registry_data as $item) {
				$resp_xml = new DOMDocument();
				$resp_xml->loadXML($item['xml']);

				if (!empty($item['signType']) && in_array($item['signType'], array('authapplet', 'authapi', 'authapitomee'))) {
					// надо в XML подсунуть хэш и подпись
					$resp_xml->getElementsByTagName('DigestValue')->item(0)->nodeValue = $item['Hash'];
					$resp_xml->getElementsByTagName('SignatureValue')->item(0)->nodeValue = $item['SignedData'];
				}

				// сам ЛВН
				$rows[] = $resp_xml->getElementsByTagName('ROW')->item(0)->C14N();
				// подпись МО
				$xml .= $resp_xml->getElementsByTagName('Security')->item(0)->C14N();
				// остальные подписи, полученные ранее
				$xml .= $item['signs'];
			}

			$xml .= '</soapenv:Header>';
			$xml .= '<soapenv:Body>';
			$xml .= '<fil:prParseFilelnlpu>';
			$xml .= '<fil:request>';
			$xml .= '<fil:ogrn>' . $ogrn . '</fil:ogrn>';
			$xml .= '<fil:pXmlFile>';
			$xml .= '<fil:ROWSET fil:author="Promed" fil:email="" fil:phone="" fil:software="Promed" fil:version="1.1" fil:version_software="1">';
			foreach ($rows as $row) {
				$xml .= $row;
			}
			$xml .= '</fil:ROWSET>';
			$xml .= '</fil:pXmlFile>';
			$xml .= '</fil:request>';
			$xml .= '</fil:prParseFilelnlpu>';
			$xml .= '</soapenv:Body>';
			$xml .= '</soapenv:Envelope>';

			$path = EXPORTPATH_ROOT . "registry_es/";

			$out_dir = "re_xml_fss_" . time();
			if (!file_exists($path . $out_dir)) {
				mkdir($path . $out_dir);
			}

			$filepath = $path . $out_dir . "/export_".$data['RegistryES_id']."_" . time() . ".xml";
			$bytes = file_put_contents($filepath, $xml);
			$this->textlog->add("RegistryES_id: " . $data['RegistryES_id'] . ", filepath: " . $filepath . ", bytes: " . $bytes);


			// помечаем реестр готовым к отправке (ставим в очередь)
			$this->setRegistryESXmlExport(array(
				'RegistryES_id' => $data['RegistryES_id'],
				'RegistryES_xmlExportPath' => $filepath
			));

			return array('Error_Msg' => '');
		}
	}

	/**
	 * Проверка ЛВНов в ФСС
	 */
	function checkRegistryESDataInFSS($data)
	{
		$this->load->library('parser');

		$registry_data = json_decode($data['xmls'], true);

		$files_path = EXPORTPATH_ROOT . "registry_es_files";
		if (!file_exists($files_path)) {
			mkdir($files_path);
		}

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
			return array('Error_Msg' => $e->getMessage());
		}

		$encryption = $this->config->item('EvnStickServiceEncryption');

		foreach ($registry_data as $item) {
			$num = (float)$item['num'];
			if (empty($num)) {
				return array('success' => false, 'Error_Msg' => toUtf('Ошибочные входящие данные.'));
			}

			$OurData = array();
			$Evn_id = null;
			$registry_data = $this->getRegistryESDataForCheckInFSS(array(
				'RegistryES_id' => $data['RegistryES_id'],
				'EvnStick_Num' => $num
			));
			if (!empty($registry_data[0]['Evn_id'])) {
				$OurData = $registry_data[0];
				$Evn_id = $registry_data[0]['Evn_id'];
			} else {
				return array('success' => false, 'Error_Msg' => toUtf('Случай не найден в реестре.'));
			}

			if (!empty($Evn_id)) {
				if (!empty($item['signType']) && in_array($item['signType'], array('authapplet', 'authapi', 'authapitomee'))) {
					// надо в XML подсунуть хэш и подпись
					$newxml = new DOMDocument();
					$newxml->loadXML($item['xml']);
					$newxml->getElementsByTagName('DigestValue')->item(0)->nodeValue = $item['Hash'];
					$newxml->getElementsByTagName('SignatureValue')->item(0)->nodeValue = $item['SignedData'];
					$item['xml'] = $newxml->saveXML();
				}

				// сохраняем запрос в файл
				$file_path = $files_path . '/es' . $data['RegistryES_id'] . '_' . $Evn_id . '_request_' . time() . rand(10000,99999) . '.log';
				file_put_contents($file_path, $item['xml']);
				// зипуем, чтобы не занимать много места
				$file_zip_path = $file_path.'.zip';
				$zip = new ZipArchive;
				$zip->open($file_zip_path, ZIPARCHIVE::CREATE);
				$zip->AddFile($file_path, basename($file_path));
				$zip->close();
				$RegistryESFiles_id = $this->saveRegistryESFiles(array(
					'RegistryESFiles_id' => null,
					'RegistryES_id' => $data['RegistryES_id'],
					'Evn_id' => $Evn_id,
					'RegistryESFiles_PathFileRequest' => $file_zip_path,
					'RegistryESFiles_PathFileResponse' => null,
					'pmUser_id' => $data['pmUser_id']
				));
				@unlink($file_path);

				if ($encryption) {
					$this->load->model('RegistryESStorage_model');
					$cert_base64 = $this->RegistryESStorage_model->getCertBase64();
					$xml = $this->RegistryESStorage_model->encodeXmlForTransfer($item['xml'], $cert_base64);
					if (is_array($xml)) {
						// если не строка пришла, значит ошибка какая то
						return $xml;
					}
				} else {
					$xml = $item['xml'];
				}

				try {
					$response = $soapClient->__doRequest($xml, $url, 'getLNData', '1.2');
				} catch (SoapFault $e) {
					// var_dump($e);
					return array('Error_Msg' => $e->getMessage());
				}

				if (!empty($response)) {
					if (!empty($_REQUEST['getDebug'])) {
						echo '<textarea>' . $response . '</textarea>';
					}
					if ($encryption) {
						// надо расшифровать, чтобы получить XML с данными
						$this->load->model('RegistryESStorage_model');
						$xml = $this->RegistryESStorage_model->decodeXmlForTransfer($response);
						if (is_array($xml)) {
							// если не строка пришла, значит ошибка какая то
							return $xml;
						}
					} else {
						$xml = $response;
					}

					// сохраняем ответ в файл
					$file_path_resp = $files_path . '/es' . $data['RegistryES_id'] . '_' . $Evn_id . '_response_' . time() . rand(10000,99999) . '.log';
					file_put_contents($file_path_resp, $xml);
					// зипуем, чтобы не занимать много места
					$file_zip_path_resp = $file_path_resp.'.zip';
					$zip = new ZipArchive;
					$zip->open($file_zip_path_resp, ZIPARCHIVE::CREATE);
					$zip->AddFile($file_path_resp, basename($file_path_resp));
					$zip->close();
					$this->saveRegistryESFiles(array(
						'RegistryESFiles_id' => $RegistryESFiles_id,
						'RegistryES_id' => $data['RegistryES_id'],
						'Evn_id' => $Evn_id,
						'RegistryESFiles_PathFileRequest' => $file_zip_path,
						'RegistryESFiles_PathFileResponse' => $file_zip_path_resp,
						'pmUser_id' => $data['pmUser_id']
					));
					@unlink($file_path_resp);

					$resp_xml = new DOMDocument();
					$resp_xml->loadXML($xml);

					$fault_arr = array();
					$faults = $resp_xml->getElementsByTagName('faultstring');
					foreach ($faults as $fault) {
						$fault_arr[] = $fault->nodeValue;
					}

					if (count($fault_arr) > 0) {
						return array('Error_Msg' => $fault_arr[0]);
					}

					$FileOperationsLnUserGetLNDataOut = $resp_xml->getElementsByTagName('FileOperationsLnUserGetLNDataOut')->item(0);
					if (!empty($FileOperationsLnUserGetLNDataOut)) {
						$status = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('STATUS')->item(0);
						if (!empty($status)) {
							if ($status->nodeValue == '1') {
								$StickFSSDataGet = array(
									'StickFSSData_id' => null,
									'Person_Snils' => null,
									'Person_SurName' => null,
									'Person_FirName' => null,
									'Person_SecName' => null,
									'Person_BirthDay' => null,
									'Sex_id' => null,
									'StickFSSData_StickNum' => null,
									'StickFSSDataGet_StickFirstNum' => null,
									'StickFSSDataGet_IsStickFirst' => null,
									'StickFSSDataGet_IsStickDouble' => null,
									'StickFSSDataGet_StickSetDate' => null,
									'StickFSSDataGet_StickReason' => null,
									'StickFSSDataGet_StickReasonDop' => null,
									'StickCause_CodeAlter' => null,
									'Diag_Code' => null,
									'StickFSSDataGet_StickResult' => null,
									'StickFSSDataGet_changeDate' => null,
									'StickFSSDataGet_workDate' => null,
									'StickFSSDataGet_StickNextNum' => null,
									'StickFSSDataGet_StickStatus' => null,
									'StickFSSDataGet_FirstPostVK' => null,
									'StickFSSDataGet_FirstBegDate' => null,
									'StickFSSDataGet_FirstEndDate' => null,
									'StickFSSDataGet_FirstPost' => null,
									'StickFSSDataGet_SecondPostVK' => null,
									'StickFSSDataGet_SecondBegDate' => null,
									'StickFSSDataGet_SecondEndDate' => null,
									'StickFSSDataGet_SecondPost' => null,
									'StickFSSDataGet_ThirdPostVK' => null,
									'StickFSSDataGet_ThirdBegDate' => null,
									'StickFSSDataGet_ThirdEndDate' => null,
									'StickFSSDataGet_ThirdPost' => null,
									'StickFSSDataGet_IsEdit' => null,
									'Lpu_StickNick' => null,
									'MedPersonal_FirstFIO' => null,
									'MedPersonalVK_FirstFIO' => null,
									'MedPersonal_SecondFIO' => null,
									'MedPersonalVK_SecondFIO' => null,
									'MedPersonal_ThirdFIO' => null,
									'MedPersonalVK_ThirdFIO' => null,
									'StickFSSDataGet_IsEmploymentServices' => null,
									'Org_StickNick' => null,
									'StickFSSDataGet_EmploymentType' => null,
									'Lpu_StickAddress' => null,
									'Lpu_OGRN' => null,
									'EvnStick_NumPar' => null,
									'EvnStick_StickDT' => null,
									'EvnStick_sstEndDate' => null,
									'EvnStick_sstNum' => null,
									'Org_sstOGRN' => null,
									'FirstRelated_Age' => null,
									'FirstRelated_AgeMonth' => null,
									'FirstRelatedLinkType_Code' => null,
									'FirstRelated_FIO' => null,
									'FirstRelated_begDate' => null,
									'FirstRelated_endDate' => null,
									'SecondRelated_Age' => null,
									'SecondRelated_AgeMonth' => null,
									'SecondRelatedLinkType_Code' => null,
									'SecondRelated_FIO' => null,
									'SecondRelated_begDate' => null,
									'SecondRelated_endDate' => null,
									'EvnStick_IsRegPregnancy' => null,
									'EvnStick_stacBegDate' => null,
									'EvnStick_stacEndDate' => null,
									'StickIrregularity_Code' => null,
									'EvnStick_irrDT' => null,
									'EvnStick_mseDT' => null,
									'EvnStick_mseRegDT' => null,
									'EvnStick_mseExamDT' => null,
									'InvalidGroupType_id' => null,
									'FirstPostVK_Code' => null,
									'SecondPostVK_Code' => null,
									'ThirdPostVK_Code' => null,
									'StickFSSDataGet_Hash' => null,
									'pmUser_id' => $data['pmUser_id']
								);
								// достаём данные из XML
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SNILS');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Person_Snils'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SURNAME');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Person_SurName'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('NAME');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Person_FirName'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PATRONIMIC');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Person_SecName'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('BIRTHDAY');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Person_BirthDay'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('GENDER');
								if ($EL->length > 0) {
									$Sex_id = null;
									switch($EL->item(0)->nodeValue) {
										case '0':
											$Sex_id = 1;
											break;
										case '1':
											$Sex_id = 2;
											break;
									}
									$StickFSSDataGet['Sex_id'] = $Sex_id;
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_CODE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSData_StickNum'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PREV_LN_CODE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_StickFirstNum'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PRIMARY_FLAG');
								if ($EL->length > 0) {
									$StickFSSDataGet_IsStickFirst = null;
									switch($EL->item(0)->nodeValue) {
										case '0':
											$StickFSSDataGet_IsStickFirst = 1;
											break;
										case '1':
											$StickFSSDataGet_IsStickFirst = 2;
											break;
									}
									$StickFSSDataGet['StickFSSDataGet_IsStickFirst'] = $StickFSSDataGet_IsStickFirst;
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DUPLICATE_FLAG');
								if ($EL->length > 0) {
									$StickFSSDataGet_IsStickDouble = null;
									switch($EL->item(0)->nodeValue) {
										case '0':
											$StickFSSDataGet_IsStickDouble = 1;
											break;
										case '1':
											$StickFSSDataGet_IsStickDouble = 2;
											break;
									}
									$StickFSSDataGet['StickFSSDataGet_IsStickDouble'] = $StickFSSDataGet_IsStickDouble;
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_DATE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_StickSetDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON1');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_StickReason'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON2');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_StickReasonDop'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON3');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickCause_CodeAlter'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DIAGNOS');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Diag_Code'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_RESULT');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_StickResult'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('OTHER_STATE_DT');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_changeDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('RETURN_DATE_LPU');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_workDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('NEXT_LN_CODE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_StickNextNum'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_STATE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_StickStatus'] = $nodeValue;
									}
								}
								$TFP = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('TREAT_FULL_PERIOD');
								if ($TFP->length > 0) {
									$TreatPeriod = $TFP->item(0);
									$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_FirstPostVK'] = $EL->item(0)->nodeValue;
										$StickFSSDataGet['FirstPostVK_Code'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
									if ($EL->length > 0) {
										$StickFSSDataGet['MedPersonal_FirstFIO'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
									if ($EL->length > 0) {
										$StickFSSDataGet['MedPersonalVK_FirstFIO'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_FirstBegDate'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_FirstEndDate'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_FirstPost'] = $EL->item(0)->nodeValue;
									}
								}
								if ($TFP->length > 1) {
									$TreatPeriod = $TFP->item(1);
									$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_SecondPostVK'] = $EL->item(0)->nodeValue;
										$StickFSSDataGet['SecondPostVK_Code'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
									if ($EL->length > 0) {
										$StickFSSDataGet['MedPersonal_SecondFIO'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
									if ($EL->length > 0) {
										$StickFSSDataGet['MedPersonalVK_SecondFIO'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_SecondBegDate'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_SecondEndDate'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_SecondPost'] = $EL->item(0)->nodeValue;
									}
								}
								if ($TFP->length > 2) {
									$TreatPeriod = $TFP->item(2);
									$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_ThirdPostVK'] = $EL->item(0)->nodeValue;
										$StickFSSDataGet['ThirdPostVK_Code'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
									if ($EL->length > 0) {
										$StickFSSDataGet['MedPersonal_ThirdFIO'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
									if ($EL->length > 0) {
										$StickFSSDataGet['MedPersonalVK_ThirdFIO'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_ThirdBegDate'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_ThirdEndDate'] = $EL->item(0)->nodeValue;
									}
									$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
									if ($EL->length > 0) {
										$StickFSSDataGet['StickFSSDataGet_ThirdPost'] = $EL->item(0)->nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_NAME');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Lpu_StickNick'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('BOZ_FLAG');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_IsEmploymentServices'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_EMPLOYER');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Org_StickNick'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_EMPL_FLAG');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (isset($nodeValue) && mb_strlen($nodeValue) > 0) {
										$StickFSSDataGet['StickFSSDataGet_EmploymentType'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_ADDRESS');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Lpu_StickAddress'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_OGRN');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Lpu_OGRN'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PARENT_CODE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_NumPar'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DATE1');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_StickDT'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DATE2');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_sstEndDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('VOUCHER_NO');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_sstNum'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('VOUCHER_OGRN');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['Org_sstOGRN'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_AGE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['FirstRelated_Age'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_MM');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['FirstRelated_AgeMonth'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_RELATION_CODE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['FirstRelatedLinkType_Code'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_FIO');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['FirstRelated_FIO'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_DT1');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['FirstRelated_begDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_DT2');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['FirstRelated_endDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_AGE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['SecondRelated_Age'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_MM');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['SecondRelated_AgeMonth'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_RELATION_CODE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['SecondRelatedLinkType_Code'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_FIO');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['SecondRelated_FIO'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_DT1');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['SecondRelated_begDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_DT2');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['SecondRelated_endDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PREGN12W_FLAG');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (isset($nodeValue) && mb_strlen($nodeValue) > 0) {
										$StickFSSDataGet['EvnStick_IsRegPregnancy'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_DT1');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_stacBegDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_DT2');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_stacEndDate'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_BREACH_CODE');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickIrregularity_Code'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_BREACH_DT');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_irrDT'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT1');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_mseDT'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT2');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_mseRegDT'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT3');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['EvnStick_mseExamDT'] = $nodeValue;
									}
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_INVALID_GROUP');
								if ($EL->length > 0) {
									$InvalidGroupType_id = null;
									switch($EL->item(0)->nodeValue) {
										case '1':
											$InvalidGroupType_id = 2;
											break;
										case '2':
											$InvalidGroupType_id = 3;
											break;
										case '3':
											$InvalidGroupType_id = 4;
											break;
									}
									$StickFSSDataGet['InvalidGroupType_id'] = $InvalidGroupType_id;
								}
								$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_HASH');
								if ($EL->length > 0) {
									$nodeValue = $EL->item(0)->nodeValue;
									if (!empty($nodeValue)) {
										$StickFSSDataGet['StickFSSDataGet_Hash'] = $nodeValue;
									}
								}

								$StickFSSDataGet['EvnStickBase_id'] = $Evn_id;
								$StickFSSDataGet['FirstEvnStickWorkRelease_id'] = $OurData['FirstEvnStickWorkRelease_id'];
								$StickFSSDataGet['SecondEvnStickWorkRelease_id'] = $OurData['SecondEvnStickWorkRelease_id'];
								$StickFSSDataGet['ThirdEvnStickWorkRelease_id'] = $OurData['ThirdEvnStickWorkRelease_id'];
								$StickFSSDataGet['StickFSSDataGet_id'] = null;
								$proc_sfssdg = 'p_StickFSSDataGet_ins';
								$result_check = $this->dbMain->query("
									select top 1
										StickFSSDataGet_id
									from
										v_StickFSSDataGet (nolock)
									where
										EvnStickBase_id = :EvnStickBase_id
										and StickFSSData_id is null
								", array(
									'EvnStickBase_id' => $Evn_id
								));
								$resp_check = $result_check->result('array');
								if (!empty($resp_check[0]['StickFSSDataGet_id'])) {
									$StickFSSDataGet['StickFSSDataGet_id'] = $resp_check[0]['StickFSSDataGet_id'];
									$proc_sfssdg = 'p_StickFSSDataGet_upd';
								}

								// записываем данные в StickFSSDataGet
								$result_save = $this->dbMain->query("
									declare
										@StickFSSDataGet_id bigint = :StickFSSDataGet_id,
										@Error_Code int,
										@Error_Message varchar(4000);
						
									exec {$proc_sfssdg}
										@StickFSSDataGet_id = @StickFSSDataGet_id output,
										@EvnStickBase_id = :EvnStickBase_id,
										@StickFSSData_id = :StickFSSData_id,
										@Person_Snils = :Person_Snils,
										@Person_SurName = :Person_SurName,
										@Person_FirName = :Person_FirName,
										@Person_SecName = :Person_SecName,
										@Person_BirthDay = :Person_BirthDay,
										@Sex_id = :Sex_id,
										@StickFSSDataGet_StickNum = :StickFSSData_StickNum,
										@StickFSSDataGet_StickFirstNum = :StickFSSDataGet_StickFirstNum,
										@StickFSSDataGet_IsStickFirst = :StickFSSDataGet_IsStickFirst,
										@StickFSSDataGet_IsStickDouble = :StickFSSDataGet_IsStickDouble,
										@StickFSSDataGet_StickSetDate = :StickFSSDataGet_StickSetDate,
										@StickFSSDataGet_StickReason = :StickFSSDataGet_StickReason,
										@StickFSSDataGet_StickReasonDop = :StickFSSDataGet_StickReasonDop,
										@StickCause_CodeAlter = :StickCause_CodeAlter,
										@Diag_Code = :Diag_Code,
										@StickFSSDataGet_StickResult = :StickFSSDataGet_StickResult,
										@StickFSSDataGet_changeDate = :StickFSSDataGet_changeDate,
										@StickFSSDataGet_workDate = :StickFSSDataGet_workDate,
										@StickFSSDataGet_StickNextNum = :StickFSSDataGet_StickNextNum,
										@StickFSSDataGet_StickStatus = :StickFSSDataGet_StickStatus,
										@StickFSSDataGet_FirstPostVK = :StickFSSDataGet_FirstPostVK,
										@StickFSSDataGet_FirstBegDate = :StickFSSDataGet_FirstBegDate,
										@StickFSSDataGet_FirstEndDate = :StickFSSDataGet_FirstEndDate,
										@StickFSSDataGet_FirstPost = :StickFSSDataGet_FirstPost,
										@StickFSSDataGet_SecondPostVK = :StickFSSDataGet_SecondPostVK,
										@StickFSSDataGet_SecondBegDate = :StickFSSDataGet_SecondBegDate,
										@StickFSSDataGet_SecondEndDate = :StickFSSDataGet_SecondEndDate,
										@StickFSSDataGet_SecondPost = :StickFSSDataGet_SecondPost,
										@StickFSSDataGet_ThirdPostVK = :StickFSSDataGet_ThirdPostVK,
										@StickFSSDataGet_ThirdBegDate = :StickFSSDataGet_ThirdBegDate,
										@StickFSSDataGet_ThirdEndDate = :StickFSSDataGet_ThirdEndDate,
										@StickFSSDataGet_ThirdPost = :StickFSSDataGet_ThirdPost,
										@StickFSSDataGet_IsEdit = :StickFSSDataGet_IsEdit,
										@Lpu_StickNick = :Lpu_StickNick,
										@MedPersonal_FirstFIO = :MedPersonal_FirstFIO,
										@MedPersonalVK_FirstFIO = :MedPersonalVK_FirstFIO,
										@MedPersonal_SecondFIO = :MedPersonal_SecondFIO,
										@MedPersonalVK_SecondFIO = :MedPersonalVK_SecondFIO,
										@MedPersonal_ThirdFIO = :MedPersonal_ThirdFIO,
										@MedPersonalVK_ThirdFIO = :MedPersonalVK_ThirdFIO,
										@StickFSSDataGet_IsEmploymentServices = :StickFSSDataGet_IsEmploymentServices,
										@Org_StickNick = :Org_StickNick,
										@StickFSSDataGet_EmploymentType = :StickFSSDataGet_EmploymentType,
										@Lpu_StickAddress = :Lpu_StickAddress,
										@Lpu_OGRN = :Lpu_OGRN,
										@EvnStick_NumPar = :EvnStick_NumPar,
										@EvnStick_StickDT = :EvnStick_StickDT,
										@EvnStick_sstEndDate = :EvnStick_sstEndDate,
										@EvnStick_sstNum = :EvnStick_sstNum,
										@Org_sstOGRN = :Org_sstOGRN,
										@FirstRelated_Age = :FirstRelated_Age,
										@FirstRelated_AgeMonth = :FirstRelated_AgeMonth,
										@FirstRelatedLinkType_Code = :FirstRelatedLinkType_Code,
										@FirstRelated_FIO = :FirstRelated_FIO,
										@FirstRelated_begDate = :FirstRelated_begDate,
										@FirstRelated_endDate = :FirstRelated_endDate,
										@SecondRelated_Age = :SecondRelated_Age,
										@SecondRelated_AgeMonth = :SecondRelated_AgeMonth,
										@SecondRelatedLinkType_Code = :SecondRelatedLinkType_Code,
										@SecondRelated_FIO = :SecondRelated_FIO,
										@SecondRelated_begDate = :SecondRelated_begDate,
										@SecondRelated_endDate = :SecondRelated_endDate,
										@EvnStick_IsRegPregnancy = :EvnStick_IsRegPregnancy,
										@EvnStick_stacBegDate = :EvnStick_stacBegDate,
										@EvnStick_stacEndDate = :EvnStick_stacEndDate,
										@StickIrregularity_Code = :StickIrregularity_Code,
										@EvnStick_irrDT = :EvnStick_irrDT,
										@EvnStick_mseDT = :EvnStick_mseDT,
										@EvnStick_mseRegDT = :EvnStick_mseRegDT,
										@EvnStick_mseExamDT = :EvnStick_mseExamDT,
										@InvalidGroupType_id = :InvalidGroupType_id,
										@FirstPostVK_Code = :FirstPostVK_Code,
										@SecondPostVK_Code = :SecondPostVK_Code,
										@ThirdPostVK_Code = :ThirdPostVK_Code,
										@StickFSSDataGet_Hash = :StickFSSDataGet_Hash,
										@FirstEvnStickWorkRelease_id = :FirstEvnStickWorkRelease_id,
										@SecondEvnStickWorkRelease_id = :SecondEvnStickWorkRelease_id,
										@ThirdEvnStickWorkRelease_id = :ThirdEvnStickWorkRelease_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @Error_Code OUTPUT,
										@Error_Message = @Error_Message OUTPUT
									select @StickFSSDataGet_id as StickFSSDataGet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
								", $StickFSSDataGet);

								$resp_save = $result_save->result('array');
								if (!empty($resp_save[0]['Error_Msg'])) {
									return array('Error_Msg' => $resp_save[0]['Error_Msg']);
								}

								if (!empty($resp_save[0]['StickFSSDataGet_id'])) {
									// Обновляем статус ЛВН
									$this->dbMain->query("
										update 
											esb with (rowlock)
										set
											esb.StickFSSType_id = sft.StickFSSType_id
										from
											EvnStickBase esb
											inner join v_StickFSSDataGet sfdg (nolock) on sfdg.EvnStickBase_id = esb.EvnStickBase_id
											inner join v_StickFSSType sft (nolock) on sft.StickFSSType_Code = sfdg.StickFSSDataGet_StickStatus
										where
											sfdg.StickFSSDataGet_id = :StickFSSDataGet_id							 
									", array(
										'StickFSSDataGet_id' => $resp_save[0]['StickFSSDataGet_id']
									));
								}

								// в ответе статус ЛВН "040 направление на МСЭ"
								if (!empty($StickFSSDataGet['StickFSSDataGet_StickStatus']) && $StickFSSDataGet['StickFSSDataGet_StickStatus'] == '040') {
									//проверяем наличие запроса в ФСС (данных по МСЭ)
									$query = "
										select top 1 
											StickFSSData_id,
											StickFSSData_StickNum,
											StickFSSData_IsNeedMSE
										from v_StickFSSData
										where 
											StickFSSData_StickNum = :StickFSSData_StickNum
											and StickFSSData_IsNeedMSE = 2
									";
									$resp = $this->db->query($query, array('StickFSSData_StickNum' => $StickFSSDataGet['StickFSSData_StickNum']));

									if (is_object($resp)) {
										$result = $resp->result('array');
										if (is_array($result) && empty($result)) {
											// добавляем запрос для получения данных о МСЭ
											$this->addRequestMSE($data, $data['RegistryES_id'], $StickFSSDataGet['StickFSSData_StickNum']);
										}
									}
								}

								if (!empty($StickFSSDataGet['StickFSSDataGet_StickStatus']) && $StickFSSDataGet['StickFSSDataGet_StickStatus'] == '090') {
									// если в ответе вернулся статус «090 Действия прекращены», то производится аннулирование ЛВН в Промеде
									// ЛВН удаляется из Системы (путем простановки признака _isDel)
									$this->dbMain->query("
										declare
											@ErrCode int,
											@ErrMessage varchar(4000);
										exec p_EvnStick_del
											@EvnStick_id = :EvnStick_id,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
										select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									", array(
										'EvnStick_id' => $Evn_id,
										'pmUser_id' => $data['pmUser_id']
									));
								}

								$diff = false;
								foreach($StickFSSDataGet as $key => $value) {
									if (!empty($value) && in_array($key, array('Person_BirthDay', 'StickFSSDataGet_StickSetDate', 'StickFSSDataGet_FirstBegDate', 'StickFSSDataGet_FirstEndDate', 'StickFSSDataGet_SecondBegDate', 'StickFSSDataGet_SecondEndDate', 'StickFSSDataGet_ThirdBegDate', 'StickFSSDataGet_ThirdEndDate'))) {
										$StickFSSDataGet[$key] = date('d.m.Y', strtotime($value));
									}
								}

								if (!empty($StickFSSDataGet['StickFSSDataGet_StickReason'])) {
									$resp_spr = $this->queryResult("select top 1 StickCause_Name from v_StickCause with (nolock) where StickCause_Code = :StickFSSDataGet_StickReason", array(
										'StickFSSDataGet_StickReason' => $StickFSSDataGet['StickFSSDataGet_StickReason']
									));
									$StickFSSDataGet['StickCause_Name'] = $StickFSSDataGet['StickFSSDataGet_StickReason'] . ' ' . (!empty($resp_spr[0]['StickCause_Name']) ? $resp_spr[0]['StickCause_Name'] : '');
								}
								if (!empty($StickFSSDataGet['StickFSSDataGet_StickResult'])) {
									$resp_spr = $this->queryResult("select top 1 StickLeaveType_Name from v_StickLeaveType with (nolock) where StickLeaveType_Code = :StickFSSDataGet_StickResult", array(
										'StickFSSDataGet_StickResult' => $StickFSSDataGet['StickFSSDataGet_StickResult']
									));
									$StickFSSDataGet['StickLeaveType_Name'] = $StickFSSDataGet['StickFSSDataGet_StickResult'] . ' ' . (!empty($resp_spr[0]['StickLeaveType_Name']) ? $resp_spr[0]['StickLeaveType_Name'] : '');
								}
								if (!empty($StickFSSDataGet['StickFSSDataGet_StickStatus'])) {
									$resp_spr = $this->queryResult("select top 1 StickFSSType_Name from v_StickFSSType with (nolock) where StickFSSType_Code = :StickFSSDataGet_StickStatus", array(
										'StickFSSDataGet_StickStatus' => $StickFSSDataGet['StickFSSDataGet_StickStatus']
									));
									$StickFSSDataGet['StickFSSType_Name'] = $StickFSSDataGet['StickFSSDataGet_StickStatus'] . ' ' . (!empty($resp_spr[0]['StickFSSType_Name']) ? $resp_spr[0]['StickFSSType_Name'] : '');
								}

								$StickFSSDataGet['Person_Fio'] = $StickFSSDataGet['Person_SurName'] . ' ' . $StickFSSDataGet['Person_FirName'] . ' ' . $StickFSSDataGet['Person_SecName'];
								$StickFSSDataGet['Lpu_Info'] = $StickFSSDataGet['Lpu_StickNick'] . ', ' . $StickFSSDataGet['Lpu_StickAddress'];

								if ($diff) { // если есть расхождения
									return array('Error_Msg' => '', 'StickData' => $StickFSSDataGet, 'EvnStick_Num' => $num);
								}

								if (!empty($StickFSSDataGet['StickFSSDataGet_Hash'])) {
									// получили хэш
									$this->db->query("update RegistryESData with (rowlock) set RegistryESData_Hash = :RegistryESData_Hash where Evn_id = :Evn_id",
										array(
											'RegistryESData_Hash' => $StickFSSDataGet['StickFSSDataGet_Hash'],
											'Evn_id' => $Evn_id
										)
									);

									if ($OurData['RegistryESStatus_Code'] == 8) {
										// снимаем признаки
										$this->updateEvnStickNotInReg(array(
											'RegistryES_id' => $OurData['RegistryES_id'],
											'Evn_id' => $Evn_id
										));

										$RegistryESDataStatus_id = 2; // принят
										if (
											// у нас есть освобождение, а в ФСС нет, значит не принят
											(empty($StickFSSDataGet['StickFSSDataGet_FirstBegDate']) && !empty($OurData['FirstEvnStickWorkRelease_id']))
											|| (empty($StickFSSDataGet['StickFSSDataGet_SecondBegDate']) && !empty($OurData['SecondEvnStickWorkRelease_id']))
											|| (empty($StickFSSDataGet['StickFSSDataGet_ThirdBegDate']) && !empty($OurData['ThirdEvnStickWorkRelease_id']))
											// у нас есть исход, а в ФСС нет, значит не принят
											|| (
												empty($StickFSSDataGet['StickFSSDataGet_StickResult'])
												&& empty($StickFSSDataGet['StickFSSDataGet_changeDate'])
												&& empty($StickFSSDataGet['StickFSSDataGet_workDate'])
												&& !empty($OurData['StickLeaveType_Code'])
												&& !empty($OurData['EvnStick_disDate'])
												&& !empty($OurData['EvnStick_returnDate'])
											)
										) {
											$RegistryESDataStatus_id = 3; // не принят
										}

										// меняем статус записи в реестре на "Принят ФСС"
										$this->db->query("update RegistryESData with (rowlock) set RegistryESDataStatus_id = :RegistryESDataStatus_id where RegistryES_id = :RegistryES_id and Evn_id = :Evn_id", array(
											'RegistryES_id' => $OurData['RegistryES_id'],
											'Evn_id' => $Evn_id,
											'RegistryESDataStatus_id' => $RegistryESDataStatus_id
										));
									}

									// проставялем IsInReg = 2, IsPaid = 2 всем пришедшим из ФСС освобождениям и ЭЛН при наличии исхода
									if (!empty($StickFSSDataGet['StickFSSDataGet_FirstBegDate']) && !empty($OurData['FirstEvnStickWorkRelease_id'])) {
										$this->db->query("
											update EvnStickWorkRelease with (rowlock) set EvnStickWorkRelease_IsInReg = 2, EvnStickWorkRelease_IsPaid = 2 where EvnStickWorkRelease_id = :EvnStickWorkRelease_id
										", array(
											'EvnStickWorkRelease_id' => $OurData['FirstEvnStickWorkRelease_id']
										));
									}
									if (!empty($StickFSSDataGet['StickFSSDataGet_SecondBegDate']) && !empty($OurData['SecondEvnStickWorkRelease_id'])) {
										$this->db->query("
											update EvnStickWorkRelease with (rowlock) set EvnStickWorkRelease_IsInReg = 2, EvnStickWorkRelease_IsPaid = 2 where EvnStickWorkRelease_id = :EvnStickWorkRelease_id
										", array(
											'EvnStickWorkRelease_id' => $OurData['SecondEvnStickWorkRelease_id']
										));
									}
									if (!empty($StickFSSDataGet['StickFSSDataGet_ThirdBegDate']) && !empty($OurData['ThirdEvnStickWorkRelease_id'])) {
										$this->db->query("
											update EvnStickWorkRelease with (rowlock) set EvnStickWorkRelease_IsInReg = 2, EvnStickWorkRelease_IsPaid = 2 where EvnStickWorkRelease_id = :EvnStickWorkRelease_id
										", array(
											'EvnStickWorkRelease_id' => $OurData['ThirdEvnStickWorkRelease_id']
										));
									}
									if (!empty($StickFSSDataGet['StickFSSDataGet_StickResult']) || !empty($StickFSSDataGet['StickFSSDataGet_changeDate']) || !empty($StickFSSDataGet['StickFSSDataGet_workDate'])) {
										$this->db->query("
											update EvnStickBase with (rowlock) set EvnStickBase_IsInReg = 2, EvnStickBase_IsPaid = 2 where EvnStickBase_id = :EvnStickBase_id
										", array(
											'EvnStickBase_id' => $Evn_id
										));
									}
								} else {
									if ($OurData['RegistryESStatus_Code'] == 8) {
										// снимаем признаки
										$this->updateEvnStickNotInReg(array(
											'RegistryES_id' => $OurData['RegistryES_id'],
											'Evn_id' => $Evn_id
										));

										// меняем статус записи в реестре на "Не принят ФСС"
										$this->db->query("update RegistryESData with (rowlock) set RegistryESDataStatus_id = 3 where RegistryES_id = :RegistryES_id and Evn_id = :Evn_id", array(
											'RegistryES_id' => $OurData['RegistryES_id'],
											'Evn_id' => $Evn_id
										));
									}

									return array('Error_Msg' => 'ЭЛН не найден в сервисе ФСС');
								}
							} else {
								$message = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MESS')->item(0);
								if (!empty($message)) {
									if ($OurData['RegistryESStatus_Code'] == 8 && mb_strpos($message->nodeValue, 'ORA-20001') !== false) {
										// снимаем признаки
										$this->updateEvnStickNotInReg(array(
											'RegistryES_id' => $OurData['RegistryES_id'],
											'Evn_id' => $Evn_id
										));

										// меняем статус записи в реестре на "Не принят ФСС"
										$this->db->query("update RegistryESData with (rowlock) set RegistryESDataStatus_id = 3 where RegistryES_id = :RegistryES_id and Evn_id = :Evn_id", array(
											'RegistryES_id' => $OurData['RegistryES_id'],
											'Evn_id' => $Evn_id
										));
									}
									// выводим ошибку пользователю, ничего не делаем.
									return array('Error_Msg' => $message->nodeValue);
								}
							}
						} else {
							return array('Error_Msg' => 'В ответе нет тэга STATUS');
						}
					} else {
						return array('Error_Msg' => 'В ответе нет тэга FileOperationsLnUserGetLNDataOut');
					}
				} else {
					return array('Error_Msg' => 'Пустой ответ сервиса');
				}
			} else {
				return array('Error_Msg' => 'ЭЛН не найден в реестре');
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Просмотр файлов
	 */
	function showFiles($data) {
		$queryParams = array(
			'RegistryES_id' => $data['RegistryES_id']
		);
		$filter = "";
		if (!empty($data['Evn_id'])) {
			$queryParams['Evn_id'] = $data['Evn_id'];
			$filter .= " and Evn_id = :Evn_id";
		} else {
			$filter .= " and Evn_id is null";
		}
		$resp = $this->queryResult("
			select
				RegistryESFiles_PathFileRequest as request,
				RegistryESFiles_PathFileResponse as response,
				convert(varchar(19), RegistryESFiles_insDT, 120) as date
			from
				RegistryESFiles (nolock)
			where
				RegistryES_id = :RegistryES_id
				{$filter}
			order by
				RegistryESFiles_insDT desc
		", $queryParams);

		echo "<html>";
		echo "<style type='text/css'> table td { border: 1px solid #000; padding: 10px; } table { border-collapse: collapse; } </style>";
		if (empty($resp)) {
			echo "Логов отправки по данному реестру нет.";
		} else {
			echo "<table>";
			echo "<tr><td>Дата</td><td>Запрос</td><td>Ответ</td></tr>";
			foreach ($resp as $respone) {
				if (!empty($respone['request'])) {
					$respone['request'] = "<a href='{$respone['request']}'>" . basename($respone['request']) . "</a>";
				}
				if (!empty($respone['response'])) {
					$respone['response'] = "<a href='{$respone['response']}'>" . basename($respone['response']) . "</a>";
				}
				echo "<tr><td>{$respone['date']}</td><td>{$respone['request']}</td><td>{$respone['response']}</td></tr>";
			}
			echo "</table>";
		}

		echo "</html>";
	}

	/**
	 * Сохранение записи в историю
	 */
	function saveRegistryESFiles($data) {
		if (!empty($data['RegistryESFiles_id'])) {
			$proc = "p_RegistryESFiles_upd";
		} else {
			$proc = "p_RegistryESFiles_ins";
			$data['RegistryESFiles_id'] = null;
		}

		$resp = $this->queryResult("
			declare
				@RegistryESFiles_id bigint = :RegistryESFiles_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$proc}
				@RegistryESFiles_id = @RegistryESFiles_id output,
				@RegistryES_id = :RegistryES_id,
				@Evn_id = :Evn_id,
				@RegistryESFiles_PathFileRequest = :RegistryESFiles_PathFileRequest,
				@RegistryESFiles_PathFileResponse = :RegistryESFiles_PathFileResponse,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @RegistryESFiles_id as RegistryESFiles_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		", array(
			'RegistryESFiles_id' => $data['RegistryESFiles_id'],
			'RegistryES_id' => $data['RegistryES_id'],
			'Evn_id' => !empty($data['Evn_id']) ? $data['Evn_id'] : null,
			'RegistryESFiles_PathFileRequest' => $data['RegistryESFiles_PathFileRequest'],
			'RegistryESFiles_PathFileResponse' => $data['RegistryESFiles_PathFileResponse'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp[0]['RegistryESFiles_id'])) {
			return $resp[0]['RegistryESFiles_id'];
		}

		return null;
	}

	/**
	 * Отправка сообщения об ошибке отправки реестра администратору МО
	 */
	function sendErrorMessage($RegistryES_id, $data) {

		$query = "
			select top 1
				REST.RegistryESType_Name,
				RES.Lpu_id,
				RES.RegistryES_Num,
				convert(varchar(10), RES.RegistryES_begDate, 104) as RegistryES_begDate,
				LB.LpuBuilding_Name
			from 
				v_RegistryES RES with (nolock)
				inner join v_RegistryESType REST with (nolock) on REST.RegistryESType_id = RES.RegistryESType_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = RES.LpuBuilding_id
			where
				RES.RegistryES_id = :RegistryES_id
		";
		$queryParams = array('RegistryES_id' => $RegistryES_id);

		$reg_resp = $this->getFirstRowFromQuery($query, $queryParams);
		if (!empty($reg_resp)) {
			$this->load->model('Messages_model');
			// получаем пользователей данной МО с группами:
			// - пользователь АРМ администратора МО;
			// - пользователь АРМ медицинского статистика.
			$resp_users = $this->queryResult("
				DECLARE
					@pmUserCacheGroup_id bigint = (select top 1 pmUserCacheGroup_id from dbo.pmUserCacheGroup where pmUserCacheGroup_SysNick = 'LpuAdmin')
				
				select
					pmUser_id
				from
					v_pmUserCache puc (nolock)
				where
					puc.Lpu_id = :Lpu_id
					and exists(select top 1 pucgl.pmUserCacheGroupLink_id from v_pmUserCacheGroupLink pucgl with (nolock) where pucgl.pmUserCacheGroup_id = @pmUserCacheGroup_id and pucgl.pmUserCache_id = puc.PMUser_id) -- АРМ администратора МО
					and ISNULL(puc.pmUser_deleted, 1) = 1
					--and puc.pmUser_EvnClass like '%StickFSSData%' --подписка на уведомления
			", array(
				'Lpu_id' => $reg_resp['Lpu_id']
			));


			if (!empty($resp_users)) {
				$user = getUser();
				$message = "При отправке реестра ЛВН с типом {$reg_resp['RegistryESType_Name']} № {$reg_resp['RegistryES_Num']}, сформированного на {$reg_resp['RegistryES_begDate']} {$reg_resp['LpuBuilding_Name']}, в ФСС произошла ошибка." . PHP_EOL . PHP_EOL;
				$message .= "Для ЛВН со статусом «Требуется проверка ЭЛН в ФСС» необходимо выполнить проверку в ФСС.";

				$noticeData = array(
					'autotype' => 5,
					'pmUser_id' => $data['pmUser_id'],
					'EvnClass_SysNick' => 'StickFSSData',
					'type' => 1,
					'title' => 'Ошибка отправки реестра ЭЛН.',
					'text' => $message
				);

				foreach($resp_users as $one_user) {
					$noticeData['User_rid'] = $one_user['pmUser_id'];
					$this->Messages_model->autoMessage($noticeData);
				}
			}
		}
	}

	/**
	 * Отправка реестров в ФСС
	 */
	function sendRegistryESToFSS($data) {
		set_time_limit(0);

		$this->load->library('textlog', array('file'=>'sendRegistryESToFSS_'.date('Y-m-d').'.log'));
		$this->textlog->add("sendRegistryESToFSS begin");

		$filter = "";
		$queryParams = array();
		if (!empty($data['RegistryES_id'])) {
			$filter .= " and RegistryES_id = :RegistryES_id";
			$queryParams['RegistryES_id'] = $data['RegistryES_id'];
		}

		$files_path = EXPORTPATH_ROOT . "registry_es_files";
		if (!file_exists($files_path)) {
			mkdir($files_path);
		}

		// берем очередь реестров
		$resp = $this->queryResult("
			select
				RegistryES_id,
				RegistryESType_id,
				RegistryES_xmlExportPath
			from
				v_RegistryES (nolock)
			where
				RegistryES_xmlExportPath is not null
				and RegistryESStatus_id = 1
				{$filter}
			order by
				RegistryES_xmlExpDT asc
		", $queryParams);

		$stream_context = stream_context_create(array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			),
			'http' => array(
				'timeout' => 1200 // 20 минут
			)
		));

		$options = array(
			'soap_version' => SOAP_1_2,
			'stream_context' => $stream_context,
			'exceptions' => 1, // обработка ошибок
			'trace' => 1, // трассировка
			'connection_timeout' => 120, // 2 минуты
			// 'proxy_host' => '192.168.36.31',
			// 'proxy_port' => '3128',
			// 'proxy_login' => '',
			// 'proxy_password' => '',
		);

		if (!empty($this->config->item('REGISTRY_LVN_PROXY_HOST')) && !empty($this->config->item('REGISTRY_LVN_PROXY_PORT'))) {
			$options['proxy_host'] = $this->config->item('REGISTRY_LVN_PROXY_HOST');
			$options['proxy_port'] = $this->config->item('REGISTRY_LVN_PROXY_PORT');
			$options['proxy_login'] = $this->config->item('REGISTRY_LVN_PROXY_LOGIN');
			$options['proxy_password'] = $this->config->item('REGISTRY_LVN_PROXY_PASSWORD');
		}

		$url = $this->config->item('EvnStickServiceUrl');
		if (empty($url)) {
			$this->textlog->add("sendRegistryESToFSS Не указан URL сервиса обмена ЛВН с ФСС");
			return array('Error_Msg' => 'Не указан URL сервиса обмена ЛВН с ФСС');
		}

		try {
			$error_msg = "Не удалось установить соединение с сервисом";
			set_error_handler(function ($errno, $errstr) {
				throw new Exception($errstr);
			}, E_ALL & ~E_NOTICE);
			$soapClient = new SoapClient($url, $options);
			restore_error_handler();
		} catch ( Exception $e ) {
			$this->textlog->add("sendRegistryESToFSS Exception: ".$e->getMessage());
			return array('Error_Msg' => $e->getMessage());
		}

		$encryption = $this->config->item('EvnStickServiceEncryption');

		foreach($resp as $respone) {
			$this->textlog->add("отправка реестра ".$respone['RegistryES_id']);

			// ставим статус "В процессе отправки".
			$this->db->query("
				update
					RegistryES with (rowlock)
				set
					RegistryESStatus_id = 11 -- В процессе отправки
				where
					RegistryES_id = :RegistryES_id
					and RegistryESStatus_id = 1 -- в очереди
			", array(
				'RegistryES_id' => $respone['RegistryES_id']
			));

			$affected_rows = $this->db->affected_rows();
			if ($affected_rows < 1) {
				// пропускаем, если ничего не проапдейтили (значит уже обработан другим заданием)
				$this->textlog->add("реестр уже отправляется другим заданием");
				continue;
			}

			if ($respone['RegistryESType_id'] == 3) {
				// по 1 запросу для каждой ЛВН
				$resp_resd = $this->queryResult("
					select
						Evn_id,
						RegistryESData_xmlExportPath
					from
						RegistryESData with (nolock)
					where
						RegistryES_id = :RegistryES_id
				", array(
					'RegistryES_id' => $respone['RegistryES_id']
				));

				foreach($resp_resd as $one_resd) {
					if (!file_exists($one_resd['RegistryESData_xmlExportPath'])) {
						$this->textlog->add("нет XML-файла");
						$this->sendErrorMessage($respone['RegistryES_id'], $data);
						$this->setRegistryESStatus(array(
							'RegistryES_id' => $respone['RegistryES_id'],
							'RegistryESStatus_id' => 8 // ошибка отправки
						));
						return array('Error_Msg' => "нет XML-файла");
					}

					$xmlReq = file_get_contents($one_resd['RegistryESData_xmlExportPath']);
					if (!empty($_REQUEST['getDebug'])) {
						echo '<textarea>' . $xmlReq . '</textarea>';
					}

					$this->textlog->add("запрос: " . $xmlReq);

					// сохраняем запрос в файл
					$file_path = $files_path . '/es' . $respone['RegistryES_id'] . '_request_' . time() . rand(10000,99999) . '.log';
					file_put_contents($file_path, $xmlReq);
					// зипуем, чтобы не занимать много места
					$file_zip_path = $file_path.'.zip';
					$zip = new ZipArchive;
					$zip->open($file_zip_path, ZIPARCHIVE::CREATE);
					$zip->AddFile($file_path, basename($file_path));
					$zip->close();
					$RegistryESFiles_id = $this->saveRegistryESFiles(array(
						'RegistryESFiles_id' => null,
						'RegistryES_id' => $respone['RegistryES_id'],
						'RegistryESFiles_PathFileRequest' => $file_zip_path,
						'RegistryESFiles_PathFileResponse' => null,
						'pmUser_id' => $data['pmUser_id']
					));
					@unlink($file_path);

					if ($encryption) {
						$this->load->model('RegistryESStorage_model');
						$cert_base64 = $this->RegistryESStorage_model->getCertBase64();
						$xml = $this->RegistryESStorage_model->encodeXmlForTransfer($xmlReq, $cert_base64);
						if (is_array($xml)) {
							// если не строка пришла, значит ошибка какая то
							$this->textlog->add("ошибка шифрования: " . print_r($xml, true));
							$this->sendErrorMessage($respone['RegistryES_id']);
							$this->setRegistryESStatus(array(
								'RegistryES_id' => $respone['RegistryES_id'],
								'RegistryESStatus_id' => 8 // ошибка отправки
							));
							return $xml;
						}

						if (!empty($_REQUEST['getDebug'])) {
							echo '<textarea>' . $xml . '</textarea>';
						}
						$this->textlog->add("зашифрованный запрос: " . $xml);
					} else {
						$xml = $xmlReq;
					}
					try {
						//$response = $soapClient->saveLn($xml);
						$response = $soapClient->__doRequest($xml, $url, 'disableLn', '1.2');
					} catch (SoapFault $e) {
						$this->textlog->add("sendRegistryESToFSS SoapFault: " . $e->getMessage());
						$this->sendErrorMessage($respone['RegistryES_id'], $data);
						$this->setRegistryESStatus(array(
							'RegistryES_id' => $respone['RegistryES_id'],
							'RegistryESStatus_id' => 8 // ошибка отправки
						));
						return array('Error_Msg' => $e->getMessage());
					}

					if (!empty($_REQUEST['getDebug'])) {
						echo '<textarea>' . $response . '</textarea>';
					}
					if (!empty($response)) {
						$this->textlog->add("ответ: " . $response);

						if ($encryption) {
							// надо расшифровать, чтобы получить XML с данными
							$this->load->model('RegistryESStorage_model');
							$xml = $this->RegistryESStorage_model->decodeXmlForTransfer($response);
							if (is_array($xml)) {
								// если не строка пришла, значит ошибка какая то
								$this->textlog->add("ошибка шифрования: " . print_r($xml, true));
								$this->sendErrorMessage($respone['RegistryES_id'], $data);
								$this->setRegistryESStatus(array(
									'RegistryES_id' => $respone['RegistryES_id'],
									'RegistryESStatus_id' => 8 // ошибка отправки
								));
								return $xml;
							}

							$this->textlog->add("расшифрованный ответ: " . $xml);
						} else {
							$xml = $response;
						}

						// сохраняем ответ в файл
						$file_path_resp = $files_path . '/es' . $respone['RegistryES_id'] . '_response_' . time() . rand(10000,99999) . '.log';
						file_put_contents($file_path_resp, $xml);
						// зипуем, чтобы не занимать много места
						$file_zip_path_resp = $file_path_resp.'.zip';
						$zip = new ZipArchive;
						$zip->open($file_zip_path_resp, ZIPARCHIVE::CREATE);
						$zip->AddFile($file_path_resp, basename($file_path_resp));
						$zip->close();
						$this->saveRegistryESFiles(array(
							'RegistryESFiles_id' => $RegistryESFiles_id,
							'RegistryES_id' => $respone['RegistryES_id'],
							'RegistryESFiles_PathFileRequest' => $file_zip_path,
							'RegistryESFiles_PathFileResponse' => $file_zip_path_resp,
							'pmUser_id' => $data['pmUser_id']
						));
						@unlink($file_path_resp);

						if (!empty($_REQUEST['getDebug'])) {
							echo "<textarea cols='150' rows='30'>{$xml}</textarea>";
						}
						$resp_xml = new DOMDocument();
						$resp_xml->loadXML($xml);

						$fault_arr = array();
						$faults = $resp_xml->getElementsByTagName('faultstring');
						foreach ($faults as $fault) {
							$fault_arr[] = $fault->nodeValue;
						}

						if (count($fault_arr) > 0) {
							$this->textlog->add("sendRegistryESToFSS Error_Msg: " . $fault_arr[0]);
							$this->sendErrorMessage($respone['RegistryES_id'], $data);
							$this->setRegistryESStatus(array(
								'RegistryES_id' => $respone['RegistryES_id'],
								'RegistryESStatus_id' => 8 // ошибка отправки
							));
							return array('Error_Msg' => $fault_arr[0]);
						}

						$regStatus = null;
						$disableLnResponse = $resp_xml->getElementsByTagName('disableLnResponse')->item(0);
						if (!empty($disableLnResponse)) {
							$status = $disableLnResponse->getElementsByTagName('STATUS')->item(0);
							if (!empty($status)) {
								$regStatus = $status->nodeValue;
								if ($regStatus == '0') {
									// реестр не принят
									$message = $disableLnResponse->getElementsByTagName('MESS')->item(0);

									if (!empty($message)) {
										// сохраняем ошибку в БД
										$this->setRegistryESError(array(
											'RegistryES_id' => $respone['RegistryES_id'],
											'Evn_id' => $one_resd['Evn_id'],
											'Error_Code' => '1',
											'Error_Message' => $message->nodeValue,
											'type' => 'fss',
											'pmUser_id' => $data['pmUser_id']
										));
									} else {
										// сохраняем ошибку в БД
										$this->setRegistryESError(array(
											'RegistryES_id' => $respone['RegistryES_id'],
											'Evn_id' => $one_resd['Evn_id'],
											'Error_Code' => '1',
											'Error_Message' => 'STATUS=0',
											'type' => 'fss',
											'pmUser_id' => $data['pmUser_id']
										));
									}

									// с ЛВН снимается признак "В реестре на удаление"
									$this->db->query("
										update EvnStickBase with (rowlock) set EvnStickBase_IsInRegDel = 1 where EvnStickBase_id = :EvnStickBase_id
									", array(
										'EvnStickBase_id' => $one_resd['Evn_id']
									));
								} else {
									// иначе удаляем ЛВН
									// Выполняем на основной БД
									$this->dbMain->query("
										declare
											@ErrCode int,
											@ErrMessage varchar(4000);
										exec p_EvnStick_del
											@EvnStick_id = :EvnStick_id,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
										select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									", array(
										'EvnStick_id' => $one_resd['Evn_id'],
										'pmUser_id' => $data['pmUser_id']
									));
								}
							} else {
								$this->textlog->add("sendRegistryESToFSS disableLnResponse: в ответе нет тега status");
								$this->sendErrorMessage($respone['RegistryES_id'], $data);
								$this->setRegistryESStatus(array(
									'RegistryES_id' => $respone['RegistryES_id'],
									'RegistryESStatus_id' => 8 // ошибка отправки
								));
								return array('Error_Msg' => 'disableLnResponse: в ответе нет тега status');
							}
						} else {
							$this->textlog->add("sendRegistryESToFSS disableLnResponse: в ответе нет тега disableLnResponse");
							$this->sendErrorMessage($respone['RegistryES_id'], $data);
							$this->setRegistryESStatus(array(
								'RegistryES_id' => $respone['RegistryES_id'],
								'RegistryESStatus_id' => 8 // ошибка отправки
							));
							return array('Error_Msg' => 'disableLnResponse: в ответе нет тега disableLnResponse');
						}
					} else {
						$this->textlog->add("sendRegistryESToFSS: пустой ответ");
						$this->textlog->add("__getLastResponseHeaders: " . $soapClient->__getLastResponseHeaders());
						$this->textlog->add("__getLastResponse: " . $soapClient->__getLastResponse());
						$this->sendErrorMessage($respone['RegistryES_id'], $data);
						$this->setRegistryESStatus(array(
							'RegistryES_id' => $respone['RegistryES_id'],
							'RegistryESStatus_id' => 8 // ошибка отправки
						));
						return array('Error_Msg' => 'Пустой ответ');
					}
				}

				// для всех ошибочных случаев проставляем статус "Не принят ФСС", для остальных "Принят ФСС"
				$query = "
					update
						resd with (rowlock)
					set
						resd.RegistryESDataStatus_id = (case when REE.Evn_id IS NOT NULL then 3 else 2 end)
					from
						RegistryESData resd
						outer apply(
							select top 1
								Evn_id
							from
								v_RegistryESError (nolock)
							where
								Evn_id = resd.Evn_id
								and RegistryES_id = resd.RegistryES_id
						) REE
					where
						resd.RegistryES_id = :RegistryES_id
						and isnull(resd.RegistryESDataStatus_id,1) in (1,2)
				";

				$this->db->query($query, array(
					'RegistryES_id' => $respone['RegistryES_id']
				));




				$resp_acc = $this->getFirstRowFromQuery("
					select
						sum(case when RegistryESDataStatus_id = 2 then 1 else 0 end) as accepted,
						sum(case when RegistryESDataStatus_id = 3 then 1 else 0 end) as declined
					from
						v_RegistryESData (nolock)
					where
						RegistryES_id = :RegistryES_id
				", array(
					'RegistryES_id' => $respone['RegistryES_id']
				));

				$RegistryESStatus_id = 6; // не принят ФСС
				if (!empty($resp_acc['accepted']) && !empty($resp_acc['declined'])) {
					// Есть принятые ЛВН и не принятые => "Принят ФСС с ошибками"
					$RegistryESStatus_id = 7;
				} else if (!empty($resp_acc['accepted'])) {
					// Есть только принятые ЛВН => "Принят ФСС"
					$RegistryESStatus_id = 5;
				}

				$this->setRegistryESStatus(array(
					'RegistryES_id' => $respone['RegistryES_id'],
					'RegistryESStatus_id' => $RegistryESStatus_id
				));
			} else {
				if (!file_exists($respone['RegistryES_xmlExportPath'])) {
					$this->textlog->add("нет XML-файла");
					$this->sendErrorMessage($respone['RegistryES_id'], $data);
					$this->setRegistryESStatus(array(
						'RegistryES_id' => $respone['RegistryES_id'],
						'RegistryESStatus_id' => 8 // ошибка отправки
					));
					return array('Error_Msg' => "нет XML-файла");
				}
				// для каждого реестра посылаем XML в ФСС
				$xmlReq = file_get_contents($respone['RegistryES_xmlExportPath']);
				if (!empty($_REQUEST['getDebug'])) {
					echo '<textarea>' . $xmlReq . '</textarea>';
				}

				$this->textlog->add("запрос: " . $xmlReq);

				// сохраняем запрос в файл
				$file_path = $files_path . '/es' . $respone['RegistryES_id'] . '_request_' . time() . rand(10000,99999) . '.log';
				file_put_contents($file_path, $xmlReq);
				// зипуем, чтобы не занимать много места
				$file_zip_path = $file_path.'.zip';
				$zip = new ZipArchive;
				$zip->open($file_zip_path, ZIPARCHIVE::CREATE);
				$zip->AddFile($file_path, basename($file_path));
				$zip->close();
				$RegistryESFiles_id = $this->saveRegistryESFiles(array(
					'RegistryESFiles_id' => null,
					'RegistryES_id' => $respone['RegistryES_id'],
					'RegistryESFiles_PathFileRequest' => $file_zip_path,
					'RegistryESFiles_PathFileResponse' => null,
					'pmUser_id' => $data['pmUser_id']
				));
				@unlink($file_path);

				if ($encryption) {
					$this->load->model('RegistryESStorage_model');
					$cert_base64 = $this->RegistryESStorage_model->getCertBase64();
					$xml = $this->RegistryESStorage_model->encodeXmlForTransfer($xmlReq, $cert_base64);
					if (is_array($xml)) {
						// если не строка пришла, значит ошибка какая то
						$this->textlog->add("ошибка шифрования: " . print_r($xml, true));
						$this->sendErrorMessage($respone['RegistryES_id'], $data);
						$this->setRegistryESStatus(array(
							'RegistryES_id' => $respone['RegistryES_id'],
							'RegistryESStatus_id' => 8 // ошибка отправки
						));
						return $xml;
					}

					if (!empty($_REQUEST['getDebug'])) {
						echo '<textarea>' . $xml . '</textarea>';
					}
					$this->textlog->add("зашифрованный запрос: " . $xml);
				} else {
					$xml = $xmlReq;
				}

				try {
					//$response = $soapClient->saveLn($xml);
					$response = $soapClient->__doRequest($xml, $url, 'prParseFilelnlpu', '1.2');
				} catch (SoapFault $e) {
					$this->textlog->add("sendRegistryESToFSS SoapFault: " . $e->getMessage());
					$this->sendErrorMessage($respone['RegistryES_id'], $data);
					$this->setRegistryESStatus(array(
						'RegistryES_id' => $respone['RegistryES_id'],
						'RegistryESStatus_id' => 8 // ошибка отправки
					));
					return array('Error_Msg' => $e->getMessage());
				}

				if (!empty($_REQUEST['getDebug'])) {
					echo '<textarea>' . $response . '</textarea>';
					var_dump($soapClient->__getLastResponseHeaders());
				}
				if (!empty($response)) {
					$this->textlog->add("ответ: " . $response);

					if ($encryption) {
						// надо расшифровать, чтобы получить XML с данными
						$this->load->model('RegistryESStorage_model');
						$xml = $this->RegistryESStorage_model->decodeXmlForTransfer($response);
						if (is_array($xml)) {
							// если не строка пришла, значит ошибка какая то
							$this->textlog->add("ошибка шифрования: " . print_r($xml, true));
							$this->sendErrorMessage($respone['RegistryES_id'], $data);
							$this->setRegistryESStatus(array(
								'RegistryES_id' => $respone['RegistryES_id'],
								'RegistryESStatus_id' => 8 // ошибка отправки
							));
							return $xml;
						}

						$this->textlog->add("расшифрованный ответ: " . $xml);
					} else {
						$xml = $response;
					}
					// сохраняет ответ на реестре, чтобы иметь возможность разобрать его в случае если дальнейишй процесс вдруг прервётся
					$RegistryES_xmlImportPath = $respone['RegistryES_xmlExportPath'] . '.imp.xml';
					file_put_contents($RegistryES_xmlImportPath, $xml);
					$this->db->query("update RegistryES with (rowlock) set RegistryES_xmlImportPath = :RegistryES_xmlImportPath where RegistryES_id = :RegistryES_id", array(
						'RegistryES_id' => $respone['RegistryES_id'],
						'RegistryES_xmlImportPath' => $RegistryES_xmlImportPath
					));

					// сохраняем ответ в файл
					$file_path_resp = $files_path . '/es' . $respone['RegistryES_id'] . '_response_' . time() . rand(10000,99999) . '.log';
					file_put_contents($file_path_resp, $xml);
					// зипуем, чтобы не занимать много места
					$file_zip_path_resp = $file_path_resp.'.zip';
					$zip = new ZipArchive;
					$zip->open($file_zip_path_resp, ZIPARCHIVE::CREATE);
					$zip->AddFile($file_path_resp, basename($file_path_resp));
					$zip->close();
					$this->saveRegistryESFiles(array(
						'RegistryESFiles_id' => $RegistryESFiles_id,
						'RegistryES_id' => $respone['RegistryES_id'],
						'RegistryESFiles_PathFileRequest' => $file_zip_path,
						'RegistryESFiles_PathFileResponse' => $file_zip_path_resp,
						'pmUser_id' => $data['pmUser_id']
					));
					@unlink($file_path_resp);

					if (!empty($_REQUEST['getDebug'])) {
						echo "<textarea cols='150' rows='30'>{$xml}</textarea>";
					}

					$resp = $this->parseXmlResponse($data, $respone['RegistryES_id'], $xml, $xmlReq);
					if (!empty($resp['Error_Msg'])) {
						return $resp;
					}
				} else {
					$this->textlog->add("sendRegistryESToFSS: пустой ответ");
					$this->textlog->add("__getLastResponseHeaders: " . $soapClient->__getLastResponseHeaders());
					$this->textlog->add("__getLastResponse: " . $soapClient->__getLastResponse());
					$this->sendErrorMessage($respone['RegistryES_id'], $data);
					$this->setRegistryESStatus(array(
						'RegistryES_id' => $respone['RegistryES_id'],
						'RegistryESStatus_id' => 8 // ошибка отправки
					));
					return array('Error_Msg' => 'Пустой ответ');
				}
			}
		}

		// берем очередь зависших реестров в процессе отправки на целый час и больше
		$resp = $this->queryResult("
			declare @dateLimit datetime = DATEADD(hour, -1, dbo.tzGetDate());
			
			select
				RegistryES_id,
				RegistryES_xmlExportPath,
				RegistryES_xmlImportPath
			from
				v_RegistryES (nolock)
			where
				RegistryES_xmlExportPath is not null
				and RegistryESStatus_id = 11
				and RegistryES_xmlExpDT <= @dateLimit
			order by
				RegistryES_xmlExpDT asc
		", $queryParams);

		foreach($resp as $respone) {
			$this->textlog->add("Обрабатываем зависший реестр " . $respone['RegistryES_id']);
			if (!empty($respone['RegistryES_xmlImportPath'])) {
				$this->textlog->add("Есть файл импорта");
				$xml = file_get_contents($respone['RegistryES_xmlImportPath']);
				$xmlReq = file_get_contents($respone['RegistryES_xmlExportPath']);

				$resp = $this->parseXmlResponse($data, $respone['RegistryES_id'], $xml, $xmlReq);
				if (!empty($resp['Error_Msg'])) {
					return $resp;
				}
			} else {
				$this->textlog->add("Нет файла импорта");
				$this->sendErrorMessage($respone['RegistryES_id'], $data);
				$this->setRegistryESStatus(array(
					'RegistryES_id' => $respone['RegistryES_id'],
					'RegistryESStatus_id' => 8 // ошибка отправки
				));
			}
		}

		$this->textlog->add("sendRegistryESToFSS end");

		return array('Error_Msg' => '');
	}

	/**
	 * Парсинг xml запроса
	 */
	function parseXmlRequest($xml) {
		$resp_xml = new DOMDocument();
		$resp_xml->loadXML($xml);

		$fault_arr = array();
		$faults = $resp_xml->getElementsByTagName('faultstring');
		foreach ($faults as $fault) {
			$fault_arr[] = $fault->nodeValue;
		}

		if (count($fault_arr) > 0) {
			return array('Error_Msg' => $fault_arr[0]);
		}

		$prParseFilelnlpu = $resp_xml->getElementsByTagName('prParseFilelnlpu')->item(0);
		if (empty($prParseFilelnlpu)) {
			return array('Error_Msg' => 'В запросе нет тэга prParseFilelnlpu');
		}

		$rows = $prParseFilelnlpu->getElementsByTagName('ROW');
		$rowCount = $rows->length;

		$response = array();
		for ($index = 0; $index < $rowCount; $index ++) {

			$FileOperationsLnUserGetLNDataOut = $rows->item($index);

			$StickFSSDataGet = array(
				'StickFSSDataGet_id' => null,
				'StickFSSData_id' => null,
				'Person_Snils' => null,
				'Person_SurName' => null,
				'Person_FirName' => null,
				'Person_SecName' => null,
				'Person_BirthDay' => null,
				'Sex_id' => null,
				'StickFSSData_StickNum' => null,
				'StickFSSDataGet_StickFirstNum' => null,
				'StickFSSDataGet_IsStickFirst' => null,
				'StickFSSDataGet_IsStickDouble' => null,
				'StickFSSDataGet_StickSetDate' => null,
				'StickFSSDataGet_StickReason' => null,
				'StickFSSDataGet_StickReasonDop' => null,
				'StickCause_CodeAlter' => null,
				'Diag_Code' => null,
				'StickFSSDataGet_StickResult' => null,
				'StickFSSDataGet_changeDate' => null,
				'StickFSSDataGet_workDate' => null,
				'StickFSSDataGet_StickNextNum' => null,
				'StickFSSDataGet_StickStatus' => null,
				'StickFSSDataGet_FirstPostVK' => null,
				'StickFSSDataGet_FirstBegDate' => null,
				'StickFSSDataGet_FirstEndDate' => null,
				'StickFSSDataGet_FirstPost' => null,
				'StickFSSDataGet_SecondPostVK' => null,
				'StickFSSDataGet_SecondBegDate' => null,
				'StickFSSDataGet_SecondEndDate' => null,
				'StickFSSDataGet_SecondPost' => null,
				'StickFSSDataGet_ThirdPostVK' => null,
				'StickFSSDataGet_ThirdBegDate' => null,
				'StickFSSDataGet_ThirdEndDate' => null,
				'StickFSSDataGet_ThirdPost' => null,
				'StickFSSDataGet_IsEdit' => null,
				'Lpu_StickNick' => null,
				'MedPersonal_FirstFIO' => null,
				'MedPersonalVK_FirstFIO' => null,
				'MedPersonal_SecondFIO' => null,
				'MedPersonalVK_SecondFIO' => null,
				'MedPersonal_ThirdFIO' => null,
				'MedPersonalVK_ThirdFIO' => null,
				'StickFSSDataGet_IsEmploymentServices' => null,
				'Org_StickNick' => null,
				'StickFSSDataGet_EmploymentType' => null,
				'Lpu_StickAddress' => null,
				'Lpu_OGRN' => null,
				'EvnStick_NumPar' => null,
				'EvnStick_StickDT' => null,
				'EvnStick_sstEndDate' => null,
				'EvnStick_sstNum' => null,
				'Org_sstOGRN' => null,
				'FirstRelated_Age' => null,
				'FirstRelated_AgeMonth' => null,
				'FirstRelatedLinkType_Code' => null,
				'FirstRelated_FIO' => null,
				'FirstRelated_begDate' => null,
				'FirstRelated_endDate' => null,
				'SecondRelated_Age' => null,
				'SecondRelated_AgeMonth' => null,
				'SecondRelatedLinkType_Code' => null,
				'SecondRelated_FIO' => null,
				'SecondRelated_begDate' => null,
				'SecondRelated_endDate' => null,
				'EvnStick_IsRegPregnancy' => null,
				'EvnStick_stacBegDate' => null,
				'EvnStick_stacEndDate' => null,
				'StickIrregularity_Code' => null,
				'EvnStick_irrDT' => null,
				'EvnStick_mseDT' => null,
				'EvnStick_mseRegDT' => null,
				'EvnStick_mseExamDT' => null,
				'InvalidGroupType_id' => null,
				'FirstPostVK_Code' => null,
				'SecondPostVK_Code' => null,
				'ThirdPostVK_Code' => null,
				'StickFSSDataGet_Hash' => null,
				'FirstEvnStickWorkRelease_id' => null,
				'SecondEvnStickWorkRelease_id' => null,
				'ThirdEvnStickWorkRelease_id' => null
			);
			// достаём данные из XML
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SNILS');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Person_Snils'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SURNAME');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Person_SurName'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('NAME');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Person_FirName'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PATRONIMIC');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Person_SecName'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('BIRTHDAY');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Person_BirthDay'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('GENDER');
			if ($EL->length > 0) {
				$Sex_id = null;
				switch($EL->item(0)->nodeValue) {
					case '0':
						$Sex_id = 1;
						break;
					case '1':
						$Sex_id = 2;
						break;
				}
				$StickFSSDataGet['Sex_id'] = $Sex_id;
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_CODE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSData_StickNum'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PREV_LN_CODE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_StickFirstNum'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PRIMARY_FLAG');
			if ($EL->length > 0) {
				$StickFSSDataGet_IsStickFirst = null;
				switch($EL->item(0)->nodeValue) {
					case '0':
						$StickFSSDataGet_IsStickFirst = 1;
						break;
					case '1':
						$StickFSSDataGet_IsStickFirst = 2;
						break;
				}
				$StickFSSDataGet['StickFSSDataGet_IsStickFirst'] = $StickFSSDataGet_IsStickFirst;
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DUPLICATE_FLAG');
			if ($EL->length > 0) {
				$StickFSSDataGet_IsStickDouble = null;
				switch($EL->item(0)->nodeValue) {
					case '0':
						$StickFSSDataGet_IsStickDouble = 1;
						break;
					case '1':
						$StickFSSDataGet_IsStickDouble = 2;
						break;
				}
				$StickFSSDataGet['StickFSSDataGet_IsStickDouble'] = $StickFSSDataGet_IsStickDouble;
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_DATE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_StickSetDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON1');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_StickReason'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON2');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_StickReasonDop'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON3');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickCause_CodeAlter'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DIAGNOS');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Diag_Code'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_RESULT');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_StickResult'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('OTHER_STATE_DT');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_changeDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('RETURN_DATE_LPU');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_workDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('NEXT_LN_CODE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_StickNextNum'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_STATE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_StickStatus'] = $nodeValue;
				}
			}
			$TFP = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('TREAT_FULL_PERIOD');
			if ($TFP->length > 0) {
				$TreatPeriod = $TFP->item(0);
				$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_FirstPostVK'] = $EL->item(0)->nodeValue;
					$StickFSSDataGet['FirstPostVK_Code'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
				if ($EL->length > 0) {
					$StickFSSDataGet['MedPersonal_FirstFIO'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
				if ($EL->length > 0) {
					$StickFSSDataGet['MedPersonalVK_FirstFIO'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_FirstBegDate'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_FirstEndDate'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_FirstPost'] = $EL->item(0)->nodeValue;
				}
			}
			if ($TFP->length > 1) {
				$TreatPeriod = $TFP->item(1);
				$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_SecondPostVK'] = $EL->item(0)->nodeValue;
					$StickFSSDataGet['SecondPostVK_Code'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
				if ($EL->length > 0) {
					$StickFSSDataGet['MedPersonal_SecondFIO'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
				if ($EL->length > 0) {
					$StickFSSDataGet['MedPersonalVK_SecondFIO'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_SecondBegDate'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_SecondEndDate'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_SecondPost'] = $EL->item(0)->nodeValue;
				}
			}
			if ($TFP->length > 2) {
				$TreatPeriod = $TFP->item(2);
				$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_ThirdPostVK'] = $EL->item(0)->nodeValue;
					$StickFSSDataGet['ThirdPostVK_Code'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
				if ($EL->length > 0) {
					$StickFSSDataGet['MedPersonal_ThirdFIO'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
				if ($EL->length > 0) {
					$StickFSSDataGet['MedPersonalVK_ThirdFIO'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_ThirdBegDate'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_ThirdEndDate'] = $EL->item(0)->nodeValue;
				}
				$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
				if ($EL->length > 0) {
					$StickFSSDataGet['StickFSSDataGet_ThirdPost'] = $EL->item(0)->nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_NAME');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Lpu_StickNick'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('BOZ_FLAG');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_IsEmploymentServices'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_EMPLOYER');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Org_StickNick'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_EMPL_FLAG');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (isset($nodeValue) && mb_strlen($nodeValue) > 0) {
					$StickFSSDataGet['StickFSSDataGet_EmploymentType'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_ADDRESS');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Lpu_StickAddress'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_OGRN');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Lpu_OGRN'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PARENT_CODE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_NumPar'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DATE1');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_StickDT'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DATE2');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_sstEndDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('VOUCHER_NO');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_sstNum'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('VOUCHER_OGRN');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['Org_sstOGRN'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_AGE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['FirstRelated_Age'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_MM');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['FirstRelated_AgeMonth'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_RELATION_CODE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['FirstRelatedLinkType_Code'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_FIO');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['FirstRelated_FIO'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_DT1');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['FirstRelated_begDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_DT2');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['FirstRelated_endDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_AGE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['SecondRelated_Age'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_MM');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['SecondRelated_AgeMonth'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_RELATION_CODE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['SecondRelatedLinkType_Code'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_FIO');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['SecondRelated_FIO'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_DT1');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['SecondRelated_begDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_DT2');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['SecondRelated_endDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PREGN12W_FLAG');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (isset($nodeValue) && mb_strlen($nodeValue) > 0) {
					$StickFSSDataGet['EvnStick_IsRegPregnancy'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_DT1');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_stacBegDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_DT2');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_stacEndDate'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_BREACH_CODE');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickIrregularity_Code'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_BREACH_DT');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_irrDT'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT1');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_mseDT'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT2');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_mseRegDT'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT3');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['EvnStick_mseExamDT'] = $nodeValue;
				}
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_INVALID_GROUP');
			if ($EL->length > 0) {
				$InvalidGroupType_id = null;
				switch($EL->item(0)->nodeValue) {
					case '1':
						$InvalidGroupType_id = 2;
						break;
					case '2':
						$InvalidGroupType_id = 3;
						break;
					case '3':
						$InvalidGroupType_id = 4;
						break;
				}
				$StickFSSDataGet['InvalidGroupType_id'] = $InvalidGroupType_id;
			}
			$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_HASH');
			if ($EL->length > 0) {
				$nodeValue = $EL->item(0)->nodeValue;
				if (!empty($nodeValue)) {
					$StickFSSDataGet['StickFSSDataGet_Hash'] = $nodeValue;
				}
			}

			$response[] = $StickFSSDataGet;
		}

		return $response;
	}

	/**
	 * Сохраняет данные из XML в таблицу StickFssDataGet
	 */
	function XMLtoStickFssDataGet($data)
	{
		if(empty($data['xml'])) {
			return false;
		}

		$records = $this->parseXmlRequest($data['xml']);

		if(!empty($records['Error_Msg'])) {
			return false;
		}

		foreach ($records as $StickFSSDataGet) {
			if (!isset($data['acceptedEvns'][$StickFSSDataGet['StickFSSData_StickNum']]) && empty($data['uploadTestFile'])) {
				continue;
			}

			if (empty($data['uploadTestFile'])) {
				$StickFSSDataGet['EvnStickBase_id'] = $data['acceptedEvns'][$StickFSSDataGet['StickFSSData_StickNum']];
			} else {
				$StickFSSDataGet['EvnStickBase_id'] = null;
			}

			$StickFSSDataGet['pmUser_id'] = $data['pmUser_id'];

			$proc_sfssdg = 'p_StickFSSDataGet_ins';
			$result_check = $this->dbMain->query("
				select top 1
					StickFSSDataGet_id
				from
					v_StickFSSDataGet (nolock)
				where
					StickFSSDataGet_StickNum = :StickFSSDataGet_StickNum
					and StickFSSData_id is null
			", array(
				'StickFSSDataGet_StickNum' => $StickFSSDataGet['StickFSSData_StickNum']
			));
			$resp_check = $result_check->result('array');
			if (!empty($resp_check[0]['StickFSSDataGet_id'])) {
				$StickFSSDataGet['StickFSSDataGet_id'] = $resp_check[0]['StickFSSDataGet_id'];
				$proc_sfssdg = 'p_StickFSSDataGet_upd';
			} 

			// записываем данные в StickFSSDataGet
			$result_save = $this->dbMain->query("
				declare
					@StickFSSDataGet_id bigint = :StickFSSDataGet_id,
					@Error_Code int,
					@Error_Message varchar(4000);

				exec {$proc_sfssdg}
					@StickFSSDataGet_id = @StickFSSDataGet_id output,
					@EvnStickBase_id = :EvnStickBase_id,
					@StickFSSData_id = :StickFSSData_id,
					@Person_Snils = :Person_Snils,
					@Person_SurName = :Person_SurName,
					@Person_FirName = :Person_FirName,
					@Person_SecName = :Person_SecName,
					@Person_BirthDay = :Person_BirthDay,
					@Sex_id = :Sex_id,
					@StickFSSDataGet_StickNum = :StickFSSData_StickNum,
					@StickFSSDataGet_StickFirstNum = :StickFSSDataGet_StickFirstNum,
					@StickFSSDataGet_IsStickFirst = :StickFSSDataGet_IsStickFirst,
					@StickFSSDataGet_IsStickDouble = :StickFSSDataGet_IsStickDouble,
					@StickFSSDataGet_StickSetDate = :StickFSSDataGet_StickSetDate,
					@StickFSSDataGet_StickReason = :StickFSSDataGet_StickReason,
					@StickFSSDataGet_StickReasonDop = :StickFSSDataGet_StickReasonDop,
					@StickCause_CodeAlter = :StickCause_CodeAlter,
					@Diag_Code = :Diag_Code,
					@StickFSSDataGet_StickResult = :StickFSSDataGet_StickResult,
					@StickFSSDataGet_changeDate = :StickFSSDataGet_changeDate,
					@StickFSSDataGet_workDate = :StickFSSDataGet_workDate,
					@StickFSSDataGet_StickNextNum = :StickFSSDataGet_StickNextNum,
					@StickFSSDataGet_StickStatus = :StickFSSDataGet_StickStatus,
					@StickFSSDataGet_FirstPostVK = :StickFSSDataGet_FirstPostVK,
					@StickFSSDataGet_FirstBegDate = :StickFSSDataGet_FirstBegDate,
					@StickFSSDataGet_FirstEndDate = :StickFSSDataGet_FirstEndDate,
					@StickFSSDataGet_FirstPost = :StickFSSDataGet_FirstPost,
					@StickFSSDataGet_SecondPostVK = :StickFSSDataGet_SecondPostVK,
					@StickFSSDataGet_SecondBegDate = :StickFSSDataGet_SecondBegDate,
					@StickFSSDataGet_SecondEndDate = :StickFSSDataGet_SecondEndDate,
					@StickFSSDataGet_SecondPost = :StickFSSDataGet_SecondPost,
					@StickFSSDataGet_ThirdPostVK = :StickFSSDataGet_ThirdPostVK,
					@StickFSSDataGet_ThirdBegDate = :StickFSSDataGet_ThirdBegDate,
					@StickFSSDataGet_ThirdEndDate = :StickFSSDataGet_ThirdEndDate,
					@StickFSSDataGet_ThirdPost = :StickFSSDataGet_ThirdPost,
					@StickFSSDataGet_IsEdit = :StickFSSDataGet_IsEdit,
					@Lpu_StickNick = :Lpu_StickNick,
					@MedPersonal_FirstFIO = :MedPersonal_FirstFIO,
					@MedPersonalVK_FirstFIO = :MedPersonalVK_FirstFIO,
					@MedPersonal_SecondFIO = :MedPersonal_SecondFIO,
					@MedPersonalVK_SecondFIO = :MedPersonalVK_SecondFIO,
					@MedPersonal_ThirdFIO = :MedPersonal_ThirdFIO,
					@MedPersonalVK_ThirdFIO = :MedPersonalVK_ThirdFIO,
					@StickFSSDataGet_IsEmploymentServices = :StickFSSDataGet_IsEmploymentServices,
					@Org_StickNick = :Org_StickNick,
					@StickFSSDataGet_EmploymentType = :StickFSSDataGet_EmploymentType,
					@Lpu_StickAddress = :Lpu_StickAddress,
					@Lpu_OGRN = :Lpu_OGRN,
					@EvnStick_NumPar = :EvnStick_NumPar,
					@EvnStick_StickDT = :EvnStick_StickDT,
					@EvnStick_sstEndDate = :EvnStick_sstEndDate,
					@EvnStick_sstNum = :EvnStick_sstNum,
					@Org_sstOGRN = :Org_sstOGRN,
					@FirstRelated_Age = :FirstRelated_Age,
					@FirstRelated_AgeMonth = :FirstRelated_AgeMonth,
					@FirstRelatedLinkType_Code = :FirstRelatedLinkType_Code,
					@FirstRelated_FIO = :FirstRelated_FIO,
					@FirstRelated_begDate = :FirstRelated_begDate,
					@FirstRelated_endDate = :FirstRelated_endDate,
					@SecondRelated_Age = :SecondRelated_Age,
					@SecondRelated_AgeMonth = :SecondRelated_AgeMonth,
					@SecondRelatedLinkType_Code = :SecondRelatedLinkType_Code,
					@SecondRelated_FIO = :SecondRelated_FIO,
					@SecondRelated_begDate = :SecondRelated_begDate,
					@SecondRelated_endDate = :SecondRelated_endDate,
					@EvnStick_IsRegPregnancy = :EvnStick_IsRegPregnancy,
					@EvnStick_stacBegDate = :EvnStick_stacBegDate,
					@EvnStick_stacEndDate = :EvnStick_stacEndDate,
					@StickIrregularity_Code = :StickIrregularity_Code,
					@EvnStick_irrDT = :EvnStick_irrDT,
					@EvnStick_mseDT = :EvnStick_mseDT,
					@EvnStick_mseRegDT = :EvnStick_mseRegDT,
					@EvnStick_mseExamDT = :EvnStick_mseExamDT,
					@InvalidGroupType_id = :InvalidGroupType_id,
					@FirstPostVK_Code = :FirstPostVK_Code,
					@SecondPostVK_Code = :SecondPostVK_Code,
					@ThirdPostVK_Code = :ThirdPostVK_Code,
					@StickFSSDataGet_Hash = :StickFSSDataGet_Hash,
					@FirstEvnStickWorkRelease_id = :FirstEvnStickWorkRelease_id,
					@SecondEvnStickWorkRelease_id = :SecondEvnStickWorkRelease_id,
					@ThirdEvnStickWorkRelease_id = :ThirdEvnStickWorkRelease_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT
				select @StickFSSDataGet_id as StickFSSDataGet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			", $StickFSSDataGet);

			$resp_save = $result_save->result('array');
			if (!empty($resp_save[0]['Error_Msg'])) {
				return array('Error_Msg' => $resp_save[0]['Error_Msg']);
			}	
		}

		return true;
	}

	/**
	 * Фикс реестров не до конца обработанных при отправке реестра в ФСС.
	 */
	function fixRegistryES($data) {
		if (true || !isSuperAdmin()) {
			return array('Error_Msg' => 'Метод только для супердамина');
		}

		$this->load->library('textlog', array('file'=>'fixRegistryES_'.date('Y-m-d').'.log'));

		$resp = $this->queryResult("
			select top 1
				RegistryESFiles_PathFileResponse
			from
				RegistryESFiles (nolock)
			where
				RegistryES_id = :RegistryES_id
				and Evn_id is null
			order by
				RegistryESFiles_insDT desc
		", array(
			'RegistryES_id' => $data['RegistryES_id']
		));

		if (!empty($resp[0]['RegistryESFiles_PathFileResponse'])) {
			/*$zipFile = 'https://promed.promedweb.ru/'.$resp[0]['RegistryESFiles_PathFileResponse'];
			$zipString = file_get_contents($zipFile);*/

			$files_path = EXPORTPATH_ROOT . "registry_es_files";
			if (!file_exists($files_path)) {
				mkdir($files_path);
			}

			$dir_path = $files_path . '/es' . $data['RegistryES_id'] . '_fix_' . time() . rand(10000,99999);
			if (!file_exists($dir_path)) {
				mkdir($dir_path);
			}

			/*$file_path = $dir_path . '/archive.zip';
			file_put_contents($file_path, $zipString);*/
			$file_path = $resp[0]['RegistryESFiles_PathFileResponse'];

			$zip = new ZipArchive;
			if ($zip->open($file_path) === TRUE)
			{
				$logfile = null;
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/.*\.log/', $filename) > 0 ) {
						$logfile = $filename;
					}
				}

				if (!empty($logfile)) {
					$zip->extractTo($dir_path);
					$xml = file_get_contents($dir_path.'/'.$logfile);

					if (!empty($xml)) {
						$resp = $this->parseXmlResponse($data, $data['RegistryES_id'], $xml);
						if (!empty($resp['Error_Msg'])) {
							return $resp;
						}
					}
				}
				$zip->close();
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Добавление запроса в ФСС для получения данных о МСЭ
	 */
	function addRequestMSE($data, $RegistryES_id, $lnCode) {
		$queryParams = array(
			'EvnStickBase_Num' => $lnCode,
			'RegistryES_id' => $RegistryES_id
		);
		
		//Получаем ОГРН
		$query = "
			select top 1
				O.Org_OGRN as Lpu_OGRN,
				RES.Lpu_id
			from
				v_RegistryES RES with (nolock)
				inner join Lpu L with (nolock) on L.Lpu_id = RES.Lpu_id
				inner join Org O with (nolock) on O.Org_id = L.Org_id
			where
				RES.RegistryES_id = :RegistryES_id
		";
		$ogrn_resp = $this->getFirstRowFromQuery($query, $queryParams);
		if(!empty($ogrn_resp)) {
			$lnLpu_OGRN = $ogrn_resp['Lpu_OGRN'];
			$RegLpu_id = $ogrn_resp['Lpu_id'];
		} else {
			$lnLpu_OGRN = null;
			$RegLpu_id = null;
		}
				
		//Получаем Person_id и СНИЛС пациента
		$query = "
			select top 1
				P.Person_id,
				P.Person_Snils,
				ESB.EvnStickBase_id
			from
				v_EvnStickBase ESB with (nolock)
				inner join v_Person_All P with (nolock) on P.PersonEvn_id = ESB.PersonEvn_id
			where
				ESB.EvnStickBase_Num = :EvnStickBase_Num

		";
		$snils_resp = $this->getFirstRowFromQuery($query, $queryParams);

		$query = "
			declare
				@StickFSSData_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			
			exec p_StickFSSData_ins
				@StickFSSData_id = @StickFSSData_id output,
				@StickFSSData_StickNum = :StickFSSData_StickNum,
				@Lpu_id = :Lpu_id,
				@Person_id= :Person_id,
				@Person_Snils = :Person_Snils,
				@EvnStickBase_id = :EvnStickBase_id,
				@Lpu_OGRN = :Lpu_OGRN,
				@StickFSSDataStatus_id = 1, -- Ожидает отправки
				@StickFSSData_IsNeedMSE = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT
			select @StickFSSData_id as StickFSSData_id,@Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$queryParams = array(
			'Lpu_id' => $RegLpu_id,
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $snils_resp['Person_id'],
			'Person_Snils' => $snils_resp['Person_Snils'],
			'EvnStickBase_id' => $snils_resp['EvnStickBase_id'],
			'StickFSSData_StickNum' => $lnCode,
			'Lpu_OGRN' => $lnLpu_OGRN
		);
		$this->db->query($query, $queryParams);

		$this->load->model('Messages_model');
		// получаем пользователей данной МО с группами:
		// - пользователь АРМ администратора МО;
		// - пользователь АРМ медицинского статистика.
		$resp_users = $this->queryResult("
			DECLARE
				@pmUserCacheGroup_id bigint = (select top 1 pmUserCacheGroup_id from dbo.pmUserCacheGroup where pmUserCacheGroup_SysNick = 'LpuAdmin')
			
			select
				pmUser_id
			from
				v_pmUserCache puc (nolock)
			where
				puc.Lpu_id = :Lpu_id
				and exists(select top 1 pucgl.pmUserCacheGroupLink_id from v_pmUserCacheGroupLink pucgl with (nolock) where pucgl.pmUserCacheGroup_id = @pmUserCacheGroup_id and pucgl.pmUserCache_id = puc.PMUser_id) -- АРМ администратора МО
				and ISNULL(puc.pmUser_deleted, 1) = 1
				and puc.pmUser_EvnClass like '%StickFSSData%'
				
			union
			
			select
				pmUser_id
			from
				v_pmUserCache puc (nolock)
				inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedPersonal_id = puc.MedPersonal_id
				inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
				inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
			where
				puc.Lpu_id = :Lpu_id
				and mst.MedServiceType_SysNick = 'mstat' -- АРМ медицинского статистика
				and ISNULL(puc.pmUser_deleted, 1) = 1
				and puc.pmUser_EvnClass like '%StickFSSData%'
		", array(
			'Lpu_id' => $RegLpu_id
		));

		if (!empty($resp_users)) {
			$user = getUser();
			$message = "Пользователь, создавший запрос: " . ($user->name) . "." . PHP_EOL . PHP_EOL;
			$message .= "Дата и время создания запроса: " . (date('d.m.Y H:i:s')) . "." . PHP_EOL . PHP_EOL;
			$message .= "Перейдите в раздел «Запросы в ФСС» для выполнения отправки.";
			$noticeData = array(
				'autotype' => 5,
				'pmUser_id' => $data['pmUser_id'],
				'EvnClass_SysNick' => 'StickFSSData',
				'type' => 1,
				'title' => 'Создан новый запрос в ФСС',
				'text' => $message
			);

			foreach($resp_users as $one_user) {
				$noticeData['User_rid'] = $one_user['pmUser_id'];
				$this->Messages_model->autoMessage($noticeData);
			}
		}
	}

	/**
	 * Распарсивание XML-ответа ФСС на отправку реестра ЛВН
	 */
	function parseXmlResponse($data, $RegistryES_id, $xml, $xmlReq = null) {
		$resp_xml = new DOMDocument();
		$resp_xml->loadXML($xml);

		if (empty($this->StickFSSType)) {
			$resp_sft = $this->queryResult("
				select
					StickFSSType_id,
					StickFSSType_Code
				from
					StickFSSType SFT with (nolock)				
			");
			foreach ($resp_sft as $one_sft) {
				$this->StickFSSType[$one_sft['StickFSSType_Code']] = $one_sft['StickFSSType_id'];
			}
		}

		$fault_arr = array();
		$faults = $resp_xml->getElementsByTagName('faultstring');
		foreach ($faults as $fault) {
			$fault_arr[] = $fault->nodeValue;
		}

		if (count($fault_arr) > 0) {
			$this->textlog->add("sendRegistryESToFSS Error_Msg: " . $fault_arr[0]);
			$this->setRegistryESStatus(array(
				'RegistryES_id' => $RegistryES_id,
				'RegistryESStatus_id' => 8 // ошибка отправки
			));
			return array('Error_Msg' => $fault_arr[0]);
		}

		$acceptedEvns = array();
		$regStatus = null;
		$prParseFilelnlpuResponse = $resp_xml->getElementsByTagName('prParseFilelnlpuResponse')->item(0);
		if (!empty($prParseFilelnlpuResponse)) {
			$status = $prParseFilelnlpuResponse->getElementsByTagName('STATUS')->item(0);
			if (empty($status)) {
				$this->textlog->add("sendRegistryESToFSS prParseFilelnlpuResponse: в ответе нет тега status");
				$this->setRegistryESStatus(array(
					'RegistryES_id' => $RegistryES_id,
					'RegistryESStatus_id' => 8 // ошибка отправки
				));
				return array('Error_Msg' => 'prParseFilelnlpuResponse: в ответе нет тега status');
			}

			$hasErrors = false;

			// тянем ошибки со случаев
			$row = $prParseFilelnlpuResponse->getElementsByTagName('ROW');
			foreach ($row as $oneRow) {
				$lnHash = null;
				$lnCode = null;
				$errorCode = null;
				$errorMessage = null;

				$lnCodeElem = $oneRow->getElementsByTagName('LN_CODE')->item(0);
				if (!empty($lnCodeElem)) {
					$lnCode = $lnCodeElem->nodeValue;
				}

				$lnStatusElem = $oneRow->getElementsByTagName('STATUS')->item(0);
				if (!empty($lnStatusElem)) {
					$lnStatus = $lnStatusElem->nodeValue;
				}

				$lnStateElem = $oneRow->getElementsByTagName('LN_STATE')->item(0);
				if (!empty($lnStateElem)) {
					$lnState = $lnStateElem->nodeValue;
				}

				$lnHashElem = $oneRow->getElementsByTagName('LN_HASH')->item(0);
				if (!empty($lnHashElem)) {
					$lnHash = $lnHashElem->nodeValue;
					if (empty($lnHash)) {
						$lnHash = null;
					}
				}

				$lnLpuOGRNElem = $oneRow->getElementsByTagName('LPU_OGRN')->item(0);
				if (!empty($lnLpuOGRNElem)) {
					$lnLpu_OGRN = $lnLpuOGRNElem->nodeValue;
					if (empty($lnLpu_OGRN)) {
						$lnLpu_OGRN = null;
					}
				}

				$RegistryESDataStatus_id = 3; // не принят
				if (!empty($lnStatus) && $lnStatus == 1) {
					$RegistryESDataStatus_id = 2; // принят
				}

				// определяем Evn_id по коду
				$Evn_id = null;
				if (!empty($lnCode)) {
					$registry_data = $this->getRegistryESDataForImport(array(
						'RegistryES_id' => $RegistryES_id,
						'EvnStick_Num' => $lnCode
					));
					if (!empty($registry_data[0]['Evn_id'])) {
						$Evn_id = $registry_data[0]['Evn_id'];
						if ($RegistryESDataStatus_id == 2) {
							$acceptedEvns[$lnCode] = $Evn_id; // массив принятых ЛВН
						}
					}
				}

				$query = "
					select top 1 
						StickFSSData_id,
						StickFSSData_StickNum,
						StickFSSData_IsNeedMSE
					from v_StickFSSData
					where 
						StickFSSData_StickNum = :StickFSSData_StickNum
						and StickFSSData_IsNeedMSE = 2
				";
				$result = $this->db->query($query, array('StickFSSData_StickNum' => $lnCode));
				
				if (is_object($result)) {
					$result = $result->result('array');

					if ( !empty($lnState) && $lnState == '040' ) {
						if(empty($result)) {
							$this->addRequestMSE($data, $RegistryES_id, $lnCode);
						}
					}

					if ( 
						!empty($lnState) && $lnState == '050' 
						&& !empty($result) && $result[0]['StickFSSData_IsNeedMSE'] == 2 
					) {
						$query = "
							update StickFSSData with (rowlock)
							set StickFSSDataStatus_id = 4
							where 
								StickFSSData_StickNum = :StickFSSData_StickNum
								and StickFSSData_IsNeedMSE = 2
						";
						$queryParams = array(
							'StickFSSData_StickNum' => $result[0]['StickFSSData_StickNum']
						);

						$this->db->query($query, $queryParams);
					}
				}

				if (!empty($Evn_id)) {
					$error = $oneRow->getElementsByTagName('ERROR');
					foreach ($error as $oneError) {
						$errorCodeElem = $oneError->getElementsByTagName('ERR_CODE')->item(0);
						if (!empty($errorCodeElem)) {
							$errorCode = $errorCodeElem->nodeValue;
						}
						$errorMessageElem = $oneError->getElementsByTagName('ERR_MESS')->item(0);
						if (!empty($errorMessageElem)) {
							$errorMessage = $errorMessageElem->nodeValue;
						}

						$hasErrors = true;
						// сохраняем ошибку в БД
						$this->setRegistryESError(array(
							'RegistryES_id' => $RegistryES_id,
							'Evn_id' => $Evn_id,
							'Error_Code' => $errorCode,
							'Error_Message' => $errorMessage,
							'type' => 'fss',
							'pmUser_id' => $data['pmUser_id']
						));
					}

					$this->db->query("update RegistryESData with (rowlock) set RegistryESData_Hash = :RegistryESData_Hash, RegistryESDataStatus_id = :RegistryESDataStatus_id where RegistryES_id = :RegistryES_id and Evn_id = :Evn_id",
						array(
							'RegistryESData_Hash' => $lnHash,
							'RegistryESDataStatus_id' => $RegistryESDataStatus_id,
							'Evn_id' => $Evn_id,
							'RegistryES_id' => $RegistryES_id
						)
					);

					if (!empty($lnState) && !empty($this->StickFSSType[$lnState])) {
						$StickFSSType_id = $this->StickFSSType[$lnState];
						$this->db->query("update RegistryESData with (rowlock) set StickFSSType_id = :StickFSSType_id where RegistryES_id = :RegistryES_id and Evn_id = :Evn_id",
							array(
								'StickFSSType_id' => $StickFSSType_id,
								'Evn_id' => $Evn_id,
								'RegistryES_id' => $RegistryES_id
							)
						);
						// Выполняем на основной БД
						$this->dbMain->query("update EvnStickBase with (rowlock) set StickFSSType_id = :StickFSSType_id where EvnStickBase_id = :Evn_id",
							array(
								'StickFSSType_id' => $StickFSSType_id,
								'Evn_id' => $Evn_id
							)
						);
					}
				}
			}

			// все что не пришли в ответе должны стать не принятыми, чтобы можно было отправить в другом реестре
			$this->db->query("update RegistryESData with (rowlock) set RegistryESDataStatus_id = 3 where RegistryES_id = :RegistryES_id and RegistryESDataStatus_id = 1", array('RegistryES_id' => $RegistryES_id));

			$regStatus = $status->nodeValue;
			if ($regStatus == '0') {
				// реестр не принят, значит на нём могут быть общием ошибки, которые проставим всем непринятым случаям, если не было ошибок по случаям
				$message = $prParseFilelnlpuResponse->getElementsByTagName('MESS')->item(0);

				if (!$hasErrors) {
					$resp_evns = $this->queryResult("select Evn_id from v_RegistryESData (nolock) where RegistryES_id = :RegistryES_id and RegistryESDataStatus_id = 3", array(
						'RegistryES_id' => $RegistryES_id
					));

					foreach ($resp_evns as $resp_evn) {
						if (!empty($message)) {
							// сохраняем ошибку в БД
							$this->setRegistryESError(array(
								'RegistryES_id' => $RegistryES_id,
								'Evn_id' => $resp_evn['Evn_id'],
								'Error_Code' => '1',
								'Error_Message' => $message->nodeValue,
								'type' => 'fss',
								'pmUser_id' => $data['pmUser_id']
							));
						} else {
							// сохраняем ошибку в БД
							$this->setRegistryESError(array(
								'RegistryES_id' => $RegistryES_id,
								'Evn_id' => $resp_evn['Evn_id'],
								'Error_Code' => '1',
								'Error_Message' => 'STATUS=0',
								'type' => 'fss',
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}
				}
			}

			$this->updateIsInRegIsPaid(array(
				'RegistryES_id' => $RegistryES_id
			));

			if (!empty($xmlReq) && !empty($acceptedEvns)) {
				$this->XMLtoStickFssDataGet(array(
					'xml' => $xmlReq,
					'acceptedEvns' => $acceptedEvns,
					'pmUser_id' => $data['pmUser_id']
				));
			}

			// Если в реестре есть хотя бы один принятый то и весь реестр принят, иначе не принят.
			$resp_acc = $this->getFirstRowFromQuery("
				select
					sum(case when RegistryESDataStatus_id = 2 then 1 else 0 end) as accepted,
					sum(case when RegistryESDataStatus_id = 3 then 1 else 0 end) as declined
				from
					v_RegistryESData (nolock)
				where
					RegistryES_id = :RegistryES_id
			", array(
				'RegistryES_id' => $RegistryES_id
			));

			$RegistryESStatus_id = 6; // не принят ФСС
			if (!empty($resp_acc['accepted']) && !empty($resp_acc['declined'])) {
				// Есть принятые ЛВН и не принятые => "Принят ФСС с ошибками"
				$RegistryESStatus_id = 7;
			} else if (!empty($resp_acc['accepted'])) {
				// Есть только принятые ЛВН => "Принят ФСС"
				$RegistryESStatus_id = 5;
			}

			$this->setRegistryESStatus(array(
				'RegistryES_id' => $RegistryES_id,
				'RegistryESStatus_id' => $RegistryESStatus_id
			));

			return true;
		} else {
			$this->textlog->add("sendRegistryESToFSS prParseFilelnlpuResponse: в ответе нет тега prParseFilelnlpuResponse");
			$this->setRegistryESStatus(array(
				'RegistryES_id' => $RegistryES_id,
				'RegistryESStatus_id' => 8 // ошибка отправки
			));
			return array('Error_Msg' => 'prParseFilelnlpuResponse: в ответе нет тега prParseFilelnlpuResponse');
		}
	}

	/**
	 * Обновление признаков IsInReg и IsPaid
	 */
	function updateIsInRegIsPaid($data) {
		$queryParams = array(
			'RegistryES_id' => $data['RegistryES_id']
		);
		$filter = "";
		if (!empty($data['Evn_id'])) {
			$filter .= " and resd.Evn_id = :Evn_id";
			$queryParams['Evn_id'] = $data['Evn_id'];
		}

		// проставляем IsInReg/IsPaid на ЛВН
		$query = "
			update
				esb with (rowlock)
			set
				esb.EvnStickBase_IsInReg = case when ISNULL(esb.EvnStickBase_IsPaid, 1) = 1 and resd.RegistryESDataStatus_id = 3 then 1 else esb.EvnStickBase_IsInReg end, -- снимаем IsInReg, если IsPaid <> 2 и есть ошибки  
				esb.EvnStickBase_IsPaid = case when esb.EvnStickBase_IsInReg = 2 and resd.RegistryESDataStatus_id = 2 then 2 else esb.EvnStickBase_IsPaid end -- ставим IsPaid = 2, если IsInReg = 2 и нет ошибок
			from
				EvnStickBase esb
				inner join v_RegistryESData resd (nolock) on resd.Evn_id = esb.EvnStickBase_id
			where
				resd.RegistryES_id = :RegistryES_id
				{$filter}
		";
		$this->db->query($query, $queryParams);

		// проставляем IsInReg/IsPaid на дату направления в бюро МСЭ
		$query = "
			update
				es with (rowlock)
			set
				es.EvnStick_IsDateInReg = case when ISNULL(es.EvnStick_IsDateInFSS, 1) = 1 and resd.RegistryESDataStatus_id = 3 then 1 else es.EvnStick_IsDateInReg end, -- снимаем IsInReg, если IsPaid <> 2 и есть ошибки 
				es.EvnStick_IsDateInFSS = case when	es.EvnStick_IsDateInReg = 2	and resd.RegistryESDataStatus_id = 2 then 2 else es.EvnStick_IsDateInFSS end -- ставим IsPaid = 2, если IsInReg = 2 и нет ошибок	
			from
				EvnStick es
				inner join v_RegistryESData resd (nolock) on resd.Evn_id = es.EvnStick_id
			where
				resd.RegistryES_id = :RegistryES_id
				{$filter}
		";
		$this->db->query($query, $queryParams);

		// проставляем IsInReg/IsPaid на освобождениях
		$query = "
			update
				eswr with (rowlock)
			set
				eswr.EvnStickWorkRelease_IsInReg = case when ISNULL(eswr.EvnStickWorkRelease_IsPaid, 1) = 1 and resd.RegistryESDataStatus_id = 3 then 1 else eswr.EvnStickWorkRelease_IsInReg end, -- снимаем IsInReg, если IsPaid <> 2 и есть ошибки  
				eswr.EvnStickWorkRelease_IsPaid = case when eswr.EvnStickWorkRelease_IsInReg = 2 and resd.RegistryESDataStatus_id = 2 then 2 else eswr.EvnStickWorkRelease_IsPaid end -- ставим IsPaid = 2, если IsInReg = 2 и нет ошибок
			from
				EvnStickWorkRelease eswr
				inner join v_RegistryESData resd (nolock) on resd.FirstEvnStickWorkRelease_id = eswr.EvnStickWorkRelease_id
			where
				resd.RegistryES_id = :RegistryES_id
				{$filter}
		";
		$this->db->query($query, $queryParams);

		// проставляем IsInReg/IsPaid на освобождениях
		$query = "
			update
				eswr with (rowlock)
			set
				eswr.EvnStickWorkRelease_IsInReg = case when ISNULL(eswr.EvnStickWorkRelease_IsPaid, 1) = 1 and resd.RegistryESDataStatus_id = 3 then 1 else eswr.EvnStickWorkRelease_IsInReg end, -- снимаем IsInReg, если IsPaid <> 2 и есть ошибки  
				eswr.EvnStickWorkRelease_IsPaid = case when eswr.EvnStickWorkRelease_IsInReg = 2 and resd.RegistryESDataStatus_id = 2 then 2 else eswr.EvnStickWorkRelease_IsPaid end -- ставим IsPaid = 2, если IsInReg = 2 и нет ошибок
			from
				EvnStickWorkRelease eswr
				inner join v_RegistryESData resd (nolock) on resd.SecondEvnStickWorkRelease_id = eswr.EvnStickWorkRelease_id
			where
				resd.RegistryES_id = :RegistryES_id
				{$filter}
		";
		$this->db->query($query, $queryParams);

		// проставляем IsInReg/IsPaid на освобождениях
		$query = "
			update
				eswr with (rowlock)
			set
				eswr.EvnStickWorkRelease_IsInReg = case when ISNULL(eswr.EvnStickWorkRelease_IsPaid, 1) = 1 and resd.RegistryESDataStatus_id = 3 then 1 else eswr.EvnStickWorkRelease_IsInReg end, -- снимаем IsInReg, если IsPaid <> 2 и есть ошибки  
				eswr.EvnStickWorkRelease_IsPaid = case when eswr.EvnStickWorkRelease_IsInReg = 2 and resd.RegistryESDataStatus_id = 2 then 2 else eswr.EvnStickWorkRelease_IsPaid end -- ставим IsPaid = 2, если IsInReg = 2 и нет ошибок
			from
				EvnStickWorkRelease eswr
				inner join v_RegistryESData resd (nolock) on resd.ThirdEvnStickWorkRelease_id = eswr.EvnStickWorkRelease_id
			where
				resd.RegistryES_id = :RegistryES_id
				{$filter}
		";
		$this->db->query($query, $queryParams);
	}

	/**
	 * Проверка ФЛК для реестров
	 */
	function doFLKControlForAll($data) {
		$this->load->library('textlog', array('file'=>'doFLKControlForAll_'.date('Y-m-d').'.log'));
		$this->textlog->add("doFLKControlForAll begin");

		$filter = "";
		$queryParams = array();
		if (!empty($data['RegistryES_id'])) {
			$filter .= " and RegistryES_id = :RegistryES_id";
			$queryParams['RegistryES_id'] = $data['RegistryES_id'];
		}

		// берем очередь реестров на проверку ФЛК
		$resp = $this->queryResult("
			select
				RegistryES_id
			from
				v_RegistryES (nolock)
			where
				RegistryESStatus_id = 13
				{$filter}
			order by
				RegistryES_xmlExpDT asc
		", $queryParams);

		foreach($resp as $respone) {
			$this->textlog->add("проверка ФЛК реестра ".$respone['RegistryES_id']);

			// ставим статус "В процессе проверки ФЛК".
			$this->db->query("
				update
					RegistryES with (rowlock)
				set
					RegistryESStatus_id = 14, -- В процессе отправки
					RegistryES_updDT = dbo.tzGetDate()
				where
					RegistryES_id = :RegistryES_id
					and RegistryESStatus_id = 13 -- в очереди ФЛК
			", array(
				'RegistryES_id' => $respone['RegistryES_id']
			));

			$affected_rows = $this->db->affected_rows();
			if ($affected_rows < 1) {
				// пропускаем, если ничего не проапдейтили (значит уже обработан другим заданием)
				$this->textlog->add("реестр уже взялся на проверку ФЛК другим заданием");
				continue;
			}

			// запускаем проверку ФЛК
			$this->doFLKControl(array(
				'RegistryES_id' => $respone['RegistryES_id'],
				'pmUser_id' => $data['pmUser_id'],
				'session' => $data['session']
			));
		}

		// берем очередь зависших реестров в процессе проверки ФЛК на целый час и больше
		$resp = $this->queryResult("
			declare @dateLimit datetime = DATEADD(hour, -1, dbo.tzGetDate());
			
			select
				RegistryES_id
			from
				v_RegistryES (nolock)
			where
				RegistryESStatus_id = 14
				and RegistryES_updDT <= @dateLimit
			order by
				RegistryES_updDT asc
		", $queryParams);

		foreach($resp as $respone) {
			$this->textlog->add("Обрабатываем зависший реестр " . $respone['RegistryES_id']);

			$this->setRegistryESStatus(array(
				'RegistryES_id' => $respone['RegistryES_id'],
				'RegistryESStatus_id' => 13 // ожидает ФЛК
			));
		}

		$this->textlog->add("doFLKControlForAll end");

		return array('Error_Msg' => '');
	}

	/**
	 * Экспорт реестра ЛВН в xml
	 */
	function exportRegistryESToXml($data) {
		set_time_limit(0);

		$registry = $this->getRegistryESExport($data);
		if ( !is_array($registry) || count($registry) == 0 ) {
			return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.'));
		}
		$registry = $registry[0];

		if ($registry['RegistryESType_id'] == 3) {
			$registry_data = $this->loadRegistryESDataForDisableXml($data);

			if (!is_array($registry_data)) {
				return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении данных из реестра. Сообщите об ошибке разработчикам.'));
			}

			if (count($registry_data) == 0) {
				return array('success' => false, 'Error_Msg' => toUtf('Реестр не содержит ЛВН.'));
			}

			$ret = array();

			$ogrn = $this->getFirstResultFromQuery("select Lpu_Ogrn from v_Lpu (nolock) where Lpu_id = :Lpu_id", array(
				'Lpu_id' => $data['Lpu_id']
			));

			foreach ($registry_data as $item) {
				$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n";
				$xml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:fil="http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><soapenv:Header>';

				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($data['certbase64']);

				$xml .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/mo/' . $ogrn . '/ELN_' . $item['EvnStick_Num'],
					'block' => 'OGRN_' . $item['ogrn'],
					'BinarySecurityToken' => $data['certbase64'],
					'DigestValue' => '',
					'SignatureValue' => '',
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);

				$xml .= '</soapenv:Header>';
				$xml .= '<soapenv:Body wsu:Id="OGRN_'.$item['ogrn'].'">';
				$xml .= $item['LpuLn'];
				$xml .= '</soapenv:Body>';
				$xml .= '</soapenv:Envelope>';

				// echo "<textarea cols='100' rows='50'>{$xml}</textarea>";

				if (!empty($data['needHash'])) {
					$doc = new DOMDocument();
					$doc->loadXML($xml);
					$toHash = $doc->getElementsByTagName('Body')->item(0)->C14N(false, false);
					// считаем хэш
					$cryptoProHash = getCryptCpHash($toHash, $data['certbase64']);
					// 2. засовываем хэш в DigestValue
					$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue = $cryptoProHash;
					// 3. считаем хэш по SignedInfo
					$toSign = $doc->getElementsByTagName('SignedInfo')->item(0)->C14N(false, false);
					$Base64ToSign = base64_encode($toSign);

					$ret[] = array(
						'num' => $item['EvnStick_Num'],
						'xml' => $xml,
						'signs' => $item['LpuLnSign'],
						'Base64ToSign' => $Base64ToSign,
						'Hash' => $cryptoProHash
					);
				} else {
					$ret[] = array(
						'num' => $item['EvnStick_Num'],
						'xml' => $xml,
						'signs' => $item['LpuLnSign']
					);
				}
			}

			return $ret;
		} else {
			$registry_data = $this->loadRegistryESDataForXml($data, 'export');

			if (!is_array($registry_data)) {
				return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении данных из реестра. Сообщите об ошибке разработчикам.'));
			}

			if (count($registry_data) == 0) {
				return array('success' => false, 'Error_Msg' => toUtf('Реестр не содержит ЛВН.'));
			}

			$ret = array();

			$ogrn = $this->getFirstResultFromQuery("select Lpu_Ogrn from v_Lpu (nolock) where Lpu_id = :Lpu_id", array(
				'Lpu_id' => $data['Lpu_id']
			));

			foreach ($registry_data as $item) {
				$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n";
				$xml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:fil="http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><soapenv:Header>';

				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($data['certbase64']);

				$xml .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/mo/' . $ogrn . '/ELN_' . $item['EvnStick_Num'],
					'block' => 'ELN_' . $item['EvnStick_Num'],
					'BinarySecurityToken' => $data['certbase64'],
					'DigestValue' => '',
					'SignatureValue' => '',
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);

				$xml .= '</soapenv:Header>';
				$xml .= '<soapenv:Body>';
				$xml .= '<fil:ROWSET>';
				$xml .= $item['LpuLn'];
				$xml .= '</fil:ROWSET>';
				$xml .= '</soapenv:Body>';
				$xml .= '</soapenv:Envelope>';

				// echo "<textarea>{$xml}</textarea>";

				if (!empty($data['needHash'])) {
					$doc = new DOMDocument();
					$doc->loadXML($xml);
					$toHash = $doc->getElementsByTagName('ROW')->item(0)->C14N(false, false);
					// считаем хэш
					$cryptoProHash = getCryptCpHash($toHash, $data['certbase64']);
					// 2. засовываем хэш в DigestValue
					$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue = $cryptoProHash;
					// 3. считаем хэш по SignedInfo
					$toSign = $doc->getElementsByTagName('SignedInfo')->item(0)->C14N(false, false);
					$Base64ToSign = base64_encode($toSign);

					$ret[] = array(
						'num' => $item['EvnStick_Num'],
						'xml' => $xml,
						'signs' => $item['LpuLnSign'],
						'Base64ToSign' => $Base64ToSign,
						'Hash' => $cryptoProHash
					);
				} else {
					$ret[] = array(
						'num' => $item['EvnStick_Num'],
						'xml' => $xml,
						'signs' => $item['LpuLnSign']
					);
				}
			}

			return $ret;
		}
	}

	/**
	 * Экспорт данных для проверки в ФСС
	 */
	function exportRegistryESDataForCheckInFSS($data) {
		$this->load->library('parser');

		$registry_data = $this->queryResult("
			select top 1
				RESD.Evn_id,
				O.Org_OGRN as Lpu_OGRN,
				RESD.EvnStick_Num,
				RESD.Person_Snils
			from
				v_RegistryESData RESD with (nolock)
				left join v_Lpu L with (nolock) on l.Lpu_id = :Lpu_id
				left join v_Org O with (nolock) on O.Org_id = L.Org_id
			where
				RESD.RegistryES_id = :RegistryES_id
				and RESD.Evn_id = :Evn_id 
		", array(
			'RegistryES_id' => $data['RegistryES_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Evn_id' => $data['Evn_id']
		));

		if (!is_array($registry_data)) {
			return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении данных из реестра. Сообщите об ошибке разработчикам.'));
		}

		if (count($registry_data) == 0) {
			return array('success' => false, 'Error_Msg' => toUtf('ЛВН не найден в реестре.'));
		}

		$ret = array();

		foreach ($registry_data as $item) {
			$this->load->helper('openssl');
			$certAlgo = getCertificateAlgo($data['certbase64']);

			$xml_data = array(
				'ogrn' => $item['Lpu_OGRN'],
				'lnCode' => $item['EvnStick_Num'],
				'snils' => $item['Person_Snils'],
				'filehash' => (!empty($data['filehash']) ? $data['filehash'] : ''),
				'filesign' => (!empty($data['filesign']) ? $data['filesign'] : ''),
				'certbase64' => (!empty($data['certbase64']) ? $data['certbase64'] : ''),
				'signatureMethod' => $certAlgo['signatureMethod'],
				'digestMethod' => $certAlgo['digestMethod']
			);

			$template = 'get_ln_data';
			$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n" . $this->parser->parse('export_xml/' . $template, $xml_data, true);

			// echo "<textarea cols='100' rows='50'>{$xml}</textarea>";

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

				$ret[] = array(
					'num' => $item['EvnStick_Num'],
					'xml' => $xml,
					'Base64ToSign' => $Base64ToSign,
					'Hash' => $cryptoProHash
				);
			} else {
				$ret[] = array(
					'num' => $item['EvnStick_Num'],
					'xml' => $xml
				);
			}
		}

		return $ret;
	}

	/**
	 * Экспорт данных для проверки в ФСС
	 */
	function exportRegistryESDataForCheckInFSSList($data) {
		$res = [];
		if (is_array($data['RegistryES_Data'])) {
			foreach($data['RegistryES_Data'] as $item) {
				$item['Lpu_id'] = $data['Lpu_id'];
				$item['certbase64'] = $data['certbase64'];
				$item['needHash'] = $data['needHash'];
				$res = array_merge($res, $this->exportRegistryESDataForCheckInFSS($item));
			}
		}
		return $res;
	}

	/**
	 * Получение списка реестров ЛВН
	 * @param $data
	 * @return array|bool
	 */
	function loadRegistryESGrid($data) {
		$params = array();
		$filters = "";

		if (!empty($data['Lpu_id'])) {
			$filters .= " and RES.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['RegistryES_id'])) {
			$filters .= " and RES.RegistryES_id";
			$params['RegistryES_id'] = $data['RegistryES_id'];
		}
		if (!empty($data['RegistryES_DateRange'][0]) && !empty($data['RegistryES_DateRange'][1])) {
			$filters .= " and RES.RegistryES_begDate between :RegistryES_Date1 and :RegistryES_Date2";
			$params['RegistryES_Date1'] = $data['RegistryES_DateRange'][0];
			$params['RegistryES_Date2'] = $data['RegistryES_DateRange'][1];
		}
		if (!empty($data['RegistryES_Num'])) {
			$filters .= " and RES.RegistryES_Num = :RegistryES_Num";
			$params['RegistryES_Num'] = $data['RegistryES_Num'];
		}
		if (!empty($data['EvnStick_Num'])) {
			$filters .= " and exists(select top 1 RESD.Evn_id from v_RegistryESData RESD (nolock) where RESD.EvnStick_Num = :EvnStick_Num and RESD.RegistryES_id = RES.RegistryES_id)";
			$params['EvnStick_Num'] = $data['EvnStick_Num'];
		}
		if (!empty($data['RegistryESStatus_id'])) {
			$filters .= " and RES.RegistryESStatus_id = :RegistryESStatus_id";
			$params['RegistryESStatus_id'] = $data['RegistryESStatus_id'];
		}
		if (!empty($data['RegistryESType_id'])) {
			$filters .= " and RES.RegistryESType_id = :RegistryESType_id";
			$params['RegistryESType_id'] = $data['RegistryESType_id'];
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filters .= " and RES.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filters .= " and RES.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		$query = "
			select
				RES.RegistryES_id,
				RegistryES_Num,
				REST.RegistryESType_Name,
				convert(varchar(10), RES.RegistryES_begDate, 104) as RegistryES_begDate,
				convert(varchar(10), RES.RegistryES_insDT, 104) as RegistryES_insDT,
				convert(varchar(10), RES.RegistryES_updDT, 104) as RegistryES_updDT,
				RES.RegistryES_RecordCount,			-- Всего ЛВН в реестре
				RESD.RegistryES_SuccessCount,		-- Принятые ЛВН в ФСС
				RES.RegistryES_ErrorCount,			-- Не принятые ЛВН в ФСС
				RES.Lpu_id,
				RES.Lpu_Nick,
				RES.Lpu_FSSRegNum,
				RES.Lpu_INN,
				RES.Lpu_OGRN,
				RES.RegistryES_UserFIO,
				RES.RegistryES_UserPhone,
				RES.RegistryES_UserEmail,
				RES.RegistryES_Export,
				RES.RegistryESType_id,
				LB.LpuBuilding_Name,
				LS.LpuSection_Name,
				RESS.RegistryESStatus_id,
				RESS.RegistryESStatus_Code,
				RESS.RegistryESStatus_Name,
				case when (
					RESS.RegistryESStatus_Code = 3
					OR (
						RESS.RegistryESStatus_Code = 2
						and not exists(
							select top 1
								resr.Evn_id
							from
								v_RegistryESError resr (nolock)
								left join v_RegistryESErrorType ret (nolock) on ret.RegistryESErrorType_id = resr.RegistryESErrorType_id
							where
								resr.RegistryES_id = RES.RegistryES_id
								and ISNULL(ret.RegistryESErrorType_Code, 0) <> '005' -- разрешить только при наличии ошибок ФЛК
						)
					)
				) then 1 else 0 end as RegistryESStatus_EnableManualActions
			from
				v_RegistryES RES with(nolock)
				left join v_RegistryESType REST with (nolock) on REST.RegistryESType_id = RES.RegistryESType_id
				left join v_RegistryESStatus RESS with(nolock) on RESS.RegistryESStatus_id = RES.RegistryESStatus_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = RES.LpuBuilding_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = RES.LpuSection_id
				outer apply(
					select top 1 count(t.Evn_id) as RegistryES_SuccessCount
					from v_RegistryESData t with(nolock)
					left join v_RegistryESDataStatus RESDS with(nolock) on RESDS.RegistryESDataStatus_id = t.RegistryESDataStatus_id
					where t.RegistryES_id = RES.RegistryES_id and RESDS.RegistryESDataStatus_Code = 2
				) RESD
			where
				not exists (
					select top 1
						RESQ.RegistryESQueue_id
					from
						v_RegistryESQueue RESQ with (nolock)
					where
						RESQ.RegistryES_id = RES.RegistryES_id
				)
				{$filters}
			
			union all
				
			select
				-RES.RegistryESQueue_id as RegistryES_id,
				RegistryES_Num,
				REST.RegistryESType_Name,
				convert(varchar(10), RES.RegistryES_begDate, 104) as RegistryES_begDate,
				convert(varchar(10), RES.RegistryESQueue_insDT, 104) as RegistryES_insDT,
				convert(varchar(10), RES.RegistryESQueue_updDT, 104) as RegistryES_updDT,
				null as RegistryES_RecordCount,			-- Всего ЛВН в реестре
				RESD.RegistryES_SuccessCount,		-- Принятые ЛВН в ФСС
				RES.RegistryES_ErrorCount,			-- Не принятые ЛВН в ФСС
				RES.Lpu_id,
				RES.Lpu_Nick,
				RES.Lpu_FSSRegNum,
				RES.Lpu_INN,
				RES.Lpu_OGRN,
				RES.RegistryES_UserFIO,
				RES.RegistryES_UserPhone,
				RES.RegistryES_UserEmail,
				RES.RegistryES_Export,
				RES.RegistryESType_id,
				LB.LpuBuilding_Name,
				LS.LpuSection_Name,
				RESS.RegistryESStatus_id,
				RESS.RegistryESStatus_Code,
				RESS.RegistryESStatus_Name,
				case when (
					RESS.RegistryESStatus_Code = 3
					OR (
						RESS.RegistryESStatus_Code = 2
						and not exists(
							select top 1
								resr.Evn_id
							from
								v_RegistryESError resr (nolock)
								left join v_RegistryESErrorType ret (nolock) on ret.RegistryESErrorType_id = resr.RegistryESErrorType_id
							where
								resr.RegistryES_id = RES.RegistryES_id
								and ISNULL(ret.RegistryESErrorType_Code, 0) <> '005' -- разрешить только при наличии ошибок ФЛК
						)
					)
				) then 1 else 0 end as RegistryESStatus_EnableManualActions
			from
				v_RegistryESQueue RES with(nolock)
				left join v_RegistryESType REST with (nolock) on REST.RegistryESType_id = RES.RegistryESType_id
				left join v_RegistryESStatus RESS with(nolock) on RESS.RegistryESStatus_id = RES.RegistryESStatus_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = RES.LpuBuilding_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = RES.LpuSection_id
				outer apply(
					select top 1 count(t.Evn_id) as RegistryES_SuccessCount
					from v_RegistryESData t with(nolock)
					left join v_RegistryESDataStatus RESDS with(nolock) on RESDS.RegistryESDataStatus_id = t.RegistryESDataStatus_id
					where t.RegistryES_id = RES.RegistryES_id and RESDS.RegistryESDataStatus_Code = 2
				) RESD
			where
				(1=1)
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return array('data' => $result->result('array'));
	}

	/**
	 * Получение данных ЛВН в реестре
	 * @param $data
	 * @return array|bool
	 */
	function loadRegistryESDataGrid($data) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);
		$filters = "";

		if (!empty($data['EvnStick_Num'])) {
			$filters .= " and RESD.EvnStick_Num like :EvnStick_Num";
			$params['EvnStick_Num'] = $data['EvnStick_Num']."%";
		}
		if (!empty($data['Person_Fio'])) {
			$filters .= " and rtrim(RESD.Person_SurName)+' '+rtrim(RESD.Person_FirName)+' '+rtrim(isnull(RESD.Person_SecName, '')) like :Person_Fio";
			$params['Person_Fio'] = $data['Person_Fio']."%";
		}
		if (!empty($data['Person_BirthDay'])) {
			$filters .= " and RESD.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if (!empty($data['RegistryESDataStatus_id'])) {
			$filters .= " and RESD.RegistryESDataStatus_id = :RegistryESDataStatus_id";
			$params['RegistryESDataStatus_id'] = $data['RegistryESDataStatus_id'];
		}
		if (!empty($data['RegistryESType_id'])) {
			if ($data['RegistryESType_id'] == 1) {
				$filters .= " and rEС.EvnClass_SysNick = 'EvnPL'";
			} else if ($data['RegistryESType_id'] == 2) {
				$filters .= " and rEС.EvnClass_SysNick = 'EvnPS'";
			}
		}

		$query = "
			select
				RESD.Evn_id,
				RESD.RegistryES_id,
				case
					when RES.RegistryESStatus_id in (3,9) and RESD.RegistryESDataStatus_id = 1 and RES.RegistryESType_id in (1,3) then 1 
					else 0
				end as delAccess,
				RESD.EvnStick_Num,
				RESD.Person_SurName+' '+RESD.Person_FirName+' '+isnull(RESD.Person_SecName, '') as Person_Fio,
				convert(varchar(10), RESD.Person_BirthDay, 104) as Person_BirthDay,
				L.Lpu_Nick,
				O.Org_Nick,
				fMP.Person_Fio as MedPersonal_fFio,
				dMP.Person_Fio as MedPersonal_dFio,
				convert(varchar(10), FirstEvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), coalesce(
					ThirdEvnStickWorkRelease_endDT,
					SecondEvnStickWorkRelease_endDT,
					FirstEvnStickWorkRelease_endDT
				), 104) as EvnStickWorkRelease_endDate,
				case
					when rEС.EvnClass_SysNick = 'EvnPL' then 'ТАП'
					when rEС.EvnClass_SysNick = 'EvnPS' then 'КВС'
				end as RegistryESType_Name,
				case
					when rEС.EvnClass_SysNick = 'EvnPL' then EPL.EvnPL_NumCard
					when rEС.EvnClass_SysNick = 'EvnPS' then EPS.EvnPS_NumCard
				end as Evn_rNum,
				RESDS.RegistryESDataStatus_Name,
				SFT.StickFSSType_Name,
				case when ESBE.Evn_deleted = 2 then 'Да' else '' end as EvnStick_deleted
			from
				v_RegistryESData RESD with(nolock)
				inner join v_RegistryESDataStatus RESDS with (nolock) on RESDS.RegistryESDataStatus_id = RESD.RegistryESDataStatus_id
				inner join v_RegistryES RES with(nolock) on RES.RegistryES_id = RESD.RegistryES_id
				inner join v_EvnStickBase_all ESB with(nolock) on ESB.EvnStickBase_id = RESD.Evn_id
				inner join Evn ESBE with (nolock) on ESBE.Evn_id = ESB.EvnStickBase_id
				left join EvnStick ESP (nolock) on ESP.EvnStick_id = ESBE.Evn_pid
				left join EvnStickBase ESBP with (nolock) on ESBP.EvnStickBase_id = ESP.EvnStick_id
				left join v_Lpu L with(nolock) on L.Lpu_id = ESBE.Lpu_id
				left join v_Org O with(nolock) on O.Org_id = ESB.Org_id
				left join v_pmUserCache fpmUser with(nolock) on fpmUser.pmUser_id = ESBE.pmUser_insID
				outer apply (
					select top 1 t2.Person_FIO
					from v_EvnStickWorkRelease t1 with (nolock)
						inner join v_MedPersonal t2 with (nolock) on t2.MedPersonal_id = t1.MedPersonal_id
					where t1.EvnStickBase_id = ESB.EvnStickBase_id
					order by t1.EvnStickWorkRelease_begDT
				) fMP
				--left join v_pmUserCache dpmUser with(nolock) on dpmUser.pmUser_id = ESBE.pmUser_updID
				left join v_MedStaffFact dMSF with(nolock) on dMSF.MedStaffFact_id = isnull(ESBP.MedStaffFact_id,ESB.MedStaffFact_id)
				outer apply (
					select top 1 Person_Fio from v_MedPersonal MP with(nolock) where MP.MedPersonal_id = ISNULL(ESB.MedPersonal_id, dMSF.MedPersonal_id)
				) dMP
				--inner join v_Evn rE with(nolock) on rE.Evn_id = ESBE.Evn_rid
				inner join Evn rE with(nolock) on rE.Evn_id = ESB.EvnStickBase_mid
				inner join EvnClass rEС with(nolock) on rEС.EvnClass_id = rE.EvnClass_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = ESB.EvnStickBase_mid
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ESB.EvnStickBase_mid
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = RESD.StickFSSType_id
			where
				RESD.RegistryES_id = :RegistryES_id
				and RESDS.RegistryESDataStatus_Code in ('1','2','3','5')
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return array('data' => $result->result('array'));
	}

	/**
	 * Получение списка ошибок ФЛК/ФСС в реестре
	 * @param $data
	 * @return array|bool
	 */
	function loadRegistryESErrorGrid($data) {
		$filters = "";
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESErrorStageType_Code' => $data['RegistryESErrorStageType_Code']
		);

		if (!empty($data['RegistryESErrorStageType_Code'])) {
			if ($data['RegistryESErrorStageType_Code'] == '2') {
				// для ФСС отображаем ещё и ошибки типа "Сбой"
				$filters .= " and RESEST.RegistryESErrorStageType_Code IN (2,3)";
			} else {
				$filters .= " and RESEST.RegistryESErrorStageType_Code = :RegistryESErrorStageType_Code";
			}
		}

		if (!empty($data['EvnStick_Num'])) {
			$filters .= " and RESD.EvnStick_Num like :EvnStick_Num";
			$params['EvnStick_Num'] = $data['EvnStick_Num']."%";
		}
		if (!empty($data['Person_Fio'])) {
			$filters .= " and rtrim(RESD.Person_SurName)+' '+rtrim(RESD.Person_FirName)+' '+rtrim(isnull(RESD.Person_SecName, '')) like :Person_Fio";
			$params['Person_Fio'] = $data['Person_Fio']."%";
		}
		if (!empty($data['Person_BirthDay'])) {
			$filters .= " and RESD.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if (!empty($data['RegistryESDataStatus_id'])) {
			$filters .= " and RESD.RegistryESDataStatus_id = :RegistryESDataStatus_id";
			$params['RegistryESDataStatus_id'] = $data['RegistryESDataStatus_id'];
		}
		if (!empty($data['RegistryESType_id'])) {
			if ($data['RegistryESType_id'] == 1) {
				$filters .= " and rEС.EvnClass_SysNick = 'EvnPL'";
			} else if ($data['RegistryESType_id'] == 2) {
				$filters .= " and rEС.EvnClass_SysNick = 'EvnPS'";
			}
		}

		$query = "
			select
				ROW_NUMBER() OVER (ORDER BY RESE.Evn_id) as RegistryESError_id,
				RESE.Evn_id,
				RESD.EvnStick_Num,
				isnull(RESE.RegistryESError_Code, RESET.RegistryESErrorType_Code) as RegistryESError_Code,
				RESET.RegistryESErrorType_Name,
				isnull(RESE.RegistryESError_Descr, RESET.RegistryESErrorType_Name) as RegistryESError_Descr,
				RESD.Person_SurName+' '+RESD.Person_FirName+' '+isnull(RESD.Person_SecName, '') as Person_Fio,
				convert(varchar(10), RESD.Person_BirthDay, 104) as Person_BirthDay,
				fMP.Person_Fio as MedPersonal_fFio,
				dMP.Person_Fio as MedPersonal_dFio,
				case
					when rEС.EvnClass_SysNick like 'EvnPL' then 'ТАП'
					when rEС.EvnClass_SysNick like 'EvnPS' then 'КВС'
				end as RegistryESType_Name,
				case
					when rEС.EvnClass_SysNick like 'EvnPL' then EPL.EvnPL_NumCard
					when rEС.EvnClass_SysNick like 'EvnPS' then EPS.EvnPS_NumCard
				end as Evn_rNum,
				RESDS.RegistryESDataStatus_Code,
				RESDS.RegistryESDataStatus_Name,
				SFT.StickFSSType_Name,
				case when exists(select top 1 RegistryESFiles_id from v_RegistryESFiles (nolock) where RegistryES_id = RESD.RegistryES_id and Evn_id = RESD.Evn_id) then 1 else 0 end as existRegistryESFiles,
				case when ESBE.Evn_deleted = 2 then 'Да' else '' end as EvnStick_deleted
			from
				v_RegistryESError RESE with(nolock)
				left join v_RegistryESErrorType RESET with(nolock) on RESET.RegistryESErrorType_id = RESE.RegistryESErrorType_id
				left join v_RegistryESErrorStageType RESEST with(nolock) on RESEST.RegistryESErrorStageType_id = RESET.RegistryESErrorStageType_id
				inner join v_RegistryESData RESD with(nolock) on RESD.Evn_id = RESE.Evn_id and RESD.RegistryES_id = RESE.RegistryES_id
				inner join EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = RESE.Evn_id
				inner join Evn ESBE with (nolock) on ESBE.Evn_id = ESB.EvnStickBase_id
				--left join v_pmUserCache dpmUser with(nolock) on dpmUser.pmUser_id = ESBE.pmUser_updID
				outer apply (
					select top 1 t2.Person_FIO
					from v_EvnStickWorkRelease t1 with (nolock)
						inner join v_MedPersonal t2 with (nolock) on t2.MedPersonal_id = t1.MedPersonal_id
					where t1.EvnStickBase_id = ESB.EvnStickBase_id
					order by t1.EvnStickWorkRelease_begDT
				) fMP
				outer apply (
					select top 1 Person_Fio from v_MedPersonal MP with(nolock) where MP.MedPersonal_id = ESB.MedPersonal_id
				) dMP
				--inner join v_Evn rE with(nolock) on rE.Evn_id = ESBE.Evn_rid
				inner join Evn rE with(nolock) on rE.Evn_id = ESBE.Evn_rid
				inner join EvnClass rEС with(nolock) on rEС.EvnClass_id = rE.EvnClass_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = rE.Evn_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = rE.Evn_id
				left join v_RegistryESDataStatus RESDS with(nolock) on RESDS.RegistryESDataStatus_id = RESD.RegistryESDataStatus_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = RESD.StickFSSType_id
			where
				RESE.RegistryES_id = :RegistryES_id
				{$filters}
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('', 'Ошибка при запросе спика ошибок реестра');
		}
		return array('data' => $response);
	}

	/**
	 * Формирование реестра ЛВН
	 * @param $data
	 * @return bool
	 */
	function saveRegistryES($data) {
		$params = $data;

		if (!empty($data['RegistryES_id'])) {
			$resp_check = $this->checkAccessToReformRegistry(array(
				'RegistryES_id' => $data['RegistryES_id'],
				'action' => 'reform'
			));
			if (!empty($resp_check['Error_Msg'])) {
				return $resp_check;
			}

			$resp = $this->deleteRegistryESError($data);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		$params['RegistryESStatus_id'] = 12;
		$params['RegistryES_UserFIO'] = $data['session']['surname'].' '.$data['session']['firname'].' '.$data['session']['secname'];
		$params['RegistryES_UserPhone'] = $data['session']['phone'];
		$params['RegistryES_UserEmail'] = $data['session']['email'];

		$query = "
			select top 1
				L.Lpu_Nick,
				L.Lpu_FSSRegNum,
				O.Org_INN as Lpu_INN,
				O.Org_OGRN as Lpu_OGRN
			from
				v_Lpu L with(nolock)
				left join v_Org O with(nolock) on O.Org_id = L.Org_id
			where L.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('', 'Ошибка при запросе данных МО');
		}

		$params['Lpu_Nick'] = $resp[0]['Lpu_Nick'];
		$params['Lpu_FSSRegNum'] = $resp[0]['Lpu_FSSRegNum'];
		$params['Lpu_INN'] = $resp[0]['Lpu_INN'];
		$params['Lpu_OGRN'] = $resp[0]['Lpu_OGRN'];

		if (empty($params['RegistryES_id'])) {
			$params['RegistryES_id'] = null;
		}

		// проверка на дубли
		$filters = "";
		$doubleParams = array(
			'RegistryES_begDate' => $data['RegistryES_begDate'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryES_Num' => $data['RegistryES_Num']
		);
		if (!empty($data['RegistryES_id'])) {
			$filters .= " and RegistryES_id <> :RegistryES_id";
			$doubleParams['RegistryES_id'] = $data['RegistryES_id'];
		}
		$resp_double = $this->queryResult("
			select top 1
				RES.RegistryES_id
			from
				v_RegistryES RES with(nolock)
			where
				RES.RegistryES_begDate = :RegistryES_begDate
				and RES.Lpu_id = :Lpu_id
				and RES.RegistryES_Num = :RegistryES_Num
				{$filters}
			
			union all
			
			select
				-RES.RegistryESQueue_id as RegistryES_id
			from
				v_RegistryESQueue RES with(nolock)
			where
				RES.RegistryES_begDate = :RegistryES_begDate
				and RES.Lpu_id = :Lpu_id
				and RES.RegistryES_Num = :RegistryES_Num
		", $doubleParams);
		if ( ! empty($resp_double[0]['RegistryES_id'])) {
			if ($resp_double[0]['RegistryES_id'] > 0) {
				return $this->createError('', 'Уже существует реестр №'.$data['RegistryES_Num'].' от '.date('d.m.Y', strtotime($data['RegistryES_begDate'])));
			} else {
				return $this->createError('', 'Уже добавлен в очередь реестр №'.$data['RegistryES_Num'].' от '.date('d.m.Y', strtotime($data['RegistryES_begDate'])));
			}
		}

		$query = "
			declare
				@RegistryESQueue_id bigint = null,
				@Error_Code int,
				@Error_Message varchar(4000),
				@Num int;
			exec p_RegistryESQueue_ins
				@RegistryESQueue_id = @RegistryESQueue_id output,
				@RegistryES_id = :RegistryES_id,
				@RegistryESStatus_id = :RegistryESStatus_id,
				@RegistryESType_id = :RegistryESType_id,
				@RegistryES_begDate = :RegistryES_begDate,
				@RegistryES_endDate = :RegistryES_begDate,
				@RegistryES_Num = :RegistryES_Num,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuSection_id = :LpuSection_id,
				@RegistryES_RecordCount = 0,
				@RegistryES_ErrorCount = 0,
				@Lpu_id = :Lpu_id,
				@Lpu_Nick = :Lpu_Nick,
				@Lpu_FSSRegNum = :Lpu_FSSRegNum,
				@Lpu_INN = :Lpu_INN,
				@Lpu_OGRN = :Lpu_OGRN,
				@RegistryES_UserFIO = :RegistryES_UserFIO,
				@RegistryES_UserPhone = :RegistryES_UserPhone,
				@RegistryES_UserEmail = :RegistryES_UserEmail,
				@RegistryES_RegRecCount = :RegistryES_RegRecCount,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT
			select @RegistryESQueue_id as RegistryESQueue_id,@Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}


	/**
	 * Надо добавить проверку, что реестра ЛВН (RegistryES) нет в очереди (RegistryESQueue).
	 *
	 * @param $data
	 * @return array
	 */
	function checkExistRegistryESQueue($data){

		$query = "
			select top 1
				RES.RegistryESQueue_id,
				RES.RegistryES_begDate,
				RES.RegistryES_Num
			from
				v_RegistryESQueue RES with(nolock)
			where
				RES.RegistryES_id = :RegistryES_id
		";
		$check_resp = $this->queryResult($query, array(
			'RegistryES_id' => $data['RegistryES_id']
		));
		if ( ! empty($check_resp[0]['RegistryESQueue_id'])) {
			return array('Error_Msg' => 'Уже добавлен в очередь реестр №'.$data['RegistryES_Num'].' от '.date('d.m.Y', strtotime($data['RegistryES_begDate'])));
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Проверка доступности удаления/переформирования реестра
	 */
	function checkAccessToReformRegistry($data) {
		$query = "
			select top 1
				RES.RegistryES_id,
				RES.RegistryESStatus_id,
				RESS.RegistryESStatus_Name
			from
				v_RegistryES RES with (nolock)
				left join v_RegistryESStatus RESS with (nolock) on RESS.RegistryESStatus_id = RES.RegistryESStatus_id
			where
				RES.RegistryES_id = :RegistryES_id
		";
		$check_resp = $this->queryResult($query, array(
			'RegistryES_id' => $data['RegistryES_id']
		));
		if (empty($check_resp[0]['RegistryES_id'])) {
			return array('Error_Msg' => 'Ошибка при получении данных реестра');
		}
		if (in_array($check_resp[0]['RegistryESStatus_id'], array(1, 5, 7, 8, 11, 12, 13, 14, 15))) {
			return array('Error_Msg' => 'Реестр не может быть ' . ($data['action'] == 'delete' ? 'удален' : 'переформирован') . ', т.к. его статус "' . $check_resp[0]['RegistryESStatus_Name'] . '".');
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Удаление реестра ЛВН
	 */
	function deleteRegistryES($data) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);

		$resp_check = $this->checkAccessToReformRegistry(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'action' => 'delete'
		));
		if (!empty($resp_check['Error_Msg'])) {
			return $resp_check;
		}

		$resp_checkExistRegistryESQueue = $this->checkExistRegistryESQueue(array(
			'RegistryES_id' => $data['RegistryES_id']
		));
		if ( ! empty($resp_checkExistRegistryESQueue['Error_Msg'])) {
			return $resp_checkExistRegistryESQueue;
		}

		$this->beginTransaction();

		$resp = $this->setAllRegistryESDataStatus(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESDataStatus_id' => null
		));
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_RegistryES_del
				@RegistryES_id = :RegistryES_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->queryResult($query, $params);

		if ($this->isSuccessful($response)) {
			$this->commitTransaction();
		} else {
			$this->rollbackTransaction();
		}

		return $response;
	}

	/**
	 * Получение данных для формы переформирования ЛВН
	 * @param $data
	 * @return bool
	 */
	function loadRegistryESForm($data) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);

		$query = "
			select top 1
				RES.RegistryES_id,
				RES.RegistryES_Num,
				RES.LpuBuilding_id,
				RES.LpuSection_id,
				convert(varchar(10), RES.RegistryES_insDT, 104) as RegistryES_insDT,
				convert(varchar(10), RES.RegistryES_begDate, 104) as RegistryES_begDate,
				RES.RegistryES_RegRecCount,
				RES.RegistryESType_id
			from v_RegistryES RES with(nolock)
			where RES.RegistryES_id = :RegistryES_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Получение номера для нового ЛВН
	 * @param $data
	 * @return array|bool
	 */
	function getNewRegistryESNum($data) {
		$filters = "";
		$params = array(
			'RegistryES_begDate' => $data['RegistryES_begDate'],
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['RegistryES_id'])) {
			$filters .= " and RegistryES_id <> :RegistryES_id";
			$params['RegistryES_id'] = $data['RegistryES_id'];
		}

		$query = "
			select
				isnull(max(RegistryES_Num), 0)+1 as RegistryES_Num
			from
				(
					select
						cast(RegistryES_Num as int) as RegistryES_Num
					from
						v_RegistryES RES with(nolock)
					where
						RES.RegistryES_begDate = :RegistryES_begDate
						and RES.Lpu_id = :Lpu_id
						{$filters}
					
					union all
					
					select
						cast(RegistryES_Num as int) as RegistryES_Num
					from
						v_RegistryESQueue RES with(nolock)
					where
						RES.RegistryES_begDate = :RegistryES_begDate
						and RES.Lpu_id = :Lpu_id
				) as res
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');
		return array('success' => true, 'RegistryES_Num' => $response[0]['RegistryES_Num']);
	}

	/**
	 * Удаление записи реестра ЛВН
	 */
	function _deleteRegistryESData($data) {
		// o	реестр в статусе «Готов к отправке» или «В ожидании ЭП физ лиц»
		// o	ЭЛН со статусом в реестре: «В реестре»
		// o	Тип реестра «Электронные ЛН», «ЛН на удаление».
		$resp = $this->queryResult("
			select
				RESD.Evn_id,
				RES.RegistryESType_id
			from
				v_RegistryESData RESD (nolock)
				inner join v_RegistryES RES (nolock) on RES.RegistryES_id = RESD.RegistryES_id
			where
				RESD.Evn_id = :Evn_id
				and RESD.RegistryES_id = :RegistryES_id
				and RES.RegistryESStatus_id in (3,9)
				and RESD.RegistryESDataStatus_id = 1
				and RES.RegistryESType_id in (1,3) 
		", array(
			'Evn_id' => $data['Evn_id'],
			'RegistryES_id' => $data['RegistryES_id']
		));

		if (empty($resp[0]['Evn_id'])) {
			return array('Error_Msg' => 'Не найдена запись для удаления');
		}

		if ($resp[0]['RegistryESType_id'] == 3) {
			// Для реестров с типом «ЛН на удаление»:
			// Удалить ЭЛН из реестра
			$resp_del = $this->queryResult("
				Declare @Error_Code bigint;
				Declare @Error_Message varchar(4000);
				exec p_RegistryESData_del
					@Evn_id = :Evn_id,
					@RegistryES_id = :RegistryES_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg;
			", array(
				'Evn_id' => $data['Evn_id'],
				'RegistryES_id' => $data['RegistryES_id']
			));

			if (!empty($resp_del[0]['Error_Msg'])) {
				return $resp_del;
			}
		} else {
			// Удаление ЭЛН из реестра.
			$resp_del = $this->queryResult("
				Declare @Error_Code bigint;
				Declare @Error_Message varchar(4000);
				exec p_RegistryESData_del
					@Evn_id = :Evn_id,
					@RegistryES_id = :RegistryES_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg;
			", array(
				'Evn_id' => $data['Evn_id'],
				'RegistryES_id' => $data['RegistryES_id']
			));

			if (!empty($resp_del[0]['Error_Msg'])) {
				return $resp_del;
			}
		}

		// Если в реестре больше нет ЭЛН со статусом в реестре «В реестре» и реестр в статусе "готов к отправке", то статус реестра должен измениться на «Ошибки ФЛК».

		$this->db->query("
			declare
				@RegistryES_id bigint = :RegistryES_id,
				@count bigint,
				@count_inreg bigint;
				
			set @count = (select count(Evn_id) from v_RegistryESData with (nolock) where RegistryES_id = @RegistryES_id);
			set @count_inreg = (select count(Evn_id) from v_RegistryESData with (nolock) where RegistryES_id = @RegistryES_id and RegistryESDataStatus_id = 1);
			
			update
				RegistryES with (rowlock)
			set
				RegistryES_RecordCount = @count,
				RegistryESStatus_id = case when @count_inreg = 0 then 2 else RegistryESStatus_id end
			where
				RegistryES_id = @RegistryES_id
				and RegistryESStatus_id = 3;
		", array(
			'RegistryES_id' => $data['RegistryES_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 * Удаление нескольких записей реестра ЛВН
	 */
	function deleteRegistryESData($data) {
		if (is_array($data['RegistryES_ids'])) {
			foreach($data['RegistryES_ids'] as $item) {
				$res = $this->_deleteRegistryESData($item);
				if (!empty($res['Error_Msg']) && $res['Error_Msg'] != 'Не найдена запись для удаления') return $res;
			}
		}
		
		return array('Error_Msg' => '');
	}

	/**
	 * Получение количества случаев в реестре ЛВН
	 */
	function getRegistryESDataCount($data) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);

		$query = "
			select top 1 RES.RegistryES_RecordCount
			from v_RegistryES RES with(nolock)
			where RES.RegistryES_id = :RegistryES_id
		";

		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Сохранение пути до файла экспорта реестра ЛВН
	 */
	function setRegistryESExport($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryES_Export' => $data['RegistryES_Export'],
		);

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400)
			set nocount on
			begin try
				update RegistryES with (rowlock)
				set RegistryES_Export = :RegistryES_Export
				where RegistryES_id = :RegistryES_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select :RegistryES_id as RegistryES_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при изменении пути до файла экспорта реестра');
		}
		return $result->result('array');
	}

	/**
	 * Сохранение пути до файла экспорта реестра ЛВН
	 */
	function setRegistryESXmlExport($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryES_xmlExportPath' => $data['RegistryES_xmlExportPath']
		);

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400)
			set nocount on
			begin try
				update RegistryES with (rowlock)
				set
					RegistryES_xmlImportPath = null,
					RegistryES_xmlExportPath = :RegistryES_xmlExportPath,
					RegistryES_xmlExpDT = dbo.tzGetDate(),
					RegistryESStatus_id = 1
				where
					RegistryES_id = :RegistryES_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select :RegistryES_id as RegistryES_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при изменении пути до файла экспорта реестра');
		}
		return $result->result('array');
	}

	/**
	 * Проставление статуса всем случаям реестра ЛВН
	 */
	function setAllRegistryESDataStatus($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESDataStatus_id' => $data['RegistryESDataStatus_id'],
		);

		$filter = "";
		if ($params['RegistryESDataStatus_id'] != 1 && $params['RegistryESDataStatus_id'] !== null) {
			$filter = " and isnull(RegistryESDataStatus_id,1) = 1";
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400)
			set nocount on
			begin try
				update RegistryESData with (rowlock)
				set RegistryESDataStatus_id = :RegistryESDataStatus_id
				where
					RegistryES_id = :RegistryES_id
					{$filter}
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при изменении статуса реестра');
		}
		return $result->result('array');
	}

	/**
	 * Изменение статуса реестра ЛВН
	 */
	function setRegistryESStatus($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESStatus_id' => $data['RegistryESStatus_id'],
		);

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400)
			set nocount on
			begin try
				update RegistryES with (rowlock)
				set RegistryESStatus_id = :RegistryESStatus_id
				where RegistryES_id = :RegistryES_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select :RegistryES_id as RegistryES_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при изменении статуса реестра');
		}

		$resp = $result->result('array');

		if (!empty($resp[0]['RegistryES_id']) && empty($resp[0]['Error_Msg']) && $data['RegistryESStatus_id'] == 8) {
			// всем ЛВН со статусом "В реестре" присваивается статус "Требуется проверка ЭЛН в ФСС"
			$items = $this->queryResult("select RESD.Evn_id from v_RegistryESData RESD (nolock) where RESD.RegistryES_id = :RegistryES_id and RESD.RegistryESDataStatus_id = 1", array(
				'RegistryES_id' => $data['RegistryES_id']
			));
			foreach($items as $item) {
				$resp = $this->setRegistryESDataStatus(array(
					'RegistryES_id' => $data['RegistryES_id'],
					'Evn_id' => $item['Evn_id'],
					'RegistryESDataStatus_id' => 7 // Требуется проверка ЭЛН в ФСС
				));

				if (!empty($resp[0]['Error_Msg'])) {
					return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
				}
			}
		}

		return $resp;
	}

	/**
	 * Изменение статуса случая в реестре ЛВН
	 */
	function setRegistryESDataStatus($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'Evn_id' => $data['Evn_id'],
			'RegistryESDataStatus_id' => $data['RegistryESDataStatus_id'],
		);

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400)
			set nocount on
			begin try
				update RegistryESData with (rowlock)
				set RegistryESDataStatus_id = :RegistryESDataStatus_id
				where RegistryES_id = :RegistryES_id and Evn_id = :Evn_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select :Evn_id as Evn_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при изменении статуса случая в реестре');
		}
		return $result->result('array');
	}

	/**
	 * Изменение статуса случая в реестре ЛВН
	 */
	function setRegistryESErrorCount($data) {
		$fields = "";
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryES_ErrorCount' => $data['RegistryES_ErrorCount'],
		);

		if (isset($data['RegistryES_NumNoSignDoctor'])) {
			$params['RegistryES_NumNoSignDoctor'] = $data['RegistryES_NumNoSignDoctor'];
			$fields .= " , RegistryES_NumNoSignDoctor = :RegistryES_NumNoSignDoctor";
		}
		if (isset($data['RegistryES_NumNoSignDelegateVK'])) {
			$params['RegistryES_NumNoSignDelegateVK'] = $data['RegistryES_NumNoSignDelegateVK'];
			$fields .= " , RegistryES_NumNoSignDelegateVK = :RegistryES_NumNoSignDelegateVK";
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(400)
			set nocount on
			begin try
				update
					RegistryES with (rowlock)
				set
					RegistryES_ErrorCount = :RegistryES_ErrorCount
					{$fields}
				where
					RegistryES_id = :RegistryES_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select :RegistryES_id as RegistryES_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при обновлении количества ошибок в реестре');
		}
		return $result->result('array');
	}

	/**
	 * Получение списка для подписи ЛВН в реестре
	 */
	function loadRegistryESIndividCertGrid($data) {
		return $this->queryResult("
			select
				emst.ExpertMedStaffType_id,
				emst.ExpertMedStaffType_Name,
				reic.RegistryESIndividCert_CertSubjectName,
				reic.RegistryESIndividCert_CertThumbprint,
				reic.pmUser_Name,
				case when emst.ExpertMedStaffType_id = 1 then re.RegistryES_NumNoSignDelegateVK else re.RegistryES_NumNoSignDoctor end as RegistryES_CountNoSign
			from
				v_ExpertMedStaffType emst (nolock)
				outer apply (
					select top 1
						pu.pmUser_Name,
						reic.RegistryESIndividCert_CertSubjectName,
						reic.RegistryESIndividCert_CertThumbprint
					from
						v_RegistryESIndividCert reic (nolock)
						left join v_pmUser pu (nolock) on pu.pmUser_id = reic.pmUser_insID
					where
						reic.RegistryES_id = :RegistryES_id
						and reic.ExpertMedStaffType_id = emst.ExpertMedStaffType_id
					order by
						reic.RegistryESIndividCert_insDT desc
				) reic
				left join v_RegistryES re (nolock) on re.RegistryES_id = :RegistryES_id
			where
				emst.ExpertMedStaffType_id IN (1,3)
			order by
				emst.ExpertMedStaffType_id desc
		", array(
			'RegistryES_id' => $data['RegistryES_id']
		));
	}

	/**
	 * Получение данных о выгружаемом реестре
	 * @param $data
	 * @return bool
	 */
	function getRegistryESExport($data) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);

		$query = "
			select top 1
				RES.RegistryES_id,
				RES.RegistryES_Num,
				RES.Lpu_OGRN,
				RES.Lpu_INN,
				RES.Lpu_FSSRegNum,
				convert(varchar(10), RES.RegistryES_begDate, 120) as RegistryES_Date,
				RES.RegistryES_UserFIO,
				RES.RegistryES_UserPhone,
				RES.RegistryES_UserEmail,
				LL.LpuLicence_Num,
				RES.RegistryES_Export,
				RES.RegistryESType_id,
				RESS.RegistryESStatus_id,
				RESS.RegistryESStatus_Code,
				RESS.RegistryESStatus_Name
			from
				v_RegistryES RES with(nolock)
				left join v_RegistryESStatus RESS with (nolock) on RESS.RegistryESStatus_id = RES.RegistryESStatus_id
				outer apply(
					select top 1 t.LpuLicence_Num
					from v_LpuLicence t with(nolock)
					where t.Lpu_id = RES.Lpu_id
					and (t.LpuLicence_begDate is null or t.LpuLicence_begDate >= dbo.tzGetDate())
					and (t.LpuLicence_endDate is null or t.LpuLicence_endDate < dbo.tzGetDate())
				) LL
			where RES.RegistryES_id = :RegistryES_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 *  Контроль ФЛК
	 */
	function doFLKControl($data)
	{
		set_time_limit(0);

		$this->deleteRegistryESError($data, 'flk');

		$registry = $this->getRegistryESExport($data);
		if ( !is_array($registry) || count($registry) == 0 ) {
			return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.'));
		}
		$registry = $registry[0];
		if ($registry['RegistryESType_id'] == 3) {
			// для ЛВН на удаление контроль ФЛК не нужен, т.к. там отправляются всего 2 поля.
			// признак ЛВН в реестре ставится хранимкой, никаких дополнительных действий не нужно.
			$items = $this->queryResult("select RESD.Evn_id from v_RegistryESData RESD (nolock) where RESD.RegistryES_id = :RegistryES_id", array(
				'RegistryES_id' => $data['RegistryES_id']
			));
			foreach($items as $item) {
				$resp = $this->setRegistryESDataStatus(array(
					'RegistryES_id' => $data['RegistryES_id'],
					'Evn_id' => $item['Evn_id'],
					'RegistryESDataStatus_id' => 1
				));

				if (!empty($resp[0]['Error_Msg'])) {
					$this->deleteRegistryESError($data, 'flk');
					return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
				}
			}

			$resp = $this->setRegistryESStatus(array(
				'RegistryES_id' => $data['RegistryES_id'],
				'RegistryESStatus_id' => 3 // Готов к отправке
			));
			if (!empty($resp[0]['Error_Msg'])) {
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			}
		} else {
			$registry_data = $this->loadRegistryESDataForXml($data, 'reconciliation');
			if ( !is_array($registry_data) ) {
				return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении данных из реестра. Сообщите об ошибке разработчикам.'));
			}

			$this->load->library('parser');
			$template = 'export_registry_es';

			$EvnStickErrors = array();

			// Если в настройках МО разрешена подпись ЭЛН уполномоченным лицом (см. ТЗ Форма Настройки.docx), то проверка наличия ЭП не производится (для таких случаев проверка будет проходить после подписания уполномоченным лицом).
			$enableSignEvnStickAuthPerson = false;
			// проверям наличие настройки подписи ЭЛН уполномоченным лицом
			$resp_ds = $this->queryResult("
				select top 1
					ds.DataStorage_id
				from
					RegistryES res (nolock) 
					inner join DataStorage ds (nolock) on res.Lpu_id = ds.Lpu_id and DataStorage_Name = 'enable_sign_evnstick_auth_person' and DataStorage_Value = '1'
				where
					RegistryES_id = :RegistryES_id
			", array(
				'RegistryES_id' => $data['RegistryES_id']
			));

			if (!empty($resp_ds[0]['DataStorage_id'])) {
				$enableSignEvnStickAuthPerson = true;
			}

			$noSignDoc = 0;
			$noSignVK = 0;

			foreach($registry_data as $item) {
				$is_error = false;
				$params = array(
					'RegistryES_id' => $data['RegistryES_id'],
					'RegistryESErrorStageType_id' => 1,
					'Evn_id' => $item['Evn_id'],
					'Error_Code' => null,
					'Error_Message' => null,
					'pmUser_id' => $data['pmUser_id']
				);

				$xsd_tpl = $_SERVER['DOCUMENT_ROOT'].'/documents/xsd/ln.xsd';

				libxml_use_internal_errors(true);
				$xml = new DOMDocument();

				$xml_str = '<fil:ROWSET xmlns:fil="http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">'.$item['LpuLn'].'</fil:ROWSET>';
				// echo "<textarea>{$xml_str}</textarea>";
				$xml->loadXML($xml_str);

				if (empty($item['LN_HASH'])) {
					// Если у ЛВН, есть хотя бы один период нетрудоспособности с признаком: «Принят ФСС»
					// Иначе, если у ЛВН есть признак «ЛВН из ФСС»
					$resp_check = $this->queryResult("
						select top 1
							case when esb.EvnStickBase_IsFSS = 2 or exists(select top 1 EvnStickWorkRelease_id from v_EvnStickWorkRelease where EvnStickBase_id = esb.EvnStickBase_id and EvnStickWorkRelease_IsPaid = 2) then 1 else 0 end as isInFSS
						from
							v_EvnStickBase esb (nolock)
						where
							esb.EvnStickBase_id = :EvnStickBase_id
					", array(
						'EvnStickBase_id' => $item['Evn_id']
					));

					if (!empty($resp_check[0]['isInFSS'])) {
						$params['Error_Code'] = '006';
						$params['Error_Message'] = 'Не заполнен ХЭШ';
						$resp = $this->setRegistryESError($params);
					}
				}

				$error = '';
				$emptyfields = array();
				if (empty($item['SNILS'])) {
					$emptyfields[] = 'СНИЛС';
				}
				if (empty($item['SURNAME'])) {
					$emptyfields[] = 'Фамилия';
				}
				if (empty($item['NAME'])) {
					$emptyfields[] = 'Имя';
				}
				if (empty($item['LN_CODE'])) {
					$emptyfields[] = 'Номер ЛН';
				}
				if (empty($item['LPU_NAME'])) {
					$emptyfields[] = 'Наименование МО';
				}
				if (empty($item['REASON1'])) {
					$emptyfields[] = 'Причина нетрудоспособности';
				}
				if (empty($item['BIRTHDAY'])) {
					$emptyfields[] = 'Дата рождения';
				}
				if (empty($item['GENDER']) && $item['GENDER'] != '0') {
					$emptyfields[] = 'Пол';
				}

				if (!empty($emptyfields)) {
					$error = 'Не заполнен обязательный элемент: ' . implode(', ', $emptyfields);
				}

				if (!empty($error)) {
					$params['Error_Code'] = '002';
					$params['Error_Message'] = $error;
					$resp = $this->setRegistryESError($params);
					if (!empty($resp[0]['Error_Msg'])) {
						$this->deleteRegistryESError($data, 'flk');
						return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
					}
				} else if (!$xml->schemaValidate($xsd_tpl)) {
					$is_error = true;
					$errors = libxml_get_errors();

					//print_r($errors);exit;
					foreach ($errors as $error)
					{
						switch($error->code)
						{
							case 1824:
								$Error_Code = '001';
								break;

							case 1871:
								$Error_Code = '002';
								break;

							case 1840:
								$Error_Code = '003';
								break;

							default:
								$Error_Code = '004';
						}

						//Переводим английские ошибки на русский
						$comment = $error->message;
						$comment = str_replace('The value \'\' is not accepted by the pattern \'.*[^\s].*\'', 'Элемент не заполнен, данный элемент обязателен для заполнения', $comment);
						$comment = str_replace('is not accepted by the pattern', 'не удовлетворяет шаблону', $comment);
						$comment = str_replace('error parsing attribute name', 'Ошибка синтаксического анализа названия атрибута', $comment);
						$comment = str_replace('This element is not expected. Expected is one of', 'Указан не верный элемент. Ожидается один из', $comment);
						$comment = str_replace('The value has a length of', 'Значение имеет длинну', $comment);
						$comment = str_replace('Missing child element(s)', 'Пропущен дочерний элемент', $comment);
						$comment = str_replace('Expected is one of', 'Ожидается один из ', $comment);
						$comment = str_replace('has more digits than are allowed', 'Состоит из большего числа знаков чем допустимо', $comment);
						$comment = str_replace('this exceeds the allowed maximum length of', 'Максимальное количество символов', $comment);
						$comment = str_replace('this underruns the allowed minimum length of', 'Минимальное количество символов', $comment);
						$comment = str_replace('is greater than the maximum value allowed', 'больше чем максимально допустимое значение', $comment);
						$comment = str_replace('is not a valid value of the local atomic type', 'тип данных не соответствует определённому в схеме', $comment);
						$comment = str_replace('is not a valid value of the atomic type', 'тип данных не соответствует определённому в схеме', $comment);
						$comment = str_replace('This element is not expected. Expected is one of', 'Указан не верный элемент. Ожидается один из следующих элементов', $comment);
						$comment = str_replace('This element is not expected. Expected is', 'Указан не верный элемент. Ожидается элемент', $comment);
						$comment = str_replace('The value', 'Значение', $comment);
						$comment = str_replace('facet \'pattern\'', 'ограничение схемы', $comment);
						$comment = str_replace('maxLength', 'Максимальная длинна', $comment);
						$comment = str_replace('minLength', 'Минимальная длинна', $comment);
						$comment = str_replace('facet', 'ограничение', $comment);
						$comment = str_replace('Element', 'Элемент', $comment);
						$comment = str_replace('{http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl}', '', $comment);
						$comment = preg_replace('/\bSNILS\b/', 'СНИЛС', $comment);
						$comment = preg_replace('/\bSURNAME\b/', 'Фамилия', $comment);
						$comment = preg_replace('/\bNAME\b/', 'Имя', $comment);
						$comment = preg_replace('/\bLN_CODE\b/', 'Номер ЛН', $comment);
						$comment = preg_replace('/\bLPU_NAME\b/', 'Наименование МО', $comment);
						$comment = preg_replace('/\bREASON1\b/', 'Причина нетрудоспособности', $comment);
						$comment = preg_replace('/\bBIRTHDAY\b/', 'Дата рождения', $comment);
						$comment = preg_replace('/\bGENDER\b/', 'Пол', $comment);

						$params['Error_Code'] = $Error_Code;
						$params['Error_Message'] = $comment;
						$resp = $this->setRegistryESError($params);
						if (!empty($resp[0]['Error_Msg'])) {
							$this->deleteRegistryESError($data, 'flk');
							return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
						}

						break; //пока сохраняется только одна ошибка на ЛВН из-за primary key в RegistryESError
					}
					libxml_clear_errors();
				} else {
					$f = json_decode(json_encode((array) simplexml_load_string($xml_str)),1);

					if (isset($f['careInfo']['fio'])) {
						$f['careInfo'] = array($f['careInfo']);
					}
					if (isset($f['treatmentInfo']['treatmentPeriod'])) {
						$f['treatmentInfo'] = array($f['treatmentInfo']);
					}

					$cond_arr = array(
						/*'EMPLOYER' => empty($f['EMPLOYER']) && !empty($f['EMPL_FLAG']) && $f['EMPL_FLAG'] <> 3,
						'DATE1' => empty($f['DATE1']) && !empty($f['REASON3']),
						'DATE2' => empty($f['DATE2']) && !empty($f['DATE1']) && !empty($f['REASON2']) && in_array($f['REASON2'], array('017','018','019')),
						'VOUCHER_NO' => empty($f['VOUCHER_NO']) && !empty($f['DATE1']) && !empty($f['REASON2']) && in_array($f['REASON2'], array('017','018','019')),
						'SERV1_AGE' => empty($f['SERV1_AGE']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')) && empty($f['SERV1_MM']),
						'SERV1_MM' => empty($f['SERV1_MM']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')) && empty($f['SERV1_AGE']),
						'SERV1_RELATION_CODE' => empty($f['SERV1_RELATION_CODE']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')),
						'SERV1_FIO' => empty($f['SERV1_FIO']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')),
						'SERV2_AGE' => empty($f['SERV2_AGE']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')) && !empty($f['SERV2_FIO']) && empty($f['SERV2_MM']),
						'SERV2_MM' => empty($f['SERV2_MM']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')) && !empty($f['SERV2_FIO']) && empty($f['SERV2_AGE']),
						'SERV2_RELATION_CODE' => empty($f['SERV2_RELATION_CODE']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')) && !empty($f['SERV2_FIO']),
						'HOSPITAL_DT1' => empty($f['HOSPITAL_DT1']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')) && !empty($f['HOSPITAL_DT2']),
						'HOSPITAL_DT2' => empty($f['HOSPITAL_DT2']) && !empty($f['REASON1']) && in_array($f['REASON1'], array('09','12','13','14','15')) && !empty($f['HOSPITAL_DT1']),
						'HOSPITAL_BREACH_CODE' => empty($f['HOSPITAL_BREACH_CODE']) && !empty($f['HOSPITAL_BREACH_DT']),
						'HOSPITAL_BREACH_DT' => empty($f['HOSPITAL_BREACH_DT']) && !empty($f['HOSPITAL_BREACH_CODE']),
						'MSE_RESULT' =>  empty($f['MSE_RESULT']) && empty($f['RETURN_DATE_LPU']),
						'TREAT2_DT1' =>  empty($f['TREAT2_DT1']) && !empty($f['TREAT2_DT2']),
						'TREAT2_DT2' =>  empty($f['TREAT2_DT2']) && !empty($f['TREAT2_DT1']),
						'TREAT2_DOCTOR_ROLE' =>  empty($f['TREAT2_DOCTOR_ROLE']) && !empty($f['TREAT2_DT1']),
						'TREAT2_DOCTOR' =>  empty($f['TREAT2_DOCTOR']) && !empty($f['TREAT2_DT1']),
						'TREAT3_DT1' =>  empty($f['TREAT3_DT1']) && !empty($f['TREAT3_DT2']),
						'TREAT3_DT2' =>  empty($f['TREAT3_DT2']) && !empty($f['TREAT3_DT1']),
						'TREAT3_DOCTOR_ROLE' =>  empty($f['TREAT3_DOCTOR_ROLE']) && !empty($f['TREAT3_DT1']),
						'TREAT3_DOCTOR' =>  empty($f['TREAT3_DOCTOR']) && !empty($f['TREAT3_DT1']),
						'OTHER_STATE_DT' =>  empty($f['OTHER_STATE_DT']) && !empty($f['MSE_RESULT']) && in_array($f['MSE_RESULT'], array('32','33','34','36')),
						'RETURN_DATE_LPU' =>  empty($f['RETURN_DATE_LPU']) && empty($f['MSE_RESULT']),
						'NEXT_LN_CODE' =>  empty($f['NEXT_LN_CODE']) && !empty($f['MSE_RESULT']) && $f['MSE_RESULT'] == 31,*/
						'yy' => isset($f['careInfo']) && ((isset($f['careInfo'][0]) && empty($f['careInfo'][0]['yy']) && empty($f['careInfo'][0]['mm'])) || (isset($f['careInfo'][1]) && empty($f['careInfo'][1]['yy']) && empty($f['careInfo'][1]['mm']))),
						'mm' => isset($f['careInfo']) && ((isset($f['careInfo'][0]) && empty($f['careInfo'][0]['mm']) && empty($f['careInfo'][0]['yy'])) || (isset($f['careInfo'][1]) && empty($f['careInfo'][1]['mm']) && empty($f['careInfo'][1]['yy']))),
					);

					foreach($cond_arr as $field => $field_error) {
						if($field_error) {
							$is_error = true;
							$params['Error_Code'] = '002';
							$params['Error_Message'] = 'Не заполнен обязательный элемент ('.$field.')';
							$resp = $this->setRegistryESError($params);
							if (!empty($resp[0]['Error_Msg'])) {
								$this->deleteRegistryESError($data, 'flk');
								return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
							}
							break; //пока сохраняется только одна ошибка на ЛВН из-за primary key в RegistryESError
						}
					}
				}

				if (!$enableSignEvnStickAuthPerson && !$is_error && !empty($item['emptySign']) && $registry['RegistryESType_id'] == 1) { // данная ошибка только для реестров "Электронный ЛН"
					$is_error = true;
					$params['Error_Code'] = '005';
					$params['Error_Message'] = 'Не все данные в ЛВН подтверждены электронной подписью';
					$resp = $this->setRegistryESError($params);
					if (!empty($resp[0]['Error_Msg'])) {
						$this->deleteRegistryESError($data, 'flk');
						return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
					}
				}

				if (
					($item['HOSPITAL_DT1'] == '###' && $item['HOSPITAL_DT2'] != '###')
					|| ($item['HOSPITAL_DT2'] == '###' && $item['HOSPITAL_DT1'] != '###')
				) {
					$is_error = true;
					$params['Error_Code'] = '007';
					$params['Error_Message'] = 'Не заполнено одно из группы полей лечения в стационаре: дата начала или дата окончания';
					$resp = $this->setRegistryESError($params);
					if (!empty($resp[0]['Error_Msg'])) {
						$this->deleteRegistryESError($data, 'flk');
						return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
					}
				}

				if (
					getRegionNick() != 'kz'
					&& empty($item['LPU_EMPLOYER']) && !empty($item['LPU_EMPL_FLAG'])
					&& $item['LPU_EMPL_FLAG'] != 0 && $item['LPU_EMPL_FLAG'] != 1
				) {
					$is_error = true;
					$params['Error_Code'] = '009';
					$params['Error_Message'] = 'Не заполнено поле «Наименование для указания в ЛВН» на форме «Выписка ЛВН»';
					$resp = $this->setRegistryESError($params);
					if (!empty($resp[0]['Error_Msg'])) {
						$this->deleteRegistryESError($data, 'flk');
						return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
					}
				}


				if ($registry['RegistryESType_id'] == 1) { // считаем кол-во ЛВН без подписи только для реестров "Электронный ЛН"
					if (!empty($item['emptySignDoc'])) {
						$noSignDoc++;
					}
					if (!empty($item['emptySignVK'])) {
						$noSignVK++;
					}
				}

				// Если у ЛВН, есть хотя бы один период нетрудоспособности с признаком: «Принят ФСС»,
				// Иначе, если у ЛВН есть признак «ЛВН из ФСС»
				// В остальных случаях не производится.
				if (empty($item['LN_HASH'])) {
					// Если тег LN_HASH не заполнен, то ЛВН присваивается ошибка ФЛК: «Не заполнен ХЭШ».

				}

				$resp = $this->setRegistryESDataStatus(array(
					'RegistryES_id' => $data['RegistryES_id'],
					'Evn_id' => $item['Evn_id'],
					'RegistryESDataStatus_id' => $is_error ? 4 : 1
				));
				if (!empty($resp[0]['Error_Msg'])) {
					$this->deleteRegistryESError($data, 'flk');
					return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
				}

				if ($is_error) {
					if (empty($EvnStickErrors[$item['Evn_id']])) {
						$EvnStickErrors[$item['Evn_id']] = 1;
					} else {
						$EvnStickErrors[$item['Evn_id']]++;
					}
				}
			}

			$recErr = count($EvnStickErrors);

			$resp = $this->setRegistryESErrorCount(array(
				'RegistryES_id' => $data['RegistryES_id'],
				'RegistryES_ErrorCount' => $recErr,
				'RegistryES_NumNoSignDoctor' => $noSignDoc,
				'RegistryES_NumNoSignDelegateVK' => $noSignVK
			));
			if (!empty($resp[0]['Error_Msg'])) {
				$this->deleteRegistryESError($data, 'flk');
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			}

			// Снимаем признаки, если при формировании найдены ошибки ФЛК или у ЛВН нет исхода
			$this->db->query("
				exec p_Registry_EvnStick_InReg_upd
					@RegistryES_id = :RegistryES_id
			", array(
				'RegistryES_id' => $data['RegistryES_id']
			));

			// И в последнюю очередь меняем статус реестра
			$RegistryESStatus_id = null;
			if (count($registry_data) > 0) {
				$RegistryESStatus_id = (count($registry_data) == $recErr) ? 2 : 3;
			}

			if ($enableSignEvnStickAuthPerson && $RegistryESStatus_id == 3 && ($noSignDoc > 0 || $noSignVK > 0)) { // Готов к отправке и есть неподписанные блоки
				$RegistryESStatus_id = 9; // тогда статус "В ожидании ЭП физ лиц"
			}

			if ( $RegistryESStatus_id != null || count($registry_data) == 0 ) { //Пустой статус может быть только если в реестре нет ЛВН
				$resp = $this->setRegistryESStatus(array(
					'RegistryES_id' => $data['RegistryES_id'],
					'RegistryESStatus_id' => $RegistryESStatus_id
				));
			}
			if (!empty($resp[0]['Error_Msg'])) {
				$this->deleteRegistryESError($data, 'flk');
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			}
		}

		return array('success' => true);
	}

	/**
	 * Получение данныж для выгрузки
	 * @param $data
	 * @return bool
	 */
	function loadRegistryESDataForXmlManual($data, $mode = 'export') {
		$params = array('RegistryES_id' => $data['RegistryES_id']);
		$filter = '';

		if ($mode == 'export') {
			$filter .= " 
				and (isnull(t.RegistryESDataStatus_id, 1) = 1
				OR (
					t.RegistryESDataStatus_id = 4
					and not exists(
						select top 1
							resr.Evn_id
						from
							v_RegistryESError resr (nolock)
							left join v_RegistryESErrorType ret (nolock) on ret.RegistryESErrorType_id = resr.RegistryESErrorType_id
						where
							resr.RegistryES_id = t.RegistryES_id
							and resr.Evn_id = t.Evn_id
							and ISNULL(ret.RegistryESErrorType_Code, 0) <> '005' -- разрешить только при наличии ошибок ФЛК
					)
				))
			";
		} else if ($mode == 'reconciliation') {
			$filter .= " and isnull(t.RegistryESDataStatus_id, 1) in (1,4)";
		}

		$query_xml = "
			select top 1
				isnull(RESD.Person_Snils,'') as 'SNILS',
				RESD.Person_SurName as 'SURNAME',
				RESD.Person_FirName as 'NAME',
				RESD.Person_SecName as 'PATRONIMIC',
				RESD.Org_StickNick as 'EMPLOYER',
				RESD.RegistryESData_EmploymentType as 'EMPL_FLAG',
				isnull(RESD.EvnStick_Num,'') as 'LN_CODE',
				isnull(RESD.EvnStick_NumPrev,'') as 'PREV_LN_CODE',
				RESD.EvnStick_IsPrimary as 'PRIMARY_FLAG',
				RESD.EvnStick_IsDuplicate as 'DUPLICATE_FLAG',
				convert(varchar(10),RESD.EvnStick_setDate,20) as 'LN_DATE',
				RESD.Lpu_StickNick as 'LPU_NAME',
				RESD.Lpu_StickAddress as 'LPU_ADDRESS',
				RESD.Lpu_OGRN as 'LPU_OGRN',
				convert(varchar(10),RESD.Person_BirthDay,20) as 'BIRTHDAY',
				case when Sex.Sex_Code=1 then 0 when Sex.Sex_Code=2 then 1 end as 'GENDER',
				RESD.StickCause_Code as 'REASON1',
				RESD.StickCauseDopType_Code as 'REASON2',
				RESD.StickCause_CodeAlter as 'REASON3',
				RESD.Diag_Code as 'DIAGNOS',
				ISNULL(RESD.EvnStick_NumPar, '') as 'PARENT_CODE',
				convert(varchar(10),RESD.EvnStick_StickDT,20) as 'DATE1',
				convert(varchar(10),RESD.EvnStick_sstEndDate,20) as 'DATE2',
				RESD.EvnStick_sstNum as 'VOUCHER_NO',
				RESD.Org_sstOGRN as 'VOUCHER_OGRN',
				RESD.FirstRelated_Age as 'SERV1_AGE',
				RESD.FirstRelated_AgeMonth as 'SERV1_MM',
				RESD.FirstRelatedLinkType_Code as 'SERV1_RELATION_CODE',
				RESD.FirstRelated_FIO as 'SERV1_FIO',
				RESD.SecondRelated_Age as 'SERV2_AGE',
				RESD.SecondRelated_AgeMonth as 'SERV2_MM',
				RESD.SecondRelatedLinkType_Code as 'SERV2_RELATION_CODE',
				RESD.SecondRelated_FIO as 'SERV2_FIO',
				RESD.EvnStick_IsRegPregnancy as 'PREGN12W_FLAG',
				convert(varchar(10),RESD.EvnStick_stacBegDate,20) as 'HOSPITAL_DT1',
				convert(varchar(10),RESD.EvnStick_stacEndDate,20) as 'HOSPITAL_DT2',
				RESD.StickIrregularity_Code as 'HOSPITAL_BREACH_CODE',
				convert(varchar(10),RESD.EvnStick_irrDT,20) as 'HOSPITAL_BREACH_DT',
				convert(varchar(10),RESD.EvnStick_mseDT,20) as 'MSE_DT1',
				convert(varchar(10),RESD.EvnStick_mseRegDT,20) as 'MSE_DT2',
				convert(varchar(10),RESD.EvnStick_mseExamDT,20) as 'MSE_DT3',
				null as 'MSE_INVALID_GROUP',
				RESD.StickLeaveType_Code as 'MSE_RESULT',
				convert(varchar(10),RESD.FirstEvnStickWorkRelease_begDT,20) as 'TREAT1_DT1',
				convert(varchar(10),RESD.FirstEvnStickWorkRelease_endDT,20) as 'TREAT1_DT2',
				RESD.FirstPost_Code as 'TREAT1_DOCTOR_ROLE',
				RESD.FirstMedPersonal_Fin as 'TREAT1_DOCTOR',
				RESD.FirstMedPersonal_Inn as 'TREAT1_DOC_ID',
				RESD.FirstPostVK_Code as 'TREAT1_DOCTOR2_ROLE',
				RESD.FirstMedPersonalVK_Fin as 'TREAT1_CHAIRMAN_VK',
				RESD.FirstMedPersonalVK_Inn as 'TREAT1_DOC2_ID',
				convert(varchar(10),RESD.SecondEvnStickWorkRelease_begDT,20) as 'TREAT2_DT1',
				convert(varchar(10),RESD.SecondEvnStickWorkRelease_endDT,20) as 'TREAT2_DT2',
				RESD.SecondPost_Code as 'TREAT2_DOCTOR_ROLE',
				RESD.SecondMedPersonal_Fin as 'TREAT2_DOCTOR',
				RESD.SecondMedPersonal_Inn as 'TREAT2_DOC_ID',
				RESD.SecondPostVK_Code as 'TREAT2_DOCTOR2_ROLE',
				RESD.SecondMedPersonalVK_Fin as 'TREAT2_CHAIRMAN_VK',
				RESD.SecondMedPersonalVK_Inn as 'TREAT2_DOC2_ID',
				convert(varchar(10),RESD.ThirdEvnStickWorkRelease_begDT,20) as 'TREAT3_DT1',
				convert(varchar(10),RESD.ThirdEvnStickWorkRelease_endDT,20) as 'TREAT3_DT2',
				RESD.ThirdPost_Code as 'TREAT3_DOCTOR_ROLE',
				RESD.ThirdMedPersonal_Fin as 'TREAT3_DOCTOR',
				RESD.ThirdMedPersonal_Inn as 'TREAT3_DOC_ID',
				RESD.ThirdPostVK_Code as 'TREAT3_DOCTOR2_ROLE',
				RESD.ThirdMedPersonalVK_Fin as 'TREAT3_CHAIRMAN_VK',
				RESD.ThirdMedPersonalVK_Inn as 'TREAT3_DOC2_ID',
				convert(varchar(10),RESD.EvnStick_disDate,20) as 'OTHER_STATE_DT',
				convert(varchar(10),RESD.EvnStick_returnDate,20) as 'RETURN_DATE_LPU',
				RESD.EvnStick_NumNext as 'NEXT_LN_CODE',
				1 as 'LN_VERSION'
			from
				v_RegistryESData RESD with (nolock)
				left join v_Sex Sex with(nolock) on Sex.Sex_id = RESD.Sex_id
			where
				RESD.RegistryES_id = t.RegistryES_id and RESD.Evn_id = t.Evn_id
			for xml path('LpuLn')
		";

		$query = "
			select
				t.Evn_id,
				cast((
					{$query_xml}
				) as xml) as LpuLn
			from
				v_RegistryESData t with (nolock)
			where
				t.RegistryES_id = :RegistryES_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данныж для выгрузки ЛВН на удаление
	 * @param $data
	 * @return bool
	 */
	function loadRegistryESDataForDisableXml($data) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);
		$filter = '';
		$filter .= " and isnull(RESD.RegistryESDataStatus_id, 1) = 1";

		$query = "
			declare @timestamp datetime = dbo.tzGetDate();

			select
				RESD.Evn_id,
				RESD.EvnStick_Num,
				RESD.EvnStick_Num as 'lnCode',
				RESD.Lpu_OGRN as 'ogrn',
				isnull(RESD.Person_Snils,'') as 'snils',
				ISNULL(SCD.StickCauseDel_Code, '010') as 'reasonCode',
				ISNULL(SCD.StickCauseDel_Name, 'Отмена оформления') as reason
			from
				v_RegistryESData RESD with (nolock)
				left join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = RESD.Evn_id
				left join v_StickCauseDel SCD with (nolock) on SCD.StickCauseDel_id = ESB.StickCauseDel_id
			where
				RESD.RegistryES_id = :RegistryES_id
				{$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);

		//var_dump($resp);
		$this->load->library('parser');
		$template = 'export_registry_disable_es';
		foreach ($resp as &$respone) {
			$xml_sign = '';

			$xml = $this->parser->parse('export_xml/' . $template, $respone, true);
			// удаляем пустые теги
			$xml = preg_replace('/<\w*><\/\w*>/u', '', $xml);
			// удаляем родительские пустые теги
			$xml = preg_replace('/<\w*>[\s]*<\/\w*>/u', '', $xml);
			// удаляем родительские пустые теги
			$xml = preg_replace('/<\w*>[\s]*<\/\w*>/u', '', $xml);

			// тэги которые могут быть пустыми, но должны присутствовать
			$xml = preg_replace('/<(\w*)>###<\/\w*>/u', '<$1 xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />', $xml);

			// добавляем нэймспейс fil: ко всем тегам
			$xml = preg_replace('/<([^\/]+?.*?)>/u', '<fil:$1>', $xml);
			$xml = preg_replace('/<\/(.*?)>/u', '</fil:$1>', $xml);

			// echo "<textarea cols='100' rows='50'>{$xml}</textarea>"; // для тестов

			$respone['LpuLn'] = $xml;
			$respone['LpuLnSign'] = $xml_sign;
		}

		return $resp;
	}

	/**
	 * Получение данныж для выгрузки
	 * @param $data
	 * @return bool
	 */
	function loadRegistryESDataForXml($data, $mode = 'export') {
		$params = array('RegistryES_id' => $data['RegistryES_id']);
		$filter = '';

		if ($mode == 'export') {
			$filter .= " and isnull(RESD.RegistryESDataStatus_id, 1) = 1";
		} else if ($mode == 'reconciliation' || $mode == 'get_unsigned_array') {
			$filter .= " and isnull(RESD.RegistryESDataStatus_id, 1) in (1,4)";
		}

		$query = "
			declare @timestamp datetime = dbo.tzGetDate();

			select
				RESD.Evn_id,
				RESD.FirstEvnStickWorkRelease_id,
				RESD.SecondEvnStickWorkRelease_id,
				RESD.ThirdEvnStickWorkRelease_id,
				RESD.EvnStick_Num,
				isnull(RESD.Person_Snils,'') as 'SNILS',
				RESD.Person_SurName as 'SURNAME',
				RESD.Person_FirName as 'NAME',
				RESD.Person_SecName as 'PATRONIMIC',
				RESD.RegistryESData_IsEmploymentServices as 'BOZ_FLAG',
				RESD.Org_StickNick as 'LPU_EMPLOYER',
				ISNULL(cast(RESD.RegistryESData_EmploymentType as varchar), '###') as 'LPU_EMPL_FLAG',
				RESD.EvnStick_Num as 'LN_CODE',
				RESD.EvnStick_NumPrev as 'PREV_LN_CODE',
				isnull(RESD.EvnStick_IsPrimary,0) as 'PRIMARY_FLAG',
				isnull(RESD.EvnStick_IsDuplicate,0) as 'DUPLICATE_FLAG',
				convert(varchar(10),RESD.EvnStick_setDate,20) as 'LN_DATE',
				RESD.Lpu_StickNick as 'LPU_NAME',
				ISNULL(RESD.Lpu_StickAddress,'-') as 'LPU_ADDRESS',
				RESD.Lpu_OGRN as 'LPU_OGRN',				
				convert(varchar(10),RESD.Person_BirthDay,20) as 'BIRTHDAY',
				case when Sex.Sex_Code=1 then 0 when Sex.Sex_Code=2 then 1 end as 'GENDER',
				RESD.StickCause_Code as 'REASON1',
				RESD.StickCauseDopType_Code as 'REASON2',
				RESD.StickCause_CodeAlter as 'REASON3',
				RESD.Diag_Code as 'DIAGNOS',
				RESD.EvnStick_NumPar as 'PARENT_CODE',
				ISNULL(convert(varchar(10),RESD.EvnStick_StickDT,20),'###') as 'DATE1',
				ISNULL(convert(varchar(10),RESD.EvnStick_sstEndDate,20),'###') as 'DATE2',
				RESD.EvnStick_sstNum as 'VOUCHER_NO',
				RESD.Org_sstOGRN as 'VOUCHER_OGRN',
				ISNULL(cast(RESD.FirstRelated_Age as varchar),'###') as 'SERV1_AGE',
				ISNULL(cast(RESD.FirstRelated_AgeMonth as varchar),'###') as 'SERV1_MM',
				RESD.FirstRelatedLinkType_Code as 'SERV1_RELATION_CODE',
				RESD.FirstRelated_FIO as 'SERV1_FIO',
				ISNULL(cast(RESD.SecondRelated_Age as varchar),'###') as 'SERV2_AGE',
				ISNULL(cast(RESD.SecondRelated_AgeMonth as varchar),'###') as 'SERV2_MM',
				RESD.SecondRelatedLinkType_Code as 'SERV2_RELATION_CODE',
				RESD.SecondRelated_FIO as 'SERV2_FIO',
				ISNULL(cast(RESD.EvnStick_IsRegPregnancy as varchar), '###') as 'PREGN12W_FLAG',
				ISNULL(convert(varchar(10),RESD.EvnStick_stacBegDate,20),'###') as 'HOSPITAL_DT1',
				ISNULL(convert(varchar(10),RESD.EvnStick_stacEndDate,20),'###') as 'HOSPITAL_DT2',
				RESD.StickIrregularity_Code as 'HOSPITAL_BREACH/HOSPITAL_BREACH_CODE',
				convert(varchar(10),RESD.EvnStick_irrDT,20) as 'HOSPITAL_BREACH/HOSPITAL_BREACH_DT',
				ISNULL(convert(varchar(10),RESD.EvnStick_mseDT,20),'###') as 'MSE_DT1',
				ISNULL(convert(varchar(10),RESD.EvnStick_mseRegDT,20),'###') as 'MSE_DT2',
				ISNULL(convert(varchar(10),RESD.EvnStick_mseExamDT,20),'###')  as 'MSE_DT3',
				case
					when RESD.InvalidGroupType_id = 2 then '1'
					when RESD.InvalidGroupType_id = 3 then '2'
					when RESD.InvalidGroupType_id = 4 then '3'
					else '###'
				end as 'MSE_INVALID_GROUP',
				RESD.StickLeaveType_Code as 'LN_RESULT/MSE_RESULT',
				ISNULL(convert(varchar(10),RESD.EvnStick_disDate,20), '###') as 'LN_RESULT/OTHER_STATE_DT',
				ISNULL(convert(varchar(10),RESD.EvnStick_returnDate,20), '###') as 'LN_RESULT/RETURN_DATE_LPU',
				RESD.EvnStick_NumNext as 'LN_RESULT/NEXT_LN_CODE',
				'0' as 'LN_STATE',
				ISNULL(LASTRESD.RegistryESData_Hash, '') as 'LN_HASH',
				-- первый период
				RESD.FirstPostVK_Code as 'TREAT1_CHAIRMAN_ROLE',
				RESD.FirstMedPersonalVK_Fin as 'TREAT1_CHAIRMAN',
				convert(varchar(10),RESD.FirstEvnStickWorkRelease_begDT,20) as 'TREAT1_DT1',
				convert(varchar(10),RESD.FirstEvnStickWorkRelease_endDT,20) as 'TREAT1_DT2',
				RESD.FirstPost_Code as 'TREAT1_DOCTOR_ROLE',
				RESD.FirstMedPersonal_Fin as 'TREAT1_DOCTOR',
				-- второй период
				RESD.SecondPostVK_Code as 'TREAT2_CHAIRMAN_ROLE',
				RESD.SecondMedPersonalVK_Fin as 'TREAT2_CHAIRMAN',
				convert(varchar(10),RESD.SecondEvnStickWorkRelease_begDT,20) as 'TREAT2_DT1',
				convert(varchar(10),RESD.SecondEvnStickWorkRelease_endDT,20) as 'TREAT2_DT2',
				RESD.SecondPost_Code as 'TREAT2_DOCTOR_ROLE',
				RESD.SecondMedPersonal_Fin as 'TREAT2_DOCTOR',
				-- третий период
				RESD.ThirdPostVK_Code as 'TREAT3_CHAIRMAN_ROLE',
				RESD.ThirdMedPersonalVK_Fin as 'TREAT3_CHAIRMAN',
				convert(varchar(10),RESD.ThirdEvnStickWorkRelease_begDT,20) as 'TREAT3_DT1',
				convert(varchar(10),RESD.ThirdEvnStickWorkRelease_endDT,20) as 'TREAT3_DT2',
				RESD.ThirdPost_Code as 'TREAT3_DOCTOR_ROLE',
				RESD.ThirdMedPersonal_Fin as 'TREAT3_DOCTOR',
				RESD.EvnStickLeave_Token,
				RESD.EvnStickLeave_Hash,
				RESD.EvnStickLeave_SignedData,
				RESD.StickIrregularity_Token,
				RESD.StickIrregularity_Hash,
				RESD.StickIrregularity_SignedData,
				RESD.FirstVK_Token,
				RESD.FirstVK_Hash,
				RESD.FirstVK_SignedData,
				RESD.SecondVK_Token,
				RESD.SecondVK_Hash,
				RESD.SecondVK_SignedData,
				RESD.ThirdVK_Token,
				RESD.ThirdVK_Hash,
				RESD.ThirdVK_SignedData,
				RESD.FirstMedPersonal_Token,
				RESD.FirstMedPersonal_Hash,
				RESD.FirstMedPersonal_SignedData,
				RESD.SecondMedPersonal_Token,
				RESD.SecondMedPersonal_Hash,
				RESD.SecondMedPersonal_SignedData,
				RESD.ThirdMedPersonal_Token,
				RESD.ThirdMedPersonal_Hash,
				RESD.ThirdMedPersonal_SignedData,
				ISNULL(ESWR1.EvnStickWorkRelease_IsPaid, 1) as FirstEvnStickWorkRelease_IsPaid,
				ISNULL(ESWR2.EvnStickWorkRelease_IsPaid, 1) as SecondEvnStickWorkRelease_IsPaid,
				ISNULL(ESWR3.EvnStickWorkRelease_IsPaid, 1) as ThirdEvnStickWorkRelease_IsPaid,
				ISNULL(ESB.EvnStickBase_IsFSS, 1) as EvnStickBase_IsFSS
			from
				v_RegistryESData RESD with (nolock)
				outer apply (
					select top 1
						RESD2.RegistryESData_Hash
					from
						v_RegistryESData RESD2 with (nolock)
					where
						RESD2.Evn_id = RESD.Evn_id
						and RESD2.RegistryESData_Hash is not null
					order by
						RESD2.RegistryESData_updDT desc
				) LASTRESD
				left join v_Sex Sex with(nolock) on Sex.Sex_id = RESD.Sex_id
				left join v_EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = RESD.Evn_id
				left join v_EvnStickWorkRelease ESWR1 on ESWR1.EvnStickWorkRelease_id = RESD.FirstEvnStickWorkRelease_id
				left join v_EvnStickWorkRelease ESWR2 on ESWR2.EvnStickWorkRelease_id = RESD.SecondEvnStickWorkRelease_id
				left join v_EvnStickWorkRelease ESWR3 on ESWR3.EvnStickWorkRelease_id = RESD.ThirdEvnStickWorkRelease_id
			where
				RESD.RegistryES_id = :RegistryES_id
				{$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);

		$unsignedArray = array();

		//var_dump($resp);
		$this->load->library('parser');
		$template = 'export_registry_es';
		foreach ($resp as &$respone) {
			if (empty($respone['LN_HASH']) && $respone['EvnStickBase_IsFSS'] == 2) {
				// если ЛВН из ФСС, то берём хэш из StickFSSData (с рабочей БД).
				$result_sfd = $this->dbMain->query("
					select top 1
						SFDG.StickFSSDataGet_Hash
					from
						v_EvnStickBase ESB with (nolock)
						inner join v_StickFSSDataGet SFDG with (nolock) on SFDG.StickFSSData_id = ESB.StickFSSData_id
					where
						ESB.EvnStickBase_id = :Evn_id
						and SFDG.StickFSSDataGet_Hash is not null
					order by
						SFDG.StickFSSDataGet_updDT desc
				", array(
					'Evn_id' => $respone['Evn_id']
				));

				if (is_object($result_sfd)) {
					$resp_sfd = $result_sfd->result('array');
					if (!empty($resp_sfd[0]['StickFSSDataGet_Hash'])) {
						$respone['LN_HASH'] = $resp_sfd[0]['StickFSSDataGet_Hash'];
					}
				}
			}

			$xml_sign = '';
			if (!empty($respone['EvnStickLeave_SignedData']) && !empty($respone['EvnStickLeave_Hash']) && !empty($respone['EvnStickLeave_Token'])) {
				if (!empty($respone['LN_RESULT/MSE_RESULT']) || $respone['LN_RESULT/OTHER_STATE_DT'] != '###' || $respone['LN_RESULT/RETURN_DATE_LPU'] != '###') {
					$this->load->helper('openssl');
					$certAlgo = getCertificateAlgo($respone['EvnStickLeave_Token']);

					$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
						'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_2_doc',
						'block' => 'ELN_' . $respone['LN_CODE'] . '_2_doc',
						'BinarySecurityToken' => $respone['EvnStickLeave_Token'],
						'DigestValue' => $respone['EvnStickLeave_Hash'],
						'SignatureValue' => $respone['EvnStickLeave_SignedData'],
						'signatureMethod' => $certAlgo['signatureMethod'],
						'digestMethod' => $certAlgo['digestMethod']
					), true);
					$respone['sl_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_2_doc"';
				} else {
					$respone['sl_sign'] = '';
				}
			} else {
				$respone['sl_sign'] = '';
				if (!empty($respone['LN_RESULT/MSE_RESULT']) || $respone['LN_RESULT/OTHER_STATE_DT'] != '###' || $respone['LN_RESULT/RETURN_DATE_LPU'] != '###') {
					$unsignedArray['leave' . $respone['Evn_id']] = array(
						'Evn_id' => $respone['Evn_id'],
						'EvnStick_Num' => $respone['EvnStick_Num'],
						'SignObject' => 'leave'
					);
					$respone['emptySign'] = true;
					$respone['emptySignDoc'] = true;
				}
			}
			if (!empty($respone['StickIrregularity_SignedData']) && !empty($respone['StickIrregularity_Hash']) && !empty($respone['StickIrregularity_Token'])) {
				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($respone['StickIrregularity_Token']);

				$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_1_doc',
					'block' => 'ELN_' . $respone['LN_CODE'] . '_1_doc',
					'BinarySecurityToken' => $respone['StickIrregularity_Token'],
					'DigestValue' => $respone['StickIrregularity_Hash'],
					'SignatureValue' => $respone['StickIrregularity_SignedData'],
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);
				$respone['si_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_1_doc"';
			} else {
				$respone['si_sign'] = '';
				if ($respone['EvnStickBase_IsFSS'] == 1) { // подпись режима для ЛВН из ФСС не нужна.
					if (!empty($respone['HOSPITAL_BREACH/HOSPITAL_BREACH_CODE'])) {
						$unsignedArray['irr' . $respone['Evn_id']] = array(
							'Evn_id' => $respone['Evn_id'],
							'EvnStick_Num' => $respone['EvnStick_Num'],
							'SignObject' => 'irr'
						);
						$respone['emptySign'] = true;
						$respone['emptySignDoc'] = true;
					}
				}
			}
			if ($respone['FirstEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['FirstVK_SignedData']) && !empty($respone['FirstVK_Hash']) && !empty($respone['FirstVK_Token'])) {
				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($respone['FirstVK_Token']);

				$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_3_vk',
					'block' => 'ELN_' . $respone['LN_CODE'] . '_3_vk',
					'BinarySecurityToken' => $respone['FirstVK_Token'],
					'DigestValue' => $respone['FirstVK_Hash'],
					'SignatureValue' => $respone['FirstVK_SignedData'],
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);
				$respone['1_vk_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_3_vk"';
			} else {
				$respone['1_vk_sign'] = '';
				if ($respone['FirstEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['TREAT1_DT1']) && !empty($respone['TREAT1_CHAIRMAN']) && !empty($respone['FirstEvnStickWorkRelease_id'])) { // подпись освобождения нужна, если освобождение добавлено в МО, а не пришло из ФСС
					$unsignedArray['vk'.$respone['FirstEvnStickWorkRelease_id']] = array(
						'Evn_id' => $respone['FirstEvnStickWorkRelease_id'],
						'EvnStick_Num' => $respone['EvnStick_Num'],
						'SignObject' => 'VK'
					);
					$respone['emptySign'] = true;
					$respone['emptySignVK'] = true;
				}
			}
			if ($respone['SecondEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['SecondVK_SignedData']) && !empty($respone['SecondVK_Hash']) && !empty($respone['SecondVK_Token'])) {
				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($respone['SecondVK_Token']);

				$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_4_vk',
					'block' => 'ELN_' . $respone['LN_CODE'] . '_4_vk',
					'BinarySecurityToken' => $respone['SecondVK_Token'],
					'DigestValue' => $respone['SecondVK_Hash'],
					'SignatureValue' => $respone['SecondVK_SignedData'],
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);
				$respone['2_vk_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_4_vk"';
			} else {
				$respone['2_vk_sign'] = '';
				if ($respone['SecondEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['TREAT2_DT1']) && !empty($respone['TREAT2_CHAIRMAN']) && !empty($respone['SecondEvnStickWorkRelease_id'])) { // подпись освобождения нужна, если освобождение добавлено в МО, а не пришло из ФСС
					$unsignedArray['vk'.$respone['SecondEvnStickWorkRelease_id']] = array(
						'Evn_id' => $respone['SecondEvnStickWorkRelease_id'],
						'EvnStick_Num' => $respone['EvnStick_Num'],
						'SignObject' => 'VK'
					);
					$respone['emptySign'] = true;
					$respone['emptySignVK'] = true;
				}
			}
			if ($respone['ThirdEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['ThirdVK_SignedData']) && !empty($respone['ThirdVK_Hash']) && !empty($respone['ThirdVK_Token'])) {
				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($respone['ThirdVK_Token']);

				$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_5_vk',
					'block' => 'ELN_' . $respone['LN_CODE'] . '_5_vk',
					'BinarySecurityToken' => $respone['ThirdVK_Token'],
					'DigestValue' => $respone['ThirdVK_Hash'],
					'SignatureValue' => $respone['ThirdVK_SignedData'],
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);
				$respone['3_vk_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_5_vk"';
			} else {
				$respone['3_vk_sign'] = '';
				if ($respone['ThirdEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['TREAT3_DT1']) && !empty($respone['TREAT3_CHAIRMAN']) && !empty($respone['ThirdEvnStickWorkRelease_id'])) { // подпись освобождения нужна, если освобождение добавлено в МО, а не пришло из ФСС
					$unsignedArray['vk'.$respone['ThirdEvnStickWorkRelease_id']] = array(
						'Evn_id' => $respone['ThirdEvnStickWorkRelease_id'],
						'EvnStick_Num' => $respone['EvnStick_Num'],
						'SignObject' => 'VK'
					);
					$respone['emptySign'] = true;
					$respone['emptySignVK'] = true;
				}
			}
			if ($respone['FirstEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['FirstMedPersonal_SignedData']) && !empty($respone['FirstMedPersonal_Hash']) && !empty($respone['FirstMedPersonal_Token'])) {
				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($respone['FirstMedPersonal_Token']);

				$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_3_doc',
					'block' => 'ELN_' . $respone['LN_CODE'] . '_3_doc',
					'BinarySecurityToken' => $respone['FirstMedPersonal_Token'],
					'DigestValue' => $respone['FirstMedPersonal_Hash'],
					'SignatureValue' => $respone['FirstMedPersonal_SignedData'],
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);
				$respone['1_doc_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_3_doc"';
			} else {
				$respone['1_doc_sign'] = '';
				if ($respone['FirstEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['TREAT1_DT1']) && !empty($respone['FirstEvnStickWorkRelease_id'])) { // подпись освобождения нужна, если освобождение добавлено в МО, а не пришло из ФСС
					$unsignedArray['mp'.$respone['FirstEvnStickWorkRelease_id']] = array(
						'Evn_id' => $respone['FirstEvnStickWorkRelease_id'],
						'EvnStick_Num' => $respone['EvnStick_Num'],
						'SignObject' => 'MP'
					);
					$respone['emptySign'] = true;
					$respone['emptySignDoc'] = true;
				}
			}
			if ($respone['SecondEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['SecondMedPersonal_SignedData']) && !empty($respone['SecondMedPersonal_Hash']) && !empty($respone['SecondMedPersonal_Token'])) {
				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($respone['SecondMedPersonal_Token']);

				$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_4_doc',
					'block' => 'ELN_' . $respone['LN_CODE'] . '_4_doc',
					'BinarySecurityToken' => $respone['SecondMedPersonal_Token'],
					'DigestValue' => $respone['SecondMedPersonal_Hash'],
					'SignatureValue' => $respone['SecondMedPersonal_SignedData'],
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);
				$respone['2_doc_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_4_doc"';
			} else {
				$respone['2_doc_sign'] = '';
				if ($respone['SecondEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['TREAT2_DT1']) && !empty($respone['SecondEvnStickWorkRelease_id'])) { // подпись освобождения нужна, если освобождение добавлено в МО, а не пришло из ФСС
					$unsignedArray['mp'.$respone['SecondEvnStickWorkRelease_id']] = array(
						'Evn_id' => $respone['SecondEvnStickWorkRelease_id'],
						'EvnStick_Num' => $respone['EvnStick_Num'],
						'SignObject' => 'MP'
					);
					$respone['emptySign'] = true;
					$respone['emptySignDoc'] = true;
				}
			}
			if ($respone['ThirdEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['ThirdMedPersonal_SignedData']) && !empty($respone['ThirdMedPersonal_Hash']) && !empty($respone['ThirdMedPersonal_Token'])) {
				$this->load->helper('openssl');
				$certAlgo = getCertificateAlgo($respone['ThirdMedPersonal_Token']);

				$xml_sign .= $this->parser->parse('export_xml/xml_signature', array(
					'id' => 'http://eln.fss.ru/actor/doc/' . $respone['LN_CODE'] . '_5_doc',
					'block' => 'ELN_' . $respone['LN_CODE'] . '_5_doc',
					'BinarySecurityToken' => $respone['ThirdMedPersonal_Token'],
					'DigestValue' => $respone['ThirdMedPersonal_Hash'],
					'SignatureValue' => $respone['ThirdMedPersonal_SignedData'],
					'signatureMethod' => $certAlgo['signatureMethod'],
					'digestMethod' => $certAlgo['digestMethod']
				), true);
				$respone['3_doc_sign'] = ' wsu:Id="ELN_' . $respone['LN_CODE'] . '_5_doc"';
			} else {
				$respone['3_doc_sign'] = '';
				if ($respone['ThirdEvnStickWorkRelease_IsPaid'] == 1 && !empty($respone['TREAT3_DT1']) && !empty($respone['ThirdEvnStickWorkRelease_id'])) { // подпись освобождения нужна, если освобождение добавлено в МО, а не пришло из ФСС
					$unsignedArray['mp'.$respone['ThirdEvnStickWorkRelease_id']] = array(
						'Evn_id' => $respone['ThirdEvnStickWorkRelease_id'],
						'EvnStick_Num' => $respone['EvnStick_Num'],
						'SignObject' => 'MP'
					);
					$respone['emptySign'] = true;
					$respone['emptySignDoc'] = true;
				}
			}

			if ($mode == 'get_unsigned_array') {
				continue;
			}

			$xml = $this->parser->parse('export_xml/' . $template, $respone, true);
			// удаляем пустые теги
			$xml = preg_replace('/<\w*><\/\w*>/u', '', $xml);
			// удаляем родительские пустые теги
			$xml = preg_replace('/<\w*>[\s]*<\/\w*>/u', '', $xml);
			// удаляем родительские пустые теги
			$xml = preg_replace('/<\w*>[\s]*<\/\w*>/u', '', $xml);

			// тэги которые могут быть пустыми, но должны присутствовать
			$xml = preg_replace('/<(\w*)>###<\/\w*>/u', '<$1 xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />', $xml);

			// добавляем нэймспейс fil: ко всем тегам
			$xml = preg_replace('/<([^\/!]+?.*?)>/u', '<fil:$1>', $xml);
			$xml = preg_replace('/<\/(.*?)>/u', '</fil:$1>', $xml);

			// echo "<textarea cols='100' rows='50'>{$xml}</textarea>"; // для тестов

			$respone['LpuLn'] = $xml;
			$respone['LpuLnSign'] = $xml_sign;
		}

		if ($mode == 'get_unsigned_array') {
			return $unsignedArray;
		}

		return $resp;
	}

	/**
	 * Идентификация записи реестра по данным из ответа
	 */
	function getRegistryESDataForImport($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'EvnStick_Num' => $data['EvnStick_Num']
		);

		$query = "
			select top 1
				RESD.RegistryES_id,
				RESD.Evn_id,
				RESDS.RegistryESDataStatus_Code
			from v_RegistryESData RESD with(nolock)
				left join v_RegistryESDataStatus RESDS with (nolock) on RESDS.RegistryESDataStatus_id = RESD.RegistryESDataStatus_id
			where
				RESD.RegistryES_id = :RegistryES_id
				and RESD.EvnStick_Num = :EvnStick_Num
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Идентификация записи реестра по данным из ответа
	 */
	function getRegistryESDataForCheckInFSS($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'EvnStick_Num' => $data['EvnStick_Num']
		);

		$query = "
			select top 1
				RESD.RegistryES_id,
				RESD.Evn_id,
				RESDS.RegistryESDataStatus_Code,
				RESD.Person_SurName,
				RESD.Person_FirName,
				RESD.Person_SecName,
				convert(varchar(10), RESD.Person_BirthDay, 120) as Person_BirthDay,
				RESD.Person_Snils,
				RESD.EvnStick_Num,
				convert(varchar(10), RESD.EvnStick_setDate, 120) as EvnStick_setDate,
				RESD.Lpu_StickNick,
				RESD.StickCause_Code,
				convert(varchar(10), RESD.FirstEvnStickWorkRelease_begDT, 120) as FirstEvnStickWorkRelease_begDT,
				convert(varchar(10), RESD.FirstEvnStickWorkRelease_endDT, 120) as FirstEvnStickWorkRelease_endDT,
				convert(varchar(10), RESD.SecondEvnStickWorkRelease_begDT, 120) as SecondEvnStickWorkRelease_begDT,
				convert(varchar(10), RESD.SecondEvnStickWorkRelease_endDT, 120) as SecondEvnStickWorkRelease_endDT,
				convert(varchar(10), RESD.ThirdEvnStickWorkRelease_begDT, 120) as ThirdEvnStickWorkRelease_begDT,
				convert(varchar(10), RESD.ThirdEvnStickWorkRelease_endDT, 120) as ThirdEvnStickWorkRelease_endDT,
				RESD.StickLeaveType_Code,
				RESD.EvnStick_disDate,
				RESD.EvnStick_returnDate,
				RESD.FirstEvnStickWorkRelease_id,
				RESD.SecondEvnStickWorkRelease_id,
				RESD.ThirdEvnStickWorkRelease_id,
				RESS.RegistryESStatus_Code
			from v_RegistryESData RESD with(nolock)
				left join v_RegistryESDataStatus RESDS with (nolock) on RESDS.RegistryESDataStatus_id = RESD.RegistryESDataStatus_id
				left join v_RegistryES RES with (nolock) on RES.RegistryES_id = RESD.RegistryES_id
				left join v_RegistryESStatus RESS with(nolock) on RESS.RegistryESStatus_id = RES.RegistryESStatus_id
			where
				RESD.RegistryES_id = :RegistryES_id
				and RESD.EvnStick_Num = :EvnStick_Num
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Добавление ошибки в реестр ЛВН
	 */
	function setRegistryESError($data) {
		$params = array(
			'RegistryES_id' => $data['RegistryES_id'],
			'Evn_id' => empty($data['Evn_id']) ? null : $data['Evn_id'],
			'Error_Code' => empty($data['Error_Code']) ? null : $data['Error_Code'],
			'Error_Message' => empty($data['Error_Message']) ? null : $data['Error_Message'],
			'RegistryESErrorType_id' => empty($data['RegistryESErrorType_id']) ? null : $data['RegistryESErrorType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$filter = "";
		if (!empty($data['RegistryESErrorStageType_id'])) {
			$filter .= " and RegistryESErrorStageType_id = :RegistryESErrorStageType_id";
			$params['RegistryESErrorStageType_id'] = $data['RegistryESErrorStageType_id'];
		}
		$query = "
			select top 1
				RegistryESErrorType_id,
				RegistryESErrorType_Name
			from
				v_RegistryESErrorType with (nolock)
			where
				RegistryESErrorType_Code = :Error_Code
				{$filter}
		";
		$error_type = $this->queryResult($query, $params);
		if (!empty($error_type[0]['RegistryESErrorType_id'])) {
			if (empty($params['RegistryESErrorType_id'])) {
				$params['RegistryESErrorType_id'] = $error_type[0]['RegistryESErrorType_id'];
			}
			if (empty($params['Error_Message'])) {
				$params['Error_Message'] = $error_type[0]['RegistryESErrorType_Name'];
			}
		} else {
			if (!empty($data['type']) && $data['type'] == 'fss') {
				$params['RegistryESErrorType_id'] = 5; // Найдены ошибки при выполнении форматно-логических проверок
			} else {
				return $this->createError('', "Не найдена ошибка в справочнике по коду {$params['Error_Code']}");
			}
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000)
			set nocount on
			begin try

				insert into	RegistryESError (
					RegistryES_id,
					Evn_id,
					RegistryESErrorType_id,
					RegistryESError_Descr,
					RegistryESError_Code,
					pmUser_insID,
					pmUser_updID,
					RegistryESError_insDT,
					RegistryESError_updDT
				)
				select top 1
					RESD.RegistryES_id,
					RESD.Evn_id,
					:RegistryESErrorType_id,
					:Error_Message,
					:Error_Code,
					:pmUser_id,
					:pmUser_id,
					dbo.tzGetDate(),
					dbo.tzGetDate()
				from RegistryESData RESD with(nolock)
				where RESD.RegistryES_id = :RegistryES_id  and RESD.Evn_id = :Evn_id

			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		//echo getDebugSQL($query,$params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при добавлении записи об ошибке реестра');
		}
		return $result->result('array');
	}

	/**
	 * Снятие признака включения ЛВН в реестр для реестра о ошибкой отправки
	 */
	function updateEvnStickNotInReg($data) {
		$resp_r = $this->queryResult("select RegistryESType_id from v_RegistryES (nolock) where RegistryES_id = :RegistryES_id", array(
			'RegistryES_id' => $data['RegistryES_id']
		));

		if (!empty($resp_r[0]['RegistryESType_id'])) {
			$RegistryESType_id = $resp_r[0]['RegistryESType_id'];
		} else {
			throw new Exception("Не удалось определить тип реестра");
		}

		// проверяем что ЛВН не содержится в другом реестре
		$resp = $this->queryResult("
			select top 1
				RES.RegistryES_id
			from
				v_RegistryESData RESD (nolock)
				inner join v_RegistryES RES (nolock) on RES.RegistryES_id = RESD.RegistryES_id
			where
				RESD.Evn_id = :Evn_id
				and RESD.RegistryESDataStatus_id = 1 -- в реестре
				and RES.RegistryESType_id = :RegistryESType_id
				and RES.RegistryESStatus_id in (1,3,11,9,10,12,13,14)
				and RESD.RegistryES_id <> :RegistryES_id
		", array(
			'Evn_id' => $data['Evn_id'],
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESType_id' => $RegistryESType_id
		));

		if (empty($resp[0]['RegistryES_id'])) {
			// если не содержится, то снимаем признаки
			if ($RegistryESType_id == 3) {
				$this->db->query("
					update EvnStickBase with (rowlock) set EvnStickBase_IsInRegDel = 1 where EvnStickBase_id = :EvnStickBase_id
				", array(
					'EvnStickBase_id' => $data['Evn_id']
				));
			} else {
				$this->db->query("
					update EvnStickBase with (rowlock) set EvnStickBase_IsInReg = 1 where EvnStickBase_id = :EvnStickBase_id and ISNULL(EvnStickBase_IsPaid, 1) = 1
					update EvnStickWorkRelease with (rowlock) set EvnStickWorkRelease_IsInReg = 1 where EvnStickBase_id = :EvnStickBase_id and ISNULL(EvnStickWorkRelease_IsPaid, 1) = 1
				", array(
					'EvnStickBase_id' => $data['Evn_id']
				));
			}
		} else {
			throw new Exception("Выбранный ЛВН находится в реестре {$resp[0]['RegistryES_id']}, для выполнения процедуры завершите работу с реестром (возможны действия в зависимости от статуса реестра: удаление реестра/удаление ЛВН из реестра/дождаться ответа от ФСС) и повторите проверку в ФСС");
		}
	}

	/**
	 * Обновление признака включения ЛВН в реестр
	 */
	function updateEvnStickIsInReg($data) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000)
			set nocount on
			begin try

				update EvnStickBase with (rowlock) set EvnStickBase_IsInReg = 1
				from v_RegistryESData RESD with(nolock)
				where
					RESD.RegistryES_id = :RegistryES_id
					and RESD.Evn_id = EvnStickBase.EvnStickBase_id
					and (
						RESD.RegistryESDataStatus_id = 3 -- Не принят ФСС
						OR (
							RESD.RegistryESDataStatus_id = 4 -- Ошибки ФЛК
							and exists(
								select top 1
									resr.Evn_id
								from
									v_RegistryESError resr (nolock)
									left join v_RegistryESErrorType ret (nolock) on ret.RegistryESErrorType_id = resr.RegistryESErrorType_id
								where
									resr.RegistryES_id = RESD.RegistryES_id
									and resr.Evn_id = RESD.Evn_id
									and ISNULL(ret.RegistryESErrorType_Code, 0) <> '005' -- только при наличии ошибок помимо ошибок подписи
							)
						)
					)

			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при обновлении признака включения ЛВН в реестр');
		}
		return $result->result('array');
	}


	/**
	 * Удаление ошибок по случаям из реестра ЛВН
	 */
	function deleteRegistryESError($data, $type = null) {
		$params = array('RegistryES_id' => $data['RegistryES_id']);
		$filter = '';

		if (!empty($type)) {
			if ($type == 'flk') {
				$filter .= " and RegistryESErrorType_id is not null";
				$filter .= " and 1 = (
					select RegistryESErrorStageType_Code
					from v_RegistryESErrorType RESET with(nolock)
					inner join v_RegistryESErrorStageType RESEST with(nolock) on RESEST.RegistryESErrorStageType_id = RESET.RegistryESErrorStageType_id
					where RESET.RegistryESErrorType_id = RegistryESError.RegistryESErrorType_id
				)";
			} else if ($type == 'fss') {
				$filter .= " and (RegistryESErrorType_id is null or 2 = (
					select RegistryESErrorStageType_Code
					from v_RegistryESErrorType RESET with(nolock)
					inner join v_RegistryESErrorStageType RESEST with(nolock) on RESEST.RegistryESErrorStageType_id = RESET.RegistryESErrorStageType_id
					where RESET.RegistryESErrorType_id = RegistryESError.RegistryESErrorType_id
				))";
			}

		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000)
			set nocount on
			begin try

				delete from RegistryESError
				where RegistryES_id = :RegistryES_id
				{$filter}

			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message();
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $this->createError('', 'Ошибка при удалении записи об ошибке в реестра');
		}
		return $result->result('array');
	}

	/**
	 * Сохранение выбранных сертификатов для подписи
	 */
	function saveRegistryESIndividCert($data) {
		if (is_array($data['RegistryESIndividCertGridData'])) {
			foreach($data['RegistryESIndividCertGridData'] as $one) {
				$resp_save = $this->queryResult("
					declare
						@RegistryESIndividCert_id bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);
						
					exec p_RegistryESIndividCert_ins
						@RegistryESIndividCert_id = @RegistryESIndividCert_id output,
						@RegistryES_id = :RegistryES_id, 
						@ExpertMedStaffType_id = :ExpertMedStaffType_id,
						@RegistryESIndividCert_CertThumbprint = :RegistryESIndividCert_CertThumbprint,
						@RegistryESIndividCert_CertSubjectName = :RegistryESIndividCert_CertSubjectName,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;				
				", array(
					'RegistryES_id' => $data['RegistryES_id'],
					'ExpertMedStaffType_id' => !empty($one['ExpertMedStaffType_id'])?$one['ExpertMedStaffType_id']:null,
					'RegistryESIndividCert_CertThumbprint' => !empty($one['RegistryESIndividCert_CertThumbprint'])?$one['RegistryESIndividCert_CertThumbprint']:null,
					'RegistryESIndividCert_CertSubjectName' => !empty($one['RegistryESIndividCert_CertSubjectName'])?$one['RegistryESIndividCert_CertSubjectName']:null,
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp_save['Error_Msg'])) {
					return $resp_save;
				}
			}
		}
		return array('Error_Msg' => '');
	}

	/**
	 * Получение данных для подписи
	 */
	function getUnsignedData($data) {
		$unsignedArray = $this->loadRegistryESDataForXml($data, 'get_unsigned_array');
		return $unsignedArray;
	}

	/**
	 * Распарсивание XML-ек принятых реестров
	 */
	public function parseXmls($data) {
		$dataArray = array();

		$resp = $this->queryResult("
			select
				RESD.EvnStick_Num,
				RESD.Evn_id,
				RESD.RegistryES_id,
				RES.RegistryES_xmlExportPath
			from
				v_RegistryES RES with (nolock)
				inner join v_RegistryESData RESD with (nolock) on RESD.RegistryES_id = RES.RegistryES_id
			where
				RESD.RegistryESDataStatus_id = 2 -- принят ФСС
				AND RES.RegistryES_xmlExportPath IS NOT null
			order by
				RegistryES_id
		");

		foreach($resp as $respone) {
			if (!isset($dataArray[$respone['RegistryES_id']])) {
				$dataArray[$respone['RegistryES_id']] = array();
				$filePath = $respone['RegistryES_xmlExportPath'];
				if (file_exists($filePath)) {
					$xml = file_get_contents($filePath);
					if (!empty($xml)) {
						// регулярками проще
						preg_match_all('/<fil:ROW.*?>(.*?)<\/fil:ROW>/uis', $xml, $matches);
						if (!empty($matches[1])) {
							foreach ($matches[1] as $match) {
								// достаём диагноз и номер ЛВН
								$diagCode = '';
								preg_match('/:DIAGNOS>(.*?)</uis', $match, $diag_match);
								if (!empty($diag_match[1])) {
									$diagCode = $diag_match[1];
								}
								$lnCode = '';
								preg_match('/:LN_CODE>(.*?)</uis', $match, $code_match);
								if (!empty($code_match[1])) {
									$lnCode = $code_match[1];
								}

								$dataArray[$respone['RegistryES_id']][$lnCode] = $diagCode;
							}
						}
					}
				}
			}

			$lnCode = $respone['EvnStick_Num'];
			if (isset($dataArray[$respone['RegistryES_id']][$lnCode])) {
				$this->db->query("insert into [tmp].[N_RegistryESData_119194] (RegistryES_id, Evn_id, Diag_Code) VALUES (:RegistryES_id, :Evn_id, :Diag_Code)", array(
					'RegistryES_id' => $respone['RegistryES_id'],
					'Evn_id' => $respone['Evn_id'],
					'Diag_Code' => $dataArray[$respone['RegistryES_id']][$lnCode]
				));
			}
		}

		return array('Error_Msg' => '');
	}
}