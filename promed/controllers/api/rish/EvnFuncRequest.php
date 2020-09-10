<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnFuncRequest - контроллер API для работы с функционалом ФД
 *
 * @package			API
 * @author			brotherhood of swan developers
 * @version			2019
 */

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property EvnFuncRequest_model dbmodel
 * @OA\Tag(
 *     name="EvnFuncRequest",
 *     description="Функциональная диагностика"
 * )
 */
class EvnFuncRequest extends SwREST_Controller {
	protected $inputRules = array(
		'mLoadEvnFuncRequestList' => array(
			array(
				'field' => 'FuncRequest_begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'FuncRequest_endDate',
				'label' => 'Окончание периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_IsCito',
				'label' => 'Только Cito!',
				'rules' => 'trim',
				'type' => 'api_flag_nc'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'mGetEvnProcRequest' => array(
			array(
				'field' => 'EvnFuncRequest_id',
				'label' => 'Идентификатор заявки ФД',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mSaveEvnProcRequest' => [
				[
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnFuncRequest_id',
					'label' => 'ID заявки',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				],
				[
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'required',
					'type' => 'id'
				],
				[
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				],
				[
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'PrescriptionStatusType_id',
					'label' => 'Статус назначения',
					'rules' => '',
					'type' => 'id'
				],

				[
					'field' => 'EvnPrescrProc_CountInDay',
					'label' => 'Повторов в сутки',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnPrescrProc_CourseDuration',
					'label' => 'Продолжительность курса',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnPrescrProc_ContReception',
					'label' => 'Непрерывный прием',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnPrescrProc_Interval',
					'label' => 'Перерыв',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnCourseProc_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnPrescrProc_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'DurationType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'DurationType_nid',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'DurationType_sid',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnDirection_setDT',
					'label' => 'Дата направления',
					'rules' => '',
					'type' => 'date'
				],
				[
					'field' => 'PrehospDirect_id',
					'label' => 'Кем направлен',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'Org_sid',
					'label' => 'Направившая организация',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'Lpu_sid',
					'label' => 'Направившее ЛПУ',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'LpuSection_id',
					'label' => 'Направившее отделение ЛПУ',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnLabRequest_Ward',
					'label' => 'Палата',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnDirection_IsCito',
					'label' => 'Cito',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnPrescr_IsExec',
					'label' => 'Выполнено',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnPrescrProc_didDT',
					'label' => 'Время выполнения',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnPrescrProc_Descr',
					'label' => 'Время выполнения',
					'rules' => '',
					'type' => 'string'
				]
			]
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnFuncRequest_model', 'dbmodel');

	}

	/**
	 * @OA\get(
	path="/api/rish/EvnFuncRequest/mLoadEvnFuncRequestList",
	tags={"EvnFuncRequest"},
	summary="Получение списка заявок ФД",

	@OA\Parameter(
	name="FuncRequest_begDate",
	in="query",
	description="Начало периода",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="FuncRequest_endDate",
	in="query",
	description="Окончание периода",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="MedService_id",
	in="query",
	description="Идентификатор службы",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Person_FIO",
	in="query",
	description="ФИО",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnDirection_IsCito",
	in="query",
	description="Только Cito!",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Идентификатор услуги",
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
	property="group_name",
	description="имя группы",
	type="string",

	)
	,
	@OA\Property(
	property="list",
	description="Список объектов",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="TimetableMedService_id",
	description="Идентификатор бирки",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnFuncRequest_id",
	description="Заявка на исследование, Идентификатор заявки",
	type="integer",

	)
	,
	@OA\Property(
	property="group_name",
	description="Имя группы",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableMedService_begDate",
	description="Дата приема",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableMedService_begTime",
	description="Время приема",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableMedService_Type",
	description="Тип расписания",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_IsCito",
	description="Выписка направлений, срочность",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnDirection_Num",
	description="Выписка направлений, номер направления",
	type="string",

	),
	@OA\Property(
	property="EvnDirection_id",
	description="Идентификатор направления",
	type="string",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_Birthday",
	description="Дата рождения",
	type="string",

	)
	,
	@OA\Property(
	property="Person_Age",
	description="Кол-во лет",
	type="string",

	)
	,
	@OA\Property(
	property="Person_FIO",
	description="ФИО",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_Name",
	description="Название услуги",
	type="string",

	)

	)

	)

	)

	)

	)
	)

	)


	 *
	 */
	function mLoadEvnFuncRequestList_get() {
		$this->load->model('EvnFuncRequestProc_model');
		$data = $this->ProcessInputData('mLoadEvnFuncRequestList', false, true);
		$response = $this->EvnFuncRequestProc_model->mLoadEvnFuncRequestList($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	@OA\get(
	path="/api/rish/EvnFuncRequest/mGetEvnProcRequest",
	tags={"EvnFuncRequest"},
	summary="Описание метода",

	@OA\Parameter(
	name="EvnFuncRequest_id",
	in="query",
	description="Идентификатор заявки ФД",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_id",
	in="query",
	description="Идентификатор направления",
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
	description="Описание",
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
	property="EvnFuncRequest_id",
	description="Заявка на исследование, Идентификатор заявки",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDirection_id",
	description="Выписка направлений, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDirection_Num",
	description="Выписка направлений, номер направления",
	type="string",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDirection_setDT",
	description="Дата направления",
	type="string",

	)
	,
	@OA\Property(
	property="PrehospDirect_id",
	description="Кем направлен в предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospDirect_Name",
	description="Кем направлен в предварительной госпитализации, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_sid",
	description="Идентификатор ЛПУ",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_Nick",
	description="Наименование ЛПУ",
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
	property="LpuSection_Name",
	description="Справочник ЛПУ: отделения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnLabRequest_Ward",
	description="Номер палаты",
	type="string",

	)
	,
	@OA\Property(
	property="MedPersonal_id",
	description="Кэш врачей, идентификатор медицинского работника",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_Fio",
	description="Врач",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_IsCito",
	description="Выписка направлений, срочность",
	type="boolean",

	)
	,
	@OA\Property(
	property="PayType_id",
	description="Тип оплаты, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnUslugaPar_id",
	description="Параклиническая услуга, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_id",
	description="Комплексные услуги, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnCourseProc_id",
	description="Курс процедур, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPrescrProc_CountInDay",
	description="Повторов в сутки",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrProc_CourseDuration",
	description="Продолжительность",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrProc_ContReception",
	description="Повторять непрерывно",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrProc_Interval",
	description="Перерыв",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrProc_id",
	description="Назначение с типом Манипуляции и процедуры, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="DurationType_id",
	description="Тип продолжительности, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPrescrProc_didDT",
	description="Время выполнения",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescr_IsExec",
	description="Выполнено (Да/Нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPrescrProc_Descr",
	description="Примечание",
	type="string",

	)

	)

	)

	)
	)

	)

	 */
	function mGetEvnProcRequest_get() {
		$this->load->model('EvnFuncRequestProc_model');
		$data = $this->ProcessInputData('mGetEvnProcRequest');
		$response = $this->EvnFuncRequestProc_model->mGetEvnProcRequest($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	@OA\post(
	path="/api/rish/EvnFuncRequest/mSaveEvnProcRequest",
	tags={"EvnFuncRequest"},
	summary="Сохранение\обновление данных процедурной заявки",

	@OA\Parameter(
	name="MedService_id",
	in="query",
	description="Служба",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnFuncRequest_id",
	in="query",
	description="ID заявки",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор человека",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PayType_id",
	in="query",
	description="Вид оплаты",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Услуга",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_id",
	in="query",
	description="Направление",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrescriptionStatusType_id",
	in="query",
	description="Статус назначения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPrescrProc_CountInDay",
	in="query",
	description="Повторов в сутки",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPrescrProc_CourseDuration",
	in="query",
	description="Продолжительность курса",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPrescrProc_ContReception",
	in="query",
	description="Непрерывный прием",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPrescrProc_Interval",
	in="query",
	description="Перерыв",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnCourseProc_id",
	in="query",
	description="",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPrescrProc_id",
	in="query",
	description="",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DurationType_id",
	in="query",
	description="",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DurationType_nid",
	in="query",
	description="",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DurationType_sid",
	in="query",
	description="",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_Num",
	in="query",
	description="Номер направления",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnDirection_setDT",
	in="query",
	description="Дата направления",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="PrehospDirect_id",
	in="query",
	description="Кем направлен",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Org_sid",
	in="query",
	description="Направившая организация",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Lpu_sid",
	in="query",
	description="Направившее ЛПУ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Направившее отделение ЛПУ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnLabRequest_Ward",
	in="query",
	description="Палата",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnDirection_IsCito",
	in="query",
	description="Cito",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPrescr_IsExec",
	in="query",
	description="Выполнено",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPrescrProc_didDT",
	in="query",
	description="Время выполнения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPrescrProc_Descr",
	in="query",
	description="Время выполнения",
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
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="EvnFuncRequest_id",
	description="Заявка на исследование, Идентификатор заявки",
	type="integer",

	)
	,
	@OA\Property(
	property="Error_Code",
	description="Код ответа",
	type="string",

	)
	,
	@OA\Property(
	property="Error_Msg",
	description="Текст ошибки",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mSaveEvnProcRequest_post() {
		$this->load->model('EvnFuncRequestProc_model');
		$data = $this->ProcessInputData('mSaveEvnProcRequest', null, true);
		$response = $this->EvnFuncRequestProc_model->mSaveEvnProcRequest($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}
}