<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Класс модели для работы по иммунопрофилактике (PostgreSQL)
 *
 * @package        Common
 * @author        Nigmatullin Tagir (Ufa)
 *
 */

class Vaccine_model extends SwPgModel
{
    protected $dateTimeFormat104 = "'dd.mm.yyyy'";
    protected $dateTimeFormat112 = "'yyyymmdd'";

    /**
     * Получение списка Вакцин из справочника вакцин
     * Используется: окно просмотра и редактирования справочника вакцин
     */
    public function getVaccineGridDetail_New()
    {
        echo '"Это моя модель!';
        return true;
    }

    /**
     * Список прививок для грида
     * @return false|array
     */
    public function getVaccineGridDetail()
    {
        $sql = "
			select
				Vaccine_id as \"Vaccine_id\",
				Name as \"Name\",
				SignComb as \"SignComb\",
				CodeInf as \"CodeInf\",
				NameVac as \"NameVac\",
				GRID_NAME_VAC as \"GRID_NAME_VAC\",
				NAME_TYPE_VAC as \"NAME_TYPE_VAC\",
				AgeRange as \"AgeRange\",
				AgeRange2Sim as \"AgeRange2Sim\",
				SignWayPlace as \"SignWayPlace\",
				WayPlace_id as \"WayPlace_id\",
				WAY_PLACE as \"WAY_PLACE\",
				SignDoza as \"SignDoza\",
				Doza as \"Doza\",
				VACCINE_DOZA as \"VACCINE_DOZA\"
			from
				vac.v_Vaccine vac
			";

        $res = $this->db->query($sql);
        if (!is_object($res))
            return false;

        return $res->result($res);
    }

    /**
     * Тип срока годности (месяцев или лет)
	 *
	 * @return false|array
     */
    public function GetVaccineTypePeriod()
    {

        $query = "
			select
				VaccineTipPeriod_id as \"TipPeriod_id\",
				VaccineTipPeriod_name as \"TipPeriod_name\"
			from
				vac.S_VaccineTipPeriod
		";

        $result = $this->db->query($query);

        if (!is_object($result))
            return false;

		return $result->result('array');
    } // end GetVaccineTypePeriod

    /**
     * Тип введения прививки
	 *
	 * @return false|array
     */
    public function GetVaccineWay()
    {

        $query = "
			SELECT
				VaccineWay_id as \"VaccineWay_id\",
				VaccineWay_name as \"VaccineWay_name\"
			FROM
				vac.S_VaccineWay 
			order by VaccineWay_name
		";

        $result = $this->db->query($query);

        if (!is_object($result))
			return false;

		return $result->result('array');
	} // end GetVaccineWay

	/**
	 * Место введения прививки
	 *
	 * @param array $data
	 * @return bool|array
	 */
    public function GetVaccinePlace($data)
    {
        $filter = "";
        if ((isset($data['VaccineWay_id'])) && ($data['VaccineWay_id'] > 0))
            $filter .= "where VaccineWay_id = " . $data['VaccineWay_id'];

        $query = "
			select
				VaccinePlace_id as \"VaccinePlace_id\",
				VaccinePlace_name as \"VaccinePlace_name\",
				VaccineWay_id as \"VaccineWay_id\"
			from
				vac.S_VaccinePlace
			{$filter}
			order by VaccinePlace_name
		";

        $result = $this->db->query($query);

        if (!is_object($result))
        	return false;

		return $result->result('array');
    } // end GetVaccinePlace

    /**
     * Список прививок
     * @return bool|array
     */
    public function GetSprInoculation()
    {

        $query = "
			select
				VaccineType_id as \"VaccineType_id\",
				VaccineType_name as \"VaccineType_name\"
			from
				vac.v_Inoculation4Combo
			order by VaccineType_name
		";


        $result = $this->db->query($query);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * Реакция манту
     * @param array $data
     * @return bool|array
     */
    public function GetMantuReaction($data)
    {
        $person_id = $data['Person_id'];
        $sql = "
            select
				Person_id as \"Person_id\",
				Age as \"Age\",
				to_char(dateVac, {$this->dateTimeFormat104}) as \"dateVac\",
				Dose as \"Dose\",
				Seria as \"Seria\",
				to_char(Period, {$this->dateTimeFormat104}) as \"Period\",
				WayPlace as \"WayPlace\",
				to_char(DateReact, {$this->dateTimeFormat104}) as \"DateReact\",
				ReactDescription as \"ReactDescription\"
            from
                vac.fn_GetMantu4PrintKard063 (" . $person_id . ")
            where dateVac is not null
		";
        $result = $this->db->query($sql);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * Исполненные прививки (Старый)
     * @param $data
     * @return bool|array
     */
    function GetInoculationData_Old($data)
    {
        $sql = "
            select
                Inoculation_id as \"Inoculation_id\",
                age as \"age\",
                typeName as \"typeName\",
                to_char(DateVac, {$this->dateTimeFormat104}) as \"DateVac\",
                VaccineType_Name as \"VaccineType_Name\",
                Vaccine_Name as \"Vaccine_Name\",
                Dose as \"Dose\",
                Seria as \"Seria\",
                to_char(Period, {$this->dateTimeFormat104}) as \"Period\",
                WayPlace as \"WayPlace\",
                ReactGeneralDescription as \"ReactGeneralDescription\"
            from
                vac.fn_GetData4PrintKard063 (:Person_id)
            order by typeName
		";
        $result = $this->db->query($sql, $data);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

	/**
	 * Исполненные прививки
	 *
	 * Результат содержит следующие столбцы:
	 *   DateVac - дата вакцинации.
	 *   age - возраст пациента.
	 *   Vaccine_Name - наименование вакцины.
	 *   VaccineType_Name - назначение вакцины.
	 *   Seria - серия.
	 *   Dose - доза.
	 *   Place - место введения.
	 *   WayPlace - способ и место введения.
	 *   ReactGeneralDescription - реакция.
	 *   typeName - вид вакцинации, столбец присутствует, только если $data['needType'] == true.
	 *   Lpu_Name - наименование ЛПУ, столбец присутствует, только если $data['needLpu'] == true.
	 */
	function GetInoculationData($data)
	{
		$person_id = $data['Person_id'];

		$fields =
			"Inoculation_id as \"Inoculation_id\",
			to_char(vacJournalAccount_DateVac, {$this->dateTimeFormat104}) as \"DateVac\",
			vacJournalAccount_age as \"age\",
			Vaccine_Name as \"Vaccine_Name\",
			VaccineType_Name as \"VaccineType_Name\",
			CASE
				WHEN vacJournalAccount_Period IS NOT NULL
					THEN vacJournalAccount_Seria
				WHEN vacJournalAccount_Period IS NULL AND POSITION('-' IN vacJournalAccount_Seria) > 0
					THEN LTRIM(RTRIM(SUBSTRING(vacJournalAccount_Seria, 1, POSITION('-' IN vacJournalAccount_Seria) - 1)))
				ELSE NULL
			END as \"Seria\",
			vacJournalAccount_Dose as \"Dose\",
			pl.VaccinePlace_Name as \"Place\",
			CASE
				WHEN pl.VaccinePlace_Name IS NULL
					AND wp.VaccinePlace_Name IS NULL
					AND ac.VaccinePlace_id IS NOT NULL
				THEN
					wp.VaccineWay_Name
				WHEN pl.VaccinePlace_Name IS NULL
					AND ac.VaccinePlace_id IS NOT NULL
				THEN
					wp.VaccinePlace_Name || ': ' || wp.VaccineWay_Name
				ELSE
					pl.VaccinePlace_Name || ': ' || w.VaccineWay_Name
			END as \"WayPlace\",
			ac.vacJournalAccount_ReactGeneralDescription AS \"ReactGeneralDescription\"
			";

		$from = "
			vac.vac_JournalAccount ac
			LEFT OUTER JOIN vac.Inoculation i ON i.vacJournalAccount_id = ac.vacJournalAccount_id
			LEFT OUTER JOIN vac.S_VaccineType vt ON vt.VaccineType_id = i.VaccineType_id
			LEFT OUTER JOIN vac.v_VaccinePlace pl ON pl.VaccinePlace_id = ac.VaccinePlace_id
			LEFT OUTER JOIN vac.S_VaccineWay w ON ac.VaccineWay_id = w.VaccineWay_id
			LEFT JOIN LATERAL(
				SELECT p2.VaccinePlace_Name, w2.VaccineWay_Name
				FROM vac.v_VaccineWayPlace wp2
					LEFT JOIN vac.v_VaccinePlace p2 ON wp2.VaccinePlace_id = p2.VaccinePlace_id
					LEFT JOIN vac.S_VaccineWay w2 ON wp2.VaccineWay_id = w2.VaccineWay_id
				WHERE ac.VaccinePlace_id = wp2.VaccineWayPlace_id
				LIMIT 1
			) as wp 
			on true
			LEFT OUTER JOIN vac.S_Vaccine v ON v.Vaccine_id = ac.vaccine_id
			";

		$order = "VaccineType_Name, vacJournalAccount_DateVac";

		if (!empty($data['needType']))
		{
			$fields = $fields . ",
				CASE
					WHEN nc.Type_id = 0 AND
						nc.NationalCalendarVac_SequenceVac >0 AND
						nc.NationalCalendarVac_SequenceVac <=4
						THEN 'V' + CAST(nc.NationalCalendarVac_SequenceVac AS VARCHAR)
					WHEN nc.Type_id = 0
						THEN 'V' -- 'Вакцинация'
					WHEN nc.Type_id = 1 AND
						nc.NationalCalendarVac_SequenceVac >0 AND
						nc.NationalCalendarVac_SequenceVac <=4
						THEN 'R' + CAST(nc.NationalCalendarVac_SequenceVac AS VARCHAR)-- + '-я ревакцинация'
					WHEN nc.Type_id = 1
						THEN 'R' --'Ревакцинация'
					WHEN nc.Type_id = 2
						THEN 'Иммунизация'
				END AS \"typeName\"
				";

			$from = $from . "
				LEFT OUTER JOIN vac.S_NationalCalendarVac nc
					ON nc.NationalCalendarVac_Scheme_id = REPLACE(i.NationalCalendarVac_Scheme_id, '_', '')
				";

			$order = $order . ", nc.Type_id";
		}

		if (!empty($data['needLpu']))
		{
			$fields = $fields . ",
				lpu.Lpu_Name Lpu_Name
				";

			$from = $from . " LEFT OUTER JOIN dbo.v_Lpu lpu ON lpu.Lpu_id = ac.vacJournalAccount_Lpu_id
				";
		}

		$sql =
			"SELECT " . $fields .
			"FROM" . $from .
			"WHERE
				ac.Person_id = {$person_id}
				AND ac.vacJournalAccount_StatusType_id = 1
			ORDER BY " . $order;

		$result = $this->db->query($sql);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	} // GetInoculationData()

    /**
     * Исполненные прививки
     *
     * @param $data
     * @return bool|array
     */
    function GetInoculationData1($data)
    {
		$fields =
			"
			Inoculation_id as \"Inoculation_id\",
            vacJournalAccount_age as \"age\" ,
			to_char(vacJournalAccount_DateVac, {$this->dateTimeFormat104}) as \"DateVac\",
            VaccineType_Name as \"VaccineType_Name\",
            Vaccine_Name as \"Vaccine_Name\",
            vacJournalAccount_Dose as \"Dose\",
			case
                    when vacJournalAccount_Period  is not null
                        then vacJournalAccount_Seria 
                    when vacJournalAccount_Period  is null and strpos('-', vacJournalAccount_Seria) > 0
                        then trim (SUBSTRING (vacJournalAccount_Seria, 1, strpos('-', vacJournalAccount_Seria) - 1))
                    else null	
                end as \"Seria\",
			pl.VaccinePlace_Name as \"Place\",
            
			CASE
				WHEN pl.VaccinePlace_Name IS NULL
					AND wp.VaccinePlace_Name IS NULL
					AND ac.VaccinePlace_id IS NOT NULL
				THEN
					wp.VaccineWay_Name
				WHEN pl.VaccinePlace_Name IS NULL
					AND ac.VaccinePlace_id IS NOT NULL
				THEN
					wp.VaccinePlace_Name || ': ' || wp.VaccineWay_Name
				ELSE
					pl.VaccinePlace_Name || ': ' || w.VaccineWay_Name
			END as \"WayPlace\",
			case
                    when vacJournalAccount_Period  is not null
                        then to_char(vacJournalAccount_Period, {$this->dateTimeFormat104}) 
                    when vacJournalAccount_Period  is null and strpos('-', vacJournalAccount_Seria) > 0
                        then trim(SUBSTRING (vacJournalAccount_Seria, strpos('-', vacJournalAccount_Seria) + 1, length(vacJournalAccount_Seria)))
                    else null	
            end as \"Period\",
			ac.vacJournalAccount_ReactGeneralDescription as  \"ReactGeneralDescription\"
			";

		$from = "
		
		vac.vac_JournalAccount ac
                left outer join vac.Inoculation i on i.vacJournalAccount_id = ac.vacJournalAccount_id
                left outer join vac.S_VaccineType vt on vt.VaccineType_id = i.VaccineType_id
                left outer join vac.S_NationalCalendarVac nc on  nc.NationalCalendarVac_Scheme_id = replace(i.NationalCalendarVac_Scheme_id, '_', '') 
                left outer join vac.S_VaccineWay way on way.VaccineWay_id = ac.VaccineWay_id 
                left outer join vac.v_VaccinePlace pl on pl.VaccinePlace_id = ac.VaccinePlace_id
                left outer join vac.S_Vaccine v on v.Vaccine_id = ac.vaccine_id

			LEFT JOIN LATERAL(
				SELECT
					p2.VaccinePlace_Name, w2.VaccineWay_Name
				FROM vac.v_VaccineWayPlace wp2
					LEFT JOIN vac.v_VaccinePlace p2 ON wp2.VaccinePlace_id = p2.VaccinePlace_id
					LEFT JOIN vac.S_VaccineWay w2 ON wp2.VaccineWay_id = w2.VaccineWay_id
				WHERE ac.VaccinePlace_id = wp2.VaccineWayPlace_id
				LIMIT 1
			) as wp on true
			";

		$order = "VaccineType_Name, vacJournalAccount_DateVac";

		if (!empty($data['needType']))
		{
			$fields = $fields . ",
				case
                    when nc.Type_id = '0' and nc.NationalCalendarVac_SequenceVac > 0 and nc.NationalCalendarVac_SequenceVac <= 4
                        then 'V' || cast(nc.NationalCalendarVac_SequenceVac as varchar) 
                    when nc.Type_id = '0'
                        then  'V'-- 'Вакцинация'
                    when nc.Type_id = '1' and nc.NationalCalendarVac_SequenceVac > 0 and nc.NationalCalendarVac_SequenceVac <= 4
                        then 'R' || cast(nc.NationalCalendarVac_SequenceVac as varchar)-- + '-я ревакцинация'
                    when nc.Type_id = '1'
                        then  'R' --'Ревакцинация'
                    when nc.Type_id = '2'
                        then  'Иммунизация'				
                end as \"typeName\",
				";

			$from = $from . "
				LEFT OUTER JOIN vac.S_NationalCalendarVac nc
					ON nc.NationalCalendarVac_Scheme_id = REPLACE(i.NationalCalendarVac_Scheme_id, '_', '')
				";

			$order = $order . ", nc.Type_id";
		}

		if (!empty($data['needLpu']))
		{
			$fields = $fields . ",
				lpu.Lpu_Name as \"Lpu_Name\"
				";

			$from = $from . "
				LEFT OUTER JOIN dbo.v_Lpu lpu ON lpu.Lpu_id = ac.vacJournalAccount_Lpu_id
				";
		}

		$sql =
			"SELECT " . $fields .
			"FROM" . $from .
			"WHERE
				ac.Person_id = :Person_id
				AND ac.vacJournalAccount_StatusType_id = 1
			ORDER BY " . $order;

        $result = $this->db->query($sql, ['Person_id' => $data['Person_id']]);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * Планируемые прививки (Старый)
     *
     * @param $data
     * @return bool|array
     */
    public function GetInocationPlanData_Old($data)
    {
        $person_id = $data['Person_id'];
        $sql = "
            select
                datePlan_Sort as \"datePlan_Sort\",
                StatusName as \"StatusName\",
                to_char(datePlan, {$this->dateTimeFormat104}) as \"datePlan\",
                Age as \"Age\",
                typeName as \"typeName\",
                vaccineTypeName as \"vaccineTypeName\",
                to_char(DateBegin, {$this->dateTimeFormat104}) as \"DateBegin\",
                to_char(DateEnd, {$this->dateTimeFormat104}) as \"DateEnd\"
            from
                vac.fn_GetPlan4PrintKard063 (:Person_id)
            where
                datediff('day', to_char(getdate(), {$this->dateTimeFormat104}), to_char(datePlan, {$this->dateTimeFormat104})) >= 0
            and
                datediff('day', to_char(getdate(), {$this->dateTimeFormat104}), to_char(datePlan, {$this->dateTimeFormat104})) <= 365
            and
                StatusName is not null
            order by to_char(datePlan, {$this->dateTimeFormat104})
        ";
        $result = $this->db->query($sql);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * Планируемые прививки
     *
     * @param $data
     * @return bool|array
     */
    function GetInocationPlanData($data)
    {
        $person_id = $data['Person_id'];
        $sql = "
            select 
                to_char(vac_PersonPlanFinal_DatePlan, {$this->dateTimeFormat112}) as datePlan_Sort,
                'Запланировано' StatusName,
                to_char(vac_PersonPlanFinal_DatePlan, {$this->dateTimeFormat104}) as datePlan,
                vac_PersonPlanFinal_Age Age,
                nc.NationalCalendarVac_typeName typeName,
                VaccineType_Name vaccineTypeName,
                to_char(vac_PersonPlanFinal_dateS, {$this->dateTimeFormat104}) as DateBegin,
                to_char(vac_PersonPlanFinal_dateE, {$this->dateTimeFormat104}) as DateEnd
            from
                vac.vac_PersonPlanFinal plf 
                left outer join  vac.v_NationalCalendarVac nc on  nc.NationalCalendarVac_Scheme_id = plf.NationalCalendarVac_Scheme_id 
                left join vac.S_VaccineType vt on  vt.VaccineType_id = plf.vaccineType_id
            where 
                plf.Person_id = {$person_id}
            and
                vac_PersonPlanFinal_DatePlan >= getDate() - interval '1 day'
            order by
                vac_PersonPlanFinal_DatePlan,
                VaccineType_Name
        ";
        $result = $this->db->query($sql);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }
	
	/**
	 * Планируемые прививки
	 */
	function GetInoculationPlanData($data)
	{
		$person_id = $data['Person_id'];
		$sql = "
			SELECT
				TO_CHAR(vac_PersonPlanFinal_DatePlan, 'YYYYMMDD') as \"datePlan_Sort\",
				'Запланировано' as \"StatusName\",
				TO_CHAR(vac_PersonPlanFinal_DatePlan, 'dd.mm.yyyy') as \"datePlan\",
				vac_PersonPlanFinal_Age as \"Age\",
				nc.NationalCalendarVac_typeName as \"typeName\",
				VaccineType_Name as \"vaccineTypeName\",
				TO_CHAR(vac_PersonPlanFinal_dateS, 'dd.mm.yyyy') as \"DateBegin\",
				TO_CHAR(vac_PersonPlanFinal_dateE, 'dd.mm.yyyy') as \"DateEnd\"
			FROM
				vac.vac_PersonPlanFinal plf 
				LEFT OUTER JOIN vac.v_NationalCalendarVac nc 
					ON nc.NationalCalendarVac_Scheme_id = plf.NationalCalendarVac_Scheme_id
				LEFT JOIN vac.S_VaccineType vt 
					ON vt.VaccineType_id = plf.vaccineType_id
			WHERE
				plf.Person_id = {$person_id}
				AND vac_PersonPlanFinal_DatePlan = cast(GETDATE() - INTERVAL '1 day' as date)
			ORDER BY
				vac_PersonPlanFinal_DatePlan, VaccineType_Name";

		$result = $this->db->query($sql);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}
}
