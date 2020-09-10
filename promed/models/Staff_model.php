<?php defined('BASEPATH') or die ('No direct script access allowed');

class Staff_model extends swModel {

	public $inputRules = array(
		'createStaff' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'isDummyStaff', 'label' => 'Флаг фиктивной ставки', 'rules' => 'required', 'type' => 'api_flag'),
			array('default'=> 1, 'field' => 'PayType_id', 'label' => 'Идентификатор источника финансирования', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Rate', 'label' => 'Количество ставок', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Идентификатор типа подразделения', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'BeginDate', 'label' => 'Дата создания', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EndDate', 'label' => 'Дата закрытия', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Comments', 'label' => 'Комментарий', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'RateNorm', 'label' => 'Количество ставок по нормативу', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LeadershipBonusPercent', 'label' => 'Процент надбавки за руководство', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'SalaryReductionPercent', 'label' => 'Процент уменьшения должностного оклада', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'IsVillageBonus', 'label' => 'Флаг «Надбавка за работу на селе»', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'SpecialLabourType', 'label' => 'Учитываемый специальный тип стажа', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'VacancyOfficialSalary', 'label' => 'Оклад у вакантных должностей', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'RateFinancing', 'label' => 'Финансирование ставки', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'UETCount', 'label' => 'Количество УЕТ', 'rules' => 'trim', 'type' => 'int')
		),
		'loadStaffById' => array(
			array(
				'field' => 'Staff_id',
				'label' => 'Идентификатор строки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'updateStaff' => array(
			array('field' => 'Staff_id', 'label' => 'Идентификатор строки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'isDummyStaff', 'label' => 'Флаг фиктивной ставки', 'rules' => '', 'type' => 'api_flag'),
			array('default'=> 1, 'field' => 'PayType_id', 'label' => 'Идентификатор источника финансирования', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Rate', 'label' => 'Количество ставок', 'rules' => '', 'type' => 'float'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Идентификатор типа подразделения', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'BeginDate', 'label' => 'Дата создания', 'rules' => '', 'type' => 'date'),
			array('field' => 'EndDate', 'label' => 'Дата закрытия', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Comments', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'),
			array('field' => 'RateNorm', 'label' => 'Количество ставок по нормативу', 'rules' => '', 'type' => 'int'),
			array('field' => 'LeadershipBonusPercent', 'label' => 'Процент надбавки за руководство', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'SalaryReductionPercent', 'label' => 'Процент уменьшения должностного оклада', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'IsVillageBonus', 'label' => 'Флаг «Надбавка за работу на селе»', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'SpecialLabourType', 'label' => 'Учитываемый специальный тип стажа', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'VacancyOfficialSalary', 'label' => 'Оклад у вакантных должностей', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'RateFinancing', 'label' => 'Финансирование ставки', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'UETCount', 'label' => 'Количество УЕТ', 'rules' => 'trim', 'type' => 'int')
		),
		'loadStaffByMedStaffFact' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadStaffByLpuSection' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'createStaffAPI' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'isDummyStaff', 'label' => 'Флаг фиктивной ставки', 'rules' => 'required', 'type' => 'api_flag'),
			array('default'=> 1, 'field' => 'PayType_id', 'label' => 'Идентификатор источника финансирования', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Rate', 'label' => 'Количество ставок', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Идентификатор типа подразделения', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'BeginDate', 'label' => 'Дата создания', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EndDate', 'label' => 'Дата закрытия', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Идентификатор вида медицинской помощи', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Comments','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
			array('field' => 'RateNorm', 'label' => 'Количество ставок по нормативу', 'rules' => '', 'type' => 'int'),
			array('field' => 'LeadershipBonusPercent', 'label' => 'Процент надбавки за руководство', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'SalaryReductionPercent', 'label' => 'Процент уменьшения должностного оклада', 'rules' => 'trim', 'type' => 'float'),
			array('field' => 'IsVillageBonus', 'label' => 'Флаг «Надбавка за работу на селе»', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'SpecialLabourType', 'label' => 'Учитываемый специальный тип стажа', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'VacancyOfficialSalary', 'label' => 'Оклад у вакантных должностей', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'RateFinancing', 'label' => 'Финансирование ставки', 'rules' => 'trim', 'type' => 'int'),
			array('field' => 'UETCount', 'label' => 'Количество УЕТ', 'rules' => 'trim', 'type' => 'int'),
		),
	);
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
	 * Создание строки штатного расписания
	 */
	function createStaff($data) {

		$query = "
			declare
				@Staff_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				insert into persis.Staff with (rowlock) (
					insDT
					,pmUser_insID
					,updDT
					,pmUser_updID
					,version
					,MedicalCareKind_id
					,BeginDate
					,EndDate
					,Rate
					,Comments
					,Post_id
					,LpuUnit_id
					,Lpu_id
					,LpuSection_id
					,LpuBuilding_id
					,PayType_id
					,RateNorm
					,LeadershipBonusPercent
					,SalaryReductionPercent
					,IsVillageBonus
					,SpecialLabourType
					,VacancyOfficialSalary
					,UETCount
					,RateFinancing
					,isDummyStaff
					,FRMPSubdivision_id
				) values (
					dbo.tzGetDate()
					,:pmUser_id
					,dbo.tzGetDate()
					,:pmUser_id
					,1
					,:MedicalCareKind_id
					,:BeginDate
					,:EndDate
					,:Rate
					,:Comments
					,:Post_id
					,:LpuUnit_id
					,:Lpu_id
					,:LpuSection_id
					,:LpuBuilding_id
					,:PayType_id
					,:RateNorm
					,:LeadershipBonusPercent
					,:SalaryReductionPercent
					,:IsVillageBonus
					,:SpecialLabourType
					,:VacancyOfficialSalary
					,:UETCount
					,:RateFinancing
					,:isDummyStaff
					,:FRMPSubdivision_id
				)

				set @Staff_id = (select scope_identity());
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			set nocount off;

			select @Staff_id as Staff_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Получение строки штатного расписания по идентификатору
	 */
	function loadStaffById($data) {

		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					id as Staff_id,
					isDummyStaff,
					PayType_id,
					Rate,
					FRMPSubdivision_id,
					convert(varchar(10), BeginDate, 120) as BeginDate,
					convert(varchar(10), EndDate, 120) as EndDate,
					MedicalCareKind_id,
					Comments,
					RateNorm,
					LeadershipBonusPercent,
					SalaryReductionPercent,
					IsVillageBonus,
					SpecialLabourType,
					VacancyOfficialSalary,
					RateFinancing,
					UETCount
				from persis.Staff with (nolock)
				where
					id = :Staff_id
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
	 * Обновление строки штатного расписания
	 */
	function updateStaff($data) {

		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				update 
					persis.Staff with (rowlock)
				set
					updDT = dbo.tzGetDate()
					,pmUser_updID = :pmUser_id
					,MedicalCareKind_id = :MedicalCareKind_id
					,BeginDate = :BeginDate
					,EndDate = :EndDate
					,Rate = :Rate
					,Comments = :Comments
					,PayType_id = :PayType_id
					,RateNorm = :RateNorm
					,LeadershipBonusPercent = :LeadershipBonusPercent
					,SalaryReductionPercent = :SalaryReductionPercent
					,IsVillageBonus = :IsVillageBonus
					,SpecialLabourType = :SpecialLabourType
					,VacancyOfficialSalary = :VacancyOfficialSalary
					,UETCount = :UETCount
					,RateFinancing = :RateFinancing
					,isDummyStaff = :isDummyStaff
					,FRMPSubdivision_id = :FRMPSubdivision_id
				where
					id = :Staff_id
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

		return $resp;
	}

	/**
	 * Получение вида должности
	 */
	function getPostKind($data) {

		$query = "
			select
				pk.code as PostKind_Code
			from persis.v_Post post with (nolock)
			inner join persis.v_PostKind pk with (nolock) on pk.id = post.PostKind_id
			where
				post.id = :Post_id
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Получение строки штатного расписания по месту работы
	 */
	function loadStaffByMedStaffFact($data) {
		$where = '';
		if(!empty($data['Lpu_id'])){
			$where .= ' AND wp.Lpu_id = :Lpu_id';
		}

		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					s.id as Staff_id
				from persis.v_WorkPlace wp with (nolock)
				inner join persis.Staff s with (nolock) on s.id = wp.Staff_id
				where
					wp.WorkPlace_id = :MedStaffFact_id
					{$where}
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
	 * Получение строки штатного расписания по отделению МО
	 */
	function loadStaffByLpuSection($data) {
		$where = '';
		if(!empty($data['Lpu_id'])){
			$where .= ' AND Lpu_id = :Lpu_id';
		}
		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					id as Staff_id
				from persis.v_Staff with (nolock)
				where
					LpuSection_id = :LpuSection_id
					{$where}
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
	 * Получение идентификатора Lpu строки штатного расписания
	 */
	function getStaffLpuID($data) {
		if(empty($data['Staff_id'])) return false;
		$query = "
			SELECT
				Lpu_id
			FROM
				persis.v_Staff
			WHERE id = :Staff_id
		";

		$resp = $this->queryResult($query, $data);
		if(!empty($resp[0]['Lpu_id'])){
			return $resp[0]['Lpu_id'];
		}else{
			return false;
		}
	}
}