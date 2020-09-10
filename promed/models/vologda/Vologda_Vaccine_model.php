<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/Vaccine_model.php');

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
				JournalMantu_id,
				convert(varchar(10), JournalMantu_DateVac, 104) as dateVac,
				CASE
					WHEN JournalMantu_StatusType_id = 0 THEN 'Назначено'
					WHEN JournalMantu_StatusType_id = 1 THEN 'Исполнено'
					WHEN JournalMantu_StatusType_id = 2 THEN 'Запланировано'
				END Status_Name,
				TubDiagnosisType_Name,
				MantuReactionType_name,
				JournalMantu_ReactDescription ReactDescription,
				JournalMantu_ReactionSize ReactionSize,
				Lpu_Name
			FROM
				vac.vac_JournalMantu m WITH (NOLOCK)
				INNER JOIN vac.S_InoculationStatusType st ON m.JournalMantu_StatusType_id = st.StatusType_id
				LEFT OUTER JOIN vac.S_TubDiagnosisType AS d WITH (NOLOCK) ON d.TubDiagnosisType_id = m.TubDiagnosisType_id
				LEFT OUTER JOIN vac.S_MantuReactionType AS mReact WITH (NOLOCK) ON mReact.MantuReactionType_id = m.MantuReactionType_id
				LEFT OUTER JOIN dbo.v_Lpu AS lpu WITH (NOLOCK) ON lpu.Lpu_id = m.JournalMantu_Lpu_id
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
}
?>