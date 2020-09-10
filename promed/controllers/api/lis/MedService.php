<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class MedService
 * @property Lis_MedService_model dbmodel
 * @OA\Tag(
 *     name="MedService",
 *     description="Службы"
 * )
 */
class MedService extends SwRest_Controller {
	protected $inputRules = array(
		'createMedServiceRefSample' => array(
			array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
			array('field' => 'RefMaterial_id','label' => 'Идентификатор биоматериала','rules' => '','type' => 'id'),
			array('field' => 'ContainerType_id','label' => 'Идентификатор типа контейнера','rules' => '','type' => 'id'),
			array('field' => 'RefSample_Name','label' => 'Наименование пробы','rules' => '','type' => 'string','default' => ''),
			array('field' => 'Usluga_ids','label' => 'Идентификаторы услуг, объединяемых в пробу','rules' => 'required','type' => 'string'),
            array('field' => 'UslugaComplexMedService_IsSeparateSample','label' => 'Флаг отдельной пробы','rules' => '','type' => 'string')
		),
		'loadUslugaComplexMedServiceGrid' => array(
			array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id'),
			array('field' => 'UslugaComplexMedService_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'Urgency_id','label' => 'Актуальность','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplex_pid','label' => 'Идентификатор','rules' => '','type' => 'id')
		),
		'getApproveRights' => array(
			array('field' => 'MedServiceMedPersonal_id','label' => 'Идентификатор врача службы','rules' => '','type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => '','type' => 'id'),
		),
		'loadMedServiceList' => array(
			array('field' => 'Lpu_isAll','label' => 'Все ЛПУ?','rules' => '','type' => 'id','default' => 0),
			array('field' => 'MedService_id','label' => 'Идентификатор службы', 'rules' => '','type' => 'id'),
			array('field' => 'ARMType','label' => 'Тип арма','rules' => '','type' => 'string'),
			array('field' => 'MedServiceTypeIsLabOrFenceStation','label' => 'Тип службы - Лаборатории и Пункты забора', 'rules' => '','type' => 'id')
		),
		'loadMedServiceGrid' => array(
			array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => '','type' => 'id'),
			array('field' => 'MedService_sid','label' => 'Идентификатор службы','rules' => 'required','type' => 'id')
		),
		'loadEditForm' => array(
			array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id')
		),
        'saveUslugaComplexMedServiceIsSeparateSample' => [
            [
                'field' => 'UslugaComplexMedService_id',
                'label' => 'ID связи услуги и мед.службы',
                'rules' => 'required',
                'type' => 'id'
            ],
            [
                'field' => 'UslugaComplex_id',
                'label' => 'ID комплексной услуги',
                'rules' => 'required',
                'type' => 'id'
            ],
            [
                'field' => 'UslugaComplexMedService_IsSeparateSample',
                'label' => 'Флаг отдельной пробы',
                'rules' => 'required',
                'type' => 'boolean'
            ],
        ]
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_MedService_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/MedService/createRefSample",
	 *  	tags={"MedService"},
	 *	    summary="Объединение услуг в пробу",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefMaterial_id",
	 *     					description="Идентификатор биоматериала",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefSample_Name",
	 *     					description="Наименование пробы",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Usluga_ids",
	 *     					description="Список идентификаторов услуг",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"MedService_id",
	 *     					"RefSample_Name",
	 *     					"Usluga_ids"
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
	function createRefSample_POST() {
		$data = $this->ProcessInputData('createMedServiceRefSample', null, true);
		$response = $this->dbmodel->createMedServiceRefSample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/MedService/loadEditForm",
	 *  	tags={"MedService"},
	 *	    summary="Загрузка информации о службе для формы редактирования",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"MedService_id"
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
	function loadEditForm_POST() {
		$data = $this->ProcessInputData('loadEditForm', null, true);
		$this->load->model('MedService_model', 'MedService_model');
		$response = $this->MedService_model->loadEditForm($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/MedService/UslugaComplexMedServiceGridChild",
	 *  	tags={"MedService"},
	 *	    summary="Получение состава услуг для настройки проб и биоматериала",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplexMedService_id",
	 *     		in="query",
	 *     		description="Идентификатор связи услуги и службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Urgency_id",
	 *     		in="query",
	 *     		description="Идентификатор актуальности",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_pid",
	 *     		in="query",
	 *     		description="Идентификатор родителской услуги",
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
	function UslugaComplexMedServiceGridChild_get() {
		$data = $this->ProcessInputData('loadUslugaComplexMedServiceGrid', null, true);
		$response = $this->dbmodel->loadUslugaComplexMedServiceGridChild($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/MedService/UslugaComplexMedServiceGrid",
	 *  	tags={"MedService"},
	 *	    summary="Получение состава услуг для формы добавления исследования к заявке в арм лаборанта",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplexMedService_id",
	 *     		in="query",
	 *     		description="Идентификатор связи услуги и службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Urgency_id",
	 *     		in="query",
	 *     		description="Идентификатор актуальности",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="UslugaComplex_pid",
	 *     		in="query",
	 *     		description="Идентификатор родителской услуги",
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
	function UslugaComplexMedServiceGrid_get() {
		$data = $this->ProcessInputData('loadUslugaComplexMedServiceGrid', null, true);
		$response = $this->dbmodel->loadUslugaComplexMedServiceGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/MedService/ApproveRights",
	 *  	tags={"MedService"},
	 *	    summary="Получить права врача на данной службе (право одобрять пробы)",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="MedServiceMedPersonal_id",
	 *     		in="query",
	 *     		description="Идентификатор врача службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="MedPersonal_id",
	 *     		in="query",
	 *     		description="Идентификатор врача",
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
	function ApproveRights_get() {
		$data = $this->ProcessInputData('getApproveRights', null, true);
		$response = $this->dbmodel->getApproveRights($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     	path="/api/MedService/MedServiceList",
	 *  	tags={"MedService"},
	 *	    summary="Читает для комбобокса MedService",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     @OA\Parameter(
	 *     		name="Lpu_isAll",
	 *     		in="query",
	 *     		description="Все ЛПУ?",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     @OA\Parameter(
	 *     		name="ARMType",
	 *     		in="query",
	 *     		description="Тип арма",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="MedServiceTypeIsLabOrFenceStation",
	 *     		in="query",
	 *     		description="Тип службы - Лаборатории и Пункты забора",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *      @OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function MedServiceList_get() {
		$data = $this->ProcessInputData('loadMedServiceList', null, true);
		$response = $this->dbmodel->loadMedServiceList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     	path="/api/MedService/MedServiceGrid",
	 *  	tags={"MedService"},
	 *	    summary="Получение списка служб",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="MedService_sid",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *      @OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function MedServiceGrid_get() {
		$data = $this->ProcessInputData('loadMedServiceGrid', null, true);
		$response = $this->dbmodel->loadMedServiceGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

    /**
     * @OA\Post(
     *     	path="/api/MedService/saveUslugaComplexMedServiceIsSeparateSample",
     *  	tags={"MedService"},
     *	    summary=" Сохранение отдельная проба услуги",
     *     	@OA\RequestBody(
     *			required=true,
     *     		@OA\MediaType(
     *     			mediaType="application/x-www-form-urlencoded",
     *     			@OA\Schema(
     *     				@OA\Property(
     *     					property="UslugaComplexMedService_id",
     *     					description="ID связи услуги и мед.службы",
     *     					type="integer"
     * 					),
     *     				@OA\Property(
     *     					property="UslugaComplex_id",
     *     					description="ID комплексной услуги",
     *     					type="integer"
     * 					),
     *     				@OA\Property(
     *     					property="UslugaComplexMedService_IsSeparateSample",
     *     					description="Флаг отдельной пробы",
     *     					type="boolean"
     * 					),
     *     				required={
     *     					"UslugaComplexMedService_id",
     *     					"UslugaComplex_id",
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
    function saveUslugaComplexMedServiceIsSeparateSample_POST() {
        $data = $this->ProcessInputData('saveUslugaComplexMedServiceIsSeparateSample', null, true);
        $response = $this->dbmodel->saveUslugaComplexMedServiceIsSeparateSample($data);

        if ($response) {
            $this->response(['error_code' => 0, 'data' => $response]);
        } else {
            $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
