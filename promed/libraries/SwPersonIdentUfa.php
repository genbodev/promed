<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2010 Swan Ltd.
 * @author		Bykov Stas aka Savage (savage@swan.perm.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		22.12.2010
 */

/**
 * Класс для работы с идентификацией людей (Уфа)
 *
 * @package		Library
 * @author		Bykov Stas aka Savage (savage@swan.perm.ru)
 */

class SwPersonIdentUfa {
	private $serviceURI = null;
	private $servicePort = null;
	private $socketTimeout = 120;

	private $responseFields = array(
		'ID_REG',
		'FAM',
		'NAM',
		'FNAM',
		'BORN_DATE',
		'SEX',
		'POL_NUM_16',
		'SMO',
		'DOC_SER',
		'DOC_NUM',
		'DOC_TYPE',
		'CLADR',
		'INDEX_P',
		'HOUSE',
		'CORP',
		'FLAT',
		'INN',
		'SNILS',
		'GIV_DATE',
		'ELIMIN_DATE',
		'IRRELEVANT',
		'BESTBEFORE',
		'CATEG',
		'BDZGUID',
		'POLISGUID'
	);


	public function __construct($uri = null, $port = null, $timeout = 120) {
		$this->serviceURI = $uri;
		$this->servicePort = $port;
		$this->socketTimeout = $timeout;
	}

	/**
	 * Разбор ответа пришедшего из Уфы по идентификации людей
	 * $xml string Ответ пришедший от сервера идентификации Уфы в виде одной строки
	 * return array Массив с информацией о людях, информация по каждому человеку в виде ассоциативного массива
	 */
	private function parsePersonIdentResponse($xml) {
		// Выходной массив результата
		$res = array();

		// Если ответ не содержит секций персональных данных....
		if ( strpos(strtolower($xml), '<data diffgr:id="data1" msdata:roworder="0">') === false )  {
			// ... возвращаем пустой массив
			return $res;
		}
		
		$person = array(); 
		
		// Выделяем перс. данные
		foreach ( $this->responseFields as $field ) {
			// Если поле в ответе отсутствует...
			if ( strpos(strtoupper($xml), '<' . $field . '>') === false ) {
				// ... идем на следующую итерацию цикла
				continue;
			}

			// Определяем позиции начала и конца поля
			$end = strpos(strtoupper($xml), '</' . $field . '>') + strlen('</' . $field . '>');
			$start = strpos(strtoupper($xml), '<' . $field . '>');

			// Вырезаем нужное поле
			$person[$field] = substr($xml, $start, $end - $start);
			// Убираем теги
			$person[$field] = strip_tags($person[$field]);
			
			if (in_array($field, array('BORN_DATE','GIV_DATE','ELIMIN_DATE'))) {
				if(!empty($person[$field])) {
					$person[$field] = date('d.m.Y', strtotime(substr($person[$field],0,10)));
				}
			}
		}

		$res[] = $person;

		return $res;
	}


	/**
	 *	Получение закрывающего тега для запроса на идентификацию
	 */
	private function getIdentRequestFooter() {
		return '</NewDataSet>';
	}


	/**
	 *	Получение заголовка запроса на идентификацию
	 */
	private function getIdentRequestHeader() {
		return '<NewDataSet xmlns="registry">
			<xs:schema id="NewDataSet" targetNamespace="registry" xmlns:mstns="registry" xmlns="registry" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata" attributeFormDefault="qualified" elementFormDefault="qualified">
			<xs:element name="NewDataSet" msdata:IsDataSet="true" msdata:MainDataTable="registry_x003A_person" msdata:UseCurrentLocale="true">
			<xs:complexType>
			<xs:choice minOccurs="0" maxOccurs="unbounded">
			<xs:element name="person">
			<xs:complexType>
			<xs:sequence>
			<xs:element name="ID_REG" type="xs:decimal" minOccurs="0" />
			<xs:element name="FAM" type="xs:string" minOccurs="0" />
			<xs:element name="NAM" type="xs:string" minOccurs="0" />
			<xs:element name="FNAM" type="xs:string" minOccurs="0" />
			<xs:element name="D_BORN" type="xs:dateTime" minOccurs="0" />
			<xs:element name="SEX" type="xs:string" minOccurs="0" />
			<xs:element name="SNILS" type="xs:string" minOccurs="0" />
			<xs:element name="DOC_TYPE" type="xs:string" minOccurs="0" />
			<xs:element name="DOC_SER" type="xs:string" minOccurs="0" />
			<xs:element name="DOC_NUM" type="xs:string" minOccurs="0" />
			<xs:element name="INN" type="xs:string" minOccurs="0" />
			<xs:element name="KLADR" type="xs:string" minOccurs="0" />
			<xs:element name="HOUSE" type="xs:string" minOccurs="0" />
			<xs:element name="ROOM" type="xs:string" minOccurs="0" />
			<xs:element name="SMO" type="xs:string" minOccurs="0" />
			<xs:element name="POL_NUM" type="xs:string" minOccurs="0" />
			<xs:element name="STATUS" type="xs:string" minOccurs="0" />
			<xs:element name="DATE_POS" type="xs:dateTime" minOccurs="0" />
			</xs:sequence>
			</xs:complexType>
			</xs:element>
			</xs:choice>
			</xs:complexType>
			</xs:element>
			</xs:schema>
		';
	}


	/**
	 *	Получение одной записи с данными человека для запроса на идентификацию
	 *	Входящие данные: массив с данными человека
	 */
	private function getIdentRequestRow($data) {
		$result = '';

		// Входящие данные - массив из 16, 17 или 18 элементов
		if ( !is_array($data) || !in_array(count($data), array(16, 17, 18)) ) {
			return $result;
		}

		$result .= '<person>';

		foreach ( $data as $key => $value ) {
			if ( !empty($value) ) {
				if ( in_array($key, array('DATE_POS', 'D_BORN')) ) {
					$value = str_replace(' ', 'T', $value) . '+05:00';
				}

				$result .= "<" . $key . ">" . toUTF($value) . "</" . $key . ">
				";
			}
		}

		$result .= '</person>';

		return $result;
	}


	/**
	 *	Выполнение запроса на идентификацию
	 *	Входящие данные: массив с данными человека
	 */
	public function doPersonIdentRequest($requestData = array()) {
		$CI =& get_instance();
		
		$result = array(
			'errorMsg' => '',
			'identData' => array(),
			'success' => true
		);

		if ( !is_array($requestData) || count($requestData) == 0 ) {
			$result['errorMsg'] = 'Отсутствуют входные данные';
			$result['success'] = false;
			return $result;
		}

		$requestText = $this->getIdentRequestHeader();

		foreach ( $requestData as $array ) {
			if ( !empty($array['SNILS']) && strlen($array['SNILS']) == 11 ) {
				$array['SNILS'] = substr($array['SNILS'], 0, 3) . '-' . substr($array['SNILS'], 3, 3) . '-' . substr($array['SNILS'], 6, 3) . '-' . substr($array['SNILS'], -2);
			}

			$requestText .= $this->getIdentRequestRow($array);
		}

		$requestText .= $this->getIdentRequestFooter();

		$post_string = '<?xml version="1.0" encoding="utf-8"?>
			<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
			  <soap12:Body>
				<GetManDataMIAS xmlns="http://rfoms-rb.ru/">
				  <data><![CDATA['.$requestText.']]></data>
				</GetManDataMIAS>
			  </soap12:Body>
			</soap12:Envelope>
		';
				
				
		// echo "<xmp>{$post_string}</xmp>";
				
		$soap_do = curl_init(); 
				
				
		$headers = array(             
			"Content-Type: application/soap+xml; charset=utf-8", 
			"Cache-Control: no-cache", 
			"Pragma: no-cache", 
			"Content-length: ".strlen($post_string)
		); 

		curl_setopt($soap_do, CURLOPT_URL,            $this->serviceURI );   
		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, $this->socketTimeout ); 
		curl_setopt($soap_do, CURLOPT_TIMEOUT,        $this->socketTimeout ); 
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($soap_do, CURLOPT_POST,           true ); 
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $post_string); 
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($soap_do);
		/* для тестов
			$response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><GetManDataMIASResponse xmlns="http://rfoms-rb.ru/"><GetManDataMIASResult><xs:schema id="LPU" xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata"><xs:element name="LPU" msdata:IsDataSet="true" msdata:MainDataTable="DATA" msdata:UseCurrentLocale="true"><xs:complexType><xs:choice minOccurs="0" maxOccurs="unbounded"><xs:element name="DATA"><xs:complexType><xs:sequence><xs:element name="ID_ASSURED" type="xs:string" minOccurs="0" /><xs:element name="FAM" type="xs:string" minOccurs="0" /><xs:element name="NAM" type="xs:string" minOccurs="0" /><xs:element name="FNAM" type="xs:string" minOccurs="0" /><xs:element name="BORN_DATE" type="xs:dateTime" minOccurs="0" /><xs:element name="SEX" type="xs:string" minOccurs="0" /><xs:element name="POL_NUM_16" type="xs:string" minOccurs="0" /><xs:element name="SMO" type="xs:decimal" minOccurs="0" /><xs:element name="DOC_SER" type="xs:string" minOccurs="0" /><xs:element name="DOC_NUM" type="xs:string" minOccurs="0" /><xs:element name="DOC_TYPE" type="xs:decimal" minOccurs="0" /><xs:element name="CLADR" type="xs:string" minOccurs="0" /><xs:element name="INDEX_P" type="xs:string" minOccurs="0" /><xs:element name="HOUSE" type="xs:string" minOccurs="0" /><xs:element name="FLAT" type="xs:string" minOccurs="0" /><xs:element name="CORP" type="xs:string" minOccurs="0" /><xs:element name="INN" type="xs:string" minOccurs="0" /><xs:element name="SNILS" type="xs:string" minOccurs="0" /><xs:element name="GIV_DATE" type="xs:dateTime" minOccurs="0" /><xs:element name="ELIMIN_DATE" type="xs:dateTime" minOccurs="0" /><xs:element name="IRRELEVANT" type="xs:decimal" minOccurs="0" /><xs:element name="BESTBEFORE" type="xs:dateTime" minOccurs="0" /><xs:element name="CATEG" type="xs:double" minOccurs="0" /></xs:sequence></xs:complexType></xs:element></xs:choice></xs:complexType></xs:element></xs:schema><diffgr:diffgram xmlns:msdata="urn:schemas-microsoft-com:xml-msdata" xmlns:diffgr="urn:schemas-microsoft-com:xml-diffgram-v1"><LPU xmlns=""><DATA diffgr:id="DATA1" msdata:rowOrder="0"><ID_ASSURED>F331BD0E-49E7-423B-A82C-7F1F482D7FDD</ID_ASSURED><FAM>РҐР°Р»РёР»РѕРІР°</FAM><NAM>Р¤Р°РЅР·РёР»СЏ</NAM><FNAM>Р Р°РёСЃРѕРІРЅР°</FNAM><BORN_DATE>2001-02-16T00:00:00+06:00</BORN_DATE><SEX>Р–</SEX><POL_NUM_16>0297899783000323</POL_NUM_16><SMO>186</SMO><DOC_SER>I-РђР </DOC_SER><DOC_NUM>511996</DOC_NUM><DOC_TYPE>2</DOC_TYPE><CLADR>02033000096000400</CLADR><INDEX_P>453354</INDEX_P><HOUSE>8</HOUSE><GIV_DATE>2013-02-28T00:00:00+06:00</GIV_DATE><IRRELEVANT>0</IRRELEVANT><CATEG>5</CATEG></DATA></LPU></diffgr:diffgram></GetManDataMIASResult></GetManDataMIASResponse></soap:Body></soap:Envelope>';
		*/
		if ($response) {
			if (!empty($_REQUEST['debugIdent'])) {
				echo '<pre>';print_r($response);exit;
			}
			// echo "response: <xmp>{$response}</xmp>";
			/*$CI->textlog->add('timeout: '.$this->socketTimeout.', serviceURL: '.$this->serviceURI);
			$CI->textlog->add('headers: '.print_r($headers, true));
			$CI->textlog->add('requestText: '.$post_string);
			$CI->textlog->add('responseText: '.$response);*/
			$identData = $this->parsePersonIdentResponse($response);

			foreach ( $identData as $arrayKey => $array ) {
				foreach ( $array as $key => $value ) {
					$identData[$arrayKey][$key] = toAnsi($value);
				}
			}

			$result['identData'] = $identData;
		} else {
			$err = curl_error($soap_do);  
			$CI->load->library('textlog', array('file'=>'PersonIdentRequest_'.date('Y-m-d', time()).'.log'));
			$CI->textlog->add('timeout: '.$this->socketTimeout.', serviceURL: '.$this->serviceURI);
			$CI->textlog->add('headers: '.print_r($headers, true));
			$CI->textlog->add('requestText: '.$post_string);
			$CI->textlog->add('responseText: '.$err);
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом идентификации: '.$err;
			$result['success'] = false;
		}
		return $result;
	}


	private function writeToSocket($sd, $buf) {
		$total = 0;
		$len = strlen($buf);

		while ( $total < $len && ($written = fwrite($sd, $buf)) ) {
			$total += $written;
			$buf = substr($buf, $written);
		}

		return $total;
	}
}
