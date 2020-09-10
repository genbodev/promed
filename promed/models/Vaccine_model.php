<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Класс модели для работы по иммунопрофилактике
 *
 * @package		Common
 * @author		Nigmatullin Tagir (Ufa)
 *
 */

class Vaccine_model extends CI_Model {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
	 */

	public function getVaccineGridDetail_New() {
		   	echo '"Это моя модель!';
			return true;
		$query = "
			SELECT [Vaccine_id] as Vaccine_id
                              ,[GRID_NAME_VAC] as GRID_NAME_VAC
                              ,[NAME_TYPE_VAC] as NAME_TYPE_VAC
                              FROM vac.v_Vaccine vac
							 
		";
                $res = $this->db->query($query);
               
		if ( is_object($res) )
                return $res->result($res);
		else  {
                        echo 'Ошибка';
                }
                        
	 }
	/**
	 * Список прививок для грида
	*/
	public function getVaccineGridDetail() {
		$filters = array();
		$queryParams = array();
	 	$sql = "
			SELECT [Vaccine_id] as Vaccine_id
                              ,[Name] as Name
                              ,[SignComb] as SignComb
                              ,[CodeInf] as CodeInf
                              ,[NameVac] as NameVac
                              ,[GRID_NAME_VAC] as GRID_NAME_VAC
                              ,[NAME_TYPE_VAC] as NAME_TYPE_VAC
                              ,[AgeRange] as AgeRange
                              ,[AgeRange2Sim] as AgeRange2Sim
                              ,[SignWayPlace] as SignWayPlace
                              ,[WayPlace_id] as WayPlace_id
                              ,[WAY_PLACE] as WAY_PLACE
                              ,[SignDoza] as SignDoza
                              ,[Doza] as Doza
                              ,[VACCINE_DOZA] as VACCINE_DOZA
                          FROM vac.v_Vaccine vac with(nolock)
                          ".ImplodeWhere($filters); 
						  
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result($res);
		else
			return false;
			
			
	}
	/**
	 * Тип срока годности (месяцев или лет)
	*/
	public function GetVaccineTypePeriod() {

    	$query = "
			select
                            VaccineTipPeriod_id TipPeriod_id,
                            VaccineTipPeriod_name TipPeriod_name
			from vac.S_VaccineTipPeriod with(nolock)";

    	$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end GetVaccineTypePeriod
	/**
	 * Тип введения прививки
	*/
	public function GetVaccineWay() {

   		$query = "SELECT VaccineWay_id, VaccineWay_name
                FROM vac.S_VaccineWay with(nolock) order by VaccineWay_name";

    	$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end GetVaccineWay

	/**
	 * Место введения прививки
	*/
	public function GetVaccinePlace($data) {
    	$filter = "";
    if ((isset($data['VaccineWay_id'])) && ($data['VaccineWay_id']>0))
			$filter .= "where VaccineWay_id = ".$data['VaccineWay_id'];

    	$query = "SELECT VaccinePlace_id, VaccinePlace_name, VaccineWay_id
                    FROM vac.S_VaccinePlace with(nolock) order by VaccinePlace_name {$filter}";

    	$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end GetVaccinePlace
        
	/**
	 * Список прививок
	*/
	public function GetSprInoculation() {

         
    	$query = "SElect  * from (
                SELECT  VaccineType_id
                    ,VaccineType_Name
                FROM vac.S_VaccineType with(nolock)
                        where VaccineType_id < 100
                union
                SELECT  1000 VaccineType_id
                    , ' Все прививки' VaccineType_Name
                FROM vac.S_VaccineType with(nolock)) t
                order by VaccineType_name";
   
    
     	$query = "SElect  * from vac.v_Inoculation4Combo with(nolock)
                order by VaccineType_name";
    

    	$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * Реакция манту
	*/
	function GetMantuReaction($data){
		$person_id = $data['Person_id'];
		$sql = 'Select
				Person_id,
				Age,
				convert(varchar(10), dateVac, 104) as dateVac,
				Dose,
				Seria,
				convert(varchar(10), Period, 104) as Period,
				WayPlace,
				convert(varchar(10), DateReact, 104) as DateReact,
				ReactDescription
				from vac.fn_GetMantu4PrintKard063 ('.$person_id.')
				where dateVac is not null
				';

		$result = $this->db->query($sql);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	 * Исполненные прививки (Старый)
	*/
	function GetInoculationData_Old($data){
		$person_id = $data['Person_id'];
		$sql = 'Select
					Inoculation_id,
					age,
					typeName,
					convert(varchar(10), DateVac, 104) as DateVac,
					VaccineType_Name,
					Vaccine_Name,
					Dose,
					Seria,
					convert(varchar(10), Period, 104) as Period,
					WayPlace,
					ReactGeneralDescription
				from vac.fn_GetData4PrintKard063 ('.$person_id.')
				order by typeName';
		$result = $this->db->query($sql);
		if(is_object($result)){
			return $result->result('array');
		}
		else{
			return false;
		}
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
			"Inoculation_id,
			CONVERT(VARCHAR(10), vacJournalAccount_DateVac, 104) AS DateVac,
			vacJournalAccount_age age,
			Vaccine_Name,
			VaccineType_Name,
			CASE
				WHEN vacJournalAccount_Period IS NOT NULL
					THEN vacJournalAccount_Seria
				WHEN vacJournalAccount_Period IS NULL AND CHARINDEX('-', vacJournalAccount_Seria) > 0
					THEN LTRIM(RTRIM(SUBSTRING(vacJournalAccount_Seria, 1, CHARINDEX('-', vacJournalAccount_Seria) - 1)))
				ELSE NULL
			END Seria,
			vacJournalAccount_Dose Dose,
			pl.VaccinePlace_Name Place,
			CASE
				WHEN pl.VaccinePlace_Name IS NULL
					AND wp.VaccinePlace_Name IS NULL
					AND ac.VaccinePlace_id IS NOT NULL
				THEN
					wp.VaccineWay_Name
				WHEN pl.VaccinePlace_Name IS NULL
					AND ac.VaccinePlace_id IS NOT NULL
				THEN
					wp.VaccinePlace_Name + ': ' + wp.VaccineWay_Name
				ELSE
					pl.VaccinePlace_Name + ': ' + w.VaccineWay_Name
			END WayPlace,
			ac.vacJournalAccount_ReactGeneralDescription AS ReactGeneralDescription
			";

		$from = "
			vac.vac_JournalAccount ac WITH (NOLOCK)
			LEFT OUTER JOIN vac.Inoculation i WITH (NOLOCK) ON i.vacJournalAccount_id = ac.vacJournalAccount_id
			LEFT OUTER JOIN vac.S_VaccineType vt WITH (NOLOCK) ON vt.VaccineType_id = i.VaccineType_id
			LEFT OUTER JOIN vac.v_VaccinePlace pl WITH (NOLOCK) ON pl.VaccinePlace_id = ac.VaccinePlace_id
			LEFT OUTER JOIN vac.S_VaccineWay w WITH (NOLOCK) ON ac.VaccineWay_id = w.VaccineWay_id
			OUTER APPLY(
				SELECT TOP 1
					p2.VaccinePlace_Name, w2.VaccineWay_Name
				FROM vac.v_VaccineWayPlace wp2 WITH (NOLOCK)
					LEFT JOIN vac.v_VaccinePlace p2 WITH (NOLOCK) ON wp2.VaccinePlace_id = p2.VaccinePlace_id
					LEFT JOIN vac.S_VaccineWay w2 WITH (NOLOCK) ON wp2.VaccineWay_id = w2.VaccineWay_id
				WHERE ac.VaccinePlace_id = wp2.VaccineWayPlace_id
			) wp
			LEFT OUTER JOIN vac.S_Vaccine v WITH (NOLOCK) ON v.Vaccine_id = ac.vaccine_id
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
				END AS typeName
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

			$from = $from . "
				LEFT OUTER JOIN dbo.v_Lpu lpu WITH (NOLOCK) ON lpu.Lpu_id = ac.vacJournalAccount_Lpu_id
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
	}

	/**
	 * Планируемые прививки (Старый)
	*/
	function GetInoculationPlanData_Old($data){
		$person_id = $data['Person_id'];
		$sql = '	Select
						datePlan_Sort,
						StatusName,
						CONVERT(varchar(10), datePlan, 104) as datePlan,
						Age,
						typeName,
						vaccineTypeName,
						CONVERT(varchar(10), DateBegin, 104) as DateBegin,
						CONVERT(varchar(10), DateEnd, 104) as DateEnd
					from vac.fn_GetPlan4PrintKard063 ('.$person_id.')
					where DATEDIFF(DAY,CONVERT(date, GETDATE(), 104),CONVERT(date, datePlan, 104)) >= 0
					and DATEDIFF(DAY,CONVERT(date, GETDATE(), 104),CONVERT(date, datePlan, 104)) <= 365
					and StatusName is not null
					order by CONVERT(datetime, datePlan, 104)';
		$result = $this->db->query($sql);
		if(is_object($result)){
			return $result->result('array');
		}
		else{
			return false;
		}
	}
	
	/**
	 * Планируемые прививки
	 */
	function GetInoculationPlanData($data)
	{
		$person_id = $data['Person_id'];
		$sql = "
			SELECT
				CONVERT(VARCHAR, vac_PersonPlanFinal_DatePlan, 112) datePlan_Sort,
				'Запланировано' StatusName,
				CONVERT(VARCHAR(10), vac_PersonPlanFinal_DatePlan, 104) AS datePlan,
				vac_PersonPlanFinal_Age Age,
				nc.NationalCalendarVac_typeName typeName,
				VaccineType_Name vaccineTypeName,
				CONVERT(VARCHAR(10), vac_PersonPlanFinal_dateS, 104) AS DateBegin,
				CONVERT(VARCHAR(10), vac_PersonPlanFinal_dateE, 104) AS DateEnd
			FROM
				vac.vac_PersonPlanFinal plf WITH (NOLOCK)
				LEFT OUTER JOIN vac.v_NationalCalendarVac nc WITH (NOLOCK)
					ON nc.NationalCalendarVac_Scheme_id = plf.NationalCalendarVac_Scheme_id
				LEFT JOIN vac.S_VaccineType vt WITH (NOLOCK)
					ON vt.VaccineType_id = plf.vaccineType_id
			WHERE
				plf.Person_id = {$person_id}
				AND vac_PersonPlanFinal_DatePlan = CONVERT(DATE, GETDATE() - 1)
			ORDER BY
				vac_PersonPlanFinal_DatePlan, VaccineType_Name";

		$result = $this->db->query($sql);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}
}
?>
