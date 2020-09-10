<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPrescr - контроллер API для работы с назначениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Maksim Sysolin
 */

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnPrescr
 * @property Lis_EvnPrescr_model dbmodel
 * @OA\Tag(
 *     name="EvnPrescr",
 *     description="Назначения"
 * )
 */
class EvnPrescr extends SwREST_Controller {
	protected  $inputRules = array(
		'deleteFromDirection' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => '','type' => 'id'),
			array('field' => 'EvnPrescr_IsExec','label' => 'Признак выполнения','rules' => '','type' => 'id'),
			array('field' => 'EvnStatus_id','label' => 'Идентификатор статуса направления','rules' => '','type' => 'id'),
			array('field' => 'DirType_id','label' => 'Идентификатор типа направления','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Идентификатор удаляемой услуги','rules' => '','type' => 'id'),
			array('field' => 'couple','label' => 'Несколько исследований в направлении','rules' => '','type' => 'boolean')
		)
	);

	protected $prescriptions = array(
		1 => 'EvnPrescrRegime',
		2 => 'EvnPrescrDiet',
		6 => 'EvnPrescrProc',
		13 => 'EvnPrescrConsUsluga',
		12 => 'EvnPrescrFuncDiag',
		11 => 'EvnPrescrLabDiag',
		5 => 'EvnPrescrTreat',
		7 => 'EvnPrescrOper'
	);

	// атрибут для получения списка услуг
	protected $prescr_attribute = array(
		6 => 'manproc',
		13 => 'consult',
		11 => 'lab',
		12 => 'func',
		7 => 'oper'
	);

	// типы длительностей ЛС
	protected $prescr_treat_duration = array(
		1 => 1,
		2 => 7,
		3 => 30
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_EvnPrescr_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnPrescr/deleteFromDirection",
	 *     tags={"EvnPrescr"},
	 *     summary="Исключение из направления",
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
	 *     					property="parentEvnClass_SysNick",
	 *     					description="Системное имя события, породившего назначение",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PrescriptionType_id",
	 *     					description="Тип назначения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnPrescr_IsExec",
	 *     					description="Признак выполнения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnStatus_id",
	 *     					description="Идентификатор статуса направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="DirType_id",
	 *     					description="Идентификатор типа направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор удаляемой услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="couple",
	 *     					description="Несколько исследований в направлении",
	 *     					type="boolean"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *		response="200",
	 *		description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function deleteFromDirection_post() {
		$data = $this->ProcessInputData('deleteFromDirection', null, true);
		$response = $this->dbmodel->deleteFromDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}
}
