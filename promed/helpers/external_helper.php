<?php
/**
* External_helper - хелпер для функций, помогающих работать с внешними системами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Petukhov Ivan (megatherion@list.ru)
* @version      30.11.2010
* 
*/


/**
 * Разбор ответа пришедшего из Уфы по идентификации людей
 * $xml string Ответ пришедший от сервера идентификации Уфы в виде одной строки
 * return array Массив с информацией о людях, информация по каждому человеку в виде ассоциативного массива
 */
function parsePersonIdentResponse($xml) {
	// Выходной массив результата
	$res = array();

	// Поля в ответе
	$responseFields = array(
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
		'FLAT',
		'INN',
		'SNILS',
		'GIV_DATE',
		'ELIMIN_DATE',
		'IRRELEVANT',
		'BESTBEFORE',
		'CATEG'
	);

	// Если ответ не содержит секций персональных данных....
	if ( strpos(strtolower($xml), '<person>') === false )  {
		// ... возвращаем пустой массив
		return $res;
	}

	// Вырезаем заголовок и отбрасываем закрывающий тег
	$xml = trim(substr($xml, strpos(strtolower($xml), '<person>') - 1), '</NewDataSet>');

	// Убираем пробелы в начале и конце, а также первый открывающий и последний закрывающий теги <person>
	$xml = trim($xml);
	$xml = trim($xml, '<person>');
	$xml = trim($xml, '</person>');

	// Убираем лишние символы между закрывающим и открывающим тегами
	$xml = preg_replace("/<\/person>.*<person>/", '</person><person>', $xml);

	// Получаем массив записей с персональными данными
	$personRecords = explode('</person><person>', $xml);

	// Обработка записей
	foreach ( $personRecords as $record ) {
		$person = array(); 

		// Выделяем перс. данные
		foreach ( $responseFields as $field ) {
			// Если поле в ответе отсутствует...
			if ( strpos(strtoupper($record), '<' . $field . '>') === false ) {
				// ... идем на следующую итерацию цикла
				continue;
			}

			// Определяем позиции начала и конца поля
			$end = strpos(strtoupper($record), '</' . $field . '>') + strlen('</' . $field . '>');
			$start = strpos(strtoupper($record), '<' . $field . '>');

			// Вырезаем нужное поле
			$person[$field] = substr($record, $start, $end - $start);
			// Убираем теги
			$person[$field] = strip_tags($person[$field]);
		}

		$res[] = $person;
	}

	return $res;
}


/**
 *	Получение закрывающего тега для запроса на идентификацию
 */
function getIdentRequestFooter() {
	return '</NewDataSet>';
}


/**
 *	Получение заголовка запроса на идентификацию
 */
function getIdentRequestHeader() {
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
function getIdentRequestRow($data) {
	$result = '';

	// Входящие данные - массив из 16 элементов
	if ( !is_array($data) || count($data) != 16 ) {
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
function doPersonIdentRequest($requestData = array(), $uri, $port) {
	$result = array(
		'errorMsg' => '',
		'identData' => array(),
		'success' => true
	);

	if ( !is_array($requestData) ) {
		$result['errorMsg'] = '';
		$result['success'] = false;
		return $result;
	}

	$requestText = getIdentRequestHeader();

	foreach ( $requestData as $array ) {
		$requestText .= getIdentRequestRow($array);
	}

	$requestText .= getIdentRequestFooter();

	try {
		$fp = fsockopen($uri, $port, $errno, $errstr);

		if ( !$fp ) {
			$result['errorMsg'] = '';
			$result['success'] = false;
		}
		else {
			writeToSocket($fp, $requestText);

			$responseText = fread($fp, 1);
			$text = "";

			if ( $responseText == '<' ) {
				while ( !strpos(strtolower($responseText), '</newdataset') && ($text = fread($fp, 2)) ) {
					$responseText .= $text;
				}

				if ( strpos(strtolower($responseText), '</newdataset') && substr($responseText, strlen($responseText) - 1, 1) != '>' ) {
					$responseText .= '>';
				}
			}
			else {
				$responseText .= fread($fp, 4095);
			}

			fclose($fp);

			if ( substr(trim($responseText), 0, 1) != '<' ) {
				$result['errorMsg'] = toAnsi($responseText);
				$result['success'] = false;
			}
			else {
				$identData = parsePersonIdentResponse($responseText);

				foreach ( $identData as $arrayKey => $array ) {
					foreach ( $array as $key => $value ) {
						$identData[$arrayKey][$key] = toAnsi($value);
					}
				}

				$result['identData'] = $identData;
			}
		}
	}
	catch ( Exception $e ) {
		$result['errorMsg'] = $e->getMessage();
		$result['success'] = false;
	}

	return $result;
}


/**
 * Description
 */
function writeToSocket($sd, $buf) {
	$total = 0;
	$len = strlen($buf);

	while ( $total < $len && ($written = fwrite($sd, $buf)) ) {
		$total += $written;
		$buf = substr($buf, $written);
	}

	return $total;
}
?>