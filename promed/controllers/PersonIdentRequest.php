<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Options_model opmodel
 * @property Person_model persmodel
 * @property PersonIdentRequest_model identmodel
 */
class PersonIdentRequest extends swController {
	public $inputRules = array(
		'PersonIdentPackage'=>array(
			
		),
		// Для вызова с формы редактирования человека
		'doPersonIdentRequest' => array(
			array(
				'field' => 'fromClient',
				'label' => 'fromClient',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Document_Num',
				'label' => 'Номер документа',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'full',
				'label' => 'Полная идентификация',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Document_Ser',
				'label' => 'Серия документа',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'DocumentType_Code',
				'label' => 'Код типа документа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSmo_id',
				'label' => 'Идентификатор СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLArea_id',
				'label' => 'Идентификатор объекта 1-4 уровня из адреса регистрации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLStreet_id',
				'label' => 'Идентификатор улицы из адреса регистрации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор застрахованного',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Inn',
				'label' => 'ИНН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SNILS',
				'label' => 'СНИЛС',
				'rules' => 'trim',
				'type' => 'snils'
			),
			array(
				'field' => 'PolisType_id',
				'label' => 'Тип полиса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Sex_Code',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'SocStatus_Code',
				'label' => 'Код социального статуса',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'UAddress_Flat',
				'label' => 'Квартира из адреса регистрации',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UAddress_House',
				'label' => 'Дом из адреса регистрации',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_IsBDZ',
				'label' => 'Признак БДЗ установлен',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonIdentOnDate',
				'label' => 'Идентификация на дату',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'BAddress_Address',
				'label' => 'Место рождения',
				'rule' => '',
				'type' => 'string',
			)
		)
	);

	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Person_model', 'persmodel');
		$this->load->model('PersonIdentRequest_model', 'identmodel');
	}


	/**
	*  Выполнение запроса на идентификацию пациента в базе данных застрахованных Республики Башкортостан
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования человека
	*/
	function doPersonIdentRequest() {
		/*
			echo'{"Person_identDT":1363861731,"PersonIdentState_id":1,"Server_id":0,"FAM":"\u0418\u0432\u0430\u043d\u043e\u0432","NAM":"\u0410\u043b\u0435\u043a\u0441\u0430\u043d\u0434\u0440","FNAM":"\u0410\u043b\u0435\u043a\u0441\u0435\u0435\u0432\u0438\u0447","BORN_DATE":"14.10.1973","POL_SER":"","POL_NUM_16":"1049620835000056","OrgSmo_id":"8000246","GIV_DATE":"30.01.2013","ELIMIN_DATE":false}';
			die();
		
			// для тестирования
			echo '{"Person_identDT":1363765266,"PersonIdentState_id":1,"Server_id":0,"ID_REG":"2510181","FAM":"\u0423\u0444\u0438\u043c\u0446\u0435\u0432\u0430","NAM":"\u0418\u0440\u0438\u043d\u0430","FNAM":"\u0410\u043b\u0435\u043a\u0441\u0430\u043d\u0434\u0440\u043e\u0432\u043d\u0430","BORN_DATE":"28.04.1951","Sex_Code":2,"POL_NUM_16":"0201951042858339","OrgSmo_id":"8000227","DOC_SER":"8004","DOC_NUM":"849831","DOC_TYPE":14,"KLCountry_rid":643,"KLRgn_rid":"2","KLSubRgn_rid":null,"KLCity_rid":"244440","KLTown_rid":null,"KLStreet_rid":"2077634","PersonSprTerrDop_rid":null,"RAddress_Name":"\u0411\u0410\u0428\u041a\u041e\u0420\u0422\u041e\u0421\u0422\u0410\u041d \u0420\u0415\u0421\u041f, \u0413 \u0423\u0424\u0410, \u0423\u041b \u041a\u0410\u041b\u0418\u041d\u0418\u041d\u0410, \u0414.82, \u041a\u0412.8","INDEX_P":"450047","HOUSE":"82","FLAT":"8","INN":"027309882280","SNILS":"01435132601","GIV_DATE":"15.03.2005","IRRELEVANT":"0","CATEG":5}';
			die();
		*/
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$isUfa = ($data['session']['region']['nick'] == 'ufa');
		$method = $this->config->item('IDENTIFY_SERVICE_METHOD');
		
		if (($data['session']['region']['nick'] == 'kareliya') && ($method=='soap')) { // идентификация для Карелии с использованием SOAP-сервиса
			$this->doPersonIdentRequestKareliya($data);
			return true;
		}

		$this->load->model("Options_model", "opmodel");		
		$globalOptions = $this->opmodel->getOptionsGlobals($data);
	
		if($isUfa) {
			$this->load->library('swPersonIdentUfa');
			$identObject = new swPersonIdentUfa(
				$this->config->item('IDENTIFY_SERVICE_URI'),
				$this->config->item('IDENTIFY_SERVICE_PORT'),
				(!empty($globalOptions['globals']['manual_identification_timeout']) ? $globalOptions['globals']['manual_identification_timeout'] : 120)
			);
		} else {
			$this->load->library('swPersonIdentKareliya');
			$identObject = new swPersonIdentKareliya(
				$this->config->item('IDENTIFY_SERVICE_URI'),
				$this->config->item('IDENTIFY_SERVICE_LOGIN'),
				$this->config->item('IDENTIFY_SERVICE_PASS')
			);
		}

		$val  = array();

		$err = getInputParams($data, $this->inputRules['doPersonIdentRequest']);

		if ( mb_strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		// Промед запрашивает со стороны ТФОМС дату обновления сводной базы застрахованных раз в сутки и хранит в своей БД.
		// Сервис ТФОМС передает дату/время обновления СБЗ.
		// Выполнение запроса к сервису БДЗ

		// Проверка актуальности данных в БД оставлена только для Уфы
		// https://redmine.swan.perm.ru/issues/20142
		if ( $isUfa ) {
			// Получение даты актуальности данных в сводной базе застрахованных
			$actual_ident_date = $this->identmodel->getActualIdentDT($globalOptions['globals']);

			// Получение даты последней идентификации человека
			$person_ident_date = $this->identmodel->getPersonIdentDT($data['Person_id']);

			if ( CheckDateFormat($actual_ident_date) == 0 && CheckDateFormat($person_ident_date) == 0 ) {
				$compareResult = swCompareDates($actual_ident_date, $person_ident_date);

				// Запрос на идентификацию не требуется
				if ( in_array($compareResult[0], array(0, 1)) ) {
					$val['PersonIdentState_id'] = -1;
					$this->ReturnData($val);
					return true;
				}
			}
		}

		$identDT = time();

		$response = $this->identmodel->getOrgSmoCode($data['OrgSmo_id']);

		if ( is_array($response) && count($response) > 0 && !empty($response[0]['OrgSmo_Code'])) {
			$data['OrgSmo_Code'] = $response[0]['OrgSmo_Code'];
		}
		else {
			$data['OrgSmo_Code'] = '';
		}

		$response = $this->identmodel->getKladrCode($data['KLArea_id'], $data['KLStreet_id']);

		if ( is_array($response) && count($response) > 0 && !empty($response[0]['Kladr_Code'])) {
			$data['Kladr_Code'] = $response[0]['Kladr_Code'];
		}
		else {
			$data['Kladr_Code'] = '';
		}

		// Формирование данных для запроса к сервису БДЗ
		$requestData = array(
			array(
				'FAM' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
				'NAM' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
				'FNAM' => mb_ucfirst(mb_strtolower($data['Person_Secname'])),
				'D_BORN' => (!empty($data['Person_Birthday']) ? $data['Person_Birthday'] . ' 00:00:00' : ''),
				'SEX' => $data['Sex_Code'],
				'DOC_TYPE' => $data['DocumentType_Code'],
				'DOC_SER' => $data['Document_Ser'],
				'DOC_NUM' => $data['Document_Num'],
				'SNILS' => $data['Person_SNILS'],
				'INN' => $data['Person_Inn'],
				'KLADR' => $data['Kladr_Code'],
				'HOUSE' => $data['UAddress_House'],
				'ROOM' => $data['UAddress_Flat'],
				'SMO' => $data['OrgSmo_Code'],
				'POL_SER' => $data['Polis_Ser'],
				'POL_NUM' => $data['Polis_Num'],
				'STATUS' => $data['SocStatus_Code'],
				'ID_REG' => (!empty($data['Person_id']) ? $data['Person_id'] : 1)
			)
		);

		if ( !$isUfa ) {
			$requestData['ACTUAL'] = true;
		}
		
		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);

		if ( !$isUfa ) {
			// По людям, которые однажды прошли идентификацию, и был проставлен признак БДЗ, при повторной идентификации, в случае возврата ответа "Соответствие не найдено" (то есть актуального полиса нет на момент идентификации) - делать повторный запрос без признака актуальности, но с указанием номера полиса. 
			if ( $requestResponse['success'] === false && !empty($requestResponse['errorCode']) && $requestResponse['errorCode'] == 1 && !empty($data['Person_IsBDZ']) && $data['Person_IsBDZ'] == 1 ) {
				$requestData[0]['ACTUAL'] = false;
				// die('test');
				$requestResponse = $identObject->doPersonIdentRequest($requestData);
			}
		}
		
		if ( $requestResponse['success'] === false ) {
			echo json_return_errors($requestResponse['errorMsg']);
			return false;
		}

		// Полученные данные
		$personData = $requestResponse['identData'];

		// Если идентифицирован...
		if ( is_array($personData) ) {
			if ( count($personData) == 1 && !empty($personData[0]['FAM']) ) {
				// ... то формируем данные для подстановки на форму редактирования
				$val['Person_identDT'] = $identDT;
				$val['PersonIdentState_id'] = 1;
				$val['Server_id'] = 0;
				
				foreach ( $personData[0] as $key => $value ) {
					switch ( $key ) {
						case 'CLADR':
							if ( !empty($value) && preg_match('/^\d+$/', $value) ) {
								if (mb_strlen($value) < 13) {
									$value = str_pad($value, 13, '0');
								} else if (mb_strlen($value) < 17) {
									$value = str_pad($value, 17, '0');
								} else if (mb_strlen($value) < 19) {
									$value = str_pad($value, 19, '0');
								}
							}
							if ( !empty($value) && preg_match('/^\d+$/', $value) && in_array(mb_strlen($value), array(13, 17, 19)) ) {
								$parseKladrCodeResponse = $this->identmodel->parseKladrCode(
									$this->identmodel->tmp_Altnames_getNewCode($value),// когда Обновление в СБЗ будет произведено, перекодировку надо будет убрать. подробнее см. #11630
									(!empty($personData[0]['HOUSE']) ? $personData[0]['HOUSE'] : ''),
									(!empty($personData[0]['CORP']) ? $personData[0]['CORP'] : ''),
									(!empty($personData[0]['FLAT']) ? $personData[0]['FLAT'] : '')
								);

								if ( is_array($parseKladrCodeResponse) && count($parseKladrCodeResponse) > 0 && !empty($parseKladrCodeResponse[0]['Address_Address'])) {
									$val['KLCountry_rid'] = $parseKladrCodeResponse[0]['KLCountry_id'];
									if (empty($val['KLAdr_Index'])) {
										$val['KLAdr_Index'] = $parseKladrCodeResponse[0]['KLAdr_Index'];
									}
									$val['KLRgn_rid'] = $parseKladrCodeResponse[0]['KLRgn_id'];
									$val['KLSubRgn_rid'] = $parseKladrCodeResponse[0]['KLSubRgn_id'];
									$val['KLCity_rid'] = $parseKladrCodeResponse[0]['KLCity_id'];
									$val['KLTown_rid'] = $parseKladrCodeResponse[0]['KLTown_id'];
									$val['KLStreet_rid'] = $parseKladrCodeResponse[0]['KLStreet_id'];
									$val['PersonSprTerrDop_rid'] = $parseKladrCodeResponse[0]['PersonSprTerrDop_id'];
									$val['RAddress_Name'] = $parseKladrCodeResponse[0]['Address_Address'];
								}
								else {
									$val['Alert_Msg'] = 'Не удалось распознать адрес регистрации';
								}
							}
						break;

						case 'INDEX_P':
							if (!empty($value)) {
								$val['KLAdr_Index'] = $value;
							}
						break;
							
						case 'ELIMIN_DATE':
							if ( mb_strlen($value) > 0 ) {
								$val['PersonIdentState_id'] = 3;
								$val['Server_id'] = $data['Server_id'];
							}
						case 'BESTBEFORE':
						case 'BORN_DATE':
						case 'GIV_DATE':
							$val[$key] = mb_substr($value, 0, 10);
						break;

						case 'SEX':
							if ( mb_strtolower($value) == 'ж' ) {
								$val['Sex_Code'] = 2;
							}
							else if ( mb_strtolower($value) == 'м' ) {
								$val['Sex_Code'] = 1;
							}
						break;

						case 'SMO':
							if ( is_numeric($value) ) {
								$smoIdResponse = $this->identmodel->getOrgSmoId($value);

								if ( is_array($smoIdResponse) && count($smoIdResponse) > 0 && !empty($smoIdResponse[0]['OrgSmo_id'])) {
									$val['OrgSmo_id'] = $smoIdResponse[0]['OrgSmo_id'];
								}
								else {
									$val['Alert_Msg'] = 'Не удалось определить идентификатор СМО';
								}
							}
						break;

						case 'SNILS':
							$val[$key] = str_replace(' ', '', str_replace('-', '', $value));
						break;

						case 'CATEG':
							$val[$key] = $this->identmodel->getValidSocStatusSysNick($value);
						break;

						case 'DOC_TYPE':
							$val[$key] = $this->identmodel->getValidDocumentTypeCode($value);
						break;

						default:
							$val[$key] = $value;
						break;
					}
				}
			}
			else {
				$val['Alert_Msg'] = 'В сводной базе данные не обнаружены';
				$val['PersonIdentState_id'] = 2;
			}
		}
		else {
			$val['Error_Msg'] = 'Неверный ответ сервиса идентификации';
		}
		
		if (!empty($val['KLAdr_Index'])) {
			$val['RAddress_Name'] = $val['KLAdr_Index'] . (!empty($val['RAddress_Name'])?', '.$val['RAddress_Name']:'');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
	
	/**
	 *  Выполнение запроса на идентификацию пациента в базе данных застрахованных республики Карелия
	 *  Входящие данные: $_POST['Person_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования человека
	 */
	function doPersonIdentRequestKareliya() {
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		if  (getRegionNick() != 'kareliya') {
			echo json_return_errors('Метод doPersonIdentRequestKareliya не применим для выбранного региона '.getRegionNick());
			return false;
		}

		$err = getInputParams($data, $this->inputRules['doPersonIdentRequest']);

		if ( mb_strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$map = array(
			'Person_Birthday' => 'Person_BirthDay',
			'Person_Surname' => 'Person_SurName',
			'Person_Firname' => 'Person_FirName',
			'Person_Secname' => 'Person_SecName',
		);
		foreach($map as $name_left => $name_right) {
			if (array_key_exists($name_left, $data)) {
				$data[$name_right] = $data[$name_left];
				unset($data[$name_left]);
			}
		}

		$response = $this->identmodel->doPersonIdentRequestKareliya($data);

		$this->ProcessModelSave($response, true, 'При идентификации произошла ошибка')->ReturnData();
		return true;
	}
	/**
	*Comment
	*/
	function PersonIdentPackage(){
		$this->load->model("Options_model", "opmodel");
		$this->load->model("PersonIdentRequest_model", "identmodel");
		
	
		$this->load->library('swPersonIdentAstrahan');
		$data = $this->ProcessInputData('PersonIdentPackage', true);
		
		if ( $data ) {
			//$globalOptions = $this->opmodel->getOptionsGlobals($data);
			$response = $this->identmodel->PersonIdentPackage($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
