<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnSection - апи-контроллер для работы с движением по отделениям
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author			Maksim Sysolin
 * @version			2019
 *
 */
require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property EvnSection_model dbmodel
 * @OA\Tag(
 *     name="EvnSection",
 *     description="Стационар. Движение в отделении"
 * )
 */
class EvnSection extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnSection_model', 'dbmodel');
	}

	public $inputRules = array(

		'mGetListByDay' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'byDate',
				'label' => 'дата',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'scope',
				'label' => 'область видимости',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'groupBy',
				'label' => 'группировать по',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSectionWard_id',
				'label' => 'Идентификатор палаты',
				'rules' => 'trim',
				'type' => 'id'
			),
			// фильтры
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество человека',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'ДР человека',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места врача',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'mGetLpuSectionWardList' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			)
		),
		'mSetEvnSectionWard' => array(
			array(
				'field' => 'LpuSectionWard_id',
				'label' => 'Идентификатор палаты',
				'rules' => 'required|zero',
				'type' => 'id'
			),
			array(
				'field' => 'ignore_sex',
				'label' => 'Игнорировать тип палаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения в стационаре',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mSetEvnSectionMedPersonal' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Движение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_pid',
				'label' => 'КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required|zero',
				'type' => 'id'
			)
		),
		'mGetLpuSectionDoctors' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор палаты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'type',
				'label' => 'Тип одразделения ЛПУ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'date',
				'label' => 'Дата',
				'rules' => 'trim',
				//'default' => date('Y-m-d'),
				'type' => 'date'
			),
		),
		'mSaveEvnSectionInHosp' => array(
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата поступления',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_setTime',
				'label' => 'Время поступления',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'vizit_direction_control_check',
				'label' => 'Проверка пересечения КВС и ТАП',
				'rules' => '',
				'type' => 'int'
			)
		),
		'mDeleteEvnSectionInHosp' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор случая движения пациента в стационаре',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			)
		),
		'mGetEvnSectionForm' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mGetEvnSectionViewData' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'evnBirthData'=> array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор сведений о родах в КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancySpec_id',
				'label' => 'Идентификатор специфики о беременностях и родах в карте диспансерного учета',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_CountPregnancy',
				'label' => 'Которая беременность',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_CountChild',
				'label' => 'Количество плодов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_CountChildAlive',
				'label' => 'В т.ч. живорожденные',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_IsHIVtest',
				'label' => 'Обследована на ВИЧ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_IsHIV',
				'label' => 'Наличие ВИЧ-инфекции',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AbortType_id',
				'label' => 'Тип аборта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_IsMedicalAbort',
				'label' => 'Медикаментозный',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_CountBirth',
				'label' => 'Роды которые',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthResult_id',
				'label' => 'Характер родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthPlace_id',
				'label' => 'Место родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_OutcomPeriod',
				'label' => 'Срок, недель',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_OutcomD',
				'label' => 'Дата исхода беременности',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'BirthSpecStac_OutcomT',
				'label' => 'Время родов',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'BirthSpec_id',
				'label' => 'Особенности родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_BloodLoss',
				'label' => 'Кровопотери (мл)',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'deathChilddata',
				'label' => 'Данные о мертворожденных',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'childdata',
				'label' => 'Данные о детях',
				'rules' => '',
				'type' => 'string'
			)
		)
	);

	/**
	 * /**
	 * @OA\Get(
	 *     path="/api/EvnSection/mGetListByDay",
	 *     tags={"EvnSection"},
	 *     summary="Получает пациентов в отделении с палатами и без",
	 *     @OA\Parameter(
	 *     		name="LpuSection_id",
	 *     		in="query",
	 *     		description="Идентификатор отделения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="byDate",
	 *     		in="query",
	 *     		description="Дата",
	 *     		required=true,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="groupBy",
	 *     		in="query",
	 *     		description="Способ группировки:
	 *	ward - по палате,
	 *	doctor - по лечащему врачу,
	 *	diag - по диагнозу
	 *	status - по статусу (пока не используется)",
	 *     		required=false,
	 *     		@OA\Schema(
	 *             type="string",
	 *             default="ward"
	 *         )
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="scope",
	 *     		in="query",
	 *     		description="Область видимости (фильтры):
	 * 	all - все,
	 *	no_retired - без выбывших,
	 *	arriver - вновь прибыли (на дату),
	 *	with_ward - с палатами,
	 *	no_ward - без палаты,
	 *	retired - выбыли,
	 *	inward - в конкретной палате (должен быть передан LpuSectionWard_id),
	 *	redirected - перенаправленные из других отделений",
	 *     		required=false,
	 *     		@OA\Schema(
	 *             type="string",
	 *             default="all|no_retired"
	 *         )
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="LpuSectionWard_id",
	 *     		in="query",
	 *     		description="Идентификатор палаты",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedStaffFact_id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего места лечащего врача",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Person_SurName",
	 *     		in="query",
	 *     		description="Фамилия пациента",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Person_FirName",
	 *     		in="query",
	 *     		description="Имя пациента",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Person_SecName",
	 *     		in="query",
	 *     		description="Отчество пациента",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Person_BirthDay",
	 *     		in="query",
	 *     		description="Дата рождения пациента",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent(
						type="object",
						@OA\Property(
	 *                     property="group_id",
	 *                     description="Идентификатор группы",
	 *                     type="integer",
	 *                 ),
						@OA\Property(
	 *                     property="group_title",
	 *                     description="Наименование группы",
	 *                     type="string",
	 *                 ),
						@OA\Property(
	 *                     property="group_data",
	 *                     description="Дополнительные данные по группе",
	 *                     type="object",
						@OA\Property(
	 *                     property="isComfortable",
	 *                     description="Палата повышенной комфортности",
	 *                     type="boolean",
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Sex_id",
	 *                     description="Тип палаты (общий, мужской, женский)",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="TotalBeds_Count",
	 *                     description="Всего палат",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="FreeBeds_Count",
	 *                     description="Свободно палат",
	 *                     type="integer"
	 *                 ),
	 *                 ),
						@OA\Property(
	 *                     property="patients",
	 *                     description="Список пациентов",
	 *                     type="array",
	 * 					@OA\Items(
	 *                     type="object",
	 *                 @OA\Property(
	 *                     property="EvnSection_id",
	 *                     description="Идентификатор движения в отделении",
	 *                     type="integer",
	 *                 ),
	 *                 @OA\Property(
	 *                     property="EvnSection_rid",
	 *                     description="Идентификатор родительского события движения",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="LpuSection_id",
	 *                     description="Идентификатор отделения",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Sex_id",
	 *                     description="Пол пациента",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="PersonEncrypHIV_Encryp",
	 *                     description="Шифр",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Person_Fio",
	 *                     description="ФИО пациента",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Person_BirthDay",
	 *                     description="Дата рождения пациента",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Person_Age",
	 *                     description="Возраст пациента",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Person_AgeMonth",
	 *                     description="Количество месяцев от дня рождения",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Diag_id",
	 *                     description="Идентификатор диагноза",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Diag_Code",
	 *                     description="Код диагноза",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Diag_Name",
	 *                     description="Наименование диагноза",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="EvnSection_setDate",
	 *                     description="Дата принятия",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="EvnSection_disDate",
	 *                     description="Дата выбытия",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Person_id",
	 *                     description="Идентификатор персоны",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Server_id",
	 *                     description="Идентификатор сервера",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="PersonEvn_id",
	 *                     description="Идентификатор периодики пациента",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Mes_id",
	 *                     description="Идентификатор МЭС",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="KoikoDni",
	 *                     description="Количество дней по МЭС",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="EvnPS_id",
	 *                     description="Идентификатор КВС",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="LpuSectionWard_id",
	 *                     description="Идентификатор палаты",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="LpuSectionWard_Name",
	 *                     description="Наименование палаты",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="MedPersonal_id",
	 *                     description="Идентификатор персонала",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="MedPersonal_Fin",
	 *                     description="ФИО лечащего врача",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="EvnSecdni",
	 *                     description="Количество дней в стационаре",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="EvnReanimatPeriod_id",
	 *                     description="Идентификатор периода реанимации",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="EvnSectionStatus_Name",
	 *                     description="Статус пациента в отделении",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="percentage",
	 *                     description="Процент",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="mes_alias",
	 *                     description="Наименование МЭС",
	 *                     type="string"
	 *                 ),
	 *      			@OA\Property(
	 *                     property="EvnPS_RFID",
	 *                     description="RFID-метка",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="Surgery_setDate",
	 *                     description="Дата операции",
	 *                     type="string"
	 *                 ))
	 *                 )
	 *
	 * 				)
	 * 			)
	 * 	   )
	 * )
	 */
	function mGetListByDay_get() {

		$data = $this->ProcessInputData('mGetListByDay', false, true);
		$result = $this->dbmodel->mGetListByDay($data);

		$response = array('error_code' => 0,'data' => $result);
		$this->response($response);
	}


	/**
	 * @OA\get(
	path="/api/EvnSection/mGetLpuSectionWardList",
	tags={"EvnSection"},
	summary="Возвращает палаты отделения действующие и свободные на данный момент времени",

    @OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор КВС",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Идентификатор отделения",
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
	property="CurrentLpuSectionWard",
	description="Признак занята ли палата пациентом с EvnPS_id переданным в параметры",
	type="boolean",

	)
	,

	@OA\Property(
	property="LpuSectionWard_id",
	description="Идентификатор палаты",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSection_id",
	description="Идентификатор отделения",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionWard_Name",
	description="Наименование палаты",
	type="string",

	)
	,
	@OA\Property(
	property="LpuWardType_id",
	description="Тип палаты, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuWardType_Name",
	description="Наименование типа палаты",
	type="string",

	)
	,
	@OA\Property(
	property="Sex_id",
	description="Тип палаты (общий, мужской, женский)",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionWard_TotalBedCount",
	description="Общее количество коек",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionWard_FreeBedCount",
	description="Количество свободных коек",
	type="integer",

	)

	)

	)

	)
	)

	)
	 */
	function mGetLpuSectionWardList_get() {
		$data = $this->ProcessInputData('mGetLpuSectionWardList', false, true);
		$this->load->model('HospitalWard_model', 'HospitalWard_model');
		$result = $this->HospitalWard_model->mGetLpuSectionWardList($data);

		$response = array('error_code' => 0,'data' => $result);
		$this->response($response);
	}

	/**
	 * /**
	 * @OA\Post(
	 *     path="/api/EvnSection/mSetEvnSectionWard",
	 *     tags={"EvnSection"},
	 *     summary="Назначение в палату.",
	 *     @OA\Parameter(
	 *     		name="LpuSectionWard_id",
	 *     		in="query",
	 *     		description="Идентификатор отделения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnSection_id",
	 *     		in="query",
	 *     		description="Идентификатор движения в отделении",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent(
	type="object",
	 *                 @OA\Property(
	 *                     property="error_code",
	 *                     description="Код ответа",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="success",
	 *                     description="Результат выполнения",
	 *                     type="boolean"
	 *                 ),
	 *     				@OA\Property(
	 *                     property="error_msg",
	 *                     description="Сообщение об ошибке",
	 *                     type="string"
	 *                 ),
	 *
	 *
	 * 				)
	 * 			)
	 *
	 * )
	 */
	function mSetEvnSectionWard_post(){
		$data = $this->ProcessInputData('mSetEvnSectionWard', false, true);
		try {
			if (empty($data['EvnSection_id'])) {
				throw new Exception('Необходимо указать идентификатор движения в отделении', 400);
			}

			if (!empty($data['EvnSection_id'])) {

				$this->dbmodel->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->dbmodel->setParams($data);

				$result = $this->dbmodel->updateLpuSectionWardId($data['EvnSection_id'], $data['LpuSectionWard_id']);
			}

			if (!empty($result['Error_Msg'])) throw new Exception($result['Error_Msg']);
			$response = array('error_code' => 0 ,'success' => true);

		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}

		$this->response($response);
	}
	/**
	 * @OA\Post(
	 *     path="/api/EvnSection/mSetEvnSectionMedPersonal",
	 *     tags={"EvnSection"},
	 *     summary="Назначение врача для пациента",
	 *     @OA\Parameter(
	 *     		name="EvnSection_id",
	 *     		in="query",
	 *     		description="Идентификатор движения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnSection_pid",
	 *     		in="query",
	 *     		description="Родительский идентификатор движения",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedStaffFact_id",
	 *     		in="query",
	 *     		description="Идентифактор рабочего места врача",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent(
	 *      @OA\Property(
	 *                     property="error_code",
	 *                     description="Код ответа",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="success",
	 *                     description="Результат выполнения",
	 *                     type="boolean"
	 *                 ),
	 *     				@OA\Property(
	 *                     property="error_msg",
	 *                     description="Сообщение об ошибке",
	 *                     type="string"
	 *                 ),
	 *              )
	 * 			)
	 * 		)
	 */

	function mSetEvnSectionMedPersonal_post() {
		$data = $this->ProcessInputData('mSetEvnSectionMedPersonal', false, true);
		try {
			if (empty($data['EvnSection_pid']) && empty($data['EvnSection_id'])) { //обязательные параметры поставить
				throw new Exception('Необходимо указать идентификатор КВС или идентификатор движения в отделении', 400);
			}
			$this->load->model('MedStaffFact_model', 'msf_model');
			$data['MedPersonal_id'] = $this->msf_model->getMedPersonal($data);
			if(empty($data['MedPersonal_id']) && $data['MedStaffFact_id'] != 0) { // в МАРМ есть вариант "без врача"
				throw new Exception ('Не удалось определить MedPersonal_id ', 400);
			}
			// врач записывается или в EvnSection или в EvnPS
			if (!empty($data['EvnSection_pid']) && $data['EvnSection_id'] == $data['EvnSection_pid']) {
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$this->EvnPS_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->EvnPS_model->setParams($data);
				$updateMedPersonalResult = $this->EvnPS_model->updateMedPersonalPid($data['EvnSection_pid'], $data['MedPersonal_id']);
				$updateMedStaffFactResult = $this->EvnPS_model->updateMedStaffFactPid($data['EvnSection_pid'], $data['MedStaffFact_id']);
			} else if (isset($data['EvnSection_id'])) {
				$this->dbmodel->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				$this->dbmodel->setParams($data);
				$updateMedPersonalResult = $this->dbmodel->updateMedPersonalId($data['EvnSection_id'], $data['MedPersonal_id']);
				$updateMedStaffFactResult = $this->dbmodel->updateMedStaffFactId($data['EvnSection_id'], $data['MedStaffFact_id']);
			}
			if(!empty($updateMedPersonalResult['Error_Msg'])) {
				throw new Exception($updateMedPersonalResult['Error_Msg']);
			}
			if(!empty($updateMedStaffFactResult['Error_Msg'])) {
				throw new Exception($updateMedStaffFactResult['Error_Msg']);
			}
			$response = array('error_code' => 0 ,'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnSection/mGetLpuSectionDoctors",
	 *     tags={"EvnSection"},
	 *     summary="Получение врачей работающих в отделении",
	 *     @OA\Parameter(
	 *     		name="LpuSection_id",
	 *     		in="query",
	 *     		description="Идентификатор отделения",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="LpuUnitType_SysNick",
	 *     		in="query",
	 *     		description="Тип подразделения ЛПУ:
	 * polka
	 * stac
	 * pstac
	 * other",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Date",
	 *     		in="query",
	 *     		description="Дата",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent(
	 *      @OA\Property(
	 *                     property="MedStaffFact_id",
	 *                     description="Идентификатор рабочего места врача",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="MedPersonal_id",
	 *                     description="Идентификатор врача",
	 *                     type="integer"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="LpuSection_id",
	 *                     description="Идентификатор отделения",
	 *                     type="integer"
	 *                 ),
	 *     				@OA\Property(
	 *                     property="Person_Fio",
	 *                     description="ФИО врача",
	 *                     type="string"
	 *                 ),
	 *              )
	 * 			)
	 * 		)
	 */

	function mGetLpuSectionDoctors_get() {
		$data = $this->ProcessInputData('mGetLpuSectionDoctors', false, true);
		$result = $this->dbmodel->mGetLpuSectionDoctors($data);
		$response = array('error_code' => 0,'data' => $result);
		$this->response($response);
	}

	/**
	@OA\get(
	path="/api/EvnSection/mGetEvnSectionForm",
	tags={"EvnSection"},
	summary="Получение движения",

	@OA\Parameter(
	name="EvnSection_id",
	in="query",
	description="Идентификатор движения",
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
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Результат",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="accessType",
	description="Тип доступа",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_id",
	description="Движение в отделении, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="справочник ЛПУ, идентификатор ЛПУ",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_pid",
	description="Идентификатор родительского события",
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
	property="PersonEvn_id",
	description="События по человеку, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Server_id",
	description="Идентификатор сервера",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDie_id",
	description="Смерть пациента, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnLeave_id",
	description="Выписка из стационара, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnOtherLpu_id",
	description="Выписка в другое ЛПУ, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnOtherSection_id",
	description="Выписка в другое отделение, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnOtherSectionBedProfile_id",
	description="Профиль коек, куда производится выписка, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnOtherStac_id",
	description="Выписка в стационар другого типа, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_id",
	description="Справочник диагнозов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_eid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="DiagSetPhase_id",
	description="Степень тяжести состояния пациента (OID 1.2.643.5.1.13.13.11.1006 ), идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="DiagSetPhase_aid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="PrivilegeType_id",
	description="тип льготы, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_PhaseDescr",
	description="Движение в отделении, Описание фазы",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_Absence",
	description="Движение в отделении, Отсутствовал (дней)",
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
	property="EvnSection_insideNumCard",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionTransType_id",
	description="Справочник Вид транспортировки в отделение, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionWard_id",
	description="Палатная структура ЛПУ, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuUnitType_id",
	description="тип подразделения ЛПУ, идентификатор",
	type="integer",

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
	property="LpuSection_Code",
	description="Справочник ЛПУ: отделения, код",
	type="string",

	)
	,
	@OA\Property(
	property="MedStaffFact_id",
	description="Идентификатор места работы врача",
	type="integer",

	)
	,
	@OA\Property(
	property="MedPersonal_id",
	description="Идентификатор медицинского работника",
	type="integer",

	)
	,
	@OA\Property(
	property="PayType_id",
	description="Тип оплаты, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PayType_SysNick",
	description="Тип оплаты, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PayTypeERSB_id",
	description="Тип оплаты ЭРСБ, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="TariffClass_id",
	description="Класс тарифа, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_IsAdultEscort",
	description="Движение в отделении, В сопровождении взрослого",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnSection_IsMedReason",
	description="Движение в отделении, По медицинским показаниям",
	type="boolean",

	)
	,
	@OA\Property(
	property="DeseaseBegTimeType_id",
	description="Время с начала заболевания , идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="DeseaseType_id",
	description="Справочник заболеваний: характер заболевания, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="RehabScale_id",
	description="Шкала реабилитационной маршрутизации, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="RehabScale_vid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_SofaScalePoints",
	description="Движение в отделении, Баллы SOFA",
	type="string",

	)
	,
	@OA\Property(
	property="TumorStage_id",
	description="Стадия опухолевого процесса, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_IsZNO",
	description="Движение в отделении, подозрение на ЗНО",
	type="boolean",

	)
	,
	@OA\Property(
	property="Diag_spid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="PainIntensity_id",
	description="Интенсивность боли, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Mes_id",
	description="справочник МЭС, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Mes_tid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_sid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_kid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MesTariff_id",
	description="Тарифы МЭС, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_CoeffCTP",
	description="Движение в отделении, Коэффициент сложности курации пациента",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_disDate",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_setDate",
	description="Дата прибытия",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_disTime",
	description="Время выбытия",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_setTime",
	description="Время прибытия",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_id",
	description="тип выписки , идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LeaveType_prmid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_fedid",
	description="тип выписки , Классификатор результатов обращения за медицинской помощью",
	type="string",

	)
	,
	@OA\Property(
	property="ResultDeseaseType_fedid",
	description="Исход заболевания, классификатор исходов заболеваний (V012)",
	type="string",

	)
	,
	@OA\Property(
	property="EvnLeave_UKL",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="ResultDesease_id",
	description="Результат заболевания, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LeaveCause_id",
	description="причина выписки, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnLeave_IsAmbul",
	description="Выписка из стационара, направлен на амбулаторное лечение",
	type="boolean",

	)
	,
	@OA\Property(
	property="Org_oid",
	description="",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnitType_oid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_oid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionBedProfile_oid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionBedProfileLink_fedoid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDie_IsWait",
	description="Смерть пациента, Умер в приемном покое",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnDie_IsAnatom",
	description="Смерть пациента, необходимость паталогоанатомической экспертизы",
	type="boolean",

	)
	,
	@OA\Property(
	property="AnatomWhere_id",
	description="Справочник мест проведения патологоанатомических экспертиз, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_aid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Org_aid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_aid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MedPersonal_aid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MedPersonal_did",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDie_expDate",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDie_expTime",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Morbus_id",
	description="Простое заболевание, идентификатор",
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
	property="LpuSectionProfile_id",
	description="профиль отделения в ЛПУ, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_eid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsPaid",
	description="Движение в отделении, Случай оплачен",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnSection_IndexRep",
	description="Движение в отделении, Признак повторной подачи",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IndexRepInReg",
	description="Движение в отделении, Признак вхождения в реестр повторной подачи",
	type="string",

	)
	,
	@OA\Property(
	property="HTMedicalCareClass_id",
	description="Классификатор методов высокотехнологичной медицинской помощи, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LeaveTypeFed_id",
	description="Описание",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospTrauma_id",
	description="Травма при предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDiagPS_id",
	description="Установка диагноза в стационаре, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_Index",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="CureResult_id",
	description="Итог лечения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="CureResult_Name",
	description="Итог лечения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsTerm",
	description="Движение в отделении, Случай прерван(Да/нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="RankinScale_id",
	description="Шкала Рэнкина, иидентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="RankinScale_sid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_InsultScale",
	description="Движение в отделении, Значения шкалы инсульта Национального института здоровья",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_NIHSSAfterTLT",
	description="Движение в отделении, Значение шкалы инсульта Национального института здоровья после проведения ТЛТ",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_NIHSSLeave",
	description="Движение в отделении, Значение шкалы инсульта Национального института здоровья при выписке",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsRehab",
	description="Движение в отделении, признак по реабилитации",
	type="boolean",

	)
	,
	@OA\Property(
	property="MesType_id",
	description="Тип МЭС, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Mes_ksgid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_Code",
	description="справочник МЭС, код",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MesOld_Num",
	description="МЭС, Номер КСГ",
	type="string",

	)
	,
	@OA\Property(
	property="MesTariff_Value",
	description="Тарифы МЭС, значение",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsST",
	description="Движение в отделении, Подъём сегмента ST",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnSection_isPartialPay",
	description="Движение в отделении, Частичная оплата",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsCardShock",
	description="Движение в отделении, Осложнен кардиогенным шоком (да/нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnSection_StartPainHour",
	description="Движение в отделении, Время от начала боли (часов)",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_StartPainMin",
	description="Движение в отделении, Время от начала боли (минут)",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_GraceScalePoints",
	description="Движение в отделении, Кол-во баллов по шкале GRACE",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_BarthelIdx",
	description="Движение в отделении, Индекс Бартел",
	type="string",

	)
	,
	@OA\Property(
	property="PregnancyEvnPS_Period",
	description="Дополнительные данные о беременности, Срок беременности в неделях",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionBedProfileLink_fedid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MedicalCareBudgType_id",
	description="Типы медицинской помощи по бюджету, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionBedProfile_id",
	description="профиль коек, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="DeathPlace_id",
	description="Справочник свидетельств о смерти: место смерти, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="ProtocolCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirectionCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDrugCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnUslugaCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnReceptCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnXmlCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnXml_id",
	description="Ненормализованные данные для событий , Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="DrugTherapy",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	)

	)

	)

	)

	)
	)

	)


	 */
	function mGetEvnSectionForm_get() {

		$data = $this->ProcessInputData('mGetEvnSectionForm', false, true);
		$resp = $this->dbmodel->mGetEvnSectionForm($data);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	@OA\post(
	path="/api/EvnSection/mSaveEvnSectionInHosp",
	tags={"EvnSection"},
	summary="Создание движения по указанному отделению",

	@OA\Parameter(
	name="EvnSection_pid",
	in="query",
	description="Идентификатор родительского события",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_setDate",
	in="query",
	description="Дата поступления",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnSection_setTime",
	in="query",
	description="Время поступления",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Отделение",
	required=true,
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
	name="PersonEvn_id",
	in="query",
	description="Идентификатор состояния человека",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Server_id",
	in="query",
	description="Идентификатор сервера",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="vizit_direction_control_check",
	in="query",
	description="Проверка пересечения КВС и ТАП",
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
	description="Код ощибки",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="boolean",

	)

	)
	)

	)
	 */
	function mSaveEvnSectionInHosp_post()
	{
		$data = $this->ProcessInputData('mSaveEvnSectionInHosp', false, true);
		try {
			$this->load->model('EvnPS_model');
			$response = $this->EvnPS_model->saveEvnSectionInHosp(array(
				'scenario' => swModel::SCENARIO_SET_ATTRIBUTE,
				'session' => $data['session'],
				'EvnPS_id' => $data['EvnSection_pid'],
				'Person_id' => $data['Person_id'],
				'EvnPS_OutcomeDate' => $data['EvnSection_setDate'],
				'EvnPS_OutcomeTime' => $data['EvnSection_setTime'],
				'vizit_direction_control_check' => $data['vizit_direction_control_check'],
				'LpuSection_eid' => $data['LpuSection_id']
			));

			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0,'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);
	}

	/**
	@OA\post(
	path="/api/EvnSection/mDeleteEvnSectionInHosp",
	tags={"EvnSection"},
	summary="Отмена госпитализации из АРМа приемного отделения",

	@OA\Parameter(
	name="EvnSection_id",
	in="query",
	description="Идентификатор случая движения пациента в стационаре",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор КВС",
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
	type="number",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="boolean",

	)

	)
	)

	)
	 */
	function mDeleteEvnSectionInHosp_post() {

		$data = $this->ProcessInputData('mDeleteEvnSectionInHosp', false, true);

		$this->load->model('EvnPS_model');
		$response = $this->EvnPS_model->mDeleteEvnSectionInHosp($data);

		if (!empty($response['Error_Msg'])) {
			$this->response(
				array(
					'error_code' => 777,
					'error_msg' => $response['Error_Msg']
				)
			);
		}

		$this->response(array('error_code' => 0,'success' => true));
	}

	/**
	@OA\get(
	path="/api/EvnSection/mGetEvnSectionViewData",
	tags={"EvnSection"},
	summary="Получение движения (расширенный метод)",

	@OA\Parameter(
	name="EvnSection_id",
	in="query",
	description="Идентификатор движения",
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
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="accessType",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="allowUnsign",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="справочник ЛПУ, ЛПУ",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_id",
	description="Справочник диагнозов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_pid",
	description="Справочник диагнозов, идентификатор диагноза родителя",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_id",
	description="Движение в отделении, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_pid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnClass_id",
	description="класс события, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDiagPS_class",
	description="Описание",
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
	property="PersonEvn_id",
	description="События по человеку, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Server_id",
	description="Описание",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_Age",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Sex_SysNick",
	description="Справочник половых признаков, системное наименование",
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
	property="LowLpuSection_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MedPersonal_Fio",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_setDate",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_setTime",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_disDate",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_disTime",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="PayType_Name",
	description="Тип оплаты, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PayTypeERSB_id",
	description="Тип оплаты ЭРСБ, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PayTypeERSB_Name",
	description="Тип оплаты ЭРСБ, Наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionWard_Name",
	description="Палатная структура ЛПУ, наименование (номер)",
	type="string",

	)
	,
	@OA\Property(
	property="TariffClass_Name",
	description="Класс тарифа, наименование",
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
	property="MedPersonal_id",
	description="Кэш врачей, идентификатор медицинского работника",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionWard_id",
	description="Палатная структура ЛПУ, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MedStaffFact_id",
	description="Кэш мест работы, идентификатор места работы",
	type="integer",

	)
	,
	@OA\Property(
	property="MedSpecOms_id",
	description="справочник специальностей врачей по ОМС, Идентификатор записи",
	type="integer",

	)
	,
	@OA\Property(
	property="FedMedSpec_id",
	description="Описание",
	type="integer",

	)
	,
	@OA\Property(
	property="Mes_id",
	description="справочник МЭС, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionTransType_Name",
	description="Справочник Вид транспортировки в отделение, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PayType_id",
	description="Тип оплаты, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PayType_SysNick",
	description="Тип оплаты, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="TariffClass_id",
	description="Класс тарифа, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_Name",
	description="Справочник диагнозов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Diag_Code",
	description="Справочник диагнозов, код",
	type="string",

	)
	,
	@OA\Property(
	property="DeseaseType_Name",
	description="Справочник заболеваний: характер заболевания, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="TumorStage_Name",
	description="Стадия опухолевого процесса, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PainIntensity_Name",
	description="Интенсивность боли, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_id",
	description="тип выписки , идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LeaveType_Code",
	description="тип выписки , код",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_SysNick",
	description="тип выписки , системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_Name",
	description="тип выписки , наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_leaveDate",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_leaveTime",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_o_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_id",
	description="профиль отделения в ЛПУ, идентификатор",
	type="integer",

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
	property="PrehospWaifRefuseCause_id",
	description="Причина отказа от госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospArrive_id",
	description="Кем доставлен при предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospArrive_SysNick",
	description="Кем доставлен при предварительной госпитализации, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PrehospType_id",
	description="Тип предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospType_SysNick",
	description="Тип предварительной госпитализации, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionNEXT_id",
	description="Описание",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPS_IsTransfCall",
	description="Карта выбывшего из стационара, признак 'Передан активный вызов' (да/нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="ResultClass_id",
	description="Полка: результат лечения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="ResultDeseaseType_id",
	description="Исход заболевания, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPS_OutcomeDate",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_OutcomeTime",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_Code",
	description="справочник МЭС, код",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_KoikoDni",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_KoikoDni",
	description="справочник МЭС, койкодни",
	type="string",

	)
	,
	@OA\Property(
	property="Procent_KoikoDni",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsSigned",
	description="Описание",
	type="boolean",

	)
	,
	@OA\Property(
	property="ins_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="sign_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="insDT",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="signDT",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="CureStandart_Count",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="DiagFedMes_FileName",
	description="Диагноз по федеральным месам, имя файла",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionBedProfile_id",
	description="профиль коек, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionBedProfile_Name",
	description="профиль коек, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_KSG",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_KSGName",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="DrugTherapyScheme_Code",
	description="Схемы лекарственной терапии, Код схемы",
	type="string",

	)
	,
	@OA\Property(
	property="DrugTherapyScheme_Name",
	description="Схемы лекарственной терапии, Наименование и описание схемы",
	type="string",

	)
	,
	@OA\Property(
	property="RehabScale_id",
	description="Шкала реабилитационной маршрутизации, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="RehabScale_Name",
	description="Шкала реабилитационной маршрутизации, Наименование",
	type="string",

	)
	,
	@OA\Property(
	property="RehabScale_vid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="RehabScaleOut_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_SofaScalePoints",
	description="Движение в отделении, Баллы SOFA",
	type="string",

	)
	,
	@OA\Property(
	property="MesRid_Code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="Mes_rid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_KPG",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_id",
	description="Комплексные услуги, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Mes_sid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_insideNumCard",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="es_LpuSectionProfile_id",
	description="Описание",
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
	property="HTMedicalCareClass_id",
	description="Классификатор методов высокотехнологичной медицинской помощи, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="isAllowFedResultFields",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_setDateYmd",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_prmid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_fedid",
	description="тип выписки , Классификатор результатов обращения за медицинской помощью",
	type="string",

	)
	,
	@OA\Property(
	property="ResultDeseaseType_fedid",
	description="Исход заболевания, классификатор исходов заболеваний (V012)",
	type="string",

	)
	,
	@OA\Property(
	property="PrmLeaveType_Code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="PrmLeaveType_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="FedLeaveType_Code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="FedLeaveType_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="FedResultDeseaseType_Code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="FedResultDeseaseType_Name",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsPriem",
	description="Движение в отделении, Признак приемного отделения (да/нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="CureResult_id",
	description="Итог лечения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="CureResult_Name",
	description="Итог лечения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsTerm",
	description="Движение в отделении, Случай прерван(Да/нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="RankinScale_id",
	description="Шкала Рэнкина, иидентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="RankinScale_sid",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="RankinScale_Name",
	description="Шкала Рэнкина, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="RankinScale_sName",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_InsultScale",
	description="Движение в отделении, Значения шкалы инсульта Национального института здоровья",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_NIHSSAfterTLT",
	description="Движение в отделении, Значение шкалы инсульта Национального института здоровья после проведения ТЛТ",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_NIHSSLeave",
	description="Движение в отделении, Значение шкалы инсульта Национального института здоровья при выписке",
	type="string",

	)
	,
	@OA\Property(
	property="DiagFinance_IsRankin",
	description="Справочник диагнозов: тип финансирования диагноза, признак указания значения по шкале Рэнкина",
	type="boolean",

	)
	,
	@OA\Property(
	property="ResultClass_Name",
	description="Полка: результат лечения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="ResultDeseaseType_Name",
	description="Исход заболевания, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_IsST",
	description="Движение в отделении, Подъём сегмента ST",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnSection_IsCardShock",
	description="Движение в отделении, Осложнен кардиогенным шоком (да/нет)",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnSection_StartPainHour",
	description="Движение в отделении, Время от начала боли (часов)",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_StartPainMin",
	description="Движение в отделении, Время от начала боли (минут)",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_GraceScalePoints",
	description="Движение в отделении, Кол-во баллов по шкале GRACE",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_BarthelIdx",
	description="Движение в отделении, Индекс Бартел",
	type="string",

	)
	,
	@OA\Property(
	property="Duration",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_KSGUslugaNumber",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection_KSGCoeff",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="PregnancyEvnPS_Period",
	description="Дополнительные данные о беременности, Срок беременности в неделях",
	type="string",

	)
	,
	@OA\Property(
	property="ProtocolCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPrescrCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirectionCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDrugCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnUslugaCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnReceptCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnXmlCount",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnXml_id",
	description="Ненормализованные данные для событий , Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="displayEvnObservGraphs",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="listMorbus",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="onko",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="MorbusType_id",
	description="Тип заболевания, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Morbus_id",
	description="Простое заболевание, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_Code",
	description="Справочник диагнозов, код",
	type="string",

	)
	,
	@OA\Property(
	property="Diag_id",
	description="Справочник диагнозов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="diagIsMain",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDiagPLSop_id",
	description="Сопутствующий диагноз в поликлинике, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="disableAddEvnNotify",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="morbusTypeSysNick",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="MorbusOnkoVizitPLDop_id",
	description="Талон дополнений на онкобольного, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MorbusOnkoLeave_id",
	description="Выписка из стационара на онкобольного, идентификатор",
	type="integer",

	)

	)

	)

	)

	)
	,
	@OA\Property(
	property="isDisabledAddEvnInfectNotify",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="isVisibleOnko",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="isDisabledAddEvnOnkoNotify",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="listPersonRegister",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="onko",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="PersonRegisterType_id",
	description="Тип регистра, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PersonRegisterType_SysNick",
	description="Тип регистра, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="MorbusType_id",
	description="Тип заболевания, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MorbusType_SysNick",
	description="Тип заболевания, системное наименование",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="prof",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="PersonRegisterType_id",
	description="Тип регистра, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PersonRegisterType_SysNick",
	description="Тип регистра, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="MorbusType_id",
	description="Тип заболевания, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MorbusType_SysNick",
	description="Тип заболевания, системное наименование",
	type="string",

	)

	)

	)

	)

	)
	,
	@OA\Property(
	property="isVisibleProf",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="isDisabledAddEvnNotifyProf",
	description="Описание",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mGetEvnSectionViewData_get() {

		$data = $this->ProcessInputData('mGetEvnSectionViewData', false, true);
		$resp = $this->dbmodel->mGetEvnSectionViewData($data);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * method
	 */
	function mSaveEvnSection_post(){

		$this->inputRules['mSaveEvnSection'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);

		$this->inputRules['mSaveEvnSection'] = array_merge($this->inputRules['mSaveEvnSection'], array(
			'silentSave' => array(
				'field' => 'silentSave',
				'label' => 'Автосохранение',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSectionBedProfileLink_fedid',
				'label' => 'таблица стыковки fed.LpuSectionBedProfileLink',
				'rules' => '',
				'type' => 'id'
			)
		));

		if (isset($_POST['birthDataPresented']) && ('2' == $_POST['birthDataPresented'])) {
			//заполнены даные о беременности
			$this->inputRules['mSaveEvnSection'] = array_merge($this->inputRules['mSaveEvnSection'], $this->inputRules['evnBirthData']);
		}

		$data = $this->ProcessInputData('mSaveEvnSection', false, true);

		if ( empty($data['silentSave']) && empty($data['isAutoCreate']) ) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}

		$response = $this->dbmodel->doSave($data);

		if (!empty($resp[0])) $response = $response[0];
		if (!empty($response['Error_Msg'])) {

			$response = array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => $response['Error_Msg']
			);

		} else {
			$response = array_merge($response, array('success'=> true, 'error_code' => 0));
		}

		$this->response($response);
	}

	/**
	@OA\post(
	path="/api/rish/EvnSection/mUpdateEvnSection",
	tags={"EvnSection"},
	summary="Обновление движения",

	@OA\Parameter(
	name="EvnSection_id",
	in="query",
	description="Идентификатор случая движения пациента в стационаре",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_pid",
	in="query",
	description="Идентификатор карты выбывшего из стационара",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_setDate",
	in="query",
	description="Дата поступления",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnSection_setTime",
	in="query",
	description="Время поступления",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор человека",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonEvn_id",
	in="query",
	description="Идентификатор состояния человека",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Server_id",
	in="query",
	description="Источник данных",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_disDate",
	in="query",
	description="Дата выписки",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnSection_disTime",
	in="query",
	description="Время выписки",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsAdultEscort",
	in="query",
	description="Сопровождается взрослым",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsMedReason",
	in="query",
	description="По медицинским показаниям",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PayType_id",
	in="query",
	description="Вид оплаты",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PayTypeERSB_id",
	in="query",
	description="Тип оплаты",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Отделение",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedPersonal_id",
	in="query",
	description="Врач",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_id",
	in="query",
	description="Рабочее место врача",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionWard_id",
	in="query",
	description="Палата",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_id",
	in="query",
	description="Основной диагноз",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_eid",
	in="query",
	description="Внешняя причина",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DiagSetPhase_id",
	in="query",
	description="Фаза/стадия",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DiagSetPhase_aid",
	in="query",
	description="Состояние пациента при выписке",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrivilegeType_id",
	in="query",
	description="Впервые выявленная инвалидность",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_PhaseDescr",
	in="query",
	description="Расшифровка",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnSection_Absence",
	in="query",
	description="Отсутствовал (дней)",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="CureResult_id",
	in="query",
	description="Итог лечения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Mes_id",
	in="query",
	description="МЭС",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Mes2_id",
	in="query",
	description="МЭС2",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Mes_tid",
	in="query",
	description="КСГ найденная через диагноз",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Mes_sid",
	in="query",
	description="КСГ найденная через услугу",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Mes_kid",
	in="query",
	description="КПГ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_CoeffCTP",
	in="query",
	description="Коэффициент КСКП",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnSection_insideNumCard",
	in="query",
	description="Внутр. № карты",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="MesTariff_id",
	in="query",
	description="Коэффициент",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="TariffClass_id",
	in="query",
	description="Вид тарифа",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_prmid",
	in="query",
	description="Исход в приемном отделении",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_fedid",
	in="query",
	description="Фед. результат",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDeseaseType_fedid",
	in="query",
	description="Фед. исход",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Услуга",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MesOldUslugaComplex_id",
	in="query",
	description="Связка по КСГ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_TotalFract",
	in="query",
	description="Количество фракций",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionProfile_id",
	in="query",
	description="Профиль",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionBedProfileLink_fedid",
	in="query",
	description="Профиль койки отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsMeal",
	in="query",
	description="С питанием",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsTerm",
	in="query",
	description="Случай прерван",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="HTMedicalCareClass_id",
	in="query",
	description="Вид высокотехнологичной медицинской помощи (V018)",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionTransType_id",
	in="query",
	description="LpuSectionTransType_id",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_id",
	in="query",
	description="Исход госпитализации",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedicalCareBudgType_id",
	in="query",
	description="Тип медицинской помощи",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IndexRep",
	in="query",
	description="Признак повторной подачи",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DeseaseBegTimeType_id",
	in="query",
	description="Время с начала заболевания",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DeseaseType_id",
	in="query",
	description="Характер",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="RehabScale_id",
	in="query",
	description="Оценка состояния по ШРМ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_SofaScalePoints",
	in="query",
	description="Оценка по шкале органной недостаточности c(SOFA)",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="TumorStage_id",
	in="query",
	description="Стадия выявленного ЗНО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsZNO",
	in="query",
	description="Подозрение на ЗНО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsZNORemove",
	in="query",
	description="Снятие признака подозрения на ЗНО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_BiopsyDate",
	in="query",
	description="Дата взятия биопсии",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="Diag_spid",
	in="query",
	description="Подозрение на диагноз",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PainIntensity_id",
	in="query",
	description="Интенсивность боли",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsRehab",
	in="query",
	description="По реабилитации",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="RankinScale_id",
	in="query",
	description="Значение по шкале Рэнкина при поступлении",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="RankinScale_sid",
	in="query",
	description="Значение по шкале Рэнкина при выписке",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_InsultScale",
	in="query",
	description="Значение шкалы инсульта Национального института здоровья",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_NIHSSAfterTLT",
	in="query",
	description="Значение шкалы инсульта Национального института здоровья после проведения ТЛТ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_NIHSSLeave",
	in="query",
	description="Значение шкалы инсульта Национального института здоровья при выписке ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionBedProfile_id",
	in="query",
	description="Профиль койки отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsST",
	in="query",
	description="Подъём сегмента ST",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_isPartialPay",
	in="query",
	description="Частичная оплата",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsCardShock",
	in="query",
	description="Осложнен кардиогенным шоком",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_StartPainHour",
	in="query",
	description="Время от начала боли, часов",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_StartPainMin",
	in="query",
	description="Время от начала боли, минут",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_GraceScalePoints",
	in="query",
	description="Кол-во баллов по шкале GRACE",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_BarthelIdx",
	in="query",
	description="Индекс Бартел",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="RehabScale_vid",
	in="query",
	description="Оценка состояния по ШРМ при выписке",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsMultiKSG",
	in="query",
	description="Более одной КСГ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="GetBed_id",
	in="query",
	description="Профиль койки",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="isAutoCreate",
	in="query",
	description="Флаг автоматического сохранения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="vizit_direction_control_check",
	in="query",
	description="Контроль пересечения движения с посещением",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="vizit_kvs_control_check",
	in="query",
	description="Контроль пересечения посещения с КВС",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="vizit_intersection_control_check",
	in="query",
	description="Контроль пересечения посещений",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreParentEvnDateCheck",
	in="query",
	description="Признак игнорирования проверки периода выполенения услуги",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="checkIsOMS",
	in="query",
	description="Проверка диагноза",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreEvnUslugaKSGCheck",
	in="query",
	description="Признак игнорирования проверки наличия услуги",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreDiagKSGCheck",
	in="query",
	description="Признак игнорирования проверки КСГ по диагнозу",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreNotHirurgKSG",
	in="query",
	description="Признак игнорирования проверки нехирургической КСГ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreFirstDisableCheck",
	in="query",
	description="Признак игнорирования проверки первичности инвалидности",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreMorbusOnkoDrugCheck",
	in="query",
	description="Признак игнорирования проверки препаратов в онко заболевании",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="silentSave",
	in="query",
	description="Автосохранение",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_Code",
	in="query",
	description="Код исхода госпитализации",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_SysNick",
	in="query",
	description="Системное наименование исхода госпитализации",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnDie_id",
	in="query",
	description="Идентификатор исхода 'Смерть'",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnLeave_id",
	in="query",
	description="Идентификатор исхода 'Выписка'",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnOtherLpu_id",
	in="query",
	description="Идентификатор исхода 'Перевод в другое ЛПУ'",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnOtherSection_id",
	in="query",
	description="Идентификатор исхода 'Перевод в другое отделение'",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnOtherSectionBedProfile_id",
	in="query",
	description="Идентификатор исхода 'Перевод на другой профиль коек'",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnOtherStac_id",
	in="query",
	description="Идентификатор исхода 'Перевод в стационар другого типа'",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnLeave_UKL",
	in="query",
	description="Уровень качества лечения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="LeaveCause_id",
	in="query",
	description="Исход госпитализации",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDesease_id",
	in="query",
	description="Исход заболевания",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Org_aid",
	in="query",
	description="Организация",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedPersonal_did",
	in="query",
	description="Врач, установивший смерть",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_did",
	in="query",
	description="Рабочее место врача, установившего смерть",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedPersonal_aid",
	in="query",
	description="Врач-патологоанатом",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_aid",
	in="query",
	description="Отделение",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DeathPlace_id",
	in="query",
	description="Идентификатор места смерти",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="editAnatom",
	in="query",
	description="Призак редактирования экспертизы",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="AnatomWhere_id",
	in="query",
	description="Место проведения экспертизы",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_aid",
	in="query",
	description="Основной патологоанатомический диагноз",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ESecEF_EvnSection_IsZNOCheckbox",
	in="query",
	description="Подозрение на ЗНО",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnDie_expDate",
	in="query",
	description="Дата проведения экспертизы",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnDie_expTime",
	in="query",
	description="Время проведения экспертизы",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnDie_IsWait",
	in="query",
	description="Умер в приемном покое",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDie_IsAnatom",
	in="query",
	description="Признак необходимости проведения экспертизы",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnLeave_IsAmbul",
	in="query",
	description="Направлен на амбулаторное лечение",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Org_oid",
	in="query",
	description="ЛПУ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_oid",
	in="query",
	description="Отделение",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuUnitType_oid",
	in="query",
	description="Тип стационара",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionBedProfile_oid",
	in="query",
	description="Профиль коек",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionBedProfileLink_fedoid",
	in="query",
	description="Профиль коек",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ChildTermType_id",
	in="query",
	description="Доношенность",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="FeedingType_id",
	in="query",
	description="Вид вскармливания",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_id",
	in="query",
	description="Идентификатор сведений о новорожденном",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsAidsMother",
	in="query",
	description="ВИЧ-инфекция у матери",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsBCG",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_Breast",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_Head",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_Height",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_Weight",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsHepatit",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsHighRisk",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsAudio",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsBleeding",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsBreath",
	in="query",
	description="Дыхание",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsHeart",
	in="query",
	description="Сердцебиение",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsPulsation",
	in="query",
	description="Пульсация пуповины",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsMuscle",
	in="query",
	description="Произвольное сокращение мускулатуры",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewborn_BloodBili",
	in="query",
	description="Общий билирубин",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewborn_BloodHemoglo",
	in="query",
	description="Гемоглобин",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewborn_BloodEryth",
	in="query",
	description="Эритроциты",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewborn_BloodHemato",
	in="query",
	description="Гематокрит",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="NewBornWardType_id",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsNeonatal",
	in="query",
	description="БЦЖ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="personHeightData",
	in="query",
	description="Измерения длины (роста) новорожденного",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="personWeightData",
	in="query",
	description="Измерения массы новорожденного",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_BCGNum",
	in="query",
	description="Номер (БЦЖ)",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_BCGDate",
	in="query",
	description="Номер (БЦЖ)",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="isPersonNewBorn",
	in="query",
	description="isPersonNewBorn",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_HepatitDate",
	in="query",
	description="Номер (БЦЖ)",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="BirthSpecStac_id",
	in="query",
	description="Номер (БЦЖ)",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ChildPositionType_id",
	in="query",
	description="Предлежание",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_CountChild",
	in="query",
	description="Который по счету",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ApgarData",
	in="query",
	description="Который по счету",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonBirthTraumaData",
	in="query",
	description="Который по счету",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_IsRejection",
	in="query",
	description="Отказ от ребенка",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_HepatitSer",
	in="query",
	description="Серия (БЦЖ)",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_HepatitNum",
	in="query",
	description="Серия (БЦЖ)",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PersonNewBorn_BCGSer",
	in="query",
	description="Серия (БЦЖ)",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="birthDataPresented",
	in="query",
	description="Заполнять ли данные по беременности и родам",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="DataViewDiag",
	in="query",
	description="Данные по клиническим диагнозам",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="ignoreCheckEvnUslugaChange",
	in="query",
	description="Признак игнорирования проверки изменения привязок услуг",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreCheckEvnUslugaDates",
	in="query",
	description="Признак игнорирования проверки дат услуг",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreEvnUslugaHirurgKSGCheck",
	in="query",
	description="Признак игнорирования проверки услуг по хирургической КСГ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreCheckKSGisEmpty",
	in="query",
	description="Признак игнорирования проверки пустой КСГ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreCheckCardioFieldsEmpty",
	in="query",
	description="Признак игнорирования проверки полей кардио-блока",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreCheckTNM",
	in="query",
	description="Признак игнорирования проверки соответствия диагноза и TNM",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreCheckMorbusOnko",
	in="query",
	description="Признак игнорирования проверки перед удалением специфики",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnSection_IsCardioCheck",
	in="query",
	description="Признак необходимости проверок поей кардио-блока",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PregnancyEvnPS_Period",
	in="query",
	description="Срок беременности",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonRegister_id",
	in="query",
	description="Идентификатор записи в базовом регистре",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonPregnancy",
	in="query",
	description="Анкета по беременности",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="PregnancyScreenList",
	in="query",
	description="Скрининги беременности",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="BirthCertificate",
	in="query",
	description="Родовой сертификат",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="BirthSpecStac",
	in="query",
	description="Исход беременности",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="skipPersonRegisterSearch",
	in="query",
	description="Пропустить поиск записи в регистре беременных",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DrugTherapyScheme_ids",
	in="query",
	description="Схема лекарственной терапии",
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
	property="EvnSection_id",
	description="Движение в отделении, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="success",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="error_code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Описание",
	type="string",

	)

	)
	)

	)

	 */
	function mUpdateEvnSection_post(){

		$this->inputRules['mUpdateEvnSection'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);

		// при обновлении обязательность полей не проверяем
		$GLOBALS['isSwanApiKey'] = true;

		// переопределяем тип чекбокса на обычный числовой,
		// чтобы избавиться от значений по умолчанию = 1
		$GLOBALS['transformCheckboxInputType'] = true;

		$data = $this->ProcessInputData('mUpdateEvnSection');

		$this->inputRules['mUpdateEvnSection'] = array_merge($this->inputRules['mUpdateEvnSection'], array(
			'silentSave' => array(
				'field' => 'silentSave',
				'label' => 'Автосохранение',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSectionBedProfileLink_fedid',
				'label' => 'таблица стыковки fed.LpuSectionBedProfileLink',
				'rules' => '',
				'type' => 'id'
			)
		));

		if (isset($_POST['birthDataPresented']) && ('2' == $_POST['birthDataPresented'])) {
			//заполнены даные о беременности
			$this->inputRules['mUpdateEvnSection'] = array_merge($this->inputRules['mUpdateEvnSection'], $this->inputRules['evnBirthData']);
		}

		$response = $this->dbmodel->mUpdateEvnSection($data, $this->_args);

		if (!empty($resp[0])) $response = $response[0];
		if (!empty($response['Error_Msg'])) {

			$response = array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => $response['Error_Msg']
			);

		} else {
			$response = array_merge($response, array('success'=> true, 'error_code' => 0));
		}

		$this->response($response);
	}
}