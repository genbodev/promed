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
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Stas Bykov aka Savage (savage1981@gmail.com)
* @version			Kareliya
*/

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

class Kareliya_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	public function loadKSGKPGKOEF2019($data)
    {
		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		$response = ['KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Code' => '', 'success' => true];

		// группировка
		if (!empty($data['EvnSection_id'])) {
			$this->pid = $data['EvnSection_pid'];
			$resp_es = $this->recalcIndexNumByGroup([
				'getGroupsOnly' => true
			]);
			$currentGroupNum = null;
			if (!empty($resp_es)) {
				$groupForKSGRecalc = [];
				foreach($resp_es as $one_es) {
					if (!empty($one_es['groupNum'])) {
						if ($one_es['EvnSection_id'] == $data['EvnSection_id']) {
							$currentGroupNum = $one_es['groupNum'];
						}
						if (empty($groupForKSGRecalc[$one_es['groupNum']])) {
							$groupForKSGRecalc[$one_es['groupNum']] = [
								'EvnSectionIds' => [],
								'EvnSection_setDate' => $one_es['EvnSection_setDate'],
								'EvnSection_disDate' => $one_es['EvnSection_disDate']
							];
						} else {
							if ($groupForKSGRecalc[$one_es['groupNum']]['EvnSection_disDate'] < $one_es['EvnSection_disDate']) {
								$groupForKSGRecalc[$one_es['groupNum']]['EvnSection_disDate'] = $one_es['EvnSection_disDate'];
							}
							if ($groupForKSGRecalc[$one_es['groupNum']]['EvnSection_setDate'] > $one_es['EvnSection_setDate']) {
								$groupForKSGRecalc[$one_es['groupNum']]['EvnSection_setDate'] = $one_es['EvnSection_setDate'];
							}
						}

						$groupForKSGRecalc[$one_es['groupNum']]['EvnSectionIds'][] = $one_es['EvnSection_id'];
					}
				}

				if (!empty($currentGroupNum)) {
					$data['EvnSectionIds'] = $groupForKSGRecalc[$currentGroupNum]['EvnSectionIds'];
					$data['EvnSection_setDate'] = $groupForKSGRecalc[$currentGroupNum]['EvnSection_setDate'];
					$data['EvnSection_disDate'] = $groupForKSGRecalc[$currentGroupNum]['EvnSection_disDate'];
				}
			}
		}

		$resp = $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
		if (!empty($resp['MesTariff_id'])) {
			$response['KSG'] = $resp['Mes_Code'] . '. ' . $resp['MesOld_Num'] . '. ' . $resp['Mes_Name'];
			$response['KPG'] = $resp['KPG'];
			$response['KOEF'] = round($resp['MesTariff_Value'], 3);
			$response['Mes_tid'] = $resp['Mes_tid'];
			$response['Mes_sid'] = $resp['Mes_sid'];
			$response['Mes_kid'] = $resp['Mes_kid'];
			$response['MesTariff_id'] = $resp['MesTariff_id'];
			$response['Mes_Code'] = $resp['Mes_Code'];
			$response['MesOldUslugaComplex_id'] = $resp['MesOldUslugaComplex_id'];
		}
		return $response;
	}

    /**
     * поиск ксг/кпг/коэф для 2018 года
     * @param $data
     * @return array
     * @throws Exception
     */
	public function loadKSGKPGKOEF2018($data)
    {
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
			return $this->loadKSGKPGKOEF2019($data);
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], ['6','7','9'])) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		if (!empty($data['EvnSectionIds'])) {
			$EvnSectionIds = $data['EvnSectionIds'];
		} else {
			$EvnSectionIds = [];
			if (!empty($data['EvnSection_id'])) {
				$EvnSectionIds[] = $data['EvnSection_id'];

				$this->pid = $data['EvnSection_pid'];
				$resp_es = $this->recalcIndexNumByGroup([
					'getGroupsOnly' => true
				]);
				$currentGroupNum = null;
				if (!empty($resp_es)) {
					$groupForKSGRecalc = array();
					foreach($resp_es as $one_es) {
						if (!empty($one_es['groupNum'])) {
							if ($one_es['EvnSection_id'] == $data['EvnSection_id']) {
								$currentGroupNum = $one_es['groupNum'];
							}
							if (empty($groupForKSGRecalc[$one_es['groupNum']])) {
								$groupForKSGRecalc[$one_es['groupNum']] = array(
									'EvnSectionIds' => array(),
									'EvnSection_setDate' => $one_es['EvnSection_setDate'],
									'EvnSection_disDate' => $one_es['EvnSection_disDate']
								);
							} else {
								if ($groupForKSGRecalc[$one_es['groupNum']]['EvnSection_disDate'] < $one_es['EvnSection_disDate']) {
									$groupForKSGRecalc[$one_es['groupNum']]['EvnSection_disDate'] = $one_es['EvnSection_disDate'];
								}
								if ($groupForKSGRecalc[$one_es['groupNum']]['EvnSection_setDate'] > $one_es['EvnSection_setDate']) {
									$groupForKSGRecalc[$one_es['groupNum']]['EvnSection_setDate'] = $one_es['EvnSection_setDate'];
								}
							}

							$groupForKSGRecalc[$one_es['groupNum']]['EvnSectionIds'][] = $one_es['EvnSection_id'];
						}
					}

					if (!empty($currentGroupNum)) {
						$EvnSectionIds = $groupForKSGRecalc[$currentGroupNum]['EvnSectionIds'];
						$data['EvnSection_setDate'] = $groupForKSGRecalc[$currentGroupNum]['EvnSection_setDate'];
						$data['EvnSection_disDate'] = $groupForKSGRecalc[$currentGroupNum]['EvnSection_disDate'];
					}
				}
			}
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				WEIGHT.PersonWeight_Weight as \"PersonWeight_Weight\",
				case
					when date_part('month', PS.Person_BirthDay) = date_part('month',:EvnSection_setDate) and date_part('day',PS.Person_BirthDay) = date_part('day',:EvnSection_setDate) then 1 else 0
				end as \"BirthToday\"
			from
				v_PersonState PS
				left join lateral (
					select
						case when pw.Okei_id = 37 then FLOOR(PersonWeight_Weight * 1000) else FLOOR(PersonWeight_Weight) end as PersonWeight_Weight
					from
						v_PersonWeight pw
					where
						pw.Person_id = ps.person_id and pw.WeightMeasureType_id = 1
					order by
						PersonWeight_setDT
					desc
					limit 1
				) WEIGHT on true
			where
				ps.Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['PersonWeight_Weight'] = $resp[0]['PersonWeight_Weight'];
				$data['BirthToday'] = $resp[0]['BirthToday'];
			} else {
				return ['Error_Msg' => 'Ошибка получения данных по человеку'];
			}
		} else {
			return ['Error_Msg' => 'Ошибка получения данных по человеку'];
		}

		$UslugaComplexIds = [];
		if (!empty($EvnSectionIds)) {
			$query = "
				select distinct
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					uc.UslugaComplex_Code as \"UslugaComplex_Code\"
				from
					v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				where
					eu.EvnUsluga_pid IN ('" . implode("','", $EvnSectionIds) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and eu.EvnUsluga_setDT is not null
			";
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$UslugaComplexIds[] = $respone['UslugaComplex_id'];
				}
			}
		}

		$MesAgeGroup11Filter = "or (:Person_Age < 2 and mu.MesAgeGroup_id = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
		}

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], ['6','7','9'])) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		// 0.	Определение КСГ при политравме
		if (!empty($EvnSectionIds)) {
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
							and edps2.EvnDiagPS_pid IN ('" . implode("','", $EvnSectionIds) . "')
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid IN ('" . implode("','", $EvnSectionIds) . "')
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
								mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
								mo.Mes_Code as \"Mes_Code\",
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
										mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml  on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
                                    and
                                        mo2.Mes_begDT <= :EvnSection_disDate
                                    and
                                        (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								) mokpg on true
							where
								mo.Mes_Code = '233'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		// Достаём список КСГ по реабилитации
		$MesReabilIds = [];
		$resp_reabil = $this->queryResult("
			select distinct
				ml.Mes_id as \"Mes_id\"
			from
				v_MesOld mo2
				inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
			where
				mo2.Mes_Code = '37'
            and mo2.Mes_begDT <= :EvnSection_disDate
            and (coalesce (mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
		", [
			'EvnSection_disDate' => $data['EvnSection_disDate']
		]);
		foreach($resp_reabil as $one_reabil) {
			$MesReabilIds[] = $one_reabil['Mes_id'];
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($UslugaComplexIds)) {
			$query = "
				select
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\",
					mo.MesType_id as \"MesType_id\"
				from v_UslugaComplex uc
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join lateral (
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					uc.UslugaComplex_id IN ('".implode("','", $UslugaComplexIds)."')
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
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id = 3 and EvnDiagPS_pid IN ('" . implode("','", $EvnSectionIds) . "')))
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
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
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					uc.UslugaComplex_id,
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
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['UslugaComplex_id']) {
							$CurUsluga = $KSGOperOne['UslugaComplex_id'];
							if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF'] || in_array($KSGOperOne['Mes_id'], $MesReabilIds)) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$uslugaComplexFilter = "and mu.UslugaComplex_id is null";
			if (!empty($UslugaComplexIds)) {
				$uslugaComplexFilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "'))";
			}

			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
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
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mu.Diag_id = :Diag_id
					{$uslugaComplexFilter}
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id = 3 and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age < 1 and mu.MesAgeGroup_id = 10)
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
					and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
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
						mokpg.Mes_Code || coalesce('. ' || mokpg.Mes_Name, '') as \"KPG\",
						mokpg.Mes_Code as \"Mes_Code\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and coalesce(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
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

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Code' => '', 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id']) || in_array($KSGOper['Mes_id'], $MesReabilIds)) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['KSG'] = $KSGOper['KSG'];
				$response['KPG'] = $KSGOper['KPG'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KPG'] = $KSGTerr['KPG'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['KPG'] = $KSGOper['KPG'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['KPG'] = $KSGTerr['KPG'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
			$response['Mes_Code'] = $KPGFromLpuSectionProfile['Mes_Code'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KPG'] = $KSGFromPolyTrauma['KPG'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф для 2017 года
	 */
	function loadKSGKPGKOEF2017($data) {
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
			return $this->loadKSGKPGKOEF2018($data);
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
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate::timestamp) + 1 as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				WEIGHT.PersonWeight_Weight as \"PersonWeight_Weight\",
				case
					when date_part('month', PS.Person_BirthDay) =  date_part('month', :EvnSection_setDate) and  date_part('day', PS.Person_BirthDay) = date_part('day',:EvnSection_setDate) then 1 else 0
				end as \"BirthToday\"
			from
				v_PersonState PS
				left join lateral (
					select
						case when pw.Okei_id = 37 then FLOOR(PersonWeight_Weight * 1000) else FLOOR(PersonWeight_Weight) end as PersonWeight_Weight
					from
						v_PersonWeight pw 
					where
						pw.Person_id = ps.person_id and pw.WeightMeasureType_id = 1
					order by
						PersonWeight_setDT
					desc
					limit 1
				) WEIGHT on true
			where
				ps.Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['PersonWeight_Weight'] = $resp[0]['PersonWeight_Weight'];
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

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
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
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
								mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as KSG,
								mo.Mes_Code as \"Mes_Code\",
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
										mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								) mokpg on true
							where
								mo.Mes_Code = '220'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		// Достаём список КСГ по реабилитации
		$MesReabilIds = array();
		$resp_reabil = $this->queryResult("
			select distinct
				ml.Mes_id as \"Mes_id\"
			from
				v_MesOld mo2
				inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
			where
				mo2.Mes_Code = '37'
				and mo2.Mes_begDT <= :EvnSection_disDate
				and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));
		foreach($resp_reabil as $one_reabil) {
			$MesReabilIds[] = $one_reabil['Mes_id'];
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as KOEF,
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
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id = 3 and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
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
							if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF'] || in_array($KSGOperOne['Mes_id'], $MesReabilIds)) {
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
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
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
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id = 3 and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age < 1 and mu.MesAgeGroup_id = 10)
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
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
						mokpg.Mes_Code || coalesce('. ' || mokpg.Mes_Name, '') as \"KPG\",
						mokpg.Mes_Code as \"Mes_Code\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and coalesce(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
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

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Code' => '', 'success' => true);

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
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id']) || in_array($KSGOper['Mes_id'], $MesReabilIds)) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['KSG'] = $KSGOper['KSG'];
				$response['KPG'] = $KSGOper['KPG'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KPG'] = $KSGTerr['KPG'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['KPG'] = $KSGOper['KPG'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['KPG'] = $KSGTerr['KPG'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
			$response['Mes_Code'] = $KPGFromLpuSectionProfile['Mes_Code'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KPG'] = $KSGFromPolyTrauma['KPG'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф для 2016 года
	 */
	function loadKSGKPGKOEF2016($data) {
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
			return $this->loadKSGKPGKOEF2017($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT
				es.EvnSection_id as \"EvnSection_id\",
				to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
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
					return $this->loadKSGKPGKOEF2017($data);
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
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate::timestamp) + 1 as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				WEIGHT.PersonWeight_Weight as \"PersonWeight_Weight\"
			from
				v_PersonState PS
				left join lateral(
					select
						case when pw.Okei_id = 37 then FLOOR(PersonWeight_Weight * 1000) else FLOOR(PersonWeight_Weight) end as PersonWeight_Weight
					from
						v_PersonWeight pw 
					where
						pw.Person_id = ps.person_id and pw.WeightMeasureType_id = 1
					order by
						PersonWeight_setDT
					desc
					limti 1
				) WEIGHT on true
			where
				ps.Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['PersonWeight_Weight'] = $resp[0]['PersonWeight_Weight'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		// если вес между 500 и 2500, то
		if (!empty($data['PersonWeight_Weight']) && $data['PersonWeight_Weight'] >= 500 && $data['PersonWeight_Weight'] <= 2500 && $data['Person_AgeDays'] <= 28) {
			if ($data['PersonWeight_Weight'] >= 1501) {
				// от 1 501 до 2 500 гр. КСГ 105
				$data['ChildMes_Code'] = '105';
			} else {
				// от 500 до 1 500 гр. КСГ 106
				$data['ChildMes_Code'] = '106';
			}

			$query = "
				select
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\",
					mo.MesType_id as \"MesType_id\"
				from
					v_MesOld mo
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mo.Mes_Code = :ChildMes_Code
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				limit 1
			";

			$resp = $this->queryResult($query, $data);
			if (!empty($resp[0])) {
				$KSGTerr = $resp[0];
			}
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
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
								mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
								mo.Mes_Code as \"Mes_Code\",
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
										mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg on true
							where
								mo.Mes_Code = '216'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
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
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id = 3 and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age < 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					eu.EvnUsluga_id,
					case when mo.Mes_Code IN ('300','301','302','303','304','305','306','307','308','111','112','113','114','115','116','117','118') then 1 else 0 end desc,
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
							if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF'] || in_array($KSGOperOne['Mes_Code'], array('300','301','302','303','304','305','306','307','308','111','112','113','114','115','116','117','118'))) {
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
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
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
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id = 3 and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age < 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and (mo.Mes_Code != '105' or (:PersonWeight_Weight >= 1501 and :PersonWeight_Weight <= 2500))
					and (mo.Mes_Code != '106' or (:PersonWeight_Weight >= 500 and :PersonWeight_Weight <= 1500))
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mo.Mes_Code IN ('300','301','302','303','304','305','306','307','308','111','112','113','114','115','116','117','118') then 1 else 0 end desc,
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
						mokpg.Mes_Code || coalesce('. ' || mokpg.Mes_Name, '') as \"KPG\",
						mokpg.Mes_Code as \"Mes_Code\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and coalesce(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
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

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Code' => '', 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id']) || in_array($KSGOper['Mes_Code'], array('300','301','302','303','304','305','306','307','308','111','112','113','114','115','116','117','118'))) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['KSG'] = $KSGOper['KSG'];
				$response['KPG'] = $KSGOper['KPG'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KPG'] = $KSGTerr['KPG'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['KPG'] = $KSGOper['KPG'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['KPG'] = $KSGTerr['KPG'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
			$response['Mes_Code'] = $KPGFromLpuSectionProfile['Mes_Code'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KPG'] = $KSGFromPolyTrauma['KPG'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		return $response;
	}

	/**
	 * Считаем КСКП для движения в 2019 году
	 */
	protected function calcCoeffCTP2019($data) {
		$EvnSection_CoeffCTP = 0;
		$EvnSection_TreatmentDiff = null;
		$List = array();

		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// КСЛП определять только в круглосуточном стационаре.
			return array(
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
				'List' => $List
			);
		}

		/**
		 * @task https://redmine.swan-it.ru/issues/162457
		 */
		$LeaveTypeArray = array(7,9,10,11);

		if ($data['EvnSection_disDate'] < '2019-07-01') {
			$LeaveTypeArray[] = 13;
			if ($data['EvnSection_disDate'] >= '2019-03-01') {
				$LeaveTypeArray[] = 12;
			}
		}

		// Если случай сверхкороткий или прерванный, то коэффициент КСКП не применяется
		if (in_array($data['LeaveType_id'], $LeaveTypeArray) || $data['DurationSeconds'] <= 72 * 60 * 60) {
			return array(
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
				'List' => $List
			);
		}

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
		    with cte as (
		        select TariffClass_id from v_TariffClass where TariffClass_SysNick = 'Kslp' limit 1
		    )
			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
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
				and avis.AttributeVision_TablePKey = (select TariffClass_id from cte)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 1. Сложность лечения пациента, связанная с возрастом, при госпитализации детей в возрасте до 1 года (кроме КСГ, относящихся к профилю «неонатология»: КСГ 107-113)
		if (isset($KSLPCodes[1])) {
			if ($data['Person_Age'] < 1) {
				$MesTariff_id = null;
				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
						select
							mt.MesTariff_id
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
				if (empty($MesTariff_id)) {
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
		}

		// 2. Необходимость предоставления спального места и питания одному из родителей, иному члену семьи или иному законному представителю ребенка, при оказании ребенку (в возрасте до 4 лет, или старше - при наличии медицинских показаний)
		if (isset($KSLPCodes[2])) {
			if ($data['EvnSection_IsAdultEscort'] == 2) {
				$coeffCTP = $KSLPCodes[2];
				$List[] = array(
					'Code' => 2,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 3. Сложность лечения пациента, связанная с возрастом (госпитализация детей в возрасте от 1 года до 4 лет)
		if (isset($KSLPCodes[3])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
				$coeffCTP = $KSLPCodes[3];
				$List[] = array(
					'Code' => 3,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 4. Сложность лечения пациента, связанная с возрастом, для лиц старше 75 лет
		if (isset($KSLPCodes[4])) {
			if ($data['Person_Age'] > 75 && !empty($data['EvnSectionIds'])) {
				$resp_es = $this->queryResult("
				    with cte as (
				        select
				            Mes_id
                        from
                            v_MesOld
                        where
                            MesOld_Num = 'st38.001'
                        and
                            coalesce(Mes_begDT, :EvnSection_disDate) <= :EvnSection_disDate
                        and
                            coalesce(Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate
                        limit 1
				    )
					
					select
						EvnSection_id as \"EvnSection_id\" 
					from
						v_EvnSection es
						inner join v_Diag d on d.Diag_id = es.Diag_id
						inner join v_EvnDiagPS edps on edps.EvnDiagPS_pid = es.EvnSection_id and edps.DiagSetClass_id IN (2,3)
						inner join v_Diag ds on ds.Diag_id = edps.Diag_id
						inner join fed.LpuSectionBedProfileLink LSBPLink on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
						inner join v_LpuSectionBedProfile LSBP on LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
						left join v_MesOldUslugaComplex mouc on mouc.Mes_id = (select Mes_id from cte) 
						    and mouc.Diag_id = es.Diag_id
						    and coalesce(mouc.MesOldUslugaComplex_begDT, :EvnSection_disDate) <= :EvnSection_disDate
						    and coalesce(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate 
					where
						ds.Diag_Code = 'R54'
						and mouc.MesOldUslugaComplex_id is null
						and LSBP.LpuSectionBedProfile_Code = '72'
						and ES.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
					limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				if (!empty($resp_es[0]['EvnSection_id'])) {
					$coeffCTP = $KSLPCodes[4];
					$List[] = array(
						'Code' => 4,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 5. Наличие у пациента тяжелой сопутствующей патологии, влияющей на сложность лечения пациента
		if (isset($KSLPCodes[5])) {
			if (!empty($data['EvnSectionIds'])) {
				$AttributeValue = $this->queryResult("
					with cte as (
						select
						    TariffClass_id as AttributeVision_TablePKey
                        from
                            v_TariffClass
                        where
                            TariffClass_SysNick = 'diagpat'
                        limit 1
					)
	
					SELECT
						av.AttributeValue_id
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join lateral  (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
							 limit 1
						) DIAGFILTER on true
						inner join v_EvnDiagPS edps on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
					WHERE
						avis.AttributeVision_TableName = 'dbo.TariffClass'
						and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from cte)
						and avis.AttributeVision_IsKeyValue = 2
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and edps.DiagSetClass_id in (3) -- обязательно только сопутствующий
						and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					 limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[5];
					$List[] = array(
						'Code' => 5,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 6. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к различным КСГ
		if (isset($KSLPCodes[6])) {
			$needKSLP6 = false;
			if (!empty($data['EvnSectionIds'])) {
				// @task #153058

				// проверяем наличие лекарственных схем
				$cntMT = 0;
				$cntSH = 0;

				$checkKSLP6 = $this->queryResult("
					select
						dts.DrugTherapyScheme_Code as \"DrugTherapyScheme_Code\"
					FROM
						v_EvnSectionDrugTherapyScheme esdts
						inner join v_DrugTherapyScheme dts on dts.DrugTherapyScheme_id = esdts.DrugTherapyScheme_id
					WHERE
						esdts.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
				");

				if ( is_array($checkKSLP6) && count($checkKSLP6) > 0 ) {
					foreach ( $checkKSLP6 as $row ) {
						if ( substr($row['DrugTherapyScheme_Code'], 0, 5) >= 'sh001' && substr($row['DrugTherapyScheme_Code'], 0, 5) <= 'sh904' ) {
							$cntSH++;
						}
						else if ( substr($row['DrugTherapyScheme_Code'], 0, 5) >= 'mt001' && substr($row['DrugTherapyScheme_Code'], 0, 5) <= 'mt017' ) {
							$cntMT++;
						}
					}
				}

				// пункт 4 в ТЗ
				if ( $cntSH >= 2 || ($cntSH >= 1 && $cntMT >= 1) ) {
					$needKSLP6 = true;
				}

				// смотрим услуги
				if (!$needKSLP6) {
					// вопрос: может быть одна услуга с двумя атрибутами luchter и hir одновременно?
					$checkKSLP6 = $this->queryResult("
						select distinct
						    ucat.UslugaComplexAttributeType_SysNick as \"UslugaComplexAttributeType_SysNick\"
						from
							v_EvnUsluga eu
							inner join v_PayType pt on pt.PayType_id = eu.PayType_id and pt.PayType_SysNick = 'oms'
							inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eu.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							eu.EvnUsluga_pid in ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.EvnClass_id in (43, 22, 29, 47)
							and ucat.UslugaComplexAttributeType_SysNick in ('luchter', 'hir','XirurgLech')
							and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
							and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					", [
						'EvnSection_disDate' => $data['EvnSection_disDate']
					]);

					if ( is_array($checkKSLP6) && count($checkKSLP6) > 0 ) {
						if (
							(count($checkKSLP6) > 1 && ($checkKSLP6[0]['UslugaComplexAttributeType_SysNick'] == 'luchter' || $checkKSLP6[1]['UslugaComplexAttributeType_SysNick'] == 'luchter')) // пункт 3 в ТЗ
							|| ($checkKSLP6[0]['UslugaComplexAttributeType_SysNick'] == 'luchter' && $cntSH > 0) // пункт 1 в ТЗ
							|| (in_array($checkKSLP6[0]['UslugaComplexAttributeType_SysNick'], array('XirurgLech', 'hir')) && $cntSH + $cntMT > 0) // пункт 2 в ТЗ
						) {
							$needKSLP6 = true;
						}
					}
				}
			}

			if ($needKSLP6) {
				$coeffCTP = $KSLPCodes[6];
				$List[] = array(
					'Code' => 6,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 7. Проведение сочетанных хирургических вмешательств
		if (isset($KSLPCodes[7])) {
			if (!empty($data['EvnSectionIds'])) {
				$AttributeValue = $this->queryResult("
					with cte as (
					    select TariffClass_id as AttributeVision_TablePKey  from v_TariffClass where TariffClass_SysNick = 'sochir' limit 1
					)
	
					SELECT
						av.AttributeValue_id
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29,47) and eu.PayType_id = :PayTypeOms_id)
						) UC1FILTER on true
						inner join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29,47) and eu.PayType_id = :PayTypeOms_id)
						) UC2FILTER on true
					WHERE
						avis.AttributeVision_TableName = 'dbo.TariffClass'
						and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from cte)
						and avis.AttributeVision_IsKeyValue = 2
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and UC2FILTER.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
					limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[7];
					$List[] = array(
						'Code' => 7,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 8. Проведение однотипных операций на парных органах
		if (isset($KSLPCodes[8])) {
			$needKSLP8 = false;
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->queryResult("
					SELECT
						SUM(coalesce(eu.EvnUsluga_Kolvo, 1)) as \"EvnUsluga_Kolvo\"
					FROM
						v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29,47)
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'parorg'
								and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
								and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
							limit 1
						)
					group by
						eu.UslugaComplex_id
					order by
						sum(EvnUsluga_Kolvo) desc
					limit 1
				", array(
					'PayTypeOms_id' => $data['PayTypeOms_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($EvnUsluga[0]['EvnUsluga_Kolvo']) && $EvnUsluga[0]['EvnUsluga_Kolvo'] > 1) {
					$needKSLP8 = true;
				}

				if (!$needKSLP8) {
					$EvnUsluga_setDate = $this->getFirstResultFromQuery("
						SELECT
						    eu.EvnUsluga_setDate
						FROM v_EvnUsluga eu
						WHERE
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.PayType_id = :PayTypeOms_id
							and eu.EvnClass_id in (43,22,29,47)
							and exists(
								select
									uca.UslugaComplexAttribute_id
								from
									v_UslugaComplexAttribute uca
									inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								where
									uca.UslugaComplex_id = eu.UslugaComplex_id
									and ucat.UslugaComplexAttributeType_SysNick = 'AtngrIssl'
									and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
									and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
								limit 1
							)
						group by eu.EvnUsluga_setDate
						having count(eu.EvnUsluga_id) > 1
						limit 1
					", array(
						'PayTypeOms_id' => $data['PayTypeOms_id'],
						'EvnSection_disDate' => $data['EvnSection_disDate']
					));
					if ($EvnUsluga_setDate !== false) {
						$needKSLP8 = true;
					}
				}
			}
			if ($needKSLP8 === true) {
				$coeffCTP = $KSLPCodes[8];
				$List[] = array(
					'Code' => 8,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 667. Случаи лечения пациентов, ранее перенесших субарахноидальное кровоизлияние в следствие разрыва аневризм церебральных сосудов и
		// госпитализированных в плановом порядке в нейрохирургическое отделение ГБУЗ "Республиканская больница им. В.А.Баранова" для
		// проведения тотальной селективной церебральной ангиографии
		if (isset($KSLPCodes[667])) {
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->getFirstResultFromQuery("
					SELECT eu.EvnUsluga_id
					FROM v_EvnUsluga eu
						inner join v_EvnSection es on es.EvnSection_id = eu.EvnUsluga_pid
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
						inner join v_Diag d on d.Diag_id = es.Diag_id
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and d.Diag_Code = 'I67.1'
						and uc.UslugaComplex_Code = 'A06.12.031.001'
					limit 1
				", [
					'PayTypeOms_id' => $data['PayTypeOms_id']
				]);

				if ($EvnUsluga !== false && !empty($EvnUsluga)) {
					$coeffCTP = $KSLPCodes[667];
					$List[] = [
						'Code' => 667,
						'Value' => $coeffCTP
					];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 668. Проведение антимикробной терапии инфекций, вызванных полирезистентными микроорганизмами
		// 1. в движении есть услуга A26.30.004 «Определение чувствительноти микроорганизмов к антимикробным химиотерапевтическим препаратам»,
		// 2. в движении есть одна из услуг: A11.02.002 «Внутримышечное введение лекарственных препаратов» или A11.12.003 «Внутривенное введение лекарственных средств»
		// 3. в движении есть одно из сочетаний основного диагноза с учетом подрубрик (поле DS1 в реестре) и медикамента с длительностью приема 5 и более дней (в разделе «Лекарственное лечение» указан медикамент (rls.Drug), связанный с соответствующим МНН, при этом в поле «Продолжительность» на форме «Курс лекарственного лечения» для этого медикамента указано значение больше или равное 5)
		if (isset($KSLPCodes[668])) {
			if (!empty($data['EvnSectionIds'])) {
				// получаем список МНН, которые есть в лекарственных назначениях
				$diagCodes = [];
				$resp_am = $this->queryResult("
					select distinct
						dcmnn.ACTMATTERS_id as \"ACTMATTERS_id\"
					from
						v_EvnPrescr EP
						inner join v_EvnPrescrTreat EPT on EPT.EvnPrescrTreat_id = EP.EvnPrescr_id
						inner join v_EvnCourseTreat ECT on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
						inner join v_EvnCourseTreatDrug ECTD on ECTD.EvnCourseTreat_id = EPT.EvnCourse_id
						inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = ECTD.DrugComplexMnn_id
						inner join rls.DrugComplexMnnName dcmnn on dcm.DrugComplexMnnName_id = dcmnn.DrugComplexMnnName_id
					where
						EP.EvnPrescr_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and EP.PrescriptionType_id = 5
						and EP.PrescriptionStatusType_id <> 3
						and ECT.EvnCourseTreat_Duration >= 5
				");
				foreach($resp_am as $one_am) {
					if (in_array($one_am['ACTMATTERS_id'], [1996, 1355, 1115])) {
						// Пневмония (J18) и МНН одно из, Меропенем (ACTMATTERS_id = 1996), Фосфомицин (ACTMATTERS_id = 1355), Полимиксин В (ACTMATTERS_id = 1115);
						$diagCodes[] = 'J18';
					}
					if (in_array($one_am['ACTMATTERS_id'], [1996, 3808])) {
						// Сепсис (A41) и МНН одно из: Меропенем (ACTMATTERS_id = 1996), Дорипенем (ACTMATTERS_id = 3808);
						$diagCodes[] = 'A41';
					}
					if (in_array($one_am['ACTMATTERS_id'], [1996, 3005])) {
						// ХОБЛ (J44) и МНН одно из: Меропенем (ACTMATTERS_id = 1996), Линезолид (ACTMATTERS_id = 3005);
						$diagCodes[] = 'J44';
					}
					if (in_array($one_am['ACTMATTERS_id'], [2418, 1794])) {
						// Бронхит (J20) и МНН одно из: Цефепим (ACTMATTERS_id = 2418), Сульбактам (ACTMATTERS_id = 1794);
						$diagCodes[] = 'J20';
					}
					if (in_array($one_am['ACTMATTERS_id'], [3217, 87, 5506])) {
						// Кандидозы (B37) и МНН одно из: Вориконазол (ACTMATTERS_id = 3217), Флуконазол (ACTMATTERS_id = 87), Микафунгин (ACTMATTERS_id = 5506).
						$diagCodes[] = 'B37';
					}
				}

				if (!empty($diagCodes)) {
					$EvnUsluga = $this->getFirstResultFromQuery("
						SELECT eu.EvnUsluga_id as \"EvnUsluga_id\"
						FROM v_EvnUsluga
							inner join v_EvnSection es on es.EvnSection_id = eu.EvnUsluga_pid
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							inner join v_Diag d on d.Diag_id = es.Diag_id
						WHERE
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.PayType_id = :PayTypeOms_id
							and eu.EvnClass_id in (43,22,29,47)
							and uc.UslugaComplex_Code = 'A26.30.004'
							and substring(d.Diag_Code, 1, 3) IN ('" . implode("','", $diagCodes) . "')
							and exists (
								select
									eu2.EvnUsluga_id
								from
									v_EvnUsluga eu2
									inner join v_UslugaComplex uc2 on uc2.UslugaComplex_id = eu2.UslugaComplex_id
								where
									eu2.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
									and eu2.PayType_id = :PayTypeOms_id
									and eu2.EvnClass_id in (43,22,29,47)
									and uc2.UslugaComplex_Code IN ('A11.02.002', 'A11.12.003')
								limit 1
							)
						LIMIT 1
					", [
						'PayTypeOms_id' => $data['PayTypeOms_id']
					]);

					if ($EvnUsluga !== false && !empty($EvnUsluga)) {
						$coeffCTP = $KSLPCodes[668];
						$List[] = [
							'Code' => 668,
							'Value' => $coeffCTP
						];
						if ($EvnSection_CoeffCTP > 0) {
							$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
						} else {
							$EvnSection_CoeffCTP = $coeffCTP;
						}
					}
				}
			}
		}

		// 670. Проведение иммуногистохимического исследования в целях диагностики злокачественных новообразований
		// КСЛП применяется при наличии в движении услуги с действующим на ДКЛ (дата выписки в движении) атрибутом «Диагностика ЗНО».
		if (isset($KSLPCodes[670])) {
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->getFirstResultFromQuery("
					SELECT
						eu.EvnUsluga_id as \"EvnUsluga_id\"
					FROM v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29,47)
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'DiagnZNO'
								and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
								and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
							limit 1
						)
					limit 1
				", [
					'PayTypeOms_id' => $data['PayTypeOms_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				]);

				if ($EvnUsluga !== false && !empty($EvnUsluga)) {
					$coeffCTP = $KSLPCodes[670];
					$List[] = [
						'Code' => 670,
						'Value' => $coeffCTP
					];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}
		
		// 672. Проведение локальной эндоваскулярной трансартериальной тромбэкстрации
		// КСЛП применяется, если одновременно выполняются условия:
		// 1. В движении определилась КСГ = ‘st25.012’;
		// 2. В движении есть услуга A16.23.034.013 «Локальная эндоваскулярная трансартериальная тромбоэкстрация».
		if (isset($KSLPCodes[672])) {
			$MesTariff_id = null;
			if (!empty($data['MesTariff_id'])) {
				$MesTariff_id = $this->getFirstResultFromQuery("
						select
							mt.MesTariff_id
						from
							v_MesTariff mt 
							inner join v_MesOld mo on mo.Mes_id = mt.Mes_id
						where
							MesTariff_id = :MesTariff_id
							and mo.MesOld_Num = 'st25.012'
						limit 1	
					", array(
					'MesTariff_id' => $data['MesTariff_id']
				));
			}
			
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->getFirstResultFromQuery("
					SELECT eu.EvnUsluga_id
					FROM v_EvnUsluga eu 
						inner join v_EvnSection es on es.EvnSection_id = eu.EvnUsluga_pid
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and uc.UslugaComplex_Code = 'A16.23.034.013'
					limit 1	
				", [
					'PayTypeOms_id' => $data['PayTypeOms_id']
				]);
				
			}
			
			if (!empty($MesTariff_id) && !empty($EvnUsluga)) {
				$coeffCTP = $KSLPCodes[672];
				$List[] = [
					'Code' => 672,
					'Value' => $coeffCTP
				];
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}
		
		// КСКП (без КСКП по длительности) не может превышать 1.8
		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		// список КСГ более 45 дней теперь в объёме 2018_КСГ_45
		$ksg45Array = array();
		$resp_mo = $this->queryResult("
			with cte as (
			    select 
			        VolumeType_id as AttributeVision_TablePKey
                from
                    v_VolumeType
                where
                    VolumeType_Code = '2018_КСГ_45'
                limit 1
			)
			
			SELECT
				mo.Mes_Code as \"Mes_Code\"
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
						and a2.Attribute_TableName = 'dbo.MesOld'
					limit 1
				) MESOLD on true
				inner join v_MesOld mo on mo.Mes_id = MESOLD.AttributeValue_ValueIdent
			WHERE
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from cte)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));
		foreach($resp_mo as $one_mo) {
			$ksg45Array[] = $one_mo['Mes_Code'];
		}

		if (
			($data['Duration'] > 30 && !in_array($data['Mes_Code'], $ksg45Array))
			|| $data['Duration'] > 45
		) {
			$normDays = 30;
			if (in_array($data['Mes_Code'], $ksg45Array)) {
				$normDays = 45;
			}

			$coefDl = 0.25;
			$coeffCTP = round((1 + ($data['Duration'] - $normDays) * $coefDl / $normDays), 2);

			// Извращение, определяем код КСЛП.
			if (in_array($data['Mes_Code'], $ksg45Array)) {
				$codeCTP = $data['Duration'] - $normDays + 343;
			} else {
				$codeCTP = $data['Duration'] - $normDays + 8;
			}

			$List[] = array(
				'Code' => $codeCTP,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 2);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
			'List' => $List
		);
	}

	/**
	 * Считаем КСКП для движения в 2018 году
	 */
	protected function calcCoeffCTP2018($data) {
		$EvnSection_CoeffCTP = 0;
		$EvnSection_TreatmentDiff = null;
		$List = array();

		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// КСЛП определять только в круглосуточном стационаре.
			return array(
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
				'List' => $List
			);
		}

		// если связка КСЛП
		$IsFullPricePay = false;
		if (!empty($data['EvnSection_id']) && $data['EvnSection_disDate'] < '2018-06-01') {
			$resp_mouc = $this->queryResult("
				select
					mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\"
				from
					v_EvnSection es
					inner join v_MesOldUslugaComplex mouc on mouc.MesOldUslugaComplex_id = es.MesOldUslugaComplex_id
				where
					es.EvnSection_id = :EvnSection_id
					and mouc.MesOldUslugaComplex_IsFullPricePay = 2
					and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
				limit 1
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			if (!empty($resp_mouc[0]['MesOldUslugaComplex_id'])) {
				$IsFullPricePay = true;
			}
		}

		// Если случай сверхкороткий или прерванный, то коэффициент КСКП не применяется, за исключением тех, что оплачиваются в полном объёме
		if (!$IsFullPricePay && (in_array($data['LeaveType_id'], array(7,9,10,11,13)) || $data['DurationSeconds'] <= 72 * 60 * 60)) {
			return array(
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
				'List' => $List
			);
		}

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			with cte as (
			    select
			        TariffClass_id as AttributeVision_TablePKey
                from
                    v_TariffClass
                where
                    TariffClass_SysNick = 'Kslp'
                limit 1
			) 

			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
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
				and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from cte)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 1. Сложность лечения пациента, связанная с возрастом, при госпитализации детей в возрасте до 1 года (кроме КСГ, относящихся к профилю «неонатология»: КСГ 107-113)
		if (isset($KSLPCodes[1])) {
			if ($data['Person_Age'] < 1) {
				$LpuSectionProfile_id = null;
				if (!empty($data['MesTariff_id'])) {
					$LpuSectionProfile_id = $this->getFirstResultFromQuery("
						select
							lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\"
						from
							v_MesTariff mt
							inner join v_MesLink ml on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
							inner join v_MesOld mokpg on mokpg.Mes_id = ml.Mes_sid
							inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = mokpg.LpuSectionProfile_id
						where
							MesTariff_id = :MesTariff_id
							and lsp.LpuSectionProfile_Code = '55'
						limit 1
					", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}
				if (empty($LpuSectionProfile_id)) {
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
		}

		// 2. Необходимость предоставления спального места и питания одному из родителей, иному члену семьи или иному законному представителю ребенка, при оказании ребенку (в возрасте до 4 лет, или старше - при наличии медицинских показаний)
		if (isset($KSLPCodes[2])) {
			if ($data['EvnSection_IsAdultEscort'] == 2) {
				$coeffCTP = $KSLPCodes[2];
				$List[] = array(
					'Code' => 2,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 3. Сложность лечения пациента, связанная с возрастом (госпитализация детей в возрасте от 1 года до 4 лет)
		if (isset($KSLPCodes[3])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
				$coeffCTP = $KSLPCodes[3];
				$List[] = array(
					'Code' => 3,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		$isGeriatr = false;
		if (!empty($data['MesTariff_id'])) {
			$resp_mt = $this->queryResult("
				select
					mt.MesTariff_id as \"MesTariff_id\" 
				from
					v_MesTariff mt
					inner join v_MesLink ml on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
					inner join v_MesOld mokpg on mokpg.Mes_id = ml.Mes_sid
				where
					mt.MesTariff_id = :MesTariff_id
					and mokpg.Mes_Code = '38' -- Гериатрия 
				limit 1
			", array(
				'MesTariff_id' => $data['MesTariff_id']
			));

			if (!empty($resp_mt[0]['MesTariff_id'])) {
				$isGeriatr = true;
			}
		}

		// 4. Сложность лечения пациента, связанная с возрастом, для лиц старше 75 лет
		if (isset($KSLPCodes[4])) {
			if ($data['Person_Age'] > 75) {
				$coeffCTP = $KSLPCodes[4];
				$List[] = array(
					'Code' => 4,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 5. Сложность лечения пациента старше 60 лет при наличии у него функциональной зависимости (индекс Бартела – 60 баллов и менее)
		if (isset($KSLPCodes[5])) {
			if ($isGeriatr && $data['Person_Age'] >= 60 && isset($data['EvnSection_BarthelIdx']) && $data['EvnSection_BarthelIdx'] <= 60) {
				$coeffCTP = $KSLPCodes[5];
				$List[] = array(
					'Code' => 5,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. Наличие у пациента тяжелой сопутствующей патологии, влияющей на сложность лечения пациента
		if (isset($KSLPCodes[6])) {
			if (!empty($data['EvnSectionIds'])) {
				$AttributeValue = $this->queryResult("
					with cte as (
					    select TariffClass_id as AttributeVision_TablePKey from v_TariffClass where TariffClass_SysNick = 'diagpat' limit 1
					)
	
					SELECT
						av.AttributeValue_id as \"AttributeValue_id\"
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
							limit 1
						) DIAGFILTER on true
						inner join v_EvnDiagPS edps on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
					WHERE
						avis.AttributeVision_TableName = 'dbo.TariffClass'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and edps.DiagSetClass_id in (3) -- обязательно только сопутствующий
						and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					limit 1 
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[6];
					$List[] = array(
						'Code' => 6,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 7. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к различным КСГ
		if (isset($KSLPCodes[7])) {
			$needKSLP7 = false;
			if (!empty($data['EvnSectionIds'])) {
				$checkKSLP7 = $this->queryResult("
					select
						eu1.EvnUsluga_id as \"EvnUsluga_id\"
					from
						v_EvnUsluga eu1
						inner join v_PayType pt1 on pt1.PayType_id = eu1.PayType_id and pt1.PayType_SysNick = 'oms'
						inner join v_UslugaComplexAttribute uca1 on uca1.UslugaComplex_id = eu1.UslugaComplex_id
						inner join v_UslugaComplexAttributeType ucat1 on ucat1.UslugaComplexAttributeType_id = uca1.UslugaComplexAttributeType_id
						left join lateral (
							select
								eu2.EvnUsluga_id
							from
								v_EvnUsluga eu2
								inner join v_PayType pt2 on pt2.PayType_id = eu2.PayType_id and pt2.PayType_SysNick = 'oms'
								inner join v_UslugaComplexAttribute uca2 on uca2.UslugaComplex_id = eu2.UslugaComplex_id
								inner join v_UslugaComplexAttributeType ucat2 on ucat2.UslugaComplexAttributeType_id = uca2.UslugaComplexAttributeType_id
							where
								eu2.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
								and eu2.EvnClass_id in (43,22,29)
								and ucat2.UslugaComplexAttributeType_SysNick IN ('luchter', 'hir')
								and coalesce(uca2.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
								and coalesce(uca2.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
								and eu2.EvnUsluga_id <> eu1.EvnUsluga_id
							limit 1
						) EU2 on true
					where
						eu1.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu1.EvnClass_id in (43,22,29)
						and ucat1.UslugaComplexAttributeType_SysNick IN ('luchter')
						and coalesce(uca1.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(uca1.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					limit 1
				", [
					'EvnSection_disDate' => $data['EvnSection_disDate']
				]);

				if (!empty($checkKSLP7[0]['EvnUsluga_id'])) {
					$needKSLP7 = true;
				}

				if (!$needKSLP7) {
					// ещё одна проверка, по наличию лекарственных схем
					$checkKSLP7 = $this->queryResult("
						SELECT 
							esdts.EvnSectionDrugTherapyScheme_id as \"EvnSectionDrugTherapyScheme_id\"
						FROM
							v_EvnSectionDrugTherapyScheme esdts
						WHERE
							esdts.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
						limit 2
					");

					if (count($checkKSLP7) > 1) { // при выполнении в группе движений двух и более схем лекарственной терапии, в том числе одинаковых
						$needKSLP7 = true;
					}
				}

				if (!$needKSLP7) {
					// ещё одна проверка
					// При наличии в группе движений схемы лекарственной терапии И услуги с атрибутом «64 Лучевая терапия».
					// При наличии в группе движений схемы лекарственной терапии И услуги с атрибутом «66 Хирургическое лечение при злокачественном новообразовании».
					$checkKSLP7 = $this->queryResult("
						select
							esdts.DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
						from
							v_EvnSectionDrugTherapyScheme esdts
							inner join lateral (
								select
									eu2.EvnUsluga_id as \"EvnUsluga_id\"
								from
									v_EvnUsluga eu2
									inner join v_PayType pt2 on pt2.PayType_id = eu2.PayType_id and pt2.PayType_SysNick = 'oms'
									inner join v_UslugaComplexAttribute uca2 on uca2.UslugaComplex_id = eu2.UslugaComplex_id
									inner join v_UslugaComplexAttributeType ucat2 on ucat2.UslugaComplexAttributeType_id = uca2.UslugaComplexAttributeType_id
								where
									eu2.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
									and eu2.EvnClass_id in (43,22,29)
									and ucat2.UslugaComplexAttributeType_SysNick IN ('luchter', 'hir')
									and coalesce(uca2.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
									and coalesce(uca2.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
							) EU2 on true
						where
							esdts.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
						limit 1
					", [
						'EvnSection_disDate' => $data['EvnSection_disDate']
					]);

					if (!empty($checkKSLP7[0]['DrugTherapyScheme_id'])) {
						$needKSLP7 = true;
					}
				}
			}

			if ($needKSLP7) {
				$coeffCTP = $KSLPCodes[7];
				$List[] = array(
					'Code' => 7,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 8. Проведение сочетанных хирургических вмешательств
		if (isset($KSLPCodes[8])) {
			if (!empty($data['EvnSectionIds'])) {
				$AttributeValue = $this->queryResult("
					with cte as (
					    select TariffClass_id as AttributeVision_TablePKey from v_TariffClass where TariffClass_SysNick = 'sochir' limit 1
                    )
	
					SELECT
						av.AttributeValue_id as \"AttributeValue_id\" 
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
						) UC1FILTER on true
						inner join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
						) UC2FILTER on true
					WHERE
						avis.AttributeVision_TableName = 'dbo.TariffClass'
						and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from cte)
						and avis.AttributeVision_IsKeyValue = 2
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and UC2FILTER.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[8];
					$List[] = array(
						'Code' => 8,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 9. Проведение однотипных операций на парных органах
		if (isset($KSLPCodes[9])) {
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->queryResult("
					SELECT
						SUM(coalesce(eu.EvnUsluga_Kolvo, 1)) as sum
					FROM
						v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick IN ('parorg', 'AtngrIssl')
								and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
								and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
							limit 1
						)
				", array(
					'PayTypeOms_id' => $data['PayTypeOms_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
					$coeffCTP = $KSLPCodes[9];
					$List[] = array(
						'Code' => 9,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// КСКП (без КСКП по длительности) не может превышать 1.8
		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		$ksg45Array = array('45', '46', '108', '109', '161', '162', '233', '279', '280', '298');
		if (
			($data['Duration'] > 30 && !in_array($data['Mes_Code'], $ksg45Array))
			|| $data['Duration'] > 45
		) {
			$normDays = 30;
			if (in_array($data['Mes_Code'], $ksg45Array)) {
				$normDays = 45;
			}

			$coefDl = 0.25;
			$coeffCTP = round((1 + ($data['Duration'] - $normDays) * $coefDl / $normDays), 2);

			// Извращение, определяем код КСЛП.
			if (in_array($data['Mes_Code'], $ksg45Array)) {
				if ($data['Duration'] > 100) {
					$codeCTP = 136;
				} else {
					$codeCTP = $data['Duration'] - $normDays + 80;
				}
			} else {
				if ($data['Duration'] > 100) {
					$codeCTP = 80;
				} else {
					$codeCTP = $data['Duration'] - $normDays + 9;
				}
			}

			$List[] = array(
				'Code' => $codeCTP,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 2);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
			'List' => $List
		);
	}

	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		if (empty($data['PayTypeOms_id'])) {
			$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id from v_PayType pt  where pt.PayType_SysNick = 'oms' limit 1");
			if (empty($data['PayTypeOms_id'])) {
				throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
			}
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			return $this->calcCoeffCTP2019($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			return $this->calcCoeffCTP2018($data);
		}

		$EvnSection_CoeffCTP = 1;
		$EvnSection_TreatmentDiff = null;

		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// КСЛП определять только в круглосуточном стационаре.
			return array(
				'EvnSection_CoeffCTP' => 0,
				'EvnSection_TreatmentDiff' => null
			);
		}

		// Если случай сверхкороткий или прерванный, то коэффициент КСКП не применяется.
		if (in_array($data['LeaveType_id'], array(7,9,10,11,13)) || $data['Duration'] <= 3) {
			return array(
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff
			);
		}

		// 8) Для случаев 2017 года. Сложность лечения пациента, связанная с возрастом, при госпитализации детей в возрасте до 1 года (кроме КСГ, относящихся к профилю «неонатология») КСЛП - 1,1.
		if (substr($data['EvnSection_disDate'], 0, 4) >= '2017' && $data['Person_Age'] < 1) {
			$LpuSectionProfile_id = null;
			if (!empty($data['MesTariff_id'])) {
				$LpuSectionProfile_id = $this->getFirstResultFromQuery("
					select
						lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\"
					from
						v_MesTariff mt
						inner join v_MesLink ml on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
						inner join v_MesOld mokpg on mokpg.Mes_id = ml.Mes_sid
						inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = mokpg.LpuSectionProfile_id
					where
						MesTariff_id = :MesTariff_id
						and lsp.LpuSectionProfile_Code = '55'
					limit 1
				", array(
					'MesTariff_id' => $data['MesTariff_id']
				));
			}
			if (empty($LpuSectionProfile_id)) {
				$coeffCTP = 1.1;

				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$EvnSection_CoeffCTP = $coeffCTP;
					$EvnSection_TreatmentDiff = 8;
				}
			}
		}

		// 1) Пребывание ребенка с одним из родителей, иным членом семьи или иным законным представителем в МО, при оказании ему медицинской помощи в стационарных условиях в течение всего периода лечения до достижения ему 4 лет, а с ребенком старше данного возраста при наличии медицинских показаний, оплачивается по тарифам КСГ с использованием КСЛП - 1,1.
		// смотрим по отметке "сопровождение взрослым"
		if ($data['EvnSection_IsAdultEscort'] == 2) {
			$coeffCTP = 1.1;

			if ($coeffCTP > $EvnSection_CoeffCTP) {
				$EvnSection_CoeffCTP = $coeffCTP;
				$EvnSection_TreatmentDiff = 1;
			}
		}

		// 2) В круглосуточном стационаре сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями, оплачиваются по тарифам КСГ с использованием КСЛП (“Коэффициент сверхдлительной госпитализации” – берется из Тарифов и объемов с учетом МО). Критерием отнесения случая к сверхдлительному является госпитализация на срок свыше 30 дней, кроме следующих КСГ, которые считаются сверхдлительными при сроке пребывания более 45 дней (КСГ круглосуточного стационара 44, 45, 105, 106, 142, 143, 216, 216, 279)

		// @task https://redmine.swan.perm.ru/issues/89062
		// Доработать определение коэффициента КСЛП на форме движения. С 01.06.2016 при определении сверхдлительных случаев длительность 45 дней смотрится у следующих КСГ круглосуточного стационара 44, 45, 106, 107, 142, 143, 216, 260, 261, 279 (убрали КСГ 105 и добавили 107).
		$ksg45Array = array('44', '45', '106', '107', '142', '143', '216', '260', '261', '279');
		if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// для 2017 года другие коды.
			$ksg45Array = array('44', '45', '106', '107', '148', '149', '220', '266', '267', '285');
		}

		if (
			($data['Duration'] > 30 && !in_array($data['Mes_Code'], $ksg45Array))
			|| $data['Duration'] > 45
		) {
			// Для случаев с датой окончания после 01.04.2017 вместо значения из тарифа для определения коэффициента используется формула: 1+(ФКД-НКД)/НКД*0,25
			$coeffCTP = 0;

			if (strtotime($data['EvnSection_disDate']) >= strtotime('01.04.2017')) {
				$normDays = 30;
				if (in_array($data['Mes_Code'], $ksg45Array)) {
					$normDays = 45;
				}

				$coefDl = 0.25;

				$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;
			} else {
				// коэфф. берём из тарифов и объёмов
				$resp = $this->queryResult("
					with cte as (
					    select TariffClass_id as AttributeVision_TablePKey from v_TariffClass where TariffClass_SysNick = 'svrgos' limit 1
					)
	
					select
						av.AttributeValue_ValueFloat as \"AttributeValue_ValueFloat\"
					from
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join lateral(
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
								and av2.AttributeValue_ValueIdent = :Lpu_id
							limit 1
						) MOFILTER on true
					where
						avis.AttributeVision_TableName = 'dbo.TariffClass'
						and avis.AttributeVision_TablePKey = (select cte from AttributeVision_TablePKey)
						and avis.AttributeVision_IsKeyValue = 2
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					limit 1
				", array(
					'Lpu_id' => $data['Lpu_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				if (!empty($resp[0]['AttributeValue_ValueFloat'])) {
					$coeffCTP = $resp[0]['AttributeValue_ValueFloat'];
				}
			}

			if ($coeffCTP > $EvnSection_CoeffCTP) {
				$EvnSection_CoeffCTP = $coeffCTP;
				$EvnSection_TreatmentDiff = 2;
			}
		}

		// 3) Сложность лечения пациента, связанная с возрастом, для лиц старше 75 лет оплачивается по тарифам КСГ с использованием КСЛП - 1,05, для лиц 85 лет и старше - с КСЛП - 1,1. (пояснение: старше 75 это 76 и старше)
		if ($data['Person_Age'] > 75) {
			$coeffCTP = 1.05;
			if ($data['Person_Age'] >= 85) {
				$coeffCTP = 1.1;
			}

			if ($coeffCTP > $EvnSection_CoeffCTP) {
				$EvnSection_CoeffCTP = $coeffCTP;
				$EvnSection_TreatmentDiff = 3;
			}
		}

		// 4) Наличие у пациента тяжелой сопутствующей патологии, влияющей на сложность лечения пациента (перечень заболеваний представлен в Приложении №28), оплачивается с применением КСЛП – 1,1. Список диагнозов хранится в “Тарифах и объемах” в тарифе “Диагноз патологии”.
		if (!empty($data['EvnSectionIds'])) {
			$AttributeValue = $this->queryResult("
				with cte as (
				    select TariffClass_id as AttributeVision_TablePKey from v_TariffClass where TariffClass_SysNick = 'diagpat' limit 1
				)

				SELECT
					av.AttributeValue_id as \"AttributeValue_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join lateral (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Diag'
						limit 1
					) DIAGFILTER on true
					inner join v_EvnDiagPS edps on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from cte)
					and avis.AttributeVision_IsKeyValue = 2
					and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and edps.DiagSetClass_id in (3) -- обязательно только сопутствующий
					and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
				limit 1
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));
			if (!empty($AttributeValue[0]['AttributeValue_id'])) {
				$coeffCTP = 1.1;

				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$EvnSection_CoeffCTP = $coeffCTP;
					$EvnSection_TreatmentDiff = 4;
				}
			}
		}

		// 5) Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения (перечень сочетаний видов лечения представлен в Приложении №29) оплачивается по КСГ с наибольшим размером оплаты с применением КСЛП – 1,3.
		// Наличие в одном движении услуг с атрибутами
		// 1. Химиотерапия + Лучевая терапия
		// 2. Химиотерапия + Хирургическое лечение при злокачественном новообразовании
		// 3. Лучевая терапия + Хирургическое лечение при злокачественном новообразовании
		// 4. Лучевая терапия + Лучевая терапия
		if (!empty($data['EvnSectionIds'])) {
			$EvnUsluga = $this->queryResult("
				SELECT
					es.EvnSection_id as \"EvnSection_id\"
				FROM
					v_EvnSection es
					inner join lateral (
						select
							eu.EvnUsluga_id
						from
							v_EvnUsluga eu
							inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eu.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.PayType_id = :PayTypeOms_id
							and eu.EvnClass_id in (43,22,29)
							and ucat.UslugaComplexAttributeType_SysNick IN ('luchter', 'him')
							and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
							and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					) EU1 on true
					inner join lateral (
						select
							eu.EvnUsluga_id
						from
							v_EvnUsluga eu
							inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eu.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.PayType_id = :PayTypeOms_id
							and eu.EvnClass_id in (43,22,29)
							and ucat.UslugaComplexAttributeType_SysNick IN ('luchter', 'hir')
							and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
							and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
							and eu.EvnUsluga_id <> EU1.EvnUsluga_id
					) EU2 on true
				WHERE
					es.EvnSection_id = :EvnSection_id
				limit 1
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));
			if (!empty($EvnUsluga[0]['EvnSection_id'])) {
				$coeffCTP = 1.3;

				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$EvnSection_CoeffCTP = $coeffCTP;
					$EvnSection_TreatmentDiff = 5;
				}
			}
		}

		// 6) Проведение сочетанных хирургических вмешательств (перечень возможных сочетанных операций представлен в Приложении №30) оплачивается с применением КСЛП – 1,2.
		if (!empty($data['EvnSectionIds'])) {
			$AttributeValue = $this->queryResult("
				with cte as (
				    select
				        TariffClass_id as AttributeVision_TablePKey
                    from
                        v_TariffClass
                    where
                        TariffClass_SysNick = 'sochir'
				    limit 1
				)

				SELECT
					av.AttributeValue_id as \"AttributeValue_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join lateral (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
					) UC1FILTER on true
					inner join lateral (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
					) UC2FILTER on true
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from cte)
					and avis.AttributeVision_IsKeyValue = 2
					and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and UC2FILTER.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
				limit 1
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));
			if (!empty($AttributeValue[0]['AttributeValue_id'])) {
				$coeffCTP = 1.2;

				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$EvnSection_CoeffCTP = $coeffCTP;
					$EvnSection_TreatmentDiff = 6;
				}
			}
		}

		// 7) Проведение однотипных операций на парных органах (перечень однотипных операций на парных органах представлен в Приложении №31) оплачивается с применением КСЛП – 1,2. Применяем, если в движении несколько одинаковых услуг (или у услуги стоит количество 2 и более) у которых есть атрибут “Однотипных операций на парных органах”
		if (!empty($data['EvnSection_id'])) {
			$EvnUsluga = $this->queryResult("
				SELECT
					SUM(coalesce(eu.EvnUsluga_Kolvo, 1)) as sum
				FROM
					v_EvnUsluga eu
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and exists(
						select
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca
							inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and ucat.UslugaComplexAttributeType_SysNick = 'parorg'
							and coalesce(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
							and coalesce(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					)
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));
			if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
				$coeffCTP = 1.2;

				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$EvnSection_CoeffCTP = $coeffCTP;
					$EvnSection_TreatmentDiff = 7;
				}
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 2);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff
		);
	}

	/**
	 * поиск ксг/кпг/коэф для 2015 года
	 */
	function loadKSGKPGKOEF2015($data) {
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
			return $this->loadKSGKPGKOEF2019($data);
		} elseif (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEF2018($data);
		} elseif (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEF2017($data);
		} elseif (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEF2016($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select
				coalesce(d4.Diag_pid, d3.Diag_pid) as \"Diag_pid\"
			from
				v_Diag d
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
			where
				d.Diag_id = :Diag_id
			limit 1
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid)) {
			// достаём дату последнего движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT
					es.EvnSection_id as \"EvnSection_id\",
					to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as EvnSection_disDate
				FROM
					v_EvnSection es 
					left join v_Diag d on d.Diag_id = es.Diag_id
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and coalesce(d4.Diag_pid, d3.Diag_pid) = :Diag_pid
				order by
					es.EvnSection_setDate desc
				limit 1
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					if (substr($data['LastEvnSection_disDate'], 0, 4) >= '2016') {
						return $this->loadKSGKPGKOEF2016($data);
					}
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate::timestamp) + 1 as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				WEIGHT.PersonWeight_Weight as \"PersonWeight_Weight\"
			from
				v_PersonState PS
				left join lateral(
					select
						case when pw.Okei_id = 37 then FLOOR(PersonWeight_Weight * 1000) else FLOOR(PersonWeight_Weight) end as PersonWeight_Weight
					from
						v_PersonWeight pw 
					where
						pw.Person_id = ps.person_id and pw.WeightMeasureType_id = 1
					order by
						PersonWeight_setDT desc
					limit 1
				) WEIGHT on true
			where
				ps.Person_id = :Person_id
			limit 1
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['PersonWeight_Weight'] = $resp[0]['PersonWeight_Weight'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		// если вес между 500 и 2500, то
		if (!empty($data['PersonWeight_Weight']) && $data['PersonWeight_Weight'] >= 500 && $data['PersonWeight_Weight'] <= 2500 && $data['Person_AgeDays'] <= 28) {
			if ($data['PersonWeight_Weight'] >= 1501) {
				// от 1 501 до 2 500 гр. КСГ 90
				$data['ChildMes_Code'] = '90';
			} else {
				// от 500 до 1 500 гр. КСГ 91
				$data['ChildMes_Code'] = '91';
			}

			$query = "
				select
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
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
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mo.Mes_Code = :ChildMes_Code
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				limit 1
			";

			$resp_ksg = $this->queryResult($query, $data);
			if (!empty($resp_ksg[0])) {
				$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
				$response['KSG'] = $resp_ksg[0]['KSG'];
				$response['Mes_tid'] = $resp_ksg[0]['Mes_id'];
				$response['KPG'] = $resp_ksg[0]['KPG'];
				$response['Mes_kid'] = $resp_ksg[0]['Mes_kid'];
				$response['KOEF'] = $resp_ksg[0]['KOEF'];
				$response['MesTariff_id'] = $resp_ksg[0]['MesTariff_id'];

				if (!empty($response['KOEF'])) {
					$response['KOEF'] = round($response['KOEF'], 3);
				}

				return $response;
			}
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
								mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
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
										mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
									from
										v_MesOld mo2
										inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								) mokpg on true
							where
								mo.Mes_Code = '192'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
						and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join lateral(
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
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
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and (mo.Mes_Code != '90' or (:PersonWeight_Weight >= 1501 and :PersonWeight_Weight <= 2500))
					and (mo.Mes_Code != '91' or (:PersonWeight_Weight >= 500 and :PersonWeight_Weight <= 1500))
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
						mokpg.Mes_Code || coalesce('. ' || mokpg.Mes_Name, '') as \"KPG\",
						mokpg.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"KOEF\",
						mtkpg.MesTariff_id as \"MesTariff_id\"
					from MesOld mokpg
						left join v_MesTariff mtkpg on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
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

		return $response;
	}
	
	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF($data) {
		$KSGFromPolyTrauma = false;
		$KSGFromUsluga = false;
		$KSGFromDiag = false;

		$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id from v_PayType pt  where pt.PayType_SysNick = 'oms' limit 1");
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

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2015') {
			// алгоритм с 2015 года
			return $this->loadKSGKPGKOEF2015($data);
		}
		
		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate) + 1 as \"Person_AgeDays\",
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
		
		if (!empty($data['EvnSection_disDate'])) {
			// 0.	Определение КСГ при политравме
			if (!empty($data['EvnSection_id']) && strtotime($data['EvnSection_disDate']) >= strtotime('01.04.2014')) {
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
								and pt2.PolyTrauma_begDT <= :EvnSection_disDate
								and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							limit 1
						) SOPUT on true
					where
						pt.Diag_id = :Diag_id
						and pt.PolyTrauma_begDT <= :EvnSection_disDate
						and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and exists(
							select
								edps.EvnDiagPS_id
							from
								v_EvnDiagPS edps
								inner join v_Diag d on d.Diag_id = edps.Diag_id
							where
								edps.DiagSetClass_id IN (2,3)
								and edps.EvnDiagPS_pid = :EvnSection_id
								and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4')
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
									mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
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
											mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
										from
											v_MesOld mo2
											inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id
										where
											ml.Mes_id = mo.Mes_id
											and mo2.Mes_begDT <= :EvnSection_disDate
											and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										limit 1
									) mokpg on true
								where
									mo.Mes_Code = '127' and mo.MesType_id IN (2,3,5) -- КСГ
									and mo.Mes_begDT <= :EvnSection_disDate
									and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
		}
		
		// 1.	Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id and mo.MesType_id IN (2,3,5) -- КСГ
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
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
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by 
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGFromUsluga = $resp[0];
				}
			}
		}
		// 2.	Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mokpg.KPG as \"KPG\",
					mokpg.Mes_kid as \"Mes_kid\"
				from v_MesOldDiag mod
					inner join v_MesOld mo on mo.Mes_id = mod.Mes_id and mo.MesType_id IN (2,3,5) -- КСГ
					left join v_Diag d on d.Diag_id = mod.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
						from
							v_MesOld mo2
							inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) mokpg on true
				where
					mod.Diag_id = :Diag_id
					and (mod.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mod.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mod.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mod.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mod.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mod.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mod.MesAgeGroup_id = 6)
						or (mod.MesAgeGroup_id IS NULL)
					)
					and mod.MesOldDiag_begDT <= :EvnSection_disDate
					and (mod.MesOldDiag_endDT >= :EvnSection_disDate OR mod.MesOldDiag_endDT IS NULL)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and (
						mo.Mes_Code NOT IN ('74','75','76','31')
						OR
						exists (
							select
								EvnUsluga_id
							from v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where uc.UslugaComplex_Code in ('A25.30.014', 'A25.30.032')
								and eu.EvnUsluga_pid = :EvnSection_id
								and eu.PayType_id = :PayTypeOms_id
							limit 1
						)
					)
				order by 
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGFromDiag = $resp[0];
					
					if ( in_array($resp[0]['Diag_Code'], array('I20.1', 'I20.8', 'I20.9', 'I24.0', 'I24.1', 'I24.8', 'I24.9', 'I25.0', 'I25.1', 'I25.2', 'I25.3', 'I25.4', 'I25.5', 'I25.6', 'I25.8', 'I25.9')) ) {
						// проверяем наличие A06.10.006
						$EvnUsluga_id = $this->getFirstResultFromQuery("select EvnUsluga_id from v_EvnUsluga eu inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A06.10.006') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id limit 1", array('EvnSection_id' => $data['EvnSection_id'], 'PayTypeOms_id' => $data['PayTypeOms_id']));
						// если нашли, то берем ксг по ней 107
						if (!empty($EvnUsluga_id)) {
							$query = "
								select
									mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
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
											mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
										from
											v_MesOld mo2
											inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id
										where
											ml.Mes_id = mo.Mes_id
											and mo2.Mes_begDT <= :EvnSection_disDate
											and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										limit 1
									) mokpg on true
								where
									mo.Mes_Code = '107' and mo.MesType_id IN (2,3,5) -- КСГ
									and mo.Mes_begDT <= :EvnSection_disDate
									and (coalesce(mo.Mes_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								limit 1
							";
							
							$result = $this->db->query($query, $data);
							if (is_object($result)) {
								$resp_ksg = $result->result('array');
								if (count($resp_ksg) > 0) {
									$KSGFromDiag = $resp_ksg[0];
								}
							}
						}
					}
					if ( in_array($resp[0]['Diag_Code'], array('I20.0', 'I21.0', 'I21.1', 'I21.2', 'I21.3', 'I21.4', 'I21.9', 'I22.0', 'I22.1', 'I22.8', 'I22.9')) ) {
						// проверяем наличие A16.12.004.009
						$EvnUsluga_id = $this->getFirstResultFromQuery("select EvnUsluga_id from v_EvnUsluga eu inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A16.12.004.009') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id limit 1", array('EvnSection_id' => $data['EvnSection_id'], 'PayTypeOms_id' => $data['PayTypeOms_id']));
						// если нашли, то берем ксг по ней 109
						if (!empty($EvnUsluga_id)) {
							$query = "
								select
									mo.Mes_Code || coalesce('. ' || mo.Mes_Name, '') as \"KSG\",
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
											mo2.Mes_Code || coalesce('. ' || mo2.Mes_Name, '') as KPG
										from
											v_MesOld mo2
											inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id
										where
											ml.Mes_id = mo.Mes_id
											and mo2.Mes_begDT <= :EvnSection_disDate
											and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										limit 1
									) mokpg on true
								where
									mo.Mes_Code = '109' and mo.MesType_id IN (2,3,5) -- КСГ
									and mo.Mes_begDT <= :EvnSection_disDate
									and (coalesce(mo.Mes_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								limit 1
							";
							
							$result = $this->db->query($query, $data);
							if (is_object($result)) {
								$resp_ksg = $result->result('array');
								if (count($resp_ksg) > 0) {
									$KSGFromDiag = $resp_ksg[0];
								}
							}
						}
					}
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
		
		if ($KSGFromUsluga && $KSGFromDiag) {
			$response['Mes_tid'] = $KSGFromDiag['Mes_id'];
			$response['Mes_sid'] = $KSGFromUsluga['Mes_id'];
			if ($KSGFromUsluga['KOEF'] > $KSGFromDiag['KOEF']) {
				$response['KSG'] = $KSGFromUsluga['KSG'];
				$response['KPG'] = $KSGFromUsluga['KPG'];
				$response['Mes_kid'] = $KSGFromUsluga['Mes_kid'];
				$response['KOEF'] = $KSGFromUsluga['KOEF'];
				$response['MesTariff_id'] = $KSGFromUsluga['MesTariff_id'];
			} else {
				$response['KSG'] = $KSGFromDiag['KSG'];
				$response['KPG'] = $KSGFromDiag['KPG'];
				$response['Mes_kid'] = $KSGFromDiag['Mes_kid'];
				$response['KOEF'] = $KSGFromDiag['KOEF'];
				$response['MesTariff_id'] = $KSGFromDiag['MesTariff_id'];
			}
		} else if ($KSGFromUsluga) {
			$response['KSG'] = $KSGFromUsluga['KSG'];
			$response['Mes_sid'] = $KSGFromUsluga['Mes_id'];
			$response['KPG'] = $KSGFromUsluga['KPG'];
			$response['Mes_kid'] = $KSGFromUsluga['Mes_kid'];
			$response['KOEF'] = $KSGFromUsluga['KOEF'];
			$response['MesTariff_id'] = $KSGFromUsluga['MesTariff_id'];
		} else if ($KSGFromDiag) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGFromDiag['KSG'];
			$response['Mes_tid'] = $KSGFromDiag['Mes_id'];
			$response['KPG'] = $KSGFromDiag['KPG'];
			$response['Mes_kid'] = $KSGFromDiag['Mes_kid'];
			$response['KOEF'] = $KSGFromDiag['KOEF'];
			$response['MesTariff_id'] = $KSGFromDiag['MesTariff_id'];
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
		
		return $response;
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


		if ($year > 2016) {
			// пересчитываем КСГ во всех сгруппированных движения КВС
			$resp_es = $this->recalcIndexNumByGroup(array(
				'getGroupsOnly' => true
			));
			if (!empty($resp_es)) {
				$this->load->model('EvnSection_model', 'es_model');

				$groupForKSGRecalc = array();
				foreach($resp_es as $one_es) {
					if (!empty($one_es['groupNum'])) {
						if (empty($groupForKSGRecalc[$one_es['groupNum']])) {
							$groupForKSGRecalc[$one_es['groupNum']] = array(
								'EvnSectionIds' => array(),
								'EvnSection_setDate' => $one_es['EvnSection_setDate'],
								'EvnSection_disDate' => $one_es['EvnSection_disDate']
							);
						} else {
							if ($groupForKSGRecalc[$one_es['groupNum']]['EvnSection_disDate'] < $one_es['EvnSection_disDate']) {
								$groupForKSGRecalc[$one_es['groupNum']]['EvnSection_disDate'] = $one_es['EvnSection_disDate'];
							}
							if ($groupForKSGRecalc[$one_es['groupNum']]['EvnSection_setDate'] > $one_es['EvnSection_setDate']) {
								$groupForKSGRecalc[$one_es['groupNum']]['EvnSection_setDate'] = $one_es['EvnSection_setDate'];
							}
						}

						$groupForKSGRecalc[$one_es['groupNum']]['EvnSectionIds'][] = $one_es['EvnSection_id'];
					}
				}

				foreach($resp_es as $one_es) {
					if (!empty($one_es['groupNum'])) {
						// пересчитываем КСГ по каждому сгруппированному движению
						$this->es_model->reset();
						$this->es_model->recalcKSGKPGKOEF($one_es['EvnSection_id'], $this->sessionParams, array(
							'EvnSection_setDate' => $groupForKSGRecalc[$one_es['groupNum']]['EvnSection_setDate'],
							'EvnSection_disDate' => $groupForKSGRecalc[$one_es['groupNum']]['EvnSection_disDate'],
							'EvnSectionIds' => $groupForKSGRecalc[$one_es['groupNum']]['EvnSectionIds']
						));
					}
				}

				return true;
			}
		} else if ($year > 2015) {
			$this->load->model('EvnSection_model', 'es_model');

			// группировка по диагнозу
			$Diag_pid = $this->getFirstResultFromQuery("
				select
					coalesce(d4.Diag_pid, d3.Diag_pid) as Diag_pid
				from
					v_Diag d
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				where
					d.Diag_id = :Diag_id
				limti 1
			", array(
				'Diag_id' => $this->Diag_id
			));

			if (!empty($Diag_pid)) {
				// достаём движения с той же категорией диагнозов, что и в текущем движении и годом меньше
				$query = "
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es 
						left join v_Diag d on d.Diag_id = es.Diag_id
						left join v_Diag d2 on d2.Diag_id = d.Diag_pid
						left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
						left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
					WHERE
						es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
						and date_part('year', coalesce(es.EvnSection_disDate, es.EvnSection_setDate)) < :Year
						and coalesce(d4.Diag_pid, d3.Diag_pid) = :Diag_pid
				";

				$result = $this->db->query($query, array(
					'EvnSection_id' => $this->id,
					'EvnSection_pid' => $this->pid,
					'Diag_pid' => $Diag_pid,
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
	}

	/**
	 * Перегруппировка движений для всей КВС
	 * @task https://redmine.swan.perm.ru/issues/90346
	 */
	protected function _recalcIndexNum()
	{
		$this->recalcIndexNumByGroup();
	}

	/**
	 * Перегруппировка движений для всей КВС
	 * @task https://redmine.swan.perm.ru/issues/90346
	 */
	protected function recalcIndexNumByGroup($data = array())
	{
		if (empty($data['getGroupsOnly'])) {
			// убираем признаки со всех движений КВС
			$query = "
				update
					EvnSection
				set
					EvnSection_IndexNum = null,
					EvnSection_IsWillPaid = null
				where
					Evn_pid = :EvnSection_pid
			";
			$this->db->query($query, array(
				'EvnSection_pid' => $this->pid
			));
		}

		// движения группируются:
		// 1. по реанимации (профиль 5. Анестезиология и реаниматология)
		// 2. по коду группы отделений 999
		// 3. по диагнозу
		// 4. исключаются случаи 158, т.е. идут отдельной группой

		// а движение в приёмном тоже учитывать в группировке ? => если есть профильные движения, то нет
		// а еще надо группировать только ОМС даижения и у которых заведено финансирование ОМС

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lu.LpuUnit_Code as \"LpuUnit_Code\",
				d.Diag_Code as \"Diag_Code\",
				coalesce(d4.Diag_Code, d3.Diag_Code) as \"DiagGroup_Code\",
				to_char(es.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnSection_setDate\",
				to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\",
				case when mtmes.MesType_id <> 4 then mtmes.Mes_Code else '' end as \"EvnSection_KSG\"
			from
				v_EvnSection es
				inner join v_PayType pt on pt.PayType_id = es.PayType_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join Diag d on d.Diag_id = es.Diag_id
				left join Diag d2 on d2.Diag_id = d.Diag_pid
				left join Diag d3 on d3.Diag_id = d2.Diag_pid
				left join Diag d4 on d4.Diag_id = d3.Diag_pid
				left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mtmes on mtmes.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and coalesce(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
				and exists (
					select
						LpuSectionFinans_id
					from
						LpuSectionFinans  
						inner join PayType  on PayType.PayType_id = LpuSectionFinans.PayType_id
					where
						LpuSectionFinans.LpuSection_id = es.LpuSection_id
						and LpuSectionFinans_begDate <= es.EvnSection_disDate
						and (LpuSectionFinans_endDate >= es.EvnSection_disDate or LpuSectionFinans_endDate is null)
						and PayType.PayType_SysNick = 'oms'
					limit 1
				)
			order by
				es.EvnSection_setDT
		", array(
			'EvnSection_pid' => $this->pid
		));

		$groupNum = 0; // счётчик групп

		// 4. исключаются случаи 158, т.е. идут отдельной группой
		foreach($resp_es as $key => $value) {
			if ($value['LpuSectionProfile_Code'] == '158') {
				$groupNum++;
				$resp_es[$key]['groupNum'] = $groupNum;
			}
		}

		// 1. по реанимации (профиль 5. Анестезиология и реаниматология)
		// Движение в отделении ИТАР группируется с движением, которое идет перед ним, а если оно было первым в КВС, то с движением, которое было после него.
		$needGroup = false;
		$predKey = null; // ключ предыдущего движения
		foreach($resp_es as $key => $value) {
			if (!empty($value['groupNum'])) {
				$predKey = $key;
				$needGroup = false;
				continue; // пропускаем те, что уже с группой
			}

			if ($needGroup) { // предыдущее было по реанимации
				if ( empty($resp_es[$predKey]['groupNum']) ) {
					$groupNum++;
					$resp_es[$predKey]['groupNum'] = $groupNum;
				}

				$resp_es[$key]['groupNum'] = $resp_es[$predKey]['groupNum'];

				$predKey = $key;
				$needGroup = ($value['LpuSectionProfile_Code'] != '5');
				continue; // пропускаем те, что уже с группой
			}

			if ($value['LpuSectionProfile_Code'] == '5') {
				if (!is_null($predKey)) {
					// если у предыдущего ещё нет группы, то группируем их
					if (empty($resp_es[$predKey]['groupNum'])) {
						$groupNum++;
						$resp_es[$key]['groupNum'] = $groupNum;
						$resp_es[$predKey]['groupNum'] = $groupNum;
					}
					// случай, когда подряд идут 2 и более движений ИТАР
					else if ($resp_es[$predKey]['LpuSectionProfile_Code'] == '5' ) {
						$resp_es[$key]['groupNum'] = $resp_es[$predKey]['groupNum'];
					}
				} else {
					// надо будет сгруппировать следующее движение с этим.
					$needGroup = true;
				}
			}

			$predKey = $key;
		}
		
		if (empty($data['getGroupsOnly'])) {
			// a.	При лечении по КСГ 2 продолжительностью  48 часов и более (длительность именно движения с КСГ 2) с указанием диагноза в отделении патологии (один из): О14.1, О34.2, О36.3, О36.4, О42.2. При этом учитывается длительность движения с КСГ 2,
			// b.	При лечении по КСГ 2 для остальных диагнозов продолжительностью 144 часа и более (длительность именно движения с КСГ 2)
			// то движение с КСГ выделяется 2 в отдельную группу. Следовательно, случай движение с КСГ 2 оплачивается (выгружается тариф и сумма) отдельно.
			// иначе движение с КСГ 2 группируется вместе с движением с КСГ 4, 5
			$needGroup = 0;
			$predKey = null; // ключ предыдущего движения
			foreach ($resp_es as $key => $value) {
				if (strtotime($value['EvnSection_disDate']) < strtotime('01.03.2017')) {
					continue; // пропускаем до 01.03.2017
				}

				$diag_array = array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2');
				$datediff = strtotime($value['EvnSection_disDate']) - strtotime($value['EvnSection_setDate']);
				$Duration = floor($datediff / 60 * 60);

				if (!empty($value['groupNum'])) {
					$needGroup = 0;
					if ($value['EvnSection_KSG'] == 2) {
						$needGroup = 1;
						if (
							(in_array($value['Diag_Code'], $diag_array) && $Duration > 48)
							|| $Duration >= 144
						) {
							$needGroup = 2;
						}
					}
					$predKey = $key;
					continue; // пропускаем те, что уже с группой
				}

				if (!empty($needGroup)) { // предыдущее было с КСГ2
					if (
						empty($resp_es[$key]['groupNum']) &&
						in_array($value['EvnSection_KSG'], array('4', '5', '107', '108', '109'))
					) {
						if (empty($resp_es[$predKey]['groupNum'])) {
							$groupNum++;
							$resp_es[$predKey]['groupNum'] = $groupNum; // КСГ 2 в отдельную группу
							if ($needGroup == 1) {
								$resp_es[$key]['groupNum'] = $groupNum; // текущее движение групируем с КСГ 2
							}
						}
					}

					$predKey = $key;
					$needGroup = 0;
					continue;
				}

				$needGroup = 0;
				if ($value['EvnSection_KSG'] == 2) {
					$needGroup = 1;
					if (
						(in_array($value['Diag_Code'], $diag_array) && $Duration > 48)
						|| $Duration >= 144
					) {
						$needGroup = 2;
					}
				}

				$predKey = $key;
			}
		}
		
		// 2. по коду группы отделений 999, Если в КВС подряд идут несколько движений в отделениях, которые относятся к группе отделений с кодом “999”, то такие движения группируются в одну группу.
		$mkbGroups = array();
		$inOrder = false; // признак подряд идущих движений с кодом 999 => должны попасть в одну группу.
		$needGroup = false;
		$predKey = null; // ключ предыдущего движения
		foreach($resp_es as $key => $value) {
			if (!empty($value['groupNum'])) {
				if ($needGroup && !empty($resp_es[$predKey]['groupNum']) && !empty($resp_es[$predKey]['DiagGroup_Code'])) {
					// по диагнозу из последнего движения группы 999 тоже идёт группировка
					$mkbGroups[$resp_es[$predKey]['DiagGroup_Code']] = $resp_es[$predKey]['groupNum'];
				}
				$predKey = $key;
				$inOrder = false;
				$needGroup = false;
				continue; // пропускаем те, что уже с группой
			}

			if ($value['LpuUnit_Code'] == '999') {
				if ($needGroup) { // предыдущее было с кодом 999
					// если у текущего ещё нет группы, то группируем их
					if (!$inOrder) {
						$groupNum++;
					}
					$resp_es[$key]['groupNum'] = $groupNum;
					$resp_es[$predKey]['groupNum'] = $groupNum;
					$inOrder = true;
				}
				// надо будет сгруппировать следующее движение с этим.
				$needGroup = true;
			} else {
				if ($needGroup && !empty($resp_es[$predKey]['groupNum']) && !empty($resp_es[$predKey]['DiagGroup_Code'])) {
					// по диагнозу из последнего движения группы 999 тоже идёт группировка
					$mkbGroups[$resp_es[$predKey]['DiagGroup_Code']] = $resp_es[$predKey]['groupNum'];
				}
				$inOrder = false;
				$needGroup = false;
			}

			$predKey = $key;
		}

		if (!empty($data['getGroupsOnly'])) {
			return $resp_es;
		}

		if ($needGroup && !empty($resp_es[$predKey]['groupNum']) && !empty($resp_es[$predKey]['DiagGroup_Code'])) {
			// по диагнозу из последнего движения группы 999 тоже идёт группировка
			$mkbGroups[$resp_es[$predKey]['DiagGroup_Code']] = $resp_es[$predKey]['groupNum'];
		}

		// 3. по диагнозу, группируются по классу МКБ вне зависимости от следования
		foreach($resp_es as $key => $value) {
			if (!empty($value['groupNum'])) {
				continue; // пропускаем те, что уже с группой
			}

			if (empty($value['DiagGroup_Code'])) { // без группы диагнозов в отдельную группу.
				$groupNum++;
				$resp_es[$key]['groupNum'] = $groupNum;
			}

			if (empty($mkbGroups[$value['DiagGroup_Code']])) {
				$groupNum++;
				$mkbGroups[$value['DiagGroup_Code']] = $groupNum;
			}

			$resp_es[$key]['groupNum'] = $mkbGroups[$value['DiagGroup_Code']];
		}

		// Проставляем isWillPaid последним движениям группы
		$paidArray = array();
		foreach($resp_es as $key => $value) {
			$paidArray[$value['groupNum']] = $value['EvnSection_id'];
		}

		// Апедйт в БД
		foreach($resp_es as $key => $value) {
			$this->db->query("
				update
					EvnSection
				set
					EvnSection_IndexNum = :EvnSection_IndexNum,
					EvnSection_IsWillPaid = :EvnSection_IsWillPaid
				where
					Evn_id = :EvnSection_id				
			", array(
				'EvnSection_IndexNum' => $value['groupNum'],
				'EvnSection_IsWillPaid' => ($paidArray[$value['groupNum']] == $value['EvnSection_id']?2:1),
				'EvnSection_id' => $value['EvnSection_id']
			));
		}
	}

	/**
	 * Пересчёт КСКП для всей КВС
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
				from p_EvnSectionKSLPLink_del
				(
					EvnSectionKSLPLink_id := :EvnSectionKSLPLink_id
				)
			", array(
				'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
			));
		}

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as \"Person_Age\",
				es.EvnSection_setDT as \"EvnSection_setDT\",
				coalesce(es.EvnSection_disDT, es.EvnSection_setDT) as \"EvnSection_disDT\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lu.LpuUnit_Code as \"LpuUnit_Code\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				es.EvnSection_BarthelIdx as \"EvnSection_BarthelIdx\",
				es.Lpu_id as \"Lpu_id\",
				mt.MesTariff_Value as \"MesTariff_Value\",
				mt.MesTariff_id as \"MesTariff_id\",
				mo.Mes_Code as \"Mes_Code\",
				es.LeaveType_id as \"LeaveType_id\",
				es.EvnSection_IsAdultEscort as \"EvnSection_IsAdultEscort\",
				es.EvnSection_IndexNum as \"EvnSection_IndexNum\"
			from
				v_EvnSection es
				inner join v_PayType pt on pt.PayType_id = es.PayType_id
				left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_PersonState ps on ps.Person_id = es.Person_id
				left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo on mo.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and coalesce(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
			order by
				es.EvnSection_setDT
		", array(
			'EvnSection_pid' => $this->pid
		));

		$groupped = array(); // группировка для КСЛП
		foreach($resp_es as $respone) {
			$key = $respone['EvnSection_IndexNum'];

			if (empty($key)) {
				$key = 'id_' . $respone['EvnSection_id']; // в отдельную группу, чтобы посчитать и им КСЛП.
			}

			$respone['EvnSection_CoeffCTP'] = 1;
			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;
			$groupped[$key]['MaxCoeff']['Lpu_id'] = $respone['Lpu_id'];

			// Возраст человека берём из первого движения группы, т.е. минимальный
			if (empty($groupped[$key]['MaxCoeff']['Person_Age']) || $groupped[$key]['MaxCoeff']['Person_Age'] > $respone['Person_Age']) {
				$groupped[$key]['MaxCoeff']['Person_Age'] = $respone['Person_Age'];
			}

			// Дату начала движений из первого движения
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_setDT']) || $groupped[$key]['MaxCoeff']['EvnSection_setDT'] > $respone['EvnSection_setDT']) {
				$groupped[$key]['MaxCoeff']['EvnSection_setDT'] = $respone['EvnSection_setDT'];
			}

			// Дату окончания движений из последнего движения
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_disDT']) || $groupped[$key]['MaxCoeff']['EvnSection_disDT'] < $respone['EvnSection_disDT']) {
				$groupped[$key]['MaxCoeff']['LastEvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_disDT'] = $respone['EvnSection_disDT'];
				$groupped[$key]['MaxCoeff']['LeaveType_id'] = $respone['LeaveType_id']; // исход из последнего движения группы
			}

			// если есть хотя бы на одном из группы
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort']) || $respone['EvnSection_IsAdultEscort'] == 2) {
				$groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort'] = $respone['EvnSection_IsAdultEscort'];
			}

			// КСГ с движения с наибольшим коэффициентом / если коэфф тот же, то с наибольшей датой начала
			if (
				empty($groupped[$key]['MaxCoeff']['MesTariff_Value'])
				|| $groupped[$key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
				|| ($groupped[$key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && $groupped[$key]['MaxCoeff']['EvnSection_setDT'] < $respone['EvnSection_setDT'])
			) {
				$groupped[$key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$key]['MaxCoeff']['LpuSectionProfile_Code'] = $respone['LpuSectionProfile_Code'];
				$groupped[$key]['MaxCoeff']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_BarthelIdx'] = $respone['EvnSection_BarthelIdx'];
				$groupped[$key]['MaxCoeff']['Mes_Code'] = $respone['Mes_Code'];
				$groupped[$key]['MaxCoeff']['MesTariff_id'] = $respone['MesTariff_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_IndexNum'] = $respone['EvnSection_IndexNum'];
			}


			if (!empty($respone['EvnSection_disDT'])) {
				// считаем без учета времени refs #156319
				$disDate = ConvertDateFormat($respone['EvnSection_disDT'],'Y-m-d');
				$setDate = ConvertDateFormat($respone['EvnSection_setDT'],'Y-m-d');
				$datediff = strtotime($disDate) - strtotime($setDate);
				$Duration = floor($datediff/(60*60*24));
			} else {
				$datediff = 0;
				$Duration = 0;
			}
			if (empty($groupped[$key]['MaxCoeff']['Duration'])) {
				$groupped[$key]['MaxCoeff']['Duration'] = 0;
			}
			$groupped[$key]['MaxCoeff']['Duration'] += $Duration;
			if (empty($groupped[$key]['MaxCoeff']['DurationSeconds'])) {
				$groupped[$key]['MaxCoeff']['DurationSeconds'] = 0;
			}
			$groupped[$key]['MaxCoeff']['DurationSeconds'] += $datediff;
		}

		// для каждого движения группы надо выбрать движение с наибольшим КСГ.
		foreach($groupped as $key => $group) {
			$EvnSectionIds = array();
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
			}
			$groupped[$key]['MaxCoeff']['EvnSectionIds'] = $EvnSectionIds; // все джвижения группы

			$groupped[$key]['MaxCoeff']['EvnSection_setDate'] = ConvertDateFormat($group['MaxCoeff']['EvnSection_setDT'],'Y-m-d');
			$groupped[$key]['MaxCoeff']['EvnSection_disDate'] = ConvertDateFormat($group['MaxCoeff']['EvnSection_disDT'],'Y-m-d');
		}

		foreach($groupped as $group) {
			// считаем КСЛП для каждого движения группы
			foreach($group['EvnSections'] as $es) {
				$esdata = array(
					'EvnSection_id' => $es['EvnSection_id'],
					'EvnSectionIds' => array($es['EvnSection_id']),
					'Lpu_id' => $es['Lpu_id'],
					'LpuSectionProfile_Code' => $es['LpuSectionProfile_Code'],
					'LpuUnitType_id' => $es['LpuUnitType_id'],
					'EvnSection_BarthelIdx' => $es['EvnSection_BarthelIdx'],
					'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
					'Person_Age' => $es['Person_Age'],
					'Duration' => $group['MaxCoeff']['Duration'],
					'DurationSeconds' => $group['MaxCoeff']['DurationSeconds'],
					'Mes_Code' => $es['Mes_Code'],
					'LeaveType_id' => $group['MaxCoeff']['LeaveType_id'],
					'MesTariff_id' => $es['MesTariff_id'],
					'EvnSection_IsAdultEscort' => $es['EvnSection_IsAdultEscort']
				);

				$kslp = $this->calcCoeffCTP($esdata);

				$query = "
					update
						EvnSection
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP,
						EvnSection_TreatmentDiff = :EvnSection_TreatmentDiff
					where
						Evn_id = :EvnSection_id
				";

				$this->db->query($query, array(
					'EvnSection_CoeffCTP' => $kslp['EvnSection_CoeffCTP'],
					'EvnSection_TreatmentDiff' => $kslp['EvnSection_TreatmentDiff'],
					'EvnSection_id' => $es['EvnSection_id']
				));

				foreach ($kslp['List'] as $one_kslp) {
					$this->db->query("
                        select 
                            EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\",
                            Error_Code as \"Error_Code\", 
                            Error_Message as \"Error_Msg\"
						from p_EvnSectionKSLPLink_ins
						(
							EvnSection_id := :EvnSection_id,
							EvnSectionKSLPLink_Code := cast( :EvnSectionKSLPLink_Code as varchar),
							EvnSectionKSLPLink_Value := :EvnSectionKSLPLink_Value,
							pmUser_id := :pmUser_id
						)
					", array(
						'EvnSection_id' => 0 + $es['EvnSection_id'],
						'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
						'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
						'pmUser_id' => 0 + $this->promedUserId
					));
				}
			}
		}
	}
}
