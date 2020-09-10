<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnLabRequest
 * @OA\Tag(
 *     name="EvnLabRequest",
 *     description="Функционал работы с заявками из мобильного ПЗ, если на регионе нет ЛИС"
 * )
 */
class EvnLabRequest extends SwREST_Controller {
	protected $inputRules = array(
		'getEvnLabRequest' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequest_BarCode', 'label' => 'Штрих-код', 'rules' => '', 'type' => 'int'),
		),
		'takeLabSample' => array(
			array('field' => 'MedServiceType_SysNick', 'label' => 'MedServiceType_SysNick', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_did', 'label' => 'Служба, где взята проба', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequests', 'label' => 'EvnLabRequests', 'rules' => '', 'type' => 'string'),
			array('field' => 'sendToLis', 'label' => 'sendToLis', 'rules' => '', 'type' => 'int'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id')
		),
		'cancelLabSample' => array(
			array('field' => 'MedServiceType_SysNick', 'label' => 'MedServiceType_SysNick', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_did', 'label' => 'Служба, где взята проба', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequests', 'label' => 'EvnLabRequests', 'rules' => '', 'type' => 'string'),
			array('field' => 'sendToLis', 'label' => 'sendToLis', 'rules' => '', 'type' => 'int'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id')
		),
		'loadEvnLabRequestList' => array(
			array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
			array('field' => 'MedServiceLab_id','label' => 'Служба','rules' => '','type' => 'id'),
			array('field' => 'MedServiceType_SysNick','label' => 'Тип службы','rules' => '','type' => 'string'),
			array('field' => 'EvnDirection_IsCito','label' => 'Cito!','rules' => '','type' => 'id', 'default' => null),
			array('field' => 'EvnLabSample_IsOutNorm','label' => 'Отклонение','rules' => '','type' => 'id', 'default' => null),
			array('field' => 'begDate','label' => 'Начало периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'endDate','label' => 'Конец периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'EvnLabRequest_id','label' => 'Заявка','rules' => '','type' => 'id'),
			array('field' => 'filterWorkELRByDate','label' => 'Фильтровать заявки в работе по дате','rules' => '','type' => 'int'),
			array('field' => 'filterDoneELRByDate','label' => 'Фильтровать заявки с результатами по дате','rules' => '','type' => 'int'),
			array('field' => 'filterSign','label' => 'Фильтр по подписи','rules' => '','type' => 'id'),
			array('field' => 'EvnStatus_id', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'label' => 'Пользователь', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Фильтр по услуге', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_Phone', 'label' => 'Телефон', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'string'),
			array('field' => 'PrehospDirect_Name', 'label' => 'Кем направлен', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabRequest_FullBarCode', 'label' => 'Штрих-код', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_SurName','label' => 'Фамилия','rules' => '','type' => 'string'),
			array('field' => 'Person_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Person_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Person_id','label' => 'ИД пациента','rules' => '','type' => 'id'),
			array('field' => 'Person_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'ElectronicService_id','label' => 'Идентификатор пункта обслуживания','rules' => '','type' => 'id'),
			array('field' => 'ElectronicQueueInfo_id','label' => 'Идентификатор электронной очереди','rules' => '','type' => 'id'),
			array('field' => 'ElectronicTalon_Num','label' => 'Номер талона','rules' => '','type' => 'string'),
			array('field' => 'ElectronicTalonPseudoStatus_id','label' => 'Псевдо-статус талона ЭО','rules' => '','type' => 'id'),
			array('field' => 'AnalyzerTest_id' ,'label' => 'Исследование','rules' => '','type' => 'int'),
			array('field' => 'MethodsIFA_id' ,'label' => 'Методики ИФА','rules' => '','type' => 'int'),
			array('field' => 'formMode','label' => 'Режим формы','rules' => '','type' => 'string'),
			array('field' => 'byElectronicService','label' => 'показать только заявки связанные с ЭО','rules' => '','type' => 'string')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->load->database();
		$this->load->model('EvnLabRequest_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение данных заявки на лаб. исследование",
	 *     @OA\Parameter(
	 *     		name="EvnLabRequest_id",
	 *     		in="query",
	 *     		description="Идентификатор заявки",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabRequest_BarCode",
	 *     		in="query",
	 *     		description="Штрих-код",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnLabRequest', null, true);
		$response = null;

		switch(true) {
			case !empty($data['EvnLabRequest_id']):
				$this->dbmodel->EvnLabRequest_id = $data['EvnLabRequest_id'];
				$response = $this->dbmodel->load();
				break;
			case !empty($data['EvnDirection_id']):
				$response = $this->dbmodel->load($data['EvnDirection_id']);
				break;
			case !empty($data['EvnLabRequest_BarCode']):
				$response = $this->dbmodel->load(null, $data['EvnLabRequest_BarCode']);
				break;
			default:
				$data = $this->ProcessInputData('loadEvnLabRequestList', null, true);
				$keys = [
					'EvnLabRequest_FullBarCode',
					'PrehospDirect_Name',
					'Person_SurName',
					'Person_FirName',
					'Person_SecName'
				];
				foreach($keys as $key) {
					if (isset($data[$key])) {
						$data[$key] = json_decode($data[$key], true);
					}
				}
				$response = $this->dbmodel->loadEvnLabRequestList($data);
		}

		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}
	
	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/LabSample/take",
	 *     tags={"EvnLabRequest"},
	 *     summary="Массовое взятие проб",
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
	 *     					property="MedService_did",
	 *     					description="Идентификатор службы, в которой взята проба",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequests",
	 *     					description="Список идентификаторов заявок в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="sendToLis",
	 *     					description="Признак необходимости отправки в ЛИС (служба АС МЛО)",
	 *     					type="integer"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function takeLabSample_post() {

		$data = $this->ProcessInputData('takeLabSample', null, true);
		if (empty($data['session']['medpersonal_id'])) {
			$data['session']['medpersonal_id'] = NULL;
		}

		$response = $this->dbmodel->takeLabSample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/LabSample/cancel",
	 *     tags={"EvnLabRequest"},
	 *     summary="Массовая отмена проб",
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
	 *     					property="MedService_did",
	 *     					description="Идентификатор службы, в которой взята проба",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequests",
	 *     					description="Список идентификаторов заявок в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="sendToLis",
	 *     					description="Признак необходимости отправки в ЛИС (служба АС МЛО)",
	 *     					type="integer"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function cancelLabSample_post() {
		$data = $this->ProcessInputData('cancelLabSample', null, true);

		if (empty($data['session']['medpersonal_id'])) {
			$data['session']['medpersonal_id'] = NULL;
		}

		$response = $this->dbmodel->cancelLabSample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}