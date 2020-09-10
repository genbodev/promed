<?php defined('BASEPATH') or die ('No direct script access allowed');

class MedStaffFact_model extends swPgModel {

    protected $dateTimeForm104 = "'DD.MM.YYYY'";
    protected $dateTimeForm108 = "HH24:MI:SS";
    protected $dateTimeForm120 = "'YYYY-MM-DD HH24:MI:SS'";

	public $inputRules = array(
		'createMedStaffFact' => array(
			array('field' => 'MedPersonal_id', 'label' => 'идентификатор сотрудника', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Staff_id', 'label' => 'Идентификатор строки штатного расписания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TabCode', 'label' => 'Табельный номер', 'rules' => '', 'type' => 'string'),
			array('field' => 'IsDummyWP', 'label' => 'Флаг фиктивного места работы', 'rules' => 'required', 'type' => 'api_flag_nc'),
			array('field' => 'PostOccupationType_id', 'label' => 'Тип занятия должности', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Rate', 'label' => 'Ставка', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'WorkMode_id', 'label' => 'Режим работы', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'MilitaryRelation_id', 'label' => 'Отношение к военной службе', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Тип подразделения', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'MedSpecOms_id', 'label' => 'Специальность врача', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Population', 'label' => 'Численность прикрепления', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'IsOMS', 'label' => 'Работает в ОМС', 'rules' => 'required', 'type' => 'api_flag_nc'),
			array('field' => 'DisableWorkPlaceChooseInDocuments', 'label' => 'Флаг запрета выбора места работы в документах', 'rules' => 'required', 'type' => 'api_flag_nc'),
			array('field' => 'Comments', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
			array('field' => 'RecType_id', 'label' => 'Тип записи', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'PriemTime', 'label' => 'Время приема', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'IsDirRec', 'label' => 'Разрешать запись к врачу через направление', 'rules' => 'required', 'type' => 'api_flag_nc'),
			array('field' => 'IsQueueOnFree', 'label' => 'Флаг, позволение посещения в очередь при наличии свободных бирок', 'rules' => 'required', 'type' => 'api_flag_nc'),
			array('field' => 'IsNotReception', 'label' => 'Флаг, не вести участковый прием', 'rules' => 'required', 'type' => 'api_flag_nc'),
			array('field' => 'Descr', 'label' => 'Примечание врача', 'rules' => '', 'type' => 'string'),
			array('field' => 'Contacts', 'label' => 'Контактная информация', 'rules' => '', 'type' => 'string'),
			array('field' => 'DLOBeginDate', 'label' => 'Дата включения в ДЛО', 'rules' => '', 'type' => 'date'),
			array('field' => 'DLOEndDate', 'label' => 'Дата исключения из ДЛО', 'rules' => '', 'type' => 'date'),
			array('field' => 'CommonLabourYears', 'label' => 'Непрерывный медицинский стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'CommonLabourMonths', 'label' => 'Непрерывный медицинский стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'CommonLabourDays', 'label' => 'Непрерывный медицинский стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'SpecialLabourDays', 'label' => 'В том числе специальный стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'SpecialLabourMonths', 'label' => 'В том числе специальный стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'SpecialLabourYears', 'label' => 'В том числе специальный стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'ArriveRecordType_id', 'label' => 'Запись на начало', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ArriveOrderNumber', 'label' => 'Номер приказа на начало', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'BeginDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'AdditionalAgreementDate', 'label' => 'Дата заключения доп. соглашения', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'AdditionalAgreementNumber', 'label' => 'Номер доп. соглашения', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LeaveRecordType_id', 'label' => 'Запись на окончание', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'LeaveOrderNumber', 'label' => 'Номер приказа на окончание', 'rules' => '', 'type' => 'string'),
			array('field' => 'EndDate', 'label' => 'Дата окончания', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'OfficialSalary', 'label' => 'Должностной оклад', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'QualificationLevel', 'label' => 'Квалификационный уровень', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'MedStaffFactOuter_id', 'label' => 'Идентификатор места работы во внешней МИС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'IsNotShown', 'label' => 'Не отображать на региональном портале', 'rules' => '', 'type' => 'int', 'default' => 0)
		),
		'loadMedStaffFactById' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFactOuter_id',
				'label' => 'Идентификатор места работы во внешней МИС',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Ид человека',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'loadMedStaffFactByMOandProfile' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Идентификатор профиля',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'updateMedStaffFact' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'Staff_id', 'label' => 'Идентификатор строки штатного расписания', 'rules' => '', 'type' => 'id'),
			array('field' => 'TabCode', 'label' => 'Табельный номер', 'rules' => '', 'type' => 'string'),
			array('field' => 'IsDummyWP', 'label' => 'Флаг фиктивного места работы', 'rules' => 'trim', 'type' => 'api_flag_nc'),
			array('field' => 'PostOccupationType_id', 'label' => 'Тип занятия должности', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Rate', 'label' => 'Ставка', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'WorkMode_id', 'label' => 'Режим работы', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'MilitaryRelation_id', 'label' => 'Отношение к военной службе', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Тип подразделения', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'MedSpecOms_id', 'label' => 'Специальность врача', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Population', 'label' => 'Численность прикрепления', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'IsOMS', 'label' => 'Работает в ОМС', 'rules' => 'trim', 'type' => 'api_flag_nc'),
			array('field' => 'DisableWorkPlaceChooseInDocuments', 'label' => 'Флаг запрета выбора места работы в документах', 'rules' => 'trim', 'type' => 'api_flag_nc'),
			array('field' => 'Comments', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
			array('field' => 'RecType_id', 'label' => 'Тип записи', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'PriemTime', 'label' => 'Время приема', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'IsDirRec', 'label' => 'Разрешать запись к врачу через направление', 'rules' => '', 'type' => 'api_flag_nc'),
			array('field' => 'IsQueueOnFree', 'label' => 'Флаг, позволение посещения в очередь при наличии свободных бирок', 'rules' => '', 'type' => 'api_flag_nc'),
			array('field' => 'IsNotReception', 'label' => 'Флаг, не вести участковый прием', 'rules' => 'trim', 'type' => 'api_flag_nc'),
			array('field' => 'Descr', 'label' => 'Примечание врача', 'rules' => '', 'type' => 'string'),
			array('field' => 'Contacts', 'label' => 'Контактная информация', 'rules' => '', 'type' => 'string'),
			array('field' => 'DLOBeginDate', 'label' => 'Дата включения в ДЛО', 'rules' => '', 'type' => 'date'),
			array('field' => 'DLOEndDate', 'label' => 'Дата исключения из ДЛО', 'rules' => '', 'type' => 'date'),
			array('field' => 'CommonLabourYears', 'label' => 'Непрерывный медицинский стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'CommonLabourMonths', 'label' => 'Непрерывный медицинский стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'CommonLabourDays', 'label' => 'Непрерывный медицинский стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'SpecialLabourDays', 'label' => 'В том числе специальный стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'SpecialLabourMonths', 'label' => 'В том числе специальный стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'SpecialLabourYears', 'label' => 'В том числе специальный стаж на момент начала работы', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'ArriveRecordType_id', 'label' => 'Запись на начало', 'rules' => '', 'type' => 'id'),
			array('field' => 'ArriveOrderNumber', 'label' => 'Номер приказа на начало', 'rules' => '', 'type' => 'string'),
			array('field' => 'BeginDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date'),
			array('field' => 'AdditionalAgreementDate', 'label' => 'Дата заключения доп. соглашения', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'AdditionalAgreementNumber', 'label' => 'Номер доп. соглашения', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LeaveRecordType_id', 'label' => 'Запись на окончание', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'LeaveOrderNumber', 'label' => 'Номер приказа на окончание', 'rules' => '', 'type' => 'string'),
			array('field' => 'EndDate', 'label' => 'Дата окончания', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'OfficialSalary', 'label' => 'Должностной оклад', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'QualificationLevel', 'label' => 'Квалификационный уровень', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'MedStaffFactOuter_id', 'label' => 'Идентификатор места работы во внешней МИС', 'rules' => '', 'type' => 'id')
		),
		'MedStaffFactByMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedSpecOms_id', 'label' => 'Идентификатор специальности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id')
		),
		'MedStaffFactByMedPersonal' => array(
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Staff_id', 'label' => 'Идентификатор строки штатного расписания', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedSpecOms_id', 'label' => 'Идентификатор специальности', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnitType_id', 'label' => 'Идентификатор типа группы отделений МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения МО', 'rules' => '', 'type' => 'id'),
		),
		'mgetMedStaffFactAll' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedSpecOms_id', 'label' => 'Идентификатор специальности', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'start', 'label' => 'Начальный диапазон', 'rules' => '', 'type' => 'int'),
			array('field' => 'limit', 'label' => 'Конечный диапазон', 'rules' => '', 'type' => 'int'),
			array('field' => 'PostKind_id', 'label' => 'тип мед. персонала', 'rules' => '', 'type' => 'array')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
	 * Создание места работы
	 */
	function createMedStaffFact($data) {

		$query = "
			insert into persis.WorkPlace (
					insDT,
					pmUser_insID,
					updDT,
					pmUser_updID,
					version,
					MilitaryRelation_id,
					BeginDate,
					EndDate,
					ArriveOrderNumber,
					LeaveOrderNumber,
					ArriveRecordType_id,
					Comments,
					Rate,
					PostOccupationType_id,
					Population,
					MedSpecOms_id,
					FRMPSubdivision_id,
					LeaveRecordType_id,
					WorkMode_id,
					MedWorker_id,
					Staff_id,
					TabCode,
					PriemTime,
					Contacts,
					Descr,
					IsDirRec,
					IsQueueOnFree,
					IsOMS,
					RecType_id,
					DLOBeginDate,
					DLOEndDate,
					OfficialSalary,
					CommonLabourDays,
					CommonLabourYears,
					CommonLabourMonths,
					SpecialLabourDays,
					SpecialLabourYears,
					SpecialLabourMonths,
					QualificationLevel,
					AdditionalAgreementDate,
					AdditionalAgreementNumber,
					DisableWorkPlaceChooseInDocuments,
					IsNotReception,
					IsDummyWP,
					IsSpecSet,
					MedStaffFactOuter_id,
					IsHomeVisit,
					IsNotShown
				) values (
					dbo.tzGetDate(),
					:pmUser_id,
					dbo.tzGetDate(),
					:pmUser_id,
					1,
					:MilitaryRelation_id,
					:BeginDate,
					:EndDate,
					:ArriveOrderNumber,
					:LeaveOrderNumber,
					:ArriveRecordType_id,
					:Comments,
					:Rate,
					:PostOccupationType_id,
					:Population,
					:MedSpecOms_id,
					:FRMPSubdivision_id,
					:LeaveRecordType_id,
					:WorkMode_id,
					:MedPersonal_id,
					:Staff_id,
					:TabCode,
					:PriemTime,
					:Contacts,
					:Descr,
					:IsDirRec,
					:IsQueueOnFree,
					:IsOMS,
					:RecType_id,
					:DLOBeginDate,
					:DLOEndDate,
					:OfficialSalary,
					:CommonLabourDays,
					:CommonLabourYears,
					:CommonLabourMonths,
					:SpecialLabourDays,
					:SpecialLabourYears,
					:SpecialLabourMonths,
					:QualificationLevel,
					:AdditionalAgreementDate,
					:AdditionalAgreementNumber,
					:DisableWorkPlaceChooseInDocuments,
					:IsNotReception,
					:IsDummyWP,
					:IsSpecSet,
					:MedStaffFactOuter_id,
					:IsHomeVisit,
					:IsNotShown
				)

				returning id as \"MedStaffFact_id\";
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		if(is_array($resp) && !empty($resp[0]['MedStaffFact_id']) && empty($resp[0]['Error_Msg'])){
			$query2 = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from persis.p_WorkPlace_ins
                (
                    WorkPlace_id := :MedStaffFact_id,
                    IsReload := 0, --(0 работа с кешем одной записи/1 работа с кешем по всем записям)
                    IsMerge := null
			    )
			";
			$resp2 = $this->queryResult($query2, array('MedStaffFact_id'=>$resp[0]['MedStaffFact_id']));
			if(!$resp2 || !empty($resp2[0]['Error_Msg'])){
				return $resp2;
			}
		}

		return $resp;
	}

	/**
	 *  Получение места работы по идентификатору
	 */
	function loadMedStaffFactById($data) {
		$filter = "";
		if(empty($data['MedStaffFact_id']) && empty($data['MedStaffFactOuter_id']) && empty($data['Person_id'])){
			return array();
		}
		if (!empty($data['MedStaffFact_id'])) {
			$filter .= " and w.id = :MedStaffFact_id";
		}
		if (!empty($data['MedStaffFactOuter_id'])) {
			$filter .= " and w.MedStaffFactOuter_id = :MedStaffFactOuter_id";
		}
		if (!empty($data['Person_id'])) {
			$filter .= " and m.Person_id = :Person_id";
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= " and m.Lpu_id = :Lpu_id";
		}

		$query = "
				select
					w.MilitaryRelation_id as \"MilitaryRelation_id\",
					to_char(w.BeginDate, {$this->dateTimeForm120}) as \"BeginDate\",
					to_char(w.EndDate, {$this->dateTimeForm120}) as \"EndDate\",
					w.ArriveOrderNumber as \"ArriveOrderNumber\",
					w.LeaveOrderNumber as \"LeaveOrderNumber\",
					w.ArriveRecordType_id as \"ArriveRecordType_id\",
					w.Comments as \"Comments\",
					w.Rate as \"Rate\",
					w.PostOccupationType_id as \"PostOccupationType_id\",
					w.Population as \"Population\",
					w.MedSpecOms_id as \"MedSpecOms_id\",
					w.FRMPSubdivision_id as \"FRMPSubdivision_id\",
					w.LeaveRecordType_id as \"LeaveRecordType_id\",
					w.WorkMode_id as \"WorkMode_id\",
					w.MedWorker_id as \"MedPersonal_id\",
					w.Staff_id as \"Staff_id\",
					w.TabCode as \"TabCode\",
					w.PriemTime as \"PriemTime\",
					w.Contacts as \"Contacts\",
					w.Descr as \"Descr\",
					w.IsDirRec as \"IsDirRec\",
					w.IsQueueOnFree as \"IsQueueOnFree\",
					w.IsOMS as \"IsOMS\",
					w.RecType_id as \"RecType_id\",
					to_char(w.DLOBeginDate, {$this->dateTimeForm120}) as \"DLOBeginDate\",
					to_char(w.DLOEndDate, {$this->dateTimeForm120}) as \"DLOEndDate\",
					w.OfficialSalary as \"OfficialSalary\",
					w.CommonLabourDays as \"CommonLabourDays\",
					w.CommonLabourYears as \"CommonLabourYears\",
					w.CommonLabourMonths as \"CommonLabourMonths\",
					w.SpecialLabourDays as \"SpecialLabourDays\",
					w.SpecialLabourYears as \"SpecialLabourYears\",
					w.SpecialLabourMonths as \"SpecialLabourMonths\",
					w.QualificationLevel as \"QualificationLevel\",
					to_char(w.AdditionalAgreementDate, {$this->dateTimeForm120}) as \"AdditionalAgreementDate\",
					w.AdditionalAgreementNumber as \"AdditionalAgreementNumber\",
					w.DisableWorkPlaceChooseInDocuments as \"DisableWorkPlaceChooseInDocuments\",
					w.IsNotReception as \"IsNotReception\",
					w.IsDummyWP as \"IsDummyWP\",
					w.MedStaffFactOuter_id as \"MedStaffFactOuter_id\",
					m.LpuSection_id as \"LpuSection_id\",
					m.Lpu_id as \"Lpu_id\",
					m.MedStaffFact_id as \"MedStaffFact_id\"
				from persis.WorkPlace w
				left join dbo.v_MedStaffFactCache m on m.MedStaffFact_id = w.id
				where
					(1=1)
					{$filter}
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Получение данных врача для API
	 */
	function getMedStaffFactInfoForAPI($data) {
		return $this->queryResult("
			select
				msf.Server_id as \"Server_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.Person_Fio as \"Person_Fio\",
				msf.LpuSection_id as \"LpuSection_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				l.Lpu_id as \"Lpu_id\"
			from
				v_MedStaffFact msf
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_Lpu l  on l.Lpu_id = msf.Lpu_id
			where
				msf.MedStaffFact_id = :MedStaffFact_id
		", array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		));
	}

	/**
	 * Получение данных врача
	 * @return array
	 */
	function getMedStaffFactInfo($data) {

		$filter = array();

		if ( isset($data['compareDate']) )
		{
			$filter[] = '(msf.WorkData_begDate is null or msf.WorkData_begDate <= CAST(:compareDate as date))';
			$filter[] = '(msf.WorkData_endDate is null or msf.WorkData_endDate > CAST(:compareDate as date))';
			$queryParams['compareDate'] = $data['compareDate'];
		}

		if ( isset($data['Person_SurName']) )
		{
			$filter[] = "msf.Person_SurName ilike :Person_SurName";
			$queryParams['Person_SurName'] = $data['Person_SurName'];
		}

		if ( isset($data['Person_FirName']) )
		{
			$filter[] = "msf.Person_FirName ilike :Person_FirName";
			$queryParams['Person_FirName'] = $data['Person_FirName'];
		}

		if ( isset($data['Person_SecName']) )
		{
			$filter[] = "msf.Person_SecName ilike :Person_SecName";
			$queryParams['Person_SecName'] = $data['Person_SecName'];
		}

		if ( isset($data['Person_BirthDay']) )
		{
			$filter[] = "to_char(msf.Person_BirthDay::timestamp, {$this->dateTimeForm104}) = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		$filter[] = "msf.MedStaffFact_Stavka != 0";

		$sql ="
			select
				msf.Person_SurName as \"Person_SurName\",
				msf.Person_FirName as \"Person_FirName\",
				msf.Person_SecName as \"Person_SecName\",
				to_char(msf.Person_BirthDay::timestamp, {$this->dateTimeForm104}) as \"Person_BirthDay\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				mp.Dolgnost_Name as \"Dolgnost_Name\",
				msf.MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				PostOccupationType.name as \"PostOccupationTypeName\",
				MoveMedWorker.MoveInOrgRecordType_id as \"MoveInOrgRecordType_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				l.Lpu_id as \"Lpu_id\"
			from
				v_MedStaffFact msf
				left join v_MedPersonal mp on msf.MedPersonal_id = mp.MedPersonal_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_Lpu l on l.Lpu_id = msf.Lpu_id
				left join persis.WorkPlace WorkPlace on WorkPlace.id = msf.MedStaffFact_id
				left join persis.MoveMedWorker MoveMedWorker on MoveMedWorker.WorkPlace_id = WorkPlace.id
				left join persis.PostOccupationType PostOccupationType on msf.PostOccupationType_id = PostOccupationType.id
			where
			".implode(" AND ", $filter);

		//var_dump(getDebugSQL($sql, $queryParams)); exit;
		return $this->queryResult($sql, $queryParams);
	}

	/**
	 * получение краткой информации по доктору
	 */
	function getMedStaffFactShortInfo($data) {

		$result = $this->getFirstRowFromQuery("
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Lpu_id as \"Lpu_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuUnit_id as \"LpuUnit_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				msf.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				concat(msf.Person_surname,' ',msf.Person_firname,' ',msf.Person_secname) as \"MedPersonal_FullName\",
				rtrim(lsp.ProfileSpec_Name) as \"ProfileSpec_Name\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				concat(rtrim(str.KLStreet_Name),', ',rtrim(a.Address_House)) as \"LpuUnit_Address\",
				msf.MedStaffFactCache_CostRec as \"MedStaffFactCache_CostRec\",
				lb.LpuBuilding_Longitude as \"LpuBuilding_Longitude\",
                lb.LpuBuilding_Latitude as \"LpuBuilding_Latitude\"
			from v_MedStaffFact msf 
			left join v_LpuSection ls on msf.LpuSection_id = ls.LpuSection_id
			left join v_Lpu lpu on msf.Lpu_id = lpu.Lpu_id
			left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
			left join v_Address a on a.Address_id = lu.Address_id
			left join v_KLStreet str on str.KLStreet_id = a.KLStreet_id
			where msf.MedStaffFact_id = :MedStaffFact_id
			limit 1
		", $data);

		return $result;
	}

	/**
	 *  Получение списка мест работы по МО и профилю
	 */
	function loadMedStaffFactByMOandProfile($data) {

		$query = "
			select
				MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_MedStaffFact
			where
				Lpu_id = :Lpu_id
				and LpuSectionProfile_id = :LpuSectionProfile_id
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Обновление места работы
	 */
	function updateMedStaffFact($data) {

		$query = "
				update
					persis.WorkPlace
				set
					updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id,
					MilitaryRelation_id = :MilitaryRelation_id,
					BeginDate = :BeginDate,
					EndDate = :EndDate,
					ArriveOrderNumber = :ArriveOrderNumber,
					LeaveOrderNumber = :LeaveOrderNumber,
					ArriveRecordType_id = :ArriveRecordType_id,
					Comments = :Comments,
					Rate = :Rate,
					PostOccupationType_id = :PostOccupationType_id,
					Population = :Population,
					MedSpecOms_id = :MedSpecOms_id,
					FRMPSubdivision_id = :FRMPSubdivision_id,
					LeaveRecordType_id = :LeaveRecordType_id,
					WorkMode_id = :WorkMode_id,
					MedWorker_id = :MedPersonal_id,
					Staff_id = :Staff_id,
					TabCode = :TabCode,
					PriemTime = :PriemTime,
					Contacts = :Contacts,
					Descr = :Descr,
					IsDirRec = :IsDirRec,
					IsQueueOnFree = :IsQueueOnFree,
					IsOMS = :IsOMS,
					RecType_id = :RecType_id,
					DLOBeginDate = :DLOBeginDate,
					DLOEndDate = :DLOEndDate,
					OfficialSalary = :OfficialSalary,
					CommonLabourDays = :CommonLabourDays,
					CommonLabourYears = :CommonLabourYears,
					CommonLabourMonths = :CommonLabourMonths,
					SpecialLabourDays = :SpecialLabourDays,
					SpecialLabourYears = :SpecialLabourYears,
					SpecialLabourMonths = :SpecialLabourMonths,
					QualificationLevel = :QualificationLevel,
					AdditionalAgreementDate = :AdditionalAgreementDate,
					AdditionalAgreementNumber = :AdditionalAgreementNumber,
					DisableWorkPlaceChooseInDocuments = :DisableWorkPlaceChooseInDocuments,
					IsNotReception = :IsNotReception,
					IsDummyWP = :IsDummyWP,
					MedStaffFactOuter_id = :MedStaffFactOuter_id,
					IsSpecSet = :IsSpecSet,
					IsHomeVisit = :IsHomeVisit
				where
					id = :MedStaffFact_id
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		if(is_array($resp) && empty($resp[0]['Error_Msg'])){
			$query2 = "
				select
				    Error_Code as \"Error_Code\",
				    Error_Message as \"Error_Msg\"
				from persis.p_WorkPlace_upd
				(
					WorkPlace_id := :MedStaffFact_id,
					IsReload := 1,
					IsMerge := null,
				)
			";
			$resp2 = $this->queryResult($query2, array('MedStaffFact_id'=>$data['MedStaffFact_id']));
			if(!$resp2 || !empty($resp2[0]['Error_Msg'])){
				return $resp2;
			}
		}

		return $resp;
	}

	/**
	 * Получение мест работы по МО
	 */
	function getMedStaffFactByMo($data) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		if (!empty($data['MedSpecOms_id'])) {
			$filter .= " and msf.MedSpecOms_id = :MedSpecOms_id";
			$queryParams['MedSpecOms_id'] = $data['MedSpecOms_id'];
		}
		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and msf.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filter .= " and msf.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}
		$resp = $this->queryResult("
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Person_id as \"Person_id\",
				msf.Person_SurName as \"PersonSurName_SurName\",
				msf.Person_FirName as \"PersonFirName_FirName\",
				msf.Person_SecName as \"PersonSecName_SecName\",
				msf.RecType_id as \"RecType_id\",
				LU.LpuUnitType_id as \"LpuUnitType_id\",
				to_char(msf.WorkData_begDate, {$this->dateTimeForm104}) as \"MedStaffFact_setDate\",
				to_char(msf.WorkData_endDate, {$this->dateTimeForm104}) as \"MedStaffFact_disDate\"
			from
				v_MedStaffFact msf
				left join v_LpuUnit LU on LU.LpuUnit_id = msf.LpuUnit_id
			where
				msf.Lpu_id = :Lpu_id
				and (coalesce(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 * Получение всех мест работы (для АПИ)
	 */
	function getMedStaffFactAll($data) {

		$filter = "";
		$queryParams = array(
			'start' => 0,
			'limit' => 5000
		);

		if (!empty($data['start'])) {
			$queryParams['start'] = $data['start'];
		}

		if (!empty($data['limit'])) {
			$queryParams['limit'] = $data['limit'];
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= " and msf.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['MedSpecOms_id'])) {
			$filter .= " and msf.MedSpecOms_id = :MedSpecOms_id";
			$queryParams['MedSpecOms_id'] = $data['MedSpecOms_id'];
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and msf.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if (!empty($data['LpuSection_id'])) {
			$filter .= " and msf.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['PostKind_id']) && is_array($data['PostKind_id'])) {

			$postkindList = "(";
			//foreach ($data['PostKind_id'] as $pk) { $postkindList .= $pk;}
			$postkindList .= implode(',', $data['PostKind_id']);
			$postkindList .= ") ";

			$filter .= " and msf.PostKind_id in ".$postkindList;
		}

		$resp = $this->queryResult("

			with MedStaffs AS
			(
                select
                    msf.MedStaffFact_id as \"MedStaffFact_id\",
                    ROW_NUMBER() OVER (ORDER BY MedStaffFact_id) AS \"RowNumber\",
                    msf.Person_id as \"Person_id\",
                    msf.Person_SurName as \"PersonSurName_SurName\",
                    msf.Person_FirName as \"PersonFirName_FirName\",
                    msf.Person_SecName as \"PersonSecName_SecName\",
                    ls.Lpu_id as \"Lpu_id\",
                    ls.Lpu_Name as \"Lpu_Name\",
                    ls.LpuSection_id as \"LpuSection_id\",
                    ls.LpuSection_Code as \"LpuSection_Code\",
                    ls.LpuSection_Name as \"LpuSection_Name\",
                    lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                    lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
                    lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
                    mso.MedSpecOms_id as \"MedSpecOms_id\",
                    mso.MedSpecOms_Code as \"MedSpecOms_Code\",
                    mso.MedSpecOms_Name as \"MedSpecOms_Name\",
                    msf.RecType_id as \"RecType_id\",
                    LU.LpuUnitType_id as \"LpuUnitType_id\",
                    to_char(msf.WorkData_begDate, {$this->dateTimeForm104}) as \"MedStaffFact_setDate\",
                    to_char(msf.WorkData_endDate, {$this->dateTimeForm104}) as \"MedStaffFact_disDate\",
                    msf.PostKind_id as \"PostKind_id\",
                    FMS.MedSpec_id as \"FedMedSpec_id\",
                    FMS.MedSpec_Name as \"FedMedSpec_Name\",
                    msf.MedPersonal_id as \"MedPersonal_id\"
                from
                    v_MedStaffFact msf
                    left join v_LpuUnit LU on LU.LpuUnit_id = msf.LpuUnit_id
                    left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
                    left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
                    left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
                    left join fed.v_MedSpec FMS on FMS.MedSpec_id = MSO.MedSpec_id
                where (1=1) {$filter}
			)
			select
			    *
			from
			    MedStaffs
			where
			    \"RowNumber\" between :start and :limit;
		", $queryParams);

		return $resp;
	}

	/**
	 * Получение мест работы по идентификатору сотрудника
	 */
	public function getMedStaffFactByMedPersonal($data) {
		$filterList = array(
			'msf.MedPersonal_id = :MedPersonal_id',
		);
		$joinList = array();
		$queryParams = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
		);

		if ( !empty($data['Lpu_id']) ) {
			$filterList[] = 'msf.Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( !empty($data['Staff_id']) ) {
			$filterList[] = 'wp.Staff_id = :Staff_id';
			$joinList['persis.WorkPlace'] = 'left join persis.WorkPlace wp on wp.id = msf.MedStaffFact_id';
			$queryParams['Staff_id'] = $data['Staff_id'];
		}

		if ( !empty($data['MedSpecOms_id']) ) {
			$filterList[] = 'msf.MedSpecOms_id = :MedSpecOms_id';
			$queryParams['MedSpecOms_id'] = $data['MedSpecOms_id'];
		}

		if ( !empty($data['Post_id']) ) {
			$filterList[] = 'msf.Post_id = :Post_id';
			$queryParams['Post_id'] = $data['Post_id'];
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = 'msf.LpuUnit_id = :LpuUnit_id';
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty($data['LpuUnitType_id']) ) {
			$filterList[] = 'lu.LpuUnitType_id = :LpuUnitType_id';
			$joinList['v_LpuUnit'] = 'left join v_LpuUnit lu  on lu.LpuUnit_id = msf.LpuUnit_id';
			$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = 'msf.LpuSection_id = :LpuSection_id';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filterList[] = 'ls.LpuSectionProfile_id = :LpuSectionProfile_id';
			$joinList['v_LpuSection'] = 'left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id';
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		$resp = $this->queryResult('
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Person_Surname as \"PersonSurName_SurName\",
				msf.Person_Firname as \"PersonFirName_FirName\",
				msf.Person_Secname as \"PersonSecName_SecName\"
			from
				v_MedStaffFact msf
				' . implode(' ', $joinList) . '
			where
				' . implode(' and ', $filterList) . '
		', $queryParams);

		return $resp;
	}

	/**
	 * Получение специальностей по МО
	 */
	function getMedSpecOmsByMo($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$fieldList = array('msf.MedSpecOms_id as \"MedSpecOms_id\"');
		$filterList = array('msf.Lpu_id = :Lpu_id');
		$joinList = array();

		if (!empty($data['LpuBuilding_id'])) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filterList[] = "msf.LpuBuilding_id = :LpuBuilding_id";
		}

		if (!empty($data['LpuSection_id'])) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filterList[] = "msf.LpuSection_id = :LpuSection_id";
		}

		if ( !empty($data['forRecord']) ) {
			$fieldList[] = "sum(rec.TimetableGraf_Count) as \"TimetableGraf_Count\"";
			$filterList[] = "msf.MedSpecOms_id is not null";
			$joinList[] = "
				left join lateral (
					select
						count(
						    case
						        when
						         ttg.Person_id is null and ttal.TimetableTypeAttributeLink_id is not null
						        and ttg.TimeTableType_id in (1, 9, 11)
						        then TimeTableGraf_id else null end
						    ) as TimetableGraf_Count
					from
					    v_TimetableGraf_lite ttg
						left join lateral (
							select TimetableTypeAttributeLink_id
							from TimetableTypeAttributeLink
							where TimetableType_id = ttg.TimetableType_id
							and TimetableTypeAttribute_id in (8, 9)
							limit 1
						) ttal on true
					where
					    ttg.MedStaffFact_id = msf.MedStaffFact_id
					and
					    ttg.TimetableGraf_begTime >= dbo.tzGetDate()
				) rec on true
			";
		}

		if (getRegionNick() == 'penza') {
			$fieldList[] = "MIN(msc.MedSpecClass_id) as \"MedSpecClass_id\"";
			$fieldList[] = "MIN(msc.MedSpecClass_Name) as \"MedSpecClass_Name\"";
			$joinList[] = "
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join fed.v_MedSpecClass msc on msc.MedSpecClass_id = mso.MedSpecClass_id
			";
		}

		$queryParams['getDate'] = $this->tzGetDate();

		return $this->queryResult("
			select
				" . implode(",", $fieldList) . "
			from
				v_MedStaffFact msf
			" . implode(" ", $joinList) . "
			where
				" . implode(" and ", $filterList) . "
			group by msf.MedSpecOms_id
		", $queryParams);
	}

	/**
	 * Получение данных места работы врача
	 * @param array $data
	 * @return array|false
	 */
	function getMedStaffFact($data) {
		$params = array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		);
		$query = "
			select
			    MSF.Post_id as \"Post_id\",
			    MSF.Server_id as \"Server_id\",
			    MSF.Lpu_id as \"Lpu_id\",
			    MSF.Lpuunit_id as \"Lpuunit_id\",
			    MSF.LpuSection_id as \"LpuSection_id\",
			    MSF.MedPersonal_id as \"MedPersonal_id\",
			    MSF.MedPersonal_code as \"MedPersonal_Code\",
			    MSF.MedPersonal_tabcode as \"MedPersonal_TabCode\",
			    MSF.Person_id as \"Person_id\",
			    MSF.Person_SurName as \"Person_SurName\",
			    MSF.Person_Firname as \"Person_FirName\",
			    MSF.Person_SecName as \"Person_SecName\",
			    MSF.Person_Fio as \"Person_Fio\",
			    MSF.Person_Fin as \"Person_Fin\",
			    MSF.Person_Birthday as \"Person_Birthday\",
			    MSF.MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
			    MSF.MedStaffFact_IsSpecialist as \"MedStaffFact_IsSpecialist\",
			    MSF.MedStaffFact_IsOms as \"MedStaffFact_IsOms\",
			    MSF.MedStaffFact_setDate as \"MedStaffFact_setDate\",
			    MSF.MedStaffFact_disDate as \"MedStaffFact_disDate\",
			    MSF.WorkData_begDate as \"WorkData_begDate\",
			    MSF.WorkData_endDate as \"WorkData_endDate\",
			    MSF.WorkData_dlobegDate as \"WorkData_dlobegDate\",
			    MSF.WorkData_dloendDate as \"WorkData_dloendDate\",
			    MSF.MedSpecOms_id as \"MedSpecOms_id\",
			    MSF.PostMedClass_id as \"PostMedClass_id\",
			    MSF.MedicalCareKind_id as \"MedicalCareKind_id\",
			    MSF.RecType_id as \"RecType_id\",
			    MSF.MedStaffFact_PriemTime as \"MedStaffFact_PriemTime\",
			    MSF.MedStatus_id as \"MedStatus_id\",
			    MSF.MedStaffFact_IsDirRec as \"MedStaffFact_IsDirRec\",
			    MSF.MedStaffFact_IsQueueOnFree as \"MedStaffFact_IsQueueOnFree\",
			    MSF.MedStaffFact_Descr as \"MedStaffFact_Descr\",
			    MSF.MedStaffFact_Contacts as \"MedStaffFact_Contacts\",
			    MSF.MedStaffFact_Guid as \"MedStaffFact_Guid\",
			    MSF.MedStaffFact_insDT as \"MedStaffFact_insDT\",
			    MSF.MedStaffFact_updDT as \"MedStaffFact_updDT\",
			    MSF.PmUser_insID as \"PmUser_insID\",
			    MSF.PmUser_updID as \"PmUser_updID\",
			    MSF.PostOccupationType_id as \"PostOccupationType_id\",
			    MSF.Population as \"Population\",
			    MSF.Population as \"Population\",
			    MSF.ArriveOrderNumber as \"ArriveOrderNumber\",
			    MSF.Comments as \"Comments\",
			    MSF.LeaveRecordType_id as \"LeaveRecordType_id\",
			    MSF.LpuBuilding_id as \"LpuBuilding_id\",
			    MSF.MedStaffFactCache_IsNotShown as \"MedStaffFactCache_IsNotShown\",
			    MSF.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
			    MSF.MedStaffFactCache_IsNotReception as \"MedStaffFactCache_IsNotReception\",
			    MSF.PostKind_id as \"PostKind_id\",
			    MSF.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			    MSF.Person_Snils as \"Person_Snils\",
			    MSF.MedStaffFactOuter_id as \"MedStaffFactOuter_id\"
			from
			    v_MedStaffFact MSF
			where
			    MSF.MedStaffFact_id = :MedStaffFact_id
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}
		if (isset($resp[0])) {
			unset($resp[0]['MedStafffactCache_Rowversion']);
		}
		return $resp;
	}

	/**
	 * Получение идентификатора рабочего места
	 */
    function getMedStaffFactByParams($data) {
        $filter = ['1=1'];

        if (!empty($data['Post_id'])) {
            $filter[] = 'Post_id = :Post_id';
        }
        if (!empty($data['MedPersonal_id'])) {
            $filter[] = 'MedPersonal_id = :MedPersonal_id';
        }
        if (!empty($data['LpuSection_id'])) {
            $filter[] = 'LpuSection_id = :LpuSection_id';
        }

        $filter[] = "(WorkData_endDate is null
			or (cast(WorkData_endDate as date) >= cast(dbo.tzgetdate() as date))
		)";

        $filter = implode("\nand ", $filter);

        $query = "
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_MedStaffFact msf
			where
				{$filter}
			limit 1
		";

        return $this->queryResult($query, $data);
    }

	/**
	 * Определение открытого рабочего места врача
	 */
	function getMSFData($data) {
		$filter_msf = "";

		if (!empty($data['LpuSection_id'])) {
			// если задано отделение, фильтруем по отделению
			$filter_msf .= "and LpuSection_id = :LpuSection_id";
		}
		$query = "
			select
				MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				LpuSection_id as \"LpuSection_id\"
			from
				v_MedStaffFact
			where
				MedPersonal_id = :MedPersonal_id
				and dbo.tzGetDate() between WorkData_begDate and WorkData_endDate
				{$filter_msf}
			limit 1
		";

		return $this->queryResult($query, $data);
	}

	function getMedPersonal($data) {
		return $this->getFirstResultFromQuery("
			select
				MedPersonal_id as \"MedPersonal_id\"
			from v_MedStaffFact
			where MedStaffFact_id = :MedStaffFact_id
		", $data);
	}

	/**
	 * Функция менеджер по формированию фильтров
	 */
	function makeFilter($filterName, $data = array(), $options = array()) {

		$makeFilter = 'makeFilter_' . $filterName;

		if (method_exists($this, $makeFilter)) {

			return $this->$makeFilter($data, $options);

		} else {
			return array('Error_Msg' => 'Фильтр '.$data['FilterName'].' не определен для этой модели.');
		}
	}

	/**
	 * Фильтр для методов получения докторов (каунта и мин. стоимости)
	 */
	function makeFilter_getDoctors($data, $options = array()) {

		$filter = ""; $params = array();

		if (!empty($data['LpuSectionProfile_id'])) {
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
			$filter .= " and msf.LpuSectionProfile_id = :LpuSectionProfile_id ";
		}

		if (!empty($data['MedPersonal_FullName'])) {

			$fullName = explode(' ',trim($data['MedPersonal_FullName']));

			if (!empty($fullName[0])) {
				$filter .= " and ps.Person_SurName iLIKE :Person_SurName || '%'";
				$params['Person_Surname'] = $fullName[0];
			}

			if (!empty($fullName[1])) {
				$filter .= " and ps.Person_FirName iLIKE :Person_Firname || '%'";
				$params['Person_Firname'] = $fullName[1];
			}

			if (!empty($fullName[2])) {
				$filter .= " and ps.Person_SecName iLIKE :Person_Secname || '%'";
				$params['Person_Secname'] = $fullName[2];
			}
		}

		if (!empty($data['isChildDoctor'])) {
			$filter .= " and (ls.LpuSectionAge_id in (2,3) or ls.LpuSectionAge_id is null) ";
		}

		if (!empty($data['Sex_id'])) {
			$params['Sex_id'] = $data['Sex_id'];
			$filter .= " and ps.Sex_id = :Sex_id ";
		}

		$timetable_filter = "";

		// если выбраны все посещения, то показываем так же тех у кого нет бирок
		if ($data['acceptDate'] === 'any') {
			//$timetable_filter = "  and cast(tt.TimetableGraf_begTime as date) >= cast(dbo.tzGetDate() as date) ";
		} else {

			// иначе включаем фильтр по биркам
			$filter .= " and tt.TimeTableGraf_begTime is not null ";

			if ($data['acceptDate'] === 'today') {
				$timetable_filter .= "
					and cast(tt.TimetableGraf_begTime as date) = cast(d.curDT as date)
					and tt.TimeTableGraf_begTime > (d.curDT + interval '15 minutes')
				";
			} else if ($data['acceptDate'] === 'tomorrow') {
				$timetable_filter .= "
					and cast(tt.TimetableGraf_begTime as date) = (cast(d.curDT + interval '1 day' as date))
				";
			} else {
				$timetable_filter .= " and cast(tt.TimetableGraf_begTime as date) = :acceptDate ";
				$params['acceptDate'] = $data['acceptDate'];
			}
		}

		// если опция по типу приема включена, фильтруем по нему
		if (in_array('acceptType', $options)) {

			// если мы получаем инфу только по платным
			if (!empty($data['isPaid'])) {

				if (!empty($data['minCost'])) {
					$params['minCost'] = $data['minCost'];
					$filter .= " and msf.MedStaffFactCache_CostRec >= :minCost ";
				}

				if (!empty($data['maxCost'])) {
					$params['maxCost'] = $data['maxCost'];
					$filter .= " and msf.MedStaffFactCache_CostRec <= :maxCost ";
				}

				// фильтр платных
				$filter .= "
					and coalesce(msf.MedStaffFactCache_IsPaidRec, 1) = 2
					and msf.MedStaffFactCache_CostRec is not null
				";

			} else {

				// фильтр бесплатных
				$filter .= "
					and coalesce(msf.MedStaffFactCache_IsPaidRec, 1) = 1
					and msf.MedStaffFactCache_CostRec is null
				";
			}
		}

		return array(
			'filter' => $filter,
			'timetable_filter' => $timetable_filter,
			'params' => $params
		);
	}

	/**
	 * получение минимальной и максимальной стоимости лечения по фильтру
	 */
	function getCostLimits($data) {

		$filters = $this->makeFilter('getDoctors', $data);

		$filter = $filters['filter'];
		$params = $filters['params'];
		$timetable_filter = $filters['timetable_filter'];

		$sql = "
			select
				min(msf.MedStaffFactCache_CostRec) as \"minCost\",
				max(msf.MedStaffFactCache_CostRec) as \"maxCost\"
			from v_MedStaffFact msf
			left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
			left join v_PersonState ps on msf.Person_id = ps.Person_id
			left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			LEFT JOIN LATERAL (
				select
					tt.TimeTableGraf_begTime
				from v_TimeTableGraf_lite tt
				where tt.MedStaffFact_id = msf.MedStaffFact_id
				{$timetable_filter}
				limit 1
			) tt on true
			where  (1=1)
				and msf.MedStaffFactCache_CostRec is not null
				and coalesce(msf.MedStaffFactCache_IsPaidRec, 1) = 2
				and (coalesce(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
				and coalesce(lsp.LpuSectionProfile_InetDontShow, 1) = 1
				and coalesce(msf.MedStaffFactCache_IsNotShown, 0) != 2
				and coalesce(msf.RecType_id, 6) not in (2,3,5,6,8)
				and lu.LpuUnit_IsEnabled = 2
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and lsp.ProfileSpec_Name is not null
				and coalesce(lpu.Lpu_IsTest, 1) = 1
				and coalesce(msf.MedStaffFactCache_IsPaidRec, 1) = 2
				and msf.MedStaffFactCache_CostRec is not null
			{$filter}
		";

		$result = $this->getFirstRowFromQuery($sql, $params);

		if (empty($result)) {
			$result = array(
				'minCost' => "0",
				'maxCost' => "0",
			);
		}

		return $result;
	}

	/**
	 * Метод получения докторов для платных услуг для портала кврачу
	 */
	function getDoctorsTotalCount($data) {

		// добавить в фильтр проверку по типу приема
		$options = array('acceptType');
		// сформируем общие фильтр и параметры
		$filters = $this->makeFilter('getDoctors', $data, $options);

		$filter = $filters['filter'];
		$params = $filters['params'];
		$timetable_filter = $filters['timetable_filter'];

		$sql = "
            WITH declared (curDT) as (
               values (now())
            )
			select
				count(msf.MedStaffFact_id) as \"totalCount\"
			from v_MedStaffFact msf
			left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			cross join declared d
			left join v_PersonState ps on msf.Person_id = ps.Person_id
			left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
			LEFT JOIN LATERAL (
				select
					tt.TimeTableGraf_begTime
				from v_TimeTableGraf_lite tt
				where tt.MedStaffFact_id = msf.MedStaffFact_id
				{$timetable_filter}
				limit 1
			) tt on true
			where (1=1)
				and (coalesce(msf.WorkData_endDate, '2030-01-01') > d.curDT)
				and coalesce(lsp.LpuSectionProfile_InetDontShow, 1) = 1
				and coalesce(msf.MedStaffFactCache_IsNotShown, 0) != 2
				and coalesce(msf.RecType_id, 6) not in (2,3,5,6,8)
				and lu.LpuUnit_IsEnabled = 2
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and lsp.ProfileSpec_Name is not null
				and coalesce(lpu.Lpu_IsTest, 1) = 1
			{$filter}
		";

		$result = $this->getFirstResultFromQuery($sql, $params);
		return array('totalCount' => $result);
	}

	/**
	 * Метод получения докторов для платных услуг для портала кврачу
	 */
	function getDoctors($data) {

		// добавить в фильтр проверку по типу приема
		$options = array('acceptType');
		// сформируем общие фильтр и параметры
		$filters = $this->makeFilter('getDoctors', $data, $options);

		$filter = $filters['filter'];
		$params = $filters['params'];
		$timetable_filter = $filters['timetable_filter'];

		$params['offset'] = !empty($data['start']) ? $data['start'] : 0;
		$params['limit'] =  !empty($data['limit']) ? $data['limit'] : 10;

		$offset = ''; $limit = '';

		if (!empty($data['sort'])) {

			$sorters = explode(',',$data['sort']);
			$sortersOrder = array();

			if (!empty($sorters)) {
				foreach ($sorters as $sorter) {
					if ($sorter === "timetable") {
						$sortersOrder[] = " case when ffd.TimeTableGraf_begTime is null then 1 else 0 end, ffd.TimeTableGraf_begTime ";
					}

					if ($sorter === "cost") {
						$sortersOrder[] = " msf.MedStaffFactCache_CostRec desc ";
					}

					if ($sorter === "remote") {
						//$sortersOrder[] = " ffd.TimeTableGraf_begTime desc ";
					}
				}
			}
		}

		if (!empty($sortersOrder)) {
			$order = "
			order by
		" . implode(',', $sortersOrder);
		} else {
			$order = " order by ffd.TimeTableGraf_begTime desc ";
		}

		if (isset($params['offset'])) {
			$offset = "offset {$params['offset']}";
		}

		if (isset($params['limit'])) {
			$limit = "limit {$params['limit']}";
		}

		$sql = "
            WITH declared (curDT) as (
                values (now())
            )
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Person_id as \"msf_Person_id\",
				case when msf.MedStaffFactCache_IsPaidRec is not null then 2 else 1 end as \"IsPaidRec\",
				msf.MedStaffFactCache_CostRec as \"CostRec\",
				rtrim(msf.Person_Surname) || ' ' || rtrim(msf.Person_Firname) || ' ' || rtrim(msf.Person_Secname) as \"MedPersonal_FullName\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				rtrim(str.KLStreet_Name) || ', ' || rtrim(a.Address_House) as \"LpuUnit_Address\",
				case when c.name = 'Без категории' THEN null ELSE c.name end  as \"QualificationCat_Name\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				lpu.Lpu_id as \"Lpu_id\",
				--fb.cnt as \"feeback_total\",
				0 as \"feeback_total\",
				--coalesce(fb.rating_sum, 0) as \"feedback_rating\",
				0 as \"feedback_rating\",
				ffd.TimeTableGraf_begTime as \"firstFreeDate\",
				ps.Sex_id as \"Sex_id\",
				klhc.KLHouseCoords_LatLng as \"KLHouseCoords_LatLng\",
				lsp.ProfileSpec_Name as \"ProfileSpec_Name\"
			from v_MedStaffFact msf
			left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			CROSS JOIN declared d
			left join v_PersonState ps on msf.Person_id = ps.Person_id
			left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
			LEFT JOIN LATERAL (
				select
					tt.TimeTableGraf_begTime
				from v_TimeTableGraf_lite tt
				where tt.MedStaffFact_id = msf.MedStaffFact_id
				{$timetable_filter}
				limit 1
			) tt on true
			left join v_Address a on a.Address_id = lu.Address_id
			left join v_KLStreet str on str.KLStreet_id = a.KLStreet_id
			left join v_KLHouseCoords klhc on a.KLStreet_id = klhc.KLStreet_id and a.Address_House = klhc.KLHouseCoords_Name
			LEFT JOIN LATERAL (
				select
					tt.TimetableGraf_begTime
				from v_TimetableGraf_lite tt
				LEFT JOIN LATERAL (
						select
							TimetableTypeAttributeLink_id
						FROM v_TimetableTypeAttributeLink
						where
							TimetableType_id = tt.TimetableType_id
							and TimetableTypeAttribute_id in (8,9)
						limit 1
				) ttal on true
				where (1=1)
					and tt.Person_id is null
					and tt.TimetableType_id in (1,11)
					and tt.TimetableGraf_IsDop is null
					and tt.TimetableGraf_begTime is not null
					and ttal.TimetableTypeAttributeLink_id is not null
					and cast(tt.TimeTableGraf_begTime as date) >= cast(d.curDT as date)
					and tt.TimeTableGraf_begTime > (d.curDT + interval '15 minutes')
					and tt.MedStaffFact_id = msf.MedStaffFact_id
				order by TimetableGraf_begTime asc
				limit 1
			) ffd on true
			LEFT JOIN LATERAL (
				select qc.* from
				persis.QualificationCategory qc
				where msf.MedPersonal_id = qc.MedWorker_id
				order by qc.id desc
				limit 1
			) qc on true
			--outer apply(
			--	select
			--		COUNT(*) as cnt,
			--		SUM(fb.rating) as rating_sum
			--	FROM UserPortal.dbo.doctor_feedbacks fb
			--	where fb.MedStaffFact_id = msf.MedStaffFact_id
			--) fb
			left join persis.Category c on qc.Category_id = c.id
			where (1=1)
				and (coalesce(msf.WorkData_endDate, '2030-01-01') > d.curDT)
				and coalesce(lsp.LpuSectionProfile_InetDontShow, 1) = 1
				and coalesce(msf.MedStaffFactCache_IsNotShown, 0) != 2
				and coalesce(msf.RecType_id, 6) not in (2,3,5,6,8)
				and lu.LpuUnit_IsEnabled = 2
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and lsp.ProfileSpec_Name is not null
				and coalesce(lpu.Lpu_IsTest, 1) = 1
			{$filter}
			{$order}
			{$limit}
			{$offset}
		";

		$result = $this->queryResult($sql, $params);
		if (!empty($result)) {

			$MedStaffFact_list = array();

			foreach ($result as $key => &$record) {

				if (!empty($record['firstFreeDate'])) {
					$record['firstFreeDate'] = DateTime::createFromFormat('Y-m-d H:i:s', $record['firstFreeDate']);
					$record['firstFreeDate'] = $record['firstFreeDate']->format('d.m.Y H:i');
				}

				if (!empty($record['MedStaffFact_id'])) {
					$MedStaffFact_list[] = $record['MedStaffFact_id'];
				} else {
					unset($result[$key]);
					continue;
				}

				$record['avatar_path'] = $this->getDoctorPhoto(
					array(
						'Person_id' => $record['msf_Person_id'],
						'Sex_id' => $record['Sex_id']
					)
				);

				unset($record['msf_Person_id']);
				unset($record['Sex_id']);

				if (!empty($record['KLHouseCoords_LatLng'])) {
					list($record['lat'], $record['lng']) = explode(' ', $record['KLHouseCoords_LatLng']);
					unset($record['KLHouseCoords_LatLng']);
				}

				if (!empty($record['IsPaidRec']) && $record['IsPaidRec'] == 2 && empty($record['firstFreeDate'])) {
					$record['canMakeRequest'] = 1;
				}
			}

			if (!empty($MedStaffFact_list)) {

				$firstFreeDateList = $this->getDoctorFirstFreeDates(array(
					'MedStaffFact_list' => $MedStaffFact_list,
					// сколько ближайших свободных бирок возвращать
					'recordsCount' => 2,
					'groupResult' => true,
					'allowTodayRecord' => !empty($data['allowTodayRecord'])
				));

				// смержим данные
				if (!empty($firstFreeDateList)) {
					foreach ($result as &$item) {
						if (!empty($item['MedStaffFact_id']) && isset($firstFreeDateList[$item['MedStaffFact_id']])) {
							$item['firstFreeDates'] = $firstFreeDateList[$item['MedStaffFact_id']];
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Получить фотография из промеда, путь
	 */
	function getDoctorPhoto($data) {

		$PromedURL = $this->config->item('PromedURL');
		$upload_path = $PromedURL.'/'.IMPORTPATH_ROOT;

		if (empty($data['Sex_id'])) {
			$data['Sex_id'] = 1;
		}

		$path = 'default_'.(($data['Sex_id'] == 1) ? 'male' : 'female');
		if (empty($data['Person_id'])) return $path;

		if (!empty($upload_path)) {
			$url_file_path = $upload_path.'persons/' . $data['Person_id'] . '/' . $data['Person_id'] . '.png';
			if ($this->url_file_exists($url_file_path)) {
				$path = $url_file_path;
			}
		}

		return $path;
	}

	/**
	 * проверка что URL вернул 200 ответ
	 */
	function url_file_exists($url){
		$headers = get_headers($url);
		return stripos($headers[0],"200 OK") ? true : false;
	}

	/**
	 * Получить статистику по ближайшим N-свободным биркам для списка врачей (или врача)
	 */
	public function getDoctorFirstFreeDates($data) {

		$filter = ""; $params = array();

		if (empty($data['MedStaffFact_list']) || (!empty($data['MedStaffFact_list']) && !is_array($data['MedStaffFact_list']) )) return array();
		$id_list = implode(',', array_unique($data['MedStaffFact_list']));

		// количество возвращаемых свободных бирок
		$N = !empty($data['recordsCount']) ? $data['recordsCount'] : 1;

		// типы по умолчанию
		// 1 - обычная
		// 11 - для интернета
		$types = array(1,11);

		// атрибуты по умолчанию
		// 8 - интернет пользователи
		// 9 - пользователи инфомата
		$attributes = array(8,9);

		// признак записи на сегодня
		// сначала определяется по признаку на ЭО дальше в запросе
		if (!empty($data['allowTodayRecord'])) {
			$begtime_filter = "
				cast(tt.TimeTableGraf_begTime as date) >= cast(dbo.tzGetDate() as date)
				and tt.TimeTableGraf_begTime > (dbo.tzGetDate() + interval '15 minutes')
			";
		} else {
			$begtime_filter = "
				cast(tt.TimeTableGraf_begTime as date) > cast(dbo.tzGetDate() as date)
			";
		}

		// запись на резервные разрешена?
		if (empty($data['enableReservedRecords'])) {
			$filter .= "
				and ttal.TimetableTypeAttributeLink_id is not null
			";
		} else {
			$types[] = 2;
		}

		// запись на платные разрешена?
		if (!empty($data['allow_pay'])) {
			$types[] = 3;
		}

		$filter .= " and tt.TimeTableType_id in (".implode(',', $types).")";
		$TTALfilter = " and TimetableTypeAttribute_id in (".implode(',', $attributes).")";
		$main_filter = "";
		$join = "";

		$this->load->model('Options_model');
		$queueOptions = $this->Options_model->getQueueOptions();

		// если включены листы ожидания то бирки врача или профиля
		// нужно исключить из первых свободных
		if (!empty($queueOptions['grant_individual_add_to_wait_list'])) {

			//$main_filter .= " and qu.inQueueCount = 0 ";

			$join .= "
        		LEFT JOIN LATERAL (
					select
						qMSF.inQueueCount as qMSF,
						qLSP.inQueueCount as qLSP,
						opt.allow_queue,
						case
							when (opt.allow_queue = '1' and (qMSF.inQueueCount > 0 or qLSP.inQueueCount > 0))
							then 1
							else 0
						end as inQueueCount
					FROM (select 0 as nothing) as tt
					LEFT JOIN LATERAL (
						select DataStorage_Value as allow_queue
						from dbo.DataStorage
						where (1=1)
							and DataStorage_Name = 'allow_queue'
							and Lpu_id = msf.Lpu_id
					) opt on true
					LEFT JOIN LATERAL (
						select
						count(qu.EvnQueue_id) as inQueueCount
						from v_EvnQueue qu
						inner join v_EvnDirection_all ed on ed.EvnDirection_id = qu.EvnDirection_id
						where
							ed.MedStaffFact_id = msf.MedStaffFact_id
							and qu.EvnQueueStatus_id = 1
							and qu.QueueFailCause_id is null
							and qu.RecMethodType_id = 1
					) qMSF on true
					LEFT JOIN LATERAL (
						select
						count(qu.EvnQueue_id) as inQueueCount
						from v_EvnQueue qu
						inner join v_EvnDirection_all ed on ed.EvnDirection_id = qu.EvnDirection_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = msf.MedStaffFact_id
						where
							qu.LpuSectionProfile_did = msf.LpuSectionProfile_id
							and ed.MedStaffFact_id is null
							and qu.EvnQueueStatus_id = 1
							and qu.QueueFailCause_id is null
							and qu.RecMethodType_id = 1
							and qu.Lpu_id = msf.Lpu_id
					) qLSP on true
				) qu on true
        	";
		}

		$result = $this->queryResult("
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				tt.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				tt.TimeTableGraf_id as \"TimeTableGraf_id\",
				tt.DateDiff as \"DateDiff\"
			from v_MedStaffFact msf
			left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
			left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id
			-- определяем признак на ЭО если она есть, запись на сегодняшний день
			LEFT JOIN LATERAL (
				select
					eq.ElectronicQueueInfo_id,
					eq.ElectronicQueueInfo_IsCurDay
				from v_MedServiceElectronicQueue mseq
				left join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
				left join v_ElectronicQueueInfo eq on eq.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
				where
					mseq.MedStaffFact_id = msf.MedStaffFact_id
					and coalesce(eq.ElectronicQueueInfo_IsOff, 1) != 2
				order by mseq.MedServiceElectronicQueue_id desc
				limit 1
			) eq on true
			INNER JOIN LATERAL (
				select
					tt.TimetableGraf_begTime,
				 	(DATE_PART('day', dbo.tzGetdate()::timestamp - tt.TimetableGraf_begTime::timestamp)) as DateDiff,
					tt.TimeTableGraf_id
				from v_TimetableGraf_lite tt
				LEFT JOIN LATERAL (
					select
						TimetableTypeAttributeLink_id
					FROM v_TimetableTypeAttributeLink
					where
						TimetableType_id = tt.TimetableType_id
						{$TTALfilter}
					limit 1
				) ttal on true
				where (1=1)
					-- определяем по признаку ЭО какой фильтр будем использовать
				 	and (1 = CASE
						  WHEN eq.ElectronicQueueInfo_IsCurDay = 2
						  	THEN case when cast(tt.TimeTableGraf_begTime as date) >= cast(dbo.tzGetDate() as date) and tt.TimeTableGraf_begTime > (dbo.tzGetDate() + interval '15 minutes')  then 1
							else 0 end
						  WHEN eq.ElectronicQueueInfo_IsCurDay is null or eq.ElectronicQueueInfo_IsCurDay = 1 THEN
							case when {$begtime_filter} then 1
							else 0 end
						  ELSE 0
				   	END)
					and tt.MedStaffFact_id = msf.MedStaffFact_id
					and tt.Person_id is null
					and tt.TimetableGraf_IsDop is null
					and tt.TimetableGraf_begTime is not null
					and ttal.TimetableTypeAttributeLink_id is not null
					{$filter}
				order by tt.TimetableGraf_begTime asc
				limit {$N}
			) tt on true
			{$join}
			where (1=1)
			 	and msf.MedStaffFact_id in ({$id_list})
			 	and (coalesce(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
				and coalesce(lsp.LpuSectionProfile_InetDontShow, 1) = 1
				and coalesce(msf.MedStaffFactCache_IsNotShown, 0) != 2
				and coalesce(msf.RecType_id, 6) not in (2,3,5,6,8)
				and lu.LpuUnit_IsEnabled = 2
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and lsp.ProfileSpec_Name is not null
				and coalesce(lpu.Lpu_IsTest, 1) = 1
				{$main_filter}

		", $params);

		$groupedResult = array();

		if (!empty($result) && !empty($data['groupResult'])) {

			$this->load->library('calendar');

			foreach ($result as $item) {
				if (!isset($groupedResult[$item['MedStaffFact_id']])) {
					$groupedResult[$item['MedStaffFact_id']] = array();
				}

				$dt = DateTime::createFromFormat('Y-m-d H:i:s', $item['TimetableGraf_begTime']);

				if ($item['DateDiff'] == 0) {
					$day_title = 'Сегодня';
				} else if ($item['DateDiff'] == 1) {
					$day_title = 'Завтра';
				} else {
					$day_title = $dt->format('d').' '. $this->calendar->get_month_name($dt->format('m'));
				}

				$groupedResult[$item['MedStaffFact_id']][] = array(
					'TimeTableGraf_id' => $item['TimeTableGraf_id'],
					'TimetableGraf_begTime' =>$day_title.' '. $dt->format('H:i')
				);
			}

			$result = $groupedResult;
		}

		return $result;
	}
	public function getDoctorInfo($doctor_id, $regions_data = false) {
		$result = $this->getFirstRowFromQuery("
		select
			msf.Lpu_id,
			lsp.LpuSectionProfile_Id,
			coalesce(rtrim(msf.Person_SurName),'')||coalesce(' '||rtrim(msf.Person_FirName),'')||coalesce(' '||rtrim(msf.Person_SecName),'') as \"FullName\",
			rtrim(coalesce(nullif(mso.MedSpecOms_PortalName, ''), lsp.ProfileSpec_Name, lsp.LpuSectionProfile_Name, 'Не указана')) as \"ProfileSpec_Name\",
			rtrim(l.Lpu_Name) as \"Lpu_Name\",
			rtrim(l.Lpu_Nick) as \"Lpu_Nick\",
			lu.LpuUnit_id as \"LpuUnit_id\",
			rtrim(LpuUnit_Name) as \"LpuUnit_Name\",
			rtrim(msf.Person_Fin) as Person_ShortFullName,
			(left(msf.Person_firname, 1)||'.'||left(msf.Person_secname, 1)||'.') as \"Person_FullNameInitials\",
			rtrim(msf.Person_surname) as \"Person_surname\",
			(rtrim(msf.Person_surname)||' '|| left(msf.Person_firname, 1)||'.'||left(msf.Person_secname, 1)||'.') as Person_ShortFullNameDots,
			msf.RecType_id,
			msf.MedStaffFact_id as \"MedStaffFact_id\",
			c.name as \"QualificationCat_Name\",
			coalesce(luat.KLArea_Name || ', ', '') || luas.KLStreet_Name || ', ' || lua.Address_House as \"LpuUnit_Address\",
			msf.RecType_id as \"RecType_id\",
			msf.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			ls.LpuSection_id,
			ls.LpuSectionAge_id,
			msf.Person_id,
			ps.Sex_id,
			ps.Sex_id as PersonSex_id,
			msf.WorkData_endDate,
			msf.MedPersonal_id,
			mso.MedSpecOms_id,
			lb.LpuBuilding_Name,
			lbc.KLCity_Name,
			es.ElectronicService_id,
			es.ElectronicService_Code,
			coalesce(lbs.KLStreet_Name || ', ', '') || coalesce(lba.Address_House, '') || coalesce(', корп. ' || lba.Address_Corpus, '') as \"LpuBuilding_Address\",
			lb.LpuBuilding_id,
			lsp.LpuSectionProfile_mainid,
			ffd.TimeTableGraf_begTime as \"firstFreeDate\",
			case when (
				select
					count(AV.AttributeValue_id)
				from
					v_AttributeValue AV
					inner join v_Attribute A  on A.Attribute_id = AV.Attribute_id and A.Attribute_SysNick in ('portalzno', 'EarlyDetect')
					left join v_AttributeVision AVI  on AVI.Attribute_id = A.Attribute_id
					left join v_AttributeSignValue ASV  on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
				where
					AV.AttributeValue_TableName = 'dbo.LpuSection'
					and AV.AttributeValue_ValueBoolean::integer = 1
					and AV.AttributeValue_TablePKey = ls.LpuSection_id
					and coalesce(AVI.AttributeVision_begDate, '2000-01-01') <= dbo.tzGetDate()
					and coalesce(AVI.AttributeVision_endDate, '2030-01-01') >= dbo.tzGetDate()
					and coalesce(A.Attribute_begDate, '2000-01-01') <= dbo.tzGetDate()
					and coalesce(A.Attribute_endDate, '2030-01-01') >= dbo.tzGetDate()
					and coalesce(ASV.AttributeSignValue_begDate, '2000-01-01') <= dbo.tzGetDate()
					and coalesce(ASV.AttributeSignValue_endDate, '2030-01-01') >= dbo.tzGetDate()
			) = 2 then 1 else 0 end as cabinetDetectionZNO
		from
			v_MedStaffFact msf
			left join v_MedSpecOms mso  on msf.MedSpecOms_id = mso.MedSpecOms_id
			left join MedStaffRegion msr   on msr.MedStaffFact_id = msf.MedStaffFact_id and coalesce(msr.MedStaffRegion_endDate, '2030-01-01') >= dbo.tzGetDate()
			left join LpuRegion lr  on msr.LpuRegion_Id = lr.LpuRegion_Id
			left join v_LpuSection ls  on msf.LpuSection_Id = ls.LpuSection_Id
			left join v_LpuBuilding lb  on lb.LpuBuilding_id = ls.LpuBuilding_id
			left join v_Address lba  on lba.Address_id = lb.Address_id
			left join v_KLStreet lbs  on lbs.KLStreet_id = lba.KLStreet_id
			left join v_KLCity lbc  on lbc.KLCity_id = lba.KLCity_id
			left join v_LpuUnit lu  on msf.LpuUnit_id = lu.LpuUnit_id
			left join Address lua  on lu.Address_id = lua.Address_id
			left join KLStreet luas  on lua.KLStreet_id = luas.KLStreet_id
			left join KLArea luat  on lua.KLTown_id = luat.KLArea_id
			left join v_Lpu l  on lu.Lpu_id = l.Lpu_id
			left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_Id = lsp.LpuSectionProfile_Id
			left join persis.QualificationCategory qc  on msf.MedPersonal_id = qc.MedWorker_id
			left join persis.Category c  on qc.Category_id = c.id
			left join v_PersonState ps  on msf.Person_id = ps.Person_id
			left join v_MedServiceElectronicQueue mseq  on mseq.MedStaffFact_id = msf.MedStaffFact_id
			left join v_ElectronicService es  on es.ElectronicService_id = mseq.ElectronicService_id
			LEFT JOIN LATERAL (
				select
					tt.TimetableGraf_begTime
				from v_TimetableGraf_lite tt
				LEFT JOIN LATERAL (
						select
							TimetableTypeAttributeLink_id
						FROM v_TimetableTypeAttributeLink
						where
							TimetableType_id = tt.TimetableType_id
							and TimetableTypeAttribute_id in (8,9)
						limit 1
				) ttal on true
				where (1=1)
					and tt.Person_id is null
					and tt.TimetableType_id in (1,11)
					and tt.TimetableGraf_IsDop is null
					and tt.TimetableGraf_begTime is not null
					and ttal.TimetableTypeAttributeLink_id is not null
					and cast(tt.TimeTableGraf_begTime as date) >= cast(dbo.tzGetDate() as date)
					and tt.TimeTableGraf_begTime > (dbo.tzGetDate() + interval '15 minutes')
					and tt.MedStaffFact_id = msf.MedStaffFact_id
				order by TimetableGraf_begTime asc
				limit 1
			) ffd on true
		where
			msf.MedStaffFact_id = :doctor_id
			and coalesce(l.Lpu_IsTest, 1) = 1

		order by qc.Category_id desc
		limit 1",array('doctor_id' => $doctor_id)
		);

		if (!empty($result)) {
			// Начальная обработка результатов
			$result['FullName'] = ucwords($result['FullName']);
			$result['Person_ShortFullNameDots'] = ucwords($result['Person_surname']).' '.$result['Person_FullNameInitials'];
			$result['ProfileSpec_Name'] = ucfirst(($result['ProfileSpec_Name']));
			$result['QualificationCat_Name'] = ucfirst($result['QualificationCat_Name']);
			$result['annot'] = $this->getMsfCommonDescription($doctor_id);

			if ( $regions_data ) {
				$regions_model = $this->load->model('LpuRegion_model');
				$result['regions'] = $regions_model->getLpuRegionByMedStaffFact($result['MedStaffFact_id']);
			}
		}
		return $result;
	}
	public function getDoctorInfoDop($data) {
		// Получаем все места работы, открытые и закрытые, чтобы получить стаж
		// Но если нет ни одного открытого места работы или медперсонал не врач - не выводим информацию
		$params = array('doctor_id'=>$data['MedStaffFact_id']);
		$result = $this->queryResult("
			Select
				coalesce(rtrim(msf.Person_SurName),'')||coalesce(' '||rtrim(msf.Person_FirName),'')||coalesce(' '||rtrim(msf.Person_SecName),'') as \"FullName\",
				qual.name as QualificationCat_Name,
				qual.qcat as QualificationCategory,
				msf.Person_id,
				msf.WorkData_begDate as \"WorkData_begDate\",
				msf.WorkData_endDate as \"WorkData_endDate\",
				msf.PostOccupationType_id as \"WorkType_id\",
				post.name as \"Dolgnost_Name\",
				l.Lpu_id as \"Lpu_id\",
				l.Lpu_Name as \"Lpu_Name\",
				mso.MedSpecOms_Name as \"MedSpecOms_Name\",
				mso.MedSpecOms_Code,
				ls.LpuSection_Name as \"LpuSection_Name\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				lu.Address_id as \"Address_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				dbo.Age(Person_BirthDay, getdate()) as \"Age\",
				msf.MedStaffFactCache_CostRec as \"MedStaffFactCache_CostRec\",
				ffd.TimeTableGraf_begTime as \"firstFreeDate\"
			from v_MedStaffFact msf
				left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_Lpu l  on msf.Lpu_id = l.Lpu_id
				left join v_MedSpecOms mso  on msf.MedSpecOms_id = mso.MedSpecOms_id
				left join v_LpuSection ls  on ls.LpuSection_id = msf.LpuSection_id
				left join persis.Post as post on msf.Post_id = Post.id
				LEFT JOIN LATERAL (
					select
						spec.name,
						cat.name as qcat
					from persis.QualificationCategory qc
						inner join persis.category cat  on qc.Category_id = cat.id
						inner join persis.Speciality spec on qc.Speciality_id = spec.id
						-- убрал:
						-- and spec.code = mso.MedSpecOms_Code
					where
						msf.MedPersonal_id = qc.MedWorker_id
						and qc.AssigmentDate = (
												SELECT
													MAX(qcd.AssigmentDate)
												FROM persis.QualificationCategory qcd (nolock)
												WHERE
													msf.MedPersonal_id = qcd.MedWorker_id
						)
					limit 1
				) as qual on true
				LEFT JOIN LATERAL (
				select
					tt.TimetableGraf_begTime
				from v_TimetableGraf_lite tt
				LEFT JOIN LATERAL (
						select
							TimetableTypeAttributeLink_id
						FROM v_TimetableTypeAttributeLink
						where
							TimetableType_id = tt.TimetableType_id
							and TimetableTypeAttribute_id in (8,9)
						limit 1
				) ttal on true
				where (1=1)
					and tt.Person_id is null
					and tt.TimetableType_id in (1,11)
					and tt.TimetableGraf_IsDop is null
					and tt.TimetableGraf_begTime is not null
					and ttal.TimetableTypeAttributeLink_id is not null
					and cast(tt.TimeTableGraf_begTime as date) >= cast(dbo.tzGetDate() as date)
					and tt.TimeTableGraf_begTime > (dbo.tzGetDate() + interval '15 minutes')
					and tt.MedStaffFact_id = msf.MedStaffFact_id
				order by TimetableGraf_begTime asc
				limit 1
			) ffd on true
			where 
				MedPersonal_id = (
					select
						msf.MedPersonal_id
					from v_MedStaffFact msf
						left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id
						left join v_Lpu l on msf.Lpu_id = l.Lpu_id
					where (1=1)
					and MedStaffFact_id = :doctor_id
					and PostKind_id in (1,6,10)
						and lu.LpuUnitType_id in (2, 5, 10, 12)
						and l.Lpu_id is not null
						and COALESCE(l.Lpu_endDate, '2030-01-01') >= getdate()
						and msf.Person_surname is not null
						and COALESCE(l.Lpu_IsTest, 1) = 1
					limit 1
				)
				and MedStaffFact_Stavka > 0
				and msf.PostKind_id in (1,6,10)
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and l.Lpu_id is not null
				and COALESCE(l.Lpu_endDate, '2030-01-01') >= getdate()
				and msf.Person_surname is not null
				and COALESCE(l.Lpu_IsTest, 1) = 1
		",$params);

		//return $result;

		$start_date = new DateTime('2030-01-01');
		$end_date = new DateTime('1970-01-01');
        $start_date = $start_date->format('Y-m-d H:i:s');
        $end_date = $end_date->format('Y-m-d H:i:s');

		$doctor = array();
		$doctor['current_addons'] = array();
		$this->load->model( 'Address_model');
		foreach($result as $mp) {
			$mp['LpuUnit_Address'] = $this->Address_model->getAddressTextBrief($mp['Address_id']);
			if ($mp['WorkData_begDate'] < $start_date) {
				$start_date = $mp['WorkData_begDate'];
			}
			if ($mp['WorkData_endDate'] > $start_date) {
				$end_date = $mp['WorkData_endDate'];
			}

			if (!isset($mp['WorkData_endDate']) && $mp['WorkType_id'] == 1 ) {
				$doctor['current_main'] = $mp; // текущее основное место работы
			}
			// todo: можно конечно где-то дополнительно показывать совмещение или совместительство, но кому это интересно?
			if (!isset($mp['WorkData_endDate']) && $mp['WorkType_id'] > 1) {
				$doctor['current_addons'][] = $mp; // текущие дополнительные места работы
			}
		}
		if ( !isset($doctor['current_main']) ) {
			// основное место работы не нашлось, пусть будет первое попавшееся
			if ( count($doctor['current_addons']) > 0 ) {
				$doctor['current_main'] = $doctor['current_addons'][0];
				unset($doctor['current_addons'][0]);
				$doctor['current_addons'] = array_values($doctor['current_addons']);
			}
		}
		$doctor['Age'] = isset($doctor['current_addons']) ? $doctor['current_addons'][0]['Age'] : null;
		$doctor['start_date'] = (DateTime::createFromFormat('Y-m-d H:i:s',$start_date)->format('Y-m-d') != '2030-01-01') ? $start_date : null;
		$doctor['end_date'] = (DateTime::createFromFormat('Y-m-d H:i:s',$end_date)->format('Y-m-d') != '1970-01-01') ? $end_date : null;
		if ( isset($doctor['current_main']) ) {
			//Данные по образованию
			$result = $this->queryResult("
				select
					YearOfGraduation as \"YearOfGraduation\",
					et.name as \"EducationType_Name\",
					coalesce(ei.name, sd.OtherEducationalInstitution) as \"EducationInstitution_Name\",
					sd.DiplomaSpeciality_id as \"Speciality_id\",
					ds.name as \"DiplomaSpeciality_Name\",
					null as \"AcademicMedicalDegree_Name\",
					null as \"Speciality_Code\"
				from persis.SpecialityDiploma sd
				left join persis.EducationType et  on sd.EducationType_id = et.id
				left join persis.EducationInstitution ei  on sd.EducationInstitution_id = ei.id
				left join persis.DiplomaSpeciality ds  on sd.DiplomaSpeciality_id = ds.id
				where
					sd.Medworker_id = :MedPersonal_id
				UNION ALL
				select
					year(graduationDate) as \"YearOfGraduation\",
					pet.name as \"EducationType_Name\",
					coalesce(ei.name, pe.OtherEducationalInstitution) as \"EducationInstitution_Name\",
					pe.Speciality_id as \"Speciality_id\",
					s.name as \"DiplomaSpeciality_Name\",
					amd.name as \"AcademicMedicalDegree_Name\",
					s.code as \"Speciality_Code\"
				from persis.PostgraduateEducation pe
				left join persis.PostgraduateEducationType pet  on pe.PostgraduateEducationType_id = pet.id
				left join persis.EducationInstitution ei  on pe.EducationInstitution_id = ei.id
				left join persis.Speciality s  on pe.Speciality_id = s.id
				left join persis.AcademicMedicalDegree amd  on pe.AcademicMedicalDegree_id = amd.id
				where
					pe.Medworker_id = :MedPersonal_id
				UNION ALL
				select
					Year as \"YearOfGraduation\",
					'Курсы повышения квалификации' as \"EducationType_Name\",
					coalesce(ei.name, qic.OtherEducationalInstitution) as \"EducationInstitution_Name\",
					qic.Speciality_id as \"Speciality_id\",
					s.name as \"DiplomaSpeciality_Name\",
					null as \"AcademicMedicalDegree_Name\",
					s.code as \"Speciality_Code\"
				from persis.QualificationImprovementCourse qic
				left join persis.EducationInstitution ei  on qic.EducationInstitution_id = ei.id
				left join persis.Speciality s on qic.Speciality_id = s.id
				where
					qic.Medworker_id = :MedPersonal_id
			", array('MedPersonal_id' => $doctor['current_main']['MedPersonal_id']));


			$doctor['educations'] = $result;

			//Степень по основной специализации
			foreach($doctor['educations'] as $education) {
				if ( isset($education['AcademicMedicalDegree_Name']) && $education['Speciality_Code'] == $doctor['current_main']['MedSpecOms_Code'] ) {
					$doctor['current_main']['degree'] = $education['AcademicMedicalDegree_Name'];
				}
			}
		}
		return $doctor;
	}
	public function getMsfCommonDescription($doctor_id) {
		$params = array(
			'doctor_id' => $doctor_id,
			'nulltime' => '00:00:00'
		);
		if (!empty($doctor_id)) {
			$result = $this->getFirstRowFromQuery("
				select
					A.Annotation_id,
					A.Annotation_Comment
				from v_Annotation A
				where
					A.MedStaffFact_id = :doctor_id and
					A.Annotation_begDate is null AND
					A.Annotation_endDate is null AND
					(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
					(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime) AND
					A.AnnotationVison_id = 1",$params);
		} else {
			$result = false;
		}
		return $result;
	}

	function getMedStaffFactandLpuUnitById($data) {
		$query = "
			select
				msf.MedStaffFact_id as \"MedStaffFact_did\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				lu.LpuBuilding_id as \"LpuBuilding_id\"
			from
				v_MedStaffFact msf
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			where
				msf.MedStaffFact_id = :MedStaffFact_id
			limit 1
		";
		return $this->dbmodel->getFirstResultFromQuery($query, $data);
	}
	
	function getMedStaffFacMedPersonalByLpuSectionIdAndPostMedName($data) {
		$query = "
			select 
				msf.MedPersonal_id as \"MedPersonal_id\"
			from
				v_MedStaffFact msf
				inner join v_PostMed ps on ps.PostMed_id = msf.Post_id
			where
				ps.PostMed_Name ilike :PostMedName
				and	msf.LpuSection_id = :LpuSection_id
			order by 
				msf.WorkData_begDate desc
			limit 1
		";
		return $this->dbmodel->getFirstRowFromQuery($query, $data);
	}
}
