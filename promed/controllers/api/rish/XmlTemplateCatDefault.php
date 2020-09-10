<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 *  XmlTemplateCatDefault - контроллер для работы с папками Xml-шаблонов по умолчанию
*/

require(APPPATH.'libraries/SwREST_Controller.php');

class XmlTemplateCatDefault extends SwREST_Controller
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('XmlTemplateCatDefault_model', 'dbmodel');
		$this->inputRules = array(
			'mGetPath' => array(
				array('field' => 'XmlType_id','label' => 'Идентификатор типа документа','rules' => 'trim|required','type' => 'id'),
				array('field' => 'EvnClass_id','label' => 'Идентификатор категории документа','rules' => 'trim|required','type' => 'id'),
				array('field' => 'MedStaffFact_id','label' => 'Идентификатор рабочего места','rules' => 'trim','type' => 'id'),
				array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'trim','type' => 'id'),
				array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => 'trim','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Идентификатор отделения пользователя','rules' => 'trim','type' => 'id'),
			)
		);
	}


	/**
	 *
	 * @OA\post(
	path="/api/XmlTemplateCatDefault/mGetPath",
	tags={"XmlTemplateCatDefault"},
	summary="Получение идентификатора папки по умолчанию или пути к ближайшей папке, доступной для редактирования",

	@OA\Parameter(
	name="XmlType_id",
	in="query",
	description="Идентификатор типа документа",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnClass_id",
	in="query",
	description="Идентификатор категории документа",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_id",
	in="query",
	description="Идентификатор рабочего места",
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
	@OA\Parameter(
	name="MedPersonal_id",
	in="query",
	description="Идентификатор врача",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Идентификатор отделения пользователя",
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
	property="XmlTemplateCat_id",
	description="Категории шаблонов отображения xml данных, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="XmlTemplateCat_Name",
	description="Категории шаблонов отображения xml данных, наименование категории шаблона",
	type="string",

	)
	,
	@OA\Property(
	property="accessType",
	description="Уровень доступа",
	type="string",

	)
	,
	@OA\Property(
	property="XmlTemplateCat_pid0",
	description="Категории шаблонов отображения xml данных, идентификатор",
	type="string",

	)
	,
	@OA\Property(
	property="XmlTemplateCat_Name0",
	description="Категории шаблонов отображения xml данных, наименование категории шаблона",
	type="string",

	)
	,
	@OA\Property(
	property="accessType0",
	description="Уровень доступа",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mGetPath_post() {
		$data = $this->ProcessInputData('mGetPath', false, true);
			$response = $this->dbmodel->mGetPath($data);
			try {
				if (!empty($response['Error_Msg'])) {
					throw new Exception($response['Error_Msg'], 400);
				}
				$response = array('error_code' => 0, 'data'=>$response);
			} catch (Exception $e) {
				$response = array('error_code' => 777, 'error_msg' => toUtf($e->getMessage()));
			}
			$this->response($response);
	}
}
