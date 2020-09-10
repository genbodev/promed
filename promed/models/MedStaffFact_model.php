<?php defined('BASEPATH') or die ('No direct script access allowed');

class MedStaffFact_model extends swModel {

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
			declare
				@MedStaffFact_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				insert into persis.WorkPlace with (rowlock) (
					insDT
					,pmUser_insID
					,updDT
					,pmUser_updID
					,version
					,MilitaryRelation_id
					,BeginDate
					,EndDate
					,ArriveOrderNumber
					,LeaveOrderNumber
					,ArriveRecordType_id
					,Comments
					,Rate
					,PostOccupationType_id
					,Population
					,MedSpecOms_id
					,FRMPSubdivision_id
					,LeaveRecordType_id
					,WorkMode_id
					,MedWorker_id
					,Staff_id
					,TabCode
					,PriemTime
					,Contacts
					,Descr
					,IsDirRec
					,IsQueueOnFree
					,IsOMS
					,RecType_id
					,DLOBeginDate
					,DLOEndDate
					,OfficialSalary
					,CommonLabourDays
					,CommonLabourYears
					,CommonLabourMonths
					,SpecialLabourDays
					,SpecialLabourYears
					,SpecialLabourMonths
					,QualificationLevel
					,AdditionalAgreementDate
					,AdditionalAgreementNumber
					,DisableWorkPlaceChooseInDocuments
					,IsNotReception
					,IsDummyWP
					,IsSpecSet
					,MedStaffFactOuter_id
					,IsHomeVisit
					,IsNotShown
				) values (
					dbo.tzGetDate()
					,:pmUser_id
					,dbo.tzGetDate()
					,:pmUser_id
					,1
					,:MilitaryRelation_id
					,:BeginDate
					,:EndDate
					,:ArriveOrderNumber
					,:LeaveOrderNumber
					,:ArriveRecordType_id
					,:Comments
					,:Rate
					,:PostOccupationType_id
					,:Population
					,:MedSpecOms_id
					,:FRMPSubdivision_id
					,:LeaveRecordType_id
					,:WorkMode_id
					,:MedPersonal_id
					,:Staff_id
					,:TabCode
					,:PriemTime
					,:Contacts
					,:Descr
					,:IsDirRec
					,:IsQueueOnFree
					,:IsOMS
					,:RecType_id
					,:DLOBeginDate
					,:DLOEndDate
					,:OfficialSalary
					,:CommonLabourDays
					,:CommonLabourYears
					,:CommonLabourMonths
					,:SpecialLabourDays
					,:SpecialLabourYears
					,:SpecialLabourMonths
					,:QualificationLevel
					,:AdditionalAgreementDate
					,:AdditionalAgreementNumber
					,:DisableWorkPlaceChooseInDocuments
					,:IsNotReception
					,:IsDummyWP
					,:IsSpecSet
					,:MedStaffFactOuter_id
					,:IsHomeVisit
					,:IsNotShown
				)

				set @MedStaffFact_id = (select scope_identity());
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			set nocount off;

			select @MedStaffFact_id as MedStaffFact_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		if(is_array($resp) && !empty($resp[0]['MedStaffFact_id']) && empty($resp[0]['Error_Msg'])){
			$query2 = "
				declare
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
				exec persis.p_WorkPlace_ins
					@WorkPlace_id = :MedStaffFact_id,
					@IsReload = 0, --(0 работа с кешем одной записи/1 работа с кешем по всем записям)
					@IsMerge = null,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					w.MilitaryRelation_id
					,convert(varchar(10), w.BeginDate, 120) as BeginDate
					,convert(varchar(10), w.EndDate, 120) as EndDate
					,w.ArriveOrderNumber
					,w.LeaveOrderNumber
					,w.ArriveRecordType_id
					,w.Comments
					,w.Rate
					,w.PostOccupationType_id
					,w.Population
					,w.MedSpecOms_id
					,w.FRMPSubdivision_id
					,w.LeaveRecordType_id
					,w.WorkMode_id
					,w.MedWorker_id as MedPersonal_id
					,w.Staff_id
					,w.TabCode
					,w.PriemTime
					,w.Contacts
					,w.Descr
					,w.IsDirRec
					,w.IsQueueOnFree
					,w.IsOMS
					,w.RecType_id
					,convert(varchar(10), w.DLOBeginDate, 120) as DLOBeginDate
					,convert(varchar(10), w.DLOEndDate, 120) as DLOEndDate
					,w.OfficialSalary
					,w.CommonLabourDays
					,w.CommonLabourYears
					,w.CommonLabourMonths
					,w.SpecialLabourDays
					,w.SpecialLabourYears
					,w.SpecialLabourMonths
					,w.QualificationLevel
					,convert(varchar(10), w.AdditionalAgreementDate, 120) as AdditionalAgreementDate
					,w.AdditionalAgreementNumber
					,w.DisableWorkPlaceChooseInDocuments
					,w.IsNotReception
					,w.IsDummyWP
					,w.MedStaffFactOuter_id
					,m.LpuSection_id
					,m.Lpu_id
					,m.MedStaffFact_id
				from persis.WorkPlace w with (nolock)
				left join dbo.v_MedStaffFactCache m with (nolock) on m.MedStaffFact_id = w.id
				where
					(1=1)
					{$filter}
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end catch

			set nocount off;
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
				msf.Server_id,
				msf.MedStaffFact_id,
				msf.MedPersonal_id,
				msf.Person_Fio,
				msf.LpuSection_id,
				ls.LpuSection_Name,
				l.Lpu_Nick,
				l.Lpu_id
			from
				v_MedStaffFact msf (nolock)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_Lpu l (nolock) on l.Lpu_id = msf.Lpu_id
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
			$filter[] = '(msf.WorkData_begDate is null or msf.WorkData_begDate <= :compareDate)';
			$filter[] = '(msf.WorkData_endDate is null or msf.WorkData_endDate > :compareDate)';
			$queryParams['compareDate'] = $data['compareDate'];
		}

		if ( isset($data['Person_SurName']) )
		{
			$filter[] = "msf.Person_SurName like :Person_SurName";
			$queryParams['Person_SurName'] = $data['Person_SurName'];
		}

		if ( isset($data['Person_FirName']) )
		{
			$filter[] = "msf.Person_FirName like :Person_FirName";
			$queryParams['Person_FirName'] = $data['Person_FirName'];
		}

		if ( isset($data['Person_SecName']) )
		{
			$filter[] = "msf.Person_SecName like :Person_SecName";
			$queryParams['Person_SecName'] = $data['Person_SecName'];
		}

		if ( isset($data['Person_BirthDay']) )
		{
			$filter[] = "convert(varchar,cast(msf.Person_BirthDay as datetime),104) = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		$filter[] = "msf.MedStaffFact_Stavka != 0";

		$sql ="
			select
				msf.Person_SurName,
				msf.Person_FirName,
				msf.Person_SecName,
				convert(varchar,cast(msf.Person_BirthDay as datetime),104) as Person_BirthDay,
				msf.MedStaffFact_id,
				msf.MedPersonal_id,
				msf.LpuSection_id,
				mp.Dolgnost_Name,
				msf.MedStaffFact_Stavka,
				ls.LpuSection_Name,
				PostOccupationType.name as PostOccupationTypeName,
				MoveMedWorker.MoveInOrgRecordType_id,
				l.Lpu_Nick,
				l.Lpu_id
			from
				v_MedStaffFact msf (nolock)
				left join v_MedPersonal mp with (NOLOCK) on msf.MedPersonal_id = mp.MedPersonal_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_Lpu l (nolock) on l.Lpu_id = msf.Lpu_id
				left join persis.WorkPlace WorkPlace with (nolock) on WorkPlace.id = msf.MedStaffFact_id
				left join persis.MoveMedWorker MoveMedWorker with (nolock) on MoveMedWorker.WorkPlace_id = WorkPlace.id
				left join persis.PostOccupationType PostOccupationType with (nolock) on msf.PostOccupationType_id = PostOccupationType.id
			where
			".implode(" AND ", $filter);

		//var_dump(getDebugSQL($sql, $queryParams)); exit;
		return $this->queryResult($sql, $queryParams);
	}

	/**
	 *  Получение списка мест работы по МО и профилю
	 */
	function loadMedStaffFactByMOandProfile($data) {

		$query = "
			select
				MedStaffFact_id
			from
				v_MedStaffFact (nolock)
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
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				update 
					persis.WorkPlace with (rowlock)
				set
					updDT = dbo.tzGetDate()
					,pmUser_updID = :pmUser_id
					,MilitaryRelation_id = :MilitaryRelation_id
					,BeginDate = :BeginDate
					,EndDate = :EndDate
					,ArriveOrderNumber = :ArriveOrderNumber
					,LeaveOrderNumber = :LeaveOrderNumber
					,ArriveRecordType_id = :ArriveRecordType_id
					,Comments = :Comments
					,Rate = :Rate
					,PostOccupationType_id = :PostOccupationType_id
					,Population = :Population
					,MedSpecOms_id = :MedSpecOms_id
					,FRMPSubdivision_id = :FRMPSubdivision_id
					,LeaveRecordType_id = :LeaveRecordType_id
					,WorkMode_id = :WorkMode_id
					,MedWorker_id = :MedPersonal_id
					,Staff_id = :Staff_id
					,TabCode = :TabCode
					,PriemTime = :PriemTime
					,Contacts = :Contacts
					,Descr = :Descr
					,IsDirRec = :IsDirRec
					,IsQueueOnFree = :IsQueueOnFree
					,IsOMS = :IsOMS
					,RecType_id = :RecType_id
					,DLOBeginDate = :DLOBeginDate
					,DLOEndDate = :DLOEndDate
					,OfficialSalary = :OfficialSalary
					,CommonLabourDays = :CommonLabourDays
					,CommonLabourYears = :CommonLabourYears
					,CommonLabourMonths = :CommonLabourMonths
					,SpecialLabourDays = :SpecialLabourDays
					,SpecialLabourYears = :SpecialLabourYears
					,SpecialLabourMonths = :SpecialLabourMonths
					,QualificationLevel = :QualificationLevel
					,AdditionalAgreementDate = :AdditionalAgreementDate
					,AdditionalAgreementNumber = :AdditionalAgreementNumber
					,DisableWorkPlaceChooseInDocuments = :DisableWorkPlaceChooseInDocuments
					,IsNotReception = :IsNotReception
					,IsDummyWP = :IsDummyWP
					,MedStaffFactOuter_id = :MedStaffFactOuter_id
					,IsSpecSet = :IsSpecSet
					,IsHomeVisit = :IsHomeVisit
				where
					id = :MedStaffFact_id
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		if(is_array($resp) && empty($resp[0]['Error_Msg'])){
			$query2 = "
				declare
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
				exec persis.p_WorkPlace_upd
					@WorkPlace_id = :MedStaffFact_id,
					@IsReload = 1,
					@IsMerge = null,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				msf.MedStaffFact_id,
				msf.Person_id,
				msf.Person_SurName as PersonSurName_SurName,
				msf.Person_FirName as PersonFirName_FirName,
				msf.Person_SecName as PersonSecName_SecName,
				msf.RecType_id,
				LU.LpuUnitType_id,
				convert(varchar(10), msf.WorkData_begDate, 104) as MedStaffFact_setDate,
				convert(varchar(10), msf.WorkData_endDate, 104) as MedStaffFact_disDate
			from
				v_MedStaffFact msf (nolock)
				left join v_LpuUnit LU on LU.LpuUnit_id = msf.LpuUnit_id
			where
				msf.Lpu_id = :Lpu_id
				and (isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
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
				msf.MedStaffFact_id,
				ROW_NUMBER() OVER (ORDER BY MedStaffFact_id) AS 'RowNumber',
				msf.Person_id,
				msf.Person_SurName as PersonSurName_SurName,
				msf.Person_FirName as PersonFirName_FirName,
				msf.Person_SecName as PersonSecName_SecName,
				ls.Lpu_id,
				ls.Lpu_Name,
				ls.LpuSection_id,
				ls.LpuSection_Code,
				ls.LpuSection_Name,
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Code,
				lsp.LpuSectionProfile_Name,
				mso.MedSpecOms_id,
				mso.MedSpecOms_Code,
				mso.MedSpecOms_Name,
				msf.RecType_id,
				LU.LpuUnitType_id,
				convert(varchar(10), msf.WorkData_begDate, 104) as MedStaffFact_setDate,
				convert(varchar(10), msf.WorkData_endDate, 104) as MedStaffFact_disDate,
				msf.PostKind_id,
				FMS.MedSpec_id as FedMedSpec_id,
				FMS.MedSpec_Name as FedMedSpec_Name,
				msf.MedPersonal_id
			from
				v_MedStaffFact msf (nolock)
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = msf.LpuUnit_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_LpuSectionProfile (nolock) lsp on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join fed.v_MedSpec FMS with (nolock) on FMS.MedSpec_id = MSO.MedSpec_id
			where (1=1) {$filter}
			)
			select *
			from MedStaffs
			where RowNumber between :start and :limit;
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
			$joinList['persis.WorkPlace'] = 'left join persis.WorkPlace wp with (nolock) on wp.id = msf.MedStaffFact_id';
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
			$joinList['v_LpuUnit'] = 'left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = msf.LpuUnit_id';
			$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = 'msf.LpuSection_id = :LpuSection_id';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filterList[] = 'ls.LpuSectionProfile_id = :LpuSectionProfile_id';
			$joinList['v_LpuSection'] = 'left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id';
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		$resp = $this->queryResult('
			select
				msf.MedStaffFact_id,
				msf.Person_Surname as PersonSurName_SurName,
				msf.Person_Firname as PersonFirName_FirName,
				msf.Person_Secname as PersonSecName_SecName
			from
				v_MedStaffFact msf (nolock)
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
		$fieldList = array('msf.MedSpecOms_id');
		$filterList = array('msf.Lpu_id = :Lpu_id');
		$joinList = array();
		$variablesList = array();

		if (!empty($data['LpuBuilding_id'])) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filterList[] = "msf.LpuBuilding_id = :LpuBuilding_id";
		}

		if (!empty($data['LpuSection_id'])) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filterList[] = "msf.LpuSection_id = :LpuSection_id";
		}

		if ( !empty($data['forRecord']) ) {
			$fieldList[] = "sum(rec.TimetableGraf_Count) as TimetableGraf_Count";
			$filterList[] = "msf.MedSpecOms_id is not null";
			$joinList[] = "
				outer apply (
					select
						count(case when ttg.Person_id is null and ttal.TimetableTypeAttributeLink_id is not null and ttg.TimeTableType_id in (1, 9, 11) then TimeTableGraf_id else null end) as TimetableGraf_Count
					from v_TimetableGraf_lite ttg (nolock)
						outer apply (
							select top 1 TimetableTypeAttributeLink_id
							from TimetableTypeAttributeLink (nolock)
							where TimetableType_id = ttg.TimetableType_id
								and TimetableTypeAttribute_id in (8, 9)
						) ttal
					where ttg.MedStaffFact_id = msf.MedStaffFact_id
						and ttg.TimetableGraf_begTime >= @getdate
				) rec
			";
			$variablesList[] = "declare @getdate datetime = dbo.tzGetDate();";
		}

		if (getRegionNick() == 'penza') {
			$fieldList[] = "MIN(msc.MedSpecClass_id) as MedSpecClass_id";
			$fieldList[] = "MIN(msc.MedSpecClass_Name) as MedSpecClass_Name";
			$joinList[] = "
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join fed.v_MedSpecClass msc (nolock) on msc.MedSpecClass_id = mso.MedSpecClass_id
			";
		}

		return $this->queryResult("
			" . implode(" ", $variablesList) . "

			select
				" . implode(",", $fieldList) . "
			from
				v_MedStaffFact msf (nolock)
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
			select top 1 MSF.*
			from v_MedStaffFact MSF with(nolock)
			where MSF.MedStaffFact_id = :MedStaffFact_id
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
			select top 1
				msf.MedStaffFact_id
			from
				v_MedStaffFact msf with(nolock)
			where
				{$filter}
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
			declare @curdate datetime = dbo.tzGetDate();
		
			select top 1
				MedPersonal_id,
				MedStaffFact_id,
				LpuSection_id
			from
				v_MedStaffFact with (nolock)
			where
				MedPersonal_id = :MedPersonal_id
				and @curdate between WorkData_begDate and WorkData_endDate
				{$filter_msf}
		";

		return $this->queryResult($query, $data);
	}

	function getMedPersonal($data) {
		return $this->getFirstResultFromQuery("
			select
				MedPersonal_id
			from v_MedStaffFact (nolock)
			where MedStaffFact_id = :MedStaffFact_id
		", $data);
	}
	
	function getMedStaffFactandLpuUnitById($data) {
		$query = "
			select top 1
				msf.MedStaffFact_id as MedStaffFact_did,
				msf.MedPersonal_id,
				msf.LpuSection_id,
				lu.LpuUnit_id,
				lu.LpuBuilding_id
			from 
				v_MedStaffFact msf (nolock)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			where 
				msf.MedStaffFact_id = :MedStaffFact_id
		";
		return $this->dbmodel->getFirstResultFromQuery($query, $data);
	}
	
	function getMedStaffFacMedPersonalByLpuSectionIdAndPostMedName($data) {
		$query = "
			select top 1
				msf.MedPersonal_id
			from
				v_MedStaffFact msf with(nolock)
				inner join v_PostMed ps with(nolock) on ps.PostMed_id = msf.Post_id
			where
				ps.PostMed_Name like :PostMedName -- запрос для заведующих
				and	msf.LpuSection_id = :LpuSection_id
			order by 
				msf.WorkData_begDate desc
		";
		return $this->dbmodel->getFirstRowFromQuery($query, $data);
	}

	/**
	 * Получить информацию о враче
	 *
	 * @param int $doctor_id id врача
	 *
	 * @return Database_Result
	 */
	public function getDoctorInfo($doctor_id, $regions_data = false) {
		$result = $this->getFirstRowFromQuery("
			declare @curDate date = dbo.tzGetDate();
			select top 1
				msf.Lpu_id,
				lsp.LpuSectionProfile_Id,
				(rtrim(msf.Person_surname)+' '+rtrim(msf.Person_firname)+' '+rtrim(msf.Person_secname)) as FullName,
				rtrim(coalesce(nullif(mso.MedSpecOms_PortalName, ''), lsp.ProfileSpec_Name, lsp.LpuSectionProfile_Name, 'Не указана')) as ProfileSpec_Name,
				rtrim(l.Lpu_Name) as Lpu_Name,
				rtrim(l.Lpu_Nick) as Lpu_Nick,
				lu.LpuUnit_id as LpuUnit_id,
				rtrim(LpuUnit_Name) as LpuUnit_Name,
				rtrim(msf.Person_Fin) as Person_ShortFullName,
				(left(msf.Person_firname, 1)+'.'+left(msf.Person_secname, 1)+'.') as Person_FullNameInitials,
				rtrim(msf.Person_surname) as Person_surname,
				(rtrim(msf.Person_surname)+' '+ left(msf.Person_firname, 1)+'.'+left(msf.Person_secname, 1)+'.') as Person_ShortFullNameDots,
				msf.RecType_id,
				msf.MedStaffFact_id,
				c.name as QualificationCat_Name,
				isnull(luat.KLArea_Name + ', ', '') + luas.KLStreet_Name + ', ' + lua.Address_House as LpuUnit_Address,
				msf.RecType_id,
				msf.LpuSectionProfile_id,
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
				isnull(lbs.KLStreet_Name + ', ', '') + isnull(lba.Address_House, '') + isnull(', корп. ' + lba.Address_Corpus, '') as LpuBuilding_Address,
				lb.LpuBuilding_id,
				lsp.LpuSectionProfile_mainid,
				case when (
					select
						count(AV.AttributeValue_id)
					from
						v_AttributeValue AV (nolock)
						inner join v_Attribute A (nolock) on A.Attribute_id = AV.Attribute_id and A.Attribute_SysNick in ('portalzno', 'EarlyDetect')
						left join v_AttributeVision AVI (nolock) on AVI.Attribute_id = A.Attribute_id
						left join v_AttributeSignValue ASV (nolock) on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
					where 
						AV.AttributeValue_TableName = 'dbo.LpuSection'
						and AV.AttributeValue_ValueBoolean = 1
						and AV.AttributeValue_TablePKey = ls.LpuSection_id
						and isnull(AVI.AttributeVision_begDate, '2000-01-01') <= @curDate
						and isnull(AVI.AttributeVision_endDate, '2030-01-01') >= @curDate
						and isnull(A.Attribute_begDate, '2000-01-01') <= @curDate
						and isnull(A.Attribute_endDate, '2030-01-01') >= @curDate
						and isnull(ASV.AttributeSignValue_begDate, '2000-01-01') <= @curDate
						and isnull(ASV.AttributeSignValue_endDate, '2030-01-01') >= @curDate
				) = 2 then 1 else 0 end as cabinetDetectionZNO
			from
				v_MedStaffFact msf with (nolock)
				left join v_MedSpecOms mso (nolock) on msf.MedSpecOms_id = mso.MedSpecOms_id
				left join MedStaffRegion msr with (nolock) on msr.MedStaffFact_id = msf.MedStaffFact_id and isnull(msr.MedStaffRegion_endDate, '2030-01-01') >= @curDate
				left join LpuRegion lr with (nolock) on msr.LpuRegion_Id = lr.LpuRegion_Id
				left join v_LpuSection ls with (nolock) on msf.LpuSection_Id = ls.LpuSection_Id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
				left join v_Address lba with (nolock) on lba.Address_id = lb.Address_id
				left join v_KLStreet lbs with (nolock) on lbs.KLStreet_id = lba.KLStreet_id
				left join v_KLCity lbc with (nolock) on lbc.KLCity_id = lba.KLCity_id
				left join v_LpuUnit lu with (nolock) on msf.LpuUnit_id = lu.LpuUnit_id
				left join Address lua with (nolock) on lu.Address_id = lua.Address_id
				left join KLStreet luas with (nolock) on lua.KLStreet_id = luas.KLStreet_id
				left join KLArea luat with (nolock) on lua.KLTown_id = luat.KLArea_id
				left join v_Lpu l with (nolock) on lu.Lpu_id = l.Lpu_id
				left join v_LpuSectionProfile lsp with (nolock) on ls.LpuSectionProfile_Id = lsp.LpuSectionProfile_Id
				left join persis.QualificationCategory qc with (nolock) on msf.MedPersonal_id = qc.MedWorker_id
				left join persis.Category c with (nolock) on qc.Category_id = c.id
				left join v_PersonState ps (nolock) on msf.Person_id = ps.Person_id
				left join v_MedServiceElectronicQueue mseq (nolock) on mseq.MedStaffFact_id = msf.MedStaffFact_id
				left join v_ElectronicService es (nolock) on es.ElectronicService_id = mseq.ElectronicService_id
			where
				msf.MedStaffFact_id = :doctor_id
				and ISNULL(l.Lpu_IsTest, 1) = 1
			order by qc.Category_id desc", array('doctor_id' => $doctor_id));

		if (!empty($result)) {
			// Начальная обработка результатов
			$result['FullName'] = ucwords($result['FullName']);
			$result['Person_ShortFullNameDots'] = ucwords($result['Person_surname']) . ' ' . $result['Person_FullNameInitials'];
			$result['ProfileSpec_Name'] = ucfirst(($result['ProfileSpec_Name']));
			$result['QualificationCat_Name'] = ucfirst($result['QualificationCat_Name']);
			$result['annot'] = $this->getMsfCommonDescription($doctor_id);

			if ($regions_data) {
				$regions_model = $this->load->model('LpuRegion_model');
				$result['regions'] = $regions_model->getLpuRegionByMedStaffFact($result['MedStaffFact_id']);
			}
		}
		return $result;
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

	/**
	 * Получение информации о докторе по идентификатору места работы
	 */
	public function getDoctorInfoDop($data) {
		// Получаем все места работы, открытые и закрытые, чтобы получить стаж
		// Но если нет ни одного открытого места работы или медперсонал не врач - не выводим информацию
		$result = $this->queryResult("
			-- Определяем работает ли врач
			Declare @mp_id as bigint, @lpu_id as bigint;
			select top 1 
				@mp_id = msf.MedPersonal_id, 
				@lpu_id = msf.Lpu_id
			from v_MedStaffFact msf
				left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id 
				left join v_Lpu l with (nolock) on msf.Lpu_id = l.Lpu_id
			where MedStaffFact_id = :doctor_id and PostKind_id in (1,6,10)
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and l.Lpu_id is not null
				and isnull(l.Lpu_endDate, '2030-01-01') >= getdate()
				and msf.Person_surname is not null
				and ISNULL(l.Lpu_IsTest, 1) = 1
			
			-- Сам запрос
			Select 
				(rtrim(msf.Person_surname)+' '+rtrim(msf.Person_firname)+' '+rtrim(msf.Person_secname)) as FullName,
				qual.name as QualificationCat_Name,
				qual.qcat as QualificationCategory,
				msf.Person_id,
				msf.WorkData_begDate,
				msf.WorkData_endDate,
				msf.PostOccupationType_id as WorkType_id,
				post.name as Dolgnost_Name,
				l.Lpu_id,
				l.Lpu_Name as Lpu_Name,
				mso.MedSpecOms_Name,
				mso.MedSpecOms_Code,
				ls.LpuSection_Name,
				lu.LpuUnit_Name,
				lu.Address_id,
				msf.MedStaffFact_id,
				msf.MedPersonal_id,
				dbo.Age(Person_BirthDay, getdate()) as Age
			from v_MedStaffFact msf (nolock)
				left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id 
				left join v_Lpu l with (nolock) on msf.Lpu_id = l.Lpu_id
				left join v_MedSpecOms mso (nolock) on msf.MedSpecOms_id = mso.MedSpecOms_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join persis.Post as post on msf.Post_id = Post.id
				outer apply (
					select top 1
						spec.name,
						cat.name as qcat
					from persis.QualificationCategory qc (nolock)
						inner join persis.Category cat (nolock) on qc.Category_id = cat.id
						inner join persis.Speciality spec (nolock) on qc.Speciality_id = spec.id
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
				) as qual
			where 
				MedPersonal_id = @mp_id
				and MedStaffFact_Stavka > 0
				and msf.PostKind_id in (1,6,10)
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and l.Lpu_id is not null
				and isnull(l.Lpu_endDate, '2030-01-01') >= getdate()
				and msf.Person_surname is not null
				and ISNULL(l.Lpu_IsTest, 1) = 1
		",array('doctor_id'=>$data['MedStaffFact_id']));


		$start_date = new DateTime('2030-01-01');
		$end_date = new DateTime('1970-01-01');

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
		$doctor['start_date'] = $start_date->format('Y-m-d') != '2030-01-01' ? $start_date : null;
		$doctor['end_date'] = $end_date->format('Y-m-d') != '1970-01-01' ? $end_date : null;

		if ( isset($doctor['current_main']) ) {
			//Данные по образованию
			$result = $this->queryResult("
				select
					YearOfGraduation,
					et.name as EducationType_Name,
					isnull(ei.name, sd.OtherEducationalInstitution) as EducationInstitution_Name,
					sd.DiplomaSpeciality_id as Speciality_id,
					ds.name as DiplomaSpeciality_Name,
					null as AcademicMedicalDegree_Name,
					null as Speciality_Code
				from persis.SpecialityDiploma sd (nolock)
				left join persis.EducationType et (nolock) on sd.EducationType_id = et.id
				left join persis.EducationInstitution ei (nolock) on sd.EducationInstitution_id = ei.id
				left join persis.DiplomaSpeciality ds (nolock) on sd.DiplomaSpeciality_id = ds.id
				where
					sd.Medworker_id = :MedPersonal_id
				UNION ALL
				select
					year(graduationDate) as YearOfGraduation,
					pet.name as EducationType_Name,
					isnull(ei.name, pe.OtherEducationalInstitution) as EducationInstitution_Name,
					pe.Speciality_id as Speciality_id,
					s.name as DiplomaSpeciality_Name,
					amd.name as AcademicMedicalDegree_Name,
					s.code as Speciality_Code
				from persis.PostgraduateEducation pe (nolock)
				left join persis.PostgraduateEducationType pet (nolock) on pe.PostgraduateEducationType_id = pet.id
				left join persis.EducationInstitution ei (nolock) on pe.EducationInstitution_id = ei.id
				left join persis.Speciality s (nolock) on pe.Speciality_id = s.id
				left join persis.AcademicMedicalDegree amd (nolock) on pe.AcademicMedicalDegree_id = amd.id
				where
					pe.Medworker_id = :MedPersonal_id
				UNION ALL
				select
					Year as YearOfGraduation,
					'Курсы повышения квалификации' as EducationType_Name,
					isnull(ei.name, qic.OtherEducationalInstitution) as EducationInstitution_Name,
					qic.Speciality_id as Speciality_id,
					s.name as DiplomaSpeciality_Name,
					null as AcademicMedicalDegree_Name,
					s.code as Speciality_Code
				from persis.QualificationImprovementCourse qic (nolock)
				left join persis.EducationInstitution ei (nolock) on qic.EducationInstitution_id = ei.id
				left join persis.Speciality s (nolock) on qic.Speciality_id = s.id
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
		$allowTodayRecord = $data['allowTodayRecord'];

		// признак записи на сегодня
		// сначала определяется по признаку на ЭО дальше в запросе
		if (!empty($allowTodayRecord)) {
			$begtime_filter = "
				cast(tt.TimeTableGraf_begTime as date) >= cast(dbo.tzGetDate() as date)
				and tt.TimeTableGraf_begTime > DATEADD(minute, 15, dbo.tzGetDate())
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

		$result = $this->queryResult("
			select 
				msf.MedStaffFact_id,
				tt.TimetableGraf_begTime,
				tt.TimeTableGraf_id,
				tt.DateDiff
			from v_MedStaffFact msf (nolock)
			left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
			left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			left join v_Lpu lpu (nolock) on lpu.Lpu_id = msf.Lpu_id
			left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
			-- определяем признак на ЭО если она есть, запись на сегодняшний день
			outer apply (
				select top 1
					eq.ElectronicQueueInfo_id,
					eq.ElectronicQueueInfo_IsCurDay
				from v_MedServiceElectronicQueue mseq (nolock)
				left join v_ElectronicService es (nolock) on es.ElectronicService_id = mseq.ElectronicService_id
				left join v_ElectronicQueueInfo eq (nolock) on eq.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
				where 
					mseq.MedStaffFact_id = msf.MedStaffFact_id
					and isnull(eq.ElectronicQueueInfo_IsOff, 1) != 2
				order by mseq.MedServiceElectronicQueue_id desc
			) eq
			cross apply (
				select top {$N}
					tt.TimetableGraf_begTime,
					datediff(d, dbo.tzGetdate(), tt.TimetableGraf_begTime) as DateDiff,
					tt.TimeTableGraf_id
				from v_TimetableGraf_lite tt (nolock)
				outer apply(
					select top 1
						TimetableTypeAttributeLink_id
					FROM v_TimetableTypeAttributeLink (nolock) 
					where 
						TimetableType_id = tt.TimetableType_id 
						{$TTALfilter}
				) as ttal
				where (1=1)
					-- определяем по признаку ЭО какой фильтр будем использовать
				 	and (1 = CASE
						  WHEN eq.ElectronicQueueInfo_IsCurDay = 2 
						  	THEN case when cast(tt.TimeTableGraf_begTime as date) >= cast(dbo.tzGetDate() as date) and tt.TimeTableGraf_begTime > DATEADD(minute, 15, dbo.tzGetDate())  then 1
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
			) tt
			where (1=1)
			 	and msf.MedStaffFact_id in ({$id_list})
			 	and (isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
				and ISNULL(lsp.LpuSectionProfile_InetDontShow, 1) = 1
				and ISNULL(msf.MedStaffFactCache_IsNotShown, 0) != 2
				and isnull(msf.RecType_id, 6) not in (2,3,5,6,8)
				and lu.LpuUnit_IsEnabled = 2
				and lu.LpuUnitType_id in (2, 5, 10, 12)
				and lsp.ProfileSpec_Name is not null
				and ISNULL(lpu.Lpu_IsTest, 1) = 1
				{$main_filter}
			
		", $params);

		if (!empty($result) && !empty($data['groupResult'])) {

			$this->load->library('calendar');

			foreach ($result as $item) {
				if (!isset($groupedResult[$item['MedStaffFact_id']])) {
					$groupedResult[$item['MedStaffFact_id']] = array();
				}

				$dt = $item['TimetableGraf_begTime'];

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

    /**
     * получение краткой информации по доктору
     */
    function getMedStaffFactShortInfo($data) {

        $result = $this->getFirstRowFromQuery("
			select top 1
				msf.MedStaffFact_id ,
				msf.Lpu_id,
				msf.MedPersonal_id ,
				msf.LpuUnit_id ,
				msf.LpuSection_id ,
				msf.LpuSectionProfile_id,
				concat(msf.Person_surname,' ',msf.Person_firname,' ',msf.Person_secname) as MedPersonal_FullName,
				rtrim(lsp.ProfileSpec_Name) as ProfileSpec_Name,
				lpu.Lpu_Nick,
				concat(rtrim(str.KLStreet_Name),', ',rtrim(a.Address_House)) as LpuUnit_Address,
				msf.MedStaffFactCache_CostRec,
				lb.LpuBuilding_Longitude,
                lb.LpuBuilding_Latitude
			from v_MedStaffFact msf 
			left join v_LpuSection (nolock) ls on msf.LpuSection_id = ls.LpuSection_id
			left join v_Lpu (nolock) lpu on msf.Lpu_id = lpu.Lpu_id
			left join v_LpuSectionProfile (nolock) lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_LpuUnit (nolock) lu on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_LpuBuilding (nolock) lb on lb.LpuBuilding_id = lu.LpuBuilding_id
			left join v_Address (nolock) a on a.Address_id = lu.Address_id
			left join v_KLStreet (nolock) str on str.KLStreet_id = a.KLStreet_id
			where msf.MedStaffFact_id = :MedStaffFact_id
		", $data);

        return $result;
    }

}
