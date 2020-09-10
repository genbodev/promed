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
 * @version			buryatiya
 */

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

class Buryatiya_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF($data) {
		// 1. загружаем комбо
		$ksgdata = array('Mes_id' => '', 'Mes_Code' => '', 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
		$combovalues = $this->loadKSGKPGKOEFCombo($data);

		// если не задан, берём с движения
		if (empty($data['MesTariff_id']) && !empty($data['EvnSection_id'])) {
			$evnsection = $this->getFirstRowFromQuery("select MesTariff_id,Diag_id from v_EvnSection where EvnSection_id = :EvnSection_id", $data);
			$data['MesTariff_id'] = !empty($evnsection['MesTariff_id']) ? $evnsection['MesTariff_id'] : null;
		}

		$defaultValue = null;
		$selectedValue = null;
		foreach($combovalues as $combovalue) {
			// если тариф задан и совпадает со значением
			if (!empty($data['MesTariff_id']) && $combovalue['MesTariff_id'] == $data['MesTariff_id']) {
				$selectedValue = $combovalue;
			}

			if (!empty($combovalue['Mes_IsDefault']) && $combovalue['Mes_IsDefault'] == 2) {
				$defaultValue = $combovalue;
			}
		}

		if (!empty($selectedValue)) {
			$ksgdata['MesTariff_id'] = $selectedValue['MesTariff_id'];
			$ksgdata['MesOldUslugaComplex_id'] = !empty($selectedValue['MesOldUslugaComplex_id']) ? $selectedValue['MesOldUslugaComplex_id'] : null;
			$ksgdata['Mes_tid'] = $selectedValue['Mes_tid'];
			$ksgdata['Mes_sid'] = $selectedValue['Mes_sid'];
			$ksgdata['Mes_kid'] = $selectedValue['Mes_kid'];
			$ksgdata['MesTariff_id'] = $selectedValue['MesTariff_id'];
			$ksgdata['MesTariff_sid'] = $selectedValue['MesTariff_sid'] ?? null;
			$ksgdata['MesTariff_Value'] = $selectedValue['MesTariff_Value'];
			$ksgdata['MesTariff_sValue'] = $selectedValue['MesTariff_sValue'] ?? null;
			$ksgdata['Mes_id'] = $selectedValue['Mes_id'];
			$ksgdata['Mes_Code'] = isset($selectedValue['Mes_Code'])?$selectedValue['Mes_Code']:null;
			if (!empty($selectedValue['KSGArray'])) {
				$ksgdata['KSGArray'] = $selectedValue['KSGArray'];
			}
			if (isset($selectedValue['coeffCTPList'])) {
				$ksgdata['coeffCTPList'] = $selectedValue['coeffCTPList'];
			}
			if (isset($selectedValue['EvnSection_CoeffCTP'])) {
				$ksgdata['EvnSection_CoeffCTP'] = $selectedValue['EvnSection_CoeffCTP'];
			}
		} else if (!empty($defaultValue)) {
			$ksgdata['MesTariff_id'] = $defaultValue['MesTariff_id'];
			$ksgdata['MesOldUslugaComplex_id'] = !empty($defaultValue['MesOldUslugaComplex_id']) ? $defaultValue['MesOldUslugaComplex_id'] : null;
			$ksgdata['Mes_tid'] = $defaultValue['Mes_tid'];
			$ksgdata['Mes_sid'] = $defaultValue['Mes_sid'];
			$ksgdata['Mes_kid'] = $defaultValue['Mes_kid'];
			$ksgdata['MesTariff_id'] = $defaultValue['MesTariff_id'];
			$ksgdata['MesTariff_sid'] = $defaultValue['MesTariff_sid'] ?? null;
			$ksgdata['MesTariff_Value'] = $defaultValue['MesTariff_Value'];
			$ksgdata['MesTariff_sValue'] = $defaultValue['MesTariff_sValue'] ?? null;
			$ksgdata['Mes_id'] = $defaultValue['Mes_id'];
			$ksgdata['Mes_Code'] = isset($defaultValue['Mes_Code'])?$defaultValue['Mes_Code']:null;
			if (!empty($defaultValue['KSGArray'])) {
				$ksgdata['KSGArray'] = $defaultValue['KSGArray'];
			}
			if (isset($defaultValue['coeffCTPList'])) {
				$ksgdata['coeffCTPList'] = $defaultValue['coeffCTPList'];
			}
			if (isset($defaultValue['EvnSection_CoeffCTP'])) {
				$ksgdata['EvnSection_CoeffCTP'] = $defaultValue['EvnSection_CoeffCTP'];
			}
		}

		return $ksgdata;
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	function loadKSGKPGKOEFCombo2019($data) {
		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Code' => '', 'success' => true);

		$EvnSectionIds = array();
		if (!empty($data['Diag_id'])) {
			$resp_group = $this->getEvnSectionGroup(array(
				'EvnSection_pid' => $data['EvnSection_pid'],
				'EvnSection_id' => $data['EvnSection_id'],
				'Diag_id' => $data['Diag_id']
			));
			foreach ($resp_group as $es) {
				if (!in_array($es['EvnSection_id'], $EvnSectionIds)) {
					$EvnSectionIds[] = $es['EvnSection_id'];
				}
			}
		}

		if (!empty($data['EvnSection_id']) && !in_array($data['EvnSection_id'], $EvnSectionIds)) {
			// если текущее движение не входит в группу, значит оно не должно группироваться.
			$EvnSectionIds = array();
			$EvnSectionIds[] = $data['EvnSection_id'];
		}

		$data['EvnSectionIds'] = $EvnSectionIds;

		return $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
	}

	/**
	 * поиск ксг/кпг/коэф для 2018 года
	 */
	function loadKSGKPGKOEFCombo2018($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEFCombo2019($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT
				es.EvnSection_id as \"EvnSection_id\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
			FROM
				v_EvnSection es
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				COALESCE(es.EvnSection_disDate, es.EvnSection_setDate) desc
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
				if (substr($data['LastEvnSection_disDate'], 0, 4) >= '2019') {
					return $this->loadKSGKPGKOEFCombo2019($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				case
					when EXTRACT(MONTH FROM PS.Person_BirthDay) = EXTRACT(MONTH FROM :EvnSection_setDate::date) and EXTRACT(DAY FROM PS.Person_BirthDay) = EXTRACT(DAY FROM :EvnSection_setDate::date) then 1 else 0
				end as BirthToday
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
				$data['BirthToday'] = $resp[0]['BirthToday'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$MesAgeGroup11Filter = "or (:Person_Age < 2 and mu.MesAgeGroup_id = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
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
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (COALESCE(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (COALESCE(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
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
								mo.Mes_Code as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mokpg.KPG as \"KPG\",
								mokpg.Mes_kid as \"Mes_kid\"
							from
								v_MesOld mo 
								left join v_MesTariff mt  on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								left join lateral(
									select 
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								) mokpg on true
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

		// Достаём список КСГ по реабилитации
		$MesReabilIds = array();
		// Если в движение указан профиль "158. медицинской реабилитации"
		if (!empty($data['LpuSectionProfile_id'])) {
			$resp_lsp = $this->queryResult("
				select
					LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
				from
					v_LpuSectionProfile
				where
					LpuSectionProfile_id = :LpuSectionProfile_id
			", array(
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
			));
			if (!empty($resp_lsp[0]['LpuSectionProfile_Code']) && $resp_lsp[0]['LpuSectionProfile_Code'] == '158') {
				$resp_reabil = $this->queryResult("
					select distinct
						ml.Mes_id as \"Mes_id\"
					from
						v_MesOld mo2 
						inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
					where
						mo2.Mes_Code = '37'
						and mo2.Mes_begDT <= :EvnSection_disDate
						and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				foreach($resp_reabil as $one_reabil) {
					$MesReabilIds[] = $one_reabil['Mes_id'];
				}
			}
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\",
					mo.MesType_id as \"MesType_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join lateral (
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true 
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
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
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
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
					eu.EvnUsluga_id,
					".(!empty($MesReabilIds)?"case when mo.Mes_id IN ('".implode("','", $MesReabilIds)."') then 1 else 0 end desc,":"")."
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end
					+ case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
					+ case when mu.RehabScale_id is not null then 1 else 0 end
					+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end -- считаются как 1 критерий
					+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF'] || in_array($KSGOperOne['Mes_id'], $MesReabilIds)) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// Если для отделения МО указан объём «Отделение с определением КСГ по услуге», т.е. если код отделения равен значению атрибута «код отделения» объёма, для таких случаев лечения данного отделения КСГ определяется по услуге.
		$ignoreDiagKsg = false;
		if (!empty($data['LpuSection_id'])) {
			$resp_vol = $this->queryResult("
				
				SELECT
					av.AttributeValue_id as \"AttributeValue_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_LpuSection ls on ls.Lpu_id = :Lpu_id and ls.LpuSection_Code = av.AttributeValue_ValueString
					inner join lateral (
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
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and ls.LpuSection_id = :LpuSection_id
					and avis.AttributeVision_TablePKey = (select VolumeType_id from v_VolumeType where VolumeType_Code = '1' limit 1)
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate	
				limit 1		
			", array(
				'Lpu_id' => $data['Lpu_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			if (!empty($resp_vol[0]['AttributeValue_id'])) {
				$ignoreDiagKsg = true;
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if ((empty($KSGOper) || !$ignoreDiagKsg) && !empty($data['Diag_id'])) {
			$query = "
				select 
					d.Diag_Code as \"Diag_Code\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
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
					".(!empty($MesReabilIds)?"case when mo.Mes_id IN ('".implode("','", $MesReabilIds)."') then 1 else 0 end desc,":"")."
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
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
					if (empty($KSGTerr) || $resp[0]['KOEF'] > $KSGTerr['KOEF']) {
						$KSGTerr = $resp[0];
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Определяем профиль отеделния, если '1035','2035','3035' и есть профильные койки до берем профиль с последней койки
			if (!empty($data['EvnSection_id'])) {
				/* Интересное условие, но не нужное
				if (empty($data['LpuSectionProfile_id'])) {
					$data['LpuSectionProfile_id'] = null;
				}*/
				$filterProfile = "lsp.LpuSectionProfile_id = :LpuSectionProfile_id";
				if (empty($data['LpuSectionProfile_id'])) {
					$filterProfile = "lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id";
				}
				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					SELECT
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as \"LpuSectionProfile_id\"
					FROM
						v_EvnSection es
						left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp on ".$filterProfile."
						left join lateral (
							select
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb
								inner join v_LpuSection esnbls on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
							limit 1
						) ESNBLSP on true
					WHERE
						es.EvnSection_id = :EvnSection_id
					limit 1
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select 
						mokpg.Mes_Code as \"KPG\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (COALESCE(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (COALESCE(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and COALESCE(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mtkpg.MesTariff_Value desc
					limit 1
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
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
			if (
				$KSGOper['KOEF'] > $KSGTerr['KOEF']
				|| !empty($data['MesLink_id'])
				|| in_array($KSGOper['Mes_id'], $MesReabilIds)
				|| (
					in_array($KSGOper['KSG'], array('6', '7'))
					&& in_array($data['LpuUnitType_id'], array('6', '7', '9'))
				)
			) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['KSG'] = $KSGOper['KSG'];
				$response['KPG'] = $KSGOper['KPG'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KPG'] = $KSGTerr['KPG'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['KPG'] = $KSGOper['KPG'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['KPG'] = $KSGTerr['KPG'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KPG'] = $KSGFromPolyTrauma['KPG'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		$combovalues = [];
		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}
		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф для 2017 года
	 */
	function loadKSGKPGKOEFCombo2017($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEFCombo2018($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT 
				es.EvnSection_id as \"EvnSection_id\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
			FROM
				v_EvnSection es
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				COALESCE(es.EvnSection_disDate, es.EvnSection_setDate) desc
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
				if (substr($data['LastEvnSection_disDate'], 0, 4) >= '2018') {
					return $this->loadKSGKPGKOEFCombo2018($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, cast(:EvnSection_setDate as date)) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				case
					when EXTRACT(MONTH FROM PS.Person_BirthDay) = EXTRACT(MONTH FROM :EvnSection_setDate::date) and EXTRACT(DAY FROM PS.Person_BirthDay) = EXTRACT(DAY FROM :EvnSection_setDate::date) then 1 else 0
				end as BirthToday
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
				$data['BirthToday'] = $resp[0]['BirthToday'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$MesAgeGroup11Filter = "or (:Person_Age < 2 and mu.MesAgeGroup_id = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
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
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (COALESCE(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (COALESCE(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select 
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
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
								mo.Mes_Code as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mokpg.KPG as \"KPG\",
								mokpg.Mes_kid as \"Mes_kid\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								left join lateral (
									select
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								) mokpg on true
							where
								mo.Mes_Code = '220'
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\",
					mo.MesType_id as \"MesType_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join lateral(
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
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
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					eu.EvnUsluga_id,
					case when mo.Mes_Code IN ('300','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315') then 1 else 0 end desc,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF'] || in_array($KSGOperOne['Mes_Code'], array('300','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315'))) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_MesOldUslugaComplex mu 
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral(
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mo.Mes_Code IN ('300','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315') then 1 else 0 end desc,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (empty($KSGTerr) || $resp[0]['KOEF'] > $KSGTerr['KOEF']) {
						$KSGTerr = $resp[0];
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Определяем профиль отеделния, если '1035','2035','3035' и есть профильные койки до берем профиль с последней койки
			if (!empty($data['EvnSection_id'])) {
				/* Интересное условие, но не нужное
				if (empty($data['LpuSectionProfile_id'])) {
					$data['LpuSectionProfile_id'] = null;
				}*/
				$filterProfile = "lsp.LpuSectionProfile_id = :LpuSectionProfile_id";
				if (empty($data['LpuSectionProfile_id'])) {
					$filterProfile = "lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id";
				}
				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					SELECT
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as \"LpuSectionProfile_id\"
					FROM
						v_EvnSection es 
						left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp on ".$filterProfile."
						left join lateral (
							select 
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb
								inner join v_LpuSection esnbls on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
							limit 1
						) ESNBLSP on true
					WHERE
						es.EvnSection_id = :EvnSection_id
					limit 1
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select
						mokpg.Mes_Code as \"KPG\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (COALESCE(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (COALESCE(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and COALESCE(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mtkpg.MesTariff_Value desc
					limit 1
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
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
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id']) || in_array($KSGOper['Mes_Code'], array('300','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315'))) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['KSG'] = $KSGOper['KSG'];
				$response['KPG'] = $KSGOper['KPG'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KPG'] = $KSGTerr['KPG'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['KPG'] = $KSGOper['KPG'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['KPG'] = $KSGTerr['KPG'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KPG'] = $KSGFromPolyTrauma['KPG'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		$combovalues = [];
		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}
		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф для 2016 года
	 */
	function loadKSGKPGKOEFCombo2016($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEFCombo2017($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT 
				es.EvnSection_id as \"EvnSection_id\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
			FROM
				v_EvnSection es
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate desc
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
				if (substr($data['LastEvnSection_disDate'], 0, 4) >= '2017') {
					return $this->loadKSGKPGKOEFCombo2017($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
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
							inner join v_PolyTrauma pt2  on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (COALESCE(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (COALESCE(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select 
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
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
								mo.Mes_Code as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mokpg.KPG as \"KPG\",
								mokpg.Mes_kid as \"Mes_kid\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								left join lateral(
									select 
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								) mokpg on true
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\",
					mo.MesType_id as \"MesType_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join lateral (
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
					left join v_MesTariff mt  on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
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
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select 
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
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
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (empty($KSGTerr) || $resp[0]['KOEF'] > $KSGTerr['KOEF']) {
						$KSGTerr = $resp[0];
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Определяем профиль отеделния, если '1035','2035','3035' и есть профильные койки до берем профиль с последней койки
			if (!empty($data['EvnSection_id'])) {
				/* Интересное условие, но не нужное
				if (empty($data['LpuSectionProfile_id'])) {
					$data['LpuSectionProfile_id'] = null;
				}*/
				$filterProfile = "lsp.LpuSectionProfile_id = :LpuSectionProfile_id";
				if (empty($data['LpuSectionProfile_id'])) {
					$filterProfile = "lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id";
				}
				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					SELECT
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as \"LpuSectionProfile_id\"
					FROM
						v_EvnSection es
						left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp on ".$filterProfile."
						left join lateral (
							select
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb
								inner join v_LpuSection esnbls on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
							limit 1
						) ESNBLSP on true
					WHERE
						es.EvnSection_id = :EvnSection_id
					limit 1
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select
						mokpg.Mes_Code as \"KPG\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (COALESCE(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (COALESCE(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and COALESCE(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mtkpg.MesTariff_Value desc
					limit 1
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
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
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['KSG'] = $KSGOper['KSG'];
				$response['KPG'] = $KSGOper['KPG'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KPG'] = $KSGTerr['KPG'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['KPG'] = $KSGOper['KPG'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['KPG'] = $KSGTerr['KPG'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KPG'] = $KSGFromPolyTrauma['KPG'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		$combovalues = [];
		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}
		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEFCombo($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

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

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEFCombo2019($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEFCombo2018($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEFCombo2017($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEFCombo2016($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT
				es.EvnSection_id as \"EvnSection_id\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
			FROM
				v_EvnSection es
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate desc
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
				if (substr($data['LastEvnSection_disDate'], 0, 4) >= '2016') {
					return $this->loadKSGKPGKOEFCombo2016($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
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
					left join lateral (
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
								mo.Mes_Code as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mokpg.KPG as \"KPG\",
								mokpg.Mes_kid as \"Mes_kid\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								left join lateral (
									select 
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								) mokpg on true
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
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join lateral (
						select 
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
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
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (count($resp) > 0) {
						$KSGOperArray = $resp;
						// ищем максимальную КСГ среди разных услуг.
						foreach ($KSGOperArray as $KSGOperOne) {
							if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
								$CurUsluga = $KSGOperOne['EvnUsluga_id'];
								if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF']) {
									$KSGOper = $KSGOperOne;
								}
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral(
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (COALESCE(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mu.Diag_id = :Diag_id
					and mu.UslugaComplex_id is null
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
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

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select
						mokpg.Mes_Code as \"KPG\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (COALESCE(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (COALESCE(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					order by
						mtkpg.MesTariff_Value desc
					limit 1
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
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
				$response['KPG'] = $KSGOper['KPG'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KPG'] = $KSGTerr['KPG'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['KPG'] = $KSGOper['KPG'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['KPG'] = $KSGTerr['KPG'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KPG'] = $KSGFromPolyTrauma['KPG'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		$combovalues = [];
		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}
		return $combovalues;
	}

	/**
	 * Пересчёт КСГ в связанных движениях, после сохранения движения
	 */
	protected function _recalcOtherKSG() {
		$year = date('Y');
		if (!empty($this->setDate)) {
			$year = substr($this->setDate, 0, 4);
		}
		if (!empty($this->disDate)) {
			$year = substr($this->disDate, 0, 4);
		}

		if ($year > 2015) {
			$this->load->model('EvnSection_model', 'es_model');

			// достаём движения той же КВС но с годом меньше на 1, им должны проставиться КСГ по алгоритму нового года.
			$query = "
				SELECT
					es.EvnSection_id as \"EvnSection_id\"
				FROM
					v_EvnSection es
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and EXTRACT(YEAR FROM CAST(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate) as date)) < :Year
					and COALESCE(es.EvnSection_IsPriem, 1) = 1
				order by
					es.EvnSection_setDate
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $this->id,
				'EvnSection_pid' => $this->pid,
				'Year' => $year
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					// пересчитываем КСГ
					$this->es_model->reset();
					$this->es_model->recalcKSGKPGKOEF($respone['EvnSection_id'], $this->sessionParams);
				}
			}
		}
	}

	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		$EvnSection_CoeffCTP = 0;
		$List = array();
		$EvnSection_CoeffCTPUltraLlong=0;

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			select
				CODE.AttributeValue_ValueString as \"code\",
				av.AttributeValue_ValueFloat as \"value\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				left join lateral (
					select
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
					limit 1
				) CODE on true
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = (select TariffClass_id from v_TariffClass where TariffClass_SysNick = 'Kslp' limit 1)
				and avis.AttributeVision_IsKeyValue = 2
				and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 1. Необходимость предоставления спального места и питания одному из родителей, иному члену семьи или иному законному представителю ребенка, при оказании ребенку (в возрасте до 4 лет, или старше - при наличии медицинских показаний)
		if (isset($KSLPCodes[1])) {

			$MesTariff_id = null;
			if (!empty($data['MesTariff_id'])) {
				$MesTariff_id = $this->getFirstResultFromQuery("
						select
							mt.MesTariff_id as \"MesTariff_id\"
						from
							v_MesTariff mt
							inner join v_MesOld mo on mo.Mes_id = mt.Mes_id
						where
							MesTariff_id = :MesTariff_id
							and mo.MesOld_Num in ('st17.001', 'st17.002', 'st17.003')
						limit 1
					", array(
					'MesTariff_id' => $data['MesTariff_id']
				));
			}

			if ($data['EvnSection_IsAdultEscort'] == 2 && empty($MesTariff_id)) {
				$coeffCTP = $KSLPCodes[1];

				$List[] = array(
					'Code' => 1,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 2. Случаи со сверхдлительными сроками госпитализации
		$ksg45Array = array('st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007');
		// КСЛП НЕ применяется. Если КСГ st19.039-st19.055
		$ksgExcArray = array('st19.039', 'st19.040', 'st19.041', 'st19.042', 'st19.043', 'st19.044', 'st19.045', 'st19.046', 'st19.047', 'st19.048', 'st19.049', 'st19.050', 'st19.051', 'st19.052', 'st19.053', 'st19.054', 'st19.055');
		if (
			!in_array($data['MesOld_Num'], $ksgExcArray)
			&& (
				($data['Duration'] > 30 && !in_array($data['MesOld_Num'], $ksg45Array))
				|| $data['Duration'] > 45
			)
		) {
			$normDays = 30;
			if (in_array($data['MesOld_Num'], $ksg45Array)) {
				$normDays = 45;
			}

			$coefDl = 0.25;
			// для реанимационных 0,4
			// Реанимационный коэффициент применяется, если в группе движений (SL) есть одна из услуг: 198199 или 098099
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->queryResult("
					SELECT 
						eu.EvnUsluga_id as \"EvnUsluga_id\"
					FROM
						v_EvnUsluga eu
						inner join v_PayType pt on pt.PayType_id = eu.PayType_id
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					WHERE
						eu.EvnUsluga_pid in (" . implode(',', $data['EvnSectionIds']) . ")
						and eu.EvnClass_id in (43,22,29)
						and uc.UslugaComplex_Code IN ('198199', '098099')
						and pt.PayType_SysNick = 'oms'
					limit 1
				", $data);

				if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
					$coefDl = 0.4;
				}
			}

			$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;

			$List[] = array(
				'Code' => 2,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTPUltraLlong = $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTPUltraLlong = $coeffCTP;
			}
		}

		// 3. Случаи оказания медицинской помощи в условиях дневного стационара по профилю «Стоматология детская» пациентам, страдающим неврологическими заболеваниями (ДЦП)
		if (isset($KSLPCodes[3])) {
			if (in_array($data['LpuUnitType_id'], array('6','7','9')) && $data['Mes_Code'] == '83') { // случаи, в которых определяется КСГ 83 «Болезни полости рта, слюнных желез и челюстей, врождённые аномалии лица и шеи, дети
				$coeffCTP = $KSLPCodes[3];

				$List[] = array(
					'Code' => 3,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0 || $EvnSection_CoeffCTPUltraLlong > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 4.  Сложность лечения, связанная с госпитализацией детей до 1 года (кроме КСГ, относящихся к профилю «Неонатология»: st17.001 – st.17.007)
		if (isset($KSLPCodes[4])) {
			if ($data['Person_Age'] < 1) {
				$MesTariff_id = null;
				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
						select
							mt.MesTariff_id as \"MesTariff_id\"
						from
							v_MesTariff mt
							inner join v_MesOld mo on mo.Mes_id = mt.Mes_id
						where
							MesTariff_id = :MesTariff_id
							and mo.MesOld_Num in ('st17.001', 'st17.002', 'st17.003','st17.004','st.17.005','st.17.006','st.17.007')
						limit 1
					", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}
				if (empty($MesTariff_id)) {
					$coeffCTP = $KSLPCodes[4];

					$List[] = array(
						'Code' => 4,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0 || $EvnSection_CoeffCTPUltraLlong > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}

				}
			}
		}

		//Суммарное значение КСЛП при наличии нескольких критериев не может превышать 1,8 за исключением случаев сверхдлительной госпитализации.
		if ($EvnSection_CoeffCTP > 1.8 && $EvnSection_CoeffCTPUltraLlong==0) {
			$EvnSection_CoeffCTP = 1.8;
		}

		$EvnSection_CoeffCTP = $EvnSection_CoeffCTP+$EvnSection_CoeffCTPUltraLlong;

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 2);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List
		);
	}

	/**
	 * Перегруппировка движений для всей КВС
	 */
	protected function _recalcIndexNum() {
		// убираем признаки со всех движений КВС
		$query = "
			update
				EvnSection
			set
				EvnSection_IndexNum = null,
				EvnSection_IsWillPaid = null
			where
				Evn_pid = :EvnSection_pid
				and COALESCE(EvnSection_IsManualIdxNum, 1) = 1
		";
		$this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));

		$groupped = array();
		// 1. группируем движения по диагнозам.
		$resp_es = $this->getEvnSectionGroup(array(
			'EvnSection_pid' => $this->pid
		));

		$k = 0;
		$prevKey = null;
		foreach($resp_es as $index => $respone) {
			$key = $respone['DiagGroup_Code'];

			if(!is_null($prevKey)){
				$datediff = strtotime($resp_es[$prevKey]['EvnSection_disDate']) - strtotime($resp_es[$prevKey]['EvnSection_setDate']);
				$duration = floor($datediff/(60*60*24));
				$diag_array = array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2');

				if(($resp_es[$prevKey]['MesOld_Num'] == 'st02.001' && (($duration >= 2 && in_array($resp_es[$prevKey]['Diag_Code'],$diag_array)) || $duration >= 6) )
					&& in_array($respone['MesOld_Num'], array('st02.003', 'st02.004'))
				){
					// В этой ситуации Движение-1 уходит в отдельную группу движений
					$groupped[$key . '_' . $resp_es[$prevKey]['EvnSection_id']] = $groupped[$resp_es[$prevKey]['DiagGroup_Code']];
					unset($groupped[$resp_es[$prevKey]['DiagGroup_Code']]);

				}

			}

			if (empty($key)) {
				$k++;
				$key = 'notgroup_'.$k;
			}

			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;

			if (
				empty($groupped[$key]['MaxCoeff']['MesTariff_Value'])
				|| $groupped[$key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
				|| ($groupped[$key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && $groupped[$key]['MaxCoeff']['EvnSection_setDate'] < $respone['EvnSection_setDate'])
			) {
				$groupped[$key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_setDate'] = $respone['EvnSection_setDate'];
			}
			$prevKey = $index;
		}

		$IndexNum = 0;
		foreach($groupped as $group) {
			$IndexNum++; // для каждой группы проставляем номер

			foreach($group['EvnSections'] as $es) {
				$this->db->query("
					update
						EvnSection
					set
						EvnSection_IndexNum = :EvnSection_IndexNum,
						EvnSection_IsWillPaid = :EvnSection_IsWillPaid
					where
						Evn_id = :EvnSection_id
				", array(
					'EvnSection_id' => $es['EvnSection_id'],
					'EvnSection_IndexNum' => $IndexNum,
					'EvnSection_IsWillPaid' => $es['EvnSection_id'] == $group['MaxCoeff']['EvnSection_id'] ? 2 : 1
				));
			}
		}
	}


	/**
	 * Пересчёт КСЛП для всей КВС
	 */
	protected function _recalcKSKP()
	{
		// убираем КСЛП со всех движений КВС
		$query = "
			update
				EvnSection
			set
				EvnSection_CoeffCTP = null
			where
				Evn_pid = :EvnSection_pid
		";
		$this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));

		// удаляем все связки КСЛП по всем движениям.
		$query = "
			select
				eskl.EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\"
			from
				EvnSectionKSLPLink eskl
				inner join Evn e on e.Evn_id = eskl.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$resp_eskl = $this->queryResult($query, array(
			'EvnSection_pid' => $this->pid
		));
		foreach($resp_eskl as $one_eskl) {
			$this->db->query("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnSectionKSLPLink_del (
					EvnSectionKSLPLink_id := :EvnSectionKSLPLink_id
				);
			", array(
				'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
			));
		}

		// Движения группируются (1 КВС = 1 случай)
		$resp_es = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				es.EvnSection_IndexNum as \"EvnSection_IndexNum\",
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as \"Person_Age\",
				to_char(es.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnSection_setDate\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\",
				COALESCE(d4.Diag_Code, d3.Diag_Code) as \"DiagGroup_Code\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				es.Lpu_id as \"Lpu_id\",
				es.MesTariff_id as \"MesTariff_id\",
				mt.MesTariff_Value as \"MesTariff_Value\",
				mo.Mes_Code as \"Mes_Code\",
				mo.MesOld_Num as \"MesOld_Num\",
				es.EvnSection_IsAdultEscort as \"EvnSection_IsAdultEscort\",
				es.EvnSection_IsMedReason as \"EvnSection_IsMedReason\"
			from
				v_EvnSection es
				inner join v_PayType pt on pt.PayType_id = es.PayType_id
				left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				left join v_PersonState ps on ps.Person_id = es.Person_id
				left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo on mo.Mes_id = mt.Mes_id
				left join v_Diag d on d.Diag_id = es.Diag_id
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
			where
				es.EvnSection_pid = :EvnSection_pid
				and COALESCE(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
			order by
				es.EvnSection_setDT
		", array(
			'EvnSection_pid' => $this->pid
		));

		$groupped = array();
		foreach($resp_es as $key => $respone) {
			$group_key = $respone['EvnSection_IndexNum'];

			$groupped[$group_key]['EvnSections'][$respone['EvnSection_id']] = $respone;
			$groupped[$group_key]['MaxCoeff']['Lpu_id'] = $respone['Lpu_id'];

			// Возраст человека берём из первого движения группы, т.е. минимальный
			if (!isset($groupped[$group_key]['MaxCoeff']['Person_Age']) || $groupped[$group_key]['MaxCoeff']['Person_Age'] > $respone['Person_Age']) {
				$groupped[$group_key]['MaxCoeff']['Person_Age'] = $respone['Person_Age'];
			}

			// Дату начала движений из первого движения
			if (empty($groupped[$group_key]['MaxCoeff']['EvnSection_setDate']) || strtotime($groupped[$group_key]['MaxCoeff']['EvnSection_setDate']) > strtotime($respone['EvnSection_setDate'])) {
				$groupped[$group_key]['MaxCoeff']['EvnSection_setDate'] = $respone['EvnSection_setDate'];
			}

			// Дату окончания движений из последнего движения
			if (empty($groupped[$group_key]['MaxCoeff']['EvnSection_disDate']) || strtotime($groupped[$group_key]['MaxCoeff']['EvnSection_disDate']) < strtotime($respone['EvnSection_disDate'])) {
				$groupped[$group_key]['MaxCoeff']['LastEvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$group_key]['MaxCoeff']['EvnSection_disDate'] = $respone['EvnSection_disDate'];
			}

			// если есть хотя бы на одном из группы
			if (empty($groupped[$group_key]['MaxCoeff']['EvnSection_IsAdultEscort']) || $respone['EvnSection_IsAdultEscort'] == 2) {
				$groupped[$group_key]['MaxCoeff']['EvnSection_IsAdultEscort'] = $respone['EvnSection_IsAdultEscort'];
			}

			// если есть хотя бы на одном из группы
			if (empty($groupped[$group_key]['MaxCoeff']['EvnSection_IsMedReason']) || $respone['EvnSection_IsMedReason'] == 2) {
				$groupped[$group_key]['MaxCoeff']['EvnSection_IsMedReason'] = $respone['EvnSection_IsMedReason'];
			}

			// КСГ с движения с наибольшим коэффициентом / если коэфф тот же, то с наибольшей датой начала
			if (
				empty($groupped[$group_key]['MaxCoeff']['MesTariff_Value'])
				|| $groupped[$group_key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
				|| ($groupped[$group_key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && strtotime($groupped[$group_key]['MaxCoeff']['EvnSection_setDate']) < strtotime($respone['EvnSection_setDate']))
			) {
				$groupped[$group_key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$group_key]['MaxCoeff']['LpuSectionProfile_Code'] = $respone['LpuSectionProfile_Code'];
				$groupped[$group_key]['MaxCoeff']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
				$groupped[$group_key]['MaxCoeff']['Mes_Code'] = $respone['Mes_Code'];
				$groupped[$group_key]['MaxCoeff']['MesTariff_id'] = $respone['MesTariff_id'];
				$groupped[$group_key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
			}
		}

		// для каждого движения группы надо выбрать движение с наибольшим КСГ.
		foreach($groupped as $key => $group) {
			$EvnSectionIds = array();
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
			}
			$groupped[$key]['MaxCoeff']['EvnSectionIds'] = $EvnSectionIds; // все движения группы

			// Длительность - общая длительность группы
			$datediff = strtotime($group['MaxCoeff']['EvnSection_disDate']) - strtotime($group['MaxCoeff']['EvnSection_setDate']);
			$Duration = floor($datediff/(60*60*24));
			$groupped[$key]['MaxCoeff']['Duration'] = $Duration;
		}

		foreach($groupped as $group) {
			// 4. записываем для каждого движения группы полученные КСЛП в БД.
			foreach($group['EvnSections'] as $es) {
				$esdata = array(
					'EvnSection_id' => $es['EvnSection_id'],
					'EvnSectionIds' => array($es['EvnSection_id']),
					'LpuSectionProfile_Code' => $es['LpuSectionProfile_Code'],
					'LpuUnitType_id' => $es['LpuUnitType_id'],
					'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
					'Person_Age' => $group['MaxCoeff']['Person_Age'],
					'Duration' => $group['MaxCoeff']['Duration'],
					'Mes_Code' => $es['Mes_Code'],
					'MesOld_Num' => $es['MesOld_Num'],
					'MesTariff_id' => $es['MesTariff_id'],
					'EvnSection_IsAdultEscort' => $es['EvnSection_IsAdultEscort'],
					'EvnSection_IsMedReason' => $es['EvnSection_IsMedReason']
				);

				$kslp = $this->calcCoeffCTP($esdata);

				$EvnSection_CoeffCTP = $kslp['EvnSection_CoeffCTP'];

				$query = "
					update
						EvnSection
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP
					where
						Evn_id = :EvnSection_id
				";

				$this->db->query($query, array(
					'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
					'EvnSection_id' => $es['EvnSection_id']
				));

				if (isset($kslp['List'])) {
					foreach ($kslp['List'] as $one_kslp) {
						$this->db->query("
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\",
								EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\"
							from p_EvnSectionKSLPLink_ins (
								EvnSection_id := :EvnSection_id,
								EvnSectionKSLPLink_Code := cast(:EvnSectionKSLPLink_Code as varchar),
								EvnSectionKSLPLink_Value := :EvnSectionKSLPLink_Value,
								pmUser_id := :pmUser_id
							);
						", array(
							'EvnSection_id' => $es['EvnSection_id'],
							'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
							'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
							'pmUser_id' => $this->promedUserId
						));
					}
				}
			}
		}
	}

	/**
	 * Достаём сгруппированные движения
	 */
	function getEvnSectionGroup($data) {
		$queryParams = array(
			'EvnSection_pid' => $data['EvnSection_pid']
		);
		$filter = "";

		if (!empty($data['Diag_id'])) {
			// только одну группу.
			$data['GroupDiag_id'] = $this->getFirstResultFromQuery("
				select
					COALESCE(d4.Diag_id, d3.Diag_id)
				from
					v_Diag d
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				where
					d.Diag_id = :Diag_id
			", array(
				'Diag_id' => $data['Diag_id']
			), true);
			$filter .= " and COALESCE(d4.Diag_id, d3.Diag_id) = :GroupDiag_id";
			$queryParams['GroupDiag_id'] = $data['GroupDiag_id'];
		}

		if (!empty($data['EvnSection_id'])) {
			$indexNum = $this->getFirstResultFromQuery("
				select 
					EvnSection_IndexNum as \"EvnSection_IndexNum\" 
				from 
					v_EvnSection 
				where 
					EvnSection_id = :EvnSection_id
				", $data, true);

			$filter .= " and es.EvnSection_IndexNum = :EvnSection_IndexNum";
			$queryParams['EvnSection_IndexNum'] = $indexNum;
		}

		return $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				COALESCE(d4.Diag_Code, d3.Diag_Code) as \"DiagGroup_Code\",
				mt.MesTariff_Value as \"MesTariff_Value\",
				to_char(es.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnSection_setDate\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\",
				d.Diag_Code as \"Diag_Code\",
				mo.Mes_id as \"Mes_id\",
				mo.Mes_Code as \"Mes_Code\",
				mo.MesOld_Num as \"MesOld_Num\"
			from
				v_EvnSection es
				inner join v_PayType pt on pt.PayType_id = es.PayType_id
				left join v_Diag d on d.Diag_id = es.Diag_id
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo on mo.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and pt.PayType_SysNick = 'oms'
				and COALESCE(es.EvnSection_IsPriem, 1) = 1
				and es.HTMedicalCareClass_id is null
				{$filter}
		", $queryParams);
	}
}
