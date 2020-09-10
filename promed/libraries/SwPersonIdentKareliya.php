<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2010 Swan Ltd.
 * @author		Alexandr Chebukin
 * @link		http://swan.perm.ru/PromedWeb
 * @version		08.02.2013
 */

/**
 * Класс для работы с идентификацией людей (Карелия)
 *
 * @package		Library
 * @author		Alexandr Chebukin
 */

class SwPersonIdentKareliya {
	private $serviceURI;
	private $username_p;
	private $password_p;
	private $session_p;
	static $dbmodel = null;
	
	public function __construct($uri = null, $username_p = null, $password_p = null ) {
		$CI =& get_instance();
		$CI->load->model('PersonIdentRequest_model', 'PersonIdentRequest_model');
		self::$dbmodel = $CI->PersonIdentRequest_model;
		$this->serviceURI = $uri;
		$this->username_p = $username_p;
		$this->password_p = $password_p;
	}
	
	private function doPersonIdentAuth() {
	
		$this->session_p = rand(1000000000,9999999999);
		
		$url = "{$this->serviceURI}/www/w_maintain_users.login";
		$post = http_build_query(array(
			'username_p' => $this->username_p,
			'password_p' => $this->password_p,
			'module_p' => 'w_search_people.show_params',
			'session_p' => $this->session_p
		));
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST,1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		
		$res = curl_exec($ch);
		
		$result = array();
		if (!$res) {
			$result['errorMsg'] = 'Не удалось установить соединение с сервером идентификации';
			$result['success'] = false;
			return $result;
		} elseif (preg_match('#(Авторизация пользователя)#is', $res)) {
			$result['errorMsg'] = 'Ошибка авторизации на сервере идентификации';
			$result['success'] = false;
			return $result;
		} else {
			return true;
		}	
	}
	
	
	/**
	 *	Выполнение запроса на идентификацию
	 *	Входящие данные: массив с данными человека
	 */
	public function doPersonIdentRequest($requestData = array()) {
		
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
		
		$cookies = $this->doPersonIdentAuth();

		if ( $cookies !== true ) {
			return $cookies;
		}
		
		$url = "{$this->serviceURI}/www/w_search_people.show_main";
		
		if (!isset($requestData[0]['ACTUAL']) || $requestData[0]['ACTUAL'] !== false) {
			$post = http_build_query(array(
				'lname_p' => $requestData[0]['FAM'],
				'fname_p' => $requestData[0]['NAM'],
				'mname_p' => $requestData[0]['FNAM'],
				'birth_date_p' => empty($requestData[0]['D_BORN']) ? null : date('d.m.Y', strtotime($requestData[0]['D_BORN'])),
				'use_year_p' => 'YES',
				'rows_per_page_p' => 1,
				'actual_p' => 'YES'
				/*'series_p' => $requestData[0]['POL_SER'],
				'number_p' => $requestData[0]['POL_NUM'],
				'area_p' => $requestData[0]['AREA'],
				'street_p' => $requestData[0]['STREET'],
				'house_p' => $requestData[0]['HOUSE'],
				'flat_p' => $requestData[0]['ROOM']*/
			));
		} else {
			$post = http_build_query(array(
				'lname_p' => $requestData[0]['FAM'],
				'fname_p' => $requestData[0]['NAM'],
				'mname_p' => $requestData[0]['FNAM'],
				'birth_date_p' => empty($requestData[0]['D_BORN']) ? null : date('d.m.Y', strtotime($requestData[0]['D_BORN'])),
				'use_year_p' => 'YES',
				'rows_per_page_p' => 1,
				// 'actual_p' => 'YES'
				'series_p' => $requestData[0]['POL_SER'],
				'number_p' => $requestData[0]['POL_NUM'],
				/*'area_p' => $requestData[0]['AREA'],
				'street_p' => $requestData[0]['STREET'],
				'house_p' => $requestData[0]['HOUSE'],
				'flat_p' => $requestData[0]['ROOM']*/
			));
		}
		//print_r($post); exit;
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST,1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_COOKIE, "session={$this->session_p};user={$this->username_p}");

		$res = curl_exec($ch);
		
		if (!$res) {
			$result['errorMsg'] = 'Не удалось установить соединение с сервером идентификации';
			$result['success'] = false;
		} elseif (preg_match('#(Соответствие не найдено)#is', $res)) {
			$result['errorMsg'] = 'Соответствие не найдено';
			$result['errorCode'] = 1;
			$result['success'] = false;
		} else {
			$identData = $this->parsePersonIdentResponse($res);
			$result['identData'] = $identData;		
		}
		
		return $result;		
	}
	
	private function parsePersonIdentResponse($result) {
	
		ConvertFromWin1251ToUTF8($result);
		$doc = new DOMDocument();
		@$doc->loadHTML(mb_convert_encoding($result, 'HTML-ENTITIES', "UTF-8"));
		$xpath = new DomXPath($doc);
		$result = $xpath->query("/html/body/table[4]/tr[2]/td/table/tr[position()>1]");
		
		$res = array();
		
		foreach ($result as $entry) {
		
			$person = array();
			
			for ($node = $entry->firstChild; $node !== NULL; $node = $node->nextSibling) {
				$person[] = $node->nodeValue;
			}
			
			$person[6] = explode(chr(194).chr(160), $person[0]);
			$person[7] = explode(chr(194).chr(160), $person[2]);
			array_walk_recursive($person,'ConvertFromUTF8ToWin1251');
			$person[5] = self::$dbmodel->getOrgSmoIdByName($person[5]);
			
			$person = array(
				'FAM' => ucfirst(strtolower($person[6][0])),
				'NAM' => ucfirst(strtolower($person[6][1])),
				'FNAM' => (strtolower($person[6][2]) == 'нет' ? '' : ucfirst(strtolower($person[6][2]))),
				'BORN_DATE' => $person[1],
				'POL_SER' => $person[7][0],
				'POL_NUM_16' => $person[7][1],
				'OrgSmo_id' => $person[5],
				'GIV_DATE' => $person[3],
				'ELIMIN_DATE' => $person[4]
			);
			
			$res[] = $person;
		}
		
		return $res;
	}
	
}
