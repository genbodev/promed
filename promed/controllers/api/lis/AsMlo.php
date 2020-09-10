<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class AsMlo
 * @OA\Tag(
 *     name="AsMlo",
 *     description="Взаимодействие с сервисом АсМло"
 * )
 * @property AsMlo_model dbmodel
 */
class AsMlo extends SwREST_Controller {
	protected  $inputRules = array(
		'checkAsMloLabSamples' => array(
		),
		'login' => array(
			array('field' => 'login', 'label' => 'Логин', 'rules' => '', 'type' => 'string'),
			array('field' => 'password', 'label' => 'Пароль', 'rules' => '', 'type' => 'string'),
		),
		'logout' => array(
		),
		'check' => array(
		),
		'setDirectory' => array(
			array('field' => 'directory', 'label' => 'Справочник', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'records', 'label' => 'Массив строк справочника', 'rules' => 'required', 'type' => 'string'),
		),
		'getDirectory' => array(
			array('field' => 'directory', 'label' => 'Справочник', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'filters', 'label' => 'Набор параметров для фильтрации', 'rules' => '', 'type' => 'string'),
		),
		'setSample' => array(
			array('field' => 'id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'number', 'label' => 'Штрих-код', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'internalNum', 'label' => 'Внутренний номер', 'rules' => '', 'type' => 'id'),
			array('field' => 'biomaterialId', 'label' => 'Идентификатор биоматериала', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'cito', 'label' => 'Признак срочности', 'rules' => '', 'type' => 'int'),
			array('field' => 'orderId', 'label' => 'Идентификатор Заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'clinicId', 'label' => 'Идентификатор клиники', 'rules' => '', 'type' => 'id'),
			array('field' => 'clinicName', 'label' => 'Наименование клиники', 'rules' => '', 'type' => 'string'),
			array('field' => 'directionNum', 'label' => 'Номер направления', 'rules' => '', 'type' => 'string'),
			array('field' => 'doctorId', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'doctor', 'label' => 'Врач', 'rules' => '', 'type' => 'string'),
			array('field' => 'patOtdelen', 'label' => 'Отделение', 'rules' => '', 'type' => 'string'),
			array('field' => 'weight', 'label' => 'Вес пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'personId', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'lastName', 'label' => 'Фамилия пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'firstName', 'label' => 'Имя пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'middleName', 'label' => 'Отчество пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'sex', 'label' => 'Пол пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'snils', 'label' => 'СНИЛС', 'rules' => '', 'type' => 'string'),
			array('field' => 'polisSer', 'label' => 'Серия полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'polisNum', 'label' => 'Номер полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'dateOfBirth', 'label' => 'Дата рождения пациента', 'rules' => '', 'type' => 'date'),
			array('field' => 'update', 'label' => 'Признак повторной отправки', 'rules' => '', 'type' => 'string'),
			array('field' => 'tests', 'label' => 'Тесты', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'targets', 'label' => 'Исследования', 'rules' => 'required', 'type' => 'string'),
		),
		'getSampleInfo' => array(
			array('field' => 'id', 'label' => 'Идентификатор пробы', 'rules' => '', 'type' => 'id'),
			array('field' => 'number', 'label' => 'Штрих-код', 'rules' => '', 'type' => 'id'),
			array('field' => 'archive', 'label' => 'Признак архивных записей', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ready', 'label' => 'Признак готовности проб', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'raw', 'label' => 'Признак возврата сырых данных', 'rules' => '', 'type' => 'checkbox'),
		),
		'setWorklist' => array(
			array('field' => 'id', 'label' => 'Идентификатор рабочего списка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'lengthX', 'label' => 'Размерность рабочего списка по горизонтали', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'lengthY', 'label' => 'Размерность рабочего списка по вертикали', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'worklist', 'label' => 'Набор проб в штативе', 'rules' => 'required', 'type' => 'string'),
		),
		'getWorklistInfo' => array(
			array('field' => 'id', 'label' => 'Идентификатор рабочего списка', 'rules' => '', 'type' => 'id'),
			array('field' => 'archive', 'label' => 'Признак архивных записей', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ready', 'label' => 'Признак готовности проб', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'raw', 'label' => 'Признак возврата сырых данных', 'rules' => '', 'type' => 'checkbox'),
		),
		'setSuccessConfirmation' => array(
			array('field' => 'samples', 'label' => 'Набор идентификаторов проб', 'rules' => '', 'type' => 'string'),
			array('field' => 'worklists', 'label' => 'Набор идентификаторов рабочих списков', 'rules' => '', 'type' => 'string'),
		),
		'moveArchive' => array(
			array('field' => 'samples', 'label' => 'Набор идентификаторов проб', 'rules' => '', 'type' => 'string'),
			array('field' => 'worklists', 'label' => 'Набор идентификаторов рабочих списков', 'rules' => '', 'type' => 'string'),
		),
		'createRequestSelections' => array(
			array('field' => 'MedServiceType_SysNick', 'label' => 'Тип службы', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabSample_id', 'label' => 'EvnLabSample_id', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabSamples', 'label' => 'Набор проб', 'rules' => '', 'type' => 'string'),
			array('field' => 'onlyNew', 'label' => 'Признак отправки только новых тестов', 'rules' => '', 'type' => 'string'),
			array('field' => 'changeNumber', 'label' => 'Признак смены номера пробы на номер текущего дня', 'rules' => '', 'type' => 'string'),
		),
		'createRequestSelectionsLabRequest' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'EvnLabRequest_id', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabRequests', 'label' => 'Набор заявок', 'rules' => '', 'type' => 'string'),
			array('field' => 'onlyNew', 'label' => 'Признак отправки только новых тестов', 'rules' => '', 'type' => 'string'),
			array('field' => 'changeNumber', 'label' => 'Признак смены номера пробы на номер текущего дня', 'rules' => '', 'type' => 'string'),
		),
		'getResultSamples' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'EvnLabSample_id', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnLabSamples', 'label' => 'EvnLabSamples', 'rules' => '', 'type' => 'json_array'),
		),
		'isSend2AnalyzerEnabled' => array(
			array('field' => 'EvnLabSamples', 'label' => 'Набор проб', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'int'),
		),
		'EvnLabSampleHasResults' => [
			['field' => 'EvnLabSample_ids', 'label' => 'Ид заявок', 'rules' => 'required', 'type' => 'string']
		]
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->load->database('lis');
		$this->load->model('AsMlo_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     path="/api/AsMlo/RequestSelections",
	 *     tags={"AsMlo"},
	 *     summary="Отправляет набор проб в АсМло",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="MedServiceType_SysNick",
	 *     					description="Системное наименование типа службы",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSamples",
	 *     					description="Список проб в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="onlyNew",
	 *     					description="Признак отправки только новых тестов",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="changeNumber",
	 *     					description="Признак смены номера пробы на номер текущего дня",
	 *     					type="integer"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function RequestSelections_post() {
		$data = $this->ProcessInputData('createRequestSelections', null, true);

		$arrayId = array();
		if($data) {
			if (!empty($data['EvnLabSamples'])) {
				$arrayId = json_decode($data['EvnLabSamples']);
			}
		} else {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "Для создания заявки необходимо выбрать хотя бы одну пробу"
			));
		}

		$response = $this->dbmodel->createRequestSelections($data, $arrayId);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/AsMlo/RequestSelectionsLabRequest",
	 *     tags={"AsMlo"},
	 *     summary="Отправляет набор заявок в АсМло",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequests",
	 *     					description="Список заявок в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="onlyNew",
	 *     					description="Признак отправки только новых тестов",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="changeNumber",
	 *     					description="Признак смены номера пробы на номер текущего дня",
	 *     					type="integer"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function RequestSelectionsLabRequest_post(){
		$data = $this->ProcessInputData('createRequestSelectionsLabRequest', null, true);

		$arrayId = array();
		if($data) {
			$arrayId = $this->dbmodel->getLabSamplesForEvnLabRequests($data);
		} else {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "Для создания заявки необходимо выбрать хотя бы одну заявку с пробами"
			));
		}
		if (count($arrayId) < 1) {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "В выбранных заявках отсутсвуют взятые пробы"
			));
		}

		$response = $this->dbmodel->createRequestSelections($data, $arrayId);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/doSuccessConfirmation",
	 *     	tags={"AsMlo"},
	 *     	summary="Выполняет setSuccessConfirmation для заданных проб",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function doSuccessConfirmation_post() {
		$response = $this->dbmodel->doSetSuccessConfirmation();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Get(
	 *		path="/api/AsMlo/ResultSamples",
	 *		tags={"AsMlo"},
	 *	 	summary="Получение данных из АсМло по нескольким выбранным пробам",
	 *     	@OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnLabSamples",
	 *     		in="query",
	 *     		description="Список идентификаторов проб в JSON-формате",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ResultSamples_get() {
		$data = $this->ProcessInputData('getResultSamples', null, true);

		if(!$data || empty($data['EvnLabSamples'])) {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "Для получения результатов нужно выбрать хотя бы одну пробу"
			));
		}
		$answers = array();
		$resultSuccess = false;
		$isErrorReplyFormat = false;
		foreach($data['EvnLabSamples'] as $idObj) {
			$data['EvnLabSample_id'] = $idObj->id;
			$data['EvnLabSample_BarCode'] = $idObj->barcode;

			if ( !$this->dbmodel->isLogon() ) {
				$result = $this->dbmodel->login($data);
				if ( !( is_array($result) && !empty($result['success']) && $result['success']==1 ) ) {
					if ( is_array($result) && !empty($result['Error_Msg']) ) {
						$this->response(array(
							'error_code' => 6,
							'error_msg' => $result['Error_Msg']
						));
					}
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Ошибка идентификации в сервисе АСМЛО'
					));
				}
			}
			if (!$this->dbmodel->isLogon()) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка авторизации в сервисе АСМЛО'
				));
			}

			$result = $this->dbmodel->getSampleInfo(array(
				'id' => $data['EvnLabSample_id']
			,'number' => $data['EvnLabSample_BarCode']
			));
			//log_message('error', 'result-getSampleInfo:'); log_message('error', print_r($result, true));
			if ( !is_array($result) ) {
				$isErrorReplyFormat = true;//Признак наличия ошибки формата ответного сообщения
				$errMess = 'Неверный формат ответа от АСМЛО';
				$answers[$errMess] = $errMess;
				//$this->ReturnError($errMess);
				//return false;
				continue;
			} else if ( !empty($result['Error_Msg']) ) {
				if (!empty($result['Error_Code'])) { //при ошибке curl'а Error_Code is undefinded
					$errMess = $this->dbmodel->getErrorMessage($result['Error_Code'], ($idObj->analyzer2way == '2'));
				} else {
					$errMess = $result['Error_Msg'];
				}

				$answers[$errMess] = $errMess;
				continue;
			}
			$result = $this->dbmodel->getResultSamples($data, $result['response']);
			$resultSuccess = true; //Признак успеха хотя бы по одной пробе
		}
		if ((!$resultSuccess || $isErrorReplyFormat) && count($answers)>0) { // Если ("Не успех" или "ошибка формата") и есть ошибки то выведем их
			$this->response(array(
				'error_code' => 6,
				'error_msg' => join(';<br/>', $answers)
			));
		}

		$response = array('success' => true);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *		path="/api/AsMlo/checkLabSamples",
	 *		tags={"AsMlo"},
	 *	 	summary="Получение результатов из АсМло",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function checkLabSamples_get() {
		$data = $this->ProcessInputData('checkAsMloLabSamples', null, true);
		$response = $this->dbmodel->checkAsMloLabSamples($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/AsMlo/login",
	 *     tags={"AsMlo"},
	 *     summary="Идентификация в сервисе",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="login",
	 *     					description="Логин",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="password",
	 *     					description="Пароль",
	 *     					type="string"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function login_post() {
		$data = $this->ProcessInputData('login', null, true);
		$response = $this->dbmodel->login($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/logout",
	 *  	tags={"AsMlo"},
	 *	    summary="Завершение сессии в сервисе",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function logout_post() {
		$data = $this->ProcessInputData('logout', null, true);
		$response = $this->dbmodel->logout($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AsMlo/check",
	 *  	tags={"AsMlo"},
	 *	    summary="Проверка готовности работы сервиса",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function check_get() {
		$data = $this->ProcessInputData('check', null, true);
		$response = $this->dbmodel->check($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/DirectoryGost2011",
	 *  	tags={"AsMlo"},
	 *	    summary="Передача ГОСТ-2011 сервису",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DirectoryGost2011_post() {
		set_time_limit(0);
		$response = $this->dbmodel->setDirectoryGost2011();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/DirectoryLpu",
	 *  	tags={"AsMlo"},
	 *	    summary="Передача МО сервису",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DirectoryLpu_post() {
		set_time_limit(0);
		$response = $this->dbmodel->setDirectoryLpu();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/Directory",
	 *  	tags={"AsMlo"},
	 *	    summary="Передача справочника сервису",
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Directory_post() {
		$data = $this->ProcessInputData('setDirectory', null, true);

		if (!empty($data['records'])) {
			$data['records'] = json_decode($data['records'], true);
		} else {
			$data['records'] = array();
		}

		$response = $this->dbmodel->setDirectory($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AsMlo/Directory",
	 *  	tags={"AsMlo"},
	 *	    summary="Получение справочника из сервиса",
	 *     	@OA\Parameter(
	 *     		name="directory",
	 *     		in="query",
	 *     		description="Справочник",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="filters",
	 *     		in="query",
	 *     		description="Набор параметров для фильтрации",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Directory_get() {
		$data = $this->ProcessInputData('getDirectory', null, true);

		if (!empty($data['filters'])) {
			$data['filters'] = json_decode($data['filters'], true);
		} else {
			$data['filters'] = array();
		}

		$response = $this->dbmodel->getDirectory($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/AsMlo/Sample",
	 *     tags={"AsMlo"},
	 *     summary="Передача проб в сервис",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="number",
	 *     					description="Штрих-код",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="internalNum",
	 *     					description="Внтренний номер",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="biomaterialId",
	 *     					description="Идентификатор биоматериала",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="orderId",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="clinicId",
	 *     					description="Идентификатор клиники",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="clinicName",
	 *     					description="Наименование клиники",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="directionNum",
	 *     					description="Номер направления",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="doctor",
	 *     					description="Врач",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="patOtdelen",
	 *     					description="Отделение",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="weight",
	 *     					description="Вес пациента",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="personId",
	 *     					description="Идентификатор пациента",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="lastName",
	 *     					description="Фамилия пациента",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="firstName",
	 *     					description="Имя пациента",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="middleName",
	 *     					description="Отчество пациента",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="sex",
	 *     					description="Пол пациента",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="snils",
	 *     					description="СНИЛС",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="polisSer",
	 *     					description="Серия полиса",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="polisNum",
	 *     					description="Номер полиса",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="dateOfBirth",
	 *     					description="Дата рождения",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="update",
	 *     					description="Признак повторной отправки",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="tests",
	 *     					description="Тесты",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="targets",
	 *     					description="Исследования",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *	 					"id",
	 *     					"number",
	 *     					"biomaterialId",
	 *     					"personId",
	 *     					"tests",
	 *     					"targets"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Sample_post() {
		$data = $this->ProcessInputData('setSample', null, true);

		if (!empty($data['tests'])) {
			$data['tests'] = json_decode($data['tests'], true);
		} else {
			$data['tests'] = array();
		}

		$response = $this->dbmodel->setSample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AsMlo/SampleInfo",
	 *  	tags={"AsMlo"},
	 *	    summary="Получение данных пробы",
	 *     	@OA\Parameter(
	 *     		name="id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="number",
	 *     		in="query",
	 *     		description="Штрих-код",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="archive",
	 *     		in="query",
	 *     		description="Признак архивных записей",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="ready",
	 *     		in="query",
	 *     		description="Признак готовности проб",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="raw",
	 *     		in="query",
	 *     		description="Признак возврата сырых данных",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function SampleInfo_get() {
		$data = $this->ProcessInputData('getSampleInfo', null, true);
		$response = $this->dbmodel->getSampleInfo($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/Worklist",
	 *  	tags={"AsMlo"},
	 *	    summary="Передача рабочих списков в сервис",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="id",
	 *     					description="Идентификатор рабочего списка",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="lengthX",
	 *     					description="Размерность рабочего списка по горизонтали",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="lengthY",
	 *     					description="Размерность рабочего списка по вертикали",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="worklist",
	 *     					description="Набор проб в штативе",
	 *     					type="string"
	 * 					),
	 *     				required={"id", "lengthX", "lengthY", "worklist"}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Worklist_post() {
		$data = $this->ProcessInputData('setWorklist', null, true);

		if (!empty($data['worklist'])) {
			$data['worklist'] = json_decode($data['worklist'], true);
		} else {
			$data['worklist'] = array();
		}

		$response = $this->dbmodel->setWorklist($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AsMlo/WorklistInfo",
	 *  	tags={"AsMlo"},
	 *	    summary="Получение данных по рабочему списку",
	 *     	@OA\Parameter(
	 *     		name="id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего списка",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="archive",
	 *     		in="query",
	 *     		description="Признак архивных записей",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="ready",
	 *     		in="query",
	 *     		description="Признак готовности проб",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="raw",
	 *     		in="query",
	 *     		description="Признак возврата сырых данных",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function WorklistInfo_get() {
		$data = $this->ProcessInputData('getWorklistInfo', null, true);
		$response = $this->dbmodel->getWorklistInfo($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/SuccessConfirmation",
	 *  	tags={"AsMlo"},
	 *	    summary="Подтверждение сервису успешной передачи информации данных рабочего списка или пробы",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="samples",
	 *     					description="Набор идентификаторов проб",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="worklists",
	 *     					description="Набор идентификаторов рабочих списков",
	 *     					type="string"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function SuccessConfirmation_post() {
		$data = $this->ProcessInputData('setSuccessConfirmation', null, true);

		if (!empty($data['samples'])) {
			$data['samples'] = json_decode($data['samples'], true);
		} else {
			$data['samples'] = array();
		}

		if (!empty($data['worklists'])) {
			$data['worklists'] = json_decode($data['worklists'], true);
		} else {
			$data['worklists'] = array();
		}

		$response = $this->dbmodel->setSuccessConfirmation($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/AsMlo/moveArchive",
	 *  	tags={"AsMlo"},
	 *	    summary="Перенос в архив пробы или рабочего списка",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="samples",
	 *     					description="Набор идентификаторов проб",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="worklists",
	 *     					description="Набор идентификаторов рабочих списков",
	 *     					type="string"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function moveArchive_post() {
		$data = $this->ProcessInputData('moveArchive', null, true);

		if (!empty($data['samples'])) {
			$data['samples'] = json_decode($data['samples'], true);
		} else {
			$data['samples'] = array();
		}
		if (!empty($data['worklists'])) {
			$data['worklists'] = json_decode($data['worklists'], true);
		} else {
			$data['worklists'] = array();
		}

		$response = $this->dbmodel->moveArchive($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/AsMlo/isSend2AnalyzerEnabled",
	 *  	tags={"AsMlo"},
	 *	    summary="Формирование признака доступности кнопки 'Отправить на анализатор'",
	 *     	@OA\Parameter(
	 *     		name="EvnLabSamples",
	 *     		in="query",
	 *     		description="Набор проб",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function isSend2AnalyzerEnabled_get() {
		$data = $this->ProcessInputData('isSend2AnalyzerEnabled', null, true);
		$response = $this->dbmodel->isSend2AnalyzerEnabled($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}