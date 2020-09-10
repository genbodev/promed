<?php defined('BASEPATH') or die ('No direct script access allowed');

class Staff_model extends SwPgModel {

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
			CREATE OR REPLACE FUNCTION pg_temp.persisStaff
            (out Staff_id bigint,
            out Error_Code int,
            out Error_Message text)
            LANGUAGE 'plpgsql'
            
            AS $$
            DECLARE
            
            Staff_id bigint;
            Error_Code int = null;
            Error_Message varchar(4000) = null;
            
            BEGIN
            
            insert into persis.Staff (
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
				)returning persisStaff.Staff_id into Staff_id;

            exception
            when division_by_zero THEN NULL;
            when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;

            END;
            $$;
            
            select
            Staff_id as \"Staff_id\",
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
            from pg_temp.persisStaff();
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
			select
                id as \"Staff_id\",
                isDummyStaff as \"isDummyStaff\",
                PayType_id as \"PayType_id\",
                Rate as \"Rate\",
                FRMPSubdivision_id as \"FRMPSubdivision_id\",
                to_char(BeginDate, 'yyyy-mm-dd hh24:mm:ss') as \"BeginDate\",
                to_char(EndDate, 'yyyy-mm-dd hh24:mm:ss') as \"EndDate\",
                MedicalCareKind_id as \"MedicalCareKind_id\",
                Comments as \"Comments\",
                RateNorm as \"RateNorm\",
                LeadershipBonusPercent as \"LeadershipBonusPercent\",
                SalaryReductionPercent as \"SalaryReductionPercent\",
                IsVillageBonus as \"IsVillageBonus\",
                SpecialLabourType as \"SpecialLabourType\",
                VacancyOfficialSalary as \"VacancyOfficialSalary\",
                RateFinancing as \"RateFinancing\",
                UETCount as \"UETCount\"
            from
                persis.Staff
            where
                id = :Staff_id
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Обновление строки штатного расписания
	 */
	function updateStaff($data) {

		$query = "
			update 
					persis.Staff
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
				pk.code as \"PostKind_Code\"
			from persis.v_Post post
			inner join persis.v_PostKind pk on pk.id = post.PostKind_id
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
			select
                s.id as \"Staff_id\"
            from 
                persis.v_WorkPlace wp
				inner join persis.Staff s on s.id = wp.Staff_id
            where
                wp.WorkPlace_id = :MedStaffFact_id
                {$where}
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
			select
                id as \"Staff_id\"
            from 
                persis.v_Staff
            where
                LpuSection_id = :LpuSection_id
                {$where}
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
				Lpu_id as \"Lpu_id\"
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