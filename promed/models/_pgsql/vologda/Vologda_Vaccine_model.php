<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/Vaccine_model.php');

class Vologda_Vaccine_model extends Vaccine_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * #182475 Для раздела "Манту/Диаскинтест" в сигнальной информации ЭМК.
	 */
	function GetMantuReaction($data)
	{
		$person_id = $data['Person_id'];

		$sql = "
			SELECT
				JournalMantu_id as \"JournalMantu_id\",
				to_char(JournalMantu_DateVac, 'dd.mm.yyyy') as \"dateVac\",
				CASE
					WHEN JournalMantu_StatusType_id = 0 THEN 'Назначено'
					WHEN JournalMantu_StatusType_id = 1 THEN 'Исполнено'
					WHEN JournalMantu_StatusType_id = 2 THEN 'Запланировано'
				END \"Status_Name\",
				TubDiagnosisType_Name as \"TubDiagnosisType_Name\",
				MantuReactionType_name as \"MantuReactionType_name\",
				JournalMantu_ReactDescription as \"ReactDescription\",
				JournalMantu_ReactionSize as \"ReactionSize\",
				Lpu_Name as \"Lpu_Name\"
			FROM
				vac.vac_JournalMantu m
				INNER JOIN vac.S_InoculationStatusType st ON m.JournalMantu_StatusType_id = st.StatusType_id
				LEFT OUTER JOIN vac.S_TubDiagnosisType AS d ON d.TubDiagnosisType_id = m.TubDiagnosisType_id
				LEFT OUTER JOIN vac.S_MantuReactionType AS mReact ON mReact.MantuReactionType_id = m.MantuReactionType_id
				LEFT OUTER JOIN dbo.v_Lpu AS lpu ON lpu.Lpu_id = m.JournalMantu_Lpu_id
			WHERE
				Person_id = {$person_id}
				AND JournalMantu_DateVac IS NOT NULL
			ORDER BY
				JournalMantu_id
			";

	    $result = $this->db->query($sql);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
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
				WHEN vacJournalAccount_Period IS NULL AND POSITION('-' in vacJournalAccount_Seria) > 0
					THEN LTRIM(RTRIM(SUBSTRING(vacJournalAccount_Seria, 1, POSITION('-' in vacJournalAccount_Seria) - 1)))
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
				SELECT
					p2.VaccinePlace_Name, w2.VaccineWay_Name
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
	}
}
?>