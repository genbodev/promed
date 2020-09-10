<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class Org
 * @property Org_model dbmodel
 * @OA\Tag(
 *     name="Org",
 *     description="Организации"
 * )
 */
class Org extends SwRest_Controller {
	protected $inputRules = array(
		'getLpuList' => array(
			['field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'],
			['field' => 'Lpu_oid', 'label' => 'Идентификатор лпу', 'rules' => '', 'type' => 'id'],
			['field' => 'Org_Name', 'label' => 'Наименование организации', 'rules' => '', 'type' => 'string'],
			['field' => 'Org_Nick', 'label' => 'Краткое наименование организации', 'rules' => '', 'type' => 'string'],
			['field' => 'DispClass_id', 'label' => 'Тип дд', 'rules' => '', 'type' => 'id'],
			['field' => 'Disp_consDate', 'label' => 'Дата согласия дд', 'rules' => '', 'type' => 'date'],
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_Org_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Org/List",
	 *  	tags={"Org"},
	 *	    summary="Получение данных человека",
	 *     	@OA\Parameter(
	 *     		name="Org_id",
	 *     		in="query",
	 *     		description="Идентификатор организации",
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
	function List_get() {
		$data = $this->ProcessInputData('getLpuList', null, true);

		$subParams = ['Org_Name','Org_Nick'];
		foreach ($subParams as $val) {
			if (!empty($data[$val]))
				$data[$val] = str_replace('%20', ' ', $data[$val]);
		}
		$response = $this->dbmodel->getLpuList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}
