<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Person
 * @property Person_model dbmodel
 * @OA\Tag(
 *     name="Person",
 *     description="Люди"
 * )
 */
class Person extends SwRest_Controller {
	protected $inputRules = array(
		'getPersonSearchGrid' => array(
			array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
			array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
			array('field' => 'getPersonWorkFields', 'label' => 'Признак необходимости вытаскивать наименование организации', 'rules' => '', 'type' => 'int'),
			array('field' => 'ParentARM', 'label' => 'Тип арма, вызвавшего метод', 'rules' => '', 'type' => 'string'),
			array('field' => 'soc_card_id', 'label' => 'Идентификатор социальной карты', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_id', 'label' => 'Персональный идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'showAll', 'label' => 'Показывать всех', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonSurName_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonFirName_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonSecName_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonBirthDay_BirthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'personBirtDayFrom', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'personBirtDayTo', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'PersonAge_AgeFrom', 'label' => 'Возраст с', 'rules' => '', 'type' => 'int'),
			array('field' => 'PersonAge_AgeTo', 'label' => 'Возраст по', 'rules' => '', 'type' => 'int'),
			array('field' => 'PersonBirthYearFrom', 'label' => 'Год рождения с', 'rules' => '', 'type' => 'int'),
			array('field' => 'PersonBirthYearTo', 'label' => 'Год рождения по', 'rules' => '', 'type' => 'int'),
			array('field' => 'Person_Snils', 'label' => 'СНИЛС', 'rules' => '', 'type' => 'snils'),
			array('field' => 'Person_Inn', 'label' => 'ИНН', 'rules' => '', 'type' => 'int'),
			array('field' => 'PersonCard_id', 'label' => 'Идентификатор карты', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonCard_Code', 'label' => 'Код карты', 'rules' => '', 'type' => 'string'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'PolisFormType_id', 'label' => 'Форма полиса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'Polis_EdNum', 'label' => 'Единый номер полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnUdost_Ser', 'label' => 'Серия полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnUdost_Num', 'label' => 'Номер полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_NumCard', 'label' => 'Номер КВС', 'rules' => '', 'type' => 'string'),
			array('field' => 'searchMode', 'label' => 'Режим поиска', 'rules' => '', 'type' => 'string'),
			array('field' => 'Year', 'label' => 'Год включения в регистр', 'rules' => '', 'type' => 'string'),
			array('field' => 'Sex_id', 'label' => 'Пол', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonRegisterType_id', 'label' => 'Тип регистра', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugRequestPeriod_id', 'label' => 'Рабочий период заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_id', 'label' => 'Участок', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonRefuse_IsRefuse', 'label' => 'Отказ от льготы', 'rules' => '', 'type' => 'id'),
			array('field' => 'search_type', 'label' => 'Тип поиска', 'rules' => '', 'type' => 'string'),
			array('field' => 'oneQuery', 'label' => 'Отсутствие запроса count', 'rules' => '', 'type' => 'string'),
			array('field' => 'checkForMainDB', 'label' => 'Проверка на основной базе', 'rules' => '', 'type' => 'boolean'),
			array('field' => 'Person_ids', 'label' => 'Список Person_id ограничивающих поиск', 'rules' => '', 'type' => 'string'),
			array('field' => 'getCountOnly', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'isNotDead', 'label' => 'isNotDead', 'rules' => '', 'type' => 'id'),
		),
		'loadPersonData' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_Person_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Person",
	 *  	tags={"Person"},
	 *	    summary="Получение данных человека",
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
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
	function index_get() {
		$data = $this->ProcessInputData('loadPersonData', null, true);
		$response = $this->dbmodel->loadPersonData($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Person/search",
	 *  	tags={"Person"},
	 *	    summary="Поиск людей",
	 *     	@OA\Parameter(
	 *     		name="start",
	 *     		in="query",
	 *     		description="Номер начальной записи",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", default=0)
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="limit",
	 *     		in="query",
	 *     		description="Количество записей",
	 *     		@OA\Schema(type="integer", default=100)
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="getPersonWorkFields",
	 *     		in="query",
	 *     		description="Признак необходимости вытаскивать наименование организации",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="getPersonWorkFields",
	 *     		in="query",
	 *     		description="Признак необходимости вытаскивать наименование организации",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="ParentARM",
	 *     		in="query",
	 *     		description="Тип АРМа",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="soc_card_id",
	 *     		in="query",
	 *     		description="Идентификатор социальной карты",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="showAll",
	 *     		in="query",
	 *     		description="Показать всех",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonSurName_SurName",
	 *     		in="query",
	 *     		description="Фамилия",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonFirName_FirName",
	 *     		in="query",
	 *     		description="Имя",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonSecName_SecName",
	 *     		in="query",
	 *     		description="Отчество",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonBirthDay_BirthDay",
	 *     		in="query",
	 *     		description="День рождения",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="personBirtDayFrom",
	 *     		in="query",
	 *     		description="День рождения с",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="personBirtDayTo",
	 *     		in="query",
	 *     		description="День рождения по",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonAge_AgeFrom",
	 *     		in="query",
	 *     		description="Вовраст с",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonAge_AgeTo",
	 *     		in="query",
	 *     		description="Вовраст по",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonBirthYearFrom",
	 *     		in="query",
	 *     		description="Год рождения с",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonBirthYearTo",
	 *     		in="query",
	 *     		description="Год рождения по",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Person_Snils",
	 *     		in="query",
	 *     		description="СНИЛС",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Person_Snils",
	 *     		in="query",
	 *     		description="СНИЛС",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Person_Inn",
	 *     		in="query",
	 *     		description="ИНН",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonCard_id",
	 *     		in="query",
	 *     		description="Идентифкатор прикрепления",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonCard_Code",
	 *     		in="query",
	 *     		description="Код прикрепления",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PolisFormType_id",
	 *     		in="query",
	 *     		description="Идентифкатор формы полиса",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Polis_Ser",
	 *     		in="query",
	 *     		description="Серия полиса",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Polis_Num",
	 *     		in="query",
	 *     		description="Номер полиса",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Polis_EdNum",
	 *     		in="query",
	 *     		description="Единый номер полиса",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnUdost_Ser",
	 *     		in="query",
	 *     		description="Серия полиса",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnUdost_Num",
	 *     		in="query",
	 *     		description="Номер полиса",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnPS_NumCard",
	 *     		in="query",
	 *     		description="Номер КВС",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="searchMode",
	 *     		in="query",
	 *     		description="Режим поиска",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Year",
	 *     		in="query",
	 *     		description="Год включения в регистр",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Sex_id",
	 *     		in="query",
	 *     		description="Идентфикатор пола",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonRegisterType_id",
	 *     		in="query",
	 *     		description="Идентификатор типа регситра",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="DrugRequestPeriod_id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего периода заявки",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="LpuRegion_id",
	 *     		in="query",
	 *     		description="Идентификатор участка",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="PersonRefuse_IsRefuse",
	 *     		in="query",
	 *     		description="Признак отказа от льготы",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="search_type",
	 *     		in="query",
	 *     		description="Тип поиска",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="oneQuery",
	 *     		in="query",
	 *     		description="Отсутствие запроса count",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="checkForMainDB",
	 *     		in="query",
	 *     		description="Проверка на основной базе",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Person_ids",
	 *     		in="query",
	 *     		description="Список идентификаторов людей",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="armMode",
	 *     		in="query",
	 *     		description="АРМ вызова запроса",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="getCountOnly",
	 *     		in="query",
	 *     		description="Вывод только количества",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="isNotDead",
	 *     		in="query",
	 *     		description="",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function search_get() {
		$data = $this->ProcessInputData('getPersonSearchGrid', null, true);

		$keys = [
			'PersonSurName_SurName',
			'PersonFirName_FirName',
			'PersonSecName_SecName'
		];
		foreach($keys as $key) {
			if (isset($data[$key])) {
				$data[$key] = json_decode($data[$key], true);
				$data[$key] = str_replace('*', ' ', $data[$key]);
			}
		}

		$response = $this->dbmodel->getPersonSearchGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
