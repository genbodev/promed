<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH . 'libraries/SwREST_Controller.php');

/**
 * Class EvnUsluga
 * @property Lis_EvnUsluga_model $dbmodel
 * @OA\Tag(
 *     name="EvnUsluga",
 *     description="Услуги"
 * )
 */
class EvnUsluga extends SwRest_Controller
{
	protected $inputRules = array(
		'saveUslugaOrder' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор периодики', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'order', 'label' => 'Заказ услуг', 'rules' => 'required', 'type' => 'string'),
		),
		'getUslugaByPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnParams' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		),
		'getReagentCountByDate' => array(
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Date', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),
		),
		'getReagentAutoRateCountOnAnalyser' => array(
			array('default' => 0, 'field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
			array('field' => 'begDate', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'endDate', 'label' => '', 'rules' => '', 'type' => 'date'),
		),
		'getDataForResults' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnUslugaParNodeList' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения стоматалогии', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPL_id', 'label' => 'Идентификатор ТАП', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLStom_id', 'label' => 'Идентификатор ТАП стоматалогии', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDispMigrant_id', 'label' => 'Идентификатор талона освидетельствования', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDispDriver_id', 'label' => 'Идентификатор талона освидетельствования', 'rules' => '', 'type' => 'id'),
			array('field' => 'type', 'label' => 'Тип отображения', 'rules' => '', 'type' => 'int'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'except_ids', 'label' => 'Исключения', 'rules' => '', 'type' => 'json_array'),
		),
		'getEvnUslugaParViewData' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEvnUslugaParEditForm' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEvnUslugaParSimpleEditForm' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
		),
		'editEvnUslugaPar' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор выполнения параклинической услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_setDate', 'label' => 'Дата оказания услуги', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'EvnUslugaPar_setTime', 'label' => 'Время оказания услуги', 'rules' => 'trim', 'type' => 'time'),
			array('field' => 'ignoreKSGChangeCheck', 'label' => 'Признак игнорирования проверки изменения КСГ', 'rules' => '', 'type' => 'int')
		),
		'loadEvnUslugaCount' => array(
			array('field' => 'EvnUsluga_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEvnUslugaPanel' => array(
			array('field' => 'EvnUsluga_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEvnUslugaParPanel' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_rid', 'label' => 'Идентификатор корневого события', 'rules' => '', 'type' => 'id'),
			array('field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int', 'default' => 100),
		),
		'getEvnUslugaParPersonHistory' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'person_in', 'label' => 'Список идентификаторов людей', 'rules' => '', 'type' => 'string'),
			array('field' => 'userLpuUnitType_SysNick', 'label' => 'Тип группы отделений', 'rules' => '', 'type' => 'string'),
			array('field' => 'useArchive', 'label' => 'Признак архивных записей', 'rules' => '', 'type' => 'int'),
		),
		'getEvnUslugaListForEvnXml' => array(
			array('field' => 'EvnUsluga_pids', 'label' => 'Список идентифифкаторов родительских событий', 'rules' => 'required', 'type' => 'json_array'),
			array('field' => 'UslugaComplexAttributeType_id', 'label' => 'Идентификатор типа атрибута услуги', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'XmlDataLevel_SysNick', 'label' => 'Уровень', 'rules' => '', 'type' => 'string'),
			array('field' => 'code2011list', 'label' => 'Список кодов ГОСТ-2011', 'rules' => '', 'type' => 'string'),
		),
		'loadEvnUslugaGrid' => array(
			array('default' => 'EvnUsluga', 'field' => 'class', 'label' => 'Класс услуги', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'parent', 'label' => 'Родительский класс', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'pid', 'label' => 'Идентификатор родительского события', 'rules' => '', 'type' => 'id'),
			array('field' => 'pid_list', 'label' => 'Идентификатор родительского события', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'rid', 'label' => 'Идентификатор корневого родительского события', 'rules' => '', 'type' => 'id'),
		),
		'getEvnUslugaViewData' => array(
			array('field' => 'EvnUsluga_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnUsluga_pid', 'label' => 'Идентификатор родительского события услуги', 'rules' => '', 'type' => 'id'),
		),
		'getEvnUslugaParInfo' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id')
		),
		'getEvnUslugaList' => array(
			array('field' => 'EvnUsluga_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnUslugaParByEvnDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEvnUslugaParSaveData' => array(
			array('field' => 'EvnUslugaPar_id','label' => 'Идентификатор параклинической услуги','rules' => 'required','type' => 'id'),
		),
		'saveEvnUslugaParFull' => array(
			array('field' => 'EvnDirection_Num','label' => 'Номер направления','rules' => 'trim','type' => 'string'),
			array('field' => 'EvnDirection_setDate','label' => 'Дата направления','rules' => 'trim','type' => 'date'),
			array('field' => 'EvnUslugaPar_id','label' => 'Идентификатор параклинической услуги','rules' => '','type' => 'id'),
			array('field' => 'EvnUslugaPar_pid','label' => 'Идентификатор родительского события','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => '','type' => 'id'),
			array('field' => 'TimetablePar_id','label' => 'Идентификатор бирки','rules' => 'trim','type' => 'id'),// Обязателен при заполнении услуги заказанной по записи
			array('field' => 'EvnUslugaPar_isCito','label' => 'Признак срочности','rules' => 'trim','type' => 'id'),
			array('field' => 'EvnUslugaPar_Kolvo','label' => 'Количество','rules' => '','type' => 'int'),
			array('field' => 'EvnUslugaPar_setDate','label' => 'Дата начала оказания услуги','rules' => 'trim|required','type' => 'date'),
			array('field' => 'EvnUslugaPar_setTime','label' => 'Время начала оказания услуги','rules' => 'trim','type' => 'time'),
			array('field' => 'EvnUslugaPar_disDate','label' => 'Дата окончания оказания услуги','rules' => 'trim','type' => 'date'),
			array('field' => 'EvnUslugaPar_disTime','label' => 'Время окончания оказания услуги','rules' => 'trim','type' => 'time'),
			array('field' => 'Lpu_uid','label' => 'МО','rules' => '','type' => 'id'),
			array('field' => 'LpuSectionProfile_id','label' => 'Профиль','rules' => '','type' => 'id'),
			array('field' => 'MedSpecOms_id','label' => 'Специальность','rules' => '','type' => 'id'),
			array('field' => 'EvnUslugaPar_MedPersonalCode','label' => 'Код врача','rules' => '','type' => 'string'),
			array('field' => 'Lpu_did','label' => 'Идентификатор направившего ЛПУ','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_did','label' => 'Идентификатор направившего отделения','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_uid','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_did','label' => 'Направивший врач','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_uid','label' => 'Врач','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_sid','label' => 'Код и ФИО среднего мед. персонала','rules' => '','type' => 'id'),
			array('field' => 'MedStaffFact_uid','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
			array('field' => 'Org_did','label' => 'Идентификатор направившей организации','rules' => '','type' => 'id'),
			array('field' => 'Org_uid','label' => 'Идентификатор другой направившей организации','rules' => '','type' => 'id'),
			array('field' => 'PayType_id','label' => 'Вид оплаты','rules' => 'required','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'required','type' => 'id'),
			array('field' => 'PersonEvn_id','label' => 'Идентификатор состояния пациента','rules' => 'required','type' => 'id'),
			array('field' => 'UslugaPlace_id','label' => 'Место выполнения','rules' => '','type' => 'id'),
			array('field' => 'PrehospDirect_id','label' => 'Кем направлен','rules' => '','type' => 'id'),
			array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
			array('field' => 'Usluga_id','label' => 'Услуга','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorStage_id', 'label' => 'Стадия выявленного ЗНО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplexTariff_id','label' => 'Тариф','rules' => '','type' => 'id'),
			array('field' => 'EvnCostPrint_setDT', 'label' => 'Дата выдачи справки/отказа', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnCostPrint_IsNoPrint', 'label' => 'Отказ', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_IndexRep', 'label' => 'Признак повторной подачи', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnUslugaPar_IndexRepInReg', 'label' => 'Признак повторной подачи в реестре', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedProductCard_id', 'label' => 'Медицинское изделие', 'rules' => '', 'type' => 'id')
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_EvnUsluga_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/EvnUsluga/Order",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Сохранение заказа услуги",
	 *     	@OA\RequestBody(
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
	 *     					property="EvnPrescr_id",
	 *     					description="Идентификатор назначения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Person_id",
	 *     					description="Идентификатор человека",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PersonEvn_id",
	 *     					description="Идентификатор периодики",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="order",
	 *     					description="Заказ услуг",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"Person_id",
	 *     					"PersonEvn_id",
	 *     					"order"
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
	function Order_post()
	{
		$data = $this->ProcessInputData('saveUslugaOrder', null, true);
		$response = $this->dbmodel->saveUslugaOrder($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/UslugaByPrescr",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Поиск услуг по назначению",
	 *     	@OA\Parameter(
	 *     		name="EvnPrescr_id",
	 *     		in="query",
	 *     		description="Идентификатор назначения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function UslugaByPrescr_get()
	{
		$data = $this->ProcessInputData('getUslugaByPrescr', null, true);
		$response = $this->dbmodel->getUslugaByPrescr($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/EvnParams",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение данных события",
	 *     	@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор события",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EvnParams_get()
	{
		$data = $this->ProcessInputData('getEvnParams', null, true);
		$response = $this->dbmodel->getEvnParams($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ReagentCountByDate",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение списка реактивов и их количества, для конкретной службы на конкретную дату",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Date",
	 *     		in="query",
	 *     		description="Дата",
	 *     		required=true,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PayType_id",
	 *     		in="query",
	 *     		description="Идентификатор вида оплаты",
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
	function ReagentCountByDate_get()
	{
		$data = $this->ProcessInputData('getReagentCountByDate', null, false);
		$response = $this->dbmodel->getReagentCountByDate($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ReagentAutoRateCountOnAnalyser",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение списка услуг для панели направлений в ЭМК",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64", default=0)
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="begDate",
	 *     		in="query",
	 *     		description="Начало периода",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="endDate",
	 *     		in="query",
	 *     		description="Окончание периода",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ReagentAutoRateCountOnAnalyser_get()
	{
		$data = $this->ProcessInputData('getReagentAutoRateCountOnAnalyser', null, false);
		$response = $this->dbmodel->getReagentAutoRateCountOnAnalyser($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/DataForResults",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение данных для создания протокола результатов услуги",
	 *     	@OA\Parameter(
	 *     		name="Evn_id",
	 *     		in="query",
	 *     		description="Идентификатор события",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function DataForResults_get()
	{
		$data = $this->ProcessInputData('getDataForResults', null, false);
		$response = $this->dbmodel->getDataForResults($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ParNodeList",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Список результатов исследований для отображения в дереве ЭМК",
	 *     	@OA\Parameter(
	 *     		name="EvnSection_id",
	 *     		in="query",
	 *     		description="Идентификатор движения",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnVizitPL_id",
	 *     		in="query",
	 *     		description="Идентификатор посещения",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnVizitPLStom_id",
	 *     		in="query",
	 *     		description="Идентификатор посещения стоматалогии",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnPS_id",
	 *     		in="query",
	 *     		description="Идентификатор КВС",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnPL_id",
	 *     		in="query",
	 *     		description="Идентификатор ТАП",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnPLStom_id",
	 *     		in="query",
	 *     		description="Идентификатор ТАП стоматалогии",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnPLDispMigrant_id",
	 *     		in="query",
	 *     		description="Идентификатор талона освидетельствования",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnPLDispDriver_id",
	 *     		in="query",
	 *     		description="Идентификатор талона освидетельствования",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="type",
	 *     		in="query",
	 *     		description="Тип отображения",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="except_ids",
	 *     		in="query",
	 *     		description="Список идентификаторов для исключения из запроса",
	 *     		@OA\Schema(type="string", format="json")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ParNodeList_get()
	{
		$data = $this->ProcessInputData('getEvnUslugaParNodeList', null, true);
		$response = $this->dbmodel->getEvnUslugaParNodeList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ParViewData",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение данных о параклинической услуге для отображения в ЭМК",
	 *     	@OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ParViewData_get()
	{
		$data = $this->ProcessInputData('getEvnUslugaParViewData', null, true);
		$response = $this->dbmodel->getEvnUslugaParViewData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ParEditForm",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение данных о параклинической услуге для формы редактирования",
	 *     	@OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ParEditForm_get()
	{
		$data = $this->ProcessInputData('loadEvnUslugaParEditForm', null, true);
		$response = $this->dbmodel->loadEvnUslugaParEditForm($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ParSimpleEditForm",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение данных о параклинической услуге для простой формы редактирования",
	 *     	@OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ParSimpleEditForm_get()
	{
		$data = $this->ProcessInputData('loadEvnUslugaParSimpleEditForm', null, true);
		$response = $this->dbmodel->loadEvnUslugaParSimpleEditForm($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}


	function ParSaveData_get() {
		$data = $this->ProcessInputData('loadEvnUslugaParSaveData', null, true);
		$response = $this->dbmodel->loadEvnUslugaParSaveData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	function ParFull_post() {
		$data = $this->ProcessInputData('saveEvnUslugaParFull', null, true);
		$response = $this->dbmodel->saveEvnUslugaParFull($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     path="/api/EvnUsluga/Par",
	 *     tags={"EvnLabSample"},
	 *     summary="Изменение параклинической услуги",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_pid",
	 *     					description="Идентификатор родительского события услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_setDate",
	 *     					description="Дата оказания услуги",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_setTime",
	 *     					description="Время оказания услуги",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="ignoreKSGChangeCheck",
	 *     					description="Признак игнорирования проверки изменения КСГ",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"EvnUslugaPar_id",
	 *	 					"EvnUslugaPar_setDate"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Par_put()
	{
		$data = $this->ProcessInputData('editEvnUslugaPar', null, true);
		$response = $this->dbmodel->editEvnUslugaPar($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/Count",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение количества услуг",
	 *     	@OA\Parameter(
	 *     		name="EvnUsluga_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Count_get()
	{
		$data = $this->ProcessInputData('loadEvnUslugaCount', null, true);
		$response = $this->dbmodel->loadEvnUslugaCount($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/Panel",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение списка услуг в ЭМК",
	 *     	@OA\Parameter(
	 *     		name="EvnUsluga_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Panel_get()
	{
		$data = $this->ProcessInputData('loadEvnUslugaPanel', null, true);
		$response = $this->dbmodel->loadEvnUslugaPanel($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ParPanel",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение списка исследований в ЭМК",
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnUslugaPar_rid",
	 *     		in="query",
	 *     		description="Идентификатор корневого события",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="start",
	 *     		in="query",
	 *     		description="Начало",
	 *     		@OA\Schema(type="interger", default=0)
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="limit",
	 *     		in="query",
	 *     		description="Количество",
	 *     		@OA\Schema(type="interger", default=100)
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ParPanel_get()
	{
		$data = $this->ProcessInputData('loadEvnUslugaParPanel', null, true);
		$response = $this->dbmodel->loadEvnUslugaParPanel($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ParPersonHistory",
	 *  	tags={"EvnUsluga"},
	 *	    summary="История болезни для новой ЭМК",
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="person_in",
	 *     		in="query",
	 *     		description="Список идентификаторов людей",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="userLpuUnitType_SysNick",
	 *     		in="query",
	 *     		description="Тип группы отделений",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="useArchive",
	 *     		in="query",
	 *     		description="Признак архивных записей",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ParPersonHistory_get()
	{
		$data = $this->ProcessInputData('getEvnUslugaParPersonHistory', null, true);
		$response = $this->dbmodel->getEvnUslugaParPersonHistory($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ListForEvnXml",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение списка услуг для вставки их протоколов через маркер документа",
	 *     	@OA\Parameter(
	 *     		name="EvnUsluga_pids",
	 *     		in="query",
	 *     		description="Список идентификаторов родительских событий",
	 *     		required=true,
	 *     		@OA\Schema(type="string", format="json")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplexAttributeType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа атрибута услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="XmlDataLevel_SysNick",
	 *     		in="query",
	 *     		description="Уровень",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="code2011list",
	 *     		in="query",
	 *     		description="Список кодов ГОСТ-2011",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ListForEvnXml_get()
	{
		$data = $this->ProcessInputData('getEvnUslugaListForEvnXml', null, true);
		$response = $this->dbmodel->getEvnUslugaListForEvnXml($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/Grid",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение списка услуг",
	 *     	@OA\Parameter(
	 *     		name="class",
	 *     		in="query",
	 *     		description="Класс услуги",
	 *     		@OA\Schema(type="string", default="EvnUsluga")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="parent",
	 *     		in="query",
	 *     		description="Родительский класс",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="pid_list",
	 *     		in="query",
	 *     		description="Идентификатор родительского события",
	 *     		@OA\Schema(type="string", format="json")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="rid",
	 *     		in="query",
	 *     		description="Идентификатор корневого родительского события",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Grid_get()
	{
		$data = $this->ProcessInputData('loadEvnUslugaGrid', null, true);
		$response = $this->dbmodel->loadEvnUslugaGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/ViewData",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение данных услуг для отображения в ЭМК",
	 *     	@OA\Parameter(
	 *     		name="EvnUsluga_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnUsluga_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function ViewData_get() {
		$data = $this->ProcessInputData('getEvnUslugaViewData', null, true);
		$response = $this->dbmodel->getEvnUslugaViewData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnUsluga/EvnUslugaParInfo",
	 *  	tags={"EvnUsluga"},
	 *	    summary="Получение данных пар. услуги для создания листа согласования",
	 *     	@OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function EvnUslugaParInfo_get() {
		$data = $this->ProcessInputData('getEvnUslugaParInfo', null, true);
		$response = $this->dbmodel->getEvnUslugaParInfo($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/*
	 * описать
	 */
	function getEvnUslugaList_get() {
		$data = $this->ProcessInputData('getEvnUslugaList');
		$response = $this->dbmodel->getEvnUslugaList($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	function EvnUslugaParByEvnDirection_get() {
		$data = $this->ProcessInputData('getEvnUslugaParByEvnDirection');
		$response = $this->dbmodel->getEvnUslugaParByEvnDirection($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
