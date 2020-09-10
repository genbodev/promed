<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnSection
 * @property EvnSection_model dbmodel
 */
class EvnSection extends SwRest_Controller {
	protected $inputRules = array(
		'recalcKSGKPGKOEF' => array(
			array('field' => 'EvnSection_id','label' => 'Идентификатор движения','rules' => 'required','type' => 'id')
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnSection_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnSection/recalcKSGKPGKOEF",
	 *     tags={"EvnSection"},
	 *     summary="Пересчёт КСГ/КПГ/Коэф в движении после сохранения КВС, услуг, удаления услуг",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnSection_id",
	 *     					description="Идентификатор движения",
	 *     					type="integer"
	 * 					),
	 *     				required={"EvnSection_id"}
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
	function recalcKSGKPGKOEF_post() {
		$data = $this->ProcessInputData('recalcKSGKPGKOEF', null, true);
		$response = $this->dbmodel->recalcKSGKPGKOEF($data['EvnSection_id'], $data['session']);
		if ($response !== true) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0));
	}
}
