<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnSection
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Valery Bondarev
 * @version			kaluga
 */

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

class Kaluga_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF2018($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionBedProfile = false;

		if ($data['EvnSection_IsPriem'] == 2) {
			return array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
		}

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$data['TariffClass_SysNick'] = 'tarksg/kpgks';
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['TariffClass_SysNick'] = 'tarksg/kpgds';
		}

		$data['TariffClass_id'] = $this->getFirstResultFromQuery("select TariffClass_id as \"TariffClass_id\" from v_TariffClass where TariffClass_SysNick = :TariffClass_SysNick limit 1", array(
			'TariffClass_SysNick' => $data['TariffClass_SysNick']
		), true);

		$crossapplymesvol = "
			inner join lateral (
				SELECT
					av.AttributeValue_id
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a on a.Attribute_id = av.Attribute_id
					inner join lateral(
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and COALESCE(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
						limit 1
					) MOFILTER on true
					inner join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesOld'
							and av2.AttributeValue_ValueIdent = mo.Mes_id -- КСГ или КПГ должен быть указан
						limit 1
					) KSGFILTER on true
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = :TariffClass_id
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				limit 1
			) MESVOL on true
		";

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select
					pt.PolyTrauma_Code as \"PolyTrauma_Code\",
					SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_PolyTrauma pt
					left join lateral(
						select
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2
							inner join v_PolyTrauma pt2 on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id and
					exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
						limit 1
					)
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select
								mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\"
							from
								v_MesOld mo
								{$crossapplymesvol}
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '233'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							limit 1
						";

						$result = $this->db->query($query, $data);
						if (is_object($result)) {
							$resp = $result->result('array');
							if (count($resp) > 0) {
								$KSGFromPolyTrauma = $resp[0];
							}
						}
					}
				}
			}
		}

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		$data['EvnUsluga_IVLHours'] = $this->getEvnUslugaIVLHours($data);
		$DrugTherapySchemeFilter = "and mu.DrugTherapyScheme_id IS NULL";
		if (!empty($data['DrugTherapyScheme_ids'])) {
			$DrugTherapySchemeFilter = "and (mu.DrugTherapyScheme_id IN ('" . implode("','", $data['DrugTherapyScheme_ids']) . "') OR mu.DrugTherapyScheme_id IS NULL)";
		}

		$data['UslugaComplexIds'] = $this->getUslugaComplexIds($data);
		$UslugaComplexFilters = "
			and mu.UslugaComplex_aid is null
			and mu.UslugaComplex_bid is null
		";
		if (!empty($data['UslugaComplexIds'])) {
			$UslugaComplexFilters = "
				and (mu.UslugaComplex_aid IS NULL or mu.UslugaComplex_aid IN ('" . implode("','", $data['UslugaComplexIds']) . "'))
				and (mu.UslugaComplex_bid IS NULL or mu.UslugaComplex_bid IN ('" . implode("','", $data['UslugaComplexIds']) . "'))
			";
		}

		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select 
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					{$DrugTherapySchemeFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end
					+ case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
					+ case when mu.RehabScale_id is not null then 1 else 0 end
					+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end -- считаются как 1 критерий
					+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOper = $resp[0];
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					and mu.UslugaComplex_id is null
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					{$DrugTherapySchemeFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end
					+ case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
					+ case when mu.RehabScale_id is not null then 1 else 0 end
					+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end -- считаются как 1 критерий
					+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGTerr = $resp[0];
				}
			}
		}

		// 3 Пробуем определить КПГ по профилю койки
		if (!empty($data['LpuSectionBedProfile_id'])) {
			$stacKod = '0';
			if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
				$stacKod = '1';
			}
			$query = "
				select
					mo.Mes_Code as \"KPG\",
					mo.Mes_id as \"Mes_id\",
					mtkpg.MesTariff_Value as \"KOEF\",
					mtkpg.MesTariff_id as \"MesTariff_id\"
				from
					v_LpuSectionBedProfile lsbp
					inner join MesOld mo on mo.Mes_Code = '169'||right('0000'||cast(lsbp.LpuSectionBedProfile_Code as varchar),3)||'{$stacKod}' and mo.MesType_id IN (4) -- КПГ
					{$crossapplymesvol}
					left join v_MesTariff mtkpg on mtkpg.Mes_id = mo.Mes_id and mtkpg.MesPayType_id = 5 -- Коэффициент КПГ
				where
					lsbp.LpuSectionBedProfile_id = :LpuSectionBedProfile_id
					and mo.Mes_begDT <= :EvnSection_setDate
					and (mo.Mes_endDT >= :EvnSection_setDate OR mo.Mes_endDT IS NULL)
				order by
					mtkpg.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KPGFromLpuSectionBedProfile = $resp[0];
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionBedProfile) {
			$response['KPG'] = $KPGFromLpuSectionBedProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionBedProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					cast(COALESCE(MesLink_endDT, :EvnSection_disDate) as date) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
				$response['KSG'] = $KSGOper['KSG'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
		} else if ($KPGFromLpuSectionBedProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionBedProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionBedProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF2016($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionBedProfile = false;

		if ($data['EvnSection_IsPriem'] == 2) {
			return array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
		}

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEF2018($data);
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$data['TariffClass_SysNick'] = 'tarksg/kpgks';
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['TariffClass_SysNick'] = 'tarksg/kpgds';
		}

		$data['TariffClass_id'] = $this->getFirstResultFromQuery("select TariffClass_id as \"TariffClass_id\" from v_TariffClass where TariffClass_SysNick = :TariffClass_SysNick limit 1", array(
			'TariffClass_SysNick' => $data['TariffClass_SysNick']
		), true);

		$crossapplymesvol = "
			inner join lateral (
				SELECT
					av.AttributeValue_id
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a on a.Attribute_id = av.Attribute_id
					inner join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and COALESCE(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
						limit 1
					) MOFILTER on true
					inner join lateral(
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesOld'
							and av2.AttributeValue_ValueIdent = mo.Mes_id -- КСГ или КПГ должен быть указан
						limit 1
					) KSGFILTER on true
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = :TariffClass_id
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				limit 1
			) MESVOL on true
		";

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select
					pt.PolyTrauma_Code as \"PolyTrauma_Code\",
					SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_PolyTrauma pt
					left join lateral(
						select
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2
							inner join v_PolyTrauma pt2 on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id and
					exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
						limit 1
					)
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select
								mo.Mes_Code + COALESCE('. ' + mo.Mes_Name, '') as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\"
							from
								v_MesOld mo
								{$crossapplymesvol}
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '216'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							limit 1
						";

						$result = $this->db->query($query, $data);
						if (is_object($result)) {
							$resp = $result->result('array');
							if (count($resp) > 0) {
								$KSGFromPolyTrauma = $resp[0];
							}
						}
					}
				}
			}
		}

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOper = $resp[0];
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					and mu.UslugaComplex_id is null
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGTerr = $resp[0];
				}
			}
		}

		// 3 Пробуем определить КПГ по профилю койки
		if (!empty($data['LpuSectionBedProfile_id'])) {
			$stacKod = '0';
			if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
				$stacKod = '1';
			}
			$query = "
				select
					mo.Mes_Code as \"KPG\",
					mo.Mes_id as \"Mes_id\",
					mtkpg.MesTariff_Value as \"KOEF\",
					mtkpg.MesTariff_id as \"MesTariff_id\"
				from
					v_LpuSectionBedProfile lsbp
					inner join MesOld mo on mo.Mes_Code = '169'||right('0000'||cast(lsbp.LpuSectionBedProfile_Code as varchar),3)||'{$stacKod}' and mo.MesType_id IN (4) -- КПГ
					{$crossapplymesvol}
					left join v_MesTariff mtkpg on mtkpg.Mes_id = mo.Mes_id and mtkpg.MesPayType_id = 5 -- Коэффициент КПГ
				where
					lsbp.LpuSectionBedProfile_id = :LpuSectionBedProfile_id
					and mo.Mes_begDT <= :EvnSection_setDate
					and (mo.Mes_endDT >= :EvnSection_setDate OR mo.Mes_endDT IS NULL)
				order by
					mtkpg.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KPGFromLpuSectionBedProfile = $resp[0];
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionBedProfile) {
			$response['KPG'] = $KPGFromLpuSectionBedProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionBedProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					cast(COALESCE(MesLink_endDT, :EvnSection_disDate) as date) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
				$response['KSG'] = $KSGOper['KSG'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
		} else if ($KPGFromLpuSectionBedProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionBedProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionBedProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionBedProfile = false;

		$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1");
		if (empty($data['PayTypeOms_id'])) {
			return array('Error_Msg' => 'Ошибка получения идентификатора вида оплаты ОМС');
		}

		if ($data['EvnSection_IsPriem'] == 2) {
			return array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
		}

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEF2018($data);
		} elseif (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEF2016($data);
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select
					pt.PolyTrauma_Code as \"PolyTrauma_Code\",
					SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_PolyTrauma pt 
					left join lateral(
						select 
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2
							inner join v_PolyTrauma pt2 on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id and
					exists(
						select 
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
						limit 1
					)
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select
								mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\"
							from
								v_MesOld mo 
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '192'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							limit 1
						";

						$result = $this->db->query($query, $data);
						if (is_object($result)) {
							$resp = $result->result('array');
							if (count($resp) > 0) {
								$KSGFromPolyTrauma = $resp[0];
							}
						}
					}
				}
			}
		}

		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOper = $resp[0];
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code || COALESCE('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					and mu.UslugaComplex_id is null
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGTerr = $resp[0];
				}
			}
		}

		// 3 Пробуем определить КПГ по профилю койки
		if (!empty($data['LpuSectionBedProfile_id'])) {
			$query = "
				select
					mokpg.Mes_Code as \"KPG\",
					mokpg.Mes_id as \"Mes_id\",
					mtkpg.MesTariff_Value as \"KOEF\",
					mtkpg.MesTariff_id as \"MesTariff_id\"
				from
					v_LpuSectionBedProfile lsbp
					inner join MesOld mokpg on mokpg.Mes_Code = '159'||right('0000'||cast(lsbp.LpuSectionBedProfile_Code as varchar),3) and mokpg.MesType_id IN (4) -- КПГ
					left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id and mtkpg.MesPayType_id = 5 -- Коэффициент КПГ
				where
					lsbp.LpuSectionBedProfile_id = :LpuSectionBedProfile_id
					and mokpg.Mes_begDT <= :EvnSection_setDate
					and (mokpg.Mes_endDT >= :EvnSection_setDate OR mokpg.Mes_endDT IS NULL)
				order by
					mtkpg.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KPGFromLpuSectionBedProfile = $resp[0];
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionBedProfile) {
			$response['KPG'] = $KPGFromLpuSectionBedProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionBedProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					COALESCE(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
				$response['KSG'] = $KSGOper['KSG'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
		} else if ($KPGFromLpuSectionBedProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionBedProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionBedProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		return $response;
	}
}
