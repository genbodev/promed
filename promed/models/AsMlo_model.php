<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      AsMlo
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      06 2014
 */

class AsMlo_model extends swModel {
	public $test_mode = false; // включает режим обмена сообщениями в тестовом режиме
	public $MedServiceData = null;
	public $CurMedService_id = null;//#PROMEDWEB-10508
	
	//Массив кодов - описаний ошибок при обмене с АСМЛО
	public $asmloErrors = array(
		'402' => 'Задание по пробе не отправлено в АСМЛО'
		,'401' => 'Результат по пробе не получен от анализатора'
	);
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('textlog', array('file'=>'AsMlo_'.date('Y-m-d').'.log'), 'asmlolog');
	}

	/**
	 * Формирование сообщения об ошибке
	 * @param string $errorCode - Код ошибки от АСМЛО
	 * @param bool $is2way - Признак "Двусторонний анализатор" (true/false)
	 * @return string - Сообщение об ошибке
	 */
	function getErrorMessage($errorCode, $is2way) {
		if ($errorCode == '402' && !$is2way) return 'Результат по пробе не получен от анализатора';
		
		if (isset($this->asmloErrors[$errorCode])) {
			return $this->asmloErrors[$errorCode];
		} else {
			return 'Ошибка при взаимодействии с АСМЛО';
		}
	}
	
	/**
	 * Получение данных службы
	 */
	function getMedServiceData() {
			$MedServiceId = null;
			$MedServiceId = $this->CurMedService_id??$_SESSION['CurMedService_id'];
			// получаем данные службы
			if (!empty($MedServiceId)) {
				$resp = $this->queryResult("
					select
						MedService_id as \"MedService_id\",
						MedService_IsExternal as \"MedService_IsExternal\",
						MedService_WialonLogin as \"MedService_WialonLogin\",
						MedService_WialonPasswd as \"MedService_WialonPasswd\",
						MedService_WialonURL as \"MedService_WialonURL\",
						MedService_WialonPort as \"MedService_WialonPort\"
					from
						v_MedService ms with(nolock)
					where
						ms.MedService_id = :MedService_id
				", array(
					'MedService_id' => $MedServiceId
				));
			} else if (!empty($_SESSION['CurLpuSection_id'])) {
				// пункт забора в АРМ полки
				$resp = $this->queryResult("
					select top 1
						MedService_id as \"MedService_id\",
						MedService_IsExternal as \"MedService_IsExternal\",
						MedService_WialonLogin as \"MedService_WialonLogin\",
						MedService_WialonPasswd as \"MedService_WialonPasswd\",
						MedService_WialonURL as \"MedService_WialonURL\",
						MedService_WialonPort as \"MedService_WialonPort\"
					from
						v_LpuSection ls with(nolock)
						inner join v_MedService ms with(nolock) on ms.LpuSection_id = ls.LpuSection_id and ms.MedServiceType_id = 7
					where
						ls.LpuSection_id = :LpuSection_id
				", array(
					'LpuSection_id' => $_SESSION['CurLpuSection_id']
				));
			}

			if (!empty($resp[0])) {
				$this->MedServiceData = $resp[0];
			}

		return $this->MedServiceData;
	}
	
	/**
	 * Отправка запросов в сервис
	 */
	function asmlo_request($method, $request = null, $silent = false) {
		$to_service = array(
			'method' => $method
		);
		
		if (!empty($request)) {
			$to_service['request'] = $request;
		}
		
		if ($this->test_mode) {
			$to_service['test'] = true;
		}

		$url = !empty($_SESSION['asmlo_server'])?$_SESSION['asmlo_server']:$this->config->item('asmlo_server');

		$port = null;
		$msData = $this->getMedServiceData();
		if (!empty($msData['MedService_IsExternal']) && $msData['MedService_IsExternal'] == 2) {
			$url = $msData['MedService_WialonURL'];
			if (!empty($msData['MedService_WialonPort'])) {
				$port = $msData['MedService_WialonPort'];
			}
		}
		
		// отправляем в сервис
		if (!empty($_SESSION['asmlo_sessionCode'])) {
			$url.= "?sessionid={$_SESSION['asmlo_sessionCode']}";
		}

		$timeout = $this->config->item('asmlo_timeout');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		if (!empty($port)) {
			curl_setopt($ch, CURLOPT_PORT, $port);
		}
		// curl_setopt($ch, CURLOPT_PROXY, "http://192.168.36.200:808");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		));
		//log_message('error', "to_service:");
		//log_message('error', serialize($to_service));
		$POST = json_encode($to_service);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
		$response = curl_exec($ch);
		// var_dump($response);
		if (!$response) {
			if (!$silent) {
				DieWithError(curl_error($ch));
			} else {
				return array('Error_Msg' => curl_error($ch));
			}
		}

		$bom = pack('H*','EFBBBF');
		$response = preg_replace("/^$bom/", '', $response);

		$this->asmlolog->add('asmlo_request: method '.$method.', request: '.var_export($request, true).', response: '.var_export($response, true).'url: '.$url);
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>asmlo_request: method ' . $method . ', request: ' . var_export($request, true) . ', response: ' . var_export($response, true) . 'url: ' . $url . "</pre><br><br>";
		}

		if (!empty($response)) {
			$response = json_decode($response, true);
		}

		if (!empty($response['errorMessage'])) {
			return array(
				'Error_Msg' => $response['errorMessage'],
				'Error_Code' => $response['errorCode'] ?? 500
			);
		}

		// при отправке результата ошибка приходит внутри массива response, пусть так, обработаем тоже.
		if (!empty($response['response'][0]['errorMessage'])) {
			return array(
				'Error_Msg' => $response['response'][0]['errorMessage'],
				'Error_Code' => $response['response'][0]['errorCode'] ?? 500
			);
		}

		return $response;
	}
	
	/**
	 * Проверка, залогинен ли пользователь на удаленном сервисе (проверка на наличие открытой сессии)
	 *
	 * @return bool
	 */
	function isLogon() {
		$result = (!empty($_SESSION['asmlo_sessionCode']));
		$this->asmlolog->add('isLogon: Проверка залогинен ли пользователь  в АсМло: '.$result);
		return $result;
	}
	
	/**
	 * Идентификация в сервисе
	 */
	function login($data, $silent = false) {
		if (empty($data['login'])) {
			$data['login'] = !empty($_SESSION['asmlo_login'])?$_SESSION['asmlo_login']:$this->config->item('asmlo_login');
			$data['password'] = !empty($_SESSION['asmlo_password'])?$_SESSION['asmlo_password']:$this->config->item('asmlo_password');

			$msData = $this->getMedServiceData();
			if (!empty($msData['MedService_IsExternal']) && $msData['MedService_IsExternal'] == 2) {
				$data['login'] = $msData['MedService_WialonLogin'];
				$data['password'] = $msData['MedService_WialonPasswd'];
			}
		}
		
		$result = $this->asmlo_request(
			'login',
			array(
				'login' => $data['login'],
				'password' => $data['password']
			),
			$silent
		);
		
		if (!empty($result['response']['sessionCode'])) {
			$_SESSION['asmlo_sessionCode'] = $result['response']['sessionCode'];
		}
		
		return $result;
	}
	
	/**
	 * Завершение сессии в сервисе
	 */
	function logout($data) {
		$result = $this->asmlo_request(
			'logout'
		);

		if (!empty($_SESSION['asmlo_sessionCode'])) {
			unset($_SESSION['asmlo_sessionCode']);
		}
		
		return $result;
	}
	
	/**
	 * Проверка готовности работы сервиса
	 */
	function check($data) {
		$result = $this->asmlo_request(
			'check'
		);
		
		return $result;
	}

	/**
	 * Передача ГОСТ-2011 сервису
	 */
	function setDirectoryGost2011() {
		$query = "
			select
				uc.UslugaComplex_id as id,
				COALESCE(uc.UslugaComplex_Code,'') as code,
				COALESCE(uc.UslugaComplex_Name,'') as name,
				COALESCE(uc.UslugaComplex_SysNick,'') as mnemonic
			from
				v_UslugaComplex uc with(nolock)
				inner join v_UslugaCategory ucat with(nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where
				ucat.UslugaCategory_SysNick = 'gost2011'
				and exists(
					select top 1
						uca.UslugaComplexAttribute_id
					from
						v_UslugaComplexAttribute uca (nolock)
					where
						uca.UslugaComplexAttributeType_id = 8 -- только лабораторные
						and uca.UslugaComplex_id = uc.UslugaComplex_id
				)
			order by
				uc.UslugaComplex_Code
		";

		$result = $this->db->query($query);
		if (is_object($result)) {
			$resp = $result->result('array');
			$arrayToService = array();
			foreach($resp as $respone) {
				$arrayToService[] = $respone;
				// посылаем партиями по 100 штук
				if (count($arrayToService) >= 100) {
					$result = $this->setDirectory(array(
						'directory' => 'default',
						'records' => $arrayToService
					));
					$arrayToService = array();
				}
			}

			// последняя партия
			if (count($arrayToService) >= 0) {
				$result = $this->setDirectory(array(
					'directory' => 'default',
					'records' => $arrayToService
				));
				$arrayToService = array();
			}
		}

		return array('Error_Msg' => '');
	}


	/**
	 * Передача ЛПУ сервису
	 */
	function setDirectoryLpu() {
		$query = "
			select
				l.Lpu_id as id,
				l.Lpu_id as code,
				COALESCE(l.Lpu_Name,'') as name,
				COALESCE(l.Lpu_Nick,'') as mnemonic
			from
				v_Lpu l with(nolock)
		";

		$result = $this->db->query($query);
		if (is_object($result)) {
			$resp = $result->result('array');
			$arrayToService = array();
			foreach ($resp as $respone) {
				$arrayToService[] = $respone;
				// посылаем партиями по 100 штук
				if (count($arrayToService) >= 100) {
					$result = $this->setDirectory(array(
						'directory' => 'lpu',
						'records' => $arrayToService
					));
					$arrayToService = array();
				}
			}

			// последняя партия
			if (count($arrayToService) >= 0) {
				$result = $this->setDirectory(array(
					'directory' => 'lpu',
					'records' => $arrayToService
				));
				$arrayToService = array();
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Передача биоматериалов сервису
	 */
	function setDirectoryRefMaterial() {
		$query = "
			select
				l.RefMaterial_id as id,
				l.RefMaterial_Code as code,
				COALESCE(l.RefMaterial_Name,'') as name,
				COALESCE(l.RefMaterial_SysNick,'') as mnemonic
			from
				v_RefMaterial l with(nolock)
		";

		$result = $this->db->query($query);
		if (is_object($result)) {
			$resp = $result->result('array');
			$arrayToService = array();
			foreach ($resp as $respone) {
				$arrayToService[] = $respone;
				// посылаем партиями по 100 штук
				if (count($arrayToService) >= 100) {
					$result = $this->setDirectory(array(
						'directory' => 'refmaterial',
						'records' => $arrayToService
					));
					$arrayToService = array();
				}
			}

			// последняя партия
			if (count($arrayToService) >= 0) {
				$result = $this->setDirectory(array(
					'directory' => 'refmaterial',
					'records' => $arrayToService
				));
				$arrayToService = array();
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Передача справочника сервису
	 */
	function setDirectory($data) {
		if (!$this->isLogon()) {
			$result = $this->login($data);
			if (is_array($result) && !empty($result['Error_Msg'])) {
				return $result;
			}
		}

		$result = $this->asmlo_request(
			'setDirectory',
			array(
				'directory' => $data['directory'],
				'records' => $data['records']
			)
		);
		
		return $result;
	}
	
	/**
	 * Получение справочника из сервиса
	 */
	function getDirectory($data) {
		if (!$this->isLogon()) {
			$result = $this->login($data);
			if (is_array($result) && !empty($result['Error_Msg'])) {
				return $result;
			}
		}

		$result = $this->asmlo_request(
			'getDirectory',
			array(
				'directory' => $data['directory'],
				'filters' => $data['filters']
			)
		);
		
		return $result;
	}
	
	/**
	 * Передача проб в сервис
	 */
	function setSample($data, $silent = false) {
		$result = $this->asmlo_request(
			'setSample',
			array(
				array(
					'id' => $data['id'],
					'number' => $data['number'],
					'internalNum' => $data['internalNum'],
					'update' => $data['update'],
					'biomaterialId' => $data['biomaterialId'],
					'cito' => $data['cito'],
					'order' => array(
						'orderId' => $data['orderId'],
						'doctorId' => $data['doctorId'],
						'doctor' => $data['doctor'],
						'clinicId' => $data['clinicId'],
						'clinicName' => $data['clinicName'],
						'directionNum' => $data['directionNum']
					),
					'person' => array(
						'personId' => $data['personId'],
						'lastName' => $data['lastName'],
						'firstName' => $data['firstName'],
						'middleName' => $data['middleName'],
						'sex' => $data['sex'],
						'snils' => $data['snils'],
						'polisSer' => $data['polisSer'],
						'polisNum' => $data['polisNum'],
						'homeAddress' => $data['homeAddress'],
						'weight' => $data['weight'],
						'patOtdelen' => $data['patOtdelen'],
						'dateOfBirth' => $data['dateOfBirth']
					),
					'targets' => $data['targets'],
					'tests' => $data['tests']
				)
			),
			$silent
		);

		return $result;
	}
	
	/**
	 * Получение данных по пробе
	 */
	function getSampleInfo($data) {
		$request = array();
		
		if (!empty($data['id'])) {
			$request['id'] = $data['id'];
		}

		if (!empty($data['number'])) {
			$request['number'] = $data['number'];
		}
		
		if (!empty($data['archive'])) {
			$request['archive'] = $data['archive'];
		}
		
		if (!empty($data['ready'])) {
			$request['ready'] = $data['ready'];
		}
		
		if (!empty($data['raw'])) {
			$request['raw'] = $data['raw'];
		}
		
		$result = $this->asmlo_request(
			'getSampleInfo',
			$request
		);

		return $result;
	}

	/**
	 * ф-ция проверки значения
	 */
	public function nvl(&$var) {
		if (isset($var)) {
			return $var;
		} else {
			return null;
		}
	}

	/**
	 * Передача рабочих списков в сервис
	 */
	function setWorklist($data) {
		$result = $this->asmlo_request(
			'setWorklist',
			array(
				'id' => $data['id'],
				'lengthX' => $data['lengthX'],
				'lengthY' => $data['lengthY'],
				'worklist' => $data['worklist']
			)
		);
		
		return $result;
	}
	
	/**
	 * Получение данных по рабочему списку
	 */
	function getWorklistInfo($data) {
		$result = $this->asmlo_request(
			'getWorklistInfo',
			array(
				'id' => $data['id'],
				'archive' => $data['archive'],
				'ready' => $data['ready'],
				'raw' => $data['raw']
			)
		);
		
		return $result;
	}

	/**
	 * Проверка наличия результатов в пробах
	 */
	function checkEvnLabSampleHasResults($data) {
		if (!empty($data['EvnLabSample_ids']) && is_array($data['EvnLabSample_ids'])) {
			$resp_result = $this->queryResult("
				select top 1
					ut.UslugaTest_id as \"UslugaTest_id\"
				from
					v_UslugaTest ut with(nolock)
				where
					ut.EvnLabSample_id in ('".implode("','", $data['EvnLabSample_ids'])."')
					and ut.UslugaTest_ResultValue is not null
					and ut.UslugaTest_ResultValue <> ''
			");

			if (!empty($resp_result[0]['EvnUslugaPar_id'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Проверка дат проб
	 */
	function checkEvnLabSampleDates($data) {
		$resp = array(
			'EvnLabSample_ids' => array(),
			'EvnLabSample_Nums' => array()
		);
		if (!empty($data['EvnLabSample_ids']) && is_array($data['EvnLabSample_ids'])) {
			$resp_result = $this->queryResult("
				select top 1
					els.EvnLabSample_id as \"EvnLabSample_id\",
					els.EvnLabSample_Num as \"EvnLabSample_Num\"
				from
					v_EvnLabSample els with(nolock)
				where
					els.EvnLabSample_id in ('".implode("','", $data['EvnLabSample_ids'])."')
					and cast(els.EvnLabSample_setDT as date) < cast(dbo.tzgetdate()as date)
			");

			foreach($resp_result as $one_result) {
				$resp['EvnLabSample_ids'][] = $one_result['EvnLabSample_id'];
				$resp['EvnLabSample_Nums'][] = $one_result['EvnLabSample_Num'];
			}
		}

		return $resp;
	}
	
	/**
	 * Подтверждение сервису успешной передачи информации данных рабочего списка или пробы
	 */
	function setSuccessConfirmation($data) {
		$result = $this->asmlo_request(
			'setSuccessConfirmation',
			array(
				'samples' => $data['samples'],
				'worklists' => $data['worklists']
			)
		);
		
		return $result;
	}
	
	/**
	 * Перенос в архив пробы или рабочего списка
	 */
	function moveArchive($data) {
		$result = $this->asmlo_request(
			'moveArchive',
			array(
				'samples' => $data['samples'],
				'worklists' => $data['worklists']
			)
		);
		
		return $result;
	}
	
	/**
	 * Получает список проб по выбранным заявкам для отправки в АсМло
	 */
	function getLabSamplesForEvnLabRequests($data)
	{
		$arrayId = array();
		
		if(!empty($data['CurMedService_id'])){//#PROMEDWEB-10508
			$this->CurMedService_id = $data['CurMedService_id'];
		}
		
		if (!empty($data['EvnLabRequests'])) {
			$data['EvnLabRequests'] = json_decode($data['EvnLabRequests']);
			if (!empty($data['EvnLabRequests'])) {
				// достаём пробы заявки
				$query = "
					select
						EvnLabSample_id as \"EvnLabSample_id\"
					from
						v_EvnLabSample with(nolock)
					where
						EvnLabRequest_id IN ('".implode("','", $data['EvnLabRequests'])."')
				";
				
				$result = $this->db->query($query);
				
				$data['EvnLabSamples'] = array();
				if (is_object($result)) {
					$resp = $result->result('array');
					foreach($resp as $respone) {
						$arrayId[] = $respone['EvnLabSample_id'];
					}
				}
			}
		}
		
		return $arrayId;
	}

	/**
	 * Формирование признака доступности кнопки "Отправить на анализатор"
	 */
	function isSend2AnalyzerEnabled($data)
	{
		$params = array('MedService_id'=>$data['MedService_id']);
		if (!empty($data['EvnLabSamples'])) {
			$params['EvnLabSamples'] = json_decode($data['EvnLabSamples']);
			$filter = "";
			if ( count($params['EvnLabSamples']) > 0 ) {
				$filter = " AND s.EvnLabSample_id IN (".implode(',', $params['EvnLabSamples']).") ";
			}
			$query = "
			with x1 as (
				SELECT
					COUNT(1) AS Count2wayOnService
				FROM
					lis.v_Analyzer a with(nolock)
				WHERE
					a.MedService_id = :MedService_id AND
					a.Analyzer_2wayComm = 2
			), x2 as (
				SELECT
					COUNT(1) AS CountNo2wayOnSamples
				FROM
					v_EvnLabSample s with(nolock)
					LEFT JOIN lis.v_Analyzer a with(nolock) ON a.Analyzer_id = s.Analyzer_id
				WHERE
					(s.Analyzer_id IS NULL OR a.Analyzer_2wayComm = 1)
				" . $filter . "
			)
			
			select
				Count2wayOnService as \"Count2wayOnService\",
				CountNo2wayOnSamples as \"CountNo2wayOnSamples\"
			from x1, x2	
			";
		}
		$result = array();
		$res = $this->db->query($query, $params);
		if (is_object($res))
		{
			$result = $res->result('array');
		}
		return $result;
	}

	/**
	 * Посчитаем количество анализаторов с установленными признаками 
	 * «Возможность двусторонней связи» и «использования двусторонней связи»
	 * для анализаторов соответствующих выбранным заявкам/заявке 
	 */
	function get2wayAnalyzersForEvnLabRequests($data)
	{
		$innerData = array();
		if (!empty($data['EvnLabRequests'])) {
			$innerData['EvnLabRequests'] = json_decode($data['EvnLabRequests']);
		} else if (!empty($data['EvnLabRequest_id'])) {
			$innerData['EvnLabRequests'] = array($data['EvnLabRequest_id']);
		}

		if (!empty($innerData['EvnLabRequests'])) {
			// выбираем заявки для которых есть анализаторы с двусторонней связью
			$query = "
			with m as (
				SELECT 
					COUNT(1) as Count2way
				FROM v_EvnLabRequest r with(nolock)
					LEFT JOIN v_EvnLabSample s with(nolock) ON r.EvnLabRequest_id = s.EvnLabRequest_id
					LEFT JOIN lis.v_Analyzer a with(nolock) ON a.MedService_id = r.MedService_id OR a.MedService_id = s.MedService_id
				WHERE 
					r.EvnLabRequest_id IN (" . implode(',', $innerData['EvnLabRequests']) . ")
					AND a.Analyzer_2wayComm = 2
			)
			
			select Count2way as \"Count2way\" from m
			";
			
			$result = array();
			$res = $this->db->query($query);
			if (is_object($res))
			{
				$result = $res->result('array');
			}
			return $result;
		}
		return array();
	}

	/**
	 * выполняет setSuccessConfirmation для заданных проб
	 */
	function doSetSuccessConfirmation() {
		// получаем необходимый набор проб
		return false;

		set_time_limit(0);
		$query = "
			select
				els.EvnLabSample_id as \"EvnLabSample_id\"
			from
				lis.v_Link l with(nolock)
				inner join v_EvnLabSample els with(nolock) on els.EvnLabSample_id = l.object_id
			where
				l.object_id = l.lis_id
				and els.EvnLabSample_setDate < '2015-01-01'
				and els.LabSampleStatus_id <> 2
		";

		$result = $this->db->query($query);
		if (is_object($result)) {
			$resp = $result->result('array');
			$samples = array();
			foreach($resp as $respone) {
				$samples[] = array('id' => $respone['EvnLabSample_id']);
				if (count($samples) > 100) {
					$response = $this->setSuccessConfirmation(array(
						'samples' => $samples,
						'worklists' => array()
					));
					//var_dump($response);
					$samples = array();
				}
			}

			if (count($samples) > 0) {
				$response = $this->setSuccessConfirmation(array(
					'samples' => $samples,
					'worklists' => array()
				));
				//var_dump($response);
			}
		}

		return array('Error_Msg' => '');
	}
	
	/**
	 * Отправляет одну запись в АсМло и возврашает ответ по одной записи
	 * @param $data
	 * @throws Exception
	 */
	function createRequest2($data, $silent = false){
		
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$response = $this->EvnLabSample_model->getRequest2DataForAsMlo($data);
		/*if (empty($response['RefMaterial_id'])) { // не определенно исследование в АсМло
			return array('Error_Msg' => 'Невозможно определить биоматериал в пробе. Проверьте наличие в пробе биоматериала.');
		}*/
		if (empty($response['Analyzer_id'])) { // не задан анализатор
			return array('Error_Msg' => 'Для отправки пробы на анлизатор необходимо указать анализатор в пробе.');
		}

		$array2way = array();
		if (!empty($response['EvnLabRequest_id'])) {
			//проверяем наличие анализаторов с двусторонней связью для данных заявок (EvnLabRequest_id)
			$array2way = $this->get2wayAnalyzersForEvnLabRequests( $response );
		}
		
		//отправка заявки в АСМЛО выполняется только для служб "Лаборатория":
		/*if ($data['MedServiceType_SysNick'] != 'lab') {
			return;
			//return array('Error_Msg' => 'Заявка не отправлена! Это не лаборатория');
		}*/
		
		if (empty($response['EvnLabSample_DelivDT'])) {
			// проставляем дату доставки пробы
			$this->EvnLabSample_model->setDelivDT($data);
		}
		
		$tests = array();
		if (empty($response['tests'])) { // не определены тесты в АсМло
			// считаем что если нет ни одного теста, то заказаны все тесты входящие в исследование
			//DieWithError('<b>Невозможно связать состав исследования с тестами в АсМло.</b><br/>Проверьте наличие правильных тестов в отправляемой заявке.');
		} else {
			// отсекаем лишнюю запятую и раскладываем ответ в массив (хотя в принципе данные можно получать отдельным запросом сразу в массив: это на подумать)
			$tests = $response['tests'];
		}

		$targets = array();
		if (!empty($response['targets'])) {
			$targets = $response['targets'];
		}

		if (!empty($array2way[0]) && $array2way[0]['Count2way'] > 0) {
			//Отправка Заявки только если на службе имеются анализаторы с двусторонней связью
			
			if (!$this->isLogon()) {
				$result = $this->login($data, $silent);
				if (is_array($result) && !empty($result['Error_Msg'])) {
					return $result;
				}
			}

			if (!$this->isLogon()) {
				return array('Error_Msg' => 'Ошибка авторизации в сервисе');
			}

			if (in_array(getRegionNick(), array('buryatiya', 'ekb', 'perm', 'kz'))) {
				$response['EvnLabSample_Num'] = $response['EvnLabSample_BarCode']; // в Екб хотят в поле number получить именно штрих-код, а не номер пробы
			}

			$result = $this->setSample(array(
				'id' => $response['EvnLabSample_id'],
				'number' => $response['EvnLabSample_Num'],
				'update' => true,
				'internalNum' => $response['EvnLabSample_BarCode'],
				'biomaterialId' => getRegionNick() == 'kz' ? $response['RefMaterial_Code'] : $response['RefMaterial_id'],
				'cito' => $response['EvnLabRequest_IsCito'] == 2 ? 1 : 0,
				'orderId' => $response['EvnLabRequest_id'],
				'clinicId' => $response['Lpu_id'],
				'clinicName' => $response['Lpu_Nick'],
				'directionNum' => $response['EvnDirection_Num'],
				'doctorId' => $response['MedPersonal_id'],
				'doctor' => $response['MedPersonal_Fio'],
				'patOtdelen' => $response['LpuSection_Name'],
				'weight' => $response['PersonWeight_Weight'],
				'personId' => ean13_check_digit(str_pad($response['Person_id'], 12, '0', STR_PAD_LEFT)),
				'lastName' => $response['Person_SurName'],
				'firstName' => $response['Person_FirName'],
				'middleName' => $response['Person_SecName'],
				'homeAddress' => $response['Address_Address'],
				'sex' => $response['Sex_id'],
				'snils' => $response['Person_Snils'],
				'polisSer' => $response['Polis_Ser'],
				'polisNum' => $response['Polis_Num'],
				'dateOfBirth' => $response['Person_BirthDay'],
				'tests' => $tests,
				'targets' => $targets
			), $silent);
			
			if (is_array($result) && !empty($result['Error_Msg'])) {
				return $result;
			}
		}

		$this->saveLink('EvnLabSample', $data['EvnLabSample_id'], $data['EvnLabSample_id'], $data); // связываем саму с собой что ли.. чтобы понять что отправлена.
		//проба переходит в статус "В работе":
		$this->EvnLabSample_model->ReCacheLabSampleStatus(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));
		
		// кэшируем статус проб в заявке
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $response['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		return array('Error_Msg' => '');
	}
	
	/**
	 * Сохраняет связь между записью ПромедВеб и записью в АсМло
	 * @param $object_name (String) Наименование объекта
	 * @param $in_id (Numeric) Идентификатор объекта в Промед
	 * @param $out_id (Numeric) Идентификатор объекта в АсМло
	 * $data Array Массив данных
	 */
	function saveLink($object_name, $in_id, $out_id, $data) {
		// todo: предварительно надо проверять, возможно запись уже существует

		$procedure = 'lis.p_Link_ins';
		$query = "
			declare
				@ResId bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @ResId = null;
			exec " . $procedure . "
				@Link_id = @ResId output,
				@link_object = :object_name,
				@object_id = :in_id,
				@lis_id = :out_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ResId as EvnDie_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'object_name' => $object_name,
			'in_id' => $in_id,
			'out_id' => $out_id,
			'pmUser_id' => $data['pmUser_id'],
		);

		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {
			return $response[0];
		} else {
			// todo: Тут надо сохранять информацию о невозможности сохранить в лог
			return false;
		}
	}
	
	/**
	 * Cохраняет результаты полученные с анализатора в EvnLabSample
	 */
	function getResultSamples($data, $sample = null){
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->load->model('TestStat_model', 'TestStat_model');
		
		$isUseAutoReg = array(); //массив "код анализатора" => "Использование автоучета"

		// Алгоритм работы функции проверки результата по определенному тесту
		// Одним запросом получаем и идентификатор заявки в АсМло и перечень тестов с кодами
		$tests = $this->getSampleTests($data);
		$lis_id = null;
		if (count($tests)>0) {
			// Выбираем идентификатор заявки в АсМло
			$lis_id = $tests[0]['lis_id'];
		}
		// формируем массив
		$linktests = array();
		$testRefs = array();
		$arrTestsDate = array();//массив дат выполнения тестов
		foreach ($tests as $test) {
			$testDateTimestamp = (is_object($test['UslugaTest_setDT']) ? $test['UslugaTest_setDT']->getTimestamp() : 0);
			if (!isset( $arrTestsDate[$test['test_code']] )) {
				$arrTestsDate[$test['test_code']] = array();
			}
			if (!isset( $arrTestsDate[$test['test_code']][$test['UslugaTest_id']] )) {
				$arrTestsDate[$test['test_code']][$test['UslugaTest_id']] = $testDateTimestamp;
			}

			$linktests[$test['test_code']][] = array(
				'EvnLabSample_NumFull' => $test['EvnLabSample_NumFull'],
				'UslugaComplex_id' => $test['UslugaComplex_id'],
				'UslugaTest_id' => $test['UslugaTest_id'],
				'UslugaTest_ResultLower' => $test['UslugaTest_ResultLower'],
				'UslugaTest_ResultUpper' => $test['UslugaTest_ResultUpper'],
				'UslugaTest_ResultValue' => $test['UslugaTest_ResultValue'],
				'UslugaTest_setDT' => $test['UslugaTest_setDT'],
				'UslugaTest_ResultUnit' => $test['UslugaTest_ResultUnit']
			);
			if ( !array_key_exists($test['test_code'], $testRefs) ) {
				$testRefs[$test['test_code']] = array();
				$testRefs[$test['test_code']]['UslugaTest_ResultLower'] = false;
				$testRefs[$test['test_code']]['UslugaTest_ResultUpper'] = false;
				$testRefs[$test['test_code']]['UslugaTest_ResultUnit'] = false;
			}
			$testRefs[$test['test_code']]['UslugaTest_ResultLower'] |= !empty($test['UslugaTest_ResultLower']);
			$testRefs[$test['test_code']]['UslugaTest_ResultUpper'] |= !empty($test['UslugaTest_ResultUpper']);
			$testRefs[$test['test_code']]['UslugaTest_ResultUnit'] |= !empty($test['UslugaTest_ResultUnit']);
		}
		
		$sampleNum = (isset($tests[0]) && isset($tests[0]['EvnLabSample_Num']))?$tests[0]['EvnLabSample_Num']:'[не определен]';
		$data['EvnLabSample_id'] = (isset($tests[0]) && isset($tests[0]['EvnLabSample_id']))?$tests[0]['EvnLabSample_id']:$data['EvnLabSample_id']; // если искали пробу по номеру пробы, то наш ид может оказаться другим.
		$sampleNumFull = (isset($tests[0]) && isset($tests[0]['EvnLabSample_NumFull']))?$tests[0]['EvnLabSample_NumFull']:'';
		if (getRegionNick() == 'ekb') {
			$sampleNumFull = (isset($tests[0]) && isset($tests[0]['EvnLabSample_BarCode']))?$tests[0]['EvnLabSample_BarCode']:''; // в Екб хотят в поле number получить именно штрих-код, а не номер пробы
		}
		
		// Получаем из АсМло по идентификатору заявки список проб
		$this->asmlolog->add('у пробы записан в комментах lis_id:'.$lis_id);
		if ($lis_id) {
			if (!$this->isLogon()) {
				$result = $this->login($data);
				if (is_array($result) && !empty($result['Error_Msg'])) {
					return $result;
				}
			}
			
			if (!$this->isLogon()) {
				return array('Error_Msg' => 'Ошибка авторизации в сервисе');
			}
			
			if (empty($sample)) {//Устарело! - $sample всегда не пустой (передается параметром)
				$this->asmlolog->add('Получаем результат по пробе с ID = '.$data['EvnLabSample_id']);
				$result = $this->getSampleInfo(array(
					'id' => $data['EvnLabSample_id']
					,'number' => $sampleNumFull
				));

				if (!empty($result['Error_Msg'])) {
					$query ="
							SELECT top 1
								COALESCE(a.Analyzer_2wayComm, 1) as anz,
								lnk.Link_id as \"Link_id\"
							FROM v_EvnLabSample els with(nolock)
								inner join lis.v_Analyzer a with(nolock) on a.Analyzer_id = els.Analyzer_id
								left join lis.v_Link lnk with(nolock) on lnk.object_id = els.EvnLabSample_id and lnk.link_Object = 'EvnLabSample'
							WHERE els.EvnLabSample_id = :EvnLabSample_id
					";
					$result_analyzer = $this->db->query($query, array('EvnLabSample_id'=>$data['EvnLabSample_id']));
					if(is_object($result_analyzer)) {
						$response_a = $result_analyzer->result('array');
						if(!empty($response_a[0]['anz'])) {
							$aN = $response_a[0]['anz'];
							$linkId = $response_a[0]['Link_id'];
							if (($aN == 1) or ($aN == 2 and !is_null($linkId))) {
								return array('Error_Msg' => "Результат по пробе № " . $sampleNum . " не получен от анализатора", 'Error_Code' => '');
							}
						}
					}
					return array('Error_Msg' => $result['Error_Msg']);
				}

				$sample = $result['response'];
			}

			if (empty($sample['id']) && !empty($sample[0]['id'])) {
				$sample = $sample[0]; // если проба от метода getSampleInfo вернулась не объектом, а массивом, то достаём из него пробу
			}

			// если есть расчетные тесты
			if (!empty($sample['id'])) {
				$sample = $this->getFormulaSample($sample, true);
			}

			// Разбираем ответ от АсМло TODO

			// признак наличия результата
			$countResult = 0;
			if (!empty($sample['targets'])) {
				foreach ($sample['targets'] as $target) {
					if (!empty($target['protocol'])) {
						$resp_eup = $this->queryResult("
							select
								eup.EvnUslugaPar_id as \"EvnUslugaPar_id\"
							from
								v_EvnUslugaPar eup with(nolock)
								inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = eup.UslugaComplex_id
							where
								uc.UslugaComplex_Code = :UslugaComplex_Code
								and eup.EvnLabSample_id = :EvnLabSample_id
						", array(
							'EvnLabSample_id' => $data['EvnLabSample_id'],
							'UslugaComplex_Code' => $target['code']
						));

						if (!empty($resp_eup[0]['EvnUslugaPar_id'])) {
							$this->load->model('EvnMediaFiles_model');
							$this->EvnMediaFiles_model->addEvnMediaDataFromAPI(array(
								'Evn_id' => $resp_eup[0]['EvnUslugaPar_id'],
								'EvnMediaData_FileName' => 'protocol.pdf',
								'File' => $target['protocol'],
								'session' => $data['session'],
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}
				}
			}
			if (!empty($sample['tests'])) {
				foreach ($sample['tests'] as $work) {
					if (isset($linktests[$work['code']])) { // если из АсМло вернулся такой же тест, как есть у нас в Промеде
						
						$flTestIsDuplicate = false;
						$flTestIsUpdated = false;//Флаг "Тест обновлён"
						foreach($linktests[$work['code']] as $test) {
							if (empty($work['timestamp'])) {
								//на расчетных тестах возникала ошибка (не было даты)
								$work['timestamp'] = date('Y-m-d H:i:s');
							}
							
							if (
								isset($work['value']) &&
								($work['value'] == $test['UslugaTest_ResultValue']) &&
								(strtotime($work['timestamp']) == (is_object($test['UslugaTest_setDT']) ? $test['UslugaTest_setDT']->getTimestamp() : 0)) &&
								($sample['number'] == $test['EvnLabSample_NumFull'])
							) {
								$flTestIsDuplicate = true;
							}
							
							$testRefsArr = array();
							if (isset( $testRefs[ $work['code'] ] )) {
								$testRefsArr = $testRefs[ $work['code'] ];
							}
							// значение теста
							if (!isset($work['value'])) {
								$work['value'] = null;
							} else {
								// если приходит число, то сохраняем как число несмотря на наличие первых нулей
								if (is_numeric($work['value'])) {
									$work['value'] = 0 + $work['value'];
								}
							}
							$this->asmlolog->add('UslugaTest_id-' . $test['UslugaTest_id']);
							$this->asmlolog->add('UslugaComplex_id-' . $test['UslugaComplex_id']);
							if (!empty($test['UslugaTest_id'])) {
								if (isset($work['value']) && strlen($work['value']) > 0) {
									$countResult++;

									$withRefs = false;
									// обновляем UslugaTest
									$UslugaTest_ResultLower = null;
									$UslugaTest_ResultUpper = null;
									if (empty($testRefsArr['UslugaTest_ResultUpper']) && empty($testRefsArr['UslugaTest_ResultLower'])) {
										//Если реф значения по тесту не заполнены
										if ( !empty($work['lower']) || !empty($work['upper']) ) {
											// и поступило хотя бы одно реф-значение
											$withRefs = true;
											$UslugaTest_ResultLower = $work['lower'];
											$UslugaTest_ResultUpper = $work['upper'];
										}
									} else {
										$UslugaTest_ResultLower = $test['UslugaTest_ResultLower'];
										$UslugaTest_ResultUpper = $test['UslugaTest_ResultUpper'];
									}
									
									$UslugaTest_ResultUnit = null;
									if (!empty($work['unit']) && empty($testRefsArr['UslugaTest_ResultUnit'])) {
										$withRefs = true;
										$UslugaTest_ResultUnit = $work['unit'];
									} else {
										$UslugaTest_ResultUnit = $test['UslugaTest_ResultUnit'];
									}
									
									$UslugaTest_Comment = null;
									if (!empty($work['comment'])) {
										$UslugaTest_Comment = $work['comment'];
									}

									$UslugaTest_ResultValue = $work['value'];
									$UslugaTest_setDT = null;
									if ($this->getRegionNick() == 'ufa') {
										if ($UslugaTest_ResultValue[0] == '=') {
											$UslugaTest_ResultValue = substr_replace($UslugaTest_ResultValue, '', 0, 1);
										}
									}
									if (!empty($UslugaTest_ResultValue)) {
										$UslugaTest_setDT = $work['timestamp'];
									}
									
									// обновляем UslugaTest
									if ($withRefs) {
										$dataForUpdate = array(
											'disableRecache' => true,
											'UslugaTest_id' => $test['UslugaTest_id'],
											'UslugaComplex_id' => $test['UslugaComplex_id'],
											'UslugaTest_ResultValue' => $UslugaTest_ResultValue,
											'UslugaTest_setDT' => $UslugaTest_setDT,
											'RefValues_id' => null,
											'UslugaTest_ResultLower' => $UslugaTest_ResultLower,
											'UslugaTest_ResultUpper' => $UslugaTest_ResultUpper,
											'UslugaTest_ResultUnit' => $UslugaTest_ResultUnit,
											'UslugaTest_Comment' => $UslugaTest_Comment,
											'updateType' => 'fromLISwithRefValues',
											'session' => $data['session'],
											'pmUser_id' => $data['pmUser_id']
										);
									} else {
										$dataForUpdate = array(
											'disableRecache' => true,
											'UslugaTest_id' => $test['UslugaTest_id'],
											'UslugaComplex_id' => $test['UslugaComplex_id'],
											'UslugaTest_ResultValue' => $UslugaTest_ResultValue,
											'UslugaTest_setDT' => $UslugaTest_setDT,
											'UslugaTest_Comment' => $UslugaTest_Comment,
											'updateType' => 'fromLIS',
											'session' => $data['session'],
											'pmUser_id' => $data['pmUser_id']
										);
									}
									
									//log_message('error', "Массив дат тестов:");
									//log_message('error', print_r($arrTestsDate, true));
									$testDateTimestamp = strtotime($work['timestamp']);
									
									if (!isset( $arrTestsDate[$work['code']] )) {
										$arrTestsDate[$work['code']] = array();
									}
									if (!isset( $arrTestsDate[$work['code']][$test['UslugaTest_id']] ) ||
										$arrTestsDate[$work['code']][$test['UslugaTest_id']] < $testDateTimestamp
									) {
										$arrTestsDate[$work['code']][$test['UslugaTest_id']] = $testDateTimestamp;
										//апдейтим значение теста
										$this->EvnLabSample_model->updateResult($dataForUpdate);
										$flTestIsUpdated = true;
									}
									
								}
							}
						}
						
						//Сохранение статистики принятых тестов
						if (isset($work['analyzerCode']) && !empty($work['analyzerCode'])) {
							//Проверяем признак "Использование автоучета" для анализатора
							if (!isset($isUseAutoReg[$work['analyzerCode']])) {
								$isUseAutoReg[$work['analyzerCode']] = $this->getFirstResultFromQuery('
									SELECT top 1
										COALESCE(Analyzer_IsUseAutoReg, 1) 
									FROM lis.v_Analyzer with(nolock)
									WHERE Analyzer_Code = :Analyzer_Code
									',
									array( 'Analyzer_Code' => $work['analyzerCode'] )
								);
							}
							
							if ($isUseAutoReg[$work['analyzerCode']] == 2) {
								//Пишем в статистику +1, если новое значение
								if ((!$flTestIsDuplicate) &&
									!empty($work['issended']) && $work['issended'] <> 2 && ($work['issended'] <> 3 || ( $flTestIsUpdated && $work['issended'] == 3 )) &&
									//isset($work['analyzerCode']) && !empty($work['analyzerCode']) &&
									isset($work['timestamp']) && !empty($work['timestamp']) &&
									isset($work['code']) && !empty($work['code'])
								) {
									$this->load->model('TestStat_model');
									$this->TestStat_model->setLabCode(substr($work['analyzerCode'], 0, 4));
									$this->TestStat_model->setAnalyzerCode($work['analyzerCode']);
									$this->TestStat_model->setTestDate($work['timestamp']);
									$this->TestStat_model->setTestCode($work['code']);
									$this->TestStat_model->saveTestStat();
								}
							}
						}
						
					}
				}
			}
			
			//массив значений для апдейта пробы
			$setEvnLabSample = [];
			//массив параметров для апдейта пробы
			$paramsEvnLabSample = [];
			if (!empty($data['EvnLabSample_id'])) {
				$paramsEvnLabSample = array('EvnLabSample_id' => $data['EvnLabSample_id']);
			}
			//Если получили от анализатора "Брак пробы":
			if ( !empty($sample['defectCauseId']) && (strval(intval($sample['defectCauseId'])) === $sample['defectCauseId']) ) {//проверка на целое число
				$defectCauseId = $this->getFirstResultFromQuery('
					SELECT top 1
						DefectCauseType_id as "DefectCauseType_id"
					FROM lis.v_DefectCauseType with(nolock)
					WHERE DefectCauseType_id = :DefectCauseType_id
					',
					array( 'DefectCauseType_id' => $sample['defectCauseId'] )
				);
				if (empty( $defectCauseId )) {
					$this->asmlolog->add('По пробе №'.$sampleNum.' получен "Брак пробы" - "'. $sample['defectCauseId'] .'" (не существующий тип брака пробы)');
				} else {
					$setEvnLabSample[] = '[DefectCauseType_id] = :DefectCauseType_id';
					$paramsEvnLabSample['DefectCauseType_id'] = $sample['defectCauseId'];
					$this->asmlolog->add('Получили "Брак пробы" - Тип="'. $sample['defectCauseId'] .'"');
				}
			}
			
			//Если получен комментарий пробы
			if ( !empty($sample['comment']) ) {
				$setEvnLabSample[] = '[EvnLabSample_Comment] = :EvnLabSample_Comment';
				$paramsEvnLabSample['EvnLabSample_Comment'] = $sample['comment'];
			}
			
			//Получена Дата в пробе
			if ( !empty($sample['timestamp']) ) {
				$setEvnLabSample[] = '[EvnLabSample_AnalyzerDate] = :EvnLabSample_AnalyzerDate';
				$paramsEvnLabSample['EvnLabSample_AnalyzerDate'] = $sample['timestamp'];
			}
			
			//Флаг "Нет результатов" - не пришло ни одного результата из АсМло и нет брака пробы
			$flagNoResults = ($countResult < 1) && empty($sample['defectCauseId']);
			if (!$flagNoResults) {//есть результаты
				// апдейтим дату в пробе EvnLabSample_StudyDT
				$setEvnLabSample[] = '[EvnLabSample_StudyDT] = dbo.tzGetDate()';
			}

			//Апдейт если есть что апдейтить в пробе
			if (!empty($setEvnLabSample)) {
				$sql = "
					UPDATE
						EvnLabSample with(rowlock)
					SET
						" . join(',', $setEvnLabSample) . "
					WHERE
						EvnLabSample_id = :EvnLabSample_id
				";
				$this->db->query($sql, $paramsEvnLabSample);
			}
			//sql_log_message('error','UPDATE-EvnLabSample-query: ',getDebugSql($sql, $paramsEvnLabSample));
			
			if ($flagNoResults) {//нет результатов
				$this->asmlolog->add('результаты не пришли');
				return array('Error_Msg' => 'Результаты по пробе №'.$sampleNum.' в АсМло не заполнены', 'Error_Code'=>'');
			}
			$this->asmlolog->add('результаты пришли ('.$countResult.' штук), обновляем дату в пробе, кэшируем статус');
			
			$this->EvnLabSample_model->ReCacheLabSampleIsOutNorm(array(
				'EvnLabSample_id' => $data['EvnLabSample_id']
			));
			$this->EvnLabSample_model->ReCacheLabSampleStatus(array(
				'EvnLabSample_id' => $data['EvnLabSample_id']
			));

			$data['EvnLabRequest_id'] = $this->getFirstResultFromQuery("
				select top 1
					EvnLabRequest_id as \"EvnLabRequest_id\"
				from
					v_EvnLabSample with(nolock)
				where
					EvnLabSample_id = :EvnLabSample_id
			", array(
				'EvnLabSample_id' => $data['EvnLabSample_id']
			));
			
			if (!empty($data['EvnLabRequest_id'])) {
				// кэшируем статус заявки
				$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
					'EvnLabRequest_id' => $data['EvnLabRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				// кэшируем статус проб в заявке
				$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
					'EvnLabRequest_id' => $data['EvnLabRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			// создаём/обновляем протокол
			$this->EvnLabSample_model->ReCacheLabRequestByLabSample(array(
				'EvnLabSample_id' => $data['EvnLabSample_id'],
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));

			$this->asmlolog->add('обновили дату, закэшировали статус');

			return array('Error_Msg' => '');

		} else {
			return array('Error_Msg' => 'Выбранная проба №'.$sampleNum.' еще не была отправлена', 'Error_Code'=>'');
		}
	}

	/**
	 * Производит расчет формул
	 * @param  [array] $data - параметры из getSampleTests
	 * @return [array] $data - с учетом расчета по формуле
	 */
	function getFormulaSample($data, $pushed = false)
	{

		$params = array('EvnLabSample_id' => $data['id']);
		$query = "
			with UslugaList as (
				select
                	UslugaComplex_id
				FROM v_EvnUslugaPar up with(nolock)
				WHERE up.EvnLabSample_id = :EvnLabSample_id
			), myvars as (
            	select top 1
                	:EvnLabSample_id as EvnLabSample_id,
                    Analyzer_id 
                from v_EvnLabSample with(nolock)
                where EvnLabSample_id = :EvnLabSample_id
            )

			SELECT DISTINCT
				atfa.Usluga_id as \"Usluga_id\", 
				(select EvnLabSample_id from myvars with(nolock)) as \"EvnLabSample_id\",
				atf.Analyzer_id as \"Analyzer_id\", 
				uc.UslugaComplex_id as \"UslugaComplex_id\", 
				uc.UslugaComplex_Name as \"UslugaComplex_Name\", 
				atfa.AnalyzerTest_id as \"Formula_id\", 
				atf.AnalyzerTestFormula_Code as \"code\", 
				atf.AnalyzerTestFormula_Formula as \"Formula\"
			FROM v_UslugaComplex uc with(nolock)
				INNER JOIN lis.AnalyzerTestFormulaArguments atfa with(nolock) on atfa.Usluga_id = uc.UslugaComplex_id
				INNER JOIN lis.AnalyzerTestFormula atf with(nolock) on atf.AnalyzerTest_id = atfa.AnalyzerTest_id
					and atf.Analyzer_id = (select Analyzer_id from myvars with(nolock))
			WHERE uc.UslugaComplex_2011id in (select UslugaComplex_id from UslugaList with(nolock))
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if(is_object($result))
		{
		    $response = $result->result('array');
		} else {
		    return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		// Собираем формулы
		$temp = array();
		array_walk($response, function($v, $k) use($data, &$temp, $pushed){
			array_walk($data['tests'], function($va, $ke) use($v, $k, &$temp, $pushed){
				if($v['code'] === $va['code']) {
					$temp[$k]['code'] = $v['code'];
					$temp[$k]['Formula_id'] = $v['Formula_id'];
					$temp[$k]['Formula'] = $v['Formula'];
				} else if($pushed) {
					$temp[$k]['code'] = $v['code'];
					$temp[$k]['Formula_id'] = $v['Formula_id'];
					$temp[$k]['Formula'] = $v['Formula'];
					$temp[$k]['is_formula'] = true;
				}
			});
		});

		// Меняем коды на значения
    	$dataTemp = array_map(function(&$v) use($data){
    		preg_match_all("/\{(.*?)\}/", $v['Formula'], $tests);
    		array_map(function($va) use ($tests, &$v) {
    			array_map(function($test) use($va, &$v) {
    				if($va['code'] === $test) {
    					$value = str_replace(',','.',$va['value']);
    					$v['value'][$va['code']] = $value;
    				}
    			}, $tests[1]);
    		}, $data['tests']);

    		return $v;
    	}, $temp);

    	// Фильтруем
    	if(count($dataTemp) > 0) {
    		array_walk($dataTemp, function(&$v, $k) use(&$dataTemp){
    			if(isset($v['value'])) {
	    			array_walk($v['value'], function($va, $ke) use($k, &$dataTemp){
	    				if(!is_numeric($va)) {
	    					unset($dataTemp[$k]);
	    				}
	    			});
    			}
    		});
    	}

		// Производим расчет
		if(count($dataTemp) > 0) {
			array_walk($dataTemp, function(&$v, $k) use(&$dataTemp, &$data) {
		    	$replace = preg_replace_callback("/\{(.*?)\}/", function($matches) use (&$v, $k) {
		    	    return $v['value'][$matches[1]];
		    	}, $v['Formula']);

	    		// проверка деления, умножения на ноль
		    	if(preg_match("/\*0|\/0/", $replace) && !preg_match("/\/0*[.]|\*0*[.]/", $replace))
		    	{
		    		unset($dataTemp[$k]);
		    	} else {
			    	// строгая проверка строки
			    	preg_match_all("/([+,\-,*,\/,^,(,)]|sqrt|[0-9]*)/", $replace, $get);

					$last = count($get[1])-1; unset($get[1][$last]);

	    			$valid = array_map(function($v){
		    			if(
		    				$v == '+' || $v == '-' || $v == '*' || $v == '/' || 
		    				$v == '^' || $v == '(' || $v == ')' || $v == 'sqrt'
		    			  )
		    			{
		    				return true;		
		    			} else {
		    				return floatval($v) > 0 ? true : false;
		    			}
	    			}, $get[1]);

		    		// проверка на последний символ
		    		$x = end($get[1]);
		    		if(
		    			$x == '+' || $x == '-' || $x == '*' || $x == '/' || 
		    			$x == '^' || $x == '(' || $x == ')' || $x == 'sqrt'
		    		  )
		    		{
		    			$valid = false;
		    		}

		    		if($valid) {
		    			// снова проверяем строку перед выполнением
		    			if(preg_match("/\*\*|\+\+|\/\/|\-\-/", $replace)) {
		    				unset($dataTemp[$k]);
		    			} else {
		    				$formula = eval('return '.$replace.';');

		    				if(is_float($formula)) {
		    					$x = (string)$formula;
		    					$dot = explode('.', $x);
		    					if(isset($dot[1])) {
		    						$formula = strlen($dot[1] >= 10) ? round($formula, 0, PHP_ROUND_HALF_UP) : $formula;		    						
		    					}
		    				}

		    				$v['value'] = $formula;
		    			}
		    		} else {
		    			unset($dataTemp[$k]);
		    		}
	 	    	}
	 		});
	 	}

	 	// Возвращаем массив с расчетами по формулам
		if(count($dataTemp) > 0) {
			array_walk($data['tests'], function(&$v) use($dataTemp, $pushed){
				array_walk($dataTemp, function($va) use(&$v, $pushed){
					if($v['code'] === $va['code']) {
						$v['value'] = $va['value'];
						$v['is_formula'] = true;
					}
				});
			});

			$data['tests'] = $pushed ? array_merge($data['tests'], $dataTemp) : $data['tests'];
		}

		return $data;
	}
	
	/**
	 * Получает список тестов по пробе
	 */
	function getSampleTests($data)
	{
		$params = array('EvnLabSample_id' => $data['EvnLabSample_id']);
		$filter = "ls.EvnLabSample_id = :EvnLabSample_id";

		if (!empty($data['EvnLabSample_Num']) && !is_numeric($data['EvnLabSample_id'])) { // если пришёл номер и не числовой id, ищем по номеру пробы
			$params = array('EvnLabSample_Num' => $data['EvnLabSample_Num']);
			$filter = "ls.EvnLabSample_Num = :EvnLabSample_Num";
		}

		$query = "
			Select
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				ls.EvnLabSample_id as \"EvnLabSample_id\",
				case when len(EvnLabSample_Num)=12 then substring(EvnLabSample_Num, (len(EvnLabSample_Num)-3), 4) else EvnLabSample_Num end as \"EvnLabSample_Num\", --номер пробы для вывода сообщения
				ls.EvnLabSample_Num as \"EvnLabSample_NumFull\",
				ls.EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				uc2011.UslugaComplex_Code as \"test_code\",
				link.lis_id as \"lis_id\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_setDT as \"UslugaTest_setDT\"
			from v_EvnLabSample ls with(nolock)
				inner join v_UslugaTest ut with(nolock) ON ut.EvnLabSample_id = ls.EvnLabSample_id
				inner join v_UslugaComplex uc with(nolock) ON uc.UslugaComplex_id = ut.UslugaComplex_id
				left join v_UslugaComplex uc2011 with(nolock) on uc.UslugaComplex_2011id = uc2011.UslugaComplex_id
				outer apply (
					Select top 1
						link.lis_id
					from
						lis.v_link link with(nolock)
					where
						object_id = ls.EvnLabSample_id
						and link_object = 'EvnLabSample'
					order by
						link_id desc -- выбираем последний сохраненный в АсМло, как наиболее правильный
				) link
			WHERE
				{$filter}
		";
		$result = array();
		$res = $this->db->query($query, $params);

		//echo getDebugSQL($query, $params);
		if (is_object($res))
		{
			$result = $res->result('array');
		}
		return $result;
	}

	/**
	 * Получение результатов из АсМло
	 */
	function checkAsMloLabSamples($data)
	{
		$this->asmlolog->add('checkAsMloLabSamples: Запуск');

		// 1. Авторизация в АС МЛО
		$result = $this->login($data);
		if (is_array($result) && !empty($result['Error_Msg'])) {
			return $result;
		}

		// 2. Отправка в АС МЛО запроса getSampleInfo с параметром ready: true (для возврата всех готовых проб).
		$result = $this->getSampleInfo(array(
			'ready' => true
		));

		if (is_array($result) && !empty($result['Error_Msg'])) {
			return $result;
		}

		// 3. Сохранение результатов полученных проб.
		$samples = array();

		foreach($result['response'] as $onesample) {
			$result = $this->getResultSamples(array(
				'EvnLabSample_id' => $onesample['id'],
				'EvnLabSample_Num' => $onesample['number'],
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			), $onesample);

			$samples[] = $onesample['id'];

		}

		// 4. Подтверждение результатов запросом setSuccessConfirmation.
		$this->setSuccessConfirmation(array(
			'samples' => $samples,
			'worklists' => array()
		));

		$this->asmlolog->add('checkAsMloLabSamples: Конец');
		return array('Error_Msg' => '');
	}

	/**
	 * Отправка проб в АС МЛО
	 */
	function createRequestSelections($data, $arrayId) {
		if (empty($data['onlyNew'])) {
			// проверяем есть ли у проб результаты
			$check = $this->checkEvnLabSampleHasResults(array(
				'EvnLabSample_ids' => $arrayId
			));

			if ($check) {
				return array('Alert_Msg' => 'Отправить на анализатор только новые тесты?', 'Alert_Code' => 100, 'Error_Msg' => '');
			}
		}

		$check = $this->checkEvnLabSampleDates(array(
			'EvnLabSample_ids' => $arrayId
		));
		if (!empty($check['EvnLabSample_ids'])) {
			if (empty($data['changeNumber'])) {
				return array('Alert_Msg' => 'Дата отправки проб №' . implode(', ', $check['EvnLabSample_Nums']) . ' на анализатор отличается от даты взятия проб. Пересчитать штрих-код этих проб?', 'Alert_Code' => 101, 'Error_Msg' => '');
			} else if ($data['changeNumber'] == 2) {
				$sysMsg = 'Штрих-код проб №' . implode(', ', $check['EvnLabSample_Nums']) . '  пересчитан в соответствии с текущей датой. Не забудьте заменить штрих-код на пробирках.';
				// пересчитываем штрих-коды проб
				$this->load->model('EvnLabSample_model');
				foreach($check['EvnLabSample_ids'] as $EvnLabSample_id) {
					$lrdata = $this->EvnLabSample_model->getDataFromEvnLabRequest(array(
						'EvnLabSample_id' => $EvnLabSample_id
					));
					$resp_num = $this->EvnLabSample_model->getNewLabSampleNum(array(
						'Lpu_id' => $lrdata['Lpu_id'],
						'MedService_id' => $lrdata['MedService_id']
					));

					if (is_array($resp_num)) {
						return $resp_num;
					}

					$time = $this->getFirstResultFromQuery("
						select
							left(convert(varchar(20), dbo.tzgetdate(), 108), 5) 
							+ ' ' + convert(varchar(20), dbo.tzgetdate(), 104)
						");
					$this->db->query("
						UPDATE EvnLabSample with(rowlock)
						SET
							EvnLabSample_Num = :EvnLabSample_Num,
							EvnLabSample_BarCode = :EvnLabSample_BarCode
						WHERE Evn_id = :EvnLabSample_id;
						
						update evn with (rowlock)
						set
							Evn_setDT = cast(:Evn_setDT as datetime)
						where Evn_id = :EvnLabSample_id
					", array(
						'EvnLabSample_Num' => $resp_num,
						'EvnLabSample_BarCode' => $resp_num,
						'EvnLabSample_id' => $EvnLabSample_id,
						'Evn_setDT' => $time
					));
				}
			}
		}

		$response = array('Error_Msg' => '');

		foreach($arrayId as $id) {
			$data['EvnLabSample_id'] = $id;
			$response = $this->createRequest2($data);
		}

		if (count($arrayId) == 1 && !empty($resp_num) && is_array($response)) {
			$response['EvnLabSample_ShortNum'] = substr($resp_num, -4);
			$response['EvnLabSample_BarCode'] = $resp_num;
			if (!empty($time)) {
				$response['EvnLabSample_setDT'] = $time;
			}
		}
		if (is_array($response) && !empty($sysMsg)) {
			$response['sysMsg'] = $sysMsg;
		}

		return $response;
	}
}