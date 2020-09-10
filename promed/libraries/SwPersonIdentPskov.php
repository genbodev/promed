<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * Класс для работы с идентификацией людей (Псков)
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2010 Swan Ltd.
 * @author		Markoff A.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		апрель 2015
 */
 
class SwPersonIdentPskov {
	protected $soapClient = null;
	private $soapUrl;
	static $dbmodel = null;
	static $textlog = null;
	
	/**
	 * Constructor
	 */
	public function __construct($url = null, $username = null, $password = null ) {
		// при создании класса определяем параметры
		$CI =& get_instance();
		$CI->load->model('PersonIdentRequest_model', 'PersonIdentRequest_model');
		self::$dbmodel = $CI->PersonIdentRequest_model;
		$this->soapUrl = $url;
		//$this->username = $username;
		//$this->password = $password;
	}
	
	/**
	 *	Выполнение запроса на идентификацию
	 *	Входящие данные: массив с данными человека
	 */
	public function doPersonIdentRequest($requestData = array()) {
		/* 
		Массив входящих данных для идентификации
		
		LastName		Фамилия
		FirstName		Имя
		FatherName		Отчество
		Birthday		Дата рождения
		*/
		
		/*
		// Тестовый ответ
		return array (
		  'errorMsg' => '',
		  'identData' => 
		  array (
			'SMOCode' => '03102',
			'SMOName' => 'ФИЛИАЛ ООО "РГС-МЕДИЦИНА"- "РОСГОССТРАХ-БУРЯТИЯ-МЕДИЦИНА"',
			'Number' => '0392799741000080',
			'IssueDate' => '2013-10-22',
			'LastName' => 'ИВАНЕНКО',
			'FirstName' => 'ИВАН',
			'FatherName' => 'АНДРЕЕВИЧ',
			'Birthday' => '2002-07-08',
			'PolisType_id' => 4,
		  ),
		  'success' => true,
		);
		*/
		
		// Note: Возвращаются всегда все полисы, нужно выбирать правильный
		
		$CI =& get_instance();
		$CI->load->library('textlog', array('file'=>'PersonIdentRequest_'.date('Y-m-d', time()).'.log', 'logging'=>true));
		$CI->load->helper('Xml_helper');
		
		$CI->textlog->add('doPersonIdentRequest: Start identification to address: '.$this->soapUrl);
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
		
		$CI->textlog->add('doPersonIdentRequest: input data: '.var_export($requestData, true));
		
		// Опции soap-клиента
		$options = array( 
			'soap_version'=>SOAP_1_1, 
			'exceptions'=>true, // обработка ошибок
			'trace'=>1, // трассировка
			//'cache_wsdl'=>WSDL_CACHE_NONE, // не кешируем WSDL
			//'authentication' => SOAP_AUTHENTICATION_BASIC, // собственно по умолчанию так и есть
			//'encoding'=>'utf-8', // преобразуем из windows-1251 (кодировка на входе)
			'connection_timeout'=>20
		);
		// Формируем набор параметров 
		$xml = '<?xml version="1.0" encoding="utf-8"?>
			<ArrayOfUlPatient xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
			  <UlPatient>
				<LastName>'.$requestData['LastName'].'</LastName>
				<FirstName>'.$requestData['FirstName'].'</FirstName>
				<FatherName>'.$requestData['FatherName'].'</FatherName>
				<Birthday>'.$requestData['Birthday'].'</Birthday>
			  </UlPatient>
			</ArrayOfUlPatient>';
		
		$request = '
			<Operation xmlns="http://tempuri.org/">
				<XmlStream xmlns:a="http://schemas.datacontract.org/2004/07/VCLib.Communication" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
					<a:Data i:nil="true"/>
					<a:Name>GetPolicys</a:Name>
					<a:XML>'.htmlspecialchars($xml).'</a:XML>
				</XmlStream>
			</Operation>';
		
		// для рабочего
		$this->soapClient = new SoapClient($this->soapUrl, $options);
		try {
			$soapResult = $this->soapClient->Operation(new SoapVar($request, XSD_ANYXML));
			$CI->textlog->add('doPersonIdentRequest: method soapClient->Operation');
		} catch ( SoapFault $e ) {
			$CI->textlog->add('doPersonIdentRequest: error service identification: '.$e->getMessage());
			$result['errorMsg'] = 'Ошибка сервиса идентификации: '.$e->getMessage();
			$result['success'] = false;
			return $result;	
		}
		
		// Проверяем что ушло и что пришло в ответ
		/*echo "XML отправленного сообщения: \n".$this->soapClient->__getLastRequest();
		echo "Ответ на отправленное сообщение: \n".$this->soapClient->__getLastResponse();*/
		
		
		$resultPolis = array();
		if ($soapResult) {
			// далее из ответа надо получить данные 
			if (isset($soapResult->OperationResult) && isset($soapResult->OperationResult->XML)) {
				$xml = simplexml_load_string($soapResult->OperationResult->XML);
				//var_dump($xml->UlPatient->PolicyList);
				if (isset($xml->UlPatient) && isset($xml->UlPatient->PolicyList)) {
					$PolicyList = objectToArray($xml->UlPatient->PolicyList);
					if (isset($PolicyList['UlPolicy'])) {
						$polises = array();
						if (!isset($PolicyList['UlPolicy'][0])) { // Если 1 запись, то все равно сделаем массивом для удобства обработки
							$polises[] = $PolicyList['UlPolicy'];
						} else {
							$polises = $PolicyList['UlPolicy'];
						}
						$polisLast = array('IssueDate'=>'1900-01-01', 'Number'=>'0');
						
						if (count($polises)>0) {
							foreach ($polises as $i=>$polis) {
								// Разбор пришедших полисов, нужно выбрать самый подходящий 
								if (!isset($polis['ValidThrough'])) {  // Если нет даты окончания, то это самый валидный полис
									$resultPolis = $polis;
								}
							}
							
							if (count($resultPolis)==0) { // Если нет незакрытых полисов (т.е все закрытые)
								foreach ($polises as $i=>$polis) {
									if (isset($polis['IssueDate'])) {
										// Если есть дата начала, то выбираем самый последний по этой дате полис
										if ($polis['IssueDate']>$polisLast['IssueDate']) {
											$polisLast = $polis;
										}
										// Но если даты одинаковы берем тот полис, у которого длиннее номер (теоретически это ЕНП)
										if ($polis['IssueDate']==$polisLast['IssueDate'] && strlen($polis['Number'])>strlen($polisLast['Number']) ) {
											$polisLast = $polis;
										}
									}
								}
								$resultPolis = $polisLast;
							}
						} else {
							$CI->textlog->add('doPersonIdentRequest: no identification: 0 polises');
						}
					} else {
						$CI->textlog->add('doPersonIdentRequest: error service identification: empty result (UlPolicy)');
					}
				} else {
					$CI->textlog->add('doPersonIdentRequest: error service identification: empty result (UlPatient->PolicyList)');
				}
			} else {
				$CI->textlog->add('doPersonIdentRequest: error service identification: empty result (OperationResult->XML)');
			}
		} else {
			// Если ничего не вернулось, то с сервисом что-то не так, но пользователю об этом не скажем
			$CI->textlog->add('doPersonIdentRequest: error service identification: empty result');
		}
		
		if (count($resultPolis)==0) {
			$result['errorMsg'] = 'По указанным данным человек не идентифицирован';
			$result['errorCode'] = 1;
			$result['success'] = false;
		} else {
			$result['identData'] = $this->parsePersonIdentResponse((array)$resultPolis);
			$result['success'] = true;
		}
		
		$CI->textlog->add('doPersonIdentRequest: finish him! with result: '.var_export($result, true));
		//print_r($result);
		return $result;
	}
	/**
	 * Разбор полученных данных
	 * $data - набор данных, полученных от soap-сервиса
	 */
	public function parsePersonIdentResponse($data = array()) {
		$result = array();
		/* 
		Ответ сервиса 
		[SMOCode] => Код СМО
		[SMOName] => Наименование СМО
		[Series] => Серия полиса
		[Number] => Номер полиса
		[IssueDate] => Дата начала действия полиса (YYYY-MM-DD)
		[ValidThrough] => Дата окончания действия полиса (YYYY-MM-DD)
		[LastName] => Фамилия
		[FirstName] => Имя
		[FatherName] => Отчество
		[Birthday] => Дата рождения (YYYY-MM-DD)
		*/
		
		// Определим тип полиса по длине
		$data['PolisType_id'] = null;
		if (isset($data['Number'])) {
			if (strlen($data['Number'])<16) { // если короче 16 символов
				if (!isset($data['Series'])) { // если отсутствует серия
					$data['PolisType_id'] = 3; // это временное
				} else {
					$data['PolisType_id'] = 1; // это старый полис
				}
			} else {
				$data['PolisType_id'] = 4; // это ЕНП
			}
		}
		// 
		foreach ( $data as &$row ) { // преобразуем
			if (is_array($row) && count($row)==0) {
				$row = null;
			}
		}
		/*if (count($data)>0) { // если есть данные в списке 
			foreach ( $data as $key=>$row ) { // преобразуем
				if (is_array($row) && count($row)==0) {
					$row = null;
				}
				// разделим серию и полис
				
				print_r($row);
			}
		}*/
		return $data;
	}
}
