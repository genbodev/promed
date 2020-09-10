<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Storage
 * @property Storage_model dbmodel
 */
class Storage extends SwRest_Controller {
	protected $inputRules = array(
		'mGetListStorage' => array(
			array('field' => 'Date', 'label' => 'текущая дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSection_id', 'label' => 'текущее отделение врача из сессии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescrTreatDrug_id', 'label' => 'идентификатор медикамента из назначения', 'rules' => 'required', 'type' => 'id'),
		),
		'mGetListMedicineStorage' => array(
			array('field' => 'Date', 'label' => 'текущая дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Storage_id', 'label' => 'текущее отделение врача из сессии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugPrepFas_id', 'label' => 'идентификатор медикамента из назначения', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Storage_model', 'dbmodel');
	}

	/**
	@OA\get(
			path="/api/rish/Storage/mGetListStorage",
			tags={"Storage"},
			summary="Метод получения списка складов",

		@OA\Parameter(
			name="Date",
			in="query",
			description="текущая дата",
			required=true,
			@OA\Schema(type="string", format="date")
		)
	,
		@OA\Parameter(
			name="LpuSection_id",
			in="query",
			description="текущее отделение врача из сессии",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnPrescrTreatDrug_id",
			in="query",
			description="идентификатор медикамента из назначения",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,

		@OA\Response(
			response="200",
			description="JSON response",
			@OA\JsonContent(
				type="object",

		@OA\Property(
			property="error_code",
			description="код ошибки",
			type="string",

		)
	,				 
		@OA\Property(
			property="data",
			description="Данные",
			type="array",

		@OA\Items(
			type="object",

		@OA\Property(
			property="Storage_id",
			description="Склад, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="StorageType_id",
			description="Тип склада, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Storage_Code",
			description="Склад, код",
			type="string",

		)
	,				 
		@OA\Property(
			property="Storage_Name",
			description="Склад, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="Storage_begDate",
			description="Склад, Дата открытия",
			type="string",

		)
	,				 
		@OA\Property(
			property="Storage_endDate",
			description="Склад, Дата закрытия",
			type="string",

		)
	,				 
		@OA\Property(
			property="LpuSection_id",
			description="Справочник ЛПУ: отделения, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="MedService_id",
			description="Cлужбы, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="MedServiceType_SysNick",
			description="Типы служб, системное наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="Org_id",
			description="Cправочник организаций, Идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Address",
			description="Адрес",
			type="string",

		)

		)

		)

			)
		)

		)
	 */
	function mGetListStorage_get(){
		//Метод получения списка складов
		$data = $this->ProcessInputData('mGetListStorage', null, true);
		$this->load->model('DocumentUc_model', 'DocumentUc_model');
		if(!empty($data['Date'])) $data['date'] = $data['Date'];
		$response = $this->DocumentUc_model->loadStorageList($data);
		if(!is_array($response)) {
			$this->response(array('error_code' => 6, 'error_msg' => "Ошибка при получении списка складов"));
		}
		// народ не хочет видеть поля, которые им не нужны, уберем их
		$result = array_map(function($res){
			unset($res['StorageStructLevel']);
			return $res;
		},$response);
		$this->response(array('error_code' => 0, 'data' => $result));
	}
	
	/**
	@OA\get(
			path="/api/rish/Storage/mGetListMedicineStorage",
			tags={"Storage"},
			summary="Метод получения списка медикаментов склада",

		@OA\Parameter(
			name="Date",
			in="query",
			description="текущая дата",
			required=true,
			@OA\Schema(type="string", format="date")
		)
	,
		@OA\Parameter(
			name="Storage_id",
			in="query",
			description="текущее отделение врача из сессии",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="DrugPrepFas_id",
			in="query",
			description="идентификатор медикамента из назначения",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,

		@OA\Response(
			response="200",
			description="JSON response",
			@OA\JsonContent(
				type="object",

		@OA\Property(
			property="error_code",
			description="код ошибки",
			type="string",

		)
	,				 
		@OA\Property(
			property="data",
			description="Данные",
			type="array",

		@OA\Items(
			type="object",

		@OA\Property(
			property="DrugPrep_Name",
			description="наименование медикамента",
			type="string",

		)
	,				 
		@OA\Property(
			property="DrugPrepFas_id",
			description="идентификатор медикамента",
			type="integer",

		)
	,				 
		@OA\Property(
			property="Storage_id",
			description="Склад, идентификатор",
			type="integer",

		)
	,				 
		@OA\Property(
			property="hintPackagingData",
			description="rls.Drug.DrugNomen",
			type="string",

		)
	,				 
		@OA\Property(
			property="hintRegistrationData",
			description="информация о регистрации",
			type="string",

		)
	,				 
		@OA\Property(
			property="hintPRUP",
			description="наименование фирмы, выпускающей препарат",
			type="string",

		)
	,				 
		@OA\Property(
			property="FirmNames",
			description="наименование фирмы, выпускающей препарат",
			type="string",

		)

		)

		)

			)
		)

		)
	 */
	function mGetListMedicineStorage_get(){
		//Метод получения списка медикаментов склада
		$this->load->model('EvnDrug_model', 'EvnDrug_model');
		$data = $this->ProcessInputData('mGetListMedicineStorage', null, true);
		$response = $this->EvnDrug_model->loadDrugPrepList($data);
		if(!is_array($response)) {
			$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка при получении списка медикаментов склада'));
		}
		// народ не хочет видеть поля, которые им не нужны, уберем их
		$result = array_map(function($res){
			unset($res['DrugPrep_id']);
			return $res;
		},$response);
		$this->response(array('error_code' => 0, 'data' => $result));
	}
}