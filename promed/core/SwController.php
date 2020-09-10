<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * @copyright Copyright (c) 2009-2011, Swan Ltd.
 * @link http://swan.perm.ru/PromedWeb
 *
 * @class swController 
 * Библиотека, рассширяющая возможности стандартного контроллера. 
 * Включает в себя проверку того, что пользователь залогинен и методы для обработки данных возвращаемых моделью и вывода их пользователю.
 *
 * @package Library
 * @access public
 *
 * @author Petukhov Ivan aka Lich (megatherion@list.ru)
 * @author Markoff A.A. <markov@swan.perm.ru>
 *
 * @version 22.11.2010
 */
/**
 * @property mixed inputRules
 * @property mixed outputRules
 */
class SwController extends CI_Controller {
	/**
	* @var boolean
	* Нужна ли проверка на логин. По умолчанию: true.
	*/
	var $NeedCheckLogin = true;
	/**
	* @var boolean
	* Не использовать кэширование. По умолчанию: true.
	*
	*/
	var $NoCache = true;
	/**
	* @var array
	* Массив входных данных. 
	*/
	protected $InData = NULL;
	/**
	* @var array
	* Массив выходных данных. 
	*/
	protected $OutData = NULL;
	
	/**
	 * @var boolean
	 * Отмена стандартного обработчика ошибок, при выставлении в true добавляет в возвращаемый JSON параметр Cancel_Error_Handle = true
	 */
	var $Cancel_Error_Handle = false;
	
	/**
	 * Модель по умолчанию, привязанная к контроллеру
	 */
	var $default_model = null;
	
	/**
	 * Объект модели
	 */
	var $dbmodel = null;

	/**
	 * @var bool
	 * Флаг использования PostgreSQL вместо MSSQL для основной подсистемы
	 */
	var $usePostgre = false;

	/**
	 * @var bool
	 * Флаг использования микросервиса ЛИС на PostgreSQL вместо MSSQL
	 */
	var $usePostgreLis = false;
	
	/**
	 * @var bool
	 * Флаг использования PostgreSQL вместо MSSQL для реестровых моделей
	 */
	var $usePostgreRegistry = false;
	
	/**
	 * Constructor
	 *
	 * 1. Проверяем использование кэша.
	 * 2. Если нужна проверка на логин, то проверяем, залогинен ли пользователь
	 *
	 * @access	public
	 */
	function __construct() {
		
		parent::__construct();
		
		if (ENVIRONMENT == 'testing') {
			if ($this->NoCache) $this->output->set_header("Pragma: no-cache");
		} else {
			if ($this->NoCache) header("Pragma: no-cache");
		}

		if ($this->input->method() === 'options') {
			exit; // отбрасываем OPTIONS запрос, т.к. ему нужны только заголовки.
		}

		$this->usePostgre = defined('USE_POSTGRESQL') && USE_POSTGRESQL;
		$this->usePostgreLis = defined('USE_POSTGRESQL_LIS') && USE_POSTGRESQL_LIS;
		$this->usePostgreRegistry = defined('USE_POSTGRESQL_REGISTRY') && USE_POSTGRESQL_REGISTRY;

		// блокируем медленный фунционал
		if (!empty($this->config->config['blockSlowDownFunctions'])) {
			$params = $this->input->get();
			if (isset($params[$this->config->config['controller_trigger']])) {
				$class = mb_strtolower($params[$this->config->config['controller_trigger']]);
				$method = 'index';
				if (isset($params[$this->config->config['function_trigger']])) {
					$method = mb_strtolower($params[$this->config->config['function_trigger']]);
				}

				$error = 'Функционал временно недоступен в связи с техническим сбоем. Приносим свои извинения за предоставленные неудобства.';
				if (!empty($this->config->config['blockSlowDownFunctionsTime'])) {
					$error .= ' Приблизительное время устранения проблемы ' . $this->config->config['blockSlowDownFunctionsTime'] . ' минут.';
				}

				if (!empty($this->config->config['blockedSlowDownFunctions'])) {
					$blocked = $this->config->config['blockedSlowDownFunctions'];
					if (is_array($blocked) && array_key_exists($class, $blocked) && (empty($blocked[$class]) || (is_array($blocked[$class]) && in_array($method, $blocked[$class])))) {
						DieWithError($error);
					}
				}

				if ($class == 'search' && !empty($this->config->config['blockedSlowDownSearchFormTypes']) && !empty($_REQUEST['SearchFormType'])) {
					$blocked = $this->config->config['blockedSlowDownSearchFormTypes'];
					if (is_array($blocked) && in_array(mb_strtolower($_REQUEST['SearchFormType']), $blocked)) {
						DieWithError($error);
					}
				}
			}
		}

		if (((!isset($_SESSION['pmuser_id'])) || (!is_numeric($_SESSION['pmuser_id'])) || ($_SESSION['pmuser_id'] <= 0) || (!isset($_SESSION['login']))) && !empty($_REQUEST['swtoken'])) {
			// пробуем атворизоваться по токену
			$user = pmAuthUser::findByToken($_REQUEST['swtoken']);
			if ($user) {
				if (is_array($user) && !empty($user['Error_Msg'])) {
					die(json_encode($user));
				}
				$user->loginTheUser(4);

				// в процессе залогирования подключается дефолтная БД и при запуске заданий где нужна реестровая на реестровую уже не переключается, поэтому ансетим $this->db
				if (isset($this->db)) {
					unset($this->db);
				}
			}
		}

		if (!empty($_REQUEST['se_code']) && !empty($_REQUEST['se_id'])) {
			// пробуем атворизоваться по системной ошибке
			$this->load->model('Common_model');
			$info = $this->Common_model->getSystemErrorInfo($_REQUEST['se_code'], $_REQUEST['se_id']);

			if (!empty($info['SystemError_TechInfo'])) {
				$_SESSION['SystemError_TechInfo'] = json_decode($info['SystemError_TechInfo'], true);
			}
			// надо запихать в сессию данные об ошибке
			if (!empty($info) && !empty($info['SystemError_Login'])) {
				$login = $info['SystemError_Login'];
				$user = pmAuthUser::find($login);
				if ($user) {
					if (is_array($user) && !empty($user['Error_Msg'])) {
						die(json_encode($user));
					}
					$user->loginTheUser(4);
				}
			}
		}

		$IsLocalSMP = $this->config->item('IsLocalSMP');
		if ($this->NeedCheckLogin) {
			if ($IsLocalSMP === true) {
				// авторизация не нужна
				$session_local = $this->config->item('LocalSMP_Session');
				if (!empty($session_local)) {
					foreach ($session_local as $key => $value) {
						$_SESSION[$key] = $value;
					}
				}
			}
			checkLogin();
		}

		// Берем данные пользователя из сессии в глобальную переменную $_USER
		// Сессия должна умереть
		if (isset($_SESSION['login'])) {
			global $_USER;
			$_USER = pmAuthUser::fromSession();
		}

		if ($IsLocalSMP !== true && !empty($_REQUEST['formHash']) && !empty($_REQUEST['formClass'])) {
			$newHash = getFormHash($_REQUEST['formClass']);
			if ($_REQUEST['formHash'] != $newHash) {
				echo json_encode(array('success' => false, 'Error_Msg' => 'Необходимо обновить форму', 'Error_Code' => 901));
				die();
			}
		}
	}
	
	/**
	 * Магический метод для перенаправления запросов
	 */
	function _remap($method) {
		$params = $this->input->get();
		if ( isset($params[$this->config->config['controller_trigger']]) ) {
			$class = $params[$this->config->config['controller_trigger']];
		} else {
			$class = null;
		}
		if ( isset($params[$this->config->config['function_trigger']]) ) {
			$method = $params[$this->config->config['function_trigger']];
		} else {
			$method = 'index';
		}
		
		if ( defined('CRON') && $params == false && count($this->uri->segments) ) {
			$class = $this->uri->segments[0];
			if ( count($this->uri->segments) == 2) {
				$method = $this->uri->segments[1];
			} else {
				$method = 'index';
			}
		}

		if (
			in_array(strtolower($method), array_map('strtolower', get_class_methods($this)))
			&& !in_array(strtolower($method), array_map('strtolower', get_class_methods('swController'))) // методы swController'а нельзя вызывать
		) {
			try {
				call_user_func(array($this, $method));
			} catch ( Exception $e ) {
				$this->ReturnError($e->getMessage(), 666); // Выдаём необработанную в методе ошибку
			}
		} else {
			if ( !(isset($this->inputRules) && isset($this->inputRules[$method]) ) ) {
				show_404("{$class}/{$method}");
				return false;
			}
			
			$data = $this->ProcessInputData($method, true);
			if ( $data === false ) {
				return false;
			}

			if ( !isset($this->db) ) {
				$this->load->database();
			}
			
			if (empty($this->dbmodel) ) {
				if ( !empty($this->default_model) ) {
					$this->load->model($this->default_model, 'dbmodel');
				} else {
					show_error("Не задана модель по умочанию для контроллера");
				}
			}

			if ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($this->dbmodel)))) {
				show_error("В модели " . $this->default_model . " не найден вызываемый метод " . $method);
			}
			$response = $this->dbmodel->$method($data);
			if ($response !== false ) {
				if (substr($method, 0, 3) == 'get' || substr($method, 0, 4) == 'load') {
					$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
				} else {
					$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();
				}
			}
		}
	}
	
	
	/**
	 * Проверяет права на действие (метод) согласно матрице прав текущего пользователя
	 * Возвращает признак разрешения действия
	 *
	 * @access public
	 * @param string $action Названия действия, которое выполняет данный метод
	 * @param string $object Названия объекта (таблицы), надо которой выполняется данный метод. Если не передано, то берется из контроллера ($this->baseobject)
	 *
	 * @return swController Возвращает объект текущего класса.
	 * // todo: Пока это заглушка
	 */
	function IsAllowed($action, $object='') {
		// Таблица (объект) для которой выполняется данный метод
		if ($object=='') {
			//??? бессмыслено, закомментировал $object = $this->baseobject;
		}
		// Проверка по матрице прав $data['roles'] согласно таблицам и объектам (и возможно методам)
		//$object
		// Если выбранное действие запрещено для данного объекта данной сгруппированной роли
		// то возвращаем false (или можно генерировать ошибку и прерывать выполнение на этом)
		$this->OutData = array(
			'success' => false,
			'Error_Msg' => toUtf('Доступ запрещен!')
		);
		// иначе возвращаем true
		return false;
	}

	/**
	 * Обрабатывает входящие данные, проверяет их на ошибки
	 *
	 * @access public
	 * @param mixed $Rules Наименование правила для проверки метода или массив с правилами.
	 * @param boolean $GetSessionParams По умолчанию: true. Установите false, если данные из сессии не требуется включать в входящие параметры.
	 * @param boolean $CloseSession По умолчанию: false. Установите true в случае, если нужно закрыть сессию после обработки входящих параметров (поскольку в большинстве случаев сессия не нужна).
	 * @param bool $PreferSession В первую очередь брать параметры из сессии
	 * @param bool $ParamsFromPost Брать параметры из $_POST
	 * @param bool $convertUTF8 Конвертировать входящие параметры из UTF 8
	 *
	 * @return array Обработанный массив входящих параметров
	 *
	 */
	function ProcessInputData($Rules, $GetSessionParams = true, $CloseSession = true, $PreferSession = false, $ParamsFromPost = false, $convertUTF8 = true, $jsonOutput = true) {
		$data = array();
		
		// Заменяем $_POST на $_GET, если $_POST пустой.
		if(!$ParamsFromPost){
			if(empty($_POST)&&(!empty($_GET))){
				$_POST = $_GET;
			}
		}
		// Получаем сессионные переменные
		If ( $GetSessionParams && (!$PreferSession)) {
			$data = array_merge($data, getSessionParams());
		}
		if ( isset($Rules) ) {
			if ( is_array($Rules) ) {
				$err = getInputParams($data, $Rules, $convertUTF8);
			}
			else {
				$err = getInputParams($data, $this->inputRules[ $Rules ], $convertUTF8);
			}
			if ( strlen($err) > 0 ) {
				if ($jsonOutput) {
					echo json_return_errors($err);
				} else {
					echo $err;
				}
				return false;
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
	 * Обрабатывает данные возвращенные моделью в виде простого списка
	 * Возвращает сам объект, что позволяет делать цепочки
	 *
	 * @access public
	 * @param array|boolean $response Массив данных, полученный при выполнении запроса и возвращенный моделью, 
	 * или значение false в случае если запрос по каким-то причинам не удалось выполнить (не надо выполнять).
	 * @param boolean $ConvertToUTF8 По умолчанию: true. Установите false, если не требуется конвертировать данные в UTF формат. Применяется рекурсивно ко всем элементам, в т.ч. вложенным
	 * @param boolean $AllowBlank По умолчанию: false. При вызове с значением по умолчанию функция возвращает ошибку 
	 * при отсутствии данных в ответе. В случае, если нужно, чтобы вернулся пустой массив данных, установите значение true.
	 * @param string $ErrMsg Сообщение, которое отдается на клиент при ошибке запроса. По умолчанию "При запросе возникла ошибка.".
	 * @param callable $ProcessFunc Функция, осуществляющая дополнительную обработку строк, вызывается для каждой строки данных, возвращает обработанную строку
	 * 
	 * @return swController Возвращает объект текущего класса.
	 *
	 */
	function ProcessModelList($response, $ConvertToUTF8 = true, $AllowBlank = false, $ErrMsg = 'При запросе возникла ошибка.', $ProcessFunc = NULL) {
		if ( !isset($this->OutData) )
			$this->OutData = array();
		// Если запрос вернул false, просто возвращаем пустой массив
		if ( $response === false ) {
			return $this;
		}
		if ( is_array($response) && ((count($response) > 0) || (count($response) == 0 && $AllowBlank)) ) {
			$response = $this->ProcessOutputData($response);
            foreach ($response as $row) {
				if ( isset($ProcessFunc) ) {
					$row = $ProcessFunc($row, $this);
				}
                if ($ConvertToUTF8 && is_array($row)) {
                    array_walk_recursive($row, 'ConvertFromWin1251ToUTF8');
                }
                $this->OutData[] = $row;
            }
			//Атрибуты грузятся при загрузке данных формы. Элементы со значениями переносятся из 0-й строки в fieldsData
			if (isset($_POST['attrObjects'])) {
				$this->load->helper('Attribute');
				$attrObjects = processInputAttributesData($_POST);
				$attrIdents = processInputIdentData($_POST, $attrObjects);
				$attributesData = getAttributesData($attrObjects, $attrIdents);
				$this->OutData = modifyOutDataWithAttributes($this->OutData, $attributesData);
			}
        } else {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($ErrMsg)
			);
		}
		return $this;
	}

	/**
	 * Осуществляет форматирование полей типа Datetime в заданном формате.
	 * Возвращает сам объект, может быть встроен в цепочку между ProcessModelList и ReturnData
	 *
	 * @param string $format
	 * @return swController
	 */
	function formatDatetimeFields($format = 'd.m.Y'){
		/**
		 * Конвертирование даты-времени
		 */
		if (!function_exists('convertDatetime')) {
			function convertDatetime(&$var, $key, $format)
			{
				if ($var instanceof DateTime ||
					preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d+)?$/', $var)
				) {
					$var = ConvertDateFormat($var, $format);
				}
			}
		}
        array_walk_recursive($this->OutData,'convertDatetime', $format);
		return $this;
	}

	/**
	 * Обрабатывает данные возвращенные моделью в виде одной записи после добавления/редактирования
	 * Возвращает сам объект, что позволяет делать цепочки
	 *
	 * @access public
	 * @param array|boolean $response Массив данных, полученный при выполнении запроса и возвращенный моделью, 
	 * или значение false в случае если запрос по каким-то причинам не удалось выполнить (не надо выполнять).
	 * @param boolean $ConvertToUTF8 По умолчанию: true. Установите false, если не требуется конвертировать данные в UTF формат.
	 * @param string $ErrMsg Сообщение, которое отдается на клиент при ошибке запроса. По умолчанию "При запросе возникла ошибка.".
	 * @param callable $ProcessFunc Функция, осуществляющая дополнительную обработку строки данных, возвращает обработанную строку
	 * 
	 * @return swController Возвращает объект текущего класса.
	 */
	function ProcessModelSave($response, $ConvertToUTF8 = true, $ErrMsg = 'При записи данных произошла ошибка.', $ProcessFunc = NULL) {
		$this->OutData = array();
		// Если запрос вернул false, просто возвращаем пустой массив
		if ( $response === false ) {
			return $this;
		}
		if ( is_array($response) && (count($response) > 0) ) {
			// В результате сохранения должен быть просто одномерный массив с элементом success или Error_Msg
			// Однако в устаревшем коде это может быть завернуто в еще один уровень
			// В этом случае поднимаем уровень массива выше
			if ( isset($response[0])) {
				$response = $response[0];
			}
			
			if  (is_array($response)) {
				if ( $ConvertToUTF8 ) {
                    array_walk($response, 'ConvertFromWin1251ToUTF8');
                }
				$this->OutData = isset($ProcessFunc) ? $ProcessFunc($response) : $response;

				if (isset($_POST['attributes']) && isset($this->InData['pmUser_id'])) {
					$this->load->helper('Attribute');
					$this->InData['attributes'] = $_POST['attributes'];
					$this->OutData['attributes'] = saveAttributes($this->InData, $response);
				}

				if (array_key_exists('success', $this->OutData)) { // если от модели пришел success = true, значит уже все хорошо, расходимся
					return $this;
				}
				if (array_key_exists('Error_Msg', $this->OutData)) {
					if (strlen($this->OutData['Error_Msg']) == 0) {
						$this->OutData['success'] = true;
					}
					else {
						$this->OutData['success'] = false;
					}
				} else {
					$this->OutData['success'] = false;
				}
			} else {
				$this->OutData = array(
					'success' => false,
					'Error_Msg' => toUtf('Ошибка при записи данных: функция сохранения вернула неправильный ответ - '.var_export($response, true))
				);
			}
		} else {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($ErrMsg)
			);
		}
		return $this;
	}

	/**
	 * Обрабатывает данные возвращенные моделью в виде мульти-списка
	 * Возвращает сам объект, что позволяет делать цепочки
	 *
	 * @access public
	 * @param array|boolean $response Массив данных, полученный при выполнении запроса и возвращенный моделью, 
	 * или значение false в случае если запрос по каким-то причинам не удалось выполнить (не надо выполнять).
	 * @param boolean $ConvertToUTF8 По умолчанию: true. Установите false, если не требуется конвертировать данные в UTF формат.
	 * @param boolean $AllowBlank По умолчанию: false. При вызове с значением по умолчанию функция возвращает ошибку 
	 * при отсутствии данных в ответе. В случае, если нужно, чтобы вернулся пустой массив данных, установите значение true.
	 * @param string $ErrMsg Сообщение, которое отдается на клиент при ошибке запроса. По умолчанию "При запросе возникла ошибка.".
	 * @param callable $ProcessFunc Функция, осуществляющая дополнительную обработку строки данных, возвращает обработанную строку
	 * @param boolean $alldata Резрешает вернуть клиенту все параметры первого уровня (дополнительно к data), которые были переданы из модели 
	 * 
	 * @return swController Возвращает объект текущего класса.
	 */
	function ProcessModelMultiList($response, $ConvertToUTF8 = true, $AllowBlank = false, $ErrMsg = 'При запросе возникла ошибка.', $ProcessFunc = NULL, $IsAllData = false) {
		$this->OutData = array();
		// Если запрос вернул false, просто возвращаем пустой массив
		if ( $response === false ) {
			return $this;
		}
		if ( isset($response['data']) && is_array($response['data']) && ((count($response['data']) > 0) || (count($response['data']) == 0 && $AllowBlank)) ) {
			$this->OutData['data'] = array();
			if ($IsAllData) { // Если разрешено передать все данные (первого уровня)
				foreach ($response as $k=>$r) {
					if ( $k!='data' && (!is_array($r)||$k=='tpl') ) { // все, кроме 'data' ('data' обрабатываем отдельно ниже)
						$this->OutData[$k] = ( $ConvertToUTF8 )?toUtf($response[$k]):$response[$k];
					}
				}
			} else { // только totalCount
				if (isset($response['totalCount'])) {
					$this->OutData['totalCount'] = $response['totalCount'];
				}
			}
			foreach ($response['data'] as $row) {
				if ( isset($ProcessFunc) ) {
					$row = $ProcessFunc($row, $this);
				}
				if ( $ConvertToUTF8 )
					array_walk($row, 'ConvertFromWin1251ToUTF8');
				$this->OutData['data'][] = $row;
			}
			if (isset($response['overLimit'])) {
				$this->OutData['overLimit'] = $response['overLimit'];
			}
		} else {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => !empty($response['Error_Msg'])?$response['Error_Msg']:toUtf($ErrMsg)
			);
		}
		return $this;
	}

	/**
	 * @param mixed $response
	 * @param string $type
	 * @return $this
	 */
	function ProcessRestResponse($response, $type = 'list') {
		$this->OutData = processRestResponse($response, $type);
		return $this;
	}
	
	/**
	 * Генерирует и печатает выходные данные для пользователя. Пока только в json-формате.
	 *
	 * @access public
	 * @param array $data Если в функцию передается входной массив $data, то для вывода используются данные переданного массива. 
	 * В другом случае используется полученный массив данных объекта ($this->OutData).
	 *
	 * @return boolean
	 */
	function ReturnData($data = NULL) {

		if ( isset($data) ) {
			// Отмена стандартного обработчика ошибок
			if ($this->Cancel_Error_Handle) {
				$data['Cancel_Error_Handle'] = true;
			}
			echo json_encode($data);
		} else {
			// Отмена стандартного обработчика ошибок
			if ($this->Cancel_Error_Handle) {
				$this->OutData['Cancel_Error_Handle'] = true;
			}
			echo json_encode($this->OutData);
		}
		return true;
	}
    
	/**
	 * Генерирует и печатает выходные данные для пользователя, с постраничным выводом
	 * 
	 * @access public
	 * @param array $data Если в функцию передается входной массив $data, то для вывода используются данные переданного массива. 
	 * @param int $start По умолчанию: 0. Значение индекса, с которого выполняется выборка записей.
	 * @param int $limit По умолчанию: 100. Количество возвращаемых записей.
	 * 
	 * @return boolean 
	 */
	function ReturnLimitData($data = NULL, $start = 0, $limit = 100) {
		if ( isset($data) ) {
			$outdata = array_slice($data, $start, $limit);
			$outcount = count($data);
		} else {
			$outdata = array_slice($this->OutData, $start, $limit);
			$outcount = count($this->OutData);
		}
		if ($outcount > 0 ) {
			echo json_encode(array(
				'data' => $outdata,
				'count' => $outcount
			));
		} else {
			echo json_encode(array());
		}
		return true;
	}
	
	/**
	 * Возвращает входящие данные в виде ассоциативного массива (пришедшие с клиента и взятые из сессии).
	 * Если задан индекс, то возвращает данные по этому индексу в массиве, иначе возвращает весь массив.
	 * 
	 * @access public
	 * @param string $index Индекс записи. По умолчанию не указан и возвращает весь массив, в обратном случае 
	 * возвращает одну запись массива с указанном индексом.
	 * 
	 * @return array|string|int Массив записей или одна запись с указанном индексом. 
	 */
	function GetInData($index = NULL) {
		if ( isset($index) ) {
			if (isset($this->InData[$index])) {
				return $this->InData[$index];
			} else {
				return NULL;
			}
		} else {
			return $this->InData;
		}
	}
	
	/**
	 * Возвращает выходные данные в виде простого двумерного массива (результат выполнения запроса). 
	 * Если задан индекс, то возвращает данные по этому индексу в массиве, иначе возвращает весь массив.
	 * 
	 * @access public
	 * @param int $index Индекс записи. По умолчанию не указан и возвращает весь массив, в обратном случае 
	 * возвращает одну запись массива с указанном индексом.
	 * 
	 * @return array Массив записей или одна запись с указанном индексом.
	 */
	public function GetOutData( $index=null ){
		if ( $index === null ) {
			return $this->OutData;
		} else if ( isset( $this->OutData[ $index ] ) ) {
			return $this->OutData[ $index ];
		} else {
			return null;
		}
	}

	/**
	 * Возвращает ошибочный ответ в JSON формате
	 *
	 * @access public
	 * @param string $ErrorMsg Сообщение об ошибке
	 * @param string $ErrorCode Код ошибки, опционально
	 *
	 * @return boolean
	 */
	function ReturnError($ErrorMsg = NULL, $ErrorCode = NULL) {
		$this->ReturnData(
			array(
				'success' => false,
				'Error_Code' => (isset($ErrorCode) ? $ErrorCode : 0),
				'Error_Msg' => toUtf($ErrorMsg)
			)
		);
		return true;
	}
	
	/**
	 * Получение массива входящих параметров. Сделано функцией дабы не нарушать инкапсуляцию
	 */
	function GetInputRules(){
		if ( isset($this->inputRules) ) {
			return $this->inputRules;
		} else {
			return null;
		}
	}

	/**
	 * Обработка возвращаемых данных
	 *
	 * @param array $data данные
	 *
	 * @return array
	 */
	function ProcessOutputData($data) {

		$funcName = debug_backtrace()[2]['function'];//определить имя метода, для которого ищется правило
		if (isset($this->outputRules) && isset($this->outputRules[$funcName])) {

			$rules = $this->outputRules[$funcName];
			$result = [];

			foreach ($data as $key1 => $value) {
				$result[$key1] = [];
				foreach ($value as $key2 => $item) {
					if (isset($rules[$key2])) {
						$result[$key1][$rules[$key2]] = $item;
					} else {
						$result[$key1][$key2] = $item;
					}
					unset($value);
				}
				unset($data[$key1]);
			}
			return $result;
		} else {
			return $data;
		}
	}

	/**
	 * Функция обработки резульата запроса
	 * @param mixed $result
	 * @return boolean
	 */
	public function isSuccessful($result) {
		if (is_array($result) && !empty($result['Error_Msg'])) return false;
		return (is_array($result) && empty($result[0]['Error_Msg']));
	}
}
// END swController class
