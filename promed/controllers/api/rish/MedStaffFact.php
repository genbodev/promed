<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с местом работы
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

class MedStaffFact extends SwREST_Controller {

	protected $inputRules = array(
		'getDoctors' => array(
			array('field' => 'start', 'label' => 'Начальная позиция', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => 'Конечная позиция', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_FullName', 'label' => 'ФИО', 'rules' => '', 'type' => 'string'),
			array('field' => 'isChildDoctor', 'label' => 'Признак детского врача', 'rules' => '', 'type' => 'api_flag_nc'),
			array('field' => 'Sex_id', 'label' => 'Пол врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'acceptDate', 'label' => 'Желаемая дата записи', 'rules' => '', 'type' => 'string'),
			array('field' => 'minCost', 'label' => 'Мин. цена', 'rules' => '', 'type' => 'int'),
			array('field' => 'maxCost', 'label' => 'Макс. цена', 'rules' => '', 'type' => 'int'),
			array('field' => 'sort', 'label' => 'Вид сортировки', 'rules' => '', 'type' => 'string'),
			array('field' => 'isPaid', 'label' => 'Признак платного врача', 'rules' => '', 'type' => 'api_flag_nc'),
			array('field' => 'allowTodayRecord', 'label' => 'Признак что запись на сегодня разрешена', 'rules' => '', 'type' => 'api_flag_nc')
		),
		'getDoctorsTotalCount' => array(
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_FullName', 'label' => 'ФИО', 'rules' => '', 'type' => 'string'),
			array('field' => 'isChildDoctor', 'label' => 'Признак детского врача', 'rules' => '', 'type' => 'api_flag_nc'),
			array('field' => 'Sex_id', 'label' => 'Пол врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'acceptDate', 'label' => 'Желаемая дата записи', 'rules' => '', 'type' => 'string'),
			array('field' => 'minCost', 'label' => 'Мин. цена', 'rules' => '', 'type' => 'int'),
			array('field' => 'maxCost', 'label' => 'Макс. цена', 'rules' => '', 'type' => 'int'),
			array('field' => 'isPaid', 'label' => 'Признак платного врача', 'rules' => '', 'type' => 'api_flag_nc')
		),
		'getCostLimits' => array(
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_FullName', 'label' => 'ФИО', 'rules' => '', 'type' => 'string'),
			array('field' => 'isChildDoctor', 'label' => 'Признак детского врача', 'rules' => '', 'type' => 'api_flag_nc'),
			array('field' => 'Sex_id', 'label' => 'Пол врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'acceptDate', 'label' => 'Желаемая дата записи', 'rules' => '', 'type' => 'string')
		),
		'getDoctorInfo' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'),
		),
		'getMedStaffFactInfo' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MedStaffFact_model', 'dbmodel');
		$this->inputRules = array_merge($this->inputRules, $this->dbmodel->inputRules);
	}

	/**
	 * Получение строки штатного расписания по идентификатору
	 */
	function MedStaffFactById_get() {
		$data = $this->ProcessInputData('loadMedStaffFactById');

		if(empty($data['MedStaffFact_id']) && empty($data['MedStaffFactOuter_id']) && empty($data['Person_id'])){
			$this->response(array(
				'error_msg' => 'Не передан ни один идентификатор',
				'error_code' => '6',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadMedStaffFactById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка мест работы по МО и профилю
	 */
	function MedStaffFactListByMOandProfile_get() {
		$data = $this->ProcessInputData('loadMedStaffFactByMOandProfile');
		
		$sp = getSessionParams();
		if( !empty($data['Lpu_id']) && $data['Lpu_id'] != $sp['Lpu_id'] ){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->loadMedStaffFactByMOandProfile($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание места работы
	 */
	function index_post() {
		$data = $this->ProcessInputData('createMedStaffFact');
		if ($data === false) 
		{ 
			$this->response(array(
				'error_code' => 4,
				'error_msg' => 'Не переданы необходимые параметры'
			));
		}

		$fields = array('IsOms','IsDirRec','IsQueueOnFree','IsNotReception','IsDummyWP','IsSpecSet','IsHomeVisit','DisableWorkPlaceChooseInDocuments');
		foreach ($fields as $field) {
			if(!empty($data[$field]) && ($data[$field] == 2 || $data[$field] == 1)){
				$data[$field] = 1;
			} else {
				$data[$field] = 0;
			}
		}
		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$reslpu = $this->dbmodel->getFirstRowFromQuery("
			select Lpu_id from dbo.MedStaffFactCache (nolock) where Staff_id = :Staff_id
		", $data);
		if(isset($reslpu['Lpu_id']) && $reslpu['Lpu_id'] != $sp['Lpu_id'] ){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$this->dbmodel->beginTransaction();
		$resp = $this->dbmodel->createMedStaffFact($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->dbmodel->rollbackTransaction();
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedStaffFact_id'])) {
			$this->dbmodel->rollbackTransaction();
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->dbmodel->commitTransaction();
		$this->response(array(
			'error_code' => 0,
			'data' => array('MedStaffFact_id'=>$resp[0]['MedStaffFact_id'])
		));
	}

	/**
	 * Редактирование строки штатного расписания
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateMedStaffFact');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMedStaffFact');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->loadMedStaffFactById($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$fields = array('IsOms','IsDirRec','IsQueueOnFree','IsNotReception','IsDummyWP','IsSpecSet','IsHomeVisit','DisableWorkPlaceChooseInDocuments');
		foreach ($fields as $field) {
			if(!empty($data[$field]) && ($data[$field] == 2 || $data[$field] == 1)){
				$data[$field] = 1;
			} else {
				$data[$field] = 0;
			}
		}

		$data = array_merge($old_data[0], $data);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		if(isset($old_data[0]['Lpu_id']) && $old_data[0]['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		$resp = $this->dbmodel->queryResult("select top 1 id from persis.WorkPlace with (nolock) where id = :MedStaffFact_id", $data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора места работы',
				'error_code' => '6'
			));
		}
		
		$resp = $this->dbmodel->updateMedStaffFact($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение мест работы по МО
	 */
	function MedStaffFactByMO_get() {
		$data = $this->ProcessInputData('MedStaffFactByMO');

		$resp = $this->dbmodel->getMedStaffFactByMo($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение мест работы по МО
	 */
	function mgetMedStaffFactAll_get() {

		$data = $this->ProcessInputData('mgetMedStaffFactAll');

		$resp = $this->dbmodel->getMedStaffFactAll($data);
		//$this->showResultParamDescription($resp[0]);

		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение мест работы по идентификатору сотрудника
	 */
	function MedStaffFactByMedPersonal_get() {
		$data = $this->ProcessInputData('MedStaffFactByMedPersonal');

		$resp = $this->dbmodel->getMedStaffFactByMedPersonal($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function getDoctors_get()	{

		$data = $this->ProcessInputData('getDoctors');

		$resp = $this->dbmodel->getDoctors($data);
		$this->response(array('error_code' => 0, 'data' => $resp));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function getDoctorsTotalCount_get()	{

		$data = $this->ProcessInputData('getDoctorsTotalCount');
		$this->load->model('MedStaffFact_model');

		$resp = $this->dbmodel->getDoctorsTotalCount($data);
		$this->response(array('error_code' => 0, 'data' => $resp));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function getCostLimits_get()	{

		$data = $this->ProcessInputData('getCostLimits');
		$this->load->model('MedStaffFact_model');

		$resp = $this->dbmodel->getCostLimits($data);

		$this->response(array('error_code' => 0, 'data' => $resp));
	}

	/**
	 * получение краткой инфы по врачам
	 */
	function getMedStaffFactInfo_get()	{

		$data = $this->ProcessInputData('getMedStaffFactInfo');

		$resp = $this->dbmodel->getMedStaffFactShortInfo($data);
		$this->response(array('error_code' => 0, 'data' => $resp));
	}

	/**
	 * @OA\get(
	path="/api/rish/MedStaffFact/getDoctorInfo",
	tags={"MedStaffFact"},
	summary="Получить информацию по доктору",

	@OA\Parameter(
	name="MedStaffFact_id",
	in="query",
	description="Идентификатор рабочего места врача",
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
	property="id",
	description="Идентификатор",
	type="string",

	)
	,
	@OA\Property(
	property="doctor_fio",
	description="ФИО доктора",
	type="string",

	)
	,
	@OA\Property(
	property="profile_name",
	description="Название профиля",
	type="string",

	)
	,
	@OA\Property(
	property="profile_id",
	description="Идентификатор профиля",
	type="integer",

	)
	,
	@OA\Property(
	property="category",
	description="Категория",
	type="string",

	)
	,
	@OA\Property(
	property="lpu_nick",
	description="Наименовение ЛПУ",
	type="string",

	)
	,
	@OA\Property(
	property="unit_name",
	description="Наименование подразделения",
	type="string",

	)
	,
	@OA\Property(
	property="address",
	description="Адрес",
	type="string",

	)
	,
	@OA\Property(
	property="current_main",
	description="Текущее место работы",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="FullName",
	description="Полное имя",
	type="string",

	)
	,
	@OA\Property(
	property="QualificationCat_Name",
	description="Наименовение квалификационной категории",
	type="string",

	)
	,
	@OA\Property(
	property="QualificationCategory",
	description="Квалификационной категории",
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
	property="WorkData_begDate",
	description="Начало работы",
	type="string",

	)
	,
	@OA\Property(
	property="WorkData_endDate",
	description="Окончание работы",
	type="string",

	)
	,
	@OA\Property(
	property="WorkType_id",
	description="Тип рабочего места",
	type="integer",

	)
	,
	@OA\Property(
	property="Dolgnost_Name",
	description="Название должности",
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
	property="Lpu_Name",
	description="Название ЛПУ",
	type="string",

	)
	,
	@OA\Property(
	property="MedSpecOms_Name",
	description="справочник специальностей врачей по ОМС, Наименование записи",
	type="string",

	)
	,
	@OA\Property(
	property="MedSpecOms_Code",
	description="справочник специальностей врачей по ОМС, Код записи",
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
	property="LpuUnit_Name",
	description="Группы отделений, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Address_id",
	description="Справочник адресов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MedStaffFact_id",
	description="Идентификатор места работы",
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
	property="Age",
	description="Возраст",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnit_Address",
	description="Адрес подразделения",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="current_addons",
	description="Остальные места работы",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="FullName",
	description="Полное имя",
	type="string",

	)
	,
	@OA\Property(
	property="QualificationCat_Name",
	description="Название квалификации",
	type="string",

	)
	,
	@OA\Property(
	property="QualificationCategory",
	description="Квалификационная категория",
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
	property="WorkData_begDate",
	description="Дата начала работы",
	type="string",

	)
	,
	@OA\Property(
	property="WorkData_endDate",
	description="Дата окончания работы",
	type="string",

	)
	,
	@OA\Property(
	property="WorkType_id",
	description="Тип рабочего места",
	type="integer",

	)
	,
	@OA\Property(
	property="Dolgnost_Name",
	description="Название должности",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="Идентфикатор ЛПУ",
	type="integer",

	)
	,
	@OA\Property(
	property="Lpu_Name",
	description="Название ЛПУ",
	type="string",

	)
	,
	@OA\Property(
	property="MedSpecOms_Name",
	description="справочник специальностей врачей по ОМС, Наименование записи",
	type="string",

	)
	,
	@OA\Property(
	property="MedSpecOms_Code",
	description="справочник специальностей врачей по ОМС, Код записи",
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
	property="LpuUnit_Name",
	description="Группы отделений, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Address_id",
	description="Справочник адресов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MedStaffFact_id",
	description="Идентификатор места работы",
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
	property="Age",
	description="Возраст",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnit_Address",
	description="Адрес подразделения",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="dolgnost",
	description="ДОлжность",
	type="string",

	)
	,
	@OA\Property(
	property="educations",
	description="Образование",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="YearOfGraduation",
	description="Год окончаения учёбы",
	type="string",

	)
	,
	@OA\Property(
	property="EducationType_Name",
	description="Уровень обучения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EducationInstitution_Name",
	description="Название образовательного учреждения",
	type="string",

	)
	,
	@OA\Property(
	property="Speciality_id",
	description="Идентификатор специальности",
	type="integer",

	)
	,
	@OA\Property(
	property="DiplomaSpeciality_Name",
	description="Название специальности в дипломе",
	type="string",

	)
	,
	@OA\Property(
	property="AcademicMedicalDegree_Name",
	description="Учёная степень",
	type="string",

	)
	,
	@OA\Property(
	property="Speciality_Code",
	description="Код специальности",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="annot",
	description="Примечание",
	type="string",

	)

	)

	)

	)
	)

	)
	 */

	function getDoctorInfo_get() {
		$data = $this->ProcessInputData('getDoctorInfo');
		$this->load->model('MedStaffFact_model');
		$doctor = $this->dbmodel->getDoctorInfo($data);
		$hdoctor = $this->dbmodel->getDoctorInfoDop($data);
		$annot = $doctor['annot']['annotation_comment'];

		$resp = array(
			'id' => $doctor['MedStaffFact_id'],
			'doctor_fio' => $doctor['FullName'],
			'profile_name' => $doctor['ProfileSpec_Name'],
			'profile_id' => $doctor['LpuSectionProfile_id'],
			'category' => $doctor['QualificationCat_Name'],
			'lpu_nick' => $doctor['Lpu_Nick'],
			'unit_name' => $doctor['LpuUnit_Name'],
			'address' => $doctor['LpuUnit_Address'],

			'current_main' => !empty($hdoctor['current_main']) ? $hdoctor['current_main'] : null,
			'current_addons' => !empty($hdoctor['current_addons']) ? $hdoctor['current_addons'] : null,
			'dolgnost' => !empty($hdoctor['current_main']['Dolgnost_Name']) ? $hdoctor['current_main']['Dolgnost_Name'] : null,
			'educations' => array(),
			'annot' => $annot,

		);

		$resp['current_main']['firstFreeDate'] =  $doctor['firstFreeDate'];
		if(!empty($hdoctor['educations']) && count($hdoctor['educations']) > 0) {
			foreach ($hdoctor['educations'] as $ed) {
				$resp['educations'][] = $ed;
			}
		}

		$this->response(array('error_code' => 0, 'data' => $resp));
	}
}