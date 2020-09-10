<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы со спецификой о новорождённом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class BirthSpecStac extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('BirthSpecStac_model', 'dbmodel');
		$this->inputRules = array(
			'getBirthSpecStac' => array(
				array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения матери, из которого добавлена специфика', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PregnancyResult_id', 'label' => 'Тип результата беременности', 'rules' => '', 'type' => 'id')
			),
			'mCheckingMovementAndPregnancyOutcome' => array(
				array('field' => 'EvnSection_id', 'label' => 'Движение', 'rules' => 'required', 'type' => 'id')
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getBirthSpecStac');

		$resp = $this->dbmodel->getBirthSpecStacForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	/**
	*@OA\get(
			path="/api/rish/BirthSpecStac/mCheckingMovementAndPregnancyOutcome",
			tags={"BirthSpecStac"},
			summary="метод проверки связи движения и исхода беременности",

		@OA\Parameter(
			name="EvnSection_id",
			in="query",
			description="Движение",
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
			description="данные",
			type="array",

		@OA\Items(
			type="object",

		@OA\Property(
			property="BirthSpecStac_OutcomDT",
			description="Специфика по родам в карте КВС, Дата и время исхода",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpecStac_CountPregnancy",
			description="Специфика по родам в карте КВС, Которая беременность",
			type="string",

		)
	,				 
		@OA\Property(
			property="PregnancyResult_Name",
			description="Справочник типов результатов беременности, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpecStac_OutcomPeriod",
			description="Специфика по родам в карте КВС, Срок исхода (нед)",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpecStac_CountChild",
			description="Специфика по родам в карте КВС, Количество плодов",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpecStac_BloodLoss",
			description="Специфика по родам в карте КВС, Кровопотери (мл)",
			type="string",

		)
	,				 
		@OA\Property(
			property="AbortLpuPlaceType_Name",
			description="Место аборта, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="AbortLawType_Name",
			description="Вид аборта (легальный/криминальный), наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="AbortMethod_Name",
			description="Метод аборта, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="AbortIndicat_Name",
			description="Показания при оборте, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpecStac_InjectVMS",
			description="Специфика по родам в карте КВС, Введено ВМС",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthPlace_Name",
			description="Справочник свидетельств о рождении: место принятия родов, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpecStac_CountBirth",
			description="Специфика по родам в карте КВС, Роды которые",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpec_Name",
			description="Справочник спецификаций родов, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthCharactType_Name",
			description="Справочник типов результатов родов, наименование",
			type="string",

		)
	,				 
		@OA\Property(
			property="BirthSpecStac_CountChildAlive",
			description="Специфика по родам в карте КВС, Количество плодов живорожденных",
			type="string",

		)

		)

		)

			)
		)

		)
	 */
	function mCheckingMovementAndPregnancyOutcome_get(){
		//метод проверки связи движения и исхода беременности
		$data = $this->ProcessInputData('mCheckingMovementAndPregnancyOutcome');

		$resp = $this->dbmodel->mCheckingMovementAndPregnancyOutcomeForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$error_code = (count($resp)>0) ? 0 : 1;

		$this->response(array(
			'error_code' => $error_code,
			'data' => $resp
		));
	}
}