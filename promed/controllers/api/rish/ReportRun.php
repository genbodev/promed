<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ReportRun - контроллер API для работы с отчетами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @author			brotherhood of swan developers
 * @version			2019
 */

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property ReportRun_model dbmodel
 * @OA\Tag(
 *     name="ReportRun",
 *     description="Отчеты"
 * )
 */

class ReportRun extends SwREST_Controller
{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('ReportRun_model', 'dbmodel');

		$this->inputRules = array(
			'RunByFileName' => array(
				array(
					'field' => 'Report_FileName',
					'label' => 'Наименование файла отчёта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Report_FileType',
					'label' => 'Тип файла отчёта',
					'rules' => '',
					'default' => 'rptdesign',
					'type' => 'string'
				),
				array(
					'field' => 'Report_Params',
					'label' => 'Параметры отчёта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Report_Format',
					'label' => 'Формат отчёта',
					'rules' => '',
					'default' => 'pdf',
					'type' => 'string'
				),
				array(
					'field' => 'isDebug',
					'label' => 'Дебаг',
					'rules' => '',
					'type' => 'int'
				)
			)
		);
	}


	/**
	@OA\get(
	path="/api/ReportRun/mRunByFileName",
	tags={"ReportRun"},
	summary="Запуск отчёта по имени файла отчёта",

	@OA\Parameter(
	name="Report_FileName",
	in="query",
	description="Наименование файла отчета (без расширения)",
	required=true,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Report_FileType",
	in="query",
	description="Тип файла отчета (по умолчанию rptdesign)",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Report_Params",
	in="query",
	description="JSON-строка с параметрами для отчета",
	required=true,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Report_Format",
	in="query",
	description="Формат отчета (PDF - по умолчанию)",
	required=false,
	@OA\Schema(type="string")
	)
	,

	@OA\Response(
	response="200",
	description="Бинарный файл отчета",
	)

	)
	 */
	function mRunByFileName_get()
	{
		$this->load->model('ReportRun_model', 'dbmodel');
		$data = $this->ProcessInputData('RunByFileName', false, false);

		// присоединяем тип файла
		$data['Report_FileName'] .= '.'.$data['Report_FileType'];

		// генерим параметры из строки
		$data['Report_Params'] = $this->dbmodel->generateReportParamsFromJSON($data['Report_Params']);

		$response = $this->dbmodel->RunByFileName($data);
		echo $response;
		exit();
	}
}