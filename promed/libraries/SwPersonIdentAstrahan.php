<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * Класс для работы с идентификацией людей (Астрахань)
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2010 Swan Ltd.
 * @author		Markoff A.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		июль 2014
 */

class SwPersonIdentAstrahan {
	protected $soapClient = null;
	private $soapUrl;
	//private $username;
	//private $password;
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
	 * Получаем массив из xml-результата 
	 */ 
	private function getArray($xml) {
		$xml = str_replace('<?xml version = "1.0" encoding="Windows-1252" standalone="yes"?>', '', $xml);
		return objectToArray(simplexml_load_string($xml));
	}
	
	/**
	 *	Выполнение запроса на идентификацию
	 *	Входящие данные: массив с данными человека
	 */
	public function doPersonIdentRequest($requestData = array()) {
		/* 
		Массив входящих данных для идентификации
		
		l_f				Фамилия
		l_i				Имя
		l_o				Отчество
		l_dr			Дата рождения
		l_s_polis		Серия полиса
		l_n_polis		Номер полиса
		l_ss			СНИЛС
		date			Дата, на которую осуществляется идентификация;
		actual			Тип запроса:
						  0 - запрос всей истории;
						  1 - запрос последнего полиса по застрахованному лицу;
		full			Полная идентификация
		*/
		$CI =& get_instance();
		$CI->load->library('textlog', array('file'=>'PersonIdentRequest_'.date('Y-m-d', time()).'.log', 'logging'=>true));
		
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
		// todo: Для тестового
		// $this->soapClient = new SoapClient('d:\\WS.WSDL', $options);
		// $this->soapClient->__setLocation($this->soapUrl);
		// для рабочего
		//echo $requestData['l_f'].' '.$requestData['l_i'].' '.$requestData['l_o'].' '.$requestData['l_dr']; return false;
		$this->soapClient = new SoapClient($this->soapUrl, $options);
		$rz = '';
		$resultRZ = null;
		$resultADR = null;
		$resultDOC = null;
		$resultFIO = null;
		
		try {
			// выполняем запрос для выбора пресловутого RZ
			If (!empty($requestData['polistype']) && strlen(trim($requestData['l_n_polis']))>0) {
				if ($requestData['polistype']!=4) { // Если передается не ЕНП, топопробуем идентифицировать по полисным данным
					$resultRZ = $this->soapClient->get_RZ_from_POLIS($requestData['l_s_polis'],$requestData['l_n_polis']);
					$CI->textlog->add('doPersonIdentRequest: method get_RZ_from_POLIS');
				} else { // А если ЕНП, то ничего идентифицировать не надо, просто берем его в качестве RZ
					$rz = $requestData['l_n_polis'];
					$CI->textlog->add('doPersonIdentRequest: Polis Number = RZ');
				}
			} else {
				$resultRZ = $this->soapClient->get_RZ_from_FIODR($requestData['l_f'],$requestData['l_i'],$requestData['l_o'],$requestData['l_dr'], '');
				$CI->textlog->add('doPersonIdentRequest: method get_RZ_from_FIODR');
			}
		} catch ( SoapFault $e ) {
			$CI->textlog->add('doPersonIdentRequest: error service identification: '.$e->getMessage());
			$result['errorMsg'] = 'Ошибка подключения к сервису: '.$e->getMessage();
			$result['success'] = false;
			return $result;	
		}
		// Проверяем что ушло и что пришло в ответ
		/*echo "XML отправленного сообщения: \n".$this->soapClient->__getLastRequest();
		echo "Ответ на отправленное сообщение: \n".$this->soapClient->__getLastResponse();*/
		
		
		if (!$resultRZ && empty($rz)) { // Если ничего не вернулось и RZ пустой, то с сервисом что-то не так, но пользователю об этом не скажем
			$CI->textlog->add('doPersonIdentRequest: error service identification: empty result');
			$result['errorMsg'] = 'Ошибка сервиса идентификации: сервис вернул пустой ответ';
			$result['errorCode'] = 1;
			$result['success'] = false;
		} else {
			// далее из ответа надо получить RZ
			//print_r($resultRZ);
			if (empty($rz)) { // Если RZ уже есть, то получать его не нужно
				$rzarray = $this->getArray($resultRZ);
				if (count($rzarray)>0 && isset($rzarray['cur1']['rz'])) {
					$rz = $rzarray['cur1']['rz'];
				}
			}
			if (!empty($rz)) {
				try {
					// Получаем полис по RZ
					$resultPL = $this->soapClient->get_POLIS_from_RZ2($rz, '');
					$CI->textlog->add('doPersonIdentRequest: method get_POLIS_from_RZ2');
					if($requestData['full']){
						$resultADR = $this->soapClient->get_ADRES_from_RZ($rz,'');
						$resultDOC = $this->soapClient->get_DOCS_from_RZ($rz,'');
						$resultFIO = $this->soapClient->get_FIODR_from_RZ($rz,'');
						$CI->textlog->add('doPersonIdentRequest: methods - (get_ADRES_from_RZ, get_RZ_from_DOCS, get_FIODR_from_RZ)');
					}
					
				} catch ( SoapFault $e ) {
					$CI->textlog->add('doPersonIdentRequest: error service identification: '.$e->getMessage());
					$result['errorMsg'] = 'Ошибка сервиса идентификации: '.$e->getMessage();
					$result['success'] = false;
					return $result;	
				}
				if (!$resultPL) {
					$CI->textlog->add('doPersonIdentRequest: error service identification: empty result');
					$result['errorMsg'] = 'Ошибка сервиса идентификации: сервис вернул пустой ответ';
					$result['errorCode'] = 1;
					$result['success'] = false;
				} else {
					//var_dump($resultPL);
					$polisarray = $this->getArray($resultPL);
					$resultPolis = array();
					
					$actualPolis = array();
					$lastPolis = array();
					$openPolis = array();
					$lastPolisDate=null;
					if (is_array($polisarray) && isset($polisarray['cur1'])) {
						if (isset($polisarray['cur1'][0])) { // несколько записей
							if (!$requestData['actual']) { // Если поиск по дате, то есть выбрать надо неактуальную дату, а попадающую под даты
								$date = date_create($requestData['date']);
								$resultPolis = $polisarray['cur1'][0];
								foreach ($polisarray['cur1'] as $row) {
									$begdate = date_create($row['datap']);
									// дату окончания возьмем из полиса (если полис неактуальный) и если ее нет, то установим текущую дату
									$enddate = date_create((!is_array($row['datape']) && isset($row['datape']) && $row['pz_actual']!=1)?$row['datape']:null); 
									// поскольку если даты окончания полиса нет, то в $enddate текущая дата, то допусловия на пустую дату оконачания не нужно
									if (($begdate <= $date) && ($date <= $enddate)) {
										$resultPolis = $row;
										$CI->textlog->add('doPersonIdentRequest: found polis on date '.$requestData['date']);
										break;
									}
								}
							} else {
								foreach ($polisarray['cur1'] as $row) {
									$enddate = date_create((!is_array($row['datape']) && isset($row['datape'])) ? $row['datape'] : null);
									if ($row['pz_actual'] == 1) {
										$actualPolis = $row; // берем актуальный полис
										break;
									}
									if ($enddate != null) {
										if ($lastPolisDate == null || $lastPolisDate < $enddate) {
											$lastPolisDate = $enddate;
											$lastPolis = $row;
										}
									} else {
										$openPolis = $row;
									}
								}
								// если актуального не нашли, возьмем первый
								if(count($actualPolis)!=0){
									$resultPolis = $actualPolis; // берем актуальный полис
									$CI->textlog->add('doPersonIdentRequest: found actual polis');
								}else if(count($openPolis)!=0){
									$resultPolis = $openPolis; // берем открытый полис
									$CI->textlog->add('doPersonIdentRequest: found open polis');
								}else if(count($lastPolis)!=0){
									$resultPolis = $lastPolis; // берем последний полис
									$CI->textlog->add('doPersonIdentRequest: found last polis');
								}else {
									$resultPolis = $polisarray['cur1'][0]; // берем первый попавшийся полис
									$CI->textlog->add('doPersonIdentRequest: found first polis');
								}
							}
						} else { // одна запись
							$resultPolis = $polisarray['cur1'];
						}
						if (!$requestData['actual']) { // Если поиск был по дате 
							if (count($resultPolis)==0) { // и мы ничего не нашли, то нужно выдать сообщение об ошибке
								$result['errorMsg'] = 'Не найден полис на дату';
								$result['errorCode'] = 1;
								$result['success'] = false;
							}
						}
					} else {
						$result['errorMsg'] = 'По указанным данным человек не идентифицирован';
						$result['errorCode'] = 1;
						$result['success'] = false;
					}
					
					$resultPolis['rz'] = $rz; // ЕНП
					$result['identData'] = $this->parsePersonIdentResponse((array)$resultPolis);
					if($resultADR!=null){
						$adresarray = $this->getArray($resultADR);
						if (is_array($adresarray)&& isset($adresarray['cur1'])){
							//$result['identData']['addres'] = ;
							$result['identData'] = array_merge($result['identData'],$adresarray['cur1']);
						}
					}
					if($resultDOC!=null){
						$documentarray = $this->getArray($resultDOC);
						if (is_array($documentarray) && isset($documentarray['cur1'])) {
							if (isset($documentarray['cur1'][0])) {
								foreach ($documentarray['cur1'] as $row) {
									if(!is_array($row['doc_v'])){
										$result['identData'] = array_merge($result['identData'],$row);
									}
								}
							}else{
								$result['identData'] = array_merge($result['identData'],$documentarray['cur1']);
							}
							
						}
					}
					if($resultFIO!=null){
						$attacharray = $this->getArray($resultFIO);
						if (is_array($attacharray)&& isset($attacharray['cur1'])){
							if(isset($attacharray['cur1']['lpu'])&&isset($attacharray['cur1']['date_prik'])){
								$result['identData']['lpu'] =$attacharray['cur1']['lpu'];
								$result['identData']['date_prik'] =$attacharray['cur1']['date_prik'];
							}
							$result['identData']['f'] =$attacharray['cur1']['f'];
							$result['identData']['i'] =$attacharray['cur1']['i'];
							$result['identData']['o'] =$attacharray['cur1']['o'];
							$result['identData']['ss'] = (!empty($attacharray['cur1']['ss']) ? $attacharray['cur1']['ss'] : '');
							$result['identData']['dr'] = date_format(date_create($attacharray['cur1']['dr']), 'd.m.Y');

						}
					}
					$result['success'] = true;
					$result['rz']=$rz;
					
				}
			} else {
				$result['errorMsg'] = 'По указанным данным человек не идентифицирован';
				$result['errorCode'] = 1;
				$result['success'] = false;
			}
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
		[pz_actual] => Актуальность полиса 
		[vid_pol] => Вид полиса 
		[sk] => Страховая компания
		[sn_pol] => серия и номер 
		[datap] => Дата полиса 
		[datapp] => Дата начала 
		[datape] => Дата окончания 
		[d_dosrochno] => Дата досрочного окончания 
		[data_izgot] => Дата изготовления
		[blank] => Номер бланка
		*/
		if (!empty($data['sn_pol'])) { // разделим серию и полис
			$pos = mb_strrpos(trim($data['sn_pol']), ' ');
			$data['polis_ser'] = trim(mb_substr($data['sn_pol'], 0, $pos));
			$data['polis_num'] = trim(mb_substr($data['sn_pol'], $pos));
		}
		if (!empty($data['vid_pol']) && $data['vid_pol']=='Новый' ) { // ЕНП
			$data['polis_ser'] = '';
			$data['polis_num'] = $data['rz'];
		}
		if (isset($data['pz_actual']) && $data['pz_actual']==1) {
			$data['datape'] = null;
		}
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
