<?php	defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/REST_Controller.php');

class SwREST_Controller extends REST_Controller {
	/**
	 * @var string|null
	 */
	public $moduleName = null;

	/**
	 * @var array
	 */
	public $editedDataCollection = array();

	/**
	 * @var bool
	 */
	public $usePostgreLis = false;

	/**
	 * @var bool
	 * Флаг использования PostgreSQL вместо MSSQL для реестровых моделей
	 */
	public $usePostgreRegistry = false;

	/**
	 * SwREST_Controller constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->library('textlog', array('file' => 'RESTLog_'.date('Y-m-d').'.log'), 'restlog');

		if (
			(
				(!isset($_SESSION['pmuser_id']))
				|| (!is_numeric($_SESSION['pmuser_id']))
				|| ($_SESSION['pmuser_id'] <= 0)
				|| (!isset($_SESSION['login'])))
			&& !empty($this->_args['swtoken'])
		) {
			// пробуем атворизоваться по токену
			$user = null;
			if (in_array($this->moduleName, array('lis'))) {
				$this->authByTokenInCommon($this->_args['swtoken']);
			} else {
				$user = pmAuthUser::findByToken($this->_args['swtoken']);
			}

			if ($user) {
				if (is_array($user) && !empty($user['Error_Msg'])) {
					$this->response(array(
						'error_code' => 1,
						'error_msg' => $user['Error_Msg']
					), self::HTTP_UNAUTHORIZED);
				}
				$user->loginTheUser(4);

				// пишем токен авторизации в сессию
				$_SESSION['swtoken'] = $this->_args['swtoken'];

				// в процессе залогирования подключается дефолтная БД и при запуске заданий где нужна реестровая на реестровую уже не переключается, поэтому ансетим $this->db
				if (isset($this->db)) {
					unset($this->db);
				}
			}
		}

		// Берем данные пользователя из сессии в глобальную переменную $_USER
		if (isset($_SESSION['login'])) {
			global $_USER;
			$_USER = pmAuthUser::fromSession();
		}
	}

	/**
	 * @param string $swtoken
	 */
	protected function authByTokenInCommon($swtoken) {
		$this->load->swapi('common');

		$resp = $this->common->POST('User/authByToken', array(
			'swtoken' => $swtoken
		), 'single');

		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 1,
				'error_msg' => $resp['Error_Msg']
			), self::HTTP_UNAUTHORIZED);
		}

		session_reset();
	}

	/**
	 * Checks to see if we have everything we need to run this library.
	 *
	 * @access protected
	 * @return Exception
	 */
	public function preflight_checks() {
		parent::preflight_checks();

		$this->usePostgre = defined('USE_POSTGRESQL') && USE_POSTGRESQL;
		$this->usePostgreLis = defined('USE_POSTGRESQL_LIS') && USE_POSTGRESQL_LIS;

		if (!empty($this->router->moduleName)) {
			$this->moduleName = $this->router->moduleName;
		}

		if ($this->moduleName == 'lis') {
			$this->usePostgre = true;
		}
	}

	/**
	 * очистка комментария от звезд и слэшей
	 */
	function clearComment($comment) {

		$comment = str_replace('*', '', $comment);
		$comment = str_replace('/', '', $comment);

		return trim($comment);
	}

	/**
	 * Описание метода на основе входящих параметров и описания в DocBlock
	 */
	function describeMethod($data) {

		$reflector = new ReflectionClass($data['class_name']);
		$comment_data = explode('@desсription', $reflector->getMethod($data['function_name'])->getDocComment());

		$json_response = "";
		if (!empty($comment_data[1])) $json_response = (array)json_decode($this->clearComment($comment_data[1]));

		$output = ""; $method = array();

		// Название метода и тип ХТТП-запроса
		list($method['name'], $method['type']) = explode('_',$data['function_name']);

		if (!empty($data['retranslated_method'])) {
			list($method['name'], $method['type']) = explode('_',$data['retranslated_method']);
		}

		$method['type'] = strtoupper($method['type']);
		$method['model'] = $data['class_name'];

		$output .= '<h2 style="margin: 0;">'.$method['type'].' /api/'.$data['class_name'].'/'.$method['name']."</h2>";

		$method['description'] = $this->clearComment($comment_data[0]);
		$output .=  "<span style='font-size: 1em;'>".trim($method['description'])."</span>";

		$output .=  "<div style='font-size: 1em;'>";

		//входящие параметры
		$output .= "<p style='font-weight: bold;'>"."Входные параметры: "."</p><ul>";
		$params = $this->getParamsInfo($data['rules']);
		foreach ($params as $field => $val) {

			$method['input_params'][$field] = $val;

			if (strpos($val, "(required)") !== false) {
				$val = str_replace('(required)', '', $val);
				$output .= "<li style='font-weight: bold; list-style: none;'>".$field.' - '.$val."</li>";
			} else {
				$output .= "<li style='list-style: none;'>".$field." - ".$val."</li>";
			}

		}

		$output .= "</ul>";

		// выходные параметры
		if (!empty($json_response['output_params'])) {
			$output .= "<p style='font-weight: bold;'>"."Выходные параметры: "."</p><ul>";

			foreach ($json_response['output_params'] as $field => $val) {
				$method['output_params'][$field] = $val;
				if (is_array($val) && !empty($val)) {

					$output .= "<li style='list-style: none;'>".$field." [array]:<ul>";
					$sub_array = $val[0];

					foreach ($sub_array as $f => $v) {
						$output .= "<li style='list-style: none;'>".$f." - ".(isset($v) ? $v: "н/д")."</li>";
					}

					$output .= "</ul></li>";

				} else {
					$output .= "<li style='list-style: none;'>".$field." - ".(isset($val) ? $val: "н/д")."</li>";
				}

			}

			$output .= "</ul>";
		}

		//пример
		$method['example'] = array();
		$method['example']['path'] = "https://prm.promedweb.ru/api/" . $data['class_name'] . '/' . $method['name'] . '?sess_id=' . $this->_args['sess_id'];

		// покажем тут все параметры из правил со значениями (если забиты)
		foreach ($data['rules'] as $p) {
			if ($p['rules'] === 'required') {
				$method['example']['path'] .= "&" . $p['field'] . '=' . (isset($this->_args[$p['field']]) ? $this->_args[$p['field']] : '');
			} else {
				if (isset($this->_args[$p['field']])) {
					$method['example']['path'] .= "&" . $p['field'].'='.$this->_args[$p['field']];
				}
			}
		}

		$output .= "<p style='font-weight: bold;'>"."Пример: "."</p>";
		$output .= "<p>".$method['example']['path']."</p>";

		// успешный ответ
		if (!empty($json_response['example'])) {
			$output .= "<p><pre style='font-size: 12px;'>".json_encode($json_response['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."</pre></p>";
			$method['example']['data'] = $json_response['example'];
		}

		$output .=  "</div>";

		if (empty($data['describe_method']) || (!empty($data['describe_method']) && $data['describe_method'] == 1)) {
			$this->response(array('error_code' => 0,'method' => $method));
		} else if (!empty($data['describe_method']) && $data['describe_method'] == 2) {
			echo $output;
		}

		die();
	}

	/**
	 * Получить функцию, вызвавшую проверку параметров
	 */
	function getCalledFunction($index) {

		$trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 4);
		$caller = $trace[$index];

		return $caller['function'];
	}

	/**
	 * Обработка входящих параметров
	 */
	function ProcessInputData($Rules, $inData = null, $GetSessionParams = false, $CloseSession = true, $PreferSession = false, $convertUTF8 = true) {
		$data = array();

		// вызываем описание метода, и прерываем дальнейшее выполнение
		if (isset($this->_args['describe_method'])) {

			$params = array(
				'class_name' => get_called_class(),
				'function_name' => $this->getCalledFunction(2),
				'rules' => $this->inputRules[$Rules],
				'describe_method' => $this->_args['describe_method']
			);

			$request_method = $this->uri->segment(2);

			if (strpos(strtolower($params['function_name']), strtolower($request_method)) === false) {
				$params['retranslated_method'] = $this->getCalledFunction(3);
			}

			$this->describeMethod($params);
		}

		if (!$inData) {
			$inData = $this->_args;
		}

		// Если передан особый apiKey, то обязательность полей не проверяем
		if (!empty($inData['apiKey'])) {
			switch($inData['apiKey']) {
				case 'f91df0e5-2597-4747-a491-6ecdd1ef1d12':
					$GLOBALS['isSwanApiKey'] = true;
					break;
				case 'a71d74c8-abc9-400c-ad7e-aa5973c4fbe7':
					$GLOBALS['isMobileApiKey'] = true;
					break;
			}
		}

		// Получаем сессионные переменные
		If ( $GetSessionParams && (!$PreferSession)) {
			$data = array_merge($data, getSessionParams());
		}
		if ( isset($Rules) ) {
			// если параметр есть в $inData, то его надо привести к нужному регистру
			// приводим все входящие параметры к нижнему регистру
			$inDataLower = array();
			foreach($inData as $key => $value) {
				$inDataLower[mb_strtolower($key)] = $value;
			}
			$inData = array();
			// проверяем наличие входящих параметров в описании, если есть, то используем название с правильным регистром
			if (is_array($Rules)) {
				foreach ($Rules as $rule) {
					if (array_key_exists(mb_strtolower($rule['field']), $inDataLower)) {
						$inData[$rule['field']] = $inDataLower[mb_strtolower($rule['field'])];
					}
				}
			} else if (isset($this->inputRules[$Rules])) {
				foreach ($this->inputRules[$Rules] as $rule) {
					if (array_key_exists(mb_strtolower($rule['field']), $inDataLower)) {
						$inData[$rule['field']] = $inDataLower[mb_strtolower($rule['field'])];
					}
				}
			}

			if (empty($GLOBALS['isSwanApiKey'])) {
				if (is_array($Rules)) {
					foreach ($Rules as $rule) {

						$value = null;
						if (isset(array_merge($data, $inData)[$rule['field']])) {
							$value = array_merge($data, $inData)[$rule['field']];
						}

						if (isset($rule['rules']) && strpos($rule['rules'], 'required') !== false
							&& (
								// todo: почему проверка по empty? иногда необходимо прислать 0 чтобы создать объект!
								// чтобы ничего не поломать введу в rule новый вид проверки 'ZERO',
								// означающий что объект может принимать значения равные нулю
								(!in_array($rule['type'],array('api_flag_nc','api_flag','int')) && empty($value) && strpos($rule['rules'], 'zero') === false)
								||
								(in_array($rule['type'],array('api_flag_nc','api_flag','int')) && empty($value) && $value !== '0' && $value !== 0)
							)
						) {
							$this->response(array(
								'error_code' => 6,
								'error_msg' => "Отсутствует обязательный параметр '{$rule['field']}'"
							));
						}
					}
				} else if (isset($this->inputRules[$Rules])) {
					foreach ($this->inputRules[$Rules] as $rule) {

						$value = null;
						if (isset(array_merge($data, $inData)[$rule['field']])) {
							$value = array_merge($data, $inData)[$rule['field']];
						}

						if (isset($rule['rules']) && strpos($rule['rules'], 'required') !== false
							&& (
								(!in_array($rule['type'],array('api_flag_nc','api_flag','int')) && empty($value) && strpos($rule['rules'], 'zero') === false)
								||
								(in_array($rule['type'],array('api_flag_nc','api_flag','int')) && empty($value) && $value !== '0' && $value !== 0)
							)
						) {
							$this->response(array(
								'error_code' => 6,
								'error_msg' => "Отсутствует обязательный параметр '{$rule['field']}'"
							));
						}
					}
				}
			}

			if ( is_array($Rules) ) {
				$err = getInputParams($data, $Rules, $convertUTF8, $inData);
			}
			else {
				$err = getInputParams($data, $this->inputRules[ $Rules ], $convertUTF8, $inData);
			}

			if ( strlen($err) > 0 ) {
				$err = preg_replace("/\n$/uis", "", $err); // убираем не нужный \n в конце
				$err = preg_replace("/([\s\.]*)\n/uis", ". ", $err);
				$this->response(array(
					'error_code' => 6,
					'error_msg' => trim($err)
				));
			}
		}
		If ( $GetSessionParams && $PreferSession)
			$data = array_merge(getSessionParams(), $data);
		// Всегда переносим сессию в переменную
		// Сделано согласно http://redmine.swan.perm.ru/issues/4439#note-8. Соответственно в моделях используем $data['session'] вместо $_SESSION
		// Нафиг нам каждый раз полностью вся сессия? Особенно если не установлена переменная $GetSessionParams? Добавил чтобы сессия бралась только при заданном $GetSessionParams
		if ($GetSessionParams && isset($_SESSION)) {
			$data['session'] = $_SESSION;
			// TODO: Здесь нужно отсечь из переменной то, что никогда не будет использоваться далее
		}
		if ( $CloseSession )
			session_write_close();
		$this->InData = $data;
		return $data;
	}

	/**
	 * Получение обязательных полей
	 */
	function getRequiredFields($Rules) {
		$requiredFields = array(array(),array());

		if ( is_array($Rules) ) {
			foreach($Rules as $rule) {
				if (isset($rule['rules']) && strpos($rule['rules'], 'required') !== false) {
					$requiredFields[0][] = $rule['field'];
					$requiredFields[1][$rule['field']] = $rule['type'];
				}
			}
		}
		else if (isset($this->inputRules[ $Rules ])) {
			foreach($this->inputRules[ $Rules ] as $rule) {
				if (isset($rule['rules']) && strpos($rule['rules'], 'required') !== false) {
					$requiredFields[0][] = $rule['field'];
					$requiredFields[1][$rule['field']] = $rule['type'];
				}
			}
		}

		return $requiredFields;
	}

	/**
	 * Убираем либо непереданные, либо пустые обязательные параметры
	 */
	function unsetEmptyFields($data, $requiredFields = array(array(),array())) {
		foreach($data as $key => $value) {
			if (empty($value) && !array_key_exists($key, $this->_args)) {
				unset($data[$key]); // если не передан
			} 
			else if (
				empty($value) 
				&& (
					(in_array($key, $requiredFields[0]) && !in_array($requiredFields[1][$key], array('api_flag_nc','api_flag')))
					|| (in_array($key, $requiredFields[0]) && in_array($requiredFields[1][$key], array('api_flag_nc','api_flag')) && $value !== '0')
				)
			) {
				unset($data[$key]); // если передан пустой, но поле обязательно
			}
		}

		return $data;
	}

	/**
	 * Проверка авторизации
	 */
	function checkAuth($data = NULL) {

		$securityLevel = 'low';
		if (!empty($data) && !empty($data['securityLevel'])) $securityLevel = $data['securityLevel'];

		if (empty($_SESSION['login'])) {
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'Требуется авторизация'
			), self::HTTP_UNAUTHORIZED);
		}

		// если включена проверка по высокому уровню безопасности
		if ($securityLevel === 'high') {

			//echo '<pre>',print_r($_SESSION),'</pre>'; die();

			try {

				// убрал пока
				//if (!empty($_SESSION['OBSOLETE']) && (!empty($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time()))
				//	throw new Exception('Попытка использовать устаревшую сесиию.');

				if (!empty($_SESSION['IPaddress']) && $_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR'])
					throw new Exception('Подстановка IP-адреса сервера (возможна кража сессии).');

				if (!empty($_SESSION['userAgent']) && $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT'])
					throw new Exception('Подстановка User-Agent сервера (возможно кража сессии).');

			} catch(Exception $e){

				$this->response(array(
					'error_code' => 1,
					'error_msg' => $e->getMessage()
				));

			}
		}
	}

	/**
	 * @param $data
	 * @param $outFields
	 * @return array
	 */
	function filterOutFields($data, $outFields) {
		$tmp = array();
		foreach($outFields as $fieldName) {
			if (isset($data[$fieldName])) {
				$tmp[$fieldName] = $data[$fieldName];
			}
		}
		return $tmp;
	}

	/**
	 * @param $data
	 * @param $outFields
	 * @return array
	 */
	function filterOutFieldsInList($data, $outFields) {
		return array_map(function($row) use($outFields) {
			return $this->filterOutFields($row, $outFields);
		}, $data);
	}

	/**
	 * @param $inpParams
	 * @param $mapping
	 * @param array $listParams
	 * @return array
	 */
	function convertParams($inpParams, $mapping, $listParams = array()) {
		$params = array();
		if (count($listParams) == 0) {
			$listParams = array_keys($mapping);
		}
		foreach($listParams as $name_left) {
			if (array_key_exists($name_left, $inpParams) && array_key_exists($name_left, $mapping) /*&& !empty($inpParams[$name_left])*/) {
				if (is_array($mapping[$name_left])) {
					foreach($mapping[$name_left] as $name_right) {
						$params[$name_right] = $inpParams[$name_left];
					}
				} else {
					$name_right = $mapping[$name_left];
					$params[$name_right] = $inpParams[$name_left];
				}
			}
		}
		return $params;
	}

	/**
	 * Add the request to the log table
	 *
	 * @access protected
	 * @param bool $authorized TRUE the user is authorized; otherwise, FALSE
	 * @return bool TRUE the data was inserted; otherwise, FALSE
	 */
	protected function _log_request($authorized = FALSE)
	{
		$this->restlog->add('Запрос: ' . json_encode(array(
			'uri' => $this->uri->uri_string(),
			'method' => $this->request->method,
			'params' => $this->_args ? ($this->config->item('rest_logs_json_params') === TRUE ? json_encode($this->_args) : serialize($this->_args)) : NULL,
			'api_key' => isset($this->rest->key) ? $this->rest->key : '',
			'ip_address' => $this->input->ip_address(),
			'time' => time(),
			'authorized' => $authorized
		)));
	}

	/**
	 * Updates the log table with the total access time
	 *
	 * @access protected
	 * @author Chris Kacerguis
	 * @return bool TRUE log table updated; otherwise, FALSE
	 */
	protected function _log_access_time()
	{
		$rtime = $this->_end_rtime - $this->_start_rtime;
		$this->restlog->add('Время доступа: ' . $rtime);
	}

	/**
	 * Updates the log table with HTTP response code
	 *
	 * @access protected
	 * @author Justin Chen
	 * @param $http_code int HTTP status code
	 * @return bool TRUE log table updated; otherwise, FALSE
	 */
	protected function _log_response_code($http_code)
	{
		$this->restlog->add('Код ответа: ' . $http_code);
	}

	/**
	 * покажем описание входных параметров на основе inputRules
	 * для формирования документации по АПИ
	 */
	function getParamsInfo($inputRules, $arr_equal = NULL)
	{
		function compare_fields($a, $b) {
			return strcmp($a["field"], $b["field"]);
		}

		$res = array();
		if (!empty($inputRules)) {
			$params = $inputRules;

			//usort($params, 'compareByName');
			//usort($params, 'required_sort');

			$req = array();
			$normal = array();

			foreach ($params as $param) {
				$param['rules'] = strtolower(trim($param['rules']));
				$contain_required = strpos($param['rules'], 'required') !== FALSE;

				if ($contain_required) $req[] = $param;
				else $normal[] = $param;
			}

			usort($req, 'compare_fields');
			usort($normal, 'compare_fields');

			$params = array_merge($req, $normal);

			foreach ($params as $p) {
				$p = (object)$p;
				if (!empty($arr_equal)) {
					if (in_array($p->field , array_keys($arr_equal))) {
						$res[$p->field] = $p->label.(!empty($p->rules) && strpos($p->rules, 'required') !==false ? ' (required)' : '' );
					};
				} else {
					$res[$p->field] = $p->label.(!empty($p->rules) && strpos($p->rules, 'required') !==false ? ' (required)' : '' );
				}

			}
		}

		return $res;
	}

	/**
	 * покажем путь на основе входящих параметров
	 * для формирования документации по АПИ
	 */
	function getPathWithParams($params)
	{
		$excluded = array('describe_method', 'Accept-Encoding', 'Accept', 'Host', 'Connection', 'User-Agent', 'Cookie', 'Cache-Control');

		$this->load->helper('url');
		$url = 'https://prm.promedweb.ru/'.explode('?',current_url())[1];

		if (!empty($params)) {
			$url_p = "?";
			foreach ($params as $fName => $val) {
				if (!in_array($fName, $excluded))
				$url_p .= $fName.'='.(isset($val) ? $val."&" : "");
			}

			$url_p = rtrim($url_p,'&');
		};

		if (!empty($url_p)) $url .= $url_p;
		return $url;
	}

	/**
	 * мержим параметры по умолчанию, если они не указаны в входных параметрах
	 */
	function mergeDefaultParams($defParams)
	{
		if (!empty($defParams)) {
			foreach ($defParams as $arg => $val) { if (!isset($this->_args[$arg])) $this->_args[$arg] = $val; }
		}
	}

	/**
	 * получаем в ответ только указанные параметры
	 */
	function filter_response_item($item, $allowed_params, $rename_params = NULL)
	{
		if (!empty($item) && !empty($allowed_params)) {
			foreach ($item as $param => $value) {
				// убираем параметр если его нет в фильтре
				if (!in_array($param, $allowed_params)) unset($item[$param]);
				else {
					// переименовываем параметр, если есть массив с таким данными
					if (!empty($rename_params) && isset($rename_params[$param])) {
						$item[$rename_params[$param]] = $value;
						unset($item[$param]);
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Takes mixed data and optionally a status code, then creates the response
	 *
	 * @access public
	 * @param array|NULL $data Data to output to the user
	 * @param int|NULL $http_code HTTP status code
	 * @param bool $continue TRUE to flush the response to the client and continue
	 * running the script; otherwise, exit
	 */
	function response($data = NULL, $http_code = NULL, $continue = FALSE) {
		$this->load->model('Common_model');
		$this->Common_model->flushEditedDataCollection();

		parent::response($data, $http_code, $continue);
	}

	/**
	 * Тупо проверка на человека
	 * @return boolean
	 */
	public function checkPersonId($Person_id) {
		$this->load->model('Common_model');
		return $this->Common_model->getFirstResultFromQuery("select Person_id from v_PersonState (nolock) where Person_id = ? ", [$Person_id]);
	}

	/**
	 * Проверка правильности данных по справочникам
	 * @return boolean | string
	 */
	public function checkFieldsData($fields, $data) {
		$this->load->model('Common_model');
		$query = [];
		foreach($fields as $field) {
			if (!empty($data[$field])) {
				$object = explode('_', $field)[0];
				if(strpos($object, 'PersonSex') !== false) $object = 'Sex';
				if(strpos($object, 'Address') == 1) $object = 'Address'; // UAddress, PAddress и подобные
				$table = in_array($object, ['MedStaffFact', 'MedPersonal']) ? "v_{$object}" : $object;
				$query[] = " (select top 1 {$object}_id from {$table} (nolock) where {$object}_id = :{$field}) as {$field} ";
			}
		}
		
		if (!count($query)) return false;
		
		$result = $this->Common_model->getFirstRowFromQuery("select ".join(',', $query), $data);
		
		$err_fields = [];
		foreach($result as $key => $res) {
			if (empty($res)) {
				$err_fields[] = $key;
			}
		}
		
		if (count($err_fields)) return "Значения для ".join(',', $err_fields)." не найдены в справочниках системы";
		
		return false;
	}

	/**
	 * Проверка на дублирование
	 * @return boolean | string
	 */
	public function commonCheckDoubles($object, $fields, $data) {
		$this->load->model('Common_model');
		$params = [];
		$query = "select top 1 {$object}_id from v_{$object} (nolock) where (1 = 1) ";
		
		if (isset($data["{$object}_id"])) {
			$query .= " and {$object}_id != :key ";
			$params['key'] = $data["{$object}_id"];
		}
		
		foreach($fields as $field) {
			if (!empty($data[$field])) {
				$query .= " and {$field} = :{$field} ";
				$params[$field] = $data[$field];
			}
		}
		
		if ($object == 'EvnQueue') {
			$query .= " 
				and EvnQueue_recDT is null
				and EvnQueue_failDT is null
				and QueueFailCause_id is null
			";
		}
		
		$result = $this->Common_model->queryResult($query, $params);
		return count($result) > 0;
	}

	/**
	 * Функция обработки резульата запроса
	 * @param mixed $result
	 * @return boolean
	 */
	public function isSuccessful($result) {
		if (is_array($result) && !empty($result['Error_Msg'])) return false;
		return ( is_array($result)&&count($result)>0&& empty($result[0]['Error_Msg']) );
	}
}
