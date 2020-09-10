<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/EvnPLDispOrp13_model.php');

class Ufa_EvnPLDispOrp13_model extends EvnPLDispOrp13_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает данные о согласии на диспансеризацию
	 */
	function loadDopDispInfoConsent($data)
	{
		$filterList = array();
		$joinList = array();
		$params = array(
			'DispClass_id' => $data['DispClass_id'],
			'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
			'EvnPLDispOrp_setDate' => (!empty($data['EvnPLDispOrp_setDate']) ? $data['EvnPLDispOrp_setDate'] : "getdate()"),
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
		);

		// Получаем список возможных уровней МО
		$query = "
			select
				t2.LpuLevel_Code as \"LpuLevel_Code\",
				1 as \"order\"
			from v_Lpu t1
				inner join v_LpuLevel t2 on t2.LpuLevel_id = t1.LpuLevel_id
			where t1.Lpu_id = :Lpu_id

			union all

			select distinct
				t2.LpuLevel_Code as \"LpuLevel_Code\",
				2 as \"order\"
			from v_LpuBuilding t1
				inner join v_LpuLevel t2 on t2.LpuLevel_id = t1.LpuLevel_id
				inner join v_Lpu t3 on t3.Lpu_id = t1.Lpu_id
			where t1.Lpu_id = :Lpu_id
				and t1.LpuLevel_id != t3.LpuLevel_id

			order by \"order\"
		";
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

		$lpuLevelList = $result->result('array');
		$response = array();
		$surveyTypeList = array();

		foreach ($lpuLevelList as $lpuLevel) {
			$params['LpuLevel_Code'] = $lpuLevel['LpuLevel_Code'];

			$query = "
				with person_info as (
					select
						COALESCE(Sex_id, 3) as person_sex_id,
						dbo.Age2(Person_BirthDay, COALESCE(cast(:EvnPLDispOrp_setDate as timestamp), dbo.tzGetDate())) as person_age
					from v_PersonState ps
					where ps.Person_id = :Person_id
					limit 1
				)
				select distinct
					COALESCE(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as \"DopDispInfoConsent_id,
					MAX(DDIC.EvnPLDisp_id) as \"EvnPLDispOrp_id\",
					MAX(UC.UslugaComplex_Code) as \"UslugaComplex_Code\",
					MAX(ODS.OrpDispSpec_Code) as \"OrpDispSpec_Code\",
					MAX(STL.SurveyTypeLink_id) as \"SurveyTypeLink_id\",
					COALESCE(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) as \"SurveyTypeLink_IsNeedUsluga\",
					MAX(ST.SurveyType_Code) as \"SurveyType_Code\",
					MAX(ST.SurveyType_Name) as \"SurveyType_Name\",
					case WHEN :EvnPLDispOrp_id IS NULL OR MAX(DDIC.DopDispInfoConsent_IsAgree) = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\", -- для новой карты проставляем чекбоксы
					case WHEN MAX(DDIC.DopDispInfoConsent_IsEarlier) = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\"
				from v_SurveyTypeLink STL, person_info t
					left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispOrp_id
					left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					left join v_OrpDispSpec ODS on ODS.OrpDispSpec_id = ST.OrpDispSpec_id
					left join lateral (
						select EvnUslugaDispDop_id
						from v_EvnUslugaDispDop
						where UslugaComplex_id = UC.UslugaComplex_id
							and EvnUslugaDispDop_rid = :EvnPLDispOrp_id
						limit 1
					) EUDD on true
					left join v_UslugaCategory ucat on ucat.UslugaCategory_id = UC.UslugaCategory_id
				where 
					COALESCE(STL.DispClass_id, 3) = :DispClass_id -- дети-сироты, 1 этап
					and (COALESCE(STL.Sex_id, t.person_sex_id) = t.person_sex_id) -- по полу
					and (t.person_age between COALESCE(SurveyTypeLink_From, 0) and  COALESCE(SurveyTypeLink_To, 999)) -- по возрасту, в принципе по библии Иссак лет 800 жил же
					and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
					and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispOrp_setDate)
					and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispOrp_setDate)
					and (
						COALESCE(ucat.UslugaCategory_SysNick, '') != 'lpusection'
						or (
							case
								when :LpuLevel_Code in (2,6) then '6'
								when :LpuLevel_Code in (3,5) then '5'
								when :LpuLevel_Code in (1,8) then '8'
								else null
							end = left(UC.UslugaComplex_Code, 1)
						)
					)
					and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)
					" . (count($surveyTypeList) > 0 ? "and ST.SurveyType_Code not in (" . implode(',', $surveyTypeList) . ")" : "") . "
				group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
				order by MAX(ST.SurveyType_Code)
			";
			//echo getDebugSql($query, $params);die();
			$result = $this->db->query($query, $params);

			if (!is_object($result)) {
				return false;
			}

			$queryResponse = $result->result('array');

			foreach ($queryResponse as $row) {
				$surveyTypeList[] = $row['SurveyType_Code'];
				$reponse[] = $row;
			}
		}

		return $reponse;
	}
}
