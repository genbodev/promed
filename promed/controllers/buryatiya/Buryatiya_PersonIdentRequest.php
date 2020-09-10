<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Options_model opmodel
 * @property Person_model persmodel
 * @property PersonIdentRequest_model identmodel
 */
 
require_once(APPPATH.'controllers/PersonIdentRequest.php');

class Buryatiya_PersonIdentRequest extends PersonIdentRequest {
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
		
		/*if (!isSuperAdmin()) {
			$val['Error_Msg'] = 'Только для тестирования!';
			$this->ReturnData($val);
			return false;
		}*/
		
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		$this->load->model("PersonIdentRequest_model", "identmodel");
		
		$this->load->library('swPersonIdentBur');
		$identObject = new swPersonIdentBur(
			$this->config->item('IDENTIFY_SERVICE_URI')
		);
		
		$val  = array();

		$err = getInputParams($data, $this->inputRules['doPersonIdentRequest']);
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$identDT = time();

		// Формирование данных для запроса к сервису БДЗ Астрахани (ФИО и ДР)
		$requestData = array(
			'LastName' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
			'FirstName' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
			'FatherName' => mb_ucfirst(mb_strtolower($data['Person_Secname'])),
			// убираем дефисы в дате, сервис понимает либо DD.MM.YYYY либо YYYYMMDD
			'Birthday' => (!empty($data['Person_Birthday']) ?  str_replace('-', '',$data['Person_Birthday']) : '19000101'),
			// Параметры оставил, но они не используются 
			'date' => (!empty($data['PersonIdentOnDate']) ? $data['PersonIdentOnDate'] : null),
			'actual' => (empty($data['PersonIdentOnDate']))
		);

		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);

		/*$requestResponse = array(
			'success' => true,
			'errorMsg' => '',
			'identData' => array(
				"SMOCode"=>"03102",
				"SMOName"=>"ООО \"РГС-МЕДИЦИНА\"",
				"Number"=>"0397689780000031",
				"IssueDate"=>"2016-05-11",
				"ENP"=>"0397689780000031",
				"LastName"=>"ЗАЦЕПИНА",
				"FirstName"=>"ВАЛЕРИЯ",
				"FatherName"=>"АЛЕКСЕЕВНА",
				"Birthday"=>"2013-02-19",
				"PolisType_id"=>4
			),
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
			if ( !empty($personData['Number']) ) { 
				// ... то формируем данные для подстановки на форму редактирования
				$val['Person_identDT'] = $identDT;
				$val['PersonIdentState_id'] = 1;
				$val['Server_id'] = 0;
				// Бурятию -> в Уфимский формат :) 
				$map = array(
					'LastName' => 'FAM',
					'FirstName' => 'NAM',
					'FatherName' => 'FNAM',
					'Birthday' => 'BORN_DATE',
					'Series' => 'POL_SER',
					'Number' => 'POL_NUM_16',
					'IssueDate' => 'GIV_DATE', 
					'ValidThrough' => 'ELIMIN_DATE'
				);
				
				foreach ( $personData as $key => $value ) {
					switch ($key) {
						case 'ValidThrough': // обработка дат
							if ( mb_strlen($value) > 0 ) {
								$val['PersonIdentState_id'] = 3;
								$val['Server_id'] = $data['Server_id'];
							}
						case 'IssueDate':
						case 'Birthday':
							$d = mb_substr($value, 0, 10);
							$val[$map[$key]] = ConvertDateEx(mb_substr($value, 0, 10), "-", ".");
						break;

						case 'SMOCode': // код страховой компании, можем получить данные из БД
							if ( is_numeric($value) ) {
								// todo: по идее можно вынести метод getOrgSmoIdOnCode из карельского в базовый и использовать его
								$OrgSmo_id = $this->identmodel->getFirstResultFromQuery(
									'select top 1 OS.OrgSMO_id as OrgSmo_id from v_OrgSMO OS with (nolock) where Orgsmo_f002smocod = :orgSmoCode', 
									array('orgSmoCode' => $value)
								);
								if (!$OrgSmo_id) {
									$val['Alert_Msg'] = 'Не удалось определить идентификатор СМО по федеральному коду ('.$value.')';
								} else {
									$val['OrgSmo_id'] = $OrgSmo_id;
								}
							}
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
		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
