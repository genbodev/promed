<?php

require_once(APPPATH.'models/PersonIdentRequest_model.php');

class Kareliya_PersonIdentRequest_model extends PersonIdentRequest_model {
	/**
	 * Kareliya_PersonIdentRequest_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param string $snils
	 * @return string|null
	 */
	function formatSNILS($snils) {
		if (strlen($snils) != 11) return null;
		$pattern = '/^(\d{3})(\d{3})(\d{3})(\d{2})$/';
		$replacement = '$1-$2-$3 $4';
		return preg_replace($pattern, $replacement, $snils);
	}

	/**
	 *	Возвращает код соц. статуса из справочника SocStatus в Промед, соответствующий коду соц. статуса в ответе из фонда
	 *	$code @int код соц. статуса
	 */
	function getValidSocStatusCode($code) {
		$result = 0;

		switch ( $code ) {
			case 0: $result = 3; break; // работающий (id:51)
			case 1: $result = 4; break; // неработающий (id:52)
		}
		return $result;
	}
	
	/**
	 *	Возвращает идентификатор СМО по федеркальному коду СМО
	 *	$orgSmoCode int федеральный код СМО
	 */
	function getOrgSmoIdOnCode($orgSmoCode) {
		$query = "
			select top 1
				OS.OrgSMO_id as OrgSmo_id
			from v_OrgSMO OS with (nolock)
			where Orgsmo_f002smocod = :orgSmoCode
		";

		$queryParams = array('orgSmoCode' => $orgSmoCode);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Определение типа полиса по коду F008
	 */
	function getPolisTypeCode($f008code) {
		$query = "
			select top 1
				PolisType_id 
			from v_PolisType pt with (nolock)
			where PolisType_CodeF008 = :f008code
		";

		$queryParams = array('f008code' => $f008code);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Конвертация данных
	 */
	function convertIdentData($identData) {
		// Карелию -> в Уфу :)
		$map = array(
			'FAM' => 'FAM',
			'IM' => 'NAM',
			'OT' => 'FNAM',
			'sex' => 'SEX',
			'birthDate' => 'BORN_DATE',
			'serpolis' => 'POL_SER',
			'numpolis' => 'POL_NUM_16',
			'vidpolic' => 'GIV_DATE', // OpenPolis
			'closepolic' => 'ELIMIN_DATE', // ClosePolis
			'codestrah' => null,
			'typepolis' => null,
			'typeclosepolis' => null,
			//'codedoc' => 'DOC_TYPE',
			//'serdoc' => 'DOC_SER',
			//'numdoc' => 'DOC_NUM',
			//'docdate' => 'Document_begDate',
			//'whovid' => 'OrgDep_id',
			'mocode' => 'Lpu_Code',
			'snils' => 'SNILS',
			'STAT' => 'CATEG',
			'PHONE' => 'PersonPhone_Phone'
		);

		$val = array();
		foreach ($identData as $key => $value) {
			switch ($key) {
				case 'closepolic': // обработка дат
				case 'birthDate':
				case 'vidpolic':
					$d = mb_substr($value, 0, 10);
					if ($d == "1899-12-30") { // сервис вместо пустой даты возвращает такое безобразие
						$val[$map[$key]] = null;
					} else {
						$val[$map[$key]] = ConvertDateEx(mb_substr($value, 0, 10), "-", ".");
					}
					break;

				case 'sex': // обработка пола
					if ( mb_strtolower($value) == 'ж' ) {
						$val['Sex_Code'] = 2;
					}
					else if ( mb_strtolower($value) == 'м' ) {
						$val['Sex_Code'] = 1;
					}
					break;

				case 'typepolis': // тип полиса
					if ( is_numeric($value) ) {
						$ptResponse = $this->getPolisTypeCode($value);
						if ( is_array($ptResponse) && count($ptResponse) > 0 && !empty($ptResponse[0]['PolisType_id'])) {
							$val['PolisType_id'] = $ptResponse[0]['PolisType_id'];
						}
					}
					break;

				case 'codestrah': // страховая
					if ( is_numeric($value) ) {
						$smoIdResponse = $this->getOrgSmoIdOnCode($value);
						if ( is_array($smoIdResponse) && count($smoIdResponse) > 0 && !empty($smoIdResponse[0]['OrgSmo_id'])) {
							$val['OrgSmo_id'] = $smoIdResponse[0]['OrgSmo_id'];
						}
						else {
							$val['Alert_Msg'] = 'Не удалось определить идентификатор СМО';
						}
					}
					break;

				case 'snils': // снилс
					$val[$map[$key]] = str_replace(array(' ', '-'), '', $value);
					break;

				case 'STAT': // социальный статус (согласно спецификации возвращается только работающий и неработающий)
					$val[$map[$key]] = $this->getValidSocStatusSysNick($value);
					break;

				case 'PHONE': // телефон
					$val[$map[$key]] = substr(preg_replace("/\D+/i", "", $value), -10);
					break;

				default:
					if (isset($map[$key])) {
						$val[$map[$key]] = $value;
					} else {
						$val[$key] = $value;
					}

					break;
			}
		}
		if (empty($val['PolisType_id']) && !empty($val['GIV_DATE'])) {
			// пробуем определить полис по серии и номеру
			if (!empty($val['POL_NUM_16']) && mb_strlen($val['POL_NUM_16']) == 16) {
				$val['PolisType_id'] = 4; // ОМС ед. образца, пример "POL_SER":"","POL_NUM_16":"1048910838000174"
			} else if (empty($val['POL_SER']) || is_numeric($val['POL_SER'])) {
				$val['PolisType_id'] = 3; // временное свидетельство, пример "POL_SER":"","POL_NUM_16":"152464401"
			} else {
				$val['PolisType_id'] = 1; // ОМС стар. образца
			}
		}
		if (!empty($val['KLAdr_Index'])) {
			$val['RAddress_Name'] = $val['KLAdr_Index'] . (!empty($val['RAddress_Name'])?', '.$val['RAddress_Name']:'');
		}

		// Обработка данных после формирования массива данных

		// 1) Если тип полиса = временный, серия отсутствует и длина номера полиса больше 6 символов, то делим номер полиса на серию и номер по следующим правилам
		//    берем 3 символа слева и кидаем их в серию, остальное в номер (https://redmine.swan.perm.ru/issues/26562#note-76)
		if ((!empty($val['PolisType_id']) && $val['PolisType_id']==3) ) {
			$val['POL_NUM_16'] = $val['POL_SER'].''.$val['POL_NUM_16'];
		}
		return $val;
	}

	/**
	 * Выполение идентификации
	 */
	function doPersonIdentRequestKareliya($data) {
		$fromClient = (!empty($data['fromClient']) && $data['fromClient']);
		$this->load->library('swPersonIdentKareliyaSoap');
		$identObject = new swPersonIdentKareliyaSoap(
			$this->config->item('IDENTIFY_SERVICE_URI'),
			$this->config->item('IDENTIFY_SERVICE_LOGIN'),
			$this->config->item('IDENTIFY_SERVICE_PASS'),
			(int)$this->config->item('IS_DEBUG')
		);

		$response  = array(
			'success' => true,
			'Alert_Msg' => null,
			'Error_Msg' => null,
			'Error_Code' => null,
			'Person_IsInErz' => null,
		);

		if ($fromClient) {
			$check_list = array(
				array('Person_SurName', 'Person_FirName', 'Person_SecName', 'Person_BirthDay'),
				array('Person_SurName', 'Person_FirName', 'Person_BirthDay'),
			);
		} else {
			$check_list = array(
				array('Person_SurName', 'Person_FirName', 'Person_BirthDay', 'Polis_Ser', 'Polis_Num'),
				array('Person_SurName', 'Person_FirName', 'Person_BirthDay', 'Polis_Num'),
				array('Person_SurName', 'Person_FirName', 'Person_BirthDay', 'Person_SNILS'),
				array('Person_SurName', 'Person_FirName', 'Person_BirthDay', 'Document' => array('Document_Ser', 'Document_Num')),
			);
		}

		$documentIsInvalid = function($data) {
			$doc_types_w_ser = array(1, 3, 4, 6, 7, 8, 14, 15, 16, 17, 25);
			return (
				empty($data['DocumentType_Code']) || empty($data['Document_Num']) ||
				(in_array($data['DocumentType_Code'], $doc_types_w_ser) && empty($data['Document_Ser']))
			);
		};

		$person_fields = null;
		foreach($check_list as $check_item) {
			$check = true;
			foreach($check_item as $key => $field) {
				if ($key === 'Document') {
					if ($documentIsInvalid($data)) {
						$check = false;
						break;
					}
				} else if (empty($data[$field])) {
					$check = false;
					break;
				}
			}
			if ($check) {
				$person_fields = $check_item;
				break;
			}
		}

		if (!$person_fields) {
			if ($fromClient) {
				$message = "Идентификация возможна если заполнены поля: Фамилия, Имя, Дата рождения.";
			} else {
				$message = "
					Идентификация возможна если заполнены поля (любая из перечисленных ниже комбинаций):<br/>
					•	{Фамилия}, {Имя}, {Дата рождения}, {СНИЛС};<br/>
					•	{Фамилия}, {Имя}, {Дата рождения}, {Код типа документа, удостоверяющего личность}, {Номер или серия и номер документа, удостоверяющего личность};<br/>
					•	{Фамилия}, {Имя}, {Дата рождения}, {Серия и номер или номер полиса}.
				";
			}
			return array_merge($response, array('success' => false, 'Error_Msg' => $message, 'Error_Code' => 4));
		}

		$person_data = array();
		foreach($person_fields as $field) {
			if (is_array($field)) {
				foreach($field as $field1) {
					$person_data[$field1] = $data[$field1];
				}
			} else {
				$person_data[$field] = $data[$field];
			}
		}

		$onDate = !empty($data['PersonIdentOnDate'])?$data['PersonIdentOnDate']:$this->currentDT;
		if (gettype($onDate) != 'object') {
			$onDate = date_create($onDate);
		}

		// Формирование данных для запроса к сервису БДЗ
		$requestData = array(
			'FAM' => mb_ucfirst(mb_strtolower($person_data['Person_SurName'])),
			'IM' => mb_ucfirst(mb_strtolower($person_data['Person_FirName'])),
			'OT' => !empty($person_data['Person_SecName'])?mb_ucfirst(mb_strtolower($person_data['Person_SecName'])) : '',
			'birthDate' => !empty($person_data['Person_BirthDay'])?$person_data['Person_BirthDay']:'1900-01-01',
			'SerPolis' => !empty($person_data['Polis_Ser'])?$person_data['Polis_Ser']:null,
			'NumPolis' => !empty($person_data['Polis_Num'])?$person_data['Polis_Num']:null,
			'SerDocument' => !empty($person_data['Document_Ser'])?$person_data['Document_Ser']:null,
			'NumDocument' => !empty($person_data['Document_Num'])?$person_data['Document_Num']:null,
			'SNILS' => !empty($person_data['Person_SNILS'])?$this->formatSNILS($person_data['Person_SNILS']):null, // тут снилс надо передавать в формате "ххх-ххх-ххх хх"
			'DATEON' => $onDate->format('Y-m-d'),
			'Type_Request' => !$fromClient
		);

		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);

		/*$test = Array(
			"errorMsg" => null,
			"identData" => Array(
				Array(
					"FAM" => 'ВАСИЛЬЕВА',
					"IM" => 'ЛЮДМИЛА',
					"OT" => 'ЕФИМОВНА',
					"birthDate" => '1939-07-26',
					"sex" => 'Ж',
					"typepolis" => 3,
					"serpolis" => null,
					"numpolis" => '1072060873000108',
					"vidpolic" => '2013-08-05',
					"closepolic" => '2014-08-06',
					"typeclosepolis" => 3,
					"codestrah" => 10003,
					"codedoc" => 14,
					"serdoc" => '86 00',
					"numdoc" => '153440',
					"docdate" => '1899-12-30',
					"whovid" => null,
					'LPU_CODE'=>'2000015376',
					'LPUDT' =>'2014-11-11',
					'LPUDX' => '',
					'LPUAUTO'=>'',
					"snils" => '084-093-745 82',
					"adresreg" => Array(
						"pred" => 'ОЛОНЕЦКИЙ Р-Н,ИЛЬИНСКИЙ П,ГАНИЧЕВА УЛ,д.2,кв.2',
						"codereg" => 86000,
						"codeOKATO" => null,
						"pochindex" => null,
						"rayon" => 'ОЛОНЕЦКИЙ Р-Н',
						"city" => 'ИЛЬИНСКИЙ П',
						"naspunkt" => null,
						"street" => 'ГАНИЧЕВА УЛ',
						"dom" => 2,
						"korpus" => null,
						"kvartira" => 2,
					),

					"adresfact" => Array(
						"pred" => 'ОЛОНЕЦКИЙ Р-Н,ИЛЬИНСКИЙ П,ГАНИЧЕВА УЛ,д.2,кв.2',
						"codereg" => '86000',
						"codeOKATO" => null,
						"pochindex" => null,
						"rayon" => 'ОЛОНЕЦКИЙ Р-Н',
						"city" => 'ИЛЬИНСКИЙ П',
						"naspunkt" => null,
						"street" => 'ГАНИЧЕВА УЛ',
						"dom" => 2,
						"korpus" => null,
						"kvartira" => 2,
					),

					"mocode" => null,
					"STAT" => 1,
					"PHONE" => null
				)
			),

			"success" => 1
		);*/

		if ($requestResponse['success'] == false && $requestResponse['errorCode'] == 1) {
			$response['Person_IsInErz'] = 1;	//Не идентифицирован
			$response['Alert_Msg'] = 'По указанным данным человек не идентифицирован';
		} else if ($requestResponse['success'] == false) {
			$response['success'] = false;		//Ошибка при идентификации
			$response['Error_Msg'] = !empty($requestResponse['errorMsg'])?$requestResponse['errorMsg']:null;
			$response['Error_Code'] = !empty($requestResponse['errorCode'])?$requestResponse['errorCode']:null;
		} else if (!$fromClient && count($requestResponse['identData']) > 1) {
			$response['Person_IsInErz'] = 1;	//Не идентифицирован
			$response['Alert_Msg'] = 'Не удалось однозначно идентифицировать человека';
		} else {
			$lastDate = null;
			$selectIdentData = null;

			foreach($requestResponse['identData'] as $identData) {
				if (empty($identData['FAM'])) continue;

				$identData = $this->convertIdentData($identData);

				$date = !empty($identData['GIV_DATE'])?date_create($identData['GIV_DATE']):null;
				if ($date <= $onDate && $date > $lastDate) {
					$lastDate = $date;
					$selectIdentData = $identData;
				}
			}

			if ($selectIdentData) {
				$response['Person_IsInErz'] = 2;
				$response['Person_identDT'] = time();	//timestamp
				$response = array_merge($response, $selectIdentData);
			} else {
				$response['Person_IsInErz'] = 1;
				$response['Alert_Msg'] = 'По указанным данным человек не идентифицирован';
			}

		}

		return array($response);
	}

	/**
	 * Конвертирование параметров
	 */
	function convertParams($inpParams, $map, $listParams = array()) {
		$params = array();
		if (count($listParams) == 0) {
			$listParams = array_keys($map);
		}
		foreach($listParams as $name_left) {
			if (array_key_exists($name_left, $inpParams) && array_key_exists($name_left, $map)) {
				if (is_array($map[$name_left])) {
					foreach($map[$name_left] as $name_right) {
						$params[$name_right] = $inpParams[$name_left];
					}
				} else {
					$name_right = $map[$name_left];
					$params[$name_right] = $inpParams[$name_left];
				}
			}
		}
		return $params;
	}

	/**
	 * Сохранение измений данных человека после пакетной идентификации
	 */
	function savePersonKareliya($identData, $data) {
		$this->load->model('Person_model');

		$map = array(
			'Person_IsInErz' => 'Person_IsInErz',
			'FAM' => 'Person_SurName',
			'NAM' => 'Person_FirName',
			'FNAM' => 'Person_SecName',
			'BORN_DATE' => 'Person_BirthDay',
			'Sex_Code' => 'PersonSex_id',
			'SNILS' => 'Person_SNILS',
			//'CATEG' => 'SocStatus_SysNick',
			'PersonPhone_Phone' => 'PersonPhone_Phone',
			//Полис
			'PolisType_id' => 'PolisType_id',
			'OrgSmo_id' => 'OrgSMO_id',
			'POL_SER' => 'Polis_Ser',
			'POL_NUM_16' => 'Polis_Num',
			'GIV_DATE' => 'Polis_begDate',
			'ELIMIN_DATE' => 'Polis_endDate',
			//Документ
			/*'OrgDep_id' => 'OrgDep_id',
			'DOC_TYPE' => 'DocumentType_Code',
			'DOC_SER' => 'Document_Ser',
			'DOC_NUM' => 'Document_Num',
			'Document_begDate' => 'Document_begDate',*/
		);
		$identResponse = $this->convertParams($identData, $map);

		$date_fields = array('Person_BirthDay','Polis_begDate','Polis_endDate','Document_begDate');
		foreach($date_fields as $field) {
			if (!empty($identResponse[$field])) {
				$identResponse[$field] = ConvertDateFormat($identResponse[$field]);
			}
		}

		if ($identResponse['PolisType_id'] == 4 && !empty($identResponse['Polis_Num'])) {
			$identResponse['Federal_Num'] = $identResponse['Polis_Num'];
		}
		if (!empty($identResponse['PolisType_id'])) {
			$OMSSprTerr_id = $this->getFirstResultFromQuery("
				select top 1 OMSSprTerr_id from v_OMSSprTerr with(nolock) where OMSSprTerr_Code = :OMSSprTerr_Code
			", array('OMSSprTerr_Code' => 1));
			if (!$OMSSprTerr_id) {
				return $this->createError('','Ошибка при получении участка страхования');
			}
			$identResponse['OMSSprTerr_id'] = $OMSSprTerr_id;
		}
		if (empty($data['Polis_Ser'])) {
			$data['Polis_Ser'] = null;
		}

		/*$identResponse['SocStatus_id'] = null;
		if (!empty($identResponse['SocStatus_SysNick'])) {
			$SocStatus_id = $this->getFirstResultFromQuery("
				select top 1 SocStatus_id from v_SocStatus with(nolock) where SocStatus_SysNick = :SocStatus_SysNick
			", array('SocStatus_SysNick' => $identResponse['SocStatus_SysNick']), true);
			if ($SocStatus_id === false) {
				return $this->createError('','Ошибка при получении соц.статуса');
			}
			$identResponse['SocStatus_id'] = $SocStatus_id;
		}*/

		$EvnTypeList = array();
		$params = array();

		$PersonEvnAttributes = array(
			'Person_SurName','Person_FirName','Person_SecName','Person_BirthDay','PersonSex_id',
			'Person_SNILS','PersonPhone_Phone'
		);
		foreach($PersonEvnAttributes as $attribute) {
			if ($identResponse[$attribute] != $data[$attribute]) {
				if ($attribute == 'PersonPhone_Phone' && empty($identResponse[$attribute])) {
					continue;
				}
				$EvnTypeList[] = $attribute;
				$params[$attribute] = $identResponse[$attribute];
			}
		}

		$PolisAttributes = array(
			'OMSSprTerr_id','OrgSMO_id','PolisType_id','Polis_Ser','Polis_Num','Federal_Num','Polis_begDate','Polis_endDate'
		);
		$updatePolis = false;
		foreach($PolisAttributes as $attribute) {
			if ($identResponse[$attribute] != $data[$attribute]) {
				$updatePolis = true;break;
			}
		}
		if ($updatePolis) {
			$EvnTypeList[] = 'Polis';
			foreach($PolisAttributes as $attribute) {
				$params[$attribute] = !empty($identResponse[$attribute])?$identResponse[$attribute]:null;
			}

			$begDate = $this->getFirstResultFromQuery("
				select top 1
				convert(varchar(19), dateadd(second, 1, P.PersonEvn_insDT), 120) as Polis_begDate
				from v_Person_all P with(nolock)
				where P.Person_id = :Person_id
				and P.PersonEvnClass_id = 16 
				and cast(P.PersonEvn_insDT as date) = cast(:Polis_begDate as date)
				order by P.PersonEvn_insDT desc
			", array(
				'Person_id' => $data['Person_id'],
				'Polis_begDate' => $params['Polis_begDate']
			), true);
			if (!empty($begDate)) {
				$params['Polis_begDate'] = $begDate;
			}
		}

		if (count($EvnTypeList) > 0) {
			$params = array_merge($params, array(
				'EvnType' => implode('|',$EvnTypeList),
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id'],
				'session' => null,
				'Person_id' => $data['Person_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Person_IsInErz' => $identResponse['Person_IsInErz'],
				'insPeriodic' => true,	//Для добавления PersonSurName
			));

			try{
				$this->Person_model->exceptionOnValidation = true;	//Создает исключение при ошибке
				$resp = $this->Person_model->editPersonEvnAttributeNew($params);
				if (isset($resp[0]) && !empty($resp[0]['Error_Msg'])) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$this->Person_model->exceptionOnValidation = false;
			} catch(Exception $e) {
				$this->Person_model->exceptionOnValidation = false;
				return $this->createError($e->getCode(), $e->getMessage());
			}
		}

		$params = array(
			'Person_id' => $data['Person_id'],
			'Person_IsInErz' => $identResponse['Person_IsInErz'],
			'Person_identDT' => $this->currentDT->format('Y-m-d H:i:s'),
			'pmUser_id' => $data['pmUser_id'],
		);

		$IsBDZ = $this->getFirstResultFromQuery("
			select top 1 
				case when Server_id = 0 and Polis_endDate is null then 1 else 0 end as IsBDZ
			from v_PersonPolis with(nolock)
			where Person_id = :Person_id
			order by PersonPolis_insDT desc
		", $params, true);
		if ($IsBDZ === false) {
			return $this->createError('','Ошибка при определении флага БДЗ');
		}
		if ($IsBDZ) {
			$params['Server_id'] = 0;
		}

		$resp = $this->Person_model->updatePerson($params);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true));
	}

	/**
	 * Пакетная идентификация
	 */
	function PersonIdentPackage() {
		$this->load->library('textlog', array('file' => 'PersonIdentPackage.log'));
		$this->textlog->add('-------------------------------------------------------');
		$this->textlog->add('>>>>>>>>>>>>>>>>>>Запускаем задание<<<<<<<<<<<<<<<<<<<<');
		$this->textlog->add('-------------------------------------------------------');

		$PersonIdentPackage_id = null;
		$list = array();

		try{
			$query = "
				select top 1000
					PIPP.PersonIdentPackagePos_id,
					coalesce(e.Evn_disDT, e.Evn_setDT, dbo.tzGetDate()) as PersonIdentOnDate,
					p.Person_id,
					p.PersonEvn_id
				from
					PersonIdentPackagePos PIPP with(nolock)
					left join v_Evn e with(nolock) on e.Evn_id = PIPP.Evn_id
					cross apply(
						select top 1 *
						from v_Person_all p with(nolock)
						where p.Person_id = PIPP.Person_id
						and p.PersonEvn_insDT <= coalesce(e.Evn_disDT, e.Evn_setDT, dbo.tzGetDate())
						order by p.PersonEvn_insDT desc
					) p
					left join v_PersonPolis pol with(nolock) on pol.Polis_id = p.Polis_id
				where
					PIPP.PersonIdentPackage_id is null
				order by
					case
						when e.Evn_id is not null and pol.Polis_id is null then 1
						when e.Evn_id is not null and pol.Polis_id is not null then 2
						else 3
					end
			";
			$list = $this->queryResult($query);
			if (!is_array($list)) {
				throw new Exception('Ошибка при запросе списка людей на идентификацию');
			}
			if (count($list) > 0) {
				$query = "
					declare
						@ErrCode int,
						@ErrMsg varchar(400),
						@PersonIdentPackage_id bigint = null;
					exec p_PersonIdentPackage_ins
						@PersonIdentPackage_id = @PersonIdentPackage_id output,
						@PersonIdentPackage_Name = 'PersonIdentPackage_Name',
						@PersonIdentPackage_begDate = :PersonIdentPackage_begDate,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;
					select @PersonIdentPackage_id as PersonIdentPackage_id, @ErrMsg as ErrMsg;
				";
				$params = array(
					'PersonIdentPackage_begDate' => $this->currentDT->format('Y-m-d')
				);
				$resp = $this->queryResult($query, $params);
				if (!is_array($resp)) {
					throw new Exception('Ошибка при создании пакета');
				}
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
				}
				$PersonIdentPackage_id = $resp[0]['PersonIdentPackage_id'];
			}
		} catch(Exception $e) {
			$this->textlog->add('Ошибка: '.$e->getMessage());
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$personQuery = "
			select top 1
				p.Person_IsInErz,
				p.Person_id,
				p.PersonEvn_id,
				p.Person_SurName,
				p.Person_FirName,
				p.Person_SecName,
				p.Person_SNILS,
				CONVERT(varchar(10),p.Person_BirthDay,120) as Person_BirthDay,
				p.PersonPhone_Phone,
				p.Sex_id as PersonSex_id,
				p.SocStatus_id,
				pol.Polis_id,
				pol.PersonPolis_id,
				pol.OrgSMO_id,
				pol.OMSSprTerr_id,
				pol.Server_id,
				pol.PolisType_id,
				pol.Polis_Ser,
				pol.Polis_Num,
				p.Person_EdNum as Federal_Num,
				CONVERT(varchar(10),pol.Polis_begDate, 120) as Polis_begDate,
				CONVERT(varchar(10),pol.Polis_endDate, 120) as Polis_endDate,
				doc.Document_id,
				doc.DocumentType_id,
				dt.DocumentType_Code,
				doc.Document_Ser,
				doc.Document_Num
			from
				v_Person_all p with(nolock)
				left join v_PersonPolis pol with(nolock) on pol.Polis_id = p.Polis_id
				left join v_PersonDocument doc with(nolock) on doc.Document_id = p.Document_id
				left join v_DocumentType dt with(nolock) on dt.DocumentType_id = doc.DocumentType_id
			where
				p.PersonEvn_id = :PersonEvn_id
		";

		foreach($list as $item) {
			$person = $this->getFirstRowFromQuery($personQuery, $item);
			if (!$person) {
				$this->textlog->add("Ошибка при получении данных человека PersonEvn_id = {$item['PersonEvn_id']}");
				continue;
			}
			$person = array_merge($item, $person);

			$this->textlog->add("Производим идентификацию человека: {$person['Person_SurName']} {$person['Person_FirName']}");

			$resp = $this->doPersonIdentRequestKareliya($person);

			if (!$this->isSuccessful($resp)) {
				$error = (isset($resp[0]) && !empty($resp[0]['Error_Msg']))?$resp[0]['Error_Msg']:'результат идентификации отсутвует';
				$this->textlog->add("Ошибка {$person['Person_SurName']} {$person['Person_FirName']} - {$resp[0]['Error_Msg']}");
			} else {
				$identData = $resp[0];

				if (!empty($identData['Person_IsInErz'])) {
					if (!empty($identData['Alert_Msg'])) {
						$this->textlog->add("Сообщение {$person['Person_SurName']} {$person['Person_FirName']} - {$identData['Alert_Msg']}");
					}

					if ($identData['Person_IsInErz'] == 1) {
						$this->load->model('Person_model');
						$resp = $this->Person_model->updatePerson(array(
							'Person_id' => $person['Person_id'],
							'Person_IsInErz' => $identData['Person_IsInErz'],
							'Person_identDT' => $this->currentDT->format('Y-m-d H:i:s'),
							'pmUser_id' => 1
						));
						if (!$this->isSuccessful($resp)) {
							$this->textlog->add("Ошибка {$person['Person_SurName']} {$person['Person_FirName']} - {$resp[0]['Error_Msg']}");
						}
					}
					else if ($identData['Person_IsInErz'] == 2) {
						$person['Server_id'] = 0;
						$person['pmUser_id'] = 1;
						$resp = $this->savePersonKareliya($identData, $person);
						if (!$this->isSuccessful($resp)) {
							$this->textlog->add("Ошибка {$person['Person_SurName']} {$person['Person_FirName']} - {$resp[0]['Error_Msg']}");
						}
					}
				}
			}

			$sql = "update PersonIdentPackagePos set PersonIdentPackage_id = :PersonIdentPackage_id where PersonIdentPackagePos_id = :PersonIdentPackagePos_id";
			$this->db->query($sql, array('PersonIdentPackagePos_id' => $person['PersonIdentPackagePos_id'], 'PersonIdentPackage_id' => $PersonIdentPackage_id));
		}

		$this->textlog->add('-------------------------------------------------------');
		$this->textlog->add('>>>>>>>>>>>>>>>>>>>>Конец задания<<<<<<<<<<<<<<<<<<<<<<');
		$this->textlog->add('-------------------------------------------------------');
		return array(array('success'=>true));
	}
}
