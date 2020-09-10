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
* @version			Krym
*/

require_once(APPPATH.'models/EvnSection_model.php');

class Krym_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	function loadKSGKPGKOEF2019($data) {
		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Code' => '', 'success' => true);
		
		// группировка
		if (!empty($data['EvnSection_id'])) {
			$this->pid = $data['EvnSection_pid'];
			$resp_es = $this->recalcIndexNumByGroup(array(
				'getGroupsOnly' => true
			));
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
	 */
	function loadKSGKPGKOEF2018($data) {
		$KSGException = false;
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

		if (strtotime($data['EvnSection_disDate']) >= strtotime('25.12.2018')) {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEF2019($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT top 1
				es.EvnSection_id,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
			FROM
				v_EvnSection es with (nolock)
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) desc
		";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
				if (strtotime($data['LastEvnSection_disDate']) >= strtotime('25.12.2018')) {
					return $this->loadKSGKPGKOEF2019($data);
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
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id,
				case
					when MONTH(PS.Person_BirthDay) = MONTH(:EvnSection_setDate) and DAY(PS.Person_BirthDay) = DAY(:EvnSection_setDate) then 1 else 0
				end as BirthToday
			from
				v_PersonState PS (nolock)
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

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && !empty($data['EvnSection_id'])) {
			// ищем услугу A16.20.037
			$resp = $this->queryResult("
				select top 1
					EvnUsluga_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and uc.UslugaComplex_Code = 'A16.20.037'
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));

			if (!empty($resp[0]['EvnUsluga_id'])) {
				// всегда определяется КСГ 6
				$query = "
					select top 1
						mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
						mo.Mes_id,
						mt.MesTariff_Value as KOEF,
						mt.MesTariff_id
					from
						v_MesOld mo (nolock)
						left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mo.Mes_Code = '6'
						and mo.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
						and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				";

				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KSGException = $resp[0];
					}
				}
			}
		} else if (!in_array($data['LpuUnitType_id'], array('6','7','9')) && !empty($data['EvnSection_id'])) {
			// ищем услугу A16.20.005
			$resp = $this->queryResult("
				select top 1
					EvnUsluga_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and uc.UslugaComplex_Code = 'A16.20.005'
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));

			if (!empty($resp[0]['EvnUsluga_id'])) {
				// всегда определяется КСГ 5
				$query = "
					select top 1
						mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
						mo.Mes_id,
						mt.MesTariff_Value as KOEF,
						mt.MesTariff_id
					from
						v_MesOld mo (nolock)
						left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mo.Mes_Code = '5'
						and mo.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
						and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				";

				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KSGException = $resp[0];
					}
				}
			}
		}

		// 0.	Определение КСГ при политравме
		if (empty($KSGException) && !empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) SOPUT
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '233'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
		if (empty($KSGException) && !empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					case when RehabKPG.cnt > 0 then 1 else 0 end as isRehab
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply (
						select top 1 count(*) as cnt
						from v_MesLink ml with(nolock)
						inner join v_MesOld kpg with(nolock) on kpg.Mes_id = ml.Mes_sid
						inner join v_LpuSectionProfile lsp with(nolock) on lsp.LpuSectionProfile_id = kpg.LpuSectionProfile_id
						where ml.Mes_id = mo.Mes_id and lsp.LpuSectionProfile_Code = '158'
					) RehabKPG
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select top 1
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex (nolock)
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
							)
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when RehabKPG.cnt > 0 then 1 else 0 end desc,
					eu.EvnUsluga_id,
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					if ($KSGOperArray[0]['isRehab']) {
						$KSGOperArray = array_filter($KSGOperArray, function($KSGOper) {
							return $KSGOper['isRehab'];
						});
					}
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
		if (empty($KSGException) && !empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
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

		// КПГ берём из профиля отделения
		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					mokpg.Mes_Code + ISNULL('. ' + mokpg.Mes_Name, '') as KPG,
					mokpg.Mes_id,
					mtkpg.MesTariff_Value as KOEF,
					mtkpg.MesTariff_id
				from MesOld mokpg (nolock)
					left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
				where
					mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id = 4 -- КПГ
					and mokpg.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					mtkpg.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KPGFromLpuSectionProfile = $resp[0];
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr && !$KSGOper['isRehab']) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select top 1
					MesLink_id
				from
					v_MesLink (nolock)
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] >= $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
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
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if ($KSGException) {
			$response['KSG'] = $KSGException['KSG'];
			$response['Mes_sid'] = $KSGException['Mes_id'];
			$response['KOEF'] = $KSGException['KOEF'];
			$response['MesTariff_id'] = $KSGException['MesTariff_id'];
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
		$KSGFromAbort = false;
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

		if (strtotime($data['EvnSection_disDate']) >= strtotime('25.12.2017')) {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEF2018($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT top 1
				es.EvnSection_id,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
			FROM
				v_EvnSection es with (nolock)
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) desc
		";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
				if (strtotime($data['LastEvnSection_disDate']) >= strtotime('25.12.2017')) {
					return $this->loadKSGKPGKOEF2018($data);
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
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id,
				case
					when MONTH(PS.Person_BirthDay) = MONTH(:EvnSection_setDate) and DAY(PS.Person_BirthDay) = DAY(:EvnSection_setDate) then 1 else 0
				end as BirthToday
			from
				v_PersonState PS (nolock)
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

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && !empty($data['EvnSection_id'])) {
			// ищем услугу A16.20.037
			$resp = $this->queryResult("
				select top 1
					EvnUsluga_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and uc.UslugaComplex_Code = 'A16.20.037'
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));

			if (!empty($resp[0]['EvnUsluga_id'])) {
				// всегда определяется КСГ 6
				$query = "
					select top 1
						mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
						mo.Mes_id,
						mt.MesTariff_Value as KOEF,
						mt.MesTariff_id
					from
						v_MesOld mo (nolock)
						left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mo.Mes_Code = '6'
						and mo.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
						and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				";

				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KSGFromAbort = $resp[0];
					}
				}
			}
		}

		// 0.	Определение КСГ при политравме
		if (empty($KSGFromAbort) && !empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) SOPUT
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '220'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
		if (empty($KSGFromAbort) && !empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					case when RehabKPG.cnt > 0 then 1 else 0 end as isRehab
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply (
						select top 1 count(*) as cnt
						from v_MesLink ml with(nolock)
						inner join v_MesOld kpg with(nolock) on kpg.Mes_id = ml.Mes_sid
						inner join v_LpuSectionProfile lsp with(nolock) on lsp.LpuSectionProfile_id = kpg.LpuSectionProfile_id
						where ml.Mes_id = mo.Mes_id and lsp.LpuSectionProfile_Code = '158'
					) RehabKPG
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select top 1
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex (nolock)
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
							)
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when RehabKPG.cnt > 0 then 1 else 0 end desc,
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					if ($KSGOperArray[0]['isRehab']) {
						$KSGOperArray = array_filter($KSGOperArray, function($KSGOper) {
							return $KSGOper['isRehab'];
						});
					}
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
		if (empty($KSGFromAbort) && !empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
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

		// КПГ берём из профиля отделения
		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					mokpg.Mes_Code + ISNULL('. ' + mokpg.Mes_Name, '') as KPG,
					mokpg.Mes_id,
					mtkpg.MesTariff_Value as KOEF,
					mtkpg.MesTariff_id
				from MesOld mokpg (nolock)
					left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
				where
					mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id = 4 -- КПГ
					and mokpg.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					mtkpg.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KPGFromLpuSectionProfile = $resp[0];
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr && !$KSGOper['isRehab']) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select top 1
					MesLink_id
				from
					v_MesLink (nolock)
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] >= $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
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
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
		}

		if ($KSGFromAbort) {
			$response['KSG'] = $KSGFromAbort['KSG'];
			$response['Mes_sid'] = $KSGFromAbort['Mes_id'];
			$response['KOEF'] = $KSGFromAbort['KOEF'];
			$response['MesTariff_id'] = $KSGFromAbort['MesTariff_id'];
		}

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 3);
		}

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф для 2016 года
	 */
	function loadKSGKPGKOEF($data) {
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
		if (empty($data['PayTypeOms_id'])) {
			return array('Error_Msg' => 'Ошибка получения идентификатора вида оплаты ОМС');
		}

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (strtotime($data['EvnSection_disDate']) >= strtotime('25.12.2018')) {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEF2019($data);
		} else if (strtotime($data['EvnSection_disDate']) >= strtotime('25.12.2017')) {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEF2018($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEF2017($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT top 1
				es.EvnSection_id,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
			FROM
				v_EvnSection es with (nolock)
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate desc
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

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id
			from
				v_PersonState PS (nolock)
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
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) SOPUT
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '216'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
				select top 100
					eu.EvnUsluga_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select top 1
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex (nolock)
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
							)
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
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
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
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

		// КПГ берём из профиля отделения
		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					mokpg.Mes_Code + ISNULL('. ' + mokpg.Mes_Name, '') as KPG,
					mokpg.Mes_id,
					mtkpg.MesTariff_Value as KOEF,
					mtkpg.MesTariff_id
				from MesOld mokpg (nolock)
					left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
				where
					mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id = 4 -- КПГ
					and mokpg.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					mtkpg.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KPGFromLpuSectionProfile = $resp[0];
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
				select top 1
					MesLink_id
				from
					v_MesLink (nolock)
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] >= $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
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
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
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

		$this->load->model('EvnSection_model', 'es_model');

		// достаём движения той же КВС но с годом меньше на 1, им должны проставиться КСГ по алгоритму нового года.
		$query = "
			SELECT
				es.EvnSection_id, es.LpuSection_id
			FROM
				v_EvnSection es with (nolock)
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
				and YEAR(ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) < :Year
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
				and es.LpuSection_id is not null
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

	/**
	 * Считаем КСКП для движения в 2019 году
	 */
	protected function calcCoeffCTP2019($data) {
		$EvnSection_CoeffCTP = 0;
		$List = array();

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			declare
				@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'kclp');

			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				outer apply (
					select top 1
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
				) CODE
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 13. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[13])) {
			$needKSLP13 = false;
			
			$LuchTerUsluga_id = $this->getFirstResultFromQuery("
				select top 1
				  	eu.EvnUsluga_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				where
					eu.EvnUsluga_pid in (" . implode(',', $data['EvnSectionIds']) . ")
					and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and ucat.UslugaComplexAttributeType_SysNick = 'luchter'
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
			", [
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate'],
			], true);

			$EvnUslugaOper_id = $this->getFirstResultFromQuery("
				select top 1
					eu.EvnUslugaOper_id
				from
					v_EvnUslugaOper eu (nolock)
				where
					eu.EvnUslugaOper_pid in (" . implode(',', $data['EvnSectionIds']) . ")
					and eu.PayType_id = :PayTypeOms_id
			", [
				'PayTypeOms_id' => $data['PayTypeOms_id'],
			], true);

			// 3. Услуги с атрибутом «Лучевая терапия» И услуги, введенной через форму «Добавление операции»
			if ( !empty($LuchTerUsluga_id) && !empty($EvnUslugaOper_id) == 2 ) {
				$needKSLP13 = true;
			}

			if ( $needKSLP13 == false ) {
				$drugTherapySchemeData = $this->queryResult("
					select
					  	dts.DrugTherapyScheme_Code
					from
						v_EvnSectionDrugTherapyScheme esdts (nolock)
						inner join v_DrugTherapyScheme as dts with (nolock) on dts.DrugTherapyScheme_id = esdts.DrugTherapyScheme_id 
					where
						esdts.EvnSection_id in ('" . implode("','", $data['EvnSectionIds']) . "')
				", []);

				if (is_array($drugTherapySchemeData) && count($drugTherapySchemeData) > 0) { 
					$countMT = 0;
					$countSH = 0;

					foreach ($drugTherapySchemeData as $row) {
						switch (strtolower(substr($row['DrugTherapyScheme_Code'], 0, 2))) {
							case 'mt': $countMT++; break;
							case 'sh': $countSH++; break;
						}
					}

					// 1. схемы лекарственной терапии с кодом ‘sh%’ (PROMEDWEB-2965) И услуги с атрибутом «Лучевая терапия»;
					if ($countSH > 0 && !empty($LuchTerUsluga_id)) {
						$needKSLP13 = true;
					}
					// 2. схемы лекарственной терапии И услуги, введенной через форму «Добавление операции»;
					else if (!empty($EvnUslugaOper_id) && !empty($LuchTerUsluga_id)) {
						$needKSLP13 = true;
					}
					// 4. двух и более схем лекарственной терапии, в том числе, одинаковых схем.
					else if ($countMT + $countSH >= 2) {
						$needKSLP13 = true;
					}
				}
			}

			if ($needKSLP13 === true) {
				$coeffCTP = $KSLPCodes[13];
				$List[] = [
					'Code' => 13,
					'Value' => $coeffCTP
				];
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 17. КСС. Сложность лечения, связанная с возрастом (госпитализация детей до 1 года), за исключением КСГ, относящихся к профилю «Неонатология».
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[17])) {
			if ($data['Person_Age'] < 1 && !in_array($data['MesOld_Num'], ['st17.001', 'st17.002', 'st17.003', 'st17.004', 'st17.005', 'st17.006', 'st17.007'])) {
				$coeffCTP = $KSLPCodes[17];
				$List[] = array(
					'Code' => 17,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
			}
		}

		// 18. КСС. Сложность лечения, связанная с возрастом (госпитализация детей от 1 года до 4 лет).
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[18])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] <= 4) {
				$coeffCTP = $KSLPCodes[18];
				$List[] = array(
					'Code' => 18,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 19. КСС. Необходимость предоставления спального места и питания законному представителю (дети до 4 лет).
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[19])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] <= 17) {
				$coeffCTP = $KSLPCodes[19];
				$List[] = array(
					'Code' => 19,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 20. КСС. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет).
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[20])) {
			if ($data['Person_Age'] >= 75 && !empty($data['EvnSectionIds'])) {
				$resp_es = $this->queryResult("
					select top 1
						EvnSection_id
					from
						v_EvnSection es (nolock)
						inner join fed.LpuSectionBedProfileLink LSBPLink with(nolock) on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
						inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id 
					where
						LSBP.LpuSectionBedProfile_Code = '72'
						and ES.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
				");

				if (empty($resp_es[0]['EvnSection_id'])) {
					$coeffCTP = $KSLPCodes[20];
					$List[] = array(
						'Code' => 20,
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

		// 21. КСС. Сложность лечения пациента при наличии у него старческой астении
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[21])) {
			if (!empty($data['EvnSectionIds'])) {
				$resp_es = $this->queryResult("
					declare @Mes_id bigint = (select top 1 Mes_id from v_MesOld (nolock) where MesOld_Num = 'st38.001');
					
					select top 1
						EvnSection_id
					from
						v_EvnSection es (nolock)
						inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
						inner join v_EvnDiagPS edps (nolock) on edps.EvnDiagPS_pid = es.EvnSection_id and edps.DiagSetClass_id IN (2,3)
						inner join v_Diag ds (nolock) on ds.Diag_id = edps.Diag_id
						inner join fed.LpuSectionBedProfileLink LSBPLink with(nolock) on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
						inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
						left join v_MesOldUslugaComplex mouc (nolock) on mouc.Mes_id = @Mes_id and mouc.Diag_id = es.Diag_id and mouc.MesOldUslugaComplex_begDT <= isnull(es.EvnSection_disDate, es.EvnSection_setDate) and (coalesce(mouc.MesOldUslugaComplex_endDT, es.EvnSection_disDate, es.EvnSection_setDate) >= isnull(es.EvnSection_disDate, es.EvnSection_setDate)) 
					where
						ds.Diag_Code = 'R54'
						and mouc.MesOldUslugaComplex_id is null
						and LSBP.LpuSectionBedProfile_Code = '72'
						and ES.EvnSection_BarthelIdx <= 60
						and ES.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
				");

				if (!empty($resp_es[0]['EvnSection_id'])) {
					$coeffCTP = $KSLPCodes[21];
					$List[] = array(
						'Code' => 21,
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

		// 22. ДС. Проведение I этапа экстракорпорального оплодотворения (стимуляция суперовуляции)
		// 23. ДС. Полный цикл экстракорпорального оплодотворения с криоконсервацией эмбрионов
		// 24. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (неполный цикл)
		// 25. ДС. Проведение I - III этапов экстракорпорального оплодотворения (стимуляция суперовуляции, получение яйцеклетки, экстракорпоральное оплодотворение и культивирование эмбрионов) с последующей криоконсервацией эмбрионов
		// 26. ДС. Полный цикл экстракорпорального оплодотворения без применения криоконсервации эмбрионов
		if (in_array($data['LpuUnitType_id'], ['6','7','9']) && !empty($data['EvnSectionIds'])) {
			$ekoKSLP = 0;

			// достаём коды услуг из группы движений
			$UslugaComplexCodes = [];

			$resp_eu = $this->queryResult("
				select
					uc.UslugaComplex_Code
				from
					v_EvnUsluga as eu (nolock)
					inner join v_UslugaComplex as uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				where
					eu.EvnUsluga_pid in (" . $data['EvnSection_id_IsCurrent'] . ")
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
			", [
				'PayTypeOms_id' => $data['PayTypeOms_id'],
			]);

			foreach ($resp_eu as $one_eu) {
				if (!in_array($one_eu['UslugaComplex_Code'], $UslugaComplexCodes)) {
					$UslugaComplexCodes[] = $one_eu['UslugaComplex_Code'];
				}
			}

			if (in_array('A11.20.017', $UslugaComplexCodes) && in_array('A11.20.027.4', $UslugaComplexCodes) && in_array('A11.20.031', $UslugaComplexCodes)) {
				$ekoKSLP = 23;
			}

			if (empty($ekoKSLP) && in_array('A11.20.017', $UslugaComplexCodes) && in_array('A11.20.027.3', $UslugaComplexCodes) && in_array('A11.20.031', $UslugaComplexCodes)) {
				$ekoKSLP = 25;
			}
			
			if (empty($ekoKSLP) && in_array('A11.20.017', $UslugaComplexCodes) && in_array('A11.20.027.4', $UslugaComplexCodes) && in_array('A11.20.030.001', $UslugaComplexCodes)) {
				$ekoKSLP = 24;
			}

			if (empty($ekoKSLP) && in_array('A11.20.017', $UslugaComplexCodes) && in_array('A11.20.027.4', $UslugaComplexCodes)) {
				$ekoKSLP = 26;
			}

			if (
				empty($ekoKSLP) && in_array('A11.20.017', $UslugaComplexCodes)
				&& (
					in_array('A11.20.027.1', $UslugaComplexCodes)
					|| in_array('A11.20.027.2', $UslugaComplexCodes)
					|| in_array('A11.20.027.3', $UslugaComplexCodes)
				)
			) {
				$ekoKSLP = 22;
			}

			if (!empty($ekoKSLP) && isset($KSLPCodes[$ekoKSLP])) {
				$coeffCTP = $KSLPCodes[$ekoKSLP];
				$List[] = [
					'Code' => $ekoKSLP,
					'Value' => $coeffCTP
				];

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				}
				else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}
		
		
		// 383. Проведение сочетанных хирургических вмешательств
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[383])) {
			
				$AttributeValue = $this->queryResult("
					-- @file " . __FILE__ . "
					-- @line " . __LINE__ . "

					declare
						@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2018-СочетХирВмеш');

					with UslugaList as (
						select
							eu.UslugaComplex_id
						from
							v_EvnUsluga eu (nolock)
						where
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.EvnClass_id in (43,22,29)
							and eu.PayType_id = :PayTypeOms_id 
					)
	
					SELECT top 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						cross apply (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UslugaList)
								and av2.AttributeValue_ValueIdent <> av.AttributeValue_ValueIdent
						) UC2FILTER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and av.AttributeValue_ValueIdent in (select UslugaComplex_id from UslugaList)
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[383];
					$List[] = [
						'Code' => 383,
						'Value' => $coeffCTP
					];
					
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					}
					else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			
		}
		
		// 384. Проведение однотипных операций на парных органах
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[384])) {
			
			$kolvo = $this->getFirstResultFromQuery("
						-- @file " . __FILE__ . "
						-- @line " . __LINE__ . "
	
						select top 1
							SUM(eu.EvnUsluga_Kolvo) as EvnUsluga_Kolvo
						from
							v_EvnUsluga eu (nolock)
						where
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.EvnClass_id in (43,22,29)
							and eu.PayType_id = :PayTypeOms_id
							and exists (
								select top 1 uca.UslugaComplexAttribute_id
								from v_UslugaComplexAttribute uca with (nolock)
									inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								where uca.UslugaComplex_id = eu.UslugaComplex_id
									and ucat.UslugaComplexAttributeType_SysNick = 'operpairorg'
									and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
									and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
							)
						group by
							eu.UslugaComplex_id
						order by
							EvnUsluga_Kolvo desc
					", array(
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'PayTypeOms_id' => $data['PayTypeOms_id'],
				)
			);
			if ($kolvo !== false && $kolvo >= 2) {
				$coeffCTP = $KSLPCodes[384];
				$List[] = [
					'Code' => 384,
					'Value' => $coeffCTP
				];
				
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
			
		}
		
		$result = array();
		foreach ( $data['EvnSectionIds'] as $EvnSection_id ) {
			$result[$EvnSection_id] = array(
				'hasKSLP14' => false,
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'List' => $List,
			);
		}
		
		foreach ( $result as $EvnSection_id => $kslpData ) {
			if ($kslpData['EvnSection_CoeffCTP'] > 1.8 ) {
				$kslpData['EvnSection_CoeffCTP'] = 1.8;
			}

			$kslpData['EvnSection_CoeffCTP'] = round($kslpData['EvnSection_CoeffCTP'], 4);

			$result[$EvnSection_id] = $kslpData;
		}

		// 14. КСС. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями.
		if ($data['LpuUnitType_id'] == '1') {
			$ksg45Array = array('st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007');
			// КСЛП НЕ применяется. Если КСГ st19.039 – st19.055
			$ksgExcArray = array('st19.039', 'st19.040', 'st19.041', 'st19.042', 'st19.043', 'st19.044', 'st19.045', 'st19.046', 'st19.047', 'st19.048', 'st19.049', 'st19.050', 'st19.051', 'st19.052', 'st19.053', 'st19.054', 'st19.055');
				if (
					!in_array($data['MesOld_Num'], $ksgExcArray)
					&& (
						( $data['Duration'] > 30 && !in_array($data['MesOld_Num'], $ksg45Array))
						|| $data['Duration'] > 45
					)
				) {
					$normDays = 30;
					if (in_array($data['MesOld_Num'], $ksg45Array)) {
						$normDays = 45;
					}

					$coefDl = 0.25;
					// в группе движений есть движение с профилем «Анестизиологии и реаниматологии» - 0,4
					foreach ( $data['LpuSectionProfiles'] as $EvnSection_id => $LpuSectionProfiles ) {
						if ($LpuSectionProfiles == '5' && isset($data['EvnSectionDurations'][$EvnSection_id]) && $data['EvnSectionDurations'][$EvnSection_id] > $normDays) {
							$coefDl = 0.4;
						}
					}

					$coeffCTP = round((1 + ( $data['Duration'] - $normDays) * $coefDl / $normDays), 4);
					foreach ( $data['EvnSectionIds'] as $EvnSection_id ) {
						$result[$EvnSection_id]['List'][] = array(
							'Code' => 14,
							'Value' => $coeffCTP,
						);
						if ($result[$EvnSection_id]['EvnSection_CoeffCTP'] > 0) {
							$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $result[$EvnSection_id]['EvnSection_CoeffCTP'] + $coeffCTP - 1;
						} else {
							$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $coeffCTP;
						}
					}
				}
		}

		return $result;
	}

	/**
	 * Считаем КСКП для движения в 2018 году
	 */
	protected function calcCoeffCTP2018($data) {
		if (DateTime::createFromFormat('Y-m-d', $data['EvnSection_disDate']) >= DateTime::createFromFormat('Y-m-d', '2018-12-25')) {
			return $this->calcCoeffCTP2019($data);
		}

		$EvnSection_CoeffCTP = 1;
		$List = array();

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			declare
				@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'kclp');

			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				outer apply (
					select top 1
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
				) CODE
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 13. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ
		if (isset($KSLPCodes[13])) {
			$needKSLP13 = false;

			$onkoUslData = $this->queryResult("
				select distinct top 2
				  	eu.EvnUsluga_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				where
					eu.EvnUsluga_pid in (" . implode(',', $data['EvnSectionIds']) . ")
					and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and ucat.UslugaComplexAttributeType_SysNick = 'luchter'
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
			", array(
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			// 4. Двух и более услуг с одним из атрибутов: «Лучевая терапия», в том числе одинаковых услуг;
			if ( is_array($onkoUslData) && count($onkoUslData) == 2 ) {
				$needKSLP13 = true;
			}

			if ( $needKSLP13 == false ) {
				$EvnUslugaOper_id = $this->getFirstResultFromQuery("
					select top 1
					  	eu.EvnUslugaOper_id
					from
						v_EvnUslugaOper eu (nolock)
					where
						eu.EvnUslugaOper_pid in (" . implode(',', $data['EvnSectionIds']) . ")
						and eu.PayType_id = :PayTypeOms_id
				", array(
					'PayTypeOms_id' => $data['PayTypeOms_id']
				), true);

				// 3. Услуга с одним из атрибутов: «Лучевая терапия», «КТ», «МРТ», «Ангиография» И Услуга, введенная через форму «Добавление операции»;
				if ( !empty($EvnUslugaOper_id) && is_array($onkoUslData) && count($onkoUslData) == 1 ) {
					$needKSLP13 = true;
				}
			}

			if ( $needKSLP13 == false ) {
				$drugTherapySchemeData = $this->queryResult("
					select top 2
					  	EvnSectionDrugTherapyScheme_id
					from
						v_EvnSectionDrugTherapyScheme (nolock)
					where
						EvnSection_id in ('" . implode("','", $data['EvnSectionIds']) . "')
						and DrugTherapyScheme_id is not null
				", array());

				if ( is_array($drugTherapySchemeData) ) {
					// 5. Двух и более схем лекарственной терапии, в том числе, одинаковых схем.
					if ( count($drugTherapySchemeData) == 2 ) {
						$needKSLP13 = true;
					}
					else if ( count($drugTherapySchemeData) == 1 ) {
						if ( !empty($EvnUslugaOper_id) ) {
							$needKSLP13 = true;
						}
						else if ( is_array($onkoUslData) && count($onkoUslData) == 1 ) {
							$needKSLP13 = true;
						}
					}
				}
			}

			if ($needKSLP13 === true) {
				$coeffCTP = $KSLPCodes[13];
				$List[] = array(
					'Code' => 13,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 17. КСС. Сложность лечения, связанная с возрастом (госпитализация детей до 1 года), за исключением КСГ, относящихся к профилю «Неонатология».
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[17])) {
			if ($data['Person_Age'] < 1) {
				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
						select top 1
							mt.MesTariff_id
						from
							v_MesTariff mt (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
							inner join v_MesOld mokpg (nolock) on mokpg.Mes_id = ml.Mes_sid
						where
							MesTariff_id = :MesTariff_id
							and mokpg.Mes_Code = '17'
					", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}
				if (empty($MesTariff_id)) { // не связано с КПГ 17
					$coeffCTP = $KSLPCodes[17];
					$List[] = array(
						'Code' => 17,
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

		// 18. КСС. Сложность лечения, связанная с возрастом (госпитализация детей от 1 года до 4 лет).
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[18])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] <= 4) {
				$coeffCTP = $KSLPCodes[18];
				$List[] = array(
					'Code' => 18,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 19. КСС. Необходимость предоставления спального места и питания законному представителю (дети до 4 лет).
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[19])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] <= 17) {
				$coeffCTP = $KSLPCodes[19];
				$List[] = array(
					'Code' => 19,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 20. КСС. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет).
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[20])) {
			if ($data['Person_Age'] >= 75) {
				// КВС НЕ содержит движений, в поле «Профиль» которых указан следующий профиль: код – 14, наименование – «гериатрии» (refs #145745)
				$resp = $this->queryResult("
					select top 1
						es.EvnSection_id
					from
						v_EvnSection es (nolock)
						inner join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
					where
						es.EvnSection_id in (" . implode(',', $data['EvnSectionIds']) . ")
						and lsp.LpuSectionProfile_Code = '14'
				");

				if (empty($resp[0]['EvnSection_id'])) {
					$coeffCTP = $KSLPCodes[20];
					$List[] = array(
						'Code' => 20,
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

		// 21. КСС. Сложность лечения пациента старше 60 лет, связанная с наличием у него функциональной зависимости
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[21])) {
			if ($data['Person_Age'] >= 60 && isset($data['EvnSection_BarthelIdx']) && $data['EvnSection_BarthelIdx'] <= 60) {
				$coeffCTP = $KSLPCodes[21];
				$List[] = array(
					'Code' => 21,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		$hasKSLP22 = false;
		$hasKSLP23 = false;
		$hasKSLP24 = false;
		$hasKSLP25 = false;
		$hasKSLP26 = false;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && !empty($data['EvnSectionIds'])) {
			// достаём коды услуг из группы движений
			$UslugaComplexCodes = array();
			$resp_eu = $this->queryResult("
				select
					uc.UslugaComplex_Code
				FROM
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid in (" . implode(',', $data['EvnSectionIds']) . ")
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
			", array(
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));

			foreach($resp_eu as $one_eu) {
				if (!in_array($one_eu['UslugaComplex_Code'], $UslugaComplexCodes)) {
					$UslugaComplexCodes[] = $one_eu['UslugaComplex_Code'];
				}
			}

			$kslpCount = 0;

			if (
			(in_array('A11.20.027', $UslugaComplexCodes) && in_array('A11.20.027.1', $UslugaComplexCodes))
				|| (in_array('A11.20.027', $UslugaComplexCodes) && in_array('A11.20.027.2', $UslugaComplexCodes))
				|| (in_array('A11.20.027', $UslugaComplexCodes) && in_array('A11.20.027.3', $UslugaComplexCodes) && !in_array('A11.20.031', $UslugaComplexCodes))
			) {
				$kslpCount++;
				$hasKSLP22 = true;
			}

			if (in_array('A11.20.027', $UslugaComplexCodes) && in_array('A11.20.027.4', $UslugaComplexCodes) && in_array('A11.20.031', $UslugaComplexCodes)) {
				$kslpCount++;
				$hasKSLP23 = true;
			}

			if (in_array('A11.20.027', $UslugaComplexCodes) && in_array('A11.20.027.4', $UslugaComplexCodes) && in_array('A11.20.030.001', $UslugaComplexCodes)) {
				$kslpCount++;
				$hasKSLP24 = true;
			}

			if (in_array('A11.20.027', $UslugaComplexCodes) && in_array('A11.20.027.3', $UslugaComplexCodes) && in_array('A11.20.031', $UslugaComplexCodes)) {
				$kslpCount++;
				$hasKSLP25 = true;
			}

			if (in_array('A11.20.027', $UslugaComplexCodes) && in_array('A11.20.027.4', $UslugaComplexCodes) && !in_array('A11.20.030.001', $UslugaComplexCodes) && !in_array('A11.20.031', $UslugaComplexCodes)) {
				$kslpCount++;
				$hasKSLP26 = true;
			}

			// Примечание по КСЛП 22-26: если для случая может быть применено несколько КСЛП 22-26 (т.е. случай содержит несколько разных наборов услуг), ни один из этих КСЛП не применяется.
			if ($kslpCount > 1) {
				$hasKSLP22 = false;
				$hasKSLP23 = false;
				$hasKSLP24 = false;
				$hasKSLP25 = false;
				$hasKSLP26 = false;
			}
		}

		// 22. ДС. Проведение I этапа экстракорпорального оплодотворения (стимуляция суперовуляции)
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[22])) {
			if ($hasKSLP22) {
				$coeffCTP = $KSLPCodes[22];
				$List[] = array(
					'Code' => 22,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 23. ДС. Полный цикл экстракорпорального оплодотворения с криоконсервацией эмбрионов
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[23])) {
			if ($hasKSLP23) {
				$coeffCTP = $KSLPCodes[23];
				$List[] = array(
					'Code' => 23,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 24. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (неполный цикл)
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[24])) {
			if ($hasKSLP24) {
				$coeffCTP = $KSLPCodes[24];
				$List[] = array(
					'Code' => 24,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 25. ДС. Проведение I - III этапов экстракорпорального оплодотворения (стимуляция суперовуляции, получение яйцеклетки, экстракорпоральное оплодотворение и культивирование эмбрионов) с последующей криоконсервацией эмбрионов
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[25])) {
			if ($hasKSLP25) {
				$coeffCTP = $KSLPCodes[25];
				$List[] = array(
					'Code' => 25,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 26. ДС. Полный цикл экстракорпорального оплодотворения без применения криоконсервации эмбрионов
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[26])) {
			if ($hasKSLP26) {
				$coeffCTP = $KSLPCodes[26];
				$List[] = array(
					'Code' => 26,
					'Value' => $coeffCTP
				);

				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// Добавление КСЛП для конкретных движений
		$result = array();

		foreach ( $data['EvnSectionIds'] as $EvnSection_id ) {
			$result[$EvnSection_id] = array(
				'hasKSLP14' => false,
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'List' => $List,
			);
		}

		// 14. КСС. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями.
		if ($data['LpuUnitType_id'] == '1') {
			$ksg45Array = array('45', '46', '108', '109', '161', '162', '233', '279', '280', '298');

			foreach ( $data['EvnSectionDurations'] as $EvnSection_id => $Duration ) {
				if (
					($Duration > 30 && !in_array($data['Mes_Code'], $ksg45Array))
					|| $Duration > 45
				) {
					$EvnSection_CoeffCTP = $result[$EvnSection_id]['EvnSection_CoeffCTP'];

					$normDays = 30;
					if (in_array($data['Mes_Code'], $ksg45Array)) {
						$normDays = 45;
					}

					$coefDl = 0.25;

					$coeffCTP = round((1 + ($Duration - $normDays) * $coefDl / $normDays), 4);
					$result[$EvnSection_id]['List'][] = array(
						'Code' => 14,
						'Value' => $coeffCTP,
					);
					if ($result[$EvnSection_id]['EvnSection_CoeffCTP'] > 0) {
						$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $result[$EvnSection_id]['EvnSection_CoeffCTP'] + $coeffCTP - 1;
					} else {
						$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $coeffCTP;
					}
					$result[$EvnSection_id]['hasKSLP14'] = true;
				}
			}
		}

		// 383. Проведение сочетанных хирургических вмешательств
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[383])) {
			foreach ( $data['EvnSections'] as $EvnSection_id => $row ) {
				$AttributeValue = $this->queryResult("
					-- @file " . __FILE__ . "
					-- @line " . __LINE__ . "

					declare
						@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2018-СочетХирВмеш');

					with UslugaList as (
						select
							eu.UslugaComplex_id
						from
							v_EvnUsluga eu (nolock)
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and eu.EvnClass_id in (43,22,29)
							and eu.PayType_id = :PayTypeOms_id 
					)
	
					SELECT top 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						cross apply (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UslugaList)
								and av2.AttributeValue_ValueIdent <> av.AttributeValue_ValueIdent
						) UC2FILTER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and av.AttributeValue_ValueIdent in (select UslugaComplex_id from UslugaList)
				", array(
					'EvnSection_id' => $EvnSection_id,
					'EvnSection_disDate' => $row['EvnSection_disDate'],
					'PayTypeOms_id' => $data['PayTypeOms_id'],
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[383];
					$result[$EvnSection_id]['List'][] = array(
						'Code' => 383,
						'Value' => $coeffCTP,
					);
					if ($result[$EvnSection_id]['EvnSection_CoeffCTP'] > 0) {
						$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $result[$EvnSection_id]['EvnSection_CoeffCTP'] + $coeffCTP - 1;
					} else {
						$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $coeffCTP;
					}
				}
			}
		}

		// 384. Проведение однотипных операций на парных органах
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[384])) {
			foreach ( $data['EvnSections'] as $EvnSection_id => $row ) {
				$kolvo = $this->getFirstResultFromQuery("
					-- @file " . __FILE__ . "
					-- @line " . __LINE__ . "

					select
						SUM(eu.EvnUsluga_Kolvo) as EvnUsluga_Kolvo
					from
						v_EvnUsluga eu (nolock)
					where
						eu.EvnUsluga_pid = :EvnSection_id
						and eu.EvnClass_id in (43,22,29)
						and eu.PayType_id = :PayTypeOms_id
						and exists (
							select top 1 uca.UslugaComplexAttribute_id
							from v_UslugaComplexAttribute uca with (nolock)
								inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'operpairorg'
								and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
								and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						)
					", array(
						'EvnSection_id' => $EvnSection_id,
						'EvnSection_disDate' => $row['EvnSection_disDate'],
						'PayTypeOms_id' => $data['PayTypeOms_id'],
					)
				);
				if ($kolvo !== false && $kolvo >= 2) {
					$coeffCTP = $KSLPCodes[384];
					$result[$EvnSection_id]['List'][] = array(
						'Code' => 384,
						'Value' => $coeffCTP,
					);
					if ($result[$EvnSection_id]['EvnSection_CoeffCTP'] > 0) {
						$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $result[$EvnSection_id]['EvnSection_CoeffCTP'] + $coeffCTP - 1;
					} else {
						$result[$EvnSection_id]['EvnSection_CoeffCTP'] = $coeffCTP;
					}
				}
			}
		}

		foreach ( $result as $EvnSection_id => $kslpData ) {
			if ( $kslpData['hasKSLP14'] == false && $kslpData['EvnSection_CoeffCTP'] > 1.8 ) {
				$kslpData['EvnSection_CoeffCTP'] = 1.8;
			}

			$kslpData['EvnSection_CoeffCTP'] = round($kslpData['EvnSection_CoeffCTP'], 4);

			$result[$EvnSection_id] = $kslpData;
		}

		return $result;
	}

	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		if (empty($data['PayTypeOms_id'])) {
			$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
			if (empty($data['PayTypeOms_id'])) {
				throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
			}
		}

		if (DateTime::createFromFormat('Y-m-d', $data['EvnSection_disDate']) >= DateTime::createFromFormat('Y-m-d', '2018-12-25')) {
			return $this->calcCoeffCTP2019($data);
		} else if (DateTime::createFromFormat('Y-m-d', $data['EvnSection_disDate']) >= DateTime::createFromFormat('Y-m-d', '2017-12-25')) {
			return $this->calcCoeffCTP2018($data);
		}

		$comaDigits = 3;
		$EvnSection_CoeffCTP = 1;
		$List = array();

		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// КСЛП определять только в круглосуточном стационаре.
			return array(
				'EvnSection_CoeffCTP' => 0,
				'List' => array(),
			);
		}

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			declare
				@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'kclp');

			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				outer apply (
					select top 1
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
				) CODE
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 8. Сложность лечения пациента, связанная с возрастом (госпитализация детей до 1 года) (кроме КСГ, относящихся к профилю "неонатология")
		if (isset($KSLPCodes[8])) {
			if ($data['Person_Age'] < 1) {
				$LpuSectionProfile_id = null;
				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
					select top 1
						mt.MesTariff_id
					from
						v_MesTariff mt (nolock)
						inner join v_MesLink ml (nolock) on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
						inner join v_MesOld mokpg (nolock) on mokpg.Mes_id = ml.Mes_sid
					where
						MesTariff_id = :MesTariff_id
						and mokpg.Mes_Code = '17'
				", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}
				if (empty($MesTariff_id)) { // не связано с КПГ 17
					$coeffCTP = $KSLPCodes[8];
					$List[] = array(
						'Code' => 15,	//Код КСЛП для реестра
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

		// 9. Сложность лечения пациента, связанная с возрастом (госпитализация детей от 1 до 4 включительно)
		if (isset($KSLPCodes[9])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] <= 4) {
				$coeffCTP = $KSLPCodes[9];
				$List[] = array(
					'Code' => 16,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 10. Необходимость предоставления спального места и питания законному представителю (дети до 4)
		if (isset($KSLPCodes[10])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] <= 4) {
				$coeffCTP = $KSLPCodes[10];
				$List[] = array(
					'Code' => 10,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 11. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет)
		if (isset($KSLPCodes[11])) {
			if ($data['Person_Age'] >= 75) {
				$coeffCTP = $KSLPCodes[11];
				$List[] = array(
					'Code' => 11,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 12. Необходимость предоставления спального места и питания законному представителю ребенка после достижения им возраста 4 лет при наличии медицинских показаний
		if (isset($KSLPCodes[12])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] >= 4 && $data['Person_Age'] < 18) {
				$coeffCTP = $KSLPCodes[12];
				$List[] = array(
					'Code' => 12,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 13. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ (перечень возможных сочетаний КСГ представлен в Инструкции)
		if (isset($KSLPCodes[13])) {
			$EvnUsluga = $this->queryResult("
				with eus as (
					select
						eu.EvnUsluga_id,
						eu.UslugaComplex_id,
						mo.Mes_id,
						eu.EvnClass_id
					FROM
						v_EvnSection es (nolock)
						inner join v_EvnUsluga eu (nolock) on eu.EvnUsluga_pid = es.EvnSection_id
						inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id and mouc.MesOldUslugaComplex_begDT <= eu.EvnUsluga_setDate and ISNULL(mouc.MesOldUslugaComplex_endDT, eu.EvnUsluga_setDate) >= eu.EvnUsluga_setDate
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
						inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id and mo.MesType_id = (case when lu.LpuUnitType_id in (6,7,9) then 9 else 10 end)
					where
						es.EvnSection_id in (" . implode(',', $data['EvnSectionIds']) . ")
						and eu.EvnClass_id in (43,22,29)
						and eu.PayType_id = :PayTypeOms_id
				)
				
				SELECT top 1
					eu.EvnUsluga_id
				FROM
					eus eu (nolock)
					inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				WHERE
					ucat.UslugaComplexAttributeType_SysNick IN ('luchter', 'him')
					and exists (
						select top 1
							eu2.EvnUsluga_id
						from
							eus eu2 (nolock)
							inner join v_UslugaComplexAttribute uca2 (nolock) on uca2.UslugaComplex_id = eu2.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat2 (nolock) on ucat2.UslugaComplexAttributeType_id = uca2.UslugaComplexAttributeType_id
						where
							ucat2.UslugaComplexAttributeType_SysNick IN ('luchter')
							and eu2.EvnUsluga_id <> eu.EvnUsluga_id
							and eu2.Mes_id <> eu.Mes_id -- относятся к разным КСГ
					)
			", array(
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));

			if (empty($EvnUsluga[0]['EvnUsluga_id'])) {
				// 2.	Услуга с атрибутом «Химиотерапия» + Услуга, введенная через форму «Добавление операции»;
				// 3.	Услуга с атрибутом «Лучевая терапия» + Услуга, введенная через форму «Добавление операции»;
				$EvnUsluga = $this->queryResult("
					with eus as (
						select
							eu.EvnUsluga_id,
							eu.UslugaComplex_id,
							mo.Mes_id,
							eu.EvnClass_id
						FROM
							v_EvnSection es (nolock)
							inner join v_EvnUsluga eu (nolock) on eu.EvnUsluga_pid = es.EvnSection_id
							inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id and mouc.MesOldUslugaComplex_begDT <= eu.EvnUsluga_setDate and ISNULL(mouc.MesOldUslugaComplex_endDT, eu.EvnUsluga_setDate) >= eu.EvnUsluga_setDate
							left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
							left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
							inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id and mo.MesType_id = (case when lu.LpuUnitType_id in (6,7,9) then 9 else 10 end)
						where
							es.EvnSection_id in (" . implode(',', $data['EvnSectionIds']) . ")
							and eu.EvnClass_id in (43,22,29)
							and eu.PayType_id = :PayTypeOms_id
					)
					
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						eus eu (nolock)
						inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
						inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					WHERE
						ucat.UslugaComplexAttributeType_SysNick IN ('luchter', 'him')
						and exists (
							select top 1
								eu2.EvnUsluga_id
							from
								eus eu2 (nolock)
							where
								eu2.EvnClass_id in (43) -- Услуга, введенная через форму «Добавление операции»
								and eu2.EvnUsluga_id <> eu.EvnUsluga_id
								and eu2.Mes_id <> eu.Mes_id -- относятся к разным КСГ
						)
				", array(
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
			}

			if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
				$coeffCTP = $KSLPCodes[13];
				$List[] = array(
					'Code' => 13,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 14. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
		$ksg45Array = array('44', '45', '106', '107', '148', '149', '220', '266', '267', '285');
		if (strtotime($data['EvnSection_disDate']) >= strtotime('25.12.2017')) {
			$ksg45Array = array('45', '46', '108', '109', '161', '162', '233', '279', '280', '298');
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

			// @task https://redmine.swan.perm.ru/issues/115705
			// Округление до 4-х знаков
			$comaDigits = 4;
			$coeffCTP = round((1 + ($data['Duration'] - $normDays) * $coefDl / $normDays), 4); // 2 знака после запятой по ТЗ, в отличие от Перми
			$List[] = array(
				'Code' => 14,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, $comaDigits);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List,
		);
	}

	/**
	 * Пересчёт КСЛП для всей КВС
	 */
	protected function _recalcKSKP()
	{
		//для дневного стационара
		if (in_array($this->lpuUnitTypeId, [6,7,9])){
			$EvnSection_id=$this->id;
			$filters='es.EvnSection_id = :EvnSection_id';
			$filtersZeroing='e.Evn_id = :EvnSection_id';

		}else {
			$EvnSection_id = $this->pid;
			$filters = 'es.EvnSection_pid = :EvnSection_id';
			$filtersZeroing = 'e.Evn_pid = :EvnSection_id';
		}
		
		// убираем КСЛП со всех движений КВС
		$query = "
			update
				es with (rowlock)
			set
				es.EvnSection_CoeffCTP = null
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
			where
				{$filtersZeroing}
		";
		$this->db->query($query, array(
			'EvnSection_id' => $EvnSection_id
		));

		// удаляем все связки КСЛП по всем движениям.
		$query = "
			select
				eskl.EvnSectionKSLPLink_id
			from
				EvnSectionKSLPLink eskl (nolock)
				inner join Evn e (nolock) on e.Evn_id = eskl.EvnSection_id
			where
				{$filtersZeroing}
		";
		$resp_eskl = $this->queryResult($query, array(
			'EvnSection_id' => $EvnSection_id
		));
		foreach($resp_eskl as $one_eskl) {
			$this->db->query("
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
	
				exec p_EvnSectionKSLPLink_del
					@EvnSectionKSLPLink_id = :EvnSectionKSLPLink_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", array(
				'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
			));
		}
		
		$resp_es = $this->recalcIndexNumByGroup(array(
			'getGroupsOnly' => true
		));
		
		$k = 0;
		$groupped = array();

		foreach($resp_es as $key => $respone) {
			$group_key = $respone['groupNum'];

			$datediff = strtotime($respone['EvnSection_disDate']) - strtotime($respone['EvnSection_setDate']);
			$Duration = floor($datediff/(60*60*24));

			$respone['Duration'] = $Duration;

			$respone['EvnSection_CoeffCTP'] = 1;
			$groupped[$group_key]['EvnSections'][$respone['EvnSection_id']] = $respone;
			$groupped[$group_key]['MaxCoeff']['Lpu_id'] = $respone['Lpu_id'];

			// Возраст человека берём из первого движения группы, т.е. минимальный
			if ($resp_es[0]['Person_Age']==0) {
				$groupped[$group_key]['MaxCoeff']['Person_Age'] = $resp_es[0]['Person_Age'];
			}else {
				if (empty($groupped[$group_key]['MaxCoeff']['Person_Age']) || $groupped[$group_key]['MaxCoeff']['Person_Age'] > $respone['Person_Age']) {
					$groupped[$group_key]['MaxCoeff']['Person_Age'] = $respone['Person_Age'];
				}
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
				(
					$respone['LpuSectionProfile_Code'] != 5
					|| empty($groupped[$group_key]['MaxCoeff']['EvnSection_id'])
				)
				&& (
					empty($groupped[$group_key]['MaxCoeff']['MesTariff_Value'])
					|| $groupped[$group_key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
					|| ($groupped[$group_key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && $groupped[$group_key]['MaxCoeff']['EvnSection_Index'] < $respone['EvnSection_Index'])
				)
			) {
				$groupped[$group_key]['MaxCoeff']['EvnSection_Index'] = $respone['EvnSection_Index'];
				$groupped[$group_key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$group_key]['MaxCoeff']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
				$groupped[$group_key]['MaxCoeff']['EvnSection_BarthelIdx'] = $respone['EvnSection_BarthelIdx'];
				$groupped[$group_key]['MaxCoeff']['Diag_Code'] = $respone['Diag_Code'];
				$groupped[$group_key]['MaxCoeff']['Mes_Code'] = $respone['Mes_Code'];
				$groupped[$group_key]['MaxCoeff']['MesTariff_id'] = $respone['MesTariff_id'];
				$groupped[$group_key]['MaxCoeff']['MesOld_Num'] = $respone['MesOld_Num'];
				$groupped[$group_key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
			}
		}

		// для каждого движения группы надо выбрать движение с наибольшим КСГ.
		foreach($groupped as $key => $group) {
			$EvnSectionIds = array();
			$EvnSectionDurations = array();
			$LpuSectionProfiles = array();
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
				$EvnSectionDurations[$es['EvnSection_id']] = $es['Duration'];
				$LpuSectionProfiles[$es['EvnSection_id']] = $es['LpuSectionProfile_Code'];
			}
			$groupped[$key]['MaxCoeff']['EvnSectionIds'] = $EvnSectionIds; // все движения группы
			$groupped[$key]['MaxCoeff']['EvnSectionDurations'] = $EvnSectionDurations; // длительность движений группы
			$groupped[$key]['MaxCoeff']['LpuSectionProfiles'] = $LpuSectionProfiles; // профили движений группы

			// Длительность - общая длительность группы
			$datediff = strtotime($group['MaxCoeff']['EvnSection_disDate']) - strtotime($group['MaxCoeff']['EvnSection_setDate']);
			$Duration = floor($datediff/(60*60*24));
			$groupped[$key]['MaxCoeff']['Duration'] = $Duration;
		}

		$indexNum = 0;
		foreach($groupped as $group) {
			$indexNum++;

			$esdata = array(
				'EvnSection_id_IsCurrent' => $this->id,
				'EvnSections' => $group['EvnSections'],
				'EvnSection_id' => $group['MaxCoeff']['EvnSection_id'],
				'EvnSectionIds' => $group['MaxCoeff']['EvnSectionIds'],
				'EvnSectionDurations' => $group['MaxCoeff']['EvnSectionDurations'],
				'LpuSectionProfiles' => $group['MaxCoeff']['LpuSectionProfiles'],
				'LpuUnitType_id' => $group['MaxCoeff']['LpuUnitType_id'],
				'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
				'Person_Age' => $group['MaxCoeff']['Person_Age'],
				'Duration' => $group['MaxCoeff']['Duration'],
				'Mes_Code' => $group['MaxCoeff']['Mes_Code'],
				'MesTariff_id' => $group['MaxCoeff']['MesTariff_id'],
				'MesOld_Num' => $group['MaxCoeff']['MesOld_Num'],
				'EvnSection_IsAdultEscort' => $group['MaxCoeff']['EvnSection_IsAdultEscort'],
				'EvnSection_IsMedReason' => $group['MaxCoeff']['EvnSection_IsMedReason'],
				'EvnSection_BarthelIdx' => $group['MaxCoeff']['EvnSection_BarthelIdx'],
				'Diag_Code' => $group['MaxCoeff']['Diag_Code']
			);

			$kslp = $this->calcCoeffCTP($esdata);

			// 4. записываем для каждого движения группы полученные КСЛП в БД.
			foreach ( $kslp as $EvnSection_id => $kslpData ) {
				$query = "
					update
						EvnSection with (rowlock)
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP
					where
						EvnSection_id = :EvnSection_id
				";

				$this->db->query($query, array(
					'EvnSection_CoeffCTP' => $kslpData['EvnSection_CoeffCTP'],
					'EvnSection_id' => $EvnSection_id
				));

				// и список КСЛП тоже для каждого движения группы refs #136750
				foreach($kslpData['List'] as $one_kslp) {
					$this->db->query("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000),
							@EvnSectionKSLPLink_id bigint;
			
						exec p_EvnSectionKSLPLink_ins
							@EvnSectionKSLPLink_id = @EvnSectionKSLPLink_id output,
							@EvnSection_id = :EvnSection_id,
							@EvnSectionKSLPLink_Code = :EvnSectionKSLPLink_Code,
							@EvnSectionKSLPLink_Value = :EvnSectionKSLPLink_Value,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			
						select @EvnSectionKSLPLink_id as EvnSectionKSLPLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", array(
						'EvnSection_id' => $EvnSection_id,
						'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
						'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
						'pmUser_id' => $this->promedUserId
					));
				}
			}
		}
	}
	
	/**
	 * Перегруппировка движений для всей КВС
	 */
	protected function _recalcIndexNum()
	{
		$this->recalcIndexNumByGroup();
	}
	
	/**
	 * Перегруппировка движений для всей КВС
	 */
	protected function recalcIndexNumByGroup($data = array())
	{
		if (empty($data['getGroupsOnly'])) {
			// убираем КСЛП со всех движений КВС
			$query = "
			update
				es with (rowlock)
			set
				es.EvnSection_IndexNum = null
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
			$this->db->query($query, array(
				'EvnSection_pid' => $this->pid
			));
		}
		
		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				ls.LpuSectionProfile_Code,
				es.LpuSection_id,
				lu.LpuUnit_Code,
				lu.LpuUnitType_id,
				d.Diag_Code,
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age,
				isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate,
				es.Lpu_id,
				es.MesTariff_id,
				mt.MesTariff_Value,
				mtmes.Mes_Code,
				mtmes.MesOld_Num,
				es.EvnSection_IsAdultEscort,
				es.EvnSection_IsMedReason,
				es.EvnSection_BarthelIdx,
				d.Diag_Code,
				es.EvnSection_Index
			from
				v_EvnSection es (nolock)
				inner join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
				left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mtmes (nolock) on mtmes.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
			order by
				es.EvnSection_setDT
		", array(
			'EvnSection_pid' => $this->pid
		));
		
		$groupNum = 0; // счётчик групп
		
		// Движения группируются по отделению, т.е. в одну группу включаются подряд идущие движения с одинаковым отделением
		$predKey = null; // ключ предыдущего движения
		foreach ($resp_es as $key => $value) {
			
			if($value['LpuSectionProfile_Code'] == 5){
				continue;
			}
			
			if(!is_null($predKey)){
				
				if($resp_es[$predKey]['LpuSection_id'] == $value['LpuSection_id']){
					
					if(empty($resp_es[$predKey]['groupNum'])){
						$groupNum++;
						$resp_es[$predKey]['groupNum'] = $groupNum;
					}
					
					$datediff = strtotime($resp_es[$predKey]['EvnSection_disDate']) - strtotime($resp_es[$predKey]['EvnSection_setDate']);
					$duration = floor($datediff/(60*60*24));
					$diag_array = array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2');
					
					if(($resp_es[$predKey]['MesOld_Num'] == 'st02.001' && (($duration >= 2 && in_array($resp_es[$predKey]['Diag_Code'],$diag_array)) || $duration >= 6) )
						&& in_array($value['MesOld_Num'], array('st02.003', 'st02.004'))
					){
						$groupNum++; // В этой ситуации Движение-1 и Движение-2 включаются в разные группы движений
					}
					
					$resp_es[$key]['groupNum'] = $groupNum;
				}
				
			}
			
			$predKey = $key;
		}
		
		// Движение по реанимации группируется с ближайшим движением с профилем отличным от «Анестезиология и реаниматология».
		// Если есть такие движения до и после движения по реанимации, то движение по реанимации группируется с предыдущим движением.
		$predKey = null; // ключ предыдущего движения
		$needGroup = false;
		foreach($resp_es as $key => $value) {
			
			if ($needGroup) { // предыдущее было по реанимации
				if (empty($resp_es[$key]['groupNum'])) {
					$groupNum++;
					$resp_es[$key]['groupNum'] = $groupNum;
				}
				$resp_es[$predKey]['groupNum'] = $resp_es[$key]['groupNum'];
				$needGroup = false;
			}
			
			if ($value['LpuSectionProfile_Code'] == '5') {
				if (!is_null($predKey)) {
					// группируем с предыдущим
					if (empty($resp_es[$predKey]['groupNum'])) {
						$groupNum++;
						$resp_es[$predKey]['groupNum'] = $groupNum;
					}
					$resp_es[$key]['groupNum'] = $resp_es[$predKey]['groupNum'];
					
				} else {
					// надо будет сгруппировать следующее движение с этим.
					$needGroup = true;
				}
			}
			
			$predKey = $key;
		}
		
		foreach($resp_es as $key => $value) {
			if(empty($value['groupNum'])){
				$groupNum++;
				$resp_es[$key]['groupNum'] = $groupNum;
			}
		}
		
		if (!empty($data['getGroupsOnly'])) {
			return $resp_es;
		}
		
		// В рамках каждой группы определяется оплачиваемое движение. На движении с самой дорогой КСГ (MesTariff.MesTariff_Value)
		// устанавливается признак EvnSection_IsWillPaid (признак НЕ может быть установлен на движении в отделении реанимации).
		$paidArray = array();
		foreach($resp_es as $key => $value) {
			if(empty($paidArray[$value['groupNum']])){
				$paidArray[$value['groupNum']] = ['EvnSection_id' => $value['EvnSection_id'], 'MesTariff_Value' => $value['MesTariff_Value']];
			}
			if($value['LpuSectionProfile_Code'] != '5' && ($value['MesTariff_Value'] >= $paidArray[$value['groupNum']]['MesTariff_Value'])){
				$paidArray[$value['groupNum']] = ['EvnSection_id' => $value['EvnSection_id'], 'MesTariff_Value' => $value['MesTariff_Value']];
			}
		}
		
		// Апедйт в БД
		foreach($resp_es as $key => $value) {
			$this->db->query("
				update
					es with (rowlock)
				set
					es.EvnSection_IndexNum = :EvnSection_IndexNum,
					es.EvnSection_IsWillPaid = :EvnSection_IsWillPaid
				from
					EvnSection es
				where
					es.EvnSection_id = :EvnSection_id				
			", array(
				'EvnSection_IndexNum' => $value['groupNum'],
				'EvnSection_IsWillPaid' => ($value['LpuSectionProfile_Code'] != '5' && $paidArray[$value['groupNum']]['EvnSection_id'] == $value['EvnSection_id']?2:1),
				'EvnSection_id' => $value['EvnSection_id']
			));
		}
	}
}
