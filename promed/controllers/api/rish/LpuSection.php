<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property EvnPS_model dbmodel
 * @OA\Tag(
 *     name="LpuSection",
 *     description="Отделение"
 * )
 */
class LpuSection extends SwREST_Controller {
	protected $inputRules = array(
		'mGetLpuSectionList' => array(
			array('field' => 'Lpu_id ', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnitType_id', 'label' => 'Идентификатор типа подразделения', 'rules' => '', 'type' => 'string'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Lpu_model', 'dbmodel');
	}

	/**
	 *@OA\get(
	path="/api/LpuSection/mGetLpuSectionList",
	tags={"LpuSection"},
	summary="Получение отделений",

	@OA\Parameter(
	name="Lpu_id ",
	in="query",
	description="Идентификатор МО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuUnitType_id",
	in="query",
	description="Идентификатор типа подразделения",
	required=false,
	@OA\Schema(type="string")
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
	property="LpuSection_id",
	description="Справочник ЛПУ: отделения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuBuilding_id",
	description="Подразделения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuUnit_id",
	description="Группы отделений, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionAge_id",
	description="тип отделения по возрасту, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_id",
	description="профиль отделения в ЛПУ, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_Code",
	description="профиль отделения в ЛПУ, код",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_Name",
	description="профиль отделения в ЛПУ, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_SysNick",
	description="профиль отделения в ЛПУ, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnitType_id",
	description="тип подразделения ЛПУ, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSection_Code",
	description="Справочник ЛПУ: отделения, код",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_Name",
	description="Справочник ЛПУ: отделения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnitType_Code",
	description="тип подразделения ЛПУ, код",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnitType_SysNick",
	description="тип подразделения ЛПУ, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_setDate",
	description="Дата начала работы",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_disDate",
	description="Дата окончания работы",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="справочник ЛПУ, ЛПУ",
	type="integer",

	)

	)

	)

	)
	)

	)
	 */

	function mGetLpuSectionList_get() {
		$data = $this->ProcessInputData('mGetLpuSectionList', false, true);
		if (!empty($data['LpuUnitType_id'])) {
			$LpuUnitType_list = explode(',', $data['LpuUnitType_id']);
			$tempArr = array();
			foreach ($LpuUnitType_list as $item) {
				if (is_numeric($item)) {
					array_push($tempArr, $item);
				}
			}
			$data['LpuUnitType_id'] = implode(',', $tempArr);
		}
		$result = $this->dbmodel->mGetLpuSectionList($data);
		$response = array('error_code' => 0,'data' => $result);
		$this->response($response);
	}
}