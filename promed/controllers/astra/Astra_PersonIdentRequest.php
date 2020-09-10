<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Options_model opmodel
 * @property Person_model persmodel
 * @property PersonIdentRequest_model identmodel
 */
 
require_once(APPPATH.'controllers/PersonIdentRequest.php');

class Astra_PersonIdentRequest extends PersonIdentRequest {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	*  Выполнение запроса на идентификацию пациента для Астрахани
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования человека
	*/
	function doPersonIdentRequest() {
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		if  ($data['session']['region']['nick'] != 'astra') {
			echo json_return_errors('Метод идентификации не применим для выбранного региона '.$data['session']['region']['nick']);
			return false;
		}
		$this->load->model("Options_model", "opmodel");
		//$this->load->model("PersonIdentRequest_model", "identmodel");
		$globalOptions = $this->opmodel->getOptionsGlobals($data);
	
		$this->load->library('swPersonIdentAstrahan');
		$identObject = new swPersonIdentAstrahan(
			$this->config->item('IDENTIFY_SERVICE_URI')
		);
		
		$val  = array();

		$err = getInputParams($data, $this->inputRules['doPersonIdentRequest']);
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$identDT = time();
		/*$val = array("Person_identDT"=>1432633208,
		"PersonIdentState_id"=>3,
		"Server_id"=>0,
		"pz_actual"=>"1",
		"PolisType_id"=>4,
		"OrgSmo_id"=>8000018,
		"sn_pol"=>"01 09 9406975",
		"datap"=>"2014-04-28T00:00:00",
		"GIV_DATE"=>"28.04.2014",
		"ELIMIN_DATE"=>"",
		"d_dosrochno"=>null,
		"rz"=>"3051999784000072",
		"POL_SER"=>"",
		"POL_NUM_16"=>"3051999784000072",
		"ssity"=>"414004",
		"province"=>"12000",
		"region"=>"12401000000",
		"sity"=>array(),
		"rayon"=>array(),
		"street"=>"ЗЕЛЕНГИНСКАЯ 3-Я",
		"street_gni"=>array(),
		"house"=>"2",
		"section"=>array(),
		"apartment"=>"21",
		"street_t"=>array(),
		"DOC_TYPE"=>"3",
		"DOC_SER"=>"I-АГ",
		"DOC_NUM"=>"567400",
		"Document_begDate"=>"29.08.2000",
		"doc_v"=>"РОССИЙСКАЯ ФЕДЕРАЦИЯ ОТДЕЛ ЗАГСА СЕВЕРСКОГО РАЙОНА КРАСНОДАРСКОГО КРАЯ",
		"lpu_Nick"=>"ГБУЗ АО \"ДГП №3\"",
		"date_prik"=>"01.01.2013");
				$val=array("Person_identDT"=>1432633755,
		"PersonIdentState_id"=>3,
		"Server_id"=>0,
		"pz_actual"=>"1",
		"PolisType_id"=>4,
		"OrgSmo_id"=>8000018,
		"sn_pol"=>"01 09 9406975",
		"datap"=>"2014-04-28T00:00:00",
		"GIV_DATE"=>"28.04.2014",
		"ELIMIN_DATE"=>"",
		"d_dosrochno"=>null,
		"rz"=>"3051999784000072",
		"POL_SER"=>"",
		"POL_NUM_16"=>"3051999784000072");
		$this->ReturnData($val);
		return true;*/
		// Формирование данных для запроса к сервису БДЗ Астрахани (ФИО и ДР)
		$requestData = array(
			'l_f' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
			'l_i' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
			'l_o' => mb_ucfirst(mb_strtolower($data['Person_Secname'])),
			'l_dr' => (!empty($data['Person_Birthday']) ?  $data['Person_Birthday'] : '1900-01-01'),
			'l_s_polis' => $data['Polis_Ser'],
			'l_n_polis' => $data['Polis_Num'],
			'polistype' => (isset($data['PolisType_id']))?$data['PolisType_id']:null,
			'l_ss' => $data['Person_SNILS'], 
			'date' => (!empty($data['PersonIdentOnDate']) ? $data['PersonIdentOnDate'] : date('Y-m-d')),
			'actual' => (empty($data['PersonIdentOnDate'])),
			'full'=>(isset($data['full'])&&$data['full'])?true:false
		);
		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);

		If (!empty($requestData['polistype']) && strlen(trim($requestData['l_n_polis']))>0 && !empty($requestResponse['errorCode']) && $requestResponse['errorCode'] === 1) {
			// если был полис и чел не идентифицирован, то ищем без учета полиса
			// Формирование данных для запроса к сервису БДЗ Астрахани (ФИО и ДР)
			$requestData = array(
				'l_f' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
				'l_i' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
				'l_o' => mb_ucfirst(mb_strtolower($data['Person_Secname'])),
				'l_dr' => (!empty($data['Person_Birthday']) ?  $data['Person_Birthday'] : '1900-01-01'),
				'l_s_polis' => '',
				'l_n_polis' => '',
				'polistype' => null,
				'l_ss' => $data['Person_SNILS'],
				'date' => (!empty($data['PersonIdentOnDate']) ? $data['PersonIdentOnDate'] : date('Y-m-d')),
				'actual' => (empty($data['PersonIdentOnDate'])),
				'full'=>(isset($data['full'])&&$data['full'])?true:false
			);
			// Выполнение запроса к сервису БДЗ
			$requestResponse = $identObject->doPersonIdentRequest($requestData);
		}

		/*$requestResponse=	Array(
		'errorMsg' => '',
		'rz'=>3070470837000052,
		'identData' => Array
        (
            'pz_actual' => 1,
            'vid_pol' => 'Новый',
            'sk' => 15,
            'sn_pol' => '01 09 2922457',
            'datap' => '2014-03-11T00:00:00',
            'datapp' => '2014-03-11T00:00:00',
            'datape' => '',
            'd_dosrochno' =>'' ,
            'rz' => 3070470837000052,
            'polis_ser' => '',
            'polis_num' => 3070470837000052,
            'ssity' => Array
                (
                ),

            'province' => 12000,
            'region' => 12401,
           'sity' => 'АСТРАХАНЬ',
           'rayon' => 'Советский',
           'street' => 'Власова',
           'street_gni' => Array
               (
               ),
            'house' => 4,
            'section' => Array
               (
                ),
            'apartment' => 2,
            'street_t' => Array
               (
               ),
            'doc_t' => 14,
            'doc_s' => '12 02',
            'doc_n' => 566064,
            'doc_d' => '2002-11-15T00:00:00',
            'doc_v' => 'СОВЕТСКИМ РОВД Г.АСТРАХАНИ.',
            'lpu' => 300019,
            'date_prik' => '2012-01-14'
        ),

		'success' => 1
		);*/
		if ( $requestResponse['success'] === false ) { 
			echo json_return_errors($requestResponse['errorMsg']);
			return false;
		}
		//print_r($requestResponse);
	
		// Полученные данные
		$personData = $requestResponse['identData'];

		// Если идентифицирован...
		if ( is_array($personData) ) {
			if ( !empty($personData['sn_pol']) ) { 
				// ... то формируем данные для подстановки на форму редактирования
				$val['Person_identDT'] = $identDT;
				$val['PersonIdentState_id'] = 1;
				$val['Server_id'] = 0;
				// Астрахань -> в Уфимский формат :) 
				$map = array(
					/*
					'FAM' => 'FAM',
					'IM' => 'NAM',
					'OT' => 'FNAM',
					'sex' => 'SEX',
					'birthDate' => 'BORN_DATE',
					*/
					'polis_ser' => 'POL_SER',
					'polis_num' => 'POL_NUM_16',
					'datapp' => 'GIV_DATE', 
					'datape' => 'ELIMIN_DATE',
					'doc_t' => 'DOC_TYPE',
					'doc_s' => 'DOC_SER',
					'doc_n' => 'DOC_NUM',
					'doc_d' => 'Document_begDate',
					'date_prik' =>'date_prik',
					'lpu'=>'lpu_Nick'
				);
				
				foreach ( $personData as $key => $value ) {
					switch ($key) {
						case 'datape': // обработка дат
							if ( mb_strlen($value) > 0 && mb_substr($value, 0, 10)!="1899-12-30" ) {
								$val['PersonIdentState_id'] = 3;
								$val['Server_id'] = $data['Server_id'];
							}
						case 'doc_d':
						case 'date_prik':
						case 'datapp': 
							$d = mb_substr($value, 0, 10);
							if ($d == "1899-12-30") { // сервис вместо пустой даты возвращает такое безобразие
								$val[$map[$key]] = null;
							} else {
								$val[$map[$key]] = ConvertDateEx(mb_substr($value, 0, 10), "-", ".");
							}
						break;

						case 'vid_pol': // тип полиса 
							if ( $value=='Старый' ) {
								$val['PolisType_id'] = 1;
							}
							if ( $value=='Временный' ) {
								$val['PolisType_id'] = 3;
							}
							if ( $value=='Новый' ) {
								$val['PolisType_id'] = 4;
							}
						break;

						case 'sk': // страховая компания
							if ( is_numeric($value) ) {
								// Прямая стыковка, поскольку стыковочной таблицы нет
								// 8000422 МАКС - М
								// 8000018 СОГАЗ - МЕД
								// 68320077752 РОСНО
								if ($value==7) {
									$val['OrgSmo_id'] = 8000018;
								} elseif ($value==15) {
									$val['OrgSmo_id'] = 8000422;
								} else {
									$val['Error_Msg'] = 'По данным Фонда у пациента нет действующего полиса.';
								}
							}
						break;
						case 'lpu':
							$res = $this->db->query("select Lpu_Nick from v_Lpu_all where Lpu_f003mcod = ?", array($value));
							if ( is_object($res) ){
								$res=$res->result('array');
								if(count($res)>0){
									$val[$map[$key]] = $res[0]['Lpu_Nick'];
								}
							}
						break;
						
							
						/*
						case 'snils': // снилс
							$val[$map[$key]] = str_replace(array(' ', '-'), '', $value);
						break;
						
						case 'sex': // обработка пола 
							if ( mb_strtolower($value) == 'ж' ) {
								$val['Sex_Code'] = 2;
							}
							else if ( mb_strtolower($value) == 'м' ) {
								$val['Sex_Code'] = 1;
							}
						break;
						*/
						default:
							if (isset($map[$key])) {
								$val[$map[$key]] = $value;
							} else {
								$val[$key] = $value;
							}
							
						break;
					}
				}
			} else {
				// такая ошибка может быть очень иногда, когда сервис идентификации вернул данные, а фамилия пустая или ответ есть, а идентифицированных записей нет 
				// (согласно спецификации если ничего не нашли, то ответ сервиса пустой, значит проверка отработает выше, а это нештатная ситуация)
				$val['Alert_Msg'] = 'Ошибка сервиса идентификации или по указанным данным человек не идентифицирован: '.var_export($personData, true); 
				$val['PersonIdentState_id'] = 2;
			}
		}
		else {
			// такое вряд ли будет 
			$val['Error_Msg'] = 'Неверный ответ сервиса идентификации: '.var_export($personData, true);
		}
		if(isset($requestResponse['rz'])){
			$val['rz']=$requestResponse['rz'];
		}
		if (in_array($val['PersonIdentState_id'], array(1,3))) {
			$val['Person_IsInErz'] = 2;
		} else {
			$val['Person_IsInErz'] = 1;
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
