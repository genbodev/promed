<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * Класс для работы с идентификацией людей (Карелия)
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2010 Swan Ltd.
 * @author		Markoff A.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		декабрь 2013
 */

class SwPersonIdentKareliyaSoap {
	protected $soapClient = null;
	private $soapUrl;
	private $username;
	private $password;
	static $dbmodel = null;
	
	/**
	 * Constructor
	 */
	public function __construct($url = null, $username = null, $password = null , $debug = false) {
		// при создании класса определяем параметры
		$this->soapUrl = $url;
		$CI =& get_instance();
		$CI->load->model('PersonIdentRequest_model', 'PersonIdentRequest_model');
		self::$dbmodel = $CI->PersonIdentRequest_model;
		$this->soapUrl = $url;
		$this->username = $username;
		$this->password = $password;
		$this->debug = $debug;
	}
	
	/**
	 * Возвращает хидер для идентификации с помощью WS-Security
	 * @return SoapHeader|array
	 */
	public function wssecurity_text_header() {
		$auth = '
		<wsse:Security env:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
			<wsse:UsernameToken>
				<wsse:Username>'.$this->username.'</wsse:Username>
				<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->password.'</wsse:Password>
			</wsse:UsernameToken>
		</wsse:Security>
		';
		$authvalues = new SoapVar($auth, XSD_ANYXML);
		return new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvalues, true);
	}
	
	/**
	 *	Выполнение запроса на идентификацию
	 *	Входящие данные: массив с данными человека
	 */
	public function doPersonIdentRequest($requestData = array()) {
		/* 
		Массив входящих данных для идентификации
		
		FAM				Фамилия
		IM				Имя
		OT				Отчество
		birthDate		Дата рождения
		SerPolis		Серия полиса
		NumPolis		Номер полиса
		SerDocument		Серия документа
		NumDocument		Номер документа
		SNILS			СНИЛС
		DATEON			Дата, на которую осуществляется идентификация;
		Type_Request	Тип запроса:
		  0 - запрос всей истории;
		  1 - запрос последнего полиса по застрахованному лицу;
		
		*/
		
		$result = array(
			'errorMsg' => '',
			'errorCode' => null,
			'identData' => array(),
			'success' => true
		);
		if ( !is_array($requestData) || count($requestData) == 0 ) {
			$result['errorMsg'] = 'Отсутствуют входные данные';
			$result['success'] = false;
			return $result;
		}
		
		// Опции soap-клиента
		$options = array( 
			'soap_version'=>SOAP_1_2, 
			'exceptions'=>1, // обработка ошибок
			'trace'=>1, // трассировка
			//'cache_wsdl'=>WSDL_CACHE_NONE, // не кешируем WSDL
			//'authentication' => SOAP_AUTHENTICATION_BASIC, // собственно по умолчанию так и есть
			//'encoding'=>'utf-8', // преобразуем из windows-1251 (кодировка на входе)
			'connection_timeout'=>15
		);
		if($this->debug){
			$options['location']='http://test.kareliya.promedweb.ru:80/soap/PatientInterchangeEndPoint';
		}
		try {
			$error_msg = "Не удалось установить соединение с сервисом";
			set_error_handler(function() use ($error_msg) { throw new Exception($error_msg); }, E_ALL & ~E_NOTICE);
			$this->soapClient = new SoapClient($this->soapUrl, $options);
			restore_error_handler();
		} catch ( Exception $e ) {
			restore_error_handler();
			$e_msg = $e->getMessage();
			$result['errorMsg'] = 'Ошибка сервиса идентификации: '.$e_msg;
			$result['errorCode'] = 3;
			$result['success'] = false;
			return $result;
		}

		try {
			// выполняем запрос на поиск человека
			$resultSearchPatient = $this->soapClient->SearchPeople($requestData, null, $this->wssecurity_text_header());
		} catch ( SoapFault $e ) {
			$e_msg = $e->getMessage();
			if ($e_msg == "Gateway Time-out") {
				$result['errorMsg'] = "Сервис идентификации недоступен. Повторите попытку позднее.";
				$result['errorCode'] = 2;
			} else {
				$result['errorMsg'] = 'Ошибка сервиса идентификации: '.$e_msg;
				$result['errorCode'] = 3;
			}
			$result['success'] = false;
			return $result;	
		}
		
		// Проверяем что ушло и что пришло в ответ
		//echo "XML отправленного сообщения: \n".var_export($this->soapClient->__getLastRequest(), true);
		//echo "Ответ на отправленное сообщение: \n".var_export($this->soapClient->__getLastResponse(), true);
		
		if (!$resultSearchPatient) { // Если ничего не вернулось, то согласно спецификации ничего не нашли
			$result['errorMsg'] = 'По указанным данным человек не идентифицирован';
			$result['errorCode'] = 1;
			$result['success'] = false;
		} else {
			$result['identData'] = $this->parsePersonIdentResponse((array)$resultSearchPatient);
			$result['success'] = true;
		}
		
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
		
		FAM				Фамилия
		IM				Имя
		OT				Отчество
		birthDate		Дата рождения
		sex				Пол застрахованного лица
		typepolis		Тип документа подтверждающего факт страхования по ОМС в соответствии со справочником «F008» *.
		serpolis		Серия полиса
		numpolis		Номер полиса
		OpenPolis		Дата выдачи полиса
		ClosePolis		Дата закрытия полиса (дата прекращения действия полиса). В случае если дата не указана, то полис действует.
		typeclosepolis	Причина прекращения действия полиса.
		codestrah		Код страховщика в системе ОМС в соответствии со справочником «F002» *
		codedoc			Код типа документа удостоверяющего личность в соответствии со справочником «F011» *
		serdoc			Серия документа
		numdoc			Номер документа
		docdate			Дата выдачи документа
		whovid			Орган, выдавший документ
		snils			СНИСЛ
		adresreg		Адрес регистрации застрахованного лица
		adresfact		Адрес жительства застрахованного лица
		mocode			Код МО, к которому прикреплен застрахованный в соответствии со справочником «F001»*
		STAT			Статус пациента:
		  0 – работающий;
		  1 – неработающий;
		PHONE			Контактные данные пациента
		ExcessTop		Превышения результатов запроса:
		  True – Результат запроса превышен (выдано 20 первых записей, но записей по запросу имеется больше, необходимо уточнить параметры поиска);
		  False – Выданы все имеющиеся записи по запросу (20 или менее);
		
		*/
		
		if (count($data)>0) { // если есть данные в списке 
			foreach ( $data as $pacient ) { 
				// непонятно нужен ли дальше адрес, но пусть будет
				$pacient = (array)$pacient;
				$pacient['adresreg'] = (isset($pacient['adresreg']))?(array)$pacient['adresreg']:array();
				$pacient['adresfact'] = (isset($pacient['adresreg']))?(array)$pacient['adresreg']:array();
				$result[] = $pacient; 
			}
		}
		return $result;		
	}
}
