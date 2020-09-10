<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы со справочниками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @OA\Tag(
 *     name="Refbook",
 *     description="Методы работы со справочниками"
 * )
 */

class Refbook extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Refbook_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Получение справочника
	 */
	function index_get() {
		$data = $this->ProcessInputData('loadRefbook');

		$resp = $this->dbmodel->loadRefbook($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение справочника услуг
	 */
	function RefbookUslugaComplex_get() {
		$data = $this->ProcessInputData('loadRefbookUslugaComplex');

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6'
			));
		}

		$sp = getSessionParams();
		$data['session'] = $sp['session'];
		if (empty($GLOBALS['isSwanApiKey']) && !empty($data['Lpu_id']) && $sp['Lpu_id'] != $data['Lpu_id']) {
			$this->response(array(
				'error_msg' => 'Услуги другой МО получить нельзя',
				'error_code' => '6'
			));
		}


		$resp = $this->dbmodel->loadRefbookUslugaComplex($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение справочника диагнозов
	 */
	function mGetRefbookDiag_get() {

		$data = $this->ProcessInputData('mGetRefbookDiag');

		$resp = $this->dbmodel->loadRefbookDiag($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 *  Получение списка справочников
	 */
	function RefbookList_get() {

		$resp = $this->dbmodel->loadRefbookList();
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение списка справочников с актуальной датой обновления
	 *  берется из кэша, если время кэша не прошло
	 */
	function mRefbookList_get() {

		$data = $this->ProcessInputData('mRefbookList');

		$resp = $this->dbmodel->loadRefbookListAPI($data);
		if (!is_array($resp)) {$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 *  Загрузка справочников
	 */
	function mLoadRefbookData_post() {

		$data = $this->ProcessInputData('mLoadRefbookData');
		$data['fromMobile'] = true;

		$resp = $this->dbmodel->loadRefbookDataForApi($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (!empty($resp['Error_Msg'])) $this->response(array('error_code' => 6,'error_msg' => $resp['Error_Msg']));
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение исхода госпитализации по типу подразделения МО
	 */
	function LeaveTypeByLpuUnitType_get() {
		$data = $this->ProcessInputData('getLeaveTypeByLpuUnitType');

		$resp = $this->dbmodel->getLeaveTypeByLpuUnitType($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение исхода заболевания по типу подразделения МО
	 */
	function ResultDeseaseByLpuUnitType_get() {
		$data = $this->ProcessInputData('getResultDeseaseByLpuUnitType');

		$resp = $this->dbmodel->getResultDeseaseByLpuUnitType($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение справочника
	 */
	function RefbookMap_get() {
		$data = $this->ProcessInputData('loadRefbookMap');

		$resp = $this->dbmodel->loadRefbookMap($data);
		if (empty($resp)) {
			$this->response(array(
				'error_code' => 0
			));
		}

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение справочника
	 */
	function RefbookbyColumn_get() {
		$data = $this->ProcessInputData('loadRefbookbyColumn');

		$resp = $this->dbmodel->loadRefbook($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение справочника по столбцу (расширенный)
	 */
	function RefbookbyColumnExt_get() {
		$data = $this->ProcessInputData('loadRefbookbyColumnExt');

		$resp = $this->dbmodel->loadRefbookbyColumnExt($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение списка СМО
	 */
	function OrgSMO_get() {
		$data = $this->ProcessInputData('loadOrgSMOList');

		$resp = $this->dbmodel->loadOrgSMOList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка СМО
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"OrgSMO_id": "Идентификатор СМО в справочнике РИШ",
				"Org_id": "Идентификатор организации",
				"OrgSMO_RegNomC": null,
				"OrgSMO_RegNomN": null,
				"OrgSmo_Name": "Наименование СМО",
				"OrgSMO_Nick": "Краткое наименование СМО",
				"OrgSMO_isDMS": "Признак ДМС",
				"KLRgn_id": "Идентификатор региона",
				"OrgSMO_endDate": "Дата окончания",
				"OrgSMO_IsTFOMS": "Признак ТФОМС",
				"Orgsmo_f002smocod": "Код СМО в федеральном справочнике"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"OrgSMO_id": "8000385",
					"Org_id": "13023916",
					"OrgSMO_RegNomC": null,
					"OrgSMO_RegNomN": null,
					"OrgSmo_Name": "ЯРОСЛАВCКИЙ ФИЛИАЛ ОБЩЕСТВА С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ \"СТРАХОВАЯ МЕДИЦИНСКАЯ КОМПАНИЯ РЕСО-МЕД\"",
					"OrgSMO_Nick": "ЯРОСЛАВСКИЙ ФИЛИАЛ ООО \"СМК РЕСО-МЕД\"",
					"OrgSMO_isDMS": null,
					"KLRgn_id": "76",
					"OrgSMO_endDate": null,
					"OrgSMO_IsTFOMS": "1",
					"Orgsmo_f002smocod": "76001"
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadOrgSMO_get() {

		$data = $this->ProcessInputData('loadOrgSMOList');
		$resp = $this->dbmodel->loadOrgSMOListForAPI($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка территорий СМО
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"OmsSprTerr_id": "Идентификатор территории ОМС",
				"OmsSprTerr_Name": "Наименование территории",
				"OmsSprTerr_code": "Код территории по справочнику",
				"KLRgn_id": "Идентификатор региона"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"OmsSprTerr_id": "1",
					"OmsSprTerr_Name": "ПЕРМЬ",
					"OmsSprTerr_code": 1,
					"KLRgn_id": "59"
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadOmsSprTerrList_get() {

		$data = $this->ProcessInputData('loadOmsSprTerrList');
		$data['fromMobile'] = true;

		$resp = $this->dbmodel->loadOmsSprTerrList($data);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 *  Получение списка МО
	 */
	function Lpu_get() {
		$data = $this->ProcessInputData('loadLpuList');

		$resp = $this->dbmodel->loadLpuList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение элементов справочника КЛАДР
	 */
	function KLArea_get() {
		$data = $this->ProcessInputData('loadKLAreaList');

		$resp = $this->dbmodel->loadKLAreaList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение элементов справочника
	 */
	function RefbookOmsSprTerr_get() {

		$data = $this->ProcessInputData('loadOmsSprTerrList');

		if( ! empty($data['KLAdr_Code']) || ! empty($data['KLArea_id']) ){
            $resp = $this->dbmodel->loadOmsSprTerrList($data);
        }
        else {
            $this->response(array(
                'error_code' => 3,
                'error_msg' => "Отсутствует один из обязательных параметров: 'KLAdr_Code' или 'KLArea_id'"
            ));
        }

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение улиц справочника КЛАДР
	 */
	function KLStreet_get() {
		$data = $this->ProcessInputData('loadKLStreetList');

		$resp = $this->dbmodel->loadKLStreetList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение списка районов
	 */
	function KLSubRgn_get() {
		$data = $this->ProcessInputData('loadKLSubRgnList');

		if ( strlen($data['KLAdr_Code']) != 13 || !is_numeric($data['KLAdr_Code']) || substr($data['KLAdr_Code'], 2, 9) != '000000000' ) {
			$this->response(array(
				'error_msg' => 'Неверный код КЛАДР региона',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->loadKLSubRgnList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		else if ( count($resp) == 0 ) {
			$this->response(array(
				'error_msg' => 'Регионы не найдены',
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение справочника единиц измерения
	 */
	function RefbookGoodsUnit_get() {
		$data = $this->ProcessInputData('loadRefbookGoodsUnit');

		$resp = $this->dbmodel->loadRefbookGoodsUnit($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение справочника типов льгот
	 */
	function RefbookPrivilegeType_get() {
		$data = $this->ProcessInputData('loadRefbookPrivilegeType');

		$resp = $this->dbmodel->loadRefbookPrivilegeType($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение справочника типов льгот
	 */
	function RefbookDrugComplexMnn_get() {
		$data = $this->ProcessInputData('loadRefbookDrugComplexMnn');

		$resp = $this->dbmodel->loadRefbookDrugComplexMnn($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * @OA\get(
	path="/api/rish/refbook/mLoadRefbookUslugaComplex",
	tags={"refbook"},
	summary="Загрузка списка услуг с использованием фильтрации",

	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Идентификатор услуги",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_pid",
	in="query",
	description="Идентификатор услуги-родителя",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplexLevel_id",
	in="query",
	description="Уровень услуги",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Lpu_id",
	in="query",
	description="МО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Отделение МО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_Code",
	in="query",
	description="Код услуги",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_Name",
	in="query",
	description="Наименование услуги",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="UslugaCategory_id",
	in="query",
	description="Категория услуги",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedService_id",
	in="query",
	description="Идентификатор службы",
	required=false,
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
	description="Код ошибки",
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
	property="UslugaComplex_id",
	description="Комплексные услуги, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_pid",
	description="Комплексные услуги, идентификатор родительской услуги",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplexLevel_id",
	description="Уровни комплексных услуг, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="справочник ЛПУ, ЛПУ",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSection_id",
	description="Справочник ЛПУ: отделения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_Code",
	description="Комплексные услуги, код",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_Name",
	description="Комплексные услуги, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaCategory_id",
	description="Категория услуг, идентификатор",
	type="integer",

	)

	)

	)

	)
	)

	)
	 */
	function mLoadRefbookUslugaComplex_get() {

		$data = $this->ProcessInputData('mLoadRefbookUslugaComplex');

		$resp = $this->dbmodel->mLoadRefbookUslugaComplex($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (!empty($resp['Error_Msg'])) $this->response(array('error_code' => 6,'error_msg' => $resp['Error_Msg']));
		$this->response(array('error_code' => 0,'data' => $resp));
	}
}