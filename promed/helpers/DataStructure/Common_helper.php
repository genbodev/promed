<?php
/**
 * Данные любого запроса
 */
class SendRequest
{
	/**
	 * 
	 * @var Message $Message
	 * @access public
	 */
	public $Message;

	/**
	 * 
	 * @var MessageData $MessageData
	 * @access public
	 */
	public $MessageData;

	/**
	 * 
	 * @param Message $Message
	 * @param MessageData $MessageData
	 * @access public
	 */
	public function __construct($Message, $MessageData)
	{
		$this->Message = $Message;
		$this->MessageData = $MessageData;
	}

}

/**
 * Блок Message содержит в себе служебные блоки сообщения СМЭВ.
 */
class Message
{

	/**
	 * 
	 * @var orgExternalType $Sender
	 * @access public
	 */
	public $Sender;

	/**
	 * 
	 * @var orgExternalType $Recipient
	 * @access public
	 */
	public $Recipient;

	/**
	 * 
	 * @var orgExternalType $Originator
	 * @access public
	 */
	public $Originator;

	/**
	 * 
	 * @var string $TypeCode
	 * @access public
	 */
	public $TypeCode;

	/**
	 * 
	 * @var dateTime $Date
	 * @access public
	 */
	public $Date;

	/**
	 * 
	 * @var idType $RequestIdRef
	 * @access public
	 */
	public $RequestIdRef;

	/**
	 * 
	 * @var string $ServiceCode
	 * @access public
	 */
	public $ServiceCode;

	/**
	 * 
	 * @var string $CaseNumber
	 * @access public
	 */
	public $CaseNumber;

	/**
	 * 
	 * @param orgExternalType $Sender
	 * @param orgExternalType $Recipient
	 * @param orgExternalType $Originator
	 * @param string $TypeCode
	 * @param dateTime $Date
	 * @param idType $RequestIdRef
	 * @param string $ServiceCode
	 * @param string $CaseNumber
	 * @access public
	 */
	public function __construct($Sender, $Recipient, $Originator, $TypeCode, $Date, $RequestIdRef = null, $ServiceCode = null, $CaseNumber = null)
	{
		$this->Sender = $Sender;
		$this->Recipient = $Recipient;
		$this->Originator = $Originator;
		$this->TypeCode = $TypeCode;
		$this->Date = $Date;
		$this->RequestIdRef = $RequestIdRef;
		$this->ServiceCode = $ServiceCode;
		$this->CaseNumber = $CaseNumber;
	}

}


/**
 * Сведения об информационной системе
 */
class orgExternalType {
	
	public $Code;
	
	
	public $Name;

	/**
	 * orgExternalType constructor.
	 * @param $Code
	 * @param $Name
	 */
	public function __construct($Code, $Name) {
		$this->Code = $Code;
		$this->Name = $Name;
	}
	
}


/**
* Данные сообщения в ответе на любой запрос
*/
class MessageData
{

	/**
	 * 
	 * @var AppData $AppData
	 * @access public
	 */
	public $AppData;

	/**
	 * 
	 * @var AppDocumentType $AppDocument
	 * @access public
	 */
	public $AppDocument;

	/**
	 * 
	 * @param AppData $AppData
	 * @param AppDocumentType $AppDocument
	 * @access public
	 */
	public function __construct($AppData, $AppDocument = null)
	{
		$this->AppData = $AppData;
		$this->AppDocument = $AppDocument;
	}

}

/**
* Данные сообщения в ответе на любой запрос к ERGateService
*/
class ERGate_MessageData
{

	/**
	 * 
	 * @var String $response
	 * @access public
	 */
	public $message;


	/**
	 * 
	 * @param String $response
	 * @access public
	 */
	public function __construct($response = '')
	{
		$this->message = $response;
	}

}

/**
 * Базовый класс для сообщений любого запроса
 */
class AppData
{
	var $messageCode;
	
	var $message;

	/**
	 * AppData constructor.
	 * @param $messageCode
	 * @param $message
	 */
	public function __construct($messageCode, $message)
	{
		$this->messageCode = $messageCode;
		$this->message = $message;
	}
}


/**
* Ответ на любой запрос
*/
class SendResponse
{
	/**
	* 
	* @var MessageData $MessageData
	* @access public
	*/
	public $MessageData;

	/**
	* 
	* @param MessageData $MessageData
	* @access public
	*/
	public function __construct( $MessageData )
	{
		$this->Message = null; // структура Message обязательна по описанию сервиса
		$this->MessageData = $MessageData;
	}
	
}

/**
* Ответ с ошибкой
*/
class SendBadResponse
{
	/**
	* 
	* @var MessageDataBadResponse $MessageData
	* @access public
	*/
	public $MessageData;

	/**
	* 
	* @param MessageDataBadResponse $MessageData
	* @access public
	*/
	public function __construct( $MessageData )
	{
		$this->Message = null; // структура Message обязательна по описанию сервиса
		$this->MessageData = $MessageData;
	}
	
}

/**
* Сообщение для ответа об ошибке
*/
class MessageDataBadResponse
{
	/**
	* 
	* @var AppDataBadResponse $AppData
	* @access public
	*/
	public $AppData;

	/**
	* 
	* @param AppDataBadResponse $AppData
	* @access public
	*/
	public function __construct( $AppData )
	{
		$this->AppData = $AppData;
	}

}


/**
* Класс с сообщения об ошибке
*/
class AppDataBadResponse
{
	/**
	* 
	* @var Error $Error
	* @access public
	*/
	public $Error;

	/**
	 * AppDataBadResponse constructor.
	 * @param $Error
	 */
	public function __construct($Error)
	{
		$this->Error = $Error;
	}

}

/**
* Класс ошибки
*/
class CommonError
{
	/**
	* 
	* @var int $errorCode
	* @access public
	*/
	public $errorCode;

	/**
	* 
	* @var string $errorMessage
	* @access public
	*/
	public $errorMessage;

	/**
	* 
	* @param int $errorCode
	* @param string $errorMessage
	* @access public
	*/
	public function __construct($errorCode, $errorMessage)
	{
		$this->errorCode = $errorCode;
		$this->errorMessage = $errorMessage;
	}

}

/**
* Медицинская специализация
*/
class MedicalSpecialization {
	
	var $id;
	var $name;
	var $description;

	/**
	 * MedicalSpecialization constructor.
	 * @param $id
	 * @param $name
	 * @param $description
	 */
	public function __construct($id, $name, $description) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
	}
}

/**
* Ответ на сообщение GetCareProfiles
*/
class AppDataGetMedicalSpecializations extends AppData
{
	/**
	 * AppDataGetMedicalSpecializations constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach($data as $row) {
			$row = new SoapVar(
				new MedicalSpecialization(
					$row['id'],
					toUtf($row['name']),
					null
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'medical-specialization'); 
		}
		$this->{'medical-specializations'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}

}


/**
* Медицинский профиль
*/
class CareProfile {
	
	var $id;
	var $name;

	/**
	 * CareProfile constructor.
	 * @param $id
	 * @param $name
	 */
	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}
}


/**
* Ответ на сообщение GetCareProfiles
*/
class AppDataGetCareProfiles extends AppData
{
	/**
	 * AppDataGetCareProfiles constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach($data as $row) {
			$row = new SoapVar(
				new CareProfile(
					$row['id'],
					toUtf($row['name'])
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'care-profile'); 
		}
		$this->{'care-profiles'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}


/**
* Метод оплаты
*/
class PaymentMethod {
	
	var $id;
	var $name;

	/**
	 * PaymentMethod constructor.
	 * @param $id
	 * @param $name
	 */
	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}
}


/**
* Ответ на сообщение GetPaymentMethods
*/
class AppDataGetPaymentMethods extends AppData
{
	/**
	 * AppDataGetPaymentMethods constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach($data as $row) {
			$row = new SoapVar(
				new PaymentMethod(
					$row['id'],
					toUtf($row['name'])
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'payment-method'); 
		}
		$this->{'payment-methods'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}

/**
* Вид услуги
*/
class ServiceType {
	
	var $id;
	var $name;

	/**
	 * ServiceType constructor.
	 * @param $id
	 * @param $name
	 */
	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}
}


/**
* Ответ на сообщение GetServiceTypes
*/
class AppDataGetServiceTypes extends AppData
{
	/**
	 * AppDataGetServiceTypes constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach($data as $row) {
			$row = new SoapVar(
				new ServiceType(
					$row['id'],
					toUtf($row['name'])
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'service-type'); 
		}
		$this->{'service-types'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}

/**
* Ответ на сообщение GetServiceType
*/
class AppDataGetServiceType extends AppData
{
	/**
	 * AppDataGetServiceType constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		$row = $data[0];
		$row = new SoapVar(
			new ServiceType(
				$row['id'],
				toUtf($row['name'])
			),
			SOAP_ENC_OBJECT
		);
		$this->{'service-type'} = new SoapVar($row, SOAP_ENC_OBJECT, '', '', 'http://smev.gosuslugi.ru/rev110801');
			
	}
}


/**
* Тип записи
*/
class ReservationType {
	
	var $id;
	var $name;

	/**
	 * ReservationType constructor.
	 * @param $id
	 * @param $name
	 */
	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}
}


/**
* Ответ на сообщение GetReservationTypes
*/
class AppDataGetReservationTypes extends AppData
{
	/**
	 * AppDataGetReservationTypes constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach($data as $row) {
			$row = new SoapVar(
				new ReservationType(
					$row['id'],
					toUtf($row['name'])
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'reservation-type'); 
		}
		$this->{'reservation-types'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}


/**
* Адрес ЛПУ
*/
class LpuAddress {
	
	var $region;
	var $city;
	var $street;
	var $house;

	/**
	 * LpuAddress constructor.
	 * @param $region
	 * @param $city
	 * @param $street
	 * @param $house
	 */
	public function __construct($region, $city, $street, $house) {
		$this->region = $region;
		$this->city = $city;
		$this->street = $street;
		$this->house = $house;
	}
}


/**
* Регион
*/
class KLRegion {
	
	//var $kladr-id;
	var $name;

	/**
	 * KLRegion constructor.
	 * @param $id
	 * @param $name
	 */
	public function __construct($id, $name) {
		$this->{'kladr-id'} = $id;
		$this->name = $name;
	}
}


/**
* Город
*/
class KLCity {
	
	//var $kladr-id;
	var $name;

	/**
	 * KLCity constructor.
	 * @param $id
	 * @param $name
	 */
	public function __construct($id, $name) {
		$this->{'kladr-id'} = $id;
		$this->name = $name;
	}
}


/**
* Улица
*/
class KLStreet {
	
	//var $kladr-id;
	var $name;

	/**
	 * KLStreet constructor.
	 * @param $id
	 * @param $name
	 */
	public function __construct($id, $name) {
		$this->{'kladr-id'} = $id;
		$this->name = $name;
	}
}


/**
* Ответ на сообщение GetPlaces
*/
class AppDataGetPlaces extends AppData
{
	/**
	 * AppDataGetPlaces constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach($data as $row) {
			
			$careprofiles = array();
			foreach($row['careprofiles'] as $row2) {
				$row2 = new SoapVar(
					new CareProfile(
						$row2['id'],
						toUtf($row2['name'])
					),
					SOAP_ENC_OBJECT
				);
				$careprofiles[] = new SoapVar($row2, SOAP_ENC_ARRAY, '', '', 'care-profile'); 
			}
			
			$row = new SoapVar(
				array(
					'id' => $row['id'],
					'name' => toUtf($row['name']),
					'address' => new SoapVar( 
						new LpuAddress(
							new KLRegion(toUtf($row['KLRgn_Name']), $row['KLRgn_Code']),
							new KLCity(toUtf($row['KLCity_Name']), $row['KLCity_Code']),
							new KLStreet(toUtf($row['KLStreet_Name']), $row['KLStreet_Code']),
							toUtf($row['house'])
						), 
						SOAP_ENC_OBJECT,
						'',
						'',
						'address'
					),
					'phone' => toUtf($row['phone']),
					'description' => toUtf($row['description']),
					'prefix' => toUtf($row['prefix']),
					'latitude' => null,
					'longtitude' => null,
					'provider' => null,
					'care-profiles' => new SoapVar($careprofiles, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801')
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'place'); 
		}
		$this->{'places'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}

/**
* Ответ на сообщение GetPlace
*/
class AppDataGetPlace extends AppDataGetPlaces
{
}

/**
* Ответ на сообщение GetRegions
*/
class AppDataGetRegions extends AppData
{
	/**
	 * AppDataGetRegions constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach ($data as $row) {
			$row = new SoapVar(
				array(
					'id' => $row['id'],
					'code' => $row['code'],
					'name' => toUtf($row['name']),
					'abbr' => toUtf($row['abbr'])
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'region');
		}
		$this->{'regions'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}

/**
* Ответ на сообщение GetCities
*/
class AppDataGetCities extends AppData
{
	/**
	 * AppDataGetCities constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach ($data as $row) {
			$row = new SoapVar(
				array(
					'id' => $row['id'],
					'code' => $row['code'],
					'name' => toUtf($row['name']),
					'abbr' => toUtf($row['abbr']),
					'district' => toUtf($row['district'])
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'city');
		}
		$this->{'cities'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}

/**
* Ответ на сообщение GetStreets
*/
class AppDataGetStreets extends AppData
{
	/**
	 * AppDataGetStreets constructor.
	 * @param $data
	 */
	public function __construct($data)
	{
		$res = array();
		foreach ($data as $row) {
			$row = new SoapVar(
				array(
					'id' => $row['id'],
					'code' => $row['code'],
					'name' => toUtf($row['name']),
					'abbr' => toUtf($row['abbr'])
				),
				SOAP_ENC_OBJECT
			);
			$res[] = new SoapVar($row, SOAP_ENC_ARRAY, '', '', 'street');
		}
		$this->{'streets'} = new SoapVar($res, SOAP_ENC_OBJECT, '', 'http://smev.gosuslugi.ru/rev110801');
	}
}
