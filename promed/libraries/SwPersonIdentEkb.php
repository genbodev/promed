<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * Класс для работы с идентификацией людей (Екатеринбург)
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2014 Swan Ltd.
 * @author		Dmitry Vlasenko
 * @link		http://swan.perm.ru/PromedWeb
 * @version		май 2014
 */

class SwPersonIdentEkb {
	protected $soapClient = null;
	private $soapUrl;
	
	/**
	 * Constructor
	 */
	public function __construct($url = null) {
		// при создании класса определяем параметры
		$this->soapUrl = $url;
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
			'exceptions'=>true, // обработка ошибок
			'trace'=>1, // трассировка
			//'cache_wsdl'=>WSDL_CACHE_NONE, // не кешируем WSDL
			//'authentication' => SOAP_AUTHENTICATION_BASIC, // собственно по умолчанию так и есть
			'encoding'=>'utf-8', // преобразуем из windows-1251 (кодировка на входе)
			'connection_timeout'=>15
		);
		
		$this->soapClient = new SoapClient($this->soapUrl, $options);
		try {
			// выполняем запрос на поиск человека
			//$resultSearchPatient = $this->soapClient->GetAnswer($requestData, null);
			//$resultSearchPatient = $this->soapClient->GetAnswerExt($requestData, null);
			$resultSearchPatient = $this->soapClient->GetInsPrkState($requestData, null);
		} catch ( SoapFault $e ) {
			$result['errorMsg'] = 'Ошибка сервиса: '.$e->getMessage();
			$result['success'] = false;
			return $result;	
		}

		// Проверяем что ушло и что пришло в ответ
		//echo "XML отправленного сообщения: \n".var_export($this->soapClient->__getLastRequest(), true);
		//echo "Ответ на отправленное сообщение: \n".var_export($this->soapClient->__getLastResponse(), true);
		if (!$resultSearchPatient) {
			$result['errorMsg'] = 'Пустой ответ от сервиса';
			$result['errorCode'] = 1;
			$result['success'] = false;
		} else {
			//$result['identData'] = $this->parsePersonIdentResponse((array)$resultSearchPatient);
			$result['identData'] = $this->parsePersonIdentResponse(objectToArray($resultSearchPatient));
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

		if (!empty($data['answer']) && !empty($data['answer']['result'])) {
			$answerResult = $data['answer']['result'];
			$ack = isset($answerResult['ack'])?$answerResult['ack']:null;

			$errors = array();
			if (!empty($answerResult['err'])) {
				$errors = isset($answerResult['err']['errcode'])?array($answerResult['err']):$answerResult['err'];
			}
			if ($ack == 2 && empty($answerResult['ins']) && empty($errors)) {
				$errors[] = array(
					'errcode' => 0,
					'errtext' => 'Не найдена актуальная СП'
				);
			}
			$result['errors'] = array_map(function($error){
				return array(
					'Error_Code' => $error['errcode'],
					'Error_Name' => $error['errtext']
				);
			}, $errors);

			if (!empty($answerResult['alg'])) {
				if (is_array($answerResult['alg'])) {
					$result['data']['PersonIdentAlgorithm_Code'] = $answerResult['alg'][0];
				} else {
					$result['data']['PersonIdentAlgorithm_Code'] = $answerResult['alg'];
				}
			}
			if (!empty($answerResult['ins'])) {
				$ins = $answerResult['ins'];

				if (!empty($ins['smo'])) {
					$result['data']['SMO_Code'] = $ins['smo'];
				}
				if (!empty($ins['dbeg'])) {
					$result['data']['Insur_BegDate'] = ConvertDateEx($ins['dbeg'], '-', '.');
					$result['data']['Polis_begDate'] = ConvertDateEx($ins['dbeg'], '-', '.');
				}
				if (!empty($ins['dend']) && $ins['dend'] != '2099-12-31') {
					$result['data']['Polis_endDate'] = ConvertDateEx($ins['dend'], '-', '.');
				}
				if (!empty($ins['id'])) {
					$result['data']['Ident'] = $ins['id'];
					$result['data']['BDZ_id'] = $ins['id'];
				}
				if (!empty($ins['w'])) {
					$result['data']['Sex_id'] = $ins['w'];
					$result['data']['Sex_Name'] = ($ins['w']==2)?'Женский':'Мужской';
				}
				if (!empty($ins['dr'])) {
					$result['data']['Person_BirthDay'] = ConvertDateEx($ins['dr'], '-', '.');
				}
				if (!empty($ins['snils'])) {
					$result['data']['Person_Snils'] = $ins['snils'];
				}
				if (!empty($ins['vpolis'])) {
					$result['data']['vpolis'] = $ins['vpolis'];
				}
				if (!empty($ins['fpolis'])) {
					$result['data']['fpolis'] = $ins['fpolis'];
				}
				if (!empty($ins['npolis'])) {
					$result['data']['Polis_Num'] = $ins['npolis'];
				}
			}
			if (!empty($answerResult['prk'])) {
				$prk = array();
				if (isset($answerResult['prk']['typeprk'])) {
					$prk = $answerResult['prk'];
				} else {
					$now = date_create();
					foreach($answerResult['prk'] as $item) {
						$prev = date_create(!empty($prk)?$prk['modt']:'1900-01-01');
						$curr = date_create($item['modt']);
						if ($prev < $curr && $curr <= $now) {
							$prk = $item;
						}
					}
				}

				if (!empty($prk['mo'])) {
					$result['data']['Lpu_Code'] = $prk['mo'];
				}
				if (!empty($prk['podr'])) {
					$result['data']['Rgn_Code'] = $prk['podr'];
				}
				if (!empty($prk['modt'])) {
					$result['data']['Attach_BegDate'] = ConvertDateEx($prk['modt'], '-', '.');
				}
			}
		}
		
		return $result;
	}
}
