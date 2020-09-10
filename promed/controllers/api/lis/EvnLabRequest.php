<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnLabRequest
 * @OA\Tag(
 *     name="EvnLabRequest",
 *     description="Заявки"
 * )
 */
class EvnLabRequest extends SwREST_Controller {
    protected $inputRules = array(
        'getEvnLabRequest' => array(
            array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequest_BarCode', 'label' => 'Штрих-код', 'rules' => '', 'type' => 'int'),
			array('field' => 'delDocsView', 'label' => 'Просмотр удаленных документов', 'rules' => '', 'type' => 'int')
        ),
        'saveEvnLabRequest' => array(
            array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'PayType_id','label' => 'Вид оплаты','rules' => 'required','type' => 'id'),
            array('field' => 'MedService_id', 'label' => 'Лаборатория', 'rules' => '', 'type' => 'id'),
            array('field' => 'MedService_sid', 'label' => 'Пункт забора','rules' => '','type' => 'id'),
            array('field' => 'EvnDirection_id' ,'label' => 'Идентификатор направления','rules' => '','type' => 'id'),
            array('field' => 'EvnDirection_Num' ,'label' => 'Номер направления', 'rules' => '', 'type' => 'string'),
            array('field' => 'EvnDirection_setDT', 'label' => 'Дата направления','rules' => '','type' => 'date', 'convertIntoObject' => true),
			array('field' => 'EvnLabRequest_RegNum'  ,'label' => 'Регистрационный номер','rules' => '','type' => 'string'),
            array('field' => 'EvnDirection_IsCito', 'label' => 'Cito', 'rules' => '','type' => 'string'),
            array('field' => 'EvnDirection_Descr', 'label' => 'Комментарий', 'rules' => '','type' => 'string'),
            array('field' => 'EvnUsluga_id', 'label' => 'Заказ на проведение лабораторного обследования', 'rules' => '', 'type' => 'id'),
            array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => '', 'type' => 'id'),
            array('field' => 'TumorStage_id', 'label' => 'Стадия выявленного ЗНО', 'rules' => '', 'type' => 'id'),
            array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
            array('field' => 'EvnLabRequest_id', 'label' => 'Заявка на проведение лабораторного обследования','rules' => '','type' => 'id'),
            array('field' => 'EvnLabRequest_Comment', 'label' => 'Комментарий','rules' => '','type' => 'string'),
            array('field' => 'EvnLabRequest_didDT', 'label' => 'Дата','rules' => '','type' => 'datetime', 'convertIntoObject' => true),
            array('field' => 'EvnLabRequest_disDT', 'label' => 'Дата','rules' => '','type' => 'datetime', 'convertIntoObject' => true),
            array('field' => 'EvnLabRequest_IsSigned', 'label' => '','rules' => '','type' => 'id'),
            array('field' => 'EvnLabRequest_pid', 'label' => '','rules' => '','type' => 'id'),
            array('field' => 'EvnLabRequest_rid', 'label' => '','rules' => '','type' => 'id'),
            array('field' => 'EvnLabRequest_setDT', 'label' => '','rules' => '','type' => 'datetime', 'convertIntoObject' => true),
            array('field' => 'EvnLabRequest_signDT', 'label' => '','rules' => '','type' => 'datetime', 'convertIntoObject' => true),
            array('field' => 'MedPersonal_id', 'label' => 'Врач','rules' => '','type' => 'id'),
            array('field' => 'MedStaffFact_id', 'label' => 'Врач','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_Code','label' => 'Код врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Направившее отделение ЛПУ','rules' => '','type' => 'id'),
            array('field' => 'Lpu_sid', 'label' => 'Направившее ЛПУ','rules' => '','type' => 'id'),
            array('field' => 'Org_sid', 'label' => 'Направившая организация','rules' => '','type' => 'id'),
            array('field' => 'PrehospDirect_id', 'label' => 'Кем направлен','rules' => '','type' => 'id'),
            array('field' => 'Morbus_id', 'label' => '','rules' => '','type' => 'id'),
            array('field' => 'Person_id', 'label' => 'Идентификатор человека','rules' => 'trim','type' => 'id'),
            array('field' => 'pmUser_signID', 'label' => '','rules' => '','type' => 'id'),
            array('field' => 'UslugaExecutionType_id', 'label' => 'Тип выполнения услуги','rules' => '','type' => 'id'),
            array('field' => 'UslugaComplex_id', 'label' => 'Услуга','rules' => '','type' => 'id'),
            array('field' => 'EvnLabRequest_Ward', 'label' => 'Палата','rules' => '','type' => 'string'),
            array('field' => 'LabSample', 'label' => 'Пробы','rules' => '','type' => 'string'),
            array('field' => 'EvnLabRequest_BarCode', 'label' => 'Штрих-код','rules' => '','type' => 'string'),
            array('field' => 'ignoreCheckPayType', 'label' => 'Игнорировать проверку вида оплаты в исследованиях','rules' => '','type' => 'int'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type'=> 'int')
        ),
		'deleteEvnLabRequest' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор заявки','rules' => 'required','type' => 'id'),
		),
        'loadCompositionMenu' => array(
            array('field' => 'EvnDirection_id', 'label' => 'Направление', 'rules' => 'required', 'type' => 'id'),
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
		'getEvnUslugaParForPrint' => array(
			array('field' => 'EvnDirections', 'label' => 'Идентификаторы направлений', 'rules' => '', 'type' => 'string'),
			array('field' => 'isProtocolPrinted', 'label' => 'Протокол распечатан', 'rules' => '', 'type' => 'int')
		),
		'approveEvnLabRequestResults' => array(
			array('field' => 'EvnLabRequests', 'label' => 'EvnLabRequests', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'onlyNormal', 'label' => 'Флаг неодобрения патологий', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MedService_id', 'label' => 'MedService_id', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_IsQualityTestApprove', 'label' => 'Флаг одобрения только качественных тестов', 'rules' => '', 'type' => 'string')
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
			array('field' => '(EvnLabRequest_IsProtocolPrinted', 'label' => 'Протокол распечатан', 'rules' => '', 'type' => 'int'),
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
			array('field' => 'byElectronicService','label' => 'показать только заявки связанные с ЭО','rules' => '','type' => 'string'),
			array('field' => 'EvnLabRequest_RegNum', 'label' => 'Регистрационный номер', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Lpu_sid', 'label' => 'Медицинская организация', 'rules' => '', 'type' => 'int' ),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'int' ),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'int' )
		),
		'getNewEvnLabRequests' => array(
			array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
		),
		'getEvnDirectionNumber' => array(
		),
		'deleteEmptySamples' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор Заявки', 'rules' => '', 'type' => 'int'),
		),
		'saveEvnLabRequestContent' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Заявка на проведение лабораторного обследования', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplexContent_ids', 'label' => 'Состав исследования', 'rules' => '', 'type' => 'string'),
		),
		'cancelDirection' => array(
			array('field' => 'EvnDirection_ids', 'label' => 'Идентификаторы направлений на лабораторное обследование', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStatusHistory_Cause', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string'),
		),
		'getLabTestsPrintData' => array(
			array('field' => 'Evn_pid', 'label' => 'Evn_pid', 'rules' => '', 'type' => 'id'),
		),
		'saveEvnLabRequestUslugaComplex' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabSample_id', 'label' => 'Проба', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_id', 'label' => 'Заказ услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
		),
		'EvnLabRequestUslugaComplex_id' => array(
			array('field' => 'EvnLabRequestUslugaComplex_id', 'label' => 'Идентификатор услуги из состава исследования', 'rules' => 'required', 'type' => 'id'),
		),
		'ReCacheEvnUslugaPar' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Заказ услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'uslugaList', 'label' => 'Результат', 'rules' => '', 'type' => 'string'),
		),
		'updateEvnUslugaParResult' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Заказ услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_Result', 'label' => 'Результат', 'rules' => '', 'type' => 'string'),
		),
		'includeUslugaComplexForPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Направление', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplexListByPrescr', 'label' => 'Услуги по назначению', 'rules' => '', 'type' => 'json_array'),
		),
		'saveEvnLabRequestPrmTime' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabRequest_prmTime', 'label' => 'Время приема', 'rules' => '', 'type' => 'string'),
		),
		'ReCacheLabRequestUslugaCount' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
		),
		'ReCacheLabRequestSampleStatusType' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
		),
		'getUslugaComplexList' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'EvnLabRequest_id', 'rules' => 'required', 'type' => 'id'),
		),
		'filterEvnLabRequests' => array(
			array('field' => 'EvnLabRequest_ids', 'label' => 'Список заявок', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'UslugaComplexAttributeType_SysNick', 'label' => 'Системное наименование атрибута', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaTestStatuses', 'label' => 'Статусы теста', 'rules' => '', 'type' => 'string')
		),
		'LabRequestUslugaComplexData' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id')
		),
		'getCanceledEvnLabRequests' => array(
			array('field' => 'begDate','label' => 'Начало периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'endDate','label' => 'Конец периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'MedService_id','label' => 'Лаборатория','rules' => 'required','type' => 'id', 'default'=> null),
		)
    );

    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();

        $this->checkAuth();

		$this->db = $this->load->database('lis', true);
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
				if($data['delDocsView'] && $data['delDocsView'] == 1)
					$response = $this->dbmodel->loadForDelDocs($data['EvnDirection_id']);
				else
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
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/newEvnLabRequests",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение новых заявкок по человеку на службе",
	 *     @OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	*/
	function newEvnLabRequests_get() {
		$data = $this->ProcessInputData('getNewEvnLabRequests', null, true);
		$response = $this->dbmodel->getNewEvnLabRequests($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest",
	 *     tags={"EvnLabRequest"},
	 *     summary="Создание заявки на лаб. исследование",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="PersonEvn_id",
	 *     					description="Идентификатор состояния человека",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PayType_id",
	 *     					description="Идентификатор вида оплаты",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы лаборатории",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_sid",
	 *     					description="Идентификатор службы пункта забора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_Num",
	 *     					description="Номер направления",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_setDT",
	 *     					description="Дата направления",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_IsCito",
	 *     					description="Cito",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_Descr",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUsluga_id",
	 *     					description="Идентификатор заказа на проведение лабораторного исследования",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Diag_id",
	 *     					description="Идентификатор диагноза",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="TumorStage_id",
	 *     					description="Идентификатор стадии выявленного ЗНО",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Mes_id",
	 *     					description="Идентификатор МЭС",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_Comment",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_IsSigned",
	 *     					description="Состояние подписи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_pid",
	 *     					description="Идентификатор родительского события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_rid",
	 *     					description="Идентификатор корневого события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_setDT",
	 *     					description="Дата и время создания заявки",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_didDT",
	 *     					description="Дата и время",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_disDT",
	 *     					description="Дата и время",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_signDT",
	 *     					description="Дата и время подписания",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_id",
	 *     					description="Идентификатор врача",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedStaffFact_id",
	 *     					description="Идентификатор рабочего места врача",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LpuSection_id",
	 *     					description="Идентификатор направившего отделения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Lpu_sid",
	 *     					description="Идентификатор направившей МО",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Org_sid",
	 *     					description="Идентификатор направившей организации",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PrehospDirect_id",
	 *     					description="Идентификатор записи справочника 'Кем направлен'",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Morbus_id",
	 *     					description="Идентификатор заболевания",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Person_id",
	 *     					description="Идентификатор человека",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="pmUser_signID",
	 *     					description="Идентификатор подписавшего пользователя",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaExecutionType_id",
	 *     					description="Идентификатор типа выполнения услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатр услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_Ward",
	 *     					description="Палата",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LabSample",
	 *     					description="Пробы",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_BarCode",
	 *     					description="Штрих-код",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="ignoreCheckPayType",
	 *     					description="Признак игнорирования проверки вида оплаты в исследованиях",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Server_id",
	 *     					description="Идентификатор сервера",
	 *     					type="integer"
	 *					),
	 *     				required={
	 *						"PersonEvn_id",
	 *     					"PayType_id"
	 *	 				}
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
	function index_post() {
		$data = $this->ProcessInputData('saveEvnLabRequest', null, true);
		$data['EvnLabRequest_id'] = null;

		if (!empty($data['EvnDirection_id'])) {
			$this->dbmodel->load($data['EvnDirection_id']);
		}

		$this->dbmodel->assign($data);
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     path="/api/EvnLabRequest",
	 *     tags={"EvnLabRequest"},
	 *     summary="Изменение заявки на лаб. исследование",
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
	 *     					property="PersonEvn_id",
	 *     					description="Идентификатор состояния человека",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PayType_id",
	 *     					description="Идентификатор вида оплаты",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы лаборатории",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_sid",
	 *     					description="Идентификатор службы пункта забора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_Num",
	 *     					description="Номер направления",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_setDT",
	 *     					description="Дата направления",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_IsCito",
	 *     					description="Cito",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_Descr",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUsluga_id",
	 *     					description="Идентификатор заказа на проведение лабораторного исследования",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Diag_id",
	 *     					description="Идентификатор диагноза",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="TumorStage_id",
	 *     					description="Идентификатор стадии выявленного ЗНО",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Mes_id",
	 *     					description="Идентификатор МЭС",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_Comment",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_IsSigned",
	 *     					description="Состояние подписи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_pid",
	 *     					description="Идентификатор родительского события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_rid",
	 *     					description="Идентификатор корневого события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_setDT",
	 *     					description="Дата и время создания заявки",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_didDT",
	 *     					description="Дата и время",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_disDT",
	 *     					description="Дата и время",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_signDT",
	 *     					description="Дата и время подписания",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_id",
	 *     					description="Идентификатор врача",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedStaffFact_id",
	 *     					description="Идентификатор рабочего места врача",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LpuSection_id",
	 *     					description="Идентификатор направившего отделения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Lpu_sid",
	 *     					description="Идентификатор направившей МО",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Org_sid",
	 *     					description="Идентификатор направившей организации",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PrehospDirect_id",
	 *     					description="Идентификатор записи справочника 'Кем направлен'",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Morbus_id",
	 *     					description="Идентификатор заболевания",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Person_id",
	 *     					description="Идентификатор человека",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="pmUser_signID",
	 *     					description="Идентификатор подписавшего пользователя",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaExecutionType_id",
	 *     					description="Идентификатор типа выполнения услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатр услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_Ward",
	 *     					description="Палата",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LabSample",
	 *     					description="Пробы",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_BarCode",
	 *     					description="Штрих-код",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="ignoreCheckPayType",
	 *     					description="Признак игнорирования проверки вида оплаты в исследованиях",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *						"EvnLabRequest_id",
	 *						"PersonEvn_id",
	 *     					"PayType_id"
	 *	 				}
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
	function index_put() {
		$data = $this->ProcessInputData('saveEvnLabRequest', null, true);

		if (empty($data['EvnLabRequest_id'])){
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "Отсутствует обязательный параметр 'EvnLabRequest_id'"
			));
		}

		$this->dbmodel->EvnLabRequest_id = $data['EvnLabRequest_id'];
		$this->dbmodel->load();

		$this->dbmodel->assign($data);
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     path="/api/EvnLabRequest",
	 *     tags={"EvnLabRequest"},
	 *     summary="Удаление заявки на лаб. исследование",
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
	 *     				required={
	 *     					"EvnLabRequest_id"
	 * 					}
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
	function index_delete() {
		$data = $this->ProcessInputData('deleteEvnLabRequest', null, true);

		$this->dbmodel->EvnLabRequest_id = $data['EvnLabRequest_id'];
		$this->dbmodel->load();

		$response = $this->dbmodel->cancelDirection(array(
			'EvnDirection_id' => $this->dbmodel->EvnDirection_id,
		));
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/compositionMenu",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение данных состава услуг для меню выбора",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
    function compositionMenu_get() {
		$data = $this->ProcessInputData('loadCompositionMenu', null, true);
		$response = $this->dbmodel->loadCompositionMenu($data, false);
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

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/EvnUslugaPar",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение услуг по заявке (для печати)",
	 *     @OA\Parameter(
	 *     		name="EvnDirections",
	 *     		in="query",
	 *     		description="Список идентификаторов направлений в JSON-формате",
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
	function EvnUslugaPar_get() {
		$data = $this->ProcessInputData('getEvnUslugaParForPrint', null, true);

		$response = $this->dbmodel->getEvnUslugaParForPrint($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/approveResult",
	 *     tags={"EvnLabRequest"},
	 *     summary="Массовое одобрение результатов заявок",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequests",
	 *     					description="Список идентификаторов заявок в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),@OA\Property(
	 *     					property="onlyNormal",
	 *     					description="Флаг неодобрения проб с патологией",
	 *     					type="string",
	 *     					format="json"
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
	function approveResult_post() {
		$data = $this->ProcessInputData('approveEvnLabRequestResults', null, true);
		$response = $this->dbmodel->approveEvnLabRequestResults($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/EvnDirectionNumber",
	 *     tags={"EvnLabRequest"},
	 *     summary="Генерация номера направления",
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function EvnDirectionNumber_get() {
		$data = $this->ProcessInputData('getEvnDirectionNumber', null, true);
		$response = $this->dbmodel->getEvnDirectionNumber($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/BarCode/generate",
	 *     tags={"EvnLabRequest"},
	 *     summary="Генерация штрих-кода",
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function genBarCode_get() {
		$response = $this->dbmodel->genEvnLabRequest_BarCode();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     path="/api/EvnLabRequest/emptySamples",
	 *     tags={"EvnLabRequest"},
	 *     summary="Удаление пустых проб заявки",
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
	 *     				required={"EvnLabRequest_id"}
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
	function emptySamples_delete() {
		$data = $this->ProcessInputData('deleteEmptySamples', null, true);
		$response = $this->dbmodel->deleteEmptySamples($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/Content",
	 *     tags={"EvnLabRequest"},
	 *     summary="Сохранение состава заявки",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplexContent_ids",
	 *     					description="Список идентификаторов услуг в JSON-формате для сохранения в качестве сотава заявки",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={"EvnDirection_id"}
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
	function Content_post() {
		$data = $this->ProcessInputData('saveEvnLabRequestContent', null, true);
		$response = $this->dbmodel->saveEvnLabRequestContent($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/cancelDirection",
	 *     tags={"EvnLabRequest"},
	 *     summary="Отмена направлений",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Список идентификаторов направлений в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnStatusCause_id",
	 *     					description="Идентификатор причины отмены",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnStatusHistory_Cause",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				required={"EvnDirection_ids"}
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
	function cancelDirection_post() {
		$data = $this->ProcessInputData('cancelDirection', null, true);

		$this->load->helper('Reg');
		$response = [];
		if (!empty($data['EvnDirection_ids'])) {
			$data['EvnDirection_ids'] = json_decode($data['EvnDirection_ids'], true);
			foreach($data['EvnDirection_ids'] as $item) {
				$data['EvnDirection_id'] = $item;
				$response = $this->dbmodel->cancelDirection($data);
				if (!is_array($response)) {
					$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
				}
			}
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/LabTestsPrintData",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение данных маркера для ЭМК",
	 *     @OA\Parameter(
	 *     		name="Evn_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function LabTestsPrintData_get() {
		$data = $this->ProcessInputData('getLabTestsPrintData', null, true);
		$response = $this->dbmodel->getLabTestsPrintData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     path="/api/EvnLabRequest/prmTime",
	 *     tags={"EvnLabRequest"},
	 *     summary="Сохранение времени приема в заявке",
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
	 *     					property="EvnLabRequest_prmTime",
	 *     					description="Время приема",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"EvnLabRequest_id",
	 *     					"EvnLabRequest_prmTime"
	 * 					}
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
	function prmTime_patch() {
		$data = $this->ProcessInputData('saveEvnLabRequestPrmTime', null, true);
		$response = $this->dbmodel->saveEvnLabRequestPrmTime($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/UslugaComplex",
	 *     tags={"EvnLabRequest"},
	 *     summary="Сохранение назначения теста для услуги/пробы",
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
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_id",
	 *     					description="Идентификатор заказа услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"EvnLabRequest_id",
	 *     					"EvnLabSample_id",
	 *     					"EvnUslugaPar_id",
	 *     					"UslugaComplex_id"
	 * 					}
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
	function UslugaComplex_post() {
		$data = $this->ProcessInputData('saveEvnLabRequestUslugaComplex', null, true);
		$response = $this->dbmodel->saveEvnLabRequestUslugaComplex($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     path="/api/EvnLabRequest/UslugaComplex",
	 *     tags={"EvnLabRequest"},
	 *     summary="Удаление назначения теста для услуги/пробы",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequestUslugaComplex_id",
	 *     					description="Идентификатор услуги из состава исследования",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"EvnLabRequestUslugaComplex_id"
	 * 					}
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
	function UslugaComplex_delete() {
		$data = $this->ProcessInputData('deleteEvnLabRequestUslugaComplex', null, true);
		$response = $this->dbmodel->deleteEvnLabRequestUslugaComplex($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/UslugaComplex/includeForPrescr",
	 *     tags={"EvnLabRequest"},
	 *     summary="Включение исследования в заявку по назначению",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnPrescr_id",
	 *     					description="Идентификатор назначения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PayType_id",
	 *     					description="Идентификатор вида оплаты",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplexListByPrescr",
	 *     					description="Список услуг по назначению в JSON-формате",
	 *     					type="string",
	 *     					type="json"
	 * 					),
	 *     				required={
	 *     					"EvnPrescr_id",
	 *     					"EvnDirection_id",
	 *     					"UslugaComplex_id"
	 * 					}
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
	function includeUslugaComplexForPrescr_post() {
		$data = $this->ProcessInputData('includeUslugaComplexForPrescr', null, true);
		$response = $this->dbmodel->includeUslugaComplexForPrescr($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/EvnUslugaPar/recache",
	 *     tags={"EvnLabRequest"},
	 *     summary="Обновление содержания заказа услуги",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_id",
	 *     					description="Идентификатор заказа услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="uslugaList",
	 *     					description="Список результатов в JSON-формате",
	 *     					type="string",
	 *     					type="json"
	 * 					),
	 *     				required={
	 *     					"EvnUslugaPar_id"
	 * 					}
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
	function ReCacheEvnUslugaPar_post() {
		$data = $this->ProcessInputData('ReCacheEvnUslugaPar', null, true);
		$response = $this->dbmodel->ReCacheEvnUslugaPar($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     path="/api/EvnLabRequest/EvnUslugaPar/Result",
	 *     tags={"EvnLabRequest"},
	 *     summary="Изменение результата в заказе услуги",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_id",
	 *     					description="Идентификатор заказа услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_Result",
	 *     					description="Результат",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"EvnUslugaPar_id"
	 * 					}
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
	function EvnUslugaParResult_patch() {
		$data = $this->ProcessInputData('updateEvnUslugaParResult', null, true);
		$response = $this->dbmodel->updateEvnUslugaParResult($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/ReCacheLabRequestUslugaCount",
	 *     tags={"EvnLabRequest"},
	 *     summary="Кэширование количества назначенных тестов в заявке",
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
	 *     				required={
	 *     					"EvnLabRequest_id"
	 * 					}
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
	function ReCacheLabRequestUslugaCount_post() {
		$data = $this->ProcessInputData('ReCacheLabRequestUslugaCount', null, true);
		$response = $this->dbmodel->ReCacheLabRequestUslugaCount($data);
		if (!$response) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$response = ['success' => true];
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabRequest/ReCacheLabRequestSampleStatusType",
	 *     tags={"EvnLabRequest"},
	 *     summary="Кэширование статуса проб внутри заявки",
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
	 *     				required={
	 *     					"EvnLabRequest_id"
	 * 					}
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
	function ReCacheLabRequestSampleStatusType_post() {
		$data = $this->ProcessInputData('ReCacheLabRequestSampleStatusType', null, true);
		$response = $this->dbmodel->ReCacheLabRequestSampleStatusType($data);
		if (!$response) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$response = ['success' => true];
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/LabRequestUslugaComplexData",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение данных исследований в услуге",
	 *     @OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function LabRequestUslugaComplexData_get()
	{
		$data = $this->ProcessInputData('LabRequestUslugaComplexData', null, true);
		$response = $this->dbmodel->LabRequestUslugaComplexData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}


	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/getUslugaComplexList_get",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение списка услуг в заявке",
	 *     @OA\Parameter(
	 *     		name="EvnLabRequest_id",
	 *     		in="query",
	 *     		description="Идентификатор заявки",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function getUslugaComplexList_get() {
		$data = $this->ProcessInputData('getUslugaComplexList', null, true);
		if ($data === false) return;
		$response = $this->dbmodel->getUslugaComplexList($data);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Фильтрация заявок
	 */
	function filterEvnLabRequests_get()	{
		$data = $this->ProcessInputData('filterEvnLabRequests', null, true);
		if ($data === false) return;
		$response = $this->dbmodel->filterEvnLabRequests($data);
		$this->response(array('error_code' => 0, 'data' => $response));
	}
	
	/**
	 * @OA\Get(
	 *     path="/api/EvnLabRequest/getCanceledEvnLabRequests_get",
	 *     tags={"EvnLabRequest"},
	 *     summary="Получение отменёных заявок",
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function getCanceledEvnLabRequests_get() {
		$data = $this->ProcessInputData('getCanceledEvnLabRequests', null, true);
		if ($data === false) return;

		$response = $this->dbmodel->getCanceledEvnLabRequests($data);
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
